<?php
	/**
	 * Класс модели для работы с констркутором форм
	 */


class MedicalForm_model extends swPgModel {

	/**
	 * Constructor
	 */
	public function __construct() {
		parent::__construct();
	}

	/**
	 * @desc
	 */
	function getMedicalForms($data){
		$query = "";
		$params = array();
		$filter = "(1=1)";
		$join = "";
		if(!empty($data['Person_id'])) {
			$query = "
				with age as (
					select
						dbo.Age2(PS.Person_BirthDay, dbo.tzGetDate()) as age,
						PS.Sex_id as sex
					from v_PersonState PS
					where Person_id = :Person_id
				),
				ageGroup as (
					select
						case when age.age<14 then 1 else 2 end as ageGroup
					from age
				)
			";
			
			$params['Person_id'] = $data['Person_id'];
			//фильтр анкет по возрасту и полу
			//причем по возрасту не совсем правильно привязались, надо было хотя бы по PersonAgeGroup_Code
			//считаем что в MedicalForm своя шкала, до 14 лет = "1", от 14 лет = "2", все = "3".
			$filter .= "
				and (MF.Sex_id = 3 or MF.Sex_id = (select sex from age))
				and (MF.PersonAgeGroup_id = 3 or MF.PersonAgeGroup_id = (select ageGroup from ageGroup))
				";
		}
		
		$query .= "
			select
				to_char(MF.MedicalForm_insDT, 'dd.mm.yyyy') as \"MedicalForm_insDT\",
				to_char(MF.MedicalForm_updDT, 'dd.mm.yyyy') as \"MedicalForm_updDT\",
			    	MF.MedicalForm_Name as \"MedicalForm_Name\",
			    	MF.MedicalForm_Description \"MedicalForm_Description\",
			    	MF.MedicalForm_id \"MedicalForm_id\",
				case when MF.Sex_id = 1 then 'Мужчины' 
				when MF.Sex_id = 2 then 'Женщины' 
                		else 'Все' end || ', ' ||
				case
			  	when MF.PersonAgeGroup_id = 1 then 'до 14 лет' 
				when MF.PersonAgeGroup_id = 2 then 'после 14 лет' 
                		else 'Все' end as \"MedicalFormAgeSex\"
			from v_MedicalForm MF
				{$join}
			where {$filter}
		";

		$result = $this->db->query($query, $params);
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * @desc
	 */
	function saveMedicalFormData($data){

		$query = "
			select
				MedicalFormPerson_id as \"MedicalFormPerson_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from MedicalFormPerson_ins (
				MedicalForm_id := :MedicalForm_id,
				Person_id := :Person_id,
				pmUser_id := :pmUser_id
			)
		";

		$result = $this->db->query($query,$data)->result('array');

		foreach($data['MedicalFormData'] as $key => $value){
			$sql = "
				select
					MedicalFormData_id as \"MedicalForm_id\",
					Error_Code as \"Error_Code\",
					Error_Message as \"Error_Msg\"
				from p_MedicalFormData_ins (
					MedicalFormPerson_id := :MedicalFormPerson_id,
					MedicalFormAnswers_id := :MedicalFormAnswers_id,
					MedicalFormQuestion_id := :MedicalFormQuestion_id,
					MedicalFormData_ValueText := :MedicalFormData_ValueText,
					MedicalFormData_ValueDT := :MedicalFormData_ValueDT,
					pmUser_id := :pmUser_id
				)
			";

			$queryData = array(
				'MedicalFormPerson_id' => $result[0]['MedicalFormPerson_id'],
				'MedicalFormQuestion_id'=> $value->{'MedicalFormQuestion_id'},
				'MedicalFormData_ValueText' => null,
				'MedicalFormAnswers_id' => null,
				'MedicalFormData_ValueDT' => null,
				'pmUser_id' =>$data['pmUser_id']
			);
			if($value->{'xtype'} == 'textareafield'){

				$queryData['MedicalFormData_ValueText'] = $value->{'value'};
				$this->db->query($sql, $queryData);

			}
			else if($value->{'xtype'}  == 'previewDateOrTime'){

				$queryData['MedicalFormData_ValueDT'] = $value->{'value'};
				$this->db->query($sql, $queryData);

			}
			else if($value->{'xtype'}  == "segmentedbutton"){

				if(gettype($value->{'value'}) == "array"){
					foreach($value->{'value'} as $valueData){
						$queryData['MedicalFormAnswers_id'] = $valueData;
						$this->db->query($sql, $queryData);
					}
				}else{
					$queryData['MedicalFormAnswers_id'] = $value->{'value'};
					$this->db->query($sql, $queryData);
				}
			}
		}

		return $result;
	}

