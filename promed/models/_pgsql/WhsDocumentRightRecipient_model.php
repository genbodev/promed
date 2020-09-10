<?php defined('BASEPATH') or die ('No direct script access allowed');

class WhsDocumentRightRecipient_model extends SwPgModel {
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
				WhsDocumentRightRecipient_id as \"WhsDocumentRightRecipient_id\",
				WhsDocumentTitle_id as \"WhsDocumentTitle_id\",
				Org_id as \"Org_id\",
				Contragent_id as \"Contragent_id\",
				WhsDocumentRightRecipient_begDate as \"WhsDocumentRightRecipient_begDate\",
				WhsDocumentRightRecipient_endDate as \"WhsDocumentRightRecipient_endDate\"
			from
				dbo.v_WhsDocumentRightRecipient
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
				v_WhsDocumentRightRecipient.WhsDocumentRightRecipient_id as \"WhsDocumentRightRecipient_id\",
				v_WhsDocumentRightRecipient.WhsDocumentTitle_id as \"WhsDocumentTitle_id\",
				v_WhsDocumentRightRecipient.Org_id as \"Org_id\",
				v_WhsDocumentRightRecipient.Contragent_id as \"Contragent_id\",
				TO_CHAR (v_WhsDocumentRightRecipient.WhsDocumentRightRecipient_begDate, 'dd.mm.yyyy') as \"WhsDocumentRightRecipient_begDate\",
				TO_CHAR (v_WhsDocumentRightRecipient.WhsDocumentRightRecipient_endDate, 'dd.mm.yyyy') as \"WhsDocumentRightRecipient_endDate\",
				WhsDocumentTitle_id_ref.WhsDocumentTitle_Name as \"WhsDocumentTitle_Name\",
				Org_id_ref.Org_Name as \"Org_Name\",
				PAddress_id_ref.Address_Address as \"PAddress_Address\",
				coalesce(Contragent_id_ref.Contragent_Code, Contragent.Contragent_Code) as \"Contragent_Code\",
				Contragent_id_ref.Contragent_Name as \"Contragent_Name\"
			FROM
				dbo.v_WhsDocumentRightRecipient
				LEFT JOIN dbo.v_WhsDocumentTitle WhsDocumentTitle_id_ref ON WhsDocumentTitle_id_ref.WhsDocumentTitle_id = v_WhsDocumentRightRecipient.WhsDocumentTitle_id
				LEFT JOIN dbo.v_Org Org_id_ref ON Org_id_ref.Org_id = v_WhsDocumentRightRecipient.Org_id
				LEFT JOIN dbo.v_Address PAddress_id_ref ON PAddress_id_ref.Address_id = Org_id_ref.PAddress_id
				LEFT JOIN dbo.v_Contragent Contragent_id_ref ON Contragent_id_ref.Contragent_id = v_WhsDocumentRightRecipient.Contragent_id
				LEFT JOIN LATERAL (
					select
						c.Contragent_Code as Contragent_Code
					from
						Contragent c
					where
						c.Org_id = Org_id_ref.Org_id and
						(
							(WhsDocumentTitle_id_ref.WhsDocumentTitleType_id = 2 and c.ContragentType_id = 6) or
							(WhsDocumentTitle_id_ref.WhsDocumentTitleType_id <> 2 and c.ContragentType_id = 3)
						) and
						c.Lpu_id is null
					order by
						c.Contragent_id
                    limit 1
				) as Contragent ON TRUE
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
			select 
			    WhsDocumentRightRecipient_id as \"WhsDocumentRightRecipient_id\",
			    Error_Code as \"Error_Code\",
			    Error_Message as \"Error_Msg\"
			from dbo." . $procedure . " (
				WhsDocumentRightRecipient_id := :WhsDocumentRightRecipient_id,
				WhsDocumentTitle_id := :WhsDocumentTitle_id,
				Org_id := :Org_id,
				Contragent_id := :Contragent_id,
				WhsDocumentRightRecipient_begDate := :WhsDocumentRightRecipient_begDate,
				WhsDocumentRightRecipient_endDate := :WhsDocumentRightRecipient_endDate,
				pmUser_id := :pmUser_id
				)
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
            select 
                Error_Code as \"Error_Code\",
                Error_Message as \"Error_Msg\"
			from dbo.p_WhsDocumentRightRecipient_del (
				WhsDocumentRightRecipient_id := :WhsDocumentRightRecipient_id
				)
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
			select
				Contragent_id as \"Contragent_id\"	
			from
				Contragent
			where ContragentType_id = :ContragentType_id
				and Org_id = :Org_id
				and Lpu_id is null
			order by
				Contragent_insDT desc
            limit 1
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
			    with C_Code as (Select coalesce(Max(Contragent_Code),10)+1 as C_Code from v_Contragent),
				C_Name as (Select coalesce(Org_Name,'Контрагент') as C_Name from v_Org where Org_id = :Org_id)
				
				select 
                    Contragent_id as \"Contragent_id\",
                    Error_Code as \"Error_Code\",
                    Error_Message as \"Error_Message\"
				from p_Contragent_ins (
				    Server_id := :Server_id,
				    Contragent_id := null,
				    Lpu_id := null,
				    ContragentType_id := :ContragentType_id,
				    Contragent_Code := (select C_Code from C_Code),
				    Contragent_Name := (select C_Name from C_Name),
				    Org_id := :Org_id,
				    OrgFarmacy_id := null,
				    LpuSection_id := null,
				    pmUser_id := :pmUser_id
				)
            ";

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
				 	count(ContragentOrg_id) as \"cnt\"
				from
					v_ContragentOrg
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
                    select
                        ContragentOrg_id as \"ContragentOrg_id\",
                        Error_Code as \"Error_Code\",
                        Error_Message as \"Error_Message\"
					from p_ContragentOrg_ins (
						Contragent_id := :Contragent_id,
						Org_id := :Org_id,
						pmUser_id := :pmUser_id
						)
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