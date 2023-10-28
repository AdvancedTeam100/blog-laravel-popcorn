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
            'name' => '共通1',
            'user_id' => '1'
        ]);

        Group::create([
            'name' => '共通2',
            'user_id' => '1'
        ]);
    }
}
