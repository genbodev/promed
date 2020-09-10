<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * MorbusOnkoPerson_model - Model to work with MorbusOnkoPerson
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
 * Онкоспецифика человека
 *
 * Person has many MorbusOnkoPerson 1:0..*
 * MorbusOnkoPerson.Person_id = Person.Person_id
 *
 * @property integer MorbusOnkoPerson_id PK
 * @property integer Person_id Человек
 * @property integer Ethnos_id этническая группа
 * @property integer OnkoOccupationClass_id социально-профессиональная группа
 * @property integer KLAreaType_id житель
 */
class MorbusOnkoPerson_model extends Abstract_model
{
    /**
     * @return string
     */
    protected function getTableName()
    {
        return 'MorbusOnkoPerson';
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
	 * MorbusOnkoPerson_model constructor.
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
        if ($this->MorbusOnkoPerson_id) {
            return parent::load($field, $value);
        } else {
            if ($this->Person_id) {
                return parent::load('Person_id', $this->Person_id);
            } else {
                throw new Exception('Для загрузки специфики человека по онкологии требуется указать ее идентификатор или идентификатор человека');
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
        //Если у этого Человека уже есть онкоспецифика, находим ее ИД, чтобы при сохранении новые данные легли в ту же запись
        $this_id = $this->getFirstResultFromQuery("Select {$this->getKeyFieldName()} from {$this->getSourceTableName()} where Person_id = :Person_id", array('Person_id' => $this->Person_id));
        if ($this_id) {
            $this->__set($this->getKeyFieldName(), $this_id);
        }
    }


    protected $fields = array(
        'MorbusOnkoPerson_id'    => null,//NULL
        'Person_id'              => null,//Человек
        'Ethnos_id'              => null,//Этническая группа
        'OnkoOccupationClass_id' => null,//Социально-профессиональная группа
        'KLAreaType_id'          => null,//Житель
        'pmUser_id'    => null,
    );

	/**
	 * @return $this
	 */
    public function validate()
    {
        $this->valid = true;


		return $this;
    }

	/**
	 * @return array|mixed
	 */
	function save()
	{
		return parent::save();
	}

	/**
	 * @param $Person_id
	 * @return bool|float|int|string
	 */
	public function getIdByPerson_id($Person_id)
	{
		return $this->getFirstResultFromQuery("SELECT MorbusOnkoPerson_id FROM v_{$this->getTableName()} with(nolock) WHERE Person_id = :Person_id", array( 'Person_id' => $Person_id));
	}


}