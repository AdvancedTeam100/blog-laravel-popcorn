<?php

namespace Database\Seeders;

use App\Models\Genre;
use Illuminate\Database\Seeder;

class GenreTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Genre::create([
            'name' => '投稿ルール(記事ジャンル)'
        ]);
        Genre::create([
            'name' => '基礎知識(記事ジャンル)'
        ]);
        Genre::create([
            'name' => '応用編(記事ジャンル)'
        ]);
        Genre::create([
            'name' => '健康管理(記事ジャンル)'
        ]);
        Genre::create([
            'name' => '目標設定(記事ジャンル)'
        ]);
        Genre::create([
            'name' => '労働寿命(記事ジャンル)'
        ]);
    }
}
