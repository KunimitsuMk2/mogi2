<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategoriesTableSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('categories')->insert([
            ['name' => 'ファッション'],
            ['name' => '家電'],
            ['name' => 'インテリア'],
            ['name' => 'レディース'],
            ['name' => 'メンズ'],
            ['name' => 'コスメ'],
            ['name' => '本／ゲーム'],
            ['name' => 'スポーツ'],
            ['name' => 'キッチン'],
            ['name' => 'ハンドメイド'],
            ['name' => 'アクセサリー'],
            ['name' => 'おもちゃ'],
            ['name' => 'ベビー・キッズ'],
        ]);
    }
}
