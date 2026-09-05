<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\MonthlyBudget;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $team = Team::firstOrCreate(
            ['code' => 'MKT'],
            ['name' => 'Marketing Operations', 'is_active' => true]
        );

        $categories = [
            ['code' => 'DIG', 'name' => 'Digital Marketing'],
            ['code' => 'EVT', 'name' => 'Events & Activations'],
            ['code' => 'DLR', 'name' => 'Dealer Support'],
            ['code' => 'POS', 'name' => 'Promotional Items/Merchandising'],
            ['code' => 'DDS', 'name' => 'Digital-Dealer Support'],
            ['code' => 'DCO', 'name' => 'Digital-Content'],
            ['code' => 'DBF', 'name' => 'Digital- Boosting (Facebook)'],
            ['code' => 'DBT', 'name' => 'Digital- Boosting (Tiktok)'],
            ['code' => 'DBY', 'name' => 'Digital- Boosting (YouTube)'],
            ['code' => 'INF', 'name' => 'Influencer Marketing'],
        ];

        foreach ($categories as $cat) {
            Category::updateOrCreate(
                ['code' => $cat['code']],
                ['name' => $cat['name'], 'is_active' => true]
            );
        }

        $hom = User::updateOrCreate(
            ['email' => 'hom@ymb.test'],
            ['name' => 'Head of Marketing', 'password' => Hash::make('password'), 'team_id' => $team->id, 'is_active' => true]
        );
        $hom->syncRoles(['staff', 'head_of_marketing']);

        $approver = User::updateOrCreate(
            ['email' => 'approver@ymb.test'],
            ['name' => 'Line Manager', 'password' => Hash::make('password'), 'team_id' => $team->id, 'manager_id' => $hom->id, 'is_active' => true]
        );
        $approver->syncRoles(['staff', 'approver']);

        $spender = User::updateOrCreate(
            ['email' => 'spender@ymb.test'],
            ['name' => 'Field Spender', 'password' => Hash::make('password'), 'team_id' => $team->id, 'manager_id' => $approver->id, 'is_active' => true]
        );
        $spender->syncRoles(['staff']);

        $initiator = User::updateOrCreate(
            ['email' => 'initiator@ymb.test'],
            ['name' => 'Marketing Initiator', 'password' => Hash::make('password'), 'team_id' => $team->id, 'is_active' => true]
        );
        $initiator->syncRoles(['staff']);

        $admin = User::updateOrCreate(
            ['email' => 'admin@ymb.test'],
            ['name' => 'System Admin', 'password' => Hash::make('password'), 'team_id' => $team->id, 'is_active' => true]
        );
        $admin->syncRoles(['admin', 'staff']);

        $superAdmin = User::updateOrCreate(
            ['email' => 'superadmin@ymb.test'],
            ['name' => 'Super Admin', 'password' => Hash::make('password'), 'team_id' => $team->id, 'is_active' => true]
        );
        $superAdmin->syncRoles(['super_admin', 'admin', 'staff']);

        MonthlyBudget::updateOrCreate(
            ['year' => now()->year, 'month' => now()->month],
            ['amount_bdt' => 5000000, 'set_by' => $hom->id, 'notes' => 'Demo monthly marketing pot']
        );
    }
}
