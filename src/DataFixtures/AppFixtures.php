<?php

namespace App\DataFixtures;

use App\Entity\Ingredient;
use App\Entity\Recipe;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private Generator $faker;
    // private UserPasswordHasherInterface $hasher; plus besoin avec le entityListener

    public function __construct(UserPasswordHasherInterface $hasher)
    {
        $this->faker = Factory::create('fr_FR');
        // $this->hasher = $hasher; plus besoin avec le entityListener
    }

    public function load(ObjectManager $manager): void
    {
        // ingrédients
        $ingredients = [];
        for ($i = 1; $i <= 50; $i++) {
            $ingredient = new Ingredient();
            $ingredient->setName($this->faker->word())
                ->setPrice(mt_rand(0, 100));
            $ingredients[] = $ingredient;
            $manager->persist($ingredient);
        }

        // recipes
        for ($j = 0; $j < 25; $j++) {
            $recipe = new Recipe();
            $recipe->setName($this->faker->word())
                ->setTime(mt_rand(0, 1) == 1 ? mt_rand(1, 1440) : null)
                ->setNbpeople(mt_rand(0, 1) == 1 ? mt_rand(1, 50) : null)
                ->setDifficulty((mt_rand(0, 1) == 1 ? mt_rand(1, 5) : null))
                ->setDescription($this->faker->text(300))
                ->setPrice((mt_rand(0, 1) == 1 ? mt_rand(1, 1000) : null))
                ->setIsFavorite((mt_rand(0, 1) == 1 ? true : false));

            for ($k = 0; $k < mt_rand(5, 15); $k++) {
                $recipe->addIngredient($ingredients[mt_rand(0, count($ingredients) - 1)]);
            }

            $manager->persist($recipe);
            // ensuite symfony console doctrine:fixtures:load
        }
        // Users
        for ($u = 0; $u < 10; $u++) {
            $user = new User();
            $user->setFullName($this->faker->name())
                ->setPseudo(mt_rand(0, 1) === 1 ? $this->faker->firstName() : null)
                ->setEmail($this->faker->email())
                ->setRoles(['ROLE_USER'])
                ->setPlainPassword('aserty');
            // on va fonctionner avec entityListenners qui permet d'couter ce qui se passe chez les entité et de maj si besoin

            // pour le hashage on fait appal au UserPasswordHasherInterface avec sa méthode hashPassword on lui donne paramètre $user et le mdp qu'on veut hasher
            // $hashPassword = $this->hasher->hashPassword(
            //     $user,
            //     'azerty'
            // );
            // $user->setPassword($hashPassword);
            $manager->persist($user);
        }

        $manager->flush();
    }
}
