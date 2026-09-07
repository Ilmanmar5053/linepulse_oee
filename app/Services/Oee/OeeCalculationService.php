<?php

namespace App\Services\Oee;

use App\Enums\OeeThreshold;
use App\Enums\SixBigLoss;

class OeeCalculationService
{
    /**
     * Calculate Availability (%)
     * Availability = (Run Time / Planned Production Time) * 100
     */
    public function calculateAvailability(float $runTimeMinutes, float $plannedProductionTimeMinutes): float
    {
        if ($plannedProductionTimeMinutes <= 0 || $runTimeMinutes < 0) {
            return 0.0;
        }

        $availability = ($runTimeMinutes / $plannedProductionTimeMinutes) * 100.0;

        return min(100.0, max(0.0, $availability));
    }

    /**
     * Calculate Performance (%)
     * Performance = ((Ideal Cycle Time * Total Quantity) / Run Time in Seconds) * 100
     */
    public function calculatePerformance(float $idealCycleTimeSeconds, int $totalQuantity, float $runTimeMinutes): float
    {
        $runTimeSeconds = $runTimeMinutes * 60.0;

        if ($runTimeSeconds <= 0 || $totalQuantity <= 0 || $idealCycleTimeSeconds <= 0) {
            return 0.0;
        }

        $performance = (($idealCycleTimeSeconds * $totalQuantity) / $runTimeSeconds) * 100.0;

        return max(0.0, $performance);
    }

    /**
     * Calculate Quality (%)
     * Quality = (Good Quantity / Total Quantity) * 100
     */
    public function calculateQuality(int $goodQuantity, int $totalQuantity): float
    {
        if ($totalQuantity <= 0) {
            return 100.0;
        }

        if ($goodQuantity < 0) {
            return 0.0;
        }

        $quality = ($goodQuantity / $totalQuantity) * 100.0;

        return min(100.0, max(0.0, $quality));
    }

    /**
     * Calculate Overall Equipment Effectiveness (OEE %)
     * OEE = Availability * Performance * Quality
     */
    public function calculateOee(float $availabilityPercentage, float $performancePercentage, float $qualityPercentage): float
    {
        $aDecimal = $availabilityPercentage / 100.0;
        $pDecimal = $performancePercentage / 100.0;
        $qDecimal = $qualityPercentage / 100.0;

        $oee = $aDecimal * $pDecimal * $qDecimal * 100.0;

        return max(0.0, $oee);
    }

    /**
     * Evaluate OEE Status Threshold (Excellent, Good, Warning, Critical)
     */
    public function evaluateStatus(float $oeePercentage, float $excellentThreshold = 85.0, float $goodThreshold = 75.0, float $warningThreshold = 60.0): string
    {
        return OeeThreshold::evaluate($oeePercentage, $excellentThreshold, $goodThreshold, $warningThreshold)->value;
    }

    /**
     * Calculate Six Big Losses breakdown (in minutes)
     */
    public function calculateSixBigLosses(
        float $plannedProductionTimeMinutes,
        float $runTimeMinutes,
        float $idealCycleTimeSeconds,
        int $totalQuantity,
        int $rejectQuantity,
        int $scrapQuantity,
        float $equipmentFailureMinutes = 0.0,
        float $setupAdjustmentMinutes = 0.0,
        float $idlingMinorStopMinutes = 0.0
    ): array {
        // Reduced speed loss in minutes: Time spent producing units beyond ideal cycle time
        $idealRunTimeMinutes = ($idealCycleTimeSeconds * $totalQuantity) / 60.0;
        $reducedSpeedMinutes = max(0.0, $runTimeMinutes - $idealRunTimeMinutes);

        // Process defect loss in minutes
        $processDefectsMinutes = ($rejectQuantity * $idealCycleTimeSeconds) / 60.0;

        // Reduced yield loss in minutes
        $reducedYieldMinutes = ($scrapQuantity * $idealCycleTimeSeconds) / 60.0;

        return [
            SixBigLoss::EQUIPMENT_FAILURE->value => round($equipmentFailureMinutes, 2),
            SixBigLoss::SETUP_ADJUSTMENT->value => round($setupAdjustmentMinutes, 2),
            SixBigLoss::IDLING_MINOR_STOP->value => round($idlingMinorStopMinutes, 2),
            SixBigLoss::REDUCED_SPEED->value => round($reducedSpeedMinutes, 2),
            SixBigLoss::PROCESS_DEFECTS->value => round($processDefectsMinutes, 2),
            SixBigLoss::REDUCED_YIELD->value => round($reducedYieldMinutes, 2),
        ];
    }

    /**
     * Calculate Mean Time To Repair (MTTR) in minutes
     * MTTR = Total Breakdown Downtime / Total Breakdown Count
     */
    public function calculateMttr(float $totalBreakdownMinutes, int $breakdownCount): float
    {
        if ($breakdownCount <= 0) {
            return 0.0;
        }
        return round($totalBreakdownMinutes / $breakdownCount, 2);
    }

    /**
     * Calculate Mean Time Between Failures (MTBF) in minutes
     * MTBF = Total Operating Run Time / Total Breakdown Count
     */
    public function calculateMtbf(float $totalRunTimeMinutes, int $breakdownCount): float
    {
        if ($breakdownCount <= 0) {
            return round($totalRunTimeMinutes, 2);
        }
        return round($totalRunTimeMinutes / $breakdownCount, 2);
    }
}
