<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * StickFSSData_model - модель для работы с запросами ЭВН
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package     Mse
 * @access      public
 * @copyright   Copyright (c) 2017 Swan Ltd.
 * @author		Dmitrii Vlasenko
 * @version     18.08.2017
 */

class StickFSSData_model extends swModel {
	/**
	 *	Конструктор
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * Получение списка
	 * @param $data
	 * @return array|bool
	 */
	public function loadStickFSSDataGrid($data) {
		$params = array();
		$filters = "(1=1)";

		if (!empty($data['Lpu_id'])) {
			$filters .= " and SFD.Lpu_id = :Lpu_id";
			$params['Lpu_id'] = $data['Lpu_id'];
		} else {
			return array();
		}

		if (!empty($data['StickFSSData_DateRange'][0])) {
			$filters .= " and cast(SFD.StickFSSData_insDT as date) >= :StickFSSData_Date1";
			$params['StickFSSData_Date1'] = $data['StickFSSData_DateRange'][0];
		}

		if (!empty($data['StickFSSData_DateRange'][1])) {
			$filters .= " and cast(SFD.StickFSSData_insDT as date) <= :StickFSSData_Date2";
			$params['StickFSSData_Date2'] = $data['StickFSSData_DateRange'][1];
		}

		$query = "
			select
				SFD.StickFSSData_id,
				L.Lpu_id,
				L.Lpu_Nick,
				L.Lpu_FSSRegNum,
				O.Org_INN as Lpu_INN,
				SFD.Lpu_OGRN,
				PS.Person_Snils,
				SFD.StickFSSDataStatus_id,
				SFD.StickFSSData_Num,
				SFT.StickFSSType_Code,
				SFT.StickFSSType_Name,
				SFD.StickFSSData_IsNeedMSE,
				case 
					when SFD.StickFSSData_IsNeedMSE = 2 
					then 'Запросы в ФСС, требующие данные о МСЭ' 
					else 'Остальные запросы в ФСС'
				end as StickFSSData_IsNeedMSE_StatusName,
				convert(varchar(10), SFD.StickFSSData_insDT, 104) + ' ' + convert(varchar(5), SFD.StickFSSData_insDT, 108) as StickFSSData_insDT,
				puc.pmUser_Name,
				case
					when EvnPL.EvnPL_id is not null then 'ТАП'
					when EvnPLStom.EvnPLStom_id is not null then 'Стом. ТАП'
					when EvnPS.EvnPS_id is not null then 'КВС'
					else ''
				end as EvnStick_ParentTypeName,
				case
					when EvnPL.EvnPL_id is not null then EvnPL.EvnPL_NumCard
					when EvnPLStom.EvnPLStom_id is not null then EvnPLStom.EvnPLStom_NumCard
					when EvnPS.EvnPS_id is not null then EvnPS.EvnPS_NumCard
					else ''
				end as EvnStick_ParentNum,
				PS.Person_SurName + ' '+ isnull(PS.Person_FirName,'') + ' ' + isnull(PS.Person_SecName,'') as Person_Fio,
				SFD.StickFSSData_StickNum,
				convert(varchar(10), SFD.StickFSSData_xmlExpDT, 104) + ' ' + convert(varchar(5), SFD.StickFSSData_xmlExpDT, 108) as StickFSSData_xmlExpDT,
				SFDS.StickFSSDataStatus_Name,
				case when exists (
					select top 1
						SFE.StickFSSError_id
					from
						v_StickFSSError SFE with (nolock)
						inner join v_StickFSSDataGet SFDG with (nolock) on SFE.StickFSSDataGet_id = SFDG.StickFSSDataGet_id
					where
						SFDG.StickFSSData_id = SFD. StickFSSData_id
				) then 'Да' else 'Нет' end as StickFSSData_hasErrors
			from
				v_StickFSSData SFD with (nolock)
				outer apply(
					select top 1 
						SFT.StickFSSType_Name,
						SFT.StickFSSType_Code
					from 
						v_EvnStickBase ESB with (nolock)
						inner join v_StickFSSType SFT with (nolock) on SFT.StickFSSType_id = ESB.StickFSSType_id
					where
						ESB.EvnStickBase_id = SFD.EvnStickBase_id
				) as SFT
				left join v_pmUserCache puc with (nolock) on puc.pmUser_id = SFD.pmUser_insID
				left join v_StickFSSDataStatus SFDS with (nolock) on SFDS.StickFSSDataStatus_id = SFD.StickFSSDataStatus_id
				left join v_EvnStickBase ESB with (nolock) on ESB.StickFSSData_id = SFD.StickFSSData_id
				left join v_PersonState PS with (nolock) on PS.Person_id = SFD.Person_id
				left join v_EvnPL EvnPL with (nolock) on ESB.EvnStickBase_pid = EvnPL.EvnPL_id
				left join v_EvnPLStom EvnPLStom with (nolock) on ESB.EvnStickBase_pid = EvnPLStom.EvnPLStom_id
				left join v_EvnPS EvnPS with (nolock) on ESB.EvnStickBase_pid = EvnPS.EvnPS_id
				left join v_Lpu L with (nolock) on L.Lpu_id = SFD.Lpu_id
				left join v_Org O with (nolock) on O.Org_id = L.Org_id
			where
				{$filters}
		";
		//echo getDebugSQL($query, $params);exit;
		return $this->queryResult($query, $params);
	}

	/**
	 * Получение списка
	 * @param $data
	 * @return array|bool
	 */
	public function loadStickFSSDataGetGrid($data) {
		$query = "
			select
				ISNULL(SFDG.Person_SurName, '') + ISNULL(' ' + SFDG.Person_FirName, '') + ISNULL(' ' + SFDG.Person_SecName, '') as Person_Fio,
				convert(varchar(10), SFDG.Person_BirthDay, 104) as Person_BirthDay,
				SFDG.Person_Snils,
				SFDG.StickFSSDataGet_StickNum,
				convert(varchar(10), SFDG.StickFSSDataGet_StickSetDate, 104) as StickFSSDataGet_StickSetDate,
				SFDG.Lpu_StickNick,
				SFDG.StickFSSDataGet_StickReason,
				convert(varchar(10), SFDG.StickFSSDataGet_FirstBegDate, 104) as StickFSSDataGet_FirstBegDate,
				convert(varchar(10), SFDG.StickFSSDataGet_FirstEndDate, 104) as StickFSSDataGet_FirstEndDate,
				convert(varchar(10), SFDG.StickFSSDataGet_SecondEndDate, 104) as StickFSSDataGet_SecondEndDate,
				convert(varchar(10), SFDG.StickFSSDataGet_ThirdEndDate, 104) as StickFSSDataGet_ThirdEndDate,
				SFDG.StickFSSDataGet_StickResult,
				convert(varchar(10), SFDG.StickFSSDataGet_changeDate, 104) as StickFSSDataGet_changeDate,
				convert(varchar(10), SFDG.StickFSSDataGet_workDate, 104) as StickFSSDataGet_workDate,
				convert(varchar(10), SFDG.EvnStick_mseDT, 104) as EvnStick_mseDT,
				convert(varchar(10), SFDG.EvnStick_mseRegDT, 104) as EvnStick_mseRegDT,
				convert(varchar(10), SFDG.EvnStick_mseExamDT, 104) as EvnStick_mseExamDT,
				SFDG.InvalidGroupType_id
			from
				v_StickFSSDataGet SFDG with (nolock)
			where
				SFDG.StickFSSData_id = :StickFSSData_id
		";

		$resp = $this->queryResult($query, array(
			'StickFSSData_id' => $data['StickFSSData_id']
		));

		$response = array();

		if (!empty($resp[0])) {
			$StickFSSDataGetGrid_id = 1;
			$response[] = array(
				'StickFSSDataGetGrid_id' => $StickFSSDataGetGrid_id++,
				'StickFSSDataGet_Field' => 'ФИО',
				'StickFSSDataGet_Value' => $resp[0]['Person_Fio']
			);
			$response[] = array(
				'StickFSSDataGetGrid_id' => $StickFSSDataGetGrid_id++,
				'StickFSSDataGet_Field' => 'Дата рождения',
				'StickFSSDataGet_Value' => $resp[0]['Person_BirthDay']
			);
			$response[] = array(
				'StickFSSDataGetGrid_id' => $StickFSSDataGetGrid_id++,
				'StickFSSDataGet_Field' => 'СНИЛС',
				'StickFSSDataGet_Value' => $resp[0]['Person_Snils']
			);
			$response[] = array(
				'StickFSSDataGetGrid_id' => $StickFSSDataGetGrid_id++,
				'StickFSSDataGet_Field' => 'Номер ЭЛН',
				'StickFSSDataGet_Value' => $resp[0]['StickFSSDataGet_StickNum']
			);
			$response[] = array(
				'StickFSSDataGetGrid_id' => $StickFSSDataGetGrid_id++,
				'StickFSSDataGet_Field' => 'Дата выдачи',
				'StickFSSDataGet_Value' => $resp[0]['StickFSSDataGet_StickSetDate']
			);
			$response[] = array(
				'StickFSSDataGetGrid_id' => $StickFSSDataGetGrid_id++,
				'StickFSSDataGet_Field' => 'МО',
				'StickFSSDataGet_Value' => $resp[0]['Lpu_StickNick']
			);
			$response[] = array(
				'StickFSSDataGetGrid_id' => $StickFSSDataGetGrid_id++,
				'StickFSSDataGet_Field' => 'Причина нетрудоспособности',
				'StickFSSDataGet_Value' => $resp[0]['StickFSSDataGet_StickReason']
			);

			$Period = $resp[0]['StickFSSDataGet_FirstBegDate'] . ' - ';
			if (!empty($resp[0]['StickFSSDataGet_ThirdEndDate'])) {
				$Period .= $resp[0]['StickFSSDataGet_ThirdEndDate'];
			} else if (!empty($resp[0]['StickFSSDataGet_SecondEndDate'])) {
				$Period .= $resp[0]['StickFSSDataGet_SecondEndDate'];
			} else {
				$Period .= $resp[0]['StickFSSDataGet_FirstEndDate'];
			}

			$response[] = array(
				'StickFSSDataGetGrid_id' => $StickFSSDataGetGrid_id++,
				'StickFSSDataGet_Field' => 'Общий период освобождения от работы',
				'StickFSSDataGet_Value' => $Period
			);
			$response[] = array(
				'StickFSSDataGetGrid_id' => $StickFSSDataGetGrid_id++,
				'StickFSSDataGet_Field' => 'Исход ЭЛН (поле «Иное»)',
				'StickFSSDataGet_Value' => $resp[0]['StickFSSDataGet_StickResult']
			);
			$response[] = array(
				'StickFSSDataGetGrid_id' => $StickFSSDataGetGrid_id++,
				'StickFSSDataGet_Field' => 'Дата исхода ЭЛН',
				'StickFSSDataGet_Value' => $resp[0]['StickFSSDataGet_changeDate']
			);
			$response[] = array(
				'StickFSSDataGetGrid_id' => $StickFSSDataGetGrid_id++,
				'StickFSSDataGet_Field' => 'Приступить к работе',
				'StickFSSDataGet_Value' => $resp[0]['StickFSSDataGet_workDate']
			);
			$response[] = array(
				'StickFSSDataGetGrid_id' => $StickFSSDataGetGrid_id++,
				'StickFSSDataGet_Field' => 'Дата направления в бюро МСЭ',
				'StickFSSDataGet_Value' => $resp[0]['EvnStick_mseDT']
			);
			$response[] = array(
				'StickFSSDataGetGrid_id' => $StickFSSDataGetGrid_id++,
				'StickFSSDataGet_Field' => 'Дата регистрации документов в бюро МСЭ',
				'StickFSSDataGet_Value' => $resp[0]['EvnStick_mseRegDT']
			);
			$response[] = array(
				'StickFSSDataGetGrid_id' => $StickFSSDataGetGrid_id++,
				'StickFSSDataGet_Field' => 'Дата освидетельствования в бюро МСЭ',
				'StickFSSDataGet_Value' => $resp[0]['EvnStick_mseExamDT']
			);
			$response[] = array(
				'StickFSSDataGetGrid_id' => $StickFSSDataGetGrid_id++,
				'StickFSSDataGet_Field' => 'Установлена/изменена группа инвалидности',
				'StickFSSDataGet_Value' => $resp[0]['InvalidGroupType_id']
			);
		}

		return $response;
	}

