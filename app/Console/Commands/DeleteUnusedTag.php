<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Tag;
use Illuminate\Console\Command;

class deleteUnusedTag extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'delete:unused_tags';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete tags which are not attached to cards';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $query = Tag::unusedTagsQuery();
        $uselessTags = $query->pluck('id');
        $count = count($uselessTags);

//progress bar and loop made for fun :)
        $bar = $this->output->createProgressBar($count);
        $bar->start();

        foreach ($uselessTags as $tag) {
            Tag::where('id', $tag)->delete();
            $bar->advance();
        }

        $bar->finish();
        $this->info(" Successfully deleted " . $count . ' useless tags!');
    }
}
