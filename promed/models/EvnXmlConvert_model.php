<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 *
 * @package		PromedWeb
 * @access		public
 * @copyright	Copyright (c) 2013 Swan Ltd.
 * @link		http://swan.perm.ru/PromedWeb
 * @version		06.2013
 */

/**
 * Модель с методами для конвертации Xml-документов
 *
 * @package		XmlTemplate
 * @author		Александр Пермяков
 */
class EvnXmlConvert_model extends swModel
{
	/**
	 * Дата, когда был реализован тип шаблона документов с множеством разделов
	 * и редактируемым шаблоном отображения (swXmlTemplate::OLD_TYPE_ID)
	 * @var string
	 */
	private $_date_beg_first_multy_part = '2011-03-01';

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();
		//$this->load->library('swEvnXml');
	}

	/**
	 * Получает сводную информацию об имещихся ошибках или о записях,
	 * которые нуждаются в исправлении
	 * @return array Стандартный ответ модели
	 * @throws Exception
	 */
	public function index()
	{
		$this->load->library('swXmlTemplate');
		$sql = "
			Declare 
				@XmlTemplate_notCorrectDeletedCnt as int = 0, 
				@XmlTemplate_withUndefinedTypeCnt as int = 0, 
				@XmlTemplate_allowAutoConvertCnt as int = 0, 
				@EvnXml_withoutTemplateDataCnt as int = 0, 
				@EvnXml_withUndefinedTemplateTypeCnt as int = 0, 
				@EvnXml_withUndefinedTypeCnt as int = 0
			select 
				@XmlTemplate_notCorrectDeletedCnt = SUM(case when XmlTemplate_IsDeleted = 2 and XmlTemplateCat_id is not null then 1 else 0 end),
				@XmlTemplate_withUndefinedTypeCnt = SUM(case when XmlTemplateType_id is null and ISNULL(XmlTemplate_IsDeleted,1) = 1 then 1 else 0 end),
				@XmlTemplate_allowAutoConvertCnt = SUM(case when XmlTemplateType_id = :From_XmlTemplateType_id
					and ISNULL(XmlTemplate_IsDeleted,1) = 1
					and XmlTemplate_insDT > CAST(:date_beg_first_multy_part as datetime)
					and XmlTemplate_HtmlTemplate is not null then 1 else 0 end)
			from v_XmlTemplate with (nolock)
			select 
				@EvnXml_withoutTemplateDataCnt = SUM(case when XmlTemplate_Data is null then 1 else 0 end),
				@EvnXml_withUndefinedTemplateTypeCnt = SUM(case when XmlTemplateType_id is null then 1 else 0 end),
				@EvnXml_withUndefinedTypeCnt = SUM(case when XmlType_id is null then 1 else 0 end)
				from v_EvnXml doc with (nolock)
				-- исключаем документы, у которых учетный документ удален
				inner join v_Evn evn with (nolock) on doc.Evn_id = evn.Evn_id

			Select @XmlTemplate_notCorrectDeletedCnt as XmlTemplate_notCorrectDeletedCnt, 
				@XmlTemplate_withUndefinedTypeCnt as XmlTemplate_withUndefinedTypeCnt, 
				@XmlTemplate_allowAutoConvertCnt as XmlTemplate_allowAutoConvertCnt, 
				@EvnXml_withoutTemplateDataCnt as EvnXml_withoutTemplateDataCnt, 
				@EvnXml_withUndefinedTemplateTypeCnt as EvnXml_withUndefinedTemplateTypeCnt, 
				@EvnXml_withUndefinedTypeCnt as EvnXml_withUndefinedTypeCnt
		";
		$params = array(
			'From_XmlTemplateType_id' => swXmlTemplate::OLD_TYPE_ID,
			'date_beg_first_multy_part' => $this->_date_beg_first_multy_part,
		);
		$result = $this->db->query($sql, $params);
		if ( !is_object($result) ) {
			throw new Exception('Ошибка запроса сводной информации');
		}
		return $result->result('array');
	}

	/**
	 * Автоматическая конвертация шаблонов в новый формат
	 * Это возможно только для части шаблонов, остальные надо
	 * конвертировать вручную
	 *
	 * @return boolean
	 */
	public function autoConvertXmlTemplate()
	{
		throw new Exception('Метод нуждается в доработке в связи с изменением схемы хранения');
		$this->load->library('swXmlTemplate');
		$sql = '
			UPDATE XmlTemplate SET
				XmlTemplateType_id = :To_XmlTemplateType_id
			WHERE
				XmlTemplateType_id = :From_XmlTemplateType_id
				and ISNULL(XmlTemplate_IsDeleted,1) = 1
				and XmlTemplate_insDT > CAST(:date_beg_first_multy_part as datetime)
				and XmlTemplate_HtmlTemplate is not null
		';
		$params = array(
			'To_XmlTemplateType_id' => swXmlTemplate::MULTIPLE_PART_TYPE_ID,
			'From_XmlTemplateType_id' => swXmlTemplate::OLD_TYPE_ID,
			'date_beg_first_multy_part' => $this->_date_beg_first_multy_part,
		);
		return $this->db->query($sql, $params);
	}

	/**
	 * Исправляет некорректное удаление шаблонов,
	 * когда при удалении шаблона осталась ссылка на папку
	 *
	 * @return boolean
	 */
	public function fixXmlTemplateNotCorrectDeleted()
	{
		$sql = '
			UPDATE XmlTemplate SET
				XmlTemplateCat_id = null,
				XmlTemplate_patch = null
			WHERE XmlTemplate_IsDeleted = 2 and XmlTemplateCat_id is not null
		';
		return $this->db->query($sql);
	}

	/**
	 * Для шаблонов, у которых не указан тип,
	 * пытаемся определить и записать тип шаблона
	 *
	 * За один запрос может быть обработано только 20 кривых шаблонов
	 *
	 * @return boolean
	 * @throws Exception
	 */
	public function fixXmlTemplateWithUndefinedType()
	{
		throw new Exception('Метод нуждается в доработке в связи с изменением схемы хранения');
		$sql = "
			select top 20
				XmlTemplate_id
				,XmlTemplate_Data
				,XmlTemplate_HtmlTemplate
				,UslugaComplex_id
				,XmlTemplateType_id
			from v_XmlTemplate with (nolock)
			where XmlTemplateType_id is null
		";
		$result = $this->db->query($sql);
		if ( !is_object($result) ) {
			throw new Exception('Ошибка запроса');
		}
		$response = $result->result('array');
		$this->load->library('swXmlTemplate');
		$definedTypes = array();
		foreach ($response as $row) {
			$type = swXmlTemplate::defineType($row);
			if (empty($definedTypes[$type])) {
				$definedTypes[$type] = array();
			}
			$definedTypes[$type][] = $row['XmlTemplate_id'];
		}
		unset($response);
		//write XmlTemplateType_id
		$sql_t = '
			UPDATE XmlTemplate SET
				XmlTemplateType_id = ?
			WHERE XmlTemplate_id in ({templates})
		';
		$success = true;
		foreach ($definedTypes as $type => $templates) {
			$sql = strtr($sql_t,array('{templates}'=>implode(', ', $templates)));
			$result = $this->db->query($sql, array($type));
			if (!$result) {
				return false;
			}
		}
		return $success;
	}

	/**
	 * Определить и записать тип шаблона у документов
	 *
	 * За один запрос может быть обработано только 100 документов
	 *
	 * @return boolean
	 * @throws Exception
	 */
	public function fixEvnXmlWithUndefinedXmlTemplateType()
	{
		set_time_limit(0);
		for ($i = 1; $i <= 10; $i++) {
			$sql = "
				select top 100
					doc.EvnXml_id
					,t.XmlTemplateType_id
				from v_EvnXml doc with (nolock)
				inner join v_XmlTemplate t with (nolock) on doc.XmlTemplate_id = t.XmlTemplate_id
				where doc.XmlTemplateType_id is null and t.XmlTemplateType_id is not null
			";
			$result = $this->db->query($sql);
			if ( !is_object($result) ) {
				throw new Exception('Ошибка запроса');
			}
			$response = $result->result('array');
			$definedTypes = array();
			foreach ($response as $row) {
				$type = $row['XmlTemplateType_id'];
				if (empty($definedTypes[$type])) {
					$definedTypes[$type] = array();
				}
				$definedTypes[$type][] = $row['EvnXml_id'];
			}
			unset($response);
			//write XmlTemplateType_id
			$sql_t = '
				UPDATE EvnXml SET
					XmlTemplateType_id = ?
				WHERE EvnXml_id in ({docs})
			';
			$success = true;
			foreach ($definedTypes as $type => $docs) {
				$sql = strtr($sql_t,array('{docs}'=>implode(', ', $docs)));
				$result = $this->db->query($sql, array($type));
				if (!$result) {
					return false;
				}
			}
		}
		return $success;
	}

	/**
	 * Определить и записать тип документов
	 *
	 * За один запрос может быть обработано только 100 документов
	 *
	 * @return boolean
	 * @throws Exception
	 */
	public function fixXmlType()
	{
		$sql = "
			select top 100
				doc.EvnXml_id
				,doc.XmlTemplateType_id
				,evn.EvnClass_id
			from v_EvnXml doc with (nolock)
			inner join Evn evn with (nolock) on doc.Evn_id = evn.Evn_id
			where doc.XmlType_id is null
		";
		$result = $this->db->query($sql);
		if ( !is_object($result) ) {
			throw new Exception('Ошибка запроса');
		}
		$response = $result->result('array');
		$definedTypes = array();
		$this->load->library('swEvnXml');
		$this->load->library('swXmlTemplate');
		foreach ($response as $row) {
			$type = null;
			switch (true) {
				case (in_array($row['EvnClass_id'], array(22,47)) || swXmlTemplate::OLD_EVN_USLUGA_TYPE_ID == $row['XmlTemplateType_id']):
					$type = swEvnXml::EVN_USLUGA_PROTOCOL_TYPE_ID;
					break;
				case (in_array($row['EvnClass_id'], array(11, 13, 36)) && in_array($row['XmlTemplateType_id'], array(
					swXmlTemplate::OLD_TYPE_ID,
					swXmlTemplate::OLD_MULTIPLE_PART_TYPE_ID,
					swXmlTemplate::OLD_SIMPLE_TYPE_ID,
					swXmlTemplate::MULTIPLE_PART_TYPE_ID,
				))):
					$type = swEvnXml::EVN_VIZIT_PROTOCOL_TYPE_ID;
					break;
				case (swXmlTemplate::OLD_FREE_TYPE_ID == $row['XmlTemplateType_id']):
					$type = swEvnXml::MULTIPLE_DOCUMENT_TYPE_ID;
					break;
				default:
					break;
			}

			if (empty($type)) {
				continue;
			}

			if (empty($definedTypes[$type])) {
				$definedTypes[$type] = array();
			}
			$definedTypes[$type][] = $row['EvnXml_id'];
		}
		unset($response);
		//write XmlType_id
		$sql_t = '
			UPDATE EvnXml SET
				XmlType_id = ?
			WHERE EvnXml_id in ({docs})
		';
		$success = true;
		foreach ($definedTypes as $type => $docs) {
			$sql = strtr($sql_t,array('{docs}'=>implode(', ', $docs)));
			$result = $this->db->query($sql, array($type));
			if (!$result) {
				return false;
			}
		}
		return $success;
	}

	/**
	 * Скопировать атрибуты шаблона в документ
	 *
	 * За один запрос может быть обработано только 20 документов
	 *
	 * @return boolean
	 * @throws Exception
	 */
	public function copyXmlTemplateDataToEvnXml()
	{
		throw new Exception('Метод нуждается в доработке в связи с изменением схемы хранения');
		$sql = "
			select top 20
				doc.EvnXml_id
				,s.XmlSchema_Data
				,t.XmlTemplate_Data
				,t.XmlTemplate_HtmlTemplate
				,t.XmlTemplate_Settings
				,t.XmlTemplateType_id
			from v_EvnXml doc with (nolock)
			inner join v_XmlTemplate t with (nolock) on doc.XmlTemplate_id = t.XmlTemplate_id
			inner join v_XmlSchema s with (nolock) on t.XmlSchema_id = s.XmlSchema_id
			where doc.XmlTemplate_Data is null
		";
		$result = $this->db->query($sql);
		if ( !is_object($result) ) {
			throw new Exception('Ошибка запроса');
		}
		$response = $result->result('array');
		$sql_t = '
			UPDATE EvnXml SET
				XmlSchema_Data = :XmlSchema_Data,
				XmlTemplate_Data = :XmlTemplate_Data,
				XmlTemplate_HtmlTemplate = :XmlTemplate_HtmlTemplate,
				XmlTemplate_Settings = :XmlTemplate_Settings,
				XmlTemplateType_id = :XmlTemplateType_id
			WHERE EvnXml_id = :EvnXml_id
		';
		$success = true;
		foreach ($response as $docs) {
			$result = $this->db->query($sql_t, $docs);
			if (!$result) {
				return false;
			}
		}
		return $success;
	}
}
