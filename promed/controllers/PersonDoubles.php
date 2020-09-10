<?php	defined('BASEPATH') or die ('No direct script access allowed');
class PersonDoubles extends swController {
	public $inputRules = array(
		'checkPersonDoublesGroup' => array(
			array(
				'field' => 'Person_did',
				'label' => 'Идентификатор пациента [2]',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор пациента [1]',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'PersonDoubles_id',
				'label' => 'Идентификатор группы двойников',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'deletePersonDoublesGroup' => array(
			array(
				'field' => 'PersonDoubles_id',
				'label' => 'Идентификатор группы двойников',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'loadPersonDoublesData' => array(
			array(
				'field' => 'PersonDoubles_id',
				'label' => 'Идентификатор группы двойников',
				'rules' => 'required',
				'type' => 'id'
			)
		),
        'loadPersonDoublesModerationList' => array(
            array(
                'default' => 0,
                'field' => 'start',
                'label' => 'Начальный номер записи',
                'rules' => 'trim',
                'type' => 'int'
            ),
            array(
                'default' => 100,
                'field' => 'limit',
                'label' => 'Количество возвращаемых записей',
                'rules' => 'trim',
                'type' => 'int'
            ),
            array(
                'default' => '',
                'field' => 'PersonSurName',
                'label' => 'Фамилия',
                'rules' => 'ban_percent|trim',
                'type' => 'string'
            ),
            array(
                'default' => '',
                'field' => 'PersonFirName',
                'label' => 'Имя',
                'rules' => 'ban_percent|trim',
                'type' => 'string'
            ),
            array(
                'default' => '',
                'field' => 'PersonSecName',
                'label' => 'Отчество',
                'rules' => 'ban_percent|trim',
                'type' => 'string'
            ),
            array(
                'default' => '',
                'field' => 'PersonBirthDay',
                'label' => 'Дата рождения',
                'rules' => 'trim',
                'type' => 'date'
            ),
            array(
                'field' => 'Lpu_did',
                'label' => 'Идентификатор МО',
                'rules' => '',
                'type' => 'id'
            ),
            array(
                'field' => 'exceptSelectedLpu',
                'label' => 'Флаг исключения выбранного МО',
                'rules' => '',
                'type' => 'checkbox'
            )
        ),
		'searchPersonDoubles' => array(
			array(
				'field' => 'Document_SerNum',
				'label' => 'Серия и номер документа',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'default' => 0,
				'field' => 'Document_SerNum_Dif',
				'label' => 'Отличие (серия и номер документа)',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'PAddress_id',
				'label' => 'Адрес проживания',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Person_Birthday',
				'label' => 'Дата рождения',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'default' => 0,
				'field' => 'Person_Birthday_Dif',
				'label' => 'Отличие (дата рождения)',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'Person_BirthYear',
				'label' => 'Год рождения',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'default' => 0,
				'field' => 'Person_BirthYear_Dif',
				'label' => 'Отличие (год рождения)',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'Person_EdNum',
				'label' => 'Единый номер полиса',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Person_Firname',
				'label' => 'Имя',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'default' => 0,
				'field' => 'Person_Firname_Dif',
				'label' => 'Отличие (имя)',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'Person_Secname',
				'label' => 'Отчество',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'default' => 0,
				'field' => 'Person_Secname_Dif',
				'label' => 'Отличие (отчество)',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'Person_Snils',
				'label' => 'СНИЛС',
				'rules' => '',
				'type' => 'snils'
			),
			array(
				'default' => 0,
				'field' => 'Person_Snils_Dif',
				'label' => 'Отличие (СНИЛС)',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'Person_Surname',
				'label' => 'Фамилия',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'default' => 0,
				'field' => 'Person_Surname_Dif',
				'label' => 'Отличие (фамилия)',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'Polis_SerNum',
				'label' => 'Серия и номер полиса',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'default' => 0,
				'field' => 'Polis_SerNum_Dif',
				'label' => 'Отличие (серия и номер полиса)',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'Sex_id',
				'label' => 'Пол',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'SocStatus_id',
				'label' => 'Социальный статус',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'UAddress_id',
				'label' => 'Адрес регистрации',
				'rules' => '',
				'type' => 'id'
			)
		),
		'changePersonDoubles' => array(
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор главной записи',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Person_did',
				'label' => 'Идентификатор двойника записи',
				'rules' => 'required',
				'type' => 'id'
			),
		),
		'cancelPersonDoubles' => array(
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор главной записи',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Person_did',
				'label' => 'Идентификатор двойника записи',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'PersonDoublesStatus_id',
				'label' => 'Причина отказа',
				'rules' => 'required',
				'type' => 'id'
			)
		)
	);

    /**
     * Конструктор
      */
    function __construct() {
		parent::__construct();

		$this->load->database();
		$this->load->model('PersonDoubles_model', 'dbmodel');
	}


	/**
	*  Проверка двойников из выбранной группы на вхождение в другие группы
	*  Входящие данные: $_POST['Person_did'], $_POST['Person_id'], $_POST['PersonDoubles_id']
	*  На выходе: JSON-строка
	*  Используется: форма работы с двойниками
	*/
	function checkPersonDoublesGroup() {
		$data = array();

		// Получаем сессионные переменные
		$data = array_merge($data, getSessionParams());

		$err = getInputParams($data, $this->inputRules['checkPersonDoublesGroup']);

		if ( strlen($err) > 0 ) {
			echo json_return_errors($err);
			return false;
		}

		echo $this->dbmodel->checkPersonDoublesGroup($data);

		return true;
	}


	/**
	*  Удаление группы двойников
	*  Входящие данные: $_POST['PersonDoubles_id']
	*  На выходе: JSON-строка
	*  Используется: форма работы с двойниками
	*/
	function deletePersonDoublesGroup() {
		$data = array();
		$val  = array();

		// Получаем сессионные переменные
		$data = array_merge($data, getSessionParams());

		$err = getInputParams($data, $this->inputRules['deletePersonDoublesGroup']);

		if ( strlen($err) > 0 ) {
			echo json_return_errors($err);
			return false;
		}

		$response = $this->dbmodel->deletePersonDoublesGroup($data);

		if ( (is_array($response)) && (count($response) > 0) ) {
			if ( (isset($response[0]['Error_Msg'])) && (strlen($response[0]['Error_Msg']) == 0) ) {
				$val['success'] = true;
			}
			else {
				$val = $response[0];
				$val['success'] = false;
			}
		}
		else {
			$val['Error_Msg'] = 'При удалении группы двойников возникли ошибки';
			$val['success'] = false;
		}

		array_walk($val, 'ConvertFromWin1251ToUTF8');

		$this->ReturnData($val);

		return true;
	}


	/**
	*  Получение данных по двойникам
	*  Входящие данные: ...
	*  На выходе: JSON-строка
	*  Используется: форма работы с двойниками
	*/
	function loadPersonDoublesData() {
		$data = array();
		$val  = array();

		// Получаем сессионные переменные
		$data = array_merge($data, getSessionParams());

		$err = getInputParams($data, $this->inputRules['loadPersonDoublesData']);

		if ( strlen($err) > 0 ) {
			echo json_return_errors($err);
			return false;
		}

		$response = $this->dbmodel->loadPersonDoublesData($data);

		if ( is_array($response) && count($response) == 2 ) {
			$response = array(
				array(
					'Row_id' => 0,
					'Row_Name' => 'Server_pid',
					'Row_Value_1' => $response[0]['Server_pid'],
					'Row_Value_2' => $response[1]['Server_pid'],
					'Row_Value_New' => ''
				),
				array(
					'Row_id' => 1,
					'Row_Name' => 'Фамилия',
					'Row_Value_1' => $response[0]['Person_Surname'],
					'Row_Value_2' => $response[1]['Person_Surname'],
					'Row_Value_New' => ''
				),
				array(
					'Row_id' => 2,
					'Row_Name' => 'Имя',
					'Row_Value_1' => $response[0]['Person_Firname'],
					'Row_Value_2' => $response[1]['Person_Firname'],
					'Row_Value_New' => ''
				),
				array(
					'Row_id' => 3,
					'Row_Name' => 'Отчество',
					'Row_Value_1' => $response[0]['Person_Secname'],
					'Row_Value_2' => $response[1]['Person_Secname'],
					'Row_Value_New' => ''
				),
				array(
					'Row_id' => 4,
					'Row_Name' => 'Дата рождения',
					'Row_Value_1' => $response[0]['Person_Birthday'],
					'Row_Value_2' => $response[1]['Person_Birthday'],
					'Row_Value_New' => ''
				),
				array(
					'Row_id' => 5,
					'Row_Name' => 'БДЗ',
					'Row_Value_1' => $response[0]['Person_IsBDZ'],
					'Row_Value_2' => $response[1]['Person_IsBDZ'],
					'Row_Value_New' => ''
				),
				array(
					'Row_id' => 6,
					'Row_Name' => 'Фед. льгот.',
					'Row_Value_1' => $response[0]['Person_IsFedLgot'],
					'Row_Value_2' => $response[1]['Person_IsFedLgot'],
					'Row_Value_New' => ''
				),
				array(
					'Row_id' => 7,
					'Row_Name' => 'Отказ',
					'Row_Value_1' => $response[0]['Person_IsRefuse'],
					'Row_Value_2' => $response[1]['Person_IsRefuse'],
					'Row_Value_New' => ''
				),
				array(
					'Row_id' => 8,
					'Row_Name' => 'ЛПУ прикрепления',
					'Row_Value_1' => $response[0]['Lpu_Name'],
					'Row_Value_2' => $response[1]['Lpu_Name'],
					'Row_Value_New' => ''
				),
				array(
					'Row_id' => 9,
					'Row_Name' => 'Полис (серия, номер)',
					'Row_Value_1' => $response[0]['Polis_SerNum'],
					'Row_Value_2' => $response[1]['Polis_SerNum'],
					'Row_Value_New' => ''
				),
				array(
					'Row_id' => 10,
					'Row_Name' => 'Полис (выдан, СМО)',
					'Row_Value_1' => $response[0]['Polis_setInfo'],
					'Row_Value_2' => $response[1]['Polis_setInfo'],
					'Row_Value_New' => ''
				),
				array(
					'Row_id' => 11,
					'Row_Name' => 'СНИЛС',
					'Row_Value_1' => $response[0]['Person_Snils'],
					'Row_Value_2' => $response[1]['Person_Snils'],
					'Row_Value_New' => ''
				),
				array(
					'Row_id' => 12,
					'Row_Name' => 'Адрес проживания',
					'Row_Value_1' => $response[0]['PAddress_Name'],
					'Row_Value_2' => $response[1]['PAddress_Name'],
					'Row_Value_New' => ''
				),
				array(
					'Row_id' => 13,
					'Row_Name' => 'Адрес регистрации',
					'Row_Value_1' => $response[0]['UAddress_Name'],
					'Row_Value_2' => $response[1]['UAddress_Name'],
					'Row_Value_New' => ''
				),
				array(
					'Row_id' => 14,
					'Row_Name' => 'Пол',
					'Row_Value_1' => $response[0]['Sex_Name'],
					'Row_Value_2' => $response[1]['Sex_Name'],
					'Row_Value_New' => ''
				),
				array(
					'Row_id' => 15,
					'Row_Name' => 'Социальный статус',
					'Row_Value_1' => $response[0]['SocStatus_Name'],
					'Row_Value_2' => $response[1]['SocStatus_Name'],
					'Row_Value_New' => ''
				),
				array(
					'Row_id' => 16,
					'Row_Name' => 'Документ (серия, номер, выдан)',
					'Row_Value_1' => $response[0]['Document_Info'],
					'Row_Value_2' => $response[1]['Document_Info'],
					'Row_Value_New' => ''
				),
				array(
					'Row_id' => 17,
					'Row_Name' => 'Место работы',
					'Row_Value_1' => $response[0]['OrgJob_Name'],
					'Row_Value_2' => $response[1]['OrgJob_Name'],
					'Row_Value_New' => ''
				)
			);

			foreach ( $response as $row ) {
				array_walk($row, 'ConvertFromWin1251ToUTF8');
				$val[] = $row;
			}
		}

		$this->ReturnData($val);

		return false;
	}


	/**
	*  Получение списка групп двойников
	*  Входящие данные: ...
	*  На выходе: JSON-строка
	*  Используется: форма работы с двойниками
	*/
	function loadPersonDoublesGroupsList() {
		$data = array();
		$val  = array();

		// Получаем сессионные переменные
		$data = array_merge($data, getSessionParams());

		$response = $this->dbmodel->loadPersonDoublesGroupsList($data);

		if ( is_array($response) && count($response) > 0 ) {
			foreach ( $response as $row ) {
				array_walk($row, 'ConvertFromWin1251ToUTF8');
				$val[] = $row;
			}
		}

		$this->ReturnData($val);

		return false;
	}


	/**
	*  Поиск двойников в БД по заданным фильтрам
	*  Входящие данные: ...
	*  На выходе: JSON-строка
	*  Используется: форма работы с двойниками
	*/
	function searchPersonDoubles() {
		$data = array();
		$val  = array();

		// Получаем сессионные переменные
		$data = array_merge($data, getSessionParams());

		$err = getInputParams($data, $this->inputRules['searchPersonDoubles']);

		if ( strlen($err) > 0 ) {
			echo json_return_errors($err);
			return false;
		}

		$response = $this->dbmodel->searchPersonDoubles($data);

		if ( is_array($response) && count($response) > 0 ) {
			if ( !isset($response['Error_Msg']) ) {
				$val = array('success' => false, 'Error_Msg' => 'Ошибка при выполнении процедуры поиска двойников');
			}
			else if ( strlen($response['Error_Msg']) > 0 ) {
				$val = array('success' => false, 'Error_Msg' => $response['Error_Msg']);
			}
			else {
				$val = array('success' => true, 'Error_Msg' => '');
			}
		}
		else {
			$val = array('success' => false, 'Error_Msg' => 'Ошибка при выполнении процедуры поиска двойников');
		}

		array_walk($val, 'ConvertFromWin1251ToUTF8');

		$this->ReturnData($val);

		return false;
	}
	
	
	/**
	 * Загрузка данных людей отправленных на модерацию
	 */
	function loadPersonDoublesModerationList() {
		$data = array();
		$val  = array();

		// Получаем сессионные переменные
		//$data = array_merge($data, getSessionParams());

        $data = $this->ProcessInputData('loadPersonDoublesModerationList', true);
        if ($data === false) { return true; }

        $info = $this->dbmodel->getPersonDoublesForModeration($data);
        if ( $info != false && count($info) > 0 )
        {
            // идешники людей для проверки был ли человек с сервер ид > 0,
            // чтобы не выводить персонов с нулевым сервером, если для него существует
            // запись с иднтификатором сервера лпу
            $person_ids = array();
            $val = array();
            $val['data'] = array();
            $val['totalCount'] = 0;
            $count = 0;

            foreach ($info as $rows)
            {
                // проверяем, есть ли уже человек с этим идентификатором в контрольном массиве
                if ( isset($rows['__countOfAllRows']) )
                {
                    $count = $rows['__countOfAllRows'];
                }
                else
                {
                    array_walk($rows, 'ConvertFromWin1251ToUTF8');
                    $val['data'][] = $rows;
                }
            }
            $val['totalCount'] = $count;
            $this->ReturnData($val);
        }
		/*if ( is_array($response) && count($response) > 0 ) {
            $val = array();
            $val['data'] = array();
            $val['totalCount'] = 0;
            $count = 0;
			foreach ( $response as $row ) {
				array_walk($row, 'ConvertFromWin1251ToUTF8');
				$val[] = $row;
			}
		}

		array_walk($val, 'ConvertFromWin1251ToUTF8');

		$this->ReturnData($val);*/

		//return false;
	}
	
	
	/**
	 * Смена главной записи и двойника
	 */
	function changePersonDoubles() {
		$data = array();
		$val  = array();

		// Получаем сессионные переменные
		$data = array_merge($data, getSessionParams());
		
		$err = getInputParams($data, $this->inputRules['changePersonDoubles']);
		
		if ( strlen($err) > 0 ) {
			echo json_return_errors($err);
			return false;
		}
		
		$response = $this->dbmodel->changePersonDoubles($data);

		if ( is_array($response) && count($response) > 0 ) {
			if ( !isset($response['Error_Msg']) ) {
				$val = array('success' => false, 'Error_Msg' => 'Ошибка при смене двойников');
			}
			else if ( strlen($response['Error_Msg']) > 0 ) {
				$val = array('success' => false, 'Error_Msg' => $response['Error_Msg']);
			}
			else {
				$val = array('success' => true, 'Error_Msg' => '');
			}
		}
		else {
			$val = array('success' => false, 'Error_Msg' => 'Ошибка при выполнении смены двойников');
		}
		
		array_walk($val, 'ConvertFromWin1251ToUTF8');
		
		$this->ReturnData($val);

		return false;
	}
	
	
	/**
	 * Отказ в модерации двойника
	 */
	function cancelPersonDoubles() {
		$data = array();
		$val  = array();

		// Получаем сессионные переменные
		$data = array_merge($data, getSessionParams());
		
		$err = getInputParams($data, $this->inputRules['cancelPersonDoubles']);
		
		if ( strlen($err) > 0 ) {
			echo json_return_errors($err);
			return false;
		}
		
		$response = $this->dbmodel->cancelPersonDoubles($data);

		if ( is_array($response) && count($response) > 0 ) {
			if ( !isset($response['Error_Msg']) ) {
				$val = array('success' => false, 'Error_Msg' => 'Ошибка при отказе в модерации двойников');
			}
			else if ( strlen($response['Error_Msg']) > 0 ) {
				$val = array('success' => false, 'Error_Msg' => $response['Error_Msg']);
			}
			else {
				$val = array('success' => true, 'Error_Msg' => '');
			}
		}
		else {
			$val = array('success' => false, 'Error_Msg' => 'Ошибка при отказе в модерации двойников');
		}
		
		array_walk($val, 'ConvertFromWin1251ToUTF8');
		
		$this->ReturnData($val);

		return false;
	}
	
}
