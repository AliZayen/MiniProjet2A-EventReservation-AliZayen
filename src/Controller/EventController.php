<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\User;
use App\Form\EventType;
use App\Repository\EventRegistrationRepository;
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
        $currentUser = $this->getUser();
        $currentUserId = $currentUser instanceof User ? $currentUser->getId() : null;
        $now = new \DateTimeImmutable();
        $today = $now->format('Y-m-d');
        $status = strtolower(trim((string) $request->query->get('status', 'all')));
        $search = trim((string) $request->query->get('q', ''));

        if (!in_array($status, ['all', 'today', 'upcoming', 'passed'], true)) {
            $status = 'all';
        }

        if ($status !== 'all' || $search !== '' || !$this->isGranted('ROLE_ADMIN')) {
            $events = array_values(array_filter(
                $events,
                function (Event $event) use ($now, $today, $status, $search, $currentUserId): bool {
                    // Visibility rules:
                    // - admin: all events
                    // - organizer: accepted events + own pending events
                    // - participant: only accepted events
                    if (!$this->isGranted('ROLE_ADMIN')) {
                        if (!$event->isAccepted()) {
                            if (!$this->isGranted('ROLE_ORGANIZER') || $event->getOrganizerId() !== $currentUserId) {
                                return false;
                            }
                        }
                    }

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

        $pinnedEvents = [];
        if ($this->isGranted('ROLE_ADMIN')) {
            $pinnedEvents = array_values(array_filter(
                $events,
                static fn (Event $event): bool => $event->isPinned()
            ));

            $events = array_values(array_filter(
                $events,
                static fn (Event $event): bool => !$event->isPinned()
            ));
        }

        return $this->render('event/index.html.twig', [
            'events' => $events,
            'pinned_events' => $pinnedEvents,
            'filters' => [
                'status' => $status,
                'q' => $search,
            ],
        ]);
    }

    #[Route('/events/new', name: 'app_events_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        if (!$this->isGranted('ROLE_ADMIN') && !$this->isGranted('ROLE_ORGANIZER')) {
            throw $this->createAccessDeniedException('Only organizers and admins can create events.');
        }

        $event = new Event();
        $canEditOrganizer = $this->isGranted('ROLE_ADMIN');

        if (!$canEditOrganizer) {
            $currentUser = $this->getUser();
            if ($currentUser instanceof User) {
                $event->setOrgnizer((string) $currentUser->getFullName());
            }
        }

        $form = $this->createForm(EventType::class, $event, [
            'can_edit_organizer' => $canEditOrganizer,
            'can_pin_event' => $canEditOrganizer,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addFlash('warning', 'Please correct the errors in the form and try again.');
        }

        if ($form->isSubmitted() && $form->isValid()) {
            // Organizer cannot spoof another organizer name.
            $currentUser = $this->getUser();
            $currentUserId = $currentUser instanceof User ? $currentUser->getId() : null;
            if (!$canEditOrganizer) {
                if ($currentUser instanceof User) {
                    $event->setOrgnizer((string) $currentUser->getFullName());
                }
            }
            $event->setOrganizerId($currentUserId);
            $event->setAccepted(false);
            if (!$canEditOrganizer) {
                $event->setPinned(false);
            }

            $em->persist($event);
            $em->flush();

            $this->addFlash('success', 'Event created successfully.');

            return $this->redirectToRoute('app_events_show', ['id' => $event->getId()]);
        }

        return $this->render('event/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/events/my', name: 'app_events_my', methods: ['GET'])]
    public function myEvents(EventRepository $repo): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ORGANIZER');

        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException('Organizer session not found.');
        }

        $events = $repo->findBy(
            ['organizerId' => $user->getId()],
            ['event_date' => 'ASC']
        );

        return $this->render('event/my_events.html.twig', [
            'approved_events' => array_values(array_filter($events, static fn (Event $event): bool => $event->isAccepted())),
            'pending_events' => array_values(array_filter($events, static fn (Event $event): bool => !$event->isAccepted())),
        ]);
    }

    #[Route('/events/{id}', name: 'app_events_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(Event $event, EventRegistrationRepository $eventRegistrationRepository): Response
    {
        if (!$event->isAccepted() && !$this->canManageEvent($event)) {
            throw $this->createAccessDeniedException('You cannot access this event yet.');
        }

        $user = $this->getUser();
        $existingRegistration = ($user instanceof User && $this->isGranted('ROLE_PARTICIPANT'))
            ? $eventRegistrationRepository->findOneByUserAndEvent($user, $event)
            : null;

        $ticketOptions = $this->buildTicketOptions($event);

        return $this->render('event/eventDetails.html.twig', [
            'event' => $event,
            'can_manage_event' => $this->canManageEvent($event),
            'can_see_approval' => $this->isGranted('ROLE_ADMIN') || $this->canManageEvent($event),
            'can_register' => $this->isGranted('ROLE_PARTICIPANT') && $event->isAccepted() && null === $existingRegistration,
            'existing_registration' => $existingRegistration,
            'ticket_options' => $ticketOptions,
        ]);
    }

    #[Route('/events/{id}/edit', name: 'app_events_edit', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function edit(Request $request, Event $event, EntityManagerInterface $em): Response
    {
        if (!$this->canManageEvent($event)) {
            throw $this->createAccessDeniedException('You cannot edit this event.');
        }

        $canEditOrganizer = $this->isGranted('ROLE_ADMIN');
        $form = $this->createForm(EventType::class, $event, [
            'can_edit_organizer' => $canEditOrganizer,
            'can_pin_event' => $canEditOrganizer,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addFlash('warning', 'Please correct the errors in the form and try again.');
        }

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$canEditOrganizer) {
                $currentUser = $this->getUser();
                if ($currentUser instanceof User) {
                    $event->setOrgnizer((string) $currentUser->getFullName());
                    $event->setOrganizerId($currentUser->getId());
                }
            }
            $em->flush();

            $this->addFlash('success', 'Event updated successfully.');

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
        if (!$this->canManageEvent($event)) {
            throw $this->createAccessDeniedException('You cannot delete this event.');
        }

        $token = (string) $request->request->get('_token');
        if ($this->isCsrfTokenValid('delete_event_'.$event->getId(), $token)) {
            $em->remove($event);
            $em->flush();
            $this->addFlash('success', 'Event deleted successfully.');
        } else {
            $this->addFlash('danger', 'Could not delete the event. Please try again.');
        }

        return $this->redirectToRoute('app_events_index');
    }

    #[Route('/events/{id}/accept', name: 'app_events_accept', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function accept(Request $request, Event $event, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $token = (string) $request->request->get('_token');
        if ($this->isCsrfTokenValid('accept_event_'.$event->getId(), $token)) {
            $event->setAccepted(true);
            $em->flush();
            $this->addFlash('success', 'Event approved and published.');
        } else {
            $this->addFlash('danger', 'Could not approve the event. Please try again.');
        }

        return $this->redirectToRoute('app_events_show', ['id' => $event->getId()]);
    }

    private function canManageEvent(Event $event): bool
    {
        if ($this->isGranted('ROLE_ADMIN')) {
            return true;
        }

        if (!$this->isGranted('ROLE_ORGANIZER')) {
            return false;
        }

        $user = $this->getUser();
        if (!$user instanceof User) {
            return false;
        }

        return $event->getOrganizerId() === $user->getId();
    }

    /**
     * @return string[]
     */
    private function buildTicketOptions(Event $event): array
    {
        $raw = trim((string) $event->getTicketPrices());
        if ($raw === '') {
            return ['General'];
        }
        $split = preg_split('/[;,]+/', $raw);
        $parts = array_filter(array_map('trim', \is_array($split) ? $split : []));
        if ($parts === []) {
            return ['General'];
        }

        return array_values($parts);
    }
}
