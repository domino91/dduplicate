<?php

namespace App\Command;

use App\Entity\Job;
use App\Message\ScanMessage;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsCommand(name: 'app:analyze:unique')]
class AnalyzeUnique extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly MessageBusInterface $bus
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $sql = 'SELECT hash, count(hash) as count, GROUP_CONCAT(path) as path FROM file_hash
                GROUP BY hash HAVING count > 1';

        $result = $this->entityManager->getConnection()->executeQuery($sql)->fetchAllAssociative();

        $table = new Table($output);
        $table
            ->setHeaderTitle('No unique files')
            ->setHeaders(['Hash', 'Count', 'Paths']);

        foreach ($result as $duplicate) {
            $table->addRow([
                $duplicate['hash'],
                $duplicate['count'],
                $duplicate['path'],
            ]);
        }

        $table->render();
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