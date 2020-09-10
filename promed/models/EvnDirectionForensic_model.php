<?php

defined( 'BASEPATH' ) or die( 'No direct script access allowed' );

/**
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * Модель для поручений
 *
 * @package      BSME
 * @package
 * @access       public
 * @copyright    Copyright (c) 2014 Swan Ltd.
 * @author       Dmitriy Vlasenko
 * @version
 */
class EvnDirectionForensic_model extends swModel {

	/**
	 * 	Получение номера поручения
	 */
	function getNextNumber( $data ) {
		$response = array(
			'EvnDirectionForensic_Num' => 1,
			'Error_Msg' => ''
		);
		$query = '
			SELECT 
				ISNULL(MAX(EDF.EvnDirectionForensic_Num),0)+1 as EvnDirectionForensic_Num
			FROM 
				v_EvnDirectionForensic EDF with (nolock)
			WHERE
				YEAR(EvnDirectionForensic_insDT) = YEAR(dbo.tzGetDate()) -- за текущий год
		';

		$result = $this->db->query( $query );
		if ( is_object( $result ) ) {
			$resp = $result->result( 'array' );
			if ( !empty( $resp[ 0 ][ 'EvnDirectionForensic_Num' ] ) ) {
				$response[ 'EvnDirectionForensic_Num' ] = $resp[ 0 ][ 'EvnDirectionForensic_Num' ];
			}

			return $response;
		}

		return false;
	}

	/**
	 * 	Сохранение поручения
	 */
	public function saveEvnDirectionForensic( $data ) {
		$procedure = "p_EvnDirectionForensic_upd";
		if ( empty( $data[ 'EvnDirectionForensic_id' ] ) ) {
			$data[ 'EvnDirectionForensic_id' ] = null;
			$procedure = "p_EvnDirectionForensic_ins";
		}

		$sql = "
			declare
				@EvnDirectionForensic_id bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @EvnDirectionForensic_id = :EvnDirectionForensic_id;
			exec ".$procedure."
				@EvnDirectionForensic_id = @EvnDirectionForensic_id output,
				@Lpu_id = :Lpu_id,
				@EvnDirectionForensic_Num = :EvnDirectionForensic_Num,
				@EvnDirectionForensic_begDate = :EvnDirectionForensic_begDate,
				@EvnDirectionForensic_endDate = :EvnDirectionForensic_endDate,
				@EvnForensicType_id = :EvnForensicType_id,
				@MedPersonal_id = :MedPersonal_id,
				@EvnForensic_id = :EvnForensic_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @EvnDirectionForensic_id as EvnDirectionForensic_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$query = $this->db->query( $sql, $data );
		if ( is_object( $query ) ) {
			$result = $query->result_array();

			// Изменяем статус заявки на Назначенные
			$this->load->model('Evn_model');
			$this->Evn_model->updateEvnStatus(array(
				'Evn_id' => $data['EvnForensic_id'],
				'EvnStatus_SysNick' => 'Appoint',
				'EvnClass_SysNick' => 'EvnForensic',
				'pmUser_id' => $data['pmUser_id']
			));

			return $result;
		}

		return false;
	}

}
