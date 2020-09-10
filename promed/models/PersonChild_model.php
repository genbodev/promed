<?php defined('BASEPATH') or die ('No direct script access allowed');
class PersonChild_model extends swModel {
	/**
	 * @construct
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * @comment
	 */
	function chekPersonChild($data){
		$query = "
			Select top 1 case when isnull(EvnPS.EvnPS_id,:EvnPS_id) = :EvnPS_id then 1 else 0 end as editPersonChild 
			from v_personchild pc with(nolock) 
			left join v_EvnPS EvnPS (nolock) on EvnPS.EvnPS_id = pc.EvnPS_id
			where pc.person_id=:Person_id
		";

		$result = $this->db->query($query, array(
			'Person_id' => $data['Person_id'],
			"EvnPS_id" => $data['EvnPS_id']
		));
		
		if ( is_object($result) ) {
			
			$res=$result->result('array');
			if(count($res)>0){
				return $res;
			}else{
				return array(array('editPersonChild'=>1));
			}
		}
		else {
			return false;
		}
	}
	/**
	 *
	 * @param type $data
	 * @return type 
	 */
	function loadPersonChildData($data) {
		$query = "
			select top 1
				PC.PersonChild_id,
				PC.ChildTermType_id,
				PC.FeedingType_id,
				ISNULL(PC.PersonChild_BCGNum, '') as PersonChild_BCGNum,
				ISNULL(PC.PersonChild_BCGSer, '') as PersonChild_BCGSer,
				PC.PersonChild_IsAidsMother,
				PC.PersonChild_IsBCG,
				PC.Person_id,
				PC.Server_id,
				PersonChild_CountChild,
				ChildPositionType_id,
				PersonChild_IsRejection
			from
				v_PersonChild PC with (nolock)
			where (1 = 1)
				and PC.Person_id = :Person_id
		";

		$result = $this->db->query($query, array(
			'Person_id' => $data['Person_id']
		));
		
		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}


	/**
	 *
	 * @param type $data
	 * @return type 
	 */
	function savePersonChild($data) {
		$procedure = "p_PersonChild_ins";

        $queryParams = array(
            'Server_id' => $data['Server_id'],
			'BirthSpecStac_id' => (!empty($data['BirthSpecStac_id']) ? $data['BirthSpecStac_id'] : NULL),
            'PersonChild_id' => (!empty($data['PersonChild_id']) ? $data['PersonChild_id'] : NULL),
			'EvnPS_id' => (!empty($data['EvnPS_id']) ? $data['EvnPS_id'] : NULL),
			'pmUser_id' => $data['pmUser_id'],
            'Person_id' => $data['Person_id']
        );

        $queryParams['ResidPlace_id'            ] = null;
        $queryParams['PersonChild_IsManyChild'  ] = null;
        $queryParams['PersonChild_IsBad'        ] = null;
        $queryParams['PersonChild_IsYoungMother'] = null;
        $queryParams['PersonChild_IsIncomplete' ] = null;
        $queryParams['PersonChild_IsTutor'      ] = null;
        $queryParams['PersonChild_IsMigrant'    ] = null;
        $queryParams['HealthKind_id'            ] = null;
        $queryParams['InvalidKind_id'           ] = null;
        $queryParams['PersonChild_IsInvalid'    ] = null;
        $queryParams['PersonChild_invDate'      ] = null;
        $queryParams['HealthAbnorm_id'          ] = null;
        $queryParams['HealthAbnormVital_id'     ] = null;
        $queryParams['Diag_id'                  ] = null;
        $queryParams['PersonSprTerrDop_id'      ] = null;
		$queryParams['BirthSvid_id'				] = null;

		if ( !empty($data['PersonChild_id']) ) {
			$procedure = "p_PersonChild_upd";
            $row = $this->getFirstRowFromQuery('select * from v_PersonChild with(nolock) where personChild_id = :PersonChild_id', array('PersonChild_id' => $data['PersonChild_id']));
            if ($row) {
            	foreach($queryParams as $key => $value) {
					if ((empty($value)||$value==NULL) && !empty($row[$key])) {
						$queryParams[$key] = $row[$key];
					}
				}
            }
		}
		
            $queryParams['FeedingType_id'] =(!empty($data['FeedingType_id']) ? $data['FeedingType_id'] : NULL);
            $queryParams['ChildTermType_id'] = (!empty($data['ChildTermType_id']) ? $data['ChildTermType_id'] : NULL);
            $queryParams['PersonChild_IsAidsMother'] = (!empty($data['PersonChild_IsAidsMother']) ? $data['PersonChild_IsAidsMother'] : NULL);
            $queryParams['PersonChild_IsBCG'] = (!empty($data['PersonChild_IsBCG']) ? $data['PersonChild_IsBCG'] : NULL);
            $queryParams['PersonChild_BCGSer'] = (!empty($data['PersonChild_BCGSer']) ? $data['PersonChild_BCGSer'] : NULL);
            $queryParams['PersonChild_BCGNum'] = (!empty($data['PersonChild_BCGNum']) ? $data['PersonChild_BCGNum'] : NULL);
            $queryParams['PersonChild_CountChild'] = (!empty($data['PersonChild_CountChild']) ? $data['PersonChild_CountChild'] : NULL);
            $queryParams['ChildPositionType_id'] = (!empty($data['ChildPositionType_id']) ? $data['ChildPositionType_id'] : NULL);
            $queryParams['PersonChild_IsRejection'] = (!empty($data['PersonChild_IsRejection']) ? $data['PersonChild_IsRejection'] : NULL);
		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);

