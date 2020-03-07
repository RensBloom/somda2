<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * SomdaTdr11In
 *
 * @ORM\Table(name="somda_tdr_11_in", uniqueConstraints={@ORM\UniqueConstraint(name="idx_48342_treinnr", columns={"treinnr", "rijdagenid", "locatie", "tijd"})}, indexes={@ORM\Index(name="idx_48342_tijd", columns={"tijd"}), @ORM\Index(name="idx_48342_locatieid", columns={"locatieid"}), @ORM\Index(name="idx_48342_treinid", columns={"treinid"})})
 * @ORM\Entity
 */
class SomdaTdr11In
{
    /**
     * @var int
     *
     * @ORM\Column(name="tdrid", type="bigint", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $tdrid;

    /**
     * @var string
     *
     * @ORM\Column(name="treinnr", type="string", length=15, nullable=false)
     */
    private $treinnr = '';

    /**
     * @var int|null
     *
     * @ORM\Column(name="treinid", type="bigint", nullable=true)
     */
    private $treinid;

    /**
     * @var int|null
     *
     * @ORM\Column(name="rijdagenid", type="bigint", nullable=true)
     */
    private $rijdagenid;

    /**
     * @var string
     *
     * @ORM\Column(name="locatie", type="string", length=100, nullable=false)
     */
    private $locatie = '';

    /**
     * @var int|null
     *
     * @ORM\Column(name="locatieid", type="bigint", nullable=true)
     */
    private $locatieid;

    /**
     * @var string
     *
     * @ORM\Column(name="actie", type="string", length=1, nullable=false)
     */
    private $actie = '';

    /**
     * @var int|null
     *
     * @ORM\Column(name="tijd", type="bigint", nullable=true)
     */
    private $tijd;

    /**
     * @var int
     *
     * @ORM\Column(name="uid", type="bigint", nullable=false)
     */
    private $uid;


}
