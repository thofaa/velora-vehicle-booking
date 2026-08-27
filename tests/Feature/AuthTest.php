<?php

use App\Models\User;

test('guest is shown the login page', function () {
    $this->get(route('login'))->assertOk();
});

test('guest can register a new account', function () {
    $this->post(route('register.submit'), [
        'name' => 'Budi',
        'email' => 'budi@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertRedirect(route('dashboard'));

    $this->assertAuthenticated();
    $this->assertDatabaseHas('users', [
        'email' => 'budi@example.com',
        'role' => 'penyetuju',
    ]);
});

test('registered credentials can log in', function () {
    $user = User::factory()->create(['password' => bcrypt('password')]);

    $this->post(route('login.submit'), [
        'email' => $user->email,
        'password' => 'password',
    ])->assertRedirect(route('dashboard'));

    $this->assertAuthenticatedAs($user);
});

test('login rejects invalid credentials', function () {
    $this->post(route('login.submit'), [
        'email' => 'tidak@ada.com',
        'password' => 'salah',
    ])->assertSessionHasErrors('email');

    $this->assertGuest();
});

test('authenticated user is redirected away from login', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get(route('login'))->assertRedirect(route('dashboard'));
});

test('guest is redirected to login when accessing dashboard', function () {
    $this->get(route('dashboard'))->assertRedirect(route('login'));
});

test('user can log out', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post(route('logout'))->assertRedirect(route('login'));

    $this->assertGuest();
});
