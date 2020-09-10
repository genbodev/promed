<?php
require_once("Polka_PersonCard_model_get.php");
require_once("Polka_PersonCard_model_common.php");
require_once("Polka_PersonCard_model_save.php");
/**
 * Polka_PersonCard_model - модель, для работы с таблицей Personcard
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2009 Swan Ltd.
 * @author       Pshenitcyn Ivan aka IvP (ipshon@rambler.ru)
 * @version      03.06.2009
 *
 * @property CI_DB_driver $db
 */
class Polka_PersonCard_model extends SwPgModel
{
	public $dateTimeForm104 = "DD.MM.YYYY";
	public $dateTimeForm108 = "HH24:MI:SS";
	public $dateTimeForm120 = "YYYY-MM-DD HH24:MI:SS";
	public $dateTimeForm110 = "MM-DD-YYYY";
	public $dateTimeForm112 = "YYYYMMDD";
	public $dateTimeFormUnixDate = "YYYY-MM-DD";

	private $Lpu_RegNum = '000000';
	private $dirExport = '';
	private $isFopenCSV = false;
	private $isFcloseCSV = false;
	private $fpCSV = null;
	private $isExistFileCSV = false;
	private $fileNameCSV = '';
	private $filePathCSV = '';
	private $isFopenError = false;
	private $isFcloseError = false;
	private $fpError = null;
	private $isExistFileError = false;
	private $fileNameError = '';
	private $filePathError = '';
	private $dataOrgSMO = null;
	private $dataPolisType = null;
	private $csvFrameIsQuote = false;
	private $csvDummyStrCharacters = "#@ @#";

	function __construct()
	{
		parent::__construct();
	}

	#region private

