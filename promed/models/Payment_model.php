<?php defined('BASEPATH') or die ('No direct script access allowed');

class Payment_model extends swModel {

	public $inputRules = array(
		'createPayment' => array(
			array('field' => 'Staff_id', 'label' => 'Идентификатор строки штатного расписания', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'PaymentKind_id', 'label' => 'Вид выплаты', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'PaymentPercent', 'label' => 'Процент', 'rules' => 'required', 'type' => 'float')
		),
		'updatePayment' => array(
			array('field' => 'Payment_id', 'label' => 'Идентификатор выплаты', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'PaymentKind_id', 'label' => 'Вид выплаты', 'rules' => '', 'type' => 'id'),
			array('field' => 'PaymentPercent', 'label' => 'Процент', 'rules' => '', 'type' => 'float')
		),
		'loadPaymentByStaff' => array(
			array(
				'field' => 'Staff_id',
				'label' => 'Идентификатор строки',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'createPaymentMedStaffFact' => array(
			array(
				'field' => 'MedStaffFact_id',
				'label' => 'Идентификатор места работы',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'PaymentKind_id',
				'label' => 'Вид выплаты',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'PaymentPercent',
				'label' => 'Процент',
				'rules' => 'required',
				'type' => 'float'
			)
		),
		'loadPaymentByMedStaffFact' => array(
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
	 * Создание выплаты для строки штатного расписания
	 */
	function createPayment($data) {

		$query = "
			declare
				@Payment_id bigint,
				@Error_Code int = null,
				@Error_Message varchar(4000) = null;

			set nocount on;

			begin try
				begin tran

				insert into persis.Payment with (rowlock) (
					insDT
					,pmUser_insID
					,updDT
					,pmUser_updID
					,version
					,PaymentPercent
					,PaymentKind_id
				) values (
					dbo.tzGetDate()
					,:pmUser_id
					,dbo.tzGetDate()
					,:pmUser_id
					,1
					,:PaymentPercent
					,:PaymentKind_id
				)

				set @Payment_id = (select scope_identity())

				insert into persis.StaffPayment with (rowlock) (
					StaffId
					,PaymentId
				) values (
					:Staff_id
					,@Payment_id
				)

				commit tran
			end try

			begin catch
				set @Error_Code = error_number()
				set @Error_Message = error_message()
				if @@trancount>0
					rollback tran
			end catch

			set nocount off;

			select @Payment_id as Payment_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
		";
		//echo getDebugSQL($query, $data);exit;
		$resp = $this->queryResult($query, $data);

		return $resp;
	}

	/**
	 * Обновление выплаты
	 */
	function updatePayment($data) {

		$query = "
			declare
				@Error_Code int = null,
				@Error_Message varchar(4000) = null;

			set nocount on;

			begin try
				update 
					persis.Payment with (rowlock)
				set
					updDT = dbo.tzGetDate()
					,pmUser_updID = :pmUser_id
					,PaymentPercent = :PaymentPercent
					,PaymentKind_id = :PaymentKind_id
				where
					id = :Payment_id
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
	 * Получение выплат по строке штатного расписания
	 */
	function loadPaymentByStaff($data) {
		$where = ''; $from = '';
		if(!empty($data['Lpu_id'])){
			$where .= ' AND s.Lpu_id = :Lpu_id';
			$from .= ' left join persis.v_Staff s with(nolock) on s.id = sp.StaffId';
		}
		$query = "
			declare
				@Error_Code int = null,
				@Error_Message varchar(4000) = null;

			set nocount on;

			begin try
				select
					p.id as Payment_id
				from persis.Payment p with (nolock)
				inner join persis.StaffPayment sp with (nolock) on sp.PaymentId = p.id
				{$from}
				where
					sp.StaffId = :Staff_id
					{$where}
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
	 * Создание выплаты для местам работы
	 */
	function createPaymentMedStaffFact($data) {

		$query = "
			declare
				@Payment_id bigint,
				@Error_Code int = null,
				@Error_Message varchar(4000) = null;

			set nocount on;

			begin try
				begin tran

				insert into persis.Payment with (rowlock) (
					insDT
					,pmUser_insID
					,updDT
					,pmUser_updID
					,version
					,PaymentPercent
					,PaymentKind_id
				) values (
					dbo.tzGetDate()
					,:pmUser_id
					,dbo.tzGetDate()
					,:pmUser_id
					,1
					,:PaymentPercent
					,:PaymentKind_id
				)

				set @Payment_id = (select scope_identity())

				insert into persis.WorkPlacePayment with (rowlock) (
					WorkPlaceId
					,PaymentId
				) values (
					:MedStaffFact_id
					,@Payment_id
				)

				commit tran
			end try

			begin catch
				set @Error_Code = error_number()
				set @Error_Message = error_message()
				if @@trancount>0
					rollback tran
			end catch

			set nocount off;

			select @Payment_id as Payment_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
		";
		//echo getDebugSQL($query, $data);exit;
		$resp = $this->queryResult($query, $data);

		return $resp;
	}

	/**
	 * Получение выплат по месту работы
	 */
	function loadPaymentByMedStaffFact($data) {
		$filter = '';
		if (!empty($data['Lpu_id'])) {
			$filter .= " and MS.Lpu_id = :Lpu_id";
		}
		$query = "
			declare
				@Error_Code int = null,
				@Error_Message varchar(4000) = null;

			set nocount on;

			begin try
				select
					p.id as Payment_id
				from persis.Payment p with (nolock)
				inner join persis.WorkPlacePayment wp with (nolock) on wp.PaymentId = p.id
				left join v_MedStaffFact MS with(nolock) on MS.MedStaffFact_id = wp.WorkPlaceId
				where
					wp.WorkPlaceId = :MedStaffFact_id
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
	 * Получение выплат по ид
	 */
	function loadPaymentById($data) {

		$query = "
			declare
				@Error_Code int = null,
				@Error_Message varchar(4000) = null;

			set nocount on;

			begin try
				select
					p.id as Payment_id,
					PaymentKind_id,
					PaymentPercent
				from persis.Payment p with (nolock)
				where
					p.id = :Payment_id
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