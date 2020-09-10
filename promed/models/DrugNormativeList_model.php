<?php defined('BASEPATH') or die ('No direct script access allowed');

class DrugNormativeList_model extends swModel {
	private $DrugNormativeList_id;//DrugNormativeList_id
	private $DrugNormativeList_Name;//Наименование
	private $WhsDocumentCostItemType_id;//Программа ЛЛО
	private $PersonRegisterType_id;//Тип перечня
	private $DrugNormativeList_BegDT;//Дата начала действия записи
	private $DrugNormativeList_EndDT;//Дата окончания действия записи
	private $pmUser_id;//Идентификатор пользователя системы Промед

	/**
	 * Гет
	 */
	public function getDrugNormativeList_id() { return $this->DrugNormativeList_id;}
	/**
	 * Сет
	 */
	public function setDrugNormativeList_id($value) { $this->DrugNormativeList_id = $value; }

	/**
	 * Гет
	 */
	public function getDrugNormativeList_Name() { return $this->DrugNormativeList_Name;}
	/**
	 * Сет
	 */
	public function setDrugNormativeList_Name($value) { $this->DrugNormativeList_Name = $value; }

	/**
	 * Гет
	 */
	public function getWhsDocumentCostItemType_id() { return $this->WhsDocumentCostItemType_id;}
	/**
	 * Сет
	 */
	public function setWhsDocumentCostItemType_id($value) { $this->WhsDocumentCostItemType_id = $value; }

	/**
	 * Гет
	 */
	public function getPersonRegisterType_id() { return $this->PersonRegisterType_id;}
	/**
	 * Сет
	 */
	public function setPersonRegisterType_id($value) { $this->PersonRegisterType_id = $value; }

	/**
	 * Гет
	 */
	public function getDrugNormativeList_BegDT() { return $this->DrugNormativeList_BegDT;}
	/**
	 * Cет
	 */
	public function setDrugNormativeList_BegDT($value) { $this->DrugNormativeList_BegDT = $value; }

	/**
	 * Гет
	 */
	public function getDrugNormativeList_EndDT() { return $this->DrugNormativeList_EndDT;}
	/**
	 * Cет
	 */
	public function setDrugNormativeList_EndDT($value) { $this->DrugNormativeList_EndDT = $value; }

	/**
	 * Гет
	 */
	public function getpmUser_id() { return $this->pmUser_id;}
	/**
	 * Cет
	 */
	public function setpmUser_id($value) { $this->pmUser_id = $value; }

	/**
	 * Конструктор
	 */
	function __construct(){
		if (isset($_SESSION['pmuser_id'])) {
			$this->setpmUser_id($_SESSION['pmuser_id']);
		} else {
			throw new Exception('Значение pmuser_id не установлено в текущей сессии (не выполнен вход в Промед?)');
		}
	}