	/**
	 * Получение списка расхождений
	 * @param $data
	 * @return array|bool
	 */
	public function loadStickFSSErrorGrid($data) {
		$query = "
			select
				SFE.StickFSSError_id,
				SFET.StickFSSErrorType_Code,
				SFET.StickFSSErrorType_Name,
				SFDG.StickFSSDataGet_StickNum
			from
				v_StickFSSError SFE (nolock)
				left join v_StickFSSErrorType SFET (nolock) on SFET.StickFSSErrorType_id = SFE.StickFSSErrorType_id
				left join v_StickFSSDataGet SFDG (nolock) on SFDG.StickFSSDataGet_id = SFE.StickFSSDataGet_id
			where
				SFDG.StickFSSData_id = :StickFSSData_id
		";

		return $this->queryResult($query, array(
			'StickFSSData_id' => $data['StickFSSData_id']
		));
	}

	/**
	 * Экспорт запроса ЭЛН в xml
	 */
	function exportStickFSSDataToXml($data) {
		$this->load->library('parser');

		$resp = $this->queryResult("
			select
				SFD.StickFSSData_id,
				SFD.Lpu_OGRN,
				SFD.StickFSSData_StickNum,
				PS.Person_Snils
			from
				v_StickFSSData SFD (nolock)
				left join v_PersonState PS with (nolock) on PS.Person_id = SFD.Person_id
			where
				SFD.StickFSSData_id = :StickFSSData_id
		", array(
			'StickFSSData_id' => $data['StickFSSData_id']
		));

		if (empty($resp[0]['StickFSSData_id'])) {
			return array('Error_Msg' => 'Ошибка получения данных по запросу ЭЛН');
		}

		$path = EXPORTPATH_ROOT . "registry_es/";

		if (!file_exists($path)) {
			mkdir($path);
		}

		$out_dir = "stickfssdata_" . $data['Lpu_id'] . "_" . time();
		if (!file_exists($path . $out_dir)) {
			mkdir($path . $out_dir);
		}

		$this->load->helper('openssl');
		$certAlgo = getCertificateAlgo($data['certbase64']);

		$xml_data = array(
			'ogrn' => $resp[0]['Lpu_OGRN'],
			'lnCode' => $resp[0]['StickFSSData_StickNum'],
			'snils' => $resp[0]['Person_Snils'],
			'filehash' => (!empty($data['filehash']) ? $data['filehash'] : ''),
			'filesign' => (!empty($data['filesign']) ? $data['filesign'] : ''),
			'certbase64' => (!empty($data['certbase64']) ? $data['certbase64'] : ''),
			'signatureMethod' => $certAlgo['signatureMethod'],
			'digestMethod' => $certAlgo['digestMethod']
		);

		$template = 'get_ln_data';
		$xml = "<?xml version='1.0' encoding='UTF-8' standalone='no'?>\n" . $this->parser->parse('export_xml/' . $template, $xml_data, true);

		// подписываем и отправляем
		$dir = $path . $out_dir;
		$tempfile = $dir . "/tmp.txt";
		$doc = new DOMDocument();
		$xml = preg_replace('/\r\n/u', "\n", $xml);
		$doc->loadXML($xml);

		// сохраняем XML
		$xml = $doc->saveXML();