    /**
     * Обработка данных для экспорта в файлы
     * @param $row
     * @param array $dataPolisType
     * @return array
     */
    private function _processingRowToFile($row, $dataPolisType = array()){
        // -------------------------------------------------------------------------
        $row['PolisFormType_Code'] = 1;
        if ($row['PolisFormType_Code'] === NULL) {
            $row['PolisFormType_Code'] = 0;
        }

        $PolisType_SysNick = $dataPolisType[$row['PolisType_id']]['PolisType_SysNick'];
        $PolisType_Name = $dataPolisType[$row['PolisType_id']]['PolisType_Name'];
        // -------------------------------------------------------------------------

        $data = array();


        // Атрибут "Действие"
        $data['action'] = 'Р';



        /*
            Атрибут "Код типа ДПФС:"
            П - Бумажный полис ОМС единого образца
            Э - Электронный полис ОМС единого образца
            В – Временное свидетельство
            С – Полис старого образца
            К – В составе УЭК
        */
        if ($PolisType_SysNick == 'OMS') {
            $data['DFPSType'] = 'С';
        } else if ($PolisType_SysNick == 'vremsvid') {
            $data['DFPSType'] = 'В';
        } else if ($PolisType_SysNick == 'OMS (new)' && in_array((int)$row['PolisFormType_Code'], array(0, 1))) {
            $data['DFPSType'] = 'П';
        } else if ($PolisType_SysNick == 'OMS (new)' && (int)$row['PolisFormType_Code'] == 2) {
            $data['DFPSType'] = 'Э';
        } else if ($PolisType_SysNick == 'OMS (new)' && (int)$row['PolisFormType_Code'] == 3) {
            $data['DFPSType'] = 'К';
        }





        // Серия и номер ДПФС
        if (in_array($PolisType_SysNick, array('OMS', 'vremsvid'))) {

            $data['ID_Polis'] = rtrim($row['Polis_Ser']) . ' № ' . rtrim($row['Polis_Num']);

            if($this->getRegionNick() == 'pskov'){
                if($PolisType_SysNick == 'vremsvid'){
                    $data['ID_Polis'] = rtrim($row['Polis_Ser']) . rtrim($row['Polis_Num']);
                }
            }


        } else {
            $data['ID_Polis'] = '';
        }



        // polis_info
        $data['polis_info'] = $PolisType_Name . ' ' . rtrim($row['Polis_Ser']) . ' № ' . rtrim($row['Polis_Num']);

        // Единый номер полиса ОМС
        $data['Person_edNum'] = $row['Person_edNum'];


        // Фамилия застрахованного лица
        $data['FAM'] = rtrim($row['Person_SurName']);//rtrim(strtoupper($row['Person_SurName']));
        // Имя застрахованного лица
        $data['IM'] = rtrim($row['Person_FirName']);//rtrim(strtoupper($row['Person_FirName']));
        // Отчество застрахованного лица
        $data['OT'] = rtrim($row['Person_Secname']);//rtrim(strtoupper($row['Person_Secname']));


        // Дата рождения застрахованного лица.
        $DR = '';
        if (!empty($row['DR'])) {
            $DR = $row['DR']->format('Ymd');
        }
        $data['DR'] = $DR;


        // Место рождения застрахованного лица.
        $data['Person_Birthplace'] = $row['Person_Birthplace'];


        // Тип документа, удостоверяющего личность.
        $data['DocumentType_Code'] = $row['DocumentType_Code'];


        // Номер или серия и номер документа, удостоверяющего личность.
        $data['Document_SerNum'] = rtrim($row['Document_Ser']) . ' № ' . rtrim($row['Document_Num']);
        if($this->getRegionNick() == 'pskov'){
            if(empty($row['DocumentType_Code'])){
                $data['Document_SerNum'] = '';
            }elseif ($data['DocumentType_Code'] == 14) {
                $ser = rtrim($row['Document_Ser']);
                $data['Document_SerNum'] = substr($ser,0,2).' '. substr($ser, -2, 2). ' № ' . rtrim($row['Document_Num']);
            }
        }

        // Дата выдачи документа, удостоверяющего личность.
        $Document_begDate = '';
        if (!empty($row['Document_begDate'])) {
            $Document_begDate = $row['Document_begDate']->format('Ymd');
        }
        $data['Document_begDate'] = $Document_begDate;


        // Наименование органа, выдавшего документ
        $data['OrgDep_Name'] = $row['Org_Name'];

        // СНИЛС застрахованного лица.
        $data['Person_Snils'] = $row['Person_Snils'];

        // Идентификатор МО
        $data['Lpu_f003mcod'] = $row['Lpu_f003mcod'];


        // Способ прикрепления
        if (!empty($row['PersonCardAttach_id'])) {
            $data['AttachSposob'] = 2;
        } else if ($row['PersonCard_IsAttachCondit'] == 2) {
            $data['AttachSposob'] = 1;
        } else {
            $data['AttachSposob'] = 0;
        }


        // Тип прикрепления
        $data['AttachType'] = '';

        // Дата прикрепления
        $PersonCard_begDate = '';
        if (!empty($row['PersonCard_begDate'])) {
            $PersonCard_begDate = $row['PersonCard_begDate']->format('Ymd');
        }
        $data['PersonCard_begDate'] = $PersonCard_begDate;


        // Дата открепления
        $PersonCard_endDate = '';
        if (!empty($row['PersonCard_endDate'])) {
            $PersonCard_endDate = $row['PersonCard_endDate']->format('Ymd');
        }
        $data['PersonCard_endDate'] = $PersonCard_endDate;


        // ОИД МО – уникальный идентификатор медицинской организации в реестре МО.
        $data['PassportToken_tid'] = $row['PassportToken_tid'];


        // Код подразделения
        $data['LpuSection_Code'] = '0';

        // Номер (код) участка
        $data['LpuRegion_Name'] = $row['LpuRegion_Name'];

        // СНИЛС медицинского работника
        $data['MedPersonal_Snils'] = $row['MedPersonal_Snils'];

        // Тип должности медицинского работника
        $data['code'] = $row['code'];



        //return $data;
        return $this->addDummyStrCharacters($data);
    }

	/**
	 * добавляем фиктивные строковые символы
	 * @param $data
	 * @return mixed
	 */
	function addDummyStrCharacters($data)
	{
		$dummyStrCharacters = $this->csvDummyStrCharacters;
		if (!$this->csvFrameIsQuote) return $data;
		foreach ($data as $key => $value) {
			$data[$key] = $value . $dummyStrCharacters;
		}
		return $data;
	}

	/**
	 * @return string
	 */
	private function _getDirExport()
	{
		if (empty($this->dirExport)) {
			$this->_createDirExport();
		}
		return $this->dirExport;
	}

