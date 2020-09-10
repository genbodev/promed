<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 *
 * @package		PromedWeb
 * @access		public
 * @copyright	Copyright (c) 2013 Swan Ltd.
 * @link		http://swan.perm.ru/PromedWeb
 * @version		09.2013
 */
require_once('EvnPrescrAbstract_model.php');
/**
 * Модель назначения "Функциональная диагностика"
 *
 * Назначения с типом "Функциональная диагностика" хранятся в таблицах EvnPrescrFuncDiag, EvnPrescrFuncDiagUsluga.
 * В назначении должна быть указана одна услуга или более.
 * Для каждой выбранной услуги создается запись в таблице EvnPrescrFuncDiagUsluga
 *
 * @package		EvnPrescr
 * @author		Александр Пермяков
 *
 */
class EvnPrescrFuncDiag_model extends EvnPrescrAbstract_model
{
	/**
	 * Конструктор
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Определение идентификатора типа назначения
	 * @return int
	 */
	public function getPrescriptionTypeId() {
		return 12;
	}

	/**
	 * Определение имени таблицы с данными назначения
	 * @return string
	 */
	public function getTableName() {
		return 'EvnPrescrFuncDiag';
	}

	/**
	 * Определение правил для входящих параметров
	 * @param string $scenario
	 * @return array
	 */
	public function getInputRules($scenario) {
		$rules = array();
		switch ($scenario) {
			case 'doSave':
				$rules = array(
					array('field' => 'parentEvnClass_SysNick','label' => 'Системное имя род.события','rules' => '','type' => 'string'),
					array('field' => 'signature','label' => 'Признак для подписания','rules' => '','type' => 'int'),
					array('field' => 'EvnPrescrFuncDiag_id','label' => 'Идентификатор назначения','rules' => '','type' => 'id'),
					array('field' => 'EvnPrescrFuncDiag_pid','label' => 'Идентификатор род.события','rules' => 'required','type' => 'id'),
					array('field' => 'MedService_id','label' => 'Служба','rules' => '','type' => 'id'),
					array('field' => 'EvnPrescrFuncDiag_uslugaList','label' => 'Выбранные услуги','rules' => 'required','type' => 'string'),
					array('field' => 'EvnPrescrFuncDiag_setDate','label' => 'Плановая дата','rules' => '','type' => 'date'),
					array('field' => 'EvnPrescrFuncDiag_IsCito','label' => 'Cito','rules' => '','type' => 'string'),
					array('field' => 'EvnPrescrFuncDiag_Descr','label' => 'Комментарий','rules' => 'trim','type' => 'string'),
					array('field' => 'PersonEvn_id','label' => 'Идентификатор','rules' => 'required','type' => 'id'),
					array('field' => 'Server_id','label' => 'Идентификатор сервера','rules' => 'required','type' => 'int'),
					array('field' => 'StudyTarget_id','label' => 'Цель исследования','rules' => 'required','type' => 'id'),
					array('field' => 'FSIDI_id','label' => 'Инструментальная диагностика','rules' => '','type' => 'id'),
					array('field' => 'Resource_id', 'label' => 'Тип ресурса', 'rules' => '', 'type' => 'string'),
					array('field' => 'StudyTargetPayloadData','label' => 'Доп. параметры исследования','rules' => '','type' => 'string')
				);
				break;
			case 'doLoad':
				$rules[] = array(
					'field' => 'EvnPrescrFuncDiag_id',
					'label' => 'Идентификатор назначения',
					'rules' => 'required',
					'type' =>  'id'
				);
				break;
		}
		return $rules;
	}

	/**
	 * Сохранение назначения
	 * @param array $data Массив, полученный методом ProcessInputData контроллера
	 * @return array|boolean
	 */
	public function doSave($data = array(), $isAllowTransaction = true){

		if (empty($data['EvnPrescrFuncDiag_id'])) {

			$action = 'ins';

			$data['EvnPrescrFuncDiag_id'] = NULL;
			$data['PrescriptionStatusType_id'] = 1;

		} else {

			$action = 'upd';

			try {
				$o_data = $this->getAllData($data['EvnPrescrFuncDiag_id']);
			} catch (Exception $e) {
				return array(array('Error_Msg' => $e->getMessage()));
			}

			foreach($o_data as $k => $v) {

				if (!array_key_exists($k, $data)) {
					$data[$k] = $v;
				}
			}
		}

		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);

			set @Res = :EvnPrescrFuncDiag_id;

