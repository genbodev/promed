<?php
/**
* EvnOnkoNotify_model - модель для работы с таблицей EvnOnkoNotify
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
 *
 * @package      MorbusOnko
 * @author       Пермяков Александр
 * @version      06.2013
 *
 *
 * Магические свойства
 * @property-read int $MorbusType_id
 * @property-read string $morbusTypeSysNick
 *
 * @todo extends EvnNotifyAbstract_model
 */
class EvnOnkoNotify_model extends swModel
{
	/**
	 * Определение имени таблицы с данными объекта
	 * @return string
	 */
	protected function tableName()
	{
		return 'EvnOnkoNotify';
	}

	/**
	 * @return string
	 */
	function getMorbusTypeSysNick()
	{
		return 'onko';
	}

	/**
	 * Определение типа заболевания
	 * @return int
	 * @throws Exception
	 */
	function getMorbusType_id()
	{
		return 3; // для всех регионов
	}

	/**
	 * construct
	 */
	function __construct()
	{
		parent::__construct();
	}
	
	/**
	 * Проверка полей при создании извещения
	 */
	function onkoSpecificFieldsValidate($onkoSpecific) {
		$empty_fields = array();
		if (empty($onkoSpecific['EvnNotifyBase_id']) || empty($onkoSpecific['EvnOnkoNotifyNeglected_id']))
		{
			/*
			 * Проверка наличия обязательных полей специфики для создания извещения, Протокола о запущенной стадии онкозаболевания
			– дата установления диагноза
			– топография (локализация) опухоли
			– морфологический тип опухоли. (Гистология опухоли).
			– стадия опухолевого процесса
			- стадия опухолевого процесса по системе TNM,  если в поле «Стадия опухолевого процесса» указано любое другое значение кроме «Неприменимо»
			– локализация отдаленных метастазов, если в поле «стадия опухолевого процесса» выбрано одно из значений: 4а, 4б, 4с, 4 стадия.
			– метод подтверждения диагноза
			– Причина смерти, если проставлено значение в поле «дата смерти»
			– Аутопсия, если проставлено значение в поле «дата смерти»
			– Результат аутопсии применительно к данной опухоли, если проставлено значение в поле «дата смерти»
			 */
			$fields = array(
				//'MorbusOnko_setDiagDT' => 'Поле «Дата установления диагноза»',
				'Diag_id' => 'Поле «Топография (локализация) опухоли»',			
				//,'OnkoDiag_mid' => 'Поле «Морфологический тип опухоли. (Гистология опухоли)»'
				'TumorStage_id' => 'Поле «Стадия опухолевого процесса»',
				//'OnkoDiagConfType_id' => 'Поле «Метод подтверждения диагноза»',
				'MorbusOnkoLink_id' => 'Раздел «Диагностика»'
			);
			foreach($fields as $f => $n) {
				if(empty($onkoSpecific[$f]))
				{
					$empty_fields[] = $n;
				}
			}
			if(isset($onkoSpecific['TumorStage_id']) && in_array($onkoSpecific['TumorStage_id'],array(13,14,15,16)))
			{
				if(empty($onkoSpecific['MorbusOnko_IsTumorDepo']))
				{
					$empty_fields[] = 'Подраздел «Локализация отдаленных метастазов»';
				}
			}
			$tnm_check = (1 == $this->getFirstResultFromQuery("
				select case 
					when 
						exists (select OnkoTLink_id from v_OnkoTLink (nolock) where Diag_id = :Diag_id and OnkoT_id is null) and
						exists (select OnkoNLink_id from v_OnkoNLink (nolock) where Diag_id = :Diag_id and OnkoN_id is null) and
						exists (select OnkoMLink_id from v_OnkoMLink (nolock) where Diag_id = :Diag_id and OnkoM_id is null)
					then 0
					else 1
				end as TNMcheck
			", array('Diag_id' => $onkoSpecific['Diag_id'])));
			if(isset($onkoSpecific['TumorStage_id']) && 18 != $onkoSpecific['TumorStage_id'] && $tnm_check)
			{
				if(empty($onkoSpecific['OnkoT_id'])
					|| empty($onkoSpecific['OnkoN_id'])
					|| empty($onkoSpecific['OnkoM_id'])
				) {
					$empty_fields[] = 'Поле «Стадия опухолевого процесса по системе TNM»';
				}
			}
			if(isset($onkoSpecific['MorbusOnkoBase_deadDT']))
			{
				$fields = array(
					'Diag_did' => 'Поле «Причина смерти»'
				,'AutopsyPerformType_id' => 'Поле «Аутопсия»'
				,'TumorAutopsyResultType_id' => 'Поле «Результат аутопсии применительно к данной опухоли»'
				);
				if (empty($onkoSpecific['Diag_did']))
				{
					$empty_fields[] = $fields['Diag_did'];
				}
				if (empty($onkoSpecific['AutopsyPerformType_id']))
				{
					$empty_fields[] = $fields['AutopsyPerformType_id'];
				}
				else if (in_array($onkoSpecific['AutopsyPerformType_id'], array(2,3)) && empty($onkoSpecific['TumorAutopsyResultType_id']))
				{
					$empty_fields[] = $fields['TumorAutopsyResultType_id']; 
				}
				else if (3 == $onkoSpecific['AutopsyPerformType_id'] && 8 != $onkoSpecific['TumorAutopsyResultType_id']) 
				{
					$empty_fields[] = 'Поле «Результат аутопсии применительно к данной опухоли» должно содержать значение «неизвестно»';
				}
			}
		}
		$onkoSpecific['Error_Msg'] = (count($empty_fields) == 0)?null:'Для выписки '. (empty($onkoSpecific['EvnNotifyBase_id'])?'Извещения по онкологии':'Протокола о запущенной стадии онкозаболевания') .' необходимо заполнить в специфике:<br />'.implode('<br />',$empty_fields);
		return array($onkoSpecific);
	}

	/**
	 * Получаем данные для проверки наличия извещения/записи регистра в поликлинике
	 */
	function loadEvnVizitPLDataCheckExists($EvnVizitPL_id, $EvnDiagPLSop_id) {
		$query = "
			SELECT 
				MOVD.OnkoT_id,
				MOVD.OnkoN_id,
				MOVD.OnkoM_id,
				MOVD.TumorStage_id,
				MOVD.MorbusOnkoVizitPLDop_setDiagDT as MorbusOnko_setDiagDT,
				MOVD.AutopsyPerformType_id,
				MOVD.Diag_id,
				MOVD.MorbusOnkoVizitPLDop_deadDT as MorbusOnkoBase_deadDT,
				MOVD.Diag_did,
				MOVD.TumorAutopsyResultType_id,
				MOVD.OnkoDiagConfType_id,
				MorbusOnkoLink.MorbusOnkoLink_id,
				coalesce(
					MOVD.MorbusOnkoVizitPLDop_IsTumorDepoBrain,
					MOVD.MorbusOnkoVizitPLDop_IsTumorDepoBones,
					MOVD.MorbusOnkoVizitPLDop_IsTumorDepoKidney,
					MOVD.MorbusOnkoVizitPLDop_IsTumorDepoLiver,
					MOVD.MorbusOnkoVizitPLDop_IsTumorDepoLungs,
					MOVD.MorbusOnkoVizitPLDop_IsTumorDepoLympha,
					MOVD.MorbusOnkoVizitPLDop_IsTumorDepoMarrow,
					MOVD.MorbusOnkoVizitPLDop_IsTumorDepoMulti,
					MOVD.MorbusOnkoVizitPLDop_IsTumorDepoOther,
					MOVD.MorbusOnkoVizitPLDop_IsTumorDepoOvary,
					MOVD.MorbusOnkoVizitPLDop_IsTumorDepoPerito,
					MOVD.MorbusOnkoVizitPLDop_IsTumorDepoSkin,
					MOVD.MorbusOnkoVizitPLDop_IsTumorDepoUnknown
				) as MorbusOnko_IsTumorDepo,
				EV.Morbus_id,
				EON.EvnOnkoNotify_id as EvnNotifyBase_id,
				EONN.EvnOnkoNotifyNeglected_id,
				PR.PersonRegister_id,
				PR.PersonRegisterOutCause_id			
			FROM
				v_MorbusOnkoVizitPLDop MOVD
				inner join v_EvnVizitPL EV on EV.EvnVizitPL_id = MOVD.EvnVizit_id
				left join v_EvnOnkoNotify EON on EON.Morbus_id = EV.Morbus_id
				left join v_EvnOnkoNotifyNeglected EONN on EONN.EvnOnkoNotify_id = EON.EvnOnkoNotify_id
				left join v_PersonRegister PR on PR.Morbus_id = EV.Morbus_id
				outer apply(
					select top 1 MorbusOnkoLink_id
					from v_MorbusOnkoLink mol (nolock)
					where mol.MorbusOnkoVizitPLDop_id = MOVD.MorbusOnkoVizitPLDop_id
				) as MorbusOnkoLink
			WHERE 
				MOVD.EvnVizit_id = :EvnVizitPL_id
				and isnull(MOVD.EvnDiagPLSop_id,0) = isnull(:EvnDiagPLSop_id,0)
				
		";
		$queryParams = array(
			'EvnVizitPL_id' => $EvnVizitPL_id,
			'EvnDiagPLSop_id' => $EvnDiagPLSop_id
		);
		$result = $this->queryResult($query, $queryParams);
		if (empty($result)) {
			return $result;
		}
		$response = $this->onkoSpecificFieldsValidate($result[0]);
		return $response;
	}

	/**
	 * Получаем данные для проверки наличия извещения/записи регистра в КВС
	 */
	function loadEvnSectionDataCheckExists($EvnSection_id, $EvnDiagPLSop_id) {
		$query = "
			SELECT 
				MOL.OnkoT_id,
				MOL.OnkoN_id,
				MOL.OnkoM_id,
				MOL.TumorStage_id,
				MOL.MorbusOnkoLeave_setDiagDT as MorbusOnko_setDiagDT,
				MOB.AutopsyPerformType_id,
				MOL.Diag_id,
				MOB.MorbusOnkoBase_deadDT as MorbusOnkoBase_deadDT,
				MOB.Diag_did,
				MOL.TumorAutopsyResultType_id,
				MOL.OnkoDiagConfType_id,
				MorbusOnkoLink.MorbusOnkoLink_id,
				coalesce(
					MOL.MorbusOnkoLeave_IsTumorDepoBrain,
					MOL.MorbusOnkoLeave_IsTumorDepoBones,
					MOL.MorbusOnkoLeave_IsTumorDepoKidney,
					MOL.MorbusOnkoLeave_IsTumorDepoLiver,
					MOL.MorbusOnkoLeave_IsTumorDepoLungs,
					MOL.MorbusOnkoLeave_IsTumorDepoLympha,
					MOL.MorbusOnkoLeave_IsTumorDepoMarrow,
					MOL.MorbusOnkoLeave_IsTumorDepoMulti,
					MOL.MorbusOnkoLeave_IsTumorDepoOther,
					MOL.MorbusOnkoLeave_IsTumorDepoOvary,
					MOL.MorbusOnkoLeave_IsTumorDepoPerito,
					MOL.MorbusOnkoLeave_IsTumorDepoSkin,
					MOL.MorbusOnkoLeave_IsTumorDepoUnknown
				) as MorbusOnko_IsTumorDepo,
				ES.Morbus_id,
				EON.EvnOnkoNotify_id as EvnNotifyBase_id,
				EONN.EvnOnkoNotifyNeglected_id,
				PR.PersonRegister_id,
				PR.PersonRegisterOutCause_id			
			FROM
				v_MorbusOnkoLeave MOL
				inner join v_EvnSection ES on ES.EvnSection_id = MOL.EvnSection_id
				left join v_Morbus M on M.Morbus_id = ES.Morbus_id
				left join v_MorbusOnkoBase  MOB on MOB.MorbusBase_id = M.MorbusBase_id
				left join v_EvnOnkoNotify EON on EON.Morbus_id = ES.Morbus_id
				left join v_EvnOnkoNotifyNeglected EONN on EONN.EvnOnkoNotify_id = EON.EvnOnkoNotify_id
				left join v_PersonRegister PR on PR.Morbus_id = ES.Morbus_id
				outer apply(
					select top 1 MorbusOnkoLink_id
					from v_MorbusOnkoLink molink (nolock)
					where molink.MorbusOnkoLeave_id = MOL.MorbusOnkoLeave_id
				) as MorbusOnkoLink	
			WHERE 
				MOL.EvnSection_id = :EvnSection_id	
				and isnull(MOL.EvnDiag_id,0) = isnull(:EvnDiag_id,0)
		";
		$queryParams = array(
			'EvnSection_id' => $EvnSection_id,
			'EvnDiag_id' => $EvnDiagPLSop_id
		);
		$result = $this->queryResult($query, $queryParams);
		if(empty($result)) {
			return $result;
		}
		$response = $this->onkoSpecificFieldsValidate($result[0]);
		return $response;
	}

	/**
	 * Получаем данные для проверки наличия извещения/записи регистра в стоматологии
	 */
	function loadEvnDiagPLStomDataCheckExists($EvnDiagPLStom_id) {
		$query = "
			SELECT 
				MODS.OnkoT_id,
				MODS.OnkoN_id,
				MODS.OnkoM_id,
				MODS.TumorStage_id,
				MODS.MorbusOnkoDiagPLStom_setDiagDT as MorbusOnko_setDiagDT,
				MOB.AutopsyPerformType_id,
				MODS.Diag_id,
				MOB.MorbusOnkoBase_deadDT as MorbusOnkoBase_deadDT,
				MOB.Diag_did,
				MODS.TumorAutopsyResultType_id,
				MODS.OnkoDiagConfType_id,
				coalesce(
					MODS.MorbusOnkoDiagPLStom_IsTumorDepoBrain,
					MODS.MorbusOnkoDiagPLStom_IsTumorDepoBones,
					MODS.MorbusOnkoDiagPLStom_IsTumorDepoKidney,
					MODS.MorbusOnkoDiagPLStom_IsTumorDepoLiver,
					MODS.MorbusOnkoDiagPLStom_IsTumorDepoLungs,
					MODS.MorbusOnkoDiagPLStom_IsTumorDepoLympha,
					MODS.MorbusOnkoDiagPLStom_IsTumorDepoMarrow,
					MODS.MorbusOnkoDiagPLStom_IsTumorDepoMulti,
					MODS.MorbusOnkoDiagPLStom_IsTumorDepoOther,
					MODS.MorbusOnkoDiagPLStom_IsTumorDepoOvary,
					MODS.MorbusOnkoDiagPLStom_IsTumorDepoPerito,
					MODS.MorbusOnkoDiagPLStom_IsTumorDepoSkin,
					MODS.MorbusOnkoDiagPLStom_IsTumorDepoUnknown
				) as MorbusOnko_IsTumorDepo,
				EVN.Morbus_id,
				EON.EvnOnkoNotify_id as EvnNotifyBase_id,
				EONN.EvnOnkoNotifyNeglected_id,
				PR.PersonRegister_id,
				PR.PersonRegisterOutCause_id			
			FROM
				v_MorbusOnkoDiagPLStom MODS
				inner join v_evn EVN on EVN.Evn_id = MODS.EvnDiagPLStom_id
				left join v_Morbus M on M.Morbus_id = EVN.Morbus_id
				left join v_MorbusOnkoBase  MOB on MOB.MorbusBase_id = M.MorbusBase_id
				left join v_EvnOnkoNotify EON on EON.Morbus_id = EVN.Morbus_id
				left join v_EvnOnkoNotifyNeglected EONN on EONN.EvnOnkoNotify_id = EON.EvnOnkoNotify_id
				left join v_PersonRegister PR on PR.Morbus_id = EVN.Morbus_id					
			WHERE 
				MODS.EvnDiagPLStom_id = :EvnDiagPLStom_id		
		";
		$queryParams = array(
			'EvnDiagPLStom_id' => $EvnDiagPLStom_id
		);
		$result = $this->queryResult($query, $queryParams);
		if(empty($result)) {
			return $result;
		}
		$response = $this->onkoSpecificFieldsValidate($result[0]);
		return $response;
	}

	/**
	 * Получаем данные для проверки наличия извещения/записи регистра
	 *
	 * В общем случае по одному заболеванию можно создать одно извещение и одну запись регистра
	 * Также получает данные для проверки наличия и создания Протокола о запущенной стадии онкозаболевания
	 * @param $Person_id
	 * @param $evn_Diag_id
	 * @return bool|array Если заболевание ещё не создано, возвращается пустой массив, а в случае ошибки - false
	 */
	function loadDataCheckExists($Person_id, $evn_Diag_id = null)
	{
		$tableName = 'EvnOnkoNotify';
		$this->load->library('swMorbus');
		$response = swMorbus::getStaticMorbusCommon()->checkExistsExtended('onko', $Person_id, $evn_Diag_id,"
				,EN.{$tableName}_id as EvnNotifyBase_id
				,PR.PersonRegister_id
				,PR.PersonRegisterOutCause_id
				,MO.MorbusOnko_setDiagDT
				,Morbus.Diag_id
				,MO.OnkoDiag_mid
				,MO.TumorStage_id
				,MO.OnkoT_id
				,MO.OnkoN_id
				,MO.OnkoM_id
				,coalesce(MO.MorbusOnko_IsTumorDepoBrain,MO.MorbusOnko_IsTumorDepoBones,MO.MorbusOnko_IsTumorDepoKidney,MO.MorbusOnko_IsTumorDepoLiver,MO.MorbusOnko_IsTumorDepoLungs,MO.MorbusOnko_IsTumorDepoLympha,MO.MorbusOnko_IsTumorDepoMarrow,MO.MorbusOnko_IsTumorDepoMulti,MO.MorbusOnko_IsTumorDepoOther,MO.MorbusOnko_IsTumorDepoOvary,MO.MorbusOnko_IsTumorDepoPerito,MO.MorbusOnko_IsTumorDepoSkin,MO.MorbusOnko_IsTumorDepoUnknown) as MorbusOnko_IsTumorDepo
				,MO.OnkoDiagConfType_id
				,MOB.Diag_did
				,MOB.AutopsyPerformType_id
				,MO.TumorAutopsyResultType_id
				,MOB.MorbusOnkoBase_deadDT
				,EONN.EvnOnkoNotifyNeglected_id" ,"
				left join v_{$tableName} EN with (nolock) on EN.Morbus_id = Morbus.Morbus_id
				left join v_PersonRegister PR with (nolock) on PR.Morbus_id = Morbus.Morbus_id
				left join v_EvnOnkoNotifyNeglected EONN with (nolock) on EONN.EvnOnkoNotify_id = EN.EvnOnkoNotify_id
				left join v_MorbusOnkoBase MOB with (nolock) on MOB.MorbusBase_id = MorbusBase.MorbusBase_id
				inner join v_MorbusOnko MO with (nolock) on MO.Morbus_id = Morbus.Morbus_id"
		);
		if (empty($response)) {
			return $response;
		}
		$empty_fields = array();
		if (empty($response[0]['EvnNotifyBase_id']) || empty($response[0]['EvnOnkoNotifyNeglected_id']))
		{
			/*
			 * Проверка наличия обязательных полей специфики для создания извещения, Протокола о запущенной стадии онкозаболевания
			– дата установления диагноза
			– топография (локализация) опухоли
			– морфологический тип опухоли. (Гистология опухоли).
			– стадия опухолевого процесса
			- стадия опухолевого процесса по системе TNM,  если в поле «Стадия опухолевого процесса» указано любое другое значение кроме «Неприменимо»
			– локализация отдаленных метастазов, если в поле «стадия опухолевого процесса» выбрано одно из значений: 4а, 4б, 4с, 4 стадия.
			– метод подтверждения диагноза
			– Причина смерти, если проставлено значение в поле «дата смерти»
			– Аутопсия, если проставлено значение в поле «дата смерти»
			– Результат аутопсии применительно к данной опухоли, если проставлено значение в поле «дата смерти»
			 */
			$fields = array(
				'MorbusOnko_setDiagDT' => 'Поле «Дата установления диагноза»',
				'Diag_id' => 'Поле «Топография (локализация) опухоли»',
				//,'OnkoDiag_mid' => 'Поле «Морфологический тип опухоли. (Гистология опухоли)»'
				'TumorStage_id' => 'Поле «Стадия опухолевого процесса»',
				'OnkoDiagConfType_id' => 'Поле «Метод подтверждения диагноза»',
			);
			foreach($fields as $f => $n) {
				if(empty($response[0][$f]))
				{
					$empty_fields[] = $n;
				}
			}
			if(isset($response[0]['TumorStage_id']) && in_array($response[0]['TumorStage_id'],array(13,14,15,16)))
			{
				if(empty($response[0]['MorbusOnko_IsTumorDepo']))
				{
					$empty_fields[] = 'Подраздел «Локализация отдаленных метастазов»';
				}
			}
			$tnm_check = (1 == $this->getFirstResultFromQuery("
				select case 
					when 
						exists (select OnkoTLink_id from v_OnkoTLink (nolock) where Diag_id = :Diag_id and OnkoT_id is null) and
						exists (select OnkoNLink_id from v_OnkoNLink (nolock) where Diag_id = :Diag_id and OnkoN_id is null) and
						exists (select OnkoMLink_id from v_OnkoMLink (nolock) where Diag_id = :Diag_id and OnkoM_id is null)
					then 0
					else 1
				end as TNMcheck
			", array('Diag_id' => $response[0]['Diag_id'])));
			if(isset($response[0]['TumorStage_id']) && 18 != $response[0]['TumorStage_id'] && $tnm_check)
			{
				if(empty($response[0]['OnkoT_id'])
					|| empty($response[0]['OnkoN_id'])
					|| empty($response[0]['OnkoM_id'])
				) {
					$empty_fields[] = 'Поле «Стадия опухолевого процесса по системе TNM»';
				}
			}
			if(isset($response[0]['MorbusOnkoBase_deadDT']))
			{
				$fields = array(
					'Diag_did' => 'Поле «Причина смерти»'
				,'AutopsyPerformType_id' => 'Поле «Аутопсия»'
				,'TumorAutopsyResultType_id' => 'Поле «Результат аутопсии применительно к данной опухоли»'
				);
				if (empty($response[0]['Diag_did']))
				{
					$empty_fields[] = $fields['Diag_did'];
				}
				if (empty($response[0]['AutopsyPerformType_id']))
				{
					$empty_fields[] = $fields['AutopsyPerformType_id'];
				}
				else if (in_array($response[0]['AutopsyPerformType_id'], array(2,3)) && empty($response[0]['TumorAutopsyResultType_id']))
				{
					$empty_fields[] = $fields['TumorAutopsyResultType_id'];
				}
				else if (3 == $response[0]['AutopsyPerformType_id'] && 8 != $response[0]['TumorAutopsyResultType_id'])
				{
					$empty_fields[] = 'Поле «Результат аутопсии применительно к данной опухоли» должно содержать значение «неизвестно»';
				}
			}
		}
		$response[0]['Error_Msg'] = (count($empty_fields) == 0)?null:'Для выписки '. (empty($response[0]['EvnNotifyBase_id'])?'Извещения по онкологии':'Протокола о запущенной стадии онкозаболевания') .' необходимо заполнить в специфике:<br />'.implode('<br />',$empty_fields);
		return $response;
	}
	
	/**
	 * Проверка наличия извещения в статусе "Отправлено" (не включено в регистр и не отклонено)
	 *
	 * @param $Person_id
	 * @param $evn_Diag_id
	 */
	function checkNotifyExists($Person_id, $notifyType = null)
	{
		$query = "
			select top 1
				EON.EvnOnkoNotify_id
			from
				v_EvnOnkoNotify EON with (nolock)
				left join v_PersonRegister PR with (nolock) on EON.EvnOnkoNotify_id = PR.EvnNotifyBase_id
			where
				EON.Person_id = ? and 
				EON.EvnOnkoNotify_niDate is null 
		";
		switch($notifyType) {
			case 'onko':
				//для извещений по онкологии проверяем существование отправленных извещений и включённых в регистр
			break;
			default:
				$query .= ' and PR.PersonRegister_setDate is null';
		}
		$res = $this->getFirstResultFromQuery($query, array($Person_id));
		return !empty($res); // true, если найдено
	}

	/**
	 * load
	 * @param $data
	 * @return bool
	 */
	function load($data)
	{
		$query = "
			select distinct
				EON.EvnOnkoNotify_id,
				EON.EvnOnkoNotify_pid,
				EON.Morbus_id,
				EONN.EvnOnkoNotifyNeglected_id,
				EON.Server_id,
				EON.PersonEvn_id,
				EON.Person_id,
				MOP.Ethnos_id,
				MOP.KLAreaType_id,
				MOP.OnkoOccupationClass_id,
				convert(varchar(10), MO.MorbusOnko_firstSignDT, 104) as MorbusOnko_firstSignDT,
				convert(varchar(10), MO.MorbusOnko_firstVizitDT, 104) as MorbusOnko_firstVizitDT,
				MO.Lpu_foid,
				Morbus.Diag_id,-- диагноз заболевания и Топография (локализация) опухоли
				convert(varchar(10), MO.MorbusOnko_setDiagDT, 104) as MorbusOnko_setDiagDT,
				MO.OnkoDiag_mid as Diag_mid,
				MO.MorbusOnko_NumHisto,
				MO.OnkoT_id,
				MO.OnkoN_id,
				MO.OnkoM_id,
				MO.TumorStage_id,
				
				MO.OnkoDiagConfType_id,
				
				MO.TumorCircumIdentType_id,
				convert(varchar(10), MOB.MorbusOnkoBase_deadDT, 104) as MorbusOnkoBase_deadDT,
				MOB.Diag_did,
				MOB.MorbusOnkoBase_deathCause,
				MOB.AutopsyPerformType_id,
				MO.TumorAutopsyResultType_id,
				convert(varchar(10), EON.EvnOnkoNotify_setDT, 104) as EvnOnkoNotify_setDT,
				
				MO.MorbusOnko_IsTumorDepoUnknown,
				MO.MorbusOnko_IsTumorDepoBones,
				MO.MorbusOnko_IsTumorDepoLiver,
				MO.MorbusOnko_IsTumorDepoSkin,
				MO.MorbusOnko_IsTumorDepoKidney,
				MO.MorbusOnko_IsTumorDepoOvary,
				MO.MorbusOnko_IsTumorDepoPerito,
				
				MO.MorbusOnko_IsTumorDepoLympha,
				MO.MorbusOnko_IsTumorDepoLungs,
				MO.MorbusOnko_IsTumorDepoBrain,
				MO.MorbusOnko_IsTumorDepoMarrow,
				MO.MorbusOnko_IsTumorDepoOther,
				MO.MorbusOnko_IsTumorDepoMulti,
				
				MO.OnkoLateDiagCause_id,
				EONN.EvnOnkoNotifyNeglected_ClinicalData,
				EONN.Lpu_cid,
				convert(varchar(10), EONN.EvnOnkoNotifyNeglected_setConfDT, 104) as EvnOnkoNotifyNeglected_setConfDT,
				EONN.EvnOnkoNotifyNeglected_OrgDescr,
				convert(varchar(10), EONN.EvnOnkoNotifyNeglected_setNotifyDT, 104) as EvnOnkoNotifyNeglected_setNotifyDT
			from
				v_EvnOnkoNotify EON with (nolock)
			inner join v_MorbusOnko MO with (nolock) on MO.Morbus_id = EON.Morbus_id
			inner join v_MorbusOnkoPerson MOP with (nolock) on MOP.Person_id = EON.Person_id
			inner join v_Morbus Morbus with (nolock) on Morbus.Morbus_id = MO.Morbus_id
			inner join v_MorbusOnkoBase MOB with (nolock) on MOB.MorbusBase_id = Morbus.MorbusBase_id
			left join EvnOnkoNotifyNeglected EONN with(nolock) on EONN.EvnOnkoNotify_id = EON.EvnOnkoNotify_id
			where
				EON.EvnOnkoNotify_id = ?
		";
		$res = $this->db->query($query, array($data['EvnOnkoNotify_id']));
		if ( is_object($res) )
			return $res->result('array');
		else
			return false;		
	}

	/**
	 * Получение онко извещений пользователя(отправленных или включённых в регистр)
	 */
	function getEvnOnkoNotifyList($data) {
		$query = "
			select 
				EON.EvnOnkoNotify_id,
				M.Diag_id,
				Diag.Diag_Code
			from 
				v_EvnOnkoNotify EON with (nolock)
				inner join v_Morbus M with (nolock) on M.Morbus_id = EON.Morbus_id 
				--inner join v_MorbusOnko MO with (nolock) on MO.Morbus_id = M.Morbus_id 

				inner join v_Diag Diag with (nolock) on Diag.Diag_id = M.Diag_id
			where 
				EON.Person_id = :Person_id
				and	EON.EvnOnkoNotify_niDate is null 
		";
		$queryParams = array(
			'Person_id' => $data['Person_id']
		);

		return $this->queryResult($query, $queryParams);
	}

	/**
	 * Сохранение объекта «Извещение»
	 * @param array $data Массив, полученный методом ProcessInputData контроллера
	 * Если массив не передается, то ранее должны быть установлены данные
	 * с помощью метода applyData($data) или методов setParams($data) и setAttributes($data)
	 * Также должен быть указан сценарий бизнес-логики с помощью метода setScenario
	 * @param bool $isAllowTransaction Флаг необходимости транзакции
	 * Если транзакция была начата ранее, то нужно установить false
	 * @return array Ответ модели в формате ассоциативного массива,
	 * пригодном для обработки методом ProcessModelSave контроллера
	 * Обязательно должны быть ключи: Error_Msg и идешник объекта
	 */
	public function doSave($data = array(), $isAllowTransaction = true)
	{
		try {
			$tableName = $this->tableName();
			$this->isAllowTransaction = $isAllowTransaction;
			if ( !$this->beginTransaction() ) {
				$this->isAllowTransaction = false;
				throw new Exception('Не удалось запустить транзакцию', 500);
			}

			//$this->_beforeSave($data);
			if (empty($data)) {
				throw new Exception('Не переданы параметры', 500);
			}
			if (!empty($data['scenario'])) {
				$this->setScenario($data['scenario']);
			}
			$this->setParams($data);
			//$this->setAttributes($data);
			//$this->_validate();
			if (empty($data['Person_id']) || empty($data['PersonEvn_id']) || false == isset($data['Server_id'])) {
				throw new Exception('Не переданы параметры человека', 500);
			}
			if (empty($data['Lpu_id'])) {
				throw new Exception('Не передан параметр МО пользователя', 500);
			}
			if (empty($data['MedPersonal_id'])) {
				throw new Exception('Не передан параметр Врач, заполнивший извещение', 500);
			}
			if (empty($data[$tableName . '_pid'])) {
				throw new Exception('Не передан параметр Учетный документ', 500);
			}
			if (empty($data['EvnOnkoNotify_setDT'])) {
				throw new Exception('Не передан параметр Дата заполнения извещения', 500);
			}

			$this->load->library('swMorbus');
			$tmp = swMorbus::onBeforeSaveEvnNotify($this->morbusTypeSysNick, $data[$tableName . '_pid'], $data['session'], $data['EvnDiagPLSop_id']);
			$data['MorbusType_id'] = $tmp['MorbusType_id'];
			$data['Morbus_id'] = $tmp['Morbus_id'];
			$data['Morbus_Diag_id'] = $tmp['Diag_id'];

			// проверяем существование извещений(отправленных или в регистре)
			if ($this->getRegionNick() == 'kz') {
				if ($this->checkNotifyExists($data['Person_id'], 'onko') === true) {
					throw new Exception('Извещение об онкобольном уже отправлено. Повторное создание извещения не предусматривается');
				}
			} else {
				$EvnOnkoNotifyList = $this->getEvnOnkoNotifyList($data);
				$exist = false;
				foreach ($EvnOnkoNotifyList as $value) {
					if($tmp['Diag_id'] == $value['Diag_id']) {
						$exist = true;
					}
				}
				if ( $exist ){
					throw new Exception('Извещение об онкозаболевании пациента уже отправлено, либо пациент уже включен в регистр с указанным диагнозом. Повторное создание извещения по указанному диагнозу не предусматривается.');
				}
			}

			$queryParams = array(
				$tableName . '_pid' => $data[$tableName . '_pid'],
				'Lpu_id' => $data['Lpu_id'],
				'Server_id' => $data['Server_id'],
				'PersonEvn_id' => $data['PersonEvn_id'],
				'Morbus_id' => $data['Morbus_id'],
				'MorbusType_id' => $data['MorbusType_id'],
				'EvnOnkoNotify_setDT' => $data['EvnOnkoNotify_setDT'],
				'MedPersonal_id' => $data['MedPersonal_id'],

				'Lpu_sid' => $data['Lpu_sid'],
			);
			if(!empty($data['paramsForAPI']) && is_array($data['paramsForAPI']) && count($data['paramsForAPI'])>0){
				$queryParams = array_merge($queryParams, $data['paramsForAPI']);
			}
			$pk = $this->primaryKey();
			$queryParams[$pk] = array(
				'value' => empty($data[$pk]) ? null : $data[$pk],
				'out' => true,
				'type' => 'bigint',
			);
			$queryParams['pmUser_id'] = $this->promedUserId;
			$tmp = $this->_save($queryParams);
			//$this->setAttribute(self::ID_KEY, $tmp[0][$this->primaryKey()]);
			$data[$pk] = $tmp[0][$pk];

			//$this->_afterSave($tmp);
			$tmp = swMorbus::onAfterSaveEvnNotify($this->morbusTypeSysNick, array(
				'EvnNotifyBase_id' => $data[$pk],
				'EvnNotifyBase_pid' => $data[$tableName . '_pid'],
				'EvnNotifyBase_setDate' => $data['EvnOnkoNotify_setDT'],
				'Server_id' => $data['Server_id'],
				'PersonEvn_id' => $data['PersonEvn_id'],
				'Person_id' => $data['Person_id'],
				'Morbus_id' => $data['Morbus_id'],
				'MorbusType_id' => $data['MorbusType_id'],
				'Morbus_Diag_id' => $data['Morbus_Diag_id'],
				'Lpu_id' => $data['Lpu_id'],
				'MedPersonal_id' => $data['MedPersonal_id'],
				'session' => $this->sessionParams
			));
			$this->_saveResponse = array_merge($this->_saveResponse, $tmp);

			$tmp = $this->createEvnNotifyRegisterInclude(array(
				'EvnNotifyRegister_pid' => $data[$tableName . '_pid'],
				'PersonRegisterType_SysNick' => 'onko',
				'MorbusType_SysNick' => 'onko',
				'Server_id' => $data['Server_id'],
				'PersonEvn_id' => $data['PersonEvn_id'],
				'Person_id' => $data['Person_id'],
				'MedPersonal_id' => $data['MedPersonal_id'],
				'Lpu_did' => $data['Lpu_id'],
				'Diag_id' => $data['Morbus_Diag_id'],
				'pmUser_id' => $data['pmUser_id'],
			));
			$this->_saveResponse = array_merge($this->_saveResponse, $tmp);

			if ( !$this->commitTransaction() ) {
				$this->isAllowTransaction = false;
				throw new Exception('Не удалось зафиксировать транзакцию', 500);
			}
			//$this->_saveResponse[$this->primaryKey(true)] = $this->id;
			$this->_saveResponse[$pk] = $data[$pk];
		} catch (Exception $e) {
			$this->rollbackTransaction();
			$this->_saveResponse['Error_Msg'] = $e->getMessage();
			if ($this->isDebug && $e->getCode() == 500) {
				// только на тестовом и только, если что-то пошло не так
				$this->_saveResponse['Error_Msg'] .= ' ' . $e->getTraceAsString();
			}
			$this->_saveResponse['Error_Code'] = $e->getCode();
		}
		$this->_onSave();
		return $this->_saveResponse;
	}

	/**
	 * Создания измещения для регистра
	 */
	function createEvnNotifyRegisterInclude($data) {
		$this->load->library('swPersonRegister');
		if (false == swPersonRegister::isAllow($data['PersonRegisterType_SysNick'])) {
			throw new Exception('Работа с этим типом регистра не доступна');
		}
		$instanceModelName = swPersonRegister::getEvnNotifyRegisterModelName($data['PersonRegisterType_SysNick']);
		$this->load->model($instanceModelName);
		// могут быть региональные модели типа ufa_EvnNotifyRegister_model, наследующие EvnNotifyRegister_model
		$className = get_class($this->{$instanceModelName});
		$instance = new $className($data['PersonRegisterType_SysNick'], 1);

		if (empty($instance)) {
			throw new Exception('Не найдена модель для извещения для регистра');
		}

		$err = array();
		$rules = $instance->getInputRules(swModel::SCENARIO_DO_SAVE);
		$params = $instance->_checkInputData($rules, $data, $err);
		if (isset($err[0]) && !empty($err[0]['Error_Msg'])) {
			throw new Exception($err[0]['Error_Msg']);
		}
		$params['scenario'] = swModel::SCENARIO_DO_SAVE;

		if(empty($instance->sessionParams['lpu_id']) && (!empty($params['Lpu_oid']) || !empty($params['Lpu_did']))) {
			if(!empty($params['Lpu_oid'])){
				$instance->Lpu_id = $params['Lpu_oid'];
			} else {
				$instance->Lpu_id = $params['Lpu_did'];
			}
		}

		$response = $instance->doSave($params);
		if (!empty($response['Error_Msg'])) {
			throw new Exception($response['Error_Msg']);
		}
		return $response;
	}

	/**
	 * getDataForPrint
	 * @param $data
	 * @return bool
	 */
	function getDataForPrint($data)
	{
		$query = "
			select --distinct
				EON.EvnOnkoNotify_id
				,convert(varchar(10), EON.EvnOnkoNotify_setDT, 104) as EvnOnkoNotify_setDT
				,PS.Person_SurName
				,PS.Person_FirName
				,PS.Person_SecName
				,convert(varchar(10), PS.Person_BirthDay, 104) as Person_BirthDay
				,sex.Sex_Name
				,isnull(AreaType.KLAreaType_Name,'неизвестно') as KLAreaType_Name
				,isnull(rtrim(PersonAddress.Address_Address), '') as Person_Address
				,Ethnos.Ethnos_Name
				,ooc.OnkoOccupationClass_Name
				,MO.MorbusOnko_NumTumor as Poryadkovyi_nomer_dannoi_opuholi_u_dannogo_bolnogo
				,convert(varchar(10), isnull(EON.EvnOnkoNotify_setFirstDT, MO.MorbusOnko_firstVizitDT), 104) as MorbusOnko_firstVizitDT
				,convert(varchar(10), isnull(EON.EvnOnkoNotify_setDiagDT, MO.MorbusOnko_setDiagDT), 104) as MorbusOnko_setDiagDT
				,diag.Diag_FullName -- диагноз заболевания и Топография (локализация) опухоли
				,od.OnkoDiag_Code + '. ' + od.OnkoDiag_Name as OnkoDiag_FullName
				,OnkoT.OnkoT_Name
				,OnkoN.OnkoN_Name
				,OnkoM.OnkoM_Name
				,ts.TumorStage_Name
				,tcit.TumorCircumIdentType_Name
				,odct.OnkoDiagConfType_Name
				,isnull(EON.EvnOnkoNotify_IsTumorDepoUnknown,MO.MorbusOnko_IsTumorDepoUnknown) as IsTumorDepoUnknown
				,isnull(EON.EvnOnkoNotify_IsTumorDepoBones,MO.MorbusOnko_IsTumorDepoBones) as IsTumorDepoBones
				,isnull(EON.EvnOnkoNotify_IsTumorDepoLiver,MO.MorbusOnko_IsTumorDepoLiver) as IsTumorDepoLiver
				,isnull(EON.EvnOnkoNotify_IsTumorDepoSkin,MO.MorbusOnko_IsTumorDepoSkin) as IsTumorDepoSkin
				,isnull(EON.EvnOnkoNotify_IsTumorDepoKidney,MO.MorbusOnko_IsTumorDepoKidney) as IsTumorDepoKidney
				,isnull(EON.EvnOnkoNotify_IsTumorDepoOvary,MO.MorbusOnko_IsTumorDepoOvary) as IsTumorDepoOvary
				,isnull(EON.EvnOnkoNotify_IsTumorDepoPerito,MO.MorbusOnko_IsTumorDepoPerito) as IsTumorDepoPerito
				,isnull(EON.EvnOnkoNotify_IsTumorDepoLympha,MO.MorbusOnko_IsTumorDepoLympha) as IsTumorDepoLympha
				,isnull(EON.EvnOnkoNotify_IsTumorDepoLungs,MO.MorbusOnko_IsTumorDepoLungs) as IsTumorDepoLungs
				,isnull(EON.EvnOnkoNotify_IsTumorDepoBrain,MO.MorbusOnko_IsTumorDepoBrain) as IsTumorDepoBrain
				,isnull(EON.EvnOnkoNotify_IsTumorDepoMarrow,MO.MorbusOnko_IsTumorDepoMarrow) as IsTumorDepoMarrow
				,isnull(EON.EvnOnkoNotify_IsTumorDepoOther,MO.MorbusOnko_IsTumorDepoOther) as IsTumorDepoOther
				,isnull(EON.EvnOnkoNotify_IsTumorDepoMulti,MO.MorbusOnko_IsTumorDepoMulti) as IsTumorDepoMulti
				
				,lpu_s.Lpu_Nick as LpuS_Name --извещение направлено в ЛПУ
				,lpu_m.Lpu_Nick as LpuM_Name --в какое медицинское учреждение направлен больной
				,mp_d.Person_Fin as MedPersonal_Fin
				,lpu_d.Lpu_Name as Lpu_Name
				,lpu_d.Lpu_Nick as LpuD_Name
				,isnull(rtrim(LpuAddress.Address_Address), '') as LpuD_Address
			from
				v_EvnOnkoNotify EON with (nolock)
				inner join v_MorbusOnkoPerson MOP with (nolock) on MOP.Person_id = EON.Person_id
				inner join v_Morbus M with (nolock) on M.Morbus_id = EON.Morbus_id
				inner join v_MorbusOnko MO with (nolock) on MO.Morbus_id = EON.Morbus_id
				/*outer apply (
					select top 1 * from v_MorbusOnko with (nolock) where Morbus_id = EON.Morbus_id order by MorbusOnko_insDT asc
				) MO*/
				left join v_PersonState PS with (nolock) on EON.Person_id = PS.Person_id
				left join v_Sex sex with (nolock) on PS.Sex_id = sex.Sex_id
				left join v_KLAreaType AreaType with (nolock) on MOP.KLAreaType_id = AreaType.KLAreaType_id
				left join v_Address PersonAddress with (nolock) on PS.UAddress_id = PersonAddress.Address_id
				left join v_Ethnos Ethnos with (nolock) on isnull(EON.Ethnos_id,MOP.Ethnos_id) = Ethnos.Ethnos_id
				left join v_OnkoOccupationClass ooc with (nolock) on isnull(EON.OnkoOccupationClass_id,MOP.OnkoOccupationClass_id) = ooc.OnkoOccupationClass_id
				left join v_TumorStage ts with (nolock) on isnull(EON.TumorStage_id,MO.TumorStage_id) = ts.TumorStage_id
				left join v_OnkoM OnkoM with (nolock) on isnull(EON.OnkoM_id,MO.OnkoM_id) = OnkoM.OnkoM_id
				left join v_OnkoN OnkoN with (nolock) on isnull(EON.OnkoN_id,MO.OnkoN_id) = OnkoN.OnkoN_id
				left join v_OnkoT OnkoT with (nolock) on isnull(EON.OnkoT_id,MO.OnkoT_id) = OnkoT.OnkoT_id
				left join v_TumorCircumIdentType tcit with (nolock) on isnull(EON.TumorCircumIdentType_id,MO.TumorCircumIdentType_id) = tcit.TumorCircumIdentType_id
				left join v_Lpu lpu_d with (nolock) on EON.Lpu_id = lpu_d.Lpu_id
				left join v_MedPersonal mp_d with (nolock) on EON.MedPersonal_id = mp_d.MedPersonal_id and EON.Lpu_id = mp_d.Lpu_id
				left join v_Address LpuAddress with (nolock) on lpu_d.UAddress_id = LpuAddress.Address_id
				left join v_Lpu lpu_s with (nolock) on EON.Lpu_sid = lpu_s.Lpu_id
				left join v_Lpu lpu_m with (nolock) on EON.Lpu_mid = lpu_m.Lpu_id
				left join v_Diag Diag with (nolock) on M.Diag_id = Diag.Diag_id
				left join v_OnkoDiag od with (nolock) on MO.OnkoDiag_mid = od.OnkoDiag_id
				left join v_OnkoDiagConfType odct with (nolock) on MO.OnkoDiagConfType_id = odct.OnkoDiagConfType_id
			where
				EON.EvnOnkoNotify_id = ?
		";
		$res = $this->db->query($query, array($data['EvnOnkoNotify_id']));
		if ( is_object($res) )
			return $res->result('array');
		else
			return false;		
	}
	
	/**
	 * getDataForSpecific
	 * @param $data
	 * @return bool
	 */
	function getDataForSpecific($data)
	{
		$query = "
			SELECT
				case
					when 1=1 then 'edit'
					else 'view'
				end as accessType,
				EON.EvnOnkoNotify_id as MorbusOnkoEvnNotify_id,
				MO.MorbusOnko_id,
				convert(varchar(10), EON.EvnOnkoNotify_setDate, 104) as EvnOnkoNotify_setDate,
				case 
					when EON.EvnOnkoNotify_niDate is null and PR.PersonRegister_setDate is not null then 'Включено в регистр'
					when EON.EvnOnkoNotify_niDate is not null then 'Отклонено'
					else 'Отправлено'
				end as EvnNotifyStatus_Name,
				case 
					when EON.EvnOnkoNotify_niDate is not null and EON.PersonRegisterFailIncludeCause_id = 1 then 'Отклонено (ошибка в Извещении)'
					when EON.EvnOnkoNotify_niDate is not null and EON.PersonRegisterFailIncludeCause_id = 2 then 'Отклонено (решение оператора)'
					else ''
				end as EvnNotifyRejectStatus_Name,
				EON.EvnOnkoNotify_Comment,
				:Evn_id as MorbusOnko_pid,
				:Evn_id as EvnVizitPL_id,
				M.Morbus_id,
				EON.Person_id,
				D.Diag_id,
				D.Diag_Code
			FROM
				v_EvnOnkoNotify EON with (nolock)
				inner join v_MorbusOnkoPerson MOP with (nolock) on MOP.Person_id = EON.Person_id
				inner join v_Morbus M with (nolock) on M.Morbus_id = EON.Morbus_id
				inner join v_MorbusOnko MO with (nolock) on MO.Morbus_id = M.Morbus_id 
				left join v_PersonRegister PR with (nolock) on EON.EvnOnkoNotify_id = PR.EvnNotifyBase_id
				left join v_Diag D with (nolock) on D.Diag_id = M.Diag_id
			where
				M.Morbus_id = :Morbus_id
		";
		$params = array(
			'Morbus_id' => $data['Morbus_id'],
			'Evn_id' => $data['Evn_id'],
		);
		$result = $this->db->query($query, $params);
		if ( is_object($result) ) {
			$response = $result->result('array');
			$this->load->library('swMorbus');
			$response = swMorbus::processingEvnData($response, 'EvnVizitPL');
			return $response;
		} else {
			return false;
		}
	}
	
	/**
	 * Метод для API. Получение извещения об онкобольном
	 * @param type $data
	 * @return int
	 */
	function loadAPI($data){
		$params = array();
		$where = '';
		if(!empty($data['EvnOnkoNotify_id'])){
			$params['EvnOnkoNotify_id'] = $data['EvnOnkoNotify_id'];
			$where .= ' AND EON.EvnOnkoNotify_id = :EvnOnkoNotify_id ';
		}
		if(!empty($data['Person_id'])){
			$params['Person_id'] = $data['Person_id'];
			$where .= ' AND EON.Person_id = :Person_id ';
		}
		if(count($params) == 0){
			return array('Error_Msg' => 'не передан ни один из параметров');
		}
		$q = "
			select distinct
				EON.EvnOnkoNotify_id,	-- Идентификатор извещения
				EON.Person_id,	-- ссылка на человека в Person
				EON.EvnOnkoNotify_pid as Evn_pid,
				EON.Lpu_sid,	-- Направить извещение
				EON.MedPersonal_id,	-- Врач, заполнивший  извещение 
				convert(varchar(10), EON.EvnOnkoNotify_setDiagDT, 104) as EvnOnkoNotify_setDiagDT,	-- дата заполнения извещения 
				convert(varchar(10), EON.EvnOnkoNotify_setDT, 104) as EvnOnkoNotify_setDT,
				MO.OnkoDiag_mid,	--  морфологический тип опухоли
				case when EON.EvnOnkoNotify_IsDiagConfCito =2 then 'Да'
					when EON.EvnOnkoNotify_IsDiagConfCito = 1 then 'Нет'
					else ''
				end as EvnOnkoNotify_IsDiagConfCito,	-- метод подтверждения диагноза: Цитологический (Да – 2, Нет – 1)
				case when EON.EvnOnkoNotify_IsDiagConfClinic =2 then 'Да'
					when EON.EvnOnkoNotify_IsDiagConfClinic = 1 then 'Нет'
					else ''
				end as EvnOnkoNotify_IsDiagConfClinic,	--  метод подтверждения диагноза: Только клинический (Да – 2, Нет – 1)
				case when EON.EvnOnkoNotify_IsDiagConfExplo =2 then 'Да'
					when EON.EvnOnkoNotify_IsDiagConfExplo = 1 then 'Нет'
					else ''
				end as EvnOnkoNotify_IsDiagConfExplo,	--  метод подтверждения диагноза: Эксплоротивная операция (Да – 2, Нет – 1)
				case when EON.EvnOnkoNotify_IsDiagConfLab =2 then 'Да'
					when EON.EvnOnkoNotify_IsDiagConfLab = 1 then 'Нет'
					else ''
				end as EvnOnkoNotify_IsDiagConfLab,	-- метод подтверждения диагноза: Лабораторно-инструментальный (Да – 2, Нет – 1)
				case when EON.EvnOnkoNotify_IsDiagConfMorfo =2 then 'Да'
					when EON.EvnOnkoNotify_IsDiagConfMorfo = 1 then 'Нет'
					else ''
				end as EvnOnkoNotify_IsDiagConfMorfo,	-- метод подтверждения диагноза: Морфологический (Да – 2, Нет – 1)
				case when EON.EvnOnkoNotify_IsDiagConfUnknown =2 then 'Да'
					when EON.EvnOnkoNotify_IsDiagConfUnknown = 1 then 'Нет'
					else ''
				end as EvnOnkoNotify_IsDiagConfUnknown,	-- метод подтверждения диагноза: Неизвестен (Да – 2, Нет – 1)
				MO.OnkoT_id as T,
				MO.OnkoN_id as N,
				MO.OnkoM_id as M,
				convert(varchar(10), MO.MorbusOnko_setDiagDT, 104) as MorbusOnko_setDiagDT,	-- Дата установления диагноза
				MO.TumorStage_id,	-- Стадия опухолевого процесса (справочник dbo.TumorStage);
				case when EON.EvnOnkoNotify_IsTumorDepoBones =2 then 'Да'
					when EON.EvnOnkoNotify_IsTumorDepoBones = 1 then 'Нет'
					else ''
				end as EvnOnkoNotify_IsTumorDepoBones,	-- локализация отдаленных метастазов: Кости (Да – 2, Нет – 1)
				case when EON.EvnOnkoNotify_IsTumorDepoBrain =2 then 'Да'
					when EON.EvnOnkoNotify_IsTumorDepoBrain = 1 then 'Нет'
					else ''
				end as EvnOnkoNotify_IsTumorDepoBrain,	-- локализация отдаленных метастазов: Головной мозг (Да – 2, Нет – 1)
				case when EON.EvnOnkoNotify_IsTumorDepoKidney =2 then 'Да'
					when EON.EvnOnkoNotify_IsTumorDepoKidney = 1 then 'Нет'
					else ''
				end as EvnOnkoNotify_IsTumorDepoKidney,	-- локализация отдаленных метастазов: Почки (Да – 2, Нет – 1)
				case when EON.EvnOnkoNotify_IsTumorDepoLiver =2 then 'Да'
					when EON.EvnOnkoNotify_IsTumorDepoLiver = 1 then 'Нет'
					else ''
				end as EvnOnkoNotify_IsTumorDepoLiver,	-- локализация отдаленных метастазов: Печень (Да – 2, Нет – 1)
				case when EON.EvnOnkoNotify_IsTumorDepoLungs =2 then 'Да'
					when EON.EvnOnkoNotify_IsTumorDepoLungs = 1 then 'Нет'
					else ''
				end as EvnOnkoNotify_IsTumorDepoLungs,	-- локализация отдаленных метастазов: Легкие и/или плевра (Да – 2, Нет – 1)
				case when EON.EvnOnkoNotify_IsTumorDepoLympha =2 then 'Да'
					when EON.EvnOnkoNotify_IsTumorDepoLympha = 1 then 'Нет'
					else ''
				end as EvnOnkoNotify_IsTumorDepoLympha,	-- локализация отдаленных метастазов: Отдаленные лимфатические узлы (Да – 2, Нет – 1)
				case when EON.EvnOnkoNotify_IsTumorDepoMarrow =2 then 'Да'
					when EON.EvnOnkoNotify_IsTumorDepoMarrow = 1 then 'Нет'
					else ''
				end as EvnOnkoNotify_IsTumorDepoMarrow,	-- локализация отдаленных метастазов: Костный мозг (Да – 2, Нет – 1)
				case when EON.EvnOnkoNotify_IsTumorDepoMulti =2 then 'Да'
					when EON.EvnOnkoNotify_IsTumorDepoMulti = 1 then 'Нет'
					else ''
				end as EvnOnkoNotify_IsTumorDepoMulti,	-- локализация отдаленных метастазов: Множественные (Да – 2, Нет – 1)
				case when EON.EvnOnkoNotify_IsTumorDepoOther =2 then 'Да'
					when EON.EvnOnkoNotify_IsTumorDepoOther = 1 then 'Нет'
					else ''
				end as EvnOnkoNotify_IsTumorDepoOther,	-- локализация отдаленных метастазов: Другие органы (Да – 2, Нет – 1)
				case when EON.EvnOnkoNotify_IsTumorDepoOvary =2 then 'Да'
					when EON.EvnOnkoNotify_IsTumorDepoOvary = 1 then 'Нет'
					else ''
				end as EvnOnkoNotify_IsTumorDepoOvary,	-- локализация отдаленных метастазов: Яичники (Да – 2, Нет – 1)
				case when EON.EvnOnkoNotify_IsTumorDepoPerito =2 then 'Да'
					when EON.EvnOnkoNotify_IsTumorDepoPerito = 1 then 'Нет'
					else ''
				end as EvnOnkoNotify_IsTumorDepoPerito,	-- локализация отдаленных метастазов: Брюшина (Да – 2, Нет – 1)
				case when EON.EvnOnkoNotify_IsTumorDepoSkin =2 then 'Да'
					when EON.EvnOnkoNotify_IsTumorDepoSkin = 1 then 'Нет'
					else ''
				end as EvnOnkoNotify_IsTumorDepoSkin,	--  локализация отдаленных метастазов: Кожа (Да – 2, Нет – 1)
				case when EON.EvnOnkoNotify_IsTumorDepoUnknown =2 then 'Да'
					when EON.EvnOnkoNotify_IsTumorDepoUnknown = 1 then 'Нет'
					else ''
				end as EvnOnkoNotify_IsTumorDepoUnknown,	-- локализация отдаленных метастазов: Неизвестна (Да – 2, Нет – 1)
				--параметры при отказе включения в регистр:
				EON.Lpu_niid,	-- Лпу, не включившее в регистр
				EON.MedPersonal_niid,	-- Врач, не включивший в регистр
				EON.PersonRegisterFailIncludeCause_id,	-- Причина невключения в регистр  (справочник dbo.PersonRegisterFailIncludeCause)
				EON.EvnOnkoNotify_Comment
			from
				v_EvnOnkoNotify EON with (nolock)
				inner join v_MorbusOnko MO with (nolock) on MO.Morbus_id = EON.Morbus_id
				--inner join v_MorbusOnkoPerson MOP with (nolock) on MOP.Person_id = EON.Person_id
				--inner join v_Morbus Morbus with (nolock) on Morbus.Morbus_id = MO.Morbus_id
				--inner join v_MorbusOnkoBase MOB with (nolock) on MOB.MorbusBase_id = Morbus.MorbusBase_id
				--left join EvnOnkoNotifyNeglected EONN with(nolock) on EONN.EvnOnkoNotify_id = EON.EvnOnkoNotify_id
			where 1=1
				{$where}
		";
		$r = $this->db->query($q, $params);
		if (is_object($r)) {
			$r = $r->result('array');
			if (isset($r[0])) {
				return $r;
			} else {
				return 0;
			}
		} else {
			return 0;
		}
	}
	
	/**
	 * Создание извещения об онкобольном. Метод для API
	 * @param type $data
	 */
	function saveEvnOnkoNotifyAPI($data){
		$query = 'SELECT PersonEvn_id, Person_id FROM v_Evn WHERE Evn_id = :Evn_pid';
		$res = $this->getFirstRowFromQuery($query, $data);
		
		if(empty($res['PersonEvn_id']) || empty($res['Person_id'])){
			return array('Error_Msg' => 'случай не найден');
		}
		$data['Person_id'] = $res['Person_id'];
		$data['PersonEvn_id'] = $res['PersonEvn_id'];
		$data['EvnOnkoNotify_pid'] = $data['Evn_pid'];
		$arrayParmsOneTwo = array(
			'EvnOnkoNotify_IsDiagConfCito',
			'EvnOnkoNotify_IsDiagConfClinic',
			'EvnOnkoNotify_IsDiagConfExplo',
			'EvnOnkoNotify_IsDiagConfLab',
			'EvnOnkoNotify_IsDiagConfMorfo',
			'EvnOnkoNotify_IsDiagConfUnknown',
			'EvnOnkoNotify_IsTumorDepoBones',
			'EvnOnkoNotify_IsTumorDepoBrain',
			'EvnOnkoNotify_IsTumorDepoKidney',
			'EvnOnkoNotify_IsTumorDepoLiver',
			'EvnOnkoNotify_IsTumorDepoLungs',
			'EvnOnkoNotify_IsTumorDepoLympha',
			'EvnOnkoNotify_IsTumorDepoMarrow',
			'EvnOnkoNotify_IsTumorDepoMulti',
			'EvnOnkoNotify_IsTumorDepoOther',
			'EvnOnkoNotify_IsTumorDepoOvary',
			'EvnOnkoNotify_IsTumorDepoPerito',
			'EvnOnkoNotify_IsTumorDepoSkin',
			'EvnOnkoNotify_IsTumorDepoUnknown'
		);
		$data['paramsForAPI'] = array();
		foreach ($arrayParmsOneTwo as $value) {
			if(!empty($data[$value]) && !in_array($data[$value], array(1,2))){
				return (array(
					'error_code' => 1,
					'Error_Msg' => 'Пареметр '.$value.' может иметь только занчение 1 или 2'
				));
			}
			if(!empty($data[$value])) {
				$data['paramsForAPI'][$value] = $data[$value];
			}
		}
		if(!empty($data['OnkoT_id'])) $data['paramsForAPI']['OnkoT_id'] = $data['OnkoT_id'];
		if(!empty($data['OnkoN_id'])) $data['paramsForAPI']['OnkoN_id'] = $data['OnkoN_id'];
		if(!empty($data['OnkoM_id'])) $data['paramsForAPI']['OnkoM_id'] = $data['OnkoM_id'];
		
		$result = $this->doSave($data);
		return $result;
	}
	
	/**
	 * Изменение извещения об онкобольном. Метод для API
	 * @param type $data
	 */
	function updateEvnOnkoNotifyAPI($data){
		//проверка существования извещения
		$query = 'SELECT * FROM v_EvnOnkoNotify WHERE EvnOnkoNotify_id = :EvnOnkoNotify_id';
		$res = $this->getFirstRowFromQuery($query, $data);
		if(empty($res['EvnOnkoNotify_id'])){
			return array(array('Error_Msg' => 'Извещение по данному заболеванию не найдено'));
		}
		
			
		$arrayParmsOneTwo = array(
			'EvnOnkoNotify_IsDiagConfCito',
			'EvnOnkoNotify_IsDiagConfClinic',
			'EvnOnkoNotify_IsDiagConfExplo',
			'EvnOnkoNotify_IsDiagConfLab',
			'EvnOnkoNotify_IsDiagConfMorfo',
			'EvnOnkoNotify_IsDiagConfUnknown',
			'EvnOnkoNotify_IsTumorDepoBones',
			'EvnOnkoNotify_IsTumorDepoBrain',
			'EvnOnkoNotify_IsTumorDepoKidney',
			'EvnOnkoNotify_IsTumorDepoLiver',
			'EvnOnkoNotify_IsTumorDepoLungs',
			'EvnOnkoNotify_IsTumorDepoLympha',
			'EvnOnkoNotify_IsTumorDepoMarrow',
			'EvnOnkoNotify_IsTumorDepoMulti',
			'EvnOnkoNotify_IsTumorDepoOther',
			'EvnOnkoNotify_IsTumorDepoOvary',
			'EvnOnkoNotify_IsTumorDepoPerito',
			'EvnOnkoNotify_IsTumorDepoSkin',
			'EvnOnkoNotify_IsTumorDepoUnknown'
		);
		
		foreach ($arrayParmsOneTwo as $value) {
			if(!empty($data[$value]) && !in_array($data[$value], array(1,2))){
				return (array(array(
					'error_code' => 1,
					'Error_Msg' => 'Пареметр '.$value.' может иметь только занчение 1 или 2'
				)));
			}
		}
		
		$query = '
			declare
				@Res bigint,
				@ErrCode bigint,
				@ErrMsg varchar(4000);
			set @Res = :EvnOnkoNotify_id;
			exec p_EvnOnkoNotify_upd
				@EvnOnkoNotify_id = @Res output,
				@EvnOnkoNotify_pid = :EvnOnkoNotify_pid,
				@EvnOnkoNotify_rid = :EvnOnkoNotify_rid,
				@Lpu_id = :Lpu_id,
				@Server_id = :Server_id,
				@PersonEvn_id = :PersonEvn_id,
				@EvnOnkoNotify_setDT = :EvnOnkoNotify_setDT,
				@EvnOnkoNotify_disDT = :EvnOnkoNotify_disDT,
				@EvnOnkoNotify_didDT = :EvnOnkoNotify_didDT,
				@EvnOnkoNotify_insDT = :EvnOnkoNotify_insDT,
				@EvnOnkoNotify_updDT = :EvnOnkoNotify_updDT,
				@EvnOnkoNotify_Index = :EvnOnkoNotify_Index,
				@EvnOnkoNotify_Count = :EvnOnkoNotify_Count,
				@Morbus_id = :Morbus_id,
				@EvnOnkoNotify_IsSigned = :EvnOnkoNotify_IsSigned,
				@pmUser_signID = :pmUser_signID,
				@EvnOnkoNotify_signDT = :EvnOnkoNotify_signDT,
				@EvnStatus_id = :EvnStatus_id,
				@EvnOnkoNotify_statusDate = :EvnOnkoNotify_statusDate,
				@MorbusType_id = :MorbusType_id,
				@MedPersonal_id = :MedPersonal_id,
				@EvnOnkoNotify_niDate = :EvnOnkoNotify_niDate,
				@MedPersonal_niid = :MedPersonal_niid,
				@Lpu_niid = :Lpu_niid,
				@PersonRegisterFailIncludeCause_id = :PersonRegisterFailIncludeCause_id,
				@PersonDisp_id = :PersonDisp_id,
				@NotifyStatus_id = :NotifyStatus_id,
				@EvnOnkoNotify_Comment = :EvnOnkoNotify_Comment,
				@EvnOnkoNotify_IsAuto = :EvnOnkoNotify_IsAuto,
				@Lpu_sid = :Lpu_sid,
				@Ethnos_id = :Ethnos_id,
				@OnkoOccupationClass_id = :OnkoOccupationClass_id,
				@EvnOnkoNotify_setFirstDT = :EvnOnkoNotify_setFirstDT,
				@EvnOnkoNotify_setDiagDT = :EvnOnkoNotify_setDiagDT,
				@Diag_id = :Diag_id,
				@EvnOnkoNotify_LocalTrumor = :EvnOnkoNotify_LocalTrumor,
				@Diag_mid = :Diag_mid,
				@OnkoT_id = :OnkoT_id,
				@OnkoN_id = :OnkoN_id,
				@OnkoM_id = :OnkoM_id,
				@TumorStage_id = :TumorStage_id,
				@EvnOnkoNotify_IsTumorDepoUnknown = :EvnOnkoNotify_IsTumorDepoUnknown,
				@EvnOnkoNotify_IsTumorDepoLympha = :EvnOnkoNotify_IsTumorDepoLympha,
				@EvnOnkoNotify_IsTumorDepoBones = :EvnOnkoNotify_IsTumorDepoBones,
				@EvnOnkoNotify_IsTumorDepoLiver = :EvnOnkoNotify_IsTumorDepoLiver,
				@EvnOnkoNotify_IsTumorDepoLungs = :EvnOnkoNotify_IsTumorDepoLungs,
				@EvnOnkoNotify_IsTumorDepoBrain = :EvnOnkoNotify_IsTumorDepoBrain,
				@EvnOnkoNotify_IsTumorDepoSkin = :EvnOnkoNotify_IsTumorDepoSkin,
				@EvnOnkoNotify_IsTumorDepoKidney = :EvnOnkoNotify_IsTumorDepoKidney,
				@EvnOnkoNotify_IsTumorDepoOvary = :EvnOnkoNotify_IsTumorDepoOvary,
				@EvnOnkoNotify_IsTumorDepoPerito = :EvnOnkoNotify_IsTumorDepoPerito,
				@EvnOnkoNotify_IsTumorDepoMarrow = :EvnOnkoNotify_IsTumorDepoMarrow,
				@EvnOnkoNotify_IsTumorDepoOther = :EvnOnkoNotify_IsTumorDepoOther,
				@EvnOnkoNotify_IsTumorDepoMulti = :EvnOnkoNotify_IsTumorDepoMulti,
				@EvnOnkoNotify_IsDiagConfUnknown = :EvnOnkoNotify_IsDiagConfUnknown,
				@EvnOnkoNotify_IsDiagConfMorfo = :EvnOnkoNotify_IsDiagConfMorfo,
				@EvnOnkoNotify_IsDiagConfCito = :EvnOnkoNotify_IsDiagConfCito,
				@EvnOnkoNotify_IsDiagConfExplo = :EvnOnkoNotify_IsDiagConfExplo,
				@EvnOnkoNotify_IsDiagConfLab = :EvnOnkoNotify_IsDiagConfLab,
				@EvnOnkoNotify_IsDiagConfClinic = :EvnOnkoNotify_IsDiagConfClinic,
				@TumorCircumIdentType_id = :TumorCircumIdentType_id,
				@Lpu_mid = :Lpu_mid,
				@NotifyFillPlace_id = :NotifyFillPlace_id,
				@NotifyDirectType_id = :NotifyDirectType_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMsg output;
			select @Res as EvnOnkoNotify_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg;
		';
		
		$queryParams = array(
			'EvnOnkoNotify_id' => $data['EvnOnkoNotify_id'],
			'EvnOnkoNotify_pid' => (!empty($data['EvnOnkoNotify_pid'])) ? $data['EvnOnkoNotify_pid'] : $res['EvnOnkoNotify_pid'],
			'EvnOnkoNotify_rid' => (!empty($data['EvnOnkoNotify_rid'])) ? $data['EvnOnkoNotify_rid'] : $res['EvnOnkoNotify_rid'],
			'Lpu_id' => $res['Lpu_id'],
			'Server_id' => $res['Server_id'],
			'PersonEvn_id' => $res['PersonEvn_id'],
			'EvnOnkoNotify_setDT' => (!empty($data['EvnOnkoNotify_setDT'])) ? $data['EvnOnkoNotify_setDT'] : $res['EvnOnkoNotify_setDT'],
			'EvnOnkoNotify_disDT' => (!empty($data['EvnOnkoNotify_disDT'])) ? $data['EvnOnkoNotify_disDT'] : $res['EvnOnkoNotify_disDT'],
			'EvnOnkoNotify_didDT' => (!empty($data['EvnOnkoNotify_didDT'])) ? $data['EvnOnkoNotify_didDT'] : $res['EvnOnkoNotify_didDT'],
			'EvnOnkoNotify_insDT' => (!empty($data['EvnOnkoNotify_insDT'])) ? $data['EvnOnkoNotify_insDT'] : $res['EvnOnkoNotify_insDT'],
			'EvnOnkoNotify_updDT' => (!empty($data['EvnOnkoNotify_updDT'])) ? $data['EvnOnkoNotify_updDT'] : $res['EvnOnkoNotify_updDT'],
			'EvnOnkoNotify_Index' => (!empty($data['EvnOnkoNotify_Index'])) ? $data['EvnOnkoNotify_Index'] : $res['EvnOnkoNotify_Index'],
			'EvnOnkoNotify_Count' => (!empty($data['EvnOnkoNotify_Count'])) ? $data['EvnOnkoNotify_Count'] : $res['EvnOnkoNotify_Count'],
			'Morbus_id' => $res['Morbus_id'],
			'EvnOnkoNotify_IsSigned' => (!empty($data['EvnOnkoNotify_IsSigned'])) ? $data['EvnOnkoNotify_IsSigned'] : $res['EvnOnkoNotify_IsSigned'],
			'pmUser_signID' => $res['pmUser_signID'],
			'EvnOnkoNotify_signDT' => (!empty($data['EvnOnkoNotify_signDT'])) ? $data['EvnOnkoNotify_signDT'] : $res['EvnOnkoNotify_signDT'],
			'EvnStatus_id' => $res['EvnStatus_id'],
			'EvnOnkoNotify_statusDate' => (!empty($data['EvnOnkoNotify_statusDate'])) ? $data['EvnOnkoNotify_statusDate'] : $res['EvnOnkoNotify_statusDate'],
			'MorbusType_id' => $res['MorbusType_id'],
			'MedPersonal_id' => (!empty($data['MedPersonal_id'])) ? $data['MedPersonal_id'] : $res['MedPersonal_id'],
			'EvnOnkoNotify_niDate' => (!empty($data['EvnOnkoNotify_niDate'])) ? $data['EvnOnkoNotify_niDate'] : $res['EvnOnkoNotify_niDate'],
			'MedPersonal_niid' => (!empty($data['MedPersonal_niid'])) ? $data['MedPersonal_niid'] : $res['MedPersonal_niid'],
			'Lpu_niid' => (!empty($data['Lpu_niid'])) ? $data['Lpu_niid'] : $res['Lpu_niid'],
			'PersonRegisterFailIncludeCause_id' => (!empty($data['PersonRegisterFailIncludeCause_id'])) ? $data['PersonRegisterFailIncludeCause_id'] : $res['PersonRegisterFailIncludeCause_id'],
			'PersonDisp_id' => $res['PersonDisp_id'],
			'NotifyStatus_id' => $res['NotifyStatus_id'],
			'EvnOnkoNotify_Comment' => (!empty($data['EvnOnkoNotify_Comment'])) ? $data['EvnOnkoNotify_Comment'] : $res['EvnOnkoNotify_Comment'],
			'EvnOnkoNotify_IsAuto' => (!empty($data['EvnOnkoNotify_IsAuto'])) ? $data['EvnOnkoNotify_IsAuto'] : $res['EvnOnkoNotify_IsAuto'],
			'Lpu_sid' => (!empty($data['Lpu_sid'])) ? $data['Lpu_sid'] : $res['Lpu_sid'],
			'Ethnos_id' => (!empty($data['Ethnos_id'])) ? $data['Ethnos_id'] : $res['Ethnos_id'],
			'OnkoOccupationClass_id' => $res['OnkoOccupationClass_id'],
			'EvnOnkoNotify_setFirstDT' => (!empty($data['EvnOnkoNotify_setFirstDT'])) ? $data['EvnOnkoNotify_setFirstDT'] : $res['EvnOnkoNotify_setFirstDT'],
			'EvnOnkoNotify_setDiagDT' => (!empty($data['EvnOnkoNotify_setDiagDT'])) ? $data['EvnOnkoNotify_setDiagDT'] : $res['EvnOnkoNotify_setDiagDT'],
			'Diag_id' => $res['Diag_id'],
			'EvnOnkoNotify_LocalTrumor' => (!empty($data['EvnOnkoNotify_LocalTrumor'])) ? $data['EvnOnkoNotify_LocalTrumor'] : $res['EvnOnkoNotify_LocalTrumor'],
			'Diag_mid' => (!empty($data['Diag_mid'])) ? $data['Diag_mid'] : $res['Diag_mid'],
			'OnkoT_id' => (!empty($data['OnkoT_id'])) ? $data['OnkoT_id'] : $res['OnkoT_id'],
			'OnkoN_id' => (!empty($data['OnkoN_id'])) ? $data['OnkoN_id'] : $res['OnkoN_id'],
			'OnkoM_id' => (!empty($data['OnkoM_id'])) ? $data['OnkoM_id'] : $res['OnkoM_id'],
			'TumorStage_id' => (!empty($data['TumorStage_id'])) ? $data['TumorStage_id'] : $res['TumorStage_id'],
			'EvnOnkoNotify_IsTumorDepoUnknown' => (!empty($data['EvnOnkoNotify_IsTumorDepoUnknown'])) ? $data['EvnOnkoNotify_IsTumorDepoUnknown'] : $res['EvnOnkoNotify_IsTumorDepoUnknown'],
			'EvnOnkoNotify_IsTumorDepoLympha' => (!empty($data['EvnOnkoNotify_IsTumorDepoLympha'])) ? $data['EvnOnkoNotify_IsTumorDepoLympha'] : $res['EvnOnkoNotify_IsTumorDepoLympha'],
			'EvnOnkoNotify_IsTumorDepoBones' => (!empty($data['EvnOnkoNotify_IsTumorDepoBones'])) ? $data['EvnOnkoNotify_IsTumorDepoBones'] : $res['EvnOnkoNotify_IsTumorDepoBones'],
			'EvnOnkoNotify_IsTumorDepoLiver' => (!empty($data['EvnOnkoNotify_IsTumorDepoLiver'])) ? $data['EvnOnkoNotify_IsTumorDepoLiver'] : $res['EvnOnkoNotify_IsTumorDepoLiver'],
			'EvnOnkoNotify_IsTumorDepoLungs' => (!empty($data['EvnOnkoNotify_IsTumorDepoLungs'])) ? $data['EvnOnkoNotify_IsTumorDepoLungs'] : $res['EvnOnkoNotify_IsTumorDepoLungs'],
			'EvnOnkoNotify_IsTumorDepoBrain' => (!empty($data['EvnOnkoNotify_IsTumorDepoBrain'])) ? $data['EvnOnkoNotify_IsTumorDepoBrain'] : $res['EvnOnkoNotify_IsTumorDepoBrain'],
			'EvnOnkoNotify_IsTumorDepoSkin' => (!empty($data['EvnOnkoNotify_IsTumorDepoSkin'])) ? $data['EvnOnkoNotify_IsTumorDepoSkin'] : $res['EvnOnkoNotify_IsTumorDepoSkin'],
			'EvnOnkoNotify_IsTumorDepoKidney' => (!empty($data['EvnOnkoNotify_IsTumorDepoKidney'])) ? $data['EvnOnkoNotify_IsTumorDepoKidney'] : $res['EvnOnkoNotify_IsTumorDepoKidney'],
			'EvnOnkoNotify_IsTumorDepoOvary' => (!empty($data['EvnOnkoNotify_IsTumorDepoOvary'])) ? $data['EvnOnkoNotify_IsTumorDepoOvary'] : $res['EvnOnkoNotify_IsTumorDepoOvary'],
			'EvnOnkoNotify_IsTumorDepoPerito' => (!empty($data['EvnOnkoNotify_IsTumorDepoPerito'])) ? $data['EvnOnkoNotify_IsTumorDepoPerito'] : $res['EvnOnkoNotify_IsTumorDepoPerito'],
			'EvnOnkoNotify_IsTumorDepoMarrow' => (!empty($data['EvnOnkoNotify_IsTumorDepoMarrow'])) ? $data['EvnOnkoNotify_IsTumorDepoMarrow'] : $res['EvnOnkoNotify_IsTumorDepoMarrow'],
			'EvnOnkoNotify_IsTumorDepoOther' => (!empty($data['EvnOnkoNotify_IsTumorDepoOther'])) ? $data['EvnOnkoNotify_IsTumorDepoOther'] : $res['EvnOnkoNotify_IsTumorDepoOther'],
			'EvnOnkoNotify_IsTumorDepoMulti' => (!empty($data['EvnOnkoNotify_IsTumorDepoMulti'])) ? $data['EvnOnkoNotify_IsTumorDepoMulti'] : $res['EvnOnkoNotify_IsTumorDepoMulti'],
			'EvnOnkoNotify_IsDiagConfUnknown' => (!empty($data['EvnOnkoNotify_IsDiagConfUnknown'])) ? $data['EvnOnkoNotify_IsDiagConfUnknown'] : $res['EvnOnkoNotify_IsDiagConfUnknown'],
			'EvnOnkoNotify_IsDiagConfMorfo' => (!empty($data['EvnOnkoNotify_IsDiagConfMorfo'])) ? $data['EvnOnkoNotify_IsDiagConfMorfo'] : $res['EvnOnkoNotify_IsDiagConfMorfo'],
			'EvnOnkoNotify_IsDiagConfCito' => (!empty($data['EvnOnkoNotify_IsDiagConfCito'])) ? $data['EvnOnkoNotify_IsDiagConfCito'] : $res['EvnOnkoNotify_IsDiagConfCito'],
			'EvnOnkoNotify_IsDiagConfExplo' => (!empty($data['EvnOnkoNotify_IsDiagConfExplo'])) ? $data['EvnOnkoNotify_IsDiagConfExplo'] : $res['EvnOnkoNotify_IsDiagConfExplo'],
			'EvnOnkoNotify_IsDiagConfLab' => (!empty($data['EvnOnkoNotify_IsDiagConfLab'])) ? $data['EvnOnkoNotify_IsDiagConfLab'] : $res['EvnOnkoNotify_IsDiagConfLab'],
			'EvnOnkoNotify_IsDiagConfClinic' => (!empty($data['EvnOnkoNotify_IsDiagConfClinic'])) ? $data['EvnOnkoNotify_IsDiagConfClinic'] : $res['EvnOnkoNotify_IsDiagConfClinic'],
			'TumorCircumIdentType_id' => $res['TumorCircumIdentType_id'],
			'Lpu_mid' => (!empty($data['Lpu_mid'])) ? $data['Lpu_mid'] : $res['Lpu_mid'],
			'NotifyFillPlace_id' => $res['NotifyFillPlace_id'],
			'NotifyDirectType_id' => $res['NotifyDirectType_id'],
			'pmUser_id' => $_SESSION['pmuser_id']
		);
		// echo getDebugSQL($query, $queryParams); exit();
		$res = $this->db->query($query, $queryParams);

		if ( is_object($res) ) {
			return $response = $res->result('array');
		}
		else {
			return false;
		}
	}
}
