<?php

namespace App\Controller;

use App\Entity\Movie;
use App\Entity\Session;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class MovieDetailController extends AbstractController
{
    #[Route('/movie-detail/{id}', name: 'movie-detail')]
    public function index(int $id, EntityManagerInterface $entityManager): Response
    {
        $movie = $entityManager->getRepository(Movie::class)->find($id);

        if (!$movie) {
            throw $this->createNotFoundException('Фильм не найден.');
        }

        $isInRelease = $movie->getStatus() === 'current';
        $sessions = [];

        if ($isInRelease) {
            $sessions = $entityManager->getRepository(Session::class)->findBy(['movie' => $movie]);

            // Группировка сеансов по дате
            $groupedSessions = [];
            foreach ($sessions as $session) {
                $date = $session->getSessionData()->format('d.m.Y');
                $groupedSessions[$date][] = $session;
            }
        }

        return $this->render('info/movie-detail.html.twig', [
            'movie' => $movie,
            'sessions' => $groupedSessions ?? [],
            'isInRelease' => $isInRelease,
        ]);
    }
}