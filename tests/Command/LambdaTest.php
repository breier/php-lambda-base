<?php

/**
 * PHP Version 8
 *
 * Lambda Command Test File
 *
 * @category Tests
 * @package  App\Tests
 * @author   Andre Breier <breier.de@gmail.com>
 * @license  GPLv3 https://www.gnu.org/licenses/gpl-3.0.en.html
 */

namespace App\Tests\Command;

use App\Tests\Controller\TestLambdaAPI;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Lambda Command Test class
 */
class LambdaTest extends KernelTestCase
{
    /** @var int $serverPid */
    private $serverPid;

    /**
     * Make sure container is running before running tests
     */
    protected function setUp(): void
    {
        $baseDir = realpath(__DIR__ . '/../../');
        $hostAddr = getenv('AWS_LAMBDA_RUNTIME_API');

        $this->serverPid = shell_exec(
            "php -S {$hostAddr} -t {$baseDir}/public > /dev/null 2>&1 & echo $!"
        );
    }

    /**
     * Tests main command of this application
     */
    public function testExecute(): void
    {
        $kernel = self::bootKernel();
        $application = new Application($kernel);

        $command = $application->find('lambda:serve');
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);
        $output = $commandTester->getDisplay();

        $commandTester->assertCommandIsSuccessful($output);

        $this->assertStringContainsString('Handled event successfully!', $output);
    }

    /**
     * Clean up temporary hashes
     */
    protected function tearDown(): void
    {
        shell_exec("kill {$this->serverPid}");

        foreach (glob(TestLambdaAPI::BASE_DIR_HASHES . '/*') as $tempHash) {
            unlink($tempHash);
        }
    }
}