	/**
	 * каталог в котором лежат выгружаемые файлы
	 * @return string
	 */
	private function _createDirExport()
	{
		if (!file_exists(EXPORTPATH_ATACHED_LIST)) {
			mkdir(EXPORTPATH_ATACHED_LIST);
		}
		$this->dirExport = EXPORTPATH_ATACHED_LIST . "csv_" . time() . "_" . "attachedList";
		mkdir($this->dirExport);
		return $this->dirExport;
	}

	/**
	 * @return string
	 */
	private function _getFileNameCSV()
	{
		return $this->fileNameCSV;
	}

	/**
	 * @return string
	 */
	private function _getFilePathCSV()
	{
		return $this->filePathCSV;
	}

	/**
	 * @return string
	 */
	private function _getFileNameError()
	{
		return $this->fileNameError;
	}

	/**
	 * @return string
	 */
	private function _getFilePathError()
	{
		return $this->filePathError;
	}

	/**
	 * Забываем данные экспорта
	 * @return bool
	 */
	private function _resetAttachedListCSV()
	{
		$this->_resetAttachedListFileCSV();
		$this->_resetAttachedListFileError();
		$this->Lpu_RegNum = "000000";
		$this->dataOrgSMO = null;
		$this->dataPolisType = null;
		return true;
	}

	/**
	 * Забываем данные файла CSV
	 * @return bool
	 */
	private function _resetAttachedListFileCSV()
	{
		$this->isFopenCSV = false;
		$this->isFcloseCSV = false;
		$this->fpCSV = null;
		$this->fileNameCSV = "";
		$this->filePathCSV = "";
		$this->isExistFileCSV = false;
		return true;
	}

	/**
	 * Забываем данные файла ошибок
	 * @return bool
	 */
	private function _resetAttachedListFileError()
	{
		$this->isFopenError = false;
		$this->isFcloseError = false;
		$this->fpError = null;
		$this->fileNameError = "";
		$this->filePathError = "";
		$this->isExistFileError = false;
		return true;
	}

	/**
	 * @return array|bool|null
	 */
	private function _getDataOrgSMO()
	{
		if (!empty($this->dataOrgSMO)) {
			return $this->dataOrgSMO;
		}
		$query = "
			select
				OrgSMO_id as \"OrgSMO_id\",
			    KLRgn_id as \"KLRgn_id\"
			from v_OrgSMO
		";
		$result = $this->db->query($query);
		if (!isset($result->result_id)) {
			return false;
		}
		$dataOrgSMO = [];
		while ($row = sqlsrv_fetch_array($result->result_id, SQLSRV_FETCH_ASSOC)) {
			$dataOrgSMO[$row["OrgSMO_id"]] = $row;
		}
		$result->result_id = null;
		unset($result->result_id);
		$result = null;
		unset($result);
		$this->dataOrgSMO = $dataOrgSMO;
		unset($dataOrgSMO);
		return $this->dataOrgSMO;
	}


	/**
	 * @return array|bool|null
	 */
	private function _getDataPolisType()
	{
		if (!empty($this->dataPolisType)) {
			return $this->dataPolisType;
		}
		$query = "
			select
				PolisType_id as \"PolisType_id\",
			    PolisType_SysNick as \"PolisType_SysNick\",
			    PolisType_Name as \"PolisType_Name\"
			from v_PolisType
		";
		$result = $this->db->query($query);
		if (!isset($result->result_id)) {
			return false;
		}
		$dataPolisType = [];
		while ($row = sqlsrv_fetch_array($result->result_id, SQLSRV_FETCH_ASSOC)) {
			$dataPolisType[$row["PolisType_id"]] = $row;
		}
		$result->result_id = null;
		unset($result->result_id);
		$result = null;
		unset($result);
		$this->dataPolisType = $dataPolisType;
		unset($dataPolisType);
		return $this->dataPolisType;
	}

	/**
	 * @param $AttachLpu_id
	 * @return bool
	 */
	private function _createLpu_RegNum($AttachLpu_id)
	{
		$Lpu_RegNum = "000000";
		if (!empty($AttachLpu_id)) {
			$query = "
				select Lpu_f003mcod as \"Lpu_f003mcod\"
				from v_Lpu
				where Lpu_id = :AttachLpu_id
				limit 1
			";
			$queryParams = ["AttachLpu_id" => $AttachLpu_id];
			$Lpu_RegNum = $this->getFirstResultFromQuery($query, $queryParams);
		}
		$this->Lpu_RegNum = $Lpu_RegNum;
		return true;
	}

