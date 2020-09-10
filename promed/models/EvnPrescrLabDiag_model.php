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
 * Модель назначения "Лабораторная диагностика"
 *
 * Назначения с типом "Лабораторная диагностика" хранятся в таблицах EvnPrescrLabDiag, EvnPrescrLabDiagUsluga
 * В назначении должна быть указана только одна услуга.
 * Если услуга имеет состав (UslugaComplexComposition), то могут выбраны все или лишь некоторые простые услуги из её состава.
 * Для каждой выбранной простой услуги из состава создается запись в EvnPrescrLabDiagUsluga.
 *
 * @package		EvnPrescr
 * @author		Александр Пермяков
 * @property EvnDirectionAll_model $EvnDirectionAll_model
 */
class EvnPrescrLabDiag_model extends EvnPrescrAbstract_model
{
	/**
	 * Конструктор
	 */
	public function __construct()
	{
		parent::__construct();

		if ($this->usePostgreLis)
			$this->load->swapi('lis');
	}

	/**
	 * Определение идентификатора типа назначения
	 * @return int
	 */
	public function getPrescriptionTypeId() {
		return 11;
	}

	/**
	 * Определение имени таблицы с данными назначения
	 * @return string
	 */
	public function getTableName() {
		return 'EvnPrescrLabDiag';
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
					array('field' => 'parentEvnClass_SysNick','label' => 'Системное имя род.события','rules' => '', 'default' => 'EvnSection','type' => 'string'),
					array('field' => 'signature','label' => 'Признак для подписания','rules' => '','type' => 'int'),
					array('field' => 'EvnPrescrLabDiag_id','label' => 'Идентификатор назначения','rules' => '','type' => 'id'),
					array('field' => 'EvnPrescrLabDiag_pid','label' => 'Идентификатор род.события','rules' => 'required','type' => 'id'),
					array('field' => 'MedService_id','label' => 'Служба','rules' => '','type' => 'id'),
					array('field' => 'UslugaComplex_id','label' => 'Услуга','rules' => 'required','type' => 'id'),
					array('field' => 'UslugaComplexMedService_pid','label' => 'Связь услуги и службы','rules' => '','type' => 'id'),
					array('field' => 'EvnPrescrLabDiag_uslugaList','label' => 'Список услуг, выбранных из состава комплексной услуги','rules' => '','type' => 'string'),
					array('field' => 'EvnPrescrLabDiag_setDate','label' => 'Плановая дата','rules' => '','type' => 'date'),
					array('field' => 'EvnPrescrLabDiag_IsCito','label' => 'Cito','rules' => '','type' => 'string'),
					array('field' => 'EvnPrescrLabDiag_Descr','label' => 'Комментарий','rules' => 'trim','type' => 'string'),
					array('field' => 'EvnDirection_id','label' => 'Идентификатор','rules' => '','type' => 'id'),
					array('field' => 'DopDispInfoConsent_id','label' => 'Идентификатор согласия карты диспансеризации','rules' => '','type' => 'id'),
					array('field' => 'PersonEvn_id','label' => 'Идентификатор','rules' => 'required','type' => 'id'),
					array('field' => 'Server_id','label' => 'Идентификатор сервера','rules' => 'required','type' => 'int'),
					array('field' => 'EvnPrescrLimitData','label' => 'Ограничения','rules' => '','type' => 'string'),
					array('field' => 'EvnUslugaOrder_id','label' => 'Идентификатор заказа','rules' => '','type' => 'id'),
					array('field' => 'EvnUslugaOrder_UslugaChecked','label' => 'Измененный состав для обновления заказа','rules' => '','type' => 'string'),
					array('field' => 'StudyTarget_id','label' => 'Цель исследования','rules' => '','type' => 'string'),
					array('field' => 'MedService_pzmid','label' => 'Пункт забора','rules' => '','type' => 'string'),
					array('field' => 'EvnPrescrLabDiag_CountComposit','label' => 'количество сохраненных услуг в составной услуге','rules' => '','type' => 'int'),
					array('field' => 'isExt6','label' => 'Сохранение из формы Ext6','rules' => '','type' => 'int'),
					array('field' => 'UslugaComplexContent_ids','label' => 'Измененный состав для обновления заказа','rules' => '','type' => 'string'),
					array('field' => 'HIVContingentTypeFRMIS_id','label' => 'Код контингента ВИЧ','rules' => '','type' => 'int'),
					array('field' => 'CovidContingentType_id', 'label' => 'Код контингента COVID', 'rules' => '', 'type' => 'int'),
					array('field' => 'HormonalPhaseType_id','label' => 'Фаза цикла','rules' => '','type' => 'int'),
					array('field' => 'PersonDetailEvnDirection_id','label' => 'PersonDetailEvnDirection_id','rules' => '','type' => 'int')
				);
				break;
			case 'doLoad':
				$rules[] = array(
					'field' => 'EvnPrescrLabDiag_id',
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
	public function doSave($data = array(), $isAllowTransaction = true) {
		if(empty($data['EvnPrescrLabDiag_id']))
		{
			$action = 'ins';
			$allow_sign = true;
			$data['EvnPrescrLabDiag_id'] = NULL;
			$data['PrescriptionStatusType_id'] = 1;
			$EvnPrescr_id = NULL;
		}
		else
		{
			$action = 'upd';
			$EvnPrescr_id = $data['EvnPrescrLabDiag_id'];
			$o_data = $this->getAllData($data['EvnPrescrLabDiag_id']);
			if(!empty($o_data['Error_Msg']))
			{
				return array($o_data);
			}
			$allow_sign = (isset($o_data['PrescriptionStatusType_id']) && $o_data['PrescriptionStatusType_id'] == 1);
			foreach($o_data as $k => $v) {
				if(!isset($data[$k]))
				{
					$data[$k] = $v;
				}
			}
		}
		if(!isset($data['UslugaComplexMedService_pid'])) $data['UslugaComplexMedService_pid'] = null;
		if(empty($data['EvnPrescrLabDiag_CountComposit']))
			$data['EvnPrescrLabDiag_CountComposit'] = NULL;
		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);

			set @Res = :EvnPrescrLabDiag_id;

			exec p_EvnPrescrLabDiag_" . $action . "
				@EvnPrescrLabDiag_id = @Res output,
				@EvnPrescrLabDiag_pid = :EvnPrescrLabDiag_pid,
				@PrescriptionType_id = :PrescriptionType_id,
				@PrescriptionStatusType_id = :PrescriptionStatusType_id,
				@Lpu_id = :Lpu_id,
				@Server_id = :Server_id,
				@PersonEvn_id = :PersonEvn_id,
				@MedService_id = :MedService_id,
				@UslugaComplex_id = :UslugaComplex_id,
				@EvnPrescrLabDiag_setDT = :EvnPrescrLabDiag_setDate,
				@EvnPrescrLabDiag_IsCito = :EvnPrescrLabDiag_IsCito,
				@EvnPrescrLabDiag_Descr = :EvnPrescrLabDiag_Descr,
				@EvnPrescrLabDiag_CountComposit = :EvnPrescrLabDiag_CountComposit,
				@DopDispInfoConsent_id = :DopDispInfoConsent_id,
				@UslugaComplexMedService_id = :UslugaComplexMedService_pid,
				@StudyTarget_id = :StudyTarget_id,
				@MedService_pzmid = :MedService_pzmid,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @Res as EvnPrescrLabDiag_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$data['EvnPrescrLabDiag_IsCito'] = (empty($data['EvnPrescrLabDiag_IsCito']) || $data['EvnPrescrLabDiag_IsCito'] != 'on')? 1 : 2;
		$data['PrescriptionType_id'] = $this->getPrescriptionTypeId();

		if (empty($data['DopDispInfoConsent_id'])) {
			$data['DopDispInfoConsent_id'] = null;
		}

		//echo getDebugSQL($query, $data); exit();

		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {
			$trans_result = $result->result('array');
			if(!empty($trans_result) && !empty($trans_result[0]) && !empty($trans_result[0]['EvnPrescrLabDiag_id']) && empty($trans_result[0]['Error_Msg']))
			{
				$trans_good = true;
				$EvnPrescr_id = $trans_result[0]['EvnPrescrLabDiag_id'];
			}
			else
			{
				$trans_good = false;
			}
		}
		else {
			$trans_good = false;
			$trans_result = false;
		}

		$uslugalist = array();
		if($trans_good === true && !empty($data['EvnPrescrLimitData']))
		{
			$data['EvnPrescrLimitData'] = toUtf($data['EvnPrescrLimitData']);
			$limitdata = json_decode($data['EvnPrescrLimitData'], true);
			foreach($limitdata as $limit) {
				$limit['EvnPrescrLimit_id'] = null;
				$limit['LimitType_IsCatalog'] = 1;
				$limit['EvnPrescr_id'] = $EvnPrescr_id;
				$limit['pmUser_id'] = $data['pmUser_id'];

				if (!empty($limit['LimitType_id'])) {
					// 1. ищем запись для соответвующего LimitType_id и RefValues_id
					$query = "
						select top 1
							epl.EvnPrescrLimit_id,
							ISNULL(lt.LimitType_IsCatalog, 1) as LimitType_IsCatalog
						from
							v_LimitType (nolock) lt
							left join v_EvnPrescrLimit (nolock) epl on epl.LimitType_id = lt.LimitType_id and epl.EvnPrescr_id = :EvnPrescr_id
						where
							lt.LimitType_id = :LimitType_id
					";

					$result = $this->db->query($query, $limit);
					if ( is_object($result) ) {
						$resp = $result->result('array');
						if (!empty($resp[0]['EvnPrescrLimit_id'])) {
							$limit['EvnPrescrLimit_id'] = $resp[0]['EvnPrescrLimit_id'];
						}
						if (!empty($resp[0]['LimitType_IsCatalog'])) {
							$limit['LimitType_IsCatalog'] = $resp[0]['LimitType_IsCatalog'];
						}
					}

					// 2. сохраняем
					$procedure = 'p_EvnPrescrLimit_ins';
					if ( !empty($limit['EvnPrescrLimit_id']) ) {
						$procedure = 'p_EvnPrescrLimit_upd';
					}

					if (empty($limit['EvnPrescrLimit_ValuesNum'])) { $limit['EvnPrescrLimit_ValuesNum'] = null;	}
					if (empty($limit['EvnPrescrLimit_Values'])) { $limit['EvnPrescrLimit_Values'] = null;	}

					$query = "
						declare
							@EvnPrescrLimit_id bigint,
							@ErrCode int,
							@ErrMessage varchar(4000);
						set @EvnPrescrLimit_id = :EvnPrescrLimit_id;
						exec " . $procedure . "
							@EvnPrescrLimit_id = @EvnPrescrLimit_id output,
							@LimitType_id = :LimitType_id,
							@EvnPrescrLimit_Values = :EvnPrescrLimit_Values,
							@EvnPrescr_id = :EvnPrescr_id,
							@EvnPrescrLimit_ValuesNum = :EvnPrescrLimit_ValuesNum,
							@pmUser_id = :pmUser_id,
							@Error_Code = @ErrCode output,
							@Error_Message = @ErrMessage output;
						select @EvnPrescrLimit_id as EvnPrescrLimit_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
					";
					$result = $this->db->query($query, $limit);

					if ( is_object($result) ) {
						$res = $result->result('array');
						if(!empty($res) && !empty($res[0]) && empty($res[0]['Error_Msg']))
						{
							$trans_good = true;
						}
						else
						{
							$trans_good = false;
							$trans_result[0]['Error_Msg'] = $res[0]['Error_Msg'];
							break;//выходим из цикла
						}
					}
					else {
						$trans_good = false;
						$trans_result[0]['Error_Msg'] = 'Ошибка запроса при ';
						break;//выходим из цикла
					}
				}
			}
		}

		if($trans_good === true && !empty($data['EvnPrescrLabDiag_uslugaList']))
		{
			$uslugalist = explode(',', $data['EvnPrescrLabDiag_uslugaList']);
			if(empty($uslugalist) || !is_numeric ($uslugalist[0]))
			{
				$trans_result[0]['Error_Msg'] = 'Ошибка формата списка услуг';
				$trans_good = false;
			}
			else
			{
				if (isset($data['EvnPrescrLabDiag_id'])) {
					//запрос на поиск уже заведенных лаб. услуг по назначению,
					//которые есть в заказе, но содержатся в других параклинических услугах
					$query = "
						with uslugas as (
							select
								euc.UslugaComplex_id,
								euc.EvnUslugaPar_id
							from
								v_EvnLabRequestUslugaComplex euc with (nolock)
								left join v_EvnLabRequest elr with (nolock) on euc.EvnLabRequest_id = elr.EvnLabRequest_id
							where
								elr.EvnDirection_id in (
									select top 1
										EvnDirection_id
									from
										v_EvnPrescrDirection with (nolock)
									where
										EvnPrescr_id = :EvnPrescrLabDiag_id
								) 	
						)

						select u1.*
						from uslugas u1
						where u1.EvnUslugaPar_id not in (
							select EvnUslugaPar_id
							from uslugas
							where UslugaComplex_id in (". implode(', ', $uslugalist) .")
						)
					";

					$res = $this->queryResult($query, $data);
					if ($res) {
						$existingIds = [];
						foreach ($res as $re) {
							$existingIds[] = $re['UslugaComplex_id'];
						}
					}
				}

				$res = $this->clearEvnPrescrLabDiagUsluga(array('EvnPrescrLabDiag_id' => $EvnPrescr_id));
				if(empty($res))
				{
					$trans_result[0]['Error_Msg'] = 'Ошибка запроса списка выбранных услуг';
					$trans_good = false;
				}
				if(!empty($res) && !empty($res[0]) && !empty($res[0]['Error_Msg']))
				{
					$trans_result[0]['Error_Msg'] = $res[0]['Error_Msg'];
					$trans_good = false;
				}
			}
		}

		if($trans_good === true && !empty($uslugalist))
		{
			foreach($uslugalist as $d)
			{
				$res = $this->saveEvnPrescrLabDiagUsluga(array(
					'UslugaComplex_id' => $d
					,'EvnPrescrLabDiag_id' => $EvnPrescr_id
					,'pmUser_id' => $data['pmUser_id']
				));
				if(empty($res))
				{
					$trans_result[0]['Error_Msg'] = 'Ошибка запроса при сохранении услуги';
					$trans_good = false;
					break;
				}
				if(!empty($res) && !empty($res[0]) && !empty($res[0]['Error_Msg']))
				{
					$trans_result[0]['Error_Msg'] = $res[0]['Error_Msg'];
					$trans_good = false;
					break;
				}
			}
		}
		if($trans_good === true && !empty($data['EvnUslugaOrder_UslugaChecked']) && empty($data['EvnUslugaOrder_id']) && !empty($data['EvnDirection_id'])) {
			//попробуем достать EvnUslugaOrder_id для следующего блока, если из формы параметр не пришел.
			$EvnUslugaOrder_id = null;
			if ($this->usePostgreLis) {
				$resEvnUslugaOrder_id = $this->lis->GET('EvnUsluga/EvnUslugaParByEvnDirection', array('EvnDirection_id' => $data['EvnDirection_id']));
				if($resEvnUslugaOrder_id['error_code'] != 0) {
					$trans_good = false;
					$trans_result[0]['Error_Msg'] = 'Ошибка получения идентификатора услуги в ЛИС';
				} else $EvnUslugaOrder_id = $resEvnUslugaOrder_id['data'];
			} else {
				$sql = "
					select
						eup.EvnUslugaPar_id
					from
						v_EvnUslugaPar eup with(nolock)
					where 
						eup.EvnDirection_id = :EvnDirection_id
				";
				$EvnUslugaOrder_id = $this->getFirstResultFromQuery($sql, $data);
			}
			$data['EvnUslugaOrder_id'] = $EvnUslugaOrder_id;
		}

		if($trans_good === true && !empty($data['EvnUslugaOrder_id']) && !empty($data['EvnUslugaOrder_UslugaChecked']))
		{
			$uslugalist = explode(',', $data['EvnUslugaOrder_UslugaChecked']);
			if (!empty($existingIds)) {
				$uslugalist = array_merge($uslugalist, $existingIds);
			}
			if(empty($uslugalist) || !is_numeric ($uslugalist[0]))
			{
				$trans_result[0]['Error_Msg'] = 'Ошибка формата списка заказанных услуг';
				$trans_good = false;
			}
			else
			{
				if ($this->usePostgreLis) {
					$resp = $this->lis->PATCH('EvnLabRequest/EvnUslugaPar/Result', array(
						'EvnUslugaPar_id' => $data['EvnUslugaOrder_id'],
						'EvnUslugaPar_Result' => json_encode($uslugalist),
					), 'list');
				} else {
					$this->load->model('EvnLabRequest_model');
					$resp = $this->EvnLabRequest_model->updateEvnUslugaParResult([
						'EvnUslugaPar_id' => $data['EvnUslugaOrder_id'],
						'EvnUslugaPar_Result' => json_encode($uslugalist),
						'pmUser_id' => $data['pmUser_id']
					]);
				}
				if (!$this->isSuccessful($resp)) {
					$trans_result[0]['Error_Msg'] = (!empty($resp[0]['Error_Msg']))?$resp[0]['Error_Msg']:'Ошибка изменения состава';
					$trans_good = false;
				}
			}
		}

		if ($action === 'upd' && $trans_good === true
			&& !empty($data['EvnDirection_id'])
		) {
			//todo: в БД ЛИС
			// обновляем пометку "Cito" заявки в АРМе лаборанта
			/*$this->load->model('EvnDirectionAll_model');
			$this->EvnDirectionAll_model->setScenario(swModel::SCENARIO_SET_ATTRIBUTE);
			$this->EvnDirectionAll_model->setParams(array(
				'session' => $data['session'],
			));
			$response = $this->EvnDirectionAll_model->updateIsCito($data['EvnDirection_id'], $data['EvnPrescrLabDiag_IsCito']);
			if ( !empty($response['Error_Msg']) ) {
				$trans_result[0]['Error_Msg'] = $response['Error_Msg'];
				$trans_good = false;
				//throw new Exception($response['Error_Msg']);
			}*/
		}
		// Обновление тестов в заказе по-новому для новой формы, РАБОТАЕТ!!!
		if($action === 'upd' && $trans_good === true
			&& !empty($uslugalist) && !empty($data['EvnUslugaOrder_id'])
			/*&& !empty($data['isExt6'])*/ && !empty($data['EvnDirection_id']))
		{
			if ($this->usePostgreLis) {
				$resp = $this->lis->POST('EvnLabRequest/Content', $data, 'single');
			} else {
				$this->load->model('EvnLabRequest_model');
				$resp = $this->EvnLabRequest_model->saveEvnLabRequestContent($data);
			}
			if (!$this->isSuccessful($resp)) {
				$trans_result[0]['Error_Msg'] = $resp['Error_Msg'];
				$trans_good = false;
			}
		}

		if ($action === 'upd' && $trans_good === true
			&& !empty($uslugalist) && !empty($data['EvnUslugaOrder_id']) && empty($data['isExt6']) //Пусть старая форма работает как хочет/как было
		) {
			if ($this->usePostgreLis) {
				$resp = $this->lis->POST('EvnLabRequest/EvnUslugaPar/recache', array(
					'EvnUslugaPar_id' => $data['EvnUslugaOrder_id'],
					'uslugaList' => json_encode($uslugalist)
				), 'list');
			} else {
				$this->load->model('EvnLabRequest_model');
				$resp = $this->EvnLabRequest_model->ReCacheEvnUslugaPar([
					'EvnUslugaPar_id' => $data['EvnUslugaOrder_id'],
					'uslugaList' => json_encode($uslugalist),
					'pmUser_id' => $data['pmUser_id']
				]);
			}
			if (!$this->isSuccessful($resp)) {
				$trans_result[0]['Error_Msg'] = $resp[0]['Error_Msg'];
				$trans_good = false;
			}
		}

		if($trans_good === true && $allow_sign && !empty($data['signature']))
		{
			/*$res = $this->signEvnPrescr(array(
				'PrescriptionType_id' => 11
				,'parentEvnClass_SysNick' => $data['parentEvnClass_SysNick']
				,'EvnPrescr_pid' => $data['EvnPrescrLabDiag_pid']
				,'EvnPrescr_id' => $EvnPrescr_id
				,'pmUser_id' => $data['pmUser_id']
			));
			if(empty($res))
			{
				$trans_result[0]['Error_Msg'] = 'Ошибка запроса при подписании назначения';
				$trans_good = false;
			}
			if(!empty($res) && !empty($res[0]) && !empty($res[0]['Error_Msg']))
			{
				$trans_result[0]['Error_Msg'] = $res[0]['Error_Msg'];
				$trans_good = false;
			}*/
		}

		return $trans_result;
	}

