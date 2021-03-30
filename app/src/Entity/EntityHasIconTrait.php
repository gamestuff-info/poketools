<?php


namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Default implementation of App\Entity\EntityHasIconInterface
 */
trait EntityHasIconTrait
{

    /**
     * Entity icon
     *
     * @ORM\Column(type="string", nullable=true)
     * @Assert\Url()
     * @Groups({"read"})
     */
    protected ?string $icon;

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function setIcon(?string $icon): self
    {
        $this->icon = $icon;

        return $this;
    }
}
