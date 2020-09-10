<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 *
 * @package      PromedWeb
 * @access       public
 * @copyright    Copyright (c) 2014 Swan Ltd.
 * @link         http://swan.perm.ru/PromedWeb
 */
require_once('EvnVizitPL_model.php');
/**
 * EvnVizitPLStom_model - Модель посещения в стоматологии
 *
 * Содержит методы и свойства общие для всех объектов,
 * классы которых наследуют класс EvnVizitPLStom
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2009-2014 Swan Ltd.
 * @author       Александр Пермяков
 * @version      08.2014
 *
 * @property int $IsPrimaryVizit
 * @property int $Tooth_id Зуб (основной стомат.диагноз)
 * @property int $Tooth_Code Номер зуба (основной стомат.диагноз)
 * @property string $ToothSurface JSON-строка со списком поверхностей (основной стомат.диагноз)
 *
 * @property EvnPLStom_model $parent
 * @property EvnDiagPLStom_model $EvnDiagPLStom_model
 * @property Kz_UslugaMedType_model $UslugaMedType_model
 */
class EvnVizitPLStom_model extends EvnVizitPL_model
{
	protected $_parentClass = 'EvnPLStom_model';

	/**
	 * Возвращает список всех используемых ключей атрибутов объекта
	 * @return array
	 */
	static function defAttributes()
	{
		$arr = parent::defAttributes();
		$arr[self::ID_KEY]['alias'] = 'EvnVizitPLStom_id';
		$arr['pid']['alias'] = 'EvnPLStom_id';
		$arr['setdate']['alias'] = 'EvnVizitPLStom_setDate';
		$arr['settime']['alias'] = 'EvnVizitPLStom_setTime';
		$arr['disdt']['alias'] = 'EvnVizitPLStom_disDT';
		$arr['diddt']['alias'] = 'EvnVizitPLStom_didDT';
		$arr['statusdate']['alias'] = 'EvnVizitPLStom_statusDate';
		$arr['isinreg']['alias'] = 'EvnVizitPLStom_IsInReg';
		$arr['ispaid']['alias'] = 'EvnVizitPLStom_IsPaid';
		$arr['uet']['alias'] = 'EvnVizitPLStom_Uet';
		$arr['uetoms']['alias'] = 'EvnVizitPLStom_UetOMS';
		$arr['time']['alias'] = 'EvnVizitPLStom_Time';
		$arr['isotherdouble']['alias'] = 'EvnVizitPLStom_IsOtherDouble';
		$arr['tooth_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'Tooth_id',
			'label' => 'Зуб',
			'save' => 'trim',
			'type' => 'id',
			'updateTable' => 'EvnVizitPLStom'
		);
		$arr['tooth_code'] = array(
			'properties' => array(
				self::PROPERTY_READ_ONLY,
			),
			'alias' => 'Tooth_Code',
			'select' => 'v_Tooth.Tooth_Code',
			'join' => 'left join v_Tooth with (nolock) on v_Tooth.Tooth_id = {ViewName}.Tooth_id',
			'label' => 'Код зуба',
			'save' => 'trim',
			'type' => 'int'
		);
		$arr['isprimaryvizit'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnVizitPLStom_IsPrimaryVizit',
			'label' => 'Признак первичного посещения в текущем году',
			'save' => 'trim',
			'type' => 'id',
			'updateTable' => 'EvnVizitPLStom'
		);
		$arr['toothsurface'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			// на клиент и с клиента приходит как список идешников через запятую,
			// а хранится как JSON-строка
			'alias' => 'ToothSurfaceType_id_list',
			'label' => 'Поверхность зуба',
			'save' => 'trim',
			'type' => 'string'
		);
		$arr['indexrep'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnVizitPLStom_IndexRep',
			'label' => 'Признак повторной подачи',
			'save' => 'trim',
			'type' => 'int',
		);
		$arr['indexrepinreg'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_READ_ONLY,
				self::PROPERTY_NOT_SAFE,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnVizitPLStom_IndexRepInReg',
		);
		return $arr;
	}

	/**
	 * Дополнительная обработка значения атрибута сохраненного объекта из БД
	 * перед записью в модель
	 * @param string $column Имя колонки в строчными символами
	 * @param mixed $value Значение. Значения, которые в БД имеют тип datetime, являются экземлярами DateTime.
	 * @return mixed
	 * @throws Exception
	 */
	protected function _processingSavedValue($column, $value)
	{
		if ( false !== strpos($column, 'toothsurface')) {
			$this->load->model('EvnDiagPLStom_model');
			$data = $this->EvnDiagPLStom_model->processingToothSurface($value);
			if (empty($data) || empty($data['ToothSurfaceTypeIdList'])) {
				$value = null;
			} else {
				$value = implode(',', $data['ToothSurfaceTypeIdList']);
			}
		}
		return parent::_processingSavedValue($column, $value);
	}

	/**
	 * Определение идентификатора класса события
	 * @return int
	 */
	static function evnClassId()
	{
		return 13;
	}

	/**
	 * Определение кода класса события
	 * @return string
	 */
	static function evnClassSysNick()
	{
		return 'EvnVizitPLStom';
	}

	/**
	 * Обязательность основного диагноза посещения
	 * @return bool
	 */
	protected function _isRequiredDiag()
	{
		/*return (in_array($this->regionNick, array('ufa'))
				&& in_array($this->scenario, array(self::SCENARIO_DO_SAVE, self::SCENARIO_SET_ATTRIBUTE))
		);*/
	}

	/**
	 * Правила для контроллера для извлечения входящих параметров при сохранении
	 * @return array
	 */
	protected function _getSaveInputRules()
	{
		$all = parent::_getSaveInputRules();
		// параметры
		if ( in_array($this->regionNick, array('ekb')) ) {
			$all['UslugaSelectedList'] = array(
				'field' => 'UslugaSelectedList',
				'label' => 'Состав кода посещения',
				'rules' => 'trim',
				'type' => 'string'
			);
		}
		$all['ignoreKSGCheck'] = array(
			'field' => 'ignoreKSGCheck',
			'label' => 'Признак игнорирования проверки КСГ',
			'rules' => '',
			'type' => 'int'
		);
		$all['UslugaComplexTariff_uid'] = array(
			'field' => 'UslugaComplexTariff_uid',
			'label' => 'Тариф',
			'rules' => '',
			'type' => 'int'
		);
		$all['EvnUslugaStom_UED'] = array(
			'field' => 'EvnUslugaStom_UED',
			'label' => 'УЕТ врача',
			'rules' => '',
			'type' => 'float'
		);
        $all['UslugaMedType_id'] = [
            'field' => 'UslugaMedType_id',
            'label' => 'Вид услуги',
            'rules' => '',
            'type' => 'int'
        ];

		return $all;
	}

	/**
	 * Извлечение значений параметров модели из входящих параметров,
	 * переданных из контроллера
	 * @param array $data
	 * @throws Exception
	 */
	function setParams($data)
	{
		parent::setParams($data);
		$this->_params['UslugaSelectedList'] = isset($data['UslugaSelectedList']) ? $data['UslugaSelectedList']: null;
		$this->_params['UslugaComplexTariff_id'] = isset($data['UslugaComplexTariff_uid']) ? $data['UslugaComplexTariff_uid']: null;
		$this->_params['EvnUslugaStom_UED'] = isset($data['EvnUslugaStom_UED']) ? $data['EvnUslugaStom_UED']: null;
        $this->_params['UslugaMedType_id'] = isset($data['UslugaMedType_id']) ? $data['UslugaMedType_id'] : null;
	}

