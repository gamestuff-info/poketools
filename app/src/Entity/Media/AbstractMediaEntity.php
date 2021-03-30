<?php
/**
 * @file AbstractMediaEntity.php
 */

namespace App\Entity\Media;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Class AbstractMediaEntity
 */
abstract class AbstractMediaEntity
{
    use MediaEntityTrait;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @ORM\Id()
     * @Groups({"read"})
     */
    protected ?string $url;

    public function __construct(?string $url = null)
    {
        if ($url !== null) {
            $this->url = $url;
        }
    }

}
