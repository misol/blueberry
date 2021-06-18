<?php
/* Copyright (C) Min-Soo Kim <misol@snu.ac.kr> */

/**
 * @class  blueberryView
 * @author Min-Soo Kim <misol@snu.ac.kr>
 * @brief  Blueberry module View class
 **/
class blueberryView extends blueberry
{
	function __construct()
	{
		parent::__construct();
	}
	
	/**
	 * @brief initialization
	 * blueberry module can be used in either normal mode or admin mode.
	 **/
	function init()
	{
		$oSecurity = new Security();
		$oSecurity->encodeHTML('data_srl', 'comment_srl', 'vid', 'mid', 'page', 'category', 'search_target', 'search_keyword', 'sort_index', 'order_type', 'trackback_srl');
		
		// set template html path
		$this->setTemplatePath($this->module_path . 'tpl');
		
		Context::loadLang('./modules/document/lang');
		Context::loadLang('./modules/comment/lang');
	}
	

	/**
	 * @brief display blueberry contents
	 **/
	function dispBlueberryContent()
	{
		/**
		 * check the access grant (all the grant has been set by the module object)
		 **/
		
		print_r($this->grant);
		if(!$this->grant->access || !$this->grant->view_data)
		{
			throw new Rhymix\Framework\Exceptions\NotPermitted('msg_not_permitted');
		}
		
		if (Context::get('data_srl')) {
			print(Context::get('data_srl'));
		}
		/**
		 * display the category list, and then setup the category list on context
		 **/
		$this->dispBlueberryList();
		

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