			exec p_EvnPrescrFuncDiag_" . $action . "
				@EvnPrescrFuncDiag_id = @Res output,
				@EvnPrescrFuncDiag_pid = :EvnPrescrFuncDiag_pid,
				@PrescriptionType_id = :PrescriptionType_id,
				@PrescriptionStatusType_id = :PrescriptionStatusType_id,
				@Lpu_id = :Lpu_id,
				@Server_id = :Server_id,
				@PersonEvn_id = :PersonEvn_id,
				@MedService_id = :MedService_id,
				@EvnPrescrFuncDiag_setDT = :EvnPrescrFuncDiag_setDT,
				@EvnPrescrFuncDiag_IsCito = :EvnPrescrFuncDiag_IsCito,
				@EvnPrescrFuncDiag_Descr = :EvnPrescrFuncDiag_Descr,
				@StudyTarget_id = :StudyTarget_id,
				@Resource_id = :Resource_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @Res as EvnPrescrFuncDiag_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$data['EvnPrescrFuncDiag_setDT'] = NULL;

		if ( !empty($data['EvnPrescrFuncDiag_setDate']) ) {
			$data['EvnPrescrFuncDiag_setDT'] = $data['EvnPrescrFuncDiag_setDate'];
		}

		$data['EvnPrescrFuncDiag_IsCito'] = (empty($data['EvnPrescrFuncDiag_IsCito']) || $data['EvnPrescrFuncDiag_IsCito'] != 'on')? 1 : 2;
		$data['PrescriptionType_id'] = $this->getPrescriptionTypeId();

		if (!isset($data['DopDispInfoConsent_id'])) { $data['DopDispInfoConsent_id'] = null; }
		if (!isset($data['Resource_id'])) { $data['Resource_id'] = null; }

		$db_query = $this->db->query($query, $data);

		if (is_object($db_query)) {

			$result = $db_query->result('array');

			if (!empty($result)
				&& !empty($result[0])
				&& !empty($result[0]['EvnPrescrFuncDiag_id'])
				&& empty($result[0]['Error_Msg'])
			) {

				// если выполнено сохранение формы "Назначение ФД: редактирование", сохраняем\обновляем зубы
				if ($action == 'upd' && !empty($data['parentEvnClass_SysNick'])
					&& $data['parentEvnClass_SysNick'] == 'EvnVizitPLStom') {

					$this->load->model('EvnUsluga_model', 'eumodel');

					$this->eumodel->saveStudyTargetPayloadData(array(
						'pmUser_id' => $data['pmUser_id'],
						'EvnPrescrFuncDiag_id' => $data['EvnPrescrFuncDiag_id'],
						'StudyTargetPayloadData' => (!empty($data['StudyTargetPayloadData']) ? json_decode(toUTF($data['StudyTargetPayloadData']), true) : null)
					));
				}

				$uslugalist = array();
				if (!empty($data['EvnPrescrFuncDiag_uslugaList'])) {

					$uslugalist = explode(',', $data['EvnPrescrFuncDiag_uslugaList']);

					if (empty($uslugalist) || !is_numeric ($uslugalist[0])) {
						$result[0]['Error_Msg'] = 'Ошибка формата списка услуг';
					} else {

						$res = $this->clearEvnPrescrFuncDiagUsluga(
							array('EvnPrescrFuncDiag_id' => $result[0]['EvnPrescrFuncDiag_id'])
						);

						if (empty($res)) { $result[0]['Error_Msg'] = 'Ошибка запроса списка выбранных услуг'; }

						if (!empty($res) && !empty($res[0]) && !empty($res[0]['Error_Msg'])) {
							$result[0]['Error_Msg'] = $res[0]['Error_Msg'];
						}
					}
				}

				if (empty($result[0]['Error_Msg']) && !empty($uslugalist)) {

					foreach($uslugalist as $d) {

						$res = $this->saveEvnPrescrFuncDiagUsluga(
							array(
								'UslugaComplex_id' => $d,
								'EvnPrescrFuncDiag_id' => $result[0]['EvnPrescrFuncDiag_id'],
								'FSIDI_id' => $data['FSIDI_id'] ?? null,
								'pmUser_id' => $data['pmUser_id']
							)
						);

						if (empty($res)) {
							$result[0]['Error_Msg'] = 'Ошибка запроса при сохранении услуги';
							break;
						}

						if (!empty($res) && !empty($res[0]) && !empty($res[0]['Error_Msg'])) {
							$result[0]['Error_Msg'] = $res[0]['Error_Msg'];
							break;
						}
					}

					if (empty($result[0]['Error_Msg'])) {

						/* в форме добавления назначения пока нет редактирования, поэтому пока не надо
                            EvnXml.EvnXml_id as EvnXmlDir_id,
                            left join EvnXml with (nolock) on EvnXml.XmlType_id = XmlType.XmlType_id and EvnXml.Evn_id = :EvnDirection_id
                            'EvnDirection_id' => null,
                        */

						$res = $this->getFirstRowFromQuery("
							select top 1
								XmlType.XmlType_id as EvnXmlDirType_id
							from XmlType with (nolock)
							where XmlType.XmlType_id = case
							when exists (
								select top 1 uca.UslugaComplexAttribute_id
								from UslugaComplexAttribute uca (nolock)
								inner join UslugaComplexAttributeType ucat (nolock) on ucat.UslugaComplexAttributeType_id = uca.UslugaComplexAttributeType_id
								and ucat.UslugaComplexAttributeType_SysNick like 'kt'
								where uca.UslugaComplex_id = :UslugaComplex_id
							) then 19
							when exists (
								select top 1 uca.UslugaComplexAttribute_id
								from UslugaComplexAttribute uca (nolock)
								inner join UslugaComplexAttributeType ucat (nolock) on ucat.UslugaComplexAttributeType_id = uca.UslugaComplexAttributeType_id
								and ucat.UslugaComplexAttributeType_SysNick like 'mrt'
								where uca.UslugaComplex_id = :UslugaComplex_id
							) then 18
							else 2 end
						", array('UslugaComplex_id' => $uslugalist[0]));

						if (!empty($res) && is_array($res)) {
							$result[0]['EvnXmlDir_id'] = null;
							$result[0]['EvnXmlDirType_id'] = $res['EvnXmlDirType_id'];
						}
					}
				}

				return $result;

			} else return false;
		} else return false;
	}
	/**
	 *  метод очистки списка услуг
	 */
	function clearEvnPrescrFuncDiagUsluga($data) {
		return $this->clearEvnPrescrTable(array(
			'object'=>'EvnPrescrFuncDiagUsluga'
			,'fk_pid'=>'EvnPrescrFuncDiag_id'
			,'pid'=>$data['EvnPrescrFuncDiag_id']
		));
	}
	/**
     * Сохранение назнач
     */
	function saveEvnPrescrFuncDiagUsluga($data) {
		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);

