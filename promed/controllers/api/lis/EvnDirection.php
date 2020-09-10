<?php defined('BASEPATH') or die ('No direct script access allowed');

require(APPPATH.'libraries/SwREST_Controller.php');

/**
 * Class EvnDirection
 * @property Lis_EvnDirection_model dbmodel
 * @OA\Tag(
 *     name="EvnDirection",
 *     description="Направления на лабораторные исследования"
 * )
 */
class EvnDirection extends SwRest_Controller {
	protected $inputRules = array(
		'saveEvnDirection' => array(
			array('field' => 'redirectEvnDirection', 'label' => 'Признак перенаправления', 'rules' => '', 'type' => 'id'),
			array('field' => 'Diag_id', 'label' => 'Диагноз', 'rules' => '', 'type' => 'id'),
			array('field' => 'TreatmentType_id', 'label' => 'Тип предстоящего лечения', 'rules' => '', 'type' => 'id'),
			array('field' => 'Lpu_sid', 'label' => 'Направившая МО', 'rules' => '', 'type' => 'id'),
			array('field' => 'Lpu_id', 'label' => 'Идентификатор МО', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnDirection_IsNeedOper', 'label' => 'Необходимость операционного вмешательства', 'rules' => '', 'type' => 'checkbox'),
			array('field' => 'DirType_id', 'label' => 'Тип направления', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'PrehospDirect_id', 'label' => 'Кем направлен', 'rules' => '', 'type' => 'id'),
			array('field' => 'MedPersonal_Code', 'label' => 'Код врача', 'rules' => '', 'type' => 'string'),
			array('field' => 'MedicalCareFormType_id', 'label' => 'Форма помощи', 'rules' => '', 'type' => 'id'),
			array('field' => 'StudyTarget_id', 'label' => 'Цель исследования', 'rules' => '', 'type' => 'id'),
			array('field' => 'PayType_id', 'label' => 'Вид оплаты', 'rules' => '', 'type' => 'id'),
			array('field' => 'ConsultingForm_id', 'label' => 'Формы оказания консультации ', 'rules' => '', 'type' => 'id'),
			array('field' => 'Lpu_did', 'label' => 'МО куда направлен', 'rules' => '', 'type' => 'id'),
			array('field' => 'Org_oid', 'label' => 'Организация направления', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnDirection_Descr', 'label' => 'Обоснование', 'rules' => '', 'type' => 'string'),
			array('field' => 'EvnDirection_id', 'label' => 'Идентификатор направления', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnDirection_pid', 'label' => 'Идентификатор родительского события', 'rules' => '', 'type' => 'id'),
			array('field' => 'MedService_id', 'label' => 'Служба ', 'rules' => 'trim', 'type' => 'id'),
			array('field' => 'TimetableMedService_id', 'label' => 'Бирка раписания службы', 'rules' => 'trim', 'type' => 'id'),
			array('field' => 'UslugaComplex_did', 'label' => 'Услуга', 'rules' => '', 'type' => 'id'),
			array('field' => 'UslugaComplex_id', 'label' => 'Услуга', 'rules' => '', 'type' => 'id'),
			array('field' => 'order', 'label' => 'Заказ услуги', 'rules' => '', 'type' => 'string'),
			array('field' => 'MedSpec_fid', 'label' => 'Специальность', 'rules' => '', 'type' => 'id'),
			array('field' => 'RemoteConsultCause_id', 'label' => 'Цель консультирования ', 'rules' => 'trim', 'type' => 'id'),
			array('field' => 'EvnDirection_IsAuto', 'label' => 'Признак является ли направление системным Да/нет ', 'rules' => '', 'type' => 'id'),
			array('field' => 'PrescriptionType_Code', 'label' => 'Идентификатор ', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnPrescr_id', 'label' => 'Идентификатор ', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnDirection_Num', 'label' => 'Номер направления', 'rules' => 'trim', 'type' => 'string'),
			array('field' => 'EvnDirection_setDate', 'label' => 'Дата выписки направления', 'rules' => 'trim|required', 'type' => 'date'),
			array('field' => 'EvnDirection_desDT', 'label' => 'Желаемая дата направления', 'rules' => 'trim', 'type' => 'date'),
			array('field' => 'EvnDirection_setDateTime', 'label' => 'Время', 'rules' => '', 'type' => 'string'),
			array('field' => 'LpuSection_id', 'label' => 'Отделение, которое направило', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuSection_did', 'label' => 'Отделение куда направили', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuUnitType_did', 'label' => 'Условия оказания медицинской помощи', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuSectionProfile_id', 'label' => 'Профиль', 'rules' => '', 'type' => 'id'),
			array('field' => 'From_MedStaffFact_id', 'label' => 'Рабочее место врача', 'rules' => 'trim', 'type' => 'id'),// кто направил, для записи должности врача
			array('field' => 'MedPersonal_id', 'label' => 'Врач', 'rules' => 'trim', 'type' => 'id'),
			array('field' => 'MedPersonal_zid', 'label' => 'Зав. отделением', 'rules' => 'trim', 'type' => 'id'),
			array('field' => 'TimetableGraf_id', 'label' => 'Идентификатор бирки поликлиники', 'rules' => '', 'type' => 'id'),
			array('field' => 'TimetableStac_id', 'label' => 'Идентификатор бирки стационара', 'rules' => '', 'type' => 'id'),
			array('field' => 'TimetablePar_id', 'label' => 'Идентификатор бирки поликлиники', 'rules' => '', 'type' => 'id'),
			array('field' => 'Person_id', 'label' => 'Идентификатор человека', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'PersonEvn_id', 'label' => 'Идентификатор состояния человека', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'Server_id', 'label' => 'Идентификатор сервера', 'rules' => 'required', 'type' => 'int'),
			array('field' => 'EvnQueue_id','label' => 'Идентификатор записи в очереди', 'rules' => '', 'type' => 'id' ),
			array('field' => 'QueueFailCause_id','label' => 'Причина отмены направления из очереди', 'rules' => '', 'type' => 'id' ),
			array('field' => 'EvnUsluga_id', 'label' => 'Сохраненный заказ', 'rules' => '', 'type' => 'id'),
			array('field' => 'ARMType_id', 'label' => 'Идентификатор типа АРМа', 'rules' => '', 'type' => 'id'),
			array('field' => 'toQueue', 'label' => 'Флаг одновременной постановки в очередь', 'rules' => '', 'type' => 'checkbox'),
			array('field' => 'EvnDirection_IsCito', 'label' => 'Cito', 'rules' => 'trim', 'type' => 'id'),
			array('field' => 'EvnDirection_IsReceive', 'label' => 'К себе', 'rules' => '', 'type' => 'id'),
			array('field' => 'ConsultationForm_id', 'label' => 'Форма оказания консультации', 'rules' => '', 'type'  => 'id'),
			array('field' => 'isElectronicQueueRedirect', 'label' => 'Признак перенаправления талона ЭО', 'rules' => '', 'type'  => 'int')
		),
		'deleteEvnDirection' => array(
			array('field' => 'EvnDirection_id', 'label' => 'Идентификатор направления', 'rules' => 'required', 'type' => 'id'),
		),
		'cancelEvnDirection' => array(
			array('field' => 'EvnDirection_id', 'label' => 'Идентификатор направлкния', 'rules' => 'required', 'type'  => 'id'),
			array('field' => 'DirFailType_id', 'label' => 'Причина отмены направления', 'rules' => '', 'type'  => 'id'),
			array('field' => 'EvnStatusCause_id', 'label' => 'Причина изменения статуса события', 'rules' => '', 'type'  => 'id'),
			array('field' => 'EvnStatusCause_id', 'label' => 'Причина изменения статуса события', 'rules' => '', 'type'  => 'id'),
			array('field' => 'cancelType', 'label' => 'Вид отмены', 'rules' => '', 'type'  => 'string'),
		),
		'cancelEvnDirectionbyRecord' => array(
			array('field' => 'EvnDirection_id', 'label' => 'Идентификатор направлкния', 'rules' => '', 'type'  => 'id'),
			array('field' => 'TimetableMedService_id', 'label' => 'Идентификатор бирки', 'rules' => '', 'type'  => 'id'),
			array('field' => 'DirFailType_id', 'label' => 'Причина отмены направления', 'rules' => '', 'type'  => 'id'),
			array('field' => 'EvnStatusCause_id', 'label' => 'Причина изменения статуса события', 'rules' => '', 'type'  => 'id'),
			array('field' => 'EvnStatusCause_id', 'label' => 'Причина изменения статуса события', 'rules' => '', 'type'  => 'id'),
			array('field' => 'cancelType', 'label' => 'Вид отмены', 'rules' => '', 'type'  => 'string'),
		),
		'getEvnDirection' => array(
			array('field' => 'EvnDirection_id', 'label' => 'Идентификатор направления', 'rules' => 'required', 'type'  => 'id'),
		),
		'doLoadView' => array(
			array('field' => 'EvnPrescrList', 'label' => 'Список назначений', 'rules' => 'required', 'type'  => 'json_array', 'assoc' => true),
			array('field' => 'sysnick', 'label' => 'sysnick', 'rules' => 'trim', 'type'  => 'string'),
		),
		'checkCanBeCancelled' => array(
			array('field' => 'EvnDirection_id', 'label' => 'Идентификатор направления', 'rules' => 'required', 'type' => 'id'),
		),
		'execDelDirection' => array(
			array('field' => 'EvnDirection_id', 'label' => 'Идентификатор направления', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'DirFailType_id', 'label' => 'Причина отмены направления', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnStatusCause_id', 'label' => 'Причина смены статуса', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnComment_Comment', 'label' => 'Комментарий отмены направления', 'rules' => '', 'type' => 'string'),
			array('field' => 'pmUser_id', 'label' => 'КИдентификатор пользователя', 'rules' => '', 'type' => 'id'),
			array('field' => 'Lpu_cid', 'label' => 'МО вызова', 'rules' => '', 'type' => 'id' ),
			array('field' => 'MedStaffFact_fid', 'label' => 'Идентификатор сотрудика', 'rules' => '', 'type' => 'id'),
		),
		'EvnLabSampleAndRequest' => array(
			array('field' => 'EvnDirection_id', 'label' => 'Идентификатор направления', 'rules' => 'required', 'type' => 'id'),
		),
		'loadEvnDirectionEditForm' => array(
			array('field' => 'EvnDirection_id','label' => 'Идентификатор направления','rules' => '','type' => 'id')
		),
		'getEvnDirectionForPrint' => array(
			array('field' => 'EvnDirection_id','label' => 'Идентификатор направления','rules' => 'required','type' => 'id')
		),
		'getEvnDirectionFields' => array(
			array('field' => 'EvnDirection_id','label' => 'Идентификатор направления','rules' => 'required','type' => 'id')
		),
		'checkEvnDirectionExists' => [
			['field' => 'Person_id',	'label' => 'Пациент', 'rules' => 'required', 'type' => 'id'],
			['field' => 'MedService_id', 'label' => 'Служба', 'rules' => 'required', 'type' => 'id'],
			['field' => 'UslugaComplex_id', 'label' => 'Услуга', 'rules' => 'required', 'type' => 'id'],
			['field' => 'EvnDirection_pid', 'label' => 'Родительское событие', 'rules' => 'required', 'type' => 'id'],
			['field' => 'EvnPrescr_id', 'label' => 'Назначение', 'rules' => '', 'type' => 'id']
		],
		'getEvnDirectionCount' => array(
			array('field' => 'EvnDirection_pid','label' => 'Идентификатор родительского события направления', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'status','label' => 'Статус', 'rules' => '', 'type' => 'string'),
		),
		'getEvnDirectionNodeList' => array(
			array('field' => 'Person_id', 'label' => 'Идентификатор человека', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'EvnSection_id', 'label' => 'Идентификатор движения', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnVizitPL_id', 'label' => 'Идентификатор посещения', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnVizitDispDop_id', 'label' => 'Идентификатор посещения по доп. диспансеризации', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnVizitPLStom_id', 'label' => 'Идентификатор посещения стоматалогии', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnPS_id', 'label' => 'Идентификатор КВС', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnPL_id', 'label' => 'Идентификатор ТАП', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnPLStom_id', 'label' => 'Идентификатор ТАП стоматалогии', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnPLDispMigrant_id', 'label' => 'Идентификатор талона освидетельствования', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnPLDispDriver_id', 'label' => 'Идентификатор талона освидетельствования', 'rules' => '', 'type' => 'id'),
			array('field' => 'DirType_id', 'label' => 'Тип направления', 'rules' => '', 'type' => 'id'),
			array('field' => 'type', 'label' => 'Тип группировки', 'rules' => '', 'type' => 'int'),
			array('field' => 'from_MZ', 'label' => 'Запуск из АРМ МЗ', 'rules' => '', 'type' => 'int'),
		),
		'getEvnDirectionPersonHistory' => array(
			array('field' => 'Person_id', 'label' => 'Идентификатор человека', 'rules' => '', 'type' => 'id'),
			array('field' => 'person_in', 'label' => 'Список идентификаторов людей', 'rules' => '', 'type' => 'string'),
			array('field' => 'userLpuUnitType_SysNick', 'label' => 'Тип группы отделений', 'rules' => '', 'type' => 'string'),
			array('field' => 'useArchive', 'label' => 'Признак архивных записей', 'rules' => '', 'type' => 'int'),
		),
		'EvnDirectionsForAPI' => array(
			array('field' => 'Evn_pid', 'label' => 'Идентификатор случая-родителя', 'rules' => '', 'type' => 'id'),
			array('field' => 'Person_id', 'label' => 'Идентификатор человека', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnDirection_id', 'label' => 'Идентификатор направления', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnDirection_Num', 'label' => 'Номер направления', 'rules' => '', 'type' => 'int'),
			array('field' => 'EvnDirection_setDate', 'label' => 'Дата направления', 'rules' => '', 'type' => 'date'),
			array('field' => 'DirType_id', 'label' => 'Тип направления', 'rules' => '', 'type' => 'id'),
			array('field' => 'Lpu_id', 'label' => 'МО, куда направили', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnDirection_beg', 'label' => 'Дата и время начала периода изменения направления на исследование', 'rules' => '', 'type' => 'datetime'),
			array('field' => 'EvnDirection_end', 'label' => 'Дата и время окончания периода изменения направления на исследование', 'rules' => '', 'type' => 'datetime')
		),
		'getEvnDirectionTalonCode' => array(
			array('field' => 'Lpu_did', 'label' => 'Идентификатор МО', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'EvnDirection_TalonCode', 'label' => 'Код бронирования', 'rules' => '', 'type' => 'string'),
			array('field' => 'Person_id', 'label' => 'Идентификатор пациента', 'rules' => '', 'type' => 'id')
		),
		'getElectronicTalonDirectionData' => array(
			array('field' => 'EvnDirection_id', 'label' => 'Идентификатор направления', 'rules' => 'required', 'type' => 'id')
		),
		'getTalonCodeByEvnDirectionList' => array(
			array('field' => 'list', 'label' => 'Список направлений', 'rules' => 'required', 'type' => 'string')
		),
		'loadEvnDirectionPanel' => array(
			array(
				'field' => 'EvnDirection_pid',
				'label' => 'Идентификатор родительского события',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'DopDispInfoConsent_id',
				'label' => 'Идентификатор согласия',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'DirType',
				'label' => 'Типы направлений',
				'rules' => '',
				'type' => 'string'
			)
		),
        'includeEvnPrescrInDirection' => array(
            array(
                'field' => 'EvnPrescr_id',
                'label' => 'Назначение',
                'rules' => 'required',
                'type' => 'id'
            ),
            array(
                'field' => 'EvnDirection_id',
                'label' => 'Направление',
                'rules' => 'required',
                'type' => 'id'
            ),
            array(
                'field' => 'UslugaComplex_id',
                'label' => 'Услуга',
                'rules' => 'required',
                'type' => 'id'
            ),
            array(
                'field' => 'checked',
                'label' => 'Заказанные услуги',
                'rules' => '',
                'type' => 'json_array'
            ),
            array(
                'field' => 'order',
                'label' => 'Детали заявки',
                'rules' => '',
                'type' => 'string'
            ),
        ),
	);

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();

		$this->checkAuth();

		$this->db = $this->load->database('lis', true);
		$this->load->model('Lis_EvnDirection_model', 'dbmodel');
	}

	/**
	 * @OA\Post(
	 *     path="/api/EvnDirection",
	 *     tags={"EvnDirection"},
	 *     summary="Сохранение направления",
	 *     @OA\RequestBody(
	 *			required=true,
	 *			@OA\MediaType(
	 *				mediaType="application/x-www-form-urlencoded",
	 *				@OA\Schema(
	 *					@OA\Property(
	 *						property="redirectEvnDirection",
	 *						description="Признак перенаправления",
	 *     					type="integer"
	 * 					),
	 *					@OA\Property(
	 *						property="Diag_id",
	 *						description="Идентификатор диагноза",
	 *     					type="integer"
	 * 					),
	 *					@OA\Property(
	 *						property="TreatmentType_id",
	 *						description="Идентификатор типа лечения",
	 *     					type="integer"
	 * 					),
	 *					@OA\Property(
	 *						property="Lpu_sid",
	 *						description="Идентификатор направившей МО",
	 *     					type="integer"
	 * 					),
	 *					@OA\Property(
	 *						property="EvnDirection_IsNeedOper",
	 *						description="Признак необходимости операционного вмешательства",
	 *     					type="integer"
	 * 					),
	 *					@OA\Property(
	 *						property="DirType_id",
	 *						description="Идентификатор типа направления",
	 *     					type="integer"
	 * 					),
	 *					@OA\Property(
	 *						property="PrehospDirect_id",
	 *						description="Идентификатор справочника 'Кем направлен'",
	 *     					type="integer"
	 * 					),
	 *					@OA\Property(
	 *						property="MedPersonal_Code",
	 *						description="Код врача",
	 *     					type="string"
	 * 					),
	 *					@OA\Property(
	 *						property="MedicalCareFormType_id",
	 *						description="Идентификатор формы оказания медицинской помощи",
	 *     					type="integer"
	 * 					),
	 *					@OA\Property(
	 *						property="StudyTarget_id",
	 *						description="Идентификатор цели исследования",
	 *     					type="integer"
	 * 					),
	 *					@OA\Property(
	 *						property="PayType_id",
	 *						description="Идентификатор вида оплаты",
	 *     					type="integer"
	 * 					),
	 *					@OA\Property(
	 *						property="ConsultingForm_id",
	 *						description="Идентификатор формы оказания консультации",
	 *     					type="integer"
	 * 					),
	 *					@OA\Property(
	 *						property="Lpu_did",
	 *						description="Идентификатор МО, в которую направили",
	 *     					type="integer"
	 * 					),
	 *					@OA\Property(
	 *						property="Org_oid",
	 *						description="Идентификатор организации, в которую направили",
	 *     					type="integer"
	 * 					),
	 *					@OA\Property(
	 *						property="EvnDirection_Descr",
	 *						description="Обоснование",
	 *     					type="string"
	 * 					),
	 *					@OA\Property(
	 *						property="EvnDirection_id",
	 *						description="Идентификатор направления",
	 *     					type="integer"
	 * 					),
	 *					@OA\Property(
	 *						property="EvnDirection_pid",
	 *						description="Идентификатор родительского события",
	 *     					type="integer"
	 * 					),
	 *					@OA\Property(
	 *						property="MedService_id",
	 *						description="Идентификатор службы, в которую направили",
	 *     					type="integer"
	 * 					),
	 *					@OA\Property(
	 *						property="TimetableMedService_id",
	 *						description="Идентификатор бирки расписания службы",
	 *     					type="integer"
	 * 					),
	 *					@OA\Property(
	 *						property="UslugaComplex_did",
	 *						description="Идентификатор услуги",
	 *     					type="integer"
	 * 					),
     *                  @OA\Property(
	 *						property="FSIDI_id",
	 *						description="Идентификатор инструментальной диагностики",
	 *     					type="integer"
	 * 					),
	 *					@OA\Property(
	 *						property="UslugaComplex_id",
	 *						description="Идентификатор услуги",
	 *     					type="integer"
	 * 					),
	 *					@OA\Property(
	 *						property="order",
	 *						description="Заказ услуги в JSON-формате",
	 *     					type="string"
	 * 					),
	 *					@OA\Property(
	 *						property="MedSpec_fid",
	 *						description="Идентификатор специальности врача (федеральный справочник V015)",
	 *     					type="integer"
	 * 					),
	 *					@OA\Property(
	 *						property="RemoteConsultCause_id",
	 *						description="Идентификатор цели консультации",
	 *     					type="integer"
	 * 					),
	 *					@OA\Property(
	 *						property="EvnDirection_IsAuto",
	 *						description="Признак системного направления",
	 *     					type="integer"
	 * 					),
	 *					@OA\Property(
	 *						property="PrescriptionType_Code",
	 *						description="Признак системного направления",
	 *     					type="integer"
	 * 					),
	 *					@OA\Property(
	 *						property="EvnPrescr_id",
	 *						description="Идентификатор назначения",
	 *     					type="integer"
	 * 					),
	 *					@OA\Property(
	 *						property="EvnDirection_Num",
	 *						description="Номер направления",
	 *     					type="string"
	 * 					),
	 *					@OA\Property(
	 *						property="EvnDirection_setDate",
	 *						description="Дата выписки направления",
	 *     					type="string",
	 *     					format="date"
	 * 					),
	 *					@OA\Property(
	 *						property="EvnDirection_desDT",
	 *						description="Желаемая дата направления",
	 *     					type="string",
	 *     					format="date"
	 * 					),
	 *					@OA\Property(
	 *						property="LpuSection_id",
	 *						description="Идентификатор направишего отделение",
	 *     					type="integer"
	 * 					),
	 *					@OA\Property(
	 *						property="LpuSection_did",
	 *						description="Идентификатор отделения, в которое направили",
	 *     					type="integer"
	 * 					),
	 *					@OA\Property(
	 *						property="LpuUnitType_did",
	 *						description="Идентификатор условия оказания медицинской помощи",
	 *     					type="integer"
	 * 					),
	 *					@OA\Property(
	 *						property="LpuSectionProfile_id",
	 *						description="Идентификатор профиля, на который направили",
	 *     					type="integer"
	 * 					),
	 *					@OA\Property(
	 *						property="From_MedStaffFact_id",
	 *						description="Идентификатор рабочего места направившего врача",
	 *     					type="integer"
	 * 					),
	 *					@OA\Property(
	 *						property="MedPersonal_id",
	 *						description="Идентификатор направившего врача",
	 *     					type="integer"
	 * 					),
	 *					@OA\Property(
	 *						property="MedPersonal_zid",
	 *						description="Идентификатор заведующего отделением",
	 *     					type="integer"
	 * 					),
	 *					@OA\Property(
	 *						property="TimetableGraf_id",
	 *						description="Идентификатор бирки поликлиники",
	 *     					type="integer"
	 * 					),
	 *					@OA\Property(
	 *						property="TimetableStac_id",
	 *						description="Идентификатор бирки стационара",
	 *     					type="integer"
	 * 					),
	 *					@OA\Property(
	 *						property="TimetablePar_id",
	 *						description="Идентификатор бирки параклиники",
	 *     					type="integer"
	 * 					),
	 *					@OA\Property(
	 *						property="Person_id",
	 *						description="Идентификатор человека",
	 *     					type="integer"
	 * 					),
	 *					@OA\Property(
	 *						property="PersonEvn_id",
	 *						description="Идентификатор состояния человека",
	 *     					type="integer"
	 * 					),
	 *					@OA\Property(
	 *						property="QueueFailCause_id",
	 *						description="Идентификатор причины отмены направления в очереди",
	 *     					type="integer"
	 * 					),
	 *					@OA\Property(
	 *						property="EvnUsluga_id",
	 *						description="Идентификатор заказа услуги",
	 *     					type="integer"
	 * 					),
	 *					@OA\Property(
	 *						property="ARMType_id",
	 *						description="Идентификатор типа арма, из которого выписывается направление",
	 *     					type="integer"
	 * 					),
	 *					@OA\Property(
	 *						property="toQueue",
	 *						description="Признак необходимости постановки в очереди",
	 *     					type="integer"
	 * 					),
	 *					@OA\Property(
	 *						property="EvnDirection_IsCito",
	 *						description="Cito",
	 *     					type="integer"
	 * 					),
	 *					@OA\Property(
	 *						property="EvnDirection_IsReceive",
	 *						description="Признак создания направления принимающей стороной",
	 *     					type="integer"
	 * 					),
	 *					@OA\Property(
	 *						property="ConsultationForm_id",
	 *						description="Идентификатор формы оказания консультации",
	 *     					type="integer"
	 * 					),
	 *     				required={
	 *     					"DirType_id",
	 *     					"EvnDirection_setDate",
	 *     					"PersonEvn_id"
	 * 					}
	 *				)
	 *			)
	 *		),
	 *     @OA\Response(
	 *     		response="200",
	 *     		description="JSON response",
	 *     		@OA\JsonContent()
	 * 	   )
	 * )
	 */
	function index_post() {
		$data = $this->ProcessInputData('saveEvnDirection', null, true);
		$response = $this->dbmodel->saveEvnDirection($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Delete(
	 *	path="/api/EvnDirection",
	 *	tags={"EvnDirection"},
	 *	summary="Удаление направления",
	 *	@OA\RequestBody(
	 *		required=true,
	 *		@OA\MediaType(
	 *			mediaType="application/x-www-form-urlencoded",
	 *			@OA\Schema(
	 *				@OA\Property(
	 *					property="EvnDirection_id",
	 *					description="Идентификатор направления",
	 *     				type="integer"
	 * 				),
	 *     			required={
	 *     				"EvnDirection_id"
 	 * 				}
	 *			)
	 *		)
	 *	),
	 *	@OA\Response(
	 *	response="200",
	 *	description="JSON response",
	 *		@OA\JsonContent()
	 *	)
	 * )
	 */
	function index_delete() {
		$data = $this->ProcessInputData('deleteEvnDirection', null, true);
		$response = $this->dbmodel->deleteEvnDirection($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Get(
	 *     path="/api/EvnDirection",
	 *     tags={"EvnDirection"},
	 *     summary="Получение данных направления",
	 *     @OA\Parameter(
	 *     		name="EvnDirection_id",
	 *     		in="query",
	 *     		description="Идентификатор направления",
	 *     		required=true,
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   ),
	 *     @OA\Response(
	 *     		response="200",
	 *     		description="JSON response",
	 *     		@OA\JsonContent()
	 * 	   )
	 * )
	 */
	function index_get() {
		$data = $this->ProcessInputData('getEvnDirection', null, true);
		$response = $this->dbmodel->getEvnDirection($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Post(
	 *     path="/api/EvnDirection/cancel",
	 *     tags={"EvnDirection"},
	 *     summary="Отмена направления",
	 *     @OA\RequestBody(
	 *			required=true,
	 *     		@OA\MediaType(
	 *     			mediaType="application/x-www-form-urlencoded",
	 *     			@OA\Schema(
	 *     				@OA\Property(
	 *     					property="EvnDirection_id",
	 *     					description="Идентификатор направления",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="DirFailType_id",
	 *     					description="Идентификатор причины отмены направления",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="EvnStatusCause_id",
	 *     					description="Идентификатор причины изменения статуса события",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="cancelType",
	 *     					description="Вид отмены",
	 *     					type="string"
	 * 					),
	 *     				required={"EvnDirection_id"}
	 * 				)
	 * 			)
	 * 		),
	 *		@OA\Response(
	 *		response="200",
	 *		description="JSON response",
	 *			@OA\JsonContent()
	 * 	   	)
	 * )
	 */
	function cancel_post() {
		$data = $this->ProcessInputData('cancelEvnDirection', null, true);
		$response = $this->dbmodel->cancelEvnDirection($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Post(
	 *     path="/api/EvnDirection/cancelByRecord",
	 *     tags={"EvnDirection"},
	 *     summary="Отмена направления по записи",
	 *     @OA\RequestBody(
	 *			required=true,
	 *     		@OA\MediaType(
	 *     			mediaType="application/x-www-form-urlencoded",
	 *     			@OA\Schema(
	 *     				@OA\Property(
	 *     					property="EvnDirection_id",
	 *     					description="Идентификатор направления",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="TimetableMedService_id",
	 *     					description="Идентификатор бирки расписания службы",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="DirFailType_id",
	 *     					description="Идентификатор причины отмены направления",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="EvnStatusCause_id",
	 *     					description="Идентификатор причины изменения статуса события",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="cancelType",
	 *     					description="Вид отмены",
	 *     					type="string"
	 * 					)
	 * 				)
	 * 			)
	 * 		),
	 *		@OA\Response(
	 *		response="200",
	 *		description="JSON response",
	 *			@OA\JsonContent()
	 * 	   	)
	 * )
	 */
	function cancelByRecord_post() {
		$data = $this->ProcessInputData('cancelEvnDirectionbyRecord', null, true);
		$response = $this->dbmodel->cancelEvnDirectionbyRecord($data);
		if (!empty($response)) {
			$this->response(array(
				'error_code' => 6,
				'error_msg' => $response
			));
		}
		$this->response(array('error_code' => 0));
	}

	/**
	 * @OA\Post(
	 *     path="/api/EvnDirection/loadView",
	 *     tags={"EvnDirection"},
	 *     summary="Получение данных для панели просмотра ЭМК и правой панели формы добавления назначений",
	 *     @OA\RequestBody(
	 *			required=true,
	 *     		@OA\MediaType(
	 *     			mediaType="application/x-www-form-urlencoded",
	 *     			@OA\Schema(
	 *     				@OA\Property(
	 *     					property="EvnPrescrList",
	 *     					description="Список назначений в JSON-формате",
	 *     					type="string",
	 *     					format="json"
	 * 					),
	 *     				@OA\Property(
	 *     					property="sysnick",
	 *     					description="sysnick",
	 *     					type="string"
	 * 					),
	 *     				required={"EvnPrescrList"}
	 * 				)
	 * 			)
	 * 		),
	 *		@OA\Response(
	 *		response="200",
	 *		description="JSON response",
	 *			@OA\JsonContent()
	 * 	   	)
	 * )
	 */
	function loadView_post() {
		$data = $this->ProcessInputData('doLoadView', null, false);
		$response = $this->dbmodel->doLoadView($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Get(
	 *     path="/api/EvnDirection/checkCanBeCancelled",
	 *     tags={"EvnDirection"},
	 *     summary="Проверка возможности удалить направления",
	 *     @OA\Parameter(
	 *     		name="EvnDirection_id",
	 *     		in="query",
	 *     		description="Идентификатор направления",
	 *     		required=true,
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   ),
	 *     @OA\Response(
	 *     		response="200",
	 *     		description="JSON response",
	 *     		@OA\JsonContent()
	 * 	   )
	 * )
	 */
	function checkCanBeCancelled_get() {
		$data = $this->ProcessInputData('checkCanBeCancelled', null, true);
		$response = $this->dbmodel->checkCanBeCancelled($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Get(
	 *     path="/api/EvnDirection/EvnLabSampleAndRequest",
	 *     tags={"EvnDirection"},
	 *     summary="Получение пробы и заявки по направлению",
	 *     @OA\Parameter(
	 *     		name="EvnDirection_id",
	 *     		in="query",
	 *     		description="Идентификатор направления",
	 *     		required=true,
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   ),
	 *     @OA\Response(
	 *     		response="200",
	 *     		description="JSON response",
	 *     		@OA\JsonContent()
	 * 	   )
	 * )
	 */
	function EvnLabSampleAndRequest_get() {
		$data = $this->ProcessInputData('EvnLabSampleAndRequest', null, true);
		$response = $this->dbmodel->getEvnLabSampleAndRequest($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Get(
	 *     path="/api/EvnDirection/loadEDEditForm",
	 *     tags={"EvnDirection"},
	 *     summary="Получение данных по направлению",
	 *     @OA\Parameter(
	 *     		name="EvnDirection_id",
	 *     		in="query",
	 *     		description="Идентификатор направления",
	 *     		required=true,
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   ),
	 *     @OA\Response(
	 *     		response="200",
	 *     		description="JSON response",
	 *     		@OA\JsonContent()
	 * 	   )
	 * )
	 */
	function loadEDEditForm_get() {
		$data = $this->ProcessInputData('loadEvnDirectionEditForm', null, true);
		$response = $this->dbmodel->loadEvnDirectionEditForm($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(['error_code' => 0, 'data' => $response]);
	}

	/**
	 * @OA\Get(
	 *     path="/api/EvnDirection/EvnDirectionForPrint",
	 *     tags={"EvnDirection"},
	 *     summary="Получение данных по направлению",
	 *     @OA\Parameter(
	 *     		name="EvnDirection_id",
	 *     		in="query",
	 *     		description="Идентификатор направления",
	 *     		required=true,
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   ),
	 *     @OA\Response(
	 *     		response="200",
	 *     		description="JSON response",
	 *     		@OA\JsonContent()
	 * 	   )
	 * )
	 */
	function EvnDirectionForPrint_get() {
		$data = $this->ProcessInputData('getEvnDirectionForPrint', null, true);
		$response = $this->dbmodel->getEvnDirectionForPrint($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(['error_code' => 0, 'data' => $response]);
	}

	/**
	 * @OA\Get(
	 *     path="/api/EvnDirection/Fields",
	 *     tags={"EvnDirection"},
	 *     summary="Получение полей направления для печати",
	 *     @OA\Parameter(
	 *     		name="EvnDirection_id",
	 *     		in="query",
	 *     		description="Идентификатор направления",
	 *     		required=true,
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   ),
	 *     @OA\Response(
	 *     		response="200",
	 *     		description="JSON response",
	 *     		@OA\JsonContent()
	 * 	   )
	 * )
	 */
	function Fields_get() {
		$data = $this->ProcessInputData('getEvnDirectionFields', null, true);
		$response = $this->dbmodel->getEvnDirectionFields($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(['error_code' => 0, 'data' => $response]);
	}

	/**
	 * @OA\Get(
	 *     path="/api/EvnDirection/EvnDirectionExists",
	 *     tags={"EvnDirection"},
	 *     summary="Проверка наличия направления в ту же службу",
	 *     @OA\Parameter(
	 *     		name="EvnDirection_pid",
	 *     		in="query",
	 *     		description="Идентификатор родителя направления",
	 *     		required=true,
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   ),
	 *     @OA\Parameter(
	 *     		name="MedService_id",
	 *     		in="query",
	 *     		description="Идентификатор службы",
	 *     		required=true,
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   ),
	 *     @OA\Parameter(
	 *     		name="Person_id",
	 *     		in="query",
	 *     		description="Идентификатор пациента",
	 *     		required=true,
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   ),
	 *     @OA\Parameter(
	 *     		name="UslugaComplex_id",
	 *     		in="query",
	 *     		description="Идентификатор услуги",
	 *     		required=true,
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   ),
	 *     @OA\Response(
	 *     		response="200",
	 *     		description="JSON response",
	 *     		@OA\JsonContent()
	 * 	   )
	 * )
	 */
	function EvnDirectionExists_get() {
		$data = $this->ProcessInputData('checkEvnDirectionExists', null, true);
		$response = $this->dbmodel->checkEvnDirectionExists($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(['error_code' => 0, 'data' => $response]);
	}

	/**
	 * @OA\Get(
	 *     path="/api/EvnDirection/MedServiceFromEvnDirection",
	 *     tags={"EvnDirection"},
	 *     summary="Получение идентификатора мед. службы",
	 *     @OA\Parameter(
	 *     		name="EvnDirection_id",
	 *     		in="query",
	 *     		description="Идентификатор направления",
	 *     		required=true,
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   ),
	 *     @OA\Response(
	 *     		response="200",
	 *     		description="JSON response",
	 *     		@OA\JsonContent()
	 * 	   )
	 * )
	 */
	function MedServiceFromEvnDirection_get() {
		$data = $this->ProcessInputData('getEvnDirection', null, true);
		$response = $this->dbmodel->getMedServiceFromDirection($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(['error_code' => 0, 'data' => $response]);
	}

	/**
	 * @OA\Get(
	 *     path="/api/EvnDirection/Count",
	 *     tags={"EvnDirection"},
	 *     summary="Получение количества направлений",
	 *     @OA\Parameter(
	 *     		name="EvnDirection_pid",
	 *     		in="query",
	 *     		description="Идентификатор родительского события направления",
	 *     		required=true,
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   ),
	 *     @OA\Parameter(
	 *     		name="status",
	 *     		in="query",
	 *     		description="Статус",
	 *     		@OA\Schema(type="string")
	 * 	   ),
	 *     @OA\Response(
	 *     		response="200",
	 *     		description="JSON response",
	 *     		@OA\JsonContent()
	 * 	   )
	 * )
	 */
	function Count_get() {
		$data = $this->ProcessInputData('getEvnDirectionCount', null, true);
		$response = $this->dbmodel->getEvnDirectionCount($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(['error_code' => 0, 'data' => $response]);
	}

	function NodeList_get() {
		$data = $this->ProcessInputData('getEvnDirectionNodeList', null, true);
		$response = $this->dbmodel->getEvnDirectionNodeList($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(['error_code' => 0, 'data' => $response]);
	}

	/**
	 * @OA\Get(
	 *     path="/api/EvnDirection/UslugasDataForAPI",
	 *     tags={"EvnDirection"},
	 *     summary="Данные услуг по направлению для api метода rish api/EvnDirection",
	 *     @OA\Parameter(
	 *     		name="EvnDirection_id",
	 *     		in="query",
	 *     		description="Идентификатор направления",
	 *     		required=true,
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   ),
	 *     @OA\Response(
	 *     		response="200",
	 *     		description="JSON response",
	 *     		@OA\JsonContent()
	 * 	   )
	 * )
	 */
	function UslugasDataForAPI_get() {
		$data = $this->ProcessInputData('getEvnDirection', null, true);
		$response = $this->dbmodel->getUslugasDataForAPI($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(['error_code' => 0, 'data' => $response]);
	}

	/**
	 * @OA\Get(
	 *     path="/api/EvnDirection/EvnDirectionsForAPI",
	 *     tags={"EvnDirection"},
	 *     summary="Направления для api метода rish api/EvnDirection",
	 *     todo параметры
	 *     @OA\Response(
	 *     		response="200",
	 *     		description="JSON response",
	 *     		@OA\JsonContent()
	 * 	   )
	 * )
	 */
	function EvnDirectionsForAPI_get() {
		$data = $this->ProcessInputData('EvnDirectionsForAPI', null, true);
		$response = $this->dbmodel->getEvnDirectionsForAPI($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(['error_code' => 0, 'data' => $response]);
	}

	/**
	 * @OA\Get(
	 *     	path="/api/EvnDirection/PersonHistory",
	 *  	tags={"EvnDirection"},
	 *	    summary="История болезни для новой ЭМК",
	 *     	@OA\Parameter(
	 *     		name="Person_id",
	 *     		in="query",
	 *     		description="Идентификатор человека",
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="person_in",
	 *     		in="query",
	 *     		description="Список идентификаторов людей",
	 *     		@OA\Schema(type="string")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="userLpuUnitType_SysNick",
	 *     		in="query",
	 *     		description="Тип группы отделений",
	 *     		@OA\Schema(type="string")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="useArchive",
	 *     		in="query",
	 *     		description="Признак архивных записей",
	 *     		@OA\Schema(type="integer")
	 * 	   	),
	 *		@OA\Response(
	 *			response="200",
	 *			description="JSON response",
	 *			@OA\JsonContent()
	 * 	   	)
	 * )
	 */
	function PersonHistory_get() {
		$data = $this->ProcessInputData('getEvnDirectionPersonHistory', null, true);
		$response = $this->dbmodel->getEvnDirectionPersonHistory($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(['error_code' => 0, 'data' => $response]);
	}

	/**
	 * Получить коды бронирования для ЭО
	 */
	function getEvnDirectionTalonCode_get() {

		$data = $this->ProcessInputData('getEvnDirectionTalonCode');
		$response = $this->dbmodel->getEvnDirectionTalonCode($data);
		$this->response(['error_code' => 0, 'data' => $response]);
	}

	/**
	 * Получить информацию по направлению для связанного талона ЭО
	 */
	function getElectronicTalonDirectionData_get(){
		$data = $this->ProcessInputData('getElectronicTalonDirectionData');
		$response = $this->dbmodel->getElectronicTalonDirectionData($data);
		$this->response(['error_code' => 0, 'data' => $response]);
	}

	/**
	 * Получить информацию по направлению для связанного талона ЭО
	 */
	function getTalonCodeByEvnDirectionList_get(){
		$data = $this->ProcessInputData('getTalonCodeByEvnDirectionList');
		$response = $this->dbmodel->getTalonCodeByEvnDirectionList($data);
		$this->response(['error_code' => 0, 'data' => $response]);
	}

	/**
	 * @OA\Get(
	 *     path="/api/EvnDirection/loadEvnDirectionPanel",
	 *     tags={"EvnDirection"},
	 *     summary="Получение списка направлений для панели направлений в ЭМК",
	 *     @OA\Parameter(
	 *     		name="EvnDirection_pid",
	 *     		in="query",
	 *     		description="Идентификатор родителя направления",
	 *     		required=true,
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   ),
	 *     @OA\Parameter(
	 *     		name="DopDispInfoConsent_id",
	 *     		in="query",
	 *     		description="Идентификатор согласия",
	 *     		required=false,
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   ),
	 *     @OA\Parameter(
	 *     		name="DirType",
	 *     		in="query",
	 *     		description="Типы направлений",
	 *     		required=false,
	 *     		@OA\Schema(type="string")
	 * 	   ),
	 *     @OA\Response(
	 *     		response="200",
	 *     		description="JSON response",
	 *     		@OA\JsonContent()
	 * 	   )
	 * )
	 */
	function loadEvnDirectionPanel_get(){
		$data = $this->ProcessInputData('loadEvnDirectionPanel');
		$response = $this->dbmodel->loadEvnDirectionPanel($data);
		$this->response(['error_code' => 0, 'data' => $response]);
	}

    /**
     * @OA\Get(
     *     path="/api/EvnDirection/includeEvnPrescrInDirection",
     *     tags={"EvnDirection"},
     *     summary="Проверка наличия направления в ту же службу",
     *     @OA\Parameter(
     *     		name="EvnPrescr_id",
     *     		in="query",
     *     		description="Идентификатор назначения",
     *     		required=true,
     *     		@OA\Schema(type="integer", format="int64")
     * 	   ),
     *     @OA\Parameter(
     *     		name="EvnDirection_id",
     *     		in="query",
     *     		description="Идентификатор направления",
     *     		required=true,
     *     		@OA\Schema(type="integer", format="int64")
     * 	   ),
     *     @OA\Parameter(
     *     		name="UslugaComplex_id",
     *     		in="query",
     *     		description="Идентификатор услуги",
     *     		required=true,
     *     		@OA\Schema(type="integer", format="int64")
     * 	   ),
     *     @OA\Parameter(
     *     		name="checked",
     *     		in="query",
     *     		description="Заказанные услуги",
     *     		required=false,
     *     		@OA\Schema(type="json_array")
     * 	   ),
     *     @OA\Parameter(
     *     		name="order",
     *     		in="query",
     *     		description="Детали заявки",
     *     		required=true,
     *     		@OA\Schema(type="json string")
     * 	   ),
     *     @OA\Response(
     *     		response="200",
     *     		description="JSON response",
     *     		@OA\JsonContent()
     * 	   )
     * )
     */
    function includeEvnPrescrInDirection_get() {
        $data = $this->ProcessInputData('includeEvnPrescrInDirection', null, true);
        $response = $this->dbmodel->includeEvnPrescrInDirection($data);
        if (!is_array($response)) {
            $this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
        }
        $this->response(['error_code' => 0, 'data' => $response]);
    }
	
}
