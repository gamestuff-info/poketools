<?php


namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Default implementation of App\Entity\EntityGroupedByGenerationInterface.
 */
trait EntityGroupedByGenerationTrait
{

    /**
     * @var Generation
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Generation")
     * @Assert\NotNull()
     */
    protected Generation $generation;

    public function getGeneration(): ?Generation
    {
        return $this->generation;
    }

    public function setGeneration(Generation $generation): self
    {
        $this->generation = $generation;

        return $this;
    }

    public function getGroup(): GroupableInterface
    {
        return $this->getGeneration();
    }

    public static function getGroupField(): string
    {
        return 'generation';
    }
}
