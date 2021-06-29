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
		
		$oData = blueberryModel::getData(intval(Context::get('data_srl')));
		if($oData->isExists()) {
			// update the document view count (if the document is not secret)
			if($oData->isAccessible())
			{
				$oData->updateReadedCount();
			}
			// disappear the document if it is secret
			else
			{
				$oData->add('content',lang('thisissecret'));
			}
		}
		else {
			$oData = blueberryModel::getData(0);
		}

		Context::set('oData', $oData);
		
		/**
		 * display the category list, and then setup the category list on context
		 **/
		$this->dispBlueberryList();
		
		$oSecurity = new Security();
		$oSecurity->encodeHTML('data_list.', 'data_list.childs.');
		// setup the tmeplate file
		$this->setTemplateFile('list');
	}
	

	/**
	 * @brief display the category list
	 **/
	function dispBlueberryList()
	{
		// check the grant
		if(!$this->grant->list)
		{
			Context::set('data_list', array());
			Context::set('total_count', 0);
			Context::set('total_page', 1);
			Context::set('page', 1);
			Context::set('page_navigation', new PageHandler(0,0,1,10));
			return;
		}
		$logged_info = Context::get('logged_info');
		
		if(!Context::get('is_logged'))
		{
			throw new Rhymix\Framework\Exceptions\NotPermitted('msg_not_permitted');
		}
		
		if(!strval(Context::get('owner_id'))) {
			Context::set('owner_id', $logged_info->user_id);
		}
		$owner_id = strval(Context::get('owner_id'));
		if(!$owner_id) {
			$owner_id = $logged_info->user_id;
		}
		$owner_id = strval(Context::get('owner_id'));
		
		if(!$owner_id || $owner_id !== $logged_info->user_id) {
			throw new Rhymix\Framework\Exceptions\InvalidRequest;
		}

		$owner_info = memberModel::getMemberInfoByUserID($owner_id);
		$args = new stdClass();
		$args->member_srl = $owner_info->member_srl;
		Context::set('data_list', blueberryModel::getInVivoDataByMemberSrl($args, $columnList = array()));

	}
	
	
	function dispBlueberryNCA()
	{
		$oData = blueberryModel::getData(intval(Context::get('data_srl')));
		Context::set('oData', $oData);
		
		// setup the tmeplate file
		$this->setTemplateFile('nca');
	}
}