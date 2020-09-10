<?php
	/**
	 * Класс модели для работы с констркутором форм
	 */


class MedicalForm_model extends swModel {

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
			$query = "declare @sex int, @age int, @ageGroup int
				
				select
					@age = dbo.Age2(PS.Person_BirthDay, dbo.tzGetDate())
					,@sex = PS.Sex_id
				from v_PersonState PS with(nolock) where Person_id = :Person_id
				
				select @ageGroup = case when @age<14 then 1 else 2 end
			";
			
			$params['Person_id'] = $data['Person_id'];
			//фильтр анкет по возрасту и полу
			//причем по возрасту не совсем правильно привязались, надо было хотя бы по PersonAgeGroup_Code
			//считаем что в MedicalForm своя шкала, до 14 лет = "1", от 14 лет = "2", все = "3".
			$filter .= "
				and (MF.Sex_id = 3 or MF.Sex_id = @sex)
				and (MF.PersonAgeGroup_id = 3 or @ageGroup = MF.PersonAgeGroup_id)
				";
		}
		
		$query .= "
			select
				CONVERT(char(10), MF.MedicalForm_insDT,104) as MedicalForm_insDT,
				CONVERT(char(10), MF.MedicalForm_updDT,104) as MedicalForm_updDT,
			    MF.MedicalForm_Name,
			    MF.MedicalForm_Description,
			    MF.MedicalForm_id,
			case when MF.Sex_id = 1 then 'Мужчины' 
				when MF.Sex_id = 2 then 'Женщины' 
                else 'Все'
                end
			+ ', ' +
			case
			  	when MF.PersonAgeGroup_id = 1 then 'до 14 лет' 
				when MF.PersonAgeGroup_id = 2 then 'после 14 лет' 
                else 'Все'
               end as MedicalFormAgeSex
			from v_MedicalForm MF with(nolock)
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

		$query = '
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000)
			
			exec p_MedicalFormPerson_ins
				@MedicalFormPerson_id = @Res output,
				@MedicalForm_id = :MedicalForm_id,
				@Person_id = :Person_id,
				
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @Res as MedicalFormPerson_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		';

		$result = $this->db->query($query,$data)->result('array');

		foreach($data['MedicalFormData'] as $key => $value){
			$sql = '
				declare
					@Res bigint,
					@ErrCode int,
					@ErrMessage varchar(4000)
				
				exec p_MedicalFormData_ins
					@MedicalFormData_id = @Res output,
					@MedicalFormPerson_id = :MedicalFormPerson_id,
					@MedicalFormAnswers_id = :MedicalFormAnswers_id,
					@MedicalFormQuestion_id = :MedicalFormQuestion_id,
					@MedicalFormData_ValueText = :MedicalFormData_ValueText,
					@MedicalFormData_ValueDT = :MedicalFormData_ValueDT,
				
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMessage output;
	
				select @Res as MedicalForm_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			';

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
			* 
		from v_MedicalForm with(nolock)
		where MedicalForm_id = :MedicalForm_id 
		";
		$resultData = $this->db->query($queryData,$data)->result('array');

		$query = '
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000)

			set @Res = :MedicalForm_id;
			
			exec p_MedicalForm_upd
				@MedicalForm_id = @Res output,
				@MedicalForm_Name = :MedicalForm_Name,
				@MedicalForm_Description = :MedicalForm_Description,
				@PersonAgeGroup_id = :PersonAgeGroup_id,
				@Sex_id = :Sex_id,
				
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @Res as MedicalForm_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		';

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
					declare
						@ErrCode int,
						@ErrMessage varchar(4000)
					
					exec p_MedicalFormQuestion_del
						@MedicalFormQuestion_id = :MedicalFormQuestion_id,
						
						@pmUser_id = :pmUser_id,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMessage output;
		
					select  @ErrCode as Error_Code, @ErrMessage as Error_Msg;
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
				declare
					@Res bigint,
					@ErrCode int,
					@ErrMessage varchar(4000)
	
				set @Res = :MedicalFormQuestion_id;
				
				exec {$procedureQuestion}
					@MedicalFormQuestion_id = @Res output,
					@MedicalFormQuestion_Name = :MedicalFormQuestion_Name,
					@MedicalForm_id = :MedicalForm_id,
					@AnswerType_id = :AnswerType_id,
					
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMessage output;
	
				select @Res as MedicalFormQuestion_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
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
						$query = '
							declare
								@ErrCode int,
								@ErrMessage varchar(4000)
							
							exec p_MedicalFormAnswers_del
								@MedicalFormAnswers_id = :MedicalFormAnswers_id,
								
								@pmUser_id = :pmUser_id,
								@Error_Code = @ErrCode output,
								@Error_Message = @ErrMessage output;
				
							select  @ErrCode as Error_Code, @ErrMessage as Error_Msg;
						';

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

						$query = '
						declare
							@Res bigint,
							@ErrCode int,
							@ErrMessage varchar(4000)
						set @Res = :MedicalFormAnswers_id;
						
						exec '.$procedureAnswers.'
							@MedicalFormAnswers_id = @Res output,
							@MedicalFormAnswers_Name = :MedicalFormAnswers_Name,
							@MedicalFormQuestion_id = :MedicalFormQuestion_id,
							@MedicalForm_id = :MedicalForm_id,
							
							@pmUser_id = :pmUser_id,
							@Error_Code = @ErrCode output,
							@Error_Message = @ErrMessage output;
			
						select @Res as MedicalFormAnswers_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
					';

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
			    MFD.MedicalFormQuestion_id,
			    MFD.MedicalFormAnswers_id,
				MFD.MedicalFormData_ValueText,
				MFD.MedicalFormData_ValueDT,
				convert(varchar(10), MFD.MedicalFormData_ValueDT, 104) as DateValue,
				convert(varchar(5), MFD.MedicalFormData_ValueDT, 108) as TimeValue
			from v_MedicalFormData MFD with(nolock)
			left join v_MedicalFormPerson MFP with(nolock) on MFD.MedicalFormPerson_id = MFP.MedicalFormPerson_id
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
				'Question' as type
				--,MFQ.MedicalFormQuestion_name as text
				,*
			from v_MedicalForm MF with(nolock)
			inner join v_MedicalFormQuestion MFQ with(nolock) on MF.MedicalForm_id = MFQ.MedicalForm_id
			where MF.MedicalForm_id = :MedicalForm_id
		";

		$resultQuestion = $this->db->query($query, $data)->result('array');

		$query = "
			select
				'Answers' as type
				--,MFA.MedicalFormAnswers_name as text, 
				,*
			from v_MedicalForm MF
			inner join v_MedicalFormAnswers MFA with(nolock) on MF.MedicalForm_id = MFA.MedicalForm_id
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
			select top 1
				*
			from v_MedicalForm MF with(nolock)
			where MF.MedicalForm_id = :MedicalForm_id
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
		$query = '
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000)

			set @Res = :MedicalForm_id;
	
			exec '.$procedure.'
				@MedicalForm_id = @Res output,
				@PersonAgeGroup_id = :PersonAgeGroup_id,
				@Sex_id = :Sex_id,
				
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @Res as MedicalForm_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		';

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
			from v_MedicalFormPerson MFP with(nolock)
				inner join v_MedicalForm MF with(nolock) on MF.MedicalForm_id = MFP.MedicalForm_id
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
				select MedicalForm_id from v_MedicalFormPerson with(nolock) where Person_id = :Person_id group by MedicalForm_id
			)
			select
				'MedicalForm' as type,
				convert(varchar(10), MFP.MedicalFormPerson_insDT,104) as PersonOnkoProfile_setDate,
				MFP.MedicalForm_id,
				MFP.MedicalFormPerson_id,
				MFP.MedicalFormPerson_id as PersonOnkoProfile_id,
				MF.MedicalForm_Name,
				MF.MedicalForm_Description,
				MF.MedicalForm_Name as PersonProfileType_Name
			from medforms with(nolock)
			outer apply (
				select top 1 * 
				from v_MedicalFormPerson with(nolock)
				where MedicalForm_id = medforms.MedicalForm_id and Person_id = :Person_id
				order by MedicalFormPerson_setDT DESC
			) MFP
			left join v_MedicalForm MF with(nolock) on MF.MedicalForm_id = MFP.MedicalForm_id";

		$resultMedicalForm = $this->db->query($queryMedicalForm, $queryParams);
		
		if ( is_object($resultMedicalForm) ) {
			return $resultMedicalForm->result('array');
		}
		else {
			return false;
		}
	}
}