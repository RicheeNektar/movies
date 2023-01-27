<?php

namespace App\Entity;

use App\Repository\MessageRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=MessageRepository::class)
 * @ORM\HasLifecycleCallbacks
 */
class Message
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer", nullable=false)
     */
    private int $id;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="messages")
     * @ORM\JoinColumn(name="user_id", nullable=false)
     */
    private User $user;

    /**
     * @ORM\Column(type="string", length="64", nullable=false)
     */
    private string $title;

    /**
     * @ORM\Column(type="string", length="255", nullable=false)
     */
    private string $text;

    /**
     * @ORM\Column(name="create_at", type="datetime_immutable", nullable=false)
     */
    private \DateTimeImmutable $createAt;

    /**
     * @ORM\PrePersist
     */
    public function onPrePersist()
    {
        $this->createAt = new \DateTimeImmutable();
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

    public function getText(): string
    {
        return $this->text;
    }

    public function setText(string $text): self
    {
        $this->text = $text;

        return $this;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;
        return $this;
    }

    public function getCreateAt(): \DateTimeImmutable
    {
        return $this->createAt;
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
}