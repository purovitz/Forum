<?php

namespace App\Repository;

use App\Entity\Answer;
use App\Entity\Comment;
use App\Entity\Post;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Post|null find($id, $lockMode = null, $lockVersion = null)
 * @method Post|null findOneBy(array $criteria, array $orderBy = null)
 * @method Post[]    findAll()
 * @method Post[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Post::class);
    }

    public function findOneBySlug(string $slug): ?Post
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.slug = :slug')
            ->setParameter('slug', $slug)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findAllPublic()
    {
        return $this->createQueryBuilder('post')
            ->where('post.is_public = 1')
            ->orderBy('post.uploaded_at', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findAllUnpublic()
    {
        return $this->createQueryBuilder('post')
            ->where('post.is_public = 0')
            ->orderBy('post.uploaded_at', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Post[]
     */
    public function findMostPopularr()
    {
        $queryBuilder = $this->createQueryBuilder('post')
            ->where('post.is_public = 1')
            ->orderBy('post.comment_count', 'DESC');


        return $queryBuilder
            ->getQuery()
            ->getResult();
    }


    /**
     * @return Post[]
     */
    public function findAllPublishedOrderedByNewest()
    {
        return $this->addIsPublishedQueryBuilder()
            ->orderBy('a.uploaded_at', 'DESC')
            ->getQuery()
            ->getResult()
            ;
    }

    private function addIsPublishedQueryBuilder(QueryBuilder $qb = null)
    {
        return $this->getOrCreateQueryBuilder($qb)
            ->andWhere('a.uploaded_at IS NOT NULL');
    }

    private function getOrCreateQueryBuilder(QueryBuilder $qb = null)
    {
        return $qb ?: $this->createQueryBuilder('a');
    }

    /**
     * @return Post[]
     */
    public function findMostPopular()
    {
        $queryBuilder = $this->createQueryBuilder('a')
            ->orderBy('a.comment_count', 'DESC');


        return $queryBuilder
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Post[]
     */
    public function findMostLiked()
    {
        $queryBuilder = $this->createQueryBuilder('a')
            ->where('a.is_public = 1')
            ->orderBy('a.votes', 'DESC');


        return $queryBuilder
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Post[]
     */
    public function findMostUnliked()
    {
        $queryBuilder = $this->createQueryBuilder('a')
            ->where('a.is_public = 1')
            ->orderBy('a.votes', 'ASC');


        return $queryBuilder
            ->getQuery()
            ->getResult();
    }


/*
 *    public function findByMostCommented() {
        $query = $this->createQueryBuilder('post')
            ->addSelect('COUNT(post.comments) AS HIDDEN comments_num')
            ->innerJoin('post.comments', 'comments')
            ->groupBy('post.id')
            ->orderBy('comments_num', 'DESC')
            ->addOrderBy('post.created', 'DESC')
            ->getQuery()->getDQL()
        ;
    }    public function findMostComments()
    {
        $dm=$this->
        $qb = $dm->createQueryBuilder();

        $qb->select($qb->expr()->count('u'))
            ->from('User', 'u')
            ->where('u.type = ?1')
            ->setParameter(1, 'employee');

        $query = $qb->getQuery();

        $usersCount = $query->getSingleScalarResult();


        $queryBuilder = $this->createQueryBuilder('post')
            ->addSelect('COUNT(post.id) AS HIDDEN comment')
            ->groupBy('post.id', 'DESC')
            ->orderBy('comment', 'DESC');

        return $queryBuilder->getQuery()->getResult();

    }
 */



/*
 *     public function findOneBySlug(string $slug): ?Post
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.slug = :slug')
            ->setParameter('slug', $slug)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findAll()
    {
        return $this->createQueryBuilder('p')
            ->orderBy('p.created_at', 'desc')
            ->getQuery();
    }
 */


    // /**
    //  * @return Post[] Returns an array of Post objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Post
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
