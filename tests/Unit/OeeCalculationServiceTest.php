<?php

namespace Tests\Unit;

use App\Services\Oee\OeeCalculationService;
use PHPUnit\Framework\TestCase;

class OeeCalculationServiceTest extends TestCase
{
    private OeeCalculationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new OeeCalculationService();
    }

    public function test_availability_calculation(): void
    {
        // 420 run time out of 480 planned production time = 87.5%
        $availability = $this->service->calculateAvailability(420, 480);
        $this->assertEquals(87.5, round($availability, 2));
    }

    public function test_availability_zero_division(): void
    {
        $availability = $this->service->calculateAvailability(0, 0);
        $this->assertEquals(0.0, $availability);
    }

    public function test_performance_calculation(): void
    {
        // Ideal cycle time = 10 sec/unit, Total Qty = 2400 units, Run time = 420 mins (25200 sec)
        // Performance = (10 * 2400) / 25200 * 100 = 95.238%
        $performance = $this->service->calculatePerformance(10.0, 2400, 420);
        $this->assertEquals(95.24, round($performance, 2));
    }

    public function test_performance_zero_run_time(): void
    {
        $performance = $this->service->calculatePerformance(10.0, 100, 0);
        $this->assertEquals(0.0, $performance);
    }

    public function test_quality_calculation(): void
    {
        // Good Qty = 2350, Total Qty = 2400 => 97.916%
        $quality = $this->service->calculateQuality(2350, 2400);
        $this->assertEquals(97.92, round($quality, 2));
    }

    public function test_quality_zero_total_quantity(): void
    {
        $quality = $this->service->calculateQuality(0, 0);
        $this->assertEquals(100.0, $quality);
    }

    public function test_overall_oee_calculation(): void
    {
        // Availability = 92.35%, Performance = 88.42%, Quality = 98.15%
        // OEE = 0.9235 * 0.8842 * 0.9815 * 100 = 80.144%
        $oee = $this->service->calculateOee(92.35, 88.42, 98.15);
        $this->assertEquals(80.15, round($oee, 2));
    }

    public function test_status_evaluation(): void
    {
        $this->assertEquals('EXCELLENT', $this->service->evaluateStatus(86.5));
        $this->assertEquals('GOOD', $this->service->evaluateStatus(78.0));
        $this->assertEquals('WARNING', $this->service->evaluateStatus(65.0));
        $this->assertEquals('CRITICAL', $this->service->evaluateStatus(55.0));
    }

    public function test_mttr_and_mtbf_calculation(): void
    {
        $mttr = $this->service->calculateMttr(120.0, 3);
        $this->assertEquals(40.0, $mttr);

        $mtbf = $this->service->calculateMtbf(900.0, 3);
        $this->assertEquals(300.0, $mtbf);
    }
}
