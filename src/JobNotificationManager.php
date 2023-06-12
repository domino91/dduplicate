<?php

namespace App;

use App\Entity\Job;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Serializer\SerializerInterface;

class JobNotificationManager
{
    public function __construct(
        private readonly HubInterface           $hub,
        private readonly EntityManagerInterface $entityManager,
        private readonly SerializerInterface    $serializer
    )
    {

    }

    public function notify(): void
    {
        $jobs = $this->entityManager->getRepository(Job::class)->findBy([], ['status' => 'DESC']);
        $jobsJson = $this->serializer->serialize($jobs, 'json');
        $update = new Update(
            'jobs',
            $jobsJson,
            false
        );

        $this->hub->publish($update);
    }
}