		if (!empty($data['needHash'])) {
			$doc = new DOMDocument();
			$doc->loadXML($xml);
			$toHash = $doc->getElementsByTagName('Body')->item(0)->C14N(true, false);
			// считаем хэш
			$cryptoProHash = getCryptCpHash($toHash, $data['certbase64']);
			// 2. засовываем хэш в DigestValue
			$doc->getElementsByTagName('DigestValue')->item(0)->nodeValue = $cryptoProHash;
			// 3. считаем хэш по SignedInfo
			$toSign = $doc->getElementsByTagName('SignedInfo')->item(0)->C14N(true, false);
			$Base64ToSign = base64_encode($toSign);

			return array('Error_Msg' => '', 'xml' => $xml, 'Base64ToSign' => $Base64ToSign, 'Hash' => $cryptoProHash);
		} else {
			return array('Error_Msg' => '', 'xml' => $xml);
		}
	}

	/**
	 * Получение номера для нового ЛВН
	 * @param $data
	 * @return array|bool
	 */
	function getNewStickFSSDataNum($data) {
		$filters = "";
		$params = array(
			'Lpu_id' => $data['Lpu_id']
		);

		if (!empty($data['StickFSSData_id'])) {
			$filters .= " and SFD.StickFSSData_id <> :StickFSSData_id";
			$params['StickFSSData_id'] = $data['StickFSSData_id'];
		}

		$query = "
			select
				isnull(max(convert(int, StickFSSData_Num)), 0)+1 as StickFSSData_Num,
				MAX(O.Org_OGRN) as Lpu_OGRN
			from 
				v_Lpu L with (nolock)
				left join v_Org O with (nolock) on O.Org_id = L.Org_id
				left join v_StickFSSData SFD with (nolock) on SFD.Lpu_id = L.Lpu_id
			where
				L.Lpu_id = :Lpu_id
				{$filters}
		";

		$result = $this->db->query($query, $params);
		if (!is_object($result)) {
			return false;
		}
		$response = $result->result('array');
		return array(
			'success' => true,
			'StickFSSData_Num' => $response[0]['StickFSSData_Num'],
			'Lpu_OGRN' => $response[0]['Lpu_OGRN']
		);
	}

	/**
	 * Формирование реестра ЛВН
	 * @param $data
	 * @return bool
	 */
	function saveStickFSSData($data) {
		if (empty($data['StickFSSData_id'])) {
			$data['StickFSSData_id'] = null;
			$procedure = 'p_StickFSSData_ins';
		} else {
			$procedure = 'p_StickFSSData_upd';
		}

		$warnExist = null;
		if (empty($data['ignoreCheckExist'])) {
			// проверка на наличие в БД Промеда (в пределах региона) ЛВН с указанным в запросе номером
			$resp = $this->queryResult("
				select top 1
					ESB.EvnStickBase_id,
					RESS.RegistryESStorage_id,
					PS.Person_SurName + ' '+ isnull(PS.Person_FirName,'') + ' ' + isnull(PS.Person_SecName,'') as Person_Fio,
					convert(varchar(10), PS.Person_BirthDay, 104) as Person_BirthDay
				from
					v_EvnStickBase ESB (nolock)
					left join v_RegistryESStorage RESS (nolock) on RESS.EvnStickBase_id = ESB.EvnStickBase_id
					left join v_PersonState PS (nolock) on PS.Person_id = ESB.Person_id
				where
					ESB.EvnStickBase_Num = :StickFSSData_StickNum
			", array(
				'StickFSSData_StickNum' => $data['StickFSSData_StickNum']
			));

			if (!empty($resp[0]['EvnStickBase_id'])) {
				if (!empty($resp[0]['RegistryESStorage_id'])) {
					// ЭЛН с таким номером уже заведён на пациента <ФИО>, <дата рождения>
					return array('Error_Msg' => 'ЭЛН с таким номером уже заведён на пациента ' . $resp[0]['Person_Fio'] . ', ' . $resp[0]['Person_BirthDay']);
				} else {
					// Неэлектронный ЛВН с таким номером уже заведён на пациента <ФИО>, <дата рождения>
					$warnExist = 'Неэлектронный ЛВН с таким номером уже заведён на пациента ' . $resp[0]['Person_Fio'] . ', ' . $resp[0]['Person_BirthDay'];
				}
			}
		}

		$query = "
			declare
				@StickFSSData_id bigint = :StickFSSData_id,
				@Error_Code int,
				@Error_Message varchar(4000); 
			
			exec {$procedure}
				@StickFSSData_id = @StickFSSData_id output,
				@StickFSSData_Num = :StickFSSData_Num,
				@Lpu_OGRN = :Lpu_OGRN,
				@Person_id = :Person_id,
				@StickFSSData_StickNum = :StickFSSData_StickNum,
				@Lpu_id = :Lpu_id,
				@StickFSSDataStatus_id = 1, -- Ожидает отправки
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code OUTPUT,
				@Error_Message = @Error_Message OUTPUT
			select @StickFSSData_id as StickFSSData_id,@Error_Code as Error_Code, @Error_Message as Error_Msg
		";

		$resp = $this->queryResult($query, $data);

		if (!empty($resp[0]['StickFSSData_id'])) {
			$resp[0]['EvnStick_Num'] = $data['StickFSSData_StickNum'];
			// надо ещё получить данные по выбранному пациенту и вернуть их на клиент, необходимо для добавления нового ЛВН
			$resp_pers = $this->queryResult("
				select
					Person_Surname,
					Person_Firname,
					Person_Secname,
					Person_id,
					PersonEvn_id,
					Server_id
				from
					v_PersonState ps (nolock)
				where
					ps.Person_id = :Person_id
			", array(
				'Person_id' => $data['Person_id']
			));

			if (empty($resp_pers[0]['Person_id'])) {
				return array('Error_Msg' => 'Ошибка получения данных по пациенту');
			}

			$resp[0] = array_merge($resp[0], $resp_pers[0]);

			$this->load->model('Messages_model');
			// получаем пользователей данной МО с группами:
			// - пользователь АРМ администратора МО;
			// - пользователь АРМ медицинского статистика.
			$resp_users = $this->queryResult("
				DECLARE
					@pmUserCacheGroup_id bigint = (select top 1 pmUserCacheGroup_id from dbo.pmUserCacheGroup where pmUserCacheGroup_SysNick = 'LpuAdmin')
				
				select
					pmUser_id
				from
					v_pmUserCache puc (nolock)
				where
					puc.Lpu_id = :Lpu_id
					and exists(select top 1 pucgl.pmUserCacheGroupLink_id from v_pmUserCacheGroupLink pucgl with (nolock) where pucgl.pmUserCacheGroup_id = @pmUserCacheGroup_id and pucgl.pmUserCache_id = puc.PMUser_id) -- АРМ администратора МО
					and ISNULL(puc.pmUser_deleted, 1) = 1
					and puc.pmUser_EvnClass like '%StickFSSData%'
					
				union
				
				select
					pmUser_id
				from
					v_pmUserCache puc (nolock)
					inner join v_MedServiceMedPersonal msmp (nolock) on msmp.MedPersonal_id = puc.MedPersonal_id
					inner join v_MedService ms (nolock) on ms.MedService_id = msmp.MedService_id
					inner join v_MedServiceType mst (nolock) on mst.MedServiceType_id = ms.MedServiceType_id
				where
					puc.Lpu_id = :Lpu_id
					and mst.MedServiceType_SysNick = 'mstat' -- АРМ медицинского статистика
					and ISNULL(puc.pmUser_deleted, 1) = 1
					and puc.pmUser_EvnClass like '%StickFSSData%'
			", array(
				'Lpu_id' => $data['Lpu_id']
			));

			if (!empty($resp_users)) {
				$user = getUser();
				$message = "Пользователь, создавший запрос: " . ($user->name) . "." . PHP_EOL . PHP_EOL;
				$message .= "Дата и время создания запроса: " . (date('d.m.Y H:i:s')) . "." . PHP_EOL . PHP_EOL;
				$message .= "Перейдите в раздел «Запросы в ФСС» для выполнения отправки.";
				$noticeData = array(
					'autotype' => 5,
					'pmUser_id' => $data['pmUser_id'],
					'EvnClass_SysNick' => 'StickFSSData',
					'type' => 1,
					'title' => 'Создан новый запрос в ФСС',
					'text' => $message
				);

				foreach($resp_users as $one_user) {
					$noticeData['User_rid'] = $one_user['pmUser_id'];
					$this->Messages_model->autoMessage($noticeData);
				}
			}
		}

		if (!empty($warnExist)) {
			$resp[0]['warnExist'] = $warnExist;
		}

		return $resp;
	}

	/**
	 * Получение данных о запросе в ФСС
	 */
	function getStickFssData($data) {
		if (empty($data['StickFSSData_id'])) { return false; }
		$query = "
			select top 1
				SFD.EvnStickBase_id,
				SFD.Lpu_id,
				SFD.Lpu_OGRN,
				SFD.Person_id,
				SFD.Person_Snils,
				SFD.StickFSSData_createDate,
				SFD.StickFSSData_id,
				SFD.StickFSSData_IsNeedMSE,
				SFD.StickFSSData_Num,
				SFD.StickFSSData_StickNum,
				SFD.StickFSSData_xmlExpDT,
				SFD.StickFSSData_xmlExportPath,
				SFD.StickFSSDataStatus_id,
				SFDG.MedPersonal_FirstFIO,
				SFDG.MedPersonal_SecondFIO,
				SFDG.MedPersonal_ThirdFIO
			from 
				v_StickFSSData SFD with (nolock)
				left join v_StickFSSDataGet SFDG with (nolock) on SFDG.StickFSSData_id = SFD.StickFSSData_id
			where
				SFD.StickFSSData_id = :StickFSSData_id
		";
		$result = $this->getFirstRowFromQuery($query, array('StickFSSData_id' => $data['StickFSSData_id']));

		return $result;
	}

	/**
	 * Получение данных для формы запроса ЭЛН
	 * @param $data
	 * @return bool
	 */
	function loadStickFSSDataForm($data) {
		$params = array('StickFSSData_id' => $data['StickFSSData_id']);

		$query = "
			select top 1
				SFD.StickFSSData_id,
				SFD.StickFSSData_Num,
				SFD.Lpu_OGRN,
				SFD.Person_id,
				SFD.StickFSSData_StickNum
			from
				v_StickFSSData SFD with(nolock)
			where
				SFD.StickFSSData_id = :StickFSSData_id
		";

		$result = $this->db->query($query, $params);
		if (!is_object($result)) {
			return false;
		}
		return $result->result('array');
	}

	/**
	 * Просмотр файлов
	 */
	function showFiles($data) {
		$resp = $this->queryResult("
			select
				StickFSSData_xmlExportPath as request,
				StickFSSData_xmlImportPath as response,
				convert(varchar(19), StickFSSData_xmlExpDT, 120) as date
			from
				StickFSSData (nolock)
			where
				StickFSSData_id = :StickFSSData_id
		", array(
			'StickFSSData_id' => $data['StickFSSData_id']
		));

		echo "<html>";
		echo "<style type='text/css'> table td { border: 1px solid #000; padding: 10px; } table { border-collapse: collapse; } </style>";
		if (empty($resp)) {
			echo "Логов отправки по данному реестру нет.";
		} else {
			echo "<table>";
			echo "<tr><td>Дата</td><td>Запрос</td><td>Ответ</td></tr>";
			foreach ($resp as $respone) {
				if (!empty($respone['request'])) {
					$respone['request'] = "<a href='{$respone['request']}'>" . basename($respone['request']) . "</a>";
				}
				if (!empty($respone['response'])) {
					$respone['response'] = "<a href='{$respone['response']}'>" . basename($respone['response']) . "</a>";
				}
				echo "<tr><td>{$respone['date']}</td><td>{$respone['request']}</td><td>{$respone['response']}</td></tr>";
			}
			echo "</table>";
		}

		echo "</html>";
	}

	/**
	 * Проверка расхождений
	 * @param $data
	 * @return bool
	 */
	public function checkStickFSSDataGet($data) {
		$this->db->query("delete from StickFSSError with (rowlock) where StickFSSDataGet_id = :StickFSSDataGet_id", array(
			'StickFSSDataGet_id' => $data['StickFSSDataGet_id']
		));

		$StickFSSErrorTypes = array();

		$resp = $this->queryResult("
			select top 1
				SFDG.StickFSSDataGet_id,
				ESB.EvnStickBase_id,
				SFDG.Org_StickNick,
				SFDG.StickFSSDataGet_EmploymentType,
				SFDG.StickFSSDataGet_IsEmploymentServices,
				SFDG.StickFSSDataGet_StickSetDate,
				SFDG.Person_SurName as FSS_Person_SurName,
				SFDG.Person_FirName as FSS_Person_FirName,
				SFDG.Person_SecName as FSS_Person_SecName,
				convert(varchar(10), SFDG.Person_BirthDay, 104) as FSS_Person_BirthDay,
				SFDG.Sex_id as FSS_Sex_id,
				PS.Person_SurName,
				PS.Person_FirName,
				PS.Person_SecName,
				convert(varchar(10), PS.Person_BirthDay, 104) as Person_BirthDay,
				PS.Sex_id,
				SFDG.StickFSSDataGet_StickStatus,
				convert(varchar(10), SFDG.EvnStick_mseDT, 120) as EvnStick_mseDT,
				convert(varchar(10), SFDG.EvnStick_mseExamDT, 120) as EvnStick_mseExamDT,
				convert(varchar(10), SFDG.EvnStick_mseRegDT, 120) as EvnStick_mseRegDT,
				SFDG.InvalidGroupType_id,
				convert(varchar(10), ESWR.EvnStickWorkRelease_begDT, 120) as EvnStickWorkRelease_begDT,
				convert(varchar(10), ESB.EvnStickBase_disDT, 120) as EvnStickBase_disDT,
				convert(varchar(10), coalesce(SFDG.StickFSSDataGet_ThirdEndDate, SFDG.StickFSSDataGet_SecondEndDate, SFDG.StickFSSDataGet_FirstEndDate), 120) as FSS_EvnStickWorkRelease_endDT,
				ESB.EvnStickBase_id,
				ES.StickLeaveType_id
			from
				v_StickFSSDataGet SFDG (nolock)
				left join v_StickFSSData SFD (nolock) on SFD.StickFSSData_id = SFDG.StickFSSData_id
				left join v_EvnStickBase ESB (nolock) on ESB.StickFSSData_id = SFD.StickFSSData_id
				left join v_EvnStick ES (nolock) on ES.EvnStick_id = ESB.EvnStickBase_id
				left join v_PersonState PS (nolock) on PS.Person_id = ESB.Person_id
				outer apply (
					select top 1
						ESWR.EvnStickWorkRelease_begDT
					from
						v_EvnStickWorkRelease ESWR with (nolock)
					where
						ESWR.EvnStickBase_id = ESB.EvnStickBase_id
					order by
						ESWR.EvnStickWorkRelease_begDT asc
				) ESWR
			where
				SFDG.StickFSSDataGet_id = :StickFSSDataGet_id
		", array(
			'StickFSSDataGet_id' => $data['StickFSSDataGet_id']
		));

		if (!empty($resp[0]['EvnStickBase_id'])) {
			// проверки, только если запрос связан с ЛВН в промеде.
			// 1. Соответствие персональных данных (ФИО, дата рождения, пол)
			if (
				(
					trim(mb_strtolower($resp[0]['FSS_Person_SurName'])) != trim(mb_strtolower($resp[0]['Person_SurName']))
					&& trim(mb_strtolower($resp[0]['FSS_Person_FirName'])) != trim(mb_strtolower($resp[0]['Person_FirName']))
					&& trim(mb_strtolower($resp[0]['FSS_Person_SecName'])) != trim(mb_strtolower($resp[0]['Person_SecName']))
				)
				|| $resp[0]['FSS_Person_BirthDay'] != $resp[0]['Person_BirthDay']
				|| $resp[0]['FSS_Sex_id'] != $resp[0]['Sex_id']
			) {
				$StickFSSErrorType_id = 1;
				$StickFSSErrorTypes[] = $StickFSSErrorType_id;
				$this->saveStickFSSError(array(
					'StickFSSDataGet_id' => $data['StickFSSDataGet_id'],
					'StickFSSErrorType_id' => $StickFSSErrorType_id,
					'pmUser_id' => $data['pmUser_id'],
				));
			}
			// 2. ЭЛН из ФСС должен быть открыт: поле LN_STATE имеет значение «010» или «020».
			if ($resp[0]['StickFSSDataGet_StickStatus'] != '010' && $resp[0]['StickFSSDataGet_StickStatus'] != '020') {
				$StickFSSErrorType_id = 2;
				$StickFSSErrorTypes[] = $StickFSSErrorType_id;
				$this->saveStickFSSError(array(
					'StickFSSDataGet_id' => $data['StickFSSDataGet_id'],
					'StickFSSErrorType_id' => $StickFSSErrorType_id,
					'pmUser_id' => $data['pmUser_id'],
				));
			}
			// 3. Отсутствие временны́х расхождений
			// A. если ЛВН в Промеде содержит периоды нетрудоспособности, то дата начала первого из этих периодов должна превышать дату окончания самого позднего из периодов нетрудоспособности в ЭЛН из ФСС (поле TREAT_DT2 в последнем элементе TREAT_FULL_PERIOD);
			if (!empty($resp[0]['EvnStickWorkRelease_begDT']) && $resp[0]['EvnStickWorkRelease_begDT'] <= $resp[0]['FSS_EvnStickWorkRelease_endDT']) {
				$StickFSSErrorType_id = 3; // Проверьте дату начала первого периода нетрудоспособности
				$StickFSSErrorTypes[] = $StickFSSErrorType_id;
				$this->saveStickFSSError(array(
					'StickFSSDataGet_id' => $data['StickFSSDataGet_id'],
					'StickFSSErrorType_id' => $StickFSSErrorType_id,
					'pmUser_id' => $data['pmUser_id'],
				));
			}
			// 4. Отсутствие временны́х расхождений
			// B. если ЛВН в Промеде не содержит периодов нетрудоспособности, то дата исхода ЛВН:
			$errorByIshod = false;
			if (empty($resp[0]['EvnStickWorkRelease_begDT'])) {
				if (in_array($resp[0]['StickLeaveType_id'], array(1, 3))) {
					// o Для исхода «Приступить к работе» или «Установлена инвалидность» (#125144 ) дата должна превышать дату окончания самого позднего из периодов нетрудоспособности в ЭЛН из ФСС (поле TREAT_DT2 в последнем элементе TREAT_FULL_PERIOD).
					if ($resp[0]['EvnStickBase_disDT'] <= $resp[0]['FSS_EvnStickWorkRelease_endDT']) {
						$errorByIshod = true;
					}
				} else {
					// o Для остальных типов исхода: дата должна превышать или совпадать с  датой окончания последнего периода нетрудоспособности (данные тега LN_DATE). (#125144 )
					if ($resp[0]['EvnStickBase_disDT'] < $resp[0]['FSS_EvnStickWorkRelease_endDT']) {
						$errorByIshod = true;
					}
				}
			}

			if ($errorByIshod) {
				$StickFSSErrorType_id = 4; // Проверьте дату исхода ЛВН
				$StickFSSErrorTypes[] = $StickFSSErrorType_id;
				$this->saveStickFSSError(array(
					'StickFSSDataGet_id' => $data['StickFSSDataGet_id'],
					'StickFSSErrorType_id' => $StickFSSErrorType_id,
					'pmUser_id' => $data['pmUser_id'],
				));
			}

			if (empty($StickFSSErrorTypes)) {
				// Если расхождений не найдено, #142902 то производится обновление свойств ЛВН, по которому был произведен запрос.
				$StickWorkType_id = 1;
				if ($resp[0]['StickFSSDataGet_IsEmploymentServices'] == 1) {
					$StickWorkType_id = 3;
				} else if ($resp[0]['StickFSSDataGet_EmploymentType'] == 0) {
					$StickWorkType_id = 2;
				}
		
				$this->db->query("
					update EvnStick with (rowlock)
					set
						EvnStick_mseDT = :EvnStick_mseDT,
						EvnStick_mseExamDT = :EvnStick_mseExamDT,
						EvnStick_mseRegDT = :EvnStick_mseRegDT
					where
						EvnStick_id = :EvnStickBase_id
				", array(
					'EvnStickBase_id' => $resp[0]['EvnStickBase_id'],
					'EvnStick_mseDT' => $resp[0]['EvnStick_mseDT'],
					'EvnStick_mseExamDT' => $resp[0]['EvnStick_mseExamDT'],
					'EvnStick_mseRegDT' => $resp[0]['EvnStick_mseRegDT']
				));

				$this->db->query("
					update EvnStickBase with (rowlock) set 
						StickWorkType_id = :StickWorkType_id, 
						EvnStickBase_OrgNick = :EvnStick_OrgNick,
						InvalidGroupType_id = :InvalidGroupType_id
					where EvnStickBase_id = :EvnStickBase_id
				", array(
					'EvnStickBase_id' => $resp[0]['EvnStickBase_id'],
					'StickWorkType_id' => $StickWorkType_id,
					'EvnStick_OrgNick' => $resp[0]['Org_StickNick'],
					'InvalidGroupType_id' => $resp[0]['InvalidGroupType_id']
				));

				$this->db->query("
					update Evn with (rowlock) set Evn_setDT = :Evn_setDT
					where Evn_id = :Evn_id
 				", array(
 					'Evn_id' => $resp[0]['EvnStickBase_id'],
 					'Evn_setDT' => $resp[0]['StickFSSDataGet_StickSetDate']
 				));
			}
		}

		return $StickFSSErrorTypes;
	}

	/**
	 * Сохранение ошибки
	 */
	public function saveStickFSSError($data) {
		return $this->queryResult("
			declare
				@StickFSSError_id BIGINT = null,
				@Error_Code INT ,
				@Error_Message VARCHAR(4000);
			exec dbo.p_StickFSSError_ins @StickFSSError_id = @StickFSSError_id OUTPUT,
				@StickFSSDataGet_id = :StickFSSDataGet_id,
				@StickFSSErrorType_id = :StickFSSErrorType_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code OUTPUT,
				@Error_Message = @Error_Message OUTPUT
				
			select @StickFSSError_id as StickFSSError_id, @Error_Code as Error_Code, @Error_Message as Error_Msg
		", array(
			'StickFSSDataGet_id' => $data['StickFSSDataGet_id'],
			'StickFSSErrorType_id' => $data['StickFSSErrorType_id'],
			'pmUser_id' => $data['pmUser_id'],
		));
	}

	/**
	 * Обработка ответа из ФСС
	 */
	public function parseXmlResponse($xml, $data) {
		$resp_xml = new DOMDocument();
		$resp_xml->loadXML($xml);

		$fault_arr = array();
		$faults = $resp_xml->getElementsByTagName('faultstring');
		foreach($faults as $fault) {
			$fault_arr[] = $fault->nodeValue;
		}

		if (count($fault_arr) > 0) {
			return array('Error_Msg' => $fault_arr[0]);
		}

		$FileOperationsLnUserGetLNDataOut = $resp_xml->getElementsByTagName('FileOperationsLnUserGetLNDataOut')->item(0);
		if (!empty($FileOperationsLnUserGetLNDataOut)) {
			$status = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('STATUS')->item(0);
			if (!empty($status)) {
				if ($status->nodeValue == '1') {
					$queryParams = array(
						'StickFSSData_id' => $data['StickFSSData_id'],
						'Person_Snils' => null,
						'Person_SurName' => null,
						'Person_FirName' => null,
						'Person_SecName' => null,
						'Person_BirthDay' => null,
						'Sex_id' => null,
						'StickFSSData_StickNum' => null,
						'StickFSSDataGet_StickFirstNum' => null,
						'StickFSSDataGet_IsStickFirst' => null,
						'StickFSSDataGet_IsStickDouble' => null,
						'StickFSSDataGet_StickSetDate' => null,
						'StickFSSDataGet_StickReason' => null,
						'StickFSSDataGet_StickReasonDop' => null,
						'StickCause_CodeAlter' => null,
						'Diag_Code' => null,
						'StickFSSDataGet_StickResult' => null,
						'StickFSSDataGet_changeDate' => null,
						'StickFSSDataGet_workDate' => null,
						'StickFSSDataGet_StickNextNum' => null,
						'StickFSSDataGet_StickStatus' => null,
						'StickFSSDataGet_FirstPostVK' => null,
						'StickFSSDataGet_FirstBegDate' => null,
						'StickFSSDataGet_FirstEndDate' => null,
						'StickFSSDataGet_FirstPost' => null,
						'StickFSSDataGet_SecondPostVK' => null,
						'StickFSSDataGet_SecondBegDate' => null,
						'StickFSSDataGet_SecondEndDate' => null,
						'StickFSSDataGet_SecondPost' => null,
						'StickFSSDataGet_ThirdPostVK' => null,
						'StickFSSDataGet_ThirdBegDate' => null,
						'StickFSSDataGet_ThirdEndDate' => null,
						'StickFSSDataGet_ThirdPost' => null,
						'StickFSSDataGet_IsEdit' => null,
						'Lpu_StickNick' => null,
						'MedPersonal_FirstFIO' => null,
						'MedPersonalVK_FirstFIO' => null,
						'MedPersonal_SecondFIO' => null,
						'MedPersonalVK_SecondFIO' => null,
						'MedPersonal_ThirdFIO' => null,
						'MedPersonalVK_ThirdFIO' => null,
						'StickFSSDataGet_IsEmploymentServices' => null,
						'Org_StickNick' => null,
						'StickFSSDataGet_EmploymentType' => null,
						'Lpu_StickAddress' => null,
						'Lpu_OGRN' => null,
						'EvnStick_NumPar' => null,
						'EvnStick_StickDT' => null,
						'EvnStick_sstEndDate' => null,
						'EvnStick_sstNum' => null,
						'Org_sstOGRN' => null,
						'FirstRelated_Age' => null,
						'FirstRelated_AgeMonth' => null,
						'FirstRelatedLinkType_Code' => null,
						'FirstRelated_FIO' => null,
						'FirstRelated_begDate' => null,
						'FirstRelated_endDate' => null,
						'SecondRelated_Age' => null,
						'SecondRelated_AgeMonth' => null,
						'SecondRelatedLinkType_Code' => null,
						'SecondRelated_FIO' => null,
						'SecondRelated_begDate' => null,
						'SecondRelated_endDate' => null,
						'EvnStick_IsRegPregnancy' => null,
						'EvnStick_stacBegDate' => null,
						'EvnStick_stacEndDate' => null,
						'StickIrregularity_Code' => null,
						'EvnStick_irrDT' => null,
						'EvnStick_mseDT' => null,
						'EvnStick_mseRegDT' => null,
						'EvnStick_mseExamDT' => null,
						'InvalidGroupType_id' => null,
						'FirstPostVK_Code' => null,
						'SecondPostVK_Code' => null,
						'ThirdPostVK_Code' => null,
						'StickFSSDataGet_Hash' => null,
						'pmUser_id' => $data['pmUser_id']
					);
					// достаём данные из XML
					$EL = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('SNILS');
					if ($EL->length > 0) {
						$nodeValue = $EL->item(0)->nodeValue;
						if (!empty($nodeValue)) {
							$queryParams['Person_Snils'] = $nodeValue;
						}
					}
					$EL = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('SURNAME');
					if ($EL->length > 0) {
						$nodeValue = $EL->item(0)->nodeValue;
						if (!empty($nodeValue)) {
							$queryParams['Person_SurName'] = $nodeValue;
						}
					}
					$EL = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('NAME');
					if ($EL->length > 0) {
						$nodeValue = $EL->item(0)->nodeValue;
						if (!empty($nodeValue)) {
							$queryParams['Person_FirName'] = $nodeValue;
						}
					}
					$EL = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('PATRONIMIC');
					if ($EL->length > 0) {
						$nodeValue = $EL->item(0)->nodeValue;
						if (!empty($nodeValue)) {
							$queryParams['Person_SecName'] = $nodeValue;
						}
					}
					$EL = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('BIRTHDAY');
					if ($EL->length > 0) {
						$nodeValue = $EL->item(0)->nodeValue;
						if (!empty($nodeValue)) {
							$queryParams['Person_BirthDay'] = $nodeValue;
						}
					}
					$EL = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('GENDER');
					if ($EL->length > 0) {
						$Sex_id = null;
						switch($EL->item(0)->nodeValue) {
							case '0':
								$Sex_id = 1;
								break;
							case '1':
								$Sex_id = 2;
								break;
						}
						$queryParams['Sex_id'] = $Sex_id;
					}
					$EL = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('LN_CODE');
					if ($EL->length > 0) {
						$nodeValue = $EL->item(0)->nodeValue;
						if (!empty($nodeValue)) {
							$queryParams['StickFSSData_StickNum'] = $nodeValue;
						}
					}
					$EL = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('PREV_LN_CODE');
					if ($EL->length > 0) {
						$nodeValue = $EL->item(0)->nodeValue;
						if (!empty($nodeValue)) {
							$queryParams['StickFSSDataGet_StickFirstNum'] = $nodeValue;
						}
					}
					$EL = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('PRIMARY_FLAG');
					if ($EL->length > 0) {
						$StickFSSDataGet_IsStickFirst = null;
						switch($EL->item(0)->nodeValue) {
							case '0':
								$StickFSSDataGet_IsStickFirst = 1;
								break;
							case '1':
								$StickFSSDataGet_IsStickFirst = 2;
								break;
						}
						$queryParams['StickFSSDataGet_IsStickFirst'] = $StickFSSDataGet_IsStickFirst;
					}
					$EL = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('DUPLICATE_FLAG');
					if ($EL->length > 0) {
						$StickFSSDataGet_IsStickDouble = null;
						switch($EL->item(0)->nodeValue) {
							case '0':
								$StickFSSDataGet_IsStickDouble = 1;
								break;
							case '1':
								$StickFSSDataGet_IsStickDouble = 2;
								break;
						}
						$queryParams['StickFSSDataGet_IsStickDouble'] = $StickFSSDataGet_IsStickDouble;
					}
					$EL = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('LN_DATE');
					if ($EL->length > 0) {
						$nodeValue = $EL->item(0)->nodeValue;
						if (!empty($nodeValue)) {
							$queryParams['StickFSSDataGet_StickSetDate'] = $nodeValue;
						}
					}
					$EL = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('REASON1');
					if ($EL->length > 0) {
						$nodeValue = $EL->item(0)->nodeValue;
						if (!empty($nodeValue)) {
							$queryParams['StickFSSDataGet_StickReason'] = $nodeValue;
						}
					}
					$EL = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('REASON2');
					if ($EL->length > 0) {
						$nodeValue = $EL->item(0)->nodeValue;
						if (!empty($nodeValue)) {
							$queryParams['StickFSSDataGet_StickReasonDop'] = $nodeValue;
						}
					}
					$EL = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('REASON3');
					if ($EL->length > 0) {
						$nodeValue = $EL->item(0)->nodeValue;
						if (!empty($nodeValue)) {
							$queryParams['StickCause_CodeAlter'] = $nodeValue;
						}
					}
					$EL = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('DIAGNOS');
					if ($EL->length > 0) {
						$nodeValue = $EL->item(0)->nodeValue;
						if (!empty($nodeValue)) {
							$queryParams['Diag_Code'] = $nodeValue;
						}
					}
					$EL = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('MSE_RESULT');
					if ($EL->length > 0) {
						$nodeValue = $EL->item(0)->nodeValue;
						if (!empty($nodeValue)) {
							$queryParams['StickFSSDataGet_StickResult'] = $nodeValue;
						}
					}
					$EL = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('OTHER_STATE_DT');
					if ($EL->length > 0) {
						$nodeValue = $EL->item(0)->nodeValue;
						if (!empty($nodeValue)) {
							$queryParams['StickFSSDataGet_changeDate'] = $nodeValue;
						}
					}
					$EL = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('RETURN_DATE_LPU');
					if ($EL->length > 0) {
						$nodeValue = $EL->item(0)->nodeValue;
						if (!empty($nodeValue)) {
							$queryParams['StickFSSDataGet_workDate'] = $nodeValue;
						}
					}
					$EL = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('NEXT_LN_CODE');
					if ($EL->length > 0) {
						$nodeValue = $EL->item(0)->nodeValue;
						if (!empty($nodeValue)) {
							$queryParams['StickFSSDataGet_StickNextNum'] = $nodeValue;
						}
					}
					$EL = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('LN_STATE');
					if ($EL->length > 0) {
						$nodeValue = $EL->item(0)->nodeValue;
						if (!empty($nodeValue)) {
							$queryParams['StickFSSDataGet_StickStatus'] = $nodeValue;
						}
					}
					$TFP = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('TREAT_FULL_PERIOD');
					if ($TFP->length > 0) {
						$TreatPeriod = $TFP->item(0);
						$EL = $TreatPeriod->getElementsByTagName('TREAT_CHAIRMAN_ROLE');
						if ($EL->length > 0) {
							$queryParams['StickFSSDataGet_FirstPostVK'] = $EL->item(0)->nodeValue;
							$queryParams['FirstPostVK_Code'] = $EL->item(0)->nodeValue;
						}
						$EL = $TreatPeriod->getElementsByTagName('TREAT_DOCTOR');
						if ($EL->length > 0) {
							$queryParams['MedPersonal_FirstFIO'] = $EL->item(0)->nodeValue;
						}
						$EL = $TreatPeriod->getElementsByTagName('TREAT_CHAIRMAN');
						if ($EL->length > 0) {
							$queryParams['MedPersonalVK_FirstFIO'] = $EL->item(0)->nodeValue;
						}
						$EL = $TreatPeriod->getElementsByTagName('TREAT_DT1');
						if ($EL->length > 0) {
							$queryParams['StickFSSDataGet_FirstBegDate'] = $EL->item(0)->nodeValue;
						}
						$EL = $TreatPeriod->getElementsByTagName('TREAT_DT2');
						if ($EL->length > 0) {
							$queryParams['StickFSSDataGet_FirstEndDate'] = $EL->item(0)->nodeValue;
						}
						$EL = $TreatPeriod->getElementsByTagName('TREAT_DOCTOR_ROLE');
						if ($EL->length > 0) {
							$queryParams['StickFSSDataGet_FirstPost'] = $EL->item(0)->nodeValue;
						}
					}
					if ($TFP->length > 1) {
						$TreatPeriod = $TFP->item(1);
						$EL = $TreatPeriod->getElementsByTagName('TREAT_CHAIRMAN_ROLE');
						if ($EL->length > 0) {
							$queryParams['StickFSSDataGet_SecondPostVK'] = $EL->item(0)->nodeValue;
							$queryParams['SecondPostVK_Code'] = $EL->item(0)->nodeValue;
						}
						$EL = $TreatPeriod->getElementsByTagName('TREAT_DOCTOR');
						if ($EL->length > 0) {
							$queryParams['MedPersonal_SecondFIO'] = $EL->item(0)->nodeValue;
						}
						$EL = $TreatPeriod->getElementsByTagName('TREAT_CHAIRMAN');
						if ($EL->length > 0) {
							$queryParams['MedPersonalVK_SecondFIO'] = $EL->item(0)->nodeValue;
						}
						$EL = $TreatPeriod->getElementsByTagName('TREAT_DT1');
						if ($EL->length > 0) {
							$queryParams['StickFSSDataGet_SecondBegDate'] = $EL->item(0)->nodeValue;
						}
						$EL = $TreatPeriod->getElementsByTagName('TREAT_DT2');
						if ($EL->length > 0) {
							$queryParams['StickFSSDataGet_SecondEndDate'] = $EL->item(0)->nodeValue;
						}
						$EL = $TreatPeriod->getElementsByTagName('TREAT_DOCTOR_ROLE');
						if ($EL->length > 0) {
							$queryParams['StickFSSDataGet_SecondPost'] = $EL->item(0)->nodeValue;
						}
					}
					if ($TFP->length > 2) {
						$TreatPeriod = $TFP->item(2);
						$EL = $TreatPeriod->getElementsByTagName('TREAT_CHAIRMAN_ROLE');
						if ($EL->length > 0) {
							$queryParams['StickFSSDataGet_ThirdPostVK'] = $EL->item(0)->nodeValue;
							$queryParams['ThirdPostVK_Code'] = $EL->item(0)->nodeValue;
						}
						$EL = $TreatPeriod->getElementsByTagName('TREAT_DOCTOR');
						if ($EL->length > 0) {
							$queryParams['MedPersonal_ThirdFIO'] = $EL->item(0)->nodeValue;
						}
						$EL = $TreatPeriod->getElementsByTagName('TREAT_CHAIRMAN');
						if ($EL->length > 0) {
							$queryParams['MedPersonalVK_ThirdFIO'] = $EL->item(0)->nodeValue;
						}
						$EL = $TreatPeriod->getElementsByTagName('TREAT_DT1');
						if ($EL->length > 0) {
							$queryParams['StickFSSDataGet_ThirdBegDate'] = $EL->item(0)->nodeValue;
						}
						$EL = $TreatPeriod->getElementsByTagName('TREAT_DT2');
						if ($EL->length > 0) {
							$queryParams['StickFSSDataGet_ThirdEndDate'] = $EL->item(0)->nodeValue;
						}
						$EL = $TreatPeriod->getElementsByTagName('TREAT_DOCTOR_ROLE');
						if ($EL->length > 0) {
							$queryParams['StickFSSDataGet_ThirdPost'] = $EL->item(0)->nodeValue;
						}
					}
					$EL = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('LPU_NAME');
					if ($EL->length > 0) {
						$nodeValue = $EL->item(0)->nodeValue;
						if (!empty($nodeValue)) {
							$queryParams['Lpu_StickNick'] = $nodeValue;
						}
					}
					$EL = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('BOZ_FLAG');
					if ($EL->length > 0) {
						$nodeValue = $EL->item(0)->nodeValue;
						if (!empty($nodeValue)) {
							$queryParams['StickFSSDataGet_IsEmploymentServices'] = $nodeValue;
						}
					}
					$EL = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('LPU_EMPLOYER');
					if ($EL->length > 0) {
						$nodeValue = $EL->item(0)->nodeValue;
						if (!empty($nodeValue)) {
							$queryParams['Org_StickNick'] = $nodeValue;
						}
					}
					$EL = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('LPU_EMPL_FLAG');
					if ($EL->length > 0) {
						$nodeValue = $EL->item(0)->nodeValue;
						if (isset($nodeValue) && mb_strlen($nodeValue) > 0) {
							$queryParams['StickFSSDataGet_EmploymentType'] = $nodeValue;
						}
					}
					$EL = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('LPU_ADDRESS');
					if ($EL->length > 0) {
						$nodeValue = $EL->item(0)->nodeValue;
						if (!empty($nodeValue)) {
							$queryParams['Lpu_StickAddress'] = $nodeValue;
						}
					}
					$EL = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('LPU_OGRN');
					if ($EL->length > 0) {
						$nodeValue = $EL->item(0)->nodeValue;
						if (!empty($nodeValue)) {
							$queryParams['Lpu_OGRN'] = $nodeValue;
						}
					}
					$EL = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('PARENT_CODE');
					if ($EL->length > 0) {
						$nodeValue = $EL->item(0)->nodeValue;
						if (!empty($nodeValue)) {
							$queryParams['EvnStick_NumPar'] = $nodeValue;
						}
					}
					$EL = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('DATE1');
					if ($EL->length > 0) {
						$nodeValue = $EL->item(0)->nodeValue;
						if (!empty($nodeValue)) {
							$queryParams['EvnStick_StickDT'] = $nodeValue;
						}
					}
					$EL = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('DATE2');
					if ($EL->length > 0) {
						$nodeValue = $EL->item(0)->nodeValue;
						if (!empty($nodeValue)) {
							$queryParams['EvnStick_sstEndDate'] = $nodeValue;
						}
					}
					$EL = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('VOUCHER_NO');
					if ($EL->length > 0) {
						$nodeValue = $EL->item(0)->nodeValue;
						if (!empty($nodeValue)) {
							$queryParams['EvnStick_sstNum'] = $nodeValue;
						}
					}
					$EL = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('VOUCHER_OGRN');
					if ($EL->length > 0) {
						$nodeValue = $EL->item(0)->nodeValue;
						if (!empty($nodeValue)) {
							$queryParams['Org_sstOGRN'] = $nodeValue;
						}
					}
					$EL = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('SERV1_AGE');
					if ($EL->length > 0) {
						$nodeValue = $EL->item(0)->nodeValue;
						if (isset($nodeValue) && ($nodeValue || $nodeValue === 0 || $nodeValue === '0')) {
							$queryParams['FirstRelated_Age'] = $nodeValue;
						}
					}
					$EL = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('SERV1_MM');
					if ($EL->length > 0) {
						$nodeValue = $EL->item(0)->nodeValue;
						if (isset($nodeValue) && ($nodeValue || $nodeValue === 0 || $nodeValue === '0')) {
							$queryParams['FirstRelated_AgeMonth'] = $nodeValue;
						}
					}
					$EL = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('SERV1_RELATION_CODE');
					if ($EL->length > 0) {
						$nodeValue = $EL->item(0)->nodeValue;
						if (!empty($nodeValue)) {
							$queryParams['FirstRelatedLinkType_Code'] = $nodeValue;
						}
					}
					$EL = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('SERV1_FIO');
					if ($EL->length > 0) {
						$nodeValue = $EL->item(0)->nodeValue;
						if (!empty($nodeValue)) {
							$queryParams['FirstRelated_FIO'] = $nodeValue;
						}
					}
					$EL = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('SERV1_DT1');
					if ($EL->length > 0) {
						$nodeValue = $EL->item(0)->nodeValue;
						if (!empty($nodeValue)) {
							$queryParams['FirstRelated_begDate'] = $nodeValue;
						}
					}
					$EL = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('SERV1_DT2');
					if ($EL->length > 0) {
						$nodeValue = $EL->item(0)->nodeValue;
						if (!empty($nodeValue)) {
							$queryParams['FirstRelated_endDate'] = $nodeValue;
						}
					}
					$EL = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('SERV2_AGE');
					if ($EL->length > 0) {
						$nodeValue = $EL->item(0)->nodeValue;
						if (isset($nodeValue) && ($nodeValue || $nodeValue === 0 || $nodeValue === '0')) {
							$queryParams['SecondRelated_Age'] = $nodeValue;
						}
					}
					$EL = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('SERV2_MM');
					if ($EL->length > 0) {
						$nodeValue = $EL->item(0)->nodeValue;
						if (isset($nodeValue) && ($nodeValue || $nodeValue === 0 || $nodeValue === '0')) {
							$queryParams['SecondRelated_AgeMonth'] = $nodeValue;
						}
					}
					$EL = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('SERV2_RELATION_CODE');
					if ($EL->length > 0) {
						$nodeValue = $EL->item(0)->nodeValue;
						if (!empty($nodeValue)) {
							$queryParams['SecondRelatedLinkType_Code'] = $nodeValue;
						}
					}
					$EL = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('SERV2_FIO');
					if ($EL->length > 0) {
						$nodeValue = $EL->item(0)->nodeValue;
						if (!empty($nodeValue)) {
							$queryParams['SecondRelated_FIO'] = $nodeValue;
						}
					}
					$EL = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('SERV2_DT1');
					if ($EL->length > 0) {
						$nodeValue = $EL->item(0)->nodeValue;
						if (!empty($nodeValue)) {
							$queryParams['SecondRelated_begDate'] = $nodeValue;
						}
					}
					$EL = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('SERV2_DT2');
					if ($EL->length > 0) {
						$nodeValue = $EL->item(0)->nodeValue;
						if (!empty($nodeValue)) {
							$queryParams['SecondRelated_endDate'] = $nodeValue;
						}
					}
					$EL = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('PREGN12W_FLAG');
					if ($EL->length > 0) {
						$nodeValue = $EL->item(0)->nodeValue;
						if (isset($nodeValue) && mb_strlen($nodeValue) > 0) {
							$queryParams['EvnStick_IsRegPregnancy'] = $nodeValue;
						}
					}
					$EL = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('HOSPITAL_DT1');
					if ($EL->length > 0) {
						$nodeValue = $EL->item(0)->nodeValue;
						if (!empty($nodeValue)) {
							$queryParams['EvnStick_stacBegDate'] = $nodeValue;
						}
					}
					$EL = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('HOSPITAL_DT2');
					if ($EL->length > 0) {
						$nodeValue = $EL->item(0)->nodeValue;
						if (!empty($nodeValue)) {
							$queryParams['EvnStick_stacEndDate'] = $nodeValue;
						}
					}
					$EL = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('HOSPITAL_BREACH_CODE');
					if ($EL->length > 0) {
						$nodeValue = $EL->item(0)->nodeValue;
						if (!empty($nodeValue)) {
							$queryParams['StickIrregularity_Code'] = $nodeValue;
						}
					}
					$EL = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('HOSPITAL_BREACH_DT');
					if ($EL->length > 0) {
						$nodeValue = $EL->item(0)->nodeValue;
						if (!empty($nodeValue)) {
							$queryParams['EvnStick_irrDT'] = $nodeValue;
						}
					}
					$EL = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('MSE_DT1');
					if ($EL->length > 0) {
						$nodeValue = $EL->item(0)->nodeValue;
						if (!empty($nodeValue)) {
							$queryParams['EvnStick_mseDT'] = $nodeValue;
						}
					}
					$EL = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('MSE_DT2');
					if ($EL->length > 0) {
						$nodeValue = $EL->item(0)->nodeValue;
						if (!empty($nodeValue)) {
							$queryParams['EvnStick_mseRegDT'] = $nodeValue;
						}
					}
					$EL = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('MSE_DT3');
					if ($EL->length > 0) {
						$nodeValue = $EL->item(0)->nodeValue;
						if (!empty($nodeValue)) {
							$queryParams['EvnStick_mseExamDT'] = $nodeValue;
						}
					}
					$EL = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('MSE_INVALID_GROUP');
					if ($EL->length > 0) {
						$InvalidGroupType_id = null;
						switch($EL->item(0)->nodeValue) {
							case '1':
								$InvalidGroupType_id = 2;
								break;
							case '2':
								$InvalidGroupType_id = 3;
								break;
							case '3':
								$InvalidGroupType_id = 4;
								break;
						}
						$queryParams['InvalidGroupType_id'] = $InvalidGroupType_id;
					}
					$EL = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('LN_HASH');
					if ($EL->length > 0) {
						$nodeValue = $EL->item(0)->nodeValue;
						if (!empty($nodeValue)) {
							$queryParams['StickFSSDataGet_Hash'] = $nodeValue;
						}
					}

					$this->beginTransaction();

					// записываем данные в StickFSSDataGet
					$this->db->query("delete from StickFSSDataGet with (rowlock) where StickFSSData_id = :StickFSSData_id", array(
						'StickFSSData_id' => $data['StickFSSData_id']
					));
					$resp_save = $this->queryResult("
						declare
							@StickFSSDataGet_id bigint = null,
							@EvnStickBase_id bigint,
							@Error_Code int,
							@Error_Message varchar(4000);
							
						select
							@EvnStickBase_id = coalesce(ESB.EvnStickBase_id, SFD.EvnStickBase_id, ESB2.EvnStickBase_id)
						from
							v_StickFSSData SFD (nolock)
							left join v_EvnStickBase ESB (nolock) on ESB.StickFSSData_id = SFD.StickFSSData_id
							left join v_EvnStickBase ESB2 (nolock) on ESB2.EvnStickBase_Num = SFD.StickFSSData_StickNum
						where
							SFD.StickFSSData_id = :StickFSSData_id;
			
						exec p_StickFSSDataGet_ins
							@StickFSSDataGet_id = @StickFSSDataGet_id output,
							@EvnStickBase_id = @EvnStickBase_id,
							@StickFSSData_id = :StickFSSData_id,
							@Person_Snils = :Person_Snils,
							@Person_SurName = :Person_SurName,
							@Person_FirName = :Person_FirName,
							@Person_SecName = :Person_SecName,
							@Person_BirthDay = :Person_BirthDay,
							@Sex_id = :Sex_id,
							@StickFSSDataGet_StickNum = :StickFSSData_StickNum,
							@StickFSSDataGet_StickFirstNum = :StickFSSDataGet_StickFirstNum,
							@StickFSSDataGet_IsStickFirst = :StickFSSDataGet_IsStickFirst,
							@StickFSSDataGet_IsStickDouble = :StickFSSDataGet_IsStickDouble,
							@StickFSSDataGet_StickSetDate = :StickFSSDataGet_StickSetDate,
							@StickFSSDataGet_StickReason = :StickFSSDataGet_StickReason,
							@StickFSSDataGet_StickReasonDop = :StickFSSDataGet_StickReasonDop,
							@StickCause_CodeAlter = :StickCause_CodeAlter,
							@Diag_Code = :Diag_Code,
							@StickFSSDataGet_StickResult = :StickFSSDataGet_StickResult,
							@StickFSSDataGet_changeDate = :StickFSSDataGet_changeDate,
							@StickFSSDataGet_workDate = :StickFSSDataGet_workDate,
							@StickFSSDataGet_StickNextNum = :StickFSSDataGet_StickNextNum,
							@StickFSSDataGet_StickStatus = :StickFSSDataGet_StickStatus,
							@StickFSSDataGet_FirstPostVK = :StickFSSDataGet_FirstPostVK,
							@StickFSSDataGet_FirstBegDate = :StickFSSDataGet_FirstBegDate,
							@StickFSSDataGet_FirstEndDate = :StickFSSDataGet_FirstEndDate,
							@StickFSSDataGet_FirstPost = :StickFSSDataGet_FirstPost,
							@StickFSSDataGet_SecondPostVK = :StickFSSDataGet_SecondPostVK,
							@StickFSSDataGet_SecondBegDate = :StickFSSDataGet_SecondBegDate,
							@StickFSSDataGet_SecondEndDate = :StickFSSDataGet_SecondEndDate,
							@StickFSSDataGet_SecondPost = :StickFSSDataGet_SecondPost,
							@StickFSSDataGet_ThirdPostVK = :StickFSSDataGet_ThirdPostVK,
							@StickFSSDataGet_ThirdBegDate = :StickFSSDataGet_ThirdBegDate,
							@StickFSSDataGet_ThirdEndDate = :StickFSSDataGet_ThirdEndDate,
							@StickFSSDataGet_ThirdPost = :StickFSSDataGet_ThirdPost,
							@StickFSSDataGet_IsEdit = :StickFSSDataGet_IsEdit,
							@Lpu_StickNick = :Lpu_StickNick,
							@MedPersonal_FirstFIO = :MedPersonal_FirstFIO,
							@MedPersonalVK_FirstFIO = :MedPersonalVK_FirstFIO,
							@MedPersonal_SecondFIO = :MedPersonal_SecondFIO,
							@MedPersonalVK_SecondFIO = :MedPersonalVK_SecondFIO,
							@MedPersonal_ThirdFIO = :MedPersonal_ThirdFIO,
							@MedPersonalVK_ThirdFIO = :MedPersonalVK_ThirdFIO,
							@StickFSSDataGet_IsEmploymentServices = :StickFSSDataGet_IsEmploymentServices,
							@Org_StickNick = :Org_StickNick,
							@StickFSSDataGet_EmploymentType = :StickFSSDataGet_EmploymentType,
							@Lpu_StickAddress = :Lpu_StickAddress,
							@Lpu_OGRN = :Lpu_OGRN,
							@EvnStick_NumPar = :EvnStick_NumPar,
							@EvnStick_StickDT = :EvnStick_StickDT,
							@EvnStick_sstEndDate = :EvnStick_sstEndDate,
							@EvnStick_sstNum = :EvnStick_sstNum,
							@Org_sstOGRN = :Org_sstOGRN,
							@FirstRelated_Age = :FirstRelated_Age,
							@FirstRelated_AgeMonth = :FirstRelated_AgeMonth,
							@FirstRelatedLinkType_Code = :FirstRelatedLinkType_Code,
							@FirstRelated_FIO = :FirstRelated_FIO,
							@FirstRelated_begDate = :FirstRelated_begDate,
							@FirstRelated_endDate = :FirstRelated_endDate,
							@SecondRelated_Age = :SecondRelated_Age,
							@SecondRelated_AgeMonth = :SecondRelated_AgeMonth,
							@SecondRelatedLinkType_Code = :SecondRelatedLinkType_Code,
							@SecondRelated_FIO = :SecondRelated_FIO,
							@SecondRelated_begDate = :SecondRelated_begDate,
							@SecondRelated_endDate = :SecondRelated_endDate,
							@EvnStick_IsRegPregnancy = :EvnStick_IsRegPregnancy,
							@EvnStick_stacBegDate = :EvnStick_stacBegDate,
							@EvnStick_stacEndDate = :EvnStick_stacEndDate,
							@StickIrregularity_Code = :StickIrregularity_Code,
							@EvnStick_irrDT = :EvnStick_irrDT,
							@EvnStick_mseDT = :EvnStick_mseDT,
							@EvnStick_mseRegDT = :EvnStick_mseRegDT,
							@EvnStick_mseExamDT = :EvnStick_mseExamDT,
							@InvalidGroupType_id = :InvalidGroupType_id,
							@FirstPostVK_Code = :FirstPostVK_Code,
							@SecondPostVK_Code = :SecondPostVK_Code,
							@ThirdPostVK_Code = :ThirdPostVK_Code,
							@StickFSSDataGet_Hash = :StickFSSDataGet_Hash,
							@pmUser_id = :pmUser_id,
							@Error_Code = @Error_Code OUTPUT,
							@Error_Message = @Error_Message OUTPUT
						select @StickFSSDataGet_id as StickFSSDataGet_id, @Error_Code as Error_Code, @Error_Message as Error_Msg
					", $queryParams);

					if (!empty($resp_save[0]['Error_Msg'])) {
						$this->rollbackTransaction();
						return array('Error_Msg' => $resp_save[0]['Error_Msg']);
					}


					// #186238 Если значение поля «Состояние ЛН» (LN_STATE) в ответе ФСС отлично от «010» (открыт), заполняются поля исхода ЛВН
					if (
						$queryParams['StickFSSDataGet_StickStatus'] != '010'  
						&& !empty($queryParams['StickFSSData_StickNum'])
					) {
						if (!empty($queryParams['StickFSSDataGet_workDate'])) {
							$queryParams['lnResult_Date'] = $queryParams['StickFSSDataGet_workDate'];
							$queryParams['lnResult'] = '01'; // приступить к работе
						}

						if (!empty($queryParams['StickFSSDataGet_changeDate'])) {
							$queryParams['lnResult_Date'] = $queryParams['StickFSSDataGet_changeDate'];
						}

						if (!empty($queryParams['StickFSSDataGet_StickResult'])) {
							$queryParams['lnResult'] = $queryParams['StickFSSDataGet_StickResult'];
						}

						$LvnOld_resp = $this->db->query("
							select top 1
								ESB.EvnStickBase_id,
								SLT.StickLeaveType_Code,
								convert(varchar(10), ESB.EvnStickBase_disDT, 23) as EvnStickBase_disDT
							from
								v_EvnStickBase ESB with (nolock)
								inner join v_StickLeaveType SLT with (nolock) on StickLeaveType_id = ESB.StickLeaveType_rid
							where
								ESB.EvnStickBase_Num = :StickFSSData_StickNum
						", $queryParams);

						if (is_object($LvnOld_resp)) {
							$LvnOld = $LvnOld_resp->result('array');
						}

						if (
							(!empty($LvnOld[0]['StickLeaveType_Code']) && !empty($queryParams['lnResult']) && $LvnOld[0]['StickLeaveType_Code'] != $queryParams['lnResult'])
							|| (!empty($LvnOld[0]['EvnStickBase_disDT']) && !empty($queryParams['lnResult_Date']) && $LvnOld[0]['EvnStickBase_disDT'] != $queryParams['lnResult_Date'])
						) {
							if ( !empty($queryParams['lnResult']) ) {//изменение исхода ЛВН
								$this->db->query("
									update
										EvnStickBase with (rowlock)
									set
										StickLeaveType_rid = (
											select top 1 
												StickLeaveType_id 
											from 
												v_StickLeaveType with (nolock)
											where
												StickLeaveType_Code = :lnResult
										)
									where
										EvnStickBase_Num = :StickFSSData_StickNum
								", $queryParams);

								$this->db->query("
									update
										ES with (rowlock)
									set
										ES.StickLeaveType_id = (
											select top 1 
												StickLeaveType_id 
											from 
												v_StickLeaveType with (nolock)
											where
												StickLeaveType_Code = :lnResult
										)
									from
										EvnStick ES
										inner join EvnStickBase ESB with (nolock) on ESB.EvnStickBase_id = ES.EvnStick_id
									where
										ESB.EvnStickBase_Num = :StickFSSData_StickNum
								", $queryParams);
							}

							if ( !empty($queryParams['lnResult_Date']) ) { //изменение даты исхода ЛВН
								$this->db->query("
									update
										E with (rowlock)
									set
										Evn_disDT = :lnResult_Date
									from
										v_EvnStickBase ESB with (nolock)
										inner join Evn E on E.Evn_id = ESB.EvnStickBase_id
									where
										ESB.EvnStickBase_Num = :StickFSSData_StickNum
								", $queryParams);
							}

							//меняем статус подписи исхода на "документ не актуален"
							$this->db->query("
								update Signatures with (rowlock)
								set SignaturesStatus_id = 3 -- документ не актуален
								where 
									Signatures_id in (
										select S.Signatures_id
										from 
											Signatures S with (nolock)
											left join EvnStick ES with (nolock) on ES.Signatures_id = S.Signatures_id
											left join EvnStickDop ESD with (nolock) on ESD.Signatures_id = S.Signatures_id
										where
											(
												ES.EvnStick_id = :EvnStick_id
												or ESD.EvnStickDop_id = :EvnStick_id
											)
											and S.SignaturesStatus_id = 1
									)
							", array('EvnStick_id' => $LvnOld[0]['EvnStickBase_id']));
						}
					}

					// если статус "050 ЛВН дополнен данными МСЭ" редактируем ЛВН пришедшими данными
					if ( $queryParams['StickFSSDataGet_StickStatus'] == '050' ) {
						$this->db->query("
							update
								ES with (rowlock)
							set
								EvnStick_mseDT = :EvnStick_mseDT,
								EvnStick_mseExamDT = :EvnStick_mseExamDT,
								EvnStick_mseRegDT = :EvnStick_mseRegDT
							from 
								v_EvnStickBase ESB with (nolock)
								inner join EvnStick ES on ES.EvnStick_id = ESB.EvnStickBase_id
							where
								ESB.EvnStickBase_Num = :StickFSSData_StickNum
						", $queryParams);

						$this->db->query("
							update
								EvnStickBase with (rowlock)
							set
								InvalidGroupType_id = :InvalidGroupType_id
							where
								EvnStickBase_Num = :StickFSSData_StickNum
						", $queryParams);
					}

					$resp_err = null;
					if (!empty($resp_save[0]['StickFSSDataGet_id'])) {
						// Обновляем статус ЛВН
						$this->db->query("
							update 
								esb with (rowlock)
							set
								esb.StickFSSType_id = sft.StickFSSType_id
							from
								EvnStickBase esb
								inner join v_StickFSSDataGet sfdg (nolock) on sfdg.StickFSSDataGet_StickNum = esb.EvnStickBase_Num
								inner join v_StickFSSType sft (nolock) on sft.StickFSSType_Code = sfdg.StickFSSDataGet_StickStatus
							where
								sfdg.StickFSSDataGet_id = :StickFSSDataGet_id							 
						", array(
							'StickFSSDataGet_id' => $resp_save[0]['StickFSSDataGet_id']
						));

						$resp_err = $this->checkStickFSSDataGet(array(
							'StickFSSDataGet_id' => $resp_save[0]['StickFSSDataGet_id'],
							'pmUser_id' => $data['pmUser_id']
						));
					}

					// присваиваем статус запросу StickFSSDataStatus ЭЛН подтверждён
					$this->db->query("update StickFSSData with (rowlock) set StickFSSDataStatus_id = 4 where StickFSSData_id = :StickFSSData_id", array(
						'StickFSSData_id' => $data['StickFSSData_id']
					));

					$this->load->model('Messages_model');
					// получаем пользователей данной МО с группами:
					// - пользователь АРМ администратора МО;
					// - пользователь АРМ медицинского статистика.
					$resp_users = $this->queryResult("
						DECLARE
							@pmUserCacheGroup_id bigint = (select top 1 pmUserCacheGroup_id from dbo.pmUserCacheGroup where pmUserCacheGroup_SysNick = 'LpuAdmin')
						
						select
							pmUser_id
						from
							v_pmUserCache puc (nolock)
						where
							puc.Lpu_id = :Lpu_id
							and exists(select top 1 pucgl.pmUserCacheGroupLink_id from v_pmUserCacheGroupLink pucgl with (nolock) where pucgl.pmUserCacheGroup_id = @pmUserCacheGroup_id and pucgl.pmUserCache_id = puc.PMUser_id) -- АРМ администратора МО
							and ISNULL(puc.pmUser_deleted, 1) = 1
							and puc.pmUser_EvnClass like '%StickFSSData%'
							
						union
						
						select
							pmUser_id
						from
							v_pmUserCache puc (nolock)
							inner join v_MedServiceMedPersonal msmp (nolock) on msmp.MedPersonal_id = puc.MedPersonal_id
							inner join v_MedService ms (nolock) on ms.MedService_id = msmp.MedService_id
							inner join v_MedServiceType mst (nolock) on mst.MedServiceType_id = ms.MedServiceType_id
						where
							puc.Lpu_id = :Lpu_id
							and mst.MedServiceType_SysNick = 'mstat' -- АРМ медицинского статистика
							and ISNULL(puc.pmUser_deleted, 1) = 1
							and puc.pmUser_EvnClass like '%StickFSSData%'
					", array(
						'Lpu_id' => $data['Lpu_id']
					));

					if (!empty($resp_users)) {
						$resp_sfd = $this->queryResult("
							select top 1
								PS.Person_SurName + ' '+ isnull(PS.Person_FirName,'') + ' ' + isnull(PS.Person_SecName,'') as Person_Fio,
								convert(varchar(10), PS.Person_BirthDay, 104) as Person_BirthDay,
								case
									when EvnPL.EvnPL_id is not null then 'ТАП'
									when EvnPLStom.EvnPLStom_id is not null then 'Стом. ТАП'
									when EvnPS.EvnPS_id is not null then 'КВС'
									else ''
								end as EvnStick_ParentTypeName,
								case
									when EvnPL.EvnPL_id is not null then EvnPL.EvnPL_NumCard
									when EvnPLStom.EvnPLStom_id is not null then EvnPLStom.EvnPLStom_NumCard
									when EvnPS.EvnPS_id is not null then EvnPS.EvnPS_NumCard
									else ''
								end as EvnStick_ParentNum,
								SFD.StickFSSData_StickNum,
								SFD.StickFSSData_IsNeedMSE,
								SFDS.StickFSSDataStatus_Name
							from
								v_StickFSSData SFD (nolock)
								left join v_StickFSSDataStatus SFDS with (nolock) on SFDS.StickFSSDataStatus_id = SFD.StickFSSDataStatus_id
								left join v_EvnStickBase ESB with (nolock) on ESB.StickFSSData_id = SFD.StickFSSData_id
								left join v_PersonState PS with (nolock) on PS.Person_id = SFD.Person_id
								left join v_EvnPL EvnPL with (nolock) on ESB.EvnStickBase_pid = EvnPL.EvnPL_id
								left join v_EvnPLStom EvnPLStom with (nolock) on ESB.EvnStickBase_pid = EvnPLStom.EvnPLStom_id
								left join v_EvnPS EvnPS with (nolock) on ESB.EvnStickBase_pid = EvnPS.EvnPS_id
							where
								SFD.StickFSSData_id = :StickFSSData_id
						", array(
							'StickFSSData_id' => $data['StickFSSData_id']
						));

						if (!empty($resp_sfd[0])) {
							$isNeedMSE = false;
							if( !empty($resp_sfd[0]['StickFSSData_IsNeedMSE']) && $resp_sfd[0]['StickFSSData_IsNeedMSE'] == 2 ) {
								$isNeedMSE = true;
							}

							$message = "Пациент: " . $resp_sfd[0]['Person_Fio'] . ", " . $resp_sfd[0]['Person_BirthDay'] . "." . PHP_EOL . PHP_EOL;
							$message .= "Номер ЛВН: " . $resp_sfd[0]['StickFSSData_StickNum'] . "." . PHP_EOL . PHP_EOL;
							$message .= "Номер ТАП/КВС: " . $resp_sfd[0]['EvnStick_ParentTypeName'] . " № " . $resp_sfd[0]['EvnStick_ParentNum'] . ".";
							$message .= "Результат: " . $resp_sfd[0]['StickFSSDataStatus_Name'] . ".";
							if (!empty($resp_err)) {
								$errors = "";
								foreach ($resp_err as $one_err) {
									$resp_errname = $this->queryResult("
										select top 1
											StickFSSErrorType_Name
										from
											v_StickFSSErrorType (nolock)
										where
											StickFSSErrorType_id = :StickFSSErrorType_id
									", array(
										'StickFSSErrorType_id' => $one_err
									));

									if (!empty($resp_errname[0]['StickFSSErrorType_Name'])) {
										if (!empty($errors)) {
											$errors .= ', ';
										}
										$errors .= $resp_errname[0]['StickFSSErrorType_Name'];
									}
								}
								$message .= "Расхождения: " . $errors;
							}
							$noticeData = array(
								'autotype' => 5,
								'pmUser_id' => $data['pmUser_id'],
								'EvnClass_SysNick' => 'StickFSSData',
								'type' => 1,
								'title' => 'Получен ответ на запрос в ФСС',
								'text' => $message
							);

							if($isNeedMSE) {
								$noticeData['title'] = 'Получен ответ на запрос в ФСС, требующий данные о МСЭ';
							}

							foreach ($resp_users as $one_user) {
								$noticeData['User_rid'] = $one_user['pmUser_id'];
								$this->Messages_model->autoMessage($noticeData);
							}
						}
					}
					$this->commitTransaction();

					return array('Error_Msg' => '');
				} else {
					// Присваиваем статус "ЭЛН не подтверждён"
					$this->db->query("update StickFSSData with (rowlock) set StickFSSDataStatus_id = 5 where StickFSSData_id = :StickFSSData_id", array(
						'StickFSSData_id' => $data['StickFSSData_id']
					));
					$message = $FileOperationsLnUserGetLNDataOut->getElementsByTagName('MESS')->item(0);
					if (!empty($message)) {
						return array('Error_Msg' => 'Получить данные ЭЛН не удалось: '."<br>".$message->nodeValue);
					} else {
						return array('Error_Msg' => 'Получить данные ЭЛН не удалось.');
					}
				}
			}
		} else {
			return array('Error_Msg' => 'Получить данные ЭЛН не удалось: в ответе нет тега FileOperationsLnUserGetLNDataOut');
		}
	}

	/**
	 * Запрос в ФСС
	 */
	public function queryStickFSSData($data) {
		$stream_context = stream_context_create(array(
			'ssl' => array(
				'verify_peer' => false,
				'verify_peer_name' => false,
				'allow_self_signed' => true
			)
		));

		$options = array(
			'soap_version'=>SOAP_1_2,
			'stream_context' => $stream_context,
			'exceptions'=>1, // обработка ошибок
			'trace'=>1, // трассировка
			'connection_timeout'=>15,
			//'proxy_host' => '192.168.36.31',
			//'proxy_port' => '3128',
			//'proxy_login' => '',
			//'proxy_password' => '',
		);

		if (!empty($this->config->item('REGISTRY_LVN_PROXY_HOST')) && !empty($this->config->item('REGISTRY_LVN_PROXY_PORT'))) {
			$options['proxy_host'] = $this->config->item('REGISTRY_LVN_PROXY_HOST');
			$options['proxy_port'] = $this->config->item('REGISTRY_LVN_PROXY_PORT');
			$options['proxy_login'] = $this->config->item('REGISTRY_LVN_PROXY_LOGIN');
			$options['proxy_password'] = $this->config->item('REGISTRY_LVN_PROXY_PASSWORD');
		}

		if (in_array($data['signType'], array('authapplet', 'authapi', 'authapitomee'))) {
			// надо в XML подсунуть хэш и подпись
			$newxml = new DOMDocument();
			$newxml->loadXML($data['xml']);
			$newxml->getElementsByTagName('DigestValue')->item(0)->nodeValue = $data['Hash'];
			$newxml->getElementsByTagName('SignatureValue')->item(0)->nodeValue = $data['SignedData'];
			$data['xml'] = $newxml->saveXML();
		}

		$files_path = EXPORTPATH_ROOT . "stickfssdata_files";
		if (!file_exists($files_path)) {
			mkdir($files_path);
		}

		// echo "<textarea>{$data['xml']}</textarea>"; die();

		$url = $this->config->item('EvnStickServiceUrl');
		if (empty($url)) {
			return array('Error_Msg' => 'Не указан URL сервиса обмена ЭЛН с ФСС');
		}

		try {
			$error_msg = "Не удалось установить соединение с сервисом";
			set_error_handler(function ($errno, $errstr) {
				throw new Exception($errstr);
			}, E_ALL & ~E_NOTICE);
			$soapClient = new SoapClient($url, $options);
			restore_error_handler();
		} catch ( Exception $e ) {
			return array('Error_Msg' => $e->getMessage());
		}

		$encryption = $this->config->item('EvnStickServiceEncryption');
		if ($encryption) {
			$this->load->model('RegistryESStorage_model');
			$cert_base64 = $this->RegistryESStorage_model->getCertBase64();
			$xml = $this->RegistryESStorage_model->encodeXmlForTransfer($data['xml'], $cert_base64);
			if (is_array($xml)) {
				// если не строка пришла, значит ошибка какая то
				return $xml;
			}
		} else {
			$xml = $data['xml'];
		}


		// echo "<textarea cols='150' rows='30'>{$xml}</textarea>";

		// сохраняем запрос в файл
		$file_path = $files_path . '/es' . $data['StickFSSData_id'] . '_request_' . time() . rand(10000,99999) . '.log';
		file_put_contents($file_path, $data['xml']);
		// зипуем, чтобы не занимать много места
		$file_zip_path = $file_path.'.zip';
		$zip = new ZipArchive;
		$zip->open($file_zip_path, ZIPARCHIVE::CREATE);
		$zip->AddFile($file_path, basename($file_path));
		$zip->close();
		// присваиваем дату и время отправки
		$this->db->query("update StickFSSData with (rowlock) set StickFSSData_xmlExpDT = dbo.tzGetDate(), StickFSSData_xmlExportPath = :StickFSSData_xmlExportPath where StickFSSData_id = :StickFSSData_id", array(
			'StickFSSData_id' => $data['StickFSSData_id'],
			'StickFSSData_xmlExportPath' => $file_zip_path
		));
		@unlink($file_path);

		try {
			$response = $soapClient->__doRequest($xml, $url, 'getLNData', '1.2');
		} catch ( SoapFault $e ) {
			// var_dump($e);
			return array('Error_Msg' => $e->getMessage());
		}

		if (!empty($response)) {
			// присваиваем статус "Отправлен в ФСС"
			$this->db->query("update StickFSSData with (rowlock) set StickFSSDataStatus_id = 2 where StickFSSData_id = :StickFSSData_id", array(
				'StickFSSData_id' => $data['StickFSSData_id']
			));
			if (!empty($_REQUEST['getDebug'])) {
				echo '<textarea>' . $response . '</textarea>';
			}
			if ($encryption) {
				// надо расшифровать, чтобы получить XML с данными
				$this->load->model('RegistryESStorage_model');
				$xml = $this->RegistryESStorage_model->decodeXmlForTransfer($response);
				if (is_array($xml)) {
					// если не строка пришла, значит ошибка какая то
					return $xml;
				}
			} else {
				$xml = $response;
			}

			// сохраняем ответ в файл
			$file_path = $files_path . '/es' . $data['StickFSSData_id'] . '_response_' . time() . rand(10000,99999) . '.log';
			file_put_contents($file_path, $xml);
			// зипуем, чтобы не занимать много места
			$file_zip_path = $file_path.'.zip';
			$zip = new ZipArchive;
			$zip->open($file_zip_path, ZIPARCHIVE::CREATE);
			$zip->AddFile($file_path, basename($file_path));
			$zip->close();
			// записываем ответ
			$this->db->query("update StickFSSData with (rowlock) set StickFSSData_xmlImportPath = :StickFSSData_xmlImportPath where StickFSSData_id = :StickFSSData_id", array(
				'StickFSSData_id' => $data['StickFSSData_id'],
				'StickFSSData_xmlImportPath' => $file_zip_path
			));
			@unlink($file_path);

			// echo "<textarea cols='150' rows='30'>{$xml}</textarea>";
			$response = $this->parseXmlResponse($xml, $data);
			return $response;

		}

		return array('Error_Msg' => 'Ошибка запроса данных ЭЛН');
	}
}