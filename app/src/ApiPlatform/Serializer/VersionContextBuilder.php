<?php


namespace App\ApiPlatform\Serializer;


use ApiPlatform\Core\Api\IriConverterInterface;
use ApiPlatform\Core\Exception\InvalidArgumentException;
use ApiPlatform\Core\Exception\ItemNotFoundException;
use ApiPlatform\Core\Serializer\SerializerContextBuilderInterface;
use App\Entity\Version;
use App\Repository\VersionRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Adds the current version to the serialization context.
 *
 * The current version is added to the context as `_useVersion`.  The following request parameters are checked for a
 * version ID or IRI:
 * - _useVersion
 * - versionGroup.versions
 *
 * The first parameter present will be used.
 */
class VersionContextBuilder implements SerializerContextBuilderInterface
{
    public function __construct(
        private SerializerContextBuilderInterface $decorated,
        private IriConverterInterface $iriConverter,
        private VersionRepository $versionRepository,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function createFromRequest(Request $request, bool $normalization, array $extractedAttributes = null): array
    {
        $context = $this->decorated->createFromRequest($request, $normalization, $extractedAttributes);
        $version = $this->getVersion($request);
        if ($version) {
            $context['_useVersion'] = $version;
        }

        return $context;
    }

    private function getVersion(Request $request): ?Version
    {
        $versionId = null;
        foreach (['_useVersion', 'versionGroup_versions'] as $param) {
            if ($versionId = $request->get($param)) {
                break;
            }
        }
        if (is_array($versionId)) {
            $versionId = $versionId[0];
        }
        if ($versionId === null) {
            return null;
        }
        $version = null;
        if (is_numeric($versionId)) {
            $version = $this->versionRepository->find($versionId);
        } else {
            try {
                $version = $this->iriConverter->getItemFromIri($versionId);
            } catch (InvalidArgumentException | ItemNotFoundException $e) {
                $version = null;
            }
        }

        if (!$version || !$version instanceof Version) {
            throw new BadRequestHttpException(sprintf('Version %s is not valid', $versionId));
        }

        return $version;
    }
}
