<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PacketPrescr_model - модель для работы с пакетами назначений
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Common
 * @access			public
 * @copyright		Copyright (c) 2013 Swan Ltd.
 * @author			Dmitry Vlasenko
 * @version			07.05.2018
 */
class PacketPrescr_model extends swPgModel {
	/**
	 * PacketPrescr_model constructor
	 */
	function __construct()
	{
		parent::__construct();
	}

	/**
	 * Создание нового пакета назначений
	 */
	function createPacketPrescr($data)
	{
		// 0. начитываем данные о случае
		// todo: тут же по идее можно проверить а есть ли вообще назначения в случае, а то может и пакет не из чего создавать
		$resp_evn = $this->queryResult("
			select
				Evn_id as \"Evn_id\"
			from
				v_Evn
			where
				Evn_id = :Evn_id
		", array(
			'Evn_id' => $data['Evn_id']
		));

		if (empty($resp_evn[0]['Evn_id'])) {
			return array('Error_Msg' => 'Ошибка получения данных о случае');
		}

		$this->beginTransaction();

		// 1. сохраняем сам пакет
		$resp_packet = $this->queryResult("
			select
				PacketPrescr_id as \"PacketPrescr_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from p_PacketPrescr_ins(
				PacketPrescr_Name := :PacketPrescr_Name,
				PacketPrescr_Descr := :PacketPrescr_Descr,
				PacketPrescrVision_id := :PacketPrescrVision_id,
				PersonAgeGroup_id := :PersonAgeGroup_id,
				Sex_id := :Sex_id,
				MedPersonal_id := :MedPersonal_id,
				pmUser_id := :pmUser_id
			)
		", array(
			'PacketPrescr_Name' => $data['PacketPrescr_Name'],
			'PacketPrescr_Descr' => $data['PacketPrescr_Descr'],
			'PacketPrescrVision_id' => $data['PacketPrescrVision_id'],
			'PersonAgeGroup_id' => (!empty($data['PersonAgeGroup_id']))?$data['PersonAgeGroup_id']:null,
			'Sex_id' => (!empty($data['Sex_id']))?$data['Sex_id']:null,
			'pmUser_id' => $data['pmUser_id'],
			'MedPersonal_id' => $data['MedPersonal_id']
		));
		if (empty($resp_packet[0]['PacketPrescr_id'])) {
			$this->rollbackTransaction();
			return array('Error_Msg' => 'Ошибка сохранения пакета назначений');
		}

		// 2. сохраняем связь пакета с Lpu
		$resp_lpu = $this->queryResult("
			select
				PacketPrescrLpu_id as \"PacketPrescrLpu_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from p_PacketPrescrLpu_ins(
				PacketPrescr_id := :PacketPrescr_id,
				Lpu_id := :Lpu_id,
				LpuSection_id := :LpuSection_id,
				LpuBuilding_id := :LpuBuilding_id,
				pmUser_id := :pmUser_id
			)
		", array(
			'PacketPrescr_id' => $resp_packet[0]['PacketPrescr_id'],
			'Lpu_id' => $data['Lpu_id'],
			'LpuSection_id' => (!empty($data['session']['CurLpuSection_id']))?$data['session']['CurLpuSection_id']:null,
			'LpuBuilding_id' => (!empty($data['session']['CurARM']) && !empty($data['session']['CurARM']['LpuBuilding_id']))?$data['session']['CurARM']['LpuBuilding_id']:null,
			'pmUser_id' => $data['pmUser_id']
		));
		if (empty($resp_lpu[0]['PacketPrescrLpu_id'])) {
			$this->rollbackTransaction();
			return array('Error_Msg' => 'Ошибка сохранения пакета назначений');
		}

		// 3. Сохраняем связь пакета с диагнозом
		if(!empty($data['Diag_id'])) {
			$diags = explode(",", $data['Diag_id']);
			foreach ($diags as $diag) {
				if ( !empty($diag) && intval($diag)>0 ) { // Проверка на отсутствие диагноза в поле
					$resp_ppd = $this->queryResult("
						select
							PacketPrescrDiag_id as \"PacketPrescrDiag_id\",
							Error_Code as \"Error_Code\",
							Error_Message as \"Error_Msg\"
						from p_PacketPrescrDiag_ins(
							PacketPrescr_id := :PacketPrescr_id,
							Diag_id := :Diag_id,
							pmUser_id := :pmUser_id
						)
						", array(
							'PacketPrescr_id' => $resp_packet[0]['PacketPrescr_id'],
							'Diag_id' => $diag,
							'pmUser_id' => $data['pmUser_id']
					));

					if (empty($resp_ppd[0]['PacketPrescrDiag_id'])) {
						$this->rollbackTransaction();
						return array('Error_Msg' => 'Ошибка сохранения диагноза пакета назначений');
					}
				}
			}
		}

		// Для каждого типа назначений (если хотя бы одно назначения с таким типом существует)
		// создаем свой PacketPrescrList_id (список, объединяющий назначения по типу в пакете)
		$arrPrescriptionTypeIDs = array();

		// 4. начитываем назначения из случая и сохраняем их в пакет
		// СОХРАНЕНИЕ В ПАКЕТ УСЛУГ (лаборатория + диагностика)
		$resp_uslnaz = $this->queryResult("
			select
				epld.UslugaComplex_id as \"UslugaComplex_id\",
				epld.StudyTarget_id as \"StudyTarget_id\",
				'LabDiag' as \"PrescriptionType_Code\",
				epld.MedService_id as \"MedService_id\"
			from
				v_EvnPrescrLabDiag epld
			where
				epld.EvnPrescrLabDiag_pid = :Evn_id

			union all

			select
				epfdu.UslugaComplex_id as \"UslugaComplex_id\",
				epfd.StudyTarget_id as \"StudyTarget_id\",
				'FuncDiag' as \"PrescriptionType_Code\",
				epfd.MedService_id as \"MedService_id\" 
			from
				v_EvnPrescrFuncDiag epfd
				inner join v_EvnPrescrFuncDiagUsluga epfdu on epfdu.EvnPrescrFuncDiag_id = epfd.EvnPrescrFuncDiag_id
			where
				epfd.EvnPrescrFuncDiag_pid = :Evn_id
		", array(
			'Evn_id' => $data['Evn_id']
		));

		if(!empty($resp_uslnaz)) {
			foreach ($resp_uslnaz as $one_uslnaz) {
				$typeCode = $one_uslnaz['PrescriptionType_Code'];
				if (empty($arrPrescriptionTypeIDs[$typeCode]))
					$arrPrescriptionTypeIDs[$typeCode] = $this->createPacketPrescrList($resp_packet[0]['PacketPrescr_id'], $typeCode, $data['pmUser_id']);
				if (empty($arrPrescriptionTypeIDs[$typeCode])) {

					$this->rollbackTransaction();
					return array('Error_Msg' => 'Ошибка сохранения пакета услуг');
				}
				if(empty($data['PacketPrescr_SaveLocation']))
					$one_uslnaz['MedService_id'] = null;

				$one_uslnaz['pmUser_id'] = $data['pmUser_id'];
				$one_uslnaz['PacketPrescr_id'] = $resp_packet[0]['PacketPrescr_id'];
				$resp_ppu = $this->createPacketPrescrUsl($one_uslnaz,$arrPrescriptionTypeIDs[$typeCode]);

				if (empty($resp_ppu[0]['PacketPrescrUsluga_id'])) {
					$this->rollbackTransaction();
					return array('Error_Msg' => 'Ошибка сохранения услуги в пакете назначений');
				}
			}
		}


		//СОХРАНЕНИЕ В ПАКЕТ РЕЖИМЫ
		$resp_regime = $this->queryResult("
			select
				coalesce(Regime.PrescriptionRegimeType_id, 0) as \"PrescriptionRegimeType_id\"
                		,COUNT(EvnPrescr_id) as \"PacketPrescrRegime_Duration\"
			from v_EvnPrescr EP
				inner join v_EvnPrescrRegime Regime on Regime.EvnPrescrRegime_pid = EP.EvnPrescr_id
			where
				EP.EvnPrescr_pid  = :EvnPrescr_pid
				and EP.PrescriptionType_id = 1
				and Regime.PrescriptionStatusType_id != 3
			GROUP BY
  				PrescriptionRegimeType_id
		", array(
			'EvnPrescr_pid' => $data['Evn_id']
		));
		if(!empty($resp_regime)) {
			// Для режимов (если имеются в случае лечения) создаем лист назначений
			$arrPrescriptionTypeIDs['Regime'] = $this->createPacketPrescrList($resp_packet[0]['PacketPrescr_id'], 'Regime', $data['pmUser_id']);
			if (empty($arrPrescriptionTypeIDs['Regime'])) {
				$this->rollbackTransaction();
				return array('Error_Msg' => 'Ошибка сохранения пакета услуг');
			}
			foreach ($resp_regime as $one_regime) {
				$one_regime['pmUser_id'] = $data['pmUser_id'];
				$one_regime['PacketPrescr_id'] = $data['PacketPrescr_id'];
				$resp_oneRegime = $this->createPacketPrescrRegime($one_regime,$arrPrescriptionTypeIDs['Regime']);
				if (empty($resp_oneRegime[0]['PacketPrescrRegime_id'])) {
					$this->rollbackTransaction();
					return array('Error_Msg' => 'Ошибка сохранения режима в пакете назначений');
				}
			}
		}


		//СОХРАНЕНИЕ В ПАКЕТ ДИЕТ
		$resp_diet = $this->queryResult("
			SELECT
			 Diet.PrescriptionDietType_id as \"PrescriptionDietType_id\",
			 COUNT(EvnPrescr_id) as \"PacketPrescrDiet_Duration\"
			FROM
			  v_EvnPrescr EP
			  INNER JOIN v_EvnPrescrDiet Diet ON Diet.EvnPrescrDiet_pid = EP.EvnPrescr_id
			WHERE
			  EP.EvnPrescr_pid = :EvnPrescr_pid AND
			  EP.PrescriptionType_id = 2 AND
			  Diet.PrescriptionStatusType_id != 3
			GROUP BY
			  PrescriptionDietType_id
		", array(
			'EvnPrescr_pid' => $data['Evn_id']
		));
		if(!empty($resp_diet)) {
			// Для диет (если имеются в случае лечения) создаем лист назначений
			$arrPrescriptionTypeIDs['Diet'] = $this->createPacketPrescrList($resp_packet[0]['PacketPrescr_id'], 'Diet', $data['pmUser_id']);
			if (empty($arrPrescriptionTypeIDs['Diet'])) {
				$this->rollbackTransaction();
				return array('Error_Msg' => 'Ошибка сохранения пакета услуг');
			}
			foreach ($resp_diet as $one_diet) {
				$one_diet['pmUser_id'] = $data['pmUser_id'];
				$one_diet['PacketPrescr_id'] = $data['PacketPrescr_id'];
				$resp_oneDiet = $this->createPacketPrescrDiet($one_diet,$arrPrescriptionTypeIDs['Diet']);
				if (empty($resp_oneDiet[0]['PacketPrescrDiet_id'])) {
					$this->rollbackTransaction();
					return array('Error_Msg' => 'Ошибка сохранения лекарства в пакете назначений');
				}

			}
		}


		// СОХРАНЕНИЕ В ПАКЕТ ПРОЦЕДУР
		$resp_proc = $this->queryResult("
			select
				 EP.EvnPrescr_id as \"EvnPrescr_id\"
				,EPPR.EvnCourse_id as \"EvnCourse_id\"
				,EP.EvnPrescr_pid as \"EvnPrescr_pid\"
				,EP.EvnPrescr_rid as \"EvnPrescr_rid\"
				,CUC.UslugaComplex_id as \"CourseUslugaComplex_id\"
				,UC.UslugaComplex_id  as \"UslugaComplex_id\"
				,EP.StudyTarget_id as \"StudyTarget_id\"
				,ECPR.EvnCourseProc_Count as \"PacketPrescrUsluga_Count\"
				,coalesce(ECPR.EvnCourseProc_MaxCountDay, ECPR.EvnCourseProc_MinCountDay, null)  as \"PacketPrescrUsluga_DailyIterCount\"
				,coalesce(ECPR.EvnCourseProc_Duration, 0) as \"PacketPrescrUsluga_Duration\"
				,coalesce(ECPR.EvnCourseProc_ContReception, 0) as \"PacketPrescrUsluga_RepeatNonstop\"
				,coalesce(ECPR.EvnCourseProc_Interval, 0) as \"PacketPrescrUsluga_Break\"
				,ECPR.DurationType_id as \"DurationType_did\"
				,ECPR.DurationType_recid as \"DurationType_rid\"
				,ECPR.DurationType_intid as \"DurationType_bid\"
				,EPPR.MedService_id as \"MedService_id\"

			from v_EvnPrescr EP
				inner join v_EvnPrescrProc EPPR on EPPR.EvnPrescrProc_id = EP.EvnPrescr_id
				left join v_EvnCourseProc ECPR on ECPR.EvnCourseProc_id = EPPR.EvnCourse_id
				left join v_UslugaComplex UC on UC.UslugaComplex_id = EPPR.UslugaComplex_id
				left join v_UslugaComplex CUC on CUC.UslugaComplex_id = ECPR.UslugaComplex_id
			where
				EP.EvnPrescr_pid  = :EvnPrescr_pid
				and EP.PrescriptionType_id = 6
				and EP.PrescriptionStatusType_id != 3
			order by
				EPPR.EvnCourse_id,
				EP.EvnPrescr_setDT
		", array(
			'EvnPrescr_pid' => $data['Evn_id']
		));
		$procUslugaIds = array();
		if(!empty($resp_proc)) {
			// Для процедур и манипуляций (если имеются в случае лечения) создаем лист назначений
			$arrPrescriptionTypeIDs['Proc'] = $this->createPacketPrescrList($resp_packet[0]['PacketPrescr_id'], 'Proc', $data['pmUser_id']);
			if (empty($arrPrescriptionTypeIDs['Proc'])) {
				$this->rollbackTransaction();
				return array('Error_Msg' => 'Ошибка сохранения пакета услуг');
			}
			foreach ($resp_proc as $one_proc) {
				if (!in_array($one_proc['UslugaComplex_id'], $procUslugaIds)) {
					$procUslugaIds[] = $one_proc['UslugaComplex_id'];
					if(empty($data['PacketPrescr_SaveLocation']))
						$one_proc['MedService_id'] = null;
					$one_proc['PacketPrescr_id'] = $data['PacketPrescr_id'];
					$one_proc['pmUser_id'] = $data['pmUser_id'];
					$resp_oneProc = $this->createPacketPrescrProc($one_proc,$arrPrescriptionTypeIDs['Proc']);

					if (empty($resp_oneProc[0]['PacketPrescrUsluga_id'])) {
						$this->rollbackTransaction();
						return array('Error_Msg' => 'Ошибка сохранения лекарства в пакете назначений');
					}
				}
			}
		}



		// СОХРАНЕНИЕ В ПАКЕТ ЛЕКАРСТВЕННЫХ НАЗНАЧЕНИЙ
		$resp_treat = $this->queryResult("
			with
			ep_distinct as (
				select
					EvnCourse_id,
					min(EvnPrescr_id) AS EvnPrescr_id
				from v_EvnPrescr
				where
					EvnPrescr_pid = :EvnPrescr_pid
					and PrescriptionType_id = 5
					and PrescriptionStatusType_id != 3
				group by EvnCourse_id
			),
			prescr as (
				select
					 EP.EvnPrescr_id
					,EP.EvnPrescr_pid
					,EP.EvnPrescr_rid
					,to_char(EP.EvnPrescr_setDT, 'dd.mm.yyyy') as EvnPrescr_setDate
					,null as EvnPrescr_setTime
					,coalesce(EP.EvnPrescr_IsExec,1) as EvnPrescr_IsExec
					,case when 2 = EP.EvnPrescr_IsExec
						then to_char(EP.EvnPrescr_updDT, 'dd.mm.yyyy HH24:MI:SS')
						else null
					end as EvnPrescr_execDT
					,1 as EvnPrescr_IsDir
					,EP.PrescriptionStatusType_id
					,EP.PrescriptionType_id
					,EP.PrescriptionType_id as PrescriptionType_Code
					,coalesce(EP.EvnPrescr_IsCito,1) as EvnPrescr_IsCito
					,coalesce(EP.EvnPrescr_Descr,'') as EvnPrescr_Descr
					,EP.EvnPrescr_setDT
					,EP.EvnPrescr_updDT
				from v_EvnPrescr EP
				where
					EP.EvnPrescr_pid  = :EvnPrescr_pid
					and EP.PrescriptionType_id = 5
					and EP.PrescriptionStatusType_id != 3
					and EP.EvnPrescr_id in (
						select EvnPrescr_id from ep_distinct
					)
			)

			select
                -- для заполнения PacketPrescrTreat
                ECT.EvnCourseTreat_Duration as \"PacketPrescrTreat_Duration\"
                ,ECT.EvnCourseTreat_MinCountDay as \"PacketPrescrTreat_MinCountDay\"
                ,ECT.EvnCourseTreat_MaxCountDay as \"PacketPrescrTreat_MaxCountDay\"
                ,ECT.EvnCourseTreat_ContReception as \"PacketPrescrTreat_ContReception\"
                ,ECT.EvnCourseTreat_Interval as \"PacketPrescrTreat_Interval\"
                ,ECT.EvnCourseTreat_MaxCountDay as \"PacketPrescrTreat_PrescrCount\"
                ,ECT.DurationType_id as \"DurationType_id\"
                ,ECT.DurationType_recid as \"DurationType_recid\"
                ,ECT.DurationType_intid as \"DurationType_intid\"

                -- для заполнения PacketPrescrTreatMethod
                ,ECT.PrescriptionIntroType_id as \"PrescriptionIntroType_id\"
                ,ECT.PrescriptionTreatType_id as \"PrescriptionTreatType_id\"
                ,ECT.PerformanceType_id as \"PerformanceType_id\"
                ,ECT.PrescriptionTimeType_id as \"PrescriptionTimeType_id\"
                ,ECT.PrescriptionTreatOrderType_id as \"PrescriptionTreatOrderType_id\"
                ,ECT.EvnCourseTreat_IsPrescrInfusion as \"PacketPrescrTreatMethod_IsPrescrInfusion\"

                -- для заполнения PacketPrescrTreatDrug
                ,ec_drug.Drug_id as \"Drug_id\"
                ,null as \"ACTMATTERS_ID\"
                ,coalesce(ec_drug.DrugComplexMnn_id,Drug.DrugComplexMnn_id) as \"DrugComplexMnn_id\"
                ,coalesce(EPTD.EvnPrescrTreatDrug_Kolvo, ec_drug.EvnCourseTreatDrug_Kolvo) as \"PacketPrescrTreatDrug_Kolvo\"
                ,coalesce(EPTD.EvnPrescrTreatDrug_KolvoEd, ec_drug.EvnCourseTreatDrug_KolvoEd) as \"PacketPrescrTreatDrug_KolvoEd\"
                ,ec_drug.GoodsUnit_id as \"GoodsUnit_id\"
                ,ec_drug.GoodsUnit_sid as \"GoodsUnit_sid\"
                ,ec_drug.EvnCourseTreatDrug_MinDoseDay as \"PacketPrescrTreatDrug_MinDoseDay\"
                ,ec_drug.EvnCourseTreatDrug_MaxDoseDay as \"PacketPrescrTreatDrug_MaxDoseDay\"
                ,ec_drug.EvnCourseTreatDrug_PrescrDose as \"PacketPrescrTreatDrug_PrescrDose\"

                -- для распределения между курсами
                ,EP.EvnPrescr_id as \"EvnPrescr_id\"
				,EP.EvnPrescr_pid as \"EvnPrescr_pid\"
				,EP.EvnPrescr_rid as \"EvnPrescr_rid\"

			from prescr EP
				inner join v_EvnPrescrTreat EPT on EPT.EvnPrescrTreat_id = EP.EvnPrescr_id
				inner join v_EvnCourseTreat ECT on ECT.EvnCourseTreat_id = EPT.EvnCourse_id
				left join v_EvnCourseTreatDrug ec_drug on ec_drug.EvnCourseTreat_id = EPT.EvnCourse_id
                left join rls.v_Drug Drug on Drug.Drug_id = ec_drug.Drug_id
                left join rls.v_DrugComplexMnn dcm on dcm.DrugComplexMnn_id = coalesce(ec_drug.DrugComplexMnn_id,Drug.DrugComplexMnn_id)
                left join v_EvnPrescrTreatDrug EPTD on EPTD.EvnPrescrTreat_id = EPT.EvnPrescrTreat_id
					and EPTD.DrugComplexMnn_id = dcm.DrugComplexMnn_id

                --left join v_GoodsUnit ec_gu  on ec_drug.GoodsUnit_id = ec_gu.GoodsUnit_id
                --left join v_EvnPrescrTreatDrug EPTD on EPTD.EvnPrescrTreat_id = EPT.EvnPrescrTreat_id
				--left join rls.v_Drug Drug on Drug.Drug_id = EPTD.Drug_id
				--left join rls.v_DrugComplexMnn dcm on dcm.DrugComplexMnn_id = coalesce(EPTD.DrugComplexMnn_id,Drug.DrugComplexMnn_id)

			order by
				EPT.EvnCourse_id,
				EP.EvnPrescr_setDT
		", array(
			'EvnPrescr_pid' => $data['Evn_id']
		));
		if(!empty($resp_treat)) {
			$evnTreatPrescrIds = array();
			$treatCourseIds = array();
			// Для лек. назначений (если имеются в случае лечения) создаем лист назначений
			$arrPrescriptionTypeIDs['Treat'] = $this->createPacketPrescrList($resp_packet[0]['PacketPrescr_id'], 'Treat', $data['pmUser_id']);
			if (empty($arrPrescriptionTypeIDs['Treat'])) {
				$this->rollbackTransaction();
				return array('Error_Msg' => 'Ошибка сохранения пакета услуг');
			}
			foreach ($resp_treat as $one_treat) {
				// Проверяем, создали ли мы курс в пакете для составного лек. назначения из случая лечения
				if (!in_array($one_treat['EvnPrescr_id'], $evnTreatPrescrIds)) {
					$evnTreatPrescrIds[] = $one_treat['EvnPrescr_id'];
					// сохраняем курс назначений лек. средств
					$resp_course = $this->queryResult("
							select
								PacketPrescrTreat_id as \"PacketPrescrTreat_id\",
								Error_Code as \"Error_Code\",
								Error_Message as \"Error_Msg\"
							from	p_PacketPrescrTreat_ins(
                            					PacketPrescrList_id := :PacketPrescrList_id,
                                				PacketPrescrTreat_Duration := :PacketPrescrTreat_Duration,
                                				PacketPrescrTreat_MinCountDay := :PacketPrescrTreat_MinCountDay,
                                				PacketPrescrTreat_MaxCountDay := :PacketPrescrTreat_MaxCountDay,
                                				PacketPrescrTreat_ContReception := :PacketPrescrTreat_ContReception,
                                				PacketPrescrTreat_Interval := :PacketPrescrTreat_Interval,
                                				PacketPrescrTreat_PrescrCount := :PacketPrescrTreat_PrescrCount,
                                				DurationType_id := :DurationType_id,
                                				DurationType_recid := :DurationType_recid,
                                				DurationType_intid := :DurationType_intid,
                                				pmUser_id := :pmUser_id
                            			)
						", array(
						'PacketPrescrList_id' => $arrPrescriptionTypeIDs['Treat'],
						'PacketPrescrTreat_Duration' => $one_treat['PacketPrescrTreat_Duration'],
						'PacketPrescrTreat_MinCountDay' => $one_treat['PacketPrescrTreat_MinCountDay'],
						'PacketPrescrTreat_MaxCountDay' => $one_treat['PacketPrescrTreat_MaxCountDay'],
						'PacketPrescrTreat_ContReception' => $one_treat['PacketPrescrTreat_ContReception'],
						'PacketPrescrTreat_Interval' => $one_treat['PacketPrescrTreat_Interval'],
						'PacketPrescrTreat_PrescrCount' => $one_treat['PacketPrescrTreat_PrescrCount'],
						'DurationType_id' => $one_treat['DurationType_id'],
						'DurationType_recid' => $one_treat['DurationType_recid'],
						'DurationType_intid' => $one_treat['DurationType_intid'],
						'pmUser_id' => $data['pmUser_id']
					));
					if (empty($resp_course[0]['PacketPrescrTreat_id'])) {
						$this->rollbackTransaction();
						return array('Error_Msg' => 'Ошибка сохранения лекарства в пакете назначений');
					}
					$treatCourseIds[$one_treat['EvnPrescr_id']]['Course_id'] = $resp_course[0]['PacketPrescrTreat_id'];

					// сохраняем метод применения для определенного курса
					$resp_method = $this->queryResult("
							select
								PacketPrescrTreatMethod_id as \"PacketPrescrTreatMethod_id\",
								Error_Code as \"Error_Code\",
								Error_Message as \"Error_Msg\"
							from	p_PacketPrescrTreatMethod_ins(
								PrescriptionIntroType_id := :PrescriptionIntroType_id,
                                				PrescriptionTreatType_id := :PrescriptionTreatType_id,
                                				PerformanceType_id := :PerformanceType_id,
                                				PrescriptionTimeType_id := :PrescriptionTimeType_id,
                                				PrescriptionTreatOrderType_id := :PrescriptionTreatOrderType_id,
                                				PacketPrescrTreatMethod_IsPrescrInfusion := :PacketPrescrTreatMethod_IsPrescrInfusion,
								pmUser_id := :pmUser_id
							)
						", array(
						'PrescriptionIntroType_id' => $one_treat['PrescriptionIntroType_id'],
						'PrescriptionTreatType_id' => $one_treat['PrescriptionTreatType_id'],
						'PerformanceType_id' => $one_treat['PerformanceType_id'],
						'PrescriptionTimeType_id' => $one_treat['PrescriptionTimeType_id'],
						'PrescriptionTreatOrderType_id' => $one_treat['PrescriptionTreatOrderType_id'],
						'PacketPrescrTreatMethod_IsPrescrInfusion' => $one_treat['PacketPrescrTreatMethod_IsPrescrInfusion'],
						'pmUser_id' => $data['pmUser_id']
					));
					if (empty($resp_method[0]['PacketPrescrTreatMethod_id'])) {
						$this->rollbackTransaction();
						return array('Error_Msg' => 'Ошибка сохранения лекарства в пакете назначений');
					}
					$treatCourseIds[$one_treat['EvnPrescr_id']]['Method_id'] = $resp_method[0]['PacketPrescrTreatMethod_id'];
				}

				// сохраняем лек. назначение с сохраненным ранее методом применения для курса и самим курсом
				$resp_drug = $this->queryResult("
							select
								PacketPrescrTreatDrug_id as \"PacketPrescrTreatDrug_id\",
								Error_Code as \"Error_Code\",
								Error_Message as \"Error_Msg\"
							from	p_PacketPrescrTreatDrug_ins(
								PacketPrescrTreat_id := :PacketPrescrTreat_id,
                                				Drug_id := :Drug_id,
                                				ACTMATTERS_ID := :ACTMATTERS_ID,
                                				DrugComplexMnn_id := :DrugComplexMnn_id,
                                				PacketPrescrTreatDrug_Kolvo := :PacketPrescrTreatDrug_Kolvo,
                                				PacketPrescrTreatDrug_KolvoEd := :PacketPrescrTreatDrug_KolvoEd,
                               					GoodsUnit_id := :GoodsUnit_id,
                               					GoodsUnit_sid := :GoodsUnit_sid,
                                				PacketPrescrTreatDrug_MinDoseDay := :PacketPrescrTreatDrug_MinDoseDay,
                                				PacketPrescrTreatDrug_MaxDoseDay := :PacketPrescrTreatDrug_MaxDoseDay,
                                				PacketPrescrTreatDrug_PrescrDose := :PacketPrescrTreatDrug_PrescrDose,
                                				PacketPrescrTreatMethod_id := :PacketPrescrTreatMethod_id,
								pmUser_id := :pmUser_id
							)
						", array(
					// id курса из пакета для определенного курса лек. назначений из случая лечения
					'PacketPrescrTreat_id' => $treatCourseIds[$one_treat['EvnPrescr_id']]['Course_id'],
					'Drug_id' => $one_treat['Drug_id'],
					'ACTMATTERS_ID' => $one_treat['ACTMATTERS_ID'],
					'DrugComplexMnn_id' => $one_treat['DrugComplexMnn_id'],
					'PacketPrescrTreatDrug_Kolvo' => $one_treat['PacketPrescrTreatDrug_Kolvo'],
					'PacketPrescrTreatDrug_KolvoEd' => $one_treat['PacketPrescrTreatDrug_KolvoEd'],
					'GoodsUnit_id' => $one_treat['GoodsUnit_id'],
					'GoodsUnit_sid' => $one_treat['GoodsUnit_sid'],
					'PacketPrescrTreatDrug_MinDoseDay' => $one_treat['PacketPrescrTreatDrug_MinDoseDay'],
					'PacketPrescrTreatDrug_MaxDoseDay' => $one_treat['PacketPrescrTreatDrug_MaxDoseDay'],
					'PacketPrescrTreatDrug_PrescrDose' => $one_treat['PacketPrescrTreatDrug_PrescrDose'],
					// id метода для определенного курса лек. назначений
					'PacketPrescrTreatMethod_id' => $treatCourseIds[$one_treat['EvnPrescr_id']]['Method_id'],
					'pmUser_id' => $data['pmUser_id']
				));
				if (empty($resp_drug[0]['PacketPrescrTreatDrug_id'])) {
					$this->rollbackTransaction();
					return array('Error_Msg' => 'Ошибка сохранения лекарства в пакете назначений');
				}

			}
		}

		// СОХРАНЕНИЕ В ПАКЕТ КОНСУЛЬТАЦИОННЫХ УСЛУГ
		$resp_cons = $this->queryResult("
			select
				 EP.EvnPrescr_id as \"EvnPrescr_id\"
				,EP.EvnPrescr_pid as \"EvnPrescr_pid\"
				,EP.EvnPrescr_rid as \"EvnPrescr_rid\"
                		,EPCU.UslugaComplex_id as \"UslugaComplex_id\"
				,EP.StudyTarget_id as \"StudyTarget_id\"
                		,EP.EvnPrescr_Count as \"PacketPrescrUsluga_Count\"
				,1 as \"PacketPrescrUsluga_DailyIterCount\"
                		,1 as \"PacketPrescrUsluga_Duration\"
                		,null as \"PacketPrescrUsluga_RepeatNonstop\"
                		,null as \"PacketPrescrUsluga_Break\"
                		,null as \"DurationType_did\"
                		,null as \"DurationType_rid\"
                		,null as \"DurationType_bid\"
                		,EPCU.MedService_id as \"MedService_id\"
			from v_EvnPrescr EP
				inner join v_EvnPrescrConsUsluga EPCU on EPCU.EvnPrescrConsUsluga_id = EP.EvnPrescr_id
			where
				EP.EvnPrescr_pid  = :EvnPrescr_pid
				and EP.PrescriptionType_id = 13
				and EP.PrescriptionStatusType_id != 3
			order by
				EP.EvnPrescr_id,
				EP.EvnPrescr_setDT
		", array(
			'EvnPrescr_pid' => $data['Evn_id']
		));
		if(!empty($resp_cons)) {
			$arrPrescriptionTypeIDs['Cons'] = $this->createPacketPrescrList($resp_packet[0]['PacketPrescr_id'], 'Cons', $data['pmUser_id']);
			if (empty($arrPrescriptionTypeIDs['Cons'])) {
				$this->rollbackTransaction();
				return array('Error_Msg' => 'Ошибка сохранения пакета услуг');
			}
			foreach ($resp_cons as $one_cons) {
				$resp_oneCons = $this->queryResult("
						select
							PacketPrescrUsluga_id as \"PacketPrescrUsluga_id\",
							Error_Code as \"Error_Code\",
							Error_Message as \"Error_Msg\"
						from p_PacketPrescrUsluga_ins(
							PacketPrescrList_id := :PacketPrescrList_id,
							UslugaComplex_id := :UslugaComplex_id,
							StudyTarget_id := :StudyTarget_id,
							PacketPrescrUsluga_Count := :PacketPrescrUsluga_Count,
							PacketPrescrUsluga_DailyIterCount := :PacketPrescrUsluga_DailyIterCount,
							PacketPrescrUsluga_Duration := :PacketPrescrUsluga_Duration,
							PacketPrescrUsluga_RepeatNonstop := :PacketPrescrUsluga_RepeatNonstop,
							PacketPrescrUsluga_Break := :PacketPrescrUsluga_Break,
							DurationType_did := :DurationType_did,
							DurationType_rid := :DurationType_rid,
							DurationType_bid := :DurationType_bid,
							MedService_id := :MedService_id,
							pmUser_id := :pmUser_id
						)
					", array(
					'PacketPrescrList_id' => $arrPrescriptionTypeIDs['Cons'],
					'UslugaComplex_id' => $one_cons['UslugaComplex_id'],
					'StudyTarget_id' => $one_cons['StudyTarget_id'],
					'PacketPrescrUsluga_Count' => $one_cons['PacketPrescrUsluga_Count'],
					'PacketPrescrUsluga_DailyIterCount' => $one_cons['PacketPrescrUsluga_DailyIterCount'],
					'PacketPrescrUsluga_Duration' => $one_cons['PacketPrescrUsluga_Duration'],
					'PacketPrescrUsluga_RepeatNonstop' => $one_cons['PacketPrescrUsluga_RepeatNonstop'],
					'PacketPrescrUsluga_Break' => $one_cons['PacketPrescrUsluga_Break'],
					'DurationType_did' => $one_cons['DurationType_did'],
					'DurationType_rid' => $one_cons['DurationType_rid'],
					'DurationType_bid' => $one_cons['DurationType_bid'],
					'MedService_id' => $one_cons['MedService_id'],
					'pmUser_id' => $data['pmUser_id']
				));
				if (empty($resp_oneCons[0]['PacketPrescrUsluga_id'])) {
					$this->rollbackTransaction();
					return array('Error_Msg' => 'Ошибка сохранения лекарства в пакете назначений');
				}
			}
		}
		$this->commitTransaction();

		return array('Error_Msg' => '','PacketPrescr_id' => $resp_packet[0]['PacketPrescr_id']);
	}

	/**
	 * Редактирование свойств пакета назначений
	 * @param $data array
	 * @return array
	 */
	function updatePacketPrescr($data)
	{
		// 0. начитываем данные о случае
		// todo: тут же по идее можно проверить а есть ли вообще назначения в случае, а то может и пакет не из чего создавать
		$resp_OldPacket = $this->queryResult("
			select
				PacketPrescr_id as \"PacketPrescr_id\",
				PacketPrescr_Name as \"PacketPrescr_Name\",
				PacketPrescr_Descr as \"PacketPrescr_Descr\",
				PacketPrescrVision_id as \"PacketPrescrVision_id\",
				MedPersonal_id as \"MedPersonal_id\"
			from
				v_PacketPrescr
			where
				PacketPrescr_id = :PacketPrescr_id
		", array(
			'PacketPrescr_id' => $data['PacketPrescr_id']
		));

		if (empty($resp_OldPacket[0]['PacketPrescr_id'])) {
			return array('Error_Msg' => 'Ошибка получения данных о пакете');
		}
		$data = array_replace($resp_OldPacket[0],$data);

		$this->beginTransaction();

		// 1. обновляем сам пакет
		$resp_packet = $this->queryResult("
			select
				PacketPrescr_id as \"PacketPrescr_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from p_PacketPrescr_upd(
				PacketPrescr_id := :PacketPrescr_id,
				PacketPrescr_Name := :PacketPrescr_Name,
				PacketPrescr_Descr := :PacketPrescr_Descr,
				PacketPrescrVision_id := :PacketPrescrVision_id,
				PersonAgeGroup_id := :PersonAgeGroup_id,
				Sex_id := :Sex_id,
				MedPersonal_id := :MedPersonal_id,
				pmUser_id := :pmUser_id
			)
		", array(
			'PacketPrescr_id' => $data['PacketPrescr_id'],
			'PacketPrescr_Name' => $data['PacketPrescr_Name'],
			'PacketPrescr_Descr' => $data['PacketPrescr_Descr'],
			'PacketPrescrVision_id' => $data['PacketPrescrVision_id'],
			'PersonAgeGroup_id' => (!empty($data['PersonAgeGroup_id']))?$data['PersonAgeGroup_id']:null,
			'Sex_id' => (!empty($data['Sex_id']))?$data['Sex_id']:null,
			'pmUser_id' => $data['pmUser_id'],
			'MedPersonal_id' => $data['MedPersonal_id']
		));

		if (empty($resp_packet[0]['PacketPrescr_id'])) {
			$this->rollbackTransaction();
			return array('Error_Msg' => 'Ошибка сохранения пакета назначений');
		}


		// 2. Удаляем все связанные с пакетом диагнозы если есть
		$resp_OldDiags = $this->queryResult("
			select
				PacketPrescrDiag_id as \"PacketPrescrDiag_id\"
			from 
				v_PacketPrescrDiag sppd
			WHERE
				sppd.PacketPrescr_id = :PacketPrescr_id
		", array(
			'PacketPrescr_id' => $data['PacketPrescr_id']
		));
		if (!empty($resp_OldDiags[0])) {
			foreach($resp_OldDiags as $diag){
				$resp_ppd = $this->queryResult("
					select
						Error_Code as \"Error_Code\",
						Error_Message as \"Error_Msg\"
					from p_PacketPrescrDiag_del(
						PacketPrescrDiag_id := :PacketPrescrDiag_id
					)
				", array(
					'PacketPrescrDiag_id' => $diag['PacketPrescrDiag_id']
				));
			}
		}

		// 2. Сохраняем связи с новыми диагнозами
		$diags = explode(",", $data['Diag_id']);
		foreach ($diags as $diag) {
			if (!empty($diag)) {
				$resp_ppd = $this->queryResult("
					select
						PacketPrescrDiag_id as \"PacketPrescrDiag_id\",
						Error_Code as \"Error_Code\",
						Error_Message as \"Error_Msg\"
					from p_PacketPrescrDiag_ins(
						PacketPrescr_id := :PacketPrescr_id,
						Diag_id := :Diag_id,
						pmUser_id := :pmUser_id
					)
					", array(
					'PacketPrescr_id' => $data['PacketPrescr_id'],
					'Diag_id' => $diag,
					'pmUser_id' => $data['pmUser_id']
				));

				if (empty($resp_ppd[0]['PacketPrescrDiag_id'])) {
					$this->rollbackTransaction();
					return array('Error_Msg' => 'Ошибка сохранения диагноза пакета назначений');
				}
			}
		}

		$this->commitTransaction();

		return array('Error_Msg' => '');
	}
	/**
	 * Получение списка пакетов
	 */
	function loadPacketPrescrList($data){
// эти 2 условия для МАРМ так как там передается параметр строкой, а не булевым значением
		if ($data['onlyFavor'] == 'false') $data['onlyFavor'] = false;
		if ($data['onlyFavor'] == 'true') $data['onlyFavor'] = true;
		$filter = "";
		$MedPersFilter = "";
		$MedPersFavFilter = "";
		$folders = array();
		$queryParams = array(
			'Lpu_id' => $data['Lpu_id'],
			'LpuSection_id' => (!empty($data['session']['CurLpuSection_id']))?$data['session']['CurLpuSection_id']:null,
			'pmUser_id' => $data['pmUser_id']
		);

		if (!empty($data['Diag_id'])) {
			$queryParams['Diag_id'] = $data['Diag_id'];
			$filter .= " and exists(select ppd.PacketPrescrDiag_id from v_PacketPrescrDiag ppd where ppd.PacketPrescr_id = pp.PacketPrescr_id and ppd.Diag_id = :Diag_id limit 1)";
		}
		if (!empty($data['MedPersonal_id'])) {
			$queryParams['MedPersonal_id'] = $data['MedPersonal_id'];
			$MedPersFilter = 'OR pp.MedPersonal_id = :MedPersonal_id';
			$MedPersFavFilter = ' and ppf.MedPersonal_id = :MedPersonal_id';
		}

		if (!empty($data['mode'])) {
			switch(strval($data['mode'])) {
				case 'my':
					$filter .= " and pp.PacketPrescrVision_id = 1";
					if($data['node']=='my'){
						$count = $this->getFirstResultFromQuery("
							select
								count(*) as \"cnt\"
							from	v_PacketPrescrShare
							where	pmUser_getID = :pmUser_id
								and Lpu_gid = :Lpu_id
							limit 1
						", $queryParams);
						$folders[] = array(
							'id' => 'share',
							'text' => 'Входящие шаблоны',
							'nodeType' => 'SharedNode',
							'childrenFoldersCount' => $count,
							'sort' => 1,
						);
					}

					break;
				case 'shared':
					$filter = "";
					break;
			}
		}
		if (!empty($data['onlyFavor'])) {
			$filter .= " and ppf.PacketPrescrFavour_id is not null";
		}
		if (!empty($data['Sex_Code'])) {
			$filter .= " and coalesce(s.Sex_Code,'3') IN (:Sex_Code,'3')";
			$queryParams['Sex_Code'] = $data['Sex_Code'];
		}
		if (!empty($data['PersonAgeGroup_Code'])) {
			$filter .= " and coalesce(pag.PersonAgeGroup_Code,'3') IN (:PersonAgeGroup_Code,'3')";
			$queryParams['PersonAgeGroup_Code'] = $data['PersonAgeGroup_Code'];
		}
		if (!empty($data['query'])) {
			$queryParams['query'] = $data['query'];
			$filter .= " and pp.PacketPrescr_Name ILIKE '%' || :query || '%'";
		}
		if (!empty($data['node']) && $data['node']=='share'){

			$query = "
				SELECT
					pp.PacketPrescr_id as \"PacketPrescr_id\",
					pps.PacketPrescrShare_id as \"PacketPrescrShare_id\",
					pp.PacketPrescr_Name as \"PacketPrescr_Name\",
					pp.PacketPrescr_Descr as \"PacketPrescr_Descr\",
					substring(Diag_Codes.Codes_str, 1, length(Diag_Codes.Codes_str)-1) as \"Diag_Codes\",
					CASE WHEN ppf.PacketPrescrFavour_id is null
						THEN 0
						ELSE 2
					END as \"Packet_IsFavorite\",
					pp.PacketPrescrVision_id as \"PacketPrescrVision_id\",
					ppv.PacketPrescrVision_Name as \"PacketPrescrVision_Name\",
					COALESCE(pag.PersonAgeGroup_Name,'Не определен') as \"PersonAgeGroup_Name\",
					COALESCE(s.Sex_Name,'Не определен') as \"Sex_Name\",
					to_char(pp.PacketPrescr_updDT, 'dd.mm.yyyy') as \"PacketPrescr_updDT\",
					'true' as \"leaf\"
				FROM dbo.pmUserCache PUC
					left join dbo.pmUserCache PUCQ ON PUC.MedPersonal_id = PUCQ.MedPersonal_id
					left join v_PacketPrescrShare pps  on pps.pmUser_getID = pucq.PMUser_id
					left join v_PacketPrescr pp  on pps.PacketPrescr_id = pp.PacketPrescr_id
					left join v_PacketPrescrLpu ppl  on ppl.PacketPrescr_id = pp.PacketPrescr_id
					left join v_PacketPrescrFavour ppf  on (ppf.PacketPrescr_id = pp.PacketPrescr_id
						and ppf.MedPersonal_id = PUCQ.MedPersonal_id)
					left join v_PacketPrescrVision ppv  on ppv.PacketPrescrVision_id = pp.PacketPrescrVision_id
					left join v_Sex s  on s.Sex_id = pp.Sex_id
					left join v_PersonAgeGroup pag  on pag.PersonAgeGroup_id = pp.PersonAgeGroup_id
					left join lateral(
						select
							string_agg(dc.Diag_Code, ', ') as Codes_str
						from 
							v_PacketPrescrDiag sppd 
							left join v_diag dc  on dc.diag_id = sppd.diag_id
						WHERE
							sppd.PacketPrescr_id = pp.PacketPrescr_id
					) Diag_Codes  on true

				WHERE PUC.PMUser_id =  :pmUser_id
					AND pp.PacketPrescr_id IS NOT NULL
					{$filter}
				ORDER BY pp.PacketPrescr_updDT DESC
			";

		}else{
			$query = "
				select
					pp.PacketPrescr_id as \"PacketPrescr_id\",
					pp.PacketPrescr_Name as \"PacketPrescr_Name\",
					pp.PacketPrescr_Descr as \"PacketPrescr_Descr\",
					substring(Diag_Codes.Codes_str, 1, length(Diag_Codes.Codes_str)-1) as \"Diag_Codes\",
					CASE WHEN ppf.PacketPrescrFavour_id is null THEN 0 ELSE 2 END as \"Packet_IsFavorite\",
					pp.PacketPrescrVision_id as \"PacketPrescrVision_id\",
					ppv.PacketPrescrVision_Name as \"PacketPrescrVision_Name\",
					COALESCE(pag.PersonAgeGroup_Name,'Не определен') as \"PersonAgeGroup_Name\",
					COALESCE(s.Sex_Name,'Не определен') as \"Sex_Name\",
					to_char(pp.PacketPrescr_updDT, 'dd.mm.yyyy') as \"PacketPrescr_updDT\",
					'true' as \"leaf\"
				from
					v_PacketPrescr pp 
					left join v_PacketPrescrLpu ppl  on ppl.PacketPrescr_id = pp.PacketPrescr_id
					left join v_PacketPrescrFavour ppf  on (ppf.PacketPrescr_id = pp.PacketPrescr_id {$MedPersFavFilter})
					left join v_PacketPrescrVision ppv  on ppv.PacketPrescrVision_id = pp.PacketPrescrVision_id
					left join v_Sex s  on s.Sex_id = pp.Sex_id
					left join v_PersonAgeGroup pag  on pag.PersonAgeGroup_id = pp.PersonAgeGroup_id
					left join lateral(
						select
							string_agg(dc.Diag_Code, ', ') as Codes_str
						from 
							v_PacketPrescrDiag sppd 
							left join v_diag dc  on dc.diag_id = sppd.diag_id
						WHERE
							sppd.PacketPrescr_id = pp.PacketPrescr_id
					) Diag_Codes on true
				where
					case
						when pp.PacketPrescrVision_id = 1 and (pp.pmUser_insID = :pmUser_id {$MedPersFilter}) then 1 
						when pp.PacketPrescrVision_id = 2 and ppl.Lpu_id = :Lpu_id then 1 
						when pp.PacketPrescrVision_id = 3 and ppl.LpuSection_id = :LpuSection_id then 1
						else 2
					end = 1
					{$filter}
				ORDER BY pp.PacketPrescr_updDT DESC
			";
		}

		$templates = $this->queryResult($query, $queryParams);

		$res = array_merge($templates,$folders);
		return $res;
	}

	/**
	 * @param array $data
	 * @return array|false
	 */
	function loadPMUserForShareList($data) {

		$params = array(
			'PacketPrescr_id' => $data['PacketPrescr_id'],
			'Lpu_id' => $data['Lpu_id'],
			'pmUser_id' => $data['pmUser_id'],
		);
		$filters = array('');

		if (!empty($data['query'])) {
			$filters[] = "(
			U.pmUser_Name ilike :query || '%'
			or U.pmUser_Login ilike :query || '%'
		)";
			$params['query'] = $data['query'];
		}

		$filters_str = implode(" \nand ", $filters);

		$query = "
			select
				U.pmUser_id || '_' || L.Lpu_id as \"id\",
				U.pmUser_id as \"pmUser_id\",
				rtrim(U.pmUser_Login) as \"pmUser_Login\",
				case when MP.MedPersonal_id is not null
					then rtrim(MP.Person_Fio)
					else rtrim(U.pmUser_Name)
				end as \"pmUser_Name\",
				L.Lpu_id as \"Lpu_id\",
				L.Lpu_Nick as \"Lpu_Nick\"
			from v_pmUserCache U
				inner join v_pmUserCacheOrg UO on UO.pmUserCache_id = U.pmUser_id
				inner join v_Lpu L on L.Org_id = UO.Org_id
				left join lateral(
					select *
					from v_MedPersonal
					where MedPersonal_id = U.MedPersonal_id
					limit 1
				) MP on true
			where
				L.Lpu_id <> :Lpu_id
					or (L.Lpu_id = :Lpu_id and U.pmUser_id <> :pmUser_id)
				and not exists (
				select *
					from v_PacketPrescrShare
					where PacketPrescr_id = :PacketPrescr_id
					and pmUser_getID = U.pmUser_id
					and Lpu_gid = L.Lpu_id
				)
				{$filters_str}
			limit 200
		";

		$response = $this->queryResult($query, $params);
		if (!is_array($response)) {
			return false;
		}

		$sort = array();
		$listById = array();

		foreach($response as $item) {
			$item['compareField'] = null;

			if (!empty($params['query'])) {
				$query = mb_strtolower($params['query']);
				$length = mb_strlen($query);
				$name = mb_strtolower(mb_substr($item['pmUser_Name'], 0, $length));
				$login = mb_strtolower(mb_substr($item['pmUser_Login'], 0, $length));

				if ($name == $query) {
					$item['compareField'] = 'pmUser_Name';
				} else if ($login == $query) {
					$item['compareField'] = 'pmUser_Login';
				}
			}

			$sort[$item['id']] = !empty($item['compareField'])
				?mb_strtolower($item[$item['compareField']])
				:$item['pmUser_Name'];

			$listById[$item['id']] = $item;
		}

		asort($sort);
		$response = array();
		foreach($sort as $id => $value) {
			$response[] = $listById[$id];
		}

		return $response;
	}

	/**
	 * @param array $data
	 * @return array
	 */
	function sharePacketPrescr($data) {
		$shareTo = json_decode($data['shareTo'], true);

		$this->beginTransaction();

		foreach ($shareTo as $item) {
			$item['PacketPrescr_id'] = $data['PacketPrescr_id'];
			$item['pmUser_sendID'] = $data['pmUser_id'];
			$item['Lpu_sid'] = $data['Lpu_id'];
			$item['pmUser_id'] = $data['pmUser_id'];

			$resp = $this->createPacketPrescrShare($item);

			if (!$this->isSuccessful($resp) && $resp[0]['Error_Code'] != 101) {
				$this->rollbackTransaction();
				return $resp;
			} else if (!empty($resp[0]['Error_Code']) && $resp[0]['Error_Code'] == 101) {
				return $resp[0];
			}
		}

		$this->commitTransaction();

		return array(array(
			'success' => true
		));
	}

	/**
	 * @param array $data
	 * @return array
	 */
	function createPacketPrescrShare($data) {
		$params = array(
			'PacketPrescrShare_IsReaded' => !empty($data['PacketPrescrShare_IsReaded'])	? $data['PacketPrescrShare_IsReaded']:1,
			'PacketPrescr_id' => $data['PacketPrescr_id'],
			'pmUser_sendID' => $data['pmUser_sendID'],
			'Lpu_sid' => $data['Lpu_sid'],
			'pmUser_getID' => $data['pmUser_getID'],
			'Lpu_gid' => $data['Lpu_gid'],
			'pmUser_id' => $data['pmUser_id'],
		);


		$count = $this->getFirstResultFromQuery("
			select
				count(*) as cnt
			from v_PacketPrescrShare
			where PacketPrescr_id = :PacketPrescr_id
				and pmUser_getID = :pmUser_getID
				and Lpu_gid = :Lpu_gid
			limit 1
		", $params);


		if ($count > 0) {
			return $this->createError(101,'Пакет уже был отправлен пользователю');
		}

		$query = "
			select
				PacketPrescrShare_id as \"PacketPrescrShare_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from p_PacketPrescrShare_ins(
				PacketPrescrShare_IsReaded := :PacketPrescrShare_IsReaded,
				PacketPrescr_id := :PacketPrescr_id,
				pmUser_sendID := :pmUser_sendID,
				Lpu_sid := :Lpu_sid,
				pmUser_getID := :pmUser_getID,
				Lpu_gid := :Lpu_gid,
				pmUser_id := :pmUser_id
			)
		";
		//echo getDebugSQL($query, $params);exit;
		$resp = $this->queryResult($query, $params);
		if (!is_array($resp)) {
			return $this->createError('','Ошибка при сохранении записи об отправленном пакете');
		}

		return $resp;
	}

	/**
	 * Получение списка клин. рекомендаций
	 */
	function loadCureStandartList($data){
		$filter = '';
		$limit = '';
		$queryParams = array();

		if (!empty($data['Diag_id'])) {
			$queryParams['Diag_id'] = $data['Diag_id'];
			$filter .= " and exists(select csd.CureStandartDiag_id from v_CureStandartDiag csd where csd.CureStandart_id = cs.CureStandart_id and csd.Diag_id = :Diag_id limit 1)";
		}
		else
			$limit = 'limit 100';

		return $this->queryResult("
			select
				CS.CureStandart_id as \"CureStandart_id\",
				CS.CureStandart_Name as \"CureStandart_Name\",
				CS.CureStandart_ClinRecDescr as \"CureStandart_ClinRecDescr\",
				CSAGT.CureStandartAgeGroupType_Name as \"CureStandartAgeGroupType_Name\",
                		CSPT.CureStandartPhaseType_Name as \"CureStandartPhaseType_Name\",
                		CSST.CureStandartStageType_Name as \"CureStandartStageType_Name\",
                		CSCT.CureStandartComplicationType_Name as \"CureStandartComplicationType_Name\",
                		MCK.MedicalCareKind_Name as \"MedicalCareKind_Name\",
				null as \"CureStandart_Descr\"
			from
				v_CureStandart CS
				left join v_CureStandartAgeGroupType as CSAGT on CS.CureStandartAgeGroupType_id = CSAGT.CureStandartAgeGroupType_id
                		left join v_CureStandartPhaseType as CSPT on CS.CureStandartPhaseType_id = CSPT.CureStandartPhaseType_id
                		left join v_CureStandartStageType as CSST on CS.CureStandartStageType_id = CSST.CureStandartStageType_id
                		left join v_CureStandartComplicationType as CSCT on CS.CureStandartComplicationType_id = CSCT.CureStandartComplicationType_id
                		left join v_MedicalCareKind as MCK on CS.MedicalCareKind_id = MCK.MedicalCareKind_id
			where
				1 = 1
				{$filter}
			{$limit}
		", $queryParams);
	}

	/**
	 * Получение списка назначений на основе пакета или стандарта
	 */
	function loadPacketForPrescrList($data){
		switch($data['objectPrescribe']) {
			case 'LabDiagData':
				if (!empty($data['CureStandart_id'])) {
					return $this->queryResult("
						WITH CureStandartUsluga AS(
							select
								csds.CureStandart_id,
								csds.UslugaComplex_id,
								csds.CureStandartDiagnosis_AverageNumber as AverageNumber,
								csds.CureStandartDiagnosis_FreqDelivery as FreqDelivery,
								csds.CureStandartDiagnosis_id,
								null as CureStandartTreatmentUsluga_id
							from
								v_CureStandartDiagnosis csds
							where
								csds.CureStandart_id = :CureStandart_id
								and exists (
									select t1.UslugaComplexAttribute_id from v_UslugaComplexAttribute t1
									inner join v_UslugaComplexAttributeType t2 on t2.UslugaComplexAttributeType_id = t1.UslugaComplexAttributeType_id
									where t1.UslugaComplex_id = csds.UslugaComplex_id and t2.UslugaComplexAttributeType_SysNick ilike 'lab'
								)
							union all
							select 
								cst.CureStandart_id,
								cstu.UslugaComplex_id,
								cstu.CureStandartTreatmentUsluga_AverageNumber as AverageNumber,
								cstu.CureStandartTreatmentUsluga_FreqDelivery as FreqDelivery,
								null as CureStandartDiagnosis_id,
								cstu.CureStandartTreatmentUsluga_id
							from
								v_CureStandartTreatment cst
								inner join v_CureStandartTreatmentUsluga cstu on cst.CureStandartTreatment_id = cstu.CureStandartTreatment_id
							where
								cst.CureStandart_id = :CureStandart_id
								and exists (
									select t1.UslugaComplexAttribute_id from v_UslugaComplexAttribute t1
									inner join v_UslugaComplexAttributeType t2 on t2.UslugaComplexAttributeType_id = t1.UslugaComplexAttributeType_id
									where t1.UslugaComplex_id = cstu.UslugaComplex_id and t2.UslugaComplexAttributeType_SysNick ilike 'lab'
								)
						) 
						select distinct
							csu.CureStandart_id as \"CureStandart_id\",
							csu.UslugaComplex_id as \"UslugaComplex_id\",
							uc.UslugaComplex_Code as \"UslugaComplex_Code\",
							uc.UslugaComplex_Name as \"UslugaComplex_Name\",
							csu.AverageNumber as \"AverageNumber\",
							csu.FreqDelivery as \"FreqDelivery\",
							csu.CureStandartDiagnosis_id as \"CureStandartDiagnosis_id\",
							csu.CureStandartTreatmentUsluga_id as \"CureStandartTreatmentUsluga_id\"
						from CureStandartUsluga csu
						inner join v_UslugaComplex uc on uc.UslugaComplex_id = csu.UslugaComplex_id
						inner JOIN (SELECT MAX(FreqDelivery) AS CureStandartDiagnosis_FreqDelivery,UslugaComplex_id FROM CureStandartUsluga GROUP BY UslugaComplex_id) AS CSDD 
						ON csu.FreqDelivery=CSDD.CureStandartDiagnosis_FreqDelivery AND CSDD.UslugaComplex_id = csu.UslugaComplex_id
						order by uc.UslugaComplex_Code
					", array(
						'CureStandart_id' => $data['CureStandart_id']
					));
				} else if (!empty($data['PacketPrescr_id'])) {
					return $this->queryResult("
						select distinct
							uc.UslugaComplex_id as \"UslugaComplex_id\",
							uc.UslugaComplex_Name as \"UslugaComplex_Name\",
							ppu.MedService_id as \"MedService_id\",
							l.Lpu_Nick as \"Lpu_Nick\",
							l.Lpu_id as \"Lpu_id\",
							ms.MedService_Nick as \"MedService_Nick\",
							ppu.PacketPrescrUsluga_id as \"PacketPrescrUsluga_id\"
						from
                        	v_PacketPrescr pp
                        	inner join v_PacketPrescrList ppl on ppl.PacketPrescr_id = pp.PacketPrescr_id
							inner join v_PacketPrescrUsluga ppu on ppu.PacketPrescrList_id = ppl.PacketPrescrList_id
							inner join v_UslugaComplex uc on uc.UslugaComplex_id = ppu.UslugaComplex_id
							left join v_MedService ms on ms.MedService_id = ppu.MedService_id
							left join v_Lpu l on l.Lpu_id = ms.Lpu_id
						where
							pp.PacketPrescr_id = :PacketPrescr_id
							and ppl.PrescriptionType_id = '11' -- лаб.диагностика
					", array(
						'PacketPrescr_id' => $data['PacketPrescr_id']
					));
				}
				break;
			case 'FuncDiagData':
				if (!empty($data['CureStandart_id'])) {
					return $this->queryResult("
						WITH CureStandartUsluga
						AS
						(
							select
								csds.CureStandart_id,
								csds.UslugaComplex_id,
								csds.CureStandartDiagnosis_AverageNumber as AverageNumber,
								csds.CureStandartDiagnosis_FreqDelivery as FreqDelivery,
								csds.CureStandartDiagnosis_id,
								null as CureStandartTreatmentUsluga_id
							from
								v_CureStandartDiagnosis csds
							where
								csds.CureStandart_id = :CureStandart_id
								and exists (
									select t1.UslugaComplexAttribute_id from v_UslugaComplexAttribute t1
									inner join v_UslugaComplexAttributeType t2 on t2.UslugaComplexAttributeType_id = t1.UslugaComplexAttributeType_id
									where t1.UslugaComplex_id = csds.UslugaComplex_id and (t2.UslugaComplexAttributeType_SysNick in ('func','xray','kt','mrt'))
								)
							union all
							select 
								cst.CureStandart_id,
								cstu.UslugaComplex_id,
								cstu.CureStandartTreatmentUsluga_AverageNumber as AverageNumber,
								cstu.CureStandartTreatmentUsluga_FreqDelivery as FreqDelivery,
								null as CureStandartDiagnosis_id,
								cstu.CureStandartTreatmentUsluga_id
							from
								v_CureStandartTreatment cst
								inner join v_CureStandartTreatmentUsluga cstu on cst.CureStandartTreatment_id = cstu.CureStandartTreatment_id
							where
								cst.CureStandart_id = :CureStandart_id
								and exists (
									select t1.UslugaComplexAttribute_id from v_UslugaComplexAttribute t1
									inner join v_UslugaComplexAttributeType t2 on t2.UslugaComplexAttributeType_id = t1.UslugaComplexAttributeType_id
									where t1.UslugaComplex_id = cstu.UslugaComplex_id and (t2.UslugaComplexAttributeType_SysNick in ('func','xray','kt','mrt'))
								)
						) 
						select distinct
							csu.CureStandart_id as \"CureStandart_id\",
							csu.UslugaComplex_id as \"UslugaComplex_id\",
							uc.UslugaComplex_Code as \"UslugaComplex_Code\",
							uc.UslugaComplex_Name as \"UslugaComplex_Name\",
							csu.AverageNumber as \"AverageNumber\",
							csu.FreqDelivery as \"FreqDelivery\",
							csu.CureStandartDiagnosis_id as \"CureStandartDiagnosis_id\",
							csu.CureStandartTreatmentUsluga_id as \"CureStandartTreatmentUsluga_id\"
						from CureStandartUsluga csu
						inner join v_UslugaComplex uc on uc.UslugaComplex_id = csu.UslugaComplex_id
						inner JOIN (SELECT MAX(FreqDelivery) AS CureStandartDiagnosis_FreqDelivery,UslugaComplex_id FROM CureStandartUsluga GROUP BY UslugaComplex_id) AS CSDD 
						ON csu.FreqDelivery=CSDD.CureStandartDiagnosis_FreqDelivery AND CSDD.UslugaComplex_id = csu.UslugaComplex_id
						order by uc.UslugaComplex_Code
					", array(
						'CureStandart_id' => $data['CureStandart_id']
					));
				} else if (!empty($data['PacketPrescr_id'])) {
					return $this->queryResult("
						select distinct
							uc.UslugaComplex_id as \"UslugaComplex_id\",
							uc.UslugaComplex_Name as \"UslugaComplex_Name\",
							ppu.MedService_id as \"MedService_id\",
							l.Lpu_Nick as \"Lpu_Nick\",
							l.Lpu_id as \"Lpu_id\",
							ms.MedService_Nick as \"MedService_Nick\",
							ppu.PacketPrescrUsluga_id as \"PacketPrescrUsluga_id\"
						from
                        	v_PacketPrescr pp
                        	inner join v_PacketPrescrList ppl on ppl.PacketPrescr_id = pp.PacketPrescr_id
							inner join v_PacketPrescrUsluga ppu on ppu.PacketPrescrList_id = ppl.PacketPrescrList_id
							inner join v_UslugaComplex uc on uc.UslugaComplex_id = ppu.UslugaComplex_id
							left join v_MedService ms on ms.MedService_id = ppu.MedService_id
							left join v_Lpu l on l.Lpu_id = ms.Lpu_id
						where
							pp.PacketPrescr_id = :PacketPrescr_id
							and ppl.PrescriptionType_id = '12' -- функц.диагностика
					", array(
						'PacketPrescr_id' => $data['PacketPrescr_id']
					));
				}
				break;
			case 'DrugData':
				if (!empty($data['CureStandart_id'])) {
					return $this->queryResult("
						select distinct
							cst.CureStandartTreatment_id as \"CureStandartTreatment_id\",
							MnnName.ACTMATTERS_id as \"ActMatters_id\",
							MnnName.DrugComplexMnnName_Name as \"ActMatters_Name\",
							atc.CLSATC_ID as \"ClsAtc_id\",
							atc.parentName as \"ClsAtc_Name\",
							ABS(CHECKSUM(NewId())) % 2 as \"FreqDelivery\",
							1 as \"Replaseability\"
						from
							v_CureStandartTreatment cst
							inner join v_CureStandartTreatmentDrug cstd on cstd.CureStandartTreatment_id = cst.CureStandartTreatment_id
							inner join rls.v_DrugComplexMnnName MnnName on MnnName.ACTMATTERS_id = cstd.ACTMATTERS_ID
							left join lateral(
								select
									c_at3.CLSATC_ID,
									c_at3.NAME as parentName
								from rls.PREP_ACTMATTERS p_am
									left join rls.v_PREP_ATC p_at on p_at.PREPID = p_am.PREPID
									inner join rls.v_CLSATC c_at on c_at.CLSATC_ID = p_at.UNIQID
									inner join rls.v_CLSATC c_at2 on c_at2.CLSATC_ID = c_at.PARENTID
									inner join rls.v_CLSATC c_at3 on c_at3.CLSATC_ID = c_at2.PARENTID
								where
									p_am.MATTERID = cstd.ACTMATTERS_ID
								order by
									p_am.PREPID
								limit 1
							) atc on true
						where
							cst.CureStandart_id = :CureStandart_id
						order by 
							cst.CureStandartTreatment_id, 
							-- Пока не придумают верную группировку по АТХ
							--ClsAtc_Name, 
							--ActMatters_Name,
							\"FreqDelivery\" DESC
						", array(
						'CureStandart_id' => $data['CureStandart_id']
					));
				} else if (!empty($data['PacketPrescr_id'])) {
					$arrDrugData = $this->queryResult("
						select
							coalesce(Drug.Drug_Name, dcm.DrugComplexMnn_RusName, MnnName.DrugComplexMnnName_Name, '') as \"Drug_Name\"
							,ppt.PacketPrescrTreat_id as \"PacketPrescrTreat_id\"
							,pptd.PacketPrescrTreatDrug_id as \"PacketPrescrTreatDrug_id\"
							,pptd.DrugComplexMnn_id as \"DrugComplexMnn_id\"
							,pptd.Drug_id as \"Drug_id\"
							,pptd.ACTMATTERS_ID as \"ACTMATTERS_ID\"
						from
							dbo.v_PacketPrescr pp
							inner join dbo.v_PacketPrescrList ppl on ppl.PacketPrescr_id = pp.PacketPrescr_id
							inner join dbo.v_PacketPrescrTreat ppt on ppt.PacketPrescrList_id = ppl.PacketPrescrList_id
							left join dbo.v_PacketPrescrTreatDrug pptd on pptd.PacketPrescrTreat_id = ppt.PacketPrescrTreat_id
							left join rls.v_DrugComplexMnnName MnnName on MnnName.ACTMATTERS_id = pptd.ACTMATTERS_ID
							left join rls.v_Drug Drug on Drug.Drug_id = pptd.Drug_id
							left join rls.v_DrugComplexMnn dcm on dcm.DrugComplexMnn_id = coalesce(pptd.DrugComplexMnn_id,Drug.DrugComplexMnn_id)
						where
							pp.PacketPrescr_id = :PacketPrescr_id
							and ppl.PrescriptionType_id = '5' -- лекарственные назначения
					", array(
						'PacketPrescr_id' => $data['PacketPrescr_id']
					));
					$resArrDrug = array();
					//$arrCourseIds = array();
					foreach($arrDrugData as $oneDrug){
						$resArrDrug[$oneDrug['PacketPrescrTreat_id']]['PacketPrescrTreat_id'] = $oneDrug['PacketPrescrTreat_id'];
						$resArrDrug[$oneDrug['PacketPrescrTreat_id']]['DrugListData'][] = $oneDrug;
						/*if(!in_array($oneDrug['PacketPrescrTreat_id'], $arrCourseIds)){
							$arrCourseIds[] = $oneDrug['PacketPrescrTreat_id'];
							$resArrDrug[$oneDrug['PacketPrescrTreat_id']] = array();
						}*/
					}
					return $resArrDrug;
				}
				break;
			case 'ConsUslData':
				if (!empty($data['CureStandart_id'])) {
					return $this->queryResult("
						select
							uc.UslugaComplex_id as \"UslugaComplex_id\",
							uc.UslugaComplex_Name as \"UslugaComplex_Name\"
						from
							v_CureStandartDiagnosis csd
							inner join v_UslugaComplex uc on uc.UslugaComplex_id = csd.UslugaComplex_id
						where
							csd.CureStandart_id = :CureStandart_id
							and exists (
								select
									t1.UslugaComplexAttribute_id
								from
									v_UslugaComplexAttribute t1
									inner join v_UslugaComplexAttributeType t2 on t2.UslugaComplexAttributeType_id = t1.UslugaComplexAttributeType_id
								where
									t1.UslugaComplex_id = uc.UslugaComplex_id
									and (t2.UslugaComplexAttributeType_SysNick in ('func','xray','kt','mrt'))
								limit 1
							)
					", array(
						'CureStandart_id' => $data['CureStandart_id']
					));
				} else if (!empty($data['PacketPrescr_id'])) {
					return $this->queryResult("
						select distinct
							uc.UslugaComplex_id as \"UslugaComplex_id\",
							uc.UslugaComplex_Name as \"UslugaComplex_Name\",
							ppu.MedService_id as \"MedService_id\",
							l.Lpu_Nick as \"Lpu_Nick\",
							l.Lpu_id as \"Lpu_id\",
							ms.MedService_Nick as \"MedService_Nick\",
							ppu.PacketPrescrUsluga_id as \"PacketPrescrUsluga_id\"
						from
							v_PacketPrescr pp
							inner join v_PacketPrescrList ppl on ppl.PacketPrescr_id = pp.PacketPrescr_id
							inner join v_PacketPrescrUsluga ppu on ppu.PacketPrescrList_id = ppl.PacketPrescrList_id
							inner join v_UslugaComplex uc on uc.UslugaComplex_id = ppu.UslugaComplex_id
							left join v_MedService ms on ms.MedService_id = ppu.MedService_id
							left join v_Lpu l on l.Lpu_id = ms.Lpu_id					
						where
							pp.PacketPrescr_id = :PacketPrescr_id
							and ppl.PrescriptionType_id = '13' -- консультационная услуга
					", array(
						'PacketPrescr_id' => $data['PacketPrescr_id']
					));
				}
				break;
			case 'ProcData':
				if (!empty($data['CureStandart_id'])) {
					return $this->queryResult("
						select
							uc.UslugaComplex_id as \"UslugaComplex_id\",
							uc.UslugaComplex_Name as \"UslugaComplex_Name\"
						from
							v_CureStandartDiagnosis csd
							inner join v_UslugaComplex uc on uc.UslugaComplex_id = csd.UslugaComplex_id
						where
							csd.CureStandart_id = :CureStandart_id
							and exists (
								select
									t1.UslugaComplexAttribute_id
								from
									v_UslugaComplexAttribute t1
									inner join v_UslugaComplexAttributeType t2 on t2.UslugaComplexAttributeType_id = t1.UslugaComplexAttributeType_id
								where
									t1.UslugaComplex_id = uc.UslugaComplex_id
									and (t2.UslugaComplexAttributeType_SysNick in ('func','xray','kt','mrt'))
								limit 1
							)
					", array(
						'CureStandart_id' => $data['CureStandart_id']
					));
				} else if (!empty($data['PacketPrescr_id'])) {
					return $this->queryResult("
						select distinct
							uc.UslugaComplex_id as \"UslugaComplex_id\",
							uc.UslugaComplex_Name as \"UslugaComplex_Name\",
							l.Lpu_Nick as \"Lpu_Nick\",
							l.Lpu_id as \"Lpu_id\",
							ms.MedService_Nick as \"MedService_Nick\",
							ppu.PacketPrescrUsluga_id as \"PacketPrescrUsluga_id\"
						from
							v_PacketPrescr pp
							inner join v_PacketPrescrList ppl on ppl.PacketPrescr_id = pp.PacketPrescr_id
							inner join v_PacketPrescrUsluga ppu on ppu.PacketPrescrList_id = ppl.PacketPrescrList_id
							inner join v_UslugaComplex uc on uc.UslugaComplex_id = ppu.UslugaComplex_id
							inner  join v_MedService ms on ms.MedService_id = ppu.MedService_id
							inner  join v_Lpu l on l.Lpu_id = ms.Lpu_id					
						where
							pp.PacketPrescr_id = :PacketPrescr_id
							and ppl.PrescriptionType_id = '6' -- процедуры и манипуляции
					", array(
						'PacketPrescr_id' => $data['PacketPrescr_id']
					));
				}
				break;
			case 'RegimeData':
				if (!empty($data['CureStandart_id'])) {
					return $this->queryResult("
						select
							uc.UslugaComplex_id as \"UslugaComplex_id\",
							uc.UslugaComplex_Name as \"UslugaComplex_Name\"
						from
							v_CureStandartDiagnosis csd
							inner join v_UslugaComplex uc on uc.UslugaComplex_id = csd.UslugaComplex_id
						where
							csd.CureStandart_id = :CureStandart_id
							and exists (
								select
									t1.UslugaComplexAttribute_id
								from
									v_UslugaComplexAttribute t1
									inner join v_UslugaComplexAttributeType t2 on t2.UslugaComplexAttributeType_id = t1.UslugaComplexAttributeType_id
								where
									t1.UslugaComplex_id = uc.UslugaComplex_id
									and (t2.UslugaComplexAttributeType_SysNick in ('func','xray','kt','mrt'))
								limit 1
							)
					", array(
						'CureStandart_id' => $data['CureStandart_id']
					));
				} else if (!empty($data['PacketPrescr_id'])) {
					return $this->queryResult("
						select
							 PRT.PrescriptionRegimeType_Name as \"PrescriptionRegimeType_Name\"
							,ppr.PrescriptionRegimeType_id as \"PrescriptionRegimeType_id\"
							,ppr.PacketPrescrRegime_Duration as \"PacketPrescrRegime_Duration\"
							,ppr.PacketPrescrRegime_id as \"PacketPrescrRegime_id\"
						from
							dbo.v_PacketPrescr pp
							inner join dbo.v_PacketPrescrList ppl on ppl.PacketPrescr_id = pp.PacketPrescr_id
							inner join dbo.v_PacketPrescrRegime ppr on ppr.PacketPrescrList_id = ppl.PacketPrescrList_id
							inner join PrescriptionRegimeType PRT on PRT.PrescriptionRegimeType_id = ppr.PrescriptionRegimeType_id
						where
							pp.PacketPrescr_id = :PacketPrescr_id
							and ppl.PrescriptionType_id = '1' -- режимы
					", array(
						'PacketPrescr_id' => $data['PacketPrescr_id']
					));
				}
				break;
			case 'DietData':
				if (!empty($data['CureStandart_id'])) {
					return $this->queryResult("
						select
							uc.UslugaComplex_id as \"UslugaComplex_id\",
							uc.UslugaComplex_Name as \"UslugaComplex_Name\"
						from
							v_CureStandartDiagnosis csd
							inner join v_UslugaComplex uc on uc.UslugaComplex_id = csd.UslugaComplex_id
						where
							csd.CureStandart_id = :CureStandart_id
							and exists (
								select
									t1.UslugaComplexAttribute_id
								from
									v_UslugaComplexAttribute t1
									inner join v_UslugaComplexAttributeType t2 on t2.UslugaComplexAttributeType_id = t1.UslugaComplexAttributeType_id
								where
									t1.UslugaComplex_id = uc.UslugaComplex_id
									and (t2.UslugaComplexAttributeType_SysNick in ('func','xray','kt','mrt'))
								limit 1
							)
					", array(
						'CureStandart_id' => $data['CureStandart_id']
					));
				} else if (!empty($data['PacketPrescr_id'])) {
					return $this->queryResult("
						select
							 pdt.PrescriptionDietType_Name as \"PrescriptionDietType_Name\"
							,ppd.PrescriptionDietType_id as \"PrescriptionDietType_id\"
							,ppd.PacketPrescrDiet_Duration as \"PacketPrescrDiet_Duration\"
							,ppd.PacketPrescrDiet_id as \"PacketPrescrDiet_id\"
						from
							dbo.v_PacketPrescr pp
							inner join dbo.v_PacketPrescrList ppl on ppl.PacketPrescr_id = pp.PacketPrescr_id
							inner join dbo.v_PacketPrescrDiet ppd on ppd.PacketPrescrList_id = ppl.PacketPrescrList_id
							inner join v_PrescriptionDietType pdt on pdt.PrescriptionDietType_id = ppd.PrescriptionDietType_id
						where
							pp.PacketPrescr_id = :PacketPrescr_id
							and ppl.PrescriptionType_id = '2' -- диеты
					", array(
						'PacketPrescr_id' => $data['PacketPrescr_id']
					));
				}
				break;
		}

		return array();
	}

	/**
	 * Получение списка назначений на основе пакета или стандарта
	 */
	function mGetPacketPrescrData($data){
		$id = $data['PacketPrescr_id'];
		$result = array();
		if (!empty($data['PacketPrescr_id'])) {
					$arrlabdiag = "
						select
							uc.UslugaComplex_id as \"id\",
							uc.UslugaComplex_Name as \"name\"
						from
                        				v_PacketPrescr pp
                        				inner join v_PacketPrescrList ppl on ppl.PacketPrescr_id = pp.PacketPrescr_id
                        				inner join v_PacketPrescrUsluga ppu on ppu.PacketPrescrList_id = ppl.PacketPrescrList_id
							inner join v_UslugaComplex uc on uc.UslugaComplex_id = ppu.UslugaComplex_id
							left join v_MedService ms on ms.MedService_id = ppu.MedService_id
							left join v_Lpu l on l.Lpu_id = ms.Lpu_id
						where
							pp.PacketPrescr_id = {$id}
							and ppl.PrescriptionType_id = '11' -- лаб.диагностика
					";
					$resultLab = $this->db->query($arrlabdiag);
					$resultLab = $resultLab->result('array');

					$resultArr = array(
						'name'=> 'labdiag',
						'content'=> $resultLab
					);
					$result[] = $resultArr;
				}

			if (!empty($data['PacketPrescr_id'])) {
					$arrFuncdiag = "
						select
							uc.UslugaComplex_id as \"id\",
							uc.UslugaComplex_Name as \"name\"
						from
                        				v_PacketPrescr pp
                        				inner join v_PacketPrescrList ppl on ppl.PacketPrescr_id = pp.PacketPrescr_id
                        				inner join v_PacketPrescrUsluga ppu on ppu.PacketPrescrList_id = ppl.PacketPrescrList_id
							inner join v_UslugaComplex uc on uc.UslugaComplex_id = ppu.UslugaComplex_id
							left join v_MedService ms on ms.MedService_id = ppu.MedService_id
							left join v_Lpu l on l.Lpu_id = ms.Lpu_id
						where
							pp.PacketPrescr_id = {$id}
							and ppl.PrescriptionType_id = '12' -- функц.диагностика
					";
				$resultFuncdiag = $this->db->query($arrFuncdiag);
				$resultFuncdiag = $resultFuncdiag->result('array');
				$resultArr = array(
					'name'=> 'funcdiag',
					'content'=> $resultFuncdiag
				);

				$result[] = $resultArr;
				}
			if (!empty($data['PacketPrescr_id'])) {
					$arrDrug = "
						select
							 pptd.PacketPrescrTreatDrug_id as \"id\"
							,coalesce(Drug.Drug_Name, dcm.DrugComplexMnn_RusName, MnnName.DrugComplexMnnName_Name, '') as \"name\"
						from
							dbo.v_PacketPrescr pp
							inner join dbo.v_PacketPrescrList ppl on ppl.PacketPrescr_id = pp.PacketPrescr_id
							inner join dbo.v_PacketPrescrTreat ppt on ppt.PacketPrescrList_id = ppl.PacketPrescrList_id
							left join dbo.v_PacketPrescrTreatDrug pptd on pptd.PacketPrescrTreat_id = ppt.PacketPrescrTreat_id
							left join rls.v_DrugComplexMnnName MnnName on MnnName.ACTMATTERS_id = pptd.ACTMATTERS_ID
							left join rls.v_Drug Drug on Drug.Drug_id = pptd.Drug_id
							left join rls.v_DrugComplexMnn dcm on dcm.DrugComplexMnn_id = coalesce(pptd.DrugComplexMnn_id,Drug.DrugComplexMnn_id)
						where
							pp.PacketPrescr_id = {$id}
							and ppl.PrescriptionType_id = '5' -- лекарственные назначения
					";
					$resultDrug = $this->db->query($arrDrug);
					$resultDrug = $resultDrug->result('array');
					$resultArr = array(
						'name'=> 'drug',
						'content'=> $resultDrug
					);

					$result[] = $resultArr;
				}
			if (!empty($data['PacketPrescr_id'])) {
					$arrСonsul = "
						select
							uc.UslugaComplex_id as \"id\",
							uc.UslugaComplex_Name as \"name\"
						from
                        				v_PacketPrescr pp
                        				inner join v_PacketPrescrList ppl on ppl.PacketPrescr_id = pp.PacketPrescr_id
                        				inner join v_PacketPrescrUsluga ppu on ppu.PacketPrescrList_id = ppl.PacketPrescrList_id
							inner join v_UslugaComplex uc on uc.UslugaComplex_id = ppu.UslugaComplex_id
							left join v_MedService ms on ms.MedService_id = ppu.MedService_id
							left join v_Lpu l on l.Lpu_id = ms.Lpu_id
						where
							pp.PacketPrescr_id = {$id}
							and ppl.PrescriptionType_id = '13' -- консультационная услуга
					";
				$resultСonsul = $this->db->query($arrСonsul);
				$resultСonsul = $resultСonsul->result('array');

				$resultArr = array(
					'name'=> 'consul',
					'content'=> $resultСonsul
				);

				$result[] = $resultArr;
				}

			if (!empty($data['PacketPrescr_id'])) {
					$arrProcedure = "
						select
							uc.UslugaComplex_id as \"id\",
							uc.UslugaComplex_Name as \"name\"
						from
                        				v_PacketPrescr pp
                        				inner join v_PacketPrescrList ppl on ppl.PacketPrescr_id = pp.PacketPrescr_id
                        				inner join v_PacketPrescrUsluga ppu on ppu.PacketPrescrList_id = ppl.PacketPrescrList_id
							inner join v_UslugaComplex uc on uc.UslugaComplex_id = ppu.UslugaComplex_id
							left join v_MedService ms on ms.MedService_id = ppu.MedService_id
							left join v_Lpu l on l.Lpu_id = ms.Lpu_id
						where
							pp.PacketPrescr_id = {$id}
							and ppl.PrescriptionType_id = '6' -- процедуры и манипуляции
					";
					$resultProc = $this->db->query($arrProcedure);
					$resultProc = $resultProc->result('array');

					$resultArr = array(
						'name'=> 'proc',
						'content'=> $resultProc
					);

					$result[] = $resultArr;
				}
			if (!empty($data['PacketPrescr_id'])) {
					$arrRegime = "
						select
							 ppr.PrescriptionRegimeType_id as \"id\"
							,PRT.PrescriptionRegimeType_Name as \"name\"
						from
							dbo.v_PacketPrescr pp
							inner join dbo.v_PacketPrescrList ppl on ppl.PacketPrescr_id = pp.PacketPrescr_id
							inner join dbo.v_PacketPrescrRegime ppr on ppr.PacketPrescrList_id = ppl.PacketPrescrList_id
							inner join PrescriptionRegimeType PRT on PRT.PrescriptionRegimeType_id = ppr.PrescriptionRegimeType_id
						where
							pp.PacketPrescr_id = {$id}
							and ppl.PrescriptionType_id = '1' -- режимы
					";
				$resultRegime = $this->db->query($arrRegime);
				$resultRegime = $resultRegime->result('array');

				$resultArr = array(
					'name'=> 'regime',
					'content'=> $resultRegime
				);

				$result[] = $resultArr;
				}

			if (!empty($data['PacketPrescr_id'])) {
					$arrDiet = "
						select
							 ppd.PrescriptionDietType_id as \"id\"
							,pdt.PrescriptionDietType_Name as \"name\"
						from
							dbo.v_PacketPrescr pp
							inner join dbo.v_PacketPrescrList ppl on ppl.PacketPrescr_id = pp.PacketPrescr_id
							inner join dbo.v_PacketPrescrDiet ppd on ppd.PacketPrescrList_id = ppl.PacketPrescrList_id
							inner join v_PrescriptionDietType pdt on pdt.PrescriptionDietType_id = ppd.PrescriptionDietType_id
						where
							pp.PacketPrescr_id = {$id}
							and ppl.PrescriptionType_id = '2' -- диеты
					";
				$resultDiet = $this->db->query($arrDiet);
				$resultDiet = $resultDiet->result('array');

				$resultArr = array(
					'name'=> 'diet',
					'content'=> $resultDiet
				);
				$result[] = $resultArr;
				}
		return $result;
	}

	/**
	 * Получение списка пакетов
	 */
	function createPacketPrescrList($PacketPrescr_id, $PrescriptionType_Code, $pmUser_id){
		// Временное явление, пока коды не заменят на текстовые
		$PrescriptionType_id = $this->getPrescriptionTypeId($PrescriptionType_Code);
		if(empty($PrescriptionType_id)) return false;
		$resp_ppl = $this->queryResult("
			select
			 	PacketPrescrList_id as \"PacketPrescrList_id\",
			 	Error_Code as \"Error_Code\",
			 	Error_Message as \"Error_Msg\"
			from p_PacketPrescrList_ins(
				PacketPrescr_id := :PacketPrescr_id,
				PrescriptionType_id := :PrescriptionType_id,
				pmUser_id := :pmUser_id
			)
		", array(
			'PacketPrescr_id' => $PacketPrescr_id,
			'PrescriptionType_id' => $PrescriptionType_id,
			'pmUser_id' => $pmUser_id
		));

		return $resp_ppl[0]['PacketPrescrList_id'];
	}
	/**
	 * Получение списка назначений из пакета с необходимыми параметрами
	 */

	function loadPrescrFromPacket($data, $typePrescr){
		$save_data = $data['encode_data'];
		$comma = '';
		$arrDrugData = array();
		switch($typePrescr){
			case 'drug':
				$filter = 'and pptd.PacketPrescrTreatDrug_id IN (';
				foreach ($save_data['drug'] as $course) {
					foreach ($course as $oneDrug) {
						$filter .= $comma.$oneDrug['PacketPrescrTreatDrug_id'];
						$comma = ',';
					}
				}
				$filter .= ')';
				$res_DrugData = $this->queryResult("
						select
							pptm.PrescriptionIntroType_id as \"PrescriptionIntroType_id\"
							,ppt.PacketPrescrTreat_id as \"PacketPrescrTreat_id\"
							,ppt.PacketPrescrTreat_Duration as \"PacketPrescrTreat_Duration\"
							,ppt.PacketPrescrTreat_MinCountDay as \"PacketPrescrTreat_MinCountDay\"
							,ppt.PacketPrescrTreat_MaxCountDay as \"PacketPrescrTreat_MaxCountDay\"
							,ppt.PacketPrescrTreat_ContReception as \"PacketPrescrTreat_ContReception\"
							,ppt.DurationType_id as \"DurationType_id\"
							,ppt.DurationType_recid as \"DurationType_recid\"
							,ppt.DurationType_intid as \"DurationType_intid\"
							,ppt.PacketPrescrTreat_PrescrCount as \"PacketPrescrTreat_PrescrCount\"
							,pptd.PacketPrescrTreatDrug_id as \"PacketPrescrTreatDrug_id\"
							,pptm.PrescriptionTreatType_id as \"PrescriptionTreatType_id\"
							,pptm.PerformanceType_id as \"PerformanceType_id\"
							,1 as \"MethodInputDrug_id\"
							,pptd.DrugComplexMnn_id as \"DrugComplexMnn_id\"
							,pptd.Drug_id as \"Drug_id\"
							,pptd.ACTMATTERS_ID as \"ACTMATTERS_ID\"
							,pptd.PacketPrescrTreatDrug_KolvoEd as \"KolvoEd\"
							,pptd.PacketPrescrTreatDrug_Kolvo as \"Kolvo\"
							,coalesce(pptd.PacketPrescrTreatDrug_MinDoseDay, pptd.PacketPrescrTreatDrug_MaxDoseDay) as \"DoseDay\"
							,pptd.PacketPrescrTreatDrug_PrescrDose as \"PrescrDose\"
							,pptd.GoodsUnit_id as \"GoodsUnit_id\"
							,pptd.GoodsUnit_sid as \"GoodsUnit_sid\"
							,'new' as \"status\"
								
						from
							dbo.v_PacketPrescr pp
							inner join dbo.v_PacketPrescrList ppl on ppl.PacketPrescr_id = pp.PacketPrescr_id
							inner join dbo.v_PacketPrescrTreat ppt on ppt.PacketPrescrList_id = ppl.PacketPrescrList_id
							left join dbo.v_PacketPrescrTreatDrug pptd on pptd.PacketPrescrTreat_id = ppt.PacketPrescrTreat_id
							left join dbo.v_PacketPrescrTreatMethod pptm on pptm.PacketPrescrTreatMethod_id = pptd.PacketPrescrTreatMethod_id
							left join rls.v_DrugComplexMnnName MnnName on MnnName.ACTMATTERS_id = pptd.ACTMATTERS_ID
							left join rls.v_Drug Drug on Drug.Drug_id = pptd.Drug_id
							left join rls.v_DrugComplexMnn dcm on dcm.DrugComplexMnn_id = coalesce(pptd.DrugComplexMnn_id,Drug.DrugComplexMnn_id)
						where
							pp.PacketPrescr_id = :PacketPrescr_id
							and ppl.PrescriptionType_id = '5' -- лекарственные назначения
							".$filter."
					", array(
					'PacketPrescr_id' => $data['PacketPrescr_id']
				));


				$coursesIDs = array();
				foreach($res_DrugData as $drug){
					if(!in_array($drug['PacketPrescrTreat_id'],$coursesIDs)){
						$coursesIDs[] = $drug['PacketPrescrTreat_id'];
						if(isset($tmp_data))
							unset($tmp_data);
						$tmp_data = array();
						$tmp_data['signature'] = NULL;
						$tmp_data['EvnCourseTreat_id'] = NULL;
						$tmp_data['EvnCourseTreat_pid'] = $data['Evn_pid'];
						$tmp_data['EvnCourseTreat_setDate'] = $data['default_set_date'];
						$tmp_data['EvnCourseTreat_Duration'] = $drug['PacketPrescrTreat_Duration'];
						$tmp_data['EvnCourseTreat_MinCountDay'] = $drug['PacketPrescrTreat_MinCountDay'];
						$tmp_data['EvnCourseTreat_MaxCountDay'] = $drug['PacketPrescrTreat_MaxCountDay'];
						$tmp_data['EvnCourseTreat_ContReception'] = $drug['PacketPrescrTreat_ContReception'];
						$tmp_data['DurationType_id'] = $drug['DurationType_id'];
						$tmp_data['DurationType_recid'] = $drug['DurationType_recid'];
						$tmp_data['DurationType_intid'] = $drug['DurationType_intid'];
						$tmp_data['EvnCourseTreat_CountDay'] = $drug['PacketPrescrTreat_PrescrCount'];
						$tmp_data['MedPersonal_id'] = $data['session']['medpersonal_id'];
						$tmp_data['LpuSection_id'] = $data['session']['CurLpuSection_id'];
						$tmp_data['Morbus_id'] = NULL;
						$tmp_data['PrescriptionIntroType_id'] = $drug['PrescriptionIntroType_id'];
						$tmp_data['PrescriptionTreatType_id'] = $drug['PrescriptionTreatType_id'];
						$tmp_data['PerformanceType_id'] = $drug['PerformanceType_id'];
						$tmp_data['EvnPrescrTreat_IsCito'] = NULL;
						$tmp_data['EvnPrescrTreat_Descr'] = NULL;
						$arrDrugData[$drug['PacketPrescrTreat_id']] = $tmp_data;
					}
					if(isset($DrugData))
						unset($DrugData);
					$DrugData = array();
					$DrugData['id'] = NULL;
					$DrugData['MethodInputDrug_id'] = 1;
					$DrugData['DrugComplexMnn_id'] = $drug['DrugComplexMnn_id'];
					$DrugData['Drug_id'] = $drug['Drug_id'];
					$DrugData['actmatters_id'] = $drug['ACTMATTERS_ID'];
					$DrugData['KolvoEd'] = $drug['KolvoEd'];
					$DrugData['Kolvo'] = $drug['Kolvo'];
					$DrugData['CUBICUNITS_id'] = NULL;
					$DrugData['MASSUNITS_id'] = NULL;
					$DrugData['ACTUNITS_id'] = NULL;
					$DrugData['GoodsUnit_id'] = $drug['GoodsUnit_id'];
					$DrugData['GoodsUnit_sid'] = $drug['GoodsUnit_sid'];
					$DrugData['DoseDay'] = $drug['DoseDay'];
					$DrugData['PrescrDose'] = $drug['PrescrDose'];
					$DrugData['status'] = 'new';
					$arrDrugData[$drug['PacketPrescrTreat_id']]['DrugListDataDecode'][] = $DrugData;
				}
				break;
			default:
				return false;
		}
		return $arrDrugData;
	}

	/**
	 * Установка/снятие избранности для пакета
	 */
	function setPacketFavorite($data){
		if($data['Packet_IsFavorite']){
			$resp_ppl = $this->queryResult("
					select
						PacketPrescrFavour_id as \"PacketPrescrFavour_id\",
						Error_Code as \"Error_Code\",
						Error_Message as \"Error_Msg\"
					from p_PacketPrescrFavour_ins(
						PacketPrescr_id := :PacketPrescr_id,
						MedPersonal_id := :MedPersonal_id,
						pmUser_id := :pmUser_id
					)
				", array(
				'PacketPrescr_id' => $data['PacketPrescr_id'],
				'MedPersonal_id' => $data['MedPersonal_id'],
				'pmUser_id' => $data['pmUser_id']
			));
		}
		else{
			$resp_fav = $this->queryResult("
				select
					PacketPrescrFavour_id as \"PacketPrescrFavour_id\"
				from
					v_PacketPrescrFavour
				where
					PacketPrescr_id = :PacketPrescr_id
					and MedPersonal_id = :MedPersonal_id
			", array(
				'PacketPrescr_id' => $data['PacketPrescr_id'],
				'MedPersonal_id' => $data['MedPersonal_id'],
			));

			if (empty($resp_fav[0]['PacketPrescrFavour_id'])) {
				return array('Error_Msg' => 'Ошибка получения записи о избранности');
			}
			$resp_ppl = $this->queryResult("
					select
						Error_Code as \"Error_Code\",
						Error_Message as \"Error_Msg\"
					from p_PacketPrescrFavour_del(
						PacketPrescrFavour_id := :PacketPrescrFavour_id
					)
				", array(
				'PacketPrescrFavour_id' => $resp_fav[0]['PacketPrescrFavour_id']
			));
		}
		if ( is_object($resp_ppl) )
			return $resp_ppl->result('array');
		else
			return false;
	}

	/**
	 * МАРМ-версия \ MSSQL \ POSTGRE
	 * Установка/снятие избранности для пакета
	 */
	function mSetPacketFavorite($data){
		if($data['Packet_IsFavorite']){
			// Записи начинали дублироваться при множественном передаче параметра Packet_IsFavorite = 2 через API. Поэтому сделал данную проверку
			$countPacket = $this->getFirstResultFromQuery("
				select
					count(PacketPrescrFavour_id)
				from
					v_PacketPrescrFavour
				where
					PacketPrescr_id = :PacketPrescr_id
					and MedPersonal_id = :MedPersonal_id
			", array(
				'PacketPrescr_id' => $data['PacketPrescr_id'],
				'MedPersonal_id' => $data['MedPersonal_id'],
			));

			if ((int) $countPacket >= 1) {
				return array('Error_Msg' => 'Вы уже добавили в избранное данный пакет');
			}
			$resp_ppl = $this->queryResult("
					select
						PacketPrescrFavour_id as \"PacketPrescrFavour_id\",
						Error_Code as \"Error_Code\",
						Error_Message as \"Error_Msg\"
					from p_PacketPrescrFavour_ins(
						PacketPrescr_id := :PacketPrescr_id,
						MedPersonal_id := :MedPersonal_id,
						pmUser_id := :pmUser_id
					)
				", array(
				'PacketPrescr_id' => $data['PacketPrescr_id'],
				'MedPersonal_id' => $data['MedPersonal_id'],
				'pmUser_id' => $data['pmUser_id']
			));
		}
		else{
			$resp_fav = $this->queryResult("
				select
					PacketPrescrFavour_id as \"PacketPrescrFavour_id\"
				from
					v_PacketPrescrFavour
				where
					PacketPrescr_id = :PacketPrescr_id
					and MedPersonal_id = :MedPersonal_id
			", array(
				'PacketPrescr_id' => $data['PacketPrescr_id'],
				'MedPersonal_id' => $data['MedPersonal_id'],
			));


			if (empty($resp_fav[0]['PacketPrescrFavour_id'])) {
				return array('Error_Msg' => 'Ошибка получения записи о избранности');
			}
			$resp_ppl = $this->queryResult("
					select
						Error_Code as \"Error_Code\",
						Error_Message as \"Error_Msg\"
					from p_PacketPrescrFavour_del(
						PacketPrescrFavour_id := :PacketPrescrFavour_id
					)
				", array(
				'PacketPrescrFavour_id' => $resp_fav[0]['PacketPrescrFavour_id']
			));
		}
		if ( is_object($resp_ppl) )
			return $resp_ppl->result('array');
		else
			return false;
	}

	/**
	 * Загрузка формы посещения в новой ЭМК
	 * @param array $data
	 * @return array
	 */
	function loadEditPacketForm($data) {

		$queryParams = array(
			'PacketPrescr_id' => $data['PacketPrescr_id']
		);

		return $this->queryResult("
			select
				pp.PacketPrescr_id as \"PacketPrescr_id\",
				pp.PacketPrescr_Name as \"PacketPrescr_Name\",
				pp.PacketPrescr_Descr as \"PacketPrescr_Descr\",
				pp.PacketPrescrVision_id as \"PacketPrescrVision_id\",
				pp.Sex_id as \"Sex_id\",
				pp.PersonAgeGroup_id as \"PersonAgeGroup_id\",
				substring(Diag_Codes.Codes_str, 1, length(Diag_Codes.Codes_str)-1) as \"Diag_id\"
			from
				v_PacketPrescr pp
				left join v_PacketPrescrLpu ppl on ppl.PacketPrescr_id = pp.PacketPrescr_id
				left join lateral(
					select
						string_agg(CAST(sppd.diag_id as varchar), ',') as Codes_str
					from 
						v_PacketPrescrDiag sppd
					WHERE
						sppd.PacketPrescr_id = pp.PacketPrescr_id
				) Diag_Codes on true 
			where
				pp.PacketPrescr_id = :PacketPrescr_id
		", $queryParams);
	}
	/**
	 * Удаление пакета
	 * @param array $data
	 * @return array
	 */
	function deletePacket($data) {

		$queryParams = array(
			'PacketPrescr_id' => $data['PacketPrescr_id'],
			'pmUser_id' => $data['pmUser_id'],
		);
		return $this->queryResult("
					select
						Error_Code as \"Error_Code\",
						Error_Message as \"Error_Msg\"
					from p_PacketPrescr_delAll(
						PacketPrescr_id := :PacketPrescr_id,
						pmUser_id := :pmUser_id
					)
				", $queryParams);
	}
	/**
	 * Применение пакета
	 * @param array $data
	 * @return array
	 */
	function applyPacketPrescr($data) {
		$save_data = $data['encode_data'];

		$evn_setdt = $this->getFirstResultFromQuery("
			select to_char(evn_setdt, 'dd.mm.yyyy') from v_evn where evn_id = :id
		", array(
			'id' => $data['Evn_pid'],
		));
		$evn_setdt = DateTime::createFromFormat('d.m.Y', $evn_setdt);
		if ($evn_setdt instanceof DateTime) {
			$default_set_date = $evn_setdt->format('Y-m-d');
			$data['default_set_date'] = $default_set_date;
		} else {
			return array(array('success' => false, 'Error_Msg' => 'Не удалось получить дату учетного документа'));
		}

		//Огромный массив данных хранится в сессии, избавимся от него, чтобы обновлять исходные значения ниже
		$lite_data = $data;
		unset($lite_data['session']);

		// Пока операции из пакета не применяем
		/*if (!empty($save_data['oper']) && is_array($save_data['oper']) && count($save_data['oper']) > 0) {
			$tmp_data = $lite_data;
			$tmp_data['EvnPrescrOper_pid'] = $data['Evn_pid'];
			$tmp_data['EvnPrescrOper_id'] = NULL;
			$tmp_data['EvnPrescrOper_setDate'] = $default_set_date;
			$tmp_data['signature'] = NULL;
			$tmp_data['EvnPrescrOper_IsCito'] = NULL;
			$tmp_data['EvnPrescrOper_Descr'] = NULL;
			$this->load->model('EvnPrescrOper_model', 'EvnPrescrOper_model');
			foreach ($save_data['oper'] as $id) {
				if (!empty($id) && is_numeric($id)) {
					$tmp_data['EvnPrescrOper_uslugaList'] = $id;
					$response = $this->EvnPrescrOper_model->doSave($tmp_data);
					if (!empty($response[0]['Error_Msg'])) {
						return array(array('success' => false, 'Error_Msg' => 'Ошибка при сохранении назначения'));
					}
				}
			}
		}*/

		//yl:5588 если проверка лекарств перед применением - поэтому первым делом их
		$DrugMnn_arr=array();//здесь будут МНН из пакета

		// Отложу до готовой структуры БД
		if (!empty($save_data['drug']) && is_array($save_data['drug']) && count($save_data['drug']) > 0) {

			$res_treat = $this->loadPrescrFromPacket($data, 'drug');

			$this->load->model('EvnPrescrTreat_model', 'EvnPrescrTreat_model');
			if(!empty($res_treat)) {
				foreach ($res_treat as $course) {
					ConvertFromWin1251ToUTF8($course['DrugListDataDecode']);
					$course['DrugListData'] = json_encode($course['DrugListDataDecode']);

					if(!empty($data["checkDrug"])){//только для проверки лекарств из пакета
						foreach ($course["DrugListDataDecode"] as $Drug){
							$DrugMnn_arr[]=$Drug["DrugComplexMnn_id"];//здесь нет названий
						}
					}else{//добавление в назначения
						unset($course['DrugListDataDecode']);
						$res_course = array_merge($data, $course);
						$response = $this->EvnPrescrTreat_model->doSaveEvnCourseTreat($res_course);
						if (!empty($response[0]['Error_Msg'])) {
							return array(array('success' => false, 'Error_Msg' => 'Ошибка при сохранении курса: ' . $response[0]['Error_Msg']));
						}
					};
				}
			}
		}
		//yl:проверка лекарств из пакета - выход
		if(!empty($data["checkDrug"])){
			$this->load->model("EvnPrescrTreat_model", "EvnPrescrTreat_model");
			return $this->EvnPrescrTreat_model->checkPersonPrescrTreatPacket($data,$DrugMnn_arr);
		};


		if (!empty($save_data['proc']) && is_array($save_data['proc']) && count($save_data['proc']) > 0) {
			$tmp_data = $data;
			$tmp_data['EvnCourseProc_pid'] = $data['Evn_pid'];
			$tmp_data['EvnCourseProc_id'] = NULL;
			$tmp_data['EvnCourseProc_setDate'] = $default_set_date;
			$tmp_data['MedPersonal_id'] = $data['session']['medpersonal_id'];
			$tmp_data['LpuSection_id'] = $data['session']['CurLpuSection_id'];
			$tmp_data['Morbus_id'] = NULL;
			$tmp_data['signature'] = NULL;
			$tmp_data['EvnPrescrProc_IsCito'] = NULL;
			$tmp_data['EvnPrescrProc_Descr'] = NULL;
			$this->load->model('EvnPrescrProc_model', 'EvnPrescrProc_model');
			foreach ($save_data['proc'] as $proc) {
				if (!empty($proc['UslugaComplex_id']) && is_numeric($proc['UslugaComplex_id'])) {
					$tmp_data['UslugaComplex_id'] = $proc['UslugaComplex_id'];
					$tmp_data['Lpu_id'] = (!empty($proc['Lpu_id']))?$proc['Lpu_id']:null;
					$response = $this->EvnPrescrProc_model->doSaveEvnCourseProc($tmp_data);
					if (!empty($response[0]['Error_Msg'])) {
						return array(array('success' => false, 'Error_Msg' => 'Ошибка при сохранении назначения: ' . $response[0]['Error_Msg']));
					}
				}
			}
		}


		if (!empty($save_data['funcdiag']) && is_array($save_data['funcdiag']) && count($save_data['funcdiag']) > 0) {
			$tmp_data = $data;
			$tmp_data['EvnPrescrFuncDiag_pid'] = $data['Evn_pid'];
			$tmp_data['EvnPrescrFuncDiag_id'] = NULL;
			$tmp_data['EvnPrescrFuncDiag_setDate'] = $default_set_date;
			$tmp_data['signature'] = NULL;
			$tmp_data['EvnPrescrFuncDiag_IsCito'] = NULL;
			$tmp_data['EvnPrescrFuncDiag_Descr'] = NULL;
			$tmp_data['StudyTarget_id'] = NULL;
			//$tmp_data['MedService_id'] = NULL; // пока неоткуда брать idшник
			$this->load->model('EvnPrescrFuncDiag_model', 'EvnPrescrFuncDiag_model');
			foreach ($save_data['funcdiag'] as $func) {
				$tmp_data['MedService_id'] = (!empty($func['MedService_id']))?$func['MedService_id']:null;
				$tmp_data['Lpu_id'] = (!empty($func['Lpu_id']))?$func['Lpu_id']:$lite_data['Lpu_id'];
				$tmp_data['Resource_id'] = (!empty($func['Resource_id']))?$func['Resource_id']:null;
				$tmp_data['StudyTarget_id'] = (!empty($func['StudyTarget_id']))?$func['StudyTarget_id']:null;
				if (!empty($func['UslugaComplex_id']) && is_numeric($func['UslugaComplex_id'])) {
					$tmp_data['EvnPrescrFuncDiag_uslugaList'] = $func['UslugaComplex_id'];
					$response = $this->EvnPrescrFuncDiag_model->doSave($tmp_data);
					if (!empty($response[0]['Error_Msg'])) {
						return array(array('success' => false, 'Error_Msg' => 'Ошибка при сохранении функциональной диагностики: ' . $response[0]['Error_Msg']));
					}
				}
			}
		}


		if (!empty($save_data['labdiag']) && is_array($save_data['labdiag']) && count($save_data['labdiag']) > 0) {
			if(!empty($data['loadComposition'])){
				$this->load->model('MedService_model');
				foreach ($save_data['labdiag'] as $uslugacomplex_pid => $lab) {
					if(!empty($lab['UslugaComposition'])) {
						$UslugaComposition = $lab['UslugaComposition'];
					} else {
						$UslugaComposition = $this->MedService_model->loadCompositionMenu(array(
							'UslugaComplexMedService_pid' => $lab['UslugaComplexMedService_pid'],
							'Lpu_id' => $lab['Lpu_id'],
							'UslugaComplex_pid' => $uslugacomplex_pid,
							'forUslugaList' => true
						));
					}
					if($UslugaComposition)
						$save_data['labdiag'][$uslugacomplex_pid]['UslugaComplex_id'] = $UslugaComposition;
				}
			}
			$tmp_data = $lite_data;
			$tmp_data['EvnPrescrLabDiag_pid'] = $data['Evn_pid'];
			$tmp_data['EvnPrescrLabDiag_id'] = NULL;
			$tmp_data['EvnPrescrLabDiag_setDate'] = $default_set_date;
			$tmp_data['signature'] = NULL;
			$tmp_data['EvnPrescrLabDiag_IsCito'] = NULL;
			$tmp_data['EvnPrescrLabDiag_Descr'] = NULL;
			$tmp_data['StudyTarget_id'] = '2';
			$tmp_data['MedService_pzmid'] = null;
			//$tmp_data['MedService_id'] = NULL; // пока неоткуда брать idшник
			$this->load->model('EvnPrescrLabDiag_model', 'EvnPrescrLabDiag_model');
			foreach ($save_data['labdiag'] as $uslugacomplex_pid => $lab) {
				$tmp_data['MedService_id'] = (!empty($lab['MedService_id']))?$lab['MedService_id']:null;
				$tmp_data['Lpu_id'] = (!empty($lab['Lpu_id']))?$lab['Lpu_id']:$lite_data['Lpu_id'];
				$tmp_data['MedService_pzmid'] = (!empty($lab['MedService_pzmid']))?$lab['MedService_pzmid']:null;
				$tmp_data['UslugaComplex_id'] = $uslugacomplex_pid;
				if(isset($lab['UslugaComplexMedService_pid'])) {
					$tmp_data['UslugaComplexMedService_pid'] = $lab['UslugaComplexMedService_pid'];
				}
				$uslugaList = $lab['UslugaComplex_id'];
				if (is_array($uslugaList) && count($uslugaList) > 0) {
					$tmp_data['EvnPrescrLabDiag_uslugaList'] = implode(',', $uslugaList);
					$tmp_data['EvnPrescrLabDiag_CountComposit'] = count($uslugaList);
					$response = $this->EvnPrescrLabDiag_model->doSave($tmp_data);
					if (!empty($response[0]['Error_Msg'])) {
						return array(array('success' => false, 'Error_Msg' => 'Ошибка при сохранении лабораторной диагностики: ' . $response[0]['Error_Msg']));
					}
				}
			}
		}
		if (!empty($save_data['consusl']) && is_array($save_data['consusl']) && count($save_data['consusl']) > 0) {
			$tmp_data = $lite_data;
			$tmp_data['EvnPrescrConsUsluga_pid'] = $data['Evn_pid'];
			$tmp_data['EvnPrescrConsUsluga_id'] = NULL;
			$tmp_data['EvnPrescrConsUsluga_setDate'] = $default_set_date;
			$tmp_data['signature'] = NULL;
			$tmp_data['DopDispInfoConsent_id'] = NULL;
			$tmp_data['EvnPrescrConsUsluga_IsCito'] = NULL;
			$tmp_data['EvnPrescrConsUsluga_Descr'] = NULL;
			$tmp_data['StudyTarget_id'] = NULL;
			$this->load->model('EvnPrescrConsUsluga_model', 'EvnPrescrConsUsluga_model');
			foreach ($save_data['consusl'] as $cons) {
				$tmp_data['MedService_id'] = (!empty($cons['MedService_id']))?$cons['MedService_id']:null;
				$tmp_data['Lpu_id'] = (!empty($cons['Lpu_id']))?$cons['Lpu_id']:$lite_data['Lpu_id'];
				if (!empty($cons['UslugaComplex_id']) && is_numeric($cons['UslugaComplex_id'])) {
					$tmp_data['UslugaComplex_id'] = $cons['UslugaComplex_id'];
					$response = $this->EvnPrescrConsUsluga_model->doSave($tmp_data);
					if (!empty($response[0]['Error_Msg'])) {
						return array(array('success' => false, 'Error_Msg' => 'Ошибка при сохранении консультативной услуги: ' . $response[0]['Error_Msg']));
					}
				}
			}
		}


		if (!empty($save_data['regime']) && is_array($save_data['regime']) && count($save_data['regime']) > 0) {
			$tmp_data = $lite_data;
			$tmp_data['EvnPrescr_pid'] = $data['Evn_pid'];
			$tmp_data['EvnPrescr_setDate'] = $default_set_date;
			$tmp_data['signature'] = NULL;
			$tmp_data['EvnPrescr_IsCito'] = NULL;
			$tmp_data['EvnPrescr_Descr'] = NULL;
			$tmp_data['StudyTarget_id'] = NULL;
			$this->load->model('EvnPrescrRegime_model', 'EvnPrescrRegime_model');
			foreach ($save_data['regime'] as $regime) {
				$id = $regime['PrescriptionRegimeType_id'];
				$dayNum = $regime['PacketPrescrRegime_Duration'];
				if(empty($dayNum) || !is_numeric($dayNum))
					$dayNum = 1;
				if (!empty($id) && is_numeric($id)) {

					$tmp_data['EvnPrescr_dayNum'] = $dayNum; // сутки
					$tmp_data['PrescriptionRegimeType_id'] = $id;
					$tmp_data['EvnPrescr_id'] = NULL;

					$this->EvnPrescrRegime_model->reset();
					$response = $this->EvnPrescrRegime_model->doSave($tmp_data);
					$data['EvnPrescr_id'] = NULL;
					if (!empty($response[0]['Error_Msg'])) {
						return array(array('success' => false, 'Error_Msg' => 'Ошибка при сохранении режима: ' . $response[0]['Error_Msg']));
					}
				}
			}
		}


		if (!empty($save_data['diet']) && is_array($save_data['diet']) && count($save_data['diet']) > 0) {
			$tmp_data = $lite_data;
			$tmp_data['EvnPrescr_pid'] = $data['Evn_pid'];
			$tmp_data['EvnPrescr_setDate'] = $default_set_date;
			$tmp_data['signature'] = NULL;
			$tmp_data['EvnPrescr_IsCito'] = NULL;
			$tmp_data['EvnPrescr_Descr'] = NULL;
			$tmp_data['StudyTarget_id'] = NULL;
			$this->load->model('EvnPrescrDiet_model', 'EvnPrescrDiet_model');
			foreach ($save_data['diet'] as $diet) {
				$id = $diet['PrescriptionDietType_id'];
				$dayNum = $diet['PacketPrescrDiet_Duration'];
				if(empty($dayNum) || !is_numeric($dayNum))
					$dayNum = 1;
				if (!empty($id) && is_numeric($id)) {
					$tmp_data['EvnPrescr_dayNum'] = $dayNum; // сутки
					$tmp_data['PrescriptionDietType_id'] = $id;
					$tmp_data['EvnPrescr_id'] = NULL;
					$response = $this->EvnPrescrDiet_model->doSave($tmp_data);
					if (!empty($response[0]['Error_Msg'])) {
						return array(array('success' => false, 'Error_Msg' => 'Ошибка при сохранении диеты: ' . $response[0]['Error_Msg']));
					}
				}
			}
		}
		return array(array('success' => true));
	}

	/**
	 * Редактирование пакета по кнопке "Сохранить"
	 * @param array $data
	 * @return array
	 */
	function editPacketPrescr($data) {
		$save_data = $data['encode_data'];
		$object = '';
		foreach($save_data as $typePrescr => $prescrArr){
			switch($typePrescr) {
				case 'drug':
					$tempPrescrArr = array();
					foreach($prescrArr as $course_id => $course){
						$tempPrescrArr[] = $course_id;
						$res = $this->deletePrescrInPacket('PacketPrescrTreatDrug', $course);
						if(is_array($res) && empty($res[0]['success']))
							return $res;
					}
					$object = 'PacketPrescrTreat';
					$prescrArr = $tempPrescrArr;
					break;
				case 'labdiag':
				case 'funcdiag':
				case 'proc':
				case 'consusl':
					$object = 'PacketPrescrUsluga';
					break;
				case 'regime':
					$object = 'PacketPrescrRegime';
					break;
				case 'diet':
					$object = 'PacketPrescrDiet';
					break;
			}
			$res = $this->deletePrescrInPacket($object, $prescrArr);
			if(is_array($res) && empty($res[0]['success']))
				return $res;
		}
		return array(array('success' => true));
	}

	/**
	 * Удаление назначения из пакета
	 * @param string $object
	 * @param array $arrIDs
	 * @return boolean|array
	 */
	function deletePrescrInPacket($object, $arrIDs) {
		if(empty($object) || empty($arrIDs))
			return false;
		foreach($arrIDs as $id) {
			if(empty($id))
				return false;
			$queryParams = array(
				'id' => $id
			);
			$res = $this->queryResult("
					select
						Error_Code as \"Error_Code\",
						Error_Message as \"Error_Msg\"
					from p_{$object}_del(
						{$object}_id := :id
					)
				", $queryParams);
			if (!empty($res[0]['Error_Msg'])) {
				return array(array('success' => false, 'Error_Msg' => $res[0]['Error_Msg']));
			}
		}
		return array(array('success' => true));
	}
	/**
	 * Применение пакета целиком
	 * @param array $data
	 * @return array
	 */
	function applyAllPacketPrescr($data) {
		$save_data = array();
		$queryParams = array(
			'PacketPrescr_id' => $data['PacketPrescr_id']
		);
		$allPrescr = $this->queryResult("
			select
				 ppu.UslugaComplex_id as \"UslugaComplex_id\"
				,ppu.MedService_id as \"MedService_id\"
				,ppl.PrescriptionType_id as \"PrescriptionType_id\"
				,coalesce(ppd.PrescriptionDietType_id,ppr.PrescriptionRegimeType_id) as \"RegimeDietType_id\"
				,coalesce(ppd.PacketPrescrDiet_Duration,ppr.PacketPrescrRegime_Duration) as \"RegimeDiet_Duration\"
				,ppt.PacketPrescrTreat_id as \"PacketPrescrTreat_id\"
				,pptd.PacketPrescrTreatDrug_id as \"PacketPrescrTreatDrug_id\"
				,pptd.DrugComplexMnn_id as \"DrugComplexMnn_id\"
				,pptd.Drug_id as \"Drug_id\"
				,pptd.ACTMATTERS_ID as \"ACTMATTERS_ID\"
			from
				v_PacketPrescr pp
				inner join v_PacketPrescrList ppl on ppl.PacketPrescr_id = pp.PacketPrescr_id
				left join v_PacketPrescrUsluga ppu on ppu.PacketPrescrList_id = ppl.PacketPrescrList_id
				left join dbo.v_PacketPrescrDiet ppd on ppd.PacketPrescrList_id = ppl.PacketPrescrList_id
				left join dbo.v_PacketPrescrRegime ppr on ppr.PacketPrescrList_id = ppl.PacketPrescrList_id
				left join dbo.v_PacketPrescrTreat ppt on ppt.PacketPrescrList_id = ppl.PacketPrescrList_id
				left join dbo.v_PacketPrescrTreatDrug pptd on pptd.PacketPrescrTreat_id = ppt.PacketPrescrTreat_id
			where
				ppl.PrescriptionType_id in (1,2,5,6,11,12,13) 
				and pp.PacketPrescr_id = :PacketPrescr_id
		", $queryParams);
		foreach($allPrescr as $prescr){
			switch($prescr['PrescriptionType_id']){
				case 1: // режим
					$save_data['regime'][] = array(
						'PrescriptionRegimeType_id' => $prescr['RegimeDietType_id'],
						'PacketPrescrRegime_Duration' => $prescr['RegimeDiet_Duration']
					);
					break;
				case 2: // диета
					$save_data['diet'][] = array(
						'PrescriptionDietType_id' => $prescr['RegimeDietType_id'],
						'PacketPrescrDiet_Duration' => $prescr['RegimeDiet_Duration']
					);
					break;
				case 5: // лек. лечение
					$save_data['drug'][$prescr['PacketPrescrTreat_id']][] = array(
						'PacketPrescrTreatDrug_id' => $prescr['PacketPrescrTreatDrug_id'],
						'DrugComplexMnn_id' => $prescr['DrugComplexMnn_id'],
						'Drug_id' => $prescr['Drug_id'],
						'ACTMATTERS_ID' => $prescr['ACTMATTERS_ID']
					);
					break;
				case 6: // манипуляции и процедуры
					$save_data['proc'][] = $prescr['UslugaComplex_id'];
					break;
				case 11: // лаборатор. диагностика
					$save_data['labdiag'][$prescr['UslugaComplex_id']] = array(
						'UslugaComplex_id' => array($prescr['UslugaComplex_id']),
						'MedService_id' => $prescr['MedService_id']
					);
					// было array($prescr['UslugaComplex_id']);
					break;
				case 12: // функциональная/инструментальная диагностика
					$save_data['funcdiag'][] = array(
						'UslugaComplex_id' => $prescr['UslugaComplex_id'],
						'MedService_id' => $prescr['MedService_id']
					);
					break;
				case 13: // Консультационные услуги
					$save_data['consusl'][] = array(
						'UslugaComplex_id' => $prescr['UslugaComplex_id'],
						'MedService_id' => $prescr['MedService_id']
					);
					break;
			}
		}
		$data['encode_data'] = $save_data;
		$res = $this->applyPacketPrescr($data);
		return $res;
	}
	/**
	 * Получение списка шаблонов лек. назначений для врача
	 */
	function loadDrugTemplateList($data){
		$res = $this->queryResult("
				select
					 ppt.PacketPrescrTreat_id as \"PacketPrescrTreat_id\"
					,ppt.PacketPrescrTreat_Name as \"PacketPrescrTreat_Name\"
				from
					dbo.v_PacketPrescrTreat ppt
				where 
					MedPersonal_id = :MedPersonal_id
			  	order by
			  		ppt.PacketPrescrTreat_insDT desc
			  	limit 5
			", array(
			'MedPersonal_id' => $data['MedPersonal_id'],
		));
		return $res;
	}
	/**
	 * Получение списка последних добавленных лекарственных назначений
	 */
	function loadLastSelectedDrugList($data)
	{
		//Оптимизация по задаче #177938 может в лучшие времена доработаем еще

		// Проверяем наличие данных в кэше
		$this->load->library('swCache', array('use'=>'mongo'));
		$cacheObject = 'LastSelectedDrugList';
		// Читаем из кэша
		if ($resCache = $this->swcache->get($cacheObject)) {
			return $resCache;
		} else {
			$res = $this->queryResult("
				select
					Drug.Drug_id as \"Drug_id\",
					dcm.DrugComplexMnn_id as \"DrugComplexMnn_id\",
					coalesce(Drug.Drug_Name, dcm.DrugComplexMnn_RusName, '') as \"Drug_Name\",
					COALESCE(ln.NAME,ACT.LATNAME,dcm.DrugComplexMnn_LatName,'') as \"LatName\"
					,cmnn.ACTMATTERS_ID as \"ActMatters_id\"
				from v_EvnPrescr EP
					inner join v_EvnPrescrTreat EPT on EPT.EvnPrescrTreat_id = EP.EvnPrescr_id
					inner join v_EvnCourseTreat ECT on ECT.EvnCourseTreat_id = EPT.EvnCourse_id
					left join v_EvnCourseTreatDrug ec_drug on ec_drug.EvnCourseTreat_id = EPT.EvnCourse_id
					left join rls.v_Drug Drug on Drug.Drug_id = ec_drug.Drug_id
					left join rls.v_DrugComplexMnn dcm on dcm.DrugComplexMnn_id = COALESCE(ec_drug.DrugComplexMnn_id,Drug.DrugComplexMnn_id)
					left join rls.drugcomplexmnnname cmnn  on cmnn.DrugComplexMnnName_id= dcm.DrugComplexMnnName_id
					left join rls.ACTMATTERS ACT on ACT.ACTMATTERS_ID = cmnn.ACTMATTERS_id
					left join rls.PREP p on p.Prep_id = Drug.DrugPrep_id 
					left join rls.LATINNAMES ln on ln.LATINNAMES_ID = p.LATINNAMEID
				where EP.PrescriptionType_id = 5
					and EP.PrescriptionStatusType_id != 3
					and EP.Lpu_id = :Lpu_id
					and EP.pmUser_insID = :pmUser_id
				order by
					EP.EvnPrescr_setDate DESC
				limit 50
			", array(
				//'MedPersonal_id' => $data['MedPersonal_id'],
				'pmUser_id' => $data['pmUser_id'],
				'Lpu_id' => $data['Lpu_id'],
			));
			if(!empty($res)){
				$res = array_unique($res, SORT_REGULAR);
				// на час кэшируем данные
				$this->swcache->set($cacheObject, $res, array('ttl' => 3600)); // кэшируем на час
			} else
				$res = array();
			return $res;
		}
	}
	/**
	 * Получение данных для формы редактирования курса
	 * @param array $data Массив, полученный методом ProcessInputData контроллера
	 * @return array|boolean
	 */
	function loadPacketCourseTreatEditForm($data) {

		$query = "
			select
				dcm.DrugComplexMnn_id as \"DrugComplexMnn_id\",
				D.Drug_id as \"Drug_id\",
				pptd.ACTMATTERS_ID as \"ACTMATTERS_ID\",
				pptd.PacketPrescrTreatDrug_Kolvo as \"Kolvo\",
				coalesce(D.Drug_Name, dcm.DrugComplexMnn_RusName, '') as \"Drug_Name\",
				pptd.PacketPrescrTreatDrug_KolvoEd as \"KolvoEd\",
				coalesce(df.CLSDRUGFORMS_NameLatinSocr,df.NAME,D.DrugForm_Name,'') as \"DrugForm_Name\",
				DrugComplexMnnDose.DrugComplexMnnDose_Mass as \"DrugComplexMnnDose_Mass\",
				pptd.PacketPrescrTreatDrug_PrescrDose as \"PrescrDose\",
				pptd.PacketPrescrTreatDrug_MaxDoseDay as \"MaxDoseDay\",
				pptd.PacketPrescrTreatDrug_MinDoseDay as \"MinDoseDay\",
				pptd.GoodsUnit_id as \"GoodsUnit_id\",
				pptd.GoodsUnit_sid as \"GoodsUnit_sid\",
				coalesce(pptd.PacketPrescrTreatDrug_MaxDoseDay, pptd.PacketPrescrTreatDrug_MinDoseDay) as \"DoseDay\",
				'' as \"DrugListData\",
				to_char(dbo.tzGetDate(), 'dd.mm.yyyy') as \"EvnCourseTreat_setDate\",
				ppt.PacketPrescrTreat_MaxCountDay as \"EvnCourseTreat_MaxCountDay\",
				ppt.PacketPrescrTreat_MinCountDay as \"EvnCourseTreat_MinCountDay\",
				ppt.PacketPrescrTreat_Duration as \"EvnCourseTreat_Duration\",
				ppt.PacketPrescrTreat_ContReception as \"EvnCourseTreat_ContReception\",
				ppt.DurationType_id as \"DurationType_id\",
				ppt.DurationType_recid as \"DurationType_recid\",
				ppt.DurationType_intid as \"DurationType_intid\",
				ppt.PacketPrescrTreat_PrescrCount as \"EvnCourseTreat_PrescrCount\",
				pptm.PrescriptionTreatType_id as \"PrescriptionTreatType_id\",
				pptm.PerformanceType_id as \"PerformanceType_id\",
				pptm.PrescriptionIntroType_id as \"PrescriptionIntroType_id\",
				COALESCE(ln.NAME,ACT.LATNAME,dcm.DrugComplexMnn_LatName,'') as \"LatName\"
				,ppt.PacketPrescrTreat_id as \"PacketPrescrTreat_id\"
				,pptd.PacketPrescrTreatDrug_id as \"PacketPrescrTreatDrug_id\"
				,pptd.PacketPrescrTreatMethod_id as \"PacketPrescrTreatMethod_id\"
				,'new' as \"status\"
			from
				v_PacketPrescrTreat ppt
				left join dbo.v_PacketPrescrTreatDrug pptd on pptd.PacketPrescrTreat_id = ppt.PacketPrescrTreat_id
				left join dbo.v_PacketPrescrTreatMethod pptm on pptm.PacketPrescrTreatMethod_id = pptd.PacketPrescrTreatMethod_id

				left join rls.Drug D on D.Drug_id = pptd.Drug_id
				left join rls.v_DrugComplexMnn dcm on dcm.DrugComplexMnn_id = coalesce(pptd.DrugComplexMnn_id,D.DrugComplexMnn_id)
				left join rls.DrugComplexMnnDose on DrugComplexMnnDose.DrugComplexMnnDose_id = dcm.DrugComplexMnnDose_id
				left join rls.CLSDRUGFORMS df on dcm.CLSDRUGFORMS_ID = df.CLSDRUGFORMS_ID
				left join rls.drugcomplexmnnname cmnn  on cmnn.DrugComplexMnnName_id= dcm.DrugComplexMnnName_id
				left join rls.ACTMATTERS ACT on ACT.ACTMATTERS_ID = cmnn.ACTMATTERS_id
				left join rls.PREP p on p.Prep_id = D.DrugPrep_id 
				left join rls.LATINNAMES ln on ln.LATINNAMES_ID = p.LATINNAMEID
			where
				ppt.PacketPrescrTreat_id  = :PacketPrescrTreat_id
		";
		$queryParams = array(
			'PacketPrescrTreat_id' => $data['PacketPrescrTreat_id']
		);
		// echo getDebugSQL($query, $queryParams); exit();
		$result = $this->db->query($query, $queryParams);

		if ( !is_object($result) ) {
			return false;
		}
		$result = $result->result('array');
		$response = array();
		$drugListData = array();
		foreach ($result as $row) {
			$doseDay = '';
			if(!empty($row['EvnCourseTreat_MaxCountDay']) && !empty($row['Kolvo']) && !empty($row['EdUnits_Nick'])){
				$doseDay = ($row['EvnCourseTreat_MaxCountDay']*$row['Kolvo']).' '.$row['EdUnits_Nick'];
			}else if(!empty($row['EvnPrescrTreatDrug_DoseDay'])){
				$doseDay = $row['EvnPrescrTreatDrug_DoseDay'];
			}
			$drug = array();
			//$drug['id'] = $row['id'];
			//$drug['MethodInputDrug_id'] = $row['MethodInputDrug_id'];
			$drug['DrugComplexMnn_id'] = $row['DrugComplexMnn_id'];
			$drug['Drug_id'] = $row['Drug_id'];
			$drug['Drug_Name'] = $row['Drug_Name'];
			$drug['Kolvo'] = round($row['Kolvo'], 5);
			//$drug['EdUnits_id'] = $row['EdUnits_id'];
			//$drug['EdUnits_Nick'] = $row['EdUnits_Nick'];
			$drug['KolvoEd'] = round($row['KolvoEd'], 5);
			$drug['DrugForm_Name'] = $row['DrugForm_Name'];
			$drug['DrugComplexMnnDose_Mass'] = round($row['DrugComplexMnnDose_Mass'], 5);
			$drug['PrescrDose'] = $row['PrescrDose'];
			//$drug['FactDose'] = $row['FactDose'];
			$drug['DoseDay'] = (!empty($doseDay) ? $doseDay : $row['MaxDoseDay']);//or $row['MinDoseDay'] ?
			$drug['MaxDoseDay'] = $row['MaxDoseDay'];//нужно только для пересчета курса!!!
			$drug['MinDoseDay'] = $row['MinDoseDay'];//нужно только для пересчета курса!!!
			//$drug['FactCount'] = $row['FactCount'];
			$drug['GoodsUnit_id'] = $row['GoodsUnit_id'];
			$drug['GoodsUnit_sid'] = $row['GoodsUnit_sid'];
			$drug['LatName'] = $row['LatName'];
			array_walk($drug,'ConvertFromWin1251ToUTF8');
			$drugListData[] = $drug;
		}
		if (!empty($drugListData)) {
			array_walk($result[0],'ConvertFromWin1251ToUTF8');

			//unset($result[0]['id']);
			//unset($result[0]['MethodInputDrug_id']);
			unset($result[0]['DrugComplexMnn_id']);
			unset($result[0]['Drug_id']);
			unset($result[0]['Drug_Name']);
			//unset($result[0]['EdUnits_Nick']);
			unset($result[0]['Kolvo']);
			//unset($result[0]['EdUnits_id']);
			unset($result[0]['KolvoEd']);
			unset($result[0]['DrugForm_Name']);
			unset($result[0]['DrugComplexMnnDose_Mass']);
			unset($result[0]['PrescrDose']);
			//unset($result[0]['FactDose']);
			unset($result[0]['MaxDoseDay']);
			unset($result[0]['MinDoseDay']);
			//unset($result[0]['FactCount']);
			unset($result[0]['GoodsUnit_id']);
			unset($result[0]['GoodsUnit_sid']);
			$result[0]['DrugListData'] = json_encode($drugListData);

			$result[0]['EvnCourseTreat_CountDay'] = $result[0]['EvnCourseTreat_MaxCountDay'];

			$response[] = $result[0];
		}
		return $response;
	}
	/**
	 * @param array $data Массив, полученный методом ProcessInputData контроллера
	 * @return array|boolean
	 */
	function saveDrugTemplate($data) {
		$treatCourseData = array();
		$TreatDrugArr = is_array($data['DrugListData'])?$data['DrugListData']:json_decode($data['DrugListData'], true);

		$PacketPrescrList_id = null;
		if(!empty($data['PacketPrescr_id']))
			$PacketPrescrList_id = $this->findPacketPrescrList($data, 'Treat');

		if(is_array($TreatDrugArr) && count($TreatDrugArr) && !is_numeric(key($TreatDrugArr))) {
			$TreatDrugArr = [$TreatDrugArr];
		}

		// СОХРАНЕНИЕ В ПАКЕТ ЛЕКАРСТВЕННЫХ НАЗНАЧЕНИЙ
		$this->beginTransaction();
		if (!empty($TreatDrugArr)) {
			// сохраняем шаблона курса назначений лек. средств
			$param_course = array(
				'PacketPrescrList_id' => $PacketPrescrList_id, // Этот шаблон лек. назначения не будет привязан к пакету
				'PacketPrescrTreat_Name' => (!empty($data['Template_Name'])?$data['Template_Name']:''),
				'MedPersonal_id' => $data['MedPersonal_id'],
				'PacketPrescrTreat_Duration' => $data['EvnCourseTreat_Duration'],
				'PacketPrescrTreat_MinCountDay' => $data['EvnCourseTreat_CountDay'],
				'PacketPrescrTreat_MaxCountDay' => $data['EvnCourseTreat_CountDay'],
				'PacketPrescrTreat_ContReception' => $data['EvnCourseTreat_ContReception'],
				'PacketPrescrTreat_Interval' => $data['EvnCourseTreat_Interval'],
				'PacketPrescrTreat_PrescrCount' => $data['EvnCourseTreat_CountDay'],
				'DurationType_id' => $data['DurationType_id'],
				'DurationType_recid' => $data['DurationType_recid'],
				'DurationType_intid' => $data['DurationType_intid'],
				'pmUser_id' => $data['pmUser_id']
			);
			$resp_course = $this->queryResult("
				select
					PacketPrescrTreat_id as \"PacketPrescrTreat_id\",
					Error_Code as \"Error_Code\",
					Error_Message as \"Error_Msg\"
				from p_PacketPrescrTreat_ins(
					PacketPrescrTreat_Name := :PacketPrescrTreat_Name,
					MedPersonal_id := :MedPersonal_id,
					PacketPrescrList_id := :PacketPrescrList_id,
					PacketPrescrTreat_Duration := :PacketPrescrTreat_Duration,
					PacketPrescrTreat_MinCountDay := :PacketPrescrTreat_MinCountDay,
					PacketPrescrTreat_MaxCountDay := :PacketPrescrTreat_MaxCountDay,
					PacketPrescrTreat_ContReception := :PacketPrescrTreat_ContReception,
					PacketPrescrTreat_Interval := :PacketPrescrTreat_Interval,
					PacketPrescrTreat_PrescrCount := :PacketPrescrTreat_PrescrCount,
					DurationType_id := :DurationType_id,
					DurationType_recid := :DurationType_recid,
					DurationType_intid := :DurationType_intid,
					pmUser_id := :pmUser_id
				)
			", $param_course);
			if (empty($resp_course[0]['PacketPrescrTreat_id'])) {
				$this->rollbackTransaction();
				return array('Error_Msg' => 'Ошибка сохранения лекарства в пакете назначений');
			}
			$treatCourseData['Course_id'] = $resp_course[0]['PacketPrescrTreat_id'];

			// сохраняем шаблон метода применения для определенного курса
			$param_method = array(
				'PrescriptionIntroType_id' => $data['PrescriptionIntroType_id'],
				'PrescriptionTreatType_id' => $data['PrescriptionTreatType_id'],
				'PerformanceType_id' => $data['PerformanceType_id'],
				'PrescriptionTimeType_id' => null, // Время приема работает только на Самаре
				'PrescriptionTreatOrderType_id' => null, // Порядок приема работает только на Самаре
				'PacketPrescrTreatMethod_IsPrescrInfusion' => null, // Инфузия, где ее брать?
				'pmUser_id' => $data['pmUser_id']
			);
			$resp_method = $this->queryResult("
				select
					PacketPrescrTreatMethod_id as \"PacketPrescrTreatMethod_id\",
					Error_Code as \"Error_Code\",
					Error_Message as \"Error_Msg\"
				from p_PacketPrescrTreatMethod_ins(
					PrescriptionIntroType_id := :PrescriptionIntroType_id,
					PrescriptionTreatType_id := :PrescriptionTreatType_id,
					PerformanceType_id := :PerformanceType_id,
					PrescriptionTimeType_id := :PrescriptionTimeType_id,
					PrescriptionTreatOrderType_id := :PrescriptionTreatOrderType_id,
					PacketPrescrTreatMethod_IsPrescrInfusion := :PacketPrescrTreatMethod_IsPrescrInfusion,
					pmUser_id := :pmUser_id
				)
			", $param_method);
			if (empty($resp_method[0]['PacketPrescrTreatMethod_id'])) {
				$this->rollbackTransaction();
				return array('Error_Msg' => 'Ошибка сохранения лекарства в пакете назначений');
			}
			$treatCourseData['Method_id'] = $resp_method[0]['PacketPrescrTreatMethod_id'];

			foreach($TreatDrugArr as $key => $TreatDrug){
				// сохраняем шаблон самого лек. назначения с сохраненным ранее методом применения для курса и самим курсом
				$param_drug = array(
					// id курса из пакета для определенного курса лек. назначений из случая лечения
					'PacketPrescrTreat_id' => $treatCourseData['Course_id'],
					'Drug_id' => $TreatDrug['Drug_id'],
					'ACTMATTERS_ID' => null,
					'DrugComplexMnn_id' => $TreatDrug['DrugComplexMnn_id'],
					'PacketPrescrTreatDrug_Kolvo' => $TreatDrug['Kolvo'],
					'PacketPrescrTreatDrug_KolvoEd' => $TreatDrug['KolvoEd'],
					'GoodsUnit_id' => $TreatDrug['GoodsUnit_id'],
					'GoodsUnit_sid' => $TreatDrug['GoodsUnit_sid'],
					'PacketPrescrTreatDrug_MinDoseDay' => $TreatDrug['DoseDay'],
					'PacketPrescrTreatDrug_MaxDoseDay' => $TreatDrug['DoseDay'],
					'PacketPrescrTreatDrug_PrescrDose' => $TreatDrug['PrescrDose'],
					// id метода для определенного курса лек. назначений
					'PacketPrescrTreatMethod_id' => $treatCourseData['Method_id'],
					'pmUser_id' => $data['pmUser_id']
				);
				$resp_drug = $this->queryResult("
				select
					PacketPrescrTreatDrug_id as \"PacketPrescrTreatDrug_id\",
					Error_Code as \"Error_Code\",
					Error_Message as \"Error_Msg\"
				from p_PacketPrescrTreatDrug_ins(
					PacketPrescrTreat_id := :PacketPrescrTreat_id,
					Drug_id := :Drug_id,
					ACTMATTERS_ID := :ACTMATTERS_ID,
					DrugComplexMnn_id := :DrugComplexMnn_id,
					PacketPrescrTreatDrug_Kolvo := :PacketPrescrTreatDrug_Kolvo,
					PacketPrescrTreatDrug_KolvoEd := :PacketPrescrTreatDrug_KolvoEd,
					GoodsUnit_id := :GoodsUnit_id,
					GoodsUnit_sid := :GoodsUnit_sid,
					PacketPrescrTreatDrug_MinDoseDay := :PacketPrescrTreatDrug_MinDoseDay,
					PacketPrescrTreatDrug_MaxDoseDay := :PacketPrescrTreatDrug_MaxDoseDay,
					PacketPrescrTreatDrug_PrescrDose := :PacketPrescrTreatDrug_PrescrDose,
					PacketPrescrTreatMethod_id := :PacketPrescrTreatMethod_id,
					pmUser_id := :pmUser_id
				)
			", $param_drug);
				if (empty($resp_drug[0]['PacketPrescrTreatDrug_id'])) {
					$this->rollbackTransaction();
					return array('Error_Msg' => 'Ошибка сохранения лекарства в пакете назначений');
				}
				$treatCourseData['TreatDrug'] = $resp_drug[0]['PacketPrescrTreatDrug_id'];
			}
		}

		$this->commitTransaction();

		return array('Error_Msg' => '', 'Data' => $treatCourseData);
	}
	/**
	 * Удаление шаблона лек
	 * @param array $data
	 * @return array
	 */
	function deletePacketPrescrTreat($data) {

		$queryParams = array(
			'PacketPrescrTreat_id' => $data['PacketPrescrTreat_id']
		);
		return $this->queryResult("
					select
						Error_Code as \"Error_Code\",
						Error_Message as \"Error_Msg\"
					from p_PacketPrescrTreat_del(
						PacketPrescrTreat_id := :PacketPrescrTreat_id
					)
				", $queryParams);
	}
	/**
	 * Создание нового пустого пакета назначений
	 * @param array $data
	 * @return array
	 */
	function createEmptyPacketPrescr($data)
	{
		$this->beginTransaction();
		$curdate = new DateTime();
		$date_str = $curdate->format('d/m/Y');
		// 1. сохраняем сам пакет
		$resp_packet = $this->queryResult("
			select
				PacketPrescr_id as \"PacketPrescr_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from p_PacketPrescr_ins(
				PacketPrescr_Name := :PacketPrescr_Name,
				PacketPrescr_Descr := :PacketPrescr_Descr,
				PacketPrescrVision_id := :PacketPrescrVision_id,
				PersonAgeGroup_id := :PersonAgeGroup_id,
				Sex_id := :Sex_id,
				MedPersonal_id := :MedPersonal_id,
				pmUser_id := :pmUser_id
			)
		", array(
			'PacketPrescr_Name' => $data['PacketPrescr_Name'].' '.$date_str,
			'PacketPrescr_Descr' => null,
			'PacketPrescrVision_id' => $data['PacketPrescrVision_id'],
			'PersonAgeGroup_id' => (!empty($data['PersonAgeGroup_id']))?$data['PersonAgeGroup_id']:null,
			'Sex_id' => (!empty($data['Sex_id']))?$data['Sex_id']:null,
			'pmUser_id' => $data['pmUser_id'],
			'MedPersonal_id' => $data['MedPersonal_id']
		));
		if (empty($resp_packet[0]['PacketPrescr_id'])) {
			$this->rollbackTransaction();
			return array('Error_Msg' => 'Ошибка сохранения пакета назначений');
		}

		// 2. сохраняем связь пакета с Lpu
		$resp_lpu = $this->queryResult("
			select
				PacketPrescrLpu_id as \"PacketPrescrLpu_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from p_PacketPrescrLpu_ins(
				PacketPrescr_id := :PacketPrescr_id,
				Lpu_id := :Lpu_id,
				LpuSection_id := :LpuSection_id,
				LpuBuilding_id := :LpuBuilding_id,
				pmUser_id := :pmUser_id
			)
		", array(
			'PacketPrescr_id' => $resp_packet[0]['PacketPrescr_id'],
			'Lpu_id' => $data['Lpu_id'],
			'LpuSection_id' => (!empty($data['session']['CurLpuSection_id']))?$data['session']['CurLpuSection_id']:null,
			'LpuBuilding_id' => (!empty($data['session']['CurARM']) && !empty($data['session']['CurARM']['LpuBuilding_id']))?$data['session']['CurARM']['LpuBuilding_id']:null,
			'pmUser_id' => $data['pmUser_id']
		));
		if (empty($resp_lpu[0]['PacketPrescrLpu_id'])) {
			$this->rollbackTransaction();
			return array('Error_Msg' => 'Ошибка сохранения пакета назначений');
		}

		// 3. Сохраняем связь пакета с диагнозом
		if(!empty($data['Diag_id'])) {
			$diags = explode(",", $data['Diag_id']);
			foreach ($diags as $diag) {
				if (!empty($diag) && intval($diag)>0) {
					$resp_ppd = $this->queryResult("
						select
							PacketPrescrDiag_id as \"PacketPrescrDiag_id\",
							Error_Code as \"Error_Code\",
							Error_Message as \"Error_Msg\"
						from p_PacketPrescrDiag_ins(
							PacketPrescr_id := :PacketPrescr_id,
							Diag_id := :Diag_id,
							pmUser_id := :pmUser_id
						)
						", array(
						'PacketPrescr_id' => $resp_packet[0]['PacketPrescr_id'],
						'Diag_id' => $diag,
						'pmUser_id' => $data['pmUser_id']
					));

					if (empty($resp_ppd[0]['PacketPrescrDiag_id'])) {
						$this->rollbackTransaction();
						return array('Error_Msg' => 'Ошибка сохранения диагноза пакета назначений');
					}
				}
			}
		}
		$this->commitTransaction();

		return array('Error_Msg' => '','PacketPrescr_id' => $resp_packet[0]['PacketPrescr_id']);
	}
	/**
	 * Создание копии пакета
	 * @param array $data
	 * @return array
	 */
	function copyPacket($data) {

		$queryParams = array(
			'PacketPrescr_id' => $data['PacketPrescr_id'],
			'PacketPrescr_Name' => $data['PacketPrescr_Name']?$data['PacketPrescr_Name']:null,
			'pmUser_id' => $data['pmUser_id'],
		);
		return $this->queryResult("
					select
						PacketPrescr_NewId as \"PacketPrescr_id\",
						Error_Code as \"Error_Code\",
						Error_Message as \"Error_Msg\"
					from p_PacketPrescr_Copy(
						PacketPrescr_id := :PacketPrescr_id,
						PacketPrescr_Name := :PacketPrescr_Name,
						pmUser_id := :pmUser_id
					)
				", $queryParams);
	}
	/**
	 * Добавление режима в пакет
	 * @param array $data
	 * @param int|boolean $PacketPrescrList_id
	 * @return array|boolean
	 */
	function createPacketPrescrRegime($data,$PacketPrescrList_id = false) {

		if(empty($PacketPrescrList_id) && empty($data['PacketPrescr_id'])){
			return false;
		}
		if(empty($PacketPrescrList_id))
			$PacketPrescrList_id = $this->findPacketPrescrList($data,'Regime');

		if(empty($PacketPrescrList_id)){
			return false;
		}

		return $this->queryResult("
						select
							PacketPrescrRegime_id as \"PacketPrescrRegime_id\",
							Error_Code as \"Error_Code\",
							Error_Message as \"Error_Msg\"
						from p_PacketPrescrRegime_ins(
							PacketPrescrList_id := :PacketPrescrList_id,
							PrescriptionRegimeType_id := :PrescriptionRegimeType_id,
							PacketPrescrRegime_Duration := :PacketPrescrRegime_Duration,
							pmUser_id := :pmUser_id
						)
					", array(
			'PacketPrescrList_id' => $PacketPrescrList_id,
			'PrescriptionRegimeType_id' => $data['PrescriptionRegimeType_id'],
			'PacketPrescrRegime_Duration' => !empty($data['PacketPrescrRegime_Duration'])?$data['PacketPrescrRegime_Duration']:null,
			'pmUser_id' => $data['pmUser_id']
		));
	}
	/**
	 * Добавление диеты в пакет
	 * @param array $data
	 * @param int|boolean $PacketPrescrList_id
	 * @return array|boolean
	 */
	function createPacketPrescrDiet($data,$PacketPrescrList_id = false) {

		if(empty($PacketPrescrList_id) && empty($data['PacketPrescr_id'])){
			return false;
		}
		if(empty($PacketPrescrList_id))
			$PacketPrescrList_id = $this->findPacketPrescrList($data,'Diet');

		if(empty($PacketPrescrList_id)){
			return false;
		}

		return $this->queryResult("
						select
							PacketPrescrDiet_id as \"PacketPrescrDiet_id\",
							Error_Code as \"Error_Code\",
							Error_Message as \"Error_Msg\"
						from p_PacketPrescrDiet_ins(
							PacketPrescrList_id := :PacketPrescrList_id,
							PrescriptionDietType_id := :PrescriptionDietType_id,
							PacketPrescrDiet_Duration := :PacketPrescrDiet_Duration,
							pmUser_id := :pmUser_id
						)
					", array(
			'PacketPrescrList_id' => $PacketPrescrList_id,
			'PrescriptionDietType_id' => $data['PrescriptionDietType_id'],
			'PacketPrescrDiet_Duration' => !empty($data['PacketPrescrDiet_Duration'])?$data['PacketPrescrDiet_Duration']:null,
			'pmUser_id' => $data['pmUser_id']
		));
	}
	/**
	 * Добавление услуги (процедуры и малнипуляции) в пакет
	 * @param array $data
	 * @param int|boolean $PacketPrescrList_id
	 * @return array|boolean
	 */
	function createPacketPrescrProc($data,$PacketPrescrList_id = false) {

		if(empty($PacketPrescrList_id) && empty($data['PacketPrescr_id'])){
			return false;
		}
		if(empty($PacketPrescrList_id))
			$PacketPrescrList_id = $this->findPacketPrescrList($data,'Proc');

		if(empty($PacketPrescrList_id)){
			return false;
		}

		return $this->queryResult("
				select
					PacketPrescrUsluga_id as \"PacketPrescrUsluga_id\",
					Error_Code as \"Error_Code\",
					Error_Message as \"Error_Msg\"
				from p_PacketPrescrUsluga_ins(
					PacketPrescrList_id := :PacketPrescrList_id,
					UslugaComplex_id := :UslugaComplex_id,
					StudyTarget_id := :StudyTarget_id,
					PacketPrescrUsluga_Count := :PacketPrescrUsluga_Count,
					PacketPrescrUsluga_DailyIterCount := :PacketPrescrUsluga_DailyIterCount,
					PacketPrescrUsluga_Duration := :PacketPrescrUsluga_Duration,
					PacketPrescrUsluga_RepeatNonstop := :PacketPrescrUsluga_RepeatNonstop,
					PacketPrescrUsluga_Break := :PacketPrescrUsluga_Break,
					DurationType_did := :DurationType_did,
					DurationType_rid := :DurationType_rid,
					DurationType_bid := :DurationType_bid,
					MedService_id := :MedService_id,
					pmUser_id := :pmUser_id
				)
			", array(
			'PacketPrescrList_id' => $PacketPrescrList_id,
			'UslugaComplex_id' => $data['UslugaComplex_id'],
			'StudyTarget_id' => !empty($data['StudyTarget_id'])?$data['StudyTarget_id']:NULL,
			'PacketPrescrUsluga_Count' => !empty($data['PacketPrescrUsluga_Count'])?$data['PacketPrescrUsluga_Count']:NULL,
			'PacketPrescrUsluga_DailyIterCount' => !empty($data['PacketPrescrUsluga_DailyIterCount'])?$data['PacketPrescrUsluga_DailyIterCount']:NULL,
			'PacketPrescrUsluga_Duration' => !empty($data['PacketPrescrUsluga_Duration'])?$data['PacketPrescrUsluga_Duration']:NULL,
			'PacketPrescrUsluga_RepeatNonstop' => !empty($data['PacketPrescrUsluga_RepeatNonstop'])?$data['PacketPrescrUsluga_RepeatNonstop']:NULL,
			'PacketPrescrUsluga_Break' => !empty($data['PacketPrescrUsluga_Break'])?$data['PacketPrescrUsluga_Break']:NULL,
			'DurationType_did' => !empty($data['DurationType_did'])?$data['DurationType_did']:NULL,
			'DurationType_rid' => !empty($data['DurationType_rid'])?$data['DurationType_rid']:NULL,
			'DurationType_bid' => !empty($data['DurationType_bid'])?$data['DurationType_bid']:NULL,
			'MedService_id' => $data['MedService_id'],
			'pmUser_id' => $data['pmUser_id']
		));
	}
	/**
	 * Добавление услуги в пакет
	 * @param array $data
	 * @param int|boolean $PacketPrescrList_id
	 * @return array|boolean
	 */
	function createPacketPrescrUsl($data,$PacketPrescrList_id = false) {

		if(empty($PacketPrescrList_id) && empty($data['PacketPrescr_id'])){
			return false;
		}
		$type = $data['PrescriptionType_Code']; //
		if(empty($PacketPrescrList_id))
			$PacketPrescrList_id = $this->findPacketPrescrList($data, $type);

		if(empty($PacketPrescrList_id)){
			return false;
		}

		return $this->queryResult("
							select
								PacketPrescrUsluga_id as \"PacketPrescrUsluga_id\",
								Error_Code as \"Error_Code\",
								Error_Message as \"Error_Msg\"
							from p_PacketPrescrUsluga_ins(
								PacketPrescrList_id := :PacketPrescrList_id,
								UslugaComplex_id := :UslugaComplex_id,
								StudyTarget_id := :StudyTarget_id,
								PacketPrescrUsluga_Count := :PacketPrescrUsluga_Count,
								PacketPrescrUsluga_DailyIterCount := :PacketPrescrUsluga_DailyIterCount,
								PacketPrescrUsluga_Duration := :PacketPrescrUsluga_Duration,
								PacketPrescrUsluga_RepeatNonstop := :PacketPrescrUsluga_RepeatNonstop,
								PacketPrescrUsluga_Break := :PacketPrescrUsluga_Break,
								DurationType_did := :DurationType_did,
								DurationType_rid := :DurationType_rid,
								DurationType_bid := :DurationType_bid,
								MedService_id := :MedService_id,
								pmUser_id := :pmUser_id
							)
						", array(
			'PacketPrescrList_id' => $PacketPrescrList_id,
			'UslugaComplex_id' => $data['UslugaComplex_id'],
			'StudyTarget_id' => !empty($data['StudyTarget_id'])?$data['StudyTarget_id']:NULL,
			'PacketPrescrUsluga_Count' => !empty($data['PacketPrescrUsluga_Count'])?$data['PacketPrescrUsluga_Count']:1,
			'PacketPrescrUsluga_DailyIterCount' => !empty($data['PacketPrescrUsluga_DailyIterCount'])?$data['PacketPrescrUsluga_DailyIterCount']:NULL,
			'PacketPrescrUsluga_Duration' => !empty($data['PacketPrescrUsluga_Duration'])?$data['PacketPrescrUsluga_Duration']:NULL,
			'PacketPrescrUsluga_RepeatNonstop' => !empty($data['PacketPrescrUsluga_RepeatNonstop'])?$data['PacketPrescrUsluga_RepeatNonstop']:NULL,
			'PacketPrescrUsluga_Break' => !empty($data['PacketPrescrUsluga_Break'])?$data['PacketPrescrUsluga_Break']:NULL,
			'DurationType_did' => !empty($data['DurationType_did'])?$data['DurationType_did']:NULL,
			'DurationType_rid' => !empty($data['DurationType_rid'])?$data['DurationType_rid']:NULL,
			'DurationType_bid' => !empty($data['DurationType_bid'])?$data['DurationType_bid']:NULL,
			'MedService_id' => $data['MedService_id'],
			'pmUser_id' => $data['pmUser_id']
		));
	}
	/**
	 * Поиск листа назначений определенного типа по пакету
	 * @param array $data
	 * @return int|boolean
	 */
	function findPacketPrescrList($data,$PrescriptionType_Code) {

		if(empty($data['PacketPrescr_id']) || empty($PrescriptionType_Code)){
			return false;
		}
		$PrescriptionType_id = $this->getPrescriptionTypeId($PrescriptionType_Code);
		$PacketPrescr_id = $data['PacketPrescr_id'];
		if(empty($PacketPrescrList_id)){
			$query = "
				SELECT
					ppl.PacketPrescrList_id as \"PacketPrescrList_id\"
				FROM v_PacketPrescrList ppl
				WHERE 
					ppl.PacketPrescr_id = :PacketPrescr_id
					AND ppl.PrescriptionType_id = :PrescriptionType_id
				limit 1
			";
			$res = $this->getFirstRowFromQuery($query, array(
				'PacketPrescr_id' => $PacketPrescr_id,
				'PrescriptionType_id' => $PrescriptionType_id,
			));
			$PacketPrescrList_id = $res?$res['PacketPrescrList_id']:false;
		}

		if($PacketPrescrList_id === false)
			$PacketPrescrList_id = $this->createPacketPrescrList($PacketPrescr_id,$PrescriptionType_Code,$data['pmUser_id']);

		return $PacketPrescrList_id;
	}
	/**
	 * id типа назначения (листа) по code
	 * @param int $PrescriptionType_Code
	 * @return int|boolean
	 */
	function getPrescriptionTypeId($PrescriptionType_Code) {
		switch($PrescriptionType_Code){
			case 'Cons':
			case 'EvnPrescrConsUsluga':
			case 'ConsUslData':
				$PrescriptionType_id = 13;
				break;
			case 'FuncDiag':
			case 'EvnPrescrFuncDiag':
			case 'FuncDiagData':
				$PrescriptionType_id = 12;
				break;
			case 'LabDiag':
			case 'EvnPrescrLabDiag':
			case 'LabDiagData':
				$PrescriptionType_id = 11;
				break;
			case 'Proc':
			case 'EvnCourseProc':
			case 'ProcData':
				$PrescriptionType_id = 6;
				break;
			case 'Treat':
			case 'EvnCourseTreat':
			case 'DrugData':
				$PrescriptionType_id = 5;
				break;
			case 'Diet':
			case 'DietData':
			case 'EvnPrescrDiet':
				$PrescriptionType_id = 2;
				break;
			case 'Regime':
			case 'RegimeData':
			case 'EvnPrescrRegime':
				$PrescriptionType_id = 1;
				break;
			case 'Oper':
			case 'EvnPrescrOperBlock':
			case 'OperBlockData':
				$PrescriptionType_id = 7;
				break;
			default:
				return false;
				break;
		}
		return $PrescriptionType_id;
	}
	function getFuncDiagData($data) {
		if (!empty($data['save_data']['funcdiag'])) {
			$id = $data['PacketPrescr_id'];
			$uclist = implode(",", $data['save_data']['funcdiag']);
			return $this->queryResult("
						select
							coalesce(ms.MedService_id,0) as \"MedService_id\",
							uc.UslugaComplex_id as \"UslugaComplex_id\"
						from
                        				v_PacketPrescr pp
                        				inner join v_PacketPrescrList ppl on ppl.PacketPrescr_id = pp.PacketPrescr_id
                        				inner join v_PacketPrescrUsluga ppu on ppu.PacketPrescrList_id = ppl.PacketPrescrList_id
							inner join v_UslugaComplex uc on uc.UslugaComplex_id = ppu.UslugaComplex_id
							left join v_MedService ms on ms.MedService_id = ppu.MedService_id
						where
							uc.UslugaComplex_id in ($uclist)
							and pp.PacketPrescr_id = $id
							and ppl.PrescriptionType_id = '12' -- функ.диагностика");
		} else {
			return array();
		}
	}
	function getDrugData($data) {
		if (!empty($data['save_data']['drug'])) {
			$id = $data['PacketPrescr_id'];
			$result = $this->queryResult("
						select
							 pptd.PacketPrescrTreatDrug_id as \"PacketPrescrTreatDrug_id\",
							 pptd.DrugComplexMnn_id as \"DrugComplexMnn_id\",
							 pptd.Drug_id as \"Drug_id\"
						from
							dbo.v_PacketPrescr pp
							inner join dbo.v_PacketPrescrList ppl on ppl.PacketPrescr_id = pp.PacketPrescr_id
							inner join dbo.v_PacketPrescrTreat ppt on ppt.PacketPrescrList_id = ppl.PacketPrescrList_id
							left join dbo.v_PacketPrescrTreatDrug pptd on pptd.PacketPrescrTreat_id = ppt.PacketPrescrTreat_id						
						where
							pp.PacketPrescr_id = $id
							and ppl.PrescriptionType_id = '5'");
			$response = array();
			foreach ($result as $item) {
				array_push($response, [$item]);
			}
			return $response;
		} else {
			return array();
		}
	}

	function getLabDiagData($data) {
		if (!empty($data['save_data']['labdiag'])) {
			$id = $data['PacketPrescr_id'];
			$labDiagList = implode(",", $data['save_data']['labdiag']);

			$result = $this->queryResult("
						select
							coalesce(ms.MedService_id,0) as \"MedService_id\",
							uc.UslugaComplex_id as \"UslugaComplex_id\"
						from
                        				v_PacketPrescr pp
                        				inner join v_PacketPrescrList ppl on ppl.PacketPrescr_id = pp.PacketPrescr_id
                        				inner join v_PacketPrescrUsluga ppu on ppu.PacketPrescrList_id = ppl.PacketPrescrList_id
							inner join v_UslugaComplex uc on uc.UslugaComplex_id = ppu.UslugaComplex_id
							left join v_MedService ms on ms.MedService_id = ppu.MedService_id
						where
							pp.PacketPrescr_id = $id
							and uc.UslugaComplex_id in ($labDiagList)
							and ppl.PrescriptionType_id = '11' -- лаб.диагностика");
			foreach ($result as $key => $item) {
				$result[$item['UslugaComplex_id']] = $item;
				$result[$item['UslugaComplex_id']]['UslugaComplex_id'] = [$item['UslugaComplex_id']];
				unset($result[$key]);
			}
			return $result;
		} else {
			return array();
		}
	}
	function getProcData($data) {
		if (!empty($data['save_data']['proc'])) {
			$id = $data['PacketPrescr_id'];
			$procList = implode(",", $data['save_data']['proc']);
			
			$result = $this->queryResult("
						select
							uc.UslugaComplex_id as \"UslugaComplex_id\"		
						from
                        				v_PacketPrescr pp
                        				inner join v_PacketPrescrList ppl on ppl.PacketPrescr_id = pp.PacketPrescr_id
                        				inner join v_PacketPrescrUsluga ppu on ppu.PacketPrescrList_id = ppl.PacketPrescrList_id
							inner join v_UslugaComplex uc on uc.UslugaComplex_id = ppu.UslugaComplex_id
							left join v_MedService ms on ms.MedService_id = ppu.MedService_id
						where
							pp.PacketPrescr_id = $id
							and uc.UslugaComplex_id in ($procList)
							and ppl.PrescriptionType_id = '6' --процедуры");

			$response = array();
			foreach ($result as $item) {
				array_push($response, $item['UslugaComplex_id']);
			}
			return $response;
		} else {
			return array();
		}
	}

	function getConsuslData($data) {
		if (!empty($data['save_data']['consusl'])) {
			$id = $data['PacketPrescr_id'];
			$consulList = implode(",", $data['save_data']['consusl']);

			return $this->queryResult("
						select
							coalesce(ms.MedService_id,0) as \"MedService_id\",
							uc.UslugaComplex_id as \"UslugaComplex_id\"
						from
                        				v_PacketPrescr pp
                        				inner join v_PacketPrescrList ppl on ppl.PacketPrescr_id = pp.PacketPrescr_id
                        				inner join v_PacketPrescrUsluga ppu on ppu.PacketPrescrList_id = ppl.PacketPrescrList_id
							inner join v_UslugaComplex uc on uc.UslugaComplex_id = ppu.UslugaComplex_id
							left join v_MedService ms on ms.MedService_id = ppu.MedService_id
							left join v_Lpu l on l.Lpu_id = ms.Lpu_id
						where
							pp.PacketPrescr_id = $id
							and uc.UslugaComplex_id in ($consulList)
							and ppl.PrescriptionType_id = '13' -- консультационная услуга");
		} else {
			return array();
		}
	}
	function getRegimeData($data) {
		if (!empty($data['save_data']['regime'])) {
			$id = $data['PacketPrescr_id'];
			$regimeList = implode(",", $data['save_data']['regime']);
			
			return $this->queryResult("
						select
							 ppr.PrescriptionRegimeType_id as \"PrescriptionRegimeType_id\"
							,coalesce(ppr.PacketPrescrRegime_Duration,0) as \"PacketPrescrRegime_Duration\"
						from
							dbo.v_PacketPrescr pp
							inner join dbo.v_PacketPrescrList ppl on ppl.PacketPrescr_id = pp.PacketPrescr_id
							inner join dbo.v_PacketPrescrRegime ppr on ppr.PacketPrescrList_id = ppl.PacketPrescrList_id
							inner join PrescriptionRegimeType PRT on PRT.PrescriptionRegimeType_id = ppr.PrescriptionRegimeType_id
						where
							pp.PacketPrescr_id = $id
							and ppr.PrescriptionRegimeType_id in ($regimeList) -- режимы
							and ppl.PrescriptionType_id = '1' -- режимы");
		} else {
			return array();
		}
	}
	function getDietData($data) {
		if (!empty($data['save_data']['diet'])) {
			$id = $data['PacketPrescr_id'];
			$dietList = implode(",", $data['save_data']['diet']);

			return $this->queryResult("
						select
							 ppd.PrescriptionDietType_id as \"PrescriptionDietType_id\"
							,coalesce(ppd.PacketPrescrDiet_Duration,0) as \"PacketPrescrDiet_Duration\"
						from
							dbo.v_PacketPrescr pp
							inner join dbo.v_PacketPrescrList ppl on ppl.PacketPrescr_id = pp.PacketPrescr_id
							inner join dbo.v_PacketPrescrDiet ppd on ppd.PacketPrescrList_id = ppl.PacketPrescrList_id
							inner join v_PrescriptionDietType pdt on pdt.PrescriptionDietType_id = ppd.PrescriptionDietType_id
						where
							pp.PacketPrescr_id = $id
							and ppd.PrescriptionDietType_id in ($dietList)
							and ppl.PrescriptionType_id = '2' -- диеты");
		} else {
			return array();
		}
	}
	function getServerId($data) {

		if(!empty($data['PersonEvn_id'])) {
			$result =  $this->getFirstResultFromQuery("SELECT Server_id FROM v_Person_all WHERE PersonEvn_id = :PersonEvn_id",
				array('PersonEvn_id'=>$data['PersonEvn_id'])); // получаю тут, чтобы не писать данный параметр в метод
			return $result;
		}
		if(!empty($data['Person_id'])) {
			$result =  $this->getFirstResultFromQuery("SELECT Server_id FROM v_Person_all WHERE Person_id = :Person_id",
				array('Person_id'=>$data['Person_id'])); // получаю тут, чтобы не писать данный параметр в метод
			return $result;
		}

	}
}