<?php

namespace App\Controller;

use App\Repository\SessionRepository;
use App\Repository\RefundRepository;
use App\Repository\HallRepository;
use App\Repository\MovieRepository;
use App\Repository\TicketRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use DateTime;
use App\Entity\Session;
use App\Entity\Refund;

final class EmployeeDashboardController extends AbstractController
{
    private $sessionRepository;
    private $refundRepository;
    private $hallRepository;
    private $movieRepository;
    private $ticketRepository;
    private $entityManager;

    public function __construct(
        SessionRepository $sessionRepository,
        RefundRepository $refundRepository,
        HallRepository $hallRepository,
        MovieRepository $movieRepository,
        TicketRepository $ticketRepository,
        EntityManagerInterface $entityManager
    ) {
        $this->sessionRepository = $sessionRepository;
        $this->refundRepository = $refundRepository;
        $this->hallRepository = $hallRepository;
        $this->movieRepository = $movieRepository;
        $this->ticketRepository = $ticketRepository;
        $this->entityManager = $entityManager;
    }

    #[Route('/employee', name: 'app_employee')]
    public function index(): Response
    {
        // Get today's sessions
        $today = new DateTime();
        $todaySessions = $this->sessionRepository->findByDate($today);

        // Calculate stats
        $stats = [
            'todaySessions' => count($todaySessions),
            'ticketsSold' => array_sum(array_map(fn($session) => count($session->getTickets()), $todaySessions)),
            'pendingRefunds' => count($this->refundRepository->findBy(['refundStatus' => 'pending'])),
            'totalSeats' => array_sum(array_map(fn($session) => $session->getHall()->getHallCapacity(), $todaySessions)),
        ];
        $stats['occupancyRate'] = $stats['totalSeats'] > 0 
            ? round(($stats['ticketsSold'] / $stats['totalSeats']) * 100)
            : 0;

        // Get schedule for next 3 days
        $schedule = [];
        for ($i = 0; $i < 3; $i++) {
            $date = (new DateTime())->modify("+$i days");
            $schedule[$date->format('Y-m-d')] = $this->sessionRepository->findByDate($date);
        }

        // Get all halls and movies
        $halls = $this->hallRepository->findAll();
        $movies = $this->movieRepository->findAll();
        $refunds = $this->refundRepository->findAll();

        return $this->render('account/employee_dashboard.html.twig', [
            'stats' => $stats,
            'todaySessions' => $todaySessions,
            'schedule' => $schedule,
            'halls' => $halls,
            'movies' => $movies,
            'refunds' => $refunds,
        ]);
    }

    #[Route('/api/hall/{id}/seats', name: 'api_hall_seats', methods: ['GET'])]
    public function getHallSeats(int $id): Response
    {
        $hall = $this->hallRepository->find($id);
        
        if (!$hall) {
            return $this->json(['error' => 'Зал не найден'], 404);
        }
        
        $seats = $hall->getSeats()->toArray();
        $seatData = array_map(function($seat) {
            $ticket = $this->ticketRepository->findOneBy(['seat' => $seat]);
            return [
                'id' => $seat->getId(),
                'seatNumber' => $seat->getSeatNumber(), // Исправлено: searNumber -> seatNumber
                'seatRow' => $seat->getSeatRow(),
                'seatType' => $seat->getSeatType(),
                'ticketStatus' => $ticket ? $ticket->getTicketStatus() : 'free',
            ];
        }, $seats);
        
        return $this->json($seatData);
    }

