<?php

namespace hosseinkalateh\SimpleDto\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MakeDtoCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:dto
                            {name : The name of the dto} ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new dto class';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->alreadyExists();

        $template = $this->getTemplate();

        file_put_contents($this->getFileFullPath(), $template);

        $this->line("<options=bold,reverse;fg=green>Dto were published</>");
    }

    protected function getNameInput()
    {
        $name = trim($this->input->getArgument('name'));

        if (Str::endsWith($name, '.php')) {
            return Str::substr($name, 0, -4);
        }

        return $name;
    }

    protected function getTemplate()
    {
        $name = $this->getNameInput();

        $namespace = $this->getNamespace($name);

        return str_replace(
            ['{{ namespace }}', '{{ class }}'],
            [$namespace, $this->resolveName($name)],
            $this->getStub()
        );
    }

    protected function resolveName(string $name)
    {
        return trim(str_replace('/', '', trim(substr($name, strrpos($name, '/'), strlen($name)))));
    }

    protected function getFileFullPath()
    {
        $fileName = $this->getNameInput();

        return app_path("Dto/$fileName.php");
    }

    protected function getStub()
    {
        return file_get_contents(__DIR__ . '/stubs/dto.stub');
    }

    protected function getNamespace(string $name)
    {
        $namespace = 'App\Dto\\' . trim(substr($name, 0, strrpos($name, '/')));

        if (Str::endsWith($namespace, '\\')) {
            $namespace = Str::substr($namespace, 0, -1);
        }

        $namespace = str_replace('/', "\\", $namespace);

        if (! is_dir($namespace))
            mkdir(base_path($namespace), 0777,true);

        return $namespace;
    }

    public function alreadyExists()
    {
        if (file_exists($this->getFileFullPath())) {
            $this->error('Dto already exists');
            exit(0);
        }
    }
}
