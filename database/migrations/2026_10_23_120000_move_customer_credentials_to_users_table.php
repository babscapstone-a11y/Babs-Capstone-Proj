<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Role-based accounts: every login (staff and customer) is a row in `users`, told apart
 * by its role. `customers` keeps only the customer's profile and links to its login
 * through user_id, the same way `staff` does.
 *
 * Also removes staff.email, which only repeated users.email.
 */
return new class extends Migration
{
    private const CREDENTIALS = ['email', 'email_verified_at', 'password', 'remember_token', 'status'];

    /** Each step is skipped when already done, so a run that stopped partway can be run again. */
    public function up(): void
    {
        $now = now();
        $linked = Schema::hasColumn('customers', 'user_id');

        // Stop before changing anything if a customer's email is already a staff login:
        // users.email is unique, so that customer couldn't be moved over
        if (Schema::hasColumn('customers', 'email')) {
            $clashes = DB::table('customers')
                ->join('users', 'users.email', '=', 'customers.email')
                ->when($linked, fn ($q) => $q->whereNull('customers.user_id'))
                ->pluck('customers.email');

            if ($clashes->isNotEmpty()) {
                throw new RuntimeException('These customer emails are already used by staff accounts; change one of each '
                    . 'pair first, then run the migration again: ' . $clashes->implode(', '));
            }
        }

        // Id 5 (after the four staff roles) when it's free; MySQL never reuses a deleted id on its own
        $customerRoleId = DB::table('roles')->where('role_name', 'customer')->value('id')
            ?? DB::table('roles')->insertGetId(array_filter([
                'id'        => DB::table('roles')->where('id', 5)->exists() ? null : 5,
                'role_name' => 'customer', 'display_name' => 'Customer', 'created_at' => $now, 'updated_at' => $now,
            ], fn ($v) => $v !== null));

        if (! $linked) {
            Schema::table('customers', function (Blueprint $table) {
                $table->foreignId('user_id')->nullable()->unique()->after('public_id')
                      ->constrained('users')->restrictOnDelete();
            });
        }

        // One login per customer, with the same email, password hash and status, so
        // customers keep signing in exactly as before
        $unlinked = Schema::hasColumn('customers', 'email')
            ? DB::table('customers')->whereNull('user_id')->orderBy('id')->get()
            : collect();

        foreach ($unlinked as $customer) {
            DB::transaction(function () use ($customer, $customerRoleId) {
                $userId = DB::table('users')->insertGetId([
                    'public_id'         => User::newPublicId(),
                    'role_id'           => $customerRoleId,
                    'email'             => $customer->email,
                    'username'          => null,
                    'status'            => $customer->status,
                    'email_verified_at' => $customer->email_verified_at ?? null,
                    'password'          => $customer->password ?? Hash::make(Str::random(40)),
                    'remember_token'    => $customer->remember_token ?? null,
                    'created_at'        => $customer->created_at,
                    'updated_at'        => $customer->updated_at,
                ]);

                DB::table('customers')->where('id', $customer->id)->update(['user_id' => $userId]);
            });
        }

        $credentials = array_values(array_filter(self::CREDENTIALS, fn ($col) => Schema::hasColumn('customers', $col)));

        Schema::table('customers', function (Blueprint $table) use ($credentials) {
            $table->unsignedBigInteger('user_id')->nullable(false)->change();
            if (Schema::hasIndex('customers', 'customers_email_unique')) {
                $table->dropUnique('customers_email_unique');
            }
            if ($credentials) {
                $table->dropColumn($credentials);
            }
        });

        if (Schema::hasColumn('staff', 'email')) {
            Schema::table('staff', function (Blueprint $table) {
                if (Schema::hasIndex('staff', 'staff_email_unique')) {
                    $table->dropUnique('staff_email_unique');
                }
                $table->dropColumn('email');
            });
        }
    }

    public function down(): void
    {
        Schema::table('staff', function (Blueprint $table) {
            $table->string('email')->nullable()->after('last_name');
        });
        DB::statement('UPDATE staff s JOIN users u ON u.id = s.user_id SET s.email = u.email');
        DB::table('staff')->whereNull('email')->update(['email' => DB::raw("CONCAT('staff-', id, '@invalid.local')")]);
        Schema::table('staff', function (Blueprint $table) {
            $table->string('email')->nullable(false)->change();
            $table->unique('email');
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->string('email')->nullable()->after('last_name');
            $table->timestamp('email_verified_at')->nullable()->after('email');
            $table->string('password')->nullable()->after('email_verified_at');
            $table->string('status')->default('active')->after('contact_no');
            $table->string('remember_token', 100)->nullable()->after('deactivated_at');
        });
        DB::statement('
            UPDATE customers c JOIN users u ON u.id = c.user_id
            SET c.email = u.email, c.email_verified_at = u.email_verified_at, c.password = u.password,
                c.status = u.status, c.remember_token = u.remember_token
        ');
        Schema::table('customers', function (Blueprint $table) {
            $table->string('email')->nullable(false)->change();
            $table->unique('email');
        });

        $userIds = DB::table('customers')->pluck('user_id');

        Schema::table('customers', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropUnique(['user_id']);
            $table->dropColumn('user_id');
        });

        DB::table('users')->whereIn('id', $userIds)->delete();
        DB::table('roles')->where('role_name', 'customer')->delete();
    }
};
