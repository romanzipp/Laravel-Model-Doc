<?php

namespace romanzipp\ModelDoc\Services;

use Doctrine\DBAL\Exception as DoctrineException;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Types;
use gossi\docblock\Docblock;
use gossi\docblock\tags\MethodTag;
use gossi\docblock\tags\PropertyTag;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model as IlluminateModel;
use Illuminate\Database\Eloquent\Relations;
use Illuminate\Support\Str;
use romanzipp\ModelDoc\Exceptions\InvalidModelException;
use romanzipp\ModelDoc\Exceptions\ModelDocumentationFailedException;
use romanzipp\ModelDoc\Services\Objects\Model;
use romanzipp\ModelDoc\Services\Tags\MixinTag;
use Symfony\Component\Finder\Finder;

class DocumentationGenerator
{
    /**
     * @var callable|null
     */
    public static $pathCallback = null;

    public static function usePath(callable $pathCallback): void
    {
        self::$pathCallback = $pathCallback;
    }

    /**
     * @return \Generator<\romanzipp\ModelDoc\Services\Objects\Model>
     */
    public function collectModels(): \Generator
    {
        $path = base_path('app/');

        if (isset(self::$pathCallback)) {
            $path = (self::$pathCallback)();
        }

        $finder = new Finder();
        $finder->files()->name('*.php')->in($path);

        foreach ($finder as $file) {
            try {
                $model = new Model($file);
            } catch (InvalidModelException $exception) {
                continue;
            }

            if ($model->isIgnored()) {
                continue;
            }

            yield $model;
        }
    }

    /**
     * @param \romanzipp\ModelDoc\Services\Objects\Model $model
     *
     * @throws \romanzipp\ModelDoc\Exceptions\ModelDocumentationFailedException
     *
     * @return \gossi\docblock\Docblock
     */
    public function generateDocBlock(Model $model): Docblock
    {
        $tags = [];

        $reflectionClass = $model->getReflectionClass();

        if ($mixinClasses = config('model-doc.custom_tags.mixins')) {
            foreach ($mixinClasses as $mixinClass) {
                $tags[] = new MixinTag($mixinClass);
            }
        }

        // 1. Generate properties from database columns

        if (true === config('model-doc.attributes.enabled') && ! $reflectionClass->isAbstract()) {
            try {
                /** @var \Illuminate\Database\Eloquent\Model $instance */
                $instance = $reflectionClass->newInstance();
            } catch (\ReflectionException $exception) {
                throw new ModelDocumentationFailedException('Can not create model instance', 0, $exception);
            }

            foreach ($this->getModelAttributesProperties($reflectionClass, $instance) as $property) {
                $tags[] = $property;
            }
        }

        // 2. Generate properties from model accessors

        if (true === config('model-doc.accessors.enabled')) {
            foreach ($this->getModelAccessors($reflectionClass) as $property) {
                $tags[] = $property;
            }
        }

        // 3. Generate properties from relation methods

        if (isset($instance) && true === config('model-doc.relations.enabled')) {
            foreach ($this->getModelRelationMethods($reflectionClass) as $reflectionMethod) {
                try {
                    /** @var \Illuminate\Database\Eloquent\Relations\Relation $relation */
                    $relation = $instance->{$reflectionMethod->getName()}();

                    foreach ($this->getPropertiesForRelation($reflectionMethod, $relation) as $property) {
                        $tags[] = $property;
                    }
                } catch (\Throwable $exception) {
                    continue; // thanks spatie :-)
                }
            }
        }

        // 4. Generate method from query scopes (https://laravel.com/docs/8.x/eloquent#query-scopes)

        if (isset($instance) && true === config('model-doc.scopes.enabled')) {
            foreach ($this->getQueryScopeMethods($reflectionClass) as $property) {
                $tags[] = $property;
            }
        }

        if (true === config('model-doc.fail_when_empty') && empty($tags)) {
            throw new ModelDocumentationFailedException('The tag is empty');
        }

        $uniques = [];

        foreach ($tags as $index => $tag) {
            if ( ! isset($uniques[$class = get_class($tag)])) {
                $uniques[$class] = [];
            }

            $identifier = $tag instanceof MethodTag ? $tag->getDescription() : $tag->getVariable();

            if ($found = ($uniques[$class][$identifier] ?? null)) {
                unset($tags[$found]);
            }

            $uniques[$class][$identifier] = $index;
        }

        $doc = new Docblock();

        foreach ($tags as $tag) {
            $doc->appendTag($tag);
        }

        return $doc;
    }

