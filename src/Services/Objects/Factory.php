<?php

namespace romanzipp\ModelDoc\Services\Objects;

use Illuminate\Database\Eloquent\Factories\Factory as IlluminateFactory;
use romanzipp\ModelDoc\Exceptions\InvalidModelException;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

final class Factory extends AbstractDocumentableClass
{
    public function __construct(SplFileInfo $fileInfo)
    {
        parent::__construct($fileInfo);

        if ( ! $this->reflectionClass->isSubclassOf(IlluminateFactory::class)) {
            throw new InvalidModelException('Class does not extend Illuminate\Database\Eloquent\Factories\Factory');
        }
    }

    public static function fromModel(Model $model): ?self
    {
        if ( ! method_exists($model->getInstance(), 'factory')) {
            return null;
        }

        /** @var \Illuminate\Database\Eloquent\Factories\Factory $illuminateFactory */
        $illuminateFactory = $model->getInstance()::factory();

        $refClass = new \ReflectionClass($illuminateFactory);

        ['dirname' => $dir, 'filename' => $file] = pathinfo($refClass->getFileName());

        $finder = new Finder();
        $finder->files()->name("{$file}.php")->in($dir);

        foreach ($finder as $file) {
            return new self($file);
        }

        return null;
    }
}
