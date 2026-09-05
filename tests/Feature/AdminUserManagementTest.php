<?php

namespace Tests\Feature;

use App\Models\Team;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminUserManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    public function test_guest_cannot_access_user_admin(): void
    {
        $this->get(route('admin.users.index'))->assertRedirect(route('login'));
    }

    public function test_staff_cannot_access_user_admin(): void
    {
        $staff = User::factory()->create();
        $staff->assignRole('staff');

        $this->actingAs($staff)
            ->get(route('admin.users.index'))
            ->assertForbidden();
    }

    public function test_admin_can_create_user_with_manager_and_roles(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(['staff', 'admin']);

        $hom = User::factory()->create(['name' => 'HoM']);
        $hom->assignRole(['staff', 'head_of_marketing']);

        $team = Team::create(['name' => 'Ops', 'code' => 'OPS', 'is_active' => true]);

        $response = $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'Field Spender',
            'email' => 'spender@example.test',
            'password' => 'password',
            'password_confirmation' => 'password',
            'team_id' => $team->id,
            'manager_id' => $hom->id,
            'roles' => ['staff', 'spender'],
            'is_active' => '1',
        ]);

        $user = User::where('email', 'spender@example.test')->first();
        $this->assertNotNull($user);
        $response->assertRedirect(route('admin.users.edit', $user));

        $this->assertTrue($user->hasRole('spender'));
        $this->assertSame($hom->id, $user->manager_id);
        $this->assertSame([$hom->id], $user->approvalChainUsers()->pluck('id')->all());
    }

    public function test_manager_cycle_is_rejected(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(['staff', 'admin']);

        $a = User::factory()->create(['name' => 'A']);
        $a->assignRole('staff');
        $b = User::factory()->create(['name' => 'B', 'manager_id' => $a->id]);
        $b->assignRole('staff');

        $this->actingAs($admin)
            ->put(route('admin.users.update', $a), [
                'name' => $a->name,
                'email' => $a->email,
                'manager_id' => $b->id,
                'roles' => ['staff'],
                'is_active' => '1',
            ])
            ->assertSessionHasErrors('manager_id');

        $this->assertNull($a->fresh()->manager_id);
    }

    public function test_approval_chains_page_renders(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(['staff', 'admin']);

        $hom = User::factory()->create(['name' => 'HoM']);
        $hom->assignRole(['staff', 'head_of_marketing']);

        $manager = User::factory()->create(['name' => 'Manager', 'manager_id' => $hom->id]);
        $manager->assignRole('staff');

        $spender = User::factory()->create(['name' => 'Spender', 'manager_id' => $manager->id]);
        $spender->assignRole('staff');

        $this->actingAs($admin)
            ->get(route('admin.users.chains'))
            ->assertOk()
            ->assertSee('Spender → Manager → HoM')
            ->assertSee('Org hierarchy');
    }

    public function test_non_super_admin_cannot_assign_super_admin_role(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(['staff', 'admin']);

        $this->actingAs($admin)
            ->post(route('admin.users.store'), [
                'name' => 'Would Be Super',
                'email' => 'nosuper@example.test',
                'password' => 'password',
                'password_confirmation' => 'password',
                'roles' => ['staff', 'super_admin'],
                'is_active' => '1',
            ])
            ->assertSessionHasErrors('roles');

        $this->assertDatabaseMissing('users', ['email' => 'nosuper@example.test']);
    }
}
