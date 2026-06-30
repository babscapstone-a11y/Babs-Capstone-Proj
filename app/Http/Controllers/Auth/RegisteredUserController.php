<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name'  => ['required', 'string', 'max:100'],
            'email'      => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'phone'      => ['nullable', 'string', 'max:20'],
            'password'   => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $customerRole = Role::where('role_name', 'customer')->firstOrFail();
        $fullName     = trim("{$request->first_name} {$request->last_name}");

        $user = DB::transaction(function () use ($request, $customerRole, $fullName) {
            $user = User::create([
                'name'     => $fullName,
                'email'    => $request->email,
                'phone'    => $request->phone,
                'password' => Hash::make($request->password),
                'role_id'  => $customerRole->id,
                'status'   => 'active',
            ]);

            Customer::create([
                'user_id'    => $user->id,
                'first_name' => $request->first_name,
                'last_name'  => $request->last_name,
                'email'      => $user->email,
                'contact_no' => $request->phone,
                'status'     => 'active',
            ]);

            return $user;
        });

        event(new Registered($user));

        // Do NOT auto-login. Redirect to login with success notice.
        return redirect()->route('login')
            ->with('registration_success', true)
            ->with('registered_name', $request->first_name);
    }
}
