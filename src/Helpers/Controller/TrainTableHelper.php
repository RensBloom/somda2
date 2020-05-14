<?php

namespace App\Helpers\Controller;

use App\Entity\RouteTrain;
use App\Entity\TrainTable;
use App\Traits\DateTrait;

class TrainTableHelper extends BaseControllerHelper
{
    use DateTrait;

    /**
     * @return TrainTable[]
     */
    public function getTrainTableLines(): array
    {
        $this->clearErrorMessages();
        if (is_null($this->getTrainTableYear())) {
            $this->addErrorMessage($this->translator->trans('general.error.trainTableIndex'));
            return [];
        }
        if (is_null($this->getRoute())) {
            $this->addErrorMessage($this->translator->trans('general.error.route'));
            return [];
        }

        return $this->doctrine->getRepository(TrainTable::class)->findBy(
            ['trainTableYear' => $this->getTrainTableYear(), 'route' => $this->getRoute()],
            ['order' => 'ASC']
        );
    }

    /**
     * @return RouteTrain[]
     */
    public function getRoutePredictions(): array
    {
        $this->clearErrorMessages();
        if (is_null($this->getTrainTableYear())) {
            $this->addErrorMessage($this->translator->trans('general.error.trainTableIndex'));
            return [];
        }
        if (is_null($this->getRoute())) {
            $this->addErrorMessage($this->translator->trans('general.error.route'));
            return [];
        }

        return $this->doctrine->getRepository(RouteTrain::class)->findBy(
            ['trainTableYear' => $this->getTrainTableYear(), 'route' => $this->getRoute()],
            ['dayNumber' => 'ASC']
        );
    }

    /**
     * @param int $dayNumber
     * @param string $startTime
     * @param string $endTime
     * @return array
     */
    public function getPassingRoutes(int $dayNumber = null, string $startTime = null, string $endTime = null): array
    {
        $this->clearErrorMessages();
        if (is_null($this->getTrainTableYear())) {
            $this->addErrorMessage($this->translator->trans('general.error.trainTableIndex'));
            return [];
        }
        if (is_null($this->getLocation())) {
            $this->addErrorMessage($this->translator->trans('general.error.location'));
            return [];
        }

        if (is_null($dayNumber)) {
            $dayNumber = date('N');
        }

        if (!is_null($startTime)) {
            $startTimeDatabase = $this->timeDisplayToDatabase($startTime);
        } else {
            $startTimeDatabase = $this->timeDisplayToDatabase(date('H:i'));
        }
        if (!is_null($endTime)) {
            $endTimeDatabase = $this->timeDisplayToDatabase($endTime);
            if ($startTimeDatabase > $endTimeDatabase) {
                $this->addErrorMessage($this->translator->trans('passingRoutes.error.dayBorderCrossed'));
                $endTimeDatabase = 1440;
            }
        } else {
            $endTimeDatabase = $startTimeDatabase + 120;
        }

        return $this->doctrine->getRepository(TrainTable::class)->findPassingRoutes(
            $this->getTrainTableYear(),
            $this->getLocation(),
            $dayNumber,
            $this->getDayName($dayNumber),
            $startTimeDatabase,
            $endTimeDatabase
        );
    }
}
