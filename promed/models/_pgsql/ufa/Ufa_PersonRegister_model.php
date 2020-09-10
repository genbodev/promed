<?php defined('BASEPATH') or die ('No direct script access allowed');
require_once(APPPATH.'models/_pgsql/PersonRegister_model.php');

class Ufa_PersonRegister_model extends PersonRegister_model {


	/**
	 * Логика после успешного выполнения запроса сохранения объекта
	 *
	 * Если сохранение выполняется внутри транзакции,
	 * то при запросах данных этого объекта из БД будут возвращены старые данные!
	 * @param array $result Результат выполнения запроса
	 * @throws Exception
	 */
	protected function _afterSave($result)
	{

		if($this->MorbusType_SysNick == 'nephro') {
			$params = array();
			$params['Person_id'] = $this->Person_id;
			$params['Morbus_id'] = $this->Morbus_id;

			$res = $this->execCommonSP('r2.p_MorbusNephro_auto',$params);

			if (empty($res)) {
				throw new Exception('Ошибка запроса записи данных объекта в БД', 500);
			}
			if (isset($res[0]['Error_Msg'])) {
				throw new Exception($res[0]['Error_Msg'], $res[0]['Error_Code']);
			}
		}
	}

}