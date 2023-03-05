<?php

namespace App\Entity;

abstract class AbstractMedia {
    public abstract function getId(): int;

    public abstract function getTitle(): string;

    public abstract function getPoster(): ?string;

    protected abstract function getImageBasePath(): string;

    public function getAsset(): string
    {
        return "{$this->getImageBasePath()}/{$this->getId()}";
    }

    public function getCreationDate(): ?\DateTimeImmutable
    {
        return null;
    }

    public abstract function getAirDate(): ?\DateTimeImmutable;
}
