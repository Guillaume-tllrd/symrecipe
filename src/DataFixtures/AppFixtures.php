<?php

namespace App\DataFixtures;

use App\Entity\Ingredient;
use App\Entity\Mark;
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
        // Users
        // on change on met users en 1er, on l'instancie dans un tableau pour pouvoir les utilisés aléatoirement dans la création des ingrédients setUser..
        $users = [];
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
            $users[] = $user;
            $manager->persist($user);
        }
        // ingrédients
        $ingredients = [];
        for ($i = 1; $i <= 50; $i++) {
            $ingredient = new Ingredient();
            $ingredient->setName($this->faker->word())
                ->setPrice(mt_rand(0, 100))
                ->setUser($users[mt_rand(0, count($users) - 1)]);

            $ingredients[] = $ingredient;
            $manager->persist($ingredient);
        }

        // recipes
        $recipes = [];
        for ($j = 0; $j < 25; $j++) {
            $recipe = new Recipe();
            $recipe->setName($this->faker->word())
                ->setTime(mt_rand(0, 1) == 1 ? mt_rand(1, 1440) : null)
                ->setNbpeople(mt_rand(0, 1) == 1 ? mt_rand(1, 50) : null)
                ->setDifficulty((mt_rand(0, 1) == 1 ? mt_rand(1, 5) : null))
                ->setDescription($this->faker->text(300))
                ->setPrice((mt_rand(0, 1) == 1 ? mt_rand(1, 1000) : null))
                ->setIsFavorite((mt_rand(0, 1) == 1 ? true : false))
                ->setIsPublic((mt_rand(0, 1) == 1 ? true : false))
                ->setUser($users[mt_rand(0, count($users) - 1)]);

            for ($k = 0; $k < mt_rand(5, 15); $k++) {
                $recipe->addIngredient($ingredients[mt_rand(0, count($ingredients) - 1)]);
            }
            $recipes[] = $recipe;
            $manager->persist($recipe);
            // ensuite symfony console doctrine:fixtures:load
        }

        //Marks
        // on place d'abord les recettes dans un tableau cf au-dessus: $recipes=[]
        foreach ($recipes as $recipe) {
            // on fait une boucle for pour qu'une recette est entre 0 et 4 notes
            for ($i = 0; $i < mt_rand(0, 4); $i++) {
                $mark = new Mark();
                $mark->setMark(mt_rand(1, 5))
                    // on prend un users au hards dans $users[] avec mt_rand
                    ->setUser($users[mt_rand(0, count($users) - 1)])
                    ->setRecipe($recipe);

                $manager->persist($mark);
            }
        }
        $manager->flush();
    }
}
