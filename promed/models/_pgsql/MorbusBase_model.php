<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * MorbusBase_model - модель общего заболевания
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
require_once('Abstract_model.php');
/**
 * @property int $MorbusBase_id Идентификатор общего заболевания
 * @property int $MorbusType_id Тип заболевания
 * @property Datetime $MorbusBase_setDT Дата начала заболевания
 * @property Datetime $MorbusBase_disDT Дата окончания заболевания
 * @property int $MorbusResult_id Исход заболевания
 * @property int $pmUser_id Пользователь
 * @property int Evn_pid Учетный документ, из которого было создано заболевание
 * @property int $Person_id Пациент
 */
class MorbusBase_model extends Abstract_model{

	/**
	 * Doc-блок
	 */
    protected function getTableName()
    {
        return 'MorbusBase';
    }

	/**
	 * Doc-блок
	 */
    protected function canDelete()
    {
        //todo реализовать проверки при удалении
        return true;
    }

    var $fields = array(
        'MorbusBase_id' => null,
        'Person_id' => null,
        'Evn_pid' => null,
        'MorbusType_id' => null,
        'MorbusBase_setDT' => null,
        'MorbusBase_disDT' => null,
        'MorbusResult_id' => null,
        'pmUser_id' => null
    );

	/**
	 * Doc-блок
	 */
    public function validate(){
        $this->valid = true;
        $this->clearErrors();
        //– Тип заболевания. Обязательный атрибут. Выбор из справочника:
        if ($this->validateRequired('MorbusType_id', 'Тип заболевания')) {
            // проверить существует ли тип заболевания с таким ид
			if ($this->validateByReference('MorbusType', 'Тип общего заболевания')) {
			    if (!$this->checkPersonUnique()){
       		        $this->addError('На этом человеке уже есть заболевание с таким типом');
                }
            }
        }
        //– Дата начала заболевания. Обязательный атрибут.
        if ($this->validateRequired('MorbusBase_setDT', 'Дата начала заболевания')) {
            // проверить является ли датой
			if (!is_object($this->MorbusBase_setDT) || get_class($this->MorbusBase_setDT) != 'DateTime') {
		        $this->addError('Неверный формат даты начала общего заболевания');
			}
            //– Дата окончания заболевания. Необязательный атрибут. Не может быть больше текущей даты и меньше даты начала заболевания.
            if (null !== $this->MorbusBase_disDT) {
                if (!is_object($this->MorbusBase_setDT) || get_class($this->MorbusBase_setDT) != 'DateTime') {
       		        $this->addError('Неверный формат даты окончания общего заболевания');
                } else {
                    if ($this->MorbusBase_disDT < $this->MorbusBase_setDT){
                        $this->addError('Дата окончания общего заболевания наступила раньше даты начала общего заболевания');
                    }
                    //– Исход заболевания. Обязательно для заполнения, если заполнен атрибут «Дата окончания» заболевания
                    if (null === $this->MorbusResult_id) {
                        $this->addError('Исход общего заболевания обязательно для заполнения, если указана дата окончания общего заболевания');
                    } else {
                        // проверить существует ли исход заболевания с таким ид
						$this->validateByReference('MorbusResult', 'Исход заболевания');
                    }
                }
            }
        }
        return $this;
    }

	/**
	 * Doc-блок
	 */
    private function checkPersonUnique(){
        $param = array();
        $condition = '';
        if ($this->MorbusBase_id) {
            $param['MorbusBase_id'] = $this->MorbusBase_id;
            $condition = ' AND MorbusBase_id<>:MorbusBase_id';
        }
        $param['MorbusType_id'] = $this->MorbusType_id;
        $param['Person_id'] = $this->Person_id;
        $count = $this->getFirstResultFromQuery("SELECT count(*) FROM {$this->getSourceTableName()} WHERE Person_id = :Person_id AND MorbusType_id = :MorbusType_id $condition", $param);
        $result = ($count === 0);
        return $result;
    }

	/**
	 * Doc-блок
	 */
    function load($field = null, $value = null, $selectFields = '*', $addNameEntries = true)
    {
        return parent::load($field, $value, $selectFields, $addNameEntries);
    }

    /**
     * Определение типа заболевания по диагнозу $diag_id. Если диагнозу $diag_id не сопоставлен никакой тип
     * в таблице MorbusTypeDiag, возвращается MorbusType_id = 1 (Общий тип)
     *
     * @param $diag_id
     * @return int
     */
    public function getMorbusTypeByDiag($diag_id){
        $result = 1;
        $MorbusType_id = $this->getFirstResultFromQuery('SELECT MorbusType_id as \"MorbusType_id\" FROM dbo.v_MorbusDiag WHERE Diag_id = :Diag_id',array('Diag_id'=>$diag_id));
        if ($MorbusType_id) {
            $result = $MorbusType_id;
        }
        return $result;
    }

