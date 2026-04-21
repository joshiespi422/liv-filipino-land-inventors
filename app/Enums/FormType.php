<?php

namespace App\Enums;

enum FormType: string
{
    case PAYMENT = "payment";
    case GRANT = "grant";

    public function label(): string
    {
        return match ($this) {
            self::PAYMENT => 'Payment',
            self::GRANT => 'Grant',
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
