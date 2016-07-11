<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

class UploadController extends Controller
{
    //
	
	function dropzoneUpload(Request $request )
	{
	
//	$return_array['request'] = $request ->all();
	
	
//	dd($_POST);
		
	$hash = $_POST['hash'];
	
				if (!empty($_FILES)) {
					 
					$tempFile = $_FILES['file']['tmp_name'];          //3             
					  
					$targetPath = "uploads/".$hash.'/';  //4
					 
					 
					 if (!file_exists($targetPath)) {
					mkdir($targetPath, 0777, true);
					}
					 
					 
					$targetFile =  $targetPath. $_FILES['file']['name'];  //5
				 
					move_uploaded_file($tempFile,$targetFile); //6
					 
				}

				$return_array['request'] = $_FILES;
				$return_array['post'] = $_POST;

			
				$return_array['text'] = $text;

				echo json_encode($return_array);
	
	
	}
	
	
	
	
	
	
}
