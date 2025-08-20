<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Tags\Tag;

class TagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tags = [
            'Urgent',
            'Bulk',
            'GrpShp',
        ];

        foreach ($tags as $tag) {
            Tag::firstOrCreate([
                'name->en' => $tag
            ]);
        }
    }
}
