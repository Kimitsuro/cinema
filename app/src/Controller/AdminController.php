<?php

namespace App\Controller;

use App\Entity\Movie;
use App\Entity\Hall;
use App\Entity\Employee;
use App\Entity\Seat;
use App\Entity\Session;
use App\Entity\Ticket;
use App\Entity\Purchase;
use App\Repository\MovieRepository;
use App\Repository\HallRepository;
use App\Repository\EmployeeRepository;
use App\Repository\SessionRepository;
use App\Repository\TicketRepository;
use App\Repository\PurchaseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Dompdf\Dompdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminController extends AbstractController
{
    #[Route('/admin', name: 'app_admin')]
    public function index(
        MovieRepository $movieRepository,
        HallRepository $hallRepository,
        EmployeeRepository $employeeRepository,
        SessionRepository $sessionRepository,
        TicketRepository $ticketRepository,
        PurchaseRepository $purchaseRepository,
        Request $request
    ): Response {
        // Получаем период из запроса или используем значение по умолчанию
        $period = $request->query->get('period', '7 days');
        $startDate = $request->query->get('startDate');
        $endDate = $request->query->get('endDate');
    
        // Вычисляем статистику для дашборда с учётом периода
        $stats = $this->getReportStats($sessionRepository, $ticketRepository, $purchaseRepository, $period, $startDate, $endDate);
        $stats['totalMovies'] = $movieRepository->count([]);
        $stats['activeHalls'] = $hallRepository->count([]);
        $stats['totalEmployees'] = $employeeRepository->count([]);
    
        // Цены (заглушка, так как нет сущности Pricing)
        $pricing = [
            'economyPrice' => 350,
            'regularPrice' => 400,
            'format3DPrice' => 100,
            'imaxPrice' => 200
        ];
    
        // Отчёты за выбранный период
        $reportStats = $this->getReportStats($sessionRepository, $ticketRepository, $purchaseRepository, $period, $startDate, $endDate);
        $movieReports = $this->getMovieReports($sessionRepository, $ticketRepository, $purchaseRepository, $period, $startDate, $endDate);
    
        return $this->render('account/admin_dashboard.html.twig', [
            'movies' => $movieRepository->findAll(),
            'halls' => $hallRepository->findAll(),
            'employees' => $employeeRepository->findAll(),
            'stats' => $stats,
            'pricing' => $pricing,
            'reportStats' => $reportStats,
            'movieReports' => $movieReports
        ]);
    }

    #[Route('/admin/movies', name: 'app_admin_movies', methods: ['POST'])]
    public function createMovie(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $movie = new Movie();
        $movie->setMovieTitle($data['movieTitle']);
        $movie->setMovieGenre($data['movieGenre']);
        $movie->setMovieDuration((int)$data['movieDuration']);
        $movie->setMovieAge((int)$data['movieAge']);
        $movie->setMovieFormat($data['movieFormat']);
        $movie->setStatus($data['status']);
        $movie->setMoviePoster($data['moviePoster'] ?? '/placeholder.svg?height=60&width=40');
        $movie->setMovieDescription($data['movieDescription'] ?? '');
        $movie->setAdmin($this->getUser());

        $em->persist($movie);
        $em->flush();

        return new JsonResponse([
            'id' => $movie->getId(),
            'movieTitle' => $movie->getMovieTitle(),
            'movieGenre' => $movie->getMovieGenre(),
            'movieDuration' => $movie->getMovieDuration(),
            'movieAge' => $movie->getMovieAge(),
            'movieFormat' => $movie->getMovieFormat(),
            'status' => $movie->getStatus(),
            'moviePoster' => $movie->getMoviePoster(),
            'movieDescription' => $movie->getMovieDescription()
        ], Response::HTTP_CREATED);
    }

    #[Route('/admin/movies/{id}', name: 'app_admin_movie_edit', methods: ['PUT'])]
    public function updateMovie(Movie $movie, Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $movie->setMovieTitle($data['movieTitle']);
        $movie->setMovieGenre($data['movieGenre']);
        $movie->setMovieDuration((int)$data['movieDuration']);
        $movie->setMovieAge((int)$data['movieAge']);
        $movie->setMovieFormat($data['movieFormat']);
        $movie->setStatus($data['status']);
        $movie->setMoviePoster($data['moviePoster'] ?? '/placeholder.svg?height=60&width=40');
        $movie->setMovieDescription($data['movieDescription'] ?? '');

        $em->flush();

        return new JsonResponse([
            'id' => $movie->getId(),
            'movieTitle' => $movie->getMovieTitle(),
            'movieGenre' => $movie->getMovieGenre(),
            'movieDuration' => $movie->getMovieDuration(),
            'movieAge' => $movie->getMovieAge(),
            'movieFormat' => $movie->getMovieFormat(),
            'status' => $movie->getStatus(),
            'moviePoster' => $movie->getMoviePoster(),
            'movieDescription' => $movie->getMovieDescription()
        ], Response::HTTP_OK);
    }

    #[Route('/admin/movies/{id}', name: 'app_admin_movie_get', methods: ['GET'])]
    public function getMovie(Movie $movie): JsonResponse
    {
        return new JsonResponse([
            'id' => $movie->getId(),
            'movieTitle' => $movie->getMovieTitle(),
            'movieGenre' => $movie->getMovieGenre(),
            'movieDuration' => $movie->getMovieDuration(),
            'movieAge' => $movie->getMovieAge(),
            'movieFormat' => $movie->getMovieFormat(),
            'status' => $movie->getStatus(),
            'moviePoster' => $movie->getMoviePoster(),
            'movieDescription' => $movie->getMovieDescription()
        ], Response::HTTP_OK);
    }

    #[Route('/admin/movies/{id}', name: 'app_admin_movie_delete', methods: ['DELETE'])]
    public function deleteMovie(Movie $movie, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($movie);
        $em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/admin/halls', name: 'app_admin_halls', methods: ['POST'])]
    public function createHall(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $hall = new Hall();
        $hall->setHallType($data['hallType']);
        $hall->setHallCapacity((int)$data['hallCapacity']);

        $em->persist($hall);

        // Создание мест
        $rows = (int)$data['rows'];
        $seatsPerRow = (int)$data['seatsPerRow'];
        $economySeats = (int)$data['economySeats'];
        $regularSeats = (int)$data['regularSeats'];

        for ($row = 1; $row <= $rows; $row++) {
            for ($seatNum = 1; $seatNum <= $seatsPerRow; $seatNum++) {
                $seat = new Seat();
                $seat->setHall($hall);
                $seat->setSeatRow($row);
                $seat->setSearNumber($seatNum);
                $seat->setSeatType($economySeats > 0 ? 'economy' : 'regular');
                $em->persist($seat);
                if ($economySeats > 0) $economySeats--;
                else $regularSeats--;
            }
        }

        $em->flush();

        return new JsonResponse(['id' => $hall->getId(), 'hallType' => $hall->getHallType(), 'hallCapacity' => $hall->getHallCapacity(), 'rows' => $rows, 'seatsPerRow' => $seatsPerRow, 'economySeats' => $data['economySeats'], 'regularSeats' => $data['regularSeats']], Response::HTTP_CREATED);
    }

    #[Route('/admin/halls/{id}', name: 'app_admin_hall_edit', methods: ['PUT'])]
    public function updateHall(Hall $hall, Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $hall->setHallType($data['hallType']);
        $hall->setHallCapacity((int)$data['hallCapacity']);

        // Обновление мест
        foreach ($hall->getSeats() as $seat) {
            $em->remove($seat);
        }
        $rows = (int)$data['rows'];
        $seatsPerRow = (int)$data['seatsPerRow'];
        $economySeats = (int)$data['economySeats'];
        $regularSeats = (int)$data['regularSeats'];

        for ($row = 1; $row <= $rows; $row++) {
            for ($seatNum = 1; $seatNum <= $seatsPerRow; $seatNum++) {
                $seat = new Seat();
                $seat->setHall($hall);
                $seat->setSeatRow($row);
                $seat->setSearNumber($seatNum);
                $seat->setSeatType($economySeats > 0 ? 'economy' : 'regular');
                $em->persist($seat);
                if ($economySeats > 0) $economySeats--;
                else $regularSeats--;
            }
        }

        $em->flush();

        return new JsonResponse(['id' => $hall->getId(), 'hallType' => $hall->getHallType(), 'hallCapacity' => $hall->getHallCapacity(), 'rows' => $rows, 'seatsPerRow' => $seatsPerRow, 'economySeats' => $data['economySeats'], 'regularSeats' => $data['regularSeats']], Response::HTTP_OK);
    }

    #[Route('/admin/halls/{id}', name: 'app_admin_hall_get', methods: ['GET'])]
    public function getHall(Hall $hall): JsonResponse
    {
        $economySeats = $hall->getSeats()->filter(fn($seat) => $seat->getSeatType() === 'economy')->count();
        $regularSeats = $hall->getSeats()->filter(fn($seat) => $seat->getSeatType() === 'regular')->count();
        $rows = $hall->getSeats()->map(fn($seat) => $seat->getSeatRow())->toArray();
        $seatsPerRow = $hall->getSeats()->filter(fn($seat) => $seat->getSeatRow() === 1)->count();

        return new JsonResponse([
            'id' => $hall->getId(),
            'hallType' => $hall->getHallType(),
            'hallCapacity' => $hall->getHallCapacity(),
            'rows' => max($rows) ?: 0,
            'seatsPerRow' => $seatsPerRow,
            'economySeats' => $economySeats,
            'regularSeats' => $regularSeats
        ], Response::HTTP_OK);
    }

    #[Route('/admin/halls/{id}', name: 'app_admin_hall_delete', methods: ['DELETE'])]
    public function deleteHall(Hall $hall, EntityManagerInterface $em, SessionRepository $sessionRepository): JsonResponse
    {
        // Проверяем, есть ли сеансы, связанные с залом
        $sessions = $sessionRepository->findBy(['hall' => $hall]);
        if (!empty($sessions)) {
            return new JsonResponse(['error' => 'Нельзя удалить зал, так как с ним связаны сеансы'], Response::HTTP_CONFLICT);
        }

        // Удаляем все связанные места
        foreach ($hall->getSeats() as $seat) {
            $em->remove($seat);
        }

        // Удаляем сам зал
        $em->remove($hall);
        $em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/admin/employees', name: 'app_admin_employees', methods: ['POST'])]
    public function createEmployee(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $employee = new Employee();
        $employee->setEmployeeName($data['employeeName']);
        $employee->setEmployeeLogin($data['employeeLogin']);
        $employee->setEmployeePosition($data['employeePosition']);
        $employee->setEmployeePassword($passwordHasher->hashPassword($employee, $data['employeePassword']));
        $employee->setAdmin($this->getUser());

        $em->persist($employee);
        $em->flush();

        return new JsonResponse(['employeeLogin' => $employee->getEmployeeLogin()], Response::HTTP_CREATED);
    }

    #[Route('/admin/employees/{employeeLogin}', name: 'app_admin_employee_edit', methods: ['PUT'])]
    public function updateEmployee(string $employeeLogin, Request $request, EmployeeRepository $employeeRepository, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher): JsonResponse
    {
        $employee = $employeeRepository->findOneBy(['employeeLogin' => $employeeLogin]);

        if (!$employee) {
            return new JsonResponse(['error' => 'Сотрудник не найден'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);
        $employee->setEmployeeName($data['employeeName']);
        $employee->setEmployeeLogin($data['employeeLogin']);
        $employee->setEmployeePosition($data['employeePosition']);
        if (!empty($data['employeePassword'])) {
            $employee->setEmployeePassword($passwordHasher->hashPassword($employee, $data['employeePassword']));
        }

        $em->flush();

        return new JsonResponse(['employeeLogin' => $employee->getEmployeeLogin()], Response::HTTP_OK);
    }

    #[Route('/admin/employees/{employeeLogin}', name: 'app_admin_employee_get', methods: ['GET'])]
    public function getEmployee(string $employeeLogin, EmployeeRepository $employeeRepository): JsonResponse
    {
        $employee = $employeeRepository->findOneBy(['employeeLogin' => $employeeLogin]);

        if (!$employee) {
            return new JsonResponse(['error' => 'Сотрудник не найден'], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse([
            'employeeLogin' => $employee->getEmployeeLogin(),
            'employeeName' => $employee->getEmployeeName(),
            'employeePosition' => $employee->getEmployeePosition()
        ], Response::HTTP_OK);
    }

    #[Route('/admin/employees/{employeeLogin}', name: 'app_admin_employee_delete', methods: ['DELETE'])]
    public function deleteEmployee(string $employeeLogin, EmployeeRepository $employeeRepository, EntityManagerInterface $em): JsonResponse
    {
        $employee = $employeeRepository->findOneBy(['employeeLogin' => $employeeLogin]);
        if (!$employee) {
            return new JsonResponse(['error' => 'Сотрудник не найден'], Response::HTTP_NOT_FOUND);
        }

        $em->remove($employee);
        $em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/admin/pricing', name: 'app_admin_pricing', methods: ['POST'])]
    public function savePricing(Request $request): JsonResponse
    {
        // Заглушка, так как нет сущности Pricing
        return new JsonResponse(['message' => 'Цены сохранены'], Response::HTTP_OK);
    }

    #[Route('/admin/pricing/reset', name: 'app_admin_pricing_reset', methods: ['POST'])]
    public function resetPricing(): JsonResponse
    {
        // Заглушка
        return new JsonResponse(['message' => 'Цены сброшены'], Response::HTTP_OK);
    }

    #[Route('/admin/reports', name: 'app_admin_reports', methods: ['GET'])]
    public function getReports(Request $request, SessionRepository $sessionRepository, TicketRepository $ticketRepository, PurchaseRepository $purchaseRepository): JsonResponse
    {
        $period = $request->query->get('period', '7 days');
        $startDate = $request->query->get('startDate');
        $endDate = $request->query->get('endDate');

        $reportStats = $this->getReportStats($sessionRepository, $ticketRepository, $purchaseRepository, $period, $startDate, $endDate);
        $movieReports = $this->getMovieReports($sessionRepository, $ticketRepository, $purchaseRepository, $period, $startDate, $endDate);

        return new JsonResponse([
            'reportStats' => $reportStats,
            'movieReports' => $movieReports
        ], Response::HTTP_OK);
    }

    private function calculateTotalRevenue(PurchaseRepository $purchaseRepository, ?string $startDate = null, ?string $endDate = null): float
    {
        $queryBuilder = $purchaseRepository->createQueryBuilder('p')
            ->select('SUM(CASE WHEN st.seatType = :economy THEN s.sessionPrice * 0.8 ELSE s.sessionPrice END) as totalRevenue')
            ->join('p.tickets', 't')
            ->join('t.session', 's')
            ->join('t.seat', 'st')
            ->where('t.ticketStatus = :status')
            ->setParameter('status', 'paid')
            ->setParameter('economy', 'economy');
    
        if ($startDate && $endDate) {
            $startDateTime = new \DateTime($startDate);
            $endDateTime = new \DateTime($endDate);
            $endDateTime->setTime(23, 59, 59);
            $queryBuilder->andWhere('p.purchaseDate BETWEEN :startDate AND :endDate')
                         ->setParameter('startDate', $startDateTime)
                         ->setParameter('endDate', $endDateTime);
        }
    
        return (float)($queryBuilder->getQuery()->getSingleScalarResult() ?? 0);
    }

    private function calculateAverageOccupancy(SessionRepository $sessionRepository, TicketRepository $ticketRepository, HallRepository $hallRepository): float
    {
        $sessions = $sessionRepository->findAll();
        $totalOccupancy = 0;
        $sessionCount = count($sessions);

        if ($sessionCount === 0) {
            return 0;
        }

        foreach ($sessions as $session) {
            $ticketsSold = $ticketRepository->count(['session' => $session, 'ticketStatus' => 'paid']);
            $hallCapacity = $session->getHall()->getHallCapacity();
            $occupancy = $hallCapacity > 0 ? ($ticketsSold / $hallCapacity) * 100 : 0;
            $totalOccupancy += $occupancy;
        }

        return round($totalOccupancy / $sessionCount, 2);
    }

    private function getReportStats(SessionRepository $sessionRepository, TicketRepository $ticketRepository, PurchaseRepository $purchaseRepository, string $period, ?string $startDate = null, ?string $endDate = null): array
    {
        $queryBuilder = $purchaseRepository->createQueryBuilder('p')
            ->select('SUM(CASE WHEN st.seatType = :economy THEN s.sessionPrice * 0.8 ELSE s.sessionPrice END) as totalRevenue, COUNT(t.id) as ticketsSold')
            ->join('p.tickets', 't')
            ->join('t.session', 's')
            ->join('t.seat', 'st')
            ->where('t.ticketStatus = :status')
            ->setParameter('status', 'paid')
            ->setParameter('economy', 'economy');
    
        $endDateTime = new \DateTime(); // Сегодняшняя дата по умолчанию
        if ($period === 'custom' && $startDate && $endDate) {
            $startDateTime = new \DateTime($startDate);
            $endDateTime = new \DateTime($endDate);
            $endDateTime->setTime(23, 59, 59); // Учитываем весь день
            $queryBuilder->andWhere('p.purchaseDate BETWEEN :startDate AND :endDate')
                         ->setParameter('startDate', $startDateTime)
                         ->setParameter('endDate', $endDateTime);
        } else {
            $startDateTime = new \DateTime();
            switch ($period) {
                case '7 days':
                    $startDateTime->modify('-7 days');
                    break;
                case '1 month':
                    $startDateTime->modify('-1 month');
                    break;
                case '3 months':
                    $startDateTime->modify('-3 months');
                    break;
                case '1 year':
                    $startDateTime->modify('-1 year');
                    break;
            }
            $queryBuilder->andWhere('p.purchaseDate BETWEEN :startDate AND :endDate')
                         ->setParameter('startDate', $startDateTime)
                         ->setParameter('endDate', $endDateTime);
        }
    
        $result = $queryBuilder->getQuery()->getSingleResult();
        $totalRevenue = (float)($result['totalRevenue'] ?? 0);
        $ticketsSold = (int)($result['ticketsSold'] ?? 0);
        $averageCheck = $ticketsSold > 0 ? round($totalRevenue / $ticketsSold, 2) : 0;
    
        // Для отладки: выведем все покупки
        $purchases = $purchaseRepository->createQueryBuilder('p')
            ->join('p.tickets', 't')
            ->join('t.session', 's')
            ->join('t.seat', 'st')
            ->where('t.ticketStatus = :status')
            ->andWhere('p.purchaseDate BETWEEN :startDate AND :endDate')
            ->setParameter('status', 'paid')
            ->setParameter('startDate', $startDateTime)
            ->setParameter('endDate', $endDateTime)
            ->getQuery()
            ->getResult();
    
        foreach ($purchases as $purchase) {
            $ticketAmounts = $purchase->getTickets()->map(function($ticket) {
                $session = $ticket->getSession();
                $seat = $ticket->getSeat();
                return $seat->getSeatType() === 'economy' ? $session->getSessionPrice() * 0.8 : $session->getSessionPrice();
            })->toArray();
            $totalAmount = array_sum($ticketAmounts); // Суммируем цены билетов
            error_log('Purchase ID: ' . $purchase->getId() . ', Date: ' . $purchase->getPurchaseDate()->format('Y-m-d H:i:s') . ', Amount: ' . $totalAmount . ', Tickets: ' . count($ticketAmounts));
        }
    
        $sessions = $sessionRepository->createQueryBuilder('s')
            ->where('s.sessionData BETWEEN :startDate AND :endDate')
            ->setParameter('startDate', $startDateTime)
            ->setParameter('endDate', $endDateTime)
            ->getQuery()
            ->getResult();
    
        $totalOccupancy = 0;
        $sessionCount = count($sessions);
    
        foreach ($sessions as $session) {
            $ticketsSold = $ticketRepository->count(['session' => $session, 'ticketStatus' => 'paid']);
            $hallCapacity = $session->getHall()->getHallCapacity();
            $occupancy = $hallCapacity > 0 ? ($ticketsSold / $hallCapacity) * 100 : 0;
            $totalOccupancy += $occupancy;
        }
    
        $averageOccupancy = $sessionCount > 0 ? round($totalOccupancy / $sessionCount, 2) : 0;
    
        return [
            'totalRevenue' => $totalRevenue,
            'ticketsSold' => $ticketsSold,
            'averageOccupancy' => $averageOccupancy,
            'averageCheck' => $averageCheck
        ];
    }

    private function getMovieReports(SessionRepository $sessionRepository, TicketRepository $ticketRepository, PurchaseRepository $purchaseRepository, string $period, ?string $startDate = null, ?string $endDate = null): array
    {
        // Получаем все фильмы и их сеансы
        $queryBuilder = $sessionRepository->createQueryBuilder('s')
            ->select('m.movieTitle, COUNT(DISTINCT s.id) as sessionCount, COALESCE(COUNT(t.id), 0) as ticketsSold, COALESCE(SUM(CASE WHEN st.seatType = :economy AND t.ticketStatus = :status THEN s.sessionPrice * 0.8 WHEN st.seatType = :regular AND t.ticketStatus = :status THEN s.sessionPrice ELSE 0 END), 0) as revenue')
            ->join('s.movie', 'm')
            ->leftJoin('s.tickets', 't')
            ->leftJoin('t.seat', 'st')
            ->groupBy('m.id')
            ->setParameter('status', 'paid')
            ->setParameter('economy', 'economy')
            ->setParameter('regular', 'regular');

        if ($period !== 'custom' || !$startDate || !$endDate) {
            $date = new \DateTime();
            switch ($period) {
                case '7 days':
                    $date->modify('-7 days');
                    break;
                case '1 month':
                    $date->modify('-1 month');
                    break;
                case '3 months':
                    $date->modify('-3 months');
                    break;
                case '1 year':
                    $date->modify('-1 year');
                    break;
            }
            $queryBuilder->andWhere('s.sessionData >= :startDate')
                         ->setParameter('startDate', $date);
        } else {
            $queryBuilder->andWhere('s.sessionData BETWEEN :startDate AND :endDate')
                         ->setParameter('startDate', new \DateTime($startDate))
                         ->setParameter('endDate', new \DateTime($endDate));
        }

        $results = $queryBuilder->getQuery()->getResult();

        $movieReports = [];

        foreach ($results as $result) {
            $sessionCount = (int)$result['sessionCount'];
            $ticketsSold = (int)$result['ticketsSold'];
            $hallCapacity = $sessionRepository->createQueryBuilder('s')
                ->select('h.hallCapacity')
                ->join('s.hall', 'h')
                ->join('s.movie', 'm')
                ->where('m.movieTitle = :title')
                ->setParameter('title', $result['movieTitle'])
                ->setMaxResults(1)
                ->getQuery()
                ->getSingleScalarResult() ?? 1;

            $occupancy = $sessionCount > 0 ? round(($ticketsSold / ($sessionCount * $hallCapacity)) * 100, 2) : 0;

            $movieReports[] = [
                'movieTitle' => $result['movieTitle'],
                'sessionCount' => $sessionCount,
                'ticketsSold' => $ticketsSold,
                'revenue' => (float)$result['revenue'],
                'occupancy' => $occupancy
            ];
        }

        return $movieReports;
    }

    #[Route('/admin/reports/export/pdf', name: 'app_admin_reports_pdf', methods: ['GET'])]
    public function exportReportsPdf(Request $request, SessionRepository $sessionRepository, TicketRepository $ticketRepository, PurchaseRepository $purchaseRepository): Response
    {
        $period = $request->query->get('period', '7 days');
        $startDate = $request->query->get('startDate');
        $endDate = $request->query->get('endDate');

        $reportStats = $this->getReportStats($sessionRepository, $ticketRepository, $purchaseRepository, $period, $startDate, $endDate);
        $movieReports = $this->getMovieReports($sessionRepository, $ticketRepository, $purchaseRepository, $period, $startDate, $endDate);

        $html = $this->renderView('account/report_pdf.html.twig', [
            'reportStats' => $reportStats,
            'movieReports' => $movieReports,
            'period' => $period,
            'startDate' => $startDate,
            'endDate' => $endDate
        ]);

        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $response = new StreamedResponse();
        $response->setCallback(function () use ($dompdf) {
            echo $dompdf->output();
        });
        $response->headers->set('Content-Type', 'application/pdf');
        $response->headers->set('Content-Disposition', 'attachment; filename="report_' . date('Ymd_His') . '.pdf"');

        return $response;
    }

    #[Route('/admin/reports/export/excel', name: 'app_admin_reports_excel', methods: ['GET'])]
    public function exportReportsExcel(Request $request, SessionRepository $sessionRepository, TicketRepository $ticketRepository, PurchaseRepository $purchaseRepository): Response
    {
        $period = $request->query->get('period', '7 days');
        $startDate = $request->query->get('startDate');
        $endDate = $request->query->get('endDate');

        $reportStats = $this->getReportStats($sessionRepository, $ticketRepository, $purchaseRepository, $period, $startDate, $endDate);
        $movieReports = $this->getMovieReports($sessionRepository, $ticketRepository, $purchaseRepository, $period, $startDate, $endDate);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Заголовки
        $sheet->setCellValue('A1', 'Отчёт по кинотеатру');
        $sheet->setCellValue('A3', 'Период:');
        $sheet->setCellValue('B3', $period === 'custom' ? "$startDate - $endDate" : $period);
        
        // Основные показатели
        $sheet->setCellValue('A5', 'Общая выручка');
        $sheet->setCellValue('B5', $reportStats['totalRevenue'] . ' ₽');
        $sheet->setCellValue('A6', 'Билетов продано');
        $sheet->setCellValue('B6', $reportStats['ticketsSold']);
        $sheet->setCellValue('A7', 'Средняя заполняемость');
        $sheet->setCellValue('B7', $reportStats['averageOccupancy'] . '%');
        $sheet->setCellValue('A8', 'Средний чек');
        $sheet->setCellValue('B8', $reportStats['averageCheck'] . ' ₽');

        // Детализация по фильмам
        $sheet->setCellValue('A10', 'Фильм');
        $sheet->setCellValue('B10', 'Сеансов');
        $sheet->setCellValue('C10', 'Билетов продано');
        $sheet->setCellValue('D10', 'Выручка');
        $sheet->setCellValue('E10', 'Заполняемость');

        $row = 11;
        foreach ($movieReports as $report) {
            $sheet->setCellValue('A' . $row, $report['movieTitle']);
            $sheet->setCellValue('B' . $row, $report['sessionCount']);
            $sheet->setCellValue('C' . $row, $report['ticketsSold']);
            $sheet->setCellValue('D' . $row, $report['revenue'] . ' ₽');
            $sheet->setCellValue('E' . $row, $report['occupancy'] . '%');
            $row++;
        }

        $response = new StreamedResponse();
        $response->setCallback(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        });
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment; filename="report_' . date('Ymd_His') . '.xlsx"');

        return $response;
    }
}