<?php

namespace App\DataFixtures;

use App\Entity\Event;
use App\Entity\Tag;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class EventFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $events = [
            [
                'title' => 'Symfony Performance Masterclass',
                'date' => '2026-04-05 09:30:00',
                'location' => 'Tunis Tech Hub',
                'picture' => 'https://images.unsplash.com/photo-1517048676732-d65bc937f952?auto=format&fit=crop&w=1200&q=80',
                'organizer' => 'Dev Community TN',
                'ticketPrices' => '25 TND, VIP 60 TND',
                'tags' => 'symfony, performance, backend',
                'planning' => "09:30 - Registration\n10:00 - Keynote\n11:00 - Profiling workshop",
                'details' => 'Hands-on workshop focused on Symfony optimization, caching and production tuning.',
            ],
            [
                'title' => 'AI for Web Developers',
                'date' => '2026-04-08 18:00:00',
                'location' => 'Sfax Innovation Center',
                'picture' => 'https://images.unsplash.com/photo-1677442136019-21780ecad995?auto=format&fit=crop&w=1200&q=80',
                'organizer' => 'CodeBridge',
                'ticketPrices' => 'Free, Pro Seat 20 TND',
                'tags' => 'ai, web, productivity',
                'planning' => "18:00 - Intro\n18:30 - Live demos\n19:30 - Q&A",
                'details' => 'Discover practical AI integrations for modern web applications.',
            ],
            [
                'title' => 'Frontend Architecture Summit',
                'date' => '2026-04-12 10:00:00',
                'location' => 'La Marsa Coworking',
                'picture' => 'https://images.unsplash.com/photo-1558403194-611308249627?auto=format&fit=crop&w=1200&q=80',
                'organizer' => 'Frontend Tunisia',
                'ticketPrices' => '30 TND',
                'tags' => 'frontend, architecture, javascript',
                'planning' => "10:00 - Talks\n12:30 - Lunch\n14:00 - Panel discussion",
                'details' => 'A day of advanced discussions on scalable frontend patterns.',
            ],
            [
                'title' => 'Cybersecurity Basics for Startups',
                'date' => '2026-04-15 14:00:00',
                'location' => 'Bizerte Startup Space',
                'picture' => 'https://images.unsplash.com/photo-1550751827-4bd374c3f58b?auto=format&fit=crop&w=1200&q=80',
                'organizer' => 'SecureLab',
                'ticketPrices' => '15 TND',
                'tags' => 'security, startup, devops',
                'planning' => "14:00 - Threat overview\n15:00 - Security checklist\n16:00 - Audit exercise",
                'details' => 'Essential security practices every startup team should implement.',
            ],
            [
                'title' => 'Product Design Sprint Day',
                'date' => '2026-04-18 09:00:00',
                'location' => 'Sousse Digital Factory',
                'picture' => 'https://images.unsplash.com/photo-1542744173-8e7e53415bb0?auto=format&fit=crop&w=1200&q=80',
                'organizer' => 'UX Tunisia',
                'ticketPrices' => '40 TND',
                'tags' => 'ux, product, workshop',
                'planning' => "09:00 - Sprint kickoff\n11:00 - Prototype session\n15:00 - User tests",
                'details' => 'Collaborative sprint to design and validate product ideas quickly.',
            ],
            [
                'title' => 'Cloud Native Bootcamp',
                'date' => '2026-04-22 08:30:00',
                'location' => 'Gabes Engineering School',
                'picture' => 'https://images.unsplash.com/photo-1451187580459-43490279c0fa?auto=format&fit=crop&w=1200&q=80',
                'organizer' => 'CloudOps MENA',
                'ticketPrices' => '50 TND, Student 20 TND',
                'tags' => 'cloud, devops, kubernetes',
                'planning' => "08:30 - Intro to containers\n10:30 - Kubernetes lab\n13:30 - CI/CD pipelines",
                'details' => 'Bootcamp covering the fundamentals of cloud-native development.',
            ],
            [
                'title' => 'Startup Pitch Night',
                'date' => '2026-04-25 19:00:00',
                'location' => 'Downtown Tunis',
                'picture' => 'https://images.unsplash.com/photo-1460925895917-afdab827c52f?auto=format&fit=crop&w=1200&q=80',
                'organizer' => 'Founders Club',
                'ticketPrices' => 'Free',
                'tags' => 'startup, networking, pitch',
                'planning' => "19:00 - Welcome\n19:30 - Pitches\n21:00 - Networking",
                'details' => 'An evening where founders pitch ideas to mentors and investors.',
            ],
            [
                'title' => 'Data Analytics with Python',
                'date' => '2026-04-27 17:30:00',
                'location' => 'Monastir Tech Campus',
                'picture' => 'https://images.unsplash.com/photo-1518186285589-2f7649de83e0?auto=format&fit=crop&w=1200&q=80',
                'organizer' => 'PyTunisia',
                'ticketPrices' => '35 TND',
                'tags' => 'python, data, analytics',
                'planning' => "17:30 - Intro\n18:00 - Pandas session\n19:00 - Visualization lab",
                'details' => 'A practical workshop on data wrangling and dashboards.',
            ],
            [
                'title' => 'Mobile Dev Meetup',
                'date' => '2026-05-02 16:00:00',
                'location' => 'Nabeul Creative Space',
                'picture' => 'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?auto=format&fit=crop&w=1200&q=80',
                'organizer' => 'Mobile Builders',
                'ticketPrices' => '10 TND, Premium 25 TND',
                'tags' => 'mobile, ios, android',
                'planning' => "16:00 - Lightning talks\n17:00 - Case studies\n18:00 - Networking",
                'details' => 'Community meetup about cross-platform and native mobile best practices.',
            ],
            [
                'title' => 'Open Source Contribution Day',
                'date' => '2026-05-06 11:00:00',
                'location' => 'Kairouan Coding Hub',
                'picture' => 'https://images.unsplash.com/photo-1521737604893-d14cc237f11d?auto=format&fit=crop&w=1200&q=80',
                'organizer' => 'OSS Tunisia',
                'ticketPrices' => 'Free',
                'tags' => 'open-source, git, community',
                'planning' => "11:00 - Project discovery\n12:00 - First PR challenge\n14:00 - Maintainer feedback",
                'details' => 'Guided event to help developers make their first open-source contributions.',
            ],
        ];

        $tagCache = [];

        foreach ($events as $row) {
            $event = new Event();
            $event->setTitle($row['title']);
            $event->setEventDate(new \DateTimeImmutable($row['date']));
            $event->setLocation($row['location']);
            $event->setPicture($row['picture']);
            $event->setOrgnizer($row['organizer']);
            $event->setTicketPrices($row['ticketPrices']);
            $tagNames = array_filter(array_map('trim', explode(',', $row['tags'])));
            foreach ($tagNames as $tagName) {
                $tagKey = mb_strtolower($tagName);
                if (!isset($tagCache[$tagKey])) {
                    $tag = new Tag();
                    $tag->setName($tagName);
                    $manager->persist($tag);
                    $tagCache[$tagKey] = $tag;
                }
                $event->addTag($tagCache[$tagKey]);
            }
            $event->setPlanning($row['planning']);
            $event->setDetails($row['details']);
            $event->setAccepted(true);
            $manager->persist($event);
        }

        $manager->flush();
    }
}
