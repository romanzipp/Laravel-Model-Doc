<?php

namespace romanzipp\ModelDoc\Services;

use Illuminate\Console\OutputStyle;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model as IlluminateModel;
use Illuminate\Database\Eloquent\Relations;
use Illuminate\Support\Str;
use phpowermove\docblock\tags\MethodTag;
use phpowermove\docblock\tags\PropertyReadTag;
use phpowermove\docblock\tags\PropertyTag;
use romanzipp\ModelDoc\Exceptions\InvalidModelException;
use romanzipp\ModelDoc\Exceptions\ModelDocumentationFailedException;
use romanzipp\ModelDoc\Services\Docblock\Docblock;
use romanzipp\ModelDoc\Services\Objects\AbstractDocumentableClass;
use romanzipp\ModelDoc\Services\Objects\Model;
use romanzipp\ModelDoc\Services\Tags\MixinTag;
use Symfony\Component\Finder\Finder;

class DocumentationGenerator
{
    /**
     * @var callable|null
     */
    public static $pathCallback;

    public function __construct(
        private readonly ?OutputStyle $output = null,
    ) {
    }

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
     * @param Model $model
     *
     * @throws ModelDocumentationFailedException
     */
    public function generate(Model $model): void
    {
        $modelDoc = $this->generateModelDocBlock($model);

        if ( ! $modelDoc->isEmpty()) {
            $this->writeDoc($model, $modelDoc);
        }
    }

