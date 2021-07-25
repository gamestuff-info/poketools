<?php


namespace App\ApiPlatform;


use App\CommonMark\VersionAwareCommonMarkFactory;
use App\Entity\MarkdownProperty;
use Ds\Map;
use Ds\Vector;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Normalizer
 *
 * Injects rendered markdown into the entity
 */
class Normalizer implements NormalizerInterface, SerializerAwareInterface
{
    /**
     * Cache which properties are markdown fields
     *
     * @var Map<class-string, Vector<\ReflectionProperty>>
     */
    private Map $classMarkdownProperties;

    public function __construct(
        private NormalizerInterface $decorated,
        private VersionAwareCommonMarkFactory $commonMarkFactory,
    ) {
        $this->classMarkdownProperties = new Map();
    }

    /**
     * @inheritDoc
     */
    public function normalize($object, string $format = null, array $context = [])
    {
        if (is_object($object)) {
            $this->injectMarkdown($object, $context);
        }

        return $this->decorated->normalize($object, $format, $context);
    }

    /**
     * Inject rendered markdown into object properties
     *
     * @param object $object
     * @param array $context
     */
    private function injectMarkdown(object $object, array $context): void
    {
        $objectClass = get_class($object);
        if (!$this->classMarkdownProperties->hasKey($objectClass)) {
            $this->classMarkdownProperties->put($objectClass, $this->findMarkdownProperties($object));
        }
        $commonMarkConverter = $this->commonMarkFactory->getForVersion($context['_useVersion'] ?? null);
        /** @var \ReflectionProperty $property */
        foreach ($this->classMarkdownProperties->get($objectClass) as $property) {
            $markdown = $property->getValue($object);
            if (!is_string($markdown)) {
                continue;
            }
            $html = trim($commonMarkConverter->convertToHtml($markdown)->getContent());
            $property->setValue($object, $html);
        }
    }

    /**
     * Finds the properties with Markdown
     *
     * @param object $object
     *
     * @return Vector<\ReflectionProperty>
     */
    private function findMarkdownProperties(object $object): Vector
    {
        $markdownProperties = new Vector();
        $refl = new \ReflectionClass($object);
        foreach ($refl->getProperties() as $property) {
            if ($property->getAttributes(MarkdownProperty::class)) {
                $property->setAccessible(true);
                $markdownProperties->push($property);
            }
        }

        return $markdownProperties;
    }

    /**
     * @inheritDoc
     */
    public function supportsNormalization($data, string $format = null)
    {
        return $this->decorated->supportsNormalization($data, $format);
    }

    /**
     * @inheritDoc
     */
    public function setSerializer(SerializerInterface $serializer)
    {
        if ($this->decorated instanceof SerializerAwareInterface) {
            $this->decorated->setSerializer($serializer);
        }
    }
}
