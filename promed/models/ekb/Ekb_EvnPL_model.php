<?php	defined('BASEPATH') or die ('No direct script access allowed');

require_once(APPPATH.'models/EvnPL_model.php');

class Ekb_EvnPL_model extends EvnPL_model {
	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * @param $data
	 * @return bool
	 */
	function getEvnPLNumber($data) {
		$query = "
			declare @EvnPL_NumCard bigint;
			exec xp_GenpmID @ObjectName = 'EvnPL', @Lpu_id = :Lpu_id, @ObjectID = @EvnPL_NumCard output, @ObjectValue = :ObjectValue;
			select @EvnPL_NumCard as EvnPL_NumCard;
		";
		$result = $this->db->query($query, array(
			 'Lpu_id' => $data['Lpu_id']
			,'ObjectValue' => (!empty($data['year']) ? $data['year'] : date('Y'))
		));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Логика после успешного сохранения объекта
	 * @param array $result Результат выполнения запроса
	 * @throws Exception
	 */
	protected function _afterSave($result)
	{
		parent::_afterSave($result);

		//$this->load->model('Rmis_model');
		//$this->Rmis_model->syncEvnPL($this->id);
	}

	/**
	 * Тест синхронизации
	 */
	function testEvnPLSync() {
		$this->load->model('Rmis_model');
		return $this->Rmis_model->syncEvnPL('73002388087060');
	}
}