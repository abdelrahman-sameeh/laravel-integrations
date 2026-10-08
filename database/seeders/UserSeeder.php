<?php

namespace Database\Seeders;

use App\Models\User;
use Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::factory()->createMany([
            [
                'name' => 'عبدالرحمن',
                'email' => 'abdo@gmail.com',
                'password' => Hash::make('123456'),
            ],
            [
                'name' => 'احمد',
                'email' => 'ahmed@gmail.com',
                'password' => Hash::make('123456'),
            ],
        ]);

        User::factory()
            ->count(8)
            ->create([
                'password' => Hash::make('123456'),
            ]);

    }
}
