<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\MovieRepository;


final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(MovieRepository $movieRepository): Response
    {
        // Получение текущих фильмов
        $currentMovies = $movieRepository->findBy(['status' => 'current'], ['id' => 'ASC'], 3);

        // Получение предстоящих фильмов
        $upcomingMovies = $movieRepository->findBy(['status' => 'upcoming'], ['id' => 'ASC'], 3);

        return $this->render('home/index.html.twig', [
            'currentMovies' => $currentMovies,
            'upcomingMovies' => $upcomingMovies,
        ]);
    }
}
