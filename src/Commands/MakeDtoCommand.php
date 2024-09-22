<?php


namespace hosseinkalateh\SimpleDto\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class MakeDtoCommand extends Command
{
    protected $signature = 'make:dto {name}';
    protected $description = 'Create a new DTO class';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {

        // Separate the name into folder and class parts
        list($className, $namespace, $path) = $this->separateTheNameIntoFolderAndClassParts();

        // Check if the directory exists, if not create it
        $this->checkIfTheDirectoryExistsIfNotCreateIt($path);

        // Path of the new DTO file
        $filePath = $this->pathOfTheNewDTOFile($path, $className);

        // Check if the file already exists
        $this->checkIfTheFileAlreadyExists($filePath, $className);

        // Read the stub file content
        $content = $this->readTheStubFileContent();

        // Replace the placeholders with actual values
        $content = $this->replaceThePlaceholdersWithActualValues($namespace, $className, $content);

        // Create the DTO file
        $this->createTheDTOFile($filePath, $content);

        $this->info("DTO class {$className} created successfully at {$filePath}.");
    }

    /**
     * @param string $path
     * @return void
     */
    public function checkIfTheDirectoryExistsIfNotCreateIt(string $path): void
    {
        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }
    }

    /**
     * @return false|string
     */
    public function readTheStubFileContent()
    {
        $stubPath = __DIR__ . '/stubs/dto.stub';
        $content = file_get_contents($stubPath);
        return $content;
    }

    /**
     * @param string $namespace
     * @param string $className
     * @param $content
     * @return array|string|string[]
     */
    public function replaceThePlaceholdersWithActualValues(string $namespace, string $className, $content)
    {
        $content = str_replace(
            ['{{ namespace }}', '{{ class }}'],
            [$namespace, $className],
            $content
        );
        return $content;
    }

    /**
     * @param string $filePath
     * @param $content
     * @return void
     */
    public function createTheDTOFile(string $filePath, $content): void
    {
        file_put_contents($filePath, $content);
    }

    /**
     * @param string $path
     * @param string $className
     * @return string
     */
    public function pathOfTheNewDTOFile(string $path, string $className): string
    {
        $filePath = "{$path}/{$className}.php";
        return $filePath;
    }

    /**
     * @param string $filePath
     * @param string $className
     * @return void
     */
    public function checkIfTheFileAlreadyExists(string $filePath, string $className): void
    {
        if (file_exists($filePath)) {
            $this->error("{$className} already exists at {$filePath}!");
        }
    }

    /**
     * @return array
     */
    public function separateTheNameIntoFolderAndClassParts(): array
    {
        $name           = $this->argument('name');
        $parts          = explode('/', $name);
        $className      = Str::studly(array_pop($parts));
        $namespace      = 'App\\DTO' . (!empty($parts) ? '\\' . implode('\\', $parts) : '');
        $path           = app_path('DTO') . (!empty($parts) ? '/' . implode('/', $parts) : '');
        return array($className, $namespace, $path);
    }
}
