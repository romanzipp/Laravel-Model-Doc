<?php

namespace romanzipp\ModelDoc\Services\Objects;

use Illuminate\Database\Eloquent\Model as IlluminateModel;
use romanzipp\ModelDoc\Exceptions\InvalidModelException;
use Symfony\Component\Finder\SplFileInfo;

final class Model extends AbstractDocumentableClass implements DocumentableClass
{
    private ?IlluminateModel $modelInstance = null;

    public function __construct(SplFileInfo $fileInfo)
    {
        parent::__construct($fileInfo);

        if ( ! $this->reflectionClass->isSubclassOf(IlluminateModel::class)) {
            throw new InvalidModelException('Class does not extend Illuminate\Database\Eloquent\Model');
        }

        if ( ! $this->reflectionClass->isAbstract()) {
            $this->modelInstance = new $this->qualifiedClassName();
        }
    }

    public function getInstance(): ?IlluminateModel
    {
        return $this->modelInstance;
    }
}
