<?php

namespace App\Repository;

use App\Entity\Forecast;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Forecast>
 */
class ForecastRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Forecast::class);
    }

    /**
     * @return list<Forecast>
     */
    public function findLatestForCity(\App\Entity\City $city): array
    {
        return $this->createQueryBuilder('forecast')
            ->andWhere('forecast.city = :city')
            ->setParameter('city', $city)
            ->addSelect('CASE WHEN forecast.createdAt IS NULL THEN 1 ELSE 0 END AS HIDDEN undated')
            ->orderBy('undated', 'ASC')
            ->addOrderBy('forecast.createdAt', 'DESC')
            ->addOrderBy('forecast.id', 'DESC')
            ->setMaxResults(5)
            ->getQuery()
            ->getResult();
    }
}
