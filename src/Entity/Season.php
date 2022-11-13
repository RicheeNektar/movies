<?php

namespace App\Entity;

use App\Repository\SeasonRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\PersistentCollection;

/**
 * @ORM\Entity(repositoryClass=SeasonRepository::class)
 */
class Season extends AbstractMedia
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\Column(type="integer")
     */
    private int $seasonId;

    /**
     * @ORM\ManyToOne(targetEntity=Series::class, inversedBy="seasons", cascade={"persist"})
     * @ORM\JoinColumn(name="series_id", referencedColumnName="id")
     */
    private ?Series $series;

    /**
     * @ORM\OneToMany(targetEntity=Episode::class, mappedBy="season", orphanRemoval=true, cascade={"all"})
     */
    private Collection $episodes;

    /**
     * @ORM\Column(type="string", length=32, nullable=true)
     */
    private ?string $poster;

    /**
     * @ORM\Column(name="name",type="string", length=255)
     */
    private string $title;

    /**
     * @ORM\Column(name="air_date", type="date_immutable", nullable=true)
     */
    private ?\DateTimeImmutable $airDate = null;

    public function __construct()
    {
        $this->episodes = new ArrayCollection();
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

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(?int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getSeries(): ?Series
    {
        return $this->series;
    }

    public function setSeries(?Series $series): self
    {
        $this->series = $series;

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

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function getSeasonId(): int
    {
        return $this->seasonId;
    }

    public function setSeasonId(int $seasonId): self
    {
        $this->seasonId = $seasonId;
        return $this;
    }

    /**
     * @return Collection|Episode[]
     */
    public function getEpisodes(): Collection
    {
        return $this->episodes;
    }

    public function addEpisode(Episode $episode): self
    {
        if (!$this->episodes->contains($episode)) {
            $this->episodes[] = $episode;
            $episode->setSeason($this);
        }

        return $this;
    }

    public function removeEpisode(Episode $episode): self
    {
        if ($this->episodes->removeElement($episode)) {
            // set the owning side to null (unless already changed)
            if ($episode->getSeason() === $this) {
                $episode->setSeason(null);
            }
        }

        return $this;
    }

    public function getImageBasePath(): string
    {
        return "season";
    }
}
