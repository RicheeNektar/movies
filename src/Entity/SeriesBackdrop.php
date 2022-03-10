<?php

namespace App\Entity;

use App\Repository\SeriesBackdropRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=SeriesBackdropRepository::class)
 */
class SeriesBackdrop
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=32)
     */
    private $file;

    /**
     * @ORM\ManyToOne(targetEntity=Series::class, inversedBy="backdrops")
     * @ORM\JoinColumn(name="series_id", referencedColumnName="id")
     */
    private $series;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getFile(): ?string
    {
        return $this->file;
    }

    public function setFile(string $file): self
    {
        $this->file = $file;

        return $this;
    }
}
