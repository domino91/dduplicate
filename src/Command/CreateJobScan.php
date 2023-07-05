<?php

namespace App\Command;

use App\Factory\ShareFactory;
use App\Manager\JobManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:job:scan')]
class CreateJobScan extends Command
{
    public function __construct(
        private readonly ShareFactory $shareFactory,
        private readonly JobManager $jobManager
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $share   = $this->shareFactory->create();
        $content = $share->dir($this->shareFactory->getDefaultDir());

        $progressBar = new ProgressBar($output, count($content));
        $progressBar->start();
        foreach ($content as $info) {
            if ($info->isDirectory()) {
                $this->jobManager->createJob($info->getPath());
            }
            $progressBar->advance();
        }

        $progressBar->finish();
        $output->writeln('');

        return Command::SUCCESS;
    }


}