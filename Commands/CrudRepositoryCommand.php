<?php

namespace Wot\CrudGenerator\Commands;

use Illuminate\Console\GeneratorCommand;

class CrudRepositoryCommand extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crud:repository
                            {name : The name of the controler.}
                            {--crud-name= : The name of the Crud.}
                            {--fields= : Field names for the form & migration.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new repository file.';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Repository';

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return __DIR__ . '/../stubs/repository.stub';
    }

    /**
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace . '\Http\Repository';
    }

    /**
     * Build the model class with the given name.
     *
     * @param  string  $name
     * @return string
     */
    protected function buildClass($name)
    {
        $stub = $this->files->get($this->getStub());

        $crudName = strtolower($this->option('crud-name'));
        $crudNameCap = $this->option('crud-name');
        $crudNamePlural = str_plural($crudName);
        $crudNamePluralCap = str_plural($crudNameCap);
        $crudNameSingular = str_singular($crudName);
        $contractNameSpace = str_singular('App\Http\Contracts');
        $contractName = $crudNameCap.'Contract';
        $fields = $this->option('fields');

        $this->changeContractNameSpace($stub, $contractNameSpace);
        $this->changeContractName($stub, $contractName);

        $whereSnippet = '';
        if ($fields) {
            $x = 0;
            $fieldAry = explode(',', $fields);
            foreach ($fieldAry as $index => $item) {
                $itemArray = explode(':', $item);
                if (trim($itemArray[1]) == 'file') {
                    $fileSnippet .= str_replace('{{fieldName}}', trim($itemArray[0]), $snippet) . "\n";
                }
                $fieldName = trim($itemArray[0]);
                $whereSnippet .= ($index == 0) ? "where('$fieldName', 'LIKE', \"%\$keyword%\")" . "\n                " : "->orWhere('$fieldName', 'LIKE', \"%\$keyword%\")" . "\n                ";
            }
            $whereSnippet .= "->";
        }

        return $this->replaceNamespace($stub, $name)
            ->replaceCrudName($stub, $crudName)
            ->replaceCrudNameCap($stub, $crudNameCap)
            ->replaceCrudNamePlural($stub, $crudNamePlural)
            ->replaceCrudNamePluralCap($stub, $crudNamePluralCap)
            ->replaceCrudNameSingular($stub, $crudNameSingular)
            ->replaceWhereSnippet($stub, $whereSnippet)
            ->replaceClass($stub, $name);
    }

    /**
     * Replace the viewPath for the given stub.
     *
     * @param  string  $stub
     * @return $this
     */
    protected function replaceViewPath(&$stub, $viewPath)
    {
        $stub = str_replace(
            '{{viewPath}}', $viewPath, $stub
        );

        return $this;
    }

    /**
     * Replace the crudName for the given stub.
     *
     * @param  string  $stub
     * @return $this
     */
    protected function replaceCrudName(&$stub, $crudName)
    {
        $stub = str_replace(
            '{{crudName}}', $crudName, $stub
        );

        return $this;
    }

    /**
     * Replace the crudNameCap for the given stub.
     *
     * @param  string  $stub
     * @return $this
     */
    protected function replaceCrudNameCap(&$stub, $crudNameCap)
    {
        $stub = str_replace(
            '{{crudNameCap}}', $crudNameCap, $stub
        );

        return $this;
    }

    /**
     * Replace the crudNamePlural for the given stub.
     *
     * @param  string  $stub
     * @return $this
     */
    protected function replaceCrudNamePlural(&$stub, $crudNamePlural)
    {
        $stub = str_replace(
            '{{crudNamePlural}}', $crudNamePlural, $stub
        );

        return $this;
    }

    /**
     * Replace the crudNamePluralCap for the given stub.
     *
     * @param  string  $stub
     * @return $this
     */
    protected function replaceCrudNamePluralCap(&$stub, $crudNamePluralCap)
    {
        $stub = str_replace(
            '{{crudNamePluralCap}}', $crudNamePluralCap, $stub
        );

        return $this;
    }

    /**
     * Replace the crudNameSingular for the given stub.
     *
     * @param  string  $stub
     * @return $this
     */
    protected function replaceCrudNameSingular(&$stub, $crudNameSingular)
    {
        $stub = str_replace(
            '{{crudNameSingular}}', $crudNameSingular, $stub
        );

        return $this;
    }

    /**
     * Replace the DummyContractNamespace for the given stub.
     *
     * @param  string  $stub
     * @return $this
     */
    protected function changeContractNameSpace(&$stub, $contractNameSpace)
    {
        $stub = str_replace(
            '{{DummyContractNamespace}}', $contractNameSpace, $stub 
        );

        return $this;
    }

    /**
     * Replace the contrcatName for the given stub.
     *
     * @param  string  $stub
     * @return $this
     */
    protected function changeContractName(&$stub, $contractName)
    {
        $stub = str_replace(
            '{{contractName}}', $contractName, $stub
        );

        return $this;
    }

    /**
     * Replace the where snippet for the given stub
     *
     * @param $stub
     * @param $whereSnippet
     *
     * @return $this
     */
    protected function replaceWhereSnippet(&$stub, $whereSnippet)
    {
        $stub = str_replace('{{whereSnippet}}', $whereSnippet, $stub);
        return $this;
    } 

}
