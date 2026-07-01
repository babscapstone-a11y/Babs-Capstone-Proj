<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'name')) {
            return;
        }

        // Backfill a staff record for any user who has neither a staff nor a
        // customer row, so their name isn't lost once the column is dropped.
        DB::table('users')
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))->from('staff')->whereColumn('staff.user_id', 'users.id');
            })
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))->from('customers')->whereColumn('customers.user_id', 'users.id');
            })
            ->get(['id', 'name', 'email'])
            ->each(function ($user) {
                $parts = explode(' ', trim($user->name), 2);

                DB::table('staff')->insert([
                    'user_id'    => $user->id,
                    'first_name' => $parts[0] ?? '',
                    'last_name'  => $parts[1] ?? '',
                    'email'      => $user->email,
                    'contact_no' => '',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('users', 'name')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->string('name')->nullable()->after('id');
        });

        DB::table('users')->orderBy('id')->get(['id'])->each(function ($user) {
            $staff    = DB::table('staff')->where('user_id', $user->id)->first();
            $customer = DB::table('customers')->where('user_id', $user->id)->first();
            $record   = $staff ?? $customer;

            if ($record) {
                DB::table('users')->where('id', $user->id)->update([
                    'name' => trim("{$record->first_name} {$record->last_name}"),
                ]);
            }
        });
    }
};
