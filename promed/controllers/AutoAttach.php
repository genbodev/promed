<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* AutoAttach - контроллер для выполенния операций автоматическим прикреплением пациентов.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Polka
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Pshenitcyn Ivan aka IvP (ipshon@rambler.ru)
* @version      11.12.2009
*/

class AutoAttach extends swController {
	/**
	 * AutoAttach constructor.
	 */
	function __construct()
	{
		parent::__construct();
		$this->inputRules = array(
			'doAutoAttach' => array(
				array(
					'field' => 'Lpu_id',
					'label' => 'Идентификатор ЛПУ',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'LpuAttachType_id',
					'label' => 'Идентификатор типа прикрепления',
					'rules' => 'trim',
					'type' => 'id'//,
					//'default' => '0'
				),
				array(
					'default' => '1',
					'field' => 'otkat',
					'label' => 'Режим отката',
					'rules' => 'trim',
					'type' => 'string'
				)
			)
		);
	}
	
	/**
	*  Функция получения учстков с зонами обслуживания.
	*  Входные данные: $Lpu_id - идентификатор ЛПУ, $LpuAttachType_id - Идентификатор типа прикрепления.
	*  Результат: Массив с участками и их атрибутими, в том числе и зонами обслуживания.
	*/
	function getLpuRegionsAreas($lpu_id,$LpuAttachType_id)
	{
		// результирующий массив
		$lpuregions_array = array('streets' => array(), 'towns'=>array(), 'cityes'=>array());
		
		$this->load->database();
		$this->load->model("AutoAttach_model", "aamodel");		
		$lpuregions_data = $this->aamodel->getLpuRegionsDataWithStreetsAndHouses($lpu_id,$LpuAttachType_id);
		if (is_array($lpuregions_data) && count($lpuregions_data) > 0)
		{
			foreach ( $lpuregions_data as $lpuregion_area )
			{
				// если есть улица, то добавляем в улицы
				if ( $lpuregion_area['KLStreet_id'] != null )
				{
					// если улиа еще не добавлена, то добавляем
					if ( !array_key_exists($lpuregion_area['KLStreet_id'], $lpuregions_array['streets']) )
					{
						$lpuregions_array['streets'][$lpuregion_area['KLStreet_id']] = array();
					}
					$street_array = &$lpuregions_array['streets'][$lpuregion_area['KLStreet_id']];
					
					// если нет участка, то добавляем его
					if ( !array_key_exists($lpuregion_area['LpuRegion_id'], $street_array) )
					{
						// добавляется тип участка и список домов пустой пока
						$street_array[$lpuregion_area['LpuRegion_id']] = array('houses'=>'', 'LpuRegionType_id' => $lpuregion_area['LpuRegionType_id']);
					}
					// добавляем строку домов
					$street_array[$lpuregion_area['LpuRegion_id']]['houses'] = implode(",", array($street_array[$lpuregion_area['LpuRegion_id']]['houses'], $lpuregion_area['LpuRegionStreet_HouseSet']));
				}
				// иначе проверяем тауны
				else
				{
					if ( $lpuregion_area['KLTown_id'] != null )
					{
						// если таун еще не добавлен, то добавляем
						if ( !array_key_exists($lpuregion_area['KLTown_id'], $lpuregions_array['towns']) )
						{
							$lpuregions_array['towns'][$lpuregion_area['KLTown_id']] = array();
						}
						$town_array = &$lpuregions_array['towns'][$lpuregion_area['KLTown_id']];
						
						// если нет участка, то добавляем его
						if ( !array_key_exists($lpuregion_area['LpuRegion_id'], $town_array) )
						{
							// добавляется тип участка и список домов пустой пока
							$town_array[$lpuregion_area['LpuRegion_id']] = array('houses'=>'', 'LpuRegionType_id' => $lpuregion_area['LpuRegionType_id']);
						}
						// добавляем строку домов
						$town_array[$lpuregion_area['LpuRegion_id']]['houses'] = implode(",", array($town_array[$lpuregion_area['LpuRegion_id']]['houses'], $lpuregion_area['LpuRegionStreet_HouseSet']));
					}
					// иначе в города
					else
					{
						// если город еще не добавлен, то добавляем
						if ( !array_key_exists($lpuregion_area['KLCity_id'], $lpuregions_array['cityes']) )
						{
							$lpuregions_array['cityes'][$lpuregion_area['KLCity_id']] = array();
						}
						$city_array = &$lpuregions_array['cityes'][$lpuregion_area['KLCity_id']];
						
						// если нет участка, то добавляем его
						if ( !array_key_exists($lpuregion_area['LpuRegion_id'], $city_array) )
						{
							// добавляется тип участка и список домов пустой пока
							$city_array[$lpuregion_area['LpuRegion_id']] = array('houses'=>'', 'LpuRegionType_id' => $lpuregion_area['LpuRegionType_id']);
						}
						// добавляем строку домов
						$city_array[$lpuregion_area['LpuRegion_id']]['houses'] = implode(",", array($city_array[$lpuregion_area['LpuRegion_id']]['houses'], $lpuregion_area['LpuRegionStreet_HouseSet']));
					}
				}
			}
			return $lpuregions_array;
		}
		else
			return false;
	}
	
