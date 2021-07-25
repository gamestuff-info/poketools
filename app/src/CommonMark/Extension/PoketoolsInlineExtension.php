<?php


namespace App\CommonMark\Extension;


use App\CommonMark\CommonMarkConfig;
use App\CommonMark\Inline\Parser\CloseBracketInternalLinkParser;
use App\Entity\Version;
use League\CommonMark\Environment\EnvironmentBuilderInterface;
use League\CommonMark\Extension\ConfigurableExtensionInterface;
use League\Config\ConfigurationBuilderInterface;
use Nette\Schema\Expect;

/**
 * CommonMark Extension to manage special app-specific pieces (inlines only).
 */
class PoketoolsInlineExtension implements ConfigurableExtensionInterface
{
    /**
     * PoketoolsCommonMarkExtension constructor.
     *
     * @param CloseBracketInternalLinkParser $closeBracketInternalLinkParser
     */
    public function __construct(
        private CloseBracketInternalLinkParser $closeBracketInternalLinkParser
    ) {
    }

    public function register(EnvironmentBuilderInterface $environment): void
    {
        $environment
            ->addInlineParser($this->closeBracketInternalLinkParser, 200);
    }

    public function configureSchema(ConfigurationBuilderInterface $builder): void
    {
        $builder->addSchema(
            CommonMarkConfig::CONFIG_NAMESPACE,
            Expect::structure([
                CommonMarkConfig::UNQUALIFIED_CURRENT_VERSION => Expect::anyOf(null, Expect::type(Version::class)),
            ])
        );
    }
}
