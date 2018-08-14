<?php

namespace Wot\CrudGenerator\Commands;

use File;
use Illuminate\Console\Command;

class CrudCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */

    protected $signature = 'crud:generate
                            {name : Module name for the form & model.}';
    // protected $signature = 'crud:generate
    //                         {name : The name of the Crud.}
    //                         {--fields= : Fields name for the form & model.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate Crud.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $pluralName = str_plural($this->argument('name'));

        if($this->argument('name') != 'all' && $this->argument('name') != $pluralName)
        {
          return $this->error('Check Your Naming Conventaion');
        }


        if($this->argument('name') == 'all')
        {
            $fileArray = array_slice(scandir(storage_path('wot_crud/')) , 2); // getting list of jsons files

            // looping thourgh json files
            foreach($fileArray as $file)
            {
                $filePath = storage_path('wot_crud/'.$file.'');

                $commonConfig = $this->getCommon($filePath);
                
                $name = ucfirst($commonConfig->table_name);

                $controllerNamespace = '';

                $fields = $this->processJSONFields($filePath);

                $foreignKeys = $this->processJSONForeignKeys($filePath);
                $validations = $this->processJSONValidations($filePath);
                $relationships = $this->processJSONRelationships($filePath);

               
                   if ($fields && $commonConfig) {
                        // $fields = $this->option('fields');
                        $viewPath = '';
                        $primaryKey = 'id';
                        $modelName = $commonConfig->model;

                        // $tableName = $commonConfig->table_name;

                        $fillableArray = explode(',', $fields);
                        foreach ($fillableArray as $value) {
                            $data[] = preg_replace("/(.*?):(.*)/", "$1", trim($value));
                        }

                        $commaSeparetedString = implode("', '", $data);
                        $fillable = "['" . $commaSeparetedString . "']";

                        $this->call('crud:controller', ['name' => $controllerNamespace . $name . 'Controller', '--crud-name' => $modelName, '--view-path' => $viewPath]);
                        $this->call('crud:repository', ['name' => $controllerNamespace . $name . 'Repository', '--crud-name' => $name, '--fields' => $fields]);           

                        $processRepositories = $this->processRepositories($name); // Append contract | repository to posts.json

                        $this->registerRepositories(); // Bind inside repository provider

                        $this->call('crud:model', ['name' => $modelName, '--fillable' => $fillable, '--table' => str_plural(strtolower($name)), '--relationships' => $relationships]);

                        $this->requestFile($name, $validations); // Validation Request File.
                        $this->contractFile($name); // Contrct File.


                        if($commonConfig->is_migration == true) // If migration is on
                        {
                            $this->call('crud:migration', ['name' => str_plural(strtolower($name)), '--schema' => $fields, '--pk' => $primaryKey, '--fk' => $foreignKeys, '--migrationprefix' => $commonConfig->migration_prefix]);
                        }


                        if($commonConfig->is_view == true) // If view is on
                        {

                            $this->call('crud:view', ['name' => $name, '--fields' => $fields, '--view-path' => $viewPath, '--server-side-table' => $commonConfig->is_serverside_database_table]);
                        }   

                        $routeFile = base_path('routes/web.php');
                        if (file_exists($routeFile) && $commonConfig->is_route == true) {
                            $controller = ($controllerNamespace != '') ? $controllerNamespace . '\\' . $name . 'Controller' : $name . 'Controller';

                            $isAdded = File::append($routeFile, "\nRoute::resource('" . strtolower($name) . "', '" . $controller . "');");
                            if ($isAdded) {
                                $this->info('Crud/Resource route added to ' . $routeFile);
                            } else {
                                $this->info('Unable to add the route to ' . $routeFile);
                            }
                        }
                    } else {
                        $this->error('Configration incorrect');
                    }
            }
        } else { // If not all

            $relationships = $this->processJSONRelationships(config_path('posts.json'));
            $commonConfig = $this->getCommon(config_path('posts.json'));

            $name = ucfirst($this->argument('name'));

            $controllerNamespace = '';

            $fields = $this->processJSONFields(config_path('posts.json'));

            $foreignKeys = $this->processJSONForeignKeys(config_path('posts.json'));
            $validations = $this->processJSONValidations(config_path('posts.json'));
            $relationships = $this->processJSONRelationships(config_path('posts.json'));


            if ($fields && $commonConfig) {
                // $fields = $this->option('fields');
                $viewPath = '';
                $primaryKey = 'id';

                // $tableName = $commonConfig->table_name;

                $fillableArray = explode(',', $fields);
                foreach ($fillableArray as $value) {
                    $data[] = preg_replace("/(.*?):(.*)/", "$1", trim($value));
                }

                $commaSeparetedString = implode("', '", $data);
                $fillable = "['" . $commaSeparetedString . "']";

                $this->call('crud:controller', ['name' => $controllerNamespace . $name . 'Controller', '--crud-name' => $name, '--view-path' => $viewPath]);
                $this->call('crud:repository', ['name' => $controllerNamespace . $name . 'Repository', '--crud-name' => $name, '--fields' => $fields]);           

                $processRepositories = $this->processRepositories($name); // Append contract | repository to posts.json

                $this->registerRepositories(); // Bind inside repository provider

                $this->call('crud:model', ['name' => $commonConfig->model, '--fillable' => $fillable, '--table' => str_plural(strtolower($name)), '--relationships' => $relationships]);

                $this->requestFile($name, $validations); // Validation Request File.
                $this->contractFile($name); // Contrct File.


                if($commonConfig->is_migration == true) // If migration is on
                {
                    $this->call('crud:migration', ['name' => str_plural(strtolower($name)), '--schema' => $fields, '--pk' => $primaryKey, '--fk' => $foreignKeys, '--migrationprefix' => $commonConfig->migration_prefix]);
                }


                if($commonConfig->is_view == true) // If view is on
                {

                    $this->call('crud:view', ['name' => $name, '--fields' => $fields, '--view-path' => $viewPath, '--server-side-table' => $commonConfig->is_serverside_database_table]);
                }   

                $routeFile = base_path('routes/web.php');
                if (file_exists($routeFile) && $commonConfig->is_route == true) {
                    $controller = ($controllerNamespace != '') ? $controllerNamespace . '\\' . $name . 'Controller' : $name . 'Controller';

                    $isAdded = File::append($routeFile, "\nRoute::resource('" . strtolower($name) . "', '" . $controller . "');");
                    if ($isAdded) {
                        $this->info('Crud/Resource route added to ' . $routeFile);
                    } else {
                        $this->info('Unable to add the route to ' . $routeFile);
                    }
                }
            } else {
                $this->error('Configration incorrect');
            }
        }
    }


    /**
     * Process the JSON Fields from default file.
     *
     * @param  string $file
     *
     * @return string
     */
    protected function processJSONFields($file)
    {
        $json = File::get($file);
        $fields = json_decode($json);
        $fieldsString = '';
        if(!empty($fields->fields))
        {
            foreach ($fields->fields as $k => $field) {
                if(count($fields->fields) != ($k + 1))
                {
                    $fieldsString .= $field->name . ':' . $field->type . ',';
                } else {
                    $fieldsString .= $field->name . ':' . $field->type;
                }
            }
        }

        $fieldsString = rtrim($fieldsString, ';');

        return $fieldsString;
    }


    /**
     * Process the JSON Foreign keys.
     *
     * @param  string $file
     *
     * @return string
     */
    protected function processJSONForeignKeys($file)
    {
        $json = File::get($file);
        $fields = json_decode($json);
        if ($fields == null || ! property_exists($fields, 'foreign_keys')) {
            return '';
        }

        $foreignKeysString = '';
            if(!empty($fields->foreign_keys))
            {
                foreach ($fields->foreign_keys as $foreign_key) {
                    if($foreign_key->column != "" && $foreign_key->references != "" && $foreign_key->on != "")
                    {
                        $foreignKeysString .= $foreign_key->column . '#' . $foreign_key->references . '#' . $foreign_key->on;
                    }

                    if (property_exists($foreign_key, 'onDelete')) {
                        if($foreign_key->onDelete != "")
                        {
                            $foreignKeysString .= '#' . $foreign_key->onDelete;
                        }
                    }

                    if (property_exists($foreign_key, 'onUpdate')) {
                        if($foreign_key->onUpdate != "")
                        {
                            $foreignKeysString .= '#' . $foreign_key->onUpdate;
                        }
                    }

                    $foreignKeysString .= ',';
                }
            }

            $foreignKeysString = rtrim($foreignKeysString, ',');

        return $foreignKeysString;
    }

    /**
     * Process the JSON Relationships.
     *
     * @param  string $file
     *
     * @return string
     */
    protected function processJSONRelationships($file)
    {
        $json = File::get($file);
        $fields = json_decode($json);

        if ($fields == null ||  !property_exists($fields, 'relationships')) {
            return '';
        }

        $relationsString = '';
        foreach ($fields->relationships as $relation) {
            $relationsString .= $relation->name . '#' . $relation->type . '#' . $relation->class . ';';
        }

        $relationsString = rtrim($relationsString, ';');

        return $relationsString;
    }

    /**
     * Process the JSON Validations.
     *
     * @param  string $file
     *
     * @return string
     */
    protected function processJSONValidations($file)
    {
        $json = File::get($file);
        $fields = json_decode($json);
        if ($fields == null || !property_exists($fields, 'validations')) {
            return '';
        }
        $validationsString = '';
        foreach ($fields->validations as $validation) {
            $validationsString .= $validation->field . '#' . $validation->rules . ';';
        }
        $validationsString = rtrim($validationsString, ';');
        return $validationsString;
    }

    /**
     * Get the Migration Prefix Name.
     *
     * @param  string $file
     *
     * @return string
     */
    protected function getCommon($file)
    {
        $json = File::get($file);
        $fields = json_decode($json);

        if ($fields == null || !property_exists($fields, 'common')) {
            return '';
        } else {
            return ($fields->common[0]) ? $fields->common[0] : null;
        }
    }

    /**
     * Get repositories and contracts.
     *
     * @param  string $file
     *
     * @return string
     */
    protected function getRepos($file)
    {
        $json = File::get($file);
        $fields = json_decode($json);

        if ($fields == null || !property_exists($fields, 'repositories')) {
            return '';
        } else {
            return ($fields->repositories) ? $fields->repositories : [];
        }
    }

    /**
     * Get the stub file.
     *
     * @param string $type
     *
     * @return slub file with content
     */  
    protected function getStub($type)
    {
        $upOne = realpath(__DIR__ . '/..');
        $slubPath = $upOne .'/stubs/'.$type.'.stub';
        return file_get_contents($slubPath);
    }

    /**
     * Process the request file.
     *
     * 
     */    
    protected function requestFile($name, $validations)
    {
        $validations = rtrim($validations, ';');
        $validationRules = '';
        if (trim($validations) != '') {
            $validationRules = "[";
            $rules = explode(';', $validations);
            foreach ($rules as $v) {
                if (trim($v) == '') {
                    continue;
                }
                // extracting of fields
                $parts = explode('#', $v);
                $fieldName = trim($parts[0]);
                $rules = trim($parts[1]);
                $validationRules .= "\n\t\t\t'$fieldName' => '$rules',";
            }
            $validationRules = substr($validationRules, 0, -1); // removed last comma
            $validationRules .= "\n\t\t];";
        }

        $requestTemplate = str_replace(
            ['{{crudNameCap}}','{{requestRules}}'],
            [$name, $validationRules],
            $this->getStub('request')
        );

        if(!file_exists($path = app_path('/Http/Requests')))
            mkdir($path, 0777, true);

        file_put_contents(app_path("/Http/Requests/{$name}Request.php"), $requestTemplate);
    }

    /**
     * Process the contract file.
     *
     * 
     */    
    protected function contractFile($name)
    {
        $contractTemplate = str_replace(
            ['{{crudNameCap}}'],
            [$name],
            $this->getStub('contract')
        );

        if(!file_exists($path = app_path('/Http/Contracts')))
            mkdir($path, 0777, true);

        file_put_contents(app_path("/Http/Contracts/{$name}Contract.php"), $contractTemplate);
    }


  
    /**
    * Append Contracts And Repositories to posts.json
    *
    *
    */
    protected function processRepositories($name)
    {

        $jsonString = file_get_contents(config_path('posts.json'));

        $data = json_decode($jsonString, true);
        
        // New Values
        $replaceContract = '\\\App\\\Http\\\Contracts\\\\'.$name.'Contract::class';
        $replaceRepository = '\\\App\\\Http\\\Repository\\\\'.$name.'Repository::class';
        $contractsArray = $data['repositories'][0]['contracts'][0];

        foreach($contractsArray as $i => $con)
        {
            $dbl = "\\\\"; // double slash
            $singl = "\\"; // single slash
            $replaced = str_replace(
                [$singl],
                [$dbl],
                $con
            );
            $contractsArray[$i] = $replaced;
        }

        // PUsh new Values to old array 
        array_push($contractsArray,  $replaceContract);


        $repositoryArray = $data['repositories'][1]['repository'][0];

        foreach($repositoryArray as $r => $con)
        {
            $dbl = "\\\\"; // double slash
            $singl = "\\"; // single slash
            $replaced = str_replace(
                [$singl],
                [$dbl],
                $con
            );
            $repositoryArray[$r] = $replaced;
        }

        array_push($repositoryArray,  $replaceRepository);

        // Update Values
        $data['repositories'][0]['contracts'] = [$contractsArray];
        $data['repositories'][1]['repository'] = [$repositoryArray];

        // Write File
        $newJsonString = json_encode($data, JSON_PRETTY_PRINT);

        file_put_contents(config_path('posts.json'), stripslashes($newJsonString));
    }

    protected function registerRepositories()
    {
        $path = file_get_contents(config_path('posts.json'));
        $arrayJson = json_decode($path , true);

        $contracts = $arrayJson['repositories'][0]['contracts'][0];
        $repository = $arrayJson['repositories'][1]['repository'][0];

        $bindedStringAry = [];
        foreach($contracts as $k => $contract)
        {
            $string = '';
            $string .= '$this->app->bind('.$contract.', '.$repository[$k].');';
            $bindedStringAry[] = $string;
        }
        $this->replaceRepoServiceProviderFile($bindedStringAry);
        return $bindedStringAry;
    }

    /**
     * Process the repository provider file.
     *
     * 
     */    
    protected function replaceRepoServiceProviderFile($repoBindString)
    {
        $prefix = $providerList = '';
        foreach ($repoBindString as $fruit)
        {
            $providerList .= $prefix . '' . $fruit . '';
            $prefix = '    ';
        }

        $template = str_replace(
            '{{RepositoryServiceProvider}}',
            $providerList,
            $this->getStub('repository_service_provider')
        );

         if(!file_exists($path = app_path('Providers')))
            mkdir($path, 0777, true);

        file_put_contents(app_path("Providers/RepositoryServiceProvider.php"), $template);
    }

}
