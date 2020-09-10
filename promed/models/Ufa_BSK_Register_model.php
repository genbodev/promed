<?php
defined('BASEPATH') or die('No direct script access allowed');
/**
 * Ufa_BSK_Register_model - молеь для работы с данными БСК (Башкирия)
 * 
 * @package			BSK
 * @author			Васинский Игорь 
 * @version			20.08.2014
 */

class Ufa_BSK_Register_model extends swModel
{
    
    var $scheme = "r2";
    var $listMorbusType = '(84,88,89, 50)';
    /**
     *  Получение всех рекомендаций по типу
     */
    
    
    /**
     * comments
     */
    function __construct()
    {
        parent::__construct();
    }
    
    /**
     *  Получение списка рекомендаций для вкладки "Управление рекомендациями"
     */
    public function listRecomendation($data)
    {
        $params = array(
            'BSKObservRecomendationType_id' => $data['BSKObservRecomendationType_id'],
            'searchRecomendation_text' => isset($data['searchRecomendation_text']) ? '%' . $data['searchRecomendation_text'] . '%' : '%%'
        );
        
        $query = "
            select 
                BSKObservRecomendation_id,
                BSKObservRecomendation_text
             from r2.BSKObservRecomendation with(nolock)
             where BSKObservRecomendationType_id = :BSKObservRecomendationType_id
             and BSKObservRecomendation_deleted = 1   
             and BSKObservRecomendation_text like :searchRecomendation_text
        ";
        
        $result = $this->db->query($query, $params);
        
        //echo getDebugSql($query, $params);
        //exit;
        
        if (is_object($result)) {
            return $result->result('array');
        } else {
            return false;
        }
    }
    
    /**
     *  Получение рекомендаций              
     */
    function getRecomendations($data)
    {
        $params = array(
            'BSKObservRecomendationType_id' => $data['BSKObservRecomendationType_id'],
            'BSKObservElementValues_id' => $data['BSKObservElementValues_id'],
            'searchRecomendation_text' => isset($data['searchRecomendation_text']) ? '%' . $data['searchRecomendation_text'] . '%' : '%%'
        );
        
        
        
        
        $query = "
            select 
            R.BSKObservRecomendation_id,
            RT.BSKObservRecomendationType_id,
            L.BSKObservElementValues_id,
            R.BSKObservRecomendation_text,
            case 
            	when RL.BSKObservElementValues_id = :BSKObservElementValues_id and RL.BSKObservRecomendationLink_deleted = 1  then 1 
            	else 0 
            	end 
            as mark
            from r2.BSKObservRecomendation R with(nolock)
            left join r2.BSKObservRecomendationLink RL with(nolock) on R.BSKObservRecomendation_id = RL.BSKObservRecomendation_id and RL.BSKObservRecomendationLink_deleted = 1
            left join  r2.BSKObservElementValues L with(nolock) on RL.BSKObservElementValues_id = L.BSKObservElementValues_id
            left join r2.BSKObservRecomendationType RT with(nolock) on RT.BSKObservRecomendationType_id = R.BSKObservRecomendationType_id
            where  RT.BSKObservRecomendationType_id = :BSKObservRecomendationType_id
            
            and BSKObservRecomendation_text like :searchRecomendation_text
            /*and L.BSKObservElementValues_id is not null
            
            or
            ((case 
            	when RL.BSKObservElementValues_id = :BSKObservElementValues_id and RL.BSKObservRecomendationLink_deleted = 1  then 1 
            	else 0 
            	end)  = 1 
                
             and RT.BSKObservRecomendationType_id = :BSKObservRecomendationType_id )     
                                                            
            order by case 
            	when RL.BSKObservElementValues_id = :BSKObservElementValues_id then 1 
            	else 0 
            	end,RL.BSKObservElementValues_id 
          */
        ";
        
        /*  Странный кусок запроса, разобраться нужно.
        
            and L.BSKObservElementValues_id is not null
            or
            ((case 
            	when RL.BSKObservElementValues_id = :BSKObservElementValues_id and RL.BSKObservRecomendationLink_deleted = 1  then 1 
            	else 0 
            	end)  = 1 
                
             and RT.BSKObservRecomendationType_id = :BSKObservRecomendationType_id )     
                                                            
            order by case 
            	when RL.BSKObservElementValues_id = :BSKObservElementValues_id then 1 
            	else 0 
            	end,RL.BSKObservElementValues_id 
         */               
        
        //echo getDebugSql($query, $params);
        //exit;        
        
        $result = $this->db->query($query, $params);
        
        if (is_object($result)) {
            return $result->result('array');
        } else {
            return false;
        }
        
    }
    
    /**
     * функция удаления ранее указанных рекомендаций 
     */
    function deleteRecomendation($data)
    {

        $params = array(
            'BSKObservElementValues_id' => $data['BSKObservElementValues_id'],
            'BSKObservRecomendationType_id' => $data['BSKObservRecomendationType_id'],
            'pmUser_id' => $data['pmUser_id']
        );
           
        /*
        $query = "
                 update  r2.BSKObservRecomendationLink  
                 set  
                    BSKObservRecomendationLink_deleted = 2,
                    BSKObservRecomendationLink_updDT = getdate(),
                    pmUser_updID = :pmUser_updID
                 where BSKObservElementValues_id = :BSKObservElementValues_id 
                 and BSKObservRecomendation_id in(
                    select 
                    	R.BSKObservRecomendation_id
                    from r2.BSKObservRecomendationLink  RL with(nolock)
                    left join r2.BSKObservRecomendation R with(nolock) on R.BSKObservRecomendation_id = RL.BSKObservRecomendation_id
                    where R.BSKObservRecomendationType_id = :BSKObservRecomendationType_id and RL.BSKObservElementValues_id = :BSKObservElementValues_id                 
                 )
                 ";
        */
		$query = "
			declare
				@BSKObservRecomendationType_id bigint,
				@BSKObservElementValues_id bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);

			exec r2.p_BSKObservRecomendationLink_del
				@BSKObservRecomendationType_id = :BSKObservRecomendationType_id,
				@BSKObservElementValues_id = :BSKObservElementValues_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
        
        $result = $this->db->query($query, $params);
        
