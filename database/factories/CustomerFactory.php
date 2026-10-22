<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * A customer profile together with its login (a users row with the "customer" role).
 *
 * @extends Factory<Customer>
 */
class CustomerFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => function () {
                $user = new User([
                    'email'    => fake()->unique()->safeEmail(),
                    'password' => static::$password ??= Hash::make('password'),
                    'role_id'  => Role::firstOrCreate(['role_name' => Role::CUSTOMER], ['display_name' => 'Customer'])->id,
                    'status'   => 'active',
                ]);
                $user->forceFill(['email_verified_at' => now(), 'remember_token' => Str::random(10)])->save();

                return $user->id;
            },
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'contact_no' => fake()->numerify('09#########'),
        ];
    }
}
