<?php


class StudyTarget_model extends swPgModel
{
	/**
	 *    Конструктор
	 */
	function __construct()
	{
		parent::__construct();
	}

	/**
	 *    Загрузка комбо ресурсов
	 */
	function loadStudyTargetList()
	{
		$query = "
			SELECT 
                StudyTarget_id as \"StudyTarget_id\",
                StudyTarget_Code as \"StudyTarget_Code\",
                StudyTarget_Name as \"StudyTarget_Name\",
                pmUser_insID as \"pmUser_insID\",
                pmUser_updID as \"pmUser_updID\",
                StudyTarget_insDT as \"StudyTarget_insDT\",
                StudyTarget_updDT as \"StudyTarget_updDT\"
			FROM v_StudyTarget
		";

		return $this->queryResult($query);
	}
}