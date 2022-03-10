<?php

namespace App\Entity;

use App\Repository\RequestRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=RequestRepository::class)
 */
class Request
{
    /**
     * @ORM\Id
     * @ORM\OneToOne(targetEntity=Movie::class, cascade={"persist"})
     * @ORM\JoinColumn(name="tmdb_id", referencedColumnName="id", nullable=false)
     */
    private Movie $movie;

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="requests")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     */
    private User $user;

    public function setMovie(Movie $movie): self
    {
        $this->movie = $movie;
        return $this;
    }

    public function getMovie(): Movie
    {
        return $this->movie;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;
        return $this;
    }

    public function getUser(): User
    {
        return $this->user;
    }
}
