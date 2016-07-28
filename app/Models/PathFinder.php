<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;
use DB;
use DNS2D;
use File;

class PathFinder extends Model
{
	
	function __construct($furnaceName = null)
    {
		if($furnaceName)
		{
			$this -> pathFinder ($furnaceName);
		}
		
		
    }
	
	function pathFinder ($furnaceName)
	{
		
		
		
		
	}
	
		
	function getpath($furnaceName,$furnaceRunName)
	{
		
		$fixeddir = 'C:\inetpub\wwwroot\api_back\storage\furnace';
		$files1 = scandir($fixeddir);
		$temp1 = $files1;
		foreach ($temp1 as $value1)
		{
			$found1 = str_contains($value1, $furnaceName);
			if ($found1)
			{
				break;
			}
		}
		
		if (!$found1)
		{
			return (false);
		}
		
		$dir1 = $fixeddir . '\\' . $value1;
		if ($furnaceName == 'HP20')
		{
			$dir1 = $dir1 . '\Data Logs';	
		}
		$files2 = scandir($dir1);
		$temp2 = $files2;
		foreach ($temp2 as $value2)
		{
			$found2 = str_contains($value2, $furnaceRunName);
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
		$filepath= $this-> getfile ($dir2);
		return ($filepath);
	}	

	function getfile ($path)
	{
		//$singletextfile = array ('HP16','HP20');
		$files3 = File::allFiles($path);	
		//dd($files3);
		$prevsize=0;
		$pathname=' ';
		foreach ($files3 as $file)
		{
			$size=$file->getSize();
			if($prevsize<$size)
				{
					$prevsize=$size;
					$pathname=$file->getPathname();
				}	
		}
	
	
	
		$filecontent = $this-> parseDebindTextFile($pathname);	
		
		return $filecontent;
	}
		
		
	function parseDebindTextFile($finalpath)	
	{
		$filecontents = File::get($finalpath);
		//$filecontents = file_get_contents($finalpath);
		//$fractions = str_getcsv ($filecontents,",");
		
		//$parselines = fgetcsv($finalpath,0,",");
		$rows = explode("\r\n", $filecontents);
			
		
		$response = app() -> make('stdClass');
    	

		// Grab units
        $response -> path = substr($rows[0], 0);
		//dd ($response,$rows);
		
		
		
        $labels = [3 => 'Ar Flow A', 4 => 'Ar2 Flow B', 5 => 'Pressure', 6 => 'Temperature'];
		
        $units = [3 => 'slpm', 4 => 'slpm', 5 => 'torr', 6 => '°C'];
		
        $response -> dataset = array();
		
		// Row parsing
		
		unset($rows[0]);
		
		foreach ($rows as $key => $value)
		{
			
				$columns [$key]= explode(",",$rows[$key]);
				
				if(isset($columns[$key][1]) && isset($columns[$key][2]))
				{
					//Gets rid of miliseconds
					$columns_time [1]= explode(":",$columns [$key][1]);
					unset($columns_time{1}[3]);
					$columns [$key][1]= implode(":",$columns_time [1]);
					
					//x axis array
					$x_axis [$key] = strtotime($columns [$key][0]." ".$columns [$key][1]."-06:00")*1000;
					
					//y-axis arrays
					$mfca [$key] = $columns [$key][2];
					$mfcb [$key] = $columns [$key][3];
					$pressure [$key] = $columns [$key][16];
					$temperature [$key] = $columns [$key][18];
				                	
				}
			//	echo ($columns [$key][1].'<br>');
		}
					
		unset($rows[$key],$columns[$key]);
		
		/*for ($v = 2; $v <= 18; $v++)
					{
						
						$x_y_data = app()->make('stdClass');
						**********************************$x_y_data->x = $x_axis[$k];
						$x_y_data->x_label = 'datetime';
						$x_y_data->y_label = $units[$k];
						$x_y_data->data = array();
						$response->dataset[] = $x_y_data;
					}
					dd ($temp_y);*/
		
		for ($k = 3; $k <= 6; $k++)
		{
            $temp = app()->make('stdClass');
            $temp->title = $labels[$k];
            $temp->x_label = 'datetime';
            $temp->y_label = $units[$k];
            $temp->data = array();
            $response->dataset[] = $temp;

		}		
		dd ($mfca,$mfcb,$pressure,$temperature,$response);
		
		
		/*for ($k = 0; $k < 3; $k++)
                {
                    $tempRow = preg_split('/[\s]+/', $rows[$i]);
                    $tempObj = array();

                    $tempObj[0] = strtotime($tempRow[0].' '.$tempRow[1]. ' '.$tempRow[2])*1000;
                    $tempObj[1] = (float) $tempRow[$k+3];

                    $response  -> dataset[$k] -> data[] = $tempObj;

                }
		
		
		
		
		
		
				dd ($labels,$units,$columns,$response);
			}
		
		
		
		
		
		
		
		//dd ($labels,$units,$response,$rows);

		for($i = 6; $i < count($rows) - 1; $i += 20)
            {
                for ($k = 0; $k < 3; $k++)
                {
                    $tempRow = preg_split('/[\s]+/', $rows[$i]);
                    $tempObj = array();

                    $tempObj[0] = strtotime($tempRow[0].' '.$tempRow[1]. ' '.$tempRow[2])*1000;
                    $tempObj[1] = (float) $tempRow[$k+3];

                    $response  -> dataset[$k] -> data[] = $tempObj;

                }


            }


        
		
		
		
		//dd($finalpath, $filecontents);
		return 	$filecontents;	
		*/
	}
			
}












?>