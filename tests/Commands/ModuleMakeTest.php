<?php

/**
 * @author simon <crcms@crcms.cn>
 * @datetime 2019-03-06 21:22
 *
 * @link http://crcms.cn/
 *
 * @copyright Copyright &copy; 2019 Rights Reserved CRCMS
 */

namespace CrCms\Microservice\Tests\Commands;

use PHPUnit\Framework\TestCase;
use Illuminate\Console\OutputStyle;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Console\Input\ArgvInput;
use Illuminate\Contracts\Foundation\Application;
use Symfony\Component\Console\Output\ConsoleOutput;
use CrCms\Microservice\Console\Commands\ModuleMakeCommand;

class ModuleMakeTest extends TestCase
{
    public function testCreateModule()
    {
        $input = new ArgvInput(['shell' => 'abc', 'name' => 'abc']);
        $output = new ConsoleOutput();
        $command = new ModuleMakeCommand(new Filesystem());
        $laravel = \Mockery::mock(Application::class);
        $laravel->shouldReceive('make')->andReturn(new OutputStyle($input, $output));
        $laravel->shouldReceive('call')->andReturn($command->handle());

        $command->setLaravel($laravel);
//        $command->handle();

        $command->run($input, $output);
    }
}