    /**
     * Определение типа заболевания по диагнозу $diag_id. Если диагнозу $diag_id не сопоставлен никакой тип
     * в таблице MorbusTypeDiag, возвращается MorbusType_id = 1 (Общий тип)
     *
     * @param $diag_id
     * @return array
     */
    public function getMorbusTypesByDiag($diag_id){
        $response = array(1);
        $query = 'SELECT MorbusType_id as \"MorbusType_id\" FROM dbo.v_MorbusDiag WHERE Diag_id = :Diag_id';
		$result = $this->db->query($query, array('Diag_id'=>$diag_id));
		if (is_object($result)) {
			$resp = $result->result('array');
			if (count($resp) > 0) {
				$response = array();
				foreach($resp as $respone) {
					$response[] = $respone['MorbusType_id'];
				}
			}
		}
        return $response;
    }

    /**
     * Возвращает количество активных простых заболеваний на указанную дату, кроме указанного
     * @param $date Datetime
     * @param $exceptThis_id int
     * @return int
     */
    public function activeMorbusCount($date, $exceptThis_id = null){
        $condition = '';
        $params = array();
        $params['MorbusBase_id'] = $this->MorbusBase_id;
        $params['date'] = $date;
        if ($exceptThis_id) {
            $condition = 'AND Morbus_id <> :Morbus_id';
            $params['Morbus_id'] = $exceptThis_id;
        }
        $result = (int)$this->getFirstResultFromQuery("SELECT COUNT(*) FROM v_Morbus WHERE (:date BETWEEN Morbus_SetDT and COALESCE(Morbus_DisDT, :date)) AND MorbusBase_id = :MorbusBase_id $condition", $params);
        return $result;
    }

    /**
     * Возвращает количество простых заболеваний на указанную дату, кроме указанного
     *
     * @param null $exceptThis_id
     * @return int
     */
    public function morbusCount($exceptThis_id = null){
        $condition = '';
        $params = array();
        $params['MorbusBase_id'] = $this->MorbusBase_id;
        if ($exceptThis_id) {
            $condition = 'AND Morbus_id <> :Morbus_id';
            $params['Morbus_id'] = $exceptThis_id;
        }
        $result = (int)$this->getFirstResultFromQuery("SELECT COUNT(*) FROM v_Morbus WHERE MorbusBase_id = :MorbusBase_id $condition", $params);
        return $result;
    }

    /**
     * Сдвигает временные границы общего заболевания таким образом, чтобы уместить все относящиеся к нему простые заболевания.
     * Если последнее из заболеваний еще активно (дата окончания простого заболевания не проставлена), то установит дату окончания
     * общего заболевания пустой (NULL)
     * Если активных заболеваний нет, проставит исход заболевания такой же как у закрытого последним простого заболевания
     *
     * @return array|mixed
     */
    public function updateBoundaries(){
        $this->MorbusBase_setDT = $this->getFirstResultFromQuery(
            'SELECT MIN(m.Morbus_setDT) FROM v_Morbus m WHERE m.MorbusBase_id = :MorbusBase_id',
            array('MorbusBase_id' => $this->MorbusBase_id)
        );
        $this->MorbusBase_disDT = $this->getFirstResultFromQuery(
            'SELECT (
              CASE
                WHEN EXISTS (SELECT 1 FROM v_Morbus m  WHERE m.MorbusBase_id = :MorbusBase_id AND m.Morbus_disDT IS null)
            		THEN NULL
                ELSE
            		(SELECT MAX(m.Morbus_disDT) FROM v_Morbus m WHERE m.MorbusBase_id = :MorbusBase_id)
              END)',
            array('MorbusBase_id' => $this->MorbusBase_id)
        );
        if ($this->MorbusBase_disDT) {
            $this->MorbusResult_id = $this->getFirstResultFromQuery(
                '
                SELECT MorbusResult_id FROM dbo.v_Morbus WHERE morbusBase_id = :MorbusBase_id AND Morbus_disDT =
                (SELECT MAX(Morbus_disDT) FROM v_Morbus WHERE morbusBase_id = :MorbusBase_id) limit 1
                ',
                array('MorbusBase_id' => $this->MorbusBase_id)
            );
        } else {
            $this->MorbusResult_id = null;
        }
        return $this->save();
    }

}