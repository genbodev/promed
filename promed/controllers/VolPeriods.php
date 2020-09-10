<?php	defined('BASEPATH') or die ('No direct script access allowed');

class VolPeriods extends swController
{
    public $options = array();
    protected $inputRules = array(		
    'DeleteObject' => array(
        array(
            'field' => 'object',
            'label' => 'object',
            'rules' => '',
            'type'  => 'string'
        ),
        array(
            'field' => 'bl',
            'label' => 'Идентификатор',
            'rules' => 'required',
            'type'  => 'id'
        )
    ),
    'doControl' => array(
        array(
            'field' => 'RequestList_id',
            'label' => 'Идентификатор',
            'rules' => 'required',
            'type'  => 'int'
        )
    ),
    'checkSmpTeamExists' => array(
        array(
            'field' => 'RequestList_id',
            'label' => 'Идентификатор',
            'rules' => 'required',
            'type'  => 'int'
        ),
        array(
            'field' => 'SprPlanObj_Code',
            'label' => 'Код объекта планирования',
            'rules' => 'required',
            'type'  => 'float'
        )
    ),
    'saveVolPeriod' => array(
        array(
            'field' => 'VolPeriod_id',
            'label' => 'Идентификатор',
            'rules' => '',
            'type' => 'int'
        ),
        array(
            'field' => 'VolPeriod_begDate',
            'label' => 'Дата начала периода заявки',
            'rules' => 'required',
            'type' => 'date'
        ),
        array(
            'field' => 'VolPeriod_endDate',
            'label' => 'Дата завершения периода заявки',
            'rules' => 'required',
            'type' => 'date'
        ),
        array(
            'field' => 'VolPeriod_Name',
            'label' => 'Наименование',
            'rules' => 'required',
            'type' => 'string'
        ),
        array(
            'field' => 'Plan_year',
            'label' => 'Год планирования',
            'rules' => 'required',
            'type' => 'int'
        )
    ),
    'addLpu2Request' => array(
        array(
            'field' => 'Request_id',
            'label' => 'Идентификатор',
            'rules' => 'required',
            'type' => 'int'
        ),
        array(
            'field' => 'Lpu_id',
            'label' => 'Ид ЛПУ',
            'rules' => 'required',
            'type' => 'int'
        ),
        array(
            'field' => 'SprRequestStatus_id',
            'label' => 'Ид статуса заявки',
            'rules' => 'required',
            'type' => 'int'
        ),
        array(
            'field' => 'SprVidMp_id',
            'label' => 'Ид вида помощи',
            'rules' => 'required',
            'type' => 'int'
        )
    ),
    'saveRequestDataFactsStacKSG' => array(
        array(
            'field' => 'RequestData_id',
            'label' => 'Идентификатор',
            'rules' => 'required',
            'type' => 'int'
        ),
        array(
            'field' => 'RequestData_EnablePlan',
            'label' => 'Разрешить планирование',
            'rules' => '',
            'type' => 'string'
        ),
        array(
            'field' => 'RequestData_Plan',
            'label' => 'План',
            'rules' => '',
            'type' => 'int'
        ),
        array(
            'field' => 'RequestData_PlanKP',
            'label' => 'План КП',
            'rules' => '',
            'type'  => 'int'
        ),
        array(
            'field' => 'RequestData_Comment',
            'label' => 'Комментарий',
            'rules' => '',
            'type'  => 'string'
        )
    ),
    'setRequestStatus' => array(
        array(
            'field' => 'RequestList_id',
            'label' => 'ид записи',
            'rules' => 'required',
            'type' => 'int'
        ),
        array(
            'field' => 'SprRequestStatus_id',
            'label' => 'ид статуса',
            'rules' => 'required',
            'type' => 'int'
        ),
    ),
    'saveRequestList' => array(
        array(
            'field' => 'RequestList_id',
            'label' => 'ид записи',
            'rules' => 'required',
            'type' => 'int'
        ),
        array(
            'field' => 'KpAdults',
            'label' => 'контрольный показатель',
            'rules' => '',
            'type' => 'float'
        ),
        array(
            'field' => 'KpKids',
            'label' => 'контрольный показатель',
            'rules' => '',
            'type' => 'float'
        ),
        array(
            'field' => 'KfLimit',
            'label' => 'предельный коэффициент',
            'rules' => '',
            'type' => 'float'
        ),
        array(
            'field' => 'DevPrc',
            'label' => 'процент отклонения',
            'rules' => '',
            'type' => 'float'
        ),
        array(
            'field' => 'doControl',
            'label' => 'проводить контроль',
            'rules' => '',
            'type' => 'string'
        ),
        array(
            'field' => 'Comment',
            'label' => 'Комментарий',
            'rules' => '',
            'type' => 'string'
        ),
		array(
            'field' => 'DispNabKP',
            'label' => 'Для проведения дисп. наблюдения',
            'rules' => '',
            'type' => 'float'
        ),
		array(
            'field' => 'RazObrCountKP',
            'label' => 'Для разовых посещений в связи с заб.',
            'rules' => '',
            'type' => 'float'
        ),
		array(
            'field' => 'MidMedStaffKP',
            'label' => 'Для посещений среднего МП',
            'rules' => '',
            'type' => 'float'
        ),
		array(
            'field' => 'OtherPurpKP',
            'label' => 'Для посещений с другими целями',
            'rules' => '',
            'type' => 'float'
        ),
        array(
            'field' => 'KpAdults_o',
            'label' => 'контрольный показатель',
            'rules' => '',
            'type' => 'float'
        ),
        array(
            'field' => 'KpKids_o',
            'label' => 'контрольный показатель',
            'rules' => '',
            'type' => 'float'
        ),
        array(
            'field' => 'KfLimit_o',
            'label' => 'предельный коэффициент',
            'rules' => '',
            'type' => 'float'
        ),
        array(
            'field' => 'DevPrc_o',
            'label' => 'процент отклонения',
            'rules' => '',
            'type' => 'float'
        ),
        array(
            'field' => 'doControl_o',
            'label' => 'проводить контроль',
            'rules' => '',
            'type' => 'string'
        ),
        array(
            'field' => 'Comment_o',
            'label' => 'Комментарий',
            'rules' => '',
            'type' => 'string'
        ),
		array(
            'field' => 'DispNabKP_o',
            'label' => 'Для проведения дисп. наблюдения',
            'rules' => '',
            'type' => 'float'
        ),
		array(
            'field' => 'RazObrCountKP_o',
            'label' => 'Для разовых посещений в связи с заб.',
            'rules' => '',
            'type' => 'float'
        ),
		array(
            'field' => 'MidMedStaffKP_o',
            'label' => 'Для посещений среднего МП',
            'rules' => '',
            'type' => 'float'
        ),
		array(
            'field' => 'OtherPurpKP_o',
            'label' => 'Для посещений с другими целями',
            'rules' => '',
            'type' => 'float'
        )
    ),
    'setRequestStatusAll' => array(
        array(
            'field' => 'Request_id',
            'label' => 'ид записи',
            'rules' => 'required',
            'type' => 'int'
        ),
        array(
            'field' => 'Status_id',
            'label' => 'ид статуса',
            'rules' => 'required',
            'type' => 'int'
        ),
    ),
    'updateLic' => array(
        array(
            'field' => 'RequestList_id',
            'label' => 'ид заявки',
            'rules' => 'required',
            'type' => 'int'
        )
    ),
    'saveRequestData' => array(
        array(
            'field' => 'RequestData_id',
            'label' => 'Идентификатор',
            'rules' => 'required',
            'type' => 'int'
        ),
        array(
            'field' => 'RequestData_AllowPlan',
            'label' => 'Разрешить планирование',
            'rules' => '',
            'type' => 'string'
        ),
        array(
            'field' => 'VolCount1',
            'label' => 'Факт. объем',
            'rules' => '',
            'type' => 'int'
        ),
        array(
            'field' => 'VolCount2',
            'label' => 'Факт. объем',
            'rules' => '',
            'type' => 'int'
        ),
        array(
            'field' => 'VolCount3',
            'label' => 'Факт. объем',
            'rules' => '',
            'type' => 'int'
        ),
        array(
            'field' => 'VolCount4',
            'label' => 'Факт. объем',
            'rules' => '',
            'type' => 'int'
        ),
        array(
            'field' => 'RequestData_Plan',
            'label' => 'План',
            'rules' => '',
            'type' => 'int'
        ),
        array(
            'field' => 'RequestData_PlanKP',
            'label' => 'План КП',
            'rules' => '',
            'type'  => 'int'
        ),
        array(
            'field' => 'RequestData_KP',
            'label' => 'КП',
            'rules' => '',
            'type'  => 'float'
        ),
        array(
            'field' => 'RequestData_Comment',
            'label' => 'Комментарий',
            'rules' => '',
            'type'  => 'string'
        ),
        array(
            'field' => 'RequestData_AvgDur',
            'label' => '',
            'rules' => '',
            'type'  => 'int'
        ),
        array(
            'field' => 'RequestData_BedCount',
            'label' => '',
            'rules' => '',
            'type'  => 'int'
        ),
        array(
            'field' => 'RequestData_EstabPostCount',
            'label' => '',
            'rules' => '',
            'type'  => 'int'
        ),
        array(
            'field' => 'RequestData_ActivePostCount',
            'label' => '',
            'rules' => '',
            'type'  => 'int'
        ),
        array(
            'field' => 'RequestData_IndividCount',
            'label' => '',
            'rules' => '',
            'type'  => 'int'
        ),
        array(
            'field' => 'RequestData_TeamCount',
            'label' => '',
            'rules' => '',
            'type'  => 'int'
        ),
        array(
            'field' => 'LpuLicence_id',
            'label' => '',
            'rules' => '',
            'type'  => 'int'
        ),
        array(
            'field' => 'SpecCertif_Num',
            'label' => '',
            'rules' => '',
            'type'  => 'string'
        ),
        array(
            'field' => 'SpecCertif_endDate',
            'label' => '',
            'rules' => '',
            'type'  => 'date'
        ),
        array(
            'field' => 'AssignedPacCount',
            'label' => '',
            'rules' => '',
            'type'  => 'int'
        ),
        array(
            'field' => 'WomanCount',
            'label' => '',
            'rules' => '',
            'type'  => 'int'
        ),
        array(
            'field' => 'SluchCountOwn1',
            'label' => '',
            'rules' => '',
            'type'  => 'int'
        ),
        array(
            'field' => 'SluchCountZone1',
            'label' => '',
            'rules' => '',
            'type'  => 'int'
        ),
        array(
            'field' => 'SluchCountOwn2',
            'label' => '',
            'rules' => '',
            'type'  => 'int'
        ),
        array(
            'field' => 'SluchCountZone2',
            'label' => '',
            'rules' => '',
            'type'  => 'int'
        ),
        array(
            'field' => 'ShiftCount',
            'label' => '',
            'rules' => '',
            'type'  => 'int'
        ),
        array(
            'field' => 'PlaceCount',
            'label' => '',
            'rules' => '',
            'type'  => 'int'
        ),
        array(
            'field' => 'AvgYearBed',
            'label' => '',
            'rules' => '',
            'type'  => 'int'
        ),
        array(
            'field' => 'RazObrCount',
            'label' => '',
            'rules' => '',
            'type'  => 'int'
        ),
        array(
            'field' => 'DispNab',
            'label' => '',
            'rules' => '',
            'type'  => 'int'
        ),
        array(
            'field' => 'MedReab',
            'label' => '',
            'rules' => '',
            'type'  => 'int'
        ),
        array(
            'field' => 'OtherPurp',
            'label' => '',
            'rules' => '',
            'type'  => 'int'
        ),
		array(
            'field' => 'DispNabPlanKP',
            'label' => 'Для проведения дисп. наблюдения',
            'rules' => '',
            'type' => 'float'
        ),
		array(
            'field' => 'RazObrCountPlanKP',
            'label' => 'Для разовых посещений в связи с заб.',
            'rules' => '',
            'type' => 'float'
        ),
		array(
            'field' => 'MidMedStaffPlanKP',
            'label' => 'Для посещений среднего МП',
            'rules' => '',
            'type' => 'float'
        ),
		array(
            'field' => 'OtherPurpPlanKP',
            'label' => 'Для посещений с другими целями',
            'rules' => '',
            'type' => 'float'
        ),
        array(
            'field' => 'PacCount',
            'label' => '',
            'rules' => '',
            'type'  => 'int'
        ),
        array(
            'field' => 'TeamCount',
            'label' => '',
            'rules' => '',
            'type'  => 'int'
        ),
        array(
            'field' => 'EmerRoom',
            'label' => '',
            'rules' => '',
            'type'  => 'int'
        ),
        array(
            'field' => 'Post',
            'label' => '',
            'rules' => '',
            'type'  => 'string'
        ),
        array(
            'field' => 'FIO',
            'label' => '',
            'rules' => '',
            'type'  => 'string'
        ),
        array(
            'field' => 'Phone',
            'label' => '',
            'rules' => '',
            'type'  => 'string'
        ),
        array(
            'field' => 'Email',
            'label' => '',
            'rules' => '',
            'type'  => 'string'
        ),
        array(
            'field' => 'EmerRoom_o',
            'label' => '',
            'rules' => '',
            'type'  => 'int'
        ),
        array(
            'field' => 'VolCount1_o',
            'label' => 'Факт. объем',
            'rules' => '',
            'type' => 'int'
        ),
        array(
            'field' => 'VolCount2_o',
            'label' => 'Факт. объем',
            'rules' => '',
            'type' => 'int'
        ),
        array(
            'field' => 'VolCount3_o',
            'label' => 'Факт. объем',
            'rules' => '',
            'type' => 'int'
        ),
        array(
            'field' => 'VolCount4_o',
            'label' => 'Факт. объем',
            'rules' => '',
            'type' => 'int'
        ),
        array(
            'field' => 'RequestData_Plan_o',
            'label' => 'План',
            'rules' => '',
            'type' => 'int'
        ),
        array(
            'field' => 'RequestData_PlanKP_o',
            'label' => 'План КП',
            'rules' => '',
            'type'  => 'int'
        ),
        array(
            'field' => 'RequestData_KP_o',
            'label' => 'КП',
            'rules' => '',
            'type'  => 'float'
        ),
        array(
            'field' => 'RequestData_Comment_o',
            'label' => 'Комментарий',
            'rules' => '',
            'type'  => 'string'
        ),
        array(
            'field' => 'RequestData_AvgDur_o',
            'label' => '',
            'rules' => '',
            'type'  => 'int'
        ),
        array(
            'field' => 'RequestData_BedCount_o',
            'label' => '',
            'rules' => '',
            'type'  => 'int'
        ),
        array(
            'field' => 'RequestData_EstabPostCount_o',
            'label' => '',
            'rules' => '',
            'type'  => 'int'
        ),
        array(
            'field' => 'RequestData_ActivePostCount_o',
            'label' => '',
            'rules' => '',
            'type'  => 'int'
        ),
        array(
            'field' => 'RequestData_IndividCount_o',
            'label' => '',
            'rules' => '',
            'type'  => 'int'
        ),
        array(
            'field' => 'RequestData_TeamCount_o',
            'label' => '',
            'rules' => '',
            'type'  => 'int'
        ),
        array(
            'field' => 'LpuLicence_id_o',
            'label' => '',
            'rules' => '',
            'type'  => 'int'
        ),
        array(
            'field' => 'SpecCertif_Num_o',
            'label' => '',
            'rules' => '',
            'type'  => 'string'
        ),
        array(
            'field' => 'SpecCertif_endDate_o',
            'label' => '',
            'rules' => '',
            'type'  => 'date'
        ),
        array(
            'field' => 'AssignedPacCount_o',
            'label' => '',
            'rules' => '',
            'type'  => 'int'
        ),
        array(
            'field' => 'WomanCount_o',
            'label' => '',
            'rules' => '',
            'type'  => 'int'
        ),
        array(
            'field' => 'SluchCountOwn1_o',
            'label' => '',
            'rules' => '',
            'type'  => 'int'
        ),
        array(
            'field' => 'SluchCountZone1_o',
            'label' => '',
            'rules' => '',
            'type'  => 'int'
        ),
        array(
            'field' => 'SluchCountOwn2_o',
            'label' => '',
            'rules' => '',
            'type'  => 'int'
        ),
        array(
            'field' => 'SluchCountZone2_o',
            'label' => '',
            'rules' => '',
            'type'  => 'int'
        ),
        array(
            'field' => 'ShiftCount_o',
            'label' => '',
            'rules' => '',
            'type'  => 'int'
        ),
        array(
            'field' => 'PlaceCount_o',
            'label' => '',
            'rules' => '',
            'type'  => 'int'
        ),
        array(
            'field' => 'AvgYearBed_o',
            'label' => '',
            'rules' => '',
            'type'  => 'int'
        ),
        array(
            'field' => 'RazObrCount_o',
            'label' => '',
            'rules' => '',
            'type'  => 'int'
        ),
        array(
            'field' => 'PacCount_o',
            'label' => '',
            'rules' => '',
            'type'  => 'int'
        ),
        array(
            'field' => 'TeamCount_o',
            'label' => '',
            'rules' => '',
            'type'  => 'int'
        ),
        array(
            'field' => 'EmerRoom_o',
            'label' => '',
            'rules' => '',
            'type'  => 'int'
        ),
        array(
            'field' => 'Post_o',
            'label' => '',
            'rules' => '',
            'type'  => 'string'
        ),
        array(
            'field' => 'FIO_o',
            'label' => '',
            'rules' => '',
            'type'  => 'string'
        ),
        array(
            'field' => 'Phone_o',
            'label' => '',
            'rules' => '',
            'type'  => 'string'
        ),
        array(
            'field' => 'Email_o',
            'label' => '',
            'rules' => '',
            'type'  => 'string'
        ),
		array(
            'field' => 'DispNabPlanKP_o',
            'label' => 'Для проведения дисп. наблюдения',
            'rules' => '',
            'type' => 'float'
        ),
		array(
            'field' => 'RazObrCountPlanKP_o',
            'label' => 'Для разовых посещений в связи с заб.',
            'rules' => '',
            'type' => 'float'
        ),
		array(
            'field' => 'MidMedStaffPlanKP_o',
            'label' => 'Для посещений среднего МП',
            'rules' => '',
            'type' => 'float'
        ),
		array(
            'field' => 'OtherPurpPlanKP_o',
            'label' => 'Для посещений с другими целями',
            'rules' => '',
            'type' => 'float'
        )
    ),
    'savePlanCatData' => array(
        array(
            'field' => 'SprPlanCat_id',
            'label' => 'ид',
            'rules' => 'required',
            'type' => 'int'
        ),
        array(
            'field' => 'RequestList_id',
            'label' => 'ид',
            'rules' => 'required',
            'type' => 'int'
        ),
        array(
            'field' => 'PlanCatData_KP',
            'label' => 'КП',
            'rules' => '',
            'type' => 'float'
        ),
        array(
            'field' => 'PlanCatData_KP_o',
            'label' => 'ид',
            'rules' => '',
            'type' => 'float'
        ),
        array(
            'field' => 'PlanCatData_KpAdults',
            'label' => 'КП',
            'rules' => '',
            'type' => 'float'
        ),
        array(
            'field' => 'PlanCatData_KpAdults_o',
            'label' => 'ид',
            'rules' => '',
            'type' => 'float'
        ),
        array(
            'field' => 'PlanCatData_KpKids',
            'label' => 'КП',
            'rules' => '',
            'type' => 'float'
        ),
        array(
            'field' => 'PlanCatData_KpKids_o',
            'label' => 'ид',
            'rules' => '',
            'type' => 'float'
        ),
        array(
            'field' => 'PlanCatData_PlanKpAdults',
            'label' => 'КП',
            'rules' => '',
            'type' => 'float'
        ),
        array(
            'field' => 'PlanCatData_PlanKpAdults_o',
            'label' => 'ид',
            'rules' => '',
            'type' => 'float'
        ),
        array(
            'field' => 'PlanCatData_PlanKpKids',
            'label' => 'КП',
            'rules' => '',
            'type' => 'float'
        ),
        array(
            'field' => 'PlanCatData_PlanKpKids_o',
            'label' => 'ид',
            'rules' => '',
            'type' => 'float'
        ),
        array(
            'field' => 'PlanCatData_KpEmer',
            'label' => 'КП',
            'rules' => '',
            'type' => 'float'
        ),
        array(
            'field' => 'PlanCatData_KpEmer_o',
            'label' => 'ид',
            'rules' => '',
            'type' => 'float'
        )
    ),
    'saveSprInfo' => array(
        array(
            'field' => 'SprInfo_id',
            'label' => 'ид',
            'rules' => 'required',
            'type' => 'int'
        ),
        array(
            'field' => 'Duration',
            'label' => 'продолжительность',
            'rules' => '',
            'type'  => 'float'
        ),
        array(
            'field' => 'Comment',
            'label' => 'Комментарий',
            'rules' => '',
            'type'  => 'string'
        ),
        array(
            'field' => 'EstabPostCount',
            'label' => '',
            'rules' => '',
            'type'  => 'float'
        ),
        array(
            'field' => 'ActivePostCount',
            'label' => '',
            'rules' => '',
            'type'  => 'float'
        ),
        array(
            'field' => 'IndividCount',
            'label' => '',
            'rules' => '',
            'type'  => 'int'
        ),
        array(
            'field' => 'LpuLicence_id',
            'label' => '',
            'rules' => '',
            'type'  => 'int'
        ),
        array(
            'field' => 'SpecCertif_Num',
            'label' => '',
            'rules' => '',
            'type'  => 'string'
        ),
        array(
            'field' => 'SpecCertif_endDate',
            'label' => '',
            'rules' => '',
            'type'  => 'string'
        ),
        array(
            'field' => 'AssignedPacCount',
            'label' => '',
            'rules' => '',
            'type'  => 'int'
        ),
        array(
            'field' => 'WomanCount',
            'label' => '',
            'rules' => '',
            'type'  => 'int'
        ),
        array(
            'field' => 'SluchCountOwn1',
            'label' => '',
            'rules' => '',
            'type'  => 'int'
        ),
        array(
            'field' => 'SluchCountZone1',
            'label' => '',
            'rules' => '',
            'type'  => 'int'
        ),
        array(
            'field' => 'SluchCountOwn2',
            'label' => '',
            'rules' => '',
            'type'  => 'int'
        ),
        array(
            'field' => 'SluchCountZone2',
            'label' => '',
            'rules' => '',
            'type'  => 'int'
        ),
        array(
            'field' => 'ShiftCount',
            'label' => '',
            'rules' => '',
            'type'  => 'int'
        ),
        array(
            'field' => 'PlaceCount',
            'label' => '',
            'rules' => '',
            'type'  => 'int'
        ),
        array(
            'field' => 'AvgYearBed',
            'label' => '',
            'rules' => '',
            'type'  => 'int'
        ),
        array(
            'field' => 'RazObrCount',
            'label' => '',
            'rules' => '',
            'type'  => 'int'
        ),
        array(
            'field' => 'PacCount',
            'label' => '',
            'rules' => '',
            'type'  => 'int'
        ),
        array(
            'field' => 'TeamCount',
            'label' => '',
            'rules' => '',
            'type'  => 'int'
        ),
        array(
            'field' => 'BedCount',
            'label' => '',
            'rules' => '',
            'type'  => 'int'
        ),
        array(
            'field' => 'Comment_o',
            'label' => 'Комментарий',
            'rules' => '',
            'type'  => 'string'
        ),
        array(
            'field' => 'EstabPostCount_o',
            'label' => '',
            'rules' => '',
            'type'  => 'float'
        ),
        array(
            'field' => 'ActivePostCount_o',
            'label' => '',
            'rules' => '',
            'type'  => 'float'
        ),
        array(
            'field' => 'IndividCount_o',
            'label' => '',
            'rules' => '',
            'type'  => 'int'
        ),
        array(
            'field' => 'LpuLicence_id_o',
            'label' => '',
            'rules' => '',
            'type'  => 'int'
        ),
        array(
            'field' => 'SpecCertif_Num_o',
            'label' => '',
            'rules' => '',
            'type'  => 'string'
        ),
        array(
            'field' => 'SpecCertif_endDate_o',
            'label' => '',
            'rules' => '',
            'type'  => 'string'
        ),
        array(
            'field' => 'AssignedPacCount_o',
            'label' => '',
            'rules' => '',
            'type'  => 'int'
        ),
        array(
            'field' => 'WomanCount_o',
            'label' => '',
            'rules' => '',
            'type'  => 'int'
        ),
        array(
            'field' => 'SluchCountOwn1_o',
            'label' => '',
            'rules' => '',
            'type'  => 'int'
        ),
        array(
            'field' => 'SluchCountZone1_o',
            'label' => '',
            'rules' => '',
            'type'  => 'int'
        ),
        array(
            'field' => 'SluchCountOwn2_o',
            'label' => '',
            'rules' => '',
            'type'  => 'int'
        ),
        array(
            'field' => 'SluchCountZone2_o',
            'label' => '',
            'rules' => '',
            'type'  => 'int'
        ),
        array(
            'field' => 'ShiftCount_o',
            'label' => '',
            'rules' => '',
            'type'  => 'int'
        ),
        array(
            'field' => 'PlaceCount_o',
            'label' => '',
            'rules' => '',
            'type'  => 'int'
        ),
        array(
            'field' => 'AvgYearBed_o',
            'label' => '',
            'rules' => '',
            'type'  => 'int'
        ),
        array(
            'field' => 'RazObrCount_o',
            'label' => '',
            'rules' => '',
            'type'  => 'int'
        ),
        array(
            'field' => 'PacCount_o',
            'label' => '',
            'rules' => '',
            'type'  => 'int'
        ),
        array(
            'field' => 'TeamCount_o',
            'label' => '',
            'rules' => '',
            'type'  => 'int'
        ),
        array(
            'field' => 'BedCount_o',
            'label' => '',
            'rules' => '',
            'type'  => 'int'
        )
    ),
    'loadRequestData' => array(
        array(
            'field' => 'RequestList_id',
            'label' => 'Идентификатор заявки',
            'rules' => 'required',
            'type' => 'int'
        ),
        array(
            'field' => 'MesAgeGroup_id',
            'label' => 'Ид возрастной группы',
            'rules' => 'required',
            'type' => 'int'
        ),
        
        array(
            'field' => 'SprPlanCat_id',
            'label' => 'Ид категории',
            'rules' => 'required',
            'type' => 'int'
        )
    ),
    'checkFact' => array(
        array(
            'field' => 'VolPeriod_id',
            'label' => 'Идентификатор периода',
            'rules' => 'required',
            'type' => 'int'
        ),
        array(
            'field' => 'VidMP_id',
            'label' => 'Идентификатор вида помощи',
            'rules' => 'required',
            'type' => 'int'
        )
    ),
    'loadSprAllowPlan' => array(
        array(
            'field' => 'SprAllowPlan_id',
            'label' => 'Ид записи',
            'rules' => '',
            'type' => 'int'
        ),
        array(
            'field' => 'SprAllowPlan_Value',
            'label' => 'Значение',
            'rules' => '',
            'type' => 'string'
        )
    ),
    'getLpuList' => array(
        array(
            'field' => 'Request_id',
            'label' => 'Идентификатор',
            'rules' => 'required',
            'type'  => 'int'
        )
    ),
    'getPlanByMo' => array(
        array(
            'field' => 'RequestList_id',
            'label' => 'Идентификатор заявки МО',
            'rules' => 'required',
            'type' => 'int'
        )
    ),
    'loadRequestDataStac' => array(
        array(
            'field' => 'RequestList_id',
            'label' => 'Идентификатор заявки',
            'rules' => 'required',
            'type' => 'int'
        ),
        array(
            'field' => 'MesAgeGroup_id',
            'label' => 'Ид возрастной группы',
            'rules' => '',
            'type' => 'int'
        ),
        array(
            'field' => 'SprPlanCat_id',
            'label' => 'Ид категории',
            'rules' => '',
            'type' => 'int'
        ),
        array(
            'field' => 'objName',
            'label' => 'Наименование объекта',
            'rules' => '',
            'type' => 'string'
        ),
        array(
            'field' => 'allowPlan',
            'label' => 'разрешенность планирования',
            'rules' => '',
            'type' => 'string'
        )
    ),
    'loadRequestDataDS' => array(
        array(
            'field' => 'RequestList_id',
            'label' => 'Идентификатор заявки',
            'rules' => 'required',
            'type' => 'int'
        ),
        array(
            'field' => 'MesAgeGroup_id',
            'label' => 'Ид возрастной группы',
            'rules' => '',
            'type' => 'int'
        ),
        array(
            'field' => 'SprPlanCat_id',
            'label' => 'Ид категории',
            'rules' => '',
            'type' => 'int'
        ),
        array(
            'field' => 'objName',
            'label' => 'Наименование объекта',
            'rules' => '',
            'type' => 'string'
        ),
        array(
            'field' => 'allowPlan',
            'label' => 'разрешенность планирования',
            'rules' => '',
            'type' => 'string'
        )
    ),
    'loadSprInfo' => array(
        array(
            'field' => 'RequestList_id',
            'label' => 'Идентификатор заявки',
            'rules' => 'required',
            'type' => 'int'
        ),
        array(
            'field' => 'MesAgeGroup_id',
            'label' => 'Ид возрастной группы',
            'rules' => 'required',
            'type' => 'int'
        ),
        array(
            'field' => 'objName',
            'label' => 'Наименование объекта',
            'rules' => '',
            'type' => 'string'
        )
    ),
    'loadRequestDataApp' => array(
        array(
            'field' => 'RequestList_id',
            'label' => 'Идентификатор заявки',
            'rules' => 'required',
            'type' => 'int'
        ),
        array(
            'field' => 'MesAgeGroup_id',
            'label' => 'Ид возрастной группы',
            'rules' => 'required',
            'type' => 'int'
        ),
        array(
            'field' => 'objName',
            'label' => 'Наименование объекта планирования',
            'rules' => '',
            'type' => 'string'
        ),
        array(
            'field' => 'SprPlanCat_id',
            'label' => 'Ид категории',
            'rules' => '',
            'type' => 'int'
        ),
        array(
            'field' => 'allowPlan',
            'label' => 'Разрешено планирование',
            'rules' => '',
            'type' => 'string'
        )
    ),
    'loadRequestDataAppNmp' => array(
        array(
            'field' => 'RequestList_id',
            'label' => 'Идентификатор заявки',
            'rules' => 'required',
            'type' => 'int'
        ),
        array(
            'field' => 'MesAgeGroup_id',
            'label' => 'Ид возрастной группы',
            'rules' => 'required',
            'type' => 'int'
        ),
        array(
            'field' => 'objName',
            'label' => 'Наименование объекта планирования',
            'rules' => '',
            'type' => 'string'
        ),
        array(
            'field' => 'allowPlan',
            'label' => 'Разрешено планирование',
            'rules' => '',
            'type' => 'string'
        ),
        array(
            'field' => 'SprPlanCat_id',
            'label' => 'Ид категории',
            'rules' => '',
            'type' => 'int'
        )
    ),
    'loadRequestDataAppCons' => array(
        array(
            'field' => 'RequestList_id',
            'label' => 'Идентификатор заявки',
            'rules' => 'required',
            'type' => 'int'
        ),
        array(
            'field' => 'MesAgeGroup_id',
            'label' => 'Ид возрастной группы',
            'rules' => 'required',
            'type' => 'int'
        ),
        array(
            'field' => 'objName',
            'label' => 'Наименование объекта планирования',
            'rules' => '',
            'type' => 'string'
        ),
        array(
            'field' => 'allowPlan',
            'label' => 'Разрешено планирование',
            'rules' => '',
            'type' => 'string'
        )
    ),
    'loadRequestDataAppProf' => array(
        array(
            'field' => 'RequestList_id',
            'label' => 'Идентификатор заявки',
            'rules' => 'required',
            'type' => 'int'
        ),
        array(
            'field' => 'MesAgeGroup_id',
            'label' => 'Ид возрастной группы',
            'rules' => 'required',
            'type' => 'int'
        ),
        array(
            'field' => 'objName',
            'label' => 'Наименование объекта планирования',
            'rules' => '',
            'type' => 'string'
        ),
        array(
            'field' => 'SprPlanCat_id',
            'label' => 'Ид категории',
            'rules' => '',
            'type' => 'int'
        ),
        array(
            'field' => 'allowPlan',
            'label' => 'Разрешено планирование',
            'rules' => '',
            'type' => 'string'
        )
    ),
    'loadRequestDataAppDisp' => array(
        array(
            'field' => 'RequestList_id',
            'label' => 'Идентификатор заявки',
            'rules' => 'required',
            'type' => 'int'
        ),
        array(
            'field' => 'MesAgeGroup_id',
            'label' => 'Ид возрастной группы',
            'rules' => 'required',
            'type' => 'int'
        ),
        array(
            'field' => 'objName',
            'label' => 'Наименование объекта планирования',
            'rules' => '',
            'type' => 'string'
        ),
        array(
            'field' => 'SprPlanCat_id',
            'label' => 'Ид категории',
            'rules' => '',
            'type' => 'int'
        ),
        array(
            'field' => 'allowPlan',
            'label' => 'Разрешено планирование',
            'rules' => '',
            'type' => 'string'
        )
    ),
    'loadRequestDataAppProfAttach' => array(
        array(
            'field' => 'RequestList_id',
            'label' => 'Идентификатор заявки',
            'rules' => 'required',
            'type' => 'int'
        ),
        array(
            'field' => 'MesAgeGroup_id',
            'label' => 'Ид возрастной группы',
            'rules' => 'required',
            'type' => 'int'
        ),
        array(
            'field' => 'objName',
            'label' => 'Наименование объекта планирования',
            'rules' => '',
            'type' => 'string'
        ),
        array(
            'field' => 'SprPlanCat_id',
            'label' => 'Ид категории',
            'rules' => '',
            'type' => 'int'
        ),
        array(
            'field' => 'allowPlan',
            'label' => 'Разрешено планирование',
            'rules' => '',
            'type' => 'string'
        )
    ),
    'loadRequestDataZpt' => array(
        array(
            'field' => 'RequestList_id',
            'label' => 'Идентификатор заявки',
            'rules' => 'required',
            'type' => 'int'
        ),
        array(
            'field' => 'MesAgeGroup_id',
            'label' => 'Ид возрастной группы',
            'rules' => 'required',
            'type' => 'int'
        ),
        array(
            'field' => 'objName',
            'label' => 'Наименование объекта планирования',
            'rules' => '',
            'type' => 'string'
        ),
        array(
            'field' => 'SprPlanCat_id',
            'label' => 'Ид категории',
            'rules' => '',
            'type' => 'int'
        ),
        array(
            'field' => 'allowPlan',
            'label' => 'Разрешено планирование',
            'rules' => '',
            'type' => 'string'
        )
    ),
    'loadRequestDataEco' => array(
        array(
            'field' => 'RequestList_id',
            'label' => 'Идентификатор заявки',
            'rules' => 'required',
            'type' => 'int'
        ),
        array(
            'field' => 'MesAgeGroup_id',
            'label' => 'Ид возрастной группы',
            'rules' => 'required',
            'type' => 'int'
        ),
        array(
            'field' => 'objName',
            'label' => 'Наименование объекта планирования',
            'rules' => '',
            'type' => 'string'
        ),
        array(
            'field' => 'SprPlanCat_id',
            'label' => 'Ид категории',
            'rules' => '',
            'type' => 'int'
        ),
        array(
            'field' => 'allowPlan',
            'label' => 'Разрешено планирование',
            'rules' => '',
            'type' => 'string'
        )
    ),
    'loadRequestDataLdi' => array(
         array(
            'field' => 'RequestList_id',
            'label' => 'Идентификатор заявки',
            'rules' => 'required',
            'type' => 'int'
        ),
        array(
            'field' => 'MesAgeGroup_id',
            'label' => 'Ид возрастной группы',
            'rules' => 'required',
            'type' => 'int'
        ),
        array(
            'field' => 'SprPlanCat_id',
            'label' => 'Ид категории',
            'rules' => '',
            'type' => 'int'
        ),
        array(
            'field' => 'objName',
            'label' => 'Наименование объекта',
            'rules' => '',
            'type' => 'string'
        ),
        array(
            'field' => 'allowPlan',
            'label' => 'разрешенность планирования',
            'rules' => '',
            'type' => 'string'
        )
    ),
    'loadRequestDataSmp' => array(
        array(
            'field' => 'RequestList_id',
            'label' => 'Идентификатор заявки',
            'rules' => 'required',
            'type' => 'int'
        ),
        array(
            'field' => 'MesAgeGroup_id',
            'label' => 'Ид возрастной группы',
            'rules' => 'required',
            'type' => 'int'
        ),
        array(
            'field' => 'SprPlanCat_id',
            'label' => 'Ид категории',
            'rules' => '',
            'type' => 'int'
        ),
        array(
            'field' => 'objName',
            'label' => 'Наименование объекта',
            'rules' => '',
            'type' => 'string'
        ),
        array(
            'field' => 'allowPlan',
            'label' => 'разрешенность планирования',
            'rules' => '',
            'type' => 'string'
        )
    ),
    'loadRequestDataVmp' => array(
        array(
            'field' => 'RequestList_id',
            'label' => 'Идентификатор заявки',
            'rules' => 'required',
            'type' => 'int'
        ),
        array(
            'field' => 'MesAgeGroup_id',
            'label' => 'Ид возрастной группы',
            'rules' => 'required',
            'type' => 'int'
        ),
        array(
            'field' => 'SprPlanCat_id',
            'label' => 'Ид категории',
            'rules' => '',
            'type' => 'int'
        ),
        array(
            'field' => 'objName',
            'label' => 'Наименование объекта',
            'rules' => '',
            'type' => 'string'
        ),
        array(
            'field' => 'allowPlan',
            'label' => 'разрешенность планирования',
            'rules' => '',
            'type' => 'string'
        )
    ),
    'collectFactsStac' => array(
        array(
            'field' => 'VolPeriod_id',
            'label' => 'Идентификатор',
            'rules' => 'required',
            'type' => 'int'
        )
    ),
    'collectFactsStacKSG' => array(
        array(
            'field' => 'VolPeriod_id',
            'label' => 'Идентификатор',
            'rules' => 'required',
            'type' => 'int'
        )
    ),
    'collectFactsDS' => array(
        array(
            'field' => 'VolPeriod_id',
            'label' => 'Идентификатор',
            'rules' => 'required',
            'type' => 'int'
        )
    ),
    'collectFactsDSKSG' => array(
        array(
            'field' => 'VolPeriod_id',
            'label' => 'Идентификатор',
            'rules' => 'required',
            'type' => 'int'
        )
    ),
    'collectFactsVMP' => array(
        array(
            'field' => 'VolPeriod_id',
            'label' => 'Идентификатор',
            'rules' => 'required',
            'type' => 'int'
        )
    ),
    'collectFactsApp' => array(
        array(
            'field' => 'VolPeriod_id',
            'label' => 'Идентификатор',
            'rules' => 'required',
            'type' => 'int'
        )
    ),
    'collectFactsAppCons' => array(
        array(
            'field' => 'VolPeriod_id',
            'label' => 'Идентификатор',
            'rules' => 'required',
            'type' => 'int'
        )
    ),
    'collectFactsAppNmp' => array(
        array(
            'field' => 'VolPeriod_id',
            'label' => 'Идентификатор',
            'rules' => 'required',
            'type' => 'int'
        )
    ),
    'collectFactsAppTreatment' => array(
        array(
            'field' => 'VolPeriod_id',
            'label' => 'Идентификатор',
            'rules' => 'required',
            'type' => 'int'
        )
    ),
    'collectFactsAppProfNotAttach' => array(
        array(
            'field' => 'VolPeriod_id',
            'label' => 'Идентификатор',
            'rules' => 'required',
            'type' => 'int'
        )
    ),
    'collectFactsAppProfAttach' => array(
        array(
            'field' => 'VolPeriod_id',
            'label' => 'Идентификатор',
            'rules' => 'required',
            'type' => 'int'
        )
    ),
    'collectFactsAppProfCZ' => array(
        array(
            'field' => 'VolPeriod_id',
            'label' => 'Идентификатор',
            'rules' => 'required',
            'type' => 'int'
        )
    ),
    'collectFactsAppProfDisp' => array(
        array(
            'field' => 'VolPeriod_id',
            'label' => 'Идентификатор',
            'rules' => 'required',
            'type' => 'int'
        )
    ),
    'collectFactsAppProf' => array(
        array(
            'field' => 'VolPeriod_id',
            'label' => 'Идентификатор',
            'rules' => 'required',
            'type' => 'int'
        )
    ),
    'collectFactsAppProfAll' => array(
        array(
            'field' => 'VolPeriod_id',
            'label' => 'Идентификатор',
            'rules' => 'required',
            'type' => 'int'
        )
    ),
    'collectFactsSMP' => array(
        array(
            'field' => 'VolPeriod_id',
            'label' => 'Идентификатор',
            'rules' => 'required',
            'type' => 'int'
        )
    ),
    'collectFactsLdi' => array(
        array(
            'field' => 'VolPeriod_id',
            'label' => 'Идентификатор',
            'rules' => 'required',
            'type' => 'int'
        )
    ),
    'collectFactsZpt' => array(
        array(
            'field' => 'VolPeriod_id',
            'label' => 'Идентификатор',
            'rules' => 'required',
            'type' => 'int'
        )
    ),
    'collectFactsEco' => array(
        array(
            'field' => 'VolPeriod_id',
            'label' => 'Идентификатор',
            'rules' => 'required',
            'type' => 'int'
        )
    ),
    'buildRequest' => array(
        array(
            'field' => 'VolPeriod_id',
            'label' => 'Идентификатор',
            'rules' => '',
            'type' => 'int'
        ),
        array(
            'field' => 'Year',
            'label' => 'Год планирования',
            'rules' => 'required',
            'type' => 'int'
        ),
        array(
            'field' => 'VidMp',
            'label' => 'Вид мед. помощи',
            'rules' => 'required',
            'type' => 'int'
        ),
        array(
            'field' => 'Prc',
            'label' => 'Процент отклонения',
            'rules' => 'required',
            'type' => 'int'
        )
    ),
    'loadRequestDataStacKSG' => array(
        array(
            'field' => 'RequestList_id',
            'label' => 'Идентификатор заявки',
            'rules' => '',
            'type' => 'id'
        ),
        array(
            'field' => 'MesAgeGroup_id',
            'label' => 'Ид возрастной группы',
            'rules' => '',
            'type' => 'string'
        ),
        array(
            'field' => 'objName',
            'label' => 'наименование объекта',
            'rules' => '',
            'type' => 'string'
        ),
        array(
            'field' => 'allowPlan',
            'label' => 'признак разрешенности планирования',
            'rules' => '',
            'type' => 'string'
        ),
        array(
            'field' => 'SprPlanCat_id',
            'label' => 'Ид категории',
            'rules' => '',
            'type' => 'int'
        )
    ),
    'loadRequestDataDSKSG' => array(
        array(
            'field' => 'RequestList_id',
            'label' => 'Идентификатор заявки',
            'rules' => '',
            'type' => 'id'
        ),
        array(
            'field' => 'MesAgeGroup_id',
            'label' => 'Ид возрастной группы',
            'rules' => '',
            'type' => 'id'
        ),
        array(
            'field' => 'objName',
            'label' => 'наименование объекта',
            'rules' => '',
            'type' => 'string'
        ),
        array(
            'field' => 'allowPlan',
            'label' => 'признак разрешенности планирования',
            'rules' => '',
            'type' => 'int'
        ),
        array(
            'field' => 'SprPlanCat_id',
            'label' => 'ид категории',
            'rules' => '',
            'type' => 'int'
        )
    ),
    'loadSprInfoData' => array(
        array(
            'field' => 'RequestList_id',
            'label' => 'Идентификатор заявки',
            'rules' => 'required',
            'type' => 'id'
        ),
        array(
            'field' => 'MesAgeGroup_id',
            'label' => 'Ид возрастной группы',
            'rules' => 'required',
            'type' => 'int'
        )
    ),
    'loadVolRequest' => array(
        array(
            'field' => 'Request_id',
            'label' => 'Идентификатор заявки',
            'rules' => '',
            'type' => 'id'
            ),
        array(
            'field' => 'VolumeType_Name',
            'label' => 'Наименование',
            'rules' => '',
            'type' => 'string'
        ),
        array(
            'field' => 'Request_LpuCount',
            'label' => 'Кол-во МО',
            'rules' => '',
            'type' => 'string'
        ),
        array(
            'field' => 'SprRequestStatus_Name',
            'label' => 'Статус',
            'rules' => '',
            'type' => 'string'
        ),
        array(
            'field' => 'Request_VolCount',
            'label' => 'Объем',
            'rules' => '',
            'type' => 'string'
        ),
        array(
            'field' => 'Request_DeviationPrct',
            'label' => 'Процент отклонения',
            'rules' => '',
            'type' => 'string'
        ),
        array(
            'field' => 'Request_Year',
            'label' => 'Год планирования',
            'rules' => '',
            'type' => 'int'
        ),
        array(
            'field' => 'year',
            'label' => 'Год планирования',
            'rules' => 'required',
            'type' => 'int'
        )
    ),
    'loadVolRequestList' => array(
            array(
                    'field' => 'VolRequest_id',
                    'label' => 'Идентификатор заявки',
                    'rules' => '',
                    'type' => 'id'
                ),
            array(
                    'field' => 'mo_name',
                    'label' => 'Название МО',
                    'rules' => '',
                    'type' => 'string'
                ),
            array(
                    'field' => 'mo_lvl',
                    'label' => 'Уровень МО',
                    'rules' => '',
                    'type' => 'string'
                ),
            array(
                    'field' => 'request_status',
                    'label' => 'Статус заявки',
                    'rules' => '',
                    'type' => 'string'
                ),
            array(
                    'default' => 0,
                    'field' => 'start',
                    'label' => 'Начальный номер записи',
                    'rules' => '',
                    'type' => 'int'
            ),
            array(
                    'default' => 100,
                    'field' => 'limit',
                    'label' => 'Количество возвращаемых записей',
                    'rules' => '',
                    'type' => 'int'
            ),
            array(
                    'field' => 'mz',
                    'label' => 'Минздрав',
                    'rules' => '',
                    'type' => 'int'
            )
    ),
    'loadRequestInfo' => array(
            array(
                    'field' => 'RequestList_id',
                    'label' => 'Ид заявки МО',
                    'rules' => '',
                    'type' => 'id'
                )
    ),
    'loadLicenceList' => array(
        array(
            'field' => 'PlanYear',
            'label' => 'Год планирования',
            'rules' => '',
            'type' => 'int'
        ),
        array(
            'field' => 'lpu_id',
            'label' => 'Ид ЛПУ',
            'rules' => '',
            'type' => 'int'
        )
    ),
    'loadCatList' => array(
            array(
                    'field' => 'SprPlanCat_id',
                    'label' => 'Идентификатор категории планирования',
                    'rules' => '',
                    'type' => 'id'
                ),
            array(
                    'field' => 'SprVidMp_id',
                    'label' => 'Идентификатор вида МП',
                    'rules' => '',
                    'type' => 'id'
                ),
            array(
                    'field' => 'SprPlanCat_Name',
                    'label' => 'Наименование категории',
                    'rules' => '',
                    'type' => 'string'
                ),
            array(
                    'field' => 'RequestList_id',
                    'label' => 'Ид заявки МО',
                    'rules' => '',
                    'type' => 'id'
                ),
            array(
                    'field' => 'catCode',
                    'label' => 'Код категории',
                    'rules' => '',
                    'type' => 'int'
                ),
            array(
                    'field' => 'catName',
                    'label' => 'Имя категории',
                    'rules' => '',
                    'type' => 'string'
                ),
            array(
                    'field' => 'catNoVol',
                    'label' => 'Без объема',
                    'rules' => '',
                    'type' => 'boolean'
                )
    ),
    'loadLpuList' => array(
            array(
                    'field' => 'Lpu_id',
                    'label' => 'Ид ЛПУ',
                    'rules' => '',
                    'type' => 'id'
                ),
            array(
                    'field' => 'Lpu_Nick',
                    'label' => 'Наименование ЛПУ',
                    'rules' => '',
                    'type' => 'string'
                )
    ),
    'loadLpuLevelList' => array(
            array(
                    'field' => 'LpuLevel_id',
                    'label' => 'Ид уровня',
                    'rules' => '',
                    'type' => 'id'
                ),
            array(
                    'field' => 'LpuLevel_Name',
                    'label' => 'Наименование уровня',
                    'rules' => '',
                    'type' => 'string'
                )
    ),
    'loadRequestStatusList' => array(
            array(
                    'field' => 'LpuLevel_id',
                    'label' => 'Ид уровня',
                    'rules' => '',
                    'type' => 'id'
                ),
            array(
                    'field' => 'LpuLevel_Name',
                    'label' => 'Наименование уровня',
                    'rules' => '',
                    'type' => 'string'
                )
    ),
    'loadVolPeriod' => array(
            array(
                    'field' => 'VolPeriod_id',
                    'label' => 'идентификатор',
                    'rules' => 'required',
                    'type' => 'int'
            )
    ),
    'loadVolPeriodList' => array(
            array(
                    'field' => 'VolPeriod_id',
                    'label' => 'Идентификатор рабочего периода',
                    'rules' => '',
                    'type' => 'id'
                ),
            array(
                    'field' => 'VolPeriod_Name',
                    'label' => 'Наименование',
                    'rules' => '',
                    'type' => 'string'
            )
    ),
    'loadVidMPList' => array(
            array(
                    'field' => 'SprVidMp_id',
                    'label' => 'Идентификатор',
                    'rules' => '',
                    'type' => 'id'
                ),
            array(
                    'field' => 'SprVidMp_Name',
                    'label' => 'Наименование',
                    'rules' => '',
                    'type' => 'string'
            )
    ),
    'getNewId' => array(
        array(
                    'field' => 'VolPeriod_id',
                    'label' => 'Идентификатор периода',
                    'rules' => '',
                    'type' => 'id'
                )
    ),
    'loadVolPlanPeriodList' => array(
            array(
                    'field' => 'VolPeriod_id',
                    'label' => 'Идентификатор рабочего периода',
                    'rules' => '',
                    'type' => 'id'
                ),
            array(
                    'field' => 'VolPeriod_Name',
                    'label' => 'Наименование',
                    'rules' => '',
                    'type' => 'string'
            )
    ),
    'deleteVolPeriod' => array(
            array(
                    'field' => 'VolPeriod_id',
                    'label' => 'Идентификатор',
                    'rules' => 'required',
                    'type' => 'int'
            )
    ),
    'deleteVolFact' => array(
        array(
            'field' => 'VolPeriod_id',
            'label' => 'Идентификатор периода',
            'rules' => 'required',
            'type' => 'int'
        ),
        array(
            'field' => 'Table',
            'label' => 'Название таблицы',
            'rules' => 'required',
            'type' => 'string'
        )
    ),
    'deleteVolRequestList' => array(
            array(
                    'field' => 'RequestList_id',
                    'label' => 'Идентификатор',
                    'rules' => 'required',
                    'type' => 'int'
            )
    ),
    'deleteVolRequest' => array(
            array(
                    'field' => 'Request_id',
                    'label' => 'Идентификатор',
                    'rules' => 'required',
                    'type' => 'int'
            )
    ),
    'ExportXls' => array(
			array(
				'field' => 'Request_id',
				'label' => 'Идентификатор',
				'rules' => 'required',
				'type' => 'int'
			),
			array(
				'field' => 'part',
				'label' => 'Номер части документа',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'form',
				'label' => 'Форма',
				'rules' => 'required',
				'type' => 'string'
			)
	)
    
    );

