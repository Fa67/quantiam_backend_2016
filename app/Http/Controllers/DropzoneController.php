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

			$this->makeThumbnails($targetPath ,$_FILES['file']['name'],null);
			
					return response() -> json($_FILES['file'], 200);
	
	
	}
	
	
	
	
private function makeThumbnails($updir, $img, $id)
{
    $thumbnail_width = 100;
    $thumbnail_height = 100;
    $thumb_beforeword = "thumb_";
    $arr_image_details = getimagesize("$updir" . $id . '' . "$img"); // pass id to thumb name
    $original_width = $arr_image_details[0];
    $original_height = $arr_image_details[1];
    if ($original_width > $original_height) {
        $new_width = $thumbnail_width;
        $new_height = intval($original_height * $new_width / $original_width);
    } else {
        $new_height = $thumbnail_height;
        $new_width = intval($original_width * $new_height / $original_height);
    }
    $dest_x = intval(($thumbnail_width - $new_width) / 2);
    $dest_y = intval(($thumbnail_height - $new_height) / 2);
    if ($arr_image_details[2] == 1) {
        $imgt = "ImageGIF";
        $imgcreatefrom = "ImageCreateFromGIF";
    }
    if ($arr_image_details[2] == 2) {
        $imgt = "ImageJPEG";
        $imgcreatefrom = "ImageCreateFromJPEG";
    }
    if ($arr_image_details[2] == 3) {
        $imgt = "ImagePNG";
        $imgcreatefrom = "ImageCreateFromPNG";
    }
    if ($imgt) {
        $old_image = $imgcreatefrom("$updir" . $id . '' . "$img");
        $new_image = imagecreatetruecolor($thumbnail_width, $thumbnail_height);
        imagecopyresized($new_image, $old_image, $dest_x, $dest_y, 0, 0, $new_width, $new_height, $original_width, $original_height);
        $imgt($new_image, "$updir" . $id . '' . "$thumb_beforeword" . "$img");
    }
}
	
}
