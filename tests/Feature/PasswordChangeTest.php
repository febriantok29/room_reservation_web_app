<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

it('redirects admin to change-password page when must_change_password is true', function () {
    $user = User::query()->create([
        'id' => (string) Str::uuid7(),
        'employee_id' => 'ADM-2026-TEST',
        'division_id' => null,
        'email' => 'force-admin@roomreservation.com',
        'password' => Hash::make('Temporary123'),
        'first_name' => 'Force',
        'last_name' => 'Admin',
        'date_of_birth' => '1990-05-10',
        'is_admin' => true,
        'is_active' => true,
        'must_change_password' => true,
    ]);

    $response = $this->post(route('admin.login.submit'), [
        'login' => $user->email,
        'password' => 'Temporary123',
    ]);

    $response->assertRedirect(route('admin.password.change'));
});

it('clears must_change_password after changing password via web', function () {
    $user = User::query()->create([
        'id' => (string) Str::uuid7(),
        'employee_id' => 'ADM-2026-TEST2',
        'division_id' => null,
        'email' => 'force-admin2@roomreservation.com',
        'password' => Hash::make('Temporary123'),
        'first_name' => 'Force',
        'last_name' => 'Admin2',
        'date_of_birth' => '1990-05-10',
        'is_admin' => true,
        'is_active' => true,
        'must_change_password' => true,
    ]);

    $this->actingAs($user);

    $response = $this->post(route('admin.password.change.submit'), [
        'current_password' => 'Temporary123',
        'new_password' => 'NewPassword456',
        'new_password_confirmation' => 'NewPassword456',
    ]);

    $response->assertRedirect(route('admin.dashboard'));
    expect($user->fresh()->must_change_password)->toBeFalse();
    expect(Hash::check('NewPassword456', $user->fresh()->password))->toBeTrue();
});

it('requires valid current password when changing password', function () {
    $user = User::query()->create([
        'id' => (string) Str::uuid7(),
        'employee_id' => 'ADM-2026-TEST3',
        'division_id' => null,
        'email' => 'force-admin3@roomreservation.com',
        'password' => Hash::make('Temporary123'),
        'first_name' => 'Force',
        'last_name' => 'Admin3',
        'date_of_birth' => '1990-05-10',
        'is_admin' => true,
        'is_active' => true,
        'must_change_password' => true,
    ]);

    $this->actingAs($user);

    $response = $this->post(route('admin.password.change.submit'), [
        'current_password' => 'WrongPassword',
        'new_password' => 'NewPassword456',
        'new_password_confirmation' => 'NewPassword456',
    ]);

    $response->assertSessionHasErrors('current_password');
    expect($user->fresh()->must_change_password)->toBeTrue();
});

it('blocks API access when must_change_password is true', function () {
    $user = User::query()->create([
        'id' => (string) Str::uuid7(),
        'employee_id' => 'ADM-2026-TEST4',
        'division_id' => null,
        'email' => 'force-admin4@roomreservation.com',
        'password' => Hash::make('Temporary123'),
        'first_name' => 'Force',
        'last_name' => 'Admin4',
        'date_of_birth' => '1990-05-10',
        'is_admin' => true,
        'is_active' => true,
        'must_change_password' => true,
    ]);

    $response = $this->withHeader('X-User-Id', $user->id)
        ->getJson('/api/v1/rooms');

    $response->assertStatus(403)
        ->assertJsonPath('error_code', 'PASSWORD_CHANGE_REQUIRED');
});

it('allows change-password endpoint when must_change_password is true', function () {
    $user = User::query()->create([
        'id' => (string) Str::uuid7(),
        'employee_id' => 'ADM-2026-TEST5',
        'division_id' => null,
        'email' => 'force-admin5@roomreservation.com',
        'password' => Hash::make('Temporary123'),
        'first_name' => 'Force',
        'last_name' => 'Admin5',
        'date_of_birth' => '1990-05-10',
        'is_admin' => true,
        'is_active' => true,
        'must_change_password' => true,
    ]);

    $response = $this->withHeader('X-User-Id', $user->id)
        ->postJson('/api/v1/auth/change-password', [
            'current_password' => 'Temporary123',
            'new_password' => 'NewPassword456',
        ]);

    $response->assertStatus(200);
    expect($user->fresh()->must_change_password)->toBeFalse();
});
