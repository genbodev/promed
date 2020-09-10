<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * MongoCache - контроллер для работы с кэшем Монго с формы управления кэшем
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Admin
 * @access			public
 * @copyright		Copyright (c) 2014 Swan Ltd.
 * @author			Марков Андрей <markov@swan.perm.ru>
 * @version			07.2014
 *
 * @property MongoCache_model dbmodel
 */

class MongoCache extends swController {
	protected  $inputRules = array(
		'saveMongoCache' => array(
			array(
				'field' => 'sysCache_id',
				'label' => 'Идентификатор объекта',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'sysCache_name',
				'label' => 'Наименование',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'sysCache_object',
				'label' => 'Объект БД',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'sysCache_ttl',
				'label' => 'Актуальность кэша, в секундах',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'sysCache_sql',
				'label' => 'SQL-запрос',
				'rules' => '',
				'type' => 'string'
			)
		),
	'deleteMongoCache' => array(
			array(
				'field' => 'id',
				'label' => 'Идентификатор объекта кэша',
				'rules' => '',
				'type' => 'id'
			), 
			array(
				'field' => 'ids',
				'label' => 'Идентификатор объекта кэша',
				'rules' => '',
				'type' => 'string'
			)
		),
		'loadMongoCacheList' => array(
			array(
				'field' => 'sysCache_id',
				'label' => 'Идентификатор объекта кэша',
				'rules' => '',
				'type' => 'id'
			), 
			array(
				'field' => 'searchName',
				'label' => 'Наименование',
				'rules' => '',
				'type' => 'string'
			), 
			array(
				'field' => 'searchAuto',
				'label' => 'Признак загрузки автоматические кэши',
				'rules' => '',
				'type' => 'checkbox'
			)
		),
		'loadMongoCacheContent' => array(
			array(
				'field' => 'sysCache_object',
				'label' => 'Наименования объекта БД',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'panelId',
				'label' => 'Id панели',
				'rules' => '',
				'type' => 'string'
			)
		),
		'recacheMongoCache' => array(
			array(
				'field' => 'sysCache_id',
				'label' => 'Идентификатор объекта',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'type',
				'label' => 'type',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field'=>'IDs',
				'label'=>'IDs',
				'rules'=>'trim',
				'type'=>'string'
				
			)
		),
		'getSettingsMongoCache' => array(
			array(
				'field' => 'sysCache_id',
				'label' => 'Идентификатор объекта',
				'rules' => '',
				'type' => 'id'
			)
		),
		'setSettingsMongoCache' => array(
			array(
				'field' => 'sysCache_id',
				'label' => 'Идентификатор объекта',
				'rules' => '',
				'type' => 'id'
			)
		)
	);

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
		$this->load->database();
		$this->load->model('MongoCache_model', 'dbmodel');
	}

	/**
	 * Сохранение объекта кэширования
	 */
	function saveMongoCache() {
		$data = $this->ProcessInputData('saveMongoCache', false);
		if ($data === false) { return false; }

		$response = $this->dbmodel->saveMongoCache($data);
		$this->ProcessModelSave($response, true)->ReturnData();
		return true;
	}

	/**
	 * Удаление объекта кэширования
	 */
	function deleteMongoCache() {
		$data = $this->ProcessInputData('deleteMongoCache', false);
		if ($data === false) { return false; }
		// Доступно удаление нескольких записей 
		$ids = array();
		if (isset($data['ids'])) {
			$ids = json_decode($data['ids']);
		} elseif (isset($data['id'])) {
			$ids[] = $id;
		} else {
			$this->ReturnData(array('success' => false, 'Error_Msg'=> 'Ошибка при удалении объекта кэширования'));
			return false;
		}
		foreach ($ids as $id) {
			$data['id'] = $id;
			$response = $this->dbmodel->deleteMongoCache($data);
			if (is_array($response) && isset($response['success']) && (!$response['success'])) {
				break; // выходим из цикла и сообщаем ошибку удаления не пытаясь удалить остальное
			}
		}
		$this->ProcessModelSave($response, true)->ReturnData();
		return true;
	}

	/**
	 * Возвращает список кэшируемых объектов
	 * @return bool
	 */
	function loadMongoCacheList() {
		$data = $this->ProcessInputData('loadMongoCacheList', false);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadMongoCacheList($data);
		$this->ProcessModelList($response, false, 'При получении данных возникли ошибки')->ReturnData();
		return true;
	}

	/**
	 * Возвращает часть кэша объекта
	 * @return bool
	 */
	function loadMongoCacheContent() {
		$data = $this->ProcessInputData('loadMongoCacheContent', false);
		if ($data === false) { return false; }
		set_time_limit(60);
		
        echo "
		<!---/*NO PARSE JSON*/--->
		<style type='text/css'>
			table.".$data['panelId']." { border-collapse: collapse; }
			table.".$data['panelId']." td { border: solid 1px black; padding: 2px 5px; text-align: left; font-size: 9pt; }
			table.".$data['panelId']." tr.header td { font-weight: bolder; text-align: center; background-color: #dddddd; }
			table.".$data['panelId']." td.header { font-weight: bolder; text-align: left; background-color: #dddddd; }
		</style>
		";
		
		// выполняем запрос
		$response = $this->dbmodel->loadMongoCacheContent($data);
		
		if (is_array($response)) {
			if (count($response) > 0) {
				echo "<table class='".$data['panelId']."'>";
				echo "<tr class='header'>";
				echo "<td>№</td>";
				// todo: Вместо ksort-сортировки можно сделать сначала получение максимального количества столбцов из набора, а затем видимо все ту же ksort либо uksort
				ksort($response[0]);
				foreach($response[0] as $key => $value) {
					echo "<td>{$key}</td>";
				}
				echo "</tr>";
				$rownum = 0;
				foreach($response as $row) {
					echo "<tr>";
					ksort($row);
					$rownum++;
					echo "<td>{$rownum}</td>";
					foreach($row as $key=>$val) {
						$vl = $val;
						if (!empty($vl)) {
							if (is_object($vl) && get_class($vl) == 'DateTime') {
								$vl = $vl->format("Y-m-d H:i:s");
							}
							if (is_array($vl) && isset($vl['date'])) {
								$vl = $vl['date'];
							}
							if (strlen($vl) > 300)
								$vl = substr($vl, 0, 300).'...';
						} else {
							$vl = '&nbsp;';
						}					
						echo '<td>'.($vl != '&nbsp;' ? htmlspecialchars($vl) : $vl).'</td>';
					}
					echo "</tr>";
				}
				echo "</table>";
			} else {
				echo 'По указанному объекту '.$data['sysCache_object'].' в кэше нет записей.';
			}
		}
		
		echo "
			</body>
			</html>
		";
		
	}

	/**
	 * Перекэширование данных объекта
	 * @return bool
	 */
	function recacheMongoCache() {
		set_time_limit(0);
		$data = $this->ProcessInputData('recacheMongoCache', false);
		if ($data === false) { return false; }
		$response = $this->dbmodel->recacheMongoCache($data);
		$this->ProcessModelList($response, false, 'При получении данных возникли ошибки')->ReturnData();
		return true;
	}
	/**
	 *
	 * @return type 
	 */
	function clearMongoCache() {
		set_time_limit(0);
		$data = $this->ProcessInputData('recacheMongoCache', false);
		if ($data === false) { return false; }
		$response = $this->dbmodel->clearMongoCache($data);
		$this->ProcessModelList($response, false, 'При получении данных возникли ошибки')->ReturnData();
		return true;
	}
	
	/**
	 * Читает настройки для кэшируемого объекта
	 * @return bool
	 */
	function getSettingsMongoCache() {
		$data = $this->ProcessInputData('getSettingsMongoCache', false);
		if ($data === false) { return false; }

		$response = $this->dbmodel->getSettingsMongoCache($data);
		$this->ProcessModelList($response, false)->ReturnData();
		return true;
	}

	/**
	 * Устанавливает настройки для кэшируемого объекта
	 * @return bool
	 */
	function setSettingsMongoCache() {
		$data = $this->ProcessInputData('setSettingsMongoCache', false);
		if ($data === false) { return false; }

		$response = $this->dbmodel->setSettingsMongoCache($data);
		$this->ProcessModelSave($response, false)->ReturnData();
		return true;
	}
	
	/**
	 * clearCacheCollections / Удаление коллекций кэша из БД
	 */
	function clearCacheCollections() {
		if (empty($_GET['db'])) {
			echo "Задайте имя базы данных";
			return false;
		}
		if ( extension_loaded( 'mongo' ) ) {
			$this->load->library('swMongodb');
			$result = $this->swmongodb->execute('function (){ return db.getCollectionNames(); }', array());
			$collcount = 0;
			if ($result) {
				foreach ($result as $i => $collection) {
					/*if (substr($collection,0,15) == "cacheVizitCode_") {
						$del = $this->swmongodb->drop_collection($_GET['db'], $collection);
						if ($del) {
							echo $collection." успешно удалена<br/>";
						}
					}
					if (substr($collection,0,19) == "cacheUslugaComplex_") {
						$del = $this->swmongodb->drop_collection($_GET['db'], $collection);
						if ($del) {
							echo $collection." успешно удалена<br/>";
						}
					}*/
					// Удаляем все коллекции начинающиеся с префикса cache
					if (substr($collection,0,5) == "cache") {
						$del = $this->swmongodb->drop_collection($_GET['db'], $collection);
						if ($del) {
							$collcount++;
						}
					}
				}
			}
			if ($collcount>0) {
				// Удалим sysCache
				$this->swmongodb->drop_collection($_GET['db'], "sysCache");
			}
			echo "Удалено ".$collcount." коллекций";
		}
	}
}