	/**
	 * Логика перед сохранения стомат. посещения
	 * @param array $data Массив входящих параметров
	 * @throws Exception
	 */
	protected function _beforeSave($data = array())
	{
		parent::_beforeSave($data);
		
		$this->set_IsPrimaryVizit();
		
		if (in_array($this->getRegionNick(), array('perm','vologda')) && $data && $data['EvnVizitPLStom_setDate'] && $data['EvnVizitPLStom_setDate'] > "2015-10-31") {
			// надо проверить, что КСГ удовлетворяет новым датам
			if (!empty($this->id)) {
				$query = "
					select
						edps.EvnDiagPLStom_id,
						edps.Mes_id,
						EU.EvnUslugaStom_setDate,
						EU.EvnUslugaStom_disDate,
						m.Mes_begDT,
						m.Mes_endDT
					from
						v_EvnDiagPLStom edps (nolock)
						inner join v_MesOld m (nolock) on m.Mes_id = edps.Mes_id
						outer apply(
							select
								MIN(EvnUslugaStom_setDate) as EvnUslugaStom_setDate,
								MAX(ISNULL(EvnUslugaStom_disDate, EvnUslugaStom_setDate)) as EvnUslugaStom_disDate
							from
								v_EvnUslugaStom (nolock)
							where
								EvnDiagPLStom_id = edps.EvnDiagPLStom_id
								and EvnUslugaStom_pid != :EvnUslugaStom_pid
						) EU
					where
						edps.EvnDiagPLStom_id IN (select EvnDiagPLStom_id from v_EvnUslugaStom (nolock) where EvnUslugaStom_pid = :EvnUslugaStom_pid)
				";

				$resp_edps = $this->queryResult($query, array(
					'EvnUslugaStom_pid' => $this->id
				));

				foreach($resp_edps as $resp_edpsone) {
					if (!empty($resp_edpsone['Mes_id'])) {
						$EvnUslugaStom_setDate = strtotime($this->setDate);
						if (!empty($resp_edpsone['EvnUslugaStom_setDate']) && $resp_edpsone['EvnUslugaStom_setDate']->getTimestamp() < $EvnUslugaStom_setDate) {
							$EvnUslugaStom_setDate = $resp_edpsone['EvnUslugaStom_setDate']->getTimestamp();
						}

						$EvnUslugaStom_disDate = strtotime($this->setDate);
						if (!empty($resp_edpsone['EvnUslugaStom_disDate']) && $resp_edpsone['EvnUslugaStom_disDate']->getTimestamp() > $EvnUslugaStom_disDate) {
							$EvnUslugaStom_disDate = $resp_edpsone['EvnUslugaStom_disDate']->getTimestamp();
						}

						// получили даты, проверяем дату КСГ
						if (
							(!empty($resp_edpsone['Mes_begDT']) && $resp_edpsone['Mes_begDT']->getTimestamp() > $EvnUslugaStom_setDate)
							|| (!empty($resp_edpsone['Mes_endDT']) && $resp_edpsone['Mes_endDT']->getTimestamp() < $EvnUslugaStom_disDate)
						) {
							if (empty($data['ignoreKSGCheck'])) {
								// если КСГ не актуальна в периоде заболевания, то выдаём ошибочку
								$this->_saveResponse['ignoreParam'] = 'ignoreKSGCheck';
								$this->_setAlertMsg('Выбранная в заболевании КСГ или связка КСГ с диагнозом закрыта на указанную дату посещения. При сохранении посещения информация по сохраненным услугам по КСГ будет потеряна. Сохранить?');
								throw new Exception('YesNo', 110);
							} else {
								// очищаем КСГ в заболевании
								$this->EvnDiagPLStom_model->updateMesId(array(
									'Mes_id' => null,
									'EvnDiagPLStom_id' => $resp_edpsone['EvnDiagPLStom_id']
								));
							}
						}
					}
				}
			}

			$EPLS = $this->getFirstRowFromQuery("
				select
					EPLS.EvnPLStom_id
				from
					v_EvnPLStom EPLS (nolock)
				where
					EPLS.EvnPLStom_id = :EvnPLStom_id
					and EPLS.EvnPLStom_setDate <= '2015-10-31'
			", array('EvnPLStom_id' => $this->id));

			if (!empty($EPLS)) {
				throw new Exception('Механизмы хранения данных и оплаты по стомат. случаям изменены ТФОМС с 01-11-2015, создание переходных случаев невозможно.');
			}
		}
		//echo count($this->evnUslugaList)." - ".$this->evnUslugaList[0]['EvnUsluga_IsVizitCode'];exit();
		if ( isset($this->_params['UslugaSelectedList']) /*&& 1 == count($this->evnUslugaList) && 2 == $this->evnUslugaList[0]['EvnUsluga_IsVizitCode']*/ ) {
			//Сохраняем стомат. услуги из списка отмеченных услуг из состава кода посещения, если ранее не было заведено услуг
			if (empty($this->MedPersonal_id)) {
				throw new Exception('Поле Врач обязательно для заполнения.');
			}
			
			/* начало модификации кода скопированного из EvnUsluga_model::_beforeSaveEvnUslugaStomPackage */
			$tmp = json_decode($this->_params['UslugaSelectedList'], true);
			if (empty($tmp) || false === is_array($tmp )) {
				// Про обязательность выбора услуг из состава ничего не сказано в ТЗ
				throw new Exception('Нет выбранных услуг из состава кода посещения');
				$tmp = array();
			}
			$this->_params['UslugaSelectedList'] = $tmp;
			$uslugaComplexIdList = array();
			$uslugaComplexRows = array();
			$summaUet = 0;
			foreach ($this->_params['UslugaSelectedList'] as $row) {
				if (empty($row['UslugaComplex_id'])
				|| !array_key_exists('UslugaComplexTariff_id', $row)
				|| (empty($row['UslugaComplexTariff_UED']) && empty($data['EvnDiagPLStom_id']))
				|| !array_key_exists('UslugaComplexTariff_UEM', $row)
				|| !array_key_exists('UslugaComplexTariff_Tariff', $row)
				|| empty($row['EvnUsluga_Kolvo'])
				) {
					throw new Exception('Неправильный формат списка отмеченных услуг из состава кода посещения');
				}
				if ($this->getRegionNick() == 'ekb') {
					$result = $this->getFirstResultFromQuery("
						select top 1 MesUsluga_id from v_MesUsluga (nolock)
							where UslugaComplex_id = :UslugaComplex_id
							and MesUslugaLinkType_id = 5
							and Mes_id = :Mes_id
					", array('UslugaComplex_id' => $row['UslugaComplex_id'],'Mes_id'=>$data['Mes_id']));
					if (empty($result)) {
						throw new Exception('Указаны услуги, не входящие в выбранный стандарт', 400);
					}
				}
				$row['EvnUslugaStom_UED'] = null;
				$row['EvnUslugaStom_UEM'] = null;
				$row['EvnUslugaStom_Price'] = 0;
				$row['EvnUslugaStom_Kolvo'] = $row['EvnUsluga_Kolvo'];
				unset($row['EvnUsluga_Kolvo']);
				if ($this->MedPersonal_id>0) {
					$row['EvnUslugaStom_UED'] = $row['UslugaComplexTariff_UED'];
				}
				if ($this->MedPersonal_sid>0) {
					$row['EvnUslugaStom_UEM'] = $row['UslugaComplexTariff_UEM'];
				}
				if ( $row['EvnUslugaStom_UED'] > 0 ) {
					$row['EvnUslugaStom_Price'] += $row['EvnUslugaStom_UED'];
				}
				if ( $row['EvnUslugaStom_UEM'] > 0 ) {
					$row['EvnUslugaStom_Price'] += $row['EvnUslugaStom_UEM'];
				}
				unset($row['UslugaComplexTariff_UED']);
				unset($row['UslugaComplexTariff_UEM']);
				unset($row['UslugaComplexTariff_Tariff']);
				$uslugaComplexRows[] = $row;
				$uslugaComplexIdList[] = $row['UslugaComplex_id'];
				$summaUet += ($row['EvnUslugaStom_Kolvo'] * $row['EvnUslugaStom_Price']);
			}
			$in_UslugaComplex_list = implode(',', $uslugaComplexIdList);

			$this->load->model('EvnUsluga_model');
			// Проверка на дубли
			$response = $this->EvnUsluga_model->checkEvnUslugaDoubles(array(
				'Person_id' => $this->Person_id,
				'Lpu_id' => $this->Lpu_id,
				'MedPersonal_id' => $this->MedPersonal_id,
				'LpuSection_uid' => $this->LpuSection_id,
				'Lpu_uid' => $this->Lpu_id,
				'Usluga_id' => NULL,
				'UslugaComplex_id' => NULL,
				'PayType_id' => $this->PayType_id,
				'EvnUslugaStom_id' => NULL,
				'EvnUslugaStom_setDate' => $this->setDate,
				'EvnUslugaStom_pid' => $this->id,
				'EvnDiagPLStom_id' => !empty($this->_params['EvnDiagPLStom_id']) ? $this->_params['EvnDiagPLStom_id'] : null,
			), 'stom', $in_UslugaComplex_list);
			if ( $response == -1 ) {
				throw new Exception('Ошибка при выполнении проверки услуг на дубли');
			}
			if ( $response > 0 ) {
				throw new Exception('Сохранение отменено, т.к. данная услуга уже заведена в посещении.
				Если было выполнено несколько услуг, то измените количество в ранее заведенной услуге');
			}
			$this->_params['UslugaSelectedList'] = $uslugaComplexRows;
			/* конец модификации кода скопированного из EvnUsluga_model::_beforeSaveEvnUslugaStomPackage */
		}

		$data['Tooth_id'] = $this->Tooth_id;
		$data['Tooth_Code'] = $this->getToothCode();
		// $this->ToothSurface в том формате, как пришло из контроллера
		$data['ToothSurfaceType_id_list'] = $this->ToothSurface;
		$this->load->model('EvnDiagPLStom_model');
		$data = $this->EvnDiagPLStom_model->beforeSaveEvnDiagPLStom($data);
		$this->setAttribute('toothsurface', $data['json']);
		$this->setAttribute('tooth_id', $data['Tooth_id']);

		if ($this->scenario == self::SCENARIO_AUTO_CREATE
			&& 13 == $this->evnClassId && isset($this->lpuSectionData['LpuSectionProfile_Code'])
			&& $this->lpuSectionData['LpuSectionProfile_Code'] > 0
		) {
			$LpuSectionProfile_id = $this->lpuSectionData['LpuSectionProfile_id'];
			if($this->getRegionNick() == 'vologda' && !empty($this->parent->evnVizitList) && is_array($this->parent->evnVizitList)){
				//значение lpusectionprofile_id из последнего посещения
				//$arr = $this->parent->evnVizitList;
				$arr = array_filter($this->parent->evnVizitList, function($vizit){
					return (!empty($vizit['EvnVizitPL_id']));
				});
				usort($arr, function($a, $b){
					return ($a['EvnVizitPL_setDT'] > $b['EvnVizitPL_setDT']) ? -1 : 1;
				});
				$lastVisit = reset($arr);
				$firstVisit = end($arr);
				
				$this->load->model('LpuStructure_model');
				//полученим профили отделения (основной и дополнительные) из рабочего места текущего пользователя.
				$lpuSectionLpuSectionProfile = $this->LpuStructure_model->getLpuStructureProfileAll(array('LpuSection_id' => $this->LpuSection_id));
				$userProfileID = array();
				if(!empty($lastVisit['LpuSectionProfile_id']) && $lpuSectionLpuSectionProfile && is_array($lpuSectionLpuSectionProfile) && count($lpuSectionLpuSectionProfile)>0){
					foreach ($lpuSectionLpuSectionProfile as $row) {
						if(!empty($row['LpuSectionProfile_id'])) $userProfileID[] = $row['LpuSectionProfile_id'];
					}
					// если у пользователя существует профиль предыдущего
					if(in_array($lastVisit['LpuSectionProfile_id'], $userProfileID)){
						$LpuSectionProfile_id = $lastVisit['LpuSectionProfile_id'];
					}
				}
			}
			$this->setAttribute('lpusectionprofile_id', $LpuSectionProfile_id);
		}		
	}

	/**
	 * Сохранение посещения стоматки
	 */
	protected function _save($queryParams = array())
	{
		$resp_save = parent::_save($queryParams);

		// эта логика должна выполняться при любом сохранении посещения (как при апдейте одного поля в ЭМК, так и при апдейте всего посещения на форме посещения)
		// поэтому решил унаследовать функцию _save.
		if (!empty($resp_save[0]['EvnVizitPLStom_id'])) {
			/* ----------- перенос логики из хранимки p_EvnVizitPLStom_set ------------------- */
			$usluga_resp = $this->queryResult("
				select top 1
					EvnUslugaCommon_id,
					UslugaComplexTariff_id,
					EvnUslugaCommon_Price
				from
					v_EvnUslugaCommon with (nolock) 
				where
					EvnUslugaCommon_pid = :EvnVizitPLStom_id
					and EvnUslugaCommon_IsVizitCode = 2
			", array(
				'EvnVizitPLStom_id' => $resp_save[0]['EvnVizitPLStom_id']
			));

			if (!empty($this->UslugaComplex_id)) {
				$action = !empty($usluga_resp[0]['EvnUslugaCommon_id']) ? 'upd' : 'ins';
				$query = "
					declare
						@Res bigint,
						@ErrCode int,
						@ErrMessage varchar(4000);
	
					set @Res = :EvnUslugaCommon_id;
					
					exec dbo.p_EvnUslugaCommon_{$action}
						@EvnUslugaCommon_id = @Res OUTPUT,
						@EvnUslugaCommon_pid = :EvnVizitPLStom_id,
						@Lpu_id = :Lpu_id,
						@Server_id = :Server_id,
						@PersonEvn_id = :PersonEvn_id,
						@EvnUslugaCommon_setDT =  :EvnVizitPLStom_setDT,
						@PayType_id = :PayType_id,
						@UslugaComplex_id = :UslugaComplex_id,
						@MedStaffFact_id = :MedStaffFact_id,
						@MedPersonal_id = :MedPersonal_id,
						@UslugaPlace_id = 1,
						@Lpu_uid = NULL,
						@LpuSection_uid =:LpuSection_id,
						@Org_uid = NULL,
						@EvnUslugaCommon_Kolvo = 1,
						@EvnUslugaCommon_IsVizitCode = 2,
						@UslugaComplexTariff_id = :UslugaComplexTariff_id,
						@EvnUslugaCommon_Price = :EvnUslugaCommon_Price,
						@EvnUslugaCommon_Summa = :EvnUslugaCommon_Summa,
						@pmUser_id = :pmUser_id,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMessage OUTPUT;
					select @Res as EvnUslugaCommon_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
				";
				$queryParams = array(
					'EvnUslugaCommon_id' => !empty($usluga_resp[0]['EvnUslugaCommon_id']) ? $usluga_resp[0]['EvnUslugaCommon_id'] : null,
					'EvnVizitPLStom_id' => $resp_save[0]['EvnVizitPLStom_id'],
					'Lpu_id' => $this->Lpu_id,
					'Server_id' => $this->Server_id,
					'PersonEvn_id' => $this->PersonEvn_id,
					'EvnVizitPLStom_setDT' => $this->setDT,
					'PayType_id' => $this->PayType_id,
					'UslugaComplex_id' => $this->UslugaComplex_id,
					'MedStaffFact_id' => $this->MedStaffFact_id,
					'MedPersonal_id' => $this->MedPersonal_id,
					'LpuSection_id' => $this->LpuSection_id,
					'UslugaComplexTariff_id' => $this->_params['UslugaComplexTariff_id'],
					'EvnUslugaCommon_Price' => $this->_params['EvnUslugaStom_UED'],
					'EvnUslugaCommon_Summa' => $this->_params['EvnUslugaStom_UED'],
					'pmUser_id' => $this->promedUserId
				);
				$result = $this->db->query($query, $queryParams);
				if (!is_object($result)) {
					throw new Exception('Ошибка при сохранении услуги посещения');
				}
				$response = $result->result('array');
				if (!empty($response[0]['Error_Msg'])) {
					throw new Exception('Ошибка при сохранении услуги посещения: ' . $response[0]['Error_Msg']);
				}
			} elseif (empty($this->UslugaComplex_id) && !empty($usluga_resp[0]['EvnUslugaCommon_id'])) {
				$query = "
					declare
						@Res bigint,
						@ErrCode int,
						@ErrMessage varchar(4000);
					
					EXEC dbo.p_EvnUslugaCommon_del 
						@EvnUslugaCommon_id = @EvnUsluga_id,
						@pmUser_id = @pmUser_id,
						@Error_Code = @Error_Code OUTPUT,
						@Error_Message = @Error_Message OUTPUT;
					select @Res as EvnUslugaCommon_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
				";
				$result = $this->db->query($query, array(
					'EvnUslugaCommon_id' => $usluga_resp[0]['EvnUslugaCommon_id'],
					'pmUser_id' => $this->promedUserId
				));
				if (!is_object($result)) {
					throw new Exception('Ошибка при удалении услуги посещения');
				}
				$response = $result->result('array');
				if (!empty($response[0]['Error_Msg'])) {
					throw new Exception('Ошибка при удалении услуги посещения: ' . $response[0]['Error_Msg']);
				}
			}
			/* ----------- end перенос логики из хранимки p_EvnVizitPLStom_set ------------------- */
		}

		return $resp_save;
	}

	/**
	 * Логика после успешного сохранения стомат. посещения
	 * @param array $result Результат выполнения запроса
	 */
	protected function _afterSave($result) {
		// Почему в старом методе сохранения не было обновления МЭС в других посещениях?
		// Почему в старом методе сохранения не было обновления специфики заболевания?
		// Почему в старом методе сохранения не было создания нового XML-документа протокола осмотра?
		parent::_afterSave($result);

        $this->saveUslugaMedTypeLink();



		if ($this->_isAttributeChanged('toothsurface')) {
			// обработка основного диагноза
			$this->_saveResponse = $this->EvnDiagPLStom_model->afterSaveEvnDiagPLStom('EvnVizitPLStom', $this->_saveResponse);
		}
		
		if ( is_array($this->_params['UslugaSelectedList']) ) {
			//Сохраняем стомат. услуги из списка отмеченных услуг из состава кода посещения
			$data = array(
				'Person_id' => $this->Person_id,
				'Lpu_id' => $this->Lpu_id,
				'MedPersonal_id' => $this->MedPersonal_id,
				'LpuSection_uid' => $this->LpuSection_id,
				'Lpu_uid' => $this->Lpu_id,
				'Usluga_id' => NULL,
				'UslugaComplex_id' => NULL,
				'PayType_id' => $this->PayType_id,
				'EvnUslugaStom_id' => NULL,
				'EvnUslugaStom_setDate' => $this->setDate,
				'EvnUslugaStom_setTime' => $this->setTime,
				'EvnUslugaStom_disDate' => NULL,
				'EvnUslugaStom_pid' => $this->id,
				'pmUser_id' => $this->promedUserId,
				'EvnDiagPLStom_id' => !empty($this->_params['EvnDiagPLStom_id']) ? $this->_params['EvnDiagPLStom_id'] : null,
				'Server_id' => $this->Server_id,
				'PersonEvn_id' => $this->PersonEvn_id,
				'MedStaffFact_id' => $this->MedStaffFact_id,
				'MedPersonal_sid' => $this->MedPersonal_sid,
				'UslugaPlace_id' => 1,
			);
			/* начало модификации кода скопированного из EvnUsluga_model::_saveEvnUslugaPackage */
			$this->load->model('PersonToothCard_model');
			$newStatesData = array(
				'EvnUsluga_pid' => $this->id,
				'EvnDiagPLStom_id' => !empty($this->_params['EvnDiagPLStom_id']) ? $this->_params['EvnDiagPLStom_id'] : null,
				'Person_id' => $this->Person_id,
				'Lpu_id' => $this->Lpu_id,
				'EvnUsluga_setDT' => $this->setDate,
				'pmUser_id' => $this->promedUserId,
				'UslugaData' => array(),
				//'session' => $this->_params['session'],
			);
			// то, что делалось в EvnUsluga_model::_beforeSaveEvnUslugaStomPackage, сделано в self::_beforeSave
			foreach ($this->_params['UslugaSelectedList'] as $row) {
				$data['UslugaComplex_id'] = $row['UslugaComplex_id'];
				$data['UslugaComplexTariff_id'] = $row['UslugaComplexTariff_id'];
				$data['EvnUslugaStom_UED'] = $row['EvnUslugaStom_UED'];
				$data['EvnUslugaStom_UEM'] = $row['EvnUslugaStom_UEM'];
				$data['EvnUslugaStom_Kolvo'] = $row['EvnUslugaStom_Kolvo'];
				$data['EvnUslugaStom_Price'] = $row['EvnUslugaStom_Price'];
				
				/* начало модификации кода скопированного из EvnUsluga_model::_saveEvnUslugaStom */
				if ( isset($data['EvnUslugaStom_setTime']) ) {
					if(strlen($data['EvnUslugaStom_setDate']) > 10) // Почему-то иногда в дате уже вшито время, поэтому избавляемся от него
						$data['EvnUslugaStom_setDate'] = substr($data['EvnUslugaStom_setDate'],0,10);
					$data['EvnUslugaStom_setDate'] .= ' ' . $data['EvnUslugaStom_setTime'] . ':00:000';
				}

				if ( !empty($data['EvnUslugaStom_disDate']) && !empty($data['EvnUslugaStom_disTime']) ) {
					if(strlen($data['EvnUslugaStom_disDate']) > 10)
						$data['EvnUslugaStom_disDate'] = substr($data['EvnUslugaStom_disDate'],0,10);
					$data['EvnUslugaStom_disDate'] .= ' ' . $data['EvnUslugaStom_disTime'] . ':00:000';
				}
				if ( empty($data['EvnUslugaStom_disDate']) ) {
					$data['EvnUslugaStom_disDate'] = $data['EvnUslugaStom_setDate'];
				}

				if ( empty($data['EvnUslugaStom_id']) || $data['EvnUslugaStom_id'] <= 0 ) {
					$action = 'ins';
				} else {
					$action = 'upd';
				}
				$query = "
					declare
						@Res bigint,
						@ErrCode int,
						@ErrMessage varchar(4000);

					set @Res = :EvnUslugaStom_id;

					exec p_EvnUslugaStom_" . $action . "
						@EvnUslugaStom_id = @Res output,
						@EvnUslugaStom_pid = :EvnUslugaStom_pid,
						@Lpu_id = :Lpu_id,
						@Server_id = :Server_id,
						@PersonEvn_id = :PersonEvn_id,
						@EvnUslugaStom_setDT = :EvnUslugaStom_setDT,
						@EvnUslugaStom_disDT = :EvnUslugaStom_disDT,
						@PayType_id = :PayType_id,
						@Usluga_id = :Usluga_id,
						@UslugaComplex_id = :UslugaComplex_id,
						@MedPersonal_id = :MedPersonal_id,
						@MedStaffFact_id = :MedStaffFact_id,
						@MedPersonal_sid = :MedPersonal_sid,
						@UslugaPlace_id = :UslugaPlace_id,
						@LpuSection_uid = :LpuSection_uid,
						@EvnUslugaStom_Kolvo = :EvnUslugaStom_Kolvo,
						@EvnUslugaStom_UED = :EvnUslugaStom_UED,
						@EvnUslugaStom_UEM = :EvnUslugaStom_UEM,
						@EvnUslugaStom_Price = :EvnUslugaStom_Price,
						@EvnUslugaStom_Summa = :EvnUslugaStom_Summa,
						@UslugaComplexTariff_id = :UslugaComplexTariff_id,
						@EvnUslugaStom_SumUL = :EvnUslugaStom_SumUL,
						@EvnUslugaStom_SumUR = :EvnUslugaStom_SumUR,
						@EvnUslugaStom_SumDL = :EvnUslugaStom_SumDL,
						@EvnUslugaStom_SumDR = :EvnUslugaStom_SumDR,
						@EvnPrescrTimetable_id = null,
						@EvnPrescr_id = null,
						@EvnUslugaStom_IsVizitCode = :EvnUslugaStom_IsVizitCode,
						@EvnDiagPLStom_id = :EvnDiagPLStom_id,
						@EvnUslugaStom_IsMes = :EvnUslugaStom_IsMes,
						@EvnUslugaStom_IsAllMorbus = :EvnUslugaStom_IsAllMorbus,
						@pmUser_id = :pmUser_id,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMessage output;
					select @Res as EvnUslugaStom_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
				";
				$queryParams = array(
					'EvnUslugaStom_id' => $data['EvnUslugaStom_id'],
					'EvnUslugaStom_pid' => $data['EvnUslugaStom_pid'],
					'Lpu_id' => $data['Lpu_id'],
					'Server_id' => $data['Server_id'],
					'PersonEvn_id' => $data['PersonEvn_id'],
					'EvnUslugaStom_setDT' => $data['EvnUslugaStom_setDate'],
					'EvnUslugaStom_disDT' => $data['EvnUslugaStom_disDate'],
					'PayType_id' => $data['PayType_id'],
					'Usluga_id' => (!empty($data['Usluga_id']) ? $data['Usluga_id'] : NULL),
					'UslugaComplex_id' => (!empty($data['UslugaComplex_id']) ? $data['UslugaComplex_id'] : NULL),
					'MedPersonal_id' => $data['MedPersonal_id'],
					'MedStaffFact_id' => $data['MedStaffFact_id'],
					'MedPersonal_sid' => (!empty($data['MedPersonal_sid']) ? $data['MedPersonal_sid'] : NULL),
					'UslugaPlace_id' => $data['UslugaPlace_id'],
					'LpuSection_uid' => $data['LpuSection_uid'],
					'EvnUslugaStom_Kolvo' => $data['EvnUslugaStom_Kolvo'],
					'EvnUslugaStom_Price' => $data['EvnUslugaStom_Price'],
					'EvnUslugaStom_UED' => (!empty($data['EvnUslugaStom_UED']) ? $data['EvnUslugaStom_UED'] : NULL),
					'EvnUslugaStom_UEM' => (!empty($data['EvnUslugaStom_UEM']) ? $data['EvnUslugaStom_UEM'] : NULL),
					'EvnUslugaStom_Summa' => number_format(($data['EvnUslugaStom_Kolvo'] * $data['EvnUslugaStom_Price']), 2, '.', ''),
					'UslugaComplexTariff_id' => (!empty($data['UslugaComplexTariff_id']) ? $data['UslugaComplexTariff_id'] : NULL),
					'EvnUslugaStom_SumUL' => (!empty($data['EvnUslugaStom_SumUL']) ? $data['EvnUslugaStom_SumUL'] : NULL),
					'EvnUslugaStom_SumUR' => (!empty($data['EvnUslugaStom_SumUR']) ? $data['EvnUslugaStom_SumUR'] : NULL),
					'EvnUslugaStom_SumDL' => (!empty($data['EvnUslugaStom_SumDL']) ? $data['EvnUslugaStom_SumDL'] : NULL),
					'EvnUslugaStom_SumDR' => (!empty($data['EvnUslugaStom_SumDR']) ? $data['EvnUslugaStom_SumDR'] : NULL),
					'EvnUslugaStom_IsVizitCode' => (!empty($data['EvnUslugaStom_IsVizitCode']) ? $data['EvnUslugaStom_IsVizitCode'] : NULL),
					'EvnDiagPLStom_id' => (!empty($data['EvnDiagPLStom_id']) ? $data['EvnDiagPLStom_id'] : NULL),
					'EvnUslugaStom_IsMes' => (!empty($data['EvnUslugaStom_IsMes']) ? $data['EvnUslugaStom_IsMes'] : NULL),
					'EvnUslugaStom_IsAllMorbus' => (!empty($data['EvnUslugaStom_IsAllMorbus']) ? $data['EvnUslugaStom_IsAllMorbus'] : NULL),
					'pmUser_id' => $data['pmUser_id']
				);
				$result = $this->db->query($query, $queryParams);
				if ( !is_object($result) ) {
					throw new Exception('Ошибка при сохранении стоматологической услуги');
				}
				/* конец модификации кода скопированного из EvnUsluga_model::_saveEvnUslugaStom */
				
				$response = $result->result('array');
				
				$newStatesData['UslugaData'][] = array(
					'EvnUsluga_id' => $response[0]['EvnUslugaStom_id'],
					'UslugaComplex_id' => $data['UslugaComplex_id'],
				);
			}
			if (empty($data['EvnUslugaStom_id'])) {
				// устанавливаем состояния только при добавлении
				$this->PersonToothCard_model->applyEvnUslugaChanges($newStatesData);
			}
			/* конец модификации кода скопированного из EvnUsluga_model::_saveEvnUslugaPackage */
		}
	}

	/**
	 * @param int $id
	 * @param mixed $value
	 * @return array
	 */
	function updateToothId($id, $value = null)
	{
		return $this->_updateAttribute($id, 'tooth_id', $value);
	}

	/**
	 * @param int $id
	 * @param mixed $value
	 * @return array
	 */
	function updateToothSurfaceTypeIdList($id, $value = null)
	{
		return $this->_updateAttribute($id, 'toothsurface', $value);
	}

	/**
	 * @param int $id
	 * @param mixed $value
	 * @return array
	 */
	function updateIsPrimaryVizit($id, $value = null)
	{
		return $this->_updateAttribute($id, 'isprimaryvizit', $value);
	}

	/**
	 * @param int $id
	 * @param mixed $value
	 * @return array
	 */
	function updateUslugaComplexTariffId($id, $value = null)
	{
		$usluga = $this->getFirstRowFromQuery("
			select top 1 
			EvnUslugaCommon_id,
			EvnUslugaCommon_pid, 
			Lpu_id,
			Server_id,
			PersonEvn_id,
			convert(varchar, EvnUslugaCommon_setDT, 120) as EvnUslugaCommon_setDT,
			PayType_id,
			UslugaComplex_id,
			MedStaffFact_id,
			MedPersonal_id,
			null as LpuSection_id,
			EvnUslugaCommon_Price,
			EvnUslugaCommon_Summa
			from v_EvnUslugaCommon with (nolock) 
			where EvnUslugaCommon_pid = :EvnVizitPLStom_id and EvnUslugaCommon_IsVizitCode = 2
		", array('EvnVizitPLStom_id' => $id));
		
		$price = $this->getFirstResultFromQuery("
			select top 1 UslugaComplexTariff_UED
			from v_UslugaComplexTariff with (nolock) 
			where UslugaComplexTariff_id = :UslugaComplexTariff_id
		", array('UslugaComplexTariff_id' => $value));
		
		if (count($usluga) && !empty($usluga['EvnUslugaCommon_id'])) {
			$query = "
				declare
					@Res bigint,
					@ErrCode int,
					@ErrMessage varchar(4000);

				set @Res = :EvnUslugaCommon_id;
				
				exec dbo.p_EvnUslugaCommon_upd
					@EvnUslugaCommon_id = @Res OUTPUT,
					@EvnUslugaCommon_pid = :EvnUslugaCommon_pid,
					@Lpu_id = :Lpu_id,
					@Server_id = :Server_id,
					@PersonEvn_id = :PersonEvn_id,
					@EvnUslugaCommon_setDT =  :EvnUslugaCommon_setDT,
					@PayType_id = :PayType_id,
					@UslugaComplex_id = :UslugaComplex_id,
					@MedStaffFact_id = :MedStaffFact_id,
					@MedPersonal_id = :MedPersonal_id,
					@UslugaPlace_id = 1,
					@Lpu_uid = NULL,
					@LpuSection_uid = :LpuSection_id,
					@Org_uid = NULL,
					@EvnUslugaCommon_Kolvo = 1,
					@EvnUslugaCommon_IsVizitCode = 2,
					@UslugaComplexTariff_id = :UslugaComplexTariff_id,
					@EvnUslugaCommon_Price = :EvnUslugaCommon_Price,
					@EvnUslugaCommon_Summa = :EvnUslugaCommon_Summa,
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMessage OUTPUT;
				select @Res as EvnUslugaCommon_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			";
			$queryParams = array(
				'EvnUslugaCommon_id' => $usluga['EvnUslugaCommon_id'],
				'EvnUslugaCommon_pid' => $usluga['EvnUslugaCommon_pid'],
				'Lpu_id' => $usluga['Lpu_id'],
				'Server_id' => $usluga['Server_id'],
				'PersonEvn_id' => $usluga['PersonEvn_id'],
				'EvnUslugaCommon_setDT' => $usluga['EvnUslugaCommon_setDT'],
				'PayType_id' => $usluga['PayType_id'],
				'UslugaComplex_id' => $usluga['UslugaComplex_id'],
				'MedStaffFact_id' => $usluga['MedStaffFact_id'],
				'MedPersonal_id' => $usluga['MedPersonal_id'],
				'LpuSection_id' => $usluga['LpuSection_id'],
				'UslugaComplexTariff_id' => $value,
				'EvnUslugaCommon_Price' => $price,
				'EvnUslugaCommon_Summa' => $price,
				'pmUser_id' => $this->promedUserId
			);
			$result = $this->db->query($query, $queryParams);
			if ( !is_object($result) ) {
				throw new Exception('Ошибка при сохранении услуги посещения');
			}
			
			return $result->result('array');
		}
	}

	/**
	 * Сохранение прикуса
	 * @data array
	 * @throws Exception
	 *
	 */
	function saveBitePersonType($data){
		//проверка на активный прикус
		$currentPD = $this -> getCurrentBitePersonData($data);

		if($currentPD && $currentPD[0]){
			if($currentPD[0]['BitePersonType_id'] != $data['BitePersonType_id']){
				//закрываем старую запись прикуса
				$query = "
					declare
						@Res bigint,
						@ErrCode int,
						@ErrMessage varchar(4000);

					set @Res = :BitePersonData_id;

					exec dbo.p_BitePersonData_upd
						@BitePersonData_id = @Res OUTPUT,
						@Person_id = :Person_id,
						@EvnVizitPLStom_id = :EvnVizitPLStom_id,
						@BitePersonData_setDate = :BitePersonData_setDate,
						@BitePersonData_disDate = :BitePersonData_disDate,
						@BitePersonType_id = :BitePersonType_id,
						@pmUser_id = :pmUser_id,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMessage OUTPUT;
					select @Res as BitePersonData_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
				";

				$closeBiteData = $currentPD[0];
				$closeBiteData['BitePersonData_disDate'] = $data['BitePersonData_setDate'];
				$closeBiteData['pmUser_id'] = $data['pmUser_id'];
				$result = $this->db->query($query, $closeBiteData);
			}
			else{
				//уже есть данный активный прикус
				return false;
			}
		}

		//создаем новую запись прикуса
		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);

			exec dbo.p_BitePersonData_ins
				@Person_id = :Person_id,
				@EvnVizitPLStom_id = :EvnVizitPLStom_id,
				@BitePersonData_setDate = :BitePersonData_setDate,
				@BitePersonType_id = :BitePersonType_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage OUTPUT;
			select @Res as BitePersonData_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		//var_dump(getDebugSQL($query, $data)); exit;
		$result = $this->db->query($query, $data);

		if ( !is_object($result) ) {
			throw new Exception('Ошибка при сохранении услуги посещения');
		}

		return $result->result('array');

	}

	/**
	 * Проверка на активный прикус
	 * @data array
	 * @throws Exception
	 */
	function getCurrentBitePersonData($data){
		$sql = "select top 1 * from v_BitePersonData where Person_id = :Person_id and BitePersonData_disDate is null";
		$result = $this->db->query($sql, array('Person_id' => $data['Person_id']));
		if(is_object($result)){
			$response = $result->result('array');
			if(!empty($response[0])){
				return $response;
			}
		}
		return false;
	}

	/**
	 * @param string $key Ключ строчными символами
	 * @throws Exception
	 */
	protected function _beforeUpdateAttribute($key)
	{
		parent::_beforeUpdateAttribute($key);
		switch ($key) {
			case 'tooth_id':
				if (empty($this->Tooth_id)) {
					$this->setAttribute('toothsurface', null);
				}
				break;
		}
		if ($this->Tooth_id) {
			$this->load->model('EvnDiagPLStom_model', 'EvnDiagPLStom_model');
			$data = array();
			$data['Tooth_id'] = $this->Tooth_id;
			$data['Tooth_Code'] = $this->getToothCode();
			$data['ToothSurfaceType_id_list'] = $this->ToothSurface;
			$data = $this->EvnDiagPLStom_model->beforeSaveEvnDiagPLStom($data);
			$this->setAttribute('toothsurface' ,$data['json']);
		}
	}

	/**
	 * @param string $key Ключ строчными символами
	 * @throws Exception
	 */
	protected function _afterUpdateAttribute($key)
	{
		parent::_afterUpdateAttribute($key);
		switch ($key) {
			case 'toothsurface':
				$this->_saveResponse = $this->EvnDiagPLStom_model->afterSaveEvnDiagPLStom('EvnVizitPLStom', $this->_saveResponse);
				break;
		}
	}

	/**
	 * @throws Exception
	 * @return int
	 */
	function getToothCode()
	{
		if (empty($this->Tooth_id)) {
			return null;
		}
		if (false == $this->_isAttributeChanged('tooth_id')) {
			return $this->_savedData['tooth_code'];
		}
		$tooth_code = $this->getFirstResultFromQuery("
			select tooth_code
			from v_Tooth with(nolock)
			where Tooth_id = :Tooth_id
		", array('Tooth_id' => $this->Tooth_id));
		if ($tooth_code > 0) {
			return $tooth_code;
		}
		throw new Exception('Не удалось получить номер зуба', 400);
	}

	/**
	 * Используется ли код посещения
	 * @task https://redmine.swan.perm.ru/issues/41628
	 */
	function getIsUseVizitCode()
	{
		return in_array($this->regionNick, array('ufa','pskov','ekb','kz','perm'));
	}

	/**
	 * Проверки и другая логика перед удалением объекта
	 * @param array $data Массив входящих параметров
	 * @throws Exception
	 */
	protected function _beforeDelete($data = array())
	{
		parent::_beforeDelete($data);
		// удаляем все записи из ПГ в рамках посещения
		$this->load->model('Parodontogram_model');
		$tmp = $this->Parodontogram_model->doRemoveByEvn($this->id, 'EvnVizitPLStom', false);
		if (!empty($tmp[0]['Error_Msg'])) {
			throw new Exception($tmp[0]['Error_Msg'], 500);
		}
		$this->load->model('PersonToothCard_model');
		if ($this->PersonToothCard_model->isAllowEdit($this->id, $this->Person_id, $this->evnClassId)) {
			// если стомат. посещение последнее, то удаляем все записи из ЗК в рамках посещения
			$this->PersonToothCard_model->setParams(array('session'=>$this->sessionParams));
			$tmp = $this->PersonToothCard_model->doRemoveByEvn($this->id, 'EvnVizitPLStom', false);
			if (!empty($tmp[0]['Error_Msg'])) {
				throw new Exception($tmp[0]['Error_Msg'], 500);
			}
		}
	}

	/**
	 * Проверка возможности удаления стомат. заболевания
	 * Условие: не должно быть услуг в рамках удаляемого заболевания
	 */
	function checkEvnUslugaStomCount($data, $action = 'delete') {
		if ( empty($data['EvnVizitPLStom_id']) ) {
			return 'Не указан идентификатор удаляемого события';
		}

		$query = "
			select top 1 EvnUslugaStom_id
			from v_EvnUslugaStom with (nolock)
			where EvnUslugaStom_pid = :EvnVizitPLStom_id
		";
		$result = $this->db->query($query, $data);

		if ( !is_object($result) ) {
			return 'Ошибка при выполнении запроса к базе данных';
		}

		$response = $result->result('array');

		if ( is_array($response) && count($response) > 0 && !empty($response[0]['EvnUslugaStom_id']) ) {
			return 'В рамках посещения добавлены услуги, ' . ($action == 'edit' ? 'редактирование' : 'удаление') . ' невозможно';
		}

		return '';
	}

	/**
	 * Проверка возможности удаления стомат. заболевания
	 * Условие: не должно быть услуг в рамках удаляемого заболевания
	 */
	function checkEvnDiagPLStomCount($data, $action = 'delete') {
		if ( empty($data['EvnVizitPLStom_id']) ) {
			return 'Не указан идентификатор удаляемого события';
		}

		$query = "
			select top 1 EvnDiagPLStom_id
			from v_EvnDiagPLStom with (nolock)
			where EvnDiagPLStom_pid = :EvnVizitPLStom_id
		";
		$result = $this->db->query($query, $data);

		if ( !is_object($result) ) {
			return 'Ошибка при выполнении запроса к базе данных';
		}

		$response = $result->result('array');

		if ( is_array($response) && count($response) > 0 && !empty($response[0]['EvnDiagPLStom_id']) ) {
			return 'В рамках посещения добавлены заболевания, ' . ($action == 'edit' ? 'редактирование' : 'удаление') . ' невозможно';
		}

		return '';
	}

	/**
	 * Редактирование посещения из АПИ
	 */
	function editEvnVizitPLStomFromAPI($data) {
		// получаем данные посещения
		$this->applyData(array(
			'EvnVizitPLStom_id' => !empty($data['EvnVizitPLStom_id'])?$data['EvnVizitPLStom_id']:null,
			'session' => $data['session']
		));

		// подменяем параметры, пришедшие от клиента
		$this->setAttribute('setdt', $data['EvnVizitPLStom_setDate']);
		$this->setAttribute('setdate', $data['EvnVizitPLStom_setDate']);
		if (!empty($data['LpuSection_id'])) {
			$this->setAttribute('lpusection_id', $data['LpuSection_id']);
			$Lpu_id = $this->getFirstResultFromQuery("select Lpu_id from v_LpuSection (nolock) where LpuSection_id = :LpuSection_id", array('LpuSection_id' => $data['LpuSection_id']));
			if (!empty($Lpu_id)) {
				$this->setAttribute('lpu_id', $Lpu_id);
			}
		}
		if (!empty($data['EvnPLStom_id'])) {
			// данные по пациенту берем из ТАП
			$this->setAttribute('pid', $data['EvnPLStom_id']);
			$resp = $this->queryResult("
				select
					EvnPLStom_id,
					Person_id,
					PersonEvn_id,
					Server_id
				from
					v_EvnPLStom (nolock)
				where
					EvnPLStom_id = :EvnPLStom_id
			", array(
				'EvnPLStom_id' => $data['EvnPLStom_id']
			));

			if (!empty($resp[0]['EvnPLStom_id'])) {
				$this->setAttribute('person_id', $resp[0]['Person_id']);
				$this->setAttribute('personevn_id', $resp[0]['PersonEvn_id']);
				$this->setAttribute('server_id', $resp[0]['Server_id']);
			}
		}
		if (!empty($data['MedStaffFact_id'])) {
			// LpuSection - проверка на то, чтобы средний персонал работал в переданном отделении, по ТЗ
			$query = "
				select top 1 
					MedStaffFact_id,
					MedPersonal_id
				from v_MedStaffFact (nolock) where 
				MedStaffFact_id = :MedStaffFact_id 
				and LpuSection_id = :LpuSection_id
			";
			$params = array(
				'MedStaffFact_id' => $data['MedStaffFact_id'],
				'LpuSection_id' => $this->getAttribute('lpusection_id')
			);

			$MedStaffFact = $this->getFirstRowFromQuery($query, $params);
			if ($MedStaffFact === false) {
				throw new Exception('Место работы врача должно быть в указанном отделении');
			}

			$this->setAttribute('medstafffact_id', $MedStaffFact['MedStaffFact_id']);
			$this->setAttribute('medpersonal_id', $MedStaffFact['MedPersonal_id']);
		}
		if (!empty($data['TreatmentClass_id'])) {
			$this->setAttribute('treatmentclass_id', $data['TreatmentClass_id']);
		}
		if (!empty($data['ServiceType_id'])) {
			$this->setAttribute('servicetype_id', $data['ServiceType_id']);
		}
		if (!empty($data['VizitType_id'])) {
			$this->setAttribute('vizittype_id', $data['VizitType_id']);
		}
		if (!empty($data['PayType_id'])) {
			$this->setAttribute('paytype_id', $data['PayType_id']);
		}
		if (!empty($data['MesEkb_id'])) {
			$this->setAttribute('mes_id', $data['MesEkb_id']);
		}
		if (!empty($data['UslugaComplex_uid'])) {
			$this->setAttribute('uslugacomplex_id', $data['UslugaComplex_uid']);
		}
		if (!empty($data['MedicalCareKind_id'])) {
			$this->setAttribute('medicalcarekind_id', $data['MedicalCareKind_id']);
		}

		// сохраняем бирку б/з
		$this->_saveVizitFactTime();

		// сохраняем посещение
		$resp = $this->_save();

		return $resp;
	}

	/**
	 * Добавление нового посещения из ЭМК
	 */
	function addEvnVizitPLStom($data) {
		$this->load->model('EPH_model');
		$resp = $this->EPH_model->loadEvnPLStomForm($data);
		if (empty($resp[0]['accessType'])) {
			return array('Error_Msg' => 'Ошибка получения информации о случае АПЛ');
		} else if ($resp[0]['accessType'] == 'view') {
			if (!empty($resp[0]['AlertReg_Msg'])) {
				return array('Error_Msg' => $resp[0]['AlertReg_Msg']);
			} else if (empty($resp[0]['canCreateVizit'])) {
				return array('Error_Msg' => 'Случай АПЛ недоступен для редактирования');
			}
		}

		// получаем данные предыдущего посещения
		$query = "
			select top 1 
				EVPLS.EvnVizitPLStom_id,
				EX.EvnXml_id
			from 
				v_EvnVizitPLStom EVPLS with(nolock)
				outer apply (
					select top 1 EX.EvnXml_id
					from v_EvnXml EX with(nolock)
					where EX.Evn_id = EVPLS.EvnVizitPLStom_id and EX.XmlType_id = 3
					order by EX.EvnXml_insDT desc
				) EX
			where 
				EvnVizitPLStom_pid = :EvnPLStom_id 
			order by 
				EvnVizitPLStom_setDT desc
		";
		$prev = $this->getFirstRowFromQuery($query, $data);
		if (!is_array($prev)) {
			return array('Error_Msg' => 'Не удалось определить предыдущее движение');
		}

		//Проверка на второе посещение НМП
		$this->_controlDoubleNMP($data['EvnPLStom_id'], null, true);

		$this->applyData(array(
			'EvnVizitPLStom_id' => $prev['EvnVizitPLStom_id'],
			'copyEvnXml_id' => $prev['EvnXml_id'],
			'allowCreateEmptyEvnDoc' => 2,
			'session' => $data['session'],
		));

		$dt = date_create();

		// убираем лишние параметры
		$this->setParams($data);
		$this->setAttribute('id', null);
		$this->setAttribute('setdt', $dt);
		$this->setAttribute('setdate', $dt->format('Y-m-d'));
		$this->setAttribute('setTime', $dt->format('H:i'));
		$this->setAttribute('uslugacomplex_id', null);
		$this->setAttribute('medstafffact_id', $data['MedStaffFact_id']);
		if (!empty($data['LpuSection_id'])) {
			$this->setAttribute('lpusection_id', $data['LpuSection_id']);
		}
		if (!empty($data['MedPersonal_id'])) {
			$this->setAttribute('medpersonal_id', $data['MedPersonal_id']);
		}
		$this->setAttribute('timetablegraf_id', (!empty($data['TimetableGraf_id']))?$data['TimetableGraf_id']:null);
		$this->setAttribute('evndirection_id', (!empty($data['EvnDirection_id']))?$data['EvnDirection_id']:null);
		$this->setScenario(self::SCENARIO_AUTO_CREATE);
		$this->isNewRecord = true;

		// сохраняем посещение
		$this->beginTransaction();

		try {
			$this->_beforeSave();
			$resp = $this->_save();
			$this->setAttribute('id', $resp[0]['EvnVizitPLStom_id']);
			$this->_afterSave($resp);
		} catch(Exception $e) {
			$this->rollbackTransaction();
			if ($e->getMessage() == 'YesNo') {
				return array('success'=>false, 'Error_Code'=>$e->getCode(), 'Error_Msg'=>'YesNo', 'Alert_Msg'=>$this->_saveResponse['Alert_Msg'], 'ignoreParam'=>$this->_saveResponse['ignoreParam']);
			}
			throw $e;
		}

		$this->commitTransaction();

		return $resp;
	}

    /**
     * Сохранение схем лекарственной терапии
     */
    protected function saveUslugaMedTypeLink()
    {
        if (getRegionNick() === 'kz') {
            $this->load->model('UslugaMedType_model');

            $result = $this->UslugaMedType_model->saveUslugaMedTypeLink([
                'UslugaMedType_id' => $this->_params['UslugaMedType_id'],
                'Evn_id' => $this->id,
                'pmUser_id' => $this->promedUserId
            ]);

            if (!$this->isSuccessful($result)) {
                throw new Exception($result[0]['Error_Msg'], $result[0]['Error_Code']);
            }
        }
    }
	
	/**
	 * Установка первичного посещения
	 * @param type $data
	 */
	function set_IsPrimaryVizit($data = array()){
		if(!empty($this->setDate) && !empty($this->Person_id) && !empty($this->VizitType_id) && $this->scenario == self::SCENARIO_AUTO_CREATE /*&& in_array(getRegionNick(), array('kareliya'))*/){
			//Ищем колличество посещений в этом году
			$year = date('Y', strtotime($this->setDate));
			$query = "
				select
					COUNT(EVPLS.EvnVizitPLStom_id) AS countEvnVizitPLStom
				from 
					v_EvnPLStom EPLS
					LEFT JOIN v_EvnVizitPLStom EVPLS with(nolock) ON EPLS.EvnPLStom_id = EVPLS.EvnVizitPLStom_pid
				where 1=1
					and EPLS.EvnClass_id = 6 
					AND ISNULL(EPLS.EvnPLStom_IsArchive, 1) = 1
					AND EPLS.Person_id = :Person_id
					AND YEAR(EPLS.EvnPLStom_setDate) = :Year
					AND EVPLS.VizitType_id = :VizitType_id
					AND EPLS.EvnPLStom_setDate < :setDate
			";
			$res = $this->getFirstRowFromQuery($query, array(
				'Person_id' => $this->Person_id, 
				'VizitType_id' => $this->VizitType_id, 
				'Year' => $year, 
				'setDate' => $this->setDate)
			);
			$isprimaryvizit = ($res['countEvnVizitPLStom']>0) ? 1 : 2;
			$this->setAttribute('isprimaryvizit', $isprimaryvizit);
		}
	}
}