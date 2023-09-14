<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Tag;
use Illuminate\Console\Command;

class FindUnusedTag extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'show:unused_tags';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Show tags which are unattached to cards';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info(Tag::unusedTagsQuery()->get(['id', 'title', 'color']));
    }
}
