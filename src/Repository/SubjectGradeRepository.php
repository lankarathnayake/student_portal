<?php

namespace App\Repository;

use App\Entity\SubjectGrade;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SubjectGrade>
 *
 * @method SubjectGrade|null find($id, $lockMode = null, $lockVersion = null)
 * @method SubjectGrade|null findOneBy(array $criteria, array $orderBy = null)
 * @method SubjectGrade[]    findAll()
 * @method SubjectGrade[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SubjectGradeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SubjectGrade::class);
    }

    public function save(SubjectGrade $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(SubjectGrade $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
	
    /**
     * @return Results[]
     */
    public function getStudentGrades(int $id): array
    {
        return $this->createQueryBuilder('r')
			->where('r.student = :student_id')
            ->setParameter('student_id', $id)
            ->getQuery()
            ->getResult();
    }
	
    /**
     * @return Results[]
     */
    public function getStudentGradesByPage(int $page): array
    {
        return $this->createQueryBuilder('r')
			->setFirstResult($page)
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }

    public function getTotalStudentGrades(): int
    {
		return $this->createQueryBuilder('r')
            ->select('count(r.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

//    /**
//     * @return SubjectGrade[] Returns an array of SubjectGrade objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('s.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?SubjectGrade
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
