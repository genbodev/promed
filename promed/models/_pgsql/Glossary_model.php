<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* Glossary_model - модель глоссария
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2009-2011 Swan Ltd.
* @author       Пермяков Александр
* @version      июнь 2011 года
*/

class Glossary_model extends swPgModel {
	var $scheme = "dbo";

	/**
	 * Glossary_model constructor.
	 */
	function __construct()
	{
		parent::__construct();
	}

	/**
	* Читает данные синонимов для меню
	*/
	function loadSynonymMenu($data)
	{
		$filter = '';
		if ($data['isEnablePersGlossary'] && $data['isEnableBaseGlossary'])
		{
			$filter = ' and (gl.pmUser_did is null or gl.pmUser_did = :pmUser_id)';
		}
		else if ($data['isEnableBaseGlossary'])
		{
			$filter = ' and gl.pmUser_did is null';
		}
		else if ($data['isEnablePersGlossary'])
		{
			$filter = ' and gl.pmUser_did = :pmUser_id';
		}
		else
		{
			return array();
		}
		$synonym_list = explode(',',$data['Synonym_list']);
		$res = array();
		foreach($synonym_list as $para){
			$s = explode('-',$para);
			$data['Glossary_id'] = $s[0];
			$data['GlossarySynonym_id'] = $s[1];
			$query = "
				Select
					gl.Glossary_id as \"Glossary_id\",
					gl.GlossaryTagType_id as \"GlossaryTagType_id\",
					gl.GlossarySynonym_id as \"GlossarySynonym_id\",
					coalesce(GlossaryTagType.GlossaryTagType_SysNick,'') as \"GlossaryTagType_SysNick\",
					coalesce(gl.Glossary_Descr,'') as \"Glossary_Descr\",
					gl.Glossary_Word as \"Glossary_Word\",
					gl.pmUser_did as \"pmUser_did\"
				from
					v_Glossary gl
					left join GlossaryTagType on gl.GlossaryTagType_id = GlossaryTagType.GlossaryTagType_id
				where
					gl.GlossarySynonym_id = :GlossarySynonym_id
					and gl.Glossary_id != :Glossary_id
					{$filter} 
				order by
					gl.Glossary_Word
			";

			//echo getDebugSql($query, $data);exit;

			$result = $this->db->query($query, $data);
			if ( is_object($result) )
			{
				$response = $result->result('array');
				$res[$data['Glossary_id']] = $response;
			}
			else
			{
				return false;
			}
		}
		//var_dump($res); exit;
		return $res;
	}

	/**
	* Метод получения записи GlossaryTagType
	*/
	function getGlossaryTagTypeBySysNick($data)
	{
		$query = "
			SELECT
				GlossaryTagType_id as \"GlossaryTagType_id\",
				GlossaryTagType_Code as \"GlossaryTagType_Code\",
				GlossaryTagType_Name as \"GlossaryTagType_Name\",
				GlossaryTagType_SysNick as \"GlossaryTagType_SysNick\"
			FROM
				GlossaryTagType
			where
				GlossaryTagType_SysNick = :GlossaryTagType_SysNick
			limit 1
		";

		$result = $this->db->query($query, $data);

		if ( is_object($result) )
		{
			return $result->result('array');
		}
		else
		{
			return false;
		}
	}

	/**
	* Читает данные (не больше 15) для меню
	*/
	function loadRecordStore($data)
	{
		$params = array(
			'query' => $data['text'].'%',
			'pmUser_id' => $data['pmUser_id'],
			'GlossaryTagType_SysNick' => $data['GlossaryTagType_SysNick']
		);
		$filter = '';
		if ($data['isEnablePersGlossary'] && $data['isEnableBaseGlossary'])
		{
			$filter = ' and (gl.pmUser_did is null or gl.pmUser_did = :pmUser_id)';
		}
		else if ($data['isEnableBaseGlossary'])
		{
			$filter = ' and gl.pmUser_did is null';
		}
		else if ($data['isEnablePersGlossary'])
		{
			$filter = ' and gl.pmUser_did = :pmUser_id';
		}
		else
		{
			return array();
		}
		if($data['GlossaryTagType_SysNick'] && $data['isEnableContextSearch'])
		{
			$filter .= ' and (gl.GlossaryTagType_id is null or GlossaryTagType.GlossaryTagType_SysNick = :GlossaryTagType_SysNick)';
		}
		$query = "
			Select
				gl.Glossary_id as \"Glossary_id\",
				gl.GlossaryTagType_id as \"GlossaryTagType_id\",
				gl.GlossarySynonym_id as \"GlossarySynonym_id\",
				coalesce(GlossaryTagType.GlossaryTagType_SysNick,'') as \"GlossaryTagType_SysNick\",
				coalesce(gl.Glossary_Descr,'') as \"Glossary_Descr\",
				gl.Glossary_Word as \"Glossary_Word\", 
				gl.pmUser_did as \"pmUser_did\"
			from
				v_Glossary gl
				left join GlossaryTagType on gl.GlossaryTagType_id = GlossaryTagType.GlossaryTagType_id
			where
				gl.Glossary_Word ilike (:query)
				{$filter} 
			order by
				gl.Glossary_Word
			limit 15
		";

		//echo getDebugSql($query, $params);exit;

		$result = $this->db->query($query, $params);

		if ( is_object($result) )
		{
			$response = $result->result('array');
			return $response;
		}
		else
		{
			return false;
		}
	}

