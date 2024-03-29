<?php
/* Copyright (C) Min-Soo Kim <misol@snu.ac.kr> */

/**
 * @class  blueberryAdminController
 * @author Min-Soo Kim <misol@snu.ac.kr>
 * @brief  Blueberry module item class
 */
class blueberryItem extends BaseObject
{
	/**
	 * Document number
	 * @var int
	 */
	private $data_srl = 0;
	private $reload = false;
	/**
	 * grant
	 * @var bool
	 */
	private $grant_cache = null;
	
	private $C0_cache = null;
	private $time_concentration_cache = null;

	/**
	 * Get data from database, and set the value to blueberryItem object
	 * @param int data_srl: target data serial number
	 * @param bool reload: set true to force reloading
	 * @return void
	 */
	public function __construct($data_srl = 0, $reload = false)
	{
		$this->setDataSrl($data_srl, $reload);
	}

	public function setDataSrl($data_srl, $reload = false)
	{
		if(!$data_srl) return;
		$this->data_srl = $data_srl;
		$this->reload = $reload;
		$this->_loadFromDB();
	}


	/**
	 * Get data from database, and set the value to blueberryItem object
	 * @param 
	 * @return void
	 */
	private function _loadFromDB()
	{
		if(!$this->isExists())
		{
			return;
		}
		
		$blueberry_item = false;
		$columnList = array();
		$reload_counts = false;
		
		if ($this->reload === true)
		{
			$reload_counts = true;
		}

		// cache control
		$cache_key = 'blueberry_item:' . getNumberingPath($this->data_srl) . $this->data_srl;
		$blueberry_item = Rhymix\Framework\Cache::get($cache_key);

		if(!$blueberry_item || $reload_counts)
		{
			$args = new stdClass();
			$args->data_srl = $this->data_srl;
			$output = executeQuery('blueberry.getData', $args, $columnList);
			$blueberry_item = $output->data;
			if($blueberry_item)
			{
				Rhymix\Framework\Cache::set($cache_key, $blueberry_item);
			}
		}

		$this->setAttribute($blueberry_item);
	}

	public function setAttribute($attribute)
	{
		if(!is_object($attribute) || !intval($attribute->data_srl))
		{
			$this->data_srl = null;
			return;
		}
		
		$this->data_srl = intval($attribute->data_srl);
		$this->adds($attribute);
		
		
	}

	public function isExists()
	{
		return (bool) ($this->data_srl);
	}


	public function isGranted()
	{
		if(!$this->isExists())
		{
			return false;
		}
		
		if ($this->grant_cache !== null)
		{
			return $this->grant_cache;
		}
		
		$logged_info = Context::get('logged_info');
		if (!$logged_info->member_srl)
		{
			return $this->grant_cache = false;
		}
		if ($logged_info->is_admin == 'Y')
		{
			return $this->setGrant();
		}
		
		if ($this->getMemberSrl() && $this->getMemberSrl() == $logged_info->member_srl)
		{
			return $this->setGrant();
		}
		
		return $this->grant_cache = false;
	}
	
	private function setGrant()
	{
		return $this->grant_cache = true;
	}
	
	public function isAccessible()
	{
		if(!$this->isExists())
		{
			return false;
		}
		if (isset($_SESSION['data_accessible'][$this->data_srl]) && $_SESSION['data_accessible'][$this->data_srl] === $this->get('last_update'))
		{
			return true;
		}
		
		if ($this->get('status') === blueberry::$statusList['public'])
		{
			$this->setAccessible();
			return true;
		}
		
		if ($this->isGranted())
		{
			$this->setAccessible();
			return true;
		}
		
		return false;
	}


	private function setAccessible()
	{
		if(Context::getSessionStatus())
		{
			$_SESSION['data_accessible'][$this->data_srl] = $this->get('last_update');
		}
	}

	public function isEditable()
	{
		return (bool) (!$this->get('member_srl') || $this->isGranted());
	}
	
	public function doCart()
	{
		if(!$this->isExists())
		{
			return false;
		}
		
		$this->isCarted() ? $this->removeCart() : $this->addCart();
	}

	public function addCart()
	{
		$_SESSION['data_management'][$this->data_srl] = true;
	}

	public function removeCart()
	{
		unset($_SESSION['data_management'][$this->data_srl]);
	}

	public function isCarted()
	{
		return isset($_SESSION['data_management'][$this->data_srl]);
	}

	public function getIpAddress()
	{
		if($this->isGranted())
		{
			return $this->get('ipaddress');
		}
		
		return '*' . strstr($this->get('ipaddress'), '.');
	}

	public function getDataSrl() {
		return $this->data_srl;
	}
	public function getPermanentUrl() {
		return getFullUrl('', 'mid', $this->getDataMid(), 'owner_id', $this->getUserID(), 'data_srl', $this->data_srl);
	}

	/**
	 * Returns the document's mid in order to construct SEO friendly URLs
	 * @return string
	 */
	public function getDataMid()
	{
		return ModuleModel::getMidByModuleSrl($this->get('module_srl'));
	}
	
	private function toPrecision($number, $precision) {
		$number = floatval($number);
		$precision = intval($precision);
		if ($number == 0) return 0;
		if ($precision <= 0) return $number;
		$exponent = floor(log10(abs($number)) + 1);
		$significand =
			round(
				($number / pow(10, $exponent))
				* pow(10, $precision)
			)
			/ pow(10, $precision);
		$results = strval($significand * pow(10, $exponent));
		
		if(substr(strval($results), 0, 2) === '0.') {
			$precision = $precision + 1;
		}
		
		if(strlen(str_replace('.', '' ,$results)) < $precision) {
			$diff_len = $precision - strlen(str_replace('.', '' ,$results));

			if(strpos($results, '.')) {
				$i = 0;
				while ($i < $diff_len) {
					$results .= '0';
					$i++;
				}
			} else {
				$results .= '.0';
				if($diff_len > 1) {
					$i = 1;
					while ($i < $diff_len) {
						$results .= '0';
						$i++;
					}
				}
			}
		}
		return $results;
	}
	
