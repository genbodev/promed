<?php defined('BASEPATH') or die ('No direct script access allowed');

class SkipPayment_model extends SwPgModel {

	public $inputRules = array(
		'createSkipPayment' => array(
			array('field' => 'MedStaffFact_id', 'label' => 'Идентификатор места работы', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'SkipPaymentReason_id', 'label' => 'Причина невыплаты', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'StartDate', 'label' => 'Дата начала', 'rules' => 'required', 'type' => 'date'),
			array('field' => 'EndDate', 'label' => 'Дата окончания', 'rules' => 'required', 'type' => 'date')
		),
		'updateSkipPayment' => array(
			array('field' => 'SkipPayment_id', 'label' => 'Идентификатор невыплаты', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'MedStaffFact_id', 'label' => 'Идентификатор места работы', 'rules' => '', 'type' => 'id'),
			array('field' => 'SkipPaymentReason_id', 'label' => 'Причина невыплаты', 'rules' => '', 'type' => 'id'),
			array('field' => 'StartDate', 'label' => 'Дата начала', 'rules' => '', 'type' => 'date'),
			array('field' => 'EndDate', 'label' => 'Дата окончания', 'rules' => '', 'type' => 'date')
		),
		'loadSkipPaymentById' => array(
			array(
				'field' => 'SkipPayment_id',
				'label' => 'Идентификатор невыплаты',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'loadSkipPaymentByMedStaffFact' => array(
			array(
				'field' => 'MedStaffFact_id',
				'label' => 'Идентификатор места работы',
				'rules' => 'required',
				'type' => 'id'
			)
		)
	);
	
	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
	}

    /**
	 * Создание невыплаты
	 */
	function createSkipPayment($data) {

		$query = "
		    CREATE OR REPLACE FUNCTION pg_temp.SkipPaymentInsert
                (out SkipPayment_id bigint,
                 out Error_Code int,
                 out Error_Message text)
                LANGUAGE 'plpgsql'
                
                AS $$
                DECLARE
                    SkipPayment_id bigint;
				    Error_Code int = null;
				    Error_Message varchar(4000) = null;
                BEGIN
                
                    insert into persis.SkipPayment (
                    insDT
                    ,pmUser_insID
                    ,updDT
                    ,pmUser_updID
                    ,version
                    ,EndDate
                    ,StartDate
                    ,SkipPaymentReason_id
                    ,WorkPlace_id
                    ) values (
                    dbo.tzGetDate()
                    ,:pmUser_id
                    ,dbo.tzGetDate()
                    ,:pmUser_id
                    ,1
                    ,:EndDate
                    ,:StartDate
                    ,:SkipPaymentReason_id
                    ,:MedStaffFact_id
                    )returning SkipPayment.SkipPayment_id into SkipPayment_id;
                
                exception
                    when division_by_zero THEN NULL;
            	    when others then Error_Code:=SQLSTATE; Error_Message:=SQLERRM;
                
                END;
                $$;
                
                select 
                    SkipPayment_id as \"SkipPayment_id\",
                    Error_Code as \"Error_Code\",
                    Error_Message as \"Error_Msg\"
                from pg_temp.SkipPaymentInsert();
		";
		//echo getDebugSQL($query, $data);exit;
		$resp = $this->queryResult($query, $data);

		return $resp;
	}

	/**
	 * Обновление невыплаты
	 */
	function updateSkipPayment($data) {

		$query = "
		    CREATE OR REPLACE FUNCTION pg_temp.SkipPaymentUpdate
                (out Error_Code int,
                 out Error_Message text)
                LANGUAGE 'plpgsql'
                
            AS $$
            DECLARE
                Error_Code int = null;
				Error_Message varchar(4000) = null;
				
            BEGIN
                update
					persis.SkipPayment
				set
					updDT = dbo.tzGetDate()
					,pmUser_updID = :pmUser_id
					,EndDate = :EndDate
					,StartDate = :StartDate
					,SkipPaymentReason_id = :SkipPaymentReason_id
					,WorkPlace_id = :MedStaffFact_id
				where
					id = :SkipPayment_id;
		
		        exception
                    when division_by_zero THEN NULL;
            	    when others then Error_Code:=SQLSTATE; Error_Message:=SQLERRM;
                
            END;
            $$;
                
                select 
                    Error_Code as \"Error_Code\",
                    Error_Message as \"Error_Msg\"
                from pg_temp.SkipPaymentUpdate();
		";
		//echo getDebugSQL($query, $data);exit;
		$resp = $this->queryResult($query, $data);

		return $resp;
	}

	/**
	 * Получение невыплаты по идентификатору
	 */
	function loadSkipPaymentById($data) {
		$filter = '';
		if (!empty($data['Lpu_id'])) {
			$filter .= " and MS.Lpu_id = :Lpu_id ";
		}
		$query = "      
        select
            SP.WorkPlace_id as \"MedStaffFact_id\"
            ,SP.SkipPaymentReason_id as \"SkipPaymentReason_id\"
            ,to_char (SP.StartDate, 'yyyy-mm-dd hh24:mm:ss') as \"StartDate\"
            ,to_char (SP.EndDate, 'yyyy-mm-dd hh24:mm:ss') as \"EndDate\"
        from 
            persis.SkipPayment SP
            left join v_MedStaffFact MS on MS.MedStaffFact_id = SP.WorkPlace_id
        where
            SP.id = :SkipPayment_id
            {$filter}
		";
		
		$resp = $this->queryResult($query, $data);

		return $resp;
	}

	/**
	 * Получение невыплаты по месту работы
	 */
	function loadSkipPaymentByMedStaffFact($data) {
		$filter = '';
		if(isset($data['Lpu_id'])){
			$filter .= ' and MS.Lpu_id = :Lpu_id';
		};
		$query = "
        select
            SP.id as \"SkipPayment_id\"
        from
            persis.SkipPayment SP
            left join v_MedStaffFact MS on MS.MedStaffFact_id = SP.WorkPlace_id
        where
            SP.WorkPlace_id = :MedStaffFact_id
            {$filter}
		";
		
		$resp = $this->queryResult($query, $data);

		return $resp;
	}
}