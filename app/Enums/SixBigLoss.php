<?php

namespace App\Enums;

enum SixBigLoss: string
{
    case EQUIPMENT_FAILURE = 'EQUIPMENT_FAILURE';
    case SETUP_ADJUSTMENT = 'SETUP_ADJUSTMENT';
    case IDLING_MINOR_STOP = 'IDLING_MINOR_STOP';
    case REDUCED_SPEED = 'REDUCED_SPEED';
    case PROCESS_DEFECTS = 'PROCESS_DEFECTS';
    case REDUCED_YIELD = 'REDUCED_YIELD';

    public function label(): string
    {
        return match($this) {
            self::EQUIPMENT_FAILURE => 'Equipment Failure (Breakdown)',
            self::SETUP_ADJUSTMENT => 'Setup & Adjustment',
            self::IDLING_MINOR_STOP => 'Idling & Minor Stops',
            self::REDUCED_SPEED => 'Reduced Speed',
            self::PROCESS_DEFECTS => 'Process Defects (Rejects)',
            self::REDUCED_YIELD => 'Reduced Yield (Scrap)',
        };
    }
}
