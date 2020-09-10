<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * EGISSOReceptExport_model - модель Журнала ручного экспорта МСЗ
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Admin
 * @access			public
 * @copyright		Copyright (c) 2019 Swan Ltd.
 */

class EGISSOReceptExport_model extends SwPgModel {

	protected $id;
	protected $repdir;

	/**
	 * Список / поиск
	 */
	function loadList($data) {

		$filter = '(1=1)';

		if (!empty($data['EGISSOReceptExport_setDT'])) $filter .= ' and ERE.EGISSOReceptExport_setDT = :EGISSOReceptExport_setDT';
		if (!empty($data['EGISSOReceptExport_begDT'])) $filter .= ' and ERE.EGISSOReceptExport_begDT = :EGISSOReceptExport_begDT';
		if (!empty($data['EGISSOReceptExport_endDT'])) $filter .= ' and ERE.EGISSOReceptExport_endDT = :EGISSOReceptExport_endDT';
		if (!empty($data['EGISSOReceptExport_isNew'])) $filter .= ' and ERE.EGISSOReceptExport_isNew = :EGISSOReceptExport_isNew';
		if (!empty($data['EGISSOReceptExportStatus_id'])) $filter .= ' and ERE.EGISSOReceptExportStatus_id = :EGISSOReceptExportStatus_id';

		$query = "
			select
				-- select
				ERE.EGISSOReceptExport_id as \"EGISSOReceptExport_id\"
				,to_char (ERE.EGISSOReceptExport_setDT, 'dd.mm.yyyy') as \"EGISSOReceptExport_setDT\"
				,to_char (ERE.EGISSOReceptExport_begDT, 'dd.mm.yyyy') as \"EGISSOReceptExport_begDT\"
				,to_char (ERE.EGISSOReceptExport_endDT, 'dd.mm.yyyy') as \"EGISSOReceptExport_endDT\"
				,case when ERE.EGISSOReceptExport_isNew = 2 then 'true' else 'false' end as \"EGISSOReceptExport_isNew\"
				,ERE.EGISSOReceptExportStatus_id as \"EGISSOReceptExportStatus_id\"
				,ERES.EGISSOReceptExportStatus_Name as \"EGISSOReceptExportStatus_Name\"
				,ERE.EGISSOReceptExport_Error as \"EGISSOReceptExport_Error\"
				,ERE.EGISSOReceptExport_Result as \"EGISSOReceptExport_Result\"
				-- end select
			from
				-- from
				v_EGISSOReceptExport ERE
				left join v_EGISSOReceptExportStatus ERES on ERES.EGISSOReceptExportStatus_id = ERE.EGISSOReceptExportStatus_id
				-- end from
			where
				-- where
				{$filter}
				-- end where
			order by
				-- order by
				ERE.EGISSOReceptExport_id desc
				-- end order by
		";

		return $this->getPagingResponse($query, $data, $data['start'], $data['limit'], true);
	}

	/**
	 * Сохранение
	 */
	function save($data) {
		set_time_limit(0);

		$query = "
            select
                EGISSOReceptExport_id as \"EGISSOReceptExport_id\",
                Error_Code as \"Error_Code\",
                Error_Message as \"Error_Msg\"
			from p_EGISSOReceptExport_ins (
				EGISSOReceptExport_id := null,
				EGISSOReceptExport_setDT := cast(dbo.tzGetDate() as date),
				EGISSOReceptExport_begDT := :EGISSOReceptExport_begDT,
				EGISSOReceptExport_endDT := :EGISSOReceptExport_endDT,
				EGISSOReceptExport_isNew := :EGISSOReceptExport_isNew,
				EGISSOReceptExportStatus_id := 1,
				EGISSOReceptExport_Error := null,
				EGISSOReceptExport_Result := null,
				pmUser_id := :pmUser_id
				)
		";

		$result = $this->queryResult($query, $data);

		$this->sendResponse($result);

		if (is_array($result) && !empty($result[0]['EGISSOReceptExport_id'])) {
			$this->id = $result[0]['EGISSOReceptExport_id'];
			$this->doExport($data);
		}
	}

