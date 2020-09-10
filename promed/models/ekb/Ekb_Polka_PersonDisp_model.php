<?php
/**
* Ekb_Polka_PersonDisp_model - модель для работы с картами дисп. учета (Пермь)
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Stanislav Bykov (savage@swan.perm.ru)
* @version      25.08.2018
*/

require_once(APPPATH.'models/Polka_PersonDisp_model.php');

class Ekb_Polka_PersonDisp_model extends Polka_PersonDisp_model {
	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * Выгрузка списка карт диспансерного наблюдения за период
	 */
	public function exportPersonDispForPeriod($data) {
		$filterLpuAttach = "
			and exists (
				select top 1 PersonCard_id
				from v_PersonCard_all with (nolock)
				where Person_id = pd.Person_id
					and LpuAttachType_id = 1
					and Lpu_id = @Lpu_id
					and PersonCard_begDate <= @EndDT
					and (PersonCard_endDate is null or PersonCard_endDate >= @EndDT)
			)
		"; // Своя МО
		switch($data['TypeFilterLpuAttach_id']) {
			case 1:
				$filterLpuAttach = ""; // Все
				break;
			case 3:
				$filterLpuAttach = "
					and exists (
						select top 1 PersonCard_id
						from v_PersonCard_all with (nolock)
						where Person_id = pd.Person_id
							and LpuAttachType_id = 1
							and Lpu_id <> @Lpu_id
							and PersonCard_begDate <= @EndDT
							and (PersonCard_endDate is null or PersonCard_endDate >= @EndDT)
					)				
				"; // Все кроме своей МО
				break;
		}
		$filterLpuCard = "and pd.Lpu_id = @Lpu_id"; // Своя МО
		switch($data['TypeFilterLpuCard_id']) {
			case 1:
				$filterLpuCard = ""; // Все
				break;
			case 3:
				$filterLpuCard = "and pd.Lpu_id <> @Lpu_id"; // Все кроме своей МО
				break;
		}

		$query = "
			declare
				@BegDT datetime = :ExportDateRange_0,
				@EndDT datetime = :ExportDateRange_1,
				@Lpu_id bigint = :Lpu_id;

			select
				ps.Person_EdNum as Enp,
				ps.Person_Surname as fam,
				ps.Person_Firname as im,
				ps.Person_Secname as ot,
				ps.Person_Birthday as dr,
				sx.Sex_fedid as w,
				dt.DocumentType_Code as doctype,
				d.Document_Ser as docser,
				d.Document_Num as docnum,
				ps.Person_Snils as snils,
				pt.PolisType_CodeF008 as vpolis,
				pls.Polis_Ser as spolis,
				pls.Polis_Num as npolis,
				l.Lpu_f003mcod as codmof_disp,
				case when pd.PersonDisp_endDate is null then 1 else 0 end as attach_disp_type,
				pd.PersonDisp_begDate as attach_disp_dt,
				dg.Diag_Code as attach_disp_ds,
				pd.PersonDisp_endDate as detach_disp_dt
			from dbo.v_PersonState ps with (nolock)
				inner join v_PersonDisp pd  with (nolock) on ps.Person_id = pd.Person_id
				inner join dbo.v_Lpu l with (nolock) on pd.Lpu_id = l.Lpu_id
				inner join dbo.v_Sex sx with (nolock) on sx.Sex_id = ps.Sex_id
				inner join dbo.v_Document d with (nolock) on d.Document_id = ps.Document_id
				inner join dbo.v_DocumentType dt with (nolock) on dt.DocumentType_id = d.DocumentType_id
				inner join dbo.v_Polis pls with (nolock) on pls.Polis_id = ps.Polis_id
				inner join dbo.v_PolisType pt with (nolock) on pt.PolisType_id = pls.PolisType_id
				inner join dbo.v_Diag dg with (nolock) on dg.Diag_id = pd.Diag_id
			where
				(pd.PersonDisp_begDate between @BegDT and @EndDT or PersonDisp_endDate between @BegDT and @EndDT)
				{$filterLpuAttach}
				{$filterLpuCard}
		";

		$result = $this->db->query($query, array(
			'ExportDateRange_0' => $data['ExportDateRange'][0],
			'ExportDateRange_1' => $data['ExportDateRange'][1],
			'Lpu_id' => $data['Lpu_id']
		));

		if ( !is_object($result) ) {
			return false;
		}

		return $result;
	}
}