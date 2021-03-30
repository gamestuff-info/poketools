<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Validator\IsExpression;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Growth rate of a Pokémon, i.e. the EXP to level function.
 *
 * @ORM\Entity(repositoryClass="App\Repository\GrowthRateRepository")
 */
#[ApiResource(
    normalizationContext: ['groups' => ['read']]
)]
class GrowthRate extends AbstractDexEntity implements EntityHasNameInterface, EntityHasSlugInterface
{

    use EntityHasNameAndSlugTrait;

    /**
     * MathML representation of the growth rate formula
     *
     * This will include `<math>` tags.
     *
     * @ORM\Column(type="text")
     * @Assert\NotBlank()
     * @Groups({"pokemon_view"})
     */
    private string $formula;

    /**
     * Usable expression for the formula
     *
     * This uses the Symfony ExpressionLanguage.
     *
     * @see https://symfony.com/doc/current/components/expression_language.html
     * @see https://symfony.com/doc/current/components/expression_language/syntax.html
     *
     * @ORM\Column(type="text")
     *
     * @IsExpression()
     */
    private string $expression;

    public function getFormula(): ?string
    {
        return $this->formula;
    }

    public function setFormula(string $formula): self
    {
        $this->formula = $formula;

        return $this;
    }

    public function getExpression(): ?string
    {
        return $this->expression;
    }

    public function setExpression(string $expression): self
    {
        $this->expression = $expression;

        return $this;
    }
}
