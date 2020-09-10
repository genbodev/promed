<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * TariffValue_model - модель для работы со справочником тарифов ТФОМС
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Admin
 * @access			public
 * @copyright		Copyright (c) 2018 Swan Ltd.
 */

class TariffValue_model extends swModel {
	/**
	 * Конструктор объекта
	 */
	function __construct()
	{
		parent::__construct();
	}

	/**
	 * Удаление тарифа
	 */
	public function delete($data) {
		return $this->queryResult("
			declare
				@Error_Code int,
				@Error_Msg varchar(4000);

			exec r58.p_TariffValue_del
				@TariffValue_id = :TariffValue_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Msg output;

			select @Error_Code as Error_Code, @Error_Msg as Error_Msg;
		", $data);
	}

	/**
	 * Импорт тарифов ТФОМС
	 */
	public function import($data) {
		ignore_user_abort(true);
		set_time_limit(0); // обязательно, иначе на больших объемах выгружаемых данных до конца не выполнится

		$response = array(
			'Error_Msg' => ''
		);

		$this->load->model('Messages_model');

		$messageData = array();

		try {
			$messageData[] = "[" . date('d.m.Y H:i:s') . "] Запуск";

			$allowed_types = array('zip', 'rar', 'dbf');
			$timestamp = time();
			$upload_path = './' . IMPORTPATH_ROOT . 'importTariffValue/' . $timestamp . '/';

			if ( !isset($_FILES['import_file']) ) {
				throw new Exception('Не выбран файл реестра!');
			}

			if ( !is_uploaded_file($_FILES['import_file']['tmp_name']) ) {
				$error = (!isset($_FILES['import_file']['error'])) ? 4 : $_FILES['import_file']['error'];

				switch ( $error ) {
					case 1:
						$message = 'Загружаемый файл превышает максимально допустимый размер, определённый в вашем файле конфигурации PHP.';
						break;
					case 2:
						$message = 'Загружаемый файл превышает максимально допустимый размер, заданный формой.';
						break;
					case 3:
						$message = 'Этот файл был загружен не полностью.';
						break;
					case 4:
						$message = 'Вы не выбрали файл для загрузки.';
						break;
					case 6:
						$message = 'Временная директория не найдена.';
						break;
					case 7:
						$message = 'Файл не может быть записан на диск.';
						break;
					case 8:
						$message = 'Неверный формат файла.';
						break;
					default :
						$message = 'При загрузке файла произошла ошибка.';
						break;
				}

				throw new Exception($message);
			}

			// Тип файла разрешен к загрузке?
			$x = explode('.', $_FILES['import_file']['name']);
			$file_data = array(
				'file_ext' => strtolower(end($x))
			);

			if ( !in_array($file_data['file_ext'], $allowed_types) ) {
				throw new Exception('Данный тип файла не разрешен.');
			}

			// Правильно ли указана директория для загрузки?
			$path = '';
			$folders = explode('/', $upload_path);

			for ( $i = 0; $i < count($folders); $i++ ) {
				if ( empty($folders[$i]) ) {
					continue;
				}

				$path .= $folders[$i].'/';

				if ( !@is_dir($path) ) {
					mkdir($path);
				}
			}

			if ( !@is_dir($upload_path) ) {
				throw new Exception('Путь для загрузки файлов некорректен.');
			}
			
			// Имеет ли директория для загрузки права на запись?
			if ( !is_writable($upload_path) ) {
				throw new Exception('Загрузка файла невозможна из-за прав пользователя.');
			}

			switch ( $file_data['file_ext'] ) {
				case 'dbf':
					$dbffile = $_FILES['import_file']['name'];

					if ( !@move_uploaded_file($_FILES["import_file"]["tmp_name"], $upload_path . $dbffile) ) {
						throw new Exception('Не удаётся переместить файл.');
					}
					break;

				case 'rar':
					// Проверить доступность библиотеки для работы с RAR
					if ( !class_exists('RarArchive') ) {
						throw new Exception('Отсутствует библиотека для работы с архивами RAR.');
					}

					$rar = RarArchive::open($_FILES["import_file"]["tmp_name"]);
					$entries = $rar->getEntries();

					foreach ( $entries as $entry ) {
						if ( preg_match('/.*\.dbf/i', $entry->getName()) ) {
							$dbffile = $entry->getName();
							$entry->extract($upload_path);
							break;
						}
					}

					$rar->close();
					unlink($_FILES["import_file"]["tmp_name"]);
					break;

				case 'zip':
					// Проверить доступность библиотеки для работы с ZIP
					$zip = new ZipArchive();

					if ( $zip->open($_FILES["import_file"]["tmp_name"]) === TRUE ) {
						$dbffile = "";

						for ( $i = 0; $i < $zip->numFiles; $i++ ) {
							$filename = $zip->getNameIndex($i);

							if ( preg_match('/.*\.dbf/i', $filename) ) {
								$dbffile = $filename;
								break;
							}
						}

						$zip->extractTo($upload_path);
						$zip->close();
					}

					unlink($_FILES["import_file"]["tmp_name"]);
					break;
			}

			if ( empty($dbffile) ) {
				throw new Exception('Файл не является архивом реестра.');
			}

			$handler = dbase_open($upload_path . $dbffile, 0);

			if ( !$handler ) {
				throw new Exception('Не удается открыть dbf-файл!');
			}

			$record_count = dbase_numrecords($handler);

			// throw new Exception($record_count);

			// посылаем ответ клиенту...
			ob_start();
			echo json_encode(array('success' => 'true', 'Alert_Msg' => 'Импорт производится в фоновом режиме'));

			$size = ob_get_length();

			header("Content-Length: $size");
			header("Content-Encoding: none");
			header("Connection: close");

			ob_end_flush();
			ob_flush();
			flush();

			if ( session_id() ) {
				session_write_close();
			}

			//$record_count = 10000;

			for ( $i = 1; $i <= $record_count; $i++ ) {
				$record = dbase_get_record_with_names($handler, $i);
				array_walk($record, 'ConvertFromWin866ToUtf8');

				$query = "
					declare
						@Res bigint,
						@Error_Code bigint,
						@Error_Message varchar(4000);

					exec r58.p_TariffValueTmp_ins
						@TariffValueTmp_id = @Res output,
						@TariffValue_Code = :TariffValue_Code,
						@TariffValue_Value = :TariffValue_Value,
						@TariffValue_begDT = :TariffValue_begDT,
						@TariffValue_endDT = :TariffValue_endDT,
						@TariffValueTmp_Ident = :TariffValueTmp_Ident,
						@pmUser_id = :pmUser_id,
						@Error_Code = @Error_Code output,
						@Error_Message = @Error_Message output;

					select @Error_Code as Error_Code, @Error_Message as Error_Msg;
				";

				$queryParams = array(
					'TariffValue_Code' => (!empty($record['C_TAR']) ? $record['C_TAR'] : null),
					'TariffValue_Value' => (!empty($record['SUMM_TAR']) ? $record['SUMM_TAR'] : null),
					'TariffValue_begDT' => (!empty($record['DATE_B']) ? $record['DATE_B'] : null),
					'TariffValue_endDT' => (!empty($record['DATE_E']) ? $record['DATE_E'] : null),
					'TariffValueTmp_Ident' => $timestamp,
					'pmUser_id' => $data['pmUser_id'],
				);

				$insResult = $this->queryResult($query, $queryParams);

				if ( $insResult === false ) {
					dbase_close($handler);
					throw new Exception('Ошибка при выполнении запроса к БД (добавление записей)');
				}
				else if ( !empty($insResult[0]['Error_Msg']) ) {
					dbase_close($handler);
					throw new Exception($insResult[0]['Error_Msg']);
				}
			}

			dbase_close($handler);

			$messageData[] = "[" . date('d.m.Y H:i:s') . "] Данные загружены. Запуск обработки...";

			$processResult = $this->queryResult("
				declare
					@Error_Code bigint,
					@Error_Message varchar(4000);

				set nocount on;

				begin try
					update tvt with (rowlock)
					set TariffValue_id = tv.TariffValue_id
					from r58.TariffValueTmp tvt
						inner join r58.TariffValue tv with (nolock) on tv.TariffValue_Code = tvt.TariffValue_Code
							and tv.TariffValue_begDT = tvt.TariffValue_begDT
					where tvt.TariffValueTmp_Ident = :TariffValueTmp_Ident

					update tv with (rowlock)
					set TariffValue_Value = tvt.TariffValue_Value,
						TariffValue_endDT = tvt.TariffValue_endDT,
						TariffValue_updDT = tvt.TariffValueTmp_updDT,
						pmUser_updID = tvt.pmUser_updID
					from r58.TariffValue tv
						inner join r58.TariffValueTmp tvt with (nolock) on tvt.TariffValue_id = tv.TariffValue_id
					where tvt.TariffValueTmp_Ident = :TariffValueTmp_Ident
						and ISNULL(tv.TariffValue_Value, 0) != ISNULL(tvt.TariffValue_Value, 0)

					update tv with (rowlock)
					set TariffValue_Value = tvt.TariffValue_Value,
						TariffValue_endDT = tvt.TariffValue_endDT,
						TariffValue_updDT = tvt.TariffValueTmp_updDT,
						pmUser_updID = tvt.pmUser_updID
					from r58.TariffValue tv
						inner join r58.TariffValueTmp tvt with (nolock) on tvt.TariffValue_id = tv.TariffValue_id
					where tvt.TariffValueTmp_Ident = :TariffValueTmp_Ident
						and tv.TariffValue_endDT != tvt.TariffValue_endDT

					insert into r58.TariffValue (TariffValue_Code, TariffValue_Value, TariffValue_begDT, TariffValue_endDT,
						pmUser_insID, pmUser_updID, TariffValue_insDT, TariffValue_updDT)
					select
						TariffValue_Code,
						TariffValue_Value,
						TariffValue_begDT,
						TariffValue_endDT,
						pmUser_insID,
						pmUser_updID,
						TariffValueTmp_insDT,
						TariffValueTmp_updDT
					from r58.TariffValueTmp tvt with (nolock)
					where tvt.TariffValueTmp_Ident = :TariffValueTmp_Ident
						and tvt.TariffValue_id is null

					delete from r58.TariffValueTmp with (rowlock) where TariffValueTmp_Ident = :TariffValueTmp_Ident
				end try

				begin catch
					set @Error_Code = error_number();
					set @Error_Message = error_message();
				end catch

				set nocount off;

				select @Error_Code as Error_Code, @Error_Message as Error_Msg;
			", array(
				'TariffValueTmp_Ident' => $timestamp,
			));

			if ( $processResult === false ) {
				throw new Exception('Ошибка при выполнении запроса к БД (обработка)');
			}
			else if ( !empty($processResult[0]['Error_Msg']) ) {
				throw new Exception($processResult[0]['Error_Msg']);
			}

			$messageData[] = "[" . date('d.m.Y H:i:s') . "] Импорт тарифов ТФОМС завершен. Обработано записей: " . $record_count . ".";

			$response['Error_Msg'] = 'Тарифы успешно загружены и обработаны';
		}
		catch ( Exception $e ) {
			$response['Error_Msg'] = $e->getMessage();
			$messageData[] = "[" . date('d.m.Y H:i:s') . "] " . $e->getMessage() . ".";
		}

		$this->Messages_model->autoMessage(array(
			'autotype' => 1,
			'title' => 'Импорт тарифов ТФОМС',
			'type' => 1,
			'User_rid' => $data['pmUser_id'],
			'pmUser_id' => $data['pmUser_id'],
			'text' => "<div>" . implode("</div><div>", $messageData) . "</div>",
		));

		return array($response);
	}

