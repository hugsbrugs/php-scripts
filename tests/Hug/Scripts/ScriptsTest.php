<?php

# For PHP7
// declare(strict_types=1);

// namespace Hug\Tests\Scripts;

use PHPUnit\Framework\TestCase;

use Hug\Scripts\Scripts as Scripts;

/**
 *
 */
final class ScriptsTest extends TestCase
{    

    /* ************************************************* */
    /* ****************** Scripts::run ***************** */
    /* ************************************************* */

    /**
     *
     */
    public function testCanRun()
    {
        $cmd = 'ls -lsa';
        $log_file = __DIR__ . '/test.log';
        $test = Scripts::run($cmd, $log_file);
        $this->assertIsArray($test);
        $this->assertEquals('success', $test['status']);
    }

    /* ************************************************* */
    /* *************** Scripts::is_running ************* */
    /* ************************************************* */

    /**
     *
     */
    public function testCanIsRunning()
    {
        $cmd = 'ls -lsa';
        $log_file = __DIR__ . '/test.log';
        $res = Scripts::run($cmd, $log_file);

        $test = Scripts::is_running($res['data']['pid']);
        $this->assertTrue($test);
        sleep(1);
        $test = Scripts::is_running($res['data']['pid']);
        $this->assertFalse($test);
    }

    /* ************************************************* */
    /* ************ Scripts::get_pid_cpu_mem *********** */
    /* ************************************************* */

    /**
     *
     */
    public function testCanGetPidCpuMem()
    {
        $log_file = __DIR__ . '/test.log';
        $cmd = 'tail -f ' . $log_file;
        $res = Scripts::run($cmd, $log_file);

        $test = Scripts::get_pid_cpu_mem($res['data']['pid']);
        $this->assertIsArray($test);

        $killed = Scripts::kill($res['data']['pid']);
        $this->assertTrue($killed);
    }

    /* ************************************************* */
    /* ***************** Scripts::kill ***************** */
    /* ************************************************* */

    /**
     *
     */
    public function testCanKill()
    {
        $log_file = __DIR__ . '/test.log';
        $cmd = 'tail -f ' . $log_file;
        $test = Scripts::run($cmd, $log_file);

        $killed = Scripts::kill($test['data']['pid']);
        $this->assertTrue($killed);
    }


}