	/**
	 * @desc
	 */
	function updateMedicalForm($data){

		$queryData = "
			select
				PersonAgeGroup_id as \"PersonAgeGroup_id\",
				Sex_id as \"Sex_id\"
			from v_MedicalForm
			where MedicalForm_id = :MedicalForm_id
		";
		$resultData = $this->db->query($queryData,$data)->result('array');

		$query = "
			select
				MedicalForm_id as \"MedicalForm_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from p_MedicalForm_upd (
				MedicalForm_id := :MedicalForm_id,
				MedicalForm_Name := :MedicalForm_Name,
				MedicalForm_Description := :MedicalForm_Description,
				PersonAgeGroup_id := :PersonAgeGroup_id,
				Sex_id := :Sex_id,
				pmUser_id := :pmUser_id
			)
		";

		$result = $this->db->query($query,array(
			'MedicalForm_id'=>$data['MedicalForm_id'],
			'MedicalForm_Name'=>$data['MedicalForm_Name'],
			'MedicalForm_Description'=>$data['MedicalForm_Description'],
			'PersonAgeGroup_id'=>$resultData[0]['PersonAgeGroup_id'],
			'Sex_id'=>$resultData[0]['Sex_id'],

			'pmUser_id'=>$data['pmUser_id'],
		));

		$MedicalFormTree_data = $data['MedicalFormTree']->{'children'};

