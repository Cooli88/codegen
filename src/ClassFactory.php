<?php

namespace Cooli88\codegen;


use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpFile;
use Nette\PhpGenerator\PhpNamespace;

class ClassFactory
{
    /** @var TypeFactory */
    private $typeFactory;

    /**
     * ClassFactory constructor.
     * @param TypeFactory $typeFactory
     */
    public function __construct(TypeFactory $typeFactory)
    {
        $this->typeFactory = $typeFactory;
    }

    public function createFile(array $schema, string $classname, string $namespace)
    {
        $file = new PhpFile;
        $file->setStrictTypes();
        $namespace = $file->addNamespace($namespace);

        $class = $this->createClass($namespace, $classname, $namespace);

        $this->addProperties($class, $schema);

        return $file;
    }

    protected function createClass(PhpNamespace $phpNamespace, string $classname, string $namespace): ClassType
    {
        $class = $phpNamespace->addClass($classname);

        $class
            ->addComment("Class {$classname}")
            ->addComment("@SWG\Definition(definition=\"{$classname}\")")
            ->addComment("@package {$namespace}");

        return $class;
    }

    protected function addProperties(ClassType $class, array $schema): void
    {
        if (isset($schema['properties'])) {
            foreach ($schema['properties'] as $propertyName => $property) {
                $this->addProperty($class, $propertyName, $property, $schema['definitions'] ?? null);
            }
        }
        if (($schema['type'] === 'array') && isset($schema['items'])) {
            $this->addItems($class, $schema['items']);
        }
    }

    protected function addProperty(ClassType $class, string $propertyName, array $propertyData, $definitions = null): void
    {
        $property = $class->addProperty($propertyName);
        if (isset($propertyData['type'])) {
            $property
                ->addComment("@SWG\Property()")
                ->addComment("@var {$propertyData['type']}");
        } elseif (isset($propertyData['$ref'])) {
            $refRaw = explode('/', $propertyData['$ref']);
            $ref = end($refRaw);
            if (isset($definitions[$ref]) && $definitions[$ref]['javaType']) {
                $type = $definitions[$ref]['javaType'];
                $property
                    ->addComment("@SWG\Property(")
                    ->addComment("    @SWG\Schema(ref=\"#/definitions/{$type}\")")
                    ->addComment(")")
                    ->addComment("@var {$type}");

            }
        }
    }

    protected function addItems(ClassType $class, array $propertyData): void
    {
        $property = $class->addProperty('items');
        if (isset($propertyData['type']) && $propertyData['javaType']) {
            $type = $propertyData['javaType'];
            $property
                ->addComment("@SWG\Property(")
                ->addComment("*   @SWG\Items(")
                ->addComment("        @SWG\Schema(ref=\"#/definitions/{$type}\")")
                ->addComment("     )")
                ->addComment(")")
                ->addComment("@var {$type}[]");

        } elseif (isset($propertyData['type'])) {
            $property
                ->addComment("@SWG\Property(")
                ->addComment("      @SWG\Items(")
                ->addComment("          type=\"array\"")
                ->addComment("          @SWG\Property(type=\"{$propertyData['type']}\")")
                ->addComment("      )")
                ->addComment(")")
                ->addComment("@var {$propertyData['type']}[]");
        }
    }
}