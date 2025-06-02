<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\SessionRepository;

final class SessionController extends AbstractController
{
    #[Route('/session', name: 'session')]
    public function index(SessionRepository $sessionRepository, Request $request): Response
    {
        // Получение всех сеансов из базы данных
        $sessions = $sessionRepository->findAll();

        // Текущая дата и время
        $currentDateTime = new \DateTime('now', new \DateTimeZone('Europe/Moscow'));

        // Получение уникальных дат из сеансов
        $availableDates = [];
        foreach ($sessions as $session) {
            $sessionDateTime = $session->getSessionData();
            // Пропускаем сеансы, которые уже прошли
            if ($sessionDateTime < $currentDateTime) {
                continue;
            }
            $date = $sessionDateTime->format('Y-m-d');
            $availableDates[$date] = new \DateTime($date);
        }

        // Преобразуем ключи в массив значений
        $availableDates = array_values($availableDates);

        // Сортировка дат
        usort($availableDates, function ($a, $b) {
            return $a <=> $b;
        });

        // Получение выбранной даты из запроса или ближайшей доступной даты
        $selectedDate = $request->query->get('date');
        if (!$selectedDate && !empty($availableDates)) {
            $selectedDate = $availableDates[0]->format('Y-m-d'); // Ближайшая дата
        }

        // Фильтрация сеансов по выбранной дате
        $groupedSessions = [];
        foreach ($sessions as $session) {
            $date = $session->getSessionData()->format('Y-m-d');
            if ($date !== $selectedDate) {
                continue;
            }

            $movieTitle = $session->getMovie()->getMovieTitle();

            if (!isset($groupedSessions[$date])) {
                $groupedSessions[$date] = [];
            }

            if (!isset($groupedSessions[$date][$movieTitle])) {
                $groupedSessions[$date][$movieTitle] = [];
            }

            $groupedSessions[$date][$movieTitle][] = $session;
        }

        return $this->render('main/sessions.html.twig', [
            'groupedSessions' => $groupedSessions,
            'availableDates' => $availableDates,
            'selectedDate' => $selectedDate,
        ]);
    }
}