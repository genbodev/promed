<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 *
 * @package        PromedWeb
 * @access        public
 * @copyright    Copyright (c) 2013 Swan Ltd.
 * @link        http://swan.perm.ru/PromedWeb
 * @version        05.2014
 */

/**
 * Модель зубной карты (ЗК)
 *
 * Актуальная ЗК является набором текущих активных состояний зубов пациента.
 *
 * Модель инкапсулирует логику
 * установки состояний зубов и переходов между ними при случае лечения,
 * чтения состояний зубов для отображения и печати ЗК,
 * и других операций с зубной картой
 *
 * @package        Stom
 * @author        Александр Пермяков
 *
 * @property EvnDiagPLStom_model $EvnDiagPLStom_model
 * @todo не допустить сохранения состояний без типа
 */
class PersonToothCard_model extends SwPgModel
{
    /**
     * Список имен операций, которые реализованы в модели
     *
     * @var array
     */
    protected $_operationList = [
        'doLoadMarkerData', // Получение данных для маркера
        'doLoadViewData', // Получение данных зубной карты на дату-время посещения для панели просмотра и редактирования
        'doPrint', // печать зубной карты
        'doOutputPng', // Вывод изображения зубной карты
        'doLoadHistory', // Получение данных списка истории
        'doSave', //Установка активных состояний сегмента из меню или зуба из формы редактирования
        'doRemove', // Отмена активных состояний, созданных в рамках посещения
    ];

    /**
     * Имя текущей операции
     * @var string
     */
    protected $_operation = '';

    /**
     * @var array
     */
    private $_ToothStateClassRelation = [];

    /**
     * @param string $operation
     */
    public function setOperation($operation)
    {
        $this->_operation = $operation;
    }

    /**
     * @return string
     */
    public function getOperation()
    {
        return $this->_operation;
    }

    /**
     * Конструктор
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Определение правил для входящих параметров
     * @param string $operation
     * @return array
     */
    public function getInputRules($operation)
    {

        $rules = [];
        switch ($operation) {
            case 'doOutputPng':
                $rules = [
                    ['field' => 'EvnVizitPLStom_id', 'label' => 'Стоматологическое посещение', 'rules' => 'trim', 'type' => 'id'],
                ];
                break;
            case 'doRemove':
            case 'doLoadMarkerData':
            case 'doPrint':
            case 'doLoadViewData':
                $rules = [
                    ['field' => 'EvnVizitPLStom_id', 'label' => 'Стоматологическое посещение', 'rules' => 'required', 'type' => 'id'],
                ];
                break;
            case 'doLoadHistory':
                $rules = [
                    ['field' => 'Person_id', 'label' => 'Человек', 'rules' => 'required', 'type' => 'id'],
                    ['field' => 'EvnVizitPLStom_id', 'label' => 'Стоматологическое посещение', 'rules' => '', 'type' => 'id'],
                ];
                break;
            case 'doSave':
                $rules = [
                    ['field' => 'EvnVizitPLStom_id', 'label' => 'Стоматологическое посещение', 'rules' => 'required', 'type' => 'id'],
                    ['field' => 'ToothType', 'label' => 'Тип зуба', 'rules' => 'required', 'type' => 'id'],
                    ['field' => 'Tooth_Code', 'label' => 'Зуб', 'rules' => 'required', 'type' => 'id'],
                    ['field' => 'ToothSurfaceType_id', 'label' => 'Поверхность зуба', 'rules' => '', 'type' => 'id'],
                    ['field' => 'PersonToothCard_IsSuperSet', 'label' => 'Сверхкомплектный зуб', 'rules' => '', 'type' => 'id'],
                    ['field' => 'ToothPositionType_aid', 'label' => 'Расположение сверхкомплектного А', 'rules' => '', 'type' => 'id'],
                    ['field' => 'ToothPositionType_bid', 'label' => 'Расположение сверхкомплектного В', 'rules' => '', 'type' => 'id'],
                    ['field' => 'states', 'label' => 'Состояния зуба', 'rules' => 'trim', 'type' => 'string'],
                    ['field' => 'deactivate', 'label' => 'Отмененные состояния зуба', 'rules' => 'trim', 'type' => 'string'],
                ];
                break;
        }
        return $rules;
    }

    /**
     * Установка состояний сегмента из меню или зуба из формы редактирования
     *
     * Активное состояние на дату-время посещения имеет
     * дату-время начала равную или меньшую, чем дата-время посещения и
     * не имеет даты окончания или дата окончания больше, чем дата-время посещения
     *
     * Более ранние по дате-времени состояния не могут отменять последующие
     *
     * Может быть несколько активных состояний на один зуб и даже на одну поверхность.
     * При редактировании активных состояний сегмента из меню
     * может быть установлено не более 2-х состояний на одну поверхность (кариес и/или пломба)
     * может быть отменено состояние при снятии галочки.
     * При редактировании активных состояний зуба из формы редактирования
     * может быть установлен только одно состояние-тип зуба (молочный или коренной или отсутствует или искуственный)
     * не могут быть установлены состояния поверхностей
     * @param array $data Массив, полученный методом ProcessInputData контроллера
     * @return array
     * @throws Exception
     */
    public function doSave($data = [], $isAllowTransaction = true)
    {
        $this->setOperation('doSave');
        try {
            $response = [[
                'Error_Msg' => null,
                'Error_Code' => null,
            ]];
            $startedTrans = false;
            if (empty($data['EvnVizitPLStom_id'])) {
                throw new Exception('Не указан случай стоматологического посещения !', 400);
            }
            if (empty($data['Tooth_Code'])) {
                throw new Exception('Не указан зуб!', 400);
            }
            if (empty($data['ToothType']) || !in_array($data['ToothType'], [12, 13, 14, 15])) {
                throw new Exception('Не указан тип зуба!', 400);
            }
            if (empty($data['Lpu_id'])) {
                throw new Exception('Не указана МО пользователя!', 400);
            }
            if (empty($data['pmUser_id'])) {
                throw new Exception('Не указан пользователь!', 400);
            }
            $isEditSurface = !empty($data['ToothSurfaceType_id']);
            $isValidFormatList = true;
            $statesIdList = [];
            $deactivateIdList = [];
            if (is_string($data['deactivate']) && !empty($data['deactivate'])) {
                $deactivateIdList = explode(',', $data['deactivate']);
                $deactivateIdList = array_unique($deactivateIdList);
            }
            if (is_string($data['states']) && !empty($data['states'])) {
                $statesIdList = explode(',', $data['states']);
                $statesIdList = array_unique($statesIdList);
            }
            foreach ($deactivateIdList as $id) {
                if (!is_numeric($id)) {
                    $isValidFormatList = false;
                }
            }
            foreach ($statesIdList as $i => $id) {
                if (!is_numeric($id)) {
                    $isValidFormatList = false;
                }
                if (in_array($id, [12, 13, 14, 15])) {
                    unset($statesIdList[$i]);
                }
            }
            if (!$isValidFormatList) {
                throw new Exception('Неправильный формат данных!', 400);
            }
            if (count($statesIdList) > 1 && $isEditSurface) {
                throw new Exception('Состояния зуба не могут быть изменены!', 400);
            }

            $tmp = $this->_loadToothDataByCode($data['Tooth_Code']);
            if (empty($tmp)) {
                throw new Exception('Указан неправильный код зуба!', 400);
            }
            $data['Tooth_id'] = $tmp[0]['Tooth_id'];
            $data['JawPartType_id'] = $tmp[0]['JawPartType_id'];
            $data['Tooth_SysNum'] = $tmp[0]['Tooth_SysNum'];


            $tmp = $this->_loadEvnVizitPLStomData($data['EvnVizitPLStom_id']);
            if (!$this->isAllowEdit($data['EvnVizitPLStom_id'], $tmp['Person_id'])) {
                throw new Exception('Разрешено только для последнего посещения!', 400);
            }
            $data['Person_id'] = $tmp['Person_id'];
            // дата-время начала активных состояний должна быть равна дате-времени посещения
            // дата-время окончания деактивированных состояний должна быть равна дате-времени посещения
            $data['history_date'] = $tmp['history_date'];

            $this->beginTransaction();
            $startedTrans = true;
            $deactivate = [];

            // получаем активные записи по зубу
            $states = $this->_loadActiveToothStates($data['Person_id'], $data['JawPartType_id'], $data['Tooth_SysNum']);
            $oldToothType = null;
            // сначала отменяем то, что отменил пользователь
            foreach ($states as $i => $active) {
                if (!$oldToothType && in_array($active['ToothStateClass_id'], array(12, 13, 14, 15))) {
                    $oldToothType = $active['ToothStateClass_id'];
                }
                if (in_array($active['PersonToothCard_id'], $deactivateIdList)) {
                    $deactivate[] = $active;
                    unset($states[$i]);
                }
            }
            if (isset($oldToothType) && $oldToothType != $data['ToothType'] && $isEditSurface) {
                throw new Exception('Тип зуба не может быть изменен!', 400);
            }
            if (empty($oldToothType) && !in_array($data['ToothType'], [12, 13]) && $isEditSurface) {
                throw new Exception('Тип зуба не может иметь состояния поверхностей!', 400);
            }
            if ($oldToothType != $data['ToothType']) {
                $newStates = array($data['ToothType']);
                foreach ($statesIdList as $id) {
                    $newStates[] = $id;
                }
                $statesIdList = $newStates;
            }
            // загружаем правила установки состояний и переходов между ними
            $rules = $this->_loadToothStateClass();
            // обработать новые состояния
            foreach ($statesIdList as $id) {
                if (empty($rules[$id])) {
                    throw new Exception('Отсутствуют правила установки состояния с идентификатором', 400);
                }
                // сначала определяем состояния, которые деактивируются новым состоянием
                if ($isEditSurface) {
                    /*
                    foreach($states as $i => $active) {
                        // пломба деактивирует кариес на той же поверхности?
                        // кариес ничего не деактивирует
                        if ($id == 5 && $active['ToothSurfaceType_id'] == $data['ToothSurfaceType_id']) {
                            if (isset($active['PersonToothCard_id'])) {
                                $deactivate[] = $active;
                            }
                            unset($states[$i]);
                        }
                    }
                    */
                } else {
                    foreach ($states as $i => $active) {
                        $aid = $active['ToothStateClass_id'];
                        if (in_array($aid, $rules[$id]['Deactivate'])) {
                            if (isset($active['PersonToothCard_id'])) {
                                $deactivate[] = $active;
                            }
                            unset($states[$i]);
                        }
                    }
                }
                $isPermission = true;
                // новое состояние может быть сохранено, если
                // оно допустимо для всех активных состояний
                // и совместимо со всеми активными состояниями
                foreach ($states as $i => $active) {
                    $aid = $active['ToothStateClass_id'];
                    if (empty($rules[$aid])) {
                        unset($states[$i]);
                        continue;
                    }
                    if ($isEditSurface) {
                        if ($active['ToothSurfaceType_id'] != $data['ToothSurfaceType_id']) {
                            continue;
                        }
                        if (in_array($id, $rules[$aid]['NoPermiss']) || !in_array($id, $rules[$aid]['Compatible'])) {
                            $isPermission = false;
                            break;
                        }
                    } else {
                        if (!empty($active['ToothSurfaceType_id'])) {
                            continue;
                        }
                        if (in_array($id, $rules[$aid]['NoPermiss']) || !in_array($id, $rules[$aid]['Compatible'])) {
                            $isPermission = false;
                            break;
                        }
                    }
                }
                /*
                if ($isEditSurface && $id==2 && !$isPermission) {
                    throw new Exception('Не удается установить состояние "Кариес"', 500);
                }
                if ($isEditSurface && $id==5 && !$isPermission) {
                    throw new Exception('Не удается установить состояние "Пломба"', 500);
                }
                */
                if (!$isEditSurface && in_array($id, [2, 5])) {
                    $isPermission = false;
                }
                if ($isPermission) {
                    $newState = [
                        'PersonToothCard_id' => null,
                        'ToothStateClass_id' => $id,
                        'Tooth_id' => $data['Tooth_id'],
                        'ToothSurfaceType_id' => null,
                        'PersonToothCard_IsSuperSet' => $data['PersonToothCard_IsSuperSet'],
                        'ToothPositionType_bid' => $data['ToothPositionType_bid'],
                        'ToothPositionType_aid' => $data['ToothPositionType_aid'],
                        'Person_id' => $data['Person_id'],
                        'Server_id' => $data['Lpu_id'],
                        'EvnVizitPLStom_id' => $data['EvnVizitPLStom_id'],
                        'EvnDiag_id' => null,
                        'EvnUsluga_id' => null,
                        'PersonToothCard_begDate' => $data['history_date'],
                        'PersonToothCard_endDate' => null,
                    ];
                    if ($isEditSurface && in_array($id, [2, 5])) {
                        $newState['ToothSurfaceType_id'] = $data['ToothSurfaceType_id'];
                    }
                    $states[] = $newState;
                }
            }

            //throw new Exception(var_export(array($states, $deactivate), true), 400);
            foreach ($deactivate as $row) {
                $row['PersonToothCard_endDate'] = $data['history_date'];
                $row['pmUser_id'] = $data['pmUser_id'];
                $this->_save($row);
            }
            foreach ($states as $active) {
                if (empty($active['PersonToothCard_id'])) {
                    $active['pmUser_id'] = $data['pmUser_id'];
                    $active['PersonToothCard_id'] = $this->_save($active);
                }
            }
        } catch (Exception $e) {
            if ($startedTrans) {
                $this->rollbackTransaction();
            }
            $response[0]['Error_Msg'] = $e->getMessage();
            $response[0]['Error_Code'] = $e->getCode();
            return $response;
        }
        if ($startedTrans) {
            $this->commitTransaction();
        }
        return $response;
    }

