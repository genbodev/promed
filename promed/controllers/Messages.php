<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* Messages - контроллер для работы с сообщениями
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Messages
* @access       public
* @copyright    Copyright (c) 2011 Swan Ltd.
* @author       Dmitry Storozhev
* @version      23.08.2011
*/

class Messages extends swController {

	public $inputRules = array(
		'getMessagesFolder' => array(
		),
		'getGroups' => array(
		),
		'getGroupsNoUser' => array(
			array(
				'field' => 'user_id',
				'label' => 'ID пользователя - отправителя ',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'GroupUser' => array(
			array(
				'field' => 'group_id',
				'label' => 'ID группы',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'dn',
				'label' => 'ID',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'group_name',
				'label' => 'Название группы',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'group_type',
				'label' => 'Тип группы',
				'rules' => 'required',
				'type' => 'int'
			),
			array(
				'field' => 'user_id',
				'label' => 'Код пользователя',
				'rules' => '',
				'type' => 'id'
			)
		),
		'deleteGroupUser' => array(
		),
		'deleteGroup' => array(
			array(
				'field' => 'group_id',
				'label' => 'ID группы',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'dn',
				'label' => 'ID',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'group_name',
				'label' => 'Название группы',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'group_type',
				'label' => 'Тип группы',
				'rules' => 'required',
				'type' => 'int'
			)
		),
		'saveGroup' => array(
			array(
				'field' => 'group_id',
				'label' => 'ID группы',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'dn',
				'label' => 'ID',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'group_name',
				'label' => 'Название группы',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'group_type',
				'label' => 'Тип группы',
				'rules' => 'required',
				'type' => 'int'
			),
			array(
				'field' => 'group_name',
				'label' => 'Название группы',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'group_type',
				'label' => 'Тип группы',
				'rules' => 'required',
				'type' => 'int'
			)
		),
		'loadMessagesGrid' => array(
			array(
				'default' => 0,
				'field' => 'start',
				'label' => '',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'default' => 100,
				'field' => 'limit',
				'label' => '',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'default' => 1,
				'field' => 'FolderType_id',
				'label' => 'Тип папки сообщений(вх, исх, черн)',
				'rules' => '',
				'type' => 'id'
			),
			// далее фильтры поиска
			array(
				'field' => 'MessagePeriodDate',
				'label' => 'Период (от - до)',
				'rules' => '',
				'type' => 'daterange'
			),
			array(
				'field' => 'UserSend_id',
				'label' => 'Ид. отправителя',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Message_isRead',
				'label' => 'Прочитано или нет',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'NoticeType_id',
				'label' => 'Вид уведомления',
				'rules' => '',
				'type' => 'id'
			)
		),
		'saveUserDataProfile' => array(
			array(
				'field' => 'user_login',
				'label' => 'Логин',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'user_surname',
				'label' => 'Фамилия',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'user_firname',
				'label' => 'Имя',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'user_secname',
				'label' => 'Отчество',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'user_email',
				'label' => 'E-mail',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'user_phone',
				'label' => 'Номер мобильного телефона',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'user_about',
				'label' => 'О себе',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'user_photo',
				'label' => 'Аватар',
				'rules' => 'trim',
				'type' => 'string'
			)
		),
		'getMedStaffactsforUser' => array(
			array(
				'field' => 'user_id',
				'label' => 'Идентификатор пользователя',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'MedPersonal_id',
				'label' => 'Идентификатор врача',
				'rules' => '',
				'type' => 'int'
			)
			/*,
			array(
				'field' => 'Lpu_id',
				'label' => 'ЛПУ',
				'rules' => '',
				'type' => 'int'
			)
			*/
		),
		'sendUserPhoneActivationCode' => array(
			array(
				'field' => 'pmUser_Login',
				'label' => 'Логин пользователя',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'user_phone',
				'label' => 'Номер телефона',
				'rules' => 'required',
				'type' => 'int'
			)
		),
		'activateUserPhone' => array(
			array(
				'field' => 'pmUser_Login',
				'label' => 'Логин пользователя',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'user_phone',
				'label' => 'Номер телефона',
				'rules' => 'required',
				'type' => 'int'
			),
			array(
				'field' => 'user_phone_act_code',
				'label' => 'Код активации телефона',
				'rules' => 'required',
				'type' => 'int'
			)
		),
		'loadUserSearchGrid' => array(
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
				'field' => 'pmUser_surName',
				'label' => 'Фамилия',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'pmUser_firName',
				'label' => 'Имя',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'pmUser_secName',
				'label' => 'Отчество',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'Lpu_Nick',
				'label' => 'ЛПУ',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'LpuSection_Name',
				'label' => 'Отделение',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'MedSpec_Name',
				'label' => 'Должность',
				'rules' => 'trim',
				'type' => 'string'
			)
		),
		'saveMessage' => array(	
			array(
				'field' => 'Message_id',
				'label' => 'Идентификатор сообщения',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'Message_pid',
				'label' => 'Идентификатор родительского сообщения',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'Message_Subject',
				'label' => 'Заголовок сообщения',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'Message_Text',
				'label' => 'Текст сообщения',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Users_id',
				'label' => 'Пользователи',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'Lpus',
				'label' => 'ЛПУ',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'pmUser_Group',
				'label' => 'Группа пользователей Промед',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'default' => null,
				'field' => 'Message_isSent',
				'label' => 'Статус (отправлено/не отправлено)',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'default' => 1,
				'field' => 'NoticeType_id',
				'label' => 'Тип уведомления',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'default' => null,
				'field' => 'Message_isFlag',
				'label' => 'Флаг сообщения',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'default' => null,
				'field' => 'Message_isDelete',
				'label' => 'Признак удаления сообщения',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'RecipientType_id',
				'label' => 'Тип получателя',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'action',
				'label' => 'Экшн сохранения',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'MessageRecipient_id',
				'label' => 'Ид полученного сообщения',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'Message_isRead',
				'label' => 'Сообщение прочитано или нет',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'Files',
				'label' => 'Файлы',
				'rules' => 'trim',
				'type' => 'string'
			)
		),
		'deleteMessage' => array(
			array(
				'field' => 'Message_id',
				'label' => 'Ид. Сообщения',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'MessageRecipient_id',
				'label' => 'Ид. полученного сообщения',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'FolderType_id',
				'label' => 'Тип папки сообщений',
				'rules' => '',
				'type' => 'int'
			)
		),
		'deleteMessages' => array(
			array(
				'field' => 'Message_ids',
				'label' => 'Ид. Сообщений',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'MessageRecipient_ids',
				'label' => 'Ид. полученного сообщений',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'FolderType_id',
				'label' => 'Тип папки сообщений',
				'rules' => '',
				'type' => 'int'
			)
		),
		'setMessageIsRead' => array(
			array(
				'field' => 'Message_id',
				'label' => 'Ид. Сообщения',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'MessageRecipient_id',
				'label' => 'Ид. полученного сообщения',
				'rules' => '',
				'type' => 'int'
			)
		),
		'setMessageActive' => array(
			array(
				'field' => 'Message_id',
				'label' => 'Ид. Сообщения',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'MessageRecipient_id',
				'label' => 'Ид. полученного сообщения',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'Message_isFlag',
				'label' => 'Флаг полученного сообщения',
				'rules' => '',
				'type' => 'int'
			)
		),
		'deleteFile' => array(
			array(
				'field' => 'url',
				'label' => 'Путь к файлу',
				'rules' => 'trim',
				'type' => 'string'
			)
		),
		'sendNotificationEmergencyTeam' => array(
			array(
				'field' => 'EmergencyTeam_id',
				'label' => 'ID бригады',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'EmergencyTeam_Num',
				'label' => 'Номер бригады',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'Person_FIO',
				'label' => 'ФИО пациента',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Urgency',
				'label' => 'Срочность',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'Numv',
				'label' => 'Номер вызова',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'Adress_Name',
				'label' => 'Адрес вызова',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'MedService_id',
				'label' => 'id мед сервиса',
				'rules' => '',
				'type' => 'int'
			),
		),
		'getMessagesList' => array(
			array(
				'field' => 'start',
				'label' => 'Позиция просматриваемого сообщения',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'limit',
				'label' => 'Кол-во подгружаемых сообщений',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'query',
				'label' => 'Строка запроса',
				'rules' => '',
				'type' => 'string'
			)
		)
	);
	
	private $inputData = array();

	/**
	 * Description
	 */
	function __construct() {
		parent::__construct();

		$this->load->database();
		$this->load->model('Messages_model', 'dbmodel');
	}
	/**
	 * Функция проверки на доступ к внесению изменений в группе
	 */
	function isAccessGroupUser($type) 
	{
		return (($type>=0 && isSuperadmin()) || ($type>=1 && isLpuAdmin()) || ($type==2));
	}
	
	/**
	 * Получение списка папок для писем
	 */
	function getMessagesFolder()
	{
		$data = $this->ProcessInputData('getMessagesFolder', true);
		if ($data) {
			$response = $this->dbmodel->getMessagesFolder($data);
			
			if(is_array($response) && !isset($response[0]['Error_Msg']))
			{
				$blocknew = array('first'=>((int)$response[0]['InputCount_new'] > 0 )?'<b>':'','last'=>((int)$response[0]['InputCount_new'] > 0 )?'</b>':'');
				$val = array(
					array(
						'FolderType' => 1,
						'id'         => 1,
						'count'      => $response[0]['InputCount_new'],
						'text'       => $blocknew['first'].'Входящие'.' - '.$response[0]['InputCount_all'].(((int)$response[0]['InputCount_new'] > 0 )?' ('.$response[0]['InputCount_new'].')':'').$blocknew['last'],
						'leaf'       => true,
						'iconCls'    => 'inbox16'
					),
					array(
						'FolderType' => 2,
						'id'         => 2,
						'count'      => $response[0]['OutputCount'],
						'text'       => 'Отправленные - '.$response[0]['OutputCount'],
						'leaf'       => true,
						'iconCls'    => 'sent16'
					),
					array(
						'FolderType' => 3,
						'id'         => 3,
						'count'      => $response[0]['DraftCount'],
						'text'       => 'Черновики - '.$response[0]['DraftCount'],
						'leaf'       => true,
						'iconCls'    => 'draft16'
					)
				);
				
				
				$this->ProcessModelList($val, true, true)->ReturnData();
			}
			else
			{
				echo json_encode(array('success' => false));
				return false;
			}
		} else {
			return false;
		}
	}
	
	/**
	 * Отображение списка пользователей определенной группы
	 */
	function getGroupUser() {
		
		// !!! Поскольку нельзя дважды сделать вызов ProcessInputData из-за неверного getInputParams (а точнее конвертируя _POST) поэтому предварительно объединяю два массива правил, хотя по идее можно было бы последовательно вызывать процедуру с разными правилами
		$this->inputRules['GroupUser'] = array_merge($this->inputRules['GroupUser'], $this->inputRules['loadUserSearchGrid']);
		$data = $this->ProcessInputData('GroupUser', true);
		
		if ($data) {
			// Группа существует 
			$addrbook = new pmAdressBooks($data['dn'],$data['group_id'],toUtf($data['group_name']), $data['group_type']);
			// Остается получить список людей в данной группе 
			$data['users'] = $addrbook->users();
			// И по нему прочитать из базы людей 
			$response = $this->dbmodel->loadUserSearchGrid($data);
			$this->ProcessModelMultiList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * Добавление пользователя в группу
	 */
	function addGroupUser() {
		$data = $this->ProcessInputData('GroupUser', true);
		if ($this->isAccessGroupUser($data['group_type'])) {
			if (!empty($data['user_id'])) {
				// Во-первых группа уже должна быть создана
				$addrbook = new pmAdressBooks($data['dn'],$data['group_id'],toUtf($data['group_name']), $data['group_type']);
				// во вторых в классе работы с адресными книгами есть метод для добавления пользователя 
				$res = $addrbook->user_insert($data['user_id']);
				if ($res) {
					$r = array('user_id'=>$data['user_id'], 'success'=>true, 'Error_Msg'=>'');
				} else {
					$r = array('user_id'=>$data['user_id'], 'success'=>false, 'Error_Msg'=>toUtf('Добавляемый пользователь уже присутствует в группе '.$data['group_name'].'.'));
				}
				echo json_encode($r);
			} else {
				echo json_encode(array('success'=>false, 'Error_Msg'=>toUtf('Не выбран пользователь для добавления.')));
			}
		} else {
			echo json_encode(array('success'=>false, 'Error_Msg'=>toUtf('Ваши права не позволяют изменять состав данной группы.')));
		}
	}
	/**
	 * Удаление пользователя из группу
	 */
	function deleteGroupUser() {
		$data = $this->ProcessInputData('GroupUser', true);
		if ($this->isAccessGroupUser($data['group_type'])) {
			if (!empty($data['user_id'])) {
				// Во-первых группа уже должна быть создана
				$addrbook = new pmAdressBooks($data['dn'],$data['group_id'],toUtf($data['group_name']), $data['group_type']);
				// во вторых в классе работы с адресными книгами есть метод для добавления пользователя 
				$res = $addrbook->user_delete($data['user_id']);
				if ($res) {
					$r = array('user_id'=>$data['user_id'], 'success'=>true, 'Error_Msg'=>'');
				} else {
					$r = array('user_id'=>$data['user_id'], 'success'=>false, 'Error_Msg'=>toUtf('Не возможно удалить пользователя из адресной книги!'));
				}
				echo json_encode($r);
			} else {
				echo json_encode(array('success'=>false, 'Error_Msg'=>toUtf('Не выбран пользователь для удаления.')));
			}
		} else {
			echo json_encode(array('success'=>false, 'Error_Msg'=>toUtf('Ваши права не позволяют изменять состав данной группы.')));
		}
	}
	/**
	 * Формирует список групп адресных книг доступных для текущего пользователя
	 */ 
	function getGroups()
	{
		/**
		 * Description
		 */
		function _sort($a, $b)
		{
			return ($a['type'] < $b['type']) ? -1 : 1;
		}
	
		$data = $this->ProcessInputData('getGroups', true);
		if ($data) {
			$response = pmAdressBooks::load($data['Lpu_id'],$data['pmUser_id']);
			usort($response, "_sort");
			echo json_encode($response);
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * Формирует список групп в которых нет указанного пользователя, в зависимости от прав текущего пользователя 
	 */ 
	function getGroupsNoUser($data)
	{
		/**
		 * Description
		 */
		function _sort($a, $b)
		{
			return ($a['type'] < $b['type']) ? -1 : 1;
		}
		$r = array();
		//$data = $this->ProcessInputData('getGroupsNoUser', true);
		if ($data) {
			$response = pmAdressBooks::no_user_books($data['Lpu_id'],$_SESSION['pmuser_id'],$data['user_id']);
			if (isset($response) && (count($response)>0)) {
				for($i=0; $i<count($response); $i++) {
					if ($this->isAccessGroupUser($response[$i]['type'])) {
						$r[] = $response[$i];
					}
				}
				usort($r, "_sort");
			}
		} 
		return $r;
	}
	/**
	 * Сохранение группы адресной книги в ЛДАП
	 */ 
	function saveGroup()
	{
		$data = $this->ProcessInputData('saveGroup', true);
		if ($this->isAccessGroupUser($data['group_type'])) {
			// Здесь два варианта 
			if (!empty($data['dn'])) {
				// 1. Если данные пересохраняются
				$addrbook = new pmAdressBooks($data['dn'],$data['group_id'],toUtf($data['group_name']), $data['group_type']);
				// Тип группы можно менять только под суперадмином
				if (isSuperAdmin()) {
					$addrbook->organizations = array();
					$addrbook->pmuser_id = array();
					if ($data['group_type']==1) { // Если сохраняем локальную АК, то указываем ЛПУ на которое сохраняем 
						$addrbook->organizations = array($data['Lpu_id']);
					}
					if ($data['group_type']==2) { // Если сохраняем персональную АК, то указываем пользователя на которого сохраняем
						$addrbook->pmuser_id = array($data['pmUser_id']);
					}
				} 
				$addrbook->edit(isSuperAdmin());
			} else {
				// 2. Данные сохраняются впервые 
				$addrbook = new pmAdressBooks(null,null,toUtf($data['group_name']), $data['group_type']);
				if ($data['group_type']==1) { // Если сохраняем локальную АК, то указываем ЛПУ на которое сохраняем 
					$addrbook->organizations = array($data['Lpu_id']);
				}
				if ($data['group_type']==2) { // Если сохраняем персональную АК, то указываем пользователя на которого сохраняем
					$addrbook->pmuser_id = array($data['pmUser_id']);
				}
				$addrbook->add();
			}
			$r = array('group_id'=>$addrbook->name, 'success'=>true, 'Error_Msg'=>'');
			echo json_encode($r);
		} else {
			echo json_encode(array('success'=>false, 'Error_Msg'=>toUtf('Ваши права не позволяют создать данную группу.')));
		}
	}
	/**
	 * Удаление группы адресной книги в ЛДАП
	 */
	function deleteGroup()
	{
		$data = $this->ProcessInputData('deleteGroup', true);
		if ($this->isAccessGroupUser($data['group_type'])) {
			// TODO: Предварительная проверка на наличие доступа на удаление 
			$addrbook = new pmAdressBooks($data['dn'],$data['group_id'],toUtf($data['group_name']), $data['group_type']);
			$addrbook->remove();
			$r = array(array('success'=>true, 'Error_Msg'=>''));
			echo json_encode($r);
		} else {
			echo json_encode(array('success'=>false, 'Error_Msg'=>toUtf('Ваши права не позволяют удалить данную группу.')));
		}
	}
	
	/**
	 * Загрузка списка сообщений
	 */
	function loadMessagesGrid()
	{
		$response = array();
		$data = $this->ProcessInputData('loadMessagesGrid', true);
		if ($data) {
			$response = $this->dbmodel->loadMessagesGrid($data);
			$this->ProcessModelMultiList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Отправка кода активации мобильного телефона
	 */
	function sendUserPhoneActivationCode()
	{
		$data = $this->ProcessInputData('sendUserPhoneActivationCode', true);
		$response = array('succes' => false, 'Error_Msg' => '');

		if(!empty($data['pmUser_Login'])) {
			$user = pmAuthUser::find($data['pmUser_Login']);
		} else {
			$user = pmAuthUser::find($data['session']['login']);
		}

		if ($data['user_phone'] == $user->phone && $user->phone_act == 2) {
			$response['success'] = true;
			$response['Error_Msg'] = toUtf('Номер телефона уже активирован');
			$this->ReturnData($response);
			return true;
		}

		$phone_act_code = '';
		for($i=0; $i<4; $i++) {
			$phone_act_code .= mt_rand(0, 9);
		}

		$user->phone = $data['user_phone'];
		$user->phone_act = 1;
		$user->phone_act_code = $phone_act_code;
		$_SESSION['phone'] = $user->phone;
		$_SESSION['phone_act'] = $user->phone_act;
		$_SESSION['phone_act_code'] = $user->phone_act_code;
		$user->post();

		$text = 'Код активации телефона '.$phone_act_code;

		$this->load->helper('Notify');
		try{
			$resp = sendPmUserNotifySMS(array(
				'sms_id' => $user->pmuser_id.$phone_act_code,
				'pmUser_Phone' => $data['user_phone'],
				'text' => $text
			));
			if (!$resp) {
				throw new Exception('Не удалось отправить СМС');
			}
		}
		catch(Exception $e){
			$response['Error_Msg'] = toUtf($e->getMessage());
			$this->ReturnData($response);
			return false;
		}

		$response['success'] = true;
		$this->ProcessModelSave($response, true, 'Ошибка при отправке кода активации')->ReturnData();
		return true;
	}

	/**
	 * Активация мобильного телефона
	 */
	function activateUserPhone()
	{
		$data = $this->ProcessInputData('activateUserPhone', true);
		$response = array('success' => false, 'Error_Msg' => '');

		if(!empty($data['pmUser_Login'])) {
			$user = pmAuthUser::find($data['pmUser_Login']);
		} else {
			$user = pmAuthUser::find($data['session']['login']);
		}

		if ($user->phone != $data['user_phone']) {
			$response['Error_Msg'] = toUtf('Не совпадает номер телефона для активации!');
			$this->ReturnData($response);
			return false;
		}

		if ($user->phone_act_code == $data['user_phone_act_code']) {
			$user->phone_act = 2;
			$_SESSION['phone_act'] = $user->phone_act;
			$user->post();
		} else {
			$response['Error_Msg'] = toUtf('Введен неверный код активации телефона!');
			$this->ReturnData($response);
			return false;
		}

		$response['success'] = true;
		$this->ProcessModelSave($response, true, 'Ошибка при отправке кода активации')->ReturnData();
		return true;
	}

	/**
	 * Получение профиля пользователя
	 */
	function getUserDataProfile()
	{
		$val = array();
		if(isset($_POST['pmUser_Login']))
			$user = pmAuthUser::find($_POST['pmUser_Login']);
		else
			$user = pmAuthUser::find($_SESSION['login']);
		//print_r($user);
		if(!$user)
		{
			DieWithError('Не удалось найти пользователя.');
			return false;
		}
		
		if(isset($_POST['Lpu_Nick']))
		{
			$response = array(
				0 => array(
					'Lpu_Nick' => $_POST['Lpu_Nick']
				)
			);
		}
		elseif(!empty($_SESSION['lpu_id']))
		{
			$response = $this->dbmodel->getLpuforUserData($_SESSION['lpu_id']);
			array_walk($response[0], 'ConvertFromWin1251ToUTF8');
		}
		
		if(empty($_SESSION['lpu_id']) || !is_array($response) || !isset($response[0]['Lpu_Nick']))
		{
			DieWithError('Не удалось найти ЛПУ.');
			return false;
		}
		
		$val[] = array(
			'pmuser_id'		=> $user->pmuser_id,
			'user_login'	=> $user->login,
			'user_surname'	=> $user->surname,
			'user_secname'	=> $user->secname,
			'user_firname'	=> $user->firname,
			'user_email'	=> $user->email,
			'user_phone'	=> $user->phone,
			'user_phone_act'=> $user->phone_act,
			'user_about'	=> $user->about,
			'user_avatar'	=> $user->avatar,
			'user_Lpu'		=> $response[0]['Lpu_Nick']
		);
		$this->ReturnData($val);
	}
	
	/**
	 * Сохранение профиля пользователя
	 */
	function saveUserDataProfile()
	{
		$data = $this->ProcessInputData('saveUserDataProfile', true, false); // тут сессию закрывать нельзя, так как ниже она меняется 
		array_walk($data, 'ConvertFromWin1251ToUTF8');
		// Разрешаем сохранять только свои профили 
		if ($data['user_login']==$_SESSION['login']) {
			$user = pmAuthUser::find($data['user_login']);
			if(!$user)
			{
				DieWithError('При сохранении вашего профиля произошла ошибка');
				return false;
			}
			$user->surname = $data['user_surname'];
			$user->firname = $data['user_firname'];
			$user->secname = $data['user_secname'];
			$user->email = $data['user_email'];
			$user->phone_act = ($user->phone!=$data['user_phone']) ? 1 : $user->phone_act;
			$user->phone = $data['user_phone'];
			$user->about = $data['user_about'];
			/** Здесь изменение сессии не нужно, так как он поменяется в $user->post()
			$_SESSION['email'] = $user->email;
			$_SESSION['phone'] = $user->phone;
			$_SESSION['phone_act'] = $user->phone_act;
			$_SESSION['about'] = $user->about;
			*/
			$user->post();
			$this->ReCacheUserData($user);
			echo json_encode(array('success' => true));
		} else {
			echo json_encode(array('success' => false));
		}
	}
	
	/**
	 * Перекэширование данных пользователя
	 */
	function ReCacheUserData($user) {
		$this->load->model("User_model", "umodel");
		return $this->umodel->ReCacheUserData($user);
	}
	
	/**
	 * Получение списка мест работы привязанных к пользователю
	 */
	function getMedStaffactsforUser()
	{
		$response = array();
		$data = $this->ProcessInputData('getMedStaffactsforUser', true);
		if($data)
		{
			$response = $this->dbmodel->getMedStaffactsforUser($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		}
		else
		{
			echo json_encode(array('success' => false));
			return false;
		}
	}
	/**
	 * Поиск по кэшу пользователей и постраничный вывод
	 */
	function loadUserSearchGrid()
	{
		$data = $this->ProcessInputData('loadUserSearchGrid', true);
		if($data)
		{
			$response = $this->dbmodel->loadUserSearchGrid($data);
			$this->ProcessModelMultiList($response, true, true)->ReturnData();
			return true;
		}
		else
		{
			echo json_encode(array('success' => false));
			return false;
		}
	}
	
	/**
	 * Задания размеров загружаемой картинки
	 */
	function createthumb($name,$type,$filename,$new_w,$new_h){
		
		if (!empty($type)) {
			$system = explode('/',$type);
			$last = count($system)-1;
		} else {
			$system = explode('.',$name);
			$last = count($system)-1;
		}
		//print $name."-".$filename." ".$new_w." ".$new_h;
		if (preg_match('/jpg|jpeg|pjpeg|JPG|JPEG|PJPEG/',$system[$last])){
			$src_img = imagecreatefromjpeg($name);
		}
		if (preg_match('/png|PNG/',$system[$last])){
			$src_img = imagecreatefrompng($name);
		}
		if (preg_match('/gif|GIF/',$system[$last])){
			$src_img = imagecreatefromgif($name);
		}
		if(!isset($src_img)){
			return 0;
		}
		$old_x = imageSX($src_img);
		$old_y = imageSY($src_img);
		if ($old_x > $old_y) {
			$thumb_w = $new_w;
			$thumb_h = $old_y*($new_w/$old_x);
		}
		if ($old_x < $old_y) {
			$thumb_w = $old_x*($new_h/$old_y);
			$thumb_h = $new_h;
		}
		if ($old_x == $old_y) {
			$thumb_w = $new_w;
			$thumb_h = $new_h;
		}
		$dst_img = ImageCreateTrueColor($thumb_w,$thumb_h);
		imagecopyresampled($dst_img,$src_img,0,0,0,0,$thumb_w,$thumb_h,$old_x,$old_y); 
		if (preg_match("/png/",$system[1]))
		{
			imagepng($dst_img,$filename); 
		} 
		if (preg_match("/gif/",$system[1]))
		{
			imagegif($dst_img,$filename);
		}
		else 
		{
			imagejpeg($dst_img,$filename); 
		}
		imagedestroy($dst_img); 
		imagedestroy($src_img); 
		return 1;
	}

	
	
	/**
	 * Метод загрузки аватарки юзера на сервер
	 */
	function uploadUserPhoto()
	{
		if(!isset($_FILES['user_ava_uploader']))
		{
			echo json_encode(array('success' => false));
			return false;
		}
		$source = $_FILES['user_ava_uploader']['tmp_name'];
		
		// Если файл успешно загрузился в темповую директорию $source
		if(is_uploaded_file($source))
		{
			$pmuser_id = $_SESSION['pmuser_id'];
			
			// Наименование файла
			$fname = $_FILES['user_ava_uploader']['name'];
			
			// Корневая директория аватарок
			$root_ava_dir = USERSPATH;
			// Если такой директории нет, то создаем её сначала
			if(!is_dir($root_ava_dir))
			{
				$success = mkdir($root_ava_dir, 0777); // 0777 - права доступа см. chmod()
				if(!$success)
				{
					DieWithError('Не удалось создать папку для хранения аватарок пользователей!');
					return false;
				}
			}
			
			// Личная директория пользователя где будут лежать его авы=)
			$user_dir = $root_ava_dir.$pmuser_id;
			
			if(!is_dir($user_dir))
			{
				$success = mkdir($user_dir, 0777); // 0777 - права доступа см. chmod()
				if(!$success)
				{
					DieWithError('Не удалось создать папку для хранения вашей аватарки!');
					return false;
				}
			}
			
			$this->createthumb($source, $_FILES['user_ava_uploader']['type'], $user_dir.'/'.$fname,300,300);
			
			///*
			// Перемещаем загруженный файл в директорию пользователя
			// move_uploaded_file($source, $user_dir.'/'.$fname);
			
			$user = pmAuthUser::find($_SESSION['login']);
			if(!$user)
			{
				DieWithError('Ошибка при сохранении файла.');
				return false;
			}
			// Сохраняем имя аватарки
			$user->avatar = toUTF(trim($fname));
			$user->post();
			
			$val = array(
				'success'	=> true,
				'file_url'	=> '/'.$user_dir.'/'.$fname
			);
			array_walk($val, 'ConvertFromWin1251ToUTF8');
			$this->ReturnData($val);
			//*/
		}
		else
		{
			DieWithError('Не удалось загрузить файл!');
			return false;
		}
	}
	
	/**
	 * Сохранение и отправка сообщения
	 */
	function saveMessage()
	{
		if($_POST['RecipientType_id'] == 2)
			$this->inputRules['saveMessage'] = array_merge($this->inputRules['saveMessage'], $this->inputRules['GroupUser'], $this->inputRules['loadUserSearchGrid']);
		$data = $this->ProcessInputData('saveMessage', true);
		//print_r($data);
		
		switch($data['RecipientType_id']) // Кто получатель?
		{
			case 1: // Пользователи
				if(!empty($data['Users_id']))
				{
					$users = explode('|', trim($data['Users_id']));
					$recipient = $users;
				}
				else
				{
					throw new Exception('Пользователь - получатель не задан. Нельзя передавать сообщения системе.');
				}
			break;
			
			case 2: // Группа
				$addrbook = new pmAdressBooks($data['dn'],$data['group_id'],toUtf($data['group_name']), $data['group_type']);
				$response_user = $addrbook->users(false);
				for($i=0; $i<count($response_user); $i++)
				{
					if($data['pmUser_id'] != $response_user[$i]) // Исключаем из адресатов отправителя если он есть в массиве
						$users[] = $response_user[$i];
				}
				$recipient = array($data['group_id']);
			break;
			
			case 3: // все в пределах текущего ЛПУ
				$Lpu = array($data['Lpu_id']);
				$response_user = $this->dbmodel->getUsersInLpu($Lpu);
				if(!is_array($response_user))
				{
					echo json_encode(array('success' => false));
					return false;
				}
				$users = array();
				foreach($response_user as $user)
				{
					if($data['pmUser_id'] != $user['PMUser_id']) // Исключаем из адресатов отправителя если он есть в массиве
						$users[] = $user['PMUser_id'];
				}
				$recipient = $Lpu;
			break;
			
			case 4: // выбранные ЛПУ
				if(!empty($data['Lpus']))
				{
					$Lpu = explode('|', trim($data['Lpus']));
					$response_user = $this->dbmodel->getUsersInLpu($Lpu, $data['pmUser_Group']);
					if(!is_array($response_user))
					{
						echo json_encode(array('success' => false));
						return false;
					}
					$users = array();
					foreach($response_user as $user)
					{
						if($data['pmUser_id'] != $user['PMUser_id']) // Исключаем из адресатов отправителя если он есть в массиве
							$users[] = $user['PMUser_id'];
					}
					$recipient = $Lpu;
				}
			break;

			case 6: // все ЛПУ
				$Lpu = $this->dbmodel->getOpenLpus();
				$lpus = array();
				foreach ($Lpu as $key) {
					array_push($lpus, $key['Lpu_id']);
				}
				$Lpu = $lpus;
				//$Lpu = array_column($Lpu, 'Lpu_id'); // не работает на версии php 5.3, а на рабочих серверах она еще используется
				$response_user = $this->dbmodel->getUsersInLpu($Lpu, $data['pmUser_Group']);
				if(!is_array($response_user))
				{
					echo json_encode(array('success' => false));
					return false;
				}
				$users = array();
				foreach($response_user as $user)
				{
					if($data['pmUser_id'] != $user['PMUser_id']) // Исключаем из адресатов отправителя если он есть в массиве
						$users[] = $user['PMUser_id'];
				}
				$recipient = $Lpu;
			break;
			
			default: // все
				$Lpu = array();
				$response_user = $this->dbmodel->getUsersInLpu($Lpu);
				if(!is_array($response_user))
				{
					echo json_encode(array('success' => false));
					return false;
				}
				$users = array();
				foreach($response_user as $user)
				{
					if($data['pmUser_id'] != $user['PMUser_id']) // Исключаем из адресатов отправителя если он есть в массиве
						$users[] = $user['PMUser_id'];
				}
				$recipient = array(null);
			break;
		}
		
		if(count($users) == 0)
		{
			DieWithError('Пользователей в этом подразделении не найдено!');
			return false;
		}
		
		//print_r($users);
		//print_r($recipient);
		
		///*
		// Добавляем само сообщение
		$response1 = $this->dbmodel->insMessage($data);
		if(is_array($response1) && strlen($response1[0]['Error_Msg']) == 0)
		{
			$Message_id = $response1[0]['Message_id'];
		}

		// Если сообщение заинсертилось, т.е. существует его ид'шник, то добавляем связи для получателей
		if(isset($Message_id))
		{			
			for($j=0; $j<count($recipient); $j++)
			{
				$res[$j] = $this->dbmodel->insMessageLink($Message_id, $recipient[$j], $data);
				if(strlen($res[$j][0]['Error_Msg']) > 0)
				{
					break;
					DieWithError('Не удалось сохранить сообщение!');
					return false;
				}
			}
			if(!empty($data['Files']))
			{
				$files = explode('|', $data['Files']);
				$file_root_dir = FILESSPATH;
				if(!is_dir($file_root_dir))
				{
					$suc = mkdir($file_root_dir, 0777);
					if(!$suc)
					{
						DieWithError('Не удалось создать папку для хранения файлов!');
						return false;
					}
				}
				
				$mes_dir = (substr($file_root_dir, -1) == '/') ? $file_root_dir.$Message_id : $file_root_dir.'/'.$Message_id;
				if(!is_dir($mes_dir))
				{
					$suc = mkdir($mes_dir, 0777);
					if(!$suc)
					{
						DieWithError('Не удалось создать папку для хранения файлов!');
						return false;
					}
				}
				
				if($data['Message_Subject'] == 'ололо')
				{
					$this->load->library('textlog', array('file'=>'Messages_log_'.date('Y-m-d').'.log'));
					$this->textlog->add('Начинаем работу с файлами - '.count($files).' шт.');
				}
				for($i=0; $i<count($files); $i++)
				{
					$file = explode('::', $files[$i]);
					$filename = $file[0];
					$filesource = $file[1];
					if($data['Message_Subject'] == 'ололо')
					{
						$this->textlog->add('$file[0] - '.$file[0]);
						$this->textlog->add('$file[1] - '.$file[1]);
						$this->textlog->add($mes_dir.'/'.$filename);
					}
					if(!file_exists($mes_dir.'/'.$filename))
					{
						if($data['Message_Subject'] == 'ололо')
							$this->textlog->add('Ренэймим');
						if($data['Message_Subject'] != 'ололо')
							@rename($filesource, toAnsi($mes_dir.'/'.$filename, true));
						else
						{
							$this->textlog->add('Ренэймим2');
							if(!rename($filesource, toAnsi($mes_dir.'/'.$filename, true)))
							{
								$error = error_get_last();
								var_dump($error);die;
							}
						}
					}
				}
			}
		}
		else
		{
			DieWithError('Не удалось сохранить сообщение!');
			return false;
		}

		if($data['Message_isSent'] != null)
			$this->sendMessage($data, $users, $Message_id);
		else
			echo json_encode(array('success' => true));
		//*/
	}
	
	/**
	 * Отправка сообщения
	 */
	function sendMessage($data, $userrecipient, $Message_id)
	{
		for($i=0; $i<count($userrecipient); $i++)
		{
			$response[$i] = $this->dbmodel->sendMessage($data, $userrecipient[$i], $Message_id);
			if(strlen($response[$i][0]['Error_Msg']) > 0)
			{
				break;
				DieWithError('Не удалось отправить сообщение!');
				return false;
			}
		}
		echo json_encode(array('success' => true));
	}
	
	/**
	 * Получение данных сообщения
	 */
	function getMessage()
	{
		$data = $_POST;
		$response = $this->dbmodel->getMessage($data);
		
		//print_r($response);
		if(is_array($response) && count($response) > 0)
		{
			$Message_id = $response[0]['Message_id'];
			$files_folder = FILESSPATH.$Message_id;
			if(is_dir($files_folder))
			{
				$op = opendir($files_folder);
				if($op)
				{
					$files = array();
					while (false !== ($file = readdir($op)))
					{
						$file_url = $files_folder.'/'.$file;
						if(is_file($file_url))
						{
							$files[] = array(
								//'name'	=> toUtf($file, true),
								'name'	=> iconv('windows-1251', 'utf-8//IGNORE', $file),
								//'url'	=> toUtf($file_url, true),
								'url'	=> iconv('windows-1251', 'utf-8//IGNORE', $file_url),
								'size'	=> filesize($file_url)
							);
						}
					}
					closedir($op);
					$response[0]['Files'] = $files;
					$response[0]['Files_cnt'] = count($files);
				}
			}
			if (($response[0]['UserSend_ID']!=0) && ($response[0]['UserSend_ID']!=$_SESSION['pmuser_id'])) {
				$response[0]['groupsMenu'] = $this->getGroupsNoUser(array('user_id'=>$response[0]['UserSend_ID'], 'Lpu_id'=>$_SESSION['lpu_id']));
			} else {
				$response[0]['groupsMenu'] = array();
			}
			$this->ProcessModelList($response, true, true)->ReturnData();
		}
	}
	
	
	/**
	 * Получение списка пользователей для рассылки
	 */
	function getDestinations_users()
	{
		$data = $_POST;
		$response = $this->dbmodel->getDestinations_users($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}
	
	/**
	 * Получение списка ЛПУ для рассылки
	 */
	function getDestinations_lpus()
	{
		$data = $_POST;
		$response = $this->dbmodel->getDestinations_lpus($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}
	
	
	/**
	 * Удаление сообщения
	 */
	function deleteMessage()
	{
		$data = $this->ProcessInputData('deleteMessage', true);
		if($data)
		{
			if($data['FolderType_id'] == 3)
			{
				$Messagedir = FILESSPATH.$data['Message_id'];
				if(is_dir($Messagedir))
				{
					$op = opendir($Messagedir);
					if($op)
					{
						while (false !== ($file = readdir($op)))
						{
							$file_url = $Messagedir.'/'.$file;
							if(is_file($file_url))
							{
								unlink($file_url);
							}
						}
						closedir($op);
						rmdir($Messagedir);
					}
				}
			}
			$response = $this->dbmodel->deleteMessage($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
		}
	}
	
	/**
	 * Удаление сообщений
	 */
	function deleteMessages()
	{
		$Message_ids = array();
		$MessageRecipient_ids = array();
		
		$data = $this->ProcessInputData('deleteMessages', true);
		
		if($data)
		{
			if (!empty($data['Message_ids']))
			{
				$Message_ids = json_decode($data['Message_ids']);
			}
			
			if (!empty($data['MessageRecipient_ids']))
			{
				$MessageRecipient_ids = json_decode($data['MessageRecipient_ids']);
			}
			
			if($data['FolderType_id'] == 3)
			{
				foreach($Message_ids as $Message_id) {
					$Messagedir = FILESSPATH.$Message_id;
					if(is_dir($Messagedir))
					{
						$op = opendir($Messagedir);
						if($op)
						{
							while (false !== ($file = readdir($op)))
							{
								$file_url = $Messagedir.'/'.$file;
								if(is_file($file_url))
								{
									unlink($file_url);
								}
							}
							closedir($op);
							rmdir($Messagedir);
						}
					}
				}
			}
			
			$data['Message_ids'] = $Message_ids;
			$data['MessageRecipient_ids'] = $MessageRecipient_ids;
			
			$response = $this->dbmodel->deleteMessages($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
		}
	}
	
	/**
	 * Отметка сообщения прочитанным
	 */
	function setMessageIsRead()
	{
		$data = $this->ProcessInputData('setMessageIsRead', true);
		if($data)
		{
			$response = $this->dbmodel->setMessageIsRead($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
		}
	}
	
	/**
	 * Отметка сообщения непрочитанным
	 */
	function setMessageActive()
	{
		$data = $this->ProcessInputData('setMessageActive', true);
		if($data)
		{
			$response = $this->dbmodel->setMessageActive($data);
			if(!$response[0]['Error_Msg'])
			{
				$resp = $this->dbmodel->getFlagMessage($data);
				$this->ProcessModelList($resp, true, true)->ReturnData();
			}
		}
	}
	
	/**
	 * Получение данных отправителя
	 */
	function getUsersSend()
	{
		$data = $_SESSION;
		if($data)
		{
			$response = $this->dbmodel->getUsersSend($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
		}
	}
	
	/**
	 * Аплоад файлов сообщения
	 */
	function uploadMessageFiles()
	{
		// http://redmine.swan.perm.ru/issues/20702
		if ( !is_dir(IMPORTPATH_ROOT) ) {
			mkdir(IMPORTPATH_ROOT) or die(json_encode(array('success' => false, 'error' => toUTF('Не определена папка для хранения загруженных файлов!'))));
		}

		if ( !is_dir(FILESSPATH) ) {
			mkdir(FILESSPATH) or die(json_encode(array('success' => false, 'error' => toUTF('Не определена папка для хранения загруженных файлов!'))));
		}

		//print_r($_FILES);
		if(!isset($_FILES['file']))
		{
			echo json_encode(array('success' => false, 'error' => toUTF('Ошибка загрузки файла!')));
			return false;
		}
		if((int)$_FILES['file']['size'] > 7340032)
		{
			echo json_encode(array('success' => false, 'error' => toUTF('Запрещено загружать файлы размером более 7 мб!')));
			return false;
		}
		if($_FILES['file']['tmp_name'] == '')
		{
			echo json_encode(array('success' => false, 'error' => toUTF('Ошибка загрузки файла!')));
			return false;
		}
		$newfile = explode('\\',$_FILES['file']['tmp_name']);
		$newname = FILESSPATH.str_replace('.', '', $newfile[count($newfile)-1]).rand(1,10000).'.tmp';
		$flag = @rename($_FILES['file']['tmp_name'], $newname);
		if(!$flag)
		{
			echo json_encode(array('success' => false, 'error' => toUTF('Ошибка загрузки файла!')));
			return false;
		}
		
		$val = array(
			'name'		=> toUTF($_FILES['file']['name']),
			'tmp_name'	=> toUTF($newname),
			'size'		=> $_FILES['file']['size'],
			'success'	=> true
		);
		$this->ReturnData($val);
	}
	
	/**
	 * Удаление файлов сообщения
	 */
	function deleteFile()
	{
		$data = $this->ProcessInputData('deleteFile', true);
		//print_r($data);
		if(file_exists($data['url']))
		{
			$data['Message_id'] = (int) trim(mb_substr(dirname($data['url']), strlen(FILESSPATH)));
			$resp = $this->dbmodel->getMessage($data);
			if(is_array($resp) && isset($resp[0]['UserSend_ID']))
			{
				$fileowner = $resp[0]['UserSend_ID'];
				if($fileowner !== $_SESSION['pmuser_id'])
				{
					DieWithError('У вас нет прав для удаления этого файла!');
					return false;
				}
				if(unlink($data['url']))
					echo json_encode(array('success' => true));
				else
					echo json_encode(array('success' => false));
			}
		}
		else
		{
			DieWithError('Файла с таким именем не существует!');
			return false;
		}
	}
	
	/**
	 * Нотификация о новых сообщениях
	 */
	function getNewMessages()
	{
		$this->load->library('parser');
		$response = array();
		$data = $this->ProcessInputData(null, true);
		if ($data) {
			$response = $this->dbmodel->getNewMessages($data);
			// обработка - возврат темплейта 
			$row = array();
			if (isset($response['data']) && (count($response['data'])>0)) {
				for($i=0; $i<count($response['data']); $i++) {
					// Формирование внешнего вида сообщения 
					$r = $response['data'][$i];
					switch ($r['NoticeType_id']) {
						case 1: $row['class'] = 'mail'; break;
						default: $row['class'] = 'info'; break;
					}
					
					$author = $r['PMUser_Name'].((!empty($r['Lpu_Nick']))?'('.$r['Lpu_Nick'].((!empty($r['Dolgnost_Name']))?','.$r['Dolgnost_Name']:'').')':'');
					$title = $r['Message_Subject'];
					$text = (strlen($r['Message_Text']>150))?mb_substr($r['Message_Text'],0,150).' ...':$r['Message_Text'];
					
					$row['html'] = '<i>'.$author.':</i><br/>'.'<b>'.$title.'</b>'.':<br/>'.$text;
					
					$response['data'][$i]['Message_Subject'];
					$response['data'][$i]['msg'] = $this->parser->parse('popup-msg', $row, true);
					
				}
			}
			$this->ProcessModelMultiList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * Нотификация о новых сообщениях (в новый интерфейс)
	 */
	function getNewMessagesExt6()
	{
		$response = array();
		$data = $this->ProcessInputData(null, true);
		if ($data) {
			$response = $this->dbmodel->getNewMessagesExt6($data);
			$this->ProcessModelMultiList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Нотификация о сообщениях от Админа ЦОД
	 */
	function getAdminMessages()
	{
		$this->load->library('parser');
		$this->load->helper('Main');
		$response = array();
		$data = $this->ProcessInputData(null, true);
		if ($data) {
			$response = $this->dbmodel->getAdminMessages($data);
			$row = array();
			if (isset($response['totalCount']) && (count($response['totalCount'])>0) && !empty($response['totalCount'])) {
				$row['class'] = 'mail';
				$row['html'] = '<b>Внимание! Имеется '.$response['totalCount'].' непрочтенн'.(($response['totalCount'] == 1)?'ое':'ых');
				$row['html'] .= ' сообщени'.(($response['totalCount'] == 1)?'е':'й');
				$row['html'] .= ' от Администратора системы Промед</b>';
				$response['data'][0] = array();
				$response['data'][0]['msg'] = $this->parser->parse('popup-msg', $row, true);
			}
			$this->ProcessModelMultiList($response, true, true, 'При запросе возникла ошибка.', NULL, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Нотификация о назначении вызова на бригаду
	 */
	function sendNotificationEmergencyTeam()
	{
		$data = $this->ProcessInputData('sendNotificationEmergencyTeam', true);
		$res = $this->dbmodel->sendNotificationEmergencyTeam($data);
		if ($res['IsSMS'] == '2' && !empty($res['SIM_number'])) {
			$response = array('success' => false, 'Error_Msg' => '');

			$text = "Бригада № " . $data['EmergencyTeam_Num'] . " назначена на вызов № " . $data['Numv'] . " по адресу: " . $data['Adress_Name'] . ", пациент: " . $data['Person_FIO'] . ", СР: " . $data['Urgency'];
			$this->load->helper('Notify');
			log_message('error','sendNotificationEmergencyTeam text ' . $text );
			try {
				$response['test'] = sendNotifySMS(array(
					'User_id' => $data["pmUser_id"],
					'UserNotify_Phone' => str_replace(array('+7(', ')', '-'), "", $res['SIM_number']),
					'text' => $text
				));
			} catch (Exception $e) {
				$response['Error_Msg'] = toUtf($e->getMessage());
				$this->ReturnData($response);
				return false;
			}

			$response['success'] = true;
			$this->ProcessModelSave($response, true, 'Ошибка при отправке смс оповещения')->ReturnData();
			return true;
		}
	}
	/**
	 * Получение количества непрочитанных сообщений.
	 * используется в виджете уведомлений (колокольчик)
	 * в главном тулбаре Промеда
	 */
	function getUnreadNoticeCount() {
		$data = $this->ProcessInputData(null, true);
		if (!$data) {
			return false;
		}
		
		$response = $this->dbmodel->getUnreadNoticeCount($data);
		
		$result = array('success'=>true, 'data' => $response);
		$this->ReturnData($result);
		
		return true;
	}
	/**
	 * Получение списка сообщений.
	 * используется в виджете уведомлений (колокольчик)
	 * в главном тулбаре Промеда
	 */
	function getMessagesList() {
		$data = $this->ProcessInputData('getMessagesList', true);
		if (!$data) {
			return false;
		}
		
		$response = $this->dbmodel->getMessagesListData($data);
		
		$result = array('success'=>true, 'data' => $response);
		$this->ReturnData($result);
		
		return true;
	}
	/**
	 * Сделать все сообщения прочитанными
	 * используется в виджете уведомлений (колокольчик)
	 * в главном тулбаре Промеда
	 */
	function setMessagesIsReaded() {
		$data = $this->ProcessInputData(null, true);
		
		$response = $this->dbmodel->setMessagesIsReaded($data);
		
		$result = array('success'=>true, 'data' => $response);
		$this->ReturnData($result);
		
		return true;
	}
}