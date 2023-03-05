<?php

namespace App\Entity;

use App\Repository\MovieRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=MovieRepository::class)
 * @ORM\HasLifecycleCallbacks()
 */
class Movie extends AbstractMedia
{
    /**
     * @ORM\Id;
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
    private ?string $poster;

    /**
     * @ORM\OneToMany(targetEntity=MovieBackdrop::class, mappedBy="movie", cascade={"all"})
     */
    private $backdrops;

    /**
     * @ORM\Column(name="creation_date", type="datetime_immutable")
     */
    private \DateTimeImmutable $creationDate;

    /**
     * @ORM\Column(name="is_hidden", type="boolean")
     */
    private bool $isHidden;

    /**
     * @ORM\Column(name="air_date", type="date_immutable", nullable=true)
     */
    private ?\DateTimeImmutable $airDate = null;

    /**
     * @ORM\ManyToMany(targetEntity=User::class, mappedBy="watchedMovies")
     */
    private $usersWatched;

    /**
     * @ORM\Column(type="string", length=4096)
     */
    private $description;

    public function __construct()
    {
        $this->backdrops = new ArrayCollection();
        $this->usersWatched = new ArrayCollection();
    }

    /**
     * @ORM\PrePersist()
     */
    public function onPrePersist()
    {
        $this->creationDate = new \DateTimeImmutable();
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

    public function getAirDate(): ?\DateTimeImmutable
    {
        return $this->airDate;
    }

    public function setAirDate(\DateTimeImmutable $airDate): self
    {
        $this->airDate = $airDate;
        return $this;
    }

    public function setIsHidden(bool $isHidden): self
    {
        $this->isHidden = $isHidden;
        return $this;
    }

    public function getIsHidden(): bool
    {
        return $this->isHidden;
    }

    /**
     * @return Collection|MovieBackdrop[]
     */
    public function getBackdrops(): Collection
    {
        return $this->backdrops;
    }

    public function addBackdrop(MovieBackdrop $backdrop): self
    {
        if (!$this->backdrops->contains($backdrop)) {
            $this->backdrops[] = $backdrop;
            $backdrop->setMovie($this);
        }

        return $this;
    }

    public function removeBackdrop(MovieBackdrop $backdrop): self
    {
        if ($this->backdrops->removeElement($backdrop)) {
            // set the owning side to null (unless already changed)
            if ($backdrop->getMovie() === $this) {
                $backdrop->setMovie(null);
            }
        }

        return $this;
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getImageBasePath(): string
    {
        return "movie";
    }
}