	/**
	 * Return dose
	 * @param bool include_unit
	 * @return string | float
	 */
	public function getDose($include_unit = true, $precision = 5)
	{
		if(!$this->isExists()) return;
		$dose = floatval($this->get('dose'));
		$dose_unit = $this->getDoseUnit();
		
		if ($include_unit) {
			return sprintf("%s %s", $this->toPrecision($dose, intval($precision)), $dose_unit);
		} else {
			return $dose;
		}
		
		return;
	}

	/**
	 * Return dose
	 * @param bool include_unit
	 * @return string | float
	 */
	public function getDoseUnit($str_replace = true)
	{
		if(!$this->isExists()) return;
		
		if ($str_replace === false) return strval($this->get('dose_unit'));
		
		return str_replace('ug','μg', strval($this->get('dose_unit')));
	}

	public function getMolecularWeight() {
		if(!$this->isExists()) return;
		if(floatval($this->get('molecular_weight')) > 0) {
			return floatval($this->get('molecular_weight'));
		}
		return false;
	}
	
	
	private function getUnitMatchedDose() {
		if(!$this->isExists()) return;
		
		$dose = $this->getDose(false, -1);
		$dose_unit = $this->getDoseUnit();
		$dose = $this->unitConversion($dose, "amount", $dose_unit, $this->getAmountUnit());
		return $dose;
	}
	
	
	public function getC0($precision = 5) {
		if(!$this->isExists()) return;
		
		if($this->C0_cache !== null) {
			return $this->toPrecision($this->C0_cache, $precision);
		}
		
		$time_concentration = $this->getTimeConcentrationArray();
		
		$C0 = 0;
		$dose_route = $this->getAdministrationRoute();
		if(in_array($dose_route, array('extravenous', 'iv_infusion'))) {
			if($this->isMultipleDose()) {
				$tau_time = [];
				$tau_conc = [];
				foreach ($time_concentration->time_concentration as $val) {
					if($this->getLastDosingTime(-1) <= $val[0]) {
						$tau_time[] = $val[0] - $this->getLastDosingTime(-1);
						$tau_conc[] = $val[1];
					}
				}
				if(abs($tau_time[0] - 0) < 0.000001) {
					$C0 = $tau_conc[0];
				} else {
					$C0 = min($tau_conc);
				}
			} else {
				$C0 = 0;
			}
		} elseif ($dose_route == "iv_bolus") {
			if($this->isMultipleDose()) {
				$tau_time = [];
				$tau_conc = [];
				$tau_lnC = [];
				$i = 0;
				foreach ($time_concentration->time_concentration as $val) {
					if($this->getLastDosingTime(-1) <= $val[0] && ($this->getLastDosingTime(-1) + $this->getTau(-1)) >= $val[0]) {
						$tau_time[] = $val[0] - $this->getLastDosingTime(-1);
						$tau_conc[] = $val[1];
						$tau_lnC[] = $time_concentration->lnC[$i];
					}
					$i++;
				}
				
				$time_concentration->time = $tau_time;
				$time_concentration->concentration = $tau_conc;
				$time_concentration->lnC = $tau_lnC;
			}
			
			$time_slice = array_slice($time_concentration->time, 0, 2);
			$conc_slice = array_slice($time_concentration->concentration, 0, 2);
			$lnC_slice = array_slice($time_concentration->lnC, 0, 2);
			
			if(abs($time_slice[0]) < 0.000001) {
				if($lnC_slice[0] !== 'NA') {
					$C0 = $conc_slice[0];
				} else {
					$C0 = 0;
				}
			} else {
				if($lnC_slice[0] === 'NA' || $lnC_slice[1] === 'NA') {
					$C0 = ($lnC_slice[0] === 'NA')? $lnC_slice[1]:$lnC_slice[0];
					if($C0 === 'NA') {
						$C0 = 0;
						foreach ($time_concentration->lnC as $lnC_item) {
							if ($lnC_item != "NA" && $C0 == 0) {
								$C0 = exp($lnC_item);
							}
						}
					} else {
						$C0 = exp($C0);
					}
				} else {
					$param = $this->linearRegression($lnC_slice, $time_slice);
					
					if($param['slope'] > 0) {
						$C0 = $conc_slice[0];
					} else {
						$C0 = exp($param['intercept']);
					}
					
				}
			}
			
		}
		$this->C0_cache = floatval($C0);
		return $this->toPrecision($this->C0_cache, $precision);
	}
	
