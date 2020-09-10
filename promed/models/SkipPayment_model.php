<?php defined('BASEPATH') or die ('No direct script access allowed');

class SkipPayment_model extends swModel {

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
			declare
				@SkipPayment_id bigint,
				@Error_Code int = null,
				@Error_Message varchar(4000) = null;

			set nocount on;

			begin try

				insert into persis.SkipPayment with (rowlock) (
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
				)

				set @SkipPayment_id = (select scope_identity())

			end try

			begin catch
				set @Error_Code = error_number()
				set @Error_Message = error_message()
			end catch

			set nocount off;

			select @SkipPayment_id as SkipPayment_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
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
			declare
				@Error_Code int = null,
				@Error_Message varchar(4000) = null;

			set nocount on;

			begin try
				update 
					persis.SkipPayment with (rowlock)
				set
					updDT = dbo.tzGetDate()
					,pmUser_updID = :pmUser_id
					,EndDate = :EndDate
					,StartDate = :StartDate
					,SkipPaymentReason_id = :SkipPaymentReason_id
					,WorkPlace_id = :MedStaffFact_id
				where
					id = :SkipPayment_id
			end try

			begin catch
				set @Error_Code = error_number();
				set @Error_Message = error_message();
			end catch

			set nocount off;

			select @Error_Code as Error_Code, @Error_Message as Error_Msg;
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
			declare
				@Error_Code int = null,
				@Error_Message varchar(4000) = null;

			set nocount on;

			begin try
				select
					SP.WorkPlace_id as MedStaffFact_id
					,SP.SkipPaymentReason_id
					,convert(varchar(10), SP.StartDate, 120) as StartDate
					,convert(varchar(10), SP.EndDate, 120) as EndDate
				from 
					persis.SkipPayment SP with (nolock)
					left join v_MedStaffFact MS with(nolock) on MS.MedStaffFact_id = SP.WorkPlace_id
				where
					SP.id = :SkipPayment_id
					{$filter}
			end try

			begin catch
				set @Error_Code = error_number();
				set @Error_Message = error_message();
				select @Error_Code as Error_Code, @Error_Message as Error_Msg;
			end catch

			set nocount off;
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
			declare
				@Error_Code int = null,
				@Error_Message varchar(4000) = null;

			set nocount on;

			begin try
				select
					SP.id as SkipPayment_id
				from 
					persis.SkipPayment SP with (nolock)
					left join v_MedStaffFact MS with(nolock) on MS.MedStaffFact_id = SP.WorkPlace_id
				where
					SP.WorkPlace_id = :MedStaffFact_id
					{$filter}
			end try

			begin catch
				set @Error_Code = error_number();
				set @Error_Message = error_message();
				select @Error_Code as Error_Code, @Error_Message as Error_Msg;
			end catch

			set nocount off;
		";
		
		$resp = $this->queryResult($query, $data);

		return $resp;
	}
}