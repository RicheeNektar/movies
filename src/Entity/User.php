<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use function Webmozart\Assert\Tests\StaticAnalysis\contains;

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
     * @ORM\ManyToMany(targetEntity=Movie::class, inversedBy="usersWatched", cascade={"all"})
     */
    private Collection $watchedMovies;

    /**
     * @ORM\OneToMany(targetEntity=Message::class, mappedBy="user", cascade={"all"})
     */
    private Collection $messages;

    /**
     * @ORM\OneToMany(targetEntity=Request::class, mappedBy="user", cascade={"all"})
     */
    private Collection $requests;

    /**
     * @ORM\Column(name="access_token", type="string", length=88, unique=true, nullable=true)
     */
    private string $accessToken;

    /**
     * @ORM\ManyToMany(targetEntity=Episode::class, inversedBy="usersWatched", cascade={"all"})
     */
    private $watchedEpisodes;

    /**
     * @ORM\OneToMany(targetEntity=Invitation::class, mappedBy="created_by", orphanRemoval=true)
     */
    private $invitations;

    /**
     * @ORM\OneToOne(targetEntity=Invitation::class, mappedBy="used_by", cascade={"persist", "remove"})
     */
    private $invitation;

    public function __construct()
    {
        $this->watchedMovies = new ArrayCollection();
        $this->requests = new ArrayCollection();
        $this->watchedEpisodes = new ArrayCollection();
        $this->invitations = new ArrayCollection();
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

    public function hasWatched(AbstractMedia $media): bool
    {
        if ($media instanceof Movie) {
            return $this->watchedMovies->contains($media);
        } else if ($media instanceof Episode) {
            return $this->watchedEpisodes->contains($media);
        }

        return false;
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

    public function setAccessToken(string $accessToken): self
    {
        $this->accessToken = $accessToken;
        return $this;
    }

    public function getAccessToken(): string
    {
        return $this->accessToken;
    }

    /**
     * @return Collection<int, Episode>
     */
    public function getWatchedEpisodes(): Collection
    {
        return $this->watchedEpisodes;
    }

    public function addWatchedEpisode(Episode $watchedEpisode): self
    {
        if (!$this->watchedEpisodes->contains($watchedEpisode)) {
            $this->watchedEpisodes[] = $watchedEpisode;
        }

        return $this;
    }

    public function removeWatchedEpisode(Episode $watchedEpisode): self
    {
        $this->watchedEpisodes->removeElement($watchedEpisode);

        return $this;
    }

    /**
     * @return Collection<int, Invitation>
     */
    public function getInvitations(): Collection
    {
        return $this->invitations;
    }

    public function addInvitation(Invitation $invitation): self
    {
        if (!$this->invitations->contains($invitation)) {
            $this->invitations[] = $invitation;
            $invitation->setCreatedBy($this);
        }

        return $this;
    }

    public function removeInvitation(Invitation $invitation): self
    {
        if ($this->invitations->removeElement($invitation)) {
            // set the owning side to null (unless already changed)
            if ($invitation->getCreatedBy() === $this) {
                $invitation->setCreatedBy(null);
            }
        }

        return $this;
    }

    public function getInvitation(): ?Invitation
    {
        return $this->invitation;
    }

    public function setInvitation(?Invitation $invitation): self
    {
        // unset the owning side of the relation if necessary
        if ($invitation === null && $this->invitation !== null) {
            $this->invitation->setUsedBy(null);
        }

        // set the owning side of the relation if necessary
        if ($invitation !== null && $invitation->getUsedBy() !== $this) {
            $invitation->setUsedBy($this);
        }

        $this->invitation = $invitation;

        return $this;
    }
}
