<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MTariffVolumes_model
{

	/**
	 * Добавляет фильтр, соответствующий выбранным датам фильтрации
	 * @param array $data
	 * @param string $filter
	 * @param array $params
	 */
	public static function dateControl(array $data,string &$filter,array &$params)
	{
		// Данные приходят в виде 2020-07-16T00:00:00
		// Исправление от 09.07.20 izabunyan с оптимизацией
		foreach(['AttributeValue_begDate_From'=>'>=','AttributeValue_begDate_To'=>'<=',
					'AttributeValue_endDate_From'=>'>=','AttributeValue_endDate_To'=>'<='] as $key=>$val)
		{
			if(!empty($data['filters'][$key])&& preg_match('/^(\d{4}-\d{2}-\d{2})/',
					$data['filters'][$key],$matches))
			{
				$filter .= " and av.".substr($key,0,strrpos($key,'_'))." $val :$key";
				$params[$key] = $matches[1];
			}
		}
	}

	/**
	 * Добавляет фильтр АРМ МО
	 * @param $session
	 * @param $filter
	 * @param $params
	 */
	public static function filterArm($session,&$filter,&$params)
	{
		if($session['CurARM']['ARMType']==='lpuadmin')
		{
			$params['Lpu_Nick']=$session['CurARM']['Lpu_Nick'];
			$filter.="  AND (av.AttributeValue_ValueText like('%МО = ,%') 
		 OR av.AttributeValue_ValueText LIKE(CONCAT('%МО = ',:Lpu_Nick,'%'))) ";
		}
	}
}
