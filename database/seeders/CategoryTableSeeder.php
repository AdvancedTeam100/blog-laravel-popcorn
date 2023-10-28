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
            'name' => '共通1_ステップ1',
            'group_id' => '1',
            'user_id' => '1'
        ]);

        Category::create([
            'name' => '共通1_ステップ2',
            'group_id' => '1',
            'user_id' => '1'
        ]);

        Category::create([
            'name' => '共通1_ステップ3',
            'group_id' => '1',
            'user_id' => '1'
        ]);

        Category::create([
            'name' => '共通2_ステップ1',
            'group_id' => '2',
            'user_id' => '1'
        ]);

        Category::create([
            'name' => '共通2_ステップ2',
            'group_id' => '2',
            'user_id' => '1'
        ]);

        Category::create([
            'name' => '共通2_ステップ3',
            'group_id' => '2',
            'user_id' => '1'
        ]);
    }
}
