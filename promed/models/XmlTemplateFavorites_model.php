<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 *
 * @package		PromedWeb
 * @access		public
 * @copyright	Copyright (c) 2013 Swan Ltd.
 * @link		http://swan.perm.ru/PromedWeb
 * @version		13.05.2013
 */

/**
 * Модель избранных шаблонов.
 *
 * @package		XmlTemplate
 * @author		Александр Пермяков
 */
class XmlTemplateFavorites_model extends swModel
{
	function __construct()
	{
		parent::__construct();
	}

	/**
	* Получение списка наиболее часто используемых шаблонов
	*/
	public function loadList($data)
	{
		$sql = "
  			select top 10
				xtf.XmlTemplateFavorites_id,
				xtf.XmlTemplateFavorites_CountLoad,
				xtf.XmlTemplate_id,
				xt.XmlTemplate_Caption
			from
				XmlTemplateFavorites xtf with (nolock)
				inner join XmlTemplate xt with (nolock) on xt.XmlTemplate_id = xtf.XmlTemplate_id AND xt.XmlTemplate_IsDeleted != 2 AND xt.EvnClass_id = :EvnClass_id AND xtf.pmUser_insID = :pmUser_id
			ORDER BY
				xtf.XmlTemplateFavorites_CountLoad DESC
		";
		$result = $this->db->query($sql, array(
			'pmUser_id' => $data['pmUser_id'],
			'EvnClass_id' => $data['EvnClass_id'],
		));
		if ( is_object($result) )
			return $result->result('array');
		else
			return false;
			//return array(array('Error_Msg'=>'Ошибка БД, не удалось получить список часто используемых шаблонов.'));
	}

}
