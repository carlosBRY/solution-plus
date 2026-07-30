<?php

use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Permission::findOrCreate('gérer-utilisateurs', 'web');
    $role = Role::findOrCreate('Administrateur', 'web');
    $role->givePermissionTo('gérer-utilisateurs');
});

test('administrateur can view users list', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Administrateur');

    $response = $this->actingAs($admin)->get(route('parametres.users.index'));

    $response->assertOk();
});

test('administrateur can create a new user', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Administrateur');

    $response = $this->actingAs($admin)->post(route('parametres.users.store'), [
        'name' => 'Nouveau Collaborateur',
        'email' => 'nouveau@caveprestige.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'role' => 'Administrateur',
    ]);

    $response->assertRedirect(route('parametres.users.index'));
    $this->assertDatabaseHas('users', ['email' => 'nouveau@caveprestige.com']);
});
