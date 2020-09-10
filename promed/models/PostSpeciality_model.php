<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PostSpeciality_model - модель для работы cо стыковочной таблицей должностей и специальностей
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Common
 * @access			public
 * @copyright		Copyright (c) 2018 Swan Ltd.
 */

class PostSpeciality_model extends swModel
{
	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();
	}

	/**
	 *Получение списка соответствий
	 */
	function loadPostSpecialityList($data)
	{
		$filter = '';
		if (isset($data['Post_Name'])) {
			$filter .= "
			and p.name like case when :Post_Name is not null then '%'+:Post_Name+'%' else '' end";
		}
		if (isset($data['Speciality_Name'])) {
			$filter .= "
			and spec.name like case when :Speciality_Name is not null then '%'+:Speciality_Name+'%' else '' end";
		}
		$query = "
			select
				--select
				ps.PostSpeciality_id,
				ps.Post_id,
				p.name as Post_Name,
				ps.Speciality_id,
				spec.name as Speciality_Name
				--end select
			from
				--from
				persis.PostSpeciality ps with(nolock)
				left join persis.Post p on ps.Post_id = p.id
				left join persis.Speciality spec on ps.Speciality_id = spec.id
				--end from
			where
				--where
				ps.Speciality_id is not null 
				and ps.Post_id is not null"
			. $filter .
				"--end where	
			order by
				--order by
				ps.PostSpeciality_id
				--end order by
		";

		return $this->getPagingResponse($query, $data, $data['start'], $data['limit'], true);
	}

	/**
	 * Получение списка специальностей
	 */
	function loadSpecialityList()
	{
		/*$query = "
			SELECT
				id,
				name
			FROM persis.v_Speciality
			";*/
		$query = "
			SELECT
				s.id,
				s.name,
				--s.name+'. '+CAST(fp.id AS VARCHAR(5))+' ('+fp.fullname+')' AS fname,
				fp.id AS code,
				fp.fullname
			FROM persis.Speciality s WITH (nolock)
				INNER JOIN persis.FRMPSertificateSpeciality fp WITH (nolock) ON s.frmpEntry_id=fp.id";
		return $this->queryResult($query);
	}

	/**
	 * Сохранение нового соответствия
	 */
	function savePostSpecialityPair($data)
	{
		$query = "
			declare
				@PostSpeciality_id bigint,
				@Error_Code int = null,
				@Error_Message varchar(4000) = null
	
			set nocount on
			
			begin try
				insert into persis.PostSpeciality with(rowlock) (
					Post_id
					,Speciality_id
					,pmUser_insID
					,pmUser_updID
					,PostSpeciality_insDT
					,PostSpeciality_updDT
				) values (
					:Post_id
					,:Speciality_id
					,:pmUser_id
					,:pmUser_id
					,dbo.tzGetDate()
					,dbo.tzGetDate()
				)
			
				set @PostSpeciality_id = (select scope_identity())
			end try
			
			begin catch
				set @Error_Code = error_number()
				set @Error_Message = error_message()
			end catch
			
			set nocount off
			select @PostSpeciality_id as PostSpeciality_id, @Error_Code as Error_Code, @Error_Message as Error_Msg
		";

		$result = $this->queryResult($query, array(
			'Post_id' => $data['Post_id'],
			'Speciality_id' => $data['Speciality_id'],
			'pmUser_id' => $data['pmUser_id']
		));

		return $result;
	}

	/**
	 * Удаление соответствия
	 */
	function deletePostSpecialityPair($data)
	{
		$query = "
		declare
			@Error_Code int = null,
			@Error_Message varchar(4000) = null
		
		set nocount on
		
		begin try
			delete from persis.PostSpeciality with(rowlock) where PostSpeciality_id = :PostSpeciality_id
		end try
		
		begin catch
			set @Error_Code = error_number()
			set @Error_Message = error_message()
		end catch
		
		set nocount off
		select @Error_Code as Error_Code, @Error_Message as Error_Msg
		";

		$result = $this->queryResult($query, array(
			'PostSpeciality_id' => $data['PostSpeciality_id']
		));

		return $result;
	}

	/**
	 * Редактирование соответствия
	 */
	function editPostSpecialityPair($data)
	{
		$query = "
		declare
			@Error_Code int = null,
			@Error_Message varchar(4000) = null

		set nocount on
		
		begin try
			update
				persis.PostSpeciality with(rowlock)
			set
				Post_id = :Post_id,
				Speciality_id = :Speciality_id,
				pmUser_updID = :pmUser_id,
				PostSpeciality_updDT = dbo.tzGetDate()
			where
				PostSpeciality_id = :PostSpeciality_id
		end try
		
		begin catch
			set @Error_Code = error_number()
			set @Error_Message = error_message()
		end catch
		
		set nocount off
		select @Error_Code as Error_Code, @Error_Message as Error_Msg
		";

		$result = $this->queryResult($query, array(
			'Post_id' => $data['Post_id'],
			'Speciality_id' => $data['Speciality_id'],
			'PostSpeciality_id' => $data['PostSpeciality_id'],
			'pmUser_id' => $data['pmUser_id']
		));

		return $result;
	}
}
