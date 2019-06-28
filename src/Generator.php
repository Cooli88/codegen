<?php

declare(strict_types=1);

namespace cooli88\codegen;


class Generator
{
    protected const EXTENSION_JSON = '.json';

    /** @var string[] [ 'schemaName' => [schema] ] */
    protected $schemas = [];
    /** @var string[] */
    protected $sequenceSchemaToCreate = [];
    /** @var string */
    protected $namespace;
    /** @var ClassFactory */
    protected $classFactory;

    /**
     * Generator constructor.
     * @param ClassFactory $classFactory
     */
    public function __construct(ClassFactory $classFactory)
    {
        $this->classFactory = $classFactory;
    }

    public function generateFromDir(string $pathToDirSchemas, string $outputDir, string $namespace)
    {
        $this->namespace = $namespace;
        $this->schemas = $this->getSchemes($pathToDirSchemas);
        $this->sequenceSchemaToCreate = $this->getSequence($this->schemas);
        $this->generateSaveDto($outputDir);
    }

    protected function generateSaveDto(string $outputDir)
    {
        foreach ($this->sequenceSchemaToCreate as $schemaName) {
            $schema = $this->schemas[$schemaName];
            $file = $this->classFactory->createFile($schema, $schemaName, $this->namespace);
            $this->write($file, $schemaName, $outputDir);
        }
    }

    /**
     * Получить схемы из папки
     *
     * @param string $pathToSchema
     * @return string[]
     */
    protected function getSchemes(string $pathToSchema): array
    {
        $sсhemesFilenamesRaw = scandir($pathToSchema);
        $sсhemesFilenames = array_diff($sсhemesFilenamesRaw, ['..', '.']);

        foreach ($sсhemesFilenames as $filename) {
            $sсhemes[$this->getSchemaName($filename)] = $this->getSchemaArr($filename, $pathToSchema);
        }

        return $sсhemes;
    }

    /**
     * Получения имени из имени файлв
     *
     * @param string $filename
     * @return string
     */
    protected function getSchemaName(string $filename): string
    {
        return str_replace(static::EXTENSION_JSON, "", $filename);
    }

    /**
     * Получения схемы в виде ассоциативного массива
     *
     * @param string $filename
     * @param string $pathToSchema
     * @return array
     */
    protected function getSchemaArr(string $filename, string $pathToSchema): array
    {
        return json_decode(file_get_contents($pathToSchema . DIRECTORY_SEPARATOR . $filename), true);
    }

    /**
     * Получение последовательности как создовать объекты
     *
     * @param string[] $schemas
     * @return string[]
     */
    protected function getSequence(array $schemas): array
    {
        $sequenceSchemaToCreate = [];
        foreach ($schemas as $schemaName => $schema) {
            $sequenceSchemaToCreate[] = $schemaName;
        }
        return $sequenceSchemaToCreate;
    }


    /**
     * @param $file
     * @param string $class
     * @param string $pathToDir
     */
    public function write($file, string $class, string $pathToDir): void
    {
        $rootDirectory = rtrim($pathToDir, '/');
        $directory = rtrim($rootDirectory, '/');
        $path = $directory . '/' . $class . '.php';
        file_put_contents($path, $file);
    }
}