	/**
	 * @return string
	 */
	private function _getLpu_RegNum()
	{
		return $this->Lpu_RegNum;
	}

	/**
	 * @return string
	 */
	private function _createFileCSV()
	{
		if ($this->isExistFileCSV != true) {
			$Lpu_RegNum = $this->_getLpu_RegNum();
			$this->fileNameCSV = "MO2" . $Lpu_RegNum . date('Ymd', time());
			// Создаем директорию
			$dirExport = $this->_getDirExport();
			$this->filePathCSV = "{$dirExport}/{$this->fileNameCSV}.csv";
			$this->isExistFileCSV = true;
		}
		return true;
	}

	/**
	 * @return bool|null|resource
	 */
	private function _doFopenCSV()
	{
		if ($this->isFopenCSV != true) {
			$this->_createFileCSV();
			$attached_list_file_path = $this->_getFilePathCSV();
			$this->fpCSV = fopen($attached_list_file_path, "w");
			$this->isFopenCSV = true;
		}
		return $this->fpCSV;
	}

	/**
	 * @return bool
	 */
	private function _doFcloseCSV()
	{
		fclose($this->fpCSV);
		$this->isFcloseCSV = true;
		return true;
	}

	/**
	 * @param $data
	 * @param bool $convertUTF8ToWin1251
	 * @return bool
	 */
	private function _putRowToFileCSV($data, $convertUTF8ToWin1251 = true)
	{
		if ($convertUTF8ToWin1251 == true) {
			array_walk_recursive($data, "ConvertFromUTF8ToWin1251", true);
		}
		$fp = $this->_doFopenCSV();
		fputs($fp, '"' . implode('";"', toAnsi($data)) . '"' . "\r\n");
		return true;
	}

	/**
	 * @param $row
	 * @param int $i
	 * @return string
	 */
	private function _processingRowToFileError($row, $i = 0)
	{
		$error = "\r\n№ {$i}\r\n" .
			'ФИО: ' . $row['FAM'] . ' ' . $row['IM'] . ' ' . $row['OT'] . "\r\n" .
			'ДР: ' . $row['DR'] . "\r\n" .
			'СНИЛС: ' . $row['Person_Snils'] . "\r\n" .
			'Полис: ' . $row['polis_info'] . "\r\n" .
			'Дата прикрепления: ' . $row['PersonCard_begDate'] . "\r\n" .
			'Дата открепления: ' . $row['PersonCard_endDate'] . "\r\n" .
			'Номер участка: ' . $row['LpuRegion_Name'] . "\r\n
		";
		return $error;
	}

	/**
	 * @return bool|null|resource
	 */
	private function _doFopenError()
	{
		if ($this->isFopenError != true) {
			$this->_createFileError();
			$attached_list_errors_file_path = $this->_getFilePathError();
			$this->fpError = fopen($attached_list_errors_file_path, "w");
			$this->isFopenError = true;
		}
		return $this->fpError;
	}

	/**
	 * @return bool
	 */
	private function _doFcloseError()
	{
		fclose($this->fpError);
		$this->isFcloseError = true;
		return true;
	}

	/**
	 * Создаем файл ошибок
	 * @return bool
	 */
	private function _createFileError()
	{
		if ($this->isExistFileError != true) {
			$Lpu_RegNum = $this->_getLpu_RegNum();
			$this->fileNameError = "MO2" . $Lpu_RegNum . date("Ymd", time()) . "_0_" . iconv("utf-8", "cp866", "ошибки");
			$dirExport = $this->_getDirExport();
			$this->filePathError = $dirExport . "/" . $this->fileNameError . ".txt";
			$this->isExistFileError = true;
		}
		return true;
	}

	/**
	 * Записываем ошибки в файл
	 * @param $error
	 * @return bool
	 */
	private function _putRowToFileError($error)
	{
		$fp = $this->_doFopenError();
		fwrite($fp, $error);
		return true;
	}