	/**
	 * Экспорт
	 */
	function doExport($data) {

		if (empty($this->id)) return false;

		$dbsearch = $this->load->database('search', true);

		$this->load->library('textlog', ['file' => 'EgissoReceptExport_' . date('Y-m-d') . '.log']);
		$this->textlog->add("Запуск экспорта (EGISSOReceptExport_id = {$this->id})");

		$filter = '';
		if ($data['EGISSOReceptExport_isNew'] == 2) {
			$filter .= " and er.EvnRecept_isExportMSZ is null ";
		}

		$query = "
			with t as (
				select case
					when pt.ReceptFinance_id = 1 and coalesce(er.EvnRecept_Is7Noz, 1) = 1 then 1
					when pt.ReceptFinance_id = 2 and coalesce(er.EvnRecept_Is7Noz, 1) = 1 then 2
					when er.EvnRecept_Is7Noz = 2 then 3
				end as paramReceptType
				from v_EvnRecept er
				inner join v_PrivilegeType pt on pt.PrivilegeType_id = er.PrivilegeType_id
				where
					er.EvnRecept_setDate between :EGISSOReceptExport_begDT and :EGISSOReceptExport_endDT
					and (
						(pt.ReceptFinance_id in (1,2) and coalesce(er.EvnRecept_Is7Noz, 1) = 1) or
						(er.ReceptFinance_id = 1 and er.EvnRecept_Is7Noz = 2)
					)
					{$filter}
			)

			select
				paramReceptType as \"paramReceptType\",
				count(*) as \"cnt\"
			from t
			group by paramReceptType
		";

		$pt_res = $this->queryResult($query, $data, $dbsearch);

		if (!count($pt_res)) {
			$this->saveError('Нет льготных рецептов, выписанных в периоде экспорта');
			$this->textlog->add("Экспорт завершён (Нет льготных рецептов, выписанных в периоде экспорта)");
			return false;
		};

		$this->repdir = EXPORTPATH_ROOT."egisso_recept/";
		if ( !is_dir($this->repdir) ) {
			mkdir($this->repdir, 0777, true);
		}

		$this->repdir .= time().'/';
		mkdir($this->repdir, 0777, true);

		$daterange = date('j.m.Y', strtotime($data['EGISSOReceptExport_begDT'])) . ' - ' . date('j.m.Y', strtotime($data['EGISSOReceptExport_endDT']));
		$daterange2 = date('j.m.Y', strtotime($data['EGISSOReceptExport_begDT'])) . '-' . date('j.m.Y', strtotime($data['EGISSOReceptExport_endDT']));

		$zipfilepath = $this->repdir . "Export_MSZ_{$daterange2}.zip";
		$zip = new ZipArchive;
		$zip->open($zipfilepath, ZIPARCHIVE::CREATE);

		$file_list = [];

		foreach($pt_res as $pt) {

			$params = [
				'paramReceptType' => $pt['paramReceptType'],
				'paramBegDate' => $data['EGISSOReceptExport_begDT'],
				'paramEndDate' => $data['EGISSOReceptExport_endDT'],
				'paramExportType' => $data['EGISSOReceptExport_isNew'] == 2 ? 1 : 2
			];

			if ($pt['paramReceptType'] == 1) {
				$elname = "Экспорт по фед. льготе за {$daterange}";
			} elseif ($pt['paramReceptType'] == 2) {
				$elname = "Экспорт по рег. льготе за {$daterange}";
			} else {
				$elname = "Экспорт по ВЗН за {$daterange}";
			}

			$zip->addEmptyDir(iconv("utf-8", "cp866", $elname));

			$pages = ceil($pt['cnt'] / 50000);

			for($i=1; $i <= $pages; $i++) {

				$block = ($pages == 1) ? '' : " - {$i}";
				$filename = "{$elname}{$block}.xlsx";

				$params['paramFileNum'] = $i;
				$params = http_build_query($params);

				$xlsfile = $this->getFile($params, $filename);

				if ($xlsfile === false) {
					$this->saveError('При экспорте произошла ошибка');
					return false;
				}

				$zip->AddFile($xlsfile['filepath'], iconv("utf-8", "cp866", $elname.'/'.$xlsfile['filename']));
				$file_list[] = $xlsfile['filepath'];
			}
		}

		$this->textlog->add(var_export($zip, true));

		$zip->close();

		foreach($file_list as $file) {
			@unlink($file);
		}

		if (!is_file($zipfilepath)) {
			$this->saveError('При экспорте произошла ошибка');
			return false;
		}

		$this->db->query("
			update EGISSOReceptExport
			set
				EGISSOReceptExportStatus_id = 2,
				EGISSOReceptExport_Result = :EGISSOReceptExport_Result,
				EGISSOReceptExport_updDT = dbo.tzGetDate()
			where
				EGISSOReceptExport_id = :EGISSOReceptExport_id
		", [
			'EGISSOReceptExport_Result' => $zipfilepath,
			'EGISSOReceptExport_id' => $this->id
		]);

