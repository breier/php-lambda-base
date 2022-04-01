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
        $lambdaServer = new LambdaServer(getenv('AWS_LAMBDA_RUNTIME_API'));

        $request = $lambdaServer->getNextRequest();
        if (empty($request['payload']) || empty($request['invocationId'])) {
            $output->writeln('ERROR: Gettin Next Request from AWS failed!');
            return self::FAILURE;
        }

        $response = $lambdaServer->handle($request['payload']);
        $lambdaServer->sendResponse($request['invocationId'], $response);

        $output->writeln('Handled event successfully!');

        return self::SUCCESS;
    }
}