    /**
     * @param \romanzipp\ModelDoc\Services\Objects\Model $model
     *
     * @throws \romanzipp\ModelDoc\Exceptions\ModelDocumentationFailedException
     */
    public function generate(Model $model): void
    {
        $doc = $this->generateDocBlock($model);

        if ($doc->isEmpty()) {
            return;
        }

        $this->writeDoc($model, $doc);
    }

    /**
     * @param \ReflectionClass<\Illuminate\Database\Eloquent\Model> $reflectionClass
     *
     * @return \Generator<\gossi\docblock\tags\PropertyTag>
     */
    public function getModelAccessors(\ReflectionClass $reflectionClass): \Generator
    {
        foreach ($reflectionClass->getMethods() as $method) {
            $matches = [];

            if ( ! preg_match('/^get([A-z0-9_]+)Attribute$/', $method->getName(), $matches)) {
                continue;
            }

            $tag = new PropertyTag();
            $tag->setVariable(Str::snake($matches[1]));

            $returnType = 'mixed';

            if (($reflectionType = $method->getReturnType()) !== null && ($typeReturn = self::getReflectionTypeDocReturn($reflectionType))) {
                $returnType = $typeReturn;
            }

            $tag->setType($returnType);

            yield $tag;
        }
    }

    /**
     * @param \ReflectionClass<\Illuminate\Database\Eloquent\Model> $reflectionClass
     *
     * @return \Generator<\gossi\docblock\tags\MethodTag>
     */
    private function getQueryScopeMethods(\ReflectionClass $reflectionClass): \Generator
    {
        $reflectionMethods = $reflectionClass->getMethods(\ReflectionMethod::IS_PUBLIC);

        $scopeReturns = [
            '\\' . Builder::class,
            '\\' . $reflectionClass->getName(),
        ];

        $ignoreList = config('model-doc.scopes.ignore') ?? [];

        $isIgnored = function (string $scopeName) use ($ignoreList): bool {
            // Matches exact string
            if (in_array($scopeName, $ignoreList, true)) {
                return true;
            }

            foreach ($ignoreList as $ignore) {
                // Is regex
                if (Str::startsWith($ignore, '/') && 1 === preg_match($ignore, $scopeName)) {
                    return true;
                }
            }

            return false;
        };

        foreach ($reflectionMethods as $reflectionMethod) {
            // $scopeName = "scopeWhereId"
            if (Str::startsWith($scopeName = $reflectionMethod->getName(), 'scope')) {
                // $methodName = "whereId"
                $methodName = lcfirst(Str::replaceFirst('scope', '', $scopeName));

                if ($isIgnored($methodName)) {
                    continue;
                }

                $methodName .= '(';

                $methodParameters = [];

                foreach ($reflectionMethod->getParameters() as $index => $reflectionParameter) {
                    if (0 === $index) {
                        continue; // First parameter is query builder instance
                    }

                    $parameter = '';

                    if (null !== ($reflectionType = $reflectionParameter->getType())) {
                        $parameter .= self::getReflectionTypeDocReturn($reflectionType);
                        $parameter .= ' ';
                    }
                    $parameter .= '$' . $reflectionParameter->getName();

                    if ($reflectionParameter->isDefaultValueAvailable()) {
                        $parameter .= ' = ' . self::getDefaultValue($reflectionParameter->getDefaultValue());
                    }

                    $methodParameters[] = $parameter;
                }

                $methodName .= implode(', ', $methodParameters);
                $methodName .= ')';

                $method = new MethodTag();
                $method->setType('static ' . implode('|', $scopeReturns));
                $method->setDescription($methodName);

                yield $method;
            }
        }
    }

