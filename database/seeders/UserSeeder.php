<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::query()->firstOrNew([
            'email' => 'test@example.com',
        ]);

        $user->forceFill([
            'name' => 'Test User',
            'password' => 'password',
            'email_verified_at' => now(),
        ])->save();
    }
}