	/**
	 * Переименовываем файл ошибок для того, чтобы название содержало кол-во ошибок
	 * @param int $count_errors
	 * @return bool
	 */
	private function _renameFileError($count_errors = 0)
	{
		$fileName = $this->_getFileNameError();
		$filePath = $this->_getFilePathError();
		if (!file_exists($filePath)) {
			return false;
		}
		$fileNameNew = str_replace("_0_", "_" . ($count_errors) . "_", $fileName);
		$this->fileNameError = $fileNameNew;
		$filePathNew = str_replace("_0_", "_" . ($count_errors) . "_", $filePath);
		$this->filePathError = $filePathNew;
		file_put_contents($filePathNew, file_get_contents($filePath));
		return true;
	}
	#endregion private
	#region get
	/**
	 * Получение данных по последней карте
	 * @param $data
	 * @return array|bool
	 */
	function getOldPersonCard($data)
	{
		return Polka_PersonCard_model_get::getOldPersonCard($this, $data);
	}

	/**
	 * Получение количества прикреплений пациента к МО
	 * @param $data
	 * @return bool|mixed
	 */
	function getCountAttachPersonInLpu($data)
	{
		return Polka_PersonCard_model_get::getCountAttachPersonInLpu($this, $data);
	}

	/**
	 * Получение чего-то
	 * @param $data
	 * @return bool|mixed
	 */
	function getCountDetachPersonInLpu($data)
	{
		return Polka_PersonCard_model_get::getCountDetachPersonInLpu($this, $data);
	}

	/**
	 * Возвращает список карт по заданным фильтрам
	 * @param $data
	 * @return array|bool
	 */
	function getPersonCardHistoryList($data)
	{
		return Polka_PersonCard_model_get::getPersonCardHistoryList($this, $data);
	}

	/**
	 * Возвращает тип ЛПУ по возрасту (справочник MesAgeLpuType)
	 * @param $data
	 * @return bool|mixed
	 */
	function getLpuAgeType($data)
	{
		return Polka_PersonCard_model_get::getLpuAgeType($this, $data);
	}

	/**
	 * Возвращает идентификатор типа участка
	 * @param $data
	 * @return bool|mixed
	 */
	function getLpuRegionType($data)
	{
		return Polka_PersonCard_model_get::getLpuRegionType($this, $data);
	}

	/**
	 * Получение данных для печати амбулаторной карты
	 * @param $data
	 * @return array|bool
	 */
	function getMedCard($data)
	{
		return Polka_PersonCard_model_get::getMedCard($this, $data);
	}

	/**
	 * Получение данных по амбулаторной карте
	 * @param $data
	 * @return array|bool|false
	 */
	function getPersonCard($data)
	{
		return Polka_PersonCard_model_get::getPersonCard($this, $data);
	}

	/**
	 * Получение номера участка, в рамках задачи 9295
	 * @param $data
	 * @return array|bool|CI_DB_result
	 */
	function getLpuRegion($data)
	{
		return Polka_PersonCard_model_get::getLpuRegion($this, $data);
	}

	/**
	 * Получение участка по адресу человека
	 * @param $data
	 * @return array|bool
	 */
	function getLpuRegionByAddress($data)
	{
		return Polka_PersonCard_model_get::getLpuRegionByAddress($this, $data);
	}

	/**
	 * Получение списка файлов, прикрепленных к карте
	 * @param $data
	 * @return array|bool
	 */
	function getFilesOnPersonCardAttach($data)
	{
		return Polka_PersonCard_model_get::getFilesOnPersonCardAttach($this, $data);
	}

	/**
	 * Получение номера карты
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	function getPersonCardCode($data)
	{
		return Polka_PersonCard_model_get::getPersonCardCode($this, $data);
	}

	/**
	 * Получение данных для журнала движения
	 * @param $data
	 * @return array|bool
	 */
	function getPersonCardStateGrid($data)
	{
		return Polka_PersonCard_model_get::getPersonCardStateGrid($this, $data);
	}

	/**
	 * Получение каких-то данных
	 * @param $data
	 * @return array|bool
	 */
	function getPersonCardDetailList($data)
	{
		return Polka_PersonCard_model_get::getPersonCardDetailList($this, $data);
	}

	/**
	 * Получение количества людей прикрепленных к ЛПУ
	 * @param $data
	 * @return array|bool
	 */
	function getPersonCardCount($data)
	{
		return Polka_PersonCard_model_get::getPersonCardCount($this, $data);
	}

