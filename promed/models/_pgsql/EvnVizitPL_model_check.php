<?php

class EvnVizitPL_model_check
{
	/**
	 * @param EvnVizitPL_model $callObject
	 * @return bool
	 * @throws Exception
	 */
	public static function _checkOnkoSpecifics(EvnVizitPL_model $callObject)
	{
		if ($callObject->regionNick != "ufa" || !$callObject->isLastVizit() || $callObject->parent->IsFinish != 2 || $callObject->payTypeSysNick != "oms" || empty($callObject->UslugaComplex_id)) {
			return true;
		}
		$query = "
			select UslugaComplex_Code as \"UslugaComplex_Code\"
			from v_UslugaComplex
			where UslugaComplex_id = :UslugaComplex_id
			limit 1
		";
		$queryParams = ["UslugaComplex_id" => $callObject->UslugaComplex_id];
		$UslugaComplex_Code = $callObject->getFirstResultFromQuery($query, $queryParams);
		if (!empty($UslugaComplex_Code) && (in_array(substr($UslugaComplex_Code, 1, 2), ["74", "75", "76", "77"]) || in_array(substr($UslugaComplex_Code, -3), ["874", "875"]))) {
			return true;
		}
		$query = "
			select evpl.EvnVizitPL_id as \"EvnVizitPL_id\"
			from
				v_EvnVizitPL evpl
				inner join v_Diag Diag on Diag.Diag_id = evpl.Diag_id
				left join v_MorbusOnkoVizitPLDop movpld on movpld.EvnVizit_id = evpl.EvnVizitPL_id 
			where evpl.EvnVizitPL_id = :EvnVizitPL_id
			  and movpld.EvnDiagPLSop_id is null
			  and ((Diag.Diag_Code >= 'C00' and Diag.Diag_Code <= 'C97') or (Diag.Diag_Code >= 'D00' and Diag.Diag_Code <= 'D09'))
			  and movpld.MorbusOnkoVizitPLDop_id is null
			  /*and (
					movpld.MorbusOnkoVizitPLDop_id is null or 
					(
						not exists (select MorbusOnkoLink_id from v_MorbusOnkoLink MOL where movpld.MorbusOnkoVizitPLDop_id = MOL.MorbusOnkoVizitPLDop_id limit 1) and 
						movpld.HistologicReasonType_id is null
					)
				)*/
			limit 1
		";
		$queryParams = ["EvnVizitPL_id" => $callObject->id];
		$checkResult = $callObject->getFirstResultFromQuery($query, $queryParams);

		$query = "
			select evpl.EvnVizitPL_id as \"EvnVizitPL_id\"
			from
				v_EvnVizitPL evpl
				inner join v_Diag Diag on Diag.Diag_id = evpl.Diag_id
				inner join v_EvnDiagPLSop eds on eds.EvnDiagPLSop_pid = evpl.EvnVizitPL_id
				inner join v_Diag DiagS on DiagS.Diag_id = eds.Diag_id
				left join v_MorbusOnkoVizitPLDop movpld on movpld.EvnVizit_id = evpl.EvnVizitPL_id and movpld.EvnDiagPLSop_id = eds.EvnDiagPLSop_id
			where  evpl.EvnVizitPL_id = :EvnVizitPL_id
			  and (((DiagS.Diag_Code >= 'C00' and DiagS.Diag_Code <= 'C80') or DiagS.Diag_Code = 'C97') and (Diag.Diag_Code = 'D70'))
			  and movpld.MorbusOnkoVizitPLDop_id is null
			  /*and (
					movpld.MorbusOnkoVizitPLDop_id is null or 
					(
						not exists (select MorbusOnkoLink_id from v_MorbusOnkoLink MOL where movpld.MorbusOnkoVizitPLDop_id = MOL.MorbusOnkoVizitPLDop_id limit 1) and 
						movpld.HistologicReasonType_id is null
					)
				)*/
			limit 1
		";
		$queryParams = ["EvnVizitPL_id" => $callObject->id];
		$checkResult2 = $callObject->getFirstResultFromQuery($query, $queryParams);
		if (!empty($checkResult) || !empty($checkResult2)) {
			throw new Exception('В случае лечения установлен диагноз из диапазона С00-C97 или D00-D09. Заполните раздел "Специфика (онкология)" или проверьте корректность заполнения обязательных полей данного раздела: «Повод обращения», «Стадия опухолевого процесса», «Т», «N», «M» (Стадия опухолевого процесса по системе TNM) в блоках ФОМС и Канцер регистр. Обязательные поля раздела отмечены символом *.');
		}
		return true;
	}

	/**
	 * @param EvnVizitPL_model $callObject
	 * @param $data
	 * @return array
	 */
	public static function checkMesOldUslugaComplexFields(EvnVizitPL_model $callObject, $data)
	{
		$response = [
			"hasDrugTherapySchemeLinks" => false,
			"hasRehabScaleLinks" => false,
			"hasSofaLinks" => false,
			"Error_Msg" => ""
		];
		$data["MesType_id"] = 10;
		if (in_array($data["LpuUnitType_id"], ["6", "7", "9"])) {
			$data["MesType_id"] = 9;
		}
		$dtsParams = [
			"MesType_id" => $data["MesType_id"],
			'EvnVizitPL_setDate' => $data['EvnVizitPL_setDate'] ?? date('Y-m-d')
		];
		if (!empty($data["Diag_id"])) {
			$drugTherapySchemeQueries[] = "
				select distinct
					mouc.DrugTherapyScheme_id as \"DrugTherapyScheme_id\"
				from
					v_MesOldUslugaComplex mouc
					inner join v_MesOld mo on mo.Mes_id = mouc.Mes_id
					inner join v_DrugTherapyScheme dts on dts.DrugTherapyScheme_id = mouc.DrugTherapyScheme_id
				where mouc.Diag_id = :Diag_id
					and mouc.DrugTherapyScheme_id is not null
					and coalesce(mo.MesType_id, :MesType_id) = :MesType_id
					and coalesce(dts.DrugTherapyScheme_endDate, :EvnVizitPL_setDate) >= :EvnVizitPL_setDate
			";
			$dtsParams["Diag_id"] = $data["Diag_id"];
		}
		if (!empty($data["EvnVizitPL_id"])) {
			$drugTherapySchemeQueries[] = "
				select distinct
					mouc.DrugTherapyScheme_id as \"DrugTherapyScheme_id\"
				from
					v_EvnUsluga eu
					inner join v_MesOldUslugaComplex mouc on mouc.UslugaComplex_id = eu.UslugaComplex_id
					inner join v_MesOld mo on mo.Mes_id = mouc.Mes_id
					inner join v_DrugTherapyScheme dts on dts.DrugTherapyScheme_id = mouc.DrugTherapyScheme_id
				where eu.EvnUsluga_pid = :EvnVizitPL_id
					and mouc.DrugTherapyScheme_id is not null
					and coalesce(mo.MesType_id, :MesType_id) = :MesType_id
					and coalesce(dts.DrugTherapyScheme_endDate, :EvnVizitPL_setDate) >= :EvnVizitPL_setDate
			";
			$dtsParams["EvnVizitPL_id"] = $data["EvnVizitPL_id"];
		}
		if (!empty($drugTherapySchemeQueries)) {
			// проверяем наличие связок
			$resp = $callObject->queryResult(implode(" union ", $drugTherapySchemeQueries), $dtsParams);
			if (!empty($resp[0]["DrugTherapyScheme_id"])) {
				$response["hasDrugTherapySchemeLinks"] = true;
				$response["DrugTherapySchemeIds"] = [];
				foreach ($resp as $respone) {
					$response["DrugTherapySchemeIds"][] = $respone["DrugTherapyScheme_id"];
				}
			}
		}
		if (!empty($data["Diag_id"])) {
			$query = "
				select mouc.MesOldUslugaComplex_id as \"MesOldUslugaComplex_id\"
				from
					v_MesOldUslugaComplex mouc
					inner join v_MesOld mo on mo.Mes_id = mouc.Mes_id
				where mouc.Diag_id = :Diag_id
				  and mouc.MesOldUslugaComplex_SofaScalePoints is not null
				  and coalesce(mo.MesType_id, :MesType_id) = :MesType_id
				limit 1
			";
			$queryParams = [
				"Diag_id" => $data["Diag_id"],
				"MesType_id" => $data["MesType_id"]
			];
			$resp = $callObject->queryResult($query, $queryParams);
			if (!empty($resp[0]["MesOldUslugaComplex_id"])) {
				$response["hasSofaLinks"] = true;
			}
		}
		if (!empty($data["EvnVizitPL_id"])) {
			// проверяем наличие связок
			$query = "
				select mouc.MesOldUslugaComplex_id as \"MesOldUslugaComplex_id\"
				from
					v_MesOldUslugaComplex mouc
					left join v_MesOld mo on mo.Mes_id = mouc.Mes_id
					left join EvnUsluga eu on eu.UslugaComplex_id = mouc.UslugaComplex_id
					left join v_Evn ev on ev.Evn_id = eu.Evn_id
				where ev.Evn_pid = :EvnVizitPL_id
				  and mouc.RehabScale_id is not null
				  and coalesce(mo.MesType_id, :MesType_id) = :MesType_id
				limit 1
			";
			$queryParams = [
				"EvnVizitPL_id" => $data["EvnVizitPL_id"],
				"MesType_id" => $data["MesType_id"]
			];
			$resp = $callObject->queryResult($query, $queryParams);
			if (!empty($resp[0]["MesOldUslugaComplex_id"])) {
				$response["hasRehabScaleLinks"] = true;
			}
		}
		return $response;
	}

