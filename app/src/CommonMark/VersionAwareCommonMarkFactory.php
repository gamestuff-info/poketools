<?php


namespace App\CommonMark;


use App\Entity\Version;
use Ds\Map;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\ExtensionInterface as CommonMarkExtensionInterface;
use League\CommonMark\MarkdownConverter;
use League\CommonMark\MarkdownConverterInterface;

/**
 * Create CommonMark environments that are aware of the current game version.
 */
class VersionAwareCommonMarkFactory
{
    /**
     * @var Map<int, MarkdownConverterInterface>
     */
    private Map $converters;

    /**
     * VersionAwareCommonMarkFactory constructor.
     *
     * @param array $commonMarkConfig
     * @param iterable<CommonMarkExtensionInterface> $extensions
     */
    public function __construct(
        private array $commonMarkConfig,
        private iterable $extensions,
    ) {
        $this->converters = new Map();
    }

    public function getForVersion(?Version $version): MarkdownConverterInterface
    {
        $versionKey = $version?->getId() ?? 0;
        if (!$this->converters->hasKey($versionKey)) {
            $this->converters->put($versionKey, $this->create($version));
        }

        return $this->converters->get($versionKey);
    }

    private function create(?Version $version): MarkdownConverterInterface
    {
        if ($version) {
            $config = $this->commonMarkConfig;
            $config[CommonMarkConfig::CONFIG_NAMESPACE] = [
                CommonMarkConfig::UNQUALIFIED_CURRENT_VERSION => $version,
            ];
            $environment = new Environment($config);
        } else {
            $environment = new Environment($this->commonMarkConfig);
        }
        foreach ($this->extensions as $extension) {
            $environment->addExtension($extension);
        }

        return new MarkdownConverter($environment);
    }
}
