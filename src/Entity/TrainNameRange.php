<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="somda_mat_naam")
 * @ORM\Entity
 */
class TrainNameRange extends Entity
{
    /**
     * @var int
     * @ORM\Column(name="id", type="bigint", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var int
     * @ORM\Column(name="nr_start", type="bigint", nullable=false)
     */
    public $start;

    /**
     * @var int
     * @ORM\Column(name="nr_eind", type="bigint", nullable=false)
     */
    public $end;

    /**
     * @var string
     * @ORM\Column(name="naam", type="string", length=25, nullable=false)
     */
    public $name;

    /**
     * @var Transporter
     * @ORM\ManyToOne(targetEntity="App\Entity\Transporter")
     * @ORM\JoinColumn(name="vervoerder_id", referencedColumnName="vervoerder_id")
     */
    public $transporter;
}
