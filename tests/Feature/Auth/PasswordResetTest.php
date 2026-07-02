<?php

use App\Models\PasswordResetOtp;
use App\Models\User;
use App\Notifications\PasswordResetOtpNotification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;

test('reset password link screen can be rendered', function () {
    $response = $this->get('/forgot-password');

    $response->assertStatus(200);
});

test('an otp is sent when a password reset is requested', function () {
    Notification::fake();

    $user = User::factory()->create();

    $response = $this->post('/forgot-password', ['email' => $user->email]);

    Notification::assertSentTo($user, PasswordResetOtpNotification::class);
    $response->assertRedirect(route('password.otp.verify'));
});

test('requesting a reset for an unknown email still redirects to the otp screen', function () {
    Notification::fake();

    $response = $this->post('/forgot-password', ['email' => 'nobody@example.com']);

    Notification::assertNothingSent();
    $response->assertRedirect(route('password.otp.verify'));
});

test('otp verify screen cannot be reached without requesting a code first', function () {
    $response = $this->get('/forgot-password/verify-otp');

    $response->assertRedirect(route('password.request'));
});

test('password can be reset with a valid otp', function () {
    Notification::fake();

    $user = User::factory()->create();

    $this->post('/forgot-password', ['email' => $user->email]);

    Notification::assertSentTo($user, PasswordResetOtpNotification::class, function ($notification) use ($user) {
        $verifyResponse = $this->post('/forgot-password/verify-otp', ['otp' => $notification->otp]);
        $verifyResponse->assertSessionHasNoErrors()->assertRedirect(route('password.otp.reset'));

        $resetResponse = $this->post('/forgot-password/reset', [
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);
        $resetResponse->assertSessionHasNoErrors()->assertRedirect(route('login'));

        expect(Hash::check('new-password', $user->fresh()->password))->toBeTrue();

        return true;
    });
});

test('an incorrect otp is rejected and does not reset the password', function () {
    Notification::fake();

    $user = User::factory()->create();
    $originalPassword = $user->password;

    $this->post('/forgot-password', ['email' => $user->email]);

    $response = $this->post('/forgot-password/verify-otp', ['otp' => '000000']);

    $response->assertSessionHasErrors('otp');
    expect($user->fresh()->password)->toBe($originalPassword);
});

test('an expired otp is rejected', function () {
    Notification::fake();

    $user = User::factory()->create();

    $this->post('/forgot-password', ['email' => $user->email]);

    Notification::assertSentTo($user, PasswordResetOtpNotification::class, function ($notification) use ($user) {
        PasswordResetOtp::where('email', $user->email)->update([
            'expires_at' => now()->subMinute(),
        ]);

        $response = $this->post('/forgot-password/verify-otp', ['otp' => $notification->otp]);
        $response->assertSessionHasErrors('otp');

        return true;
    });
});
