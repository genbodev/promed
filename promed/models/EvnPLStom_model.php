<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 *
 * @package      PromedWeb
 * @access       public
 * @copyright    Copyright (c) 2014 Swan Ltd.
 * @link         http://swan.perm.ru/PromedWeb
 */
require_once('EvnPL_model.php');
/**
 * EvnPLStom_model - Лечение в стоматологии
 *
 * Содержит методы и свойства общие для всех объектов,
 * классы которых наследуют класс EvnPLStom
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2009-2014 Swan Ltd.
 * @author       Александр Пермяков
 * @version      08.2014
 *
 * @property int $IsSan
 * @property int $SanationStatus_id
 *
 * @property PersonIdentRequest_model $identmodel
 * @property EvnDiagPLStom_model $EvnDiagPLStom_model
 * @property EvnVizit_model $EvnVizit_model
 * @property TimetableGraf_model $ttgmodel
 * @property Org_model $orgmodel
 */
class EvnPLStom_model extends EvnPL_model {
	/**
	 *	Конструктор
	 */
	function __construct()
	{
		parent::__construct();
	}

	/**
	 * Возвращает список всех используемых ключей атрибутов объекта
	 * @return array
	 */
	static function defAttributes()
	{
		$arr = parent::defAttributes();
		$arr[self::ID_KEY]['alias'] = 'EvnPLStom_id';
		$arr['setdate']['alias'] = 'EvnPLStom_setDate';
		$arr['settime']['alias'] = 'EvnPLStom_setTime';
		$arr['disdt']['alias'] = 'EvnPLStom_disDT';
		$arr['isfinish']['alias'] = 'EvnPLStom_IsFinish';
		$arr['issurveyrefuse']['alias'] = 'EvnPLStom_IsSurveyRefuse';
		$arr['diddt']['alias'] = 'EvnPLStom_didDT';
		$arr['numcard']['alias'] = 'EvnPLStom_NumCard';
		$arr['ukl']['alias'] = 'EvnPLStom_UKL';
		//$arr['diag_did']['alias'] = 'Diag_id';
		//$arr['evndirection_num']['alias'] = 'Direction_Number';
		//$arr['evndirection_setdt']['alias'] = 'Direction_setDate';
		$arr['isunlaw']['alias'] = 'EvnPLStom_IsUnlaw';
		$arr['isunport']['alias'] = 'EvnPLStom_IsUnport';
		$arr['isfirsttime']['alias'] = 'EvnPLStom_IsFirstTime';
		$arr['complexity']['alias'] = 'EvnPLStom_Complexity';
		$arr['firstvizitdt']['alias'] = 'EvnPLStom_FirstVizitDT';
		$arr['lastvizitdt']['alias'] = 'EvnPLStom_LastVizitDT';
		$arr['lastuslugadt']['alias'] = 'EvnPLStom_LastUslugaDT';
		$arr['isinreg']['alias'] = 'EvnPLStom_IsInReg';
		$arr['ispaid']['alias'] = 'EvnPLStom_IsPaid';
		$arr['iscons']['alias'] = 'EvnPLStom_IsCons';
		$arr['medpersonalcode']['alias'] = 'EvnPLStom_MedPersonalCode';
		$arr['issan'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnPLStom_IsSan',
			'label' => 'Санирован',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['sanationstatus_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'SanationStatus_id',
			'label' => 'Санация',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['indexrep'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnPLStom_IndexRep',
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
			'alias' => 'EvnPLStom_IndexRepInReg',
		);
		return $arr;
	}

	/**
	 * Правила для контроллера для извлечения входящих параметров при сохранении
	 * @return array
	 */
	protected function _getSaveInputRules()
	{
		$all = parent::_getSaveInputRules();
		// параметры
		$all['EvnCostPrint_setDT'] = array(
			'field' => 'EvnCostPrint_setDT',
			'label' => 'Дата выдачи справки/отказа',
			'rules' => '',
			'type' => 'date'
		);
		$all['EvnCostPrint_IsNoPrint'] = array(
			'field' => 'EvnCostPrint_IsNoPrint',
			'label' => 'Отказ',
			'rules' => '',
			'type' => 'id'
		);
		$all['ignoreKsgInMorbusCheck'] = array(
			'field' => 'ignoreKsgInMorbusCheck',
			'label' => 'Признак игнорирования проверки заполнения КСГ в заболеваниях',
			'rules' => '',
			'type' => 'int'
		);
		$all['ignoreUetSumInNonMorbusCheck'] = array(
			'field' => 'ignoreUetSumInNonMorbusCheck',
			'label' => 'Признак игнорирования проверки превышения суммы УЕТ в услугах максимального КСГ по указанному диагнозу',
			'rules' => '',
			'type' => 'int'
		);
		$all['MedicalStatus_id'] = array(
			'field' => 'MedicalStatus_id',	
			'label' => 'Состояние здоровья', 
			'rules' => '', 
			'type' => 'id'
		);
		return $all;
	}

	/**
	 * Логика после сохранения ТАП
	 * @param array $data Массив входящих параметров
	 * @throws Exception
	 */
	protected function _afterSave($result)
	{
		parent::_afterSave($result);

		if (!empty($this->isFinish) && $this->isFinish == 2 ) {
			// пересчитываем КСКП во всех закрытых заболеваниях
			$EvnDiagPLs = $this->queryResult("
				select
					EvnDiagPLStom_id,
					Mes_id
				from
					v_EvnDiagPLStom (nolock)
				where
					EvnDiagPLStom_rid = :EvnDiagPLStom_rid
					and EvnDiagPLStom_IsClosed = 2
			", array('EvnDiagPLStom_rid' => $this->id));

			$this->load->model('EvnDiagPLStom_model');
			foreach ($EvnDiagPLs as $oneEvnDiagPL) {
				$KSKP = $this->EvnDiagPLStom_model->calcKSKP(array(
					'EvnDiagPLStom_id' => $oneEvnDiagPL['EvnDiagPLStom_id'],
					'Mes_id' => $oneEvnDiagPL['Mes_id']
				));

				$this->db->query("update EvnDiagPLStom with (rowlock) set EvnDiagPLStom_KSKP = :EvnDiagPLStom_KSKP where EvnDiagPLStom_id = :EvnDiagPLStom_id", array(
					'EvnDiagPLStom_id' => $oneEvnDiagPL['EvnDiagPLStom_id'],
					'EvnDiagPLStom_KSKP' => $KSKP
				));
			}
			
			$firstEvnVizit = null;
			$lastEvnVizit = null;
			foreach ($this->evnVizitList as $vizit) {
				if (empty($firstEvnVizit) || (!empty($vizit['EvnVizitPL_setDate']) && strtotime($vizit['EvnVizitPL_setDate'] . ' ' . $vizit['EvnVizitPL_setTime']) >= strtotime($firstEvnVizit['EvnVizitPL_setDate'] . ' ' . $firstEvnVizit['EvnVizitPL_setTime']))) {
					$firstEvnVizit = $vizit;
				}
				if (empty($lastEvnVizit) || (!empty($vizit['EvnVizitPL_setDate']) && strtotime($vizit['EvnVizitPL_setDate'] . ' ' . $vizit['EvnVizitPL_setTime']) >= strtotime($lastEvnVizit['EvnVizitPL_setDate'] . ' ' . $lastEvnVizit['EvnVizitPL_setTime']))) {
					$lastEvnVizit = $vizit;
				}
			}

			if (
				$this->regionNick == 'penza' && strtotime($lastEvnVizit['EvnVizitPL_setDate']) >= strtotime('2018-08-01')
				&& strtotime($firstEvnVizit['EvnVizitPL_setDate']) < strtotime('2019-06-01')
			) {
				// Выполняем проверки
				// @task https://redmine.swan.perm.ru/issues/136169
				$dataSet = $this->EvnDiagPLStom_model->getEvnDiagPLStomData($this->id);

				// Если в рамках хотя бы одного заболевания ТАП добавлены услуги с разными значениями атрибута «Вид услуги», то открывается сообщение:
				// «Введены услуги разного вида (лечебные, профилактические, неотложные). В рамках одного заболевания должны быть услуги только одного вида.
				// Перенесите услуги на разные заболевания». Кнопка ОК. При нажатии на кнопку сообщение закрывается, сохранение ТАП не выполняется, в поле
				// «Случай закончен» устанавливается значение «Нет»;

				// Если в рамках ТАП есть заболевание(я), в котором(ых) только услуги со значением атрибута «Вид услуги»=«01» (учитывается правило определения
				// значения атрибута «Вид услуги», описанное в ТЗ выполнение стоматологической услуги)
				// и
				// в одной МО на данного пациента существует закрытый в текущем месяце ТАП, в котором есть хотя бы одно заболевание:
				// - с такой же группой диагнозов по МКБ-10;
				// - в котором выполнена хотя бы одна услуга со значением атрибута «Вид услуги» = «01» (учитывается правило определения значения атрибута «Вид услуги»,
				//   описанное в ТЗ выполнение стоматологической услуги),
				// то открывается сообщение: «В текущем месяце у данного пациента уже есть закрытое заболевание с такой же группой диагнозов и услугами с лечебной
				// целью: ТАП № <номер(а) талона(ов), в котором(ых) есть закрытые заболевания с такой же группой диагнозов и хотя бы одной услугой, для которых
				// значение атрибута «Вид услуги»= «01», если найдено более одного талона, то перечисляются номера через запятую>. Услуги с лечебной целью
				// необходимо добавить в ранее созданный талон». Кнопка ОК. При нажатии на кнопку сообщение закрывается, сохранение услуги не выполняется». 

				$diagList01 = array();
				$uslugaTypeInfo = array();

				foreach ( $dataSet as $row ) {
					if ( !isset($uslugaTypeInfo[$row['EvnDiagPLStom_id']]) ) {
						$uslugaTypeInfo[$row['EvnDiagPLStom_id']] = array(
							'01' => 0,
							'02' => 0,
							'03' => 0,
						);
					}
					if (
						$row['UslugaComplexAttribute_Value'] == '01'
						|| (
							$row['UslugaComplexAttribute_Value'] == '03'
							&& $row['ServiceType_SysNick'] != 'neotl'
							&& $row['ServiceType_SysNick'] != 'polnmp'
						)
					) {
						if ( !in_array($row['Diag_pid'], $diagList01) ) {
							$diagList01[] = $row['Diag_pid'];
						}

						$uslugaTypeInfo[$row['EvnDiagPLStom_id']]['01'] = 1;
					}
					else if ( $row['UslugaComplexAttribute_Value'] == '02' ) {
						$uslugaTypeInfo[$row['EvnDiagPLStom_id']]['02'] = 1;
					}
					else if (
						$row['UslugaComplexAttribute_Value'] == '03'
						&& ($row['ServiceType_SysNick'] == 'neotl' || $row['ServiceType_SysNick'] == 'polnmp')
					) {
						$uslugaTypeInfo[$row['EvnDiagPLStom_id']]['03'] = 1;
					}
				}

				foreach ( $uslugaTypeInfo as $row ) {
					if ( $row['01'] + $row['02'] + $row['03'] > 1 ) {
						throw new Exception('Введены услуги разного вида (лечебные, профилактические, неотложные). В рамках одного заболевания должны быть услуги только одного вида. Перенесите услуги на разные заболевания', 191);
					}
				}

				if ( count($diagList01) > 0 ) {
					$EvnPLStom_NumCard = $this->getFirstResultFromQuery("
						declare
							@Year int = :Year,
							@Month int = :Month;

						if ( @Year is null or @Month is null )
							select top 1
								@Year = YEAR(EvnPLStom_setDT),
								@Month = MONTH(EvnPLStom_setDT)
							from v_EvnPLStom with (nolock)
							where EvnPLStom_id = :EvnPLStom_id;

						with epls as (
							select
								EvnPLStom_id,
								EvnPLStom_NumCard
							from v_EvnPLStom with (nolock)
							where Person_id = :Person_id
								and Lpu_id = :Lpu_id
								and EvnPLStom_id != :EvnPLStom_id
								and YEAR(EvnPLStom_disDT) = @Year
								and MONTH(EvnPLStom_disDT) = @Month
						)

						select top 1
							epls.EvnPLStom_NumCard
						from v_EvnUslugaStom eus with (nolock)
							inner join v_EvnDiagPLStom edpls on edpls.EvnDiagPLStom_id = eus.EvnDiagPLStom_id
							inner join v_Diag d with (nolock) on d.Diag_id = edpls.Diag_id
							inner join v_EvnVizitPLStom evpls on evpls.EvnVizitPLStom_id = eus.EvnUslugaStom_pid
							inner join epls on epls.EvnPLStom_id = eus.EvnUslugaStom_rid
							inner join v_UslugaComplexAttribute uca on uca.UslugaComplex_id = eus.UslugaComplex_id
							inner join v_UslugaComplexAttributeType ucat on ucat.UslugaComplexAttributeType_id = uca.UslugaComplexAttributeType_id
						where d.Diag_pid in (" . implode(",", $diagList01) . ")
							and edpls.EvnDiagPLStom_IsClosed = 2
							and ucat.UslugaComplexAttributeType_SysNick = 'uslugatype'
							and uca.UslugaComplexAttribute_Value = '01'

						union all

						select top 1
							epls.EvnPLStom_NumCard
						from v_EvnUslugaStom eus with (nolock)
							inner join v_EvnDiagPLStom edpls on edpls.EvnDiagPLStom_id = eus.EvnDiagPLStom_id
							inner join v_Diag d with (nolock) on d.Diag_id = edpls.Diag_id
							inner join v_EvnVizitPLStom evpls on evpls.EvnVizitPLStom_id = eus.EvnUslugaStom_pid
							inner join epls on epls.EvnPLStom_id = eus.EvnUslugaStom_rid
							inner join v_ServiceType st on st.ServiceType_id = evpls.ServiceType_id
							inner join v_UslugaComplexAttribute uca on uca.UslugaComplex_id = eus.UslugaComplex_id
							inner join v_UslugaComplexAttributeType ucat on ucat.UslugaComplexAttributeType_id = uca.UslugaComplexAttributeType_id
						where d.Diag_pid in (" . implode(",", $diagList01) . ")
							and edpls.EvnDiagPLStom_IsClosed = 2
							and st.ServiceType_SysNick != 'neotl'
							and st.ServiceType_SysNick != 'polnmp'
							and ucat.UslugaComplexAttributeType_SysNick = 'uslugatype'
							and uca.UslugaComplexAttribute_Value = '03'
					", array(
						'Person_id' => $this->Person_id,
						'Lpu_id' => $this->Lpu_id,
						'EvnPLStom_id' => $this->id,
						'Year' => (!empty($this->disDT) && $this->disDT instanceof DateTime ? $this->disDT->format('Y') : null),
						'Month' => (!empty($this->disDT) && $this->disDT instanceof DateTime ? $this->disDT->format('n') : null),
					));

					if ( $EvnPLStom_NumCard !== false && !empty($EvnPLStom_NumCard) ) {
						throw new Exception('В текущем месяце у данного пациента уже есть закрытое заболевание с такой же группой диагнозов и услугами с лечебной целью: ТАП № ' . $EvnPLStom_NumCard . '. Услуги с лечебной целью необходимо добавить в ранее созданный талон»', 192);
					}
				}

				// Группируем заболевания
				$this->EvnDiagPLStom_model->afterSaveEvnDiagPLStom('EvnDiagPLStom', array(), array('EvnPLStom_id' => $this->id));
			}
		}
		
		if ($this->getRegionNick() == 'kz') {
			$this->db->query("
				delete from r101.EvnPlMedicalStatusLink where EvnPL_id = :EvnPL_id
			", array(
				'EvnPL_id' => $this->id
			));
			if (!empty($this->_params['MedicalStatus_id'])) {
				$query = "
					declare
						@Res bigint,
						@ErrCode int,
						@ErrMessage varchar(4000);
					set @Res = null;
					exec r101.p_EvnPlMedicalStatusLink_ins
						@EvnPlMedicalStatusLink_id = @Res output,
						@EvnPL_id = :EvnPL_id,
						@MedicalStatus_id = :MedicalStatus_id,
						@pmUser_id = :pmUser_id,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMessage output;
					select @Res as EvnPlMedicalStatusLink_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
				";
				$this->db->query($query, array(
					'EvnPL_id' => $this->id,
					'MedicalStatus_id' => $this->_params['MedicalStatus_id'],
					'pmUser_id' => $this->_params['session']['pmuser_id']
				));
			}
		}
	}

	/**
	 * Логика перед сохранением ТАП
	 * @param array $data Массив входящих параметров
	 * @throws Exception
	 */
	protected function _beforeSave($data = array())
	{
		parent::_beforeSave($data);

		if (!empty($this->isFinish) && $this->isFinish == 2 ) {
			// У всех заведенных заболеваний должна быть проставлено значение «Да» в поле «Заболевание закрыто».
			$query = "
				select top 1
					EvnDiagPLStom_id
				from
					v_EvnDiagPLStom (nolock)
				where
					EvnDiagPLStom_rid = :EvnDiagPLStom_rid
					and ISNULL(EvnDiagPLStom_IsClosed, 1) = 1
			";
			$resp_edps = $this->queryResult($query, array(
				'EvnDiagPLStom_rid' => $this->id
			));
			if (!empty($resp_edps[0]['EvnDiagPLStom_id'])) {
				throw new Exception('Случай не может быть закончен, т.к. закрыты не все заболевания.');
			}

			if (array_key_exists('ignoreParentEvnDateCheck', $data)) {
				$checkDate = $this->CheckEvnUslugasDate($this->id, $data['ignoreParentEvnDateCheck']);
				if (!$this->isSuccessful($checkDate)) {
					throw new Exception($checkDate[0]['Error_Msg'], (int)$checkDate[0]['Error_Code']);
				}
			}
		}

		// Если случай закрыт и задана дата справки, то сохраняем справку.
		if (!empty($data['EvnPLStom_id']) && !empty($data['EvnPLStom_IsFinish']) && $data['EvnPLStom_IsFinish'] == 2 && !empty($data['EvnCostPrint_setDT']))
		{
			// сохраняем справку
			$this->load->model('CostPrint_model');
			$this->CostPrint_model->saveEvnCostPrint(array(
				'Evn_id' => $data['EvnPLStom_id'],
				'CostPrint_IsNoPrint' => $data['EvnCostPrint_IsNoPrint'],
				'CostPrint_setDT' => $data['EvnCostPrint_setDT'],
				'pmUser_id' => $data['pmUser_id']
			));
		}
	}

	/**
	 * Определение идентификатора класса события
	 * @return int
	 */
	static function evnClassId()
	{
		return 6;
	}

	/**
	 * Определение кода класса события
	 * @return string
	 */
	static function evnClassSysNick()
	{
		return 'EvnPLStom';
	}

	/**
	 * @param int $id
	 * @param mixed $value
	 * @return array
	 */
	function updateEvnPLStomIsSan($id, $value = null)
	{
		return $this->_updateAttribute($id, 'issan', $value);
	}
	/**
	 * @param int $id
	 * @param mixed $value
	 * @return array
	 */
	function updateSanationStatusId($id, $value = null)
	{
		return $this->_updateAttribute($id, 'sanationstatus_id', $value);
	}

	/**
	 * Проверки и другая логика перед удалением объекта
	 * @param array $data Массив входящих параметров
	 * @throws Exception
	 */
	protected function _beforeDelete($data = array())
	{
		parent::_beforeDelete($data);
		// удаляем все записи из ПГ в рамках талона
		$this->load->model('Parodontogram_model');
		$tmp = $this->Parodontogram_model->doRemoveByEvn($this->id, 'EvnPLStom', false);
		if (!empty($tmp[0]['Error_Msg'])) {
			throw new Exception($tmp[0]['Error_Msg'], 500);
		}
		$this->load->model('PersonToothCard_model');
		if ($this->PersonToothCard_model->isAllowEdit($this->id, $this->Person_id, $this->evnClassId)) {
			// если стомат. талон последний, то удаляем все записи из ЗК в рамках талона
			$this->PersonToothCard_model->setParams(array('session'=>$this->sessionParams));
			$tmp = $this->PersonToothCard_model->doRemoveByEvn($this->id, 'EvnPLStom', false);
			if (!empty($tmp[0]['Error_Msg'])) {
				throw new Exception($tmp[0]['Error_Msg'], 500);
			}
		}
	}

	/**
	 *	Получение полей для печати стомат. ТАП (Пермь)
	 */
	function getEvnPLStomFieldsPerm($data) {
		$inner = '';
		if(!isTFOMSUser()){
			$inner = "and Lpu.Lpu_id " . getLpuIdFilter($data) ;
		}
		$query = "
			select
				RTRIM(ISNULL(DirectClass.DirectClass_Name, '')) as DirectClass_Name,
				RTRIM(ISNULL(DirectType.DirectType_Name, '')) as DirectType_Name,
				RTRIM(ISNULL(DirectType.DirectType_Code, '')) as DirectType_Code,
				RTRIM(ISNULL(DirectType.DirectType_SysNick, '')) as DirectType_SysNick,
				ISNULL(convert(varchar(10), Document.Document_begDate, 104), '') as Document_begDate,
				RTRIM(ISNULL(Document.Document_Num, '')) as Document_Num,
				RTRIM(ISNULL(Document.Document_Ser, '')) as Document_Ser,
				PS.Sex_id,
				RTRIM(ISNULL(DocumentType.DocumentType_Name, '')) as DocumentType_Name,
				ISNULL(IsFinish.YesNo_Code, 0) as EvnPLStom_IsFinish,
				ISNULL(SocStatus.SocStatus_Code, '') as SocStatus_Code,
				ISNULL(IsUnlaw.YesNo_Code, 0) as EvnPLStom_IsUnlaw,
				ISNULL(IsUnport.YesNo_Code, 0) as EvnPLStom_IsUnport,
				ISNULL(EvnPLStom.EvnPLStom_NumCard, '') as EvnPLStom_NumCard,
				ISNULL(EvnPLStom.DeseaseType_id, '') as DeseaseType_id,
				ROUND(ISNULL(EvnPLStom.EvnPLStom_UKL, 0), 3) as EvnPLStom_UKL,
				RTRIM(ISNULL(Lpu.Lpu_Name, '')) as Lpu_Name,
				RTRIM(ISNULL(Lpu.Lpu_OGRN, '')) as Lpu_OGRN,
				RTRIM(ISNULL(PS.Person_Snils, '')) as Person_Snils,
				RTRIM(ISNULL(Lpu.PAddress_Address, '')) as Lpu_Address,
				RTRIM(ISNULL(MP.MedPersonal_TabCode, '')) as MedPersonal_TabCode,
				RTRIM(ISNULL(EvnVizitPL.Diag_Code, '')) as FinalDiag_Code,
				RTRIM(ISNULL(EvnVizitPL.ServiceType_Code, '')) as ServiceType_Code,
				RTRIM(ISNULL(EvnVizitPL.VizitType_SysNick, '')) as VizitType_SysNick,
				ISNULL(EvnVizitPL.PayType_Code,'') as PayType_Code,
				RTRIM(ISNULL(MP.Person_Fio, '')) as MedPersonal_Fio,
				RTRIM(ISNULL(EvnStick.EvnStick_Age, '')) as EvnStick_Age,
				RTRIM(ISNULL(EvnStick.EvnStick_begDate, '')) as EvnStick_begDate,
				RTRIM(ISNULL(EvnStick.EvnStick_endDate, '')) as EvnStick_endDate,
				RTRIM(ISNULL(EvnStick.StickType_SysNick, '')) as StickType_SysNick,
				RTRIM(ISNULL(EvnStick.StickCause_SysNick, '')) as StickCause_SysNick,
				ISNULL(EvnStick.Sex_Code, 0) as EvnStick_Sex,
				CASE
					WHEN EvnStick.EvnStick_begDate IS NULL THEN 0
					WHEN EvnStick.EvnStick_begDate IS NOT NULL AND EvnStick.EvnStick_endDate IS NULL THEN 1
					ELSE 2
				END as EvnStick_Open,
				CASE
					WHEN PersonDisp.PersonDisp_id IS NOT NULL THEN 1
					ELSE 0
				END as PersonDisp,
				CASE
					WHEN PersonDispSop.PersonDisp_id IS NOT NULL THEN 1
					ELSE 0
				END as PersonDispSop,
				CASE
					WHEN PrivilegeType_fromBirth.invalidKind_id IS NOT NULL THEN 1
					ELSE 0
				END as PrivilegeType_fromBirth,
				RTRIM(ISNULL(EvnDiagPLStomSop.Diag_Code, '')) as DiagSop_Code,
				RTRIM(ISNULL(EvnDiagPLStomSop.DeseaseType_SysNick, '')) as DeseaseTypeSop_SysNick,
				RTRIM(ISNULL(EvnDiagPLStomSop.DeseaseType_Code, '')) as DeseaseTypeSop_Code,
				RTRIM(ISNULL(EvnVizitPL.DeseaseType_SysNick, '')) as FinalDeseaseType_SysNick,
				ISNULL(convert(varchar(10), EvnPLStom.EvnPLStom_setDT, 104), '') as EvnPL_setDate,
				RTRIM(ISNULL(LpuRegion.LpuRegion_Name, '')) as LpuRegion_Name,
				RTRIM(ISNULL(OD.Org_Name, '')) as OrgDep_Name,
				RTRIM(ISNULL(OJ.Org_Name, '')) as Org_Name,
				RTRIM(ISNULL(OS.Org_Name, '')) as OrgSmo_Name,
				RTRIM(case when PrehospDirect.PrehospDirect_Code = 1 then PrehospLS.LpuSection_Name else case when PrehospDirect.PrehospDirect_Code = 2 then PrehospLpu.Lpu_Name else PrehospOrg.Org_Name end end) as PrehospOrg_Name,
				RTRIM(case when DirectClass.DirectClass_Code = 1 then DirectLS.LpuSection_Name else case when DirectClass.DirectClass_Code = 2 then DirectLpu.Lpu_Name else '' end end) as DirectOrg_Name,
				ISNULL(Diag.Diag_Code, '') as PrehospDiag_Code,
				ISNULL(Diag.Diag_Name, '') as PrehospDiag_Name,
				convert(varchar(10), PS.Person_Birthday, 104) as Person_Birthday,
				case when PC.Lpu_id = :Lpu_id then RTRIM(PC.PersonCard_Code) else null end as PersonCard_Code,
				RTRIM(RTRIM(ISNULL(PS.Person_Surname, '')) + ' ' + RTRIM(ISNULL(PS.Person_Firname, '')) + ' ' + RTRIM(ISNULL(PS.Person_Secname, ''))) as Person_Fio,
				RTRIM(ISNULL(PAddr.Address_Address, '')) as PAddress_Name,
				RTRIM(ISNULL(UAddr.Address_Address, '')) as UAddress_Name,
				RTRIM(ISNULL(KLAreaType.KLAreaType_Name, '')) as KLAreaType_Name,
				convert(varchar(10), Polis.Polis_begDate, 104) as Polis_begDate,
				convert(varchar(10), Polis.Polis_endDate, 104) as Polis_endDate,
				RTRIM(ISNULL(case when Polis.PolisType_id = 4 then PS.Person_EdNum else Polis.Polis_Num end, '')) as Polis_Num,
				RTRIM(ISNULL(case when Polis.PolisType_id = 4 then '' else Polis.Polis_Ser end, '')) as Polis_Ser,
				RTRIM(ISNULL(PolisType.PolisType_Name, '')) as PolisType_Name,
				RTRIM(ISNULL(Post.Post_Name, '')) as Post_Name,
				RTRIM(ISNULL(PrehospDirect.PrehospDirect_Name, '')) as PrehospDirect_Name,
				RTRIM(ISNULL(PHT.PrehospTrauma_Name, '')) as PrehospTrauma_Name,
				RTRIM(ISNULL(PHT.PrehospTrauma_Code, 0)) as PrehospTrauma_Code,
				RTRIM(ISNULL(ResultClass.ResultClass_Name, '')) as ResultClass_Name,
				RTRIM(ISNULL(ResultClass.ResultClass_Code, '')) as ResultClass_Code,
				RTRIM(ISNULL(ResultClass.ResultClass_SysNick, '')) as ResultClass_SysNick,
				RTRIM(ISNULL(Sex.Sex_Name, '')) as Sex_Name,
				RTRIM(ISNULL(SocStatus.SocStatus_Name, '')) as SocStatus_Name,
				RTRIM(ISNULL(PersonPrivilege.PersonPrivilege_begDate, '')) as PersonPrivilege_begDate,
				RTRIM(ISNULL(PersonPrivilege.PrivilegeType_Name, '')) as PrivilegeType_Name,
				RTRIM(ISNULL(PrivilegeType_Code.PrivilegeType_Code, '')) as PrivilegeType_Code,
				RTRIM(ISNULL(PrivilegeType_gr.PrivilegeType_Code, '')) as PrivilegeType_gr,
				RTRIM(ISNULL(EvnUdost.EvnUdost_Num, '')) as EvnUdost_Num,
				RTRIM(ISNULL(EvnUdost.EvnUdost_Ser, '')) as EvnUdost_Ser
			from v_EvnPLStom EvnPLStom WITH (NOLOCK)
				inner join v_Lpu Lpu (nolock) on Lpu.Lpu_id = EvnPLStom.Lpu_id
					".$inner."
				inner join v_Person_all PS (nolock) on PS.Server_id = EvnPLStom.Server_id
					and PS.PersonEvn_id = EvnPLStom.PersonEvn_id
				left join v_EvnVizitPLStom EVPLS with (nolock) on EVPLS.EvnVizitPLStom_pid = EvnPLStom.EvnPLStom_id
    		    left join v_MedPersonal MP with (nolock) on MP.MedPersonal_id = EVPLS.MedPersonal_id and MP.Lpu_id = EVPLS.Lpu_id
				left join [Address] UAddr (nolock) on UAddr.Address_id = PS.UAddress_id
				left join [Address] PAddr (nolock) on PAddr.Address_id = PS.PAddress_id
				left join KLAreaType (nolock) on KLAreaType.KLAreaType_id = PAddr.KLAreaType_id
				left join DirectClass (nolock) on DirectClass.DirectClass_id = EvnPLStom.DirectClass_id
				left join DirectType (nolock) on DirectType.DirectType_id = EvnPLStom.DirectType_id
				left join v_Lpu DirectLpu (nolock) on DirectLpu.Lpu_id = EvnPLStom.Lpu_oid
				left join LpuSection DirectLS (nolock) on DirectLS.LpuSection_id = EvnPLStom.LpuSection_oid
				left join Document (nolock) on Document.Document_id = PS.Document_id
				left join DocumentType (nolock) on DocumentType.DocumentType_id = Document.DocumentType_id
				left join OrgDep (nolock) on OrgDep.OrgDep_id = Document.OrgDep_id
				left join Org OD (nolock) on OD.Org_id = OrgDep.Org_id
				left join Job (nolock) on Job.Job_id = PS.Job_id
				left join Org OJ (nolock) on OJ.Org_id = Job.Org_id
				left join v_Lpu PrehospLpu (nolock) on PrehospLpu.Lpu_id = EvnPLStom.Lpu_did
				left join LpuSection PrehospLS (nolock) on PrehospLS.LpuSection_id = EvnPLStom.LpuSection_did
				left join Org PrehospOrg (nolock) on PrehospOrg.Org_id = EvnPLStom.Org_did
				left join Diag (nolock) on Diag.Diag_id = EvnPLStom.Diag_did
				left join LpuSection DLS (nolock) on DLS.LpuSection_id = EvnPLStom.LpuSection_did
				left join v_PersonCard PC (nolock) on PC.Person_id = PS.Person_id
					and PC.PersonCard_begDate is not null
					and PC.PersonCard_begDate <= EvnPLStom.EvnPLStom_insDT
					and (PC.PersonCard_endDate is null or PC.PersonCard_endDate > EvnPLStom.EvnPLStom_insDT)
					and PC.Lpu_id = EvnPLStom.Lpu_id
				left join v_LpuRegion (nolock) on LpuRegion.LpuRegion_id = PC.LpuRegion_id
				left join Post (nolock) on Post.Post_id = Job.Post_id
				left join Polis (nolock) on Polis.Polis_id = PS.Polis_id
				left join PolisType (nolock) on PolisType.PolisType_id = Polis.PolisType_id
				left join OrgSmo (nolock) on OrgSmo.OrgSmo_id = Polis.OrgSmo_id
				left join Org OS (nolock) on OS.Org_id = OrgSmo.Org_id
				left join PrehospDirect (nolock) on PrehospDirect.PrehospDirect_id = EvnPLStom.PrehospDirect_id
				left join v_PrehospTrauma PHT (nolock) on PHT.PrehospTrauma_id = EvnPLStom.PrehospTrauma_id
				left join ResultClass (nolock) on ResultClass.ResultClass_id = EvnPLStom.ResultClass_id
				left join Sex (nolock) on Sex.Sex_id = PS.Sex_id
				left join SocStatus (nolock) on SocStatus.SocStatus_id = PS.SocStatus_id
				left join YesNo IsFinish (nolock) on IsFinish.YesNo_id = EvnPLStom.EvnPLStom_IsFinish
				left join YesNo IsUnlaw (nolock) on IsUnlaw.YesNo_id = EvnPLStom.EvnPLStom_IsUnlaw
				left join YesNo IsUnport (nolock) on IsUnport.YesNo_id = EvnPLStom.EvnPLStom_IsUnport
				outer apply (
					select top 1
						PrivilegeType_Name,
						convert(varchar(10), PersonPrivilege_begDate, 104) as PersonPrivilege_begDate
					from
						v_PersonPrivilege WITH (NOLOCK)
					where PrivilegeType_Code in ('81', '82', '83')
						and Person_id = PS.Person_id
					order by PersonPrivilege_begDate desc
				) PersonPrivilege
				outer apply (
					select top 1
						PrivilegeType_Code
					from
						v_PersonPrivilege WITH (NOLOCK)
					where Person_id = PS.Person_id
					order by PersonPrivilege_begDate desc
				) PrivilegeType_Code
				--madness start
				outer apply (
				    select top 1
						PrivilegeType_Code
					from
						v_PersonPrivilege WITH (NOLOCK)
					where Person_id = PS.Person_id
					and privilegetype_code in ('81', '82', '83', '84')
					order by PersonPrivilege_begDate desc
				) PrivilegeType_gr
				outer apply (
				    select top 1
						invalidKind_id
					from
						v_personchild WITH (NOLOCK)
					where Person_id = PS.Person_id
					and invalidKind_id = 2
					order by PersonPrivilege_begDate desc
				) PrivilegeType_fromBirth
				-- madness end
				outer apply (
					select top 1
						EvnUdost_Num,
						EvnUdost_Ser
					from
						v_EvnUdost (nolock)
					where EvnUdost_setDate <= dbo.tzGetDate()
						and Person_id = PS.Person_id
					order by EvnUdost_setDate desc
				) EvnUdost
				outer apply (
				    select top 1
						PersonDisp_id
					from
						v_PersonDisp (nolock)
					where (PersonDisp_endDate is null or PersonDisp_endDate > EVPLS.EvnVizitPLStom_setDT)
						and Person_id = EvnPLStom.Person_id
						and diag_id = EvnPLStom.diag_id
				) PersonDisp
				outer apply (
					select top 1
						D.Diag_Code,
						D.Diag_id,
						DT.DeseaseType_Code,
						DT.DeseaseType_SysNick
					from v_EvnDiagPLStomSop EDPLS with (nolock)
						left join Diag D with (nolock) on D.Diag_id = EDPLS.Diag_id
						left join DeseaseType DT with (nolock) on DT.DeseaseType_id = EDPLS.DeseaseType_id
					where EDPLS.EvnDiagPLStomSop_rid = EvnPLStom.EvnPLStom_id
					order by
						EDPLS.EvnDiagPLStomSop_id
				) EvnDiagPLStomSop
				outer apply (
				    select top 1
						PersonDisp_id
					from
						v_PersonDisp (nolock)
					where (PersonDisp_endDate is null or PersonDisp_endDate > EVPLS.EvnVizitPLStom_setDT)
						and Person_id = EvnPLStom.Person_id
						and diag_id = EvnDiagPLStomSop.diag_id
				) PersonDispSop
				outer apply (
					select top 1
						D.Diag_Code,
						PT.PayType_Code,
						ST.ServiceType_Code,
						VT.VizitType_SysNick,
						DT.DeseaseType_SysNick
					from v_EvnVizitPLStom EVPL with (nolock)
						left join v_Diag D with (nolock) on D.Diag_id = EVPL.Diag_id
						left join PayType PT with (nolock) on PT.PayType_id = EVPL.PayType_id
						left join ServiceType ST with (nolock) on ST.ServiceType_id = EVPL.ServiceType_id
						left join VizitType VT with (nolock) on VT.VizitType_id = EVPL.VizitType_id
						left join DeseaseType DT with (nolock) on DT.DeseaseType_id = EVPL.DeseaseType_id
					where EVPL.EvnVizitPLStom_pid = EvnPLStom.EvnPLStom_id
					order by
						EVPL.EvnVizitPLStom_id
				) EvnVizitPL
				outer apply (
					select top 1
						ES.EvnStick_Age,
						convert(varchar(10), ES.EvnStick_begDate, 104) as EvnStick_begDate,
						convert(varchar(10), ES.EvnStick_endDate, 104) as EvnStick_endDate,
						SC.StickCause_SysNick,
						ST.StickType_SysNick,
						Sex.Sex_Code
					from v_EvnStick ES (nolock)
					    left join StickCause SC (nolock) on SC.StickCause_id = ES.StickCause_id
				        left join StickType ST (nolock) on ST.StickType_id = ES.StickType_id
				        left join Sex with (nolock) on Sex.Sex_id = ES.Sex_id
					where ES.EvnStick_pid = EvnPLStom.EvnPLStom_id
					order by ES.EvnStick_id
				) EvnStick
    		where
				EvnPLStom.EvnPLStom_id = :EvnPLStom_id
		";

		/*
		echo getDebugSQL($query, array(
			'EvnPLStom_id' => $data['EvnPLStom_id'],
			'Lpu_id' => $data['Lpu_id']
		)); exit();
		*/
		$result = $this->db->query($query, array(
			'EvnPLStom_id' => $data['EvnPLStom_id'],
			'Lpu_id' => $data['Lpu_id']
		));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 *	Получение данных о стомат. посещениях для печати стомат. ТАП (Пермь)
	 */
	function getEvnVizitPLStomDataPerm($data) {
		$query = "
			select
				convert(varchar(10), EVPL.EvnVizitPLStom_setDate, 104) as EVPL_EvnVizitPL_setDate,
				RTRIM(LS.LpuSection_Code) as EVPL_LpuSection_Code,
				RTRIM(MP.Person_Fio) as EVPL_MedPersonal_Fio,
				RTRIM(MMP.MedPersonal_TabCode) as EVPL_MidMedPersonal_Code,
				RTRIM(LS.LpuSection_Name) as EVPL_EvnVizitPL_Name,
				RTRIM(ST.ServiceType_Name) as EVPL_ServiceType_Name,
				RTRIM(VT.VizitType_Name) as EVPL_VizitType_Name,
				RTRIM(PT.PayType_Name) as EVPL_PayType_Name
			from v_EvnVizitPLStom EVPL WITH (NOLOCK)
				left join LpuSection LS (nolock) on LS.LpuSection_id = EVPL.LpuSection_id
				left join v_MedPersonal MP (nolock) on MP.MedPersonal_id = EVPL.MedPersonal_id
					and MP.Lpu_id = :Lpu_id
				left join v_MedPersonal MMP (nolock) on MMP.MedPersonal_id = EVPL.MedPersonal_sid
					and MMP.Lpu_id = :Lpu_id
				left join PayType PT (nolock) on PT.PayType_id = EVPL.PayType_id
				left join ServiceType ST (nolock) on ST.ServiceType_id = EVPL.ServiceType_id
				left join VizitType VT (nolock) on VT.VizitType_id = EVPL.VizitType_id
			where EVPL.EvnVizitPLStom_pid = :EvnPLStom_id
		";
		$result = $this->db->query($query, array(
			'EvnPLStom_id' => $data['EvnPLStom_id'],
			'Lpu_id' => $data['Lpu_id']
		));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 *	Получение списка основных диагнозов для печати стомат. ТАП
	 */
	function getEvnDiagPLOsnData($data) {
		$query = "
		select
			convert(varchar(10), EVPLS.EvnVizitPLStom_setDate, 104) as EvnDiagPL_setDate,
			RTRIM(LS.LpuSection_Name) as LpuSection_Name,
			RTRIM(MP.MedPersonal_TabCode) as MedPersonal_Code,
			RTRIM(Diag.Diag_Code) as Diag_Code,
			RTRIM(DT.DeseaseType_Name) as DeseaseType_Name
		from
			v_EvnVizitPLStom EVPLS WITH (NOLOCK)
			cross apply (
				select top 1 MedPersonal_TabCode from v_MedPersonal (nolock)
				where 
					MedPersonal_id = EVPLS.MedPersonal_id
					and Lpu_id = EVPLS.Lpu_id
			) mp
			left join LpuSection LS (nolock) on LS.LpuSection_id = EVPLS.LpuSection_id
			inner join Diag (nolock) on Diag.Diag_id = EVPLS.Diag_id
			left join DeseaseType DT (nolock) on DT.DeseaseType_id = EVPLS.DeseaseType_id
		where EVPLS.EvnVizitPLStom_pid = :EvnPLStom_id
		";
		$result = $this->db->query($query, array(
			'EvnPLStom_id' => $data['EvnPLStom_id']
		));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}


	/**
	 *	Получение списка сопутствующих диагнозов для печати стомат. ТАП
	 */
	function getEvnDiagPLSopData($data) {
		$query = "
			select
				convert(varchar(10), EDPLS.EvnDiagPLStomSop_setDate, 104) as EvnDiagPL_setDate,
				RTRIM(LS.LpuSection_Name) as LpuSection_Name,
				RTRIM(MP.MedPersonal_TabCode) as MedPersonal_Code,
				RTRIM(Diag.Diag_Code) as Diag_Code,
				RTRIM(DT.DeseaseType_Name) as DeseaseType_Name
			from
				v_EvnDiagPLStomSop EDPLS WITH (NOLOCK)
				inner join v_EvnVizitPLStom EVPL (nolock) on EVPL.EvnVizitPLStom_id = EDPLS.EvnDiagPLStomSop_pid
				left join LpuSection LS (nolock) on LS.LpuSection_id = EVPL.LpuSection_id
				cross apply	(
					select top 1 MedPersonal_TabCode 
					from v_MedPersonal WITH (NOLOCK)
					where
						MedPersonal_id = EVPL.MedPersonal_id
						and Lpu_id = EVPL.Lpu_id
				) mp
				left join Diag (nolock) on Diag.Diag_id = EDPLS.Diag_id
				left join DeseaseType DT (nolock) on DT.DeseaseType_id = EDPLS.DeseaseType_id
			where EVPL.EvnVizitPLStom_pid = :EvnPLStom_id
		";
		$result = $this->db->query($query, array(
			'EvnPLStom_id' => $data['EvnPLStom_id']
		));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}


	/**
	 *	Получение данных об ЛВН для печати стомат. ТАП
	 */
	function getEvnStickData($data) {
		$query = "
			select
				convert(varchar(10), ES.EvnStick_begDate, 104) as EvnStick_begDate,
				convert(varchar(10), ES.EvnStick_endDate, 104) as EvnStick_endDate,
				RTRIM(ST.StickType_Name) as StickType_Name,
				RTRIM(ES.EvnStick_Ser) as EvnStick_Ser,
				RTRIM(ES.EvnStick_Num) as EvnStick_Num,
				RTRIM(SC.StickCause_Name) as StickCause_Name,
				RTRIM(SI.StickIrregularity_Name) as StickIrregularity_Name,
				RTRIM(Sex.Sex_Name) as Sex_Name,
				ES.EvnStick_Age
			from v_EvnStick ES WITH (NOLOCK)
				left join StickIrregularity SI (nolock) on SI.StickIrregularity_id = ES.StickIrregularity_id
				left join StickType ST (nolock) on ST.StickType_id = ES.StickType_id
				left join StickCause SC (nolock) on SC.StickCause_id = ES.StickCause_id
				left join Sex (nolock) on Sex.Sex_id = ES.Sex_id
			where ES.EvnStick_pid = :EvnStick_pid
		";
		$result = $this->db->query($query, array(
			'EvnStick_pid' => $data['EvnPLStom_id']
		));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 *	Получение списка освобождений от работы для печати стомат. ТАП
	 */
	function getEvnStickWorkReleaseData($data) {
		$query = "
			select top 4
				RTRIM(LTRIM(ISNULL(ES.EvnStick_Ser, '') + ' ' + ISNULL(ES.EvnStick_Num, ''))) as EvnStick_SerNum,
				convert(varchar(10), ESWR.EvnStickWorkRelease_begDT, 104) as EvnStickWorkRelease_begDate,
				convert(varchar(10), ESWR.EvnStickWorkRelease_endDT, 104) as EvnStickWorkRelease_endDate,
				RTRIM(MP.Person_Fio) as MedPersonal_Fio
			from v_EvnStickWorkRelease ESWR with (nolock)
				inner join v_EvnStick ES with (nolock) on ES.EvnStick_id = ESWR.EvnStickBase_id
				outer apply (
					select top 1
						Person_Fio
					from
						v_MedPersonal with (nolock)
					where
						MedPersonal_id = ESWR.MedPersonal_id
				) MP
			where
				ES.EvnStick_pid = :EvnStick_pid
			order by
				ES.EvnStick_begDate
		";
		$result = $this->db->query($query, array(
			'EvnStick_pid' => $data['EvnPLStom_id']
		));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	
	/**
	 * @param $data
	 * @return bool
	 * Получение данных для уфимских ТАП
	 */
	function getEvnPLStomFieldsUfa($data) {
		$query = "
			select
				RTRIM(ISNULL(Lpu.Lpu_Nick, '')) as Lpu_Name,
				ISNULL(convert(varchar(10), EvnPLStom.EvnPLStom_setDT, 104), '') as EvnPLStom_setDate,
				RTRIM(ISNULL(OJ.Org_Name, '')) as OrgJob_Name,
				PS.Person_id,
				RTRIM(RTRIM(ISNULL(PS.Person_Surname, '')) + ' ' + RTRIM(ISNULL(PS.Person_Firname, '')) + ' ' + RTRIM(ISNULL(PS.Person_Secname, ''))) as Person_Fio,
				convert(varchar(10), PS.Person_Birthday, 104) as Person_Birthday,
				RTRIM(Sex.Sex_Name) as Sex_Name,
				'' as Person_INN,
				PS.Person_Snils,
				case when PC.Lpu_id = :Lpu_id then RTRIM(PC.PersonCard_Code) else null end as PersonCard_Code,
				RTRIM(ISNULL(OS.Org_Name, '')) as OrgSmo_Name,
				convert(varchar(10), Polis.Polis_begDate, 104) as Polis_begDate,
				convert(varchar(10), Polis.Polis_endDate, 104) as Polis_endDate,
				CASE WHEN PolisType.PolisType_Code = 4 then '' ELSE RTRIM(ISNULL(Polis.Polis_Ser, '')) END as Polis_Ser,
				CASE WHEN PolisType.PolisType_Code = 4 then isnull(RTRIM(PS.Person_EdNum), '') ELSE RTRIM(ISNULL(Polis.Polis_Num, '')) END AS Polis_Num,
				RTRIM(ISNULL(SocStatus.SocStatus_Name, '')) as SocStatus_Name,
				RTRIM(ISNULL(PAddr.Address_Address, '')) as PAddress_Name,
				RTRIM(ISNULL(UAddr.Address_Address, '')) as UAddress_Name,
				RTRIM(ISNULL(LpuRegion.LpuRegion_Name, '')) as LpuRegion_Name,
				'' as DiagSopAgg_Code,
				ISNULL(Diag.Diag_Code, '') as PrehospDiag_Code,
				'' as PrehospDiag_regDate,
				ISNULL(convert(varchar(10), Document.Document_begDate, 104), '') as Document_begDate,
				RTRIM(ISNULL(Document.Document_Num, '')) as Document_Num,
				RTRIM(ISNULL(Document.Document_Ser, '')) as Document_Ser,
				RTRIM(ISNULL(DocumentType.DocumentType_Name, '')) as DocumentType_Name,
				RTRIM(ISNULL(DirectType.DirectType_SysNick, '')) as DirectType_SysNick,
				RTRIM(ISNULL(PHT.PrehospTrauma_Code, 0)) as PrehospTrauma_Code,
				RTRIM(ISNULL(ResultClass.ResultClass_SysNick, '')) as ResultClass_SysNick,
				RTRIM(ISNULL(EvnVizitPLStom.Diag_Code, '')) as FinalDiag_Code,
				RTRIM(ISNULL(EvnVizitPLStom.DiagAgg_Code, '')) as DiagAgg_Code,
				RTRIM(ISNULL(EvnVizitPLStom.DeseaseType_SysNick, '')) as FinalDeseaseType_SysNick,
				RTRIM(ISNULL(EvnVizitPLStom.PayType_Name, '')) as PayType_Name,
				RTRIM(ISNULL(EvnVizitPLStom.ServiceType_Name, '')) as ServiceType_Name,
				RTRIM(ISNULL(EvnVizitPLStom.VizitType_SysNick, '')) as VizitType_SysNick,
				RTRIM(ISNULL(EvnDiagPLSop.Diag_Code, '')) as DiagSop_Code,
				RTRIM(ISNULL(EvnDiagPLSop.DeseaseType_SysNick, '')) as DeseaseTypeSop_SysNick,
				RTRIM(ISNULL(EvnVizitPLStom.MedPersonal_Fio, '')) as MedPersonal_Fio,
				RTRIM(ISNULL(EvnVizitPLStom.LpuSectionProfile_Code, '')) as LpuSectionProfile_Code,
				RTRIM(ISNULL(EvnVizitPLStom.LpuSectionProfile_Name, '')) as LpuSectionProfile_Name,
				EvnStick.EvnStick_Age,
				EvnStick.EvnStick_begDate,
				EvnStickWorkRelease.EvnStickWorkRelease_endDate as EvnStick_endDate,
				ISNULL(EvnStick.Sex_Code, 0) as EvnStick_Sex,
				RTRIM(ISNULL(EvnStick.StickCause_SysNick, '')) as StickCause_SysNick,
				RTRIM(ISNULL(EvnStick.StickType_SysNick, '')) as StickType_SysNick,
				PDP.PersonDeputy_Fio
			from v_EvnPLStom EvnPLStom WITH (NOLOCK)
				inner join v_Lpu Lpu with (nolock) on Lpu.Lpu_id = EvnPLStom.Lpu_id
					and Lpu.Lpu_id " . getLpuIdFilter($data) . "
				inner join v_Person_all PS with (nolock) on PS.Server_id = EvnPLStom.Server_id
					and PS.PersonEvn_id = EvnPLStom.PersonEvn_id
				left join Sex with (nolock) on Sex.Sex_id = PS.Sex_id
				left join [Address] UAddr with (nolock) on UAddr.Address_id = PS.UAddress_id
				left join [Address] PAddr with (nolock) on PAddr.Address_id = PS.PAddress_id
				left join Document with (nolock) on Document.Document_id = PS.Document_id
				left join DocumentType with (nolock) on DocumentType.DocumentType_id = Document.DocumentType_id
				left join Job with (nolock) on Job.Job_id = PS.Job_id
				left join Org OJ with (nolock) on OJ.Org_id = Job.Org_id
				left join Diag with (nolock) on Diag.Diag_id = EvnPLStom.Diag_did
				left join v_PersonCard PC with (nolock) on PC.Person_id = PS.Person_id
					and PC.PersonCard_begDate is not null
					and PC.PersonCard_begDate <= EvnPLStom.EvnPLStom_insDT
					and (PC.PersonCard_endDate is null or PC.PersonCard_endDate > EvnPLStom.EvnPLStom_insDT)
					and PC.Lpu_id = EvnPLStom.Lpu_id
				left join v_LpuRegion as LpuRegion with (nolock) on LpuRegion.LpuRegion_id = PC.LpuRegion_id
				left join Polis with (nolock) on Polis.Polis_id = PS.Polis_id
				left join PolisType with (nolock) on PolisType.PolisType_id = Polis.PolisType_id
				left join OrgSmo with (nolock) on OrgSmo.OrgSmo_id = Polis.OrgSmo_id
				left join Org OS with (nolock) on OS.Org_id = OrgSmo.Org_id
				left join DirectType with (nolock) on DirectType.DirectType_id = EvnPLStom.DirectType_id
				left join v_PrehospTrauma PHT with (nolock) on PHT.PrehospTrauma_id = EvnPLStom.PrehospTrauma_id
				left join ResultClass with (nolock) on ResultClass.ResultClass_id = EvnPLStom.ResultClass_id
				left join SocStatus with (nolock) on SocStatus.SocStatus_id = PS.SocStatus_id
				outer apply (
					select top 1
						AD.Diag_Code as DiagAgg_Code,
						D.Diag_Code,
						DT.DeseaseType_SysNick,
						PT.PayType_Name,
						ST.ServiceType_Name,
						VT.VizitType_SysNick,
						MP.MedPersonal_TabCode,
						MP.Person_Fio as MedPersonal_Fio,
						LSP.LpuSectionProfile_Code,
						LSP.LpuSectionProfile_Name
					from v_EvnVizitPLStom EVPL with (nolock)
						left join LpuSection LS with (nolock) on LS.LpuSection_id = EVPL.LpuSection_id
						left join LpuSectionProfile LSP with (nolock) on LSP.LpuSectionProfile_id = LS.LpuSectionProfile_id
						left join Diag D with (nolock) on D.Diag_id = EVPL.Diag_id
						left join Diag AD with (nolock) on AD.Diag_id = EVPL.Diag_agid
						left join DeseaseType DT with (nolock) on DT.DeseaseType_id = EVPL.DeseaseType_id
						left join v_MedPersonal MP with (nolock) on MP.MedPersonal_id = EVPL.MedPersonal_id
							and MP.Lpu_id = EvnPLStom.Lpu_id
						left join PayType PT with (nolock) on PT.PayType_id = EVPL.PayType_id
						left join ServiceType ST with (nolock) on ST.ServiceType_id = EVPL.ServiceType_id
						left join VizitType VT with (nolock) on VT.VizitType_id = EVPL.VizitType_id
					where EVPL.EvnVizitPLStom_pid = EvnPLStom.EvnPLStom_id
					order by
						EVPL.EvnVizitPLStom_id
				) EvnVizitPLStom
				outer apply (
					select top 1
						D.Diag_Code,
						DT.DeseaseType_SysNick
					from v_EvnDiagPLSop EDPLS with (nolock)
						left join Diag D with (nolock) on D.Diag_id = EDPLS.Diag_id
						left join DeseaseType DT with (nolock) on DT.DeseaseType_id = EDPLS.DeseaseType_id
					where EDPLS.EvnDiagPLSop_rid = EvnPLStom.EvnPLStom_id
					order by
						EDPLS.EvnDiagPLSop_id
				) EvnDiagPLSop
				outer apply (
					select top 1
						ES.EvnStick_id,
						ES.EvnStick_Age,
						convert(varchar(10), ES.EvnStick_begDate, 104) as EvnStick_begDate,
						SC.StickCause_SysNick,
						ST.StickType_SysNick,
						Sex.Sex_Code
					from v_EvnStick ES with (nolock)
						left join StickCause SC with (nolock) on SC.StickCause_id = ES.StickCause_id
						left join StickType ST with (nolock) on ST.StickType_id = ES.StickType_id
						left join Sex with (nolock) on Sex.Sex_id = ES.Sex_id
					where ES.EvnStick_pid = EvnPLStom.EvnPLStom_id
					order by ES.EvnStick_id
				) EvnStick
				outer apply (
					select convert(varchar(10), max(EvnStickWorkRelease_endDT), 104) as EvnStickWorkRelease_endDate
					from v_EvnStickWorkRelease with (nolock)
					where EvnStickBase_id = EvnStick.EvnStick_id
				) EvnStickWorkRelease
				outer apply (
					select top 1
						RTRIM(RTRIM(ISNULL(PDEPS.Person_Surname, '')) + ' ' + RTRIM(ISNULL(PDEPS.Person_Firname, '')) + ' ' + RTRIM(ISNULL(PDEPS.Person_Secname, ''))) as PersonDeputy_Fio
					from
						v_PersonDeputy PDEP with (nolock)
						left join v_PersonState PDEPS with (nolock) on PDEPS.Person_id = PDEP.Person_pid
					where
						PDEP.Person_id = PS.Person_id
				) PDP
			where
				EvnPLStom.EvnPLStom_id = :EvnPLStom_id
		";
		$result = $this->db->query($query, array(
			'EvnPLStom_id' => $data['EvnPLStom_id'],
			'Lpu_id' => $data['Lpu_id']
		));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}


	/**
	 * @param $data
	 * @return bool
	 */
	function getEvnVizitPLStomDataUfa($data) {
		$query = "
			select
				 convert(varchar(10), EVPLS.EvnVizitPLStom_setDT, 104) as EvnVizitPL_setDate
				,ROUND(EPLS.EvnPLStom_UKL, 2) as EvnVizitPL_UKL
				,UC.UslugaComplex_Code
			from v_EvnUsluga EU with (nolock)
				inner join v_EvnVizitPLStom EVPLS with (nolock) on EVPLS.EvnVizitPLStom_id = EU.EvnUsluga_pid
				inner join v_EvnPLStom EPLS with (nolock) on EPLS.EvnPLStom_id = EVPLS.EvnVizitPLStom_pid
				inner join v_UslugaComplex UC with (nolock) on UC.UslugaComplex_id = EU.UslugaComplex_id
				inner join v_UslugaCategory UCat with (nolock) on UCat.UslugaCategory_id = UC.UslugaCategory_id
			where EU.EvnUsluga_rid = :EvnPLStom_id
				and EPLS.Lpu_id " . getLpuIdFilter($data) . "
				and UCat.UslugaCategory_SysNick = 'lpusection'
			order by
				EVPLS.EvnVizitPLStom_setDate
		";
		$result = $this->db->query($query, array(
			'EvnPLStom_id' => $data['EvnPLStom_id'],
			'Lpu_id' => $data['Lpu_id']
		));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}


	/**
	 * @param $data
	 * @return bool
	 */
	function getEvnReceptData($data) {
		$query = "
			select
				convert(varchar(10), ER.EvnRecept_setDate, 104) as ER_EvnRecept_setDate,
				ER.EvnRecept_Ser as ER_EvnRecept_Ser,
				ER.EvnRecept_Num as ER_EvnRecept_Num,
				Diag.Diag_Code as ER_Diag_Code,
				ISNULL(DrugRls.Drug_Name, Drug.Drug_Name) as ER_Drug_Name,
				ER.EvnRecept_Kolvo as ER_EvnRecept_Kolvo
			from v_EvnRecept ER WITH (NOLOCK)
				inner join v_Diag Diag WITH (NOLOCK) on Diag.Diag_id = ER.Diag_id
				left join v_Drug Drug WITH (NOLOCK) on Drug.Drug_id = ER.Drug_id
				left join rls.v_Drug DrugRls WITH (NOLOCK) on DrugRls.Drug_id = ER.Drug_rlsid
			where ER.EvnRecept_rid = :EvnPLStom_id
		";
		$result = $this->db->query($query, array(
			'EvnPLStom_id' => $data['EvnPLStom_id'],
			'Lpu_id' => $data['Lpu_id']
		));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}


	/**
	 *	Проверка стомат. ТАП на дубли
	 */
	function checkEvnPLStomDoubles($data) {
		$query = "
			select
				count(EvnPLStom_id) as EvnPLStomCount
			from
				v_EvnPLStom WITH (NOLOCK)
			where
				Lpu_id = :Lpu_id
				and EvnPLStom_NumCard = :EvnPLStom_NumCard
				and EvnPLStom_id <> ISNULL(:EvnPLStom_id, 0)
		";
		$result = $this->db->query($query, array(
			'EvnPLStom_id' => $data['EvnPLStom_id'],
			'EvnPLStom_NumCard' => $data['EvnPLStom_NumCard'],
			'Lpu_id' => $data['Lpu_id']
		));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 *	Получение номера стомат. ТАП
	 */
	function getEvnPLStomNumber($data) {
		$query = "
			declare @EvnPLStom_NumCard bigint;
			exec xp_GenpmID @ObjectName = 'EvnPLStom', @Lpu_id = :Lpu_id, @ObjectID = @EvnPLStom_NumCard output;
			select @EvnPLStom_NumCard as EvnPLStom_NumCard;
		";
		/*
		$result = $this->db->query($query, array('Lpu_id' => $data['Lpu_id']));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
		
		 */
		$num = 1;
		$incrementMax = 100;
		$again = false;
		do {
			$result = $this->db->query($query, array('Lpu_id' => $data['Lpu_id']));
			if ( is_object($result) ) {
				$result = $result->result('array');
				if(!empty($result[0]['EvnPLStom_NumCard'])){
					$query2 = "
						select top 1
							EvnPL.EvnPL_id
						from
							EvnPL WITH (NOLOCK)
							inner join Evn WITH (NOLOCK) on Evn.Evn_id = EvnPL.EvnPL_id
						where
							Evn.Lpu_id = :Lpu_id
							and IsNull(Evn.Evn_deleted,1) = 1
							and EvnPL.EvnPL_NumCard = :NumCard
							and year(Evn.Evn_setDT) = year(:setDT)
							and Evn.EvnClass_id = {$this->evnClassId}
					";
					$EvnPL_id = $this->getFirstResultFromQuery($query2, array(
						'NumCard' => $result[0]['EvnPLStom_NumCard'],
						'setDT' => date('Y-m-d'),
						'Lpu_id' => $data['Lpu_id']
					));
					if ($EvnPL_id > 0) {
						$again = true;
					} else {
						$again = false;
					}
				} else {
					$again = false;
					$result = false;
				}
			} else {
				$again = false;
				$result = false;
			}
			$num++;
		} while ($again && $num <= $incrementMax);
		
		
		return $result;
	}

	/**
	 * Получение части запроса для определения прав доступа к форме редатирования события
	 */
	function getAccessTypeQueryPart($data, &$params) {
		$EvnClass = !empty($data['EvnClass'])?$data['EvnClass']:$this->evnClassSysNick;
		$EvnAlias = !empty($data['EvnAlias'])?$data['EvnAlias']:$this->evnClassSysNick;
		$session = $data['session'];

		$linkLpuIdList = isset($session['linkedLpuIdList'])?$session['linkedLpuIdList']:array();
		$linkLpuIdList_str = count($linkLpuIdList)>0?implode(',', $linkLpuIdList):'0';

		$queryPart = "
			case
				when {$EvnAlias}.Lpu_id = :Lpu_id and :LpuSection_id in (select LpuSection_id
					from v_EvnVizitPLStom with (nolock)
					where EvnVizitPLStom_pid = :{$EvnClass}_id) then 1
				when {$EvnAlias}.Lpu_id in ({$linkLpuIdList_str}) and ISNULL({$EvnAlias}.{$EvnClass}_IsTransit, 1) = 2 then 1
				when (:isMedStatUser = 1 or :withoutMedPersonal = 1) and {$EvnAlias}.Lpu_id = :Lpu_id then 1
				when :isSuperAdmin = 1 then 1
				else 0
			end = 1
		";

		$params['LpuSection_id'] = !empty($data['session']['CurLpuSection_id']) ? $data['session']['CurLpuSection_id'] : null;
		$params['isMedStatUser'] = isMstatArm($data);
		$params['isSuperAdmin'] = isSuperadmin();
		$params['withoutMedPersonal'] = ((isLpuAdmin() || isLpuUser()) && $data['session']['medpersonal_id'] == 0);

		$this->load->helper('MedStaffFactLink');
		$med_personal_list = getMedPersonalListWithLinks();

		if ( $session['isMedStatUser'] == false && count($med_personal_list)>0 && !isSuperadmin()) {
			$queryPart .= "and exists (
				select top 1 t1.MedStaffFact_id
				from v_MedStaffFact t1 with (nolock)
					inner join v_LpuUnit t2 with (nolock) on t2.LpuUnit_id = t1.LpuUnit_id
					inner join v_LpuUnitType t3 with (nolock) on t3.LpuUnitType_id = t2.LpuUnitType_id
				where t1.MedPersonal_id in (".implode(',',$med_personal_list).")
					and t1.WorkData_begDate <= ISNULL({$EvnAlias}.{$EvnClass}_disDate, dbo.tzGetDate())
					and (t1.WorkData_endDate is null or t1.WorkData_endDate >= ISNULL({$EvnAlias}.{$EvnClass}_disDate, {$EvnAlias}.{$EvnClass}_setDate))
					and t2.LpuUnitType_SysNick in ('polka', 'ccenter', 'traumcenter', 'fap')
			)";
		}

		return $queryPart;
	}

	/**
	 *	Получение данных для формы редактирования стомат. ТАП
	 */
	function loadEvnPLStomEditForm($data) {
		$params = array(
			'EvnPLStom_id' => $data['EvnPLStom_id'],
			'Lpu_id' => $data['Lpu_id']
		);
		
		$fields = "";
		$joins = "";
		
		if (getRegionNick() == 'kz') {
			$fields .= " ,msl.MedicalStatus_id";
			$joins .= " left join r101.EvnPlMedicalStatusLink msl (nolock) on msl.EvnPL_id = EPLS.EvnPLStom_id ";
		}

		$accessType = $this->getAccessTypeQueryPart(array(
			'EvnAlias' => 'EPLS',
			'session' => $data['session'],
		), $params);

		$selectEvnDirectionData = "
			EPLS.PrehospDirect_id,
			EPLS.EvnDirection_Num,
			convert(varchar(10), EPLS.EvnDirection_setDT, 104) as EvnDirection_setDate,
			case when 1 = isnull(ED.EvnDirection_IsAuto,1) then coalesce(EPLS.Org_did, LPUDID.Org_id, ED.Org_sid) 
				else isnull(EPLS.Org_did, LPUDID.Org_id)
			end as Org_did,
			EPLS.Lpu_did,
			EPLS.LpuSection_did,
			EPLS.Diag_did,
			EPLS.EvnDirection_id,
			isnull(ED.EvnDirection_IsAuto,1) as EvnDirection_IsAuto,
			isnull(ED.EvnDirection_IsReceive,1) as EvnDirection_IsReceive,
			isnull(ED.Lpu_sid,ED.Lpu_id) as Lpu_fid,
		";
		
		$query = "
			SELECT TOP 1
				case when {$accessType} then 'edit' else 'view' end as accessType,
				case when EPLS.Lpu_id = :Lpu_id and EPLS.EvnPLStom_IsFinish != 2 then 'true' else 'false' end as canCreateVizit,
				{$selectEvnDirectionData}
				EPLS.Diag_did as Diag_id,
				EPLS.Diag_fid,
				EPLS.Diag_lid,
				EPLS.Diag_preid,
				EPLS.Diag_concid,
				EPLS.DirectClass_id,
				EPLS.DirectType_id,
				EPLS.EvnPLStom_id,
				convert(varchar(10), EPLS.EvnPLStom_setDT, 104) as EvnPLStom_setDate,
				convert(varchar(10), EPLS.EvnPLStom_disDate, 104) as EvnPLStom_disDate,
				EPLS.EvnPLStom_IsFinish,
				EPLS.EvnPLStom_IsSurveyRefuse,
				EPLS.EvnPLStom_IsSan,
				EPLS.EvnPLStom_IsUnlaw,
				EPLS.EvnPLStom_IsUnport,
				RTRIM(EPLS.EvnPLStom_NumCard) as EvnPLStom_NumCard,
				case when EPLS.EvnPLStom_IsCons = 2 then 1 else 0 end as EvnPLStom_IsCons,
				ROUND(EPLS.EvnPLStom_UKL, 3) as EvnPLStom_UKL,
				EPLS.Lpu_oid,
				EPLS.LpuSection_oid,
				EPLS.Person_id,
				EPLS.PersonEvn_id,
				EPLS.PrehospTrauma_id,
				EPLS.ResultClass_id,
				EPLS.ResultDeseaseType_id,
				EPLS.InterruptLeaveType_id,
				EPLS.LeaveType_fedid,
				EPLS.ResultDeseaseType_fedid,
				EPLS.SanationStatus_id,
				EPLS.MedicalCareKind_id,
				EPLS.Server_id,
				convert(varchar(10), ecp.EvnCostPrint_setDT, 104) as EvnCostPrint_setDT,
				ecp.EvnCostPrint_IsNoPrint,
				ISNULL(EPLS.EvnPLStom_IsPaid, 1) as EvnPLStom_IsPaid,
				ISNULL(EPLS.EvnPLStom_IndexRep, 0) as EvnPLStom_IndexRep,
				ISNULL(EPLS.EvnPLStom_IndexRepInReg, 1) as EvnPLStom_IndexRepInReg
				{$fields}
			FROM
				v_EvnPLStom EPLS with (nolock)
				left join v_EvnCostPrint ecp (nolock) on ecp.Evn_id = EPLS.EvnPLStom_id
				left join v_EvnDirection_all ED with (nolock) on ED.EvnDirection_id = EPLS.EvnDirection_id
				left join v_Lpu LPUDID with (nolock) on LPUDID.Lpu_id = EPLS.Lpu_did
				outer apply (
					select top 1
						EVPLS.LpuSection_id as EvnLpuSection_id
					from
						v_EvnVizitPLStom EVPLS with (nolock)
					where
						EVPLS.EvnVizitPLStom_pid = EPLS.EvnPLStom_id
				) EVPLS
				{$joins}
			WHERE (1 = 1)
				and EPLS.EvnPLStom_id = :EvnPLStom_id
				and (EPLS.Lpu_id " . getLpuIdFilter($data) . " or " . (isset($data['session']['medpersonal_id']) && $data['session']['medpersonal_id'] > 0 ? 1 : 0) . " = 1)
		";
		$result = $this->db->query($query, $params);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}


	/**
	 *	Получение данных для формы редактирования стомат. ТАП
	 */
	function loadEvnPLStomEditFormForDelDocs($data) {
		$params = ['EvnPLStom_id' => $data['EvnPLStom_id'],	'Lpu_id' => $data['Lpu_id']];

		$fields = "";
		$joins = "";

		if (getRegionNick() == 'kz') {
			$fields .= " ,msl.MedicalStatus_id";
			$joins .= " left join r101.EvnPlMedicalStatusLink msl (nolock) on msl.EvnPL_id = EPLS.EvnPLStom_id ";
		}

		$selectEvnDirectionData = "
			EvnPL.PrehospDirect_id,
			EvnPL.EvnDirection_Num,
			convert(varchar(10), EvnPL.EvnDirection_setDT, 104) as EvnDirection_setDate,
			case when 1 = isnull(ED.EvnDirection_IsAuto,1) then coalesce(EvnPL.Org_did, LPUDID.Org_id, ED.Org_sid) 
				else isnull(EvnPL.Org_did, LPUDID.Org_id)
			end as Org_did,
			EvnPL.Lpu_did,
			EvnPL.LpuSection_did,
			EvnPL.Diag_did,
			EvnPL.EvnDirection_id,
			isnull(ED.EvnDirection_IsAuto,1) as EvnDirection_IsAuto,
			isnull(ED.EvnDirection_IsReceive,1) as EvnDirection_IsReceive,
			isnull(ED.Lpu_sid,ED.Lpu_id) as Lpu_fid,
		";

		$query = "
			select top 1
				'view' as accessType,
				'false' as canCreateVizit,
				{$selectEvnDirectionData}
				EvnPL.Diag_did as Diag_id,
				EvnPL.Diag_fid,
				EvnPL.Diag_lid,
				EvnPL.Diag_preid,
				EvnPL.Diag_concid,
				EvnPL.DirectClass_id,
				EvnPL.DirectType_id,
				EPLS.EvnPLStom_id,
				convert(varchar(10), Evn.Evn_setDT, 104) as EvnPLStom_setDate,
				convert(varchar(10), Evn.Evn_disDT, 104) as EvnPLStom_disDate,
				EvnPLBase.EvnPLBase_IsFinish as EvnPLStom_IsFinish,
				EvnPL.EvnPL_IsSurveyRefuse as EvnPLStom_IsSurveyRefuse,
				EPLS.EvnPLStom_IsSan,
				EvnPL.EvnPL_IsUnlaw as EvnPLStom_IsUnlaw,
				EvnPL.EvnPL_IsUnport as EvnPLStom_IsUnport,
				RTRIM(EvnPL.EvnPL_NumCard) as EvnPLStom_NumCard,
				case when EvnPL.EvnPL_IsCons = 2 then 1 else 0 end as EvnPLStom_IsCons,
				ROUND(EvnPL.EvnPL_UKL, 3) as EvnPLStom_UKL,
				EvnPL.Lpu_oid,
				EvnPL.LpuSection_oid,
				Evn.Person_id,
				Evn.PersonEvn_id,
				EvnPL.PrehospTrauma_id,
				EvnPL.ResultClass_id,
				EvnPL.ResultDeseaseType_id,
				EvnPL.InterruptLeaveType_id,
				EvnPL.LeaveType_fedid,
				EvnPL.ResultDeseaseType_fedid,
				EPLS.SanationStatus_id,
				EvnPL.MedicalCareKind_id,
				Evn.Server_id,
				convert(varchar(10), ecp.EvnCostPrint_setDT, 104) as EvnCostPrint_setDT,
				ecp.EvnCostPrint_IsNoPrint,
				ISNULL(EvnPL.EvnPL_IsPaid, 1) as EvnPLStom_IsPaid,
				ISNULL(EvnPL.EvnPL_IndexRep, 0) as EvnPLStom_IndexRep,
				ISNULL(EvnPL.EvnPL_IndexRepInReg, 1) as EvnPLStom_IndexRepInReg
				{$fields}
			FROM
				EvnPLStom EPLS with (nolock)
				inner join Evn with(nolock) on EPLS.EvnPL_id = Evn.Evn_id and Evn.EvnClass_id in (6)
				inner join EvnPLBase ON Evn.Evn_id = EvnPLBase.Evn_id
				inner join EvnPL on EvnPLBase.EvnPLBase_id = EvnPL.EvnPLBase_id
				left join v_EvnCostPrint ecp (nolock) on ecp.Evn_id = EPLS.EvnPLStom_id
				left join v_EvnDirection_all ED with (nolock) on ED.EvnDirection_id = EvnPL.EvnDirection_id
				left join v_Lpu LPUDID with (nolock) on LPUDID.Lpu_id = EvnPL.Lpu_did
				outer apply (
					select top 1
						EVPLS.LpuSection_id as EvnLpuSection_id
					from
						v_EvnVizitPLStom EVPLS with (nolock)
					where
						EVPLS.EvnVizitPLStom_pid = EPLS.EvnPLStom_id
				) EVPLS
				{$joins}
			WHERE (1 = 1)
				and EPLS.EvnPLStom_id = :EvnPLStom_id
				and (Evn.Lpu_id " . getLpuIdFilter($data) . " or " . (isset($data['session']['medpersonal_id']) && $data['session']['medpersonal_id'] > 0 ? 1 : 0) . " = 1)
		";
		$result = $this->db->query($query, $params);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}
	/**
	 *	Получение списка стомат. ТАП для текущей сессии поточного ввода
	 */
	function loadEvnPLStomStreamList($data) {
		$filter = '(1 = 1)';
		$queryParams = array();

		$filter .= " and EPLS.pmUser_insID = :pmUser_id";
		$queryParams['pmUser_id'] = $data['pmUser_id'];

		if ( (isset($data['begDate'])) && (isset($data['begTime'])) ) {
			$filter .= " and EPLS.EvnPLStom_insDT >= :EvnPLStom_insDT";
			$queryParams['EvnPLStom_insDT'] = $data['begDate'] . " " . $data['begTime'];
		}

		if ( $data['Lpu_id'] > 0 ) {
			$filter .= " and EPLS.Lpu_id = :Lpu_id";
			$queryParams['Lpu_id'] = $data['Lpu_id'];
		}

		$query = "
			SELECT DISTINCT
				EPLS.EvnPLStom_id as EvnPLStom_id,
				EPLS.Person_id as Person_id,
				EPLS.Server_id as Server_id,
				EPLS.PersonEvn_id as PersonEvn_id,
				RTRIM(EPLS.EvnPLStom_NumCard) as EvnPLStom_NumCard,
				RTRIM(PS.Person_Surname) as Person_Surname,
				RTRIM(PS.Person_Firname) as Person_Firname,
				RTRIM(PS.Person_Secname) as Person_Secname,
				convert(varchar(10), PS.Person_Birthday, 104) as Person_Birthday,
				convert(varchar(10), EPLS.EvnPLStom_setDate, 104) as EvnPLStom_setDate,
				convert(varchar(10), EPLS.EvnPLStom_disDate, 104) as EvnPLStom_disDate,
				EPLS.EvnPLStom_VizitCount as EvnPLStom_VizitCount,
				IsFinish.YesNo_Name as EvnPLStom_IsFinish,
				convert(varchar(10), ecp.EvnCostPrint_setDT, 104) as EvnCostPrint_setDT,
				case when ecp.EvnCostPrint_IsNoPrint = 2 then 'Отказ от справки' when ecp.EvnCostPrint_IsNoPrint = 1 then 'Справка выдана' else '' end as EvnCostPrint_IsNoPrintText
			FROM v_EvnPLStom EPLS with (nolock)
				inner join v_PersonState PS with (nolock) on PS.Person_id = EPLS.Person_id
				left join v_EvnCostPrint ecp (nolock) on ecp.Evn_id = EPLS.EvnPLStom_id
				left join YesNo IsFinish with (nolock) on IsFinish.YesNo_id = EPLS.EvnPLStom_IsFinish
			WHERE " . $filter . "
			ORDER BY EPLS.EvnPLStom_id desc
		";
		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 *	Получение списка стомат. ТАП
	 */
	function loadEvnVizitPLStomGrid($data) {
		$filter = "(1 = 1)";
		$queryParams = array();

		$queryParams['Lpu_id'] = $data['Lpu_id'];

		if ( isset($data['EvnPLStom_id']) ) {
			$filter .= " and EVPLS.EvnVizitPLStom_pid = :EvnPLStom_id";
			$queryParams['EvnPLStom_id'] = $data['EvnPLStom_id'];
		}

		if ( isset($data['EvnVizitPLStom_id']) ) {
			$filter .= " and EVPLS.EvnVizitPLStom_id = :EvnVizitPLStom_id";
			$queryParams['EvnVizitPLStom_id'] = $data['EvnVizitPLStom_id'];
		}

		$this->load->helper('MedStaffFactLink');
		$med_personal_list = getMedPersonalListWithLinks();

		$access_type = '
			case
				when EVPLS.Lpu_id = :Lpu_id and (EVPLS.LpuSection_id = SMP.LpuSection_id OR EVPLS.MedStaffFact_sid = :MedStaffFact_id) then 1
				' . (count($data['session']['linkedLpuIdList']) > 1 ? 'when EVPLS.Lpu_id in (' . implode(',', $data['session']['linkedLpuIdList']) . ') and ISNULL(EVPLS.EvnVizitPLStom_IsTransit, 1) = 2 then 1' : '') . '
				else 0
			end = 1
		';
		$queryParams['MedStaffFact_id'] = (!empty($data['session']['CurMedStaffFact_id'])) ? $data['session']['CurMedStaffFact_id'] : null;

		$query = "
			select
				case
					when {$access_type} " . ($data['session']['isMedStatUser'] == false && count($med_personal_list)>0 ? "and exists (select top 1 MedStaffFact_id from v_MedStaffFact with (nolock) where MedPersonal_id in (".implode(',',$med_personal_list).") and LpuSection_id = EVPLS.LpuSection_id and WorkData_begDate <= EVPLS.EvnVizitPLStom_setDate and (WorkData_endDate is null or WorkData_endDate >= EVPLS.EvnVizitPLStom_setDate))" : "") . " then 'edit'
					else 'view'
				end as accessType,
				EVPLS.EvnVizitPLStom_id,
				EVPLS.EvnVizitPLStom_pid as EvnPLStom_id,
				ISNULL(EVPLS.EvnVizitPLStom_IsSigned, 1) as EvnVizitPLStom_IsSigned,
				EVPLS.Person_id,
				EVPLS.PersonEvn_id,
				EVPLS.Server_id,
				LS.LpuSection_id,
				MP.MedPersonal_id,
				EVPLS.MedPersonal_sid,
				PT.PayType_id,
				EVPLS.ProfGoal_id,
				ST.ServiceType_id,
				VT.VizitType_id,
				EVPLS.EvnVizitPLStom_AssignedCure,
				EVPLS.EvnVizitPLStom_Examination,
				EVPLS.EvnVizitPLStom_ObjectiveData,
				EVPLS.EvnVizitPLStom_Recomendations,
				EVPLS.EvnVizitPLStom_Time,
				EVPLS.LpuSectionProfile_id,
				ROUND(EVPLS.EvnVizitPLStom_Uet, 2) as EvnVizitPLStom_Uet,
				ROUND(EVPLS.EvnVizitPLStom_UetOMS, 2) as EvnVizitPLStom_UetOMS,
				convert(varchar(10), EVPLS.EvnVizitPLStom_setDate, 104) as EvnVizitPLStom_setDate,
				EVPLS.EvnVizitPLStom_setTime,
				Diag.Diag_Count,
				RTRIM(LS.LpuSection_Name) as LpuSection_Name,
				RTRIM(MP.Person_Fio) as MedPersonal_Fio,
				RTRIM(PT.PayType_Name) as PayType_Name,
				RTRIM(ST.ServiceType_Name) as ServiceType_Name,
				RTRIM(VT.VizitType_Name) as VizitType_Name
			from v_EvnVizitPLStom EVPLS with (nolock)
				left join LpuSection LS with (nolock) on LS.LpuSection_id = EVPLS.LpuSection_id
				outer apply (
					select top 1 MedPersonal_id, Person_Fio
					from v_MedPersonal with (nolock)
					where MedPersonal_id = EVPLS.MedPersonal_id
						and Lpu_id " . getLpuIdFilter($data) . "
				) MP
				left join PayType PT with (nolock) on PT.PayType_id = EVPLS.PayType_id
				left join ServiceType ST with (nolock) on ST.ServiceType_id = EVPLS.ServiceType_id
				left join VizitType VT with (nolock) on VT.VizitType_id = EVPLS.VizitType_id
				outer apply (
					select count(ED.EvnDiagPLStom_id) as Diag_Count
					from v_EvnDiagPLStom ED with (nolock)
					where ED.EvnDiagPLStom_pid = EVPLS.EvnVizitPLStom_id
				) Diag
			where " . $filter . "
		";
		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 *	Получение данных стомат. ТАП для панели просмотра в ЭМК
	 */
	function getEvnPLStomViewData($data) {
		$params = array(
			'pmUser_id' => $data['pmUser_id'],
			'EvnPLStom_id' => $data['EvnPLStom_id'],
			'Lpu_id' => $data['Lpu_id']
		);
		$accessType = 'EPL.Lpu_id = :Lpu_id';
		$withMedStaffFact_from = '';
		if (isset($data['user_MedStaffFact_id']))
		{
			$accessType .= " AND LU.LpuUnitType_SysNick in ('polka', 'ccenter', 'traumcenter', 'fap')";
			$withMedStaffFact_from = 'left join v_MedStaffFact MSF with (nolock) on MSF.MedStaffFact_id = :MedStaffFact_id
				left join v_LpuUnit LU with (nolock) on MSF.LpuUnit_id = LU.LpuUnit_id
			';
			$params['MedStaffFact_id'] = $data['user_MedStaffFact_id'];
		}
		$this->load->model('CureStandart_model');
		$cureStandartCountQuery = $this->CureStandart_model->getCountQuery('D', 'PS.Person_BirthDay', 'isnull(EPL.EvnPLStom_setDT,dbo.tzGetDate())');
		$diagFedMesFileNameQuery = $this->CureStandart_model->getDiagFedMesFileNameQuery('D');

		$disableCancelSign = "";
		if (getRegionNick() != 'perm') {
			$disableCancelSign = "OR (
				ISNULL(EPL.EvnPLStom_IsInReg,1) = 2
				AND ISNULL(EPL.EvnPLStom_IsPaid,2) = 2
			)";
		}
		
		$fields = "";
		$joins = "";
		
		if (getRegionNick() == 'kz') {
			$fields .= " ,msl.MedicalStatus_id";
			$fields .= " ,ms.rus_name as MedicalStatus_Name";
			$joins .= " left join r101.EvnPlMedicalStatusLink msl (nolock) on msl.EvnPL_id = EPL.EvnPLStom_id ";
			$joins .= " left join r101.MedicalStatus ms (nolock) on ms.MedicalStatus_id = msl.MedicalStatus_id ";
		}
		
		$query = "
			SELECT TOP 1
				case when {$accessType} then 'edit' else 'view' end as accessType,
				EPL.EvnPLStom_id,
				isnull(convert(varchar(10), EPL.EvnPLStom_setDT, 104),'') as EvnPLStom_setDate,
				isnull(convert(varchar(10), EPL.EvnPLStom_disDT, 104),'') as EvnPLStom_disDate,
				RTRIM(EPL.EvnPLStom_NumCard) as EvnPLStom_NumCard,
				case when EPL.EvnPLStom_IsCons = 2 then 1 else 0 end as EvnPLStom_IsCons,
				EPL.EvnPLStom_IsSigned,
				case when (
					EPL.Lpu_id != :Lpu_id OR
					ISNULL(EPL.EvnPLStom_IsSigned,1) = 1
					{$disableCancelSign}
				) then 2 else 1 end as isDisabledCancelSigned,
				case when (EVPL.EvnVizitPLStom_id is not null AND ISNULL(EPL.EvnPLStom_IsFinish,1) = 2) then 2 else 1 end as EvnPLStom_IsOpenable,
				case when (EVPLDisp.EvnVizitPLStom_id is not null) then 2 else 1 end as EvnPLStom_IsDisp,
				isnull(EPL.EvnPLStom_IsFinish,1) as EvnPLStom_IsFinish,
				IsFinish.YesNo_Name as IsFinish_Name,
				EPL.ResultClass_id,
				RC.ResultClass_Code,
				RC.ResultClass_SysNick,
				RC.ResultClass_Name,
				ROUND(EPL.EvnPLStom_UKL, 3) as EvnPLStom_UKL,
				EPL.DirectType_id,
				DirT.DirectType_Name,
				EPL.DirectClass_id,
				DirC.DirectClass_Name,
				EPL.LpuSection_oid,
				PreDiag.Diag_Code as DiagPreid_Code,
				PreDiag.Diag_Name as DiagPreid_Name,
				LSO.LpuSection_Name as LpuSectionO_Name,
				EPL.Lpu_oid,
				LpuO.Lpu_Nick as LpuO_Nick,
				case when dbo.GetRegion() in (19, 59) then 1 else 0 end as isAllowFedResultFields,
				convert(varchar(10), EPL.EvnPLStom_disDT, 120) as EvnPLStom_disDateYmd,
				EPL.LeaveType_fedid,
				fedLT.LeaveType_Code as FedLeaveType_Code,
				fedLT.LeaveType_Name as FedLeaveType_Name,
				EPL.ResultDeseaseType_fedid,
				fedRDT.ResultDeseaseType_Code as FedResultDeseaseType_Code,
				fedRDT.ResultDeseaseType_Name as FedResultDeseaseType_Name,
				EPL.Diag_id,
				isnull(D.Diag_Code,'') as Diag_Code,
				isnull(D.Diag_Name,'') as Diag_Name,
				EPL.MedicalCareKind_id,
				MCK.MedicalCareKind_Code,
				MCK.MedicalCareKind_Name,
				DT.DeseaseType_Name,
				FM.CureStandart_Count,
				DFM.DiagFedMes_FileName,
				Lpu.Lpu_Nick,
				EPL.EvnPLStom_VizitCount as Children_Count,
				PT.PrehospTrauma_id,
				PT.PrehospTrauma_Name,
				IsSurveyRefuse.YesNo_id as EvnPL_IsSurveyRefuse,
				IsSurveyRefuse.YesNo_Name as IsSurveyRefuse_Name,
				IsUnlaw.YesNo_id as EvnPLStom_IsUnlaw,
				IsUnlaw.YesNo_Name as IsUnlaw_Name,
				IsUnport.YesNo_id as EvnPLStom_IsUnport,
				IsUnport.YesNo_Name as IsUnport_Name,
				PD.PrehospDirect_Name,
				PD.PrehospDirect_Code,
				convert(varchar(10), case when ED.EvnDirection_id is not null AND 1 = isnull(ED.EvnDirection_IsAuto, 1) then ED.EvnDirection_setDT else EPL.EvnDirection_setDT end,104) as EvnDirection_setDate,
				case when ED.EvnDirection_id is not null AND 1 = isnull(ED.EvnDirection_IsAuto, 1) then ED.EvnDirection_Num else EPL.EvnDirection_Num end as EvnDirection_Num,
				isnull(DD.Diag_Code,'') as DiagD_Code,
				isnull(DD.Diag_Name,'') as DiagD_Name,
				LSD.LpuSection_Name as LpuSectionD_Name,
				OD.Org_Nick as OrgD_Name,
				EPL.Person_id,
				EPL.PersonEvn_id,
				EPL.Server_id,
				IsSan.YesNo_id as EvnPLStom_IsSan,
				IsSan.YesNo_Name as IsSan_Name,
				EPL.SanationStatus_id,
				v_SanationStatus.SanationStatus_Name,
				RDT.ResultDeseaseType_id,
				RDT.ResultDeseaseType_Name,
				convert(varchar(10), ecp.EvnCostPrint_setDT, 104) as EvnCostPrint_setDT,
				ecp.EvnCostPrint_IsNoPrint,
				STR(ecp.EvnCostPrint_Cost, 19, 2) as CostPrint,
				EPL.Diag_concid,
				EPL.InterruptLeaveType_id,
				ILT.InterruptLeaveType_Name,
				EPL.Diag_fid,
				DiagF.Diag_Code as DiagF_Code,
				DiagF.Diag_Name as DiagF_Name,
				EPL.Diag_lid,
				DiagL.Diag_Code as DiagL_Code,
				DiagL.Diag_Name as DiagL_Name
				{$fields}
			FROM
				v_EvnPLStom EPL with (nolock)
				left join v_EvnCostPrint ecp (nolock) on ecp.Evn_id = EPL.EvnPLStom_id
				left join v_InterruptLeaveType ILT with(nolock) on ILT.InterruptLeaveType_id=EPL.InterruptLeaveType_id
				left join v_Diag PreDiag with(nolock) on PreDiag.Diag_id = EPL.Diag_preid
				left join v_ResultClass RC with (nolock) on EPL.ResultClass_id = RC.ResultClass_id
				left join v_ResultDeseaseType RDT with (nolock) on RDT.ResultDeseaseType_id = EPL.ResultDeseaseType_id
				left join v_DeseaseType DT with (nolock) on EPL.DeseaseType_id = DT.DeseaseType_id
				left join v_PrehospTrauma PT with (nolock) on EPL.PrehospTrauma_id = PT.PrehospTrauma_id
				left join v_PrehospDirect PD with (nolock) on EPL.PrehospDirect_id = PD.PrehospDirect_id
				left join v_EvnDirection_all ED with (nolock) on EPL.EvnDirection_id = ED.EvnDirection_id
				left join v_Lpu LD with (nolock) on PD.PrehospDirect_Code = 2 and LD.Lpu_id = case when ED.EvnDirection_id is not null AND 1 = isnull(ED.EvnDirection_IsAuto, 1) then isnull(ED.Lpu_sid,ED.Lpu_id) else EPL.Lpu_did end
				left join v_LpuSection LSD with (nolock) on PD.PrehospDirect_Code = 1 and case when ED.EvnDirection_id is not null AND 1 = isnull(ED.EvnDirection_IsAuto, 1) then ED.LpuSection_id else EPL.LpuSection_did end = LSD.LpuSection_id
				left join v_Org OD with (nolock) on PD.PrehospDirect_Code in (2,3,4,5,6) and OD.Org_id = case when ED.EvnDirection_id is not null AND 1 = isnull(ED.EvnDirection_IsAuto, 1) then isnull(ED.Org_sid,LD.Org_id) else ISNULL(LD.Org_id, EPL.Org_did) end
				left join v_Diag DD with (nolock) on DD.Diag_id = case when ED.EvnDirection_id is not null AND 1 = isnull(ED.EvnDirection_IsAuto, 1) then ED.Diag_id else EPL.Diag_did end
				left join v_YesNo IsFinish with (nolock) on isnull(EPL.EvnPLStom_IsFinish,1) = IsFinish.YesNo_id
				left join v_YesNo IsSurveyRefuse with (nolock) on isnull(EPL.EvnPLStom_IsSurveyRefuse,1) = IsSurveyRefuse.YesNo_id
				left join v_YesNo IsUnlaw with (nolock) on isnull(EPL.EvnPLStom_IsUnlaw,1) = IsUnlaw.YesNo_id
				left join v_YesNo IsUnport with (nolock) on isnull(EPL.EvnPLStom_IsUnport,1) = IsUnport.YesNo_id
				left join v_Diag D with (nolock) on EPL.Diag_id = D.Diag_id
				left join v_Diag DiagF with (nolock) on EPL.Diag_fid = DiagF.Diag_id
				left join v_Diag DiagL with (nolock) on EPL.Diag_lid = DiagL.Diag_id
				left join v_Lpu Lpu with (nolock) on EPL.Lpu_id = Lpu.Lpu_id
				left join v_DirectClass DirC with (nolock) on EPL.DirectClass_id = DirC.DirectClass_id
				left join v_DirectType DirT with (nolock) on EPL.DirectType_id = DirT.DirectType_id
				left join v_LpuSection LSO with (nolock) on EPL.LpuSection_oid = LSO.LpuSection_id
				left join v_Lpu LpuO with (nolock) on EPL.Lpu_oid = LpuO.Lpu_id
				left join v_YesNo IsSan with (nolock) on isnull(EPL.EvnPLStom_IsSan,1) = IsSan.YesNo_id
				left join v_SanationStatus with (nolock) on EPL.SanationStatus_id = v_SanationStatus.SanationStatus_id
				left join v_MedicalCareKind MCK with(nolock) on MCK.MedicalCareKind_id = EPL.MedicalCareKind_id
				left join fed.v_LeaveType fedLT with(nolock) on fedLT.LeaveType_id = EPL.LeaveType_fedid
				left join fed.v_ResultDeseaseType fedRDT with(nolock) on fedRDT.ResultDeseaseType_id = EPL.ResultDeseaseType_fedid
				outer apply (
					select top 1
						EvnVizitPLStom_id
					from
						v_EvnVizitPLStom with (nolock)
					where
						EvnVizitPLStom_pid = EPL.EvnPLStom_id
						and pmUser_insID = :pmUser_id
				) EVPL
				outer apply (
					select top 1
						EvnVizitPLStom_id
					from
						v_EvnVizitPLStom with (nolock)
					where
						EvnVizitPLStom_pid = EPL.EvnPLStom_id and 
						VizitType_id = 118
				) EVPLDisp
				left join v_PersonState PS with (nolock) on EPL.Person_id = PS.Person_id
				outer apply (
					{$cureStandartCountQuery}
				) FM
				outer apply (
					{$diagFedMesFileNameQuery}
				) DFM
				{$withMedStaffFact_from}
				{$joins}
			WHERE EPL.EvnPLStom_id = :EvnPLStom_id
		";

		/*
		echo getDebugSql($query, $params);
		exit;
		*/

		$result = $this->db->query($query, $params);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Извлечение значений параметров модели из входящих параметров, переданных из контроллера
	 * @param array $data
	 * @throws Exception
	 */
	function setParams($data)
	{
		parent::setParams($data);

		// @task https://redmine.swan.perm.ru/issues/78021
		$this->_params['ignoreKsgInMorbusCheck'] = empty($data['ignoreKsgInMorbusCheck']) ? false : true;
		// @task https://redmine.swan.perm.ru/issues/78034
		$this->_params['ignoreUetSumInNonMorbusCheck'] = empty($data['ignoreUetSumInNonMorbusCheck']) ? false : true;
		$this->_params['MedicalStatus_id'] = empty($data['MedicalStatus_id']) ? false : true;
	}

	/**
	 * Проверяем возможность поменять параметр "Случай закончен"
	 * и реализуем логику перед его записью
	 * @throws Exception
	 */
	protected function _checkChangeIsFinish()
	{
		parent::_checkChangeIsFinish();

		if (
			in_array($this->regionNick, array('perm','vologda'))
			&& in_array($this->scenario, array(self::SCENARIO_SET_ATTRIBUTE, self::SCENARIO_DO_SAVE))
			&& 2 == $this->IsFinish
		) {
			// @task https://redmine.swan.perm.ru/issues/78021
			if ( $this->_params['ignoreKsgInMorbusCheck'] === false ) {
				$query = "
					select
						count(case when Mes_id is null then EvnDiagPLStom_id else null end) as mesIsEmpty,
						count(case when Mes_id is not null then EvnDiagPLStom_id else null end) as mesIsNotEmpty
					from v_EvnDiagPLStom with (nolock)
					where EvnDiagPLStom_rid = :EvnDiagPLStom_rid
				";
				$resp = $this->queryResult($query, array(
					'EvnDiagPLStom_rid' => $this->id
				));

				if ( !is_array($resp) || count($resp) == 0 ) {
					throw new Exception('Ошибка при проверке заполнения поля КСГ в заболеваниях.');
				}
				else if ( $this->_params['ignoreKsgInMorbusCheck'] === false && $resp[0]['mesIsEmpty'] > 0 && $resp[0]['mesIsNotEmpty'] > 0 ) {
					$this->_saveResponse['ignoreParam'] = 'ignoreKsgInMorbusCheck';
					$this->_setAlertMsg('Согласно требованиям ТФОМС, стоматологические случаи должны содержать либо все заболевания с КСГ, либо все заболевания без КСГ. При невыполнении данного условия в оплате по ОМС будет отказано. Проверьте данные в случае. Продолжить сохранение?');
					throw new Exception('YesNo', '119');
				}
			}

			// @task https://redmine.swan.perm.ru/issues/78034
			if ( $this->_params['ignoreUetSumInNonMorbusCheck'] === false ) {
				$query = "
					with MorbusList (
						EvnDiagPLStom_id,
						EvnDiagPLStom_setDate,
						Person_Age,
						Diag_id,
						Diag_Code
					) as (
						select
							EDPLS.EvnDiagPLStom_id,
							cast(EDPLS.EvnDiagPLStom_setDT as date) as EvnDiagPLStom_setDate,
							dbo.Age2(PS.Person_BirthDay, cast(EDPLS.EvnDiagPLStom_setDT as date)) as Person_Age,
							D.Diag_id,
							D.Diag_Code
						from v_EvnDiagPLStom EDPLS with (nolock)
							inner join v_Diag D with (nolock) on D.Diag_id = EDPLS.Diag_id
							inner join v_PersonState PS with (nolock) on PS.Person_id = EDPLS.Person_id
						where EDPLS.EvnDiagPLStom_rid = :EvnDiagPLStom_rid
							and EDPLS.Mes_id is null
					),
					MesList (
						Diag_id,
						Mes_KoikoDni
					) as (
						select
							mo.Diag_id,
							mo.Mes_KoikoDni
						from v_MesOld mo with (nolock)
							left join MorbusList on MorbusList.Diag_id = mo.Diag_id
						where MorbusList.Diag_id is not null
							and (
								mo.MesAgeGroup_id is null
								or (mo.MesAgeGroup_id = 1 and MorbusList.Person_Age >= 18)
								or (mo.MesAgeGroup_id = 2 and MorbusList.Person_Age < 18)
							)
							and mo.MesType_id = 7
							and mo.Lpu_id is null
							and mo.Mes_begDT <= MorbusList.EvnDiagPLStom_setDate
							and (mo.Mes_endDT is null or mo.Mes_endDT >= MorbusList.EvnDiagPLStom_setDate)
					),
					UslugaList (
						EvnDiagPLStom_id,
						EvnUslugaStom_UED
					) as (
						select
							eus.EvnDiagPLStom_id,
							SUM(eus.EvnUslugaStom_UED) as EvnUslugaStom_UED
						from v_EvnUslugaStom eus with (nolock)
							inner join v_PayType pt with (nolock) on pt.PayType_id = eus.PayType_id
								and pt.PayType_SysNick = 'oms'
						where eus.EvnDiagPLStom_id in (select EvnDiagPLStom_id from MorbusList with(nolock))
							and ISNULL(eus.EvnUslugaStom_IsAllMorbus, 1) = 1
						group by eus.EvnDiagPLStom_id
					)

					select
						MorbusList.Diag_Code,
						MesList.Diag_id,
						ISNULL(MesList.Mes_KoikoDni, 0) as Mes_KoikoDni,
						ISNULL(UslugaList.EvnUslugaStom_UED, 0) as EvnUslugaStom_UED
					from MorbusList with(nolock)
						left join MesList on MesList.Diag_id = MorbusList.Diag_id
						left join UslugaList on UslugaList.EvnDiagPLStom_id = MorbusList.EvnDiagPLStom_id
				";
				$resp = $this->queryResult($query, array(
					'EvnDiagPLStom_rid' => $this->id
				));

				if ( is_array($resp) && count($resp) > 0 ) {
					$diagList = array();

					foreach ( $resp as $row ) {
						if ( !empty($row['Diag_id']) && $row['EvnUslugaStom_UED'] > $row['Mes_KoikoDni'] && !in_array($row['Diag_Code'], $diagList) ) {
							$diagList[] = $row['Diag_Code'];
						}
					}

					if ( count($diagList) > 0 ) {
						$this->_saveResponse['ignoreParam'] = 'ignoreUetSumInNonMorbusCheck';
						$this->_setAlertMsg('Для заболевани' . (count($diagList) == 1 ? 'я' : 'й') . ' <b>' . implode(', ', $diagList) . '</b> суммарное количество УЕТ превышает максимальное значение УЕТ по любой КСГ с диагнозом заболевания. Продолжить сохранение?');
						throw new Exception('YesNo', '129');
					}
				}
			}

		}
	}

	/**
	 * @param $data
	 * @return bool
	 */
	function loadEmkEvnPLStomEditForm($data) {
		$filter = "";
		$fields = "";
		$joinQuery = "";
		$queryParams = array(
			'Lpu_id' => $data['Lpu_id']
		);

		$selectEvnDirectionData = "
			EPL.PrehospDirect_id,
			EPL.EvnDirection_Num,
			convert(varchar(10), EPL.EvnDirection_setDT, 104) as EvnDirection_setDate, case when 1 = isnull(ED.EvnDirection_IsAuto,1) then coalesce(EPL.Org_did, LPUDID.Org_id, ED.Org_sid) else isnull(EPL.Org_did, LPUDID.Org_id) end as Org_did,
			EPL.Lpu_did,
			EPL.LpuSection_did,
			EPL.MedStaffFact_did,
			EPL.Diag_did,
			EPL.Diag_preid,
			EPL.EvnDirection_id,
			EVPL.EvnDirection_id as EvnDirection_vid,
			isnull(ED.EvnDirection_IsAuto,1) as EvnDirection_IsAuto,
			isnull(ED.EvnDirection_IsReceive,1) as EvnDirection_IsReceive,
			isnull(ED.Lpu_sid,ED.Lpu_id) as Lpu_fid,
		";

		$this->load->model('EvnVizitPLStom_model');
		if ( !empty($data['EvnVizitPLStom_id']) ) {
			$joinQuery .= "
				inner join v_EvnVizitPLStom EVPL with (nolock) on EVPL.EvnVizitPLStom_pid = EPL.EvnPLStom_id and EVPL.EvnVizitPLStom_id = :EvnVizitPLStom_id
				inner join v_LpuSection LS with (nolock) on LS.LpuSection_id = EVPL.LpuSection_id
				inner join v_LpuUnit LU with (nolock) on LU.LpuUnit_id = LS.LpuUnit_id
				left join v_EvnDirection_all ED with (nolock) on ED.EvnDirection_id = EPL.EvnDirection_id
			";

			$fields .= 'BPD.BitePersonType_id,';
			$joinQuery .= 'outer apply(
					select top 1 BitePersonType_id 
					from v_BitePersonData with (nolock)
					where EvnVizitPLStom_id = :EvnVizitPLStom_id and BitePersonType_id is not null and BitePersonData_disDate is null
					order by BitePersonData_updDT DESC
				) BPD';

			if ( $this->EvnVizitPLStom_model->isUseVizitCode ) {
				$joinQuery .= "
					outer apply (
						select top 1
							t1.EvnUslugaCommon_id,
							t1.UslugaComplex_id as UslugaComplex_uid
						from
							v_EvnUslugaCommon t1 with (nolock)
						where
							t1.EvnUslugaCommon_pid = :EvnVizitPLStom_id
							and ISNULL(t1.EvnUslugaCommon_IsVizitCode, 1) = 2
						order by
							t1.EvnUslugaCommon_setDT desc
					) EU
					left join v_UslugaComplex UC with (nolock) on UC.UslugaComplex_id = ISNULL(EU.UslugaComplex_uid, EVPL.UslugaComplex_id)
				";
			}

			$queryParams['EvnVizitPLStom_id'] = $data['EvnVizitPLStom_id'];
		}
		else if ( !empty($data['EvnPLStom_id']) ) {
			$orderBy = (!empty($data['loadLast']) && $data['loadLast'] == 1) ? 'EvnVizitPLStom_setDT desc' : 'EvnVizitPLStom_setDT asc';
			$joinQuery .= "
				outer apply (
					select top 1
						DeseaseType_id,
						Diag_agid,
						Diag_id,
						EvnVizitPLStom_id,
						EvnDirection_id,
						EvnVizitPLStom_Index,
						HealthKind_id,
						LpuSection_id,
						MedPersonal_id,
						MedStaffFact_id,
						MedPersonal_sid,
						PayType_id,
						ProfGoal_id,
						TreatmentClass_id,
						ServiceType_id,
						VizitClass_id,
						VizitType_id,
						EvnVizitPLStom_setDT,
						TimetableGraf_id,
						EvnPrescr_id,
						EvnVizitPLStom_setTime,
						EvnVizitPLStom_Time,
						LpuSectionProfile_id,
						Mes_id,
						UslugaComplex_id,
						ROUND(EvnVizitPLStom_Uet, 2) as EvnVizitPLStom_Uet,
						ROUND(EvnVizitPLStom_UetOMS, 2) as EvnVizitPLStom_UetOMS,
						RiskLevel_id,
						EvnVizitPLStom_Count,
						EvnVizitPLStom_IsSigned,
						DispClass_id,
						EvnPLDisp_id,
						PersonDisp_id,
						RankinScale_id,
						DispProfGoalType_id,
						EvnVizitPLStom_IsPaid,
						MedicalCareKind_id
					from
						v_EvnVizitPLStom with (nolock)
					where
						EvnVizitPLStom_pid = EPL.EvnPLStom_id
					order by
						{$orderBy}
				) EVPL
				left join v_EvnDirection_all ED with (nolock) on ED.EvnDirection_id = EPL.EvnDirection_id
				left join v_LpuSection LS with (nolock) on LS.LpuSection_id = EVPL.LpuSection_id
				left join v_LpuUnit LU with (nolock) on LU.LpuUnit_id = LS.LpuUnit_id
			";

			if ( $this->EvnVizitPLStom_model->isUseVizitCode ) {
				$joinQuery .= "
					outer apply (
						select top 1
							t1.EvnUslugaCommon_id,
							t1.UslugaComplex_id as UslugaComplex_uid
						from
							v_EvnUslugaCommon t1 with (nolock)
						where
							t1.EvnUslugaCommon_pid = EVPL.EvnVizitPLStom_id
							and ISNULL(t1.EvnUslugaCommon_IsVizitCode, 1) = 2
						order by
							t1.EvnUslugaCommon_setDT desc
					) EU
					left join v_UslugaComplex UC with (nolock) on UC.UslugaComplex_id = ISNULL(EU.UslugaComplex_uid, EVPL.UslugaComplex_id)
				";
			}

			$filter .= "and EPL.EvnPLStom_id = :EvnPLStom_id";
			$queryParams['EvnPLStom_id'] = $data['EvnPLStom_id'];
		}
		else {
			return array();
		}

		$diagLid = "isnull(EPL.Diag_lid,LASTEVPL.Diag_id) as Diag_lid,";
		if (getRegionNick() == 'ufa') {
			$diagLid = "EPL.Diag_lid as Diag_lid,";
		}

		// Здесь тоже надо поменять условие для accessType
		// https://redmine.swan.perm.ru/issues/28433
		$query = "
			select top 1
				case when EPL.Lpu_id = :Lpu_id then 'edit' else 'view' end as accessType,
				EPL.DirectClass_id,
				EPL.DirectType_id,
				{$selectEvnDirectionData}
				EPL.EvnPLStom_id,
				EPL.EvnPLStom_IsFinish,
				EPL.EvnPLStom_IsUnlaw,
				EPL.EvnPLStom_IsUnport,
				RTRIM(ISNULL(EPL.EvnPLStom_NumCard, '')) as EvnPLStom_NumCard,
				case when EPL.EvnPLStom_IsCons = 2 then 1 else 0 end as EvnPLStom_IsCons,
				ROUND(EPL.EvnPLStom_UKL, 3) as EvnPLStom_UKL,
				EPL.Lpu_oid,
				EPL.LpuSection_oid,
				EPL.Person_id,
				EPL.PersonEvn_id,
				EPL.PrehospTrauma_id,
				EPL.ResultClass_id,
				EPL.ResultDeseaseType_id,
				EPL.LeaveType_fedid,
				EPL.ResultDeseaseType_fedid,
				EPL.Server_id,
				EPL.MedicalCareKind_id,
				-- Данные по посещению
				EVPL.DeseaseType_id,
				EVPL.Diag_agid,
				EVPL.Diag_id,
				ISNULL(EVPL.EvnVizitPLStom_id, 0) as EvnVizitPLStom_id,
				ISNULL(EVPL.EvnVizitPLStom_Index, 0) as EvnVizitPLStom_Index,
				LU.LpuBuilding_id,
				LU.LpuUnit_id,
				LU.LpuUnitSet_id,
				EVPL.LpuSection_id,
				EVPL.MedPersonal_id,
				EVPL.MedStaffFact_id,
				EVPL.MedPersonal_sid,
				EVPL.PayType_id,
				EVPL.MedicalCareKind_id as MedicalCareKind_vid,
				EVPL.HealthKind_id,
				EVPL.RiskLevel_id,
				EVPL.ProfGoal_id,
				EVPL.TreatmentClass_id,
				EVPL.ServiceType_id,
				EVPL.VizitClass_id,
				EVPL.VizitType_id,
				convert(varchar(10), EVPL.EvnVizitPLStom_setDT, 104) as EvnVizitPLStom_setDate,
				EVPL.TimetableGraf_id,
				EVPL.EvnPrescr_id,
				EVPL.EvnVizitPLStom_setTime,
				EVPL.EvnVizitPLStom_Time,
				isnull(EVPL.LpuSectionProfile_id,LS.LpuSectionProfile_id) as LpuSectionProfile_id,
				EVPL.Mes_id,
				{$fields}
				EVPL.EvnVizitPLStom_Uet,
				EPL.Diag_concid,
				EPL.Diag_fid as Diag_fid,
				{$diagLid}
				EPL.InterruptLeaveType_id,
				EVPL.EvnVizitPLStom_UetOMS,
				EVPL.EvnVizitPLStom_IsSigned,
				EVPL.DispClass_id,
				EVPL.EvnPLDisp_id,
				EVPL.PersonDisp_id,
				EVPL.RankinScale_id,
				EVPL.DispProfGoalType_id,
				EvnXml.EvnXml_id,
				EVPL.EvnVizitPLStom_IsPaid,
				convert(varchar(10), LASTEVPL.EvnVizitPLStom_setDT, 104) as LastEvnVizitPLStom_setDate,
				-- Услуга
				" . ($this->EvnVizitPLStom_model->isUseVizitCode ? "EU.EvnUslugaCommon_id, ISNULL(EU.UslugaComplex_uid, EVPL.UslugaComplex_id) as UslugaComplex_uid, UC.UslugaComplex_Code, " : "NULL as EvnUslugaCommon_id, NULL as UslugaComplex_uid, NULL as UslugaComplex_Code,") . "
				EVPL.EvnVizitPLStom_Count
			FROM
				v_EvnPLStom EPL with (nolock)
				left join v_Lpu LPUDID with (nolock) on EPL.Lpu_did = LPUDID.Lpu_id
				" . $joinQuery . "
				outer apply (
					select top 1
						EvnVizitPLStom_setDT,
						Diag_id
					from
						v_EvnVizitPLStom (nolock)
					where
						EvnVizitPLStom_pid = EPL.EvnPLStom_id
						--and EvnVizitPLStom_id != EVPL.EvnVizitPLStom_id
					order by
						EvnVizitPLStom_setDT desc
				) LASTEVPL
				outer apply (
					select top 1 v_EvnXml.EvnXml_id
					from v_EvnVizitPLStom with (nolock)
						inner join v_EvnXml  with (nolock) on v_EvnXml.Evn_id = v_EvnVizitPLStom.EvnVizitPLStom_id and v_EvnXml.XmlType_id = 3
					where v_EvnVizitPLStom.EvnVizitPLStom_pid = EPL.EvnPLStom_id
					order by
						/* надо последнее посещение и последний осмотр */
						v_EvnVizitPLStom.EvnVizitPLStom_setDT desc, v_EvnXml.EvnXml_insDT desc
				) EvnXml
			WHERE (1 = 1)
				" . $filter . "
				and (EPL.Lpu_id = :Lpu_id or " . (!empty($data['session']['medpersonal_id']) ? 1 : 0) . " = 1)
		";

		//echo getDebugSQL($query, $queryParams); exit();

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Получение даты ТАП (используется для печати ТАП)
	 */
	function getEvnPLDate($data) {
		$EvnPL_Date = '01.01.2015'; // по умолчанию до 2016 года.

		// для стоматки смотреть по дате начала случая, для всех регионов
		$resp = $this->queryResult("
			select top 1
				convert(varchar(10), EvnVizitPL_setDate, 104) as EvnPL_Date
			from
				v_EvnVizitPL (nolock)
			where
				EvnVizitPL_pid = :EvnPL_id
			order by
				EvnVizitPL_setDate asc
		", array(
			'EvnPL_id' => $data['EvnPL_id']
		));

		if (!empty($resp[0]['EvnPL_Date'])) {
			$EvnPL_Date = $resp[0]['EvnPL_Date'];
		}

		return array('EvnPL_Date' => $EvnPL_Date, 'Error_Msg' => '');
	}

	/**
	 * Получение данных по заболеваниям в посещении
	 */
	function getEvnDiagPLStom($data) {
		$params = array();
		if(empty($data['EvnPLStom_id']) || empty($data['EvnVizitPLStom_id']) || empty($data['Lpu_id'])) {
			return false;
		}
		
		$query = "
			select distinct
				EDPLS.EvnDiagPLStom_id as EvnDiagPLStom_id,
				EDPLS.EvnDiagPLStom_pid as EvnDiagPLStom_pid,
				RTRIM(DT.DeseaseType_Name) as DeseaseType_Name,
				convert(varchar(10), EDPLS.EvnDiagPLStom_setDate, 104) as EvnDiagPLStom_setDate,
				case
					when EDPLS.EvnDiagPLStom_IsClosed = 2 then convert(varchar(10), EDPLS.EvnDiagPLStom_disDate, 104)
					else null
				end as EvnDiagPLStom_disDate,
				EDPLS.Diag_id,
				RTrim(Diag.Diag_Code) as Diag_Code,
				RTrim(Diag.Diag_Name) as Diag_Name,
				rtrim(isnull(RTrim(Diag.Diag_Code),'') +' '+ isnull(RTrim(Diag.Diag_Name),'')) as diag,
				tooth.Tooth_Code,
				case when isnull(EVPLS.EvnVizitPLStom_id,0) = :EvnVizitPLStom_id then 1 else 0 end as inThisVizit,
				vizit.Diag_id as vizDiag
			from v_EvnDiagPLStom EDPLS with (nolock)
				left join v_EvnVizitPLStom EVPLS with (nolock) on EVPLS.EvnVizitPLStom_id = EDPLS.EvnDiagPLStom_pid
				left join v_Tooth tooth with (nolock) on tooth.Tooth_id = EDPLS.Tooth_id
				left join Diag with (nolock) on Diag.Diag_id = EDPLS.Diag_id
				left join DeseaseType DT with (nolock) on DT.DeseaseType_id = EDPLS.DeseaseType_id
				outer apply (
					select top 1 EVPS.Diag_id 
					from v_EvnPLStom viz with (nolock) 
					left join v_EvnVizitPLStom EVPS with (nolock) on EVPS.EvnVizitPLStom_pid = viz.EvnPLStom_id
					where viz.EvnPLStom_id = :EvnPLStom_id
					order by EvnVizitPLStom_setDT desc
				) vizit
				left join v_EvnUslugaStom EU with (nolock) on EU.EvnDiagPLStom_id = EDPLS.EvnDiagPLStom_id
			where 
				EDPLS.Lpu_id = :Lpu_id
				and (EDPLS.EvnDiagPLStom_pid = :EvnVizitPLStom_id or EU.EvnUslugaStom_pid = :EvnVizitPLStom_id) 
		";
		$params['Lpu_id'] = $data['Lpu_id'];
		$params['EvnPLStom_id'] = $data['EvnPLStom_id'];
		$params['EvnVizitPLStom_id'] = $data['EvnVizitPLStom_id'];
		$result = $this->db->query($query, $params);

		if ( is_object($result) ) {
			$result = $result->result('array');
			if(count($result) > 1){
				$res = array();
				foreach ($result as $value) {
					if($value['Diag_id'] == $value['vizDiag']){
						array_push($res, $value);
					}
				}
				if(count($res) == 0){
					return $result;
				}
				return $res;
			} else if (count($result) == 1) {
				return $result;
			} else {
				return array(array('Error_Msg'=>'В случае не указано заболеваний. Копирование невозможно'));
			}
		}
		else {
			return false;
		}
	}

	/**
	 * Получение данных ТАП. Метод для API
	 */
	function getEvnPLStomForAPI($data) {
		$params = array(
			'EvnPLStom_id' => $data['EvnPLStom_id'],
			'Lpu_id' => $data['Lpu_id']
		);

		$query = "
			select top 1
				EPLS.Person_id,
				EPLS.Lpu_id,
				convert(varchar(10), EPLS.EvnPLStom_setDT, 120) as Date,
				EPLS.EvnPLStom_NumCard as NumCard,
				EPLS.EvnPLStom_IsFinish as IsFinish,
				EPLS.ResultClass_id,
				EPLS.ResultDeseaseType_id,
				EPLS.EvnPLStom_UKL,
				EPLS.Diag_lid
			from
				v_EvnPLStom EPLS with (nolock)
			where
				EPLS.EvnPLStom_id = :EvnPLStom_id
				and EPLS.Lpu_id = :Lpu_id
		";

		$resp = $this->queryResult($query, $params);

		return $resp;
	}

	/**
	 * Получение списка посещений. Метод для API
	 */
	function getEvnVizitPLStomListForAPI($data) {
		$params = array(
			'EvnPLStom_id' => $data['EvnPLStom_id'],
			'Lpu_id' => $data['Lpu_id']
		);

		$query = "
			select
				EVPLS.EvnVizitPLStom_id
			from
				v_EvnVizitPLStom EVPLS with (nolock)
			where
				EVPLS.EvnVizitPLStom_rid = :EvnPLStom_id
				and EVPLS.Lpu_id = :Lpu_id
		";

		$resp = $this->queryResult($query, $params);

		return $resp;
	}

	/**
	 * Получение данных заболевания. Метод для API
	 */
	function getEvnDiagPLStomForAPI($data) {
		$params = array(
			'EvnDiagPLStom_id' => $data['EvnDiagPLStom_id'],
			'Lpu_id' => $data['Lpu_id']
		);

		$query = "
			select top 1
				EDPLS.Diag_id,
				EDPLS.DeseaseType_id,
				EDPLS.EvnDiagPLStom_IsClosed
			from
				v_EvnDiagPLStom EDPLS with (nolock)
			where
				EDPLS.EvnDiagPLStom_id = :EvnDiagPLStom_id
				and EDPLS.Lpu_id = :Lpu_id
		";

		$resp = $this->queryResult($query, $params);

		return $resp;
	}

	/**
	 * Получение данных стомат. услуги. Метод для API
	 */
	function getEvnUslugaStomForAPI($data) {
		$params = array(
			'EvnUslugaStom_id' => $data['EvnUslugaStom_id'],
			'Lpu_id' => $data['Lpu_id']
		);

		$query = "
			select top 1
				EVPLS.EvnVizitPLStom_id,
				EUS.EvnDiagPLStom_id,
				EUS.MedStaffFact_id,
				EUS.PayType_id,
				UC.UslugaCategory_id,
				EUS.UslugaComplex_id
			from
				v_EvnUslugaStom EUS with (nolock)
				left join v_EvnVizitPLStom EVPLS with (nolock) on EVPLS.EvnVizitPLStom_id = EUS.EvnUslugaStom_pid
				left join v_UslugaComplex uc with (nolock) on uc.UslugaComplex_id = EUS.UslugaComplex_id
			where
				EUS.EvnUslugaStom_id = :EvnUslugaStom_id
				and EUS.Lpu_id = :Lpu_id
		";

		$resp = $this->queryResult($query, $params);

		return $resp;
	}

	/**
	 * Получение списка заболеваний. Метод для API
	 */
	function getEvnDiagPLStomListForAPI($data) {
		$params = array(
			'EvnPLStom_id' => $data['EvnPLStom_id'],
			'Lpu_id' => $data['Lpu_id']
		);

		$query = "
			select
				EDPLS.EvnDiagPLStom_id
			from
				v_EvnDiagPLStom EDPLS with (nolock)
			where
				EDPLS.EvnDiagPLStom_rid = :EvnPLStom_id
				and EDPLS.Lpu_id = :Lpu_id
		";

		$resp = $this->queryResult($query, $params);

		return $resp;
	}

	/**
	 * Получение списка сопутствующих диагнозов. Метод для API
	 */
	function getEvnDiagPLStomSopListForAPI($data) {
		$params = array(
			'EvnDiagPLStom_id' => $data['EvnDiagPLStom_id'],
			'Lpu_id' => $data['Lpu_id']
		);

		$query = "
			select
				EDPLSS.EvnDiagPLStomSop_id as DiagPLStomSop_id
			from
				v_EvnDiagPLStomSop EDPLSS with (nolock)
			where
				EDPLSS.EvnDiagPLStomSop_pid = :EvnDiagPLStom_id
				and EDPLSS.Lpu_id = :Lpu_id 
		";

		$resp = $this->queryResult($query, $params);

		return $resp;
	}

	/**
	 * Получение списка услуг. Метод для API
	 */
	function getEvnUslugaStomListForAPI($data) {
		$params = array(
			'Lpu_id' => $data['Lpu_id']
		);
		$filter = "Lpu_id = :Lpu_id";
		if (!empty($data['EvnDiagPLStom_id'])) {
			$params['EvnDiagPLStom_id'] = $data['EvnDiagPLStom_id'];
			$filter .= " and EUS.EvnDiagPLStom_id = :EvnDiagPLStom_id";
		} else if (!empty($data['EvnVizitPLStom_id'])) {
			$params['EvnVizitPLStom_id'] = $data['EvnVizitPLStom_id'];
			$filter .= " and EUS.EvnUslugaStom_pid = :EvnVizitPLStom_id";
		} else {
			return array();
		}

		$query = "
			select
				EUS.EvnUslugaStom_id
			from
				v_EvnUslugaStom EUS with (nolock)
			where
				{$filter}
		";

		$resp = $this->queryResult($query, $params);

		return $resp;
	}

	/**
	 * Получение данных посещения. Метод для API
	 */
	function getEvnVizitPLStomForAPI($data) {
		$params = array(
			'EvnVizitPLStom_id' => $data['EvnVizitPLStom_id'],
			'Lpu_id' => $data['Lpu_id']
		);

		$query = "
			select top 1
				convert(varchar(10), EVPLS.EvnVizitPLStom_setDT, 120) as EvnVizitPLStom_setDate,
				EVPLS.LpuSection_id,
				EVPLS.MedStaffFact_id,
				EVPLS.TreatmentClass_id,
				EVPLS.ServiceType_id,
				EVPLS.VizitType_id,
				EVPLS.PayType_id,
				EVPLS.Mes_id as MesEkb_id,
				EVPLS.UslugaComplex_id as UslugaComplex_uid,
				EVPLS.MedicalCareKind_id
			from
				v_EvnVizitPLStom EVPLS with (nolock)
			where
				EVPLS.EvnVizitPLStom_id = :EvnVizitPLStom_id
				and EVPLS.Lpu_id = :Lpu_id
		";

		$resp = $this->queryResult($query, $params);

		return $resp;
	}

	/**
	 * Получение данных сопутствующего диагноза. Метод для API
	 */
	function getEvnDiagPLStomSopForAPI($data) {
		$params = array(
			'EvnDiagPLStomSop_id' => $data['DiagPLStomSop_id'],
			'Lpu_id' => $data['Lpu_id']
		);

		$query = "
			select top 1
				EDPLSS.Diag_id,
				EDPLSS.DeseaseType_id
			from
				v_EvnDiagPLStomSop EDPLSS with (nolock)
			where
				EDPLSS.EvnDiagPLStomSop_id = :EvnDiagPLStomSop_id
				and EDPLSS.Lpu_id = :Lpu_id
		";

		$resp = $this->queryResult($query, $params);

		return $resp;
	}

	/**
	 * Редактирование стомат. ТАП из АПИ
	 */
	function editEvnPLStomFromAPI($data) {
		// получаем данные ТАП
		$this->applyData(array(
			'EvnPLStom_id' => !empty($data['EvnPLStom_id'])?$data['EvnPLStom_id']:null,
			'session' => $data['session']
		));

		// подменяем параметры, пришедшие от клиента
		$this->setAttribute('isfinish', !empty($data['IsFinish'])?$data['IsFinish']:1);
		if (!empty($data['EvnVizitPLStom_setDate'])) {
			$this->setAttribute('setdt', $data['EvnVizitPLStom_setDate']);
		}
		if (!empty($data['NumCard'])) {
			$this->setAttribute('numcard', $data['NumCard']);
		}
		if (!empty($data['Lpu_id'])) {
			$this->setAttribute('lpu_id', $data['Lpu_id']);
		}
		if (!empty($data['ResultClass_id'])) {
			$this->setAttribute('resultclass_id', $data['ResultClass_id']);
		}
		if (!empty($data['ResultDeseaseType_id'])) {
			$this->setAttribute('resultdeseasetype_id', $data['ResultDeseaseType_id']);
		}
		if (!empty($data['Diag_lid'])) {
			$this->setAttribute('diag_lid', $data['Diag_lid']);
		}
		if (!empty($data['EvnPLStom_UKL'])) {
			$this->setAttribute('ukl', $data['EvnPLStom_UKL']);
		}
		if (!empty($data['Person_id'])) {
			// данные по пациенту берем из PersonState
			$resp = $this->queryResult("
				select
					Person_id,
					PersonEvn_id,
					Server_id
				from
					v_PersonState (nolock)
				where
					Person_id = :Person_id
			", array(
				'Person_id' => $data['Person_id']
			));

			if (!empty($resp[0]['Person_id'])) {
				$this->setAttribute('person_id', $resp[0]['Person_id']);
				$this->setAttribute('personevn_id', $resp[0]['PersonEvn_id']);
				$this->setAttribute('server_id', $resp[0]['Server_id']);
			}
		}

		// проверяем на дубли
		$this->scenario = self::SCENARIO_DO_SAVE;
		$this->_checkEvnPLDoubles();

		// сохраняем ТАП
		$resp = $this->_save();

		return $resp;
	}

	/**
	 * Загрузка формы завершения случая лечения
	 */
	function loadEvnPLStomFinishForm($data)
	{
		return $this->queryResult("
			select
				epls.EvnPLStom_id,
				epls.EvnPLStom_IsFinish,
				epls.ResultClass_id,
				epls.InterruptLeaveType_id,
				epls.EvnPLStom_UKL,
				epls.DirectType_id,
				epls.DirectClass_id,
				ISNULL(evpl.Diag_id, epls.Diag_lid) as Diag_lid,
				epls.PrehospTrauma_id,
				epls.EvnPLStom_IsUnlaw,
				epls.EvnPLStom_IsUnport,
				epls.LeaveType_fedid,
				epls.ResultDeseaseType_fedid,
				isnull(epls.EvnPLStom_IsSan,1) as EvnPLStom_IsSan,
				epls.SanationStatus_id
			from
				v_EvnPLStom epls (nolock)
				outer apply (
					select top 1
						evpl.Diag_id
					from
						v_EvnVizitPL evpl (nolock)
					where
						evpl.EvnVizitPL_pid = epls.EvnPLStom_id
					order by
						evpl.EvnVizitPL_setDate DESC,
						evpl.EvnVizitPL_setTime DESC
				) evpl
			where
				epls.EvnPLStom_id = :EvnPLStom_id
		", array(
			'EvnPLStom_id' => $data['EvnPLStom_id']
		));
	}
}