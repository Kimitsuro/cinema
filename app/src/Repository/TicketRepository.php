<?php

namespace App\Repository;

use App\Entity\Ticket;
use App\Entity\Goer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Ticket>
 */
class TicketRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Ticket::class);
    }

    public function getTotalSpentByGoer(Goer $goer): float
    {
        $result = $this->createQueryBuilder('t')
            ->select('SUM(CASE WHEN s.seatType = :economy THEN sess.sessionPrice * 0.8 ELSE sess.sessionPrice END)')
            ->join('t.purchase', 'p')
            ->join('t.session', 'sess')
            ->join('t.seat', 's')
            ->where('p.goer = :goer')
            ->setParameter('goer', $goer)
            ->setParameter('economy', 'economy')
            ->getQuery()
            ->getSingleScalarResult();

        return (float) ($result ?? 0.0);
    }

    public function countActiveTickets(Goer $goer): int
    {
        return (int) $this->createQueryBuilder('t')
            ->select('COUNT(t.id)')
            ->join('t.purchase', 'p')
            ->join('t.session', 's')
            ->where('p.goer = :goer')
            ->andWhere('s.sessionData > :now')
            ->andWhere('t.ticketStatus = :status')
            ->setParameter('goer', $goer)
            ->setParameter('now', new \DateTime())
            ->setParameter('status', 'paid')
            ->getQuery()
            ->getSingleScalarResult();
    }

        public function countTicketsSoldForDate(\DateTimeInterface $date): int
    {
        $start = (clone $date)->setTime(0, 0);
        $end = (clone $date)->setTime(23, 59, 59);

        return $this->createQueryBuilder('t')
            ->select('COUNT(t.id)')
            ->join('t.session', 's')
            ->where('s.startTime >= :start')
            ->andWhere('s.startTime <= :end')
            ->andWhere('t.status = :status')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->setParameter('status', 'sold')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getOccupancyRateForDate(\DateTimeInterface $date): float
    {
        $start = (clone $date)->setTime(0, 0);
        $end = (clone $date)->setTime(23, 59, 59);

        $soldTickets = $this->createQueryBuilder('t')
            ->select('COUNT(t.id)')
            ->join('t.session', 's')
            ->where('s.startTime >= :start')
            ->andWhere('s.startTime <= :end')
            ->andWhere('t.status = :status')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->setParameter('status', 'sold')
            ->getQuery()
            ->getSingleScalarResult();

        $totalSeats = $this->createQueryBuilder('t')
            ->select('SUM(h.capacity)')
            ->join('t.session', 's')
            ->join('s.hall', 'h')
            ->where('s.startTime >= :start')
            ->andWhere('s.startTime <= :end')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->getQuery()
            ->getSingleScalarResult();

        return $totalSeats > 0 ? round(($soldTickets / $totalSeats) * 100, 2) : 0;
    }

}
