<?php

use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Permission::findOrCreate('gérer-categories', 'web');
    $role = Role::findOrCreate('Gérant', 'web');
    $role->givePermissionTo('gérer-categories');
});

test('user with permission can view categories', function () {
    $user = User::factory()->create();
    $user->assignRole('Gérant');

    $response = $this->actingAs($user)->get(route('categories.index'));

    $response->assertOk();
});

test('user can create a category via store endpoint', function () {
    $user = User::factory()->create();
    $user->assignRole('Gérant');

    $response = $this->actingAs($user)->post(route('categories.store'), [
        'nom' => 'Vins de Bordeaux',
        'description' => 'Grands crus classés du Bordelais',
    ]);

    $response->assertRedirect(route('categories.index'));
    $this->assertDatabaseHas('categories', ['nom' => 'Vins de Bordeaux']);
});
