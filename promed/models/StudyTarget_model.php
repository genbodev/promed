<?php


class StudyTarget_model extends swModel
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
			SELECT *
			FROM v_StudyTarget (nolock)
		";

		return $this->queryResult($query);
	}
}