	/**
	 *  метод очистки списка услуг
	 */
	function clearEvnPrescrLabDiagUsluga($data) {
		return $this->clearEvnPrescrTable(array(
			'object'=>'EvnPrescrLabDiagUsluga'
		,'fk_pid'=>'EvnPrescrLabDiag_id'
		,'pid'=>$data['EvnPrescrLabDiag_id']
		));
	}

	/**
     * Сохранение назнач
     */
	function saveEvnPrescrLabDiagUsluga($data) {
		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);

			set @Res = :EvnPrescrLabDiagUsluga_id;

			exec p_EvnPrescrLabDiagUsluga_" . (!empty($data['EvnPrescrLabDiagUsluga_id']) ? "upd" : "ins") . "
				@EvnPrescrLabDiagUsluga_id = @Res output,
				@EvnPrescrLabDiag_id = :EvnPrescrLabDiag_id,
				@UslugaComplex_id = :UslugaComplex_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @Res as EvnPrescrLabDiagUsluga_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$queryParams = array(
			'EvnPrescrLabDiagUsluga_id' => (empty($data['EvnPrescrLabDiagUsluga_id'])? NULL : $data['EvnPrescrLabDiagUsluga_id'] ),
			'EvnPrescrLabDiag_id' => $data['EvnPrescrLabDiag_id'],
			'UslugaComplex_id' => $data['UslugaComplex_id'],
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
		// если создана заявка и она не в статусе новая, то только просмотр назначения
		$query = "
			select
				case when EP.PrescriptionStatusType_id = 1 and LR.EvnStatus_id = 1
				then 'edit' else 'view' end as accessType,
				EP.EvnPrescrLabDiag_id,
				EP.EvnPrescrLabDiag_pid,
				ED.EvnDirection_id,
				EP.UslugaComplex_id,
				UCC.UslugaComplex_id as UslugaComplex_sid,
				convert(varchar(10), EP.EvnPrescrLabDiag_setDT, 104) as EvnPrescrLabDiag_setDate,
				case when isnull(EP.EvnPrescrLabDiag_IsCito,1) = 1 then 'off' else 'on' end as EvnPrescrLabDiag_IsCito,
				EP.EvnPrescrLabDiag_Descr,
				EP.Person_id,
				EP.PersonEvn_id,
				EP.Server_id,
				ED.MedService_id,
				EP.StudyTarget_id
			from 
				v_EvnPrescrLabDiag EP with (nolock)
				-- состав услуги, если услуга комплексная
				left join v_EvnPrescrLabDiagUsluga UCC with (nolock) on UCC.EvnPrescrLabDiag_id = EP.EvnPrescrLabDiag_id
				outer apply (
					Select top 1
						isnull(ED.EvnDirection_id, epd.EvnDirection_id) as EvnDirection_id,
						ED.MedService_id
					from v_EvnPrescrDirection epd with (nolock)
					inner join v_EvnDirection_all ED with (nolock) on epd.EvnDirection_id = ED.EvnDirection_id
						and ED.EvnDirection_failDT is null
						and ISNULL(ED.EvnStatus_id, 16) not in (12,13)
					where epd.EvnPrescr_id = EP.EvnPrescrLabDiag_id
					order by epd.EvnPrescrDirection_insDT desc
				) ED
				left join v_EvnLabRequest LR with (nolock) on LR.EvnDirection_id = ED.EvnDirection_id
			where
				EP.EvnPrescrLabDiag_id = :EvnPrescrLabDiag_id
		";

		$queryParams = array(
			'EvnPrescrLabDiag_id' => $data['EvnPrescrLabDiag_id']
		);
		//echo getDebugSql($query, $queryParams);exit;
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
				if(!empty($row['UslugaComplex_sid']))
				{
					$uslugalist[] = $row['UslugaComplex_sid'];
				}
			}
			$response[0] = $tmp_arr[0];
			$response[0]['EvnPrescrLabDiag_uslugaList'] = implode(',',$uslugalist);
			return $response;
		}
		else {
			return false;
		}
	}

	/**
	 * Получение данных для панели просмотра ЭМК и правой панели формы добавления назначений
	 */
	public function doLoadViewData($section, $evn_pid, $sessionParams, $excepts = array()) {
		$sysnick = swPrescription::getParentEvnClassSysNickBySectionName($section);
		$addJoin = '';
		$filter = '';
		$testFilter = getAccessRightsTestFilter('UC.UslugaComplex_id');
		$filterAccessRightsDenied = getAccessRightsTestFilter('UCMPp.UslugaComplex_id', false, true);

		$except_ids = array();
		foreach($excepts as $item) {
			if (!empty($item['EvnPrescr_id'])) {
				$except_ids[] = $item['EvnPrescr_id'];
			}
		}
		if (count($except_ids) > 0) {
			$except_ids = implode(",", $except_ids);
			$filter .= " and EP.EvnPrescr_id not in ({$except_ids})";
		}

		$ucpCondition = ' and UCp.UslugaComplex_id is null ';
		if (!$sysnick) $ucpCondition = "";

		if (!empty($testFilter)){
			$filter .= "
				and (
					ED.MedPersonal_id = :MedPersonal_id
					or exists (
						select top 1 Evn_id from v_Evn with(nolock) where Evn_id = :EvnPrescr_pid and EvnClass_sysNick = 'EvnSection' and Evn_setDT <= EP.EvnPrescr_setDT and (Evn_disDT is null or Evn_disDT >= EP.EvnPrescr_setDT)
					)
					or ($testFilter {$ucpCondition})
				)";
		}

		if ($sysnick) {
			$accessType = "case when {$sysnick}.Lpu_id = :Lpu_id
			AND isnull({$sysnick}.{$sysnick}_IsSigned,1) = 1
			AND LR.EvnStatus_id = 1
			AND ISNULL(EP.EvnPrescr_IsExec, 1) = 1
			then 'edit' else 'view' end as accessType";
			$addJoin = "left join v_{$sysnick} {$sysnick} with (nolock) on {$sysnick}.{$sysnick}_id = EP.EvnPrescr_pid
				outer apply (
					select top 1
						UCMPp.UslugaComplex_id
					from
						v_UslugaComplexMedService UCMPp (nolock)
					inner join v_EvnLabRequestUslugaComplex ELRUC (nolock) on UCMPp.UslugaComplex_id = ELRUC.UslugaComplex_id and ELRUC.EvnLabRequest_id = LR.EvnLabRequest_id
					inner join v_EvnLabSample ELS (nolock) on ELS.EvnLabSample_id = ELRUC.EvnLabSample_id and ELS.LabSampleStatus_id IN(4,6)
					where
						UCMS.UslugaComplexMedService_id = UCMPp.UslugaComplexMedService_pid
						".((!empty($filterAccessRightsDenied))?"and ".$filterAccessRightsDenied:'')."
				) as UCp";
		} else {
			$accessType = "'view' as accessType";
		}

		$UslugaComplex_Code = 'UC.UslugaComplex_Code';
		$UslugaComplex_Name = 'coalesce(ucms.UslugaComplex_Name, UC.UslugaComplex_Name) as UslugaComplex_Name';

		if (!empty($this->options['prescription']['enable_grouping_by_gost2011']) || $this->options['prescription']['service_name_show_type'] == 2) {
			$UslugaComplex_Code = 'coalesce(UC11.UslugaComplex_Code, UC.UslugaComplex_Code) as UslugaComplex_Code';
			$UslugaComplex_Name = 'coalesce(UC11.UslugaComplex_Name, UC.UslugaComplex_Name) as UslugaComplex_Name';
		}

		$query = "
			declare @cur_date date = cast(GETDATE() as date);
			select
				{$accessType},
				EP.EvnPrescr_id
				,'EvnPrescrLabDiag' as object
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
				,EQ.EvnQueue_id
				,case when ED.EvnDirection_Num is null /*or isnull(ED.EvnDirection_IsAuto,1) = 2*/ then '' else cast(ED.EvnDirection_Num as varchar) end as EvnDirection_Num
				,case
					when TTMS.TimetableMedService_id is not null then isnull(MS.MedService_Name,'') +' / '+ isnull(Lpu.Lpu_Nick,'')
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
					when EQ.EvnQueue_id is not null then 'В очереди с '+ isnull(convert(varchar(10), EQ.EvnQueue_setDate, 104),'')
				else '' end as RecDate
				,case
					when TTMS.TimetableMedService_id is not null then 'TimetableMedService'
					when EQ.EvnQueue_id is not null then 'EvnQueue'
				else '' end as timetable
				,case
					when TTMS.TimetableMedService_id is not null  then TTMS.TimetableMedService_id
					--when EU.EvnUsluga_id is not null then EU.EvnUsluga_id
					when EQ.EvnQueue_id is not null then EQ.EvnQueue_id
				else '' end as timetable_id
				,EP.EvnPrescr_pid as timetable_pid
				,LU.LpuUnitType_SysNick
				,DT.DirType_Code
				,EP.MedService_id
				,UC.UslugaComplex_id
				,UC.UslugaComplex_2011id
				,UCMS.UslugaComplexMedService_id
				,case when exists(
					select top 1 ucms2.UslugaComplexMedService_id
					from v_UslugaComplexMedService ucms2 with (nolock)
						inner join lis.v_AnalyzerTest at2 (nolock) on at2.UslugaComplexMedService_id = ucms2.UslugaComplexMedService_id
						inner join lis.v_Analyzer a2 (nolock) on a2.Analyzer_id = at2.Analyzer_id
					where
						ucms2.UslugaComplexMedService_pid = UCMS.UslugaComplexMedService_id
						and ISNULL(at2.AnalyzerTest_IsNotActive, 1) = 1 and ISNULL(a2.Analyzer_IsNotActive, 1) = 1
				) then 1 else 0 end as isComposite
				,EP.EvnPrescr_CountComposit
				,composition.cnt as EvnPrescr_MaxCountComposit
				,{$UslugaComplex_Code}
				,{$UslugaComplex_Name}
				,null as TableUsluga_id
				,CASE 
					when EPLD.Lpu_id is not null and EPLD.Lpu_id <> :Lpu_id then 2 else 1
				end as otherMO
				,EPLD.Lpu_id as Lpu_id
				,EvnStatus.EvnStatus_SysNick
				,EUP.EvnUslugaPar_id
				,EP.StudyTarget_id
				,EPLD.UslugaComplexMedService_id as UslugaComplexMedService_pid
				,EPLD.MedService_pzmid
			from v_EvnPrescr EP with (nolock)
				inner join v_EvnPrescrLabDiag EPLD with (nolock) on EPLD.EvnPrescrLabDiag_id = EP.EvnPrescr_id
				left join v_UslugaComplex UC with (nolock) on UC.UslugaComplex_id = EPLD.UslugaComplex_id
				left join v_UslugaComplex UC11 with(nolock) on UC11.UslugaComplex_id = UC.UslugaComplex_2011id
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
						,ED.LpuSectionProfile_id
						,ED.DirType_id
						,ED.EvnStatus_id
						,ED.EvnDirection_statusDate
						,ED.DirFailType_id
						,ED.EvnDirection_failDT
						,ED.MedPersonal_id
					from v_EvnPrescrDirection epd with (nolock)
					inner join v_EvnDirection_all ED with (nolock) on epd.EvnDirection_id = ED.EvnDirection_id and ISNULL(ED.EvnStatus_id, 16) not in (12,13)
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
				-- сама служба
				left join v_MedService MS with (nolock) on MS.MedService_id = ED.MedService_id
				-- отделение для полки и стаца и для очереди
				left join v_LpuSection LS with (nolock) on LS.LpuSection_id = coalesce(ED.LpuSection_did, MS.LpuSection_id)
				-- заказанная услуга для параклиники
				outer apply (
					select top 1
						EUP.EvnUslugaPar_id
					from
						v_EvnUslugaPar EUP with (NOLOCK)
					where
						EUP.EvnDirection_id = ED.EvnDirection_id
						and EUP.EvnPrescr_id = EP.EvnPrescr_id
				) EUP 
				-- подразделение для очереди и служб
				left join v_LpuUnit LU with (nolock) on coalesce(ED.LpuUnit_did,EQ.LpuUnit_did,MS.LpuUnit_id) = LU.LpuUnit_id -- todo: в ED.LpuUnit_did пусто, чего не должно быть
				-- профиль для очереди
				left join v_LpuSectionProfile LSPD with (nolock) on coalesce(ED.LpuSectionProfile_id,EQ.LpuSectionProfile_did,LS.LpuSectionProfile_id) = LSPD.LpuSectionProfile_id -- todo: тут на примере оказалось что почему то ED.LpuSectionProfile_id != EQ.LpuSectionProfile_did, чего не должно быть
				-- тип направления
				left join v_DirType DT with (NOLOCK) on ED.DirType_id = DT.DirType_id
				-- ЛПУ
				left join v_Lpu Lpu with (nolock) on Lpu.Lpu_id = coalesce(ED.Lpu_did, LS.Lpu_id, MS.Lpu_id, EQ.Lpu_id)
				outer apply (
					select top 1 EvnUsluga_id, EvnUsluga_setDT from v_EvnUsluga with (nolock)
					where EP.EvnPrescr_IsExec = 2 and UC.UslugaComplex_id is not null and EvnPrescr_id = EP.EvnPrescr_id
				) EU
				left join v_EvnLabRequest LR with (nolock) on LR.EvnDirection_id = ED.EvnDirection_id
				-- услуга на службе
				left join v_UslugaComplexMedService UCMS (nolock) on UCMS.MedService_id = LR.MedService_id and UCMS.UslugaComplex_id = UC.UslugaComplex_id and UCMS.UslugaComplexMedService_pid is null
				outer apply (
					select COUNT(ucoa.UslugaComplex_id) as cnt
					from v_UslugaComplexMedService ucmsoa with (nolock)
						inner join v_UslugaComplex ucoa with (nolock) on ucmsoa.UslugaComplex_id = ucoa.UslugaComplex_id
						inner join lis.v_AnalyzerTest lat with (nolock) on lat.UslugaComplexMedService_id = ucmsoa.UslugaComplexMedService_id
						inner join v_UslugaComplex ucato with (nolock) on ucato.UslugaComplex_id = lat.UslugaComplex_id
					where ucmsoa.UslugaComplexMedService_pid = case when UCMS.UslugaComplexMedService_id is null then EPLD.UslugaComplexMedService_id else UCMS.UslugaComplexMedService_id end
						and isnull(lat.AnalyzerTest_endDT, @cur_date) >= @cur_date
						and isnull(ucato.UslugaComplex_endDT, @cur_date) >= @cur_date
				) composition
				{$addJoin}
			where
				EP.EvnPrescr_pid  = :EvnPrescr_pid
				and EP.PrescriptionType_id = 11
				and EP.PrescriptionStatusType_id != 3
				{$filter}
			order by
				EP.EvnPrescr_id,
				EP.EvnPrescr_setDT
		";
		$queryParams = array(
			'EvnPrescr_pid' => $evn_pid,
			'Lpu_id' => $sessionParams['lpu_id'],
			'MedPersonal_id' => $sessionParams['medpersonal_id'],
		);
		//echo '<pre>',print_r(getDebugSql($query, $queryParams)),'</pre>'; die();
		//echo getDebugSql($query, $queryParams);exit;
		$result = $this->db->query($query, $queryParams);
		if (is_object($result)) {
			$tmp_arr = $result->result('array');
			$response = array();
			foreach ($tmp_arr as $i => $row) {
				$row['UslugaId_List'] = $row['UslugaComplex_id'];
				if ($this->options['prescription']['enable_show_service_code']) {
					$row['Usluga_List'] = $row['UslugaComplex_Code'].' '.$row['UslugaComplex_Name'];
				} else {
					$row['Usluga_List'] = $row['UslugaComplex_Name'];
				}
				$row[$section . '_id'] = $row['EvnPrescr_id'].'-0';
				$response[] = $row;
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
			foreach ($response as $key => $row) {
				$response[$key]['EvnLabSampleDefect'] = $this->getEvnLabSamplesDefect(array('EvnPrescr_id' => $row['EvnPrescr_id']));
			}
			return $response;
		} else {
			return false;
		}
	}

	/**
	 * Получение данных для панели просмотра ЭМК и правой панели формы добавления назначений
	 * @param string $section
	 * @param int $evn_pid
	 * @param array $sessionParams
	 * @return array|false
	 */
	public function doLoadViewDataPostgres($section, $evn_pid, $sessionParams) {
		$sysnick = swPrescription::getParentEvnClassSysNickBySectionName($section);
		$addJoin = '';
		$accessType = "'view' as accessType";

		if ($sysnick) {
			$accessType = "case when {$sysnick}.Lpu_id = :Lpu_id
			AND isnull({$sysnick}.{$sysnick}_IsSigned,1) = 1
			AND ISNULL(EP.EvnPrescr_IsExec, 1) = 1
			then 'edit' else 'view' end as accessType";

			$addJoin = "left join v_{$sysnick} {$sysnick} with (nolock) on {$sysnick}.{$sysnick}_id = EP.EvnPrescr_pid";
		}

		$UslugaComplex_Code = 'UC.UslugaComplex_Code';
		$UslugaComplex_Name = 'coalesce(UCMS.UslugaComplex_Name, UC.UslugaComplex_Name) as UslugaComplex_Name';

		if (!empty($this->options['prescription']['enable_grouping_by_gost2011']) || $this->options['prescription']['service_name_show_type'] == 2) {
			$UslugaComplex_Code = 'UC11.UslugaComplex_Code';
			$UslugaComplex_Name = 'UC11.UslugaComplex_Name';
		}

		$query = "
			select
				{$accessType},
				EP.EvnPrescr_id,
				'EvnPrescrLabDiag' as object,
				EP.EvnPrescr_pid,
				EP.EvnPrescr_rid,
				convert(varchar,EP.EvnPrescr_setDT,104) as EvnPrescr_setDate,
				null as EvnPrescr_setTime,
				isnull(EP.EvnPrescr_IsExec,1) as EvnPrescr_IsExec,
				case when 2 = EP.EvnPrescr_IsExec
					then convert(varchar,EP.EvnPrescr_updDT,104)+' '+convert(varchar,EP.EvnPrescr_updDT,108) else null
				end as EvnPrescr_execDT,
				EP.EvnPrescr_setDT,
				EP.PrescriptionStatusType_id,
				EP.PrescriptionType_id,
				EP.PrescriptionType_id as PrescriptionType_Code,
				isnull(EP.EvnPrescr_IsCito,1) as EvnPrescr_IsCito,
				isnull(EP.EvnPrescr_Descr,'') as EvnPrescr_Descr,
				1 as EvnPrescr_IsDir,
				'Отсутствует направление' as Usluga_List,
				EP.EvnPrescr_pid as timetable_pid,
				EP.MedService_id,
				EP.EvnPrescr_CountComposit,
				composition.cnt as EvnPrescr_MaxCountComposit,
				null as TableUsluga_id,
				epd.EvnDirection_id,
				EP.Lpu_id as Lpu_id,
				CASE 
					when EP.Lpu_id is not null and EP.Lpu_id <> :Lpu_id then 2 else 1
				end as otherMO,
				:MedPersonal_id as MedPersonal_id,
				EPLD.UslugaComplex_id,
				{$UslugaComplex_Code},
				{$UslugaComplex_Name},
				ttms.TimetableMedService_id,
			    ttms.TimetableMedService_begTime
				,EPLD.UslugaComplexMedService_id as UslugaComplexMedService_pid
				,EPLD.MedService_pzmid
			from v_EvnPrescr EP with (nolock)
				inner join EvnPrescrLabDiag EPLD with (nolock) on EPLD.EvnPrescrLabDiag_id = EP.EvnPrescr_id
				outer apply (
					select top 1 *
					from v_EvnPrescrDirection epd WITH (nolock)
					where epd.EvnPrescr_id = ep.EvnPrescr_id
				) epd
				left join v_UslugaComplex UC with (nolock) on UC.UslugaComplex_id = EPLD.UslugaComplex_id
				left join v_UslugaComplex UC11 with(nolock) on UC11.UslugaComplex_id = UC.UslugaComplex_2011id
				outer apply (
					Select top 1 
						ED.EvnDirection_id,
						ED.MedService_id
					from v_EvnPrescrDirection epd with (nolock)
					inner join v_EvnDirection_all ED with (nolock) on epd.EvnDirection_id = ED.EvnDirection_id and ISNULL(ED.EvnStatus_id, 16) not in (12,13)
					where epd.EvnPrescr_id = EP.EvnPrescr_id
					order by 
						case when ISNULL(ED.EvnStatus_id, 16) in (12,13) then 2 else 1 end /* первым неотмененное/неотклоненное направление */
						,epd.EvnPrescrDirection_insDT desc
				) ED
				left join v_UslugaComplexMedService UCMS (nolock) on UCMS.MedService_id = ED.MedService_id and UCMS.UslugaComplex_id = UC.UslugaComplex_id and UCMS.UslugaComplexMedService_pid is null
				outer apply(
					Select top 1
						TimetableMedService_id,
						TimetableMedService_begTime
					from
						v_TimetableMedService_lite TTMS with (nolock)
					where
						TTMS.EvnDirection_id = epd.EvnDirection_id
			  	) ttms
			  	outer apply (
					select COUNT(ucoa.UslugaComplex_id) as cnt
					from v_UslugaComplexMedService ucmsoa with (nolock)
					inner join v_UslugaComplex ucoa with (nolock) on ucmsoa.UslugaComplex_id = ucoa.UslugaComplex_id
					inner join lis.v_AnalyzerTest lat with (nolock) on lat.UslugaComplexMedService_id = ucmsoa.UslugaComplexMedService_id
					where ucmsoa.UslugaComplexMedService_pid = case when UCMS.UslugaComplexMedService_id is null then EPLD.UslugaComplexMedService_id else UCMS.UslugaComplexMedService_id end
				) composition
				{$addJoin}
			where
				EP.EvnPrescr_pid  = :EvnPrescr_pid
				and EP.PrescriptionType_id = 11
				and EP.PrescriptionStatusType_id != 3
			order by
				EP.EvnPrescr_id,
				EP.EvnPrescr_setDT		
		";

		$queryParams = array(
			'EvnPrescr_pid' => $evn_pid,
			'Lpu_id' => $sessionParams['lpu_id'],
			'MedPersonal_id' => $sessionParams['medpersonal_id'],
		);

		$tmp_arr = $this->queryResult($query, $queryParams);
		if (!is_array($tmp_arr)) {
			return false;
		}

		$EvnPrescrList = array();
		$listByDirection = array();
		foreach($tmp_arr as $idx => $item) {
			if (!empty($item['EvnPrescr_id'])) {
				$EvnPrescrList[] = $item;
				$listByDirection[$item['EvnPrescr_id']] = $item;
			} else {
				$listByDirection[-$idx] = $item;
			}
		}
		if (count($EvnPrescrList) > 0) {
			if ($this->usePostgreLis) {
				$resp_lis = $this->lis->POST('EvnDirection/loadView', array(
					'EvnPrescrList' => $EvnPrescrList,
					'sysnick' => $sysnick
				), 'list');
				if (!$this->isSuccessful($resp_lis)) {
					return $resp_lis;
				}
			}
			/**
			 * sysnick - EvnVizitPL
			 * options_precription_enable_grouping_by_gost2011_empty - 1
			 * evn_pid - 590930000100953
			 * lpu_id - 10010833
			 * medpersonal_id - 41
			 * tmp_arr: нет информации по очереди
			 * resp_lis: получает назначения с направлениями
			 * resp: пустой массив
			 */
			if (is_array($resp_lis)) {
				$this->load->model('EvnDirection_model');
				$resp = $this->EvnDirection_model->doLoadView([
					'EvnPrescrList' => $EvnPrescrList,
					'sysnick' => $sysnick,
					'excepts' => $resp_lis
				]);
				if (!is_array($resp)) {
					return $this->createError('','Ошибка при получении данных направлений');
				}
				$resp = array_merge($resp, $resp_lis);
			}
			foreach($resp as $item) {
				$key = $item['EvnPrescr_id'];
				if (!isset($listByDirection[$key])) {
					continue;
				}

				if ($item['EvnLabRequestStatus'] != 1) {
					$item['accessType'] = 'view';
				}
				if ($this->options['prescription']['enable_show_service_code']) {
					$item['Usluga_List'] = $item['UslugaComplex_Code'].' '.$item['UslugaComplex_Name'];
				} else {
					$item['Usluga_List'] = $item['UslugaComplex_Name'];
				}

				$listByDirection[$key] = array_merge($listByDirection[$key], $item);
			}
		}

		$tmp_arr = array();
		$evnPrescrIdList = array();
		foreach ($listByDirection as $key => &$item) {
			unset($item['EvnPrescr_setDT']);
			unset($item['TimeTableMedService_id']);
			unset($item['TimetableMedService_begTime']);

			$item[$section . '_id'] = $item['EvnPrescr_id'].'-0';

			if (!empty($item['EvnPrescr_IsExec']) && !empty($item['EvnPrescr_IsHasEvn']) && $item['EvnPrescr_IsExec'] == 2 && $item['EvnPrescr_IsHasEvn'] == 2) {
			    $item['EvnXml_id'] = null;
				if (!empty($item['EvnPrescr_id'])) {
					$id = $item['EvnPrescr_id'];
					$evnPrescrIdList[] = $id;
					$tmp_arr[$id] = $key;
				}
			}
		}
		unset($item);

		//загружаем документы
		if (count($evnPrescrIdList) > 0) {
			$evnPrescrIdList = implode(',',$evnPrescrIdList);
			$query = "
				WITH EvnPrescrEvnXml as (
					select doc.EvnXml_id, EU.EvnPrescr_id
					from  v_EvnUsluga EU (nolock)
					inner join v_EvnXml doc (nolock) on doc.Evn_id = EU.EvnUsluga_id
					where EU.EvnPrescr_id in ({$evnPrescrIdList})
				)
				select EvnXml_id, EvnPrescr_id 
				from EvnPrescrEvnXml with(nolock)
				order by EvnPrescr_id
			";
			$evnPrescrIdList = $this->queryResult($query);
			if (!is_array($evnPrescrIdList)) {
				return false;
			}
			if (!empty($evnPrescrIdList)) {
				foreach ($evnPrescrIdList as $item) {
					$id = $item['EvnPrescr_id'];
					if (isset($tmp_arr[$id])) {
						$key = $tmp_arr[$id];
						if (isset($listByDirection[$key])) {
							$listByDirection[$key]['EvnXml_id'] = $item['EvnXml_id'];
						}
					}
				}
			} else {
				foreach ($listByDirection as $key => $value) {
					if (!empty($value['EvnUslugaPar_id'])) {
						$query = "
						select top 1
							EvnXml_id
						from
							v_EvnXml (nolock)
						where
							Evn_id = :EvnUslugaPar_id	
					";
						$evnXml_id = $this->getFirstResultFromQuery($query, $value);
						if (!empty($evnXml_id))
							$listByDirection[$key]['EvnXml_id'] = $evnXml_id;
					}
				}



			}

		}

		return array_values($listByDirection);
	}

	/**
	* возвращает бракованные пробы по номеру назначения
	*/
	function getEvnLabSamplesDefect($data) {
		if (!isset($data['EvnPrescr_id']) || empty($data['EvnPrescr_id'])) {
			return false;
		}

		$query = "
			select 
				ELS.EvnLabSample_id,
				DCT.DefectCauseType_Name
			from v_EvnPrescrLabDiag EPLD with (nolock)
			inner join v_EvnPrescrDirection EPD with (nolock) on EPD.EvnPrescr_id = EPLD.EvnPrescrLabDiag_id
			inner join v_EvnLabRequest ELR with (nolock) on ELR.EvnDirection_id = EPD.EvnDirection_id
			inner join v_EvnLabSample ELS (nolock) on ELS.EvnLabRequest_id = ELR.EvnLabRequest_id
			inner join lis.v_DefectCauseType DCT (nolock) on DCT.DefectCauseType_id = ELS.DefectCauseType_id
			where 
				EPLD.EvnPrescrLabDiag_id = :EvnPrescr_id and 
				ELS.LabSampleStatus_id = 5
		";
		$queryParams = array(
			'EvnPrescr_id' => $data['EvnPrescr_id']
		);
		//echo getDebugSql($query, $queryParams);exit;
		$result = $this->db->query($query, $queryParams);
		if ( is_object($result) ) {
			return $result->result('array');
		}
	}

	/**/
	function getEvnPrescrLabDiagDescr($data) {
		$res = $this->queryResult("
			select
				epr.EvnPrescrLabDiag_Descr
			from
				v_EvnUslugaPar eup (nolock)
				left join v_EvnPrescrLabDiag epr (nolock) on epr.EvnPrescrLabDiag_id = eup.EvnPrescr_id
			where 
				eup.EvnDirection_id = :EvnDirection_id
		", $data);

		return $res;
	}
}
