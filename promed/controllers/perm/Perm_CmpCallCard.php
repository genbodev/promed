<?php defined('BASEPATH') or die('No direct script access allowed');

require_once(APPPATH.'controllers/CmpCallCard.php');

class Perm_CmpCallCard extends CmpCallCard {
	
	/**
	 * Конструктор
	 */
	public function __construct() {
		parent::__construct();
		
		$this->inputRules['saveCmpCallCardUsluga'] = array(
			array('field' => 'CmpCallCardUsluga_id', 'label' => 'Идентификатор услуги в карте вызова СМП', 'rules' => '', 'type' => 'id' ),
			array('field' => 'CmpCallCard_id', 'label' => 'Идентификатор карты вызова СМП', 'rules' => 'required', 'type' => 'id' ),
			array('field' => 'CmpCallCardUsluga_setDate', 'label' => 'Дата выполнения', 'rules' => 'required', 'type' => 'date' ),
			array('field' => 'CmpCallCardUsluga_setTime', 'label' => 'Время выполнения', 'rules' => 'required', 'type' => 'time' ),
			array('field' => 'MedStaffFact_id', 'label' => 'Идентификатор врача', 'rules' => 'required', 'type' => 'id' ),
			array('field' => 'PayType_id', 'label' => 'Идентификатор вида оплаты', 'rules' => 'required', 'type' => 'id' ),
			array('field' => 'UslugaCategory_id', 'label' => 'Идентификатор категории услугии', 'rules' => 'required', 'type' => 'id' ),
			array('field' => 'UslugaComplex_id', 'label' => 'Идентификатор услуги', 'rules' => 'required', 'type' => 'id' ),
			array('field' => 'UslugaComplexTariff_id', 'label' => 'Идентификатор тарифа', 'rules' => 'required', 'type' => 'id' ),
			array('field' => 'CmpCallCardUsluga_Cost', 'label' => 'Цена', 'rules' => '', 'type' => 'float' ),
			array('field' => 'CmpCallCardUsluga_Kolvo', 'label' => 'Количество', 'rules' => '', 'type' => 'int' ),
			array('field' => 'usluga_array', 'label' => 'JSON-массив услуг', 'rules' => '', 'type' => 'string' ),
			array('field' => 'ignoreUslugaComplexTariffCountCheck', 'label' => 'Признак игнорирования проверки количества тарифов на услуге', 'rules' => '', 'type' => 'int' ),
		);

		/**
		* Добавляем параметры для сохранения карты вызова
		*/
		$this->inputRules['saveCmpCallCard'] = array_merge($this->inputRules['saveCmpCallCard'], array(
			array('field' => 'CmpCallCard_Condition','label' => 'Состояние','rules' => '','type' => 'string', 'default' =>''),
			array('field' => 'CmpCallCard_Recomendations','label' => 'Рекомендации','rules' => '','type' => 'string', 'default' =>''),
			array('field' => 'LpuSection_id', 'label' => 'Идентификатор отделения', 'rules' => '', 'type' => 'id' ),
			array('field' => 'CmpCallCard_isShortEditVersion', 'label' => 'Признак сокращенной версии талона', 'rules' => '', 'type' => 'int', 'default'=>0 ),
			array('field' => 'Lpu_cid', 'label' => 'МО вызова', 'rules' => '', 'type' => 'id' ),
		));
	}

}