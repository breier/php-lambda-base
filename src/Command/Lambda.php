<?php

/**
 * PHP Version 8
 *
 * Lambda Command File
 *
 * @category Command
 * @package  App\Command
 * @author   Andre Breier <breier.de@gmail.com>
 * @license  MIT /LICENSE
 */

namespace App\Command;

use App\Service\LambdaServer;
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
        $output->writeln('Starting Event Monitor ...');

        if (!getenv('AWS_LAMBDA_RUNTIME_API')) {
            $output->writeln('!! AWS_LAMBDA_RUNTIME_API not found !!');
            return self::FAILURE;
        }

        $lambdaServer = new LambdaServer(getenv('AWS_LAMBDA_RUNTIME_API'));

        while (true) {
            // Ask the runtime API for a request to handle.
            $request = $lambdaServer->getNextRequest();

            // Retry until timeout
            if (empty($request['payload']) || empty($request['invocationId'])) {
                $output->writeln('!! Invalid event request !!');
                $output->writeln(json_encode($request, JSON_PRETTY_PRINT));

                sleep(1);
                continue;
            }

            // Execute the desired function and obtain the response.
            $response = $lambdaServer->handle($request['payload']);

            // Submit the response back to the runtime API.
            $lambdaServer->sendResponse($request['invocationId'], $response);

            $output->writeln('Handled one event successfully!');
        }

        return 0;
    }
}
