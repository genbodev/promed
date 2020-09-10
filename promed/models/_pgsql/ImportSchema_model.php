<?php

/**
 * Class ImportSchema_model
 * @property DbfImporter_model DbfImporter_model
 * @property RegisterListLog_model RegisterListLog_model
 * @property RegisterListDetailLog_model RegisterListDetailLog_model
 */
class ImportSchema_model extends swPgModel {
	const SESSION_PROGRESS_REFRESH_RATE = 500; //Частота обновления прогресса в сессии
	const SESSION_VARNAME = 'IMPORT_SCHEMA_STATUS';

	private $_sessionProgressLastTimeUpdated = 0;
	private $progressPersentage;
	private $title;
	private $progressTitle;
	private $sessionActive;
	private $pmUser_id;
	private $Err = 0;
	public	$privilegeClose = 0;
	private $YesrRef;
	private $verLoad;

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
		$this->load->database();
		$this->load->model('RegisterListLog_model', 'RegisterListLog_model');
		$this->load->model('RegisterListDetailLog_model', 'RegisterListDetailLog_model');
		$this->session_active = isset($_SESSION);
		$this->load->library('textlog', array('file' => 'ImportSchema.log'));
	}

	/**
	 * Создание исключений по ошибкам
	 */
	function exceptionErrorHandler($errno, $errstr, $errfile, $errline) {
		switch ($errno) {
			case E_NOTICE:
			case E_USER_NOTICE:
				$errors = "Notice";
				break;
			case E_WARNING:
			case E_USER_WARNING:
				$errors = "Warning";
				break;
			case E_ERROR:
			case E_USER_ERROR:
				$errors = "Fatal Error";
				break;
			default:
				$errors = "Unknown Error";
				break;
		}

		$msg = sprintf("%s:  %s in %s on line %d", $errors, $errstr, $errfile, $errline);
		throw new ErrorException($msg, 0, $errno, $errfile, $errline);
	}

	/**
	 * Обработка Fatal Error
	 */
	function shutdownErrorHandler($func) {
		$this->textlog->add('test shutdownErrorHandler');
		$error = error_get_last();

		if (!empty($error)) {
			switch ($error['type']) {
				case E_NOTICE:
				case E_USER_NOTICE:
					$type = "Notice";
					break;
				case E_WARNING:
				case E_USER_WARNING:
					$type = "Warning";
					break;
				case E_ERROR:
				case E_USER_ERROR:
					$type = "Fatal Error";
					break;
				default:
					$type = "Unknown Error";
					break;
			}

			$msg = sprintf("%s:  %s in %s on line %d", $type, $error['message'], $error['file'], $error['line']);

			//$func($msg);
			call_user_func($func, $msg);

			exit($error['type']);
		}
	}

	/**
	 * 	Установка статуса
	 * @param type $title
	 * @param type $progressTitle
	 * @param type $progressPersentage
	 */
	public function setStatus($title, $progressTitle, $progressPersentage) {
		$this->progressPersentage = $progressPersentage;
		$this->title = $title;
		$this->progressTitle = $progressTitle;
		if ((microtime(true) - $this->_sessionProgressLastTimeUpdated) > self::SESSION_PROGRESS_REFRESH_RATE) {
			$this->setSessionActive(true);
			$_SESSION[self::SESSION_VARNAME] = array(
				'title' => $this->title,
				'progressPersentage' => $this->progressPersentage,
				'progressTitle' => $this->progressTitle,
			);
			$this->setSessionActive(false);
		}
	}

	/**
	 * 	Установка состояние сессии
	 * @param type $sessionActive
	 */
	public function setSessionActive($sessionActive) {
		if ($sessionActive) {
			@session_start();
		} else {
			session_write_close();
		}
		$this->sessionActive = $sessionActive;
	}

	/**
	 * 	загрузка параметров
	 * @param type $schemaName
	 * @param type $RegisterList_id
	 * @param type $that
	 * @param type $that
	 * @return array
	 */
	private function load($schemaName, $RegisterList_id) {
		getSessionParams();
		$that = $this;
		$actions = array();

		// PersonPrivilege
		$actions['PersonPrivilege'] = array();
		$actions['PersonPrivilege']['RegisterList_id'] = $RegisterList_id;
		$actions['PersonPrivilege']['name'] = 'Федеральный регистр льготников';
		$actions['PersonPrivilege']['actions'] = array();
		$actions['PersonPrivilege']['actions'][1] = array(
			'title' => 'Загрузка файлов DBase(*.DBF)...',
			'type' => 'importDbf',
			'files' => array(
				'C_REGL.DBF' => array(
					'destination' => array(
						'tableName' => 'frlPrivilege',
						'schemaName' => 'stg',
						'fields_mapping' => array(
							'SS' => 'ss',
							'C_KATL' => 'c_katl',
							'NAME_DL' => 'name_dl',
							'SN_DL' => 'sn_dl',
							'DATE_BL' => 'date_bl',
							'DATE_EL' => 'date_el',
						)
					)
				),
				'C_REGO.DBF' => array(
					'destination' => array(
						'tableName' => 'frlPerson',
						'schemaName' => 'stg',
						'fields_mapping' => array(
							'SS' => 'ss',
							'SN_POL' => 'sn_pol',
							'FAM' => 'fam',
							'IM' => 'im',
							'OT' => 'ot',
							'W' => 'w',
							'DR' => 'dr',
							'SN_DOC' => 'sn_doc',
							'C_DOC' => 'c_doc',
							'ADRES' => 'adres',
							'DOM' => 'dom',
							'KOR' => 'kor',
							'KV' => 'kv',
							'OKATO_REG' => 'okato_reg',
							'S_EDV' => 's_edv',
							'DB_EDV' => 'db_edv',
							'DE_EDV' => 'de_edv',
							'C_KAT1' => 'c_kat1',
							'C_KAT2' => 'c_kat2',
							'DATE_RSB' => 'date_rsb',
							'DATE_RSE' => 'date_rse',
							'U_TYPE' => 'u_type',
							'D_TYPE' => 'd_type',
							'C_REG' => 'c_reg',
						)
					)
				)
			)
		);
		$actions['PersonPrivilege']['actions'][2] = array(
			'title' => 'Выполнение загрузки...',
			'type' => 'queries',
			'queries' => array(
				array(
					'title' => 'Удаление пустых СНИЛСов',
					'except_region' => array('krym'),	//Не удалять пустые снилсы для Крыма
					'sql' => 'delete from stg.frlPerson where (ss is null or length(ss)=0) and RegisterListLog_id = :RegisterListLog_id'
				),
				array(
					'title' => 'Конвертация дат',
					'sql' => 'update stg.frlPerson set Person_birthday = to_char(dr, \'yyyy-mm-dd\') where RegisterListLog_id = :RegisterListLog_id'
				),
				array(
					'title' => 'Форматирование СНИЛС',
					'sql' => "update stg.frlPerson set Person_SNILS=REPLACE(REPLACE(ss,'-',''),' ','') where RegisterListLog_id = :RegisterListLog_id;
						update stg.frlPrivilege set Person_SNILS=REPLACE(REPLACE(ss,'-',''),' ','') where RegisterListLog_id = :RegisterListLog_id;"
				),
				array(
					'title' => 'Перекодировка поля "Пол"',
					'sql' => "update stg.frlPerson set sex_id=1 where w='М' and RegisterListLog_id = :RegisterListLog_id;
						update stg.frlPerson set sex_id=2 where w='Ж' and RegisterListLog_id = :RegisterListLog_id;"
				),

				array(
					'title' => 'Присвоение даты льгот',
					'sql' => "update stg.frlPrivilege set Privilege_begDate=to_char(date_bl, 'yyyy-mm-dd')where (date_bl<>'9999/99/99' and date_bl<>'') and RegisterListLog_id = :RegisterListLog_id;
update stg.frlPrivilege set Privilege_endDate=to_char(date_el, 'yyyy-mm-dd')where (date_el<>'9999/99/99' and date_el<>'' and date_el<>'2030/01/01')  and RegisterListLog_id = :RegisterListLog_id;"
				),
				array(
					'title' => 'Поиск Двойников 1',
					'sql' => "
						with tmpPerson as (
							select
								l.frlPerson_id,
								pc.cnt
							from
								stg.frlPerson l
								inner join lateral(
									select COUNT(*) as cnt
									from v_PersonState ps
									where rtrim(REPLACE(ps.Person_SurName,'ё','е')) = rtrim(ltrim(REPLACE(l.fam,'ё','е')))
										and rtrim(REPLACE(ps.Person_FirName,'ё','е')) = rtrim(ltrim(REPLACE(l.im,'ё','е')))
										and (rtrim(REPLACE(ps.Person_SecName,'ё','е')) = rtrim(ltrim(REPLACE(l.ot,'ё','е'))) or (nullif(replace(ps.Person_SecName,' ',''),'---') is null and nullif(rtrim(ltrim(l.ot)),'') is null))
										and ps.Person_BirthDay = l.Person_BirthDay
										and ps.Person_Snils = l.Person_Snils
								) pc on true
							where
								coalesce(l.identCount,0) = 0
								and l.RegisterListLog_id = :RegisterListLog_id
						)

						update
							stg.frlPerson
						set
							identCount = ps.cnt,
							identVariant = 1
						from
							tmpPerson ps
						where
							frlPerson.frlPerson_id = ps.frlPerson_id
							and frlPerson.RegisterListLog_id = :RegisterListLog_id;
					"
				),
				array(
					'title' => 'Поиск Двойников 2',
					'sql' => "
						with tmpPerson as (
							select
								l.frlPerson_id,
								pc.cnt
							from
								stg.frlPerson l
								inner join lateral(
									select COUNT(*) as cnt
									from v_PersonState ps
									where rtrim(REPLACE(ps.Person_SurName,'ё','е')) = rtrim(ltrim(REPLACE(l.fam,'ё','е')))
										and rtrim(REPLACE(ps.Person_FirName,'ё','е')) = rtrim(ltrim(REPLACE(l.im,'ё','е')))
										and ps.Person_BirthDay = l.Person_BirthDay
										and ps.Person_Snils = l.Person_Snils
										and nullif(l.ot,'') is null
								) pc on true
							where
								l.identCount = 0
								and l.RegisterListLog_id = :RegisterListLog_id
						)

						update
							stg.frlPerson
						set
							identCount = ps.cnt,
							identVariant = 2
						from
							tmpPerson ps
						where
							frlPerson.frlPerson_id = ps.frlPerson_id
							and frlPerson.RegisterListLog_id = :RegisterListLog_id;
					"
				),
				array(
					'title' => 'Поиск Двойников 3',
					'sql' => "
						with tmpPerson as (
							select
								l.frlPerson_id,
								pc.cnt
							from
								stg.frlPerson l
								inner join lateral(
									select COUNT(*) as cnt
									from v_PersonState ps
									where rtrim(ltrim(REPLACE(ps.Person_SurName,'ё','е'))) = rtrim(ltrim(REPLACE(l.fam,'ё','е')))
										and ps.Person_BirthDay = l.Person_BirthDay
										and rtrim(ltrim(ps.Person_Snils)) = rtrim(ltrim(l.Person_Snils))
										and nullif(l.im,'') is null
								) pc on true
							where
								l.identCount = 0
								and l.RegisterListLog_id = :RegisterListLog_id
						)

						update
							stg.frlPerson
						set
							identCount = ps.cnt,
							identVariant = 3
						from
							tmpPerson ps
						where
							frlPerson.frlPerson_id = ps.frlPerson_id
							and frlPerson.RegisterListLog_id = :RegisterListLog_id;
					"
				),
				array(
					'title' => 'Поиск Двойников 4',
					'sql' => "
						with tmpPerson as (
							select
								l.frlPerson_id,
								pc.cnt
							from
								stg.frlPerson l
								inner join lateral(
									select COUNT(*) as cnt
									from v_PersonState ps
									where rtrim(ltrim(REPLACE(ps.Person_SurName,'ё','е'))) = rtrim(ltrim(REPLACE(l.fam,'ё','е')))
										and rtrim(ltrim(REPLACE(ps.Person_FirName,'ё','е'))) = rtrim(ltrim(REPLACE(l.im,'ё','е')))
										and (rtrim(ltrim(REPLACE(ps.Person_SecName,'ё','е'))) = rtrim(ltrim(REPLACE(l.ot,'ё','е'))) or (nullif(replace(ps.Person_SecName,' ',''),'---') is null and nullif(rtrim(ltrim(l.ot)),'') is null))
										and ps.Person_Snils = l.Person_Snils
								) pc on true
							where
								l.identCount = 0
								and l.RegisterListLog_id = :RegisterListLog_id
						)

						update
							stg.frlPerson
						set
							identCount = ps.cnt,
							identVariant = 4
						from
							tmpPerson ps
						where
							frlPerson.frlPerson_id = ps.frlPerson_id
							and frlPerson.RegisterListLog_id = :RegisterListLog_id;
					"
				),
				array(
					'title' => 'Поиск Двойников 5',
					'sql' => "
						with tmpPerson as (
							select
								l.frlPerson_id,
								pc.cnt
							from
								stg.frlPerson l
								inner join lateral(
									select COUNT(*) as cnt
									from v_PersonState ps
									where rtrim(ltrim(REPLACE(ps.Person_SurName,'ё','е'))) = rtrim(ltrim(REPLACE(l.fam,'ё','е')))
									and rtrim(ltrim(REPLACE(ps.Person_FirName,'ё','е'))) = rtrim(ltrim(REPLACE(l.im,'ё','е')))
									and (rtrim(ltrim(REPLACE(ps.Person_SecName,'ё','е'))) = rtrim(ltrim(REPLACE(l.ot,'ё','е'))) or (nullif(replace(ps.Person_SecName,' ',''),'---') is null and nullif(rtrim(ltrim(l.ot)),'') is null))
									and ps.Person_BirthDay = l.Person_BirthDay
								) pc on true
							where
								l.identCount = 0
								and l.RegisterListLog_id = :RegisterListLog_id
						)

						update
							stg.frlPerson
						set
							identCount = ps.cnt,
							identVariant = 5
						from
							tmpPerson ps
						where
							frlPerson.frlPerson_id = ps.frlPerson_id
							and frlPerson.RegisterListLog_id = :RegisterListLog_id;
					"
				),
				array(
					'title' => 'Поиск Двойников 6',
					'region' => array('krym'),
					'sql' => "
						with tmpPerson as (
							select
								l.frlPerson_id,
								pc.cnt
							from
								stg.frlPerson l
								inner join lateral(
									select COUNT(*) as cnt
									from v_PersonState ps
									where rtrim(REPLACE(ps.Person_SurName,'ё','е')) = rtrim(ltrim(REPLACE(l.fam,'ё','е')))
										and rtrim(REPLACE(ps.Person_FirName,'ё','е')) = rtrim(ltrim(REPLACE(l.im,'ё','е')))
										and (rtrim(REPLACE(ps.Person_SecName,'ё','е')) = rtrim(ltrim(REPLACE(l.ot,'ё','е'))) or (nullif(replace(ps.Person_SecName,' ',''),'---') is null and nullif(rtrim(ltrim(l.ot)),'') is null))
										and ps.Person_BirthDay = l.Person_BirthDay
										and l.Person_Snils = ''
								) pc on true
							where
								l.identCount = 0
								and l.RegisterListLog_id = :RegisterListLog_id
						)

						update
							stg.frlPerson
						set
							identCount = ps.cnt,
							identVariant = 6
						from
							tmpPerson ps
						where
							frlPerson.frlPerson_id = ps.frlPerson_id
							and frlPerson.RegisterListLog_id = :RegisterListLog_id;
					"
				),
				array(
					'title' => 'Идентификация 1',
					'sql' => "
						with tmpPerson as (
							select
								l.frlPerson_id,
								ps.Person_id
							from
								stg.frlPerson l
								inner join v_PersonState ps on rtrim(REPLACE(ps.Person_SurName,'ё','е')) = rtrim(ltrim(REPLACE(l.fam,'ё','е'))) and rtrim(REPLACE(ps.Person_FirName,'ё','е')) = rtrim(ltrim(REPLACE(l.im,'ё','е')))
								and (rtrim(REPLACE(ps.Person_SecName,'ё','е')) = rtrim(ltrim(REPLACE(l.ot,'ё','е'))) or (nullif(replace(ps.Person_SecName,' ',''),'---') is null and nullif(rtrim(ltrim(l.ot)),'') is null))
								and ps.Person_BirthDay = l.Person_BirthDay and ps.Person_Snils = l.Person_Snils
							where
								l.person_id is null
								and l.identCount = 1
								and l.identVariant = 1
								and l.RegisterListLog_id = :RegisterListLog_id
						)
							
						update
							stg.frlPerson
						set
							Person_id = ps.Person_id
						from
							tmpPerson ps
						where
							frlPerson.frlPerson_id = ps.frlPerson_id
							and frlPerson.RegisterListLog_id = :RegisterListLog_id;
					"
				),
				array(
					'title' => 'Идентификация 2',
					'sql' => "
						with tmpPerson as (
							select
								l.frlPerson_id,
								ps.Person_id
							from
								stg.frlPerson l
								inner join v_PersonState ps on rtrim(REPLACE(ps.Person_SurName,'ё','е')) = rtrim(ltrim(REPLACE(l.fam,'ё','е'))) and rtrim(REPLACE(ps.Person_FirName,'ё','е')) = rtrim(ltrim(REPLACE(l.im,'ё','е')))
								and ps.Person_BirthDay = l.Person_BirthDay and ps.Person_Snils = l.Person_Snils
							where
								l.person_id is null
								and l.identCount = 1
								and l.identVariant = 2
								and l.RegisterListLog_id = :RegisterListLog_id
						)

						update
							stg.frlPerson
						set
							Person_id = ps.Person_id
						from
							tmpPerson ps
						where
							frlPerson.frlPerson_id = ps.frlPerson_id
							and frlPerson.RegisterListLog_id = :RegisterListLog_id;
					"
				),
				array(
					'title' => 'Идентификация 3',
					'sql' => "
						with tmpPerson as (
							select
								l.frlPerson_id,
								ps.Person_id
							from
								stg.frlPerson l
								inner join v_PersonState ps on rtrim(REPLACE(ps.Person_SurName,'ё','е')) = rtrim(ltrim(REPLACE(l.fam,'ё','е')))
								and ps.Person_BirthDay = l.Person_BirthDay
								and rtrim(ltrim(ps.Person_Snils)) = rtrim(ltrim(l.Person_Snils))
							where
								l.Person_id is null
								and l.identCount = 1
								and l.identVariant = 3
								and l.RegisterListLog_id = :RegisterListLog_id
						)

						update
							stg.frlPerson
						set
							Person_id = ps.Person_id
						from
							tmpPerson ps
						where
							frlPerson.frlPerson_id = ps.frlPerson_id
							and frlPerson.RegisterListLog_id = :RegisterListLog_id;
					"
				),
				array(
					'title' => 'Идентификация 4',
					'sql' => "
						with tmpPerson as (
							select
								l.frlPerson_id,
								ps.Person_id
							from
								stg.frlPerson l
								inner join v_PersonState ps on rtrim(ltrim(REPLACE(ps.Person_SurName,'ё','е'))) = rtrim(ltrim(REPLACE(l.fam,'ё','е')))
								and rtrim(ltrim(REPLACE(ps.Person_FirName,'ё','е'))) = rtrim(ltrim(REPLACE(l.im,'ё','е')))
								and (rtrim(ltrim(REPLACE(ps.Person_SecName,'ё','е'))) = rtrim(ltrim(REPLACE(l.ot,'ё','е'))) or (nullif(replace(ps.Person_SecName,' ',''),'---') is null and nullif(rtrim(ltrim(l.ot)),'') is null))
								and rtrim(ltrim(ps.Person_Snils)) = rtrim(ltrim(l.Person_Snils))
							where
								l.Person_id is null
								and l.identCount = 1
								and l.identVariant = 4
								and l.RegisterListLog_id = :RegisterListLog_id
						)

						update
							stg.frlPerson
						set
							Person_id = ps.Person_id
						from
							tmpPerson ps
						where
							frlPerson.frlPerson_id = ps.frlPerson_id
							and frlPerson.RegisterListLog_id = :RegisterListLog_id;
					"
				),
				array(
					'title' => 'Идентификация 5',
					'sql' => "
						with tmpPerson as (
							select
								l.frlPerson_id,
								ps.Person_id
							from
								stg.frlPerson l
								inner join v_PersonState ps on rtrim(ltrim(REPLACE(ps.Person_SurName,'ё','е'))) = rtrim(ltrim(REPLACE(l.fam,'ё','е')))
								and rtrim(ltrim(REPLACE(ps.Person_FirName,'ё','е'))) = rtrim(ltrim(REPLACE(l.im,'ё','е')))
								and (rtrim(ltrim(REPLACE(ps.Person_SecName,'ё','е'))) = rtrim(ltrim(REPLACE(l.ot,'ё','е'))) or (nullif(replace(ps.Person_SecName,' ',''),'---') is null and nullif(rtrim(ltrim(l.ot)),'') is null))
								and ps.Person_BirthDay = l.Person_BirthDay
							where
								l.Person_id is null
								and l.identCount = 1
								and l.identVariant = 5
								and l.RegisterListLog_id = :RegisterListLog_id
						)

						update
							stg.frlPerson
						set
							Person_id = ps.Person_id
						from
							tmpPerson ps
						where
							frlPerson.frlPerson_id = ps.frlPerson_id
							and frlPerson.RegisterListLog_id = :RegisterListLog_id;
					"
				),
				array(
					'title' => 'Идентификация 5',
					'region' => array('krym'),
					'sql' => "
						with tmpPerson as (
							select
								l.frlPerson_id,
								ps.Person_id
							from
								stg.frlPerson l
								inner join v_PersonState ps on rtrim(REPLACE(ps.Person_SurName,'ё','е')) = rtrim(ltrim(REPLACE(l.fam,'ё','е'))) and rtrim(REPLACE(ps.Person_FirName,'ё','е')) = rtrim(ltrim(REPLACE(l.im,'ё','е')))
								and (rtrim(REPLACE(ps.Person_SecName,'ё','е')) = rtrim(ltrim(REPLACE(l.ot,'ё','е'))) or (nullif(replace(ps.Person_SecName,' ',''),'---') is null and nullif(rtrim(ltrim(l.ot)),'') is null))
								and ps.Person_BirthDay = l.Person_BirthDay
							where
								l.Person_id is null
								and l.identCount = 1
								and l.identVariant = 6
								and l.RegisterListLog_id = :RegisterListLog_id
						)

						update
							stg.frlPerson
						set
							Person_id = ps.Person_id
						from
							tmpPerson ps
						where
							frlPerson.frlPerson_id = ps.frlPerson_id
							and frlPerson.RegisterListLog_id = :RegisterListLog_id;
					"
				),
				array(
					'title' => 'Двойники 1',
					'sql' => "
						with tmpPerson as (
							select
								l.frlPerson_id,
								pc.Person_id
							from
								stg.frlPerson l
								inner join lateral(
									select
										ps.Person_id
									from
										v_PersonState_all ps
									where
										rtrim(REPLACE(ps.Person_SurName,'ё','е')) = rtrim(ltrim(REPLACE(l.fam,'ё','е')))
										and rtrim(REPLACE(ps.Person_FirName,'ё','е')) = rtrim(ltrim(REPLACE(l.im,'ё','е')))
										and (rtrim(REPLACE(ps.Person_SecName,'ё','е')) = rtrim(ltrim(REPLACE(l.ot,'ё','е'))) or (nullif(replace(ps.Person_SecName,' ',''),'---') is null and nullif(rtrim(ltrim(l.ot)),'') is null))
										and ps.Person_BirthDay = l.Person_BirthDay
										and ps.Person_Snils = l.Person_Snils
									order by
										ps.Person_IsBDZ desc, ps.Person_id
									limit 1
								) pc on true
							where
								l.identCount > 1
								and l.Person_id is null
								and l.RegisterListLog_id = :RegisterListLog_id
						)

						update
							stg.frlPerson
						set
							Person_id = ps.Person_id
						from
							tmpPerson ps
						where
							frlPerson.frlPerson_id = ps.frlPerson_id
							and frlPerson.RegisterListLog_id = :RegisterListLog_id;
					"
				),
				array(
					'title' => 'Двойники 2',
					'sql' => "
						with tmpPerson as (
							select
								l.frlPerson_id,
								pc.Person_id
							from
								stg.frlPerson l
								inner join lateral(
									select
										ps.Person_id
									from
										v_PersonState_all ps
									where
										rtrim(ltrim(REPLACE(ps.Person_SurName,'ё','е'))) = rtrim(ltrim(REPLACE(l.fam,'ё','е')))
										and ps.Person_BirthDay = l.Person_BirthDay
										and rtrim(ltrim(ps.Person_Snils)) = rtrim(ltrim(l.Person_Snils))
									order by
										ps.Person_IsBDZ desc, ps.Person_id
									limit 1
								) pc on true
							where
								l.identCount > 1
								and l.Person_id is null
								and l.RegisterListLog_id = :RegisterListLog_id
						)

						update
							stg.frlPerson
						set
							Person_id = ps.Person_id
						from
							tmpPerson ps
						where
							frlPerson.frlPerson_id = ps.frlPerson_id
							and frlPerson.RegisterListLog_id = :RegisterListLog_id;
					"
				),
				array(
					'title' => 'Двойники 3',
					'sql' => "
						with tmpPerson as (
							select
								l.frlPerson_id,
								pc.Person_id
							from
								stg.frlPerson l
								inner join lateral(
									select
										ps.Person_id
									from
										v_PersonState_all ps
									where
										rtrim(ltrim(REPLACE(ps.Person_SurName,'ё','е'))) = rtrim(ltrim(REPLACE(l.fam,'ё','е')))
										and rtrim(ltrim(REPLACE(ps.Person_FirName,'ё','е'))) = rtrim(ltrim(REPLACE(l.im,'ё','е')))
										and (rtrim(ltrim(REPLACE(ps.Person_SecName,'ё','е'))) = rtrim(ltrim(REPLACE(l.ot,'ё','е'))) or (nullif(replace(ps.Person_SecName,' ',''),'---') is null and nullif(rtrim(ltrim(l.ot)),'') is null))
										and ps.Person_Snils = l.Person_Snils
									order by
										ps.Person_IsBDZ desc, ps.Person_id
									limit 1
								) pc on true
							where
								l.identCount > 1
								and l.Person_id is null
								and l.RegisterListLog_id = :RegisterListLog_id
						)

						update
							stg.frlPerson
						set
							Person_id = ps.Person_id
						from
							tmpPerson ps
						where
							frlPerson.frlPerson_id = ps.frlPerson_id
							and frlPerson.RegisterListLog_id = :RegisterListLog_id;
					"
				),
				array(
					'title' => 'Двойники 4',
					'sql' => "
						with tmpPerson as (
							select
								l.frlPerson_id,
								pc.Person_id
							from
								stg.frlPerson l
								inner join lateral(
									select
										ps.Person_id
									from
										v_PersonState_all ps
									where
										rtrim(ltrim(REPLACE(ps.Person_SurName,'ё','е'))) = rtrim(ltrim(REPLACE(l.fam,'ё','е')))
										and rtrim(ltrim(REPLACE(ps.Person_FirName,'ё','е'))) = rtrim(ltrim(REPLACE(l.im,'ё','е')))
										and (rtrim(ltrim(REPLACE(ps.Person_SecName,'ё','е'))) = rtrim(ltrim(REPLACE(l.ot,'ё','е'))) or (nullif(replace(ps.Person_SecName,' ',''),'---') is null and nullif(rtrim(ltrim(l.ot)),'') is null))
										and ps.Person_BirthDay = l.Person_BirthDay
									order by
										ps.Person_IsBDZ desc, ps.Person_id
									limit 1
								) pc on true
							where
								l.identCount > 1
								and l.Person_id is null
								and l.RegisterListLog_id = :RegisterListLog_id
						)

						update
							stg.frlPerson
						set
							Person_id = ps.Person_id
						from
							tmpPerson ps
						where
							frlPerson.frlPerson_id = ps.frlPerson_id
							and frlPerson.RegisterListLog_id = :RegisterListLog_id;
					"
				),
				array(
					'title' => 'Поиск Двойников 5',
					'region' => array('krym'),
					'sql' => "
						with tmpPerson as (
							select
								l.frlPerson_id,
								pc.Person_id
							from
								stg.frlPerson l
								inner join lateral(
									select
										ps.Person_id
									from
										v_PersonState_all ps
									where
										rtrim(REPLACE(ps.Person_SurName,'ё','е')) = rtrim(ltrim(REPLACE(l.fam,'ё','е')))
										and rtrim(REPLACE(ps.Person_FirName,'ё','е')) = rtrim(ltrim(REPLACE(l.im,'ё','е')))
										and (rtrim(REPLACE(ps.Person_SecName,'ё','е')) = rtrim(ltrim(REPLACE(l.ot,'ё','е'))) or (nullif(replace(ps.Person_SecName,' ',''),'---') is null and nullif(rtrim(ltrim(l.ot)),'') is null))
										and ps.Person_BirthDay = l.Person_BirthDay
										and l.Person_Snils = ''
									order by
										ps.Person_IsBDZ desc, ps.Person_id
									limit 1
								) pc on true
							where
								l.identCount > 1
								and l.Person_id is null
								and l.RegisterListLog_id = :RegisterListLog_id;
						)

						update
							stg.frlPerson
						set
							Person_id = ps.Person_id
						from
							tmpPerson ps
						where
							frlPerson.frlPerson_id = ps.frlPerson_id
							and frlPerson.RegisterListLog_id = :RegisterListLog_id;
					"
				),
				array(
					'title' => 'Импорт новых данных о людях (1)',
					'sql' => "
							do
							$$
							declare
								cur1 REFCURSOR;
								p_Server_id bigint;
								p_Person_id bigint;
								p_Lgot_id bigint;
								p_Error_Code bigint;
								p_Error_Message varchar(4000);
								p_frlPerson_id bigint;
								p_fam varchar(30);
								p_im varchar(30);
								p_ot varchar(30);
								p_person_birthday timestamp;
								p_sex_id bigint;
								p_Person_SNILS varchar(11);
								p_Server_id := 3;
								
							begin
								open cur1 for
									select
										frlPerson_id,
										REPLACE(fam,'ё','е') as fam,
										REPLACE(im,'ё','е') as im,
										REPLACE(ot,'ё','е') as ot,
										person_birthday, sex_id,
										Person_SNILS
									from stg.frlPerson
									where identCount = 0
										and Person_id is null
										and RegisterListLog_id = :RegisterListLog_id
										
							loop
								fetch next from cur1 into
									p_frlPerson_id,
									p_fam,
									p_im,
									p_ot,
									p_person_birthday,
									p_sex_id,
									p_Person_SNILS;
									
								EXIT WHEN NOT FOUND;
								p_Error_Code := null;
								p_Error_Message := null;
								p_Person_id := null;

								select
									fnc.Error_Code,
									fnc.Error_Message
								into
									p_Error_Code,
									p_Error_Message
								from p_PersonAll_ins(
									PersonSurName_SurName := p_fam,
									PersonFirName_FirName := p_im,
									PersonSecName_SecName := p_ot,
									PersonBirthDay_BirthDay := p_person_birthday,
									Sex_id := p_sex_id,
									PersonSnils_Snils := p_Person_SNILS,
									Server_id := p_Server_id,
									Person_id := p_Person_id,
									Lgot_id := p_frlPerson_id,
									pmUser_id := 1,
									isRebuildState := 1
								) fnc
								if (p_Error_Code is not null)
								then
									RAISE USING ERRCODE = p_error_code, MESSAGE = p_Error_Message;
								end if;
		
							end loop;
							close cur1;
							end;
							$$"
				),
				array(
					'title' => 'Идентификация после добавления',
					'sql' => "
						with mv as (
							select
								l.frlPerson_id,
								ps.Person_id
							from
								stg.frlPerson l
								inner join v_PersonState ps on rtrim(REPLACE(ps.Person_SurName,'ё','е')) = rtrim(ltrim(REPLACE(l.fam,'ё','е'))) and rtrim(REPLACE(ps.Person_FirName,'ё','е')) = rtrim(ltrim(REPLACE(l.im,'ё','е')))
								and (rtrim(REPLACE(ps.Person_SecName,'ё','е')) = rtrim(ltrim(REPLACE(l.ot,'ё','е'))) or (nullif(replace(ps.Person_SecName,' ',''),'---') is null and nullif(rtrim(ltrim(l.ot)),'') is null))
								and ps.Person_BirthDay = l.Person_BirthDay and coalesce(ps.Person_Snils,'') = coalesce(l.Person_Snils,'')
							where
								l.Person_id is null
								and l.identCount <= 1
								and l.identVariant = 5
								and l.RegisterListLog_id = :RegisterListLog_id
						)
							
						update
							stg.frlPerson
						set
							Person_id = ps.Person_id
						from
							tmpPerson ps
						where
							frlPerson.frlPerson_id = ps.frlPerson_id
							and frlPerson.RegisterListLog_id = :RegisterListLog_id;
					"
				, 'callback' => function ($th) use ($that) {

					/**
					 * Вроде бы просто сохраняем и выводим те записи, которые не были добавлены (это двойники)
					 * Функция получает список двойников, сохраняет из в файлик и выводит ссылку на скачивание файла с двойниками
					 *
					 * @var $that ImportSchema_model
					 */
					$rslt = $th->db->query('
						select
						(select COUNT(*) from  stg.frlPerson
							where identCount !=0 and Person_id is not null
						) as "ident",
						(select COUNT(*) from stg.frlPerson
					  	) as "allpers",
						(select COUNT(*) from stg.frlPerson where
							identCount =0 and Person_id is not null
						) as "new", 
						(select COUNT(*) from stg.frlPerson 
							where identCount >1 and Person_id is null
						) as "doubl"
					')->result('array');
					$double = $th->db->query('
						select
							fam as "fam",
							im as "im",
							ot as "ot",
							identCount as "identCount",
							Person_SNILS as "Person_SNILS",
							to_char(Person_birthday, \'dd.mm.yyyy\') as "birthDay"
						from stg.frlPerson 
							where identCount >1
					')->result('array');

					$doublePeople = '';
					if (count($double) > 30) {
						$str_arr = array();
						foreach($double as $val) {
							$str_arr[] = "{$val['fam']} {$val['im']} {$val['ot']} {$val['birthDay']} - {$val['Person_SNILS']}; кол-во: {$val['identCount']}\n";
						}

						$out_dir = EXPORTPATH_ROOT.'frl_import/doubles_'.time();
						mkdir($out_dir, 0777, true);
						$errorFile = $out_dir . '/doublePeople.txt';
						file_put_contents($errorFile, $str_arr);

						$doublePeople = "&nbsp;<a href='{$errorFile}' target='_blank'>скачать<a>";
					} else {
						$doublePeople = "<ul>";
						foreach($double as $val){
							$doublePeople.="<li>{$val['fam']} {$val['im']} {$val['ot']} {$val['birthDay']} - {$val['Person_SNILS']}; кол-во: {$val['identCount']}</li>";
						}
						$doublePeople.="</ul>";
					}
					RegisterListDetailLog_model::createLogMessage(new DateTime(), 1, "Импорт ФРЛ Персоны: Всего - {$rslt[0]['allpers']} чел., идентифицировано - {$rslt[0]['ident']} чел; добавлено - {$rslt[0]['new']} чел.", $th->RegisterListLog_model->getRegisterListLog_id(), $th->getPmUserId());
					RegisterListDetailLog_model::createLogMessage(new DateTime(), 1, "Двойники: ".$doublePeople, $th->RegisterListLog_model->getRegisterListLog_id(), $th->getPmUserId());
				}
				),
				array(
					'title' => 'Признак отказа от льгот',
					'sql' => "
							do
							$$
							declare
								cur1 REFCURSOR;
								p_pers_id bigint;
								p_frlPerson_isRefuse bigint;
								p_YearRefuse int = date_part('year', dbo.tzGetDate());
								p_Error_Code bigint;
								p_Error_Message varchar(4000);
								p_ins bigint;
							begin
								open cur1 for
									select
										frlPerson_isRefuse,
										Person_id as pers_id
									from stg.frlPerson
									where identCount =1
										and frlPerson_isRefuse =2
							
							loop
								fetch next from cur1 into
									p_frlPerson_isRefuse,
									p_pers_id;
									
								EXIT WHEN NOT FOUND;
								p_Error_Code := null;
								p_Error_Message := null;
								p_ins := (select PersonRefuse_id from PersonRefuse pr where pr.Person_id=p_pers_id and pr.PersonRefuse_Year=p_YearRefuse limit 1);
								
								if (p_ins is not null)
								then
									select
										fnc.Error_Code,
										fnc.Error_Message
									into
										p_Error_Code,
										p_Error_Message
									from p_PersonRefuse_ins(
										Person_id := p_pers_id,
										PersonRefuse_Year := p_YearRefuse,
										PersonRefuse_IsRefuse := p_frlPerson_isRefuse,
										pmUser_id := 1
									) fnc
								else
									select
										fnc.Error_Code,
										fnc.Error_Message
									into
										p_Error_Code,
										p_Error_Message
									from p_PersonRefuse_upd
										PersonRefuse_id := p_ins,
										Person_id := p_pers_id,
										PersonRefuse_Year := p_YearRefuse,
										PersonRefuse_IsRefuse := p_frlPerson_isRefuse,
										pmUser_id := 1
								) fnc
								end if;
								if (p_Error_Code is not null)
								then
									RAISE USING ERRCODE = p_error_code, MESSAGE = p_Error_Message;
								end if;
		
							end loop;
							close cur1;
							end;
							$$
"
				),
				array(
					'title' => 'Импорт документа',
					'sql'=>'
							do
							$$
							declare
								cur1 REFCURSOR;
								p_Error_Code bigint;
								p_Error_Message varchar(4000);
								p_s_doc varchar(10);
								p_curDT timestamp = dbo.tzGetDate();
								p_n_doc varchar(30);
								p_c_doc bigint;
								p_dt_doc timestamp;
								p_pers_id bigint;
								p_OrgDep_id bigint;
								p_KLCountry_id bigint;

							begin
								open cur1 for
									select to_char(fP.dt_doc, \'yyyy-mm-dd\') as dt_doc,fP.s_doc,fP.n_doc,Org.OrgDep_id,fP.c_doc,fP.Person_id as pers_id, coalesce(Country.KLCountry_id,643) as KLCountry_id
									from stg.frlPerson fP 
									left join v_personState_all PS on PS.Person_id=fP.Person_id
									left join v_KLCountry Country on Country.KLCountry_Name ilike fp.frlPerson_Nationality
									left join lateral(select OrgDep_id from v_OrgDep where Org_Name=fP.o_doc limit 1) Org on true
									where fP.identCount =1 and PS.Document_id is null and fP.dt_doc is not null and ps.Person_IsBDZ!=1
						
							loop
								fetch next from cur1 into
									p_dt_doc,
									p_s_doc,
									p_n_doc,
									p_OrgDep_id,
									p_c_doc,
									p_pers_id,
									p_KLCountry_id;
									
								EXIT WHEN NOT FOUND;
								p_Error_Code := null;
								p_Error_Message := null;
								
								select
									fnc.Error_Code,
									fnc.Error_Message
								into
									p_Error_Code,
									p_Error_Message
								from p_PersonDocument_ins(
									Server_id := 3,
									Person_id := p_pers_id,
									PersonDocument_insDT := p_curDT,
									DocumentType_id := p_c_doc,
									OrgDep_id := p_OrgDep_id,
									Document_Ser := p_s_doc,
									Document_Num := p_n_doc,
									Document_begDate := p_dt_doc,
									KLCountry_id := p_KLCountry_id,
									pmUser_id := 1
								) fnc
								if (p_Error_Code is not null)
								then
									RAISE USING ERRCODE = p_error_code, MESSAGE = p_Error_Message;
								end if;
		
							end loop;
							close cur1;
							end;
							$$
						'
				),
				array(
					'title' => 'Признак отказа от льгот на следующий год',
					'sql' => "
							do
							$$
							declare
								cur1 REFCURSOR;
								p_pers_id bigint;
								p_frlPerson_isRefuseNext bigint;
								p_YearRefuse int = date_part('year', dbo.tzGetDate())+1;
								p_Error_Code bigint;
								p_Error_Message varchar(4000);
								p_ins bigint;
								
							begin
								open cur1 for
									select
										frlPerson_isRefuseNext,
										Person_id as pers_id
									from stg.frlPerson
									where identCount = 1
										and frlPerson_isRefuseNext = 2

							loop
								fetch next from cur1 into
									p_frlPerson_isRefuseNext,
									p_pers_id;
								
							 	EXIT WHEN NOT FOUND;
								p_Error_Code := null;
								p_Error_Message := null;
								p_ins = (select
											PersonRefuse_id
										from PersonRefuse pr
										where pr.Person_id = p_pers_id
											and pr.PersonRefuse_Year = p_YearRefuse
										limit 1);
										
								if(p_ins is null)
								then
									select
										fnc.Error_Code,
										fnc.Error_Message
									into
										p_Error_Code,
										p_Error_Message
									from p_PersonRefuse_ins(
										Person_id := p_pers_id,
										PersonRefuse_Year := p_YearRefuse,
										PersonRefuse_IsRefuse := p_frlPerson_isRefuseNext,
										pmUser_id=1
									) fnc
								else
									select
										fnc.Error_Code,
										fnc.Error_Message
									into
										p_Error_Code,
										p_Error_Message
									from p_PersonRefuse_upd(
										PersonRefuse_id := p_ins,
										Person_id := p_pers_id,
										PersonRefuse_Year := p_YearRefuse,
										PersonRefuse_IsRefuse := p_frlPerson_isRefuseNext,
										pmUser_id := 1
									) fnc
								end if;	
									
								if (p_Error_Code is not null)
								then
									RAISE USING ERRCODE = p_error_code, MESSAGE = p_Error_Message;
								end if;
		
							end loop;
							close cur1;
							end;
							$$
"
				),
				array(
					'title' => 'Форматирование СНИЛС и кода льготы',
					'sql' => "
update stg.frlPrivilege set Person_SNILS=REPLACE(REPLACE(ss,'-',''),' ','') where RegisterListLog_id = :RegisterListLog_id;
update stg.frlPrivilege set PrivilegeType_Code=CAST(c_katl as int) where RegisterListLog_id = :RegisterListLog_id;"
				),
				array(
					'title' => 'Форматирование кода льготы',
					'sql' => "update stg.frlPrivilege set PrivilegeType_Code = cast(C_KATL as int) where RegisterListLog_id = :RegisterListLog_id"
				),
				array(
					'title' => 'Копирование результатов идентификации в stg.frlPrivilege',
					'sql' => "update stg.frlPrivilege set
Person_id = frlpers.Person_id
from stg.frlPrivilege frlpriv 
inner join stg.frlPerson frlpers on frlpers.frlPerson_id = frlpriv.frlPerson_id and frlpers.RegisterListLog_id = :RegisterListLog_id where frlpriv.RegisterListLog_id = :RegisterListLog_id"
				),
				array(
					'title' => 'Перекодировка льгот',
					'sql' => "update stg.frlPrivilege set
PersonPrivilege_id = pp.PersonPrivilege_id
from stg.frlPrivilege imp
left join (select personprivilege.PersonPrivilege_id,personprivilege.person_id,personprivilege.Server_id,
					PrivilegeType.ReceptFinance_id,PrivilegeType.PrivilegeType_Code,personprivilege.PersonPrivilege_begDate,personprivilege.PersonPrivilege_endDate
		from v_personprivilege personprivilege
		inner join v_PrivilegeType PrivilegeType on PrivilegeType.PrivilegeType_id = personprivilege.PrivilegeType_id) pp
on imp.person_id = pp.person_id
	and pp.ReceptFinance_id = 1
	and pp.PrivilegeType_Code = cast(imp.PrivilegeType_code as varchar)
	and (pp.PersonPrivilege_begDate between imp.Privilege_begDate and coalesce(imp.Privilege_endDate,'3000-01-01')
	or pp.PersonPrivilege_endDate between imp.Privilege_begDate and coalesce(imp.Privilege_endDate,'3000-01-01')
	or imp.Privilege_begDate between pp.PersonPrivilege_begDate and coalesce(pp.PersonPrivilege_endDate,'3000-01-01')
	or imp.Privilege_endDate between pp.PersonPrivilege_begDate and coalesce(pp.PersonPrivilege_endDate,'3000-01-01'))"
				),
				array(
					'title' => 'Копирование результатов идентификации в stg.frlPrivilege (2)', //todo уточнить у Врубель, опять выполняется дважды
					'sql' => "update stg.frlPrivilege set
Person_id = frlpers.Person_id
from stg.frlPrivilege frlpriv 
inner join stg.frlPerson frlpers on frlpers.frlPerson_id = frlpriv.frlPerson_id and frlpers.RegisterListLog_id = :RegisterListLog_id where frlpriv.RegisterListLog_id = :RegisterListLog_id"
				),
				array(
					'title'=>'Добавление периодик',
					'except_region' => array('perm','kz'),
					'sql'=>"
							do
							$$
							declare
								cur1 REFCURSOR;
								p_time date;
								p_Person_id bigint;
								p_Person_SNILS varchar(11);
								p_fam varchar(11);
								p_im varchar(11);
								p_ot varchar(11);
								p_Person_BirthDay date;
								p_Error_Code int;
								p_Error_Message varchar(400);
							p_time := (select cast(dbo.tzGetDate() as date));
							
							begin
								open cur1 for
									select distinct 
										fp.Person_id,
										case when coalesce(fp.Person_SNILS,'')<>coalesce(ps.Person_Snils,'') 
											then coalesce(fp.Person_SNILS,'') else null
										end as Person_SNILS,
										case when coalesce(fp.fam,'')<>coalesce(ps.Person_SurName,'') 
											then coalesce(fp.fam,'') else null
										end as fam,
										case when coalesce(fp.im,'')<>coalesce(ps.Person_FirName,'') 
											then coalesce(fp.im,'') else null
										end as im,
										case when coalesce(fp.ot,'')<>coalesce(ps.Person_SecName,'') 
											then coalesce(fp.ot,'') else null
										end as ot,
										case when fp.Person_BirthDay<>ps.Person_BirthDay 
											then fp.Person_BirthDay else null
										end as Person_BirthDay
									from 
										stg.frlPerson fp
										inner join v_PersonState ps on ps.Person_id = fp.Person_id
									where 
										cast(fp.u_type as int) = 3 
										and (
											coalesce(fp.Person_SNILS,'')<>coalesce(ps.Person_Snils,'')
											or coalesce(fp.fam,'')<>coalesce(ps.Person_SurName,'')
											or coalesce(fp.im,'')<>coalesce(ps.Person_FirName,'')
											or coalesce(fp.ot,'')<>coalesce(ps.Person_SecName,'')
											or fp.Person_BirthDay<>ps.Person_BirthDay
										)
							loop
								fetch next from cur1 into
									p_Person_id,
									p_Person_SNILS,
									p_fam,
									p_im,
									p_ot,
									p_Person_BirthDay;
									
								EXIT WHEN NOT FOUND;
								p_Error_Code := null;
								p_Error_Message := null;
								
								if (p_Person_SNILS is not null)
								then
									select
										fnc.Error_Code,
										fnc.Error_Message
									into
										p_Error_Code,
										p_Error_Message
									from p_PersonSnils_ins(
										Server_id := 3,
										Person_id := p_Person_id,
										PersonSnils_insDT := p_time,
										PersonSnils_Snils := p_Person_SNILS,
										pmUser_id := 1
									) fnc
								end if;
								if (p_Error_Code is not null)
								then
									RAISE USING ERRCODE = p_error_code, MESSAGE = p_Error_Message;
								end if;
								
								if (p_fam is not null)
								then
									select
										fnc.Error_Code,
										fnc.Error_Message
									into
										p_Error_Code,
										p_Error_Message
									from p_PersonSnils_ins(
										Server_id := 3,
										Person_id := p_Person_id,
										PersonSnils_insDT := p_time,
										PersonSurName_SurName := p_fam,
										pmUser_id := 1
									) fnc
								end if;
								if (p_Error_Code is not null)
								then
									RAISE USING ERRCODE = p_error_code, MESSAGE = p_Error_Message;
								end if;
								
								if (p_im is not null)
								then
									select
										fnc.Error_Code,
										fnc.Error_Message
									into
										p_Error_Code,
										p_Error_Message
									from p_PersonSnils_ins(
										Server_id := 3,
										Person_id := p_Person_id,
										PersonSnils_insDT := p_time,
										PersonFirName_FirName := p_im,
										pmUser_id := 1
									) fnc
								end if;
								if (p_Error_Code is not null)
								then
									RAISE USING ERRCODE = p_error_code, MESSAGE = p_Error_Message;
								end if;
								
								if (p_ot is not null)
								then
									select
										fnc.Error_Code,
										fnc.Error_Message
									into
										p_Error_Code,
										p_Error_Message
									from p_PersonSnils_ins(
										Server_id := 3,
										Person_id := p_Person_id,
										PersonSnils_insDT := p_time,
										PersonSecName_SecName := p_ot,
										pmUser_id := 1
									) fnc
								end if;
								if (p_Error_Code is not null)
								then
									RAISE USING ERRCODE = p_error_code, MESSAGE = p_Error_Message;
								end if;
								
								if (p_Person_BirthDay is not null)
								then
									select
										fnc.Error_Code,
										fnc.Error_Message
									into
										p_Error_Code,
										p_Error_Message
									from p_PersonSnils_ins(
										Server_id := 3,
										Person_id := p_Person_id,
										PersonSnils_insDT := p_time,
										PersonBirthDay_BirthDay := p_Person_BirthDay,
										pmUser_id := 1
									) fnc
								end if;
								if (p_Error_Code is not null)
								then
									RAISE USING ERRCODE = p_error_code, MESSAGE = p_Error_Message;
								end if;
		
							end loop;
							close cur1;
							end;
							$$
					"
				),
				array(
					'title'=>'Добавление СНИЛС',
					'region' => array('ufa','saratov','khak','krym'),
					'sql'=>"
							do
							$$
							declare
								cur1 REFCURSOR;
								p_time date;
								p_Person_id bigint;
								p_Person_SNILS varchar(11);
								p_Error_Code int;
								p_Error_Message varchar(400);
							p_time := (select cast(dbo.tzGetDate() as date));
								
							begin
								open cur1 for
									select distinct
										fp.Person_id,
										fp.Person_SNILS
									from stg.frlPrivilege fp
									inner join v_PersonState ps on ps.Person_id = fp.Person_id
									where coalesce(ps.person_snils,'')=''


							loop
								fetch next from cur1 into
									p_Person_id,
									p_Person_SNILS;
									
								EXIT WHEN NOT FOUND;
								p_Error_Code := null;
								p_Error_Message := null;
								
								select
									fnc.Error_Code,
									fnc.Error_Message
								into
									p_Error_Code,
									p_Error_Message
								from p_PersonSnils_ins(
									Server_id := 3,
									Person_id := p_Person_id,
									PersonSnils_insDT := p_time,
									PersonSnils_Snils := p_Person_SNILS,
									pmUser_id := 1
								) fnc
								if (p_Error_Code is not null)
								then
									RAISE USING ERRCODE = p_error_code, MESSAGE = p_Error_Message;
								end if;
		
							end loop;
    						close cur1;
							end;
							$$
						"
				),
				array(
					'title'=>'Добавление СНИЛС',	//Добавление периодики для изменения перс.данных
					'except_region' => array('perm'),
					'sql'=>"
						do
						$$
						declare
							cur1 REFCURSOR;
							p_time date;
							p_Person_id bigint;
							p_Person_SNILS varchar(11);
							p_Error_Code int;
							p_Error_Message varchar(400);
						p_time := (select cast(dbo.tzGetDate() as date));
						
						begin
							open cur1 for
								select distinct
									fp.Person_id,
									fp.Person_SNILS
								from stg.frlPrivilege fp
								inner join v_PersonState ps on ps.Person_id = fp.Person_id
								where coalesce(ps.person_snils,'')<>coalesce(fp.Person_SNILS,'')
							
						loop
							fetch next from cur1 into
								p_Person_id,
								p_Person_SNILS;
								
							EXIT WHEN NOT FOUND;
							p_Error_Code := null;
							p_Error_Message := null;
							
							select
								fnc.Error_Code,
								fnc.Error_Message
							into
								p_Error_Code,
								p_Error_Message
							from p_PersonSnils_ins(
								Server_id := 3,
								Person_id := p_Person_id,
								PersonSnils_insDT := p_time,
								PersonSnils_Snils := p_Person_SNILS,
								pmUser_id := 1
							) fnc
							if (p_Error_Code is not null)
							then
								RAISE USING ERRCODE = p_error_code, MESSAGE = p_Error_Message;
							end if;
		
						end loop;
    					close cur1;
						end;
						$$
					"
				),
				array(
					'title' => 'Удаление дублей льгот',
					'region' => 'ufa',
					'sql' => "select  * from dbo.p_PersonPrivilege_DoubleDel()"
				),
				array(
					'title' => 'Удаление повторяющихся льгот человека',
					'sql' => "
with doubleList as (
	select 
		priv1.frlPrivilege_id,
		priv1.Person_id
	from stg.frlPrivilege priv1
	where priv1.RegisterListLog_id = :RegisterListLog_id	
	and exists(
		select *
		from stg.frlPrivilege priv2
		where priv2.RegisterListLog_id = priv1.RegisterListLog_id
		and priv2.frlPrivilege_id <> priv1.frlPrivilege_id
		and priv2.Person_id = priv1.Person_id
		and priv2.PrivilegeType_Code = priv1.PrivilegeType_Code
		and coalesce(priv2.Privilege_begDate, '3000-01-01') = coalesce(priv1.Privilege_begDate, '3000-01-01')
		and coalesce(priv2.Privilege_endDate, '3000-01-01') = coalesce(priv1.Privilege_endDate, '3000-01-01')
	)
),
privList as (
	select frlPrivilege_id
	from stg.frlPrivilege t
	where t.frlPrivilege_id = (select frlPrivilege_id from doubleList where Person_id = t.Person_id limit 1)
)
delete stg.frlPrivilege where frlPrivilege_id in (select frlPrivilege_id from privList)
					"
				),
				array(
					'title' => 'Импорт льгот',
					'sql' => "
						select
							Error_Code as \"Error_Code\",
							Error_Message as \"Error_Msg\"
						from stg.xp_ImportPersonPrivilege(
							RegisterListLog_id := :RegisterListLog_id,
							pmUser_id := 1
						)				    
					", "qa" => true
				),
				array(
					'title' => 'Перекодировка льгот',
					'sql' => "update stg.frlPrivilege set
PersonPrivilege_id = pp.PersonPrivilege_id
from stg.frlPrivilege imp
left join (select personprivilege.PersonPrivilege_id,personprivilege.person_id,personprivilege.Server_id,
					PrivilegeType.ReceptFinance_id,PrivilegeType.PrivilegeType_Code,personprivilege.PersonPrivilege_begDate,personprivilege.PersonPrivilege_endDate
		from v_personprivilege personprivilege
		inner join v_PrivilegeType PrivilegeType on PrivilegeType.PrivilegeType_id = personprivilege.PrivilegeType_id) pp
on imp.person_id = pp.person_id
	and pp.ReceptFinance_id = 1
	and pp.PrivilegeType_Code = cast(imp.PrivilegeType_code as varchar)
	and (pp.PersonPrivilege_begDate between imp.Privilege_begDate and coalesce(imp.Privilege_endDate,'3000-01-01')
	or pp.PersonPrivilege_endDate between imp.Privilege_begDate and coalesce(imp.Privilege_endDate,'3000-01-01')
	or imp.Privilege_begDate between pp.PersonPrivilege_begDate and coalesce(pp.PersonPrivilege_endDate,'3000-01-01')
	or imp.Privilege_endDate between pp.PersonPrivilege_begDate and coalesce(pp.PersonPrivilege_endDate,'3000-01-01'))"
				),

				array(
					'title' => 'Добавление уд-ия в справочник',
					'sql' => "
							do
							$$
							declare
								cur1 REFCURSOR;
								p_cnt bigint;
								p_name_dl varchar(4000);
								p_Error_Code bigint;
								p_Error_Message varchar(4000);
								p_ins bigint;
								
							begin
								open cur1 for
									select distinct
										name_dl
									from stg.frlPrivilege
									where Person_id is not null
									and s_dl is not null
									and n_dl is not null
							
							loop
								fetch next from cur1 into
									p_name_dl;
									
								EXIT WHEN NOT FOUND;
								p_Error_Code := null;
								p_Error_Message := null;
								p_ins := (select COUNT(*) from DocumentPrivilegeType dpt where dpt.DocumentPrivilegeType_Name=p_name_dl);
								p_cnt := (select COUNT(*)+1 from DocumentPrivilegeType);
								
								if (p_ins = 0)
								then
									select
										fnc.Error_Code,
										fnc.Error_Message
									into
										p_Error_Code,
										p_Error_Message
									from p_DocumentPrivilegeType_ins
										DocumentPrivilegeType_Code := p_cnt,
										DocumentPrivilegeType_Name := p_name_dl,
										pmUser_id := 1
									) fnc
								end if;
							if (p_Error_Code is not null)
							then
								RAISE USING ERRCODE = p_error_code, MESSAGE = p_Error_Message;
							end if;
		
							end loop;
    						close cur1;
							end;
							$$
					"
				),
				array(
					'title' => 'Добавление уд-ия льготника',
					'sql' => "
							do
							$$
							declare
								cur1 REFCURSOR;
								p_DocumentPrivilegeType_id bigint;
								p_PersonPrivilege_id bigint;
								p_s_dl varchar(10);
								p_n_dl varchar(30);
								p_sn_dl timestamp;
								p_Privilege_begDate timestamp;
								p_frlPrivilege_Org varchar(300);
								p_Error_Code bigint;
								p_Error_Message varchar(4000);

							begin
								open cur1 for
									select 
										dpt.DocumentPrivilegeType_id as DocumentPrivilegeType_id,
										fp.PersonPrivilege_id as PersonPrivilege_id,
										fp.s_dl as s_dl,
										fp.n_dl as n_dl,
										to_char(fp.sn_d, 'yyyy-mm-dd'l) as sn_dl,
										fp.Privilege_begDate as Privilege_begDate,
										fp.frlPrivilege_Org as frlPrivilege_Org
									from stg.frlPrivilege fp
									inner join DocumentPrivilegeType dpt on fp.name_dl=dpt.DocumentPrivilegeType_Name
									where fp.PersonPrivilege_id is not null and fp.s_dl is not null and fp.n_dl is not null
									and not exists(select 1 from DocumentPrivilege DP 
									where DP.DocumentPrivilege_Num = fp.n_dl 
									and DocumentPrivilege_Ser = fp.s_dl 
									and DocumentPrivilege_begDate = to_char(fp.sn_dl, 'yyyy-mm-dd') 
									and DocumentPrivilegeType_id = dpt.DocumentPrivilegeType_id)

							loop
								fetch next from cur1 into
									p_DocumentPrivilegeType_id,
									p_PersonPrivilege_id,
									p_s_dl,
									p_n_dl,
									p_sn_dl,
									p_Privilege_begDate,
									p_frlPrivilege_Org;
									
								EXIT WHEN NOT FOUND;
								p_Error_Code := null;
								p_Error_Message := null;
								
								select
									fnc.Error_Code,
									fnc.Error_Message,
									fnc.Person_id
								into
									p_Error_Code,
									p_Error_Message,
									p_Person_id
								from p_DocumentPrivilege_ins(
									DocumentPrivilegeType_id := p_DocumentPrivilegeType_id,
									PersonPrivilege_id := p_PersonPrivilege_id,
									DocumentPrivilege_Ser := p_s_dl,
									DocumentPrivilege_Num := p_n_dl,
									DocumentPrivilege_begDate := p_sn_dl,
									DocumentPrivilege_Org := p_frlPrivilege_Org,
									pmUser_id := 1
								) fnc
								
								if (p_Error_Code is not null)
								then
									RAISE USING ERRCODE = p_error_code, MESSAGE = p_Error_Message;
								end if;
		
							end loop;
    						close cur1;
							end;
							$$
"
				),
				array(
					'title' => 'Обновление льгот',
					'sql' => "
						with mv as (select dbo.tzgetdate() as dt)
						update PersonPrivilege set server_id=3, personprivilege_enddate=t.enddate,PersonPrivilege_updDT=(select dt from mv)
						from PersonPrivilege
						inner join(
						select pg.personPrivilege_id,
						case when l.date_el='9999/99/99' then null
							 when l.date_el='' then null
							 when l.date_el='2030/01/01' then null
							 else to_char(l.date_el, 'yyyy-mm-dd') end as enddate
						from stg.frlPrivilege l
						inner join PersonPrivilege pg on pg.PersonPrivilege_id=l.PersonPrivilege_id
						and l.personPrivilege_id = pg.PersonPrivilege_id and pg.PersonPrivilege_endDate<>case when l.date_el='9999/99/99' then '2030-01-01'
						when l.date_el='' then '2030-01-01' else to_char(l.date_el, 'yyyy-mm-dd') end
						where l.RegisterListLog_id = :RegisterListLog_id
						) t on t.PersonPrivilege_id=PersonPrivilege.PersonPrivilege_id
					"
				),
				array(
					'title' => 'Удаление дублей льгот',
					'region' => array('ufa', 'saratov', 'khak'),
					'sql' => "select * from dbo.p_PersonPrivilege_DoubleDel()"
				),
				array(
					'title' => 'Закрытие устаревших льгот',
					'region' => array('perm','saratov','khak','krym'),
					'sql' => "
						with mv as (select dbo.tzgetdate() as dt)
						UPDATE dbo.PersonPrivilege
						SET PersonPrivilege_endDate = (select dt from mv),
							pmUser_updID = 1,
							PersonPrivilege_updDT = (select dt from mv)
						where personPrivilege_id  in (
						select pp.personPrivilege_id from PersonPrivilege pp
							inner join v_PrivilegeType pt on pt.PrivilegeType_id = pp.PrivilegeType_id
							left join stg.frlPerson fp on pp.Person_id = fp.Person_id
								and RegisterListLog_id = :RegisterListLog_id
							left join v_WhsDocumentCostItemType wdcit on wdcit.WhsDocumentCostItemType_id = pt.WhsDocumentCostItemType_id
							left join lateral(select PersonPrivilege_id as PPrivilege_id from stg.frlPrivilege dwhere  d.PersonPrivilege_id=pp.PersonPrivilege_id limit 1)fpr on true
						WHERE (fp.Person_id is null or (fp.Person_id is not null and fpr.PPrivilege_id is null))
							and personPrivilege_id = pp.personprivilege_id
							and coalesce(PersonPrivilege_endDate,'2030-01-01') >= (select dt from mv)
							and wdcit.WhsDocumentCostItemType_Nick='fl'
							and not exists( select PersonRefuse_id from PersonRefuse pr where pr.PersonRefuse_Year =date_part('year', (select dt from mv)) and pr.Person_id = pp.Person_id)
						);
					"
				),
				array(
					'title' => 'Закрытие устаревших льгот',
					'region' => 'ufa',
					'sql' => "
						with mv as (select dbo.tzGetDate() as dt)
						UPDATE dbo.PersonPrivilege
						SET PersonPrivilege_endDate = (select dt from mv),
							pmUser_updID = 1,
							PersonPrivilege_updDT = (select dt from mv)
						where personPrivilege_id  in (
						select pp.personPrivilege_id from PersonPrivilege pp
							inner join v_PrivilegeType pt on pt.PrivilegeType_id = pp.PrivilegeType_id
							left join stg.frlPerson fp on pp.Person_id = fp.Person_id
								and RegisterListLog_id = :RegisterListLog_id
							left join v_WhsDocumentCostItemType wdcit on wdcit.WhsDocumentCostItemType_id = pt.WhsDocumentCostItemType_id	
							left join lateral(select PersonPrivilege_id as PPrivilege_id from stg.frlPrivilege dwhere  d.PersonPrivilege_id=pp.PersonPrivilege_id limit 1)fpr on true
						WHERE (fp.Person_id is null or (fp.Person_id is not null and fpr.PPrivilege_id is null))
							and personPrivilege_id = pp.personprivilege_id
							and coalesce(PersonPrivilege_endDate,'2030-01-01') >= (select dt from mv)
							and wdcit.WhsDocumentCostItemType_Nick='fl'
						);
					"
				),
				array(
					'title' => 'Закрытие устаревших льгот не федерального значения, у которых период действия пересекается 
								с периодом действия льготы в ФРЛ',
					'region' => 'perm',
					'sql' => "
						with mv as (select dbo.tzGetDate() as dt)
						UPDATE dbo.PersonPrivilege
						SET server_id = 3,
							PersonPrivilege_endDate=(select dt from mv),
							pmUser_updID=1,
							PersonPrivilege_updDT=(select dt from mv)
						where personPrivilege_id in (
						select pp.personPrivilege_id from PersonPrivilege pp
							inner join v_PrivilegeType pt on pt.PrivilegeType_id = pp.PrivilegeType_id
							left join v_WhsDocumentCostItemType wdcit on wdcit.WhsDocumentCostItemType_id = pt.WhsDocumentCostItemType_id
							left join stg.frlPerson fp on pp.Person_id = fp.Person_id
							left join stg.frlPrivilege fpr on fp.Person_id = fpr.Person_id
						WHERE fp.Person_id is not null
							AND coalesce(PersonPrivilege_endDate,'2030-01-01') >= (select dt from mv)
							and wdcit.WhsDocumentCostItemType_Nick='rl'
							and
								(pp.PersonPrivilege_begDate < fpr.Privilege_begDate and
									(pp.PersonPrivilege_endDate is null or pp.PersonPrivilege_endDate > fpr.Privilege_begDate)
								) or
								(pp.PersonPrivilege_begDate > fpr.Privilege_begDate and
									(fpr.Privilege_endDate is null or pp.PersonPrivilege_begDate < coalesce(fpr.Privilege_endDate,'2030-01-01'))
								)
						)
					"
				),
				array(
					'title' => 'Закрываются все льготы, которые связаны с региональным регистром льготников',
					'region' => 'msk',
					'sql' => "
						with mv as (select dbo.tzGetDate() as dt)
						UPDATE dbo.PersonPrivilege
						SET PersonPrivilege_endDate=(select dbo.tzGetDate() as dt),
							pmUser_updID=1,
							PersonPrivilege_updDT=(select dbo.tzGetDate() as dt) 
						where personPrivilege_id in (
							select 
								pp.personPrivilege_id 
							from PersonPrivilege pp
								INNER JOIN v_PrivilegeType pt on pt.PrivilegeType_id = pp.PrivilegeType_id
								INNER JOIN v_WhsDocumentCostItemType WDCIT ON WDCIT.WhsDocumentCostItemType_id = pt.WhsDocumentCostItemType_id
								LEFT JOIN stg.frlPerson fp on pp.Person_id = fp.Person_id
							WHERE fp.Person_id is not null
								AND WDCIT.WhsDocumentCostItemType_Nick = 'rl'
						);
					"
				),
				array(
					'title' => 'Закрываются льготы по программе ЛЛО ССЗ, период действия которых пересекается 
								с периодами действия федеральной льготы либо отказа от федеральной льготы',
					'sql' => "
						with mv as (select dbo.tzGetDate() as dt)
						UPDATE dbo.PersonPrivilege
						SET server_id = 3,
							PersonPrivilege_endDate=(select dt from mv),
							pmUser_updID=1,
							PersonPrivilege_updDT=(select dt from mv)
						where personPrivilege_id in (
						select pp.personPrivilege_id from PersonPrivilege pp
							inner join v_PrivilegeType pt on pt.PrivilegeType_id = pp.PrivilegeType_id
							left join v_WhsDocumentCostItemType wdcit on wdcit.WhsDocumentCostItemType_id = pt.WhsDocumentCostItemType_id
							left join stg.frlPerson fp on pp.Person_id = fp.Person_id
							left join stg.frlPrivilege fpr on fp.Person_id = fpr.Person_id
						WHERE fp.Person_id is not null
							AND coalesce(PersonPrivilege_endDate,'2030-01-01') >= (select dt from mv)
							and wdcit.WhsDocumentCostItemType_Nick='acs'
							and (
									(pp.PersonPrivilege_begDate < fpr.Privilege_begDate and
										(pp.PersonPrivilege_endDate is null or pp.PersonPrivilege_endDate > fpr.Privilege_begDate)
									) or
									(pp.PersonPrivilege_begDate > fpr.Privilege_begDate and
										(fpr.Privilege_endDate is null or pp.PersonPrivilege_begDate < coalesce(fpr.Privilege_endDate,'2030-01-01'))
									)
								) or 
								(exists( select PersonRefuse_id from PersonRefuse pr where pr.PersonRefuse_Year =date_part('year', (select dt from mv)) and pr.Person_id = pp.Person_id))
						)
					"
				)

			)
		);

		// MedPersonal
		$actions['MedPersonal'] = array();
		$actions['MedPersonal']['RegisterList_id'] = $RegisterList_id;
		$actions['MedPersonal']['name'] = 'Федеральный регистр медицинского персонала';
		$actions['MedPersonal']['actions'] = array();
		$actions['MedPersonal']['actions'][1] = array(
			'title' => 'Загрузка файлов XML...',
			'type' => 'importXml'
		);
		$actions['MedPersonal']['actions'][2] = array(
			'title' => 'Выполнение загрузки...',
			'type' => 'queries',
			'queries' => array(
				array(
					'title' => 'Идентификация по СНИЛС',
					'sql' => "UPDATE p SET person_id=ps.person_id
FROM tmp._ERMP_Emplpyee p
INNER JOIN v_personstate ps ON replace(replace(p.Snils,'-',''),' ','')=ps.Person_Snils AND ps.Person_SurName=LTRIM(RTRIM(p.Surname))
WHERE p.person_id is null;",
					'log_affected_rows' => true
				),
				array(
					'title' => 'Идентификация по Ф.И.О. и дате рождения',
					'sql' => "UPDATE p SET person_id=ps.person_id
FROM tmp._ERMP_Emplpyee p
INNER JOIN v_personstate ps ON ps.Person_SurName=LTRIM(RTRIM(p.Surname))
AND ps.Person_FirName=LTRIM(RTRIM(p.Name)) AND ps.Person_SecName=LTRIM(RTRIM(p.Secname))
AND ps.Person_BirthDay=LTRIM(RTRIM(p.Birthdate))
WHERE p.person_id is null",
					'log_affected_rows' => true
				),
				array(
					'title' => 'Импорт новых данных о людях (1)',
					'sql' => "
							do
							$$
							declare
								cur1 REFCURSOR;
								p_Server_id bigint;
								p_Person_id bigint;
								p_Error_Code bigint;
								p_Error_Message varchar(4000);
								p_GUID UUID;
								p_Emplpyee_id bigint;
								p_pers_id bigint;
								p_Phone varchar(40);
								p_Surname varchar(30);
								p_Name varchar(30);
								p_SecName varchar(30);
								p_Birthdate timestamp;
								p_Sex_id bigint;
								p_Snils varchar(11);
								p_Inn varchar(20);
							p_Server_id := 4;
							
							begin
							 	open cur1 for
									select
										Emplpyee_id,
										Person_id as pers_id,
										Surname,
										Name,
										SecName,
										Birthdate,
										Sex_id,
										Snils,
										Inn,
										Phone,
										GUID
									from tmp._ERMP_Emplpyee
									WHERE person_id IS NULL
							
							loop
								fetch next from cur1 into
									p_Emplpyee_id,
									p_pers_id,
									p_Surname,
									p_Name,
									p_Secname,
									p_Birthdate,
									p_Sex_id,
									p_Snils,
									p_Inn,
									p_Phone,
									p_GUID;
									
								EXIT WHEN NOT FOUND;
								p_Error_Code := null;
								p_Error_Message := null;
								p_Person_id := null;
								
								select
									fnc.Error_Code,
									fnc.Error_Message,
									fnc.Person_id
								into
									p_Error_Code,
									p_Error_Message,
									p_Person_id
								from p_PersonAll_ins(
									PersonSurName_SurName := p_Surname,
									PersonFirName_FirName := p_Name,
									PersonSecName_SecName := p_Secname,
									PersonBirthDay_BirthDay := p_Birthdate,
									Sex_id := p_sex_id,
									Person_Guid := p_GUID,
									PersonPhone_Phone := p_Phone,
									PersonSnils_Snils := p_Snils,
									PersonInn_Inn := p_Inn,
									Server_id := 4,
									pmUser_id := 1
								) fnc;

							update tmp._ERMP_Emplpyee
							set Person_id=p_Person_id
							where Emplpyee_id=p_Emplpyee_id;
							
							if (p_Error_Code is not null)
							then
								RAISE USING ERRCODE = p_error_code, MESSAGE = p_Error_Message;
							end if;
		
							end loop;
    						close cur1;
							end;
							$$
					"
				),
				array(
					'title'=>'добавление информации о новых людях (11)',
					'sql'=>"
							do
							$$
							declare
								cur REFCURSOR;
								p_Server_id bigint;
								p_Person_id bigint;
								p_Cnt bigint;
								p_Error_Code bigint;
								p_Error_Message varchar(4000);
								p_amilyStatus_id BIGINT;
								p_ersonFamilyStatus_IsMarried bigint;
							p_Server_id := 4;
    						p_Cnt := 1;
							
							begin
								open cur for
									SELECT DISTINCT ps.Person_id,f.FamilyStatus_id,(CASE WHEN s.FamilyStatus_code IN (2) THEN 2 ELSE 1 END) AS PersonFamilyStatus_IsMarried 
									from PersonState ps
									inner join Person p on ps.Person_id = p.Person_id
									inner join tmp._ERMP_Emplpyee s on p.person_id = s.Person_id
									LEFT JOIN dbo.FamilyStatus f ON f.FamilyStatus_id=s.FamilyStatus_code
									where --p.Server_id = 4 and 
									ps.PersonFamilyStatus_id is null  AND s.FamilyStatus_code IS NOT NULL

							loop
								fetch next from cur into
									p_Person_id,
									p_PersonFamilyStatus_IsMarried,
									p_FamilyStatus_id;
									
								EXIT WHEN NOT FOUND;
								p_Error_Code := null;
								p_Error_Message := null;
								p_Cnt := p_Cnt + 1;
								
								select
									fnc.Error_Code,
									fnc.Error_Message
								into
									p_Error_Code,
									p_Error_Message
								from dbo.p_PersonFamilyStatus_ins(
									Server_id := p_Server_id, -- bigint
									PersonFamilyStatus_id := null, -- bigint
									Person_id := p_Person_id, -- bigint
									PersonFamilyStatus_Index := null, -- int
									PersonFamilyStatus_Count := null, -- int
									FamilyStatus_id := p_FamilyStatus_id, -- bigint
									PersonFamilyStatus_IsMarried := p_PersonFamilyStatus_IsMarried, -- bigint
									pmUser_id := 1, -- bigint
								) fnc
								if (p_Error_Code is not null)
								then
									RAISE USING ERRCODE = p_error_code, MESSAGE = p_Error_Message;
								end if;
		
							end loop;
    						close cur;
							end;
							$$
"
				),
				array(
					'title' => 'Добавление информации о новых людях (12)',
					'sql' => "
							do
							$$
							delcare
								cur12 REFCURSOR;
								p_Server_id bigint;
								p_Person_id bigint;
								p_Cnt bigint;
								p_Error_Code bigint;
								p_Error_Message varchar(4000);
								p_Nationality_id BIGINT;
							p_Server_id := 4;
    						p_Cnt := 1;
    						
							begin
								open cur12 for
									SELECT DISTINCT ps.Person_id,n.Nationality_id
									from PersonState ps
									inner join Person p on ps.Person_id = p.Person_id
									inner join tmp._ERMP_Emplpyee s on p.person_id = s.Person_id
									LEFT JOIN dbo.Nationality n ON n.Nationality_Code=s.Nationality_Code
									LEFT JOIN personinfo i ON i.Person_id=s.person_id
									where p.Server_id = 4 and i.Nationality_id is null  AND s.Nationality_Code IS NOT NULL
							loop
								fetch next from cur12 into
									p_Person_id,
									p_Nationality_id;
									
								EXIT WHEN NOT FOUND;
								p_Error_Code := null;
								p_Error_Message := null;
								p_Cnt := p_Cnt + 1;
								
								select
									fnc.Error_Code,
									fnc.Error_Message
								into
									p_Error_Code,
									p_Error_Message
								from dbo.p_PersonInfo_ins(
									Server_id := p_Server_id, -- bigint
									PersonInfo_id := null, -- bigint
									Person_id := p_Person_id, -- bigint
        							Nationality_id := p_Nationality_id, -- bigint
									pmUser_id := 1, -- bigint
								) fnc
								if (p_Error_Code is not null)
								then
									RAISE USING ERRCODE = p_error_code, MESSAGE = p_Error_Message;
								end if;
		
							end loop;
    						close cur12;
							end;
							$$
",
				),
				array(
					'title' => 'Добавление информации о новых людях (13)',
					'sql' => "
							do
							$$
							declare
								cur13 REFCURSOR;
								p_Server_id bigint;
								p_Person_id bigint;
								p_Cnt bigint;
								p_Error_Code bigint;
								p_Error_Message varchar(4000);
								p_PersonChildExist_setDT timestamp;
								p_PersonChildExist_IsChild BIGINT;
							p_Server_id := 4;
    						p_Cnt := 1;
    						
							begin
								open cur13 for
									SELECT DISTINCT
										ps.Person_id, CASE WHEN s.HasChildren=1 THEN 2 ELSE 1 END AS PersonChildExist_IsChild
									from PersonState ps
									inner join Person p on ps.Person_id = p.Person_id
									inner join tmp._ERMP_Emplpyee s on p.person_id = s.Person_id
									LEFT JOIN PersonChildExist i ON i.Person_id=s.person_id
									where p.Server_id = 4 and i.PersonChildExist_id is null  AND s.HasChildren IS NOT NULL
									
							loop
								fetch next from cur13 into
									p_Person_id,
									p_PersonChildExist_IsChild;
									
								EXIT WHEN NOT FOUND;
								p_Error_Code := null;
								p_Error_Message := null;
								p_PersonCarExist_setDT := '01.01.2000'::timestamp;
								p_Cnt := p_Cnt + 1;
								
								select
									fnc.Error_Code,
									fnc.Error_Message
								into
									p_Error_Code,
									p_Error_Message
								from dbo.p_PersonChildExist_ins(
									Server_id := p_Server_id, -- bigint
									PersonChildExist_id := NULL, -- bigint
									Person_id := p_Person_id, -- bigint
									PersonChildExist_setDT := p_PersonChildExist_setDT,
									PersonChildExist_IsChild := p_PersonChildExist_IsChild, -- bigint
									pmUser_id := 1, -- bigint
								) fnc
								if (p_Error_Code is not null)
								then
									RAISE USING ERRCODE = p_error_code, MESSAGE = p_Error_Message;
								end if;
		
							end loop;
    						close cur13;
							end;
							$$
",
				),//todo cursor
				array(
					'title' => 'Добавление информации о новых людях (14)',
					'sql' => "
							do
							$$
							declare
								cur14 REFCURSOR;
								p_Server_id bigint;
								p_Person_id bigint;
								p_Cnt bigint;
								p_Error_Code bigint;
								p_Error_Message varchar(4000);
								p_PersonCarExist_IsCar BIGINT;
								p_PersonCarExist_setDT timestamp;
							p_Server_id := 4;
    						p_Cnt := 1;
    						p_PersonCarExist_setDT := '01.01.2000'::timestamp;
							
							begin
								open cur14 for
									SELECT DISTINCT
										ps.Person_id, (CASE WHEN s.HasAuto=1 THEN 2 ELSE 1 END) AS PersonCarExist_IsCar
									from PersonState ps
									inner join Person p on ps.Person_id = p.Person_id
									inner join tmp._ERMP_Emplpyee s on p.person_id = s.Person_id
									LEFT JOIN PersonCarExist i ON i.Person_id=s.person_id
									where p.Server_id = 4 and i.PersonCarExist_id is null  AND s.HasAuto IS NOT NULL
							
							loop
								fetch next from cur14 into
									p_Person_id,
									p_PersonCarExist_IsCar;
								
								EXIT WHEN NOT FOUND;
								p_Error_Code := null;
								p_Error_Message := null;
								p_Cnt := p_Cnt + 1;
								
								select
									fnc.Error_Code,
									fnc.Error_Message
								into
									p_Error_Code,
									p_Error_Message
								from p_PersonCarExist_ins(
									Server_id := p_Server_id, -- bigint
									PersonCarExist_id := NULL, -- bigint
									Person_id := p_Person_id, -- bigint
									PersonCarExist_IsCar := p_PersonCarExist_IsCar, -- bigint
									PersonCarExist_setDT := p_PersonCarExist_setDT, -- timestamp
									pmUser_id := 1
								) fnc
								if (p_Error_Code is not null)
								then
									RAISE USING ERRCODE = p_error_code, MESSAGE = p_Error_Message;
								end if;
		
							end loop;
    						close cur14;
							end;
							$$
",
				),
				array(
					'title' => 'Добавление информации о новых людях (15)',
					'sql' => "
							do
							$$
							declare
								cur15 REFCURSOR;
								p_Server_id bigint;
								p_Person_id bigint;
								p_Cnt bigint;
								p_Error_Code bigint;
								p_Error_Message varchar(4000);
								p_OrgDep_id bigint,
								p_Org_id bigint,
								p_OrgDep1_id bigint;
								p_OrgDep_Name varchar(150);
								p_DocumentType_id bigint;
								p_Document_Ser varchar(10);
								p_Document_Num varchar(30);
								p_Document_begDate timestamp;
							p_Server_id := 4;
    						p_Cnt := 1;
								
							begin
								open cur15 for	
									select distinct
										ps.Person_id,
										DocumentType_id,
										nullif(replace(rtrim(ltrim(d.Document_Ser)),' ',''),'') as Document_Ser,
										nullif(rtrim(ltrim(d.Document_Num)),'') as Document_Num,
										o.orgdep_id,d.OrgDep_Name,
										Document_begDate as Document_begDate
									from PersonState ps
										inner join Person p on ps.Person_id = p.Person_id
										inner join tmp._ERMP_Emplpyee s on  p.Person_id = s.person_id
										INNER JOIN tmp._ERMP_Document d ON s.Emplpyee_id=d.Employee_id
										left join v_DocumentType DocumentType on d.DocumentType_Code = DocumentType.DocumentType_code
										left join v_OrgDep o on o.Orgdep_nick=d.OrgDep_Name or o.Orgdep_name=d.OrgDep_Name
									where p.Server_id = 4 and ps.Document_id is null
									
							loop
								fetch next from cur15 into
									p_Person_id,
									p_Document_Num,
									p_OrgDep1_id,
									p_orgDep_Name,
									p_Document_begDate;
								EXIT WHEN NOT FOUND;
								p_Error_Code := null;
								p_Error_Message := null;
								p_OrgDep_id := null;
								p_Cnt := p_Cnt + 1;

								if (p_OrgDep_id is null and p_OrgDep_Name in (select orgDep_name from v_orgDep)
								then
									select
										OrpDep_id
									into
										p_OrpDep_id
									from v_OrgDep
									where orgDep_Name = p_orgDep_Name;
								end if;
								if (OrgDep1_id is null and p_OrgDep_Name in (select orgDep_name from v_orgDep)
								then
									select
										fnc.Error_Code, fnc.Error_Message, fnc.Org_id
									into
										p_Error_Code, p_Error_Message, p_Org_id
									from p_Org_ins(
										Server_id := 0,
										Org_Name := p_OrgDep_Name,
										Org_Nick := p_OrgDep_Name,
										OrgType_id := 1,
										pmUser_id := 1
									) fnc;
									select
										fnc1.Error_Code, fnc1.Error_Message, fnc1.p_OrgDep_id
									into
										p_Error_Code, p_Error_Message, p_OrgDep_id
									from p_OrgDep_ins(
										Server_id := 0,
										Org_id := p_Org_id,
										pmUser_id := 1
									) fnc1;
								end if;
								if (p_OrgDep1_id is not null)
								then
									p_OrgDep_id := p_OrgDep1_id;
								end if;
								if (p_Document_Ser is not null and p_Document_Num is not null)
								then
									select
										fnc2.Error_Code, fnc2.Error_Message, fnc2.PersonDocument_id
									into
										p_Error_Code, p_Error_Message, p_PersonDocument_id
									from p_PersonDocument_ins(
										Server_id := p_Server_id,
										PersonDocument_id := null,
										Person_id := p_Person_id,
										PersonDocument_Index := null,
										PersonDocument_Count := null,
										PersonDocument_insDT := null,
										DocumentType_id := p_DocumentType_id,
										OrgDep_id := p_OrgDep_id,
										Document_Ser := p_Document_Ser,
										Document_Num := p_Document_Num,
										Document_begDate := p_Document_begDate,
										Document_endDate := null,
										pmUser_id := 1
									) fnc2;
								end if;
								if (p_Error_Code is not null)
								then
									RAISE USING ERRCODE = p_error_code, MESSAGE = p_Error_Message;
								end if;
		
							end loop;
    						close cur15;
							end;
							$$
					",
				),
				array(
					'title' => 'Адреса (1)',
					'sql' => "
update tmp._ERMP_Address set KLRgn_id=klarea_id
from tmp._ERMP_Address
inner join klarea on klarea.KLAdr_Code=tmp._ERMP_Address.District_code and KLAreaLevel_Id=1
					",
					'log_affected_rows' => true
				),
				array(
					'title' => 'Адреса (2)',
					'sql' => "update tmp._ERMP_Address set KLCity_id=klarea.KLArea_id,KLSubRgn_id =k.KLArea_id
from tmp._ERMP_Address
inner join klarea on klarea.KLAdr_Code=tmp._ERMP_Address.City_code and klarea.KLAreaLevel_id=3
left join klarea k on k.KLArea_id=klarea.KLArea_pid and k.KLAreaLevel_id=2
",
					'log_affected_rows' => true
				),
				array(
					'title' => 'Адреса (3)',
					'sql' => "update tmp._ERMP_Address set KLTown_id=klarea.KLArea_id,KLSubRgn_id =k.KLArea_id
from tmp._ERMP_Address
inner join klarea on klarea.KLAdr_Code=tmp._ERMP_Address.City_code and klarea.KLAreaLevel_id=4
left join klarea k on k.KLArea_id=klarea.KLArea_pid and k.KLAreaLevel_id=2
",
					'log_affected_rows' => true
				),
				array(
					'title' => 'Адреса (4)',
					'sql' => "update tmp._ERMP_Address set KLStreet_id=s.KLStreet_id from tmp._ERMP_Address
inner join KLStreet s on s.KLStreet_Name=tmp._ERMP_Address.Street and s.KLArea_id=coalesce(tmp._ERMP_Address.KLCity_id,tmp._ERMP_Address.KLTown_id)
",
					'log_affected_rows' => true
				),
				array(
					'title' => 'Адреса (5)',
					'sql' => "update tmp._ERMP_Address set KLStreet_id=s.KLStreet_id from tmp._ERMP_Address
inner join KLStreet s on s.KLStreet_Name=tmp._ERMP_Address.Street and s.KLArea_id IN (77,78)
",
					'log_affected_rows' => true
				),
				array(
					'title' => 'Адреса (6)',
					'sql' => "update tmp._ERMP_Address set KLStreet_id=s.KLStreet_id from tmp._ERMP_Address
inner join KLStreet s on s.KLStreet_Name=rtrim(ltrim(REPLACE(rtrim(ltrim(REPLACE(rtrim(LTRIM(REPLACE(rtrim(ltrim(REPLACE(rtrim(ltrim(REPLACE(rtrim(ltrim(REPLACE(rtrim(ltrim(tmp._ERMP_Address.Street)),'пр.',''))),'пер.',''))),'шоссе',''))),'пр-кт',''))),'проспект',''))),'переулок',''))) and s.KLArea_id=coalesce(tmp._ERMP_Address.KLCity_id,tmp._ERMP_Address.KLTown_id)
",
					'log_affected_rows' => true
				),
				array(
					'title' => 'Адреса (7)',
					'sql' => "update tmp._ERMP_Address set KLStreet_id=s.KLStreet_id from tmp._ERMP_Address
inner join v_KLStreet s on s.KLStreet_Name=REPLACE(rtrim(ltrim(REPLACE(rtrim(ltrim(REPLACE(rtrim(ltrim(tmp._ERMP_Address.Street)),'пер.',''))),'шоссе',''))),'пр-кт','') and s.KLArea_id=coalesce(tmp._ERMP_Address.KLCity_id,tmp._ERMP_Address.KLTown_id)
",
					'log_affected_rows' => true
				),
				array(
					'title' => 'Адрес регистрации',
					'sql' => "
					do
					$$
					declare
						cura REFCURSOR;
						p_Person_id bigint;
						p_Server_id bigint;
						p_Cnt bigint;
						p_Error_Code bigint;
						p_Error_Message varchar(4000);
						p_KLRgn_id bigint;
						p_KLSubRgn_id bigint;
						p_KLCity_id bigint;
						p_KLTown_id bigint;
						p_KLStreet_id bigint;
						p_Address_House varchar(5);
						p_Address_Corpus varchar(5);
						p_Address_Flat varchar(5);
						p_Error_Code varchar(10);
    					p_Error_Message varchar(4000);
    				p_Server_id := 4;
    				p_Cnt := 1;
    				begin
    					open cur1 for
							select distinct
								ps.Person_id,
								d.KLRgn_id,
								d.KLSubRgn_id,
								d.KLCity_id,
								d.KLTown_id,
								d.KLStreet_id,
								nullif(left(rtrim(ltrim(House)),10),''),
								nullif(left(rtrim(ltrim(Corpus)),5),''),
								nullif(left(rtrim(ltrim(Apartment)),5),'')
							from PersonState ps
								inner join Person p on ps.Person_id = p.Person_id
								inner join tmp._ERMP_Emplpyee s on  p.Person_id = s.person_id
								inner join tmp._ERMP_Address d on  s.Emplpyee_id=d.Employee_id and RegistrationTypeName='Постоянная регистрация'
								left join Address a on ps.UAddress_id = a.Address_id
							where p.Server_id = 4
								and (d.KLRgn_id is not null or d.KLCity_id is not null or d.KLTown_id is not NULL)
								AND ((Street IS NULL AND d.Klstreet_id is NULL) OR (Street IS not NULL and d.Klstreet_id is NOT null))
								and ps.UAddress_id is NULL
						
						loop
							fetch next from cura into
								p_Person_id,
								p_KLRgn_id,
								p_KLSubRgn_id,
								p_KLCity_id,
								p_KLTown_id,
								p_KLStreet_id,
								p_Address_House,
								p_Address_Corpus,
								p_Address_Flat;
							EXIT WHEN NOT FOUND;
							p_Error_Code := null;
							p_Error_Message := null;

							select
								fnc.Error_Code, fnc.Error_Message
							into
								p_Error_Code, p_Error_Message
							from p_PersonUAddress_ins(
								Server_id := p_Server_id,
								PersonUAddress_id := null,
								Person_id := p_Person_id,
								PersonUAddress_Index := null,
								PersonUAddress_Count := null,
								PersonUAddress_insDT := null,
								Address_id := null,
								KLAreaType_id := null,
								KLCountry_id := 643,
								KLRgn_id := p_KLRgn_id,
								KLSubRgn_id := p_KLSubRgn_id,
								KLCity_id := p_KLCity_id,
								KLTown_id := p_KLTown_id,
								KLStreet_id := p_KLStreet_id,
								Address_Zip := null,
								Address_House := p_Address_House,
								Address_Corpus := p_Address_Corpus,
								Address_Flat := p_Address_Flat,
								Address_Address := null,
								pmUser_id := 1
							) fnc
							if (p_Error_Code is not null)
							then
								RAISE USING ERRCODE = p_error_code, MESSAGE = p_Error_Message;
							end if;
		
						end loop;
    					close cur1;
					end;
					$$
",
				),
				array(
					'title' => 'Адрес проживания',
					'sql' => "
					do
					$$
					declare
						curp REFCURSOR;
						p_Person_id bigint;
						p_Server_id bigint;
						p_Cnt bigint;
						p_Error_Code bigint;
						p_Error_Message varchar(4000);
						p_KLRgn_id bigint;
						p_KLSubRgn_id bigint;
						p_KLCity_id bigint;
						p_KLTown_id bigint;
						p_KLStreet_id bigint;
						p_Address_House varchar(5);
						p_Address_Corpus varchar(5);
						p_Address_Flat varchar(5);
						p_Error_Code varchar(10);
    					p_Error_Message varchar(4000);
    				p_Server_id := 4;
    				p_Cnt := 1;
    				
    				begin
    					open curp for
							select distinct
								ps.Person_id,
								d.KLRgn_id,
								d.KLSubRgn_id,
								d.KLCity_id,
								d.KLTown_id,
								d.KLStreet_id,
								nullif(left(rtrim(ltrim(House)),10),''),
								nullif(left(rtrim(ltrim(Corpus)),5),''),
								nullif(left(rtrim(ltrim(Apartment)),5),'')
							from PersonState ps
								inner join Person p on ps.Person_id = p.Person_id
								inner join tmp._ERMP_Emplpyee s on  p.Person_id = s.person_id
								inner join tmp._ERMP_Address d on  s.Emplpyee_id=d.Employee_id and RegistrationTypeName='Временная регистрация'
								left join Address a on ps.PAddress_id = a.Address_id
							where p.Server_id = 4
								and (d.KLRgn_id is not null or d.KLCity_id is not null or d.KLTown_id is not NULL)
								AND ((Street IS NULL AND d.Klstreet_id is NULL) OR (Street IS not NULL and d.Klstreet_id is NOT null))
								and ps.PAddress_id is null
						
						loop
							fetch next from curp into
								p_Person_id,
								p_KLRgn_id,
								p_KLSubRgn_id,
								p_KLCity_id,
								p_KLTown_id,
								p_KLStreet_id,
								p_Address_House,
								p_Address_Corpus,
								p_Address_Flat;
							EXIT WHEN NOT FOUND;
							p_Error_Code := null;
							p_Error_Message := null;
							
							select
								fnc.Error_Code, fnc.Error_Message
							into
								p_Error_Code, p_Error_Message
							from p_PersonPAddress_ins (
								Server_id := p_Server_id,
								PersonPAddress_id := null,
								Person_id := p_Person_id,
								PersonPAddress_Index := null,
								PersonPAddress_Count := null,
								PersonPAddress_insDT := null,
								Address_id := null,
								KLAreaType_id := null,
								KLCountry_id := 643,
								KLRgn_id := p_KLRgn_id,
								KLSubRgn_id := p_KLSubRgn_id,
								KLCity_id := p_KLCity_id,
								KLTown_id := p_KLTown_id,
								KLStreet_id := p_KLStreet_id,
								Address_Zip := null,
								Address_House := p_Address_House,
								Address_Corpus := p_Address_Corpus,
								Address_Flat := p_Address_Flat,
								Address_Address := null,
								pmUser_id := 1
							) fnc
							if (p_Error_Code is not null)
							then
								RAISE USING ERRCODE = p_error_code, MESSAGE = p_Error_Message;
							end if;
		
						end loop;
    					close cur1;
					end;
					$$
"),
				//Информация о врачебности

				array(
					'title' => 'Информация о враче(1)',
					'sql' => "
						insert into persis.MedWorker
						(insDT	,pmUser_insID,	updDT,	pmUser_updID,	version,	Person_id,	CodeDLO,	HonouredBrevetDate,	PeoplesBrevetDate,current_region)
						select distinct dbo.tzgetdate(),1,dbo.tzgetdate(),1,0,person_id,null,null,null,10
						from tmp._ERMP_Emplpyee f
						--inner JOIN tmp.ERMP_WorkPlace f1 ON f1.Emplpyee_id=f.Emplpyee_id and f1.EndDate is null
						WHERE person_id NOT IN (select person_id from persis.MedWorker)
					"
				),
				array(
					'title' => 'Информация о враче(2)',
					'sql' => "
						UPDATE s SET MedWorker_id=m.id
						from tmp._ERMP_Emplpyee s
						INNER JOIN persis.MedWorker m ON s.Person_id=m.Person_id
						where s.MedWorker_id is null 	
					"
				),
				array(
					'title' => 'Информация о враче(3)', //Диплом
					'sql' => "
						insert into persis.SpecialityDiploma 
						(insdt,pmUser_insID,updDT,pmUser_updID,version,YearOfGraduation,
						DiplomaNumber,DiplomaSeries,OtherEducationalInstitution,DiplomaSpeciality_id,
						EducationType_id,EducationInstitution_id,MedWorker_id)
						select distinct dbo.tzgetdate(),1,dbo.tzgetdate(),1,1, f.YearOfGraduation AS YearOfGraduation,
						f.DiplomaNumber AS DiplomaNumber,f.DiplomaSeries AS DiplomaSeries,NULL,d.id AS DiplomaSpeciality_id,
						t.id AS EducationType_id,e.id AS EducationInstitution_id, s.MedWorker_id 
						FROM tmp._ERMP_SpecialityDiploma f
						INNER JOIN persis.EducationInstitution e ON f.EducationInstitution_id = e.frmpEntry_id
						INNER JOIN persis.DiplomaSpeciality d ON f.DiplomaSpeciality_id=d.frmpEntry_id
						INNER JOIN persis.EducationType t ON f.EducationType_id=t.id
						INNER JOIN tmp._ERMP_Emplpyee s ON s.Emplpyee_id=f.Emplpyee_id
						left join persis.SpecialityDiploma sp on s.MedWorker_id=sp.MedWorker_id --and 
						--inner JOIN tmp.ERMP_WorkPlace f1 ON f1.Emplpyee_id=f.Emplpyee_id and f1.EndDate is null
						where sp.MedWorker_id is null			
					"
				),
				array(
					'title' => 'Информация о враче(4)',
					'sql' => "
						INSERT INTO persis.Reward ( insDT ,pmUser_insID ,updDT ,pmUser_updID ,version ,date ,name ,number ,MedWorker_id)
						select distinct dbo.tzgetdate(),1,dbo.tzgetdate(),1,1,f.date, f.name,f.number,s.MedWorker_id
						FROM tmp._ERMP_Reward f
						INNER JOIN tmp._ERMP_Emplpyee s ON s.Emplpyee_id=f.Emplpyee_id
						left join persis.Reward r on r.MedWorker_id=s.MedWorker_id
						--inner JOIN tmp.ERMP_WorkPlace f1 ON f1.Emplpyee_id=f.Emplpyee_id and f1.EndDate is null
						WHERE r.MedWorker_id is null					
					"
				),
				array(
					'title' => 'Информация о враче(5)',
					'sql' => "
						INSERT INTO persis.PostgraduateEducation 
						(insDT,pmUser_insID,updDT,pmUser_updID,version,graduationDate,startDate,DiplomaNumber,
						endDate,DiplomaSeries,PostgraduateEducationType_id,Speciality_id,EducationInstitution_id,AcademicMedicalDegree_id,MedWorker_id)
						SELECT DISTINCT dbo.tzgetdate(),1,dbo.tzgetdate(),1,1,pe.graduationDate AS graduationDate,pe.startDate AS startDate,pe.DiplomaNumber AS DiplomaNumber,
						pe.endDate AS endDate,pe.DiplomaSeries AS DiplomaSeries,p.id AS PostgraduateEducationType_id,s.id AS Speciality_id,e.id AS EducationInstitution_id,
						a.id AS AcademicMedicalDegree_id,s1.MedWorker_id
						FROM tmp._ERMP_PostgraduateEducation pe
						inner JOIN persis.EducationInstitution e ON pe.EducationInstitution_id = e.frmpEntry_id
						LEFT JOIN persis.PostgraduateEducationType p ON pe.PostgraduateEducationType_id = p.id
						left JOIN persis.AcademicMedicalDegree a ON pe.AcademicMedicalDegree_id = a.id
						inner JOIN persis.Speciality s ON pe.Speciality_id=s.code
						inner JOIN tmp._ERMP_Emplpyee s1 ON s1.Emplpyee_id=pe.Emplpyee_id
						left join persis.PostgraduateEducation pe1 on pe1.MedWorker_id=s1.MedWorker_id
						--inner JOIN tmp.ERMP_WorkPlace f1 ON f1.Emplpyee_id=s1.Emplpyee_id and f1.EndDate is null
						WHERE pe1.MedWorker_id is null
					"
				),
				array(
					'title' => 'Информация о враче(6)',
					'sql' => "
						INSERT INTO persis.Certificate (insDT,pmUser_insID,updDT,pmUser_updID,version,
						CertificateReceipDate,CertificateNumber,CertificateSeries,Speciality_id,
						EducationInstitution_id,MedWorker_id)
						SELECT distinct dbo.tzgetdate(),1,dbo.tzgetdate(),1,1,c.CertificateReceipDate,c.CertificateNumber,c.CertificateSeries,s.id AS Speciality_id,
						e.id AS EducationInstitution_id,f.MedWorker_id
						--SELECT * 
						FROM tmp._ERMP_Certificate c
						left JOIN persis.EducationInstitution e ON c.EducationInstitution_id = e.frmpEntry_id
						inner JOIN tmp._ERMP_Emplpyee f ON f.Emplpyee_id=c.Emplpyee_id
						--inner JOIN tmp.ERMP_WorkPlace f1 ON f1.Emplpyee_id=tmp.[_ERMP_Emplpyee].Emplpyee_id and f1.EndDate is null
						inner JOIN persis.Speciality s ON c.Speciality_id=s.code
						left join persis.Certificate c1 on c1.MedWorker_id=f.MedWorker_id
						WHERE c1.MedWorker_id is null						
					"
				),
				array(
					'title' => 'Информация о враче(7)',
					'sql' => "
						INSERT INTO persis.QualificationImprovementCourse(
						insDT,pmUser_insID,updDT,pmUser_updID,version,
						Year,DocumentRecieveDate,HoursCount,DocumentNumber,DocumentSeries,
						Round,Speciality_id,EducationInstitution_id,MedWorker_id)
						SELECT distinct dbo.tzgetdate(),1,dbo.tzgetdate(),1,1, f.Year,f.DocumentRecieveDate,f.HoursCount,
						f.DocumentNumber,f.DocumentSeries, f.Round, s1.id AS Speciality_id,e.id AS EducationInstitution_id,s.MedWorker_id
						--SELECT * 
						FROM tmp._ERMP_QualificationImprovementCourse f
						left JOIN persis.EducationInstitution e ON f.EducationInstitution_id = e.frmpEntry_id
						inner JOIN tmp._ERMP_Emplpyee s ON s.Emplpyee_id=f.Emplpyee_id
						--inner JOIN tmp.ERMP_WorkPlace f1 ON f1.Emplpyee_id=f.Emplpyee_id and f1.EndDate is null
						INNER JOIN persis.Speciality s1 ON f.Speciality_id=s1.code
						left join persis.QualificationImprovementCourse q on q.MedWorker_id=s.MedWorker_id
						WHERE q.MedWorker_id is null
					"
				),
				array(
					'title' => 'Информация о враче(8)',
					'sql' => "
						INSERT INTO persis.QualificationCategory(
						insDT,pmUser_insID,updDT,pmUser_updID,version,
						Category_id,Speciality_id,MedWorker_id,AssigmentDate)
						SELECT distinct dbo.tzgetdate(),1,dbo.tzgetdate(),1,1,e.id AS Category_id,s1.id AS Speciality_id,s.MedWorker_id,f.AssigmentDate
						--SELECT * 
						FROM tmp._ERMP_QualificationCategory f
						INNER JOIN persis.Category e ON f.Category_code = e.code
						INNER JOIN persis.Speciality s1 ON f.Speciality_id=s1.code
						inner JOIN tmp._ERMP_Emplpyee s ON s.Emplpyee_id=f.Emplpyee_id
						--inner JOIN tmp.ERMP_WorkPlace f1 ON f1.Emplpyee_id=f.Emplpyee_id and f1.EndDate is null
						left join persis.QualificationCategory qc on qc.MedWorker_id=s.Medworker_id
						WHERE qc.MedWorker_id is null					
					"
				),
				array(
					'title' => 'Информация о враче(8.2)',
					'sql' => '
						INSERT INTO persis.RetrainingCourse
						(insDT,pmUser_insID,updDT,pmUser_updID,version,PassYear,HoursCount,DocumentNumber,DocumentSeries,
						OtherEducationalInstitution,Speciality_id,EducationInstitution_id,MedWorker_id)
						SELECT distinct dbo.tzgetdate(),1,dbo.tzgetdate(),1,1,f.PassYear,f.HoursCount,f.DocumentNumber,f.DocumentSeries,e.id AS OtherEducationalInstitution,
						s.id AS Speciality_id,e.id AS EducationInstitution_id,em.MedWorker_id
						--SELECT *
						FROM tmp._ERMP_RetrainingCourse f
						left JOIN persis.EducationInstitution e ON f.EducationInstitution_id = e.frmpEntry_id
						inner JOIN tmp._ERMP_Emplpyee em ON em.Emplpyee_id=f.Emplpyee_id
						--inner JOIN tmp.ERMP_WorkPlace f1 ON f1.Emplpyee_id=f.Emplpyee_id and f1.EndDate is null
						INNER JOIN persis.Speciality s ON f.Speciality_id=s.code
						left join persis.RetrainingCourse rc on rc.medworker_id=em.Medworker_id
						WHERE rc.medworker_id is NULL
					'
				),
				array(
					'title' => 'Информация о враче(9)',
					'sql' => "
						with mv as (
							select
								LpuSectionProfile_id as lsp
							from dbo.LpuSectionProfile
							where LpuSectionProfile_Name ilike 'терапи%'
							limit 1
						)

						update tmp._ERMP_WorkPlace set lpu_id=".$_SESSION['lpu_id'].";

						INSERT INTO dbo.LpuBuilding
						( Server_id ,Lpu_id ,LpuBuildingType_id, LpuBuilding_Code ,LpuBuilding_Name ,pmUser_insID ,pmUser_updID ,LpuBuilding_insDT ,LpuBuilding_updDT )
						SELECT DISTINCT 3, s.Lpu_id,1,1,'ЕРМП',1,1,dbo.tzgetdate(),dbo.tzgetdate()
						FROM tmp._ERMP_WorkPlace s
						left JOIN dbo.LpuBuilding lb ON s.Lpu_id = lb.Lpu_id and lb.LpuBuilding_Name='ЕРМП'
						where lb.LpuBuilding_id is null and s.lpu_id in (".$_SESSION['lpu_id'].")
						and _ERMP_WorkPlace_insDT>'2013-07-07' and s.lpu_id is not null;

						INSERT INTO dbo.LpuUnit
								( Server_id ,LpuBuilding_id ,LpuUnitType_id ,LpuUnit_Code ,LpuUnit_Name ,pmUser_insID ,pmUser_updID ,LpuUnit_insDT ,LpuUnit_updDT)
						SELECT  distinct 3,lb.LpuBuilding_id,5,5,'Фиктивные ставки',1,1,dbo.tzgetdate(),dbo.tzgetdate()
						FROM tmp.ERMP_WorkPlace s
						inner JOIN dbo.LpuBuilding lb ON s.Lpu_id = lb.Lpu_id and lb.LpuBuilding_Name='ЕРМП'
						left join dbo.LpuUnit lu on lb.LpuBuilding_id = lu.LpuBuilding_id and lu.LpuUnit_Name='Фиктивные ставки'
						where lu.LpuUnit_id is null
						and s.lpu_id is not null;
						
						--- отделения
						INSERT INTO dbo.LpuSection
								( Server_id ,LpuUnit_id ,LpuSectionProfile_id ,LpuSection_Code ,LpuSection_Name ,pmUser_insID ,pmUser_updID ,LpuSection_insDT ,LpuSection_updDT)
						SELECT distinct 3,lu.LpuUnit_id,(select lsp from mv),1,'Фиктивные ставки',1,1,dbo.tzgetdate(),dbo.tzgetdate()
						FROM tmp.ERMP_WorkPlace s
						inner JOIN dbo.LpuBuilding lb ON s.Lpu_id = lb.Lpu_id and lb.LpuBuilding_Name='ЕРМП'
						inner join dbo.LpuUnit lu on lb.LpuBuilding_id = lu.LpuBuilding_id and lu.LpuUnit_Name='Фиктивные ставки'
						left join lpusection ls on ls.lpuunit_id=lu.lpuunit_id and ls.LpuSection_Name='Фиктивные ставки'
						where ls.lpusection_id is null
						and s.lpu_id is not null;
						
						update tmp.ERMP_WorkPlace set FRMPPost_id=s.code
						FROM tmp.ERMP_WorkPlace f
						left join(select pp.code,pp.name from persis.post pp)s on f.FRMPPost_name = s.name
					"
				),
				array(
					'title' => 'Информация о враче(9)',
					'sql' => "
					insert into persis.Staff(insDT,pmUser_insID,updDT,pmUser_updID,version,MedicalCareKind_id,Begindate,
					rate,Post_id,LpuSection_id, PayType_id, IsVillageBonus,Lpu_id,LpuBuilding_id,LpuUnit_id,IsDummyStaff)

					SELECT distinct
						dbo.tzgetdate(),
						1,
						dbo.tzgetdate(),
						1,
						1,
						coalesce(f.MedicalCareKind_id,1) AS MedicalCareKind_id,
						'2000-01-01',
						50 AS Staff_Rate,
						pp.id,
						ls.lpusection_id,
						paytype.paytype_id,0,
						ls.Lpu_id,
						lb.LpuBuilding_id,
						ls.LpuUnit_id,
						1
					FROM tmp._ERMP_WorkPlace f
						inner join persis.post pp ON pp.code=f.FRMPPost_id
						INNER JOIN dbo.v_LpuSection ls ON f.Lpu_id = ls.Lpu_id
						left join lateral(select paytype_id from v_paytype where paytype_sysnick = 'oms' limit 1) paytype on true
						LEFT JOIN persis.v_staff s ON  ls.LpuSection_id = s.LpuSection_id AND s.Post_id=f.FRMPPost_id
						inner JOIN dbo.LpuBuilding lb ON ls.Lpu_id = lb.Lpu_id and lb.LpuBuilding_Name='ЕРМП'
					WHERE s.id IS NULL and f.lpu_id is not null and ls.LpuSection_Name = 'Фиктивные ставки'	
					"
				),
				array(
					'title' => 'Информация о враче(9)',
					'sql' => "
					insert into persis.WorkPlace(insDT,pmUser_insID,updDT,pmUser_updID,version,--id,
						MilitaryRelation_id, --отношение к военной службе
						BeginDate , -- дата начала работы
						EndDate , -- дата окончания работы
						ArriveOrderNumber, -- номер приказа на начало работы
						LeaveOrderNumber, -- номер приказа на окончание работы
						ArriveRecordType_id, -- тип записи на начало работы  
						Comments, --примечание
						Rate , -- ставка
						PostOccupationType_id, -- тип занятия должности        
						Population, -- численность прикрепленного населения
						MedSpecOms_id, -- S90
						FRMPSubdivision_id, -- тип подразделения (FRMPSubdivision)
						LeaveRecordType_id, -- тип записи на окончание работы (LeaveRecordType)
						WorkMode_id, -- режим работы (WorkMode)
						MedWorker_id , -- мед работник (MedWorker)
						Staff_id , -- строка штатного расписания (Staff)
						TabCode, -- табельный номер
						PriemTime, -- времяПриема
						Contacts , -- контактнаяИнформация
						Descr , -- примечаниеВрача
						IsDirRec, -- разрешатьЗаписьЧерезНаправления Нет(0)/Да(1)
						IsQueueOnFree, -- ставитьВОчередьПриНаличииСвободныхБирок Нет(0)/Да(1)
						IsOms, -- работает в ОМС Нет(0)/Да(1)
						RecType_id,
						DLObeginDate,
						DLOEndDate,
						DisableWorkPlaceChooseInDocuments,
						IsNotReception,
						IsDummyWP
					)
					SELECT DISTINCT
						dbo.tzgetdate(),
						1,
						dbo.tzgetdate(),
						1,
						1,
						mr.id AS MilitaryRelation_id, --отношение к военной службе
						f.BeginDate AS BeginDate , -- дата начала работы
						f.EndDate AS EndDate , -- дата окончания работы
						f.ArriveOrderNumber AS ArriveOrderNumber, -- номер приказа на начало работы
						f.LeaveOrderNumber AS LeaveOrderNumber, -- номер приказа на окончание работы
						art.id AS ArriveRecordType_id, -- тип записи на начало работы  
						NULL AS Comments, --примечание
						f.Rate AS Rate , -- ставка
						pot.id AS PostOccupationType_id, -- тип занятия должности        
						f.Population AS Population, -- численность прикрепленного населения
						NULL AS MedSpecOms_id, -- S90 !!!!!!!
						fs.id AS FRMPSubdivision_id, -- тип подразделения (FRMPSubdivision)
						lrt.id AS LeaveRecordType_id, -- тип записи на окончание работы (LeaveRecordType)
						1 AS WorkMode_id, -- режим работы (WorkMode)
						f1.MedWorker_id AS MedWorker_id , -- мед работник (MedWorker)
						s1.id AS Staff_id , -- строка штатного расписания (Staff)
						LEFT(f.TabCode,10) AS TabCode, -- табельный номер
						NULL AS PriemTime, -- времяПриема
						NULL AS Contacts , -- контактнаяИнформация
						NULL AS Descr , -- примечаниеВрача
						0 AS IsDirRec, -- разрешатьЗаписьЧерезНаправления Нет(0)/Да(1)
						0 AS IsQueueOnFree, -- ставитьВОчередьПриНаличииСвободныхБирок Нет(0)/Да(1)
						1 AS IsOms, -- работает в ОМС Нет(0)/Да(1)
						NULL AS RecType_id, --тип записи,
						NULL AS DLObeginDate,
						NULL AS  DLOEndDate,
						0 AS DisableWorkPlaceChooseInDocuments,
						0 as IsNotReception,
						1 as IsDummyWP
					FROM tmp._ERMP_WorkPlace f
						inner JOIN tmp._ERMP_Emplpyee f1 ON f1.Emplpyee_id=f.Emplpyee_id
						inner join persis.post pp ON pp.code=f.FRMPPost_id
						INNER JOIN v_LpuSection ls ON f.Lpu_id = ls.Lpu_id 
						INNER JOIN persis.Staff s1 ON s1.Lpusection_id = ls.Lpusection_id AND s1.Post_id=pp.id AND s1.MedicalCareKind_id=coalesce(f.MedicalCareKind_id,1)
						LEFT JOIN persis.MilitaryRelation mr ON mr.code=f.MilitaryRelation_id
						LEFT JOIN persis.ArriveRecordType art ON art.code=f.ArriveRecordType_id
						LEFT JOIN persis.PostOccupationType pot ON pot.code=f.PostOccupationType_id
						LEFT JOIN persis.LeaveRecordType lrt ON lrt.code=f.LeaveRecordType_id
						LEFT JOIN persis.FRMPSubdivision fs ON fs.id=f.FRMPSubdivision_id
						LEFT JOIN persis.v_WorkPlace wp1  ON f.Rate = wp1.rate AND f.TabCode = wp1.TabCode AND wp1.MedWorker_id=f1.MedWorker_id AND f.BeginDate = wp1.BeginDate and wp1.lpu_id = ".$_SESSION['lpu_id']."
					--AND wp1.Staff_id=s1.id
					WHERE wp1.WorkPlace_Id IS NULL
					and f.lpu_id is not null and ls.LpuSection_Name = 'Фиктивные ставки';


					select
						Error_Code as \"Error_Code\",
						Error_Message as \"Error_Msg\"
					from persis.p_WorkPlace_ins(
						IsReload := -1,
						IsMerge := 1
					)
"
				),
				array(
					'title' => 'Информация о враче(9)',
					'sql' => "
					insert into dbo.MedStaffFactCache (Server_id, MedStaffFact_id, MedPersonal_id, Person_id, Staff_id, Lpu_id, LpuUnit_id, LpuSection_id, MedPersonal_Code, MedPersonal_TabCode,
						MedStaffFact_Stavka, MedStaffFact_IsOms, MedStaffFact_IsDlo, WorkData_begDate, WorkData_endDate, WorkData_dlobegDate, WorkData_dloendDate, MedSpecOms_id, 
						Post_id, MedicalCareKind_id, PostKind_id, RecType_id, MedStaffFact_PriemTime,
						MedStaffFact_IsDirRec, MedStaffFact_IsQueueOnFree, MedStaffFact_Descr, MedStaffFact_Contacts, pmUser_insID, pmUser_updID, 
						MedStaffFact_insDT, MedStaffFact_updDT,
						PostOccupationType_id, Population, ArriveOrderNumber, Comments, LeaveRecordType_id,LpuBuilding_id,MedStaffFactCache_IsDisableInDoc
					)
					select
						WorkPlace.Lpu_id as \"Server_id\",
						WorkPlace.WorkPlace_id as \"MedStaffFact_id\",
						WorkPlace.MedWorker_id as \"MedPersonal_id\",
						WorkPlace.Person_id as \"Person_id\",
						WorkPlace.Staff_id as \"Staff_id\",
						WorkPlace.Lpu_id as \"Lpu_id\",
						WorkPlace.LpuUnit_id as \"LpuUnit_id\",
						WorkPlace.LpuSection_id as \"LpuSection_id\",
						WorkPlace.CodeDLO as \"MedPersonal_Code\",
						WorkPlace.TabCode as \"MedPersonal_TabCode\",
						WorkPlace.Rate as \"MedStaffFact_Stavka\",
						WorkPlace.IsOms + 1 as \"MedStaffFact_IsOms\",
						case
							when WorkPlace.CodeDLO is not null then 2
							else 1
						end as \"MedStaffFact_IsDlo\",
						WorkPlace.BeginDate as \"WorkData_begDate\",
						WorkPlace.EndDate as \"WorkData_endDate\",
						WorkPlace.DLOBeginDate as \"WorkData_dlobegDate\",
						WorkPlace.DLOEndDate as \"WorkData_dloendDate\",
						WorkPlace.MedSpecOms_id as \"MedSpecOms_id\",
						WorkPlace.Post_id as \"Post_id\",
						WorkPlace.MedicalCareKind_id as \"MedicalCareKind\",
						WorkPlace.PostKind_id as \"PostKind_id\",
						WorkPlace.RecType_id as \"RecType_id\",
						WorkPlace.PriemTime as \"MedStaffFact_PriemTime\",
						WorkPlace.IsDirRec + 1 as \"MedStaffFact_IsDirRec\",
						WorkPlace.IsQueueOnFree + 1 as \"MedStaffFact_IsQueueOnFree\",
						nullif(WorkPlace.Descr,'') as \"MedStaffFact_Descr\",
						nullif(WorkPlace.Contacts,'') as \"MedStaffFact_Contacts\",
						WorkPlace.pmUser_insID as \"pmUser_insID\",
						WorkPlace.pmUser_updID as \"pmUser_updID\",
						WorkPlace.insDT as \"MedStaffFact_insDT\",
						WorkPlace.updDT as \"MedStaffFact_updDT\",
						WorkPlace.PostOccupationType_id as \"PostOccupationType_id\",
						WorkPlace.Population as \"Population\",
						WorkPlace.ArriveOrderNumber as \"ArriveOrderNumber\",
						WorkPlace.Comments as \"Comments\",
						WorkPlace.LeaveRecordType_id as \"LeaveRecordType_id\",
						WorkPlace.LpuBuilding_id as \"LpuBuilding_id\",
						case
							when WorkPlace.DisableWorkPlaceChooseInDocuments=1 then 2
							when WorkPlace.DisableWorkPlaceChooseInDocuments=0 then 1
							else null
						end as \"MedStaffFactCache_IsDisableInDoc\"
					from persis.v_WorkPlace WorkPlace
						left join dbo.MedStaffFactCache MedStaffFactCache on WorkPlace.WorkPlace_id = MedStaffFactCache.MedStaffFact_id
					where MedStaffFactCache.MedStaffFact_id is null"
				)
			)
		);

		// PersisFRMP
		$actions['PersisFRMP'] = array();
		$actions['PersisFRMP']['RegisterList_id'] = $RegisterList_id;
		$actions['PersisFRMP']['name'] = 'Справочник ФРМП';
		$actions['PersisFRMP']['actions'] = array();
		$actions['PersisFRMP']['actions'][1] = array(
			'title' => 'Загрузка файлов XML...',
			'type' => 'importFRMP',
			'files' => array(
				'academicdegree.xml' => array(
					"name" => 'AcademicDegree',
					"table" => "xp_AcademicMedicalDegree_import",
				),
				'citizenship.xml' => array(
					"name" => 'Сitizenship',
					"table" => "xp_FRMPCitizenshipState_import"
				),
				'documtypes.xml' => array(
					"name" => 'DocumentType',
					"table" => "xp_FRMPDocumentType_import"
				),
				'educationinstitution.xml' => array(
					"name" => 'EducationInstitution',
					"table" => "xp_FRMPEducationInstitution_import"
				),
				'educationtype.xml' => array(
					"name" => 'EducationType',
					"table" => "xp_FRMPEducationType_import"
				),
				'level.xml' => array(
					"name" => 'Level',
					"table" => "xp_FRMPLpuLevel_import"
				),
				'medicalcare.xml' => array(
					"name" => 'MedicalCare',
					"table" => "xp_MedicalCareKind_import"
				),
				'military.xml' => array(
					"name" => 'Military',
					"table" => "xp_MilitaryRelation_import"
				),
				'nomenclature.xml' => array(
					"name" => 'Nomenclature',
					"table" => "xp_FRMPNomenclature_import"
				),
				'positiontype.xml' => array(
					"name" => 'PositionType',
					"table" => "xp_FRMPPositionType_import"
				),
				'post.xml' => array(
					"name" => 'Post',
					"table" => "xp_FRMPPost_import"
				),
				'qualificationcategory.xml' => array(
					"name" => 'QualificationCategory',
					"table" => "xp_Category_import"
				),
				'recordtypeout.xml' => array(
					"name" => 'RecordTypeOut',
					"table" => "xp_FRMPRecordTypeOut_import"
				),
				'regime.xml' => array(
					"name" => 'Regime',
					"table" => "xp_FRMPRegime_import"
				),
				'sertificatespeciality.xml' => array(
					"name" => 'SertificateSpeciality',
					"table" => "xp_FRMPSertificateSpeciality_import"
				),
				'skippaymentreason.xml' => array(
					"name" => 'SkipPaymentReason',
					"table" => "xp_FRMPSkipPaymentReason_import"
				),
				'specialities.xml' => array(
					"name" => 'Speciality',
					"table" => "xp_FRMPSpeciality_import"
				),
				'subdivision.xml' => array(
					"name" => 'Subdivision',
					"table" => "xp_FRMPSubdivision_import"
				),
				'territories.xml' => array(
					"name" => 'Territory',
					"table" => "xp_FRMPTerritories_import"
				),
			)
		);

		// PersonDead
		$actions['PersonDead'] = array();
		$actions['PersonDead']['RegisterList_id'] = $RegisterList_id;
		$actions['PersonDead']['name'] = 'Список Умерших';
		$actions['PersonDead']['actions'] = array();
		$actions['PersonDead']['actions'][1] = array(
			'title' => 'Загрузка файлов DBase(*.DBF)...',
			'type' => 'importDead',
			'files' => array(
				'dead.DBF' => array(
					'destination' => array(
						'tableName' => 'frlPrivilege',
						'schemaName' => 'stg',
						'fields_mapping' => array(
							'SS' => 'ss',
							'C_KATL' => 'c_katl',
							'NAME_DL' => 'name_dl',
							'SN_DL' => 'sn_dl',
							'DATE_BL' => 'date_bl',
							'DATE_EL' => 'date_el',
						)
					)
				),
			)
		);

		if (isset($actions[$schemaName])) {
			return $actions[$schemaName];
		} else {
			return false;
		}
	}

	/**
	 * @param $schemaName
	 * @param string $inputFolder Каталог с входными файлами. Со слешем на конце.
	 * @return array
	 * @throws Exception
	 */
	public function run($schemaName, $inputFolder = '/', $RegisterList_id, $files = null, $ignoredelete = false, $fileName = false) {
		set_time_limit(100000);
		ini_set("memory_limit", "2048M");
		ini_set("max_execution_time", "0");
		ini_set("max_input_time", "0");
		ini_set("post_max_size", "500");
		ini_set("default_socket_timeout", "999");
		ini_set("upload_max_filesize", "500M");

		$this->db->save_queries = false;
		$this->db->query_timeout = 12000;
		$refsY = (date('m-d') > '10-01' && date('m-d') < '12-31') ? (date('Y') + 1) : (date('Y'));
		$this->YesrRef = (int) $refsY;
		$currenYear = (int)(date('Y'));
		$nextYear = (int)(date('Y') + 1);
		$this->privilegeClose = 0;

		// Получаем кол-во льготополучателей и льгот на текущий момент (до обновления)
		$recordCount = $this->db->query("
			with mv as (
				select
					dbo.tzgetdate() as dt
			)
			select 
				(select COUNT(*) from(select distinct person_id
					from PersonPrivilege pp
					inner join v_PrivilegeType pt on pt.PrivilegeType_id = pp.PrivilegeType_id
					where 
					(pp.PersonPrivilege_endDate>(select dt from mv)
					or pp.PersonPrivilege_endDate is null) 
					and (pp.PersonPrivilege_begDate<(select dt from mv)
					or pp.PersonPrivilege_begDate is null ) 
					and pt.ReceptFinance_id= 1) as d
				) as \"person\",

				(select count(*) from (select distinct pr.person_id from v_PersonRefuse pr
					inner join PersonPrivilege pp on pr.Person_id = pp.Person_id
					inner join v_PrivilegeType pt on pp.PrivilegeType_id = pt.PrivilegeType_id 
					where pr.PersonRefuse_Year =".$currenYear."
					and pt.ReceptFinance_id= 1)dis
				) as \"disCurrent\",
				
				(select count(*) from (select distinct pr.person_id from v_PersonRefuse pr
					inner join PersonPrivilege pp on pr.Person_id = pp.Person_id
					inner join v_PrivilegeType pt on pp.PrivilegeType_id = pt.PrivilegeType_id 
					where pr.PersonRefuse_Year =".$nextYear." 
					and pt.ReceptFinance_id= 1)dis
				) as \"disNext\",
				
				(select COUNT(*) from(select distinct person_id
					from PersonPrivilege pp
					inner join v_PrivilegeType pt on pt.PrivilegeType_id = pp.PrivilegeType_id
					where
					(pp.PersonPrivilege_endDate>(select dt from mv)
					or pp.PersonPrivilege_endDate is null) 
					and (pp.PersonPrivilege_begDate<(select dt from mv)
					or pp.PersonPrivilege_begDate is null ) 
					and pt.ReceptFinance_id= 1
					and not exists(select distinct pr.person_id from v_PersonRefuse pr where pr.Person_id = pp.Person_id)) 
					as d
				) as \"actual\",
				
				(select COUNT(*)
					from PersonPrivilege PP
					inner join v_PrivilegeType PT on PT.PrivilegeType_id = PP.PrivilegeType_id 
					where 
					PT.ReceptFinance_id = 1 
				) as \"allprivilege\",
				
				(select count(*)	
					from PersonPrivilege PP
					inner join v_PrivilegeType PT on PT.PrivilegeType_id = PP.PrivilegeType_id 
					where 
					PT.ReceptFinance_id = 1 
					and PP.PersonPrivilege_begDate is not null 
					and PP.PersonPrivilege_begDate <= (select dt from mv)
					and (PP.PersonPrivilege_endDate is null 
					or PP.PersonPrivilege_endDate > (select dt from mv))
					and not exists(select distinct pr.person_id from v_PersonRefuse pr where pr.Person_id = pp.Person_id and pr.PersonRefuse_Year =".$this->YesrRef.") 
				) as \"privilege\",
				to_char((select dt from mv), 'yyyy-mm-dd') as \"begtime\"
		")->result('array');

		RegisterListDetailLog_model::createLogMessage(new DateTime(), 1, 'Начало выполнения - ' . $recordCount[0]["person"], $this->RegisterListLog_model->getRegisterListLog_id(), $this->getPmUserId());

		$importSchema = $this->load($schemaName, $RegisterList_id);
		$this->RegisterListLog_model->setpmUser_id($this->getPmUserId());
		$this->RegisterListLog_model->setRegisterList_id($importSchema['RegisterList_id']);
		$this->RegisterListLog_model->setRegisterListLog_begDT(new DateTime());
		$this->RegisterListLog_model->setRegisterListRunType_id(8); //8-вручную
		$this->RegisterListLog_model->setRegisterListResultType_id(2); //2-Выполняется
		if(!empty($fileName)) $this->RegisterListLog_model->setRegisterListLog_NameFile($fileName);
		$this->RegisterListLog_model->save();
		$RegisterListLog_id = $this->RegisterListLog_model->getRegisterListLog_id();

		//посылаем ответ клиенту...
		if (function_exists('fastcgi_finish_request')) {
			echo json_encode(array("success" => "true"));
			if (session_id()) session_write_close();
			fastcgi_finish_request();
		}
		else {
			ignore_user_abort(true);
			echo json_encode(array("success" => "true"));

			$size = ob_get_length();

			header("Content-Length: $size");
			header("Content-Encoding: none");
			header("Connection: close");

			ob_end_flush();
			ob_flush();
			flush();

			if (session_id()) session_write_close();
		}

		//... и продолжаем выполнять скрипт на стороне сервера.
		if ($importSchema['name'] == 'Федеральный регистр льготников') {
			$begtime = $recordCount[0]["begtime"];
			$addPriv = $recordCount[0]["privilege"];

			RegisterListDetailLog_model::createLogMessage(new DateTime(), 1, "ФРЛ до обновления, Персоны: всего - {$recordCount[0]["person"]} чел., в т.ч. отказников - в {$currenYear} г. {$recordCount[0]["disCurrent"]} чел., в {$nextYear} г. {$recordCount[0]["disNext"]} чел., в актуальном регистре - {$recordCount[0]["actual"]} чел. ", $this->RegisterListLog_model->getRegisterListLog_id(), $this->getPmUserId());
			RegisterListDetailLog_model::createLogMessage(new DateTime(), 1, "ФРЛ до обновления, Льготы: всего - {$recordCount[0]["allprivilege"]} шт., в т.ч. действующих - {$recordCount[0]["privilege"]} шт.", $this->RegisterListLog_model->getRegisterListLog_id(), $this->getPmUserId());
		}

		try {
			set_error_handler(array($this, 'exceptionErrorHandler'));

			foreach ($importSchema['actions'] as $action) {


				switch ($action['type']) {
					case 'importFRMP':
						$this->importFRMP($action, $inputFolder);
						break;
					case 'importXml':
						$this->importXml($action, $files[0]);
						break;
					case 'importDbf':
						$this->importDbf($RegisterListLog_id, $action, $inputFolder, $files, $ignoredelete);

						break;
					case 'importDead':
						$this->importDead($action, $inputFolder);
						break;
					case 'queries':
						$this->doQueries($action);
						break;
					default:
						throw new Exception('not implemented');
				}
			}

			$this->RegisterListLog_model->setRegisterListLog_endDT(new Datetime());

			if ($importSchema['name'] == 'Федеральный регистр льготников') {
				$recordCount = $this->db->query("
					with mv as (
						select
							dbo.tzgetdate() as dt
					)

					select 
						(select COUNT(*) from(select distinct person_id
							from PersonPrivilege pp
							inner join v_PrivilegeType pt on pt.PrivilegeType_id = pp.PrivilegeType_id
							where 
							(pp.PersonPrivilege_endDate>(select dt from mv)
							or pp.PersonPrivilege_endDate is null) 
							and (pp.PersonPrivilege_begDate<(select dt from mv)
							or pp.PersonPrivilege_begDate is null ) 
							and pt.ReceptFinance_id= 1) as d
						) as \"person\",
						
						(select count(*) from (select distinct pr.person_id from v_PersonRefuse pr
							inner join PersonPrivilege pp on pr.Person_id = pp.Person_id
							inner join v_PrivilegeType pt on pp.PrivilegeType_id = pt.PrivilegeType_id 
							where pr.PersonRefuse_Year =".$currenYear." 
							and pt.ReceptFinance_id= 1)dis
						) as \"disCurrent\",
						
						(select count(*) from (select distinct pr.person_id from v_PersonRefuse pr
							inner join PersonPrivilege pp on pr.Person_id = pp.Person_id
							inner join v_PrivilegeType pt on pp.PrivilegeType_id = pt.PrivilegeType_id 
							where pr.PersonRefuse_Year =".$nextYear."
							and pt.ReceptFinance_id= 1)dis
						) as \"disNext\",
						
						(select COUNT(*) from(select distinct person_id
							from PersonPrivilege pp
							inner join v_PrivilegeType pt on pt.PrivilegeType_id = pp.PrivilegeType_id
							where
							(pp.PersonPrivilege_endDate>(select dt from mv)
							or pp.PersonPrivilege_endDate is null) 
							and (pp.PersonPrivilege_begDate<(select dt from mv)
							or pp.PersonPrivilege_begDate is null ) 
							and  pt.ReceptFinance_id= 1
							and not exists(select distinct pr.person_id from v_PersonRefuse pr where pr.Person_id = pp.Person_id and pr.PersonRefuse_Year =".$this->YesrRef.")) 
							as d
						) as \"actual\",
						
						(select COUNT(*)
							from PersonPrivilege PP
							inner join v_PrivilegeType PT on PT.PrivilegeType_id = PP.PrivilegeType_id 
							where 
							PT.ReceptFinance_id = 1
						) as \"allprivilege\",
						
						(select count(*)	
							from PersonPrivilege PP
							inner join v_PrivilegeType PT on PT.PrivilegeType_id = PP.PrivilegeType_id 
							left join v_PersonState_all PS on PS.Person_id=PP.Person_id
							where 
							PT.ReceptFinance_id = 1 
							and PP.PersonPrivilege_begDate is not null 
							and PP.PersonPrivilege_begDate <= (select dt from mv)
							and coalesce(PP.PersonPrivilege_endDate,'2080-01-01')> (select dt from mv)
							and coalesce(PS.Person_IsRefuse2, 1) != 2
						) as \"privilege\",
						to_char((select dt from mv), 'yyyy-mm-dd') as \"endtime\"
				")->result('array');
				$endtime = $recordCount[0]['endtime'];
				$resx = $this->db->query("
					with mv as (
						select
							dbo.tzgetdate() as dt
					)

					select
					(select COUNT(*) from stg.frlPrivilege) as \"allpriv\",
					(select COUNT(*) from stg.frlPrivilege s
							left join v_PersonState_all PS on s.Person_id=PS.Person_id	
						where
							s.Person_id is not null
						and s.Privilege_begDate is not null 
							and s.Privilege_begDate <= (select dt from mv)
							and coalesce(s.Privilege_endDate,'2080-01-01')> (select dt from mv)
							and coalesce(PS.Person_IsRefuse2, 1) != 2
					) as \"actualpriv\",
					(select COUNT(*) from stg.frlPrivilege s
						where s.Person_id is null
						or s.Privilege_begDate is null 
							or s.Privilege_begDate > (select dt from mv) 
							or s.Privilege_endDate < (select dt from mv)
					) as \"clospriv\",
					(select count(*) 
						from stg.frlPerson
						where identCount =1 
						and frlPerson_isRefuse =2
					) as \"disew\",
					(select count(*) 
						from stg.frlPerson fp
						inner join PersonRefuse pr on pr.Person_id = fp.person_id
						where identCount =1 
						and frlPerson_isRefuse =2
						and pr.PersonRefuse_insDT  between :begtime and :endtime
					) as \"disex\"
"
					,array('begtime'=>$begtime,'endtime'=>$endtime))->result('array');

				RegisterListDetailLog_model::createLogMessage(new DateTime(), 1, "Импорт ФРЛ Льготы: всего - {$resx[0]['allpriv']} шт., в т.ч. закрытых - {$resx[0]['clospriv']} шт., действующих - {$resx[0]['actualpriv']} шт.", $this->RegisterListLog_model->getRegisterListLog_id(), $this->getPmUserId());
				RegisterListDetailLog_model::createLogMessage(new DateTime(), 1, "Импорт ФРЛ Отказы: всего - " . $resx[0]["disew"] . "; добавлено - " . $resx[0]["disex"], $this->RegisterListLog_model->getRegisterListLog_id(), $this->getPmUserId());

				RegisterListDetailLog_model::createLogMessage(new DateTime(), 1, "ФРЛ после обновления, Персоны: всего - {$recordCount[0]["person"]} чел., в т.ч. отказников - в {$currenYear} г. {$recordCount[0]["disCurrent"]} чел., в {$nextYear} г. {$recordCount[0]["disNext"]} чел., в актуальном регистре - {$recordCount[0]["actual"]} чел. ", $this->RegisterListLog_model->getRegisterListLog_id(), $this->getPmUserId());
				RegisterListDetailLog_model::createLogMessage(new DateTime(), 1, "ФРЛ после обновления, Льготы: всего - {$recordCount[0]["allprivilege"]} шт., в т.ч. действующих - {$recordCount[0]["privilege"]} шт.", $this->RegisterListLog_model->getRegisterListLog_id(), $this->getPmUserId());
			}


			if ($this->Err == 0) {
				$this->RegisterListLog_model->setRegisterListResultType_id(1); //1-успешно
			} else {
				$this->RegisterListLog_model->setRegisterListResultType_id(3);
			}


			restore_error_handler();

		}
		catch (Exception $e) {
			restore_error_handler();
			$this->RegisterListDetailLog_model->setpmUser_id($this->getPmUserId());
			$this->RegisterListDetailLog_model->setRegisterListLog_id($this->RegisterListLog_model->getRegisterListLog_id());
			$this->RegisterListDetailLog_model->setRegisterListLogType_id(2);
			$this->RegisterListDetailLog_model->setRegisterListDetailLog_Message($e->getMessage() . '. Техническая информация: ' . $e->getTraceAsString());
			$this->RegisterListDetailLog_model->setRegisterListDetailLog_setDT(new DateTime());
			$this->RegisterListDetailLog_model->save();
			$this->RegisterListLog_model->setRegisterListLog_endDT(new Datetime());
			$this->RegisterListLog_model->setRegisterListResultType_id(3); //3-завершено с ошибкой
		}


		$this->RegisterListLog_model->save();

		return array('success' => 'true');
	}


	/**
	 * @param $RegisterList_id
	 * @param null $words
	 * @param bool $ignoredelete
	 * @return array
	 */
	public function runMS1($RegisterList_id, $words = null, $ignoredelete = false, $fileName = false) {
		$that = $this;

		set_time_limit(100000);
		ini_set("memory_limit", "2048M");
		ini_set("max_execution_time", "0");
		ini_set("max_input_time", "0");
		ini_set("post_max_size", "500");
		ini_set("default_socket_timeout", "999");
		ini_set("upload_max_filesize", "500M");

		$this->db->save_queries = false;
		$this->db->query_timeout = 12000;
		$refsY = (date('m-d') > '10-01' && date('m-d') < '12-31') ? (date('Y') + 1) : (date('Y'));
		$this->YesrRef = (int) $refsY;
		$currenYear = (int)(date('Y'));
		$nextYear = (int)(date('Y') + 1);
		$this->privilegeClose = 0;

		// Получаем кол-во льготополучателей и льгот на текущий момент (до обновления)
		$recordCount = $this->db->query("
			with mv as (
				select
					dbo.tzgetdate() as dt
			)

			select 
				(select COUNT(*) from(select distinct person_id
					from PersonPrivilege pp
					inner join v_PrivilegeType pt on pt.PrivilegeType_id = pp.PrivilegeType_id
					where 
					(pp.PersonPrivilege_endDate>(select dt from mv)
					or pp.PersonPrivilege_endDate is null) 
					and (pp.PersonPrivilege_begDate<(select dt from mv)
					or pp.PersonPrivilege_begDate is null ) 
					and pt.ReceptFinance_id= 1) as d ) as person,		
					(select count(*) from (select distinct pr.person_id from v_PersonRefuse pr
					inner join PersonPrivilege pp on pr.Person_id = pp.Person_id
					inner join v_PrivilegeType pt on pp.PrivilegeType_id = pt.PrivilegeType_id 
					where pr.PersonRefuse_Year =".$currenYear."
					and pt.ReceptFinance_id= 1)dis
				) as \"disCurrent\",
				
				(select count(*) from (select distinct pr.person_id from v_PersonRefuse pr
					inner join PersonPrivilege pp on pr.Person_id = pp.Person_id
					inner join v_PrivilegeType pt on pp.PrivilegeType_id = pt.PrivilegeType_id 
					where pr.PersonRefuse_Year =".$nextYear." 
					and pt.ReceptFinance_id= 1)dis
				) as \"disNext\",
				
				(select COUNT(*) from(select distinct person_id
					from PersonPrivilege pp
					inner join v_PrivilegeType pt on pt.PrivilegeType_id = pp.PrivilegeType_id
					where
					(pp.PersonPrivilege_endDate>(select dt from mv)
					or pp.PersonPrivilege_endDate is null) 
					and (pp.PersonPrivilege_begDate<(select dt from mv)
					or pp.PersonPrivilege_begDate is null ) 
					and pt.ReceptFinance_id= 1
					and not exists(select distinct pr.person_id from v_PersonRefuse pr where pr.Person_id = pp.Person_id)) 
				as d ) as \"actual\",
				
				(select COUNT(*)
					from PersonPrivilege PP
					inner join v_PrivilegeType PT on PT.PrivilegeType_id = PP.PrivilegeType_id 
					where 
					PT.ReceptFinance_id = 1 
				) as \"allprivilege\",
				
				(select count(*)	
					from PersonPrivilege PP
					inner join v_PrivilegeType PT on PT.PrivilegeType_id = PP.PrivilegeType_id 
					where 
					PT.ReceptFinance_id = 1 
					and PP.PersonPrivilege_begDate is not null 
					and PP.PersonPrivilege_begDate <= (select dt from mv)
					and (PP.PersonPrivilege_endDate is null 
					or PP.PersonPrivilege_endDate > (select dt from mv))
					and not exists(select distinct pr.person_id from v_PersonRefuse pr where pr.Person_id = pp.Person_id and pr.PersonRefuse_Year =".$this->YesrRef.") 
				) as \"privilege\",
				to_char((select dt from mv), 'yyyy-mm-dd') as \"begtime\"
		")->result('array');

		RegisterListDetailLog_model::createLogMessage(new DateTime(), 1, 'Начало выполнения - ' . $recordCount[0]["person"], $this->RegisterListLog_model->getRegisterListLog_id(), $this->getPmUserId());

		$this->RegisterListLog_model->setpmUser_id($this->getPmUserId());
		$this->RegisterListLog_model->setRegisterList_id($RegisterList_id);
		$this->RegisterListLog_model->setRegisterListLog_begDT(new DateTime());
		$this->RegisterListLog_model->setRegisterListRunType_id(8); //8-вручную
		$this->RegisterListLog_model->setRegisterListResultType_id(2); //2-Выполняется
		if(!empty($fileName)) $this->RegisterListLog_model->setRegisterListLog_NameFile($fileName); //имя файла-импорта

		$this->RegisterListLog_model->save();
		$RegisterListLog_id = $this->RegisterListLog_model->getRegisterListLog_id();



		//посылаем ответ клиенту...
		if (function_exists('fastcgi_finish_request')) {
			echo json_encode(array("success" => "true"));
			if (session_id()) session_write_close();
			fastcgi_finish_request();
		}
		else {
			ignore_user_abort(true);
			echo json_encode(array("success" => "true"));

			$size = ob_get_length();

			header("Content-Length: $size");
			header("Content-Encoding: none");
			header("Connection: close");

			ob_end_flush();
			ob_flush();
			flush();

			if (session_id()) session_write_close();
		}
		//... и продолжаем выполнять скрипт на стороне сервера.


		$begtime = $recordCount[0]["begtime"];
		$addPriv = $recordCount[0]["privilege"];

		RegisterListDetailLog_model::createLogMessage(new DateTime(), 1, "ФРЛ до обновления, Персоны: всего - {$recordCount[0]["person"]} чел., в т.ч. отказников - в {$currenYear} г. {$recordCount[0]["disCurrent"]} чел., в {$nextYear} г. {$recordCount[0]["disNext"]} чел., в актуальном регистре - {$recordCount[0]["actual"]} чел. ", $this->RegisterListLog_model->getRegisterListLog_id(), $this->getPmUserId());
		RegisterListDetailLog_model::createLogMessage(new DateTime(), 1, "ФРЛ до обновления, Льготы: всего - {$recordCount[0]["allprivilege"]} шт., в т.ч. действующих - {$recordCount[0]["privilege"]} шт.", $this->RegisterListLog_model->getRegisterListLog_id(), $this->getPmUserId());


		try {
			set_error_handler(array($this, 'exceptionErrorHandler'));


			// Сохраняем данные во временные таблицы
			$this->importDbfFromMS1($RegisterListLog_id, $words, $ignoredelete);

			// Подготовка (обработка) данных льготополучателей (таблица stg.frlPerson)
			$this->_process_frlPerson($RegisterListLog_id);

			// Подготовка (обработка) данных о льготах  (таблица stg.frlPrivilege)
			$this->_process_frlPrivilege($RegisterListLog_id);

			// Поиск данных о человеке в системе, если он существует,
			// то устанавливаем Person_id в таблице "frlPerson"
			$this->_identification_frlPerson($RegisterListLog_id);



			// обрабатываем код 01 "Добавление льгополучателя в регистр"
			$this->run_code01($RegisterListLog_id);

			// обрабатываем код 02 "гражданин исключен из регионального сегмента Федерального регистра."
			$this->run_code02($RegisterListLog_id);

			// обрабатываем код 03 "произошли изменения в учетных данных гражданина (запись типа «О» или запись типа «Л»)"
			$this->run_code03($RegisterListLog_id);


			$this->RegisterListLog_model->setRegisterListLog_endDT(new Datetime());
			//$this->RegisterListLog_model->setRegisterListResultType_id(1); // 1-успешно
			//return array('success' => 'true');

			$queries = array(
				'title' => 'Выполнение загрузки...',
				'type' => 'queries',
				'queries' => array(

					// Идентификация после добавления
					array(
						'title' => 'Идентификация после добавления',
						'sql' => "
							with tmpPerson as (
								select
									l.frlPerson_id,
									ps.Person_id
								from
									stg.frlPerson l
									inner join v_PersonState ps 
										on 
											rtrim(REPLACE(ps.Person_SurName,'ё','е')) = rtrim(ltrim(REPLACE(l.fam,'ё','е'))) and 
											rtrim(REPLACE(ps.Person_FirName,'ё','е')) = rtrim(ltrim(REPLACE(l.im,'ё','е'))) and 
											(
												rtrim(REPLACE(ps.Person_SecName,'ё','е')) = rtrim(ltrim(REPLACE(l.ot,'ё','е'))) or 
												(
													nullif(replace(ps.Person_SecName,' ',''),'---') is null and 
													nullif(rtrim(ltrim(l.ot)),'') is null
												)
											) and 
											ps.Person_BirthDay = l.Person_BirthDay and 
											coalesce(ps.Person_Snils,'') = coalesce(l.Person_Snils,'')
								where
									l.Person_id is null and 
									l.identCount <= 1 and 
									l.identVariant = 5 and 
									l.RegisterListLog_id = :RegisterListLog_id
							)
						
							update
								stg.frlPerson
							set
								Person_id = ps.Person_id
							from
								tmpPerson ps
							where
								frlPerson.frlPerson_id = ps.frlPerson_id
								and frlPerson.RegisterListLog_id = :RegisterListLog_id;
						",
						'callback' => function ($th) use ($that) {

							/**
							 * Вроде бы просто сохраняем и выводим те записи, которые не были добавлены (это двойники)
							 * Функция получает список двойников, сохраняет из в файлик и выводит ссылку на скачивание файла с двойниками
							 *
							 * @var $that ImportSchema_model
							 */
							$rslt = $th->db->query('
								select
									(select COUNT(*) from  stg.frlPerson
										where identCount !=0 and Person_id is not null
									) as "ident",
									(select COUNT(*) from stg.frlPerson) as "allpers",
									(select COUNT(*) from stg.frlPerson where
									identCount =0 and Person_id is not null) as "new", 
									(select COUNT(*) from stg.frlPerson 
									where identCount >1 and Person_id is null) as "doubl"
								')->result('array');
							$double = $th->db->query("
								select
									fam as \"fam\",
									im as \"im\",
									ot as \"ot\",
									identCount as \"identCount\",
									Person_SNILS as \"Person_SNILS\",
									to_char(Person_birthday, 'dd.mm.yyyy') as \"birthDay\"
								from stg.frlPerson 
								where identCount >1
							")->result('array');

							$doublePeople = '';
							if (count($double) > 30) {
								$str_arr = array();
								foreach($double as $val) {
									$str_arr[] = "{$val['fam']} {$val['im']} {$val['ot']} {$val['birthDay']} - {$val['Person_SNILS']}; кол-во: {$val['identCount']}\n";
								}

								$out_dir = EXPORTPATH_ROOT.'frl_import/doubles_'.time();
								mkdir($out_dir, 0777, true);
								$errorFile = $out_dir . '/doublePeople.txt';
								file_put_contents($errorFile, $str_arr);

								$doublePeople = "&nbsp;<a href='{$errorFile}' target='_blank'>скачать<a>";
							} else {
								$doublePeople = "<ul>";
								foreach($double as $val){
									$doublePeople.="<li>{$val['fam']} {$val['im']} {$val['ot']} {$val['birthDay']} - {$val['Person_SNILS']}; кол-во: {$val['identCount']}</li>";
								}
								$doublePeople.="</ul>";
							}
							RegisterListDetailLog_model::createLogMessage(new DateTime(), 1, "Импорт ФРЛ Персоны: Всего - {$rslt[0]['allpers']} чел., идентифицировано - {$rslt[0]['ident']} чел; добавлено - {$rslt[0]['new']} чел.", $th->RegisterListLog_model->getRegisterListLog_id(), $th->getPmUserId());
							RegisterListDetailLog_model::createLogMessage(new DateTime(), 1, "Двойники: ".$doublePeople, $th->RegisterListLog_model->getRegisterListLog_id(), $th->getPmUserId());
						}
					),

					// Признак отказа от льгот
					// exec p_PersonRefuse_ins|p_PersonRefuse_upd from stg.frlPerson
					array(
						'title' => 'Признак отказа от льгот',
						'sql' => "
								do
								$$
								declare
									cur1 REFCURSOR;
									p_pers_id bigint;
									p_ins bigint;
									p_frlPerson_isRefuse bigint;
									p_YearRefuse int = date_part('year', dbo.tzGetDate());
									p_Error_Code varchar(10);
    								p_Error_Message varchar(4000);
    							
    							begin
    								open cur1 for
										select 
											frlPerson_isRefuse,
											Person_id as pers_id 
										from 
											stg.frlPerson 
										where 
											identCount = 1 and 
											frlPerson_isRefuse = 2
								
							
								loop
									fetch next from cur1 into 
										p_frlPerson_isRefuse,
										p_pers_id;
									EXIT WHEN NOT FOUND;
									p_Error_Code := null;
									p_Error_Message := null;
									p_ins := (
										select
											PersonRefuse_id 
										from 
											PersonRefuse pr 
										where 
											pr.Person_id=p_pers_id and 
											pr.PersonRefuse_Year=p_YearRefuse
										limit 1
									);
									p_Error_Code = null;
									p_Error_Message = null;
							
									if( p_ins is null)
										then
											select
												fnc.Error_Code, fnc.Error_Message
											into
												p_Error_Code, p_Error_Message
											from p_PersonRefuse_ins(
												Person_id:=p_pers_id,
												PersonRefuse_Year := p_YearRefuse,
												PersonRefuse_IsRefuse:=p_frlPerson_isRefuse,
												pmUser_id:=1
											) fnc
										else
											select
												fnc.Error_Code, fnc.Error_Message
											into
												p_Error_Code, p_Error_Message
											from p_PersonRefuse_upd(
												PersonRefuse_id:=p_ins,
												Person_id:=p_pers_id,
												PersonRefuse_Year := p_YearRefuse,
												PersonRefuse_IsRefuse:=p_frlPerson_isRefuse,
												pmUser_id:=1
											) fnc
									end if;
									if (p_Error_Code is not null)
										then
											RAISE USING ERRCODE = p_error_code, MESSAGE = p_Error_Message;
										end if;
		
								end loop;
    							close cur1;
								end;
								$$
						"
					),

					// Импорт документа
					// exec p_PersonDocument_ins from stg.frlPerson
					array(
						'title' => 'Импорт документа',
						'sql' => "
								do
								$$
								declare
									cur1 REFCURSOR;
									p_s_doc varchar(10);
									p_curDT datetime = dbo.tzGetDate();
									p_n_doc varchar(30);
									p_c_doc bigint;
									p_dt_doc timestamp;
									p_pers_id bigint;
									p_OrgDep_id bigint;
									p_KLCountry_id bigint;
									p_Error_Code varchar(10);
    								p_Error_Message varchar(4000);
    							
    							begin
    								open cur1 for
										select 
											to_char(fP.dt_doc, 'yyyy-mm-dd') as dt_doc,
											fP.s_doc,
											fP.n_doc,
											Org.OrgDep_id,
											fP.c_doc,
											fP.Person_id as pers_id, 
											coalesce(Country.KLCountry_id,643) as KLCountry_id
										from 
											stg.frlPerson fP 
											left join v_personState_all PS on PS.Person_id=fP.Person_id
											left join v_KLCountry Country on Country.KLCountry_Name ilike fp.frlPerson_Nationality
											left join lateral(
												select
													OrgDep_id 
												from 
													v_OrgDep 
												where 
													Org_Name=fP.o_doc
												limit 1
											) Org on true
										where 
											fP.identCount =1 and 
											PS.Document_id is null and 
											fP.dt_doc is not null and 
											ps.Person_IsBDZ!=1
							
								loop
							
									fetch next from cur1 into 
										p_dt_doc,
										p_s_doc,
										p_n_doc,
										p_OrgDep_id,
										p_c_doc,
										p_pers_id,
										p_KLCountry_id;
									EXIT WHEN NOT FOUND;
									p_Error_Code := null;
									p_Error_Message := null;
							
									
									select
										fnc.Error_Code, fnc.Error_Message
									into
										p_Error_Code, p_Error_Message
									from p_PersonDocument_ins(
										Server_id := 3,
										Person_id := p_pers_id,
										PersonDocument_insDT := p_curDT,
										DocumentType_id := p_c_doc,
										OrgDep_id := p_OrgDep_id,
										Document_Ser := p_s_doc,
										Document_Num := p_n_doc,
										Document_begDate := p_dt_doc,
										KLCountry_id := p_KLCountry_id,
										pmUser_id := 1
									) fnc
									if (p_Error_Code is not null)
									then
										RAISE USING ERRCODE = p_error_code, MESSAGE = p_Error_Message;
									end if;
		
								end loop;
    							close cur1;
								end;
								$$
						"
					),

					// Признак отказа от льгот на следующий год
					// exec p_PersonRefuse_ins|p_PersonRefuse_upd from stg.frlPerson
					array(
						'title' => 'Признак отказа от льгот на следующий год',
						'sql' => "
								do
								$$
								declare
									cur1 REFCURSOR;
									p_pers_id bigint;
									p_frlPerson_isRefuseNext bigint;
									p_YearRefuse int = date_part('year', dbo.tzGetDate())+1;
    								p_Error_Code varchar(10);
    								p_Error_Message varchar(4000);
    								p_ins bigint;
    							
    							begin
    								open cur1 for
										select 
											frlPerson_isRefuseNext,
											Person_id as pers_id 
										from 
											stg.frlPerson
										where 
											identCount = 1 and 
											frlPerson_isRefuseNext = 2
													
								loop
									fetch next from cur1 into 
										p_frlPerson_isRefuseNext, 
										p_pers_id;
									EXIT WHEN NOT FOUND;
									p_Error_Code := null;
									p_Error_Message := null;
									p_ins := (
										select
											PersonRefuse_id 
										from 
											PersonRefuse pr 
										where 
											pr.Person_id=p_pers_id and 
											pr.PersonRefuse_Year=p_YearRefuse
										limit 1
									);
									p_Error_Code := null;
									p_Error_Message := null;
							
									if( p_ins is null)
										then
											select
												fnc.Error_Code, fnc.Error_Message
											into
												p_Error_Code, p_Error_Message
											from p_PersonRefuse_ins(
												Person_id:=p_pers_id,
												PersonRefuse_Year := p_YearRefuse,
												PersonRefuse_IsRefuse:=p_frlPerson_isRefuseNext,
												pmUser_id:=1
											) fnc
										else
											select
												fnc.Error_Code, fnc.Error_Message
											into
												p_Error_Code, p_Error_Message
											from p_PersonRefuse_upd(
												PersonRefuse_id:=p_ins,
												Person_id:=p_pers_id,
												PersonRefuse_Year := p_YearRefuse,
												PersonRefuse_IsRefuse:=p_frlPerson_isRefuseNext,
												pmUser_id:=1
											) fnc
									end if;
									if (p_Error_Code is not null)
										then
											RAISE USING ERRCODE = p_error_code, MESSAGE = p_Error_Message;
										end if;
		
								end loop;
    							close cur1;
								end;
								$$
						"
					),

					// Форматирование СНИЛС и кода льготы
					array(
						'title' => 'Форматирование СНИЛС и кода льготы',
						'sql' => "
							update stg.frlPrivilege set Person_SNILS=REPLACE(REPLACE(ss,'-',''),' ','') where RegisterListLog_id = :RegisterListLog_id;
							update stg.frlPrivilege set PrivilegeType_Code=CAST(c_katl as int) where RegisterListLog_id = :RegisterListLog_id;
						"
					),

					// Форматирование кода льготы
					array(
						'title' => 'Форматирование кода льготы',
						'sql' => "
							update stg.frlPrivilege set PrivilegeType_Code = cast(C_KATL as int) where RegisterListLog_id = :RegisterListLog_id
						"
					),

					// Копирование результатов идентификации в stg.frlPrivilege
					array(
						'title' => 'Копирование результатов идентификации в stg.frlPrivilege',
						'sql' => "
							update 
								stg.frlPrivilege 
							set
								Person_id = frlpers.Person_id
							from 
								stg.frlPrivilege frlpriv 
								inner join 
									stg.frlPerson frlpers 
									on 
										frlpers.frlPerson_id = frlpriv.frlPerson_id and 
										frlpers.RegisterListLog_id = :RegisterListLog_id 
							where 
								frlpriv.RegisterListLog_id = :RegisterListLog_id"
					),

					// Перекодировка льгот
					array(
						'title' => 'Перекодировка льгот',
						'sql' => "
							update 
								stg.frlPrivilege 
							set
								PersonPrivilege_id = pp.PersonPrivilege_id
							from 
								stg.frlPrivilege imp
								left join (
									select 
										personprivilege.PersonPrivilege_id,
										personprivilege.person_id,
										personprivilege.Server_id,
										PrivilegeType.ReceptFinance_id,
										PrivilegeType.PrivilegeType_Code,
										personprivilege.PersonPrivilege_begDate,
										personprivilege.PersonPrivilege_endDate
									from 
										v_personprivilege personprivilege
										inner join v_PrivilegeType PrivilegeType on PrivilegeType.PrivilegeType_id = personprivilege.PrivilegeType_id
								) pp
								on imp.person_id = pp.person_id and 
								pp.ReceptFinance_id = 1 and 
								pp.PrivilegeType_Code = cast(imp.PrivilegeType_code as varchar) and 
								(
									pp.PersonPrivilege_begDate between imp.Privilege_begDate and 
									coalesce(imp.Privilege_endDate,'3000-01-01') or 
									
									pp.PersonPrivilege_endDate between imp.Privilege_begDate and 
									coalesce(imp.Privilege_endDate,'3000-01-01') or 
									
									imp.Privilege_begDate between pp.PersonPrivilege_begDate and 
									coalesce(pp.PersonPrivilege_endDate,'3000-01-01') or 
									
									imp.Privilege_endDate between pp.PersonPrivilege_begDate and 
									coalesce(pp.PersonPrivilege_endDate,'3000-01-01')
								)
						"
					),

					// Копирование результатов идентификации в stg.frlPrivilege (2)
					array(
						'title' => 'Копирование результатов идентификации в stg.frlPrivilege (2)', //todo уточнить у Врубель, опять выполняется дважды
						'sql' => "
							update 
								stg.frlPrivilege 
							set
								Person_id = frlpers.Person_id
							from 
								stg.frlPrivilege frlpriv 
								inner join 
									stg.frlPerson frlpers 
								on 
									frlpers.frlPerson_id = frlpriv.frlPerson_id and 
									frlpers.RegisterListLog_id = :RegisterListLog_id 
							where 
								frlpriv.RegisterListLog_id = :RegisterListLog_id
						"
					),

					// Добавление периодик
					// exec p_PersonSnils_ins|p_PersonSurName_ins|p_PersonFirName_ins... from stg.frlPerson
					array(
						'title'=>'Добавление периодик',
						'except_region' => array('perm','kz'),
						'sql'=> "
								do
								$$
								declare
									cur1 REFCURSOR;
									p_Person_id bigint;
									p_Person_SNILS bigint;
									p_fam varchar(11);
									p_im varchar(11);
									p_ot varchar(11);
									p_Person_BirthDay date;
									p_Error_Code varchar(10);
    								p_Error_Message varchar(4000);
    								
    							begin
    								open cur1 for
										select distinct 
											fp.Person_id,
											case 
												when coalesce(fp.Person_SNILS,'') <> coalesce(ps.Person_Snils,'') 
												then coalesce(fp.Person_SNILS,'') else null
											end as Person_SNILS,
									
											case 
												when coalesce(fp.fam,'') <> coalesce(ps.Person_SurName,'') 
												then coalesce(fp.fam,'') else null
											end as fam,
									
											case 
												when coalesce(fp.im,'') <> coalesce(ps.Person_FirName,'') 
												then coalesce(fp.im,'') else null
											end as im,
									
											case 
												when coalesce(fp.ot,'') <> coalesce(ps.Person_SecName,'') 
												then coalesce(fp.ot,'') else null
											end as ot,
									
											case 
												when fp.Person_BirthDay <> ps.Person_BirthDay 
												then fp.Person_BirthDay else null
											end as Person_BirthDay
										from 
											stg.frlPerson fp
											inner join v_PersonState ps on ps.Person_id = fp.Person_id
										where 
											cast(fp.u_type as int) = 3 
											and (
												coalesce(fp.Person_SNILS,'') <> coalesce(ps.Person_Snils,'') or 
												coalesce(fp.fam,'') <> coalesce(ps.Person_SurName,'') or 
												coalesce(fp.im,'') <> coalesce(ps.Person_FirName,'') or 
												coalesce(fp.ot,'') <> coalesce(ps.Person_SecName,'') or 
												fp.Person_BirthDay <> ps.Person_BirthDay
											)
									loop
										fetch next from cur1 into
											p_Person_id,
											p_Person_SNILS,
											p_fam,
											p_im,
											p_ot,
											p_Person_BirthDay;
										EXIT WHEN NOT FOUND;
										p_Error_Code := null;
										p_Error_Message := null;

											if (p_Person_SNILS is not null)
											then
												p_Person_SNILS := nullif(p_Person_SNILS,'');
												select
													fnc.Error_Code, fnc.Error_Message
												into
													p_Error_Code, p_Error_Message
												from p_PersonSnils_ins(
													Server_id := 3,
													Person_id := p_Person_id,
													PersonSnils_insDT := dbo.tzGetDate(),
													PersonSnils_Snils := p_Person_SNILS,
													pmUser_id := 1
												) fnc
											end if;

											if (p_fam is not null)
											then
												p_fam := nullif(p_fam,'');
												select
													fnc.Error_Code, fnc.Error_Message
												into
													p_Error_Code, p_Error_Message
												from p_PersonSurName_ins(
													Server_id := 3,
													Person_id := p_Person_id,
													PersonSurName_insDT := dbo.tzGetDate(),
													PersonSurName_SurName := p_fam,
													pmUser_id := 1
												) fnc
											end if;
											
											if (p_im is not null)
											then
												p_im := nullif(p_im,'');
												select
													fnc.Error_Code, fnc.Error_Message
												into
													p_Error_Code, p_Error_Message
												from p_PersonFirName_ins(
													Server_id := 3,
													Person_id := p_Person_id,
													PersonFirName_insDT := dbo.tzGetDate(),
													PersonFirName_FirName := p_im,
													pmUser_id := 1
												) fnc
											end if;
											
											if (p_ot is not null)
											then
												p_ot := nullif(p_ot,'');
												select
													fnc.Error_Code, fnc.Error_Message
												into
													p_Error_Code, p_Error_Message
												from p_PersonSecName_ins(
													Server_id := 3,
													Person_id := p_Person_id,
													PersonSecName_insDT := dbo.tzGetDate(),
													PersonSecName_SecName := p_ot,
													pmUser_id := 1
												) fnc
											end if;
											
											if (p_Person_BirthDay is not null)
											then
												p_Person_BirthDay := nullif(p_Person_BirthDay,'');
												select
													fnc.Error_Code, fnc.Error_Message
												into
													p_Error_Code, p_Error_Message
												from p_PersonBirthDay_ins(
													Server_id := 3,
													Person_id := p_Person_id,
													PersonBirthDay_insDT := dbo.tzGetDate(),
													PersonBirthDay_BirthDay := p_Person_BirthDay,
													pmUser_id := 1
												) fnc
											end if;
											if (p_Error_Code is not null)
										then
											RAISE USING ERRCODE = p_error_code, MESSAGE = p_Error_Message;
										end if;
		
									end loop;
    								close cur1;
								end;
								$$
						"
					),

					// Добавление СНИЛС
					// exec p_PersonSnils_ins from stg.frlPrivilege
					array(
						'title'=>'Добавление СНИЛС',
						'region' => array('ufa','saratov','khak','krym'),
						'sql'=> "
								do
								$$
								declare
									cur1 REFCURSOR;
									p_Person_id bigint;
									p_Person_SNILS bigint;
									p_Error_Code varchar(10);
    								p_Error_Message varchar(4000);
    							
    							begin
    								open cur1 for
    									select distinct
											fp.Person_id,
											fp.Person_SNILS 
										from 
											stg.frlPrivilege fp
											inner join v_PersonState ps on ps.Person_id = fp.Person_id
										where 
											coalesce(ps.person_snils, '') = ''

									loop
										fetch next from cur1 into 
											p_Person_id, 
											p_Person_SNILS;
										EXIT WHEN NOT FOUND;
										p_Error_Code := null;
										p_Error_Message := null;
								
										select
											fnc.Error_Code, fnc.Error_Message
											into
											p_Error_Code, p_Error_Message
											from p_PersonSnils_ins(
												Server_id := 3,
												Person_id := p_Person_id,
												PersonSnils_insDT := dbo.tzGetDate(),
												PersonSnils_Snils := p_Person_SNILS,
												pmUser_id := 1
											) fnc
										if (p_Error_Code is not null)
										then
											RAISE USING ERRCODE = p_error_code, MESSAGE = p_Error_Message;
										end if;
		
									end loop;
    								close cur1;
								end;
								$$
						"
					),

					// Добавление СНИЛС
					// exec p_PersonSnils_ins from stg.frlPrivilege
					array(
						'title'=>'Добавление СНИЛС',	//Добавление периодики для изменения перс.данных
						'except_region' => array('perm'),
						'sql'=> "
								do
								$$
								declare
									cur1 REFCURSOR;
									p_Person_id bigint;
									p_Person_SNILS bigint;
									p_Error_Code varchar(10);
    								p_Error_Message varchar(4000);
    								
    							begin
    								open cur1 for
										select distinct 
											fp.Person_id,
											fp.Person_SNILS 
										from 
											stg.frlPrivilege fp
											inner join v_PersonState ps on ps.Person_id = fp.Person_id
										where 
											coalesce(ps.person_snils,'') <> coalesce(fp.Person_SNILS,'');
																		
										loop
									
											fetch next from cur1 into 
												p_Person_id, 
												p_Person_SNILS;
											EXIT WHEN NOT FOUND;
											p_Error_Code := null;
											p_Error_Message := null;
									
											select
												fnc.Error_Code, fnc.Error_Message
												into
												p_Error_Code, p_Error_Message
												from p_PersonSnils_ins(
													Server_id := 3,
													Person_id := p_Person_id,
													PersonSnils_insDT := dbo.tzGetDate(),
													PersonSnils_Snils := p_Person_SNILS,
													pmUser_id := 1
												) fnc
											if (p_Error_Code is not null)
											then
												RAISE USING ERRCODE = p_error_code, MESSAGE = p_Error_Message;
											end if;
										end loop;
    								close cur1;
								end;
								$$
						"
					),


					// Удаление льгот
					// from v_PersonPrivilege
					// delete DocumentPrivilege
					// delete PersonPrivilege
					// -- удаляет дубликаты. Выполнять надо
					array(
						'title' => 'Удаление льгот',
						'region'=> 'ufa',
						'sql' => "
							with forDelete as (
								select 
									pp.personprivilege_id
								from 
									v_personprivilege pp
									inner join (
										select 
											min(personprivilege_id) as mid,
											person_id,privilegetype_id 
										from 
											personprivilege 
										where 
											privilegetype_id <= 150
										group by 
											person_id,
											privilegetype_id
										having 
											count(*) > 1
									) t 
									on 
										t.mid = pp.PersonPrivilege_id
							)
							
							delete 
								DocumentPrivilege 
							where 
								PersonPrivilege_id in (
									select PersonPrivilege_id from forDelete
								);
							
							delete 
								PersonPrivilege 
							where 
								PersonPrivilege_id in (
									select PersonPrivilege_id from forDelete
								)
						"
					),


					// Удаление повторяющихся льгот человека
					// from stg.frlPrivilege
					// delete stg.frlPrivilege
					array(
						'title' => 'Удаление повторяющихся льгот человека',
						'sql' => "
							with doubleList as (
								select 
									priv1.frlPrivilege_id,
									priv1.Person_id
								from 
									stg.frlPrivilege priv1
								where 
									priv1.RegisterListLog_id = :RegisterListLog_id	
									and exists(
										select 
											*
										from 
											stg.frlPrivilege priv2
										where 
											priv2.RegisterListLog_id = priv1.RegisterListLog_id and 
											priv2.frlPrivilege_id <> priv1.frlPrivilege_id and 
											priv2.Person_id = priv1.Person_id and 
											priv2.PrivilegeType_Code = priv1.PrivilegeType_Code and 
											coalesce(priv2.Privilege_begDate, '3000-01-01') = coalesce(priv1.Privilege_begDate, '3000-01-01') and 
											coalesce(priv2.Privilege_endDate, '3000-01-01') = coalesce(priv1.Privilege_endDate, '3000-01-01')
									)
							),
							privList as (
								select 
									frlPrivilege_id
								from 
									stg.frlPrivilege t
								where 
									t.frlPrivilege_id = (
										select
											frlPrivilege_id 
										from 
											doubleList 
										where 
											Person_id = t.Person_id
										limit 1
									)
							)
							
							delete 
								stg.frlPrivilege 
							where 
								frlPrivilege_id in (
									select 
										frlPrivilege_id 
									from 
										privList
								)
						"
					),


					// Импорт льгот
					// EXEC stg.xp_ImportPersonPrivilege
					array(
						'title' => 'Импорт льгот',
						'sql' => "
							select
								Error_Code as \"Error_Code\",
								Error_Message as \"Error_Msg\"
							from stg.xp_ImportPersonPrivilege(
								RegisterListLog_id := :RegisterListLog_id,
								pmUser_id := 1
							)				    
						",
						"qa" => true
					),


					// Перекодировка льгот
					array(
						'title' => 'Перекодировка льгот',
						'sql' => "
							update 
								stg.frlPrivilege 
							set
								PersonPrivilege_id = pp.PersonPrivilege_id
							from 
								stg.frlPrivilege imp
								left join (
									select 
										personprivilege.PersonPrivilege_id,
										personprivilege.person_id,
										personprivilege.Server_id,
										PrivilegeType.ReceptFinance_id,
										PrivilegeType.PrivilegeType_Code,
										personprivilege.PersonPrivilege_begDate,
										personprivilege.PersonPrivilege_endDate
									from 
										personprivilege
										inner join v_PrivilegeType PrivilegeType on PrivilegeType.PrivilegeType_id = personprivilege.PrivilegeType_id
								) pp on 
									imp.person_id = pp.person_id and 
									pp.ReceptFinance_id = 1 and 
									pp.PrivilegeType_Code = cast(imp.PrivilegeType_code as varchar) and 
									(
										pp.PersonPrivilege_begDate between imp.Privilege_begDate and coalesce(imp.Privilege_endDate,'3000-01-01') or 
										pp.PersonPrivilege_endDate between imp.Privilege_begDate and coalesce(imp.Privilege_endDate,'3000-01-01') or 
										imp.Privilege_begDate between pp.PersonPrivilege_begDate and coalesce(pp.PersonPrivilege_endDate,'3000-01-01') or 
										imp.Privilege_endDate between pp.PersonPrivilege_begDate and coalesce(pp.PersonPrivilege_endDate,'3000-01-01')
									)
	"
					),


					// Добавление уд-ия в справочник
					// exec p_DocumentPrivilegeType_ins from stg.frlPrivilege
					array(
						'title' => 'Добавление уд-ия в справочник',
						'sql' => "
								do 
								$$
								declare
									cur1 REFCURSOR;
									p_cnt bigint;
									p_ins bigint;
									p_name_dl varchar(4000);
									p_Error_Code varchar(10);
									p_Error_Message varchar(4000);
									
								begin
									open cur1 for
										select distinct
											name_dl 
										from 
											stg.frlPrivilege 
										where 
											Person_id is not null and 
											s_dl is not null and 
											n_dl is not null
											
								open cur1
								loop
									fetch  cur1 into p_name_dl;
									EXIT WHEN NOT FOUND;
									p_Error_Code := null;
									p_Error_Message := null;
									
									select
										fnc.Error_Code,fnc.Error_Message
									into p_Error_Code,p_Error_Message
									from p_DocumentPrivilegeType_ins(
										DocumentPrivilegeType_Code := (select COUNT(*)+1 from DocumentPrivilegeType),
										DocumentPrivilegeType_Name := (select COUNT(*) from DocumentPrivilegeType dpt where dpt.DocumentPrivilegeType_Name=p_name_dl),
										pmUser_id := 1
									) fnc;
									if (p_Error_Code is not null)
									then
										RAISE USING ERRCODE = p_error_code, MESSAGE = p_Error_Message;
									end if;
								end loop;
									close cur1;
								
								end;
								$$
						"
					),

					// Добавление удостоверения льготника
					// exec p_DocumentPrivilege_ins from stg.frlPrivilege
					array(
						'title' => 'Добавление уд-ия льготника',
						'sql' => "
								do 
								$$
								declare
									cur1 REFCURSOR;
									p_DocumentPrivilegeType_id bigint;
									p_PersonPrivilege_id bigint;
									p_s_dl varchar(10);
									p_n_dl varchar(30);
									p_sn_dl timestamp;
									p_Privilege_begDate timestamp;
									p_frlPrivilege_Org varchar(300);
									p_Error_Code varchar(10);
									p_Error_Message varchar(4000);
									
								begin
									open cur1 for
										select 
											dpt.DocumentPrivilegeType_id as DocumentPrivilegeType_id,
											fp.PersonPrivilege_id as PersonPrivilege_id,
											fp.s_dl as s_dl,
											fp.n_dl as n_dl,
											to_char(fp.sn_dl, 'yyyy-mm-dd') as sn_dl,
											fp.Privilege_begDate as Privilege_begDate,
											fp.frlPrivilege_Org as frlPrivilege_Org
										from 
											stg.frlPrivilege fp
											inner join DocumentPrivilegeType dpt on fp.name_dl=dpt.DocumentPrivilegeType_Name
										where fp.PersonPrivilege_id is not null 
										and fp.s_dl is not null 
										and	fp.n_dl is not null 
										and not exists(
												select 
													1 
												from 
													DocumentPrivilege DP 
												where DP.DocumentPrivilege_Num = fp.n_dl 
												and DocumentPrivilege_Ser = fp.s_dl 
												and	DocumentPrivilege_begDate = to_char(fp.sn_dl, 'yyyy-mm-dd') 
												and DocumentPrivilegeType_id = dpt.DocumentPrivilegeType_id	);
										loop
											fetch  cur1 into p_DocumentPrivilegeType_id,p_PersonPrivilege_id,p_s_dl,p_n_dl,p_sn_dl,p_Privilege_begDate,p_frlPrivilege_Org;
											EXIT WHEN NOT FOUND;
											p_Error_Code := null;
											p_Error_Message := null;
											
											select fnc.Error_Code,fnc.Error_Message into p_Error_Code,p_Error_Message from p_DocumentPrivilege_ins(DocumentPrivilegeType_id:= p_DocumentPrivilegeType_id,PersonPrivilege_id := p_PersonPrivilege_id,DocumentPrivilege_Ser := p_s_dl,
												DocumentPrivilege_Num := p_n_dl,DocumentPrivilege_begDate := p_sn_dl,DocumentPrivilege_Org := p_frlPrivilege_Org,pmUser_id:=1) fnc;
											if (p_Error_Code is not null)
											then
												RAISE USING ERRCODE = p_error_code, MESSAGE = p_Error_Message;
											end if;		
										end loop;
									close cur1;
								end;
								$$
						"
					),


					// Обновление льгот
					// update from PersonPrivilege
					// from stg.frlPrivilege
					array(
						'title' => 'Обновление льгот',
						'sql' => "
							with mv as (
								select
									dbo.tzgetdate() as dt
							)
							
							update 
								PersonPrivilege 
							set 
								server_id = 3, 
								personprivilege_enddate = t.enddate,
								PersonPrivilege_updDT = (select dt from mv)
							from 
								PersonPrivilege
								inner join (
									select 
										pg.personPrivilege_id,
										case 
											when l.date_el='9999/99/99' then null
											when l.date_el='' then null
											when l.date_el='2030/01/01' then null
											else to_char(l.date_el, 'yyyy-mm-dd') 
										end as enddate
									from 
										stg.frlPrivilege l
										inner join 
											PersonPrivilege pg 
										on 
											pg.PersonPrivilege_id=l.PersonPrivilege_id and 
											l.personPrivilege_id = pg.PersonPrivilege_id and 
											pg.PersonPrivilege_endDate <> 
											case 
												when l.date_el='9999/99/99' then '2030-01-01'
												when l.date_el='' then '2030-01-01' 
												else to_char(l.date_el, 'yyyy-mm-dd') 
											end
									where 
										l.RegisterListLog_id = :RegisterListLog_id
								) t on t.PersonPrivilege_id = PersonPrivilege.PersonPrivilege_id
						"
					),



					// Удаление льгот (удаляет дубликаты. Выполнять надо)
					// from PersonPrivilege
					// delete DocumentPrivilege
					// delete PersonPrivilege
					array(
						'title' => 'Удаление льгот',
						'region'=>'ufa',
						'sql' => "
							with forDelete as (
								SELECT 
									pp.personprivilege_id
								FROM 
									personprivilege pp
									INNER JOIN (
										SELECT 
											min(personprivilege_id) as mid,
											person_id,
											privilegetype_id 
										FROM 
											personprivilege
										WHERE 
											privilegetype_id <= 150
										GROUP BY 
											person_id, 
											privilegetype_id
										HAVING 
											count(*)>1
									) t ON t.mid = pp.PersonPrivilege_id
							)
							
							delete 
								DocumentPrivilege 
							where 
								PersonPrivilege_id in (select PersonPrivilege_id from forDelete);
							
							delete 
								PersonPrivilege 
							where 
								PersonPrivilege_id in (select PersonPrivilege_id from forDelete)
						"
					),

					// Удаление льгот ('ufa', 'saratov', 'khak')
					// exec dbo.p_PersonPrivilege_DoubleDel
					array(
						'title' => 'Удаление льгот',
						'region' => array('ufa', 'saratov', 'khak'),
						'sql' => "
							select * from dbo.p_PersonPrivilege_DoubleDel()
						"
					),





					// Закрытие устаревших льгот
					// update dbo.PersonPrivilege
					// from PersonPrivilege
					array(
						'title' => 'Закрытие устаревших льгот',
						'region' => array('perm','saratov','khak','krym'),
						'sql' => "
							with mv as (
								select
									dbo.tzgetdate() as dt
							)

							UPDATE 
								dbo.PersonPrivilege
							SET 
								PersonPrivilege_endDate = (select dt from mv),
								pmUser_updID = 1,
								PersonPrivilege_updDT = (select dt from mv)
							where 
								personPrivilege_id in (
									select 
										pp.personPrivilege_id 
									from 
										PersonPrivilege pp
										inner join v_PrivilegeType pt on pt.PrivilegeType_id = pp.PrivilegeType_id
										left join stg.frlPerson fp on pp.Person_id = fp.Person_id and RegisterListLog_id = :RegisterListLog_id
										left join lateral(
											select
												PersonPrivilege_id as PPrivilege_id 
											from 
												stg.frlPrivilege d
											where
												d.PersonPrivilege_id = pp.PersonPrivilege_id
											limit 1
										) fpr on true
									WHERE 
										(
											fp.Person_id is null or 
											(
												fp.Person_id is not null and 
												fpr.PPrivilege_id is null
											)
										) and 
										personPrivilege_id = pp.personprivilege_id and 
										coalesce(PersonPrivilege_endDate,'2030-01-01') >= (select dt from mv) and 
										pt.ReceptFinance_id = 1 and 
										not exists(
											select 
												PersonRefuse_id 
											from 
												PersonRefuse pr 
											where 
												pr.PersonRefuse_Year = date_part('year', (select dt from mv)) and 
												pr.Person_id = pp.Person_id
										)
								)
							;
						"
					),

					// Закрытие устаревших льгот
					array(
						'title' => 'Закрытие устаревших льгот',
						'region' => 'ufa',
						'sql' => "
							with mv as (
								select
									dbo.tzgetdate() as dt
							)

							UPDATE 
								dbo.PersonPrivilege
							SET 
								PersonPrivilege_endDate = (select dt from mv),
								pmUser_updID = 1,
								PersonPrivilege_updDT = (select dt from mv)
							where 
								personPrivilege_id in (
								
								select 
									pp.personPrivilege_id 
								from 
									PersonPrivilege pp
									inner join v_PrivilegeType pt on pt.PrivilegeType_id = pp.PrivilegeType_id
									left join stg.frlPerson fp on pp.Person_id = fp.Person_idand RegisterListLog_id = :RegisterListLog_id
									left join lateral(
										select
											PersonPrivilege_id as PPrivilege_id 
										from 
											stg.frlPrivilege d
										where  
											d.PersonPrivilege_id=pp.PersonPrivilege_id
										limit 1
									) fpr on true
								WHERE
									(
										fp.Person_id is null or 
										(
											fp.Person_id is not null and 
											fpr.PPrivilege_id is null
										)
									) and 
									personPrivilege_id = pp.personprivilege_id and 
									coalesce(PersonPrivilege_endDate,'2030-01-01') >= (select dt from mv) and 
									pt.ReceptFinance_id = 1
							)
						"
					),

					// Закрытие устаревших льгот не федерального значения
					array(
						'title' => 'Закрытие устаревших льгот не федерального значения',
						'region' => 'perm',
						'sql' => "
							with mv as (
								select
									dbo.tzgetdate() as dt
							)
							
							UPDATE 
								dbo.PersonPrivilege
							SET 
								server_id = 3,
								PersonPrivilege_endDate = (select dt from mv),
								pmUser_updID = 1,
								PersonPrivilege_updDT = (select dt from mv)
							where 
								personPrivilege_id in (
									select 
										pp.personPrivilege_id 
									from 
										PersonPrivilege pp
										inner join v_PrivilegeType pt on pt.PrivilegeType_id = pp.PrivilegeType_id
										left join stg.frlPerson fp on pp.Person_id = fp.Person_id
									WHERE 
										fp.Person_id is not null
										AND coalesce(PersonPrivilege_endDate,'2030-01-01') >= (select dt from mv)
										and pt.ReceptFinance_id != 1
								)
						"
					),
					// Закрываются все льготы, которые связаны с региональным регистром льготников
					array(
						'title' => 'Закрываются все льготы, которые связаны с региональным регистром льготников',
						'region' => 'msk',
						'sql' => "
							with mv as (
								select dbo.tzgetdate() as dt
							)
							UPDATE dbo.PersonPrivilege WITH (ROWLOCK)
							SET PersonPrivilege_endDate=(select dt from mv),
								pmUser_updID=1,
								PersonPrivilege_updDT=(select dt from mv) 
							where personPrivilege_id in (
								select 
									pp.personPrivilege_id 
								from PersonPrivilege pp
									INNER JOIN v_PrivilegeType pt on pt.PrivilegeType_id = pp.PrivilegeType_id
									INNER JOIN v_WhsDocumentCostItemType WDCIT ON WDCIT.WhsDocumentCostItemType_id = pt.WhsDocumentCostItemType_id
									LEFT JOIN stg.frlPerson fp on pp.Person_id = fp.Person_id
								WHERE fp.Person_id is not null
									AND WDCIT.WhsDocumentCostItemType_Nick = 'rl'
							);
						"
					)

				)
			);
			/*foreach($queries as $query) {
				$this->doQueries($query);
			}*/
			$this->doQueries($queries);


			$this->RegisterListLog_model->setRegisterListLog_endDT(new Datetime());


			$recordCount = $this->db->query("
				with mv as (
					select
						dbo.tzgetdate() as dt
				)

				select 
					(select COUNT(*) from(select distinct person_id
						from PersonPrivilege pp
						inner join v_PrivilegeType pt on pt.PrivilegeType_id = pp.PrivilegeType_id
						where 
						(pp.PersonPrivilege_endDate>(select dt from mv)
						or pp.PersonPrivilege_endDate is null) 
						and (pp.PersonPrivilege_begDate<(select dt from mv)
						or pp.PersonPrivilege_begDate is null ) 
						and pt.ReceptFinance_id= 1) as d
					) as \"person\",
					
					(select count(*) from (select distinct pr.person_id from v_PersonRefuse pr
						inner join PersonPrivilege pp on pr.Person_id = pp.Person_id
						inner join v_PrivilegeType pt on pp.PrivilegeType_id = pt.PrivilegeType_id 
						where pr.PersonRefuse_Year =".$currenYear." 
						and pt.ReceptFinance_id= 1) dis
					) as \"disCurrent\",
	
					(select count(*) from (select distinct pr.person_id from v_PersonRefuse pr
						inner join PersonPrivilege pp on pr.Person_id = pp.Person_id
						inner join v_PrivilegeType pt on pp.PrivilegeType_id = pt.PrivilegeType_id 
						where pr.PersonRefuse_Year =".$nextYear."
						and pt.ReceptFinance_id= 1)dis
					) as \"disNext\",
	
					(select COUNT(*) from(select distinct person_id
						from PersonPrivilege pp
						inner join v_PrivilegeType pt on pt.PrivilegeType_id = pp.PrivilegeType_id
						where
						(pp.PersonPrivilege_endDate> (select dt from mv)
						or pp.PersonPrivilege_endDate is null) 
						and (pp.PersonPrivilege_begDate< (select dt from mv)
						or pp.PersonPrivilege_begDate is null ) 
						and  pt.ReceptFinance_id= 1
						and not exists(select distinct pr.person_id from v_PersonRefuse pr where pr.Person_id = pp.Person_id and pr.PersonRefuse_Year =".$this->YesrRef.")) 
						as d
					) as \"actual\",
					
					(select COUNT(*)
						from PersonPrivilege PP
						inner join v_PrivilegeType PT on PT.PrivilegeType_id = PP.PrivilegeType_id 
						where 
						PT.ReceptFinance_id = 1
					) as \"allprivilege\",
					
					(select count(*)	
						from PersonPrivilege PP
						inner join v_PrivilegeType PT on PT.PrivilegeType_id = PP.PrivilegeType_id 
						left join v_PersonState_all PS on PS.Person_id=PP.Person_id
						where 
						PT.ReceptFinance_id = 1 
						and PP.PersonPrivilege_begDate is not null 
						and PP.PersonPrivilege_begDate <= (select dt from mv)
						and coalesce(PP.PersonPrivilege_endDate,'2080-01-01')> (select dt from mv)
						and coalesce(PS.Person_IsRefuse2, 1) != 2
					) as \"privilege\",
					to_char((select dt from mv), 'yyyy-mm-dd') as \"endtime\"
			")->result('array');
			$endtime = $recordCount[0]['endtime'];
			$resx = $this->db->query("
				with mv as (
					select
						dbo.tzgetdate() as dt
				)

				select
					(select COUNT(*)
						from stg.frlPrivilege
					) as \"allpriv\",
					(select COUNT(*)
						from stg.frlPrivilege s
							left join v_PersonState_all PS on s.Person_id=PS.Person_id
						where s.Person_id is not null
							and s.Privilege_begDate is not null 
							and s.Privilege_begDate <= (select dt from mv)
							and coalesce(s.Privilege_endDate,'2080-01-01')> (select dt from mv)
							and coalesce(PS.Person_IsRefuse2, 1) != 2
					) as \"actualpriv\",
					(select COUNT(*)
						from stg.frlPrivilege s
						where s.Person_id is null
							or s.Privilege_begDate is null 
							or s.Privilege_begDate > (select dt from mv) 
							or s.Privilege_endDate < (select dt from mv)
					) as \"clospriv\",
					(select count(*) 
						from stg.frlPerson
						where identCount =1 
							and frlPerson_isRefuse =2
					) as \"disew\",
					(select count(*) 
						from stg.frlPerson fp
							inner join PersonRefuse pr on pr.Person_id = fp.person_id
						where identCount =1 
							and frlPerson_isRefuse =2
							and pr.PersonRefuse_insDT  between :begtime and :endtime
					) as \"disex\"
			", array('begtime'=>$begtime,'endtime'=>$endtime))->result('array');

			RegisterListDetailLog_model::createLogMessage(new DateTime(), 1, "Импорт ФРЛ Льготы: всего - {$resx[0]['allpriv']} шт., в т.ч. закрытых - {$resx[0]['clospriv']} шт., действующих - {$resx[0]['actualpriv']} шт.", $this->RegisterListLog_model->getRegisterListLog_id(), $this->getPmUserId());
			RegisterListDetailLog_model::createLogMessage(new DateTime(), 1, "Импорт ФРЛ Отказы: всего - " . $resx[0]["disew"] . "; добавлено - " . $resx[0]["disex"], $this->RegisterListLog_model->getRegisterListLog_id(), $this->getPmUserId());

			RegisterListDetailLog_model::createLogMessage(new DateTime(), 1, "ФРЛ после обновления, Персоны: всего - {$recordCount[0]["person"]} чел., в т.ч. отказников - в {$currenYear} г. {$recordCount[0]["disCurrent"]} чел., в {$nextYear} г. {$recordCount[0]["disNext"]} чел., в актуальном регистре - {$recordCount[0]["actual"]} чел. ", $this->RegisterListLog_model->getRegisterListLog_id(), $this->getPmUserId());
			RegisterListDetailLog_model::createLogMessage(new DateTime(), 1, "ФРЛ после обновления, Льготы: всего - {$recordCount[0]["allprivilege"]} шт., в т.ч. действующих - {$recordCount[0]["privilege"]} шт.", $this->RegisterListLog_model->getRegisterListLog_id(), $this->getPmUserId());


			if ($this->Err == 0) {
				$this->RegisterListLog_model->setRegisterListResultType_id(1); // 1-успешно
			} else {
				$this->RegisterListLog_model->setRegisterListResultType_id(3);
			}


			restore_error_handler();

		}
		catch (Exception $e) {
			restore_error_handler();
			$this->RegisterListDetailLog_model->setpmUser_id($this->getPmUserId());
			$this->RegisterListDetailLog_model->setRegisterListLog_id($this->RegisterListLog_model->getRegisterListLog_id());
			$this->RegisterListDetailLog_model->setRegisterListLogType_id(2);
			$this->RegisterListDetailLog_model->setRegisterListDetailLog_Message($e->getMessage() . '. Техническая информация: ' . $e->getTraceAsString());
			$this->RegisterListDetailLog_model->setRegisterListDetailLog_setDT(new DateTime());
			$this->RegisterListDetailLog_model->save();
			$this->RegisterListLog_model->setRegisterListLog_endDT(new Datetime());
			$this->RegisterListLog_model->setRegisterListResultType_id(3); //3-завершено с ошибкой
		}


		$this->RegisterListLog_model->save();

		return array('success' => 'true');
	}

	/**
	 * Добавление НОВЫХ льгополучателей в регистр
	 * Логика верна (Проверено)
	 *
	 * @param $RegisterListLog_id
	 * @return bool
	 */
	public function run_code01($RegisterListLog_id){

		$frlPerson_ChangeCode = '01';


		$arrCount = $this->db->query('
            SELECT
                count(*) as "cnt"
            FROM
                stg.frlPerson
            WHERE
                frlPerson_ChangeCode = :frlPerson_ChangeCode AND
                RegisterListLog_id = :RegisterListLog_id
            limit 1
        ', array(
			'frlPerson_ChangeCode' => $frlPerson_ChangeCode,
			'RegisterListLog_id' => $RegisterListLog_id,
		))->row_array();

		if($arrCount['cnt'] == 0){
			return true;
		}


		// получаем все записи с кодом 01 и не идентифицированный Person и добавлем его в систему
		$arrNewPersons = $this->db->query("
			select 
				frlPerson_id as \"frlPerson_id\", 
				REPLACE(fam,'ё','е') as \"fam\",
				REPLACE(im,'ё','е') as \"im\",
				REPLACE(ot,'ё','е') as \"ot\",
				person_birthday as \"person_birthday\",
				sex_id as \"sex_id\",
				Person_SNILS as \"Person_SNILS\"
			from 
				stg.frlPerson
			where 
				identCount = 0 AND
				Person_id is null AND 
				frlPerson_ChangeCode = :frlPerson_ChangeCode AND
				RegisterListLog_id = :RegisterListLog_id
		", array(
			'frlPerson_ChangeCode' => $frlPerson_ChangeCode,
			'RegisterListLog_id' => $RegisterListLog_id,
		))->result('array');


		if( ! empty($arrNewPersons)){
			foreach($arrNewPersons as $dataNewPerson){
				$this->_insertPerson(array(
					'Lgot_id' => $dataNewPerson['frlPerson_id'],
					'Server_id' => 3,
					'frlPerson_id' => $dataNewPerson['frlPerson_id'],
					'fam' => $dataNewPerson['fam'],
					'im' => $dataNewPerson['im'],
					'ot' => $dataNewPerson['ot'],
					'person_birthday' => $dataNewPerson['person_birthday'],
					'sex_id' => $dataNewPerson['sex_id'],
					'Person_SNILS' => $dataNewPerson['Person_SNILS'],
				));
			}
		}




		return true;
	}


	/**
	 * Закрытие льготы у льгополучателя
	 * Вроде как в целом норм (Проверено)
	 *
	 * @param $RegisterListLog_id
	 * @return bool
	 * @throws Exception
	 */
	public function run_code02($RegisterListLog_id){

		// получаем все записи с кодом 02 и идентифицированные Person_id, далее находим все льготы и устанавливаем
		// дату закрытия

		// Выписка рецепта
		// EvnRecept

		// дата выписки рецепта
		// EvnRecept_obrDT


		// дате окончания льготы
		// date_el


		// дата закрытия
		// PersonPrivilege_endDate

		$frlPerson_ChangeCode = '02';


		$arrCount = $this->db->query('
            SELECT
                count(*) as "cnt" 
            FROM 
                stg.frlPerson 
            WHERE 
                frlPerson_ChangeCode = :frlPerson_ChangeCode AND
                RegisterListLog_id = :RegisterListLog_id
            limit 1
        ', array(
			'frlPerson_ChangeCode' => $frlPerson_ChangeCode,
			'RegisterListLog_id' => $RegisterListLog_id,
		))->row_array();

		if($arrCount['cnt'] == 0){
			return true;
		}



		$arrPersons = $this->db->query("
			select 
				Person_id as \"Person_id\" 
			from 
				stg.frlPerson
			where 
				Person_id is not null AND 
				frlPerson_ChangeCode = :frlPerson_ChangeCode AND
				RegisterListLog_id = :RegisterListLog_id
		", array(
			'frlPerson_ChangeCode' => $frlPerson_ChangeCode,
			'RegisterListLog_id' => $RegisterListLog_id,
		))->result('array');

		if( ! empty($arrPersons)) {
			foreach ($arrPersons as $dataPerson) {
				$this->doQuery(array(
					'title' => 'Закрытие устаревших льгот',
					'region' => array('perm','saratov','khak','krym'),
					'sql' => "
						with mv1 as (
							select
								dbo.tzgetdate() as dt
						), mv2 as (
							select
								to_char(er.EvnRecept_obrDT, 'yyyy-mm-dd') as EvnRecept_obrDT
							from 
								v_EvnRecept_all er
							where
								er.Person_id = :Person_id and
								er.EvnRecept_obrDT > (select dt frm mv1)
							order by
								er.EvnRecept_obrDT DESC
							limit 1
						), mv3 as (
							select
								case 
									when (select EvnRecept_obrDT from mv2) is NULL then (select dt frm mv1)
									else (select EvnRecept_obrDT from mv2) 
								end as PersonPrivilege_endDate
							limit 1
						)
						
						-- закрываем все льготы Person_id
						UPDATE 
							dbo.PersonPrivilege
						SET 
							PersonPrivilege_endDate = (select PersonPrivilege_endDate from mv3),
							pmUser_updID = 1,
							PersonPrivilege_updDT = (select dt frm mv1)
						where 
							personPrivilege_id in (
								select 
									pp.personPrivilege_id 
								from 
									PersonPrivilege pp
									inner join stg.frlPerson fp on 
										fp.Person_id = :Person_id AND
										pp.Person_id = fp.Person_id AND 
										RegisterListLog_id = :RegisterListLog_id AND 
										fp.frlPerson_ChangeCode = :frlPerson_ChangeCode
								WHERE 
									personPrivilege_id = pp.personprivilege_id 
							)
					"
				), array(
					'RegisterListLog_id' => $RegisterListLog_id,
					'Person_id' => $dataPerson['Person_id']
				));
			}
		}



	}


	/**
	 * обновление данных о льготе
	 *
	 * @param $RegisterListLog_id
	 * @return bool
	 */
	public function run_code03($RegisterListLog_id){

		//return false;

		$frlPerson_ChangeCode = '03';
		// -------------------------------------------------------------------------------------------------------------
		// Получаем список записей с кодом 03
		$arrPersons = $this->db->query("
			select 
				frlPerson_id as \"frlPerson_id\" 
			from 
				stg.frlPerson
			where 
				Person_id is not null AND 
				frlPerson_ChangeCode = :frlPerson_ChangeCode AND
				RegisterListLog_id = :RegisterListLog_id
		", array(
			'frlPerson_ChangeCode' => $frlPerson_ChangeCode,
			'RegisterListLog_id' => $RegisterListLog_id,
		))->result('array');

		if(empty($arrPersons) || !is_array($arrPersons) || count($arrPersons) == 0){
			// Проверка на наличие
			return true;
		}

		if( ! empty($arrPersons)) {
			//$output = array_slice($arrPersons, 0, 5);
			if($this->getRegionNick() == 'ufa'){
				$considerDateIntersection = "
					,case 
						when (FP.Privilege_begDate <= PP.PersonPrivilege_endDate OR PP.PersonPrivilege_endDate is null) 
								AND 
							(FP.Privilege_endDate >= PP.PersonPrivilege_begDate OR FP.Privilege_endDate is null)
						 then 1
						 else 0
					end as \"considerDateIntersection\" ";
			}else{
				$considerDateIntersection = " ,0 as \"considerDateIntersection\" ";
			}

			foreach ($arrPersons as $dataPerson) {
				$dataPrivilege = $this->db->query("
					select 
                        FP.PrivilegeType_Code as \"PrivilegeType_Code\",
                        FP.name_dl as \"name_dl\",					-- DocumentPrivilegeType_Name
                        FP.s_dl as \"s_dl\",					-- DocumentPrivilege_Ser
                        FP.n_dl as \"n_dl\",					-- DocumentPrivilege_Num
                        FP.sn_dl as \"sn_dl\",					-- DocumentPrivilege_begDate
                        FP.frlPrivilege_Org as \"frlPrivilege_Org\",		-- DocumentPrivilege_Org
                        FP.Privilege_begDate as \"Privilege_begDate\",		-- дата начала действия льготы
                        FP.Privilege_endDate as \"Privilege_endDate\",		-- дата окончания действия льготы
						FP.PersonPrivilege_id as \"PersonPrivilege_id\",
						PT.PrivilegeType_id as \"PrivilegeType_id\",
						PP.PersonPrivilege_IsAddMZ as \"PersonPrivilege_IsAddMZ\",
						PP.Server_id as \"Server_id\",
						PP.PersonPrivilege_begDate as \"PersonPrivilege_begDate\",
						PP.Person_id as \"Person_id\",
						DP.DocumentPrivilege_id as \"DocumentPrivilege_id\",
						case
							--Если у пациента нет рецептов, выписанных раньше этой даты, 
							--то устанавливается дата начала льготы равная дате начала действия права на ГСП, иначе: дата не меняется
							when EXISTS (select ERR.EvnRecept_setDate from v_EvnRecept ERR where ERR.Person_id = FP.Person_id AND ERR.EvnRecept_setDate < FP.Privilege_begDate)
							 then FP.Privilege_begDate
							 else PP.PersonPrivilege_begDate
						end as \"setPersonPrivilege_BegDate\",
						case
							--Если у пациента нет рецептов, выписанных позже этой даты, 
							--то устанавливается дата закрытия льготы равная дате окончания действия права на ГСП, 
							--иначе: дата закрытия льготы равна дате выполнения импорта.
							when FP.Privilege_endDate is null then '2030-01-01'
							when EXISTS (select ERR.EvnRecept_setDate from v_EvnRecept ERR where ERR.Person_id = FP.Person_id AND ERR.EvnRecept_setDate > FP.Privilege_begDate)
							 then dbo.tzGetDate()
							 else FP.Privilege_endDate
						end as \"setPersonPrivilege_EndDate\"
						{$considerDateIntersection}
                    from 
                        stg.frlPrivilege FP
						left join v_PersonPrivilege PP on PP.PersonPrivilege_id = FP.PersonPrivilege_id
						left join v_DocumentPrivilege DP on DP.PersonPrivilege_id = PP.PersonPrivilege_id
						left Join v_PrivilegeType PT on PT.PrivilegeType_Code = cast(FP.PrivilegeType_Code as varchar)
                    where 
						FP.PersonPrivilege_id is not null AND
                        FP.frlPerson_id = :frlPerson_id AND
                        FP.RegisterListLog_id = :RegisterListLog_id
                ", array(
					'frlPerson_id' => $dataPerson['frlPerson_id'],
					'RegisterListLog_id' => $RegisterListLog_id,
				))->row_array();




				//------------------------------------------------------------------------------------------------------
				// скорее всего нужно удалять текущие привилегии и добавлять заново т.к.
				// нет возможности 100% идентификации для изменения информации льгот, возможно, но только если считать,
				// что может быть только 1 льгота определенного типа
				//------------------------------------------------------------------------------------------------------



				// PersonPrivilege -> DocumentPrivilege -> DocumentPrivilegeType_id (Наименование документа, подтверждающего право на ГСП) меняется через замену типа документа
				// [frlPrivilege].PrivilegeType_Code = [PrivilegeType].[PrivilegeType_Code] -> [PrivilegeType].[PrivilegeType_id] = PersonPrivilege.PrivilegeType_id
				// PersonPrivilege.PersonPrivilege_id = DocumentPrivilege.[PersonPrivilege_id] -> DocumentPrivilege.DocumentPrivilege_id


				// @Diag_id =   :Diag_id,
				// @PersonPrivilege_Serie =    :PersonPrivilege_Serie,
				// @PersonPrivilege_Number =   :PersonPrivilege_Number,
				// @PersonPrivilege_IssuedBy = :PersonPrivilege_IssuedBy,
				// @PersonPrivilege_Group =    :PersonPrivilege_Group

				if(empty($dataPrivilege['PersonPrivilege_id'])){
					continue;
				}
				$PersonPrivilege_id = $dataPrivilege['PersonPrivilege_id'];
				$pmUser = $this->getPmUserId();

				$response = $this->queryResult("
                	select
                	 	PersonPrivilege_id as \"PersonPrivilege_id\",
                	 	Error_Code as \"Error_Code\",
                	 	Error_Message as \"Error_Msg\"
                	from p_PersonPrivilege_upd(
                	    Server_id := :Server_id,
                	    PersonPrivilege_id := :PersonPrivilege_id,
                	    Person_id := :Person_id,
                	    PrivilegeType_id := :PrivilegeType_id,
                	    PersonPrivilege_begDate := :PersonPrivilege_begDate,
                	    PersonPrivilege_endDate := :PersonPrivilege_endDate,
                	    pmUser_id := :pmUser_id
                	)
                ", array(
					'PersonPrivilege_id' => $PersonPrivilege_id,
					'Server_id' => $dataPrivilege['Server_id'],
					'Person_id' => $dataPrivilege['Person_id'],
					'PrivilegeType_id' => $dataPrivilege['PrivilegeType_id'],
					'PersonPrivilege_begDate' => ($dataPrivilege['considerDateIntersection']) ? $dataPrivilege['PersonPrivilege_begDate'] : $dataPrivilege['Privilege_begDate'],
					'PersonPrivilege_endDate' => $dataPrivilege['Privilege_endDate'],
					'pmUser_id' => $pmUser

				));

				// =====================================================================================================
				if(empty($dataPrivilege['DocumentPrivilege_id'])){
					continue;
				}

				$DocumentPrivilege_id = $dataPrivilege['DocumentPrivilege_id'];
				//------------------------------------------------------------------------------------------------------
				if( ! empty($dataPrivilege['name_dl'])){
					$dataDocumentPrivilegeType = $this->db->query("
                        select 
                            DocumentPrivilegeType_id as \"DocumentPrivilegeType_id\",
                            DocumentPrivilegeType_Code as \"DocumentPrivilegeType_Code\" 
                        from 
                            DocumentPrivilegeType
                        WHERE
                            DocumentPrivilegeType_Name = :DocumentPrivilegeType_Name;
                    ", array(
						'DocumentPrivilegeType_Name' => $dataPrivilege['name_dl']
					))->row_array();
				}

				$DocumentPrivilegeType_id = null;
				if( ! empty($dataDocumentPrivilegeType['DocumentPrivilegeType_id'])){
					$DocumentPrivilegeType_id = $dataDocumentPrivilegeType['DocumentPrivilegeType_id'];
				}
				//------------------------------------------------------------------------------------------------------





				$s_dl = null;
				if( ! empty($dataPrivilege['s_dl'])){
					$s_dl = $dataPrivilege['s_dl'];
				}

				$n_dl = null;
				if( ! empty($dataPrivilege['n_dl'])){
					$n_dl = $dataPrivilege['n_dl'];
				}

				$sn_dl = null;
				if( ! empty($dataPrivilege['sn_dl'])){
					$sn_dl = $dataPrivilege['sn_dl'];
				}



				$frlPrivilege_Org = null;
				if( ! empty($dataPrivilege['frlPrivilege_Org'])){
					$frlPrivilege_Org = $dataPrivilege['frlPrivilege_Org'];
				}


				//------------------------------------------------------------------------------------------------------
				// DocumentPrivilege

				// DocumentPrivilege.[DocumentPrivilegeType_id]
				// Наименование документа, подтверждающего право на ГСП

				// DocumentPrivilege.[DocumentPrivilege_Ser]
				// Серия документа, подтверждающего право на ГСП

				// DocumentPrivilege.[DocumentPrivilege_Num]
				// Номер документа, подтверждающего право на ГСП

				// DocumentPrivilege.[DocumentPrivilege_begDate]
				// Дата выдачи документа, подтверждающего право на ГСП: ГГГГ/ММ/ДД (п. 13 примечаний)

				// DocumentPrivilege.[DocumentPrivilege_Org]
				// Наименование органа, выдавшего документ, подтверждающий право на ГСП

				$response = $this->queryResult("
                    select
                    	DocumentPrivilege_id as \"DocumentPrivilege_id\",
                    	Error_Code as \"Error_Code\",
                    	Error_Message as \"Error_Mess\"
                    from p_DocumentPrivilege_upd(
                        DocumentPrivilege_id := :DocumentPrivilege_id,
                        DocumentPrivilegeType_id := :DocumentPrivilegeType_id,
						PersonPrivilege_id := :PersonPrivilege_id,
                        DocumentPrivilege_Ser := :s_dl,
                        DocumentPrivilege_Num := :n_dl,
                        DocumentPrivilege_begDate := :sn_dl,
                        DocumentPrivilege_Org := :frlPrivilege_Org,
                        pmUser_id := :pmUser_id
                    )
                ", array(
					'DocumentPrivilege_id' => (int)$DocumentPrivilege_id,
					'DocumentPrivilegeType_id' => (int)$DocumentPrivilegeType_id,
					'PersonPrivilege_id' => $PersonPrivilege_id,
					's_dl' => $s_dl,
					'n_dl' => $n_dl,
					'sn_dl' => $sn_dl,
					'frlPrivilege_Org' => $frlPrivilege_Org,
					'pmUser_id' => $pmUser
				));
				//------------------------------------------------------------------------------------------------------
				//die;

			}
		}
		// -------------------------------------------------------------------------------------------------------------
		return true;
	}


	/**
	 * Подготовка (обработка) данных льготополучателей
	 *
	 * @param $RegisterListLog_id
	 * @return bool
	 * @throws Exception
	 */
	public function _process_frlPerson($RegisterListLog_id){

		// Удаление пустых СНИЛСов
		$this->doQuery(array(
			'title' => 'Удаление пустых СНИЛСов',
			'except_region' => array('krym'),	//Не удалять пустые снилсы для Крыма
			'sql' => '
				delete from 
                    stg.frlPerson 
                where 
                    (ss is null or length(ss)=0) and 
                    RegisterListLog_id = :RegisterListLog_id
			'
		), $RegisterListLog_id);

		// Конвертация дат
		$this->doQuery(array(
			'title' => 'Конвертация дат',
			'sql' => '
				update 
                    stg.frlPerson 
                set 
                    Person_birthday = to_char(dr, \'yyyy-mm-dd\') 
                where 
                    RegisterListLog_id = :RegisterListLog_id
			'
		), $RegisterListLog_id);

		// Форматирование СНИЛС
		$this->doQuery(array(
			'title' => 'Форматирование СНИЛС',
			'sql' => "
				update 
                    stg.frlPerson 
                set 
                    Person_SNILS=REPLACE(REPLACE(ss,'-',''),' ','') 
                where 
                    RegisterListLog_id = :RegisterListLog_id;
                
                update 
                    stg.frlPrivilege 
                set 
                    Person_SNILS=REPLACE(REPLACE(ss,'-',''),' ','') 
                where 
                    RegisterListLog_id = :RegisterListLog_id;
			"
		), $RegisterListLog_id);

		// Перекодировка поля "Пол"
		$this->doQuery(array(
			'title' => 'Перекодировка поля "Пол"',
			'sql' => "
				update 
                    stg.frlPerson 
                set 
                    sex_id=1
                where 
                    w='М' and 
                    RegisterListLog_id = :RegisterListLog_id;
                
                update 
                    stg.frlPerson 
                set 
                    sex_id=2 
                where 
                    w='Ж' and 
                    RegisterListLog_id = :RegisterListLog_id;
			"
		), $RegisterListLog_id);

		return true;

	}

	/**
	 * Подготовка (обработка) данных о льготах
	 *
	 * @param $RegisterListLog_id
	 * @return bool
	 * @throws Exception
	 */
	public function _process_frlPrivilege($RegisterListLog_id){

		// Присвоение даты льгот
		// update stg.frlPrivilege
		$this->doQuery(array(
			'title' => 'Присвоение даты льгот',
			'sql' => "
				update stg.frlPrivilege set Privilege_begDate=to_char(date_bl, 'yyyy-mm-dd')where (date_bl<>'9999/99/99' and date_bl<>'') and RegisterListLog_id = :RegisterListLog_id;
				update stg.frlPrivilege set Privilege_endDate=to_char(date_el, 'yyyy-mm-dd')where (date_el<>'9999/99/99' and date_el<>'' and date_el<>'2030/01/01')  and RegisterListLog_id = :RegisterListLog_id;
			"
		), $RegisterListLog_id);


		$this->doQuery(array(
			'title' => 'Форматирование СНИЛС и кода льготы',
			'sql' => "
                update stg.frlPrivilege set Person_SNILS=REPLACE(REPLACE(ss,'-',''),' ','') where RegisterListLog_id = :RegisterListLog_id;
                update stg.frlPrivilege set PrivilegeType_Code=CAST(c_katl as int) where RegisterListLog_id = :RegisterListLog_id;
            "
		), $RegisterListLog_id);


		$this->doQuery(array(
			'title' => 'Форматирование кода льготы',
			'sql' => "update stg.frlPrivilege set PrivilegeType_Code = cast(C_KATL as int) where RegisterListLog_id = :RegisterListLog_id"
		), $RegisterListLog_id);

		return true;
	}

	/**
	 * Поиск двойников и определение Person_id
	 * В итоге записи с двойниками будут stg.frlPerson.Person_id != 0, новые льготополучатели stg.frlPerson.Person_id = 0
	 *
	 * @param $RegisterListLog_id
	 * @return bool
	 * @throws Exception
	 */
	public function _identification_frlPerson($RegisterListLog_id){
		// Поиск Двойников 1

		// Запрос 1: узнаем количество двойников и сохраняем данные в #tmpPerson
		// Запрос 2: сохраняем кол-во двойников по идентификатору frlPerson_id в таблице stg.frlPerson
		// identVariant - это вроде что-то типа "номер этапа идентификации"
		$this->doQuery(array(
			'title' => 'Поиск Двойников 1',
			'sql' => "
				with tmpPerson as (
					select
               		    l.frlPerson_id,
               		    pc.cnt
               		from
               		    stg.frlPerson l
               		    inner join lateral(
               		        select COUNT(*) as cnt
               		        from v_PersonState ps
               		        where rtrim(REPLACE(ps.Person_SurName,'ё','е')) = rtrim(ltrim(REPLACE(l.fam,'ё','е')))
               		            and rtrim(REPLACE(ps.Person_FirName,'ё','е')) = rtrim(ltrim(REPLACE(l.im,'ё','е')))
               		            and (rtrim(REPLACE(ps.Person_SecName,'ё','е')) = rtrim(ltrim(REPLACE(l.ot,'ё','е'))) or (nullif(replace(ps.Person_SecName,' ',''),'---') is null and nullif(rtrim(ltrim(l.ot)),'') is null))
               		            and ps.Person_BirthDay = l.Person_BirthDay
               		            and ps.Person_Snils = l.Person_Snils
               		    ) pc on true
               		where
               		    coalesce(l.identCount,0) = 0 -- для тех frlPerson_id по которым не найдены совпадения
               		    and l.RegisterListLog_id = :RegisterListLog_id
                )
    
                update
                    stg.frlPerson
                set
                    identCount = ps.cnt,
                    identVariant = 1
                from
                    tmpPerson ps
                where
                    frlPerson.frlPerson_id = ps.frlPerson_id
                    and frlPerson.RegisterListLog_id = :RegisterListLog_id;
            "
		), $RegisterListLog_id);

		// Поиск Двойников 2
		$this->doQuery(array(
			'title' => 'Поиск Двойников 2',
			'sql' => "
				with tmpPerson as (
					select
						l.frlPerson_id,
						pc.cnt
					from
						stg.frlPerson l
						inner join lateral(
							select COUNT(*) as cnt
							from v_PersonState ps
							where rtrim(REPLACE(ps.Person_SurName,'ё','е')) = rtrim(ltrim(REPLACE(l.fam,'ё','е')))
								and rtrim(REPLACE(ps.Person_FirName,'ё','е')) = rtrim(ltrim(REPLACE(l.im,'ё','е')))
								and ps.Person_BirthDay = l.Person_BirthDay
								and ps.Person_Snils = l.Person_Snils
								and nullif(l.ot,'') is null
						) pc on true
					where
						l.identCount = 0
						and l.RegisterListLog_id = :RegisterListLog_id
				)
					
				update
					stg.frlPerson
				set
					identCount = ps.cnt,
					identVariant = 2
				from
					tmpPerson ps
				where
					frlPerson.frlPerson_id = ps.frlPerson_id
					and frlPerson.RegisterListLog_id = :RegisterListLog_id;
			"
		), $RegisterListLog_id);

		// Поиск Двойников 3
		$this->doQuery(array(
			'title' => 'Поиск Двойников 3',
			'sql' => "
				with tmpPerson as (
					select
						l.frlPerson_id,
						pc.cnt
					from
						stg.frlPerson l
						inner join lateral(
							select COUNT(*) as cnt
							from v_PersonState ps
							where rtrim(ltrim(REPLACE(ps.Person_SurName,'ё','е'))) = rtrim(ltrim(REPLACE(l.fam,'ё','е')))
								and ps.Person_BirthDay = l.Person_BirthDay
								and rtrim(ltrim(ps.Person_Snils)) = rtrim(ltrim(l.Person_Snils))
								and nullif(l.im,'') is null
						) pc on true
					where
						l.identCount = 0
						and l.RegisterListLog_id = :RegisterListLog_id
				)
					
				update
					stg.frlPerson
				set
					identCount = ps.cnt,
					identVariant = 3
				from
					tmpPerson ps
				where
					frlPerson.frlPerson_id = ps.frlPerson_id
					and frlPerson.RegisterListLog_id = :RegisterListLog_id;
			"
		), $RegisterListLog_id);

		// Поиск Двойников 4
		$this->doQuery(array(
			'title' => 'Поиск Двойников 4',
			'sql' => "
				with tmpPerson as (
					select
						l.frlPerson_id,
						pc.cnt
					from
						stg.frlPerson l
						inner join lateral(
							select COUNT(*) as cnt
							from v_PersonState ps
							where rtrim(ltrim(REPLACE(ps.Person_SurName,'ё','е'))) = rtrim(ltrim(REPLACE(l.fam,'ё','е')))
								and rtrim(ltrim(REPLACE(ps.Person_FirName,'ё','е'))) = rtrim(ltrim(REPLACE(l.im,'ё','е')))
								and (rtrim(ltrim(REPLACE(ps.Person_SecName,'ё','е'))) = rtrim(ltrim(REPLACE(l.ot,'ё','е'))) or (nullif(replace(ps.Person_SecName,' ',''),'---') is null and nullif(rtrim(ltrim(l.ot)),'') is null))
								and ps.Person_Snils = l.Person_Snils
						) pc on true
					where
						l.identCount = 0
						and l.RegisterListLog_id = :RegisterListLog_id
				)
					
				update
					stg.frlPerson
				set
					identCount = ps.cnt,
					identVariant = 4
				from
					tmpPerson ps
				where
					frlPerson.frlPerson_id = ps.frlPerson_id
					and frlPerson.RegisterListLog_id = :RegisterListLog_id;
			"
		), $RegisterListLog_id);

		// Поиск Двойников 5
		$this->doQuery(array(
			'title' => 'Поиск Двойников 5',
			'sql' => "
				with tmpPerson as (
					select
						l.frlPerson_id,
						pc.cnt
					from
						stg.frlPerson l
						inner join lateral(
							select COUNT(*) as cnt
							from v_PersonState ps
							where rtrim(ltrim(REPLACE(ps.Person_SurName,'ё','е'))) = rtrim(ltrim(REPLACE(l.fam,'ё','е')))
							and rtrim(ltrim(REPLACE(ps.Person_FirName,'ё','е'))) = rtrim(ltrim(REPLACE(l.im,'ё','е')))
							and (rtrim(ltrim(REPLACE(ps.Person_SecName,'ё','е'))) = rtrim(ltrim(REPLACE(l.ot,'ё','е'))) or (nullif(replace(ps.Person_SecName,' ',''),'---') is null and nullif(rtrim(ltrim(l.ot)),'') is null))
							and ps.Person_BirthDay = l.Person_BirthDay
						) pc on true
					where
						l.identCount = 0
						and l.RegisterListLog_id = :RegisterListLog_id
				)
					
				update
					stg.frlPerson
				set
					identCount = ps.cnt,
					identVariant = 5
				from
					tmpPerson ps
				where
					frlPerson.frlPerson_id = ps.frlPerson_id
					and frlPerson.RegisterListLog_id = :RegisterListLog_id;
			"
		), $RegisterListLog_id);

		// Поиск Двойников 6
		$this->doQuery(array(
			'title' => 'Поиск Двойников 6',
			'region' => array('krym'),
			'sql' => "
				with tmpPerson as (
					select
						l.frlPerson_id,
						pc.cnt
					from
						stg.frlPerson l
						inner join lateral(
							select COUNT(*) as cnt
							from v_PersonState ps
							where rtrim(REPLACE(ps.Person_SurName,'ё','е')) = rtrim(ltrim(REPLACE(l.fam,'ё','е')))
								and rtrim(REPLACE(ps.Person_FirName,'ё','е')) = rtrim(ltrim(REPLACE(l.im,'ё','е')))
								and (rtrim(REPLACE(ps.Person_SecName,'ё','е')) = rtrim(ltrim(REPLACE(l.ot,'ё','е'))) or (nullif(replace(ps.Person_SecName,' ',''),'---') is null and nullif(rtrim(ltrim(l.ot)),'') is null))
								and ps.Person_BirthDay = l.Person_BirthDay
								and l.Person_Snils = ''
						) pc on true
					where
						l.identCount = 0
						and l.RegisterListLog_id = :RegisterListLog_id
				)
					
				update
					stg.frlPerson
				set
					identCount = ps.cnt,
					identVariant = 6
				from
					tmpPerson ps
				where
					frlPerson.frlPerson_id = ps.frlPerson_id
					and frlPerson.RegisterListLog_id = :RegisterListLog_id;
			"
		), $RegisterListLog_id);

		// Идентификация 1
		$this->doQuery(array(
			'title' => 'Идентификация 1',
			'sql' => "
				with tmpPerson as (
					select
						l.frlPerson_id,
						ps.Person_id
					from
						stg.frlPerson l
						inner join v_PersonState ps on rtrim(REPLACE(ps.Person_SurName,'ё','е')) = rtrim(ltrim(REPLACE(l.fam,'ё','е'))) and rtrim(REPLACE(ps.Person_FirName,'ё','е')) = rtrim(ltrim(REPLACE(l.im,'ё','е')))
						and (rtrim(REPLACE(ps.Person_SecName,'ё','е')) = rtrim(ltrim(REPLACE(l.ot,'ё','е'))) or (nullif(replace(ps.Person_SecName,' ',''),'---') is null and nullif(rtrim(ltrim(l.ot)),'') is null))
						and ps.Person_BirthDay = l.Person_BirthDay and ps.Person_Snils = l.Person_Snils
					where
						l.person_id is null
						and l.identCount = 1
						and l.identVariant = 1
						and l.RegisterListLog_id = :RegisterListLog_id
				)
					
				update
					stg.frlPerson
				set
					Person_id = ps.Person_id
				from
					tmpPerson ps
				where
					frlPerson.frlPerson_id = ps.frlPerson_id
					and frlPerson.RegisterListLog_id = :RegisterListLog_id;
			"
		), $RegisterListLog_id);

		// Идентификация 2
		$this->doQuery(array(
			'title' => 'Идентификация 2',
			'sql' => "
				with tmpPerson as (
					select
						l.frlPerson_id,
						ps.Person_id
					from
						stg.frlPerson l
						inner join v_PersonState ps on rtrim(REPLACE(ps.Person_SurName,'ё','е')) = rtrim(ltrim(REPLACE(l.fam,'ё','е'))) and rtrim(REPLACE(ps.Person_FirName,'ё','е')) = rtrim(ltrim(REPLACE(l.im,'ё','е')))
						and ps.Person_BirthDay = l.Person_BirthDay and ps.Person_Snils = l.Person_Snils
					where
						l.person_id is null
						and l.identCount = 1
						and l.identVariant = 2
						and l.RegisterListLog_id = :RegisterListLog_id
				)
					
				update
					stg.frlPerson
				set
					Person_id = ps.Person_id
				from
					tmpPerson ps
				where
					frlPerson.frlPerson_id = ps.frlPerson_id
					and frlPerson.RegisterListLog_id = :RegisterListLog_id;
			"
		), $RegisterListLog_id);

		// Идентификация 3
		$this->doQuery(array(
			'title' => 'Идентификация 3',
			'sql' => "
				with tmpPerson as (
					select
						l.frlPerson_id,
						ps.Person_id
					from
						stg.frlPerson l
						inner join v_PersonState ps on rtrim(REPLACE(ps.Person_SurName,'ё','е')) = rtrim(ltrim(REPLACE(l.fam,'ё','е')))
						and ps.Person_BirthDay = l.Person_BirthDay
						and rtrim(ltrim(ps.Person_Snils)) = rtrim(ltrim(l.Person_Snils))
					where
						l.Person_id is null
						and l.identCount = 1
						and l.identVariant = 3
						and l.RegisterListLog_id = :RegisterListLog_id
				)
	
				update
					stg.frlPerson
				set
					Person_id = ps.Person_id
				from
					tmpPerson ps
				where
					frlPerson.frlPerson_id = ps.frlPerson_id
					and frlPerson.RegisterListLog_id = :RegisterListLog_id;
			"
		), $RegisterListLog_id);

		// Идентификация 4
		$this->doQuery(array(
			'title' => 'Идентификация 4',
			'sql' => "
				with tmpPerson as (
					select
						l.frlPerson_id,
						ps.Person_id
					from
						stg.frlPerson l
						inner join v_PersonState ps on rtrim(ltrim(REPLACE(ps.Person_SurName,'ё','е'))) = rtrim(ltrim(REPLACE(l.fam,'ё','е')))
						and rtrim(ltrim(REPLACE(ps.Person_FirName,'ё','е'))) = rtrim(ltrim(REPLACE(l.im,'ё','е')))
						and (rtrim(ltrim(REPLACE(ps.Person_SecName,'ё','е'))) = rtrim(ltrim(REPLACE(l.ot,'ё','е'))) or (nullif(replace(ps.Person_SecName,' ',''),'---') is null and nullif(rtrim(ltrim(l.ot)),'') is null))
						and rtrim(ltrim(ps.Person_Snils)) = rtrim(ltrim(l.Person_Snils))
					where
						l.Person_id is null
						and l.identCount = 1
						and l.identVariant = 4
						and l.RegisterListLog_id = :RegisterListLog_id
				)
					
				update
					stg.frlPerson
				set
					Person_id = ps.Person_id
				from
					tmpPerson ps
				where
					frlPerson.frlPerson_id = ps.frlPerson_id
					and frlPerson.RegisterListLog_id = :RegisterListLog_id;
			"
		), $RegisterListLog_id);

		// Идентификация 5
		$this->doQuery(array(
			'title' => 'Идентификация 5',
			'sql' => "
				with tmpPerson as (
					select
						l.frlPerson_id,
						ps.Person_id
					from
						stg.frlPerson l
						inner join v_PersonState ps on rtrim(ltrim(REPLACE(ps.Person_SurName,'ё','е'))) = rtrim(ltrim(REPLACE(l.fam,'ё','е')))
						and rtrim(ltrim(REPLACE(ps.Person_FirName,'ё','е'))) = rtrim(ltrim(REPLACE(l.im,'ё','е')))
						and (rtrim(ltrim(REPLACE(ps.Person_SecName,'ё','е'))) = rtrim(ltrim(REPLACE(l.ot,'ё','е'))) or (nullif(replace(ps.Person_SecName,' ',''),'---') is null and nullif(rtrim(ltrim(l.ot)),'') is null))
						and ps.Person_BirthDay = l.Person_BirthDay
					where
						l.Person_id is null
						and l.identCount = 1
						and l.identVariant = 5
						and l.RegisterListLog_id = :RegisterListLog_id
				)
	
				update
					stg.frlPerson
				set
					Person_id = ps.Person_id
				from
					tmpPerson ps
				where
					frlPerson.frlPerson_id = ps.frlPerson_id
					and frlPerson.RegisterListLog_id = :RegisterListLog_id;
			"
		), $RegisterListLog_id);

		// Идентификация 6
		$this->doQuery(array(
			'title' => 'Идентификация 6',
			'region' => array('krym'),
			'sql' => "
				with tmpPerson as (
					select
						l.frlPerson_id,
						ps.Person_id
					from
						stg.frlPerson l
						inner join v_PersonState ps on rtrim(REPLACE(ps.Person_SurName,'ё','е')) = rtrim(ltrim(REPLACE(l.fam,'ё','е'))) and rtrim(REPLACE(ps.Person_FirName,'ё','е')) = rtrim(ltrim(REPLACE(l.im,'ё','е')))
						and (rtrim(REPLACE(ps.Person_SecName,'ё','е')) = rtrim(ltrim(REPLACE(l.ot,'ё','е'))) or (nullif(replace(ps.Person_SecName,' ',''),'---') is null and nullif(rtrim(ltrim(l.ot)),'') is null))
						and ps.Person_BirthDay = l.Person_BirthDay
					where
						l.Person_id is null
						and l.identCount = 1
						and l.identVariant = 6
						and l.RegisterListLog_id = :RegisterListLog_id
				)
					
				update
					stg.frlPerson
				set
					Person_id = ps.Person_id
				from
					tmpPerson ps
				where
					frlPerson.frlPerson_id = ps.frlPerson_id
					and frlPerson.RegisterListLog_id = :RegisterListLog_id;
			"
		), $RegisterListLog_id);

		// Двойники 1
		$this->doQuery(array(
			'title' => 'Двойники 1',
			'sql' => "
				with tmpPerson as (
					select
						l.frlPerson_id,
						pc.Person_id
					from
						stg.frlPerson l
						inner join lateral(
							select
								ps.Person_id
							from
								v_PersonState_all ps
							where
								rtrim(REPLACE(ps.Person_SurName,'ё','е')) = rtrim(ltrim(REPLACE(l.fam,'ё','е')))
								and rtrim(REPLACE(ps.Person_FirName,'ё','е')) = rtrim(ltrim(REPLACE(l.im,'ё','е')))
								and (rtrim(REPLACE(ps.Person_SecName,'ё','е')) = rtrim(ltrim(REPLACE(l.ot,'ё','е'))) or (nullif(replace(ps.Person_SecName,' ',''),'---') is null and nullif(rtrim(ltrim(l.ot)),'') is null))
								and ps.Person_BirthDay = l.Person_BirthDay
								and ps.Person_Snils = l.Person_Snils
							order by
								ps.Person_IsBDZ desc, ps.Person_id
							limit 1
						) pc on true
					where
						l.identCount > 1
						and l.Person_id is null
						and l.RegisterListLog_id = :RegisterListLog_id
				)
					
				update
					stg.frlPerson
				set
					Person_id = ps.Person_id
				from
					tmpPerson ps
				where
					frlPerson.frlPerson_id = ps.frlPerson_id
					and frlPerson.RegisterListLog_id = :RegisterListLog_id;
			"
		), $RegisterListLog_id);

		// Двойники 2
		$this->doQuery(array(
			'title' => 'Двойники 2',
			'sql' => "
				with tmpPerson as (
					select
						l.frlPerson_id,
						pc.Person_id
					from
						stg.frlPerson l
						inner join lateral(
							select
								ps.Person_id
							from
								v_PersonState_all ps
							where
								rtrim(ltrim(REPLACE(ps.Person_SurName,'ё','е'))) = rtrim(ltrim(REPLACE(l.fam,'ё','е')))
								and ps.Person_BirthDay = l.Person_BirthDay
								and rtrim(ltrim(ps.Person_Snils)) = rtrim(ltrim(l.Person_Snils))
							order by
								ps.Person_IsBDZ desc, ps.Person_id
							limit 1
						) pc on true
					where
						l.identCount > 1
						and l.Person_id is null
						and l.RegisterListLog_id = :RegisterListLog_id
				)
					
				update
					stg.frlPerson
				set
					Person_id = ps.Person_id
				from
					tmpPerson ps
				where
					frlPerson.frlPerson_id = ps.frlPerson_id
					and frlPerson.RegisterListLog_id = :RegisterListLog_id;
			"
		), $RegisterListLog_id);

		// Двойники 3
		$this->doQuery(array(
			'title' => 'Двойники 3',
			'sql' => "
				with tmpPerson as (
					select
						l.frlPerson_id,
						pc.Person_id
					from
						stg.frlPerson l
						inner join lateral(
							select
								ps.Person_id
							from
								v_PersonState_all ps
							where
								rtrim(ltrim(REPLACE(ps.Person_SurName,'ё','е'))) = rtrim(ltrim(REPLACE(l.fam,'ё','е')))
								and rtrim(ltrim(REPLACE(ps.Person_FirName,'ё','е'))) = rtrim(ltrim(REPLACE(l.im,'ё','е')))
								and (rtrim(ltrim(REPLACE(ps.Person_SecName,'ё','е'))) = rtrim(ltrim(REPLACE(l.ot,'ё','е'))) or (nullif(replace(ps.Person_SecName,' ',''),'---') is null and nullif(rtrim(ltrim(l.ot)),'') is null))
								and ps.Person_Snils = l.Person_Snils
							order by
								ps.Person_IsBDZ desc, ps.Person_id
							limit 1
						) pc on true
					where
						l.identCount > 1
						and l.Person_id is null
						and l.RegisterListLog_id = :RegisterListLog_id
				)
	
				update
					stg.frlPerson
				set
					Person_id = ps.Person_id
				from
					tmpPerson ps
				where
					frlPerson.frlPerson_id = ps.frlPerson_id
					and frlPerson.RegisterListLog_id = :RegisterListLog_id;
			"
		), $RegisterListLog_id);

		// Двойники 4
		$this->doQuery(array(
			'title' => 'Двойники 4',
			'sql' => "
				with tmpPerson as (
					select
						l.frlPerson_id,
						pc.Person_id
					from
						stg.frlPerson l
						inner join lateral(
							select
								ps.Person_id
							from
								v_PersonState_all ps
							where
								rtrim(ltrim(REPLACE(ps.Person_SurName,'ё','е'))) = rtrim(ltrim(REPLACE(l.fam,'ё','е')))
								and rtrim(ltrim(REPLACE(ps.Person_FirName,'ё','е'))) = rtrim(ltrim(REPLACE(l.im,'ё','е')))
								and (rtrim(ltrim(REPLACE(ps.Person_SecName,'ё','е'))) = rtrim(ltrim(REPLACE(l.ot,'ё','е'))) or (nullif(replace(ps.Person_SecName,' ',''),'---') is null and nullif(rtrim(ltrim(l.ot)),'') is null))
								and ps.Person_BirthDay = l.Person_BirthDay
							order by
								ps.Person_IsBDZ desc, ps.Person_id
							limit 1
						) pc on true
					where
						l.identCount > 1
						and l.Person_id is null
						and l.RegisterListLog_id = :RegisterListLog_id
				)
				
				update
					stg.frlPerson
				set
					Person_id = ps.Person_id
				from
					tmpPerson ps
				where
					frlPerson.frlPerson_id = ps.frlPerson_id
					and frlPerson.RegisterListLog_id = :RegisterListLog_id;
			"
		), $RegisterListLog_id);

		// Поиск Двойников 5
		$this->doQuery(array(
			'title' => 'Поиск Двойников 5',
			'region' => array('krym'),
			'sql' => "
				with tmpPerson as (
					select
						l.frlPerson_id,
						pc.Person_id
					from
						stg.frlPerson l
						inner join lateral(
							select
								ps.Person_id
							from
								v_PersonState_all ps
							where
								rtrim(REPLACE(ps.Person_SurName,'ё','е')) = rtrim(ltrim(REPLACE(l.fam,'ё','е')))
								and rtrim(REPLACE(ps.Person_FirName,'ё','е')) = rtrim(ltrim(REPLACE(l.im,'ё','е')))
								and (rtrim(REPLACE(ps.Person_SecName,'ё','е')) = rtrim(ltrim(REPLACE(l.ot,'ё','е'))) or (nullif(replace(ps.Person_SecName,' ',''),'---') is null and nullif(rtrim(ltrim(l.ot)),'') is null))
								and ps.Person_BirthDay = l.Person_BirthDay
								and l.Person_Snils = ''
							order by
								ps.Person_IsBDZ desc, ps.Person_id
							limit 1
						) pc on true
					where
						l.identCount > 1
						and l.Person_id is null
						and l.RegisterListLog_id = :RegisterListLog_id
				)
	
				update
					stg.frlPerson
				set
					Person_id = ps.Person_id
				from
					tmpPerson ps
				where
					frlPerson.frlPerson_id = ps.frlPerson_id
					and frlPerson.RegisterListLog_id = :RegisterListLog_id;
			"
		), $RegisterListLog_id);

		return true;
	}

	/**
	 *
	 * @param type $registerName
	 */
	public function getRegisterListIdByName($registerName) {
		$q = "Select RegisterList_id as \"RegisterList_id\"
		from stg.v_RegisterList
		where RegisterList_Name = :RegName";
		$p = array("RegName" => $registerName);
		$res = $this->db->query($q, $p)->result();
	}

	/**
	 *
	 * @return type
	 */
	public function getPmUserId() {
		return $this->pmUser_id;
	}

	/**
	 *
	 * @return type
	 */
	public function cleanArray($array) {
		foreach ($array as $key => &$value) {
			if (empty($value)) {
				unset($array[$key]);
			}
		}
		return $array;
	}


	/**
	 * Импорт карт СМП
	 */
	public function importCmpCallCardFromDBF($files, $fileName=false) {
		set_time_limit(0);
		ini_set("memory_limit", "2024M");
		ini_set("max_execution_time", "0");
		ini_set("max_input_time", "0");
		ini_set("post_max_size", "220");
		ini_set("default_socket_timeout", "999");
		ini_set("upload_max_filesize", "220M");
		//посылаем ответ клиенту...
		ignore_user_abort(true);
		ob_start();
		echo json_encode(array("success" => "true"));

		$size = ob_get_length();

		header("Content-Length: $size");
		header("Content-Encoding: none");
		header("Connection: close");

		ob_end_flush();
		ob_flush();
		flush();

		if (session_id())
			session_write_close();

		//... и продолжаем выполнять скрипт на стороне сервера.
		libxml_use_internal_errors(true); // изменить на false для вывода ошибок в консоль
		error_reporting(0); // что бы не выводились предупреждения при отладке*/

		$this->load->model('RegisterListLog_model', 'RegisterListLog_model');
		$this->load->model('RegisterListDetailLog_model', 'RegisterListDetailLog_model');
		$this->load->model('Org_model', 'Org_model');

		$pmUserId = $this->getPmUserId();

		$this->RegisterListLog_model->setpmUser_id($pmUserId);
		$this->RegisterListLog_model->setRegisterList_id(27);
		$this->RegisterListLog_model->setRegisterListLog_begDT(new DateTime());
		$this->RegisterListLog_model->setRegisterListRunType_id(8); //8-вручную
		$this->RegisterListLog_model->setRegisterListResultType_id(2); //2-Выполняется
		if(!empty($fileName)) $this->RegisterListLog_model->setRegisterListLog_NameFile($fileName);

		$this->RegisterListLog_model->save();
		$RegisterListLog_id = $this->RegisterListLog_model->getRegisterListLog_id();
		//RegisterListDetailLog_model::createLogMessage(new DateTime(), 1, 'Запуск импорта организаций', $RegisterListLog_id, $pmUserId);

		$this->load->model('AmbulanceCard_model');

		$counter = 0;
		$addedUpdatedCounter = 0;
		foreach ($files as $key => $value) {
			// дербаним дбф-ку
			$h = dbase_open($value, 0);
			if ($h) {
				$r = dbase_numrecords($h);
				for ($i = 1; $i <= $r; $i++) {
					$counter++;
					$rech = dbase_get_record_with_names($h, $i);

					$inputParams = array('pmUser_id' => $pmUserId);

					array_walk($rech, 'ConvertFromWin866ToUtf8');

					foreach($rech as $key=>$val) {
						$val = trim($val);

						switch ($key) {
							case 'TABN':
								$inputParams['Tabn'] = $val;
								break;
							case 'PROF':
								$inputParams['PROFC'] = $val;
								$inputParams['PROFS'] = $val;
								break;
							case 'PRFB':
								$inputParams['PRFBC'] = $val;
								$inputParams['PRFBS'] = $val;
								break;
							case 'REZL':
								$inputParams['REZLC'] = $val;
								$inputParams['REZLS'] = $val;
								break;
							case 'MEST':
								$inputParams['PLC'] = $val;
								$inputParams['PLS'] = $val;
								break;
							case 'POVT':
								$inputParams['CLTPC'] = $val;
								$inputParams['CLTPS'] = $val;
								break;
							case 'MKB':
								$inputParams['DGUC'] = $val;
								break;
							case 'DS1':
								$inputParams['DGSC'] = $val;
								break;
							case 'POVD':
								$inputParams['REAC'] = $val;
								$inputParams['REAS'] = $val;
								break;
							case 'MEDS':
								$inputParams[$key] = $val;
								break;
							case 'TPRM':
							case 'TPER':
							case 'VYEZ':
							case 'PRZD':
							case 'TGSP':
							case 'TSTA':
							case 'TISP':
							case 'TVZV':
							case 'TEND':
								$inputParams[$key] = empty($val) || preg_match('/\d\d:\d\d/', $val) ? $val : '00:00';
								break;
							case 'VOZR':
								$inputParams[$key] = (empty($val) || is_numeric($val) ? $val : null);
								break;
							default:
								$inputParams[$key] = $val;
								break;
						}
					}

					// Преобразуем поля, содержащие время, в дата-время
					// https://redmine.swan.perm.ru/issues/49361
					if ( !empty($inputParams['DPRM']) ) {
						$DPRM = DateTime::createFromFormat('ymd H:i', $inputParams['DPRM'] . ' ' . $inputParams['TPRM']);

						if ( is_object($DPRM) ) {
							foreach ( $inputParams as $key => $val ) {
								switch ( $key ) {
									case 'TPER':
									case 'VYEZ':
									case 'PRZD':
									case 'TGSP':
									case 'TSTA':
									case 'TISP':
									case 'TVZV':
									case 'TEND':
										$parsed = DateTime::createFromFormat('ymd H:i', $inputParams['DPRM'] . ' ' . $inputParams[$key]);

										if ( is_object($parsed) ) {
											if ( $parsed < $DPRM ) {
												$parsed->add(new DateInterval('P1D'));
											}

											$inputParams[$key] = $parsed->format('Y-m-d H:i:s');
										}
										else {
											$inputParams[$key] = $inputParams['DPRM'] . ' ' . $inputParams[$key];
										}
										break;
								}
							}
						}
					}

					if ( !empty($inputParams['DPRM']) ) {
						$inputParams['DPRM'] .= ' ' . $inputParams['TPRM'];
					}

					$result = $this->AmbulanceCard_model->saveAmbulanceCard($inputParams);

					if (!$result || !empty($result[0]['Error_Msg'])) {
						// ошибка импорта записи
					} elseif (!array_key_exists('exists', $result[0]) || $result[0]['exists'] === false) {
						$addedUpdatedCounter++;
					}
				}

				dbase_close ($h);
			}
		}

		$this->RegisterListLog_model->setRegisterListLog_endDT(new Datetime());
		$this->RegisterListLog_model->setRegisterListLog_AllCount($counter); //Всего карт во всех файлах
		$this->RegisterListLog_model->setRegisterListLog_UploadCount($addedUpdatedCounter); //Загружено и обновлено карт
		if ($addedUpdatedCounter > 0) {
			$this->RegisterListLog_model->setRegisterListResultType_id(1);
		} else {
			$this->RegisterListLog_model->setRegisterListResultType_id(3);
		}
		$this->RegisterListLog_model->save();

		$this->textlog->add('importCmpCallCardFromDBF: Закончен импорт карт СМП из всех файлов');

		return array('Error_Msg' => '');
	}

	/**
	 *
	 * @param type $pmUser_id
	 */
	public function setPmUserId($pmUser_id) {
		$this->pmUser_id = $pmUser_id;
	}

	/**
	 *
	 * @return type
	 */
	public function getVersion() {
		return $this->verLoad;
	}

	/**
	 *
	 * @param type $Ver
	 */
	public function setVersion($ver) {
		$this->verLoad = $ver;
	}

	/**
	 *
	 * @param type $RegisterListLog_id
	 * @param type $action
	 * @param type $inputFolder
	 * @param type $files
	 */
	private function importDbf($RegisterListLog_id, $action, $inputFolder, $files=null, $ignoredelete = false) {
		$this->load->model('DbfImporter_model', 'DbfImporter_model');
		$recordCount = 0;
		$iii = 0;

		if ($inputFolder != null) {
			foreach ($action['files'] as $filename => $dbfFile) {
				if (is_file($inputFolder . $filename)) {
					$iii++;
					$this->DbfImporter_model->schemaName = $dbfFile['destination']['schemaName'];
					$that = $this;

					$callback = function ($current_record, $record_count) use ($that, $filename, $dbfFile, $action) {
						$that->setStatus($action['title'], $filename, round($current_record / $record_count * 100));
					};

					$this->DbfImporter_model->import($RegisterListLog_id, $inputFolder . $filename, $dbfFile['destination']['tableName'], $dbfFile['destination']['fields_mapping'], false, true);
					$recordCount = $recordCount + $this->DbfImporter_model->getRecordCount();
					RegisterListDetailLog_model::createLogMessage(
						new DateTime(), 1, 'Количество записей в загруженных файлах: ' . $recordCount, $this->RegisterListLog_model->getRegisterListLog_id(), $this->getPmUserId()
					);
					$res = $this->db->query("select (select count(*) from stg.frlPerson) as \"Person\",(select count(*) from stg.frlPrivilege) as \"Privilege\"")->result('array');
					$this->RegisterListLog_model->setRegisterListLog_AllCount($res[0]["Person"]);
					$this->RegisterListLog_model->setRegisterListLog_UploadCount($res[0]["Privilege"]);
				}
				else {
					throw new Exception("Не был передан $filename");
				}
			}
			$this->DbfImporter_model->linkBySNILS($RegisterListLog_id);
		}
		else {
			$this->DbfImporter_model->importFRL($files, $RegisterListLog_id, $this->getPmUserId(),$ignoredelete);
			$res = $this->db->query("select (select count(*) from stg.frlPerson) as \"Person\",(select count(*) from stg.frlPrivilege) as \"Privilege\"")->result('array');
			$this->RegisterListLog_model->setRegisterListLog_AllCount($res[0]["Person"]);
			$this->RegisterListLog_model->setRegisterListLog_UploadCount($res[0]["Privilege"]);
		}
	}


	/**
	 * @param $RegisterListLog_id
	 * @param null $words
	 * @param bool $ignoredelete
	 * @throws Exception
	 */
	private function importDbfFromMS1($RegisterListLog_id, $words = null, $ignoredelete = false) {
		$this->load->model('DbfImporter_model', 'DbfImporter_model');
		$recordCount = 0;
		$iii = 0;


		$this->DbfImporter_model->importFRL($words, $RegisterListLog_id, $this->getPmUserId(), $ignoredelete);


		$res = $this->db->query("
			select
				(select count(*) from stg.frlPerson) as \"Person\",
				(select count(*) from stg.frlPrivilege) as \"Privilege\"
		")->result('array');
		$this->RegisterListLog_model->setRegisterListLog_AllCount($res[0]["Person"]);
		$this->RegisterListLog_model->setRegisterListLog_UploadCount($res[0]["Privilege"]);

	}

	/**
	 * @param $data
	 * @return bool
	 */
	public function _insertPerson($data){
		// Добавление Person
		$sql = "
			select
				Person_id as \"Person_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from p_PersonAll_ins(
				PersonSurName_SurName := :fam,
				PersonFirName_FirName := :im,
				PersonSecName_SecName := :ot,
				PersonBirthDay_BirthDay := :person_birthday,
				Sex_id := :sex_id,
				PersonSnils_Snils := :Person_SNILS,
				Server_id := :Server_id,
				Person_id := :Person_id,
				Lgot_id := :frlPerson_id,
				pmUser_id := 1,
				isRebuildState := 1
			)
		";


		$query = $this->db->query($sql, $data);


		if( ! $query){
			return false;
		}

		//		Array
		//		(
		//			[id] => 59499607
		//			[Error_Code] =>
		//			[Error_Message] =>
		//		)
		$result = $query->row_array();

		if(empty($result)){
			return false;
		}


		return $result;
	}



	/**
	 *
	 * @param type $action
	 * @param type $filename
	 */
	public function importXml($action, $filename) {
		if (!is_file($filename)) {
			throw new Exception('Файл не найден - ' . $filename);
		}
		//гружу с глушением ошибок @ потому что высирает некритичную для нас нелепицу про схемы
		$sxml = @new SimpleXMLElement(file_get_contents($filename));
		if (!is_object($sxml)) {
			throw new Exception('Не удалось разобрать XML');
		}
		$cnt = 0;
		$cnt_doc = 0;
		$cnt_adr = 0;
		$cnt_rec = 0;
		$cnt_dip = 0;
		$cnt_ski = 0;
		$cnt_qua = 0;
		$cnt_cer = 0;
		$cnt_pos = 0;
		$cnt_ret = 0;
		$cnt_rew = 0;

		$q_del = <<<Q
		delete from	tmp._ERMP_Emplpyee;
		delete from	tmp._ERMP_Document;
		delete from	tmp._ERMP_Address;
		delete from	tmp._ERMP_WorkPlace;
		delete from	tmp._ERMP_SpecialityDiploma;
		delete from	tmp._ERMP_QualificationImprovementCourse;
		delete from	tmp._ERMP_QualificationCategory;
		delete from	tmp._ERMP_Certificate;
		delete from	tmp._ERMP_PostgraduateEducation;
		delete from	tmp._ERMP_RetrainingCourse;
		delete from	tmp._ERMP_Reward;
Q;

		$q = <<<Q
		select
			Emplpyee_id as "id",
			Error_Code as "Error_Code",
			Error_Message as "Error_Msg"
		from tmp.p__ERMP_Emplpyee_ins(
			Name := :Name,
			Surname := :Surname,
			Secname := :Secname,
			Sex_id := :Sex_id,
			Birthdate := :Birthdate,
			Deathdate := :Deathdate,
			Inn := :Inn,
			Snils := :Snils,
			Phone := :Phone,
			FamilyStatus_code := :FamilyStatus_code,
			Nationality_Code := :Nationality_Code,
			HasAuto := :HasAuto,
			HasChildren := :HasChildren,
			GUID := :GUID,
			Person_id := :Person_id,
			MedWorker_id  := :MedWorker_id,
			pmUser_id := :pmUser_id
		)
Q;
		$q_doc = <<<Q
		select
			_ERMP_Document_id as "id",
			Error_Code as "Error_Code",
			Error_Message as "Error_Msg"
		from tmp.p__ERMP_Document_ins(
			Employee_id := :Employee_id,
			DocumentType_Code := :DocumentType_Code,
			OrgDep_Name := :OrgDep_Name,
			Document_Ser := :Document_Ser,
			Document_Num := :Document_Num,
			Document_begDate := :Document_begDate,
			pmUser_id := :pmUser_id
		)
Q;
		$q_addr = <<<Q
		select
			_ERMP_Address_id as "id",
			Error_Code as "Error_Code",
			Error_Message as "Error_Msg"
		from tmp.p__ERMP_Address_ins(
			Employee_id := :Employee_id,
			District_code := :District_code,
			District_Name := :District_Name,
			District_Prefix := :District_Prefix,
			City_code := :City_code,
			City_Name := :City_Name,
			City_Prefix	:= :City_Prefix,
			Street := :Street,
			House := :House,
			Corpus := :Corpus,
			Apartment := :Apartment,
			RegistrationTypeName := :RegistrationTypeName,
			pmUser_id := :pmUser_id,
		)
Q;
		$q_rec = <<<Q
		select
			_ERMP_WorkPlace_id as "id",
			Error_Code as "Error_Code",
			Error_Message as "Error_Msg"
		from tmp.p__ERMP_WorkPlace_ins(
			Emplpyee_id := :Emplpyee_id,
			MilitaryRelation_id	= :MilitaryRelation_id,
			BeginDate := :BeginDate,
			EndDate	= :EndDate,
			ArriveOrderNumber := :ArriveOrderNumber,
			ArriveRecordType_id	:= :ArriveRecordType_id,
			LeaveOrderNumber := :LeaveOrderNumber,
			LeaveRecordType_id := :LeaveRecordType_id,
			FRMPSubdivision_id := :FRMPSubdivision_id,
			TabCode	= :TabCode,
			Population := :Population,
			Rate := :Rate,
			PostOccupationType_id := :PostOccupationType_i,
			FRMPPost_id	:= :FRMPPost_id,
			FRMPPost_name := :FRMPPost_name,
			PostKind_id	:= :PostKind_id,
			MedicalCareKind_id := :MedicalCareKind_id,
			LPU_name := :LPU_name,
			pmUser_id := :pmUser_id
		)
Q;
		$q_dip = <<<Q
		select
			_ERMP_SpecialityDiploma_id as "id",
			Error_Code as "Error_Code",
			Error_Message as "Error_Msg"
		from tmp.p__ERMP_SpecialityDiploma_ins(
			Emplpyee_id := :Emplpyee_id,
			YearOfGraduation := :YearOfGraduation,
			DiplomaNumber := :DiplomaNumber,
			DiplomaSeries := :DiplomaSeries,
			DiplomaSpeciality_id := :DiplomaSpeciality_id,
			EducationType_id := :EducationType_id,
			EducationInstitution_id := :EducationInstitution_id,
			pmUser_id := :pmUser_id
		)
Q;
		$q_ski = <<<Q
		select
			_ERMP_QualificationImprovementCourse_id as "id",
			Error_Code as "Error_Code",
			Error_Message as "Error_Msg"
		from tmp.p__ERMP_QualificationImprovementCourse_ins(
			Emplpyee_id := :Emplpyee_id,
			Year := :Year,
			DocumentRecieveDate := :DocumentRecieveDate,
			HoursCount := :HoursCount,
			DocumentNumber := :DocumentNumber,
			Round := :Round,
			Speciality_id := :Speciality_id,
			EducationInstitution_id := :EducationInstitution_id,
			pmUser_id := :pmUser_id
		)
Q;
		$q_qua = <<<Q
		select
			_ERMP_QualificationCategory_id as "id",
			Error_Code as "Error_Code",
			Error_Message as "Error_Msg"
		from tmp.p__ERMP_QualificationCategory_ins(
			Emplpyee_id := :Emplpyee_id  ,
			Category_code := :Category_code,
			Speciality_id := :Speciality_id,
			AssigmentDate := :AssigmentDate,
			pmUser_id := :pmUser_id
		)
Q;
		$q_cer = <<<Q
 		select
			_ERMP_Certificate_id as "id",
			Error_Code as "Error_Code",
			Error_Message as "Error_Msg"
		from tmp.p__ERMP_Certificate_ins(
			Emplpyee_id := :Emplpyee_id ,
			CertificateReceipDate := :CertificateReceipDate ,
			CertificateNumber := :CertificateNumber,
			CertificateSeries := :CertificateSeries,
			Speciality_id := :Speciality_id,
			EducationInstitution_id := :EducationInstitution_id,
			pmUser_id := :pmUser_id
		)
Q;
		$q_pos = <<<Q
		select
			_ERMP_PostgraduateEducation_id as "id",
			Error_Code as "Error_Code",
			Error_Message as "Error_Msg"
		from tmp.p__ERMP_PostgraduateEducation_ins(
			Emplpyee_id	:= :Emplpyee_id,
			graduationDat := :graduationDate,
			startDate := :startDate,
			DiplomaNumbe := :DiplomaNumber,
			endDate := :endDate,
			DiplomaSeries := :DiplomaSeries,
			PostgraduateEducationType_id := :PostgraduateEducationType_id,
			AcademicMedicalDegree_id := :AcademicMedicalDegree_id,
			Speciality_id := :Speciality_id,
			EducationInstitution_id	= :EducationInstitution_id,
			pmUser_id := :pmUser_id
		)
Q;
		$q_ret = <<<Q
		select
			_ERMP_RetrainingCourse_id as "id",
			Error_Code as "Error_Code",
			Error_Message as "Error_Msg"
		from tmp.p__ERMP_RetrainingCourse_ins(
			Emplpyee_id := :Emplpyee_id,
			PassYear := :PassYear,
			HoursCount := :HoursCount,
			DocumentNumber := :DocumentNumber,
			DocumentSeries := :DocumentSeries,
			Speciality_id := :Speciality_id,
			EducationInstitution_id := :EducationInstitution_id,
			pmUser_id := :pmUser_id
		)
Q;
		$q_rew = <<<Q
		select
			_ERMP_Reward_id as "id",
			Error_Code as "Error_Code",
			Error_Message as "Error_Msg"
		from tmp.p__ERMP_Reward_ins(
			Emplpyee_id := :Emplpyee_id,
			date := :date,
			name := :name,
			number := :number,
			pmUser_id := :pmUser_id
		)
Q;
		$this->db->query($q_del);
		foreach ($sxml as $s) {

			/** @var $s SimpleXMLElement */
			if ('Employee' == $s->getName()) {
				//=============Employee=============
				$p = array(
					'Name' => toAnsi((string) $s->Name),
					'Surname' => toAnsi((string) $s->Surname),
					'Secname' => toAnsi((string) $s->Patroname),
					'Sex_id' => strtolower((string) $s->Sex) == 'female' ? 2 : 1,
					'Birthdate' => strlen((string) $s->Birthdate) ? (string) $s->Birthdate : null,
					'Deathdate' => strlen((string) $s->Deathdate) ? (string) $s->Deathdate : null,
					'Inn' => (string) $s->INN,
					'Snils' => (string) $s->SNILS,
					'Phone' => (string) $s->Phone,
					'FamilyStatus_code' => is_object($s->MarriageState) ? (string) $s->MarriageState->ID : '',
					'Nationality_Code' => is_object($s->CitezenshipState) ? (string) $s->CitezenshipState->ID : '',
					'HasAuto' => strtolower((string) $s->HasAuto) == 'false' ? 0 : 1,
					'HasChildren' => strtolower((string) $s->HasChildren) == 'false' ? 0 : 1,
					'GUID' => (string) $s->ID,
					'Person_id' => NULL,
					'MedWorker_id' => NULL,
					'pmUser_id' => 1,
					'_ERMP_Emplpyee_insDT' => (string) $s->_ERMP_Emplpyee_insDT,
					'_ERMP_Emplpyee_updDT' => (string) $s->_ERMP_Emplpyee_updDT,
				);
				$res = $this->getFirstRowFromQuery($q, $p);
				if (empty($res['id']) || !empty($res['Error_Message'])) {
					throw new Exception('Не удалось сохранить запись в таблицу.' . getDebugSQL($q, $p));
				}

				$cnt++;
				//=============Document=============
				$p_doc = array(
					'Employee_id' => $res['id'],
					'DocumentType_Code' => (string) $s->Document->Type->ID,
					'OrgDep_Name' => toAnsi((string) $s->Document->Issued),
					'Document_Ser' => (string) $s->Document->Serie,
					'Document_Num' => (string) $s->Document->Number,
					'Document_begDate' => strlen((string) $s->Document->IssueDate) ? (string) $s->Document->IssueDate : null,
					'pmUser_id' => 1
				);
				$res_doc = $this->getFirstRowFromQuery($q_doc, $p_doc);
				if (!empty($res_doc['Error_Message'])) {
					throw new Exception('Не удалось сохранить запись в таблицу.' . getDebugSQL($q_doc, $p_doc));
				}
				$cnt_doc++;
				//=============Address=============
				foreach ($s->Addresses->AddressEntity as $addr) {
					$p_addr = array(
						'Employee_id' => $res['id'],
						'District_code' => (string) $addr->District->ID,
						'District_Name' => toAnsi((string) $addr->District->Name),
						'District_Prefix' => toAnsi((string) $addr->District->Prefix),
						'City_code' => toAnsi((string) $addr->City->ID),
						'City_Name' => toAnsi((string) $addr->City->Name),
						'City_Prefix' => toAnsi((string) $addr->City->Prefix),
						'Street' => toAnsi((string) $addr->Street),
						'House' => toAnsi((string) $addr->House),
						'Corpus' => null,
						'Apartment' => toAnsi((string) $addr->Apartment),
						'RegistrationTypeName' => toAnsi((string) $addr->Registration->Name),
						'pmUser_id' => 1,
					);
					$res_addr = $this->getFirstRowFromQuery($q_addr, $p_addr);
					if (!empty($res_addr['Error_Message'])) {
						throw new Exception('Не удалось сохранить запись в таблицу.' . getDebugSQL($q_addr, $p_addr));
					}
					$cnt_adr++;
				}
				//=============EmployeeRecords=============

				foreach ($s->EmployeeRecords->CardRecord as $rec) {
					$p_rec = array(
						'Emplpyee_id' => $res['id'],
						'MilitaryRelation_id' => toAnsi((string) $rec->RecordMilitary->ID),
						'BeginDate' => toAnsi((string) $rec->DateBegin),
						'EndDate' => (toAnsi((string) $rec->DateEnd)) ? toAnsi((string) $rec->DateEnd) : null,
						'ArriveOrderNumber' => toAnsi((string) $rec->OrderIn),
						'ArriveRecordType_id' => toAnsi((string) $rec->TypeIn->ID),
						'LeaveOrderNumber' => toAnsi((string) $rec->OrderOut),
						'LeaveRecordType_id' => toAnsi((string) $rec->TypeOut->ID),
						'FRMPSubdivision_id' => toAnsi((string) $rec->RecordSubdivision->ID),
						'TabCode' => toAnsi((string) $s->TabelNumber),
						'Population' => toAnsi((string) $rec->Population),
						'Rate' => toAnsi((string) $rec->Wage),
						'PostOccupationType_id' => toAnsi((string) $rec->RecordPositionType->ID),
						'FRMPPost_id' => toAnsi((string) $rec->RecordPost->ID),
						'FRMPPost_name' => toAnsi((string) $rec->RecordPost->Name),
						'PostKind_id' => toAnsi((string) $rec->RecrodPosition->ID),
						'MedicalCareKind_id' => toAnsi((string) $rec->Care->ID),
						'LPU_name' => toAnsi((string) $s->UZ->Name),
						'pmUser_id' => 1,
					);

					$res_rec = $this->getFirstRowFromQuery($q_rec, $p_rec);
					if (!empty($res_rec['Error_Message'])) {
						throw new Exception('Не удалось сохранить запись в таблицу.' . getDebugSQL($q_rec, $p_rec));
					}
					$cnt_rec++;
				}
				//=============EmployeeSpecialities=============
				foreach ($s->EmployeeSpecialities->DiplomaEducation as $dip) {
					$p_dip = array(
						'Emplpyee_id' => $res['id'],
						'YearOfGraduation' => toAnsi((string) $dip->GraduationDate),
						'DiplomaNumber' => toAnsi((string) $dip->DiplomaNumber),
						'DiplomaSeries' => toAnsi((string) $dip->DiplomaSerie),
						'DiplomaSpeciality_id' => toAnsi((string) $dip->GraduationSpeciality->ID),
						'EducationType_id' => toAnsi((string) $dip->Type->ID),
						'EducationInstitution_id' => toAnsi((string) $dip->GraduatedFrom->ID),
						'pmUser_id' => 1,
					);
					$res_dip = $this->getFirstRowFromQuery($q_dip, $p_dip);
					if (!empty($res_dip['Error_Message'])) {
						throw new Exception('Не удалось сохранить запись в таблицу.' . getDebugSQL($q_dip, $p_dip));
					}
					$cnt_dip++;
				}
				//=============EmployeeSkillImprovement=============
				foreach ($s->EmployeeSkillImprovement->SkillImprovement as $ski) {
					$p_ski = array(
						'Emplpyee_id' => $res['id'],
						'Year' => toAnsi((string) $ski->Year),
						'DocumentRecieveDate' => toAnsi((string) $ski->IssueDate),
						'HoursCount' => toAnsi((string) $ski->Hours),
						'DocumentNumber' => toAnsi((string) $ski->DiplomaNumber),
						'Round' => toAnsi((string) $ski->Cycle),
						'Speciality_id' => toAnsi((string) $ski->EducationSpeciality->ID),
						'EducationInstitution_id' => toAnsi((string) $ski->Organisation->ID),
						'pmUser_id' => 1,
					);
					$res_ski = $this->getFirstRowFromQuery($q_ski, $p_ski);
					if (!empty($res_ski['Error_Message'])) {
						throw new Exception('Не удалось сохранить запись в таблицу.' . getDebugSQL($q_ski, $p_ski));
					}
					$cnt_ski++;
				}
				//=============EmployeeQualification=============
				foreach ($s->EmployeeQualification->Qualification as $qua) {
					if (!empty($qua)) {
						$p_qua = array(
							'Emplpyee_id' => $res['id'],
							'Category_code' => toAnsi((string) $qua->Category->ID),
							'Speciality_id' => toAnsi((string) $qua->Speciality->ID),
							'AssigmentDate' => toAnsi((string) $qua->Year),
							'pmUser_id' => 1,
						);
						$res_qua = $this->getFirstRowFromQuery($q_qua, $p_qua);
						if (!empty($res_qua['Error_Message'])) {
							throw new Exception('Не удалось сохранить запись в таблицу.' . getDebugSQL($q_qua, $p_qua));
						}
						$cnt_qua++;
					}
				}
				//=============EmployeeSertificateEducation=============
				foreach ($s->EmployeeSertificateEducation->SertificateEducation as $cer) {
					if (!empty($cer)) {
						$p_cer = array(
							'Emplpyee_id' => $res['id'],
							'CertificateReceipDate' => toAnsi((string) $cer->IssueDate),
							'CertificateNumber' => toAnsi((string) $cer->SertificateNumber),
							'CertificateSeries' => toAnsi((string) $cer->SertificateSerie),
							'Speciality_id' => toAnsi((string) $cer->EducationSpeciality->ID),
							'EducationInstitution_id' => toAnsi((string) $cer->IssueOrg->ID),
							'pmUser_id' => 1,
						);
						$res_cer = $this->getFirstRowFromQuery($q_cer, $p_cer);
						if (!empty($res_cer['Error_Message'])) {
							throw new Exception('Не удалось сохранить запись в таблицу.' . getDebugSQL($q_cer, $p_cer));
						}
						$cnt_cer++;
					}
				}
				//=============EmployeePostGraduateEducation=============
				foreach ($s->EmployeePostGraduateEducation->PostGraduateEducation as $pos) {
					if (!empty($pos)) {
						$p_pos = array(
							'Emplpyee_id' => $res['id'],
							'graduationDate' => toAnsi((string) $pos->DateDocum),
							'startDate' => toAnsi((string) $pos->DateBegin),
							'DiplomaNumber' => toAnsi((string) $pos->DiplomaNumber),
							'endDate' => toAnsi((string) $pos->DateEnd),
							'DiplomaSeries' => toAnsi((string) $pos->DiplomaSerie),
							'PostgraduateEducationType_id' => toAnsi((string) $pos->Type->ID),
							'AcademicMedicalDegree_id' => toAnsi((string) $pos->Degree->ID),
							'Speciality_id' => toAnsi((string) $pos->PostGraduationSpeciality->ID),
							'EducationInstitution_id' => toAnsi((string) $pos->BaseOrg->ID),
							'pmUser_id' => 1,
						);
						$res_pos = $this->getFirstRowFromQuery($q_pos, $p_pos);
						if (!empty($res_pos['Error_Message'])) {
							throw new Exception('Не удалось сохранить запись в таблицу.' . getDebugSQL($q_pos, $p_pos));
						}
						$cnt_pos++;
					}
				}
				//=============EmployeeRetrainment=============
				foreach ($s->EmployeeRetrainment->Retrainment as $ret) {
					if (!empty($ret)) {
						$p_ret = array(
							'Emplpyee_id' => $res['id'],
							'PassYear' => toAnsi((string) $ret->TrainingDate),
							'HoursCount' => toAnsi((string) $ret->Hours),
							'DocumentNumber' => toAnsi((string) $ret->DiplomaNumber),
							'DocumentSeries' => toAnsi((string) $ret->DiplomaSerie),
							'Speciality_id' => toAnsi((string) $ret->EducationSpeciality->ID),
							'EducationInstitution_id' => toAnsi((string) $ret->Organisation->ID),
							'pmUser_id' => 1,
						);
						$res_ret = $this->getFirstRowFromQuery($q_ret, $p_ret);
						if (!empty($res_ret['Error_Message'])) {
							throw new Exception('Не удалось сохранить запись в таблицу.' . getDebugSQL($q_ret, $p_ret));
						}
						$cnt_ret++;
					}
				}
				//=============EmployeeAwards=============
				foreach ($s->EmployeeAwards->Award as $rew) {
					if (!empty($rew)) {
						$p_rew = array(
							'Emplpyee_id' => $res['id'],
							'date' => toAnsi((string) $rew->Issued),
							'name' => toAnsi((string) $rew->Name),
							'number' => toAnsi((string) $rew->Number),
							'pmUser_id' => 1
						);
						$res_rew = $this->getFirstRowFromQuery($q_rew, $p_rew);
						if (!empty($res_rew['Error_Message'])) {
							throw new Exception('Не удалось сохранить запись в таблицу.' . getDebugSQL($q_rew, $p_rew));
						}
						$cnt_rew++;
					}
				}
			}
		}
		RegisterListDetailLog_model::createLogMessage(new DateTime(), 1, "Разбор XML файла завершен. Записей Employee: $cnt" . PHP_EOL .
			"Записей Document: $cnt_doc" . PHP_EOL .
			"Записей Address: $cnt_adr" . PHP_EOL .
			"Записей EmployeeRecords: $cnt_rec" . PHP_EOL .
			"Записей EmployeeSpecialities: $cnt_dip" . PHP_EOL .
			"Записей EmployeeSkillImprovement: $cnt_ski" . PHP_EOL .
			"Записей EmployeeQualification: $cnt_qua" . PHP_EOL .
			"Записей EmployeeSertificateEducation: $cnt_cer" . PHP_EOL .
			"Записей EmployeePostGraduateEducation: $cnt_pos" . PHP_EOL .
			"Записей EmployeeRetrainment: $cnt_ret" . PHP_EOL .
			"Записей EmployeeAwards: $cnt_rew" . PHP_EOL, $this->RegisterListLog_model->getRegisterListLog_id(), $this->getPmUserId()
		);
	}

	/**
	 *
	 * @param type $action
	 * @param type $inputFolder
	 * @param string $fields_mapping
	 */
	public function importDead($action, $inputFolder, $fields_mapping = array()) {
		foreach ($action['files'] as $filename => $dbfFile) {
			if (is_file($inputFolder . $filename)) {
				$dbf = dbase_open($inputFolder . $filename, 0);
				$dbf_header = dbase_get_header_info($dbf);
				$ddl = array();
				$conv = array();
				if (0 == count($fields_mapping)) {
					$fields_mapping_empty = true;
				} else {
					$fields_mapping_empty = false;
				}
				foreach ($dbf_header as $dbf_field) {
					switch ($dbf_field['type']) {
						case 'character':
							$dbf_field['type'] = 'VARCHAR(' . $dbf_field['length'] . ')';
							$conv[] = $dbf_field['name'];
							break;
						default:
							$dbf_field['type'] = 'VARCHAR(4000)';
							break;
					}
					$ddl[] = '[' . $dbf_field['name'] . '] ' . $dbf_field['type'];
					if ($fields_mapping_empty) {
						$fields_mapping[$dbf_field['name']] = $dbf_field['name'];
					}
				}
				$this->recordCount = dbase_numrecords($dbf);
				for ($i = 1; $i <= $this->recordCount; $i++) {
					$row = dbase_get_record_with_names($dbf, $i);
					$values = array();
					foreach ($conv as $f_idx) {
						$row[$f_idx] = iconv('cp866', 'UTF-8', $row[$f_idx]);
					}
					foreach ($fields_mapping as $source_field => $destination_field) {
						$values[$source_field] = $row[$source_field];
					}
					$this->PersonKill($values);
				}
				dbase_close($dbf);
			}
		}
	}

	/**
	 *
	 * @param type $values
	 */
	public function PersonKill($values) {
		$q = "
		SELECT
			Person_id as \"Person_id\",
			Server_pid as \"Server_pid\",
			Person_IsInErz as \"Person_IsInErz\",
			Server_id as \"Server_id\",
			PersonEvn_id as \"PersonEvn_id\",
			Person_SurName as \"Person_SurName\",
			Person_FirName as \"Person_FirName\",
			Person_SecName as \"Person_SecName\",
			Person_SurNameR as \"Person_SurNameR\",
			Person_FirNameR as \"Person_FirNameR\",
			Person_SecNameR as \"Person_SecNameR\",
			Person_BirthDay as \"Person_BirthDay\",
			Sex_id as \"Sex_id\",
			Person_Snils as \"Person_Snils\",
			SocStatus_id as \"SocStatus_id\",
			Polis_id as \"Polis_id\",
			Polis_Ser as \"Polis_Ser\",
			Polis_Num as \"Polis_Num\",
			PolisType_id as \"PolisType_id\",
			Document_id as \"Document_id\",
			Document_Ser as \"Document_Ser\",
			Document_Num as \"Document_Num\",
			UAddress_id as \"UAddress_id\",
			PAddress_id as \"PAddress_id\",
			Job_id as \"Job_id\",
			Person_EdNum as \"Person_EdNum\",
			Person_Phone as \"Person_Phone\",
			Person_Inn as \"Person_Inn\",
			PersonSocCardNum_id as \"PersonSocCardNum_id\",
			Person_SocCardNum as \"Person_SocCardNum\",
			Lpu_id as \"Lpu_id\",
			PersonFamilyStatus_id as \"PersonFamilyStatus_id\",
			FamilyStatus_id as \"FamilyStatus_id\",
			PersonFamilyStatus_IsMarried as \"PersonFamilyStatus_IsMarried\",
			Person_IsUnknown as \"Person_IsUnknown\",
			Person_IsDead as \"Person_IsDead\",
			PersonCloseCause_id as \"PersonCloseCause_id\",
			Person_deadDT as \"Person_deadDT\",
			Person_closeDT as \"Person_closeDT\",
			PersonIdentState_id as \"PersonIdentState_id\",
			Person_identDT as \"Person_identDT\",
			Person_IsEncrypHIV as \"Person_IsEncrypHIV\",
			PersonPhone_Comment as \"PersonPhone_Comment\",
			PersonNationalityStatus_id as \"PersonNationalityStatus_id\",
			NationalityStatus_id as \"NationalityStatus_id\",
			KLCountry_id as \"KLCountry_id\",
			NationalityStatus_IsTwoNation as \"NationalityStatus_IsTwoNation\",
			LegalStatusVZN_id as \"LegalStatusVZN_id\",
			Person_IsNotINN as \"Person_IsNotINN\",
			pmUser_insID as \"pmUser_insID\",
			pmUser_updID as \"pmUser_updID\",
			PersonState_insDT as \"PersonState_insDT\",
			PersonState_updDT as \"PersonState_updDT\"
		FROM dbo.v_PersonState
		where Person_FirName=:firname
			and Person_SecName=:secname
			and Person_SurName =:surname
			and Person_BirthDay = :birthday
		";

		$p = array(
			"surname" => $values["FAMPEOP"],
			"firname" => $values["NAMEPEOP"],
			"secname" => $values["OTCHPEOP"],
			"birthday" => date("Y-m-d", strtotime($values["DATER"]))
		);
		$result = $this->db->query($q, $p)->result('array');
		if (sizeof($result) == 1) {
			$q = "
				with mv as (
					select
						case when (dbo.getregion() = 10)
							then :Person_deadDT
							else :Person_closeDT
						end as closeDT
				)

				select
					Error_Code as \"Error_Code\",
					Error_Message as \"Error_Msg\"
				from dbo.p_Person_kill(
					Person_id := :Person_id,
					PersonCloseCause_id := :PersonCloseCause_id,
					Person_deadDT := :Person_deadDT,
					Person_closeDT := (select closeDT from mv),
					pmUser_id := :pmUser_id
				)
			";
			if ($values["DATESM"] == null)
				$values["DATESM"] = $values["DATESM1"];
			$p = array(
				'Person_id' => $result[0]["Person_id"],
				'PersonCloseCause_id' => 1,
				'Person_deadDT' => date("Y-m-d", strtotime($values["DATESM"])),
				'Person_closeDT' => date("Y-m-d", strtotime($values["DATESM1"])),
				'pmUser_id' => $this->getPmUserId()
			);
			$res = $this->getFirstRowFromQuery($q, $p);
			if (!empty($res['Error_Message'])) {
				RegisterListDetailLog_model::createLogMessage(new DateTime(), 1, 'Не удалось сохранить запись в таблицу.' . $res['Error_Message'], $this->RegisterListLog_model->getRegisterListLog_id(), $this->getPmUserId());
				$this->Err++;
			} else {
				$this->load->library('swPersonRegister');
				swPersonRegister::onPersonDead($p);
			}
		} else if (sizeof($result) > 1) {
			RegisterListDetailLog_model::createLogMessage(new DateTime(), 1, 'Найдено ' . sizeof($result) . ' записи(ей) с параметрами:
						Фамилия - ' . $values["FAMPEOP"] . "\n
						Имя - " . $values["NAMEPEOP"] . "\n
						Отчество - " . $values["OTCHPEOP"] . "\n
						Дата Рождения - " . $values["DATER"] . "\n", $this->RegisterListLog_model->getRegisterListLog_id(), $this->getPmUserId());
			$this->Err++;
		} else {
			RegisterListDetailLog_model::createLogMessage(new DateTime(), 1, 'Не найдено записей с параметрами:
						Фамилия - ' . $values["FAMPEOP"] . "\n
						Имя - " . $values["NAMEPEOP"] . "\n
						Отчество - " . $values["OTCHPEOP"] . "\n
						Дата Рождения - " . $values["DATER"] . "\n", $this->RegisterListLog_model->getRegisterListLog_id(), $this->getPmUserId());
			$this->Err++;
		}
	}

	/**
	 *
	 * @param type $action
	 * @param type $inputFolder
	 */
	public function importFRMP($action, $inputFolder) {//Справочник ФРМП
		foreach ($action['files'] as $filename => $dbfFile) {
			if (strpos($dbfFile['table'], "FRMP")) {
				$level = "
					select
						Error_Code as \"Error_Code\",
						Error_Message as \"Error_Msg\"
					from persis.{$dbfFile['table']}(
						xml := :xml
					)
				";
			} else {
				$level = "
					select
						Error_Code as \"Error_Code\",
						Error_Message as \"Error_Msg\"
					from persis.{$dbfFile['table']}(
						xml := :xml,
						pmUser_id := :pmUser_id
					)
				";
			}

			$dbfFile['name'] = iconv('', 'utf-8', $dbfFile['name']);
			if (is_file($inputFolder . $filename)) {
				$xml = file_get_contents($inputFolder . $filename);
				$xml = substr($xml, strpos($xml, "<" . $dbfFile['name'] . ">"));
				$xml = str_replace("</ArrayOf" . $dbfFile['name'] . ">", "", $xml);
				$xml = str_replace('<Parent xsi:nil="true" />', "<Parent></Parent>", $xml);
				$xml = str_replace('<KLADR xsi:nil="true" />', "<KLADR></KLADR>", $xml);
				$xml = str_replace('<Order xsi:nil="true" />', "<Order></Order>", $xml);
				$p = array(
					'xml' => toAnsi((string) $xml),
					'pmUser_id' => $this->getPmUserId()
				);
				$res = $this->getFirstRowFromQuery($level, $p);

				if (!empty($res['Error_Message'])) {
					throw new Exception('Не удалось сохранить запись в таблицу.' . $res['Error_Message']);
				}
			}
		}
	}

	/**
	 *
	 * @return type
	 */
	public function getStatus() {
		$result = array();
		$this->setSessionActive(true);
		if (isset($_SESSION[self::SESSION_VARNAME])) {
			$result = $_SESSION[self::SESSION_VARNAME];
		}
		$this->setSessionActive(false);
		return $result;
	}

	/**
	 * @param $dataQuery
	 * @param $RegisterListLog_id
	 * @throws Exception
	 */
	private function doQuery($dataQuery, $RegisterListLog_id){
		if (!isset($dataQuery['region'])) {
			$dataQuery['region'] = $this->getRegionNick();
		}
		if (!isset($dataQuery['ver'])) {
			$dataQuery['ver'] = $this->getVersion();
		}

		$checkRegion = false;
		if(is_array($dataQuery['region'])){
			$checkRegion = (in_array($this->getRegionNick(),$dataQuery['region']))?true:false;
		} else {
			$checkRegion = ($dataQuery['region'] == $this->getRegionNick())?true:false;
		}


		$except_region = isset($dataQuery['except_region'])?$dataQuery['except_region']:array();
		if (count($except_region) > 0 && in_array($this->getRegionNick(),$dataQuery['except_region'])) {
			$checkRegion = false;
		}


		if($checkRegion && $dataQuery['ver'] == $this->getVersion()){

			$params = array(
				'RegisterListLog_id' => $RegisterListLog_id,
				'PersonPrivilege_insDT1' => '2013-02-13 09:17:16.410',
				'PersonPrivilege_insDT2' => '2013-02-11 10:26:55.927',
				'myTime' => new Datetime(),
			);

			$dataQuery['sql'] = "set nocount on; ".$dataQuery['sql'];


			$res = $this->db->query($dataQuery['sql'], $params);


			if (true != $res) {
				throw new Exception('Ошибка при выполнении запроса "' . $dataQuery['title'] . '"');
			}

			if (isset($dataQuery['log_affected_rows'])) {

				if ( ! empty($this->db->result_id)) {
					$rows_affected = sqlsrv_rows_affected($this->db->result_id);
				} else {
					$rows_affected = 0;
				}

				RegisterListDetailLog_model::createLogMessage(
					new DateTime(), 1, $dataQuery['title'] . '. Количество обработанных записей: ' . $rows_affected, $this->RegisterListLog_model->getRegisterListLog_id(), $this->getPmUserId()
				);
			}
			if (isset($dataQuery['callback'])){
				$dataQuery['callback']($this);
			}
		}
	}

	/**
	 * @param array $action
	 * @throws Exception
	 */
	private function doQueries($action) {
		foreach($action['queries'] as $q) {

			$RegisterListLog_id = $this->RegisterListLog_model->getRegisterListLog_id();

			$this->doQuery($q, $RegisterListLog_id);
		}
	}

	/**
	 *
	 * @param type $data
	 * @return string
	 */
	public function createPersonDBF($data){
		$fullFields='
			LEFT(pers.fam,1) as "FAM",
			pers.im as "IM",
			pers.ot as "OT",
			pers.w as "W",
			pers.dr as "DR",
			NULL as "COUNT_R",
			NULL as "C_FP",'
		;
		if($data['full']){
			$fullFields='
	pers.fam as "FAM",
	pers.im as "IM",
	pers.ot as "OT",
	pers.w as "W",
	pers.dr as "DR",
	NULL as "COUNT_R",
	NULL as "C_FP",
	pers.c_doc as "C_P",
	DT.DocumentType_Name as "NAME_P",
	pers.s_doc as "SER_P",
	pers.n_doc as "NUMB_P",
	pers.dt_doc as "DATE_VP",
	pers.o_doc as "NAME_VP",
	pers.adres as "ADRES_R",
	pers.adr_fact as "ADRES_F",';
		}
		$query="
			select
				pers.frlPerson_id as \"NPP\",
				NULL as \"RC_MED\",
				NULL as \"RN_MED\",
				pers.ss as \"SS\",
				NULL as \"SN_POL\",
				pers.frlPerson_CodeReg as \"C_SUB\",
				pers.frlPerson_CodeArea as \"C_REG\",
				".$fullFields."
				NULL as \"K_KATL\",
				NULL as \"OKATO_OMS\",
				NULL as \"QM_OGRN\",
				NULL as \"OKATO_EDV\",
				NULL as \"QL_OGRN\",
				pers.frlPerson_ChangeCode as \"KOD_IZM\",
				NULL as \"MSG_TEXT\",
				pers.frlPerson_KolvoGSP as \"LKN1\",
				pers.c_kat1 as \"LKN2\",
				pers.adr_type as \"MESTO_PR\",
				case when pers.frlPerson_IsRefuse = '1' then 'Д' else 'Н' end as \"PR_POL_1\",
				case when pers.frlPerson_IsRefuse2 = '1' then 'Д' else 'Н' end as \"PR_POL_2\",
				case when pers.frlPerson_IsRefuse3 = '1' then 'Д' else 'Н' end as \"PR_POL_3\",
				pers.db_edv as \"D_B_FR\",
				pers.de_edv as \"D_E_FR\",
				pers.date_rsb as \"D_B_RS\",
				pers.date_rse as \"D_E_RS\",
				pers.frlPerson_Rezerv as \"RESERV\",
				case when pers.frlPerson_isRefuseNext = '1' then 'Д' else 'Н' end as \"GOD_OLD\",
				case when pers.frlPerson_IsRefuseNext2 = '1' then 'Д' else 'Н' end as \"GOD_NEW\",
				case when pers.frlPerson_IsRefuseNext3 = '1' then 'Д' else 'Н' end as \"GOD_OLD3\",
				pers.frlPerson_Phone as \"PHONE\"
			from stg.frlPerson pers
			left join v_DocumentType DT on DT.DocumentType_id=pers.c_doc		
		";
		$defs = array();
		$p = array(

		);
		$res = $this->db->query($query, $p);
		//$result = $query->result('array');

		$defs = array(
			array("NPP","N",19,0),//ID
			array("RC_MED","N",19,0),//NULL
			array("RN_MED","N",19,0),//NULL
			array("SS","C",14),//SS
			array("SN_POL","C",25),//sn_pol
			array("C_SUB","C",3),//frlPerson_CodeReg
			array("C_REG","C",3),//frlPerson_CodeArea
			array("FAM","C",($data['full'])?40:1),//fam
			array("IM","C",40),//im
			array("OT","C",40),//ot
			array("W","C",1),//w
			array("DR","C",10),//dr
			array("COUNT_R","C",40),//NULL
			array("C_FP","C",1),//NULL
		);
		if($data['full']){
			$dop = array(
				array("C_P","C",2),//C_doc
				array("NAME_P","C",80),//select DocumentType_Name from v_DocumentType
				array("SER_P","C",8),//s_doc
				array("NUMB_P","C",8),//n_doc
				array("DATE_VP","C",10),//dt_doc
				array("NAME_VP","C",80),//o_doc
				array("ADRES_R","C",200),//adres
				array("ADRES_F","C",200)//adr_fact
			);
			$defs = array_merge($defs,$dop);
		}
		$dop = array(
			array("K_KATL","C",3),//NULL
			array("OKATO_OMS","C",5),//NULL
			array("QM_OGRN","C",15),//NULL
			array("OKATO_EDV","C",5),//NULL
			array("QL_OGRN","C",15),//NULL
			array("KOD_IZM","C",2),//Privilege_ChangeCode
			array("MSG_TEXT","C",100),//NULL
			array("LKN1","C",3),//frlPerson_KolvoGSP
			array("LKN2","C",3),//c_kat1
			array("MESTO_PR","C",1),//adr_type
			array("PR_POL_1","C",1),//frlPerson_IsRefuse
			array("PR_POL_2","C",1),//frlPerson_IsRefuse2
			array("PR_POL_3","C",1),//frlPerson_IsRefuse3
			array("D_B_FR","C",10),//db_edv
			array("D_E_FR","C",10),//de_edv
			array("D_B_RS","C",10),//date_rsb
			array("D_E_RS","C",10),//date_rse
			array("RESERV","C",30),//frlPerson_Rezerv
			array("GOD_OLD","C",1),//frlPerson_isRefuseNext
			array("GOD_NEW","C",1),//frlPerson_IsRefuseNext2
			array("GOD_OLD3","C",1),//frlPerson_IsRefuseNext3
			array("PHONE","C",20)//frlPerson_Phone
		);
		$defs = array_merge($defs,$dop);
		$fname = substr(md5(time()), 0, 5);
		$dbf = "export/" . $fname . 'p.dbf';
		$sd = dbase_create($dbf, $defs);

		//$res->_data_seek(0);
		while ($row = $res->_fetch_assoc()) {
			$results = array();
			$row['LKN1']= str_pad($row['LKN1'], 3, "0", STR_PAD_LEFT);
			foreach ($row as $val) {
				$results[] = iconv('UTF-8', 'cp1251', $val);
			}
			dbase_add_record($sd, $results);
		}
		dbase_close($sd);
		return  $dbf;
	}
	/**
	 *
	 * @param type $data
	 * @return string
	 */
	public function createPrivelegeDBF($data){
		$query = "
			select 
				priv.frlPrivilege_id as \"NPP\",
				NULL as \"NPP1\",
				priv.ss as \"SS\",
				priv.c_katl as \"C_KATL\",
				priv.name_dl as \"NAME_DL\",
				priv.s_dl as \"SER_DL\",
				priv.n_dl as \"NUMB_DL\",
				priv.frlPrivilege_Org as \"NAME_VD\",
				priv.sn_dl as \"DATE_VD\",
				priv.date_bl as \"DATE_BL\",
				case when priv.date_el='2030/01/01' then NULL else priv.date_el end  as \"DATE_EL\",
				NULL as \"PR_VIBOR\",
				NULL as \"RESERV\"
			from stg.frlPrivilege priv
		";
		$defs = array();
		$p = array(

		);
		$res = $this->db->query($query, $p);
		//$result = $query->result('array');

		$defs = array(
			array("NPP","N",19,0),//ID
			array("NPP1","N",19,0),// null
			array("SS","C",14),//ss
			array("C_KATL","C",3),//c_katl
			array("NAME_DL","C",80),//name_dl
			array("SER_DL","C",8),//s_dl
			array("NUMB_DL","C",10),//n_dl
			array("NAME_VD","C",80),//frlPrivilege_Org
			array("DATE_VD","C",10),//sn_dl
			array("DATE_BL","C",10),//date_bl
			array("DATE_EL","C",10),//date_el
			array("PR_VIBOR","C",1),//null
			array("RESERV","C",10)//null
		);
		$fname = substr(md5(time()), 0, 5);
		$dbf = "export/" . $fname . 'l.dbf';
		$sd = dbase_create($dbf, $defs);

		//$res->_data_seek(0);
		while ($row = $res->_fetch_assoc()) {
			$results = array();
			foreach ($row as $val) {
				$results[] = iconv('UTF-8', 'cp1251', $val);
			}

			dbase_add_record($sd, $results);
		}
		dbase_close($sd);
		return  $dbf;
	}
	/**
	 *
	 * @param type $data
	 * @return type
	 */
	public function createDBF($data) {
		$select = "
			SELECT /*ФИО*/
				WP.Person_SurName as \"FAM\",
				WP.Person_FirName as \"IM\",
				WP.Person_SecName as \"OTCH\",
				to_char(WP.Person_BirthDay, 'yyyymmdd') as \"BD\",
				P.name as \"POST\",/*Должность*/
				WPP.QualificationLevel as \"QUAL_LVL\",/*Квалификационный уровень*/
				WPP.OfficialSalary as \"OFF_SAL\",/*Должностной оклад*/
				ST.LeadershipBonusPercent / 100 as \"LDR_COEF\",/*Коэффициент за руководство*/
				(ST.LeadershipBonusPercent / 100 * WPP.OfficialSalary) as \"LDR_SUM\",/*Надбавка за руководство*/
				null as \"RNK_COEF\",/*Коэффициент за звание*/
				null as \"RNL_SUM\",/*Надбавка за звание*/
				WPP.OfficialSalary * (1 + ST.LeadershipBonusPercent / 100) as \"SALARY\",/*Оклад с учетом руководства и звания*/
				ST.SalaryReductionPercent as \"SAL_RED\",/*Процент уменьшения оклада*/
				ST.IsVillageBonus as \"VIL_BNS\",/*Должностной оклад с учетом повышения за работу на селе*/
				WP.rate as \"RATE\",/*Ставка*/
				(WP.rate * WPP.OfficialSalary * (1 + ST.LeadershipBonusPercent / 100 - ST.SalaryReductionPercent / 100)) as \"RATE_SAL\",/*Итого должностной оклад с учетом ставки*/
				null as \"COMP_SAL\",/*Сумма компенсационных выплат*/
				null as \"STG\",/*Стаж*/
				null as \"STG_ADDP\",/*Процент надбавки за стаж*/
				null as \"STG_ADD\",/*Надбавка за стаж*/
				null as \"STIM_SAL\",/*Сумма стимулирующих выплат*/
				(WP.rate * WPP.OfficialSalary * (1 + ST.LeadershipBonusPercent / 100 - ST.SalaryReductionPercent / 100)) as \"FOND\",/*Итого месячный фонд*/
				null as \"CATEG_DT\",/*Дата присвоения категории*/
				null as \"CATEG\",/*Категория*/
				MDS.MedSpecOms_name as \"SPC_NAME\",/*Специальность*/
				POT.name as \"POT_NAME\",/*Тип занятия должности*/
				CASE P.tariflist
					WHEN 0 THEN 'Руководители'
					WHEN 1 THEN 'Служащие'
					WHEN 2 THEN 'Медицинские работники'
					WHEN 3 THEN 'Рабочие'
				END as \"TRF_LIST\",/*Тарификационный лист*/
				PT.PayType_Code as \"PT_CODE\", /*Код источника финансирования*/
				PT.PayType_Name as \"PT_NAME\" /*Источник финансирования*/
			FROM persis.v_WorkPlace WP
				LEFT JOIN persis.v_Post P on P.id = WP.post_id
				LEFT JOIN persis.WorkPlace WPP on WPP.id = WP.workplace_id
				LEFT JOIN persis.PostOccupationType POT on POT.id = WP.PostOccupationType_id
				LEFT JOIN persis.staff ST on ST.id = WP.Staff_id
				LEFT JOIN dbo.PayType PT on PT.PayType_id = ST.PayType_id
				--LEFT JOIN persis.Speciality S on S.id = P.Speciality_id
				LEFT JOIN dbo.v_MedSpecOms MDS ON MDS.MedSpecOms_id = WPP.MedSpecOms_id
			WHERE WP.BeginDate <= :ReportDate
				AND (WP.EndDate is null or WP.EndDate > :ReportDate)
				AND WP.Lpu_id = :Lpu_id
			ORDER BY
				WP.Person_SurName,
				WP.Person_FirName,
				WP.Person_SecName
		";

		$defs = array();
		$p = array(
			'ReportDate' => $data['ReportDate'],
			'Lpu_id' => $data['Lpu_id']
		);
		$query = $this->db->query($select, $p);
		$result = $query->result('array');
		$defs = array(
			Array('FAM','C',30),
			Array('IM','C',30),
			Array('OTCH','C',30),
			Array('BD','C',8),
			Array('POST','C',254),
			Array('QUAL_LVL','N',16,0),
			Array('OFF_SAL',"N",16,1),
			Array('LDR_COEF',"N",16,1),
			Array('LDR_SUM','N',16,1),
			Array('RNK_COEF','N',16,0),
			Array('RNL_SUM','N',16,0),
			Array('SALARY','N',16,1),
			Array('SAL_RED','N',16,1),
			Array('VIL_BNS','N',1,0),
			Array('RATE','N',16,1),
			Array('RATE_SAL','N',16,1),
			Array('COMP_SAL','N',16,0),
			Array('STG','N',16,0),
			Array('STG_ADDP','N',16,0),
			Array('STG_ADD','N',16,0),
			Array('STIM_SAL','N',16,0),
			Array('FOND','N',16,1),
			Array('CATEG_DT','N',16,0),
			Array('CATEG','N',16,0),
			Array('SPC_NAME','C',200),
			Array('POT_NAME','C',254),
			Array('TRF_LIST','C',21),
			Array('PT_CODE','N',16,0),
			Array('PT_NAME','C',50)

		);
		$fname = mb_substr(md5(time()), 0, 5);

		$dbf = "export/" . $fname . '.dbf';

		$sd = dbase_create($dbf, $defs);
		foreach ($result as $key) {
			$results = array();
			foreach ($key as $val) {
				$results[] = iconv('UTF-8', 'cp866', $val);
			}
			dbase_add_record($sd, $results);
		}
		dbase_close($sd);
		$zipname = $fname . ".zip";
		$zip = new ZipArchive();
		$zip->open("export/" . $zipname, ZIPARCHIVE::CREATE);
		$zip->AddFile($dbf, "TarifList.dbf");
		$zip->close();
		unlink($dbf);
		$this->exportDBF("export/" . $zipname);
		unlink("export/" . $zipname);
		return true;
	}

	/**
	 *
	 * @param type $file
	 */
	public function exportDBF($file) {

		if (ob_get_level()) {
			ob_end_clean();
		}
		header('Content-Description: File Transfer');
		header('Content-Type: application/octet-stream');
		header('Content-Disposition: attachment; filename=' . basename($file));
		header('Content-Transfer-Encoding: binary');
		header('Expires: 0');
		header('Cache-Control: must-revalidate');
		header('Pragma: public');
		header('Content-Length: ' . filesize($file));
		readfile($file);
		exit;
	}

	/**
	 *
	 * Ищет организацию по ИНН, КПП и ОКПП и возвращает параметры организации и режим (одноыление/добавление)
	 * @param type $file
	 */
	public function getOrgXmlActionAndParams($orgData) {

		if (empty($orgData)) {
			return false;
		}
		$filter = "(1=1)";
		$queryParams = array();

		if (!empty($orgData['Org_INN'])){
			$queryParams['Org_INN'] = $orgData['Org_INN'];
			$filter .= " and Org_INN = cast(:Org_INN as varchar)";
		}

		if ($orgData['OrgType_id'] != '20') {

			if (!empty($orgData['Org_OGRN'])){
				$queryParams['Org_OGRN'] = $orgData['Org_OGRN'];
				$filter .= " and Org_OGRN = cast(:Org_OGRN as varchar)";
			}

			if (!empty($orgData['Org_KPP'])){
				$queryParams['Org_KPP'] = $orgData['Org_KPP'];
				$filter .= " and Org_KPP = cast(:Org_KPP as varchar)";
			}
		}

		$query = "
			select
				O.Server_id as \"Server_id\",
				O.Org_id as \"Org_id\",
				O.Org_Code as \"Org_Code\",
				O.Org_Nick as \"Org_Nick\",
				O.Org_rid as \"Org_rid\",
				O.Org_nid as \"Org_nid\",
				to_char(O.Org_begDate, 'YYYY-MM-DD HH24:MI:SS') as \"Org_begDate\",
				to_char(O.Org_endDate, 'YYYY-MM-DD HH24:MI:SS') as \"Org_endDate\",
				O.Org_Description as \"Org_Description\",
				O.Org_Name as \"Org_Name\",
				O.Okved_id as \"Okved_id\",
				ved.Okved_Code as \"Okved_Code\",
				opf.Okopf_Code as \"Okopf_Code\",
				O.Oktmo_id as \"Oktmo_id\",
				O.Org_INN as \"Org_INN\",
				O.Org_OGRN as \"Org_OGRN\",
				O.Org_Phone as \"Org_Phone\",
				O.Org_Email as \"Org_Email\",
				O.OrgType_id as \"OrgType_id\",
				O.UAddress_id as \"UAddress_id\",
				O.PAddress_id as \"PAddress_id\",
				O.Okopf_id as \"Okopf_id\",
				O.Okogu_id as \"Okogu_id\",
				O.Okonh_id as \"Okonh_id\",
				O.Okfs_id as \"Okfs_id\",
				O.Org_KPP as \"Org_KPP\",
				O.Org_OKPO as \"Org_OKPO\",
				O.Org_OKATO as \"Org_OKATO\",
				O.Org_OKDP as \"Org_OKDP\",
				O.Org_Rukovod as \"Org_Rukovod\",
				O.Org_Buhgalt as \"Org_Buhgalt\",
				O.Org_StickNick as \"Org_StickNick\",
				O.Org_IsEmailFixed as \"Org_IsEmailFixed\",
				O.Org_KBK as \"Org_KBK\",
				O.Org_pid as \"Org_pid\",
				O.Org_RGN as \"Org_RGN\",
				O.Org_WorkTime as \"Org_WorkTime\",
				O.Org_Www as \"Org_Www\",
				O.Org_isAccess as \"Org_isAccess\",
				O.DepartAffilType_id as \"DepartAffilType_id\"
			from
				Org O
				left join Okved ved on ved.Okved_id = O.Okved_id
				left join Okopf opf on opf.Okopf_id = O.Okopf_id
			where
				{$filter}
		";

		//echo getDebugSQL($query, $queryParams);die;
		$result = $this->db->query($query, $queryParams);

		if (is_object($result)){
			return $result->result('array');
		}else {
			return false;
		}
	}

}
