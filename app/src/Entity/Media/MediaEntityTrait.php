<?php
/**
 * @file MediaEntityTrait.php
 */

namespace App\Entity\Media;


use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

trait MediaEntityTrait
{
    /**
     * @ORM\Column(type="string", nullable=true)
     * @Groups({"read"})
     */
    protected ?string $url;

    public function __toString(): string
    {
        return $this->getUrl() ?? '';
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }
}
