<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DemoCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = ['Technical', 'Official', 'Content', 'Entertainment', 'Business'];

        foreach ($categories as $category) {
            Category::create(['name' => $category]);
        }
    }
}
