<?php

namespace App\DataFixtures;

use App\Entity\Product;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $userPasswordHasher;

    public function __construct(UserPasswordHasherInterface $userPasswordHasher)
    {
        $this->userPasswordHasher = $userPasswordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        // Création d'un user "normal"
        $user = new User();
        $user
            ->setEmail('user@geengoodies.com')
            ->setRoles(['ROLE_USER'])
            ->setPassword($this->userPasswordHasher->hashPassword($user, 'user'))
            ->setLastName('Doe')
            ->setFirstName('John')
            ->setCguAccepted(true);
        $manager->persist($user);

        // Création d'un user "admin"
        $user = new User();
        $user
            ->setEmail('admin@geengoodies.com')
            ->setRoles(['ROLE_USER', 'ROLE_API_ACCESS'])
            ->setPassword($this->userPasswordHasher->hashPassword($user, 'admin'))
            ->setLastName('Dupond')
            ->setFirstName('Jane')
            ->setCguAccepted(true);
        $manager->persist($user);

        // Création de products
        $productData = [
            [
                'name' => 'Kit d\'hygiène recyclable',
                'shortDescription' => 'Pour une salle de bain éco-friendly',
                'fullDescription' => 'Un kit complet comprenant une brosse à dents en bambou, un savon naturel et un rasoir réutilisable. Tous les éléments sont conçus pour minimiser l\'impact environnemental. Idéal pour ceux qui souhaitent adopter un mode de vie plus durable.',
                'price' => 24.99,
                'picture' => '78e3e6600f07c28090abf9ac0d263bf4473ba9a6.webp',
                'isActive' => true,
            ],
            [
                'name' => 'Shot Tropical',
                'shortDescription' => 'Fruits frais, pressés à froid',
                'fullDescription' => 'Un shot revitalisant à base de mangue, ananas et curcuma, parfait pour booster votre énergie le matin. Pressé à froid pour conserver toutes les vitamines et saveurs naturelles. Sans sucre ajouté ni conservateurs.',
                'price' => 4.50,
                'picture' => '0d36171c14b95ab56656af06d7a69ab2d9ee44d0.webp',
                'isActive' => true,
            ],
            [
                'name' => 'Gourde en bois',
                'shortDescription' => '50cl, bois d’olivier',
                'fullDescription' => 'Une gourde élégante fabriquée en bois d\'olivier durable, idéale pour rester hydraté tout au long de la journée. Sa capacité de 50cl la rend parfaite pour les déplacements, le sport ou le bureau. Un choix écologique et stylé pour vos boissons préférées.',
                'price' => 16.90,
                'picture' => '64e347aa5c542819963e653209f118071a79567b.webp',
                'isActive' => true,
            ],
            [
                'name' => 'Disques Démaquillants x3',
                'shortDescription' => 'Solution efficace pour vous démaquiller en douceur',
                'fullDescription' => 'Ces disques démaquillants réutilisables sont la solution idéale pour un démaquillage doux et respectueux de l\'environnement. Fabriqués en coton bio, ils sont lavables en machine et durables. Dites adieu aux disques jetables et optez pour une routine beauté éco-responsable.',
                'price' => 19.90,
                'picture' => 'fb8cafcb83102a01875727a5366e6a6fa9a75445.webp',
                'isActive' => true,
            ],
            [
                'name' => 'Bougie Lavande & Patchouli',
                'shortDescription' => 'Cire naturelle',
                'fullDescription' => 'Cette bougie est fabriquée à partir de cire naturelle et parfumée à la lavande et au patchouli. Elle crée une ambiance apaisante et relaxante, parfaite pour vos moments de détente.',
                'price' => 32.00,
                'picture' => 'dc415efac4700f712d7bef2fade2b494d4d2cd98.webp',
                'isActive' => true,
            ],
            [
                'name' => 'Brosse à dent',
                'shortDescription' => 'Bois de hêtre rouge issu de forêts gérées durablement',
                'fullDescription' => 'Cette brosse à dents est fabriquée en bois de hêtre rouge, un matériau durable et écologique. Elle est conçue pour offrir un brossage efficace tout en respectant l\'environnement. Un choix idéal pour une hygiène bucco-dentaire responsable.',
                'price' => 5.40,
                'picture' => '486b684fedebd52c007e82d992ba79ed0df88597.webp',
                'isActive' => true,
            ],
            [
                'name' => 'Kit couvert en bois',
                'shortDescription' => 'Revêtement Bio en olivier & sac de transport',
                'fullDescription' => 'Ce kit de couverts en bois est parfait pour les repas en plein air. Fabriqué à partir de bois d\'olivier durable, il est livré avec un sac de transport pratique. Un choix écologique pour vos pique-niques et barbecues.',
                'price' => 12.30,
                'picture' => '0b156bb49499862906fd402eb6ed9de766d7b289.webp',
                'isActive' => true,
            ],
            [
                'name' => 'Nécessaire, déodorant Bio',
                'shortDescription' => '50ml déodorant à l’eucalyptus',
                'fullDescription' => 'Ce déodorant bio est fabriqué à partir d\'ingrédients naturels et biologiques, offrant une protection efficace contre les odeurs tout en respectant la peau. Son parfum frais d\'eucalyptus vous procure une sensation de bien-être tout au long de la journée.',
                'price' => 8.50,
                'picture' => 'ba20c61715cd7d442e6e9686d8678f0c1236a01f.webp',
                'isActive' => true,
            ],
            [
                'name' => 'Savon Bio',
                'shortDescription' => 'Thé, Orange & Girofle',
                'fullDescription' => 'Ce savon bio est fabriqué à partir d\'ingrédients naturels et biologiques, offrant une expérience de nettoyage douce et respectueuse de la peau. Son parfum subtil de thé, d\'orange et de girofle vous enveloppe dans une sensation de bien-être.',
                'price' => 18.90,
                'picture' => 'b3e02521fa1216c1cf674ce8c25148d240677fed.webp',
                'isActive' => true,
            ],
        ];

        foreach ($productData as $data) {
            $product = new Product();
            $product
                ->setName($data['name'])
                ->setShortDescription($data['shortDescription'])
                ->setFullDescription($data['fullDescription'])
                ->setPrice($data['price'])
                ->setPicture($data['picture'])
                ->setIsActive($data['isActive']);
            $manager->persist($product);
        }

        $manager->flush();
    }
}
