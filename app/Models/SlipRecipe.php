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
					->select(['material_id','required_mass','variance','slip_material_name','slip_material_grade'])
					->join('manu_slip_materials', 'manu_slip_materials.slip_material_id', '=','manu_slip_recipe_components.material_id')
					->where('slip_recipe_id','=',$recipeID)
					->get();
	
			return $query;
	
	}
	

	
	
}
