<?php

namespace App\Repository;

use App\Entity\Apps;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Apps>
 *
 * @method Apps|null find($id, $lockMode = null, $lockVersion = null)
 * @method Apps|null findOneBy(array $criteria, array $orderBy = null)
 * @method Apps[]    findAll()
 * @method Apps[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AppsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Apps::class);
    }

    public function save(Apps $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Apps $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

   /**
    * @return Apps[] Returns an array of Apps objects
    */
   public function findByRole($value): array
   {
       return $this->createQueryBuilder('a')
           ->andWhere('a.role  = :val')
           ->setParameter('val', $value)
           ->getQuery()
           ->getResult()
       ;
   }

//    public function findOneBySomeField($value): ?Apps
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
