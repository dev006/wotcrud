<?php

namespace Wot\CrudGenerator\Commands;

use Illuminate\Console\GeneratorCommand;

class CrudModelCommand extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crud:model
                            {name : The name of the model.}
                            {--table= : The name of the table.}
                            {--fillable= : The names of the fillable columns.}
                            {--relationships= : The relationships for the model}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new model.';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Model';

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return __DIR__ . '/../stubs/model.stub';
    }

    /**
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace;
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

        $table = $this->option('table') ?: strtolower($this->argument('name'));
        $fillable = $this->option('fillable');
        $primaryKey = 'id';
        $relationships = trim($this->option('relationships')) != '' ? explode(';', trim($this->option('relationships'))) : [];


        foreach ($relationships as $rel) {
            // relationshipname#relationshiptype#args_separated_by_pipes
            // e.g. employees#hasMany#App\Employee|id|dept_id
            // user is responsible for ensuring these relationships are valid
            if($rel != "##")
            {       
                $parts = explode('#', $rel);
                if (count($parts) != 3) {
                    continue;
                }
                // blindly wrap each arg in single quotes
                $args = explode('|', trim($parts[2]));
                $argsString = '';
                foreach ($args as $k => $v) {
                    if (trim($v) == '') {
                        continue;
                    }
                    $argsString .= "'" . trim($v) . "', ";
                }
                $argsString = substr($argsString, 0, -2); // remove last comma
                $this->createRelationshipFunction($stub, trim($parts[0]), trim($parts[1]), $argsString);
            }
        }
        $this->replaceRelationshipPlaceholder($stub);


        return $this->replaceNamespace($stub, $name)
            ->replaceTable($stub, $table)
            ->replaceFillable($stub, $fillable)
            ->replaceClass($stub, $name);
    
    }

    /**
     * Replace the table for the given stub.
     *
     * @param  string  $stub
     * @return $this
     */
    protected function replaceTable(&$stub, $table)
    {
        $stub = str_replace(
            '{{table}}', $table, $stub
        );

        return $this;
    }

    /**
     * Replace the fillable for the given stub.
     *
     * @param  string  $stub
     * @return $this
     */
    protected function replaceFillable(&$stub, $fillable)
    {
        $stub = str_replace(
            '{{fillable}}', $fillable, $stub
        );

        return $this;
    }

    /**
     * Create the code for a model relationship
     *
     * @param string $stub
     * @param string $relationshipName  the name of the function, e.g. owners
     * @param string $relationshipType  the type of the relationship, hasOne, hasMany, belongsTo etc
     * @param array $relationshipArgs   args for the relationship function
     */
    protected function createRelationshipFunction(&$stub, $relationshipName, $relationshipType, $argsString)
    {
        $tabIndent = '    ';
        $code = "public function " . $relationshipName . "()\n" . $tabIndent . "{\n" . $tabIndent . $tabIndent
            . "return \$this->" . $relationshipType . "(" . $argsString . ");"
            . "\n" . $tabIndent . "}";
        $str = '{{relationships}}';
        $stub = str_replace($str, $code . "\n" . $tabIndent . $str, $stub);
        return $this;
    }
    
    /**
     * remove the relationships placeholder when it's no longer needed
     *
     * @param $stub
     * @return $this
     */
    protected function replaceRelationshipPlaceholder(&$stub)
    {
        $stub = str_replace('{{relationships}}', '', $stub);
        return $this;
    }
}
