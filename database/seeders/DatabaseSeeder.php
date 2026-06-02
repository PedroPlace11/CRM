<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Reset Spatie cache so newly created roles/permissions are visible.
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Default roles.
        foreach (['admin', 'manager', 'sales'] as $roleName) {
            Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
        }

        $admin = User::updateOrCreate(
            ['email' => 'admin@crm.test'],
            [
                'name' => 'Admin',
                'password' => bcrypt('admin1234'),
                'email_verified_at' => now(),
            ]
        );

        $admin->syncRoles(['admin']);

        $this->call(CrmDefaultsSeeder::class);
        $this->call(EntitiesSeeder::class);
        $this->call(PeopleSeeder::class);
        $this->call(PublicFormsSeeder::class);
        $this->call(DealsSeeder::class);
        $this->call(ProductsSeeder::class);
        $this->call(AiSuggestionsSeeder::class);
        $this->call(AutomationRulesSeeder::class);
        $this->call(CalendarEventsSeeder::class);
    }
}
