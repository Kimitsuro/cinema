<?php

namespace App\Controller;

use App\Repository\HallRepository;
use App\Repository\SeatRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HallController extends AbstractController
{
    #[Route('/hall', name: 'hall')]
    public function index(HallRepository $hallRepository, SeatRepository $seatRepository): Response
    {
        // Fetch all halls
        $halls = $hallRepository->findAll();

        // Prepare data for each hall
        $hallData = [];
        foreach ($halls as $hall) {
            // Fetch seats for the hall, sorted by row and number
            $seats = $seatRepository->findBy(
                ['hall' => $hall],
                ['seatRow' => 'ASC', 'searNumber' => 'ASC']
            );

            // Group seats by row
            $seatsByRow = [];
            $economyCount = 0;
            $regularCount = 0;
            foreach ($seats as $seat) {
                $row = $seat->getSeatRow();
                if (!isset($seatsByRow[$row])) {
                    $seatsByRow[$row] = [];
                }
                $seatsByRow[$row][] = $seat;
                if ($seat->getSeatType() === 'economy') {
                    $economyCount++;
                } else {
                    $regularCount++;
                }
            }

            $hallData[] = [
                'hall' => $hall,
                'seatsByRow' => $seatsByRow,
                'economyCount' => $economyCount,
                'regularCount' => $regularCount,
            ];
        }

        return $this->render('main/halls.html.twig', [
            'hallData' => $hallData,
        ]);
    }
}