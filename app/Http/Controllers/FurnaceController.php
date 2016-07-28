<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Models\FurnaceRun;
use App\Models\FurnaceRunProfile;
use App\Models\Furnace;
use DB;
use File;


class FurnaceController extends Controller
{
   
   function buildFurnaceRun($furnacerunID)
	{
		$fullobject = (new FurnaceRun($furnacerunID));
		return response() -> json($fullobject, 200);
	} 
      
   
	function furnacesteelrun($furnacerunID)
	{
		$steel = (new FurnaceRun()) -> getfurnacesteel($furnacerunID);
		return response() -> json($steel, 200);
	} 
	
	
	function createFurnacerun (Request $request)
	{
		$userID = $request->user->employeeid;
		$response = (new FurnaceRun()) -> createFurnacerun($userID);			
		return response() -> json($response, 200);
	} 
	
	function furnaceoperatorrun($furnacerunID)
	{
		$operator = (new FurnaceRun()) -> getfurnaceoperator($furnacerunID);
		return response() -> json($operator, 200);
	} 
	
	function furnacepropertiesrun($furnacerunID)
	{
		$properties = (new FurnaceRun()) -> getfurnaceproperties($furnacerunID);
		return response() -> json($properties, 200);
	} 
	
	
	function furnaceprofilerun($furnacerunID)
	{
		$profile = (new FurnaceRun()) -> getfurnaceprofile($furnacerunID);
		return response() -> json($profile, 200);
	} 

	function furnaceRunDatatables (Request $request)
	{
		$params = $request->all();
		$response = (new FurnaceRun())->datatablesFurnaceRunlist($params);
		return response() -> json($response, 200);
	
	}
	
	function getFurnaceList(Request $request)
	{
		$params = $request->all();
		$response = (new Furnace())->getFurnaceList($params);
		return response() -> json($response, 200);
	
	}	
	
	function getFurnaceProfileList(Request $request)
	{
		$params = $request->all();
		$response = (new FurnaceRunProfile())->getFurnaceProfileList($params);
		return response() -> json($response, 200);
	
	}
	function getFurnaceRunTypeList(Request $request)
	{
		$params = $request->all();
		$response = (new FurnaceRun())->getFurnaceRunTypeList($params);
		return response() -> json($response, 200);
	
	}
	
	

	
	
	function editFurnaceRun(Request $request,$furnacerunID)
	{
	
		$input = $request->all();
		$response = (new FurnaceRun())->editFurnaceRun($furnacerunID,$input);
		return response() -> json($response, 200);
	
	
	}	
	
	function editFurnaceRunSteel(Request $request,$furnacerunID,$inventoryID)
	{
		$input = $request->all();
		$response = (new FurnaceRun())->editFurnaceRunSteel($furnacerunID,$inventoryID,$input);
		return response() -> json($response, 200);
	}
	
	
	function addFurnaceRunSteel (Request $request,$furnacerunID,$inventoryID)
	{
		$response = (new FurnaceRun())->addSteel($furnacerunID,$inventoryID);
		return response() -> json($response, 200);
	}
	
	function deleteFurnaceRunSteel (Request $request,$furnacerunID,$inventoryID)
	{
		$response = (new FurnaceRun())->deleteSteel($furnacerunID,$inventoryID);
		return response() -> json($response, 200);
	}
	
	
	function addFurnaceRunOperator  (Request $request,$furnacerunID,$employeeID)
	{
		$response = (new FurnaceRun())->addOperator($furnacerunID,$employeeID);
		return response() -> json($response, 200);
	}
	
	function deleteFurnaceRunOperator (Request $request,$furnacerunID,$employeeID)
	{
		$response = (new FurnaceRun())->deleteOperator($furnacerunID,$employeeID);
		return response() -> json($response, 200);
	}
	
	// Image Processing Function

	function imageProcessing(Request $request,$experiment,$coup_type,$press,$grt_size,$load,$pick_color=20)
	//function imageProcessing($pick_color=20)
	{
		ini_set('memory_limit','16M');
		
		
		$input = $request->all(); //place get paramters into an array
		if($input['threshold'])
		{
			$pick_color = $input['threshold']; // if threshodl paramters exists, override default pickcolor. 
		}
	
		
		$finalpatharray = $this-> getpath ($experiment,$coup_type,$press,$grt_size,$load);
		//dd($finalpatharray,$coup_type,$press,$grt_size,$load);
		
		foreach($finalpatharray as $file2modify)
		{
			
		
			$finalpath = $file2modify;
			//$finalpath = "C:\\inetpub\\wwwroot\\api_back\\public\\experiment\\enviro\coated\80psi\\30-60grit_3kg\\img02.jpg";	
			//dd($finalpath);
			
			//$proc_path = "C://inetpub/wwwroot/api_back/public/experiment/enviro/coated/80psi/30-60grit_3kg/img02_proc.jpg";
			
			
			$sections = explode(".jpg",$finalpath);
			//dd($sections);
			$proc_path = $sections[0] . '_proc.jpg';
			//dd($sections,$proc_path);
			
			$modif_name = basename($proc_path);
			//dd($modif_name);
			
			
			
			
			//dd($proc_path);
			//$webpath = "http://localhost/api_back/public/experiment/enviro/coated/80psi/30-60grit_3kg/img02_proc.jpg";
			$webpath = 'http://localhost/api_back/public/experiment/'.$experiment.'/'.$coup_type.'/'.$press.'psi/'.$grt_size.'grit_'.$load.'kg/'.$modif_name;
			//dd($finalpath,$proc_path,$webpath);
			
			$imageobject=imagecreatefromjpeg ($finalpath); 
		
			$imageobjectfinal=$imageobject;
		
			$resourcetype=get_resource_type ($imageobject);
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
			
			//dd($pixel_ratio);
			
			$info_for_database = DB::table('processed_images_enviro_grit')->insert(['total_pixels' => $total_pixels, 'chosen_color' => $pick_color,'changed_pixels' => $changedpixels, 'changed_pixel_ratio' => $pixel_ratio,
											'coupon_type' => $coup_type, 'pressure' => $press, 'grit_size' => $grt_size, 'loading' => $load, 'file_name' => $modif_name, 'experiment_type' => $experiment]);
		
			//dd($info_for_database);
		}
			

			return response() -> json($img_properties, 200);
		
		
	} 
	
	
	function getpath($experiment,$coupon_type,$pressure,$grit_size,$loading)
	{
		
		//$fixeddir = 'C:\\inetpub\\wwwroot\\api_back\\public\\experiment\\enviro';
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
		//dd($dir0);		
		$files1 = scandir($dir0);
		//dd($files1);
		
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
		//dd($dir1);
		
		
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
		//dd($dir2);
		
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
		
		//dd($dir3);
		$filepath= $this-> getfile ($dir3);
		//dd($filepath);
		return ($filepath);
	} 
	
	function getfile ($path)
	{
		
		$files4 = File::allFiles($path);	
		//dd($files4);
				
		//$pathname= array();
		$files2process = 0;
		$files_processed = 0;
		//dd($pathname);
		foreach ($files4 as $file)
		{
			
			$filename=$file->getFilename();
			//dd ($filename);
			
			$proc_search = strpos($filename, '_proc');
			//dd($proc_search);
			if(!$proc_search)
				{
					$pathname [$files2process]= $file-> getPathname();
				}
			//dd($pathname);
			if($proc_search)
				{
					$files_processed++;
				}
			$files2process++;
		}
		$files2process=$files2process-$files_processed;
		//dd($pathname,$files2process,$files_processed);
		
		return $pathname;
	}
	
	
	
	
	
	
	
}