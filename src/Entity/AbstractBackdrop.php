<?php

namespace App\Entity;

abstract class AbstractBackdrop {
    public abstract function getId(): int;

    public abstract function getFile(): ?string;

    protected abstract function getImageBasePath(): string;

    public function getAsset(): string
    {
        return "{$this->getImageBasePath()}/{$this->getId()}";
    }
}
