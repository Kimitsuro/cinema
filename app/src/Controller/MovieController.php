<?php

namespace App\Controller;

use App\Repository\MovieRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class MovieController extends AbstractController
{
    #[Route('/movie', name: 'movie')]
    public function index(Request $request, MovieRepository $movieRepository): Response
    {
        // Получаем параметры фильтров из GET-запроса
        $genre = $request->query->get('genre', '');
        $age = $request->query->get('age', '');
        $format = $request->query->get('format', '');
        $status = $request->query->get('status', 'current'); // Устанавливаем значение по умолчанию
        $sort = $request->query->get('sort', 'title');

        // Формируем критерии для фильтрации
        $criteria = [];
        if ($genre) {
            $criteria['movieGenre'] = $genre;
        }
        if ($age) {
            $criteria['movieAge'] = (int)$age;
        }
        if ($format) {
            $criteria['movieFormat'] = $format;
        }
        if ($status) {
            $criteria['status'] = $status;
        }

        // Определяем сортировку
        $orderBy = [];
        switch ($sort) {
            case 'duration':
                $orderBy['movieDuration'] = 'ASC';
                break;
            case 'age':
                $orderBy['movieAge'] = 'ASC';
                break;
            case 'title':
            default:
                $orderBy['movieTitle'] = 'ASC';
                break;
        }

        // Получаем фильмы с учетом фильтров
        $movies = $movieRepository->findBy($criteria, $orderBy);

        // Получаем уникальные значения для фильтров
        $genres = $movieRepository->createQueryBuilder('m')
            ->select('DISTINCT m.movieGenre')
            ->orderBy('m.movieGenre', 'ASC')
            ->getQuery()
            ->getSingleColumnResult();
        $ages = $movieRepository->createQueryBuilder('m')
            ->select('DISTINCT m.movieAge')
            ->orderBy('m.movieAge', 'ASC')
            ->getQuery()
            ->getSingleColumnResult();
        $formats = $movieRepository->createQueryBuilder('m')
            ->select('DISTINCT m.movieFormat')
            ->orderBy('m.movieFormat', 'ASC')
            ->getQuery()
            ->getSingleColumnResult();

        return $this->render('main/movies.html.twig', [
            'movies' => $movies,
            'genres' => $genres,
            'ages' => $ages,
            'formats' => $formats,
            'currentGenre' => $genre,
            'currentAge' => $age,
            'currentFormat' => $format,
            'currentStatus' => $status,
            'currentSort' => $sort,
        ]);
    }
}