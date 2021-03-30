<?php


namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Default implementation of App\Entity\EntityGroupedByVersionGroupInterface
 */
trait EntityGroupedByVersionGroupTrait
{

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\VersionGroup")
     * @Assert\NotNull()
     * @Groups({"read"})
     */
    #[ApiProperty(readableLink: false, writableLink: false)]
    protected VersionGroup $versionGroup;

    public function getVersionGroup(): ?VersionGroup
    {
        return $this->versionGroup;
    }

    public function setVersionGroup(VersionGroup $versionGroup): self
    {
        $this->versionGroup = $versionGroup;

        return $this;
    }

    public function getGroup(): GroupableInterface
    {
        return $this->getVersionGroup();
    }

    public static function getGroupField(): string
    {
        return 'versionGroup';
    }
}