	/**
	 * Возвращает список тарифов
	 */
	public function loadList($data) {
		$filterList = array('(1 = 1)');
		$joinList = array();
		$queryParams = array();
		$variables = '';

		if ( !isSuperAdmin() ) {
			$data['Lpu_id'] = $data['session']['lpu_id'];
		}

		if ( !empty($data['Lpu_id']) ) {
			$queryParams['Lpu_id'] = $data['Lpu_id'];
			$variables = 'declare @Lpu_RegNomN2 varchar(3) = (select top 1 Lpu_RegNomN2 from v_Lpu with (nolock) where Lpu_id = :Lpu_id);';
			$filterList[] = "left(TariffValue_Code, 3) = @Lpu_RegNomN2";
		}

		if ( !empty($data['TariffValue_Code']) ) {
			$queryParams['TariffValue_Code'] = $data['TariffValue_Code'];
			$filterList[] = "TariffValue_Code = :TariffValue_Code";
		}

		if ( !empty($data['TariffValue_begDT_From']) ) {
			$queryParams['TariffValue_begDT_From'] = $data['TariffValue_begDT_From'];
			$filterList[] = "TariffValue_begDT >= :TariffValue_begDT_From";
		}

		if ( !empty($data['TariffValue_begDT_To']) ) {
			$queryParams['TariffValue_begDT_To'] = $data['TariffValue_begDT_To'];
			$filterList[] = "TariffValue_begDT <= :TariffValue_begDT_To";
		}

		if ( !empty($data['TariffValue_endDT_From']) ) {
			$queryParams['TariffValue_endDT_From'] = $data['TariffValue_endDT_From'];
			$filterList[] = "TariffValue_endDT >= :TariffValue_endDT_From";
		}

		if ( !empty($data['TariffValue_endDT_To']) ) {
			$queryParams['TariffValue_endDT_To'] = $data['TariffValue_endDT_To'];
			$filterList[] = "TariffValue_endDT <= :TariffValue_endDT_To";
		}

		if ( !empty($data['TariffValue_Code']) ) {
			$queryParams['TariffValue_Code'] = $data['TariffValue_Code'];
			$filterList[] = "TariffValue_Code = :TariffValue_Code";
		}

		$query = (!empty($variables) ? "
			-- variables
			{$variables}
			-- end variables

		" : "") . "
			select
				-- select
				 TariffValue_id
				,TariffValue_Code
				,TariffValue_Value
				,convert(varchar(10), TariffValue_begDT, 104) as TariffValue_begDT
				,convert(varchar(10), TariffValue_endDT, 104) as TariffValue_endDT
				-- end select
			from
				-- from
				r58.v_TariffValue with (nolock)
				-- end from
			where
				-- where
				" . implode(' and ', $filterList) . "
				-- end where
			order by
				-- order by
				TariffValue_Code,
				TariffValue_begDT
				-- end order by
		";

		$response = $this->getPagingResponse($query, $queryParams, $data['start'], $data['limit'], true, true);

		return $response;
	}

	/**
	 * Возвращает тариф ТФОМС
	 */
	public function load($data) {
		return $this->queryResult("
			select top 1
				TariffValue_id
				,TariffValue_Code
				,TariffValue_Value
				,convert(varchar(10), TariffValue_begDT, 104) as TariffValue_begDT
				,convert(varchar(10), TariffValue_endDT, 104) as TariffValue_endDT
			from
				r58.v_TariffValue with (nolock)
			where
				TariffValue_id = :TariffValue_id
		", $data);
	}

	/**
	 * Сохраняет повод обращения
	 */
	public function save($data) {
		try {
			// начнем транзакцию
			$this->beginTransaction();

			if ( !isSuperAdmin() && $this->regionNick != 'vologda') {
				throw new Exception('Доступ на изменение запрещен');
			}

			// проверим даты
			if ( $data['TariffValue_begDT'] > $data['TariffValue_endDT'] ) {
				throw new Exception('Дата начала не может быть больше даты окончания');
			}

			$action = (empty($data['TariffValue_id']) ? 'ins' : 'upd');

			// Проверяем уникальность кода
			$checkTariffValueCode = $this->getFirstResultFromQuery("
				select top 1 TariffValue_id
				from r58.v_TariffValue with (nolock)
				where TariffValue_Code = :TariffValue_Code
					and TariffValue_id != ISNULL(:TariffValue_id, 0)
					and TariffValue_begDT <= cast(:TariffValue_endDT as datetime)
					and cast(:TariffValue_begDT as datetime) <= TariffValue_endDT
			", $data, true);

			if ( $checkTariffValueCode === false ) {
				throw new Exception('Ошибка при выполнении запроса к БД (' . __LINE__ . ')');
			}

			if ( !empty($checkTariffValueCode) ) {
				throw new Exception('Код тарифа должен быть уникальным в определенный период времени.');
			}

			$query = "
				declare
					@TariffValue_id bigint = :TariffValue_id,
					@Error_Code bigint,
					@Error_Message varchar(4000);

				exec r58.p_TariffValue_{$action}
					@TariffValue_id = @TariffValue_id output,
					@TariffValue_Code = :TariffValue_Code,
					@TariffValue_Value = :TariffValue_Value,
					@TariffValue_begDT = :TariffValue_begDT,
					@TariffValue_endDT = :TariffValue_endDT,
					@pmUser_id = :pmUser_id,
					@Error_Code = @Error_Code output,
					@Error_Message = @Error_Message output;

				select @TariffValue_id as TariffValue_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
			";

			$response = $this->queryResult($query, $data);

			if ( !empty($response[0]['Error_Msg']) ) {
				throw new Exception($response[0]['Error_Msg']);
			}

			$this->commitTransaction() ;
		}
		catch ( Exception $e ) {
			$this->rollbackTransaction() ;
			$response = array(array('Error_Msg' => $e->getMessage()));
		}

		return $response;
	}
}