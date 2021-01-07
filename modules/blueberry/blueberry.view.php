<?php
/* Copyright (C) Min-Soo Kim <misol@snu.ac.kr> */

/**
 * @class  blueberryView
 * @author Min-Soo Kim <misol@snu.ac.kr>
 * @brief  Blueberry module View class
 **/
class blueberryView extends blueberry
{
	
	/**
	 * @brief initialization
	 * blueberry module can be used in either normal mode or admin mode.
	 **/
	function init()
	{
	}
	

	/**
	 * @brief display blueberry contents
	 **/
	function dispBlueberryContent()
	{
		/**
		 * check the access grant (all the grant has been set by the module object)
		 **/
		if(!$this->grant->access || !$this->grant->list)
		{
			return $this->dispBoardMessage('msg_not_permitted');
		}
		

		/**
		 * display the category list, and then setup the category list on context
		 **/
		$this->dispBlueberryList();
		
		
		// display the board content
		$this->dispBoardContentView();

	}
	

	/**
	 * @brief display the category list
	 **/
	function dispBlueberryList()
	{
		// check if the use_category option is enabled
		if($this->module_info->use_category=='Y')
		{
			// check the grant
			if(!$this->grant->list)
			{
				Context::set('category_list', array());
				return;
			}

			Context::set('category_list', DocumentModel::getCategoryList($this->module_srl));

			$oSecurity = new Security();
			$oSecurity->encodeHTML('category_list.', 'category_list.childs.');
		}
	}
	
}