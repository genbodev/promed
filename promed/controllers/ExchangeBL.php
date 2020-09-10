<?php defined('BASEPATH') or die ('No direct script access allowed');

/**
 * AisPolka - контроллер
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * @property ExchangeBL_model $dbmodel
 *
 * @package			Common
 * @access			public
 * @copyright		Copyright (c) 2019 Swan Ltd.
 */
class ExchangeBL extends swController {

    /**
     * Конструктор
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->database();

        set_time_limit(0);
        ini_set("max_execution_time", "0");

        $this->load->model('ExchangeBL_model', 'dbmodel');
    }
}