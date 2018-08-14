<?php

namespace Wot\CrudGenerator\Commands;

use File;
use Illuminate\Console\Command;

class CrudRemoveCommand extends Command {
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'crud:remove';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Remove crud.';

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function handle() {

		if(is_dir(app_path('Http/Contracts')))
		{

			$contractArray = array_slice(scandir(app_path('Http/Contracts')) , 2);
			foreach($contractArray as $contractSingle){ // 
			  if(is_file($contractSingle = app_path('Http/Contracts/'.$contractSingle)))
			    unlink($contractSingle); // 
			}
		}

		if(is_dir(app_path('Http/Repository')))
		{
			$repoArray = array_slice(scandir(app_path('Http/Repository')) , 2);
			foreach($repoArray as $repoSingle){ // 
			  if(is_file($repoSingle = app_path('Http/Repository/'.$repoSingle)))
			    unlink($repoSingle); // 
			}
		}


		if(is_dir(app_path('Http/Requests')))
		{
			$requestArray = array_slice(scandir(app_path('Http/Requests')) , 2);
			foreach($requestArray as $requestSingle){ // 
			  if(is_file($requestSingle = app_path('Http/Requests/'.$requestSingle)))
			    unlink($requestSingle); // 
			}
		}


		if(is_dir(app_path('Http/Controllers')))
		{
			$controllerAry = array_slice(scandir(app_path('Http/Controllers')) , 2);
			foreach($controllerAry as $controllerSingle) { // 
				if($controllerSingle == 'Controller.php') // Except Controller.php 
				{
				  if(is_file($controllerSingle = app_path('Http/Controllers/'.$controllerSingle)))
				    unlink($controllerSingle); // 	
				}
			}
		}

			$this->removeRepositories();

	}


	  /**
    * Remove repositories and contracts from posts.json
    *
    */
    protected function removeRepositories()
    {

        $jsonString = file_get_contents(config_path('posts.json'));

        $data = json_decode($jsonString, true);
        
        // New values
        $contractsArray = $data['repositories'][0]['contracts'][0];

        // Push new values to old array 
        array_push($contractsArray,  []);


        $repositoryArray = $data['repositories'][1]['repository'][0];

        array_push($repositoryArray,  []);

        // Update values
        $data['repositories'][0]['contracts'] = [ [] ];
        $data['repositories'][1]['repository'] = [ [] ];


        // Write File
        $newJsonString = json_encode($data, JSON_PRETTY_PRINT);

        file_put_contents(config_path('posts.json'), stripslashes($newJsonString));
    }

}

