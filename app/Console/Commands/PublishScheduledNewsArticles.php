<?php

namespace App\Console\Commands;

use App\Enums\NewsArticleStatus;
use App\Models\NewsArticle;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('news:publish-scheduled')]
#[Description('Publish scheduled news articles whose publish date has arrived')]
class PublishScheduledNewsArticles extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        /// One bulk UPDATE, no models loaded into memory
        $count = NewsArticle::query()
            ->where('status', NewsArticleStatus::SCHEDULED->value)
            ->where('published_at', '<=', now())
            ->update(['status' => NewsArticleStatus::PUBLISHED->value]);

        $this->info("Published {$count} scheduled article(s).");

        return self::SUCCESS;
    }
}
