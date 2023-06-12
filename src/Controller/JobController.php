<?php

namespace App\Controller;

use App\Entity\Job;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class JobController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    )
    {
    }

    #[Route('/', name: 'app_job')]
    public function index(): Response
    {
        return $this->render('index.html.twig');
    }

    #[Route('/jobs')]
    public function jobsList(): Response
    {
        $jobs = $this->entityManager->getRepository(Job::class)->findBy([], ['status' => 'DESC']);

        return $this->json($jobs);
    }
}
