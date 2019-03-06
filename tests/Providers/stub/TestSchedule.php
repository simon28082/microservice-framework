<?php

namespace CrCms\Microservice\Tests\tmp\modules\Testing\Schedules;

use Illuminate\Console\Scheduling\Schedule;
use CrCms\Microservice\Tests\tmp\modules\Testing\Commands\TestCommand;

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