	private function getAUCArray() {
		if(!$this->isExists()) return;
		
		static $results = null;
		if ($results !== null) return $results;
		
		$lambda_z = $this->getLambda(-1);
		$integration_method = $this->getInterpolationMethod();
		$sorted_T_C = $this->getTimeConcentrationArray()->time_concentration;
		$C0 = $this->getC0(-1);
		
		
		if($this->isMultipleDose()) {
			$tau_time_conc = array();
			$i = 0;
			foreach ($sorted_T_C as $val) {
				if($this->getLastDosingTime(-1) <= $val[0]) {
					$tau_time_conc[] = array($val[0] - $this->getLastDosingTime(-1), $val[1]);
				}
				$i++;
			}
			$sorted_T_C = $tau_time_conc;
		}
		
		if (abs($sorted_T_C[0][0]) > 0.000001) {
			$sorted_T_C = array_merge(array(array(0, $C0)), $sorted_T_C);
		}
		
		$i = 0;
		$AUC = $AUCinf = $AUCtau = $C_AUCtau_last = $T_AUCtau_last = 0;
		$time_before = $time_this = $conc_before = $conc_this = 0;
		foreach ($sorted_T_C as $time_conc) {
			if ($i != 0) {
				$time_before = floatval($sorted_T_C[$i-1][0]);
				$time_this = floatval($sorted_T_C[$i][0]);
				
				$conc_before = floatval($sorted_T_C[$i-1][1]);
				$conc_this = floatval($sorted_T_C[$i][1]);
				
				
				if ($integration_method == "log_trapezoidal_method" && ($conc_this > 0 && $conc_before > 0) && ($conc_this < $conc_before)) {
					$AUC += ($time_this - $time_before) * ($conc_this - $conc_before) / ( log($conc_this / $conc_before) );
				} else {
					$AUC += ($time_this - $time_before) * ($conc_this + $conc_before) / 2;
					if ($integration_method == "linear_trapezoidal_method_w_end_corr" && $i != 1 && $i < (count($sorted_T_C) - 1)) {
						$time_before_before = floatval($sorted_T_C[$i-2][0]);
						$time_after = floatval($sorted_T_C[$i+1][0]);
						
						$conc_before_before = floatval($sorted_T_C[$i-2][1]);
						$conc_after = floatval($sorted_T_C[$i+1][1]);
						
						$dfdt = $this->getExpFirstDeri_center(array($time_before_before, $time_before, $time_this, $time_after), array($conc_before_before, $conc_before, $conc_this, $conc_after));

						$AUC +=  ($dfdt[0] - $dfdt[1]) * pow(($time_before - $time_this), 2) / 12;
					}
				}
				if($this->isMultipleDose() && $lambda_z !== null && $time_this <= $this->getTau(-1)) {
					$AUCtau = $AUC;
					$C_AUCtau_last = $conc_this;
					$T_AUCtau_last = $time_this;
					
					/*
					if(isset($sorted_T_C[$i+1]) {
						$T_AUCtau_last_after = floatval($sorted_T_C[$i+1][0]);
						$C_AUCtau_last_after = floatval($sorted_T_C[$i+1][1]);
					}
					*/
				}
			}
			$i++;
		}
		
		$conc_last = $conc_this;
		
		if($lambda_z !== null) {
			$AUCinf += $AUC + $conc_last/$lambda_z;
			$Extrapolation_portion = ($conc_last/$lambda_z)/$AUCinf;
			
			$AUCtau = $AUCtau + $C_AUCtau_last / $lambda_z * ( 1- exp(-1 * $lambda_z * ($this->getTau(-1) - $T_AUCtau_last)));
			$AUCtauinf = $AUCinf - $AUCtau;
		} else {
			$AUCinf = null;
			$Extrapolation_portion = null;
			
			$AUCtau = null;
			$AUCtauinf = null;
		}
		
		$results = array("AUC"=> $AUCinf, "AUClast"=> $AUC, "AUCtauinf" => $AUCtauinf, "AUCtau" => $AUCtau, "Extrapolation_portion"=> $Extrapolation_portion);
		return $results;
	}
	
	public function getAUC($precision = 5) {
		if(!$this->isExists()) return;
		
		if($this->getAUCArray()['AUC'] === null) {
			return null;
		}
		return $this->toPrecision($this->getAUCArray()['AUC'], $precision);
	}
	
	public function getAUClast($precision = 5) {
		if(!$this->isExists()) return;
		
		if($this->getAUCArray()['AUClast'] === null) {
			return null;
		}
		return $this->toPrecision($this->getAUCArray()['AUClast'], $precision);
	}
	
	public function getAUCtau($precision = 5) {
		if(!$this->isExists()) return;
		if(!$this->isMultipleDose()) return;
		
		if($this->getAUCArray()['AUCtau'] === null) {
			return null;
		}
		return $this->toPrecision($this->getAUCArray()['AUCtau'], $precision);
	}
	
	public function getAUCtauinf($precision = 5) {
		if(!$this->isExists()) return;
		
		if($this->getAUCArray()['AUCtauinf'] === null) {
			return null;
		}
		return $this->toPrecision($this->getAUCArray()['AUCtauinf'], $precision);
	}
	public function getAUCExtPortion($precision = 5) {
		if(!$this->isExists()) return;
		
		if($this->getAUCArray()['Extrapolation_portion'] === null) {
			return null;
		}
		return $this->toPrecision($this->getAUCArray()['Extrapolation_portion'], $precision);
	}
	public function getAUCExtPortionPercent($precision = 5) {
		if(!$this->isExists()) return;
		if($this->getAUCArray()['Extrapolation_portion'] === null) {
			return null;
		}
		return $this->toPrecision($this->getAUCArray()['Extrapolation_portion'] * 100, $precision);
	}
	
	public function getCL($precision = 5) {
		$dose = $this->getUnitMatchedDose();
		if($this->isMultipleDose()) {
			$AUC = $this->getAUCtau(-1);
		} else {
			$AUC = $this->getAUC(-1);
		}
		
		if(!$AUC || !$dose) {
			return null;
		}
		return $this->toPrecision($dose / $AUC, $precision);
	}
	
	
	public function getVss($precision = 5) {
		$CL = $this->getCL(-1);
		$MRT = $this->getMRT(-1);
		if($CL === null || $MRT === null) {
			return null;
		}
		return $this->toPrecision($MRT * $CL, $precision);
	}
	
	public function getVz($precision = 5) {
		$dose = $this->getUnitMatchedDose();
		if($this->isMultipleDose()) {
			$AUC = $this->getAUCtau(-1);
		} else {
			$AUC = $this->getAUC(-1);
		}
		$lambda = $this->getLambda(-1);
		
		if(!$dose || !$AUC || !$lambda) {
			return null;
		}
		
		return $this->toPrecision($dose / ($lambda * $AUC), $precision);
	}
	
