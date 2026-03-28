<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

final class UserController extends AbstractController
{
    #[Route('/users', name: 'app_users_index', methods: ['GET'])]
    public function index(UserRepository $repo): Response
    {
        return $this->render('user/index.html.twig', [
            'users' => $repo->findBy([], ['id' => 'DESC']),
        ]);
    }

    #[Route('/users/new', name: 'app_users_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $hasher): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = (string) $form->get('plainPassword')->getData();
            if ($plainPassword === '') {
                $form->get('plainPassword')->addError(new \Symfony\Component\Form\FormError('Password is required for a new user.'));
            } else {
                $user->setPassword($hasher->hashPassword($user, $plainPassword));
                $em->persist($user);
                $em->flush();

                return $this->redirectToRoute('app_users_show', ['id' => $user->getId()]);
            }
        }

        return $this->render('user/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/users/{id}', name: 'app_users_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(User $user): Response
    {
        return $this->render('user/show.html.twig', [
            'userEntity' => $user,
        ]);
    }

    #[Route('/users/{id}/edit', name: 'app_users_edit', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function edit(Request $request, User $user, EntityManagerInterface $em, UserPasswordHasherInterface $hasher): Response
    {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = (string) $form->get('plainPassword')->getData();
            if ($plainPassword !== '') {
                $user->setPassword($hasher->hashPassword($user, $plainPassword));
            }

            $em->flush();

            return $this->redirectToRoute('app_users_show', ['id' => $user->getId()]);
        }

        return $this->render('user/edit.html.twig', [
            'userEntity' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/users/{id}', name: 'app_users_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function delete(Request $request, User $user, EntityManagerInterface $em): Response
    {
        $token = (string) $request->request->get('_token');
        if ($this->isCsrfTokenValid('delete_user_'.$user->getId(), $token)) {
            $em->remove($user);
            $em->flush();
        }

        return $this->redirectToRoute('app_users_index');
    }
}

