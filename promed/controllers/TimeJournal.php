<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * TimeJournal - контроллер для управления журналом учета рабочего времени
 * сотрудников.
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2009 Swan Ltd.
 * @author
 * @version      12.2019
 *
 * @property TimeJournal_model dbmodel
 */
class TimeJournal extends swController
{
/**
 * Конструктор
 */
	function __construct()
	{
		parent::__construct();

		$this->load->library('textlog', array('file'=>'LDAP_'.date('Y-m-d').'.log'));

		$this->inputRules =
		array(
			'saveTimeJournalRecord' =>
				array(
					array(
						'field' => 'TimeJournal_id',
						'label' => 'Идентификатор записи в журнале',
						'rules' => '',
						'type' => 'string'
					),
					array(
						'field' => 'pmUser_id',
						'label' => 'Идентификатор пользователя, заносящего изменения',
						'rules' => '',
						'type' => 'id'
					),
					array(
						'field' => 'pmUser_tid',
						'label' => 'Идентификатор пользователя, к которому относится смена',
						'rules' => '',
						'type' => 'id'
					),
					array(
						'field' => 'TimeJournal_BegDT',
						'label' => 'Дата и время начала смены',
						'rules' => '',
						'type' => 'string'
					),
					array(
						'field' => 'TimeJournal_EndDT',
						'label' => 'Дата и время завершения смены',
						'rules' => '',
						'type' => 'string'
					),
					array(
						'field' => 'Server_id',
						'label' => 'Идентификатор источника данных (ЛПУ)',
						'rules' => '',
						'type' => 'id'
					)
				),
			'loadTimeJournal' =>
				array(
					array(
						'field' => 'Lpu_id',
						'label' => 'Идентификатор ЛПУ',
						'rules' => '',
						'type' => 'id'
					),
					array(
						'field' => 'MedStaffFact_id',
						'label' => 'Идентификатор врача',
						'rules' => '',
						'type' => 'id'
					),
					array(
						'field' => 'pmUser_tid',
						'label' => 'Идентификатор пользователя',
						'rules' => '',
						'type' => 'id'
					),
					array(
						'field' => 'currentDateTime',
						'label' => 'Текущие дата и время',
						'rules' => '',
						'type' => 'string'
					),
					array(
						'field' => 'minBegDT',
						'label' => 'Минимальные дата и время начала смены',
						'rules' => '',
						'type' => 'string'
					),
					array(
						'field' => 'maxBegDT',
						'label' => 'Максимальные дата и время завершения смены',
						'rules' => '',
						'type' => 'string'
					),
					array(
						'field' => 'minEndDT',
						'label' => 'Минимальные дата и время завершения смены',
						'rules' => '',
						'type' => 'string'
					),
					array(
						'field' => 'maxEndDT',
						'label' => 'Максимальные дата и время завершения смены',
						'rules' => '',
						'type' => 'string'
					),
					array(
						'field' => 'fullInfo',
						'label' => 'Нужны ли ФИО и табельный №',
						'rules' => '',
						'type' => 'boolean'
					)
				)
		);
	}

/**
 * Получение записей журнала с заданной фильтрацией.
 */
	function loadTimeJournal()
	{
		$data = $this->ProcessInputData('loadTimeJournal', true);
		$this->load->database();
		$this->load->model("TimeJournal_model", "dbmodel");

		if ($data)
		{
			$res = $this->dbmodel->loadTimeJournal($data);
			echo json_encode($res);

			return true;
		}
		else
			return false;
	}

/**
 * Создание новой или редактирование имеющейся записи в журнале, в
 * в зависимости от того, передан ли идентификатор записи.
 */
	function saveTimeJournalRecord()
	{
		$data = $this->ProcessInputData('saveTimeJournalRecord', true);

		$this->load->database();
		$this->load->model("TimeJournal_model", "dbmodel");

		$res = $this->dbmodel->saveTimeJournalRecord($data);

		echo json_encode($res);
	}
}
?>
