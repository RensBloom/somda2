<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Swagger\Annotations as SWG;

/**
 * @ORM\Table(name="somda_positie")
 * @ORM\Entity(repositoryClass="App\Repository\Position")
 */
class Position
{
    /**
     * @var int|null
     * @ORM\Column(name="posid", type="bigint", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @JMS\Expose()
     * @SWG\Property(description="Unique identifier", type="integer")
     */
    public ?int $id = null;

    /**
     * @var string
     * @ORM\Column(name="positie", type="string", length=2, nullable=false)
     * @JMS\Expose()
     * @SWG\Property(description="The position", maxLength=2, type="string")
     */
    public string $name;
}