	/**
	 * Получение массива номеров участко, которые обслуживают человека с заданным адресом в данном ЛПУ
	 * @param $Person_id
	 * @param $Lpu_id
	 * @param $KLStreet_id
	 * @param $Address_House
	 * @return array|bool
	 */
	function getPersonRegionList($Person_id, $Lpu_id, $KLStreet_id, $Address_House)
	{
		return Polka_PersonCard_model_get::getPersonRegionList($this, $Person_id, $Lpu_id, $KLStreet_id, $Address_House);
	}

	/**
	 * Получение перс. данных пациента
	 * @param $data
	 * @return array|bool
	 */
	function getPersonData($data)
	{
		return Polka_PersonCard_model_get::getPersonData($this, $data);
	}

	/**
	 * Получение доп. информации по карте
	 * @param $data
	 * @return array|bool
	 */
	function getPersonCardAttachOnPersonCard($data)
	{
		return Polka_PersonCard_model_get::getPersonCardAttachOnPersonCard($this, $data);
	}

	/**
	 * Получение значения поля персональной инф-ции из портала "к варчу"
	 * @param $data
	 * @return bool
	 */
	function getPersonInfoKVRACHU($data)
	{
		return Polka_PersonCard_model_get::getPersonInfoKVRACHU($this, $data);
	}

	/**
	 * Получение последнего статуса заявления
	 * @param $data
	 * @return array|bool
	 */
	function getPersonCardAttachStatus($data)
	{
		return Polka_PersonCard_model_get::getPersonCardAttachStatus($this, $data);
	}

	/**
	 * Получение списка всех статусов заявления
	 * @param $data
	 * @return array|bool
	 */
	function getPersonCardAttachStatusesHistory($data)
	{
		return Polka_PersonCard_model_get::getPersonCardAttachStatusesHistory($this, $data);
	}

	/**
	 * @param $data
	 * @return array
	 */
	function getInfoForAttachesFile($data)
	{
		return Polka_PersonCard_model_get::getInfoForAttachesFile($this, $data);
	}

	/**
	 * Получение данных для рассылки уведомлений
	 * @param $person_id
	 * @param $old_lpu_id
	 * @param $new_lpu_id
	 * @return array
	 */
	function getDataForMessages($person_id, $old_lpu_id, $new_lpu_id)
	{
		return Polka_PersonCard_model_get::getDataForMessages($this, $person_id, $old_lpu_id, $new_lpu_id);
	}

	/**
	 * Возвращает список прикреплений пациента
	 * @param $data
	 * @return array|bool
	 */
	public function getPersonAttach($data)
	{
		return Polka_PersonCard_model_get::getPersonAttach($this, $data);
	}

	/**
	 * Возвращает список изменений прикреплений пациентов за период
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	public function getPersonAttachList($data)
	{
		return Polka_PersonCard_model_get::getPersonAttachList($this, $data);
	}

	/**
	 * Получение согласия на обработку персональных данных
	 * @param $data
	 * @return array|false
	 */
	public function getPersonLpuInfoIsAgree($data)
	{
		return Polka_PersonCard_model_get::getPersonLpuInfoIsAgree($this, $data);
	}

	/**
	 * Получение списка прикреплений.  метод для API
	 * @param $data
	 * @return array|bool
	 */
	function getPersonCardAPI($data)
	{
		return Polka_PersonCard_model_get::getPersonCardAPI($this, $data);
	}

	/**
	 * Справочник RecMethodType
	 * @return array|false
	 */
	function getRecMethodTypeCombo()
	{
		return Polka_PersonCard_model_get::getRecMethodTypeCombo($this);
	}
	
	#endregion get
	#region common
	/**
	 * Список прикрепленного населения к указанной СМО на указанную дату
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	function loadAttachedList($data)
	{
		return Polka_PersonCard_model_common::loadAttachedList($this, $data);
	}

	/**
	 * Список прикрепленного населения к указанной СМО на указанную дату
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	function loadAttachedListCSV($data)
	{
		return Polka_PersonCard_model_common::loadAttachedListCSV($this, $data);
	}

	/**
	 * Получение данных для редактирования заявления на прикрепление
	 * @param $data
	 */
	function loadPersonCardAttachForm($data)
	{
		return Polka_PersonCard_model_common::loadPersonCardAttachForm($this, $data);
	}

