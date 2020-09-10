<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * AccessRightsDiag - контроллер для работы c правами доступа к тестам
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Admin
 * @access			public
 * @copyright		Copyright (c) 2014 Swan Ltd.
 *
 * @property AccessRightsTest_model dbmodel
 */
require_once('AccessRights.php');

class AccessRightsPrivilegeType extends AccessRights {
	protected $model_name = 'AccessRightsPrivilegeType_model';

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();
		$this->inputRules = array_merge(
			$this->inputRules,
			array(
				'saveAccessRights' => array(
					array(
						'field' => 'AccessRightsPrivilegeType_id',
						'label' => 'Идентификатор доступа',
						'rules' => '',
						'type' => 'id'
					),
					array(
						'field' => 'AccessRightsName_id',
						'label' => 'Идентификатор наименования группы доступа',
						'rules' => '',
						'type' => 'id'
					),
					array(
						'field' => 'AccessRightsName_Name',
						'label' => 'Наименования группы доступа',
						'rules' => '',
						'type' => 'string'
					),
					array(
						'field' => 'AccessRightsName_Code',
						'label' => 'Код группы доступа',
						'rules' => '',
						'type' => 'int'
					),
					array(
						'field' => 'PrivilegeType_id',
						'label' => 'Закрытая льгота',
						'rules' => 'required',
						'type' => 'id'
					)
				)
			)
		);
	}
}