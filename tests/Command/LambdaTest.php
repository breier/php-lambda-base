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
    /**
     * Make sure container is running before running tests
     */
    protected function setUp(): void
    {
        $baseDir = realpath(__DIR__ . '/../../');
        exec("docker-compose -f {$baseDir}/docker-compose.yaml up -d");
    }

    /**
     * Tests main command of this aplication
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
        foreach (glob(TestLambdaAPI::BASE_DIR_HASHES . '/*') as $tempHash) {
            unlink($tempHash);
        }
    }
}
