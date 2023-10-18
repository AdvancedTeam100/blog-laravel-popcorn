<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Let's clear the users table first
        User::truncate();

        $faker = \Faker\Factory::create();

        // Let's make sure everyone has the same password and
        // let's hash it before the loop, or else our seeder
        // will be too slow.

        User::create([
            'user_id' => 'Super Admin',
            'password' => bcrypt('123456'),
            'email' => 'popcorn@gmail.com',
            'name' => 'Potter Amos',
            'read_name' => 'Potter Amos',
            'status' => '1',
            'birthday' => '1990-01-01',
            'phone_number' => '1234567890',
            'memo' => 'My memo',
            'phone_device' => 'android',
            'ninetieth_life' => 90,
            'work_life' => 40,
            'die_life' => 80,
            'healthy_life' => 70,
            'average_life' => 75,
            'group_id' => '0',
            'role_id' => 1, // Assign the appropriate role ID
            'email_verified_at' => now(),
        ]);
    }
}
