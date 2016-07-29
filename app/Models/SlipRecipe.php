<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;

use DB;

class SlipRecipe extends Model
{
    //
		
	function __construct($recipeID = null)
	{
	
		if($recipeID)
		{
		
			$this->buildSlipRecipeObj($recipeID);
		}
	
		return $this;
	}
	
	
	function buildSlipRecipeObj($recipeID){

		$temp = $this->getRecipeAttr($recipeID);
		$temp->components = $this->getRecipeComponents($recipeID);
		
		foreach($temp as $key=>$value)
		{
			$this->$key = $value;
		
		}
		
	}
	
	function getRecipeAttr ($recipeID){
			
			$query = DB::table('manu_slip_recipe')
					->select('*')
					->where('recipe_id','=',$recipeID)
					->get();
					
				
					return $query[0];
	
	}
	
	function editRecipeAttr ($recipe, $obj)
	{
		//process $obj
		
		//$update DB
	
	
	}
	
	function deleteRecipe ($recipeID)
	{
		$query = DB::table('manu_slip_recipe')
					->where('recipe_id','=',$recipeID)
					->delete();
		return;
	
	}
	
	
	function getRecipeComponents ($recipeID){
	
			$query = DB::table('manu_slip_recipe_components')
					->select(['material_id','required_mass','variance','slip_material_name','slip_material_grade','slip_material_category'])
					->join('manu_slip_materials', 'manu_slip_materials.slip_material_id', '=','manu_slip_recipe_components.material_id')
					->where('slip_recipe_id','=',$recipeID)
					->get();
	
			return $query;
	
	}
	
function getSlipRecipeList($params)
	{
	
		$query = DB::table('manu_slip_recipe')
				->select(['recipe_id','recipe_name']);
				
				
				if(isset($params['like']))
				{
					$query->where('recipe_id','Like',$params['like'].'%');
			
				}
				
				$query = $query
				->take(20)
				->orderBy('recipe_id','desc')
				->get();
				
				
				
				if(isset($params['guarantee']))
				{
					$guarantee = DB::table('manu_slip_recipe')
					->select(['recipe_id'])
					->where('recipe_id','=',$params['guarantee'])
					->first();
					$query[] = $guarantee;
				}
				
				
				
			
		$temp = array();
		foreach($query as $obj)
		{
		$temp[] = array('id' => $obj->recipe_id, 'text'=>$obj->recipe_name);
		
		}
		
		return $temp;
	
	}
	
	
}
