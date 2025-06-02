<?php

namespace App\Controller;

use App\Entity\Goer;
use App\Entity\Ticket;
use App\Entity\Refund;
use App\Repository\PurchaseRepository;
use App\Repository\TicketRepository;
use App\Repository\RefundRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

final class GoerAccountController extends AbstractController
{
    private $entityManager;
    private $purchaseRepository;
    private $ticketRepository;
    private $refundRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        PurchaseRepository $purchaseRepository,
        TicketRepository $ticketRepository,
        RefundRepository $refundRepository
    ) {
        $this->entityManager = $entityManager;
        $this->purchaseRepository = $purchaseRepository;
        $this->ticketRepository = $ticketRepository;
        $this->refundRepository = $refundRepository;
    }

    #[Route('/goer', name: 'goer_account')]
    public function index(): Response
    {
        if (!$this->isGranted('ROLE_GOER')) {
            throw new AccessDeniedException('Требуется авторизация с ролью Goer');
        }
    
        /** @var Goer $goer */
        $goer = $this->getUser();
    
        // Статистика
        $totalPurchases = $this->purchaseRepository->count(['goer' => $goer]);
        $activeTickets = $this->ticketRepository->countActiveTickets($goer);
        $totalRefunds = $this->refundRepository->count(['goer' => $goer]);
    
        // Подсчет потраченной суммы (исключая возвращенные билеты)
        $purchaseIds = $goer->getPurchases()->map(fn($purchase) => $purchase->getId())->toArray();
        $tickets = $this->ticketRepository->findBy(['purchase' => $purchaseIds]);
        $totalSpent = 0;
        foreach ($tickets as $ticket) {
            if ($ticket->getTicketStatus() !== 'refunded') {
            $sessionPrice = $ticket->getSession()->getSessionPrice();
            $seatType = $ticket->getSeat()->getSeatType(); // Assuming this method exists and returns 'regular' or 'economy'
            $priceMultiplier = $seatType === 'economy' ? 0.8 : 1.0;
            $totalSpent += $sessionPrice * $priceMultiplier;
            }
        }
    
        // Последние билеты (например, последние 3)
        $latestTickets = $this->ticketRepository->createQueryBuilder('t')
            ->join('t.purchase', 'p')
            ->where('p.goer = :goer')
            ->setParameter('goer', $goer)
            ->orderBy('p.purchaseDate', 'DESC')
            ->setMaxResults(3)
            ->getQuery()
            ->getResult();
    
        // Все билеты
        $allTickets = $this->ticketRepository->createQueryBuilder('t')
            ->join('t.purchase', 'p')
            ->where('p.goer = :goer')
            ->setParameter('goer', $goer)
            ->orderBy('p.purchaseDate', 'DESC')
            ->getQuery()
            ->getResult();
    
        // Возвраты
        $refunds = $this->refundRepository->findBy(['goer' => $goer], ['refundDate' => 'DESC']);
    
        return $this->render('account/goer_dashboard.html.twig', [
            'goer' => $goer,
            'totalPurchases' => $totalPurchases,
            'totalSpent' => $totalSpent,
            'activeTickets' => $activeTickets,
            'totalRefunds' => $totalRefunds,
            'latestTickets' => $latestTickets,
            'allTickets' => $allTickets,
            'refunds' => $refunds,
        ]);
    }
}