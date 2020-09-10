<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * Abstract_model - Модель сферического объекта в вакууме
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2009-2010 Swan Ltd.
 * @author       IGabdushev
 * @version      12 2011
 *
 * @deprecated
 * @property ModelGenerator_model ModelGenerator_model
 */
abstract class Abstract_model extends swModel {

	/**
	 * Method description
	 */
	protected abstract function canDelete();
	private $rawLoadResult = array();

    public $transactional = true;//Разрешить объекту начинать/откатывать/коммитить

	/**
	 * Method description
	 */
    protected function start_transaction(){
        if ($this->transactional) {
            return $this->db->trans_begin();
        } else {
            return true;
        }
    }

	/**
	 * Method description
	 */
    protected function commit(){
        if ($this->transactional) {
            return $this->db->trans_commit();
        } else {
            return true;
        }
    }

	/**
	 * Method description
	 */
    protected function rollback(){
        if ($this->transactional) {
            return $this->db->trans_rollback();
        } else {
            return true;
        }
    }

    /**
     * @abstract
     * @return string
     */
    protected function getKeyFieldName(){
        return $this->getTableName().'_id';
    }

    /**
     * @abstract
     * @return string
     */
    protected function getSaveProcedureName(){
        return 'p_'.$this->getTableName();
    }

	/**
	 * Method description
	 */
    protected function getSourceTableName(){
        return 'v_'.$this->getTableName();
    }

	/**
	 * Method description
	 */
    abstract protected function getTableName();

    protected $scheme = 'dbo';

    protected $fields = array();

    protected $valid;

    private $errors = array();

	/**
	 * Method description
	 */
    protected function invalidate()
    {
        $this->valid = false;
    }

    /**
     * @abstract
     * @return Abstract_model
     */
    abstract public function validate();

    /**
     * Годно ли к сохранению
     *
     * @return bool
     */
    public function isValid()
    {
        return $this->valid;
    }

	/**
	 * Method description
	 */
    public function getErrors()
    {
        return $this->errors;
    }

	/**
	 * Method description
	 */
    protected function clearErrors(){
   		$this->errors = array();
   	}

	/**
	 * Method description
	 */
    public function addError($errmsg)
    {
        $this->valid = false;
        $this->errors[] = $errmsg;
    }

	/**
	 * Method description
	 */
    public function __get($name)
    {
        $result = null;
        if (array_key_exists($name, $this->fields)) {
            $result = $this->fields[$name];
        } else {
            $result = parent::__get($name);
        }
        return $result;
    }

	/**
	 * Method description
	 */
    public function getKeyFieldValue(){
        return $this->__get($this->getKeyFieldName());
    }

	/**
	 * Method description
	 */
    public function setKeyFieldValue($value){
        $this->__set($this->getKeyFieldName(), $value);
    }

	/**
	 * Method description
	 */
    public function __set($name, $value)
    {
        if (array_key_exists($name, $this->fields)) {
            $this->fields[$name] = $value;
            $this->invalidate();
        } else {
            throw new Exception("Попытка присвоить значение $value несуществующему полю $name");
        }
    }

	/**
	 * Method description
	 */
    function __isset($name)
    {
        return isset($this->fields[$name]);
    }


    /**
     * Заполняет модель модель переданными в массиве значениями (ключ массива соответствует имени поля в модели)
     *
     * @param $values array
     *
     */
    protected function assign($values)
    {
        foreach ($this->fields as $field_name => $field_value) {
            if (array_key_exists($field_name, $values)) {
                $this->fields[$field_name] = $values[$field_name];
            }
        }
        $this->invalidate();
    }

