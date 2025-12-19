<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $defaults = [
            'F&B',
            'Tagihan',
            'Bensin',
            'Kuota',
        ];

        foreach ($defaults as $name) {
            Category::firstOrCreate(
                [
                // Global default category (bisa dipakai semua user)
                    'user_id' => null,
                    'slug' => Str::slug($name),
                ],
                [
                    'name' => $name,
                ]
            );
        }
    }
}


