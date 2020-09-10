<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * EvnHistologicProto - контроллер для работы с протоколами патологогистологического исследования
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Polka
 * @access       public
 * @copyright    Copyright (c) 2009-2011 Swan Ltd.
 * @author       SWAN developers
 * @version      31.01.2011
 */
 
class EvnHistologicProto extends swController {
	public $inputRules = array(
		'deleteEvnHistologicProto' => array(
			array(
				'field' => 'EvnHistologicProto_id',
				'label' => 'Идентификатор протокола патологогистологического исследования',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'loadEvnHistologicProtoList' => array(
			array('field' => 'Person_id', 'label' => 'Идентификатор пациента', 'rules' => 'required', 'type' => 'id'),
		),
		'loadEvnHistologicProtoGrid' => array(
			array(
				'field' => 'EvnType_id',
				'label' => 'Состояние протокола',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Person_Firname',
				'label' => 'Имя',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'Person_Secname',
				'label' => 'Отчество',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'Person_Surname',
				'label' => 'Фамилия',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'didRangeStart',
				'label' => 'Дата иследования С',
				'rules' => 'trim',
				'type' => 'date'
			),
			array(
				'field' => 'didRangeEnd',
				'label' => 'Дата иследования По',
				'rules' => 'trim',
				'type' => 'date'
			),
			array(
				'field' => 'setRangeStart',
				'label' => 'Дата поступления материала С',
				'rules' => 'trim|required',
				'type' => 'date'
			),
			array(
				'field' => 'setRangeEnd',
				'label' => 'Дата поступления материала По',
				'rules' => 'trim|required',
				'type' => 'date'
			),
			array(
				'field' => 'minAge',
				'label' => 'Возраст С',
				'rules' => 'trim',
				'type' => 'int'
			),
			array(
				'field' => 'maxAge',
				'label' => 'Возраст По',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'PT_Diag_Code_From',
				'label' => 'Диагноз С',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'PT_Diag_Code_To',
				'label' => 'Диагноз По',
				'rules' => 'trim',
				'type' => 'string'
			),
			// Параметры страничного вывода
			array(
				'default' => 0,
				'field' => 'start',
				'label' => 'Номер стартовой записи',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'default' => 100,
				'field' => 'limit',
				'label' => 'Количество записей',
				'rules' => '',
				'type' => 'id'
			)
		),
		'saveEvnHistologicProto' => array(
			array(
				'field' => 'Diag_id',
				'label' => 'Диагноз',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnDirectionHistologic_id',
				'label' => 'Идентификатор направления на патологогистологическое исследование',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'EvnHistologicProto_BitCount',
				'label' => 'Количество кусочков',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'EvnHistologicProto_BlockCount',
				'label' => 'Количество блоков',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'EvnHistologicProto_Comments',
				'label' => 'Комментарии к заключению и рекомендации',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'EvnHistologicProto_CutDate',
				'label' => 'Дата вырезки',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'EvnHistologicProto_CutTime',
				'label' => 'Время вырезки',
				'rules' => '',
				'type' => 'time'
			),
			array(
				'field' => 'EvnHistologicProtoBiopsy_setDate',
				'label' => 'Дата регистрации биопсийного (операционного материала)',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'EvnHistologicProtoBiopsy_setTime',
				'label' => 'Время регистрации биопсийного (операционного материала)',
				'rules' => '',
				'type' => 'time'
			),
			array(
				'field' => 'EvnHistologicProto_didDate',
				'label' => 'Дата исследования',
				'rules' => 'trim',
				'type' => 'date'
			),
			array(
				'field' => 'EvnHistologicProto_HistologicConclusion',
				'label' => 'Патологогистологическое заключение (диагноз)',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'EvnHistologicProto_id',
				'label' => 'Идентификатор протокола патологогистологического исследования',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnHistologicProto_IsBad',
				'label' => 'Признак испорченного протокола',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnHistologicProto_IsDelivSolFormalin',
				'label' => 'Материал доставлен в 10%-ном растворе нейтрального формалина',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnHistologicProto_IsPolluted',
				'label' => 'Загрязнен',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnHistologicProto_IsDiag',
				'label' => 'Биопсия диагностическая',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'EvnHistologicProto_IsOper',
				'label' => 'Операционный материал',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'EvnHistologicProto_MacroDescr',
				'label' => 'Макроскопическое описание',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'EvnHistologicProto_Num',
				'label' => 'Номер протокола',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'EvnHistologicProto_Ser',
				'label' => 'Серия протокола',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'EvnHistologicProto_setDate',
				'label' => 'Дата поступления материала',
				'rules' => 'trim|required',
				'type' => 'date'
			),
			array(
				'field' => 'EvnHistologicProto_setTime',
				'label' => 'Время поступления материала',
				'rules' => 'trim',
				'type' => 'time'
			),
			array(
				'field' => 'EvnHistologicProto_CategoryDiff',
				'label' => 'Категория сложности',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'MarkSavePack_id',
				'label' => 'Отметка о сохранности упаковки',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'MedPersonal_id',
				'label' => 'Патологоанатом',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'MedPersonal_sid',
				'label' => 'Лаборант',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'MedStaffFact_id',
				'label' => 'Врач-специалист, осуществляющий консультирование',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'OnkoDiag_id',
				'label' => 'Морфологический код МКБ-О',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор человека',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'PersonEvn_id',
				'label' => 'Идентификатор состояния человека',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'PrescrReactionType_ids',
				'label' => 'Назначенные окраски (реакции, определения)',
				'rules' => '',
				'type' => 'multipleid'
			),
			array(
				'field' => 'Server_id',
				'label' => 'Идентификатор сервера',
				'rules' => 'required',
				'type' => 'int'
			)
		),
		'setEvnHistologicProtoIsBad' => array(
			array(
				'field' => 'EvnHistologicProto_id',
				'label' => 'Идентификатор протокола',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'EvnHistologicProto_IsBad',
				'label' => 'Признак испорченного протокола',
				'rules' => 'required',
				'type' => 'int'
			)
		),
		'loadEvnHistologicProtoEditForm' => array(
			array(
				'field' => 'EvnHistologicProto_id',
				'label' => 'Идентификатор протокола патологогистологического исследования',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'printEvnHistologicProto' => array(
			array(
				'field' => 'EvnHistologicProto_id',
				'label' => 'Идентификатор протокола патологогистологического исследования',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'getEvnHistologicProtoSerNum' => array(
			array('field' => 'generateNew','label' => 'Генерировать новый номер','rules' => '','type' => 'int')
		),
	);


	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();

		$this->load->database();
		$this->load->model('EvnHistologicProto_model', 'dbmodel');
	}


	/**
	*  Удаление протокола патологогистологического исследования
	*  Входящие данные: $_POST['EvnHistologicProto_id']
	*  На выходе: JSON-строка
	*  Используется: журнал протоколов патологогистологических исследований
	*/
	function deleteEvnHistologicProto() {
		$data = $this->ProcessInputData('deleteEvnHistologicProto', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->deleteEvnHistologicProto($data);
		$this->ProcessModelSave($response, true, 'При удалении протокола патологогистологического исследования возникли ошибки')->ReturnData();

		return true;
	}


	/**
	*  Получение номера протокола патологогистологического исследования
	*  Входящие данные: нет
	*  На выходе: JSON-строка
	*  Используется: форма редактирования протокола патологогистологического исследования
	*/
	function getEvnHistologicProtoNumber() {
		$data = getSessionParams();

		$response = $this->dbmodel->getEvnHistologicProtoNumber($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		
		return true;
	}

	/**
	 * Генерация серии и номера протокола патологогистологического исследования
	 * 
	 */
	function getEvnHistologicProtoSerNum($showOnly = true) {

		$val  = array();

		// Получаем сессионные переменные
		$data = $this->ProcessInputData('getEvnHistologicProtoSerNum', true);
		if ($data === false) { return false; }

		$data['showOnly'] = $showOnly;
		$numData = $this->dbmodel->getEvnHistologicProtoSerNum($data);
		if (!empty($numData['Error_Msg'])) {
			array_walk($val, 'ConvertFromWin1251ToUTF8');
			$this->ReturnData($numData);
			return true;
		}
		$val['num'] = $numData['Numerator_Num'];
		$val['intnum'] = $numData['Numerator_IntNum'];
		$val['prenum'] = $numData['Numerator_PreNum'];
		$val['postnum'] = $numData['Numerator_PostNum'];
		$val['ser'] = $numData['Numerator_Ser'];
		$val['success'] = true;

		array_walk($val, 'ConvertFromWin1251ToUTF8');
		$this->ReturnData($val);
	}


	/**
	*  Получение данных для формы редактирования протокола патологогистологического исследования
	*  Входящие данные: $_POST['EvnHistologicProto_id']
	*  На выходе: JSON-строка
	*  Используется: форма редактирования протокола патологогистологического исследования
	*/
	function loadEvnHistologicProtoEditForm() {
		$data = $this->ProcessInputData('loadEvnHistologicProtoEditForm', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->loadEvnHistologicProtoEditForm($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}


	/**
	*  Получение списка протоколов патологогистологических исследований
	*  Входящие данные: <фильтры>
	*  На выходе: JSON-строка
	*  Используется: журнал протоколов патологогистологических исследований
	*/
	function loadEvnHistologicProtoGrid() {
		$data = $this->ProcessInputData('loadEvnHistologicProtoGrid', true);
		if ( $data === false ) { return false; }
		$response = $this->dbmodel->loadEvnHistologicProtoGrid($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();

		return true;
	}


	/**
	*  Получение списка протоколов патологогистологических исследований пациента
	*  На выходе: JSON-строка
	*  Используется: форма редактирования патологогистологического направления
	*/
	public function loadEvnHistologicProtoList() {
		$data = $this->ProcessInputData('loadEvnHistologicProtoList', true);
		if ( $data === false ) { return false; }
		$response = $this->dbmodel->loadEvnHistologicProtoList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}


	/**
	*  Печать протокола патологогистологического исследования
	*  Входящие данные: $_GET['EvnHistologicProto_id']
	*  На выходе: форма для печати протокола патологогистологического исследования
	*  Используется: форма редактирования протокола патологогистологического исследования
	*                журнал протоколов патологогистологических исследований
	*/
	function printEvnHistologicProto() {
		$this->load->library('parser');

		$data = $this->ProcessInputData('printEvnHistologicProto', true);
		if ( $data === false ) { return false; }

		// Получаем данные по направлению
		$response = $this->dbmodel->getEvnHistologicProtoFields($data);

		if ( !is_array($response) || count($response) == 0 ) {
			echo 'Ошибка при получении данных по протоколу патологогистологического исследования';
			return true;
		}

        $arMonthOf = array(
            1 => "января",
            2 => "февраля",
            3 => "марта",
            4 => "апреля",
            5 => "мая",
            6 => "июня",
            7 => "июля",
            8 => "августа",
            9 => "сентября",
            10 => "октября",
            11 => "ноября",
            12 => "декабря",
        );
		$template = 'print_evn_histologic_proto';

		$print_data = array(
			'Diag_Code' => returnValidHTMLString($response[0]['Diag_Code']),
			'EvnHistologicProto_BitCount' => returnValidHTMLString($response[0]['EvnHistologicProto_BitCount']),
			'EvnHistologicProto_BlockCount' => returnValidHTMLString($response[0]['EvnHistologicProto_BlockCount']),
			'EvnHistologicProtoDescrList' => array(),
			'EvnHistologicProto_didDay' => returnValidHTMLString($response[0]['EvnHistologicProto_didDay']),
			'EvnHistologicProto_didMonth' => returnValidHTMLString(array_key_exists($response[0]['EvnHistologicProto_didMonth'], $arMonthOf) ? $arMonthOf[$response[0]['EvnHistologicProto_didMonth']] : ''),
			'EvnHistologicProto_didYear' => returnValidHTMLString($response[0]['EvnHistologicProto_didYear']),
			'EvnHistologicProtoHistologicConclusionList' => array(),
			'EvnHistologicProto_IsDiag' => returnValidHTMLString($response[0]['EvnHistologicProto_IsDiag']),
			'EvnHistologicProto_IsOper' => returnValidHTMLString($response[0]['EvnHistologicProto_IsOper']),
			'EvnHistologicProto_IsUrgent' => returnValidHTMLString($response[0]['EvnHistologicProto_IsUrgent']),
			'EvnHistologicProto_Num' => returnValidHTMLString($response[0]['EvnHistologicProto_Num']),
			'EvnHistologicProto_Ser' => returnValidHTMLString($response[0]['EvnHistologicProto_Ser']),
			'EvnHistologicProto_setDate' => returnValidHTMLString($response[0]['EvnHistologicProto_setDate']),
			'EvnHistologicProto_setTime' => returnValidHTMLString($response[0]['EvnHistologicProto_setTime']),
			'MedPersonal_Fio' => returnValidHTMLString($response[0]['MedPersonal_Fio']),
			'MedPersonalS_Fio' => returnValidHTMLString($response[0]['MedPersonalS_Fio'])
		);

		$description = preg_replace("/[ \n]+/u", ' ', $response[0]['EvnHistologicProto_MacroDescr']);
		$description_array = explode(' ', $description);
		$histologic_conclusion = preg_replace("/[ \n]+/u", ' ', $response[0]['EvnHistologicProto_HistologicConclusion']);
		$histologic_conclusion_array = explode(' ', $histologic_conclusion);

		if ( count($description_array) > 0 ) {
			$limit = 95;
			$tempStr = "";

			for ( $i = 0; $i < count($description_array); $i++ ) {
				if ( mb_strlen($tempStr . $description_array[$i] ) <= $limit ) {
					$tempStr .= $description_array[$i] . ' ';
				}
				else {
					$print_data['EvnHistologicProtoDescrList'][] = array('EvnHistologicProto_Descr' => returnValidHTMLString(trim($tempStr)));
					$tempStr = $description_array[$i] . ' ';
				}
			}

			$tempStr = trim($tempStr);

			if ( !empty($tempStr) ) {
				$print_data['EvnHistologicProtoDescrList'][] = array('EvnHistologicProto_Descr' => returnValidHTMLString($tempStr));
			}
		}

		if ( count($histologic_conclusion_array) > 0 ) {
			$limit = 95;
			$tempStr = "";

			for ( $i = 0; $i < count($histologic_conclusion_array); $i++ ) {
				if ( mb_strlen($tempStr . $histologic_conclusion_array[$i] ) <= $limit ) {
					$tempStr .= $histologic_conclusion_array[$i] . ' ';
				}
				else {
					$print_data['EvnHistologicProtoHistologicConclusionList'][] = array('EvnHistologicProto_HistologicConclusion' => returnValidHTMLString(trim($tempStr)));
					$tempStr = $histologic_conclusion_array[$i] . ' ';
				}
			}

			$tempStr = trim($tempStr);

			if ( !empty($tempStr) ) {
				$print_data['EvnHistologicProtoHistologicConclusionList'][] = array('EvnHistologicProto_HistologicConclusion' => returnValidHTMLString($tempStr));
			}
		}

		// Получаем микроскопические описания
		$response = $this->dbmodel->getEvnHistologicMicroData($data);

		if ( is_array($response) && count($response) > 0 ) {
			$description = "";

			foreach ( $response as $row ) {
				$description .= "Место забора материала: " . $row['HistologicSpecimenPlace_Name'] . ", ";
				$description .= "метод окраски: " . $row['HistologicSpecimenSaint_Name'] . ", ";
				$description .= "описание: " . $row['EvnHistologicMicro_Descr'] . ". ";
			}

			$description = preg_replace("/[ \n]+/u", ' ', $description);
			$description_array = explode(' ', $description);

			if ( count($description_array) > 0 ) {
				$limit = 95;
				$tempStr = "";

				for ( $i = 0; $i < count($description_array); $i++ ) {
					if ( mb_strlen($tempStr . $description_array[$i] ) <= $limit ) {
						$tempStr .= $description_array[$i] . ' ';
					}
					else {
						$print_data['EvnHistologicProtoDescrList'][] = array('EvnHistologicProto_Descr' => returnValidHTMLString(trim($tempStr)));
						$tempStr = $description_array[$i] . ' ';
					}
				}

				$tempStr = trim($tempStr);

				if ( !empty($tempStr) ) {
					$print_data['EvnHistologicProtoDescrList'][] = array('EvnHistologicProto_Descr' => returnValidHTMLString($tempStr));
				}
			}
		}

		if ( count($print_data['EvnHistologicProtoDescrList']) < 10 ) {
			for ( $i = count($print_data['EvnHistologicProtoDescrList']); $i <= 10; $i++ ) {
				$print_data['EvnHistologicProtoDescrList'][] = array('EvnHistologicProto_Descr' => '&nbsp;');
			}
		}

		if ( count($print_data['EvnHistologicProtoHistologicConclusionList']) < 4 ) {
			for ( $i = count($print_data['EvnHistologicProtoHistologicConclusionList']); $i <= 4; $i++ ) {
				$print_data['EvnHistologicProtoHistologicConclusionList'][] = array('EvnHistologicProto_HistologicConclusion' => '&nbsp;');
			}
		}

		return $this->parser->parse($template, $print_data);
	}


	/**
	*  Сохранение протокола патологогистологического исследования
	*  Входящие данные: <поля формы>
	*  На выходе: JSON-строка
	*  Используется: форма редактирования протокола патологогистологического исследования
	*/
	function saveEvnHistologicProto() {
		$data = $this->ProcessInputData('saveEvnHistologicProto', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->saveEvnHistologicProto($data);
		$this->ProcessModelSave($response, true, 'Ошибка при сохранении протокола патологогистологического исследования')->ReturnData();

		return true;
	}


	/**
	*  Установка/снятие признака испорченного протокола патологогистологического исследования
	*  Входящие данные: EvnDirectionHistologic_id, EvnDirectionHistologic_IsBad
	*  На выходе: JSON-строка
	*  Используется: журнал протоколов патологогистологическох исследований
	*/
	function setEvnHistologicProtoIsBad() {
		$data = $this->ProcessInputData('setEvnHistologicProtoIsBad', true);
		if ( $data === false ) { return false; }

		switch ( $data['EvnHistologicProto_IsBad'] ) {
			case 0:
				$data['EvnHistologicProto_IsBad'] = 1;
				$data['pmUser_pid'] = NULL;
			break;

			case 1:
				$data['EvnHistologicProto_IsBad'] = 2;
				$data['pmUser_pid'] = $data['pmUser_id'];
			break;

			default:
				echo json_return_errors('Неверное значение признака испорченного протокола патологогистологического исследования');
				return false;
			break;
		}

		$response = $this->dbmodel->setEvnHistologicProtoIsBad($data);

		if ( is_array($response) && count($response) > 0 ) {
			$val = $response;
		}
		else {
			$val = array('success' => false, 'Error_Msg' => 'Ошибка при установке/снятии признака испорченного протокола патологогистологического исследования');
		}

		array_walk($val, 'ConvertFromWin1251ToUTF8');

		$this->ReturnData($val);

		return true;
		
	}
}
