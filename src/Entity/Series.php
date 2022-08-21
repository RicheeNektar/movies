<?php

namespace App\Entity;

use App\Repository\SeriesRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=SeriesRepository::class)
 * @ORM\HasLifecycleCallbacks
 */
class Series extends AbstractMedia
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $title;

    /**
     * @ORM\Column(type="string", length=32, nullable=true)
     */
    private $poster;

    /**
     * @ORM\OneToMany(targetEntity=Season::class, mappedBy="series", orphanRemoval=true, cascade={"all"})
     */
    private $seasons;

    /**
     * @ORM\OneToMany(targetEntity=SeriesBackdrop::class, mappedBy="series", cascade={"all"})
     */
    private $backdrops;

    /**
     * @ORM\Column(name="last_updated", type="datetime_immutable")
     */
    private \DateTimeImmutable $lastUpdated;

    /**
     * @ORM\Column(name="air_date", type="date_immutable", nullable=true)
     */
    private ?\DateTimeImmutable $airDate = null;

    /**
     * @ORM\Column(type="string", length=4096)
     */
    private $description;

    public function __construct()
    {
        $this->seasons = new ArrayCollection();
        $this->backdrops = new ArrayCollection();
    }

    /**
     * @ORM\PrePersist
     */
    public function onPrePersist()
    {
        $this->lastUpdated = new \DateTimeImmutable();
    }

    public function getAirDate(): ?\DateTimeImmutable
    {
        return $this->airDate;
    }

    public function setAirDate(\DateTimeImmutable $airDate): self
    {
        $this->airDate = $airDate;
        return $this;
    }

    public function getCreationDate(): \DateTimeImmutable
    {
        return $this->creationDate;
    }

    public function setCreationDate(\DateTimeImmutable $creationDate): self
    {
        $this->creationDate = $creationDate;
        return $this;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getTitle(): string
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

    public function setPoster(?string $poster): self
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }
}
