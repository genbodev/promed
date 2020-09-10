<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 *
 * @package		Library
 * @access		private
 * @copyright	Copyright (c) 2009-2011 Swan Ltd.
 * @link		http://swan.perm.ru/PromedWeb
 * @version		22.11.2010
 */

/**
 * Фильтрация ответа модели после получения данных моделью и до того, как модель вернет ответ контроллеру
 *
 * @package		Library
 * @author		Alexander Permyakov
 */
class SwFilterResponse{
	
	/**
	 * @var EPH_model
	 */
	static $filter_model = null;
	
	/**
	 * Список диагнозов, документы с которыми должны быть недоступны для просмотра врачом согласно его должности
	 * @var array
	 */
	private static $_list_not_view_diag = null;
	
	/**
	 * Список ЛПУ с особым статусом
	 * @var array
	 */
	private static $_list_vip_lpu = null;

	/**
	 * Параметры сеанса пользователя
	 * @var array
	 */
	private static $_session_params = null;

	/**
	 * Текущее место работы врача, пришедшее как параметр user_MedStaffFact_id
	 * @var array
	 */
	private static $_user_MedStaffFact_id = null;

	function __construct() {
		$CI =& get_instance();
		$CI->load->model('EPH_model', 'EPH_model');
		self::$filter_model = $CI->EPH_model;
	}
	
	/**
	 * Реализация видимости документов
	 *
	 * Фильтры для ответа модели 
	 * 1) По ГУЗам - для реализации видимости документов происходит фильтрация 
	 * по списку диагнозов, недоступных для просмотра врачом согласно его должности
	 * Для фильтрации необходимо, чтобы модель возвращала Diag_id и Lpu_id
	 * В POST должен быть параметр user_MedStaffFact_id или MedStaffFact_id в сессии
	 * 2) По особому статусу ЛПУ #18448 - для реализации видимости документов происходит фильтрация 
	 * по признаку особого статуса ЛПУ (Lpu_IsSecret).
	 * Все события и документы созданные в ЛПУ с особым статусом должны быть видимы в ЭМК только для пользователей этой ЛПУ.
	 *
	 * Реализовано тут, чтобы не надо было писать фильтры для каждого запроса,
	 * которых может много и, которые могут выполняться за одно обращение к серверу.
	 *
	 * Используется в методах моделей, где это необходимо
	 *
	 * @param array $response_arr Ответ модели
	 * @param array $data Параметры запроса к модели
	 * @return array $response_arr Ответ модели, обработанный фильтрами
	 * @example
	 * if ( is_object($res) )
	 * 	return swFilterResponse::filterNotViewDiag($res->result('array'), $data);
	 * else
	 * 	return false;
	*/
	public static function filterNotViewDiag($response_arr, $data)
	{
		self::$_user_MedStaffFact_id = (empty($data['user_MedStaffFact_id'])) ? null : $data['user_MedStaffFact_id'];
		try {
			self::_checkLoad();
		} catch (Exception $e) {
				//@todo лучше писать в лог
				//$val = array( 'success' => false, 'Error_Msg' => toUtf($e->getMessage()) );
				//echo json_encode($val);
				return false;
				//return $response_arr;
		}

		foreach($response_arr as $i => $row)
		{
			if (
				isset($row['Lpu_id'])
				AND (
					( isset($row['Diag_id']) AND in_array($row['Diag_id'],self::$_list_not_view_diag) )
					OR in_array($row['Lpu_id'],self::$_list_vip_lpu)
				)
				AND $row['Lpu_id'] != self::$_session_params['Lpu_id']
			)
			{
				// не даем увидеть то, что не должен видеть
				unset($response_arr[$i]);
			}
		}
		$response_arr = array_values($response_arr);
		//var_dump($response_arr); exit;
		return $response_arr;
	}// END filterNotViewDiag 

	/**
	 * Загрузка списка диагнозов, документы с которыми должны быть недоступны для просмотра врачом согласно его должности
	 * @throws Exception
	 * @todo возможна ситауция, когда рабочее место не указано,
	 * тогда пользователь будет видеть все диагнозы
	 */
	private static function _loadListNotViewDiag()
	{
		self::$_list_not_view_diag = array();
		$params = array(
			'MedStaffFact_id' => self::$_session_params['MedStaffFact_id']
		);
		if (!empty(self::$_user_MedStaffFact_id)) {
			$params['MedStaffFact_id'] = self::$_user_MedStaffFact_id;
		}

		$needFilterByMedService = true;
		if (!empty(self::$_session_params['session']['CurMedService_id'])) {
			// получаем тип службы
			$query = "
				select top 1
					mst.MedServiceType_SysNick
				from
					v_MedService ms (nolock)
					inner join v_MedServiceType mst (nolock) on mst.MedServiceType_id = ms.MedServiceType_id
				where
					ms.MedService_id = :MedService_id
			";
			if (self::$_session_params['session']['DBTYPE_ENV'] == 'pgsql') {
				$query = "select
					mst.MedServiceType_SysNick
				from
					v_MedService ms
					inner join v_MedServiceType mst on mst.MedServiceType_id = ms.MedServiceType_id
				where
					ms.MedService_id = :MedService_id
				LIMIT 1
				";
			}
			
			$MedServiceType_SysNick = self::$filter_model->getFirstResultFromQuery($query,
			array(
				'MedService_id' => self::$_session_params['session']['CurMedService_id']
			));

			if (!empty($MedServiceType_SysNick) && in_array($MedServiceType_SysNick, array('vk'))) { // службы для которых не нужна фильтрация
				$needFilterByMedService = false;
			}
		}

		if($params['MedStaffFact_id'] > 0 || $needFilterByMedService) // Если задан медстаффакт или сотрудник не определенной службы
		{
			self::$_list_not_view_diag = self::$filter_model->loadListNotViewDiag($params);
		}
	}

	/**
	 * Загрузка списка ЛПУ с особым статусом
	 * @throws Exception
	 */
	private static function _loadListVipLpu()
	{
		self::$_list_vip_lpu = array();
		$res_arr = self::$filter_model->loadListVipLpu();
		foreach($res_arr as $row)
		{
			self::$_list_vip_lpu[] = $row['Lpu_id'];
		}
	}

	/**
	 * Загрузка параметров сеанса пользователя
	 * @throws Exception
	 */
	private static function _loadSessionParams()
	{
		self::$_session_params = getSessionParams();
	}

	
	/**
	 * Проверка, что все данные для фильтров загружены
	 * @throws Exception
	 */
	private static function _checkLoad()
	{
		if(!isset(self::$_session_params))
		{
			self::_loadSessionParams();
		}
		if(!isset(self::$_list_not_view_diag))
		{
			self::_loadListNotViewDiag();
		}
		if(!isset(self::$_list_vip_lpu))
		{
			self::_loadListVipLpu();
		}
	}

}
