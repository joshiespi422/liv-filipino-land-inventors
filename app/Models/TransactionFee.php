<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransactionFee extends Model
{
    protected $fillable = [
        'module',
        'type',
        'value',
        'minimum_fee',
    ];

    protected $casts = [
        'value' => 'decimal:4',
        'minimum_fee' => 'decimal:4',
    ];

    public const TYPE_PERCENTAGE = 'Percentage';

    public const TYPE_PHP = 'PHP';

    public static function forModule(string $module): ?self
    {
        return static::where('module', $module)->first();
    }

    /**
     * Calculate the fee based on the configured type.
     * The calculated fee will never be lower than the minimum fee.
     */
    public function calculate(float $amount): float
    {
        if ($this->type === self::TYPE_PERCENTAGE) {
            $fee = $amount * ((float) $this->value / 100);
        } else {
            $fee = (float) $this->value;
        }

        return round(
            max($fee, (float) $this->minimum_fee),
            2
        );
    }
}