        if (is_object($result)) {
            return $result->result('array');
        } else {
            return false;
        }
    }
    
    /**
     * Удаление рекомендации
     */
    function deleteEditRecomendation($data)
    {
        
        $params = array(
            'BSKObservRecomendation_id' => $data['BSKObservRecomendation_id'],
            'pmUser_id' => $data['pmUser_id']
        );
        /*
        return;
        $query = "
                  update r2.BSKObservRecomendation 
                  set BSKObservRecomendation_deleted = 2,
                  BSKObservRecomendation_updDT = getdate(),
                  pmUser_updID = :pmUser_updID
                  where BSKObservRecomendation_id = :BSKObservRecomendation_id              
        ";
        */
		$query = "
			declare
				@BSKObservRecomendation_id bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);

			exec r2.p_BSKEditRecomendation_del
				@BSKObservRecomendation_id = :BSKObservRecomendation_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
        
        $result = $this->db->query($query, $params);
        
        //echo getDebugSql($query, $params);
        //exit;
        
        if (is_object($result)) {
            return $result->result('array');
        } else {
            return false;
        }
        
    }
    /**
     * Сохранение рекомендаций после редактирования
     */
    function saveAfterEditRecomendation($data)
    {
        return;
        $params = array(
            'BSKObservRecomendation_text' => $data['BSKObservRecomendation_text'],
            'BSKObservRecomendation_id' => $data['BSKObservRecomendation_id'],
            'pmUser_id' => $data['pmUser_id']
        );
        /*
        $query = "
            update r2.BSKObservRecomendation 
            set BSKObservRecomendation_text = :BSKObservRecomendation_text, 
            pmUser_updID = :pmUser_updID,
            BSKObservRecomendation_updDT = getdate()  
            where BSKObservRecomendation_id = :BSKObservRecomendation_id
        ";
        */
		$query = "
			declare
				@BSKObservRecomendation_text varchar(500),
                @BSKObservRecomendation_id bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);

			exec r2.p_BSKEditRecomendation_upd
				@BSKObservRecomendation_text = :BSKObservRecomendation_text,
                @BSKObservRecomendation_id = :BSKObservRecomendation_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
        
        $result = $this->db->query($query, $params);
        
        //echo getDebugSql($query, $params);
        //exit;
        
        if (is_object($result)) {
            return $result->result('array');
        } else {
            return false;
        }
    }
    
    /**
     * функция многократного вызова сохранения рекомендаций 
     */
    function preSaveRecomendation($data)
    {
        $params = array(
            'BSKObservElementValues_id' => $data['BSKObservElementValues_id'],
            'BSKObservRecomendation_id' => $data['BSKObservRecomendation_id'],
            'pmUser_id' => $data['pmUser_id']
        );
        
        //echo '<pre>' . print_r($params, 1) . '</pre>';
                
        $query = "
              
			declare
				@BSKObservRecomendation_id varchar(500),
                @BSKObservElementValues_id bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);        
    
            if((select count(BSKObservRecomendationLink_id) from r2.BSKObservRecomendationLink with(nolock) where BSKObservElementValues_id = :BSKObservElementValues_id and BSKObservRecomendation_id = :BSKObservRecomendation_id) = 0)
            begin
                                  
        		exec r2.p_BSKPreRecomendation_ins
        			@BSKObservRecomendation_id = :BSKObservRecomendation_id,
                    @BSKObservElementValues_id = :BSKObservElementValues_id,
        			@pmUser_id = :pmUser_id,
        			@Error_Code = @ErrCode output,
        			@Error_Message = @ErrMessage output;
        		select @ErrCode as Error_Code, @ErrMessage as Error_Msg;                  
              
            end  
            else
    
        		exec r2.p_BSKPreRecomendation_upd
        			@BSKObservRecomendation_id = :BSKObservRecomendation_id,
                    @BSKObservElementValues_id = :BSKObservElementValues_id,
        			@pmUser_id = :pmUser_id,
        			@Error_Code = @ErrCode output,
        			@Error_Message = @ErrMessage output;
        		select @ErrCode as Error_Code, @ErrMessage as Error_Msg;                 
        ";

        /**
          insert into  r2.BSKObservRecomendationLink
          (
            BSKObservRecomendation_id,
            BSKObservElementValues_id,
            pmUser_insID,
            pmUser_updID,
            BSKObservRecomendationLink_insDT,
            BSKObservRecomendationLink_updDT,
            BSKObservRecomendationLink_deleted
          )
          values
          (
           :BSKObservRecomendation_id,
           :BSKObservElementValues_id,
           :pmUser_insID,
           null,
           getdate(),
           null,
           1
          )
          */
          
          /**
           update  r2.BSKObservRecomendationLink set BSKObservRecomendationLink_deleted = 1
           where BSKObservRecomendation_id = :BSKObservRecomendation_id and BSKObservElementValues_id = :BSKObservElementValues_id;
          */

        //echo getDebugSql($query, $params);
        //exit;
                                      
        $result = $this->db->query($query, $params);
        
        if (is_object($result)) {
            return $result->result('array');
        } else {
            return false;
        }
    }
    
    /**
     *  Создание рекомендации
     */
    function addRecomendation($data)
    {
    
        $params = array(
            'BSKObservRecomendation_text' => $data['BSKObservRecomendation_text'],
            'BSKObservRecomendationType_id' => $data['BSKObservRecomendationType_id'],
            'pmUser_id' => $data['pmUser_id']
        );
        /*
        return;
        $query = "
        insert into r2.BSKObservRecomendation 
        (
         BSKObservRecomendationType_id,
         BSKObservRecomendation_text,
         pmUser_insID,
         pmUser_updID,
         BSKObservRecomendation_insDT,
         BSKObservRecomendation_updDT,
         BSKObservRecomendation_deleted
        )
        values
        (
          :BSKObservRecomendationType_id,
          :BSKObservRecomendation_text,
          :pmUser_id,
          null,
          getdate(),
          null,
          1  
        )
        ";*/
        $query = "
			declare
				@BSKObservRecomendation_text varchar(500),
                @BSKObservRecomendationType_id bigint,
                @pmUser_id bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);

    		exec r2.p_BSKRecomendation_ins
    			@BSKObservRecomendation_text = :BSKObservRecomendation_text,
                @BSKObservRecomendationType_id = :BSKObservRecomendationType_id,
    			@pmUser_id = :pmUser_id,
    			@Error_Code = @ErrCode output,
    			@Error_Message = @ErrMessage output;
    		select @ErrCode as Error_Code, @ErrMessage as Error_Msg;             
        ";       
        
        $result = $this->db->query($query, $params);
        
        //echo getDebugSql($query, $params);
        //exit;
        
        if (is_object($result)) {
            return $result->result('array');
        } else {
            return false;
        }
    }
    
    /**
     * Сохранение рекомендаций
     */
    function saveRecomendation($data)
    {
        $recomendations = json_decode($data['jsonSetRecomendation']);
        
        $params = array(
            'BSKObservElementValues_id' => $data['BSKObservElementValues_id'],
            'BSKObservRecomendationType_id' => $data['BSKObservRecomendationType_id'],
            'pmUser_id'=>$data['pmUser_id']
        );
        
        $this->deleteRecomendation($params);
        
        
        foreach ($recomendations as $k => $v) {
            
            $params = array(
                'BSKObservRecomendation_id' => $v,
                'BSKObservElementValues_id' => $data['BSKObservElementValues_id'],
                'BSKObservRecomendationType_id' => $data['BSKObservRecomendationType_id'],
                'pmUser_id' => $data['pmUser_id']
            );
            
            $query = $this->preSaveRecomendation($params);
            
            //echo $query.'<hr/>';
            
            //echo '<pre>' . print_r($params, 1) . '</pre>';
            //exit;
        }
    }
    
    
    
    /**
     * Получение общих вопросов
     */
    function getLinks()
    {
        $params = array();
        
        $query = "select 
                 BSKObservElement_id,
                 BSKObject_id
                from r2.BSKObservElementLink with(nolock)
                where BSKObservElementLink_deleted = 1";
        
        $result = $this->db->query($query, $params);
        
        if (is_object($result)) {
            return $result->result('array');
        } else {
            return false;
        }
    }
    
    /**
     *  Управление общими вопросами
     */
    function manageLinks($data)
    {
        $params = array(
            'BSKObject_id' => $data['BSKObject_id'],
            'BSKObservElement_id' => $data['BSKObservElement_id'],
            'pmUser_id' => $data['pmUser_id']
        );
        
        if ($data['action'] == 'set') {
            $query = "
    			declare
    				@BSKObservElementLink_deleted bigint = 1,
                    @BSKObservElement_id bigint,
                    @BSKObject_id bigint,
                    @pmUser_id bigint,
    				@ErrCode int,
    				@ErrMessage varchar(4000);
                            
                if (select 
                		count(BSKObservElementLink_id) 
                	from r2.BSKObservElementLink with(nolock)
                	where BSKObservElement_id = :BSKObservElement_id and BSKObject_id = :BSKObject_id) > 0
                	begin

                		exec r2.p_BSKObservElementLink_upd
                			@BSKObservElementLink_deleted = @BSKObservElementLink_deleted,
                            @BSKObservElement_id = :BSKObservElement_id,
                            @BSKObject_id = :BSKObject_id,
                			@pmUser_id = :pmUser_id,
                			@Error_Code = @ErrCode output,
                			@Error_Message = @ErrMessage output;
                		select @ErrCode as Error_Code, @ErrMessage as Error_Msg;                                       
                        
                        
                	end
                else	
        
            		exec r2.p_BSKObservElementLinkAdd_ins
                        @BSKObservElement_id = :BSKObservElement_id,
                        @BSKObject_id = :BSKObject_id,
                        @BSKObservElementLink_deleted = @BSKObservElementLink_deleted,
            			@pmUser_id = :pmUser_id,
            			@Error_Code = @ErrCode output,
            			@Error_Message = @ErrMessage output;
            		select @ErrCode as Error_Code, @ErrMessage as Error_Msg;                                 
           ";
            /*
            --update r2.BSKObservElementLink set BSKObservElementLink_deleted = 1 where BSKObservElement_id = :BSKObservElement_id and BSKObject_id = :BSKObject_id
                  
            insert into r2.BSKObservElementLink 
            (
             BSKObservElement_id,
             BSKObject_id,
             pmUser_insID,
             pmUser_updID,
             BSKObservElementLink_insDT,
             BSKObservElementLink_updDT,
             BSKObservElementLink_deleted
            )           
            values
            (
             :BSKObservElement_id,
             :BSKObject_id,
             :pmUser_id,
             null,
             getdate(),
             null,
             1
            )
           
            */
           
        } elseif ($data['action'] == 'unset') {
            $query = "
            	declare
    				@BSKObservElementLink_deleted bigint = 2,
                    @BSKObservElement_id bigint,
                    @BSKObject_id bigint,
                    @pmUser_id bigint,
    				@ErrCode int,
    				@ErrMessage varchar(4000);            
            
                if (select 
                		count(BSKObservElementLink_id) 
                	from r2.BSKObservElementLink with(nolock)
                	where BSKObservElement_id = :BSKObservElement_id and BSKObject_id = :BSKObject_id) > 0
                	begin

                		exec r2.p_BSKObservElementLink_upd
                			@BSKObservElementLink_deleted = @BSKObservElementLink_deleted,
                            @BSKObservElement_id = :BSKObservElement_id,
                            @BSKObject_id = :BSKObject_id,
                			@pmUser_id = :pmUser_id,
                			@Error_Code = @ErrCode output,
                			@Error_Message = @ErrMessage output;
                		select @ErrCode as Error_Code, @ErrMessage as Error_Msg;  
                                         	
                    end
                else	
        
            		exec r2.p_BSKObservElementLinkAdd_ins
                        @BSKObservElement_id = :BSKObservElement_id,
                        @BSKObject_id = :BSKObject_id,
                        @BSKObservElementLink_deleted = 2,
            			@pmUser_id = :pmUser_id,
            			@Error_Code = @ErrCode output,
            			@Error_Message = @ErrMessage output;
            		select @ErrCode as Error_Code, @ErrMessage as Error_Msg;                         
           ";
        }
           /*
           --update r2.BSKObservElementLink set BSKObservElementLink_deleted = 2 where BSKObservElement_id = :BSKObservElement_id and BSKObject_id = :BSKObject_id
                  
            insert into r2.BSKObservElementLink 
            (
             BSKObservElement_id,
             BSKObject_id,
             pmUser_insID,
             pmUser_updID,
             BSKObservElementLink_insDT,
             BSKObservElementLink_updDT,
             BSKObservElementLink_deleted
            )           
            values
            (
             :BSKObservElement_id,
             :BSKObject_id,
             :pmUser_id,
             null,
             getdate(),
             null,
             2
            )
           
           */        
        //echo getDebugSql($query, $params);
        
        //exit;
        
        
        $result = $this->db->query($query, $params);
        
        if (is_object($result)) {
            return $result->result('array');
        } else {
            return false;
        }
    }
    /**
     *  Получить все предметы наблюдения
     */
    function getListObjects($data)
    {
        $params = array(
            'BSKObject_id' => empty($data['BSKObject_id']) ? ' ' : $data['BSKObject_id']
        );
        
        $query = "
            select 
               O.BSKObject_id,
               M.MorbusType_id,
               M.MorbusType_name
            from r2.BSKObject O with(nolock)
            left join dbo.MorbusType M with(nolock) on M.MorbusType_id = O.MorbusType_id   
            where M.MorbusType_id in(84,88,89, 50) and  O.BSKObject_deleted = 1
            and  O.BSKObject_id != :BSKObject_id
        ";
        
        //echo getDebugSql($query, $params);
        
        //exit; 
        
        
        $result = $this->db->query($query, $params);
        
        if (is_object($result)) {
            return $result->result('array');
        } else {
            return false;
        }
    }
    
    /**
     *  Получить все Группы сведений предмета наблюдения
     */
    function getListGroupTypes($data)
    {
        $params = array(
            'BSKObject_id' => $data['BSKObject_id']
        );
        //select * from r2.BSKObservElementGroup with(nolock)
        $query  = "
            select 
              BSKObservElementGroup_id , 
              BSKObservElementGroup_name    
            from 
              {$this->scheme}.BSKObservElementGroup
            where
              BSKObject_id = :BSKObject_id
              and
              BSKObservElementGroup_deleted != 2  
        ";
        
        
        
        $result = $this->db->query($query, $params);
        
        if (is_object($result)) {
            return $result->result('array');
        } else {
            return false;
        }
    }
    
    /**
     *  Получить все сведения предмета наблюдения
     */
    function getListTypes($data)
    {
        $params = array(
            'BSKObservElementGroup_id' => $data['BSKObservElementGroup_id'],
            'BSKObject_id' => $data['BSKObject_id']
        );
        
        $query = "
            select 
            
                E.BSKObservElement_id,
                E.BSKObservElement_name,
                E.BSKObservElement_stage,
                E.BSKObservElement_symbol,
                E.BSKObservElement_formula,
                E.BSKObservElement_Sex_id,
                E.BSKObservElement_minAge,
                E.BSKObservElement_maxAge,
                E.BSKObservElement_Anketa,
                (select 
    				BSKObservElementFormat_name 
    			 from r2.BSKObservElementFormat with(nolock)
    			 where BSKObservElementFormat_id = E.BSKObservElementFormat_id) as BSKObservElementFormat_name
                
            from {$this->scheme}.BSKObservElement E
            left join {$this->scheme}.BSKObservElementGroup EG on E.BSKObservElementGroup_id = EG.BSKObservElementGroup_id
            left join {$this->scheme}.BSKObservElementLink EL on EL.BSKObservElement_id = E.BSKObservElement_id 
            where E.BSKObservElement_deleted = 1 
            and EG.BSKObservElementGroup_deleted = 1
            and EL.BSKObject_id = :BSKObject_id
            and EG.BSKObservElementGroup_id = :BSKObservElementGroup_id
            and EL.BSKObservElementLink_deleted = 1                     
        ";
        
        
        //echo getDebugSql($query, $params);
        
        //exit;
        
        $result = $this->db->query($query, $params);
        
        if (is_object($result)) {
            return $result->result('array');
        } else {
            return false;
        }
    }
    
    /**
     *  Получить все значения сведений
     */
    function getListValues($data)
    {
        $params = array(
            'BSKObservElement_id' => $data['BSKObservElement_id']
        );
        
        $query = "
           select 
              BSKObservElementValues_id,
              BSKObservElementValues_min,
              BSKObservElementValues_max,
              BSKObservElementValues_points,
              BSKObservElementValues_sign,
              BSKObservElementValues_data
           from
             {$this->scheme}.BSKObservElementValues  
           where
              BSKObservElement_id = :BSKObservElement_id  
              and
              BSKObservElementValues_deleted = 1            
        ";
        
        $result = $this->db->query($query, $params);
        
        if (is_object($result)) {
            return $result->result('array');
        } else {
            return false;
        }
    }
    
    /**
     *  Добавление нового предмета наблюдения
     *  НЕ ИСПОЛЬЗУЕТСЯ
     */
    function addObject($data)
    {
        return;
        
        $params = array(
            'BSKObject_deleted' => 1,
            'BSKObject_name' => $data['BSKObject_name'],
            'pmUser_insID' => $data['pmUser_id'],
            'MorbusType_id' => $data['MorbusType_id']
        );
        
        $query = "
            insert into {$this->scheme}.BSKObject
            (
             BSKObject_deleted,
             BSKObject_name,
             MorbusType_id,
             pmUser_insID,
             pmUser_updID,
             BSKObject_insDT,
             BSKObject_updDT
            )
            values
            (
             :BSKObject_deleted,
             :BSKObject_name,
             :MorbusType_id,
             :pmUser_insID,
             null,
             getdate(),
             null
            )
         ";
        
        $result = $this->db->query($query, $params);
        
        if (is_object($result)) {
            return $result->result('array');
        } else {
            return false;
        }
        
    }
    
    /**
     *  Добавление группы сведений
     */
    function addGroupType($data)
    {
        $params = array(
            'BSKObject_id' => $data['BSKObject_id'],
            'BSKObservElementGroup_name' => $data['BSKObservElementGroup_name'],
            'pmUser_id' => $data['pmUser_id']
        );
        /*
        $query = "
          insert into r2.BSKObservElementGroup 
          (
           BSKObservElementGroup_name,
           BSKObject_id,
           pmUser_insID,
           pmUser_updID,
           BSKObservElementGroup_insDT,
           BSKObservElementGroup_updDT,
           BSKObservElementGroup_deleted
          )
          values
          (
           :BSKObservElementGroup_name,
           :BSKObject_id,
           :pmUser_id,
           null,
           getdate(),
           null,
           1
          )
        ";
        */
		$query = "
			declare
				@BSKObservElementGroup_name varchar(100),
				@BSKObject_id bigint,
                @pmUser_id bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);

			exec r2.p_BSKObservElementGroup_ins
				@BSKObservElementGroup_name = :BSKObservElementGroup_name,
				@BSKObject_id = :BSKObject_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
        
        $result = $this->db->query($query, $params);
        
        if (is_object($result)) {
            return $result->result('array');
        } else {
            return false;
        }
    }
    
    /**
     *  Добавление нового типа сведений предмета наблюдения
     */ 
    function addType($data)
    {
        
        if ($data['BSKObservElement_stage'] != 1) {
            $data['BSKObservElement_Anketa'] = null;
        }
        /*
        $query = "
            insert into r2.BSKObservElement 
            select 
              :BSKObservElement_name as BSKObservElement_name,
              :BSKObservElement_stage as BSKObservElement_stage,
              :BSKObservElement_symbol as BSKObservElement_symbol,
              :BSKObservElement_formula as BSKObservElement_formula,
              :BSKObservElementGroup_id as BSKObservElementGroup_id,
              (
               select top 1 BSKObservElementFormat_id 
               from r2.BSKObservElementFormat with(nolock)
               where BSKObservElementFormat_name = :BSKObservElementFormat_name
              ) as BSKObservElementFormat_id,   
              :BSKObservElement_IsRequire as BSKObservElement_IsRequire,
              :BSKObservElement_Sex_id as BSKObservElement_Sex_id,
              :BSKObservElement_minAge as BSKObservElement_minAge,
              :BSKObservElement_maxAge as BSKObservElement_maxAge,
              :pmUser_insID as pmUser_insID,
              null as pmUser_updID,
              getdate() as BSKObservElement_insDT,
              null as BSKObservElement_updDT,
              1 as BSKObservElement_deleted,
              :BSKObservElement_Anketa as BSKObservElement_Anketa;
              
              insert into r2.BSKObservElementLink 
              (
                BSKObservElement_id,
                BSKObject_id,
                pmUser_insID,
                pmUser_updID,
                BSKObservElementLink_insDT, 
                BSKObservElementLink_updDT,
                BSKObservElementLink_deleted              
              )
              values
              (
                (SELECT BSKObservElement_id AS LastID FROM r2.BSKObservElement with(nolock) WHERE BSKObservElement_id = @@Identity),
                :BSKObject_id,
                :pmUser_insID,
                null,
                getdate(),
                null,
                1
              );
                 
        ";
        */
                
        $params = array(
            'BSKObject_id' => $data['BSKObject_id'],
            'BSKObservElementGroup_id' => $data['BSKObservElementGroup_id'],
            'BSKObservElement_name' => $data['BSKObservElement_name'],
            'BSKObservElementFormat_name' => $data['formatText'],
            'BSKObservElement_symbol' => $data['BSKObservElement_symbol'],
            'BSKObservElement_formula' => $data['BSKObservElement_formula'],
            'BSKObservElement_stage' => $data['BSKObservElement_stage'],
            'BSKObservElement_Sex_id' => $data['BSKObservElement_Sex_id'],
            'BSKObservElement_minAge' => $data['BSKObservElement_minAge'],
            'BSKObservElement_maxAge' => $data['BSKObservElement_maxAge'],
            'BSKObservElement_IsRequire' => $data['BSKObservElement_IsRequire'],
            'pmUser_id' => $data['pmUser_id'],
            'BSKObservElement_Anketa' => ($data['BSKObservElement_Anketa'] == '') ? null : $data['BSKObservElement_Anketa']
        );

		$query = "
			declare
                @BSKObservElement_name varchar(200),
                @BSKObservElement_stage bigint,
                @BSKObservElement_symbol varchar(10),
                @BSKObservElement_formula varchar(20),
				@BSKObservElementGroup_id bigint,
                @BSKObservElementFormat_name varchar(50),
                @BSKObservElement_Sex_id bigint,
                @BSKObservElement_minAge bigint,
                @BSKObservElement_maxAge bigint,
                @pmUser_id bigint,
                @BSKObservElement_IsRequire bigint,
                @BSKObservElement_Anketa varchar(500),

				@ErrCode int,
				@ErrMessage varchar(4000);

			exec r2.p_BSKObservElement_ins
				@BSKObservElement_name = :BSKObservElement_name,
                @BSKObservElement_stage = :BSKObservElement_stage,
                @BSKObservElement_symbol = :BSKObservElement_symbol,
                @BSKObservElement_formula = :BSKObservElement_formula,
                @BSKObservElementGroup_id = :BSKObservElementGroup_id,
                @BSKObservElementFormat_name = :BSKObservElementFormat_name,
                @BSKObservElement_Sex_id = :BSKObservElement_Sex_id,
                @BSKObservElement_minAge = :BSKObservElement_minAge,
                @BSKObservElement_maxAge = :BSKObservElement_maxAge,
				@pmUser_id = :pmUser_id,
                @BSKObservElement_IsRequire = :BSKObservElement_IsRequire,
                @BSKObservElement_Anketa = :BSKObservElement_Anketa,
                
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
            
            declare
                @BSKObject_id bigint
            
			exec r2.p_BSKObservElementLink_ins
				@BSKObject_id = :BSKObject_id,
				@pmUser_id = :pmUser_id,
                
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;            
		";        
        //echo getDebugSql($query, $params);        
        //exit;
        
        $result = $this->db->query($query, $params);
        
        if (is_object($result)) {
            return $result->result('array');
        } else {
            return false;
        }
        
    }
    
    /**
     *  Добавление значения для сведения
     */
    function addValue($data)
    {
        $params = array(
            'BSKObservElementValues_data' => $data['BSKObservElementValues_data'],
            'BSKObservElement_id' => $data['BSKObservElement_id'],
            'BSKObservElementValues_min' => $data['BSKObservElementValues_min'],
            'BSKObservElementValues_max' => $data['BSKObservElementValues_max'],
            'BSKObservElementValues_points' => $data['BSKObservElementValues_points'],
            'BSKObservElementValues_sign' => $data['BSKObservElementValues_sign'],
            'pmUser_id' => $data['pmUser_id']
        );
        /*
        $query = "
            insert into r2.BSKObservElementValues
            (
              BSKObservElement_id,
              BSKObservElementValues_min,
              BSKObservElementValues_max, 
              BSKObservElementValues_points,
              BSKObservElementValues_sign,
              pmUser_insID,
              pmUser_updID,
              BSKObservElementValues_insDT,
              BSKObservElementValues_updDT,
              BSKObservElementValues_deleted,  
              BSKObservElementValues_data
             )
             values
             (
                :BSKObservElement_id,
                :BSKObservElementValues_min,
                :BSKObservElementValues_max,
                :BSKObservElementValues_points,
                :BSKObservElementValues_sign,
                :pmUser_insID,
                null,
                getdate(),
                null,
                1,
                :BSKObservElementValues_data
            )    
        ";
        */
        $query = "
			declare
                @BSKObservElement_id bigint,
                @BSKObservElementValues_data varchar(300),
                @BSKObservElementValues_min bigint,
                @BSKObservElementValues_max bigint,
                @BSKObservElementValues_points bigint,
                @BSKObservElementValues_sign varchar(10),
                @pmUser_id bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);

			exec r2.p_BSKObservElementValues_ins
                @BSKObservElement_id = :BSKObservElement_id,
                @BSKObservElementValues_data = :BSKObservElementValues_data,
                @BSKObservElementValues_min = :BSKObservElementValues_min,
                @BSKObservElementValues_max = :BSKObservElementValues_max,
                @BSKObservElementValues_points = :BSKObservElementValues_points,
                @BSKObservElementValues_sign = :BSKObservElementValues_sign,
				@pmUser_id = :pmUser_id,
                
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;        
        ";
        
        //echo '<pre>' . print_r($params, 1) . '</pre>';
        //exit;        
        
        $result = $this->db->query($query, $params);
        
        if (is_object($result)) {
            return $result->result('array');
        } else {
            return false;
        }
    }
    
    /**
     *  Редактирование предмета наблюдения
     *  НЕ ИСПОЛЬЗУЕТСЯ
     */
    function editObject($data)
    {
        return;
        $params = array(
            'MorbusType_name' => $data['nameObject'],
            'MorbusType_id' => $data['MorbusType_id'],
            'BSKObject' => $data['object_id'],
            'pmUser_updID' => $data['pmUser_id']
        );
        

        
        $query = "
            update dbo.MorbusType 
            set 
                MorbusType_name =:MorbusType_name, 
                MorbusType_updDT = getdate(),
                pmUser_updID = :pmUser_updID
            where MorbusType_id =:MorbusType_id;
            
            update {$this->scheme}.BSKObject 
            set 
               BSKObject_updDT = getdate(),
               pmUser_updID = :pmUser_updID
            where BSKObject_id = :BSKObject  
        ";
        
        // echo getDebugSql($query, $params);
        
        //exit;
        
        $result = $this->db->query($query, $params);
        
        if (is_object($result)) {
            return $result->result('array');
        } else {
            return false;
        }
    }
    
    /**
     *  Редактирование группы сведений
     */
    function editGroupType($data)
    {
        $params = array(
            'BSKObservElementGroup_name' => $data['BSKObservElementGroup_name'],
            'BSKObservElementGroup_id' => $data['BSKObservElementGroup_id'],
            'pmUser_id' => $data['pmUser_id']
        );
        /*        
        $query = "
            update r2.BSKObservElementGroup 
            set 
                BSKObservElementGroup_name = :BSKObservElementGroup_name,
                BSKObservElementGroup_updDT = getdate(),
                pmUser_updID = :pmUser_updID
                 
            where BSKObservElementGroup_id = :BSKObservElementGroup_id
        ";
        */
		$query = "
			declare
				@BSKObservElementGroup_name varchar(100),
				@BSKObservElementGroup_id bigint,
                @pmUser_id bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);

			exec r2.p_BSKObservElementGroup_upd
				@BSKObservElementGroup_name = :BSKObservElementGroup_name,
				@BSKObservElementGroup_id = :BSKObservElementGroup_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

        //echo getDebugSql($query, $params);
        //exit;
                
        $result = $this->db->query($query, $params);
        
        
        if (is_object($result)) {
            return $result->result('array');
        } else {
            return false;
        }
    }
    
    /**
     *  Редактирование типа сведений
     */
    function editType($data)
    {
        $params = array(
            'BSKObservElement_id' => $data['BSKObservElement_id'],
            'BSKObservElement_name' => $data['BSKObservElement_name'],
            'BSKObservElementFormat_name' => $data['formatText'],
            'BSKObservElement_symbol' => $data['BSKObservElement_symbol'],
            'BSKObservElement_formula' => $data['BSKObservElement_formula'],
            'BSKObservElement_stage' => $data['BSKObservElement_stage'],
            'BSKObservElement_Sex_id' => $data['BSKObservElement_Sex_id'],
            'BSKObservElement_minAge' => $data['BSKObservElement_minAge'],
            'BSKObservElement_maxAge' => $data['BSKObservElement_maxAge'],
            'BSKObservElement_IsRequire' => $data['BSKObservElement_IsRequire'],
            'pmUser_id' => $data['pmUser_id'],
            'BSKObservElement_Anketa' => isset($data['BSKObservElement_Anketa']) ? $data['BSKObservElement_Anketa'] : null
        );
        /*
        $textfieldValue = 'formula (автоматический расчёт)';
        
        $query = " 
            update r2.BSKObservElement
            set
                BSKObservElement_name = :BSKObservElement_name,
                BSKObservElementFormat_id = (select BSKObservElementFormat_id from {$this->scheme}.BSKObservElementFormat where BSKObservElementFormat_name = :BSKObservElementFormat_name),
                pmUser_updID = :pmUser_updID,
                BSKObservElement_updDT = getdate(),
                BSKObservElement_symbol = :BSKObservElement_symbol,
                BSKObservElement_formula = case when :BSKObservElementFormat_name = '{$textfieldValue}' then :BSKObservElement_formula else null end,
                BSKObservElement_stage = :BSKObservElement_stage,
                BSKObservElement_Sex_id = :BSKObservElement_Sex_id,
                BSKObservElement_minAge = :BSKObservElement_minAge,
                BSKObservElement_maxAge = :BSKObservElement_maxAge,
                BSKObservElement_IsRequire = :BSKObservElement_IsRequire,
                BSKObservElement_Anketa = :BSKObservElement_Anketa    
            where BSKObservElement_id = :BSKObservElement_id
        ";  
        */
		$query = "
			declare
                @BSKObservElement_id bigint,
                @BSKObservElement_name varchar(200),
                @BSKObservElementFormat_name varchar(50),
                @BSKObservElement_symbol varchar(10),
                @BSKObservElement_formula varchar(20),
                @BSKObservElement_stage bigint,
                @BSKObservElement_Sex_id bigint,
                @BSKObservElement_minAge bigint,
                @BSKObservElement_maxAge bigint,
                @BSKObservElement_IsRequire bigint,
                @BSKObservElement_Anketa varchar(500),
                @pmUser_id bigint,

				@ErrCode int,
				@ErrMessage varchar(4000);

			exec r2.p_BSKObservElement_upd
				@BSKObservElement_id = :BSKObservElement_id,
                @BSKObservElement_name = :BSKObservElement_name,
                @BSKObservElementFormat_name = :BSKObservElementFormat_name,
                @BSKObservElement_symbol = :BSKObservElement_symbol,
                @BSKObservElement_formula = :BSKObservElement_formula,
                @BSKObservElement_stage = :BSKObservElement_stage,
                @BSKObservElement_Sex_id = :BSKObservElement_Sex_id,
                @BSKObservElement_minAge = :BSKObservElement_minAge,
                @BSKObservElement_maxAge = :BSKObservElement_maxAge,
                @BSKObservElement_IsRequire = :BSKObservElement_IsRequire,
                @BSKObservElement_Anketa = :BSKObservElement_Anketa,
				@pmUser_id = :pmUser_id,                
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;         
		";        
        
        //echo getDebugSql($query, $params);
        //exit;
        $result = $this->db->query($query, $params);
        
        if (is_object($result)) {
            return $result->result('array');
        } else {
            return false;
        }
    }
    
    /**
     *  Редактирование значения типа сведений  
     */
    function editValue($data)
    {
        $params = array(
            'BSKObservElementValues_data' => $data['BSKObservElementValues_data'],
            'BSKObservElementValues_id' => $data['BSKObservElementValues_id'],
            'BSKObservElementValues_min' => $data['BSKObservElementValues_min'],
            'BSKObservElementValues_max' => $data['BSKObservElementValues_max'],
            'BSKObservElementValues_points' => $data['BSKObservElementValues_points'],
            'BSKObservElementValues_sign' => $data['BSKObservElementValues_sign'],
            'pmUser_id' => $data['pmUser_id']
        );
        /*
        $query = "
            update {$this->scheme}.BSKObservElementValues set 
                BSKObservElementValues_data = :BSKObservElementValues_data,
                BSKObservElementValues_min = :BSKObservElementValues_min,
                BSKObservElementValues_max = :BSKObservElementValues_max,
                BSKObservElementValues_points = :BSKObservElementValues_points,
                BSKObservElementValues_sign = :BSKObservElementValues_sign,
                pmUser_updID = :pmUser_updID,
                BSKObservElementValues_updDT = getDate()
            where BSKObservElementValues_id = :BSKObservElementValues_id   
        ";
        */
        $query = "
			declare
                @BSKObservElementValues_id bigint,
                @BSKObservElementValues_data varchar(300),
                @BSKObservElementValues_min bigint,
                @BSKObservElementValues_max bigint,
                @BSKObservElementValues_points bigint,
                @BSKObservElementValues_sign varchar(10),
                @pmUser_id bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);

			exec r2.p_BSKObservElementValues_upd
                @BSKObservElementValues_id = :BSKObservElementValues_id,
                @BSKObservElementValues_data = :BSKObservElementValues_data,
                @BSKObservElementValues_min = :BSKObservElementValues_min,
                @BSKObservElementValues_max = :BSKObservElementValues_max,
                @BSKObservElementValues_points = :BSKObservElementValues_points,
                @BSKObservElementValues_sign = :BSKObservElementValues_sign,
				@pmUser_id = :pmUser_id,
                
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;        
        ";        
        //echo getDebugSql($query, $params);
        
        $result = $this->db->query($query, $params);
        
        if (is_object($result)) {
            return $result->result('array');
        } else {
            return false;
        }
    }
    
    /**
     *  Удаление предмета наблюдения
     *  НЕ ИСПОЛЬЗУЕТСЯ
     */
    function deleteObject($object_name, $data)
    {
        return;
        
        $params = array(
            'BSK_GenObjObsn_id' => $data['object_id'],
            'pmUser_updID' => $data['pmUser_id']
        );
        
        $query = "
             update {$this->scheme}.BSK_GenObjObsn set 
                 BSK_genObjObsn_isActive = 1,
                 BSK_genObjObsn_updDT = GETDATE(),
                 pmUser_updID = :pmUser_updID
             where BSK_genObjObsn_id = :BSK_GenObjObsn_id;
         ";
        
        $result = $this->db->query($query, $params);
        
        //echo getDebugSql($query, $params);
        
        if (is_object($result)) {
            return $result->result('array');
        } else {
            return false;
        }
    }
    
    /**
     *  Удаление группы сведений
     */
    function deleteGroupType($data)
    {
        $params = array(
            'BSKObservElementGroup_id' => $data['BSKObservElementGroup_id'],
            'pmUser_id' => $data['pmUser_id']
        );
        /*
        $query = "
             update r2.BSKObservElementGroup set 
                 BSKObservElementGroup_deleted = 2,
                 pmUser_updID = :pmUser_id,
                 BSKObservElementGroup_updDT = getdate()  
             where BSKObservElementGroup_id = :BSKObservElementGroup_id;
         ";
        */
		$query = "
			declare
				@BSKObservElementGroup_id bigint,
                @pmUser_id bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);

			exec r2.p_BSKObservElementGroup_del
				@BSKObservElementGroup_id = :BSKObservElementGroup_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
                
        $result = $this->db->query($query, $params);
        
        //echo getDebugSql($query, $params);
        
        if (is_object($result)) {
            return $result->result('array');
        } else {
            return false;
        }
    }
    
    /**
     *  Удаление типа сведений
     */
    function deleteType($data)
    {
        $params = array(
            'BSKObservElement_id' => $data['BSKObservElement_id'],
            'pmUser_id' => $data['pmUser_id']
        );
        /*
        $query = "
             update r2.BSKObservElement set 
                BSKObservElement_deleted = 2,
                pmUser_updID = :pmUser_updID,
                BSKObservElement_updDT = getdate()  
             where BSKObservElement_id = :BSKObservElement_id;
         ";
        */
		$query = "
			declare
				@BSKObservElement_id bigint,
                @pmUser_id bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);

			exec r2.p_BSKObservElement_del
				@BSKObservElement_id = :BSKObservElement_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";        
        $result = $this->db->query($query, $params);
        
        //echo getDebugSql($query, $params);
        
        if (is_object($result)) {
            return $result->result('array');
        } else {
            return false;
        }
    }
    
    /**
     *  Удаление значения типа сведений
     */
    function deleteValue($data)
    {
        $params = array(
            'BSKObservElementValues_id' => $data['BSKObservElementValues_id'],
            'pmUser_id' => $data['pmUser_id']
        );
        /*
        $query = "
             update r2.BSKObservElementValues set 
                 BSKObservElementValues_deleted = 2,
                 BSKObservElementValues_updDT = GETDATE(),
                 pmUser_updID = :pmUser_updID
             where BSKObservElementValues_id = :BSKObservElementValues_id;
         ";
        */
        $query = "
			declare
                @BSKObservElementValues_id bigint,
                @pmUser_id bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);

			exec r2.p_BSKObservElementValues_del
                @BSKObservElementValues_id = :BSKObservElementValues_id,
				@pmUser_id = :pmUser_id,
                
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;        
        ";              
        
        $result = $this->db->query($query, $params);
        
        //echo getDebugSql($query, $params);
        
        if (is_object($result)) {
            return $result->result('array');
        } else {
            return false;
        }
    }
    
    /**
     * Вернёт древо предметов наблюдения + сведения + свойства
     */
    function getTreeObjects()
    {
        $params = array();
        
        $query = "  
                    /** 
                    (select 
                     BSK_GenObjObsn_id,
                     BSK_GenObjObsn_name,
                     null as BSKGroups_TypesInfo_id,
                     null as BSKGroups_TypesInfo_name  
                    from r2.BSK_GenObjObsn with(nolock)
                    where BSK_GenObjObsn_isActive = 2)
                    
                    union all
                    
                    (select 
                     
                     BSK_GenObjObsn_id, 
                     null as BSK_GenObjObsn_name,
                     BSKGroups_TypesInfo_id,
                     BSKGroups_TypesInfo_name
                    from r2.BSKGroups_TypesInfo with(nolock)
                    where BSKGroups_TypesInfo_isActive = 2)
                   */
                   
                    select  
                     o.BSK_GenObjObsn_name as ObjectName
                     ,g.BSKGroups_TypesInfo_name as GroupType
                     ,ti.BSK_TypesInfo_name as TypeName
                     ,f.BSK_TypesInfo_Format_name as format
                     ,u.BSK_units_name as unit
                     ,ti.BSK_TypesInfo_formula as formula
                     ,ti.BSK_TypesInfo_symbol as symbol
                     ,v.BSK_TypesInfoValues_data as Data
                     ,v.BSK_TypesInfoValues_min as [min]
                     ,v.BSK_TypesInfoValues_max as [max]
                    from r2.BSK_GenObjObsn o with(nolock)
                    left outer  join r2.BSKGroups_TypesInfo g with(nolock) on g.BSK_GenObjObsn_id = o.BSK_genObjObsn_id and g.BSKGroups_TypesInfo_isActive = 2
                    left outer join r2.BSK_TypesInfo ti with(nolock) on ti.BSKGroups_TypesInfo_id = g.BSKGroups_TypesInfo_id and ti.BSK_TypesInfo_isActive = 2
                    left outer join r2.BSK_TypesInfoValues v with(nolock) on v.BSK_TypesInfo_id = ti.BSK_TypesInfo_id and v.BSK_TypesInfoValues_isActive = 2
                    left join r2.BSK_TypesInfo_Format f with(nolock) on ti.BSK_TypesInfo_Format_id = f.BSK_TypesInfo_Format_id 
                    left join r2.BSK_units u with(nolock) on ti.BSK_units_id = u.BSK_units_id
                    where o.BSK_genObjObsn_isActive = 2                    
         ";
        
        $result = $this->db->query($query, $params);
        
        //echo getDebugSql($query, $params);
        
        if (is_object($result)) {
            return $result->result('array');
        } else {
            return false;
        }
    }
    
    /**
     *  Получение списка возможных вариантов типов сведений
     */
    function getTypesFormat()
    {
        $params = array();
        
        $query = "
           select 
              BSKObservElementFormat_id,
              BSKObservElementFormat_name
           from
             {$this->scheme}.BSKObservElementFormat  
        ";
        
        $result = $this->db->query($query, $params);
        
        //echo getDebugSql($query, $params);
        
        if (is_object($result)) {
            return $result->result('array');
        } else {
            return false;
        }
    }
    
    /**
     *  Получение списка возможных вариантов единиц измерения
     */
    function getUnits()
    {
        $params = array();
        
        $query = "
           select 
              unit_id as BSKUnits_id,
              unit_Name as BSKUnits_name
           from
             lis.Unit with(nolock)
        ";
        
        $result = $this->db->query($query, $params);
        
        $emptyUnit = array(
            array(
                'BSKUnits_id' => 0,
                'BSKUnits_name' => 'Без единицы измерения'
            )
        );
        
        if (is_object($result)) {
            $units = array_merge($emptyUnit, $result->result('array'));
            return $units;
        } else {
            return false;
        }
    }
    
    
    /**
     *  Получение формата и ед. измерения для конкретного типа сведений
     */ 
    function getFormatAndUnit($data)
    {
        $params = array(
            'BSK_TypesInfo_id' => $data['Type_id']
        );
        
        $query = "
            select 
            tf.BSK_TypesInfo_Format_name as format,
            tf.BSK_TypesInfo_min as minvalue,
            tf.BSK_TypesInfo_max as maxvalue,
            u.BSK_units_name as unit
            from r2.BSK_TypesInfo ti with(nolock)
            left join r2.BSK_TypesInfo_Format tf with(nolock) on tf.BSK_TypesInfo_Format_id  = ti.BSK_TypesInfo_Format_id
            left join r2.BSK_units u with(nolock) on u.BSK_units_id = ti.BSK_units_id
            where ti.BSK_TypesInfo_id = :BSK_TypesInfo_id         
         ";
        
        $result = $this->db->query($query, $params);
        //echo '<pre>' . print_r($arr, 1) . '</pre>';
        //echo getDebugSql($query, $params);
        
        if (is_object($result)) {
            return $result->result('array');
        } else {
            return false;
        }
    }
    
    /**
     * Проверка условного обозначения для формулы
     */  
    function checkSymbol($data)
    {
        $params = array(
            'symbol' => $data['symbol'],
            'BSKObservElement_id' => $data['BSKObservElement_id']
        );
        
        $query = "
            select 
                BSKObservElement_symbol 
            from r2.BSKObservElement with(nolock)
            where BSKObservElement_symbol = :symbol
            and BSKObservElement_id != :BSKObservElement_id
         ";
        
        $result = $this->db->query($query, $params);
        
        //echo getDebugSql($query, $params);
        
        if (is_object($result)) {
            return $result->result('array');
        } else {
            return false;
        }
    }
    
    /**
     *  Добавление единицы измерения
     */
    function addUnit($data)
    {
        return;
        $params = array(
            'BSK_units_name' => $data['nameUnit'],
            'pmUser_insID' => $data['pmUser_id']
        );
        
        $query = "
            insert into {$this->scheme}.BSK_units
            (
              BSK_units_name,
              pmUser_insID,
              pmUser_updID,
              BSK_units_insDT,
              BSK_units_updDT,
              BSK_units_isActive
             )
             values
             (
                :BSK_units_name,
                :pmUser_insID,
                null,
                getdate(),
                null,
                2
            )    
        ";
        
        //echo getDebugSql($query, $params);
        
        $result = $this->db->query($query, $params);
        
        if (is_object($result)) {
            return $result->result('array');
        } else {
            return false;
        }
    }
    
    /**
     * Редактирование единицы измерения
     */
    function editUnit($data)
    {
        return;
        $params = array(
            'BSK_units_id' => $data['unit_id'],
            'BSK_units_name' => $data['editUnit'],
            'pmUser_updID' => $data['pmUser_id']
        );
        
        $query = "
            update {$this->scheme}.BSK_units set
                BSK_units_name =:BSK_units_name,
                pmUser_updID =:pmUser_updID,
                BSK_units_updDT = getdate()
           where
                BSK_units_id = :BSK_units_id         
        ";
        
        //echo getDebugSql($query, $params);
        
        $result = $this->db->query($query, $params);
        
        if (is_object($result)) {
            return $result->result('array');
        } else {
            return false;
        }
    }
    
    /**
     * Проверка используемости единицы измерения (перед удалением)
     */
    function checkUnitBeforeDelete($data)
    {
        $params = array(
            'BSK_units_id' => $data['unit_id']
        );
        
        $query = "
            select 
                BSK_units_id
            from  
                {$this->scheme}.BSK_TypesInfo
            where
                BSK_units_id = :BSK_units_id   
                and
                BSK_TypesInfo_isActive = 2    
        ";
        
        //echo getDebugSql($query, $params);
        
        $result = $this->db->query($query, $params);
        
        if (is_object($result)) {
            return $result->result('array');
        } else {
            return false;
        }
    }
    
    /**
     * Удаление единицы измерения
     */
    function deleteUnit($data)
    {
        return;
        $params = array(
            'BSK_units_id' => $data['unit_id'],
            'pmUser_updID' => $data['pmUser_id']
        );
        
        $query = "
            update {$this->scheme}.BSK_units set
                BSK_units_isActive = 1,
                pmUser_updID =:pmUser_updID,
                BSK_units_updDT = getdate()
           where
                BSK_units_id = :BSK_units_id         
        ";
        
        //echo getDebugSql($query, $params);
        
        $result = $this->db->query($query, $params);
        
        if (is_object($result)) {
            return $result->result('array');
        } else {
            return false;
        }
    }
}
?> 