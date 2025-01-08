<?php

namespace App\Repository;

use App\Entity\Recipe;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Recipe>
 */
class RecipeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Recipe::class);
    }

    /**
     * This method allow us to find public recipes based on number of recipes
     *
     * @param integer $nbRecipes
     * @return Array
     */
    public function findPulicRecipe(?int $nbRecipes): array
    {

        $queryBuilder = $this->createQueryBuilder('r')->where('r.isPublic = 1')
            ->orderBy('r.created_at', 'DESC');
        // si on lui passe aucun paramètre il envoie le nbre max
        if ($nbRecipes !== 0 || $nbRecipes !== null) {
            $queryBuilder->setMaxResults($nbRecipes);
        }
        return $queryBuilder->getQuery()
            ->getResult();
    }

    //    /**
    //     * @return Recipe[] Returns an array of Recipe objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('r.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Recipe
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
