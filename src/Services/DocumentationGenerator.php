<?php

namespace romanzipp\ModelDoc\Services;

use Doctrine\DBAL\Exception as DoctrineException;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Types;
use Generator;
use gossi\docblock\Docblock;
use gossi\docblock\tags\PropertyTag;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model as IlluminateModel;
use Illuminate\Database\Eloquent\Relations;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use ModelDocumentationFailedException;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use ReflectionNamedType;
use romanzipp\ModelDoc\Exceptions\InvalidModelException;
use romanzipp\ModelDoc\Services\Objects\Model;
use Symfony\Component\Finder\Finder;

class DocumentationGenerator
{
    public function collectModels(): Generator
    {
        $finder = new Finder();
        $finder->files()->in(base_path('app/'));

        foreach ($finder as $file) {
            try {
                $model = new Model($file);
            } catch (InvalidModelException $exception) {
                continue;
            }

            yield $model;
        }
    }

    /**
     * @param \ReflectionClass $reflectionClass
     *
     * @return \ReflectionMethod[]
     */
    private function getModelRelationMethods(ReflectionClass $reflectionClass): array
    {
        $reflectionMethods = $reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC);

        $methods = [];

        foreach ($reflectionMethods as $reflectionMethod) {
            $reflectionReturnType = $reflectionMethod->getReturnType();

            if ( ! ($reflectionReturnType instanceof ReflectionNamedType)) {
                continue;
            }

            if ($reflectionReturnType->isBuiltin()) {
                continue;
            }

            try {
                $returnReflection = new ReflectionClass($reflectionReturnType->getName());
            } catch (ReflectionException $exception) {
                continue;
            }

            if ( ! $returnReflection->isSubclassOf(Relations\Relation::class)) {
                continue;
            }

            $methods[] = $reflectionMethod;
        }

