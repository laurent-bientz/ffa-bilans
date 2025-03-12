<?php

namespace App\Repository;

use App\Entity\Performance;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\AbstractQuery;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method null|Performance find($id, $lockMode = null, $lockVersion = null)
 * @method null|Performance findOneBy(array $criteria, array $orderBy = null)
 * @method Performance[]    findAll()
 * @method Performance[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 **/
class PerformanceRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
    )
    {
        parent::__construct($registry, Performance::class);
    }

    public function getDistinctYears()
    {
        return $this->createQueryBuilder('p')
            ->select('DISTINCT(p.year)')
            ->where('p.year IS NOT NULL')
            ->orderBy('p.year', 'ASC')
            ->getQuery()
            ->getResult(AbstractQuery::HYDRATE_SCALAR_COLUMN);
    }

    public function getDistinctCategories()
    {
        return $this->createQueryBuilder('p')
            ->select('DISTINCT(p.category)')
            ->where('p.category IS NOT NULL')
            ->orderBy('p.category', 'ASC')
            ->getQuery()
            ->getResult(AbstractQuery::HYDRATE_SCALAR_COLUMN);
    }

    public function getDistinctBirths()
    {
        return $this->createQueryBuilder('p')
            ->select('DISTINCT(p.birth)')
            ->where('p.birth IS NOT NULL')
            ->orderBy('p.birth', 'DESC')
            ->getQuery()
            ->getResult(AbstractQuery::HYDRATE_SCALAR_COLUMN);
    }

    public function getDistinctLocations()
    {
        return $this->createQueryBuilder('p')
            ->select('DISTINCT(p.location)')
            ->where('p.location IS NOT NULL')
            ->orderBy('p.location', 'ASC')
            ->getQuery()
            ->getResult(AbstractQuery::HYDRATE_SCALAR_COLUMN);
    }

    public function getMetrics(array $filters = [])
    {
        $qb = $this->createQueryBuilder('p')
            ->select('COUNT(p.time) AS total')
            ->addSelect('MIN(p.time) AS min')
            ->addSelect('MAX(p.time) AS max')
            ->addSelect('AVG(p.time) AS avg');

        if (!empty($filters['trial'])) {
            $qb->andWhere('p.trial = :trial')->setParameter('trial', $filters['trial']);
        }
        if (!empty($filters['year'])) {
            $qb->andWhere('p.year = :year')->setParameter('year', $filters['year']);
        }
        if (!empty($filters['gender'])) {
            $qb->andWhere('p.gender = :gender')->setParameter('gender', $filters['gender']);
        }
        if (!empty($filters['category'])) {
            $qb->andWhere('p.category = :category')->setParameter('category', $filters['category']);
        }
        if (!empty($filters['birth'])) {
            $qb->andWhere('p.birth = :birth')->setParameter('birth', $filters['birth']);
        }
        if (!empty($filters['location'])) {
            $qb->andWhere('p.location = :location')->setParameter('location', $filters['location']);
        }

        return $qb
            ->getQuery()
            ->getSingleResult(AbstractQuery::HYDRATE_ARRAY);
    }

    public function deleteOlder(int $trial, string $year)
    {
        $qb = $this->createQueryBuilder('p');
        $q = $qb->delete()
            ->where('p.trial = :trial')
            ->setParameter('trial', $trial)
            ->andWhere('p.date LIKE :year')
            ->setParameter('year', $year . '-%')
            ->getQuery();

        return $q->getSingleScalarResult();
    }
}
