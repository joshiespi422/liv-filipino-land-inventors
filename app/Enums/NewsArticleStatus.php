<?php

namespace App\Enums;

enum NewsArticleStatus: string
{
    case DRAFT = "draft";
    case SCHEDULED = "scheduled";
    case PUBLISHED = "published";
    case ARCHIVED = "archived";

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Draft',
            self::SCHEDULED => 'Scheduled',
            self::PUBLISHED => 'Published',
            self::ARCHIVED => 'Archived',
        };
    }
}