	/**
	 * Получение списка
	 */
	function load() {
		$q = "
			select
				DrugNormativeList_id, DrugNormativeList_Name, PersonRegisterType_id, WhsDocumentCostItemType_id, DrugNormativeList_BegDT, DrugNormativeList_EndDT
			from
				dbo.v_DrugNormativeList with (nolock)
			where
				DrugNormativeList_id = :DrugNormativeList_id
		";
		$r = $this->db->query($q, array('DrugNormativeList_id' => $this->DrugNormativeList_id));
		if ( is_object($r) ) {
			$r = $r->result('array');
			if (isset($r[0])) {
				$this->DrugNormativeList_id = $r[0]['DrugNormativeList_id'];
				$this->DrugNormativeList_Name = $r[0]['DrugNormativeList_Name'];
				$this->WhsDocumentCostItemType_id = $r[0]['WhsDocumentCostItemType_id'];
				$this->PersonRegisterType_id = $r[0]['PersonRegisterType_id'];
				$this->DrugNormativeList_BegDT = $r[0]['DrugNormativeList_BegDT'];
				$this->DrugNormativeList_EndDT = $r[0]['DrugNormativeList_EndDT'];
				return $r;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	/**
	 * Получение списка
	 */
	function loadList($filter) {
		$where = array();
		$p = array();
		if (isset($filter['DrugNormativeList_id']) && $filter['DrugNormativeList_id']) {
			$where[] = 'v_DrugNormativeList.DrugNormativeList_id = :DrugNormativeList_id';
			$p['DrugNormativeList_id'] = $filter['DrugNormativeList_id'];
		}
		if (isset($filter['DrugNormativeList_Name']) && $filter['DrugNormativeList_Name']) {
			$where[] = 'v_DrugNormativeList.DrugNormativeList_Name = :DrugNormativeList_Name';
			$p['DrugNormativeList_Name'] = $filter['DrugNormativeList_Name'];
		}
		if (isset($filter['PersonRegisterType_id']) && $filter['PersonRegisterType_id']) {
			$where[] = 'v_DrugNormativeList.PersonRegisterType_id = :PersonRegisterType_id';
			$p['PersonRegisterType_id'] = $filter['PersonRegisterType_id'];
		}
		if (isset($filter['DrugNormativeList_BegDT']) && $filter['DrugNormativeList_BegDT']) {
			$where[] = 'v_DrugNormativeList.DrugNormativeList_BegDT = :DrugNormativeList_BegDT';
			$p['DrugNormativeList_BegDT'] = $filter['DrugNormativeList_BegDT'];
		}
		if (isset($filter['DrugNormativeList_EndDT']) && $filter['DrugNormativeList_EndDT']) {
			$where[] = 'v_DrugNormativeList.DrugNormativeList_EndDT = :DrugNormativeList_EndDT';
			$p['DrugNormativeList_EndDT'] = $filter['DrugNormativeList_EndDT'];
		}
		$where_clause = implode(' AND ', $where);
		if (strlen($where_clause)) {
			$where_clause = 'WHERE '.$where_clause;
		}
		$q = "
			SELECT
				v_DrugNormativeList.DrugNormativeList_id,
				v_DrugNormativeList.DrugNormativeList_Name,
				v_DrugNormativeList.PersonRegisterType_id,
				WhsDocumentCostItemType_ref.WhsDocumentCostItemType_Name,
				CONVERT(varchar(10), v_DrugNormativeList.DrugNormativeList_BegDT, 104)  DrugNormativeList_BegDT,
				CONVERT(varchar(10), v_DrugNormativeList.DrugNormativeList_EndDT, 104)  DrugNormativeList_EndDT,
				PersonRegisterType_id_ref.PersonRegisterType_Name PersonRegisterType_Name,
				(select count(DrugNormativeListSpec_id) from v_DrugNormativeListSpec with (nolock) where DrugNormativeList_id = v_DrugNormativeList.DrugNormativeList_id) as DrugNormativeListSpec_count
			FROM
				dbo.v_DrugNormativeList WITH (NOLOCK)
				LEFT JOIN dbo.v_PersonRegisterType PersonRegisterType_id_ref WITH (NOLOCK) ON PersonRegisterType_id_ref.PersonRegisterType_id = v_DrugNormativeList.PersonRegisterType_id
				LEFT JOIN dbo.WhsDocumentCostItemType WhsDocumentCostItemType_ref WITH (NOLOCK) ON WhsDocumentCostItemType_ref.WhsDocumentCostItemType_id = v_DrugNormativeList.WhsDocumentCostItemType_id
			$where_clause
		";
		$result = $this->db->query($q, $filter);
		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Сохранение
	 */
	function save() {
		$procedure = 'p_DrugNormativeList_ins';
		if ( $this->DrugNormativeList_id > 0 ) {
			$procedure = 'p_DrugNormativeList_upd';
		}
		$q = "
			declare
				@DrugNormativeList_id bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @DrugNormativeList_id = :DrugNormativeList_id;
			exec dbo." . $procedure . "
				@DrugNormativeList_id = :DrugNormativeList_id,
				@DrugNormativeList_Name = :DrugNormativeList_Name,
				@WhsDocumentCostItemType_id = :WhsDocumentCostItemType_id,
				@PersonRegisterType_id = :PersonRegisterType_id,
				@DrugNormativeList_BegDT = :DrugNormativeList_BegDT,
				@DrugNormativeList_EndDT = :DrugNormativeList_EndDT,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @DrugNormativeList_id as DrugNormativeList_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$p = array(
			'DrugNormativeList_id' => $this->DrugNormativeList_id,
			'DrugNormativeList_Name' => $this->DrugNormativeList_Name,
			'WhsDocumentCostItemType_id' => $this->WhsDocumentCostItemType_id,
			'PersonRegisterType_id' => $this->PersonRegisterType_id,
			'DrugNormativeList_BegDT' => $this->DrugNormativeList_BegDT,
			'DrugNormativeList_EndDT' => $this->DrugNormativeList_EndDT,
			'pmUser_id' => $this->pmUser_id
		);
		$r = $this->db->query($q, $p);
		if ( is_object($r) ) {
		    $result = $r->result('array');
		    $this->DrugNormativeList_id = $result[0]['DrugNormativeList_id'];
		} else {
			log_message('error', var_export(array('q' => $q, 'p' => $p, 'e' => sqlsrv_errors()), true));
			$result = array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных'));
		}
		return $result;
	}

	/**
	 * Удаление
	 */
	function delete() {
		$q = "
			delete from
				DrugNormativeListSpecTorgLink
			where
				DrugNormativeListSpec_id in (
					select
						DrugNormativeListSpec_id
					from
						DrugNormativeListSpec
					where
						DrugNormativeList_id = :DrugNormativeList_id
				);
		";
		$r = $this->db->query($q, array(
			'DrugNormativeList_id' => $this->DrugNormativeList_id
		));

		$q = "
			delete from
				DrugNormativeListSpecFormsLink
			where
				DrugNormativeListSpec_id in (
					select
						DrugNormativeListSpec_id
					from
						DrugNormativeListSpec
					where
						DrugNormativeList_id = :DrugNormativeList_id
				);
		";
		$r = $this->db->query($q, array(
			'DrugNormativeList_id' => $this->DrugNormativeList_id
		));

		$q = "
			delete from
				DrugNormativeListSpec
			where
				DrugNormativeList_id = :DrugNormativeList_id;
		";
		$r = $this->db->query($q, array(
			'DrugNormativeList_id' => $this->DrugNormativeList_id
		));
		
		$q = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec dbo.p_DrugNormativeList_del
				@DrugNormativeList_id = :DrugNormativeList_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$r = $this->db->query($q, array(
			'DrugNormativeList_id' => $this->DrugNormativeList_id
		));

		if ( is_object($r) ) {
			return $r->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Сохранение
	 */
	function saveDrugNormativeListSpecFromJSON($data) {
		if (!empty($data['DrugNormativeList_JsonData']) && $data['DrugNormativeList_id'] > 0) {
			ConvertFromWin1251ToUTF8($data['DrugNormativeList_JsonData']);
			$dt = (array) json_decode($data['DrugNormativeList_JsonData']);
			foreach($dt as $record) {
				if ($record->state == 'add' || $record->state == 'edit') {
					$this->editDrugNormativeListSpec(array_merge((array)$record, array(
						'DrugNormativeList_id' => $data['DrugNormativeList_id'],
						'pmUser_id' => $data['pmUser_id']
					)));
				} else if ($record->state == 'delete') {
					if (isset($record->DrugNormativeListSpec_id) && $record->DrugNormativeListSpec_id > 0)
						$this->deleteDrugNormativeListSpec($record->DrugNormativeListSpec_id);
				}
			}
		}

		return array(array('Error_Code' => '', 'Error_Msg' => ''));
	}

	/**
	 * Редактирование
	 */
	function editDrugNormativeListSpec($data) {
		$spec_id = 0;
		if ($data['state'] == 'edit') {
			$this->deleteDrugNormativeListSpec($data['DrugNormativeListSpec_id']);
		}
		$torg_name_array = explode(',',$data['TorgNameArray']);
		$drug_form_array = explode(',',$data['DrugFormArray']);

		$q = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000),
				@DrugNormativeListSpec_id bigint = 0,
				@DrugNormativeListSpec_IsVK bigint;

			set @DrugNormativeListSpec_IsVK = (select YesNo_id from YesNo with (nolock) where YesNo_code = :DrugNormativeListSpec_IsVK)

			exec dbo.p_DrugNormativeListSpec_ins
				@DrugNormativeListSpec_id = @DrugNormativeListSpec_id output,
				@DrugNormativeList_id = :DrugNormativeList_id,
				@DrugNormativeListSpecMNN_id = :DrugNormativeListSpecMNN_id,
				@DrugNormativeListSpec_BegDT = :DrugNormativeListSpec_BegDT,
				@DrugNormativeListSpec_EndDT = :DrugNormativeListSpec_EndDT,
				@DrugNormativeListSpec_IsVK = @DrugNormativeListSpec_IsVK,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @DrugNormativeListSpec_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$r = $this->getFirstResultFromQuery($q, array(
			'DrugNormativeList_id' => $data['DrugNormativeList_id'],
			'DrugNormativeListSpecMNN_id' => $data['RlsActmatters_id'] > 0 ? $data['RlsActmatters_id'] : null,
			'DrugNormativeListSpec_BegDT' => $data['DrugNormativeListSpec_BegDT'] != '' ? join('-', array_reverse(explode('.', $data['DrugNormativeListSpec_BegDT']))) : null,
			'DrugNormativeListSpec_EndDT' => $data['DrugNormativeListSpec_EndDT'] != '' ? join('-', array_reverse(explode('.', $data['DrugNormativeListSpec_EndDT']))) : null,
			'DrugNormativeListSpec_IsVK' => $data['DrugNormativeListSpec_IsVK']  ? 1 : 0,
			'pmUser_id' => $data['pmUser_id']
		));

		if ($r && $r > 0) {
			$spec_id = $r;
		}

		if ($spec_id > 0) {
			foreach($torg_name_array as $torg_id) {
				if ($torg_id > 0) {
					$q = "
						declare
							@ErrCode int,
							@ErrMessage varchar(4000);

						exec dbo.p_DrugNormativeListSpecTorgLink_ins
							@DrugNormativeListSpecTorgLink_id = 0,
							@DrugNormativeListSpec_id = :DrugNormativeListSpec_id,
							@DrugNormativeListSpecTorg_id = :DrugNormativeListSpecTorg_id,
							@pmUser_id = :pmUser_id,
							@Error_Code = @ErrCode output,
							@Error_Message = @ErrMessage output;
						select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
					";
					$r = $this->db->query($q, array(
						'DrugNormativeListSpec_id' => $spec_id,
						'DrugNormativeListSpecTorg_id' => $torg_id,
						'pmUser_id' => $data['pmUser_id']
					));
				}
			}

			foreach($drug_form_array as $form_id) {
				if ($form_id > 0) {
					$q = "
						declare
							@ErrCode int,
							@ErrMessage varchar(4000);

						exec dbo.p_DrugNormativeListSpecFormsLink_ins
							@DrugNormativeListSpecFormsLink_id = 0,
							@DrugNormativeListSpec_id = :DrugNormativeListSpec_id,
							@DrugNormativeListSpecForms_id = :DrugNormativeListSpecForms_id,
							@pmUser_id = :pmUser_id,
							@Error_Code = @ErrCode output,
							@Error_Message = @ErrMessage output;
						select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
					";
					$r = $this->db->query($q, array(
						'DrugNormativeListSpec_id' => $spec_id,
						'DrugNormativeListSpecForms_id' => $form_id,
						'pmUser_id' => $data['pmUser_id']
					));
				}
			}
		}
	}

	/**
	 * Удаление
	 */
	function deleteDrugNormativeListSpec($id) {
		if ($id > 0) {
			$q = "
				delete from DrugNormativeListSpecTorgLink
				where
					DrugNormativeListSpec_id = :DrugNormativeListSpec_id;

				delete from DrugNormativeListSpecFormsLink
				where
					DrugNormativeListSpec_id = :DrugNormativeListSpec_id;

				delete from DrugNormativeListSpec
				where
					DrugNormativeListSpec_id = :DrugNormativeListSpec_id;
			";
			$r = $this->db->query($q, array(
				'DrugNormativeListSpec_id' => $id
			));
		}
	}

	/**
	 * Получение списка
	 */
	function loadDrugNormativeListSpecList($filter) {
		$q = "
			select
				dnls.DrugNormativeListSpec_id,
				dnls.DrugNormativeListSpecMNN_id as RlsActmatters_id,
				am.RUSNAME as RlsActmatters_RusName,
				am.STRONGGROUPID,
				am.NARCOGROUPID,
				CONVERT(varchar(10), dnls.DrugNormativeListSpec_BegDT, 104) as DrugNormativeListSpec_BegDT,
				CONVERT(varchar(10), dnls.DrugNormativeListSpec_EndDT, 104) as DrugNormativeListSpec_EndDT,
				(CONVERT(varchar(10), dnls.DrugNormativeListSpec_BegDT, 104) + ' - ' + CONVERT(varchar(10), dnls.DrugNormativeListSpec_EndDT, 104)) as DrugNormativeListSpec_DateRange,
				atx.NAME as ATX_Name,
				atx.CLSATC_ID as ATX_id,
				parent_atx.NAME as ParentATX_Name,
				parent_atx.CLSATC_ID as ParentATX_id,
				replace((
					select
						convert(varchar, dnlsfl.DrugNormativeListSpecForms_id) as 'data()'
					from
						v_DrugNormativeListSpecFormsLink dnlsfl with (nolock)
					where
						dnlsfl.DrugNormativeListSpec_id = dnls.DrugNormativeListSpec_id
					for xml path('')
				), ' ', ',') as \"DrugFormArray\",
				replace(replace((
					select
						FULLNAME+',' as 'data()'
					from
						v_DrugNormativeListSpecFormsLink dnlsfl with (nolock)
						left join rls.CLSDRUGFORMS cdf with (nolock) on cdf.CLSDRUGFORMS_ID = dnlsfl.DrugNormativeListSpecForms_id
					where
						dnlsfl.DrugNormativeListSpec_id = dnls.DrugNormativeListSpec_id
					for xml path('')
				)+',,', ',,,', ''), ',,', '') as \"DrugForm_NameList\",
				replace((
					select
						convert(varchar, dnlstl.DrugNormativeListSpecTorg_id) as 'data()'
					from
						v_DrugNormativeListSpecTorgLink dnlstl with (nolock)
					where
						dnlstl.DrugNormativeListSpec_id = dnls.DrugNormativeListSpec_id
					for xml path('')
				), ' ', ',') as \"TorgNameArray\",
				replace(replace((
					select
						isnull(trd_code.code+', ','')+NAME+';' as 'data()'
					from
						v_DrugNormativeListSpecTorgLink dnlstl with (nolock)
						left join rls.TRADENAMES trd with (nolock) on trd.TRADENAMES_ID = dnlstl.DrugNormativeListSpecTorg_id
						outer apply (
							select top 1
								DrugTorgCode_Code as code
							from
								rls.v_DrugTorgCode with (nolock)
							where
								TRADENAMES_id = trd.TRADENAMES_ID
							order by
								DrugTorgCode_id
						) trd_code
					where
						dnlstl.DrugNormativeListSpec_id = dnls.DrugNormativeListSpec_id
					for xml path('')
				)+';;', ';;;', ''), ';;', '') as TorgName_NameList,
				dnls_yn.YesNo_Code as DrugNormativeListSpec_IsVK,
				dmc.DrugMnnCode_Code
			from
				v_DrugNormativeListSpec dnls with (nolock)
				left join rls.ACTMATTERS am with (nolock) on am.ACTMATTERS_ID = dnls.DrugNormativeListSpecMNN_id
				outer apply (
					select top 1 ca.NAME, ca.CLSATC_ID
					from rls.PREP_ACTMATTERS pam with (nolock)
					left join rls.PREP_ATC pca with (nolock) on pca.PREPID = pam.PREPID
					left join rls.CLSATC ca with (nolock) on ca.CLSATC_ID = pca.UNIQID
					where pam.MATTERID = dnls.DrugNormativeListSpecMNN_id
				) atx
				outer apply (
					select top 1 ca.NAME, ca.CLSATC_ID
					from rls.CLSATC ca with (nolock)
					where atx.CLSATC_ID > 0 and CLSATC_ID = dbo.GetClsAtcParentID(atx.CLSATC_ID, 5)
				) parent_atx
				outer apply (
					select top 1
						DrugMnnCode_Code
					from
						rls.v_DrugMnnCode with (nolock)
					where
						ACTMATTERS_id = am.ACTMATTERS_id
					order by
						DrugMnnCode_id
				) dmc
				left join YesNo dnls_yn with (nolock) on dnls_yn.YesNo_id = dnls.DrugNormativeListSpec_IsVK
			where
				DrugNormativeList_id = :DrugNormativeList_id
		";
		$result = $this->db->query($q, $filter);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Получение контекста
	 */
	function getDrugNormativeListSpecContext($data) {
		$result = array(
			'RlsActmatters_RusName' => null,
			'ATX_id' => null,
			'ATX_Name' => null,
			'ParentATX_Name' => null,
			'ParentATX_id' => null,
			'DrugForm_NameList' => null,
			'TorgName_NameList' => null,
			'DrugMnnCode_Code' => null
		);

		$q = "
			select
				replace(replace((
					select FULLNAME+',' as 'data()'
					from rls.CLSDRUGFORMS cdf with (nolock)
					where
						cdf.CLSDRUGFORMS_ID in (".(!empty($data['DrugFormArray']) ?  $data['DrugFormArray'] : "null").")
					for xml path('')
				)+',,', ',,,', ''), ',,', '') as DrugForm_NameList,
				replace(replace((
					select isnull(trd_code.code+', ','')+NAME+';' as 'data()'
					from
						rls.TRADENAMES trd with (nolock)
						outer apply (
							select top 1
								DrugTorgCode_Code as code
							from
								rls.v_DrugTorgCode with (nolock)
							where
								TRADENAMES_id = trd.TRADENAMES_ID
							order by
								DrugTorgCode_id
						) trd_code
					where
						trd.TRADENAMES_ID in (".(!empty($data['TorgNameArray']) ?  $data['TorgNameArray'] : "null").")
					for xml path('')
				)+';;', ';;;', ''), ';;', '') as TorgName_NameList
		";
		$r = $this->getFirstRowFromQuery($q);
		if (is_array($r)) {
			$result = array_merge($result, $r);
		}

		$q = "
			select
				RUSNAME as RlsActmatters_RusName,
				atx.CLSATC_ID as ATX_id,
				atx.Name as ATX_Name,
				parent_atx.NAME as ParentATX_Name,
				parent_atx.CLSATC_ID as ParentATX_id,
				replace(replace((
					select FULLNAME+',' as 'data()'
					from rls.CLSDRUGFORMS cdf with (nolock)
					where
						cdf.CLSDRUGFORMS_ID in (".(!empty($data['DrugFormArray']) ?  $data['DrugFormArray'] : "null").")
					for xml path('')
				)+',,', ',,,', ''), ',,', '') as DrugForm_NameList,
				replace(replace((
					select isnull(trd_code.code+', ','')+NAME+';' as 'data()'
					from
						rls.TRADENAMES trd with (nolock)
						outer apply (
							select top 1
								DrugTorgCode_Code as code
							from
								rls.v_DrugTorgCode with (nolock)
							where
								TRADENAMES_id = trd.TRADENAMES_ID
							order by
								DrugTorgCode_id
						) trd_code
					where
						trd.TRADENAMES_ID in (".(!empty($data['TorgNameArray']) ?  $data['TorgNameArray'] : "null").")
					for xml path('')
				)+';;', ';;;', ''), ';;', '') as TorgName_NameList,
				dmc.DrugMnnCode_Code
			from
				rls.ACTMATTERS am with (nolock)
				outer apply (
					select top 1 ca.NAME, ca.CLSATC_ID
					from rls.PREP_ACTMATTERS pam with (nolock)
					left join rls.PREP_ATC pca with (nolock) on pca.PREPID = pam.PREPID
					left join rls.CLSATC ca with (nolock) on ca.CLSATC_ID = pca.UNIQID
					where pam.MATTERID = am.ACTMATTERS_ID
				) atx
				outer apply (
					select top 1 ca.NAME, ca.CLSATC_ID
					from rls.CLSATC ca with (nolock)
					where atx.CLSATC_ID > 0 and CLSATC_ID = dbo.GetClsAtcParentID(atx.CLSATC_ID, 5)
				) parent_atx
				outer apply (
					select top 1
						DrugMnnCode_Code
					from
						rls.v_DrugMnnCode with (nolock)
					where
						ACTMATTERS_id = am.ACTMATTERS_id
					order by
						DrugMnnCode_id
				) dmc
			where
				ACTMATTERS_ID = :RlsActmatters_id
		";
		$r = $this->db->query($q, array(
			'RlsActmatters_id' => $data['RlsActmatters_id']
		));
		if ( is_object($r) ) {
			$r = $r->result('array');
			if (isset($r[0])) {
				$result = array_merge($result, $r[0]);
			}
		}

		return array($result);
	}

	/**
	 * Получение списка
	 */
	function copyDrugNormativeList($data) {
		$q = "
			declare
				@DrugNormativeList_id int = null,
				@Error_Code bigint,
				@Error_Message varchar(4000);
			
			set nocount on
			begin try			
			begin tran
				insert into	DrugNormativeList (
					DrugNormativeList_Name,
					PersonRegisterType_id,
					DrugNormativeList_BegDT,
					DrugNormativeList_EndDT,
					pmUser_insID,
					pmUser_updID,
					DrugNormativeList_insDT,
					DrugNormativeList_updDT
				)
				select
					DrugNormativeList_Name,
					PersonRegisterType_id,
					DrugNormativeList_BegDT,
					DrugNormativeList_EndDT,
					:pmUser_id,
					:pmUser_id,
					dbo.tzGetDate(),
					dbo.tzGetDate()
				from
					v_DrugNormativeList with (nolock)
				where
					DrugNormativeList_id = :DrugNormativeList_id
				
				set @DrugNormativeList_id = (select scope_identity())

			commit tran
			end try				
			begin catch
				set @Error_Code = error_number()
				set @Error_Message = error_message()
				if @@trancount>0
					rollback tran
			end catch

			set nocount off
			
			select
				@DrugNormativeList_id as DrugNormativeList_id,
				@Error_Code as Error_Code,
				@Error_Message as Error_Msg;
		";
		$p = array(
			'DrugNormativeList_id' => $data['DrugNormativeList_id'],
			'pmUser_id' => $data['pmUser_id']
		);
		$r = $this->db->query($q, $p);
		if ( is_object($r) ) {
			$spec_count = 0;
		    $result = $r->result('array');
		    $new_list_id = $result[0]['DrugNormativeList_id'];

			$q = "
				select
					DrugNormativeListSpec_id,
					DrugNormativeListSpecMNN_id,
					DrugNormativeListSpec_BegDT,
					DrugNormativeListSpec_EndDT,
					DrugNormativeListSpec_IsVK
				from
					v_DrugNormativeListSpec with (nolock)
				where
					DrugNormativeList_id = :DrugNormativeList_id;
			";
			$r = $this->db->query($q, array(
				'DrugNormativeList_id' =>  $data['DrugNormativeList_id']
			));
			if (is_object($r)) {
				$result = $r->result('array');

				foreach($result as $spec_data) {
					$q = "
						declare
							@ErrCode int,
							@ErrMessage varchar(4000),
							@DrugNormativeListSpec_id bigint = 0;

						exec dbo.p_DrugNormativeListSpec_ins
							@DrugNormativeListSpec_id = @DrugNormativeListSpec_id output,
							@DrugNormativeList_id = :DrugNormativeList_id,
							@DrugNormativeListSpecMNN_id = :DrugNormativeListSpecMNN_id,
							@DrugNormativeListSpec_BegDT = :DrugNormativeListSpec_BegDT,
							@DrugNormativeListSpec_EndDT = :DrugNormativeListSpec_EndDT,
							@DrugNormativeListSpec_IsVK = :DrugNormativeListSpec_IsVK,
							@pmUser_id = :pmUser_id,
							@Error_Code = @ErrCode output,
							@Error_Message = @ErrMessage output;
						select @DrugNormativeListSpec_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
					";

					$r = $this->getFirstResultFromQuery($q, array(
						'DrugNormativeList_id' => $new_list_id,
						'DrugNormativeListSpecMNN_id' => $spec_data['DrugNormativeListSpecMNN_id'],
						'DrugNormativeListSpec_BegDT' => $spec_data['DrugNormativeListSpec_BegDT'],
						'DrugNormativeListSpec_EndDT' => $spec_data['DrugNormativeListSpec_EndDT'],
						'DrugNormativeListSpec_IsVK' => $spec_data['DrugNormativeListSpec_IsVK'],
						'pmUser_id' => $data['pmUser_id']
					));

					if ($r > 0) {
						$spec_count++;

						$q = "
							insert into DrugNormativeListSpecFormsLink (
								DrugNormativeListSpec_id,
								DrugNormativeListSpecForms_id,
								pmUser_insID,
								pmUser_updID,
								DrugNormativeListSpecFormsLink_insDT,
								DrugNormativeListSpecFormsLink_updDT
							)
							select
								:NewDrugNormativeListSpec_id,
								DrugNormativeListSpecForms_id,
								:pmUser_id,
								:pmUser_id,
								dbo.tzGetDate(),
								dbo.tzGetDate()
							from
								v_DrugNormativeListSpecFormsLink with (nolock)
							where
								DrugNormativeListSpec_id = :OldDrugNormativeListSpec_id;
						";
						$result = $this->db->query($q, array(
							'NewDrugNormativeListSpec_id' => $r,
							'OldDrugNormativeListSpec_id' => $spec_data['DrugNormativeListSpec_id'],
							'pmUser_id' => $data['pmUser_id']
						));

						$q = "
							insert into DrugNormativeListSpecTorgLink (
								DrugNormativeListSpec_id,
								DrugNormativeListSpecTorg_id,
								pmUser_insID,
								pmUser_updID,
								DrugNormativeListSpecTorgLink_insDT,
								DrugNormativeListSpecTorgLink_updDT
							)
							select
								:NewDrugNormativeListSpec_id,
								DrugNormativeListSpecTorg_id,
								:pmUser_id,
								:pmUser_id,
								dbo.tzGetDate(),
								dbo.tzGetDate()
							from
								v_DrugNormativeListSpecTorgLink with (nolock)
							where
								DrugNormativeListSpec_id = :OldDrugNormativeListSpec_id;
						";
						$result = $this->db->query($q, array(
							'NewDrugNormativeListSpec_id' => $r,
							'OldDrugNormativeListSpec_id' => $spec_data['DrugNormativeListSpec_id'],
							'pmUser_id' => $data['pmUser_id']
						));
					}
				}
			}
			$result = array(array(
				'DrugNormativeListSpec_count' => $spec_count,
				'DrugNormativeList_id' => $new_list_id,
				'Error_Code' => null,
				'Error_Msg' => null
			));
		} else {
			log_message('error', var_export(array('q' => $q, 'p' => $p, 'e' => sqlsrv_errors()), true));
			$result = array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных'));
		}
		return $result;
	}

	/**
	 * Получение списка
	 */
	function loadDrugFormsCombo($data) {
		$where = array();

		$where[] = "cdf.FULLNAME != ''";

		if ($data['RlsClsdrugforms_id'] > 0) {
			$where[] = 'cdf.CLSDRUGFORMS_ID = :RlsClsdrugforms_id';
		} else {
			if (strlen($data['query']) > 0) {
				$data['query'] = '%'.$data['query'].'%';
				$where[] = 'cdf.FULLNAME like :query';
			}
			if ($data['RlsActmatters_id']) {
				$where[] = "cdf.CLSDRUGFORMS_ID in (
						select
					p.DRUGFORMID
				from
					rls.PREP_ACTMATTERS pa with (nolock)
					left join rls.PREP p with (nolock) on p.Prep_id = pa.PREPID
				where
					pa.MATTERID = :RlsActmatters_id
			)";
			}
		}

		$where = join(' and ', $where);

		$query = "
			select distinct top 500
				cdf.CLSDRUGFORMS_ID as RlsClsdrugforms_id,
				cdf.FULLNAME as RlsClsdrugforms_Name
			from
				rls.Clsdrugforms as cdf with (nolock)
			where
				{$where}
			order by
				cdf.FULLNAME
		";
		$result = $this->db->query($query, $data);
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Получение списка
	 */
	function loadTradenamesCombo($data) {
		$where = array();

		$where[] = "t.NAME != ''";

		if ($data['RlsTradenames_id'] > 0) {
			$where[] = 't.TRADENAMES_ID = :RlsTradenames_id';
		} else {
			if (strlen($data['query']) > 0) {
				$data['query'] = '%'.$data['query'].'%';
				$where[] = 't.NAME like :query';
			}
			if ($data['RlsActmatters_id'] > 0) {
				$where[] = "t.TRADENAMES_ID in (
					select
						p.TRADENAMEID
					from
						rls.PREP_ACTMATTERS pa with (nolock)
						left join rls.PREP p with (nolock) on p.Prep_id = pa.PREPID
					where
						pa.MATTERID = :RlsActmatters_id
						".(isset($data['DrugFormList']) && !empty($data['DrugFormList']) ? "and p.DRUGFORMID in (".$data['DrugFormList'].")" : "")."
				)";
			} else {
				$where[] = "t.TRADENAMES_ID in (
					select
						p.TRADENAMEID
					from
						rls.PREP p with (nolock)
					where
						p.Prep_id not in (select PREPID from rls.PREP_ACTMATTERS with (nolock))
						".(isset($data['DrugFormList']) && !empty($data['DrugFormList']) ? "and p.DRUGFORMID in (".$data['DrugFormList'].")" : "")."
				)";
			}
		}

		$where = join(' and ', $where);

		$query = "
			select distinct top 500
				t.TRADENAMES_ID as RlsTradenames_id,
				t.NAME as RlsTradenames_Name
			from
				rls.TRADENAMES t with (nolock)
			where
				{$where}
			order by
				t.NAME
		";
		$result = $this->db->query($query, $data);
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Получение списка по Drug_id
	 */
	function loadListByRlsDrug($data) {
		$params = array('Drug_id' => $data['Drug_id']);

		$query = "
			select
				DNL.DrugNormativeList_id,
				DNL.DrugNormativeList_Name,
				MT.PersonRegisterType_id,
				MT.PersonRegisterType_Code,
				MT.PersonRegisterType_Name,
				convert(varchar(10), DNL.DrugNormativeList_BegDT, 104) as DrugNormativeList_begDate
			from
				v_DrugNormativeList DNL with(nolock)
				left join v_PersonRegisterType MT with(nolock) on MT.PersonRegisterType_id = DNL.PersonRegisterType_id
				outer apply(
					select top 1
						t.DrugNormativeList_id
					from
						rls.v_Drug D with (nolock)
						inner join v_DrugNormativeListSpec t with(nolock) on t.DrugNormativeList_id = DNL.DrugNormativeList_id
						inner join rls.v_DrugComplexMnn DCM with(nolock) on DCM.DrugComplexMnn_id = D.DrugComplexMnn_id
						inner join rls.v_ACTMATTERS AM with(nolock) on AM.ACTMATTERS_ID = DCM.ActMatters_id
						left join v_DrugNormativeListSpecTorgLink dnlstl with(nolock) on dnlstl.DrugNormativeListSpec_id = t.DrugNormativeListSpec_id
						left join v_DrugNormativeListSpecFormsLink dnlsfl with(nolock) on dnlsfl.DrugNormativeListSpec_id = t.DrugNormativeListSpec_id
						left join rls.v_CLSDRUGFORMS CDF with(nolock) on CDF.CLSDRUGFORMS_ID = DCM.CLSDRUGFORMS_ID
						left join rls.v_Prep P with(nolock) on P.Prep_id = D.DrugPrep_id
						left join rls.v_TRADENAMES TN with(nolock) on TN.TRADENAMES_ID = P.TRADENAMEID
					where
						D.Drug_id = :Drug_id
						and t.DrugNormativeListSpecMNN_id = AM.ACTMATTERS_ID
						and (dnlsfl.DrugNormativeListSpecForms_id is null or dnlsfl.DrugNormativeListSpecForms_id = CDF.CLSDRUGFORMS_ID)
						and (dnlstl.DrugNormativeListSpecTorg_id is null or dnlstl.DrugNormativeListSpecTorg_id = TN.TRADENAMES_ID)
						and (t.DrugNormativeListSpec_EndDT is null or t.DrugNormativeListSpec_EndDT > dbo.tzGetDate())
				) as DNLS
			where
				DNLS.DrugNormativeList_id is not null
				and (DNL.DrugNormativeList_EndDT is null or DNL.DrugNormativeList_EndDT > dbo.tzGetDate())
		";

		$result = $this->db->query($query,$params);
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Получение Типа регистра по Статье расходов
	 */
	function getPersonRegisterTypeByWhsDocumentCostItemType($data) {
		$params = array('WhsDocumentCostItemType_id' => $data['WhsDocumentCostItemType_id']);

		$query = "select PersonRegisterType_id from dbo.WhsDocumentCostItemType	where WhsDocumentCostItemType_id = :WhsDocumentCostItemType_id";

		$result = $this->db->query($query,$params);
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}
}