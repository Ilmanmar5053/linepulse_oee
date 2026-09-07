<?php

namespace Database\Seeders;

use App\Enums\MachineStatus;
use App\Enums\SixBigLoss;
use App\Models\Area;
use App\Models\AuditLog;
use App\Models\DailyProductionSummary;
use App\Models\DefectCategory;
use App\Models\DefectReason;
use App\Models\Department;
use App\Models\Downtime;
use App\Models\DowntimeCategory;
use App\Models\DowntimeReason;
use App\Models\Employee;
use App\Models\Machine;
use App\Models\MachineProduct;
use App\Models\MachineType;
use App\Models\OeeRecord;
use App\Models\Operator;
use App\Models\Permission;
use App\Models\Plant;
use App\Models\Process;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductionLine;
use App\Models\ProductionOrder;
use App\Models\ProductionPlan;
use App\Models\ProductionRecord;
use App\Models\QualityRecord;
use App\Models\Role;
use App\Models\Shift;
use App\Models\ShiftSummary;
use App\Models\SystemSetting;
use App\Models\User;
use App\Models\WorkCenter;
use App\Services\Oee\OeeCalculationService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $oeeService = new OeeCalculationService();

        // 1. SYSTEM SETTINGS
        SystemSetting::set('target_oee', '85.0', 'oee_targets', 'World Class OEE Target (%)');
        SystemSetting::set('target_availability', '90.0', 'oee_targets', 'World Class Availability Target (%)');
        SystemSetting::set('target_performance', '95.0', 'oee_targets', 'World Class Performance Target (%)');
        SystemSetting::set('target_quality', '99.0', 'oee_targets', 'World Class Quality Target (%)');
        SystemSetting::set('auto_refresh_interval', '30', 'tv_mode', 'TV Floor Mode Auto Refresh Interval (seconds)');

        // 2. ROLES & PERMISSIONS
        $roles = [
            'super_admin' => 'Super Admin',
            'admin' => 'Admin',
            'production_manager' => 'Production Manager',
            'production_supervisor' => 'Production Supervisor',
            'production_leader' => 'Production Leader',
            'operator' => 'Operator',
            'quality_control' => 'Quality Control',
            'maintenance' => 'Maintenance',
            'management' => 'Management / Viewer',
        ];

        $roleModels = [];
        foreach ($roles as $key => $displayName) {
            $roleModels[$key] = Role::create([
                'name' => $key,
                'display_name' => $displayName,
                'description' => "Role for {$displayName}",
            ]);
        }

        $permissions = [
            'view_dashboard', 'export_reports',
            'manage_master_data', 'manage_users',
            'create_production', 'edit_production', 'approve_production',
            'create_downtime', 'edit_downtime',
            'create_quality', 'edit_quality',
        ];

        foreach ($permissions as $p) {
            Permission::create(['name' => $p, 'module' => explode('_', $p)[1] ?? 'general']);
        }

        // Attach all permissions to super_admin & admin
        $allPermissions = Permission::all();
        $roleModels['super_admin']->permissions()->sync($allPermissions);
        $roleModels['admin']->permissions()->sync($allPermissions);

        // Management only view permissions
        $roleModels['management']->permissions()->sync(
            Permission::whereIn('name', ['view_dashboard', 'export_reports'])->get()
        );

        // 3. USERS
        $adminUser = User::create([
            'name' => 'Super Administrator',
            'email' => 'admin@oeesys.com',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);
        $adminUser->roles()->attach($roleModels['super_admin']);

        $managerUser = User::create([
            'name' => 'Plant Manager',
            'email' => 'manager@oeesys.com',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);
        $managerUser->roles()->attach($roleModels['production_manager']);

        $operatorUser = User::create([
            'name' => 'Lead Operator',
            'email' => 'operator@oeesys.com',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);
        $operatorUser->roles()->attach($roleModels['operator']);

        // 4. PLANTS, AREAS, DEPARTMENTS
        $plants = [];
        for ($p = 1; $p <= 3; $p++) {
            $plants[] = Plant::create([
                'code' => "PLANT-0{$p}",
                'name' => "Manufacturing Plant {$p}",
                'address' => "Industrial Estate Block B{$p}, Jakarta",
                'timezone' => 'Asia/Jakarta',
                'is_active' => true,
            ]);
        }

        $mainPlant = $plants[0];

        $deptProd = Department::create(['plant_id' => $mainPlant->id, 'code' => 'DEPT-PRD', 'name' => 'Production Department']);
        $deptMaint = Department::create(['plant_id' => $mainPlant->id, 'code' => 'DEPT-MNT', 'name' => 'Maintenance Department']);
        $deptQc = Department::create(['plant_id' => $mainPlant->id, 'code' => 'DEPT-QC', 'name' => 'Quality Control Department']);

        $areas = [];
        $areaCodes = ['ASSEMBLY', 'MACHINING', 'PACKAGING'];
        foreach ($areaCodes as $idx => $ac) {
            $areas[] = Area::create([
                'plant_id' => $mainPlant->id,
                'code' => $ac,
                'name' => ucfirst(strtolower($ac)) . ' Area',
                'is_active' => true,
            ]);
        }

        // 5. SHIFTS
        $shifts = [
            Shift::create(['plant_id' => $mainPlant->id, 'name' => 'SHIFT 1', 'start_time' => '07:00:00', 'end_time' => '15:00:00', 'break_duration_minutes' => 60]),
            Shift::create(['plant_id' => $mainPlant->id, 'name' => 'SHIFT 2', 'start_time' => '15:00:00', 'end_time' => '23:00:00', 'break_duration_minutes' => 60]),
            Shift::create(['plant_id' => $mainPlant->id, 'name' => 'SHIFT 3', 'start_time' => '23:00:00', 'end_time' => '07:00:00', 'break_duration_minutes' => 60]),
        ];

        // 6. PRODUCTION LINES (5 lines: LINE-A .. LINE-E)
        $lines = [];
        $lineNames = ['LINE-A', 'LINE-B', 'LINE-C', 'LINE-D', 'LINE-E'];
        foreach ($lineNames as $idx => $lname) {
            $lines[] = ProductionLine::create([
                'area_id' => $areas[$idx % count($areas)]->id,
                'code' => $lname,
                'name' => "Production Line {$lname}",
                'target_oee' => 85.00,
                'is_active' => true,
            ]);
        }

        // 7. WORK CENTERS & MACHINE TYPES
        $mTypeStamping = MachineType::create(['name' => 'Stamping Press', 'description' => 'Heavy metal stamping']);
        $mTypeCnc = MachineType::create(['name' => 'CNC Milling', 'description' => 'Precision machining']);
        $mTypeAssembly = MachineType::create(['name' => 'Robotic Assembler', 'description' => 'Automated pick & place']);
        $mTypePackaging = MachineType::create(['name' => 'Carton Packer', 'description' => 'Automatic packaging']);

        $machineTypes = [$mTypeStamping, $mTypeCnc, $mTypeAssembly, $mTypePackaging];

        $workCenters = [];
        foreach ($lines as $line) {
            $workCenters[] = WorkCenter::create([
                'production_line_id' => $line->id,
                'code' => "WC-{$line->code}",
                'name' => "Work Center {$line->name}",
            ]);
        }

        // 8. MACHINES (20 Machines: MC-001 .. MC-020)
        $machines = [];
        for ($m = 1; $m <= 20; $m++) {
            $codeStr = sprintf("MC-%03d", $m);
            $wc = $workCenters[($m - 1) % count($workCenters)];
            $mt = $machineTypes[($m - 1) % count($machineTypes)];

            $status = match($m % 5) {
                0 => MachineStatus::RUNNING,
                1 => MachineStatus::RUNNING,
                2 => MachineStatus::IDLE,
                3 => MachineStatus::BREAKDOWN,
                default => MachineStatus::RUNNING,
            };

            $machines[] = Machine::create([
                'work_center_id' => $wc->id,
                'machine_type_id' => $mt->id,
                'code' => $codeStr,
                'name' => "Industrial Machine {$codeStr}",
                'serial_number' => "SN-2024-{$codeStr}",
                'installation_date' => '2023-01-15',
                'status' => $status,
                'is_active' => true,
            ]);
        }

        // 9. PRODUCTS (10 Products: PRODUCT-A .. PRODUCT-J)
        $catAuto = ProductCategory::create(['name' => 'Automotive Components', 'description' => 'Precision metal parts']);
        $catElec = ProductCategory::create(['name' => 'Electronics Housing', 'description' => 'Plastic & metal enclosures']);

        $products = [];
        $productLetters = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J'];
        foreach ($productLetters as $idx => $letter) {
            $products[] = Product::create([
                'product_category_id' => ($idx % 2 === 0) ? $catAuto->id : $catElec->id,
                'sku' => "PRODUCT-{$letter}",
                'name' => "Engine Part Model {$letter}",
                'unit_of_measure' => 'PCS',
                'ideal_cycle_time' => 8.0 + ($idx * 0.5), // 8.0s to 12.5s per unit
            ]);
        }

        $processObj = Process::create(['code' => 'PROC-STAMP', 'name' => 'Main Stamping & Finishing']);

        // Link machines & products
        foreach ($machines as $mKey => $mObj) {
            $pObj = $products[$mKey % count($products)];
            $mObj->products()->attach($pObj->id, [
                'process_id' => $processObj->id,
                'specific_ideal_cycle_time' => $pObj->ideal_cycle_time,
            ]);
        }

        // 10. EMPLOYEES & OPERATORS (50 Operators)
        $operators = [];
        for ($e = 1; $e <= 50; $e++) {
            $emp = Employee::create([
                'department_id' => $deptProd->id,
                'nik' => sprintf("EMP-%04d", $e),
                'name' => "Operator Staff {$e}",
                'email' => "operator{$e}@oeesys.com",
            ]);

            $operators[] = Operator::create([
                'employee_id' => $emp->id,
                'badge_number' => sprintf("OP-BADGE-%03d", $e),
                'skill_level' => ($e % 3 === 0) ? 'EXPERT' : 'INTERMEDIATE',
            ]);
        }

        // 11. DOWNTIME CATEGORIES & REASONS
        $catPlanned = DowntimeCategory::create(['code' => 'PLANNED', 'name' => 'Planned Downtime', 'is_planned' => true]);
        $catUnplanned = DowntimeCategory::create(['code' => 'UNPLANNED', 'name' => 'Unplanned Breakdown', 'is_planned' => false]);

        $downtimeReasons = [
            DowntimeReason::create([
                'downtime_category_id' => $catPlanned->id,
                'six_big_loss_category' => SixBigLoss::SETUP_ADJUSTMENT->value,
                'code' => 'DT-CHANGE', 'name' => 'Die & Mold Changeover',
            ]),
            DowntimeReason::create([
                'downtime_category_id' => $catPlanned->id,
                'six_big_loss_category' => SixBigLoss::SETUP_ADJUSTMENT->value,
                'code' => 'DT-CLEAN', 'name' => 'Scheduled Cleaning & Lubrication',
            ]),
            DowntimeReason::create([
                'downtime_category_id' => $catUnplanned->id,
                'six_big_loss_category' => SixBigLoss::EQUIPMENT_FAILURE->value,
                'code' => 'DT-MECH', 'name' => 'Mechanical Jam & Motor Overheat',
            ]),
            DowntimeReason::create([
                'downtime_category_id' => $catUnplanned->id,
                'six_big_loss_category' => SixBigLoss::EQUIPMENT_FAILURE->value,
                'code' => 'DT-ELEC', 'name' => 'Electrical Sensor & PLC Failure',
            ]),
            DowntimeReason::create([
                'downtime_category_id' => $catUnplanned->id,
                'six_big_loss_category' => SixBigLoss::IDLING_MINOR_STOP->value,
                'code' => 'DT-MINOR', 'name' => 'Part Misfeed & Minor Jam (< 5m)',
            ]),
            DowntimeReason::create([
                'downtime_category_id' => $catUnplanned->id,
                'six_big_loss_category' => SixBigLoss::EQUIPMENT_FAILURE->value,
                'code' => 'DT-MAT', 'name' => 'Waiting Raw Material Delivery',
            ]),
        ];

        // 12. DEFECT CATEGORIES & REASONS
        $catDefectDimensional = DefectCategory::create(['code' => 'DIMENSIONAL', 'name' => 'Dimensional & Geometry']);
        $catDefectSurface = DefectCategory::create(['code' => 'SURFACE', 'name' => 'Surface Finish & Cosmetics']);

        $defectReasons = [
            DefectReason::create(['defect_category_id' => $catDefectDimensional->id, 'code' => 'DEF-SIZE', 'name' => 'Out of Tolerance Thickness']),
            DefectReason::create(['defect_category_id' => $catDefectDimensional->id, 'code' => 'DEF-WARP', 'name' => 'Part Deformation & Warpage']),
            DefectReason::create(['defect_category_id' => $catDefectSurface->id, 'code' => 'DEF-SCRATCH', 'name' => 'Surface Scratch & Dent']),
            DefectReason::create(['defect_category_id' => $catDefectSurface->id, 'code' => 'DEF-BURR', 'name' => 'Excess Burr & Rough Edges']),
        ];

        // 13. GENERATE 30 DAYS OF HISTORICAL PRODUCTION DATA
        $startDate = Carbon::now()->subDays(30);
        $endDate = Carbon::now();

        $planNumber = 1000;
        $orderNumber = 5000;

        for ($date = clone $startDate; $date->lte($endDate); $date->addDay()) {
            $dateStr = $date->toDateString();

            foreach ($lines as $lineIdx => $line) {
                // Create dummy plan and order for the line day
                $plan = ProductionPlan::create([
                    'production_line_id' => $line->id,
                    'plan_number' => "PLN-{$planNumber}",
                    'start_date' => $dateStr,
                    'end_date' => $dateStr,
                    'status' => 'COMPLETED',
                ]);
                $planNumber++;

                $product = $products[$lineIdx % count($products)];

                $order = ProductionOrder::create([
                    'production_plan_id' => $plan->id,
                    'product_id' => $product->id,
                    'order_number' => "ORD-{$orderNumber}",
                    'target_quantity' => 7200,
                    'due_date' => $dateStr,
                    'status' => 'COMPLETED',
                ]);
                $orderNumber++;

                // Line machines
                $lineMachines = $line->workCenters->flatMap->machines;
                if ($lineMachines->isEmpty()) {
                    $lineMachines = collect([$machines[$lineIdx % count($machines)]]);
                }

                foreach ($shifts as $shiftIdx => $shift) {
                    $shiftTarget = 0;
                    $shiftActual = 0;
                    $shiftGood = 0;
                    $shiftReject = 0;

                    $shiftRunTimes = 0;
                    $shiftPlannedTimes = 0;

                    foreach ($lineMachines as $machine) {
                        $operator = $operators[rand(0, count($operators) - 1)];

                        // Planned Production Time: 480 mins total, 60 mins break => 420 mins available
                        $plannedProdTime = 480;
                        $plannedDowntime = 60;
                        $availableTime = 420;

                        // Randomize unplanned downtime & idle
                        $unplannedDowntime = rand(10, 50); // minutes
                        $idleTime = rand(5, 20); // minutes
                        $runTime = max(60, $availableTime - $unplannedDowntime - $idleTime);

                        $idealCycleTime = $product->ideal_cycle_time; // e.g. 10 sec/unit

                        // Calculate target & actual quantities
                        $targetQty = (int) floor(($availableTime * 60) / $idealCycleTime);
                        
                        // Performance variation (80% to 98%)
                        $perfFactor = (rand(80, 98) / 100.0);
                        $totalQty = (int) floor(($runTime * 60 / $idealCycleTime) * $perfFactor);

                        // Quality variation (95% to 99.5%)
                        $qualFactor = (rand(950, 995) / 1000.0);
                        $goodQty = (int) floor($totalQty * $qualFactor);
                        $rejectQty = max(0, $totalQty - $goodQty);
                        $scrapQty = (int) floor($rejectQty * 0.3);

                        $prodRate = ($runTime > 0) ? round(($goodQty / ($runTime / 60.0)), 2) : 0;
                        $actualCycleTime = ($totalQty > 0) ? round(($runTime * 60.0) / $totalQty, 4) : $idealCycleTime;

                        $prodRecord = ProductionRecord::create([
                            'production_order_id' => $order->id,
                            'production_line_id' => $line->id,
                            'machine_id' => $machine->id,
                            'product_id' => $product->id,
                            'shift_id' => $shift->id,
                            'operator_id' => $operator->id,
                            'production_date' => $dateStr,
                            'planned_production_time' => $plannedProdTime,
                            'planned_downtime' => $plannedDowntime,
                            'available_production_time' => $availableTime,
                            'run_time' => $runTime,
                            'downtime' => $unplannedDowntime,
                            'idle_time' => $idleTime,
                            'ideal_cycle_time' => $idealCycleTime,
                            'actual_cycle_time' => $actualCycleTime,
                            'target_quantity' => $targetQty,
                            'total_quantity' => $totalQty,
                            'good_quantity' => $goodQty,
                            'reject_quantity' => $rejectQty,
                            'scrap_quantity' => $scrapQty,
                            'production_rate' => $prodRate,
                            'status' => 'COMPLETED',
                        ]);

                        // Record Downtimes
                        $downtimeReason = $downtimeReasons[rand(0, count($downtimeReasons) - 1)];
                        $dtStart = Carbon::parse("{$dateStr} 08:30:00")->addMinutes(rand(0, 180));
                        $dtEnd = (clone $dtStart)->addMinutes($unplannedDowntime);

                        Downtime::create([
                            'production_record_id' => $prodRecord->id,
                            'machine_id' => $machine->id,
                            'production_line_id' => $line->id,
                            'start_time' => $dtStart,
                            'end_time' => $dtEnd,
                            'duration_minutes' => $unplannedDowntime,
                            'downtime_category_id' => $downtimeReason->downtime_category_id,
                            'downtime_reason_id' => $downtimeReason->id,
                            'description' => "Automatic breakdown record: {$downtimeReason->name}",
                            'is_planned' => $downtimeReason->downtimeCategory->is_planned,
                            'created_by' => $adminUser->id,
                        ]);

                        // Record Quality Defect
                        if ($rejectQty > 0) {
                            $defectReason = $defectReasons[rand(0, count($defectReasons) - 1)];
                            QualityRecord::create([
                                'production_record_id' => $prodRecord->id,
                                'machine_id' => $machine->id,
                                'product_id' => $product->id,
                                'total_quantity' => $totalQty,
                                'good_quantity' => $goodQty,
                                'reject_quantity' => $rejectQty,
                                'rework_quantity' => max(0, $rejectQty - $scrapQty),
                                'scrap_quantity' => $scrapQty,
                                'defect_category_id' => $defectReason->defect_category_id,
                                'defect_reason_id' => $defectReason->id,
                                'inspection_time' => Carbon::parse("{$dateStr} 14:00:00"),
                                'inspector_id' => $adminUser->id,
                                'notes' => "Routine QC Inspection: {$defectReason->name}",
                            ]);
                        }

                        // Compute OEE Record
                        $availability = $oeeService->calculateAvailability($runTime, $availableTime);
                        $performance = $oeeService->calculatePerformance($idealCycleTime, $totalQty, $runTime);
                        $quality = $oeeService->calculateQuality($goodQty, $totalQty);
                        $oee = $oeeService->calculateOee($availability, $performance, $quality);

                        $sixLosses = $oeeService->calculateSixBigLosses(
                            $availableTime,
                            $runTime,
                            $idealCycleTime,
                            $totalQty,
                            $rejectQty,
                            $scrapQty,
                            ($downtimeReason->six_big_loss_category === SixBigLoss::EQUIPMENT_FAILURE) ? $unplannedDowntime : 0,
                            ($downtimeReason->six_big_loss_category === SixBigLoss::SETUP_ADJUSTMENT) ? $unplannedDowntime : 0,
                            ($downtimeReason->six_big_loss_category === SixBigLoss::IDLING_MINOR_STOP) ? $idleTime : 0
                        );

                        OeeRecord::create([
                            'production_record_id' => $prodRecord->id,
                            'machine_id' => $machine->id,
                            'production_line_id' => $line->id,
                            'shift_id' => $shift->id,
                            'record_date' => $dateStr,
                            'availability' => $availability,
                            'performance' => $performance,
                            'quality' => $quality,
                            'oee' => $oee,
                            'six_big_losses_summary' => $sixLosses,
                        ]);

                        // Accumulate shift summaries
                        $shiftTarget += $targetQty;
                        $shiftActual += $totalQty;
                        $shiftGood += $goodQty;
                        $shiftReject += $rejectQty;
                        $shiftRunTimes += $runTime;
                        $shiftPlannedTimes += $availableTime;
                    }

                    // Save Shift Summary
                    $shiftAvail = $oeeService->calculateAvailability($shiftRunTimes, $shiftPlannedTimes);
                    $shiftPerf = ($shiftRunTimes > 0 && $shiftActual > 0) ? round(($shiftActual / (($shiftRunTimes * 60) / 10.0)) * 100.0, 4) : 0;
                    $shiftQual = $oeeService->calculateQuality($shiftGood, $shiftActual);
                    $shiftOee = $oeeService->calculateOee($shiftAvail, $shiftPerf, $shiftQual);

                    ShiftSummary::create([
                        'production_line_id' => $line->id,
                        'shift_id' => $shift->id,
                        'summary_date' => $dateStr,
                        'total_target_qty' => $shiftTarget,
                        'total_actual_qty' => $shiftActual,
                        'total_good_qty' => $shiftGood,
                        'total_reject_qty' => $shiftReject,
                        'availability' => $shiftAvail,
                        'performance' => $shiftPerf,
                        'quality' => $shiftQual,
                        'oee' => $shiftOee,
                    ]);
                }

                // Save Daily Summary for the Line
                $lineDailyRecords = OeeRecord::where('production_line_id', $line->id)
                    ->where('record_date', $dateStr)
                    ->get();

                if ($lineDailyRecords->isNotEmpty()) {
                    $avgAvail = $lineDailyRecords->avg('availability');
                    $avgPerf = $lineDailyRecords->avg('performance');
                    $avgQual = $lineDailyRecords->avg('quality');
                    $avgOee = $oeeService->calculateOee($avgAvail, $avgPerf, $avgQual);

                    $dailyProdRecords = ProductionRecord::where('production_line_id', $line->id)
                        ->where('production_date', $dateStr)
                        ->get();

                    DailyProductionSummary::create([
                        'production_line_id' => $line->id,
                        'summary_date' => $dateStr,
                        'total_target_qty' => $dailyProdRecords->sum('target_quantity'),
                        'total_actual_qty' => $dailyProdRecords->sum('total_quantity'),
                        'total_good_qty' => $dailyProdRecords->sum('good_quantity'),
                        'total_reject_qty' => $dailyProdRecords->sum('reject_quantity'),
                        'availability' => $avgAvail,
                        'performance' => $avgPerf,
                        'quality' => $avgQual,
                        'oee' => $avgOee,
                    ]);
                }
            }
        }

        // AUDIT LOG SEED
        AuditLog::create([
            'user_id' => $adminUser->id,
            'action' => 'INITIAL_SYSTEM_SEED',
            'module' => 'SYSTEM',
            'record_id' => '1',
            'new_value' => ['status' => 'SEED_COMPLETED_SUCCESSFULLY'],
            'ip_address' => '127.0.0.1',
        ]);
    }
}