    /**
     * Функция
     */
    function __construct() {
        parent::__construct();
        $this->load->database();
        if (isset($_REQUEST['VolPeriod_id']))
        {
            if ($_REQUEST['VolPeriod_id']==12010)
            {
                $this->options['normativ_fed_lgot'] = 400;
                $this->options['koef_fed_lgot'] = 1;
                $this->options['normativ_reg_lgot'] = 75;
                $this->options['koef_reg_lgot'] = 1;
            }
            if ($_REQUEST['VolPeriod_id']==22010)
            {
                $this->options['normativ_fed_lgot'] = 560;
                $this->options['koef_fed_lgot'] = 1;
                $this->options['normativ_reg_lgot'] = 125;
                $this->options['koef_reg_lgot'] = 1;
            }
            if ($_REQUEST['VolPeriod_id']==32010)
            {
                $this->options['normativ_fed_lgot'] = 590;
                $this->options['koef_fed_lgot'] = 1;
                $this->options['normativ_reg_lgot'] = 190;
                $this->options['koef_reg_lgot'] = 1;
            }
            if ($_REQUEST['VolPeriod_id']==42010)
            {
                $this->options['normativ_fed_lgot'] = 570;
                $this->options['koef_fed_lgot'] = 1;
                $this->options['normativ_reg_lgot'] = 190;
                $this->options['koef_reg_lgot'] = 1;
            }
            if ($_REQUEST['VolPeriod_id']==12011)
            {
                $this->options['normativ_fed_lgot'] = 600;
                $this->options['koef_fed_lgot'] = 1;
                $this->options['normativ_reg_lgot'] = 100;
                $this->options['koef_reg_lgot'] = 1;
            }
            if ($_REQUEST['VolPeriod_id']==22011)
            {
                $this->options['normativ_fed_lgot'] = 590;
                $this->options['koef_fed_lgot'] = 1;
                $this->options['normativ_reg_lgot'] = 110;
                $this->options['koef_reg_lgot'] = 1;
            }
            if ($_REQUEST['VolPeriod_id']==32011)
            {
                $this->options['normativ_fed_lgot'] = 590;
                $this->options['koef_fed_lgot'] = 1;
                $this->options['normativ_reg_lgot'] = 130;
                $this->options['koef_reg_lgot'] = 1;
            }
            if ($_REQUEST['VolPeriod_id']==42011)
            {
                $this->options['normativ_fed_lgot'] = 590;
                $this->options['koef_fed_lgot'] = 1;
                $this->options['normativ_reg_lgot'] = 130;
                $this->options['koef_reg_lgot'] = 1;
            }
            if ($_REQUEST['VolPeriod_id']==12012)
            {
                $this->options['normativ_fed_lgot'] = 630;
                $this->options['koef_fed_lgot'] = 1;
                $this->options['normativ_reg_lgot'] = 140;
                $this->options['koef_reg_lgot'] = 1;
            }
            if ($_REQUEST['VolPeriod_id']==22012)
            {
                $this->options['normativ_fed_lgot'] = 630;
                $this->options['koef_fed_lgot'] = 1;
                $this->options['normativ_reg_lgot'] = 140;
                $this->options['koef_reg_lgot'] = 1;
            }
            if ($_REQUEST['VolPeriod_id']==52012)
            {
                $this->options['normativ_fed_lgot'] = 630;
                $this->options['koef_fed_lgot'] = 1;
                $this->options['normativ_reg_lgot'] = 140;
                $this->options['koef_reg_lgot'] = 1;
            }
            if ($_REQUEST['VolPeriod_id']==32012)
            {
                $this->options['normativ_fed_lgot'] = 800;
                $this->options['koef_fed_lgot'] = 1;
                $this->options['normativ_reg_lgot'] = 180;
                $this->options['koef_reg_lgot'] = 1;
            }
            if ($_REQUEST['VolPeriod_id']==42012)
            {
                $this->options['normativ_fed_lgot'] = 800;
                $this->options['koef_fed_lgot'] = 1;
                $this->options['normativ_reg_lgot'] = 180;
                $this->options['koef_reg_lgot'] = 1;
            }
            if ($_REQUEST['VolPeriod_id']==12013)
            {
                $this->options['normativ_fed_lgot'] = 700;
                $this->options['koef_fed_lgot'] = 1;
                $this->options['normativ_reg_lgot'] = 220;
                $this->options['koef_reg_lgot'] = 1;
            }
            if (in_array($_REQUEST['VolPeriod_id'], array(22013, 32013, 42013, 12014, 22014, 32014, 42014)))
            {
                $this->options['normativ_fed_lgot'] = 650;
                $this->options['koef_fed_lgot'] = 1;
                $this->options['normativ_reg_lgot'] = 250;
                $this->options['koef_reg_lgot'] = 1;
            }
            if (in_array($_REQUEST['VolPeriod_id'], array(42014)))
            {
                $this->options['normativ_fed_lgot'] = 380;
                $this->options['koef_fed_lgot'] = 1;
                $this->options['normativ_reg_lgot'] = 50;
                $this->options['koef_reg_lgot'] = 1;
            }
            if (in_array($_REQUEST['VolPeriod_id'], array(12015)))
            {
                $this->options['normativ_fed_lgot'] = 390;
                $this->options['koef_fed_lgot'] = 1;
                $this->options['normativ_reg_lgot'] = 70;
                $this->options['koef_reg_lgot'] = 1;
            }
            if (in_array($_REQUEST['VolPeriod_id'], array(22015, 32015, 42015)))
            {
                $this->options['normativ_fed_lgot'] = 390;
                $this->options['koef_fed_lgot'] = 1;
                $this->options['normativ_reg_lgot'] = 75;
                $this->options['koef_reg_lgot'] = 1;
            }
            if (in_array($_REQUEST['VolPeriod_id'], array(62036)))
            {
                $this->options['normativ_fed_lgot'] = 310;
                $this->options['koef_fed_lgot'] = 1;
                $this->options['normativ_reg_lgot'] = 90;
                $this->options['koef_reg_lgot'] = 1;
            }
            if (in_array($_REQUEST['VolPeriod_id'], array(62039)))
            {
                $this->options['normativ_fed_lgot'] = 408;
                $this->options['koef_fed_lgot'] = 1;
                $this->options['normativ_reg_lgot'] = 130;
                $this->options['koef_reg_lgot'] = 1;
            }
            if (in_array($_REQUEST['VolPeriod_id'], array(62157)))
            {
                $this->options['normativ_fed_lgot'] = 425;
                $this->options['koef_fed_lgot'] = 1;
                $this->options['normativ_reg_lgot'] = 138;
                $this->options['koef_reg_lgot'] = 1;
            }
        }
    }

