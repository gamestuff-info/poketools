<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * The shape of a Pokémon’s body. Appears in the Pokédex starting with
 * Generation IV.
 *
 * @ORM\Entity(repositoryClass="App\Repository\PokemonShapeInVersionGroupRepository")
 *
 * @method PokemonShape getParent()
 * @method self setParent(PokemonShape $parent)
 */
#[ApiResource(
    normalizationContext: ['groups' => ['read']]
)]
class PokemonShapeInVersionGroup extends AbstractDexEntity implements EntityHasNameInterface, EntityHasSlugInterface, EntityHasDescriptionInterface, EntityHasIconInterface, EntityHasParentInterface, EntityGroupedByVersionGroupInterface
{

    use EntityHasNameAndSlugTrait;
    use EntityHasDescriptionTrait;
    use EntityHasIconTrait;
    use EntityHasParentTrait;
    use EntityGroupedByVersionGroupTrait;

    /**
     * @var PokemonShape
     * @ORM\ManyToOne(targetEntity="App\Entity\PokemonShape", inversedBy="children")
     */
    protected EntityHasChildrenInterface $parent;

    /**
     * A taxonomy name for this shape, roughly corresponding to a family name
     * in zoological taxonomy.
     *
     * @ORM\Column(type="string")
     * @Assert\NotBlank()
     * @Groups({"read"})
     */
    private string $taxonomyName;

    public function getTaxonomyName(): ?string
    {
        return $this->taxonomyName;
    }

    public function setTaxonomyName(string $taxonomyName): self
    {
        $this->taxonomyName = $taxonomyName;

        return $this;
    }
}
