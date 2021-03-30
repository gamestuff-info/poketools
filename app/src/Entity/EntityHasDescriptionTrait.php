<?php


namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

trait EntityHasDescriptionTrait
{

    /**
     * Shortened or summary of this entity
     *
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"read"})
     */
    #[MarkdownProperty]
    protected ?string $shortDescription = null;

    /**
     * Description of this entity
     *
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"read"})
     */
    #[MarkdownProperty]
    protected ?string $description = null;

    public function getShortDescription(): ?string
    {
        return $this->shortDescription;
    }

    public function setShortDescription(?string $shortDescription): self
    {
        if ($shortDescription === '') {
            $this->shortDescription = null;
        } else {
            $this->shortDescription = $shortDescription;
        }

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        if ($description === '') {
            $this->description = null;
        } else {
            $this->description = $description;
        }

        return $this;
    }
}
