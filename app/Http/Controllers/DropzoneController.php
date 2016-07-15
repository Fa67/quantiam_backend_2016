<?php

namespace App\Http\Controllers;

use DirectoryIterator;

use Illuminate\Http\Request;

use App\Http\Requests;

class DropzoneController extends Controller
{

	function __construct(){
	
	
	$this->path = 'uploads/';
	
	}

	function deleteImage($hash,$filename)
	{
	
	unlink($this->path.$hash.'/'.$filename);
	unlink($this->path.$hash.'/thumb/'.$filename);
	return response() -> json(['success'=>'Deleted '.$filename.''], 200);
	
	
	}
	
function getImages ($hash)
		
		{

				$dir = 	$this->path.$hash;
				$webpath = url('/').'/'.$this->path.$hash.'/';
				
				$filelist = scandir($dir);
				foreach ($filelist as $key => $link) {
					if(!is_dir($dir.$link) && $link != 'thumb'){
					
						$tempObj['url'] = $webpath.$link;
						$tempObj['thumbUrl'] = $webpath.'thumb/'.$link;
						$tempObj['filename'] = $link;
						
						//dd($link);
						
						$return[] = $tempObj;
						
					}

					}
					return response() -> json($return, 200);
			
			
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
    $thumbnail_width = 50;
    $thumbnail_height = 50;
   // $thumb_beforeword = "thumb_";
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
		
		if (!file_exists("$updir" . $id . '' . "/thumb/")) {
			mkdir("$updir" . $id . '' . "/thumb/", 0777, true);
		}
				
        $imgt($new_image, "$updir" . $id . '' . "/thumb/" . "$img");
    }
}
	
}
