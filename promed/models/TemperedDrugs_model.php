<?php
/**
* TemperedDrugs_model - модель 
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
* Импорт файла отпущенных ЛС в БД.
*
* @package      Farmacy
* @access       public
* @copyright    Copyright (c) 2009-2011 Swan Ltd.
* @author       SWAN developers
* @version      30.03.2011
*/
 
class TemperedDrugs_model extends swModel {
	/**
	 * Method description
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * Method description
	 */
	function importDrugsFromDbf($data){
		$query = "
			insert into r2.apt_pin (
				SS,
				C_OGRN,
				PCOD,
				DS,
				SN_R,
				DATE_VR,
				PR_LR,
				C_PO,
				DATE_OTP,
				C_TRN,
				C_LF,
				N_LF,
				KO_ALL,
				KU_ALL,
				SL_ALL,
				N_PP,
				IMJ_TOVAR,
				IMJ_MTOV
			)
			values (
				:SS,
				:C_OGRN,
				:PCOD,
				:DS,
				:SN_R,
				:DATE_VR,
				:PR_LR,
				:C_PO,
				:DATE_OTP,
				:C_TRN,
				:C_LF,
				:N_LF,
				:KO_ALL,
				:KU_ALL,
				:SL_ALL,
				:N_PP,
				:IMJ_TOVAR,
				:IMJ_MTOV
			)
		";

		try {

			//Выполнение простого инсерта возвращает не объект а bool
			$result = $this->db->query($query,$data);

			if (!$result){
				throw new Exception('Ошибка записи отпущенных ЛС в БД');
			} else {
				return true;
			}
		}
		catch ( Exception $e ) {
			return false;
		}
	}

	/**
	 * Method description
	 */
	function TemperedDrugsDelFromTable(){
		$query = "
			delete from r2.apt_pin with (rowlock)
		";

		try {
			if ( !$this->db->query($query) ) {
				throw new Exception('Ошибка записи отпущенных ЛС в БД');
			}

			return true;
		}
		catch ( Exception $e ) {
			return false;
		}
	}

	/**
	 * Method description
	 */
	function TemperedDrugsExProcedure() {
		$response = '';

		$query = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);

			exec r2.xp_FetchReceptOtov
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		try {
			$result = $this->db->query($query);

			if ( !is_object($result) ) {
				throw new Exception('Ошибка при выполнении запроса к БД (процедура обработки загруженных ЛС)');
			}
			else {
				$resp = $result->result('array');

				if ( !is_array($resp) || count($resp) == 0 ) {
					throw new Exception('Ошибка при выполнении процедуры обработки загруженных ЛС');
				}
				else if ( !empty($resp[0]['Error_Msg']) ) {
					throw new Exception($resp[0]['Error_Msg']);
				}
			}
		}
		catch ( Exception $e ) {
			$response = $e->getMessage();
		}

		return $response;
	}
}
