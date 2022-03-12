<?php

namespace App\Entity;

use App\Repository\EpisodeRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=EpisodeRepository::class)
 */
class Episode extends AbstractMedia
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
     * @ORM\ManyToOne(targetEntity=Season::class, inversedBy="episodes", cascade={"persist"})
     * @ORM\JoinColumn(name="season_id")
     */
    private $season;

    /**
     * @ORM\ManyToOne(targetEntity=Series::class, cascade={"persist"})
     * @ORM\JoinColumn(name="series_id", referencedColumnName="id")
     */
    private $series;

    /**
     * @ORM\Column(name="air_date", type="date_immutable", nullable=true)
     */
    private ?\DateTimeImmutable $airDate = null;

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

    public function getSeason(): ?Season
    {
        return $this->season;
    }

    public function setSeason(Season $season): self
    {
        $this->season = $season;

        return $this;
    }

    public function getSeries(): ?Series
    {
        return $this->series;
    }

    public function setSeries(Series $series): self
    {
        $this->series = $series;
        return $this;
    }

    public function getPoster(): string
    {
        return '';
    }
}