	private function getTCmax() {
		if(!$this->isExists()) return;
		
		static $results = null;
		if ($results !== null) return $results;
		
		$sorted_T_C = $this->getTimeConcentrationArray()->time_concentration;
		
		
		if($this->isMultipleDose()) {
			$tau_time_conc = array();
			$i = 0;
			foreach ($sorted_T_C as $val) {
				if($this->getLastDosingTime(-1) < $val[0]) {
					$tau_time_conc[] = array($val[0] - $this->getLastDosingTime(-1), $val[1]);
				}
				$i++;
			}
			$sorted_T_C = $tau_time_conc;
		}
		$time = 0;
		$conc = 0;
		foreach ($sorted_T_C as $val) {
			if($conc < $val[1]) {
				$time = $val[0];
				if($this->isMultipleDose() && $this->getLastDosingTime(-1) > 0) {
					$time = $time + $this->getLastDosingTime(-1);
				}
				$conc = $val[1];
			}
		}
		$results = array("time"=> $time, "conc"=> $conc);
		
		return $results;
	}
	
	
	public function getCmax($precision = 5) {
		if(!$this->isExists()) return;
		$TCmax = $this->getTCmax();
		
		return $this->toPrecision($TCmax['conc'], $precision);
	}
	
	public function getTmax($precision = 5) {
		if(!$this->isExists()) return;
		$TCmax = $this->getTCmax();
		
		return $this->toPrecision($TCmax['time'], $precision);
	}
	
	public function getCss($precision = 5) {
		if(!$this->isExists()) return;
		if(!$this->isMultipleDose()) return;
		
		$AUCtau = $this->getAUCtau(-1);
		$tau = $this->getTau(-1);
		
		if($tau <= 0) return;
		
		if($AUCtau === null) {
			return null;
		}
		
		return $this->toPrecision($AUCtau / $tau, $precision);
	}
	
	private function getAUMCArray() {
		if(!$this->isExists()) return;
		
		static $results = null;
		if ($results !== null) return $results;
		
		$lambda_z = $this->getLambda(-1);
		$integration_method = $this->getInterpolationMethod();
		$sorted_T_C = $this->getTimeConcentrationArray()->time_concentration;
		$C0 = $this->getC0(-1);
		
		
		if($this->isMultipleDose()) {
			$tau_time_conc = array();
			$i = 0;
			foreach ($sorted_T_C as $val) {
				if($this->getLastDosingTime(-1) < $val[0]) {
					$tau_time_conc[] = array($val[0] - $this->getLastDosingTime(-1), $val[1]);
				}
				$i++;
			}
			$sorted_T_C = $tau_time_conc;
		}
		
		if (abs($sorted_T_C[0][0]) > 0.000001) {
			$sorted_T_C = array_merge(array(array(0, $C0)), $sorted_T_C);
		}
		
		$i = 0;
		$AUMC = $AUMCinf = $AUMCtau = 0;
		$time_before = $time_this = $conc_before = $conc_this = 0;
		
		
		foreach ($sorted_T_C as $time_conc) {
			if ($i != 0) {
				$time_before = floatval($sorted_T_C[$i-1][0]);
				$time_this = floatval($sorted_T_C[$i][0]);
				
				$conc_before = floatval($sorted_T_C[$i-1][1]);
				$conc_this = floatval($sorted_T_C[$i][1]);
				
				
				if ($integration_method == "log_trapezoidal_method" && ($conc_this > 0 && $conc_before > 0) && $conc_this < $conc_before && log($conc_this / $conc_before) !== 0) {
					$AUMC += ((($time_this - $time_before) / (log($conc_this / $conc_before))) * ($time_this * $conc_this - $time_before * $conc_before)) - (pow(($time_this - $time_before) / (log($conc_this / $conc_before)), 2) * ($conc_this - $conc_before));
				} else {
					$AUMC += ($time_this - $time_before) * ($time_this * $conc_this + $time_before * $conc_before) / 2;
					if ($integration_method == "linear_trapezoidal_method_w_end_corr" && $i != 1 && $i < (count($sorted_T_C) - 1)) {
						$time_before_before = floatval($sorted_T_C[$i-2][0]);
						$time_after = floatval($sorted_T_C[$i+1][0]);
						
						$conc_before_before = floatval($sorted_T_C[$i-2][1]);
						$conc_after = floatval($sorted_T_C[$i+1][1]);
						
						$dfdt = $this->getExpFirstDeri_center(array($time_before_before, $time_before, $time_this, $time_after), array($time_before_before * $conc_before_before, $time_before * $conc_before, $time_this * $conc_this, $time_after * $conc_after));
						
						$AUMC +=  ($dfdt[0] - $dfdt[1]) * pow(($time_before - $time_this), 2) / 12;
					}
				}
				
				if($this->isMultipleDose() && $lambda_z !== null && $time_this <= $this->getTau(-1)) {
					$AUMCtau = $AUMC;
					$C_AUCtau_last = $conc_this;
					$T_AUCtau_last = $time_this;
				}
			}
			$i++;
		}
		
		$time_last = $time_this;
		$conc_last = $conc_this;
		
		
		
		if($lambda_z !== null) {
			$AUMCinf = $AUMC + $conc_last / pow($lambda_z, 2) + $time_last * $conc_last / $lambda_z;
			$Extrapolation_portion = ($AUMCinf - $AUMC) / $AUMCinf;
			
			$AUMCtau = $AUMCtau + $C_AUCtau_last * (1/(-1 * $lambda_z) * ($this->getTau(-1) * exp(-1 * $lambda_z * ($this->getTau(-1) - $T_AUCtau_last)) - $T_AUCtau_last) - 1/pow($lambda_z,2) * (exp(-1 * $lambda_z * ($this->getTau(-1) - $T_AUCtau_last)) - 1));
		} else {
			$AUMCinf = null;
			$Extrapolation_portion = null;
			
			$AUMCtau = null;
		}
		
		$results = array("AUMC"=> $AUMCinf, "AUMClast"=> $AUMC, "AUMCtau" => $AUMCtau, "Extrapolation_portion"=> $Extrapolation_portion);
		return $results;
	}
	
	
	public function getAUMC($precision = 5) {
		if(!$this->isExists()) return;
		
		if($this->getAUMCArray()['AUMC'] === null) {
			return null;
		}
		return $this->toPrecision($this->getAUMCArray()['AUMC'], $precision);
	}
	public function getAUMClast($precision = 5) {
		if(!$this->isExists()) return;
		
		if($this->getAUMCArray()['AUMClast'] === null) {
			return null;
		}
		return $this->toPrecision($this->getAUMCArray()['AUMClast'], $precision);
	}
	public function getAUMCtau($precision = 5) {
		if(!$this->isExists()) return;
		if(!$this->isMultipleDose()) return;
		
		if($this->getAUMCArray()['AUMCtau'] === null) {
			return null;
		}
		
		return $this->toPrecision($this->getAUMCArray()['AUMCtau'], $precision);
	}
	public function getAUMCExtPortion($precision = 5) {
		if(!$this->isExists()) return;
		
		if($this->getAUMCArray()['Extrapolation_portion'] === null) {
			return null;
		}
		return $this->toPrecision($this->getAUMCArray()['Extrapolation_portion'], $precision);
	}
	public function getAUMCExtPortionPercent($precision = 5) {
		if(!$this->isExists()) return;
		
		if($this->getAUMCArray()['Extrapolation_portion'] === null) {
			return null;
		}
		return $this->toPrecision($this->getAUMCArray()['Extrapolation_portion'] * 100, $precision);
	}
	
