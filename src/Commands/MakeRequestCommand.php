<?php

namespace hosseinkalateh\SimpleDto\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class MakeRequestCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:request-dto
                            {name : The name of the form request}
                            {--dto= : The name of the dto}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new form request class with dto';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->alreadyExists();

        file_put_contents($this->getFileFullPath(), $this->addRulesToTemplate($this->getTemplate()));

        $this->line("<options=bold,reverse;fg=green>Form request were published</>");
    }

    protected function addRulesToTemplate(string $template): string
    {
        $properties = $this->getDtoProperties();

        $rules = "return [ \n";

        foreach ($properties as $property) {
            $rules .= "\t\t\t'" . $property['name'] . "' => [";
            if ($property['nullable'])
                $rules .= "'nullable'";
            else
                $rules .= "'required'";
            $rules .= "],\n";
        }

        $rules .= "\t\t];";

        return Str::replace(
            ['{{ rules }}'],
            [$rules],
            $template
        );
    }

    protected function getDtoProperties(): array
    {
        $allDtos = $this->getAllDtos();

        $dtoData = $allDtos[array_search($this->getDtoOption(), array_column($allDtos, 'name'))];

        $className = '\\' . Str::substr(Str::substr($dtoData['namespace'], 4), 0 , -1);

        $dto = new $className();
        $dtoContent = file_get_contents($dtoData['full_path']);

        $attributes = [];

        foreach (get_class_vars(get_class($dto)) as $key => $value) {
            $searchForKey = '$' . $key;

            $pattern = preg_quote($searchForKey, '/');

            $pattern = "/^.*$pattern.*\$/m";

            preg_match_all($pattern, $dtoContent, $matches);

            $type = trim(Str::replace(['public', "$key", '$', ';', '?'], '', $matches[0][0]));

            $attributes[] = [
                'name' => Str::snake($key),
                'type' => $type,
                'nullable' => Str::contains($matches[0][0], '?')
            ];
        }

        return $attributes;
    }

    protected function getNameInput(): string
    {
        $name = trim($this->input->getArgument('name'));

        if (Str::endsWith($name, '.php')) {
            return Str::substr($name, 0, -4);
        }

        return $name;
    }

    protected function getDtoOption(): string
    {
        $name = trim($this->option('dto'));

        if ($name == null) {
            $this->error("Dto name doesn't entered!");
            exit(0);
        }

        if (Str::endsWith($name, '.php')) {
            $name = Str::substr($name, 0, -4);
        }

        $allDtos = $this->getAllDtos();

        if (array_search($name, array_column($allDtos, 'name')) === false) {
            $this->error('Provided dto do not exists');
            exit(0);
        }

        return $name;
    }

    protected function getDtoNamespace(string $dtoName): string
    {
        $allDtos = $this->getAllDtos();

        return $allDtos[array_search($dtoName, array_column($allDtos, 'name'))]['namespace'];
    }

    protected function getTemplate(): string
    {
        $requestName = $this->getNameInput();

        $dtoName = $this->getDtoOption();

        $requestNamespace = $this->getRequestNamespace($requestName);

        $dtoNamespace = $this->getDtoNamespace($dtoName);

        return Str::replace(
            ['{{ namespace }}', '{{ dtoNamespace }}', '{{ class }}', '{{ dto }}'],
            [$requestNamespace, $dtoNamespace, $this->resolveRequestName($requestName), $dtoName],
            $this->getStub()
        );
    }

    protected function resolveRequestName(string $requestName): string
    {
        return trim(Str::replace('/', '', trim(Str::substr($requestName, strrpos($requestName, '/'), Str::length($requestName)))));
    }

    protected function getAllDtos(): array
    {
        $dtosPath = base_path('app/Dto/');

        $dtos = [];
        foreach ($this->getDirContents($dtosPath) as $dto) {
            $dtoName = Str::substr($dto, strrpos($dto, "\\") + 1, Str::length($dto));

            $dtoNamespace = Str::ucfirst(Str::substr($dto, strrpos($dto, "app"), strrpos($dto, '.')));

            $dtos[] = [
                'name'      => Str::substr($dtoName, 0, -4),
                'full_path' => $dto,
                'namespace' => 'use ' . Str::substr($dtoNamespace, 0, -4) . ';'
            ];
        }

        return $dtos;
    }

    protected function getDirContents(string $dir, &$results = array()): array
    {
        $files = scandir($dir);

        foreach ($files as $key => $value) {
            $path = realpath($dir . DIRECTORY_SEPARATOR . $value);

            if (!is_dir($path)) {
                $results[] = $path;
            } else if ($value != "." && $value != "..") {
                $this->getDirContents($path, $results);
            }
        }

        return $results;
    }

    protected function getFileFullPath(): string
    {
        $fileName = $this->getNameInput();

        return app_path("Http/Requests/$fileName.php");
    }

    protected function getStub(): string
    {
        return file_get_contents(__DIR__ . '/stubs/request-dto.stub');
    }

    protected function getRequestNamespace(string $requestName): string
    {
        $namespace = 'App\Http\Requests\\' . trim(Str::substr($requestName, 0, strrpos($requestName, '/')));

        if (Str::endsWith($namespace, '\\')) {
            $namespace = Str::substr($namespace, 0, -1);
        }

        $namespace = Str::replace('/', "\\", $namespace);

        if (! is_dir($namespace))
            mkdir(base_path($namespace), 0777,true);

        return $namespace;
    }

    public function alreadyExists(): void
    {
        if (file_exists($this->getFileFullPath())) {
            $this->error('Request already exists');

            exit(0);
        }
    }
}
