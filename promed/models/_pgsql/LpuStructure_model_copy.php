<?php


class LpuStructure_model_copy
{
	/**
	 * @param LpuStructure_model $callObject
	 * @param $data
	 * @param null $UslugaComplex_pid
	 * @param null $UslugaComplex_id
	 * @return bool|CI_DB_result|mixed
	 */
	public static function copyUslugaFromSection(LpuStructure_model $callObject, $data, $UslugaComplex_pid = NULL, $UslugaComplex_id = NULL)
	{
		$andwhere = (empty($UslugaComplex_pid)) ? " and UslugaComplex_pid is null" : " and UslugaComplex_pid = :UslugaComplex_pid";
		if (isset($data["LpuSection_id"])) {
			$queryParams = [
				"LpuSection_id" => $data["LpuSection_id"],
				"LpuSection_pid" => $data["LpuSection_pid"],
				"pmUser_id" => $data["pmUser_id"],
				"Server_id" => $data["Server_id"],
				"UslugaComplex_pid" => $UslugaComplex_pid
			];
			$query = "
				select
					Server_id as \"Server_id\",
					UslugaComplex_id as \"UslugaComplex_id\",
					UslugaComplex_pid as \"UslugaComplex_pid\",
					Lpu_id as \"Lpu_id\",
					LpuSection_id as \"LpuSection_id\",
					UslugaComplex_ACode as \"UslugaComplex_ACode\",
					UslugaComplex_Code as \"UslugaComplex_Code\",
					UslugaComplex_Name as \"UslugaComplex_Name\",
					Usluga_id as \"Usluga_id\",
					RefValues_id as \"RefValues_id\",
					XmlTemplate_id as \"XmlTemplate_id\",
					UslugaGost_id as \"UslugaGost_id\",
					UslugaComplex_BeamLoad as \"UslugaComplex_BeamLoad\",
					UslugaComplex_UET as \"UslugaComplex_UET\",
					UslugaComplex_Cost as \"UslugaComplex_Cost\",
					UslugaComplex_DailyLimit as \"UslugaComplex_DailyLimit\",
					XmlTemplateSeparator_id as \"XmlTemplateSeparator_id\",
					pmUser_insID as \"pmUser_insID\",
					pmUser_updID as \"pmUser_updID\",
					UslugaComplex_insDT as \"UslugaComplex_insDT\",
					UslugaComplex_updDT as \"UslugaComplex_updDT\",
					UslugaComplex_isGenXml as \"UslugaComplex_isGenXml\",
					UslugaComplex_isAutoSum as \"UslugaComplex_isAutoSum\",
					LpuSectionProfile_id as \"LpuSectionProfile_id\",
					UslugaComplex_begDT as \"UslugaComplex_begDT\",
					UslugaComplex_endDT as \"UslugaComplex_endDT\",
					UslugaComplexLevel_id as \"UslugaComplexLevel_id\",
					UslugaComplex_SysNick as \"UslugaComplex_SysNick\",
					UslugaComplex_Nick as \"UslugaComplex_Nick\",
					UslugaCategory_id as \"UslugaCategory_id\",
					UslugaComplex_TFOMSid as \"UslugaComplex_TFOMSid\",
					UslugaComplex_2004id as \"UslugaComplex_2004id\",
					UslugaComplex_2011id as \"UslugaComplex_2011id\",
					UslugaComplex_slprofid as \"UslugaComplex_slprofid\",
					UslugaComplex_llprofid as \"UslugaComplex_llprofid\",
					UslugaKind_id as \"UslugaKind_id\",
					Report_id as \"Report_id\",
					Region_id as \"Region_id\",
					UslugaComplex_oid as \"UslugaComplex_oid\",
					ConsultationType_id as \"ConsultationType_id\"
				from UslugaComplex  
				where LpuSection_id = :LpuSection_pid
				{$andwhere}
			";
			/**@var CI_DB_result $res */
			$res = $callObject->db->query($query, $queryParams);
			$result = false;
			if (!is_object($res)) {
				return false;
			}
			$response = $res->result("array");
			if (is_array($response) && count($response) > 0) {
				$query = "
					select 
						uslugacomplex_id as \"UslugaComplex_id\",
						error_code as \"Error_Code\",
						error_message as \"Error_Msg\"
					from p_uslugacomplex_ins(
					    server_id := :Server_id,
					    uslugacomplex_pid := :UslugaComplex_pid,
					    lpu_id := :Lpu_id,
					    lpusection_id := :LpuSection_id,
					    uslugacomplex_acode := :UslugaComplex_ACode,
					    uslugacomplex_code := :UslugaComplex_Code,
					    uslugacomplex_name := :UslugaComplex_Name,
					    usluga_id := :Usluga_id,
					    refvalues_id := :RefValues_id,
					    xmltemplate_id := :XmlTemplate_id,
					    uslugagost_id := :UslugaGost_id,
					    uslugacomplex_beamload := :UslugaComplex_BeamLoad,
					    uslugacomplex_uet := :UslugaComplex_UET,
					    uslugacomplex_cost := :UslugaComplex_Cost,
					    uslugacomplex_dailylimit := :UslugaComplex_DailyLimit,
					    xmltemplateseparator_id := :XmlTemplateSeparator_id,
					    uslugacomplex_isgenxml := :UslugaComplex_isGenXml,
					    uslugacomplex_isautosum := :UslugaComplex_isAutoSum,
					    lpusectionprofile_id := :LpuSectionProfile_id,
					    uslugacomplex_begdt := :UslugaComplex_begDT,
					    uslugacomplex_enddt := :UslugaComplex_endDT,
					    pmuser_id := :pmUser_id
					);
				";
				foreach ($response as $usluga) {
					$queryParamsNew = array_merge($usluga, $queryParams);
					$queryParamsNew["UslugaComplex_pid"] = $UslugaComplex_id;
					$res = $callObject->db->query($query, $queryParamsNew);
					$result = $res;
					if (is_object($res)) {
						$responseNew = $res->result("array");
						if (is_array($responseNew[0]) && count($responseNew[0]) > 0) {
							if (empty($usluga["UslugaComplex_pid"])) {
								$callObject->copyUslugaFromSection($data, $usluga["UslugaComplex_id"], $responseNew[0]["UslugaComplex_id"]);
							}
						}
					}
				}

			}
			return $result;
		}
		if (isset($data['MedService_id'])) {
			$query = "
				select
					uc.UslugaComplex_id as \"UslugaComplex_id\",
					cast(uc.UslugaComplex_begDT as date) as \"UslugaComplex_begDT\",
					cast(uc.UslugaComplex_endDT as date) as \"UslugaComplex_endDT\"
				from v_UslugaComplex uc 
				where uc.LpuSection_id = :LpuSection_pid
				  and uc.UslugaComplex_pid is null
			";
			$queryParams = [
				"LpuSection_pid" => $data["LpuSection_pid"]
			];
			$result = $callObject->db->query($query, $queryParams);
			$error = null;
			if (!is_object($result)) {
				return false;
			}
			$callObject->load->model("MedService_model", "MedService_model");
			$response = $result->result("array");
			foreach ($response as $row) {
				$row["UslugaComplexMedService_id"] = 0;
				$row["MedService_id"] = $data["MedService_id"];
				$row["pmUser_id"] = $data["pmUser_id"];
				$res = $callObject->MedService_model->saveUslugaComplexMedService($row);
				if (empty($res)) {
					$error = "Ошибка запроса БД при копировании услуг отделения";
					break;
				}
				if (!empty($res[0]["Error_Msg"])) {
					$error = $res[0]["Error_Msg"];
					break;
				}
			}
			if (empty($error)) {
				return true;
			}
		}
		return false;
	}

