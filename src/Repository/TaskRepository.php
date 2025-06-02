<?php

namespace App\Repository;

use App\Entity\Task;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Task>
 */
class TaskRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Task::class);
    }

    public function findByFiltres(?string $statut, ?int $projetId, ?int $userId, ?string $terme): array
    {
        $qb = $this->createQueryBuilder('t');

        if ($statut) {
            $qb->andWhere('t.statut = :statut')
                ->setParameter('statut', $statut);
        }

        if ($projetId) {
            $qb->andWhere('t.projet = :projet')
                ->setParameter('projet', $projetId);
        }

        if ($userId) {
            $qb->andWhere('t.assigneA = :user')
                ->setParameter('user', $userId);
        }

        if ($terme) {
            $qb->andWhere('t.titre LIKE :terme OR t.description LIKE :terme')
                ->setParameter('terme', '%' . $terme . '%');
        }

        return $qb->getQuery()->getResult();
    }


    //    /**
    //     * @return Task[] Returns an array of Task objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('t.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Task
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
