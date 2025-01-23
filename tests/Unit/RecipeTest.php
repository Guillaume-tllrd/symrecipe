<?php

namespace App\Tests\Unit;

use App\Entity\Mark;
use App\Entity\Recipe;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class RecipeTest extends KernelTestCase
{
    // en développement on fait du DRY don't repeat youyrself donc on crée une fonction pour récupérer l'entité afin d'éviter de répéter du code 
    public function getEntity(): Recipe
    {
        return (new Recipe())
            ->setName('recipe #1')
            ->setDescription('Description #1')
            ->setIsFavorite(true)
            ->setCreatedAt(new \DateTimeImmutable())
            ->setUpdatedAt(new \DateTimeImmutable());
    }
    // on teste que l'entite recipe est valide:
    public function testEntityIsValid(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        $recipe = $this->getEntity();
        // les erreurs vont se stoké dans la var:
        $errors = $container->get('validator')->valide($recipe);

        // ensuite on demande que les erreurs soit à 0:
        $this->assertCount(0, $errors);
    }
    // pour tester le nom on attend 2 erreurs car moins de 2 carctères et vide 
    public function testInvalidName()
    {
        self::bootKernel();
        $container = static::getContainer();

        $recipe = $this->getEntity();
        $recipe->setName('');
        // les erreurs vont se stoké dans la var:
        $errors = $container->get('validator')->valide($recipe);
        $this->assertCount(2, $errors);
    }

    public function testGetAverage()
    {

        $recipe = $this->getEntity();
        // pour récuper le user on lui ddemander d'aller chercher un service: doctrine.orm...
        $user = static::getContainer()->get('doctrine.orm.entity_manager')->fin(User::class, 1);

        // On donne 5 note d'une valeur de 2:
        for ($i = 0; $i < 5; $i++) {
            $mark = new Mark();
            $mark->setMark(2)
                ->setUser($user)
                ->setRecipe($recipe);

            $recipe->addMark($mark);
        }

        // on s'attend à récupérer un float donc mettre .0
        $this->assertTrue(2.0 === $recipe->getAverage());
    }
}
