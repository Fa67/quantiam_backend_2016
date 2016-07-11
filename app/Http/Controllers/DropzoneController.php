<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

class DropzoneController extends Controller
{

	function __construct(){
	
	
	$this->path = 'uploads/';
	
	}

function get_hashed_images ($hash)
		
		{
		
				if(!$hash)
				{
				$return_array['error'] = 'There are missing mandatory arguments.'; 
				goto end;
				}	
				
				
				$dir = 	$this->path.$hash;
				$webpath = url('/').$this->path.$hash;
					
				$files = scandir($dir);
										
				
				foreach($files as $file_name)
				{
				
				
					if(!is_dir($file_name))
					{
						
					$key = preg_replace('/\\.[^.\\s]{3,4}$/', '', $file_name);
				
					$image_size = getimagesize($dir.'/'.$file_name);
					
						if( $image_size['mime'])
						{
						$return_array[$key]['web_path'] =  $webpath.'/'.$file_name;
						$return_array[$key]['file_name'] =  $file_name;
						$return_array[$key]['width'] =  $image_size['0'];
						$return_array[$key]['height'] =  $image_size['1'];
						$return_array[$key]['mime'] =  $image_size['mime'];
						}
					}
					
				}
				
			
						
		
				return $return_array;
			
			
		}
	
	
	function dropzoneUpload(Request $request )
	{
	
				$input = $request ->all();
	
				
				$hash = $input['hash'];

	
	
				if (!empty($_FILES)) {
					 
					$tempFile = $_FILES['file']['tmp_name'];          //3             
					  
					$targetPath = $this->path.$hash.'/';  //4
					 
					 
					 if (!file_exists($targetPath)) {
					mkdir($targetPath, 0777, true);
					}
					 
					 
					$targetFile =  $targetPath. $_FILES['file']['name'];  //5
				 
					move_uploaded_file($tempFile,$targetFile); //6
					 
				}

				$return_array['request'] = $_FILES;
				$return_array['post'] = $_POST;

			
			
					return response() -> json($_FILES['file'], 200);
	
	
	}
	
	
	
	
	
	
}
