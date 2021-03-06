<?php

namespace ShopwareCli\Tests;

use ShopwareCli\Services\ProcessExecutor;
use Symfony\Component\Console\Output\BufferedOutput;

class ProcessExecutorTest extends \PHPUnit_Framework_TestCase
{
    public function testCliToolGateway()
    {
        $output = new BufferedOutput();
        $executor = new ProcessExecutor($output);

        $exitCode = $executor->execute('true');
        $this->assertEquals(0, $exitCode);
        $this->assertEquals('', $output->fetch());

        $exitCode = $executor->execute('echo foo');
        $this->assertEquals(0, $exitCode);
        $this->assertEquals("foo\n", $output->fetch());
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Command failed. Error Output:
     * @expectedExceptionCode 1
     */
    public function testFailedCommand()
    {
        $output = new BufferedOutput();
        $executor = new ProcessExecutor($output);

        $executor->execute('false');
    }

    public function testFailedCommand2()
    {
        $output = new BufferedOutput();
        $executor = new ProcessExecutor($output);

        $expectedOutput = "ls: cannot access /no-such-file: No such file or directory\n";
        try {
            $executor->execute('LC_ALL=C ls /no-such-file');
        } catch (\Exception $e) {
            $this->assertEquals(2, $e->getCode());
            $this->assertContains($expectedOutput, $e->getMessage());
            $this->assertEquals($expectedOutput, $output->fetch());

            return;
        }

        $this->fail("Executor should throw exception on failed command");
    }

    public function testAllowFailingCmmand()
    {
        $output = new BufferedOutput();
        $executor = new ProcessExecutor($output);

        $expectedOutput = "ls: cannot access /no-such-file: No such file or directory\n";

        $exitCode = $executor->execute('LC_ALL=C ls /no-such-file', null, true);

        $this->assertEquals(2, $exitCode);
        $this->assertEquals($expectedOutput, $output->fetch());
    }
}
