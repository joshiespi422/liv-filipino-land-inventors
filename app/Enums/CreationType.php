<?php

namespace App\Enums;

enum CreationType: string
{
    case BUSINESS_IDEA = "business_idea";
    case INVENTION = "invention";

    public function label(): string
    {
        return match ($this) {
            self::BUSINESS_IDEA => 'Business Idea',
            self::INVENTION => 'Invention',
        };
    }

    public static function options(): array
    {
        return array_map(fn($case) => [
            'value' => $case->value,
            'label' => $case->label(),
        ], self::cases());
    }
}
