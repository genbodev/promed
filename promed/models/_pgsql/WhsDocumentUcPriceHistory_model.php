<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * WhsDocumentUcPriceHistory_model - модель для работы с периодикой цен на гос. контракты
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Common
 * @access			public
 * @copyright		Copyright (c) 2013 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			27.11.2013
 */

class WhsDocumentUcPriceHistory_model extends SwPgModel{
	/**
	 * Конструктор
	 */
	function __construct(){
		parent::__construct();
	}

	/**
	 * Сохранение
	 */
	function saveWhsDocumentUcPriceHistoryFromJSON($data) {
		if (empty($data['SupplySpecJSON']) || $data['WhsDocumentUc_id'] == 0) {
			return false;
		}
		ConvertFromWin1251ToUTF8($data['SupplySpecJSON']);
		$SupplySpecData = (array) json_decode($data['SupplySpecJSON'],true);


		$query = "
			select
				WDU.WhsDocumentUc_id as \"WhsDocumentUc_id\"
			from
				v_WhsDocumentUc WDU
			where
				WDU.WhsDocumentUc_pid = :WhsDocumentUc_pid
			order by
				WDU.WhsDocumentUc_Date desc,
				WDU.WhsDocumentUc_id desc
				limit 1
		";
		$result = $this->db->query($query, array('WhsDocumentUc_pid'=>$data['WhsDocumentUc_id']));

		$WhsDocumentUc_sid = null;
		if (is_object($result)) {
			$response = $result->result('array');
			if (is_array($response) && count($response)>0) {
				$WhsDocumentUc_sid = $response[0]['WhsDocumentUc_id'];
			}
		}

		if ($data['WhsDocumentSupply_State'] == 'add') {
			foreach($SupplySpecData as $SupplySpec) {
				$params['WhsDocumentUcPriceHistory_id'] = null;
				$params['WhsDocumentUc_id'] = $data['WhsDocumentUc_id'];
				$params['Drug_id'] = $SupplySpec['Drug_id'];
				$params['WhsDocumentUcPriceHistory_Price'] = $SupplySpec['WhsDocumentSupplySpec_Price'];
				$params['WhsDocumentUcPriceHistory_PriceNDS'] = $SupplySpec['WhsDocumentSupplySpec_PriceNDS'];
				$params['WhsDocumentUc_sid'] = $WhsDocumentUc_sid;
                $params['WhsDocumentUcPriceHistory_SuppPrice'] = $SupplySpec['WhsDocumentSupplySpec_SuppPrice'];
				$params['pmUser_id'] = $data['pmUser_id'];

				$this->saveWhsDocumentUcPriceHistory($params);
			}
		} else
		if ($data['WhsDocumentSupply_State'] == 'edit') {
			$CompareStatus_Code = 0;
			$query = "
				select
					WDUPH.WhsDocumentUcPriceHistory_id as \"WhsDocumentUcPriceHistory_id\",
					WDUPH.Drug_id as \"Drug_id\",
					WDUPH.WhsDocumentUcPriceHistory_Price as \"WhsDocumentUcPriceHistory_Price\",
					WDUPH.WhsDocumentUcPriceHistory_PriceNDS as \"WhsDocumentUcPriceHistory_PriceNDS\"
				from
					v_WhsDocumentUcPriceHistory WDUPH
					left join lateral (
						select
							WhsDocumentUcPriceHistory_id
						from
							v_WhsDocumentUcPriceHistory
						where
							Drug_id = WDUPH.Drug_id and
							WhsDocumentUc_id = WDUPH.WhsDocumentUc_id
						order by
							WhsDocumentUcPriceHistory_begDT desc,
							WhsDocumentUcPriceHistory_id desc
							limit 1
					) t on true
				where
					WDUPH.WhsDocumentUc_id = :WhsDocumentUc_id
					and WDUPH.WhsDocumentUcPriceHistory_id = t.WhsDocumentUcPriceHistory_id
			";
			$result = $this->db->query($query, array('WhsDocumentUc_id'=>$data['WhsDocumentUc_id']));
			if (is_object($result)) {
				$response = $result->result('array');
			} else {
				return false;
			}
			if (!is_array($response) || count($response) == 0) {
				$CompareStatus_Code = -1;
			}
			foreach($SupplySpecData as $SupplySpec) {
				if ($SupplySpec['state'] == 'delete') {
					continue;
				}
				if ($CompareStatus_Code != -1) {
					$CompareStatus_Code = -1;
					foreach($response as $UcPriceHistory) {
						if ($UcPriceHistory['Drug_id'] == $SupplySpec['Drug_id']) {
							if (
								$UcPriceHistory['WhsDocumentUcPriceHistory_Price'] != $SupplySpec['WhsDocumentSupplySpec_Price'] ||
								$UcPriceHistory['WhsDocumentUcPriceHistory_PriceNDS'] != $SupplySpec['WhsDocumentSupplySpec_PriceNDS']
							) {
								$CompareStatus_Code = 1;
								break;
							} else {
								$CompareStatus_Code = 0;
								break;
							}
						}
					}
				}

				if (!$CompareStatus_Code) {
					continue;
				}

				$params['WhsDocumentUcPriceHistory_id'] = null;
				$params['WhsDocumentUc_id'] = $data['WhsDocumentUc_id'];
				$params['Drug_id'] = $SupplySpec['Drug_id'];
				$params['WhsDocumentUcPriceHistory_Price'] = $SupplySpec['WhsDocumentSupplySpec_Price'];
				$params['WhsDocumentUcPriceHistory_PriceNDS'] = $SupplySpec['WhsDocumentSupplySpec_PriceNDS'];
				$params['WhsDocumentUc_sid'] = $WhsDocumentUc_sid;
                $params['WhsDocumentUcPriceHistory_SuppPrice'] = $SupplySpec['WhsDocumentSupplySpec_SuppPrice'];
				$params['pmUser_id'] = $data['pmUser_id'];

				$this->saveWhsDocumentUcPriceHistory($params);
			}
		}

	}

	/**
	 * Сохранение периодики по ГК в БД
	 */
	function saveWhsDocumentUcPriceHistory($data) {
		if (!empty($data['WhsDocumentUcPriceHistory_id']) && $data['WhsDocumentUcPriceHistory_id'] > 0) {
			$procedure = 'p_WhsDocumentUcPriceHistory_upd';
		} else {
			$procedure = 'p_WhsDocumentUcPriceHistory_ins';
		}

		if(empty($data['WhsDocumentUcPriceHistory_SuppPrice'])) {
			$data['WhsDocumentUcPriceHistory_SuppPrice'] = null;
		}
		
        $query = "
                select WhsDocumentUcPRiceHistory_id as \"WhsDocumentUcPRiceHistory_id\",
                        error_code as \"Error_Code\",
                        error_message as \"Error_Msg\"
                        from " . $procedure . " 
                        (
                            WhsDocumentUc_id := :WhsDocumentUc_id,
                            WhsDocumentUcPriceHistory_begDT := dbo.tzGetDate(),
                            WhsDocumentUcPriceHistory_Price := :WhsDocumentUcPriceHistory_Price,
                            WhsDocumentUcPriceHistory_PriceNDS := :WhsDocumentUcPriceHistory_PriceNDS,
                            WhsDocumentUc_sid := :WhsDocumentUc_sid,
                            WhsDocumentUcPriceHistory_SuppPrice := :WhsDocumentUcPriceHistory_SuppPrice,
                            Drug_id := :Drug_id,
                            pmUser_id := :pmUser_id
                        )
		    ";

		$this->db->query($query, $data);
	}
}