	/**
	 * @param $data
	 * @return array|bool
	 */
	function loadPersonCardMedicalInterventGrid($data)
	{
		return Polka_PersonCard_model_common::loadPersonCardMedicalInterventGrid($this, $data);
	}

	/**
	 * Получение списка заявлений о выборе МО
	 * @param $data
	 * @return array|bool
	 */
	function loadPersonCardAttachGrid($data)
	{
		return Polka_PersonCard_model_common::loadPersonCardAttachGrid($this, $data);
	}

	/**
	 * Берет название СМО для печати списка прикрепленного населения
	 * @param $data
	 * @return array|bool
	 */
	function printAttachedList($data)
	{
		return Polka_PersonCard_model_common::printAttachedList($this, $data);
	}

	/**
	 * Получение данных для экспорта амбулаторных карт в DBF
	 * @param $data
	 * @return array|bool
	 */
	function ExportPCToDBF($data)
	{
		return Polka_PersonCard_model_common::ExportPCToDBF($this, $data);
	}

	/**
	 * Запрос для выгрузки списка прикрепленного населения за период
	 * @param $data
	 * @return CI_DB_result
	 */
	function exportPersonAttaches($data)
	{
		return Polka_PersonCard_model_common::exportPersonAttaches($this, $data);
	}

	/**
	 * Выгрузка списка прикрепленного населения за период
	 * @param $data
	 * @return bool|CI_DB_result
	 */
	function exportPersonCardForPeriod($data)
	{
		return Polka_PersonCard_model_common::exportPersonCardForPeriod($this, $data);
	}

	/**
	 * Поиск участка прикрепления человека по адресу проживания в заданной ЛПУ
	 * @param $sStreet
	 * @param $sHouse
	 * @param $Lpu_id
	 * @return array
	 */
	function FindAddressRegionsIDByAddress($sStreet, $sHouse, $Lpu_id)
	{
		return Polka_PersonCard_model_common::FindAddressRegionsIDByAddress($this, $sStreet, $sHouse, $Lpu_id);
	}

	/**
	 * Поиск участка прикрепления человека по указанному в картотеке участку в заданной ЛПУ
	 * @param $Person_id
	 * @param $Lpu_id
	 * @return array
	 */
	function FindAddressRegionsIDByPersonCard($Person_id, $Lpu_id)
	{
		return Polka_PersonCard_model_common::FindAddressRegionsIDByPersonCard($this, $Person_id, $Lpu_id);
	}

	/**
	 * Открепление пациента
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	function closePersonCard($data)
	{
		return Polka_PersonCard_model_common::closePersonCard($this, $data);
	}

	/**
	 * Проверка возможности добавить льготу пациенту
	 * @param $data
	 * @return bool
	 */
	function allowAddPrivilegeChild($data)
	{
		return Polka_PersonCard_model_common::allowAddPrivilegeChild($this, $data);
	}
	#endregion common
	#region save
	/**
	 * Сохранение амбулаторной карты пациента
	 * @param $data
	 * @param bool $api
	 * @return array|bool
	 * @throws Exception
	 */
	function savePersonCard($data, $api = false)
	{
		return Polka_PersonCard_model_save::savePersonCard($this, $data, $api);
	}

	/**
	 * Сохранение участка для прикрепления
	 * @param $data
	 * @return array|bool
	 */
	function savePersonCardLpuRegion($data)
	{
		return Polka_PersonCard_model_save::savePersonCardLpuRegion($this, $data);
	}

	/**
	 * Сохранение амбулаторной карты пациента по ДМС
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	function savePersonCardDms($data)
	{
		return Polka_PersonCard_model_save::savePersonCardDms($this, $data);
	}

	/**
	 * Сохранение прикрепления для формы автоприкрепления
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	function SavePersonCardAuto($data)
	{
		return Polka_PersonCard_model_save::SavePersonCardAuto($this, $data);
	}

	/**
	 * Сохранение заявления о выборе МО
	 * @param $data
	 * @return array|bool|CI_DB_result
	 */
	function savePersonCardAttach($data)
	{
		return Polka_PersonCard_model_save::savePersonCardAttach($this, $data);
	}

	/**
	 * Сохранение статуса заявления о выборе МО
	 * @param $data
	 * @return array|bool
	 */
	function savePersonCardAttachStatus($data)
	{
		return Polka_PersonCard_model_save::savePersonCardAttachStatus($this, $data);
	}

