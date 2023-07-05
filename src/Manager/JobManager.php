<?php

namespace App\Manager;

use App\Entity\Job;
use App\JobNotificationManager;
use App\Message\ScanMessage;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class JobManager
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly MessageBusInterface    $bus,
        private readonly JobNotificationManager $notificationManager
    )
    {
    }

    public function createJob(string $path): Job
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
        $this->notificationManager->notify();

        return $job;
    }

    public function truncate(): void
    {
        $jobs = $this->entityManager->getRepository(Job::class)->findAll();

        foreach ($jobs as $job) {
            $this->entityManager->remove($job);
        }

        $this->entityManager->flush();
        $this->notificationManager->notify();
    }
}