	/**
	 * Method description
	 */
	function save($data = array())
	{
		if ($this->validate()->isValid()) {
			$proc = $this->chooseSaveProcedure();
			$params = array();
			$query_paramlist_exclude = array('Error_Code', 'Error_Message', $this->getKeyFieldName());
			list($params, $query_paramlist) = $this->getParamList($params, $query_paramlist_exclude);
			$query_declarations = "@Error_Code bigint, @Error_Message varchar(4000), @{$this->getKeyFieldName()} bigint = :{$this->getKeyFieldName()};";
			$query = "
                declare
                   $query_declarations
                exec {$this->scheme}.$proc
                   @{$this->getKeyFieldName()}       = @{$this->getKeyFieldName()} output, -- bigint
                   $query_paramlist
                   @Error_Code      = @Error_Code      output, -- int
                   @Error_Message   = @Error_Message   output  -- varchar(4000)
               select @{$this->getKeyFieldName()} as {$this->getKeyFieldName()}, @Error_Code as Error_Code, @Error_Message as Error_Msg;
            ";
            try {
                $dbresponse = $this->db->query($query, $params);
                if (is_object($dbresponse)) {
                    $result = $dbresponse->result('array');
                    if (isset($result[0])) {
                        $this->__set($this->getKeyFieldName(), $result[0][$this->getKeyFieldName()]);
                    }
                } else {
                    $result = array(
                        0 => array(
                            $this->getKeyFieldName() => null,
                            'Error_Code' => null,
                            'Error_Msg' => 'При сохранении произошли ошибки'
                        )
                    );
                }
                $save_ok = self::save_ok($result);
				if (!$save_ok) {
					log_message('error', 'query error: '. $query.' params'.var_export($params, true));
				}
            } catch (Exception $e) {
                $result = array(
                    0 => array(
                        $this->getKeyFieldName() => null,
                        'Error_Code' => null,
                        'Error_Msg' => 'При вызове процедуры сохранения произошли ошибки: '.str_replace(chr(13),' ', str_replace(chr(10),'<br> ', $e->getCode().' '.$e->getMessage()))
                    )
                );
            }
        } else {
            $result = array(
                0 => array(
                    $this->getKeyFieldName() => null,
                    'Error_Code' => null,
                    'Error_Msg' => 'При сохранении произошли ошибки: <br/>' . implode('<br/>', $this->getErrors())
                )
            );
        }
        return $result;
    }

	/**
	 * Method description
	 */
    public function chooseSaveProcedure()
    {
        if ($this->__get($this->getKeyFieldName()) > 0) {
            $proc = $this->getSaveProcedureName() . '_upd';
        } else {
            $proc = $this->getSaveProcedureName() . '_ins';
            $this->__set($this->getKeyFieldName(), null);
        }
        return $proc;
    }

	/**
	 * Method description
	 */
    function delete($data = array())
    {
        if ($this->canDelete()) {
            if ($this->__get($this->getKeyFieldName()) > 0) {
                $proc = $this->getSaveProcedureName() . '_del';
            } else {
                throw new Exception('Попытка удалить запись с неправильным идентификатором: '.$this->__get($this->getKeyFieldName()));
            }
            $params = array(
                $this->getKeyFieldName() => $this->__get($this->getKeyFieldName())
            );
			$query = "
                declare
                    @Error_Code bigint, @Error_Message varchar(4000);
                exec {$this->scheme}.$proc
                   @{$this->getKeyFieldName()} = :{$this->getKeyFieldName()},
                   @Error_Code      = @Error_Code      output, -- int
                   @Error_Message   = @Error_Message   output  -- varchar(4000)
               select @Error_Code as Error_Code, @Error_Message as Error_Msg;
            ";
            $dbresponse = $this->db->query($query, $params);
            if (is_object($dbresponse)) {
                $result = $dbresponse->result('array');
            } else {
                $result = array(
                    0 => array(
                        $this->getKeyFieldName() => null,
                        'Error_Code' => null,
                        'Error_Msg' => 'При удалении произошли ошибки'
                    )
                );
            }
        } else {
            $result = array(
                0 => array(
                    $this->getKeyFieldName() => null,
                    'Error_Code' => null,
                    'Error_Msg' => 'При удалении произошли ошибки: <br/>' . implode('<br/>', $this->getErrors())
                )
            );
        }
        return $result;
    }

	/**
	 * Method description
	 */
    function load($field = null,$value = null, $selectFields = 't.*', $addNameEntries = true){
        if ($field && $value){
            $params = array(
                $field => $value
       		);
            $where = "t.{$field} = :{$field}";
        } else {
            $params = array(
                $this->getKeyFieldName() => $this->__get($this->getKeyFieldName())
       		);
            $where = "{$this->getKeyFieldName()} = :{$this->getKeyFieldName()}";
        }
		$nameEntries = '';
		$joins = '';
		if ($addNameEntries) {
			$this->load->model('ModelGenerator_model', 'ModelGenerator_model');
			$tableFields = $this->ModelGenerator_model->getTableInfo($this->getTableName());
			$nameEntries_t = array();
			$joins_t = array();
			foreach ($tableFields as $tableField) {
				if ($tableField['ref_name']) {
					$nameEntries_t[] = $tableField['column_name'].'_ref'.'.'.$tableField['ref_table'].'_Name as '.$tableField['column_name'].'_Name';
					$joins_t[] = 'LEFT JOIN v_'.$tableField['ref_table'].' with(nolock) '.$tableField['column_name'].'_ref'.' on '.
						$tableField['column_name'].'_ref.'.$tableField['ref_col'].' = t.'.$tableField['column_name'];
				}
			}
			$nameEntries = ','.implode(',', $nameEntries_t);
			$joins = implode(PHP_EOL, $joins_t);
		}
   		$query = "
   			select
                $selectFields
                {$nameEntries}
   			from
   		        {$this->scheme}.{$this->getSourceTableName()} t with (nolock)
   		        {$joins}
   		    where
   				$where
   		";
		//echo getDebugSql($query,$params);
   		$result = $this->db->query($query, $params);
   		if ( is_object($result) ) {
   			$response = $result->result('array');
            if (isset($response[0])) {
       		    $this->rawLoadResult = $response[0];
				$this->assign($response[0]);
            } else {
				log_message('error', __CLASS__.'::load() error: no any found. query:'.$query.' params: '.var_export($params, true));
			}
   			return $response;
   		}
   		else {
   			return false;
   		}
   	}

