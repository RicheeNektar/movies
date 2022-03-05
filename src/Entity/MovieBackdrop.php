<?php

namespace App\Entity;

use App\Repository\MovieBackdropRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=BackdropRepository::class)
 */
class MovieBackdrop
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Movie::class, inversedBy="backdrops")
     * @ORM\JoinColumn(name="movie_id", referencedColumnName="id")
     */
    private $movie;

    /**
     * @ORM\Column(type="string", length=32)
     */
    private $file;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMovie(): ?Movie
    {
        return $this->movie;
    }

    public function setMovie(?Movie $movie): self
    {
        $this->movie = $movie;

        return $this;
    }

    public function getFile(): ?string
    {
        return $this->file;
    }

    public function setFile(string $file): self
    {
        $this->file = $file;

        return $this;
    }
}
