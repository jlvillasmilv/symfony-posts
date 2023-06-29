<?php

namespace App\Repository;

use App\Entity\Post;
use Doctrine\ORM\Query;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;


/**
 * @extends ServiceEntityRepository<Post>
 *
 * @method Post|null find($id, $lockMode = null, $lockVersion = null)
 * @method Post|null findOneBy(array $criteria, array $orderBy = null)
 * @method Post[]    findAll()
 * @method Post[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PostRepository extends ServiceEntityRepository
{
     /**
     * @var PaginatorInterface
     */
    private $paginator;

    public function __construct(ManagerRegistry $registry,
    PaginatorInterface $paginator)
    {
        parent::__construct($registry, Post::class);
        $this->paginator = $paginator;
    }

    public function save(Post $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Post $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findPostByUser(Request $request,array $params): PaginationInterface
    {
        $qb = $this->createQueryBuilder('p')
        ->where('p.user = :id')
        ->orderBy('p.id', 'DESC')
        ->setParameter('id', $params['user']);

        return $this->createPaginator($qb->getQuery(), $request);

        // return $this->getEntityManager()
        // ->createQuery(
        //     'select post.id, post.title, post.description, post.file, post.url, user.id as user_id, user.email, post.created_at
        //      from App:Post post
        //      join post.user user
        //      order by post.created_at desc'
        // )
        // ->getResult();
    }

    public function findAllPost(Request $request): PaginationInterface
    {
        $qb = $this->createQueryBuilder('p')
        ->orderBy('p.created_at', 'DESC');

        return $this->createPaginator($qb->getQuery(), $request);
    }

    private function createPaginator(
        Query $query,
        Request $request
    ): PaginationInterface {
        // Paginate the results of the query
        return $this->paginator->paginate(
            // Doctrine Query, not results
            $query,
            $request->query->getInt('page', 1), // Define the page parameter
            5 /*limit per page*/
        );
    }

//    /**
//     * @return Post[] Returns an array of Post objects
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

//    public function findOneBySomeField($value): ?Post
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
