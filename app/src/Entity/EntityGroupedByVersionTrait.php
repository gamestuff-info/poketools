<?php


namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Default implementation of App\Entity\EntityGroupedByVersionInterface
 */
trait EntityGroupedByVersionTrait
{

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Version")
     * @Assert\NotNull()
     */
    protected Version $version;

    public function getVersion(): Version
    {
        return $this->version;
    }

    public function setVersion(Version $version): self
    {
        $this->version = $version;

        return $this;
    }

    public function getGroup(): GroupableInterface
    {
        return $this->getVersion();
    }

    public static function getGroupField(): string
    {
        return 'version';
    }
}
