<?php defined('BASEPATH') or die ('No direct script access allowed');

require_once(APPPATH.'controllers/Specifics.php');

class Samara_Specifics extends Specifics {

	/**
	 * Description
	 */
	function __construct() {
		parent::__construct();
	}


	/**
	*  Получение дерева с возможными вариантами по специфике
	*  Входящие данные: -
	*  На выходе: JSON-строка
	*  Используется: форма редактирования КВС
	*/
	function getSpecificsTree() {
        $this->load->helper('Options');

        $val = array();
        $options = getOptions();
        if ( $options['specifics']['born_data'] === true ) {
            $val[] = array(
                'id' => 'born_data',
                'value' => 'born_data',
                'text' => toUTF('Сведения о новорожденном'),
                'leaf' => true
            );
        }
        if ( $options['specifics']['childbirth_data'] === true ) {
            $val[] = array(
                'id' => 'childbirth_data',
                'value' => 'childbirth_data',
                'text' => toUTF('Беременность и роды'),
                'leaf' => true
            );
        }
        
	    $val[] = array(
			'id' => 'onko_data',
			'value' => 'onko_data',
			'text' => toUTF('Сведения об онкобольном'),
			'leaf' => true
	    );
        
		array_walk($val, 'ConvertFromWin1251ToUTF8');

		$this->ReturnData($val);

		return true;
	}
}
