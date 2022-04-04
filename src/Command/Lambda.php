<?php

/**
 * PHP Version 8
 *
 * Lambda Command File
 *
 * @category Command
 * @package  App\Command
 * @author   Andre Breier <breier.de@gmail.com>
 * @license  GPLv3 https://www.gnu.org/licenses/gpl-3.0.en.html
 */

namespace App\Command;

use App\Service\LambdaService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Lambda Command class
 */
class Lambda extends Command
{
    /**
     * Symfony Expected Variable
     */
    protected static $defaultName = 'lambda:serve';

    /**
     * Symfony Expected Description
     */
    protected function configure(): void
    {
        $this->setDescription('Serves AWS Lambda Functions');
    }

    /**
     * Symfony Expected Execute
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $lambdaService = new LambdaService(getenv('AWS_LAMBDA_RUNTIME_API'));
        } catch (\Exception $e) {
            $output->writeln("ERROR: {$e->getMessage()}");
            return self::FAILURE;
        }

        try {
            $successOnce = false;

            while (true) {
                $request = $lambdaService->getNextRequest();

                if (empty($request['payload']) || empty($request['invocationId'])) {
                    throw new \Exception('Getting Next Request from AWS failed!');
                }

                $response = $lambdaService->handle($request['payload']);
                $lambdaService->sendResponse($request['invocationId'], $response);

                $output->writeln('Handled event successfully!');
                $successOnce = true;
            }
        } catch (\Exception $e) {
            if (!$successOnce) {
                $output->writeln("ERROR: {$e->getMessage()}");
                return self::FAILURE;
            }
        }

        return self::SUCCESS;
    }
}
