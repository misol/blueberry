<?php
/* Copyright (C) Min-Soo Kim <misol@snu.ac.kr> */

/**
 * @class  blueberryView
 * @author Min-Soo Kim <misol@snu.ac.kr>
 * @brief  Blueberry module View class
 **/
class blueberryView extends blueberry
{
	public function __construct()
	{
		parent::__construct();
	}
	
	/**
	 * @brief initialization
	 * blueberry module can be used in either normal mode or admin mode.
	 **/
	public function init()
	{
		$this->list_count = $this->module_info->list_count ?? 20;
		$this->search_list_count = $this->module_info->search_list_count ?? 20;
		$this->page_count = $this->module_info->page_count ?? 10;
		$oSecurity = new Security();
		$oSecurity->encodeHTML('data_srl', 'comment_srl', 'vid', 'mid', 'page', 'category', 'search_target', 'search_keyword', 'sort_index', 'order_type', 'trackback_srl');
		
		// set template html path
		$templatePath = sprintf('%sskins/%s', $this->module_path, $this->module_info->skin ?: 'default');
		$this->setTemplatePath($templatePath);
		
		Context::set('blueberry_module_info', ModuleModel::getModuleInfoXml('blueberry'));
		Context::loadLang('./modules/document/lang');
		Context::loadLang('./modules/comment/lang');
	}
	

	/**
	 * @brief display blueberry contents
	 **/
	public function dispBlueberryContent()
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
		
		// remove a search option that is not public in member config
		$memberConfig = ModuleModel::getModuleConfig('member');
		foreach($memberConfig->signupForm as $signupFormElement)
		{
			if(in_array($signupFormElement->title, $search_option))
			{
				if($signupFormElement->isPublic == 'N')
				{
					unset($search_option[$signupFormElement->name]);
				}
			}
		}
		Context::set('search_option', $search_option);
		