    /**
     * @param \ReflectionClass<\Illuminate\Database\Eloquent\Model> $reflectionClass
     *
     * @return \ReflectionMethod[]
     */
    private function getModelRelationMethods(\ReflectionClass $reflectionClass): array
    {
        $reflectionMethods = $reflectionClass->getMethods(\ReflectionMethod::IS_PUBLIC);

        $methods = [];

        foreach ($reflectionMethods as $reflectionMethod) {
            $reflectionReturnType = $reflectionMethod->getReturnType();

            if ( ! ($reflectionReturnType instanceof \ReflectionNamedType)) {
                continue;
            }

            if ($reflectionReturnType->isBuiltin()) {
                continue;
            }

            try {
                $returnReflection = new \ReflectionClass($reflectionReturnType->getName());
            } catch (\ReflectionException $exception) {
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
    private function getPropertiesForRelation(\ReflectionMethod $reflectionMethod, Relations\Relation $relation): array
    {
        $related = $relation->getRelated();
        $relatedClass = get_class($related);

        $isMany = false;

        foreach ([
            Relations\HasMany::class,
            Relations\HasManyThrough::class,
            Relations\MorphMany::class,
            Relations\BelongsToMany::class,
        ] as $relationClass) {
            $isMany = $isMany || $relation instanceof $relationClass;
        }

        if ($relation instanceof Relations\MorphTo) {
            $relatedClass = config('model-doc.relations.base_model') ?? IlluminateModel::class;
        }

        $propertyReturns = $isMany
            ? [
                '\\' . Collection::class,
                '\\' . $relatedClass . '[]',
            ]
            : [
                '\\' . $relatedClass,
                'null',
            ];

        $relationProperty = new PropertyTag();
        $relationProperty->setVariable("\${$reflectionMethod->getName()}");
        $relationProperty->setType(
            implode('|', $propertyReturns)
        );

        if (false === config('model-doc.relations.counts.enabled')) {
            return [$relationProperty];
        }

        $countVariable = $reflectionMethod->getName();
        $countVariable = Str::snake($countVariable);

        if ( ! Str::endsWith($countVariable, ['_up', '_down'])) {
            $countVariable = Str::plural($countVariable);
        }

        $countProperty = new PropertyTag();
        $countProperty->setVariable("\${$countVariable}_count");
        $countProperty->setType('int|null');

        return [$relationProperty, $countProperty];
    }

    /**
     * @param \romanzipp\ModelDoc\Services\Objects\Model $model
     * @param \gossi\docblock\Docblock $docblock
     *
     * @throws \romanzipp\ModelDoc\Exceptions\ModelDocumentationFailedException
     */
    private function writeDoc(Model $model, Docblock $docblock): void
    {
        $reflectionClass = $model->getReflectionClass();

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
            throw new ModelDocumentationFailedException('Can not find class declaration');
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
     * @param \ReflectionClass<\Illuminate\Database\Eloquent\Model> $reflectionClass
     * @param \Illuminate\Database\Eloquent\Model $model
     *
     * @throws \romanzipp\ModelDoc\Exceptions\ModelDocumentationFailedException
     *
     * @return \Generator<\gossi\docblock\tags\PropertyTag>
     */
    private function getModelAttributesProperties(\ReflectionClass $reflectionClass, IlluminateModel $model): \Generator
    {
        /**
         * @var \gossi\docblock\tags\PropertyTag[] $accessors
         */
        $accessors = [];

        if (true === config('model-doc.accessors.enabled')) {
            $accessors = iterator_to_array($this->getModelAccessors($reflectionClass));
        }

        $hasAccessor = function (string $variable) use ($accessors) {
            foreach ($accessors as $accessor) {
                if ($accessor->getVariable() === $variable) {
                    return true;
                }
            }

            return false;
        };

        $connection = $model->getConnection();

        $schemaManager = $connection->getDoctrineSchemaManager();

        try {
            $tableColumns = $schemaManager->listTableColumns($model->getTable());
        } catch (DoctrineException $exception) {
            throw new ModelDocumentationFailedException("Can not list table columns for table {$model->getTable()}", 0, $exception);
        }

        foreach ($tableColumns as $tableColumn) {
            $name = $tableColumn->getName();

            if ($hasAccessor($name)) {
                continue;
            }

            $property = new PropertyTag();
            $property->setVariable("\${$name}");

            $types = $this->getTypesForTableColumn($model, $tableColumn);

            if ($model->hasCast($name)) {
                $castedTypes = [self::getReturnTypeForCast($model->getCasts()[$name])];
                if ( ! empty(array_filter($castedTypes))) {
                    if (in_array('null', $types)) {
                        $castedTypes[] = 'null';
                    }

                    $types = $castedTypes;
                }
            }

            if ( ! empty($types)) {
                $property->setType(
                    implode('|', $types)
                );
            }

            if ($comment = $tableColumn->getComment()) {
                $property->setDescription($comment);
            }

            yield $property;
        }
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param \Doctrine\DBAL\Schema\Column $column
     *
     * @throws \romanzipp\ModelDoc\Exceptions\ModelDocumentationFailedException
     *
     * @return array<string>
     */
    private function getTypesForTableColumn(IlluminateModel $model, Column $column): array
    {
        $types = [];

        if (method_exists($model, 'getStates')) {
            foreach ($model::getStates() as $stateAttribute => $state) {
                if ($column->getName() !== $stateAttribute) {
                    continue;
                }

                try {
                    $class = new \ReflectionClass($state->first());
                } catch (\ReflectionException $exception) {
                    throw new ModelDocumentationFailedException("Failed get type for database column {$column->getName()} on table {$model->getTable()}", 0, $exception);
                }

                $types[] = '\\' . $class->getParentClass()->getName();
            }
        }

        foreach ($model->getDates() as $date) {
            if ($column->getName() !== $date) {
                continue;
            }

            $types[] = '\\' . get_class(now());
        }

        if (empty($types)) {
            switch ($typeClass = get_class($column->getType())) {
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
                default:
                    $types[] = config('model-doc.attributes.fallback_type') ? '\\' . $typeClass : 'mixed';
            }
        }

        if (false === $column->getNotnull()) {
            $types[] = 'null';
        }

        return $types;
    }

    private static function getReflectionTypeDocReturn(\ReflectionType $reflectionType): ?string
    {
        $parameter = '';

        if ( ! $reflectionType instanceof \ReflectionNamedType) {
            return null;
        }

        if ($reflectionType->allowsNull()) {
            $parameter .= '?';
        }

        if ($reflectionType->isBuiltin()) {
            $parameter .= $reflectionType->getName();
        } else {
            $parameter .= '\\' . $reflectionType->getName();
        }

        return $parameter;
    }

    /**
     * @internal
     *
     * @param string $castType
     *
     * @return string
     */
    public static function getReturnTypeForCast(string $castType): ?string
    {
        switch ($castType) {
            case 'int':
            case 'integer':
                return 'int';
            case 'real':
            case 'float':
            case 'double':
            case 'decimal':
                return 'float';
            case 'string':
                return 'string';
            case 'bool':
            case 'boolean':
                return 'bool';
            case 'object':
                return '\stdClass';
            case 'array':
            case 'json':
                return 'array';
            case 'collection':
                return '\\' . \Illuminate\Support\Collection::class;
            case 'date':
            case 'datetime':
            case 'custom_datetime':
            case 'immutable_date':
            case 'immutable_custom_datetime':
            case 'immutable_datetime':
            case 'timestamp':
                return '\\' . get_class(now());
        }

        return null;
    }

    /**
     * Get the string representation of any value for the default method parameter.
     *
     * @param mixed $value
     *
     * @return string
     */
    private static function getDefaultValue($value): string
    {
        if (null === $value) {
            return 'null';
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if (is_int($value)) {
            return strval($value);
        }

        if (is_float($value)) {
            return strval($value);
        }

        if (is_string($value)) {
            return sprintf('\'%s\'', $value);
        }

        if (is_array($value)) {
            return '[]';
        }

        return strval($value);
    }
}
