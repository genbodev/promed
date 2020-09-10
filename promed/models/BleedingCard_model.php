<?php
/**
 * @property BleedingCard_model $BleedingCard_model
 */
class BleedingCard_model extends swModel {
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
			SELECT TOP 1
				BC.BleedingCard_id,
				BC.EvnSection_id,
				convert(varchar(10), BC.BleedingCard_setDT, 104) as BleedingCard_setDT
			FROM v_BleedingCard BC with (nolock)
			WHERE BC.BleedingCard_id = :BleedingCard_id
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
				BCC.BleedingCardCondition_id,
				convert(varchar(10), BCC.BleedingCardCondition_setDT, 104) + ' '
					+ convert(varchar(5), BCC.BleedingCardCondition_setDT, 108) as BleedingCardCondition_setDT,
				convert(varchar(10), BCC.BleedingCardCondition_setDT, 104) as BleedingCardCondition_setDate,
				convert(varchar(5), BCC.BleedingCardCondition_setDT, 108) as BleedingCardCondition_setTime,
				BCC.BleedingCardCondition_Temperature,
				BCC.BleedingCardCondition_SistolPress,
				BCC.BleedingCardCondition_DiastolPress,
				cast(BCC.BleedingCardCondition_SistolPress as varchar(3)) + ' / '
					+ cast(BCC.BleedingCardCondition_DiastolPress as varchar(3)) as BleedingCardCondition_Pressure,
				BCC.BleedingCardCondition_Pulse,
				BCC.BleedingCardCondition_BreathFrequency,
				D.Diuresis_id,
				D.Diuresis_Name,
				CNS.CentralNervousSystem_id,
				CNS.CentralNervousSystem_Name,
				PO.PulseOximetry_id,
				PO.PulseOximetry_Name,
				convert(varchar(5), BCC.BleedingCardCondition_CatheterTime, 108) as BleedingCardCondition_CatheterTime,
				BCC.BleedingCardCondition_TotalScore,
				1 as RecordStatus_Code
			FROM v_BleedingCardCondition BCC with (nolock)
				INNER JOIN v_Diuresis D with (nolock) ON D.Diuresis_id = BCC.Diuresis_id
				INNER JOIN v_CentralNervousSystem CNS with (nolock) ON CNS.CentralNervousSystem_id = BCC.CentralNervousSystem_id
				INNER JOIN v_PulseOximetry PO with (nolock) ON PO.PulseOximetry_id = BCC.PulseOximetry_id
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
				BCS.BleedingCardSolution_id,
				convert(varchar(10), BCS.BleedingCardSolution_setDT, 104) + ' '
					+ convert(varchar(5), BCS.BleedingCardSolution_setDT, 108) as BleedingCardSolution_setDT,
				convert(varchar(10), BCS.BleedingCardSolution_setDT, 104) as BleedingCardSolution_setDate,
				convert(varchar(5), BCS.BleedingCardSolution_setDT, 108) as BleedingCardSolution_setTime,
				BCS.BleedingCardSolution_Volume,
				ST.SolutionType_id,
				ST.SolutionType_Name,
				1 as RecordStatus_Code
			FROM v_BleedingCardSolution BCS with (nolock)
				LEFT JOIN v_SolutionType ST with (nolock) ON ST.SolutionType_id = BCS.SolutionType_id
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
				BCD.BleedingCardDrug_id,
				convert(varchar(10), BCD.BleedingCardDrug_setDT, 104) + ' '
					+ convert(varchar(5), BCD.BleedingCardDrug_setDT, 108) as BleedingCardDrug_setDT,
				convert(varchar(10), BCD.BleedingCardDrug_setDT, 104) as BleedingCardDrug_setDate,
				convert(varchar(5), BCD.BleedingCardDrug_setDT, 108) as BleedingCardDrug_setTime,
				DCM.DrugComplexMnn_id,
				DCM.DrugComplexMnn_RusName as DrugComplexMnn_Name,
				PIT.PrescriptionIntroType_id,
				PIT.PrescriptionIntroType_Name,
				BCD.BleedingCardDrug_Dosage,
				GU.GoodsUnit_id,
				BCD.BleedingCardDrug_TotalScore,
				1 as RecordStatus_Code
			FROM v_BleedingCardDrug BCD with (nolock)
				INNER JOIN rls.DrugComplexMnn DCM with (nolock) ON DCM.DrugComplexMnn_id = BCD.DrugComplexMnn_id
				LEFT JOIN v_PrescriptionIntroType PIT with (nolock) ON PIT.PrescriptionIntroType_id = BCD.PrescriptionIntroType_id
				LEFT JOIN v_GoodsUnit GU with (nolock) ON GU.GoodsUnit_id = BCD.GoodsUnit_id
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
					select {$object}_id from v_{$object} with (nolock) where BleedingCard_id = :BleedingCard_id
				", [
					'BleedingCard_id' => $data['BleedingCard_id'],
				]);

				if ( !is_array($objectList) ) {
					continue;
				}

				foreach ( $objectList as $row ) {
					$queryResponse = $this->queryResult("
						declare
							@ErrCode int,
							@ErrMessage varchar(4000);
	
						exec dbo.p_{$object}_del
							@{$object}_id = :{$object}_id,
							@Error_Code = @ErrCode output,
							@Error_Message = @ErrMessage output;
	
						select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
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
				declare
					@ErrCode int,
					@ErrMessage varchar(4000);

				exec dbo.p_BleedingCard_del
					@BleedingCard_id = :BleedingCard_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMessage output;

				select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
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
				BC.BleedingCard_id,
				BC.EvnSection_id,
				convert(varchar(10), BC.BleedingCard_setDT, 104) as BleedingCard_setDT
			from v_BleedingCard BC with (nolock)
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

			$response = $this->queryResult("
				declare
					@setDT datetime,
					@Res bigint,
					@ErrCode int,
					@ErrMessage varchar(4000);

				set @Res = :BleedingCard_id;

				if ( @Res is not null )
					set @setDT = (select top 1 BleedingCard_setDT from dbo.v_BleedingCard with (nolock) where BleedingCard_id = @Res);
				else
					set @setDT = dbo.tzGetdate(); 

				exec dbo.p_BleedingCard_" . (!empty($data['BleedingCard_id']) ? "upd" : "ins") . "
					@BleedingCard_id = @Res output,
					@EvnSection_id = :EvnSection_id,
					@BleedingCard_setDT = @setDT,
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMessage output;

				select @Res as BleedingCard_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			", [
				'BleedingCard_id' => (!isset($data['BleedingCard_id']) || $data['BleedingCard_id'] <= 0 ? NULL : $data['BleedingCard_id']),
				'EvnSection_id' => $data['EvnSection_id'],
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
								declare
									@ErrCode int,
									@ErrMessage varchar(4000);

								exec dbo.p_{$object}_del
									@{$object}_id = :{$object}_id,
									@Error_Code = @ErrCode output,
									@Error_Message = @ErrMessage output;

								select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
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
				declare
					@Res bigint,
					@ErrCode int,
					@ErrMessage varchar(4000);

				set @Res = :BleedingCardCondition_id;

				exec dbo.p_BleedingCardCondition_" . (!empty($data['BleedingCardCondition_id']) ? "upd" : "ins") . "
					@BleedingCardCondition_id = @Res output,
					@BleedingCard_id = :BleedingCard_id,
					@BleedingCardCondition_setDT = :BleedingCardCondition_setDT,
					@BleedingCardCondition_Temperature = :BleedingCardCondition_Temperature,
					@BleedingCardCondition_SistolPress = :BleedingCardCondition_SistolPress,
					@BleedingCardCondition_DiastolPress = :BleedingCardCondition_DiastolPress,
					@BleedingCardCondition_Pulse = :BleedingCardCondition_Pulse,
					@BleedingCardCondition_BreathFrequency = :BleedingCardCondition_BreathFrequency,
					@Diuresis_id = :Diuresis_id,
					@CentralNervousSystem_id = :CentralNervousSystem_id,
					@PulseOximetry_id = :PulseOximetry_id,
					@BleedingCardCondition_CatheterTime = :BleedingCardCondition_CatheterTime,
					@BleedingCardCondition_TotalScore = :BleedingCardCondition_TotalScore,
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMessage output;

				select @Res as BleedingCardCondition_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			", [
				'BleedingCardCondition_id' => (!isset($data['BleedingCardCondition_id']) || $data['BleedingCardCondition_id'] <= 0 ? NULL : $data['BleedingCardCondition_id']),
				'BleedingCard_id' => $data['BleedingCard_id'],
				'BleedingCardCondition_setDT' => date('Y-m-d H:i:s', strtotime($data['BleedingCardCondition_setDate'] . ' ' . $data['BleedingCardCondition_setTime'])),
				'BleedingCardCondition_Temperature' => ($data['BleedingCardCondition_Temperature'] ?? null),
				'BleedingCardCondition_SistolPress' => ($data['BleedingCardCondition_SistolPress'] ?? null),
				'BleedingCardCondition_DiastolPress' => ($data['BleedingCardCondition_DiastolPress'] ?? null),
				'BleedingCardCondition_Pulse' => ($data['BleedingCardCondition_Pulse'] ?? null),
				'BleedingCardCondition_BreathFrequency' => ($data['BleedingCardCondition_BreathFrequency'] ?? null),
				'Diuresis_id' => ($data['Diuresis_id'] ?? null),
				'CentralNervousSystem_id' => ($data['CentralNervousSystem_id'] ?? null),
				'PulseOximetry_id' => ($data['PulseOximetry_id'] ?? null),
				'BleedingCardCondition_CatheterTime' => ($data['BleedingCardCondition_CatheterTime'] ?? null),
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
				declare
					@Res bigint,
					@ErrCode int,
					@ErrMessage varchar(4000);

				set @Res = :BleedingCardDrug_id;

				exec dbo.p_BleedingCardDrug_" . (!empty($data['BleedingCardDrug_id']) ? "upd" : "ins") . "
					@BleedingCardDrug_id = @Res output,
					@BleedingCard_id = :BleedingCard_id,
					@BleedingCardDrug_setDT = :BleedingCardDrug_setDT,
					@DrugComplexMnn_id = :DrugComplexMnn_id,
					@PrescriptionIntroType_id = :PrescriptionIntroType_id,
					@BleedingCardDrug_Dosage = :BleedingCardDrug_Dosage,
					@GoodsUnit_id = :GoodsUnit_id,
					@BleedingCardDrug_TotalScore = :BleedingCardDrug_TotalScore,
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMessage output;

				select @Res as BleedingCardDrug_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
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
				declare
					@Res bigint,
					@ErrCode int,
					@ErrMessage varchar(4000);

				set @Res = :BleedingCardSolution_id;

				exec dbo.p_BleedingCardSolution_" . (!empty($data['BleedingCardSolution_id']) ? "upd" : "ins") . "
					@BleedingCardSolution_id = @Res output,
					@BleedingCard_id = :BleedingCard_id,
					@BleedingCardSolution_setDT = :BleedingCardSolution_setDT,
					@SolutionType_id = :SolutionType_id,
					@BleedingCardSolution_Volume = :BleedingCardSolution_Volume,
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMessage output;

				select @Res as BleedingCardSolution_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
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