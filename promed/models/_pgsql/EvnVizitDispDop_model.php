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
					select
						movpld.MorbusOnkoVizitPLDop_id as \"MorbusOnkoVizitPLDop_id\"
					from v_EvnVizitDispDop evdd
					inner join v_Diag Diag on Diag.Diag_id = evdd.Diag_id
					inner join v_MorbusOnkoVizitPLDop movpld on movpld.EvnVizit_id = evdd.EvnVizitDispDop_id
					where evdd.EvnVizitDispDop_id = :id
						and not ((Diag.Diag_Code >= 'C00' AND Diag.Diag_Code <= 'C97') or (Diag.Diag_Code >= 'D00' AND Diag.Diag_Code <= 'D09'))
					limit 1
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
						select
							evpl.EvnVizitDispDop_id as \"EvnVizitDispDop_id\"
						from v_EvnVizitDispDop evpl
						inner join v_Diag Diag on Diag.Diag_id = evpl.Diag_id
						left join v_MorbusOnkoVizitPLDop movpld on movpld.EvnVizit_id = evpl.EvnVizitDispDop_id
						where 
							evpl.EvnVizitDispDop_id = :EvnVizit_id
							and ((left(Diag.Diag_Code, 3) >= 'C00' AND left(Diag.Diag_Code, 3) <= 'C97') or (left(Diag.Diag_Code, 3) >= 'D00' AND left(Diag.Diag_Code, 3) <= 'D09'))
							and movpld.MorbusOnkoVizitPLDop_id is null
							/*and (
								movpld.MorbusOnkoVizitPLDop_id is null or 
								not exists (select MorbusOnkoLink_id from v_MorbusOnkoLink MOL where movpld.MorbusOnkoVizitPLDop_id = MOL.MorbusOnkoVizitPLDop_id limit 1)
							)*/
							and not(
								dbo.getRegion() = '91'
								and coalesce(evpl.EvnVizitDispDop_IsInRegZNO, 1) = 2
							)
						limit 1
					", array('EvnVizit_id' => $this->id));
			$mo_chk2 = $this->getFirstResultFromQuery("
						select
							evpl.EvnVizitDispDop_id as \"EvnVizitDispDop_id\"
						from v_EvnVizitDispDop evpl
						inner join v_Diag Diag on Diag.Diag_id = evpl.Diag_id
						inner join v_EvnDiagPLSop eds on eds.EvnDiagPLSop_pid = evpl.EvnVizitDispDop_id
						inner join v_Diag DiagS on DiagS.Diag_id = eds.Diag_id
						left join v_MorbusOnkoVizitPLDop movpld on movpld.EvnVizit_id = evpl.EvnVizitDispDop_id
							and movpld.EvnDiagPLSop_id = eds.EvnDiagPLSop_id
						where 
							evpl.EvnVizitDispDop_id = :EvnVizit_id
							and (((left(DiagS.Diag_Code, 3) >= 'C00' AND left(DiagS.Diag_Code, 3) <= 'C80') or left(DiagS.Diag_Code, 3) = 'C97') and (left(Diag.Diag_Code, 3) = 'D70'))
							/*and (
								movpld.MorbusOnkoVizitPLDop_id is null or 
								not exists (select MorbusOnkoLink_id from v_MorbusOnkoLink MOL where movpld.MorbusOnkoVizitPLDop_id = MOL.MorbusOnkoVizitPLDop_id limit 1)
							)*/
							and movpld.MorbusOnkoVizitPLDop_id is null
							and not(
								dbo.getRegion() = '91'
								and coalesce(evpl.EvnVizitDispDop_IsInRegZNO, 1) = 2
							)
						limit 1
					", array('EvnVizit_id' => $this->id));
			if (!empty($mo_chk) || !empty($mo_chk2)) {
				return array(
					//'openSpecificAfterSave' => true,
					'Alert_Msg' => 'В случае лечения установлен диагноз из диапазона С00-C97 или D00-D09. Заполните раздел "Специфика (онкология)" или проверьте корректность заполнения обязательных полей данного раздела: «Повод обращения», «Стадия опухолевого процесса», «Т», «N», «M» (Стадия опухолевого процесса по системе TNM) в блоках ФОМС и Канцер регистр. Обязательные поля раздела отмечены символом *.'
				);
			}

			$mo_chk = $this->getFirstRowFromQuery("
				select
					evpl.EvnVizitDispDop_setDate as \"filterDate\",
					evpl.Diag_id as \"Diag_id\",
					evpl.EvnVizitDispDop_IsInRegZNO as \"EvnVizitDispDop_IsInRegZNO\",
					MOVD.MorbusOnkoVizitPLDop_id as \"MorbusOnkoVizitPLDop_id\",
					MOVD.Diag_id as \"Diag_id\",
					MOVD.OnkoRegOutType_id as \"OnkoRegOutType_id\",
					MOVD.OnkoStatusYearEndType_id as \"OnkoStatusYearEndType_id\",
					MOVD.OnkoInvalidType_id as \"OnkoInvalidType_id\",
					MOVD.OnkoDiag_id as \"OnkoDiag_id\",
					MOVD.MorbusOnkoVizitPLDop_MorfoDiag as \"MorbusOnkoVizitPLDop_MorfoDiag\",
					MOVD.OnkoT_id as \"OnkoT_id\",
					MOVD.OnkoN_id as \"OnkoN_id\",
					MOVD.OnkoM_id as \"OnkoM_id\",
					MOVD.TumorStage_id as \"TumorStage_id\",
					MOVD.MorbusOnkoVizitPLDop_IsTumorDepoUnknown as \"MorbusOnkoVizitPLDop_IsTumorDepoUnknown\",
					MOVD.MorbusOnkoVizitPLDop_IsTumorDepoLympha as \"MorbusOnkoVizitPLDop_IsTumorDepoLympha\",
					MOVD.MorbusOnkoVizitPLDop_IsTumorDepoBones as \"MorbusOnkoVizitPLDop_IsTumorDepoBones\",
					MOVD.MorbusOnkoVizitPLDop_IsTumorDepoLiver as \"MorbusOnkoVizitPLDop_IsTumorDepoLiver\",
					MOVD.MorbusOnkoVizitPLDop_IsTumorDepoLungs as \"MorbusOnkoVizitPLDop_IsTumorDepoLungs\",
					MOVD.MorbusOnkoVizitPLDop_IsTumorDepoBrain as \"MorbusOnkoVizitPLDop_IsTumorDepoBrain\",
					MOVD.MorbusOnkoVizitPLDop_IsTumorDepoSkin as \"MorbusOnkoVizitPLDop_IsTumorDepoSkin\",
					MOVD.MorbusOnkoVizitPLDop_IsTumorDepoKidney as \"MorbusOnkoVizitPLDop_IsTumorDepoKidney\",
					MOVD.MorbusOnkoVizitPLDop_IsTumorDepoOvary as \"MorbusOnkoVizitPLDop_IsTumorDepoOvary\",
					MOVD.MorbusOnkoVizitPLDop_IsTumorDepoPerito as \"MorbusOnkoVizitPLDop_IsTumorDepoPerito\",
					MOVD.MorbusOnkoVizitPLDop_IsTumorDepoMarrow as \"MorbusOnkoVizitPLDop_IsTumorDepoMarrow\",
					MOVD.MorbusOnkoVizitPLDop_IsTumorDepoOther as \"MorbusOnkoVizitPLDop_IsTumorDepoOther\",
					MOVD.MorbusOnkoVizitPLDop_IsTumorDepoMulti as \"MorbusOnkoVizitPLDop_IsTumorDepoMulti\",
					MOVD.MorbusOnkoVizitPLDop_deadDT as \"MorbusOnkoVizitPLDop_deadDT\",
					MOVD.Diag_did as \"Diag_did\",
					MOVD.MorbusOnkoVizitPLDop_deathCause as \"MorbusOnkoVizitPLDop_deathCause\",
					MOVD.AutopsyPerformType_id as \"AutopsyPerformType_id\",
					MOVD.TumorAutopsyResultType_id as \"TumorAutopsyResultType_id\",
					MOVD.MorbusOnkoVizitPLDop_setDT as \"MorbusOnkoVizitPLDop_setDT\",
					MOVD.MedPersonal_id as \"MedPersonal_id\",
					MOVD.MorbusOnkoBasePersonState_id as \"MorbusOnkoBasePersonState_id\",
					MOVD.pmUser_insID as \"pmUser_insID\",
					MOVD.pmUser_updID as \"pmUser_updID\",
					MOVD.MorbusOnkoVizitPLDop_insDT as \"MorbusOnkoVizitPLDop_insDT\",
					MOVD.MorbusOnkoVizitPLDop_updDT as \"MorbusOnkoVizitPLDop_updDT\",
					MOVD.MorbusOnkoBaseLateComplTreat_id as \"MorbusOnkoBaseLateComplTreat_id\",
					MOVD.OnkoTumorStatusType_id as \"OnkoTumorStatusType_id\",
					MOVD.OnkoDiagConfType_id as \"OnkoDiagConfType_id\",
					MOVD.OnkoLateComplTreatType_id as \"OnkoLateComplTreatType_id\",
					MOVD.OnkoCombiTreatType_id as \"OnkoCombiTreatType_id\",
					MOVD.MorbusOnkoVizitPLDop_NumTumor as \"MorbusOnkoVizitPLDop_NumTumor\",
					MOVD.OnkoLesionSide_id as \"OnkoLesionSide_id\",
					MOVD.MorbusOnkoVizitPLDop_NumHisto as \"MorbusOnkoVizitPLDop_NumHisto\",
					MOVD.TumorCircumIdentType_id as \"TumorCircumIdentType_id\",
					MOVD.OnkoLateDiagCause_id as \"OnkoLateDiagCause_id\",
					MOVD.TumorPrimaryTreatType_id as \"TumorPrimaryTreatType_id\",
					MOVD.TumorRadicalTreatIncomplType_id as \"TumorRadicalTreatIncomplType_id\",
					MOVD.OnkoDiag_mid as \"OnkoDiag_mid\",
					MOVD.OnkoPostType_id as \"OnkoPostType_id\",
					MOVD.DiagAttribType_id as \"DiagAttribType_id\",
					MOVD.DiagAttribDict_id as \"DiagAttribDict_id\",
					MOVD.DiagResult_id as \"DiagResult_id\",
					MOVD.MorbusOnkoVizitPLDop_specSetDT as \"MorbusOnkoVizitPLDop_specSetDT\",
					MOVD.MorbusOnkoVizitPLDop_specDisDT as \"MorbusOnkoVizitPLDop_specDisDT\",
					MOVD.MorbusOnkoVizitPLDop_setDiagDT as \"MorbusOnkoVizitPLDop_setDiagDT\",
					MOVD.MorbusOnkoVizitPLDop_IsMainTumor as \"MorbusOnkoVizitPLDop_IsMainTumor\",
					MOVD.DiagAttribDict_fid as \"DiagAttribDict_fid\",
					MOVD.DiagResult_fid as \"DiagResult_fid\",
					MOVD.OnkoTreatment_id as \"OnkoTreatment_id\",
					MOVD.EvnDiagPLSop_id as \"EvnDiagPLSop_id\",
					MOVD.MorbusOnkoVizitPLDop_FirstSignDT as \"MorbusOnkoVizitPLDop_FirstSignDT\",
					MOVD.TumorPrimaryMultipleType_id as \"TumorPrimaryMultipleType_id\",
					MOVD.HistologicReasonType_id as \"HistologicReasonType_id\",
					MOVD.MorbusOnkoVizitPLDop_histDT as \"MorbusOnkoVizitPLDop_histDT\",
					MOVD.OnkoT_fid as \"OnkoT_fid\",
					MOVD.OnkoN_fid as \"OnkoN_fid\",
					MOVD.OnkoM_fid as \"OnkoM_fid\",
					MOVD.TumorStage_fid as \"TumorStage_fid\",
					MOVD.EvnVizit_id as \"EvnVizit_id\",
					to_char(MOVD.MorbusOnkoVizitPLDop_takeDT, 'dd.mm.yyyy') as \"MorbusOnko_takeDT\",
					OT.OnkoTreatment_id as \"OnkoTreatment_id\",
					OT.OnkoTreatment_Code as \"OnkoTreatment_Code\",
					dbo.Age2(PS.Person_Birthday, evpl.EvnVizitDispDop_setDT) as \"Person_Age\",
					MorbusOnkoLink.MorbusOnkoLink_id as \"MorbusOnkoLink_id\",
					null as \"OnkoConsult_id\"
				from v_EvnVizitDispDop evpl
					inner join v_Person_all PS on PS.PersonEvn_id = evpl.PersonEvn_id
						and PS.Server_id = evpl.Server_id
					inner join v_Diag Diag on Diag.Diag_id = evpl.Diag_id
					inner join v_MorbusOnkoVizitPLDop MOVD on MOVD.EvnVizit_id = evpl.EvnVizitDispDop_id
					left join v_OnkoTreatment OT on OT.OnkoTreatment_id = MOVD.OnkoTreatment_id
					left join lateral (
						SELECT
							MorbusOnkoLink_id
						FROM
							v_MorbusOnkoLink
						WHERE
							MorbusOnkoVizitPLDop_id = MOVD.MorbusOnkoVizitPLDop_id
						limit 1
					) as MorbusOnkoLink on true
				where
					evpl.EvnVizitDispDop_id = :EvnVizitDispDop_id
					and ((left(Diag.Diag_Code, 3) >= 'C00' AND left(Diag.Diag_Code, 3) <= 'C97') or (left(Diag.Diag_Code, 3) >= 'D00' AND left(Diag.Diag_Code, 3) <= 'D09'))
				limit 1
			", array('EvnVizitDispDop_id' => $this->id));

			if (!empty($mo_chk)) {

				if (
					empty($mo_chk['OnkoTreatment_id'])
					/*|| (
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
									Diag_id as \"Diag_id\",
									{$field}_fid as \"{$field}_fid\",
									{$field}Link_begDate as \"{$field}Link_begDate\",
									{$field}Link_endDate as \"{$field}Link_endDate\"
								from
									dbo.v_{$field}Link
								where
									Diag_id = :Diag_id
									and (
										{$field}Link_begDate is null
										or {$field}Link_begDate <= cast(:FilterDate as date)
									)
								union all
								select
									Diag_id as \"Diag_id\",
									{$field}_fid as \"{$field}_fid\",
									{$field}Link_begDate as \"{$field}Link_begDate\",
									{$field}Link_endDate as \"{$field}Link_endDate\"
								from
									dbo.v_{$field}Link
								where
									Diag_id is null
									and (
										{$field}Link_begDate is null
										or {$field}Link_begDate <= cast(:FilterDate as date)
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
