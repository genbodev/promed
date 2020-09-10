<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * Сервис для получения идентификаторов
 */
class Generator_model extends swModel {
	protected $url = 'http://192.168.36.185:85';

	/**
	 * Запуск сервиса
	 */
	function exec() {
		set_time_limit(0); // а вдруг будет работать долго.

		$generatorServiceURL = $this->config->item('generatorServiceURL');
		if (!empty($generatorServiceURL)) {
			$this->url = $generatorServiceURL;
		}

		// 1. Начитывается список данных из generator.ObjectList
		$resp = $this->queryResult("
			select
				ObjectList_Name,
				ObjectList_IdCount,
				ObjectList_Table,
				ObjectList_Threshold
			from
				generator.ObjectList with (nolock)
			where
				ObjectList_IsActive = 2
		");

		if (is_array($resp)) {
			foreach ($resp as $respone) {
				// 2. По каждому объекту запрашивается количество свободных записей
				$resp_count = $this->queryResult("
					select
						count(*) as cnt
					from
						{$respone['ObjectList_Table']} with (nolock)
					where
						ISNULL(Generator_IsUsed, 1) = 1 
				");

				if (isset($resp_count[0]['cnt'])) {
					$countFree = $resp_count[0]['cnt'];
					if ($countFree < $respone['ObjectList_Threshold']) {
						// 3. Выполняется запрос к генератору, в качестве параметров отправляется массив со списком объектов, для которых требуется пополнение списка, и требуемым количеством идентификаторов
						$result = $this->request(array(
							'object' => $respone['ObjectList_Name'],
							'count' => $respone['ObjectList_IdCount'] - $countFree
						));

						if (!empty($result['Error_Msg'])) {
							return $result;
						} else if (!empty($result['success']) && $result['success'] === true) {
							// 4. В ответе по каждому объекту получаем 2 числа - начало и конец выделенного диапазона. Заполняем соответствующую таблицу идентификаторов новыми значениями.
							$values = array();
							for ($i = $result['start']; $i <= $result['finish']; $i++) {
								$values[] = "({$i}, 1, GETDATE(), 1, 1, GETDATE(), GETDATE())";

								if (count($values) >= 300) {
									// запихиваем в БД пачками
									$this->db->query("INSERT INTO {$respone['ObjectList_Table']} (Generator_id, Generator_IsUsed, Generator_GenDate, pmUser_insID, pmUser_updID, Generator_insDT, Generator_updDT) VALUES " . implode(",", $values));
									$values = array();
								}
							}

							if (count($values) > 0) {
								// запихиваем в БД пачками
								$this->db->query("INSERT INTO {$respone['ObjectList_Table']} (Generator_id, Generator_IsUsed, Generator_GenDate, pmUser_insID, pmUser_updID, Generator_insDT, Generator_updDT) VALUES " . implode(",", $values));
							}
						} else {
							return array('Error_Msg' => 'Ошибка запроса идентификаторов по таблице ' . $respone['ObjectList_Name']);
						}
					}
				} else {
					return array('Error_Msg' => 'Ошибка получения количества свободных идентификаторов по таблице ' . $respone['ObjectList_Table']);
				}
			}
		} else {
			return array('Error_Msg' => 'Ошибка получения данных из generator.ObjectList');
		}

		return array('success' => true);
	}

	/**
	 * Отправка запросов в сервис генератор
	 */
	function request($post) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
		curl_setopt($ch, CURLOPT_TIMEOUT, 100);
		curl_setopt($ch, CURLOPT_POST,1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post);

		$result=curl_exec($ch);
		if (!$result) {
			return array('Error_Msg' => curl_error($ch));
		}

		return json_decode($result, true);
	}
}