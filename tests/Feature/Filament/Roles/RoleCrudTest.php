<?php

use App\Filament\Resources\Roles\Pages\CreateRole;
use App\Filament\Resources\Roles\Pages\EditRole;
use App\Filament\Resources\Roles\Pages\ListRoles;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Filament\Actions\Testing\TestAction;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);

    app(PermissionRegistrar::class)->forgetCachedPermissions();
});

function roleManager(): User
{
    $user = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $user->givePermissionTo('roles.manage');

    return $user;
}

it('allows users with the role management permission to view roles', function () {
    $user = roleManager();

    Livewire::actingAs($user)
        ->test(ListRoles::class)
        ->assertSuccessful()
        ->assertCanSeeTableRecords([
            Role::findByName('admin', 'web'),
            Role::findByName('contributor', 'web'),
        ]);
});

it('denies role management access to users without the required permission', function () {
    $user = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    Livewire::actingAs($user)
        ->test(ListRoles::class)
        ->assertForbidden();
});

it('creates a role with selected existing permissions', function () {
    $user = roleManager();

    $permission = Permission::findByName('pages.create', 'web');

    Livewire::actingAs($user)
        ->test(CreateRole::class)
        ->fillForm([
            'name' => 'reviewer',
            'permissions_pages' => [$permission->id],
        ])
        ->assertSchemaStateSet([
            'permissions_pages' => [$permission->id],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $role = Role::findByName('reviewer', 'web');

    expect($role->hasPermissionTo('pages.create'))->toBeTrue()
        ->and($role->permissions)->toHaveCount(1);
});

it('rejects duplicate role names', function () {
    $user = roleManager();

    Livewire::actingAs($user)
        ->test(CreateRole::class)
        ->fillForm([
            'name' => 'contributor',
            'permissions' => [],
        ])
        ->call('create')
        ->assertHasFormErrors(['name']);
});

it('updates a role name and replaces its permissions', function () {
    $user = roleManager();

    $role = Role::findByName('contributor', 'web');
    $permission = Permission::findByName('pages.review', 'web');

    Livewire::actingAs($user)
        ->test(EditRole::class, ['record' => $role->getRouteKey()])
        ->fillForm([
            'name' => 'content-reviewer',
            'permissions_pages' => [$permission->id],
            'permissions_users' => [],
            'permissions_roles' => [],
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $role->refresh();

    expect($role->name)->toBe('content-reviewer')
        ->and($role->permissions)->toHaveCount(1)
        ->and($role->hasPermissionTo('pages.review'))->toBeTrue()
        ->and($role->hasPermissionTo('pages.create'))->toBeFalse();
});

it('allows a role to keep its name while all permissions are removed', function () {
    $user = roleManager();

    $role = Role::findByName('contributor', 'web');

    Livewire::actingAs($user)
        ->test(EditRole::class, ['record' => $role->getRouteKey()])
        ->fillForm([
            'name' => 'contributor',
            'permissions_pages' => [],
            'permissions_users' => [],
            'permissions_roles' => [],
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($role->fresh()->permissions)->toBeEmpty();
});

it('allows a role to be deleted', function () {
    $user = roleManager();

    $role = Role::create([
        'name' => 'temporary-reviewer',
        'guard_name' => 'web',
    ]);

    Livewire::actingAs($user)
        ->test(ListRoles::class)
        ->callAction(TestAction::make('delete')->table($role));

    expect(Role::find($role->id))->toBeNull();
});

it('does not allow users without role management permission to create roles', function () {
    $user = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    Livewire::actingAs($user)
        ->test(CreateRole::class)
        ->assertForbidden();
});

it('does not allow users without role management permission to edit roles', function () {
    $user = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $role = Role::findByName('contributor', 'web');

    Livewire::actingAs($user)
        ->test(EditRole::class, ['record' => $role->getRouteKey()])
        ->assertForbidden();
});

it('creates a role with permissions selected from multiple groups', function () {
    $user = roleManager();

    $pagePermission = Permission::findByName('pages.create', 'web');
    $userPermission = Permission::findByName('users.manage', 'web');
    $rolePermission = Permission::findByName('roles.manage', 'web');

    Livewire::actingAs($user)
        ->test(CreateRole::class)
        ->fillForm([
            'name' => 'reviewer',
            'permissions_pages' => [$pagePermission->id],
            'permissions_users' => [$userPermission->id],
            'permissions_roles' => [$rolePermission->id],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $role = Role::findByName('reviewer', 'web');

    expect($role->permissions)->toHaveCount(3)
        ->and($role->hasPermissionTo('pages.create'))->toBeTrue()
        ->and($role->hasPermissionTo('users.manage'))->toBeTrue()
        ->and($role->hasPermissionTo('roles.manage'))->toBeTrue();
});

it('replaces permissions from all groups when updating a role', function () {
    $user = roleManager();

    $role = Role::findByName('contributor', 'web');
    $pagePermission = Permission::findByName('pages.review', 'web');
    $userPermission = Permission::findByName('users.view', 'web');

    Livewire::actingAs($user)
        ->test(EditRole::class, ['record' => $role->getRouteKey()])
        ->fillForm([
            'name' => 'content-reviewer',
            'permissions_pages' => [$pagePermission->id],
            'permissions_users' => [$userPermission->id],
            'permissions_roles' => [],
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $role->refresh();

    expect($role->permissions)->toHaveCount(2)
        ->and($role->hasPermissionTo('pages.review'))->toBeTrue()
        ->and($role->hasPermissionTo('users.view'))->toBeTrue()
        ->and($role->hasPermissionTo('pages.create'))->toBeFalse()
        ->and($role->hasPermissionTo('pages.submit-for-review'))->toBeFalse();
});

it('loads existing permissions into their corresponding groups when editing a role', function () {
    $user = roleManager();

    $role = Role::findByName('contributor', 'web');

    $expectedPagePermissionIds = $role->permissions()
        ->whereIn('name', [
            'pages.administration',
            'pages.view',
            'pages.create',
            'pages.update',
            'pages.submit-for-review',
        ])
        ->pluck('permissions.id')
        ->sort()
        ->values()
        ->all();

    Livewire::actingAs($user)
        ->test(EditRole::class, ['record' => $role->getRouteKey()])
        ->assertSchemaStateSet([
            'permissions_pages' => $expectedPagePermissionIds,
            'permissions_users' => [],
            'permissions_roles' => [],
        ]);
});

it('removes all permissions when every grouped permission list is empty', function () {
    $user = roleManager();

    $role = Role::findByName('contributor', 'web');

    Livewire::actingAs($user)
        ->test(EditRole::class, ['record' => $role->getRouteKey()])
        ->fillForm([
            'name' => 'contributor',
            'permissions_pages' => [],
            'permissions_users' => [],
            'permissions_roles' => [],
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($role->fresh()->permissions)->toBeEmpty();
});
