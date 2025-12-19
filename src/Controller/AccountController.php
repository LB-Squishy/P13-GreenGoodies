<?php

namespace App\Controller;

use App\Form\Account\ApiAccessDisableFormType;
use App\Form\Account\ApiAccessEnableFormType;
use App\Form\Account\DeleteAccountFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

#[Route('/compte')]
final class AccountController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private TokenStorageInterface $tokenStorage,
    ) {}

    #[Route(path: '/supprimer', name: 'app_delete_account', methods: ['POST'])]
    public function deleteAccount(Request $request): Response
    {
        $form = $this->createForm(DeleteAccountFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var \App\Entity\User|null $user */
            $user = $this->getUser();

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
        flash()->error('Une erreur est survenue lors de la suppression de votre compte.');
        return $this->redirectToRoute('app_order_show');
    }

    #[Route(path: '/acces-api-active', name: 'app_api_access_enable', methods: ['POST'])]
    public function enableApiAccess(Request $request): Response
    {
        $form = $this->createForm(ApiAccessEnableFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var \App\Entity\User|null $user */
            $user = $this->getUser();
            $roles = $user->getRoles();
            if (in_array('ROLE_API_ACCESS', $roles, true)) {
                flash()->info('Votre accès API est déjà activé.');
                return $this->redirectToRoute('app_order_show');
            }
            $roles[] = 'ROLE_API_ACCESS';
            $user->setRoles($roles);

            $this->entityManager->flush();

            $this->refreshSecurityToken($user);

            flash()->success('Votre accès API a été activé avec succès.');
            return $this->redirectToRoute('app_order_show');
        }

        flash()->error('Une erreur est survenue.');
        return $this->redirectToRoute('app_order_show');
    }

    #[Route(path: '/acces-api-desactive', name: 'app_api_access_disable', methods: ['POST'])]
    public function disableApiAccess(Request $request): Response
    {
        $form = $this->createForm(ApiAccessDisableFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var \App\Entity\User|null $user */
            $user = $this->getUser();
            $roles = $user->getRoles();
            if (!in_array('ROLE_API_ACCESS', $roles, true)) {
                flash()->info('Votre accès API est déjà désactivé.');
                return $this->redirectToRoute('app_order_show');
            }
            $roles = array_filter($roles, fn($role) => $role !== 'ROLE_API_ACCESS');
            $user->setRoles($roles);

            $this->refreshSecurityToken($user);

            $this->entityManager->flush();

            flash()->success('Votre accès API a été désactivé avec succès.');
            return $this->redirectToRoute('app_order_show');
        }
        flash()->error('Une erreur est survenue.');
        return $this->redirectToRoute('app_order_show');
    }

    private function refreshSecurityToken(\App\Entity\User $user): void
    {
        $token = new UsernamePasswordToken(
            $user,
            'main',
            $user->getRoles()
        );
        $this->tokenStorage->setToken($token);
    }
}
