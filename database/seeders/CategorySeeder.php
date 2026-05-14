<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => '仕事'],
            ['name' => 'プライベート'],
            ['name' => '学習'],
            ['name' => '買い物'],
            ['name' => '趣味'],
        ];

        foreach ($categories as $cat) {
            Category::updateOrCreate(['name' => $cat['name']], $cat);
        }
    }
}