<?php


namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Default implementation of App\Entity\EntityHasNameInterface
 */
trait EntityHasNameTrait
{

    /**
     * Entity name
     *
     * @ORM\Column(type="string")
     * @Groups({"read", "name"})
     * @Assert\NotBlank()
     */
    protected string $name;

    public function __toString(): string
    {
        return $this->getName() ?? '';
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }
}
