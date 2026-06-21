<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Force seed/reset admin credentials specifically by email
        \App\Models\Admin::updateOrCreate(
            ['email' => 'admin@reso.local'],
            [
                'name' => 'Reso Admin',
                'password' => bcrypt('AdminPassword123!'),
            ]
        );

        \App\Models\Admin::updateOrCreate(
            ['email' => 'admin@musicsocial.com'],
            [
                'name' => 'MusicSocial Admin',
                'password' => bcrypt('AdminPassword123!'),
            ]
        );

        // Only seed test users if we're not in production
        if (!app()->environment('production')) {
            if (User::where('email', 'test@example.com')->count() === 0) {
                User::factory()->create([
                    'name' => 'Test User',
                    'email' => 'test@example.com',
                ]);
            }
        }
    }
}
