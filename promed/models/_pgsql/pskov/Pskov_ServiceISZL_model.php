<?php

defined('BASEPATH') or die('No direct script access allowed');
/**
 * Pskov_ISZLService_model - модель для синхронизации данных с АИС «Информационное сопровождение застрахованных лиц»
 * для Пскова
 *
 * @package			Common
 * @access			public
 * @copyright		Copyright (c) 2019 Swan Ltd.
 */
require_once(APPPATH . 'models/_pgsql/ServiceISZL_model.php');

class Pskov_ServiceISZL_model extends ServiceISZL_model {

    /**
     * Конструктор
     */
    function __construct() {
        parent::__construct();
    }

    /**
     * Получение порядка обработки данных
     */
    function getProcConfig() {
        $procConfig = array(
            'Insert' => array(
                'BRANCH' => 'BRANCH',
                'DIVISION' => 'DIVISION',
                'DIVISION_LINK_STRUCTURE_BED' => 'DIVISION_LINK_STRUCTURE_BED',
                'AMOUNT_BED' => 'AMOUNT_BED',
                'FREE_BEDS_INFORMATION' => 'FREE_BEDS_INFORMATION',
                'HOSPITALISATION_REFERRAL' => 'HOSPITALISATION_REFERRAL',
                'CANCEL_HOSPITALISATION_REFERRAL' => 'CANCEL_HOSPITALISATION_REFERRAL',
                'HOSPITALISATION' => 'HOSPITALISATION',
                'MOTION_IN_HOSPITAL' => 'MOTION_IN_HOSPITAL',
                'CANCEL_HOSPITALISATION' => 'CANCEL_HOSPITALISATION',
            ),
            'Update' => array(),
            'Delete' => array(),
        );

        $procConfig['Update'] = $procConfig['Insert'];
        $procConfig['Delete'] = array_reverse($procConfig['Insert']);

        return $procConfig;
    }

    /**
     * Получение карты объектов
     */
    function getObjectMap() {
        return array(
            'BRANCH' => 'LpuBuilding',
            'DIVISION' => 'LpuSection',
            'DIVISION_LINK_STRUCTURE_BED' => 'LpuSectionBedState',
            'AMOUNT_BED' => 'LpuSectionBedState',
            'FREE_BEDS_INFORMATION' => 'LpuSectionBedState',
            'HOSPITALISATION_REFERRAL' => 'EvnDirection',
            'CANCEL_HOSPITALISATION_REFERRAL' => 'EvnDirection',
            'HOSPITALISATION' => 'EvnPS',
            'MOTION_IN_HOSPITAL' => 'EvnSection',
            'CANCEL_HOSPITALISATION' => 'EvnSection',
        );
    }

    /**
     * Возвращает данные о койках
     * 
     * @param string $tmpTable
     * @param string $procDataType
     * @param array $data
     * @param string $returnType
     * @param int $start
     * @param int $limit
     * 
     * @return array|false
     */
    protected function package_DIVISION_LINK_STRUCTURE_BED($tmpTable, $procDataType, $data, $returnType = 'data', $start = 0, $limit = 500) {
        return $this->package_BED($tmpTable, $procDataType, $data, $returnType, $start, $limit);
    }

    /**
     * Возвращает данные коек в профиле
     * 
     * @param string $tmpTable
     * @param string $procDataType
     * @param array $data
     * @param string $returnType
     * @param int $start
     * @param int $limit
     * 
     * @return array|false
     */
    protected function package_AMOUNT_BED($tmpTable, $procDataType, $data, $returnType = 'data', $start = 0, $limit = 500) {
        return $this->package_BED($tmpTable, $procDataType, $data, $returnType, $start, $limit);
    }

    /**
     * Возвращает данные о свободных койках
     * 
     * @param string $tmpTable
     * @param string $procDataType
     * @param array $data
     * @param string $returnType
     * @param int $start
     * @param int $limit
     * 
     * @return array|false
     */
    protected function package_FREE_BEDS_INFORMATION($tmpTable, $procDataType, $data, $returnType = 'data', $start = 0, $limit = 500) {
        return $this->package_BED($tmpTable, $procDataType, $data, $returnType, $start, $limit);
    }

}