	/**
	 * Сохранение данных об отказе от видов медицинского вмешательства
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	function savePersonCardMedicalInterventData($data)
	{
		return Polka_PersonCard_model_save::savePersonCardMedicalInterventData($this, $data);
	}

	/**
	 * Сохранение отказа от видов медицинского вмешательства
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	function savePersonCardMedicalIntervent($data)
	{
		return Polka_PersonCard_model_save::savePersonCardMedicalIntervent($this, $data);
	}

	/**
	 * Удаление карты
	 * @param $data
	 * @param bool $api
	 * @return array|bool
	 */
	function deletePersonCard($data, $api = false)
	{
		return Polka_PersonCard_model_save::deletePersonCard($this, $data, $api);
	}

	/**
	 * Удаление карты (ДМС)
	 * @param $data
	 * @return array|bool
	 */
	function deleteDmsPersonCard($data)
	{
		return Polka_PersonCard_model_save::deleteDmsPersonCard($this, $data);
	}

	/**
	 * Удаление всех отказов от мед.вмешательств по PersonCard_id
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	function deleteAllPersonCardMedicalIntervent($data)
	{
		return Polka_PersonCard_model_save::deleteAllPersonCardMedicalIntervent($this, $data);
	}

	/**
	 * Удаление отказа от видов медицинского вмешательства
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	function deletePersonCardMedicalIntervent($data)
	{
		return Polka_PersonCard_model_save::deletePersonCardMedicalIntervent($this, $data);
	}

	/**
	 * Удаление заявления о выборе МО
	 * @param $data
	 * @return array|bool
	 */
	function deletePersonCardAttach($data)
	{
		return Polka_PersonCard_model_save::deletePersonCardAttach($this, $data);
	}

	/**
	 * Удаление статуса заявления о выборе МО из журнала статусов
	 * @param $data
	 * @return array|bool
	 */
	function deletePersonCardAttachStatus($data)
	{
		return Polka_PersonCard_model_save::deletePersonCardAttachStatus($this, $data);
	}

	/**
	 * удаляем фиктивные строковые символы из файла
	 * @param $data
	 * @return bool|int
	 */
	function delDummyStrCharacters($data)
	{
		return Polka_PersonCard_model_save::delDummyStrCharacters($this, $data);
	}

	/**
	 * @param $data
	 * @return array|bool
	 */
	function checkPersonCard($data)
	{
		return Polka_PersonCard_model_save::checkPersonCard($this, $data);
	}

	/**
	 * Проверка номера карты на уникальность
	 * @param $data
	 * @return bool
	 * @throws Exception
	 */
	function checkPersonCardUniqueness($data)
	{
		return Polka_PersonCard_model_save::checkPersonCardUniqueness($this, $data);
	}

	/**
	 * Получение какой-то информации
	 * @param $data
	 * @return array|bool
	 */
	function checkIfPersonCardIsExists($data)
	{
		return Polka_PersonCard_model_save::checkIfPersonCardIsExists($this, $data);
	}

	/**
	 * Проверка наличия активного прикрепления в другом ЛПУ (для задачи https://redmine.swan.perm.ru/issues/62755)
	 * @param $data
	 * @return bool
	 */
	function checkAttachExists($data)
	{
		return Polka_PersonCard_model_save::checkAttachExists($this, $data);
	}

	/**
	 * Проверка на фондодержание
	 * @param $data
	 * @return bool
	 */
	function checkLpuFondHolder($data)
	{
		return Polka_PersonCard_model_save::checkLpuFondHolder($this, $data);
	}

	/**
	 * Проверка возможности прикрепления
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	function checkAttachPosible($data)
	{
		return Polka_PersonCard_model_save::checkAttachPosible($this, $data);
	}

	/**
	 * Проверка номера карты
	 * @param $data
	 * @return array|bool
	 */
	function checkPersonCardCode($data)
	{
		return Polka_PersonCard_model_save::checkPersonCardCode($this, $data);
	}

	/**
	 * Проверка наличия дисп карт в другом МО (по гинекологии) https://redmine.swan.perm.ru/issues/72643
	 * @param $data
	 * @return array|bool
	 */
	function checkPersonDisp($data)
	{
		return Polka_PersonCard_model_save::checkPersonDisp($this, $data);
	}
	#endregion save
}