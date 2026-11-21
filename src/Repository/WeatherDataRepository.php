<?php

namespace App\Repository;

use App\Entity\WeatherData;
use App\Entity\WeatherStation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<WeatherData>
 */
class WeatherDataRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, WeatherData::class);
    }

    public function findSinceWeatherDataByStation(WeatherStation $weatherStation, \DateTime $dateTime): array
    {
        return $this->createQueryBuilder('wd')
            ->andWhere('wd.weatherStation = :station')
            ->andWhere('wd.createdAt >= :dateTime')
            ->setParameter('station', $weatherStation)
            ->setParameter('dateTime', $dateTime)
            ->orderBy('w.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

//    /**
//     * @return WeatherData[] Returns an array of WeatherData objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('w')
//            ->andWhere('w.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('w.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?WeatherData
//    {
//        return $this->createQueryBuilder('w')
//            ->andWhere('w.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
