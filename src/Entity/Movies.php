<?php

namespace App\Entity;

use App\Repository\MovieRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=MovieRepository::class)
 */
class Movies
{
    /**
     * @ORM\Id;
     * @ORM\Column(name="tmdb_id", type="integer")
     */
    private $tmdb_id;

    /**
     * @ORM\Column(name="title", type="string", length=255)
     */
    private $title;

    /**
     * @ORM\Column(name="file", type="string", length=255)
     */
    private $file;

    /**
     * @ORM\Column(name="poster", type="string", length=255)
     */
    private $poster;

    public function __construct()
    {
    }

    public function setTmdbId(int $tmdb_id): self
    {
        $this->tmdb_id = $tmdb_id;
        return $this;
    }

    public function getTmdbId(): ?int
    {
        return $this->tmdb_id;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setPoster(string $poster): self
    {
        $this->poster = $poster;
        return $this;
    }

    public function getPoster(): ?string
    {
        return $this->poster;
    }

    public function setFile(string $file): self
    {
        $this->file = $file;
        return $this;
    }

    public function getFile(): ?string
    {
        return $this->file;
    }
}
