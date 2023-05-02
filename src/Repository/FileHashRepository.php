<?php

namespace App\Repository;

use App\Entity\FileHash;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FileHash>
 *
 * @method FileHash|null find($id, $lockMode = null, $lockVersion = null)
 * @method FileHash|null findOneBy(array $criteria, array $orderBy = null)
 * @method FileHash[]    findAll()
 * @method FileHash[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FileHashRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FileHash::class);
    }

    public function save(FileHash $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(FileHash $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
