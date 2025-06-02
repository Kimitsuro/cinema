<?php

namespace App\Repository;

use App\Entity\Session;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use DateTime;
use Doctrine\ORM\QueryBuilder;

/**
 * @extends ServiceEntityRepository<Session>
 */
class SessionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Session::class);
    }

    public function findByDate(DateTime $date): array
    {
        return $this->createQueryBuilder('s')
            ->where('s.sessionData >= :start')
            ->andWhere('s.sessionData < :end')
            ->setParameter('start', $date->format('Y-m-d 00:00:00'))
            ->setParameter('end', $date->format('Y-m-d 23:59:59'))
            ->orderBy('s.sessionData', 'ASC')
            ->getQuery()
            ->getResult();
    }

}
