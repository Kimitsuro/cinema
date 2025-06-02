<?php

namespace App\Controller;

use App\Entity\Purchase;
use App\Entity\Session;
use App\Entity\Ticket;
use App\Entity\Goer;
use App\Entity\Seat;
use App\Repository\SessionRepository;
use App\Repository\SeatRepository;
use App\Repository\TicketRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

final class TicketPurchaseController extends AbstractController
{
    private $entityManager;
    private $sessionRepository;
    private $seatRepository;
    private $ticketRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        SessionRepository $sessionRepository,
        SeatRepository $seatRepository,
        TicketRepository $ticketRepository
    ) {
        $this->entityManager = $entityManager;
        $this->sessionRepository = $sessionRepository;
        $this->seatRepository = $seatRepository;
        $this->ticketRepository = $ticketRepository;
    }

    #[Route('/ticket-purchase/{movieId<\d+>}', name: 'ticket_purchase')]
    public function index(int $movieId, Request $request): Response
    {
        // Текущая дата и время
        $currentDateTime = new \DateTime('now', new \DateTimeZone('Europe/Moscow'));

        // Получение сеансов для фильма, исключая прошедшие
        $sessions = $this->sessionRepository->findBy(
            ['movie' => $movieId],
            ['sessionData' => 'ASC']
        );

        // Фильтрация прошедших сеансов
        $sessions = array_filter($sessions, function ($session) use ($currentDateTime) {
            return $session->getSessionData() >= $currentDateTime;
        });

        $sessionId = $request->query->get('sessionId');
        $currentSession = $sessionId ? $this->sessionRepository->find($sessionId) : (reset($sessions) ?: null);

        // Проверка, что текущий сеанс не прошел
        if ($currentSession && $currentSession->getSessionData() < $currentDateTime) {
            $currentSession = reset($sessions) ?: null;
        }

        if ($currentSession) {
            $hall = $currentSession->getHall();
            $hall->getHallType();
            $hall->getHallCapacity();
        }

        $groupedSessions = [];
        foreach ($sessions as $session) {
            $date = $session->getSessionData()->format('Y-m-d');
            $groupedSessions[$date][] = $session;
        }

        return $this->render('info/ticket-purchase.html.twig', [
            'movie' => $this->entityManager->getRepository(\App\Entity\Movie::class)->find($movieId),
            'groupedSessions' => $groupedSessions,
            'currentSession' => $currentSession,
        ]);
    }

    #[Route('/ticket-purchase/seats/{sessionId<\d+>}', name: 'ticket_purchase_seats')]
    public function getSeats(int $sessionId): JsonResponse
    {
        $session = $this->sessionRepository->find($sessionId);
        if (!$session) {
            return new JsonResponse(['error' => 'Сеанс не найден'], 404);
        }

        $seats = $this->seatRepository->findBy(['hall' => $session->getHall()]);
        $tickets = $this->ticketRepository->findBy(['session' => $session, 'ticketStatus' => 'paid']);
        $occupiedSeats = array_map(function ($ticket) {
            return $ticket->getSeat()->getId();
        }, $tickets);

        $seatsData = array_map(function ($seat) {
            return [
                'id' => $seat->getId(),
                'searNumber' => $seat->getSearNumber(),
                'seatRow' => $seat->getSeatRow(),
                'seatType' => $seat->getSeatType(),
            ];
        }, $seats);

        return new JsonResponse([
            'seats' => $seatsData,
            'occupiedSeats' => $occupiedSeats
        ]);
    }

    #[Route('/ticket-purchase/session/{sessionId<\d+>}', name: 'ticket_purchase_session')]
    public function getSessionData(int $sessionId): JsonResponse
    {
        $session = $this->sessionRepository->find($sessionId);
        if (!$session) {
            return new JsonResponse(['error' => 'Сеанс не найден'], 404);
        }

        return new JsonResponse([
            'id' => $session->getId(),
            'time' => $session->getSessionData()->format('H:i'),
            'date' => $session->getSessionData()->format('d.m.Y'),
            'hall' => $session->getHall()->getId(),
            'price' => $session->getSessionPrice(),
            'format' => $session->getMovie()->getMovieFormat()
        ]);
    }

    #[Route('/ticket-purchase/confirm', name: 'ticket_purchase_confirm', methods: ['POST'])]
    public function confirmPurchase(Request $request): JsonResponse
    {
        if (!$this->isGranted('ROLE_GOER')) {
            throw new AccessDeniedException('Требуется авторизация с ролью Goer');
        }

        $data = json_decode($request->getContent(), true);
        $sessionId = $data['sessionId'] ?? null;
        $seatIds = $data['seatIds'] ?? [];

        if (!$sessionId || empty($seatIds)) {
            return new JsonResponse(['error' => 'Неверные данные'], 400);
        }

        $session = $this->sessionRepository->find($sessionId);
        if (!$session) {
            return new JsonResponse(['error' => 'Сеанс не найден'], 404);
        }

        $occupiedSeats = $this->ticketRepository->findBy([
            'session' => $session,
            'seat' => $seatIds,
            'ticketStatus' => 'paid'
        ]);

        if (!empty($occupiedSeats)) {
            return new JsonResponse(['error' => 'Некоторые места уже заняты'], 400);
        }

        $purchase = new Purchase();
        $purchase->setSession($session);
        $purchase->setGoer($this->getUser());
        $purchase->setPurchaseDate(new \DateTime());

        foreach ($seatIds as $seatId) {
            $seat = $this->seatRepository->find($seatId);
            if (!$seat) {
                return new JsonResponse(['error' => 'Место не найдено'], 404);
            }

            $ticket = new Ticket();
            $ticket->setSession($session);
            $ticket->setPurchase($purchase);
            $ticket->setSeat($seat);
            $ticket->setTicketStatus('paid');
            $purchase->addTicket($ticket);
            $this->entityManager->persist($ticket);
        }

        $this->entityManager->persist($purchase);
        $this->entityManager->flush();

        return new JsonResponse(['success' => true]);
    }
}