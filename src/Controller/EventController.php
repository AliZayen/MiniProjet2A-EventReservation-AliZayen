<?php

namespace App\Controller;

use App\Entity\Event;
use App\Form\EventType;
use App\Repository\EventRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class EventController extends AbstractController
{
    #[Route('/events', name: 'app_events_index', methods: ['GET'])]
    public function index(Request $request, EventRepository $repo): Response
    {
        $events = $repo->findBy([], ['event_date' => 'ASC']);
        $now = new \DateTimeImmutable();
        $today = $now->format('Y-m-d');
        $status = strtolower(trim((string) $request->query->get('status', 'all')));
        $search = trim((string) $request->query->get('q', ''));

        if (!in_array($status, ['all', 'today', 'upcoming', 'passed'], true)) {
            $status = 'all';
        }

        if ($status !== 'all' || $search !== '') {
            $events = array_values(array_filter(
                $events,
                static function (Event $event) use ($now, $today, $status, $search): bool {
                    $eventDate = $event->getEventDate();
                    $eventDay = $eventDate->format('Y-m-d');

                    $matchesStatus = match ($status) {
                        'today' => $eventDay === $today,
                        'upcoming' => $eventDay > $today,
                        'passed' => $eventDay < $today,
                        default => true,
                    };

                    if (!$matchesStatus) {
                        return false;
                    }

                    if ($search === '') {
                        return true;
                    }

                    $needle = mb_strtolower($search);
                    $title = mb_strtolower($event->getTitle() ?? '');
                    $location = mb_strtolower($event->getLocation() ?? '');
                    $organizer = mb_strtolower($event->getOrgnizer() ?? '');

                    return str_contains($title, $needle)
                        || str_contains($location, $needle)
                        || str_contains($organizer, $needle);
                }
            ));
        }

        usort(
            $events,
            static function (Event $a, Event $b) use ($now): int {
                $rank = static function (Event $event) use ($now): int {
                    $eventDate = $event->getEventDate();
                    $eventDay = $eventDate->format('Y-m-d');
                    $today = $now->format('Y-m-d');

                    if ($eventDay === $today) {
                        return 0; // today
                    }

                    return $eventDate > $now ? 1 : 2; // upcoming : passed
                };

                $ra = $rank($a);
                $rb = $rank($b);

                if ($ra !== $rb) {
                    return $ra <=> $rb;
                }

                // Within the same group:
                // - today + upcoming: earlier first
                // - passed: most recent first
                if ($ra === 2) {
                    return $b->getEventDate() <=> $a->getEventDate();
                }

                return $a->getEventDate() <=> $b->getEventDate();
            }
        );

        return $this->render('event/index.html.twig', [
            'events' => $events,
            'filters' => [
                'status' => $status,
                'q' => $search,
            ],
        ]);
    }

    #[Route('/events/new', name: 'app_events_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $event = new Event();
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($event);
            $em->flush();

            return $this->redirectToRoute('app_events_show', ['id' => $event->getId()]);
        }

        return $this->render('event/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/events/{id}', name: 'app_events_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(Event $event): Response
    {
        return $this->render('event/eventDetails.html.twig', [
            'event' => $event,
        ]);
    }

    #[Route('/events/{id}/edit', name: 'app_events_edit', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function edit(Request $request, Event $event, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            return $this->redirectToRoute('app_events_show', ['id' => $event->getId()]);
        }

        return $this->render('event/edit.html.twig', [
            'event' => $event,
            'form' => $form,
        ]);
    }

    #[Route('/events/{id}', name: 'app_events_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function delete(Request $request, Event $event, EntityManagerInterface $em): Response
    {
        $token = (string) $request->request->get('_token');
        if ($this->isCsrfTokenValid('delete_event_'.$event->getId(), $token)) {
            $em->remove($event);
            $em->flush();
        }

        return $this->redirectToRoute('app_events_index');
    }
}
