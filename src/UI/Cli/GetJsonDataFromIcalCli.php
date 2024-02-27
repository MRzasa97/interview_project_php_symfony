<?php

namespace App\UI\Cli;

use App\Domain\Query\GetInformationsFromIcalAsJsonQueryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use App\Application\Message\Command\SendJsonDataToS3BucketCommand;
use Symfony\Component\Messenger\MessageBusInterface;
use App\Application\MessageHandler\SendJsonDataToS3BucketMessageHandler;

class GetJsonDataFromIcalCli extends Command
{
    public function __construct(
        private readonly GetInformationsFromIcalAsJsonQueryInterface $getInformationsFromIcalAsJsonQuery,
        private readonly MessageBusInterface $messageBus
    )
    {
        parent::__construct();

        $this->setName('app:get-json-data-from-ical');
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Get json data from ical')
            ->addArgument('sourceFileName', InputOption::VALUE_REQUIRED, 'The source file name')
            ->addArgument('destinationFileName', InputOption::VALUE_REQUIRED, 'The destination file name')
            ->setHelp('This command allows you to get json data from ical...');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $sourceFileName = $input->getArgument('sourceFileName');
        $destinationFileName = $input->getArgument('destinationFileName');
        $json = $this->getInformationsFromIcalAsJsonQuery->execute($sourceFileName);
        $this->messageBus->dispatch(new SendJsonDataToS3BucketCommand(json_encode($json, JSON_PRETTY_PRINT), $destinationFileName));
        $output->writeln(json_encode($json, JSON_PRETTY_PRINT));
        return Command::SUCCESS;
    }
}