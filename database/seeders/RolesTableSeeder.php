<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Role::create([
            'role' =>1, 
            'name' => 'スパーアドミン'
        ]);

        Role::create([
            'role' =>2, 
            'name' => 'チームリーダ'
        ]);

        Role::create([
            'role' =>3, 
            'name' => 'ユーザー'
        ]);
    }
}
