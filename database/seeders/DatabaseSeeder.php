<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // This way, we can simply run 
        // $ php artisan db:seed and it will run all the called classes: 
        $this->call(UsersTableSeeder::class);
        $this->call(RolesTableSeeder::class);
        $this->call(GroupTableSeeder::class);
        $this->call(CategoryTableSeeder::class);
    }
}