	public function getAccumulationIndex($precision = 5) {
		if(!$this->isExists()) return;
		if(!$this->isMultipleDose()) return;
		
		$lambda = $this->getLambda(-1);
		$tau = $this->getTau(-1);
		
		if(!$lambda || !$tau) {
			return null;
		}
		return $this->toPrecision(1/(1-exp(-1*$lambda*$tau)), $precision);
	}
	
	public function getMRT($precision = 5) {
		if(!$this->isExists()) return;
		
		$duration = 0;
		$AUC = 0;
		$AUMC = 0;
		
		if($this->isMultipleDose()) {
			$AUC = $this->getAUCtau(-1);
			$AUMC = $this->getAUMCtau(-1) + $this->getTau(-1) *  $this->getAUCtauinf(-1);
		} else {
			$AUC = $this->getAUC(-1);
			$AUMC = $this->getAUMC(-1);
		}
		
		if($this->getAdministrationRoute() === 'iv_infusion') {
			$duration = $this->getLengthofInfusion(-1);
			
		}
		
		if(!$AUC || !$AUMC) {
			return null;
		}
		
		return $this->toPrecision($AUMC/$AUC - $duration/2, $precision);
		
	}
	public function getMRTlast($precision = 5) {
		if(!$this->isExists()) return;
		if($this->isMultipleDose()) return;
		
		$duration = 0;
		$AUClast = $this->getAUClast(-1);
		$AUMClast = $this->getAUMClast(-1);
		
		if($this->getAdministrationRoute() === 'iv_infusion') {
			$duration = $this->getLengthofInfusion(-1);
		}
		
		if(!$AUClast || !$AUMClast) {
			return null;
		}
		
		return $this->toPrecision($AUMClast/$AUClast - $duration/2, $precision);
		
	}
	
	
	public function getLengthofInfusion($precision = 5) {
		if(!$this->isExists()) return;
		if($this->getAdministrationRoute() !== 'iv_infusion') return;
		
		$duration = floatval($this->get('duration_of_infusion'));
		
		return $this->toPrecision($duration, $precision);
		
	}
	
	
	public function isMultipleDose() {
		if(!$this->isExists()) return;
		
		$dose_repeat = trim(strval($this->get('dose_repeat')));
		
		if($dose_repeat === 'M') return true;
		
		return false;
	}
	
	/**
	 * Return dose route
	 * @param bool include_unit
	 * @return string | float
	 */
	public function getAdministrationRoute()
	{
		if(!$this->isExists()) return;
		$administration_route = trim(strval($this->get('administration_route')));

		
		if(isset(blueberry::$available_routes[$administration_route])) {
			return blueberry::$available_routes[$administration_route];
		}
		return blueberry::$available_routes['IVeBo'];
	}
	
	/**
	 * Return dose route
	 * @param bool include_unit
	 * @return string | float
	 */
	public function getLastDosingTime($precision = 5)
	{
		if(!$this->isExists()) return;
		$last_dosing = floatval($this->get('last_dosing'));

		if($last_dosing < 0) {
			return 0;
		}
		return $this->toPrecision($last_dosing, $precision);
	}
	public function getInterpolationMethod() {
		if(!$this->isExists()) return;
		$integration_method = trim(strval($this->get('integration_method')));

		
		if(isset(blueberry::$available_interpolation[$integration_method])) {
			return blueberry::$available_interpolation[$integration_method];
		}
		return blueberry::$available_interpolation['linear-trapezoidal'];
	}
	
	/**
	 * Return author's profile image
	 * @return string
	 */
	public function getProfileImage()
	{
		if(!$this->isExists() || $this->get('member_srl') <= 0) return;
		$profile_info = MemberModel::getProfileImage($this->get('member_srl'));
		if(!$profile_info) return;

		return $profile_info->src;
	}
	
	public function getHomepageUrl()
	{
		if(!$url = trim($this->get('homepage')))
		{
			return;
		}
		
		if(!preg_match('@^[a-z]+://@i', $url))
		{
			$url = 'http://' . $url;
		}
		
		return escape($url, false);
	}

	public function getMemberSrl()
	{
		return $this->get('member_srl');
	}

	public function getUserID()
	{
		return escape($this->get('user_id'), false);
	}

	public function getUserEmailAddress()
	{
		return escape($this->get('email_address'), false);
	}
	public function getUserName()
	{
		return escape($this->get('user_name'), false);
	}

	public function getNickName()
	{
		return escape($this->get('nick_name'), false);
	}

	public function getLastUpdater()
	{
		return escape($this->get('last_updater'), false);
	}

