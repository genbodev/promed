<?php defined('BASEPATH') or die ('No direct script access allowed');

class WhsDocumentRightRecipient_model extends swModel {
	private $WhsDocumentRightRecipient_id;//WhsDocumentRightRecipient_id
	private $WhsDocumentTitle_id;//Правоустанавливающий документ
	private $Org_id;//Организация
	private $Contragent_id;//Контрагент
	private $WhsDocumentRightRecipient_begDate;//Дата начала действия
	private $WhsDocumentRightRecipient_endDate;//Дата окончания дийствия
	private $pmUser_id;//Идентификатор пользователя системы Промед

	/**
	 * Получение параметра
	 */
	public function getWhsDocumentRightRecipient_id() { return $this->WhsDocumentRightRecipient_id;}

	/**
	 * Установка параметра
	 */
	public function setWhsDocumentRightRecipient_id($value) { $this->WhsDocumentRightRecipient_id = $value; }

	/**
	 * Получение параметра
	 */
	public function getWhsDocumentTitle_id() { return $this->WhsDocumentTitle_id;}

	/**
	 * Установка параметра
	 */
	public function setWhsDocumentTitle_id($value) { $this->WhsDocumentTitle_id = $value; }

	/**
	 * Получение параметра
	 */
	public function getOrg_id() { return $this->Org_id;}

	/**
	 * Установка параметра
	 */
	public function setOrg_id($value) { $this->Org_id = $value; }

	/**
	 * Получение параметра
	 */
	public function getContragent_id() { return $this->Contragent_id;}

	/**
	 * Установка параметра
	 */
	public function setContragent_id($value) { $this->Contragent_id = $value; }

	/**
	 * Получение параметра
	 */
	public function getWhsDocumentRightRecipient_begDate() { return $this->WhsDocumentRightRecipient_begDate;}

	/**
	 * Установка параметра
	 */
	public function setWhsDocumentRightRecipient_begDate($value) { $this->WhsDocumentRightRecipient_begDate = $value; }

	/**
	 * Получение параметра
	 */
	public function getWhsDocumentRightRecipient_endDate() { return $this->WhsDocumentRightRecipient_endDate;}

	/**
	 * Установка параметра
	 */
	public function setWhsDocumentRightRecipient_endDate($value) { $this->WhsDocumentRightRecipient_endDate = $value; }

	/**
	 * Получение параметра
	 */
	public function getpmUser_id() { return $this->pmUser_id;}

	/**
	 * Установка параметра
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
	 * Загрузка
	 */
	function load() {
		$q = "
			select
				WhsDocumentRightRecipient_id, WhsDocumentTitle_id, Org_id, Contragent_id, WhsDocumentRightRecipient_begDate, WhsDocumentRightRecipient_endDate
			from
				dbo.v_WhsDocumentRightRecipient with(nolock)
			where
				WhsDocumentRightRecipient_id = :WhsDocumentRightRecipient_id
		";
		$r = $this->db->query($q, array('WhsDocumentRightRecipient_id' => $this->WhsDocumentRightRecipient_id));
		if ( is_object($r) ) {
			$r = $r->result('array');
			if (isset($r[0])) {
				$this->WhsDocumentRightRecipient_id = $r[0]['WhsDocumentRightRecipient_id'];
				$this->WhsDocumentTitle_id = $r[0]['WhsDocumentTitle_id'];
				$this->Org_id = $r[0]['Org_id'];
				$this->Contragent_id = $r[0]['Contragent_id'];
				$this->WhsDocumentRightRecipient_begDate = $r[0]['WhsDocumentRightRecipient_begDate'];
				$this->WhsDocumentRightRecipient_endDate = $r[0]['WhsDocumentRightRecipient_endDate'];
				return $r;
			} else {
				return false;
			}
		}
		else {
			return false;
		}
	}

