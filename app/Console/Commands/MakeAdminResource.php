<?php

namespace App\Console\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;

class MakeAdminResource extends GeneratorCommand
{
    protected $name = 'make:admin-resource';

    protected $signature = 'make:admin-resource {name : Resource name (e.g. Post)} {--model=}';

    protected $description = 'Scaffold a basic admin resource controller that extends BaseAdminResourceController';

    protected function getStub(): string
    {
        return __DIR__.'/stubs/admin-resource-controller.stub';
    }

    protected function getDefaultNamespace($rootNamespace): string
    {
        return $rootNamespace.'\Http\Controllers\Web\Admin';
    }

    protected function buildClass($name): string
    {
        $stub = parent::buildClass($name);

        $model = $this->option('model') ?: $this->argument('name');
        $modelClass = $this->qualifyModel($model);
        $resourceName = Str::kebab(class_basename($model));

        $replacements = [
            '{{ modelNamespace }}' => $modelClass,
            '{{ modelBase }}' => class_basename($modelClass),
            '{{ resourceName }}' => $resourceName,
            '{{ resourceTitle }}' => Str::headline($resourceName),
        ];

        return str_replace(
            array_keys($replacements),
            array_values($replacements),
            $stub
        );
    }
}