	public function getTitle($cut_size = 0, $tail = '...')
	{
		if(!$this->isExists())
		{
			return;
		}
		
		return $cut_size ? escape(cut_str($this->get('title'), $cut_size, $tail), false) : escape($this->get('title'), false);
	}
	
	
	public function getContent($cut_size = 0, $tail = '...')
	{
		if(!$this->isExists())
		{
			return;
		}
		
		return $cut_size ? escape(cut_str($this->get('content'), $cut_size, $tail), false) : escape($this->get('content'), false);
	}
	
	
	public function getTimeConcentrationArray()
	{
		if(!$this->isExists())
		{
			return;
		}
		if($this->time_concentration_cache !== null && is_countable($this->time_concentration_cache)) {
			return $this->time_concentration_cache;
		} else {
			$this->time_concentration_cache = null;
		}
		
		$time_concentration = null;
		$time_concentration = unserialize($this->get('time_concentration'));
		
		if(is_countable($time_concentration) && is_array($time_concentration) && (isset($time_concentration['time']) && isset($time_concentration['concentration'])) && (is_array($time_concentration['time']) && is_array($time_concentration['concentration'])) ) {
			$time_concentration_old = $time_concentration;
			$time_concentration = new stdClass();
			$time_concentration->time = $time_concentration_old['time'];
			$time_concentration->concentration = $time_concentration_old['concentration'];
			
		}
		
		if(!is_countable($time_concentration->time) || !is_countable($time_concentration->concentration)) return;
		
		$time_concentration->time = array_map( "floatval", $time_concentration->time);
		$time_concentration->concentration = array_map( "floatval", $time_concentration->concentration);
		
		
		if (count($time_concentration->time) !== count($time_concentration->concentration)) return;
		$new_time = array();
		$new_conc = array();
		$new_lnC = array();
		$new_TC = array();
		foreach ($time_concentration->concentration as $key=>$val) {
			if(floatval($val) > 0) {
				$new_time[] = $time_concentration->time[$key];
				$new_conc[] = $val;
				$new_TC[] = array($time_concentration->time[$key], $val);
				
				if($val > 0) {
					$new_lnC[] = log($val);
				} else {
					$new_lnC[] = 'NA';
				}
				
			}
		}
		$time_concentration->time = $new_time;
		$time_concentration->concentration = $new_conc;
		$time_concentration->lnC = $new_lnC;
		$time_concentration->time_concentration = $new_TC;
		
		// match the count;
		if (count($time_concentration->time) !== count($time_concentration->concentration)) return;
		
		// ascending sort (time)
		asort($time_concentration->time);
		$new_time = array();
		$new_conc = array();
		$new_lnC = array();
		$new_TC = array();
		foreach($time_concentration->time as $key => $val) {
			$new_time[] = $time_concentration->time[$key];
			$new_conc[] = $time_concentration->concentration[$key];
			$new_lnC[] = $time_concentration->lnC[$key];
			$new_TC[] = $time_concentration->time_concentration[$key];
		}
		$time_concentration = new stdClass();
		$time_concentration->time = $new_time;
		$time_concentration->concentration = $new_conc;
		$time_concentration->lnC = $new_lnC;
		$time_concentration->time_concentration = $new_TC;
		
		$this->time_concentration_cache = $time_concentration;
		return $this->time_concentration_cache;
	}
	
	public function getTau($precision = 5) {
		return $this->toPrecision($this->get('tau'), $precision);
	}
	
	public function getTimeUnit()
	{
		return $this->get('time_unit');
	}
	
	public function getAmountUnit($str_replace = true)
	{
		if(!$this->isExists())
		{
			return;
		}
		
		if ($str_replace === false) return $this->get('amount_unit');
		return str_replace('ug','μg', $this->get('amount_unit'));
	}
	
	public function getVolumeUnit($str_replace = true)
	{
		if(!$this->isExists())
		{
			return;
		}
		
		if ($str_replace === false) return $this->get('volume_unit');
		return str_replace('uL','μL', $this->get('volume_unit'));
	}
	
	public function getVoted()
	{
		return $this->getMyVote();
	}

	public function getMyVote()
	{
		if(!$this->isExists())
		{
			return false;
		}

		if(isset($_SESSION['voted_data'][$this->data_srl]))
		{
			return $_SESSION['voted_data'][$this->data_srl];
		}
		
		$logged_info = Context::get('logged_info');
		if(!$logged_info->member_srl)
		{
			$module_info = ModuleModel::getModuleInfoByModuleSrl($this->get('module_srl'));
			if($module_info->non_login_vote !== 'Y')
			{
				return false;
			}
		}

		$args = new stdClass;
		if($logged_info->member_srl)
		{
			$args->member_srl = $logged_info->member_srl;
		}
		else
		{
			$args->ipaddress = \RX_CLIENT_IP;
		}
		$args->data_srl = $this->data_srl;
		$output = executeQuery('blueberry.getDataVotedLog', $args);
		if(isset($output->data) && $output->data->point)
		{
			return $_SESSION['voted_data'][$this->data_srl] = $output->data->point;
		}
		return $_SESSION['voted_data'][$this->data_srl] = false;
	}
	
	
	public function getRegdate($format = 'Y.m.d H:i:s', $conversion = true)
	{
		return zdate($this->get('regdate'), $format, $conversion);
	}

	public function getRegdateTime()
	{
		return ztime($this->get('regdate'));
	}

	public function getRegdateGM($format = 'r')
	{
		return gmdate($format, $this->getRegdateTime());
	}

	public function getRegdateDT($format = 'c')
	{
		return Rhymix\Framework\DateTime::formatTimestampForCurrentUser($format, $this->getRegdateTime());
	}

	public function getUpdate($format = 'Y.m.d H:i:s', $conversion = true)
	{
		return zdate($this->get('last_update'), $format, $conversion);
	}

	public function getUpdateTime()
	{
		return ztime($this->get('last_update'));
	}

	public function getUpdateGM($format = 'r')
	{
		return gmdate($format, $this->getUpdateTime());
	}

