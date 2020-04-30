<?php

namespace App\Command;

use App\Entity\Position;
use App\Entity\Route;
use App\Entity\RouteTrain;
use App\Entity\Spot;
use App\Entity\TrainNamePattern;
use App\Entity\TrainTableYear;
use AurimasNiekis\SchedulerBundle\ScheduledJobInterface;
use DateTime;
use Doctrine\Common\Persistence\ManagerRegistry;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateRouteTrainsCommand extends Command implements ScheduledJobInterface
{
    private const CHECK_DATE_DAYS = 300;

    /**
     * @var string
     */
    protected static $defaultName = 'app:update-route-trains';

    /**
     * @var ManagerRegistry
     */
    private ManagerRegistry $doctrine;

    /**
     * @param ManagerRegistry $doctrine
     */
    public function __construct(ManagerRegistry $doctrine)
    {
        parent::__construct(self::$defaultName);

        $this->doctrine = $doctrine;
    }

    /**
     *
     */
    public function __invoke()
    {
        $this->execute();
    }

    /**
     * @return string
     */
    public function getSchedulerExpresion(): string
    {
        return '32 1 * * *';
    }

    /**
     *
     */
    protected function configure(): void
    {
        $this->setDescription('Update route-trains');
    }

    /**
     * @param InputInterface|null $input
     * @param OutputInterface|null $output
     * @return int
     * @throws Exception
     */
    protected function execute(InputInterface $input = null, OutputInterface $output = null): int
    {
        /**
         * @var TrainTableYear $trainTableYear
         */
        $trainTableYear = $this->doctrine->getRepository(TrainTableYear::class)->findCurrentTrainTableYear();
        $checkDate = max($trainTableYear->startDate, new DateTime('-' . self::CHECK_DATE_DAYS . ' days'));

        $routeArray = $this->doctrine->getRepository(Spot::class)->findForRouteTrains($checkDate);
        foreach ($routeArray as $routeItem) {
            /**
             * @var Route $route
             */
            $route = $this->doctrine->getRepository(Route::class)->find($routeItem['routeId']);
            /**
             * @var TrainNamePattern $pattern
             */
            $pattern = $this->doctrine->getRepository(TrainNamePattern::class)->find($routeItem['patternId']);
            /**
             * @var Position $position
             */
            $position = $this->doctrine->getRepository(Position::class)->find($routeItem['positionId']);

            $routeTrain = $this->doctrine->getRepository(RouteTrain::class)->findOneBy([
                'trainTableYear' => $trainTableYear,
                'route' => $route,
                'position' => $position,
                'dayNumber' => $routeItem['dayOfWeek'],
            ]);
            if (is_null($routeTrain)) {
                $routeTrain = new RouteTrain();
                $routeTrain->trainTableYear = $trainTableYear;
                $routeTrain->route = $route;
                $routeTrain->position = $position;
                $routeTrain->dayNumber = $routeItem['dayOfWeek'];

                $this->doctrine->getManager()->persist($routeTrain);
            }

            $routeTrain->numberOfSpots = $routeItem['numberOfSPots'];
            $routeTrain->trainNamePattern = $pattern;

            $this->doctrine->getManager()->flush();
        }

        return 0;
    }
}