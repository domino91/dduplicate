<?php

namespace App\Controller;

use App\Entity\Job;
use App\Factory\ShareFactory;
use App\Manager\JobManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class JobController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ShareFactory           $shareFactory,
        private readonly JobManager             $jobManager
    )
    {
    }

    #[Route('/', name: 'app_job')]
    public function index(): Response
    {
        return $this->render('index.html.twig');
    }

    #[Route('/jobs', methods: ["GET"])]
    public function list(): Response
    {
        $jobs = $this->entityManager->getRepository(Job::class)->findBy([], ['status' => 'DESC']);

        return $this->json($jobs);
    }

    #[Route('/jobs', methods: ["POST"])]
    public function create(): Response
    {
        $jobs = [];

        $share = $this->shareFactory->create();
        $content = $share->dir($this->shareFactory->getDefaultDir());
        foreach ($content as $info) {
            if ($info->isDirectory()) {
                $jobs[] = $this->jobManager->createJob($info->getPath());
            }
        }

        return $this->json($jobs);
    }

    #[Route('/jobs', methods: ["DELETE"])]
    public function truncate(): Response
    {
        $this->jobManager->truncate();
        return new JsonResponse();
    }
}