	public function getUpdateDT($format = 'c')
	{
		return Rhymix\Framework\DateTime::formatTimestampForCurrentUser($format, $this->getUpdateTime());
	}
	
	
	/**
	 * Return the status code.
	 * 
	 * @return string
	 */
	public function getStatus()
	{
		$oBlueberry = getClass('blueberry');
		$status = $this->get('status');
		return $status ?: $oBlueberry->getDefaultStatus();
	}

	/**
	 * Return the status in human-readable text.
	 * 
	 * @return string
	 */
	public function getStatusText()
	{
		$status = $this->get('status');
		$statusList = lang('document.status_name_list');
		if ($status && isset($statusList[$status]))
		{
			return $statusList[$status];
		}
		else
		{
			return $statusList[$oBlueberry->getDefaultStatus()];
		}
	}
	
	/**
	 * get readed count
	 * @return void
	 */
	public function getReadedCount()
	{
		return $this->get('readed_count');
	}
	/**
	 * Update readed count
	 * @return void
	 */
	public function updateReadedCount()
	{
		if(blueberryController::updateReadedCount($this))
		{
			$readed_count = $this->get('readed_count');
			$this->add('readed_count', $readed_count+1);
		}
	}

	/*
	 * @ brief: Return the parameters of an optimized line with least square method.
	*/
	private function linearRegression($y, $x) {
		if(!is_countable($y) || !is_countable($x)) return;
		
		$lr = array();
		$m = count($x);
		$n = count($y);
		$sum_x = $sum_y = $sum_xy = $sum_xx = $sum_yy = 0;
		

		if ( $n < 1 || $m != $n ) {
			return;
		}
		
		for ($i = 0; $i < $n; $i++) {

			$sum_x += $x[$i];
			$sum_y += $y[$i];
			$sum_xy += ($x[$i]*$y[$i]);
			$sum_xx += ($x[$i]*$x[$i]);
			$sum_yy += ($y[$i]*$y[$i]);
		}

		$lr['slope'] = ($n * $sum_xy - $sum_x * $sum_y) / ($n*$sum_xx - $sum_x * $sum_x);
		$lr['intercept'] = ($sum_y - $lr['slope'] * $sum_x)/$n;
		$lr['r2'] = pow(($n*$sum_xy - $sum_x*$sum_y)/sqrt(($n*$sum_xx-$sum_x*$sum_x)*($n*$sum_yy-$sum_y*$sum_y)), 2);
		if($n > 2) {
			$lr['adjusted_r2'] = 1-((1-($lr['r2']))*($n-1)/($n-2));
		} else {
			$lr['adjusted_r2'] = $lr['r2'];
		}

		return $lr;
	}


	public function isKgNormalized() {
		static $kg_normalized = null;
		if($kg_normalized !== null) return $kg_normalized;
		
		$dose_unit = $this->getDoseUnit();
	
		if (strpos($dose_unit, '/') > 0) {
			$kg_normalized = true;
		} else {
			$kg_normalized = false;
		}
		return $kg_normalized;
	}

	private function unitConversion($value, $unit_type, $org_unit, $target_unit) {
		$unit_types = array('amount', 'time', '1/time', 'volume', 'concentration');
		if (!in_array($unit_type, $unit_types)) {
			return;
		}
		$org_unit = str_replace('μ', 'u', preg_replace(array("/^g/", "/^L/", "/^mol/", "/^IU/"), array("1g", "1L", "1mol", "1IU"), $org_unit));
		$target_unit = str_replace('μ', 'u', preg_replace(array("/^g/", "/^L/", "/^mol/", "/^IU/"), array("1g", "1L", "1mol", "1IU"), $target_unit));
		
		$SI_prefixes = array(
			'Y'=> 1e24,
			'Z'=> 1e21,
			'E'=> 1e18,
			'P'=> 1e15,
			'T'=> 1e12,
			'G'=> 1e9,
			'M'=> 1e6,
			'k'=> 1e3,
			'h'=> 1e2,
			'1'=> 1e0,
			'd'=> 1e-1,
			'c'=> 1e-2,
			'm'=> 1e-3,
			'u'=> 1e-6,
			'μ'=> 1e-6,
			'n'=> 1e-9,
			'p'=> 1e-12,
			'f'=> 1e-15,
			'a'=> 1e-18,
			'z'=> 1e-21,
			'y'=> 1e-24
		);
		
		$time_units_eq = array(
			's'=> 604800,
			'sec'=> 604800,
			'min'=> 10080,
			'h'=> 168,
			'hr'=> 168,
			'd'=> 7,
			'day'=> 7,
			'w'=> 1,
			'week'=> 1
		);
		
		if ($unit_type == 'time') {
			if (isset($time_units_eq[$org_unit]) && isset($time_units_eq[$target_unit])) {
				return $value * ($time_units_eq[$target_unit]/$time_units_eq[$org_unit]);
			}
		} elseif ($unit_type == '1/time') {
			if (isset($time_units_eq[str_replace("/","",$org_unit)]) && isset($time_units_eq[str_replace("/","",$target_unit)])) {
				return $value * ($time_units_eq[$org_unit]/$time_units_eq[$target_unit]);
			}
		} elseif ($unit_type == 'amount' || $unit_type == 'volume') {
			if( substr($org_unit, -1) !== substr($target_unit, -1) ){
				if( (substr($org_unit, -3) === 'mol' || substr($target_unit, -3) === 'mol') && $this->getMolecularWeight() ) {
					if(substr($org_unit, -3) === 'mol') {
						$value = $value * $this->getMolecularWeight();
						$org_unit = substr($org_unit, 0, -3) . 'g';
					} elseif (substr($target_unit, -3) === 'mol' && substr($org_unit, -1) === 'g') {
						$value = $value / $this->getMolecularWeight();
						$org_unit = substr($org_unit, 0, -1) . 'mol';
					} else {
						throw new Rhymix\Framework\Exception('Unit conversion: unit not matched.<br>'.strval($org_unit).' to '.strval($target_unit));
					}
				} else {
					throw new Rhymix\Framework\Exception('Unit conversion: unit not matched.<br>'.strval($org_unit).' to '.strval($target_unit));
				}
			}
			if (isset($SI_prefixes[substr($org_unit, 0, 1)]) && isset($SI_prefixes[substr($target_unit, 0, 1)])) {
				return $value * ($SI_prefixes[substr($org_unit, 0, 1)]/$SI_prefixes[substr($target_unit, 0, 1)]);
			}
		} elseif ($unit_type == 'concentration') {
			$org_unit = explode('/', $org_unit);
			$target_unit = explode('/', $target_unit);
			
			if (isset($SI_prefixes[substr($org_unit[0], 0, 1)]) && isset($SI_prefixes[substr($org_unit[1], 0, 1)]) && isset($SI_prefixes[substr($target_unit[0], 0, 1)]) && isset($SI_prefixes[substr($target_unit[1], 0, 1)])) {
				return $value * ($SI_prefixes[substr($org_unit[0], 0, 1)]/$SI_prefixes[substr($target_unit[0], 0, 1)]) * ($SI_prefixes[substr($target_unit[1], 0, 1)]/$SI_prefixes[substr($org_unit[1], 0, 1)]);
			}
		} else {
			throw new Rhymix\Framework\Exception('Unit conversion: unpredicted error.');
		}
		
		return;
		
	}

