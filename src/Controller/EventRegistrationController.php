<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\EventRegistration;
use App\Entity\User;
use App\Repository\EventRegistrationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Attribute\Route;

final class EventRegistrationController extends AbstractController
{
    #[Route('/events/{id}/register', name: 'app_event_register', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function register(
        Request $request,
        Event $event,
        EntityManagerInterface $em,
        EventRegistrationRepository $registrations,
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_PARTICIPANT');

        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        if (!$event->isAccepted()) {
            throw $this->createAccessDeniedException('This event is not open for registration.');
        }

        if ($registrations->findOneByUserAndEvent($user, $event)) {
            $this->addFlash('warning', 'You are already registered for this event.');

            return $this->redirectToRoute('app_events_show', ['id' => $event->getId()]);
        }

        $token = (string) $request->request->get('_token');
        if (!$this->isCsrfTokenValid('register_event_'.$event->getId(), $token)) {
            throw $this->createAccessDeniedException('Invalid CSRF token.');
        }

        $ticketType = trim((string) $request->request->get('ticket_type', 'General'));
        if ($ticketType === '') {
            $ticketType = 'General';
        }

        $registration = new EventRegistration();
        $registration->setUser($user);
        $registration->setEvent($event);
        $registration->setPaymentStatus(EventRegistration::PAYMENT_UNPAID);
        $registration->setReservedAt(new \DateTimeImmutable());
        $registration->setTicketType($ticketType);

        $em->persist($registration);
        $em->flush();

        $this->addFlash('success', 'Your registration was recorded.');

        return $this->redirectToRoute('app_events_show', ['id' => $event->getId()]);
    }

    #[Route('/registrations/my', name: 'app_registrations_my', methods: ['GET'])]
    public function myRegistrations(EventRegistrationRepository $registrations): Response
    {
        $this->denyAccessUnlessGranted('ROLE_PARTICIPANT');

        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        return $this->render('registration/my.html.twig', [
            'registrations' => $registrations->findByParticipantOrdered($user),
        ]);
    }

    #[Route('/events/{id}/registrations', name: 'app_event_registrations', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function forEvent(Event $event, EventRegistrationRepository $registrations): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        if (!$this->canViewEventRegistrations($event)) {
            throw $this->createAccessDeniedException();
        }

        $items = $registrations->findByEventOrdered($event);

        return $this->render('registration/event_registrations.html.twig', [
            'event' => $event,
            'registrations' => $items,
            'stats' => [
                'total' => $registrations->countForEvent($event),
                'paid' => $registrations->countPaidForEvent($event),
                'unpaid' => $registrations->countForEvent($event) - $registrations->countPaidForEvent($event),
                'today' => $registrations->countReservedTodayForEvent($event),
            ],
        ]);
    }

    #[Route('/events/{id}/registrations/export', name: 'app_event_registrations_export', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function exportCsv(Event $event, EventRegistrationRepository $registrations): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        if (!$this->canViewEventRegistrations($event)) {
            throw $this->createAccessDeniedException();
        }

        $items = $registrations->findByEventOrdered($event);
        $filename = sprintf('event-%d-registrations.csv', $event->getId());

        $response = new StreamedResponse(function () use ($items): void {
            $out = fopen('php://output', 'w');
            if ($out === false) {
                return;
            }
            fputcsv($out, ['id', 'participant_email', 'participant_name', 'phone', 'ticket_type', 'payment_status', 'reserved_at']);
            foreach ($items as $r) {
                $u = $r->getUser();
                fputcsv($out, [
                    $r->getId(),
                    $u?->getEmail() ?? '',
                    $u?->getFullName() ?? '',
                    $u?->getPhoneNumber() ?? '',
                    $r->getTicketType() ?? '',
                    $r->getPaymentStatus(),
                    $r->getReservedAt()?->format(\DateTimeInterface::ATOM) ?? '',
                ]);
            }
            fclose($out);
        });

        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="'.$filename.'"');

        return $response;
    }

    #[Route('/registrations/{id}/payment', name: 'app_registration_payment', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function setPayment(
        Request $request,
        EventRegistration $registration,
        EntityManagerInterface $em,
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $event = $registration->getEvent();
        if (null === $event || !$this->canViewEventRegistrations($event)) {
            throw $this->createAccessDeniedException();
        }

        $token = (string) $request->request->get('_token');
        if (!$this->isCsrfTokenValid('registration_payment_'.$registration->getId(), $token)) {
            throw $this->createAccessDeniedException('Invalid CSRF token.');
        }

        $status = (string) $request->request->get('payment_status', EventRegistration::PAYMENT_UNPAID);
        $registration->setPaymentStatus(
            $status === EventRegistration::PAYMENT_PAID ? EventRegistration::PAYMENT_PAID : EventRegistration::PAYMENT_UNPAID
        );
        $em->flush();
        $this->addFlash('success', 'Payment status updated.');

        return $this->redirectToRoute('app_event_registrations', ['id' => $event->getId()]);
    }

    #[Route('/admin/registrations', name: 'app_admin_registrations', methods: ['GET'])]
    public function adminAll(EventRegistrationRepository $registrations): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        return $this->render('registration/admin_all.html.twig', [
            'registrations' => $registrations->findAllOrdered(),
        ]);
    }

    private function canViewEventRegistrations(Event $event): bool
    {
        if ($this->isGranted('ROLE_ADMIN')) {
            return true;
        }

        $user = $this->getUser();
        if (!$user instanceof User || !$this->isGranted('ROLE_ORGANIZER')) {
            return false;
        }

        return $event->getOrganizerId() === $user->getId();
    }
}
