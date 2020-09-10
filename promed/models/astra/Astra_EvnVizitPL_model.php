<?php	defined('BASEPATH') or die ('No direct script access allowed');

require_once(APPPATH.'models/EvnVizitPL_model.php');

class Astra_EvnVizitPL_model extends EvnVizitPL_model {
	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * Проверка двойственности посещений пациентов
	 * @task https://redmine.swan.perm.ru/issues/70829
	 * @throws Exception
	 */
	protected function _controlDoubleVizit()
	{
		$double_vizit_control = $this->allOptions['polka']['double_vizit_control'];

		$isAllowControlDoubleVizit = (
			($this->scenario == self::SCENARIO_DO_SAVE || $this->scenario == self::SCENARIO_AUTO_CREATE)
			&& (
				$double_vizit_control == 3 // Запрет сохранения
				|| ($double_vizit_control == 2 && $this->_params['ignoreDayProfileDuplicateVizit'] == false) // Предупреждение
			)
		);

		if (
			$isAllowControlDoubleVizit
			&& in_array($this->evnClassId, array(11, 13))
			&& $this->payTypeSysNick == 'oms'
			&& isset($this->lpuSectionData['LpuSectionProfile_Code'])
			&& (!in_array($this->lpuSectionData['LpuSectionProfile_Code'], array('63','85','86','87','88','89','90','131','2','3','306','18','60','136','137')))
		) {
			$query = "
				select
					LS.LpuSection_Name,
					LS.LpuSectionProfile_Code,
					LS.LpuSectionProfile_Name,
					convert(varchar(10), EVPL.{$this->tableName()}_setDate, 104) as EvnVizitPL_setDate,
					PS.Person_Surname,
					PS.Person_Firname,
					PS.Person_Secname,
					VT.VizitType_SysNick
				from {$this->viewName()} EVPL with (nolock)
					inner join v_LpuSection LS with (nolock) on LS.LpuSection_id = EVPL.LpuSection_id
					inner join v_PayType PT with (nolock) on EVPL.PayType_id = PT.PayType_id
						and PT.PayType_SysNick = 'oms'
					inner join v_PersonState PS with (nolock) on PS.Person_id = EVPL.Person_id
					inner join v_VizitType VT with (nolock) on VT.VizitType_id = EVPL.VizitType_id
				where EVPL.Person_id = :Person_id
					and EVPL.{$this->primaryKey()} <> ISNULL(:EvnVizitPL_id, 0)
					and EVPL.{$this->tableName()}_setDate = cast(:EvnVizitPL_setDate as datetime)
			";
			$result = $this->db->query($query, array(
				'EvnVizitPL_id' => $this->id,
				'EvnVizitPL_setDate' => $this->setDate,
				'Person_id' => $this->Person_id,
			));
			if ( !is_object($result) ) {
				throw new Exception('Ошибка при выполнении запроса к базе данных (контроль двойственности посещений пациентов)', 500);
			}
			$response = $result->result('array');
			if ( is_array($response) && count($response) > 0 ) {
				foreach ( $response as $vizitData ) {
					if (
						$vizitData['LpuSectionProfile_Code'] == $this->lpuSectionData['LpuSectionProfile_Code']
						|| (
							$vizitData['LpuSectionProfile_Code'] == '68'
							&& $this->lpuSectionData['LpuSectionProfile_Code'] == '316'
							&& !($vizitData['VizitType_SysNick'] == 'prof' && in_array($this->getVizitTypeSysNick(), array('desease', 'razpos')))
						)
						|| (
							$vizitData['LpuSectionProfile_Code'] == '316'
							&& $this->lpuSectionData['LpuSectionProfile_Code'] == '68'
							&& !(in_array($vizitData['VizitType_SysNick'], array('desease', 'razpos')) && $this->getVizitTypeSysNick() == 'prof')
						)
						|| (
							$vizitData['LpuSectionProfile_Code'] == '97'
							&& $this->lpuSectionData['LpuSectionProfile_Code'] == '315'
							&& !($vizitData['VizitType_SysNick'] == 'prof' && in_array($this->getVizitTypeSysNick(), array('desease', 'razpos')))
						)
						|| (
							$vizitData['LpuSectionProfile_Code'] == '315'
							&& $this->lpuSectionData['LpuSectionProfile_Code'] == '97'
							&& !(in_array($vizitData['VizitType_SysNick'], array('desease', 'razpos')) && $this->getVizitTypeSysNick() == 'prof')
						)
					) {
						if ( $double_vizit_control == 2 ) {
							$this->_saveResponse['ignoreParam'] = "ignoreDayProfileDuplicateVizit";
							$this->_saveResponse['Alert_Msg'] = "В системе уже имеется посещение пациента {$vizitData['Person_Surname']} {$vizitData['Person_Firname']} {$vizitData['Person_Secname']}, {$vizitData['EvnVizitPL_setDate']}, профиль '{$vizitData['LpuSectionProfile_Name']}', отделение '{$vizitData['LpuSection_Name']}'. Продолжить сохранение?";
							throw new Exception('YesNo');
						}
						else if ( $double_vizit_control == 3 ) {
							throw new Exception("Нельзя сохранить посещение. В системе уже имеется посещение пациента {$vizitData['Person_Surname']} {$vizitData['Person_Firname']} {$vizitData['Person_Secname']}, {$vizitData['EvnVizitPL_setDate']}, профиль '{$vizitData['LpuSectionProfile_Name']}', отделение '{$vizitData['LpuSection_Name']}'");
						}
					}
				}
			}
		}
	}
}