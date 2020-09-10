<?php

defined('BASEPATH') or die('No direct script access allowed');
/**
 * MorbusCrazyBaseDrugStart_model - Возраст начала употребления психоактивных средств
 *
 * Promed
 * http://swan.perm.ru/projects/promed
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2009-2011 Swan Ltd.
 * @author       Alexander "Alf" Arefyev <avaref@gmail.com>
 * @version      12 2011
 */
require_once('Abstract_model.php');

/**
 * @property int $MorbusCrazyBaseDrugStart_id    Идентификатор
 * @property int $MorbusCrazyBase_id                Идентификатор общего заболевания по психиатрии/наркологии
 * @property string $MorbusCrazyBaseDrugStart_Name    Наименование
 * @property int $CrazyDrugReceptType_id            Тип приема
 * @property int $MorbusCrazyBaseDrugStart_Age    Число полных лет
 * @property int $pmUser_id                        Пользователь
 * @property MorbusCrazyBase_model $MorbusCrazyBase Модель специфики общего заболевания по психиатрии/наркологии
 */
class MorbusCrazyBaseDrugStart_model extends Abstract_model
{

    var $fields = [
        'MorbusCrazyBaseDrugStart_id' => null,
        'MorbusCrazyBaseDrugStart_Name' => null,
        'CrazyDrugReceptType_id' => null,
        'MorbusCrazyBaseDrugStart_Age' => null,
        'pmUser_id' => null
    ];

    private $MorbusCrazyBase;

    /**
     * @return string
     */
    protected function getTableName()
    {
        return 'MorbusCrazyBaseDrugStart';
    }

    /**
     * @return bool
     */
    protected function canDelete()
    {
        //todo реализовать проверки при удалении
        return true;
    }

    /**
     * MorbusCrazyBaseDrugStart_model constructor.
     */
    function __construct()
    {
        parent::__construct();
        $this->load->model('MorbusCrazyBase_model', 'MorbusCrazyBase');
    }

    /**
     * Проверки
     */
    public function validate()
    {
        $this->valid = true;
        $this->clearErrors();

        // Обязательные поля со ссылками на справочники
        $fields_required_reference = [
            'Наименование' => 'MorbusCrazyBaseDrugStart_Name',
            'Число полных лет' => 'MorbusCrazyBaseDrugStart_Age'
        ];

        // Проверки обязательных полей и существование в справочниках
        foreach ($fields_required_reference as $label => $name) {
            if ($this->validateRequired($name, $label)) {
                $this->validateByReference($name, $label);
            }
        }

        // Тип приема
        if ($this->validateRequired('CrazyDrugReceptType_id', 'Тип приема')) {
            $this->validateByReference('CrazyDrugReceptType', 'Тип приема');
        }

        return $this;
    }

    /**
     * Загрузка
     *
     * @return array|bool
     * @throws Exception
     */
    public function load()
    {
        $params = [
            'MorbusCrazyBase_id' => $this->MorbusCrazyBase_id
        ];

        $query = "
			select
				MorbusCrazyBaseDrugStart_id as \"MorbusCrazyBaseDrugStart_id\",
				MorbusCrazyBase_id as \"MorbusCrazyBase_id\",
				MorbusCrazyBaseDrugStart_Name as \"MorbusCrazyBaseDrugStart_Name\",
				CrazyDrugReceptType_id as \"CrazyDrugReceptType_id\",
				MorbusCrazyBaseDrugStart_Age as \"MorbusCrazyBaseDrugStart_Age\",
				pmUser_insID as \"pmUser_insID\",
				pmUser_updID as \"pmUser_updID\",
				MorbusCrazyBaseDrugStart_insDT as \"MorbusCrazyBaseDrugStart_insDT\",
				MorbusCrazyBaseDrugStart_updDT as \"MorbusCrazyBaseDrugStart_updDT\"
			from
				{$this->scheme}.v_MorbusCrazyBaseDrugStart
			where
				MorbusCrazyBase_id = :MorbusCrazyBase_id
		";
        $result = $this->db->query($query, $params);
        if (!is_object($result))
            return false;

        $response = $result->result('array');
        $this->assign($response[0]);
        return $response;
    }

    /**
     * @param array $values
     * @throws Exception
     */
    public function assign($values)
    {
        parent::assign($values);
        if (isset($values['MorbusCrazyBase_id']) && $values['MorbusCrazyBase_id']) {
            $this->MorbusCrazyBase->MorbusCrazyBase_id = $values['MorbusCrazyBase_id'];
            $this->MorbusCrazyBase->load();
        }
    }

    /**
     * Удаление
     *
     * @param array $data
     * @return array|bool
     */
    public function delete($data = [])
    {
        $params = [
            'MorbusCrazyBaseDrugStart_id' => $data['MorbusCrazyBaseDrugStart_id'],
			'pmUser_id' => $data['pmUser_id']
        ];

        $query = "
            select
                Error_Code as \"Error_Code\",
                Error_Message as \"Error_Msg\";
			from {$this->scheme}.p_MorbusCrazyBaseDrugStart_del
			(
				MorbusCrazyBaseDrugStart_id := :MorbusCrazyBaseDrugStart_id,
				pmUser_id := :pmUser_id
			)
		";
        $result = $this->db->query($query, $params);
        if (!is_object($result))
            return false;

        return $result->result('array');
    }

    /**
     * Добавляет параметр
     *
     * @param $params
     * @param $query_paramlist_exclude
     * @return array
     */
    protected function getParamList($params, $query_paramlist_exclude)
    {
        list($params, $query_paramlist) = parent::getParamList($params, $query_paramlist_exclude);
        $params['MorbusCrazyBase_id'] = $this->MorbusCrazyBase->MorbusCrazyBase_id;
        $query_paramlist = $query_paramlist . ' MorbusCrazyBase_id := :MorbusCrazyBase_id,';
        return [$params, $query_paramlist];
    }

}
