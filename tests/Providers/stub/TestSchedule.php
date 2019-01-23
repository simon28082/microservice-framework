<?php

namespace CrCms\Microservice\Tests\modules\Support\Schedules;

use Illuminate\Console\Scheduling\Schedule;
use CrCms\Microservice\Tests\modules\Support\Commands\TestCommand;

/**
 * Class TestSchedule
 */
class TestSchedule
{
    public function handle(Schedule $schedule): void
    {
        $schedule->command(TestCommand::class)->daily();
    }
}