			set @Res = :EvnPrescrFuncDiagUsluga_id;

			exec p_EvnPrescrFuncDiagUsluga_" . (!empty($data['EvnPrescrFuncDiagUsluga_id']) ? "upd" : "ins") . "
				@EvnPrescrFuncDiagUsluga_id = @Res output,
				@EvnPrescrFuncDiag_id = :EvnPrescrFuncDiag_id,
				@UslugaComplex_id = :UslugaComplex_id,
				@FSIDI_id = :FSIDI_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @Res as EvnPrescrFuncDiagUsluga_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$queryParams = array(
			'EvnPrescrFuncDiagUsluga_id' => (empty($data['EvnPrescrFuncDiagUsluga_id'])? NULL : $data['EvnPrescrFuncDiagUsluga_id'] ),
			'EvnPrescrFuncDiag_id' => $data['EvnPrescrFuncDiag_id'],
			'UslugaComplex_id' => $data['UslugaComplex_id'],
			'FSIDI_id' => $data['FSIDI_id'] ?? null,
			'pmUser_id' => $data['pmUser_id']
		);

		// echo getDebugSQL($query, $queryParams); exit();

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}
	/**
	 * Получение данных для формы редактирования
	 * @param array $data Массив, полученный методом ProcessInputData контроллера
	 * @return array|boolean
	 */
	public function doLoad($data) {
		$query = "
			select
				case when EP.PrescriptionStatusType_id = 1 then 'edit' else 'view' end as accessType,
				EP.EvnPrescrFuncDiag_id,
				EP.EvnPrescrFuncDiag_pid,
				EPU.UslugaComplex_id,
			    EPU.FSIDI_id,
				ED.EvnDirection_id,
				convert(varchar(10), EP.EvnPrescrFuncDiag_setDT, 104) as EvnPrescrFuncDiag_setDate,
				case when isnull(EP.EvnPrescrFuncDiag_IsCito,1) = 1 then 'off' else 'on' end as EvnPrescrFuncDiag_IsCito,
				EP.EvnPrescrFuncDiag_Descr,
				EP.Person_id,
				EP.PersonEvn_id,
				EP.Server_id,
				isnull(EP.StudyTarget_id,ED.StudyTarget_id) as StudyTarget_id,
				tth.ToothNums
			from 
				v_EvnPrescrFuncDiag EP with (nolock)
				inner join v_EvnPrescrFuncDiagUsluga EPU with (nolock) on EP.EvnPrescrFuncDiag_id = EPU.EvnPrescrFuncDiag_id
				outer apply (
					Select top 1 ED.EvnDirection_id, ED.StudyTarget_id
					from v_EvnPrescrDirection epd with (nolock)
					inner join v_EvnDirection_all ED with (nolock) on epd.EvnDirection_id = ED.EvnDirection_id
						and ED.EvnDirection_failDT is null
						and ED.EvnStatus_id not in (12,13)
					where epd.EvnPrescr_id = EP.EvnPrescrFuncDiag_id
					order by epd.EvnPrescrDirection_insDT desc
				) ED
				outer apply (
					select (
						select
							ISNULL(CAST(tneu.ToothNumEvnUsluga_ToothNum as VARCHAR),'') + ',' as 'data()'
						from v_EvnUsluga eu with (nolock)
						inner join v_ToothNumEvnUsluga tneu with(nolock) on tneu.EvnUsluga_id = eu.EvnUsluga_id
						where eu.EvnPrescr_id = EP.EvnPrescrFuncDiag_id
					for xml path(''), TYPE
					) as ToothNums
				) tth
			where
				EP.EvnPrescrFuncDiag_id = :EvnPrescrFuncDiag_id
		";

		$queryParams = array(
			'EvnPrescrFuncDiag_id' => $data['EvnPrescrFuncDiag_id']
		);

		$result = $this->db->query($query, $queryParams);
		if ( is_object($result) ) {
			$tmp_arr = $result->result('array');
			if(count($tmp_arr) > 0)
			{
				$response = array();
				$uslugalist = array();
			}
			else
			{
				return $tmp_arr;
			}
			foreach($tmp_arr as $row) {
				if(!empty($row['UslugaComplex_id']))
				{
					$uslugalist[] = $row['UslugaComplex_id'];
				}
			}
			$response[0] = $tmp_arr[0];
			$response[0]['EvnPrescrFuncDiag_uslugaList'] = implode(',',$uslugalist);
			return $response;
		}
		else {
			return false;
		}
	}


	/**
	 * Получение данных для панели просмотра ЭМК и правой панели формы добавления назначений
	 */
	public function doLoadViewData($section, $evn_pid, $sessionParams) {
		
		$query = "
			select
				EP.EvnPrescr_id 
			from v_EvnPrescr EP with (nolock)
			where 
				EP.EvnPrescr_pid  = :EvnPrescr_pid
				and EP.PrescriptionType_id = 12
				and EP.PrescriptionStatusType_id != 3
		";
		//echo getDebugSQL($query, $queryParams); exit();
		$queryParams = array(
			'EvnPrescr_pid' => $evn_pid,
			'Lpu_id' => $sessionParams['lpu_id']
		);
		//echo getDebugSql($query, $queryParams);exit;
		$result = $this->db->query($query, $queryParams);
		
		$qr = $this->db->query($query, $queryParams);
		if ( !is_object($qr) ) {
			return false;
		}
		$list = $qr->result('array');
		
		if (count($list)>0) {
			// преобразуем list в массив для фильтра 
			$evId = array();
			foreach ($list as $v) {
				$evId[] = $v['EvnPrescr_id'];
			}
			$list_evId = implode(",", $evId);
			if (count($evId)==1) {
				$filterEv = "EP.EvnPrescr_id = :EvnPrescr_id";
				$queryParams['EvnPrescr_id'] = $evId[0];
			}  else {
				$filterEv = "EP.EvnPrescr_id in (".$list_evId.")";
			}
		
			$sysnick = swPrescription::getParentEvnClassSysNickBySectionName($section);
			$addJoin = '';
			if ($sysnick) {
				$accessType = "case when {$sysnick}.Lpu_id = :Lpu_id AND isnull({$sysnick}.{$sysnick}_IsSigned,1) = 1 then 'edit' else 'view' end as accessType";
				$addJoin = "left join v_{$sysnick} {$sysnick} with (nolock) on {$sysnick}.{$sysnick}_id = EP.EvnPrescr_pid";
			} else {
				$accessType = "'view' as accessType";
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
						when exists (
							select top 1 uca.UslugaComplexAttribute_id
							from UslugaComplexAttribute uca (nolock)
							inner join UslugaComplexAttributeType ucat (nolock) on ucat.UslugaComplexAttributeType_id = uca.UslugaComplexAttributeType_id
							and ucat.UslugaComplexAttributeType_SysNick like 'kt'
							where uca.UslugaComplex_id = UC.UslugaComplex_id
						) then 19
						when exists (
							select top 1 uca.UslugaComplexAttribute_id
							from UslugaComplexAttribute uca (nolock)
							inner join UslugaComplexAttributeType ucat (nolock) on ucat.UslugaComplexAttributeType_id = uca.UslugaComplexAttributeType_id
							and ucat.UslugaComplexAttributeType_SysNick like 'mrt'
							where uca.UslugaComplex_id = UC.UslugaComplex_id
						) then 18
						else 2 end
					) EvnXmlDir";
			}
			$query = "
				select
					{$accessType},
					EP.EvnPrescr_id
					,'EvnPrescrFuncDiag' as object
					,EP.EvnPrescr_pid
					,EP.EvnPrescr_rid
					,convert(varchar,EP.EvnPrescr_setDT,104) as EvnPrescr_setDate
					,null as EvnPrescr_setTime
					,isnull(EP.EvnPrescr_IsExec,1) as EvnPrescr_IsExec
					,case when EU.EvnUsluga_id is null then 1 else 2 end as EvnPrescr_IsHasEvn
					-- Если в качестве даты-времени выполнения брать EU.EvnUsluga_setDT, то дата может не отобразиться, если при выполнении не была создана услуга или услуга не связана с назначением
					-- Поэтому решил использовать EP.EvnPrescr_updDT, т.к. после выполнения эта дата не меняется
					,case when 2 = EP.EvnPrescr_IsExec
					then convert(varchar,EP.EvnPrescr_updDT,104)+' '+convert(varchar,EP.EvnPrescr_updDT,108) else null
					end as EvnPrescr_execDT
					,EP.PrescriptionStatusType_id
					,EP.PrescriptionType_id
					,EP.PrescriptionType_id as PrescriptionType_Code
					,isnull(EP.EvnPrescr_IsCito,1) as EvnPrescr_IsCito
					,isnull(EP.EvnPrescr_Descr,'') as EvnPrescr_Descr
					
					,case when ED.EvnDirection_id is null OR ISNULL(ED.EvnStatus_id, 16) in (12,13) then 1 else 2 end as EvnPrescr_IsDir
					,case when ED.EvnStatus_id is null and (ED.DirFailType_id > 0 OR EQ.QueueFailCause_id > 0 ) then 12 else ED.EvnStatus_id end as EvnStatus_id
					,case when EvnStatus.EvnStatus_Name is null and (ED.DirFailType_id > 0 OR EQ.QueueFailCause_id > 0 ) then 'Отменено' else EvnStatus.EvnStatus_Name end as EvnStatus_Name
					,coalesce(EvnStatusCause.EvnStatusCause_Name, DFT.DirFailType_Name, QFC.QueueFailCause_Name) as EvnStatusCause_Name
					,convert(varchar(10), coalesce(ED.EvnDirection_statusDate, ED.EvnDirection_failDT, EQ.EvnQueue_failDT), 104) as EvnDirection_statusDate
					,ESH.EvnStatusCause_id
					,ED.DirFailType_id
					,EQ.QueueFailCause_id 
					,ESH.EvnStatusHistory_Cause
					
					,ED.EvnDirection_id
					,EPFD.Resource_id
					,EQ.EvnQueue_id
					,case when ED.EvnDirection_Num is null /*or isnull(ED.EvnDirection_IsAuto,1) = 2*/ then '' else cast(ED.EvnDirection_Num as varchar) end as EvnDirection_Num
					,case
						when TTMS.TimetableMedService_id is not null then isnull(MS.MedService_Name,'') +' / '+ isnull(Lpu.Lpu_Nick,'')
						when TTR.TimetableResource_id is not null then isnull(MS.MedService_Name,'') +' / '+ isnull(R.Resource_Name,'') +' / '+ isnull(Lpu.Lpu_Nick,'')
						when EQ.EvnQueue_id is not null then
							case
								when MS.MedService_id is not null and  MS.LpuSection_id is null and MS.LpuUnit_id is null
								then isnull(MS.MedService_Name,'')
								when MS.MedService_id is not null and  MS.LpuSection_id is null and MS.LpuUnit_id is not null
								then isnull(MS.MedService_Name,'') +' / '+ isnull(LU.LpuUnit_Name,'')
								when MS.MedService_id is not null and  MS.LpuSection_id is not null and MS.LpuUnit_id is not null
								then isnull(MS.MedService_Name,'') +' / '+ isnull(LSPD.LpuSectionProfile_Name,'') +' / '+ isnull(LU.LpuUnit_Name,'')
								else isnull(LSPD.LpuSectionProfile_Name,'') +' / '+ isnull(LU.LpuUnit_Name,'')
							end +' / '+ isnull(Lpu.Lpu_Nick,'')
					else '' end as RecTo
					,case
						when TTMS.TimetableMedService_id is not null then isnull(convert(varchar(10), TTMS.TimetableMedService_begTime, 104),'')+' '+isnull(convert(varchar(5), TTMS.TimetableMedService_begTime, 108),'')
						when TTR.TimetableResource_id is not null then isnull(convert(varchar(10), TTR.TimetableResource_begTime, 104),'')+' '+isnull(convert(varchar(5), TTR.TimetableResource_begTime, 108),'')
						when EQ.EvnQueue_id is not null then 'В очереди с '+ isnull(convert(varchar(10), EQ.EvnQueue_setDate, 104),'')
					else '' end as RecDate
					,case
						when TTMS.TimetableMedService_id is not null then 'TimetableMedService'
						when TTR.TimetableResource_id is not null then 'TimetableResource'
						when EQ.EvnQueue_id is not null then 'EvnQueue'
					else '' end as timetable
					,case
						when TTMS.TimetableMedService_id is not null  then TTMS.TimetableMedService_id
						when TTR.TimetableResource_id is not null  then TTR.TimetableResource_id
						--when EU.EvnUsluga_id is not null then EU.EvnUsluga_id
						when EQ.EvnQueue_id is not null then EQ.EvnQueue_id
					else '' end as timetable_id
					,EP.EvnPrescr_pid as timetable_pid
					,LU.LpuUnitType_SysNick
					,DT.DirType_Code
					,EP.MedService_id
					,UC.UslugaComplex_id
					,UC.UslugaComplex_2011id
					,UC.UslugaComplex_Code
					,UC.UslugaComplex_Name
					,EPFDU.EvnPrescrFuncDiagUsluga_id as TableUsluga_id
					,etr.ElectronicTalon_id
					,CASE 
						when ((ED.Lpu_did is not null and ED.Lpu_did <> LpuSession.Lpu_id) or (EPFD.Lpu_id is not null and EPFD.Lpu_id <> LpuSession.Lpu_id)) then 2 else 1
					end as otherMO
					,EvnStatus.EvnStatus_SysNick
					,EUP.EvnUslugaPar_id
					,ED.StudyTarget_id
					,EPFD.Lpu_id
					{$addSelect}
				from v_EvnPrescr EP with (nolock)
					inner join EvnPrescrFuncDiagUsluga EPFDU with (nolock) on EPFDU.EvnPrescrFuncDiag_id = EP.EvnPrescr_id
					inner join v_EvnPrescrFuncDiag EPFD with (nolock) on EPFD.EvnPrescrFuncDiag_id = EP.EvnPrescr_id
					left join v_UslugaComplex UC with (nolock) on UC.UslugaComplex_id = EPFDU.UslugaComplex_id
					outer apply (
						Select top 1 ED.EvnDirection_id
							,isnull(ED.Lpu_sid, ED.Lpu_id) Lpu_id
							,ED.EvnQueue_id
							,ED.EvnDirection_Num
							,ED.EvnDirection_IsAuto
							,ED.LpuSection_did
							,ED.LpuUnit_did
							,ED.Lpu_did
							,ED.MedService_id
							,ED.Resource_id
							,ED.LpuSectionProfile_id
							,ED.DirType_id
							,ED.EvnStatus_id
							,ED.EvnDirection_statusDate
							,ED.DirFailType_id
							,ED.EvnDirection_failDT
							,ED.StudyTarget_id
						from v_EvnPrescrDirection epd with (nolock)
						inner join v_EvnDirection_all ED with (nolock) on epd.EvnDirection_id = ED.EvnDirection_id
						where epd.EvnPrescr_id = EP.EvnPrescr_id
						order by 
							case when ISNULL(ED.EvnStatus_id, 16) in (12,13) then 2 else 1 end /* первым неотмененное/неотклоненное направление */
							,epd.EvnPrescrDirection_insDT desc
					) ED
					-- службы и параклиника
					outer apply (
						Select top 1 TimetableMedService_id, TimetableMedService_begTime from v_TimetableMedService_lite TTMS with (nolock) where TTMS.EvnDirection_id = ED.EvnDirection_id
					) TTMS
					-- очередь
					outer apply (
						Select top 1 EQ.EvnQueue_id, EQ.LpuUnit_did, EQ.LpuSectionProfile_did, Lpu_id, EQ.EvnQueue_setDate, EQ.EvnQueue_failDT, EQ.QueueFailCause_id 
						from v_EvnQueue EQ with (nolock)
						where EQ.EvnDirection_id = ED.EvnDirection_id
						and EQ.EvnQueue_recDT is null
						-- это костыль для того, чтобы направления у которых есть связь по EQ.EvnQueue_id = ED.EvnQueue_id, но нет реальной записи в TimetableMedService можно было отменить
						union
						Select top 1 EQ.EvnQueue_id, EQ.LpuUnit_did, EQ.LpuSectionProfile_did, Lpu_id, EQ.EvnQueue_setDate, EQ.EvnQueue_failDT, EQ.QueueFailCause_id 
						from v_EvnQueue EQ with (nolock)
						where (EQ.EvnQueue_id = ED.EvnQueue_id)
						and (EQ.EvnQueue_recDT is null or TTMS.TimetableMedService_id is null)
						and EQ.EvnQueue_failDT is null
					) EQ
					outer apply(
						select top 1 ESH.EvnStatus_id, ESH.EvnStatusCause_id, ESH.pmUser_insID, ESH.EvnStatusHistory_Cause
						from EvnStatusHistory ESH with(nolock)
						where ESH.Evn_id = ED.EvnDirection_id
							and ESH.EvnStatus_id = ED.EvnStatus_id
						order by ESH.EvnStatusHistory_begDate desc
					) ESH
					left join EvnStatus with(nolock) on EvnStatus.EvnStatus_id = ESH.EvnStatus_id
					left join EvnStatusCause with(nolock) on EvnStatusCause.EvnStatusCause_id = ESH.EvnStatusCause_id
					left join v_DirFailType DFT with(nolock) on DFT.DirFailType_id = ED.DirFailType_id
					left join v_QueueFailCause QFC with(nolock) on QFC.QueueFailCause_id = EQ.QueueFailCause_id
					-- сама служба (todo: надо ли оно)
					left join v_MedService MS with (nolock) on MS.MedService_id = ED.MedService_id -- ED.MedService_did должно быть
					-- ресурсы
					outer apply (
						Select top 1 TimetableResource_id, TimetableResource_begTime from v_TimetableResource_lite TTR with (nolock) where TTR.EvnDirection_id = ED.EvnDirection_id
					) TTR
					-- сам ресрс
					left join v_Resource R with (nolock) on R.Resource_id = ED.Resource_id
					-- отделение для полки и стаца и для очереди
					left join v_LpuSection LS with (nolock) on LS.LpuSection_id = coalesce(ED.LpuSection_did, MS.LpuSection_id)
					-- заказанная услуга для параклиники
					left join v_EvnUslugaPar EUP with (NOLOCK) on EUP.EvnDirection_id = ED.EvnDirection_id
					-- подразделение для очереди и служб
					left join v_LpuUnit LU with (nolock) on coalesce(ED.LpuUnit_did,EQ.LpuUnit_did,MS.LpuUnit_id) = LU.LpuUnit_id -- todo: в ED.LpuUnit_did пусто, чего не должно быть
					-- профиль для очереди
					left join v_LpuSectionProfile LSPD with (nolock) on coalesce(ED.LpuSectionProfile_id,EQ.LpuSectionProfile_did,LS.LpuSectionProfile_id) = LSPD.LpuSectionProfile_id -- todo: тут на примере оказалось что почему то ED.LpuSectionProfile_id != EQ.LpuSectionProfile_did, чего не должно быть
					-- тип направления
					left join v_DirType DT with (NOLOCK) on ED.DirType_id = DT.DirType_id
					-- ЛПУ
					left join v_Lpu Lpu with (nolock) on Lpu.Lpu_id = coalesce(ED.Lpu_did, LS.Lpu_id, MS.Lpu_id, EQ.Lpu_id)
					left join v_Lpu LpuSession with (nolock) on LpuSession.Lpu_id = :Lpu_id
					outer apply (
						select top 1 EvnUsluga_id, EvnUsluga_setDT from v_EvnUsluga with (nolock)
						where EP.EvnPrescr_IsExec = 2 and UC.UslugaComplex_id is not null and EvnPrescr_id = EP.EvnPrescr_id
					) EU
					outer apply (
						select top 1
							etra.ElectronicTalon_id
						from v_ElectronicTalonRedirect etra with (nolock)
						inner join v_ElectronicTalon et with (nolock) on (et.ElectronicService_id = etra.ElectronicService_id and et.EvnDirection_uid = etra.EvnDirection_uid)
						where
							etra.EvnDirection_uid = ED.EvnDirection_id
					) etr
					{$addJoin}
				where
					{$filterEv}
					and EP.PrescriptionType_id = 12
					and EP.PrescriptionStatusType_id != 3
				order by
					EP.EvnPrescr_id,
					EP.EvnPrescr_setDT
			";

			//echo getDebugSql($query, $queryParams);exit;
			$result = $this->db->query($query, $queryParams);
			if (is_object($result)) {
				$tmp_arr = $result->result('array');
				$response = array();
				$last_ep = null;
				$is_exe = null;
				$is_sign = null;
				$uslugaIdList = array();
				$uslugaList = array();
				foreach ($tmp_arr as $i => $row) {
					if ($last_ep != $row['EvnPrescr_id']) {
						//это первая итерация с другим назначением
						$last_ep = $row['EvnPrescr_id'];
						$is_exe = false;
						$is_sign = false;
						$uslugaIdList = array();
						$uslugaList = array();
					}
					if ( empty($uslugaList[$row['TableUsluga_id']]) ) {
						$uslugaIdList[] = $row['UslugaComplex_id'];
						if ($this->options['prescription']['enable_show_service_code']) {
							$uslugaList[$row['TableUsluga_id']] = $row['UslugaComplex_Code'].' '.$row['UslugaComplex_Name'];
						} else {
							$uslugaList[$row['TableUsluga_id']] = $row['UslugaComplex_Name'];
						}
					}
					if ($is_exe == false) $is_exe = ($row['EvnPrescr_IsExec'] == 2);
					if ($is_sign == false) $is_sign = ($row['PrescriptionStatusType_id'] == 2);
					if (empty($tmp_arr[$i+1]) || $last_ep != $tmp_arr[$i+1]['EvnPrescr_id']) {
						if ($is_exe) $row['EvnPrescr_IsExec'] = 2;
						if ($is_sign) $row['PrescriptionStatusType_id'] = 2;
						$row['UslugaId_List'] = implode(',', $uslugaIdList);
						$row['Usluga_List'] = implode('<br />', $uslugaList);
						$row[$section . '_id'] = $row['EvnPrescr_id'].'-'.$row['TableUsluga_id'];
						$response[] = $row;
					}
				}
				//загружаем документы
				$tmp_arr = array();
				$evnPrescrIdList = array();
				foreach ($response as $key => $row) {
					if (isset($row['EvnPrescr_IsExec'])
						&& 2 == $row['EvnPrescr_IsExec']
						&& isset($row['EvnPrescr_IsHasEvn'])
						&& 2 == $row['EvnPrescr_IsHasEvn']
					) {
						$response[$key]['EvnXml_id'] = null;
						$id = $row['EvnPrescr_id'];
						$evnPrescrIdList[] = $id;
						$tmp_arr[$id] = $key;
					}
				}
				if (count($evnPrescrIdList) > 0) {
					$evnPrescrIdList = implode(',',$evnPrescrIdList);
					$query = "
					WITH EvnPrescrEvnXml
					as (
						select doc.EvnXml_id, EU.EvnPrescr_id
						from  v_EvnUsluga EU (nolock)
						inner join v_EvnXml doc (nolock) on doc.Evn_id = EU.EvnUsluga_id
						where EU.EvnPrescr_id in ({$evnPrescrIdList})
					)

					select EvnXml_id, EvnPrescr_id from EvnPrescrEvnXml with(nolock)
					order by EvnPrescr_id";
					$result = $this->db->query($query);
					if ( is_object($result) ) {
						$evnPrescrIdList = $result->result('array');
						foreach ($evnPrescrIdList as $row) {
							$id = $row['EvnPrescr_id'];
							if (isset($tmp_arr[$id])) {
								$key = $tmp_arr[$id];
								if (isset($response[$key])) {
									$response[$key]['EvnXml_id'] = $row['EvnXml_id'];
								}
							}
						}
					}
				}
				return $response;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	/**
	 * Очистка связанных зубов с назначения на ФД
	 */
	function delEvnUslugaToothNum($data)
	{
		$this->load->model('EvnUsluga_model', 'eumodel');

		if (empty($data['EvnUsluga_id']) && !empty($data['EvnPrescr_id'])) {

			$resp = $this->eumodel->getEvnUslugaByEvnPrescrId(array('EvnPrescr_id' => $data['EvnPrescr_id']));

			if (!empty($resp[0]) && !empty($resp[0]['EvnUsluga_id'])) {
				$data['EvnUsluga_id'] = $resp[0]['EvnUsluga_id'];
			}
		}

		if (!empty($data['EvnUsluga_id'])) {

			// возмьем данные по существующим зубам для события_услуги
			$resp = $this->eumodel->getToothNumEvnUsluga(array('EvnUsluga_id' => $data['EvnUsluga_id']));

			if (!empty($resp[0]) && empty($resp[0]['Error_Msg'])) {

				$related_tooths = array_column($resp, 'ToothNumEvnUsluga_ToothNum', 'ToothNumEvnUsluga_id');

				foreach ($related_tooths as $key => $value) {
					$this->eumodel->delToothNumEvnUsluga($key);
				}
			}
		}
	}

	/**
	 * Обработка после отмены назначения
	 */
	function onAfterCancel($data)
	{
		// очистка связанных зубов с назначения на ФД
		if (!empty($data['parentEvnClass_SysNick']) && $data['parentEvnClass_SysNick'] == 'EvnVizitPLStom'
		) {
			$this->delEvnUslugaToothNum($data);
		}
	}
}