	/**
	*  Функция автоматического прикрепления.
	*  Используется: форма автоматического прикрепления
	*/
	function doAutoAttach()
	{
		if ( !isSuperAdmin() )
		{
			return;
		}
		$data = array();
		$err = getInputParams($data, $this->inputRules['doAutoAttach']);

		// выводим форму
		$this->load->database();
		$this->load->model("Utils_model", "ogmodel");
		$lpu_data = $this->ogmodel->getObjectList(array('object'=>'Lpu', 'Lpu_id'=>null, 'Lpu_Nick'=>null, 'order_by_field'=>'Lpu_Nick' ));
		if (is_array($lpu_data) && count($lpu_data) > 0)
		{
			// отмечаем выбраное лпу
			foreach ($lpu_data as $key => $lpu)
			{
				if ( isset($data['Lpu_id']) && $lpu['Lpu_id'] == $data['Lpu_id'] )
					$lpu_data[$key]['Lpu_IsChecked'] = 'selected="selected"';
				else
					$lpu_data[$key]['Lpu_IsChecked'] = '';
			}
			$lpuatt_data = array(
				//array('LpuAttachType_id' => '0','LpuAttachType_Name' => 'НЕ ВЫБРАН'),
				array('LpuAttachType_id' => 1,'LpuAttachType_Name' => 'Основной'),
				array('LpuAttachType_id' => 2,'LpuAttachType_Name' => 'Гинекологический')
				//array('LpuAttachType_id' => 3,'LpuAttachType_Name' => 'Стоматологический'),
				//array('LpuAttachType_id' => 4,'LpuAttachType_Name' => 'Служебный'),
			);
			// отмечаем выбраный тип прикрепления
			foreach ($lpuatt_data as $key => $att)
			{
				if ( $att['LpuAttachType_id'] == $data['LpuAttachType_id'] )
					$lpuatt_data[$key]['LpuAtt_IsChecked'] = 'selected="selected"';
				else
					$lpuatt_data[$key]['LpuAtt_IsChecked'] = '';
			}
			// выводим собственно форму
			$this->load->library('parser');
			$this->parser->parse("auto_attach", array(
				'lpus_data' => $lpu_data,
				'lpuatts_data' => $lpuatt_data
			));
		}
		else
			return false;

		if ( isset($data['Lpu_id']) AND in_array($data['LpuAttachType_id'], array(1,2)) )
		{
			// откат авторикрепленных карт
			if ( isset($data["otkat"]) && $data["otkat"] == "2" )
			{
				$this->load->database();
				$this->load->model("AutoAttach_model", "aamodel");
				$Lpu_id = $data['Lpu_id'];
				$result = $this->aamodel->otkatAutoAttach($Lpu_id,$data['LpuAttachType_id']);
			}
			// простановка автоприкрепления
			else
			{
				$Lpu_id = $data['Lpu_id'];
				$lpuregions_areas = $this->getLpuRegionsAreas($Lpu_id,$data['LpuAttachType_id']);
				if ( $lpuregions_areas !== false )
				{
					// вызываем метод модели для прикрепления, передаем в него хэш
					$this->load->database();
					$this->load->model("AutoAttach_model", "aamodel");
					$result = $this->aamodel->doAutoAttach($Lpu_id,$data['LpuAttachType_id'], $lpuregions_areas);
				}
				else
				{
					echo "Ошибка определения зон обслуживания участков ЛПУ (возможно они не определены)";
					return false;
				}
			}
		}
	}
	
