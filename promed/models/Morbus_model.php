<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * Morbus_model - модель заболевания. содержит методы прикладного уровня и сохранения в БД
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2009-2010 Swan Ltd.
 * @author       gabdushev
 * @version      декабрь 2010 года
 */
require_once('Abstract_model.php');
require_once('MorbusBase_model.php');
/**
 * @property int $Morbus_id
 * @property int $Evn_pid
 * @property int $Diag_id
 * @property int $MorbusKind_id
 * @property string $Morbus_Nick
 * @property string $Morbus_Name
 * @property Datetime $Morbus_setDT
 * @property Datetime $Morbus_disDT
 * @property int $MorbusResult_id
 * @property MorbusBase_model $MorbusBase
 * @property int $pmUser_id Пользователь
 *
 * */
class Morbus_model extends Abstract_model
{
	protected $fields = array(
		'Morbus_id' => null,
		'Evn_pid' => null, //– Учетный документ, из которого добавлено заболевание
		'Diag_id' => null, //– Диагноз
		'MorbusKind_id' => null, //– Характер заболевания
		'Morbus_Name' => null, //-Описание
		'Morbus_Nick' => null, //– Краткое описание
		'Morbus_setDT' => null, //– Дата/время начала заболевания
		'Morbus_disDT' => null, //– Дата/время окончания заболевания
		'MorbusResult_id' => null, //– Исход заболевания
		'pmUser_id' => null
	);
	public $MorbusBase = null;
	private $personId = null;
	protected $updating = false;
	private $Evn_id = null; //учетный документ, из которого данное заболевание редактируется. Не путать с Evn_pid - документом, из которого заболевание было создано!
	private $usedByAnotherEvn = null;

	/**
	 * Doc-блок
	 */
	protected function getTableName()
	{
		return 'Morbus';
	}

