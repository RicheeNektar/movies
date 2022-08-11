<?php

namespace App\Entity;

use App\Repository\EpisodeRepository;
use Doctrine\Common\Collections\Collection;
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
     * @ORM\JoinColumn(name="season_id", referencedColumnName="id")
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

    /**
     * @ORM\ManyToMany(targetEntity=User::class, mappedBy="watchedEpisodes")
     */
    private $usersWatched;

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


    /**
     * @return Collection<int, User>
     */
    public function getUsersWatched(): Collection
    {
        return $this->usersWatched;
    }

    public function addUsersWatched(User $usersWatched): self
    {
        if (!$this->usersWatched->contains($usersWatched)) {
            $this->usersWatched[] = $usersWatched;
        }

        return $this;
    }

    public function removeUsersWatched(User $usersWatched): self
    {
        $this->usersWatched->removeElement($usersWatched);

        return $this;
    }
}
