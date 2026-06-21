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
        // Ensure default admin users exist specifically by email
        if (\App\Models\Admin::where('email', 'admin@reso.local')->count() === 0) {
            \App\Models\Admin::create([
                'name' => 'Reso Admin',
                'email' => 'admin@reso.local',
                'password' => bcrypt('AdminPassword123!'),
            ]);
        }

        if (\App\Models\Admin::where('email', 'admin@musicsocial.com')->count() === 0) {
            \App\Models\Admin::create([
                'name' => 'MusicSocial Admin',
                'email' => 'admin@musicsocial.com',
                'password' => bcrypt('AdminPassword123!'),
            ]);
        }

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
