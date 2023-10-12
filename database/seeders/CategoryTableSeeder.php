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
            'name' => '全体基礎(１)(カテゴリ)'
        ]);
        Category::create([
            'name' => '全体応用(２)(カテゴリ)'
        ]);
        Category::create([
            'name' => '全体知識(３)(カテゴリ)'
        ]);
        Category::create([
            'name' => '全体技術(4)(カテゴリ)'
        ]);
    }
}
