<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * SomdaTdr8InSE
 *
 * @ORM\Table(name="somda_tdr_8_in_s_e")
 * @ORM\Entity
 */
class SomdaTdr8InSE
{
    /**
     * @var int
     *
     * @ORM\Column(name="treinid", type="bigint", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $treinid;

    /**
     * @var int
     *
     * @ORM\Column(name="dag", type="bigint", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $dag;

    /**
     * @var int|null
     *
     * @ORM\Column(name="min", type="bigint", nullable=true)
     */
    private $min;

    /**
     * @var int|null
     *
     * @ORM\Column(name="max", type="bigint", nullable=true)
     */
    private $max;


}
