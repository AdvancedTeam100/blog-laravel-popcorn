<?php

namespace Database\Seeders;

use App\Models\Group;
use App\Models\Genre;

use Illuminate\Database\Seeder;

class GroupTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Group::create([
            'name' => '共通グループ1'
        ]);
    }
}
