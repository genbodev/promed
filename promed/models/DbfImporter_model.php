<?php
class DbfImporter_model extends swModel {
	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
		$this->load->database();
		$this->load->model('RegisterListLog_model', 'RegisterListLog_model');
		$this->load->model('RegisterListDetailLog_model', 'RegisterListDetailLog_model');
		$this->load->library('textlog', array('file' => 'ImportSchema.log'));
	}

	private $recordCount;
	public $schemaName = 'stg';

	/**
	 * Загрузка 4.0
	 * @param type $files
	 * @param type $RegisterListLog_id
	 * @param type $pmUser_id 
	 */
	public function importFRL($files, $RegisterListLog_id, $pmUser_id, $ignoredelete = false) {

		ini_set("memory_limit","2048M");

        if($ignoredelete == false) {
			$registerListLog_id_list = $this->queryList('SELECT distinct RegisterListLog_id FROM stg.frlPrivilege with (rowlock)');
			if(is_array($registerListLog_id_list) && count($registerListLog_id_list)>0){
				if (
					TRUE !== $this->db->query("delete from [stg].[frlPrivilege] with (rowlock) WHERE RegisterListLog_id in (".implode(', ', $registerListLog_id_list).")") 
					|| 
					TRUE !== $this->db->query("delete from  [stg].[frlPerson] with (rowlock) WHERE RegisterListLog_id in (".implode(', ', $registerListLog_id_list).")")
				){
					throw new Exception('Не удалось очистить таблицу перед импортом');
				}
			}
        }

        $this->db->save_queries = false;


		$d = 0;
		$b = 0;
		$bad = array();
        $qwe = 0;
		try{
			// Добавляем льготополучателя
			foreach ($files as $key => $value) {
				$qwe++;

				switch(true) {
					case ($this->getRegionNick() == 'krym' && $value['c_doc'] == 'ПАСПОРТ УКР'):
					case ($this->getRegionNick() == 'krym' && $value['c_doc'] == 'ПАСПОРТГУ'):
						$res = $this->db->query("SELECT [DocumentType_id],[DocumentType_MaskSer],[DocumentType_MaskNum] FROM [dbo].[DocumentType] with (nolock) where DocumentType_SysNick =  :s", array("s" => 'ИНПАСПОРТ'));
						$res = $res->first_row();
						$value["c_doc"] = is_object($res)?$res->DocumentType_id:null;
						if (empty($value["frlPerson_Nationality"])) {
							$value["frlPerson_Nationality"] = "УКРАИНА";
						}
					break;

					case ($this->getRegionNick() == 'krym' && $value['c_doc'] == 'СВИД О РОЖД' && strtotime($value['dr']) < strtotime('2014-03-18')):
						$res = $this->db->query("SELECT [DocumentType_id],[DocumentType_MaskSer],[DocumentType_MaskNum] FROM [dbo].[DocumentType] with (nolock) where DocumentType_SysNick =  :s", array("s" => 'СВИД О РОЖ НЕ РФ'));
						$res = $res->first_row();
						$value["c_doc"] = is_object($res)?$res->DocumentType_id:null;
					break;

					default:
						$res = $this->db->query("SELECT [DocumentType_id],[DocumentType_MaskSer],[DocumentType_MaskNum] FROM [dbo].[DocumentType] with (nolock) where DocumentType_SysNick =  :s", array("s" => $value["c_doc"]));
						$res = $res->first_row();
						$value["c_doc"] = is_object($res)?$res->DocumentType_id:null;
				}

				if($value["c_doc"]==null){
					$bad[$b]['fam']=$value["fam"];
					$bad[$b]['im']=$value["im"];
					$bad[$b]['ot']=$value["ot"];
					$bad[$b]['dr']=$value["dr"];
					$b++;
					continue;
				}
				else {
					if($res->DocumentType_MaskSer!=null){
						preg_match("/".$res->DocumentType_MaskSer."/", $value["s_doc"], $ser);
						if(count($ser)==0){
							$value["dt_doc"]=null;
						}
					}
					preg_match("/".$res->DocumentType_MaskNum."/", $value["n_doc"], $num);
					if(count($num)==0){
						$value["dt_doc"]=null;
					}
				}

				$d++;
				$q = "
					declare
						@Err_Msg varchar(400),
						@Err_Code int,
						@Res bigint;
					exec stg.p_frlPerson_ins
						@frlPerson_id = @Res output,
						@sn_doc = :sn_doc,
						@frlPerson_IsPensionPFR = :frlPerson_IsPensionPFR,
						@frlPerson_Summ = :frlPerson_Summ,
						@frlPerson_Phone = :frlPerson_Phone,
						@frlPerson_Number = :frlPerson_Number,
						@frlPerson_Rezerv = :frlPerson_Rezerv,
						@frlPerson_Descr = :frlPerson_Descr,
						@frlPerson_BirthdayNoStand = :frlPerson_BirthdayNoStand,
						@frlPerson_IsRefuseNext2 = :frlPerson_IsRefuseNext2,
						@frlPerson_IsRefuseNext3 = :frlPerson_IsRefuseNext3,
						@frlPerson_IsRefuse2 = :frlPerson_IsRefuse2,
						@frlPerson_IsRefuse3 = :frlPerson_IsRefuse3,
						@frlPerson_KolvoGSP = :frlPerson_KolvoGSP,
						@adr_fact = :adr_fact,
						@frlPerson_CodeAccom = :frlPerson_CodeAccom,
						@frlPerson_Nationality = :frlPerson_Nationality,
						@frlPerson_CodeReg = :frlPerson_CodeReg,
						@frlPerson_CodeArea = :frlPerson_CodeArea,
						@frlPerson_ChangeCode = :frlPerson_ChangeCode,
						@db_edv = :db_edv,
						@de_edv = :de_edv,
						@s_doc = :s_doc,
						@n_doc = :n_doc,
						@dt_doc = :dt_doc,
						@o_doc = :o_doc,
						@adr_type = :adr_type,
						@ss = :ss,
						@fam = :fam,
						@im = :im,
						@ot = :ot,
						@w = :w,
						@dr = :dr,
						@c_doc = :c_doc,
						@adres = :adres,
						@c_kat1 = :c_kat1,
						@c_kat2 = :c_kat2,
						@frlPerson_isRefuse = :frlPerson_isRefuse,
						@frlPerson_isRefuseNext = :frlPerson_isRefuseNext,
						@date_rsb = :date_rsb,
						@date_rse = :date_rse,
						@RegisterListLog_id = :RegisterListLog_id,
						@pmUser_id = :pmUser_id,
						@Error_Code = @Err_Code output,
						@Error_Message = @Err_Code output;
					select @Res as frlPerson_id, @Err_Code as Error_Code, @Err_Msg as Error_Msg;
				";
				$p = array(
					"sn_doc"=>$value['sn_doc'],
					"frlPerson_IsPensionPFR"=>$value['frlPerson_IsPensionPFR'],
					"frlPerson_Summ"=>$value['frlPerson_Summ'],
					"frlPerson_Phone"=>$value['frlPerson_Phone'],
					"frlPerson_Number"=>$value['frlPerson_Number'],
					"frlPerson_Rezerv"=>$value['frlPerson_Rezerv'],
					"frlPerson_Descr"=>$value['frlPerson_Descr'],
					"frlPerson_BirthdayNoStand"=>$value['frlPerson_BirthdayNoStand'],
					"frlPerson_IsRefuseNext2"=>$value['frlPerson_IsRefuseNext2'],
					"frlPerson_IsRefuseNext3"=>$value['frlPerson_IsRefuseNext3'],
					"frlPerson_IsRefuse2"=>$value['frlPerson_IsRefuse2'],
					"frlPerson_IsRefuse3"=>$value['frlPerson_IsRefuse3'],
					"frlPerson_KolvoGSP"=>$value['frlPerson_KolvoGSP'],
					"adr_fact"=>$value['adr_fact'],
					"frlPerson_CodeAccom"=>$value['frlPerson_CodeAccom'],
					"frlPerson_Nationality"=>$value['frlPerson_Nationality'],
					"frlPerson_CodeReg"=>$value['frlPerson_CodeReg'],
					"frlPerson_CodeArea"=>$value['frlPerson_CodeArea'],
					"frlPerson_ChangeCode"=>$value['frlPerson_ChangeCode'],
					"db_edv"=>$value['db_edv'],
					"de_edv"=>$value['de_edv'],
					"s_doc"=> $value["s_doc"],
					"n_doc"=> $value["n_doc"],
					"dt_doc"=> $value["dt_doc"],
					"o_doc"=> $value["o_doc"],
					"adr_type"=> $value["adr_type"],
					"ss" => $value["ss"],
					"fam" => $value["fam"],
					"im" => $value["im"],
					"ot" => $value["ot"],
					"w" => $value["w"],
					"dr" => $value["dr"],
					"c_doc" => $value["c_doc"],
					"adres" => $value["adres"],
					"c_kat1" => $value["c_kat1"],
					"c_kat2" => $value["c_kat2"],
					"frlPerson_isRefuse"=>$value["isRefuse"],
					"frlPerson_isRefuseNext"=>$value["isRefuseNext"],
					"date_rsb" => $value["date_rsb"],
					"date_rse" => $value["date_rse"],
					"RegisterListLog_id" => $RegisterListLog_id,
					"pmUser_id" => $pmUser_id,
				);

				$frlPersonResp = $this->queryResult($q, $p);

				if ( ! $this->isSuccessful($frlPersonResp)) {
					continue;
				}

				// Добавляем льготы
				foreach ($value["L"] as $n => $val) {
					$d++;
					$q = "
						declare
							@Err_Msg varchar(400),
							@Err_Code int,
							@Res bigint;
						exec stg.p_frlPrivilege_ins
							@frlPrivilege_id = @Res output,
							@frlPrivilege_isWhoGSP = :frlPrivilege_isWhoGSP,
							@frlPrivilege_Rezerv = :frlPrivilege_Rezerv,
							@ss = :ss,
							@c_katl = :c_katl,
							@name_dl = :name_dl,
							@s_dl = :s_dl,
							@n_dl = :n_dl,
							@date_bl = :date_bl,
							@sn_dl = :sn_dl,
							@date_el = :date_el,
							@frlPrivilege_Org = :frlPrivilege_Org,
							@frlPerson_id = :frlPerson_id,
							@RegisterListLog_id = :RegisterListLog_id,
							@pmUser_id = :pmUser_id,
							@Error_Code = @Err_Code output,
							@Error_Message = @Err_Code output;
						select @Res as frlPrivilege_id, @Err_Code as Error_Code, @Err_Msg as Error_Msg;
					";
					$p = array(
						"frlPrivilege_isWhoGSP"=>$val['frlPrivilege_isWhoGSP'],
						"frlPrivilege_Rezerv"=>$val['frlPrivilege_Rezerv'],
						"ss" => $val["ss"],
						"c_katl" => $val["c_katl"],
						"name_dl" => $val["name_dl"],
						"s_dl" => $val["s_dl"],
						"n_dl" => $val["n_dl"],
						"date_bl" => $val["date_bl"],
						"sn_dl" =>$val['sn_dl'],
						"date_el" => $val["date_el"],
						"frlPrivilege_Org" => $val["Org"],
						"frlPerson_id" => $frlPersonResp[0]["frlPerson_id"],
						"RegisterListLog_id" => $RegisterListLog_id,
						"pmUser_id" => $pmUser_id,

					);

					$frlPrivilegeResp = $this->queryResult($q, $p);
				}
			}
		} catch(Exception $ex){
			$this->textlog->add("Ошибка при insert: ".$ex->getMessage());
			throw new Exception("Ошибка при внесении информации: ".$ex->getMessage());
		}
		$badPeople = '';	//Строка для вывода списка ошибочных людей, либо ссылки на файл со списком
		if (count($bad) > 30) {
			$str_arr = array();
			foreach($bad as $val) {
				$str_arr[] = "{$val['fam']} {$val['im']} {$val['ot']} {$val['dr']}\n";
			}

			$out_dir = EXPORTPATH_ROOT.'frl_import/errors_'.time();
			mkdir($out_dir, 0777, true);
			$errorFile = $out_dir . '/badPeople.txt';
			file_put_contents($errorFile, $str_arr);

			$badPeople = "&nbsp;<a href='{$errorFile}' target='_blank'>скачать<a>";
		} else {
			$badPeople = "<ul>";
			foreach($bad as $val){
				$badPeople.="<li>".$val['fam'].' '.$val['im'].' '.$val['ot']."</li>";
			}
			$badPeople.="</ul>";
		}
		RegisterListDetailLog_model::createLogMessage(new DateTime(), 1, "Список ошибочных людей:".$badPeople, $this->RegisterListLog_model->getRegisterListLog_id(), 1); 

		$this->recordCount = $d;

	}



	// отказе от льгот

	/**
	 * загрузка 5.0
	 * @param string $dbfFile DBF-файл-источник
	 * @param string $tableName Название таблицы
	 * @param array $fields_mapping Соответствие полей "поле источника (DBF)" => "поле назначения (mssql)". Если не указано считается сквозной.
	 * @param bool $dropTable Дропнуть если такая таблица уже есть
	 * @param bool $clearTable Очистить (truncate) если таблица уже есть
	 * @param callable $onEveryRecord
	 * @throws Exception
	 */
	public function import($RegisterListLog_id, $dbfFile, $tableName, $fields_mapping = array(), $dropTable = false, $clearTable = false, Closure $onEveryRecord = null) {
		if (!is_file($dbfFile)) {
			throw new Exception("$dbfFile не найден");
		}
		$dbf = dbase_open($dbfFile, 0);
		if ($dbf === FALSE) {
			throw new Exception("Не удалось открыть файл $dbfFile");
		}
		$dbf_header = dbase_get_header_info($dbf);
		if ($dbf_header === FALSE) {
			throw new Exception("Информация в заголовке базы данных $dbfFile не может быть прочитана");
		}
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
		
		if ($this->exists($tableName)) {
			if ($dropTable) {
				if (TRUE !== $this->db->query("drop table [stg].[{$tableName}]")) {
					throw new Exception("Не удалось drop table [stg].[{$tableName}]");
				}
			}
		} else {
			$ddl = 'CREATE TABLE [stg].[' . $tableName . '](' . implode(',' . PHP_EOL, $ddl) . ')';
			$this->db->query($ddl);
			if (!$this->exists($tableName)) {
				throw new Exception("Не удалось создать $tableName");
			}
		}
		$this->recordCount = dbase_numrecords($dbf);
		// $this->recordCount = 10; // TO-DO убрать нужно обязательн!!
		$fields_mapping['RegisterListLog_id'] = 'RegisterListLog_id';
		$fields_mapping[$tableName . '_insDT'] = $tableName . '_insDT';
		$fields_mapping[$tableName . '_updDT'] = $tableName . '_updDT';
		$fields_mapping['pmUser_insID'] = 'pmUser_insID';
		$fields_mapping['pmUser_updID'] = 'pmUser_updID';

		$destination_fields = '[' . implode('],[', array_values($fields_mapping)) . ']';
		RegisterListDetailLog_model::createLogMessage(new DateTime(), 1, 'Очищаем таблицу '.$tableName.' - ' . memory_get_usage(), $this->RegisterListLog_model->getRegisterListLog_id(), 1);
		if ($clearTable) {
			$this->textlog->add("clear Table version 5.0");
			if (TRUE !== $this->db->query('delete from [stg].[' . $tableName . '] with (rowlock)')) {
				throw new Exception('Не удалось очистить таблицу перед импортом');
			}
		}
		$insert = 'INSERT INTO [stg].[' . $tableName . ']' . ' (' . $destination_fields . ') VALUES (?' . str_repeat(',?', count($dbf_header) + 4) . ');';
		RegisterListDetailLog_model::createLogMessage(new DateTime(), 1, 'Заносим данные в таблицу '.$tableName.' - ' . memory_get_usage(), $this->RegisterListLog_model->getRegisterListLog_id(), 1);
		for ($i = 1; $i <= $this->recordCount; $i++) {
			if ((is_object($onEveryRecord) and ($onEveryRecord instanceof Closure))) {
				$onEveryRecord($i, $this->recordCount);
			}
			$row = dbase_get_record_with_names($dbf, $i);
			$values = array();
			foreach ($conv as $f_idx) {
				$row[$f_idx] = iconv('cp866', 'UTF-8', $row[$f_idx]);
			}
			$row['RegisterListLog_id'] = $RegisterListLog_id;
			$row[$tableName . '_insDT'] = date('Y-m-d H:i:s');
			$row[$tableName . '_updDT'] = date('Y-m-d H:i:s');
			$row['pmUser_insID'] = 1;
			$row['pmUser_updID'] = 1;
			foreach ($fields_mapping as $source_field => $destination_field) {
				$values[] = $row[$source_field];
			}
			$res = $this->db->query($insert, $values);
			$this->db->queries = array(); // очищаем запросы чтобы не забивать память
			$this->db->query_times = array(); // очищаем запросы чтобы не забивать память
			if (TRUE !== $res) {
				throw new Exception("Ошибка вставки строки $i");
			}
			unset($res);
		}
		dbase_close($dbf);
	}

	/**
	 * @param int $RegisterListLog_id
	 */
	function linkBySNILS($RegisterListLog_id) {
		//Проставить frlPerson_id в stg.frlPrivilege
		$params = array('RegisterListLog_id' => $RegisterListLog_id);
		$query = "
			declare @Error_Code bigint = null
			declare @Error_Message varchar(4000) = ''
			set nocount on
			begin try
				update stg.frlPrivilege
				set frlPerson_id = Pers.frlPerson_id
				from stg.frlPrivilege Priv
				inner join stg.frlPerson Pers on Pers.ss = Priv.ss 
					and Pers.RegisterListLog_id = :RegisterListLog_id
				where Priv.frlPerson_id is null and Priv.RegisterListLog_id = :RegisterListLog_id
			end try
			begin catch
				set @Error_Code = error_number()
				set @Error_Message = error_message()
			end catch
			set nocount off
			select @Error_Code as Error_Code, @Error_Message as Error_Msg
		";
		return $this->queryResult($query, $params);
	}

	/**
	 * незнаю
	 * @param type $tableName
	 * @return type 
	 */
	public function exists($tableName) {
		$q = <<<Q

SELECT
	COUNT(*)
FROM
	sys.all_objects o with(nolock),
	sys.schemas s with(nolock)
WHERE
	o.schema_id = s.schema_id
	AND s.NAME = :schemaName
	AND o.name = :tableName

Q;
		return (1 == $this->getFirstResultFromQuery($q, array(
					'schemaName' => $this->schemaName,
					'tableName' => $tableName
				)));
	}

	/**
	 * Подсчет загруженых записей.
	 * @return type 
	 */
	public function getRecordCount() {
		return $this->recordCount;
	}

}