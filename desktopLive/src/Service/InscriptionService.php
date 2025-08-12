<?php

namespace App\Service;

use App\Entity\Users;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use App\Security\LoginAuthenticator;
use Symfony\Component\HttpFoundation\Response;

class InscriptionService
{
    public function __construct(
        private EntityManagerInterface $em,
        private UserPasswordHasherInterface $hasher,
        private UserAuthenticatorInterface $userAuthenticator,
        private LoginAuthenticator $authenticator
    ) {}

    public function register(Users $user, string $plainPassword, Request $request): Response
    {
        $user->setPassword($this->hasher->hashPassword($user, $plainPassword));
        $this->em->persist($user);
        $this->em->flush();

        $request->attributes->set('from_registration', true);
        $request->attributes->set('is_seller', $user->isSeller());

        return $this->userAuthenticator->authenticateUser(
            $user,
            $this->authenticator,
            $request
        );
    }
}
