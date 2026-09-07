<?php

namespace App\Enums;

enum OeeThreshold: string
{
    case EXCELLENT = 'EXCELLENT';
    case GOOD = 'GOOD';
    case WARNING = 'WARNING';
    case CRITICAL = 'CRITICAL';

    public static function evaluate(float $oeePercentage, float $excellentMin = 85.0, float $goodMin = 75.0, float $warningMin = 60.0): self
    {
        if ($oeePercentage >= $excellentMin) {
            return self::EXCELLENT;
        }
        if ($oeePercentage >= $goodMin) {
            return self::GOOD;
        }
        if ($oeePercentage >= $warningMin) {
            return self::WARNING;
        }
        return self::CRITICAL;
    }

    public function badgeColor(): string
    {
        return match($this) {
            self::EXCELLENT => 'bg-emerald-500/20 text-emerald-400 border-emerald-500/30',
            self::GOOD => 'bg-cyan-500/20 text-cyan-400 border-cyan-500/30',
            self::WARNING => 'bg-amber-500/20 text-amber-400 border-amber-500/30',
            self::CRITICAL => 'bg-rose-500/20 text-rose-400 border-rose-500/30',
        };
    }
}