	/**
	 * Doc-блок
	 */
	protected function canDelete()
	{
		$this->valid = true;
		//Возможно удаление любого заболевания, добавленного в рамках данного посещения/движения,
		if ($this->Evn_id) {
			if ($this->Evn_id != $this->Evn_pid) {
				$this->addError('Нельзя удалить это заболевание, поскольку оно создано из другого учетного документа');
			}
		} else {
			$this->addError('При удалении необходимо указать, из какого учетного документа производится удаление учетного документа');
		}
		if ($this->getUsedByAnotherEvn()) { //если оно не выбрано в другом посещении/движении.
			$this->addError('Нельзя удалить это заболевание, поскольку оно уже используется в других учетных документах');
		}
		return $this->valid;
	}

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();
		$this->MorbusBase = new MorbusBase_model();
	}

	/**
	 * Создает/привязывает заболевание к учетному документу
	 *
	 * @param int $Evn_id
	 * @param int $Diag_id
	 * @param datetime $setDT
	 * @param $pmUser_id
	 * @return array
	 */
	function ApplyEvn($Evn_id, $Diag_id, $setDT, $pmUser_id){
		//есть ли у человека заболевание с таким же диагнозом или с диагнозом,
		//отличающимся только в четвертом знаке?
		$this->setEvnId($Evn_id);//устанавливаю идентификатор идентификтор движения...
		$this->Diag_id = $Diag_id;//        ... и диагноз (объект использует эти данные для поиска)
		if ($this->findPersonMorbusByDiag()) {
			//если есть - привязываю это движение к открытому онкозаболеванию (произойдет при сохранении заболевания)
			$this->Diag_id = $Diag_id;//на случай если диагноз уточнился, заменяю даигноз заболевания диагнозом этого движения (это нужно делать поскольку если заболевание было найдено, оно целиком загружается из базы)
			$this->setEvnId($Evn_id);//устанавливаю идентификатор учетного документа, из которого было отредактировано заболевания
		} else {
			//если нет - создаю
			//заполняю обязательные для заболевания поля
			$this->Morbus_setDT = $setDT;
			$this->Evn_pid = $Evn_id;
		}
		$this->pmUser_id = $pmUser_id;
		return $this->save();
	}

	/**
	 * Doc-блок
	 */
	public function validate()
	{
		$this->valid = true;
		$this->clearErrors();
		if (!$this->pmUser_id) {
			$this->addError('Не указан пользователь, который вносит изменения в заболевание');
		}
		//– Диагноз. Обязательный атрибут. Значение из справочника МКБ;
		if (null === $this->Diag_id) {
			$this->addError('Диагноз не указан');
		} else {
			//todo проверить правильность значения диагноз
		}
		//– Дата начала заболевания. Обязательный атрибут. По умолчанию, дата начала лечения. Не может быть больше текущей даты.
		if (null === $this->Morbus_setDT) {
			$this->addError('Дата начала заболевания не указана');
		} else {
			// проверить является ли датой
			if (!is_object($this->Morbus_setDT) || (get_class($this->Morbus_setDT) != 'DateTime')) {
				$this->addError('Неверный формат даты начала заболевания');
			} else {
				// проверить не больше ли текущей даты
				if ($this->Morbus_setDT > new DateTime()) {
					$this->addError('Дата начала заболевания не должна быть больше текущей даты');
				}
			}
			//– Дата окончания заболевания. Необязательный атрибут. Не может быть больше текущей даты и меньше даты начала заболевания.
			if (null !== $this->Morbus_disDT) {
				if (!is_object($this->Morbus_disDT) || (get_class($this->Morbus_disDT) != 'DateTime')) {
					$this->addError('Неверный формат даты окончания заболевания');
				} else {
					if ($this->Morbus_disDT < $this->Morbus_setDT) {
						$this->addError('Дата окончания заболевания наступила раньше даты начала заболевания');
					}
					// проверить не больше ли текущей даты
					if ($this->Morbus_disDT > new DateTime()) {
						$this->addError('Дата окончания заболевания не должна быть больше текущей даты');
					}
					//– Исход заболевания. Обязательно для заполнения, если заполнен атрибут «Дата окончания» заболевания
					if (null === $this->MorbusResult_id) {
						$this->addError('Исход заболевания обязательно для заполнения, если указана дата окончания заболевания');
					} else {
						// проверить существует ли исход заболевания с таким ид
						$this->validateByReference('MorbusResult', 'Исход заболевания');
					}
				}
			}
		}
		if (null === $this->Evn_pid) {
			$this->addError('Не указан учетный документ, из которого заболевание было создано');
		}
		if (null === $this->Evn_id) {
			$this->addError('Не указан учетный документ, из которого заболевание было отредактировано');
		} else {
			if ($this->Morbus_id) {
				//проверяю не относится ли данный учетный документ к другому заболеванию
				$cnt = $this->getFirstResultFromQuery(
					'SELECT count(*) FROM v_evn with(nolock) WHERE Morbus_id <> :Morbus_id AND Evn_id = :Evn_id',
					array(
						'Morbus_id' => $this->Morbus_id,
						'Evn_id' => $this->Evn_id
					)
				);
				if ($cnt === false) {
					$this->addError('Не удалось проверить проверку связи заболевания и учетного документа');
				} else {
					if ($cnt !== 0) {
						$alreadyBindedTo = new Morbus_model();
						$alreadyBindedTo->Morbus_id = $this->getFirstResultFromQuery(
							'SELECT Morbus_id FROM v_evn with(nolock) WHERE Evn_id = :Evn_id',
							array('Evn_id' => $this->Evn_id)
						);
						if ($alreadyBindedTo->load()) {
							$this->addError('Данный учетный документ уже относится к другому заболеванию: ' . $alreadyBindedTo);
						} else {
							$this->addError('Ошибка в базе данных. Данный учетный документ относится к несуществующему заболеванию ' . $alreadyBindedTo->Morbus_id);
						}
					}
				}
			}
		}
		//todo написать проверки необязательных параметров (проверить их наличие в справочнике)
		//общее заболевание валидно?
		if (!$this->MorbusBase->isValid()) {
			//нет? запускаю проверку...
			$this->MorbusBase->validate();
		}
		//а теперь?
		if (!$this->MorbusBase->isValid()) {
			//ну значит ошибка
			$this->addError('Общее заболевание имеет ошибки: <br>' . implode('<br>', $this->MorbusBase->getErrors()));
		}
		return $this;
	}

	/**
	 * Возвращает список заболеваний, который можно отобразить в учетном документе.<br/>
	 * Отображаются все "простые" заболевания человека, кроме<br/>
	 *  - тех, группа диагноза которых отличается от группы того диагноза, который указан в учетном документе<br/>
	 *  - помеченных как удаленные<br/>
	 * На входе: идентификтор учетного документа, идентификатор диагноза и идентификатор человека.
	 * Если последние два параметра не переданы, они будут вычислены по первому
	 *
	 * @param int $Evn_id
	 * @param int $Person_id
	 * @param int $Diag_id
	 * @throws Exception
	 * @return array|bool
	 */
	function loadMorbusList($Evn_id, $Person_id = null, $Diag_id = null)
	{
		try {
			if ($Diag_id && $Person_id) {
				// ну и отлично...
			} else {
				list($Diag_id, $Person_id) = $this->detectDiagAndPersonByEvnId($Evn_id);
			}
			$params = array(
				'Diag_id' => $Diag_id,
				'Person_id' => $Person_id,
				'Evn_id' => $Evn_id
			);
			$query = '
                   SELECT
                    Morbus_id,
                    ( SELECT TOP 1
                        diag_FullName
                      FROM
                        v_diag d with(nolock)
                      WHERE
                        d.diag_id = s.diag_id
                    ) AS diag_FullName,
                    CONVERT(VARCHAR(10), Morbus_setDT, 104) AS Morbus_setDT,
                    CONVERT(VARCHAR(10), Morbus_disDT, 104) AS Morbus_disDT,
                    ( SELECT TOP 1
                        MorbusResult_Name
                      FROM
                        v_MorbusResult r with(nolock)
                      WHERE
                        r.MorbusResult_id = s.MorbusResult_id
                    ) AS MorbusResult_Name,
                    CASE WHEN EXISTS ( SELECT
                                        1
                                       FROM
                                        v_evn e with(nolock)
                                       WHERE
                                        e.morbus_id <> s.morbus_id
                                        AND evn_id = :Evn_id )
                         THEN \'false\'
                         ELSE \'true\'
                    END AS Editable,
                    CASE evn_pid
                      WHEN :Evn_id
                      THEN --удаление разрешено если заболевание создано из этого учетного документа
                           CASE WHEN NOT EXISTS ( SELECT
                                                    1
                                                  FROM
                                                    v_evn e with(nolock)
                                                  WHERE
                                                    e.morbus_id = s.morbus_id
                                                    AND evn_id <> :Evn_id ) --и если это заболевание в других учетных документах не используется
                                     THEN \'true\'
                                ELSE \'false\'
                           END
                      ELSE \'false\'
                    END AS Deletable
                   FROM
                    v_Morbus s with(nolock)
                   WHERE
                    --исключаем заболевания из другой группы
                    MorbusBase_id IN (
                    SELECT
                        MorbusBase_id
                    FROM
                        dbo.v_MorbusBase with(nolock)
                    WHERE
                        Person_id = :Person_id
                        AND ( ( MorbusType_id IN ( SELECT
                                                    MorbusType_id
                                                   FROM
                                                    dbo.v_MorbusDiag with(nolock)
                                                   WHERE
                                                    Diag_id = :Diag_id ) )
                              OR ( NOT EXISTS ( SELECT
                                                    MorbusType_id
                                                FROM
                                                    dbo.v_MorbusDiag with(nolock)
                                                WHERE
                                                    Diag_id = :Diag_id )
                                   AND ( MorbusType_id = 1 )
                                 )
                            ) )
                        --исключаем удаленные заболевания
                        AND ( Morbus_Deleted <> 2
                              OR Morbus_Deleted IS NULL
                            ) ';
			$result = $this->db->query($query, $params);
			if (is_object($result)) {
				$response = $result->result('array');
				return $response;
			} else {
				return false;
			}
		} catch (Exception $e) {
			log_message('error', $e->getMessage());
			throw new Exception('Ошибка выполнения запроса. Более подробная информация доступна в протоколе ошибок.');
		}
	}

	/**
	 * Doc-блок
	 */
	public function detectMorbusType($Evn_id)
	{
		//определение Типа заболевания по диагнозу
		if ($this->Diag_id) {
			$Diag_id = $this->Diag_id;
		} else {
			list($Diag_id) = $this->detectDiagAndPersonByEvnId($Evn_id);
		}
		$result = $this->getFirstResultFromQuery('SELECT MorbusType_id FROM dbo.v_MorbusDiag with(nolock) WHERE Diag_id = :Diag_id', array('Diag_id' => $Diag_id));
		if (!$result) {
			$result = 1; //если не найдено - значит общий тип
		}
		return $result;
	}

	/**
	 * Doc-блок
	 */
	public function detectDiagAndPersonByEvnId($Evn_id)
	{
		$Diag_id = null;
		$Person_id = null;
		if ($Evn_id) {
			//определяем диагноз и человека по учетному документу
			try {
				//определяем вьюху, из которой можно дернуть диагноз
				$evn_view = $this->getFirstResultFromQuery("SELECT EvnClass_SysNick FROM v_evn with(nolock) WHERE evn_id = :Evn_id", array('Evn_id' => $Evn_id));
				if ($evn_view) {
					$Diag_id = $this->getFirstResultFromQuery("select diag_id from v_{$evn_view} with(nolock) where {$evn_view}_id = :Evn_id", array('Evn_id' => $Evn_id));
					if (!$Diag_id) {
						throw new Exception("Ошибка: Не удается определить диагноз из учетного документа. Возможно диагноз в учетном документе не указан. Идентификатор учетного документа: " . $Evn_id);
					}
				} else {
					//значит имеем дело с картой диспучета
					//todo написать определение диагноза из карты ДУ
					throw new Exception("Ошибка определения типа учетного документа. Идентификатор учетного документа: " . $Evn_id);
				}
			} catch (Exception $e) {
				$Diag_id = null;
				throw new Exception("Ошибка определения диагноза. Идентификатор учетного документа: " . $Evn_id, 0, $e);
			}
		} else {
			throw new Exception("Не задано необходимых параметров для построения списка заболеваний. Необходимо либо указать учетный документ, либо человека и диагноз", 0);
		}
		try {
			if (null === $Person_id) {
				$Person_id = $this->getFirstResultFromQuery("select Person_id from v_{$evn_view} with(nolock) where {$evn_view}_id = :Evn_id", array('Evn_id' => $Evn_id));
			}
		} catch (Exception $e) {
			throw new Exception("Ошибка определения человека. Идентификатор учетного документа: " . $Evn_id, 0, $e);
		}
		return array($Diag_id, $Person_id);
	}

	/**
	 * Doc-блок
	 */
	public function assign($values)
	{
		//запоминаем переданный документ как документ, из которого данное заболевание было отредактировано
		if (isset($values['Evn_pid'])) {
			$this->Evn_id = $values['Evn_pid'];
		} else {
			if (isset($values['Evn_id'])) {
				$this->Evn_id = $values['Evn_id'];
			}
		}
		if ($this->updating) {
			//менять значение Evn_pid нельзя, документ из которого создано заболевание всегда один и тот же
			if (isset($values['Evn_pid'])) {
				unset($values['Evn_pid']);
			}
		}
		parent::assign($values);
		if (isset($values['MorbusBase_id']) && $values['MorbusBase_id']) {
			$this->MorbusBase->MorbusBase_id = $values['MorbusBase_id'];
			$this->MorbusBase->load();
		}
	}

	/**
	 *  Saves some data to database
	 *
	 * @throws Exception
	 * @return array|bool|mixed
	 */
	function save($data = array())
	{
		$this->start_transaction();
		if (null === $this->MorbusBase->MorbusBase_id) {
			if (!$this->findMorbusBase()) {
				$this->createMorbusBase();
			}
		}
		$response = parent::save();
		if (self::save_ok($response)) {
			$this->MorbusBase->pmUser_id = $this->pmUser_id;
			$response = $this->MorbusBase->updateBoundaries();
			if (self::save_ok($response)) {
				//создаем связь между заболеванием и учетным документом, из которого данное заболевание было отредактировано
				$evnSet_responce = $this->evnSetMorbus();
				$evnSet_ok = self::save_ok($evnSet_responce);
				if ($evnSet_ok) {
					$this->commit();
				} else {
					$this->rollback();
					throw new Exception('Ошибка при создании связи между заболеванием и учетным документом, из которого заболевание было отредактировано. ' . $evnSet_responce[0]['Error_Msg']);
				}
			} else {
				$this->rollback();
			}
		} else {
			$this->rollback();
		}
		return $response;
	}

	/**
	 * Doc-блок
	 */
	function load($field = null, $value = null, $selectFields = '*', $addNameEntries = true)
	{
		$this->updating = false; //переустанавливаем флаг на случай если производится загрузка потомка
		$result = parent::load($field, $value, $selectFields, $addNameEntries);
		$this->updating = true;
		return $result;
	}

	/**
	 * Doc-блок
	 */
	public function chooseSaveProcedure()
	{
		if ($this->__get($this->getKeyFieldName()) > 0) {
			$proc = $this->getSaveProcedureName() . '_upd';
		} else {
			$proc = $this->getSaveProcedureName() . '_ins';
			$this->__set($this->getKeyFieldName(), null);
		}
		return $proc;
	}

	/**
	 * Устанавливает связь между учетным документом и заболеванием.
	 * Если в качестве параметра передано true, то удаляет связь.
	 *
	 * @param bool $setToNull
	 * @return array|mixed
	 */
	private function evnSetMorbus($setToNull = false)
	{
		if ($this->validate()->isValid()) {
			if ($setToNull) {
				$morbus_id = null;
			} else {
				$morbus_id = $this->Morbus_id;
			}
			$params = array(
				'Morbus_id' => $morbus_id,
				'Evn_id' => $this->Evn_id,
				'pmUser_id' => $this->pmUser_id
			);
			$query_declarations = "@Error_Code bigint, @Error_Message varchar(4000);";
			$query = "
                declare
                   $query_declarations
                EXEC dbo.p_Evn_setMorbus @Evn_id = :Evn_id, -- bigint
                    @Morbus_id = :Morbus_id, -- bigint
                    @pmUser_id = :pmUser_id, -- bigint
                    @Error_Code = @Error_Code output, -- int
                    @Error_Message = @Error_Message output-- varchar(4000)
               select :Morbus_id as Morbus_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
            ";
			try {
				$dbresponse = $this->db->query($query, $params);
				if (is_object($dbresponse)) {
					$result = $dbresponse->result('array');
				} else {
					$result = array(
						0 => array(
							$this->getKeyFieldName() => null,
							'Error_Code' => null,
							'Error_Msg' => 'При сохранении связи заболевания и учетного документа произошли ошибки'
						)
					);
				}
			} catch (Exception $e) {
				$result = array(
					0 => array(
						$this->getKeyFieldName() => null,
						'Error_Code' => null,
						'Error_Msg' => 'При вызове процедуры сохранения связи заболевания и учетного документа произошли ошибки: ' . str_replace(chr(13), ' ', str_replace(chr(10), '<br> ', $e->getCode() . ' ' . $e->getMessage()))
					)
				);
			}
		} else {
			$result = array(
				0 => array(
					$this->getKeyFieldName() => null,
					'Error_Code' => null,
					'Error_Msg' => 'При сохранении связи заболевания и учетного документа произошли ошибки: <br/>' . implode('<br/>', $this->getErrors())
				)
			);
		}
		return $result;
	}

	/**
	 * Doc-блок
	 */
	function createMorbusBase()
	{
		$this->MorbusBase->MorbusBase_id = null;
		$this->MorbusBase->MorbusBase_setDT = $this->Morbus_setDT;
		$this->MorbusBase->MorbusBase_disDT = $this->Morbus_disDT;
		$this->MorbusBase->MorbusResult_id = $this->MorbusResult_id;
		$this->MorbusBase->MorbusType_id = $this->MorbusBase->getMorbusTypeByDiag($this->Diag_id);
		$this->MorbusBase->Evn_pid = $this->Evn_pid;
		$this->MorbusBase->pmUser_id = $this->pmUser_id;
		$this->MorbusBase->Person_id = $this->getPersonId();
		$this->MorbusBase->transactional = $this->transactional;
		return $this->MorbusBase->save();
	}

	/**
	 * Doc-блок
	 */
	function getPersonId()
	{
		if (null === $this->personId) {
			if ($this->Evn_pid) {
				$this->personId = $this->getFirstResultFromQuery('SELECT Person_id FROM v_evn with(nolock) WHERE evn_id = :Evn_id', array('Evn_id' => $this->Evn_pid));
			} else {
				if ($this->Evn_id) {
					$this->personId = $this->getFirstResultFromQuery('SELECT Person_id FROM v_evn with(nolock) WHERE evn_id = :Evn_id', array('Evn_id' => $this->Evn_id));
				} else {
					throw new Exception('Невозможно определить человека, т.к. не указан учетный документ');
				}
			}
		}
		return $this->personId;
	}

	/**
	 * Ищет общее заболевание для этого простого заболевания, подходящее по типу. Вернет True если найдет, иначе False.
	 * Если находит - загружает его в MorbusBase
	 * @return bool
	 */
	function findMorbusBase()
	{
		$morbusType = $this->MorbusBase->getMorbusTypeByDiag($this->Diag_id);
		$MorbusBase_id = $this->getFirstResultFromQuery(
			'
            SELECT
                MorbusBase_id
            FROM
                dbo.v_MorbusBase with(nolock)
            WHERE
                Person_id = :Person_id
                AND MorbusType_id = :MorbusType_id',
			array('Person_id' => $this->getPersonId(), 'MorbusType_id' => $morbusType));
		if (!$MorbusBase_id) {
			return false;
		} else {
			$this->MorbusBase->MorbusBase_id = $MorbusBase_id;
			$this->MorbusBase->load();
			return true;
		}
	}


	/**
	 * Doc-блок
	 */
	protected function getParamList($params, $query_paramlist_exclude)
	{
		list($params, $query_paramlist) = parent::getParamList($params, $query_paramlist_exclude);
		$params['MorbusBase_id'] = $this->MorbusBase->MorbusBase_id;
		$query_paramlist = "$query_paramlist @MorbusBase_id = :MorbusBase_id,";
		return array($params, $query_paramlist);
	}

	/**
	 * @return MorbusBase_model
	 */
	public function getMorbusBase()
	{
		return $this->MorbusBase;
	}

	/**
	 * Doc-блок
	 */
	function __toString()
	{
		if ($this->Morbus_setDT) {
			$begin_date = $this->Morbus_setDT->format('d.m.Y');
		} else {
			$begin_date = '...';
		}
		if ($this->Morbus_disDT) {
			$end_date = $this->Morbus_setDT->format('d.m.Y');
		} else {
			$end_date = '...';
		}
		if ($this->Diag_id) {
			$diag_name = $this->getFirstResultFromQuery('SELECT diag_fullName FROM v_diag with(nolock) WHERE diag_id = :Diag_id',
				array('Diag_id' => $this->Diag_id));
		} else {
			$diag_name = 'Диагноз не указан';
		}
		$result = "$diag_name (с $begin_date по $end_date)";
		return $result;
	}

	/**
	 * Doc-блок
	 */
	public function getEvnId()
	{
		return $this->Evn_id;
	}

	/**
	 * Устанавливает учетный документ, из которого заболевание будет редактироваться/удаляться
	 * @param $Evn_id
	 */
	public function setEvnId($Evn_id)
	{
		$this->Evn_id = $Evn_id;
	}

	/**
	 * Doc-блок
	 */
	public function getUsedByAnotherEvn()
	{
		if ($this->usedByAnotherEvn === null) {
			$cnt = $this->getFirstResultFromQuery('SELECT Count(*) FROM v_evn with(nolock) WHERE evn_id <> :Evn_id and Morbus_id = :Morbus_id',
				array('Evn_id' => $this->Evn_id, 'Morbus_id' => $this->Morbus_id)
			);
			$this->usedByAnotherEvn = ($cnt !== 0);
		}
		return $this->usedByAnotherEvn;
	}

	/**
	 * Doc-блок
	 */
	function delete($data = array())
	{
		// При удалении простого заболевания, проводить проверку на наличие других простых заболеваний данного типа.
		// - если нет заболеваний (ни открытых, ни закрытых) данного типа, то общее заболевание удалять
		// - если есть простое заболевание данного типа, то с общим заболеванием ничего не делаем
		if ($this->start_transaction()) {
			try {
				$removeMorbusBase = (0 === $this->MorbusBase->morbusCount($this->__get($this->getKeyFieldName())));
				$evnSet_responce = $this->evnSetMorbus(true);
				if (!self::save_ok($evnSet_responce)) {
					throw new Exception('Ошибка удаления связи заболевания с учетным документом');
				}
				$result = parent::delete();
				if (!self::save_ok($result)) {
					$err = '';
					if (isset($result[0]) && (isset($result[0]['Error_Msg']))) {
						$err = ': ' . $result[0]['Error_Msg'];
					}
					throw new Exception("Ошибка удаления заболевания{$err}");
				}
				if ($removeMorbusBase) {
					$morbusBaseDelResult = $this->MorbusBase->delete();
					if (!self::save_ok($morbusBaseDelResult)) {
						throw new Exception('Ошибка удаления общего заболевания');
					}
				} else {
					$this->MorbusBase->pmUser_id = $this->pmUser_id;
					$morbusBaseUpdResult = $this->MorbusBase->updateBoundaries();
					if (!self::save_ok($morbusBaseUpdResult)) {
						$err = '';
						if (isset($morbusBaseUpdResult[0]) && (isset($morbusBaseUpdResult[0]['Error_Msg']))) {
							$err = ': ' . $morbusBaseUpdResult[0]['Error_Msg'];
						}
						throw new Exception('Ошибка сохранения даты начала и даты окончания общего заболевания' . $err);
					}
				}
				$this->commit();
			} catch (Exception $e) {
				$this->rollback();
				throw $e;
			}
		} else {
			throw new Exception('Ошибка открытия транзакции');
		}
		return $result;
	}

	/**
	 * Находит заболевание по даигнозу
	 *
	 * @param null $Person_id
	 * @throws Exception
	 * @return bool|mixed
	 */
	function findPersonMorbusByDiag($Person_id = null)
	{
		//Запрос всех активных заболеваний у данного человека с таким же диагнозом или диагнозом, "отличающимся только 4-ым знком"
		$where_clause = "MorbusBase_id IN ( SELECT
                                            MorbusBase_id
                                           FROM
                                            dbo.v_MorbusBase with(nolock)
                                           WHERE
                                            person_id = :Person_id )
                        AND diag_id IN (
                            SELECT diag_id FROM v_diag with(nolock)
                              WHERE
                                diag_id IN (SELECT diag_id FROM dbo.v_MorbusDiag with(nolock) WHERE MorbusType_id = :MorbusType_id)
                                AND DiagLevel_id = 4
                                AND diag_pid = (select diag_pid FROM v_diag with(nolock) WHERE diag_id = :Diag_id)
                        )
                        AND Morbus_disDT IS null";
		if (!$Person_id) {
			$Person_id = $this->getPersonId();
		}
		$params = array(
			'Person_id' => $Person_id,
			'Diag_id' => $this->Diag_id,
			'MorbusType_id' => $this->detectMorbusType($this->Evn_id)
		);
		$query = "
            SELECT
                count(*)
            FROM
                v_Morbus m with(nolock)
            WHERE
                $where_clause";
		$count = $this->getFirstResultFromQuery($query, $params);
		if ($count === 0) {
			return false; //значит заболевания с таким диагнозом у человека нет
		} else {
			if ($count > 1) {
				throw new Exception("Ошибочные данные в БД: для человека с идентификатором {$this->personId} существует более одного заболевания с диагнозом {$this->Diag_id}");
			} else {
				if ($count === 1) {
					$query = "SELECT
                          Morbus_id
                        FROM
                            v_Morbus m with(nolock)
                        WHERE
                            $where_clause";
					$this->Morbus_id = $this->getFirstResultFromQuery($query, $params);
					return $this->load();
				}
			}
		}
	}

	/**
	 * Закрытие заболевания
	 * @param array $data
	 * @return array
	 * @comment закрывать заболевание может только оператор по регистрам при исключении из регистра. 
	 */
	public function closeMorbus($data) {
		$query = '
			declare 
			@Morbus_id bigint = :Morbus_id,
			@pmUser_id bigint = :pmUser_id,
			@Morbus_disDT datetime = :Morbus_disDT,
			@Error_Code int = null,
			@Error_Message varchar(4000) = null;

			set nocount on;
			
			begin try
				begin tran

					update Morbus with (rowlock)
						SET Morbus_disDT = @Morbus_disDT,
							Morbus_updDT = GetDate(),
							pmUser_updID = @pmUser_id
					where Morbus_id = @Morbus_id
					
				commit tran
			end try

			begin catch
				set @Error_Code = error_number();
				set @Error_Message = error_message();
				if @@trancount>0
					rollback tran
			end catch

			select @Morbus_id as Morbus_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
			
			set nocount off;
		';
		try {
			if ( empty($data['Morbus_id']) )
			{
				throw new Exception('Не указан идентификатор заболевания');
			}
			if ( empty($data['pmUser_id']) )
			{
				throw new Exception('Не указан идентификатор пользователя');
			}
			
			$p = array(
				'Morbus_id' => $data['Morbus_id']
				,'Morbus_disDT' => (empty($data['Morbus_disDT']))?date('Y-m-d'):$data['Morbus_disDT']
				,'pmUser_id' => $data['pmUser_id']
			);
			// echo getDebugSQL($query, $p); exit();
			$result = $this->db->query($query, $p);
			if ( is_object($result) )
			{
				return $result->result('array');
			}
			else
			{
				throw new Exception('Ошибка БД');	
			}
		} catch (Exception $e) {
			return array(array('Error_Msg' => 'Закрытие заболевания: '. $e->getMessage()));	
		}
	}

	/**
	 * Открытие заболевания
	 * @param array $data
	 * @return array
	 * @comment открывать заболевание может только оператор по регистрам при добавлении новой записи или при удалении из регистра. 
	 */
	public function openMorbus($data) {
		$query = '
			declare 
			@Morbus_id bigint = :Morbus_id,
			@pmUser_id bigint = :pmUser_id,
			@Error_Code int = null,
			@Error_Message varchar(4000) = null;

			set nocount on;
			
			begin try
				begin tran

					update Morbus with (rowlock)
						SET Morbus_disDT = null,
							Morbus_updDT = GetDate(),
							pmUser_updID = @pmUser_id
					where Morbus_id = @Morbus_id
					
				commit tran
			end try

			begin catch
				set @Error_Code = error_number();
				set @Error_Message = error_message();
				if @@trancount>0
					rollback tran
			end catch

			select @Morbus_id as Morbus_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
			
			set nocount off;
		';
		try {
			if ( empty($data['Morbus_id']) )
			{
				throw new Exception('Не указан идентификатор заболевания');
			}
			if ( empty($data['pmUser_id']) )
			{
				throw new Exception('Не указан идентификатор пользователя');
			}
			
			$p = array(
				'Morbus_id' => $data['Morbus_id']
				,'pmUser_id' => $data['pmUser_id']
			);
			// echo getDebugSQL($query, $p); exit();
			$result = $this->db->query($query, $p);
			if ( is_object($result) )
			{
				return $result->result('array');
			}
			else
			{
				throw new Exception('Ошибка БД');	
			}
		} catch (Exception $e) {
			return array(array('Error_Msg' => 'Открытие заболевания: '. $e->getMessage()));	
		}
	}

	/**
	 * Устанавливает/удаляет связь между учетным документом и заболеванием.
	 *
	 * @param array $data
	 * @return array
	 */
	public function evn_setMorbus($data)
	{
		$query = '
			declare
				@Evn_id bigint = :Evn_id,
				@Morbus_id bigint = :Morbus_id,
				@pmUser_id bigint = :pmUser_id,
				@Error_Code int,
				@Error_Message varchar(4000);
			EXEC dbo.p_Evn_setMorbus
				@Evn_id,
				@Morbus_id,
				@pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output
		   select @Evn_id as Evn_id, @Morbus_id as Morbus_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
		';
		try {
			if ( empty($data['Evn_id']) )
			{
				throw new Exception('Не указан идентификатор учетного документа');
			}
			if ( empty($data['pmUser_id']) )
			{
				throw new Exception('Не указан идентификатор пользователя');
			}
			
			$p = array(
				'Morbus_id' => empty($data['Morbus_id'])?null:$data['Morbus_id']
				,'Evn_id' => $data['Evn_id']
				,'pmUser_id' => $data['pmUser_id']
			);
			$result = $this->db->query($query, $p);
			if ( is_object($result) )
			{
				return $result->result('array');
			}
			else
			{
				throw new Exception('Ошибка БД');	
			}
		} catch (Exception $e) {
			return array(array('Error_Msg' => 'Сохранение связи между учетным документом и заболеванием: '. $e->getMessage()));	
		}
	}

	/**
	 * Устанавливает связь между записью регистра и заболеванием.
	 *
	 * @param array $data
	 * @return array
	 */
	public function PersonRegister_setMorbus($data)
	{
		$query = '
			declare
				@PersonRegister_id bigint = :PersonRegister_id,
				@Morbus_id bigint = :Morbus_id,
				@pmUser_id bigint = :pmUser_id,
				@Error_Code int,
				@Error_Message varchar(4000);

			set nocount on;

			begin try
				begin tran

					update PersonRegister with (rowlock)
						SET Morbus_id = @Morbus_id,
							PersonRegister_updDT = GetDate(),
							pmUser_updID = @pmUser_id
					where PersonRegister_id = @PersonRegister_id
					
				commit tran
			end try

			begin catch
				set @Error_Code = error_number();
				set @Error_Message = error_message();
				if @@trancount>0
					rollback tran
			end catch

			select @PersonRegister_id as PersonRegister_id, @Morbus_id as Morbus_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;

			set nocount off;
		';
		try {
			if ( empty($data['Morbus_id']) )
			{
				throw new Exception('Не указан идентификатор заболевания');
			}
			if ( empty($data['PersonRegister_id']) )
			{
				throw new Exception('Не указан идентификатор записи регистра');
			}
			if ( empty($data['pmUser_id']) )
			{
				throw new Exception('Не указан идентификатор пользователя');
			}
			
			$p = array(
				'Morbus_id' => $data['Morbus_id']
				,'PersonRegister_id' => $data['PersonRegister_id']
				,'pmUser_id' => $data['pmUser_id']
			);
			$result = $this->db->query($query, $p);
			if ( is_object($result) )
			{
				return $result->result('array');
			}
			else
			{
				throw new Exception('Ошибка БД');	
			}
		} catch (Exception $e) {
			return array(array('Error_Msg' => 'Сохранение связи между записью регистра и заболеванием: '. $e->getMessage()));	
		}
	}
}