	/**
	 * @param EvnVizitPL_model $callObject
	 * @throws Exception
	 */
	public static function _checkChangeEvnUsluga(EvnVizitPL_model $callObject)
	{
		// проверка наличия стомат.услуг при сохранении стомат.посещения из формы редактирования #40490
		if ($callObject->regionNick == "ufa" && !$callObject->isNewRecord && $callObject->scenario == EvnVizitPL_model::SCENARIO_DO_SAVE && $callObject->evnClassId == 13) {
			$isEmptyEvnUslugaList = empty($callObject->evnUslugaList);
			// исключаем код посещения
			foreach ($callObject->evnUslugaList as $row) {
				if ($row["EvnUsluga_IsVizitCode"] == 1) {
					$isEmptyEvnUslugaList = false;
					break;
				}
			}
			if ($isEmptyEvnUslugaList) {
				throw new Exception("Не введено ни одной услуги. Сохранение посещения невозможно");
			}
		}
		if ($callObject->regionNick == "perm" &&
			empty($callObject->getParam("ignoreControl59536")) &&
			in_array($callObject->scenario, [EvnVizitPL_model::SCENARIO_DO_SAVE, EvnVizitPL_model::SCENARIO_SET_ATTRIBUTE]) &&
			$callObject->evnClassId == 11 &&
			$callObject->payTypeSysNick == "oms" &&
			$callObject->person_Age < 18
		) {
			// проверяем по услугам в рамках посещения
			$uslugaComplexCodeList = [];
			foreach ($callObject->evnUslugaList as $row) {
				$uslugaComplexCodeList[] = $row["UslugaComplex_Code"];
			}
			if (in_array("B01.003.004.099", $uslugaComplexCodeList) && !(in_array("A06.30.003.001", $uslugaComplexCodeList) || in_array("A05.30.003", $uslugaComplexCodeList))) {
				$callObject->setSaveResponse("Alert_Msg", "
					Случай не будет оплачен, так как услуга  B01.003.004.099 Анестезиологическое пособие оплачивается для детей
					только при наличии услуги A06.30.003.001 Проведение компьютерных томографических исследований
					или A05.30.003 Проведение магнитно-резонансных томографических исследований.  Продолжить сохранение?
				");
				throw new Exception("YesNo", 103);
			}
		}
		if ($callObject->regionNick == "penza" && empty($callObject->getParam("ignoreControl122430")) &&
			in_array($callObject->scenario, [EvnVizitPL_model::SCENARIO_DO_SAVE, EvnVizitPL_model::SCENARIO_SET_ATTRIBUTE]) &&
			$callObject->evnClassId == 11 &&
			$callObject->payTypeSysNick == "oms" &&
			isset($callObject->lpuSectionData["LpuUnitType_SysNick"]) && $callObject->lpuSectionData["LpuUnitType_SysNick"] == "fap"
		) {
			// и в посещении не заведена услуга с атрибутом «Услуга ФАП», то выходит предупреждение «Посещение ФАП без услуги ФАП не будет оплачено»
			$hasFapUslugaComplex = false;
			if (!empty($callObject->UslugaComplex_id)) {
				//проверяем услугу кода посещения
				$query = "
					select uc.UslugaComplex_id as \"UslugaComplex_id\"
					from v_UslugaComplex uc
					where uc.UslugaComplex_id = :UslugaComplex_id
					  and exists (
							select t1.UslugaComplexAttribute_id
							from
								UslugaComplexAttribute t1
								inner join UslugaComplexAttributeType t2 on t2.UslugaComplexAttributeType_id = t1.UslugaComplexAttributeType_id
							where t1.UslugaComplex_id = uc.UslugaComplex_id
							  and t2.UslugaComplexAttributeType_SysNick in ('code_usl')
						)
					limit 1
				";
				$queryParams = ["UslugaComplex_id" => $callObject->UslugaComplex_id];
				$resp_eu = $callObject->queryResult($query, $queryParams);
				if (!empty($resp_eu[0]["UslugaComplex_id"])) {
					$hasFapUslugaComplex = true;
				}
			}
			if (!$hasFapUslugaComplex && !empty($callObject->id)) {
				//проверяем дополнительные услуги
				$query = "
					select eu.EvnUsluga_id as \"EvnUsluga_id\"
					from v_EvnUsluga eu
					where eu.EvnUsluga_pid = :EvnUsluga_pid
					  and exists (
							select t1.UslugaComplexAttribute_id
							from
								UslugaComplexAttribute t1
								inner join UslugaComplexAttributeType t2 on t2.UslugaComplexAttributeType_id = t1.UslugaComplexAttributeType_id
							where t1.UslugaComplex_id = EU.UslugaComplex_id
							  and t2.UslugaComplexAttributeType_SysNick in ('code_usl')
					  )
					  and coalesce(eu.EvnUsluga_IsVizitCode, 1) = 1
					limit 1
				";
				$queryParams = ["EvnUsluga_pid" => $callObject->id];
				$resp_eu = $callObject->queryResult($query, $queryParams);
				if (!empty($resp_eu[0]["EvnUsluga_id"])) {
					$hasFapUslugaComplex = true;
				}
			}
			if (!$hasFapUslugaComplex) {
				$callObject->setSaveResponse("Alert_Msg", "Посещение ФАП без услуги ФАП не будет оплачено.  Продолжить сохранение?");
				//отменяем сохранение, пользователю показываем Alert_Msg и выводим вопрос: Продолжить сохранение?
				throw new Exception("YesNo", 105);
			}
		}

		if ( $callObject->regionNick == 'ekb'
			&& in_array($callObject->scenario, array(EvnVizitPL_model::SCENARIO_DO_SAVE))
			&& $callObject->evnClassId == 11
			&& !empty($callObject->UslugaComplex_id)
		) {

			$Mes_code_query = "
			select 
				Mes_Code 
			from 
				v_MesOld
			where 
				Mes_id=:Mes_id
			";
			$Mes_code = $callObject->getFirstResultFromQuery($Mes_code_query,
				array("Mes_id" => $callObject->Mes_id));

			if (in_array($Mes_code,array(1703,1704))) {
				return;
			}
			
			$ServiceType_SysNick = $callObject->getFirstResultFromQuery("
				select ServiceType_SysNick
				from v_ServiceType
				where ServiceType_id = :ServiceType_id
				limit 1
			", array('ServiceType_id' => $callObject->ServiceType_id));
			if (!$ServiceType_SysNick) {
				throw new Exception('Ошибка при запросе данных для типа обслуживания');
			}

			if (in_array($ServiceType_SysNick, array('home','ahome','neotl'))) {
				$vizitUslugaComplexPartition_Code = null;
				$flag = false;
				foreach($callObject->evnUslugaList as $row) {
					if ($row['EvnUsluga_IsVizitCode'] != 2 && $row['UslugaComplex_Code'] == 'B04.069.333') {
						$flag = true;
					}
				}

				$vizitUslugaComplexPartition_Code = $callObject->getFirstResultFromQuery("
					with sex as 
					(
						select
							P.Sex_id
						from 
							v_Person_all P
						where 
							P.PersonEvn_id = :PersonEvn_id and P.Server_id = :Server_id
						limit 1
					), 
					MedSpecOms as
					(
						select
							MSF.MedSpecOms_id
						from 
							v_MedStaffFact MSF
						where
							MSF.MedStaffFact_id = :MedStaffFact_id
						limit 1
					)

					select 
						UCP.UslugaComplexPartition_Code
					from 
						r66.v_UslugaComplexPartitionLink UCPL  
						inner join r66.v_UslugaComplexPartition UCP on UCP.UslugaComplexPartition_id = UCPL.UslugaComplexPartition_id
					where
						UCPL.UslugaComplex_id = :UslugaComplex_id
						and (UCPL.Sex_id is null or UCPL.Sex_id = (select Sex_id from Sex))
						and (UCPL.MedSpecOms_id is null or UCPL.MedSpecOms_id = (select MedSpecOms_id from MedSpecOms))
						and (UCPL.LpuSectionProfile_id is null or UCPL.LpuSectionProfile_id = :LpuSectionProfile_id)
						and UCPL.PayType_id = :PayType_id
						and coalesce(UCPL.UslugaComplexPartitionLink_IsMes, 1) = :IsMes
					limit 1
				", array(
					'UslugaComplex_id' => $callObject->UslugaComplex_id,
					'PersonEvn_id' => $callObject->PersonEvn_id,
					'Server_id' => $callObject->Server_id,
					'MedStaffFact_id' => $callObject->MedStaffFact_id,
					'LpuSectionProfile_id' => $callObject->LpuSectionProfile_id,
					'PayType_id' => $callObject->PayType_id,
					'IsMes' => empty($callObject->Mes_id) ? 1 : 2
				));

				if ($vizitUslugaComplexPartition_Code == '300' && !$flag) {
					throw new Exception('В посещении с местом обслуживания «2. На дому» , «3. На дому: Актив» либо «4. На дому: НМП» не заведена услуга B04.069.333 «Оказание медицинской помощи вне медицинской организации (на дому)»');
				}
			}
		}
	}

	/**
	 * @param EvnVizitPL_model $callObject
	 * @throws Exception
	 */
	public static function _checkEvnDirectionProfile(EvnVizitPL_model $callObject)
	{
		if (empty($callObject->getParam("ignoreEvnDirectionProfile")) && in_array($callObject->scenario, [EvnVizitPL_model::SCENARIO_DO_SAVE, EvnVizitPL_model::SCENARIO_SET_ATTRIBUTE]) && $callObject->evnClassId == 11) {
			// Если в первом посещении ТАП профиль отделения, указанного в основном разделе, отличается от профиля электронного направления, выбранного в ТАП, то:
			$first = true;
			$vizitList = $callObject->parent->evnVizitList;
			if (!empty($callObject->setDT)) {
				foreach ($vizitList as $vizit) {
					if ($vizit["EvnVizitPL_id"] != $callObject->id && !empty($vizit["EvnVizitPL_setDate"]) && strtotime($vizit["EvnVizitPL_setDate"] . " " . $vizit["EvnVizitPL_setTime"]) < strtotime($callObject->setDT->format("Y-m-d H:i"))) {
						$first = false;
					}
				}
			}
			if ($first && !empty($callObject->LpuSectionProfile_id) && !empty($callObject->parent->EvnDirection_id)) {
				// получаем профиль
				$LpuSectionProfile_id = $callObject->getFirstResultFromQuery("select LpuSectionProfile_id as \"LpuSectionProfile_id\" from v_EvnDirection where EvnDirection_id = :EvnDirection_id", ["EvnDirection_id" => $callObject->parent->EvnDirection_id]);
				if (!empty($LpuSectionProfile_id) && $LpuSectionProfile_id != $callObject->LpuSectionProfile_id) {
					$deny = false; // Предупреждение
					if (!empty($callObject->globalOptions["globals"]["evndirection_check_profile"]) && $callObject->globalOptions["globals"]["evndirection_check_profile"] == 2) {
						$deny = true; // Ошибка
					}
					if (!$deny) {
						$callObject->setSaveResponse("Alert_Msg", "Профиль отделения первого посещения не совпадает с профилем выбранного электронного направления. Продолжить сохранение?");
						//отменяем сохранение, пользователю показываем Alert_Msg и выводим вопрос: Продолжить сохранение?
						throw new Exception("YesNo", 104);
					} else {
						throw new Exception("Необходимо совпадение профиля отделения в первом посещении с профилем выбранного электронного направления.", 400);
					}
				}
			}
		}
	}

	/**
	 * @param EvnVizitPL_model $callObject
	 * @throws Exception
	 */
	public static function _checkChangeSetDate(EvnVizitPL_model $callObject)
	{
		if (empty($callObject->setDT)) {
			throw new Exception("Дата и время посещения обязательны для заполнения", 400);
		}
		// проверка наличия ссылок в событиях (кроме услуг) на редактируемое стомат.посещение при изменении даты посещения
		if (!$callObject->isNewRecord &&
			$callObject->evnClassId == 13 &&
			$callObject->setDate != $callObject->getSavedData("EvnVizitPL_setDate") &&
			$callObject->scenario == EvnVizitPL_model::SCENARIO_DO_SAVE
		) {
			$query = "
				select count(Evn_id) as \"Count\"
				from v_Evn E
				where E.Evn_pid = :id
				  and E.EvnClass_SysNick not in ('EvnDiagPLStom','EvnUslugaStom','EvnUslugaCommon')
			";
			$queryParams = ["id" => $callObject->id];
			$result = $callObject->getFirstResultFromQuery($query, $queryParams);
			if ($result > 0) {
				throw new Exception("На посещение ссылаются другие события, которые требуют отмены или ручного изменения даты.", 400);
			}
		}
		//Проверяем, есть ли пересечения даты сохраняемого посещения с каким либо движением
		$vizit_kvs_control = 1;
		if (array_key_exists("vizit_kvs_control", $callObject->globalOptions["globals"])) {
			$vizit_kvs_control = $callObject->globalOptions["globals"]["vizit_kvs_control"];
		}
		$control_paytype = 0;
		if (array_key_exists("vizit_kvs_control", $callObject->globalOptions["globals"])) {
			$control_paytype = $callObject->globalOptions["globals"]["vizit_kvs_control_paytype"];
		}
		if (empty($callObject->getParam('ignore_vizit_kvs_control')) && empty($callObject->getParam('ignoreDayProfileDuplicateVizit'))) {
			if ($vizit_kvs_control == 3 || ($vizit_kvs_control == 2 && empty($callObject->getParam("vizit_kvs_control_check")))) {
				$and = " and LUT.LpuUnitType_Code = 2";
				if ($callObject->regionNick == "kareliya") {
					$and = "";
				}
				$queryParams = array(
					"EvnVizitPL_setDT" => $callObject->setDT->format("Y-m-d H:i"),
					"Person_id" => $callObject->Person_id,
					"PayType_id" => $callObject->PayType_id
				);
				$payTypeFilter = $control_paytype ? "and ES.PayType_id = :PayType_id" : "";
				$diagFilter = getAccessRightsDiagFilter("D.Diag_Code");
				$diagFilter = !empty($diagFilter) ? "and {$diagFilter}" : "";
				$query = "
					select
						ES.EvnSection_id as \"EvnSection_id\",
						to_char(ES.EvnSection_setDate, 'dd.mm.yyyy') as \"EvnSection_setDate\",
						to_char(ES.EvnSection_disDate, 'dd.mm.yyyy') as \"EvnSection_disDate\",
						L.Lpu_Nick as \"Lpu_Nick\",
						D.Diag_FullName as \"Diag_FullName\",
						D.Diag_Code as \"Diag_Code\"
					from
						v_EvnSection ES
						inner join v_LpuSection LS on ES.LpuSection_id = LS.LpuSection_id
						inner join v_LpuUnit LU on LU.LpuUnit_id = LS.LpuUnit_id
						inner join LpuUnitType LUT on LU.LpuUnitType_id = LUT.LpuUnitType_id {$and}
						left join v_Lpu L on L.Lpu_id = ES.Lpu_id
						left join v_Diag D on D.Diag_id = ES.Diag_id
					where ES.EvnSection_setDT < :EvnVizitPL_setDT
					  and (ES.EvnSection_disDT > :EvnVizitPL_setDT or ES.EvnSection_disDT is null)
					  and ES.Person_id = :Person_id
					  and (coalesce(ES.EvnSection_IsPriem, 1) != 2 or ES.EvnSection_Count = 1)
					  {$diagFilter}
					  {$payTypeFilter}
					limit 1
				";
				$result = $callObject->db->query($query, $queryParams);
				if (!is_object($result)) {
					throw new Exception("Не удалось проверить пересечение посещения с движением.");
				}
				$checkEvnSection = $result->result("array");
				if (is_array($checkEvnSection) && count($checkEvnSection) > 0 && !empty($checkEvnSection[0]["EvnSection_id"])) {
					$disDate = !empty($checkEvnSection[0]["EvnSection_disDate"]) ? $checkEvnSection[0]["EvnSection_disDate"] : "текущее время";
					$diagFullName = checkDiagAccessRights($checkEvnSection[0]["Diag_Code"]) ? $checkEvnSection[0]["Diag_FullName"] : "";
					if ($vizit_kvs_control == 3) {
						//Запрет сохранения
						$msg = "Данное посещение имеет пересечение со случаем стационарного лечения {$checkEvnSection[0]["EvnSection_setDate"]} - {$disDate} / {$diagFullName} / {$checkEvnSection[0]["Lpu_Nick"]}. Сохранить невозможно.";
						throw new Exception($msg);
					} elseif (empty($callObject->getParam("vizit_kvs_control_check"))) {
						//предупреждение
						$callObject->setSaveResponse("ignoreParam", 'vizit_kvs_control_check');
						$callObject->setSaveResponse("Alert_Msg", "Данное посещение имеет пересечение со случаем стационарного лечения {$checkEvnSection[0]["EvnSection_setDate"]} - {$disDate} / {$diagFullName} / {$checkEvnSection[0]["Lpu_Nick"]}.");
						throw new Exception("YesNo", 111);
					}
				}
			}
		}
	}

	/**
	 * @param EvnVizitPL_model $callObject
	 * @throws Exception
	 */
	public static function _checkChangeVizitType(EvnVizitPL_model $callObject)
	{
		// Проверка заполнения поля "Цель посещения"
		if (in_array($callObject->scenario, [EvnVizitPL_model::SCENARIO_DO_SAVE, EvnVizitPL_model::SCENARIO_SET_ATTRIBUTE]) && empty($callObject->VizitType_id)) {
			throw new Exception("Поле \"Цель посещения\" обязательно для заполнения", 400);
		}
		if ($callObject->vizitTypeSysNick != "prof") {
			$callObject->setRefactorAttribute("ProfGoal_id", null);
		}
		if ($callObject->regionNick == "astra" && ($callObject->vizitTypeSysNick != "cz" || ($callObject->person_Age >= 18 && strtotime($callObject->setDate) >= strtotime("2017-07-21")) || ($callObject->person_Age < 18 && strtotime($callObject->setDate) >= strtotime("2017-07-24")))) {
			$callObject->setRefactorAttribute("RiskLevel_id", null);
		}
		if ($callObject->regionNick == "astra" && ($callObject->vizitTypeSysNick != "cz" || $callObject->person_Age < 2 || $callObject->person_Age >= 18 || strtotime($callObject->setDate) < strtotime("2017-07-24"))) {
			$callObject->setRefactorAttribute("WellnessCenterAgeGroups_id", null);
		}
		//учитывать в первую очередь параметр, переданный с формы
		$EvnPL_IsFinish = (!empty($callObject->getParam("IsFinish")))
			?(!empty($callObject->getParam("IsFinish")))
			:($callObject->parent->IsFinish == 2);
		if (in_array($callObject->regionNick, ["buryatiya", "kareliya", "astra"]) && in_array($callObject->scenario, [EvnVizitPL_model::SCENARIO_DO_SAVE, EvnVizitPL_model::SCENARIO_SET_ATTRIBUTE])) {
			$EvnVizitPL_Count = count($callObject->parent->evnVizitList);
			$cntConsulDiagn = 0;
			$cntDesease = 0;
			$cntOther = 0;
			foreach ($callObject->parent->evnVizitList as $id => $row) {
				if ($id == $callObject->id) {
					continue;
				}
				if ($row["VizitType_SysNick"] == "desease") {
					$cntDesease++;
				} else if ($row["VizitType_SysNick"] == "ConsulDiagn") {
					$cntConsulDiagn++;
				} else {
					$cntOther++;
				}
			}
			if ($callObject->isNewRecord && $EvnVizitPL_Count > 1 && $cntOther > 0 && !("kareliya" === $callObject->regionNick && strpos($callObject->setDate, "2017") >= 0)) {
				throw new Exception("В ТАП более одного посещения и присутствуют посещения с целью, отличной от \"Обращение по поводу заболевания\"!", 400);
			}
			if (!$callObject->isNewRecord) {
				if ($callObject->vizitTypeSysNick == 'desease') {
					$cntDesease++;
				} else if ($callObject->vizitTypeSysNick == 'ConsulDiagn') {
					$cntConsulDiagn++;
				}
			}
			//Проверка соответствия переданного VizitType и IsFinish (ЭМК)
			if (!($callObject->getParam("streamInput") != null && $callObject->getParam("streamInput"))) {
				if ($callObject->vizitTypeSysNick == "desease" && $EvnPL_IsFinish && $EvnVizitPL_Count == 1 && false === in_array($callObject->regionNick, ["buryatiya", "astra"])) {
					throw new Exception("Сохранение закрытого ТАП по заболеванию с одним посещением невозможно", 400);
				}
				if ($callObject->vizitTypeSysNick == "desease" && $EvnPL_IsFinish && $EvnVizitPL_Count == 1 && $callObject->evnClassId == 11 && "astra" === $callObject->regionNick) {
					throw new Exception("Сохранение закрытого ТАП по заболеванию с одним посещением невозможно", 400);
				}
			}
			//Добавляемое посещение с целью отличной от desease должно быть единственным
			if ("kareliya" === $callObject->regionNick && strpos($callObject->setDate, "2017") >= 0) {
				if ($callObject->isNewRecord && ($callObject->vizitTypeSysNick != "desease" && $callObject->vizitTypeSysNick != "consulspec") && $EvnVizitPL_Count > 1) {
					throw new Exception("Добавление посещения невозможно, т.к. в ТАП уже создано посещение!", 400);
				}
				if (!$callObject->isNewRecord && ($callObject->vizitTypeSysNick != "desease" && $callObject->vizitTypeSysNick != "consulspec") && $EvnVizitPL_Count > 1) {
					throw new Exception("Сохранение посещения с целью отличной от \"Обращение по поводу заболевания\" или \"Диспансерное наблюдение\" невозможно, т.к. в ТАП более одного посещения и присутствуют посещения с целью \"Обращение по поводу заболевания\" или \"Диспансерное наблюдение\"!", 400);
				}
			} else {
				if ($callObject->isNewRecord && !in_array($callObject->vizitTypeSysNick, array("ConsulDiagn", "desease")) && $EvnVizitPL_Count > 1) {
					throw new Exception("Добавление посещения невозможно, т.к. в ТАП уже создано посещение!", 400);
				}
				if (!$callObject->isNewRecord && $callObject->vizitTypeSysNick != "desease" && $callObject->vizitTypeSysNick != "ConsulDiagn" && $EvnVizitPL_Count > 1) {
					if ($cntDesease > 0) {
						throw new Exception("Сохранение посещения с целью, отличной от \"Обращение по поводу заболевания\" невозможно, т.к. в ТАП более одного посещения, и присутствуют посещения с целью \"Обращение по поводу заболевания\"!", 400);
					}
					if ($cntConsulDiagn > 0) {
						throw new Exception("Сохранение посещения с целью, отличной от \"Консультативно-диагностическая\" невозможно, т.к. в ТАП более одного посещения, и присутствуют посещения с целью \"Консультативно-диагностическая\"!", 400);
					}
				}
				if (!$callObject->isNewRecord && $callObject->regionNick == "astra" && $EvnVizitPL_Count > 1) {
					if ($callObject->vizitTypeSysNick == "desease" && $cntConsulDiagn > 0) {
						throw new Exception("Сохранение посещения с целью \"Обращение по поводу заболевания\" невозможно, т.к. в ТАП более одного посещения, и присутствуют посещения с целью \"Консультативно-диагностическая\"!", 400);
					}
					if ($callObject->vizitTypeSysNick == "ConsulDiagn" && $cntDesease > 0 && $EvnVizitPL_Count > 1) {
						throw new Exception("Сохранение посещения с целью \"Консультативно-диагностическая\" невозможно, т.к. в ТАП более одного посещения, и присутствуют посещения с целью \"Обращение по поводу заболевания\"!", 400);
					}
				}
			}
		}
		if ($callObject->getRegionNick() == "kareliya" && in_array($callObject->scenario, [EvnVizitPL_model::SCENARIO_DO_SAVE, EvnVizitPL_model::SCENARIO_SET_ATTRIBUTE]) && ($callObject->vizitTypeSysNick == "npom" || $callObject->vizitTypeSysNick == "nform") && strtotime($callObject->setDate) >= strtotime("2015-05-01") && $EvnPL_IsFinish) {
			$query = "
				select count(EU.EvnUsluga_id) as \"Count\"
				from v_EvnUsluga EU
				where EU.EvnUsluga_pid = :EvnUsluga_pid
				  and exists (
					select t1.UslugaComplexAttribute_id
					from
					    UslugaComplexAttribute t1
						inner join UslugaComplexAttributeType t2 on t2.UslugaComplexAttributeType_id = t1.UslugaComplexAttributeType_id
					where t1.UslugaComplex_id = EU.UslugaComplex_id
					  and t2.UslugaComplexAttributeType_SysNick in ('uslcmp')
				  )
				limit 1
			";
			$queryParams = ["EvnUsluga_pid" => $callObject->id];
			$uslCmpCnt = $callObject->getFirstResultFromQuery($query, $queryParams);
			if ($uslCmpCnt === false) {
				throw new Exception("Не удалось определить количество услуг из РК 20", 500);
			}
			if ($uslCmpCnt == 0) {
				throw new Exception("При посещении по поводу неотложной помощи должна быть указана хотя бы одна<br/>услуга из РК 20", 400);
			}
		}
		if ($callObject->getRegionNick() == "kareliya" && in_array($callObject->scenario, [EvnVizitPL_model::SCENARIO_DO_SAVE, EvnVizitPL_model::SCENARIO_SET_ATTRIBUTE])) {
			$query = "
				select
					EVPL.EvnVizitPL_id as \"EvnVizitPL_id\",
					cnt.cnt as \"cnt\"
				from
					v_EvnPL EPL
					inner join v_EvnVizitPL EVPL on EVPL.EvnVizitPL_pid = EPL.EvnPL_id
					inner join v_VizitType VT on VT.VizitType_id = EVPL.VizitType_id and VT.VizitType_Code not in ('11', '23')
					left join lateral (
						select count(EvnVizitPL_id) as cnt
						from v_EvnVizitPL
						where EvnVizitPL_pid = EPL.EvnPL_id
					) as cnt on true
				where EPL.EvnPL_id = :EvnPL_id 
				  and EVPL.EvnVizitPL_id <> coalesce(CAST(:exceptEvnVizitPL_id as bigint), 0)
				  and date_part('year', EVPL.EvnVizitPL_setDate) = 2017
				limit 1
			";
			$queryParams = [
				"EvnPL_id" => $callObject->parent->id,
				"exceptEvnVizitPL_id" => $callObject->id
			];
			$result = $callObject->queryResult($query, $queryParams);
			if (!empty($result["EvnVizitPL_id"]) && $result["cnt"] > 0) {
				throw new Exception("Добавление посещения невозможно, т.к. в рамках текущего ТАП уже есть посещение.", 499);
			}
		}
	}

	/**
	 * @param EvnVizitPL_model $callObject
	 * @throws Exception
	 */
	public static function _checkChangeServiceType(EvnVizitPL_model $callObject)
	{
		$isStom = (13 == $callObject->evnClassId);
		if (in_array($callObject->regionNick, ["perm"]) && in_array($callObject->scenario, [EvnVizitPL_model::SCENARIO_SET_ATTRIBUTE]) && $isStom == false && $callObject->getParam("ignoreCheckB04069333") == false && $callObject->setDT->getTimestamp() < strtotime("01.05.2018")) {
			$query = "
				select t1.EvnUsluga_id as \"EvnUsluga_id\"
				from
					v_EvnUsluga t1
					inner join v_UslugaComplex t2 on t2.UslugaComplex_id = t1.UslugaComplex_id
				where t1.EvnUsluga_pid = :EvnVizitPL_id
				  and t2.UslugaComplex_Code = 'B04.069.333'
				limit 1
			";
			$queryParams = ["EvnVizitPL_id" => $callObject->id];
			$EvnUsluga_id = $callObject->getFirstResultFromQuery($query, $queryParams);
			if (in_array($callObject->serviceTypeSysNick, ["home", "ahome", "neotl"]) && empty($EvnUsluga_id)) {
				$query = "
					select UslugaComplex_Name as \"UslugaComplex_Name\"
					from v_UslugaComplex
					where UslugaComplex_Code = 'B04.069.333'
					  and (UslugaComplex_begDT is null or UslugaComplex_begDT <= :setDate)
					  and (UslugaComplex_endDT is null or UslugaComplex_endDT >= :setDate)
					limit 1
				";
				$queryParams = ["setDate" => $callObject->setDate];
				$UslugaComplex_Name = $callObject->getFirstResultFromQuery($query, $queryParams, null);
				$callObject->setSaveResponse("Alert_Msg", "Добавить в посещение услугу B04.069.333 «" . (!empty($UslugaComplex_Name) ? $UslugaComplex_Name : "Оказание неотложной помощи вне медицинской организации (на дому)") . "»?");
				throw new Exception("YesNo", 131);
			}
		}
	}
	
	/**
	 * @param EvnVizitPL_model $callObject
	 * @throws Exception
	 */
	public static function _checkDiagDispCard(EvnVizitPL_model $callObject)
	{
		if (in_array($callObject->scenario, array([EvnVizitPL_model::SCENARIO_DO_SAVE], [EvnVizitPL_model::SCENARIO_SET_ATTRIBUTE])) && !$callObject->_params['ignoreDiagDispCheck']) {
			// ищем прикрепление
			$query_attach = "
					select
						PersonCard_id as \"PersonCard_id\"
					from
						v_PersonCard
					where
						Lpu_id = :Lpu_id and Person_id = :Person_id
					limit 1
				";
			
			$response_attach = $callObject->getFirstRowFromQuery($query_attach, array(
				'Person_id' => $callObject->Person_id,
				'Lpu_id' => $callObject->Lpu_id
			));
			
			if (!empty($response_attach)) {
				// если прикрепление есть, проверяем диагноз
				$query_diag = "
						select
							DispSickDiag_id as \"DispSickDiag_id\"
						from
							v_DispSickDiag
						where
							Diag_id = :Diag_id
						limit 1
					";
				
				$response_diag = $callObject->getFirstRowFromQuery($query_diag, array(
					'Diag_id' => $callObject->Diag_id
				));
				
				if (!empty($response_diag)) {
					// если диагноз входит в список, проверяем карту диспансерного наблюдения
					$query_disp_card = "
							select
								 PersonDisp_id as \"PersonDisp_id\"
							from
								v_PersonDisp
							where
								Person_id = :Person_id
								and Lpu_id = :Lpu_id
								and CAST(:setDate as date) between PersonDisp_begDate and COALESCE(PersonDisp_endDate, dbo.tzGetDate())
								and Diag_id = :Diag
							limit 1";
					
					$response_disp_card = $callObject->getFirstRowFromQuery($query_disp_card, array(
						'Person_id' => $callObject->Person_id,
						'Lpu_id' => $callObject->Lpu_id,
						'setDate' => $callObject->setDate,
						'Diag' => $callObject->Diag_id
					));
					
					if (empty($response_disp_card)) {
						$diag_code_result = $callObject->getFirstRowFromQuery('select Diag_Code as "Diag_Code" from v_Diag where Diag_id = :Diag_id limit 1', array('Diag_id' => $callObject->Diag_id));
						$diag_code = $diag_code_result['Diag_Code'];
						
						$callObject->setSaveResponse("Alert_Msg", "Пациент с диагнозом $diag_code нуждается в диспансерном наблюдении. Создать карту диспансерного наблюдения?");
						throw new Exception('YesNo', 182);
					}
				}
			}
		}
	}

	/**
	 * @param EvnVizitPL_model $callObject
	 * @throws Exception
	 */
	public static function _checkChangeMedStaffFact(EvnVizitPL_model $callObject)
	{
		if (in_array($callObject->scenario, [EvnVizitPL_model::SCENARIO_DO_SAVE, EvnVizitPL_model::SCENARIO_AUTO_CREATE]) && empty($callObject->MedStaffFact_id)) {
			throw new Exception("Не указано место работы", 400);
		}
		// для ЕКБ При сохранении посещения должны быть следующие контроли:
		// 1) У специальности врача GroupAPP?0.
		if ($callObject->regionNick == "ekb" && in_array($callObject->evnClassId, [11, 36])) {
			// эта проверка из старого метода сохранения посещения полки и осмотра ВОВ
			$query = "
				select MSOG.MedSpecOMSGROUP_APP as \"MedSpecOMSGROUP_APP\"
				from
					v_MedStaffFact MSF
					inner join r66.v_MedSpecOMSGROUP MSOG on MSOG.MedSpecOMS_id = msf.MedSpecOMS_id
				where MSF.MedStaffFact_id = :MedStaffFact_id
				  and coalesce(MSF.WorkData_endDate, :date) >= :date
				  and coalesce(MSOG.MedSpecOMSGROUP_begDate, :date) <= :date
				  and coalesce(MSOG.MedSpecOMSGROUP_endDate, :date) >= :date
				limit 1
			";
			$queryParams = [
				"MedStaffFact_id" => $callObject->MedStaffFact_id,
				"date" => $callObject->setDate
			];
			$result = $callObject->db->query($query, $queryParams);
			if (!is_object($result)) {
				throw new Exception("Ошибка при выполнении запроса к базе данных", 500);
			}
			$pos = $result->result("array");
			if (count($pos) == 0) {
				throw new Exception("Указана некорректная специальность врача", 400);
			}
			if ($callObject->payTypeSysNick == "oms" && $pos[0]["MedSpecOMSGROUP_APP"] == 0) {
				throw new Exception("Специальность выбранного врача не может использоваться в поликлиническом случае", 400);
			}
		}
	}

	/**
	 * @param EvnVizitPL_model $callObject
	 * @return bool
	 * @throws Exception
	 */
	public static function _checkChangeLpuSectionProfileId(EvnVizitPL_model $callObject)
	{
		// Проверка применяется только к Пензе, только к обычным ТАП (не стомат).
		if ($callObject->regionNick !== 'penza' || $callObject->evnClassId == 13) {
			return true;
		}
		// Узнаем код профиля отделения в ЛПУ (больнице), в который хочет попасть пациент (текущий профиль)
		$query = "
			select LpuSectionProfile_Code as \"Code\"
			from v_LpuSectionProfile
			where LpuSectionProfile_id = :LpuSectionProfile_id
			limit 1
		";
		$queryParams = ["LpuSectionProfile_id" => $callObject->LpuSectionProfile_id];
		$LpuSectionProfile_Code = $callObject->getFirstResultFromQuery($query, $queryParams);
		// В ТАП должны быть посещения только по одному профилю. Исключение являются профили с кодом 57 и 97, которые могут быть в одном ТАП (но не с другими)
		$codesAcceptedTogether = [57, 97];
		// Проверка на редактирование единственного посещения, если id редактируемого посещения не пустой, то добавим дополнительное условие в запрос
		$checkIfEdit = empty($callObject->id) ? null : " and VZPL.EvnVizitPL_id != :EvnVizitPL_id";
		if (in_array($LpuSectionProfile_Code, $codesAcceptedTogether)) {
			// Узнаем, были ли посещения профилей отделений, кроме разрешенных вместе 57 и 97, в рамках данного ТАП (EvnVizitPL_pid)
			$query = "
				select LSP.LpuSectionProfile_Code as \"LpuSectionProfile_Code\"
				from
					v_EvnVizitPL VZPL  
					join v_LpuSectionProfile LSP on VZPL.LpuSectionProfile_id = LSP.LpuSectionProfile_id
				where VZPL.EvnVizitPL_pid = :EvnVizitPL_pid
				  and LSP.LpuSectionProfile_Code != '57'
				  and LSP.LpuSectionProfile_Code != '97'
				{$checkIfEdit}
				limit 1
			";
			$queryParams = [
				"EvnVizitPL_pid" => $callObject->pid,
				"EvnVizitPL_id" => $callObject->id
			];
			$result = $callObject->getFirstResultFromQuery($query, $queryParams);
		} else {
			// Узнаем, были ли посещения профилей отделений, отличных от текущего профиля, в рамках данного ТАП (EvnVizitPL_pid)
			$query = "
				select LSP.LpuSectionProfile_Code as \"LpuSectionProfile_Code\"
				from
					v_EvnVizitPL VZPL  
					join v_LpuSectionProfile LSP on VZPL.LpuSectionProfile_id = LSP.LpuSectionProfile_id
				where VZPL.EvnVizitPL_pid = :EvnVizitPL_pid
				  and LSP.LpuSectionProfile_Code != :LpuSectionProfile_Code 
				  {$checkIfEdit}
				limit 1
			";
			$queryParams = [
				"EvnVizitPL_pid" => $callObject->pid,
				"LpuSectionProfile_Code" => $LpuSectionProfile_Code,
				"EvnVizitPL_id" => $callObject->id
			];
			$result = $callObject->getFirstResultFromQuery($query, $queryParams);
		}
		// Если не было посещений, отличных от текущего сохраняемого профиля, то проверка пройдена, иначе ошибка
		if ($result !== false) {
			throw new Exception("В ТАП должны быть посещения только по одному профилю");
		}
		return true;
	}

	/**
	 * @param EvnVizitPL_model $callObject
	 * @throws Exception
	 */
	public static function _checkChangeLpuSection(EvnVizitPL_model $callObject)
	{
		if (in_array($callObject->scenario, [EvnVizitPL_model::SCENARIO_DO_SAVE, EvnVizitPL_model::SCENARIO_AUTO_CREATE]) && empty($callObject->LpuSection_id)) {
			throw new Exception("Не указано отделение", 400);
		}
		//Проверка не закрыто ли отделение
		if ($callObject->regionNick == "ufa" && in_array($callObject->scenario, [EvnVizitPL_model::SCENARIO_DO_SAVE, EvnVizitPL_model::SCENARIO_AUTO_CREATE])) {
			//Проверяем что отделение не закрыто
			$query = "
				with myvars as (
					select tzgetdate() as tzdate
				)
                select 1 as \"count\"
                from v_LpuSection
                where LpuSection_id = :LpuSection_id
                  and (coalesce(LpuSection_disDate, (select tzdate from myvars)) >= (select tzdate from myvars))
                limit 1
			";
			$queryParams = ["LpuSection_id" => $callObject->LpuSection_id];
			$result = $callObject->getFirstResultFromQuery($query, $queryParams);
			if ($result != 1) {
				throw new Exception("Данное отделение закрыто или не найдено в базе данных. Сохранение невозможно.", 400);
			}

			// Проверка разрешения оплаты по ОМС для отделения
			if ($callObject->payTypeSysNick == "oms") {
				$callObject->load->model("LpuStructure_model");
				$response = $callObject->LpuStructure_model->getLpuUnitIsOMS(["LpuSection_id" => $callObject->LpuSection_id]);
				if (!$response[0]["LpuUnit_IsOMS"]) {
					throw new Exception("Данное отделение не работает по ОМС", 400);
				}
			}
		}
	}

	/**
	 * @param EvnVizitPL_model $callObject
	 * @throws Exception
	 */
	public static function _checkPerson(EvnVizitPL_model $callObject)
	{
		if (($callObject->options["polka"]["check_person_birthday"] === true || $callObject->options["polka"]["check_person_birthday"] == "1") && $callObject->person_BirthDay instanceof DateTime) {
			$compare_result = swCompareDates($callObject->person_BirthDay->format("d.m.Y"), $callObject->setDT->format("d.m.Y"));
			// Если дата рождения больше даты посещения...
			if (in_array($compare_result[0], array(-1))) {
				throw new Exception("Дата рождения пациента больше, чем дата поликлинического обслуживания. Исправьте дату посещения", 400);
			}
		}
		// Для Уфы при добавлении посещения полки, стоматки, осмотра ВОВ
		// и включенном режиме полуавтоматической идентификации...
		if ($callObject->regionNick == "ufa" && $callObject->isNewRecord && !empty($callObject->globalOptions["globals"]["enable_semiautomatic_identification"])) {
			// ... производится идентификация застрахованного
			$callObject->load->model("PersonIdentRequest_model");
			$response = $callObject->PersonIdentRequest_model->doPersonIdentOnEvnSave(
				[
					"Server_id" => $callObject->Server_id,
					"Person_id" => $callObject->Person_id,
					"pmUser_id" => $callObject->promedUserId
				],
				$callObject->setDate . " " . $callObject->setTime . ":00",
				$callObject->globalOptions
			);
			if (!empty($response["errorMsg"])) {
				throw new Exception($response["errorMsg"], 400);
			}
		}
	}

	/**
	 * @param EvnVizitPL_model $callObject
	 * @throws Exception
	 */
	public static function _checkChangeDiag(EvnVizitPL_model $callObject)
	{
		// Проверка заполнения поля "Диагноз"
		if ($callObject->_isRequiredDiag() && empty($callObject->Diag_id)) {
			throw new Exception("Поле \"Диагноз\" обязательно для заполнения", 400);
		}

		if ($callObject->getSavedData("diag_id") != null && in_array($callObject->scenario, [EvnVizitPL_model::SCENARIO_DO_SAVE, EvnVizitPL_model::SCENARIO_SET_ATTRIBUTE]) && $callObject->isAttributeChanged("diag_id")) {
			$callObject->ignoreCheckMorbusOnko = $callObject->getParam("ignoreCheckMorbusOnko");
			$callObject->load->library("swMorbus");
			$tmp = swMorbus::onBeforeChangeDiag($callObject);
			if ($tmp !== true && isset($tmp["Alert_Msg"])) {
				$callObject->setSaveResponse("ignoreParam", $tmp["ignoreParam"]);
				$callObject->setSaveResponse("Alert_Msg", $tmp["Alert_Msg"]);
				throw new Exception("YesNo", 289);
			}
			$callObject->load->library("swPersonRegister");
			swPersonRegister::onBeforeChangeDiag($callObject);
		}

		if ($callObject->regionNick == "ufa" && $callObject->payTypeSysNick == "oms" && !empty($callObject->Diag_id) && !empty($callObject->Person_id)) {
			//Проверяем что если пациенту больше 17 лет диагноз должен быть по ОМС
			//Проверяем что у инотера диагноз не из территориального финансирования
			$query = "
				select
					case
						when ((dbo.Age2(VPSA.Person_BirthDay, dbo.tzgetdate()) > 17
							and d.DiagFinance_isOms = 1)
							or (DIAG.Diag_Code ilike 'Z80.%'
							and d.Lpu_id!=:Lpu_id
							and dbo.Age2(VPSA.Person_BirthDay, dbo.tzgetdate()) > 17 ))
						then 1
						else 2
					end as \"Err_DiagOMS\",
					case
						when Terr.KLRgn_id != 2
							and d.DiagFinance_IsAlien = 1
						then 1
						else 2
					end as \"Err_inoDiag\"
				from
					v_PersonState_all VPSA
					left join v_DiagFinance d on d.Diag_id = :Diag_id 
						and d.PersonAgeGroup_id = (case 
							when (dbo.Age2(VPSA.Person_BirthDay, dbo.tzgetdate()) > 17) 
							then 1
							else 2
						end)
					left join v_Diag DIAG on DIAG.Diag_id = :Diag_id
					left join v_Polis VP on VP.Polis_id = VPSA.Polis_id
					left join v_OMSSprTerr Terr on VP.OMSSprTerr_id = Terr.OMSSprTerr_id
				where VPSA.Person_id = :Person_id
			";
			$queryParams = [
				"Diag_id" => $callObject->Diag_id,
				"Person_id" => $callObject->Person_id,
				"Lpu_id" => $callObject->Lpu_id,
			];
			$result = $callObject->getFirstRowFromQuery($query, $queryParams);
			if (!is_array($result)) {
				throw new Exception("Не удалось проверить диагноз. Сохранение невозможно", 400);
			}
			if ($result["Err_DiagOMS"] != 2) {
				throw new Exception("У пациента старше 17 лет диагноз не по ОМС. Сохранение невозможно", 400);
			}
			if ($result["Err_inoDiag"] != 2) {
				throw new Exception("Диагноз у инотера относится к территориальной программе финансирования. Сохранение невозможно.", 400);
			}
		}

		if ($callObject->regionNick == 'perm' && $callObject->evnClassId == 13 && $callObject->getSavedData("Diag_id") != null && in_array($callObject->scenario, [EvnVizitPL_model::SCENARIO_DO_SAVE, EvnVizitPL_model::SCENARIO_SET_ATTRIBUTE]) && $callObject->isAttributeChanged("Diag_id")) {
			// В Перми нет кода посещения, там где он есть его не нужно учитывать
			$isEmptyEvnUslugaList = empty($callObject->evnUslugaList);
			if ($isEmptyEvnUslugaList) {
				throw new Exception("Невозможно изменить диагноз, необходимо удалить услуги");
			}
		}
		$query = "
			select Diag_Code as \"Diag_Code\"
			from v_Diag
			where Diag_id = :Diag_id
			limit 1
		";
		$queryParams = ["Diag_id" => $callObject->Diag_id];
		$resp_diag = $callObject->getFirstResultFromQuery($query, $queryParams);
		if ((($callObject->regionNick == "ufa" || ($callObject->regionNick == "buryatiya" && in_array($callObject->scenario, [EvnVizitPL_model::SCENARIO_SET_ATTRIBUTE]))) && ($resp_diag === false || $resp_diag != "Z03.1")) || ($callObject->regionNick != "ufa" && getRegionNick() != "krym" && ($resp_diag === false || substr($resp_diag, 0, 1) == "C" || substr($resp_diag, 0, 2) == "D0"))) {
			$callObject->setRefactorAttribute("iszno", 1);
			$callObject->setRefactorAttribute("diag_spid", null);
		} else if (($callObject->regionNick == "ufa" || ($callObject->regionNick == "buryatiya" && in_array($callObject->scenario, [EvnVizitPL_model::SCENARIO_SET_ATTRIBUTE]))) && $resp_diag !== false && $resp_diag == "Z03.1") {
			$callObject->setRefactorAttribute("iszno", 2);
		}
	}

	/**
	 * @param EvnVizitPL_model $callObject
	 * @return bool
	 * @throws Exception
	 */
	public static function _checkChangeVizitCode(EvnVizitPL_model $callObject)
	{
		if (false == $callObject->isUseVizitCode) {
			return true;
		}
		// Проверка заполнения поля "Код посещения"
		if (in_array($callObject->regionNick, ["pskov"]) && in_array($callObject->scenario, [EvnVizitPL_model::SCENARIO_DO_SAVE, EvnVizitPL_model::SCENARIO_SET_ATTRIBUTE]) && empty($callObject->UslugaComplex_id)) {
			// Если посещение создается автоматически, то код посещения не проверяем
			throw new Exception("Поле \"Код посещения\" обязательно для заполнения", 400);
		}
		if (in_array($callObject->regionNick, ["buryatiya", "vologda"]) && $callObject->evnClassId != 13 && in_array($callObject->scenario, [EvnVizitPL_model::SCENARIO_DO_SAVE, EvnVizitPL_model::SCENARIO_SET_ATTRIBUTE]) && empty($callObject->UslugaComplex_id)) {
			// Если посещение создается автоматически, то код посещения не проверяем
			throw new Exception("Поле \"Код посещения\" обязательно для заполнения", 400);
		}
		if (in_array($callObject->regionNick, ["buryatiya"]) && $callObject->evnClassId != 13 && in_array($callObject->scenario, [EvnVizitPL_model::SCENARIO_SET_ATTRIBUTE]) && !empty($callObject->UslugaComplex_id) && empty($callObject->parent->Lpu_did)) {
			$callObject->load->model("Person_model", "pmodel");
			$KLRgn_id = $callObject->pmodel->getPersonPolisRegionId([
				"PersonEvn_id" => $callObject->PersonEvn_id,
				"Server_id" => $callObject->Server_id
			]);
			if ($KLRgn_id == getRegionNumber()) {
				$query = "
					select count(UCA.UslugaComplex_id) as \"Count\"
					from
						v_UslugaComplexAttribute UCA
						inner join v_UslugaComplexAttributeType UCAT on UCAT.UslugaComplexAttributeType_id = UCA.UslugaComplexAttributeType_id
					where UCAT.UslugaComplexAttributeType_SysNick ilike 'mur'
					  and UCA.UslugaComplex_id = :UslugaComplex_id
					limit 1
				";
				$queryParams = ["UslugaComplex_id" => $callObject->UslugaComplex_id];
				$count = $callObject->getFirstResultFromQuery($query, $queryParams);
				if ($count === false) {
					throw new Exception("Ошибка при проверке атрибута МУР", 500);
				}
				if ($count > 0) {
					throw new Exception("В посещении указана услуга МУРа. Необходимо указать информацию о медицинской организации, выдавшей направление", 400);
				}
			}
		}
		if (in_array($callObject->regionNick, ["perm"]) && in_array($callObject->scenario, [EvnVizitPL_model::SCENARIO_DO_SAVE, EvnVizitPL_model::SCENARIO_SET_ATTRIBUTE]) && empty($callObject->UslugaComplex_id) && strtotime($callObject->setDate) >= strtotime("01.11.2015") && in_array($callObject->payTypeSysNick, ["oms"])) {
			// Если посещение создается автоматически, то код посещения не проверяем
			throw new Exception("Поле \"Код посещения\" обязательно для заполнения", 400);
		}
		/*if (in_array($callObject->regionNick, ["perm"]) && !empty($callObject->UslugaComplex_id)) {
			$query = "
				select FMS.MedSpec_id as \"MedSpec_id\"
				from
					v_MedStaffFact MSF
					left join v_MedSpecOms MSO on MSO.MedSpecOms_id = MSF.MedSpecOms_id
					left join fed.v_MedSpec FMS on FMS.MedSpec_id = MSO.MedSpec_id
				where MSF.MedStaffFact_id = :MedStaffFact_id
				limit 1
			";
			$queryParams = ["MedStaffFact_id" => $callObject->MedStaffFact_id];
			$FedMedSpec_id = $callObject->getFirstResultFromQuery($query, $queryParams, true);
			if ($FedMedSpec_id === false) {
				throw new Exception("Ошибка при получении специальности врача");
			}
			$setDT = $callObject->setDT;
			$lastEvnVizit = $callObject->parent->lastEvnVizit;
			$lastEvnUsluga = $callObject->parent->lastEvnUsluga;
			if (is_array($lastEvnVizit) && $lastEvnVizit["EvnVizitPL_id"] != $callObject->id && $lastEvnVizit["EvnVizitPL_setDT"] > $setDT) {
				$setDT = $lastEvnVizit["EvnVizitPL_setDT"];
			}
			if (is_array($lastEvnUsluga) && $lastEvnUsluga["EvnUsluga_setDT"] > $setDT) {
				$setDT = $lastEvnUsluga["EvnUsluga_setDT"];
			}
			// Проверяем наличие объёма для кода посещения.
			$callObject->load->model("TariffVolumes_model");
			$resp = $callObject->TariffVolumes_model->checkVizitCodeHasVolume([
				"UslugaComplex_id" => $callObject->UslugaComplex_id,
				"Lpu_id" => $callObject->Lpu_id,
				"LpuSectionProfile_id" => $callObject->LpuSectionProfile_id,
				"FedMedSpec_id" => $FedMedSpec_id,
				"VizitClass_id" => $callObject->VizitClass_id,
				"VizitType_id" => $callObject->VizitType_id,
				"TreatmentClass_id" => $callObject->TreatmentClass_id,
				"isPrimaryVizit" => isset($callObject->IsPrimaryVizit) ? $callObject->IsPrimaryVizit : null,
				"UslugaComplex_Date" => $setDT->format("Y-m-d"),
				"EvnClass_SysNick" => $callObject->evnClassSysNick,
				"PayType_SysNick" => $callObject->payTypeSysNick
			]);
			if (!$callObject->isSuccessful($resp)) {
				throw new Exception($resp[0]["Error_Msg"], $resp[0]["Error_Code"]);
			}
		}*/
		if ($callObject->regionNick == "ekb" && in_array($callObject->scenario, [EvnVizitPL_model::SCENARIO_DO_SAVE, EvnVizitPL_model::SCENARIO_SET_ATTRIBUTE]) && $callObject->evnClassId == 11 && empty($callObject->UslugaComplex_id) && empty($callObject->Mes_id) && $callObject->payTypeSysNick != "bud" && $callObject->payTypeSysNick != "dms") {
			// Если посещение создается автоматически, то не проверяем
			throw new Exception("Обязательно для заполнения одно из полей \"МЭС\" или \"Код посещения\"", 400);
		}
		if ($callObject->regionNick == 'ekb'
			&& in_array($callObject->scenario, array(EvnVizitPL_model::SCENARIO_DO_SAVE))
			&& $callObject->evnClassId == 13
			&& (empty($callObject->UslugaComplex_id) || empty($callObject->Mes_id))
			&& $callObject->payTypeSysNick != 'bud'
			&& $callObject->payTypeSysNick != 'dms'
		) { // Если посещение создается автоматически, то не проверяем
			throw new Exception('Обязательны для заполнения поля \"МЭС\" и \"Код посещения\"', 400);
		}

		if ($callObject->regionNick == "ekb" && in_array($callObject->scenario, [EvnVizitPL_model::SCENARIO_SET_ATTRIBUTE]) && $callObject->evnClassId == 13 && empty($callObject->UslugaComplex_id) && $callObject->payTypeSysNick != "bud" && $callObject->payTypeSysNick != "dms") {
			// Если посещение создается автоматически, то не проверяем
			throw new Exception("Обязательны для заполнения поля \"МЭС\" и \"Код посещения\"", 400);
		}
		if (in_array($callObject->regionNick, ["ekb", "perm"]) && $callObject->isAttributeChanged("UslugaComplex_id") && !empty($callObject->UslugaComplex_id) && !$callObject->isNewRecord) {
			// проверям что код посещения не занесен как отдельная услуга
			$isFound = false;
			foreach ($callObject->evnUslugaList as $row) {
				if ($row["UslugaComplex_id"] == $callObject->UslugaComplex_id && 1 == $row["EvnUsluga_IsVizitCode"]) {
					$isFound = true;
					break;
				}
			}
			if ($isFound) {
				throw new Exception("Код посещения присутствует в списке услуг, сохранение невозможно", 400);
			}
		}
		if ($callObject->regionNick == "perm" && in_array($callObject->scenario, [EvnVizitPL_model::SCENARIO_DO_SAVE, EvnVizitPL_model::SCENARIO_SET_ATTRIBUTE]) && !empty($callObject->UslugaComplex_id) && $callObject->payTypeSysNick == "oms") {
			$query = "
				with mv as (
					select
						dbo.Age2(:Person_BirthDay, :EvnVizitPL_setDate) as Person_Age,
						date_part('day', (:EvnVizitPL_setDate::timestamp - :Person_BirthDay::timestamp)) as Person_AgeDays
				)
				select count(UCT.UslugaComplexTariff_id) as \"Count\"
				from v_UslugaComplexTariff UCT
				where UCT.UslugaComplex_id = :UslugaComplex_id
				  and UCT.PayType_id = :PayType_id
				  and (
				    ((select person_age from mv) >= 18 and UCT.MesAgeGroup_id = 1) or
				    ((select person_age from mv) < 18 and UCT.MesAgeGroup_id = 2) or
				    ((select Person_AgeDays from mv) > 28 and UCT.MesAgeGroup_id = 3) or
				    ((select Person_AgeDays from mv) <= 28 and UCT.MesAgeGroup_id = 4) or
				    ((select person_age from mv) < 18 and UCT.MesAgeGroup_id = 5) or
				    ((select person_age from mv) >= 18 and UCT.MesAgeGroup_id = 6) or
				    ((select person_age from mv) < 8 and UCT.MesAgeGroup_id = 7) or
				    ((select person_age from mv) >= 8 and UCT.MesAgeGroup_id = 8) or
				    ((select Person_AgeDays from mv) <= 90 and UCT.MesAgeGroup_id = 9) or
				    (UCT.MesAgeGroup_id is null)
					)
				  and UCT.UslugaComplexTariff_begDate <= :EvnVizitPL_setDate
				  and (UCT.UslugaComplexTariff_endDate > :EvnVizitPL_setDate or UCT.UslugaComplexTariff_endDate is null)
				limit 1
			";
			$queryParams = [
				"Person_BirthDay" => $callObject->person_BirthDay,
				"EvnVizitPL_setDate" => $callObject->setDate,
				"UslugaComplex_id" => $callObject->UslugaComplex_id,
				"PayType_id" => $callObject->PayType_id
			];
			$tariff_count = $callObject->getFirstResultFromQuery($query, $queryParams);
			if ($tariff_count === false) {
				throw new Exception("Ошибка при проверке наличия тарифов.", 500);
			}
			if ($tariff_count == 0) {
				$warningFrom = $callObject->getParam("isEmk") ? "ЭМК" : "Посещение";
				$callObject->addWarningMsg($warningFrom . ": На данную услугу нет тарифа!");
			}
		}
		if ($callObject->regionNick == "ufa" && in_array($callObject->scenario, [EvnVizitPL_model::SCENARIO_DO_SAVE, EvnVizitPL_model::SCENARIO_SET_ATTRIBUTE]) && $callObject->isAttributeChanged("UslugaComplex_id")) {
			// Если ОМС (или ДД), то код посещения должен быть обязательным. Иначе - нет.
			if (in_array($callObject->payTypeSysNick, array("oms"/*, "dopdisp"*/)) && empty($callObject->UslugaComplex_id)) {
				throw new Exception("Поле \"Код посещения\" обязательно для заполнения", 400);
			}
			//Проверка соответствия оказанной услуге полу пациента
			if (!empty($callObject->UslugaComplex_id) && !empty($callObject->Person_id)) {
				$query = "
					select
						case
                        	when VPSA.Sex_id = 1 and (
                            	select left(UslugaComplex_code,3) as Usluga
                                from v_UslugaComplex
                                where UslugaComplex_id = :UslugaComplex_id
                            ) in ('522', '622')
							then 1
							else 2
                        end as \"Err_SexUsluga\"
                    from v_PersonState_all VPSA
                    where VPSA.Person_id = :Person_id
				";
				$queryParams = [
					"Person_id" => $callObject->Person_id,
					"UslugaComplex_id" => $callObject->UslugaComplex_id
				];
				$result = $callObject->getFirstResultFromQuery($query, $queryParams);
				if (empty($result)) {
					throw new Exception("Не удалось проверить соответствие оказанной услуги полу пациента. Сохранение невозможно.", 400);
				}
				if ($result == 1) {
					throw new Exception("Оказываемая услуга не соответствует полу пациента. Сохранение невозможно.", 400);
				}
			}
			// [2013-01-29 16:40]
			// Проверка кодов посещений и группы диагнозов
			// https://redmine.swan.perm.ru/issues/15258
			if ($callObject->UslugaComplex_id > 0) {
				if (!isset($callObject->parent)) {
					throw new Exception("Не удалось прочитать ТАП. Сохранение невозможно.", 500);
				}
				$isFinish = (2 == $callObject->parent->IsFinish);
				$vizitCodePart = EvnVizitPL_model::vizitCodePart($callObject->vizitCode);
				//Проверка соответствия результата коду посещения
				if ($isFinish && in_array($vizitCodePart, EvnVizitPL_model::$morbusMultyVizitCodePartList) && !empty($callObject->parent->leaveTypeCode) && !in_array($callObject->parent->leaveTypeCode, ["301", "313", "305", "306", "311", "307", "309"])) {
					throw new Exception("Результат лечения не соответствует коду посещения. Сохранение невозможно.", 400);
				}
				//Проверки по кодам других посещений
				$otherVizitCnt = 0;
				$diagIdList = [];
				foreach ($callObject->parent->evnVizitList as $id => $row) {
					if (isset($row["Diag_id"])) {
						$diagIdList[] = $row["Diag_id"];
					}
					if ($callObject->id == $id) {
						continue;
					}
					$otherVizitCnt++;
					if (empty($row["UslugaComplex_Code"])) {
						continue;
					}
					// Если сохраняемое посещение профилактическое или однократное посещение по заболеванию и в рамках ТАП имеются какие-либо другие коды посещений...
					if (in_array($vizitCodePart, EvnVizitPL_model::oneVizitCodePartList())) {
						$msg = "профилактического/консультативного посещения";
						switch ($vizitCodePart) {
							case "871":
								$msg = "однократного посещения по заболеванию";
								break;
							case "824":
							case "825":
								$msg = "посещения по неотложной помощи";
								break;
						}
						throw new Exception("Сохранение {$msg} невозможно, т.к. в рамках текущего ТАП имеются другие посещения", 400);
					}
					$vizitCodePartAlt = EvnVizitPL_model::vizitCodePart($row['UslugaComplex_Code']);
					// Если в рамках текущего ТАП имеется профилактическое посещение или однократное посещение по заболеванию...
					if (in_array($vizitCodePartAlt, EvnVizitPL_model::oneVizitCodePartList())) {
						$msg = "профилактического посещения";
						switch ($vizitCodePartAlt) {
							case "871":
								$msg = "однократного посещения по заболеванию";
								break;
							case "824":
							case "825":
								$msg = "посещения по неотложной помощи";
								break;
						}
						throw new Exception("Сохранение посещения невозможно, т.к. в рамках текущего ТАП имеется посещение с кодом {$msg}", 400);
					}
					// Если сохраняемое посещение по заболеванию и имеется посещения не по заболеванию...
					if (in_array($vizitCodePart, EvnVizitPL_model::$morbusMultyVizitCodePartList) && !in_array($vizitCodePartAlt, EvnVizitPL_model::$morbusMultyVizitCodePartList)) {
						// ... не сохранять, выдать ошибку
						throw new Exception("Сохранение посещения с кодом по заболеванию невозможно, т.к. в рамках текущего ТАП имеются посещения не по заболеванию", 400);
					} else if (!in_array($vizitCodePart, EvnVizitPL_model::$morbusMultyVizitCodePartList) && in_array($vizitCodePartAlt, EvnVizitPL_model::$morbusMultyVizitCodePartList)) {
						// Если сохраняемое посещение любое, кроме посещения по заболеванию...
						// ... не сохранять, выдать ошибку
						throw new Exception("Сохранение посещения невозможно, т.к. в рамках текущего ТАП допускаются только посещения по заболеванию", 400);
					}
				}
				// добавил проверку по #39924
				if (!$callObject->isNewRecord && $isFinish && in_array($vizitCodePart, EvnVizitPL_model::$morbusMultyVizitCodePartList) && empty($otherVizitCnt)) {
					throw new Exception("Сохранение посещения по заболеванию в закрытом ТАП с одним посещением невозможно.", 400);
				}
				// 3) При сохранении посещения по заболеванию требуется проверить, что группа диагнозов (до точки) одинаковая для всех посещений
				if (in_array($vizitCodePart, EvnVizitPL_model::$morbusMultyVizitCodePartList) && count($diagIdList) > 0) {
					$diagIdList = implode(",", $diagIdList);
					$query = "
						select distinct
							pd.Diag_Code as \"Diag_Code\"
						from
							v_Diag d
							inner join v_Diag pd on pd.Diag_id = d.Diag_pid
						where d.DiagLevel_id = 4
						  and d.Diag_id in ({$diagIdList})
					";
					$result = $callObject->db->query($query);
					if (!is_object($result)) {
						throw new Exception("Ошибка при выполнении запроса к базе данных (получение кодов посещений в рамках текущего ТАП)", 500);
					}
					$response = $result->result("array");
					if (is_array($response) && count($response) > 1) {
						throw new Exception("В одном документе случая по заболеванию может быть только одна группа диагнозов. Измените диагнозы одного или нескольких посещений", 400);
					}
				}
			}
		}
		return true;
	}
}