	/**
	 * Возвращает признак вхождения в диапазон домов (используется в модели)
	 * @param $h_arr
	 * @param $houses
	 * @return string
	 */
	function HouseExist($h_arr, $houses)
	{
		// Сначала разбираем h_arr и определяем: 
		// 1. Обычный диапазон 
		// 2. Четный диапазон
		// 3. Нечетный диапазон
		// 4. Перечисление 
		
		// Разбиваем на номера домов и диапазоны с которым будем проверять
		$hs_arr = preg_split('[,|;]', $houses, -1, PREG_SPLIT_NO_EMPTY);
		$i = 0;
		foreach ($h_arr as $row_arr)
		{
			//print $row_arr."   | ";
			$ch = $this->getHouseArray($row_arr); // сохраняемый 
			//print_r($ch);
			if (count($ch)>0)
			{
				//print $i."-";
				foreach ($hs_arr as $rs_arr)
				{
					$chn = $this->getHouseArray($rs_arr); // выбранный
					if (count($chn)>0) 
					{
						// Проверка на правильность указания диапазона
						if ((($ch[count($ch)-1] == 1) || ($ch[count($ch)-1] == 2)) && ($ch[2]>$ch[4]))
						{
							return "Проверьте поле 'Номера домов': Неверно указан диапазон!";
						}
						
						if ((($ch[count($ch)-1] == 1) && ($chn[count($chn)-1] == 1) && ($ch[1]=='Ч') && ($chn[1]=='Ч')) || // сверяем четный с четным
								(($ch[count($ch)-1] == 1) && ($chn[count($chn)-1] == 1) && ($ch[1]=='Н') && ($chn[1]=='Н')) || // сверяем нечетный с нечетным
								((($ch[count($ch)-1] == 1) || ($ch[count($ch)-1] == 2)) && ($chn[count($chn)-1] == 2)))        // или любой диапазон с обычным
						{
							if (($ch[2]<=$chn[4]) && ($ch[4]>=$chn[2]))
							{
								return "Дома пересекаются с ранее введенным участком!"; // Перечесение (С) и (В) диапазонов
							}
						}
						if ((($ch[count($ch)-1] == 1) || ($ch[count($ch)-1] == 2)) && ($chn[count($chn)-1] == 3)) // Любой диапазон с домом 
						{
							if ((($ch[1]=='Ч') && ($chn[2]%2==0)) || // если четный
									(($ch[1]=='Н') && ($chn[2]%2<>0)) || // нечетный 
									($ch[count($ch)-1] == 2)) // обычный
							{
								if (($ch[2]<=$chn[2]) && ($ch[4]>=$chn[2]))
								{
									return "Дома пересекаются с ранее введенным участком!"; // Перечесение диапазона с конкретным домом
								}
							}
						}
						if ((($chn[count($chn)-1] == 1) || ($chn[count($chn)-1] == 2)) && ($ch[count($ch)-1] == 3)) // Любой дом с диапазоном
						{
							if ((($chn[1]=='Ч') && ($ch[2]%2==0)) || // если четный
									(($chn[1]=='Н') && ($ch[2]%2<>0)) || // нечетный 
									($chn[count($chn)-1] == 2)) // обычный
							{
								if (($chn[2]<=$ch[2]) && ($chn[4]>=$ch[2]))
								{
									return "Дома пересекаются с ранее введенным участком!"; // Перечесение дома с каким-либо диапазоном
								}
							}
						}
						if (($ch[count($ch)-1] == 3) && ($chn[count($chn)-1] == 3)) // Дом с домом
						{
							if (strtolower($ch[0])==strtolower($chn[0]))
							{
								return "Дома пересекаются с ранее введенным участком!"; // Перечесение дома с домом
							}
						}
					}
				}
			}
			else 
			{
				return "Поле 'Номера домов' заполнено с ошибками!"; // Перечесение дома с домом
			}
		}
		return "";
	}
}

?>