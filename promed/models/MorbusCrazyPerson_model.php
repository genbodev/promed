<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * MorbusCrazyPerson_model - Model to work with MorbusCrazyPerson
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2009-2010 Swan Ltd.
 * @author       IGabdushev
 * @version      12 2011
 */
/**
 * @property int      Person_id                            Человек
 * @property DateTime MorbusCrazyPerson_firstDT            Дата обращения к психиатру (наркологу) впервые в жизни
 * @property int      InvalidGroupType_id                  Инвалидность по общему заболеванию
 * @property int      MorbusCrazyPerson_IsWowInvalid       Инвалид ВОВ
 * @property int      MorbusCrazyPerson_IsWowMember        Участник ВОВ
 * @property int      CrazyEducationType_id                Образование
 * @property int      MorbusCrazyPerson_CompleteClassCount Число законченных классов среднеобразовательного учреждения
 * @property int      MorbusCrazyPerson_IsEducation        Учится
 * @property int      CrazySourceLivelihoodType_id         Источник средств существования
 * @property int      CrazyResideType_id                   Проживает
 * @property int      CrazyResideConditionsType_id         Условия проживания
 * @property int      MorbusCrazyPerson_id
 */
class MorbusCrazyPerson_model extends Abstract_model
{
    /**
     * @return string
     */
    protected function getTableName()
    {
        return 'MorbusCrazyPerson';
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
	 * MorbusCrazyPerson_model constructor.
	 */
    function __construct()
    {
        parent::__construct();
    }

	/**
	 * @param null $field
	 * @param null $value
	 * @return bool|mixed
	 * @throws Exception
	 */
    function load($field = null, $value = null)
    {
        if ($this->MorbusCrazyPerson_id) {
            return parent::load($field, $value);
        } else {
            if ($this->Person_id) {
                return parent::load('Person_id', $this->Person_id);
            } else {
                throw new Exception('Для загрузки специфики человека по психиатрии/наркологии требуется указать ее идентификатор или идентификатор человека');
            }
        }
    }

	/**
	 * @param array $values
	 * @throws Exception
	 */
    public function assign($values)
    {
        parent::assign($values);
        //Если у этого Человека уже есть психоспецифика, находим ее ИД, чтобы при сохранении новые данные легли в ту же запись
        $this_id = $this->getFirstResultFromQuery("Select {$this->getKeyFieldName()} from {$this->getSourceTableName()} where Person_id = :Person_id", array('Person_id' => $this->Person_id));
        if ($this_id) {
            $this->__set($this->getKeyFieldName(), $this_id);
        }
    }


    protected $fields = array(
        'Person_id'                            => null,//Человек
        'MorbusCrazyPerson_firstDT'            => null,//Дата обращения к психиатру (наркологу) впервые в жизни
        'InvalidGroupType_id'                  => null,//Инвалидность по общему заболеванию
        'MorbusCrazyPerson_IsWowInvalid'       => null,//Инвалид ВОВ
        'MorbusCrazyPerson_IsWowMember'        => null,//Участник ВОВ
        'CrazyEducationType_id'                => null,//Образование
        'MorbusCrazyPerson_CompleteClassCount' => null,//Число законченных классов среднеобразовательного учреждения
        'MorbusCrazyPerson_IsEducation'        => null,//Учится
        'CrazySourceLivelihoodType_id'         => null,//Источник средств существования
        'CrazyResideType_id'                   => null,//Проживает
        'CrazyResideConditionsType_id'         => null,//Условия проживания
        'MorbusCrazyPerson_id'                 => null,
        'pmUser_id'=> null
    );

	/**
	 * @return $this
	 */
    public function validate()
    {
        $this->valid = true;

		// Необязательные поля со ссылками на справочники
		$fields_reference = array(
			'Инвалидность по общему заболеванию' => 'InvalidGroupType',
			'Образование' => 'CrazyEducationType',
			'Источник средств существования' => 'CrazySourceLivelihoodType',
			'Проживает' => 'CrazyResideType',
			'Условия проживания' => 'CrazyResideConditionsType'
		);
		// Проверки необязательных полей по справочникам
		foreach ($fields_reference as $label => $name) {
			$field = $name . '_id';
			if ($this->$field != NULL) {
				$this->validateByReference($name, $label);
			}
		}

		// Необязательные поля с признаком да/нет
		$fields_reference = array(
			'Инвалид ВОВ' => 'MorbusCrazyPerson_IsWowInvalid',
			'Участник ВОВ' => 'MorbusCrazyPerson_IsWowMember',
			'Учится' => 'MorbusCrazyPerson_IsEducation'
		);
		// Проверки необязательных полей да/нет
		foreach ($fields_reference as $label => $field) {
			if ($this->$field != NULL) {
				$this->validateYesNo($field, $label);
			}
		}

		return $this;
    }

}