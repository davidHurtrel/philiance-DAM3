<?php

namespace App\Repository;

use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Product>
 *
 * @method Product|null find($id, $lockMode = null, $lockVersion = null)
 * @method Product|null findOneBy(array $criteria, array $orderBy = null)
 * @method Product[]    findAll()
 * @method Product[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    public function add(Product $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Product $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return Product[] Returns an array of Product objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('p.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Product
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }

    /**
     * @param int $nb Number of desired products
     * @return Product[] Returns an array of nb Product objects ordered by latest creation date
     */
    public function findLast(int $nb): array
    {
        return $this->createQueryBuilder('fl') // 'fl' est un alias
            ->orderBy('fl.created_at', 'DESC') // tri par date d'ajout d??croissant
            ->orderBy('fl.id', 'DESC') // tri par id d??croissant
            ->setMaxResults($nb) // s??lectionne un nombre de r??sultats maximum
            ->getQuery() // construit la requ??te
            ->getResult() // r??cup??re les r??sultats
        ;
    }

    /**
     * @return Product[] Returns an array of 8 Product objects ordered by latest creation date
     */
    public function findLastEight(): array
    {
        // $db = $this->getEntityManager()->getConnection();
        // $req = $db->prepare('SELECT * FROM product ORDER BY created_at DESC, id DESC LIMIT 8');
        // $results = $req->executeQuery();
        // return $results->fetchAllAssociative();

        $entityManager = $this->getEntityManager();
        $query = $entityManager->createQuery(
            'SELECT p
            FROM App\Entity\Product p
            ORDER BY p.created_at DESC, p.id DESC'
        )->setMaxResults(8);
        return $query->getResult();
    }

    /**
     * @return Product[] Returns an array of 8 best-seller Product objects
     */
    public function findBestSellers(): array
    {
        return $this->createQueryBuilder('p')
            ->innerJoin('App\Entity\OrderDetail', 'od', 'WITH', 'p.id = od.product_id')
            ->groupBy('od.product_id')
            ->orderBy('SUM(od.quantity)', 'DESC')
            ->setMaxResults(8)
            ->getQuery()
            ->getResult()
        ;
    }

    public function search(string $searchedValue): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.name LIKE :val')
            ->setParameter(':val', '%' . $searchedValue . '%')
            ->getQuery()
            ->getResult()
        ;
    }
}
