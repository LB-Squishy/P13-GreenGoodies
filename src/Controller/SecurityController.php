<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class SecurityController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private TokenStorageInterface $tokenStorage,
    ) {}

    #[Route('/inscription', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        if ($this->getUser()) {
            flash()->info('Vous êtes déjà connecté.');
            return $this->redirectToRoute('app_accueil');
        }

        $user = new User();
        $user->setRoles(['ROLE_USER']);

        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();

            // encode the plain password
            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));

            $entityManager->persist($user);
            $entityManager->flush();

            flash()->success('Votre compte a été créé avec succès ! Vous pouvez maintenant vous connecter.');

            // do anything else you need here, like send an email

            return $this->redirectToRoute('app_login');
        }

        return $this->render('auth/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }

    #[Route(path: '/connexion', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            flash()->info('Vous êtes déjà connecté.');
            return $this->redirectToRoute('app_accueil');
        }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        if ($error) {
            flash()->error('Identifiants incorrects. Veuillez réessayer.');
        }

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('auth/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    #[Route(path: '/deleteAccount', name: 'app_delete_account', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function deleteAccount(Request $request): Response
    {
        /** @var \App\Entity\User|null $user */
        $user = $this->getUser();

        // Vérifie le token CSRF
        $token = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('app_delete_account', $token)) {
            flash()->error('Une erreur est survenue.');
            return $this->redirectToRoute('app_accueil');
        }

        // Supprimer le panier de l'utilisateur
        $cart = $user->getCart();
        if ($cart) {
            $this->entityManager->remove($cart);
        }

        // Supprimer les commandes associées de l'utilisateur
        foreach ($user->getOrders() as $order) {
            $this->entityManager->remove($order);
        }

        // Supprimer l'utilisateur
        $this->entityManager->remove($user);
        $this->entityManager->flush();

        // Déconnecter l'utilisateur après la suppression du compte
        $this->tokenStorage->setToken(null);
        $request->getSession()->invalidate();

        flash()->success('Votre compte et toutes vos données associées ont été supprimées avec succès.');
        return $this->redirectToRoute('app_accueil');
    }

    #[Route(path: '/api-access-enable', name: 'app_api_access_enable', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function enableApiAccess(Request $request): Response
    {
        /** @var \App\Entity\User|null $user */
        $user = $this->getUser();

        // Vérifie le token CSRF
        $token = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('app_api_access_enable', $token)) {
            flash()->error('Une erreur est survenue.');
            return $this->redirectToRoute('app_order_show');
        }

        $user->setApiAccessEnabled(true);
        $this->entityManager->flush();

        flash()->success('Votre accès API a été activé avec succès.');
        return $this->redirectToRoute('app_order_show');
    }

    #[Route(path: '/api-access-disable', name: 'app_api_access_disable', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function disableApiAccess(Request $request): Response
    {
        /** @var \App\Entity\User|null $user */
        $user = $this->getUser();

        // Vérifie le token CSRF
        $token = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('app_api_access_disable', $token)) {
            flash()->error('Une erreur est survenue.');
            return $this->redirectToRoute('app_order_show');
        }

        $user->setApiAccessEnabled(false);
        $this->entityManager->flush();

        flash()->success('Votre accès API a été désactivé avec succès.');
        return $this->redirectToRoute('app_order_show');
    }
}
