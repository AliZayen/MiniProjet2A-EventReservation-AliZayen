<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\SignupType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

final class SignupController extends AbstractController
{
    #[Route('/register', name: 'app_register', methods: ['GET', 'POST'])]
    public function register(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $hasher,
    ): Response {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        $user = new User();
        $form = $this->createForm(SignupType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plain = (string) $form->get('plainPassword')->getData();
            $user->setPassword($hasher->hashPassword($user, $plain));

            if ($user->getType() === User::TYPE_ORGANIZER) {
                $user->setOrganizerApproved(false);
            } else {
                $user->setOrganizerApproved(true);
            }

            $em->persist($user);
            $em->flush();

            if ($user->getType() === User::TYPE_ORGANIZER) {
                $this->addFlash('success', 'Your account was created. An administrator must approve your organizer profile before you can sign in.');
            } else {
                $this->addFlash('success', 'Your account was created. You can sign in now.');
            }

            return $this->redirectToRoute('app_login');
        }

        return $this->render('security/register.html.twig', [
            'form' => $form,
        ]);
    }
}
