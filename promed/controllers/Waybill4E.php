<?php
defined('BASEPATH') or die('404. Script not found.');

/**
 * @class EmergencyTeam
 * 
 * Бригада СМП
 * 
 * @author Dyomin Dmitry
 * @since 09.2012
 */

class Waybill4E extends swController {
	
	public $inputRules = array(
		'saveWaybill' => array(
			// Общие сведения
			array(
				'field'	=> 'Waybill_id',
				'label'	=> 'Идентификатор путевого листа',
				'rules'	=> '',
				'type'	=> 'id',
			),
			array(
				'field'	=> 'Lpu_id',
				'label'	=> 'Идентификатор ЛПУ',
				'rules'	=> '',
				'type'	=> 'id',
			),
			array(
				'field'	=> 'EmergencyTeam_id',
				'label'	=> 'Идентификатор бригады СМП',
				'rules'	=> 'required',
				'type'	=> 'id',
			),
			array(
				'field'	=> 'Waybill_Series',
				'label'	=> 'Серия ПЛ',
				'rules'	=> '',
				'type'	=> 'string',
			),
			array(
				'field'	=> 'Waybill_Num',
				'label'	=> 'Номер ПЛ',
				'rules'	=> '',
				'type'	=> 'string',
			),
			array(
				'field'	=> 'Waybill_Date',
				'label'	=> 'Дата ПЛ',
				'rules'	=> '',
				'type'	=> 'date',
			),
			array(
				'field'	=> 'Waybill_GarageNum',
				'label'	=> 'Гаражный номер',
				'rules'	=> '',
				'type'	=> 'string',
			),
			array(
				'field'	=> 'Waybill_EmployeeNum',
				'label'	=> 'Табельный номер',
				'rules'	=> 'required',
				'type'	=> 'string',
			),
			array(
				'field'	=> 'Waybill_IdentityNum',
				'label'	=> 'Номер удостоверения',
				'rules'	=> '',
				'type'	=> 'string',
			),
			array(
				'field'	=> 'Waybill_Class',
				'label'	=> 'Класс',
				'rules'	=> '',
				'type'	=> 'string',
			),
			array(
				'field'	=> 'Waybill_LicenseCard',
				'label'	=> 'Лицензионная карточка',
				'rules'	=> '',
				'type'	=> 'int',
			),
			array(
				'field'	=> 'Waybill_RegNum',
				'label'	=> 'Регистрационный №',
				'rules'	=> '',
				'type'	=> 'string',
			),
			array(
				'field'	=> 'Waybill_RegSeries',
				'label'	=> 'Серия',
				'rules'	=> '',
				'type'	=> 'string',
			),
			array(
				'field'	=> 'Waybill_RegNum2',
				'label'	=> 'Номер',
				'rules'	=> '',
				'type'	=> 'string',
			),
			
			// Задание водителю
			array(
				'field'	=> 'Waybill_Address',
				'label'	=> 'Адрес подачи',
				'rules'	=> 'required',
				'type'	=> 'string',
			),
			array(
				'field'	=> 'Waybill_TimeStart',
				'label'	=> 'Время выезда из гаража',
				'rules'	=> 'required',
				'type'	=> 'time',
			),
			array(
				'field'	=> 'Waybill_TimeFinish',
				'label'	=> 'Время возвращения в гараж',
				'rules'	=> '',
				'type'	=> 'time',
			),
			array(
				'field'	=> 'Waybill_Justification',
				'label'	=> 'Опоздания, ожидания, простои, заезды в гараж и т.п.',
				'rules'	=> '',
				'type'	=> 'string',
			),
			
			// Учет ГСМ
			array(
				'field'	=> 'Waybill_OdometrBefore',
				'label'	=> 'Показания спидометра при выезде, км',
				'rules'	=> 'required',
				'type'	=> 'int',
			),
			array(
				'field'	=> 'Waybill_OdometrAfter',
				'label'	=> 'Покозания спидометра при возвращении, км',
				'rules'	=> '',
				'type'	=> 'int',
			),
			array(
				'field'	=> 'WaybillGas_id',
				'label'	=> 'Марка горючего',
				'rules'	=> '',
				'type'	=> 'int',
			),
			array(
				'field'	=> 'Waybill_RefillCardNum',
				'label'	=> '№ заправочного листа',
				'rules'	=> '',
				'type'	=> 'int',
			),
			array(
				'field'	=> 'Waybill_FuelGet',
				'label'	=> 'Выдано по заправочному листу, л',
				'rules'	=> '',
				'type'	=> 'int',
			),
			array(
				'field'	=> 'Waybill_FuelBefore',
				'label'	=> 'Остаток при выезде, л',
				'rules'	=> '',
				'type'	=> 'int',
			),
			array(
				'field'	=> 'Waybill_FuelAfter',
				'label'	=> 'Остаток при возвращени, л',
				'rules'	=> '',
				'type'	=> 'int',
			),
			array(
				'field'	=> 'Waybill_FuelConsumption',
				'label'	=> 'Расход по норме, л',
				'rules'	=> '',
				'type'	=> 'float',
			),
			array(
				'field'	=> 'Waybill_FuelFact',
				'label'	=> 'Расход фактический, л',
				'rules'	=> '',
				'type'	=> 'int',
			),
			array(
				'field'	=> 'Waybill_FuelEconomy',
				'label'	=> 'Экономия, л',
				'rules'	=> '',
				'type'	=> 'int',
			),
			array(
				'field'	=> 'Waybill_FuelOverrun',
				'label'	=> 'Перерасход, л',
				'rules'	=> '',
				'type'	=> 'int',
			),
			
			// Дополнительные сведения
			array(
				'field'	=> 'Waybill_PersonCnt',
				'label'	=> 'Всего в наряде, час.',
				'rules'	=> '',
				'type'	=> 'int',
			),
			array(
				'field'	=> 'Waybill_Trip',
				'label'	=> 'Пройдено, км',
				'rules'	=> '',
				'type'	=> 'int',
			),
			array(
				'field'	=> 'Waybill_PaymentOdometr',
				'label'	=> 'За километраж, руб. коп',
				'rules'	=> '',
				'type'	=> 'float',
			),
			array(
				'field'	=> 'Waybill_PaymentTime',
				'label'	=> 'За часы, руб. коп',
				'rules'	=> '',
				'type'	=> 'float',
			),
			array(
				'field'	=> 'Waybill_PaymentTotal',
				'label'	=> 'Итого, руб. коп',
				'rules'	=> '',
				'type'	=> 'float',
			),
			array(
				'field'	=> 'Waybill_CalcMakePost',
				'label'	=> 'Должность производившего расчет',
				'rules'	=> '',
				'type'	=> 'string',
			),
			array(
				'field'	=> 'Waybill_CalcMakeName',
				'label'	=> 'ФИО производившего расчет',
				'rules'	=> '',
				'type'	=> 'string',
			),
			array (
				'field' => 'WaybillRoute',
				'label' => 'Список маршрутов движения',
				'rules' => 'trim',
				'type' => 'string',
				'default' => '[]',
			),
		),
		
		'loadWaybillGrid' => array(
			array(
				'field' => 'dateStart',
				'label' => 'Дата начала поиска смен',
				'rules' => '',
				'type' => 'string',
			),
			array(
				'field' => 'dateFinish',
				'label' => 'Дата окончания поиска смен',
				'rules' => '',
				'type' => 'string',
			)
		),

		'loadWaybill' => array(
			array(
				'field'	=> 'Waybill_id',
				'label'	=> 'Идентификатор путевого листа',
				'rules'	=> 'required',
				'type'	=> 'id',
			),
		),

		'loadWaybillRoute' => array(
			array(
				'field'	=> 'Waybill_id',
				'label'	=> 'Идентификатор путевого листа',
				'rules'	=> 'required',
				'type'	=> 'id',
			),
		),
		
		'deleteWaybill' => array(
			array(
				'field'	=> 'Waybill_id',
				'label'	=> 'Идентификатор путевого листа',
				'rules'	=> 'required',
				'type'	=> 'id',
			),
		),
		
		'printWaybill' => array(
			array(
				'field'	=> 'Waybill_id',
				'label'	=> 'Идентификатор путевого листа',
				'rules'	=> 'required',
				'type'	=> 'id',
			),
		)
	);
	
	
	/**
	 * @desc Инициализация
	 * 
	 * @return void
	 */
	public function __construct(){
		parent::__construct();
		
		$this->load->database();
		$this->load->model('Waybill_model4E', 'dbmodel');
	}
	
	
	/**
	 * @desc Сохранение путевого листа
	 * 
	 * @return bool
	 */
	public function saveWaybill() {
		$data = $this->ProcessInputData( 'saveWaybill', true );
		if ( $data === false ) {
			return false;
		}

		$response = $this->dbmodel->saveWaybill( $data );

		$this->ProcessModelSave( $response, true, 'Ошибка при сохранении путевого листа' )->ReturnData();

		return true;
	}
	
	
	/**
	 * @desc Возвращает список путевых листов
	 * 
	 * @return bool
	 */
	public function loadWaybillGrid(){
		$data = $this->ProcessInputData( 'loadWaybillGrid', true );
		if ( $data === false ) {
			return false;
		}
		
		$response = $this->dbmodel->loadWaybillGrid( $data );
	
		$this->ProcessModelMultiList( $response, true, true )->ReturnData();
		
		return true;
	}
	
	
	/**
	 * @desc Возвращает данные путевого листа
	 * 
	 * @return bool
	 */
	public function loadWaybill(){
		$data = $this->ProcessInputData( 'loadWaybill', true );
		if ( $data === false ) {
			return false;
		}
		
		$response = $this->dbmodel->loadWaybill( $data );

		$this->ProcessModelList( $response, true )->ReturnData();

		return true;
	}
	
	
	/**
	 * @desc Возвращает данные путевого листа
	 * 
	 * @return bool
	 */
	public function loadWaybillRoute(){
		$data = $this->ProcessInputData( 'loadWaybillRoute', true );
		if ( $data === false ) {
			return false;
		}
		
		$response = $this->dbmodel->loadWaybillRoute( $data );

		$this->ProcessModelList( $response, true, true )->ReturnData();

		return true;
	}
	
	
	/**
	 * @desc Удаляет путевой лист
	 * 
	 * @return bool
	 */
	public function deleteWaybill() {
		$data = $this->ProcessInputData( 'deleteWaybill', true );
		if ( $data === false ) {
			return false;
		}
		
		$response = $this->dbmodel->deleteWaybill( $data );
		
		$this->ProcessModelSave( $response, true, true )->ReturnData();

		return true;
	}
	

	/**
	 * @desc Выводит путевой лист на печать
	 * 
	 * @return html
	 */
	function printWaybill() {
		
		$data = $this->ProcessInputData( 'printWaybill', true );
		if ( $data === false ) {
			return false;
		}
		
		$response = $this->dbmodel->printWaybill( $data );
		if ( !is_array( $response ) || !sizeof( $response ) ) {
			echo 'Не удалось получить данные путевого листа.';
			return false;
		}
		
		$months = array(
			'1' => 'января',
			'2' => 'февраля',
			'3' => 'марта',
			'4' => 'апреля',
			'5' => 'мая',
			'6' => 'июня',
			'7' => 'июля',
			'8' => 'августа',
			'9' => 'сентября',
			'10' => 'октября',
			'11' => 'ноября',
			'12' => 'декабря',
		);
				
		$date = strtotime( $response['Waybill_Date'] );
		$response['Waybill_Date'] = '«'.date('d',$date).'» '.$months[ date('n',$date) ].' '.date('Y',$date);
		$response['Waybill_LicenseCard'] = $response['Waybill_LicenseCard'] == 1 ? 'Стандартая' : 'Ограниченная';

		
		$this->load->library('parser');
		
		$this->parser->parse( 'print_waybill', $response );		
	}	
}