		$data_srl = (intval(Context::get('data_srl')) > 0)? intval(Context::get('data_srl')) : 0;
		$oData = blueberryModel::getData($data_srl);
		if($oData->isExists()) {
			// update the document view count (if the document is not secret)
			if($oData->isAccessible())
			{
				$oData->updateReadedCount();
				Context::setCanonicalURL($oData->getPermanentUrl());
				
				$seo_title = config('seo.document_title') ?: '$SITE_TITLE - $DOCUMENT_TITLE';
				$seo_title = Context::replaceUserLang($seo_title);
				Context::setBrowserTitle($seo_title, array(
					'site_title' => Context::getSiteTitle(),
					'site_subtitle' => Context::getSiteSubtitle(),
					'subpage_title' => $this->module_info->browser_title,
					'document_title' => $oData->getTitle(),
					'page' => Context::get('page') ?: 1,
				));
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
	public function dispBlueberryList() {
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
		
		$owner_id = strval(Context::get('owner_id'));
		if(!$owner_id) {
			$owner_id = $logged_info->user_id;
		}
		if(!strval(Context::get('owner_id'))) {
			Context::set('owner_id', $logged_info->user_id);
		}
		
		if(!$owner_id || $owner_id !== $logged_info->user_id) {
			throw new Rhymix\Framework\Exceptions\InvalidRequest;
		}

		$owner_info = memberModel::getMemberInfoByUserID($owner_id);
		$args = new stdClass();
		$args->member_srl = $owner_info->member_srl;
		$args->page = intval(Context::get('page')) ?: null;
		$args->list_count = $this->list_count;
		$args->page_count = $this->page_count;
		
		// setup the sort index and order index
		$args->sort_index = Context::get('sort_index');
		$args->order_type = Context::get('order_type');
		if(!in_array($args->sort_index, $this->order_target))
		{
			$args->sort_index = $this->module_info->order_target?$this->module_info->order_target:'list_order';
		}
		if(!in_array($args->order_type, array('asc','desc')))
		{
			$args->order_type = $this->module_info->order_type?$this->module_info->order_type:'asc';
		}

		// get the search target and keyword
		if ($this->grant->list && $this->grant->view_data)
		{
			$args->search_target = trim(Context::get('search_target'));
		}
		
		if(!in_array($args->search_target, parent::$search_option))
		{
			$args->search_target = '';
		} else {
			switch($args->search_target)
			{
				case 'title' :
					$args->search_title_keyword = trim(Context::get('search_keyword'));
					break;
				case 'content' :
					$args->search_content_keyword = trim(Context::get('search_keyword'));
					break;
				case 'tag' :
					$args->search_tag_keyword = trim(Context::get('search_keyword'));
				case 'title_content' :
					$args->search_title_keyword = trim(Context::get('search_keyword'));
					$args->search_content_keyword = trim(Context::get('search_keyword'));
					break;
				case 'dose_dose_unit' :
					$args->search_dose = (floatval(Context::get('search_dose')) > 0)? floatval(Context::get('search_dose')) : 0;
					$args->search_dose_start = $args->search_dose * 0.999;
					$args->search_dose_end = $args->search_dose * 1.001;
					$args->search_dose_unit = trim(Context::get('search_dose_unit'));
					if(!isset(lang('blueberry.blueberry_dose_units')[$args->search_dose_unit])) {
						$args->search_dose_unit = 'mg';
					}
					break;
				case 'dosing_route' :
					$args->search_dose_route = trim(Context::get('search_dose_route'));
					if(!isset(blueberry::$available_routes[$args->search_dose_route])) {
						$args->search_dose_route = 'IVeBo';
					}
					break;
				case 'regdate': 
					$args->search_regdate_start = (preg_match("[0-9]{4}\-[0-9]{2}\-[0-9]{2}", trim(Context::get('search_start_day'))))? date("Ymd", strtotime(trim(Context::get('search_start_day')))): null;
					$args->search_regdate_end = (preg_match("[0-9]{4}\-[0-9]{2}\-[0-9]{2}", trim(Context::get('search_end_day'))))? date("Ymd", strtotime(trim(Context::get('search_end_day')))): null;
				case 'last_update':
					$args->search_last_update_start = (preg_match("[0-9]{4}\-[0-9]{2}\-[0-9]{2}", trim(Context::get('search_start_day'))))? date("Ymd", strtotime(trim(Context::get('search_start_day')))): null;
					$args->search_last_update_end = (preg_match("[0-9]{4}\-[0-9]{2}\-[0-9]{2}", trim(Context::get('search_end_day'))))? date("Ymd", strtotime(trim(Context::get('search_end_day')))): null;
					break;
				default:
					$args->search_target = null;
			}
			
			
			

		}
		// setup the list count to be serach list count, if the category or search keyword has been set
		if($args->category_srl ?? null || $args->search_keyword ?? null)
		{
			$args->list_count = $this->search_list_count;
		}
		
		$output = blueberryModel::getInVivoDataByMemberSrl($args, $columnList = array());
		Context::set('data_list', $output->data);
		Context::set('total_count', $output->total_count);
		Context::set('total_page', $output->total_page);
		Context::set('page', $output->page);
		Context::set('page_navigation', $output->page_navigation);

	}
	
	
	public function dispBlueberryNCA()
	{
		// check grant
		if(!$this->grant->add_data)
		{
			throw new Rhymix\Framework\Exceptions\NotPermitted;
		}
		$oData = blueberryModel::getData(intval(Context::get('data_srl')));
		Context::set('oData', $oData);
		
		// setup the tmeplate file
		$this->setTemplateFile('nca');
	}
	
	public function dispBlueberryDeleteNCA()
	{
		// check grant
		if(!$this->grant->add_data)
		{
			throw new Rhymix\Framework\Exceptions\NotPermitted;
		}
		$oData = blueberryModel::getData(intval(Context::get('data_srl')));
		Context::set('oData', $oData);
		
		// setup the tmeplate file
		$this->setTemplateFile('delete_form');
	}

}