			set @Res = :PersonChild_id;

			exec " . $procedure . "
				@Server_id = :Server_id,
				@PersonChild_id = @Res output,
				@Person_id = :Person_id,
				@FeedingType_id = :FeedingType_id,
				@ChildTermType_id = :ChildTermType_id,
				@PersonChild_IsAidsMother = :PersonChild_IsAidsMother,
				@PersonChild_IsBCG = :PersonChild_IsBCG,
				@PersonChild_BCGSer = :PersonChild_BCGSer,
				@PersonChild_BCGNum = :PersonChild_BCGNum,
				@BirthSvid_id = :BirthSvid_id,
				@EvnPS_id = :EvnPS_id,
				@BirthSpecStac_id = :BirthSpecStac_id,
				@PersonChild_CountChild = :PersonChild_CountChild, -- int
				@ChildPositionType_id = :ChildPositionType_id, -- bigint
				@PersonChild_IsRejection = :PersonChild_IsRejection, -- bigint
                @ResidPlace_id             = :ResidPlace_id            ,
                @PersonChild_IsManyChild   = :PersonChild_IsManyChild  ,
                @PersonChild_IsBad         = :PersonChild_IsBad        ,
                @PersonChild_IsYoungMother = :PersonChild_IsYoungMother,
                @PersonChild_IsIncomplete  = :PersonChild_IsIncomplete ,
                @PersonChild_IsTutor       = :PersonChild_IsTutor      ,
                @PersonChild_IsMigrant     = :PersonChild_IsMigrant    ,
                @HealthKind_id             = :HealthKind_id            ,
                @InvalidKind_id            = :InvalidKind_id           ,
                @PersonChild_IsInvalid     = :PersonChild_IsInvalid    ,
                @PersonChild_invDate       = :PersonChild_invDate      ,
                @HealthAbnorm_id           = :HealthAbnorm_id          ,
                @HealthAbnormVital_id      = :HealthAbnormVital_id     ,
                @Diag_id                   = :Diag_id                  ,
                @PersonSprTerrDop_id      =  :PersonSprTerrDop_id      ,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @Res as PersonChild_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		/*echo getDebugSQL($query, $queryParams);exit();*/
		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (сохранение сведений о новорожденном)'));
		}
	}
}
