<?php
/* Copyright (C) Min-Soo Kim <misol@snu.ac.kr> */

/**
 * @class  blueberryView
 * @author Min-Soo Kim <misol@snu.ac.kr>
 * @brief  Blueberry module Controller class
 **/
class blueberryController extends blueberry
{
	/**
	 * @brief initialization
	 **/
	public function init()
	{
	}
	
	/**
	 * @brief insert data
	 **/
	public function procBlueberryUpsertData()
	{
		
		// check grant
		if(!$this->grant->add_data)
		{
			throw new Rhymix\Framework\Exceptions\NotPermitted;
		}
		
		if(!checkCSRF())
		{
			throw new Rhymix\Framework\Exceptions\SecurityViolation;
		}
		
		$text_inputs = ['mid', 'act', 'title', 'content', 'time_unit', 'amount_unit', 'volume_unit', 'dose_unit', 'dosing_route', 'dose_repeat', 'integration_method'];
		$float_inputs = ['dose', 'last_dosing_time', 'tau'];
		
		// setup variables
		$obj = Context::getRequestVars();
		$obj->module_srl = $this->module_srl;
		
		
		// Remove disallowed Unicode symbols.
		foreach ($text_inputs as $key) {
			if (isset($obj->{$key}))
			{
				$obj->{$key} = trim(utf8_clean($obj->{$key}));
			}
		}
		if (isset($obj->data_srl))
		{
			$obj->data_srl = intval($obj->data_srl);
		}
		foreach ($float_inputs as $key) {
			if (isset($obj->{$key}))
			{
				$obj->{$key} = floatval($obj->{$key});
			}
		}
		
	}
	
	/**
	 * Insert the data
	 * @param object $obj
	 * @return object
	 */
	public function insertBlueberryData($obj) {
		if(!checkCSRF())
		{
			throw new Rhymix\Framework\Exceptions\SecurityViolation;
		}
		
		// Return error if content is too large.
		$document_length_limit = ($this->module_info->document_length_limit ?: 1024) * 1024;
		if (strlen(trim($obj->content)) > $document_length_limit && !$this->grant->manager)
		{
			throw new Rhymix\Framework\Exception('msg_content_too_long');
		}
		
		// begin transaction
		$oDB = DB::getInstance();
		$oDB->begin();
		
		$args = new stdClass;
		// sanitize variables
		$text_inputs = ['title', 'content', 'time_unit', 'amount_unit', 'volume_unit', 'dose_unit', 'dosing_route', 'dose_repeat', 'integration_method', 'extrapolation_method', 'password', 'user_id', 'user_name', 'nick_name', 'status', 'comment_status'];
		$float_inputs = ['dose', 'last_dosing_time', 'tau', 'time_min', 'time_max'];
		$int_inputs = ['data_srl', 'category_srl', 'group_count', 'repeat_count', 'member_srl', 'regdate', 'last_update', 'list_order', 'update_order'];
		$unset_inputs = ['regdate', 'last_update', 'ipaddress', 'allow_trackback', 'notify_message'];
		
		foreach ($text_inputs as $key) {
			if(isset($obj->{$key})) {
			
				$args->{$key} = trim(utf8_clean(strval($obj->{$key})));
			}
		}
		
		foreach ($float_inputs as $key) {
			if(isset($obj->{$key})) {
			
				$args->{$key} = floatval($obj->{$key});
			}
		}
		
		foreach ($int_inputs as $key) {
			if(isset($obj->{$key})) {
			
				$args->{$key} = intval($obj->{$key});
			}
		}
		
		foreach ($unset_inputs as $key) {
			if(isset($obj->{$key})) {
			
				unset($obj->{$key});
			}
		}
		
		if (!is_array($obj->TC_data['time']) || !is_array($obj->TC_data['concentration']) || !is_array($obj->TC_data['lnC']) || !is_array($obj->TC_data['time-concentration']))
		{
			throw new Rhymix\Framework\Exceptions\InvalidRequest;
		}
		
		$obj->TC_data['time'] = array_map(floatval, $obj->TC_data['time']);
		$obj->TC_data['concentration'] = array_map(floatval, $obj->TC_data['concentration']);
		$obj->TC_data['lnC'] = array_map(floatval, $obj->TC_data['lnC']);
		foreach ($obj->TC_data['time-concentration'] as $key => $val) {
			$obj->TC_data['time-concentration'][$key] = array_map(floatval, $val);
		}
		$args->time_concentration = serialize($obj->TC_data);
		
		
	}
}