	/**
	 * @param LpuStructure_model $callObject
	 * @param $data
	 * @return bool|CI_DB_result|mixed
	 */
	public static function copyUslugaSectionList(LpuStructure_model $callObject, $data)
	{
		if (isset($data["LpuSection_pid"])) {
			return $callObject->copyUslugaFromSection($data);
		}
		$result = null;
		if (isset($data["LpuSection_id"])) {
			$query = "
				insert into UslugaComplex (
					Server_id,
				    Lpu_id,
				    LpuSection_id,
				    UslugaComplex_Name,
					UslugaComplex_Code,
				    UslugaComplex_UET,
				    UslugaComplex_begDT,
				    Usluga_id,
				    pmUser_insID,
				    pmUser_updID,
				    UslugaComplex_insDT,
					UslugaComplex_updDT
				)
				select
				    :Server_id as Server_id,
				    UPL.Lpu_id as Lpu_id,
				    :LpuSection_id as LpuSection_id,
				    U.Usluga_Name as UslugaComplex_Name,
				    U.Usluga_Code as UslugaComplex_Code,
				    UPL.UslugaPriceList_Ue as UslugaComplex_UET,
				    ls.LpuSection_setDate as UslugaComplex_begDT,
				    UPL.Usluga_id as Usluga_id,
				    :pmUser_id as pmUser_insID,
				    :pmUser_id as pmUser_updID,
				    tzgetdate() as UslugaComplex_insDT,
				    tzgetdate() as UslugaComplex_updDT
				from
				    v_UslugaPriceList UPL
				    inner join v_LpuSection ls  on ls.LpuSection_id = :LpuSection_id
				    join v_Usluga U  on U.Usluga_id = UPL.Usluga_id
				where UPL.Lpu_id = :Lpu_id
			";
			$queryParams = [
				"LpuSection_id" => $data["LpuSection_id"],
				"Lpu_id" => $data["Lpu_id"],
				"pmUser_id" => $data["pmUser_id"],
				"Server_id" => $data["Server_id"]
			];
			$result = $callObject->db->query($query, $queryParams);
		}
		return (is_object($result)) ? true : false;
	}

