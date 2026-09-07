<?php

namespace App\Enums;

enum MachineStatus: string
{
    case RUNNING = 'RUNNING';
    case STOPPED = 'STOPPED';
    case IDLE = 'IDLE';
    case BREAKDOWN = 'BREAKDOWN';
    case CHANGEOVER = 'CHANGEOVER';
    case MAINTENANCE = 'MAINTENANCE';
    case OFFLINE = 'OFFLINE';

    public function badgeColor(): string
    {
        return match($this) {
            self::RUNNING => 'bg-emerald-500/20 text-emerald-400 border-emerald-500/30',
            self::STOPPED, self::BREAKDOWN => 'bg-rose-500/20 text-rose-400 border-rose-500/30',
            self::IDLE => 'bg-amber-500/20 text-amber-400 border-amber-500/30',
            self::CHANGEOVER, self::MAINTENANCE => 'bg-purple-500/20 text-purple-400 border-purple-500/30',
            self::OFFLINE => 'bg-slate-500/20 text-slate-400 border-slate-500/30',
        };
    }
}
