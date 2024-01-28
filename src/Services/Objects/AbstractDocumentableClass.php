<?php

namespace romanzipp\ModelDoc\Services\Objects;

use Illuminate\Database\Eloquent\Model as IlluminateModel;
use romanzipp\ModelDoc\Exceptions\InvalidModelException;
use Symfony\Component\Finder\SplFileInfo;

abstract class AbstractDocumentableClass
{
    protected SplFileInfo $fileInfo;

    /**
     * @var \ReflectionClass<IlluminateModel>|null
     */
    protected ?\ReflectionClass $reflectionClass = null;

    protected string $qualifiedClassName;

    /**
     * Model constructor.
     *
     * @param SplFileInfo $fileInfo
     *
     * @throws InvalidModelException
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

        $this->qualifiedClassName = $this->getQualifiedClassName();

        try {
            $this->reflectionClass = new \ReflectionClass($this->qualifiedClassName);
        } catch (\ReflectionException $exception) {
            throw new InvalidModelException('Could not create reflection class');
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
    public function getReflectionClass(): \ReflectionClass
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

    public function isIgnored(): bool
    {
        return in_array($this->reflectionClass->name, (array) config('model-doc.ignore', []));
    }

    private function isClassLoaded(): bool
    {
        return class_exists(
            $this->getQualifiedClassName()
        );
    }
}