	/*
	 * @ brief: Return the first derivatives for the end correction of AUC calculation.
	*/

	private function getExpFirstDeri_center($time_arr, $conc_arr) {
		$t1 = $time_arr[0];
		$t2 = $time_arr[1];
		$t3 = $time_arr[2];
		$t4 = $time_arr[3];
		
		$C1 = $conc_arr[0];
		$C2 = $conc_arr[1];
		$C3 = $conc_arr[2];
		$C4 = $conc_arr[3];
		
		$dfdt_center1 = ($C2 - $C1) / ($t2 - $t1);
		$dfdt_center2 = ($C3 - $C2) / ($t3 - $t2);
		$dfdt_center3 = ($C4 - $C3) / ($t4 - $t3);
		
		$t_center1 = ($t1 + $t2) / 2;
		$t_center2 = ($t2 + $t3) / 2;
		$t_center3 = ($t3 + $t4) / 2;
		
		$dfdt_2 = (($dfdt_center1) * ( $t2 - $t_center1 ) + ($dfdt_center2) * ( $t_center2 - $t2 ) ) / ( $t_center2 - $t_center1 );
		$dfdt_3 = (($dfdt_center2) * ( $t3 - $t_center2 ) + ($dfdt_center3) * ( $t_center3 - $t3 ) ) / ( $t_center3 - $t_center2 );
		
		return array($dfdt_2, $dfdt_3);
	}
	
	/*
	 * @ brief: Return the terminal phase lambda as a array
	*/
	public function getLambdaArray() {
		$time_conc = $this->getTimeConcentrationArray();
		static $results = null;
		
		if($results !== null && is_array($results)) {
			return $results;
		}
		
		$r2 = null;
		$adj_r2 = null;
		$lambdaZ = null;
		$intercept = null;
		$slice_i = null;
		$n = count($time_conc->time);
		$i = 0;
		while ($i < $n) {
			$time_slice = array_slice($time_conc->time, $i);
			$lnC_slice = array_slice($time_conc->lnC, $i);
			
			if( $this->getLengthofInfusion(-1) !== null) {
				if( $this->getLengthofInfusion(-1) > $time_slice[0] ) {
					$i++;
					continue;
				}
			}
			
			if( $this->getTmax(-1) !== null) {
				if( $this->getTmax(-1) > $time_slice[0] ) {
					$i++;
					continue;
				} elseif( abs($this->getTmax(-1) - $time_slice[0]) < 0.000001 && $this->getAdministrationRoute() !== "iv_bolus" ) {
					$i++;
					continue;
				}
			}
			
			if (count($time_slice) > 2) {
				$param = $this->linearRegression($lnC_slice, $time_slice);
				
				if ($adj_r2 < $param['adjusted_r2'] && ($param['adjusted_r2'] - $adj_r2) > 0.0001 && $param['slope'] < 0) {
					$r2 = $param['r2'];
					$adj_r2 = $param['adjusted_r2'];
					$lambdaZ = $param['slope'];
					$intercept = $param['intercept'];
					$slice_i = $i;
				}
				
			}
			$i++;
		}
		
		$results = array("lambda_Z" => $lambdaZ, "intercept"=> $intercept, "terminal_points"=> count(array_slice($time_conc->time, $slice_i)), "adj_r2"=> $adj_r2, "r2"=> $r2);
		return $results;
	}
	
	/*
	 * @ brief: Return the terminal phase lambda as a float value
	*/
	public function getLambda($precision = 5) {
		if($this->getLambdaArray()['lambda_Z'] === null) {
			return null;
		}
		return -1 * $this->toPrecision($this->getLambdaArray()['lambda_Z'], $precision);
	}
	
	/*
	 * @ brief: Return the number of points used to determine terminal phase lambda
	*/
	public function getTerminalPoints() {
		if($this->getLambdaArray()['lambda_Z'] === null) {
			return null;
		}
		return $this->getLambdaArray()['terminal_points'];
	}
	public function getAdjustedRSquare($precision = 5) {
		if($this->getLambdaArray()['lambda_Z'] === null) {
			return null;
		}
		return $this->toPrecision($this->getLambdaArray()['adj_r2'], $precision);
	}
	public function getRSquare($precision = 5) {
		if($this->getLambdaArray()['lambda_Z'] === null) {
			return null;
		}
		return $this->toPrecision($this->getLambdaArray()['r2'], $precision);
	}
	public function getTerminalHalfLife($precision = 5) {
		if($this->getLambdaArray()['lambda_Z'] === null) {
			return null;
		}
		return $this->toPrecision(log(2)/$this->getLambda(-1), $precision);
	}

}

/* End of file blueberry.item.php */
/* Location: ./modules/blueberry/blueberry.item.php */