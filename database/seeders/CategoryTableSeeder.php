<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategoryTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *  
     * @return void
     */
    public function run()
    {
        Category::create([
            'name' => '共通グループ1',
            'group_id' => 1,
        ]);

        Category::create([
            'name' => '共通グループ2',
            'group_id' => 1,
        ]);

        Category::create([
            'name' => '共通グループ3',
            'group_id' => 1,
        ]);
    }
}
