<?php

namespace romanzipp\ModelDoc\Services\Objects;

use Illuminate\Database\Eloquent\Model as IlluminateModel;
use ReflectionClass;
use ReflectionException;
use romanzipp\ModelDoc\Exceptions\InvalidModelException;
use Symfony\Component\Finder\SplFileInfo;

final class Model
{
    private SplFileInfo $fileInfo;

    /**
     * @var \ReflectionClass<IlluminateModel>|null
     */
    private ?ReflectionClass $reflectionClass = null;

    /**
     * Model constructor.
     *
     * @param \Symfony\Component\Finder\SplFileInfo $fileInfo
     *
     * @throws \romanzipp\ModelDoc\Exceptions\InvalidModelException
     */
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

    /**
     * @return \ReflectionClass<IlluminateModel>
     */
    public function getReflectionClass(): ReflectionClass
    {
        return $this->reflectionClass;
    }

    public function getName(): string
    {
        return "{$this->reflectionClass->getNamespaceName()}\\{$this->reflectionClass->getName()}";
    }

    private function load(): void
    {
        require_once $this->fileInfo->getPathname();
    }

    public function getQualifiedClassName(): string
    {
        $fileName = str_replace('.php', '', $this->fileInfo->getFilename());

        return "{$this->guessNamespace()}\\{$fileName}";
    }

    private function isClassLoaded(): bool
    {
        return class_exists(
            $this->getQualifiedClassName()
        );
    }
}
