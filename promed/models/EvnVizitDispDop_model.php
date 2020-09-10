<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* EvnUslugaDispDop_model - модель для работы с услугами дд
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
* */

require_once('EvnVizitAbstract_model.php');

/**
* @package      Polka
* @access       public
* @copyright    Copyright (c) 2009-2019 Swan Ltd.
* @author       Sobenin Alexander
* @version      08.2019
*
*/

class EvnVizitDispDop_model extends EvnVizitAbstract_model
{

	public $ignoreCheckMorbusOnko = null;

	/**
	 * Конструктор объекта
	 */
	function __construct()
	{
		parent::__construct();
		$this->_setScenarioList(array(
			self::SCENARIO_AUTO_CREATE,
			self::SCENARIO_DO_SAVE,
			self::SCENARIO_SET_ATTRIBUTE,
			self::SCENARIO_DELETE,
		));
	}

	/**
	 * Возвращает список всех используемых ключей атрибутов объекта
	 * @return array
	 */
	static function defAttributes()
	{
		$arr = parent::defAttributes();
		$arr[self::ID_KEY]['alias'] = 'EvnVizitDispDop_id';
		$arr['pid']['alias'] = 'EvnVizitDispDop_pid';
		$arr['setdate']['alias'] = 'EvnVizitDispDop_setDate';
		$arr['settime']['alias'] = 'EvnVizitDispDop_setTime';
		$arr['disdt']['alias'] = 'EvnVizitDispDop_disDT';
		$arr['diddt']['alias'] = 'EvnVizitDispDop_didDT';
		$arr['statusdate']['alias'] = 'EvnVizitDispDop_statusDate';
		$arr['isinreg']['alias'] = 'EvnVizitDispDop_IsInReg';
		$arr['ispaid']['alias'] = 'EvnVizitDispDop_IsPaid';
		$arr['uet']['alias'] = 'EvnVizitDispDop_Uet';
		$arr['uetoms']['alias'] = 'EvnVizitDispDop_UetOMS';

		$arr['lpudispcontract_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'LpuDispContract_id',
			'label' => 'По договору',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['diag_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'Diag_id',
			'label' => 'Основной диагноз',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['healthkind_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'HealthKind_id',
			'label' => 'Группа здоровья',
			'save' => 'trim',
			'type' => 'id',
			'updateTable' => 'EvnVizitDispDop'
		);
		$arr['lpusectionprofile_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'LpuSectionProfile_id',
			'label' => 'Профиль посещения',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['uslugacomplex_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'UslugaComplex_uid',
			'label' => 'Код посещения',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['tumorstage_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'TumorStage_id',
			'label' => 'Стадия выявленного ЗНО',
			'save' => 'trim',
			'type' => 'id',
			'updateTable' => 'EvnVizitPL'
		);

		return $arr;
	}

	/**
	 * Определение идентификатора класса события
	 * @return int
	 */
	static function evnClassId()
	{
		return 14;
	}

	/**
	 * Определение кода класса события
	 * @return string
	 */
	static function evnClassSysNick()
	{
		return 'EvnVizitDispDop';
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
		$this->_params['ignoreCheckMorbusOnko'] = empty($data['ignoreCheckMorbusOnko']) ? false : true;
	}

	/**
	 * Определение кода класса события
	 * @throws Exception
	 */
	function onAfterSaveEvn($data){
		$this->setParams($data);
		$this->setAttributes($data);

		$this->_updateMorbus();

		// Удаление специфики, если есть параметр
		if (!empty($data['ignoreCheckMorbusOnko'])) {
			$query = "
					select top 1 movpld.MorbusOnkoVizitPLDop_id
					from v_EvnVizitDispDop evdd (nolock)
					inner join v_Diag Diag (nolock) on Diag.Diag_id = evdd.Diag_id
					inner join v_MorbusOnkoVizitPLDop movpld (nolock) on movpld.EvnVizit_id = evdd.EvnVizitDispDop_id
					where evdd.EvnVizitDispDop_id = :id
					and not ((Diag.Diag_Code >= 'C00' AND Diag.Diag_Code <= 'C97') or (Diag.Diag_Code >= 'D00' AND Diag.Diag_Code <= 'D09'))
				";
			//echo getDebugSQL($query, array('id' => $this->id));exit;
			$MorbusOnkoVizitPLDop_id = $this->getFirstResultFromQuery($query, array('id' => $data['EvnVizitDispDop_id']), true);
			if ($MorbusOnkoVizitPLDop_id === false) {
				throw new Exception('Ошибка при проверке талона дополнений больного ЗНО');
			}
			if (!empty($MorbusOnkoVizitPLDop_id)) {
				$this->load->model('MorbusOnkoVizitPLDop_model');
				$resp = $this->MorbusOnkoVizitPLDop_model->delete(array(
					'MorbusOnkoVizitPLDop_id' => $MorbusOnkoVizitPLDop_id,
					'pmUser_id' => $this->promedUserId
				));
				if (!$this->isSuccessful($resp)) {
					throw new Exception($resp[0]['Error_Msg']);
				}
				$data['deletedMorbusOnkoVizitPLDop_id'] = $MorbusOnkoVizitPLDop_id;
			}
		}
	}
	/**
	 * Проверка возможности изменения основного диагноза посещения
	 * @throws Exception
	 */
	function checkChangeDiag($data)
	{
		// Если диагноз не отличается от сохраненного (не меняется), проверять нечего
		if($data['saved_Diag_id'] !== $data['Diag_id']){
			$this->setParams($data);
			$this->setAttributes($data);

			$this->ignoreCheckMorbusOnko = $this->_params['ignoreCheckMorbusOnko'];
			$this->load->library('swMorbus');
			$tmp = swMorbus::onBeforeChangeDiag($this);
			if ($tmp !== true && isset($tmp['Alert_Msg'])) {
				return array(
					'Error_Code'=> 289,
					'ignoreParam' => 'ignoreCheckMorbusOnko',
					'Alert_Msg' => $tmp['Alert_Msg'] // 'При изменении диагноза данные раздела «Специфика (онкология)», связанные с текущим диагнозом, будут удалены.'
				);
			}
			$this->load->library('swPersonRegister');
			$tmp = swPersonRegister::onBeforeChangeDiag($this);
			return $tmp;
		} else {
			return true;
		}
	}

	/**
	 * Проверка корректности заполнения обязательных полей в онкоспецифике
	 */
	function checkOnkoSpecifics($data) {
		if ( $this->regionNick != 'perm' ) {
			return true;
		}

		$this->setParams($data);
		$this->setAttributes($data);

		if (!empty($this->id)) {

			$mo_chk = $this->getFirstResultFromQuery("
						select top 1 evpl.EvnVizitDispDop_id
						from v_EvnVizitDispDop evpl (nolock)
						inner join v_Diag Diag (nolock) on Diag.Diag_id = evpl.Diag_id
						left join v_MorbusOnkoVizitPLDop movpld (nolock) on movpld.EvnVizit_id = evpl.EvnVizitDispDop_id
						where 
							evpl.EvnVizitDispDop_id = :EvnVizit_id
							and ((left(Diag.Diag_Code, 3) >= 'C00' AND left(Diag.Diag_Code, 3) <= 'C97') or (left(Diag.Diag_Code, 3) >= 'D00' AND left(Diag.Diag_Code, 3) <= 'D09'))
							and movpld.MorbusOnkoVizitPLDop_id is null
							/*and (
								movpld.MorbusOnkoVizitPLDop_id is null or 
								not exists (select top 1 MorbusOnkoLink_id from v_MorbusOnkoLink MOL (nolock) where movpld.MorbusOnkoVizitPLDop_id = MOL.MorbusOnkoVizitPLDop_id)
							)*/
							and movpld.MorbusOnkoVizitPLDop_id is null
							and not(
								dbo.getRegion() = '91'
								and isnull(evpl.EvnVizitDispDop_IsInRegZNO, 1) = 2
							)
					", array('EvnVizit_id' => $this->id));
			$mo_chk2 = $this->getFirstResultFromQuery("
						select top 1 evpl.EvnVizitDispDop_id
						from v_EvnVizitDispDop evpl (nolock)
						inner join v_Diag Diag (nolock) on Diag.Diag_id = evpl.Diag_id
						inner join v_EvnDiagPLSop eds (nolock) on eds.EvnDiagPLSop_pid = evpl.EvnVizitDispDop_id
						inner join v_Diag DiagS (nolock) on DiagS.Diag_id = eds.Diag_id
						left join v_MorbusOnkoVizitPLDop movpld (nolock) on movpld.EvnVizit_id = evpl.EvnVizitDispDop_id and movpld.EvnDiagPLSop_id = eds.EvnDiagPLSop_id
						where 
							evpl.EvnVizitDispDop_id = :EvnVizit_id
							and (((left(DiagS.Diag_Code, 3) >= 'C00' AND left(DiagS.Diag_Code, 3) <= 'C80') or left(DiagS.Diag_Code, 3) = 'C97') and (left(Diag.Diag_Code, 3) = 'D70'))
							/*and (
								movpld.MorbusOnkoVizitPLDop_id is null or 
								not exists (select top 1 MorbusOnkoLink_id from v_MorbusOnkoLink MOL (nolock) where movpld.MorbusOnkoVizitPLDop_id = MOL.MorbusOnkoVizitPLDop_id)
							)*/
							and movpld.MorbusOnkoVizitPLDop_id is null
							and not(
								dbo.getRegion() = '91'
								and isnull(evpl.EvnVizitDispDop_IsInRegZNO, 1) = 2
							)
					", array('EvnVizit_id' => $this->id));
			if (!empty($mo_chk) || !empty($mo_chk2)) {
				return array(
					//'openSpecificAfterSave' => true,
					'Alert_Msg' => 'В случае лечения установлен диагноз из диапазона С00-C97 или D00-D09. Заполните раздел "Специфика (онкология)" или проверьте корректность заполнения обязательных полей данного раздела: «Повод обращения», «Стадия опухолевого процесса», «Т», «N», «M» (Стадия опухолевого процесса по системе TNM) в блоках ФОМС и Канцер регистр. Обязательные поля раздела отмечены символом *.'
				);
			}

//			if (empty($this->_params['ignoreMorbusOnkoDrugCheck'])) {
//				$rslt = $this->getFirstResultFromQuery("
//							select top 1 MorbusOnkoDrug_id
//							from v_MorbusOnkoDrug with (nolock)
//							where Evn_id = :EvnVizitPL_id
//						", array('EvnVizit_id' => $this->id), true);
//				if (!empty($rslt)) {
//					$this->_saveResponse['ignoreParam'] = "ignoreMorbusOnkoDrugCheck";
//					$this->_saveResponse['Alert_Msg'] = "В разделе «Данные о препаратах» остались препараты, не связанные с лечением. Продолжить сохранение?";
//					throw new Exception('YesNo', 106);
//				}
//			}

			$mo_chk = $this->getFirstRowFromQuery("
				select top 1
					evpl.EvnVizitDispDop_setDate as filterDate,
					evpl.Diag_id,
					evpl.EvnVizitDispDop_IsInRegZNO,
					MOVD.*,
					convert(varchar(10), MOVD.MorbusOnkoVizitPLDop_takeDT, 104) as MorbusOnko_takeDT,
					OT.OnkoTreatment_id,
					OT.OnkoTreatment_Code,
					dbo.Age2(PS.Person_Birthday, evpl.EvnVizitDispDop_setDT) as Person_Age,
					MorbusOnkoLink.MorbusOnkoLink_id,
					null as OnkoConsult_id
				from v_EvnVizitDispDop evpl (nolock)
					inner join v_Person_all PS with (nolock) on PS.PersonEvn_id = evpl.PersonEvn_id and PS.Server_id = evpl.Server_id
					inner join v_Diag Diag (nolock) on Diag.Diag_id = evpl.Diag_id
					inner join v_MorbusOnkoVizitPLDop MOVD (nolock) on MOVD.EvnVizit_id = evpl.EvnVizitDispDop_id
					left join v_OnkoTreatment OT with (nolock) on OT.OnkoTreatment_id = MOVD.OnkoTreatment_id
					outer apply(
						SELECT top 1
							MorbusOnkoLink_id
						FROM
							v_MorbusOnkoLink WITH (nolock)
						WHERE
							MorbusOnkoVizitPLDop_id = MOVD.MorbusOnkoVizitPLDop_id
					) as MorbusOnkoLink
				where
					evpl.EvnVizitDispDop_id = :EvnVizitDispDop_id
					and ((left(Diag.Diag_Code, 3) >= 'C00' AND left(Diag.Diag_Code, 3) <= 'C97') or (left(Diag.Diag_Code, 3) >= 'D00' AND left(Diag.Diag_Code, 3) <= 'D09'))
			", array('EvnVizitDispDop_id' => $this->id));

			if (!empty($mo_chk)) {

				if (
					empty($mo_chk['OnkoTreatment_id'])
					/*#192967
					|| (
						empty($mo_chk['MorbusOnkoLink_id']) && empty($mo_chk['HistologicReasonType_id'])
					)*/
					|| (
						empty($mo_chk['TumorStage_fid']) && !empty($mo_chk['OnkoTreatment_id']) && $mo_chk['OnkoTreatment_Code'] != 5 && $mo_chk['OnkoTreatment_Code'] != 6
					)
					|| (
					empty($mo_chk['TumorStage_id'])
					)
					|| (
						!empty($mo_chk['HistologicReasonType_id'])
						&& empty($mo_chk['MorbusOnkoVizitPLDop_histDT'])
					)

				) {
					return array(
						//'openSpecificAfterSave' => true,
						'Alert_Msg' => 'В случае лечения установлен диагноз из диапазона С00-C97 или D00-D09. Заполните раздел "Специфика (онкология)" или проверьте корректность заполнения обязательных полей данного раздела: «Повод обращения», «Стадия опухолевого процесса», «Т», «N», «M» (Стадия опухолевого процесса по системе TNM) в блоках ФОМС и Канцер регистр. Обязательные поля раздела отмечены символом *.'
					);
				}

				$onkoFields = array('OnkoT', 'OnkoN', 'OnkoM');
				foreach ( $onkoFields as $field ) {
					if ( empty($mo_chk[$field . '_id']) ) {
						return array(
							//'openSpecificAfterSave' => true,
							'Alert_Msg' => 'В случае лечения установлен диагноз из диапазона С00-C97 или D00-D09. Заполните раздел "Специфика (онкология)" или проверьте корректность заполнения обязательных полей данного раздела: «Повод обращения», «Стадия опухолевого процесса», «Т», «N», «M» (Стадия опухолевого процесса по системе TNM) в блоках ФОМС и Канцер регистр. Обязательные поля раздела отмечены символом *.'
						);
					}
				}

				$onkoFields = array();

				if ( $mo_chk['OnkoTreatment_Code'] === 0 && $mo_chk['Person_Age'] >= 18 ) {
					$onkoFields[] = 'OnkoT';
					$onkoFields[] = 'OnkoN';
					$onkoFields[] = 'OnkoM';
				}

				foreach ( $onkoFields as $field ) {
					if ( !empty($mo_chk[$field . '_fid']) ) {
						continue;
					}

					$param1 = false; // Есть связка с диагнозом и OnkoT_id is not null
					$param2 = false; // Есть связка с диагнозом и OnkoT_id is null
					$param3 = false; // Нет связки с диагнозом и есть записи с Diag_id is null

					$LinkData = $this->queryResult("
								select 
									Diag_id, 
									{$field}_fid, 
									{$field}Link_begDate, 
									{$field}Link_endDate 
								from 
									dbo.v_{$field}Link with (nolock) 
								where 
									Diag_id = :Diag_id 
									and (
										{$field}Link_begDate is null
										or {$field}Link_begDate <= :FilterDate
									)
								union all
								select 
									Diag_id, 
									{$field}_fid, 
									{$field}Link_begDate, 
									{$field}Link_endDate 
								from 
									dbo.v_{$field}Link with (nolock) 
								where 
									Diag_id is null
									and (
										{$field}Link_begDate is null
										or {$field}Link_begDate <= :FilterDate
									)

							", array(
						'Diag_id' => $mo_chk['Diag_id'],
						'FilterDate' => $mo_chk['filterDate']
					));

					if ( $LinkData !== false ) {
						foreach ( $LinkData as $row ) {
							if ( !empty($row['Diag_id']) && $row['Diag_id'] == $mo_chk['Diag_id'] ) {
								if ( !empty($row[$field . '_fid']) ) {
									$param1 = true;
								}
								else {
									$param2 = true;
								}
							}
							else if ( empty($row['Diag_id']) ) {
								$param3 = true;
							}
						}
					}

					if ( $param1 == true || ($param3 == true && $param2 == false) ) {
						return array(
							//'openSpecificAfterSave' => true,
							'Alert_Msg' => 'В случае лечения установлен диагноз из диапазона С00-C97 или D00-D09. Заполните раздел "Специфика (онкология)" или проверьте корректность заполнения обязательных полей данного раздела: «Повод обращения», «Стадия опухолевого процесса», «Т», «N», «M» (Стадия опухолевого процесса по системе TNM) в блоках ФОМС и Канцер регистр. Обязательные поля раздела отмечены символом *.'
						);
					}
				}
			}
		}

		return true;
	}
}
?>