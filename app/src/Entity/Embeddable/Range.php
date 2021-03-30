<?php


namespace App\Entity\Embeddable;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;

/**
 * A range of values
 *
 * @ORM\Embeddable()
 */
class Range
{

    /**
     * Minimum value
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"read"})
     */
    private ?int $min;

    /**
     * Maximum value
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"read"})
     */
    private ?int $max;

    /**
     * @param string $range
     *
     * @return self
     */
    public static function fromString(string $range): self
    {
        if (empty($range)) {
            return (new self())->setMin(null)->setMax(null);
        } elseif (!str_contains($range, '-')) {
            return (new self())->setMin($range)->setMax($range);
        } else {
            $parts = explode('-', $range);

            return (new self())->setMin($parts[0])->setMax($parts[1]);
        }
    }

    /**
     * Compare two range objects for equality.
     *
     * @param Range $a
     * @param Range $b
     *
     * @return bool
     */
    public static function equals(Range $a, Range $b): bool
    {
        return $a->getMin() === $b->getMin()
            && $a->getMax() === $b->getMax();
    }

    public function __toString(): string
    {
        return $this->getString();
    }

    /**
     * @Groups({"read"})
     * @SerializedName("toString")
     */
    public function getString(): string
    {
        if ($this->isNull()) {
            return '';
        } elseif ($this->getMin() === $this->getMax()) {
            return (string)$this->getMin();
        } else {
            return sprintf('%d-%d', $this->getMin(), $this->getMax());
        }
    }

    public function isNull(): bool
    {
        return $this->getMin() === null || $this->getMax() === null;
    }

    public function getMin(): ?int
    {
        return $this->min;
    }

    public function setMin(?int $min): self
    {
        $this->min = $min;

        return $this;
    }

    public function getMax(): ?int
    {
        return $this->max;
    }

    public function setMax(?int $max): self
    {
        $this->max = $max;

        return $this;
    }
}