	/**
	 * Загрузка списка
	 */
	function loadList($filter) {
		$where = array();
		$p = array();

		if (!isset($filter['Lpu_id'])) {
			$filter['Lpu_id'] = null;
		}

		if (isset($filter['WhsDocumentRightRecipient_id']) && $filter['WhsDocumentRightRecipient_id']) {
			$where[] = 'v_WhsDocumentRightRecipient.WhsDocumentRightRecipient_id = :WhsDocumentRightRecipient_id';
			$p['WhsDocumentRightRecipient_id'] = $filter['WhsDocumentRightRecipient_id'];
		}
		if (isset($filter['WhsDocumentTitle_id']) && $filter['WhsDocumentTitle_id']) {
			$where[] = 'v_WhsDocumentRightRecipient.WhsDocumentTitle_id = :WhsDocumentTitle_id';
			$p['WhsDocumentTitle_id'] = $filter['WhsDocumentTitle_id'];
		}
		if (isset($filter['Org_id']) && $filter['Org_id']) {
			$where[] = 'v_WhsDocumentRightRecipient.Org_id = :Org_id';
			$p['Org_id'] = $filter['Org_id'];
		}
		if (isset($filter['Contragent_id']) && $filter['Contragent_id']) {
			$where[] = 'v_WhsDocumentRightRecipient.Contragent_id = :Contragent_id';
			$p['Contragent_id'] = $filter['Contragent_id'];
		}
		if (isset($filter['WhsDocumentRightRecipient_begDate']) && $filter['WhsDocumentRightRecipient_begDate']) {
			$where[] = 'v_WhsDocumentRightRecipient.WhsDocumentRightRecipient_begDate = :WhsDocumentRightRecipient_begDate';
			$p['WhsDocumentRightRecipient_begDate'] = $filter['WhsDocumentRightRecipient_begDate'];
		}
		if (isset($filter['WhsDocumentRightRecipient_endDate']) && $filter['WhsDocumentRightRecipient_endDate']) {
			$where[] = 'v_WhsDocumentRightRecipient.WhsDocumentRightRecipient_endDate = :WhsDocumentRightRecipient_endDate';
			$p['WhsDocumentRightRecipient_endDate'] = $filter['WhsDocumentRightRecipient_endDate'];
		}
		$where_clause = implode(' AND ', $where);
		if (strlen($where_clause)) {
			$where_clause = 'WHERE '.$where_clause;
		}
		$q = "
			SELECT
				v_WhsDocumentRightRecipient.WhsDocumentRightRecipient_id,
				v_WhsDocumentRightRecipient.WhsDocumentTitle_id,
				v_WhsDocumentRightRecipient.Org_id,
				v_WhsDocumentRightRecipient.Contragent_id,
				CONVERT(varchar(10), v_WhsDocumentRightRecipient.WhsDocumentRightRecipient_begDate, 104) WhsDocumentRightRecipient_begDate,
				CONVERT(varchar(10), v_WhsDocumentRightRecipient.WhsDocumentRightRecipient_endDate, 104) WhsDocumentRightRecipient_endDate,
				WhsDocumentTitle_id_ref.WhsDocumentTitle_Name,
				Org_id_ref.Org_Name,
				PAddress_id_ref.Address_Address PAddress_Address,
				isnull(Contragent_id_ref.Contragent_Code, Contragent.Contragent_Code) as Contragent_Code,
				Contragent_id_ref.Contragent_Name
			FROM
				dbo.v_WhsDocumentRightRecipient WITH (NOLOCK)
				LEFT JOIN dbo.v_WhsDocumentTitle WhsDocumentTitle_id_ref WITH (NOLOCK) ON WhsDocumentTitle_id_ref.WhsDocumentTitle_id = v_WhsDocumentRightRecipient.WhsDocumentTitle_id
				LEFT JOIN dbo.v_Org Org_id_ref WITH (NOLOCK) ON Org_id_ref.Org_id = v_WhsDocumentRightRecipient.Org_id
				LEFT JOIN dbo.v_Address PAddress_id_ref WITH (NOLOCK) ON PAddress_id_ref.Address_id = Org_id_ref.PAddress_id
				LEFT JOIN dbo.v_Contragent Contragent_id_ref WITH (NOLOCK) ON Contragent_id_ref.Contragent_id = v_WhsDocumentRightRecipient.Contragent_id
				outer apply (
					select top 1
						c.Contragent_Code
					from
						Contragent c with(nolock)
					where
						c.Org_id = Org_id_ref.Org_id and
						(
							(WhsDocumentTitle_id_ref.WhsDocumentTitleType_id = 2 and c.ContragentType_id = 6) or
							(WhsDocumentTitle_id_ref.WhsDocumentTitleType_id <> 2 and c.ContragentType_id = 3)
						) and
						c.Lpu_id is null
					order by
						c.Contragent_id
				) as Contragent
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
	 * Сохранение из JSON
	 */
	function saveFromJSON($data) {
		if (!empty($data['json_str']) && $data['WhsDocumentTitle_id'] > 0) {
			ConvertFromWin1251ToUTF8($data['json_str']);
			$dt = (array) json_decode($data['json_str']);
			foreach($dt as $record) {
				$this->WhsDocumentRightRecipient_id = $record->state == 'add' ? 0 :  $record->WhsDocumentRightRecipient_id;
				$this->pmUser_id = $data['pmUser_id'];
				switch($record->state) {
					case 'add':
					case 'edit':						
						$this->WhsDocumentTitle_id = $data['WhsDocumentTitle_id'];
						$this->Org_id = $record->Org_id;
						$this->WhsDocumentRightRecipient_begDate = $record->WhsDocumentRightRecipient_begDate;
						$this->WhsDocumentRightRecipient_endDate = $record->WhsDocumentRightRecipient_endDate;
						$this->save();
					break;
					case 'delete':
						$this->delete();
					break;						
				}
			}
		}
	}

	/**
	 * Сохранение
	 */
	function save() {
		$procedure = 'p_WhsDocumentRightRecipient_ins';
		if ( $this->WhsDocumentRightRecipient_id > 0 ) {
			$procedure = 'p_WhsDocumentRightRecipient_upd';
		}
		$q = "
			declare
				@WhsDocumentRightRecipient_id bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @WhsDocumentRightRecipient_id = :WhsDocumentRightRecipient_id;
			exec dbo." . $procedure . "
				@WhsDocumentRightRecipient_id = @WhsDocumentRightRecipient_id output,
				@WhsDocumentTitle_id = :WhsDocumentTitle_id,
				@Org_id = :Org_id,
				@Contragent_id = :Contragent_id,
				@WhsDocumentRightRecipient_begDate = :WhsDocumentRightRecipient_begDate,
				@WhsDocumentRightRecipient_endDate = :WhsDocumentRightRecipient_endDate,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @WhsDocumentRightRecipient_id as WhsDocumentRightRecipient_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$p = array(
			'WhsDocumentRightRecipient_id' => $this->WhsDocumentRightRecipient_id,
			'WhsDocumentTitle_id' => $this->WhsDocumentTitle_id,
			'Org_id' => $this->Org_id,
			'Contragent_id' => $this->Contragent_id,
			'WhsDocumentRightRecipient_begDate' => $this->WhsDocumentRightRecipient_begDate,
			'WhsDocumentRightRecipient_endDate' => $this->WhsDocumentRightRecipient_endDate,
			'pmUser_id' => $this->pmUser_id,
		);
		$r = $this->db->query($q, $p);
		if ( is_object($r) ) {
		    $result = $r->result('array');
		    $this->WhsDocumentRightRecipient_id = $result[0]['WhsDocumentRightRecipient_id'];
		}
		else {
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
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec dbo.p_WhsDocumentRightRecipient_del
				@WhsDocumentRightRecipient_id = :WhsDocumentRightRecipient_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$r = $this->db->query($q, array(
			'WhsDocumentRightRecipient_id' => $this->WhsDocumentRightRecipient_id
		));
		if ( is_object($r) ) {
			return $r->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Правополучателю присваивается идентификатор конкретного контрагента. функция используется для исполнения правоустанавливающего документа.
	 */
	function setContragent($data) {
		if (!isset($data['WhsDocumentRightRecipient_id']) || !isset($data['ContragentType_id'])) {
			return false;
		}

		$contragent_id = 0;
		
		//загружаем данные по правополучателю
		$this->setWhsDocumentRightRecipient_id($data['WhsDocumentRightRecipient_id']);
		$this->load();
	
		//ищем контрагент
		$q = "
			select top 1
				Contragent_id	
			from
				Contragent with(nolock)
			where ContragentType_id = :ContragentType_id
				and Org_id = :Org_id
				and Lpu_id is null
			order by
				Contragent_insDT desc;
		";
		$r = $this->db->query($q, array(
			'ContragentType_id' => $data['ContragentType_id'],
			'Lpu_id' => $data['Lpu_id'],
			'Org_id' => $this->getOrg_id()
		));
		if (is_object($r)) {
			$res = $r->result('array');
			if (isset($res[0]) && isset($res[0]['Contragent_id']) && $res[0]['Contragent_id'] > 0)
				$contragent_id = $res[0]['Contragent_id'];
		}
		
		//если не находим контрагента, то добавляем его в бд
		if ($contragent_id <= 0) {
			$q = "
				Declare @Contragent_id bigint = null;
				Declare @Error_Code bigint = 0;
				Declare @Error_Message varchar(4000) = '';
				Declare	@C_Code int = (Select IsNull(Max(Contragent_Code),10)+1 from v_Contragent with(nolock));
				Declare	@C_Name varchar(100) = (Select IsNull(Org_Name,'Контрагент') from v_Org with(nolock) where Org_id = :Org_id);
				
				exec p_Contragent_ins
				@Server_id = :Server_id,
				@Contragent_id = @Contragent_id output,
				@Lpu_id = null,
				@ContragentType_id = :ContragentType_id,
				@Contragent_Code = @C_Code,
				@Contragent_Name = @C_Name,
				@Org_id = :Org_id,
				@OrgFarmacy_id = null,
				@LpuSection_id = null,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
				select @Contragent_id as Contragent_id, @Error_Code as Error_Code, @Error_Message as Error_Message;";

			$r = $this->db->query($q, array(
				'Server_id' => $data['Server_id'],
				'ContragentType_id' => $data['ContragentType_id'],
				'Org_id' => $this->getOrg_id(),
				'pmUser_id' => $this->getpmUser_id()
			));
			if (is_object($r)) {
				$res = $r->result('array');
				if (isset($res[0]) && isset($res[0]['Contragent_id']) && $res[0]['Contragent_id'] > 0)
					$contragent_id = $res[0]['Contragent_id'];
			}
		}

		//проверяем связь контрагента с минздравом
		$mzorg_id = $this->getFirstResultFromQuery("select dbo.GetMinzdravDloOrgId() as Org_id");
		if (!empty($mzorg_id) && $contragent_id > 0) {
			//ищем существующую связь
			$query = "
				select
				 	count(ContragentOrg_id) as cnt
				from
					v_ContragentOrg with (nolock)
				where
					Contragent_id = :Contragent_id and
					Org_id = :Org_id
			";
			$mz_link = $this->getFirstResultFromQuery($query, array(
				'Contragent_id' => $contragent_id,
				'Org_id' => $mzorg_id
			));

			if (empty($mz_link) || $mz_link < 1) {
				//добавляем связь
				$query = "
					declare
						@ContragentOrg_id bigint,
						@Error_Code bigint,
						@Error_Message varchar(4000);
					exec p_ContragentOrg_ins
						@ContragentOrg_id = @ContragentOrg_id output,
						@Contragent_id = :Contragent_id,
						@Org_id = :Org_id,
						@pmUser_id = :pmUser_id,
						@Error_Code = @Error_Code output,
						@Error_Message = @Error_Message output;
					select @ContragentOrg_id as ContragentOrg_id, @Error_Code as Error_Code, @Error_Message as Error_Message
				";
				$result = $this->getFirstRowFromQuery($query, array(
					'Contragent_id' => $contragent_id,
					'Org_id' => $mzorg_id,
					'pmUser_id' => $this->getpmUser_id()
				));
			}
		}
		
		//дописываем контрагент в текущего правополучателя
		if ($contragent_id > 0) {
			$this->setContragent_id($contragent_id);
			$this->save();
		}
	}
}