		$this->textlog->add("Экспорт завершён");
	}

	/**
	 * Запуск отчёта на формирование
	 */
	function getFile($params, $filename) {

		$url = $this->getUrl($params);
		$this->textlog->add($url);

		try {

			$ch = curl_init();

			if($ch === false){
				$this->textlog->add("Ошибка при инициализации сеанса CURL");
				return false;
			}

			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)");
			curl_setopt($ch, CURLOPT_TIMEOUT, 28800);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_HEADER, 1);
			$str = curl_exec($ch);

			if (curl_errno($ch) == 28) {
				$this->textlog->add("СURL отвалился по таймауту. URL: ".$url);
				return false;
			}

			if($str === false){
				$this->textlog->add("Ошибка при выполнении запроса СURL. URL: ".$url);
				return false;
			}

			$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			$res = curl_getinfo($ch);
			curl_close($ch);

			if ( $str && $res['http_code'] == 200 ) {
				$php_eol = "\r\n";

				if ( !preg_match("/\r/", substr($str, 0, 200)) ) {
					$php_eol = "\n";
				}

				$this->textlog->add('Бирт: получили ответ (код 200)');
				$str = substr($str, strpos($str, $php_eol.$php_eol) + 4);

				$this->textlog->add('Получили из ответа имя файла отчета: сохраняем отчёт с именем '. $filename);
				$tmp_filepath = $this->repdir . time() . '.xlsx';

				//Записываем содержимое отчёта в файл
				file_put_contents($tmp_filepath, $str);

				return [
					'filename' => $filename,
					'filepath' => $tmp_filepath
				];
			} else {
				$this->textlog->add('Бирт: получили ошибку (код '.$res['http_code'].')');
				return false;
			}
		} catch (Exception $e) {
			$this->textlog->add('Формирование отчёта не удалось, критическая ошибка: '.$e->getMessage());
			return false;
		}
	}

	/**
	 * получение адреса до бирта с параметрами
	 */
	function getUrl($report_params) {
		$params = [];
		$host = BIRT_SERVLET_PATH_ABS.'preview?';
		$params[] = '__report=report/Uploading_to_PFR_pg.rptdesign';
		$params[] = toUTF(str_replace(" ", "%20", $report_params));
		$params[] = '__format=xlsx';
		return $host.join('&',$params);
	}

	/**
	 * Разрыв соединения c клиентом после запуска экспорта
	 */
	function sendResponse($response) {
		ignore_user_abort(true);

		if (function_exists('fastcgi_finish_request')) {
			echo json_encode($response);
			if (session_id()) session_write_close();
			fastcgi_finish_request();
		} else {
			ob_start();
			echo json_encode($response);

			$size = ob_get_length();

			header("Content-Length: $size");
			header("Content-Encoding: none");
			header("Connection: close");

			ob_end_flush();
			ob_flush();
			flush();

			if (session_id()) session_write_close();
		}
	}

	/**
	 * Сохранение ошибки
	 */
	function saveError($errText) {

		if (empty($this->id)) return false;

		$this->db->query("
			update EGISSOReceptExport
			set
				EGISSOReceptExportStatus_id = 3,
				EGISSOReceptExport_Error = :EGISSOReceptExport_Error,
				EGISSOReceptExport_updDT = dbo.tzGetDate()
			where
				EGISSOReceptExport_id = :EGISSOReceptExport_id
		", [
			'EGISSOReceptExport_Error' => $errText,
			'EGISSOReceptExport_id' => $this->id
		]);
	}
}