	/**
	 * Получение массива параметров и списка аргументов(строка) для вызова хранимки
	 *
	 * @param $params Массив параметров со значениями
	 * @param $query_paramlist_exclude Массив имен параметров, которые надо исключить из строки со списком аргументов
	 * @return array
	 */
	protected function getParamList($params, $query_paramlist_exclude)
	{
		$query_paramlist = '';
		foreach ($this->fields as $key => $value) {
			$params[$key] = $value;
			if (!in_array($key, $query_paramlist_exclude)) {
				if (strlen($query_paramlist)>0) {
					$query_paramlist = $query_paramlist.PHP_EOL;
				}
				$query_paramlist = $query_paramlist." @$key = :$key,";
			}
		}
		$params[$this->getKeyFieldName()] = $this->fields[$this->getKeyFieldName()];
		return array($params, $query_paramlist);
	}

	/**
	 * Method description
	 */
    public function getFields()
    {
        return $this->fields;
    }

    /**
	 * Проверяет заполнено ли обязательное поле
	 *
	 * @param mixed $field Поле таблицы
	 * @param string $label Метка поля на форме
	 *
	 * @return bool
	 */
	function validateRequired($field, $label) {
		if ($this->$field === NULL) {
			$this->addError('Поле "' . $label . '" обязательно для заполнения.');
			return FALSE;
		}
		return TRUE;
	}

    /**
	 * Проверяет существование значия поля в справочнике
	 *
	 * @param string $reference
	 * @param string $label Метка поля на форме
	 *
	 * @return bool
	 */
	function validateByReference($reference, $label) {
		$id = $reference . '_id';
		$table = 'v_' . $reference;
		$result = $this->getFirstResultFromQuery("SELECT $id FROM dbo.$table with (nolock) WHERE $id = :$id", array($id => $this->$id));
		if (!$result) {
			$this->addError('Значение поля "' . $label . '" не найдено в справочнике.');
			return FALSE;
		}
		return TRUE;
	}

	/**
	 * Method description
	 */
    function getNameRetrieveFieldListEntry($object){
        return "(select {$object}_Name FROM v_{$object} d223 with (nolock) WHERE d223.{$object}_id = t.{$object}_id) as {$object}_Name";
    }

    /**
	 * Проверяет на правильность значения да/нет
	 *
	 * @param string $field
	 * @param string $label Метка поля на форме
	 *
	 * @return bool
	 */
	function validateYesNo($field, $label) {
		$result = $this->getFirstResultFromQuery("SELECT YesNo_id FROM dbo.v_YesNo with (nolock) WHERE YesNo_id = :id", array('id' => $this->$field));
		if (!$result) {
			$this->addError('Значение поля "' . $label . '" не найдено в справочнике.');
			return FALSE;
		}
		return TRUE;
	}

    /**
     * @static
     * @param $save_response array
     * @return bool
     */
    public static function save_ok($save_response){
        $result = false;
        if (is_array($save_response)) {
            if (isset($save_response[0])) {
                $result = ((empty($save_response[0]['Error_Code'])) && (empty($save_response[0]['Error_Msg'])));
            }
        } else {
            if ($save_response === true) {
                $result = true;
            }
        }
        return $result;
    }

	/**
	 * Method description
	 */
	public function convertDatetimeFieldsToFormat($arr, $format = 'd.m.Y'){
		/**
		 * Method description
		 */
		function convertDatetimeFields(&$var, $key, $format){
			if (is_object($var)) {
				if ($var instanceof DateTime) {
					if ($key !== null ) {
						//$key не используется :)
					}
					/**
					 * @var DateTime $var
					 */
					$var = $var->format($format);
				}
			}
		}
		array_walk_recursive($arr,'convertDatetimeFields', $format);
		return $arr;
	}

	/**
	 * Method description
	 */
	public function setRawLoadResult($rawLoadResult)
	{
		$this->rawLoadResult = $rawLoadResult;
	}
	
	/**
	 * Method description
	 */
	public function getRawLoadResult()
	{
		return $this->rawLoadResult;
	}
}