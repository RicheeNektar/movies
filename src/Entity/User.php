<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 */
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     */
    private string $username;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @ORM\Column(type="string")
     */
    private string $password;

    /**
     * @ORM\ManyToMany(targetEntity=Movie::class, inversedBy="usersWatched")
     * @ORM\JoinTable(name="user_watched_movie",
     *     joinColumns={@ORM\JoinColumn(name="user_id")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="movie_id")}
     *     )
     */
    private Collection $watchedMovies;

    /**
     * @ORM\OneToMany(targetEntity=Request::class, mappedBy="user")
     */
    private Collection $requests;

    public function __construct()
    {
        $this->watchedMovies = new ArrayCollection();
        $this->requests = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    /**
     * @deprecated
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return $this->username;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Returning a salt is only needed, if you are not using a modern
     * hashing algorithm (e.g. bcrypt or sodium) in your security.yaml.
     *
     * @see UserInterface
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function isAdmin(): bool
    {
        return array_search('ROLE_ADMIN', $this->getRoles());
    }

    /**
     * @return Collection<int, Movie>
     */
    public function getWatchedMovies(): Collection
    {
        return $this->watchedMovies;
    }

    public function addWatchedMovie(Movie $watchedMovie): self
    {
        if (!$this->watchedMovies->contains($watchedMovie)) {
            $this->watchedMovies[] = $watchedMovie;
            $watchedMovie->addUsersWatched($this);
        }

        return $this;
    }

    public function removeWatchedMovie(Movie $watchedMovie): self
    {
        if ($this->watchedMovies->removeElement($watchedMovie)) {
            $watchedMovie->removeUsersWatched(null);
        }

        return $this;
    }

    public function hasWatched(Movie $movie): bool
    {
        return $this->watchedMovies->contains($movie);
    }

    public function addRequest(Request $request): self
    {
        if (!$this->watchedMovies->contains($request)) {
            $this->watchedMovies[] = $request;
            $request->setUser($this);
        }

        return $this;
    }

    public function removeRequest(Request $request): self
    {
        if ($this->watchedMovies->removeElement($request)) {
            $request->setUser(null);
        }

        return $this;
    }

    public function getRequests(): Collection
    {
        return $this->requests;
    }
}