	/**
	 * @param LpuStructure_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function ExportErmpStaff(LpuStructure_model $callObject, $data)
	{
		$params = [
			"Lpu_id" => $data["Lpu_id"]
		];
		$and = "";
		$and_DT = "";
		if (($data["Lpu_id"]) && ($data["Lpu_id"] != "100500")) {
			// 100500 - значение фильтра МО - "Все"
			$and .= " and s.Lpu_id = '{$data['Lpu_id']}'";
		}
		if ($data["ESESW_date"]) {
			$and_DT = " and ss.updDT >= '{$data['ESESW_date']}'";
		}
		$sql = "
			WITH zzz as (
			    SELECT
			        ltrim(rtrim(Lpu_Nick)) as UZ_Name,
			        coalesce(ORG_INN, '') as UZ_INN,
			        coalesce(ORG_KPP, '') as UZ_KPP,
			        coalesce(Org_OGRN, '') as UZ_OGRN,
			        coalesce(pt.PassportToken_tid, '') as UZ_OID,
			        '' as UZ_Type,
			        coalesce(ll.LpuSubjectionLevel_id, 0) as UZ_LPULevel_ID,
			        coalesce(ll.LpuSubjectionLevel_pid, 0) as UZ_LPULevel_Parent,
			        ltrim(rtrim(ll.LpuSubjectionLevel_name)) as UZ_LPULevel_Name,
			        l.LpuType_id as UZ_Nomen_ID,
			        coalesce(LpuType_pid, 0) as UZ_Nomen_Parent,
			        ltrim(rtrim(l.LpuType_Name)) as UZ_Nomen_Name,
			        coalesce(KL.id, kll.KLAdr_Code) as UZ_Municipality_ID,
			        coalesce(KL.name, kll.KLArea_Name) as UZ_Municipality_Name,
			        case when KL.id is not null then COALESCE(KL.parent, kll2.KLAdr_Code)
			             when KL.id is null then kll2.KLAdr_Code
			            end as UZ_Municipality_Parent,
			        case when KL.id is not null then coalesce(kl.prefix, '')
			             when KL.id is null then 'Город'
			            end as UZ_Municipality_Prefix,
			        sd.id as Branch_ID,
			        ltrim(rtrim(sd.name)) as Branch_Name,
			        coalesce(sd.parent, '') as Branch_Parent,
			        '' as Unit,
			        s.post_id,
			        s.rate,
			        '' as Comment
			    FROM
			        persis.v_staff s
			        left join persis.Staff ss on ss.id=s.id
			        LEFT JOIN LATERAL (
			            select p.FRMPSubdivision_id
			            from persis.WorkPlace p
			            where s.id = p.Staff_id
			              and p.IsDummyWP = 0
			            order by p.FRMPSubdivision_id desc
			            limit 1
			        ) as p on true
			        left join v_Lpu l on l.Lpu_id = s.Lpu_id
			        left join v_Org o on o.Org_id = l.org_id
			        left join LpuSubjectionLevel ll on ll.LpuSubjectionLevel_id = l.LpuSubjectionLevel_id
			        left join LpuType lt on lt.LpuType_id = l.LpuType_id
			        left join v_OrgServiceTerr OST on OST.Org_id = l.Org_id
			        left join KLArea kll on kll.KLArea_id = coalesce(OST.klcity_id, OST.kltown_id, ost.KLSubRgn_id, getmaincityregion())
			        left join v_KLArea kll2 on kll2.KLArea_id = kll.KLArea_pid
			        left join KLArea kl3 on kl3.KLArea_id = getmaincityregion()
			        left join persis.FRMPKladr kl on kl.id = COALESCE(kll.KLAdr_Code, kl3.KLAdr_Code)
			        left join fed.PassportToken pt on pt.Lpu_id = l.lpu_id
			        left join fed.PasportMO PMO on PMO.Lpu_id = l.lpu_id
			        LEFT JOIN LATERAL (
			            select sd.id,sd.name,sd.parent
			            from persis.FRMPSubdivision sd
			            where sd.id = p.FRMPSubdivision_id
			            limit 1
			        ) as sd on true
			    where sd.id is not null
			      and s.Rate<>0
			      and ll.LpuSubjectionLevel_name is not null
			      and o.ORG_KPP is not null
			      and l.Lpu_Nick not like '%закрыт%'
			      and l.Lpu_endDate is null
			      and coalesce(PMO.PasportMO_IsNoFRMP, '1') <> '2'
			        {$and}
			        {$and_DT}
			    ),
			yyy AS (
			    SELECT
			           persis.post.*,
			           persis.frmppost.fullname,
			           persis.frmppost.name AS name1,
			           persis.frmppost.parent AS parent1,
			           persis.frmppost.id AS idd
			    FROM
			        persis.post
			        INNER JOIN persis.frmppost ON persis.Post.frmpEntry_id = persis.FRMPPost.id
			)
			SELECT
			    uuid_generate_v4() as \"UZ_ID\",
			    UZ_Name as \"UZ_Name\",
			    UZ_INN as \"UZ_INN\",
			    UZ_KPP as \"UZ_KPP\",
			    UZ_OGRN as \"UZ_OGRN\",
			    UZ_OID as \"UZ_OID\",
			    UZ_Type as \"UZ_Type\",
			    UZ_LPULevel_ID as \"UZ_LPULevel_ID\",
			    UZ_LPULevel_Parent as \"UZ_LPULevel_Parent\",
			    UZ_LPULevel_Name as \"UZ_LPULevel_Name\",
			    UZ_Nomen_ID as \"UZ_Nomen_ID\",
			    UZ_Nomen_Parent as \"UZ_Nomen_Parent\",
			    UZ_Nomen_Name as \"UZ_Nomen_Name\",
			    UZ_Municipality_ID as \"UZ_Municipality_ID\",
			    UZ_Municipality_Name as \"UZ_Municipality_Name\",
			    UZ_Municipality_Parent as \"UZ_Municipality_Parent\",
			    UZ_Municipality_Prefix as \"UZ_Municipality_Prefix\",
			    Branch_ID as \"Branch_ID\",
			    Branch_Name as \"Branch_Name\",
			    Branch_Parent as \"Branch_Parent\",
			    Unit as \"Unit\",
			    Comment as \"Comment\",
			    coalesce(fp.idd, '') as \"StuffPost_ID\",
			    coalesce(fp.parent1, '') as \"StuffPost_Parent\",
			    ltrim(rtrim(fp.name1)) as \"StuffPost_Name\",
			    cast(sum(rate) as varchar(8)) as \"Quantity\"
			FROM
			    zzz
			    left join yyy fp on fp.id = post_id
			group by
				UZ_Name,UZ_INN,UZ_KPP,UZ_OGRN,UZ_OID,UZ_Type,UZ_LPULevel_ID,UZ_LPULevel_Parent,UZ_LPULevel_Name,UZ_Nomen_ID,UZ_Nomen_Parent,UZ_Nomen_Name,UZ_Municipality_ID,UZ_Municipality_Name,UZ_Municipality_Parent,UZ_Municipality_Prefix,Branch_ID,Branch_Name,Branch_Parent,Unit,Comment,fp.idd,fp.parent1,fp.name1
			order BY UZ_Name, ltrim(rtrim(fp.name1))
        ";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($sql, $params);
		if (!is_object($result)) {
			return false;
		}
		$res = $result->result("array");
		$temp = [];
		foreach ($res as &$item) {
			if ($item["StuffPost_ID"] != 0) {
				$temp[] = $item;
			}
		}
		return $temp;
	}

	/**
	 * @param LpuStructure_model $callObject
	 * @param $data
	 * @return bool
	 */
	public static function hasMedStaffFactInAIDSCenter(LpuStructure_model $callObject, $data)
	{
		$params = ["MedPersonal_id" => $data["MedPersonal_id"]];
		$query = "
			select count(*) as cnt
			from
				v_MedStaffFact MSF 
				inner join v_LpuBuilding LB on LB.LpuBuilding_id = MSF.LpuBuilding_id
			where MSF.MedPersonal_id = :MedPersonal_id
			  and coalesce(LB.LpuBuilding_IsAIDSCenter, 1) = 2
			limit 1
		";
		$count = $callObject->getFirstResultFromQuery($query, $params);
		if ($count === false) {
			return false;
		}
		return $count > 0;
	}

