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
	function init()
	{
	}
	
	/**
	 * @brief insert data
	 **/
	function procBlueberryUpsertData()
	{
		// check grant
		if(!$this->grant->add_data)
		{
			throw new Rhymix\Framework\Exceptions\NotPermitted;
		}
		
		$text_inputs = ['mid', 'act', 'title', 'content', 'time_unit', 'amount_unit', 'volume_unit', 'dose_unit', 'dose_route', 'dose_repeat', 'integration_method'];
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
		
		// Return error if content is too large.
		$document_length_limit = ($this->module_info->document_length_limit ?: 1024) * 1024;
		if (strlen($obj->content) > $document_length_limit && !$this->grant->manager)
		{
			throw new Rhymix\Framework\Exception('msg_content_too_long');
		}
	}
}