    /**
     * @param Model $model
     *
     * @throws ModelDocumentationFailedException
     *
     * @return Docblock
     */
    public function generateModelDocBlock(Model $model): Docblock
    {
        $tags = [];

        $reflectionClass = $model->getReflectionClass();

        if ($mixinClasses = config('model-doc.custom_tags.mixins')) {
            foreach ($mixinClasses as $mixinClass) {
                $mixinTag = new MixinTag($mixinClass);

                if (config('model-doc.generics')) {
                    $mixinTag->setModelForGenerics($model);
                }

                $tags[] = $mixinTag;
            }
        }

        // 1. Generate properties from database columns

        if (true === config('model-doc.attributes.enabled') && ! $reflectionClass->isAbstract()) {
            try {
                /** @var IlluminateModel $instance */
                $instance = $reflectionClass->newInstance();
            } catch (\ReflectionException $exception) {
                throw new ModelDocumentationFailedException('Can not create model instance', 0, $exception);
            }

            foreach ($this->getModelAttributesProperties($reflectionClass, $instance) as $property) {
                $tags[] = $property;
            }
        }

        // 2. Generate properties from model accessors

        if (true === config('model-doc.accessors.enabled') && ! $reflectionClass->isAbstract()) {
            foreach ($this->getModelAccessors($reflectionClass) as $property) {
                $tags[] = $property;
            }

            // Generate properties from model accessors when using Attribute::make()
            try {
                /** @var IlluminateModel $instance */
                $instance = $reflectionClass->newInstance();
            } catch (\ReflectionException $exception) {
                throw new ModelDocumentationFailedException('Can not create model instance', 0, $exception);
            }

            foreach ($this->getModelAttributesCasts($reflectionClass, $instance) as $property) {
                $tags[] = $property;
            }
        }

        // 3. Generate properties from relation methods

        if (isset($instance) && true === config('model-doc.relations.enabled')) {
            foreach ($this->getModelRelationMethods($reflectionClass) as $reflectionMethod) {
                try {
                    /** @var Relations\Relation $relation */
                    $relation = $instance->{$reflectionMethod->getName()}();

                    foreach ($this->getPropertiesForRelation($model, $reflectionMethod, $relation) as $property) {
                        $tags[] = $property;
                    }
                } catch (\ArgumentCountError $exception) {
                    $this->output?->warning(sprintf('Could not analyze relation `%s` because it has non-default arguments', $reflectionMethod->getName()));
                } catch (\Throwable $exception) {
                    $this->output?->warning(sprintf('Caught %s error: %s', get_class($exception), $exception->getMessage()));

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

        // 5. Generate "factory" method

        if (true === config('model-doc.factories.enabled')) {
            foreach ($this->getModelFactoryMethods($model) as $property) {
                $tags[] = $property;
            }
        }

        // Generate final Docblock

        if (true === config('model-doc.fail_when_empty') && empty($tags)) {
            throw new ModelDocumentationFailedException('The tag is empty');
        }

        $uniqueTags = [];

        foreach ($tags as $index => $tag) {
            if ( ! isset($uniqueTags[$class = get_class($tag)])) {
                $uniqueTags[$class] = [];
            }

            $identifier = $tag instanceof MethodTag ? $tag->getDescription() : $tag->getVariable();

            if ($found = ($uniqueTags[$class][$identifier] ?? null)) {
                unset($tags[$found]);
            }

            $uniqueTags[$class][$identifier] = $index;
        }

        $doc = new Docblock();

        foreach ($tags as $tag) {
            $doc->appendTag($tag);
        }

        return $doc;
    }

    /**
     * @param Model $model
     *
     * @return array|\phpowermove\docblock\tags\MethodTag[]
     */
    public function getModelFactoryMethods(Model $model): array
    {
        $factory = $model->getFactory();
        if (null === $factory) {
            return [];
        }

        $tag = new MethodTag();
        $tag->setType('static \\' . $factory->getQualifiedClassName() . '<self>');
        $tag->setDescription('factory($count = null, $state = [])');

        return [
            $tag,
        ];
    }

    /**
     * @param \ReflectionClass<\Illuminate\Database\Eloquent\Model> $reflectionClass
     *
     * @return \Generator<\phpowermove\docblock\tags\PropertyTag>
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
     * @param IlluminateModel $model
     *
     * @return \Generator<\phpowermove\docblock\tags\PropertyTag>
     */
    public function getModelAttributesCasts(\ReflectionClass $reflectionClass, IlluminateModel $model): \Generator
    {
        foreach ($reflectionClass->getMethods() as $method) {
            if (\Illuminate\Database\Eloquent\Casts\Attribute::class != $method->getReturnType()) {
                continue;
            }

            if ( ! $method->isPublic()) {
                $method->setAccessible(true);
            }

            /** @var ?callable $get */
            $get = $method->invoke($model)?->get;

            if (null === $get) {
                continue;
            }

            $camelCaseTag = new PropertyTag();
            $camelCaseTag->setVariable($methodName = $method->getName());

            $returnType = 'mixed';

            $callableFunction = new \ReflectionFunction($get);

            if (($reflectionType = $callableFunction->getReturnType()) !== null && ($typeReturn = self::getReflectionTypeDocReturn($reflectionType))) {
                $returnType = $typeReturn;
            }

            $camelCaseTag->setType($returnType);

            $snakeCaseTag = clone $camelCaseTag;
            $snakeCaseTag->setVariable(Str::snake($methodName));

            yield $camelCaseTag;
            yield $snakeCaseTag;
        }
    }

    /**
     * @param \ReflectionClass<\Illuminate\Database\Eloquent\Model> $reflectionClass
     *
     * @return \Generator<\phpowermove\docblock\tags\MethodTag>
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
     * @param Relations\Relation $relation
     *
     * @return \phpowermove\docblock\tags\PropertyReadTag[]
     */
    private function getPropertiesForRelation(Model $model, \ReflectionMethod $reflectionMethod, Relations\Relation $relation): array
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

        if ($relation instanceof Relations\HasOneThrough) {
            $isMany = false;
        }

        if ($relation instanceof Relations\MorphTo) {
            $relatedClass = config('model-doc.relations.base_model') ?? IlluminateModel::class;
        }

        $isNullable = true;

        // Get information about the foreign key and check if nullable
        if ($relation instanceof Relations\BelongsTo) {
            $isNullable = null;
            $columnName = $relation->getForeignKeyName();

            $tableColumns = self::getTableColumnsForModel($model->getInstance());

            foreach ($tableColumns as $tableColumn) {
                if ($tableColumn['name'] === $columnName) {
                    $isNullable = $tableColumn['nullable'];

                    break;
                }
            }

            if (null === $isNullable) {
                $this->output?->warning(sprintf('Could not determine if relation key column `%s` is nullable', $columnName));

                $isNullable = true;
            }
        }

        if ($isMany) {
            $propertyReturns = [
                self::makeAbsoluteClassName(Collection::class),
                self::makeAbsoluteClassName($relatedClass) . '[]',
            ];
        } else {
            $propertyReturns = [
                self::makeAbsoluteClassName($relatedClass),
            ];

            if ($isNullable) {
                $propertyReturns[] = 'null';
            }
        }

        $relationProperty = new PropertyReadTag();
        $relationProperty->setVariable("\${$reflectionMethod->getName()}");
        $relationProperty->setType(
            implode('|', $propertyReturns)
        );

        // Return if no `*_count` readonly property should be added
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
     * @param AbstractDocumentableClass $model
     * @param Docblock $docblock
     *
     * @throws ModelDocumentationFailedException
     */
    private function writeDoc(AbstractDocumentableClass $model, Docblock $docblock): void
    {
        $reflectionClass = $model->getReflectionClass();

        $content = file_get_contents($reflectionClass->getFileName());

        $startLineIndex = null;

        $lines = explode(PHP_EOL, $content);

        foreach ($lines as $index => $line) {
            if ( ! preg_match('/^(abstract|final|readonly)? ?class ([A-z]+)/', $line)) {
                continue;
            }

            $startLineIndex = $index;
            break;
        }

        if (null === $startLineIndex) {
            throw new ModelDocumentationFailedException('Can not find class declaration');
        }

        // Check if class declaration preceeds Attribute(s)

        $isAttributeStarting = false;
        $attributeLines = [];
        $startLineIndexByAttribute = null;

        for ($i = 0; $i <= $startLineIndex; ++$i) {
            $line = $lines[$i];

            $trimmedLine = trim($line);

            $isAttributeStarting = $isAttributeStarting || str_starts_with($trimmedLine, '#[');
            $isAttributeEnding = str_ends_with($trimmedLine, ']');

            if ($isAttributeStarting && null === $startLineIndexByAttribute) {
                $startLineIndexByAttribute = $i;
            }

            if ($isAttributeStarting) {
                $attributeLines[] = $line;
            }

            // dump('comment: '.($isComment?'y':'n'). ', blank: '.($isBlank?'y':'n').'   '. $line);

            // Stop if attribute is single line or multiline declaration ends
            if (($isAttributeStarting && $isAttributeEnding) || ($isAttributeEnding && count($attributeLines) > 1)) {
                $startLineIndex = $i;
                break;
            }
        }

        $startLineIndex = $startLineIndexByAttribute ?? $startLineIndex;

        // Remove existing phpdoc

        foreach ($lines as $index => $line) {
            if ($index >= $startLineIndex) {
                break;
            }

            if ( ! Str::startsWith($line, ['/**', ' *', ' */'])) {
                continue;
            }

            $lines[$index] = null;
        }

        $docLines = explode(PHP_EOL, $docblock->toString());

        foreach (array_reverse($docLines) as $docLine) {
            array_splice($lines, $startLineIndex, 0, $docLine);
        }

        $lines = array_filter($lines, static fn ($line) => null !== $line);

        file_put_contents($reflectionClass->getFileName(), implode(PHP_EOL, $lines));
    }

    /**
     * @param \ReflectionClass<\Illuminate\Database\Eloquent\Model> $reflectionClass
     * @param IlluminateModel $model
     *
     * @throws ModelDocumentationFailedException
     *
     * @return \Generator<\phpowermove\docblock\tags\PropertyTag>
     */
    private function getModelAttributesProperties(\ReflectionClass $reflectionClass, IlluminateModel $model): \Generator
    {
        /** @var \phpowermove\docblock\tags\PropertyTag[] $accessors */
        $accessors = [];

        if (true === config('model-doc.accessors.enabled')) {
            $accessors = iterator_to_array(
                $this->getModelAccessors($reflectionClass)
            );
        }

        $hasAccessor = function (string $variable) use ($accessors) {
            foreach ($accessors as $accessor) {
                if ($accessor->getVariable() === $variable) {
                    return true;
                }
            }

            return false;
        };

        $tableColumns = $this->getTableColumnsForModel($model);

        foreach ($tableColumns as $tableColumn) {
            $name = $tableColumn['name'];

            // Skip
            if ($hasAccessor($name)) {
                continue;
            }

            $property = new PropertyTag();
            $property->setVariable("\${$name}");

            $types = $this->getTypesForTableColumn($model, $tableColumn);

            if ($model->hasCast($name)) {
                $castedTypes = $this->getReturnTypesForCast($model->getCasts()[$name], $name);

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

            if (filled($tableColumn['comment'])) {
                $property->setDescription($tableColumn['comment']);
            }

            yield $property;
        }
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $model
     *
     * @return array{name: string, type_name: string, nullable: bool, comment: string|null}[]
     */
    private static function getTableColumnsForModel(IlluminateModel $model): array
    {
        $connection = $model->getConnection();
        $schemaBuilder = $connection->getSchemaBuilder();

        if (method_exists($schemaBuilder, 'getColumns')) {
            $tableColumns = $schemaBuilder->getColumns($model->getTable());
        } else {
            $tableColumnNames = $schemaBuilder->getColumnListing($model->getTable());

            $tableColumns = array_map(function ($colName) use ($schemaBuilder, $model) {
                /** @phpstan-ignore-next-line */
                $docCol = $schemaBuilder->getConnection()->getDoctrineColumn($model->getTable(), $colName);

                return [
                    'name' => $colName,
                    'type_name' => $schemaBuilder->getColumnType($model->getTable(), $colName),
                    'nullable' => ! $docCol->getNotnull(),
                    'comment' => null,
                ];
            }, $tableColumnNames);
        }

        return $tableColumns;
    }

    /**
     * @param IlluminateModel $model
     * @param array<string, mixed> $column
     *
     * @throws ModelDocumentationFailedException
     *
     * @return array<string>
     */
    private function getTypesForTableColumn(IlluminateModel $model, array $column): array
    {
        $types = [];

        if (method_exists($model, 'getStates')) {
            /** @phpstan-ignore-next-line */
            foreach ($model::getStates() as $stateAttribute => $state) {
                if ($column['name'] !== $stateAttribute) {
                    continue;
                }

                try {
                    $class = new \ReflectionClass($state->first());
                } catch (\ReflectionException $exception) {
                    $this->output?->warning(
                        sprintf('Failed to get type for database column `%s` on table `%s`: %s', $column['name'], $model->getTable(), $exception->getMessage()),
                    );

                    continue;
                }

                $types[] = self::makeAbsoluteClassName($class->getParentClass()->getName());
            }
        }

        foreach ($model->getDates() as $date) {
            if ($column['name'] !== $date) {
                continue;
            }

            $types[] = self::makeAbsoluteClassName(get_class(now()));
        }

        if (empty($types)) {
            $detectedType = match ($column['type_name'] ?? null) {
                'int',
                'int2',
                'int4',
                'int8',
                'integer',
                'mediumint',
                'bigint',
                'smallint',
                'tinyint',
                'numeric',
                'year' => 'int',
                // -----------------------------
                'float',
                'double',
                'decimal' => 'float',
                // -----------------------------
                'string',
                'varchar',
                'bpchar',
                'char',
                'text',
                'tinytext',
                'mediumtext',
                'longtext',
                'json',
                'jsonb',
                'datetime',
                'date',
                'time',
                'timestamp',
                'blob',
                'uuid',
                'enum' => 'string',
                // -----------------------------
                'bool',
                'boolean' => 'bool',
                // -----------------------------
                default => null,
            };

            if (null === $detectedType) {
                $this->output?->warning(sprintf('Could not derive column type from type-name `%s` for column `%s`', $column['type_name'], $column['name']));

                $detectedType = config('model-doc.attributes.fallback_type') ?: 'mixed';
            }

            $types[] = $detectedType;
        }

        if ($column['nullable'] ?? false) {
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
     * @param string $castType
     * @param string $tableColumn
     *
     * @return array<string>
     *
     * @internal
     */
    public function getReturnTypesForCast(string $castType, string $tableColumn): array
    {
        if (str_starts_with($castType, 'datetime:')) {
            return ['\\' . now()::class];
        }

        switch ($castType) {
            case 'int':
            case 'integer':
                return ['int'];
            case 'real':
            case 'float':
            case 'double':
            case 'decimal':
                return ['float'];
            case 'string':
            case 'hashed':
            case 'encrypted':
                return ['string'];
            case 'bool':
            case 'boolean':
                return ['bool'];
            case 'object':
                return ['\stdClass'];
            case 'array':
            case 'json':
                return ['array'];
            case 'collection':
                return ['\\' . \Illuminate\Support\Collection::class];
            case 'date':
            case 'datetime':
            case 'custom_datetime':
            case 'immutable_date':
            case 'immutable_custom_datetime':
            case 'immutable_datetime':
            case 'timestamp':
                return ['\\' . get_class(now())];
        }

        if ( ! str_contains($castType, '\\')) {
            // The cast is an unknown type
            $this->output?->warning(sprintf('Unknown cast type `%s` for column `%s`', $castType, $tableColumn));

            return [];
        }

        $defaultType = self::makeAbsoluteClassName($castType);

        // Check if cast is a `Illuminate\Contracts\Database\Eloquent\CastsAttributes` caster
        try {
            $castReflection = new \ReflectionClass($castType);

            if ( ! $castReflection->isInstantiable()) {
                return [$defaultType];
            }

            $castInstance = $castReflection->newInstance();

            if ( ! ($castInstance instanceof CastsAttributes)) {
                return [self::makeAbsoluteClassName($castType)];
            }

            $getMethod = $castReflection->getMethod('get');

            $returnTypes = $getMethod->getReturnType();

            if ($returnTypes instanceof \ReflectionNamedType) {
                return [
                    $returnTypes->isBuiltin()
                        ? $returnTypes->getName()
                        : self::makeAbsoluteClassName($returnTypes->getName()),
                ];
            }

            if ($returnTypes instanceof \ReflectionUnionType) {
                return array_map(
                    fn (\ReflectionNamedType $type) => $type->isBuiltin()
                        ? $type->getName()
                        : self::makeAbsoluteClassName($type->getName()),
                    $returnTypes->getTypes()
                );
            }

            return [$defaultType];
        } catch (\ReflectionException $exception) {
            $this->output?->warning(sprintf('Could not instanziate cast class `%s` for column `%s`', $castType, $tableColumn));
        }

        // The cast type is a class name (most probably). Maybe check with class_exists()?
        return [$defaultType];
    }

    /**
     * Get the string representation of any value for the default method parameter.
     *
     * @param mixed $value
     *
     * @return string
     */
    private static function getDefaultValue(mixed $value): string
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

    private static function makeAbsoluteClassName(string $class): string
    {
        $nullable = false;

        if (str_starts_with($class, '?')) {
            $class = substr($class, 1, null);
            $nullable = true;
        }

        if ( ! str_starts_with($class, '\\')) {
            $class = "\\{$class}";
        }

        if ($nullable) {
            $class = "?{$class}";
        }

        return $class;
    }
}
