<?php

namespace romanzipp\ModelDoc\Services\Objects;

use Illuminate\Database\Eloquent\Model as IlluminateModel;
use Illuminate\Support\Str;
use ReflectionClass;
use ReflectionException;
use romanzipp\ModelDoc\Exceptions\InvalidModelException;
use Symfony\Component\Finder\SplFileInfo;

final class Model
{
    private SplFileInfo $fileInfo;

    private ?ReflectionClass $reflectionClass = null;

    public function __construct(SplFileInfo $fileInfo)
    {
        $this->fileInfo = $fileInfo;

        if ( ! $this->fileExists()) {
            throw new InvalidModelException('File not found');
        }

        if (null === $this->guessNamespace()) {
            throw new InvalidModelException('Model namespace not found');
        }

        if ( ! $this->isClassLoaded()) {
            $this->load();
        }

        try {
            $this->reflectionClass = new ReflectionClass(
                $this->getQualifiedClassName()
            );
        } catch (ReflectionException $exception) {
            throw new InvalidModelException('Could not create reflection class');
        }

        if ( ! $this->reflectionClass->isSubclassOf(IlluminateModel::class)) {
            throw new InvalidModelException('Class does not extend Illuminate\Database\Eloquent\Model');
        }
    }

    public function fileExists(): bool
    {
        return file_exists(
            $this->fileInfo->getPathname()
        );
    }

    public function guessNamespace(): ?string
    {
        $contents = file_get_contents($this->fileInfo->getPathname());

        if (preg_match('/namespace\s+(.+?);/m', $contents, $namespaceMatches)) {
            return $namespaceMatches[1];
        }

        return null;
    }

    public function getReflectionClass(): ReflectionClass
    {
        return $this->reflectionClass;
    }

    public function refreshReflection(): void
    {
        $this->reflectionClass = new ReflectionClass(
            $this->getQualifiedClassName()
        );
    }

    public function getName(): string
    {
        return "{$this->reflectionClass->getNamespaceName()}\\{$this->reflectionClass->getName()}";
    }

    private function load(): void
    {
        require_once $this->fileInfo->getPathname();
    }

    public function getQualifiedClassName()
    {
        return "{$this->guessNamespace()}\\{$this->fileInfo->getFilenameWithoutExtension()}";
    }

    private function isClassLoaded(): bool
    {
        return class_exists(
            $this->getQualifiedClassName()
        );
    }

    private function isIncluded(): bool
    {
        $path = $this->fileInfo->getPathname();

        if ( ! Str::contains($path, 'Models/')) {
            return false;
        }

        return true;
    }
}
