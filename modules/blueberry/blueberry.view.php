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
		$templatePath = sprintf('%sskins/%s', $this->module_path, $this->module_info->skin ?: 'default');
		$this->setTemplatePath($templatePath);
		
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
		
		if(!$this->grant->access || !$this->grant->view_data)
		{
			throw new Rhymix\Framework\Exceptions\NotPermitted('msg_not_permitted');
		}
		
		
		/**
		 * display the search options on the screen
		 * add extra vaiables to the search options
		 **/
		// use search options on the template (the search options key has been declared, based on the language selected)
		$search_option = Array();
		foreach(parent::$search_option as $opt) $search_option[$opt] = lang($opt);
		Context::set('search_option', $search_option);
		
		$oData = blueberryModel::getData($data_srl);
		Context::set('oData', $oData);
		
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
		// check the grant
		if(!$this->grant->list)
		{
			Context::set('category_list', array());
			return;
		}

		Context::set('category_list', DocumentModel::getCategoryList($this->module_srl));

		$oSecurity = new Security();
		$oSecurity->encodeHTML('category_list.', 'category_list.childs.');
		// setup the tmeplate file
		$this->setTemplateFile('list');
	}
	
	
	function dispBlueberryNCA()
	{
		$oData = blueberryModel::getData($data_srl);
		Context::set('oData', $oData);
		
		// setup the tmeplate file
		$this->setTemplateFile('nca');
	}
}