<?php


namespace App\CommonMark;


use ApiPlatform\Core\Api\IriConverterInterface;
use App\Entity\Version;
use Ds\Map;
use League\CommonMark\CommonMarkConverter;
use League\CommonMark\Environment;
use League\CommonMark\Extension\ExtensionInterface as CommonMarkExtensionInterface;

/**
 * Create CommonMark environments that are aware of the current game version.
 */
class VersionAwareCommonMarkFactory
{
    /**
     * @var Map<int, CommonMarkConverter>
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

    public function getForVersion(?Version $version): CommonMarkConverter
    {
        $versionKey = $version?->getId() ?? 0;
        if (!$this->converters->hasKey($versionKey)) {
            $this->converters->put($versionKey, $this->create($version));
        }

        return $this->converters->get($versionKey);
    }

    private function create(?Version $version): CommonMarkConverter
    {
        if ($version) {
            $environmentConfig = ['currentVersion' => $version] + $this->commonMarkConfig;
        } else {
            $environmentConfig = $this->commonMarkConfig;
        }

        $environment = new Environment($environmentConfig);
        foreach ($this->extensions as $extension) {
            $environment->addExtension($extension);
        }

        return new CommonMarkConverter($this->commonMarkConfig, $environment);
    }
}