		foreach($MedicalFormTree_data as $value){

			if(	!empty($value->{'MedicalFormQuestion_deleted'})
				&& $value->{'MedicalFormQuestion_deleted'} == 2
				&& empty($value->{'MedicalFormQuestion_id'})){ // Удален не созданый элемент
					continue;
			}
			if( !empty($value->{'MedicalFormAnswers_deleted'}) && $value->{'MedicalFormAnswers_deleted'} == 2){

				$query = "
					select
						Error_Code as \"Error_Code\",
						Error_Message as \"Error_Msg\"
					from p_MedicalFormQuestion_del (
						MedicalFormQuestion_id := :MedicalFormQuestion_id,
						pmUser_id := :pmUser_id
					)
				";

				$resultQuestion = $this->db->query($query,array(
					'MedicalFormQuestion_id' => $value->{'MedicalFormQuestion_id'},
					'pmUser_id' => $data['pmUser_id'],
				));

				$resultQuestion = $resultQuestion->result('array');

			}else{
				if(!empty($value->{'MedicalFormQuestion_id'})){
					$procedureQuestion = 'p_MedicalFormQuestion_upd';
					$MedicalFormQuestion_id = $value->{'MedicalFormQuestion_id'};
				}else{
					$procedureQuestion = 'p_MedicalFormQuestion_ins';
					$MedicalFormQuestion_id = null;
				}

				$query = "
				select
					MedicalFormQuestion_id as \"MedicalFormQuestion_id\",
					Error_Code as \"Error_Code\",
					Error_Message as \"Error_Msg\"
				from {$procedureQuestion} (
					MedicalFormQuestion_id := :MedicalFormQuestion_id,
					MedicalFormQuestion_Name := :MedicalFormQuestion_Name,
					MedicalForm_id := :MedicalForm_id,
					AnswerType_id := :AnswerType_id,
					pmUser_id := :pmUser_id
				)
			";

				$resultQuestion = $this->db->query($query,array(
					'MedicalFormQuestion_id' => $MedicalFormQuestion_id,
					'MedicalFormQuestion_Name' => $value->{'MedicalFormQuestion_Name'},
					'AnswerType_id' => $value->{'AnswerType_id'},
					'MedicalForm_id' => $data['MedicalForm_id'],
					'pmUser_id' => $data['pmUser_id'],
				));

				$resultQuestion = $resultQuestion->result('array');
			}

			if(!empty($value->{'children'})){
				$answers = $value->{'children'};

				foreach($answers as $valueAnswers){
					if(	!empty($valueAnswers->{'MedicalFormAnswers_deleted'})
						&& $valueAnswers->{'MedicalFormAnswers_deleted'} == 2
						&& empty($valueAnswers->{'MedicalFormAnswers_id'})){ // Удален не созданый элемент
						continue;
					}
					if( !empty($valueAnswers->{'MedicalFormAnswers_deleted'}) && $valueAnswers->{'MedicalFormAnswers_deleted'} == 2){
						$query = "
							select
								Error_Code as \"Error_Code\",
								Error_Message as \"Error_Msg\"
							from p_MedicalFormAnswers_del (
								MedicalFormAnswers_id := :MedicalFormAnswers_id,
								pmUser_id := :pmUser_id
							)
						";

						$this->db->query($query,array(
							'MedicalFormAnswers_id' => $valueAnswers->{'MedicalFormAnswers_id'},
							'pmUser_id' => $data['pmUser_id'],
						));
					}else{
						if(!empty($valueAnswers->{'MedicalFormAnswers_id'})){
							$procedureAnswers = 'p_MedicalFormAnswers_upd';
							$MedicalFormAnswers_id = $valueAnswers->{'MedicalFormAnswers_id'};
						}else{
							$procedureAnswers = 'p_MedicalFormAnswers_ins';
							$MedicalFormAnswers_id = null;
						}

						$query = "
						select
							MedicalFormAnswers_id as \"MedicalFormAnswers_id\",
							Error_Code as \"Error_Code\",
							Error_Message as \"Error_Msg\"
						from {$procedureAnswers} (
							MedicalFormAnswers_id := :MedicalFormAnswers_id,
							MedicalFormAnswers_Name := :MedicalFormAnswers_Name,
							MedicalFormQuestion_id := :MedicalFormQuestion_id,
							MedicalForm_id := :MedicalForm_id,
							pmUser_id := :pmUser_id
						)
					";

						$this->db->query($query,array(
							'MedicalFormAnswers_id' => $MedicalFormAnswers_id,
							'MedicalFormQuestion_id' => $resultQuestion[0]['MedicalFormQuestion_id'],
							'MedicalFormAnswers_Name' => $valueAnswers->{'MedicalFormAnswers_Name'},
							'MedicalForm_id' => $data['MedicalForm_id'],
							'pmUser_id' => $data['pmUser_id'],
						));
					}
				}
			}
		}
		return $result->result('array');
	}

	/**
	 * @desc
	 */
	function loadMedicalFormData($data){
		$query = "
			select
			    MFD.MedicalFormQuestion_id as \"MedicalFormQuestion_id\",
			    MFD.MedicalFormAnswers_id as \"MedicalFormAnswers_id\",
				MFD.MedicalFormData_ValueText as \"MedicalFormData_ValueText\",
				MFD.MedicalFormData_ValueDT \"MedicalFormData_ValueDT\",
				to_char(MFD.MedicalFormData_ValueDT, 'dd.mm.yyyy') as \"DateValue\",
				to_char(MFD.MedicalFormData_ValueDT, 'hh24:mi') as \"TimeValue\"
			from v_MedicalFormData MFD
			left join v_MedicalFormPerson MFP on MFD.MedicalFormPerson_id = MFP.MedicalFormPerson_id
			where MFP.MedicalFormPerson_id = :MedicalFormPerson_id
		";

		$result = $this->db->query($query, $data);
		return $result->result('array');
	}

	/**
	 * @desc
	 */
	function loadMedicalForm($data){
		$query = "
			select
				'Question' as \"type\"
				--,MFQ.MedicalFormQuestion_name as \"text\"
				,*
			from v_MedicalForm MF
			inner join v_MedicalFormQuestion MFQ on MF.MedicalForm_id = MFQ.MedicalForm_id
			where MF.MedicalForm_id = :MedicalForm_id
		";

		$resultQuestion = $this->db->query($query, $data)->result('array');

		$query = "
			select
				'Answers' as \"type\"
				--,MFA.MedicalFormAnswers_name as \"text\", 
				,*
			from v_MedicalForm MF
			inner join v_MedicalFormAnswers MFA on MF.MedicalForm_id = MFA.MedicalForm_id
			where MF.MedicalForm_id = :MedicalForm_id
		";

		$resultAnswers = $this->db->query($query, $data)->result('array');

		$result = array_merge($resultQuestion, $resultAnswers);

		return $result;
	}
	/**
	 * @desc
	 */
	function buildTree(array &$elements, $parentId = 0) {
		$branch = array();

		foreach ($elements as $element) {
			if (!isset($element['MedicalFormAnswers_id']) && $parentId == 0) {

				$children = $this->buildTree($elements, $element['MedicalFormQuestion_id']);
				if ($children) {
					$element['children'] = $children;
				}
				$branch[] = $element;
				//unset($elements[$element['id']]);
			}
			else if( isset($element['MedicalFormAnswers_id']) && $element['MedicalFormQuestion_id'] == $parentId){
				$branch[] = $element;
				//unset($elements[$element['id']]);
			}
		}
		return $branch;
	}
	
	/**
	 * @desc
	 */
	function getMedicalForm($data){
		$query = "
			select
				*
			from v_MedicalForm MF
			where MF.MedicalForm_id = :MedicalForm_id
			limit 1
		";
		$result = $this->getFirstRowFromQuery($query, $data);
		if (!is_array($result)) {
			return false;
		} else return $result;
	}
	/**
	 * @desc
	 */
	function saveMedicalForm($data){
		if(empty($data['MedicalForm_id'])){
			$procedure ='p_MedicalForm_ins';
			$MedicalForm_id = null;
		}else{
			$procedure ='p_MedicalForm_upd';
			$MedicalForm_id = $data['MedicalForm_id'];
		}
		$query = "
			select
				MedicalForm_id as \"MedicalForm_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from {$procedure}(
				MedicalForm_id := :MedicalForm_id,
				PersonAgeGroup_id := :PersonAgeGroup_id,
				Sex_id := :Sex_id,
				pmUser_id := :pmUser_id
			)
		";

		$result = $this->db->query($query,array(
			'MedicalForm_id' => $MedicalForm_id,
			'PersonAgeGroup_id' => $data['PersonAgeGroup_id'],
			'Sex_id' => $data['Sex_id'],
			'pmUser_id'=>$data['pmUser_id']
		));

		return $result->result('array');
	}
	
	/**
	 * @desc
	 */
	function getMedicalFormInfo($data) {
		
		$query = "
			select
				MF.* 
			from v_MedicalFormPerson MFP
				inner join v_MedicalForm MF on MF.MedicalForm_id = MFP.MedicalForm_id
			where MFP.MedicalFormPerson_id = :MedicalFormPerson_id
		";

		return $this->db->query($query, $data)->result('array')[0];
	}
	
	/**
	 * Получить список актуальных анкет по пациенту
	 */
	function getMedicalFormActualList($data) {
		$queryParams = array(
			'Person_id' => $data['Person_id'],
		);
		
		$queryMedicalForm = "
			with medforms (MedicalForm_id) 
			as (
				select MedicalForm_id from v_MedicalFormPerson where Person_id = :Person_id group by MedicalForm_id
			)
			select
				'MedicalForm' as \"type\",
				to_char(MFP.MedicalFormPerson_insDT, 'dd.mm.yyyy') as \"PersonOnkoProfile_setDate\",
				MFP.MedicalForm_id as \"MedicalForm_id\",
				MFP.MedicalFormPerson_id as \"MedicalFormPerson_id\",
				MFP.MedicalFormPerson_id as \"PersonOnkoProfile_id\",
				MF.MedicalForm_Name as \"MedicalForm_Name\",
				MF.MedicalForm_Description as \"MedicalForm_Description\",
				MF.MedicalForm_Name as \"PersonProfileType_Name\"
			from medforms
			left join lateral (
				select * 
				from v_MedicalFormPerson
				where MedicalForm_id = medforms.MedicalForm_id and Person_id = :Person_id
				order by MedicalFormPerson_setDT DESC
				limit 1
			) MFP on true
			left join v_MedicalForm MF on MF.MedicalForm_id = MFP.MedicalForm_id";

		$resultMedicalForm = $this->db->query($queryMedicalForm, $queryParams);
		
		if ( is_object($resultMedicalForm) ) {
			return $resultMedicalForm->result('array');
		}
		else {
			return false;
		}
	}
}