        return $methods;
    }

    /**
     * @param \ReflectionMethod $reflectionMethod
     * @param \Illuminate\Database\Eloquent\Relations\Relation $relation
     *
     * @return \gossi\docblock\tags\PropertyTag[]
     */
    private function getPropertiesForRelation(ReflectionMethod $reflectionMethod, Relations\Relation $relation): array
    {
        $related = $relation->getRelated();

        $isMany = false;

        foreach ([Relations\HasMany::class, Relations\HasManyThrough::class, Relations\MorphMany::class, Relations\BelongsToMany::class] as $relationClass) {
            $isMany = $isMany || $relation instanceof $relationClass;
        }

        $propertyReturns = $isMany
            ? [
                '\\' . Collection::class,
                '\\' . get_class($related) . '[]',
            ]
            : [
                '\\' . get_class($related),
                'null',
            ];

        $relationProperty = new PropertyTag();
        $relationProperty->setVariable("\${$reflectionMethod->getName()}");
        $relationProperty->setType(
            implode('|', $propertyReturns)
        );

        $countVariable = Str::snake($reflectionMethod->getName());
        $countVariable = Str::plural($countVariable);

        $countProperty = new PropertyTag();
        $countProperty->setVariable("\${$countVariable}_count");
        $countProperty->setType('int|null');

        return [$relationProperty, $countProperty];
    }

    /**
     * @param \gossi\docblock\Docblock $docblock
     * @param \ReflectionClass $reflectionClass
     *
     * @throws \ModelDocumentationFailedException
     */
    private function writeDoc(Docblock $docblock, ReflectionClass $reflectionClass): void
    {
        $content = file_get_contents($reflectionClass->getFileName());

        $lineIndexClassDeclaration = null;

        $lines = explode(PHP_EOL, $content);

        foreach ($lines as $index => $line) {
            if ( ! preg_match('/^(abstract|final)? ?class ([A-z]+)/', $line)) {
                continue;
            }

            $lineIndexClassDeclaration = $index;
            break;
        }

        if (null === $lineIndexClassDeclaration) {
            throw new ModelDocumentationFailedException('Cant find class declaration');
        }

        // Remove existing phpdoc

        foreach ($lines as $index => $line) {
            if ($index >= $lineIndexClassDeclaration) {
                break;
            }

            if ( ! Str::startsWith($line, ['/**', ' *', ' */'])) {
                continue;
            }

            $lines[$index] = null;
        }

        $docLines = explode(PHP_EOL, $docblock->toString());

        foreach (array_reverse($docLines) as $docLine) {
            array_splice($lines, $lineIndexClassDeclaration, 0, $docLine);
        }

        $lines = array_filter($lines, static fn ($line) => null !== $line);

        file_put_contents($reflectionClass->getFileName(), implode(PHP_EOL, $lines));
    }

    /**
     * @param \ReflectionClass $reflectionClass
     * @param \Illuminate\Database\Eloquent\Model $model
     *
     * @throws \ModelDocumentationFailedException
     *
     * @return array
     */
    private function getModelAttributesProperties(ReflectionClass $reflectionClass, IlluminateModel $model): array
    {
        $properties = [];

        $connection = $model->getConnection();

        $schemaManager = $connection->getDoctrineSchemaManager();

        try {
            $tableColumns = $schemaManager->listTableColumns($model->getTable());
        } catch (DoctrineException $exception) {
            throw new ModelDocumentationFailedException('failed', 0, $exception);
        }

        foreach ($tableColumns as $tableColumn) {
            $property = new PropertyTag();
            $property->setVariable("\${$tableColumn->getName()}");

            $types = $this->getTypesForTableColumn($model, $tableColumn);

            if ( ! empty($types)) {
                $property->setType(
                    implode('|', $types)
                );
            }

            if ($comment = $tableColumn->getComment()) {
                $property->setDescription($comment);
            }

            $properties[] = $property;
        }

        return $properties;
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param \Doctrine\DBAL\Schema\Column $column
     *
     * @throws \ModelDocumentationFailedException
     *
     * @return array
     */
    private function getTypesForTableColumn(IlluminateModel $model, Column $column): array
    {
        $castsMapping = [
            'json' => 'array',
        ];

        $types = [];

        $casts = $model->getCasts();

        if (method_exists($model, 'getStates')) {
            foreach ($model::getStates() as $stateAttribute => $state) {
                if ($column->getName() !== $stateAttribute) {
                    continue;
                }

                try {
                    $class = new ReflectionClass($state->first());
                } catch (ReflectionException $exception) {
                    throw new ModelDocumentationFailedException('Failed get type for database column', 0, $exception);
                }

                $types[] = '\\' . $class->getParentClass()->getName();
            }
        }

        foreach ($model->getDates() as $date) {
            if ($column->getName() !== $date) {
                continue;
            }

            $types[] = '\\' . Carbon::class;
        }

        if (empty($types)) {
            if (array_key_exists($column->getName(), $casts) && array_key_exists($casts[$column->getName()], $castsMapping)) {
                $types[] = $castsMapping[$casts[$column->getName()]];
            } else {
                switch (get_class($column->getType())) {
                    case Types\IntegerType::class:
                    case Types\BigIntType::class:
                        $types[] = 'int';
                        break;
                    case Types\FloatType::class:
                        $types[] = 'float';
                        break;
                    case Types\StringType::class:
                    case Types\TextType::class:
                    case Types\JsonType::class:
                    case Types\DateTimeType::class:
                        $types[] = 'string';
                        break;
                    case Types\BooleanType::class:
                        $types[] = 'bool';
                        break;
                }
            }
        }

        if (false === $column->getNotnull()) {
            $types[] = 'null';
        }

        return $types;
    }

    /**
     * @param \romanzipp\ModelDoc\Services\Objects\Model $model
     *
     * @throws \ModelDocumentationFailedException
     */
    public function generate(Model $model): void
    {
        $doc = new Docblock();

        $reflectionClass = $model->getReflectionClass();

        if ( ! $reflectionClass->isAbstract()) {
            try {
                /** @var \Illuminate\Database\Eloquent\Model $instance */
                $instance = $reflectionClass->newInstance();
            } catch (ReflectionException $exception) {
                throw new ModelDocumentationFailedException('Can not create model instance', 0, $exception);
            }

            foreach ($this->getModelAttributesProperties($reflectionClass, $instance) as $property) {
                $doc->appendTag($property);
            }
        }

        foreach ($this->getModelRelationMethods($reflectionClass) as $reflectionMethod) {
            /** @var \Illuminate\Database\Eloquent\Relations\Relation $relation */
            $relation = $model->{$reflectionMethod->getName()}();

            if ($relation instanceof Relations\MorphOneOrMany || $relation instanceof Relations\MorphTo) {
                continue; // TODO
            }

            $properties = $this->getPropertiesForRelation($reflectionMethod, $relation);

            if (empty($properties)) {
                continue;
            }

            foreach ($properties as $property) {
                $doc->appendTag($property);
            }
        }

        if ($doc->getTags()->isEmpty()) {
            throw new ModelDocumentationFailedException('The tag is empty');
        }

        $this->writeDoc($doc, $reflectionClass);
    }
}
