<?php

namespace App\Controller;

use App\Entity\Goer;
use App\Entity\Refund;
use App\Entity\Ticket;
use App\Repository\TicketRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

final class GoerActionController extends AbstractController
{
    private $entityManager;
    private $ticketRepository;
    private $passwordHasher;

    public function __construct(
        EntityManagerInterface $entityManager,
        TicketRepository $ticketRepository,
        UserPasswordHasherInterface $passwordHasher
    ) {
        $this->entityManager = $entityManager;
        $this->ticketRepository = $ticketRepository;
        $this->passwordHasher = $passwordHasher;
    }

    #[Route('/goer/refund', name: 'goer_refund', methods: ['POST'])]
    public function submitRefund(Request $request): JsonResponse
    {
        if (!$this->isGranted('ROLE_GOER')) {
            throw new AccessDeniedException('Требуется авторизация с ролью Goer');
        }

        $data = json_decode($request->getContent(), true);
        $ticketId = $data['ticketId'] ?? null;
        $reason = $data['reason'] ?? '';

        if (!$ticketId || !$reason) {
            return new JsonResponse(['error' => 'Неверные данные'], 400);
        }

        $ticket = $this->ticketRepository->find($ticketId);
        if (!$ticket || $ticket->getPurchase()->getGoer() !== $this->getUser()) {
            return new JsonResponse(['error' => 'Билет не найден или не принадлежит вам'], 404);
        }

        if ($ticket->getTicketStatus() !== 'paid') {
            return new JsonResponse(['error' => 'Билет уже возвращен или недействителен'], 400);
        }

        $sessionDate = $ticket->getSession()->getSessionData();
        $now = new \DateTime();
        $twoHoursBefore = (clone $sessionDate)->modify('-2 hours');
        if ($now > $twoHoursBefore) {
            return new JsonResponse(['error' => 'Возврат возможен не позднее чем за 2 часа до сеанса'], 400);
        }

        $ticketPrice = $ticket->getSeat()->getSeatType() === 'economy'
            ? $ticket->getSession()->getSessionPrice() * 0.8
            : $ticket->getSession()->getSessionPrice();

        $refund = new Refund();
        $refund->setTicket($ticket);
        $refund->setGoer($this->getUser());
        $refund->setRefundReason($reason);
        $refund->setRefundStatus('pending');
        $refund->setRefundDate(new \DateTime());

        $ticket->setTicketStatus('refunded');
        $ticket->setRefund($refund);

        $this->entityManager->persist($refund);
        $this->entityManager->persist($ticket);
        $this->entityManager->flush();

        return new JsonResponse(['success' => true]);
    }

    #[Route('/goer/profile/update', name: 'goer_profile_update', methods: ['POST'])]
    public function updateProfile(Request $request): JsonResponse
    {
        if (!$this->isGranted('ROLE_GOER')) {
            throw new AccessDeniedException('Требуется авторизация с ролью Goer');
        }

        /** @var Goer $goer */
        $goer = $this->getUser();
        $data = json_decode($request->getContent(), true);

        $name = $data['name'] ?? null;
        $phone = $data['phone'] ?? null;
        $password = $data['password'] ?? null;

        if (!$name) {
            return new JsonResponse(['error' => 'Имя обязательно'], 400);
        }

        $goer->setGoerName($name);
        if ($phone) {
            $goer->setGoerPhone($phone);
        }
        if ($password) {
            $goer->setGoerPassword($this->passwordHasher->hashPassword($goer, $password));
        }

        $this->entityManager->persist($goer);
        $this->entityManager->flush();

        return new JsonResponse(['success' => true]);
    }
}