    #[Route('/api/schedule', name: 'api_schedule', methods: ['GET'])]
    public function getSchedule(Request $request): Response
    {
        $dateStr = $request->query->get('date', (new DateTime())->format('Y-m-d'));
        $startDate = new DateTime($dateStr);
        
        $schedule = [];
        for ($i = 0; $i < 3; $i++) {
            $date = (clone $startDate)->modify("+$i days");
            $sessions = $this->sessionRepository->findByDate($date);
            $schedule[$date->format('d.m.Y')] = array_map(function ($session) {
                return [
                    'id' => $session->getId(),
                    'movieTitle' => $session->getMovie()->getMovieTitle(),
                    'sessionTime' => $session->getSessionData()->format('H:i'),
                    'hallId' => $session->getHall()->getId(),
                    'hallCapacity' => $session->getHall()->getHallCapacity(),
                    'sessionPrice' => $session->getSessionPrice(),
                    'ticketsSold' => count($session->getTickets()),
                ];
            }, $sessions);
        }

        return $this->json($schedule);
    }

    #[Route('/api/session/{id}', name: 'api_session', methods: ['GET'])]
    public function getSession(int $id): Response
    {
        $session = $this->sessionRepository->find($id);
        return $this->json([
            'id' => $session->getId(),
            'movie' => ['id' => $session->getMovie()->getId()],
            'hall' => ['id' => $session->getHall()->getId()],
            'sessionData' => $session->getSessionData()->format('Y-m-d H:i'),
            'sessionPrice' => $session->getSessionPrice(),
        ]);
    }

    #[Route('/api/session/{id}/seats', name: 'api_session_seats', methods: ['GET'])]
    public function getSessionSeats(int $id): Response
    {
        $session = $this->sessionRepository->find($id);
        if (!$session) {
            return $this->json(['error' => 'Сеанс не найден'], 404);
        }
    
        $seats = $session->getHall()->getSeats()->toArray();
        $seatData = array_map(function ($seat) use ($session) {
            $ticket = $this->ticketRepository->findOneBy(['seat' => $seat, 'session' => $session]);
            $ticketStatus = $ticket ? $ticket->getTicketStatus() : 'free';
            return [
                'id' => $seat->getId(),
                'searNumber' => $seat->getSearNumber(),
                'seatRow' => $seat->getSeatRow(),
                'seatType' => $seat->getSeatType(),
                'ticketStatus' => $ticketStatus,
                'ticketId' => $ticket ? $ticket->getId() : null, // Для отладки
                'isOccupied' => $ticketStatus === 'paid', // Исправлено: используем $ticketStatus
            ];
        }, $seats);
    
        return $this->json([
            'seats' => $seatData,
            'totalSeats' => count($seats),
            'occupiedSeats' => count(array_filter($seatData, fn($seat) => $seat['isOccupied'])),
        ]);
    }

    #[Route('/api/hall/{id}/sessions', name: 'api_hall_sessions', methods: ['GET'])]
    public function getHallSessions(int $id): Response
    {
        $sessions = $this->sessionRepository->findBy(['hall' => $id]);
        $sessionData = array_map(function ($session) {
            return [
                'id' => $session->getId(),
                'sessionData' => $session->getSessionData()->format('Y-m-d H:i'),
                'movieTitle' => $session->getMovie()->getMovieTitle(),
                'movieDuration' => $session->getMovie()->getMovieDuration(),
            ];
        }, $sessions);

        return $this->json($sessionData);
    }

    #[Route('/api/session', name: 'api_session_create', methods: ['POST'])]
    public function createSession(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);
    
        $movie = $this->movieRepository->find($data['movie_select']);
        $hall = $this->hallRepository->find($data['hall_select']);
        $sessionDateTime = new DateTime($data['session_date'] . ' ' . $data['session_time']);
    
        // Проверка конфликтов
        $existingSessions = $this->sessionRepository->findBy(['hall' => $hall]);
        foreach ($existingSessions as $session) {
            $existingStart = $session->getSessionData();
            $existingEnd = clone $existingStart;
            $existingEnd->modify('+' . $session->getMovie()->getMovieDuration() . ' minutes');
    
            $newEnd = clone $sessionDateTime;
            $newEnd->modify('+' . $movie->getMovieDuration() . ' minutes');
    
            if ($sessionDateTime < $existingEnd && $newEnd > $existingStart) {
                return $this->json(['error' => 'Конфликт с существующим сеансом'], 400);
            }
        }
    
