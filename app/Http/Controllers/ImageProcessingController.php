<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

use DB;
use File;

class ImageProcessingController extends Controller
{
    
	
	// Image Processing Function

	function imageProcessing(Request $request,$experiment,$coup_type,$press,$grt_size,$load,$pick_color=20)
	{
		ini_set('memory_limit','16M');
		
		$input = $request->all(); //place get paramters into an array
		
		if($input['threshold'])
		{
			$pick_color = $input['threshold']; // if threshold paramters exists, override default pickcolor. 
		}
		
		$finalpatharray = $this-> getpath ($experiment,$coup_type,$press,$grt_size,$load);
				
		foreach($finalpatharray as $file2modify)
		{
			$finalpath = $file2modify;
			$sections = explode(".jpg",$finalpath);
			$proc_path = $sections[0] . '_proc.jpg';
			$modif_name = basename($proc_path);
			$webpath = 'http://localhost/api_back/public/experiment/'.$experiment.'/'.$coup_type.'/'.$press.'psi/'.$grt_size.'grit_'.$load.'kg/'.$modif_name;
			$imageobject=imagecreatefromjpeg ($finalpath); 
			$imageobjectfinal=$imageobject;
			$value= getimagesize ($finalpath);
			$changedpixels = 0;
			$max_x = $value [0];
			$max_y = $value [1];
			$total_pixels = $max_x * $max_y;
			
			for ($y = 0; $y < $max_y; $y++)
			{
				for ($x = 0; $x < $max_x; $x++)
					{
						$hex_rgb = imagecolorat($imageobject, $x, $y);
						$color_map = imagecolorsforindex($imageobject, $hex_rgb);
				
						if($color_map['red']<=$pick_color)
							{
								$target_color = imagecolorallocate ($imageobject,0,255,0);
								$found= imagesetpixel ($imageobject,$x, $y,$target_color);
								$changedpixels++;
							}
					}
			}
		
			$pixel_ratio = $changedpixels / $total_pixels * 100;
			imagejpeg($imageobject,$proc_path);
			$img_properties = app()->make('stdClass');
			$img_properties-> max_columns = $value [0];
            $img_properties-> max_rows = $value [1];
            $img_properties-> total_pixels = $total_pixels;
            $img_properties-> chosen_color = $pick_color;
            $img_properties-> changed_pixels = $changedpixels;
			$img_properties-> changed_pixel_ratio = round($pixel_ratio,2);
			$img_properties-> original_path = $finalpath;
			$img_properties-> processed_path = $proc_path;
			$img_properties-> html_process_image = "<img src = '".$webpath."'></img>";
			echo($img_properties-> html_process_image);
			$info_for_database = DB::table('processed_images_enviro_grit')->insert(['total_pixels' => $total_pixels, 'chosen_color' => $pick_color,'changed_pixels' => $changedpixels, 'changed_pixel_ratio' => $pixel_ratio,
											'coupon_type' => $coup_type, 'pressure' => $press, 'grit_size' => $grt_size, 'loading' => $load, 'file_name' => $modif_name, 'experiment_type' => $experiment]);
		}
			return response() -> json($img_properties, 200);
	} 
	
	
	function getpath($experiment,$coupon_type,$pressure,$grit_size,$loading)
	{
		$fixeddir = 'C:\\inetpub\\wwwroot\\api_back\\public\\experiment';
		$files0 = scandir($fixeddir);
		$temp0 = $files0;
		
		foreach ($temp0 as $value0)
		{
			$found0 = str_contains($value0, $experiment);
			
			if ($found0)
				{
					break;
				}
		}
		
		if (!$found0)
			{
				return (false);
			}
				
		$dir0 = $fixeddir . '\\' . $value0;
		$files1 = scandir($dir0);
		$temp1 = $files1;
		
		foreach ($temp1 as $value1)
		{
			$found1 = str_contains($value1, $coupon_type);
			
			if ($found1)
				{
					break;
				}
		}
		
		if (!$found1)
			{
				return (false);
			}
				
		$dir1 = $dir0 . '\\' . $value1;
		$files2 = scandir($dir1);
		$temp2 = $files2;
		
		foreach ($temp2 as $value2)
		{
			$found2 = str_contains($value2, $pressure);
			
			if ($found2)
				{
					break;
				}
		}
			
		if (!$found2)
			{
				return (false);
			}
		
		$dir2 = $dir1 . '\\' . $value2;
		$files3 = scandir($dir2);
		$temp3 = $files3;
		$grit_size_loading = $grit_size . "grit_" . $loading . "kg";
		
		foreach ($temp3 as $value3)
		{
			$found3 = str_contains($value3, $grit_size_loading);
				
				if ($found3)
					{
						break;
					}
		}
			
		if (!$found3)
			{
				return (false);
			}
		
		$dir3 = $dir2 . '\\' . $value3;
		$filepath= $this-> getfile ($dir3);
		return ($filepath);
	} 
	
	function getfile ($path)
	{
		$files4 = File::allFiles($path);	
		$files2process = 0;
		$files_processed = 0;
		
		foreach ($files4 as $file)
		{
			$filename=$file->getFilename();
			$proc_search = strpos($filename, '_proc');
			
			if(!$proc_search)
				{
					$pathname [$files2process]= $file-> getPathname();
				}
			
			if($proc_search)
				{
					$files_processed++;
				}
			
			$files2process++;
		}
		
		$files2process=$files2process-$files_processed;
		return $pathname;
	}
}