    /**
     * Получение методов
     */
    function index() {
        if (!isset($_SESSION['login']))
        {
            // тут перекидываем на форму логина
        }
        elseif ( !isset($_REQUEST['method']) )
            header("Location: /?c=promed");

        // Временно закрыть доступ
        /*
        $val = array('Error_Code' => 1, 'Error_Msg' => 'Доступ к заявке временно закрыт!');
        array_walk($val, 'ConvertFromWin1251ToUTF8');
        $this->ReturnData($val);
        return false;
        */
        if (isset($_REQUEST['method']))
        {
            switch ($_REQUEST['method'])
            {
                case 'checkDrugRequestLimitExceed':
                        $this->checkDrugRequestLimitExceed();
                break;
                case 'getPersonGrid':
                        $this->getDrugRequestPersonGrid();
                break;
                case 'saveDrugRequestPerson': case 'saveDrugRequestRow': case 'saveDrugRequest': case 'setDrugRequestLpuClose': case 'setDrugRequestLpuUt': case 'saveDrugRequestLpu': case 'setDrugRequestLpuReallocated':
                        $this->SaveObject($_REQUEST['method']);
                break;
                case 'deleteDrugRequestPerson': case 'deleteDrugRequestRow': case 'deleteVolPeriod': case 'deleteDrugRequestRow':
                        $this->DeleteObject($_REQUEST['method']);
                break;
                case 'getDrugRequestRow':
                        $this->getDrugRequestRowGrid();
                break;
                case 'getDrugRequest':
                        $this->getDrugRequestGrid();
                break;
                case 'getDrugRequestSum':
                        $this->getDrugRequestGridSum();
                break;
                case 'getDrugRequestLpuClose':
                        $this->getDrugRequestLpuClose();
                break;
                case 'getDrugRequestLpuUt':
                        $this->getDrugRequestLpuUt();
                break;
                case 'getDrugRequestLpuReallocated':
                        $this->getDrugRequestLpuReallocated();
                break;
                case 'getDrugRequestLast':
                        $this->getDrugRequestLast();
                break;
                case 'loadDrugCombo':
                        $this->loadDrugProtoMnnList();
                break;
                case 'loadMnnCombo':
                    $this->loadDrugMnnList();
                break;
                case 'printDrugRequest':
                        $this->printDrugRequest();
                default:
                        die;
            }
            return false;
        }
        $this->load->helper('Main');
        $this->load->view("index");
    }
 

