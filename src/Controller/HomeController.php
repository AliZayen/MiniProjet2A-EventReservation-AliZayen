<?php

namespace App\Controller;

use App\Repository\EventRegistrationRepository;
use App\Repository\EventRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home', methods: ['GET'])]
    public function index(
        Request $request,
        EventRepository $events,
        UserRepository $users,
        EventRegistrationRepository $registrations,
    ): Response {
        if ($request->query->get('signed_out') === '1') {
            $this->addFlash('success', 'You have been signed out.');

            return $this->redirectToRoute('app_home');
        }

        return $this->render('home/index.html.twig', [
            'nearestEvents' => $events->findNearestUpcoming(6),
            'stats' => [
                'events' => $events->count([]),
                'users' => $users->count([]),
                'registrations' => $registrations->count([]),
            ],
        ]);
    }
}

