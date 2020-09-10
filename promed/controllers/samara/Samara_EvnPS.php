<?php defined('BASEPATH') or die ('No direct script access allowed');


require_once(APPPATH.'controllers/EvnPS.php');

class Samara_EvnPS extends EvnPS {

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
		
		$this->inputRules['printEvnPSSamara'] = array(
            array(
				'field' => 'format',
				'label' => 'Формат печати',
				'rules' => '',
				'type' => 'string'
			),			
            array(
				'field' => 'EvnPS_id',
				'label' => 'Идентификатор КВС',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Parent_Code',
				'label' => 'Код вызвавшей кнопки печати',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'KVS_Type',
				'label' => 'Тип печати КВС',
				'rules' => '',
				'type'  => 'string'
			),
			array(
				'field' => 'EvnSection_id',
				'label' => 'ID движения',
				'rules' => '',
				'type'  => 'id'
			)
		);
        $this->inputRules['loadWorkPlacePriem'] = array(
            array(
                'field' => 'date',
                'label' => 'Дата приема',
                'rules' => '',
                'type' => 'date'
            ),
            array(
                'field' => 'LpuSection_id',
                'label' => 'Приемное отделение',
                'rules' => 'required',
                'type' => 'id'
            ),
            array(
                'field' => 'Person_SurName',
                'label' => 'Фамилия',
                'rules' => 'trim',
                'type' => 'string'
            ),
            array(
                'field' => 'Person_FirName',
                'label' => 'Имя',
                'rules' => '',
                'type' => 'string'
            ),
            array(
                'field' => 'Person_SecName',
                'label' => 'Отчество',
                'rules' => '',
                'type' => 'string'
            ),
            array(
                'field' => 'Person_BirthDay',
                'label' => 'Дата рождения',
                'rules' => '',
                'type' => 'date'
            ),
            array(
                'field' => 'EvnQueueShow_id',
                'label' => 'Очередь',
                'rules' => '',
                'type' => 'int'
            ),
            array(
                'field' => 'EvnDirectionShow_id',
                'label' => 'План госпитализаций',
                'rules' => '',
                'type' => 'int'
            ),
            array(
                'field' => 'PrehospStatus_id',
                'label' => 'Статус',
                'rules' => '',
                'type' => 'id'
            ),
        );
		$this->inputRules['getHospCount'] = array(
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор человека',
				'rules' => '',
				'type'  => 'id'
			)
		);        
        
        
        ////
        $this->inputRules['saveEvnPS'] =  array(
            // samara
            array(
				'field' => 'HospType_id',
				'label' => 'Цель госпитализации',
				'rules' => '',
				'type' => 'id'
			),				
			array(
				'field' => 'DeputyKind_id',
				'label' => 'Статус',
				'rules' => '',
				'type' => 'id'
			),		
			array(
				'field' => 'EvnPS_DeputyFIO',
				'label' => 'ФИО',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'EvnPS_DeputyContact',
				'label' => 'Контактные данные',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'MedPersonal_did',
				'label' => 'Идентификатор направившего врача', 
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EntranceModeType_id',
				'label' => 'Вид транспортировки',		
				'rules' => '',
				'type' => 'string'
			),
			array(										
				'field' => 'EvnPS_DrugActions',
				'label' => 'Побочное действие лекарств',
				'rules' => '',
				'type' => 'string'
			),
            //
			array(
				'field' => 'from',
				'label' => 'from',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'PrehospStatus_id',
				'label' => 'Статус',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'TimetableStac_id', // Для АРМ приемного
				'label' => 'Бирка',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'LpuSection_eid',
				'label' => 'Отделение ("Госпитализирован в")', // Для АРМ приемного
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'LpuSection_id',
				'label' => 'Отделение',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Diag_aid',
				'label' => 'Основной диагноз (паталого-анатомический)',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Diag_did',
				'label' => 'Основной диагноз направившего учреждения',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'DiagSetPhase_did',
				'label' => 'Фаза/стадия',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnPS_PhaseDescr_did',
				'label' => 'Расшифровка',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Diag_pid',
				'label' => 'Основной диагноз приемного отделения',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'DiagSetPhase_pid',
				'label' => 'Фаза/стадия',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnPS_PhaseDescr_pid',
				'label' => 'Расшифровка',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'EvnQueue_id',
				'label' => 'Идентификатор очереди',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnDirection_id',
				'label' => 'Идентификатор электронного направления',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnDirection_Num',
				'label' => 'Номер направления',
				'rules' => 'max_length[16]',
				'type' => 'string'
			),
			array(
				'field' => 'EvnDirection_setDate',
				'label' => 'Дата направления',
				'rules' => 'trim',
				'type' => 'date'
			),
			array(
				'field' => 'EvnPS_CodeConv',
				'label' => 'Код',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'EvnPS_disDate',
				'label' => 'Дата закрытия КВС',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'EvnPS_disTime',
				'label' => 'Время закрытия КВС',
				'rules' => '',
				'type' => 'time'
			),
			array(
				'field' => 'EvnPS_HospCount',
				'label' => 'Количество госпитализаций',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'EvnPS_id',
				'label' => 'Идентификатор карты выбывшего из стационара',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnPS_IsCont',
				'label' => 'Переведен',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'EvnPS_IsDiagMismatch',
				'label' => 'Несовпадение диагноза',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnPS_IsImperHosp',
				'label' => 'Несвоевременность госпитализации',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnPS_IsShortVolume',
				'label' => 'Недостаточный объем клинико-диагностического обследования',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnPS_IsNeglectedCase',
				'label' => 'Случай запущен',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'EvnPS_IsPLAmbulance',
				'label' => 'Талон передан на ССМП',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnPS_IsPrehospAcceptRefuse',
				'label' => 'Отказ в подтверждении госпитализации',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnPS_IsTransfCall',
				'label' => 'Передан активный вызов',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnPS_IsUnlaw',
				'label' => 'Противоправная',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnPS_IsUnport',
				'label' => 'Нетранспортабельность',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnPS_IsWaif',
				'label' => 'Беспризорный',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnPS_IsWithoutDirection',
				'label' => 'Без электронного направления',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnPS_IsWrongCure',
				'label' => 'Неправильная тактика лечения',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnPS_NumCard',
				'label' => 'Номер карты',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'EvnPS_NumConv',
				'label' => 'Номер наряда',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'EvnPS_PrehospAcceptRefuseDT',
				'label' => 'Дата отказа в подтверждении госпитализации',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'EvnPS_PrehospWaifRefuseDT',
				'label' => 'Дата отказа приёма',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'EvnPS_setDate',
				'label' => 'Дата поступления',
				'rules' => 'required',
				'type' => 'date'
			),
			array(
				'field' => 'EvnPS_setTime',
				'label' => 'Время поступления',
				'rules' => '',
				'type' => 'time'
			),
			array(
				'field' => 'EvnPS_OutcomeDate',
				'label' => 'Дата исхода из приемного отделения',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'EvnPS_OutcomeTime',
				'label' => 'Время исхода из приемного отделения',
				'rules' => '',
				'type' => 'time'
			),
			array(
				'field' => 'EvnPS_TimeDesease',
				'label' => 'Время с начала заболевания',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'Okei_id',
				'label' => 'Еденица измерения (времени с начала заболевания)',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Lpu_did',
				'label' => 'ЛПУ ("Госпитализация")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'LpuSection_did',
				'label' => 'Отделение ("Госпитализация")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'LpuSection_pid',
				'label' => 'Приемное отделение ("Приемное")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'MedPersonal_pid',
				'label' => 'Врач приемного отделения ("Приемное")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Org_did',
				'label' => 'Организация ("Госпитализация")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'OrgMilitary_did',
				'label' => 'Военкомат ("Госпитализация")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PayType_id',
				'label' => 'Вид оплаты',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'PersonEvn_id',
				'label' => 'Идентификатор состояния пациента',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'PrehospArrive_id',
				'label' => 'Кем доставлен',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PrehospDirect_id',
				'label' => 'Кем направлен',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PrehospToxic_id',
				'label' => 'Состояние опьянения',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PrehospTrauma_id',
				'label' => 'Травма',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PrehospType_id',
				'label' => 'Тип госпитализации',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'PrehospWaifRefuseCause_id',
				'label' => 'Отказ',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'ResultClass_id',
				'label' => 'Исход',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'ResultDeseaseType_id',
				'label' => 'Результат обращения',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PrehospWaifArrive_id',
				'label' => 'PrehospWaifArrive_id',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PrehospWaifReason_id',
				'label' => 'PrehospWaifReason_id',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Server_id',
				'label' => 'Идентификатор сервера',
				'rules' => 'required',
				'type' => 'int'
			),
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор пациента',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'addEvnSection',
				'label' => 'Флаг добавления движения',
				'rules' => '',
				'type'	=> 'string'
			),
			array(
				'field' => 'MedPersonal_id',
				'label' => 'Идентификатор врача', // для добавления из ЭМК
				'rules' => '',
				'type' => 'id'
			)
			/*,array(
				'field' => 'TimeDeseaseType_id',
				'label' => 'Идентификатор времени заболевания',
				'rules' => '',
				'type' => 'id'
			)*/
		);
        
		$this->load->database();
        $this->load->model('Samara_EvnPS_model', 'samara_dbmodel');
        $this->load->helper('Text');
	}    

	/**
	*  Печать карты выбывшего из стационара
	*  Входящие данные: $_GET['EvnPS_id']
	*  На выходе: форма для печати карты выбывшего из стационара
	*  Используется: форма редактирования карты выбывшего из стационара
	*                форма потокового ввода КВС
	*Значение параметра Parent_Code означают, что печать вызвана из следующих форм:
	*	1	Форма «Карта выбывшего из стационара» по кнопке Печать кнопок управления формой
	*	2	Форма «Карта выбывшего из стационара: Поиск» по кнопке Печать панели управления списком КВС
	*	3	Форма «Карта выбывшего из стационара: Поточный ввод» по кнопке Печать панели управления списком КВС
	*	4	ЭМК. Панель просмотра. Случай стационарного лечения. Кнопка Печать КВС.
	*	5	Форма «Карта выбывшего из стационара», в панели управления списком Движений в форме «Карта выбывшего из стационара»
	*	6	Форма «Электронная медицинская карта (ЭМК)». В панели просмотра данных о Движении пациента в приемном отделении
	*	7	Форма «Электронная медицинская карта (ЭМК)». В панели просмотра данных о Движении пациента в профильном отделении
	*
	*Значение параметра KVS_Type означает следующее:
	*	"AB" - список составлен по КВС
	*	"VG" - список составлен по движениям
	*	"V"  - выбрано первое по хронологии движение
	*	"G"  - выбрано НЕ первое по хронологии движение
	*/
	function printEvnPSSamara() {
		$this->load->library('parser');

		$data = $this->ProcessInputData('printEvnPSSamara', true);
		if ( $data === false ) { return false; }

		$response = $this->samara_dbmodel->getEvnPSFields($data);

        // Sannikov
        $response_child = array();
        $response_death_child = array();
        $response_diag_osl = array();
        $response_agg = array();
        $response_diag_sect = array();
        $response_oper = array();
        $response_day_stac = array();
        $response_mother_data = array();
        $response_height = array();
        $response_weight = array();
        $widht_height = array();
        $response_eslast_diag = array();
        // END OF Sannikov
               
		if ( !is_array($response) || count($response) == 0 ) {
			echo 'Ошибка при получении данных по КВС';
			return true;
		}

		$evn_diag_ps_admit_data = array();
		$evn_diag_ps_anatom_data = array();
		$evn_diag_ps_hosp_data = array();
		$evn_diag_ps_section_data = array();
		$evn_section_data = array();
		$evn_stick_data = array();
		$evn_usluga_oper_data = array();

		if ( strlen($response[0]['DiagP_Name']) > 0 ) {
			$evn_diag_ps_admit_data[] = array('DiagSetClass_Name' => 'Основной', 'Diag_Code' => $response[0]['DiagP_Code'], 'Diag_Name' => $response[0]['DiagP_Name']);
		}

		if ( strlen($response[0]['DiagA_Name']) > 0 ) {
			$evn_diag_ps_anatom_data[] = array('DiagSetClass_Name' => 'Основной', 'Diag_Code' => $response[0]['DiagA_Code'], 'Diag_Name' => $response[0]['DiagA_Name']);
		}

		if ( strlen($response[0]['DiagH_Name']) > 0 ) {
			$evn_diag_ps_hosp_data[] = array('DiagSetClass_Name' => 'Основной', 'Diag_Code' => $response[0]['DiagH_Code'], 'Diag_Name' => $response[0]['DiagH_Name']);
		}

		$sectionsDays= $this->samara_dbmodel->getEvnSectionsDays($data);
		
		if ($sectionsDays){
			$sectionsDays = $sectionsDays[0]['SectionsDays'];
		} else {
			$sectionsDays = '&nbsp;';
		}

		$response_temp = $this->samara_dbmodel->getEvnDiagPSList($data);
		if ( is_array($response_temp) ) {
			for ( $i = 0; $i < count($response_temp); $i++ ) {
				switch ( $response_temp[$i]['DiagSetType_Code'] ) {
					case 1:
						$evn_diag_ps_hosp_data[] = array(
							'DiagSetClass_Name' => $response_temp[$i]['DiagSetClass_Name'],
							'Diag_Code' => $response_temp[$i]['Diag_Code'],
							'Diag_Name' => $response_temp[$i]['Diag_Name']
						);
					break;

					case 2:
						$evn_diag_ps_admit_data[] = array(
							'DiagSetClass_Name' => $response_temp[$i]['DiagSetClass_Name'],
							'Diag_Code' => $response_temp[$i]['Diag_Code'],
							'Diag_Name' => $response_temp[$i]['Diag_Name']
						);
					break;

					case 3:
						$evn_diag_ps_section_data[] = array(
							'EvnDiagPS_pid' => $response_temp[$i]['EvnDiagPS_pid'],
							'DiagSetClass_Name' => $response_temp[$i]['DiagSetClass_Name'],
							'Diag_Code' => $response_temp[$i]['Diag_Code'],
							'Diag_Name' => $response_temp[$i]['Diag_Name']
						);
					break;

					case 5:
						$evn_diag_ps_anatom_data[] = array(
							'DiagSetClass_Name' => $response_temp[$i]['DiagSetClass_Name'],
							'Diag_Code' => $response_temp[$i]['Diag_Code'],
							'Diag_Name' => $response_temp[$i]['Diag_Name']
						);
					break;
				}
			}
		}

		//-------------------------Печать разных вариантов КВС----------------------------------
		$KVS_Type = $data['KVS_Type'];
		//Выбираем вьюху
		switch ($data['format'])
		{
			case 'A4': $template = 'samara_evn_ps_template_list_a4';
			break;
			
			case 'cardio': $template = 'samara_evn_ps_template_list_cardio';
			break;
			
			case 'ApplyMV': $template = 'samara_evn_ps_template_list_MV';
			break;
			
			case 'ApplyOPD': $template = 'samara_evn_ps_template_list_OPD';
			break;
			
			case 'ambCard': $template = 'samara_evn_ps_ambulance_card';
			break;
			
			case 'notice': $template = 'samara_evn_ps_template_list_notice';
			break;
			
			case 'serviceContract': $template = 'samara_evn_ps_template_list_service_contract';
			break;
			
			case 'payServiceAmbContract': $template = 'samara_evn_ps_template_list_amb_service_contract';
			break;
			
			case 'payServiceStacContract': $template = 'samara_evn_ps_template_list_stac_service_contract';
			break;
			
			case 'agreement': $template = 'samara_evn_ps_template_list_agreement';
			break;
			
			case 'amb_pacient_talon': $template = 'samara_evn_ps_template_list_amb_pacient_talon';
			break;

            // Sannikov
            case 'abortMedCard':
                $template = 'samara_evn_ps_template_list_abort_med_card';
                //$this->getBirthAbortData($response, $data);
                $response_child = array();
                $response_death_child = array();
                $response_diag_osl = array();
                $response_agg = array();
                if (strlen($response[0]['LpuSection_id']) > 0) {  
                    //print("<pre>");
                    $response_child = $this->samara_dbmodel->getChildsData($response[0]['LpuSection_id']); 
                    $response_death_child = $this->samara_dbmodel->getDeathChildsData($response[0]['LpuSection_id'], $data['Lpu_id']);
                    $response_diag_sect = $this->samara_dbmodel->getDiagSectList($response[0]['LpuSection_id'], $response[0]['Person_id']);
                    $response_agg = $this->samara_dbmodel->getAggList($response[0]['LpuSection_id'], $data['Lpu_id'], 'birth');
                    $response_oper = $this->samara_dbmodel->getOperationList($response[0]['LpuSection_id'], $data['Lpu_id']);
                    $response_eslast_diag = $this->samara_dbmodel->getLastSectionDiag($response[0]['LpuSection_id'], $data['Lpu_id']);
                    //print("</pre>");
                }
            break;

            case 'birthHistory': 
                $template = 'samara_evn_ps_template_list_birth_history';

                $response_child = array();
                $response_death_child = array();
                $response_diag_osl = array();
                $response_agg = array();
                if (strlen($response[0]['LpuSection_id']) > 0) {  // TODO: что делать, если else??
                    //print("<pre>");
                    $response_child = $this->samara_dbmodel->getChildsData($response[0]['LpuSection_id']); // Информация по детям Sannikov
                    $response_death_child = $this->samara_dbmodel->getDeathChildsData($response[0]['LpuSection_id'], $data['Lpu_id']);
                    $response_diag_sect = $this->samara_dbmodel->getDiagSectList($response[0]['LpuSection_id'], $response[0]['Person_id']); 
                    $response_agg = $this->samara_dbmodel->getAggList($response[0]['LpuSection_id'], $data['Lpu_id'], 'birth');
                    $response_oper = $this->samara_dbmodel->getOperationList($response[0]['LpuSection_id'], $data['Lpu_id']);                    
                    $response_eslast_diag = $this->samara_dbmodel->getLastSectionDiag($response[0]['LpuSection_id'], $data['Lpu_id']);
                    //print("</pre>");
                }
            break;
            
            case 'newbornHistory': 
                $template = 'samara_evn_ps_template_list_newborn_history';
                $response_child = array();
                $response_death_child = array();
                $response_diag_osl = array();
                $response_agg = array();
                if (strlen($response[0]['LpuSection_id']) > 0) {  // TODO: что делать, если else??
                    //print("<pre>");
                    $response_mother_data = $this->samara_dbmodel->getMotherData($response[0]['LpuSection_id']);
                    if ((isset($response_mother_data)) && (is_array($response_mother_data))) {
                        if ((isset($response_mother_data[0])) && (strlen($response_mother_data[0]['Mother_EvnPS_rid']) > 0)) {
                            $response_diag_sect = $this->samara_dbmodel->getDiagSectList($response_mother_data[0]['Mother_EvnPS_rid']);
                            $response_oper = $this->samara_dbmodel->getOperationList($response_mother_data[0]['Mother_EvnPS_rid'], $data['Lpu_id']);
                        } else {
                            echo 'Ошибка при получении данных по КВС матери';
                            return true;
                        }
                    } else {
                        echo 'Ошибка при получении данных по КВС матери';
                        return true;
                    }

                    $response_height = $this->samara_dbmodel->getHeight($response[0]['Person_id']);
                    $response_weight = $this->samara_dbmodel->getWeight($response[0]['Person_id']);
                    if ((isset($response_height)) && (is_array($response_height))) {
                        foreach ($response_height as $value) {
                            $widht_height[intval($value['HeightMeasureType_id'])] = array();
                            $widht_height[intval($value['HeightMeasureType_id'])]['Height'] = $value['PersonHeight_Height'];
                        }
                    } 
                    if ((isset($response_height)) && (is_array($response_height))) {
                        foreach ($response_weight as $value_1) {
                            $widht_height[intval($value_1['WeightMeasureType_id'])]['Weight'] = $value_1['PersonWeight_Weight'];
                        }
                    }
                    
                    $response_child = $this->samara_dbmodel->getChildsData($response[0]['LpuSection_id']); // Информация по детям Sannikov
                    $response_death_child = $this->samara_dbmodel->getDeathChildsData($response[0]['LpuSection_id'], $data['Lpu_id']);
                    $response_agg = $this->samara_dbmodel->getAggList($response[0]['LpuSection_id'], $data['Lpu_id'], 'birth');
                    $response_eslast_diag = $this->samara_dbmodel->getLastSectionDiag($response[0]['LpuSection_id'], $data['Lpu_id']);
                    //print("</pre>");
                }
            break;
            
            case 'form_003_2':
                // Дневной стационар
                $template = 'samara_evn_ps_template_list_day_hosp';
                if (strlen($response[0]['LpuSection_id']) > 0) {
                    //print("<pre>");
                    $response_day_stac = $this->samara_dbmodel->getDayStac($response[0]['LpuSection_id'], $data['Lpu_id']);
                    $response_oper = $this->samara_dbmodel->getOperationList($response[0]['LpuSection_id'], $data['Lpu_id']);
                    //print("</pre>");
                }
            break;    
            // end Sannikov

			default: $template = 'samara_evn_ps_template_list_a5'; 
			break;
		}
		
		$response_temp = $this->samara_dbmodel->getEvnSectionData($data);

		if (($data['Parent_Code'] == '1')||($data['Parent_Code']=='3')||($data['Parent_Code']=='4'))
		{

			if((is_array($response_temp))&&(count($response_temp)==0)) //В истории болезни нет ни одного движения
			{
				$template = 'evn_ps_template_list_a4_first';
			}
		}

		if ($data['Parent_Code'] == '6')
		{
			$template = 'evn_ps_template_list_a4_first';
		}

		if (($data['Parent_Code'] == '2')||($data['Parent_Code']=='5')||($data['Parent_Code']=='7'))
		{

			if($data['KVS_Type'] == 'AB') //Здесь означает, что список был составлен по КВС
			{
				if((is_array($response_temp))&&(count($response_temp)==0)) //В истории болезни нет ни одного движения
				{
					$template = 'evn_ps_template_list_a4_first';
				}
			}
			if($data['KVS_Type'] == 'VG') //Здесь означает, что список был составлен по движениям
			{
				//проверка на то, какое по хронологии движение было выбрано. Тут уже поможет только SQL-запрос
				$response_section = $this->samara_dbmodel->checkEvnSection($data);

				if ($response_section == '0')
				{
					//Не первое по хронологии;
					$KVS_Type = 'G';
				}
				else
				{
					//Первое по хронологии;
					$KVS_Type = 'V';
				}
				//echo json_return_errors($KVS_Type);
				//return false;
			}
		}

		//-----------------------------------------------------------------------------------------

		if ( is_array($response_temp) ) {
			$evn_section_data = $response_temp;

			if ( count($evn_diag_ps_hosp_data) < 3 ) {
				for ( $j = count($evn_diag_ps_hosp_data); $j < 3; $j++ ) {
					$evn_diag_ps_hosp_data[] = array(
						'DiagSetClass_Name' => '&nbsp;<br />&nbsp;',
						'Diag_Code' => '&nbsp;',
						'Diag_Name' => '&nbsp;'
					);
				}
			}

			if ( count($evn_diag_ps_admit_data) < 3 ) {
				for ( $j = count($evn_diag_ps_admit_data); $j < 3; $j++ ) {
					$evn_diag_ps_admit_data[] = array(
						'DiagSetClass_Name' => '&nbsp;<br />&nbsp;',
						'Diag_Code' => '&nbsp;',
						'Diag_Name' => '&nbsp;'
					);
				}
			}

			if ( count($evn_diag_ps_anatom_data) < 2 ) {
				for ( $j = count($evn_diag_ps_anatom_data); $j < 2; $j++ ) {
					$evn_diag_ps_anatom_data[] = array(
						'DiagSetClass_Name' => '&nbsp;<br />&nbsp;',
						'Diag_Code' => '&nbsp;',
						'Diag_Name' => '&nbsp;'
					);
				}
			}

			for ( $i = 0; $i < (count($evn_section_data) < 2 ? 2 : count($evn_section_data)); $i++ ) {
				if ( $i >= count($evn_section_data) ) {
					$evn_section_data[$i] = array(
						'EvnSection_id' => '&nbsp;',
						'EvnSection_setDT' => '&nbsp;<br />&nbsp;',
						'LpuSection_Name' => '&nbsp;',
						'PayType_Name' => '&nbsp;',
						'MedPersonal_Code' => '&nbsp;',
						'EvnSection_disDT' => '&nbsp;',
						'EvnSectionDiagSetClassOsn_Name' => '&nbsp;',
						'EvnSectionDiagOsn_Name' => '&nbsp;',
						'EvnSectionDiagOsn_Code' => '&nbsp;',
						'EvnSectionMesOsn_Code' => '&nbsp;',
                        'LpuSectionBedProfile_Name' => '&nbsp;',
					);
				}

				$evn_section_data[$i]['EvnSectionDiagData'] = array();

				if ( $i < count($evn_section_data) ) {
					foreach ( $evn_diag_ps_section_data as $key => $value ) {
						if ( $value['EvnDiagPS_pid'] == $evn_section_data[$i]['EvnSection_id'] ) {
							$evn_section_data[$i]['EvnSectionDiagData'][] = array(
								'EvnSectionDiagSetClass_Name' => $value['DiagSetClass_Name'],
								'EvnSectionDiag_Code' => $value['Diag_Code'],
								'EvnSectionDiag_Name' => $value['Diag_Name'],
								'EvnSectionMes_Code' => '&nbsp;'
							);
						}
					}
				}

				if ( count($evn_section_data[$i]['EvnSectionDiagData']) < 2 ) {
					for ( $j = count($evn_section_data[$i]['EvnSectionDiagData']); $j < 2; $j++ ) {
						$evn_section_data[$i]['EvnSectionDiagData'][$j] = array(
							'EvnSectionDiagSetClass_Name' => '&nbsp;<br />&nbsp;',
							'EvnSectionDiag_Code' => '&nbsp;',
							'EvnSectionDiag_Name' => '&nbsp;',
							'EvnSectionMes_Code' => '&nbsp;'
						);
					}
				}
			}
		}

		$response_temp = $this->samara_dbmodel->getEvnStickData($data);
		if ( is_array($response_temp) ) {
			$evn_stick_data = $response_temp;

			if ( count($evn_stick_data) < 2 ) {
				for ( $i = count($evn_stick_data); $i < 2; $i++ ) {
					$evn_stick_data[$i] = array(
						'EvnStick_begDate' => '&nbsp;',
						'EvnStick_endDate' => '&nbsp;',
						'StickOrder_Name' => '&nbsp;',
						'EvnStick_Ser' => '&nbsp;',
						'EvnStick_Num' => '&nbsp;',
						'StickCause_Name' => '&nbsp;',
						'Sex_Name' => '&nbsp;',
						'EvnStick_Age' => '&nbsp;'
					);
				}
			}
		}

		$response_temp = $this->samara_dbmodel->getEvnUslugaOperData($data);
		if ( is_array($response_temp) ) {
			for ( $i = 0; $i < count($response_temp); $i++ ) {
				$evn_usluga_oper_data[] = array(
					'EvnUslugaOper_setDT' => $response_temp[$i]['EvnUslugaOper_setDT'],
					'LpuSection_Code' => $response_temp[$i]['LpuSection_Code'],
					'MedPersonal_Code' => $response_temp[$i]['MedPersonal_Code'],
					'PayType_Name' => $response_temp[$i]['PayType_Name'],
					'Usluga_Name' => $response_temp[$i]['Usluga_Name'],
					'Usluga_Code' => $response_temp[$i]['Usluga_Code'], // Petrov
					'AggType_Name' => $response_temp[$i]['AggType_Name'],
					'AggType_Code' => $response_temp[$i]['AggType_Code'],                    
					'AnesthesiaClass_Name' => $response_temp[$i]['AnesthesiaClass_Name'],
					'EvnUslugaOper_IsEndoskop' => $response_temp[$i]['EvnUslugaOper_IsEndoskop'] == 1 ? 'X' : '&nbsp;',
					'EvnUslugaOper_IsLazer' => $response_temp[$i]['EvnUslugaOper_IsLazer'] == 1 ? 'X' : '&nbsp;',
					'EvnUslugaOper_IsKriogen' => $response_temp[$i]['EvnUslugaOper_IsKriogen'] == 1 ? 'X' : '&nbsp;'
				);
			}

			// https://redmine.swan.perm.ru/issues/6484
			// savage: Добавляем пустые строки в таблицу с хирургическими операциями, если количество операций меньше двух
			for ( $j = $i; $j < 3; $j++ ) {
				$evn_usluga_oper_data[] = array(
					'EvnUslugaOper_setDT' => '&nbsp;<br />&nbsp;',
					'LpuSection_Code' => '&nbsp;<br />&nbsp;',
					'MedPersonal_Code' => '&nbsp;<br />&nbsp;',
					'PayType_Name' => '&nbsp;<br />&nbsp;',
					'Usluga_Name' => '&nbsp;<br />&nbsp;',
                    'Usluga_Code' => '&nbsp;<br />&nbsp;', // Petrov
                    'AggType_Name' => '&nbsp;<br />&nbsp;',
                    'AggType_Code' => '&nbsp;<br />&nbsp;',
					'AnesthesiaClass_Name' => '&nbsp;<br />&nbsp;',
					'EvnUslugaOper_IsEndoskop' => '&nbsp;',
					'EvnUslugaOper_IsLazer' => '&nbsp;',
					'EvnUslugaOper_IsKriogen' => '&nbsp;'
				);
			}
		}

		$invalid_type_name = '';
		$lpu_unit_type_name = '';

		switch ( $response[0]['InvalidType_Code'] ) {
			case 81:
				$invalid_type_name = "3-я группа";
			break;

			case 82:
				$invalid_type_name = "2-я группа";
			break;

			case 83:
				$invalid_type_name = "1-я группа";
			break;
		}

		switch ( $response[0]['LpuUnitType_Code'] ) {
			case 2:
				$lpu_unit_type_name = "круглосуточного стационара";
			break;

			case 3:
				$lpu_unit_type_name = "дневного стационара при стационаре";
			break;

			case 4:
				$lpu_unit_type_name = "стационара на дому";
			break;

			case 5:
				$lpu_unit_type_name = "дневного стационара при поликлинике";
			break;
		}

		if (($KVS_Type == 'V')||($KVS_Type == 'G'))
		{
			$hosp_result = $this->samara_dbmodel->GetHosp_Result($data);

			if($KVS_Type == 'G')
			{

				for ( $j = count($evn_diag_ps_admit_data); $j < 3; $j++ ) {
					$evn_diag_ps_admit_data[] = array(
						'DiagSetClass_Name' => '&nbsp;<br />&nbsp;',
						'Diag_Code' => '&nbsp;',
						'Diag_Name' => '&nbsp;'
					);
				}
				//Проверям исход предыдущего движения (был ли перевод в другое отделение)
				$prev_result = $this->samara_dbmodel->Check_OtherDep($data);

				if ($prev_result) {
					//var_dump($prev_result);
					//exit;
					$response[0]['EvnPS_setDate'] = $prev_result[0]['EvnSection_setDate'];
					$response[0]['EvnPS_setTime'] = $prev_result[0]['EvnSection_setTime'];
					$response[0]['PrehospDirect_Name'] = $prev_result[0]['LpuSection_Name'];
					$response[0]['PrehospOrg_Name'] = $prev_result[0]['Post_Name'];
				}
				$print_data = array(
					'AnatomMedPersonal_Code' => returnValidHTMLString($hosp_result[0]['AnatomMedPersonal_Code']),
					'AnatomMedPersonal_Fio' => returnValidHTMLString($hosp_result[0]['AnatomMedPersonal_Fio']),
					'AnatomWhere_Name' => returnValidHTMLString($hosp_result[0]['AnatomWhere_Name']),
					'Document_Num' => returnValidHTMLString($response[0]['Document_Num']),
					'Document_Ser' => returnValidHTMLString($response[0]['Document_Ser']),
					'DocumentType_Name' => returnValidHTMLString($response[0]['DocumentType_Name']),
					'EvnAnatomPlace' => returnValidHTMLString($hosp_result[0]['EvnAnatomPlace']),
					'EvnDie_expDate' => returnValidHTMLString($hosp_result[0]['EvnDie_expDate']),
					'EvnDie_expTime' => returnValidHTMLString($hosp_result[0]['EvnDie_expTime']),
					'EvnDie_IsAnatom' => $hosp_result[0]['EvnDie_IsAnatom'] == 1 ? 'X' : '&nbsp;',
					'EvnDiagPSAdmitData' => $evn_diag_ps_admit_data,
					'EvnDiagPSAnatomData' => $evn_diag_ps_anatom_data,
					'EvnDiagPSHospData' => $evn_diag_ps_hosp_data,
					'EvnDieMedPersonal_Code' => returnValidHTMLString($hosp_result[0]['EvnDieMedPersonal_Code']),
					'EvnDieMedPersonal_Fin' => returnValidHTMLString($hosp_result[0]['EvnDieMedPersonal_Fin']),
					'EvnDirection_Num' => returnValidHTMLString($response[0]['EvnDirection_Num']),
					'EvnDirection_setDate' => returnValidHTMLString($response[0]['EvnDirection_setDate']),
					'EvnLeave_IsAmbul' => $hosp_result[0]['EvnLeave_IsAmbul'] == 1 ? 'X' : '&nbsp;',
					'EvnLeave_UKL' => returnValidHTMLString($hosp_result[0]['EvnLeave_UKL']),
					'EvnPS_CodeConv' => returnValidHTMLString($response[0]['EvnPS_CodeConv']),
					'EvnPS_disDate' => returnValidHTMLString($hosp_result[0]['EvnSection_disDate']),
					'EvnPS_disTime' => returnValidHTMLString($hosp_result[0]['EvnSection_disTime']),
					'EvnPS_HospCount' => '',
					'EvnPS_IsDiagMismatch' => $response[0]['EvnPS_IsDiagMismatch'] == 1 ? 'X' : '&nbsp;',
					'EvnPS_IsImperHosp' => $response[0]['EvnPS_IsImperHosp'] == 1 ? 'X' : '&nbsp;',
					'EvnPS_IsShortVolume' => $response[0]['EvnPS_IsShortVolume'] == 1 ? 'X' : '&nbsp;',
					'EvnPS_IsUnlaw' => '',
					'EvnPS_IsUnport' => '',
					'EvnPS_IsWrongCure' => $response[0]['EvnPS_IsWrongCure'] == 1 ? 'X' : '&nbsp;',
					'EvnPS_NumCard' => returnValidHTMLString($response[0]['EvnPS_NumCard']),
					'EvnPS_NumConv' => returnValidHTMLString($response[0]['EvnPS_NumConv']),
					'EvnPS_setDate' => returnValidHTMLString($response[0]['EvnPS_setDate']),
					'EvnPS_setTime' => returnValidHTMLString($response[0]['EvnPS_setTime']),
					'EvnPS_TimeDesease' => '',
					'EvnPSTemplateTitle' => 'Печать карты выбывшего из стационара',
					'EvnSectionData' => $evn_section_data,
					'EvnStickData' => $evn_stick_data,
					'EvnUdost_SerNum' => returnValidHTMLString($response[0]['EvnUdost_SerNum']),
					'EvnUslugaOperData' => $evn_usluga_oper_data,
					'InvalidType_begDate' => returnValidHTMLString($response[0]['InvalidType_begDate']),
					'InvalidType_Name' => returnValidHTMLString($invalid_type_name),
					'KLAreaType_Name' => returnValidHTMLString($response[0]['KLAreaType_Name']),
					'LeaveCause_Name' => returnValidHTMLString($hosp_result[0]['LeaveCause_Name']),
					'LeaveType_Name' => returnValidHTMLString($hosp_result[0]['LeaveType_Name']),
					'Lpu_Name' => returnValidHTMLString($response[0]['Lpu_Name']),
					'LpuRegion_Name' => returnValidHTMLString($response[0]['LpuRegion_Name']),
					'LpuSection_Name' => '',
					'LpuUnitType_Name' => returnValidHTMLString($lpu_unit_type_name),
					'OmsSprTerr_Code' => '&nbsp;',
					'OmsSprTerr_Name' => '&nbsp;',
					'OrgDep_Name' => returnValidHTMLString($response[0]['OrgDep_Name']),
					'OrgJob_Name' => returnValidHTMLString($response[0]['OrgJob_Name']),
					'OrgSmo_Name' => returnValidHTMLString($response[0]['OrgSmo_Name']),
					'OtherLpu_Name' => returnValidHTMLString($hosp_result[0]['OtherLpu_Name']),
					'OtherStac_Name' => returnValidHTMLString($hosp_result[0]['OtherStac_Name']),
					'OtherStacType_Name' => returnValidHTMLString($hosp_result[0]['OtherStacType_Name']),
					'PAddress_Name' => returnValidHTMLString($response[0]['PAddress_Name']),
					'PayType_Name' => returnValidHTMLString($response[0]['PayType_Name']),
					'Person_Birthday' => returnValidHTMLString($response[0]['Person_Birthday']),
					'Person_Fio' => returnValidHTMLString($response[0]['Person_Fio']),
					'PersonCard_Code' => returnValidHTMLString($response[0]['PersonCard_Code']),
					'Polis_Num' => returnValidHTMLString($response[0]['Polis_Num']),
					'Polis_Ser' => returnValidHTMLString($response[0]['Polis_Ser']),
					'PolisType_Name' => returnValidHTMLString($response[0]['PolisType_Name']),
					'Post_Name' => returnValidHTMLString($response[0]['Post_Name']),
					'PrehospArrive_Name' => returnValidHTMLString($response[0]['PrehospArrive_Name']),
					'PrehospDirect_Name' => returnValidHTMLString($response[0]['PrehospDirect_Name']),
					'PreHospMedPersonal_Fio' => '',
					'PrehospOrg_Name' => returnValidHTMLString($response[0]['PrehospOrg_Name']),
					'PrehospToxic_Name' => '',
					'PrehospTrauma_Name' => '',
					'PrehospType_Name' => '',
					'PrivilegeType_Name' => returnValidHTMLString($response[0]['PrivilegeType_Name']),
					'ResultDesease_Name' => returnValidHTMLString($hosp_result[0]['ResultDesease_Name']),
					'Sex_Name' => returnValidHTMLString($response[0]['Sex_Name']),
					'SocStatus_Name' => returnValidHTMLString($response[0]['SocStatus_Name']),
					'UAddress_Name' => returnValidHTMLString($response[0]['UAddress_Name']),
					'MedPersonal_did' => returnValidHTMLString($response[0]['MedPersonal_did']),
					'HospSection_Name' => returnValidHTMLString($response[0]['HospSection_Name']),
					'HospSectionBedProfile_Name' => returnValidHTMLString($response[0]['HospSectionBedProfile_Name']),
					'Hosp_disDate' => returnValidHTMLString($response[0]['Hosp_disDate']),
					'HospitalDays' => returnValidHTMLString($response[0]['HospitalDays']),
					'HospLeaveType_Name' => returnValidHTMLString($response[0]['HospLeaveType_Name']),
					'HospResultDesease_Name' => returnValidHTMLString($response[0]['HospResultDesease_Name'])
				);
			}
			else{
				$print_data = array(
					'AnatomMedPersonal_Code' => returnValidHTMLString($hosp_result[0]['AnatomMedPersonal_Code']),
					'AnatomMedPersonal_Fio' => returnValidHTMLString($hosp_result[0]['AnatomMedPersonal_Fio']),
					'AnatomWhere_Name' => returnValidHTMLString($hosp_result[0]['AnatomWhere_Name']),
					'Document_Num' => returnValidHTMLString($response[0]['Document_Num']),
					'Document_Ser' => returnValidHTMLString($response[0]['Document_Ser']),
					'DocumentType_Name' => returnValidHTMLString($response[0]['DocumentType_Name']),
					'EvnAnatomPlace' => returnValidHTMLString($hosp_result[0]['EvnAnatomPlace']),
					'EvnDie_expDate' => returnValidHTMLString($hosp_result[0]['EvnDie_expDate']),
					'EvnDie_expTime' => returnValidHTMLString($hosp_result[0]['EvnDie_expTime']),
					'EvnDie_IsAnatom' => $hosp_result[0]['EvnDie_IsAnatom'] == 1 ? 'X' : '&nbsp;',
					'EvnDiagPSAdmitData' => $evn_diag_ps_admit_data,
					'EvnDiagPSAnatomData' => $evn_diag_ps_anatom_data,
					'EvnDiagPSHospData' => $evn_diag_ps_hosp_data,
					'EvnDieMedPersonal_Code' => returnValidHTMLString($hosp_result[0]['EvnDieMedPersonal_Code']),
					'EvnDieMedPersonal_Fin' => returnValidHTMLString($hosp_result[0]['EvnDieMedPersonal_Fin']),
					'EvnDirection_Num' => returnValidHTMLString($response[0]['EvnDirection_Num']),
					'EvnDirection_setDate' => returnValidHTMLString($response[0]['EvnDirection_setDate']),
					'EvnLeave_IsAmbul' => $hosp_result[0]['EvnLeave_IsAmbul'] == 1 ? 'X' : '&nbsp;',
					'EvnLeave_UKL' => returnValidHTMLString($hosp_result[0]['EvnLeave_UKL']),
					'EvnPS_CodeConv' => returnValidHTMLString($response[0]['EvnPS_CodeConv']),
					'EvnPS_disDate' => returnValidHTMLString($hosp_result[0]['EvnSection_disDate']),
					'EvnPS_disTime' => returnValidHTMLString($hosp_result[0]['EvnSection_disTime']),
					'EvnPS_HospCount' => returnValidHTMLString($response[0]['EvnPS_HospCount']),
					'EvnPS_IsDiagMismatch' => $response[0]['EvnPS_IsDiagMismatch'] == 1 ? 'X' : '&nbsp;',
					'EvnPS_IsImperHosp' => $response[0]['EvnPS_IsImperHosp'] == 1 ? 'X' : '&nbsp;',
					'EvnPS_IsShortVolume' => $response[0]['EvnPS_IsShortVolume'] == 1 ? 'X' : '&nbsp;',
					'EvnPS_IsUnlaw' => $response[0]['EvnPS_IsUnlaw'] == 1 ? 'X' : '&nbsp;',
					'EvnPS_IsUnport' => $response[0]['EvnPS_IsUnport'] == 1 ? 'X' : '&nbsp;',
					'EvnPS_IsWrongCure' => $response[0]['EvnPS_IsWrongCure'] == 1 ? 'X' : '&nbsp;',
					'EvnPS_NumCard' => returnValidHTMLString($response[0]['EvnPS_NumCard']),
					'EvnPS_NumConv' => returnValidHTMLString($response[0]['EvnPS_NumConv']),
					'EvnPS_setDate' => returnValidHTMLString($response[0]['EvnPS_setDate']),
					'EvnPS_setTime' => returnValidHTMLString($response[0]['EvnPS_setTime']),
					'EvnPS_TimeDesease' => returnValidHTMLString($response[0]['EvnPS_TimeDesease']),
					'EvnPSTemplateTitle' => 'Печать карты выбывшего из стационара',
					'EvnSectionData' => $evn_section_data,
					'EvnStickData' => $evn_stick_data,
					'EvnUdost_SerNum' => returnValidHTMLString($response[0]['EvnUdost_SerNum']),
					'EvnUslugaOperData' => $evn_usluga_oper_data,
					'InvalidType_begDate' => returnValidHTMLString($response[0]['InvalidType_begDate']),
					'InvalidType_Name' => returnValidHTMLString($invalid_type_name),
					'KLAreaType_Name' => returnValidHTMLString($response[0]['KLAreaType_Name']),
					'LeaveCause_Name' => returnValidHTMLString($hosp_result[0]['LeaveCause_Name']),
					'LeaveType_Name' => returnValidHTMLString($hosp_result[0]['LeaveType_Name']),
					'Lpu_Name' => returnValidHTMLString($response[0]['Lpu_Name']),
					'LpuRegion_Name' => returnValidHTMLString($response[0]['LpuRegion_Name']),
					'LpuSection_Name' => returnValidHTMLString($response[0]['LpuSection_Name']),
					'LpuUnitType_Name' => returnValidHTMLString($lpu_unit_type_name),
					'OmsSprTerr_Code' => '&nbsp;',
					'OmsSprTerr_Name' => '&nbsp;',
					'OrgDep_Name' => returnValidHTMLString($response[0]['OrgDep_Name']),
					'OrgJob_Name' => returnValidHTMLString($response[0]['OrgJob_Name']),
					'OrgSmo_Name' => returnValidHTMLString($response[0]['OrgSmo_Name']),
					'OtherLpu_Name' => returnValidHTMLString($hosp_result[0]['OtherLpu_Name']),
					'OtherStac_Name' => returnValidHTMLString($hosp_result[0]['OtherStac_Name']),
					'OtherStacType_Name' => returnValidHTMLString($hosp_result[0]['OtherStacType_Name']),
					'PAddress_Name' => returnValidHTMLString($response[0]['PAddress_Name']),
					'PayType_Name' => returnValidHTMLString($response[0]['PayType_Name']),
					'Person_Birthday' => returnValidHTMLString($response[0]['Person_Birthday']),
					'Person_Fio' => returnValidHTMLString($response[0]['Person_Fio']),
					'PersonCard_Code' => returnValidHTMLString($response[0]['PersonCard_Code']),
					'Polis_Num' => returnValidHTMLString($response[0]['Polis_Num']),
					'Polis_Ser' => returnValidHTMLString($response[0]['Polis_Ser']),
					'PolisType_Name' => returnValidHTMLString($response[0]['PolisType_Name']),
					'Post_Name' => returnValidHTMLString($response[0]['Post_Name']),
					'PrehospArrive_Name' => returnValidHTMLString($response[0]['PrehospArrive_Name']),
					'PrehospDirect_Name' => returnValidHTMLString($response[0]['PrehospDirect_Name']),
					'PreHospMedPersonal_Fio' => returnValidHTMLString($response[0]['PreHospMedPersonal_Fio']),
					'PrehospOrg_Name' => returnValidHTMLString($response[0]['PrehospOrg_Name']),
					'PrehospToxic_Name' => returnValidHTMLString($response[0]['PrehospToxic_Name']),
					'PrehospTrauma_Name' => returnValidHTMLString($response[0]['PrehospTrauma_Name']),
					'PrehospType_Name' => returnValidHTMLString($response[0]['PrehospType_Name']),
					'PrivilegeType_Name' => returnValidHTMLString($response[0]['PrivilegeType_Name']),
					'ResultDesease_Name' => returnValidHTMLString($hosp_result[0]['ResultDesease_Name']),
					'Sex_Name' => returnValidHTMLString($response[0]['Sex_Name']),
					'SocStatus_Name' => returnValidHTMLString($response[0]['SocStatus_Name']),
					'UAddress_Name' => returnValidHTMLString($response[0]['UAddress_Name']),
					'MedPersonal_did' => returnValidHTMLString($response[0]['MedPersonal_did']),
					'HospSection_Name' => returnValidHTMLString($response[0]['HospSection_Name']),
					'HospSectionBedProfile_Name' => returnValidHTMLString($response[0]['HospSectionBedProfile_Name']),
					'Hosp_disDate' => returnValidHTMLString($response[0]['Hosp_disDate']),
					'HospitalDays' => returnValidHTMLString($response[0]['HospitalDays']),
					'HospLeaveType_Name' => returnValidHTMLString($response[0]['HospLeaveType_Name']),
					'HospResultDesease_Name' => returnValidHTMLString($response[0]['HospResultDesease_Name'])
				);
			}
		}
		else if ($KVS_Type == 'A')
		{
			$hosp_result = $this->samara_dbmodel->GetHosp_Result($data);
			$print_data = array(
				'AnatomMedPersonal_Code' => returnValidHTMLString($hosp_result[0]['AnatomMedPersonal_Code']),
				'AnatomMedPersonal_Fio' => returnValidHTMLString($hosp_result[0]['AnatomMedPersonal_Fio']),
				'AnatomWhere_Name' => returnValidHTMLString($hosp_result[0]['AnatomWhere_Name']),
				'Document_Num' => returnValidHTMLString($response[0]['Document_Num']),
				'Document_Ser' => returnValidHTMLString($response[0]['Document_Ser']),
				'DocumentType_Name' => returnValidHTMLString($response[0]['DocumentType_Name']),
				'EvnAnatomPlace' => returnValidHTMLString($hosp_result[0]['EvnAnatomPlace']),
				'EvnDie_expDate' => returnValidHTMLString($hosp_result[0]['EvnDie_expDate']),
				'EvnDie_expTime' => returnValidHTMLString($hosp_result[0]['EvnDie_expTime']),
				'EvnDie_IsAnatom' => $hosp_result[0]['EvnDie_IsAnatom'] == 1 ? 'X' : '&nbsp;',
				'EvnDiagPSAdmitData' => $evn_diag_ps_admit_data,
				'EvnDiagPSAnatomData' => $evn_diag_ps_anatom_data,
				'EvnDiagPSHospData' => $evn_diag_ps_hosp_data,
				'EvnDieMedPersonal_Code' => returnValidHTMLString($hosp_result[0]['EvnDieMedPersonal_Code']),
				'EvnDieMedPersonal_Fin' => returnValidHTMLString($hosp_result[0]['EvnDieMedPersonal_Fin']),
				'EvnDirection_Num' => returnValidHTMLString($response[0]['EvnDirection_Num']),
				'EvnDirection_setDate' => returnValidHTMLString($response[0]['EvnDirection_setDate']),
				'EvnLeave_IsAmbul' => $hosp_result[0]['EvnLeave_IsAmbul'] == 1 ? 'X' : '&nbsp;',
				'EvnLeave_UKL' => returnValidHTMLString($hosp_result[0]['EvnLeave_UKL']),
				'EvnPS_CodeConv' => returnValidHTMLString($response[0]['EvnPS_CodeConv']),
				'EvnPS_disDate' => returnValidHTMLString($hosp_result[0]['EvnPS_disDate']),
				'EvnPS_disTime' => returnValidHTMLString($hosp_result[0]['EvnPS_disTime']),
				'EvnPS_HospCount' => returnValidHTMLString($response[0]['EvnPS_HospCount']),
				'EvnPS_IsDiagMismatch' => $response[0]['EvnPS_IsDiagMismatch'] == 1 ? 'X' : '&nbsp;',
				'EvnPS_IsImperHosp' => $response[0]['EvnPS_IsImperHosp'] == 1 ? 'X' : '&nbsp;',
				'EvnPS_IsShortVolume' => $response[0]['EvnPS_IsShortVolume'] == 1 ? 'X' : '&nbsp;',
				'EvnPS_IsUnlaw' => $response[0]['EvnPS_IsUnlaw'] == 1 ? 'X' : '&nbsp;',
				'EvnPS_IsUnport' => $response[0]['EvnPS_IsUnport'] == 1 ? 'X' : '&nbsp;',
				'EvnPS_IsWrongCure' => $response[0]['EvnPS_IsWrongCure'] == 1 ? 'X' : '&nbsp;',
				'EvnPS_NumCard' => returnValidHTMLString($response[0]['EvnPS_NumCard']),
				'EvnPS_NumConv' => returnValidHTMLString($response[0]['EvnPS_NumConv']),
				'EvnPS_setDate' => returnValidHTMLString($response[0]['EvnPS_setDate']),
				'EvnPS_setTime' => returnValidHTMLString($response[0]['EvnPS_setTime']),
				'EvnPS_TimeDesease' => returnValidHTMLString($response[0]['EvnPS_TimeDesease']),
				'EvnPSTemplateTitle' => 'Печать карты выбывшего из стационара',
				'EvnSectionData' => $evn_section_data,
				'EvnStickData' => $evn_stick_data,
				'EvnUdost_SerNum' => returnValidHTMLString($response[0]['EvnUdost_SerNum']),
				'EvnUslugaOperData' => $evn_usluga_oper_data,
				'InvalidType_begDate' => returnValidHTMLString($response[0]['InvalidType_begDate']),
				'InvalidType_Name' => returnValidHTMLString($invalid_type_name),
				'KLAreaType_Name' => returnValidHTMLString($response[0]['KLAreaType_Name']),
				'LeaveCause_Name' => returnValidHTMLString($hosp_result[0]['LeaveCause_Name']),
				'LeaveType_Name' => returnValidHTMLString($hosp_result[0]['LeaveType_Name']),
				'Lpu_Name' => returnValidHTMLString($response[0]['Lpu_Name']),
				'LpuRegion_Name' => returnValidHTMLString($response[0]['LpuRegion_Name']),
				'LpuSection_Name' => returnValidHTMLString($response[0]['LpuSection_Name']),
				'LpuUnitType_Name' => returnValidHTMLString($lpu_unit_type_name),
				'OmsSprTerr_Code' => '&nbsp;',
				'OmsSprTerr_Name' => '&nbsp;',
				'OrgDep_Name' => returnValidHTMLString($response[0]['OrgDep_Name']),
				'OrgJob_Name' => returnValidHTMLString($response[0]['OrgJob_Name']),
				'OrgSmo_Name' => returnValidHTMLString($response[0]['OrgSmo_Name']),
				'OtherLpu_Name' => returnValidHTMLString($hosp_result[0]['OtherLpu_Name']),
				'OtherStac_Name' => returnValidHTMLString($hosp_result[0]['OtherStac_Name']),
				'OtherStacType_Name' => returnValidHTMLString($hosp_result[0]['OtherStacType_Name']),
				'PAddress_Name' => returnValidHTMLString($response[0]['PAddress_Name']),
				'PayType_Name' => returnValidHTMLString($response[0]['PayType_Name']),
				'Person_Birthday' => returnValidHTMLString($response[0]['Person_Birthday']),
				'Person_Fio' => returnValidHTMLString($response[0]['Person_Fio']),
				'PersonCard_Code' => returnValidHTMLString($response[0]['PersonCard_Code']),
				'Polis_Num' => returnValidHTMLString($response[0]['Polis_Num']),
				'Polis_Ser' => returnValidHTMLString($response[0]['Polis_Ser']),
				'PolisType_Name' => returnValidHTMLString($response[0]['PolisType_Name']),
				'Post_Name' => returnValidHTMLString($response[0]['Post_Name']),
				'PrehospArrive_Name' => returnValidHTMLString($response[0]['PrehospArrive_Name']),
				'PrehospDirect_Name' => returnValidHTMLString($response[0]['PrehospDirect_Name']),
				'PreHospMedPersonal_Fio' => returnValidHTMLString($response[0]['PreHospMedPersonal_Fio']),
				'PrehospOrg_Name' => returnValidHTMLString($response[0]['PrehospOrg_Name']),
				'PrehospToxic_Name' => returnValidHTMLString($response[0]['PrehospToxic_Name']),
				'PrehospTrauma_Name' => returnValidHTMLString($response[0]['PrehospTrauma_Name']),
				'PrehospType_Name' => returnValidHTMLString($response[0]['PrehospType_Name']),
				'PrivilegeType_Name' => returnValidHTMLString($response[0]['PrivilegeType_Name']),
				'ResultDesease_Name' => returnValidHTMLString($hosp_result[0]['ResultDesease_Name']),
				'Sex_Name' => returnValidHTMLString($response[0]['Sex_Name']),
				'SocStatus_Name' => returnValidHTMLString($response[0]['SocStatus_Name']),
				'UAddress_Name' => returnValidHTMLString($response[0]['UAddress_Name']),
				'MedPersonal_did' => returnValidHTMLString($response[0]['MedPersonal_did']),
				'HospSection_Name' => returnValidHTMLString($response[0]['HospSection_Name']),
				'HospSectionBedProfile_Name' => returnValidHTMLString($response[0]['HospSectionBedProfile_Name']),
				'Hosp_disDate' => returnValidHTMLString($response[0]['Hosp_disDate']),
				'HospitalDays' => returnValidHTMLString($response[0]['HospitalDays']),
				'HospLeaveType_Name' => returnValidHTMLString($response[0]['HospLeaveType_Name']),
				'HospResultDesease_Name' => returnValidHTMLString($response[0]['HospResultDesease_Name'])
			);
		}
		else{ //Вариант Б
			$print_data = array(
				'AnatomMedPersonal_Code' => returnValidHTMLString($response[0]['AnatomMedPersonal_Code']),
				'AnatomMedPersonal_Fio' => returnValidHTMLString($response[0]['AnatomMedPersonal_Fio']),
				'AnatomWhere_Name' => returnValidHTMLString($response[0]['AnatomWhere_Name']),
				'Document_Num' => returnValidHTMLString($response[0]['Document_Num']),
				'Document_Ser' => returnValidHTMLString($response[0]['Document_Ser']),
				'DocumentType_Name' => returnValidHTMLString($response[0]['DocumentType_Name']),
				'DocumentType_Code' => returnValidHTMLString($response[0]['DocumentType_Code']),
				'DocumentType_MaskSer' => returnValidHTMLString($response[0]['DocumentType_MaskSer']),
				'DocumentType_MaskNum' => returnValidHTMLString($response[0]['DocumentType_MaskNum']),
				'EvnAnatomPlace' => returnValidHTMLString($response[0]['EvnAnatomPlace']),
				'EvnDie_expDate' => returnValidHTMLString($response[0]['EvnDie_expDate']),
				'EvnDie_expTime' => returnValidHTMLString($response[0]['EvnDie_expTime']),
				'EvnDie_IsAnatom' => $response[0]['EvnDie_IsAnatom'] == 1 ? 'X' : '&nbsp;',
				'EvnDiagPSAdmitData' => $evn_diag_ps_admit_data,
				'EvnDiagPSAnatomData' => $evn_diag_ps_anatom_data,
				'EvnDiagPSHospData' => $evn_diag_ps_hosp_data,
				'EvnDieMedPersonal_Code' => returnValidHTMLString($response[0]['EvnDieMedPersonal_Code']),
				'EvnDieMedPersonal_Fin' => returnValidHTMLString($response[0]['EvnDieMedPersonal_Fin']),
				'EvnDirection_Num' => returnValidHTMLString($response[0]['EvnDirection_Num']),
				'EvnDirection_setDate' => returnValidHTMLString($response[0]['EvnDirection_setDate']),
				'EvnLeave_IsAmbul' => $response[0]['EvnLeave_IsAmbul'] == 1 ? 'X' : '&nbsp;',
				'EvnLeave_UKL' => returnValidHTMLString($response[0]['EvnLeave_UKL']),
				'EvnPS_CodeConv' => returnValidHTMLString($response[0]['EvnPS_CodeConv']),
				'EvnPS_disDate' => returnValidHTMLString($response[0]['EvnPS_disDate']),
				'EvnPS_disTime' => returnValidHTMLString($response[0]['EvnPS_disTime']),
				'EvnPS_HospCount' => returnValidHTMLString($response[0]['EvnPS_HospCount']),
				'EvnPS_IsDiagMismatch' => $response[0]['EvnPS_IsDiagMismatch'] == 1 ? 'X' : '&nbsp;',
				'EvnPS_IsImperHosp' => $response[0]['EvnPS_IsImperHosp'] == 1 ? 'X' : '&nbsp;',
				'EvnPS_IsShortVolume' => $response[0]['EvnPS_IsShortVolume'] == 1 ? 'X' : '&nbsp;',
				'EvnPS_IsUnlaw' => $response[0]['EvnPS_IsUnlaw'] == 1 ? 'X' : '&nbsp;',
				'EvnPS_IsUnport' => $response[0]['EvnPS_IsUnport'] == 1 ? 'X' : '&nbsp;',
				'EvnPS_IsWrongCure' => $response[0]['EvnPS_IsWrongCure'] == 1 ? 'X' : '&nbsp;',
				'EvnPS_NumCard' => returnValidHTMLString($response[0]['EvnPS_NumCard']),
				'EvnPS_NumConv' => returnValidHTMLString($response[0]['EvnPS_NumConv']),
				'EvnPS_setDate' => returnValidHTMLString($response[0]['EvnPS_setDate']),
				'EvnPS_setTime' => returnValidHTMLString($response[0]['EvnPS_setTime']),
				'EvnPS_TimeDesease' => returnValidHTMLString($response[0]['EvnPS_TimeDesease']),
				'EvnPSTemplateTitle' => 'Печать карты выбывшего из стационара',
				'EvnSectionData' => $evn_section_data,
				'EvnStickData' => $evn_stick_data,
				'EvnUdost_SerNum' => returnValidHTMLString($response[0]['EvnUdost_SerNum']),
				'EvnUslugaOperData' => $evn_usluga_oper_data,
				'InvalidType_begDate' => returnValidHTMLString($response[0]['InvalidType_begDate']),
				'InvalidType_Name' => returnValidHTMLString($invalid_type_name),
				'KLAreaType_Name' => returnValidHTMLString($response[0]['KLAreaType_Name']),
				'LeaveCause_Name' => returnValidHTMLString($response[0]['LeaveCause_Name']),
				'LeaveType_Name' => returnValidHTMLString($response[0]['LeaveType_Name']),
				'Lpu_Name' => returnValidHTMLString($response[0]['Lpu_Name']),
				'LpuRegion_Name' => returnValidHTMLString($response[0]['LpuRegion_Name']),
				'LpuSection_Name' => returnValidHTMLString($response[0]['LpuSection_Name']),
				'LpuUnitType_Name' => returnValidHTMLString($lpu_unit_type_name),
				'OmsSprTerr_Code' => returnValidHTMLString($response[0]['OMSSprTerr_Code']),
				'OmsSprTerr_Name' => '&nbsp;',
				'OrgDep_Name' => returnValidHTMLString($response[0]['OrgDep_Name']),
				'OrgJob_Name' => returnValidHTMLString($response[0]['OrgJob_Name']),
				'OrgSmo_Name' => returnValidHTMLString($response[0]['OrgSmo_Name']),
				'OtherLpu_Name' => returnValidHTMLString($response[0]['OtherLpu_Name']),
				'OtherStac_Name' => returnValidHTMLString($response[0]['OtherStac_Name']),
				'OtherStacType_Name' => returnValidHTMLString($response[0]['OtherStacType_Name']),
				'PAddress_Name' => returnValidHTMLString($response[0]['PAddress_Name']),
				'PayType_Name' => returnValidHTMLString($response[0]['PayType_Name']),
				'Person_Birthday' => returnValidHTMLString($response[0]['Person_Birthday']),
				'Person_Fio' => returnValidHTMLString($response[0]['Person_Fio']),
				'PersonCard_Code' => returnValidHTMLString($response[0]['PersonCard_Code']),
				'Person_Snils' => returnValidHTMLString($response[0]['Person_Snils']),
				'Person_EdNum' => returnValidHTMLString($response[0]['Person_EdNum']),
				'Polis_Num' => returnValidHTMLString($response[0]['Polis_Num']),
				'Polis_Ser' => returnValidHTMLString($response[0]['Polis_Ser']),
				'PolisType_Name' => returnValidHTMLString($response[0]['PolisType_Name']),
				'Post_Name' => returnValidHTMLString($response[0]['Post_Name']),
				'PrehospArrive_Name' => returnValidHTMLString($response[0]['PrehospArrive_Name']),
				'PrehospDirect_Name' => returnValidHTMLString($response[0]['PrehospDirect_Name']),
				'PreHospMedPersonal_Fio' => returnValidHTMLString($response[0]['PreHospMedPersonal_Fio']),
				'PrehospLpu_Id' => returnValidHTMLString($response[0]['PreHospLpu_Id']),
				'PrehospOrg_Name' => returnValidHTMLString($response[0]['PrehospOrg_Name']),
				'PrehospOrg_Nick' => returnValidHTMLString($response[0]['PrehospOrg_Nick']),
				'PrehospToxic_Name' => returnValidHTMLString($response[0]['PrehospToxic_Name']),
				'PrehospTrauma_Name' => returnValidHTMLString($response[0]['PrehospTrauma_Name']),
				'PrehospType_Name' => returnValidHTMLString($response[0]['PrehospType_Name']),
				'PrivilegeType_Name' => returnValidHTMLString($response[0]['PrivilegeType_Name']),
				'PrivilegeType_Code' => returnValidHTMLString($response[0]['PrivilegeType_Code']),
				'ResultDesease_Name' => returnValidHTMLString($response[0]['ResultDesease_Name']),
				'Sex_Name' => returnValidHTMLString($response[0]['Sex_Name']),
				'Sex_Code' => returnValidHTMLString($response[0]['Sex_Code']),
				'SocStatus_Name' => returnValidHTMLString($response[0]['SocStatus_Name']),
				'UAddress_Name' => returnValidHTMLString($response[0]['UAddress_Name']),
				'MedPersonal_did' => returnValidHTMLString($response[0]['MedPersonal_did'])
				
				// Petrov
				,				
				'OrgSmo_Nick' => returnValidHTMLString($response[0]['OrgSmo_Nick']),
				'OrgSmo_Code' => returnValidHTMLString($response[0]['OrgSmo_Code']),
				'PrimaryHospDiag_Name' => $evn_diag_ps_hosp_data[0]['Diag_Code']." ".$evn_diag_ps_hosp_data[0]['Diag_Name'],
				'PrimaryRecepDiag_Name' => $evn_diag_ps_admit_data[0]['Diag_Code']." ".$evn_diag_ps_admit_data[0]['Diag_Name'],
                'PreHospMedPersonal_Code' => returnValidHTMLString($response[0]['PreHospMedPersonal_Code']),
                'EvnPS_outcomeDate' => returnValidHTMLString($response[0]['EvnPS_outcomeDate']),
                'EvnPS_outcomeTime' => returnValidHTMLString($response[0]['EvnPS_outcomeTime']),
          		'EvnStick_begDate' => $evn_stick_data[0]['EvnStick_begDate'],
				'EvnStick_endDate' => $evn_stick_data[0]['EvnStick_endDate'],
				'EvnStick_Sex_Name' => $evn_stick_data[0]['Sex_Name'],
				'EvnStick_Age' => $evn_stick_data[0]['EvnStick_Age'],
                'Ocato' => returnValidHTMLString($response[0]['Ocato']),
                'SectionsDays' => $sectionsDays,
				'KemNapravlen' => returnValidHTMLString($response[0]['KemNapravlen']),
                'HospType_Name' => returnValidHTMLString($response[0]['HospType_Name']),
                'Dolgnost_Name'=> returnValidHTMLString($response[0]['Dolgnost_Name']),
                'Person_Phone' => returnValidHTMLString($response[0]['Person_Phone'])
                		
				//Golovin
				,
				'HospSection_Name' => returnValidHTMLString($response[0]['HospSection_Name']),
				'HospSectionBedProfile_Name' => returnValidHTMLString($response[0]['HospSectionBedProfile_Name']),
				'Hosp_disDate' => returnValidHTMLString($response[0]['Hosp_disDate']),
				'HospitalDays' => returnValidHTMLString($response[0]['HospitalDays']),
				'HospLeaveType_Name' => returnValidHTMLString($response[0]['HospLeaveType_Name']),
				'HospLeaveType_Nick' => returnValidHTMLString($response[0]['HospLeaveType_Nick']),
				'HospResultDesease_Name' => returnValidHTMLString($response[0]['HospResultDesease_Name']),
				'HospResultDesease_SysNick' => returnValidHTMLString($response[0]['HospResultDesease_SysNick']),
				'EntranceModeType_id' => returnValidHTMLString($response[0]['EntranceModeType_id']),               
				'Lpu_Nick' => returnValidHTMLString($response[0]['Lpu_Nick']),
				'Person_Age' => returnValidHTMLString($response[0]['Person_Age']),
				'KLAreaType_SysNick' => returnValidHTMLString($response[0]['KLAreaType_SysNick']),
				'DeputyKind_Name' => returnValidHTMLString($response[0]['DeputyKind_Name']),
				'EvnPS_DeputyFIO' => returnValidHTMLString($response[0]['EvnPS_DeputyFIO']),
				'EvnPS_DeputyContact' => returnValidHTMLString($response[0]['EvnPS_DeputyContact']),
				'PrehospType_SysNick' => returnValidHTMLString($response[0]['PrehospType_SysNick']),
				'EvnPS_PhaseDescr_pid' => returnValidHTMLString($response[0]['EvnPS_PhaseDescr_pid']),
				'EvnPS_PhaseDescr_did' => returnValidHTMLString($response[0]['EvnPS_PhaseDescr_did']),
				'LpuAddress' => returnValidHTMLString($response[0]['LpuAddress']),
				'Document_begDate' => returnValidHTMLString($response[0]['Document_begDate']),
				'AttachedLpuNick' => returnValidHTMLString($response[0]['AttachedLpuNick']),
				'PersonPrivilege_Serie' => returnValidHTMLString($response[0]['PersonPrivilege_Serie']),
				'PersonPrivilege_Number' => returnValidHTMLString($response[0]['PersonPrivilege_Number']),
				'InvalidType_Name' => returnValidHTMLString($response[0]['InvalidType_Name'])

                // Sannikov
                ,
                'koikodni' => returnValidHTMLString($response[0]['koikodni']), 
                'UAddress_Name_length' => strlen(returnValidHTMLString($response[0]['UAddress_Name'])),  // TODO: сделать прилично выглядящий адрес или убрать это отсюда вместе с функциями
                //'UAddress_Name_Long' => $this->getLongAddress(returnValidHTMLString($response[0]['UAddress_Name'])),
                'PersonPhone_Phone' => returnValidHTMLString($response[0]['PersonPhone_Phone']),   // Дублирует код Павла Петрова
                'FamilyStatus_id' => returnValidHTMLString($response[0]['FamilyStatus_id'])
                ,'UslugaComplex_Code' => ''//returnValidHTMLString($response[0]['UslugaComplex_Code']).' ', // код операции
                ,'UslugaComplex_Name' => '' //returnValidHTMLString($response[0]['UslugaComplex_Name']),  // название операции
                ,'LeaveType_Code' => returnValidHTMLString($response[0]['LeaveType_Code']),   // Код типа выписки
                'new_EvnPS_disDate' => returnValidHTMLString($response[0]['new_EvnPS_disDate']),      // Дата выписки TODO: разобраться почему не работал старый вариант
                //'child_Person_id' => returnValidHTMLString($response[0]['child_Person_id']),
                'EvnSection_id' => returnValidHTMLString($response[0]['EvnSection_id']),  // id движения - для тестов
                'BirthSpecStac_BloodLoss' => returnValidHTMLString($response[0]['BirthSpecStac_BloodLoss']), // Кровопотеря при родах
                'BirthSpecStac_CountPregnancy' => returnValidHTMLString($response[0]['BirthSpecStac_CountPregnancy']),  // Которая беременность
                'BirthSpecStac_CountBirth' => returnValidHTMLString($response[0]['BirthSpecStac_CountBirth']),  // Которые роды 
                'BirthSpecStac_CountChildAlive' => returnValidHTMLString($response[0]['BirthSpecStac_CountChildAlive']),  // Кол-во живых детей
                'DeathChildCount' => !empty($response[0]['DeathChildCount']) ? returnValidHTMLString($response[0]['DeathChildCount']) : ''//0  // TODO: тестить мертворожденных на другом пациенте
                //,'Person_Phone' => returnValidHTMLString($response[0]['Person_Phone'])
                ,'LpuSectionWard_Name' => returnValidHTMLString($response[0]['LpuSectionWard_Name'])  // Номер палаты
                ,'BirthSpecStac_OutcomDT_h' => returnValidHTMLString($response[0]['BirthSpecStac_OutcomDT_h'])
                ,'BirthSpecStac_OutcomDT_m' => returnValidHTMLString($response[0]['BirthSpecStac_OutcomDT_m'])
                ,'PersonFamilyStatus_IsMarried' => returnValidHTMLString($response[0]['PersonFamilyStatus_IsMarried'])
                // Даты
                ,'EvnPS_setDate_Year' => returnValidHTMLString($response[0]['EvnPS_setDate_Year'])
                ,'EvnPS_setDate_Month' => returnValidHTMLString($response[0]['EvnPS_setDate_Month'])
                ,'EvnPS_setDate_Day' => returnValidHTMLString($response[0]['EvnPS_setDate_Day'])
                ,'EvnPS_setDate_Hour' => ($response[0]['EvnPS_setDate_Hour'] === 0) ? '00' : returnValidHTMLString($response[0]['EvnPS_setDate_Hour'])
                ,'EvnPS_setDate_Minute' => ($response[0]['EvnPS_setDate_Minute'] === 0) ? '00' : returnValidHTMLString($response[0]['EvnPS_setDate_Minute'])
                
                ,'EvnPS_disDate_Year' => returnValidHTMLString($response[0]['EvnPS_disDate_Year'])
                ,'EvnPS_disDate_Month' => returnValidHTMLString($response[0]['EvnPS_disDate_Month'])
                ,'EvnPS_disDate_Day' => returnValidHTMLString($response[0]['EvnPS_disDate_Day'])
                ,'EvnPS_disDate_Hour' => ($response[0]['EvnPS_disDate_Hour'] === 0) ? '00' : returnValidHTMLString($response[0]['EvnPS_disDate_Hour'])
                ,'EvnPS_disDate_Minute' => ($response[0]['EvnPS_disDate_Minute'] === 0) ? '00' : returnValidHTMLString($response[0]['EvnPS_disDate_Minute'])
                ,'EvnPS_disDate_raw' => returnValidHTMLString($response[0]['EvnPS_disDate_raw'])
                
                ,'last_EvnPS_disDate_Year' => returnValidHTMLString($response[0]['last_EvnPS_disDate_Year'])
                ,'last_EvnPS_disDate_Month' => returnValidHTMLString($response[0]['last_EvnPS_disDate_Month'])
                ,'last_EvnPS_disDate_Day' => returnValidHTMLString($response[0]['last_EvnPS_disDate_Day'])
                ,'last_EvnPS_disDate_Hour' => ($response[0]['last_EvnPS_disDate_Hour'] === 0) ? '00' : returnValidHTMLString($response[0]['last_EvnPS_disDate_Hour'])
                ,'last_EvnPS_disDate_Minute' => ($response[0]['last_EvnPS_disDate_Minute'] === 0) ? '00' : returnValidHTMLString($response[0]['last_EvnPS_disDate_Minute'])
                
                // Диагноз ребенка
                ,'new_Diag_Name' => returnValidHTMLString($response[0]['new_Diag_Name'])
                // Перевод в друго отделение
                ,'LpuSection_FullName' => returnValidHTMLString($response[0]['LpuSection_FullName'])
			);
            //print("</pre>");  
            // $evn_diag_ps_hosp_data[0]['Diag_Code']." ".$evn_diag_ps_hosp_data[0]['Diag_Name']
            /*$_tstr = $evn_diag_ps_admit_data[0]['Diag_Code'];
            $_str = trim($_tstr);
            var_dump($_str);
            
            $whitespaces = " ";

            if(!(trim($evn_diag_ps_admit_data[0]['Diag_Code']))) {
                echo "Строка содержит только пробелы";
            } else print "Нормальная строка"; 
            if ($_str == '') {
                echo 'true';
            } else {
                echo 'false';
            }
            if ((isset($print_data['PrimaryRecepDiag_Name'])) && (trim($print_data['PrimaryRecepDiag_Name']) == '')) {
                $print_data['PrimaryRecepDiag_Name'] = '13';
            }
            else {
                $print_data['PrimaryRecepDiag_Name'] = '131';
            }*/
            //$whitespaces = " ";
            //var_dump($whitespaces);
            
            //print("<pre>");
            //print_r($print_data);
            //print("</pre>");
            
            //print_r('EvnPS_disDate '.$response[0]['EvnPS_disDate']);
            //print_r('EvnPS_setDate_Hour '.$response[0]['EvnPS_setDate_Hour']);
            //print_r('EvnPS_setDate_Minute '.$response[0]['EvnPS_setDate_Minute']);
            $my_time = explode(':', $response[0]['EvnPS_setTime']);
            if ((isset($my_time)) && (is_array($my_time))){
                $print_data['EvnPS_setDate_Hour'] = $my_time[0];
                $print_data['EvnPS_setDate_Minute'] = isset($my_time[1]) ? $my_time[1] : '';
            }
            $my_time = explode(':', $response[0]['EvnPS_disTime']);
            if ((isset($my_time)) && (is_array($my_time))){
                $print_data['EvnPS_disDate_Hour'] = $my_time[0];
                $print_data['EvnPS_disDate_Minute'] = isset($my_time[1]) ? $my_time[1] : '';
            }
            $my_time = explode(':', $response[0]['last_EvnPS_disTime']);
            if ((isset($my_time)) && (is_array($my_time))){
                $print_data['last_EvnPS_disDate_Hour'] = $my_time[0];
                $print_data['last_EvnPS_disDate_Minute'] = isset($my_time[1]) ? $my_time[1] : '';
            }
            
            // Диагноз при выписке ( диагноз последнего движения )
            if (!empty($response_eslast_diag)) {
                //print_r($response_day_stac[0]['LpuUnitType_Code']);
                $print_data['Leave_Diag_FullName'] = $response_eslast_diag[0]['Diag_FullName'];
            } else {
                $print_data['Leave_Diag_FullName'] = 0;
            }
            
            // Дневной стационар
            if (!empty($response_day_stac)) {
                //print_r($response_day_stac[0]['LpuUnitType_Code']);
                $print_data['LpuUnitType_Code'] = $response_day_stac[0]['LpuUnitType_Code'];
                $print_data['ResultDesease_Code'] = $response_day_stac[0]['ResultDesease_Code'];
                $print_data['LeaveType_id'] = $response_day_stac[0]['LeaveType_id'];
            } else {
                $print_data['LpuUnitType_Code'] = 0;
                $print_data['ResultDesease_Code'] = 0;
                $print_data['LeaveType_id'] = 0;
            }
            
            // Данные матери новорожденного
            if (!empty($response_mother_data)) {
                //print_r($response_day_stac[0]['LpuUnitType_Code']);
                $print_data['Mother_Fio'] = $response_mother_data[0]['Mother_Fio'];
                $print_data['Mother_Age'] = $response_mother_data[0]['Mother_Age'];
                $print_data['Mother_IsMarried'] = $response_mother_data[0]['Mother_IsMarried'];
                $print_data['Mother_FamilyStatus_id'] = $response_mother_data[0]['Mother_FamilyStatus_id'];
                $print_data['Mother_UAddress'] = $response_mother_data[0]['Mother_UAddress'];
                $print_data['Mother_PAddress'] = $response_mother_data[0]['Mother_PAddress'];  // TODO: что делать с адресом проживания, если его нет
                $print_data['Mother_Ward_Name'] = $response_mother_data[0]['Mother_Ward_Name'];
                $print_data['Mother_CountPregnancy'] = $response_mother_data[0]['Mother_CountPregnancy'];
                $print_data['Mother_CountBirth'] = $response_mother_data[0]['Mother_CountBirth'];
                $print_data['Mother_Post_Name'] = $response_mother_data[0]['Mother_Post_Name'];
                /*$print_data['ResultDesease_Code'] = $response_day_stac[0]['ResultDesease_Code'];
                $print_data['LeaveType_id'] = $response_day_stac[0]['LeaveType_id'];*/
            }
            
            /*var_dump($response[0]['BirthSpecStac_OutcomDT']);
            var_dump($response[0]['BirthSpecStac_OutcomDT_h']);
            var_dump($response[0]['BirthSpecStac_OutcomDT_m']);*/
            //var_dump(explode(':', $response[0]['BirthSpecStac_OutcomDT']));
            //print($print_data['EvnPS_NumCard']);
            $print_data['child_measure'] = array();
            $print_data['child_measure'] = $widht_height;
            
            $print_data['diag_sec'] = array();
            //$diagi = 0;
            foreach ($response_diag_sect as $diag) {
                $print_data['diag_sec'][] = array(//[]'123','qwer');
                    'Diag_Code' => returnValidHTMLString($diag['Diag_Code']),
                    'Diag_Name' => returnValidHTMLString($diag['Diag_Name'])
                );
                //var_dump($print_data['diag_sec']);
            }
            
            $print_data['Operation_Agg'] = array();
            //$diagi = 0;
            foreach ($response_agg as $agg) {
                $print_data['Operation_Agg'][] = array(//[]'123','qwer');
                    'AggType_Code' => returnValidHTMLString($agg['AggType_Code']),
                    'AggType_Name' => returnValidHTMLString($agg['AggType_Name'])
                );
                //var_dump($print_data['Operation_Agg']);
            }
            
            
            $print_data['Operations'] = array();
            //$diagi = 0;
            foreach ($response_oper as $operation) {
                $print_data['Operations'][] = array(//[]'123','qwer');
                    'UslugaComplex_Code' => returnValidHTMLString($operation['UslugaComplex_Code']),
                    'UslugaComplex_Name' => returnValidHTMLString($operation['UslugaComplex_Name']),
                    'EvnUsluga_Date' => returnValidHTMLString($operation['EvnUsluga_Date'])
                );
                //var_dump($print_data['Operations']);
            }
            
            
            $print_data['child_1'] = "";
            $print_data['child_2'] = ""; //Person_cid

            $print_data['PersonHeight_Height_1'] = "";
            $print_data['PersonHeight_Height_2'] = "";
            
            $print_data['PersonWeight_Weight_1'] = "";
            $print_data['PersonWeight_Weight_2'] = "";
            
            $print_data['Sex_name_1'] = "";
            $print_data['Sex_name_2'] = "";
            
            
            $print_data['child_Person_Bday_1'] = "";
            $print_data['child_Person_Bday_2'] = "";
            
            
            $chld = 1;    
            if (!empty($response_death_child)) {   
                
                foreach ($response_death_child as $child) {  
                    $chld = empty($child['ChildDeath_Count']) ? 1 : returnValidHTMLString($child['ChildDeath_Count']);
                    //print($chld."<br>");
                    $print_data['alive_'.$chld] = false;
                    
                    $print_data['child_'.$chld] = empty($child['Person_id']) ? "" : returnValidHTMLString($child['Person_id']);
                    
                    $print_data['PersonHeight_Height_'.$chld] = empty($child['ChildDeath_Height']) ? "" : returnValidHTMLString($child['ChildDeath_Height']);

                    $print_data['PersonWeight_Weight_'.$chld] = empty($child['ChildDeath_Weight']) ? "" : returnValidHTMLString($child['ChildDeath_Weight']);

                    $print_data['Sex_name_'.$chld] = empty($child['Sex_Name']) ? "" : returnValidHTMLString($child['Sex_Name']);

                    $print_data['child_Person_Bday_'.$chld] = empty($child['ChildDeath_insDT']) ? "" : returnValidHTMLString($child['ChildDeath_insDT']);
                    //print_r($print_data['PersonHeight_Height_1']);
                    $chld++;
                }
            }    
            
            if (!empty($response_child)) {    
                foreach ($response_child as $child) {
                    $print_data['alive_'.$chld] = true;
                    
                    $print_data['child_'.$chld] = empty($child['Person_id']) ? "" : returnValidHTMLString($child['Person_id']);
                    
                    $print_data['PersonHeight_Height_'.$chld] = empty($child['PersonHeight_Height']) ? "" : returnValidHTMLString($child['PersonHeight_Height']);

                    $print_data['PersonWeight_Weight_'.$chld] = empty($child['PersonWeight_Weight']) ? "" : returnValidHTMLString($child['PersonWeight_Weight']);

                    $print_data['Sex_name_'.$chld] = empty($child['Sex_name']) ? "" : returnValidHTMLString($child['Sex_name']);

                    $print_data['child_Person_Bday_'.$chld] = empty($child['child_Person_Bday']) ? "" : returnValidHTMLString($child['child_Person_Bday']);
                    $chld++;
                }
                
                
                
            } 
		}
        
        
        
		/*if ($data['Parent_Code'] == '1')
		{
		//Форма «Карта выбывшего из стационара» по кнопке Печать кнопок управления формой
			if ($evn_section_data[0]['EvnSection_id'] == '')
			{
				$template = 'evn_ps_template_list_a4_first';
			}
		}

		if ($data['Parent_Code'] == '5')
		{
		//Форма «Карта выбывшего из стационара», в панели управления списком Движений в форме «Карта выбывшего из стационара»
			{

			}

		}*/

		return $this->parser->parse($template, $print_data);
	}


	/**
	 * Печать КВС
	 */
	function printEvnPSHakasiya($data, $response) {
		$invalid_type_name = '';
		$template = 'evn_ps_template_list_a4_hakasiya';

		$evn_section_data = array();
		$evn_usluga_oper_data = array();

		$response_temp = $this->samara_dbmodel->getEvnSectionData($data);

		if ( is_array($response_temp) ) {
			$evn_section_data = $response_temp;

			for ( $i = 0; $i < (count($evn_section_data) < 2 ? 2 : count($evn_section_data)); $i++ ) {
				if ( $i >= count($evn_section_data) ) {
					$evn_section_data[$i] = array(
						'Index' => $i + 1,
						'LpuSection_Name' => '&nbsp;',
						'EvnSection_setDT' => '&nbsp;',
						'EvnSection_disDT' => '&nbsp;',
						'EvnSectionDiagOsn_Code' => '&nbsp;',
						'EvnSectionMesOsn_Code' => '&nbsp;',
						'EvnSection_UKL' => '&nbsp;',
						'EvnSectionPayType_Name' => '&nbsp;',
						'LpuSectionBedProfile_Name' => '&nbsp;',
						'MedPersonal_Code' => '&nbsp;'
					);
				} else {
					$evn_section_data[$i]['Index'] = $i + 1;
					if(!empty($evn_section_data[$i]['PayType_Name'])) { $evn_section_data[$i]['EvnSectionPayType_Name'] = $evn_section_data[$i]['PayType_Name']; }
				}
			}
		}

		$response_temp = $this->samara_dbmodel->getEvnUslugaOperData($data);

		if ( is_array($response_temp) ) {
			for ( $i = 0; $i < count($response_temp); $i++ ) {
				$evn_usluga_oper_data[] = array(
					'EvnUslugaOper_setDT' => $response_temp[$i]['EvnUslugaOper_setDT'],
					'EvnUslugaOperMedPersonal_Code' => $response_temp[$i]['MedPersonal_Code'],
					'EvnUslugaOperLpuSection_Code' => $response_temp[$i]['LpuSection_Code'],
					'EvnUslugaOper_Name' => $response_temp[$i]['Usluga_Name'],
					'EvnUslugaOper_Code' => $response_temp[$i]['Usluga_Code'],
					'EvnUslugaOperAnesthesiaClass_Name' => $response_temp[$i]['AnesthesiaClass_Name'],
					'EvnUslugaOper_IsEndoskop' => $response_temp[$i]['EvnUslugaOper_IsEndoskop'] == 1 ? 'X' : '&nbsp;',
					'EvnUslugaOper_IsLazer' => $response_temp[$i]['EvnUslugaOper_IsLazer'] == 1 ? 'X' : '&nbsp;',
					'EvnUslugaOper_IsKriogen' => $response_temp[$i]['EvnUslugaOper_IsKriogen'] == 1 ? 'X' : '&nbsp;',
					'EvnUslugaOperPayType_Name' => $response_temp[$i]['PayType_Name']
				);
			}

			// https://redmine.swan.perm.ru/issues/6484
			// savage: Добавляем пустые строки в таблицу с хирургическими операциями, если количество операций меньше двух
			for ( $j = $i; $j < 3; $j++ ) {
				$evn_usluga_oper_data[] = array(
					'EvnUslugaOper_setDT' => '&nbsp;<br />&nbsp;',
					'EvnUslugaOperMedPersonal_Code' => '&nbsp;<br />&nbsp;',
					'EvnUslugaOperLpuSection_Code' => '&nbsp;<br />&nbsp;',
					'EvnUslugaOper_Name' => '&nbsp;<br />&nbsp;',
					'EvnUslugaOper_Code' => '&nbsp;<br />&nbsp;',
					'EvnUslugaOperAnesthesiaClass_Name' => '&nbsp;<br />&nbsp;',
					'EvnUslugaOper_IsEndoskop' => '&nbsp;',
					'EvnUslugaOper_IsLazer' => '&nbsp;',
					'EvnUslugaOper_IsKriogen' => '&nbsp;',
					'EvnUslugaOperPayType_Name' => '&nbsp;<br />&nbsp;'
				);
			}
		}

		switch ( $response[0]['PrivilegeType_id'] ) {
			case 81:
				$invalid_type_name = "3-я группа";
			break;

			case 82:
				$invalid_type_name = "2-я группа";
			break;

			case 83:
				$invalid_type_name = "1-я группа";
			break;
		}

		$print_data = array(
			 'EvnPSTemplateTitle' => 'Печать карты выбывшего из стационара'
			,'EvnPS_NumCard' => returnValidHTMLString($response[0]['EvnPS_NumCard'])
			,'PolisType_Name' => returnValidHTMLString($response[0]['PolisType_Name'])
			,'Polis_Num' => returnValidHTMLString($response[0]['Polis_Num'])
			,'Polis_Ser' => returnValidHTMLString($response[0]['Polis_Ser'])
			,'OMSSprTerr_Code' => returnValidHTMLString($response[0]['OMSSprTerr_Code'])
			,'OrgSmo_Name' => returnValidHTMLString($response[0]['OrgSmo_Name'])
			,'Person_Fio' => returnValidHTMLString($response[0]['Person_Fio'])
			,'Person_OKATO' => returnValidHTMLString($response[0]['Person_OKATO'])
			,'Sex_Name' => returnValidHTMLString($response[0]['Sex_Name'])
			,'Person_Birthday' => returnValidHTMLString($response[0]['Person_Birthday'])
			,'Person_Age' => returnValidHTMLString($response[0]['Person_Age'])
			,'DocumentType_Name' => returnValidHTMLString($response[0]['DocumentType_Name'])
			,'Document_Ser' => returnValidHTMLString($response[0]['Document_Ser'])
			,'Document_Num' => returnValidHTMLString($response[0]['Document_Num'])
			,'KLAreaType_Name' => returnValidHTMLString($response[0]['KLAreaType_Name'])
			,'KLAreaType_id' => returnValidHTMLString($response[0]['KLAreaType_id'])
			,'Person_Phone' => returnValidHTMLString($response[0]['Person_Phone'])
			,'PAddress_Name' => returnValidHTMLString($response[0]['PAddress_Name'])
			,'UAddress_Name' => returnValidHTMLString($response[0]['UAddress_Name'])
			,'PayType_Name' => returnValidHTMLString($response[0]['PayType_Name'])
			,'SocStatus_Name' => returnValidHTMLString($response[0]['SocStatus_Name'])
			,'InvalidType_Name' => returnValidHTMLString($invalid_type_name)
			,'PrehospOrg_Name' => returnValidHTMLString($response[0]['PrehospOrg_Name'])
			,'PrehospArrive_Name' => returnValidHTMLString($response[0]['PrehospArrive_Name'])
			,'PersonCard_Code' => returnValidHTMLString($response[0]['PersonCard_Code'])
			,'Lpu_Name' => returnValidHTMLString($response[0]['Lpu_Name'])
			,'PrehospDiag_Name' => returnValidHTMLString($response[0]['PrehospDiag_Name'])
			,'AdmitDiag_Name' => returnValidHTMLString($response[0]['AdmitDiag_Name'])
			,'PrehospToxic_Name' => returnValidHTMLString($response[0]['PrehospToxic_Name'])
			,'PrehospType_Name' => returnValidHTMLString($response[0]['PrehospType_Name'])
			,'EvnPS_HospCount' => returnValidHTMLString($response[0]['EvnPS_HospCount'])
			,'EvnPS_TimeDesease' => returnValidHTMLString($response[0]['EvnPS_TimeDesease'])
			,'PrehospTrauma_Name' => returnValidHTMLString($response[0]['PrehospTrauma_Name'])
			,'EvnPS_setDate' => returnValidHTMLString($response[0]['EvnPS_setDate'])
			,'EvnPS_setTime' => returnValidHTMLString($response[0]['EvnPS_setTime'])
			,'LpuSectionFirst_Name' => returnValidHTMLString($response[0]['LpuSectionFirst_Name'])
			,'EvnSectionFirst_setDate' => returnValidHTMLString($response[0]['EvnSectionFirst_setDate'])
			,'EvnSectionFirst_setTime' => returnValidHTMLString($response[0]['EvnSectionFirst_setTime'])
			,'EvnPS_disDate' => returnValidHTMLString($response[0]['EvnPS_disDate'])
			,'EvnPS_disTime' => returnValidHTMLString($response[0]['EvnPS_disTime'])
			,'EvnPS_KoikoDni' => returnValidHTMLString($response[0]['EvnPS_KoikoDni'])
			,'LeaveType_Name' => returnValidHTMLString($response[0]['LeaveType_Name'])
			,'ResultDesease_Name' => returnValidHTMLString($response[0]['ResultDesease_Name'])
			,'EvnStick_setDate' => returnValidHTMLString($response[0]['EvnStick_setDate'])
			,'EvnStick_disDate' => returnValidHTMLString($response[0]['EvnStick_disDate'])
			,'PersonCare_Age' => returnValidHTMLString($response[0]['PersonCare_Age'])
			,'PersonCare_SexName' => returnValidHTMLString($response[0]['PersonCare_SexName'])
			,'EvnSectionData' => $evn_section_data
			,'EvnUslugaOperData' => $evn_usluga_oper_data
			,'LeaveDiag_Code' => returnValidHTMLString($response[0]['LeaveDiag_Code'])
			,'LeaveDiag_Name' => returnValidHTMLString($response[0]['LeaveDiag_Name'])
			,'LeaveDiagAgg_Code' => returnValidHTMLString($response[0]['LeaveDiagAgg_Code'])
			,'LeaveDiagAgg_Name' => returnValidHTMLString($response[0]['LeaveDiagAgg_Name'])
			,'LeaveDiagSop_Code' => returnValidHTMLString($response[0]['LeaveDiagSop_Code'])
			,'LeaveDiagSop_Name' => returnValidHTMLString($response[0]['LeaveDiagSop_Name'])
			,'AnatomDiag_Code' => returnValidHTMLString($response[0]['AnatomDiag_Code'])
			,'AnatomDiag_Name' => returnValidHTMLString($response[0]['AnatomDiag_Name'])
			,'AnatomDiagAgg_Code' => returnValidHTMLString($response[0]['AnatomDiagAgg_Code'])
			,'AnatomDiagAgg_Name' => returnValidHTMLString($response[0]['AnatomDiagAgg_Name'])
			,'AnatomDiagSop_Code' => returnValidHTMLString($response[0]['AnatomDiagSop_Code'])
			,'AnatomDiagSop_Name' => returnValidHTMLString($response[0]['AnatomDiagSop_Name'])
			,'EvnPS_IsDiagMismatch' => returnValidHTMLString($response[0]['EvnPS_IsDiagMismatch'])
			,'EvnPS_IsImperHosp' => returnValidHTMLString($response[0]['EvnPS_IsImperHosp'])
			,'EvnPS_IsShortVolume' => returnValidHTMLString($response[0]['EvnPS_IsShortVolume'])
			,'EvnPS_IsWrongCure' => returnValidHTMLString($response[0]['EvnPS_IsWrongCure'])
		);

		return $this->parser->parse($template, $print_data);
	}

	/**
	 * Получение количества госпитализаций
	 */
	public function getHospCount()
	{		
		$data = $this->ProcessInputData('getHospCount', true);
		if ( $data === false ) {
            return false;
        }    
    
		$response = $this->samara_dbmodel->getHospCount($data);
		$this->ReturnData($response);
	}	
    
	/**
	*  Получение данных для формы редактирования КВС
	*  Входящие данные: $_POST['EvnPS_id']
	*  На выходе: JSON-строка
	*  Используется: форма редактирования КВС
	*/
	function loadEvnPSEditForm() {
		$data = array();
		$val  = array();

		// Получаем сессионные переменные
		$data = $this->ProcessInputData('loadEvnPSEditForm', true);

		if ( $data === false ) {
			return false;
		}

		$response = $this->samara_dbmodel->loadEvnPSEditForm($data);

		if ( is_array($response) && count($response) > 0 ) {
			// Времянка, но временное, как известно, долговечнее постоянного (c) Night
			if ($response[0]['Lpu_id'] != $data['Lpu_id'])
			{
				$response[0]['action'] = 'view';
			}
			$val = $response;
			array_walk($val[0], 'ConvertFromWin1251ToUTF8');
		}

		$this->ReturnData($val);

		return true;
	}

	/**
	 * Сохранение КВС
	 */
	function saveEvnPS() {
		$this->load->helper('Options');
		$this->load->model('Common_model', 'commonmodel');
		$this->load->model('EvnSection_model', 'EvnSection');
		$this->load->model("Org_model", "orgmodel");
		
		$alert_msg = '';
		$omsSprTerrCode = 0;

		$data = $this->ProcessInputData('saveEvnPS', true, true);
		if ( $data === false ) { return false; }

        //print_r('TimeDeseaseType_id'. $data['TimeDeseaseType_id']);
		//print_r($data); exit();

		$is_perm = ($data['session']['region']['nick'] == 'perm');
		$isSamara = ($data['session']['region']['nick'] == 'samara');
		$isUfa = ($data['session']['region']['nick'] == 'ufa');

		// Получение данных по пациенту
		$response = $this->commonmodel->loadPersonDataShort($data);

		if ( is_array($response) ) {
			if ( count($response) > 0 && array_key_exists('OmsSprTerr_Code', $response[0]) ) {
				$omsSprTerrCode = $response[0]['OmsSprTerr_Code'];
			}
			else {
				$this->ReturnError('Ошибка при получении данных пациента');
				return false;
			}
		}
		else {
			$this->ReturnError('Ошибка при получении данных пациента');
			return false;
		}


		// https://redmine.swan.perm.ru/issues/4614
		// Проверка заполнения хотя бы одного из диагнозов - направившего учреждения или приемного отделения
		if ( !empty($data['PrehospDirect_id']) ) {
			if ( empty($data['Diag_did']) && $is_perm == true && $omsSprTerrCode > 100 ) {
				echo json_return_errors('При заполненном поле "Кем направлен" диагноз направившего учреждения обязателен для заполнения');
				return false;
			}

			if ( $data['PrehospDirect_id'] == 2 ) {
				$data['Lpu_did'] = $data['Org_did'];
				$response = $this->orgmodel->getLpuData(array('Org_id'=>$data['Org_did']));
				if (!empty($response[0]) && !empty($response[0]['Lpu_id'])) {
					$data['Lpu_did'] = $response[0]['Lpu_id'];
				}
				$data['Org_did'] = NULL;
			}
		}

		if ( !empty($data['LpuSection_pid']) && empty($data['Diag_pid']) && !$isUfa && !$isSamara) {
			$this->ReturnError('При выбранном приемном отделении поле "Основной диагноз приемного отделения" обязательно для заполнения');
			return false;
		}

		// По задаче #5536 + #6270
		$data['LpuUnitType_id'] = $this->samara_dbmodel->getLpuUnitTypeFromFirstEvnSection($data);
		//print_r($data);
	
		// Серверная проверка (refs #8881)
		$dataFromLastEvnSection = $this->samara_dbmodel->getDataFromLastEvnSection($data);
		$payTypeSysNick = $this->samara_dbmodel->getPayTypeSysNick($data);

		if (!empty($dataFromLastEvnSection)) {
			if ((strtotime($dataFromLastEvnSection['EvnSection_DisDate']) > strtotime('31.03.2012')) && //дата окончания движениями позднее 31.03.2012
				($dataFromLastEvnSection['LpuUnitType_id'] == 1) && // тип группы отделения в последнем движении
				($data['PrehospType_id'] == 2) && // планово
				($data['EvnPS_IsCont'] == 1) && // переведен = нет
				($payTypeSysNick == 'oms') && // <Вид оплаты> = ОМС
				(!$isSamara) && // кроме Самары
				(!$isUfa) && // кроме Уфы
				(empty($data['EvnDirection_Num']) || empty($data['EvnDirection_setDate']) || ($data['PrehospDirect_id'] != 1 && $data['PrehospDirect_id'] !=2))) {
				$this->ReturnError('При плановой госпитализации в круглосуточный стационар с видом оплаты ОМС и без перевода, начиная с 01.04.2012 поля <Номер направления> и <Дата направления> - обязательны к заполнению, поле <Кем направлен> может принимать значение "Другое ЛПУ" или "Отделение ЛПУ"');
				return false;
			}
		}
		
		if ( $data['Lpu_id'] == 0 || !isset($data['Server_id']) || $data['Server_id'] < 0 ) {
			$this->ReturnData(array('success' => false, 'Error_Msg' => toUTF('Невозможно выполнить запрос на сохранение КВС, так как не определена принадлежность пользователя к ЛПУ')));
			return true;
		}

		// Если не совпадают дата выписки в одном из движений и дата госпитализации в последующем движении, то сохранение отменять и
		// выводить сообщение
		$responseCheckEvnSectionDates = $this->samara_dbmodel->checkEvnSectionDates($data);

		if ( !is_array($responseCheckEvnSectionDates) ) {
			$this->ReturnData(array('success' => false, 'Error_Msg' => toUTF('Ошибка при проверке непрерывности движения по отделениям')));
			return false;
		}

		foreach ( $responseCheckEvnSectionDates as $checkResult ) {
			if ( is_array($checkResult) && array_key_exists('disDateIsIncorrect', $checkResult) && $checkResult['disDateIsIncorrect'] == 1 ) {
				$this->ReturnData(array('success' => false, 'Error_Msg' => toUTF('Сохранение отменено, т.к. не совпадают дата выписки в одном из движений и дата госпитализации в последующем движении.')));
				return false;
			}
		}

        //if ( $isSamara === true && !empty($data['EvnPS_id']) )  {
        //    // Проверяем наличие хотя бы одной услуги по каждому движению
        //    // В ответе содержится список движений с указанием количества услуг, соответствующих этому движению
        //    $responseCheckEvnUslugaConformity = $this->samara_dbmodel->checkEvnUslugaConformity($data);

        //    foreach ( $responseCheckEvnUslugaConformity as $rec ) {
        //        if ( empty($rec['evnUslugaCount']) ) {
        //            $this->ReturnError('Движениям должна соответствовать хотя бы одна услуга! Проверьте движение от ' . $rec['EvnSection_setDate'] . ', отделение "' . $rec['LpuSection_Name'] . '"');
        //            return false;
        //        }
        //    }
        //}

		if ( $is_perm === false ) {
			// Проверка КВС на дубли по номеру
			$response = $this->samara_dbmodel->checkEvnPSDoublesByNum($data);

			if ( !is_array($response) || count($response) == 0 ) {
				$this->ReturnData(array('success' => false, 'Error_Msg' => toUTF('Ошибка при проверке дублей карты по номеру')));
				return false;
			}

			if ( $response[0]['cnt'] > 0 ) {
				$this->ReturnData(array('success' => false, 'Error_Code' => 1, 'Error_Msg' => toUTF('Указанный номер карты уже используется')));
				return true;
			}

			// Проверка КВС на пересечение по дате госпитализации с другими стационарными случаями
			$response = $this->samara_dbmodel->checkEvnPSDoubles($data);

			if ( !is_array($response) ) {
				$this->ReturnData(array('success' => false, 'Error_Code' => 1, 'Error_Msg' => toUTF('Ошибка при проверке дублирования случаев пребывания пациента в стационаре')));
				return true;
			}

			if ( count($response) > 0 ) {
				$ext_count = 0;
				$int_count = 0;

				foreach ( $response as $double ) {
					if ( $response[0]['ext_count'] > 0 ) {
						$ext_count += $response[0]['ext_count'];
					}

					if ( $response[0]['int_count'] > 0 ) {
						$int_count += $response[0]['int_count'];
					}
				}

				$alert_msg .= '<div>Внимание!</div>';
				$alert_msg .= '<div>Имеется пересечение по дате госпитализации с другими стационарными случаями</div>';
				$alert_msg .= '<div>Случаев пересечения внутри ЛПУ: ' . $int_count . '</div>';
				$alert_msg .= '<div>Случаев пересечения с другими ЛПУ: ' . $ext_count . '</div>';

				foreach ( $response as $double ) {
					if ( $response[0]['ext_count'] > 0 ) {
						$alert_msg .= '<div>- ' . $response[0]['Lpu_Nick'] . '</div>';
					}
				}
			}
		}

		// Сохранение КВС
		$response = $this->samara_dbmodel->saveEvnPS($data);
		$outdata = $this->ProcessModelSave($response, true, 'Ошибка при сохранении карты выбывшего из стационара')->GetOutData();

		if ($outdata['success']) {
			/*
			* Создание пустого движения в том случае, если приходит флаг "addEvnSection"
			* Когда КВС добавляется из ЭМК по нажатию кнопки "добавить новый случай"
			* Когда КВС добавляется из АРМа стационара по нажатию кнопки "добавить пациента" (swEvnPSEditWindow.form_mode == 'arm_stac_add_patient')
			* Для Уфы когда КВС добавляется из Журнала госпитализаций (swEvnPSEditWindow.form_mode == 'dj_hosp')
			*/
			if (!empty($data['addEvnSection'])) {
				$evnSectionData = $data;
				$evnSectionData['EvnSection_id'] = null;
				$evnSectionData['EvnSection_pid'] = $outdata['EvnPS_id'];
				$evnSectionData['EvnSection_setDate'] = $data['EvnPS_setDate'];
				$evnSectionData['EvnSection_setTime'] = $data['EvnPS_setTime'];
				$evnSectionData['EvnSection_IsAdultEscort'] = 1;
				$evnSectionData['EvnSection_disDate'] = null;
				$evnSectionData['Diag_id'] = null;
				$evnSectionData['Mes_id'] = null;
				$evnSectionData['TariffClass_id'] = null;
				$evnSectionData['LpuSectionWard_id'] = null;

				$response = $this->EvnSection->saveEvnSection($evnSectionData);
			}
			
			// КВС сохранена и теперь, если запись выполнена из АРМ приемного, надо связать ее с биркой 
			if ((isset($data['TimetableStac_id'])) && ($data['TimetableStac_id']>0)) {
				$data['Evn_id'] = $outdata['EvnPS_id'];
				$r = $this->samara_dbmodel->saveEvnPSTimetableStac($data);
				/*
				if ( is_array($r) && count($r) > 0 ) {
					if ( strlen($r[0]['Error_Msg']) > 0 ) {
						// TODO: Ошибка при сохранении бирки - надо ли ее выводить
					}
				}*/
			}
			
			/*
			Для АРМ приемного
			*/
			if ( $data['from'] == 'workplacepriem' )
			{
				// КВС создана и теперь нужно обновить запись в очереди
				if (empty($data['EvnPS_id']) && !empty($data['EvnQueue_id'])) {
					$r = $this->samara_dbmodel->saveEvnPSFromQueue($data);
					/*
					if ( is_array($r) && count($r) > 0 ) {
						if ( strlen($r[0]['Error_Msg']) > 0 ) {
							// TODO: Ошибка при обновлении записи в очереди - надо ли ее выводить
						}
					}*/
				}
				$data['EvnPS_id'] = (empty($data['EvnPS_id']))?$outdata['EvnPS_id']:$data['EvnPS_id'];
				// Получаем данные о первом движении
				$this->load->model('EvnSection_model', 'EvnSection');
				$evnsection_data = $this->EvnSection->getEvnSectionFirst($data);
				if ( is_array($evnsection_data) )
				{
					//если выбрано отделение, то создавать движение на данное отделение с текущими датой/временем поступления.
					if( count($evnsection_data) == 0 AND !empty($data['LpuSection_eid']) )
					{
						$data['EvnSection_pid'] = $data['EvnPS_id'];
						$data['EvnSection_setDate'] = $data['EvnPS_OutcomeDate'];
						$data['EvnSection_setTime'] = $data['EvnPS_OutcomeTime'];
						$data['LpuSection_id'] = $data['LpuSection_eid'];
						$response = $this->EvnSection->saveEvnSectionInHosp($data);
					}
					//При редактировании, если отделение перевыбрано, то менять отделение
					if( count($evnsection_data) == 1 AND !empty($data['LpuSection_eid']) AND $data['LpuSection_eid'] != $evnsection_data[0]['LpuSection_id'])
					{
						$evnsection_data[0]['LpuSection_id'] = $data['LpuSection_eid'];
						$response = $this->EvnSection->setEvnSectionLpuSection($evnsection_data[0]);
					}
					//При редактировании, если отделение установлено на пустое значение, то удалять движение.
					if( count($evnsection_data) == 1 AND empty($data['LpuSection_eid']) )
					{
						$data['EvnSection_id'] = $evnsection_data[0]['EvnSection_id'];
						$response = $this->EvnSection->deleteEvnSectionInHosp($data);
					}
				}
			}

			if ($outdata['EvnPS_id'] && $this->samara_dbmodel->hasEvnSectionWithOtherPayType($outdata['EvnPS_id'], $data['PayType_id'])) {
				$alert_msg .= '<div>Случай содержит движения в отделении с другим видом оплаты.</div><div>Пожалуйста, проверьте правильность вида оплаты в отделениях.</div>';
			}
			
			if ( strlen($alert_msg) > 0 ) {
				ConvertFromWin1251ToUTF8($alert_msg);
				$outdata['Alert_Msg'] = $alert_msg;
			}
			
			// Уведомление отсылать нужно только когда добавляем КВС и когда есть направление на госпитализацию
			if( empty($data['EvnPS_id']) && $data['EvnDirection_id'] > 0 ) {
				// Получим необходимые данные для уведомления
				$this->load->model('EvnDirection_model', 'dir_model');
				$ndata = $this->dir_model->getDirectionDataForNotice($data);
				if( is_array($ndata) ) {					
					$text = 'Направленный вами пациент ' .$ndata['Person_Fio']. ' в ' .$ndata['Lpu_Nick']. ' по профилю ' .$ndata['LpuSectionProfile_Name'];
					$text .= ' госпитализирован ' .$ndata['EvnPS_setDT']. ' в ' . $ndata['Lpu_H_Nick'] . ' ' .$ndata['LpuSection_H_FullName'];
					
					$noticeData = array(
						'autotype' => 1
						,'Lpu_rid' => $data['Lpu_id']
						,'pmUser_id' => $data['pmUser_id']
						,'MedPersonal_rid' => $ndata['MedPersonal_id']
						,'type' => 1
						,'title' => 'Госпитализация по направлению'
						,'text' => $text
					);
					$this->load->model('Messages_model', 'Messages_model');
					$this->Messages_model->autoMessage($noticeData);
				}
			}
		}

		$this->ReturnData($outdata);

		return true;
	}    
    
}
