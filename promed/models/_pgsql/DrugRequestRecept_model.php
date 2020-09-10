<?php defined('BASEPATH') or die ('No direct script access allowed');

class DrugRequestRecept_model extends SwPgModel {
	/**
	 * Конструктор
	 */
	function __construct(){
		parent::__construct();
	}

	/**
	 * Загрузка
	 */
	function load($data) {
		$q = "
			select
				drr.Server_id as \"Server_id\",
				drr.DrugRequestRecept_id as \"DrugRequestRecept_id\",
				drr.DrugRequestPeriod_id as \"DrugRequestPeriod_id\",
				drr.DrugProtoMnn_id as \"DrugProtoMnn_id\",
				drr.DrugRequestRecept_Kolvo as \"DrugRequestRecept_Kolvo\",
				drr.DrugRequestRecept_KolvoRAS as \"DrugRequestRecept_KolvoRAS\",
				drr.DrugRequestRecept_KolvoPurch as \"DrugRequestRecept_KolvoPurch\",
				drr.DrugRequestRecept_KolvoDopPurch as \"DrugRequestRecept_KolvoDopPurch\",
				dpm.DrugProtoMnn_Code as \"DrugProtoMnn_Code\",
				dpm.DrugProtoMnn_Name as \"DrugProtoMnn_Name\"
			from
				dbo.v_DrugRequestRecept drr
				left join dbo.v_DrugProtoMnn dpm on dpm.DrugProtoMnn_id = drr.DrugProtoMnn_id
			where
				DrugRequestRecept_id = :DrugRequestRecept_id
		";
		$r = $this->db->query($q, array('DrugRequestRecept_id' => $data['DrugRequestRecept_id']));
		if ( is_object($r) ) {
			$r = $r->result('array');
			if (isset($r[0])) {
				return $r;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	/**
	 * Загрузка списка
	 */
	function loadList($filter) {
		$where = array();

		if (isset($filter['ReceptFinance_id']) && $filter['ReceptFinance_id']) {
			$where[] = 'dpm.ReceptFinance_id = :ReceptFinance_id';
		}
		if (isset($filter['DrugRequestPeriod_id']) && $filter['DrugRequestPeriod_id']) {
			$where[] = 'drr.DrugRequestPeriod_id = :DrugRequestPeriod_id';
		}

		$where_clause = implode(' and ', $where);
		if (strlen($where_clause)) {
			$where_clause = 'where '.$where_clause;
		}

		$q = "
			select
				drr.Server_id as \"Server_id\",
				drr.DrugRequestRecept_id as \"DrugRequestRecept_id\",
				drr.DrugRequestPeriod_id as \"DrugRequestPeriod_id\",
				drr.DrugProtoMnn_id as \"DrugProtoMnn_id\",
				drr.DrugRequestRecept_Kolvo as \"DrugRequestRecept_Kolvo\",
				drr.DrugRequestRecept_KolvoRAS as \"DrugRequestRecept_KolvoRAS\",
				drr.DrugRequestRecept_KolvoPurch as \"DrugRequestRecept_KolvoPurch\",
				drr.DrugRequestRecept_KolvoDopPurch as \"DrugRequestRecept_KolvoDopPurch\",
				drp.DrugRequestPeriod_Name as \"DrugRequestPeriod_Name\",
				dpm.DrugProtoMnn_Code as \"DrugProtoMnn_Code\",
				dpm.DrugProtoMnn_Name as \"DrugProtoMnn_Name\"
			from
				dbo.v_DrugRequestRecept drr
				left join dbo.v_DrugRequestPeriod drp on drp.DrugRequestPeriod_id = drr.DrugRequestPeriod_id
				left join dbo.v_DrugProtoMnn dpm on dpm.DrugProtoMnn_id = drr.DrugProtoMnn_id
			$where_clause
		";
		$result = $this->db->query($q, $filter);

		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Загрузка списка сводных заявок
	 */
	function loadDrugRequestReceptConsolidatedList($filter) {
		$where = array();
		$p = array();
		if (isset($filter['Year']) && $filter['Year']) {
			$where[] = 'extract(year from drp.DrugRequestPeriod_begDate) = :Year';
		}
		$where_clause = implode(' and ', $where);
		if (strlen($where_clause)) {
			$where_clause = 'where '.$where_clause;
		}

		$q = "
			select distinct
				cast(dpm.ReceptFinance_id as varchar)||'_'||cast(drr.DrugRequestPeriod_id as varchar) as \"DrugRequestReceptConsolidated_id\",
				dpm.ReceptFinance_id as \"ReceptFinance_id\",
				rf.ReceptFinance_Name as \"ReceptFinance_Name\",
				drr.DrugRequestPeriod_id as \"DrugRequestPeriod_id\",
				drp.DrugRequestPeriod_Name as \"DrugRequestPeriod_Name\"
			from
				dbo.v_DrugRequestRecept drr
				left join dbo.v_DrugRequestPeriod drp on drp.DrugRequestPeriod_id = drr.DrugRequestPeriod_id
				left join dbo.v_DrugProtoMnn dpm on dpm.DrugProtoMnn_id = drr.DrugProtoMnn_id
				left join dbo.v_ReceptFinance rf on rf.ReceptFinance_id = dpm.ReceptFinance_id
			where
				extract(year from drp.DrugRequestPeriod_begDate) = :Year;
		";
		$result = $this->db->query($q, $filter);

		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Сохранение
	 */
	function save($data) {
		$procedure = 'p_DrugRequestRecept_ins';
		if ( $data['DrugRequestRecept_id'] > 0 ) {
			$procedure = 'p_DrugRequestRecept_upd';
		}
		$q = "
			select
			    DrugRequestRecept_id as \"DrugRequestRecept_id\",
			    Error_Code as \"Error_Code\",
			    Error_Message as \"Error_Msg\"
			from dbo." . $procedure . " (
				Server_id := :Server_id,
				DrugRequestRecept_id := :DrugRequestRecept_id,
				DrugRequestPeriod_id := :DrugRequestPeriod_id,
				DrugProtoMnn_id := :DrugProtoMnn_id,
				DrugRequestRecept_Kolvo := :DrugRequestRecept_Kolvo,
				DrugRequestRecept_KolvoRAS := :DrugRequestRecept_KolvoRAS,
				DrugRequestRecept_KolvoPurch := :DrugRequestRecept_KolvoPurch,
				DrugRequestRecept_KolvoDopPurch := :DrugRequestRecept_KolvoDopPurch,
				pmUser_id := :pmUser_id
				)
		";
		$r = $this->db->query($q, $data);
		//print getDebugSQL($q, $data);
		if ( is_object($r) ) {
			$result = $r->result('array');

			if ($data['DrugRequestPeriod_id'] > 0 && $data['DrugProtoMnn_id'] > 0) {
				if ($data['DrugRequestRecept_id'] <= 0) { //при добавлении необходимо сгенерировать разнарядку
					$q = "
                        select
                            Error_Code as \"Error_Code\",
                            Error_Message as \"Error_Msg\"
						from dbo.p_DrugRequestReceptLpu_import (
							DrugRequestPeriod_id := :DrugRequestPeriod_id,
							DrugProtoMnn_id := :DrugProtoMnn_id,
							pmUser_id := :pmUser_id
							)
					";
					$r = $this->db->query($q, $data);
				} else { //при редактировании необходим пересчет разнарядки
					$q = "
                        select
                            Error_Code as \"Error_Code\", 
                            Error_Message as \"Error_Msg\"
						from dbo.p_DrugRequestReceptLpu_recount (
							DrugRequestPeriod_id := :DrugRequestPeriod_id,
							DrugProtoMnn_id := :DrugProtoMnn_id,
							pmUser_id := :pmUser_id
							)
					";
					$r = $this->db->query($q, $data);
				}
			}
		} else {
			$result = array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных'));
		}
		return $result;
	}

	/**
	 * Удаление
	 */
	function delete() {
		$q = "
            select
                Error_Code as \"Error_Code\", 
                Error_Message as \"Error_Msg\"
			from dbo.p_DrugRequestRecept_del (
				DrugRequestRecept_id := :DrugRequestRecept_id
				)
		";
		$r = $this->db->query($q, array(
			'DrugRequestRecept_id' => $this->DrugRequestRecept_id
		));
		if ( is_object($r) ) {
			return $r->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Удаление списка заявок
	 */
	function deleteDrugRequestReceptConsolidated($data) {
		$id_array = explode('_', $data['id']);

		if (count($id_array) == 2 && $id_array[0] > 0 && $id_array[1] > 0) {
			$q = "
				select
					drr.DrugRequestRecept_id as \"DrugRequestRecept_id\"
				from
					v_DrugRequestRecept drr
					left join v_DrugProtoMnn dpm on dpm.DrugProtoMnn_id = drr.DrugProtoMnn_id
				where
					drr.DrugRequestPeriod_id = :DrugRequestPeriod_id and
					dpm.ReceptFinance_id = :ReceptFinance_id;
			";
			$r = $this->db->query($q, array(
				'DrugRequestPeriod_id' => $id_array[1],
				'ReceptFinance_id' => $id_array[0]
			));
			if ( is_object($r) ) {
				$request_arr = $r->result('array');
				foreach($request_arr as $request) {
					//получение списка и удаление разнарядок
					$q = "
						select
							DrugRequestReceptLpu_id as \"DrugRequestReceptLpu_id\"
						from
							v_DrugRequestReceptLpu
						where
							DrugRequestRecept_id = :DrugRequestRecept_id
					";
					$r = $this->db->query($q, array(
						'DrugRequestRecept_id' => $request['DrugRequestRecept_id']
					));
					if ( is_object($r) ) {
						$lpu_request_arr = $r->result('array');
						foreach($lpu_request_arr as $lpu_request) {
							//удаление самой заявки
							$q = "
                                select
                                    Error_Code as \"Error_Code\",
                                    Error_Message as \"Error_Msg\"
								from dbo.p_DrugRequestReceptLpu_del (
									DrugRequestReceptLpu_id := :DrugRequestReceptLpu_id
									)
							";
							$r = $this->db->query($q, array(
								'DrugRequestReceptLpu_id' => $lpu_request['DrugRequestReceptLpu_id']
							));
						}
					}

					//удаление самой заявки
					$q = "
                        select 
                            Error_Code as \"Error_Code\",
                            Error_Message as \"Error_Msg\"
						from dbo.p_DrugRequestRecept_del (
							DrugRequestRecept_id := :DrugRequestRecept_id
							)
					";
					$r = $this->db->query($q, array(
						'DrugRequestRecept_id' => $request['DrugRequestRecept_id']
					));
				}
			}
			return array('success' => true);
		}

		return false;
	}

	/**
	 *  Импорт данных из xls файла.
	 */
	function importFromXls($data) {
		require_once("promed/libraries/Spreadsheet_Excel_Reader/Spreadsheet_Excel_Reader.php");

		$result = array('success' => true);
		$err_arr = array();
		$data_start = false;

		$xls_data = new Spreadsheet_Excel_Reader();
		//$xls_data->setOutputEncoding('CP1251');
		$xls_data->read($data['FileFullName']);

		if (isset($xls_data->sheets[0])) {
			for ($i = 1; $i <= $xls_data->sheets[0]['numRows']; $i++) {
				if (isset($xls_data->sheets[0]['cells'][$i])) {
					$row = $xls_data->sheets[0]['cells'][$i];
					if ($data_start && isset($row[2]) && $row[2] != 0 && isset($row[11])) {
						$query = "
							select
								DrugProtoMnn_id as \"DrugProtoMnn_id\"
							from
								v_DrugProtoMnn
							where
								DrugProtoMnn_Code = :DrugProtoMnn_Code and
								ReceptFinance_id = :ReceptFinance_id
						";
						$mnn_id = $this->getFirstResultFromQuery($query, array(
							'DrugProtoMnn_Code' => $row[2],
							'ReceptFinance_id' => $data['ReceptFinance_id']
						));

						if ($mnn_id > 0) {
							//сохранение в таблицу
							$result = $this->save(array(
								'DrugRequestRecept_id' => null,
								'Server_id' => $data['Server_id'],
								'DrugRequestPeriod_id' => $data['DrugRequestPeriod_id'],
								'DrugProtoMnn_id' => $mnn_id,
								'DrugRequestRecept_Kolvo' => $row[11],
								'DrugRequestRecept_KolvoRAS' => !empty($row[16]) ? $row[16] : null ,
								'DrugRequestRecept_KolvoPurch' => !empty($row[17]) ? $row[17] : null,
								'DrugRequestRecept_KolvoDopPurch' => !empty($row[18]) ? $row[18] : null,
								'pmUser_id' => $data['pmUser_id']
							));
						} else {
							//Выводим в лог сообщение о ошибке
							$err_arr[] = "Не удалось найти медикамент. Код: {$row[2]}".(!empty($row[5]) ? " Наименование: {$row[5]}." : "");
						}
					} else {
						if (isset($row[1]) && $row[1] == 1 && isset($row[2]) && $row[2] == 2 && isset($row[5]) && $row[5] == 3) {
							$data_start = true;
						}
					}
				}
			}
		}

		if (count($err_arr) > 0) {
			$result['Error_Array'] = $err_arr;
		}

		return $result;
	}

	/**
	 * Получние количества заявок
	 */
	function getCount($data) {
		$q = "
			select
				count(drr.DrugRequestRecept_id) as \"cnt\"
			from
				v_DrugRequestRecept drr
				left join v_DrugProtoMnn dpm on dpm.DrugProtoMnn_id = drr.DrugProtoMnn_id
			where
				drr.DrugRequestPeriod_id = :DrugRequestPeriod_id and
				dpm.ReceptFinance_id = :ReceptFinance_id;
		";
		$cnt = $this->getFirstResultFromQuery($q, $data);
		if ($cnt !== false) {
			return array('success' => true, 'cnt' => $cnt);
		}
		return array('success' => false);
	}

	/**
	 * Получние количества заявок
	 */
	function getDrugRequestReceptTotalKolvo($data) {
		$where = array();

		if ($data['Drug_id'] > 0 || $data['DrugMnn_id'] > 0) {
			$where[] = "dpm.ReceptFinance_id = :ReceptFinance_id";
			$where[] = "drrl.Lpu_id = :Lpu_id";

			if ($data['Drug_id'] > 0) {
				$where[] = "
					drr.DrugProtoMnn_id in (
						select
							ds.DrugProtoMnn_id as DrugProtoMnn_id
						from
							DrugState ds
							left join DrugProto dpr on ds.DrugProto_id=dpr.DrugProto_id
							inner join drp on drp.DrugRequestPeriod_id=dpr.DrugRequestPeriod_id
							left join Drug d on d.Drug_id=ds.Drug_id
						where
							ds.Drug_id = :Drug_id
					)
				";
			}

			if ($data['DrugMnn_id'] > 0) {
				$where[] = "
					dpm.DrugMnn_id = :DrugMnn_id
				";
			}

			if (count($where) > 0) {
				$where = 'where '.join(' and ', $where);
			} else {
				$where = null;
			}

			$q = "
				with drp as (
					select
						DrugRequestPeriod_id as DrugRequestPeriod_id
					from
						v_DrugRequestPeriod
					where
						DrugRequestPeriod_begDate <= :Date and DrugRequestPeriod_endDate >= :Date

				)
				select
					sum(case
						when drrl.DrugRequestReceptLpu_KolvoAcc > coalesce(drrl.DrugRequestReceptLpu_KolvoRec,0) then drrl.DrugRequestReceptLpu_KolvoAcc - coalesce(drrl.DrugRequestReceptLpu_KolvoRec,0)
						else 0
					end) as \"DrugRequestRecept_TotalKolvo\"
				from
					v_DrugRequestRecept drr
					inner join drp on drp.DrugRequestPeriod_id = drr.DrugRequestPeriod_id
					left join v_DrugProtoMnn dpm on dpm.DrugProtoMnn_id = drr.DrugProtoMnn_id
					left join v_DrugRequestReceptLpu drrl on drrl.DrugRequestRecept_id = drr.DrugRequestRecept_id
				{$where};
			";
			$sum = $this->getFirstResultFromQuery($q, $data);
			if ($sum !== false) {
				return array('success' => true, 'sum' => $sum);
			}
		}

		return array('success' => false);
	}
}