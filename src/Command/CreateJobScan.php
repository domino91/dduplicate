<?php

namespace App\Command;

use App\Entity\Job;
use App\Factory\ShareFactory;
use App\Message\ScanMessage;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsCommand(name: 'app:job:scan')]
class CreateJobScan extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly MessageBusInterface $bus,
        private readonly ShareFactory $shareFactory
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
                $this->createJob($info->getPath());
            }
            $progressBar->advance();
        }

        $progressBar->finish();
        $output->writeln('');

        return Command::SUCCESS;
    }

    private function createJob(string $path): int
    {
        $job = new Job();

        $job->setPath($path);
        $job->setCreatedAt(new DateTimeImmutable());
        $job->setStatus(Job::STATUS_PENDING);

        $this->entityManager->persist($job);
        $this->entityManager->flush();

        $this->bus->dispatch(
            new ScanMessage($job->getId())
        );

        return $job->getId();
    }
}