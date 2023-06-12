<?php

namespace App\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:analyze:unique')]
class AnalyzeUnique extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $sql = 'SELECT hash, count(hash) as count, GROUP_CONCAT(path) as path FROM file_hash
                GROUP BY hash HAVING count > 1';

        $result = $this->entityManager->getConnection()->executeQuery($sql)->fetchAllAssociative();
        $totalCount = count($result);
        $table = new Table($output);
        $table
            ->setHeaderTitle(sprintf('No unique files (%d)', $totalCount))
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
}