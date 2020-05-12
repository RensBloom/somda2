<?php

namespace App\Repository;

use App\Entity\Location;
use App\Entity\Route;
use App\Entity\TrainTable as TrainTableEntity;
use App\Entity\TrainTableFirstLast;
use App\Entity\TrainTableYear;
use App\Traits\DateTrait;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query\Expr\Join;

class TrainTable extends EntityRepository
{
    use DateTrait;

    public const FIELD_ROUTE_NUMBER = 'routeNumber';
    public const FIELD_TRANSPORTER_NAME = 'transporterName';
    public const FIELD_CHARACTERISTIC_NAME = 'characteristicName';
    public const FIELD_CHARACTERISTIC_DESCRIPTION = 'characteristicDescription';
    public const FIELD_SECTION = 'section';

    private const PARAMETER_TRAIN_TABLE_YEAR = 'trainTableYear';

    /**
     * @param TrainTableYear $trainTableYear
     * @param Location $location
     * @param int $dayNumber
     * @param string $dayName
     * @param int $startTime
     * @param int $endTime
     * @return array
     */
    public function findPassingRoutes(
        TrainTableYear $trainTableYear,
        Location $location,
        int $dayNumber,
        string $dayName,
        int $startTime,
        int $endTime
    ): array {
        $queryBuilder = $this->getEntityManager()
            ->createQueryBuilder()
            ->addSelect('t.time AS time')
            ->addSelect('t.action AS action')
            ->addSelect('route.number as route_number')
            ->addSelect('fl_first.name AS fl_first_name')
            ->addSelect('fl_first.description AS fl_first_description')
            ->addSelect('fl_last.name AS fl_last_name')
            ->addSelect('fl_last.description AS fl_last_description')
            ->addSelect('transporter.name AS ' . self::FIELD_TRANSPORTER_NAME)
            ->addSelect('characteristic.description AS ' . self::FIELD_CHARACTERISTIC_DESCRIPTION)
            ->from(TrainTableEntity::class, 't')
            ->andWhere('t.trainTableYear = :' . self::PARAMETER_TRAIN_TABLE_YEAR)
            ->setParameter(self::PARAMETER_TRAIN_TABLE_YEAR, $trainTableYear)
            ->andWhere('t.location = :location')
            ->setParameter('location', $location)
            ->andWhere('t.time >= :startTime')
            ->setParameter('startTime', $startTime)
            ->andWhere('t.time <= :endTime')
            ->setParameter('endTime', $endTime)
            ->join('t.routeOperationDays', 'routeOperationDays')
            ->andWhere('routeOperationDays.' . $dayName . ' = TRUE')
            ->join('t.route', 'route')
            ->join('route.trainTableFirstLasts', 'trainTableFirstLasts')
            ->andWhere('trainTableFirstLasts.dayNumber = :dayNumber')
            ->setParameter('dayNumber', $dayNumber + 1)
            ->andWhere('trainTableFirstLasts.trainTableYear = :' . self::PARAMETER_TRAIN_TABLE_YEAR)
            ->join('trainTableFirstLasts.firstLocation', 'fl_first')
            ->join('trainTableFirstLasts.lastLocation', 'fl_last')
            ->join('route.routeLists', 'routeLists')
            ->andWhere('routeLists.trainTableYear = :' . self::PARAMETER_TRAIN_TABLE_YEAR)
            ->join('routeLists.transporter', 'transporter')
            ->join('routeLists.characteristic', 'characteristic')
            ->addOrderBy('t.time', 'ASC');
        return $queryBuilder->getQuery()->getArrayResult();
    }

    /**
     * @param TrainTableYear $trainTableYear
     * @return array
     */
    public function findAllTrainTablesForForum(TrainTableYear $trainTableYear): array
    {
        $queryBuilder = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('r.number AS ' . self::FIELD_ROUTE_NUMBER)
            ->addSelect('tr.name AS ' . self::FIELD_TRANSPORTER_NAME)
            ->addSelect('c.name AS ' . self::FIELD_CHARACTERISTIC_NAME)
            ->addSelect('c.description AS ' . self::FIELD_CHARACTERISTIC_DESCRIPTION)
            ->addSelect('l1.name AS firstLocation')
            ->addSelect('fl.firstTime AS firstTime')
            ->addSelect('l2.name AS lastLocation')
            ->addSelect('fl.lastTime AS lastTime')
            ->addSelect('rl.section AS ' . self::FIELD_SECTION)
            ->from(TrainTableFirstLast::class, 'fl')
            ->join('fl.route', 'r')
            ->join('fl.firstLocation', 'l1')
            ->join('fl.lastLocation', 'l2')
            ->join('r.routeLists', 'rl', Join::WITH, 'rl.trainTableYear = :' . self::PARAMETER_TRAIN_TABLE_YEAR)
            ->join('rl.transporter', 'tr')
            ->join('rl.characteristic', 'c')
            ->andWhere('fl.trainTableYear = :' . self::PARAMETER_TRAIN_TABLE_YEAR)
            ->andWhere('fl.dayNumber = 1')
            ->setParameter(self::PARAMETER_TRAIN_TABLE_YEAR, $trainTableYear);
        return $queryBuilder->getQuery()->getArrayResult();
    }

    /**
     * @param TrainTableYear $trainTableYear
     * @param Route $route
     * @param Location $location
     * @param int $dayNumber
     * @return bool
     */
    public function isExistingForSpot(
        TrainTableYear $trainTableYear,
        Route $route,
        Location $location,
        int $dayNumber
    ): bool {
        $queryBuilder = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('COUNT(t.id)')
            ->from(TrainTableEntity::class, 't')
            ->andWhere('t.trainTableYear = :' . self::PARAMETER_TRAIN_TABLE_YEAR)
            ->setParameter(self::PARAMETER_TRAIN_TABLE_YEAR, $trainTableYear)
            ->andWhere('t.route = :route')
            ->setParameter('route', $route)
            ->andWhere('t.location = :location')
            ->setParameter('location', $location)
            ->join('t.routeOperationDays', 'o')
            ->andWhere('o.' . $this->getDayName($dayNumber - 1) .' = TRUE');
        try {
            return $queryBuilder->getQuery()->getSingleScalarResult() > 0;
        } catch (NonUniqueResultException $exception) {
            return false;
        } catch (NoResultException $exception) {
            return false;
        }
    }
}