	/**
	* Возвращает массив с данными, если термин уже имеется в базовом или личном словаре пользователя с учетом привязки к тэгу
	*/
	function checkDouble($data)
	{
		// проверяем при добавлении и редактировании
		$params = array(
			'Glossary_Word' => $data['Glossary_Word'],
			'pmUser_id' => $data['pmUser_id']
		);
		$f = '';
		if (!empty($data['Glossary_id']))
		{
			$f = ' and Glossary_id != :Glossary_id';
			$params['Glossary_id'] = $data['Glossary_id'];
		}
		if (!empty($data['GlossaryTagType_id']))
		{
			// Если же термин из словаря привязан к конкретному тегу, а пользователь хочет привязать его к другому – то можно разрешить сохранение
			$f .= ' and (GlossaryTagType_id is null or GlossaryTagType_id = :GlossaryTagType_id)';
			$params['GlossaryTagType_id'] = $data['GlossaryTagType_id'];
		}
		else
		{
			// если в словаре есть такой же термин и без привязки к тегу, то нужно запретить сохранение дубля.
			// и разрешаем сохранение дубликата без привязки к тэгу, если в словаре есть термин с привязкой к тэгу
			$f .= ' and GlossaryTagType_id is null';
		}
		$query = "
			Select
				Glossary_id as \"Glossary_id\",
				GlossarySynonym_id as \"GlossarySynonym_id\",
				pmUser_did as \"pmUser_did\"
			from
				v_Glossary
			where
				Glossary_Word ilike :Glossary_Word
				and (pmUser_did is null or pmUser_did = :pmUser_id)
				{$f}
		";
		//echo getDebugSql($query, $params);exit;
		$result = $this->db->query($query, $params);
		if ( is_object($result) )
		{
			return $result->result('array');
		}
		else
		{
			return false;
		}
	}

	/**
	* Возвращает истину, если пользователю разрешено редактирование базового глоссария
	*/
	function allowBaseEdit($data)
	{
		return ( $data['session']['login'] == 'admin' || isSuperadmin() );
	}
	
	/**
	*  Читает часть данных (используя пейджинг)
	*/
	function loadRecordGrid($data)
	{
		if (!(($data['start'] >= 0) && ($data['limit'] >= 0)))
		{
			return false;
		}

		$is_admin = $this->allowBaseEdit($data);
		$params = array(
			'Glossary_Word' => $data['Glossary_Word'],
			'query' => '%'.$data['Glossary_Word'].'%',
			'pmUser_id' => $data['pmUser_id'],
			'GlossaryTagType_id' => $data['GlossaryTagType_id'],
			'GlossarySynonym_id' => $data['GlossarySynonym_id']
		);
		
		/*
		Aдмин видит только базовый глоссарий, а юзер свой и/или базовый.
		Eсли нужно будет админу просмотреть и редактировать личный словарь какого-то пользователя,
		достаточно будет передать параметр $data['pmUser_did']
		*/
		if ($is_admin)
		{
			if(empty($data['pmUser_did']))
			{
				$user_filter = 'gl.pmUser_did is null';
			}
			else
			{
				$params['pmUser_id'] = $data['pmUser_did'];
				$user_filter = 'gl.pmUser_did = :pmUser_id';
			}
		}
		else
		{
			if(empty($data['GlossaryType_id']))
			{
				// свой и базовый
				$user_filter = '(gl.pmUser_did is null OR gl.pmUser_did = :pmUser_id)';
			}
			else if(1 == $data['GlossaryType_id'])
			{
				// базовый
				$user_filter = 'gl.pmUser_did is null';
			}
			else
			{
				$user_filter = 'gl.pmUser_did = :pmUser_id';
			}
		}
		
		$query = "
			Select
				-- select
				gl.Glossary_id as \"Glossary_id\",
				gl.GlossarySynonym_id as \"GlossarySynonym_id\",
				gl.Glossary_Word as \"Glossary_Word\",
				gl.GlossaryTagType_id as \"GlossaryTagType_id\",
				gtt.GlossaryTagType_Name as \"GlossaryTagType_Name\",
				case when gl.pmUser_did is not null then 'true' else 'false' end as \"Glossary_IsPers\",
				gl.pmUser_did as \"pmUser_did\"
				-- end select
			from
				-- from
				v_Glossary gl
				left join v_GlossaryTagType gtt on gtt.GlossaryTagType_id = gl.GlossaryTagType_id
			-- end from
			where
				-- where
				(:Glossary_Word is null or gl.Glossary_Word ilike (:query)) and 
				(:GlossaryTagType_id is null or gl.GlossaryTagType_id = :GlossaryTagType_id) and 
				(:GlossarySynonym_id is null or gl.GlossarySynonym_id = :GlossarySynonym_id) and
				{$user_filter}
				-- end where
			order by
				-- order by
				gl.Glossary_Word
				-- end order by
		";
		/*
		echo getDebugSql(getLimitSQLPH($query, $data['start'], $data['limit']), $params);
		exit;
		*/
		return $this->getPagingResponse($query, $params, $data['start'], $data['limit'], true);
	}
	
