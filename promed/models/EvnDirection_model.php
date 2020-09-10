<?php
/**
* EvnDirection_model - модель для работы с направлениями
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Reg
* @access       public
* @copyright    Copyright (c) 2009-2012 Swan Ltd.
 * @property EvnQueue_model EvnQueue_model
 * @property EvnPS_model EvnPS_model
 * 
*/
class EvnDirection_model extends swModel {
	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * Определение имени таблицы с данными объекта
	 * @return string
	 */
	protected function tableName()
	{
		return 'EvnDirection';
	}

	/**
	 * Возвращает правила проверки данных для метода сохранения направления
	 * @return array
	 */
	function getSaveRules($options = array())
	{
		return array(
			array(
				'field' => 'redirectEvnDirection',
				'label' => 'Признак перенаправления',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PrehospDirect_id',
				'label' => 'Кем направлен',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnDirection_IsCito',
				'label' => 'Cito!',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Diag_id',
				'label' => 'Диагноз',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'DirType_id',
				'label' => 'Тип направления',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'MedicalCareFormType_id',
				'label' => 'Форма помощи',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'StudyTarget_id',
				'label' => 'Цель исследования',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'PayType_id',
				'label' => 'Вид оплаты',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Lpu_did',
				'label' => 'МО куда направлен',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Lpu_sid',
				'label' => 'МО кем направлен',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Org_sid',
				'label' => 'Организация кем направлен',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnDirection_IsNeedOper',
				'label' => 'Необходимость операционного вмешательства',
				'rules' => '',
				'type' => 'checkbox'
			),
			array(
				'field' => 'EvnDirection_Descr',
				'label' => 'Обоснование',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'EvnDirection_id',
				'label' => 'Идентификатор направления',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnDirection_pid',
				'label' => 'Идентификатор родительского события',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'DopDispInfoConsent_id',
				'label' => 'Идентификатор согласия',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'MedService_id',
				'label' => 'Идентификатор службы',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'MedService_pzid',
				'label' => 'Пункт забора',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'TimetableMedService_id',
				'label' => 'Идентификатор бирки службы',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Resource_id',
				'label' => 'Идентификатор ресурса',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'TimetableResource_id',
				'label' => 'Идентификатор бирки службы',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'TreatmentType_id',
				'label' => 'Тип предстоящего лечения',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'RemoteConsultCause_id',
				'label' => 'Цель консультирования ',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'EvnDirection_IsAuto',
				'label' => 'Признак является ли направление автоматическим Да/нет ',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnDirection_IsReceive',
				'label' => 'Признак создано ли направление принимающей стороной (к себе) Да/нет ',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PrescriptionType_Code',
				'label' => 'Идентификатор ',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnPrescr_id',
				'label' => 'Идентификатор ',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnPrescrVK_id',
				'label' => 'Идентификатор направления на ВК',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnDirection_Num',
				'label' => 'Номер направления',
				'rules' => 'trim|required',
				'type' => 'string'
			),
			array(
				'field' => 'EvnDirection_setDate',
				'label' => 'Дата выписки направления',
				'rules' => 'trim|required',
				'type' => 'date'
			),
			array(
				'field' => 'EvnDirection_desDT',
				'label' => 'Желаемая дата',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'EvnDirection_setDateTime',
				'label' => 'Время',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'LpuSection_id',
				'label' => 'Отделение, которое направило',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'LpuSection_did',
				'label' => 'Отделение куда направили',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'LpuSectionProfile_id',
				'label' => 'Профиль',
				'rules' => empty($options['lpuSectionProfileNotRequired'])?'required':'',
				'type' => 'id'
			),
			array(
				// кто направил, для записи должности врача
				'field' => 'From_MedStaffFact_id',
				'label' => 'Рабочее место врача',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'MedPersonal_id',
				'label' => 'Врач',
				'rules' => '', // при записи из арм call-центра нет врача, а сохранение направления вызывается, убрал обязательность
				'type' => 'id'
			),
			array(
				'field' => 'MedPersonal_did',
				'label' => 'Врач кому направили',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'MedPersonal_zid',
				'label' => 'Зав. отделением',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'TimetableGraf_id',
				'label' => 'Идентификатор бирки поликлиники',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'TimetableStac_id',
				'label' => 'Идентификатор бирки стационара',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'TimetablePar_id',
				'label' => 'Идентификатор бирки поликлиники',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор человека',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'PersonEvn_id',
				'label' => 'Идентификатор состояния человека',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Server_id',
				'label' => 'Идентификатор сервера',
				'rules' => 'required',
				'type' => 'int'
			),
			array(
					'field' => 'LpuUnit_did',
					'label' => 'LpuUnit_did',
					'rules' => '',
					'type' => 'id'
			),
			array( 'field' => 'EvnQueue_id','label' => 'Идентификатор записи в очереди', 'rules' => '', 'type' => 'id' ),
			array( 'field' => 'QueueFailCause_id','label' => 'Причина отмены направления из очереди', 'rules' => '', 'type' => 'id' ),
			array(
				'field' => 'UslugaComplex_did',
				'label' => 'Услуга',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'MedSpec_fid',
				'label' => 'Специальность',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'UslugaComplex_id',
				'label' => 'Услуга',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnUsluga_id',
				'label' => 'Сохраненный заказ',
				'rules' => '',
				'type' => 'id'
			),
			array(
					'field' => 'order',
					'label' => 'Заказ услуги',
					'rules' => '',
					'type' => 'string'
			),
			array(
					'field' => 'EvnXml_id',
					'label' => 'Эпикриз',
					'rules' => '',
					'type' => 'id'
			),
			array(
					'field' => 'EvnDirectionOper_IsAgree',
					'label' => 'Согласие на операцию',
					'rules' => '',
					'type' => 'id'
			),
			array(
				'field' => 'GetBed_id',
				'label' => 'Профиль койки',
				'rules' => '',
				'type'  => 'id'
			),
			array('field' => 'IgnoreCheckHospitalOffice', 'label' => 'Игнорировать проверку', 'rules' => '', 'type'  => 'id'),
			array('field' => 'EvnLinkAPP_StageRecovery', 'label' => 'Тип оплаты', 'rules' => '', 'type'  => 'id'),
			array('field' => 'PurposeHospital_id', 'label' => 'Тип оплаты', 'rules' => '', 'type'  => 'id'),
			array('field' => 'ReasonHospital_id', 'label' => 'Тип оплаты', 'rules' => '', 'type'  => 'id'),
			array('field' => 'Diag_cid', 'label' => 'Тип оплаты', 'rules' => '', 'type'  => 'id'),
			array('field' => 'PayTypeKAZ_id', 'label' => 'Тип оплаты', 'rules' => '', 'type'  => 'id'),
			array('field' => 'ScreenType_id', 'label' => 'Вид скрининга', 'rules' => '', 'type'  => 'id'),
			array('field' => 'TreatmentClass_id', 'label' => 'Повод обращения', 'rules' => '', 'type'  => 'id')
			
		);
	}

	/**
	 * Сохранение вида оплаты направлений
	 */
	function setPayType($data) {
		// меняем только часть полей, остальное начитываем.
		$this->load->model('EvnDirectionAll_model');
		$this->EvnDirectionAll_model->setAttributes(array('EvnDirection_id' => $data['EvnDirection_id']));
		$this->EvnDirectionAll_model->setAttribute('paytype_id', $data['PayType_id']);
		$this->EvnDirectionAll_model->_save();

		// обновить вид оплаты в заявке и услугах
		$query = "
			update EvnUsluga with (rowlock) set PayType_id = :PayType_id where EvnDirection_id = :EvnDirection_id;
			update EvnLabRequest with (rowlock) set PayType_id = :PayType_id where EvnDirection_id = :EvnDirection_id;

			update
				efr with (rowlock)
			set
				PayType_id = :PayType_id
			from
				EvnFuncRequest efr
				inner join v_Evn e (nolock) on e.Evn_id = efr.EvnFuncRequest_id
			where
				Evn_pid = :EvnDirection_id
		";

		$this->db->query($query, array(
			'PayType_id' => $data['PayType_id'],
			'EvnDirection_id' => $data['EvnDirection_id']
		));

		return array('Error_Msg' => '');
	}

	/**
	 *  Запрос при приеме пациента из очереди в АРМе приемного отделения
	 */
	function saveEvnDirectionAuto($data)
	{
		// Сначала получаем данные из очереди
		$query = "
			select
				ED.EvnQueue_id,
				ED.EvnDirection_id,
				ED.EvnQueue_pid as EvnDirection_pid,
				ED.Lpu_id as Lpu_id,
				ED.PersonEvn_id,
				ED.Server_id,
				convert(varchar,ED.EvnQueue_setDT,121) as EvnDirection_setDT,
				convert(varchar(10),ED.EvnQueue_setDT,104) as EvnDirection_setDate,
				ED.Diag_id,
				ED.DirType_id,
				ED.Direction_Num as EvnDirection_Num,
				ED.EvnDirection_Descr,
				ED.LpuSection_did,
				ED.MedPersonal_id,
				ED.MedPersonal_zid,
				ED.LpuSectionProfile_did as LpuSectionProfile_id,
				ED.TimetableGraf_id,
				ED.TimetablePar_id,
				ED.TimetableStac_id,
				ED.TimetableResource_id,
				LU.Lpu_id as Lpu_did,
				L.Org_id as Org_did,
				ED.Resource_did
			from v_EvnQueue ED with (NOLOCK)
				left join v_LpuUnit LU with (NOLOCK) on ED.LpuUnit_did = LU.LpuUnit_id
				left join v_Lpu L (nolock) on l.Lpu_id = LU.Lpu_id
			where
				ED.EvnQueue_id = :EvnQueue_id 
		";
		//echo getDebugSQL($query, $data);exit;
		$result = $this->db->query($query, $data);
		$response = array();
		
		if ( is_object($result) ) {
			$response = $result->result('array'); 
		} else {
			return array(array('Error_Code' => 1,'Error_Msg'=>'Ошибка БД при запросе записи о постановке в очередь'));
		}
		
		if (count($response) > 0)
		{
			$params = $response[0];
			$params['pmUser_id'] = $data['pmUser_id'];
			//$params['DirType_id'] = (empty($params['DirType_id']))?1:$params['DirType_id'];
		} else {
			return array(array('Error_Code' => 3,'Error_Msg'=>'Запись о постановке в очередь не найдена'));
		}
		
		if (!empty($params['EvnDirection_id']))
		{
			// нужно вернуть данные существующего направления
			$query = "
				select
					ED.EvnDirection_id,
					ED.EvnDirection_Num,
					convert(varchar(10), ED.EvnDirection_setDate, 104) as EvnDirection_setDate,
					ED.Diag_id,
					ED.LpuSection_id,--направившее отделение
					ED.DirType_id,
					ED.Lpu_id,--направившее ЛПУ
					L.Org_id,--направившая организация
					ED.LpuSectionProfile_id,
					ED.TimetableGraf_id,
					ED.TimetablePar_id,
					ED.TimetableStac_id,
					ED.TimetableResource_id,
					ED.ConsultationForm_id
				from
					v_EvnDirection_all ED (nolock)
					left join v_Lpu L (nolock) on l.Lpu_id = ed.Lpu_id
				where
					ED.EvnDirection_id = :EvnDirection_id
			";
			$result = $this->db->query($query, $params);
		
			if ( is_object($result) ) {
				$response = $result->result('array');
			}
			else {
				return array(array('Error_Code' => 5,'Error_Msg'=>'Ошибка БД при запросе направления'));
			}
			if ( empty($response) || empty($response[0]) ) {
				return array(array('Error_Code' => 6,'Error_Msg'=>'Запись о постановке в очередь содержит ссылку на несуществующее направление'));
			}
			$params['EvnDirection_id'] = $response[0]['EvnDirection_id'];
			$params['EvnDirection_Num'] = $response[0]['EvnDirection_Num'];
			$params['EvnDirection_setDate'] = $response[0]['EvnDirection_setDate'];
			$params['Diag_id'] = $response[0]['Diag_id'];
			$params['LpuSection_did'] = $response[0]['LpuSection_id'];
			$params['DirType_id'] = $response[0]['DirType_id'];
			$params['Lpu_did'] = $response[0]['Lpu_id'];
			$params['Org_did'] = $response[0]['Org_id'];
			$params['LpuSectionProfile_id'] = $response[0]['LpuSectionProfile_id'];
			$params['TimetableGraf_id'] = $response[0]['TimetableGraf_id'];
			$params['TimetablePar_id'] = $response[0]['TimetablePar_id'];
			$params['TimetableStac_id'] = $response[0]['TimetableStac_id'];
			$params['TimetableResource_id'] = $response[0]['TimetableResource_id'];
			$params['ConsultationForm_id'] = $response[0]['ConsultationForm_id'];
		}
		else
		{
			$response = array(array('EvnDirection_id'=>null));
			//если нет направления, то возвращаем данные очереди
			/*if (empty($params['EvnDirection_Num']))
			{
				$response = $this->getEvnDirectionNumber($data);
			}
			if (empty($response) || empty($response[0]) || empty($response[0]['EvnDirection_Num']))
			{
				return array(array('Error_Code' => 7,'Error_Msg'=>'Ошибка при получении номера направления'));
			}
			$params['EvnDirection_Num'] = $response[0]['EvnDirection_Num'];

			$query = "
				declare
					@Res bigint,
					@ErrCode int,
					@ErrMessage varchar(4000);
				set @Res = :EvnDirection_id;
				exec p_EvnDirection_ins
					@EvnDirection_id = @Res output,
					@EvnDirection_pid = :EvnDirection_pid,
					@EvnDirection_IsAuto = 2,
					@Lpu_id = :Lpu_id,
					@Server_id = :Server_id,
					@PersonEvn_id = :PersonEvn_id,
					@EvnDirection_setDT = :EvnDirection_setDT,
					@DirType_id = :DirType_id,
					@Diag_id = :Diag_id,
					@EvnDirection_Num = :EvnDirection_Num,
					@EvnDirection_Descr = :EvnDirection_Descr,
					@LpuSection_id = :LpuSection_id,
					@MedPersonal_id = :MedPersonal_id,
					@MedPersonal_zid = :MedPersonal_zid,
					@LpuSectionProfile_id = :LpuSectionProfile_id,
					@TimetableGraf_id = :TimetableGraf_id,
					@TimetableStac_id = :TimetableStac_id,
					@TimetablePar_id = :TimetablePar_id,
					@Lpu_did = :Lpu_did,
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMessage output;
				select @Res as EvnDirection_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			";

			//echo getDebugSQL($query, $params);exit;
			$result = $this->db->query($query, $params);

			if ( is_object($result) ) {
				$response = $result->result('array');
			}
			else {
				return array(array('Error_Code' => 9,'Error_Msg'=>'Ошибка БД при записи направления'));
			}
			
			if (empty($response) || empty($response[0]) || !empty($response[0]['Error_Msg']) || empty($response[0]['EvnDirection_id']))
			{
				return $response;
			}
			*/
		}
		$params['EvnDirection_id'] = $response[0]['EvnDirection_id'];
		$params['Error_Code'] = null;
		$params['Error_Msg'] = null;
		return array($params);
	}

	/**
	 * Возвращает данные для шаблона print_direction_list
	 * Для маркера @#@СписокНаправленийПрочее
	 */
	function getDirectionPrintData($data) {
		$query = "
			select
				direction.EvnDirection_id
				,EQ.EvnQueue_id,
				case when isnull(direction.EvnDirection_IsAuto,1) = 2 then '' else direction.EvnDirection_Num end as EvnDirection_Num
				,convert(varchar(10),direction.EvnDirection_setDT,104) as EvnDirection_setDate
				,LSP.LpuSectionProfile_Name
				,Lpu.Lpu_Nick as Lpu_Name
				,LU.LpuUnit_Name
				,case when EUP.EvnUslugaPar_setDT is null then isnull(DT.DirType_Name,'Очередь') +': ' + isnull(UC.UslugaComplex_Name,'') else 'На обследование: ' + isnull(UC.UslugaComplex_Name,'') end as DirType_Name
				,case when EUP.EvnUslugaPar_setDT is null then 'в очереди с '+ isnull(convert(varchar(10), EQ.EvnQueue_setDate, 104),'') else convert(varchar(10), EUP.EvnUslugaPar_setDT, 104)+' '+convert(varchar(5), EUP.EvnUslugaPar_setDT, 108) end as RecDate
			from
				v_EvnDirection_all direction with (NOLOCK)
				left join v_EvnQueue EQ with (NOLOCK) on direction.EvnDirection_id = EQ.EvnDirection_id AND EQ.TimetableGraf_id is null AND EQ.TimetablePar_id is null AND EQ.TimetableStac_id is null
				left join v_EvnUslugaPar EUP with (NOLOCK) on EQ.EvnUslugaPar_id = EUP.EvnUslugaPar_id
				left join v_UslugaComplex UC with (NOLOCK) on EUP.UslugaComplex_id = UC.UslugaComplex_id
				left join v_Lpu Lpu with (nolock) on isnull(EQ.Lpu_id,direction.Lpu_did) = Lpu.Lpu_id
				left join v_LpuSectionProfile LSP with (nolock) on isnull(EQ.LpuSectionProfile_did,direction.LpuSectionProfile_id) = LSP.LpuSectionProfile_id
				left join v_LpuUnit LU with (nolock) on isnull(EQ.LpuUnit_did,direction.LpuUnit_did) = LU.LpuUnit_id
				left join v_DirType DT with (NOLOCK) on isnull(EQ.DirType_id,direction.DirType_id) = DT.DirType_id
				left join v_EvnPrescrDirection epd with (nolock) on direction.EvnDirection_id = epd.EvnDirection_id
				left join v_EvnPrescr EP with (NOLOCK) on epd.EvnPrescr_id = EP.EvnPrescr_id and EP.PrescriptionType_id in (11,12)
			where
				direction.EvnDirection_pid = :Evn_pid
				--исключаем направления на госпитализацию, на консультацию
				and direction.DirType_id not in (1,5,6,3)
				--исключаем направления на исследования
				and EP.EvnPrescr_id is null and direction.TimetablePar_id is null and direction.TimetableMedService_id is null
				and direction.DirFailType_id is null
			order by direction.EvnDirection_setDT
		";
		//echo getDebugSQL($query, $data); exit();
		$result = $this->db->query($query, $data);
		$response = array();
		if ( is_object($result) )
		{
			$response = $result->result('array');
		}
		return $response;
	}

	/**
	 * Возвращает данные для шаблона print_hospitalisation_list
	 * Для маркера @#@СписокНаправленийГоспитализация
	 */
	function getHospitalisationPrintData($data) {
		$query = "
			select 
				hospitalisation.EvnDirection_id 
				,case when isnull(hospitalisation.EvnDirection_IsAuto,1) = 2 then '' else hospitalisation.EvnDirection_Num end as EvnDirection_Num
				,convert(varchar(10),hospitalisation.EvnDirection_setDT,104) as EvnDirection_setDate
				,LSP.LpuSectionProfile_Name
				,Lpu.Lpu_Nick as Lpu_Name
				,DT.DirType_Name
				,isnull(LS.LpuSection_Name,'') as LpuSection_Name
				,isnull(convert(varchar(10), TTS.TimetableStac_setDate, 104),'') as RecDate
			from 
				v_EvnDirection_all hospitalisation with (NOLOCK)
				left join v_TimetableStac_lite TTS with (NOLOCK) on hospitalisation.EvnDirection_id = TTS.EvnDirection_id
				left join v_LpuSectionProfile LSP with (NOLOCK) on hospitalisation.LpuSectionProfile_id = LSP.LpuSectionProfile_id
				left join v_LpuSection LS with (nolock) on isnull(TTS.LpuSection_id,hospitalisation.LpuSection_did) = LS.LpuSection_id
				left join v_Lpu Lpu with (NOLOCK) on hospitalisation.Lpu_did = Lpu.Lpu_id
				left join v_DirType DT with (NOLOCK) on hospitalisation.DirType_id = DT.DirType_id
			where
				hospitalisation.EvnDirection_pid = :Evn_pid
				and hospitalisation.DirType_id in (1,5,6)
				and hospitalisation.DirFailType_id is null
			order by hospitalisation.EvnDirection_setDT
		";
		//echo getDebugSQL($query, $data); exit();
		$result = $this->db->query($query, $data);
		$response = array();
		if ( is_object($result) )
		{
			$response = $result->result('array');
		}
		return $response;
	}

	/**
	 * Возвращает данные для шаблона print_consultation_list
	 * Для маркера @#@СписокНаправленийКонсультация
	 */
	function getConsultationPrintData($data) {
		$query = "
			select 
				consultation.EvnDirection_id 
				,consultation.EvnDirection_Num 
				,convert(varchar(10),consultation.EvnDirection_setDT,104) as EvnDirection_setDate
				,LSP.LpuSectionProfile_Name
				,mp.MedPersonal_Name
				,convert(varchar(10), TTG.TimetableGraf_begTime, 104)+' '+ convert(varchar(5), TTG.TimetableGraf_begTime, 108) as RecDate
				-- в очереди с 14.05.2012 (сейчас из очереди нельзя однозначно определить, что на консультацию)
			from 
				v_EvnDirection_all consultation with (NOLOCK)
				left join v_EvnQueue EQ with (NOLOCK) on consultation.EvnQueue_id = EQ.EvnQueue_id
				left join v_TimetableGraf_lite TTG with (NOLOCK) on consultation.EvnDirection_id = TTG.EvnDirection_id
				outer apply (
					select top 1 MSF.Person_Fin as MedPersonal_Name
					from v_MedStaffFact MSF with (NOLOCK)
					where TTG.MedStaffFact_id = MSF.MedStaffFact_id
					or (EQ.MedPersonal_did = MSF.MedPersonal_id and EQ.LpuSection_did = MSF.LpuSection_id)
				) mp
				left join v_LpuSectionProfile LSP with (NOLOCK) on consultation.LpuSectionProfile_id = LSP.LpuSectionProfile_id
			where consultation.EvnDirection_pid = :Evn_pid
				and consultation.DirType_id = 3
				and consultation.DirFailType_id is null
			order by consultation.EvnDirection_setDT
		";
		//echo getDebugSQL($query, $data); exit();
		$result = $this->db->query($query, $data);
		$response = array();
		if ( is_object($result) )
		{
			$response = $result->result('array');
		}
		return $response;
	}

	/**
	 * Возвращает данные для шаблона print_evnscreening_list
	 * Для маркера @#@СписокИсследованийПосещение
	 */
	function getScreeningPrintData($data) {
		$query = "
			select 
				EP.EvnPrescr_id as screening_id
				,EP.PrescriptionType_id
				,EPD.EvnDirection_id
				,ED.EvnDirection_Num
				,convert(varchar(10),ED.EvnDirection_setDT,104) as EvnDirection_setDate
				,UC.UslugaComplex_Name
				,case
					when EP.PrescriptionType_id = 12 then (select count(EvnPrescrFuncDiag_id) from v_EvnPrescrFuncDiagUsluga with (nolock) where EvnPrescrFuncDiag_id = EP.EvnPrescr_id)
					else 1
				end as cntUsluga
				,case
					when TTMS.TimetableMedService_id is not null then 'записан: '+ convert(varchar(10), TTMS.TimetableMedService_begTime, 104)+' '+ convert(varchar(5), TTMS.TimetableMedService_begTime, 108)
					when EQ.EvnQueue_id is not null then 'в очереди с '+ isnull(convert(varchar(10), EQ.EvnQueue_setDate, 104),'')
					else ''
				end as RecDate
			from 
				v_EvnPrescr EP with (nolock)
				cross apply (
					select top 1 epd.EvnDirection_id from v_EvnPrescrDirection epd with (nolock)
					where EP.EvnPrescr_id = epd.EvnPrescr_id
				) EPD
				inner join v_EvnDirection_all ED with (NOLOCK) on EPD.EvnDirection_id = ED.EvnDirection_id
				--left join v_EvnPrescrLabDiag EPLD with (nolock) on EP.PrescriptionType_id = 11 and EPLD.EvnPrescrLabDiag_id = EP.EvnPrescr_id
				outer apply (
					select top 100 * from v_EvnPrescrLabDiag with (nolock) where EP.PrescriptionType_id = 11 and EvnPrescrLabDiag_id = EP.EvnPrescr_id
				) EPLD
				--left join v_EvnPrescrFuncDiagUsluga EPFDU with (nolock) on EP.PrescriptionType_id = 12 and EPFDU.EvnPrescrFuncDiag_id = EP.EvnPrescr_id
				outer apply (
					select top 100 * from v_EvnPrescrFuncDiagUsluga with (nolock) where EP.PrescriptionType_id = 12 and EvnPrescrFuncDiag_id = EP.EvnPrescr_id
				) EPFDU
				inner join v_UslugaComplex UC with (nolock) on UC.UslugaComplex_id = coalesce(EPLD.UslugaComplex_id,EPFDU.UslugaComplex_id)
				left join v_EvnQueue EQ with (NOLOCK) on ED.EvnDirection_id = EQ.EvnDirection_id
				left join v_TimetableMedService_lite TTMS with (NOLOCK) on ED.EvnDirection_id = TTMS.EvnDirection_id
			where
				EP.EvnPrescr_pid = :Evn_pid
				and EP.PrescriptionStatusType_id != 3
				and EP.PrescriptionType_id in (11,12)
			order by EP.EvnPrescr_id
		";
		//echo getDebugSQL($query, $data); exit();
		$result = $this->db->query($query, $data);
		$response = array();
		if ( is_object($result) )
		{
			$tmp = $result->result('array');
			$last_id = 0;
			foreach($tmp as $row) {
				if($last_id != $row['screening_id'])
				{
					$last_id = $row['screening_id'];
					$cnt = 0;
					if($row['PrescriptionType_id'] == 11)
					{
						$usluga_list = array($row['UslugaComplex_Name']);
					}
					else
					{
						$usluga_list = array();
					}
				}
				
				if($row['PrescriptionType_id'] != 11)
				{
					$usluga_list[] = $row['UslugaComplex_Name'];
				}
				$cnt++;
				
				if($cnt == $row['cntUsluga'])
				{
					$response[]=array(
						'UslugaComplex_Name_List' => implode(', ',$usluga_list)
						,'EvnDirection_Num' => $row['EvnDirection_Num']
						,'EvnDirection_setDate' => $row['EvnDirection_setDate']
						,'RecDate' => $row['RecDate']
					);
					$cnt = 0;
				}
			}
		}
		return $response;
	}

	/**
	 * Часть условий по отмене направлений/записей для входящих в нашу МО
	 */
	function getDirectionCancelConditionsForIncoming()
	{
		$session = $this->getSessionParams();

		$MedPersonal_id = $session['medpersonal_id'];
		$allCanCancel = (isset($session['setting']['server']['evn_direction_cancel_right_mo_where_created']) && $session['setting']['server']['evn_direction_cancel_right_mo_where_created'] == 2) ? '1' : '0';
		$hasGroupThatCanCancel = in_array('toCurrMoDirCancel', explode('|', $session['groups']) ) ? '1' : '0';

		return "( {$allCanCancel} = 1 OR ED.MedPersonal_did = {$MedPersonal_id} OR {$hasGroupThatCanCancel} = 1)";
	}

	/**
	 * Часть условий по отмене направлений/записей для исходящих из нашей МО
	 */
	function getDirectionCancelConditionsForOutcoming()
	{
		$session = $this->getSessionParams();

		$MedPersonal_id = $session['medpersonal_id'];
		$allCanCancel = (isset($session['setting']['server']['evn_direction_cancel_right_mo_where_adressed']) && $session['setting']['server']['evn_direction_cancel_right_mo_where_adressed'] == 2) ? '1' : '0';
		$hasGroupThatCanCancel = in_array('currMoDirCancel', explode('|', $session['groups']) ) ? '1' : '0';

		return "( {$allCanCancel} = 1 OR ED.MedPersonal_id = {$MedPersonal_id} OR {$hasGroupThatCanCancel} = 1)";
	}

	/**
	 * Загрузка списка направлений в раздел "Направления"
	 * в рамках посещений поликлиники или лечения в стационаре
	 * @param array $data
	 * @param string $parentEvnClass_SysNick
	 * @return array|bool
	 */
	function getEvnDirectionViewData($data, $parentEvnClass_SysNick = 'EvnVizitPL') {
		// сначала проверяем, есть ли вообще направления
		$query = "
			select COUNT(EvnDirection_id) as cnt
			from v_EvnDirection_all with (nolock)
			where EvnDirection_pid = :EvnDirection_pid
		";
		$goAll = $this->getGlobalOptions();
		$go = $goAll['globals'];
		$session = $this->getSessionParams();


		$crossApplyPmUser = "
			outer apply (
					select top 1 * from (
						select top 1 
							Timetable.pmUser_updID,
							Timetable.TimetableStac_setDate as recDate
						from 
							v_TimetableStac_lite Timetable (nolock)
						where
							ED.DirType_id in (1,2,4,5,6)
							and Timetable.EvnDirection_id = ED.EvnDirection_id
						UNION ALL
						select top 1
							Timetable.pmUser_updID,
							Timetable.TimetableMedService_begTime as recDate
						from 
							v_TimetableMedService_lite Timetable (nolock)
						where
							ED.DirType_id in (2,3,10,11,15,25)
							and Timetable.EvnDirection_id = ED.EvnDirection_id
						UNION ALL
						select top 1
							Timetable.pmUser_updID,
							Timetable.TimetableResource_begTime as recDate
						from
							v_TimetableResource_lite Timetable (nolock)
						where
							ED.DirType_id in (10)
							and Timetable.EvnDirection_id = ED.EvnDirection_id
						UNION ALL
						select top 1
							Timetable.pmUser_updID,
							ISNULL(Timetable.TimetableGraf_begTime, Timetable.TimetableGraf_factTime) as recDate
						from
							v_TimetableGraf_lite Timetable (nolock)
						where
							ED.DirType_id in (2,3,4,6,16)
							and Timetable.EvnDirection_id = ED.EvnDirection_id
						UNION ALL
						select top 1
							EQ.pmUser_updID,
							null as recDate
						from
							v_EvnQueue EQ (nolock)
						where 
							EQ.EvnDirection_id = ED.EvnDirection_id
							and (EQ.EvnQueue_recDT is null or EQ.pmUser_recID = 1)
							and EQ.EvnQueue_failDT is null
							and EQ.EvnQueue_IsArchived is null
						UNION ALL
						select
							ED.pmUser_updID,
							null as recDate
						where isnull(ED.EvnDirection_IsAuto,1) = 1
					) tt
				) TT
		";


		$params = array(
			'EvnDirection_pid' => $data['EvnDirection_pid'],
			'Lpu_id' => $session['lpu_id']
		);
		//echo getDebugSQL($query, $params); exit;
		$result = $this->db->query($query, $params);

		if ( !is_object($result) ) {
			return $result;
		}

		$rows = $result->result('array');

		if ( $rows[0]['cnt'] == 0 ) {
			return array();
		}

		$addJoin = "";
		$addWhere = '';

		//Направления, отмененные с причиной “Неверный ввод”, не отображать на форме
		$addWhere .= ' and isnull(ED.DirFailType_id, 0) != 14
				and isnull(EQ.QueueFailCause_id, 0) != 5
				and isnull(ESH.EvnStatusCause_id, 0) != 4 ';

		$selectStatusData = "case when ED.EvnStatus_id is null and (ED.DirFailType_id > 0 OR EQ.QueueFailCause_id > 0 ) then 12 else ED.EvnStatus_id end as EvnStatus_id,
			case 
				when EPM.EvnPrescrMse_setDT is not null then 'Создано'
				when EvnStatus.EvnStatus_Name is null and (ED.DirFailType_id > 0 OR EQ.QueueFailCause_id > 0 ) then 'Отменено' 
				else EvnStatus.EvnStatus_Name 
			end as EvnStatus_Name,
			coalesce(EvnStatusCause.EvnStatusCause_Name, DFT.DirFailType_Name, QFC.QueueFailCause_Name) as EvnStatusCause_Name,
			convert(varchar(10), coalesce(EPM.EvnPrescrMse_setDT, ED.EvnDirection_statusDate, ED.EvnDirection_failDT, EQ.EvnQueue_failDT), 104) as EvnDirection_statusDate,
			";
		$addJoin .= "
			outer apply(
				select top 1 ESH.EvnStatus_id, ESH.EvnStatusCause_id, ESH.pmUser_insID/*, ESH.EvnStatusHistory_Cause*/
				from EvnStatusHistory ESH with(nolock)
				where ESH.Evn_id = ED.EvnDirection_id
					and ESH.EvnStatus_id = ED.EvnStatus_id
				order by ESH.EvnStatusHistory_begDate desc
			) ESH
			left join EvnStatus with(nolock) on EvnStatus.EvnStatus_id = ESH.EvnStatus_id
			left join EvnStatusCause with(nolock) on EvnStatusCause.EvnStatusCause_id = ESH.EvnStatusCause_id
			left join v_DirFailType DFT with(nolock) on DFT.DirFailType_id = ED.DirFailType_id
			left join v_QueueFailCause QFC with(nolock) on QFC.QueueFailCause_id = EQ.QueueFailCause_id
			";
		if (!in_array($parentEvnClass_SysNick, array('EvnVizitPL','EvnVizitPLStom'))) {
			$selectStatusData .= "fLpu.Lpu_Nick as StatusFromLpu,
				fMP.Person_Fio as StatusFromMP,";
			$addJoin .= "
				left join v_pmUserCache fUser with(nolock) on fUser.PMUser_id = coalesce(ED.pmUser_failID,EQ.pmUser_failID,ESH.pmUser_insID)
				left join v_Lpu fLpu with(nolock) on fLpu.Lpu_id = fUser.Lpu_id
				outer apply(
					select top 1 MP.MedPersonal_id, MP.Person_Fio
					from v_MedPersonal MP with(nolock)
					where MP.MedPersonal_id = fUser.MedPersonal_id and MP.Lpu_id = fUser.Lpu_id and MP.WorkType_id = 1
				) fMP";
		}

		$addSelect = ' ';
		if (isset($addSelect)) {
			$addSelect .= ',EvnXmlDir.EvnXml_id as EvnXmlDir_id
				,EvnXmlDir.XmlType_id as EvnXmlDirType_id';
			$addJoin .= "
				outer apply (
					select top 1 EvnXml.EvnXml_id, XmlType.XmlType_id
					from XmlType with (nolock)
					left join EvnXml with (nolock) on EvnXml.XmlType_id = XmlType.XmlType_id and EvnXml.Evn_id = ED.EvnDirection_id
					where XmlType.XmlType_id = case
					when 13 = DT.DirType_Code then 20
					else null end
				) EvnXmlDir";
		}

		$directionNum = "cast(ED.EvnDirection_Num as varchar)";
		if (getRegionNick() == 'vologda') {
			$directionNum = "ISNULL(RIGHT(L.Lpu_f003mcod, 4), '') + RIGHT('000000' + ED.EvnDirection_Num, 6)";
		}
		
		if ($this->regionNick == 'msk') {
			$addSelect .= '
				,OO.Org_IsNotForSystem
				,EUT.EvnUslugaTelemed_id
				,EUT.ServiceListPackage_id
				,ED.UslugaComplex_did
				,ED.Org_oid
			';
			$addJoin .= "
				outer apply (
					select top 1 
						EUT.EvnUslugaTelemed_id,
						SLP.ServiceListPackage_id
					from v_EvnUslugaTelemed EUT (nolock)
						left join stg.ServiceListPackage SLP (nolock) on 
							SLP.ServiceListPackage_ObjectID = EUT.EvnUslugaTelemed_id and 
							SLP.PackageStatus_id = 5
					where EUT.EvnDirection_id = ED.EvnDirection_id
				) EUT
			";
		}

		$query = "
			declare
				@Evn_id bigint = :EvnDirection_pid,
				@curDate datetime = dbo.tzGetDate(),
				@curLpu_id bigint = :Lpu_id;
				
			SELECT
				ED.Lpu_id as Lpu_id,
				ED.Diag_id as Diag_id,-- для фильтра по ГУЗам
				ED.EvnDirection_IsSigned,
				EPM.EvnPrescrMse_IsSigned,
				CASE
					WHEN (ED.EvnStatus_id in (12,13,15)) THEN 0
					WHEN EDC.EvnDirectionCVI_id is not null and ED.Lpu_id = @curLpu_id THEN 1
					WHEN TT.recDate > @curDate and ( ED.ARMType_id =24 OR TT.pmUser_updID BETWEEN 1000000 AND 5000000) THEN 0
					WHEN ET.ElectronicTalonStatus_id = 1 THEN 0
					WHEN ED.EvnDirection_IsAuto = 2 AND " . ($go['disallow_canceling_el_dir_for_elapsed_time'] ? '1' : '0') . " = 1 AND TT.recDate <= @curDate THEN 0
					WHEN ISNULL(ED.EvnDirection_IsAuto, 1) = 1 AND " . ($go['allow_canceling_without_el_dir_for_past_days'] ? '1' : '0') . " = 0 and TT.recDate <= @curDate THEN 0
					WHEN DF.DirectionFrom = 'incoming' THEN CASE WHEN {$this->getDirectionCancelConditionsForIncoming()} THEN 1 ELSE 0 END
					WHEN DF.DirectionFrom = 'outcoming' THEN CASE WHEN {$this->getDirectionCancelConditionsForOutcoming()} THEN 1 ELSE 0 END
					WHEN DF.DirectionFrom = 'both' THEN CASE WHEN {$this->getDirectionCancelConditionsForIncoming()} OR {$this->getDirectionCancelConditionsForOutcoming()} THEN 1 ELSE 0 END
			
				ELSE 1 END as allowCancel,
	
				case
					when TTG.TimetableGraf_id is not null then isnull(DT.DirType_Name,'На консультацию') +':'
					when TTMS.TimetableMedService_id is not null then 'На исследование: ' + isnull(UC.UslugaComplex_Name,'')
					when TTS.TimetableStac_id is not null then isnull(DT.DirType_Name,'В стационар') +':'
					when EQ.EvnQueue_id is not null then
						case when EUP.EvnUslugaPar_setDT is null then isnull(DT.DirType_Name,'Очередь') +':' else 'На исследование: ' + isnull(UC.UslugaComplex_Name,'') end
					-- Пытаемся получить инфрмацию из самого направления
					when coalesce(TTG.TimetableGraf_id, TTMS.TimetableMedService_id, TTS.TimetableStac_id, EQ.EvnQueue_id) is null and ED.DirType_id is not null then DT.DirType_Name +':'
				else '' end as RecWhat,
				case
					when EDC.EvnDirectionCVI_Lab is not null then EDC.EvnDirectionCVI_Lab 
					when ED.Org_oid is not null then OO.Org_Nick
					when TTG.TimetableGraf_id is not null then isnull(LS.LpuSection_Name,'') +' / '+ isnull(Lpu.Lpu_Nick,'')
					when TTMS.TimetableMedService_id is not null then isnull(MS.MedService_Name,'') +' / '+ isnull(Lpu.Lpu_Nick,'')
					when TTS.TimetableStac_id is not null then isnull(LS.LpuSection_Name,'') +' / '+ isnull(Lpu.Lpu_Nick,'')
					when EQ.EvnQueue_id is not null then
						case
							when MS.MedService_id is not null and  MS.LpuSection_id is null and MS.LpuUnit_id is null
							then isnull(MS.MedService_Name,'')
							when MS.MedService_id is not null and  MS.LpuSection_id is null and MS.LpuUnit_id is not null
							then isnull(MS.MedService_Name,'') +' / '+ isnull(LU.LpuUnit_Name,'')
							when MS.MedService_id is not null and  MS.LpuSection_id is not null and MS.LpuUnit_id is not null
							then isnull(MS.MedService_Name,'') +' / '+ isnull(LSP.LpuSectionProfile_Name,'') +' / '+ isnull(LU.LpuUnit_Name,'')
							else isnull(LSP.LpuSectionProfile_Name,'') +' / '+ isnull(LU.LpuUnit_Name,'')
						end +' / '+ isnull(Lpu.Lpu_Nick,'')
					when coalesce(TTG.TimetableGraf_id, TTMS.TimetableMedService_id, TTS.TimetableStac_id, EQ.EvnQueue_id) is null
						then isnull(LSP.LpuSectionProfile_Name,'') +' / '+ isnull(LS.LpuSection_Name,'') +' / '+ isnull(Lpu.Lpu_Nick,'')
				else '' end as RecTo,

				case
					when TTG.TimetableGraf_id is not null then isnull(convert(varchar(10), TTG.TimetableGraf_begTime, 104),'')+' '+isnull(convert(varchar(5), TTG.TimetableGraf_begTime, 108),'')
					when TTMS.TimetableMedService_id is not null then isnull(convert(varchar(10), TTMS.TimetableMedService_begTime, 104),'')+' '+isnull(convert(varchar(5), TTMS.TimetableMedService_begTime, 108),'')
					when TTS.TimetableStac_id is not null then isnull(convert(varchar(10), TTS.TimetableStac_setDate, 104),'')
					when EVK.EvnVK_id is not null then isnull(convert(varchar(10), EVK.EvnVK_setDate, 104),'')
					when EPM.EvnPrescrMse_id is not null then EPM.EvnStatus_Name
					when EDC.EvnDirectionCVI_takeDT is not null
						then 'Дата взятия образца ' + convert(varchar(10), EDC.EvnDirectionCVI_takeDT, 104)
					when EQ.EvnQueue_id is not null then
						case when EUP.EvnUslugaPar_setDT is null then 'В очереди с '+ isnull(convert(varchar(10), EQ.EvnQueue_setDate, 104),'') else convert(varchar(10), EUP.EvnUslugaPar_setDT, 104)+' '+convert(varchar(5), EUP.EvnUslugaPar_setDT, 108) end
					when coalesce(TTG.TimetableGraf_id, TTMS.TimetableMedService_id, TTS.TimetableStac_id, EQ.EvnQueue_id) is null
						then 'Направление выписано ' + convert(varchar(10), ED.EvnDirection_setDT, 104)
				else '' end as RecDate,
				case
					when
						TTG.TimetableGraf_id is null and TTMS.TimetableMedService_id is null and TTS.TimetableStac_id is null and EVK.EvnVK_id is null and EQ.EvnQueue_id is not null and EUP.EvnUslugaPar_setDT is null
					then
						datediff(day, EQ.EvnQueue_setDT, @curDate)
					else
						null
				end as EvnQueue_Days,
				0 as use_template_str,
				case
					when TTG.TimetableGraf_id is not null then 'TimetableGraf'
					when TTMS.TimetableMedService_id is not null then 'TimetableMedService'
					when TTS.TimetableStac_id is not null then 'TimetableStac'
					when EQ.EvnQueue_id is not null then
						case when EUP.EvnUslugaPar_setDT is null then 'EvnQueue' else 'EvnUslugaPar' end
					when coalesce(TTG.TimetableGraf_id, TTMS.TimetableMedService_id, TTS.TimetableStac_id, EQ.EvnQueue_id) is null
						then 'EvnDirection'
				else '' end as timetable,
				case
					when TTG.TimetableGraf_id is not null then TTG.TimetableGraf_id
					when TTMS.TimetableMedService_id is not null  then TTMS.TimetableMedService_id
					when TTS.TimetableStac_id is not null then TTS.TimetableStac_id
					when EQ.EvnQueue_id is not null then
						case when EUP.EvnUslugaPar_setDT is null then EQ.EvnQueue_id else EQ.EvnUslugaPar_id end
					when coalesce(TTG.TimetableGraf_id, TTMS.TimetableMedService_id, TTS.TimetableStac_id, EQ.EvnQueue_id) is null
						then ED.EvnDirection_id
				else '' end as timetable_id,
				ED.EvnDirection_id as EvnDirection_id,
				isnull(ED.EvnDirection_IsAuto,1) as EvnDirection_IsAuto,
				case when EQ.EvnQueue_failDT is null then
					case 
						when EDC.EvnDirectionCVI_RegNumber is not null then '/ Регистрационный номер: ' + cast(EDC.EvnDirectionCVI_RegNumber as varchar)
						when ED.EvnDirection_Num is null or isnull(ED.EvnDirection_IsAuto,1) = 2 then '' 
						else '/ Направление № '+ {$directionNum} 
					end
				else
				 	case when EQ.QueueFailCause_id = 6 then 'Обслужен вне очереди' else 'ОТМЕНЕНО' end
				end as EvnDirection_Num,
				ED.Person_id,
				ED.PersonEvn_id as PersonEvn_id,
				ED.Server_id as Server_id,
				@Evn_id as timetable_pid,
				{$selectStatusData}
				LU.LpuUnitType_SysNick,
				EPM.EvnPrescrMse_id,
				EDHT.EvnDirectionHTM_id,
				EDH.EvnDirectionHistologic_id,
				EDC.EvnDirectionCVI_id,
				EPM.Lpu_gid,
				DT.DirType_Code,
				EPVK.EvnPrescrVK_id,
				EPVK.EvnStatus_Name as EvnStatus_epvkName,
				EPVK.EvnStatus_SysNick as EvnStatus_epvkSysNick,
				RCP.RemoteConsultProtocol_FilePath as RCP_FilePath,
				RCP.RemoteConsultProtocol_id as RCP_id
				{$addSelect}
			FROM
				v_EvnDirection_all ED with (nolock)
				 -- полка
				outer apply (
					Select top 1 TimetableGraf_id, MedStaffFact_id, TimetableGraf_begTime from v_TimetableGraf_lite TTG with (nolock) where TTG.EvnDirection_id = ED.EvnDirection_id
				) TTG
				left join v_MedStaffFact MSF with (nolock) on TTG.MedStaffFact_id = MSF.MedStaffFact_id
				 -- службы и параклиника
				outer apply (
					Select top 1 TimetableMedService_id, TimetableMedService_begTime from v_TimetableMedService_lite TTMS with (nolock) where TTMS.EvnDirection_id = ED.EvnDirection_id
				) TTMS
				 -- стац
				outer apply (
					Select top 1 TimetableStac_id, LpuSection_id, TimetableStac_setDate from v_TimetableStac_lite TTS with (nolock) where TTS.EvnDirection_id = ED.EvnDirection_id
				) TTS
				 -- очередь
				left join v_EvnQueue EQ with (nolock) on EQ.EvnDirection_id = ED.EvnDirection_id
				-- назначение на ВК в очереди
				outer apply (
					select top 1
					 	EPVK.EvnPrescrVK_id,
					 	ES.EvnStatus_SysNick,
					 	ES.EvnStatus_Name
					from
						v_EvnPrescrVK EPVK with (nolock)
						left join v_EvnStatus ES with (nolock) on ES.EvnStatus_id = ISNULL(EPVK.EvnStatus_id, 43)
					where
						EPVK.EvnQueue_id = EQ.EvnQueue_id
				) EPVK
				-- протокол ВК
				outer apply (
					select top 1 * from v_EvnVK EVK with (nolock) where EVK.EvnPrescrVK_id = EPVK.EvnPrescrVK_id
				) EVK
				-- направление МСЭ
				outer apply (
					select top 1 EPM.EvnPrescrMse_id, EPM.EvnPrescrMse_setDT, EPM.Lpu_gid, ES.EvnStatus_Name, EPM.EvnPrescrMse_IsSigned
					from v_EvnPrescrMse EPM with (nolock) 
					left join EvnStatus ES with (nolock) on EPM.EvnStatus_id = ES.EvnStatus_id
					where ED.EvnQueue_id = EPM.EvnQueue_id
				) EPM
				-- Направление на ВМП
				left join v_EvnDirectionHTM EDHT with (nolock) on EDHT.EvnDirectionHTM_id = ED.EvnDirection_id
				-- Направление на патологогистологическое исследование
				left join v_EvnDirectionHistologic EDH with (nolock) on EDH.EvnDirectionHistologic_id = ED.EvnDirection_id
				-- Направление на тест на КВИ
				left join v_EvnDirectionCVI EDC with (nolock) on EDC.EvnDirectionCVI_id = ED.EvnDirection_id
				-- сама служба (todo: надо ли оно)
				left join v_MedService MS with (nolock) on MS.MedService_id = ED.MedService_id -- ED.MedService_did должно быть
				-- отделение для полки и стаца и для очереди
				left join v_LpuSection LS with (nolock) on LS.LpuSection_id = coalesce(ED.LpuSection_did, MSF.LpuSection_id, TTS.LpuSection_id, MS.LpuSection_id)
				-- заказанная услуга для параклиники
				left join v_EvnUslugaPar EUP with (NOLOCK) on EUP.EvnDirection_id = ED.EvnDirection_id
				left join v_UslugaComplex UC with (NOLOCK) on EUP.UslugaComplex_id = UC.UslugaComplex_id
				-- подразделение для очереди и служб
				left join v_LpuUnit LU with (nolock) on coalesce(ED.LpuUnit_did,EQ.LpuUnit_did,MS.LpuUnit_id,LS.LpuUnit_id) = LU.LpuUnit_id -- todo: в ED.LpuUnit_did пусто, чего не должно быть
				-- профиль для очереди
				left join v_LpuSectionProfile LSP with (nolock) on coalesce(ED.LpuSectionProfile_id,EQ.LpuSectionProfile_did,LS.LpuSectionProfile_id) = LSP.LpuSectionProfile_id -- todo: тут на примере оказалось что почему то ED.LpuSectionProfile_id != EQ.LpuSectionProfile_did, чего не должно быть
				-- тип направления
				left join v_DirType DT with (NOLOCK) on ED.DirType_id = DT.DirType_id
				-- ЛПУ
				left join v_Lpu L with (nolock) on l.Lpu_id = ED.Lpu_id
				left join v_Lpu Lpu with (nolock) on Lpu.Lpu_id = coalesce(ED.Lpu_did, LS.Lpu_id, MS.Lpu_id, EQ.Lpu_id)
				left join v_Org OO with (nolock) on OO.Org_id = ED.Org_oid
				left join v_RemoteConsultProtocol RCP with (nolock) on RCP.EvnDirection_id = ED.EvnDirection_id
				{$addJoin}
				{$crossApplyPmUser}
				outer apply (
					SELECT
						CASE
							WHEN ISNULL(ED.Lpu_did, ED.Lpu_id) = ED.Lpu_sid THEN 'both'
							WHEN ISNULL(ED.Lpu_did, ED.Lpu_id) = @curLpu_id THEN 'incoming'
							ELSE 'outcoming' END
						as DirectionFrom
				) DF

				outer apply (
					SELECT TOP 1
						ElectronicTalonStatus_id
					from 
						v_ElectronicTalon ET with (nolock)
					WHERE ET.EvnDirection_id = ED.EvnDirection_id
			) ET
			WHERE
				ED.EvnDirection_pid = @Evn_id
				--and coalesce(ED.TimetableGraf_id, ED.TimetableMedService_id, ED.TimetableStac_id, ED.EvnQueue_id) is not null
				-- исключаем из списка направления, связанные с назначениями
				and not exists (select top 1 epd.EvnPrescr_id from v_EvnPrescrDirection epd with (nolock) where epd.EvnDirection_id = ED.EvnDirection_id)
				-- исключаем из списка направления на МСЭ кроме статусов Новое и Отказ
				and not exists (select top 1 * from v_EvnPrescrMse EPM with (nolock) where ED.EvnQueue_id = EPM.EvnQueue_id and EPM.EvnStatus_id not in (27,32))
				{$addWhere}
			order by ED.EvnDirection_id

		";
		//echo getDebugSQL($query, $params); exit;
		
		$result = $this->db->query($query, $params);
		if (is_object($result))
		{
			$resp = $result->result('array');

			$EvnDirectionIds = [];
			foreach($resp as $one) {
				if (!empty($one['EvnDirection_id']) && $one['EvnDirection_IsSigned'] == 2 && !in_array($one['EvnDirection_id'], $EvnDirectionIds)) {
					$EvnDirectionIds[] = $one['EvnDirection_id'];
				}
			}
			$isEMDEnabled = $this->config->item('EMD_ENABLE');
			if (!empty($EvnDirectionIds) && !empty($isEMDEnabled)) {
				$this->load->model('EMD_model');
				$signStatus = $this->EMD_model->getSignStatus([
					'EMDRegistry_ObjectName' => 'EvnDirection',
					'EMDRegistry_ObjectIDs' => $EvnDirectionIds,
					'MedStaffFact_id' => $data['session']['CurMedStaffFact_id'] ?? null
				]);

				foreach($resp as $key => $one) {
					$resp[$key]['EvnDirection_SignCount'] = 0;
					$resp[$key]['EvnDirection_MinSignCount'] = 0;
					if (!empty($one['EvnDirection_id']) && $one['EvnDirection_IsSigned'] == 2 && isset($signStatus[$one['EvnDirection_id']])) {
						$resp[$key]['EvnDirection_SignCount'] = $signStatus[$one['EvnDirection_id']]['signcount'];
						$resp[$key]['EvnDirection_MinSignCount'] = $signStatus[$one['EvnDirection_id']]['minsigncount'];
						$resp[$key]['EvnDirection_IsSigned'] = $signStatus[$one['EvnDirection_id']]['signed'];
					}
				}
			}

			return swFilterResponse::filterNotViewDiag($resp, $data);
		}
		else
		{
			return false;
		}
	}

	/**
	 * Загрузка списка направлений в рамках движения в стационаре в раздел "Направления"
	 * @param $data
	 * @return array|bool
	 */
	function getEvnDirectionStacViewData($data) {
		$data['EvnDirection_pid'] = $data['EvnDirectionStac_pid'];
		return $this->getEvnDirectionViewData($data, 'EvnSection');
	}

	/**
	 * Загрузка списка направлений в рамках посещения в стоматологии в раздел "Направления"
	 * @param $data
	 * @return array|bool
	 */
	function getEvnDirectionStomViewData($data) {
		$data['EvnDirection_pid'] = $data['EvnDirectionStom_pid'];
		return $this->getEvnDirectionViewData($data, 'EvnVizitPLStom');
	}

	/**
	 * Подтверждение направления на госпитализацию
	 */
	function setConfirmed($data) 
	{
		return $this->execCommonSP('p_EvnDirection_confirm', array(
			'EvnDirection_id' => $data['EvnDirection_id'],
			'EvnDirection_confDT' => $data['Hospitalisation_setDT'],
			'pmUser_id' => $data['pmUser_id']
		), 'array_assoc');
	}


	/**
	 * Выборка списка направлений на госпитализации в текущее ЛПУ 
	 */
	function loadHospDirectionGrid($data) {
		$params = array(
			'Lpu_id' => $data['Lpu_id'],
			'beg_date' => $data['beg_date'],
			'end_date' => $data['end_date']
		);
		$filters = '';
		
		if (empty($data['DirType_id']))
		{
			$filters .= ' AND ED.DirType_id in (1,5,6)';
		}
		else
		{
			$filters .= ' AND ED.DirType_id = :DirType_id';
			$params['DirType_id'] = $data['DirType_id'];
		}
		
		if (!empty($data['LpuSectionProfile_id']))
		{
			$filters .= ' AND ED.LpuSectionProfile_id = :LpuSectionProfile_id';
			$params['LpuSectionProfile_id'] = $data['LpuSectionProfile_id'];
		}

		$join = "";
		$isSearchByEncryp = false;
		$selectPersonData = "
			convert(varchar(10), PS.Person_Birthday, 104) as Person_Birthday,
			PS.Person_Surname + ' '
				+ case when PS.Person_Firname is not null then SUBSTRING(PS.Person_Firname,1,1) +'.' else '' end
				+ case when PS.Person_Secname is not null then SUBSTRING(PS.Person_Secname,1,1) +'.' else '' end
			as Person_Fio,
		";
		if (isEncrypHIVRegion($this->regionNick)) {
			if (allowPersonEncrypHIV($data['session'])) {
				$isSearchByEncryp = isSearchByPersonEncrypHIV($data['Person_Fio']);
				$join .= " left join v_PersonEncrypHIV peh with(nolock) on peh.Person_id = ED.Person_id";
				$selectPersonData = "
					case when peh.PersonEncrypHIV_Encryp is null then convert(varchar(10), PS.Person_Birthday, 104) end as Person_Birthday,
					case when peh.PersonEncrypHIV_Encryp is null 
						then PS.Person_Surname +' '+ SUBSTRING(PS.Person_Firname,1,1) +'.'+ SUBSTRING(PS.Person_Secname,1,1) +'.'
						else rtrim(peh.PersonEncrypHIV_Encryp) end 
					as Person_Fio,";
			} else {
				//Не отображать анонимных шифрованных пациентов
				$filters .= " and not exists(
					select top 1 peh.PersonEncrypHIV_Encryp
					from v_PersonEncrypHIV peh with(nolock)
					inner join v_EncrypHIVTerr eht with(nolock) on eht.EncrypHIVTerr_id = peh.EncrypHIVTerr_id
						and isnull(eht.EncrypHIVTerr_Code,0) = 20
					where peh.Person_id = ED.Person_id
				)";
			}
		}
		
		if ( !empty($data['Person_Fio']) ) {
			if (allowPersonEncrypHIV($data['session']) && $isSearchByEncryp) {
				$filters .= " and peh.PersonEncrypHIV_Encryp like :Person_Fio";
			} else {
				$filters .= " and (ISNULL(PS.Person_Surname, '') + ' ' + ISNULL(PS.Person_Firname, '') + ' ' + ISNULL(PS.Person_Secname, '')) like :Person_Fio";
			}
			$params['Person_Fio'] = $data['Person_Fio'] . '%';
		}

		if (!empty($data['Person_BirthDay']))
		{
			$filters .= ' AND cast(PS.Person_BirthDay as date) = cast(:Person_BirthDay as date)';
			$params['Person_BirthDay'] = $data['Person_BirthDay'];
		}
		
		if (!empty($data['IsHospitalized']))
		{
			if ($data['IsHospitalized'] == 2)
			{
				$filters .= ' AND EvnPS.EvnPS_id is not null';
			}
			else
			{
				$filters .= ' AND EvnPS.EvnPS_id is null';
			}
		}

		if (!empty($data['IsConfirmed']))
		{
			if ($data['IsConfirmed'] == 1)
			{
				$filters .= ' AND (ED.EvnDirection_IsConfirmed is null OR ED.EvnDirection_IsConfirmed = 1)';
			}
			else
			{
				$filters .= ' AND ED.EvnDirection_IsConfirmed = 2';
			}
		}
		
		$query = "
			select
				-- select
				ED.EvnDirection_id,
				--EvnPS.EvnPS_id,
				case when EvnPS.EvnPS_id is not null then 'true' else 'false' end as IsHospitalized,
				--ED.pmUser_confID,
				--ED.EvnDirection_confDT,
				case when ED.EvnDirection_IsConfirmed = 2 then 'true' else 'false' end as IsConfirmed,
				ED.Lpu_id,
				Lpu.Org_id,
				Lpu.Org_Nick,
				Lpu.Org_Name,
				RTRIM(Lpu.Lpu_Nick) as Lpu_Name,
				ED.EvnDirection_Num,
				ED.DirType_id,
				RTRIM(DT.DirType_Name) as DirType_Name,
				ED.LpuSectionProfile_id,
				RTRIM(LSP.LpuSectionProfile_Name) as LpuSectionProfile_Name,
				convert(varchar(10), ED.EvnDirection_setDate, 104) + ' ' + ISNULL(ED.EvnDirection_setTime,'') as EvnDirection_setDateTime,
				{$selectPersonData}
				ED.Diag_id,
				RTRIM(Diag.Diag_Code) + '. ' + RTRIM(Diag.Diag_Name) as Diag_Name,
				ED.EvnDirection_Descr,
				ED.MedPersonal_id,
				MP.Person_Fin as MedPersonal_Fio,
				ED.Person_id,
				ED.PersonEvn_id,
				ED.Server_id,
				convert(varchar(10), ED.EvnDirection_confDT, 104) as Hospitalisation_setDT
				-- end select
			from 
				-- from
				v_EvnDirection_all ED with (NOLOCK)
				left join v_PersonState PS with (NOLOCK) on ED.Person_id = PS.Person_id
				left join v_MedPersonal MP with (NOLOCK) on ED.MedPersonal_id = MP.MedPersonal_id AND ED.Lpu_id = MP.Lpu_id
				inner join v_Lpu Lpu with (NOLOCK) on Lpu.Lpu_id = ED.Lpu_id
				inner join v_DirType DT with (NOLOCK) on DT.DirType_id = ED.DirType_id
				left join v_LpuSectionProfile LSP with (NOLOCK) on LSP.LpuSectionProfile_id = ED.LpuSectionProfile_id
				left join v_Diag Diag with (NOLOCK) on Diag.Diag_id = ED.Diag_id
				left join v_EvnPS EvnPS with (NOLOCK) on EvnPS.EvnDirection_id = ED.EvnDirection_id
				-- end from
			where
				-- where
				ED.Lpu_did = :Lpu_id
				AND cast(ED.EvnDirection_setDate as DATE) <= :end_date
				AND cast(ED.EvnDirection_setDate as DATE) >= :beg_date
				and ED.DirFailType_id is null
				{$filters}
				-- end where
			order by 
				-- order by
				ED.EvnDirection_setDate DESC
				-- end order by
		";
		
		//echo getDebugSQL($query, $params);

		$result = $this->db->query(getLimitSQLPH($query, $data['start'], $data['limit']), $params);
		$result_count = $this->db->query(getCountSQLPH($query), $params);
		
		if (is_object($result_count))
		{
			$cnt_arr = $result_count->result('array');
			$count = $cnt_arr[0]['cnt'];
			unset($cnt_arr);
		}
		else
		{
			$count = 0;
		}
		if (is_object($result))
		{
			$response = array();
			$response['data'] = $result->result('array');
			$response['totalCount'] = $count;
			return $response;
		}
		else
		{
			return false;
		}
	}


	/**
	*	Выборка списка направлений для журнала направлений
	*/
	function loadEvnDirectionJournal($data) {
		$params = array(
			 'beg_date' => $data['beg_date']
			,'end_date' => $data['end_date']
			,'Lpu_id' => $data['Lpu_id']
		);
		$filter = '';
		$filter_ps = '';
		$addQuery = "";
		$archive_database_enable = $this->config->item('archive_database_enable');
		if (!empty($archive_database_enable)) {
			$addQuery .= "
				, case when ISNULL(Evn.Evn_IsArchive, 1) = 1 then 0 else 1 end as archiveRecord
			";

			if (empty($_REQUEST['useArchive'])) {
				// только актуальные
				$filter .= " and ISNULL(Evn.Evn_IsArchive, 1) = 1";
			} elseif (!empty($_REQUEST['useArchive']) && $_REQUEST['useArchive'] == 1) {
				// только архивные
				//$data['start'] = $data['start'] - $data['archiveStart'];
				$filter .= " and ISNULL(Evn.Evn_IsArchive, 1) = 2";
			} else {
				// все из архивной
				$filter .= "";
			}
		}

		$filter .= ($data['isCanceled'] == true) ? " and Evn.EvnStatus_id in (12,13) and ED.EvnDirection_failDT is not null":
				" and Evn.EvnStatus_id not in (12,13) and ED.EvnDirection_failDT is null";

		if ( !empty($data['EvnStatusCause_id']) ) {
			$query = "
					select top 1 
						escl.DirFailType_id 
					from v_EvnStatusCauseLink escl with (nolock) 
					where escl.EvnStatusCause_id = :EvnStatusCause_id";
			$result = $this->db->query($query, array('EvnStatusCause_id' => $data['EvnStatusCause_id']))->result('array');
			$filter .= ' and ED.DirFailType_id = :DirFailType_id';
			$params['DirFailType_id'] = $result[0]['DirFailType_id'];
		}
		if ( !empty($data['DirType_id']) ) {
			$filter .= ' and ED.DirType_id = :DirType_id';
			$params['DirType_id'] = $data['DirType_id'];
		}
		
		if ( !empty($data['LpuSectionProfile_id']) ) {
			$filter .= ' and ED.LpuSectionProfile_id = :LpuSectionProfile_id';
			$params['LpuSectionProfile_id'] = $data['LpuSectionProfile_id'];
		}

		if ( !empty($data['Lpu_sid']) ) {
			$filter .= ' and Lpu.Lpu_id = :Lpu_sid';
			$params['Lpu_sid'] = $data['Lpu_sid'];
		}

		if ( !empty($data['MedPersonalProfile_id']) ) {
			$filter .= ' and MedPersLSP.LpuSectionProfile_id = :MedPersonalProfile_id';
			$params['MedPersonalProfile_id'] = $data['MedPersonalProfile_id'];
		}

		if ( !empty($data['Diag_id']) ) {
			$filter .= ' and ED.Diag_id = :Diag_id';
			$params['Diag_id'] = $data['Diag_id'];
		}

		if ( !empty($data['EvnPS_setDate']) ) {
			$filter .= ' and ( cast(EvnPS.EvnPS_setDate as date) = cast(:EvnPS_setDate as date) and EvnPS.PrehospStatus_id = 4 )';
			$params['EvnPS_setDate'] = $data['EvnPS_setDate'];
		}

		if ( !empty($data['LeaveType_id']) ) {
			$filter .= ' and LT.LeaveType_id = :LeaveType_id';
			$params['LeaveType_id'] = $data['LeaveType_id'];
		}

		if ( !empty($data['PrehospWaifRefuseCause_id']) ) {
			$filter .= ' and PWRC.PrehospWaifRefuseCause_id = :PrehospWaifRefuseCause_id';
			$params['PrehospWaifRefuseCause_id'] = $data['PrehospWaifRefuseCause_id'];
		}

		$join = "";
		$isSearchByEncryp = false;
		$selectPersonData = "
			convert(varchar(10), PS.Person_Birthday, 104) as Person_Birthday,
			PS.Person_Surname + ' '
				+ case when PS.Person_Firname is not null then SUBSTRING(PS.Person_Firname,1,1) +'.' else '' end
				+ case when PS.Person_Secname is not null then SUBSTRING(PS.Person_Secname,1,1) +'.' else '' end
			as Person_Fio,
		";
		if (isEncrypHIVRegion($this->regionNick)) {
			if (allowPersonEncrypHIV($data['session'])) {
				$isSearchByEncryp = isSearchByPersonEncrypHIV($data['Person_SurName']);
				$join .= " left join v_PersonEncrypHIV peh with(nolock) on peh.Person_id = Evn.Person_id";
				$selectPersonData = "
					case when peh.PersonEncrypHIV_Encryp is null then convert(varchar(10), PS.Person_Birthday, 104) end as Person_Birthday,
					case when peh.PersonEncrypHIV_Encryp is null 
						then PS.Person_Surname +' '+ SUBSTRING(PS.Person_Firname,1,1) +'.'+ SUBSTRING(PS.Person_Secname,1,1) +'.'
						else rtrim(peh.PersonEncrypHIV_Encryp) end 
					as Person_Fio,";
			} else {
				//Не отображать анонимных шифрованных пациентов
				$filter .= " and not exists(
					select top 1 peh.PersonEncrypHIV_Encryp
					from v_PersonEncrypHIV peh with(nolock)
					inner join v_EncrypHIVTerr eht with(nolock) on eht.EncrypHIVTerr_id = peh.EncrypHIVTerr_id
						and isnull(eht.EncrypHIVTerr_Code,0) = 20
					where peh.Person_id = ps.Person_id
				)";
			}
		}
		
		if ( !empty($data['Person_SurName']) ) {
			if (allowPersonEncrypHIV($data['session']) && $isSearchByEncryp) {
				$filter .= " and peh.PersonEncrypHIV_Encryp like :Person_SurName";
			} else {
				$filter_ps .= " and PS.Person_Surname like :Person_SurName";
			}
			$params['Person_SurName'] = $data['Person_SurName'] . '%';
		}

		if ( !empty($data['Person_FirName']) ) {
			$filter_ps .= " and PS.Person_FirName like :Person_FirName";
			$params['Person_FirName'] = $data['Person_FirName'] . '%';
		}

		if ( !empty($data['Person_SecName']) ) {
			$filter_ps .= " and PS.Person_SecName like :Person_SecName";
			$params['Person_SecName'] = $data['Person_SecName'] . '%';
		}

		if ( !empty($data['Person_BirthDay']) ) {
			$filter_ps .= ' and cast(PS.Person_BirthDay as date) = cast(:Person_BirthDay as date)';
			$params['Person_BirthDay'] = $data['Person_BirthDay'];
		}

		if ( !empty($data['Person_INN']) ) {
			$filter_ps .= ' and PS.Person_INN = :Person_INN';
			$params['Person_INN'] = $data['Person_INN'];
		}

		if ( !empty($data['IsConfirmed']) ) {
			$filter .= ' and ISNULL(ED.EvnDirection_IsConfirmed, 1) = :IsConfirmed';
			$params['IsConfirmed'] = $data['IsConfirmed'];
		}

		$diagFilter = getAccessRightsDiagFilter('Diag.Diag_Code');
		if (!empty($diagFilter)) {
			$filter .= " and $diagFilter";
		}
		$lpuFilter = getAccessRightsLpuFilter('Evn.Lpu_id');
		if (!empty($lpuFilter)) {
			$filter .= " and $lpuFilter";
		}

		if ($_SESSION['CurArmType'] == 'stacpriem')
		{
			$prehospSel = ",phs.PrehospStatus_id, phs.PrehospStatus_Name";

			$prehospFrom = "
					OUTER APPLY (SELECT TOP 1 *
						FROM v_EvnPS WITH (NOLOCK)
						WHERE EvnDirection_id = ED.EvnDirection_id
					) EPS
					LEFT JOIN v_PrehospStatus phs WITH (NOLOCK)
						ON EPS.PrehospStatus_id = phs.PrehospStatus_id";

			if (!empty($data['PrehospStatus_id']))
			{
				$prehospWhere = "AND phs.PrehospStatus_id = :PrehospStatus_id";
				$params['PrehospStatus_id'] = $data['PrehospStatus_id'];
			}
			else
				$prehospWhere = "";
		}
		else
			$prehospSel = $prehospFrom = $prehospWhere = "";

		$query = "
			--variables
			DECLARE @date date = dbo.tzGetDate();
			--end variables
			
			select
				-- select
				
				case when EvnPS.PrehospStatus_id = 4 then convert(varchar(10), EvnPS.EvnPS_setDate, 104) else null end as EvnPS_setDate,
				coalesce(LT.LeaveType_Name,PWRC.PrehospWaifRefuseCause_Name) as LeaveType_Name,
				RTRIM(MedPersLSP.LpuSectionProfile_Name) as MedPersonalProfile_Name,
				Evn.EvnStatus_id,

				ED.EvnDirection_id,
				ED.PayType_id,
				Lpu.Lpu_id,
				case when ED.EvnDirection_IsConfirmed = 2 then 'true' else 'false' end as IsConfirmed,
				Lpu.Org_id,
				Lpu.Org_Nick,
				Lpu.Org_Name,
				RTRIM(Lpu.Lpu_Nick) as Lpu_Name,
				ED.EvnDirection_Num,
				ED.DirType_id,
				DT.DirType_Code,
				DFT.DirFailType_id,
				DFT.DirFailType_Name,
				RTRIM(DT.DirType_Name) as DirType_Name,
				ED.LpuSectionProfile_id,
				RTRIM(LSP.LpuSectionProfile_Name) as LpuSectionProfile_Name,
				ED.LpuSection_did,
				RTRIM(LS.LpuSection_Name) as LpuSection_Name,
				{$selectPersonData}
				ED.Diag_id,
				RTRIM(Diag.Diag_FullName) as Diag_Name,
				ED.EvnDirection_Descr,
				convert(varchar(10), ED.EvnDirection_desDT, 104) as EvnDirection_desDT,
				ED.MedPersonal_id,
				ED.LpuSection_id,
				MP.Person_Fin as MedPersonal_Fio,
				Evn.Person_id,
				Evn.PersonEvn_id,
				Evn.Server_id,
				convert(varchar(10),  Evn.Evn_setDT, 104) as EvnDirection_setDate,
				isnull(convert(varchar(10), TTS.TimetableStac_setDate, 104),'Очередь') as TimetableStac_setDate,
				left(cast(Evn.Evn_setDT as time),5) as EvnDirection_setTime,
				case
					when
						TTS.TimetableStac_setDate is not null
					then
						null
					else
						datediff(day, EQ.EvnQueue_setDT, @date)
				end as EvnQueue_Days
				{$addQuery}
				{$prehospSel}
				-- end select
			from 
				-- from
				EvnDirection ED with (NOLOCK)
				inner join Evn Evn with (NOLOCK) on Evn.Evn_id = ED.EvnDirection_id and Evn.Evn_deleted = 1
				inner join v_Lpu Lpu with (NOLOCK) on Lpu.Lpu_id = isnull(ED.Lpu_sid, Evn.Lpu_id)
				inner join v_DirType DT with (NOLOCK) on DT.DirType_id = ED.DirType_id and DT.DirType_Code in (1,4,5,6)
				inner join v_PersonState PS with (NOLOCK) on Evn.Person_id = PS.Person_id {$filter_ps}
				outer apply (
					select top 1
						EQ.EvnQueue_id,
						EQ.EvnQueue_setDT
					from 
						v_EvnQueue EQ (nolock)
					where EQ.EvnDirection_id = ED.EvnDirection_id
						and EQ.EvnQueue_recDT is null
						and EQ.EvnQueue_failDT is null
						and EQ.EvnQueue_IsArchived is null
				) EQ
				left join v_MedPersonal MP with (NOLOCK) on ED.MedPersonal_id = MP.MedPersonal_id AND Lpu.Lpu_id = MP.Lpu_id
				left join v_LpuSectionProfile LSP with (NOLOCK) on LSP.LpuSectionProfile_id = ED.LpuSectionProfile_id
				left join v_Diag Diag with (NOLOCK) on Diag.Diag_id = ED.Diag_id
				left join v_LpuSection LS with (nolock) on ED.LpuSection_did = LS.LpuSection_id
				left join v_TimetableStac_lite TTS with (nolock) on TTS.EvnDirection_id = ED.EvnDirection_id

				left join v_EvnPS EvnPS with (nolock) on EvnPS.EvnDirection_id = ED.EvnDirection_id
				left join v_MedStaffFact MSF with (nolock) on MSF.MedStaffFact_id = ED.MedStaffFact_id
				left join v_LpuSectionProfile MedPersLSP with (nolock) on MedPersLSP.LpuSectionProfile_id = MSF.LpuSectionProfile_id
				left join v_LeaveType LT with (nolock) on EvnPS.LeaveType_id = LT.LeaveType_id
				left join v_PrehospWaifRefuseCause PWRC with (nolock) on PWRC.PrehospWaifRefuseCause_id = EvnPS.PrehospWaifRefuseCause_id
				left join v_DirFailType DFT with (nolock) on DFT.DirFailType_id = ED.DirFailType_id
				{$join}
				{$prehospFrom}
				-- end from
			where
				-- where
				ED.Lpu_did = :Lpu_id
				and cast(Evn.Evn_setDT as DATE) <= :end_date
				and cast(Evn.Evn_setDT as DATE) >= :beg_date

				--and not exists (select top 1 EvnPS.EvnDirection_id from v_EvnPS EvnPS with (NOLOCK) where EvnPS.EvnDirection_id = ED.EvnDirection_id)

				{$filter}
				{$prehospWhere}
				-- end where
			order by 
				-- order by
				Evn.Evn_setDT DESC
				-- end order by
		";

		return $this->getPagingResponse($query, $params, $data['start'], $data['limit'], true);
	}

	/**
	 * Удаление направления
	 *
	 * @param $data
	 * @return array
	 */
	function deleteEvnDirection($data) {
		$queryParams = array(
			'EvnDirection_id' => $data['EvnDirection_id'],
			'pmUser_id' => $data['pmUser_id']
		);
		
		if ( ($resp = $this->execCommonSP("p_EvnDirection_del", $queryParams)) ) {
			return $resp;
		} else {
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (удаление направления)'));
		}
	}


	/**
	 * @param $data
	 * @return bool
	 */
	public function getEvnDirectionNumber($data, $tryCount = 0) {
		$query = "
			declare @EvnDirection_Num bigint;
			exec xp_GenpmID @ObjectName = 'EvnDirection', @Lpu_id = :Lpu_id, @ObjectID = @EvnDirection_Num output;
			select @EvnDirection_Num as EvnDirection_Num;
		";
		$result = $this->db->query($query, array('Lpu_id' => $data['Lpu_id']));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}
	
	/**
	 * Получение кол-ва посещений к определенному врачу определенного отделения на определенную дату
	 */
	function countEvnVizitPL($data) {
		
		$filter = "(1=1) ";
		$params = array();
		$join = "";
		if ($data['session']['medpersonal_id']>0)
		{
			$filter .="and EvnVizitPL.MedPersonal_id = :MedPersonal_id ";
			$params['MedPersonal_id'] = $data['session']['medpersonal_id'];
		}
		elseif ($data['MedStaffFact_id']>0)
		{
			$filter .="and MSF.MedStaffFact_id = :MedStaffFact_id ";
			$params['MedStaffFact_id'] = $data['MedStaffFact_id'];
			$join = "inner join v_MedStaffFact MSF with (NOLOCK) on MSF.MedPersonal_id = EvnVizitPL.MedPersonal_id and MSF.LpuSection_id = EvnVizitPL.LpuSection_id";
		}
		else 
		{
			return -1;
		}
		if ($data['LpuSection_id']>0)
		{
			$filter .="and EvnVizitPL.LpuSection_id = :LpuSection_id ";
			$params['LpuSection_id'] = $data['LpuSection_id'];
		}
		else 
		{
			return -1;
		}
		
		if ($data['setDate']>0)
		{
			$filter .="and EvnVizitPL.EvnVizitPL_setDate = :setDate ";
			$params['setDate'] = $data['setDate'];
		}
		else 
		{
			return -1;
		}
		
		if ($data['Person_id']>0)
		{
			$filter .="and EvnVizitPL.Person_id = :Person_id ";
			$params['Person_id'] = $data['Person_id'];
		}
		else 
		{
			return -1;
		}
		if ($data['EvnVizitPL_id']>0)
		{
			$filter .="and EvnVizitPL.EvnVizitPL_id != :EvnVizitPL_id ";
			$params['EvnVizitPL_id'] = $data['EvnVizitPL_id'];
		}
		
		$query = "
			Select 
				count(EvnVizitPL_id) as [kolvo]
			from v_EvnVizitPL EvnVizitPL with (NOLOCK)
			where {$filter};
		";
		$result = $this->db->query($query, $params);

		if ( is_object($result) ) 
		{
			$res = $result->result('array');
			if ((count($res)>0) && ($res[0]['kolvo']))
			{
				return $res[0]['kolvo'];
			}
		}
		else 
		{
			return -1;
		}
	}

	/**
	 * Проверить связь рабочего места с ЭО
	 */
	function checkEQMedStaffFactLink($data) {
		$query = "
			SELECT top 1 *
			FROM 
				MedServiceElectronicQueue
			WHERE 
				MedStaffFact_id = :MedStaffFact_id 
		";
		$queryParams = array(
			'MedStaffFact_id' => $data['session']['CurMedStaffFact_id']
		);
		$response = $this->getFirstResultFromQuery($query, $queryParams);
		return !empty($response);
	}

	/**
	 *  Проверка связи онкологического диагноза с результатом диагностики
	 */
	function checkOnkoDiagforDiagnosisResult($data){
		
		if(getRegionNick() == 'krym'){
			$diag_code = $this->getFirstResultFromQuery("
				select top 1
					Diag_id
				from
					v_Diag (nolock)
				where 
					Diag_id = :Diag_id
					and (LEFT(Diag_Code,1)='C' or (Diag_Code >= 'D00' AND Diag_Code <= 'D09'))
			", array('Diag_id' => $data['Diag_id']));

			if(!empty($diag_code)) {

				$diag_attrib_dict = $this->getFirstResultFromQuery("
					select
						dad.DiagAttribType_id
					from
						v_DiagAttribDict dad with (nolock)
						inner join v_DiagResult dr on dr.DiagAttribDict_id = dad.DiagAttribDict_id
						inner join v_DiagnosisResultDiagLink drdl on drdl.DiagResult_id = dr.DiagResult_id
						inner join v_Diag diag ON diag.Diag_id = drdl.Diag_id
					where
						diag.Diag_id = :Diag_id 
						and :EvnDirection_setDate between isnull(dad.DiagAttribDict_begDT, :EvnDirection_setDate) and isnull(dad.DiagAttribDict_endDT, :EvnDirection_setDate)
						and :EvnDirection_setDate between isnull(drdl.DiagnosisResultDiagLink_begDate, :EvnDirection_setDate) and isnull(drdl.DiagnosisResultDiagLink_endDate, :EvnDirection_setDate)
				", $data);

				if (empty($diag_attrib_dict)) {
					return array(
						'Error_Msg' => '',
						'ho_warning' => "Выбранный  диагноз не соответствует онкологическому маркеру. Укажите корректный диагноз. Продолжить сохранение?"
					);
				}

			}

			return array('Error_Msg' => '');

		}

	}
	
	/**
	 * Получение кол-ва посещений к определенному врачу определенного отделения на определенную дату
	 */
	function countEvnVizitPLStom($data) {
		
		$filter = "(1=1) ";
		$params = array();
		$join = "";
		if ($data['session']['medpersonal_id']>0)
		{
			$filter .="and EvnVizitPLStom.MedPersonal_id = :MedPersonal_id ";
			$params['MedPersonal_id'] = $data['session']['medpersonal_id'];
		}
		elseif ($data['MedStaffFact_id']>0)
		{
			$filter .="and MSF.MedStaffFact_id = :MedStaffFact_id ";
			$params['MedStaffFact_id'] = $data['MedStaffFact_id'];
			$join = "inner join v_MedStaffFact MSF with (NOLOCK) on MSF.MedPersonal_id = EvnVizitPLStom.MedPersonal_id and MSF.LpuSection_id = EvnVizitPLStom.LpuSection_id";
		}
		else 
		{
			return -1;
		}
		if ($data['LpuSection_id']>0)
		{
			$filter .="and EvnVizitPLStom.LpuSection_id = :LpuSection_id ";
			$params['LpuSection_id'] = $data['LpuSection_id'];
		}
		else 
		{
			return -1;
		}
		
		if ($data['setDate']>0)
		{
			$filter .="and EvnVizitPLStom.EvnVizitPLStom_setDate = :setDate ";
			$params['setDate'] = $data['setDate'];
		}
		else 
		{
			return -1;
		}
		
		if ($data['Person_id']>0)
		{
			$filter .="and EvnVizitPLStom.Person_id = :Person_id ";
			$params['Person_id'] = $data['Person_id'];
		}
		else 
		{
			return -1;
		}
		if ($data['EvnVizitPLStom_id']>0)
		{
			$filter .="and EvnVizitPLStom.EvnVizitPLStom_id != :EvnVizitPLStom_id ";
			$params['EvnVizitPLStom_id'] = $data['EvnVizitPLStom_id'];
		}
		
		$query = "
			Select 
				count(EvnVizitPLStom_id) as [kolvo]
			from v_EvnVizitPLStom EvnVizitPLStom with (NOLOCK)
			where {$filter};
		";
		$result = $this->db->query($query, $params);

		if ( is_object($result) ) 
		{
			$res = $result->result('array');
			if ((count($res)>0) && ($res[0]['kolvo']))
			{
				return $res[0]['kolvo'];
			}
		}
		else 
		{
			return -1;
		}
	}

	/**
	 * @param array $data
	 * @return array|bool
	 */
	function loadBaseJournal($data){
		$queryParams = array();
		$filters = '';

		$join = '';
		$before_join_lpu_s = 'left';
		$filters_lpu_s = '';
		$before_join_lpu_d = 'left';
		$filters_lpu_d = '';
		$join_diag = 'left join v_Diag Diag with(nolock) on ED.Diag_id = Diag.Diag_id';
		$joinEDLforKz = '';
		$ttg_filters = '';
		$tts_filters = '';
		$ttms_filters = '';
		$ttr_filters = '';
		$eq_filters = '';
		$emergency_filters = '';
		$addFields = '';
		$addSelectFields = '';

		$person_filters = array('ED.Person_id = PS.Person_id');

		if (isset($data['Person_Birthday'])) {
			$person_filters[] = 'PS.Person_Birthday=:Person_Birthday';
			$queryParams['Person_Birthday']=$data['Person_Birthday'];
		}
		if (isset($data['MedPersonal_did'])) {
			$filters .= ' and ED.MedPersonal_did=:MedPersonal_did';
			$queryParams['MedPersonal_did']=$data['MedPersonal_did'];
		}
		if (isset($data['MedService_did'])) {
			$filters .= ' and ED.MedService_id=:MedService_did';
			$queryParams['MedService_did']=$data['MedService_did'];
		}
		if (isset($data['LpuSection_did'])) {
			$filters .= ' and ED.LpuSection_did=:LpuSection_did';
			$queryParams['LpuSection_did']=$data['LpuSection_did'];
		}
		if (isset($data['LpuUnit_did'])) {
			$filters .= ' and ED.LpuUnit_did=:LpuUnit_did';
			$queryParams['LpuUnit_did']=$data['LpuUnit_did'];
		}

		if (isset($data['MedPersonal_sid'])) {
			$filters .= ' and ED.MedPersonal_id=:MedPersonal_sid';
			$queryParams['MedPersonal_sid']=$data['MedPersonal_sid'];
		}
		if (isset($data['LpuSection_sid'])) {
			$filters .= ' and ED.LpuSection_sid=:LpuSection_sid';
			$queryParams['LpuSection_sid']=$data['LpuSection_sid'];
		}
		if (isset($data['Lpu_sid'])) { // todo: По идее этот фильтр не нужен, т.к. отфильтруется по outcoming, но пусть будет
			$filters .= ' and ED.Lpu_sid = :Lpu_sid';
			$queryParams['Lpu_sid']=$data['Lpu_sid'];
		}
		if (isset($data['EvnDirection_Num'])) {
			$filters .= ' and ED.EvnDirection_Num=:EvnDirection_Num';
			$queryParams['EvnDirection_Num']=$data['EvnDirection_Num'];
		}
		if (!empty($data['pmUser_id'])) {
			$filters .= ' and pmi.pmUser_id=:pmUser_id';
			$queryParams['pmUser_id'] = $data['pmUser_id'];
		}

		if (!empty($data['KlDistrict_sid']) && $this->getRegionNick() =='ekb') {
			$join .= "
				inner join r66.v_KLDistrictLpu KLDL with (nolock) on KLDL.Lpu_id = ED.Lpu_sid and KLDL.KLDistrict_id = :KlDistrict_sid
			";

			$queryParams['KlDistrict_sid']=$data['KlDistrict_sid'];
		}
		if (isset($data['Org_sid'])) {
			$filters .= ' and ED.Org_sid=:Org_sid';
			$queryParams['Org_sid']=$data['Org_sid'];
		}
		if (!empty($data['IsHospitalized']) ) {
			if ($data['IsHospitalized'] == 2) {
				$filters .= ' and exists(
					select top 1 EvnPS.EvnPS_id from v_EvnPS EvnPS with (nolock) where EvnPS.EvnDirection_id = ED.EvnDirection_id
				)';
			} else {
				$filters .= ' and not exists(
					select top 1 EvnPS.EvnPS_id from v_EvnPS EvnPS with (nolock) where EvnPS.EvnDirection_id = ED.EvnDirection_id
				)';
			}
		}
		if (isset($data['PayType_id'])) {
			$filters .= ' and ED.PayType_id=:PayType_id';
			$queryParams['PayType_id']=$data['PayType_id'];
		}
		if (!empty($data['IsConfirmed']))
		{
			if ($data['IsConfirmed'] == 1)
			{
				$filters .= ' AND ISNULL(ED.EvnDirection_IsConfirmed,1) = 1';
			}
			else
			{
				$filters .= ' AND ED.EvnDirection_IsConfirmed = 2';
			}
		}
		if (empty($data['useCase'])) { $data['useCase'] = ''; }
		if ('record_from_queue' == $data['useCase']) {
			$data['EvnStatus_id'] = 10;
			$filters .= ' and isnull(ED.DirType_id,1) in (3, 16)';
		} else {
			$filters .= ' and isnull(ED.DirType_id,1) not in (7, 18, 19, 20)';
		}

		if (isset($data['Diag_id'])) {
			$filters .= ' and ED.Diag_id=:Diag_id';
			$queryParams['Diag_id']=$data['Diag_id'];
		}
		if (!empty($data['UslugaComplex_id'])) {
			$filters .= ' and EUP.UslugaComplex_id=:UslugaComplex_id';
			$queryParams['UslugaComplex_id'] = $data['UslugaComplex_id'];
		}

		$need_canceled = '';
		$EvnStatus_ids = array();
		if (isset($data['EvnStatus_id'])) {
			if (mb_strpos($data['EvnStatus_id'], ',') !== false) {
				$EvnStatus_ids = explode(',', $data['EvnStatus_id']);
				$filters .= ' and ED.EvnStatus_id IN ('.implode($EvnStatus_ids, ',').')';
			} else {
				$EvnStatus_ids = array($data['EvnStatus_id']);
				$filters .= ' and ED.EvnStatus_id=:EvnStatus_id';
				$queryParams['EvnStatus_id']=$data['EvnStatus_id'];
			}
		}
		if (in_array($data['winType'], array('reg','call')) && $data['loadAddFields'] == 0) {
			$filters .= ' and ED.EvnStatus_id not in (12, 13, 15)'; // обслуженые, отменённые для АРМов регистратора и колцентра не нужны.
		} else if (/*in_array($data['winType'], array('queue'))*/
			0 == count($EvnStatus_ids) 
			|| in_array(12, $EvnStatus_ids) 
			|| in_array(13, $EvnStatus_ids)
		) {
			// В журнале направлений отменённые / отклонённые направления должны отображаться
			$need_canceled = '
					UNION ALL
					select
						null as EvnQueue_id,
						ED.LpuSectionProfile_id as LpuSectionProfile_did,
						null as TimetableGraf_id,
						null as TimetableMedService_id,
						null as TimetableResource_id,
						null as TimetableStac_id,
						null as EvnQueue_setDT,
						ED.pmUser_insID,
						ED.pmUser_updID,
						null as recDate,
						null as recSort,
						null as MedPersonal_Fio
					where ED.EvnStatus_id IN (12,13)';
		}

		if (isset($data['EvnDirection_IsAuto'])) {
			$filters .= ' and ED.EvnDirection_IsAuto=:EvnDirection_IsAuto';
			$queryParams['EvnDirection_IsAuto']=$data['EvnDirection_IsAuto'];
		}
		if (isset($data['eQueueOnly'])) {
			$filters .= ' and (ED.EvnDirection_IsAuto is null or ED.EvnDirection_IsAuto = 1)';
		}

		if (!in_array("15", $EvnStatus_ids) && !empty($EvnStatus_ids)) {
			$ttg_filters .= ' and Timetable.TimeTableGraf_factTime is null';
		}

		if (isset($data['DirType_id'])) {
			if (mb_strpos($data['DirType_id'], ',') !== false) {
				$DirType_ids = explode(',', $data['DirType_id']);
				$filters .= ' and ED.DirType_id IN ('.implode($DirType_ids, ',').')';
			} else {
				$filters .= ' and ED.DirType_id=:DirType_id';
				$queryParams['DirType_id'] = $data['DirType_id'];
			}
		}
		if (isset($data['Person_id'])) {
			$filters .= ' and ED.Person_id=:Person_id';
			$queryParams['Person_id']=$data['Person_id'];
		}
		$join_peh = '';
		$isSearchByEncryp = false;
		$selectPersonData = ",1 as accessType
			,convert(varchar(10), PS.Person_BirthDay, 104) as Person_BirthDay
				,ps.Person_SurName+ISNULL(' '+ps.Person_FirName,'')+ISNULL(' '+ps.Person_SecName,'') as Person_Fio
				,[dbo].[getPersonPhones](PS.Person_id, '<br />') as Person_Phone
				,ISNULL(Uaddress.Address_Nick, UAddress.Address_Address) as Address_Address";
		if (isEncrypHIVRegion($this->regionNick)) {
			if (allowPersonEncrypHIV($data['session'])) {
				$isSearchByEncryp = isSearchByPersonEncrypHIV($data['Person_SurName']);
				$join_peh = "left join v_PersonEncrypHIV peh with(nolock) on peh.Person_id = ED.Person_id";
				$selectPersonData = ",case when peh.PersonEncrypHIV_Encryp is null 
						then 1
						else 0 end 
					as accessType
					,case when peh.PersonEncrypHIV_Encryp is null 
						then ps.Person_SurName+ISNULL(' '+ps.Person_FirName,'')+ISNULL(' '+ps.Person_SecName,'') 
						else rtrim(peh.PersonEncrypHIV_Encryp) end 
					as Person_Fio
					,case when peh.PersonEncrypHIV_Encryp is null then convert(varchar(10), PS.Person_BirthDay, 104) end as Person_BirthDay
					,case when peh.PersonEncrypHIV_Encryp is null then [dbo].[getPersonPhones](PS.Person_id, '<br />') end as Person_Phone
					,case when peh.PersonEncrypHIV_Encryp is null then ISNULL(Uaddress.Address_Nick, UAddress.Address_Address) end as Address_Address";
			} else {
				//Не отображать анонимных шифрованных пациентов
				$person_filters[] = "not exists(
					select top 1 peh.PersonEncrypHIV_Encryp
					from v_PersonEncrypHIV peh with(nolock)
					inner join v_EncrypHIVTerr eht with(nolock) on eht.EncrypHIVTerr_id = peh.EncrypHIVTerr_id
						and isnull(eht.EncrypHIVTerr_Code,0) = 20
					where peh.Person_id = ps.Person_id
				)";
			}
		}
		
		if ( !empty($data['Person_SurName']) ) {
			if (allowPersonEncrypHIV($data['session']) && $isSearchByEncryp) {
				$join_peh = "inner join v_PersonEncrypHIV peh with(nolock) on peh.Person_id = ED.Person_id and peh.PersonEncrypHIV_Encryp like :Person_SurName";
			} else {
				$person_filters[] = "PS.Person_SurName like :Person_SurName + '%'";
			}
			$queryParams['Person_SurName']=$data['Person_SurName'];
		}

		if ( !empty($data['Person_FirName']) ) {
			$person_filters[] = "PS.Person_FirName like :Person_FirName + '%'";
			$queryParams['Person_FirName']=$data['Person_FirName'];
		}

		if ( !empty($data['Person_SecName']) ) {
			$person_filters[] = "PS.Person_SecName like :Person_SecName + '%'";
			$queryParams['Person_SecName']=$data['Person_SecName'];
		}

		$this->load->helper('Reg');
		 // Это не надо, форма должна возвращать либо входящие либо исходящие, т.к. вкладки только две и их нельзя выбрать одновременно
		/*
		if ( !isSuperadmin() && !IsCZUser() ) {
			$filters .= ' and (Lpu.Lpu_id = :Lpu_id OR DLpu.Lpu_id = :Lpu_id)';
			$queryParams['Lpu_id'] = $data['Lpu_id'];
		}
		*/
		
		// Если "Куда направлен" Lpu_did указано // todo: По идее этот фильтр не нужен, т.к. отфильтруется по incoming, но пусть будет
		if (isset($data['Lpu_did'])) {
			$filters .= " and ED.Lpu_did = :Lpu_did";
			$queryParams['Lpu_did']=$data['Lpu_did'];
		}
		
		if (!empty($data['Lpu_id'])) {
			$queryParams['Lpu_id'] = $data['Lpu_id'];
			
			// Просто затычка на случай если SearchType не указан, хотя такого не должно быть
			if (empty($data['SearchType'])) {
				if (isset($data['Lpu_did'])) {
					$data['SearchType'] = 'incoming';
				} elseif (isset($data['Lpu_sid'])) {
					$data['SearchType'] = 'outcoming';
				} else { // если все равно SearchType не указан то ограничим
					if ( !isSuperadmin() && !IsCZUser() ) {
						$filters .= ' and (Lpu.Lpu_id = :Lpu_id OR DLpu.Lpu_id = :Lpu_id)';
					}
				}
			}
			if (!empty($data['SearchType'])) {
				if ($data['SearchType'] == 'outcoming') {
					// исходящие
					$before_join_lpu_s = 'inner';
					$filters_lpu_s .= " and Lpu.Lpu_id = :Lpu_id";
				} else {
					// входящие
					$before_join_lpu_d = 'inner';
					$filters_lpu_d .= " and DLpu.Lpu_id = :Lpu_id";
				}
			}
		}
		
		if (false === in_array($data['winType'], array('reg','call'))
			&& $data['dateRangeMode'] != 'allTime'
			&& empty($data['beg_date'])
		) {
			throw new Exception('Поле Дата начала периода обязательно для заполнения.');
		}
		if (false === in_array($data['winType'], array('reg','call'))
			&& $data['dateRangeMode'] != 'allTime'
			&& empty($data['end_date'])
		) {
			throw new Exception('Поле Дата конца периода обязательно для заполнения.');
		}
		if (in_array($data['winType'], array('reg','call'))
			&& empty($data['Person_id'])
		) {
			throw new Exception('Поле Идентификатор человека обязательно для заполнения.');
		}
		
		if (isset($data['beg_date'])) {
			if(getRegionNick() == 'kz') {
				$filters .= ' and cast(ISNULL(ED.EvnDirection_setDate, ED.EvnDirection_updDT) as date) >= :beg_date';
			} else {
				$filters .= ' and cast(ISNULL(ED.EvnDirection_statusDate, ED.EvnDirection_updDT) as date) >= :beg_date';
			}
			$queryParams['beg_date']=$data['beg_date'];
		}
		if (isset($data['end_date'])) {
			if(getRegionNick() == 'kz') {
				$filters .= ' and cast(ISNULL(ED.EvnDirection_setDate, ED.EvnDirection_updDT) as date) <= :end_date';
			} else {
				$filters .= ' and cast(ISNULL(ED.EvnDirection_statusDate, ED.EvnDirection_updDT) as date) <= :end_date';
			}
			$queryParams['end_date']=$data['end_date'];
		}
		if (isset($data['RecordDate_from'])) {
			$filters .= ' and cast(ED.EvnDirection_insDT as date) >= :RecordDate_from';
			$tts_filters .= ' and cast(TimeTableStac_setDate as date) >= :RecordDate_from';
			$eq_filters .= " and cast(ED.EvnDirection_insDT as date) >= :RecordDate_from";
			$queryParams['RecordDate_from']=$data['RecordDate_from'];
		}
		if (isset($data['RecordDate_to'])) {
			$filters .= ' and cast(ED.EvnDirection_insDT as date) <= :RecordDate_to';
			$tts_filters .= ' and cast(TimeTableStac_setDate as date) >= :RecordDate_to';
			$eq_filters .= " and cast(ED.EvnDirection_insDT as date) <= :RecordDate_to";
			$queryParams['RecordDate_to']=$data['RecordDate_to'];
		}
		if (isset($data['VizitDate_from'])) {
			$filters .= " and cast(TimeTableGraf_factTime as date) >= :VizitDate_from";
			$queryParams['VizitDate_from']=$data['VizitDate_from'];
		}
		if (isset($data['VizitDate_to'])) {
			$filters .= " and cast(TimeTableGraf_factTime as date) <= :VizitDate_to";
			$queryParams['VizitDate_to']=$data['VizitDate_to'];
		}

		if (isset($data['LpuSectionProfile_did'])) {
			$ttg_filters .= ' and ED.LpuSectionProfile_id = :LpuSectionProfile_did';
			$tts_filters .= ' and ED.LpuSectionProfile_id = :LpuSectionProfile_did';
			$ttms_filters .= ' and ED.LpuSectionProfile_id = :LpuSectionProfile_did';
			$ttr_filters .= ' and ED.LpuSectionProfile_id = :LpuSectionProfile_did';
			$eq_filters .= ' and EQ.LpuSectionProfile_did = :LpuSectionProfile_did';
			$emergency_filters .= ' and ED.LpuSectionProfile_id = :LpuSectionProfile_did';
			$queryParams['LpuSectionProfile_did']=$data['LpuSectionProfile_did'];
		}

		//режим просмотра истории
		if (!empty($data['loadAddFields']) && $data['loadAddFields'] == 1) {
			$addFields .= "
				,pm.PMUser_id as pmUser_id
				,rtrim(pm.PMUser_surName) +' '+upper(SUBSTRING ( pm.PMUser_firName , 1,1))+' '+upper(SUBSTRING ( pm.PMUser_secName , 1,1)) as pmUser_Name
			";
			$addSelectFields = "
				,pmUser_id
				,pmUser_Name";

			$join.='left join v_pmUserCache pm with (nolock) on pm.PMUser_id=TT.pmUser_updID';
		} else if (in_array($data['winType'], array('reg','call'))) {
			//если не режим просмотра истории и работа в АРМе то записи из прошлого не нужны
			$ttg_filters .= ' and ISNULL(cast(isnull(Timetable.TimetableGraf_begTime, Timetable.TimetableGraf_factTime) as DATE), @date) >= @date';
			$tts_filters .= ' and ISNULL(cast(Timetable.TimetableStac_setDate as DATE), @date) >= @date';
			$ttms_filters .= ' and ISNULL(cast(Timetable.TimetableMedService_begTime as DATE), @date) >= @date';
			$ttr_filters .= ' and ISNULL(cast(Timetable.TimetableResource_begTime as DATE), @date) >= @date';
		}

		if ( !isSuperadmin() ) {
			$diagFilter = getAccessRightsDiagFilter('Diag.Diag_Code');
			if (!empty($diagFilter)) {
				$join_diag = "cross apply (
						select top 1
							Diag.Diag_Code,
							Diag.Diag_Name
						from v_Diag Diag (nolock)
						/* фильтруем направления по диагнозам СЗЗ, если указан диагноз в направлении */
						where ED.Diag_id is not null AND ED.Diag_id = Diag.Diag_id and {$diagFilter} 
						UNION ALL
						select
							'' as Diag_Code,
							'' as Diag_Name
						where ED.Diag_id is null
				) Diag";
			}
			$lpuFilter = getAccessRightsLpuFilter('Lpu.Lpu_id');
			if (!empty($lpuFilter)) {
				$before_join_lpu_s = 'inner';
				$filters_lpu_s .= ' and ' . $lpuFilter;
			}
		}
		$userGroups = array();
		if (!empty($data['session']['groups']) && is_string($data['session']['groups'])) { //$_SESSION['groups']
			$userGroups = explode('|', $data['session']['groups']);
		}
		$isVisibleEvnXmlDirLink = (in_array('BlankDirection', $userGroups)
			//&& in_array($data['winType'], array('reg','queue'))
			//&& (in_array('RegUser', $userGroups) || in_array('RegAdmin', $userGroups))
		);
		if ($isVisibleEvnXmlDirLink) {
			$addFields .= '
				,EvnXmlDir.EvnXml_id as EvnXmlDir_id
				,EvnXmlDir.XmlType_id as EvnXmlDirType_id';
			/*
			Гиперссылка должна быть доступна для направлений (как на бирку, так и в очередь) на следующие типы служб
			•	Диагностика
			•	Центр удалённой консультации
			Чтобы гиперссылка была доступной, надо определить XmlType_id
			*/
			$join .= "
				outer apply (
					select top 1 EvnXml.EvnXml_id, XmlType.XmlType_id
					from XmlType with (nolock)
					left join EvnXml with (nolock) on EvnXml.XmlType_id = XmlType.XmlType_id and EvnXml.Evn_id = ED.EvnDirection_id
					where XmlType.XmlType_id = case
					when /* ЦУК */ 36 = MS.MedServiceType_id then 20
					when /* Диагностика */ 8 = MS.MedServiceType_id AND exists (
						select top 1 epd.EvnPrescr_id
						from v_EvnPrescrDirection epd with (nolock)
						inner join EvnPrescrFuncDiagUsluga EPFDU with (nolock) on EPFDU.EvnPrescrFuncDiag_id = epd.EvnPrescr_id
						inner join UslugaComplexAttribute uca (nolock) on uca.UslugaComplex_id = EPFDU.UslugaComplex_id
						inner join UslugaComplexAttributeType ucat (nolock) on ucat.UslugaComplexAttributeType_id = uca.UslugaComplexAttributeType_id
							and ucat.UslugaComplexAttributeType_SysNick like 'kt'
						where epd.EvnDirection_id = ED.EvnDirection_id
					) then 19
					when /* Диагностика */ 8 = MS.MedServiceType_id AND exists (
						select top 1 epd.EvnPrescr_id
						from v_EvnPrescrDirection epd with (nolock)
						inner join EvnPrescrFuncDiagUsluga EPFDU with (nolock) on EPFDU.EvnPrescrFuncDiag_id = epd.EvnPrescr_id
						inner join UslugaComplexAttribute uca (nolock) on uca.UslugaComplex_id = EPFDU.UslugaComplex_id
						inner join UslugaComplexAttributeType ucat (nolock) on ucat.UslugaComplexAttributeType_id = uca.UslugaComplexAttributeType_id
							and ucat.UslugaComplexAttributeType_SysNick like 'mrt'
						where epd.EvnDirection_id = ED.EvnDirection_id
					) then 18
					when /* Диагностика */ 8 = MS.MedServiceType_id then 2
					else null end
				) EvnXmlDir";
		}


		if (getRegionNick() == 'kz')
		{
			$addFields .= ' ,EDL.Referral_id';
			$joinEDLforKz = 'left join r101.v_EvnDirectionLink EDL with (nolock) on ED.EvnDirection_id = EDL.EvnDirection_id';
		}
		if ( ! empty($data['Referral_id']) && getRegionNick() == 'kz')
		{
			switch ($data['Referral_id'])
			{
				case 1:
					$filters .= " and EDL.Referral_id IS NULL";
					break;
				case 2:
					$filters .= " and EDL.Referral_id IS NOT NULL";
					break;
			}
		}

		$person_filters = implode(' AND ', $person_filters);

		if (!empty($data['onlyWaitingList'])) {

			$addFields .= "
				,eqs.EvnQueueStatus_Name
				,eqfc.QueueFailCause_Name
				,eq.EvnQueue_DeclineCount
			";

			$filters .= "
				and eq.RecMethodType_id in (1,2)
				and eq.EvnQueueStatus_id is not null
			";

			$join .= "
				inner join v_EvnQueue eq (nolock) on eq.EvnDirection_id = ED.EvnDirection_id
				left join v_EvnQueueStatus eqs (nolock) on eqs.EvnQueueStatus_id = eq.EvnQueueStatus_id
				left join v_QueueFailCause eqfc (nolock) on eqfc.QueueFailCause_id = eq.QueueFailCause_id
			";


		} else {
			$eq_filters .= " 
				and (EQ.EvnQueue_recDT is null or EQ.pmUser_recID = 1)
				and EQ.EvnQueue_failDT is null
			";
		}

		if (!empty($EvnStatus_ids) && in_array(10, $EvnStatus_ids)) {
			$addFields .= "
				,case when ed.MedStaffFact_id is null
			   		then 
			   			case when inQueueCounterProfile.counter > 0 
			   				then inQueueCounterProfile.counter 
			   				else null 
						end
					else 
						case when (inQueueCounterMSF.counter + inQueueCounterProfile.counter) > 0 
							then (inQueueCounterMSF.counter + inQueueCounterProfile.counter) 
							else null 
						end 
			   	end as inQueueCounter
			";

			$join .= "
				outer apply (
					select count(q_inner.EvnQueue_id) as counter
						from v_EvnQueue q_inner (nolock)
						inner join v_EvnDirection_all ed_inner (nolock) on ed_inner.EvnDirection_id = q_inner.EvnDirection_id
						where q_inner.EvnQueueStatus_id in (1,2)
							and q_inner.EvnQueue_failDT is null
							and q_inner.EvnQueue_id < (TT.EvnQueue_id + 1)
							and q_inner.RecMethodType_id = 1
							and q_inner.LpuSectionProfile_did = TT.LpuSectionProfile_did
							and ed_inner.MedStaffFact_id = ED.MedStaffFact_id
							and q_inner.Lpu_id = ED.Lpu_sid
							and ED.RecMethodType_id = 1
				) inQueueCounterMSF
				outer apply (
					select count(q_inner.EvnQueue_id) as counter
						from v_EvnQueue q_inner (nolock)
						inner join v_EvnDirection_all ed_inner (nolock) on ed_inner.EvnDirection_id = q_inner.EvnDirection_id
						where q_inner.EvnQueueStatus_id in (1,2)
							and q_inner.EvnQueue_failDT is null
							and q_inner.EvnQueue_id < (TT.EvnQueue_id + 1)
							and q_inner.RecMethodType_id = 1
							and q_inner.LpuSectionProfile_did = TT.LpuSectionProfile_did
							and ed_inner.MedStaffFact_id is null
							and q_inner.Lpu_id = ED.Lpu_sid
							and ED.RecMethodType_id = 1
				) inQueueCounterProfile
			";
		}

		$query="
			--variables
			DECLARE @date date = dbo.tzGetDate();
			DECLARE @time datetime = dbo.tzGetDate();
			--end variables
		
			 select
			-- select
				ED.Person_id
				,ISNULL(cast(et.ElectronicTalon_Num as varchar), cast(ED.EvnDirection_TalonCode as varchar)) as EvnDirection_TalonCode
				,ED.LpuUnit_did
				,MPD.Person_Fio + ' / ' + MPD.Dolgnost_Name as MedPersonalDid
				,ES.EvnStatus_SysNick
				,TT.recDate as EvnDirection_RecDate
				,convert(varchar(10), ED.EvnDirection_setDate, 104) as EvnDirection_setDate
				,EUP.UslugaComplex_id
				,EUP.UslugaComplex_Name
				,TT.EvnQueue_id
				,TT.TimetableMedService_id
				,TT.TimetableResource_id
				,TT.TimetableGraf_id
				,TT.TimetableStac_id
				,ED.EvnDirection_id
				,ISNULL(ED.EvnDirection_IsAuto, 1) as EvnDirection_IsAuto
				,ED.EvnDirection_Num
				,Lpu.Lpu_id as Lpu_sid
				,LSP.LpuSectionProfile_Name
				,TT.recSort
				,DLpu.Lpu_id as Lpu_did
				,DT.DirType_id
				,DT.DirType_Code
				,DT.DirType_Name
				,Diag.Diag_Code+' '+Diag.Diag_Name as Diag_Name
				{$selectPersonData}
				,convert(varchar(10),ED.EvnDirection_insDT,104) as EvnDirection_insDT
				,ES.EvnStatus_Name
				,case when isnull(ttf.RecClass_id,1) = 2 then '' when isnull(slot.Slot_id,0) > 0 then '' else (Lpu.Lpu_Nick + ISNULL(' / ' + LS.LpuSection_Name, '') + ISNULL(' / ' + MP.Person_Fio, '')) end as EvnDirection_From
				,DLpu.Lpu_Nick + ISNULL(' / ' + DLpuSection.LpuSection_Name, '') + ISNULL(' / ' + MS.MedService_Name, '') + ISNULL(' / ' + TT.MedPersonal_Fio, '')  as EvnDirection_To
				,case when isnull(ttf.RecClass_id,1) = 2 then 'Запись через интернет' else rtrim(isnull(pmi.pmUser_Name,'')) end as pmUser_Fio
				,ED.ARMType_id
				,RMT.RecMethodType_Name
				,TT.pmUser_updID
				,ED.MedPersonal_id
				,Ed.MedPersonal_did
				,case
					when
						TT.EvnQueue_id is not null
					then
						datediff(day, TT.EvnQueue_setDT, @date)
					else
						null
				end as EvnQueue_Days
				
				{$addFields}
			-- end select
			from
			-- from
				v_EvnDirection_all ED with (nolock)
				left join v_MedPersonal MPD on MPD.MedPersonal_id = ED.MedPersonal_did and MPD.Lpu_id = ED.Lpu_did
				left join RecMethodType RMT on RMT.RecMethodType_id = ED.RecMethodType_id
				inner join v_PersonState PS with(nolock) on {$person_filters}
				{$join_peh}
				inner join v_Lpu_all Lpu with (nolock) on Lpu.Lpu_id = ED.Lpu_sid {$filters_lpu_s} 
				--left join v_LpuUnit DLU with (nolock) on DLU.LpuUnit_id = ED.LpuUnit_did
				{$before_join_lpu_d} join v_Lpu_all DLpu with (nolock) on DLpu.Lpu_id = ED.Lpu_did {$filters_lpu_d} 
				{$joinEDLforKz}
				{$join_diag} 
				cross apply (
					select top 1 * from (
						select top 1 
							null as EvnQueue_id,
							ED.LpuSectionProfile_id as LpuSectionProfile_did,
							null as TimetableGraf_id,
							null as TimetableMedService_id,
							null as TimetableResource_id,
							Timetable.TimetableStac_id,
							null as EvnQueue_setDT,
							Timetable.pmUser_insID,
							Timetable.pmUser_updID,
							convert(varchar(10), Timetable.TimetableStac_setDate, 104) as recDate,
							null as recSort,
							null as MedPersonal_Fio
						from 
							v_TimetableStac_lite Timetable (nolock)
						where
							ED.DirType_id in (1,2,4,5,6)
							and Timetable.EvnDirection_id = ED.EvnDirection_id {$tts_filters}
						UNION ALL
						select top 1
							null as EvnQueue_id,
							ED.LpuSectionProfile_id as LpuSectionProfile_did,
							null as TimetableGraf_id,
							Timetable.TimetableMedService_id,
							null as TimetableResource_id,
							null as TimetableStac_id,
							null as EvnQueue_setDT,
							Timetable.pmUser_insID,
							Timetable.pmUser_updID,
							convert(varchar(10),Timetable.TimetableMedService_begTime,104) + ' ' + convert(varchar(5),Timetable.TimetableMedService_begTime,108) as recDate,
							Timetable.TimetableMedService_begTime as recSort,
							null as MedPersonal_Fio
						from 
							v_TimetableMedService_lite Timetable (nolock)
							left join v_UslugaComplexMedService ucms (nolock) on ucms.UslugaComplexMedService_id = Timetable.UslugaComplexMedService_id
						where
							ED.DirType_id in (2,3,6,10,11,15,25)
							and Timetable.EvnDirection_id = ED.EvnDirection_id {$ttms_filters}
						UNION ALL
						select top 1
							null as EvnQueue_id,
							ED.LpuSectionProfile_id as LpuSectionProfile_did,
							null as TimetableGraf_id,
							null as TimetableMedService_id,
							Timetable.TimetableResource_id,
							null as TimetableStac_id,
							null as EvnQueue_setDT,
							Timetable.pmUser_insID,
							Timetable.pmUser_updID,
							convert(varchar(10),Timetable.TimetableResource_begTime,104) + ' ' + convert(varchar(5),Timetable.TimetableResource_begTime,108) as recDate,
							Timetable.TimetableResource_begTime as recSort,
							null as MedPersonal_Fio
						from
							v_TimetableResource_lite Timetable (nolock)
							left join v_Resource r (nolock) on r.Resource_id = Timetable.Resource_id
						where
							ED.DirType_id in (10)
							and Timetable.EvnDirection_id = ED.EvnDirection_id {$ttr_filters}
						UNION ALL
						select top 1
							null as EvnQueue_id,
							ED.LpuSectionProfile_id as LpuSectionProfile_did,
							Timetable.TimetableGraf_id,
							null as TimetableMedService_id,
							null as TimetableResource_id,
							null as TimetableStac_id,
							null as EvnQueue_setDT,
							Timetable.pmUser_insID,
							Timetable.pmUser_updID,
							convert(varchar(10),isnull(Timetable.TimetableGraf_begTime, Timetable.TimetableGraf_factTime),104) + ' ' + convert(varchar(5),isnull(Timetable.TimetableGraf_begTime, Timetable.TimetableGraf_factTime),108) as recDate,
							isnull(Timetable.TimetableGraf_begTime, Timetable.TimetableGraf_factTime) as recSort,
							MSFT.Person_Fio as MedPersonal_Fio
						from
							v_TimetableGraf_lite Timetable (nolock)
							left join v_MedStaffFact MSFT with (NOLOCK) on Timetable.MedStaffFact_id = MSFT.MedStaffFact_id
						where
							ED.DirType_id in (2,3,4,6,16)
							and Timetable.EvnDirection_id = ED.EvnDirection_id {$ttg_filters}
						UNION ALL
						select top 1
							EQ.EvnQueue_id,
							EQ.LpuSectionProfile_did,
							null as TimetableGraf_id,
							null as TimetableMedService_id,
							null as TimetableResource_id,
							null as TimetableStac_id,
							EQ.EvnQueue_setDT,
							EQ.pmUser_insID,
							EQ.pmUser_updID,
							null as recDate,
							EQ.EvnQueue_setDT as recSort,
							null as MedPersonal_Fio
						from 
							v_EvnQueue EQ (nolock)
						where EQ.EvnDirection_id = ED.EvnDirection_id						
							and EQ.EvnQueue_IsArchived is null {$eq_filters}
						UNION ALL
						select
							null as EvnQueue_id,
							ED.LpuSectionProfile_id as LpuSectionProfile_did,
							null as TimetableGraf_id,
							null as TimetableMedService_id,
							null as TimetableResource_id,
							null as TimetableStac_id,
							null as EvnQueue_setDT,
							ED.pmUser_insID,
							ED.pmUser_updID,
							null as recDate,
							null as recSort,
							null as MedPersonal_Fio
						where ED.DirType_id = 5 and isnull(ED.EvnDirection_IsAuto,1) = 1 {$emergency_filters}
						{$need_canceled}
					) tt
				) TT
				left join v_MedStaffFact MSF with (NOLOCK) on ed.MedStaffFact_id = MSF.MedStaffFact_id
				left join v_MedPersonal MP with (NOLOCK) on ED.MedPersonal_id = MP.MedPersonal_id and MP.Lpu_id = Lpu.Lpu_id
				left join v_LpuSection LS with (nolock) on ED.LpuSection_id = LS.LpuSection_id
				left join v_Address_all UAddress (nolock) on UAddress.Address_id = PS.UAddress_id
				left join v_MedService MS with (nolock) on MS.MedService_id = ED.MedService_id
				left join v_LpuSection DLpuSection with(nolock) on DLpuSection.LpuSection_id = ED.LpuSection_did
				left join v_DirType DT with (nolock) on DT.DirType_id = ED.DirType_id
				left join v_EvnStatus ES (nolock) on ES.EvnStatus_id = ED.EvnStatus_id
				--left join v_ElectronicTalon et (nolock) on et.EvnDirection_id = ed.EvnDirection_id
				outer apply (
					Select top 1 * from v_ElectronicTalon et (nolock) where et.EvnDirection_id = ed.EvnDirection_id
				) et
				outer apply (
					Select top 1
						EUP.UslugaComplex_id,
						uc.UslugaComplex_Name
					from 
						v_EvnUslugaPar EUP (nolock)
						left join v_UslugaComplex uc (nolock) on uc.UslugaComplex_id = EUP.UslugaComplex_id
					where
						ED.DirType_id in (10,11,15)
						and EUP.EvnDirection_id = ED.EvnDirection_id
				) EUP
				left join v_pmUser pmi with (nolock) on pmi.PMUser_id=ED.pmUser_insID
				{$join}
				left join v_LpuSectionProfile LSP with (nolock) on TT.LpuSectionProfile_did = LSP.LpuSectionProfile_id
				left join v_TimeTableGraf_lite ttf with (nolock) on ttf.TimeTableGraf_id = TT.TimetableGraf_id
				left join fer.v_Slot slot with (nolock) on slot.TimeTableGraf_id = TT.TimetableGraf_id
			-- end from
			where
			-- where
			(1=1)
			and ED.DirType_id != 24
			{$filters}
			-- end where
			order by
			-- order by
			TT.recSort desc
			-- end order by
		";
		//echo getDebugSQL($query, $queryParams);exit;
		if (!empty($data['onlySQL']) && strtolower($data['onlySQL']) == 'true' && isSuperAdmin()) {
			echo getDebugSQL($query, $queryParams);
			return false;
		}
		return $this->getPagingResponse($query, $queryParams, $data['start'], $data['limit'], true);
	}
	/**
	 *
	 * @param type $data
	 * @return type 
	 */
	function getDataEvnDirection($data)
	{

		$join = "";
		$selectPersonData = "convert(varchar(10), PS.Person_Birthday, 104) as Person_Birthday,
				convert(varchar(10), PS.Person_Birthday, 104) as Person_BirthDay,
				PS.Person_SurName,
				PS.Person_FirName,
				PS.Person_SecName,";
		if (allowPersonEncrypHIV($data['session'])) {
			$join .= " left join v_PersonEncrypHIV peh with(nolock) on peh.Person_id = ED.Person_id";
			$selectPersonData = "case when peh.PersonEncrypHIV_Encryp is null then convert(varchar(10), PS.Person_Birthday, 104) end as Person_Birthday,
				case when peh.PersonEncrypHIV_Encryp is null then convert(varchar(10), PS.Person_Birthday, 104) end as Person_BirthDay,
				case when peh.PersonEncrypHIV_Encryp is null then PS.Person_SurName else peh.PersonEncrypHIV_Encryp end as Person_SurName,
				case when peh.PersonEncrypHIV_Encryp is null then PS.Person_FirName end as Person_FirName,
				case when peh.PersonEncrypHIV_Encryp is null then PS.Person_SecName end as Person_SecName,";
		}
		if ($this->regionNick == 'kz') {
			$selectPersonData .= "
				ED.PayType_id,
				edla.PurposeHospital_id,
				edla.Diag_cid,
			";
			$join .= "
				left join r101.EvnLinkAPP edla (nolock) on edla.Evn_id = ED.EvnDirection_id
			";
		}
		$query = "
			DECLARE @time datetime = dbo.tzGetDate();
			select distinct
				case
					when isnull(TTG.TimetableGraf_id,0)!=0 then 'TimetableGraf'
					when isnull(TMS.TimetableMedService_id,0)!=0 then 'TimetableMedService'
					when isnull(TTS.TimetableStac_id,0)!=0 then 'TimetableStac'
					when isnull(TTR.TimetableResource_id,0)!=0 then 'TimetableResource'
					when isnull(EQ.EvnQueue_id,0)!=0 then 'EvnQueue'
				end as type,
				convert(varchar(10), ED.EvnDirection_setDT, 104) as EvnDirection_setDate,
				convert(varchar(10), ED.EvnDirection_desDT, 104) as EvnDirection_desDT,
				ED.Org_sid,
				ED.Lpu_sid,
				ED.Lpu_id,
				ED.MedStaffFact_id,
				ED.MedStaffFact_id as From_MedStaffFact_id,
				ED.MedPersonal_id,
				ED.LpuSection_id,
				ED.MedPersonal_zid,
				dir.DirType_id,
				ED.Diag_id,
				EQ.EvnQueue_id,
				ED.PrehospDirect_id,
				TMS.TimetableMedService_id,
				TTS.TimetableStac_id,
				TTG.TimetableGraf_id,
				TTR.TimetableResource_id,
				ED.EvnDirection_pid,
				ED.EvnDirection_id,
				ED.EvnDirection_Num,
				ED.EvnDirection_Descr,
				ED.Person_id,
				ED.Server_id,
				ED.PersonEvn_id,
				ED.RemoteConsultCause_id,
				ED.EvnDirection_IsReceive,
				ED.EvnDirection_IsNeedOper,
				ED.EvnDirection_IsAuto,
				ED.EvnDirection_IsCito,
				ED.Post_id,
				ED.EvnStatus_id,
				{$selectPersonData}
				ED.Diag_id,
				EQ.LpuSectionProfile_did,
				ED.LpuSectionProfile_id,
				LSPD.LpuSectionProfile_Name,
				MSFD.MedStaffFact_id as MedStaffFact_did,
				meddid.MedPersonal_id as MedPersonal_did,
				meddid.Person_Fio as MedPersonal_Fio,
				ED.Lpu_did,
				ED.MedicalCareFormType_id,
				lpudid.Lpu_Nick,
				LUD.LpuUnit_Name,
				isnull(ED.LpuUnitType_id,LUD.LpuUnitType_id) as LpuUnitType_did,
				LUD.LpuUnit_id as LpuUnit_did,
				LSD.LpuSection_Name,
				LSD.LpuSection_id as LpuSection_did,
				dir.DirType_Code,
				dir.DirType_Name,
				ed.ARMType_id,
				mst.MedServiceType_SysNick,
				ucms.UslugaComplexMedService_id,
				ucms.UslugaComplex_id,
				case when ucms.UslugaComplexMedService_id is not null AND exists(
					/* если у услуги есть своё расписание, тогда для записи из очереди открывать его в противном случае открывать расписание самой службы */
					Select top 1 Timetable.TimetableMedService_id
					from v_TimetableMedService_lite Timetable (nolock)
					where Timetable.UslugaComplexMedService_id = ucms.UslugaComplexMedService_id
					and Timetable.TimetableMedService_begTime >= @time
				) then 1 end as isAllowRecToUslugaComplexMedService,
				ms.MedService_Nick,
				ms.MedService_id as MedService_did,
				ms.MedService_id,
				ms_pzm.MedService_id as MedService_pzid,
				EPP.EvnPrescrProc_id,
				R.Resource_Name,
				R.Resource_id as Resource_did,
				R.Resource_id
			from
				v_EvnDirection_all ED with (nolock)
				left join v_EvnQueue EQ with (nolock) on EQ.EvnDirection_id = ED.EvnDirection_id
				outer apply (
					Select top 1 TimetableGraf_id, MedStaffFact_id from v_TimeTableGraf_lite TTG with (nolock) where TTG.EvnDirection_id = ED.EvnDirection_id and TTG.MedStaffFact_id is not null
				) TTG
				outer apply (
					Select top 1 TimetableStac_id, LpuSection_id from v_TimetableStac_lite TTS with (nolock) where ED.EvnDirection_id = TTS.EvnDirection_id
				) TTS
				left join v_MedStaffFact MSFD with (nolock) on MSFD.MedStaffFact_id = TTG.MedStaffFact_id AND MSFD.Lpu_id = ED.Lpu_did
				left join v_MedService MS with (nolock) on ED.Medservice_id = MS.Medservice_id
				left join v_MedServiceType mst with (nolock) on mst.MedServiceType_id = ms.MedServiceType_id
				left join v_LpuSection LSD with (nolock) on coalesce(MSFD.LpuSection_id, TTS.LpuSection_id, MS.LpuSection_id, ED.LpuSection_did) = LSD.LpuSection_id AND LSD.Lpu_id = ED.Lpu_did
				left join v_LpuSectionProfile LSPD with(nolock) on LSPD.LpuSectionProfile_id = LSD.LpuSectionProfile_id
				left join v_LpuUnit LUD with (nolock) on isnull(LSD.LpuUnit_id, ED.LpuUnit_did) = LUD.LpuUnit_id AND LUD.Lpu_id = ED.Lpu_did
				left join v_DirType dir with (nolock) on ED.DirType_id = dir.DirType_id
				outer apply (
					Select top 1 EUP.UslugaComplex_id
					from v_EvnUslugaPar EUP (nolock)
					where EUP.EvnDirection_id = ED.EvnDirection_id
						and dir.DirType_id in (10,11,15)
				) EUP
				outer apply (
					Select top 1 TimetableResource_id from v_TimetableResource_lite TTR with (nolock) where TTR.EvnDirection_id = ED.EvnDirection_id
				) TTR
				outer apply (
					Select top 1 TimetableMedService_id, UslugaComplexMedService_id, MedService_id from v_TimetableMedService_lite TMS with (nolock) where TMS.EvnDirection_id = ED.EvnDirection_id
				) TMS
				outer apply (
					select top 1 ucms.UslugaComplexMedService_id, ucms.UslugaComplex_id
					from v_UslugaComplexMedService ucms with (nolock)
					where dir.DirType_id in (10,11,15) AND tms.UslugaComplexMedService_id is not null 
					AND ucms.UslugaComplexMedService_id = tms.UslugaComplexMedService_id
					union all 
					select top 1 ucms.UslugaComplexMedService_id, ucms.UslugaComplex_id
					from v_UslugaComplexMedService ucms with (nolock)
					where dir.DirType_id in (10,11,15) AND tms.UslugaComplexMedService_id is null 
					AND ucms.MedService_id = EQ.MedService_did and ucms.UslugaComplex_id = EUP.UslugaComplex_id
					AND ucms.UslugaComplexMedService_pid is null
					AND cast(ucms.UslugaComplexMedService_begDT as DATE) <= cast(ED.EvnDirection_setDT as DATE)
					AND cast(isnull(ucms.UslugaComplexMedService_endDT, ED.EvnDirection_setDT) as DATE) >= cast(ED.EvnDirection_setDT as DATE)
					union all
					select top 1 ucms.UslugaComplexMedService_id, ucms.UslugaComplex_id
					from v_UslugaComplexMedService ucms with (nolock)
					inner join v_UslugaComplexResource ucr with(nolock) on ucr.UslugaComplexMedService_id = ucms.UslugaComplexMedService_id
					where dir.DirType_id in (10)
					and ucms.UslugaComplex_id = EUP.UslugaComplex_id
					AND ucms.UslugaComplexMedService_pid is null
					and ucms.MedService_id = MS.MedService_id
					AND cast(ucms.UslugaComplexMedService_begDT as DATE) <= cast(ED.EvnDirection_setDT as DATE)
					AND cast(isnull(ucms.UslugaComplexMedService_endDT, ED.EvnDirection_setDT) as DATE) >= cast(ED.EvnDirection_setDT as DATE)
				) ucms
				outer apply(
					select top 1
						ms.MedService_id
					from
						v_MedService ms (nolock)
						inner join v_MedServiceType mst (nolock) on mst.MedServiceType_id = ms.MedServiceType_id
					where
						ms.MedService_id = tms.MedService_id
						and mst.MedServiceType_SysNick = 'pzm'
				) ms_pzm
				outer apply(
					select top 1
					EPP.EvnPrescrProc_id
					from v_EvnPrescrProc EPP with (nolock)
					left join EvnPrescrDirection EPD with (nolock) on EPD.EvnPrescr_id = EPP.EvnPrescrProc_id
					where
					EPP.EvnPrescrProc_pid = ED.EvnDirection_pid
					and EPD.EvnDirection_id is null
				) EPP
				left join v_Resource R with (nolock) on R.Resource_id = ED.Resource_id
				left join v_Lpu lpudid with (nolock) on ED.Lpu_did = lpudid.Lpu_id
				left join v_medPersonal meddid with (nolock) on isnull(MSFD.MedPersonal_id, ED.MedPersonal_did) = meddid.MedPersonal_id AND meddid.Lpu_id = ED.Lpu_did
				left join v_PersonState PS with (nolock) on ED.Person_id = PS.Person_id
				{$join}
			Where
				ED.EvnDirection_id = :EvnDirection_id
		";
		$queryParams = array('EvnDirection_id' => $data['EvnDirection_id']);
		//echo getDebugSQL($query, $queryParams);exit();
		$result = $this->db->query($query, $queryParams);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}
	
	/**
	 * Получение EvnDirection_id из расписания, если таковое есть
	 */
	function getEvnDirection_id($data)
	{
		$filter = "(1=1) ";
		$params = array();
		if ($data['TimetableGraf_id']>0)
		{
			$filter .="and TTG.TimetableGraf_id = :TimetableGraf_id ";
			$params['TimetableGraf_id'] = $data['TimetableGraf_id'];
		}
		else 
		{
			return false;
		}
		
		if ($data['setDate']>0)
		{
			$filter .="and cast(TTG.TimetableGraf_begTime as date) = :setDate ";
			$params['setDate'] = $data['setDate'];
		}
		else 
		{
			return false;
		}
		if ($data['MedStaffFact_id']>0)
		{
			$filter .="and TTG.MedStaffFact_id = :MedStaffFact_id ";
			$params['MedStaffFact_id'] = $data['MedStaffFact_id'];
		}
		$query = "
			Select top 1
				ed.EvnDirection_id
			from v_TimeTableGraf_lite TTG with (NOLOCK)
			left join v_EvnDirection_all ed with (NOLOCK) on ed.EvnDirection_id= ttg.EvnDirection_id
			where {$filter};
		";
		$result = $this->db->query($query, $params);

		if ( is_object($result) ) 
		{
			$res = $result->result('array');
			if ((count($res)>0) && ($res[0]['EvnDirection_id']))
			{
				return $res[0]['EvnDirection_id'];
			}
		}
		else 
		{
			return false;
		}
	}

	/**
	 * Получение данных направления для печати
	 */
	function getEvnDirectionForPrint($data) {
		$resp = $this->queryResult("
			select
				ed.EvnDirection_id,
				dt.DirType_Code,
				mst.MedServiceType_SysNick
			from
				v_EvnDirection_all ed (nolock)
				left join v_DirType dt (nolock) on dt.DirType_id = ed.DirType_id
				left join v_MedService ms (nolock) on ed.MedService_id = ms.MedService_id
				left join v_MedServiceType mst (nolock) on mst.MedServiceType_id = ms.MedServiceType_id
			where
				ed.EvnDirection_id = :EvnDirection_id
		", array(
			'EvnDirection_id' => $data['EvnDirection_id']
		));

		if (!empty($resp[0]['EvnDirection_id'])) {
			$resp[0]['Error_Msg'] = '';
			return $resp[0];
		}

		return array(
			'Error_Msg' => 'Ошибка получения данных по направлению'
		);
	}

	/**
	 * @param $data
	 * @return int
	 */
	function getEvnDirectionInEvnVizit($data)
	{
		$filter = "(1=1) ";
		$params = array();
		if ($data['EvnPL_id']>0)
		{
			$filter .="and EvnVizitPL_pid = :EvnPL_id ";
			$params['EvnPL_id'] = $data['EvnPL_id'];
		}
		else 
		{
			return 0;
		}

		$query = "
			Select top 1
				EvnVizitPL.EvnDirection_id
			from v_EvnVizitPL EvnVizitPL with (NOLOCK)
			where {$filter} and EvnVizitPL.EvnDirection_id is not null
			order by EvnVizitPL_setDate;
		";
		$result = $this->db->query($query, $params);

		if ( is_object($result) ) 
		{
			$res = $result->result('array');
			if ((count($res)>0) && ($res[0]['EvnDirection_id']))
			{
				return $res[0]['EvnDirection_id'];
			}
		}
		else 
		{
			return 0;
		}
	}

	/**
	 * @param $data
	 * @return int
	 */
	function getEvnDirectionInEvnVizitStom($data)
	{
		$filter = "(1=1) ";
		$params = array();
		if ($data['EvnPLStom_id']>0)
		{
			$filter .="and EvnVizitPLStom_pid = :EvnPLStom_id ";
			$params['EvnPLStom_id'] = $data['EvnPLStom_id'];
		}
		else 
		{
			return 0;
		}

		$query = "
			Select top 1
				EvnVizitPLStom.EvnDirection_id
			from v_EvnVizitPLStom EvnVizitPLStom with (NOLOCK)
			where {$filter} and EvnVizitPLStom.EvnDirection_id is not null
			order by EvnVizitPLStom_setDate;
		";
		$result = $this->db->query($query, $params);

		if ( is_object($result) ) 
		{
			$res = $result->result('array');
			if ((count($res)>0) && ($res[0]['EvnDirection_id']))
			{
				return $res[0]['EvnDirection_id'];
			}
		}
		else 
		{
			return 0;
		}
	}

	/**
	 * @param $data
	 * @return bool
	 */
	function getDirectionEvnPLIf($data) 
	{
		$data['EvnDirection_id'] = $this->getEvnDirectionInEvnVizit($data);
		if ($data['EvnDirection_id']>0)
		{
			return $this->loadEvnDirectionFull($data);
		}
		return false;
	}

	/**
	 * @param $data
	 * @return bool
	 */
	function getDirectionEvnPLStomIf($data) 
	{
		$data['EvnDirection_id'] = $this->getEvnDirectionInEvnVizitStom($data);
		if ($data['EvnDirection_id']>0)
		{
			return $this->loadEvnDirectionStomFull($data);
		}
		return false;
	}

	/**
	 * @param $data
	 * @return bool
	 */
	function getDirectionIf($data) 
	{
		$data['EvnDirection_id'] = $this->getEvnDirection_id($data);
		if ($data['EvnDirection_id']>0)
		{
			$kolvo = $this->countEvnVizitPL($data);
			if ($kolvo==0)
			{
				return $this->loadEvnDirectionFull($data);
			}
		}
		return false;
	}

	/**
	 * @param $data
	 * @return bool
	 */
	function getDirectionStomIf($data) 
	{
		$data['EvnDirection_id'] = $this->getEvnDirection_id($data);
		if ($data['EvnDirection_id']>0)
		{
			$kolvo = $this->countEvnVizitPLStom($data);
			if ($kolvo==0)
			{
				return $this->loadEvnDirectionStomFull($data);
			}
		}
		return false;
	}

	/**
	 * @param $data
	 * @return array|bool
	 */
	function loadEvnDirectionGrid($data) {
		$filter = '';
		if ($data['includeDeleted'] == 1) {
			$filter .= ' and ED.EvnStatus_id not in (12,13) ';
		}
		$query = "
			select
				-- select
				ED.EvnDirection_id,
				ED.EvnDirection_pid,
				ED.EvnStatus_id,
				ED.Person_id,
				ED.PersonEvn_id,
				ED.Server_id,
				ED.Diag_id,
				DT.DirType_id,
				ED.LpuSection_id,
				ED.MedPersonal_id,
				ED.MedPersonal_zid,
				LSP.LpuSectionProfile_id,
				ED.EvnDirection_Num,
				ED.EvnDirection_Descr,
				ED.TimetableGraf_id,
				ED.TimetableMedService_id,
				ED.TimetableResource_id,
				ED.EvnQueue_id,
				convert(varchar(10), ED.EvnDirection_setDate, 104) as EvnDirection_setDate,
				RTRIM(DT.DirType_Name) as DirType_Name,
				RTRIM(LSP.LpuSectionProfile_Name) as LpuSectionProfile_Name
				-- end select
			from 
				-- from
				v_EvnDirection_all ED with (NOLOCK)
				inner join DirType DT with (NOLOCK) on DT.DirType_id = ED.DirType_id
				left join LpuSectionProfile LSP with (NOLOCK) on LSP.LpuSectionProfile_id = ED.LpuSectionProfile_id
				-- end from
			where
				-- where
				EvnDirection_pid = :EvnDirection_pid
				{$filter}
				-- end where
			order by 
				-- order by
				ED.EvnDirection_setDate DESC
				-- end order by
		";
		
		$params = array(
			'EvnDirection_pid' => $data['EvnDirection_pid']
		);
		$result = $this->db->query(getLimitSQLPH($query, $data['start'], $data['limit']), $params);
		$result_count = $this->db->query(getCountSQLPH($query), $params);
		
		if (is_object($result_count))
		{
			$cnt_arr = $result_count->result('array');
			$count = $cnt_arr[0]['cnt'];
			unset($cnt_arr);
		}
		else
		{
			$count = 0;
		}
		if (is_object($result))
		{
			$response = array();
			$response['data'] = $result->result('array');
			$response['totalCount'] = $count;
			return $response;
		}
		else
		{
			return false;
		}
	}

	/**
	 *  Получение списка направлений для панели направлений в ЭМК
	 */
	function loadEvnDirectionPanel($data)
	{
		$filter = '';
		$whereEdd = '';
		$joinEdd = '';
		if (!empty($data['DopDispInfoConsent_id'])) {
			$filter .= ' and ed.DopDispInfoConsent_id = :DopDispInfoConsent_id ';
		}

		if(getRegionNick() != 'kz'){
			// Направление на ВМП
			$joinEdd = ' LEFT JOIN EvnLink EL WITH(NOLOCK) ON EL.Evn_id = E.Evn_id ';
			$whereEdd = ' OR EL.Evn_lid = '.$data['EvnDirection_pid'];
		}
		
		if (!empty($data['DirType'])) {
			switch ($data['DirType'])
			{
				case 'swGridEvnDirection':
					$filter .= " and dt.DirType_Code IN (3, 12, 13, 18)";
					break;
				case 'swGridEvnDirectionCommon':
					$filter .= " and dt.DirType_Code IN (2, 8, 9, 10, 11, 15, 23, 25, 26, 27, 28, 30)";
					break;
				case 'swGridEvnDirectionHosp':
					$filter .= " and dt.DirType_Code IN (1, 4, 5, 6)";
					break;
				case 'swGridEvnDirectionPat':
					$filter .= " and dt.DirType_Code IN (7, 29)";
					break;
			}
		}

		$resp = $this->queryResult("
			with edd as (
				select E.* from v_Evn E (nolock)
				{$joinEdd}
			 	where 
			 		( E.Evn_pid = :EvnDirection_pid {$whereEdd})
			 		and EvnClass_id IN (27,49,117,199,216)
			) 
			select
				-- Необходимо отличать лишь направления на лаб и инстр диагностики от остальных
				case
					when ms.MedServiceType_id in (6,7,71) then 'EvnDirectionLabDiag'
					when ms.MedServiceType_id in (8) then 'EvnDirectionFuncDiag'
					else 'EvnDirection'
				end as 'object',
				EDH.EvnDirectionHistologic_id,
				ed.EvnDirection_id,
				case when EPM.EvnPrescrMse_id is not null then EPM.EvnPrescrMse_IsSigned else ED.EvnDirection_IsSigned end as IsSigned,
				case when EPM.EvnPrescrMse_id is not null then 'EvnPrescrMse' else 'EvnDirection' end as EMDRegistry_ObjectName,
				case when EPM.EvnPrescrMse_id is not null then EPM.EvnPrescrMse_id else ED.EvnDirection_id end as EMDRegistry_ObjectID,
				dt.DirType_Name,
				ls.LpuSection_Name,
				isnull(l.Lpu_Name, LH.LpuHTM_Nick) as Lpu_Name,
				o.Org_Name,
				l.Lpu_Nick,
				o.Org_Nick,
				convert(varchar(10), coalesce(ttms.TimetableMedService_begTime, ttg.TimetableGraf_begTime, tts.TimetableStac_setDate, ttr.TimetableResource_begTime, ed.EvnDirection_setDate, EDHT.EvnDirectionHTM_directDate), 104) as EvnDirection_setDate,
				convert(varchar(5), coalesce(ttms.TimetableMedService_begTime, ttg.TimetableGraf_begTime, tts.TimetableStac_setDate, ttr.TimetableResource_begTime, ed.EvnDirection_setTime), 108) as EvnDirection_setTime,
				ed.EvnDirection_Num,
				dt.DirType_Code,
				ttg.TimetableGraf_id,
				ttms.TimetableMedService_id,
				ttr.TimetableResource_id,
				tts.TimetableStac_id,
				eq.EvnQueue_id,
				ED.EvnStatus_id,
				ES.EvnStatus_Name,
				ES.EvnStatus_SysNick,
				convert(varchar(10), ED.EvnDirection_statusDate, 104) as EvnDirection_statusDate, coalesce(ESC.EvnStatusCause_Name,dft.DirFailType_Name,null) as EvnStatusCause_Name,
				EPM.EvnPrescrMse_id,
				EDHT.EvnDirectionHTM_id,
				EDHT.LpuHTM_id, 
				EDHT.EvnDirectionHTM_directDate,
				EPM.Lpu_gid,
				EDC.EvnDirectionCVI_id,
				EDC.EvnDirectionCVI_Lab,
				convert(varchar(10), EDC.EvnDirectionCVI_takeDT, 104) as EvnDirectionCVI_takeDate,
				isnull(EPM.Lpu_gid, EDC.Lpu_id) as Lpu_gid,
				EPVK.EvnPrescrVK_id,
				EPVK.EvnStatus_Name as EvnStatus_epvkName,
				EPVK.EvnStatus_SysNick as EvnStatus_epvkSysNick
			from
				v_EvnDirection_all ed (nolock)
				left join v_MedService ms (nolock) on ms.MedService_id = ed.MedService_id
				left join v_DirType dt (nolock) on dt.DirType_id = ed.DirType_id
				left join v_DirFailType dft (nolock) on dft.DirFailType_id = ed.DirFailType_id
				left join v_LpuSection ls (nolock) on ls.LpuSection_id = ed.LpuSection_did
				left join v_Lpu l (nolock) on l.Lpu_id = ed.Lpu_did
				left join v_Org o (nolock) on o.Org_id = ed.Org_oid
				left join v_TimeTableGraf_lite ttg (nolock) on ttg.EvnDirection_id = ed.EvnDirection_id
				left join v_TimetableMedService_lite ttms (nolock) on ttms.EvnDirection_id = ed.EvnDirection_id
				left join v_TimetableResource_lite ttr (nolock) on ttr.EvnDirection_id = ed.EvnDirection_id
				left join v_TimetableStac_lite tts (nolock) on tts.EvnDirection_id = ed.EvnDirection_id
				left join v_EvnQueue eq (nolock) on eq.EvnDirection_id = ed.EvnDirection_id
				outer apply(
					select top 1
						ESH.EvnStatusCause_id
					from
						v_EvnStatusHistory ESH (nolock)
					where
						ESH.Evn_id = ED.EvnDirection_id
						and ESH.EvnStatus_id = ED.EvnStatus_id
					order by
						ESH.EvnStatusHistory_begDate desc
				) ESH
				left join v_EvnStatus ES (nolock) on ES.EvnStatus_id = ED.EvnStatus_id
				left join v_EvnStatusCause ESC (nolock) on ESC.EvnStatusCause_id = ESH.EvnStatusCause_id
				-- направление МСЭ
				outer apply (
					select top 1 EPM.EvnPrescrMse_id, EPM.Lpu_gid, EPM.EvnPrescrMse_IsSigned
					from v_EvnPrescrMse EPM with (nolock)
					where ED.EvnQueue_id = EPM.EvnQueue_id
				) EPM
				-- Направление на ВМП
				left join v_EvnDirectionHTM EDHT with (nolock) on EDHT.EvnDirectionHTM_id = ED.EvnDirection_id 
				left join v_LpuHTM LH with (nolock) on LH.LpuHTM_id = EDHT.LpuHTM_id
				-- Направление на тест на КВИ
				left join v_EvnDirectionCVI EDC with (nolock) on EDC.EvnDirectionCVI_id = ED.EvnDirection_id
				-- назначение на ВК в очереди
				outer apply (
					select top 1
					 	EPVK.EvnPrescrVK_id,
					 	ES.EvnStatus_SysNick,
					 	ES.EvnStatus_Name
					from
						v_EvnPrescrVK EPVK with (nolock)
						left join v_EvnStatus ES with (nolock) on ES.EvnStatus_id = ISNULL(EPVK.EvnStatus_id, 43)
					where
						EPVK.EvnQueue_id = EQ.EvnQueue_id
				) EPVK
				-- Направление на патологогистологическое исследование
				left join v_EvnDirectionHistologic EDH with (nolock) on EDH.EvnDirectionHistologic_id = ED.EvnDirection_id
			where
				exists (select top 1 Evn_id from edd where ed.EvnDirection_id = edd.Evn_id)
				{$filter}
		", $data);

		$EvnDirectionIds = [];
		foreach($resp as $one) {
			if ($one['EMDRegistry_ObjectName'] == 'EvnDirection' && !empty($one['EvnDirection_id']) && $one['IsSigned'] == 2 && !in_array($one['EvnDirection_id'], $EvnDirectionIds)) {
				$EvnDirectionIds[] = $one['EvnDirection_id'];
			}
		}
		
		$isEMDEnabled = $this->config->item('EMD_ENABLE');
		if (!empty($EvnDirectionIds) && !empty($isEMDEnabled)) {
			$this->load->model('EMD_model');
			$signStatus = $this->EMD_model->getSignStatus([
				'EMDRegistry_ObjectName' => 'EvnDirection',
				'EMDRegistry_ObjectIDs' => $EvnDirectionIds,
				'MedStaffFact_id' => $data['session']['CurMedStaffFact_id'] ?? null
			]);

			foreach($resp as $key => $one) {
				$resp[$key]['EvnDirection_SignCount'] = 0;
				$resp[$key]['EvnDirection_MinSignCount'] = 0;
				if (!empty($one['EvnDirection_id']) && $one['IsSigned'] == 2 && isset($signStatus[$one['EvnDirection_id']])) {
					$resp[$key]['EvnDirection_SignCount'] = $signStatus[$one['EvnDirection_id']]['signcount'];
					$resp[$key]['EvnDirection_MinSignCount'] = $signStatus[$one['EvnDirection_id']]['minsigncount'];
					$resp[$key]['IsSigned'] = $signStatus[$one['EvnDirection_id']]['signed'];
				}
			}
		}

		return $resp;
	}

	/**
	 * Получение списка направлений для раздела направлений в ДВН
	 */
	function loadEvnDirectionPanel_EvnPLDispDop($data) {
		$filter = '';
		if (!empty($data['DopDispInfoConsent_id'])) {
			$filter .= ' and ed.DopDispInfoConsent_id = :DopDispInfoConsent_id ';
		}
		$filter .= " and dt.DirType_Code IN (3, 12)";
		

		$sql = "
			select
				EDH.EvnDirectionHistologic_id,
				ed.EvnDirection_id,
				case when EPM.EvnPrescrMse_id is not null then EPM.EvnPrescrMse_IsSigned else ED.EvnDirection_IsSigned end as IsSigned,
				case when EPM.EvnPrescrMse_id is not null then 'EvnPrescrMse' else 'EvnDirection' end as EMDRegistry_ObjectName,
				case when EPM.EvnPrescrMse_id is not null then EPM.EvnPrescrMse_id else ED.EvnDirection_id end as EMDRegistry_ObjectID,
				dt.DirType_Name,
				ls.LpuSection_Name,
				l.Lpu_Name,
				o.Org_Name,
				l.Lpu_Nick,
				o.Org_Nick,
				convert(varchar(10), coalesce(ttms.TimetableMedService_begTime, ttg.TimetableGraf_begTime, tts.TimetableStac_setDate, ttr.TimetableResource_begTime, ed.EvnDirection_setDate), 104) as EvnDirection_setDate,
				ed.EvnDirection_Num,
				dt.DirType_Code,
				ttg.TimetableGraf_id,
				ttms.TimetableMedService_id,
				ttr.TimetableResource_id,
				tts.TimetableStac_id,
				eq.EvnQueue_id,
				ED.EvnStatus_id,
				ES.EvnStatus_Name,
				ES.EvnStatus_SysNick,
				convert(varchar(10), ED.EvnDirection_statusDate, 104) as EvnDirection_statusDate,
				ESC.EvnStatusCause_Name,
				EPM.EvnPrescrMse_id,
				EDHT.EvnDirectionHTM_id,
				EPM.Lpu_gid,
				EPVK.EvnPrescrVK_id,
				EPVK.EvnStatus_Name as EvnStatus_epvkName,
				EPVK.EvnStatus_SysNick as EvnStatus_epvkSysNick
			from
				v_EvnDirection_all ed (nolock)
				left join v_DirType dt (nolock) on dt.DirType_id = ed.DirType_id
				left join v_LpuSection ls (nolock) on ls.LpuSection_id = ed.LpuSection_did
				left join v_Lpu l (nolock) on l.Lpu_id = ed.Lpu_did
				left join v_Org o (nolock) on o.Org_id = ed.Org_oid
				left join v_TimeTableGraf_lite ttg (nolock) on ttg.EvnDirection_id = ed.EvnDirection_id
				left join v_TimetableMedService_lite ttms (nolock) on ttms.EvnDirection_id = ed.EvnDirection_id
				left join v_TimetableResource_lite ttr (nolock) on ttr.EvnDirection_id = ed.EvnDirection_id
				left join v_TimetableStac_lite tts (nolock) on tts.EvnDirection_id = ed.EvnDirection_id
				left join v_EvnQueue eq (nolock) on eq.EvnDirection_id = ed.EvnDirection_id
				outer apply(
					select top 1
						ESH.EvnStatusCause_id
					from
						v_EvnStatusHistory ESH (nolock)
					where
						ESH.Evn_id = ED.EvnDirection_id
						and ESH.EvnStatus_id = ED.EvnStatus_id
					order by
						ESH.EvnStatusHistory_begDate desc
				) ESH
				left join v_EvnStatus ES (nolock) on ES.EvnStatus_id = ED.EvnStatus_id
				left join v_EvnStatusCause ESC (nolock) on ESC.EvnStatusCause_id = ESH.EvnStatusCause_id
				-- направление МСЭ
				outer apply (
					select top 1 EPM.EvnPrescrMse_id, EPM.Lpu_gid, EPM.EvnPrescrMse_IsSigned
					from v_EvnPrescrMse EPM with (nolock)
					where ED.EvnQueue_id = EPM.EvnQueue_id
				) EPM
				-- Направление на ВМП
				left join v_EvnDirectionHTM EDHT with (nolock) on EDHT.EvnDirectionHTM_id = ED.EvnDirection_id
				-- назначение на ВК в очереди
				outer apply (
					select top 1
					 	EPVK.EvnPrescrVK_id,
					 	ES.EvnStatus_SysNick,
					 	ES.EvnStatus_Name
					from
						v_EvnPrescrVK EPVK with (nolock)
						left join v_EvnStatus ES with (nolock) on ES.EvnStatus_id = ISNULL(EPVK.EvnStatus_id, 43)
					where
						EPVK.EvnQueue_id = EQ.EvnQueue_id
				) EPVK
				-- Направление на патологогистологическое исследование
				left join v_EvnDirectionHistologic EDH with (nolock) on EDH.EvnDirectionHistologic_id = ED.EvnDirection_id
			where
				ed.EvnDirection_pid = :EvnDirection_pid
				{$filter}
		";
		
		//exit(getDebugSQL($sql, $data));
		$resp = $this->queryResult($sql, $data);

		$EvnDirectionIds = [];
		foreach($resp as $one) {
			if ($one['EMDRegistry_ObjectName'] == 'EvnDirection' && !empty($one['EvnDirection_id']) && $one['IsSigned'] == 2 && !in_array($one['EvnDirection_id'], $EvnDirectionIds)) {
				$EvnDirectionIds[] = $one['EvnDirection_id'];
			}
		}

		if (!empty($EvnDirectionIds)) {
			$this->load->model('EMD_model');
			$signStatus = $this->EMD_model->getSignStatus([
				'EMDRegistry_ObjectName' => 'EvnDirection',
				'EMDRegistry_ObjectIDs' => $EvnDirectionIds,
				'MedStaffFact_id' => $data['session']['CurMedStaffFact_id'] ?? null
			]);

			foreach($resp as $key => $one) {
				$resp[$key]['EvnDirection_SignCount'] = 0;
				$resp[$key]['EvnDirection_MinSignCount'] = 0;
				if (!empty($one['EvnDirection_id']) && $one['IsSigned'] == 2 && isset($signStatus[$one['EvnDirection_id']])) {
					$resp[$key]['EvnDirection_SignCount'] = $signStatus[$one['EvnDirection_id']]['signcount'];
					$resp[$key]['EvnDirection_MinSignCount'] = $signStatus[$one['EvnDirection_id']]['minsigncount'];
					$resp[$key]['IsSigned'] = $signStatus[$one['EvnDirection_id']]['signed'];
				}
			}
		}

		return $resp;
	}
	
	/**
	 *
	 * @param type $data
	 * @return type 
	 */
	function checkRecordByPerson($data){
		$queryParams = array(
			'Person_id' => $data['Person_id']
		);
		$join ='';
		$filter='';
		if ( isset($data['MedStaffFact_id'])&&$data['formType']=='polka') {
			$join.=' left join EvnQueue EQ with (nolock) on EQ.EvnDirection_id = ED.EvnDirection_id and EQ.EvnQueue_recDT is null and EQ.EvnQueue_failDT is null
				outer apply (select top 1 LpuSectionProfile_id from v_LpuSection ls with (nolock)
                where ls.LpuSection_id=:LpuSection_id) DLSP
				';
			$filter .= ' and (TTG.MedStaffFact_id=:MedStaffFact_id or (LSP.LpuSectionProfile_id = DLSP.LpuSectionProfile_id and EQ.EvnQueue_id is not null))';
			$filter .= ' and ED.DirType_id in (3, 11, 16) and ED.Lpu_did = :Lpu_did';
			$queryParams['Lpu_did'] = $data['Lpu_id'];
			//$filter .=' and ED.EvnStatus_id not in(12,13,15)';
			$queryParams['MedStaffFact_id'] = $data['MedStaffFact_id'];
			$queryParams['LpuSection_id'] = $data['LpuSection_id'];
			
		}
		if ( isset($data['LpuSection_id'])&&$data['formType']=='stac') {
			$join.=' left join EvnQueue EQ with (nolock) on EQ.EvnDirection_id = ED.EvnDirection_id and EQ.EvnQueue_recDT is null and EQ.EvnQueue_failDT is null
				outer apply (select top 1 LpuSectionProfile_id from v_LpuSection ls with (nolock)
                where ls.LpuSection_id=:LpuSection_id) DLSP
				';
			$filter .= ' and (TTS.LpuSection_id=:LpuSection_id or (LSP.LpuSectionProfile_id = DLSP.LpuSectionProfile_id and EQ.EvnQueue_id is not null)) and ED.EvnStatus_id!=15';
			$filter .= ' and ED.DirType_id in (1, 2, 4, 5, 6)';
			//$filter .=' and ED.EvnStatus_id not in(12,13,15)';
			$queryParams['LpuSection_id'] = $data['LpuSection_id'];
		}
		$query = "
			Select top 1 1 as cnt -- вернет null виесто 0, но это не страшно
				from v_EvnDirection_all ED with (nolock)
				--left join v_Lpu Lpu with (nolock) on Lpu.Lpu_id = ED.Lpu_id
				--left join DirType DT with (nolock) on DT.DirType_id = ED.DirType_id
				left join LpuSectionProfile LSP with (nolock) on LSP.LpuSectionProfile_id = ED.LpuSectionProfile_id
				--left join Diag with (nolock) on Diag.Diag_id = ED.Diag_id
				left join v_TimeTableGraf_lite TTG with (nolock) on TTG.EvnDirection_id = ED.EvnDirection_id
				--left join TimetablePar TTP with (nolock) on TTP.TimetablePar_id = ED.TimetablePar_id
				left join v_TimetableStac_lite TTS with (nolock) on TTS.EvnDirection_id = ED.EvnDirection_id
				--left join v_EvnPL EvnPL with (nolock) on EvnPL.EvnDirection_id = ED.EvnDirection_id
                --left join v_EvnPS EvnPS with (nolock) on EvnPS.EvnDirection_id = ED.EvnDirection_id
			".$join."
				where ED.Person_id = :Person_id and ED.EvnStatus_id!=15
				" . $filter . "
		";
		$queryParams['MedStaffFact_id'] = $data['MedStaffFact_id'];
		$queryParams['LpuSection_id'] = $data['LpuSection_id'];
		$result = $this->db->query($query, $queryParams);
		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}
	
	/**
	 * Загрузка списка направлений из формы выбора направления
	 */
	function loadEvnDirectionList($data) {
		$filter = '';
		$join='';
		$select='';
		//print_r($data);
		$queryParams = array(
			'Person_id' => $data['Person_id']
		);
		if ( $data['parentClass'] == 'EvnPS' ) {
			$filter .= ' and ED.DirType_id in (1, 2, 4, 5, 6)';
		}
		else if (getRegionNick() != 'kareliya' && $data['parentClass'] == 'EvnPL' ) {
			$filter .= ' and ED.DirType_id in (3, 11, 16) and ED.Lpu_did = :Lpu_did';
			$queryParams['Lpu_did'] = $data['Lpu_id'];
		}
		else if(getRegionNick() != 'kareliya' && !isset($data['MedStaffFact_id'])){
			$filter .= ' and ED.Lpu_did = :Lpu_did';
			$queryParams['Lpu_did'] = $data['Lpu_id'];
		}

		if ( isset($data['MedStaffFact_id'])&&$data['formType']=='polka') {
			$join.=' left join EvnQueue EQ with (nolock) on EQ.EvnDirection_id = ED.EvnDirection_id and EQ.EvnQueue_recDT is null and EQ.EvnQueue_failDT is null
				outer apply (select top 1 LpuSectionProfile_id from v_LpuSection ls with (nolock)
                where ls.LpuSection_id=:LpuSection_id) DLSP
				';
			$filter .= ' and (TTG.MedStaffFact_id=:MedStaffFact_id or (LSP.LpuSectionProfile_id = DLSP.LpuSectionProfile_id and EQ.EvnQueue_id is not null))';
			$filter .= ' and ED.DirType_id in (3, 11, 16)';
			if(getRegionNick() != 'kareliya') {
				$filter .= ' and ED.Lpu_did = :Lpu_did';
			}
			$queryParams['Lpu_did'] = $data['Lpu_id'];
			//$filter .=' and ED.EvnStatus_id not in(12,13,15)';
			$queryParams['MedStaffFact_id'] = $data['MedStaffFact_id'];
			$queryParams['LpuSection_id'] = $data['LpuSection_id'];
			
		}
		if ( isset($data['LpuSection_id'])&&$data['formType']=='stac') {
			$join.=' left join EvnQueue EQ with (nolock) on EQ.EvnDirection_id = ED.EvnDirection_id and EQ.EvnQueue_recDT is null and EQ.EvnQueue_failDT is null
				outer apply (select top 1 LpuSectionProfile_id from v_LpuSection ls with (nolock)
                where ls.LpuSection_id=:LpuSection_id) DLSP
				';
			$filter .= ' and (TTS.LpuSection_id=:LpuSection_id or (LSP.LpuSectionProfile_id = DLSP.LpuSectionProfile_id and EQ.EvnQueue_id is not null)) and ED.EvnStatus_id!=15';
			$filter .= ' and ED.DirType_id in (1, 2, 4, 5, 6)';
			//$filter .=' and ED.EvnStatus_id not in(12,13,15)';
			$queryParams['LpuSection_id'] = $data['LpuSection_id'];
		}

		if ($data['formType']=='par') {
			$filter .= ' and ED.MedService_id is null';
			//$filter .=' and ED.EvnStatus_id not in(12,13,15)';
			$queryParams['LpuSection_id'] = $data['LpuSection_id'];
		}

		if (!empty($data['DirType_id'])) {
			$filter .= ' and ED.DirType_id = :DirType_id';
			$queryParams['DirType_id'] = $data['DirType_id'];
		}

		if (!empty($data['EvnDirection_pid'])) {
			$filter .= ' and ED.EvnDirection_pid = :EvnDirection_pid';
			$queryParams['EvnDirection_pid'] = $data['EvnDirection_pid'];
		}

		// (Статус <> отменено, отклонено)
		$filter .= ' and ISNULL(ED.EvnStatus_id, 16) not in (12,13)';

		// Дата, на которую отображаются заявления. Заявления, созданные позже этой даты, не получать
		// https://redmine.swan.perm.ru/issues/8048
		/* Убрал контроль согласно (refs #8559)
		if ( !empty($data['onDate']) ) {
			$filter .= ' and ED.EvnDirection_setDT <= :onDate';
			$queryParams['onDate'] = $data['onDate'];
		}
		*/
		
		if ($this->regionNick == 'kz') {
			$select .= "
				,ED.PayType_id
				,edla.PurposeHospital_id
				,edla.Diag_cid
			";
			$join .= "
				left join r101.EvnLinkAPP edla (nolock) on edla.Evn_id = ED.EvnDirection_id
			";
		}

		$query = "
			select
				ED.EvnDirection_id,
				ED.LpuSection_id,
				ED.MedPersonal_id,
				ED.Diag_id,
				DT.DirType_id,
				ED.EvnDirection_IsAuto,
				ED.EvnDirection_IsReceive,
				ED.Lpu_sid as Lpu_sid,
				ED.Lpu_id,
				isnull(ED.Org_sid,Lpu.Org_id) as Org_id,
				uc.UslugaComplex_id,
				uc.UslugaComplex_Name,
				RTRIM(Org.Org_Nick) as Lpu_Name,
				RTRIM(LSP.LpuSectionProfile_Name) as LpuSectionProfile_Name,
				ED.EvnDirection_Num,
				convert(varchar(10), ISNULL(ISNULL(TTG.TimetableGraf_begTime, TTP.TimetablePar_begTime), TTS.TimetableStac_setDate), 104) as Timetable_begTime,
				RTRIM(DT.DirType_Name) as DirType_Name,
				RTRIM(Diag.Diag_Code) + '. ' + RTRIM(Diag.Diag_Name) as Diag_Name,
				convert(varchar(10), ED.EvnDirection_setDate, 104) as EvnDirection_setDate,
				LpuD.Lpu_Nick as Lpu_dNick,
				case when (EvnUslugaPar.EvnUslugaPar_id is null and EvnPL.EvnPL_id is null and EvnPS.EvnPS_id is null) then 2 else 0 end as enabled,
				LB.LpuBuildingType_id
				{$select}
			from v_EvnDirection_all ED with (nolock)
				left join v_Lpu Lpu with (nolock) on Lpu.Lpu_id = ED.Lpu_sid
				left join v_Lpu LpuD with (nolock) on LpuD.Lpu_id = ED.Lpu_did
				left join v_Org Org with (nolock) on Org.Org_id = isnull(ED.Org_sid,Lpu.Org_id)
				left join DirType DT with (nolock) on DT.DirType_id = ED.DirType_id
				left join LpuSectionProfile LSP with (nolock) on LSP.LpuSectionProfile_id = ED.LpuSectionProfile_id
				left join v_LpuSection LS with (nolock) on LS.LpuSection_id = ED.LpuSection_id
				left join v_LpuUnit LU with (nolock) on LU.LpuUnit_id = LS.LpuUnit_id
				left join v_MedService MS with (nolock) on MS.MedService_id = ED.MedService_id
				left join v_LpuBuilding LB with (nolock) on LB.LpuBuilding_id = isnull(LU.LpuBuilding_id,MS.LpuBuilding_id)
				left join Diag with (nolock) on Diag.Diag_id = ED.Diag_id
				outer apply (
					select top 1
						ISNULL(EPLD.UslugaComplex_id,EPFDU.UslugaComplex_id) as UslugaComplex_id
					from
						v_EvnPrescrDirection EPD with(nolock)
						left join EvnPrescrLabDiag EPLD with(nolock) on EPLD.EvnPrescr_id = EPD.EvnPrescr_id
						left join EvnPrescrFuncDiag EPFD with (nolock) on EPFD.EvnPrescr_id = EPD.EvnPrescr_id
						left join EvnPrescrFuncDiagUsluga EPFDU with (nolock) on EPFDU.EvnPrescrFuncDiag_id = EPFD.EvnPrescrFuncDiag_id
					where
						EPD.EvnDirection_id = ed.EvnDirection_id
				) UCOMPL
				left join v_UslugaComplex UC with(nolock) on UC.UslugaComplex_id = UCOMPL.UslugaComplex_id
				outer apply (
					Select top 1 TimetableGraf_begTime, MedStaffFact_id from v_TimeTableGraf_lite TTG with (nolock) 
					where TTG.EvnDirection_id = ED.EvnDirection_id
				) TTG
				left join TimetablePar TTP with (nolock) on TTP.TimetablePar_id = ED.TimetablePar_id
				
				outer apply (
					Select top 1 TimetableStac_setDate, LpuSection_id from v_TimetableStac_lite TTS with (nolock)
					where TTS.EvnDirection_id = ED.EvnDirection_id
				) TTS
				outer apply (
					Select top 1 EvnPL_id from v_EvnPL EvnPL with (nolock) 
					where EvnPL.EvnDirection_id = ED.EvnDirection_id
				) EvnPL
				outer apply (
					Select top 1 EvnPS_id from v_EvnPS EvnPS with (nolock) 
					where EvnPS.EvnDirection_id = ED.EvnDirection_id
				) EvnPS
				outer apply (
					Select top 1 EvnUslugaPar_id from v_EvnUslugaPar EvnUslugaPar with (nolock) 
					where EvnUslugaPar.EvnDirection_id = ED.EvnDirection_id
				) EvnUslugaPar
				
			{$join}
			where ED.Person_id = :Person_id
				" . $filter . "
				--and EvnPL.EvnPL_id is null and EvnPS.EvnPS_id is null
		";
		//echo getDebugSQL($query, $queryParams);exit();
		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * МАРМ-версия
	 * Загрузка списка направлений из формы выбора направления
	 */
	function mLoadEvnDirectionList($data) {
		$filter = '';
		$join='';
		$queryParams = array(
			'Person_id' => $data['Person_id']
		);
		if ( $data['parentClass'] == 'EvnPS' ) {
			$filter .= ' and ED.DirType_id in (1, 2, 4, 5, 6)';
		}
		else if (getRegionNick() != 'kareliya' && $data['parentClass'] == 'EvnPL' ) {
			$filter .= ' and ED.DirType_id in (3, 11, 16) and ED.Lpu_did = :Lpu_did';
			$queryParams['Lpu_did'] = $data['Lpu_id'];
		}
		else if(getRegionNick() != 'kareliya' && !isset($data['MedStaffFact_id'])){
			$filter .= ' and ED.Lpu_did = :Lpu_did';
			$queryParams['Lpu_did'] = $data['Lpu_id'];
		}

		if ( isset($data['MedStaffFact_id'])&&$data['formType']=='polka') {
			$join.=' left join EvnQueue EQ with (nolock) on EQ.EvnDirection_id = ED.EvnDirection_id and EQ.EvnQueue_recDT is null and EQ.EvnQueue_failDT is null
				outer apply (select top 1 LpuSectionProfile_id from v_LpuSection ls with (nolock)
                where ls.LpuSection_id=:LpuSection_id) DLSP
				';
			$filter .= ' and (TTG.MedStaffFact_id=:MedStaffFact_id or (LSP.LpuSectionProfile_id = DLSP.LpuSectionProfile_id and EQ.EvnQueue_id is not null))';
			$filter .= ' and ED.DirType_id in (3, 11, 16)';
			if(getRegionNick() != 'kareliya') {
				$filter .= ' and ED.Lpu_did = :Lpu_did';
			}
			$queryParams['Lpu_did'] = $data['Lpu_id'];
			//$filter .=' and ED.EvnStatus_id not in(12,13,15)';
			$queryParams['MedStaffFact_id'] = $data['MedStaffFact_id'];
			$queryParams['LpuSection_id'] = $data['LpuSection_id'];

		}
		if ( isset($data['LpuSection_id'])&&$data['formType']=='stac') {
			$join.=' left join EvnQueue EQ with (nolock) on EQ.EvnDirection_id = ED.EvnDirection_id and EQ.EvnQueue_recDT is null and EQ.EvnQueue_failDT is null
				outer apply (select top 1 LpuSectionProfile_id from v_LpuSection ls with (nolock)
                where ls.LpuSection_id=:LpuSection_id) DLSP
				';
			$filter .= ' and (TTS.LpuSection_id=:LpuSection_id or (LSP.LpuSectionProfile_id = DLSP.LpuSectionProfile_id and EQ.EvnQueue_id is not null)) and ED.EvnStatus_id!=15';
			$filter .= ' and ED.DirType_id in (1, 2, 4, 5, 6)';
			//$filter .=' and ED.EvnStatus_id not in(12,13,15)';
			$queryParams['LpuSection_id'] = $data['LpuSection_id'];
		}

		if ($data['formType']=='par') {
			$filter .= ' and ED.MedService_id is null';
			//$filter .=' and ED.EvnStatus_id not in(12,13,15)';
			$queryParams['LpuSection_id'] = $data['LpuSection_id'];
		}

		if (!empty($data['DirType_id'])) {
			$filter .= ' and ED.DirType_id = :DirType_id';
			$queryParams['DirType_id'] = $data['DirType_id'];
		}

		if (!empty($data['EvnDirection_pid'])) {
			$filter .= ' and ED.EvnDirection_pid = :EvnDirection_pid';
			$queryParams['EvnDirection_pid'] = $data['EvnDirection_pid'];
		}

		// (Статус <> отменено, отклонено)
		$filter .= ' and ISNULL(ED.EvnStatus_id, 16) not in (12,13)';

		$query = "
			select
				ED.EvnDirection_id,
				ED.LpuSection_id,
				ED.MedPersonal_id,
				ED.Diag_id,
				DT.DirType_id,
				ED.EvnDirection_IsAuto,
				ED.EvnDirection_IsReceive,
				ED.Lpu_sid as Lpu_sid,
				ED.Lpu_id,
				isnull(ED.Org_sid,Lpu.Org_id) as Org_id,
				uc.UslugaComplex_id,
				uc.UslugaComplex_Name,
				RTRIM(Org.Org_Nick) as Lpu_Name,
				RTRIM(LSP.LpuSectionProfile_Name) as LpuSectionProfile_Name,
				ED.EvnDirection_Num,
				convert(varchar(10), ISNULL(ISNULL(TTG.TimetableGraf_begTime, TTP.TimetablePar_begTime), TTS.TimetableStac_setDate), 104) as Timetable_begTime,
				RTRIM(DT.DirType_Name) as DirType_Name,
				RTRIM(Diag.Diag_Code) + '. ' + RTRIM(Diag.Diag_Name) as Diag_Name,
				convert(varchar(10), ED.EvnDirection_setDate, 104) as EvnDirection_setDate,
				LpuD.Lpu_Nick as Lpu_dNick,
				case when (EvnUslugaPar.EvnUslugaPar_id is null and EvnPL.EvnPL_id is null and EvnPS.EvnPS_id is null) then 2 else 0 end as enabled,
				LB.LpuBuildingType_id
			from v_EvnDirection_all ED with (nolock)
				left join v_Lpu Lpu with (nolock) on Lpu.Lpu_id = ED.Lpu_sid
				left join v_Lpu LpuD with (nolock) on LpuD.Lpu_id = ED.Lpu_did
				left join v_Org Org with (nolock) on Org.Org_id = isnull(ED.Org_sid,Lpu.Org_id)
				left join DirType DT with (nolock) on DT.DirType_id = ED.DirType_id
				left join LpuSectionProfile LSP with (nolock) on LSP.LpuSectionProfile_id = ED.LpuSectionProfile_id
				left join v_LpuSection LS with (nolock) on LS.LpuSection_id = ED.LpuSection_id
				left join v_LpuUnit LU with (nolock) on LU.LpuUnit_id = LS.LpuUnit_id
				left join v_MedService MS with (nolock) on MS.MedService_id = ED.MedService_id
				left join v_LpuBuilding LB with (nolock) on LB.LpuBuilding_id = isnull(LU.LpuBuilding_id,MS.LpuBuilding_id)
				left join Diag with (nolock) on Diag.Diag_id = ED.Diag_id
				outer apply (
					select top 1
						ISNULL(EPLD.UslugaComplex_id,EPFDU.UslugaComplex_id) as UslugaComplex_id
					from
						v_EvnPrescrDirection EPD with(nolock)
						left join EvnPrescrLabDiag EPLD with(nolock) on EPLD.EvnPrescr_id = EPD.EvnPrescr_id
						left join EvnPrescrFuncDiag EPFD with (nolock) on EPFD.EvnPrescr_id = EPD.EvnPrescr_id
						left join EvnPrescrFuncDiagUsluga EPFDU with (nolock) on EPFDU.EvnPrescrFuncDiag_id = EPFD.EvnPrescrFuncDiag_id
					where
						EPD.EvnDirection_id = ed.EvnDirection_id
				) UCOMPL
				left join v_UslugaComplex UC with(nolock) on UC.UslugaComplex_id = UCOMPL.UslugaComplex_id
				outer apply (
					Select top 1 TimetableGraf_begTime, MedStaffFact_id from v_TimeTableGraf_lite TTG with (nolock) 
					where TTG.EvnDirection_id = ED.EvnDirection_id
				) TTG
				left join TimetablePar TTP with (nolock) on TTP.TimetablePar_id = ED.TimetablePar_id
				
				outer apply (
					Select top 1 TimetableStac_setDate, LpuSection_id from v_TimetableStac_lite TTS with (nolock)
					where TTS.EvnDirection_id = ED.EvnDirection_id
				) TTS
				outer apply (
					Select top 1 EvnPL_id from v_EvnPL EvnPL with (nolock) 
					where EvnPL.EvnDirection_id = ED.EvnDirection_id
				) EvnPL
				outer apply (
					Select top 1 EvnPS_id from v_EvnPS EvnPS with (nolock) 
					where EvnPS.EvnDirection_id = ED.EvnDirection_id
				) EvnPS
				outer apply (
					Select top 1 EvnUslugaPar_id from v_EvnUslugaPar EvnUslugaPar with (nolock) 
					where EvnUslugaPar.EvnDirection_id = ED.EvnDirection_id
				) EvnUslugaPar
				
			{$join}
			where ED.Person_id = :Person_id
				" . $filter . "
				--and EvnPL.EvnPL_id is null and EvnPS.EvnPS_id is null
		";
		//echo getDebugSQL($query, $queryParams);exit();
		$result = $this->db->query($query, $queryParams);

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
	function loadLpuBuildingGrid($data) {
		$query = "
			select
				-- select
				LpuBuilding_id,
				RTRIM(LpuBuilding_Name) as LpuBuilding_Name
				-- end select
			from
				-- from
				v_LpuBuilding LB with (nolock)
				inner join Lpu with (nolock) on Lpu.Lpu_id = LB.Lpu_id
					and Lpu.Lpu_id = :Lpu_id
					and (Lpu_endDate is null or Lpu_endDate > dbo.tzGetDate())
				-- end from
			where
				-- where
				(1 = 1)
				-- end where
			order by
				-- order by
				LpuBuilding_Name
				-- end order by
		";

		$queryParams = array(
			'Lpu_id' => $data['id']
		);

		$get_count_query = getCountSQLPH($query);
		$get_count_result = $this->db->query($get_count_query, $queryParams);

		if ( is_object($get_count_result) ) {
			$response['data'] = array();
			$response['totalCount'] = $get_count_result->result('array');
			$response['totalCount'] = $response['totalCount'][0]['cnt'];
		}
		else {
			return false;
		}

		$query = getLimitSQLPH($query, $data['start'], $data['limit']);
		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			$response['data'] = $result->result('array');
		}
		else {
			return false;
		}

		return $response;
	}

	/**
	 * @param $data
	 * @return bool
	 */
	function loadLpuGrid($data) {
		$query = "
			select
				-- select
				Lpu_id,
				RTRIM(Lpu_Nick) as Lpu_Nick,
				RTRIM(Lpu_Name) as Lpu_Name
				-- end select
			from
				-- from
				v_Lpu with (nolock)
				-- end from
			where
				-- where
				(1 = 1)
				and (Lpu_endDate is null or Lpu_endDate > dbo.tzGetDate())
				-- end where
			order by
				-- order by
				Lpu_Nick
				-- end order by
		";

		$get_count_query = getCountSQLPH($query);
		$get_count_result = $this->db->query($get_count_query);

		if ( is_object($get_count_result) ) {
			$response['data'] = array();
			$response['totalCount'] = $get_count_result->result('array');
			$response['totalCount'] = $response['totalCount'][0]['cnt'];
		}
		else {
			return false;
		}

		$query = getLimitSQLPH($query, $data['start'], $data['limit']);
		$result = $this->db->query($query);

		if ( is_object($result) ) {
			$response['data'] = $result->result('array');
		}
		else {
			return false;
		}

		return $response;
	}

	/**
	 * @param $data
	 * @return bool
	 */
	function loadLpuUnitGrid($data) {
		$query = "
			select
				-- select
				LU.LpuUnit_id,
				RTRIM(LU.LpuUnit_Name) as LpuUnit_Name
				-- end select
			from
				-- from
				LpuUnit LU with (nolock)
				inner join LpuBuilding LB with (nolock) on LB.LpuBuilding_id = LU.LpuBuilding_id
					and LB.LpuBuilding_id = :LpuBuilding_id
				-- end from
			where
				-- where
				(1 = 1)
				-- end where
			order by
				-- order by
				LU.LpuUnit_Name
				-- end order by
		";

		$queryParams = array(
			'LpuBuilding_id' => $data['id']
		);

		$get_count_query = getCountSQLPH($query);
		$get_count_result = $this->db->query($get_count_query, $queryParams);

		if ( is_object($get_count_result) ) {
			$response['data'] = array();
			$response['totalCount'] = $get_count_result->result('array');
			$response['totalCount'] = $response['totalCount'][0]['cnt'];
		}
		else {
			return false;
		}

		$query = getLimitSQLPH($query, $data['start'], $data['limit']);
		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			$response['data'] = $result->result('array');
		}
		else {
			return false;
		}

		return $response;
	}

	/**
	 * @param $data
	 * @return bool
	 */
	function loadMedStaffFactGrid($data) {
		$query = "
			select
				-- select
				MSF.MedStaffFact_id,
				RTRIM(MSF.Person_FIO) as MedPersonal_Fio,
				RTRIM(LS.LpuSection_Name) as LpuSection_Name,
				'' as LpuRegion_Name,
				RTRIM(LSP.LpuSectionProfile_Name) as LpuSectionProfile_Name
				-- end select
			from
				-- from
				v_MedStaffFact MSF with (nolock)
				inner join LpuSection LS with (nolock) on LS.LpuSection_id = MSF.LpuSection_id
				inner join LpuSectionProfile LSP with (nolock) on LSP.LpuSectionProfile_id = LS.LpuSectionProfile_id
				-- end from
			where
				-- where
				(1 = 1)
				and MSF.LpuUnit_id = :LpuUnit_id
				-- end where
			order by
				-- order by
				MSF.Person_FIO,
				LS.LpuSection_Name,
				LSP.LpuSectionProfile_Name
				-- end order by
		";

		$queryParams = array(
			'LpuUnit_id' => $data['id']
		);

		$get_count_query = getCountSQLPH($query);
		$get_count_result = $this->db->query($get_count_query, $queryParams);

		if ( is_object($get_count_result) ) {
			$response['data'] = array();
			$response['totalCount'] = $get_count_result->result('array');
			$response['totalCount'] = $response['totalCount'][0]['cnt'];
		}
		else {
			return false;
		}

		$query = getLimitSQLPH($query, $data['start'], $data['limit']);
		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			$response['data'] = $result->result('array');
		}
		else {
			return false;
		}

		return $response;
	}

	/**
	 * @param array $data
	 * @return array
	 * @throws Exception
	 */
	function beforeRedirectEvnDirectionLis($data) {
		if (800 == $data['redirectEvnDirection']) {
			if (!empty($data['oldTimetableMedService_id'])) {
				$old_tms = $this->getFirstRowFromQuery("
					select top 1
						TMS.TimetableMedService_id,
						TMS.TimetableMedService_Day,
						TMS.MedService_id
					from
						v_TimetableMedService_lite TMS with(nolock)
						left join v_MedService MS with(nolock) on MS.MedService_id = TMS.TimetableMedService_id
						left join v_MedServiceType MST with(nolock) on MST.MedServiceType_id = MS.MedServiceType_id
					where
						TMS.TimetableMedService_id = :oldTimetableMedService_id
				", $data);

				// освобождаю бирку
				$tmp = $this->swUpdate('TimetableMedService', array(
					'TimetableMedService_id' => $old_tms['TimetableMedService_id'],
					'pmUser_id' => $data['pmUser_id'],
					'EvnDirection_id' => null,
					'Evn_id' => null,
					'Person_id' => null,
					'RecClass_id' => null,
					'TimetableMedService_factTime' => null,
				), true);
				if (empty($tmp) || false == is_array($tmp)) {
					throw new Exception('Ошибка запроса к БД', 500);
				}
				if (false == empty($tmp[0]['Error_Msg'])) {
					throw new Exception($tmp[0]['Error_Msg'], 500);
				}
				// Обновляем кэш по дню
				$tmp = $this->execCommonSP('p_MedServiceDay_recount', array(
					'MedService_id' => $old_tms['MedService_id'],
					'Day_id' => $old_tms['TimetableMedService_Day'],
					'pmUser_id' => $data['pmUser_id'],
				), 'array_assoc');
				if (empty($tmp)) {
					throw new Exception('Ошибка запроса к БД', 500);
				}
				if (isset($tmp['Error_Msg'])) {
					throw new Exception($tmp['Error_Msg'], 500);
				}
				// Заносим изменения бирки в историю
				$tmp = $this->execCommonSP('p_AddTTMSToHistory', array(
					'TimetableMedService_id' => $old_tms['TimetableMedService_id'],
					'TimeTableActionType_id' => 3, // Освобождение бирки
					'pmUser_id' => $data['pmUser_id'],
				), 'array_assoc');
				if (empty($tmp)) {
					throw new Exception('Ошибка запроса к БД', 500);
				}
				if (isset($tmp['Error_Msg'])) {
					throw new Exception($tmp['Error_Msg'], 500);
				}
			}
		}

		return array(array(
			'success' => true
		));
	}

	/**
	 * Перед перезаписью / перенаправлением
	 */
	function beforeRedirectEvnDirection(&$data) {
		// надо очистить все дургие бирки с этим же направлением. (перенеправление/перезапись)
		if (!empty($data['EvnDirection_id']) && (!empty($data['toQueue']) || !empty($data['TimetableGraf_id']) || !empty($data['TimetableStac_id']) || !empty($data['TimetableMedService_id']) || !empty($data['TimetableResource_id']))) {
			// получаем параметры направления которые не должны меняться в направлении
			$query = "
				select top 1 
				TTS.TimetableStac_id, 
				TTS.TimetableStac_Day, 
				LSD.LpuSection_id as LpuSection_did,
				LUD.LpuUnit_id as LpuUnit_did,
				TTR.TimetableResource_id,
				ED.Resource_id,
				TTR.TimetableResource_Day,
				TMS.TimetableMedService_id,
				ED.MedService_id,
				TMS.TimetableMedService_Day,
				TMS.UslugaComplexMedService_id, 
				TTG.TimetableGraf_id, 
				TTG.TimetableGraf_Day,
				MSFD.MedStaffFact_id as MedStaffFact_did,
				isnull(MSFD.MedPersonal_id, ED.MedPersonal_did) as MedPersonal_did, 
				ED.EvnDirection_id, 
				ED.EvnDirection_pid, 
				ED.EvnDirection_setDT,
				ED.Lpu_id, 
				ED.Person_id, 
				ED.PersonEvn_id, 
				ED.Server_id, 
				ED.Morbus_id, 
				ED.EvnStatus_id,
				ED.LpuSectionProfile_id, 
				ED.PrehospType_did, 
				ED.Lpu_did, 
				ED.Lpu_sid, 
				ED.Org_sid, 
				ED.LpuSection_id, 
				ED.MedPersonal_id, 
				ED.MedStaffFact_id, 
				ED.MedPersonal_zid, 
				ED.DirType_id, 
				ED.EvnDirection_Descr, 
				ED.EvnDirection_Num, 
				ED.Diag_id, 
				ED.EvnDirection_desDT, 
				ED.EvnDirection_IsAuto, 
				ED.EvnDirection_IsCito, 
				ED.Post_id, 
				ED.PrehospDirect_id,
				EQ.EvnQueue_id,
				ED.EvnDirection_Ser, 
				ED.EvnDirection_IsConfirmed, 
				ED.pmUser_confID, 
				ED.EvnDirection_confDT, 
				ED.RemoteConsultCause_id,
				ED.EvnDirection_IsNeedOper, 
				ED.ARMType_id
				from
					v_EvnDirection_all ED (nolock)
					left join v_EvnQueue EQ with (nolock) on EQ.EvnDirection_id = ED.EvnDirection_id
					left join v_TimetableMedService_lite TMS with (nolock) on TMS.EvnDirection_id = ED.EvnDirection_id
					left join v_TimetableResource_lite TTR with (nolock) on TTR.EvnDirection_id = ED.EvnDirection_id
					left join v_TimetableStac_lite TTS with (nolock) on ED.EvnDirection_id = TTS.EvnDirection_id
					left join v_TimeTableGraf_lite TTG with (nolock) on TTG.EvnDirection_id = ED.EvnDirection_id and TTG.MedStaffFact_id is not null
					left join v_MedStaffFact MSFD with (nolock) on MSFD.MedStaffFact_id = TTG.MedStaffFact_id AND MSFD.Lpu_id = ED.Lpu_did
					left join v_MedService MS with (nolock) on ED.Medservice_id = MS.Medservice_id AND MS.Lpu_id = ED.Lpu_did
					left join v_LpuSection LSD with (nolock) on coalesce(ED.LpuSection_did, MSFD.LpuSection_id, TTS.LpuSection_id, MS.LpuSection_id) = LSD.LpuSection_id AND LSD.Lpu_id = ED.Lpu_did
					left join v_LpuUnit LUD with (nolock) on isnull(ED.LpuUnit_did, LSD.LpuUnit_id) = LUD.LpuUnit_id AND LUD.Lpu_id = ED.Lpu_did
				where
					ED.EvnDirection_id = :EvnDirection_id
			";
			$result = $this->db->query($query, array(
				'EvnDirection_id' => $data['EvnDirection_id']
			));
			$ed_data = false;
			if (is_object($result)) {
				$resp = $result->result('array');
				if (!empty($resp[0]['EvnDirection_id'])) {
					$ed_data = $resp[0];
				}
			}
			if (false == $ed_data) {
				return array(array('Error_Msg' => 'Ошибка получения данных направления'));
			}

			if (800 == $data['redirectEvnDirection']) {
				if (17 != $ed_data['EvnStatus_id']) {
					return array(array('Error_Msg' => 'Направление должно быть в статусе Записано'));
				}
				// параметры, которые не должны меняться в направлении при перезаписи (ничего, кроме бирки)
				$data['EvnDirection_pid'] = $ed_data['EvnDirection_pid'];
				$data['EvnDirection_setDate'] = ($ed_data['EvnDirection_setDT'] instanceof DateTime) ? $ed_data['EvnDirection_setDT']->format('Y-m-d') : null;
				$data['Person_id'] = $ed_data['Person_id'];
				$data['PersonEvn_id'] = $ed_data['PersonEvn_id'];
				$data['Server_id'] = $ed_data['Server_id'];
				$data['Morbus_id'] = $ed_data['Morbus_id'];
				$data['EvnQueue_id'] = $ed_data['EvnQueue_id'];
				$data['MedService_id'] = $ed_data['MedService_id'];
				$data['LpuSectionProfile_id'] = $ed_data['LpuSectionProfile_id'];
				$data['PrehospType_did'] = $ed_data['PrehospType_did'];
				$data['Lpu_did'] = $ed_data['Lpu_did'];
				$data['LpuUnit_did'] = $ed_data['LpuUnit_did'];
				$data['LpuSection_did'] = $ed_data['LpuSection_did'];
				$data['MedPersonal_did'] = $ed_data['MedPersonal_did'];
				$data['Lpu_id'] = $ed_data['Lpu_id'];
				$data['Lpu_sid'] = $ed_data['Lpu_sid'];
				$data['Org_sid'] = $ed_data['Org_sid'];
				$data['LpuSection_id'] = $ed_data['LpuSection_id'];
				$data['MedPersonal_id'] = $ed_data['MedPersonal_id'];
				$data['MedStaffFact_id'] = $ed_data['MedStaffFact_id'];
				$data['MedPersonal_zid'] = $ed_data['MedPersonal_zid'];
				$data['DirType_id'] = $ed_data['DirType_id'];
				$data['EvnDirection_Descr'] = $ed_data['EvnDirection_Descr'];
				$data['EvnDirection_Num'] = $ed_data['EvnDirection_Num'];
				$data['Diag_id'] = $ed_data['Diag_id'];
				$data['EvnDirection_desDT'] = ($ed_data['EvnDirection_desDT'] instanceof DateTime) ? $ed_data['EvnDirection_desDT']->format('Y-m-d') : null;
				$data['EvnDirection_IsAuto'] = $ed_data['EvnDirection_IsAuto'];
				$data['EvnDirection_IsCito'] = $ed_data['EvnDirection_IsCito'];
				$data['PrehospDirect_id'] = $ed_data['PrehospDirect_id'];
				$data['DirFailType_id'] = null;
				$data['EvnDirection_failDT'] = null;
				$data['pmUser_failID'] = null;
				$data['EvnDirection_Ser'] = $ed_data['EvnDirection_Ser'];
				$data['EvnDirection_IsConfirmed'] = $ed_data['EvnDirection_IsConfirmed'];
				$data['pmUser_confID'] = $ed_data['pmUser_confID'];
				$data['EvnDirection_confDT'] = ($ed_data['EvnDirection_confDT'] instanceof DateTime) ? $ed_data['EvnDirection_confDT']->format('Y-m-d') : null;
				$data['RemoteConsultCause_id'] = $ed_data['RemoteConsultCause_id'];
				$data['EvnDirection_IsNeedOper'] = $ed_data['EvnDirection_IsNeedOper'];
				//$data['ARMType_id'] = $ed_data['ARMType_id'];
				$data['EvnStatus_id'] = $ed_data['EvnStatus_id'];
				$isOk = false;
				//throw new Exception($data['TimetableGraf_id'] . ' ' . $ed_data['TimetableGraf_id'], 500);
				if (!empty($ed_data['TimetableGraf_id']) && !empty($data['TimetableGraf_id']) && $data['TimetableGraf_id'] != $ed_data['TimetableGraf_id']) {
					// освобождаю бирку
					$tmp = $this->swUpdate('TimetableGraf', array(
						'TimetableGraf_id' => $ed_data['TimetableGraf_id'],
						'pmUser_id' => $data['pmUser_id'],
						'EvnDirection_id' => null,
						'Evn_id' => null,
						'Person_id' => null,
						'RecClass_id' => null,
						'TimetableGraf_factTime' => null,
						'TimetableGraf_IsModerated' => null,
						'RecMethodType_id' => null
					), true);
					if (empty($tmp) || false == is_array($tmp)) {
						throw new Exception('Ошибка запроса к БД', 500);
					}
					if (false == empty($tmp[0]['Error_Msg'])) {
						throw new Exception($tmp[0]['Error_Msg'], 500);
					}
					// Обновляем кэш по дню
					$tmp = $this->execCommonSP('p_MedPersonalDay_recount', array(
						'MedStaffFact_id' => $ed_data['MedStaffFact_did'],
						'Day_id' => $ed_data['TimetableGraf_Day'],
						'pmUser_id' => $data['pmUser_id'],
					), 'array_assoc');
					if (empty($tmp)) {
						throw new Exception('Ошибка запроса к БД', 500);
					}
					if (isset($tmp['Error_Msg'])) {
						throw new Exception($tmp['Error_Msg'], 500);
					}
					// Заносим изменения бирки в историю
					$tmp = $this->execCommonSP('p_AddTTGToHistory', array(
						'TimetableGraf_id' => $ed_data['TimetableGraf_id'],
						'TimeTableGrafAction_id' => 3, // Освобождение бирки
						'pmUser_id' => $data['pmUser_id'],
					), 'array_assoc');
					if (empty($tmp)) {
						throw new Exception('Ошибка запроса к БД', 500);
					}
					if (isset($tmp['Error_Msg'])) {
						throw new Exception($tmp['Error_Msg'], 500);
					}
					$isOk = true;
				}
				if (!empty($ed_data['TimetableMedService_id']) && !empty($data['TimetableMedService_id']) && $data['TimetableMedService_id'] != $ed_data['TimetableMedService_id']) {
					// освобождаю бирку
					$tmp = $this->swUpdate('TimetableMedService', array(
						'TimetableMedService_id' => $ed_data['TimetableMedService_id'],
						'pmUser_id' => $data['pmUser_id'],
						'EvnDirection_id' => null,
						'Evn_id' => null,
						'Person_id' => null,
						'RecClass_id' => null,
						'TimetableMedService_factTime' => null,
					), true);
					if (empty($tmp) || false == is_array($tmp)) {
						throw new Exception('Ошибка запроса к БД', 500);
					}
					if (false == empty($tmp[0]['Error_Msg'])) {
						throw new Exception($tmp[0]['Error_Msg'], 500);
					}
					// Обновляем кэш по дню
					if (empty($ed_data['UslugaComplexMedService_id'])) {
						$tmp = $this->execCommonSP('p_MedServiceDay_recount', array(
							'MedService_id' => $ed_data['MedService_id'],
							'Day_id' => $ed_data['TimetableMedService_Day'],
							'pmUser_id' => $data['pmUser_id'],
						), 'array_assoc');
					} else {
						$tmp = $this->execCommonSP('p_MedServiceUslugaComplexDay_recount', array(
							'UslugaComplexMedService_id' => $ed_data['UslugaComplexMedService_id'],
							'Day_id' => $ed_data['TimetableMedService_Day'],
							'pmUser_id' => $data['pmUser_id'],
						), 'array_assoc');
					}
					if (empty($tmp)) {
						throw new Exception('Ошибка запроса к БД', 500);
					}
					if (isset($tmp['Error_Msg'])) {
						throw new Exception($tmp['Error_Msg'], 500);
					}
					// Заносим изменения бирки в историю
					$tmp = $this->execCommonSP('p_AddTTMSToHistory', array(
						'TimetableMedService_id' => $ed_data['TimetableMedService_id'],
						'TimeTableActionType_id' => 3, // Освобождение бирки
						'pmUser_id' => $data['pmUser_id'],
					), 'array_assoc');
					if (empty($tmp)) {
						throw new Exception('Ошибка запроса к БД', 500);
					}
					if (isset($tmp['Error_Msg'])) {
						throw new Exception($tmp['Error_Msg'], 500);
					}
					$isOk = true;
				}
				if (!empty($ed_data['TimetableResource_id']) && !empty($data['TimetableResource_id']) && $data['TimetableResource_id'] != $ed_data['TimetableResource_id']) {
					// освобождаю бирку
					$tmp = $this->swUpdate('TimetableResource', array(
						'TimetableResource_id' => $ed_data['TimetableResource_id'],
						'pmUser_id' => $data['pmUser_id'],
						'EvnDirection_id' => null,
						'Evn_id' => null,
						'Person_id' => null,
						'RecClass_id' => null,
						//'TimetableResource_factTime' => null,
					), true);
					if (empty($tmp) || false == is_array($tmp)) {
						throw new Exception('Ошибка запроса к БД', 500);
					}
					if (false == empty($tmp[0]['Error_Msg'])) {
						throw new Exception($tmp[0]['Error_Msg'], 500);
					}
					// Обновляем кэш по дню
					$tmp = $this->execCommonSP('p_ResourceDay_recount', array(
						'Resource_id' => $ed_data['Resource_id'],
						'Day_id' => $ed_data['TimetableResource_Day'],
						'pmUser_id' => $data['pmUser_id'],
					), 'array_assoc');
					if (empty($tmp)) {
						throw new Exception('Ошибка запроса к БД', 500);
					}
					if (isset($tmp['Error_Msg'])) {
						throw new Exception($tmp['Error_Msg'], 500);
					}
					// Заносим изменения бирки в историю
					$tmp = $this->execCommonSP('p_AddTTRToHistory', array(
						'TimetableResource_id' => $ed_data['TimetableResource_id'],
						'TimeTableActionType_id' => 3, // Освобождение бирки
						'pmUser_id' => $data['pmUser_id'],
					), 'array_assoc');
					if (empty($tmp)) {
						throw new Exception('Ошибка запроса к БД', 500);
					}
					if (isset($tmp['Error_Msg'])) {
						throw new Exception($tmp['Error_Msg'], 500);
					}
					$isOk = true;
				}
				if (!empty($ed_data['TimetableStac_id']) && !empty($data['TimetableStac_id']) && $data['TimetableStac_id'] != $ed_data['TimetableStac_id']) {
					// освобождаю бирку
					$tmp = $this->swUpdate('TimetableStac', array(
						'TimetableStac_id' => $ed_data['TimetableStac_id'],
						'pmUser_id' => $data['pmUser_id'],
						'EvnDirection_id' => null,
						'Evn_id' => null,
						'Person_id' => null,
						'RecClass_id' => null,
					), true);
					if (empty($tmp) || false == is_array($tmp)) {
						throw new Exception('Ошибка запроса к БД', 500);
					}
					if (false == empty($tmp[0]['Error_Msg'])) {
						throw new Exception($tmp[0]['Error_Msg'], 500);
					}
					// Обновляем кэш по дню
					$tmp = $this->execCommonSP('p_LpuSectionDay_recount', array(
						'LpuSection_id' => $ed_data['LpuSection_did'],
						'Day_id' => $ed_data['TimetableStac_Day'],
						'pmUser_id' => $data['pmUser_id'],
					), 'array_assoc');
					if (empty($tmp)) {
						throw new Exception('Ошибка запроса к БД', 500);
					}
					if (isset($tmp['Error_Msg'])) {
						throw new Exception($tmp['Error_Msg'], 500);
					}
					// Заносим изменения бирки в историю
					$tmp = $this->execCommonSP('p_AddTTSToHistory', array(
						'TimetableStac_id' => $ed_data['TimetableStac_id'],
						'TimeTableActionType_id' => 3, // Освобождение бирки
						'pmUser_id' => $data['pmUser_id'],
					), 'array_assoc');
					if (empty($tmp)) {
						throw new Exception('Ошибка запроса к БД', 500);
					}
					if (isset($tmp['Error_Msg'])) {
						throw new Exception($tmp['Error_Msg'], 500);
					}
					$isOk = true;
				}
				if ( false === $isOk ) {
					return array(array('Error_Msg' => 'Ошибка при перезаписи'));
				}
			}
			if (700 == $data['redirectEvnDirection']) {
				// параметры, которые не должны меняться в направлении
				$data['Lpu_id'] = $ed_data['Lpu_id'];
				$data['Lpu_sid'] = $ed_data['Lpu_sid'];
				$data['Org_sid'] = $ed_data['Org_sid'];
				$data['EvnDirection_pid'] = $ed_data['EvnDirection_pid'];
				$data['EvnStatus_id'] = $ed_data['EvnStatus_id'];
			}

			// создаём объект очереди если его нет
			if (700 == $data['redirectEvnDirection'] && !empty($data['toQueue']) && empty($data['EvnQueue_id'])) {
				$data['onlySaveDirection'] = true;
				$data['createdEvnQueue'] = true;
				$this->load->model('EvnQueue_model');
				$result = $this->EvnQueue_model->doSave(array(
					'scenario' => self::SCENARIO_DO_SAVE,
					'session' => $this->sessionParams,
					'EvnQueue_id' => null,
					'Lpu_id' => $data['Lpu_id'],
					'Server_id' => $data['Server_id'],
					'PersonEvn_id' => $data['PersonEvn_id'],
					'EvnQueue_setDT' => $data['EvnDirection_setDT'],
					'LpuSectionProfile_did' => $data['LpuSectionProfile_id'],
					'LpuUnit_did' => (!empty($data['LpuUnit_did']))?$data['LpuUnit_did']:null,
					'EvnDirection_id' => $data['EvnDirection_id'],
					'MedService_did' => $data['MedService_id'],
					'MedPersonal_did' => (!empty($data['MedPersonal_did']))? $data['MedPersonal_did']: null,
					'LpuSection_did' => $data['LpuSection_did']
				), false);

				if (!empty($result['EvnQueue_id'])) {
					$data['EvnQueue_id'] = $result['EvnQueue_id'];
				} else {
					return array(array('Error_Msg' => 'Ошибка сохранения объекта очереди'));
				}
			}

			if (700 == $data['redirectEvnDirection'] && empty($data['createdEvnQueue'])
				&& !empty($data['toQueue']) && !empty($data['EvnQueue_id'])
			) {
				$data['onlySaveDirection'] = true; // отключаю Queue_model::ReceptionFromQueue
				// объект постановки в очередь должен обновиться с помощью p_EvnQueue_upd
				$this->load->model('EvnQueue_model');
				$result = $this->EvnQueue_model->doSave(array(
					'scenario' => self::SCENARIO_DO_SAVE,
					'session' => $this->sessionParams,
					'EvnQueue_id' => $data['EvnQueue_id'],
					'Lpu_id' => $data['Lpu_id'],
					'Server_id' => $data['Server_id'],
					'PersonEvn_id' => $data['PersonEvn_id'],
					'EvnQueue_setDT' => $data['EvnDirection_setDT'],
					'LpuSectionProfile_did' => $data['LpuSectionProfile_id'],
					'LpuUnit_did' => (!empty($data['LpuUnit_did']))?$data['LpuUnit_did']:null,
					'EvnDirection_id' => $data['EvnDirection_id'],
					'MedService_did' => $data['MedService_id'],
					'Resource_did' => $data['Resource_id'],
					'MedPersonal_did' => (!empty($data['MedPersonal_did']))? $data['MedPersonal_did']: null,
					'LpuSection_did' => $data['LpuSection_did']
				), false);
				return array($result);
			}
		}
		return array(array('Error_Msg' => null));
	}

	/**
	 * Включение назначения в существующее направление
	 */
	function includeEvnPrescrInDirection($data) {
		//$this->beginTransaction();
		// добавляем связь в EvnPrescrDirection

		$this->load->model('EvnPrescr_model', 'EvnPrescr_model');
		$this->EvnPrescr_model->directEvnPrescr($data);

		// нам понядобится EvnLabRequest_id, MedService_id, PersonEvn_id, Server_id
		$query = "
			select top 1
				ed.EvnDirection_id,
				ed.MedService_id
			from
				v_EvnPrescrDirection epd (nolock)
				inner join v_EvnDirection_all ed (nolock) on ed.EvnDirection_id = epd.EvnDirection_id
				inner join v_EvnStatus es (nolock) on es.EvnStatus_id = ed.EvnStatus_id
			where
				epd.EvnPrescr_id = :EvnPrescr_id
				and es.EvnStatus_SysNick in ('Queued', 'DirZap')
		";
		$resp = $this->queryResult($query, array(
			'EvnPrescr_id' => $data['EvnPrescr_id']
		));

        $resp = $resp[0];

        $res = $this->getEvnLabSampleAndRequest($resp);
        if (!is_array($res)) {
            throw new Exception('Ошибка получения данных по заявке', 500);
        }

		$resp = array_merge($resp, $res);
		if (empty($resp['EvnLabRequest_id'])) {
			$this->rollbackTransaction();
			return array('Error_Msg' => 'Ошибка получения данных по заявке');
		} else {
			$data['EvnLabRequest_id'] = $resp['EvnLabRequest_id'];
			$data['MedService_id'] = $resp['MedService_id'];
			$data['PersonEvn_id'] = $resp['PersonEvn_id'];
			$data['Server_id'] = $resp['Server_id'];
			$data['EvnLabSample_id'] = $resp['EvnLabSample_id'];
		}

		// добавляем исследование в заявку
		if (!empty($data['UslugaComplex_id'])) {
			if (empty($data['EvnLabSample_id'])) {
				$dt = [
					'EvnLabRequest_id' => $data['EvnLabRequest_id'],
					'RefSample_id' => null,
					'Lpu_id' => $data['Lpu_id'],
					'Server_id' => $data['Server_id'],
					'PersonEvn_id' => $data['PersonEvn_id'],
					'MedService_id' => $data['MedService_id'],
					'pmUser_id' => $data['pmUser_id']
				];

                $this->load->model('EvnLabSample_model');
                $resp_labsample = $this->EvnLabSample_model->saveLabSample($data);
                if (!$this->isSuccessful($resp_labsample)) {
                    throw new Exception($resp_labsample[0]['Error_Msg'], 500);
                }
                if (!empty($resp_labsample[0]['EvnLabSample_id'])) {
                    $data['EvnLabSample_id'] = $resp_labsample[0]['EvnLabSample_id'];
                }
			}

			if (!empty($data['EvnLabSample_id'])) {
				$data['PayType_id'] = $this->getFirstResultFromQuery("
					select top 1
						PayType_id
					from
						v_PayType (nolock)
					where
						PayType_SysNick = :PayType_SysNick
				", array('PayType_SysNick' => getPayTypeSysNickOMS()));
				if (empty($data['PayType_id'])) {
					$data['PayType_id'] = null;
				}

                // исследование
                $params = $data;
                $params['RefSample_id'] = null;
                $checked_tests = $data['checked'];
                $orderparams = json_decode(toUTF($data['order']), true);
                $params['researches'][] = $orderparams['UslugaComplexMedService_id'];

                // сохраняем услуги и тесты, разбиваем на пробы по биоматериалу
                $this->load->model('EvnLabSample_model');
                $this->EvnLabSample_model->saveLabSampleResearches($params, $checked_tests);

			}
		}

		// кэшируем количество тестов
		if ($this->usePostgreLis) {
			$res = $this->lis->POST('EvnLabRequest/ReCacheLabRequestUslugaCount', [
			'EvnLabRequest_id' => $data['EvnLabRequest_id'],
			'pmUser_id' => $data['pmUser_id']
			], 'single');
			if (!$this->isSuccessful($res)) {
				throw new Exception($res['Error_Msg'], 500);
			}
		} else {
			$this->load->model('EvnLabRequest_model');
			$this->EvnLabRequest_model->ReCacheLabRequestUslugaCount([
				'EvnLabRequest_id' => $data['EvnLabRequest_id'],
				'pmUser_id' => $data['pmUser_id']
			]);
		}

		// кэшируем статус проб в заявке
		if ($this->usePostgreLis) {
			$res = $this->lis->POST('EvnLabRequest/ReCacheLabRequestSampleStatusType', [
				'EvnLabRequest_id' => $data['EvnLabRequest_id'],
			'pmUser_id' => $data['pmUser_id']
			], 'single');
			if (!$this->isSuccessful($res)) {
				throw new Exception($res['Error_Msg'], 500);
			}
		} else {
			$this->load->model('EvnLabRequest_model');
			$this->EvnLabRequest_model->ReCacheLabRequestSampleStatusType([
				'EvnLabRequest_id' => $data['EvnLabRequest_id'],
				'pmUser_id' => $data['pmUser_id']
			]);
		}

		return array('Error_Msg' => '');
	}

	/**
	 * Поиск назначенных услуг по Prescr_id
	 */
	function getUslugaComplexByPrescrId($prescr_id) {
		$params = array(
			'prescr_id' => $prescr_id
		);
		$query = "
			select 
				EvnPrescrLabDiagUsluga_id,
				UslugaComplex_id
			from
				v_EvnPrescrLabDiagUsluga
			where EvnPrescrLabDiag_id = :prescr_id
		";
		return $this->queryResult($query, $params);
	}
	/**
	 * Сохранение услуги
	 */
	function saveEvnDirectionUslugaComplex($data) {
		$this->load->database('default', true);
		if (!empty($data['EvnDirectionUslugaComplex_id'])) {
			$proc = 'p_EvnDirectionUslugaComplex_upd';
		} else {
			$proc = 'p_EvnDirectionUslugaComplex_ins';
			$data['EvnDirectionUslugaComplex_id'] = null;
		}

		$query = "
			declare
				@Res bigint,
				@ErrCode bigint,
				@ErrMsg varchar(4000);
			set @Res = :EvnDirectionUslugaComplex_id;

			exec {$proc}
				@EvnDirectionUslugaComplex_id = @Res output,
				@EvnDirectionUslugaComplex_pid = :EvnDirectionUslugaComplex_pid,
				@EvnDirection_id = :EvnDirection_id,
				@UslugaComplex_id = :UslugaComplex_id,
				@pmUser_id = :pmUser_id
			select @Res as EvnDirectionUslugaComplex_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg;
		";

		return $this->queryResult($query, array(
			'EvnDirectionUslugaComplex_id' => $data['EvnDirectionUslugaComplex_id'],
			'EvnDirectionUslugaComplex_pid' => !empty($data['EvnDirectionUslugaComplex_pid'])?$data['EvnDirectionUslugaComplex_pid']:null,
			'EvnDirection_id' => $data['EvnDirection_id'],
			'UslugaComplex_id' => $data['UslugaComplex_id'],
			'pmUser_id' => $data['pmUser_id']
		));
	}

	/**
	 * Создаем заявку на исследование
	 */
	function makeEvnLabRequest($data) {

		// проверяем что заявка ещё не создана
		$EvnLabRequest_id = $this->getFirstResultFromQuery("
			select
				EvnLabRequest_id
			from
				v_EvnLabRequest elr (nolock)
			where
				elr.EvnDirection_id = :EvnDirection_id
		", $data);

		if (empty($EvnLabRequest_id)) {

			// если указана бирка, то берем время и место выполнения из неё
			if (!empty($data['TimetableMedService_id'])) {

				$data['EvnLabRequest_prmTime'] = null;

				$query = "
					select top 1
						ttms.TimetableMedService_id,
						ms_pzm.MedService_id as MedService_pzid,
						convert(varchar, ttms.TimetableMedService_begTime, 120) as EvnLabRequest_prmTime
					from
						v_TimetableMedService_lite ttms (nolock)
						left join v_UslugaComplexMedService ucms (nolock) on ucms.UslugaComplexMedService_id = ttms.UslugaComplexMedService_id
						outer apply(
							select top 1
								ms.MedService_id
							from
								v_MedService ms (nolock)
								inner join v_MedServiceType mst (nolock) on mst.MedServiceType_id = ms.MedServiceType_id
							where
								ms.MedService_id = ISNULL(ucms.MedService_id, ttms.MedService_id)
								and mst.MedServiceType_SysNick = 'pzm'
						) ms_pzm
					where
						ttms.TimetableMedService_id = :TimetableMedService_id
				";

				$result = $this->db->query($query, $data);
				if (is_object($result)) {
					$resp = $result->result('array');
					if (!empty($resp[0]['TimetableMedService_id'])) {

						$data['MedService_pzid'] = $resp[0]['MedService_pzid'];
						$data['EvnLabRequest_prmTime'] = $resp[0]['EvnLabRequest_prmTime'];

						//спорный метод...отключу пока
						//$edsl_id = $this->saveEvnDirectionServiceLink($data);
					}
				}
			}

			if (empty($data['MedService_pzid'])) { $data['MedService_pzid'] = null; }

			$data['EvnLabRequest_id'] = null; // создаём заявку
			$query = "
				declare
					@EvnLabRequest_id bigint,
					@ErrCode int,
					@ErrMessage varchar(4000);
				set @EvnLabRequest_id = :EvnLabRequest_id;
				exec p_EvnLabRequest_ins
					@EvnLabRequest_id = @EvnLabRequest_id output,
					@Server_id = :Server_id,
					@PersonEvn_id = :PersonEvn_id,
					@Lpu_id = :Lpu_id,
					@PayType_id = :PayType_id,
					@EvnDirection_id = :EvnDirection_id,
					@MedService_id = :MedService_id,
					@MedService_sid = :MedService_pzid,
					@EvnLabRequest_prmTime = :EvnLabRequest_prmTime,
					@EvnLabRequest_IsCito = :EvnDirection_IsCito,
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMessage output;
				select @EvnLabRequest_id as EvnLabRequest_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			";

			$result = $this->db->query($query, array(
				'EvnLabRequest_id' => $data['EvnLabRequest_id'],
				'Server_id' => $data['Server_id'],
				'PersonEvn_id' => $data['PersonEvn_id'],
				'Lpu_id' => $data['Lpu_id'],
				'PayType_id' => $data['PayType_id'],
				'EvnDirection_id' => $data['EvnDirection_id'],
				'MedService_id' => $data['MedService_id'],
				'MedService_pzid' => $data['MedService_pzid'],
				'EvnLabRequest_prmTime' => !empty($data['EvnLabRequest_prmTime']) ? $data['EvnLabRequest_prmTime'] : null,
				'EvnDirection_IsCito' => empty($data['EvnDirection_IsCito']) ? NULL : $data['EvnDirection_IsCito'],
				'pmUser_id' => $data['pmUser_id']
			));

			if (is_object($result)) {

				$resp = $result->result('array');
				if (!empty($resp[0]['EvnLabRequest_id'])) {

					$EvnLabRequest_id = $resp[0]['EvnLabRequest_id'];

					// сразу добавляем пробу и исследование
					$this->load->model('EvnLabRequest_model');

					if (!empty($data['UslugaComplex_id'])) {

						$this->load->model('EvnLabSample_model');
						$resp_labsample = $this->EvnLabSample_model->saveLabSample(array(
							'EvnLabRequest_id' => $resp[0]['EvnLabRequest_id'],
							'RefSample_id' => null,
							'Lpu_id' => $data['Lpu_id'],
							'Server_id' => $data['Server_id'],
							'PersonEvn_id' => $data['PersonEvn_id'],
							'MedService_id' => $data['MedService_id'],
							'pmUser_id' => $data['pmUser_id']
						));

						if (!empty($resp_labsample[0]['EvnLabSample_id'])) {

							if (empty($data['PayType_id'])) {

								$data['PayType_id'] = $this->getFirstResultFromQuery("
									select top 1
										PayType_id
									from
										v_PayType (nolock)
									where
										PayType_SysNick = :PayType_SysNick
								", array('PayType_SysNick' => getPayTypeSysNickOMS()));

								if (empty($data['PayType_id'])) {$data['PayType_id'] = null;}
							}

                            // исследование
                            $params = $data;
                            $params['EvnLabRequest_id'] = $resp[0]['EvnLabRequest_id'];
                            $params['EvnLabSample_id'] = $resp_labsample[0]['EvnLabSample_id'];
                            $params['RefSample_id'] = null;
                            $orderparams = json_decode(toUTF($data['order']), true);
                            $params['researches'][] = $orderparams['UslugaComplexMedService_id'];
                            $checked_tests = json_decode($orderparams['checked']);

                            // сохраняем услуги и тесты, разбиваем на пробы по биоматериалу
                            $this->EvnLabSample_model->saveLabSampleResearches($params, $checked_tests);
						}
					}

					$this->load->model('Evn_model', 'Evn_model');
					$this->Evn_model->updateEvnStatus(array(
						'Evn_id' => $resp[0]['EvnLabRequest_id'],
						'EvnStatus_SysNick' => 'New',
						'EvnClass_SysNick' => 'EvnLabRequest',
						'pmUser_id' => $data['pmUser_id']
					));

					// кэшируем количество тестов
					$this->EvnLabRequest_model->ReCacheLabRequestUslugaCount(array(
						'EvnLabRequest_id' => $resp[0]['EvnLabRequest_id'],
						'pmUser_id' => $data['pmUser_id']
					));

					// кэшируем названия услуг
					$this->EvnLabRequest_model->ReCacheLabRequestUslugaName(array(
						'EvnLabRequest_id' => $resp[0]['EvnLabRequest_id'],
						'pmUser_id' => $data['pmUser_id']
					));

					// кэшируем статус проб в заявке
					$this->EvnLabRequest_model->ReCacheLabRequestSampleStatusType(array(
						'EvnLabRequest_id' => $resp[0]['EvnLabRequest_id'],
						'pmUser_id' => $data['pmUser_id']
					));

				} else {
					$this->rollbackTransaction();
					throw new Exception('Не удалось сохранить лабораторную заявку', 500);
				}
			}
		} else {

			// если направление было связано с EvnLabRequest, нужно перекешировать EvnLabRequest_prmTime - время записи
			$query = "
				update elr with (rowlock)
					set EvnLabRequest_prmTime = ttms.TimetableMedService_begTime
				from EvnLabRequest elr
				left join v_TimetableMedService_lite ttms (nolock) on ttms.EvnDirection_id = elr.EvnDirection_id
				where elr.EvnDirection_id = :EvnDirection_id
			";

			$this->db->query($query, array('EvnDirection_id' => $data['EvnDirection_id']));

			if (!empty($data['PayType_id'])) { // надо обновить в заявке Вид оплаты

				$query = "
					update
						EvnLabRequest with (rowlock)
					set
						PayType_id = :PayType_id
					where
						EvnLabRequest_id = :EvnLabRequest_id
				";

				$this->db->query($query, array(
					'PayType_id' => $data['PayType_id'],
					'EvnLabRequest_id' => $EvnLabRequest_id
				));
			}
		}

		return array('EvnLabRequest_id' => $EvnLabRequest_id);
	}

	/**
	 * Сохранение связи оснвого направления бирки и связанной с этой биркой других направлений
	 */
	function saveEvnDirectionServiceLink($data) {

		$query = "
			declare
				@Res bigint,
				@ErrCode bigint,
				@ErrMsg varchar(4000);
			set @Res = :EvnDirectionServiceLink_id;

			exec p_EvnDirectionServiceLink_ins
				@EvnDirectionServiceLink_id = @Res output,
				@EvnDirection_id = :EvnDirection_id,
				@EvnDirection_nid = :EvnDirection_nid,
				@pmUser_id = :pmUser_id
			select @Res as EvnDirectionServiceLink_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg;
		";

		return $this->queryResult($query, array(
			'EvnDirectionServiceLink_id' => null,
			'EvnDirection_id' => $data['EvnDirection_id'],
			'EvnDirection_nid' => $data['EvnDirection_nid'],
			'pmUser_id' => $data['pmUser_id']
		));
	}

	/**
	 *  Проверка наличия направления в ту же службу
	 */
	function checkEvnDirectionExists($data) {
		// пробуем получить биоматериал по заказываемому исследоваию
		$RefMaterials = $this->queryResult("
			select distinct
				rs.RefMaterial_id
			from
				v_UslugaComplexMedService ucms (nolock)
				left join v_UslugaComplexMedService ucms_child (nolock) on ucms_child.UslugaComplexMedService_pid  = ucms.UslugaComplexMedService_id
				inner join v_RefSample rs (nolock) on rs.RefSample_id = ISNULL(ucms_child.RefSample_id, ucms.RefSample_id)
			where
				ucms.UslugaComplex_id = :UslugaComplex_id
				and ucms.MedService_id = :MedService_id
				and ucms.UslugaComplexMedService_pid is null
		", array(
			'MedService_id' => $data['MedService_id'],
			'UslugaComplex_id' => $data['UslugaComplex_id']
		));

		$filter = "";
		if (!empty($RefMaterials)) {
			$RefMats = "";
			foreach($RefMaterials as $RefMaterial) {
				if (!empty($RefMats)) {
					$RefMats .= ",";
				}
				$RefMats .= "'{$RefMaterial['RefMaterial_id']}'";
			}
			$filter = "
				and exists( -- биоматериал в услуге заявки такой же как в заказываемой услуге.
					select top 1
						rs.RefMaterial_id
					from
						v_UslugaComplexMedService ucms (nolock)
						left join v_UslugaComplexMedService ucms_child (nolock) on ucms_child.UslugaComplexMedService_pid  = ucms.UslugaComplexMedService_id
						inner join v_RefSample rs (nolock) on rs.RefSample_id = ISNULL(ucms_child.RefSample_id, ucms.RefSample_id)
					where
						ucms.UslugaComplex_id = eup.UslugaComplex_id
						and ucms.MedService_id = ed.MedService_id
						and ucms.UslugaComplexMedService_pid is null
						and rs.RefMaterial_id IN ({$RefMats})
				)
			";
		} else {
			$filter = "
				and not exists( -- биоматериал в услуге заявки отсутсвует
					select top 1
						rs.RefMaterial_id
					from
						v_UslugaComplexMedService ucms (nolock)
						left join v_UslugaComplexMedService ucms_child (nolock) on ucms_child.UslugaComplexMedService_pid  = ucms.UslugaComplexMedService_id
						inner join v_RefSample rs (nolock) on rs.RefSample_id = ISNULL(ucms_child.RefSample_id, ucms.RefSample_id)
					where
						ucms.UslugaComplex_id = eup.UslugaComplex_id
						and ucms.MedService_id = ed.MedService_id
						and ucms.UslugaComplexMedService_pid is null
						and rs.RefMaterial_id is not null
				)
			";
		}

		$query = "
			declare @curDate date = dbo.tzGetDate();

			select distinct
				ed.EvnDirection_id,
				ed.EvnDirection_Num,
				convert(varchar(10), ed.EvnDirection_setDT, 104) as EvnDirection_setDate,
				ms.MedService_Nick
			from
				v_EvnDirection_all ed (nolock)
				inner join v_EvnStatus es (nolock) on es.EvnStatus_id = ed.EvnStatus_id
				cross apply (
					Select top 1 eup.UslugaComplex_id from v_EvnUslugaPar eup (nolock) where eup.EvnDirection_id = ed.EvnDirection_id -- услуга по заявке
				) eup
				inner join v_MedService ms (nolock) on ms.MedService_id = ed.MedService_id
			where
				ed.Person_id = :Person_id -- тот же пациент
				and ed.EvnDirection_pid = :EvnDirection_pid
				and ed.MedService_id = :MedService_id -- обслуживается той же службой
				and cast(ed.EvnDirection_insDT as date) = @curDate -- добавлялось в тот же день
				and es.EvnStatus_SysNick in ('Queued', 'DirZap') -- не обслужено
				and eup.UslugaComplex_id != :UslugaComplex_id -- должны быть разные услуги
				{$filter}
		";

		$resp = $this->queryResult($query, array(
			'Person_id' => $data['Person_id'],
			'EvnDirection_pid' => $data['EvnDirection_pid'],
			'MedService_id' => $data['MedService_id'],
			'UslugaComplex_id' => $data['UslugaComplex_id']
		));

		if (!empty($resp[0]['EvnDirection_id'])) {
			// Нашли доступное для записи направление
			// Найдем всё, что можно в него записать
			$UslugaList = $this->getUslugaWithoutDirectoryList($data);
			return array('Error_Msg' => '', 'EvnDirections' => $resp, 'UslugaList' => (!empty($UslugaList)?json_encode($UslugaList):null));
			// возвращаем на форму направление в которое можно включить назначение
			// возвращаем на форму все назначения, доступные для включения в направление
		}

		return array('Error_Msg' => '');
	}

	/**
	 * Формирование кода бронирования для электронной очереде
	 */
	function genEvnDirectionTalonCode($data) {
		$params = array(
			'Lpu_did' => $data['Lpu_did'],
			'TimetableMedService_id' => $data['TimetableMedService_id']
		);
		$query = "
			declare
				@EvnDirection_TalonCode varchar(8),
				@UslugaComplexMedService_id bigint,
				@TimetableMedService_Day bigint,
				@TimetableMedService_begTime datetime
			
			select 
				@TimetableMedService_begTime = TimetableMedService_begTime,
				@UslugaComplexMedService_id = UslugaComplexMedService_id,
				@TimetableMedService_Day = TimetableMedService_Day
			from v_TimetableMedService_lite with (nolock)
			WHERE
				TimetableMedService_id = :TimetableMedService_id
				
			EXEC dbo.xp_GenTalonCode
				@UslugaComplexMedService_id = @UslugaComplexMedService_id,
				@TimetableMedService_begTime = @TimetableMedService_begTime,
				@EvnDirection_id = null,
				@Lpu_did = :Lpu_did,
				@Day_id = @TimetableMedService_Day,
				@EvnDirection_TalonCode = @EvnDirection_TalonCode OUTPUT
			
			select @EvnDirection_TalonCode as EvnDirection_TalonCode
		";
		return $this->getFirstResultFromQuery($query, $params);
	}

	/**
	 * Формирование кода бронирования для электронной очереде
	 */
	function genEvnDirectionTalonCodeMedService($data) {
		$params = array(
			'Lpu_did' => $data['Lpu_did'],
			'TimetableMedService_id' => $data['TimetableMedService_id']
		);
		$query = "
			declare
				@EvnDirection_TalonCode varchar(8);
				
			EXEC dbo.xp_GenTalonCodeMedService
				@TimetableMedService_id = :TimetableMedService_id,
				@Lpu_did = :Lpu_did,
				@EvnDirection_TalonCode = @EvnDirection_TalonCode OUTPUT
			
			select @EvnDirection_TalonCode as EvnDirection_TalonCode
		";
		return $this->getFirstResultFromQuery($query, $params);
	}

	/**
	 * Формирование кода бронирования для электронной очереде
	 */
	function genEvnDirectionTalonCodeGraf($data) {
		$params = array(
			'Lpu_did' => $data['Lpu_did'],
			'TimetableGraf_id' => $data['TimetableGraf_id']
		);
		$query = "
			declare
				@EvnDirection_TalonCode varchar(8);
				
			EXEC dbo.xp_GenTalonCodeGraf
				@TimetableGraf_id = :TimetableGraf_id,
				@Lpu_did = :Lpu_did,
				@EvnDirection_TalonCode = @EvnDirection_TalonCode OUTPUT
			
			select @EvnDirection_TalonCode as EvnDirection_TalonCode
		";
		return $this->getFirstResultFromQuery($query, $params);
	}

	/**
	 * Формирование кода бронирования для электронной очереде
	 */
	function genEvnDirectionTalonCodeResource($data) {
		$params = array(
			'Lpu_did' => $data['Lpu_did'],
			'TimetableResource_id' => $data['TimetableResource_id']
		);
		$query = "
			declare
				@EvnDirection_TalonCode varchar(8);

			EXEC dbo.xp_GenTalonCodeResource
				@TimetableResource_id = :TimetableResource_id,
				@Lpu_did = :Lpu_did,
				@EvnDirection_TalonCode = @EvnDirection_TalonCode OUTPUT

			select @EvnDirection_TalonCode as EvnDirection_TalonCode
		";
		return $this->getFirstResultFromQuery($query, $params);
	}

	/**
	 * Создание нового направления на время или в очередь
	 *
	 * @param $data
	 * @return array|bool
	 */
	function saveEvnDirection($data) {
		//для армов смо и тфомс подменяем лпу, чтоб было не нулевое. #105675
		if(isset($data['session']) && !empty($data['session']['CurArmType']) && ($data['session']['CurArmType']=='smo' || $data['session']['CurArmType']=='tfoms')) {
			if( empty($data['Lpu_id']) && !empty($data['Lpu_did']) )
				$data['Lpu_id']=$data['Lpu_did'];
			else
				return array(array('Error_Msg'=>'Не указана МО назначения!', 'Error_Code'=>400, 'EvnDirection_id'=>!empty($data['EvnDirection_id'])?$data['EvnDirection_id']:null));
		}
		$procedure = '';
		$additional_params = '';

		$data['EvnDirection_TalonCode'] = null;
		$isEvnDirectionInsert = (!isset($data['EvnDirection_id'])) || ($data['EvnDirection_id'] <= 0);

		if (!empty($data['EvnPrescr_id'])) {
			$resp_ed = $this->queryResult("
				select top 1
					ed.EvnDirection_id,
					ed.EvnStatus_id
				from
					v_EvnPrescrDirection epd (nolock)
					inner join v_EvnDirection_all ed (nolock) on ed.EvnDirection_id = epd.EvnDirection_id
				where
					epd.EvnPrescr_id = :EvnPrescr_id
					and ISNULL(ed.EvnStatus_id, 16) not in (12, 13) -- не отменено/отклонено
			", array(
				'EvnPrescr_id' => $data['EvnPrescr_id']
			));
			if (!empty($resp_ed[0]['EvnDirection_id'])) {
				if ($resp_ed[0]['EvnStatus_id'] == 17) {
					// если с бирки на бирку
					$data['redirectEvnDirection'] = 800;
				} else {
					// если из очереди
					$data['redirectEvnDirection'] = 600;
				}

				$data['EvnDirection_id'] = $resp_ed[0]['EvnDirection_id'];
				if (empty($data['EvnStatus_id'])) {
					$data['EvnStatus_id'] = $resp_ed[0]['EvnStatus_id'];
				}
				$isEvnDirectionInsert = false;
			}
		}

		if ($isEvnDirectionInsert) {

			if ( isset($data['toQueue']) ) {
				$procedure = 'p_EvnDirection_insToQueue';
				$additional_params = "
					@MedStaffFact_did = :MedStaffFact_did,
					@LpuSectionProfile_did = :LpuSectionProfile_did,
					@MedService_did = :MedService_did,
					@Resource_did = :Resource_did,
				";
			} else {
				$procedure = 'p_EvnDirection_ins';
			}

		} else {

			if (!empty($data['EvnDirection_id'])) {

				$data['EvnDirection_TalonCode'] = $this->getFirstResultFromQuery("
					select top 1
						EvnDirection_TalonCode
					from v_EvnDirection_all with(nolock)
					where EvnDirection_id = :EvnDirection_id
				", $data);
			}

			$procedure = 'p_EvnDirection_upd';
			$additional_params = "
				@EvnDirection_IsSigned = @EvnDirection_IsSigned,
				@pmUser_signID = @pmUser_signID,
				@EvnDirection_signDT = @EvnDirection_signDT,
			";
		}

		if ( empty($data['DirType_id']) ) {
			return array(array('Error_Msg'=>'Не указан тип направления!', 'Error_Code'=>400, 'EvnDirection_id'=>!empty($data['EvnDirection_id'])?$data['EvnDirection_id']:null));
		}

		if (!empty($data['LpuSection_did'])) {
			$resp_ls = $this->queryResult("
				select
					LpuSection_id,
					LpuUnit_id,
					Lpu_id
				from
					v_LpuSection (nolock)
				where
					LpuSection_id = :LpuSection_id
			", array(
				'LpuSection_id' => $data['LpuSection_did']
			));

			if (empty($data['EvnPrescrVK_id']) && !empty($data['LpuUnit_did']) && !empty($resp_ls[0]['LpuUnit_id']) && $resp_ls[0]['LpuUnit_id'] != $data['LpuUnit_did']) {
				return array(array('Error_Msg' => 'Отделение, куда направили не соответствует группе отделений, куда направили, проверьте корректность введённых данных'));
			}

			if (!empty($data['Lpu_did']) && !empty($resp_ls[0]['Lpu_id']) && $resp_ls[0]['Lpu_id'] != $data['Lpu_did']) {
				return array(array('Error_Msg' => 'Некорректно заполнено поле «МО направления» или «Отделение МО». В выбранной МО отсутствует указанное отделение.'));
			}
		}
		
		/*
		 * https://redmine.swan-it.ru/issues/169405
		 * Если для Организации направления (Org_oid) есть связанная запись в dbo.Lpu, то также производится сохранение связанного значения в Lpu_did
		 */
		if (!empty($data['Org_oid']) && empty($data['Lpu_did'])) {
			$linkLpu_did = $this->getFirstResultFromQuery("
				select top 1 Lpu_id
				from v_Lpu_all with(nolock)
				where Org_id = :Org_oid
			", $data);
			if (!empty($linkLpu_did)) $data['Lpu_did'] = $linkLpu_did;
		}

		// https://redmine.swan.perm.ru/issues/3754
		// вообще не понятно откуда получать эти поля для параклиники и стационара
		// и почему для полки сделано именно таким образом
		$LpuSectionProfile_id = $data['LpuSectionProfile_id'];
		$Lpu_did = $data['Lpu_did'];

		if ( !empty($Lpu_did) ) {
			$Lpu_IsNotForSystem = $this->getFirstResultFromQuery("
				select top 1 isnull(O.Org_IsNotForSystem,1) as Lpu_IsNotForSystem
				from v_Lpu_all L with(nolock)
				inner join v_Org O with(nolock) on O.Org_id = L.Org_id
				where Lpu_id = :Lpu_did
			", $data);
			if ($Lpu_IsNotForSystem === false) {
				return array(array('Error_Msg' => 'Ошибка при получении свойств МО'));
			}
		}
		
		//Получаем профиль и ЛПУ по бирке в поликлинике
		if ( !empty($data['TimetableGraf_id']) ) {
			$query = "
				select
					ls.LpuSectionProfile_id,
					msf.Lpu_id
				from v_TimeTableGraf_lite ttg with (nolock)
				left join v_MedStaffFact msf with (nolock) on ttg.MedStaffFact_id = msf.MedStaffFact_id
				left join v_LpuSection ls with (nolock) on msf.LpuSection_id = ls.LpuSection_id
				where TimetableGraf_id = :TimetableGraf_id
			";
			
			$queryParams = array(
				'TimetableGraf_id' => $data['TimetableGraf_id'],
			);
			$result = $this->db->query($query, $queryParams);
			
			if ( is_object($result) ) {
				$res = $result->result('array');
				if (count($res) > 0) {
					if(empty($LpuSectionProfile_id)) $LpuSectionProfile_id = $res[0]['LpuSectionProfile_id']; //#138336 Всегда должен сохраняться выбранный на форме "Направление" профиль.
					$Lpu_did = $res[0]['Lpu_id'];
				}
			}
		}

		// профиль должен сохраняться всегда, кроме служб (для служб берется с отделения службы, если оно есть) и кроме патологоанатомич. направлений
		if (empty($LpuSectionProfile_id)) {
			if (!empty($data['MedService_id'])) {
				// получаем профиль с отделения службы
				$query = "
					select top 1
						ls.LpuSectionProfile_id
					from
						v_MedService ms (nolock)
						left join v_LpuSection ls with (nolock) on ms.LpuSection_id = ls.LpuSection_id
					where
						ms.MedService_id = :MedService_id
				";

				$queryParams = array(
					'MedService_id' => $data['MedService_id'],
				);
				$result = $this->db->query($query, $queryParams);

				if ( is_object($result) ) {
					$res = $result->result('array');
					if (count($res) > 0) {
						$LpuSectionProfile_id = $res[0]['LpuSectionProfile_id'];
					}
				}
			} else if (
				!in_array(getRegionNick(), array('astra', 'ekb'))  
				&& !in_array($data['DirType_id'], array(7,9,18,23,26))
				&& /*!(getRegionNick() == 'kz' && in_array($data['DirType_id'], array(3,10)))*/ empty($data['OuterKzDirection'])
			) {
				return array(array('Error_Msg' => 'Не указан профиль в направлении, сохранение невозможно'));
			}
		}

		// сохраняем должность врача #18572
		$post_id = null;
		$is_recieve = (!empty($data['EvnDirection_IsReceive']) && $data['EvnDirection_IsReceive'] == 2);

		if (!empty($data['Lpu_sid'])) {
			$Lpu_IsNotForSystem = $this->getFirstResultFromQuery("
				select top 1 isnull(O.Org_IsNotForSystem,1) as Lpu_IsNotForSystem
				from v_Lpu L with(nolock)
				inner join v_Org O with(nolock) on O.Org_id = L.Org_id
				where Lpu_id = :Lpu_sid
			", $data);
			if ($Lpu_IsNotForSystem === false) {
				return array(array('Error_Msg' => 'Ошибка при получении свойств МО'));
			}
		}

		if (!empty($data['From_MedStaffFact_id']) && $data['From_MedStaffFact_id'] < 0) {
			//Отрицательный идентификатор может прийти при импорте направлений из файла
			$data['From_MedStaffFact_id'] = null;
		} else if (!empty($data['From_MedStaffFact_id'])) {
			$query = "
				select
					msf.Post_id,
					msf.MedPersonal_id,
					msf.LpuSection_id
				from v_MedStaffFact msf with (nolock)
				where msf.MedStaffFact_id = :MedStaffFact_id
			";
			$queryParams = array(
				'MedStaffFact_id' => $data['From_MedStaffFact_id'],
			);
			$result = $this->db->query($query, $queryParams);

			if ( is_object($result) ) {
				$res = $result->result('array');
				if (count($res) > 0) {
					$post_id = $res[0]['Post_id'];
					if (empty($data['MedPersonal_id'])) {
						$data['MedPersonal_id'] = $res[0]['MedPersonal_id'];
					}
					if (empty($data['LpuSection_id']) && !$is_recieve) {
						$data['LpuSection_id'] = $res[0]['LpuSection_id'];
					}
				}
			}
		} else if (
			($is_recieve && (17 == $data['DirType_id'] || (isset($Lpu_IsNotForSystem) && $Lpu_IsNotForSystem == 2) || getRegionNick() == 'buryatiya'))
			|| /*(getRegionNick() == 'kz' && in_array($data['DirType_id'], array(3,10)))*/ !empty($data['OuterKzDirection'])
		) {
			// должность врача необязательна, т.к. поле врач необязательное поле
		} else {
			// должность врача необязательна только для системных направлений
			if ( empty($data['EvnDirection_IsAuto']) || 2 != $data['EvnDirection_IsAuto'] ) {
				return array(array('Error_Msg'=>'Не указана должность врача!', 'Error_Code'=>400, 'EvnDirection_id'=>empty($data['EvnDirection_id'])?null:$data['EvnDirection_id']));
			}
		}
		
		if (empty($data['EvnDirection_setDT'])) {
			if ( empty($data['EvnDirection_setDate']) ) {
				return array(array('Error_Msg'=>'Не указана дата направления!', 'Error_Code'=>400));
			}
			$data['EvnDirection_setDT'] = $data['EvnDirection_setDate'];
		}

		if ( $data['EvnDirection_setDT'] instanceof DateTime ) {
			$data['year'] = $data['EvnDirection_setDT']->format('Y');
		}
		else {
			$data['year'] = substr($data['EvnDirection_setDT'], 0, 4);
		}

		// Если нет номера направления (автоматическое), то генерим его по текущей ЛПУ
		// TODO: Это надо будет в дальнейшем проверить, может быть для авто нужна своя нумерация, а не общая
		if ($data['EvnDirection_Num']=='0') {
			$response = $this->getEvnDirectionNumber($data);
			if (is_array($response) && isset($response[0]['EvnDirection_Num'])) {
				// временно согласно задаче #10647 убираю буквенное обозначение для системных направлений 
				//$data['EvnDirection_Num'] = 'A'.$response[0]['EvnDirection_Num'];
				$data['EvnDirection_Num'] = $response[0]['EvnDirection_Num'];
			}
		}

		// если запись на бирку ресурса, то ресурс однозначно берём с бирки.
		if (!empty($data['TimetableResource_id'])) {
			$resp_ttr = $this->queryResult("
				select top 1
					Resource_id
				from
					v_TimetableResource_lite with (nolock)
				where
					TimetableResource_id = :TimetableResource_id
			", array(
				'TimetableResource_id' => $data['TimetableResource_id']
			));

			if (!empty($resp_ttr[0]['Resource_id'])) {
				$data['Resource_id'] = $resp_ttr[0]['Resource_id'];
			} else {
				return array(array('Error_Msg' => 'Ошибка определения ресурса бирки'));
			}
		}

		// берём услугу из заказа.
		if (empty($data['UslugaComplex_id']) && !empty($data['order'])) {
			$orderparams = json_decode(toUTF($data['order']), true);
			if (!empty($orderparams['UslugaComplex_id'])) {
				$data['UslugaComplex_id'] = $orderparams['UslugaComplex_id'];
			}
		}
		if (empty($data['UslugaComplex_id'])) {
			$data['UslugaComplex_id'] = null;
		}

		if (!empty($data['withResource']) && $data['withResource'] && !empty($data['MedService_id']) && empty($data['Resource_id'])) {
			$data['Resource_id'] = $this->getFirstResultFromQuery("
				select top 1
					r.Resource_id
				from
					v_Resource r with (nolock)
					inner join v_UslugaComplexResource ucr with (nolock) on r.Resource_id = ucr.Resource_id
					inner join v_UslugaComplexMedService ucms with (nolock) on ucms.UslugaComplexMedService_id = ucr.UslugaComplexMedService_id
				where
					r.MedService_id = :MedService_id
					and ucms.UslugaComplex_id = :UslugaComplex_id
					and Resource_begDT <= :EvnDirection_setDT
					and (Resource_endDT is null or Resource_endDT > :EvnDirection_setDT)
			", array(
				'MedService_id' => $data['MedService_id'],
				'UslugaComplex_id' => $data['UslugaComplex_id'],
				'EvnDirection_setDT' => $data['EvnDirection_setDT'],
			));
		}

		// перезапись и перенаправление, особая логика перед записью.
		if (!empty($data['redirectEvnDirection'])) {
			$beforeResult = $this->beforeRedirectEvnDirection($data);
			if (!empty($beforeResult[0]['Error_Msg'])) {
				return $beforeResult;
			}
		}

		if ($is_recieve) {
			// При сохранении нужно производить проверку, не существует ли направление с такими же параметрами: Лпу направления, тип, номер, дата, пациент. Если есть выдавать сообщение «Направление уже создано направившей стороной».
			if (empty($data['Person_id'])) {
				$data['Person_id'] = $this->getFirstResultFromQuery("select top 1 Person_id from v_PersonEvn (nolock) where PersonEvn_id = :PersonEvn_id", array(
					'PersonEvn_id' => $data['PersonEvn_id']
				));
			}
			$query = "
				select
					EvnDirection_id
				from
					v_EvnDirection (nolock)
				where
					EvnDirection_Num = :EvnDirection_Num
					and Lpu_id = :Lpu_id
					and DirType_id = :DirType_id
					and EvnDirection_setDT = :EvnDirection_setDT
					and Person_id = :Person_id
			";
			$params = array(
				'EvnDirection_Num' => $data['EvnDirection_Num'],
				'Lpu_id' => $data['Lpu_id'],
				'DirType_id' => $data['DirType_id'],
				'EvnDirection_setDT' => $data['EvnDirection_setDT'],
				'Person_id' => $data['Person_id']
			);
			if (!empty($data['EvnDirection_id'])) {
				$query .= ' and EvnDirection_id <> :EvnDirection_id ';
				$params['EvnDirection_id'] = $data['EvnDirection_id'];
			}
			$result = $this->db->query($query, $params);
			if (is_object($result)) {
				$resp = $result->result('array');
				if (!empty($resp[0]['EvnDirection_id'])) {
					return array(array('Error_Msg' => 'Направление уже создано направившей стороной'));
				}
			} else {
				return array(array('Error_Msg' => 'Ошибка при проверке наличия дублей направления'));
			}
		}

		if (empty($data['PayType_id'])) {
			$data['PayType_id'] = null;

			if (!empty($data['EvnPrescr_id'])) {
				// берём вид оплаты с события, в котором происходит назначение (движение/посещение)
				$query = "
					select
						ISNULL(ev.PayType_id, es.PayType_id) as PayType_id
					from
						v_EvnPrescr ep (nolock)
						left join v_EvnVizit ev (nolock) on ev.EvnVizit_id = ep.EvnPrescr_pid
						left join v_EvnSection es (nolock) on es.EvnSection_id = ep.EvnPrescr_pid
					where
						EvnPrescr_id = :EvnPrescr_id
				";

				$resp_ep = $this->queryResult($query, array(
					'EvnPrescr_id' => $data['EvnPrescr_id']
				));

				if (!empty($resp_ep[0]['PayType_id'])) {
					$data['PayType_id'] = $resp_ep[0]['PayType_id'];
				}
			}
		}

		if (!empty($data['TimetableResource_id']) && !empty($data['MedService_id'])) {
			// бирка ресурса должна соответствовать службе, в которую ведётся запись (refs #83067).
			$resp = $this->queryResult("
				select
					r.MedService_id
				from
					v_TimetableResource_lite ttr (nolock)
					inner join Resource r (nolock) on r.Resource_id = ttr.Resource_id
				where
					ttr.TimetableResource_id = :TimetableResource_id
			", array(
				'TimetableResource_id' => $data['TimetableResource_id']
			));

			if (empty($resp[0]['MedService_id'])) {
				return array(array('Error_Msg' => 'Ошибка определения службы у бирки'));
			} else if ($resp[0]['MedService_id'] != $data['MedService_id']) {
				return array(array('Error_Msg' => 'Выбранная бирка не соответствует службе'));
			}
		}

		// если указано родительское событие, то проверяем, чтобы пациент направления соответствовал пациенту события (refs #96733)
		if (!empty($data['EvnDirection_pid'])) {
			$resp_evn = $this->queryResult("
				select
					e.Evn_id
				from
					Evn e (nolock)
					inner join v_PersonEvn pe (nolock) on pe.Person_id = e.Person_id and pe.PersonEvn_id = :PersonEvn_id
				where
					e.Evn_id = :EvnDirection_pid
			", array(
				'EvnDirection_pid' => $data['EvnDirection_pid'],
				'PersonEvn_id' => $data['PersonEvn_id']
			));

			if (empty($resp_evn[0]['Evn_id'])) {
				return array(array('Error_Msg' => 'Некорректно указан пациент, сохранение направления не возможно2!'));
			}
		}

		// сгенерим код брони
		if ($isEvnDirectionInsert && empty($data['isElectronicQueueRedirect'])) {
			$data['EvnDirection_TalonCode'] = $this->makeEvnDirectionTalonCode($data);
		}

		$this->beginTransaction();
		
		if(empty($data['RecMethodType_id']) && !empty($data['ARMType_id'])){
			if(empty($data['ARMType_id']) && !empty($data['session']['CurARM']['ARMType_id'])) $data['ARMType_id'] = $data['session']['CurARM']['ARMType_id'];
			if(!empty($data['ARMType_id'])) $data['RecMethodType_id'] = $this->getRecMethodTypeForDirection($data);		
		}

		$query = "
			declare
				@Res bigint,
				@EvnDirection_IsSigned bigint,
				@pmUser_signID bigint,
				@EvnDirection_signDT datetime,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @Res = :EvnDirection_id;
			
			if @Res is not null
			  	begin
			  		select
			  			@EvnDirection_IsSigned = case when EvnDirection_IsSigned = 2 then 1 else EvnDirection_IsSigned end,
						@pmUser_signID = pmUser_signID,
						@EvnDirection_signDT = EvnDirection_signDT
					from
						v_EvnDirection_all (nolock)
					where
						EvnDirection_id = @Res
				end
				
			exec " . $procedure . "
				@ConsultingForm_id = :ConsultingForm_id,
				@EvnDirection_id = @Res output,
				@EvnDirection_pid = :EvnDirection_pid,
				@Server_id = :Server_id,
				@PersonEvn_id = :PersonEvn_id,
				@EvnDirection_setDT = :EvnDirection_setDT,
				@DirType_id = :DirType_id,
				@MedicalCareFormType_id = :MedicalCareFormType_id,
				@StudyTarget_id = :StudyTarget_id,
				@PayType_id = :PayType_id,
				@Diag_id = :Diag_id,
				@EvnDirection_Num = :EvnDirection_Num,
				@EvnDirection_Descr = :EvnDirection_Descr,
				@Lpu_did = :Lpu_did, --куда направлен
				@LpuSection_did = :LpuSection_did,
				@LpuSectionProfile_id = :LpuSectionProfile_id,
				@Lpu_id = :Lpu_id, -- кто направил
				@LpuSection_id = :LpuSection_id,
				@LpuUnit_did = :LpuUnit_did,
				@MedPersonal_id = :MedPersonal_id,
				@MedStaffFact_id = :MedStaffFact_id,
				@MedPersonal_zid = :MedPersonal_zid,
				@MedPersonal_Code = :MedPersonal_Code,
				@MedService_id = :MedService_id,
				@EvnDirection_desDT = :EvnDirection_desDT,
				@EvnDirection_IsAuto = :EvnDirection_IsAuto,
				@EvnDirection_IsCito = :EvnDirection_IsCito,
				@EvnStatus_id = :EvnStatus_id,
				@Post_id = :Post_id,
				@Lpu_sid = :Lpu_sid,
				@Org_sid = :Org_sid,
				@Org_oid = :Org_oid,
				@PrehospDirect_id = :PrehospDirect_id,
				@TimetableGraf_id = :TimetableGraf_id,
				@TimetableStac_id = :TimetableStac_id,
				@TimetableResource_id = :TimetableResource_id,
				@TimetableMedService_id = :TimetableMedService_id,
				@EvnDirection_IsNeedOper = :EvnDirection_IsNeedOper,
				@EvnQueue_id = :EvnQueue_id,
				@ARMType_id = :ARMType_id,
				@EvnDirection_IsReceive = :EvnDirection_IsReceive,
				@Resource_id = :Resource_id,
				@MedPersonal_did = :MedPersonal_did,
				@RemoteConsultCause_id = :RemoteConsultCause_id,
				@MedSpec_fid = :MedSpec_fid,
				@UslugaComplex_did = :UslugaComplex_did,
				@FSIDI_id = :FSIDI_id,
				@LpuUnitType_id = :LpuUnitType_id,
				@EvnDirection_TalonCode = :EvnDirection_TalonCode,
				@RecMethodType_id = :RecMethodType_id,
				@ConsultationForm_id =:ConsultationForm_id,
				{$additional_params}
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @Res as EvnDirection_id, :EvnDirection_TalonCode as EvnDirection_TalonCode, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		if (!empty($data['EvnDirection_id']) && $data['EvnDirection_id'] > 0 && empty($data['EvnDirection_pid'])) {
			$queryEvnDirectionPid = "
				select top 1
					EvnDirection_pid
				from v_EvnDirection_all with (nolock)
				where
					EvnDirection_id = :EvnDirection_id	
			";

			$pid = $this->queryResult($queryEvnDirectionPid, $data);
			if (isset($pid[0]['EvnDirection_pid']))
				$data['EvnDirection_pid'] = $pid[0]['EvnDirection_pid'];
		}

		$queryParams = array(
			'ConsultingForm_id' => !isset($data['ConsultingForm_id']) ? NULL : $data['ConsultingForm_id'],
			'EvnDirection_id' => ( !isset($data['EvnDirection_id']) || $data['EvnDirection_id'] <= 0 ? NULL : $data['EvnDirection_id'] ),
			'EvnDirection_pid' => $data['EvnDirection_pid'],
			'Lpu_id' => $data['Lpu_id'],
			'Server_id' => $data['Server_id'],
			'PersonEvn_id' => $data['PersonEvn_id'],
			'EvnDirection_setDT' => $data['EvnDirection_setDT'],
			'DirType_id' => $data['DirType_id'],
			'MedicalCareFormType_id' => !isset($data['MedicalCareFormType_id']) ? NULL : $data['MedicalCareFormType_id'],
			'StudyTarget_id' => !isset($data['StudyTarget_id']) ? NULL : $data['StudyTarget_id'],
			'PayType_id' => $data['PayType_id'],
			'Diag_id' => $data['Diag_id'],
			'EvnDirection_Num' => $data['EvnDirection_Num'],
			'EvnDirection_Descr' => $data['EvnDirection_Descr'],
			'LpuSection_did' => $data['LpuSection_did'],
			'LpuSection_id' => $data['LpuSection_id'],
			'MedStaffFact_id' => $data['From_MedStaffFact_id'],
			'MedPersonal_id' => $data['MedPersonal_id'],
			'MedPersonal_zid' => empty($data['MedPersonal_zid']) ? null : $data['MedPersonal_zid'],
			'MedPersonal_Code' => !isset($data['MedPersonal_Code']) ? NULL : $data['MedPersonal_Code'],
			'EvnDirection_IsNeedOper' => (isset($data['EvnDirection_IsNeedOper'])&&$data['EvnDirection_IsNeedOper']>0)? 2 : 1,
			'EvnDirection_desDT'=>!isset($data['EvnDirection_desDT']) ? NULL : $data['EvnDirection_desDT'],
			'TimetableGraf_id' => !isset($data['TimetableGraf_id']) ? NULL : $data['TimetableGraf_id'],
			'TimetableStac_id' => !isset($data['TimetableStac_id']) ? NULL : $data['TimetableStac_id'],
			'TimetablePar_id' => !isset($data['TimetablePar_id']) ? NULL : $data['TimetablePar_id'],
			'TimetableResource_id' => !isset($data['TimetableResource_id']) ? NULL : $data['TimetableResource_id'],
			'MedService_id' => empty($data['MedService_id']) ? NULL : $data['MedService_id'], // служба куда направили
			'MedService_did' => empty($data['MedService_id']) ? NULL : $data['MedService_id'], // служба в которую ставят в очередь равна службе куда направили
			'Resource_id' => empty($data['Resource_id']) ? NULL : $data['Resource_id'],
			'Resource_did' => empty($data['Resource_id']) ? NULL : $data['Resource_id'],
			'RemoteConsultCause_id' => empty($data['RemoteConsultCause_id']) ? NULL : $data['RemoteConsultCause_id'],
			'TimetableMedService_id' => empty($data['TimetableMedService_id']) ? NULL : $data['TimetableMedService_id'],
			'EvnQueue_id' => empty($data['EvnQueue_id']) ? NULL : $data['EvnQueue_id'],
			'EvnDirection_IsAuto' => empty($data['EvnDirection_IsAuto']) ? NULL : $data['EvnDirection_IsAuto'],
			'EvnDirection_IsCito' => empty($data['EvnDirection_IsCito']) ? NULL : $data['EvnDirection_IsCito'],
			'ConsultationForm_id' => empty($data['ConsultationForm_id']) ? NULL : $data['ConsultationForm_id'],
			'EvnStatus_id' => empty($data['EvnStatus_id']) ? NULL : $data['EvnStatus_id'],
			'pmUser_id' => $data['pmUser_id'],
			'LpuSectionProfile_id' => $LpuSectionProfile_id,
			'Lpu_did' => $Lpu_did,
			'Post_id' => $post_id,
			'Lpu_sid' => empty($data['Lpu_sid']) ? $data['Lpu_id']: $data['Lpu_sid'],
			'Org_sid' => empty($data['Org_sid']) ? NULL : $data['Org_sid'],
			'Org_oid' => empty($data['Org_oid']) ? NULL : $data['Org_oid'],
			'PrehospDirect_id' => empty($data['PrehospDirect_id']) ? NULL : $data['PrehospDirect_id'],
			'EvnUslugaPar_id' => !empty($data['EvnUslugaPar_id']) ? $data['EvnUslugaPar_id'] : null,
			'EvnQueue_pid' => !empty($data['EvnQueue_pid']) ? $data['EvnQueue_pid'] : null,
			'LpuUnit_did' => !empty($data['LpuUnit_did']) ? $data['LpuUnit_did'] : null,
			'LpuSectionProfile_did' => !empty($data['LpuSectionProfile_did']) ? $data['LpuSectionProfile_did'] : null,
			'MedStaffFact_did' => !empty($data['MedStaffFact_did']) ? $data['MedStaffFact_did'] : null,
			'MedPersonal_did' => !empty($data['MedPersonal_did']) ? $data['MedPersonal_did'] : null,
			'UslugaComplex_did' => !empty($data['UslugaComplex_did']) ? $data['UslugaComplex_did'] : (!empty($data['UslugaComplex_id']) ? $data['UslugaComplex_id'] : null),
			'FSIDI_id' => !empty($data['FSIDI_id']) ? $data['FSIDI_id'] : null,
			'LpuUnitType_id' => !empty($data['LpuUnitType_id']) ? $data['LpuUnitType_id'] : (!empty($data['LpuUnitType_did']) ? $data['LpuUnitType_did'] : null),
			'MedSpec_fid' => !empty($data['MedSpec_fid']) ? $data['MedSpec_fid'] : null,
			'ARMType_id' => !empty($data['ARMType_id']) ? $data['ARMType_id'] : null,
			//параметра @EvnCourse_id нет в p_EvnDirection_insToQueue
			//@EvnCourse_id = :EvnCourse_id,
			//'EvnCourse_id' => !empty($data['EvnCourse_id']) ? $data['EvnCourse_id'] : null,
			'EvnDirection_IsReceive' => !empty($data['EvnDirection_IsReceive']) ? $data['EvnDirection_IsReceive'] : null,
			'EvnDirection_TalonCode' => !empty($data['EvnDirection_TalonCode']) ? $data['EvnDirection_TalonCode'] : null,
			'RecMethodType_id' => !empty($data['RecMethodType_id']) ? $data['RecMethodType_id'] : 10 // промед
		);
		
		// не заполняем поля врача и отделения для автоматического направления PROMEDWEB-9115
		if ( $isEvnDirectionInsert 
		&& !empty($data['EvnDirection_IsAuto']) 
		&& $data['EvnDirection_IsAuto'] == 2 
		&& $queryParams['MedStaffFact_id'] == $queryParams['MedStaffFact_did']) {
			$queryParams['MedPersonal_id'] = null;
			$queryParams['MedPersonal_did'] = null;
			$queryParams['MedStaffFact_id'] = null;
			$queryParams['MedStaffFact_did'] = null;
			$queryParams['LpuSection_did'] = null;
			$queryParams['LpuSection_id'] = null;
		}

		if (empty($queryParams['PrehospDirect_id']) && !empty($data['order'])) {
			$order = json_decode($data['order'], true);
			if (isset($order['PrehospDirect_id'])) {
				$queryParams['PrehospDirect_id'] = $order['PrehospDirect_id'];
			}
		}
		//echo getDebugSQL($query, $queryParams);die();
		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			$response = $result->result('array');

			$data['EvnDirection_id'] = $response[0]['EvnDirection_id'];
			$response[0]['EvnDirection_Num'] = $data['EvnDirection_Num'];

			if (getRegionNick() == 'astra' && !empty($data['EvnDirection_id'])) {
				if (empty($data['Person_id'])) {
					$data['Person_id'] = $this->getFirstResultFromQuery("select top 1 Person_id from v_PersonEvn (nolock) where PersonEvn_id = :PersonEvn_id", array(
						'PersonEvn_id' => $data['PersonEvn_id']
					));
				}
				// При создании электронного направления происходит поиск по всем КВС пациента, в которых не указано электронное направление, по следующим параметрам:
				// – Дата направления;
				// – МО, создавшая направление;
				// – МО, куда выписано направление.
				// При нахождении одного КВС с такими параметрами, созданное электронное направление автоматически указывается в найденном КВС.
				$resp_eps = $this->queryResult("
					select
						eps.EvnPS_id
					from
						v_EvnPS eps (nolock)
						left join v_LpuSection ls (nolock) on ls.LpuSection_id = eps.LpuSection_did
					where
						eps.EvnDirection_id is null
						and eps.Person_id = :Person_id
						and eps.EvnDirection_setDT = :EvnDirection_setDT -- Дата направления
						and ISNULL(eps.Lpu_did, ls.Lpu_id) = :Lpu_did -- МО, создавшая направление
						and eps.Lpu_id = :Lpu_id -- МО, куда выписано направление
				", array(
					'Person_id' => $data['Person_id'],
					'EvnDirection_setDT' => $data['EvnDirection_setDT'], // Дата направления
					'Lpu_did' => $data['Lpu_id'], // МО, создавшая направление
					'Lpu_id' => $Lpu_did // МО, куда выписано направление
				));

				if (count($resp_eps) == 1) {
					// Подставляем эл. направление в КВС
					$this->load->model('EvnPS_model', 'EvnPS_model');
					$this->EvnPS_model->setScenario(swModel::SCENARIO_SET_ATTRIBUTE);
					$this->EvnPS_model->setParams(array(
						'session' => $data['session']
					));
					$this->EvnPS_model->setAttributes(array(
						'EvnPS_id' => $resp_eps[0]['EvnPS_id'],
						'EvnDirection_id' => $data['EvnDirection_id'],
						'EvnDirection_setDT' => $data['EvnDirection_setDT'],
						'EvnDirection_Num' => $data['EvnDirection_Num']
					));
					$this->EvnPS_model->_save();

					// Обслуживаем направление
					$this->load->model('EvnDirectionAll_model');
					$this->EvnDirectionAll_model->setStatus(array(
						'Evn_id' => $data['EvnDirection_id'],
						'EvnStatusCause_id' => null,
						'EvnStatusHistory_Cause' => null,
						'EvnStatus_SysNick' => EvnDirectionAll_model::EVN_STATUS_DIRECTION_SERVICED,
						'EvnClass_id' => 27,
						'pmUser_id' => $data['pmUser_id']
					));
				}
			}
			
			if (getRegionNick() == 'msk' && $data['DirType_id'] == 17 && $data['isRKC']) {
				$this->load->model('RepositoryObserv_model', 'obsmodel');
				$observ = $this->obsmodel->getLastCreatedObserve($data['EvnDirection_pid']);
				
				if (!isset($data['CVIConsultRKC_id'])) {
					$result = $this->obsmodel->getLastCreatedConsultRKC($data['EvnDirection_pid']);
					$data['CVIConsultRKC_id'] = $result > 0 ? $result : null;
				}
				$obsParams = [
					'CVIConsultRKC_id' => $data['CVIConsultRKC_id'],
					'CVIConsultRKC_setDT' => $observ[0]['RepositoryObserv_setDT'] ?? null,
					'RepositoryObserv_id' => $observ[0]['RepositoryObserv_id'] ?? null,
					'RepositoryObserv_sid' => $data['RepositoryObserv_sid'] ?? $observ[0]['RepositoryObserv_id'] ?? null,
					'EvnDirection_id' =>  $data['EvnDirection_id'],
					'pmUser_id' => $data['pmUser_id']
				];
				$this->obsmodel->saveCVIConsultRKC($obsParams);
			}
			
			if( 
				getRegionNick() == 'penza'
				&& isset($data['TreatmentType_id'])
			){
				$TreatmentTypeLink_response = $this->getFirstRowFromQuery("
					select top 1 *
					from r58.TreatmentTypeLink
					where EvnDirection_id = :EvnDirection_id
				", array(
					'EvnDirection_id' => $data['EvnDirection_id']
				));

				$TreatmentTypeLink_params = array(
					'TreatmentType_id' => $data['TreatmentType_id'],
					'EvnDirection_id' => $data['EvnDirection_id'],
					'pmUser_id' => $data['pmUser_id'],
					'TreatmentTypeLink_id' => null
				);

				if(empty($TreatmentTypeLink_response)) {
					$TreatmentTypeLink_proc = 'r58.p_TreatmentTypeLink_ins';
				} else {
					$TreatmentTypeLink_proc = 'r58.p_TreatmentTypeLink_upd';
					$TreatmentTypeLink_params['TreatmentTypeLink_id'] = $TreatmentTypeLink_response['TreatmentTypeLink_id'];
				}
				
				$TreatmentTypeLink_response = $this->db->query("
					declare
						@Res bigint,
						@ErrCode int,
						@ErrMessage varchar(4000);
					set @Res = :TreatmentTypeLink_id;
					exec {$TreatmentTypeLink_proc}
						@TreatmentTypeLink_id = @Res output,
						@TreatmentType_id = :TreatmentType_id,
						@EvnDirection_id = :EvnDirection_id,
						@pmUser_id = :pmUser_id,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMessage output;
					select @Res as TreatmentTypeLink_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
				",
					$TreatmentTypeLink_params
				);
				if(is_object($TreatmentTypeLink_response)) {
					$TreatmentTypeLink_result = $TreatmentTypeLink_response->result('array');
					
					if(!empty($TreatmentTypeLink_result[0]['Error_Code'])) {
						$this->rollbackTransaction();
						throw new Exception($TreatmentTypeLink_result['Error_Msg']);
					}

					
				} else {
					$this->rollbackTransaction();
					throw new Exception ('Ошибка при сохранении типа предстоящего лечения');
				}
			}

			if (!empty($data['EvnDirection_id']) && !empty($data['DopDispInfoConsent_id'])) {
				$this->db->query("
					update EvnDirection set DopDispInfoConsent_id = :DopDispInfoConsent_id where EvnDirection_id = :EvnDirection_id
				", array(
					'EvnDirection_id' => $data['EvnDirection_id'],
					'DopDispInfoConsent_id' => $data['DopDispInfoConsent_id']
				));
			}

			// т.к. есть p_EvnDirection_insToQueue и нет p_EvnDirectionOper_insToQueue, а так же EvnDirectionOper не отображаются в v_EvnDirection и v_EvnDirection_all
			// добавим недостающие данные в объект EvnDirectionOper вручную (поля EvnDirection_id, EvnDirectionOper_IsAgree, EvnXml_id)
			if (!empty($data['EvnDirection_id']) && $data['DirType_id'] == 20) {
				// в оперблок
				$resp = $this->queryResult("
					select EvnDirectionOper_id from EvnDirectionOper (nolock) where EvnDirectionOper_id = :EvnDirectionOper_id
				", array(
					'EvnDirectionOper_id' => $data['EvnDirection_id']
				));

				if (!empty($resp[0]['EvnDirectionOper_id'])) {
					// обновляем
					$this->db->query("UPDATE EvnDirectionOper with (rowlock) set EvnDirection_id = :EvnDirection_id, EvnDirectionOper_IsAgree = :EvnDirectionOper_IsAgree, EvnXml_id = :EvnXml_id WHERE EvnDirectionOper_id = :EvnDirectionOper_id", array(
						'EvnDirectionOper_id' => $data['EvnDirection_id'],
						'EvnDirection_id' => $data['EvnDirection_id'],
						'EvnDirectionOper_IsAgree' => !empty($data['EvnDirectionOper_IsAgree'])?$data['EvnDirectionOper_IsAgree']:null,
						'EvnXml_id' => !empty($data['EvnXml_id'])?$data['EvnXml_id']:null
					));
				} else {
					// добавляем
					$this->db->query("INSERT INTO EvnDirectionOper with (rowlock) (EvnDirectionOper_id, EvnDirection_id, EvnDirectionOper_IsAgree, EvnXml_id) VALUES (:EvnDirectionOper_id, :EvnDirection_id, :EvnDirectionOper_IsAgree, :EvnXml_id)", array(
						'EvnDirectionOper_id' => $data['EvnDirection_id'],
						'EvnDirection_id' => $data['EvnDirection_id'],
						'EvnDirectionOper_IsAgree' => !empty($data['EvnDirectionOper_IsAgree'])?$data['EvnDirectionOper_IsAgree']:null,
						'EvnXml_id' => !empty($data['EvnXml_id'])?$data['EvnXml_id']:null
					));
				}
			}

			if (!empty($data['EvnPrescrVK_id'])) {
				if (isset($data['toQueue'])) {
					$this->db->query("
						update EvnPrescrVK with(rowlock)
						set MedService_id = :MedService_id, TimetableMedService_id = null, EvnQueue_id = (select top 1 EvnQueue_id from v_EvnQueue with (nolock) where EvnDirection_id = :EvnDirection_id)
						where EvnPrescrVK_id = :EvnPrescrVK_id
					", $data);
				} else {
					$this->db->query("
						update EvnPrescrVK with(rowlock)
						set MedService_id = :MedService_id, EvnQueue_id = null, TimetableMedService_id = (select top 1 TimetableMedService_id from v_TimetableMedService with (nolock) where EvnDirection_id = :EvnDirection_id)
						where EvnPrescrVK_id = :EvnPrescrVK_id
					", $data);
				}

				if (in_array(getRegionNick(), ['perm', 'vologda']) && !empty($data['MedService_id'])) {
					$this->load->model('Evn_model');
					$this->Evn_model->updateEvnStatus([
						'Evn_id' => $data['EvnPrescrVK_id'],
						'EvnStatus_SysNick' => 'SubmittedVK',
						'EvnClass_SysNick' => 'EvnPrescrVK',
						'pmUser_id' => $data['pmUser_id']
					]);
				}
			}
			if (isset($data['toQueue']) && !empty($data['EvnPrescrMse_id'])) {
				$this->db->query("
					update EvnPrescrMse with(rowlock)
					set EvnQueue_id = (select top 1 EvnQueue_id from v_EvnQueue with (nolock) where EvnDirection_id = :EvnDirection_id)
					where EvnPrescrMse_id = :EvnPrescrMse_id
				", $data);
			}
			if(isset($data['EvnPrescr_id']) && isset($data['PrescriptionType_Code']) && is_array($response) && count($response)==1 && empty($response[0]['Error_Msg']) && !empty($response[0]['EvnDirection_id']) && empty($queryParams['EvnDirection_id']))
			{
				// если уже есть направление по данному назначению, то выдаём ошибку refs #88216
				$resp_ed = $this->queryResult("
					select top 1
						ed.EvnDirection_id
					from
						v_EvnPrescrDirection epd (nolock)
						inner join v_EvnDirection_all ed (nolock) on ed.EvnDirection_id = epd.EvnDirection_id
					where
						epd.EvnPrescr_id = :EvnPrescr_id
						and ISNULL(ed.EvnStatus_id, 16) not in (12, 13) -- не отменено/отклонено
				", array(
					'EvnPrescr_id' => $data['EvnPrescr_id']
				));
				if (!empty($resp_ed[0]['EvnDirection_id'])) {
					$this->rollbackTransaction();
					throw new Exception('По данному назначению уже создано направление', 500);
				}

				$this->load->model('EvnPrescr_model', 'EvnPrescr_model');
				$tmp_response = $this->EvnPrescr_model->directEvnPrescr($data);
			}
			// TODO: Сохраняем связь заказанной услуги с данным направлением 
			if (empty($response[0]['Error_Msg']) && isset($data['EvnUsluga_id'])&&$data['EvnUsluga_id']>0) {
				$this->load->model('EvnUsluga_model', 'EvnUsluga_model');
				$this->EvnUsluga_model->saveEvnDirectionInEvnUsluga($data);
			}
			
			if (empty($response[0]['Error_Msg']) && !empty($data['MedService_id'])) {

				// проверяем тип, если лаборатория или пункт забора, значит создаём ещё и заявку.
				$MedServiceType_SysNick = $this->getFirstResultFromQuery("
					select
						mst.MedServiceType_SysNick
					from
						v_MedService ms (nolock)
						inner join v_MedServiceType mst (nolock) on mst.MedServiceType_id = ms.MedServiceType_id
					where
						ms.MedService_id = :MedService_id
				", $data);

				// сохраняем заказ в EvnDirectionUslugaComplex
				if (!empty($MedServiceType_SysNick) && in_array($MedServiceType_SysNick, array('oper_block', 'func', 'lab', 'microbiolab'))) {
					// для лаборатории древовидная структура
					if (in_array($MedServiceType_SysNick, ['lab', 'microbiolab'])) {
						if (!empty($data['EvnPrescr_id'])) {
							$data['UslugaComplex_id'] = $this->getFirstResultFromQuery("
								select
									UslugaComplex_id
								from
									v_EvnPrescrLabDiag with (nolock)
								where
									EvnPrescrLabDiag_id = :EvnPrescr_id
							", $data);
						}

						// если не назначение и пришёл заказ с формы направления, достаём услугу из заказа.
						if (empty($data['UslugaComplex_id']) && !empty($data['order'])) {
							$orderparams = json_decode(toUTF($data['order']), true);
							if (!empty($orderparams['UslugaComplex_id'])) {
								$data['UslugaComplex_id'] = $orderparams['UslugaComplex_id'];
							}
						}


						if (empty($data['UslugaComplex_id'])) {
							$data['UslugaComplex_id'] = null;
						}

						if (!empty($data['UslugaComplex_id'])) {
							// сохраняем основную услугу
							$resp_savep = $this->saveEvnDirectionUslugaComplex(array(
								'EvnDirectionUslugaComplex_id' => null,
								'EvnDirection_id' => $data['EvnDirection_id'],
								'UslugaComplex_id' => $data['UslugaComplex_id'],
								'pmUser_id' => $data['pmUser_id']
							));
						}
					}

					// сохраняем подуслуги
					if (!empty($data['order'])) {
						$orderparams = json_decode(toUTF($data['order']), true);
						if (count($orderparams) > 0) {
							$orderdata['checked'] = json_decode($orderparams['checked']);
							if (!empty($orderdata['checked'])) {
								foreach ($orderdata['checked'] as $UslugaComplexContent_id) {
									$resp_save = $this->saveEvnDirectionUslugaComplex(array(
										'EvnDirectionUslugaComplex_id' => null,
										'EvnDirectionUslugaComplex_pid' => !empty($resp_savep[0]['EvnDirectionUslugaComplex_id'])?$resp_savep[0]['EvnDirectionUslugaComplex_id']:null,
										'EvnDirection_id' => $data['EvnDirection_id'],
										'UslugaComplex_id' => $UslugaComplexContent_id,
										'pmUser_id' => $data['pmUser_id']
									));
								}
							} else if (!empty($resp_savep[0]['EvnDirectionUslugaComplex_id'])) {
								// если единичный тест, то сам себя.
								$resp_savep = $this->saveEvnDirectionUslugaComplex(array(
									'EvnDirectionUslugaComplex_id' => null,
									'EvnDirectionUslugaComplex_pid' => $resp_savep[0]['EvnDirectionUslugaComplex_id'],
									'EvnDirection_id' => $data['EvnDirection_id'],
									'UslugaComplex_id' => $data['UslugaComplex_id'],
									'pmUser_id' => $data['pmUser_id']
								));
							}
						}
					}
				}

				if (!empty($MedServiceType_SysNick) && in_array($MedServiceType_SysNick, array('lab','pzm', 'microbiolab'))) {
					$this->makeEvnLabRequest($data);
				}
				
				if (!empty($MedServiceType_SysNick) && in_array($MedServiceType_SysNick, array('func','prock'))) {
					// проверяем что заявка ещё не создана
					$EvnFuncRequest_id = $this->getFirstResultFromQuery("
						select
							EvnFuncRequest_id
						from
							v_EvnFuncRequest efr (nolock)
						where
							efr.EvnFuncRequest_pid = :EvnDirection_id
					", $data);
					
					if (empty($data['PayType_id'])) {
						$PayType_SysNick = 'oms';

						switch ( $this->getRegionNick() ) {
							case 'by': $PayType_SysNick = 'besus'; break;
							case 'kz': $PayType_SysNick = 'Resp'; break;
						}

						$data['PayType_id'] = $this->getFirstResultFromQuery("
							select PayType_id from v_PayType with (nolock) where PayType_SysNick = '{$PayType_SysNick}'
						");
						if ($data['PayType_id'] === false) {
							return false;
						}
					}

					$this->load->model('EvnFuncRequest_model', 'EvnFuncRequest_model');
					if (empty($EvnFuncRequest_id)) {
						$data['EvnFuncRequest_id'] = null;
						$proc_evnreq = 'p_EvnFuncRequest_ins';
						// создаём заявку
						$query = "
							declare
								@EvnFuncRequest_id bigint,
								@ErrCode int,
								@ErrMessage varchar(4000);
							set @EvnFuncRequest_id = :EvnFuncRequest_id;
							exec p_EvnFuncRequest_ins
								@EvnFuncRequest_id = @EvnFuncRequest_id output,
								@EvnFuncRequest_pid = :EvnDirection_id,
								@EvnFuncRequest_setDT = :EvnDirection_setDT,
								@Lpu_id = :Lpu_id,
								@Server_id = :Server_id,
								@PersonEvn_id = :PersonEvn_id,
								@MedService_id = :MedService_id,
								@PayType_id = :PayType_id,
								@Diag_id = :Diag_id,
								@pmUser_id = :pmUser_id,
								@Error_Code = @ErrCode output,
								@Error_Message = @ErrMessage output;
							select @EvnFuncRequest_id as EvnFuncRequest_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
						";

						$result = $this->db->query($query, $data);

						if ( is_object($result) ) {
							$resp = $result->result('array');
							if (!empty($resp[0]['EvnFuncRequest_id'])) {
								$this->load->model('Evn_model', 'Evn_model');
								$this->Evn_model->updateEvnStatus(array(
									'Evn_id' => $resp[0]['EvnFuncRequest_id'],
									'EvnStatus_SysNick' => 'FuncNew',
									'EvnClass_SysNick' => 'EvnFuncRequest',
									'pmUser_id' => $data['pmUser_id']
								));

								// рекэш списка услуг по заявке
								$this->EvnFuncRequest_model->ReCacheFuncRequestUslugaCache(array(
									'MedService_id' => $data['MedService_id'],
									'EvnFuncRequest_id' => $resp[0]['EvnFuncRequest_id'],
									'EvnDirection_id' => $data['EvnDirection_id'],
									'pmUser_id' => $data['pmUser_id']
								));
							} else {
								$this->rollbackTransaction();
								throw new Exception('Не удалось сохранить заявку функциональной диагностики', 500);
							}
						}
					} else {
						// если заявка уже создана
						if (!empty($data['PayType_id'])) {
							// надо обновить в заявке Вид оплаты
							$query = "
								update
									EvnFuncRequest with (rowlock)
								set
									PayType_id = :PayType_id
								where
									EvnFuncRequest_id = :EvnFuncRequest_id
							";
							$this->db->query($query, array(
								'PayType_id' => $data['PayType_id'],
								'EvnFuncRequest_id' => $EvnFuncRequest_id
							));
						}
					}
				}
			}
			
			if (getRegionNick() == 'kz') {
				
				$getbedevnlink_id = $this->getFirstResultFromQuery("select GetBedEvnLink_id from r101.GetBedEvnLink with(nolock) where Evn_id = ?", [$data['EvnDirection_id']]);
				$proc = !$getbedevnlink_id ? 'r101.p_GetBedEvnLink_ins' : 'r101.p_GetBedEvnLink_upd';
				
				if (!empty($data['GetBed_id'])) {
					$this->execCommonSP($proc, [
						'GetBedEvnLink_id' => $getbedevnlink_id ? $getbedevnlink_id : null,
						'Evn_id' => $data['EvnDirection_id'],
						'GetBed_id' => $data['GetBed_id'],
						'pmUser_id' => $data['pmUser_id']
					], 'array_assoc');
				} elseif ($getbedevnlink_id != false) {
					$this->execCommonSP('r101.p_GetBedEvnLink_del', [
						'GetBedEvnLink_id' => $getbedevnlink_id
					], 'array_assoc');
				}

				$EvnLinkAPP_id = $this->getFirstResultFromQuery("select EvnLinkAPP_id from r101.EvnLinkAPP with(nolock) where Evn_id = ?", [$data['EvnDirection_id']]);
				$proc = !$EvnLinkAPP_id ? 'r101.p_EvnLinkAPP_ins' : 'r101.p_EvnLinkAPP_upd';

				if (
					!empty($data['PayTypeKAZ_id']) ||
					!empty($data['ScreenType_id']) ||
					!empty($data['TreatmentClass_id']) ||
					!empty($data['EvnLinkAPP_StageRecovery']) ||
					!empty($data['PurposeHospital_id']) ||
					!empty($data['ReasonHospital_id']) ||
					!empty($data['Diag_cid'])
				) {
					$this->execCommonSP($proc, [
						'EvnLinkAPP_id' => $EvnLinkAPP_id ? $EvnLinkAPP_id : null,
						'Evn_id' => $data['EvnDirection_id'],
						'PayTypeKAZ_id' => $data['PayTypeKAZ_id'],
						'ScreenType_id' => $data['ScreenType_id'],
						'TreatmentClass_id' => $data['TreatmentClass_id'],
						'EvnLinkAPP_StageRecovery' => !empty($data['EvnLinkAPP_StageRecovery'])?$data['EvnLinkAPP_StageRecovery']:null,
						'PurposeHospital_id' => !empty($data['PurposeHospital_id'])?$data['PurposeHospital_id']:null,
						'ReasonHospital_id' => !empty($data['ReasonHospital_id'])?$data['ReasonHospital_id']:null,
						'Diag_cid' => !empty($data['Diag_cid'])?$data['Diag_cid']:null,
						'pmUser_id' => $data['pmUser_id']
					], 'array_assoc');
				} elseif ($EvnLinkAPP_id != false) {
					$this->execCommonSP('r101.p_EvnLinkAPP_del', [
						'EvnLinkAPP_id' => $EvnLinkAPP_id
					], 'array_assoc');
				}

				if (in_array($data['DirType_id'], [1,4,5]) && $data['IgnoreCheckHospitalOffice'] != 2) {
					$this->load->model('HospitalOffice_model');
					$res = $this->HospitalOffice_model->saveReferral([
						'Evn_id' => $data['EvnDirection_id'],
						'isForm' => true,
						'bookingDateReserveId' => (!empty($data['bookingDateReserveId']))?$data['bookingDateReserveId']:null
					]);
					if ($res !== true && is_array($res)) {
						$this->rollbackTransaction();
						return [
							'success' => false,
							'Error_Msg' => true,
							'Cancel_Error_Handle' => true,
							'ho_warning' => $res['msg']
						];
					}
				}
			}

			$enableSmsTalonCode = $this->config->item('enableSmsTalonCode');
			if (!empty($data['EvnDirection_TalonCode']) && !empty($enableSmsTalonCode)) {
				$this->load->model('ElectronicTalon_model');
				$this->ElectronicTalon_model->sendElectronicTalonMessage($data);
			}
			$this->commitTransaction();

			//Шлем уведомление пациентам записаным в стационар на госпитализацию(плановую или экстренную)
			if (in_array(getRegionNick(), ['msk','ufa','vologda']) && $data['DirType_id'] == 1 && isset($response[0])) {
				$this->load->model("NoticeModeSettings_model", "dbnotify");

				//TODO нужно будет что то придумать с статусом change когда это будет нужно (v_EvnStatus)
				$evnStatus = strpos($procedure, 'ins') ? 17 : 0 ;
				$this->dbnotify->prepareNotify($response[0], $evnStatus);
			}

			$this->load->model('ApprovalList_model');
			$this->ApprovalList_model->saveApprovalList(array(
				'ApprovalList_ObjectName' => 'EvnDirection',
				'ApprovalList_ObjectId' => $data['EvnDirection_id'],
				'pmUser_id' => $this->promedUserId
			));

			return $response;
		}
		else {
			return false;
		}
	}

	/**
	* Загрузка полного направления
	*/
	function loadEvnDirectionFull($data) {
	
		$params = array();
		$filter = "(1=1) ";
		$join = "";
		$select = "";
		if (isset($data['EvnDirection_id']) && ($data['EvnDirection_id']>0))
		{
			$params['EvnDirection_id'] = $data['EvnDirection_id'];
			$filter .= "and ED.EvnDirection_id = :EvnDirection_id ";
		}
		elseif  (isset($data['TimetableGraf_id']) && ($data['TimetableGraf_id']>0))
		{
			$params['TimetableGraf_id'] = $data['TimetableGraf_id'];
			$filter .= "and TTG.TimetableGraf_id = :TimetableGraf_id ";

			if (!empty($data['EvnDirection_IsAuto'])) {
				$params['EvnDirection_IsAuto'] = $data['EvnDirection_IsAuto'];
				$filter .= "and ISNULL(ED.EvnDirection_IsAuto, 1) = :EvnDirection_IsAuto ";
			}
		}
		elseif ($data['EvnVizitPL_id']>0)
		{
			$params['EvnVizitPL_id'] = $data['EvnVizitPL_id'];
			$filter .= "and EV.EvnVizitPL_id = :EvnVizitPL_id ";
			$join .= "inner join v_EvnVizitPL EV with(nolock) on EV.TimetableGraf_id = TTG.TimetableGraf_id ";
		}

		if(getRegionNick() == 'penza') {
			$select .= " TTL.TreatmentType_id,";
			$join .= " left join r58.TreatmentTypeLink TTL with (nolock) on TTL.EvnDirection_id = ED.EvnDirection_id";
		}

		if (count($params)>0)
		{
			$selectPersonData = "
					convert(varchar(10), ps.Person_Birthday, 104) as Person_Birthday,
					ps.Person_Surname,
					ps.Person_Firname,
					ps.Person_Secname";
			if (allowPersonEncrypHIV($data['session'])) {
				$join .= " left join v_PersonEncrypHIV peh with(nolock) on peh.Person_id = ED.Person_id";
				$selectPersonData = "case when peh.PersonEncrypHIV_Encryp is null then convert(varchar(10), PS.Person_Birthday, 104) end as Person_Birthday,
					case when peh.PersonEncrypHIV_Encryp is null then PS.Person_SurName else peh.PersonEncrypHIV_Encryp end as Person_Surname,
					case when peh.PersonEncrypHIV_Encryp is null then PS.Person_FirName end as Person_Firname,
					case when peh.PersonEncrypHIV_Encryp is null then PS.Person_SecName end as Person_Secname";
			}
			$query = "
				select
					ED.EvnDirection_id,
					ED.EvnDirection_Num,
					ED.Diag_id,
					Diag.Diag_Code,
					Diag.Diag_Name,
					ED.DirType_id,
					DirType.DirType_Name,
					ED.Lpu_id,--направившее ЛПУ
					ED.Lpu_did,
					Lpu.Org_id,
					Lpu.Lpu_Nick,
					ED.LpuSection_id,--направившее отделение
					ED.LpuSection_did as LpuSection_did,-- куда направлен
					ED.LpuSectionProfile_id,
					LSP.LpuSectionProfile_Code,
					LSP.LpuSectionProfile_Name,
					convert(varchar(10), cast(ISNULL(ISNULL(TTG.TimetableGraf_begTime, TTP.TimetablePar_begTime), TTS.TimetableStac_setDate) as date), 104) as EvnDirection_getDate,
					convert(varchar(10), ISNULL(ISNULL(TTG.TimetableGraf_begTime, TTP.TimetablePar_begTime), TTS.TimetableStac_setDate), 104) + ' ' + 
						convert(varchar(5), ISNULL(ISNULL(TTG.TimetableGraf_begTime, TTP.TimetablePar_begTime), TTS.TimetableStac_setDate), 108) as EvnDirection_setDateTime,
					convert(varchar(10), ED.EvnDirection_setDate, 104) as EvnDirection_setDate,
					EvnDirection_Descr,
					ED.MedPersonal_id as MedStaffFact_id,
					isnull(MSF.Person_Fio, '') as MedStaffFact_FIO,
					ED.MedPersonal_zid as MedStaffFact_zid,
					isnull(ZMSF.Person_Fio, '') as MedStaffFact_ZFIO,
					{$select}
					{$selectPersonData}
				from v_EvnDirection_all ED with (nolock)
				left join v_PersonState PS with (nolock) on ED.Person_id = PS.Person_id
				left join LpuSectionProfile LSP with (nolock) on LSP.LpuSectionProfile_id = ED.LpuSectionProfile_id
				left join Diag with (nolock) on Diag.Diag_id = ED.Diag_id
				left join DirType with (nolock) on DirType.DirType_id = ED.DirType_id
				left join v_Lpu Lpu with (nolock) on Lpu.Lpu_id = ED.Lpu_id -- направившее лпу
				left join v_MedPersonal MSF with (nolock) on MSF.MedPersonal_id = ED.MedPersonal_id and Lpu.Lpu_id = MSF.Lpu_id
				left join v_MedPersonal ZMSF with (nolock) on ZMSF.MedPersonal_id = ED.MedPersonal_zid and Lpu.Lpu_id = ZMSF.Lpu_id
				
				left join v_TimeTableGraf_lite TTG with (nolock) on TTG.EvnDirection_id = ED.EvnDirection_id
				left join TimetablePar TTP with (nolock) on TTP.TimetablePar_id = ED.TimetablePar_id
				left join v_TimetableStac_lite TTS with (nolock) on TTS.EvnDirection_id = ED.EvnDirection_id
				{$join}
				where {$filter}
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
		else 
			return false;
	}
	
	/**
	* Загрузка полного направления по стоматке
	*/
	function loadEvnDirectionStomFull($data) {
	
		$params = array();
		$filter = "(1=1) ";
		$join = "";
		if (isset($data['EvnDirection_id']) && ($data['EvnDirection_id']>0))
		{
			$params['EvnDirection_id'] = $data['EvnDirection_id'];
			$filter .= "and ED.EvnDirection_id = :EvnDirection_id ";
		}
		elseif  (isset($data['TimetableGraf_id']) && ($data['TimetableGraf_id']>0))
		{
			$params['TimetableGraf_id'] = $data['TimetableGraf_id'];
			$filter .= "and TTG.TimetableGraf_id = :TimetableGraf_id ";
		}
		elseif ($data['EvnVizitPLStom_id']>0)
		{
			$params['EvnVizitPLStom_id'] = $data['EvnVizitPLStom_id'];
			$filter .= "and EV.EvnVizitPLStom_id = :EvnVizitPLStom_id ";
			$join .= "inner join v_EvnVizitPLStom EV with(nolock) on EV.TimetableGraf_id = TTG.TimetableGraf_id ";
		}
		if (count($params)>0)
		{
			$selectPersonData = "
					convert(varchar(10), ps.Person_Birthday, 104) as Person_Birthday,
					ps.Person_Surname,
					ps.Person_Firname,
					ps.Person_Secname";
			if (allowPersonEncrypHIV($data['session'])) {
				$join .= " left join v_PersonEncrypHIV peh with(nolock) on peh.Person_id = ED.Person_id";
				$selectPersonData = "case when peh.PersonEncrypHIV_Encryp is null then convert(varchar(10), PS.Person_Birthday, 104) end as Person_Birthday,
					case when peh.PersonEncrypHIV_Encryp is null then PS.Person_SurName else peh.PersonEncrypHIV_Encryp end as Person_Surname,
					case when peh.PersonEncrypHIV_Encryp is null then PS.Person_FirName end as Person_Firname,
					case when peh.PersonEncrypHIV_Encryp is null then PS.Person_SecName end as Person_Secname";
			}
			$query = "
				select
					ED.EvnDirection_id,
					ED.EvnDirection_Num,
					ED.Diag_id,
					Diag.Diag_Code,
					Diag.Diag_Name,
					ED.DirType_id,
					DirType.DirType_Name,
					ED.Lpu_did,
					Lpu.Lpu_Nick,
					ED.LpuSectionProfile_id,
					LSP.LpuSectionProfile_Code,
					LSP.LpuSectionProfile_Name,
					convert(varchar(10), cast(ISNULL(ISNULL(TTG.TimetableGraf_begTime, TTP.TimetablePar_begTime), TTS.TimetableStac_setDate) as date), 104) as EvnDirection_getDate,
					convert(varchar(10), ISNULL(ISNULL(TTG.TimetableGraf_begTime, TTP.TimetablePar_begTime), TTS.TimetableStac_setDate), 104) + ' ' + 
						convert(varchar(5), ISNULL(ISNULL(TTG.TimetableGraf_begTime, TTP.TimetablePar_begTime), TTS.TimetableStac_setDate), 108) as EvnDirection_setDateTime,
					convert(varchar(10), ED.EvnDirection_setDate, 104) as EvnDirection_setDate,
					EvnDirection_Descr,
					ED.MedPersonal_id as MedStaffFact_id,
					isnull(MSF.Person_Fio, '') as MedStaffFact_FIO,
					ED.MedPersonal_zid as MedStaffFact_zid,
					isnull(ZMSF.Person_Fio, '') as MedStaffFact_ZFIO,
					{$selectPersonData}
				from v_EvnDirection_all ED with (nolock)
				left join v_PersonState PS with (nolock) on ED.Person_id = PS.Person_id
				left join LpuSectionProfile LSP with (nolock) on LSP.LpuSectionProfile_id = ED.LpuSectionProfile_id
				left join Diag with (nolock) on Diag.Diag_id = ED.Diag_id
				left join DirType with (nolock) on DirType.DirType_id = ED.DirType_id
				left join v_Lpu Lpu with (nolock) on Lpu.Lpu_id = ED.Lpu_did
				left join v_MedPersonal MSF with (nolock) on MSF.MedPersonal_id = ED.MedPersonal_id and Lpu.Lpu_id = MSF.Lpu_id
				left join v_MedPersonal ZMSF with (nolock) on ZMSF.MedPersonal_id = ED.MedPersonal_zid and Lpu.Lpu_id = ZMSF.Lpu_id
				
				left join v_TimeTableGraf_lite TTG with (nolock) on TTG.EvnDirection_id = ED.EvnDirection_id
				left join TimetablePar TTP with (nolock) on TTP.TimetablePar_id = ED.TimetablePar_id
				left join v_TimetableStac_lite TTS with (nolock) on TTS.EvnDirection_id = ED.EvnDirection_id
				{$join}
				where {$filter}
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
		else 
			return false;
	}
	
	/**
	 * Загрузка данных по направлению
	 */
	function loadEvnDirectionEditForm($data) {
	
		$params = array();
		$filter = "(1=1) ";
		$join = "";
		$fields = "";
		if ($data['EvnDirection_id']>0)
		{
			$params['EvnDirection_id'] = $data['EvnDirection_id'];
			$filter .= "and ED.EvnDirection_id = :EvnDirection_id ";
		}
		if ($data['TimetableGraf_id']>0)
		{
			$params['TimetableGraf_id'] = $data['TimetableGraf_id'];
			$filter .= "and TTG.TimetableGraf_id = :TimetableGraf_id ";
		}
		elseif ($data['EvnVizitPL_id']>0)
		{
			$params['EvnVizitPL_id'] = $data['EvnVizitPL_id'];
			$filter .= "and EV.EvnVizitPL_id = :EvnVizitPL_id ";
			$join .= "inner join v_EvnVizitPL EV with(nolock) on EV.TimetableGraf_id = TTG.TimetableGraf_id ";
		}

		if(getRegionNick() == 'penza') {
			$fields .= " 
				TTL.TreatmentType_id,
			";
			$join .= "
				left join r58.TreatmentTypeLink TTL with (nolock) on TTL.EvnDirection_id = ED.EvnDirection_id
			";
		}
		if(getRegionNick() == 'kz') {
			$fields .= "
				gbel.GetBed_id,
				edla.PayTypeKAZ_id,
				edla.ScreenType_id,
				edla.TreatmentClass_id,
				edla.EvnLinkAPP_StageRecovery,
				edla.PurposeHospital_id,
				edla.ReasonHospital_id,
				edla.Diag_cid,
			";
			$join .= "
				left join r101.GetBedEvnLink gbel (nolock) on gbel.Evn_id = ED.EvnDirection_id
				left join r101.EvnLinkAPP edla (nolock) on edla.Evn_id = ED.EvnDirection_id
			";
		}

		if (count($params)>0)
		{
			$selectPersonData = "
					convert(varchar(10), ps.Person_Birthday, 104) as Person_Birthday,
					ps.Person_Surname,
					ps.Person_Firname,
					ps.Person_Secname";
			if (allowPersonEncrypHIV($data['session'])) {
				$join .= " left join v_PersonEncrypHIV peh with(nolock) on peh.Person_id = ED.Person_id";
				$selectPersonData = "case when peh.PersonEncrypHIV_Encryp is null then convert(varchar(10), PS.Person_Birthday, 104) end as Person_Birthday,
					case when peh.PersonEncrypHIV_Encryp is null then PS.Person_SurName else peh.PersonEncrypHIV_Encryp end as Person_Surname,
					case when peh.PersonEncrypHIV_Encryp is null then PS.Person_FirName end as Person_Firname,
					case when peh.PersonEncrypHIV_Encryp is null then PS.Person_SecName end as Person_Secname";
			}
			if( getRegionNick() == 'ufa' ) {
				$fields .= '
					PR.RaceType_id,
					EDPD.HIVContingentTypeFRMIS_id,
					EDPD.CovidContingentType_id,
					EDPD.HormonalPhaseType_id,
					PH.PersonHeight_Height,substring(
					convert( varchar, PH.PersonHeight_Height), 0, (datalength(convert(varchar, PH.PersonHeight_Height)) - 1) ) as PersonHeight_Height,
					IIF(PW.PersonWeight_Weight is not null, concat(substring(
						convert(varchar, PW.PersonWeight_Weight), 0, (
							datalength(convert(varchar, PW.PersonWeight_Weight)) - 1
						)
					), \' \', WO.Okei_NationSymbol), null) as PersonWeight_WeightText,
					convert(varchar, PH.PersonHeight_setDT, 104) as PersonHeight_setDT,
					convert(varchar, PW.PersonWeight_setDT, 104) as PersonWeight_setDT,';
				$join .= '
					left join v_PersonDetailEvnDirection (nolock) EDPD on EDPD.EvnDirection_id = ED.EvnDirection_id
					left join v_PersonRace (nolock) PR on PR.PersonRace_id = (select top 1 PersonRace_id from v_PersonRace where Person_id = PS.Person_id)
					left join v_PersonHeight (nolock) PH on PH.PersonHeight_id = (select top 1 PersonHeight_id from v_PersonHeight where Person_id = PS.Person_id order by PersonHeight_setDT desc)
					left join v_PersonWeight (nolock) PW on PW.PersonWeight_id = (select top 1 PersonWeight_id from v_PersonWeight where Person_id = PS.Person_id order by PersonWeight_setDT desc)
					left join v_Okei (nolock) WO on WO.Okei_id = PW.Okei_id
				';
			}
			$query = "
				select
					ED.EvnDirection_id,
					ED.EvnDirection_IsReceive,
					ED.EvnDirection_IsAuto,
					ED.EvnDirection_Num,
					ED.ConsultingForm_id,
					ED.Diag_id,
					Diag.Diag_Name,
					Diag.Diag_Code,
					ED.PayType_id,
					ED.DirType_id,
					ED.MedicalCareFormType_id,
					ED.StudyTarget_id,
					ISNULL(ED.Lpu_did, DLU.Lpu_id) as Lpu_did,
					ED.Org_oid,
					ED.LpuSection_did,
					ED.Lpu_sid,
					ED.LpuSectionProfile_id,
					ED.EvnDirection_Num,
					ED.ConsultationForm_id,
					convert(varchar(10), coalesce(TTMS.TimetableMedService_begTime, TTG.TimetableGraf_begTime, TTP.TimetablePar_begTime, TTS.TimetableStac_setDate, TTR.TimetableResource_begTime), 104) + ' ' +
						convert(varchar(5), coalesce(TTMS.TimetableMedService_begTime, TTG.TimetableGraf_begTime, TTP.TimetablePar_begTime, TTS.TimetableStac_setDate, TTR.TimetableResource_begTime), 108) as EvnDirection_setDateTime,
					convert(varchar(10), ED.EvnDirection_setDate, 104) as EvnDirection_setDate,
					convert(varchar(10), ED.EvnDirection_desDT, 104) as EvnDirection_desDT,
					ED.EvnDirection_Descr,
					ED.EvnDirection_IsCito,
					ED.MedStaffFact_id,
					ED.MedStaffFact_id as MedStaffFact_sid,
					ED.MedStaffFact_id as From_MedStaffFact_id,
					ED.MedPersonal_zid as MedStaffFact_zid,
					ED.MedPersonal_id,
					ED.LpuSection_id,
					ED.Post_id,
					ED.MedPersonal_zid,
					case when isnull(ED.EvnDirection_IsNeedOper,1)= 1 then 'false' else 'true' end as EvnDirection_IsNeedOper,
					LU.LpuUnitType_SysNick,
					ED.LpuUnitType_id as LpuUnitType_did,
					ED.EvnDirection_pid,
					ED.MedService_id,
					MST.MedServiceType_SysNick,
					ED.Resource_id,
					ED.RemoteConsultCause_id,
					rcc.RemoteConsultCause_Name,
					ED.TimetableMedService_id,
					ED.TimetableResource_id,
					null as PrescriptionType_Code,
					ep.EvnPrescr_id,
					ED.TimetableGraf_id,
					ED.TimetablePar_id,
					ED.TimetableStac_id,
					ED.ARMType_id,
					ED.MedSpec_fid,
					ED.FSIDI_id,
					dUC.UslugaComplex_id as UslugaComplex_did,
					dUC.UslugaComplex_Code,
					dUC.UslugaComplex_Name,
					dUC.UslugaCategory_id as UslugaCategory_did,
					dUCat.UslugaCategory_Code,
					dUCat.UslugaCategory_Name,
					EDO.EvnXml_id,
					EDO.EvnDirectionOper_IsAgree,
					ED.pmUser_insID,
					{$fields}
					ps.Person_id,
					ps.PersonEvn_id,
					ps.Server_id,
					tth.ToothNums,
					{$selectPersonData}
				from v_EvnDirection_all ED with (nolock)
				outer apply(
					select top 1
						EvnPrescr_id
					from
						v_EvnPrescrDirection epd (nolock)
					where
						epd.EvnDirection_id = ed.EvnDirection_id
				) ep
				outer apply (
					select (
						select
							ISNULL(CAST(tneu.ToothNumEvnUsluga_ToothNum as VARCHAR),'') + ',' as 'data()'
						from v_EvnUsluga eu with (nolock)
						inner join v_ToothNumEvnUsluga tneu with(nolock) on tneu.EvnUsluga_id = eu.EvnUsluga_id
						where eu.EvnPrescr_id = ep.EvnPrescr_id
					for xml path(''), TYPE
					) as ToothNums
				) tth
				left join v_LpuSection LS with(nolock) on LS.LpuSection_id = ED.LpuSection_did
				left join v_LpuUnit LU with(nolock) on LU.LpuUnit_id = LS.LpuUnit_id
				left join v_LpuUnit DLU with(nolock) on DLU.LpuUnit_id = ED.LpuUnit_did
				left join v_Lpu DL with(nolock) on DL.Lpu_id = ED.Lpu_did
				left join v_Org DO with(nolock) on DO.Org_id = DL.Org_id
				left join v_PersonState PS with (nolock) on ED.Person_id = PS.Person_id
				left join LpuSectionProfile LSP with (nolock) on LSP.LpuSectionProfile_id = ED.LpuSectionProfile_id
				left join Diag with (nolock) on Diag.Diag_id = ED.Diag_id
				left join v_TimeTableGraf_lite TTG with (nolock) on TTG.EvnDirection_id = ED.EvnDirection_id
				left join TimetablePar TTP with (nolock) on TTP.TimetablePar_id = ED.TimetablePar_id
				left join v_TimetableStac_lite TTS with (nolock) on TTS.EvnDirection_id = ED.EvnDirection_id
				left join v_TimetableMedService_lite TTMS with (nolock) on TTMS.EvnDirection_id = ED.EvnDirection_id
				left join v_TimetableResource_lite TTR with (nolock) on TTR.EvnDirection_id = ED.EvnDirection_id
				left join EvnDirectionOper edo (nolock) on edo.EvnDirectionOper_id = ed.EvnDirection_id
				left join v_UslugaComplex dUC with(nolock) on dUC.UslugaComplex_id = ED.UslugaComplex_did
				left join v_UslugaCategory dUCat with(nolock) on dUC.UslugaCategory_id = dUCat.UslugaCategory_id
				left join v_RemoteConsultCause rcc with(nolock) on rcc.RemoteConsultCause_id = ED.RemoteConsultCause_id
				left join v_MedService MS with(nolock) on MS.MedService_id = ED.MedService_id
				left join v_MedServiceType MST with(nolock) on MST.MedServiceType_id = MS.MedServiceType_id
				{$join}
				where {$filter}
			";
			$result = $this->db->query($query, $params);

			if ( is_object($result) ) {
				return $result->result('array');
			}
			else {
				return false;
			}
		}
		else 
			return false;
	}
	
	/**
	 * Дополнение данных по направлению для postgres
	 */
	function additionForEDEditFormPostgre($data) {
		$params = array(
			'EvnDirection_id' => $data['EvnDirection_id'],
			'RemoteConsultCause_id' => !empty($data['RemoteConsultCause_id'])?$data['EvnDirection_id']:null,
		);

		$fields = '';
		$join = '';
		if(getRegionNick() == 'penza') {
			$fields .= ",TTL.TreatmentType_id";
			$join .= "left join r58.TreatmentTypeLink TTL with (nolock) on TTL.EvnDirection_id = ep.EvnDirection_id
			";
		}

		$query = "
			select
				convert(varchar(10), TTMS.TimetableMedService_begTime, 104) + ' ' +
				convert(varchar(5), TTMS.TimetableMedService_begTime, 108) as EvnDirection_setDateTime,
				rcc.RemoteConsultCause_Name,
				ep.EvnPrescr_id,
				tth.ToothNums
				{$fields}
			from EvnPrescrDirection ep with (nolock)
				outer apply (
					select (
						select
							ISNULL(CAST(tneu.ToothNumEvnUsluga_ToothNum as VARCHAR),'') + ',' as 'data()'
						from v_EvnUsluga eu with (nolock)
						inner join v_ToothNumEvnUsluga tneu with(nolock) on tneu.EvnUsluga_id = eu.EvnUsluga_id
						where eu.EvnPrescr_id = ep.EvnPrescr_id
					for xml path(''), TYPE
					) as ToothNums
				) tth
				left join v_RemoteConsultCause rcc with(nolock) on rcc.RemoteConsultCause_id = :RemoteConsultCause_id
				left join v_TimeTableGraf_lite TTG with (nolock) on TTG.EvnDirection_id = ep.EvnDirection_id
				left join v_TimetableStac_lite TTS with (nolock) on TTS.EvnDirection_id = ep.EvnDirection_id
				left join v_TimetableMedService_lite TTMS with (nolock) on TTMS.EvnDirection_id = ep.EvnDirection_id
				left join v_TimetableResource_lite TTR with (nolock) on TTR.EvnDirection_id = ep.EvnDirection_id
				{$join}
			where
				ep.EvnDirection_id = :EvnDirection_id
		";
		return $this->queryResult($query, $data);
	}

	/**
	 * Загрузка полей по направлению для печати
	 */
	function getEvnDirectionFields($data) {

		$UslugaComplexNameField = "ISNULL(UC.UslugaComplex_Name,'')";

		if ($this->regionNick == 'ufa' && $this->options['prescription']['service_name_show_type'] == 1) {

			$UslugaComplexNameField = "COALESCE(UCMS.UslugaComplex_Name,UC.UslugaComplex_Name,'')";
		}
		
		$query = "
			select top 1
				d.EvnDirection_Num,
				isnull(fedMCFT.MedicalCareFormType_Code, '') as MedicalCareFormType_Code,
				-- Адрес направляющего учреждения
				case when a.klcity_id != 3310 or a.klcity_id is null then isnull((select top 1
				rtrim(KLSocr.KLSocr_Nick) + ' ' + rtrim(KLArea.KLArea_Name) + ', '
				from klarea with (nolock)
				left outer join KLSocr with (nolock) on KLSocr.KLSocr_id = KLArea.KLSocr_id
				where klarea.klarea_id=a.klrgn_id),'') else '' end+
				isnull((select top 1
				rtrim(KLSocr.KLSocr_Nick) + ' ' + rtrim(KLArea.KLArea_Name) + ', '
				from klarea with (nolock)
				left outer join KLSocr with (nolock) on KLSocr.KLSocr_id = KLArea.KLSocr_id
				where klarea.klarea_id=a.klsubrgn_id),'')+
				isnull((select top 1
				rtrim(KLSocr.KLSocr_Nick) + ' ' + rtrim(KLArea.KLArea_Name) + ', '
				from klarea with (nolock)
				left outer join KLSocr with (nolock) on KLSocr.KLSocr_id = KLArea.KLSocr_id
				where klarea.klarea_id=a.klcity_id),'')+
				isnull((select top 1
				rtrim(KLSocr.KLSocr_Nick) + ' ' + rtrim(KLArea.KLArea_Name) + ', '
				from klarea with (nolock)
				left outer join KLSocr with (nolock) on KLSocr.KLSocr_id = KLArea.KLSocr_id
				where klarea.klarea_id=a.kltown_id),'')+
				isnull((select top 1
				rtrim(KLSocr.KLSocr_Nick) + ' ' + rtrim(KLStreet.KLStreet_Name) + ', '
				from KlStreet with (nolock)
				left outer join KLSocr with(nolock) on KLSocr.KLSocr_id = KlStreet.KLSocr_id
				where KlStreet.KlStreet_id=a.KlStreet_id),'')+
				isnull('д '+RTrim(a.Address_House),'')+
				isnull(', кв '+RTrim(a.Address_Flat),'') as Address_Address,
				-- Адрес подразделения, куда направляют
				case when lua.klcity_id != 3310 or lua.klcity_id is null then isnull((select top 1
				rtrim(KLSocr.KLSocr_Nick) + ' ' + rtrim(KLArea.KLArea_Name) + ', '
				from klarea with (nolock)
				left outer join KLSocr with (nolock) on KLSocr.KLSocr_id = KLArea.KLSocr_id
				where klarea.klarea_id=lua.klrgn_id),'') else '' end+
				isnull((select top 1
				rtrim(KLSocr.KLSocr_Nick) + ' ' + rtrim(KLArea.KLArea_Name) + ', '
				from klarea with (nolock)
				left outer join KLSocr with (nolock) on KLSocr.KLSocr_id = KLArea.KLSocr_id
				where klarea.klarea_id=lua.klsubrgn_id),'')+
				isnull((select top 1
				rtrim(KLSocr.KLSocr_Nick) + ' ' + rtrim(KLArea.KLArea_Name) + ', '
				from klarea with (nolock)
				left outer join KLSocr with (nolock) on KLSocr.KLSocr_id = KLArea.KLSocr_id
				where klarea.klarea_id=lua.klcity_id),'')+
				isnull((select top 1
				rtrim(KLSocr.KLSocr_Nick) + ' ' + rtrim(KLArea.KLArea_Name) + ', '
				from klarea with (nolock)
				left outer join KLSocr with (nolock) on KLSocr.KLSocr_id = KLArea.KLSocr_id
				where klarea.klarea_id=lua.kltown_id),'')+
				isnull((select top 1
				rtrim(KLSocr.KLSocr_Nick) + ' ' + rtrim(KLStreet.KLStreet_Name) + ', '
				from KlStreet with (nolock)
				left outer join KLSocr with (nolock) on KLSocr.KLSocr_id = KlStreet.KLSocr_id
				where KlStreet.KlStreet_id=lua.KlStreet_id),'')+
				isnull('д '+RTrim(lua.Address_House),'')+
				isnull(', кв '+RTrim(lua.Address_Flat),'') as LpuUnit_Address,

				msf.Person_Snils as med_snils,
				msf.medpersonal_init as med_init,
				zav.medpersonal_init as zav_init,
				rtrim(l.Lpu_Name) as Lpu_Name,
				rtrim(l.Lpu_f003mcod) as Lpu_f003mcod,
				rtrim(l.Lpu_OGRN) as Lpu_OGRN,
				rtrim(ISNULL(ld.Lpu_Name, od.Org_Name)) as dLpu_Name,
				rtrim(lsp.LpuSectionProfile_Name) as LpuSectionProfile_Name, 
				case when pol.Polis_endDate is null OR d.EvnDirection_setDate < pol.Polis_endDate 
				then 
					(case when pol.PolisType_id = 4 then '' else isnull(pol.Polis_Ser, '') end) 
					+' ' 
					+(case when pol.PolisType_id = 4 and p.Person_EdNum is not null then p.Person_EdNum else isnull(pol.Polis_Num, '') end)
				else ''
				end as Polis,
				ISNULL(p.Person_Inn,'') as INN,
				rtrim(p.Person_Surname)+' '+rtrim(p.Person_Firname)+' '+isnull(rtrim(p.Person_Secname),'') as Person_FIO,
				convert(varchar, Person_Birthday, 104) as Person_Birthdate,
				rtrim(Person_Phone) as Person_Phone,
				case when pa1.Address_id is not null
				then
						case when pa1.klcity_id != 3310 or pa1.klcity_id is null then isnull((select top 1
						rtrim(KLSocr.KLSocr_Nick) + ' ' + rtrim(KLArea.KLArea_Name) + ', '
						from klarea with (nolock)
						left outer join KLSocr with (nolock) on KLSocr.KLSocr_id = KLArea.KLSocr_id
						where klarea.klarea_id=pa1.klrgn_id),'') else '' end+
						isnull((select top 1
						rtrim(KLSocr.KLSocr_Nick) + ' ' + rtrim(KLArea.KLArea_Name) + ', '
						from klarea with (nolock)
						left outer join KLSocr with (nolock) on KLSocr.KLSocr_id = KLArea.KLSocr_id
						where klarea.klarea_id=pa1.klsubrgn_id),'')+
						isnull((select top 1
						rtrim(KLSocr.KLSocr_Nick) + ' ' + rtrim(KLArea.KLArea_Name) + ', '
						from klarea with (nolock)
						left outer join KLSocr with (nolock) on KLSocr.KLSocr_id = KLArea.KLSocr_id
						where klarea.klarea_id=pa1.klcity_id),'')+
						isnull((select top 1
						rtrim(KLSocr.KLSocr_Nick) + ' ' + rtrim(KLArea.KLArea_Name) + ', '
						from klarea with (nolock)
						left outer join KLSocr with (nolock) on KLSocr.KLSocr_id = KLArea.KLSocr_id
						where klarea.klarea_id=pa1.kltown_id),'')+
						isnull((select top 1
						rtrim(KLSocr.KLSocr_Nick) + ' ' + rtrim(KLStreet.KLStreet_Name) + ', '
						from KlStreet with (nolock)
						left outer join KLSocr with (nolock) on KLSocr.KLSocr_id = KlStreet.KLSocr_id
						where KlStreet.KlStreet_id=pa1.KlStreet_id),'')+
						isnull('д '+RTrim(pa1.Address_House)+', ','')+
						isnull('кор '+RTrim(pa1.Address_Corpus)+', ','')+
						isnull('кв '+RTrim(pa1.Address_Flat),'')
				else
						case when pa.klcity_id != 3310 or pa.klcity_id is null then isnull((select top 1
						rtrim(KLSocr.KLSocr_Nick) + ' ' + rtrim(KLArea.KLArea_Name) + ', '
						from klarea with (nolock)
						left outer join KLSocr with (nolock) on KLSocr.KLSocr_id = KLArea.KLSocr_id
						where klarea.klarea_id=pa.klrgn_id),'') else '' end+
						isnull((select top 1
						rtrim(KLSocr.KLSocr_Nick) + ' ' + rtrim(KLArea.KLArea_Name) + ', '
						from klarea with (nolock)
						left outer join KLSocr with (nolock) on KLSocr.KLSocr_id = KLArea.KLSocr_id
						where klarea.klarea_id=pa.klsubrgn_id),'')+
						isnull((select top 1
						rtrim(KLSocr.KLSocr_Nick) + ' ' + rtrim(KLArea.KLArea_Name) + ', '
						from klarea with (nolock)
						left outer join KLSocr with (nolock) on KLSocr.KLSocr_id = KLArea.KLSocr_id
						where klarea.klarea_id=pa.klcity_id),'')+
						isnull((select top 1
						rtrim(KLSocr.KLSocr_Nick) + ' ' + rtrim(KLArea.KLArea_Name) + ', '
						from klarea with (nolock)
						left outer join KLSocr with (nolock) on KLSocr.KLSocr_id = KLArea.KLSocr_id
						where klarea.klarea_id=pa.kltown_id),'')+
						isnull((select top 1
						rtrim(KLSocr.KLSocr_Nick) + ' ' + rtrim(KLStreet.KLStreet_Name) + ', '
						from KlStreet with (nolock)
						left outer join KLSocr with (nolock) on KLSocr.KLSocr_id = KlStreet.KLSocr_id
						where KlStreet.KlStreet_id=pa.KlStreet_id),'')+
						isnull('д '+RTrim(pa.Address_House)+', ','')+
						isnull('кор '+RTrim(pa.Address_Corpus)+', ','')+
						isnull('кв '+RTrim(pa.Address_Flat),'')
				end
				as Person_Address,
				d.DirType_id,
				ES.EvnStatus_SysNick,
				(case when Job_nfData is null or Job_nfData = '' then rtrim(jn.Job_Name) else Job_nfData end) as Job_Name,
				rtrim(Post_Name) as Post_Name,
				rtrim(diag.Diag_Code) as Diag_Code,
				rtrim(diag.Diag_Name) as Diag_Name,
				d.EvnDirection_Descr,
				datepart(dd, d.EvnDirection_setDate) as Dir_Day,
				datepart(m, d.EvnDirection_setDate) as Dir_Month,
				datepart(yyyy, d.EvnDirection_setDate) as Dir_Year,
				case when pol.Polis_endDate is null OR d.EvnDirection_setDate < pol.Polis_endDate 
				then
				(o.OrgSmo_Nick) 
				else '' 
				end
				as OrgSmo_Nick,
				PersisPost.name as PostMed_Name,
				--StacType_Name,
				case
				when d.TimetableGraf_id is not null then convert(varchar(10),TimetableGraf_begTime, 104)+' '+convert(varchar(5),TimetableGraf_begTime, 108)
				when d.TimetableStac_id is not null then convert(varchar(10),TimetableStac_setDate, 104)
				when d.TimetablePar_id is not null then convert(varchar(10),TimetablePar_begTime, 104)+' '+convert(varchar(5),TimetablePar_begTime, 108)
				when ttms.TimetableMedService_id is not null then convert(varchar(10), ttms.TimetableMedService_begTime, 104)+' '+convert(varchar(5), ttms.TimetableMedService_begTime, 108)
				when ttr.TimetableResource_id is not null then convert(varchar(10), ttr.TimetableResource_begTime, 104)+' '+convert(varchar(5), ttr.TimetableResource_begTime, 108)
				end
				as RecDate,
				EP.EvnPrescr_IsCito,
				ISNULL(EQueue.EvnQueue_id,0) as EvnQueue_id,
				case when d.TimetableGraf_id is not null
						then msfmp.MedPersonal_FIO
				when d.TimetableStac_id is not null
						then ls.LpuSection_Name
				when d.TimetablePar_id is not null
						then ls1.LpuSection_Name
				/*when d.MedPersonal_did is not null
						then msf1.Person_FIO*/
				when d.LpuSection_did is not null
						then ls2.LpuSection_Name
				when d.TimetableMedService_id is not null
						then ls3.LpuSection_Name
				end as RecMP,
				ls22.LpuSection_Name as LpuSection_NameAstra,
				lsp2.LpuSectionProfile_Name as LpuSectionProfile_NameAstra,
				isnull(ctt.CauseTreatmentType_Name,ctt2.CauseTreatmentType_Name) as CauseTreatmentType_Name,
				lumsf.LpuUnit_Phone,
				lud.LpuUnit_Phone as Contact_Phone,
				COALESCE(ls2.LpuSection_Contacts, ls.LpuSection_Contacts, ls3.LpuSection_Contacts) as SectionContact_Phone,
				ld.Lpu_Phone as Lpu_Phone,
				d.TimetableGraf_id,
				d.TimetableStac_id,
				d.TimetablePar_id,
				lud.LpuUnit_Name as dLpuUnit_Name,
				d.MedPersonal_did,
				ISNULL(UC.UslugaComplex_Code,'') + ' ' + {$UslugaComplexNameField} as Usluga_Name,
				EPLD.EvnPrescrLabDiag_id,
				UC.UslugaComplex_id,
				d.MedService_id
			from v_EvnDirection_all d  with (nolock)
			/*outer apply(
				select top 1 Person_FIO
				from v_MedPersonal mp with (nolock)
				where
					mp.MedPersonal_id = d.MedPersonal_did
					and mp.Lpu_id = d.Lpu_did
			) msf1*/
			left join DirType dt with (nolock) on d.DirType_id = dt.DirType_id
			left join v_Person_ER p with (nolock) on p.Person_id = d.Person_id
			left join Polis pol with (nolock) on p.Polis_id = pol.Polis_id
			left join v_OrgSmo o with (nolock) on pol.OrgSmo_id = o.OrgSmo_id
			left join LpuSectionProfile lsp with (nolock) on lsp.LpuSectionProfile_id=d.LpuSectionProfile_id
			left join Diag with (nolock) on Diag.Diag_id = d.Diag_id
			left join v_EvnStatus ES (nolock) on ES.EvnStatus_id = d.EvnStatus_id
			outer apply (select top 1 mf.post_id from v_MedStaffFact mf with(nolock) where d.medstafffact_id= mf.medstafffact_id  )MSFf
			left join persis.v_Post PersisPost with (nolock) on COALESCE(MSFf.post_id,d.Post_id) = PersisPost.id
			outer apply(
				select top 1
					msf.MedPersonal_FIO as Person_FIO,
					msf.LpuUnit_id,
					msf.MedDol_id,
					msf.medpersonal_init,
					msf.Person_Snils
				from v_Medstafffact_ER msf with (nolock)
				where
					msf.medpersonal_id = d.Medpersonal_id
					and msf.Lpu_id = d.Lpu_id
			) msf
			outer apply(
				select top 1
					zav.MedPersonal_FIO as Person_FIO,
					msf.LpuUnit_id,
					medpersonal_init
				from v_Medstafffact_ER zav with (nolock)
				where
					zav.medpersonal_id = d.Medpersonal_zid
					and zav.Lpu_id = d.Lpu_id
			) zav
			outer apply(
				select top 1 EQ.EvnQueue_id
				from v_EvnQueue EQ
				where EQ.EvnDirection_id = d.EvnDirection_id
			) EQueue
			left join v_Lpu l with (nolock) on l.Lpu_id=d.Lpu_id
			left join v_Lpu ld with (nolock) on ld.Lpu_id=d.Lpu_did
			left join Org od with (nolock) on od.Org_id = d.Org_oid
			left join v_LpuUnit_ER lud with (nolock) on lud.LpuUnit_id=d.LpuUnit_did
			left join v_LpuUnit_ER lumsf with (nolock) on msf.LpuUnit_id=lumsf.LpuUnit_id
			left join Address a with (nolock) on l.UAddress_id = a.Address_id
			left join Address lua with (nolock) on lud.Address_id = lua.Address_id
			left join Address pa with (nolock) on pa.Address_id = p.UAddress_id
			left join Address pa1 with (nolock) on pa1.Address_id = p.PAddress_id
			left join Job j with (nolock) on j.Job_id = p.Job_id
			left join v_Job_ER jn with (nolock) on jn.Job_id = p.Job_id
			left join Post with (nolock) on post.Post_id = j.Post_id
			--left join StacType st with (nolock) on d.StacType_id = st.StacType_id
			left join v_TimeTableGraf_lite ttg with (nolock) on d.EvnDirection_id = ttg.EvnDirection_id and ttg.Person_id is not null
			left join v_medstafffact_er msfmp with (nolock) on ttg.MedStaffFact_id = msfmp.MedStaffFact_id
			left join v_TimetableStac_lite tts with (nolock) on d.EvnDirection_id = tts.EvnDirection_id and tts.Person_id is not null
			left join v_LpuSection ls with (nolock) on tts.LpuSection_id = ls.LpuSection_id
			left join v_TimetablePar ttp with (nolock) on d.TimetablePar_id = ttp.TimetablePar_id and ttp.Person_id is not null
			left join v_LpuSection ls1 with (nolock) on ttp.LpuSection_id = ls1.LpuSection_id
			left join v_TimetableMedService_lite ttms with (nolock) on d.EvnDirection_id = ttms.EvnDirection_id and ttms.Person_id is not null
			left join v_EvnPrescrVK epvk with (nolock) on epvk.TimetableMedService_id = ttms.TimetableMedService_id
			left join v_CauseTreatmentType ctt with (nolock) on ctt.CauseTreatmentType_id = epvk.CauseTreatmentType_id
			left join v_EvnPrescrVK epvk2 with (nolock) on epvk2.PersonEvn_id = d.PersonEvn_id and epvk2.Diag_id = d.Diag_id
			left join v_CauseTreatmentType ctt2 with (nolock) on ctt2.CauseTreatmentType_id = epvk2.CauseTreatmentType_id
			left join v_LpuSection ls2 with(nolock) on ls2.LpuSection_id = d.LpuSection_did
			left join v_LpuSection ls22 with(nolock) on ls22.LpuSection_id = d.LpuSection_id
			left join v_LpuSectionProfile lsp2 with(nolock) on lsp2.LpuSectionProfile_id = ls22.LpuSectionProfile_id
			left join v_MedService MS3 with (nolock) on MS3.MedService_id = ttms.MedService_id
			left join v_LpuSection ls3 with(nolock) on ls3.LpuSection_id = MS3.LpuSection_id
			left join LpuSectionProfile lsp3 with (nolock) on lsp3.LpuSectionProfile_id=ls3.LpuSectionProfile_id
			left outer join v_TimetableResource_lite ttr with (nolock) on ttr.EvnDirection_id = d.EvnDirection_id and ttr.Person_id is not null
			left join v_EvnPrescrDirection EPD with(nolock) on EPD.EvnDirection_id = d.EvnDirection_id
			left join v_EvnPrescr EP with (nolock) on EPD.EvnPrescr_id = EP.EvnPrescr_id
			left join EvnPrescrLabDiag EPLD with(nolock) on EPLD.EvnPrescr_id = EPD.EvnPrescr_id
			left join EvnPrescrFuncDiag EPFD with (nolock) on EPFD.EvnPrescr_id = EPD.EvnPrescr_id
			left join EvnPrescrFuncDiagUsluga EPFDU with (nolock) on EPFDU.EvnPrescrFuncDiag_id = EPFD.EvnPrescrFuncDiag_id
			left join EvnDirectionUslugaComplex EDUC with (nolock) on EDUC.EvnDirection_id = d.EvnDirection_id
			left join v_UslugaComplex UC with(nolock) on UC.UslugaComplex_id = coalesce(EPLD.UslugaComplex_id,EPFDU.UslugaComplex_id,educ.UslugaComplex_id)
			left join v_UslugaComplexMedService UCMS with(nolock) on UCMS.MedService_id = d.MedService_id and UCMS.UslugaComplex_id = UC.UslugaComplex_id
			left join fed.v_MedicalCareFormType fedMCFT with(nolock) on fedMCFT.MedicalCareFormType_id = d.MedicalCareFormType_id
			
			where d.EvnDirection_id = :EvnDirection_id
		";

		$result = $this->db->query(
			$query,
			array('EvnDirection_id' => $data['EvnDirection_id'])
		);

		if (is_object($result)) {

			$result = $result->result('array');
			if (!isset($result[0])) {
				return false;
			}

			$lab_services = array();
			$lab_services_ids = array();
			$medService_id = null;

			foreach ($result as $value) {

				if ($medService_id == null)
				{
					$medService_id = $value['MedService_id'];
				}

				if (!in_array($value['Usluga_Name'] ,$lab_services)) {

					if (isset($value['EvnPrescrLabDiag_id']))
						$lab_services[$value['EvnPrescrLabDiag_id']] = $value['Usluga_Name'];
					else
						$lab_services[] = $value['Usluga_Name'];
				}

				if (isset($value['EvnPrescrLabDiag_id'])) {
					if (!in_array($value['EvnPrescrLabDiag_id'], $lab_services_ids)) {

						$lab_services_ids[] = $value['EvnPrescrLabDiag_id'];
					}
				}
			}

			$result[0]['Usluga_Name'] = $lab_services;

			//получаем набор назначенных услуг из комплексной услуги
			if (count($lab_services_ids) > 0)
			{
				$mnemonikaJoin = null;
				$mnemonikaSelect = null;
				$mnemonikaOuterApply = '';

				// Вытаскиваем MedService_id из направления, он может быть не указан
				/*$medServiceQuery = "
					SELECT
						MedService_id
					FROM
						v_EvnDirection_all with (nolock)
					WHERE
						EvnDirection_id = :EvnDirection_id
				";

				$medService_id = $this->getFirstResultFromQuery($medServiceQuery, array('EvnDirection_id' => $data['EvnDirection_id']));
				*/

				// Дополнительный запрос, чтобы достать мнемонику тестов. Но только если в направлении указана мед служба для исследований MedService_id
				if ($medService_id !== null)
				{
					$mnemonikaSelect = ', analyzertest.AnalyzerTest_SysNick as AnalyzerTest_SysNick';
					$mnemonikaOuterApply = 'outer apply (
						select top 1
							at.AnalyzerTest_SysNick as AnalyzerTest_SysNick
						from
							v_UslugaComplexMedService UCMS with (nolock)
							inner join lis.v_AnalyzerTest at (nolock) on at.UslugaComplexMedService_id = UCMS.UslugaComplexMedService_id
							inner join lis.v_Analyzer a (nolock) on a.Analyzer_id = at.Analyzer_id
						where
							UCMS.UslugaComplex_id = UC.UslugaComplex_id
							and UCMS.MedService_id = :MedService_id
							and ISNULL(at.AnalyzerTest_IsNotActive, 1) = 1
							and ISNULL(a.Analyzer_IsNotActive, 1) = 1
							and (at.AnalyzerTest_endDT >= @curdate or at.AnalyzerTest_endDT is null)
					) analyzertest';
				}

				// Достаем все тесты, которые проводятся в рамках назначенных исследований. Если регион карелия и указаны MedService_id, то достаем с мнемониками
				$query = "
					declare @curdate datetime = dbo.tzGetDate();
					
					select
						UC.UslugaComplex_id,
						EPLDU.EvnPrescrLabDiag_id,
						ISNULL(UC.UslugaComplex_Code,'') as Usluga_Code,
						ISNULL(UC.UslugaComplex_Name,'') as Usluga_Name
						{$mnemonikaSelect}
					from
						v_EvnPrescrLabDiagUsluga EPLDU (nolock)
						left join v_UslugaComplex UC with(nolock) on UC.UslugaComplex_id = EPLDU.UslugaComplex_id
						{$mnemonikaOuterApply}
					where
						EPLDU.EvnPrescrLabDiag_id IN (
							".implode(',', $lab_services_ids)."
						)
				";

				$sub_services = $this->db->query($query, array('MedService_id' => $medService_id));

				if (is_object($sub_services)) {

					$sub_services = $sub_services->result('array');
					$grouped_subsvcs = array();

					foreach ($sub_services as $svc) {

						$grouped_subsvcs[$svc['EvnPrescrLabDiag_id']][] = $svc;
					}

					$result[0]['SubServices'] = $grouped_subsvcs;
				}
			}

			return $result[0];
		}
		else {
			return false;
		}
	}
	
	/**
	 * Получение данных для создания уведомления о госпиталлизации
	 */
	function getDirectionDataForNotice($data)
	{
		$params = array();
		$filter = '1=1';
		if( !empty($data['EvnDirection_id']) ) {
			$filter .= ' and ED.EvnDirection_id = :EvnDirection_id';
			$params['EvnDirection_id'] = $data['EvnDirection_id'];
		}
		
		if( !empty($data['EvnPS_id']) ) {
			$filter .= ' and EPS.EvnPS_id = :EvnPS_id';
			$params['EvnPS_id'] = $data['EvnPS_id'];
		}
		
		$query = "
			select top 1
				ED.MedPersonal_id,
				PA.Person_Fio,
				Lpu.Lpu_Nick,
				LSP.LpuSectionProfile_Name,
				Lpu_H.Lpu_Nick as Lpu_H_Nick,
				LS_H.LpuSection_FullName as LpuSection_H_FullName,
				convert(varchar(10), cast(EPS.EvnPS_setDate as datetime), 104) + ' ' + EPS.EvnPS_setTime as EvnPS_setDT,
				PWRC.PrehospWaifRefuseCause_Name,
				LT.LeaveType_Name
			from
				v_EvnDirection_all ED with (nolock)
				left join v_Person_all PA with (nolock) on PA.Person_id = ED.Person_id
					and PA.PersonEvn_id = ED.PersonEvn_id
				left join v_Lpu Lpu with (nolock) on Lpu.Lpu_id = ED.Lpu_id
				left join v_LpuSectionProfile LSP with (nolock) on LSP.LpuSectionProfile_id = ED.LpuSectionProfile_id
				left join v_EvnPS EPS with (nolock) on EPS.EvnDirection_id = ED.EvnDirection_id
				left join v_Lpu Lpu_H with (nolock) on Lpu_H.Lpu_id = EPS.Lpu_id
				left join v_LpuSection LS_H with (nolock) on LS_H.LpuSection_id = EPS.LpuSection_eid
				left join v_PrehospWaifRefuseCause PWRC with (nolock) on PWRC.PrehospWaifRefuseCause_id = EPS.PrehospWaifRefuseCause_id
				left join v_LeaveType LT with (nolock) on LT.LeaveType_id = EPS.LeaveType_id
			where
				{$filter}
		";
		
		$result = $this->db->query($query, $params);
		if ( is_object($result) ) {
			$result = $result->result('array');
			if( isset($result[0]) )
				return $result[0];
			else
				return false;
		} else {
			return false;
		}
	}

	/**
	 * Отмена услуги в направлении
	 */
	function cancelUslugaComplex($data) {
		$queryParams = array(
			'EvnDirection_id' => $data['EvnDirection_id'],
			'UslugaComplex_id' => $data['UslugaComplex_id']
		);

		$query = "
			select
				EvnDirectionUslugaComplex_id
			from
				v_EvnDirectionUslugaComplex (nolock)
			where
				EvnDirection_id = :EvnDirection_id
				and UslugaComplex_id = :UslugaComplex_id
				and EvnDirectionUslugaComplex_pid is null
		";

		$resp = $this->queryResult($query, $queryParams);
		foreach($resp as $respone) {
			$this->deleteEvnDirectionUslugaComplex(array(
				'EvnDirectionUslugaComplex_id' => $respone['EvnDirectionUslugaComplex_id']
			));
		}
	}

	/**
	 * Удаление
	 */
	function deleteEvnDirectionUslugaComplex($data, $count = 0) {
		$limit = 10; // максимум вложенностей, для защиты от зацикливания рекурсии
		if ($count > $limit) {
			return false;
		}
		$count++;

		// рекурсивно удаляем дочерние
		$query = "
			select
				EvnDirectionUslugaComplex_id
			from
				v_EvnDirectionUslugaComplex (nolock)
			where
				EvnDirectionUslugaComplex_pid = :EvnDirectionUslugaComplex_id
		";
		$resp = $this->queryResult($query, array(
			'EvnDirectionUslugaComplex_id' => $data['EvnDirectionUslugaComplex_id']
		));
		foreach($resp as $respone) {
			$this->deleteEvnDirectionUslugaComplex(array(
				'EvnDirectionUslugaComplex_id' => $respone['EvnDirectionUslugaComplex_id']
			), $count);
		}

		$this->db->query("
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec p_EvnDirectionUslugaComplex_del
				@EvnDirectionUslugaComplex_id = :EvnDirectionUslugaComplex_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		", array(
			'EvnDirectionUslugaComplex_id' => $data['EvnDirectionUslugaComplex_id']
		));

		return true;
	}

	/**
	 * @param $data
	 * @return bool
	 */
	function loadPathoMorphologyWorkPlace($data) {
		$filter = '(1 = 1)';
		$queryList = array();
		$queryParams = array();
		$filterEDH = '';
		$filterEDMH = '';
		$requireEDH = true;

		$queryParams['Lpu_id'] = $data['Lpu_id'];

		$filter .= "
		and ED.DirFailType_id is null
		";

		if ( !empty($data['Search_SurName']) ) {
			$filter .= " and PS.Person_SurName like :Person_SurName";
			$queryParams['Person_SurName'] = rtrim($data['Search_SurName']) . '%';
		}

		if ( !empty($data['Search_FirName']) ) {
			$filter .= " and PS.Person_FirName like :Person_FirName";
			$queryParams['Person_FirName'] = rtrim($data['Search_FirName']) . '%';
		}

		if ( !empty($data['Search_SecName']) ) {
			$filter .= " and PS.Person_SecName like :Person_SecName";
			$queryParams['Person_SecName'] = rtrim($data['Search_SecName']) . '%';
		}

		if ( !empty($data['Search_BirthDay']) ) {
			$filter .= " and PS.Person_BirthDay = :Person_BirthDay";
			$queryParams['Person_BirthDay'] = $data['Search_BirthDay'];
		}

		if ( !empty($data['EvnDirection_Ser']) ) {
			$filterEDH .= " and ED.EvnDirectionHistologic_Ser like :EvnDirection_Ser";
			$filterEDMH .= " and ED.EvnDirectionMorfoHistologic_Ser like :EvnDirection_Ser";
			$queryParams['EvnDirection_Ser'] = $data['EvnDirection_Ser'] . '%';
		}

		if ( !empty($data['EvnDirection_Num']) ) {
			$filterEDH .= " and ED.EvnDirectionHistologic_Num like :EvnDirection_Num";
			$filterEDMH .= " and ED.EvnDirectionMorfoHistologic_Num like :EvnDirection_Num";
			$queryParams['EvnDirection_Num'] = $data['EvnDirection_Num'] . '%';
		}

		if ( !empty($data['begDate']) ) {
			$filterEDH .= " and ED.EvnDirectionHistologic_setDate >= :begDate";
			$filterEDMH .= " and ED.EvnDirectionMorfoHistologic_setDT >= :begDate";
			$queryParams['begDate'] = $data['begDate'];
		}

		if ( !empty($data['endDate']) ) {
			$filterEDH .= " and ED.EvnDirectionHistologic_setDate <= :endDate";
			$filterEDMH .= " and ED.EvnDirectionMorfoHistologic_setDT <= :endDate";
			$queryParams['endDate'] = $data['endDate'];
		}

		if ( !empty($data['Search_CorpseRecieptDate']) ) {
			$requireEDH = false;
			$filterEDMH .= " and MHCR.MorfoHistologicCorpseReciept_setDT >= :CorpseReciept_begDate and MHCR.MorfoHistologicCorpseReciept_setDT <= :CorpseReciept_endDate";
			$queryParams['CorpseReciept_begDate'] = $data['Search_CorpseRecieptDate'][0];
			$queryParams['CorpseReciept_endDate'] = $data['Search_CorpseRecieptDate'][1];	
		}
		
		if ( !empty($data['Search_CorpseGiveawayDate']) ) {
			$requireEDH = false;
			$filterEDMH .= " and MHCG.MorfoHistologicCorpseGiveaway_setDT >= :CorpseGiveaway_begDate and MHCG.MorfoHistologicCorpseGiveaway_setDT <= :CorpseGiveaway_endDate";
			$queryParams['CorpseGiveaway_begDate'] = $data['Search_CorpseGiveawayDate'][0];
			$queryParams['CorpseGiveaway_endDate'] = $data['Search_CorpseGiveawayDate'][1];
		}
		

		// Для EvnDirectionHistologic
		if ( (empty($data['DirectionType_id']) || $data['DirectionType_id'] == 1) && $requireEDH ) {
			// https://redmine.swan.perm.ru/issues/36707
			/*if ( $data['session']['region']['nick'] == 'ufa' ) {
				$filterEDH .= " and ED.Lpu_aid = :Lpu_id";
			}
			else {
				$filterEDH .= " and ED.Lpu_id = :Lpu_id";
			}*/

			if (!empty($data['Lpu_id'])) {
				$filterEDH .= " and ED.Lpu_aid = :Lpu_id";
			}

			$queryList[] = "
				select
					 ED.EvnDirectionHistologic_id as EvnDirection_id
                    ,1 as DirectionType_id
                    ,Evn.Person_id
                    ,Evn.PersonEvn_id
                    ,Evn.Server_id
                    ,ED.Lpu_aid as Lpu_id
                    ,EvnDirection.Lpu_did
                    ,convert(varchar(10), Evn.Evn_setDT, 104) as Group_date
                    ,PS.Person_SurName
                    ,PS.Person_FirName
                    ,PS.Person_SecName
                    ,Evn.pmUser_insID
                    ,ISNULL(IsBad.YesNo_Code, 0) as EvnDirection_IsBad
                    ,case when EDP.EvnHistologicProto_id is not null then 'true' else 'false' end as Proto_Exists
                    ,EDP.EvnHistologicProto_id as EvnProto_id
                    ,EDP.MedPersonal_id
                    ,'Патологогистологическое' as DirectionType_Name
                    ,convert(varchar(10), Evn.Evn_setDT, 104) as EvnDirection_setDate
                    ,EvnDirection.EvnDirection_Ser as EvnDirection_Ser
                    ,EvnDirection.EvnDirection_Num as EvnDirection_Num
                    ,ISNULL(PS.Person_SurName, '') + ' ' + ISNULL(PS.Person_FirName, '') + ' ' + ISNULL(PS.Person_SecName, '') as Person_FIO
                    ,convert(varchar(10), PS.Person_BirthDay, 104) as Person_BirthDay
                    ,ED.EvnDirectionHistologic_NumCard as EvnPS_NumCard
                    ,DSE.DeathSvid_Exists
				    ,null as CorpseGiveaway_Exists
                    ,null as CorpseGiveaway_Id
					,null as CorpseGiveaway_Date
					,null as CorpseReciept_Exists
					,null as CorpseReciept_Id
					,null as CorpseReciept_Date
                	,null as EvnMorfoHistologicProto_autopsyDate
				    ,null as EvnMorfoHistologicProto_deathDate
                	,null as Refuse_Exists
                	,null as MorfoHistologicRefuse_Id
				from
					v_Evn Evn with (nolock)
                    inner join v_EvnDirectionHistologic ED with (nolock) on ED.EvnDirectionHistologic_id = Evn.Evn_id
                    inner join EvnDirection EvnDirection with (nolock) on EvnDirection.EvnDirection_id = Evn.Evn_id
                    inner join v_PersonState PS with (nolock) on PS.Person_id = Evn.Person_id
                    outer apply (
                        select top 1 EvnHistologicProto_id, MedPersonal_id
                        from EvnHistologicProto with (nolock)
                        where EvnDirectionHistologic_id = ED.EvnDirectionHistologic_id
                    ) EDP
                    left join YesNo IsBad with (nolock) on IsBad.YesNo_id = ED.EvnDirectionHistologic_IsBad
                    outer apply (
                        select top 1 1 as DeathSvid_Exists from DeathSvid with(nolock) where Person_id = Evn.Person_id 
                    ) as DSE
				where
					" . $filter . "
					" . $filterEDH . "
			";
		}

		// Для EvnDirectionMorfoHistologic
		if ( empty($data['DirectionType_id']) || $data['DirectionType_id'] == 2 ) {
			// https://redmine.swan.perm.ru/issues/36707
			/*if ( $data['session']['region']['nick'] == 'ufa' ) {
				$filterEDMH .= " and oa.Org_id = l.Org_id";
			}
			else {
				$filterEDH .= " and ED.Lpu_id = :Lpu_id";
			}*/
			if (!empty($data['Lpu_id'])) {
				$filterEDMH .= " and ISNULL(oa.Org_id, ol.Org_id) = l.Org_id";
			}

			$queryList[] = "
				select
					 ED.EvnDirectionMorfoHistologic_id as EvnDirection_id
					,2 as DirectionType_id
					,ED.Person_id
					,ED.PersonEvn_id
					,ED.Server_id
					,l.Lpu_id
					,ED.Lpu_did
                    ,CONVERT(VARCHAR(10), ED.EvnDirectionMorfoHistologic_setDT, 104) AS Group_date
					,PS.Person_SurName
					,PS.Person_FirName
					,PS.Person_SecName
					,ED.pmUser_insID
					,ISNULL(IsBad.YesNo_Code, 0) as EvnDirection_IsBad
					,case when EDP.EvnMorfoHistologicProto_id is not null then 'true' else 'false' end as Proto_Exists
					,EDP.EvnMorfoHistologicProto_id as EvnProto_id
					,EDP.MedPersonal_id
					,'Патоморфогистологическое' as DirectionType_Name
         			,convert(varchar(10), ED.EvnDirectionMorfoHistologic_setDT, 104) as EvnDirection_setDate
					,ED.EvnDirectionMorfoHistologic_Ser AS EvnDirection_Ser
          			,ED.EvnDirectionMorfoHistologic_Num AS EvnDirection_Num
					,ISNULL(PS.Person_SurName, '') + ' ' + ISNULL(PS.Person_FirName, '') + ' ' + ISNULL(PS.Person_SecName, '') as Person_FIO
					,convert(varchar(10), PS.Person_BirthDay, 104) as Person_BirthDay
					,EPS.EvnPS_NumCard
					,DSE.DeathSvid_Exists
					,case when MHCG.MorfoHistologicCorpseGiveaway_id is not null then 'true' else 'false' end as CorpseGiveaway_Exists
					,MHCG.MorfoHistologicCorpseGiveaway_id as CorpseGiveaway_Id
					,convert(varchar(10), MHCG.MorfoHistologicCorpseGiveaway_setDT, 104) as CorpseGiveaway_Date
					,case when MHCR.MorfoHistologicCorpseReciept_id is not null then 'true' else 'false' end as CorpseReciept_Exists
					,MHCR.MorfoHistologicCorpseReciept_id as CorpseReciept_Id
					,convert(varchar(10), MHCR.MorfoHistologicCorpseReciept_setDT, 104) as CorpseReciept_Date
					,convert(varchar(10), EDP.EvnMorfoHistologicProto_autopsyDT, 104) as EvnMorfoHistologicProto_autopsyDate 
				    ,convert(varchar(10), EDP.EvnMorfoHistologicProto_deathDT, 104) as EvnMorfoHistologicProto_deathDate
					,case when MHR.MorfoHistologicRefuse_id is not null then 'true' else 'false' end as Refuse_Exists
					,MHR.MorfoHistologicRefuse_id as MorfoHistologicRefuse_Id
				from v_EvnDirectionMorfoHistologic ED WITH (NOLOCK)
					INNER JOIN v_PersonState PS WITH (NOLOCK) ON PS.Person_id = ED.Person_id
        			LEFT JOIN EvnPS EPS WITH (NOLOCK) ON EPS.EvnPS_id = ED.EvnPS_id
        			OUTER APPLY (
						SELECT TOP 1 EvnMorfoHistologicProto_id, MedPersonal_id, EvnMorfoHistologicProto_autopsyDT, EvnMorfoHistologicProto_deathDT
						from EvnMorfoHistologicProto with (nolock)
						inner join Evn on Evn.Evn_id  = EvnMorfoHistologicProto_id and Evn.Evn_deleted = 1
						WHERE EvnDirectionMorfoHistologic_id = ED.EvnDirectionMorfoHistologic_id
					) EDP
					left join YesNo IsBad with (nolock) on IsBad.YesNo_id = ED.EvnDirectionMorfoHistologic_IsBad
					left join v_OrgAnatom oa with (nolock) on oa.OrgAnatom_id = ED.OrgAnatom_did
					left join v_Lpu ol with (nolock) on ol.Lpu_id = ED.Lpu_did
					left join v_Lpu l with (nolock) on l.Lpu_id = :Lpu_id
				    LEFT JOIN v_MorfoHistologicCorpseGiveaway MHCG with (nolock) on ED.EvnDirectionMorfoHistologic_id = MHCG.EvnDirectionMorfoHistologic_id
					LEFT JOIN v_MorfoHistologicCorpseReciept MHCR with (nolock) on ED.EvnDirectionMorfoHistologic_id = MHCR.EvnDirectionMorfoHistologic_id
					LEFT JOIN v_MorfoHistologicRefuse MHR with (nolock) on ED.EvnDirectionMorfoHistologic_id = MHR.EvnDirectionMorfoHistologic_id
				    
					outer apply (
						select top 1 1 as DeathSvid_Exists from DeathSvid with(nolock) where Person_id = ED.Person_id
					) as DSE
				where
					" . $filter . "
					" . $filterEDMH . "
			";
		}

		$query = "
			select
				*
			from (
				" . implode(' union ', $queryList) . "
			) t1
			order by
				 t1.EvnDirection_setDate
				,t1.Person_SurName
				,t1.Person_FirName
				,t1.Person_SecName
				,t1.Person_BirthDay
		";

		//echo getDebugSQL($query, $queryParams);die;
		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}
	
	/**
	 * получения списка направлений для смены статуса в "обслужено" при приеме без записи
	 */
	function getEvnDirectionCommitList($data){
		$sql = "
			select 
			EvnDirection_id,
			TimetableGraf_id,
			EvnQueue_id,
			DLpu_Name,
			LpuSectionProfile_Name,
			recDT,
			EvnDirection_Num,
			DirType_Name,
			EvnDirection_setDT
			from(
			select
				ED.EvnDirection_id
				,Timetable.TimetableGraf_id
				,null as EvnQueue_id
				,Lpu.Lpu_Nick as DLpu_Name -- направившее ЛПУ
				,LS.LpuSectionProfile_Name
				,convert(varchar(10),isnull(Timetable.TimetableGraf_begTime, Timetable.TimetableGraf_factTime),104)+' '+
				convert(varchar(5),isnull(Timetable.TimetableGraf_begTime, Timetable.TimetableGraf_factTime),108) as recDT
				,ED.EvnDirection_Num
				,DT.DirType_Name
				,convert(varchar(10),EvnDirection_setDate,104)+' '+convert(varchar(5),EvnDirection_setTime,108) as EvnDirection_setDT
			from
				v_TimeTableGraf_lite Timetable with (NOLOCK)
				left join v_MedStaffFact MSF with (NOLOCK) on Timetable.MedStaffFact_id = MSF.MedStaffFact_id
				left join v_LpuSection LS with (nolock) on MSF.LpuSection_id = LS.LpuSection_id
				left join v_EvnDirection_all ED with (nolock) on Timetable.EvnDirection_id = ED.EvnDirection_id
				left join v_Lpu Lpu with (nolock) on Lpu.Lpu_id = ED.Lpu_sid
				left join v_DirType DT with (nolock) on DT.DirType_id = ED.DirType_id
				left join v_pmUserCache pm with (nolock) on pm.PMUser_id=isnull(ED.pmUser_insID,Timetable.pmUser_updID)
			where LS.LpuSection_id=:LpuSection_id and Timetable.Person_id=:Person_id
				and cast(isnull(Timetable.TimetableGraf_begTime, Timetable.TimetableGraf_factTime) as DATE) >= cast(dbo.tzGetDate() as DATE)
			union all
			select
				ED.EvnDirection_id
				,null as TimetableGraf_id
				,EQ.EvnQueue_id
				,Lpu.Lpu_Nick as DLpu_Name -- направившее ЛПУ
				,LSP.LpuSectionProfile_Name
				,convert(varchar(10),isnull(ED.EvnDirection_desDT,EQ.EvnQueue_setDT),104)+' '+convert(varchar(5),EQ.EvnQueue_setDT,108) as recDT
				,ED.EvnDirection_Num
				,DT.DirType_Name
				,convert(varchar(10),EvnDirection_setDate,104)+' '+convert(varchar(5),EvnDirection_setTime,108) as EvnDirection_setDT
			from
				v_EvnQueue EQ with (NOLOCK)
				left join v_EvnDirection_all ED with (nolock) on EQ.EvnQueue_id = ED.EvnQueue_id
				left join v_LpuSectionProfile LSP with (nolock) on EQ.LpuSectionProfile_did = LSP.LpuSectionProfile_id
				left join v_Lpu Lpu with (nolock) on ED.Lpu_id = Lpu.Lpu_id
				left join v_DirType DT with (nolock) on DT.DirType_id = ED.DirType_id
			where EQ.Person_id=:Person_id and LSP.LpuSectionProfile_id in (select LpuSectionProfile_id from v_LpuSection LS with (nolock) where  LS.LpuSection_id=:LpuSection_id)
				and EQ.EvnQueue_recDT is null
				and EQ.EvnQueue_failDT is null
			)ed
	";
		//print_r($data);exit();
		//echo getDebugSQL($sql, array('Person_id'=>$data['Person_id'],'LpuSection_id'=>$data['LpuSection_id']));
		$result = $this->db->query($sql, array('Person_id'=>$data['Person_id'],'LpuSection_id'=>$data['LpuSection_id']));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}
	/**
	 * Получение списка направления для формы журнала направления для регистраторов
	 */
	function getEvnDirectionList($data) {
		$params = array(
			'Lpu_id' => $data['Lpu_id'],
			'beg_date' => $data['beg_date'],
			'end_date' => $data['end_date']
		);
		$filters = '';
		
		if (!empty($data['DirType_id']))
		{
			$filters .= ' AND ED.DirType_id = :DirType_id';
			$params['DirType_id'] = $data['DirType_id'];
		}
		
		if (!empty($data['LpuSectionProfile_id']))
		{
			$filters .= ' AND ED.LpuSectionProfile_id = :LpuSectionProfile_id';
			$params['LpuSectionProfile_id'] = $data['LpuSectionProfile_id'];
		}
		
		$join = "";
		$isSearchByEncryp = false;
		$selectPersonData = "
			convert(varchar(10), PS.Person_Birthday, 104) as Person_Birthday,
			PS.Person_Surname + ' '
				+ case when PS.Person_Firname is not null then SUBSTRING(PS.Person_Firname,1,1) +'.' else '' end
				+ case when PS.Person_Secname is not null then SUBSTRING(PS.Person_Secname,1,1) +'.' else '' end
			as Person_Fio,
		";
		if (isEncrypHIVRegion($this->regionNick)) {
			if (allowPersonEncrypHIV($data['session'])) {
				$isSearchByEncryp = isSearchByPersonEncrypHIV($data['Person_Fio']);
				$join .= " left join v_PersonEncrypHIV peh with(nolock) on peh.Person_id = ED.Person_id";
				$selectPersonData = "
					case when peh.PersonEncrypHIV_Encryp is null then convert(varchar(10), PS.Person_Birthday, 104) end as Person_Birthday,
					case when peh.PersonEncrypHIV_Encryp is null 
						then PS.Person_Surname +' '+ SUBSTRING(PS.Person_Firname,1,1) +'.'+ SUBSTRING(PS.Person_Secname,1,1) +'.'
						else rtrim(peh.PersonEncrypHIV_Encryp) end 
					as Person_Fio,";
			} else {
				//Не отображать анонимных шифрованных пациентов
				$filters .= " and not exists(
					select top 1 peh.PersonEncrypHIV_Encryp
					from v_PersonEncrypHIV peh with(nolock)
					inner join v_EncrypHIVTerr eht with(nolock) on eht.EncrypHIVTerr_id = peh.EncrypHIVTerr_id
						and isnull(eht.EncrypHIVTerr_Code,0) = 20
					where peh.Person_id = ED.Person_id
				)";
			}
		}
		
		if ( !empty($data['Person_Fio']) ) {
			if (allowPersonEncrypHIV($data['session']) && $isSearchByEncryp) {
				$filters .= " and peh.PersonEncrypHIV_Encryp like :Person_Fio";
			} else {
				$filters .= " and (ISNULL(PS.Person_Surname, '') + ' ' + ISNULL(PS.Person_Firname, '') + ' ' + ISNULL(PS.Person_Secname, '')) like :Person_Fio";
			}
			$params['Person_Fio'] = $data['Person_Fio'] . '%';
		}

		if (!empty($data['Person_BirthDay']))
		{
			$filters .= ' AND cast(PS.Person_BirthDay as date) = cast(:Person_BirthDay as date)';
			$params['Person_BirthDay'] = $data['Person_BirthDay'];
		}
		
		if ($_SESSION['CurArmType'] == 'stacpriem')
			{
				$prehospSel = ",phs.PrehospStatus_id, phs.PrehospStatus_Name";

				$prehospFrom = "
					OUTER APPLY (SELECT TOP 1 *
						FROM v_EvnPS WITH (NOLOCK)
						WHERE EvnDirection_id = ED.EvnDirection_id
					) EPS
					LEFT JOIN v_PrehospStatus phs WITH (NOLOCK)
						ON EPS.PrehospStatus_id = phs.PrehospStatus_id";

				if (!empty($data['PrehospStatus_id']))
					{
						$prehospWhere = "AND phs.PrehospStatus_id = :PrehospStatus_id";
						$params['PrehospStatus_id'] = $data['PrehospStatus_id'];
					}
				else
					$prehospWhere = "";
			}
		else
			$prehospSel = $prehospFrom = $prehospWhere = "";

		$query = "
			select
				-- select
				ED.EvnDirection_id,
				ED.Lpu_id,
				Lpu.Org_id,
				Lpu.Org_Nick,
				Lpu.Org_Name,
				RTRIM(Lpu.Lpu_Nick) as Lpu_Name,
				rtrim(DLpu.Lpu_Nick) as DLpu_Nick,
				ED.EvnDirection_Num,
				ED.DirType_id,
				RTRIM(DT.DirType_Name) as DirType_Name,
				ED.LpuSectionProfile_id,
				RTRIM(LSP.LpuSectionProfile_Name) as LpuSectionProfile_Name,
				convert(varchar(10), ED.EvnDirection_setDate, 104) as EvnDirection_setDateTime,
				{$selectPersonData}
				ED.Diag_id,
				RTRIM(Diag.Diag_Code) + '. ' + RTRIM(Diag.Diag_Name) as Diag_Name,
				ED.EvnDirection_Descr,
				ED.MedPersonal_id,
				MSF.Person_FIO as MedPersonal_Fio,
				ED.Person_id,
				ED.PersonEvn_id,
				ED.Server_id,
				case 
						when ED.TimetableGraf_id is not null 
						then convert(varchar(10), TimetableGraf_begTime, 104)+' '+convert(varchar(5), TimetableGraf_begTime, 108) 
						when (ED.TimetableStac_id is not null and ED.TimetablePar_id is null)
						then convert(varchar(10), TimetableStac_setDate, 104) 
						when ED.TimetablePar_id is not null 
						then convert(varchar(10), TimetablePar_begTime, 104)+' '+convert(varchar(5), TimetablePar_begTime, 108)
						when ED.TimetableMedService_id is not null
						then convert(varchar(10), TimetableMedService_begTime, 104)+' '+convert(varchar(5), TimetableMedService_begTime, 108)
					end as RecDate,
					case when ED.TimetableGraf_id is not null 
						then msfmp.MedPersonal_FIO 
						when ED.TimetableStac_id is not null 
						then ls.LpuSection_Name 
						when ED.TimetablePar_id is not null 
						then ls1.LpuSection_Name
						when ED.TimetableMedService_id is not null 
						then ms.MedService_Name 
						when ED.MedPersonal_did is not null 
						then msf1.Person_FIO
					end as RecMP
					{$prehospSel}
				-- end select
			from 
				-- from
				v_EvnDirection_all ED with (NOLOCK)
				left join v_PersonState PS with (NOLOCK) on ED.Person_id = PS.Person_id
				outer apply(
					select top 1
						MedStaffFact_id,
						Person_FIO
					from v_MedStaffFact msf with (nolock)
					where msf.medpersonal_id = ED.Medpersonal_id and msf.Lpu_id = ED.Lpu_id and msf.LpuSection_id = ED.LpuSection_id
				) msf
				inner join v_Lpu Lpu with (NOLOCK) on Lpu.Lpu_id = ED.Lpu_id
				inner join v_Lpu DLpu with (NOLOCK) on DLpu.Lpu_id = ED.Lpu_did
				inner join v_DirType DT with (NOLOCK) on DT.DirType_id = ED.DirType_id
				left join v_LpuSectionProfile LSP with (NOLOCK) on LSP.LpuSectionProfile_id = ED.LpuSectionProfile_id
				left join v_Diag Diag with (NOLOCK) on Diag.Diag_id = ED.Diag_id
				outer apply(
					select top 1 Person_FIO
					from v_MedPersonal mp with (nolock)
					where mp.MedPersonal_id = ED.MedPersonal_did and mp.Lpu_id = ED.Lpu_did
				) msf1
				left outer join v_TimeTableGraf_lite ttg with (nolock) on ED.EvnDirection_id = ttg.EvnDirection_id
				left outer join v_MedstaffFact_er msfmp with (nolock) on ttg.MedStaffFact_id = msfmp.MedStaffFact_id 
				left outer join v_TimetableStac_lite tts with (nolock) on ED.EvnDirection_id = tts.EvnDirection_id
				left outer join LpuSection ls with (nolock) on tts.LpuSection_id = ls.LpuSection_id 
				left outer join TimetablePar ttp with (nolock) on ED.TimetablePar_id = ttp.TimetablePar_id
				left outer join LpuSection ls1 with (nolock) on ttp.LpuSection_id = ls1.LpuSection_id
				left outer join v_TimetableMedService_lite ttms with (nolock) on ED.EvnDirection_id = ttms.EvnDirection_id
				left outer join MedService ms with (nolock) on ttms.MedService_id = ms.MedService_id
				{$join}
				{$prehospFrom}
				-- end from
			where
				-- where
				cast(ED.EvnDirection_setDate as DATE) <= :end_date
				AND cast(ED.EvnDirection_setDate as DATE) >= :beg_date
				--AND EvnDirection_IsAuto is null
				{$filters}
				{$prehospWhere}
				-- end where
			order by 
				-- order by
				ED.EvnDirection_setDate DESC
				-- end order by
		";
		
		//echo getDebugSQL($query, $params);

		$result = $this->db->query(getLimitSQLPH($query, $data['start'], $data['limit']), $params);
		$result_count = $this->db->query(getCountSQLPH($query), $params);
		
		if (is_object($result_count))
		{
			$cnt_arr = $result_count->result('array');
			$count = $cnt_arr[0]['cnt'];
			unset($cnt_arr);
		}
		else
		{
			$count = 0;
		}
		if (is_object($result))
		{
			$response = array();
			$response['data'] = $result->result('array');
			$response['totalCount'] = $count;
			return $response;
		}
		else
		{
			return false;
		}
	}

	
	
	/**
	 * Получениe данных направления для заполнения полей исхода/закрытия случая лечения
	 */
	function loadDirectionDataForLeave($data) {
		$queryParams = array('EvnDirection_rid'=>$data['EvnDirection_rid']);
		$filters = 'and ED.DirType_id in (1,3,4,5)';
		if ($data['rootEvnClass_SysNick'] == 'EvnPS') {
			$filters = 'and ED.DirType_id in (1,4,5)';
		}
		$query = "
			select top 1
				ED.DirType_id,
				ED.Lpu_did,
				L.Org_id as Org_did,
				LS.LpuSection_id as LpuSection_did,
				LU.LpuUnit_id as LpuUnit_did,
				LU.LpuUnitType_SysNick
			from v_EvnDirection_all ED with (NOLOCK)
				left join v_LpuSection LS with (NOLOCK) on ED.LpuSection_did = LS.LpuSection_id
				left join v_LpuUnit LU with (NOLOCK) on isnull(ED.LpuUnit_did, LS.LpuUnit_id) = LU.LpuUnit_id
				left join v_Lpu L with (nolock) on L.Lpu_id = ED.Lpu_did
			where ED.EvnDirection_rid = :EvnDirection_rid
				-- https://redmine.swan.perm.ru/issues/28485
				and ED.DirFailType_id is null
				{$filters}
			order by ED.EvnDirection_setDT desc
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
	 * Получениe данных для проверки созданых направлений
	 */
	function loadDirectionDataForZNO($data) {
		$queryParams = array('EvnDirection_pid'=>$data['EvnDirection_pid']);
		if ($data['typeofdirection'] == 'consultation') {
			$filters = 'and ED.DirType_id in (3)';
		} else {
			$filters = 'and ED.DirType_id in (2,3,7)';
		}
		$query = "
			select top 1
				ED.EvnDirection_id,
				ED.EvnDirection_pid,
				ED.EvnDirection_rid
			from v_EvnDirection_all ED with (NOLOCK)
			where ED.EvnDirection_pid = :EvnDirection_pid
				and ED.DirFailType_id is null
				{$filters}
			order by ED.EvnDirection_setDT desc
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
	 * Получениe данных для журнала в АРМ СМО и АРМ ТФОМС
	 */
	function loadSMOWorkplaceJournal($data) {
		$filterList = array();
		$queryParams = array();
		$selectFields = array();
		$rid_where = "1=1";
		$polisJoin = "";

		if ( empty($data['Over20DaysInQueue']) && empty($data['EvnDirection_setDate']) && empty($data['EvnPS_setDate']) ) {
			return false;
		}
		else if ( empty($data['OrgSmo_id']) && empty($data['Lpu_did']) && empty($data['LpuSectionProfile_did']) && empty($data['Lpu_sid']) ) {
			return false;
		}

		if ( !empty($data['Person_Surname']) ) {
			$filterList[] = "PS.Person_Surname like :Person_Surname";
			$queryParams['Person_Surname'] = $data['Person_Surname'] . '%';
		}

		if ( !empty($data['Person_Firname']) ) {
			$filterList[] = "PS.Person_Firname like :Person_Firname";
			$queryParams['Person_Firname'] = $data['Person_Firname'] . '%';
		}

		if ( !empty($data['Person_Secname']) ) {
			$filterList[] = "PS.Person_Secname like :Person_Secname";
			$queryParams['Person_Secname'] = $data['Person_Secname'] . '%';
		}

		if ( !empty($data['Person_Birthday']) ) {
			$filterList[] = "PS.Person_Birthday = :Person_Birthday";
			$queryParams['Person_Birthday'] = $data['Person_Birthday'];
		}

		if ( !empty($data['EvnDirection_setDate']) ) {
			$filterList[] = "ED.EvnDirection_setDate = :EvnDirection_setDate";
			$queryParams['EvnDirection_setDate'] = $data['EvnDirection_setDate'];
		}

		if ( !empty($data['Lpu_sid']) ) {
			$filterList[] = "ED.Lpu_sid = :Lpu_sid";
			$queryParams['Lpu_sid'] = $data['Lpu_sid'];
		}

		if ( !empty($data['LpuSectionProfile_did']) ) {
			$filterList[] = "ED.LpuSectionProfile_id = :LpuSectionProfile_did";
			$queryParams['LpuSectionProfile_did'] = $data['LpuSectionProfile_did'];
		}

		if ( !empty($data['EvnDirection_failDate']) ) {
			$filterList[] = "cast(ED.EvnDirection_failDT as date) = :EvnDirection_failDate";
			$queryParams['EvnDirection_failDate'] = $data['EvnDirection_failDate'];
		}

		if ( !empty($data['DirType_id']) ) {
			$filterList[] = "ED.DirType_id = :DirType_id";
			$queryParams['DirType_id'] = $data['DirType_id'];
		}

		if ( !empty($data['DirFailType_id']) ) {
			$filterList[] = "ED.DirFailType_id = :DirFailType_id";
			$queryParams['DirFailType_id'] = $data['DirFailType_id'];
		}

		if ( !empty($data['Lpu_did']) ) {
			$filterList[] = "ISNULL(ED.Lpu_did, EPS.Lpu_id) = :Lpu_did";
			$queryParams['Lpu_did'] = $data['Lpu_did'];
		}

		if ( !empty($data['EvnPS_setDate']) ) {
			$filterList[] = "EPS.EvnPS_setDate = :EvnPS_setDate";
			$queryParams['EvnPS_setDate'] = $data['EvnPS_setDate'];
		}

		if ( !empty($data['PrehospType_id']) ) {
			$filterList[] = "EPS.PrehospType_id = :PrehospType_id";
			$queryParams['PrehospType_id'] = $data['PrehospType_id'];
		}

		if ( !empty($data['PrehospArrive_id']) ) {
			$filterList[] = "EPS.PrehospArrive_id = :PrehospArrive_id";
			$queryParams['PrehospArrive_id'] = $data['PrehospArrive_id'];
		}

		if ( !empty($data['LeaveType_id']) ) {
			$filterList[] = "EPS.LeaveType_id = :LeaveType_id";
			$queryParams['LeaveType_id'] = $data['LeaveType_id'];
		}

		if ( !empty($data['EvnPS_disDate']) ) {
			$filterList[] = "EPS.EvnPS_disDate = :EvnPS_disDate";
			$queryParams['EvnPS_disDate'] = $data['EvnPS_disDate'];
		}

		if ( !empty($data['OrgSmo_id']) || !empty($data['EvnDirection_setDate']) || !empty($data['EvnPS_setDate']) ) {
			$polisFilters = array('t1.Person_id = ED.Person_id');
			if ( !empty($data['OrgSmo_id']) ) {
				$polisFilters[] = "(t2.OrgSmo_id = :OrgSmo_id or t2.OrgSmo_id = rid.OrgSmo_id)";
				$rid_where .= " and OS1.OrgSMO_id = :OrgSmo_id";
				$queryParams['OrgSmo_id'] = $data['OrgSmo_id'];
			}

			if ( !empty($data['EvnDirection_setDate']) && !empty($data['EvnPS_setDate']) ) {
				$polisFilters[] = "(
					(cast(t2.Polis_begDate as date) <= :EvnDirection_setDate and ISNULL(cast(t2.Polis_endDate as date), '2030-01-01') >= :EvnDirection_setDate)
					or (cast(t2.Polis_begDate as date) <= :EvnPS_setDate and ISNULL(cast(t2.Polis_endDate as date), '2030-01-01') >= :EvnPS_setDate)
				)";

				$queryParams['EvnDirection_setDate'] = $data['EvnDirection_setDate'];
				$queryParams['EvnPS_setDate'] = $data['EvnPS_setDate'];
			}
			else if ( !empty($data['EvnDirection_setDate']) ) {
				$polisFilters[] = "cast(t2.Polis_begDate as date) <= :EvnDirection_setDate";
				$polisFilters[] = "ISNULL(cast(t2.Polis_endDate as date), '2030-01-01') >= :EvnDirection_setDate";

				$queryParams['EvnDirection_setDate'] = $data['EvnDirection_setDate'];
			}
			else if ( !empty($data['EvnPS_setDate']) ) {
				$polisFilters[] = "cast(t2.Polis_begDate as date) <= :EvnPS_setDate";
				$polisFilters[] = "ISNULL(cast(t2.Polis_endDate as date), '2030-01-01') >= :EvnPS_setDate";

				$queryParams['EvnPS_setDate'] = $data['EvnPS_setDate'];
			}
			else {
				$polisFilters[] = "cast(t2.Polis_begDate as date) <= ED.EvnDirection_setDT";
				$polisFilters[] = "ISNULL(cast(t2.Polis_endDate as date), '2030-01-01') >= ED.EvnDirection_setDT";
			}

			$filterList[] = "
				exists (
					select top 1 t1.Polis_id
					from v_PersonPolis t1 with (nolock)
						inner join v_Polis t2 with (nolock) on t2.Polis_id = t1.Polis_id
					where " . implode(' and ', $polisFilters) . "
				)
			";
		}

		if ( !empty($data['Over20DaysInQueue']) ) {
			$filterList[] = "ED.EvnQueue_id is not null"; // Признак постановки в очередь
			$filterList[] = "DATEDIFF(DAY, ED.EvnDirection_setDT, ISNULL(EPS.EvnPS_setDT, dbo.tzGetDate())) > 20"; // Более 20 дней
			$selectFields[] = 'datediff(day, ED.EvnDirection_setDate, COALESCE(TTS.TimetableStac_setDate,ES.EvnSection_setDT, EPS.EvnPS_OutcomeDT, EPS.EvnPS_setDT, getdate())) as WaitingDays';
		}
		else {
			$selectFields[] = 'case when TTS.TimetableStac_setDate is not null then datediff(day, ED.EvnDirection_setDate, TTS.TimetableStac_setDate) else datediff(day, ED.EvnDirection_setDate, getdate())  end as WaitingDays';
		}

		$query = "
			select
				-- select
				 ED.EvnDirection_id
				,ED.Person_id
				,ED.PersonEvn_id
				,ED.Server_id
				,DLpuSection.LpuSection_Name as LpuSection
				,RTRIM(LTRIM(PS.Person_Surname + ' ' + ISNULL(PS.Person_Firname, '') + ' ' + ISNULL(PS.Person_Secname, ''))) as Person_Fio
				,convert(varchar(10), PS.Person_Birthday, 104) as Person_Birthday
				,DT.DirType_Name
				,case when LD.Lpu_f003mcod is not null then LD.Lpu_f003mcod + '. ' else '' end + RTRIM(LD.Lpu_Nick) as LpuDir_Name
				,convert(varchar(10), ED.EvnDirection_setDT, 104) as EvnDirection_setDate
				,convert(varchar(10), ED.EvnDirection_failDT, 104) as EvnDirection_failDate
				,DFT.DirFailType_Name
				,case when LF.Lpu_f003mcod is not null then LF.Lpu_f003mcod + '. ' else '' end + RTRIM(LF.Lpu_Nick) as LpuFail_Name
				,LSDP.LpuSectionProfile_Name
				,MP.Person_Fio as MedPersonal_Fio
				,case when L.Lpu_f003mcod is not null then L.Lpu_f003mcod + '. ' else '' end + RTRIM(L.Lpu_Nick) as Lpu_Name
				,case
					when TTS.TimetableStac_setDate is not null then convert(varchar(10), TTS.TimetableStac_setDate, 104)
					when ED.EvnQueue_id is not null then 'очередь'
					else ''
				 end as TimetableStac_setDate
				" . (count($selectFields) > 0 ? "," . implode(", ", $selectFields) : ""). "
				,PT.PrehospType_Name
				,PA.PrehospArrive_Name
				,case when EPS.PrehospWaifRefuseCause_id is not null then convert(varchar(10), EPS.EvnPS_OutcomeDT, 104) end as EvnPS_OutcomeDate
				,PWRC.PrehospWaifRefuseCause_Name
				,case
					when ES.EvnSection_setDT is not null then convert(varchar(10), ES.EvnSection_setDT, 104)
					when EPS.PrehospWaifRefuseCause_id is null then convert(varchar(10), ISNULL(EPS.EvnPS_OutcomeDT, EPS.EvnPS_setDT), 104)
				 end as EvnPS_setDate
				,case
					when ES.EvnSection_setDT is not null then convert(varchar(5), ES.EvnSection_setDT, 108)
					when EPS.PrehospWaifRefuseCause_id is null then convert(varchar(5), ISNULL(EPS.EvnPS_OutcomeDT, EPS.EvnPS_setDT), 108)
				 end as EvnPS_setTime
				,EPS.EvnPS_NumCard
				,D.Diag_Code + ' ' + D.Diag_Name as Diag_Name
				,case when EPS.PrehospWaifRefuseCause_id is null then convert(varchar(10), EPS.EvnPS_disDT, 104) end as EvnPS_disDate
				-- end select
			from 
				-- from
				v_EvnDirection ED with (NOLOCK)
				left join v_PersonState PS with (NOLOCK) on ED.Person_id = PS.Person_id
				left join v_Lpu LD with (nolock) on LD.Lpu_id = ED.Lpu_sid
				left join v_DirFailType DFT with (nolock) on DFT.DirFailType_id = ED.DirFailType_id
				left join v_pmUser PUF with (nolock) on PUF.pmUser_id = ED.pmUser_failID
				left join v_Lpu LF with (nolock) on LF.Lpu_id = PUF.Lpu_id
				left join v_Lpu L with (nolock) on L.Lpu_id = ED.Lpu_did
				left join v_LpuSectionProfile LSDP with (nolock) on LSDP.LpuSectionProfile_id = ED.LpuSectionProfile_id
				left join v_LpuSection DLpuSection with(nolock) on DLpuSection.LpuSection_id = ED.LpuSection_did
				outer apply (
					select top 1 Person_Fio
					from v_MedPersonal with (NOLOCK)
					where MedPersonal_id = ED.MedPersonal_id
						and Lpu_id = ED.Lpu_id
				) MP
				outer apply (
					select top 1 *
					from v_EvnPS with (nolock)
					where EvnDirection_id = ED.EvnDirection_id
				) EPS
				outer apply (
					select top 1
						OS2.OrgSmo_id
					from
						v_OrgSMO OS1 with (nolock)
						left join v_Org O1 with (nolock) on OS1.Org_id = O1.Org_id
						left join v_Org O2 with (nolock) on O1.Org_rid = O2.Org_id
						left join v_OrgSMO OS2 with (nolock) on OS2.Org_id = O2.Org_id
					where
						{$rid_where}
				) rid
				left join v_EvnSection ES with (nolock) on ES.EvnSection_pid = EPS.EvnPS_id and ES.EvnSection_Index = 0 -- для Самары - 1
				left join v_DirType DT with (NOLOCK) on DT.DirType_id = ED.DirType_id
				left join v_PrehospType PT with (NOLOCK) on PT.PrehospType_id = EPS.PrehospType_id
				left join v_PrehospArrive PA with (NOLOCK) on PA.PrehospArrive_id = EPS.PrehospArrive_id
				left join v_PrehospWaifRefuseCause PWRC with (NOLOCK) on PWRC.PrehospWaifRefuseCause_id = EPS.PrehospWaifRefuseCause_id
				left join v_Diag D with (NOLOCK) on D.Diag_id = EPS.Diag_pid
				left join v_TimetableStac_lite TTS with (NOLOCK) on TTS.EvnDirection_id = ED.EvnDirection_id
				-- end from
			where
				-- where
				" . implode(' and ', $filterList) . "
				and LD.Lpu_id not in (100, 101)
				and L.Lpu_id not in (100, 101)
				-- end where
			order by 
				-- order by
				ED.EvnDirection_setDate DESC
				-- end order by
		";
	
		//echo getDebugSQL($query, $queryParams);
		$result = $this->db->query(getLimitSQLPH($query, $data['start'], $data['limit']), $queryParams);
		$result_count = $this->db->query(getCountSQLPH($query), $queryParams);
		
		if ( is_object($result_count) ) {
			$cnt_arr = $result_count->result('array');
			$count = $cnt_arr[0]['cnt'];
			unset($cnt_arr);
		}
		else {
			$count = 0;
		}

		if ( is_object($result) ) {
			$response = array();
			$response['data'] = $result->result('array');
			$response['totalCount'] = $count;
			return $response;
		}
		else {
			return false;
		}
	}

	/**
	 * Получение списка отмененных направлений для отображнения в сигнальной информации ЭМК
	 */
	function getDirFailListViewData($data) {

		$queryParams = array('Person_id' => $data['Person_id']);

		$select = "
			ED.EvnDirection_id,
			MPSet.Person_Fio as Person_setFio,
			MPFail.Person_Fio as Person_failFio,
			convert(varchar(10), ED.EvnDirection_setDT, 104) as EvnDirection_setDate,
			convert(varchar(20), coalesce(ED.EvnDirection_statusDate, ED.EvnDirection_failDT, EQ.EvnQueue_failDT), 120) as sortDT,
			convert(varchar(10), coalesce(ED.EvnDirection_statusDate, ED.EvnDirection_failDT, EQ.EvnQueue_failDT), 104) as EvnDirection_failDate,
			coalesce(EvnStatusCause.EvnStatusCause_Name, DFT.DirFailType_Name, QFC.QueueFailCause_Name) as FailCause_Name
		";

		if (!empty($data['onlyCount'])) {
			$select = "
				count(ED.EvnDirection_id) as DirFailListCount
			";
		}

		$query = "
			select
				{$select}
				from
				v_EvnDirection_all ED with(nolock)
				-- очередь
				-- left join v_EvnQueue EQ with (nolock) on EQ.EvnDirection_id = ED.EvnDirection_id
				-- Оптимизация
				outer apply(
					select top 1 QueueFailCause_id, pmUser_failID, EvnQueue_failDT
					from EvnQueue with (nolock)
					inner join Evn (nolock) Evn on Evn.Evn_id = EvnQueue.EvnQueue_id and Evn.Evn_deleted = 1
					where EvnDirection_id = ED.EvnDirection_id
				) as EQ
				outer apply(
					select top 1 ESH.EvnStatus_id, ESH.EvnStatusCause_id, ESH.pmUser_insID/*, ESH.EvnStatusHistory_Cause*/
					from EvnStatusHistory ESH with(nolock)
					where ESH.Evn_id = ED.EvnDirection_id
						and ESH.EvnStatus_id = ED.EvnStatus_id
					order by ESH.EvnStatusHistory_begDate desc
				) ESH
				left join EvnStatus with(nolock) on EvnStatus.EvnStatus_id = ED.EvnStatus_id
				left join EvnStatusCause with(nolock) on EvnStatusCause.EvnStatusCause_id = ESH.EvnStatusCause_id
				left join v_DirFailType DFT with(nolock) on DFT.DirFailType_id = ED.DirFailType_id
				left join v_QueueFailCause QFC with(nolock) on QFC.QueueFailCause_id = EQ.QueueFailCause_id
				left join v_pmUserCache userIns with(nolock) on userIns.PMUser_id = ED.pmUser_insID
				outer apply(
					select top 1 MP.MedPersonal_id, MP.Person_Fio
					from v_MedPersonal MP with(nolock)
					where MP.MedPersonal_id = userIns.MedPersonal_id and MP.Lpu_id = userIns.Lpu_id /*and MP.WorkType_id = 1*/
				) MPSet
				left join v_pmUserCache userFail with(nolock) on userFail.PMUser_id = coalesce(ED.pmUser_failID,EQ.pmUser_failID,ESH.pmUser_insID)
				outer apply(
					select top 1 MP.MedPersonal_id, MP.Person_Fio
					from v_MedPersonal MP with(nolock)
					where MP.MedPersonal_id = userFail.MedPersonal_id and MP.Lpu_id = userFail.Lpu_id /*and MP.WorkType_id = 1*/
				) MPFail
			where
				ED.Person_id = :Person_id
				and (
					ED.EvnStatus_id in (12,13)
					OR ED.EvnDirection_failDT is not null 
					OR EQ.EvnQueue_failDT is not null 
				)
				/* Направления, отмененные с причиной “Неверный ввод”, не отображать */
				and isnull(ED.DirFailType_id, 0) != 14
				and isnull(EQ.QueueFailCause_id, 0) != 5
				and isnull(ESH.EvnStatusCause_id, 0) != 4
			/*order by
				coalesce(ED.EvnDirection_statusDate, ED.EvnDirection_failDT, EQ.EvnQueue_failDT) desc*/
		";
		/*
		outer apply(
			select top 1 MP.MedPersonal_id, MP.Person_Fio
			from v_MedPersonal MP with(nolock)
			where MP.MedPersonal_id = ED.MedPersonal_id and MP.Lpu_id = ED.Lpu_sid and MP.WorkType_id = 1
		) MPSet
		*/

		//echo getDebugSQL($query, $queryParams); exit;
		$result = $this->db->query($query, $queryParams);

		if ( !is_object($result) )
		{
			return false;
			//return swFilterResponse::filterNotViewDiag($result->result('array'), $data);
		}

		$response = $result->result('array');
		if (empty($data['onlyCount'])) {

			/**
			 * Для сортировки массива
			 */
			function cmp($a, $b) {
				if ( $a['sortDT'] == $b['sortDT'] ) {
					return 0;
				}

				return ($a['sortDT'] > $b['sortDT']) ? -1 : 1;
			}

			usort($response, "cmp");

			if ( empty($data['showAll']) || $data['showAll'] != 2 ) {
				$response = array_slice($response, 0, 10);
			}
		}

		return $response;
	}

	/**
	 * Отправка сообщений об отмене направления
	 */
	function sendCancelEvnDirectionMessage($data) {
		$record = $this->getCancelEvnDirectionMessageData($data);

		if (!$record) {
			return false;
		}

		$title = 'Отмена направления';

		$text = "Отменено направление пациента {$record['Person_Fio']} {$record['Person_BirthDay']} года рождения"
			." в {$record['directionToPlace']} по профилю {$record['LpuSectionProfile_dName']}"
			." от {$record['EvnDirection_setDT']} по причине \"{$record['DirFailType_Name']}\"."
			." Дата отклонениея {$record['EvnDirection_failDT']}."
			." Отклонил(а) направление {$record['PostMed_fName']} {$record['Person_fFio']}.";

		$recipients[] = array();

		if (!empty($record['MedPersonal_did']) && $data['pmUser_id'] == $record['pmUser_insID']) {
			// Отменяет направивший пользователь. Отправляем сообщение назначенному врачу, если он указан.
			$recipients[] = array(
				'MedPersonal_rid' => $record['MedPersonal_did'],
				'Lpu_rid' => $record['Lpu_did']
			);
		} else
		if (!empty($record['MedPersonal_did']) && !empty($data['MedPersonal_id']) && $data['MedPersonal_id'] == $record['MedPersonal_did']) {
			// Отменяет назначенный врач. Отправляем сообщение направевшему пользователю.
			$recipients[]['User_rid'] = $record['pmUser_insID'];
		}
		else {
			// Отменяет другой пользователь. Отправляем сообщение назначающему пользователю и назначенному врачу
			$recipients[]['User_rid'] = $record['pmUser_insID'];
			if (!empty($record['MedPersonal_did'])) {
				$recipients[] = array(
					'MedPersonal_rid' => $record['MedPersonal_did'],
					'Lpu_rid' => $record['Lpu_did']
				);
			}
		}

		$this->load->model('Messages_model', 'Messages_model');

		if (!empty($recipients)) {
			foreach($recipients as $recipient) {
				$noticeData = array(
					'autotype' => 5,
					'pmUser_id' => $data['pmUser_id'],
					'type' => 6,
					'title' => $title,
					'text' => $text
				);
				$noticeData = array_merge($noticeData, $recipient);
				$noticeResponse = $this->Messages_model->autoMessage($noticeData);
			}
		} else {
			return false;
		}
		return true;
	}

	/**
	 * Получение данных для формирования сообщения об отмене направления
	 */
	function getCancelEvnDirectionMessageData($data) {
		$params = array(
			'EvnDirection_id' => $data['EvnDirection_id'],
			'MedStaffFact_id' => isset($data['session']['CurMedStaffFact_id'])&&!empty($data['session']['CurMedStaffFact_id'])?$data['session']['CurMedStaffFact_id']:null
		);

		$join = "";
		$selectPersonData = "convert(varchar(10), PS.Person_Birthday, 104) as Person_BirthDay,
				RTRIM(PS.Person_SurName + ' ' + PS.Person_FirName + ' ' + isnull(PS.Person_SecName, '')) as Person_Fio,";
		if (allowPersonEncrypHIV($data['session'])) {
			$join .= " left join v_PersonEncrypHIV peh with(nolock) on peh.Person_id = ED.Person_id";
			$selectPersonData = "case when peh.PersonEncrypHIV_Encryp is null then convert(varchar(10), PS.Person_Birthday, 104) end as Person_BirthDay,
				case when peh.PersonEncrypHIV_Encryp is null then RTRIM(PS.Person_SurName + ' ' + PS.Person_FirName + ' ' + isnull(PS.Person_SecName, '')) else peh.PersonEncrypHIV_Encryp end as Person_Fio,";
		}
		
		$query = "
			select top 1
				ED.EvnDirection_id,
				ED.pmUser_insID,
				ED.pmUser_failID,
				CONVERT(varchar(10), ED.EvnDirection_setDT, 104) as EvnDirection_setDT,
				CONVERT(varchar(10), ED.EvnDirection_failDT, 104) as EvnDirection_failDT,
				{$selectPersonData}
				DFT.DirFailType_Name,
				ED.MedPersonal_did,
				ED.Lpu_did,
				case
					when LS.LpuSection_Name is not null then L1.Lpu_Name +', '+ LS.LpuSection_Name
					when LU.LpuUnit_Name is not null then L1.Lpu_Name +', '+ LU.LpuUnit_Name
					else L1.Lpu_Name
				end  as directionToPlace,
				LSP.LpuSectionProfile_Name as LpuSectionProfile_dName,
				MPFail.MedPersonal_id as MedPersonal_fid,
				MPFail.Person_Fio as Person_fFio,
				MPFail.PostMed_Name as PostMed_fName
			from
				v_EvnDirection_all ED with(nolock)
				left join v_PersonState PS with(nolock) on PS.PersonEvn_id = ED.PersonEvn_id
				left join v_LpuSection LS with(nolock) on LS.LpuSection_id = ED.LpuSection_did
				left join v_LpuUnit LU with(nolock) on LU.LpuUnit_id = ED.LpuUnit_did
				left join v_Lpu L1 with(nolock) on L1.Lpu_id = LS.Lpu_id
				left join v_LpuSectionProfile LSP with(nolock) on LSP.LpuSectionProfile_id = ED.LpuSectionProfile_id
				left join v_DirFailType DFT with(nolock) on DFT.DirFailType_id = ED.DirFailType_id
				outer apply(
					select top 1 t.MedPersonal_id,t.Person_Fio, PM.PostMed_Name
					from v_MedStaffFact t with (nolock)
						left join v_PostMed PM with(nolock) on PM.PostMed_id = t.Post_id
					where t.MedStaffFact_id = :MedStaffFact_id
				) MPFail
				{$join}
			where
				ED.EvnDirection_id = :EvnDirection_id
		";
		//echo getDebugSQL($query, $params);
		$result = $this->db->query($query, $params);

		if (is_object($result)) {
			$response = $result->result('array');
			if (isset($response[0])) {
				return $response[0];
			}
		}
		return false;
	}

	/**
	 * Получение идентификатора направления выписанного на бирку
	 */
	function getEvnDirectionIdByRecord($data) {
		// Без этого не обойтись, на групповой бирке направление в связанной таблице
		$object = $data['object'];
		if($data['object']=='TimetableGraf' && !empty($data['TimetableGrafRecList_id']))
			$object = 'TimetableGrafRecList';

		$res = $this->db->query(" 
				select
					EvnDirection_id
				from {$object} with (nolock)
				where
					{$object}_id = :Id
			",
			array(
				'Id' => $data[$object.'_id']
			)
		);
		if (is_object($res)) {
			$res = $res->result('array');
			if (isset($res[0])) {
				return $res[0]['EvnDirection_id'];
			}
		}
		return null;
	}

	/**
	 * Проверка может ли направление быть отменено
	 */
	function checkEvnDirectionCanBeCancelled($data) {
		$isEMDEnabled = $this->config->item('EMD_ENABLE');
		if (!empty($isEMDEnabled)) {
			$this->load->model('EMD_model');
			$checkResult = $this->EMD_model->getEMDDocumentListByEvn(
				array(
					'Evn_id' => $data['EvnDirection_id'],
					'EvnClass_SysNick' => 'EvnDirection'
				)
			);
			if (!empty($checkResult)) {
				return "Отмена направления невозможна, т.к. оно зарегистрировано в РЭМД";
			}
		}
		// Если направление связано с заявкой, то отменить можно направление, заявка по которому имеет статус "Новая"
		$query = "
			select top 1
				1 as code
			from
				v_EvnLabRequest elr (nolock)
				inner join v_EvnStatus es (nolock) on es.EvnStatus_id = elr.EvnStatus_id
			where
				elr.EvnDirection_id = :EvnDirection_id and es.EvnStatus_SysNick != 'New'

			union all

			select top 1
				1 as code
			from
				v_EvnFuncRequest efr (nolock)
				inner join v_EvnStatus es (nolock) on es.EvnStatus_id = efr.EvnStatus_id
			where
				efr.EvnFuncRequest_pid = :EvnDirection_id and es.EvnStatus_SysNick != 'FuncNew'

			union all
			select top 1 3 as code
			FROM v_EvnUslugaTelemed EUT with (nolock)
			inner join v_EvnDirection_all ED with (nolock) on EUT.EvnDirection_id = ED.EvnDirection_id and ED.DirFailType_id is null
			where ED.EvnDirection_id = :EvnDirection_id
			union all
			select top 1
				2 as code
			from
				v_EvnDirection_all ed (nolock)
			where
				ed.EvnDirection_id = :EvnDirection_id and ed.EvnStatus_id not in (10,17,12) -- #75518 Костыль: Если направление уже отменено, то скажем что его можно отменить еще раз
		";

		$resp = $this->queryResult($query, $data);
		if (!is_array($resp)) {
			return 'Ошибка при проверке статуса направления';
		}
			if (count($resp) > 0) {
				if (1 == $resp[0]['code']) {
					return 'Нельзя отменить направление, т.к. заявка по направлению имеет статус не "Новая"';
				} else if (3 == $resp[0]['code']){
					return 'Нельзя отменить направление, т.к. выполнена консультационая услуга';
				} else {
					return 'Можно отменить направление, если направление имеет статус "Записано на бирку" или "В очереди"';
				}
			}

		return '';
	}

	/**
	 * Отмена направления. Метод для API.
	 */
	function cancelEvnDirectionFromAPI($data) {
		$this->load->model('EvnDirectionAll_model');

		$data['DirFailType_id'] = 14; // Неверный ввод

		// 1. получение данных направления
		$resp_ed = $this->queryResult("
			select top 1
				ed.EvnDirection_id,
				ed.TimetableMedService_id,
				ed.TimetableResource_id,
				ed.TimetableGraf_id,
				ed.EvnQueue_id,
				ed.MedStaffFact_id
			from
				v_EvnDirection_all ed (nolock)
			where
				ed.EvnDirection_id = :EvnDirection_id
		", array(
			'EvnDirection_id' => $data['EvnDirection_id']
		));
		if (empty($resp_ed[0]['EvnDirection_id'])) {
			throw new Exception('Ошибка получения данных по направлению', 500);
		}

		// получение данных назначений
		$resp_ep = $this->queryResult("
			select
				ep.EvnPrescr_id,
				ep.EvnCourse_id,
				ep.PrescriptionType_id
			from
				v_EvnPrescrDirection epd (nolock)
				inner join v_EvnPrescr ep (nolock) on ep.EvnPrescr_id = epd.EvnPrescr_id
			where
				EvnDirection_id = :EvnDirection_id
		", array(
			'EvnDirection_id' => $data['EvnDirection_id']
		));
		if (!is_array($resp_ep)) {
			throw new Exception('Ошибка получения данных по направлению', 500);
		}

		$this->load->model('Timetable_model', 'Timetable_model');
		$data['cancelType'] = 'cancel';

		if (!empty($resp_ed[0]['TimetableGraf_id'])) {
			// освобождение бирки службы
			$data['object'] = 'TimetableGraf';
			$data['TimetableGraf_id'] = $resp_ed[0]['TimetableGraf_id'];
			$tmp = $this->Timetable_model->Clear($data);
			if (!$tmp['success']) {
				throw new Exception($tmp['Error_Msg'], 500);
			}

			// Отмена направления происходит при освобождении бирки
			return $tmp;
		} else if (!empty($resp_ed[0]['TimetableMedService_id'])) {
			// освобождение бирки службы
			$this->load->model('TimetableGraf_model', 'TimetableGraf_model');
			$data['object'] = 'TimetableMedService';
			$data['TimetableMedService_id'] = $resp_ed[0]['TimetableMedService_id'];
			$tmp = $this->Timetable_model->Clear($data);
			if (!$tmp['success']) {
				throw new Exception($tmp['Error_Msg'], 500);
			}

			// Отмена направления происходит при освобождении бирки
			return $tmp;
		} else if (!empty($resp_ed[0]['TimetableResource_id'])) {
			// освобождение бирки ресурса
			$this->load->model('TimetableGraf_model', 'TimetableGraf_model');
			$data['object'] = 'TimetableResource';
			$data['TimetableResource_id'] = $resp_ed[0]['TimetableResource_id'];
			$tmp = $this->Timetable_model->Clear($data);
			if (!$tmp['success']) {
				throw new Exception($tmp['Error_Msg'], 500);
			}

			// Отмена направления происходит при освобождении бирки
			return $tmp;
		} if (!empty($resp_ed[0]['EvnQueue_id'])) {
			// удаление из очереди
			$this->load->model('Queue_model', 'MPQueue_model');
			$data['EvnQueue_id'] = $resp_ed[0]['EvnQueue_id'];
			$tmp = $this->MPQueue_model->deleteQueueRecord($data);
			if ( !$tmp ) {
				throw new Exception('Ошибка при удалении из очереди', 500);
			}
			if(isset($tmp[0]['Error_Msg'])) {
				throw new Exception($tmp[0]['Error_Msg'], 500);
			}
		}

		$resp = $this->execCommonSP('p_EvnDirection_cancel', array(
			'EvnDirection_id' => $data['EvnDirection_id'],
			'DirFailType_id' => $data['DirFailType_id'],
			'EvnStatusCause_id' => !empty($data['EvnStatusCause_id']) ? $data['EvnStatusCause_id'] : 14,
			'EvnComment_Comment' => !empty($data['EvnComment_Comment']) ? $data['EvnComment_Comment'] : 14,
			'pmUser_id' => $data['pmUser_id'],
			'MedStaffFact_fid' => $data['session']['CurMedStaffFact_id'],
			'Lpu_cid' => $data['session']['lpu_id']
		), 'array_assoc');

		if (empty($resp)) {
			throw new Exception('Ошибка запроса к БД', 500);
		}

		if (isset($resp['Error_Msg'])) {
			throw new Exception($resp['Error_Msg'], 500);
		}

		$this->load->model('ApprovalList_model');
		$this->ApprovalList_model->deleteApprovalList(array(
			'ApprovalList_ObjectName' => 'EvnDirection',
			'ApprovalList_ObjectId' => $data['EvnDirection_id']
		));

		// если было назначение - отменяем и его
		$this->load->model('EvnPrescr_model');
		foreach($resp_ep as $one_ep) {
			if (in_array($one_ep['PrescriptionType_id'], array(6)) && !empty($one_ep['EvnCourse_id'])) {
				//Отмена курса назначения
				$params = array(
					'EvnCourse_id' => $one_ep['EvnCourse_id'],
					'PrescriptionType_id' => $one_ep['PrescriptionType_id'],
					'MedStaffFact_cid' => $resp_ed[0]['MedStaffFact_id'],
					'pmUser_id' => $data['pmUser_id'],
				);
				$resp = $this->EvnPrescr_model->cancelEvnCourse($params);
				if (!$this->isSuccessful($resp)) {
					throw new Exception($resp[0]['Error_Msg'], 500);
				}
			} else {
				//Отмена назначения
				$params = array(
					'EvnPrescr_id' => $one_ep['EvnPrescr_id'],
					'PrescriptionType_id' => $one_ep['PrescriptionType_id'],
					'MedStaffFact_cid' => $resp_ed[0]['MedStaffFact_id'],
					'pmUser_id' => $data['pmUser_id'],
				);
				$resp = $this->EvnPrescr_model->cancelEvnPrescr($params, true);
				if (!$this->isSuccessful($resp)) {
					throw new Exception($resp[0]['Error_Msg'], 500);
				}
			}
		}

		return $resp;
	}

	/**
	 * Отмена направления
	 */
	function cancelEvnDirection($data) {

		$queryParams = array(
			'EvnDirection_id' => $data['EvnDirection_id'],
			'DirFailType_id' => !empty($data['DirFailType_id'])?$data['DirFailType_id']:null,
			'EvnStatusCause_id' => !empty($data['EvnStatusCause_id'])?$data['EvnStatusCause_id']:null,
			'EvnComment_Comment' => !empty($data['EvnComment_Comment'])?$data['EvnComment_Comment']:null,
			'pmUser_id' => $data['pmUser_id'],
			'Lpu_cid' => !empty($data['Lpu_id']) ? $data['Lpu_id'] : null,
			'MedStaffFact_fid' => !empty($data['session']['CurMedStaffFact_id']) ? $data['session']['CurMedStaffFact_id'] : null
		);

		$cancelProc = "p_EvnDirection_cancel";
		if (!empty($data['cancelType']) && $data['cancelType'] == 'decline') {
			$cancelProc = "p_EvnDirection_decline";
		}

		$resp = $this->execCommonSP($cancelProc, $queryParams);
		if (!$this->isSuccessful($resp)) {
			return $resp;
		}

		$this->load->model('ApprovalList_model');
		$this->ApprovalList_model->deleteApprovalList(array(
			'ApprovalList_ObjectName' => 'EvnDirection',
			'ApprovalList_ObjectId' => $data['EvnDirection_id']
		));

		return $resp;
	}
	
	/**
	 * Decline or Cancel direction
	*/
	function execDelDirection($cancelProc, $queryParams) {
		return $this->execCommonSP($cancelProc, $queryParams);
	}
	/**
	 * Отмена направления по записи
	 */
	function cancelEvnDirectionbyRecord($data) {
		if (empty($data['EvnDirection_id'])) {
		$data['EvnDirection_id'] = $this->getEvnDirectionIdByRecord($data);
		}
		if ( !empty($data['EvnDirection_id']) ) {
			$error = $this->checkEvnDirectionCanBeCancelled($data);
			if (!empty($error)) {
				return $error;
			}

			$this->load->model('ElectronicTalon_model');
			$resp = $this->ElectronicTalon_model->cancelElectronicTalonByEvnDirection(array(
				'EvnDirection_id' => $data['EvnDirection_id'],
				'pmUser_id' => $data['pmUser_id']
			));
			if (!$this->isSuccessful($resp)) {
				return $resp['Error_Msg'];
			}

			$data['Lpu_cid'] = $data['session']['lpu_id'];
			$resp = $this->cancelEvnDirection($data);

			if ($resp) {
				if (count($resp) > 0 && !empty($resp[0]['Error_Msg'])) {
					// возвращаем ошибку
					return $resp[0]['Error_Msg'];
				}
				// Если после отмены направление не было удалено, посылаем сообщение
				if ($this->getEvnDirectionIdByRecord($data)) {
					$this->sendCancelEvnDirectionMessage($data);
				}
			}
			
			if(in_array(getRegionNick(), ['msk','ufa','vologda'])) {
				$this->load->model("NoticeModeSettings_model", "dbnotify");
				// вторым параметром передаем статус направления
				$this->dbnotify->prepareNotify($data, 12);
			}

		}
		
		return '';
	}
	
	/**
	 * Запись направления из очереди на бирку
	 *
	 * @param $data
	 * @return array|bool
	 */
	function applyEvnDirectionFromQueue($data) {

		if ( empty($data['EvnQueue_id']) ) {
			throw new Exception('Параметр EvnQueue_id обязателен для правильной работы хранимой процедуры p_EvnDirection_recordFromQueue', 500);
		}
		if ( empty($data['DirType_id']) ) {
			//throw new Exception('Поле Тип направления обязательно для заполнения', 400);
			// Если направление без типа записывается из очереди, то принудительно присваивать ему тип.
			switch (true) {
				case ( !empty($data['TimetableGraf_id']) ):
					// однозначно на поликлинический прием
					$data['DirType_id'] = 16;
					break;
				case ( !empty($data['TimetableStac_id']) ):
					// однозначно в стационар на госпитализацию плановую, т.к. на экстренную нельзя записать из очереди
					$data['DirType_id'] = 1;
					break;
				default:
					$query = "
						select top 1 ED.EvnDirection_id, dir.DirType_id
						from v_EvnQueue EQ with (nolock)
							inner join v_EvnDirection_all ED with (nolock) on EQ.EvnDirection_id = ED.EvnDirection_id
							left join v_TimeTableGraf_lite TTG with (nolock) on TTG.EvnDirection_id = ED.EvnDirection_id and TTG.MedStaffFact_id is not null
							left join v_TimetableStac_lite TTS with (nolock) on ED.EvnDirection_id = TTS.EvnDirection_id
							left join v_MedStaffFact MSFD with (nolock) on MSFD.MedStaffFact_id = TTG.MedStaffFact_id AND MSFD.Lpu_id = ED.Lpu_did
							left join v_MedService MS with (nolock) on ED.Medservice_id = MS.Medservice_id
							left join v_MedServiceType mst with (nolock) on mst.MedServiceType_id = ms.MedServiceType_id
							left join v_LpuSection LSD with (nolock) on coalesce(MSFD.LpuSection_id, TTS.LpuSection_id, MS.LpuSection_id, ED.LpuSection_did) = LSD.LpuSection_id AND LSD.Lpu_id = ED.Lpu_did
							left join v_LpuUnit LUD with (nolock) on isnull(LSD.LpuUnit_id, ED.LpuUnit_did) = LUD.LpuUnit_id AND LUD.Lpu_id = ED.Lpu_did
							left join v_DirType dir with (nolock) on ISNULL(ED.DirType_id, case 
								when TTG.MedStaffFact_id is not null OR LUD.LpuUnitType_SysNick in ('polka','ccenter','traumcenter','fap') then 16 /* Если направление без типа к врачу поликлиники/стоматологии, то принудительно присаивать ему тип на поликлинический прием. */
								when mst.MedServiceType_SysNick in ('lab','pzm','reglab','func') then 10 /* То же самое в лабораторную службу (включая пз, регслужбу лаборатории) или диагностическую службу - присваивать тип На исследование */
								when mst.MedServiceType_SysNick in ('konsult') then 11 /* В службу типа Консультативный прием - В консультационный кабинет */
								when mst.MedServiceType_SysNick in ('prock') then 15 /* В службу типа Процедурный кабинет - В процедурный кабинет */
							else null end) = dir.DirType_id
						where
							EQ.EvnQueue_id = :EvnQueue_id
					";
					$queryParams = array('EvnQueue_id' => $data['EvnQueue_id']);
					//echo getDebugSQL($query, $queryParams);exit();
					$result = $this->db->query($query, $queryParams);
					if (is_object($result)) {
						$res = $result->result('array');
						if (empty($res)) {
							throw new Exception('Запрос для определения типа направления ничего не вернул', 500);
						}
						$data['DirType_id'] = $res[0]['DirType_id'];
						$data['EvnDirection_id'] = $res[0]['EvnDirection_id'];
					} else {
						throw new Exception('Не удалось выполнить запрос для определения типа направления', 500);
					}
			}
			if ( empty($data['DirType_id']) ) {
				throw new Exception('Не удалось автоматически определить тип направления', 500);
			}
			if ( empty($data['EvnDirection_id']) ) {
				$this->load->model("Queue_model", "qmodel");
				$res = $this->qmodel->getDirectionId($data['EvnQueue_id']);
				if ( $res !== false ) {
					$data['EvnDirection_id'] = $res;
				} else {
					throw new Exception('Невозможно автоматически определить идентификатор направления', 500);
				}
			}
			$res = $this->swUpdate('EvnDirection', array(
				'DirType_id' =>$data['DirType_id'],
				'EvnDirection_id' => $data['EvnDirection_id'],
			), false);
			if (empty($res) || false == is_array($res)) {
				throw new Exception('Ошибка запроса к БД', 500);
			}
			if (false == empty($res[0]['Error_Msg'])) {
				throw new Exception($res[0]['Error_Msg'], 500);
			}
		}


		if (empty($data['Lpu_did']) && !empty($data['Lpu_id'])) {
			$data['Lpu_did'] = $data['Lpu_id'];
		}

		// сгенерим код бронирования
		$data['EvnDirection_TalonCode'] = $this->makeEvnDirectionTalonCode($data);

		$queryParams = array(
			'EvnQueue_id' => $data['EvnQueue_id'],
			'EvnDirection_id' => array(
				'out' => true,
				'value' => ( empty($data['EvnDirection_id']) ? NULL : $data['EvnDirection_id'] ),
				'type' => 'bigint'
			),
			'EvnDirection_TalonCode' => $data['EvnDirection_TalonCode'],
			'TimetableGraf_id' => empty($data['TimetableGraf_id']) ? NULL : $data['TimetableGraf_id'],
			'TimetableStac_id' => empty($data['TimetableStac_id']) ? NULL : $data['TimetableStac_id'],
			'TimetableResource_id' => empty($data['TimetableResource_id']) ? NULL : $data['TimetableResource_id'],
			'TimetableMedService_id' => empty($data['TimetableMedService_id']) ? NULL : $data['TimetableMedService_id'],
			'ARMType_id' => empty($data['ARMType_id']) ? NULL : $data['ARMType_id'],
			'pmUser_id' => $data['pmUser_id']
		);

		$EvnQueueData = $this->getFirstRowFromQuery("
				select top 1 RecMethodType_id, pmUser_insID from v_EvnQueue (nolock) where EvnQueue_id = :EvnQueue_id
		", $data);

		// если тип записи в очередь "Портал", изменяем статус на "Ожидает подтверждения"
		if (!empty($EvnQueueData['RecMethodType_id']) && $EvnQueueData['RecMethodType_id'] == 1) {
			$queryParams['EvnQueueStatus_id'] = 2;
		}

		$response = $this->execCommonSP("p_EvnDirection_recordFromQueue", $queryParams);
		if (!$response || (isset($response[0]) && !empty($response[0]['Error_Msg']))) {
			return $response;
		}


		// если тип записи в очередь "Портал", шлем уведомления пользователю
		if (
			isset($EvnQueueData['RecMethodType_id'])
			&& $EvnQueueData['RecMethodType_id'] == 1
			&& $data['object'] == 'TimetableGraf'
		) {

			$this->load->model("Queue_model");
			$this->Queue_model->sendNotify(array(
				'notify_type' => 'timetableOffer',
				'pmUser_id' => $EvnQueueData['pmUser_insID'],
				'TimetableGraf_id' => $data['TimetableGraf_id']
			));
		}

		// если направление было связано с EvnLabRequest, нужно перекешировать EvnLabRequest_prmTime - время записи
		$query = "
			update
				elr with (rowlock)
			set
				EvnLabRequest_prmTime = ttms.TimetableMedService_begTime
			from
				EvnLabRequest elr
				left join v_TimetableMedService_lite ttms (nolock) on ttms.EvnDirection_id = elr.EvnDirection_id
			where
				elr.EvnDirection_id = :EvnDirection_id
		";
		$this->db->query($query, array(
			'EvnDirection_id' => $data['EvnDirection_id']
		));

		//Если записывается на бирку по назначению, то привязать переданное назначение к направление,
		//предыдущие назначения по направлению отменить
		if (!empty($data['EvnPrescr_id'])) {
			$this->load->model('EvnPrescr_model', 'EvnPrescr_model');

			$resp = $this->EvnPrescr_model->directEvnPrescr($data);
			if (!$this->isSuccessful($resp)) {
				throw new Exception($resp[0]['Error_Msg'], 500);
			}

			$EvnPrescrList = $this->queryResult("
				select
					EP.EvnPrescr_id,
					EP.PrescriptionType_id,
					EPD.EvnPrescrDirection_id
				from v_EvnPrescrDirection EPD with(nolock)
				inner join v_EvnPrescr EP with(nolock) on EP.EvnPrescr_id = EPD.EvnPrescr_id
				where EPD.EvnDirection_id = :EvnDirection_id and EP.EvnPrescr_id <> :EvnPrescr_id
			", $data);
			foreach($EvnPrescrList as $EvnPrescr) {
				$resp = $this->EvnPrescr_model->deleteEvnPrescrDirection(array(
					'EvnPrescrDirection_id' => $EvnPrescr['EvnPrescrDirection_id']
				));
				if (!$this->isSuccessful($resp)) {
					throw new Exception($resp[0]['Error_Msg'], 500);
				}

				$params = array(
					'EvnPrescr_id' => $EvnPrescr['EvnPrescr_id'],
					'PrescriptionType_id' => $EvnPrescr['PrescriptionType_id'],
					'MedStaffFact_cid' => $data['From_MedStaffFact_id'],
					'MedPersonal_cid' => $data['MedPersonal_id'],
					'LpuSection_cid' => $data['LpuSection_id'],
					'pmUser_id' => $data['pmUser_id'],
				);
				$resp = $this->EvnPrescr_model->cancelEvnPrescr($params, true);
				if (!$this->isSuccessful($resp)) {
					throw new Exception($resp[0]['Error_Msg'], 500);
				}
			}
		}

		$enableSmsTalonCode = $this->config->item('enableSmsTalonCode');
		if (!empty($data['EvnDirection_TalonCode']) && !empty($enableSmsTalonCode)) {
			$response[0]['EvnDirection_TalonCode'] = $data['EvnDirection_TalonCode'];

			$this->load->model('ElectronicTalon_model');
			$this->ElectronicTalon_model->sendElectronicTalonMessage($data);
		}


		return $response;
	}
	
	/**
	 * Получение списка записей для формы поиска записей на прием 
	 */
	function loadTimetableRecords($data) {
		$filter = '(1=1)';
		$TimetableGrafFilter = '(1=1)';
		$TimetableStacFilter = '(1=1)';
		$EvnDirectionFilter = '(1=1)';
		$EvnQueueFilter = '(1=1)';

		//https://redmine.swan.perm.ru/issues/35651 - переделал работу с фильтрами (и выбор из полей в запросе)
		if ( !empty($data['RecordDate_from']) ) {
			$TimetableGrafFilter .= " and cast(ED.EvnDirection_insDT as date) >= :RecordDate_from";
			$TimetableStacFilter .= " and cast(Timetable.TimeTableStac_setDate as date) >= :RecordDate_from";
			$EvnDirectionFilter .= " and cast(ED.EvnDirection_insDT as date) >= :RecordDate_from";
			$EvnQueueFilter .= " and cast(ED.EvnDirection_insDT as date) >= :RecordDate_from";
		}

		if ( !empty($data['RecordDate_to']) ) {
			$TimetableGrafFilter .= " and cast(ED.EvnDirection_insDT as date) <= :RecordDate_to";
			$TimetableStacFilter .= " and cast(Timetable.TimeTableStac_setDate as date) <= :RecordDate_to";
			$EvnDirectionFilter .= " and cast(ED.EvnDirection_insDT as date) <= :RecordDate_to";
			$EvnQueueFilter .= " and cast(ED.EvnDirection_insDT as date) <= :RecordDate_to";
		}

		if ( !empty($data['VizitDate_from']) ) {
			$TimetableGrafFilter .= " and cast(Timetable.TimeTableGraf_begTime as date) >= :VizitDate_from";
			$TimetableStacFilter .= " and cast(TimeTableStac_setDate as date) >= :VizitDate_from";
			$EvnDirectionFilter .= " and cast(ISNULL(ttg.TimeTableGraf_begTime, ED.EvnDirection_desDT) as date) >= :VizitDate_from";
			$EvnQueueFilter .= " and 1=0";
		}

		if ( !empty($data['VizitDate_to']) ) {
			$TimetableGrafFilter .= " and cast(Timetable.TimeTableGraf_begTime as date) <= :VizitDate_to";
			$TimetableStacFilter .= " and cast(TimeTableStac_setDate as date) <= :VizitDate_to";
			$EvnDirectionFilter .= " and cast(ISNULL(ttg.TimeTableGraf_begTime, ED.EvnDirection_desDT) as date) <= :VizitDate_to";
			$EvnQueueFilter .= " and 1=0";
		}

		if ( !empty($data['RecLpu_id']) ) {
			$filter .= " and DLpu.Lpu_id = :RecLpu_id";
		}

		if ( !empty($data['onlyCallCenterUsers']) && $data['onlyCallCenterUsers'] === 2 ) {
			$filter .= " and (PATINDEX('%CallCenterAdmin%', pm.pmUser_groups) > 0 or PATINDEX('%OperatorCallCenter%', pm.pmUser_groups) > 0)";
		}

		$join = "";
		$isSearchByEncryp = false;
		$select_person_data = "PS.Person_id,
				PS.Person_FirName,
				PS.Person_SurName,
				PS.Person_SecName,
				PS.Person_Phone,
				PS.Person_BirthDay,
				PS.Person_isDead,
				PS.server_id,
				PS.PersonEvn_id,
				A.Address_Address,
				L.Lpu_Nick,/* ЛПУ прикрепления */
				null as PersonEncrypHIV_Encryp";
		if (isEncrypHIVRegion($this->regionNick)) {
			if (allowPersonEncrypHIV($data['session'])) {
				$join .= " left join v_PersonEncrypHIV peh with(nolock) on peh.Person_id = PS.Person_id";
				$isSearchByEncryp = isSearchByPersonEncrypHIV($data['Person_SurName']);
				$select_person_data = "PS.Person_id,
				case when peh.PersonEncrypHIV_Encryp is null then PS.Person_SurName else peh.PersonEncrypHIV_Encryp end as Person_SurName,
				case when peh.PersonEncrypHIV_Encryp is null then PS.Person_FirName end as Person_FirName,
				case when peh.PersonEncrypHIV_Encryp is null then PS.Person_SecName end as Person_SecName,
				case when peh.PersonEncrypHIV_Encryp is null then PS.Person_Phone end as Person_Phone,
				case when peh.PersonEncrypHIV_Encryp is null then PS.Person_BirthDay end as Person_BirthDay,
				case when peh.PersonEncrypHIV_Encryp is null then PS.Person_isDead end as Person_isDead,
				case when peh.PersonEncrypHIV_Encryp is null then PS.server_id end as server_id,
				case when peh.PersonEncrypHIV_Encryp is null then PS.PersonEvn_id end as PersonEvn_id,
				case when peh.PersonEncrypHIV_Encryp is null then A.Address_Address end as Address_Address,
				case when peh.PersonEncrypHIV_Encryp is null then L.Lpu_Nick end as Lpu_Nick,/* ЛПУ прикрепления */
				peh.PersonEncrypHIV_Encryp";
			} else {
				//Не отображать анонимных шифрованных пациентов
				$filter .= " and not exists(
					select top 1 peh.PersonEncrypHIV_Encryp
					from v_PersonEncrypHIV peh with(nolock)
					inner join v_EncrypHIVTerr eht with(nolock) on eht.EncrypHIVTerr_id = peh.EncrypHIVTerr_id
						and isnull(eht.EncrypHIVTerr_Code,0) = 20
					where peh.Person_id = PSt.Person_id
				)";
			}
		}

		if (!empty($data['Person_SurName'])) {
			if (allowPersonEncrypHIV($data['session']) && $isSearchByEncryp) {
				$filter .= " and PSt.PersonEncrypHIV_Encryp LIKE :Person_SurName";
			} else {
				$filter .= " and PSt.Person_SurName LIKE :Person_SurName";
				$data['Person_SurName'] = trim(preg_replace('/ё/iu', 'е', $data['Person_SurName'])) . "%";
			}
		}

		if ( !empty($data['Person_FirName']) ) {
			$filter .= " and PSt.Person_FirName like(:Person_FirName+'%') ";
		}

		if ( !empty($data['Person_SecName']) ) {
			$filter .= " and PSt.Person_SecName like(:Person_SecName+'%') ";
		}

		if ( !empty($data['Person_Birthday']) ) {
			$filter .= " and PSt.Person_BirthDay = :Person_Birthday ";
		}

		if ( !empty($data['pmUser_id']) ) {
			$filter .= " and pm.PMUser_id = :pmUser_id ";
		}

		if ( !empty($data['EvnDirection_Num']) ) {
			$filter .= " and ED.EvnDirection_Num = :EvnDirection_Num ";
		}

		$query = "
			--variables
			DECLARE @date date = dbo.tzGetDate();
			DECLARE @time datetime = dbo.tzGetDate();
			--end variables
			
			select --Запись на бирку в поликлиннику TimetableGrafFilter
				Timetable.Person_id
				,'Запись' as RecType_Name -- тип записи
				,'TimetableGraf_' + cast(Timetable.TimetableGraf_id as varchar) as keyNote -- идентификатор ссобытия
				,isnull(PSt.Person_SurName, '') + ' ' + isnull(PSt.Person_FirName, '') + ' ' + isnull(PSt.Person_SecName, '')  as Person_FIO -- ФИО пациента
				,PSt.Address_Address -- Адрес проживания пациента
			    ,convert(varchar(10), PSt.Person_BirthDay, 104) as Person_BirthDay
				,isnull(MSF.Person_Fio, '  ') + '/' + isnull(MS.MedService_Name, '  ') + '/' + isnull(LS.LpuSection_Name, '  ') as MedUnit_Name -- Врач / Служба / Отделение
				,DLpu.Lpu_Nick -- ЛПУ куда направили
				,LS.LpuSection_Name -- Отделение куда направили
				,LS.LpuSectionProfile_Name -- профиль
				,isnull(LB.LpuBuilding_Name, 'Поликлинника') as LpuBuilding_Name -- Подразделение, куда направили
				,convert(varchar(10), ED.EvnDirection_insDT, 104) +' '+ convert(varchar(5), ED.EvnDirection_insDT, 108) as RecordDate -- дата записи
				,convert(varchar(10), Timetable.TimeTableGraf_begTime, 104) +' '+ convert(varchar(5), Timetable.TimeTableGraf_begTime, 108) as VizitDate -- дата посещения
				,pm.PMUser_id as pmUser_id -- пользователь, который добавил запись
				,pm.PMUser_Name + ' / ' + pm.PMUser_Login as PMUser_Name
				,Timetable.TimeTableGraf_begTime as RecDate
				,Timetable.TimeTableGraf_factTime as VizDate
				,PSt.Lpu_Nick as AttachLpu_Name
				,PSt.Person_isDead
				,PSt.Server_id
		        ,PSt.PersonEvn_id
		        ,null as EvnDirection_id
		        ,null as DirType_Code
				,PSt.Person_FirName
				,PSt.Person_SurName
				,PSt.Person_SecName
		        ,PSt.Person_Phone
		        ,ED.EvnDirection_Num
			from
				v_TimeTableGraf_lite Timetable with (NOLOCK)
				outer apply (
					select top 1
						{$select_person_data}
					from
						v_PersonState PS with (NOLOCK)
						left join v_Address A with (nolock) on A.Address_id = isnull(PAddress_id, UAddress_id)
						left join v_Lpu L with (nolock) on PS.Lpu_id = L.Lpu_id
						{$join}
					where
						Timetable.Person_id = PS.Person_id
				) PSt
				left join v_MedStaffFact MSF with (NOLOCK) on Timetable.MedStaffFact_id = MSF.MedStaffFact_id
				left join v_LpuSection LS with (nolock) on MSF.LpuSection_id = LS.LpuSection_id
				left join v_LpuUnit LU with (nolock) on LS.LpuUnit_id = LU.LpuUnit_id
				left join v_LpuBuilding LB with (nolock) on LU.LpuBuilding_id = LB.LpuBuilding_id
				left join v_EvnDirection_all ED with (nolock) on Timetable.EvnDirection_id = ED.EvnDirection_id
				left join v_Lpu DLpu with (nolock) on DLpu.Lpu_id = isnull(MSF.Lpu_id,ED.Lpu_did)
				left join v_MedService MS with (nolock) on MS.MedService_id = ED.MedService_id
				left join v_pmUserCache pm with (nolock) on pm.PMUser_id=isnull(ED.pmUser_insID,Timetable.pmUser_updID)
			where
				Timetable.Person_id is not null
				and ED.EvnQueue_id is null
				and cast(Timetable.TimeTableGraf_begTime as DATE) >= @date
				and {$filter}
				and {$TimetableGrafFilter}
				and (PSt.Person_isDead = 1 or PSt.Person_isDead is null)
				and ED.EvnDirection_IsAuto = 2

			union all

			select --Запись на бирку в поликлиннику TimetableGrafFilter
				Timetable.Person_id
				,'Запись' as RecType_Name -- тип записи
				,'TimetableGraf_' + cast(Timetable.TimetableGraf_id as varchar) as keyNote -- идентификатор ссобытия
				,isnull(PSt.Person_SurName, '') + ' ' + isnull(PSt.Person_FirName, '') + ' ' + isnull(PSt.Person_SecName, '')  as Person_FIO -- ФИО пациента
				,PSt.Address_Address -- Адрес проживания пациента
			    ,convert(varchar(10), PSt.Person_BirthDay, 104) as Person_BirthDay
				,isnull(MSF.Person_Fio, '  ') + '/' + isnull(MS.MedService_Name, '  ') + '/' + isnull(LS.LpuSection_Name, '  ') as MedUnit_Name -- Врач / Служба / Отделение
				,DLpu.Lpu_Nick -- ЛПУ куда направили
				,LS.LpuSection_Name -- Отделение куда направили
				,LS.LpuSectionProfile_Name -- профиль
				,isnull(LB.LpuBuilding_Name, 'Поликлинника') as LpuBuilding_Name -- Подразделение, куда направили
				,convert(varchar(10), ED.EvnDirection_insDT, 104) +' '+ convert(varchar(5), ED.EvnDirection_insDT, 108) as RecordDate -- дата записи
				,convert(varchar(10), Timetable.TimeTableGraf_begTime, 104) +' '+ convert(varchar(5), Timetable.TimeTableGraf_begTime, 108) as VizitDate -- дата посещения
				,pm.PMUser_id as pmUser_id -- пользователь, который добавил запись
				,pm.PMUser_Name + ' / ' + pm.PMUser_Login as PMUser_Name
				,Timetable.TimeTableGraf_begTime as RecDate
				,Timetable.TimeTableGraf_factTime as VizDate
				,PSt.Lpu_Nick as AttachLpu_Name
				,PSt.Person_isDead
				,PSt.Server_id
		        ,PSt.PersonEvn_id
		        ,null as EvnDirection_id
		        ,null as DirType_Code
				,PSt.Person_FirName
				,PSt.Person_SurName
				,PSt.Person_SecName
		        ,PSt.Person_Phone
		        ,ED.EvnDirection_Num
			from
				v_TimeTableGraf_lite Timetable with (NOLOCK)
				outer apply (
					select top 1
						isnull(slot.Person_id, '' ) as Person_id,
						isnull(slot.Slot_FirName, isnull(PS.Person_FirName, '' )) as Person_FirName,
						isnull(slot.Slot_SurName, isnull(PS.Person_SurName, '' )) as Person_SurName,
						isnull(slot.Slot_SecName, isnull(PS.Person_SecName, '' )) as Person_SecName,
						isnull(PS.Person_Phone, '' ) as Person_Phone,
						isnull(PS.Person_BirthDay, '' ) as Person_BirthDay,
						isnull(PS.Person_isDead, '' ) as Person_isDead,
						isnull(PS.server_id, '' ) as server_id,
						isnull(PS.PersonEvn_id, '' ) as PersonEvn_id,
						isnull(A.Address_Address, '' ) as Address_Address,
						isnull(L.Lpu_Nick, '' ) as Lpu_Nick,
						null as PersonEncrypHIV_Encryp
					from
						fer.v_slot slot with (NOLOCK)
						left join v_PersonState PS with (NOLOCK) on PS.Person_id = slot.Person_id
						left join v_Address A with (nolock) on A.Address_id = isnull(PAddress_id, UAddress_id)
						left join v_Lpu L with (nolock) on PS.Lpu_id = L.Lpu_id
						{$join}
					where
						Timetable.TimetableGraf_id = slot.TimetableGraf_id
				) PSt
				left join v_MedStaffFact MSF with (NOLOCK) on Timetable.MedStaffFact_id = MSF.MedStaffFact_id
				left join v_LpuSection LS with (nolock) on MSF.LpuSection_id = LS.LpuSection_id
				left join v_LpuUnit LU with (nolock) on LS.LpuUnit_id = LU.LpuUnit_id
				left join v_LpuBuilding LB with (nolock) on LU.LpuBuilding_id = LB.LpuBuilding_id
				left join v_EvnDirection_all ED with (nolock) on Timetable.EvnDirection_id = ED.EvnDirection_id
				left join v_Lpu DLpu with (nolock) on DLpu.Lpu_id = isnull(MSF.Lpu_id,ED.Lpu_did)
				left join v_MedService MS with (nolock) on MS.MedService_id = ED.MedService_id
				left join v_pmUserCache pm with (nolock) on pm.PMUser_id=isnull(ED.pmUser_insID,Timetable.pmUser_updID)
			where
				Timetable.Person_id is not null
				and ED.EvnQueue_id is null
				and cast(Timetable.TimeTableGraf_begTime as DATE) >= @date
				and {$filter}
				and {$TimetableGrafFilter}
				and (PSt.Person_isDead = 1 or PSt.Person_isDead is null)

			union all

			select -- запись на койку TimetableStacFilter
				Timetable.Person_id
				,'Запись на койку' as RecType_Name
				,'TimetableStac_'+ cast(Timetable.TimetableStac_id as varchar) as keyNote
				,isnull(PSt.Person_SurName, '') + ' ' + isnull(PSt.Person_FirName, '') + ' ' + isnull(PSt.Person_SecName, '')  as Person_FIO -- ФИО пациента
				,PSt.Address_Address -- Адрес проживания пациента
			    ,convert(varchar(10), PSt.Person_BirthDay, 104) as Person_BirthDay
				,'  ' + '/' + isnull(MS.MedService_Name, '  ') + '/' + isnull(LS.LpuSection_Name, '  ') as MedUnit_Name -- Врач / Служба / Отделение
				,DLpu.Lpu_Nick -- ЛПУ куда направили
				,LS.LpuSection_Name -- Отделение куда направили
				,LS.LpuSectionProfile_Name -- профиль
				,LB.LpuBuilding_Name -- Подразделение, куда направили
				,convert(varchar(10), Timetable.TimeTableStac_setDate, 104) +' '+ convert(varchar(5), Timetable.TimeTableStac_setDate, 108) as RecordDate -- дата записи
				,convert(varchar(10), Timetable.TimeTableStac_setDate, 104) +' '+ convert(varchar(5), Timetable.TimeTableStac_setDate, 108) as VizitDate -- дата посещения
				,pm.PMUser_id as pmUser_id
				,pm.PMUser_Name + ' / ' + pm.PMUser_Login as PMUser_Name
				,Timetable.TimeTableStac_setDate as RecDate
				,EvnPS.EvnPS_setDate as VizDate
				,PSt.Lpu_Nick as AttachLpu_Name
				,PSt.Person_isDead
				,PSt.server_id
		        ,PSt.PersonEvn_id
		        ,null as EvnDirection_id
		        ,null as DirType_Code
				,PSt.Person_FirName
				,PSt.Person_SurName
				,PSt.Person_SecName
		        ,PSt.Person_Phone
		        ,null as EvnDirection_Num
			from
				v_TimetableStac_lite Timetable with (NOLOCK)
				outer apply (
					select top 1
						{$select_person_data}
					from
						v_PersonState PS with (NOLOCK)
						left join v_Address A with (nolock) on A.Address_id = isnull(PAddress_id, UAddress_id)
						left join v_Lpu L with (nolock) on PS.Lpu_id = L.Lpu_id
						{$join}
					where
						Timetable.Person_id = PS.Person_id
				) PSt
				outer apply (
					select top 1
						EvnPS.EvnPS_setDate
					from
						v_EvnPS EvnPS with (nolock)
					where
						Timetable.Evn_id = EvnPS.EvnPS_id
				) EvnPS
				left join v_EvnDirection_all ED with (nolock) on ED.EvnDirection_id = Timetable.EvnDirection_id
				left join v_MedService MS with (nolock) on MS.MedService_id = ED.MedService_id
				left join v_LpuSection LS with (nolock) on Timetable.LpuSection_id = LS.LpuSection_id
				left join v_LpuUnit LU with (nolock) on LS.LpuUnit_id = LU.LpuUnit_id
				left join v_LpuBuilding LB with (nolock) on LU.LpuBuilding_id = LB.LpuBuilding_id
				left join v_Lpu DLpu with (nolock) on isnull(ED.Lpu_did, LS.Lpu_id )= DLpu.Lpu_id
				left join v_pmUserCache pm with (nolock) on pm.PMUser_id=Timetable.pmUser_insID
			where
				Timetable.Person_id is not null
				and ED.EvnQueue_id is null
				and cast(Timetable.TimetableStac_setDate as DATE) >= @date
				and {$filter}
				and {$TimetableStacFilter}
				and (PSt.Person_isDead = 1 or PSt.Person_isDead is null)
				and ED.EvnDirection_IsAuto = 2

            union all

			select -- по направлению EvnDirectionFilter
				ED.Person_id
				,'По направлению' as RecType_Name
				,'EvnDirection_'+ cast(ED.EvnDirection_id as varchar) as keyNote
				,isnull(PSt.Person_SurName, '') + ' ' + isnull(PSt.Person_FirName, '') + ' ' + isnull(PSt.Person_SecName, '')  as Person_FIO -- ФИО пациента
				,PSt.Address_Address -- Адрес проживания пациента
			    ,convert(varchar(10), PSt.Person_BirthDay, 104) as Person_BirthDay
				,isnull(MSF.Person_Fin, '  ') + '/' + isnull(MS.MedService_Name, '  ') + '/' + isnull(LS.LpuSection_Name, '  ') as MedUnit_Name -- Врач / Служба / Отделение
				,DLpu.Lpu_Nick -- ЛПУ куда направили
				,LS.LpuSection_Name -- Отделение куда направили
				,LS.LpuSectionProfile_Name -- профиль
				,LB.LpuBuilding_Name as LpuBuilding_Name -- Подразделение, куда направили
				,convert(varchar(10), ED.EvnDirection_insDT, 104) +' '+ convert(varchar(5), ED.EvnDirection_insDT, 108) as RecordDate
				,convert(varchar(10), ISNULL(ttg.TimeTableGraf_begTime, ED.EvnDirection_desDT), 104) +' '+ convert(varchar(5), ISNULL(ttg.TimeTableGraf_begTime, ED.EvnDirection_desDT), 108) as VizitDate -- дата посещения
				,pm.PMUser_id as pmUser_id
				,pm.PMUser_Name + ' / ' + pm.PMUser_Login as PMUser_Name
				,ED.EvnDirection_insDT as RecDate
				,ED.EvnDirection_desDT as VizDate
				,PSt.Lpu_Nick as AttachLpu_Name
				,PSt.Person_isDead
				,PSt.server_id
		        ,PSt.PersonEvn_id
		        ,ED.EvnDirection_id
		        ,DT.DirType_Code
				,PSt.Person_FirName
				,PSt.Person_SurName
				,PSt.Person_SecName
		        ,PSt.Person_Phone
		        ,ED.EvnDirection_Num
			from
			    v_EvnDirection_all ED with (NOLOCK)
				outer apply (
					select top 1
						{$select_person_data}
					from
						v_PersonState PS with (NOLOCK)
						left join v_Address A with (nolock) on A.Address_id = isnull(PAddress_id, UAddress_id)
						left join v_Lpu L with (nolock) on PS.Lpu_id = L.Lpu_id
						{$join}
					where
						ED.Person_id = PS.Person_id
				) PSt
				left join v_DirType DT with (nolock) on ED.DirType_id = DT.DirType_id
				left join v_Lpu DLpu with (nolock) on ED.Lpu_did = DLpu.Lpu_id
			    left join v_LpuUnit DLpuUnit with (nolock) on ED.LpuUnit_did = DLpuUnit.LpuUnit_id
				left join v_LpuBuilding LB with (nolock) on DLpuUnit.LpuBuilding_id = LB.LpuBuilding_id
			    left join v_MedService MS with (nolock) on ED.Medservice_id = MS.Medservice_id
			    left join v_TimeTableGraf_lite TTG with (nolock) on TTG.EvnDirection_id = ED.EvnDirection_id
			    left join v_TimetableStac_lite TTS with (nolock) on TTS.EvnDirection_id = ED.EvnDirection_id
			    left join v_MedStaffFact MSF with (nolock) on MSF.MedStaffFact_id = TTG.MedStaffFact_id
			    left join v_LpuSection LS with (nolock) on LS.LpuSection_id = coalesce(MS.LpuSection_id, MSF.LpuSection_id, TTS.LpuSection_id)
				left join v_pmUserCache pm with (nolock) on pm.PMUser_id=ED.pmUser_insID
			where
				ED.EvnQueue_id is null
				and ED.DirType_id is not null
				and (ED.EvnDirection_isAuto = 1 or ED.EvnDirection_isAuto is null)
				and {$filter}
				and {$EvnDirectionFilter}
				and (PSt.Person_isDead = 1 or PSt.Person_isDead is null)
			union all

			select -- Очередь EvnQueueFilter
				Timetable.Person_id
				,'Очередь' as RecType_Name
				,'EvnQueue_' + cast(Timetable.EvnQueue_id as varchar) as keyNote
				,isnull(PSt.Person_SurName, '') + ' ' + isnull(PSt.Person_FirName, '') + ' ' + isnull(PSt.Person_SecName, '')  as Person_FIO -- ФИО пациента
				,PSt.Address_Address -- Адрес проживания пациента
			    ,convert(varchar(10), PSt.Person_BirthDay, 104) as Person_BirthDay
				,'  ' + '/' + isnull(MS.MedService_Name, '  ') + '/' + isnull(LS.LpuSection_Name, '  ') as MedUnit_Name -- Врач / Служба / Отделение
				,DLpu.Lpu_Nick -- ЛПУ куда направили
				,LS.LpuSection_Name -- Отделение куда направили
				,LS.LpuSectionProfile_Name -- профиль
				,isnull(LB.LpuBuilding_Name, '') as LpuBuilding_Name -- Подразделение, куда направили
				,convert(varchar(10), ED.EvnDirection_insDT, 104) +' '+ convert(varchar(5), ED.EvnDirection_insDT, 108) as RecordDate
				,null as VizitDate -- дата посещения
				,pm.PMUser_id as pmUser_id
				,pm.PMUser_Name + ' / ' + pm.PMUser_Login as PMUser_Name
				,isnull(ED.EvnDirection_desDT,Timetable.EvnQueue_setDT) as RecDate
				,null as VizDate
				,PSt.Lpu_Nick as AttachLpu_Name
				,PSt.Person_isDead
				,PSt.server_id
		        ,PSt.PersonEvn_id
		        ,ED.EvnDirection_id
		        ,null as DirType_Code
				,PSt.Person_FirName
				,PSt.Person_SurName
				,PSt.Person_SecName
		        ,PSt.Person_Phone
		        ,ed.EvnDirection_Num
			from
				v_EvnQueue Timetable with (NOLOCK)
				outer apply (
					select top 1
						{$select_person_data}
					from
						v_PersonState PS with (NOLOCK)
						left join v_Address A with (nolock) on A.Address_id = isnull(PAddress_id, UAddress_id)
						left join v_Lpu L with (nolock) on PS.Lpu_id = L.Lpu_id
						{$join}
					where
						Timetable.Person_id = PS.Person_id
				) PSt
				left join v_EvnDirection_all ED with (nolock) on Timetable.EvnDirection_id = ED.EvnDirection_id
				left join v_pmUserCache pm with (nolock) on isnull(ED.pmUser_insID,Timetable.pmUser_updID)=pm.PMUser_id
				left join v_LpuSection LS with (nolock) on LS.LpuSection_id = ED.LpuSection_did
				left join v_LpuUnit LU with (nolock) on isnull(Timetable.LpuUnit_did, LS.LpuUnit_id) = LU.LpuUnit_id
				left join v_LpuBuilding LB with (nolock) on LU.LpuBuilding_id = LB.LpuBuilding_id
				left join v_LpuUnit DLpuUnit with (nolock) on coalesce(ED.LpuUnit_did,Timetable.LpuUnit_did,LS.LpuUnit_id) = DLpuUnit.LpuUnit_id
				left join v_Lpu DLpu with (nolock) on isnull(ED.Lpu_did,DLpuUnit.Lpu_id) = DLpu.Lpu_id
				left join v_MedService MS with (nolock) on MS.MedService_id = ED.MedService_id
			where
				Timetable.EvnQueue_recDT is null
				and Timetable.EvnQueue_failDT is null
				and {$filter}
				and {$EvnQueueFilter}
				and (PSt.Person_isDead = 1 or PSt.Person_isDead is null)
		";

		//echo getDebugSQL($query,$data);die;
		$result = $this->db->query($query,$data);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Получение данных по направлению
	 */
	function getEvnDirectionInfo($data) {
		return $this->queryResult("
			select
				ed.EvnDirection_id,
				dt.DirType_Code
			from
				v_EvnDirection_all ed (nolock)
				left join v_DirType dt (nolock) on dt.DirType_id = ed.DirType_id
			where
				ed.EvnDirection_id = :EvnDirection_id
		", array(
			'EvnDirection_id' => $data['EvnDirection_id']
		));
	}

	function getEvnDirection($data) {
		$queryParams = [
			'Lpu_id' => $data['Lpu_id'],
			'EvnPL_id' => $data['EvnPL_id']
		];
		$this->load->model('EvnVizitPL_model');

		$selectEvnDirectionData = "
			EPL.PrehospDirect_id,
			PD.PrehospDirect_Name,
			EPL.EvnDirection_Num,
			convert(varchar(10), EPL.EvnDirection_setDT, 104) as EvnDirection_setDate,
			case when 1 = isnull(ED.EvnDirection_IsAuto,1) 
				then coalesce(EPL.Org_did, LPUDID.Org_id, ED.Org_sid) 
				else isnull(EPL.Org_did, LPUDID.Org_id) end as Org_did,
			EPL.Lpu_did,
			coalesce(LPUDID.Lpu_Nick, O.Org_Name) as Lpu_Nick,
			EPL.LpuSection_did,
			LS.LpuSection_Name,
			EPL.MedStaffFact_did,
			MSF.Person_fio,
			EPL.Diag_did,
			D.Diag_Name,
			EPL.Diag_preid,
			EPL.EvnDirection_id,
			isnull(ED.EvnDirection_IsAuto,1) as EvnDirection_IsAuto,
			isnull(ED.EvnDirection_IsReceive,1) as EvnDirection_IsReceive,
			isnull(ED.Lpu_sid,ED.Lpu_id) as Lpu_fid,
		";

		$query = "
			select top 1
				case when EPL.Lpu_id = :Lpu_id then 'edit' else 'view' end as accessType,
				EPL.DirectClass_id,
				EPL.DirectType_id,
				EPL.Person_id,
				{$selectEvnDirectionData}
				EPL.EvnPL_id,
				convert(varchar(10), EPL.EvnPL_setDate, 104) as EvnPL_setDate,
				EPL.EvnPL_IsFinish,
				EPL.EvnPL_IsSurveyRefuse,
				EPL.EvnPL_IsUnlaw,
				EPL.EvnPL_IsUnport,
				RTRIM(ISNULL(EPL.EvnPL_NumCard, '')) as EvnPL_NumCard,
				case when EPL.EvnPL_IsCons = 2 then 1 else 0 end as EvnPL_IsCons,
				ROUND(EPL.EvnPL_UKL, 3) as EvnPL_UKL
			FROM
				v_EvnPL EPL with (nolock)
				left join v_Lpu LPUDID with (nolock) on EPL.Lpu_did = LPUDID.Lpu_id
				left join v_LpuSection LS with (nolock) on LS.LpuSection_id = EPL.LpuSection_did
				left join v_EvnDirection_all ED with (nolock) on ED.EvnDirection_id = EPL.EvnDirection_id
				left join v_PrehospDirect PD with (nolock) on PD.PrehospDirect_id = EPL.PrehospDirect_id
				left join v_MedStaffFact MSF with (nolock) on MSF.MedStaffFact_id = EPL.MedStaffFact_did
				left join v_Diag D with (nolock) on D.Diag_id = EPL.Diag_did
				left join v_Org O with (nolock) on O.Org_id = coalesce(EPL.Org_did, LPUDID.Org_id, ED.Org_sid) 
			WHERE (1 = 1) and EPL.Lpu_id = :Lpu_id and EPL.EvnPL_id = :EvnPL_id
		";
//		echo getDebugSQL($query, $queryParams);exit;
		$resp = $this->db->query($query, $queryParams);

		if ( is_object($resp) ) {
			return $resp->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Сохранение ссылки на случай
	 */
	function saveEvnDirectionPid($data) {
		$this->load->model('EvnDirectionAll_model');
		$this->EvnDirectionAll_model->setParams(array(
			'session' => $data['session']
		));
		$this->EvnDirectionAll_model->setAttributes(array(
			'EvnDirection_id' => $data['EvnDirection_id'],
			'EvnDirection_pid' => $data['EvnDirection_pid']
		));
		$resp = $this->EvnDirectionAll_model->_save();

		return $resp;
	}

	/**
	 * Получение данных о направлении
	 */
	function getEvnDirectionData($data) {
		$resp = $this->queryResult("
			select top 1
				ed.EvnDirection_id,
				ed.DirType_id,
				ed.EvnStatus_id,
				ttms.TimetableMedService_id,
				ttr.TimetableResource_id,
				eq.EvnQueue_id,
				'' as Error_Msg
			from
				v_EvnDirection_all ed (nolock)
				left join v_TimetableMedService_lite ttms (nolock) on ttms.EvnDirection_id = ed.EvnDirection_id
				left join v_TimetableResource_lite ttr (nolock) on ttr.EvnDirection_id = ed.EvnDirection_id
				left join EvnQueue eq (nolock) on eq.EvnDirection_id = ed.EvnDirection_id and eq.EvnQueue_recDT is null and eq.EvnQueue_failDT is null
			where
				ed.EvnDirection_id = :EvnDirection_id
		", array(
			'EvnDirection_id' => $data['EvnDirection_id']
		));

		if (!empty($resp[0]['EvnDirection_id'])) {
			return $resp[0];
		}

		return false;
	}

	/**
	 * Загрузка списка направлений для АПИ
	 */
	function loadEvnDirectionListForAPI($data) {
		$filter = "";
		$queryParams = array();

		if (!empty($data['Person_id'])) {
			$filter .= " and ed.Person_id = :Person_id";
			$queryParams['Person_id'] = $data['Person_id'];
		}

		if (!empty($data['Evn_pid'])) {
			$filter .= " and ed.EvnDirection_pid = :Evn_pid";
			$queryParams['Evn_pid'] = $data['Evn_pid'];
		}

		if (empty($filter)) {
			return array(); // без фильтров нельзя.
		}

		$resp = $this->queryResult("
			select
				ed.EvnDirection_id,
				ed.EvnDirection_id as Evn_id,
				ed.EvnDirection_pid as Evn_pid,
				ed.Person_id,
				ed.EvnDirection_Num,
				convert(varchar(10), ed.EvnDirection_setDate, 120) as EvnDirection_setDate,
				ed.DirType_id
			from
				v_EvnDirection_all ed (nolock)
			where
				(1=1)
				{$filter}
		", $queryParams);

		return $resp;
	}

	/**
	 * Загрузка направлений для АПИ
	 */
	function loadEvnDirectionForAPI($data) {
		$filter = "";
		$queryParams = array();
		if ($this->usePostgreLis) {
			//ограничение на запрос к postgre в 5 минут, чтобы направления успевали находиться
			$cfg = $this->config->item('SwServiceLis');
			$cfg['timeout'] = 300;
			$this->load->library('swServiceApi', $cfg, 'lis');
		}

		if (!empty($data['Person_id'])) {
			$filter .= " and ed.Person_id = :Person_id";
			$queryParams['Person_id'] = $data['Person_id'];
		}

		if (!empty($data['Evn_pid'])) {
			$filter .= " and ed.EvnDirection_pid = :Evn_pid";
			$queryParams['Evn_pid'] = $data['Evn_pid'];
		}

		if (!empty($data['EvnDirection_id'])) {
			$filter .= " and ed.EvnDirection_id = :EvnDirection_id";
			$queryParams['EvnDirection_id'] = $data['EvnDirection_id'];
		}

		if (!empty($data['EvnDirection_Num'])) {
			$filter .= " and ed.EvnDirection_Num = :EvnDirection_Num";
			$queryParams['EvnDirection_Num'] = $data['EvnDirection_Num'];
		}

		if (!empty($data['EvnDirection_setDate'])) {
			$filter .= " and ed.EvnDirection_setDate = :EvnDirection_setDate";
			$queryParams['EvnDirection_setDate'] = $data['EvnDirection_setDate'];
		}
		
		if (!empty($data['Lpu_id'])) {
			$filter .= " and ed.Lpu_did = :Lpu_did";
			$queryParams['Lpu_did'] = $data['Lpu_id'];
		}
		
		if (!empty($data['DirType_id'])) {
			$filter .= " and ed.DirType_id = :DirType_id";
			$queryParams['DirType_id'] = $data['DirType_id'];
		}

		if ((!empty($data['EvnDirection_beg']) || !empty($data['EvnDirection_end'])) && $data['DirType_id'] != 10) {
			return array('error_msg' => 'Запрос направлений за период доступен только для направлений с типом «На исследование». Укажите корректный тип направления или удалите период.');
		}

		if ((!empty($data['EvnDirection_beg']) || !empty($data['EvnDirection_end'])) && $data['DirType_id'] == 10) {
			$period = 'between :EvnDirection_beg and :EvnDirection_end';
			if(empty($data['EvnDirection_beg'])) $period = '<= :EvnDirection_end';
			if(empty($data['EvnDirection_end'])) $period = '>= :EvnDirection_beg';
			
			$filter .= " and EXISTS (
				SELECT EvnUsluga_id
				FROM v_EvnUsluga EU with (nolock)
				WHERE EU.EvnDirection_id = ed.EvnDirection_id
				AND EU.EvnUsluga_updDT {$period})
			";
			
			$queryParams['EvnDirection_beg'] = $data['EvnDirection_beg'];
			$queryParams['EvnDirection_end'] = $data['EvnDirection_end'];
		}

		if (empty($filter)) {
			return array('error_msg' => 'Не заданы фильтры'); // без фильтров нельзя.
		}

		$resp = $this->queryResult("
			SET NOCOUNT ON;
			
			select top 10001
			    ed.EvnDirection_id
			    INTO #tmpEvnDirection
			FROM
			v_evn AS Evn WITH (NOLOCK)
			INNER JOIN v_EvnDirection_all ed (nolock) ON ed.EvnDirection_id = Evn.Evn_id
			where (1=1)
				{$filter}
			
			SET NOCOUNT OFF;

            select 
				ed.EvnDirection_id,
				ed.EvnDirection_id as Evn_id,
				ed.EvnDirection_pid as Evn_pid,
				ed.Person_id,
				ed.EvnDirection_Num,
				ed.PayType_id,
				ed.DirType_id,
				convert(varchar(10), ed.EvnDirection_setDate, 120) as EvnDirection_setDate,
				convert(varchar(20), ed.EvnDirection_insDT, 20) as EvnDirection_insDT,
				ed.Diag_id,
				d.Diag_Code,
				ed.EvnDirection_Descr,
				ed.Lpu_sid,
				ed.LpuSection_id,
				ed.MedPersonal_id,
				ed.MedStaffFact_id,
				ed.MedPersonal_zid,
				ed.Lpu_did,
				ed.LpuUnit_did,
				ed.LpuSectionProfile_id,
				ed.MedPersonal_did,
				ed.TimeTableStac_id,
				ed.DirFailType_id,
				dft.DirFailType_Name,
				convert(varchar(10), ed.EvnDirection_failDT, 120) as EvnDirection_failDT,
				ed.pmUser_failID,
				ed.TimeTableGraf_id,
				ed.TimeTableStac_id,
				ed.TimeTableMedService_id,
				ed.TimeTableResource_id,
				ed.EvnQueue_id,
				epd.EvnPrescr_id,
				epd.PrescriptionType_id,
				case when epd.EvnPrescr_IsCito = 2 then 1 else 0 end as EvnPrescr_IsCito,
				epd.UslugaComplex_id,
				ed.Resource_id
			FROM 
                #tmpEvnDirection as tED 
                INNER JOIN v_EvnDirection_all AS ED (NOLOCK)ON ED.EvnDirection_id=tED.EvnDirection_id
                outer apply (
                    select top 1
                        epd.EvnPrescr_id,
                        ep.PrescriptionType_id,
                        ep.EvnPrescr_IsCito,
                        ISNULL(EPLD.UslugaComplex_id,EPFDU.UslugaComplex_id) as UslugaComplex_id
                    from
                        v_EvnPrescrDirection epd (nolock)
                        inner join v_EvnPrescr ep (nolock) on ep.EvnPrescr_id = epd.EvnPrescr_id
                        left join EvnPrescrLabDiag EPLD with(nolock) on EPLD.EvnPrescr_id = EPD.EvnPrescr_id
                        left join EvnPrescrFuncDiag EPFD with (nolock) on EPFD.EvnPrescr_id = EPD.EvnPrescr_id
                        left join EvnPrescrFuncDiagUsluga EPFDU with (nolock) on EPFDU.EvnPrescrFuncDiag_id = EPFD.EvnPrescrFuncDiag_id
                    where
                        epd.EvnDirection_id = ed.EvnDirection_id
                ) epd
                left join v_Diag d (nolock) on d.Diag_id = ed.Diag_id
                left join v_DirFailType dft (nolock) on dft.DirFailType_id = ed.DirFailType_id
		", $queryParams);
			
		//поиск направлений по postgres
		if ($this->usePostgreLis) {
			$directions = [];
			if (!empty($queryParams['EvnDirection_beg'])) {
				$queryParams['EvnDirection_beg'] = str_replace(' ', '%20', $queryParams['EvnDirection_beg']);
				$queryParams['EvnDirection_beg'] = str_replace(':', '%3A', $queryParams['EvnDirection_beg']);
			}
			if (!empty($queryParams['EvnDirection_end'])) {
				$queryParams['EvnDirection_end'] = str_replace(' ', '%20', $queryParams['EvnDirection_end']);
				$queryParams['EvnDirection_end'] = str_replace(':', '%3A', $queryParams['EvnDirection_end']);
			}

			$lisDirections = $this->lis->GET('EvnDirection/EvnDirectionsForAPI', $queryParams);
			if ($this->isSuccessful($lisDirections) && isset($lisDirections['data'])) {
				$lisDirections = $lisDirections['data'];
				foreach ($lisDirections as $lisDirection) {
					$dir = $this->queryResult("
						with mv as (
							select
								:EvnDirection_id as EvnDirection_id,
								:Evn_id as Evn_id,
								:Evn_pid as Evn_pid,
								:Person_id as Person_id,
								:EvnDirection_Num as EvnDirection_Num,
								:PayType_id as PayType_id,
								:DirType_id as DirType_id,
								:EvnDirection_setDate as EvnDirection_setDate,
								:EvnDirection_insDT as EvnDirection_insDT,
								:Diag_id as Diag_id,
								:EvnDirection_Descr as EvnDirection_Descr,
								:Lpu_sid as Lpu_sid,
								:LpuSection_id as LpuSection_id,
								:MedPersonal_id as MedPersonal_id,
								:MedStaffFact_id as MedStaffFact_id,
								:MedPersonal_zid as MedPersonal_zid,
								:Lpu_did as Lpu_did,
								:LpuUnit_did as LpuUnit_did,
								:LpuSectionProfile_id as LpuSectionProfile_id,
								:MedPersonal_did as MedPersonal_did,
								:DirFailType_id as DirFailType_id,
								:EvnDirection_failDT as EvnDirection_failDT,
								:pmUser_failID as pmUser_failID,
								:TimeTableGraf_id as TimeTableGraf_id,
								:TimeTableStac_id as TimeTableStac_id,
								:TimeTableMedService_id as TimeTableMedService_id,
								:TimeTableResource_id as TimeTableResource_id,
								:EvnQueue_id as EvnQueue_id,
								:Resource_id as Resource_id
						)
						
						select
							mv.*,
							:EvnQueue_id as EvnQueue_id,
							:Resource_id as Resource_id,
							ep.EvnPrescr_id,
							ep.PrescriptionType_id,
							case when ep.EvnPrescr_IsCito = 2
								then 1
								else 0
							end as EvnPrescr_IsCito,
							ISNULL(EPLD.UslugaComplex_id,EPFDU.UslugaComplex_id) as UslugaComplex_id,
							d.Diag_Code,
							dft.DirFailType_Name
						from
							mv mv
							left join v_EvnPrescrDirection epd (nolock) on epd.EvnDirection_id = mv.EvnDirection_id
							left join v_EvnPrescr ep (nolock) on ep.EvnPrescr_id = epd.EvnPrescr_id
							left join EvnPrescrLabDiag EPLD with(nolock) on EPLD.EvnPrescr_id = EPD.EvnPrescr_id
							left join EvnPrescrFuncDiag EPFD with (nolock) on EPFD.EvnPrescr_id = EPD.EvnPrescr_id
							left join EvnPrescrFuncDiagUsluga EPFDU with (nolock) on EPFDU.EvnPrescrFuncDiag_id = EPFD.EvnPrescrFuncDiag_id
							left join v_Diag d (nolock) on d.Diag_id = :Diag_id
							left join v_DirFailType dft (nolock) on dft.DirFailType_id = :DirFailType_id
					", $lisDirection);
					if (!empty($dir[0])) {
						$directions[] = $dir[0];
					}
				}
				$resp = array_merge($resp ,$directions);
			}
		}

		if(count($resp)>10000){
			return array('error_msg' => 'Найдено более 10000 записей, пожалуйста, уточните запрос');
		}
		//доп условия в случае, если записи для временной таблицы не найдены
		if(count($resp) == 0 || isset($resp[0]['Error_Code']) && $resp[0]['Error_Code'] == 666){
			return array('count' => 0);
		}
		foreach ($resp as $key => $value) {
			if($value['DirType_id'] == 10){
				//Список исследований
				$UslugaComplexParList = array();
				$result = $this->queryResult("
					SELECT distinct EU.UslugaComplex_id, EU.EvnUsluga_Result, EUP.EvnUslugaPar_id
					FROM v_EvnUsluga EU with (nolock)
						left join v_EvnUslugaPar EUP with (NOLOCK) on EUP.UslugaComplex_id = EU.UslugaComplex_id and EUP.EvnDirection_id = EU.EvnDirection_id
					WHERE EU.EvnDirection_id = :EvnDirection_id
				", array('EvnDirection_id' => $value['EvnDirection_id']));
				
				//дополнение списка исследований
				if ($this->usePostgreLis) {
					$res = $this->lis->GET('EvnDirection/UslugasDataForAPI', ['EvnDirection_id' => $value['EvnDirection_id']]);
					if ($this->isSuccessful($res) && !empty($res['data'])) {
						$result = array_merge($result, $res['data']);
					}
				}

				if(count($result) > 0){
					$i=0;
					foreach ($result as $k => $v) {
						if(empty($v['UslugaComplex_id'])) continue;
						
						$UslugaComplexParList[$i] = array();
						$UslugaComplexParList[$i]['Evn_id'] = $v['EvnUslugaPar_id'];
						$UslugaComplexParList[$i]['UslugaComplex_id'] = $v['UslugaComplex_id'];
						$UslugaComplexParList[$i]['UslugaList'] = array();

						// получаем данные о назначенном составе услуги
						$resp_elruc = $this->queryResult("
							select
								elruc.UslugaComplex_id
							from
								v_EvnLabRequestUslugaComplex elruc (nolock)
							where
								elruc.EvnUslugaPar_id = :EvnUslugaPar_id
						", array(
							'EvnUslugaPar_id' => $v['EvnUslugaPar_id']
						));

						if (empty($resp_elruc) && $this->usePostgreLis) {
							$res = $this->lis->GET('EvnLabRequest/LabRequestUslugaComplexData', [
								'EvnUslugaPar_id' => $v['EvnUslugaPar_id']
							]);
							if ($this->isSuccessful($res) && !empty($res['data'])) {
								$resp_elruc = $res['data'];
							}
						}

						foreach($resp_elruc as $one_elruc) {
							if (isset($one_elruc['UslugaComplex_id']) && !empty($one_elruc['UslugaComplex_id']))
								$UslugaComplexParList[$i]['UslugaList'][] = $one_elruc['UslugaComplex_id'];
						}
						$i++;
					}
				}
				$resp[$key]['UslugaComplexParList'] = json_encode($UslugaComplexParList);
			}
		}

		return $resp;
	}

	/**
	 *  Получение информации о стационарной бирке. Метод для API.
	 */
	function getTimeTableStacById($data) {
		$queryParams = array(
			'TimetableStac_id' => $data['TimeTableStac_id']
		);

		$resp = $this->queryResult("
			select
				tts.EvnDirection_id, 
				tts.Evn_id,
				tts.Evn_pid,
				tts.Person_id,
				tts.LpuSectionBedType_id,
				convert(varchar(10), tts.TimeTableStac_setDate, 120) as TimeTableStac_setDate,
				tts.TimeTableType_id,
				tts.LpuSection_id
			from
				v_TimetableStac_lite tts (nolock)
			where
				TimetableStac_id = :TimetableStac_id
		", $queryParams);

		return $resp;
	}

	/**
	 *  Получение информации о бирке. Метод для API.
	 */
	function getTimeTableMedServiceById($data) {
		$queryParams = array(
			'TimeTableMedService_id' => $data['TimeTableMedService_id']
		);

		$resp = $this->queryResult("
			select
				convert(varchar(19), ttms.TimeTableMedService_begTime, 120) as TimeTableMedService_begTime, 
				ttms.MedService_id,
				ttms.TimeTableType_id,
				ttms.TimetableMedService_Time,
				ttms.EvnDirection_id,
				ttms.Person_id,
				ttms.TimeTableMedService_IsDop,
				convert(varchar(19), ttms.TimeTableMedService_factTime, 120) as TimeTableMedService_factTime
			from
				v_TimetableMedService_lite ttms (nolock)
			where
				TimeTableMedService_id = :TimeTableMedService_id
		", $queryParams);

		return $resp;
	}

	/**
	 *  Получение информации о бирке. Метод для API.
	 */
	function getTimeTableResourceById($data) {
		$queryParams = array(
			'TimeTableResource_id' => $data['TimeTableResource_id']
		);

		$resp = $this->queryResult("
			select
				convert(varchar(19), ttr.TimeTableResource_begTime, 120) as TimeTableResource_begTime, 
				ttr.Resource_id,
				ttr.TimeTableType_id,
				ttr.TimetableResource_Time,
				ttr.EvnDirection_id,
				ttr.Person_id,
				ttr.TimetableResource_Day,
				ttr.TimeTableResource_IsDop
			from
				v_TimetableResource_lite ttr (nolock)
			where
				TimeTableResource_id = :TimeTableResource_id
		", $queryParams);

		return $resp;
	}

	/**
	 * Сохранение направления из АПИ
	 */
	function saveEvnDirectionFromAPI($data) {

		$body = "
		 	from v_PersonState ps (nolock)
			where ps.Person_id = :Person_id
		";

		if (!empty($data['fromMobile'])) {
			$body = "
				from v_Evn e (nolock)
				inner join v_PersonState ps (nolock) on ps.Person_id = e.Person_id
				where e.Evn_id = :Evn_pid
			";
		}

		$info = $this->getFirstRowFromQuery("
			select top 1
				ps.Person_id,
				ps.PersonEvn_id,
				ps.Server_id
			{$body}
		", $data);

		//echo '<pre>',print_r($data),'</pre>'; die();

		if (empty($info['Person_id'])) return false;
		$toQueue = false; $cnt = 0;

		$docInfo = $this->getFirstRowFromQuery("
			select top 1
				msf.LpuSection_id,
				msf.MedPersonal_id
			from v_MedStaffFact msf (nolock)
			where msf.MedStaffFact_id = :MedStaffFact_id
		", $data);

		if (empty($data['TimetableGraf_id'])
			&& empty($data['TimetableStac_id'])
			&& empty($data['TimetableMedService_id'])
			&& empty($data['TimetableResource_id'])
		) {
			$toQueue = true;

		} else if (!empty($data['TimetableGraf_id'])) {

			$data['TimetableStac_id'] = null;
			$data['TimetableMedService_id'] = null;
			$data['TimetableResource_id'] = null;

			$cnt = $this->getFirstResultFromQuery("
				select top 1 count(*) from v_TimetableGraf_lite with(nolock)
				where TimetableGraf_id = :TimetableGraf_id and Person_id is not null
			", $data);

		} else if (!empty($data['TimetableStac_id'])) {

			$data['TimetableMedService_id'] = null;
			$data['TimetableResource_id'] = null;

			$cnt = $this->getFirstResultFromQuery("
				select top 1 count(*) from v_TimetableStac_lite with(nolock)
				where TimetableStac_id = :TimetableStac_id and Person_id is not null
			", $data);

		} else if (!empty($data['TimetableMedService_id'])) {

			$data['TimetableResource_id'] = null;

			$cnt = $this->getFirstResultFromQuery("
				select top 1 count(*) from v_TimetableMedService_lite with(nolock)
				where TimetableMedService_id = :TimetableMedService_id and Person_id is not null
			", $data);

		} else if (!empty($data['TimetableResource_id'])) {

			$cnt = $this->getFirstResultFromQuery("
				select top 1 count(*) from v_TimetableResource_lite with(nolock)
				where TimetableResource_id = :TimetableResource_id and Person_id is not null
			", $data);
		}

		if ($cnt === false) return false;
		if ($cnt > 0) throw new Exception('На бирку уже выписано другое направление или она занята', 500);

		$this->beginTransaction();

		$data['EvnPrescr_id'] = null;
		$data['PrescriptionType_Code'] = null;

		if (
			!empty($data['DirType_id'])
			&& in_array($data['DirType_id'], array(10, 11, 15, 20))
			&& !empty($data['PrescriptionType_id'])
		) {

			// создаём назначение
			$data['PrescriptionType_Code'] = $this->getFirstResultFromQuery("
				select top 1 PrescriptionType_Code from v_PrescriptionType (nolock)
				where PrescriptionType_id = :PrescriptionType_id
				", array('PrescriptionType_id' => $data['PrescriptionType_id']
			));

			if ($data['PrescriptionType_id'] == 6) {
				// Манипуляции и процедуры
				$this->load->model('EvnPrescrProc_model');
				$resp_ep = $this->EvnPrescrProc_model->doSaveEvnCourseProc(array(
					'EvnCourseProc_pid' => $data['Evn_pid'],
					'EvnCourseProc_setDate' => $data['EvnDirection_setDate'],
					'EvnPrescrProc_Descr' => '',
					'MedPersonal_id' => $data['MedPersonal_id'],
					'LpuSection_id' => $data['LpuSection_id'],
					'PrescriptionType_id' => $data['PrescriptionType_id'],
					'EvnPrescrProc_IsCito' => ($data['EvnPrescr_IsCito'] == 2)?'on':null,
					'UslugaComplex_id' => $data['UslugaComplex_id'],
					'PersonEvn_id' => $info['PersonEvn_id'],
					'Server_id' => $info['Server_id'],
					'Lpu_id' => $data['Lpu_id'],
					'pmUser_id' => $data['pmUser_id']
				), false);
				if (!$this->isSuccessful($resp_ep)) {
					throw new Exception($resp_ep[0]['Error_Msg'], 500);
				}
				if (!empty($resp_ep[0]['EvnPrescrProc_id0'])) {
					$data['EvnPrescr_id'] = $resp_ep[0]['EvnPrescrProc_id0'];
				}
			} else if ($data['PrescriptionType_id'] == 11) {
				// Лабораторная диагностика
				$this->load->model('EvnPrescrLabDiag_model');
				$resp_ep = $this->EvnPrescrLabDiag_model->doSave(array(
					'EvnPrescrLabDiag_pid' => $data['Evn_pid'],
					'EvnPrescrLabDiag_setDate' => $data['EvnDirection_setDate'],
					'EvnPrescrLabDiag_Descr' => '',
					'PrescriptionType_id' => $data['PrescriptionType_id'],
					'EvnPrescrLabDiag_IsCito' => ($data['EvnPrescr_IsCito'] == 2)?'on':null,
					'EvnPrescrLabDiag_uslugaList' => $data['UslugaComplex_id'],
					'UslugaComplex_id' => $data['UslugaComplex_id'],
					'PersonEvn_id' => $info['PersonEvn_id'],
					'Server_id' => $info['Server_id'],
					'Lpu_id' => $data['Lpu_id'],
					'pmUser_id' => $data['pmUser_id']
				), false);
				if (!$this->isSuccessful($resp_ep)) {
					throw new Exception($resp_ep[0]['Error_Msg'], 500);
				}
				if (!empty($resp_ep[0]['EvnPrescrLabDiag_id'])) {
					$data['EvnPrescr_id'] = $resp_ep[0]['EvnPrescrLabDiag_id'];
				}
			} else if ($data['PrescriptionType_id'] == 12) {
				// Инструментальная диагностика
				$this->load->model('EvnPrescrFuncDiag_model');
				$resp_ep = $this->EvnPrescrFuncDiag_model->doSave(array(
					'EvnPrescrFuncDiag_pid' => $data['Evn_pid'],
					'EvnPrescrFuncDiag_setDate' => $data['EvnDirection_setDate'],
					'EvnPrescrFuncDiag_Descr' => '',
					'PrescriptionType_id' => $data['PrescriptionType_id'],
					'EvnPrescrFuncDiag_IsCito' => ($data['EvnPrescr_IsCito'] == 2)?'on':null,
					'EvnPrescrFuncDiag_uslugaList' => $data['UslugaComplex_id'],
					'UslugaComplex_id' => $data['UslugaComplex_id'],
					'PersonEvn_id' => $info['PersonEvn_id'],
					'Server_id' => $info['Server_id'],
					'Lpu_id' => $data['Lpu_id'],
					'pmUser_id' => $data['pmUser_id']
				), false);
				if (!$this->isSuccessful($resp_ep)) {
					throw new Exception($resp_ep[0]['Error_Msg'], 500);
				}
				if (!empty($resp_ep[0]['EvnPrescrFuncDiag_id'])) {
					$data['EvnPrescr_id'] = $resp_ep[0]['EvnPrescrFuncDiag_id'];
				}
			} else if ($data['PrescriptionType_id'] == 13) {
				// Консультационная услуга
				$this->load->model('EvnPrescrConsUsluga_model');
				$resp_ep = $this->EvnPrescrConsUsluga_model->doSave(array(
					'EvnPrescrConsUsluga_pid' => $data['Evn_pid'],
					'EvnPrescrConsUsluga_setDate' => $data['EvnDirection_setDate'],
					'EvnPrescrConsUsluga_Descr' => '',
					'PrescriptionType_id' => $data['PrescriptionType_id'],
					'EvnPrescrConsUsluga_IsCito' => ($data['EvnPrescr_IsCito'] == 2)?'on':null,
					'UslugaComplex_id' => $data['UslugaComplex_id'],
					'PersonEvn_id' => $info['PersonEvn_id'],
					'Server_id' => $info['Server_id'],
					'Lpu_id' => $data['Lpu_id'],
					'DopDispInfoConsent_id' => null,
					'pmUser_id' => $data['pmUser_id']
				), false);
				if (!$this->isSuccessful($resp_ep)) {
					throw new Exception($resp_ep[0]['Error_Msg'], 500);
				}
				if (!empty($resp_ep[0]['EvnPrescrConsUsluga_id'])) {
					$data['EvnPrescr_id'] = $resp_ep[0]['EvnPrescrConsUsluga_id'];
				}
			} else if ($data['PrescriptionType_id'] == 7) {
				// Операционный блок
				$this->load->model('EvnPrescrOperBlock_model');
				$resp_ep = $this->EvnPrescrOperBlock_model->doSave(array(
					'EvnPrescrOperBlock_pid' => $data['Evn_pid'],
					'EvnPrescrOperBlock_setDate' => $data['EvnDirection_setDate'],
					'EvnPrescrOperBlock_Descr' => '',
					'PrescriptionType_id' => $data['PrescriptionType_id'],
					'EvnPrescrOperBlock_IsCito' => ($data['EvnPrescr_IsCito'] == 2)?'on':null,
					'UslugaComplex_id' => $data['UslugaComplex_id'],
					'PersonEvn_id' => $info['PersonEvn_id'],
					'Server_id' => $info['Server_id'],
					'Lpu_id' => $data['Lpu_id'],
					'pmUser_id' => $data['pmUser_id']
				), false);
				if (!$this->isSuccessful($resp_ep)) {
					throw new Exception($resp_ep[0]['Error_Msg'], 500);
				}
				if (!empty($resp_ep[0]['EvnPrescrOperBlock_id'])) {
					$data['EvnPrescr_id'] = $resp_ep[0]['EvnPrescrOperBlock_id'];
				}
			}
		}

		$params = array(
			'EvnDirection_pid' => $data['Evn_pid'],
			'PersonEvn_id' => $info['PersonEvn_id'],
			'Server_id' => $info['Server_id'],
			'Person_id' => $info['Person_id'],
			'EvnDirection_Num' => !empty($data['EvnDirection_Num']) ? $data['EvnDirection_Num'] : '0',
			'EvnDirection_setDate' => $data['EvnDirection_setDate'],
			'PayType_id' => $data['PayType_id'],
			'DirType_id' => $data['DirType_id'],
			'Diag_id' => $data['Diag_id'],
			'EvnDirection_Descr' => $data['EvnDirection_Descr'],
			'Lpu_id' => $data['Lpu_id'],
			'Lpu_sid' => $data['Lpu_sid'],
			'LpuSection_id' => !empty($docInfo['LpuSection_id']) ? $docInfo['LpuSection_id'] : null,
			'MedPersonal_id' => !empty($docInfo['MedPersonal_id']) ? $docInfo['MedPersonal_id'] : null,
			'MedStaffFact_id' => $data['MedStaffFact_id'],
			'From_MedStaffFact_id' => $data['MedStaffFact_id'],
			'MedPersonal_zid' => $data['MedPersonal_zid'],
			'Lpu_did' => $data['Lpu_did'],
			'LpuUnit_did' => $data['LpuUnit_did'],
			'LpuSection_did' => !empty($data['LpuSection_did']) ? $data['LpuSection_did'] : null,
			'LpuSectionProfile_id' => $data['LpuSectionProfile_id'],
			'MedPersonal_did' => $data['MedPersonal_did'],
			'TimetableGraf_id' => !empty($data['TimetableGraf_id']) ? $data['TimetableGraf_id'] : null,
			'TimetableStac_id' => !empty($data['TimetableStac_id']) ? $data['TimetableStac_id'] : null,
			'TimetableMedService_id' => !empty($data['TimetableMedService_id']) ? $data['TimetableMedService_id'] : null,
			'TimetableResource_id' => !empty($data['TimetableResource_id']) ? $data['TimetableResource_id'] : null,
			'EvnDirection_IsCito' => $data['EvnPrescr_IsCito'],
			'EvnPrescr_id' => $data['EvnPrescr_id'],
			'PrescriptionType_Code' => $data['PrescriptionType_Code'],
			'MedService_id' => $data['MedService_id'],
			'Resource_id' => $data['Resource_id'],
			'pmUser_id' => $data['pmUser_id'],
			'EvnStatus_id' => 16, // пусть будет новое
			'EvnDirection_IsAuto' => 1,
			'StudyTarget_id' => !empty($data['StudyTarget_id']) ? $data['StudyTarget_id'] : null,
			'RecMethodType_id' => 13, // РИШ
			'RemoteConsultCause_id' => !empty($data['RemoteConsultCause_id']) ? $data['RemoteConsultCause_id'] : null,
			'session' => $data['session'],
		);

		if ($toQueue) $params['toQueue'] = true;
		$resp_ed = $this->saveEvnDirection($params);

		if (!$this->isSuccessful($resp_ed)) {
			$this->rollbackTransaction();
			throw new Exception($resp_ed[0]['Error_Msg'], 500);
		}

		if (!empty($resp_ed[0]['EvnDirection_id']) && $toQueue) {

			// достаём идентификатор очереди
			$resp_ed[0]['EvnQueue_id'] = $this->getFirstResultFromQuery("
				select top 1 EvnQueue_id from v_EvnQueue (nolock)
				where EvnDirection_id = :EvnDirection_id
				", array('EvnDirection_id' => $resp_ed[0]['EvnDirection_id'])
			);
		}

		if (!empty($data['EvnPrescr_id'])) {
			$resp_ed[0]['EvnPrescr_id'] = $data['EvnPrescr_id'];

			// Сохраняем заказ услуги
			$this->load->model('EvnUsluga_model', 'eumodel');
			try {
				$this->eumodel->saveUslugaOrder(array(
					'order' => json_encode(array(
						'UslugaComplex_id' => $data['UslugaComplex_id'],
						'checked' => '[]'
					)),
					'EvnDirection_id' => $resp_ed[0]['EvnDirection_id'],
					'EvnPrescr_id' => $resp_ed[0]['EvnPrescr_id'],
					'Person_id' => $data['Person_id'],
					'PersonEvn_id' => $info['PersonEvn_id'],
					'Server_id' => $info['Server_id'],
					'session' => $data['session']
				));
			} catch (Exception $e) {
				$this->rollbackTransaction();
				throw new Exception($e->getMessage(), 500);
			}
		}

		$this->commitTransaction();

		return $resp_ed;
	}

	/**
	 * Загрузка направлений и записей для РВФД Ufa, gaf #116422, для ГАУЗ РВФД
	*/	
	function getEvnDirectionRVFD($data) {
		$query = "							

			--variables
			DECLARE @date date = dbo.tzGetDate();
			DECLARE @time datetime = dbo.tzGetDate();
			--end variables
		
			 select top 10

				TT.recDate as EvnDirection_RecDate
				,LSP.LpuSectionProfile_Name
				
			-- end select
			from
			-- from
				v_EvnDirection_all ED with (nolock)
				inner join v_PersonState PS with(nolock) on ED.Person_id = PS.Person_id
				
				left join v_Lpu_all Lpu with (nolock) on Lpu.Lpu_id = ED.Lpu_sid  
				--left join v_LpuUnit DLU with (nolock) on DLU.LpuUnit_id = ED.LpuUnit_did
				left join v_Lpu_all DLpu with (nolock) on DLpu.Lpu_id = ED.Lpu_did
				left join v_Diag Diag with(nolock) on ED.Diag_id = Diag.Diag_id 
				cross apply (
					select top 1 * from (
						select top 1 
							null as EvnQueue_id,
							ED.LpuSectionProfile_id as LpuSectionProfile_did,
							null as TimetableGraf_id,
							null as TimetableMedService_id,
							null as TimetableResource_id,
							Timetable.TimetableStac_id,
							null as EvnQueue_setDT,
							Timetable.pmUser_insID,
							Timetable.pmUser_updID,
							convert(varchar(10), Timetable.TimetableStac_setDate, 104) as recDate,
							null as recSort,
							null as MedPersonal_Fio
						from 
							v_TimetableStac_lite Timetable (nolock)
						where
							ED.DirType_id in (1,2,4,5,6)
							and Timetable.EvnDirection_id = ED.EvnDirection_id  and ISNULL(cast(Timetable.TimetableStac_setDate as DATE), @date) >= @date
						UNION ALL
						select top 1
							null as EvnQueue_id,
							ED.LpuSectionProfile_id as LpuSectionProfile_did,
							null as TimetableGraf_id,
							Timetable.TimetableMedService_id,
							null as TimetableResource_id,
							null as TimetableStac_id,
							null as EvnQueue_setDT,
							Timetable.pmUser_insID,
							Timetable.pmUser_updID,
							convert(varchar(10),Timetable.TimetableMedService_begTime,104) + ' ' + convert(varchar(5),Timetable.TimetableMedService_begTime,108) as recDate,
							Timetable.TimetableMedService_begTime as recSort,
							null as MedPersonal_Fio
						from 
							v_TimetableMedService_lite Timetable (nolock)
							left join v_UslugaComplexMedService ucms (nolock) on ucms.UslugaComplexMedService_id = Timetable.UslugaComplexMedService_id
						where
							ED.DirType_id in (2,3,10,11,15,25)
							and Timetable.EvnDirection_id = ED.EvnDirection_id  and ISNULL(cast(Timetable.TimetableMedService_begTime as DATE), @date) >= @date
						UNION ALL
						select top 1
							null as EvnQueue_id,
							ED.LpuSectionProfile_id as LpuSectionProfile_did,
							null as TimetableGraf_id,
							null as TimetableMedService_id,
							Timetable.TimetableResource_id,
							null as TimetableStac_id,
							null as EvnQueue_setDT,
							Timetable.pmUser_insID,
							Timetable.pmUser_updID,
							convert(varchar(10),Timetable.TimetableResource_begTime,104) + ' ' + convert(varchar(5),Timetable.TimetableResource_begTime,108) as recDate,
							Timetable.TimetableResource_begTime as recSort,
							null as MedPersonal_Fio
						from
							v_TimetableResource_lite Timetable (nolock)
							left join v_Resource r (nolock) on r.Resource_id = Timetable.Resource_id
						where
							ED.DirType_id in (10)
							and Timetable.EvnDirection_id = ED.EvnDirection_id  and ISNULL(cast(Timetable.TimetableResource_begTime as DATE), @date) >= @date
						UNION ALL
						select top 1
							null as EvnQueue_id,
							ED.LpuSectionProfile_id as LpuSectionProfile_did,
							Timetable.TimetableGraf_id,
							null as TimetableMedService_id,
							null as TimetableResource_id,
							null as TimetableStac_id,
							null as EvnQueue_setDT,
							Timetable.pmUser_insID,
							Timetable.pmUser_updID,
							convert(varchar(10),isnull(Timetable.TimetableGraf_begTime, Timetable.TimetableGraf_factTime),104) + ' ' + convert(varchar(5),isnull(Timetable.TimetableGraf_begTime, Timetable.TimetableGraf_factTime),108) as recDate,
							isnull(Timetable.TimetableGraf_begTime, Timetable.TimetableGraf_factTime) as recSort,
							MSFT.Person_Fio as MedPersonal_Fio
						from
							v_TimeTableGraf_lite Timetable (nolock)
							left join v_MedStaffFact MSFT with (NOLOCK) on Timetable.MedStaffFact_id = MSFT.MedStaffFact_id
						where
							ED.DirType_id in (2,3,4,6,16)
							and Timetable.EvnDirection_id = ED.EvnDirection_id  and ISNULL(cast(isnull(Timetable.TimetableGraf_begTime, Timetable.TimetableGraf_factTime) as DATE), @date) >= @date
						UNION ALL
						select top 1
							EQ.EvnQueue_id,
							EQ.LpuSectionProfile_did,
							null as TimetableGraf_id,
							null as TimetableMedService_id,
							null as TimetableResource_id,
							null as TimetableStac_id,
							EQ.EvnQueue_setDT,
							EQ.pmUser_insID,
							EQ.pmUser_updID,
							null as recDate,
							EQ.EvnQueue_setDT as recSort,
							null as MedPersonal_Fio
						from 
							v_EvnQueue EQ (nolock)
						where EQ.EvnDirection_id = ED.EvnDirection_id
							and (EQ.EvnQueue_recDT is null or EQ.pmUser_recID = 1)
							and EQ.EvnQueue_failDT is null
							and EQ.EvnQueue_IsArchived is null 
						UNION ALL
						select
							null as EvnQueue_id,
							ED.LpuSectionProfile_id as LpuSectionProfile_did,
							null as TimetableGraf_id,
							null as TimetableMedService_id,
							null as TimetableResource_id,
							null as TimetableStac_id,
							null as EvnQueue_setDT,
							ED.pmUser_insID,
							ED.pmUser_updID,
							null as recDate,
							null as recSort,
							null as MedPersonal_Fio
						where ED.DirType_id = 5 and isnull(ED.EvnDirection_IsAuto,1) = 1 
						
					) tt
				) TT
				left join v_MedStaffFact MSF with (NOLOCK) on ed.MedStaffFact_id = MSF.MedStaffFact_id
				left join v_MedPersonal MP with (NOLOCK) on ED.MedPersonal_id = MP.MedPersonal_id and MP.Lpu_id = Lpu.Lpu_id
				left join v_LpuSection LS with (nolock) on ED.LpuSection_id = LS.LpuSection_id
				left join v_Address_all UAddress (nolock) on UAddress.Address_id = PS.UAddress_id
				left join v_MedService MS with (nolock) on MS.MedService_id = ED.MedService_id
				left join v_LpuSection DLpuSection with(nolock) on DLpuSection.LpuSection_id = ED.LpuSection_did
				left join v_DirType DT with (nolock) on DT.DirType_id = ED.DirType_id
				left join v_EvnStatus ES (nolock) on ES.EvnStatus_id = ED.EvnStatus_id
				outer apply (
					Select top 1
						EUP.UslugaComplex_id,
						uc.UslugaComplex_Name
					from 
						v_EvnUslugaPar EUP (nolock)
						left join v_UslugaComplex uc (nolock) on uc.UslugaComplex_id = EUP.UslugaComplex_id
					where
						ED.DirType_id in (10,11,15)
						and EUP.EvnDirection_id = ED.EvnDirection_id
				) EUP
				left join v_pmUser pmi with (nolock) on pmi.PMUser_id=ED.pmUser_insID
				
				left join v_LpuSectionProfile LSP with (nolock) on TT.LpuSectionProfile_did = LSP.LpuSectionProfile_id
				left join v_TimeTableGraf_lite ttf with (nolock) on ttf.TimeTableGraf_id = TT.TimetableGraf_id
				left join fer.v_Slot slot with (nolock) on slot.TimeTableGraf_id = TT.TimetableGraf_id

			where
		
			(1=1)
			 and isnull(ED.DirType_id,1) not in (7, 18, 19, 20) and ED.EvnStatus_id not in (12, 13, 15) 
			 and ED.Person_id=:Person_id
			 and ISNULL(ED.EvnDirection_IsAuto, 1) = 2
			order by
			TT.recSort desc
		";
		/*
		$db = $this->load->database('default', true);
		$result = $db->query($query, array(
			'Person_id' => $data
		));
		
		if (is_object($result)) {
			$resp = $result->result('array');
			if (!empty($resp[0])) {
				$resp[0]['Error_Msg'] = '';
				return $resp;
				
			}
		}

		return false;
		*/
		$resp = $this->queryResult($query, array('Person_id' => $data));		
		return $resp;		
	}

	/**
	 * Загрузка ФИО, Адрес рег Ufa, gaf #116422, для ГАУЗ РВФД
	 */	
	function getPersonFIOAddr($data)
	{
		$query = "							
			select top 1
				vper.Person_SurName,
				vper.Person_SecName,
				vper.Person_FirName,
				LEFT(CONVERT(VARCHAR, vper.Person_BirthDay, 104), 10) Person_BirthDay,
				uaddr.Address_Address as UAddress_AddressText,
				(select pp.post_name from v_PersonState (nolock) vps , job (nolock) jj, post (nolock) pp where vps.person_id=vper.person_id and vps.job_id=jj.job_id and jj.post_id=pp.Post_id) as Job_Name
			from v_PersonState vper with (nolock)
				left join v_Person per with (nolock) on per.Person_id=vper.Person_id
				left join v_Address uaddr with (nolock) on vper.UAddress_id = uaddr.Address_id
			where vper.Person_id = :Person_id
		";
		/*		
		$db = $this->load->database('default', true);
		
		$result = $db->query($query, array(
			'Person_id' => $data
		));
		
		if (is_object($result)) {
			$resp = $result->result('array');
			if (!empty($resp[0])) {
				$resp[0]['Error_Msg'] = '';
				return $resp[0];
				
			}
		}

		return false;
		*/		
		$resp = $this->queryResult($query, array('Person_id' => $data));		
		return $resp[0];
	}

	/**
	 * Метод возвращает информацию о направлении, переданном в БГ
	 *
	 * @param $data
	 * @return array|bool
	 */
	function getInfoEvnDirectionfromBg($data)
	{
		$query = "
			SELECT 
				Referral_id as id,
				Referral_Code as code,
				convert(varchar, EvnDirectionLink_insDT,104) +' '+ convert(char(5), EvnDirectionLink_insDT,108) as insDate
			FROM
				r101.v_EvnDirectionLink
			WHERE Referral_id = :Referral_id
		";

		$result = $this->getFirstRowFromQuery($query, array('Referral_id' => $data['id']));


		if (!is_array($result)) {
			return false;
		}
		return $result;
	}

	/**
	 * Метод возвращает справочник форм оказания консультаций
	 */
	function getConsultingFormList() {
		$query = "
		SELECT
			ConsultingForm_id,
			ConsultingForm_Name
		FROM ConsultingForm
		ORDER BY ConsultingForm_id ASC
		";
		$result = $this->db->query($query);
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}
	/**
	 * Получение информации об услуге по идентификатору направления
	*/
	function EvnDirectionUslugaComplex($data) {
		$filter = "";
		if(isset($data['Lpu_id']) && !empty($data['Lpu_id']))
			$filter = " and EUP.Lpu_id = :Lpu_id";

		$query = "
		select
			EUP.UslugaComplex_id,
			UslugaComplex_Code
		from 
			v_EvnUslugaPar EUP with (nolock)
			left join v_UslugaComplex UC with (nolock) on EUP.UslugaComplex_id = UC.UslugaComplex_id
		where 
			EUP.EvnDirection_id = :EvnDirection_id
		" . $filter;

		$result = $this->db->query($query, $data);
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}
	
	/**
	 * Получение Evn_id из PersonEvn
	 */
	function getEvnfromPersonEvn($data) {
		$resp_evn = $this->queryResult("
				select
					e.Evn_id
				from
					v_Evn e with (nolock)
					inner join v_PersonEvn pe with (nolock) on pe.Person_id = e.Person_id
						and pe.PersonEvn_id = :PersonEvn_id
				where
					e.Evn_id = :EvnDirection_pid
			",$data);

		if (empty($resp_evn[0]['Evn_id'])) {
			return array(array('Error_Msg' => 'Некорректно указан пациент, сохранение направления не возможно'));
		}

		return ['Evn_id' => $resp_evn[0]['Evn_id']];
	}

	/**
	 * Получение способа записи для направления по ARMType_id
	 */
	function getRecMethodTypeForDirection($data){
		if (empty($data['ARMType_id'])) {
			return null;
		}

		$query = "
			SELECT top 1
				RMT.RecMethodType_id
			FROM v_RecMethodType RMT
				outer apply(
					SELECT ARMType_Code, ARMType_id,
						case 
							when ARMType_Code in (1,2,3,12,11,41,7) then 10	--если АРМ «Врач поликлиники», «АРМ врача стационара», «АРМ диагностики», «АРМ пункта забора», «АРМ регистрационной службы лаборатории», «АРМ лаборанта» , то «Промед: Врач»
							when ARMType_Code in (25) then 16				--если АРМ Регистратор поликлиники, то способ записи = «Промед: регистратор»
							when ARMType_Code in (26) then 17				--если форма вызвана в «АРМ оператор call-центра», то способ записи = «Промед: оператор call-центра»
							when ARMType_Code in (45,138,139) then 18		--если в «АРМ пользователя СМО» / «АРМ пользователя ТФОМС», то способ записи = «СМО/ТФОМС».
							else 0
						end RecMethodType_Code
					FROM v_ARMType
				) ARMType
			WHERE 
				ARMType.ARMType_id = :ARMType_id
				AND ARMType.RecMethodType_Code = RMT.RecMethodType_Code";
		$res = $this->getFirstRowFromQuery($query, $data);
		if(!empty($res['RecMethodType_id'])){
			if(getRegionNick() == 'penza' && $res['RecMethodType_id'] == 16 && in_array('OperatorCallCenter', explode('|', $data['session']['groups'])) ){
				//при выполнении действий из арм регистратора ползователями группы кол-центр, источник записи равен "кол-центр"
				$res['RecMethodType_id'] = 17;
			}
			return $res['RecMethodType_id'];
		} else {
			return null;
		}
	}

	/**
	 * Получение evnlabsample и evnlabrequest по evndirection
	 */
	function getEvnLabSampleAndRequest($data) {
		$res = $this->getFirstRowFromQuery("
			select top 1
				els.EvnLabSample_id as \"EvnLabSample_id\",
				elr.EvnLabRequest_id as \"EvnLabRequest_id\",
				elr.PersonEvn_id as \"PersonEvn_id\",
				elr.Server_id as \"Server_id\"
			from
				v_EvnDirection_all ed with(nolock)
				inner join v_EvnLabRequest elr with(nolock) on elr.EvnDirection_id = ed.EvnDirection_id
				inner join v_EvnLabSample els with(nolock) on els.EvnLabRequest_id = elr.EvnLabRequest_id
			where
				ed.EvnDirection_id = :EvnDirection_id
		", $data);

		return $res;
}

	/**
	 * @param array $data
	 * @return array|false
	 */
	function getEvnLabSamplesDefect($data) {
		if (!isset($data['EvnDirection_id']) || empty($data['EvnDirection_id'])) {
			return false;
		}

		$queryParams = array(
			'EvnDirection_id' => $data['EvnDirection_id']
		);

		$query = "
			select
				ELS.EvnLabSample_id as \"EvnLabSample_id\",
				DCT.DefectCauseType_Name as \"DefectCauseType_Name\"
			from 
				v_EvnLabRequest ELR with(nolock)
				inner join v_EvnLabSample ELS with(nolock) on ELS.EvnLabRequest_id = ELR.EvnLabRequest_id
				inner join lis.v_DefectCauseType DCT with(nolock) on DCT.DefectCauseType_id = ELS.DefectCauseType_id
			where 
				ELR.EvnDirection_id = :EvnDirection_id and 
				ELS.LabSampleStatus_id = 5
		";

		//echo getDebugSql($query, $queryParams);exit;
		return $this->queryResult($query, $queryParams);
	}

	/**
	 * Получение данных для панели просмотра ЭМК и правой панели формы добавления назначений
	 */
	function doLoadView($data) {
		$sysnick = $data['sysnick'];
		$addJoin = '';
		$filter = '';
		$testFilter = getAccessRightsTestFilter('UC.UslugaComplex_id');
		$filterAccessRightsDenied = getAccessRightsTestFilter('UCMPp.UslugaComplex_id', false, true);

		$except_ids = array();
		if (!empty($data['excepts'])) {
			foreach($data['excepts'] as $except) {
				if (!empty($except['EvnDirection_id'])) {
					$excepts_ids[] = $except['EvnDirection_id'];
				}
			}
		}
		if (count($except_ids) > 0) {
			$except_ids = implode(",", $except_ids);
			$filter .= " and ED.EvnDirection_id not in ({$except_ids})";
		}

		$ucpCondition = ' and UCp.UslugaComplex_id is null ';
		if (!$sysnick) $ucpCondition = "";

		if (!empty($testFilter)){
			$filter .= "
				and (
					ED.MedPersonal_id = :MedPersonal_id or
					exists (
						select 
							*
						from 
							v_Evn with(nolock)
						where
							Evn_id = :EvnPrescr_pid
							and EvnClass_sysNick = 'EvnSection'
							and Evn_setDT <= :EvnPrescr_setDT
							and (Evn_disDT is null
								or Evn_disDT >= :EvnPrescr_setDT)
					)
					or ({$testFilter} {$ucpCondition})
				)";
		}

		if ($sysnick) {
			$addJoin = "outer apply(
					select top 1
						UCMPp.UslugaComplex_id
					from
						v_UslugaComplexMedService UCMPp with(nolock)
					inner join v_EvnLabRequestUslugaComplex ELRUC with(nolock) on UCMPp.UslugaComplex_id = ELRUC.UslugaComplex_id
						and ELRUC.EvnLabRequest_id = LR.EvnLabRequest_id
					inner join v_EvnLabSample ELS with(nolock) on ELS.EvnLabSample_id = ELRUC.EvnLabSample_id
						and ELS.LabSampleStatus_id IN(4,6)
					where
						UCMS.UslugaComplexMedService_id = UCMPp.UslugaComplexMedService_pid
						".((!empty($filterAccessRightsDenied))?"and ".$filterAccessRightsDenied:'')."
				) UCp
			";
		}

		$UslugaComplex_Code = "UC.UslugaComplex_Code as \"UslugaComplex_Code\"";
		$UslugaComplex_Name = "coalesce(ucms.UslugaComplex_Name, UC.UslugaComplex_Name) as \"UslugaComplex_Name\"";

		if (!empty($this->options['prescription']['enable_grouping_by_gost2011']) || $this->options['prescription']['service_name_show_type'] == 2) {
			$UslugaComplex_Code = 'UC11.UslugaComplex_Code';
			$UslugaComplex_Name = 'UC11.UslugaComplex_Name';
		}

		$query = "
			select
				:EvnPrescr_id as \"EvnPrescr_id\",
				case when ED.EvnDirection_id is null OR coalesce(ED.EvnStatus_id, 16) in (12,13) then 1 else 2 end as \"EvnPrescr_IsDir\",
				case when ED.EvnStatus_id is null and (ED.DirFailType_id > 0) then 12 else ED.EvnStatus_id end as \"EvnStatus_id\",
				ED.DirFailType_id as \"DirFailType_id\",
				ED.EvnStatus_id as \"EvnStatus_id\",
				null as \"QueueFailCause_id\",
				null as \"EvnQueue_id\",
				case when EU.EvnUsluga_id is null then 1 else 2 end as \"EvnPrescr_IsHasEvn\",
				ED.EvnDirection_id as \"EvnDirection_id\",
				case when ED.EvnDirection_Num is null then '' else cast(ED.EvnDirection_Num as varchar) end as \"EvnDirection_Num\",
				LU.LpuUnitType_SysNick as \"LpuUnitType_SysNick\",
				case
					when :TimetableMedService_id != '1' then coalesce(MS.MedService_Name,'') +' / '+ coalesce(Lpu.Lpu_Nick,'')
					when ED.EvnStatus_id is not null then
						case
							when MS.MedService_id is not null and  MS.LpuSection_id is null and MS.LpuUnit_id is null
							then coalesce(MS.MedService_Name,'')
							when MS.MedService_id is not null and  MS.LpuSection_id is null and MS.LpuUnit_id is not null
							then coalesce(MS.MedService_Name,'') +' / '+ coalesce(LU.LpuUnit_Name,'')
							when MS.MedService_id is not null and  MS.LpuSection_id is not null and MS.LpuUnit_id is not null
							then coalesce(MS.MedService_Name,'') +' / '+ coalesce(LSPD.LpuSectionProfile_Name,'') +' / '+ coalesce(LU.LpuUnit_Name,'')
							else coalesce(LSPD.LpuSectionProfile_Name,'') +' / '+ coalesce(LU.LpuUnit_Name,'')
						end +' / '+ coalesce(Lpu.Lpu_Nick,'')
				else '' end as \"RecTo\",
				case
					when :TimetableMedService_id != '1' then coalesce(convert(varchar(10), :TimetableMedService_begTime, 104)+' '+convert(varchar(8), :TimetableMedService_begTime, 108),'')
					when ED.EvnStatus_id is not null then 'В очереди с '+ coalesce(convert(varchar(10), ED.EvnDirection_setDate, 104),'') --пока так
				else '' end as \"RecDate\",
				case
					when :TimetableMedService_id != '1' then 'TimetableMedService'
					when ED.EvnStatus_id is not null then 'EvnQueue'
				else '' end as \"timetable\",
				case
					when :TimetableMedService_id != '1'  then :TimetableMedService_id -- пока так
				else null end as \"timetable_id\",
				DT.DirType_Code as \"DirType_Code\",
				UCMS.UslugaComplexMedService_id as \"UslugaComplexMedService_id\",
				null as \"EvnStatusCause_id\",
				null as \"EvnStatusHistory_Cause\",
				case when exists(
					select
						*
					from
						v_UslugaComplexMedService ucms2 with(nolock)
						inner join lis.v_AnalyzerTest at2 with(nolock) on at2.UslugaComplexMedService_id = ucms2.UslugaComplexMedService_id
						inner join lis.v_Analyzer a2 with(nolock) on a2.Analyzer_id = at2.Analyzer_id
					where
						ucms2.UslugaComplexMedService_pid = UCMS.UslugaComplexMedService_id
						and coalesce(at2.AnalyzerTest_IsNotActive, 1) = 1 and coalesce(a2.Analyzer_IsNotActive, 1) = 1	
				) then 1 else 0 end as \"isComposite\",
				UC.UslugaComplex_id as \"UslugaComplex_id\",
				UC.UslugaComplex_2011id as \"UslugaComplex_2011id\",
				{$UslugaComplex_Code},
				{$UslugaComplex_Name},
				EUP.EvnUslugaPar_id as \"EvnUslugaPar_id\",
				CASE 
					when Lpu.Lpu_id is not null and Lpu.Lpu_id <> LpuSession.Lpu_id then 2 else 1
				end as \"otherMO\",
				ES.EvnStatus_SysNick as \"EvnStatus_SysNick\",
				case when ES.EvnStatus_Name is null and (ED.DirFailType_id > 0 ) then 'Отменено' else ES.EvnStatus_Name end as \"EvnStatus_Name\",
				DFT.DirFailType_Name as \"EvnStatusCause_Name\",
				convert(varchar(10), coalesce(ED.EvnDirection_statusDate, ED.EvnDirection_failDT), 104) as \"EvnDirection_statusDate\",
				lr.EvnStatus_id as \"EvnLabRequestStatus\"
			from
				v_EvnDirection_all ED with(nolock)
				left join v_UslugaComplex UC with(nolock) on UC.UslugaComplex_id = :UslugaComplex_id
				left join v_UslugaComplex UC11 with(nolock) on UC11.UslugaComplex_id = UC.UslugaComplex_2011id
				left join v_MedService MS with(nolock) on ms.MedService_id  = ED.MedService_id
				left join v_LpuSection LS with(nolock) on LS.LpuSection_id = coalesce(ED.LpuSection_did, MS.LpuSection_id)
				outer apply(
					select top 1
						EUP.EvnUslugaPar_id
					from
						v_EvnUslugaPar EUP with(nolock)
					where
						EUP.EvnDirection_id = ED.EvnDirection_id
						and EUP.EvnPrescr_id = :EvnPrescr_id
				) EUP
				left join v_LpuUnit LU with(nolock) on LU.LpuUnit_id = coalesce(ED.LpuUnit_did, MS.LpuUnit_id)
				left join v_LpuSectionProfile LSPD with(nolock) on LSPD.LpuSectionProfile_id = coalesce(ED.LpuSectionProfile_id, LS.LpuSectionProfile_id)
			  	-- ЛПУ
				left join v_Lpu Lpu with(nolock) on Lpu.Lpu_id = coalesce(ED.Lpu_did, LS.Lpu_id, MS.Lpu_id)
				left join v_Lpu LpuSession with(nolock) on LpuSession.Lpu_id = :Lpu_id
				outer apply(
					select top 1
						EvnUsluga_id,
						EvnUsluga_setDT
					from
						v_EvnUsluga with(nolock)
					where
						:EvnPrescr_IsExec = 2
						and UC.UslugaComplex_id is not null
						and EvnPrescr_id = :EvnPrescr_id	
				) EU
				left join v_EvnLabRequest LR with(nolock) on LR.EvnDirection_id = ED.EvnDirection_id
				-- услуга на службе
				left join v_UslugaComplexMedService UCMS with(nolock) on UCMS.MedService_id = LR.MedService_id
					and UCMS.UslugaComplex_id = :UslugaComplex_id
					and UCMS.UslugaComplexMedService_pid is null
				left join v_EvnStatus ES with(nolock) on ES.EvnStatus_id = ED.EvnStatus_id
				left join v_DirFailType DFT with(nolock) on DFT.DirFailType_id = ED.DirFailType_id
				left join v_DirType DT with(nolock) on ED.DirType_id = DT.DirType_id
				{$addJoin}
			where
				ED.EvnDirection_id = :EvnDirection_id
				{$filter}
		";

		$list = array();
		foreach($data['EvnPrescrList'] as $EvnPrescr) {
			$EvnDirectionData = $this->queryResult($query, $EvnPrescr);
			if (!is_array($EvnDirectionData)) {
				return false;
			}
			if (count($EvnDirectionData) == 0) {
				continue;
			}

			$EvnLabSamplesDefect = $this->getEvnLabSamplesDefect($EvnPrescr);
			if (!is_array($EvnLabSamplesDefect)) {
				return false;
			}
			foreach($EvnDirectionData as &$item) {
				$item['EvnLabSampleDefect'] = $EvnLabSamplesDefect;
			}

			$list = array_merge($list, $EvnDirectionData);
		}

		return $list;
	}

	/**
	 * Загрузка полей по направлению для печати
	*/
	function getEvnDirectionFieldsForPostge($data)
	{
		$UslugaComplexNameField = "ISNULL(UC.UslugaComplex_Name,'')";

		if ($this->regionNick == 'ufa' && $this->options['prescription']['service_name_show_type'] == 1) {

			$UslugaComplexNameField = "COALESCE(UCMS.UslugaComplex_Name,UC.UslugaComplex_Name,'')";
		}

		$query = "
			with EvnDirectionCTE as (
				select
					:EvnDirection_id as EvnDirection_id,
					:EvnDirection_Num as EvnDirection_Num,
					:DirType_id as DirType_id,
					:EvnDirection_Descr as EvnDirection_Descr,
					:Dir_Day as Dir_Day,
					:Dir_Month as Dir_Month,
					:Dir_Year as Dir_Year,
					:EvnDirection_setDate as EvnDirection_setDate,
					:TimetableGraf_id as TimetableGraf_id,
					:TimetableStac_id as TimetableStac_id,
					:TimetablePar_id as TimetablePar_id,
					:TimetableMedService_id as TimetableMedService_id,
					:Lpu_id as Lpu_id,
					:LpuSection_id as LpuSection_id,
					:LpuSection_did as LpuSection_did,
					:Lpu_did as Lpu_did,
					:Org_oid as Org_oid,
					:LpuUnit_did as LpuUnit_did,
					:Medpersonal_id as Medpersonal_id,
					:MedPersonal_did as MedPersonal_did,
					:Medpersonal_zid as Medpersonal_zid,
					:MedService_id as MedService_id,
					:Person_id as Person_id,
					:PersonEvn_id as PersonEvn_id,
					:LpuSectionProfile_id as LpuSectionProfile_id,
					:Diag_id as Diag_id,
					:EvnStatus_id as EvnStatus_id,
					:MedStaffFact_id as MedStaffFact_id,
					:Post_id as Post_id,
					:MedicalCareFormType_id as MedicalCareFormType_id
			)
		
			select
				d.EvnDirection_Num,
				isnull(fedMCFT.MedicalCareFormType_Code, '') as MedicalCareFormType_Code,
				-- Адрес направляющего учреждения
				case when a.klcity_id != 3310 or a.klcity_id is null then isnull((select top 1
				rtrim(KLSocr.KLSocr_Nick) + ' ' + rtrim(KLArea.KLArea_Name) + ', '
				from klarea with (nolock)
				left outer join KLSocr with (nolock) on KLSocr.KLSocr_id = KLArea.KLSocr_id
				where klarea.klarea_id=a.klrgn_id),'') else '' end+
				isnull((select top 1
				rtrim(KLSocr.KLSocr_Nick) + ' ' + rtrim(KLArea.KLArea_Name) + ', '
				from klarea with (nolock)
				left outer join KLSocr with (nolock) on KLSocr.KLSocr_id = KLArea.KLSocr_id
				where klarea.klarea_id=a.klsubrgn_id),'')+
				isnull((select top 1
				rtrim(KLSocr.KLSocr_Nick) + ' ' + rtrim(KLArea.KLArea_Name) + ', '
				from klarea with (nolock)
				left outer join KLSocr with (nolock) on KLSocr.KLSocr_id = KLArea.KLSocr_id
				where klarea.klarea_id=a.klcity_id),'')+
				isnull((select top 1
				rtrim(KLSocr.KLSocr_Nick) + ' ' + rtrim(KLArea.KLArea_Name) + ', '
				from klarea with (nolock)
				left outer join KLSocr with (nolock) on KLSocr.KLSocr_id = KLArea.KLSocr_id
				where klarea.klarea_id=a.kltown_id),'')+
				isnull((select top 1
				rtrim(KLSocr.KLSocr_Nick) + ' ' + rtrim(KLStreet.KLStreet_Name) + ', '
				from KlStreet with (nolock)
				left outer join KLSocr with(nolock) on KLSocr.KLSocr_id = KlStreet.KLSocr_id
				where KlStreet.KlStreet_id=a.KlStreet_id),'')+
				isnull('д '+RTrim(a.Address_House),'')+
				isnull(', кв '+RTrim(a.Address_Flat),'') as Address_Address,
				-- Адрес подразделения, куда направляют
				case when lua.klcity_id != 3310 or lua.klcity_id is null then isnull((select top 1
				rtrim(KLSocr.KLSocr_Nick) + ' ' + rtrim(KLArea.KLArea_Name) + ', '
				from klarea with (nolock)
				left outer join KLSocr with (nolock) on KLSocr.KLSocr_id = KLArea.KLSocr_id
				where klarea.klarea_id=lua.klrgn_id),'') else '' end+
				isnull((select top 1
				rtrim(KLSocr.KLSocr_Nick) + ' ' + rtrim(KLArea.KLArea_Name) + ', '
				from klarea with (nolock)
				left outer join KLSocr with (nolock) on KLSocr.KLSocr_id = KLArea.KLSocr_id
				where klarea.klarea_id=lua.klsubrgn_id),'')+
				isnull((select top 1
				rtrim(KLSocr.KLSocr_Nick) + ' ' + rtrim(KLArea.KLArea_Name) + ', '
				from klarea with (nolock)
				left outer join KLSocr with (nolock) on KLSocr.KLSocr_id = KLArea.KLSocr_id
				where klarea.klarea_id=lua.klcity_id),'')+
				isnull((select top 1
				rtrim(KLSocr.KLSocr_Nick) + ' ' + rtrim(KLArea.KLArea_Name) + ', '
				from klarea with (nolock)
				left outer join KLSocr with (nolock) on KLSocr.KLSocr_id = KLArea.KLSocr_id
				where klarea.klarea_id=lua.kltown_id),'')+
				isnull((select top 1
				rtrim(KLSocr.KLSocr_Nick) + ' ' + rtrim(KLStreet.KLStreet_Name) + ', '
				from KlStreet with (nolock)
				left outer join KLSocr with (nolock) on KLSocr.KLSocr_id = KlStreet.KLSocr_id
				where KlStreet.KlStreet_id=lua.KlStreet_id),'')+
				isnull('д '+RTrim(lua.Address_House),'')+
				isnull(', кв '+RTrim(lua.Address_Flat),'') as LpuUnit_Address,

				msf.Person_Snils as med_snils,
				msf.medpersonal_init as med_init,
				zav.medpersonal_init as zav_init,
				rtrim(l.Lpu_Name) as Lpu_Name,
				rtrim(l.Lpu_f003mcod) as Lpu_f003mcod,
				rtrim(l.Lpu_OGRN) as Lpu_OGRN,
				rtrim(ISNULL(ld.Lpu_Name, od.Org_Name)) as dLpu_Name,
				rtrim(lsp.LpuSectionProfile_Name) as LpuSectionProfile_Name, 
				case when pol.Polis_endDate is null OR d.EvnDirection_setDate < pol.Polis_endDate 
				then 
					(case when pol.PolisType_id = 4 then '' else isnull(pol.Polis_Ser, '') end) 
					+' ' 
					+(case when pol.PolisType_id = 4 and p.Person_EdNum is not null then p.Person_EdNum else isnull(pol.Polis_Num, '') end)
				else ''
				end as Polis,
				ISNULL(p.Person_Inn,'') as INN,
				rtrim(p.Person_Surname)+' '+rtrim(p.Person_Firname)+' '+isnull(rtrim(p.Person_Secname),'') as Person_FIO,
				convert(varchar, Person_Birthday, 104) as Person_Birthdate,
				rtrim(Person_Phone) as Person_Phone,
				case when pa1.Address_id is not null
				then
						case when pa1.klcity_id != 3310 or pa1.klcity_id is null then isnull((select top 1
						rtrim(KLSocr.KLSocr_Nick) + ' ' + rtrim(KLArea.KLArea_Name) + ', '
						from klarea with (nolock)
						left outer join KLSocr with (nolock) on KLSocr.KLSocr_id = KLArea.KLSocr_id
						where klarea.klarea_id=pa1.klrgn_id),'') else '' end+
						isnull((select top 1
						rtrim(KLSocr.KLSocr_Nick) + ' ' + rtrim(KLArea.KLArea_Name) + ', '
						from klarea with (nolock)
						left outer join KLSocr with (nolock) on KLSocr.KLSocr_id = KLArea.KLSocr_id
						where klarea.klarea_id=pa1.klsubrgn_id),'')+
						isnull((select top 1
						rtrim(KLSocr.KLSocr_Nick) + ' ' + rtrim(KLArea.KLArea_Name) + ', '
						from klarea with (nolock)
						left outer join KLSocr with (nolock) on KLSocr.KLSocr_id = KLArea.KLSocr_id
						where klarea.klarea_id=pa1.klcity_id),'')+
						isnull((select top 1
						rtrim(KLSocr.KLSocr_Nick) + ' ' + rtrim(KLArea.KLArea_Name) + ', '
						from klarea with (nolock)
						left outer join KLSocr with (nolock) on KLSocr.KLSocr_id = KLArea.KLSocr_id
						where klarea.klarea_id=pa1.kltown_id),'')+
						isnull((select top 1
						rtrim(KLSocr.KLSocr_Nick) + ' ' + rtrim(KLStreet.KLStreet_Name) + ', '
						from KlStreet with (nolock)
						left outer join KLSocr with (nolock) on KLSocr.KLSocr_id = KlStreet.KLSocr_id
						where KlStreet.KlStreet_id=pa1.KlStreet_id),'')+
						isnull('д '+RTrim(pa1.Address_House)+', ','')+
						isnull('кор '+RTrim(pa1.Address_Corpus)+', ','')+
						isnull('кв '+RTrim(pa1.Address_Flat),'')
				else
						case when pa.klcity_id != 3310 or pa.klcity_id is null then isnull((select top 1
						rtrim(KLSocr.KLSocr_Nick) + ' ' + rtrim(KLArea.KLArea_Name) + ', '
						from klarea with (nolock)
						left outer join KLSocr with (nolock) on KLSocr.KLSocr_id = KLArea.KLSocr_id
						where klarea.klarea_id=pa.klrgn_id),'') else '' end+
						isnull((select top 1
						rtrim(KLSocr.KLSocr_Nick) + ' ' + rtrim(KLArea.KLArea_Name) + ', '
						from klarea with (nolock)
						left outer join KLSocr with (nolock) on KLSocr.KLSocr_id = KLArea.KLSocr_id
						where klarea.klarea_id=pa.klsubrgn_id),'')+
						isnull((select top 1
						rtrim(KLSocr.KLSocr_Nick) + ' ' + rtrim(KLArea.KLArea_Name) + ', '
						from klarea with (nolock)
						left outer join KLSocr with (nolock) on KLSocr.KLSocr_id = KLArea.KLSocr_id
						where klarea.klarea_id=pa.klcity_id),'')+
						isnull((select top 1
						rtrim(KLSocr.KLSocr_Nick) + ' ' + rtrim(KLArea.KLArea_Name) + ', '
						from klarea with (nolock)
						left outer join KLSocr with (nolock) on KLSocr.KLSocr_id = KLArea.KLSocr_id
						where klarea.klarea_id=pa.kltown_id),'')+
						isnull((select top 1
						rtrim(KLSocr.KLSocr_Nick) + ' ' + rtrim(KLStreet.KLStreet_Name) + ', '
						from KlStreet with (nolock)
						left outer join KLSocr with (nolock) on KLSocr.KLSocr_id = KlStreet.KLSocr_id
						where KlStreet.KlStreet_id=pa.KlStreet_id),'')+
						isnull('д '+RTrim(pa.Address_House)+', ','')+
						isnull('кор '+RTrim(pa.Address_Corpus)+', ','')+
						isnull('кв '+RTrim(pa.Address_Flat),'')
				end
				as Person_Address,
				d.DirType_id,
				ES.EvnStatus_SysNick,
				(case when Job_nfData is null or Job_nfData = '' then rtrim(jn.Job_Name) else Job_nfData end) as Job_Name,
				rtrim(Post_Name) as Post_Name,
				rtrim(diag.Diag_Code) as Diag_Code,
				rtrim(diag.Diag_Name) as Diag_Name,
				d.EvnDirection_Descr,
				datepart(dd, d.EvnDirection_setDate) as Dir_Day,
				datepart(m, d.EvnDirection_setDate) as Dir_Month,
				datepart(yyyy, d.EvnDirection_setDate) as Dir_Year,
				case when pol.Polis_endDate is null OR d.EvnDirection_setDate < pol.Polis_endDate 
				then
				(o.OrgSmo_Nick) 
				else '' 
				end
				as OrgSmo_Nick,
				PersisPost.name as PostMed_Name,
				case
				when d.TimetableGraf_id is not null then convert(varchar(10),TimetableGraf_begTime, 104)+' '+convert(varchar(5),TimetableGraf_begTime, 108)
				when d.TimetableStac_id is not null then convert(varchar(10),TimetableStac_setDate, 104)
				when d.TimetablePar_id is not null then convert(varchar(10),TimetablePar_begTime, 104)+' '+convert(varchar(5),TimetablePar_begTime, 108)
				when ttms.TimetableMedService_id is not null then convert(varchar(10), ttms.TimetableMedService_begTime, 104)+' '+convert(varchar(5), ttms.TimetableMedService_begTime, 108)
				when ttr.TimetableResource_id is not null then convert(varchar(10), ttr.TimetableResource_begTime, 104)+' '+convert(varchar(5), ttr.TimetableResource_begTime, 108)
				end
				as RecDate,
				EP.EvnPrescr_IsCito,
				ISNULL(EQueue.EvnQueue_id,0) as EvnQueue_id,
				case when d.TimetableGraf_id is not null
						then msfmp.MedPersonal_FIO
				when d.TimetableStac_id is not null
						then ls.LpuSection_Name
				when d.TimetablePar_id is not null
						then ls1.LpuSection_Name
				/*when d.MedPersonal_did is not null
						then msf1.Person_FIO*/
				when d.LpuSection_did is not null
						then ls2.LpuSection_Name
				when d.TimetableMedService_id is not null
						then ls3.LpuSection_Name
				end as RecMP,
				ls22.LpuSection_Name as LpuSection_NameAstra,
				lsp2.LpuSectionProfile_Name as LpuSectionProfile_NameAstra,
				isnull(ctt.CauseTreatmentType_Name,ctt2.CauseTreatmentType_Name) as CauseTreatmentType_Name,
				lumsf.LpuUnit_Phone,
				lud.LpuUnit_Phone as Contact_Phone,
				COALESCE(ls2.LpuSection_Contacts, ls.LpuSection_Contacts, ls3.LpuSection_Contacts) as SectionContact_Phone,
				ld.Lpu_Phone as Lpu_Phone,
				d.TimetableGraf_id,
				d.TimetableStac_id,
				d.TimetablePar_id,
				lud.LpuUnit_Name as dLpuUnit_Name,
				d.MedPersonal_did,
				ISNULL(UC.UslugaComplex_Code,'') + ' ' + {$UslugaComplexNameField} as Usluga_Name,
				EPLD.EvnPrescrLabDiag_id,
				UC.UslugaComplex_id,
				d.MedService_id
			from EvnDirectionCTE d
			left join DirType dt with (nolock) on d.DirType_id = dt.DirType_id
			left join v_Person_ER p with (nolock) on p.Person_id = d.Person_id
			left join Polis pol with (nolock) on p.Polis_id = pol.Polis_id
			left join v_OrgSmo o with (nolock) on pol.OrgSmo_id = o.OrgSmo_id
			left join LpuSectionProfile lsp with (nolock) on lsp.LpuSectionProfile_id=d.LpuSectionProfile_id
			left join Diag with (nolock) on Diag.Diag_id = d.Diag_id
			left join v_EvnStatus ES (nolock) on ES.EvnStatus_id = d.EvnStatus_id
			outer apply (select top 1 mf.post_id from v_MedStaffFact mf with(nolock) where d.medstafffact_id= mf.medstafffact_id  )MSFf
			left join persis.v_Post PersisPost with (nolock) on COALESCE(MSFf.post_id,d.Post_id) = PersisPost.id
			outer apply(
				select top 1
					msf.MedPersonal_FIO as Person_FIO,
					msf.LpuUnit_id,
					msf.MedDol_id,
					msf.medpersonal_init,
					msf.Person_Snils
				from v_Medstafffact_ER msf with (nolock)
				where
					msf.medpersonal_id = d.Medpersonal_id
					and msf.Lpu_id = d.Lpu_id
			) msf
			outer apply(
				select top 1
					zav.MedPersonal_FIO as Person_FIO,
					msf.LpuUnit_id,
					medpersonal_init
				from v_Medstafffact_ER zav with (nolock)
				where
					zav.medpersonal_id = d.Medpersonal_zid
					and zav.Lpu_id = d.Lpu_id
			) zav
			outer apply(
				select top 1 EQ.EvnQueue_id
				from v_EvnQueue EQ
				where EQ.EvnDirection_id = d.EvnDirection_id
			) EQueue
			left join v_Lpu l with (nolock) on l.Lpu_id=d.Lpu_id
			left join v_Lpu ld with (nolock) on ld.Lpu_id=d.Lpu_did
			left join Org od with (nolock) on od.Org_id = d.Org_oid
			left join v_LpuUnit_ER lud with (nolock) on lud.LpuUnit_id=d.LpuUnit_did
			left join v_LpuUnit_ER lumsf with (nolock) on msf.LpuUnit_id=lumsf.LpuUnit_id
			left join Address a with (nolock) on l.UAddress_id = a.Address_id
			left join Address lua with (nolock) on lud.Address_id = lua.Address_id
			left join Address pa with (nolock) on pa.Address_id = p.UAddress_id
			left join Address pa1 with (nolock) on pa1.Address_id = p.PAddress_id
			left join Job j with (nolock) on j.Job_id = p.Job_id
			left join v_Job_ER jn with (nolock) on jn.Job_id = p.Job_id
			left join Post with (nolock) on post.Post_id = j.Post_id
			left join v_TimeTableGraf_lite ttg with (nolock) on d.EvnDirection_id = ttg.EvnDirection_id and ttg.Person_id is not null
			left join v_medstafffact_er msfmp with (nolock) on ttg.MedStaffFact_id = msfmp.MedStaffFact_id
			left join v_TimetableStac_lite tts with (nolock) on d.EvnDirection_id = tts.EvnDirection_id and tts.Person_id is not null
			left join v_LpuSection ls with (nolock) on tts.LpuSection_id = ls.LpuSection_id
			left join v_TimetablePar ttp with (nolock) on d.TimetablePar_id = ttp.TimetablePar_id and ttp.Person_id is not null
			left join v_LpuSection ls1 with (nolock) on ttp.LpuSection_id = ls1.LpuSection_id
			left join v_TimetableMedService_lite ttms with (nolock) on d.EvnDirection_id = ttms.EvnDirection_id and ttms.Person_id is not null
			left join v_EvnPrescrVK epvk with (nolock) on epvk.TimetableMedService_id = ttms.TimetableMedService_id
			left join v_CauseTreatmentType ctt with (nolock) on ctt.CauseTreatmentType_id = epvk.CauseTreatmentType_id
			left join v_EvnPrescrVK epvk2 with (nolock) on epvk2.PersonEvn_id = d.PersonEvn_id and epvk2.Diag_id = d.Diag_id
			left join v_CauseTreatmentType ctt2 with (nolock) on ctt2.CauseTreatmentType_id = epvk2.CauseTreatmentType_id
			left join v_LpuSection ls2 with(nolock) on ls2.LpuSection_id = d.LpuSection_did
			left join v_LpuSection ls22 with(nolock) on ls22.LpuSection_id = d.LpuSection_id
			left join v_LpuSectionProfile lsp2 with(nolock) on lsp2.LpuSectionProfile_id = ls22.LpuSectionProfile_id
			left join v_MedService MS3 with (nolock) on MS3.MedService_id = ttms.MedService_id
			left join v_LpuSection ls3 with(nolock) on ls3.LpuSection_id = MS3.LpuSection_id
			left join LpuSectionProfile lsp3 with (nolock) on lsp3.LpuSectionProfile_id=ls3.LpuSectionProfile_id
			left outer join v_TimetableResource_lite ttr with (nolock) on ttr.EvnDirection_id = d.EvnDirection_id and ttr.Person_id is not null
			left join v_EvnPrescrDirection EPD with(nolock) on EPD.EvnDirection_id = d.EvnDirection_id
			left join v_EvnPrescr EP with (nolock) on EPD.EvnPrescr_id = EP.EvnPrescr_id
			left join EvnPrescrLabDiag EPLD with(nolock) on EPLD.EvnPrescr_id = EPD.EvnPrescr_id
			left join EvnPrescrFuncDiag EPFD with (nolock) on EPFD.EvnPrescr_id = EPD.EvnPrescr_id
			left join EvnPrescrFuncDiagUsluga EPFDU with (nolock) on EPFDU.EvnPrescrFuncDiag_id = EPFD.EvnPrescrFuncDiag_id
			left join EvnDirectionUslugaComplex EDUC with (nolock) on EDUC.EvnDirection_id = d.EvnDirection_id
			left join v_UslugaComplex UC with(nolock) on UC.UslugaComplex_id = coalesce(EPLD.UslugaComplex_id,EPFDU.UslugaComplex_id,educ.UslugaComplex_id)
			left join v_UslugaComplexMedService UCMS with(nolock) on UCMS.MedService_id = d.MedService_id and UCMS.UslugaComplex_id = UC.UslugaComplex_id
			left join fed.v_MedicalCareFormType fedMCFT with(nolock) on fedMCFT.MedicalCareFormType_id = d.MedicalCareFormType_id
			
			where d.EvnDirection_id = :EvnDirection_id
		";

		$result = $this->db->query($query, $data);

		if (is_object($result)) {

			$result = $result->result('array');
			$lab_services = array();
			$lab_services_ids = array();
			$medService_id = null;

			foreach ($result as $value) {

				if ($medService_id == null)
				{
					$medService_id = $value['MedService_id'];
				}

				if (!in_array($value['Usluga_Name'] ,$lab_services)) {

					if (isset($value['EvnPrescrLabDiag_id']))
						$lab_services[$value['EvnPrescrLabDiag_id']] = $value['Usluga_Name'];
					else
						$lab_services[] = $value['Usluga_Name'];
				}

				if (isset($value['EvnPrescrLabDiag_id'])) {
					if (!in_array($value['EvnPrescrLabDiag_id'], $lab_services_ids)) {

						$lab_services_ids[] = $value['EvnPrescrLabDiag_id'];
					}
				}
			}

			$result[0]['Usluga_Name'] = $lab_services;

			//получаем набор назначенных услуг из комплексной услуги
			if (count($lab_services_ids) > 0)
			{
				$mnemonikaJoin = null;
				$mnemonikaSelect = null;
				$mnemonikaOuterApply = '';

				// Дополнительный запрос, чтобы достать мнемонику тестов. Но только если в направлении указана мед служба для исследований MedService_id
				if ($medService_id !== null)
				{
					$mnemonikaSelect = ', analyzertest.AnalyzerTest_SysNick as AnalyzerTest_SysNick';
					$mnemonikaOuterApply = 'outer apply (
						select top 1
							at.AnalyzerTest_SysNick as AnalyzerTest_SysNick
						from
							v_UslugaComplexMedService UCMS with (nolock)
							inner join lis.v_AnalyzerTest at (nolock) on at.UslugaComplexMedService_id = UCMS.UslugaComplexMedService_id
							inner join lis.v_Analyzer a (nolock) on a.Analyzer_id = at.Analyzer_id
						where
							UCMS.UslugaComplex_id = UC.UslugaComplex_id
							and UCMS.MedService_id = :MedService_id
							and ISNULL(at.AnalyzerTest_IsNotActive, 1) = 1
							and ISNULL(a.Analyzer_IsNotActive, 1) = 1
							and (at.AnalyzerTest_endDT >= @curdate or at.AnalyzerTest_endDT is null)
					) analyzertest';
				}

				// Достаем все тесты, которые проводятся в рамках назначенных исследований. Если регион карелия и указаны MedService_id, то достаем с мнемониками
				$query = "
					declare @curdate datetime = dbo.tzGetDate();
					
					select
						UC.UslugaComplex_id,
						EPLDU.EvnPrescrLabDiag_id,
						ISNULL(UC.UslugaComplex_Code,'') as Usluga_Code,
						ISNULL(UC.UslugaComplex_Name,'') as Usluga_Name
						{$mnemonikaSelect}
					from
						v_EvnPrescrLabDiagUsluga EPLDU (nolock)
						left join v_UslugaComplex UC with(nolock) on UC.UslugaComplex_id = EPLDU.UslugaComplex_id
						{$mnemonikaOuterApply}
					where
						EPLDU.EvnPrescrLabDiag_id IN (
							".implode(',', $lab_services_ids)."
						)
				";

				$sub_services = $this->db->query($query, array('MedService_id' => $medService_id));

				if (is_object($sub_services)) {

					$sub_services = $sub_services->result('array');
					$grouped_subsvcs = array();

					foreach ($sub_services as $svc) {

						$grouped_subsvcs[$svc['EvnPrescrLabDiag_id']][] = $svc;
					}

					$result[0]['SubServices'] = $grouped_subsvcs;
				}
			}

			return $result[0];
		}
		else {
			return false;
		}
	}

	/**
	 * Получение типа назначения
	 */
	function getPrescriptionTypeByEvnDirection($data) {
		return $this->getFirstResultFromQuery("
			select top 1
				ep.PrescriptionType_id
			from
				v_EvnPrescrDirection epd (nolock)
				inner join v_EvnPrescr ep (nolock) on ep.EvnPrescr_id = epd.EvnPrescr_id
			where
				epd.EvnDirection_id = :EvnDirection_id
		", array(
			'EvnDirection_id' => $data['EvnDirection_id']
		), true);
	}

	/*
	 * генерация кода брони
	 */
	function makeEvnDirectionTalonCode($data){

		$code = null;
		if (!empty($data['TimetableGraf_id'])) {

			$ElectronicQueueInfo_id = $this->getFirstResultFromQuery("
				select top 1
					EQI.ElectronicQueueInfo_id
				from
					v_TimeTableGraf_lite ttg (nolock)
					inner join v_MedServiceElectronicQueue MSEQ (nolock) on MSEQ.MedStaffFact_id = ttg.MedStaffFact_id
					inner join v_ElectronicService ES (nolock) on ES.ElectronicService_id = MSEQ.ElectronicService_id
					inner join v_ElectronicQueueInfo EQI (nolock) on EQI.ElectronicQueueInfo_id = ES.ElectronicQueueInfo_id
				where
					ttg.TimetableGraf_id = :TimetableGraf_id
					and EQI.ElectronicQueueInfo_IsOff = 1
			", $data, true);

			if ($ElectronicQueueInfo_id === false) {
				return $this->createError('', 'Ошибка при проверке наличия электронной очереди');
			}

			if (!empty($ElectronicQueueInfo_id)) {
				$code = $this->genEvnDirectionTalonCodeGraf($data);
				if (empty($code)) {
					return $this->createError('', 'Ошибка при формирования кода бронирования записи к врачу');
				}
			}

		} else if (!empty($data['TimetableMedService_id'])) {

			$eqInfo = $this->queryResult("
			select top 1
				eqi.ElectronicQueueInfo_id,
				MSEQ.MedServiceElectronicQueue_id,
				mst.MedServiceType_SysNick,
				eqi.MedService_id
			from
				v_TimetableMedService_lite ttms (nolock)
				left join v_MedServiceElectronicQueue MSEQ (nolock) on MSEQ.UslugaComplexMedService_id = ttms.UslugaComplexMedService_id
				left join v_UslugaComplexMedService ucms (nolock) on ucms.UslugaComplexMedService_id = ttms.UslugaComplexMedService_id
				left join v_MedService ms (nolock) on (ms.MedService_id = ttms.MedService_id or ms.MedService_id = ucms.MedService_id)
				left join v_ElectronicQueueInfo eqi (nolock) on (eqi.MedService_id = ttms.MedService_id or eqi.MedService_id = ucms.MedService_id)
				left join v_MedServiceType mst (nolock) on mst.MedServiceType_id = ms.MedServiceType_id
			where
				ttms.TimetableMedService_id = :TimetableMedService_id
				and eqi.ElectronicQueueInfo_IsOff = 1
		", $data);

			if (!empty($eqInfo[0]['ElectronicQueueInfo_id']) && (!empty($eqInfo[0]['MedServiceElectronicQueue_id']) || !empty($eqInfo[0]['MedService_id']))) {

				if (!empty($eqInfo[0]['MedServiceElectronicQueue_id']) && $eqInfo[0]['MedServiceType_SysNick'] == 'medosv') {
					$code = $this->genEvnDirectionTalonCode($data);
				} else {
					$code = $this->genEvnDirectionTalonCodeMedService($data);
				}

				if (empty($code)) {
					return $this->createError('', 'Ошибка при формировании кода бронирования записи на услугу');
				}
			}
		} else if (!empty($data['TimetableResource_id'])) {

			$eq = $this->getFirstResultFromQuery("
				select top 1
					MSEQ.MedServiceElectronicQueue_id
				from
					v_TimetableResource_lite ttr (nolock)
					inner join v_MedServiceElectronicQueue MSEQ (nolock) on MSEQ.Resource_id = ttr.Resource_id
					inner join v_Resource r (nolock) on r.Resource_id = ttr.Resource_id
					inner join v_ElectronicQueueInfo eqi (nolock) on eqi.MedService_id = r.MedService_id
				where
					ttr.TimetableResource_id = :TimetableResource_id
					and eqi.ElectronicQueueInfo_IsOff = 1
			", $data, true);

			if (!empty($eq)) {
				$code = $this->genEvnDirectionTalonCodeResource($data);

				if (empty($code)) {
					return $this->createError('', 'Ошибка при формировании кода бронирования записи на ресурс');
				}
			}
		}

		return $code;
	}
	/**
	 * Проверка возможности объединения услуг в одно направление
	 */
	function getUslugaWithoutDirectoryList($data) {
		if(empty($data['Evn_id']) || empty($data['EvnPrescr_id']))
			return false;

		$uslugaFilter = '';
		$MedServiceType = $this->getFirstRowFromQuery("
			SELECT
				mst.MedServiceType_SysNick
			FROM 
				v_MedService ms with(nolock)
				INNER JOIN v_MedServiceType mst with(nolock) ON mst.MedServiceType_id = ms.MedServiceType_id
			WHERE
				MedService_id = :MedService_id
		", $data);
		if(empty($MedServiceType)) return false;

		$MedServiceType_SysNick = $MedServiceType['MedServiceType_SysNick'];
		switch ($MedServiceType_SysNick) {
			case 'lab':
				//Для лаборатории нужен список всех оказываемых услуг, для включения в направление таких же из посещения
				$sql = "
					SELECT distinct
						ucms.UslugaComplex_id
					from
						v_UslugaComplexMedService ucms with(nolock) -- услуга на службе
						inner join v_UslugaComplex uc with(nolock) on uc.UslugaComplex_id = ucms.UslugaComplex_id -- комплексная услуга (услуга МО или ГОСТ)
						inner join v_UslugaComplex uc11 with(nolock) on uc11.UslugaComplex_id = uc.UslugaComplex_2011id -- комплексная услуга ( ГОСТ)
					where
						ucms.MedService_id = :MedService_id
				";
				$uslugaIDs = $this->queryResult($sql , $data);
				if(!empty($uslugaIDs) && count($uslugaIDs) > 0){
					$IDs = array();
					foreach($uslugaIDs as $usl)
						$IDs[] = $usl['UslugaComplex_id'];
					$uslugaFilter = " AND EPLD.UslugaComplex_id IN (".implode(",", $IDs).") ";
				}
				break;
			case 'pzm':
				//Для пункта забора Нужно проверить на способы забора оказываемые пунктом (наличие услуг)
				$sql = "
					SELECT distinct
						UC11.UslugaComplex_Code
					from
						v_UslugaComplexMedService ucms with(nolock) -- услуга на службе
						inner join v_UslugaComplex uc with(nolock) on uc.UslugaComplex_id = ucms.UslugaCOmplex_id -- комплексная услуга (услуга МО или ГОСТ)
						inner join v_UslugaComplex uc11 with(nolock) on uc11.UslugaComplex_id = uc.UslugaCOmplex_2011id -- комплексная услуга ( ГОСТ)
					where
						ucms.MedService_id = :MedService_id
						AND uc11.UslugaComplex_Code in ('A11.05.001', 'A11.12.009', 'A11.16.005')
				";
				$uslugaIDs = $this->queryResult($sql , $data);
				if(!empty($uslugaIDs) && count($uslugaIDs) > 0){
					$IDs = array();
					foreach($uslugaIDs as $usl)
						$IDs[] = "'".$usl['UslugaComplex_Code']."'";
					$uslugaFilter = " AND exists (
						SELECT top 1
							st.SamplingType_Code
						FROM
							dbo.v_UslugaComplex uc with(nolock)
							inner join v_UslugaComplex uc11 with(nolock) on uc11.UslugaComplex_id = uc.UslugaComplex_2011id 
							left JOIN UslugaComplexAttribute ua with(nolock) ON ua.UslugaComplex_id = uc.UslugaComplex_id
							left JOIN UslugaComplexAttribute ua2 with(nolock) ON ua2.UslugaComplex_id = uc.UslugaComplex_2011id
							LEFT JOIN SamplingType st with(nolock) ON (st.SamplingType_id = ua.UslugaComplexAttribute_DBTableID OR st.SamplingType_id = ua2.UslugaComplexAttribute_DBTableID)
						WHERE
							uc.UslugaComplex_id  = EPLD.UslugaComplex_id
							AND (ua.UslugaComplexAttributeType_id = 129 OR ua2.UslugaComplexAttributeType_id = 129)
							AND st.SamplingType_Code IN (".implode(",", $IDs).")
					)";
				}
				break;
			default:
		}

		$params = array(
			'Evn_id' => $data['Evn_id'],
			'EvnPrescr_id' => $data['EvnPrescr_id'],
		);
		// Запрос на все услуги лабораторной диагн. в данном посещении
		$sql = "
			declare @curdate datetime = dbo.tzGetDate();
			
			select 
				EPLD.UslugaComplex_id,
				UC.UslugaComplex_Name,
				ucms.UslugaComplexMedService_id as UslugaComplexMedService_pid,
				CAST(etr.checked as varchar(max)) as checked,
				EP.MedService_id,
				EP.EvnPrescr_id
			from v_EvnPrescr EP with (nolock)
				inner join EvnPrescrLabDiag EPLD with (nolock) on EPLD.EvnPrescrLabDiag_id = EP.EvnPrescr_id
				left join v_UslugaComplex UC with (nolock) on UC.UslugaComplex_id = EPLD.UslugaComplex_id
				left JOIN v_UslugaComplexMedService ucms WITH (NOLOCK) on ucms.UslugaComplex_id = EPLD.UslugaComplex_id AND ucms.MedService_id = EP.MedService_id
					 AND ucms.UslugaComplexMedService_pid IS NULL
                     and ucms.UslugaComplexMedService_begDT <= @curdate
					and (ucms.UslugaComplexMedService_endDT is null or ucms.UslugaComplexMedService_endDT > @curdate)
					outer apply (
					Select (
						select
							ISNULL(CAST(UC.UslugaComplex_id as VARCHAR),'') + ',' as 'data()' 
						from v_UslugaComplexMedService ucmsTemp with (nolock)
						inner join v_UslugaComplex UC with (nolock) on ucmsTemp.UslugaComplex_id = UC.UslugaComplex_id
						cross apply(
							select top 1
								at_child.AnalyzerTest_SortCode,
								at_child.AnalyzerTest_id,
								ISNULL(at_child.AnalyzerTest_SysNick, uc.UslugaComplex_Name) as AnalyzerTest_SysNick
							from
								lis.v_AnalyzerTest at_child (nolock)
								inner join lis.v_AnalyzerTest at (nolock) on at.AnalyzerTest_id = at_child.AnalyzerTest_pid
								inner join lis.v_Analyzer a (nolock) on a.Analyzer_id = at.Analyzer_id
								left join v_UslugaComplex uc (nolock) on uc.UslugaComplex_id = at_child.UslugaComplex_id
							where
								at_child.UslugaComplexMedService_id = ucmsTemp.UslugaComplexMedService_id
								and at.UslugaComplexMedService_id = ucmsTemp.UslugaComplexMedService_pid
								and ISNULL(at_child.AnalyzerTest_IsNotActive, 1) = 1
								and ISNULL(at.AnalyzerTest_IsNotActive, 1) = 1
								and ISNULL(a.Analyzer_IsNotActive, 1) = 1
								and (at_child.AnalyzerTest_endDT >= dbo.tzGetDate() or at_child.AnalyzerTest_endDT is null)
								and (uc.UslugaComplex_endDT >= dbo.tzGetDate() or uc.UslugaComplex_endDT is null)
						) ATEST -- фильтрация услуг по активности тестов связанных с ними
						where ucmsTemp.UslugaComplexMedService_pid = ucms.UslugaComplexMedService_id
						order by ISNULL(ATEST.AnalyzerTest_SortCode, 999999999)
					for xml path(''), TYPE
					) as checked
				) etr
			where
				EP.EvnPrescr_pid  = :Evn_id
				and EP.EvnPrescr_id != :EvnPrescr_id
				and EP.PrescriptionType_id = 11
				and EP.PrescriptionStatusType_id != 3
				and not exists (
					Select top 1 epd.EvnDirection_id
					from v_EvnPrescrDirection epd with (nolock)
					--inner join v_EvnDirection_all ED with (nolock) on epd.EvnDirection_id = ED.EvnDirection_id
					where epd.EvnPrescr_id = EP.EvnPrescr_id
					--and  ISNULL(ED.EvnStatus_id, 16) not in (12,13)
				)
				{$uslugaFilter}
		";
		//echo getDebugSQL($sql, $params);die();
		$res = $this->queryResult($sql, $params);

		if (empty($res[0])){
			return false;
		}
		return $res;
	}
	/**
	 * Включение назначения в существующее направление
	 */
	function includeToDirection($data) {
		if(!empty($data['UslugaList'])){
			$uslugaList = json_decode($data['UslugaList'],true);
			if(!empty($uslugaList) && is_array($uslugaList)){
				$msg = '';
				foreach($uslugaList as $usluga){
					$msg .= $usluga['UslugaComplex_Name'].'<br>';

					$params = array(
						'EvnPrescr_id' => $usluga['EvnPrescr_id'],
						'EvnDirection_id' => $data['EvnDirection_id'],
						'UslugaComplex_id' => $usluga['UslugaComplex_id'],
						'checked' => !empty($usluga['checked'])?(trim($usluga['checked'],',')):'',
						'pmUser_id' => $data['pmUser_id'],
						'Lpu_id' => $data['Lpu_id']
					);

					$resp = $this->includeEvnPrescrInDirection($params);

					if (!$this->isSuccessful($resp)) {
						return $resp;
					}
				}
				$msg .= " были объединены в одно направление";
				if(!empty($msg)) $response[0]['addingMsg'] = $msg;

				return ['success' => true, 'addingMsg' => $msg];
			}
		} else {
			/*if (!empty($data['EvnDirection_id'])) {
				// Нашли доступное для записи направление
				// Найдем всё, что можно в него записать
				$UslugaList = $this->getUslugaWithoutDirectoryList($data);
				// возвращаем на форму направление в которое можно включить назначение
				// возвращаем на форму все назначения, доступные для включения в направление
			}*/
			return $this->includeEvnPrescrInDirection($data);
		}
		return false;
	}
}