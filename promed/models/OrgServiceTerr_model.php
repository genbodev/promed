<?php
/**
* OrgServiceTerr_model - модель, для работы с таблицей OrgServiceTerr
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Марков Андрей
* @version      май 2010
*/

class OrgServiceTerr_model extends swModel {

	public $inputRules = array(
		'createOrgServiceTerr' => array(
			array('field' => 'Org_id', 'label' => 'Идентификатор организации', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'KLCountry_id', 'label' => 'Идентификатор страны', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'KLRgn_id', 'label' => 'Идентификатор региона', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'KLSubRgn_id', 'label' => 'Идентификатор района', 'rules' => '', 'type' => 'id'),
			array('field' => 'KLCity_id', 'label' => 'Идентификатор города', 'rules' => '', 'type' => 'id'),
			array('field' => 'KLTown_id', 'label' => 'Идентификатор нас. пункта', 'rules' => '', 'type' => 'id'),
			array('field' => 'KLAreaType_id', 'label' => 'Идентификатор типа населенного пункта', 'rules' => '', 'type' => 'id')
		),
		'updateOrgServiceTerr' => array(
			array('field' => 'OrgServiceTerr_id', 'label' => 'Идентификатор территории обслуживания', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'KLCountry_id', 'label' => 'Идентификатор страны', 'rules' => '', 'type' => 'id'),
			array('field' => 'KLRgn_id', 'label' => 'Идентификатор региона', 'rules' => '', 'type' => 'id'),
			array('field' => 'KLSubRgn_id', 'label' => 'Идентификатор района', 'rules' => '', 'type' => 'id'),
			array('field' => 'KLCity_id', 'label' => 'Идентификатор города', 'rules' => '', 'type' => 'id'),
			array('field' => 'KLTown_id', 'label' => 'Идентификатор нас. пункта', 'rules' => '', 'type' => 'id'),
			array('field' => 'KLAreaType_id', 'label' => 'Идентификатор типа населенного пункта', 'rules' => '', 'type' => 'id')
		),
		'deleteOrgServiceTerr' => array(
			array('field' => 'OrgServiceTerr_id', 'label' => 'Идентификатор территории обслуживания', 'rules' => 'required', 'type' => 'id')
		)
	);

	/**
	 *	Конструктор
	 */
	function __construct()
	{
		parent::__construct();
	}

	/**
	 *	Получение списка территорий обслуживания
	 */
	function loadOrgServiceTerrGrid($data) 
	{
		$params = array('Org_id' => $data['Org_id']);
		
		$query = "
			Select
				OST.OrgServiceTerr_id,
				Country.KLCountry_Name,
				RGN.KLRgn_Name,
				SRGN.KLSubRgn_Name,
				City.KLCity_Name,
				Town.KLTown_Name,
				KLAT.KLAreaType_Name
			from
				v_OrgServiceTerr OST (nolock)
				left join v_KLCountry Country (nolock) on Country.KLCountry_id = OST.KLCountry_id
				left join v_KLRgn RGN (nolock) on RGN.KLRgn_id = OST.KLRgn_id
				left join v_KLSubRgn SRGN (nolock) on SRGN.KLSubRgn_id = OST.KLSubRgn_id
				left join v_KLCity City (nolock) on City.KLCity_id = OST.KLCity_id
				left join v_KLTown Town (nolock) on Town.KLTown_id = OST.KLTown_id
				left join v_KLAreaType KLAT (nolock) on KLAT.KLAreaType_id = OST.KLAreaType_id
			where
				OST.Org_id = :Org_id
		";
		
		$result = $this->db->query($query, $params);

		if ( is_object($result) ) 
		{
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 *	Сохранение территории обслуживания
	 */
	function saveOrgServiceTerr($data)
	{
		$procedure_action = '';	

		if ( !empty($data['OrgServiceTerr_id']) ) {
			$procedure_action = "upd";
		}
		else {
			$procedure_action = "ins";
		}

        $query = "
            select
                COUNT (*) as [count]
            from
                v_OrgServiceTerr (nolock)
            where
                ISNULL(KLCountry_id, 0) = ISNULL(:KLCountry_id, 0) and
                ISNULL(KLRgn_id, 0) = ISNULL(:KLRgn_id, 0) and
                ISNULL(KLSubRgn_id, 0) = ISNULL(:KLSubRgn_id, 0) and
                ISNULL(KLCity_id, 0) = ISNULL(:KLCity_id, 0) and
                ISNULL(KLTown_id, 0) = ISNULL(:KLTown_id, 0) and
                ISNULL(KLAreaType_id, 0) = ISNULL(:KLAreaType_id, 0) and
                Org_id = :Org_id and
				OrgServiceTerr_id <> ISNULL(:OrgServiceTerr_id, 0)
        ";

        $res = $this->db->query($query, $data);
		if ( is_object($res) ) {
			$response = $res->result('array');
		
			if ( !empty($response[0]['count']) && $response[0]['count'] > 0) {
				return array(0 => array('Error_Msg' => 'Запись с введенными данными уже существует.'));
			}
		}
		
		$query = "
			declare
				@Res bigint,
				@ErrCode bigint,
				@ErrMsg varchar(4000);
			set @Res = :OrgServiceTerr_id;
			exec p_OrgServiceTerr_" . $procedure_action . "
				@OrgServiceTerr_id = @Res output,
				@Org_id = :Org_id,
				@KLCountry_id = :KLCountry_id,
				@KLRgn_id = :KLRgn_id,
				@KLSubRgn_id = :KLSubRgn_id,
				@KLCity_id = :KLCity_id,
				@KLTown_id = :KLTown_id,
				@KLAreaType_id = :KLAreaType_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMsg output;
			select @Res as OrgServiceTerr_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg;
		";

		$res = $this->db->query($query, $data);

		if ( is_object($res) ) {
			return $response = $res->result('array');
		}
		else {
			return false;
		}
	}
	
	/**
	 *	Получение списка территорий обслуживания
	 */
	function loadOrgServiceTerrEditForm($data) 
	{
		$filter = '';
		$params = array('OrgServiceTerr_id' => $data['OrgServiceTerr_id']);
		
		if (!empty($data['Lpu_id'])) {
			$params['Lpu_id'] = $data['Lpu_id'];
			$filter .= " and L.Lpu_id = :Lpu_id";
		}
		
		$query = "
			Select
				OST.OrgServiceTerr_id,
				OST.Org_id,
				OST.KLCountry_id,
				OST.KLRgn_id,
				OST.KLSubRgn_id,
				OST.KLCity_id,
				OST.KLTown_id,
				OST.KLAreaType_id,
				KAS.KLAreaStat_id
			from
				v_OrgServiceTerr OST (nolock)
				outer apply(
					select top 1
						KLAreaStat_id
					from
						v_KLAreaStat with(nolock)
					where
						(KLCountry_id IS NULL OR KLCountry_id = OST.KLCountry_id) and
						(KLRgn_id IS NULL OR KLRgn_id = OST.KLRgn_id) and
						(KLSubRgn_id IS NULL OR KLSubRgn_id = OST.KLSubRgn_id) and
						(KLCity_id IS NULL OR KLCity_id = OST.KLCity_id) and
						(KLTown_id IS NULL OR KLTown_id = OST.KLTown_id)
				) KAS
				left join v_Lpu L with (nolock) on L.Org_id = OST.Org_id
			where
				OST.OrgServiceTerr_id = :OrgServiceTerr_id
				{$filter}
		";
		
		$result = $this->db->query($query, $params);

		if ( is_object($result) ) 
		{
			return $result->result('array');
		}
		else {
			return false;
		}
	}
}

?>