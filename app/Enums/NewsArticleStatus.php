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

    /** @return array<self> */
    public static function creatable(): array
    {
        return [self::DRAFT, self::SCHEDULED, self::PUBLISHED];
    }

    /** Archived articles are frozen; everything else can have its content edited. */
    public function isEditable(): bool
    {
        return $this !== self::ARCHIVED;
    }

    /** A live article must be archived before it can be permanently deleted. */
    public function isDeletable(): bool
    {
        return $this !== self::PUBLISHED;
    }

    /** @return array<self> statuses this one may move to (the first is the default choice) */
    public function transitions(): array
    {
        return match ($this) {
            self::DRAFT => [self::SCHEDULED, self::PUBLISHED],
            self::SCHEDULED => [self::SCHEDULED, self::DRAFT, self::PUBLISHED], // first = reschedule
            self::PUBLISHED => [self::ARCHIVED],
            self::ARCHIVED => [self::PUBLISHED], // restore
        };
    }
}
