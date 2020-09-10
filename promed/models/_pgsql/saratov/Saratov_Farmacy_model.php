<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb
 *
 * Класс модели для общих операций используемых во всех модулях
 *
 * The New Generation of Medical Statistic Software
 *
 * @package				Common
 * @copyright			Copyright (c) 2009 Swan Ltd.
 * @author				Stas Bykov aka Savage (savage@swan.perm.ru)
 * @link				http://swan.perm.ru/PromedWeb
 * @version				?
 */

require_once(APPPATH.'models/_pgsql/Farmacy_model.php');

class Saratov_Farmacy_model extends Farmacy_model {
	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * Функция для получения идентификатора серии по самой серии. Если серии еще нет в справочнике, она добавляется туда.
	 */
	/*function PrepSeriesAdd($data) {
		$PrepSeries_id = isset($data['PrepSeries_id']) ? $data['PrepSeries_id'] : null;

		if ((isset($data['PrepSeries_Ser']) && !empty($data['PrepSeries_Ser']))) {
			$sql = "
				select top 1
					ps.PrepSeries_id
				from
					rls.v_PrepSeries ps
					left join rls.v_Drug d on d.DrugPrep_id = ps.Prep_id
				where
					d.Drug_id = :Drug_id and
					ps.PrepSeries_Ser = :PrepSeries_Ser;
			";
			$result = $this->db->query($sql, array(
				'Drug_id' => $data['Drug_id'],
				'PrepSeries_Ser' => $data['PrepSeries_Ser']
			));

			if (is_object($result)) {
				$sel = $result->result('array');
				if (isset($sel[0]) && $sel[0]['PrepSeries_id'] > 0) {
					$PrepSeries_id = $sel[0]['PrepSeries_id'];
				} else {
					$sql = "
						declare
							@PrepSeries_id bigint,
							@Prep_id bigint,
							@Error_Code int,
							@Error_Message varchar(4000);

						set @Prep_id = (select top 1 DrugPrep_id from rls.Drug where Drug_id = :Drug_id);

						execute rls.p_PrepSeries_ins
							@PrepSeries_id = @PrepSeries_id output,
							@Prep_id = @Prep_id,
							@PrepSeries_Ser = :PrepSeries_Ser,
							@PrepSeries_GodnDate = :PrepSeries_GodnDate,
							@PackNx_Code = null,
							@PrepSeries_IsDefect = null,
							@pmUser_id = :pmUser_id,
							@Error_Code = @Error_Code output,
							@Error_Message = @Error_Message output;

						select @PrepSeries_id as PrepSeries_id;
					";
					$result = $this->db->query($sql, array(
						'Drug_id' => $data['Drug_id'],
						'PrepSeries_Ser' => $data['PrepSeries_Ser'],
						'PrepSeries_GodnDate' => isset($data['PrepSeries_GodnDate']) ? $data['PrepSeries_GodnDate'] : null,
						'pmUser_id' => $data['pmUser_id']
					));
					if (is_object($result)) {
						$sel = $result->result('array');
						if ( $sel[0]['PrepSeries_id'] > 0 )
							$PrepSeries_id = $sel[0]['PrepSeries_id'];
					}
				}
			}
		}
		return $PrepSeries_id;
	}*/
}
