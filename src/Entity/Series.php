<?php

namespace App\Entity;

use App\Repository\SeriesRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=SeriesRepository::class)
 */
class Series
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     */
    private $tmdb_id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $title;

    /**
     * @ORM\Column(type="string", length=32)
     */
    private $poster;

    /**
     * @ORM\OneToMany(targetEntity=Season::class, mappedBy="series", orphanRemoval=true)
     */
    private $seasons;

    /**
     * @ORM\OneToMany(targetEntity=SeriesBackdrop::class, mappedBy="series")
     */
    private $backdrops;

    public function __construct()
    {
        $this->seasons = new ArrayCollection();
        $this->backdrops = new ArrayCollection();
    }

    public function getTmdbId(): ?int
    {
        return $this->tmdb_id;
    }

    public function setTmdbId(int $tmdb_id): self
    {
        $this->tmdb_id = $tmdb_id;
        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getPoster(): ?string
    {
        return $this->poster;
    }

    public function setPoster(string $poster): self
    {
        $this->poster = $poster;

        return $this;
    }

    /**
     * @return Collection|Season[]
     */
    public function getSeasons(): Collection
    {
        return $this->seasons;
    }

    public function addSeason(Season $season): self
    {
        if (!$this->seasons->contains($season)) {
            $this->seasons[] = $season;
            $season->setSeries($this);
        }

        return $this;
    }

    public function removeSeason(Season $season): self
    {
        if ($this->seasons->removeElement($season)) {
            // set the owning side to null (unless already changed)
            if ($season->getSeries() === $this) {
                $season->setSeries(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|SeriesBackdrop[]
     */
    public function getBackdrops(): Collection
    {
        return $this->backdrops;
    }

    public function addBackdrop(SeriesBackdrop $backdrop): self
    {
        if (!$this->backdrops->contains($backdrop)) {
            $this->backdrops[] = $backdrop;
            $backdrop->setSeries($this);
        }

        return $this;
    }

    public function removeBackdrop(SeriesBackdrop $backdrop): self
    {
        if ($this->backdrops->removeElement($backdrop)) {
            // set the owning side to null (unless already changed)
            if ($backdrop->getSeries() === $this) {
                $backdrop->setSeries(null);
            }
        }

        return $this;
    }
}