    /**
     * Установка состояний из парадонтограммы.
     *
     * @param array $data
     * @param array $nothingList
     * @return boolean
     * @throws Exception
     */
    public function applyParodontogramChanges($data, $nothingList)
    {
        if (empty($data['Lpu_id'])) {
            throw new Exception('Не указана МО пользователя!', 400);
        }
        if (empty($data['pmUser_id'])) {
            throw new Exception('Не указан пользователь!', 400);
        }
        $queryParams = array('EvnUslugaStom_id' => $data['EvnUslugaStom_id']);
        $query = "
			SELECT
				v.Person_id as \"Person_id\",
				v.EvnVizitPLStom_id as \"EvnVizitPLStom_id\",
				v.EvnVizitPLStom_setDT as \"EvnVizitPLStom_setDT\"
			FROM
			    v_EvnUslugaStom u
			    inner join v_EvnVizitPLStom v on v.EvnVizitPLStom_id = u.EvnUslugaStom_pid
			WHERE
			    u.EvnUslugaStom_id = :EvnUslugaStom_id
		";
        $result = $this->db->query($query, $queryParams);
        if (!is_object($result)) {
            throw new Exception('Ошибка при запросе данных посещения', 500);
        }
        $tmp = $result->result('array');
        if (empty($tmp)) {
            throw new Exception('Отсутствие данных посещения', 404);
        }
        $tmp = $this->_formatDatetimeFields($tmp);
        $data['Person_id'] = $tmp[0]['Person_id'];
        $data['EvnVizitPLStom_id'] = $tmp[0]['EvnVizitPLStom_id'];
        // дата-время начала активных состояний должна быть равна дате-времени посещения
        // дата-время окончания деактивированных состояний должна быть равна дате-времени посещения
        $data['history_date'] = $tmp[0]['EvnVizitPLStom_setDT'];

        // получаем активные типы зубов на дату и время посещения
        $queryParams = [
            'Person_id' => $data['Person_id'],
            'history_date' => $data['history_date'],
        ];
        $query = "
		    with cte as (
		        select cast(:history_date as timestamp) as history_date
		    )
			SELECT
				s.PersonToothCard_id as \"PersonToothCard_id\",
				s.PersonToothCard_begDate as \"PersonToothCard_begDate\",
				s.PersonToothCard_endDate as \"PersonToothCard_endDate\",
				s.Tooth_id as \"Tooth_id\",
				s.ToothStateClass_id as \"ToothStateClass_id\"
			FROM v_PersonToothCard s
			WHERE
			    s.Person_id = :Person_id
            and
                s.ToothStateClass_id in (12,13,14,15)
            and
                s.PersonToothCard_begDate <= (select history_date from cte)
            and (
                s.PersonToothCard_endDate is null or
                s.PersonToothCard_endDate > (select history_date from cte)
            )
			";
        // echo getDebugSQL($query, $queryParams); exit();
        $result = $this->db->query($query, $queryParams);
        if (!is_object($result)) {
            throw new Exception('Ошибка при запросе данных зубной карты', 500);
        }
        $syncData = [];
        $toothChangesIdList = [];
        $tmp = $result->result('array');
        $tmp = $this->_formatDatetimeFields($tmp);
        // обрабатываем активные типы зубов на дату и время посещения
        foreach ($tmp as $row) {
            $syncData[$row['Tooth_id']] = [
                'toothType' => null,
                'cancel' => null,
            ];
            if (14 == $row['ToothStateClass_id'] && !in_array($row['Tooth_id'], $nothingList)) {
                // Активный тип - отсутствует, но в парадонтограмме зуб есть
                if (empty($row['PersonToothCard_endDate'])) {
                    // это свежие данные, то деактивируем,
                    // а какой новый тип? - решит врач
                    $syncData[$row['Tooth_id']]['cancel'] = $row['PersonToothCard_id'];
                    $toothChangesIdList[] = $row['Tooth_id'];
                }
            }
            if (14 != $row['ToothStateClass_id'] && in_array($row['Tooth_id'], $nothingList)) {
                // Активный тип - не отсутсвует, но в парадонтограмме зуб отсутствует
                if (empty($row['PersonToothCard_endDate'])) {
                    // это свежие данные, то добавляем новый тип
                    $syncData[$row['Tooth_id']]['toothType'] = 14;
                    $toothChangesIdList[] = $row['Tooth_id'];
                    // ниже деактивируем активный тип и все его состояния
                } else {
                    // это старые данные, то добавляем в историю, что зуба не было
                    $syncData[$row['Tooth_id']]['PersonToothCard_endDate'] = $row['PersonToothCard_endDate'];
                    $syncData[$row['Tooth_id']]['toothType'] = 14;
                    $toothChangesIdList[] = $row['Tooth_id'];
                }
            }
        }
        foreach ($nothingList as $tooth_id) {
            if (empty($syncData[$tooth_id])) {
                // по этому зубу нет состояний, то добавляем состояние - отсутствует
                $syncData[$tooth_id] = [
                    'toothType' => 14,
                    'cancel' => null,
                ];
                $toothChangesIdList[] = $tooth_id;
            }
        }
        if (empty($toothChangesIdList)) {
            return true;
        }

        // получаем активные записи на ТЕКУЩУЮ дату и время
        $states = $this->_loadActiveToothStates($data['Person_id']);

        foreach ($toothChangesIdList as $tooth_id) {
            $state = [];
            $deactivate = [];
            if (!empty($syncData[$tooth_id]['toothType'])) {
                // Более ранние по дате-времени состояния не могут отменять последующие
                $isAllowDeactivate = empty($syncData[$tooth_id]['PersonToothCard_endDate']);
                $state = [
                    'PersonToothCard_id' => null,
                    'ToothStateClass_id' => $syncData[$tooth_id]['toothType'],
                    'Tooth_id' => $tooth_id,
                    'ToothSurfaceType_id' => null,
                    'PersonToothCard_IsSuperSet' => null,
                    'ToothPositionType_bid' => null,
                    'ToothPositionType_aid' => null,
                    'Person_id' => $data['Person_id'],
                    'Server_id' => $data['Lpu_id'],
                    'EvnVizitPLStom_id' => $data['EvnVizitPLStom_id'],
                    'EvnDiag_id' => null,
                    'EvnUsluga_id' => $data['EvnUslugaStom_id'],
                    'PersonToothCard_begDate' => $data['history_date'],
                    'PersonToothCard_endDate' => $isAllowDeactivate ? null : $syncData[$tooth_id]['PersonToothCard_endDate'],
                ];
                if ($isAllowDeactivate) {
                    // определяем активные состояния, которые деактивируются новым состоянием
                    foreach ($states as $i => $active) {
                        if ($tooth_id != $active['Tooth_id']) {
                            continue;
                        }
                        //все
                        $deactivate[] = $active;
                        unset($states[$i]);
                    }
                }
            } else if (!empty($syncData[$tooth_id]['cancel'])) {
                // отменяем то, что отменил пользователь
                foreach ($states as $i => $active) {
                    if ($active['PersonToothCard_id'] == $syncData[$tooth_id]['cancel']) {
                        $deactivate[] = $active;
                        unset($states[$i]);
                    }
                }
            }
            if (!empty($state)) {
                $state['pmUser_id'] = $data['pmUser_id'];
                $state['PersonToothCard_id'] = $this->_save($state);
            }
            foreach ($deactivate as $row) {
                $row['PersonToothCard_endDate'] = $data['history_date'];
                $row['pmUser_id'] = $data['pmUser_id'];
                $this->_save($row);
            }
        }
        return true;
    }

