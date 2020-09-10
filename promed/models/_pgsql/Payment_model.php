<?php
defined('BASEPATH') or die ('No direct script access allowed');

/**
 * Class Payment_model
 *
 * @property-read array $inputRules
 */
class Payment_model extends SwPgModel
{

	public $inputRules = [
		'createPayment' => [
			['field' => 'Staff_id', 'label' => 'Идентификатор строки штатного расписания', 'rules' => 'required', 'type' => 'id'],
			['field' => 'PaymentKind_id', 'label' => 'Вид выплаты', 'rules' => 'required', 'type' => 'id'],
			['field' => 'PaymentPercent', 'label' => 'Процент', 'rules' => 'required', 'type' => 'float']
		],
		'updatePayment' => [
			['field' => 'Payment_id', 'label' => 'Идентификатор выплаты', 'rules' => 'required', 'type' => 'id'],
			['field' => 'PaymentKind_id', 'label' => 'Вид выплаты', 'rules' => '', 'type' => 'id'],
			['field' => 'PaymentPercent', 'label' => 'Процент', 'rules' => '', 'type' => 'float']
		],
		'loadPaymentByStaff' => array(
			[
				'field' => 'Staff_id',
				'label' => 'Идентификатор строки',
				'rules' => 'required',
				'type' => 'id'
			]
		),
		'createPaymentMedStaffFact' => array(
			[
				'field' => 'MedStaffFact_id',
				'label' => 'Идентификатор места работы',
				'rules' => 'required',
				'type' => 'id'
			],
			[
				'field' => 'PaymentKind_id',
				'label' => 'Вид выплаты',
				'rules' => 'required',
				'type' => 'id'
			],
			[
				'field' => 'PaymentPercent',
				'label' => 'Процент',
				'rules' => 'required',
				'type' => 'float'
			]
		),
		'loadPaymentByMedStaffFact' => [
			[
				'field' => 'MedStaffFact_id',
				'label' => 'Идентификатор места работы',
				'rules' => 'required',
				'type' => 'id'
			]
		]
	];


	protected function getPaymentId($data)
    {
        $q = "
            insert into persis.Payment (
                insDT,
                pmUser_insID,
                updDT,
                pmUser_updID,
                version,
                PaymentPercent,
                PaymentKind_id
            ) values (
                dbo.tzGetDate(),
                :pmUser_id,
                dbo.tzGetDate(),
                :pmUser_id,
                1,
                :PaymentPercent,
                :PaymentKind_id
            )
            returning Payment_id as \"Payment_id\";
        ";

        $result  = $this->db->query($q, $data);

        if(!is_object($result)) {
            $this->rollbackTransaction();
            throw new Exception('Не удалось создать Выплату');
        }

       return $result[0]['Payment_id'];
    }

    /**
     * Создание выплаты для строки штатного расписания
     * @param $data
     * @return array
     * @throws Exception
     */
	function createPayment($data) {
        $this->beginTransaction();

	    $Payment_id = $this->getPaymentId($data);

	    $query = "
            insert into persis.StaffPayment (
					StaffId,
					PaymentId
            ) values (
					:Staff_id,
					{$Payment_id}
            )
        ";

		//echo getDebugSQL($query, $data);exit;
        $this->queryResult($query, $data);

		if(!$this->commitTransaction()) {
		    throw new Exception('Не удалось создать Выплату');
        }

		return [['Payment_id' => $Payment_id]];
	}

    /**
     * Обновление выплаты
     *
     * @param array $data
     * @return array|false
     */
	public function updatePayment($data)
    {

		$query = "
			update 
                persis.Payment
				set
					updDT = dbo.tzGetDate(),
					pmUser_updID = :pmUser_id,
					PaymentPercent = :PaymentPercent,
					PaymentKind_id = :PaymentKind_id
				where
					id = :Payment_id
		";
		$resp = $this->queryResult($query, $data);

		return $resp;
	}

    /**
     * Получение выплат по строке штатного расписания
     * @param array $data
     * @return array|false
     */
	public function loadPaymentByStaff($data)
    {
		$where = '';
		$from = '';

		if(!empty($data['Lpu_id'])){
			$where .= ' AND s.Lpu_id = :Lpu_id';
			$from .= ' left join persis.v_Staff s on s.id = sp.StaffId';
		}
		$query = "
            select
                p.id as \"Payment_id\"
            from
                persis.Payment p
                inner join persis.StaffPayment sp on sp.PaymentId = p.id
                {$from}
            where
                sp.StaffId = :Staff_id
                {$where}
		";

		$resp = $this->queryResult($query, $data);

		return $resp;
	}

    /**
     * Создание выплаты для местам работы
     *
     * @param array $data
     * @return array|false
     * @throws Exception
     */
	public function createPaymentMedStaffFact($data)
    {
        $this->beginTransaction();

        $Payment_id = $this->getPaymentId($data);


        $query = "
		insert into persis.WorkPlacePayment (
                WorkPlaceId,
                PaymentId
            ) values (
                :MedStaffFact_id,
                {$Payment_id}
            )
		";
		//echo getDebugSQL($query, $data);exit;
		$resp = $this->queryResult($query, $data);

		return $resp;
	}

    /**
     * Получение выплат по месту работы
     *
     * @param array $data
     * @return array|false
     */
	public function loadPaymentByMedStaffFact($data)
    {
		$filter = '';
		if (!empty($data['Lpu_id'])) {
			$filter .= " and MS.Lpu_id = :Lpu_id";
		}
		$query = "
			select
					p.id as \"Payment_id\"
            from persis.Payment p
                inner join persis.WorkPlacePayment wp on wp.PaymentId = p.id
				left join v_MedStaffFact MS on MS.MedStaffFact_id = wp.WorkPlaceId
            where
                wp.WorkPlaceId = :MedStaffFact_id
                {$filter}
		";

		$resp = $this->queryResult($query, $data);

		return $resp;
	}

    /**
     * Получение выплат по ид
     * 
     * @param $data
     * @return array|false
     */
	public function loadPaymentById($data)
    {

		$query = "
			select
                p.id as \"Payment_id\",
                PaymentKind_id as \"PaymentKind_id\",
                PaymentPercent as \"PaymentPercent\"
            from
                persis.Payment p
            where
                p.id = :Payment_id
		";

		$resp = $this->queryResult($query, $data);

		return $resp;
	}
}