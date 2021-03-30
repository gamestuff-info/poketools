<?php


namespace App\Search;

use App\Entity\AbilityInVersionGroup;
use App\Entity\AbstractDexEntity;
use App\Entity\ItemInVersionGroup;
use App\Entity\LocationInVersionGroup;
use App\Entity\MoveInVersionGroup;
use App\Entity\Nature;
use App\Entity\Pokemon;
use App\Entity\Type;
use App\Entity\Version;
use Twig\Environment;

/**
 * Build teasers for search results
 */
class TeaserBuilder
{
    public function __construct(
        private Environment $twig,
        private ?Version $activeVersion,
    ) {
    }

    /**
     * @param object $entity
     * @param Version|null $version
     *
     * @return string
     */
    public function getTeaser(object $entity, ?Version $version): string
    {
        $entityTemplates = [
            Pokemon::class => 'pokemon/teaser.html.twig',
            MoveInVersionGroup::class => 'move/teaser.html.twig',
            Type::class => 'type/teaser.html.twig',
            ItemInVersionGroup::class => 'item/teaser.html.twig',
            LocationInVersionGroup::class => 'location/teaser.html.twig',
            Nature::class => 'nature/teaser.html.twig',
            AbilityInVersionGroup::class => 'ability/teaser.html.twig',
        ];

        $templateArgs = [
            'entity' => $entity,
            'version' => $version ?? $this->activeVersion,
        ];

        // Must allow that the actual entity class may be different because of proxy objects.
        // This makes a class map useless.
        foreach ($entityTemplates as $entityClass => $entityTemplate) {
            if (is_a($entity, $entityClass)) {
                return $this->twig->render($entityTemplate, $templateArgs);
            }
        }

        // Default teaser is an empty string.
        return '';
    }
}