    /**
     * Установка состояний (ToothStateClass) на основании оказания услуг.
     *
     * Выполнение некоторых услуг (ToothStateClassUsluga) из ГОСТ-11,
     * либо связанных с ними, должно изменять состояние зуба.
     * Изменения состояний и переходы между ними подчиняются определенным правилам.
     * Номер зуба и указание поверхностей берется из диагноза посещения.
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function applyEvnUslugaChanges($data)
    {
        if (empty($data['Lpu_id'])) {
            throw new Exception('Не указана МО пользователя!', 400);
        }
        if (empty($data['Person_id'])) {
            throw new Exception('Не указан человек!', 400);
        }
        if (empty($data['pmUser_id'])) {
            throw new Exception('Не указан пользователь!', 400);
        }
        if (empty($data['EvnUsluga_pid'])) {
            throw new Exception('Не указан случай стоматологического посещения!', 400);
        }
        if (empty($data['EvnUsluga_setDT'])) {
            //throw new Exception('Не указана дата и время случая оказания услуги!', 400);
        }
        if (empty($data['UslugaData']) || !is_array($data['UslugaData'])) {
            throw new Exception('Не указаны данные случаев оказания услуги!', 400);
        }
        /*
        if (!$this->isAllowEdit($data['EvnUsluga_pid'], $data['Person_id'])) {
            // Разрешено только для последнего посещения!
            return true;
        }
        */

        // Отсеять услуги, для которых не требуется изменение состояний зуба
        $EvnUslugaList = [];
        $UslugaComplexIdList = [];
        foreach ($data['UslugaData'] as $row) {
            $uc_id = $row['UslugaComplex_id'];
            $EvnUslugaList[$uc_id] = $row['EvnUsluga_id'];
            $UslugaComplexIdList[] = $uc_id;
        }
        if (empty($UslugaComplexIdList)) {
            throw new Exception('Не указана услуга!', 400);
        }
        $query = "
			select
                uc.UslugaComplex_id as \"UslugaComplex_id\",
                s.ToothStateClass_id as \"ToothStateClass_id\"
			from
			    v_UslugaComplex uc
                inner join v_UslugaComplex uc11 on uc11.UslugaComplex_id = uc.UslugaComplex_2011id
                inner join v_ToothStateClassUsluga s on s.UslugaComplex_id = uc11.UslugaComplex_id
			where uc.UslugaComplex_id in (" . implode(',', $UslugaComplexIdList) . ")
		";
        $result = $this->db->query($query, []);
        if (!is_object($result)) {
            throw new Exception('Ошибка при запросе услуг, для которых требуется изменение состояний зуба', 500);
        }
        $tmp = $result->result('array');
        if (empty($tmp)) {
            return true;
        }
        $newStates = [];
        foreach ($tmp as $row) {
            $uc_id = $row['UslugaComplex_id'];
            $newState = [
                'PersonToothCard_id' => null,
                'Tooth_id' => null,
                'ToothSurfaceType_id' => null,
                'PersonToothCard_IsSuperSet' => null,
                'ToothPositionType_bid' => null,
                'ToothPositionType_aid' => null,
                'Person_id' => $data['Person_id'],
                'Server_id' => $data['Lpu_id'],
                'EvnVizitPLStom_id' => $data['EvnUsluga_pid'],
                'EvnDiag_id' => null,
                'PersonToothCard_begDate' => null,
                'PersonToothCard_endDate' => null,
            ];
            $newState['EvnUsluga_id'] = $EvnUslugaList[$uc_id];
            $newState['ToothStateClass_id'] = $row['ToothStateClass_id'];
            $newStates[] = $newState;
        }
        unset($UslugaComplexIdList);
        unset($EvnUslugaList);

        // Получить данные из посещения
        $query = "
			select
				e.EvnVizitPLStom_id as \"EvnVizitPLStom_id\",
				e.Person_id as \"Person_id\",
				v_Tooth.Tooth_id as \"Tooth_id\",
				v_Tooth.Tooth_Code as \"Tooth_Code\",
				substring(cast(v_Tooth.Tooth_Code as varchar(2)), 2, 1) as \"Tooth_SysNum\",
				v_Tooth.JawPartType_id as \"JawPartType_id\",
				e.EvnVizitPLStom_ToothSurface as \"EvnVizitPLStom_ToothSurface\",
				e.EvnVizitPLStom_setDT as \"history_date\",
				NextState.PersonToothCard_begDate as \"next_history_date\"
			from
			    v_EvnVizitPLStom e
                inner join v_Tooth on v_Tooth.Tooth_id = e.Tooth_id
                left join lateral(
                    select
                        s.PersonToothCard_begDate
                    from
                        v_PersonToothCard s
                    where
                        s.Person_id = e.Person_id
                    and 
                        s.Tooth_id = e.Tooth_id
                    and 
                        s.PersonToothCard_begDate > e.EvnVizitPLStom_setDT
                    order by
                        s.PersonToothCard_begDate
                    limit 1
                ) NextState on true
			where e.EvnVizitPLStom_id = :EvnVizitPLStom_id
			limit 1
		";
        $result = $this->db->query($query, ['EvnVizitPLStom_id' => $data['EvnUsluga_pid']]);
        if (!is_object($result)) {
            throw new Exception('Ошибка при запросе данных случая стоматологического посещения ', 500);
        }
        $tmp = $result->result('array');
        if (empty($tmp)) {
            //это не стомат.посещение или не указан зуб
            return true;
        }
        $tmp = $this->_formatDatetimeFields($tmp);
        // дата-время окончания деактивированных состояний должна быть равна дате-времени посещения
        $data['history_date'] = $tmp[0]['history_date'];
        $data['Tooth_SysNum'] = $tmp[0]['Tooth_SysNum'];
        $data['JawPartType_id'] = $tmp[0]['JawPartType_id'];
        foreach ($newStates as &$row) {
            $row['Tooth_id'] = $tmp[0]['Tooth_id'];
            // дата-время начала активных состояний должна быть равна дате-времени посещения
            $row['PersonToothCard_begDate'] = $tmp[0]['history_date'];
            // Более ранние по дате-времени состояния не могут отменять последующие
            // Для этой проверки запоминаем дату-время начала следующего состояния, если оно есть
            $row['PersonToothCard_endDate'] = $tmp[0]['next_history_date'];
        }
        $this->load->model('EvnDiagPLStom_model', 'EvnDiagPLStom_model');
        $tmp = $this->EvnDiagPLStom_model->processingToothSurface($tmp[0]['EvnVizitPLStom_ToothSurface']);

        // загружаем правила установки состояний и переходов между ними
        $rules = $this->_loadToothStateClass();

