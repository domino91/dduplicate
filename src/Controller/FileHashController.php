<?php

namespace App\Controller;

use App\Entity\FileHash;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FileHashController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    )
    {
    }

    #[Route('/fileHash', methods: ["DELETE"])]
    public function truncate(): Response
    {
        $fileHashes = $this->entityManager->getRepository(FileHash::class)->findAll();

        foreach ($fileHashes as $fileHash) {
            $this->entityManager->remove($fileHash);
        }

        $this->entityManager->flush();
        return new JsonResponse();
    }
}
