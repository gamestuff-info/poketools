<?php


namespace App\ApiPlatform\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Serializer\Filter\GroupFilter;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Search results
 */
#[ApiResource(
    normalizationContext: ['groups' => ['read']],
)]
#[ApiFilter(GroupFilter::class)]
class SearchResult
{
    /**
     * @Groups({"read"})
     */
    #[ApiProperty(identifier: true)]
    private string $type;

    /**
     * @Groups({"read"})
     */
    #[ApiProperty(identifier: true)]
    private int $id;

    /**
     * @Groups({"read"})
     */
    private object $result;

    /**
     * @Groups({"read"})
     */
    private string $label;

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     *
     * @return SearchResult
     */
    public function setType(string $type): SearchResult
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return SearchResult
     */
    public function setId(int $id): SearchResult
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return object
     */
    public function getResult(): object
    {
        return $this->result;
    }

    /**
     * @param object $result
     *
     * @return SearchResult
     */
    public function setResult(object $result): SearchResult
    {
        $this->result = $result;

        return $this;
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * @param string $label
     *
     * @return SearchResult
     */
    public function setLabel(string $label): SearchResult
    {
        $this->label = $label;

        return $this;
    }
}
