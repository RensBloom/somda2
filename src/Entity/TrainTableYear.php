<?php

namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="somda_tdr_drgl")
 * @ORM\Entity
 */
class TrainTableYear extends Entity
{
    /**
     * @var int
     * @ORM\Column(name="tdr_nr", type="bigint", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var string
     * @ORM\Column(name="naam", type="string", length=10, nullable=false)
     */
    public $name;

    /**
     * @var DateTime
     * @ORM\Column(name="start_datum", type="date", nullable=false)
     */
    public $startDate;

    /**
     * @var DateTime
     * @ORM\Column(name="eind_datum", type="date", nullable=false)
     */
    public $endDate;
}
