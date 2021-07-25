<?php


namespace App\CommonMark\Extension;


use App\CommonMark\CommonMarkConfig;
use App\CommonMark\Handler\JsxEscaper;
use App\CommonMark\Inline\Parser\CloseBracketInternalLinkParser;
use App\Entity\Version;
use League\CommonMark\Environment\EnvironmentBuilderInterface;
use League\CommonMark\Event\DocumentPreParsedEvent;
use League\CommonMark\Event\DocumentRenderedEvent;
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
        private CloseBracketInternalLinkParser $closeBracketInternalLinkParser,
        private JsxEscaper $jsxEscaper,
    ) {
    }

    public function register(EnvironmentBuilderInterface $environment): void
    {
        $environment
            ->addInlineParser($this->closeBracketInternalLinkParser, 200)
            ->addEventListener(DocumentPreParsedEvent::class, $this->jsxEscaper)
            ->addEventListener(DocumentRenderedEvent::class, $this->jsxEscaper);
    }

    public function configureSchema(ConfigurationBuilderInterface $builder): void
    {
        // Allows injection of the effective version.
        $builder->addSchema(
            CommonMarkConfig::CONFIG_NAMESPACE,
            Expect::structure([
                CommonMarkConfig::UNQUALIFIED_CURRENT_VERSION => Expect::anyOf(null, Expect::type(Version::class)),
            ])
        );
    }
}