    /**
     * Сохранение
     */
    function SaveObject($method)
    {
        $this->load->model('DrugRequest_model', 'drmodel');
        $this->load->helper('Text');

        $data = $this->ProcessInputData($method,true);
        if($data === false) {return false;}

        $val = array();

        $err = $this->getObjectCheck($this->drmodel, $data, $method);
        if (strlen($err) > 0) 
        {
            $this->ReturnError($err);
            return false;
        }
        if( method_exists($this->drmodel, $method) )
            $result = $this->drmodel->$method($data);
        else
            return false;

        if (is_array($result) && (count($result) == 1))
        {
            if ($result[0]['Error_Code']>0)
            {
                $result[0]['success'] = false;
            }
            else 
            {
                $result[0]['success'] = true;
            }
            $val = $result[0];
        }
        else
        {
            $this->ReturnError('Системная ошибка при выполнении скрипта',100002);
            return false;
        }
        array_walk($val, 'ConvertFromWin1251ToUTF8');
        $this->ReturnData($val);
    }
    
    /**
     * выполнение контроля на утверждение заявки
     */
    function doControl() {
        $data = $this->ProcessInputData('doControl', true, true);
        if ($data) {
            $this->load->model('VolPeriods_model', 'VolPeriods_model');
            $response = $this->VolPeriods_model->doControl($data);
            $this->ProcessModelList($response, true, true)->ReturnData();
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * проверяет, указано ли количество бригад СМП
     */
    function checkSmpTeamExists() {
        $data = $this->ProcessInputData('checkSmpTeamExists', true, true);
        if ($data) {
            $this->load->model('VolPeriods_model', 'VolPeriods_model');
            $response = $this->VolPeriods_model->checkSmpTeamExists($data);
            $this->ProcessModelList($response, true, true)->ReturnData();
            return true;
        } else {
            return false;
        }
    }

    /**
     * Удаление
     */
    function DeleteObject($method)
    {
        $this->load->model('DrugRequest_model', 'drmodel');
        $this->load->helper('Text');

        $data = $this->ProcessInputData('DeleteObject',true);
        if($data === false) {return false;}

        $data['session'] = $_SESSION;

        $err = $this->getObjectCheck($this->drmodel, $data, $method);
        if (strlen($err) > 0)
        {
            $this->ReturnError($err);
            return false;
        }
        if( method_exists($this->drmodel, $method) )
            $result = $this->drmodel->$method($data);
        else
            return false;

        if (is_array($result) && (count($result) == 1))
        {
            if ($result[0]['Error_Code']>0)
            {
                $result[0]['success'] = false;
            }
            else
            {
                $result[0]['success'] = true;
            }
            $val = $result[0];
        }
        else
        {
            $this->ReturnError('Системная ошибка при выполнении скрипта',100002);
            return false;
        }
        array_walk($val, 'ConvertFromWin1251ToUTF8');
        $this->ReturnData($val);
    }

    /**
     * Сохранение периода планирования
     */
    function saveVolPeriod() {
        $data = $this->ProcessInputData('saveVolPeriod', true, true);
        if ($data) {
            $this->load->model('VolPeriods_model', 'VolPeriods_model');
            $response = $this->VolPeriods_model->saveVolPeriod($data);
            $this->ProcessModelSave($response, true, $response)->ReturnData();
            return true;
        } else {
            return false;
        }
    }

    /**
     * Добавление МО в список заявок
     */
    function addLpu2Request() {
        $data = $this->ProcessInputData('addLpu2Request', true, true);
        if ($data) {
            $this->load->model('VolPeriods_model', 'VolPeriods_model');
            $response = $this->VolPeriods_model->addLpu2Request($data);
            $this->ProcessModelSave($response, true, true);//->ReturnData();
            return true;
        } else {
            return false;
        }
    }

    /**
     * Собрать фактические объемы по КС
     */
    function collectFactsStac() {
        $data = $this->ProcessInputData('collectFactsStac', true, true);
        if ($data) {
            $this->load->model('VolPeriods_model', 'VolPeriods_model');
            $response = $this->VolPeriods_model->collectFactsStac($data);
            $this->ProcessModelSave($response, true, $response)->ReturnData();
            return true;
        } else {
            return false;
        }
    }

    /**
     * Собрать фактические объемы по КС КПГ/КСГ
     */
    function collectFactsStacKSG() {
        $data = $this->ProcessInputData('collectFactsStacKSG', true, true);
        if ($data) {
            $this->load->model('VolPeriods_model', 'VolPeriods_model');
            $response = $this->VolPeriods_model->collectFactsStacKSG($data);
            $this->ProcessModelSave($response, true, $response)->ReturnData();
            return true;
        } else {
            return false;
        }
    }

    /**
     * Собрать фактические объемы по ДС
     */
    function collectFactsDS() {
        $data = $this->ProcessInputData('collectFactsDS', true, true);
        if ($data) {
            $this->load->model('VolPeriods_model', 'VolPeriods_model');
            $response = $this->VolPeriods_model->collectFactsDS($data);
            $this->ProcessModelSave($response, true, $response)->ReturnData();
            return true;
        } else {
            return false;
        }
    }

    /**
     * Собрать фактические объемы по ДС КПГ/КСГ
     */
    function collectFactsDSKSG() {
        $data = $this->ProcessInputData('collectFactsDSKSG', true, true);
        if ($data) {
            $this->load->model('VolPeriods_model', 'VolPeriods_model');
            $response = $this->VolPeriods_model->collectFactsDSKSG($data);
            $this->ProcessModelSave($response, true, $response)->ReturnData();
            return true;
        } else {
            return false;
        }
    }

    /**
     * Собрать фактические объемы по ВМП
     */
    function collectFactsVMP() {
        $data = $this->ProcessInputData('collectFactsVMP', true, true);
        if ($data) {
            $this->load->model('VolPeriods_model', 'VolPeriods_model');
            $response = $this->VolPeriods_model->collectFactsVMP($data);
            $this->ProcessModelSave($response, true, $response)->ReturnData();
            return true;
        } else {
            return false;
        }
    }

    /**
     * Собрать фактические объемы по АПП
     */
    function collectFactsApp() {
        $data = $this->ProcessInputData('collectFactsApp', true, true);
        if ($data) {
            $this->load->model('VolPeriods_model', 'VolPeriods_model');
            $response = $this->VolPeriods_model->collectFactsAPP($data);
            $this->ProcessModelSave($response, true, $response)->ReturnData();
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * Собрать фактические объемы по АПП консультативные пос.
     */
    function collectFactsAppCons() {
        $data = $this->ProcessInputData('collectFactsAppCons', true, true);
        if ($data) {
            $this->load->model('VolPeriods_model', 'VolPeriods_model');
            $response = $this->VolPeriods_model->collectFactsAppCons($data);
            $this->ProcessModelSave($response, true, $response)->ReturnData();
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * Собрать фактические объемы по АПП НМП
     */
    function collectFactsAppNmp() {
        $data = $this->ProcessInputData('collectFactsAppNmp', true, true);
        if ($data) {
            $this->load->model('VolPeriods_model', 'VolPeriods_model');
            $response = $this->VolPeriods_model->collectFactsAppNmp($data);
            $this->ProcessModelSave($response, true, $response)->ReturnData();
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * Собрать фактические объемы по АПП образабы
     */
    function collectFactsAppTreatment() {
        $data = $this->ProcessInputData('collectFactsAppTreatment', true, true);
        if ($data) {
            $this->load->model('VolPeriods_model', 'VolPeriods_model');
            $response = $this->VolPeriods_model->collectFactsAppTreatment($data);
            $this->ProcessModelSave($response, true, $response)->ReturnData();
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * Собрать фактические объемы по АПП неприкрепленные
     */
    function collectFactsAppProfNotAttach() {
        $data = $this->ProcessInputData('collectFactsAppProfNotAttach', true, true);
        if ($data) {
            $this->load->model('VolPeriods_model', 'VolPeriods_model');
            $response = $this->VolPeriods_model->collectFactsAppProfNotAttach($data);
            $this->ProcessModelSave($response, true, $response)->ReturnData();
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * Собрать фактические объемы по АПП прикрепленные
     */
    function collectFactsAppProfAttach() {
        $data = $this->ProcessInputData('collectFactsAppProfAttach', true, true);
        if ($data) {
            $this->load->model('VolPeriods_model', 'VolPeriods_model');
            $response = $this->VolPeriods_model->collectFactsAppProfAttach($data);
            $this->ProcessModelSave($response, true, $response)->ReturnData();
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * Собрать фактические объемы по АПП ЦЗ
     */
    function collectFactsAppProfCZ() {
        $data = $this->ProcessInputData('collectFactsAppProfCZ', true, true);
        if ($data) {
            $this->load->model('VolPeriods_model', 'VolPeriods_model');
            $response = $this->VolPeriods_model->collectFactsAppProfCZ($data);
            $this->ProcessModelSave($response, true, $response)->ReturnData();
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * Собрать фактические объемы по АПП диспансеризация
     */
    function collectFactsAppProfDisp() {
        $data = $this->ProcessInputData('collectFactsAppProfDisp', true, true);
        if ($data) {
            $this->load->model('VolPeriods_model', 'VolPeriods_model');
            $response = $this->VolPeriods_model->collectFactsAppProfDisp($data);
            $this->ProcessModelSave($response, true, $response)->ReturnData();
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * Собрать фактические объемы по АПП медосмотры
     */
    function collectFactsAppProf() {
        $data = $this->ProcessInputData('collectFactsAppProf', true, true);
        if ($data) {
            $this->load->model('VolPeriods_model', 'VolPeriods_model');
            $response = $this->VolPeriods_model->collectFactsAppProf($data);
            $this->ProcessModelSave($response, true, $response)->ReturnData();
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * Собрать фактические объемы по АПП профилактические посещения
     */
    function collectFactsAppProfAll() {
        $data = $this->ProcessInputData('collectFactsAppProfAll', true, true);
        if ($data) {
            $this->load->model('VolPeriods_model', 'VolPeriods_model');
            $response = $this->VolPeriods_model->collectFactsAppProfAll($data);
            $this->ProcessModelSave($response, true, $response)->ReturnData();
            return true;
        } else {
            return false;
        }
    }

    /**
     * Собрать фактические объемы по СМП
     */
    function collectFactsSMP() {
        $data = $this->ProcessInputData('collectFactsSMP', true, true);
        if ($data) {
            $this->load->model('VolPeriods_model', 'VolPeriods_model');
            $response = $this->VolPeriods_model->collectFactsSMP($data);
            $this->ProcessModelSave($response, true, $response)->ReturnData();
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * Собрать фактические объемы по ЛДИ
     */
    function collectFactsLdi() {
        $data = $this->ProcessInputData('collectFactsLdi', true, true);
        if ($data) {
            $this->load->model('VolPeriods_model', 'VolPeriods_model');
            $response = $this->VolPeriods_model->collectFactsLdi($data);
            $this->ProcessModelSave($response, true, $response)->ReturnData();
            return true;
        } else {
            return false;
        }
    }

    /**
     * Собрать фактические объемы по Заместительной почечной терапии
     */
    function collectFactsZpt() {
        $data = $this->ProcessInputData('collectFactsZpt', true, true);
        if ($data) {
            $this->load->model('VolPeriods_model', 'VolPeriods_model');
            $response = $this->VolPeriods_model->collectFactsZpt($data);
            $this->ProcessModelSave($response, true, $response)->ReturnData();
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * Собрать фактические объемы по ЭКО
     */
    function collectFactsEco() {
        $data = $this->ProcessInputData('collectFactsEco', true, true);
        if ($data) {
            $this->load->model('VolPeriods_model', 'VolPeriods_model');
            $response = $this->VolPeriods_model->collectFactsEco($data);
            $this->ProcessModelSave($response, true, $response)->ReturnData();
            return true;
        } else {
            return false;
        }
    }

    /**
     * Создать заявку
     */
    function buildRequest() {
        $data = $this->ProcessInputData('buildRequest', true, true);
        if ($data) {
            $this->load->model('VolPeriods_model', 'VolPeriods_model');
            $response = $this->VolPeriods_model->buildRequest($data);
            $this->ProcessModelSave($response, true, $response)->ReturnData();
            return true;
        } else {
            return false;
        }
    }

    /**
     * Загрузка периода планирования
     */
    function loadVolPeriod() {
        $data = $this->ProcessInputData('loadVolPeriod', true);
        if ($data){			
            $this->load->model('VolPeriods_model', 'VolPeriods_model');
            $response = $this->VolPeriods_model->loadVolPeriod($data);
            $this->ProcessModelList($response, true, true)->formatDatetimeFields()->ReturnData();
            return true;
        } else {
            return false;
        }
    }

    /**
     * Загрузка списка периодов
     */
    function loadVolPeriodList() {
        $data = $this->ProcessInputData('loadVolPeriodList', true);
        if ($data){			
            $this->load->model('VolPeriods_model', 'VolPeriods_model');
            $response = $this->VolPeriods_model->loadVolPeriodList($data);
            $this->ProcessModelList($response, true, true)->formatDatetimeFields()->ReturnData();
            return true;
        } else {
            return false;
        }
    }

    /**
     * Загрузка списка рабочих периодов
     */
    function loadRequestData() {
        $data = $this->ProcessInputData('loadRequestData', true, true);
        if ($data) {
            $this->load->model('VolPeriods_model', 'VolPeriods_model');
            $response = $this->VolPeriods_model->loadRequestData($data);
            $this->ProcessModelList($response, true, true)->ReturnData();
            return true;
        } else {
            return false;
        }
    }

    /**
     * Проверить наличие фактических объемов
     */
    function checkFact() {
        $data = $this->ProcessInputData('checkFact', true, true);
        if ($data) {
            $this->load->model('VolPeriods_model', 'VolPeriods_model');
            $response = $this->VolPeriods_model->checkFact($data);
            $this->ProcessModelList($response, true, true)->ReturnData();
            return true;
        } else {
            return false;
        }
    }

    /**
     * Загрузка справочника разрешенности планирования
     */
    function loadSprAllowPlan() {
        $data = $this->ProcessInputData('loadSprAllowPlan', true, true);
        if ($data) {
            $this->load->model('VolPeriods_model', 'VolPeriods_model');
            $response = $this->VolPeriods_model->loadSprAllowPlan($data);
            $this->ProcessModelList($response, true, true)->ReturnData();
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * Загрузка списка МО, не имеющих заявок
     */
    function getLpuList() {
        $data = $this->ProcessInputData('getLpuList', true, true);
        if ($data) {
            $this->load->model('VolPeriods_model', 'VolPeriods_model');
            $response = $this->VolPeriods_model->getLpuList($data);
            $this->ProcessModelList($response, true, true)->ReturnData();
            return true;
        } else {
            return false;
        }
    }

    /**
     * Получить план по МО
     */
    function getPlanByMo() {
        $data = $this->ProcessInputData('getPlanByMo', true, true);
        if ($data) {
            $this->load->model('VolPeriods_model', 'VolPeriods_model');
            $response = $this->VolPeriods_model->getPlanByMo($data);
            $this->ProcessModelList($response, true, true)->ReturnData();
            return true;
        } else {
            return false;
        }
    }        

    /**
     * Загрузка заявки по КС
     */
    function loadRequestDataStac() {
        $data = $this->ProcessInputData('loadRequestDataStac', true, true);
        if ($data) {
            $this->load->model('VolPeriods_model', 'VolPeriods_model');
            $response = $this->VolPeriods_model->loadRequestDataStac($data);
            $this->ProcessModelList($response, true, true)->ReturnData();
            return true;
        } else {
            return false;
        }
    }

    /**
     * Загрузка заявки по ДС
     */
    function loadRequestDataDS() {
        $data = $this->ProcessInputData('loadRequestDataDS', true, true);
        if ($data) {
            $this->load->model('VolPeriods_model', 'VolPeriods_model');
            $response = $this->VolPeriods_model->loadRequestDataDS($data);
            $this->ProcessModelList($response, true, true)->ReturnData();
            return true;
        } else {
            return false;
        }
    }

    /**
     * Загрузка заявки по категории "справочная информация"
     */
    function loadSprInfo() {
        $data = $this->ProcessInputData('loadSprInfo', true, true);
        if ($data) {
            $this->load->model('VolPeriods_model', 'VolPeriods_model');
            $response = $this->VolPeriods_model->loadSprInfo($data);
            $this->ProcessModelList($response, true, true)->ReturnData();
            return true;
        } else {
            return false;
        }
    }

    /**
     * Загрузка заявки по АПП
     */
    function loadRequestDataApp() {
        $data = $this->ProcessInputData('loadRequestDataApp', true, true);
        if ($data) {
            $this->load->model('VolPeriods_model', 'VolPeriods_model');
            $response = $this->VolPeriods_model->loadRequestDataApp($data);
            $this->ProcessModelList($response, true, true)->ReturnData();
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * Загрузка заявки по АПП НМП
     */
    function loadRequestDataAppNmp() {
        $data = $this->ProcessInputData('loadRequestDataAppNmp', true, true);
        if ($data) {
            $this->load->model('VolPeriods_model', 'VolPeriods_model');
            $response = $this->VolPeriods_model->loadRequestDataAppNmp($data);
            $this->ProcessModelList($response, true, true)->ReturnData();
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * Загрузка заявки по АПП конс. пос.
     */
    function loadRequestDataAppCons() {
        $data = $this->ProcessInputData('loadRequestDataAppCons', true, true);
        if ($data) {
            $this->load->model('VolPeriods_model', 'VolPeriods_model');
            $response = $this->VolPeriods_model->loadRequestDataAppCons($data);
            $this->ProcessModelList($response, true, true)->ReturnData();
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * Загрузка заявки по АПП проф.
     */
    function loadRequestDataAppProf() {
        $data = $this->ProcessInputData('loadRequestDataAppProf', true, true);
        if ($data) {
            $this->load->model('VolPeriods_model', 'VolPeriods_model');
            $response = $this->VolPeriods_model->loadRequestDataAppProf($data);
            $this->ProcessModelList($response, true, true)->ReturnData();
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * Загрузка заявки по АПП диспансеризация
     */
    function loadRequestDataAppDisp() {
        $data = $this->ProcessInputData('loadRequestDataAppDisp', true, true);
        if ($data) {
            $this->load->model('VolPeriods_model', 'VolPeriods_model');
            $response = $this->VolPeriods_model->loadRequestDataAppDisp($data);
            $this->ProcessModelList($response, true, true)->ReturnData();
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * Загрузка заявки по АПП прикрепленные
     */
    function loadRequestDataAppProfAttach() {
        $data = $this->ProcessInputData('loadRequestDataAppProfAttach', true, true);
        if ($data) {
            $this->load->model('VolPeriods_model', 'VolPeriods_model');
            $response = $this->VolPeriods_model->loadRequestDataAppProfAttach($data);
            $this->ProcessModelList($response, true, true)->ReturnData();
            return true;
        } else {
            return false;
        }
    }

    /**
     * Загрузка заявки по ЗПТ
     */
    function loadRequestDataZpt() {
        $data = $this->ProcessInputData('loadRequestDataZpt', true, true);
        if ($data) {
            $this->load->model('VolPeriods_model', 'VolPeriods_model');
            $response = $this->VolPeriods_model->loadRequestDataZpt($data);
            $this->ProcessModelList($response, true, true)->ReturnData();
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * Загрузка заявки по ЭКО
     */
    function loadRequestDataEco() {
        $data = $this->ProcessInputData('loadRequestDataEco', true, true);
        if ($data) {
            $this->load->model('VolPeriods_model', 'VolPeriods_model');
            $response = $this->VolPeriods_model->loadRequestDataEco($data);
            $this->ProcessModelList($response, true, true)->ReturnData();
            return true;
        } else {
            return false;
        }
    }

    /**
     * Загрузка заявки по ЛДИ
     */
    function loadRequestDataLdi() {
        $data = $this->ProcessInputData('loadRequestDataLdi', true, true);
        if ($data) {
            $this->load->model('VolPeriods_model', 'VolPeriods_model');
            $response = $this->VolPeriods_model->loadRequestDataLdi($data);
            $this->ProcessModelList($response, true, true)->ReturnData();
            return true;
        } else {
            return false;
        }
    }

    /**
     * Загрузка заявки по СМП
     */
    function loadRequestDataSmp() {
        $data = $this->ProcessInputData('loadRequestDataSmp', true, true);
        if ($data) {
            $this->load->model('VolPeriods_model', 'VolPeriods_model');
            $response = $this->VolPeriods_model->loadRequestDataSmp($data);
            $this->ProcessModelList($response, true, true)->ReturnData();
            return true;
        } else {
            return false;
        }
    }

    /**
     * Загрузка заявки по ВМП
     */
    function loadRequestDataVmp() {
        $data = $this->ProcessInputData('loadRequestDataVmp', true, true);
        if ($data) {
            $this->load->model('VolPeriods_model', 'VolPeriods_model');
            $response = $this->VolPeriods_model->loadRequestDataVmp($data);
            $this->ProcessModelList($response, true, true)->ReturnData();
            return true;
        } else {
            return false;
        }
    }

    /**
     * Загрузка заявок
     */
    function loadVolRequest() {
        $data = $this->ProcessInputData('loadVolRequest', true, true);
        if ($data) {
            $this->load->model('VolPeriods_model', 'VolPeriods_model');
            $response = $this->VolPeriods_model->loadVolRequest($data);
            $this->ProcessModelList($response, true, true)->ReturnData();
            return true;
        } else {
            return false;
        }
    }

    /**
     * Загрузка списка заявок
     */
    function loadVolRequestList() {
        $data = $this->ProcessInputData('loadVolRequestList', true, true);
        if ($data) {
            $this->load->model('VolPeriods_model', 'VolPeriods_model');
            $response = $this->VolPeriods_model->loadVolRequestList($data);
            $this->ProcessModelList($response, true, true)->ReturnData();

            return true;

        } else {
            return false;
        }
    }

    /**
     * Загрузка заявки по КС КПГ/КСГ
     */
    function loadRequestDataStacKSG() {
        $data = $this->ProcessInputData('loadRequestDataStacKSG', true, true);
        if ($data) {
            $this->load->model('VolPeriods_model', 'VolPeriods_model');
            $response = $this->VolPeriods_model->loadRequestDataStacKSG($data);
            $this->ProcessModelList($response, true, true)->ReturnData();
            return true;
        } else {
            return false;
        }
    }

    /**
     * Загрузка заявки по ДС КПГ/КСГ
     */
    function loadRequestDataDSKSG() {
        $data = $this->ProcessInputData('loadRequestDataDSKSG', true, true);
        if ($data) {
            $this->load->model('VolPeriods_model', 'VolPeriods_model');
            $response = $this->VolPeriods_model->loadRequestDataDSKSG($data);
            $this->ProcessModelList($response, true, true)->ReturnData();
            return true;
        } else {
            return false;
        }
    }

    /**
     * Сохранение данных в заявке по КС КПГ/КСГ
     */
    function saveRequestDataFactsStacKSG() {
        $data = $this->ProcessInputData('saveRequestDataFactsStacKSG', true, true);
        if ($data) {
            $this->load->model('VolPeriods_model', 'VolPeriods_model');
            $response = $this->VolPeriods_model->saveRequestDataFactsStacKSG($data);
            $this->ProcessModelSave($response, true, $response)->ReturnData();
            return true;
        } else {
            return false;
        }
    }

    //        function setRequestStatus() {
    //            $data = $this->ProcessInputData('setRequestStatus', true, true);
    //		if ($data) {
    //			$this->load->model('VolPeriods_model', 'VolPeriods_model');
    //			$response = $this->VolPeriods_model->setRequestStatus($data);
    //			$this->ProcessModelSave($response, true, $response)->ReturnData();
    //			return true;
    //		} else {
    //			return false;
    //		}
    //        }

    /**
     * Задать статус заявки
     */
    function setRequestStatus() {
        $data = $this->ProcessInputData('setRequestStatus', true, true);
        if ($data) 
        {
            $this->load->model('VolPeriods_model', 'VolPeriods_model');
            $response = $this->VolPeriods_model->setRequestStatus($data);
            $this->ProcessModelSave($response, true, $response)->ReturnData();
            return true;
        } 
        else 
        {
            return false;
        }
    }

    /**
     * сохранить список заявок
     */
    function saveRequestList() {
        $data = $this->ProcessInputData('saveRequestList', true, true);
        if ($data) 
        {
            $this->load->model('VolPeriods_model', 'VolPeriods_model');
            $response = $this->VolPeriods_model->saveRequestList($data);
            $this->ProcessModelSave($response, true, $response)->ReturnData();
            return true;
        } 
        else 
        {
            return false;
        }
    }
    
    /**
     * установить статус
     */
    function setRequestStatusAll() {
        $data = $this->ProcessInputData('setRequestStatusAll', true, true);
        if ($data) 
        {
            $this->load->model('VolPeriods_model', 'VolPeriods_model');
            $response = $this->VolPeriods_model->setRequestStatusAll($data);
            $this->ProcessModelSave($response, true, $response)->ReturnData();
            return true;
        } 
        else 
        {
            return false;
        }
    }
    
    /**
     * установить статус
     */
    function updateLic() {
        $data = $this->ProcessInputData('updateLic', true, true);
        if ($data) 
        {
            $this->load->model('VolPeriods_model', 'VolPeriods_model');
            $response = $this->VolPeriods_model->updateLic($data);
            $this->ProcessModelSave($response, true, $response)->ReturnData();
            return true;
        } 
        else 
        {
            return false;
        }
    }

    /**
     * сохранить изменения в заявке
     */
    function saveRequestData() {
        $data = $this->ProcessInputData('saveRequestData', true, true);
        if ($data) {
            $this->load->model('VolPeriods_model', 'VolPeriods_model');
            $response = $this->VolPeriods_model->saveRequestData($data);
            $this->ProcessModelSave($response, true, $response)->ReturnData();
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * сохранить данные категории
     */
    function savePlanCatData() {
        $data = $this->ProcessInputData('savePlanCatData', true, true);
        if ($data) {
            $this->load->model('VolPeriods_model', 'VolPeriods_model');
            $response = $this->VolPeriods_model->savePlanCatData($data);
            $this->ProcessModelSave($response, true, $response)->ReturnData();
            return true;
        } else {
            return false;
        }
    }

    /**
     * сохранить изменения в заявке в категории "справочная информация"
     */
    function saveSprInfo() {
        $data = $this->ProcessInputData('saveSprInfo', true, true);
        if ($data) {
            $this->load->model('VolPeriods_model', 'VolPeriods_model');
            $response = $this->VolPeriods_model->saveSprInfo($data);
            $this->ProcessModelSave($response, true, $response)->ReturnData();
            return true;
        } else {
            return false;
        }
    }

    /**
     * загрузка информации о заявке
     */
    function loadRequestInfo() {
        $data = $this->ProcessInputData('loadRequestInfo', true, true);
        if ($data) {
            $this->load->model('VolPeriods_model', 'VolPeriods_model');
            $response = $this->VolPeriods_model->loadRequestInfo($data);
            $this->ProcessModelList($response, true, true)->ReturnData();
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * загрузка списка лицензий
     */
    function loadLicenceList() {
        $data = $this->ProcessInputData('loadLicenceList', true, true);
        if ($data) {
            $this->load->model('VolPeriods_model', 'VolPeriods_model');
            $response = $this->VolPeriods_model->loadLicenceList($data);
            $this->ProcessModelList($response, true, true)->ReturnData();
            return true;
        } else {
            return false;
        }
    }

    /**
     * загрузка списка категорий
     */
    function loadCatList() {
        $data = $this->ProcessInputData('loadCatList', true, true);
        if ($data) {
            $this->load->model('VolPeriods_model', 'VolPeriods_model');
            $response = $this->VolPeriods_model->loadCatList($data);
            $this->ProcessModelList($response, true, true)->ReturnData();
            return true;
        } else {
            return false;
        }
    }

    /**
     * загрузка списка МО
     */
    function loadLpuList() {
        $data = $this->ProcessInputData('loadLpuList', true, true);
        if ($data) {
            $this->load->model('VolPeriods_model', 'VolPeriods_model');
            $response = $this->VolPeriods_model->loadLpuList($data);
            $this->ProcessModelList($response, true, true)->ReturnData();
            return true;
        } else {
            return false;
        }
    }

    /**
     * загрузка списка уровней
     */
    function loadLpuLevelList() {
        $data = $this->ProcessInputData('loadLpuLevelList', true, true);
        if ($data) {
            $this->load->model('VolPeriods_model', 'VolPeriods_model');
            $response = $this->VolPeriods_model->loadLpuLevelList($data);
            $this->ProcessModelList($response, true, true)->ReturnData();
            return true;
        } else {
            return false;
        }
    }

    /**
     * загрузка списка статусов заявок
     */
    function loadRequestStatusList() {
        $data = $this->ProcessInputData('loadRequestStatusList', true, true);
        if ($data) {
            $this->load->model('VolPeriods_model', 'VolPeriods_model');
            $response = $this->VolPeriods_model->loadRequestStatusList($data);
            $this->ProcessModelList($response, true, true)->ReturnData();
            return true;
        } else {
            return false;
        }
    }

    /**
     * Загрузка списка видов мед. помощи
     */
    function loadVidMPList() {
        $data = $this->ProcessInputData('loadVidMPList', true, true);
        if ($data) {
            $this->load->model('VolPeriods_model', 'VolPeriods_model');
            $response = $this->VolPeriods_model->loadVidMPList($data);
            $this->ProcessModelList($response, true, true)->ReturnData();
            return true;
        } else {
            return false;
        }
    }

    /**
     * Загрузка списка периодов
     */
    function loadVolPlanPeriodList() {
        $data = $this->ProcessInputData('loadVolPlanPeriodList', true, true);
        if ($data) {
            $this->load->model('VolPeriods_model', 'VolPeriods_model');
            $response = $this->VolPeriods_model->loadVolPlanPeriodList($data);
            $this->ProcessModelList($response, true, true)->ReturnData();
            return true;
        } else {
            return false;
        }
    }

    /**
     * Удаление рабочего периода
     */
    function deleteVolPeriod() {
        $data = $this->ProcessInputData('deleteVolPeriod', true, true);
        if ($data) {
            $this->load->model('VolPeriods_model', 'VolPeriods_model');
            $response = $this->VolPeriods_model->deleteVolPeriod($data);
            $this->ProcessModelSave($response, true, $response)->ReturnData();
            return true;
        } else {
            return false;
        }
    }

    /**
     * удалить фактические объемы
     */
    function deleteVolFact() {
        $data = $this->ProcessInputData('deleteVolFact', true, true);
        if ($data) {
            $this->load->model('VolPeriods_model', 'VolPeriods_model');
            $response = $this->VolPeriods_model->deleteVolFact($data);
            $this->ProcessModelSave($response, true, $response)->ReturnData();
            return true;
        } else {
            return false;
        }
    }

    /**
     * удалить заявку из списка по МО
     */
    function deleteVolRequestList() {
        $data = $this->ProcessInputData('deleteVolRequestList', true, true);
        if ($data) {
            $this->load->model('VolPeriods_model', 'VolPeriods_model');
            $response = $this->VolPeriods_model->deleteVolRequestList($data);
            $this->ProcessModelSave($response, true, $response)->ReturnData();
            return true;
        } else {
            return false;
        }
    }

    /**
     * удалить заявку
     */
    function deleteVolRequest() {
        $data = $this->ProcessInputData('deleteVolRequest', true, true);
        if ($data) {
            $this->load->model('VolPeriods_model', 'VolPeriods_model');
            $response = $this->VolPeriods_model->deleteVolRequest($data);
            $this->ProcessModelSave($response, true, $response)->ReturnData();
            return true;
        } else {
            return false;
        }
    }

    /**
     * новый ИД
     */
    function getNewId() {
        $this->load->model('VolPeriods_model', 'VolPeriods_model');
        $response = $this->VolPeriods_model->getNewId();
        $this->ProcessModelList($response, true, true)->ReturnData();
        return true;
    }

    /**
     * Получение максимальной даты
     */
    function getVolPeriodMaxDate() {
        $this->load->model('VolPeriods_model', 'VolPeriods_model');
        $response = $this->VolPeriods_model->getVolPeriodMaxDate();
        $this->ProcessModelList($response, true, true)->ReturnData();
        return true;
    }
    
    /**
     * выгрузка в excel
     */
    function ExportXls() {
		set_time_limit(0);
		ini_set("memory_limit", "2048M");//"2048M"
		ini_set("max_execution_time", "0");
		ini_set("max_input_time", "0");
		ini_set("post_max_size", "220");
		ini_set("default_socket_timeout", "9999");
		ini_set("upload_max_filesize", "512M");
		$data = $this->ProcessInputData('ExportXls', true, true);
		if ($data) {
			$this->load->model('VolPeriods_model', 'VolPeriods_model');
			$response = $this->VolPeriods_model->ExportXls($data);
			return $response;
		}
		else 
			return false;
        //$this->ProcessModelList($response, true, true)->ReturnData();
	}
}