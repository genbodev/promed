<?php defined('BASEPATH') or die ('No direct script access allowed');

/**
 * Class MorbusOnkoSopDiag_model
 *
 * @property-read  array $inputRules
 */
class MorbusOnkoSopDiag_model extends SwPgModel
{

	public $inputRules = [
		'saveMorbusOnkoSopDiag' => [
			['field' => 'Diag_id', 'label' => 'идентификатор сопутствующего заболевания', 'rules' => 'required', 'type' => 'id'],
			['field' => 'MorbusOnkoBase_id', 'label' => 'Идентификатор специфики', 'rules' => 'required', 'type' => 'id']
		],
		'deleteMorbusOnkoSopDiag' => [
			['field' => 'MorbusOnkoBaseDiagLink_id', 'label' => 'идентификатор сопутствующего заболевания', 'rules' => 'required', 'type' => 'id']
		],
		'loadMorbusOnkoSopDiagList' => [
			['field' => 'MorbusOnkoBase_id', 'label' => 'Идентификатор специфики', 'rules' => 'required', 'type' => 'id']
		],
		'loadMorbusOnkoSopDiag' => [
			['field' => 'MorbusOnkoBaseDiagLink_id', 'label' => 'идентификатор сопутствующего заболевания', 'rules' => 'required', 'type' => 'id']
		]
	];

    /**
     * Получение списка сопутствующих заболеваний
     *
     * @param $data
     * @return array|bool|false
     */
	public function loadMorbusOnkoSopDiagList($data)
    {
		$params = [];

		if(empty($data['MorbusOnkoBase_id'])) {
			return false;
		}

		$query = "
			select 
				mobdl.MorbusOnkoBaseDiagLink_id as \"MorbusOnkoBaseDiagLink_id\",
				rtrim(d.Diag_Code || ' ' || d.Diag_Name) as \"SopDiag_Name\"
			from
				dbo.v_MorbusOnkoBaseDiagLink mobdl
				left join v_Diag d on d.Diag_id = mobdl.Diag_id
			where
				mobdl.MorbusOnkoBase_id = :MorbusOnkoBase_id
		";

		return $this->queryResult($query, $data);
	}

    /**
     * Получение сопутствующего заболевания
     * @param $data
     * @return array|bool|false
     */
	public function loadMorbusOnkoSopDiag($data)
    {
		if(empty($data['MorbusOnkoBaseDiagLink_id'])){
            return false;

		}

		$query = "
			select 
				mobdl.MorbusOnkoBaseDiagLink_id as \"MorbusOnkoBaseDiagLink_id\",
				mobdl.MorbusOnkoBase_id as \"MorbusOnkoBase_id\",
				mobdl.Diag_id as \"Diag_id\"
			from
				dbo.v_MorbusOnkoBaseDiagLink mobdl
			where
				mobdl.MorbusOnkoBaseDiagLink_id = :MorbusOnkoBaseDiagLink_id
		";

		return $this->queryResult($query, $data);
	}

	/**
	 * @param array $data
	 * @return array|bool
	 */
	public function getViewData($data)
	{
		$query = "
			select
				case
					when 1=1 then 'edit'
					else 'view'
				end as \"accessType\",
				mobdl.MorbusOnkoBaseDiagLink_id as \"MorbusOnkoBaseDiagLink_id\",
				MO.MorbusOnko_id as \"MorbusOnko_id\",
				rtrim(d.Diag_Code || ' ' || d.Diag_Name) as \"SopDiag_Name\",
				:Evn_id as \"MorbusOnko_pid\",
				Morbus.Morbus_id as \"Morbus_id\"
			from
				dbo.v_Morbus Morbus
				inner join dbo.v_MorbusOnko MO on Morbus.Morbus_id = MO.Morbus_id
				inner join dbo.v_MorbusOnkoBase mob on Morbus.MorbusBase_id = mob.MorbusBase_id
				inner join dbo.v_MorbusOnkoBaseDiagLink mobdl on mob.MorbusOnkoBase_id = mobdl.MorbusOnkoBase_id
				left join v_Diag d on d.Diag_id = mobdl.Diag_id
			where
				Morbus.Morbus_id = :Morbus_id
		";
		$params = [
			'Morbus_id' => $data['Morbus_id'],
			'Evn_id' => $data['Evn_id']
		];
		$result = $this->db->query($query, $params);

		if (!is_object($result) )
			return false;

        return $result->result('array');
    }

    /**
     * Сохранение сопутствующего заболевания
     *
     * @param $data
     * @return mixed
     */
    public function saveMorbusOnkoSopDiag($data)
    {
        $proc = 'dbo.p_MorbusOnkoBaseDiagLink_' . empty($data['MorbusOnkoBaseDiagLink_id']) ? 'ins' : 'upd';

        $params = [
            'MorbusOnkoBaseDiagLink_id' => (!empty($data['MorbusOnkoBaseDiagLink_id']) ? $data['MorbusOnkoBaseDiagLink_id'] : null),
            'Diag_id' => (!empty($data['Diag_id']) ? $data['Diag_id'] : null),
            'MorbusOnkoBase_id' => (!empty($data['MorbusOnkoBase_id']) ? $data['MorbusOnkoBase_id'] : null),
            'pmUser_id' => (!empty($data['pmUser_id']) ? $data['pmUser_id'] : 1)
        ];

		return $this->execCommonSP($proc,$params);
	}

    /**
     * Удаление сопутствующего заболевания
     *
     * @param array $data
     * @return array|mixed
     * @throws Exception
     */
	public function deleteMorbusOnkoSopDiag($data)
    {
		if(!empty($data['MorbusOnkoBaseDiagLink_id'])){
            throw new Exception('Не указан идентификатор Сопутствующего заболевания');
		}

        $params = [
            'MorbusOnkoBaseDiagLink_id' => (!empty($data['MorbusOnkoBaseDiagLink_id']) ? $data['MorbusOnkoBaseDiagLink_id'] : null)
        ];
        return $this->execCommonSP('dbo.p_MorbusOnkoBaseDiagLink_del', $params);
	}
}