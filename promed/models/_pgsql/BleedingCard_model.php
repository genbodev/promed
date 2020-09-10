<?php
/**
 * @property BleedingCard_model $BleedingCard_model
 */
class BleedingCard_model extends swPGModel {
	protected $_objects = [
		'BleedingCardCondition',
		'BleedingCardDrug',
		'BleedingCardSolution',
	];

	/**
	 * Конструктор
	 */
	function __construct() {
		parent ::__construct();
	}

	/**
	 * @param $data
	 * @return bool
	 */
	public function loadBleedingCardEditForm($data = []) {
		return $this->getFirstRowFromQuery("
			SELECT	BC.BleedingCard_id as \"BleedingCard_id\",
				BC.EvnSection_id as \"EvnSection_id\",
				to_char(BC.BleedingCard_setDT, 'dd.mm.yyyy') as \"BleedingCard_setDT\"
			FROM v_BleedingCard BC
			WHERE BC.BleedingCard_id = :BleedingCard_id
			LIMIT 1
		", [
			'BleedingCard_id' => $data['BleedingCard_id']
		]);
	}

	/**
	 *
	 * @param type $data
	 * @return type
	 */
	public function loadBleedingCardConditionGrid($data = []) {
		return $this->queryResult("
			SELECT
				BCC.BleedingCardCondition_id as \"BleedingCardCondition_id\",
				to_char(BCC.BleedingCardCondition_setDT, 'dd.mm.yyyy hh24:mi') as \"BleedingCardCondition_setDT\",
				to_char(BCC.BleedingCardCondition_setDT, 'dd.mm.yyyy') as \"BleedingCardCondition_setDate\",
				to_char(BCC.BleedingCardCondition_setDT, 'hh24:mi') as \"BleedingCardCondition_setTime\",
				BCC.BleedingCardCondition_Temperature as \"BleedingCardCondition_Temperature\",
				BCC.BleedingCardCondition_SistolPress as \"BleedingCardCondition_SistolPress\",
				BCC.BleedingCardCondition_DiastolPress as \"BleedingCardCondition_DiastolPress\",
				cast(BCC.BleedingCardCondition_SistolPress as varchar(3)) || ' / '
					|| cast(BCC.BleedingCardCondition_DiastolPress as varchar(3)) as \"BleedingCardCondition_Pressure\",
				BCC.BleedingCardCondition_Pulse as \"BleedingCardCondition_Pulse\",
				BCC.BleedingCardCondition_BreathFrequency as \"BleedingCardCondition_BreathFrequency\",
				D.Diuresis_id as \"Diuresis_id\",
				D.Diuresis_Name as \"Diuresis_Name\",
				CNS.CentralNervousSystem_id as \"CentralNervousSystem_id\",
				CNS.CentralNervousSystem_Name as \"CentralNervousSystem_Name\",
				PO.PulseOximetry_id as \"PulseOximetry_id\",
				PO.PulseOximetry_Name as \"PulseOximetry_Name\",
				to_char(BCC.BleedingCardCondition_CatheterTime, 'h24:mi') as \"BleedingCardCondition_CatheterTime\",
				BCC.BleedingCardCondition_TotalScore as \"BleedingCardCondition_TotalScore\",
				1 as \"RecordStatus_Code\"
			FROM v_BleedingCardCondition BCC
				INNER JOIN v_Diuresis D ON D.Diuresis_id = BCC.Diuresis_id
				INNER JOIN v_CentralNervousSystem CNS ON CNS.CentralNervousSystem_id = BCC.CentralNervousSystem_id
				INNER JOIN v_PulseOximetry PO ON PO.PulseOximetry_id = BCC.PulseOximetry_id
			WHERE BCC.BleedingCard_id = :BleedingCard_id
		", [
			'BleedingCard_id' => $data['BleedingCard_id']
		]);
	}

	/**
	 * @param type $data
	 * @return type
	 */
	public function loadBleedingCardSolutionGrid($data = []) {
		return $this->queryResult("
			SELECT
				BCS.BleedingCardSolution_id as \"BleedingCardSolution_id\",
				to_char(BCS.BleedingCardSolution_setDT, 'dd.mm.yyyy hh24:mi') as \"BleedingCardSolution_setDT\",
				to_char(BCS.BleedingCardSolution_setDT, 'dd.mm.yyyy') as \"BleedingCardSolution_setDate\",
				to_char(BCS.BleedingCardSolution_setDT, 'hh24:mi') as \"BleedingCardSolution_setTime\",
				BCS.BleedingCardSolution_Volume as \"BleedingCardSolution_Volume\",
				ST.SolutionType_id as \"SolutionType_id\",
				ST.SolutionType_Name as \"SolutionType_Name\",
				1 as \"RecordStatus_Code\"
			FROM v_BleedingCardSolution BCS
				LEFT JOIN v_SolutionType ST ON ST.SolutionType_id = BCS.SolutionType_id
			WHERE BCS.BleedingCard_id = :BleedingCard_id
		", [
			'BleedingCard_id' => $data['BleedingCard_id']
		]);
	}

	/**
	 * @param type $data
	 * @return type
	 */
	public function loadBleedingCardDrugGrid($data = []) {
		return $this->queryResult("
			SELECT
				BCD.BleedingCardDrug_id as \"BleedingCardDrug_id\",
				to_char(BCD.BleedingCardDrug_setDT, 'dd.mm.yyyy hh24:mi') as \"BleedingCardDrug_setDT\",
				to_char(BCD.BleedingCardDrug_setDT, 'dd.mm.yyyy') as \"BleedingCardDrug_setDate\",
				to_char(BCD.BleedingCardDrug_setDT, 'hh24:mi') as \"BleedingCardDrug_setTime\",
				DCM.DrugComplexMnn_id as \"DrugComplexMnn_id\",
				DCM.DrugComplexMnn_RusName as \"DrugComplexMnn_Name\",
				PIT.PrescriptionIntroType_id as \"PrescriptionIntroType_id\",
				PIT.PrescriptionIntroType_Name as \"PrescriptionIntroType_Name\",
				BCD.BleedingCardDrug_Dosage as \"BleedingCardDrug_Dosage\",
				GU.GoodsUnit_id as \"GoodsUnit_id\",
				BCD.BleedingCardDrug_TotalScore as \"BleedingCardDrug_TotalScore\",
				1 as \"RecordStatus_Code\"
			FROM v_BleedingCardDrug BCD
				INNER JOIN rls.DrugComplexMnn DCM ON DCM.DrugComplexMnn_id = BCD.DrugComplexMnn_id
				LEFT JOIN v_PrescriptionIntroType PIT ON PIT.PrescriptionIntroType_id = BCD.PrescriptionIntroType_id
				LEFT JOIN v_GoodsUnit GU ON GU.GoodsUnit_id = BCD.GoodsUnit_id
			WHERE BCD.BleedingCard_id = :BleedingCard_id
		", [
			'BleedingCard_id' => $data['BleedingCard_id']
		]);
	}

	/**
	 * Удаление карты
	 */
	public function deleteBleedingCard($data = []) {
		$response = [[ 'Error_Msg' => '' ]];

		try {
			$this->beginTransaction();

			foreach ( $this->_objects as $object ) {
				$objectList = $this->queryResult("
					select {$object}_id as \"{$object}_id\" from v_{$object} where BleedingCard_id = :BleedingCard_id
				", [
					'BleedingCard_id' => $data['BleedingCard_id'],
				]);

				if ( !is_array($objectList) ) {
					continue;
				}

				foreach ( $objectList as $row ) {
					$queryResponse = $this->queryResult("
	                                        select 	Error_Code as \"Error_Code\",
	                                        	Error_Message as \"Error_Msg\"
						from	dbo.p_{$object}_del(
							{$object}_id := :{$object}_id )
					", [
						$object . '_id' => $row[$object . '_id'],
					]);

					if ( !is_array($queryResponse) ) {
						throw new Exception('Ошибка при удалении записи (' . $object . ')');
					}
					else if ( !empty($queryResponse[0]['Error_Msg']) ) {
						throw new Exception($queryResponse[0]['Error_Msg']);
					}
				}
			}

			$response = $this->queryResult("
	                        select 	Error_Code as \"Error_Code\",
	                                Error_Message as \"Error_Msg\"
				from	dbo.p_BleedingCard_del(
					BleedingCard_id := :BleedingCard_id )
			", [
				'BleedingCard_id' => $data['BleedingCard_id'],
			]);

			if ( $response === false ) {
				throw new Exception('Ошибка при выполнении запроса к БД');
			}
			else if ( !empty($response[0]['Error_Msg']) ) {
				throw new Exception($response[0]['Error_Msg']);
			}

			$this->commitTransaction();
		}
		catch ( Exception $e ) {
			$this->rollbackTransaction();
			$response[0]['Error_Msg'] = $e->getMessage();
		}

		return $response;
	}

	/**
	 * Загрузка списка карт
	 */
	public function loadBleedingCardGrid($data = []) {
		return $this->queryResult("
			select
				BC.BleedingCard_id as \"BleedingCard_id\",
				BC.EvnSection_id as \"EvnSection_id\",
				to_char(BC.BleedingCard_setDT, 'dd.mm.yyyy') as \"BleedingCard_setDT\"
			from v_BleedingCard BC
			where EvnSection_id = :EvnSection_id
		", [
			'EvnSection_id' => $data['EvnSection_id']
		]);
	}

	/**
	 * Сохранение
	 */
	public function saveBleedingCard($data = []) {
		$response = [[ 'Error_Msg' => '' ]];

		try {
			$this->beginTransaction();

			if( !isset($data['BleedingCard_id']) || $data['BleedingCard_id'] <= 0 )
			{
			  $setDT = $this->getFirstResultFromQuery( "select dbo.tzGetdate()" );
			}
			else
			{
			  $setDT = $this->getFirstResultFromQuery( "select BleedingCard_setDT from dbo.v_BleedingCard where BleedingCard_id = :BleedingCard_id", $data );
			}
			
			$response = $this->queryResult("
				select 	BleedingCard_id as \"BleedingCard_id\",
					Error_Code as \"Error_Code\",
					Error_Message as \"Error_Msg\"
				from	dbo.p_BleedingCard_" . (!empty($data['BleedingCard_id']) ? "upd" : "ins") . "(
					BleedingCard_id := :BleedingCard_id,
					EvnSection_id := :EvnSection_id,
					BleedingCard_setDT := :setDT,
					pmUser_id := :pmUser_id )

			", [
				'BleedingCard_id' => (!isset($data['BleedingCard_id']) || $data['BleedingCard_id'] <= 0 ? NULL : $data['BleedingCard_id']),
				'EvnSection_id' => $data['EvnSection_id'],
				'setDT' => $setDT,
				'pmUser_id' => $data['pmUser_id'],
			]);

			if ( $response === false ) {
				throw new Exception('Ошибка при выполнении запроса к БД');
			}
			else if ( !empty($response[0]['Error_Msg']) ) {
				throw new Exception($response[0]['Error_Msg']);
			}

			$BleedingCard_id = $response[0]['BleedingCard_id'];

			foreach ( $this->_objects as $object ) {
				if ( !isset($data[$object . 'Data']) || !is_array($data[$object . 'Data']) ) {
					continue;
				}

				foreach ( $data[$object . 'Data'] as $row ) {
					if ( $row['RecordStatus_Code'] == 1 || empty($row[$object . '_id']) ) {
						continue;
					}

					if ( $row[$object . '_id'] < 0 ) {
						$row[$object . '_id'] = null;
					}

					$row['BleedingCard_id'] = $BleedingCard_id;
					$row['pmUser_id'] = $data['pmUser_id'];

					switch ( $row['RecordStatus_Code'] ) {
						case 0:
						case 2:
							$saveMethod = 'save' . $object;
							$queryResponse = $this->$saveMethod($row);
							break;

						case 3:
							$queryResponse = $this->queryResult("
                                                                select	Error_Code as \"Error_Code\",
                                                                	Error_Message as \"Error_Msg\"
								from	dbo.p_{$object}_del(
									{$object}_id := :{$object}_id )
							", [
								$object . '_id' => $row[$object . '_id'],
							]);
							break;
					}

					if ( !is_array($queryResponse) ) {
						throw new Exception('Ошибка при ' . ($row['RecordStatus_Code'] == 3 ? 'удалении' : 'сохранении') . ' записи (' . $object . ')');
					}
					else if ( !empty($queryResponse[0]['Error_Msg']) ) {
						throw new Exception($queryResponse[0]['Error_Msg']);
					}
				}
			}

			$this->commitTransaction();
		}
		catch ( Exception $e ) {
			$this->rollbackTransaction();
			$response[0]['Error_Msg'] = $e->getMessage();
		}

		return $response;
	}

	/**
	 * Сохранение оценки состояния
	 */
	public function saveBleedingCardCondition($data = []) {
		$response = [[ 'Error_Msg' => '' ]];

		try {
			$response = $this->queryResult("
				select	BleedingCardCondition_id as \"BleedingCardCondition_id\",
					Error_Code as \"Error_Code\",
					Error_Message as \"Error_Msg\"
				from	dbo.p_BleedingCardCondition_" . (!empty($data['BleedingCardCondition_id']) ? "upd" : "ins") . "(
					BleedingCardCondition_id := :BleedingCardCondition_id,
					BleedingCard_id := :BleedingCard_id,
					BleedingCardCondition_setDT := :BleedingCardCondition_setDT,
					BleedingCardCondition_Temperature := :BleedingCardCondition_Temperature,
					BleedingCardCondition_SistolPress := :BleedingCardCondition_SistolPress,
					BleedingCardCondition_DiastolPress := :BleedingCardCondition_DiastolPress,
					BleedingCardCondition_Pulse := :BleedingCardCondition_Pulse,
					BleedingCardCondition_BreathFrequency := :BleedingCardCondition_BreathFrequency,
					Diuresis_id := :Diuresis_id,
					CentralNervousSystem_id := :CentralNervousSystem_id,
					PulseOximetry_id := :PulseOximetry_id,
					BleedingCardCondition_CatheterTime := :BleedingCardCondition_CatheterTime,
					BleedingCardCondition_TotalScore := :BleedingCardCondition_TotalScore,
					pmUser_id := :pmUser_id )
			", [
				'BleedingCardCondition_id' => (!isset($data['BleedingCardCondition_id']) || $data['BleedingCardCondition_id'] <= 0 ? NULL : $data['BleedingCardCondition_id']),
				'BleedingCard_id' => $data['BleedingCard_id'],
				'BleedingCardCondition_setDT' => date('Y-m-d H:i:s', strtotime($data['BleedingCardCondition_setDate'] . ' ' . $data['BleedingCardCondition_setTime'])),
				'BleedingCardCondition_Temperature' => ($data['BleedingCardCondition_Temperature'] ?? null),
				'BleedingCardCondition_SistolPress' => ($data['BleedingCardCondition_SistolPress'] ?? null),
				'BleedingCardCondition_DiastolPress' => ($data['BleedingCardCondition_DiastolPress'] ?? null),
				'BleedingCardCondition_Pulse' => !empty($data['BleedingCardCondition_Pulse']) ? $data['BleedingCardCondition_Pulse'] : null,
				'BleedingCardCondition_BreathFrequency' => ($data['BleedingCardCondition_BreathFrequency'] ?? null),
				'Diuresis_id' => ($data['Diuresis_id'] ?? null),
				'CentralNervousSystem_id' => ($data['CentralNervousSystem_id'] ?? null),
				'PulseOximetry_id' => ($data['PulseOximetry_id'] ?? null),
				'BleedingCardCondition_CatheterTime' => !empty($data['BleedingCardCondition_CatheterTime']) ? $data['BleedingCardCondition_CatheterTime'] : null,
				'BleedingCardCondition_TotalScore' => ($data['BleedingCardCondition_TotalScore'] ?? null),
				'pmUser_id' => $data['pmUser_id'],
			]);

			if ( $response === false ) {
				throw new Exception('Ошибка при выполнении запроса к БД');
			}
			else if ( !empty($response[0]['Error_Msg']) ) {
				throw new Exception($response[0]['Error_Msg']);
			}
		}
		catch ( Exception $e ) {
			$this->rollbackTransaction();
			$response[0]['Error_Msg'] = $e->getMessage();
		}

		return $response;
	}

	/**
	 * Сохранение лекарственного средства
	 */
	public function saveBleedingCardDrug($data = []) {
		$response = [[ 'Error_Msg' => '' ]];

		try {
			$response = $this->queryResult("
				select	BleedingCardDrug_id as \"BleedingCardDrug_id\",
					Error_Code as \"Error_Code\",
					Error_Message as \"Error_Msg\"
				from	dbo.p_BleedingCardDrug_" . (!empty($data['BleedingCardDrug_id']) ? "upd" : "ins") . "(
					BleedingCardDrug_id := :BleedingCardDrug_id,
					BleedingCard_id := :BleedingCard_id,
					BleedingCardDrug_setDT := :BleedingCardDrug_setDT,
					DrugComplexMnn_id := :DrugComplexMnn_id,
					PrescriptionIntroType_id := :PrescriptionIntroType_id,
					BleedingCardDrug_Dosage := :BleedingCardDrug_Dosage,
					GoodsUnit_id := :GoodsUnit_id,
					BleedingCardDrug_TotalScore := :BleedingCardDrug_TotalScore,
					pmUser_id := :pmUser_id )
			", [
				'BleedingCardDrug_id' => (!isset($data['BleedingCardDrug_id']) || $data['BleedingCardDrug_id'] <= 0 ? NULL : $data['BleedingCardDrug_id']),
				'BleedingCard_id' => $data['BleedingCard_id'],
				'BleedingCardDrug_setDT' => date('Y-m-d H:i:s', strtotime($data['BleedingCardDrug_setDate'] . ' ' . $data['BleedingCardDrug_setTime'])),
				'DrugComplexMnn_id' => (!empty($data['DrugComplexMnn_id']) ? $data['DrugComplexMnn_id'] : null),
				'PrescriptionIntroType_id' => (!empty($data['PrescriptionIntroType_id']) ? $data['PrescriptionIntroType_id'] : null),
				'BleedingCardDrug_Dosage' => (!empty($data['BleedingCardDrug_Dosage']) || ($data['BleedingCardDrug_Dosage'] ?? null) === '0' ? $data['BleedingCardDrug_Dosage'] : null),
				'GoodsUnit_id' => (!empty($data['GoodsUnit_id']) ? $data['GoodsUnit_id'] : null),
				'BleedingCardDrug_TotalScore' => (!empty($data['BleedingCardDrug_TotalScore']) || ($data['BleedingCardDrug_TotalScore'] ?? null) === '0' ? $data['BleedingCardDrug_TotalScore'] : null),
				'pmUser_id' => $data['pmUser_id'],
			]);

			if ( $response === false ) {
				throw new Exception('Ошибка при выполнении запроса к БД');
			}
			else if ( !empty($response[0]['Error_Msg']) ) {
				throw new Exception($response[0]['Error_Msg']);
			}
		}
		catch ( Exception $e ) {
			$this->rollbackTransaction();
			$response[0]['Error_Msg'] = $e->getMessage();
		}

		return $response;
	}

	/**
	 * Сохранение раствора
	 */
	public function saveBleedingCardSolution($data = []) {
		$response = [[ 'Error_Msg' => '' ]];

		try {
			$response = $this->queryResult("
				select	BleedingCardSolution_id as \"BleedingCardSolution_id\",
					Error_Code as \"Error_Code\",
					Error_Message as \"Error_Msg\"
				from	dbo.p_BleedingCardSolution_" . (!empty($data['BleedingCardSolution_id']) ? "upd" : "ins") . "(
					BleedingCardSolution_id := :BleedingCardSolution_id,
					BleedingCard_id := :BleedingCard_id,
					BleedingCardSolution_setDT := :BleedingCardSolution_setDT,
					SolutionType_id := :SolutionType_id,
					BleedingCardSolution_Volume := :BleedingCardSolution_Volume,
					pmUser_id := :pmUser_id )
			", [
				'BleedingCardSolution_id' => (!isset($data['BleedingCardSolution_id']) || $data['BleedingCardSolution_id'] <= 0 ? NULL : $data['BleedingCardSolution_id']),
				'BleedingCard_id' => $data['BleedingCard_id'],
				'BleedingCardSolution_setDT' => date('Y-m-d H:i:s', strtotime($data['BleedingCardSolution_setDate'] . ' ' . $data['BleedingCardSolution_setTime'])),
				'SolutionType_id' => ($data['SolutionType_id'] ?? null),
				'BleedingCardSolution_Volume' => ($data['BleedingCardSolution_Volume'] ?? null),
				'pmUser_id' => $data['pmUser_id'],
			]);

			if ( $response === false ) {
				throw new Exception('Ошибка при выполнении запроса к БД');
			}
			else if ( !empty($response[0]['Error_Msg']) ) {
				throw new Exception($response[0]['Error_Msg']);
			}
		}
		catch ( Exception $e ) {
			$this->rollbackTransaction();
			$response[0]['Error_Msg'] = $e->getMessage();
		}

		return $response;
	}
}