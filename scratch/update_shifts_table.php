<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Shift;

$shiftsData = [
    ['id' => 1, 'name' => 'SHIFT 1', 'start_time' => '07:30:00', 'end_time' => '16:30:00', 'break_duration_minutes' => 60],
    ['id' => 2, 'name' => 'SHIFT 2', 'start_time' => '16:10:00', 'end_time' => '00:10:00', 'break_duration_minutes' => 60],
    ['id' => 3, 'name' => 'SHIFT 3', 'start_time' => '23:50:00', 'end_time' => '07:50:00', 'break_duration_minutes' => 60],
];

foreach ($shiftsData as $sd) {
    Shift::updateOrCreate(
        ['id' => $sd['id']],
        [
            'name' => $sd['name'],
            'start_time' => $sd['start_time'],
            'end_time' => $sd['end_time'],
            'break_duration_minutes' => $sd['break_duration_minutes'],
        ]
    );
}

echo "Shifts updated successfully!\n";
foreach (Shift::all() as $s) {
    echo "ID: {$s->id} | {$s->name} | Start: {$s->start_time} | End: {$s->end_time} | Break: {$s->break_duration_minutes}m\n";
}
