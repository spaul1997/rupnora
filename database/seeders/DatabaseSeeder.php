<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $users = [
            [
                'name' => 'Admin',
                'email' => 'admin@aurellejewellery.com',
                'phone' => '+91 90000 00001',
                'role' => 'admin',
                'password' => 'password',
            ],
            [
                'name' => 'Demo Customer',
                'email' => 'customer@aurellejewellery.com',
                'phone' => '+91 90000 00002',
                'role' => 'customer',
                'password' => 'password',
            ],
        ];

        foreach ($users as $user) {
            User::query()->updateOrCreate(
                ['email' => $user['email']],
                [
                    'name' => $user['name'],
                    'phone' => $user['phone'],
                    'role' => $user['role'],
                    'password' => Hash::make($user['password']),
                    'is_active' => true,
                    'email_verified_at' => now(),
                ]
            );
        }

        $this->call([
            CatalogSeeder::class,
            MasterDataSeeder::class,
        ]);
    }
}
