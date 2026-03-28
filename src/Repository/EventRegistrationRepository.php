<?php

namespace App\Repository;

use App\Entity\Event;
use App\Entity\EventRegistration;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<EventRegistration>
 */
class EventRegistrationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EventRegistration::class);
    }

    public function findOneByUserAndEvent(User $user, Event $event): ?EventRegistration
    {
        return $this->findOneBy(['user' => $user, 'event' => $event]);
    }

    /**
     * @return EventRegistration[]
     */
    public function findByEventOrdered(Event $event): array
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.event = :event')
            ->setParameter('event', $event)
            ->orderBy('r.reservedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return EventRegistration[]
     */
    public function findByParticipantOrdered(User $user): array
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.user = :user')
            ->setParameter('user', $user)
            ->orderBy('r.reservedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function countForEvent(Event $event): int
    {
        return (int) $this->createQueryBuilder('r')
            ->select('COUNT(r.id)')
            ->andWhere('r.event = :event')
            ->setParameter('event', $event)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countPaidForEvent(Event $event): int
    {
        return (int) $this->createQueryBuilder('r')
            ->select('COUNT(r.id)')
            ->andWhere('r.event = :event')
            ->andWhere('r.paymentStatus = :paid')
            ->setParameter('event', $event)
            ->setParameter('paid', EventRegistration::PAYMENT_PAID)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countReservedTodayForEvent(Event $event): int
    {
        $start = new \DateTimeImmutable('today');
        $end = $start->modify('+1 day');

        return (int) $this->createQueryBuilder('r')
            ->select('COUNT(r.id)')
            ->andWhere('r.event = :event')
            ->andWhere('r.reservedAt >= :start')
            ->andWhere('r.reservedAt < :end')
            ->setParameter('event', $event)
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @return EventRegistration[]
     */
    public function findAllOrdered(): array
    {
        return $this->createQueryBuilder('r')
            ->orderBy('r.reservedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
