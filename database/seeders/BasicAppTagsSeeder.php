<?php

namespace Database\Seeders;

use App\Models\Tag;
use Illuminate\Database\Seeder;

class BasicAppTagsSeeder extends Seeder
{

    public function run(): void
    {
        foreach (Tag::$basicAppTags as $tag) {
            Tag::create($tag);
        }
    }
}
