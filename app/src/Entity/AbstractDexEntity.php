<?php


namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

abstract class AbstractDexEntity
{

    /**
     * Unique Id
     *
     * @ORM\Id()
     * @ORM\Column(type="integer", unique=true)
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({"read"})
     */
    #[ApiProperty(iri: 'https://schema.org/identifier')]
    protected int $id;

    public function getId(): int
    {
        return $this->id;
    }
}