	/**
	 * @param LpuStructure_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function updMaxEmergencyBed(LpuStructure_model $callObject, $data)
	{
		$query = "
			update LpuSection
			set LpuSection_MaxEmergencyBed = :LpuSection_MaxEmergencyBed
			where LpuSection_id = :LpuSection_id
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $data);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * @param LpuStructure_model $callObject
	 * @param $data
	 * @param $files
	 * @return array
	 * @throws Exception
	 */
	public static function uploadOrgPhoto(LpuStructure_model $callObject, $data, $files)
	{
		/**
		 * Создание каталогов
		 * @param $path
		 * @return bool
		 */
		function createDir($path)
		{
			if (!is_dir($path)) {
				// Если нет корневой папки для хранения файлов организаций то создадим ее
				$success = mkdir($path, 0777);
				if (!$success) {
					throw new Exception("Не удалось создать папку '{$path}'");
				}
			}
			return true;
		}

		$callObject->load->helper("Image_helper");
		if (!defined("ORGSPATH") || !defined("ORGSPHOTOPATH")) {
			throw new Exception("Необходимо задать константы с указанием папок для загрузки файлов (config/promed,php): ORGSPATH и ORGSPHOTOPATH");
		}
		if (!isset($files["org_photo"])) {
			throw new Exception("Не удалось загрузить файл.");
		}
		$source = $files["org_photo"]["tmp_name"];
		// Если файл успешно загрузился в темповую директорию $source
		if (!is_uploaded_file($source)) {
			throw new Exception("Не удалось загрузить файл!");
		}
		// Наименование файла
		$flname = $files["org_photo"]["name"];
		$fltype = $files["org_photo"]["type"];
		$ext = pathinfo($flname, PATHINFO_EXTENSION);
		if ($data["Lpu_id"] == 0) {
			throw new Exception("Не удалось загрузить файл, т.к. МО не определена!");
		}
		$name = $data["Lpu_id"];
		// Создание директорий, если нужно
		createDir(ORGSPATH);
		createDir(ORGSPHOTOPATH); // Корневая директория хранения фотографий подразделений
		$orgDir = ORGSPHOTOPATH . $data["Lpu_id"] . "/"; // Директория конкретной организации, где будут лежать фотографии
		createDir($orgDir);
		if ($data["LpuSection_id"] > 0) {
			//$orgDir .= $data["LpuSection_id"]."/";
			$orgDir .= "LpuSection/";
			$name = $data["LpuSection_id"];
			createDir($orgDir);
		} elseif ($data["LpuUnit_id"] > 0) {
			$orgDir .= "LpuUnit/";
			$name = $data["LpuUnit_id"];
			createDir($orgDir);
		} elseif ($data["LpuBuilding_id"] > 0) {
			$orgDir .= "LpuBuilding/";
			$name = $data["LpuBuilding_id"];
			createDir($orgDir);
		}
		// Какой бы каталог не был выбран - создаем в нем папку для хранения уменьшенных копий (thumbs)
		createDir($orgDir . "thumbs/");
		// удаляем все файлы с таким названием и любым расширением (если они есть)
		array_map("unlink", glob($orgDir . $name . ".*"));
		// Расширение файла
		$name .= "." . $ext;
		// создаем уменьшенную копию изображения
		createThumb($source, $fltype, $orgDir . "thumbs/" . $name, 300, 300);
		// Перемещаем загруженный файл в директорию пользователя с новым именем
		move_uploaded_file($source, $orgDir . $name);
		return [
			"success" => true,
			"file_url" => $orgDir . "thumbs/" . $name . "?t=" . time() // добавляем параметр, чтобы не застывал в кеше
		];
	}

	/**
	 * @param LpuStructure_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function _lpuBuildingIsHeadSmpUnit(LpuStructure_model $callObject, $data)
	{
		if (!isset($data["LpuBuilding_id"])) {
			return false;
		}
		$query = "
			SELECT SUP.SmpUnitParam_id as \"SmpUnitParam_id\"
			FROM v_SmpUnitParam SUP 
			WHERE SUP.LpuBuilding_pid = :LpuBuilding_id
		";
		$queryParams = [
			"LpuBuilding_id" => $data["LpuBuilding_id"]
		];
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}
}