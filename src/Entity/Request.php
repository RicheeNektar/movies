<?php

namespace App\Entity;

use App\Repository\RequestRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=RequestRepository::class)
 */
class Request
{
    /**
     * @ORM\Id
     * @ORM\Column(name="tmdb_id", type="integer")
     */
    private int $tmdbId;

    public function setTmdbId(int $tmdbId): self
    {
        $this->tmdbId = $tmdbId;
        return $this;
    }

    public function getTmdbId(): int
    {
        return $this->tmdbId;
    }
}