	/**
	* Читает данные (не больше 50) для комбо
	*/
	function getSynonymList($data)
	{
		$is_admin = $this->allowBaseEdit($data);
		$params = array(
			//Синонимы берутся как из базового словаря, так и из личного (для пользователя)
			'pmUser_did' => (!$is_admin)?$data['pmUser_id']:$data['pmUser_did'],
			'Glossary_Word' => $data['GlossarySynonym_Name'],
			'query' => $data['GlossarySynonym_Name'].'%',
			'GlossaryTagType_id' => $data['GlossaryTagType_id'],
			'Glossary_id' => $data['Glossary_id'],
			'GlossarySynonym_id' => $data['GlossarySynonym_id']
		);
		$query = "
			Select
				gl.GlossarySynonym_id as \"GlossarySynonym_id\",
				gl.Glossary_Word as \"GlossarySynonym_Name\",
				gl.GlossaryTagType_id as \"GlossaryTagType_id\",
				gl.pmUser_did as \"pmUser_did\"
			from
				v_Glossary gl
			where
				(:Glossary_Word is null or gl.Glossary_Word ilike (:query)) and 
				(:Glossary_id is null or gl.Glossary_id = :Glossary_id) and 
				(:GlossarySynonym_id is null or gl.GlossarySynonym_id = :GlossarySynonym_id) and 
				(:GlossaryTagType_id is null or gl.GlossaryTagType_id = :GlossaryTagType_id) and 
				(gl.pmUser_did is null or (:pmUser_did is not null and gl.pmUser_did = :pmUser_did)) 
			order by
				gl.Glossary_Word
			limit 50
		";

		//echo getDebugSql($query, $params);exit;

		$result = $this->db->query($query, $params);

		if ( is_object($result) )
		{
			$response = $result->result('array');
			return $response;
		}
		else
		{
			return false;
		}
	}

	/**
	*  Читает одну строку для формы редактирования
	*/
	function getRecord($data)
	{
		$params = array(
			'Glossary_id' => $data['Glossary_id']
		);
		$query = "
			Select
				gl.Glossary_id as \"Glossary_id\",
				gl.Glossary_Word as \"Glossary_Word\",
				gl.Glossary_Descr as \"Glossary_Descr\",
				gl.GlossarySynonym_id as \"GlossarySynonym_id\",
				gl.GlossaryTagType_id as \"GlossaryTagType_id\",
				gl.pmUser_did as \"pmUser_did\"
			from
				v_Glossary gl
			where
				gl.Glossary_id = :Glossary_id
			limit 1
		";
		$result = $this->db->query($query, $params);
		if ( is_object($result) )
		{
			return $result->result('array');
		}
		else
		{
			return false;
		}
	}
	
	/**
	*  Записывает одну строку
	*/
	function saveRecord($data)
	{
		if ($data['Glossary_id'] > 0)
		{
			$proc = 'p_Glossary_upd';
		}
		else
		{
			$proc = 'p_Glossary_ins';
			$data['Glossary_id'] = null;
		}

		$is_admin = $this->allowBaseEdit($data);
		$params = array
		(
			'Glossary_id' => $data['Glossary_id'],
			'GlossarySynonym_id' => $data['GlossarySynonym_id'],
			'Glossary_Word' => $data['Glossary_Word'],
			'Glossary_Descr' => $data['Glossary_Descr'],
			'GlossaryTagType_id' => $data['GlossaryTagType_id'],
			'pmUser_did' => (!$is_admin && empty($data['pmUser_did']))?$data['pmUser_id']:$data['pmUser_did'],
			'pmUser_id' => $data['pmUser_id']
		);
		$query = "
			select
				Glossary_id as \"Glossary_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from {$proc} (
				Glossary_id := :Glossary_id,
				GlossarySynonym_id := :GlossarySynonym_id,
				Glossary_Word := :Glossary_Word,
				Glossary_Descr := :Glossary_Descr,
				GlossaryTagType_id := :GlossaryTagType_id,
				pmUser_did := :pmUser_did,
				pmUser_id := :pmUser_id
				)
		";

		//echo getDebugSQL($query, $params);exit;
		$result = $this->db->query($query, $params);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}

	}
}