        foreach ($tmp['ToothSurfaceTypeIdList'] as $i => $id) {
            foreach ($newStates as $key => $row) {
                $id = $row['ToothStateClass_id'];
                if (empty($rules[$id])) {
                    throw new Exception('Отсутствуют правила установки состояния с идентификатором ' . $id, 500);
                }
                if (!$rules[$id]['OnlySurface']) {
                    // если не применимо к поверхностям
                    continue;
                }
                if ($i > 0 && empty($newStates[$key]['ToothSurfaceType_id'])) {
                    $newStates[$key]['ToothSurfaceType_id'] = $id;
                    $newStates[] = $newStates[$key];
                } else {
                    $newStates[$key]['ToothSurfaceType_id'] = $id;
                }
            }
        }

        // получаем активные записи по зубу на ТЕКУЩУЮ дату и время
        $states = $this->_loadActiveToothStates($data['Person_id'], $data['JawPartType_id'], $data['Tooth_SysNum']);
        // обработать новые состояния
        $deactivate = [];
        foreach ($newStates as $row) {
            $id = $row['ToothStateClass_id'];
            if (empty($rules[$id])) {
                throw new Exception('Отсутствуют правила установки состояния с идентификатором ' . $id, 500);
            }
            $isPermission = true;
            if (empty($row['PersonToothCard_endDate'])) {
                // сначала определяем состояния, которые деактивируются новым состоянием
                foreach ($states as $i => $active) {
                    $aid = $active['ToothStateClass_id'];
                    if (in_array($aid, $rules[$id]['Deactivate']) && empty($active['PersonToothCard_endDate'])) {
                        if (isset($active['PersonToothCard_id'])) {
                            $deactivate[] = $active;
                        }
                        unset($states[$i]);
                    }
                }
                // новое состояние может быть сохранено, если
                // оно допустимо для всех активных состояний
                // и совместимо со всеми активными состояниями
                foreach ($states as $i => $active) {
                    $aid = $active['ToothStateClass_id'];
                    if (empty($rules[$aid])) {
                        unset($states[$i]);
                        continue;
                    }
                    if (in_array($id, $rules[$aid]['NoPermiss']) || !in_array($id, $rules[$aid]['Compatible'])) {
                        $isPermission = false;
                        break;
                    }
                }
            }
            if ($isPermission) {
                $states[] = $row;
            }
        }
        //throw new Exception(var_export(array($states, $deactivate), true), 400);
        foreach ($states as $active) {
            if (empty($active['PersonToothCard_id'])) {
                // если в PersonToothCard_endDate дата-время начала следующего состояния,
                // то новое состояние уйдет в историю, иначе будет активным
                $active['pmUser_id'] = $data['pmUser_id'];
                $active['PersonToothCard_id'] = $this->_save($active);
            }
        }
        foreach ($deactivate as $row) {
            $row['PersonToothCard_endDate'] = $data['history_date'];
            $row['pmUser_id'] = $data['pmUser_id'];
            $this->_save($row);
        }
        return true;
    }

    /**
     * Отмена активных состояний по идешнику ТАП или посещения или услуги
     * @param int $id
     * @param string $sysnick
     * @param bool $isAllowTransaction
     * @return array
     */
    public function doRemoveByEvn($id, $sysnick, $isAllowTransaction = true)
    {
        $response = [['Error_Msg' => null, 'Error_Code' => null,]];
        $this->isAllowTransaction = $isAllowTransaction;
        $this->beginTransaction();
        try {
            if (!in_array($sysnick, ['EvnVizitPLStom', 'EvnPLStom', 'EvnUslugaStom'])
                || empty($this->promedUserId)
            ) {
                throw new Exception('Неправильные параметры для отмены активных состояний зубной карты', 500);
            }
            $queryParams = array('id' => $id);
            $query = "
			    select
                    d.PersonToothCard_id as \"PersonToothCard_did\",
                    s.PersonToothCard_id as \"PersonToothCard_id\",
                    s.Server_id as \"Server_id\",
                    s.Person_id as \"Person_id\",
                    s.EvnDiag_id as \"EvnDiag_id\",
                    s.EvnUsluga_id as \"EvnUsluga_id\",
                    s.EvnVizitPLStom_id as \"EvnVizitPLStom_id\",
                    s.PersonToothCard_begDate as \"PersonToothCard_begDate\",
                    s.PersonToothCard_endDate as \"PersonToothCard_endDate\",
                    s.Tooth_id as \"Tooth_id\",
                    s.PersonToothCard_IsSuperSet as \"PersonToothCard_IsSuperSet\",
                    s.ToothPositionType_aid as \"ToothPositionType_aid\",
                    s.ToothPositionType_bid as \"ToothPositionType_bid\",
                    s.ToothSurfaceType_id as \"ToothSurfaceType_id\",
                    s.ToothStateClass_id as \"ToothStateClass_id\"
			    from
			        v_PersonToothCard d
			        left join v_PersonToothCard s on s.PersonToothCard_endDate = d.PersonToothCard_begDate and s.Person_id = d.Person_id
            ";

            if ('EvnPLStom' == $sysnick) {
                $query .= "
                    inner join v_Evn on v_Evn.Evn_id = d.EvnVizitPLStom_id
                    where v_Evn.Evn_pid = :id and v_Evn.EvnClass_id = 13
                ";
            } else if ('EvnVizitPLStom' == $sysnick) {
                $query .= "
				    where d.EvnVizitPLStom_id = :id
				";
            } else {
                $query .= "
			        where d.EvnUsluga_id = :id
			    ";
            }
            
            $result = $this->db->query($query, $queryParams);
            
            if (!is_object($result)) {
                throw new Exception('Ошибка при запросе состояний зубной карты', 500);
            }
            
            $tmp = $result->result('array');
            $tmp = $this->_formatDatetimeFields($tmp);
            $deletedList = [];
            
            foreach ($tmp as $row) {
                if (!in_array($row['PersonToothCard_did'], $deletedList)) {
                    $data['PersonToothCard_id'] = $row['PersonToothCard_did'];
                    $data['pmUser_id'] = $this->promedUserId;
                    //$data['IsRemove'] = 2;
                    $this->_destroy($data);
                    $deletedList[] = $row['PersonToothCard_did'];
                }
                if (isset($row['PersonToothCard_id'])) {
                    // нужно сделать снова активными состояния,
                    // которые были деактивированы удаленными состояниями
                    unset($row['PersonToothCard_did']);
                    $row['PersonToothCard_endDate'] = null;
                    $row['pmUser_id'] = $this->promedUserId;
                    $this->_save($row);
                }
            }
            //throw new Exception(var_export($deletedList, true), 500);
            $this->commitTransaction();
        } catch (Exception $e) {
            $this->rollbackTransaction();
            $response[0]['Error_Msg'] = $e->getMessage();
            $response[0]['Error_Code'] = $e->getCode();
        }
        return $response;
    }

    /**
     * Отмена активных состояний, созданных в рамках посещения
     *
     * @param array $data Массив, полученный методом ProcessInputData контроллера
     * @return array
     * @throws Exception
     */
    public function doRemove($data)
    {
        $this->setOperation('doRemove');
        try {
            if (empty($data['EvnVizitPLStom_id'])) {
                throw new Exception('Не указан случай стоматологического посещения!', 400);
            }
            $this->setParams($data);
            $tmp = $this->_loadEvnVizitPLStomData($data['EvnVizitPLStom_id']);
            if (!$this->isAllowEdit($data['EvnVizitPLStom_id'], $tmp['Person_id'])) {
                throw new Exception('Разрешено только для последнего посещения!', 400);
            }
            $response = $this->doRemoveByEvn($data['EvnVizitPLStom_id'], 'EvnVizitPLStom');
        } catch (Exception $e) {
            $response = [[
                'Error_Msg' => $e->getMessage(),
                'Error_Code' => $e->getCode(),
            ]];
        }
        return $response;
    }

    /**
     * Разрешено только для последнего посещения
     * @param int $Evn_id
     * @param int $Person_id
     * @param int $EvnClass_id
     * @return Boolean
     * @throws Exception
     */
    function isAllowEdit($Evn_id, $Person_id, $EvnClass_id = 13)
    {
        if (empty($Evn_id)) {
            return false;
        }
        if (empty($Person_id)) {
            throw new Exception('Не указан человек!', 400);
        }
        $query = "
			select
			    e.Evn_id as \"Evn_id\"
			from
			    v_Evn e
			where
			    e.Person_id = :Person_id
            and
                EvnClass_id = :EvnClass_id
			order by e.Evn_setDT desc
			limit 1
		";
        $queryParams = ['Person_id' => $Person_id, 'EvnClass_id' => $EvnClass_id];
        $result = $this->db->query($query, $queryParams);
        if (!is_object($result)) {
            throw new Exception('Ошибка при запросе списка дат случаев стоматологического посещения!', 500);
        }
        
        $tmp = $result->result('array');
        if (empty($tmp)) {
            return false;
        }
        return ($Evn_id == $tmp[0]['Evn_id']);
    }

    /**
     * Возвращает данные для комбика "История"
     * панели просмотра и редактирования зубной карты.
     *
     * В истории отображаются даты сохраненных посещений,
     * содержащие состояния зубной карты.
     * @param array $data Массив, полученный методом ProcessInputData контроллера
     * @return array
     * @throws Exception
     */
    public function doLoadHistory($data)
    {
        $this->setOperation('doLoadHistory');
        if (empty($data['Person_id'])) {
            throw new Exception('Не указан человек!', 400);
        }
        $filter = '';
        $queryParams = ['Person_id' => $data['Person_id']];
        if (!empty($data['EvnVizitPLStom_id'])) {
            $filter = 'e.EvnVizitPLStom_id = :EvnVizitPLStom_id OR ';
            $queryParams['EvnVizitPLStom_id'] = $data['EvnVizitPLStom_id'];
        }
        $query = "
			select
				e.EvnVizitPLStom_id as \"EvnVizitPLStom_id\",
				e.EvnVizitPLStom_setDT as \"EvnVizitPLStom_setDT\"
			from
			    v_EvnVizitPLStom e
			where
			    e.Person_id = :Person_id
			and ({$filter} exists(
				select
				    p.PersonToothCard_id
				from
				    v_PersonToothCard p
				where
				    p.EvnVizitPLStom_id = e.EvnVizitPLStom_id
				limit 1
			))
			order by e.EvnVizitPLStom_setDT desc
		";
        $result = $this->db->query($query, $queryParams);
        if (is_object($result)) {
            $tmp = $result->result('array');
            return $this->_formatDatetimeFields($tmp, 'd.m.Y H:i');
        } else {
            throw new Exception('Ошибка при запросе списка дат случаев стоматологического посещения!', 500);
        }
    }

    /**
     * Получение данных зубной карты на дату-время посещения
     * для панели просмотра и редактирования зубной карты
     *
     * @param array $data Массив, полученный методом ProcessInputData контроллера
     * @return array возвращает данные состояний зубов созданные в рамках посещения и
     * другие состояния активные на момент посещения
     * @throws Exception
     */
    public function doLoadViewData($data)
    {
        if (empty($data['EvnVizitPLStom_id'])) {
            throw new Exception('Не указан случай стоматологического посещения!', 400);
        }
        $this->setOperation('doLoadViewData');
        $output = $this->_loadEvnVizitPLStomData($data['EvnVizitPLStom_id']);
        // считываем для шаблона данные зубной карты
        $this->load->library('parser');
        $data = $this->_getParseData($output);
        $output['ToothMap'] = $this->parser->parse('stom/ToothMap_layout', $data, true);
        return [$output];
    }

    /**
     * Получение данных для маркера
     * @param array $data
     * @return array|boolean
     */
    public function doLoadMarkerData($data)
    {
        $this->setOperation('doLoadMarkerData');
        if (empty($data['EvnVizitPLStom_id'])) {
            return false; //маркер должен быть замещен пустой строкой
        }
        try {
            return $this->_loadEvnVizitPLStomData($data['EvnVizitPLStom_id']);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Проверка наличия записей в зубной карте,
     * в рамках указанного посещения
     * @param int $EvnVizitPLStom_id
     * @return boolean
     * @throws Exception
     */
    public function hasPersonToothCard($EvnVizitPLStom_id)
    {
        $query = "
			select
			    p.EvnVizitPLStom_id as \"EvnVizitPLStom_id\"
			from
			    v_PersonToothCard p
			where
			    p.EvnVizitPLStom_id = :EvnVizitPLStom_id
			limit 1
		";
        $queryParams = ['EvnVizitPLStom_id' => $EvnVizitPLStom_id];
        $result = $this->db->query($query, $queryParams);
        if (!is_object($result)) {
            throw new Exception('Ошибка при проверке наличия зубной карты', 500);
        }
        
        return (count($result->result('array')) > 0);
    }

    /**
     * Получение данных зуба по коду для сохранения состояний
     * @param int $code
     * @return array
     * @throws Exception
     */
    private function _loadToothDataByCode($code)
    {
        $query = "
			select
				v_Tooth.Tooth_id as \"Tooth_id\",
				v_Tooth.Tooth_Code as \"Tooth_Code\",
				substring(cast(v_Tooth.Tooth_Code as varchar(2)), 2, 1) as \"Tooth_SysNum\",
				v_Tooth.JawPartType_id as \"JawPartType_id\"
			from
			    v_Tooth
			where
			    v_Tooth.Tooth_Code = :Tooth_Code
			limit 1
		";
        $result = $this->db->query($query, ['Tooth_Code' => $code]);
        if (!is_object($result)) {
            throw new Exception('Ошибка при запросе данных зуба по коду', 500);
        }
        
        return $result->result('array');
    }

    /**
     * Получение данных случая стоматологического посещения для отображения зубной карты
     * @param int $EvnVizitPLStom_id Вернутся данные указанного случая
     * @return array
     * @throws Exception
     */
    private function _loadEvnVizitPLStomData($EvnVizitPLStom_id)
    {
        $queryParams = array('EvnVizitPLStom_id' => $EvnVizitPLStom_id);
        $query = "
			select
				PS.Person_id as \"Person_id\",
				PS.Person_SurName as \"Person_SurName\",
				PS.Person_FirName as \"Person_FirName\",
				PS.Person_SecName as \"Person_SecName\",
				to_char(PS.Person_BirthDay, 'dd.mm.yyyy') as \"Person_BirthDay\",
				e.EvnVizitPLStom_id as \"EvnVizitPLStom_id\",
				mp.Person_Fin as \"MedPersonal_Fin\",
				to_char(e.EvnVizitPLStom_setDT, 'dd.mm.yyyy') as \"EvnVizitPLStom_setDate\",
				dbo.Age2(PS.Person_BirthDay, e.EvnVizitPLStom_setDT) as \"Person_Age\",
				e.EvnVizitPLStom_setDT as \"history_date\"
			from
			    v_EvnVizitPLStom e
                inner join v_PersonState PS on PS.Person_id = e.Person_id
                left join v_MedPersonal mp on mp.MedPersonal_id = e.MedPersonal_id
			where e.EvnVizitPLStom_id = :EvnVizitPLStom_id
			limit 1
		";
        $result = $this->db->query($query, $queryParams);
        if (!is_object($result)) {
            throw new Exception('Ошибка при запросе данных случая стоматологического посещения ', 500);
        }

        $tmp = $result->result('array');
        if (empty($tmp)) {
            throw new Exception('Данные случая стоматологического посещения не найдены' . " ({$this->regionNick})", 404);
        }
        $tmp = $this->_formatDatetimeFields($tmp);
        return $tmp[0];
    }

    /**
     * Получение данных зубной карты при отсутствии состояний в БД
     *
     * @param int $Person_age
     * @return array
     * @throws Exception
     */
    protected function _loadDefaultTooth($Person_age)
    {
        $where_clause = SwTooth::getDefaultFilter($Person_age);
        $query = "
			SELECT
				v_Tooth.Tooth_id as \"Tooth_id\",
				v_Tooth.Tooth_Code as \"Tooth_Code\",
				substring(cast(v_Tooth.Tooth_Code as varchar(2)), 2, 1) as \"Tooth_Num\",
				v_Tooth.JawPartType_id as \"JawPartType_Code\" -- === JawPartType_Code
			FROM
			    v_Tooth
			WHERE
				{$where_clause}
			ORDER BY
				v_Tooth.JawPartType_id, \"Tooth_Num\"
		";
        $result = $this->db->query($query, []);
        if (is_object($result)) {
            $tmp = $result->result('array');
            if (empty($tmp)) {
                throw new Exception('Данные зубной карты при отсутствии состояний в БД не найдены!', 404);
            }
            return $tmp;
        } else {
            throw new Exception('Ошибка при запросе данных зубной карты при отсутствии состояний в БД', 500);
        }
    }

    /**
     * Получение данных состояний для зубной карты
     * на указанную дату и время посещения
     *
     * Надо всегда выводить актуальную на дату-время посещения зубную карту
     * @param string $history_date
     * @param int $Person_id
     * @return array
     * @throws Exception
     */
    protected function _loadToothStates($history_date, $Person_id)
    {
        $queryParams = [
            'history_date' => $history_date,
            'Person_id' => $Person_id,
        ];
        // case when s.PersonToothCard_endDate is null then 1 else 0 end as isAllowDeactivate,
        $query = "
        with cte as (
            select cast(:history_date as timestamp) as history_date  
        )

		SELECT
			v_Tooth.Tooth_id as \"Tooth_id\",
			v_Tooth.Tooth_Code as \"Tooth_Code\",
			substring(cast(v_Tooth.Tooth_Code as varchar(2)), 2, 1) as \"Tooth_Num\",
			v_Tooth.JawPartType_id as \"JawPartType_Code\",
			s.PersonToothCard_id as \"PersonToothCard_id\",
			s.PersonToothCard_IsSuperSet as \"PersonToothCard_IsSuperSet\",
			s.ToothPositionType_aid as \"ToothPositionType_aid\",
			s.ToothPositionType_bid as \"ToothPositionType_bid\",
			s.ToothSurfaceType_id as \"ToothSurfaceType_id\",
			s.ToothStateClass_id as \"ToothStateClass_id\",
			st.ToothStateClass_Code as \"ToothStateClass_Code\",
			st.ToothStateClass_SysNick as \"ToothStateClass_SysNick\"
		FROM
		    v_PersonToothCard s
            inner join v_Tooth on v_Tooth.Tooth_id = s.Tooth_id
            inner join v_ToothStateClass st on st.ToothStateClass_id = s.ToothStateClass_id
		WHERE
		    s.Person_id = :Person_id
		and
		    s.PersonToothCard_begDate <= (select history_date from cte)
		and (
			s.PersonToothCard_endDate is null or
			s.PersonToothCard_endDate > (select history_date from cte)
		)
		ORDER BY
			v_Tooth.JawPartType_id, \"Tooth_Num\"
		";
        //echo getDebugSQL($query, $queryParams); exit();
        $result = $this->db->query($query, $queryParams);
        if (!is_object($result)) {
            throw new Exception('Ошибка при запросе данных зубной карты', 500);
        }

        $tmp = $result->result('array');
        /* в БД могли попасть несколько состояний с ToothStateClass_Code 1-4 для одного зуба
         * а должно быть только одно состояние с ToothStateClass_Code 1-4 для одного зуба
         * поэтому нужен этот фикс
         */
        $toothCode = null;
        $toothTypeCode = null;
        $result = [];
        foreach ($tmp as $row) {
            if ($row['Tooth_Code'] != $toothCode) {
                $toothCode = (int)$row['Tooth_Code'];
                $toothTypeCode = null;
            }
            $isToothType = false;
            if (is_numeric($row['ToothStateClass_Code'])
                && $row['ToothStateClass_Code'] > 0
                && $row['ToothStateClass_Code'] < 5
            ) {
                $isToothType = true;
            }
            if (false == $isToothType || empty($toothTypeCode)) {
                $result[] = $row;
            }
            if ($isToothType && empty($toothTypeCode)) {
                $toothTypeCode = $row['ToothStateClass_Code'];
            }
        }
        return $result;
    }

    /**
     * Получение данных активных состояний зубной карты
     * на текущую дату и время
     *
     * @param int $Person_id
     * @param int $JawPartType_id
     * @param int $Tooth_SysNum
     * @return array
     * @throws Exception
     */
    protected function _loadActiveToothStates($Person_id, $JawPartType_id = null, $Tooth_SysNum = null)
    {
        $queryParams = [
            'Person_id' => $Person_id,
        ];
        $where_clause = '';
        if (isset($JawPartType_id) && isset($Tooth_SysNum)) {
            $queryParams['JawPartType_id'] = $JawPartType_id;
            $queryParams['Tooth_SysNum'] = $Tooth_SysNum;
            $where_clause = "and v_Tooth.JawPartType_id = :JawPartType_id
			and substring(cast(v_Tooth.Tooth_Code as varchar(2)), 2, 1) = :Tooth_SysNum";
        }
        $query = "
			with cte as (select dbo.tzGetDate() as history_date)

			SELECT
				s.PersonToothCard_id as \"PersonToothCard_id\",
				s.Server_id as \"Server_id\",
				s.Person_id as \"Person_id\",
				s.EvnDiag_id as \"EvnDiag_id\",
				s.EvnUsluga_id as \"EvnUsluga_id\",
				s.EvnVizitPLStom_id as \"EvnVizitPLStom_id\",
				s.PersonToothCard_begDate as \"PersonToothCard_begDate\",
				s.PersonToothCard_endDate as \"PersonToothCard_endDate\",
				s.Tooth_id as \"Tooth_id\",
				s.PersonToothCard_IsSuperSet as \"PersonToothCard_IsSuperSet\",
				s.ToothPositionType_aid as \"ToothPositionType_aid\",
				s.ToothPositionType_bid as \"ToothPositionType_bid\",
				s.ToothSurfaceType_id as \"ToothSurfaceType_id\",
				s.ToothStateClass_id as \"ToothStateClass_id\"
			FROM
			    v_PersonToothCard s
				inner join v_Tooth on v_Tooth.Tooth_id = s.Tooth_id
				inner join Evn e on e.Evn_id = s.EvnVizitPLStom_id and coalesce(e.Evn_deleted, 1) = 1
			WHERE s.Person_id = :Person_id
				{$where_clause}
				and s.PersonToothCard_begDate < (select history_date from cte)
				and s.PersonToothCard_endDate is null
		";
        //echo getDebugSQL($query, $queryParams); exit();
        $result = $this->db->query($query, $queryParams);
        if (!is_object($result)) {
            throw new Exception('Ошибка при запросе данных зубной карты', 500);
        }

        $tmp = $result->result('array');
        return $this->_formatDatetimeFields($tmp);
    }

    /**
     * Получение зубной карты для печати
     * @param array $data Массив, полученный методом ProcessInputData контроллера
     * @return string
     * @throws Exception
     */
    public function doPrint($data)
    {
        $this->setOperation('doPrint');
        if (empty($data['EvnVizitPLStom_id'])) {
            throw new Exception('Не указан случай стоматологического посещения!', 400);
        }
        //будем выводить данные указанного случая
        $output = $this->_loadEvnVizitPLStomData($data['EvnVizitPLStom_id']);
        // считываем для шаблона данные зубной карты
        $this->load->library('parser');
        $data = $this->_getParseData($output);
        return $this->parser->parse('stom/ToothMap_layout', $data, true);
    }


    /**
     * Вывод изображения зубной карты
     *
     * Генерируем изображение зубной карты для отображения в документах и отчетах
     * @todo кэшировать
     */
    public function doOutputPng()
    {
        $this->setOperation('doOutputPng');
        $this->load->library('ToothMap');
        try {
            if (empty($_GET['EvnVizitPLStom_id'])) {
                //будем выводить "чистую" зубную карту
                $output = [
                    'Person_Age' => 18,
                    'Person_id' => null,
                    'history_date' => null,
                ];
                $toothStates = [];
            } else {
                //будем выводить данные указанного случая
                $output = $this->_loadEvnVizitPLStomData($_GET['EvnVizitPLStom_id']);
                // считываем данные зубной карты
                $toothStates = $this->_loadToothStates($output['history_date'], $output['Person_id']);
            }
            $defaultTooth = $this->_loadDefaultTooth($output['Person_Age']);
            ToothMap::applyData($toothStates, $defaultTooth, $output);
            ToothMap::output();
            return true;
        } catch (Exception $e) {
            ToothMap::outputError($e->getMessage());
            return false;
        }
    }

    /**
     * Метод
     * @param array $output
     * @return array
     * @throws Exception
     */
    protected function _getParseData(&$output)
    {
        $isForPrint = ($this->getOperation() == 'doPrint');
        $data = [];
        //данные для печати
        $data['EvnVizitPLStom_setDate'] = $output['EvnVizitPLStom_setDate'];
        $data['MedPersonal_Fin'] = $output['MedPersonal_Fin'];
        $data['Person_SurName'] = $output['Person_SurName'];
        $data['Person_FirName'] = $output['Person_FirName'];
        $data['Person_SecName'] = $output['Person_SecName'];
        $data['Person_BirthDay'] = $output['Person_BirthDay'];
        $data['Person_Age'] = $output['Person_Age'];
        //могут быть региональные отличия, поэтому беру из БД
        $types = $this->_loadToothStateClass();
        foreach ($types as $row) {
            $code = 'ToothStateClass_Code' . $row['ToothStateClass_id'];
            $name = 'ToothStateClass_Name' . $row['ToothStateClass_id'];
            $data[$code] = $row['ToothStateClass_Code'];
            $data[$name] = $row['ToothStateClass_Name'];
        }
        $toothStates = $this->_loadToothStates($output['history_date'], $output['Person_id']);
        if (!$isForPrint) {
            //значения для редактирования
            $output['ToothStateClassRelation'] = $types;
            $output['ToothStates'] = [];
            foreach ($toothStates as $row) {
                $code = $row['Tooth_Code'] . '';
                if (empty($output['ToothStates'][$code])) {
                    $output['ToothStates'][$code] = [
                        //для сохранения
                        'Tooth_id' => $row['Tooth_id'],
                        //для отображения
                        'JawPartType_Code' => $row['JawPartType_Code'],
                        'Tooth_Code' => (int)$row['Tooth_Code'],
                        'PersonToothCard_IsSuperSet' => $row['PersonToothCard_IsSuperSet'],
                        'ToothPositionType_aid' => $row['ToothPositionType_aid'],
                        'ToothPositionType_bid' => $row['ToothPositionType_bid'],
                        'states' => [],
                    ];
                }
                $output['ToothStates'][$code]['states'][] = [
                    'PersonToothCard_id' => $row['PersonToothCard_id'],
                    'ToothStateClass_id' => $row['ToothStateClass_id'],
                    'ToothSurfaceType_id' => $row['ToothSurfaceType_id'],
                ];
            }
        }
        $this->load->library('ToothMap');
        $defaultTooth = $this->_loadDefaultTooth($output['Person_Age']);
        ToothMap::$isForPrint = $isForPrint;
        $jawParts = ToothMap::processingToothStates($toothStates, $defaultTooth, $output);
        if (!$isForPrint) {
            //значения для редактирования по зубам, у которых нет состояний
            foreach ($defaultTooth as $row) {
                $code = $row['Tooth_Code'] . '';
                if (empty($output['ToothStates'][$code])) {
                    $output['ToothStates'][$code] = [
                        //для сохранения
                        'Tooth_id' => $row['Tooth_id'],
                        //для отображения + Tooth_Num ?
                        'JawPartType_Code' => $row['JawPartType_Code'],
                        'Tooth_Code' => (int)$row['Tooth_Code'],
                        'PersonToothCard_IsSuperSet' => NULL,
                        'ToothPositionType_aid' => NULL,
                        'ToothPositionType_bid' => NULL,
                        'states' => [],
                    ];
                }
            }
        }
        $jawPartTpl = 'stom/ToothMap_jaw_part';
        // правый верхний
        $data['JawPart1'] = $this->parser->parse($jawPartTpl, $jawParts['1'], true);
        // левый верхний
        $data['JawPart2'] = $this->parser->parse($jawPartTpl, $jawParts['2'], true);
        // левый нижний
        $data['JawPart3'] = $this->parser->parse($jawPartTpl, $jawParts['3'], true);
        // правый нижний
        $data['JawPart4'] = $this->parser->parse($jawPartTpl, $jawParts['4'], true);
        return $data;
    }

    /**
     * Загрузка разновидностей состояния зуба и их отношений
     *
     * Deactivate «Отменяет состояния» – какие состояния деактивируются установкой данного состояния.
     * Compatible «Совместимо с» - с какими состояниями данное состояние может существовать одновременно.
     * NoPermiss «Недопустимо для типа зуба» - для каких типов недопустимо добавление к ним.
     * OnlyTooth - применимо только к зубу
     * OnlySurface - применимо только к поверхности зуба
     * @return array
     * @throws Exception
     */
    protected function _loadToothStateClass()
    {
        if (!empty($this->_ToothStateClassRelation)) {
            return $this->_ToothStateClassRelation;
        }
        $query = "
			select
				s.ToothStateClass_id as \"ToothStateClass_id\",
				s.ToothStateClass_Code as \"ToothStateClass_Code\",
				s.ToothStateClass_Name as \"ToothStateClass_Name\",
				s.ToothStateClass_IsType as \"ToothStateClass_IsType\"
			from v_ToothStateClass s
		";
        $queryParams = [];
        $result = $this->db->query($query, $queryParams);
        if (is_object($result)) {
            $this->_ToothStateClassRelation = [];
            $tmp = $result->result('array');
            foreach ($tmp as $row) {
                $id = $row['ToothStateClass_id'];
                $stateClass = [
                    'ToothStateClass_id' => $row['ToothStateClass_id'],
                    'ToothStateClass_Code' => $row['ToothStateClass_Code'],
                    'ToothStateClass_Name' => $row['ToothStateClass_Name'],
                    'ToothStateClass_IsType' => (2 == $row['ToothStateClass_IsType']),
                    'FieldName' => '',// имя для чекбокса/группы чекбоксов или радиогруппы
                    'OnlyTooth' => true,// применимо только к зубу
                    'OnlySurface' => false,// применимо только к поверхности
                    'Deactivate' => [],// какие состояния/типы деактивируются установкой данного состояния
                    'Compatible' => [],// с какими состояниями данное состояние может существовать одновременно
                    'NoPermiss' => [],// для каких типов НЕ допустим либо переход в указанное состояние/тип, либо добавление к ним.
                ];
                switch ($id) {
                    case 2: // Кариес
                        $stateClass['FieldName'] = 'caries';
                        $stateClass['OnlyTooth'] = false;
                        $stateClass['OnlySurface'] = true;
                        $stateClass['Compatible'] = [3, 4, 5, 6, 7, 8, 9, 10];
                        //$stateClass['Compatible'][] = 1;
                        // Состояние не может быть добавлено к удаленному или искуственному зубу или корню
                        $stateClass['NoPermiss'] = [1, 14, 15];
                        break;
                    case 5: // Пломбированный
                        $stateClass['FieldName'] = 'seal';
                        $stateClass['OnlyTooth'] = false;
                        $stateClass['OnlySurface'] = true;
                        $stateClass['Compatible'] = [2, 3, 4, 6, 7, 8, 9, 10];
                        // Состояние не может быть добавлено к удаленному или искуственному зубу или корню
                        $stateClass['NoPermiss'] = [1, 14, 15];
                        break;
                    case 1: // Корень
                        $stateClass['FieldName'] = 'radix';
                        $stateClass['Deactivate'] = [2, 3, 5, 10];
                        $stateClass['Compatible'] = [4, 6, 7, 8, 9];
                        // Состояние не может быть добавлено к удаленному или искуственному зубу
                        $stateClass['NoPermiss'] = [14, 15];
                        break;
                    case 3: // Пульпит
                        $stateClass['FieldName'] = 'pulpitis';
                        $stateClass['Compatible'] = [2, 4, 5, 6, 7, 8, 9, 10];
                        // Состояние не может быть добавлено к удаленному или искуственному зубу
                        $stateClass['NoPermiss'] = [14, 15];
                        break;
                    case 4: // Периодонтит
                        $stateClass['FieldName'] = 'periodontitis';
                        $stateClass['Compatible'] = [1, 2, 3, 5, 6, 7, 8, 9, 10];
                        // Состояние не может быть добавлено к удаленному или искуственному зубу
                        $stateClass['NoPermiss'] = [14, 15];
                        break;
                    case 6: // Пародонтоз
                        $stateClass['FieldName'] = 'alveolysis';
                        $stateClass['Compatible'] = [1, 2, 3, 4, 5, 7, 8, 9, 10];
                        // Состояние не может быть добавлено к искуственному зубу
                        $stateClass['NoPermiss'] = [15];
                        break;
                    case 7: // Подвижность I степени
                        $stateClass['FieldName'] = 'mobility';
                        $stateClass['Deactivate'] = array(8, 9);
                        $stateClass['Compatible'] = array(1, 2, 3, 4, 5, 6, 10);
                        // Состояние не может быть добавлено к удаленному или искуственному зубу
                        $stateClass['NoPermiss'] = array(14, 15);
                        break;
                    case 8: // Подвижность II степени
                        $stateClass['FieldName'] = 'mobility';
                        $stateClass['Deactivate'] = [7, 9];
                        $stateClass['Compatible'] = [1, 2, 3, 4, 5, 6, 10];
                        // Состояние не может быть добавлено к удаленному или искуственному зубу
                        $stateClass['NoPermiss'] = [14, 15];
                        break;
                    case 9: // Подвижность III степени
                        $stateClass['FieldName'] = 'mobility';
                        $stateClass['Deactivate'] = [7, 8];
                        $stateClass['Compatible'] = [1, 2, 3, 4, 5, 6, 10];
                        // Состояние не может быть добавлено к удаленному или искуственному зубу
                        $stateClass['NoPermiss'] = [14, 15];
                        break;
                    case 10: // Коронка
                        $stateClass['FieldName'] = 'crown';
                        $stateClass['Compatible'] = [2, 3, 4, 5, 6, 7, 8, 9];
                        // Состояние не может быть добавлено к удаленному или искуственному зубу
                        $stateClass['NoPermiss'] = [14, 15];
                        break;
                    case 12: // Постоянный
                        $stateClass['FieldName'] = 'type';
                        // все предыдущие состояния должны стать неактивными
                        $stateClass['Deactivate'] = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 13, 14, 15];
                        $stateClass['Compatible'] = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];
                        // на месте искуственного не может вырасти постоянный
                        $stateClass['NoPermiss'] = [15];
                        break;
                    case 13: // Молочный
                        $stateClass['FieldName'] = 'type';
                        // все предыдущие состояния должны стать неактивными
                        $stateClass['Deactivate'] = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 12, 14, 15];
                        $stateClass['Compatible'] = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];
                        // на месте искуственного или постоянного не может вырасти молочный
                        $stateClass['NoPermiss'] = [12, 15];
                        break;
                    case 14:
                    case 15: // Удален (выпал, выбит), Искусственный
                        $stateClass['FieldName'] = 'type';
                        $stateClass['Deactivate'] = [1, 2, 3, 4, 5, 7, 8, 9, 10, 12];
                        if (14 == $id) {
                            // почти все предыдущие состояния должны стать неактивными
                            $stateClass['Deactivate'][] = 13;
                            $stateClass['Deactivate'][] = 15;
                            $stateClass['Compatible'][] = 6;
                        } else {
                            // все предыдущие состояния должны стать неактивными
                            $stateClass['Deactivate'][] = 6;
                            $stateClass['Deactivate'][] = 13;
                            $stateClass['Deactivate'][] = 14;
                            // молочный не может быть заменен искуственным
                            $stateClass['NoPermiss'][] = 13;
                        }
                        break;
                }
                $this->_ToothStateClassRelation[$id] = $stateClass;
            }
            //@todo кэшировать результат
            return $this->_ToothStateClassRelation;
        } else {
            throw new Exception('Ошибка при запросе списка разновидностей состояния зуба', 500);
        }
    }

    /**
     * Отмена или полное удаление записи "История по зубной карте"
     *
     * Сейчас в хранимке p_PersonToothCard_del проставляется признак PersonToothCard_deleted = 2
     * @param array $data
     * @throws Exception
     */
    protected function _destroy($data)
    {
        $query = "
			select
			    Error_Code as \"Error_Code\",
			    Error_Message as \"Error_Msg\"

			from p_PersonToothCard_del
			(
				PersonToothCard_id := :PersonToothCard_id,
				pmUser_id := :pmUser_id,
				IsRemove := :IsRemove
			);
		";
        $queryParams = [
            'PersonToothCard_id' => $data['PersonToothCard_id'],
            'pmUser_id' => $data['pmUser_id'],
            'IsRemove' => empty($data['IsRemove']) ? 1 : $data['IsRemove'],
        ];
        $result = $this->db->query($query, $queryParams);
        if (!is_object($result)) {
            throw new Exception('Ошибка при удалении записи');
        }
        $response = $result->result('array');
        if (!empty($response[0]['Error_Msg'])) {
            throw new Exception($response[0]['Error_Msg']);
        }
    }

    /**
     * Сохранение записи "История по зубной карте"
     *
     * В этой табличке можно обновить поля
     * PersonToothCard_endDate чтобы сделать запись активной/неактивной на текущий момент
     * PersonToothCard_Deleted чтобы была возможность восстановить отмененное состояние
     * @param array $data
     * @return array
     * @throws Exception
     */
    protected function _save($data = [])
    {
        if (!empty($data['EvnVizitPLStom_id'])) {
            // проверяем, что у нас в БД есть такой Evn_id.
            $resp_check = $this->queryResult("select Evn_id as \"Evn_id\" from Evn where Evn_id = :Evn_id limit 1 ",
                [
                    'Evn_id' => $data['EvnVizitPLStom_id']
                ]);

            if (empty($resp_check[0]['Evn_id'])) {
                throw new Exception('Указанное стомат. посещение не найдено в БД');
            }
        }

        $action = empty($data['PersonToothCard_id']) ? 'ins' : 'upd';
        $query = "
            select
                PersonToothCard_id as \"PersonToothCard_id\",
                Error_Code as \"Error_Code\",
                Error_Message as \"Error_Msg\"
			from p_PersonToothCard_{$action}
			(
				PersonToothCard_id := :PersonToothCard_id,
				Server_id := :Server_id,
				Person_id := :Person_id,
				EvnVizitPLStom_id := :EvnVizitPLStom_id,
				EvnUsluga_id := :EvnUsluga_id,
				EvnDiag_id := :EvnDiag_id,
				PersonToothCard_begDate := :PersonToothCard_begDate,
				PersonToothCard_endDate := :PersonToothCard_endDate,
				Tooth_id := :Tooth_id,
				PersonToothCard_IsSuperSet := :PersonToothCard_IsSuperSet,
				ToothPositionType_aid := :ToothPositionType_aid,
				ToothPositionType_bid := :ToothPositionType_bid,
				ToothSurfaceType_id := :ToothSurfaceType_id,
				ToothStateClass_id := :ToothStateClass_id,
				pmUser_id := :pmUser_id
			)
		";

        $queryParams = [
            'PersonToothCard_id' => empty($data['PersonToothCard_id']) ? NULL : $data['PersonToothCard_id'],
            'Tooth_id' => $data['Tooth_id'],
            'EvnVizitPLStom_id' => $data['EvnVizitPLStom_id'],
            'EvnUsluga_id' => empty($data['EvnUsluga_id']) ? NULL : $data['EvnUsluga_id'],
            'EvnDiag_id' => empty($data['EvnDiag_id']) ? NULL : $data['EvnDiag_id'],
            'PersonToothCard_IsSuperSet' => empty($data['PersonToothCard_IsSuperSet']) ? 1 : $data['PersonToothCard_IsSuperSet'],
            'ToothPositionType_aid' => empty($data['ToothPositionType_aid']) ? NULL : $data['ToothPositionType_aid'],
            'ToothPositionType_bid' => empty($data['ToothPositionType_bid']) ? NULL : $data['ToothPositionType_bid'],
            'ToothSurfaceType_id' => empty($data['ToothSurfaceType_id']) ? NULL : $data['ToothSurfaceType_id'],
            'PersonToothCard_begDate' => $data['PersonToothCard_begDate'],
            'PersonToothCard_endDate' => empty($data['PersonToothCard_endDate']) ? NULL : $data['PersonToothCard_endDate'],
            'ToothStateClass_id' => $data['ToothStateClass_id'],
            'Person_id' => $data['Person_id'],
            'Server_id' => $data['Server_id'],
            'pmUser_id' => $data['pmUser_id'],
            //'PersonToothCard_Deleted' => empty($data['PersonToothCard_Deleted']) ? 1 : $data['PersonToothCard_Deleted'],
        ];

        // echo getDebugSQL($query, $queryParams); exit();

        $result = $this->db->query($query, $queryParams);

        if (!is_object($result)) {
            throw new Exception('Ошибка при запросе к БД при сохранении истории', 500);
        }
        
        $response = $result->result('array');
        if (!empty($response[0]['Error_Msg'])) {
            throw new Exception($response[0]['Error_Msg'], 500);
        }
        return $response[0]['PersonToothCard_id'];
    }

    /**
     * Удаление записи  "состояние по зубной карте"
     *
     * Операция должна проводиться внутри транзакции
     * @param array $data
     * @return bool
     * @throws Exception
     */
    public function deleteState($data)
    {
        $recData = $this->getFirstRowFromQuery("
			select
				s.Person_id as \"Person_id\",
				s.ToothStateClass_id as \"ToothStateClass_id\",
				s.PersonToothCard_begDate as \"PersonToothCard_begDate\",
				s.PersonToothCard_endDate as \"PersonToothCard_endDate\",
				substring(cast(v_Tooth.Tooth_Code as varchar(2)), 2, 1) as \"Tooth_Num\",
				v_Tooth.JawPartType_id as \"JawPartType_id\"
			from
			    v_PersonToothCard s
				inner join v_Tooth on v_Tooth.Tooth_id = s.Tooth_id
			where s.PersonToothCard_id = :PersonToothCard_id
		", $data);
        if (empty($recData)) {
            throw new Exception('Что-то когда-то пошло не так, нужно обратиться к разработчикам программы!', 500);
        }
        $recData = $this->_formatDatetimeFields($recData);
        $this->_destroy($data);
        if (empty($recData['PersonToothCard_endDate'])) {
            /*
             * если это было активное состояние, то надо восстановить состояния,
             * которые были деактивированы установкой этого состояния
             */
            $id = $recData['ToothStateClass_id'];
            $rules = $this->_loadToothStateClass();
            if (empty($rules[$id])) {
                throw new Exception('Отсутствуют правила установки состояния с идентификатором ' . $id, 500);
            }
            if (empty($rules[$id]['Deactivate'])) {
                return true;
            }
            $deactivated = implode(', ', $rules[$id]['Deactivate']);
            $query = "
                with cte as (
				    select cast(:PersonToothCard_begDate as timestamp) as PersonToothCard_begDate
				)
				select
					s.PersonToothCard_id as \"PersonToothCard_id\",
					s.Server_id as \"Server_id\",
					s.Person_id as \"Person_id\",
					s.EvnDiag_id as \"EvnDiag_id\",
					s.EvnUsluga_id as \"EvnUsluga_id\",
					s.EvnVizitPLStom_id as \"EvnVizitPLStom_id\",
					s.PersonToothCard_begDate as \"PersonToothCard_begDate\",
					s.PersonToothCard_endDate as \"PersonToothCard_endDate\",
					s.Tooth_id as \"Tooth_id\",
					s.PersonToothCard_IsSuperSet as \"PersonToothCard_IsSuperSet\",
					s.ToothPositionType_aid as \"ToothPositionType_aid\",
					s.ToothPositionType_bid as \"ToothPositionType_bid\",
					s.ToothSurfaceType_id as \"ToothSurfaceType_id\",
					s.ToothStateClass_id as \"ToothStateClass_id\"
				from
				    v_PersonToothCard s
				    inner join v_Tooth on v_Tooth.Tooth_id = s.Tooth_id
				where
				    s.Person_id = :Person_id
                and
                    s.ToothStateClass_id in ({$deactivated})
                and
                    v_Tooth.JawPartType_id = :JawPartType_id
                and
                    substring(cast(v_Tooth.Tooth_Code as varchar(2)), 2, 1) = :Tooth_Num
                and
                    s.PersonToothCard_endDate = (select PersonToothCard_begDate from cte)
			";
            $queryParams = [
                'Person_id' => $recData['Person_id'],
                'Tooth_Num' => $recData['Tooth_Num'],
                'JawPartType_id' => $recData['JawPartType_id'],
                'PersonToothCard_begDate' => $recData['PersonToothCard_begDate'],
            ];
            $result = $this->db->query($query, $queryParams);
            if (!is_object($result)) {
                throw new Exception('Ошибка при запросе состояний зубной карты', 500);
            }
            $tmp = $result->result('array');
            $tmp = $this->_formatDatetimeFields($tmp);
            foreach ($tmp as $row) {
                $row['PersonToothCard_endDate'] = null;
                $row['pmUser_id'] = $data['pmUser_id'];
                $this->_save($row);
            }
        }
        return true;
    }

    /**
     * Осуществляет форматирование полей типа Datetime в заданном формате.
     *
     * @param array $response
     * @param string $format
     * @return array
     */
    protected function _formatDatetimeFields($response, $format = 'Y-m-d H:i:s')
    {
        array_walk_recursive($response, array($this, 'convertDatetimeToStr'), $format);
        return $response;
    }

    /**
     * Конвертирование даты-времени в строку
     */
    public function convertDatetimeToStr(&$var, $key, $format)
    {
        if (is_object($var)) {
            if ($var instanceof DateTime) {
                /**
                 * @var DateTime $var
                 */
                $var = $var->format($format);
            }
        }
    }
}