        $session = new Session();
        $session->setMovie($movie);
        $session->setHall($hall);
        $session->setSessionData($sessionDateTime);
        $session->setSessionPrice($data['session_price']);
        $session->setEmployee($this->getUser());
    
        $this->entityManager->persist($session);
        $this->entityManager->flush();
    
        return $this->json(['success' => true]);
    }

    #[Route('/api/session/{id}', name: 'api_session_delete', methods: ['DELETE'])]
    public function deleteSession(int $id): Response
    {
        $session = $this->sessionRepository->find($id);
        $this->entityManager->remove($session);
        $this->entityManager->flush();

        return $this->json(['success' => true]);
    }

    #[Route('/api/refund/{id}/approve', name: 'api_refund_approve', methods: ['POST'])]
    public function approveRefund(int $id): Response
    {
        $refund = $this->refundRepository->find($id);
        $refund->setRefundStatus('approved');
        $this->entityManager->flush();

        return $this->json(['success' => true]);
    }

    #[Route('/api/refund/{id}/reject', name: 'api_refund_reject', methods: ['POST'])]
    public function rejectRefund(int $id, Request $request): Response
    {
        $data = json_decode($request->getContent(), true);
        $refund = $this->refundRepository->find($id);
        $refund->setRefundStatus('rejected');
        $refund->setRefundReason($refund->getRefundReason() . " (Отклонено: {$data['reason']})");
        $refund->getTicket()->setTicketStatus("rejected");
        $this->entityManager->flush();

        return $this->json(['success' => true]);
    }

    #[Route('/api/session/{id}', name: 'api_session_update', methods: ['PATCH'])]
    public function updateSession(int $id, Request $request): JsonResponse
    {
        $session = $this->sessionRepository->find($id);
        if (!$session) {
            return $this->json(['error' => 'Сеанс не найден'], 404);
        }
    
        $data = json_decode($request->getContent(), true);
        $movie = $this->movieRepository->find($data['movie_select']);
        $hall = $this->hallRepository->find($data['hall_select']);
        $sessionDateTime = new DateTime($data['session_date'] . ' ' . $data['session_time']);
    
        // Проверка конфликтов
        $existingSessions = $this->sessionRepository->findBy(['hall' => $hall]);
        foreach ($existingSessions as $existingSession) {
            if ($existingSession->getId() === $id) continue; // Пропускаем текущий сеанс
    
            $existingStart = $existingSession->getSessionData();
            $existingEnd = clone $existingStart;
            $existingEnd->modify('+' . $existingSession->getMovie()->getMovieDuration() . ' minutes');
    
            $newEnd = clone $sessionDateTime;
            $newEnd->modify('+' . $movie->getMovieDuration() . ' minutes');
    
            if ($sessionDateTime < $existingEnd && $newEnd > $existingStart) {
                return $this->json(['error' => 'Конфликт с существующим сеансом'], 400);
            }
        }
    
        $session->setMovie($movie);
        $session->setHall($hall);
        $session->setSessionData($sessionDateTime);
        $session->setSessionPrice($data['session_price']);
        $session->setEmployee($this->getUser());
    
        $this->entityManager->persist($session);
        $this->entityManager->flush();
    
        return $this->json(['success' => true]);
    }

    #[Route('/api/movie/{id}', name: 'api_movie', methods: ['GET'])]
    public function getMovie(int $id): Response
    {
        $movie = $this->movieRepository->find($id);
        if (!$movie) {
            return $this->json(['error' => 'Фильм не найден'], 404);
        }

        return $this->json([
            'id' => $movie->getId(),
            'movieTitle' => $movie->getMovieTitle(),
            'movieDuration' => $movie->getMovieDuration(),
        ]);
    }
}