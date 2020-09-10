<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * VideoChat_model - модель для работы с видеосвязью
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Common
 * @access			public
 * @copyright		Copyright (c) 2018 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			24.05.2018
 *
 */

class VideoChat_model extends swModel {
	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
		
		$this->typeMap = [
			'application' => 'Бинарный',
			'audio' => 'Аудио',
			//'font' => 'Шрифт',
			'image' => 'Изображение',
			//'model' => 'Модель',
			'text' => 'Текст',
			'video' => 'Видео',
		];
		
		$this->subTypeMap = [
			'application/xml' => 'XML',
			'application/pdf' => 'PDF',
			'application/zip' => 'Архив',
			'application/x-rar-compressed' => 'Архив',
			'application/x-tar' => 'Архив',
			'application/vnd.ms-excel' => 'MS Excel',
			'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'MS Excel',
			'application/vnd.ms-powerpoint' => 'MS Powerpoint',
			'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'MS Powerpoint',
			'application/msword' => 'MS Word',
			'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'MS Word',
			'text/html' => 'HTML',
		];
	}

	/**
	 * Инициализация mongodb
	 */
	function initMongo() {
		switch (checkMongoDb()) {
			case 'mongo':
				$this->load->library('swMongodb', array(), 'swmongodb');
				break;
			case 'mongodb':
				$this->load->library('swMongodbPHP7', array(), 'swmongodb');
				break;
		}

		$this->load->library('swMongoExt');
		$this->load->helper('MongoDB');
	}

	/**
	 * Получение настроек видеосвязи пользователя
	 */
	function getVideoSettings($data) {
		$params = array(
			'pmUser_id' => $data['pmUser_id'],
		);

		$query = "
			select
				VideoSettings_Camera as Camera,
				VideoSettings_Micro as Micro,
				VideoSettings_Photo as Avatar
			from v_VideoSettings with(nolock)
			where pmUserCache_id = :pmUser_id
		";
		$settings = $this->getFirstRowFromQuery($query, $params, true);
		if ($settings === false) {
			return false;
		}

		if (!empty($settings['Avatar']) && !file_exists($settings['Avatar'])) {
			$settings['Avatar'] = null;
		}

		$response = array(
			'success' => true,
			'settings' => $settings
		);

		return array($response);
	}

	/**
	 * Изменение настроек видеосвязи пользователя
	 */
	function setVideoSettings($data) {
		$params = array(
			'pmUser_id' => $data['pmUser_id'],
		);

		$query = "
			select top 1
				VS.VideoSettings_id,
				VS.VideoSettings_Camera as Camera,
				VS.VideoSettings_Micro as Micro,
				VS.VideoSettings_Photo as Avatar
			from
				(select 1 as a) t
				left join v_VideoSettings VS with(nolock) on VS.pmUserCache_id = :pmUser_id
		";
		$resp = $this->getFirstRowFromQuery($query, $params);

		if (!is_array($resp)) {
			return $this->createError('Ошибка при получении текущих настроек видеосвязи');
		}

		$params = array_merge($params, $resp, $data['settings']);

		return $this->saveVideoSettings($params);
	}

	/**
	 * Сохранение настроек видеосвязи пользователя
	 */
	function saveVideoSettings($data) {
		$params = array(
			'VideoSettings_id' => !empty($data['VideoSettings_id'])?$data['VideoSettings_id']:null,
			'pmUserCache_id' => $data['pmUser_id'],
			'VideoSettings_Camera' => !empty($data['Camera'])?$data['Camera']:null,
			'VideoSettings_Micro' => !empty($data['Micro'])?$data['Micro']:null,
			'VideoSettings_Photo' => !empty($data['Avatar'])?$data['Avatar']:null,
			'pmUser_id' => $data['pmUser_id'],
		);
		if (empty($data['VideoSettings_id'])) {
			$procedure = 'p_VideoSettings_ins';
		} else {
			$procedure = 'p_VideoSettings_upd';
		}
		$query = "
			declare
				@Error_Message varchar(4000),
				@Error_Code bigint,
				@Res bigint = :VideoSettings_id;
			exec {$procedure}
				@VideoSettings_id = @Res output,
				@pmUserCache_id = :pmUserCache_id,
				@VideoSettings_Camera = :VideoSettings_Camera,
				@VideoSettings_Micro = :VideoSettings_Micro,
				@VideoSettings_Photo = :VideoSettings_Photo,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output
			select @Res as VideoSettings_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
		";
		$resp = $this->queryResult($query, $params);
		if (!is_array($resp)) {
			return $this->createError('','Ошибка при сохранении настроек видеосвязи');
		}
		return $resp;
	}

	/**
	 * Сохранение изображения на сервер
	 */
	function saveImage($data) {
		$imageBase64 = explode(',', $data['imageBase64']);
		$image = base64_decode($imageBase64[1]);

		$out_dir = USERSPATH;
		$file = $out_dir.time().'.png';

		$out_dir_arr = explode("/", $out_dir);
		$tmp_dir = "";
		foreach($out_dir_arr as $dir) {
			if (empty($dir)) continue;
			$tmp_dir .= $dir.'/';
			if (!file_exists($tmp_dir)) {
				mkdir($tmp_dir);
			}
		}

		$fp = fopen($file, 'w');
		fwrite($fp, $image);
		fclose($fp);

		return array(array(
			'success' => true,
			'url' => $file,
		));
	}

	/**
	 * Загрузка изображения на сервер
	 */
	function uploadImage($file) {
		$arr = explode('.', $file['name']);
		$ext = end($arr);

		$out_dir = USERSPATH;
		$file_path = $out_dir.time().'.'.$ext;

		$out_dir_arr = explode("/", $out_dir);
		$tmp_dir = "";
		foreach($out_dir_arr as $dir) {
			if (empty($dir)) continue;
			$tmp_dir .= $dir.'/';
			if (!file_exists($tmp_dir)) {
				mkdir($tmp_dir);
			}
		}

		rename($file['tmp_name'], $file_path);

		return array(array(
			'success' => true,
			'url' => $file_path,
		));
	}

	/**
	 * Получение списка контактов пользователя
	 */
	function loadPMUserContactList($data) {
		$filters = array();
		$params = array();
		$searchInPromed = !empty($data['searchInPromed']);

		if ($searchInPromed && empty($data['Lpu_oid']) && empty($data['pmUser_oid'])) {
			return array('data' => array());
		}

		$params['pmUser_id'] = $data['pmUser_id'];
		$params['query'] = '';

		if (empty($data['pmUser_oid'])) {
			$filters[] = "UC.pmUser_id <> :pmUser_id";
		}
		if (!$searchInPromed && empty($data['pmUser_oid'])) {
			$filters[] = "Contact.pmUserContacts_id is not null";
		}

		if (!empty($data['pmUser_oid'])) {
			$filters[] = "UC.pmUser_id = :pmUser_oid";
			$params['pmUser_oid'] = $data['pmUser_oid'];
		}

		if (!empty($data['query'])) {
			$filters[] = "(
				(UC.pmUser_SurName+isnull(' '+UC.pmUser_FirName,'')+isnull(' '+UC.pmUser_SecName,'') like :query+'%') or
				(UC.pmUser_Login like :query+'%')
			)";
			$params['query'] = $data['query'];
		}

		if (!empty($data['Lpu_oid']) && empty($data['LpuSection_id']) && empty($data['Dolgnost_id'])) {
			$params['Lpu_oid'] = $data['Lpu_oid'];

			$filters[] = "(
				exists (
					select * from v_MedStaffFact MSF with(nolock)
					inner join v_MedPersonal MP with(nolock) on MP.MedPersonal_id = MSF.MedPersonal_id
					where MP.MedPersonal_id = UC.MedPersonal_id
					and MSF.Lpu_id = :Lpu_oid
				) or exists(
					select * from v_pmUserCacheOrg UO with(nolock)
					inner join v_Lpu_all L with(nolock) on L.Org_id = UO.Org_id
					where UO.pmUserCache_id = UC.pmUser_id 
					and L.Lpu_id = :Lpu_oid
				)
			)";
		} else if (!empty($data['LpuSection_id']) || !empty($data['Dolgnost_id'])) {
			$mpFilters = array();

			$mpFilters[] = "MP.MedPersonal_id = UC.MedPersonal_id";

			if (!empty($data['LpuSection_id'])) {
				$mpFilters[] = "MSF.LpuSection_id = :LpuSection_id";
				$params['LpuSection_id'] = $data['LpuSection_id'];
			}
			if (!empty($data['Dolgnost_id'])) {
				$mpFilters[] = "MP.Dolgnost_id = :Dolgnost_id";
				$params['Dolgnost_id'] = $data['Dolgnost_id'];
			}

			$mpFilters_str = implode(" and ", $mpFilters);

			$filters[] = "exists(
				select * from v_MedStaffFact MSF with(nolock)
				inner join v_MedPersonal MP with(nolock) on MP.MedPersonal_id = MSF.MedPersonal_id
				where {$mpFilters_str}
			)";
		}

		$filters_str = implode("\nand ", $filters);

		$query = "
			select
				-- select
				UC.pmUser_id,
				rtrim(UC.pmUser_SurName) as SurName,
				rtrim(UC.pmUser_FirName) as FirName,
				rtrim(UC.pmUser_SecName) as SecName,
				rtrim(UC.pmUser_SurName)+' '+rtrim(UC.pmUser_FirName)+coalesce(' '+rtrim(UC.pmUser_SecName), '') as FullName,
				rtrim(UC.pmUser_Login) as Login,
				Lpu.List as LpuListText,
				Contact.pmUserContacts_id,
				VS.VideoSettings_id,
				VS.VideoSettings_Photo as Avatar,
				case 
					when Contact.pmUserContacts_id is null then 'add'
				end as Status,
				case when VS.VideoSettings_Camera is null then 0 else 1 end as hasCamera
				-- end select
			from
				-- from
				v_pmUserCache UC with(nolock)
				left join v_VideoSettings VS with(nolock) on VS.pmUserCache_id = UC.pmUser_id
				outer apply (
					select top 1 Contact.pmUserContacts_id
					from v_pmUserContacts Contact with(nolock)
					where Contact.pmUserCache_rid = UC.pmUser_id
					and Contact.pmUserCache_id = :pmUser_id
				) Contact
				outer apply (
					select stuff((select
						'|::|' + cast(L.Lpu_id as varchar(max)) + '|' + L.Lpu_Nick
					from
						v_pmUserCacheOrg UO with(nolock)
						inner join v_Lpu_all L with(nolock) on L.Org_id = UO.Org_id
					where
						UO.pmUserCache_id = UC.pmUser_id
					for xml path('')), 1, 4, '') as List
				) Lpu
				-- end from
			where
				-- where
				{$filters_str}
				-- end where
			order by
				-- order by
				case 
					when UC.pmUser_Login like :query 
					then 0 else 1 
				end,
				UC.pmUser_SurName,
				UC.pmUser_FirName,
				UC.pmUser_SecName
				-- end order by
		";
		//echo getDebugSQL($query, $params);exit;
		$contacts = $this->queryResult(getLimitSQLPH($query, 0, 200), $data);
		if (!is_array($contacts)) {
			return false;
		}
		
		$totalCount = $this->getFirstResultFromQuery(getCountSQLPH($query), $data);
		if ($totalCount === false) {
			return false;
		}

		$portalContacts = $this->queryResult("
			SELECT
				Contact.pmUserCache_rid as pmUser_id,
				null as SurName,
				null as FirName,
				null as SecName,
				null as FullName,
				null as Login,
				null as LpuListText,
				Contact.pmUserContacts_id,
				VS.VideoSettings_id,
				VS.VideoSettings_Photo as Avatar,
				case 
					when Contact.pmUserContacts_id is null then 'add'
				end as Status,
				case when VS.VideoSettings_Camera is null then 0 else 1 end as hasCamera
			FROM  
				v_pmUserContacts Contact (nolock)
				left join v_VideoSettings VS (nolock) on VS.pmUserCache_id = Contact.pmUserCache_rid
				WHERE 
				Contact.pmUserCache_id = :pmUser_id
				and Contact.pmUserCache_rid < 5000000
		", $data);
		
		if (!empty($portalContacts)) {

			$tempResult = array();
			$userList = array();
			
			foreach ($portalContacts as $portalContact) {
				$tempResult[$portalContact['pmUser_id']] = $portalContact;
				$userList[] = $portalContact['pmUser_id'];
			}

			$portalContacts = $tempResult;
			
			if (!empty($userList)) {

				$userList = implode(',', $userList);
				$this->userportaldb = $this->load->database('UserPortal', true);

				$portalUsers = $this->queryResult("
					SELECT
						id as pmUser_id,
						rtrim(u.surname) as SurName,
						rtrim(u.first_name) as FirName,
						rtrim(u.second_name) as SecName,
						rtrim(u.surname)+' '+rtrim(u.first_name)+coalesce(' '+rtrim(u.surname), '') as FullName,
						rtrim(u.username) as Login,
						null as LpuListText,
						null as pmUserContacts_id,
						null as VideoSettings_id,
						null as Avatar,
						null as Status,
						null as hasCamera
					FROM users u (nolock)
					WHERE u.id in ({$userList})
				", $data, $this->userportaldb);
				
				if (!empty($portalUsers)) {
					foreach ($portalUsers as $user) {
						if (isset($portalContacts[$user['pmUser_id']])) {
							$portalContacts[$user['pmUser_id']] = array_merge($portalContacts[$user['pmUser_id']], $user);
						}
					}
					
					$portalContacts = array_values($portalContacts);
				}
			}
		}
		
		$portalCount = count($portalContacts);
		$contacts = array_merge($contacts,$portalContacts);

		foreach($contacts as &$contact) {
			if (!empty($contact['LpuListText'])) {
				$LpuList = explode('|::|', $contact['LpuListText']);
				unset($contact['LpuListText']);

				foreach($LpuList as $lpu) {
					$lpu = explode('|', $lpu);
					$contact['LpuList'][] = array(
						'Lpu_id' => $lpu[0],
						'Lpu_Nick' => $lpu[1]
					);
				}
			}
		}

		$response = array(
			'data' => array_values($contacts),
			'totalCount' => $totalCount + $portalCount
		);

		return $response;
	}

    /**
     * Получение списка контактов пользователя
     */
    function mloadPMUserContactList($data) {
        $filters = array();
        $params = array();
        $searchInPromed = !empty($data['searchInPromed']);

        if ($searchInPromed && empty($data['Lpu_oid']) && empty($data['pmUser_oid'])) {
            return array('data' => array());
        }

        $params['pmUser_id'] = $data['pmUser_id'];
        $params['query'] = '';

        if (empty($data['pmUser_oid'])) {
            $filters[] = "UC.pmUser_id <> :pmUser_id";
        }
        if (!$searchInPromed && empty($data['pmUser_oid'])) {
            $filters[] = "Contact.pmUserContacts_id is not null";
        }

        if (!empty($data['pmUser_oid'])) {
            $filters[] = "UC.pmUser_id = :pmUser_oid";
            $params['pmUser_oid'] = $data['pmUser_oid'];
        }

        if (!empty($data['query'])) {
            $filters[] = "(
				(UC.pmUser_SurName+isnull(' '+UC.pmUser_FirName,'')+isnull(' '+UC.pmUser_SecName,'') like :query+'%') or
				(UC.pmUser_Login like :query+'%')
			)";
            $params['query'] = $data['query'];
        }

        if (!empty($data['Lpu_oid']) && empty($data['LpuSection_id']) && empty($data['Dolgnost_id'])) {
            $params['Lpu_oid'] = $data['Lpu_oid'];

            $filters[] = "(
				exists (
					select * from v_MedStaffFact MSF with(nolock)
					inner join v_MedPersonal MP with(nolock) on MP.MedPersonal_id = MSF.MedPersonal_id
					where MP.MedPersonal_id = UC.MedPersonal_id
					and MSF.Lpu_id = :Lpu_oid
				) or exists(
					select * from v_pmUserCacheOrg UO with(nolock)
					inner join v_Lpu_all L with(nolock) on L.Org_id = UO.Org_id
					where UO.pmUserCache_id = UC.pmUser_id 
					and L.Lpu_id = :Lpu_oid
				)
			)";
        } else if (!empty($data['LpuSection_id']) || !empty($data['Dolgnost_id'])) {
            $mpFilters = array();

            $mpFilters[] = "MP.MedPersonal_id = UC.MedPersonal_id";

            if (!empty($data['LpuSection_id'])) {
                $mpFilters[] = "MSF.LpuSection_id = :LpuSection_id";
                $params['LpuSection_id'] = $data['LpuSection_id'];
            }
            if (!empty($data['Dolgnost_id'])) {
                $mpFilters[] = "MP.Dolgnost_id = :Dolgnost_id";
                $params['Dolgnost_id'] = $data['Dolgnost_id'];
            }

            $mpFilters_str = implode(" and ", $mpFilters);

            $filters[] = "exists(
				select * from v_MedStaffFact MSF with(nolock)
				inner join v_MedPersonal MP with(nolock) on MP.MedPersonal_id = MSF.MedPersonal_id
				where {$mpFilters_str}
			)";
        }

        $filters_str = implode("\nand ", $filters);

        $query = "
			select
				UC.pmUser_id,
				rtrim(UC.pmUser_SurName) as SurName,
				rtrim(UC.pmUser_FirName) as FirName,
				rtrim(UC.pmUser_SecName) as SecName,
				rtrim(UC.pmUser_Login) as Login,
				Lpu.List as LpuListText,
				Contact.pmUserContacts_id,
				VS.VideoSettings_id,
				VS.VideoSettings_Photo as Avatar,
				case 
					when Contact.pmUserContacts_id is null then 'add'
				end as Status,
				case when VS.VideoSettings_Camera is null then 0 else 1 end as hasCamera
			from
				v_pmUserCache UC with(nolock)
				left join v_VideoSettings VS with(nolock) on VS.pmUserCache_id = UC.pmUser_id
				outer apply (
					select top 1 Contact.pmUserContacts_id
					from v_pmUserContacts Contact with(nolock)
					where Contact.pmUserCache_rid = UC.pmUser_id
					and Contact.pmUserCache_id = :pmUser_id
				) Contact
				outer apply (
					select stuff((select
						'|::|' + cast(L.Lpu_id as varchar(max)) + '|' + L.Lpu_Nick
					from
						v_pmUserCacheOrg UO with(nolock)
						inner join v_Lpu_all L with(nolock) on L.Org_id = UO.Org_id
					where
						UO.pmUserCache_id = UC.pmUser_id
					for xml path('')), 1, 4, '') as List
				) Lpu
			where
				{$filters_str}
			order by
				case 
					when UC.pmUser_Login like :query 
					then 0 else 1 
				end,
				UC.pmUser_SurName,
				UC.pmUser_FirName,
				UC.pmUser_SecName
		";
        //echo getDebugSQL($query, $params);exit;
        $contacts = $this->queryResult($query, $params);
        if (!is_array($contacts)) {
            return false;
        }

		$protocol = stripos($_SERVER['SERVER_PROTOCOL'],'https') === 0 ? 'https://' : 'http://';
        $serverName = '';
        if (!empty($_SERVER['SERVER_NAME'])) {
            $serverName = $_SERVER['SERVER_NAME'];
        } else {
            $serverName = $_SERVER['SERVER_ADDR'];
        }
		$domain = $protocol.$serverName;

        foreach($contacts as &$contact) {
            if (!empty($contact['LpuListText'])) {
                $LpuList = explode('|::|', $contact['LpuListText']);
                unset($contact['LpuListText']);

                foreach($LpuList as $lpu) {
                    $lpu = explode('|', $lpu);
                    $contact['LpuList'][] = array(
                        'Lpu_id' => $lpu[0],
                        'Lpu_Nick' => $lpu[1]
                    );
                }
            }

            if (!empty($contact['Avatar'])) {
				$contact['Avatar'] = $domain.'/'.$contact['Avatar'];
			}
        }

        $response = array(
            'data' => array_values($contacts)
        );

        return $response;
    }

	/**
	 * Добавление контакта
	 */
	function addPMUserContact($data) {
		$params = array(
			'pmUserCache_id' => $data['pmUser_id'],
			'pmUserCache_rid' => $data['pmUserCache_rid'],
			'pmUser_id' => $data['pmUser_id'],
		);

		$query = "
			select top 1 count(*) as cnt
			from v_pmUserContacts with(nolock)
			where pmUserCache_id = :pmUserCache_id
			and pmUserCache_rid = :pmUserCache_rid
		";

		$count = $this->getFirstResultFromQuery($query, $params);
		if ($count === false) {
			return $this->createError('','Ошибка при проверке существования контакта');
		}
		if ($count > 0) {
			return $this->createError('','Уже существует контакт с выбранным пользователем');
		}

		$query = "
			declare
				@Error_Message varchar(4000),
				@Error_Code bigint,
				@Res bigint;
			exec p_pmUserContacts_ins
				@pmUserContacts_id = @Res output,
				@pmUserCache_id = :pmUserCache_id,
				@pmUserCache_rid = :pmUserCache_rid,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output
			select @Res as pmUserContacts_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
		";

		$resp = $this->queryResult($query, $params);
		if (!is_array($resp)) {
			return $this->createError('','Ошибка при добавлении контакта');
		}

		return $resp;
	}

    /**
     * Добавление контакта
     */
    function mAddPMUserContact($data) {
        $params = array(
            'pmUserCache_id' => $data['pmUser_id'],
            'pmUserCache_rid' => $data['pmUserCache_rid'],
            'pmUser_id' => $data['pmUser_id'],
        );

        $query = "
			select top 1 count(*) as cnt
			from v_pmUserContacts with(nolock)
			where pmUserCache_id = :pmUserCache_id
			and pmUserCache_rid = :pmUserCache_rid
		";

        $count = $this->getFirstResultFromQuery($query, $params);
        if ($count === false) {
            return $this->createError('','Ошибка при проверке существования контакта');
        }
        if ($count > 0) {
            return $this->createError('','Уже существует контакт с выбранным пользователем');
        }

        $query = "
			declare
				@Error_Message varchar(4000),
				@Error_Code bigint,
				@Res bigint;
			exec p_pmUserContacts_ins
				@pmUserContacts_id = @Res output,
				@pmUserCache_id = :pmUserCache_id,
				@pmUserCache_rid = :pmUserCache_rid,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output
			select @Res as pmUserContacts_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
		";

        $resp = $this->queryResult($query, $params);
        if (!is_array($resp)) {
            return $this->createError('','Ошибка при добавлении контакта');
        }

        return $resp;
    }

	/**
	 * Удаление контакта
	 */
	function deletePMUserContact($data) {
		$params = array(
			'pmUserCache_id' => $data['pmUser_id'],
			'pmUserCache_rid' => $data['pmUserCache_rid'],
			'pmUser_id' => $data['pmUser_id'],
		);

		$query = "
			select top 1 pmUserContacts_id
			from v_pmUserContacts with(nolock)
			where pmUserCache_id = :pmUserCache_id
			and pmUserCache_rid = :pmUserCache_rid
		";

		$params['pmUserContacts_id'] = $this->getFirstResultFromQuery($query, $params, true);
		if ($params['pmUserContacts_id'] === false) {
			return $this->createError('','Ошибка при проверке существования контакта');
		}
		if (empty($params['pmUserContacts_id'])) {
			return array(array('success' => true));
		}

		$query = "
			declare
				@Error_Message varchar(4000),
				@Error_Code bigint;
			exec p_pmUserContacts_del
				@pmUserContacts_id = :pmUserContacts_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output
			select @Error_Code as Error_Code, @Error_Message as Error_Msg;
		";

		$resp = $this->queryResult($query, $params);
		if (!is_array($resp)) {
			return $this->createError('','Ошибка при удалении контакта');
		}

		return $resp;
	}

	/**
	 * Получение ниформации о пользователе
	 */
	function getPMUserInfo($data) {
		$params = array(
			'pmUserCache_rid' => $data['pmUserCache_rid'],
		);

		$query = "
			select top 1
				UC.pmUser_id,
				rtrim(UC.pmUser_Login) as Login,
				rtrim(UC.pmUser_SurName)+isnull(' '+rtrim(UC.pmUser_FirName),'')+isnull(' '+rtrim(UC.pmUser_SecName),'') as FIO,
				rtrim(UC.pmUser_SurName) as SurName,
				rtrim(UC.pmUser_FirName) as FirName,
				rtrim(UC.pmUser_SecName) as SecName,
				VS.VideoSettings_Photo as Avatar
			from
				v_pmUserCache UC with(nolock)
				left join v_VideoSettings VS with(nolock) on VS.pmUserCache_id = UC.pmUser_id
			where
				UC.pmUser_id = :pmUserCache_rid
		";

		$resp = $this->getFirstRowFromQuery($query, $params);

		if (!is_array($resp)) {
			return $resp;
		}

		return array(array(
			'success' => true,
			'userInfo' => $resp
		));
	}

	/**
	 * @param array $data
	 * @return array
	 */
	function prepareMessage($data) {
		if (!isset($data['file_name'])) {
			return array(
				'id' => $data['id'],
				'pmUser_sid' => $data['pmUser_sid'],
				'text' => $data['text'],
				'file_name' => null,
				'dt' => $data['dt'],
			);
		}

		$file_type = explode('/', $data['file_type']);

		switch(true) {
			case (!file_exists($data['file_path'])):
				$text = "<span style='color: red;'>Файл {$data['file_name']} не найден на сервере!</span>";
				break;
			case ($file_type[0] == 'image'):
				$img = base64_encode(file_get_contents($data['file_path']));
				$text = "<a target='_blank' href='/?c=VideoChat&m=getFileMessage&id={$data['id']}'><img src='data:{$data['file_type']};base64,{$img}'></img></a>";
				break;
			default:
				$text = "<a target='_blank' href='/?c=VideoChat&m=getFileMessage&id={$data['id']}'>{$data['file_name']}</a>";
				break;
		}

		$protocol = stripos($_SERVER['SERVER_PROTOCOL'],'https') === 0 ? 'https://' : 'http://';
		$serverName = '';
		if (!empty($_SERVER['SERVER_NAME'])) {
			$serverName = $_SERVER['SERVER_NAME'];
		} else {
			$serverName = $_SERVER['SERVER_ADDR'];
		}
		$domain = $protocol.$serverName;


		return array(
			'id' => $data['id'],
			'pmUser_sid' => $data['pmUser_sid'],
			'text' => $text,
			'file_name' => $data['file_name'],
			'file_path' => $domain."/api/rish/VideoChat/mgetFileMessage?id={$data['id']}",
			'dt' => $data['dt'],
		);
	}
	
	/**
	 * @param string $mimeType
	 * @return string|null
	 */
	function getFileTypeName($mimeType) {
		$type_parts = explode("/", explode(";", $mimeType)[0]);
		return $this->subTypeMap[$mimeType] ?? $this->typeMap[$type_parts[0]] ?? null;
	}
	
	/**
	 * @param array $data
	 * @return array
	 */
	function prepareFile($data) {
		return [
			'id' => $data['id'],
			'pmUser_sid' => $data['pmUser_sid'],
			'file_link' => "<a target='_blank' href='/?c=VideoChat&m=getFileMessage&id={$data['id']}'>{$data['file_name']}</a>",
			'file_name' => $data['file_name'],
			'file_type_mime' => $data['file_type'],
			'file_type_name' => $this->getFileTypeName($data['file_type']),
			'dt' => $data['dt'],
		];
	}

	/**
	 * @param array $data
	 * @return array
	 */
	function sendTextMessage($data) {
		$this->initMongo();

		$object = 'videochatmessage';

		$params = array(
			'id' => $this->swmongoext->generateCode($object),
			'pmUser_sid' => (string)$data['pmUser_id'],
			'pmUser_gid' => $data['pmUser_gid_list'],
			'text' => $data['text'],
			'dt' => $this->getCurrentDT()->format('Y-m-d H:i:s'),
		);

		$this->swmongodb->insert($object, $params);

		$this->load->helper('NodeJS');

		$config = array(
			'host' => NODEJS_VIDEOCHAT_SOCKET_HOST.'/message',
			'port' => NODEJS_VIDEOCHAT_SOCKET_PORT
		);

		$message = $this->prepareMessage($params);

		$postResult = NodePostRequest(array(
			'message' => $message,
			'userKeys' => $data['pmUser_gid_list'],
		), $config);
		if (!$this->isSuccessful($postResult)) {
			return $postResult;
		}

		return array(array(
			'success' => true
		));
	}

    /**
     * @param array $data
     * @return array
     */
    function mSendTextMessage($data) {
        $this->initMongo();

        $object = 'videochatmessage';

        $params = array(
            'id' => $this->swmongoext->generateCode($object),
            'pmUser_sid' => (string)$data['pmUser_id'],
            'pmUser_gid' => strval($data['pmUser_gid_list']),
            'text' => $data['text'],
            'dt' => $this->getCurrentDT()->format('Y-m-d H:i:s'),
        );

        $this->swmongodb->insert($object, $params);

        $this->load->helper('NodeJS');

        $config = array(
            'host' => NODEJS_VIDEOCHAT_SOCKET_HOST.'/message',
            'port' => NODEJS_VIDEOCHAT_SOCKET_PORT
        );

        $message = $this->prepareMessage($params);

        $postResult = NodePostRequest(array(
            'message' => $message,
            'userKeys' => $data['pmUser_gid_list'],
        ), $config);
        if (!$this->isSuccessful($postResult)) {
            return $postResult;
        }

        return array(array(
            'success' => true
        ));
    }

	/**
	 * @param array $data
	 * @return array
	 */
	function sendFileMessage($data) {
		if (empty($_FILES['FileMessage'])) {
			return $this->createError('','Не передан файл');
		}

		$sizeLimitMB = 500;
		$sizeLimitB = $sizeLimitMB * 1024 * 1024;

		if ($_FILES['FileMessage']['size'] > $sizeLimitB) {
			return $this->createError('',"Размер файла превышает {$sizeLimitMB} MB");
		}

		$pmUser_sid = $data['pmUser_id'];

		$file = $_FILES['FileMessage'];
		$file_link = md5($pmUser_sid.time());
		
		if (preg_match('/\.rar$/', $file['name'], $match)) {
			$file['type'] = 'application/x-rar-compressed';
		}

		$out_dir = IMPORTPATH_ROOT.'video_chat_message/';
		$file_path = $out_dir.$file_link;

		$out_dir_arr = explode("/", $out_dir);
		$tmp_dir = "";
		foreach($out_dir_arr as $dir) {
			if (empty($dir)) continue;
			$tmp_dir .= $dir.'/';
			if (!file_exists($tmp_dir)) {
				mkdir($tmp_dir);
			}
		}

		rename($file['tmp_name'], $file_path);

		$this->initMongo();

		$object = 'videochatmessage';

		$params = array(
			'id' => $this->swmongoext->generateCode($object),
			'pmUser_sid' => (string)$pmUser_sid,
			'pmUser_gid' => $data['pmUser_gid_list'],
			'file_path' => $file_path,
			'file_name' => $file['name'],
			'file_type' => $file['type'],
			'dt' => $this->getCurrentDT()->format('Y-m-d H:i:s'),
		);

		$this->swmongodb->insert($object, $params);

		$this->load->helper('NodeJS');

		$config = array(
			'host' => NODEJS_VIDEOCHAT_SOCKET_HOST.'/message',
			'port' => NODEJS_VIDEOCHAT_SOCKET_PORT
		);

		$message = $this->prepareMessage($params);

		$postResult = NodePostRequest(array(
			'message' => $message,
			'userKeys' => $data['pmUser_gid_list'],
		), $config);
		if (!$this->isSuccessful($postResult)) {
			return $postResult;
		}

		return array(array(
			'success' => true
		));
	}

	/**
	 * @param array $data
	 * @return mixed|null
	 */
	function getFileMessage($data) {
		$this->initMongo();

		$object = 'videochatmessage';
		$id = (int)$data['id'];
		$pmUser_id = (string)$data['pmUser_id'];

		$where = array(
			'id' => $id,
			'$or' => array(
				array('pmUser_sid' => $pmUser_id),
				array('pmUser_gid' => $pmUser_id)
			),
		);

		$resp = $this->swmongodb->get_where($object, $where);

		return (is_array($resp) && count($resp) > 0)?$resp[0]:null;
	}

	/**
	 * @param array $data
	 * @return mixed|null
	 */
	function mgetFileMessage($data) {
		$this->initMongo();

		$object = 'videochatmessage';
		$id = (int)$data['id'];
		$pmUser_id = (string)$data['pmUser_id'];

		$where = array(
			'id' => $id,
			'$or' => array(
				array('pmUser_sid' => $pmUser_id),
				array('pmUser_gid' => $pmUser_id)
			),
		);

		$resp = $this->swmongodb->get_where($object, $where);

		return (is_array($resp) && count($resp) > 0)?$resp[0]:null;
	}

	/**
	 * @param array $data
	 * @return mixed
	 */
	function loadMessageList($data) {
		$this->initMongo();

		$object = 'videochatmessage';
		$where = array();

		$pmUser_id = (string)$data['pmUser_id'];

		if (empty($data['pmUser_cid_list'])) {
			$where = array(
				'$or' => array(
					array('pmUser_sid' => $pmUser_id),
					array('pmUser_gid' => $pmUser_id)
				)
			);
		} else {
			$pmUser_cid_list = array_map(
				function($pmUser_cid) {return (string)$pmUser_cid;},
				is_array($data['pmUser_cid_list'])?$data['pmUser_cid_list']:array($data['pmUser_cid_list'])
			);

			$where = array(
				'$or' => array(
					array('pmUser_sid' => $pmUser_id, 'pmUser_gid' => array('$in' => $pmUser_cid_list)),
					array('pmUser_gid' => $pmUser_id, 'pmUser_sid' => array('$in' => $pmUser_cid_list)),
				)
			);
		}
		if (!empty($data['beforeDT'])) {
			$where['dt'] = array('$lt' => $data['beforeDT']);
		}

		$resp = $this->swmongodb->order_by(array('dt' => 'desc'))->limit(15)->get_where($object, $where);
		if (!is_array($resp)) {
			return false;
		}

		return array_map(function($item) {
			return $this->prepareMessage($item);
		}, $resp);
	}
    /**
     * @param array $data
     * @return mixed
     */
    function mloadMessageList($data) {
        $this->initMongo();

        $object = 'videochatmessage';
        $where = array();

        $pmUser_id = (string)$data['pmUser_id'];

        if (empty($data['pmUser_cid_list'])) {
            $where = array(
                '$or' => array(
                    array('pmUser_sid' => $pmUser_id),
                    array('pmUser_gid' => $pmUser_id)
                )
            );
        } else {
            $pmUser_cid_list = array_map(
                function($pmUser_cid) {return (string)$pmUser_cid;},
                is_array($data['pmUser_cid_list'])?$data['pmUser_cid_list']:array($data['pmUser_cid_list'])
            );

            $where = array(
                '$or' => array(
                    array('pmUser_sid' => $pmUser_id, 'pmUser_gid' => array('$in' => $pmUser_cid_list)),
                    array('pmUser_gid' => $pmUser_id, 'pmUser_sid' => array('$in' => $pmUser_cid_list)),
                )
            );
        }
        if (!empty($data['beforeDT'])) {
            $where['dt'] = array('$lt' => $data['beforeDT']);
        }

        $resp = $this->swmongodb->order_by(array('dt' => 'desc'))->limit(15)->get_where($object, $where);
        if (!is_array($resp)) {
            return false;
        }

		return array_map(function($item) {
			return $this->prepareMessage($item);
		}, $resp);
	}
	
	/**
	 * @param array $data
	 * @return array|bool
	 */
	function loadFileList($data) {
		$prepareUserIds = function($ids) {
			return array_map(
				function($id) {return (string)$id;},
				is_array($ids)?$ids:array($ids)
			);
		};
	
		$this->initMongo();

		$object = 'videochatmessage';

		$pmUser_id = (string)$data['pmUser_id'];
		
		$pmUser_cid_list = $prepareUserIds($data['pmUser_cid_list']);
		$pmUser_sid_list = $prepareUserIds($data['pmUser_sid_list'] ?? []);
		
		$where = [];
		
		$where[] = ['$or' => [
			['pmUser_sid' => $pmUser_id, 'pmUser_gid' => ['$in' => $pmUser_cid_list]],
			['pmUser_gid' => $pmUser_id, 'pmUser_sid' => ['$in' => $pmUser_cid_list]],
		]];
		
		$where[] = ['file_path' => array('$exists' => true)];
		
		if (!empty($data['query'])) {
			$where[] = ['$or' => [
				['pmUser_sid' => ['$in' => $pmUser_sid_list]],
				['file_name' => ['$regex' => "^{$data['query']}", '$options' => 'i']],
			]];
		}
		
		if (!empty($data['fileTypeName'])) {
			$fileTypeRegExp = null;
			$fileTypeFilters = [];
			$fileTypeExclude = [];
			
			foreach($this->typeMap as $type => $typeName) {
				if ($typeName == $data['fileTypeName']) {
					$fileTypeRegExp = "^{$type}\\/";
					$fileTypeFilters[] = ['file_type' => ['$regex' => $fileTypeRegExp]];
					break;
				}
			}
			foreach($this->subTypeMap as $subType => $typeName) {
				if ($typeName == $data['fileTypeName']) {
					$fileTypeFilters[] = ['file_type' => $subType];
				} else if ($fileTypeRegExp && preg_match("/{$fileTypeRegExp}/", $subType)) {
					$fileTypeExclude[] = $subType;
				}
			}
			
			if (count($fileTypeFilters) > 0) {
				$where[] = ['$or' => $fileTypeFilters];
			}
			if (count($fileTypeExclude) > 0) {
				$where[] = ['file_type' => ['$nin' => $fileTypeExclude]];
			}
		}
		
		$where = ['$and' => $where];

		$resp = $this->swmongodb->order_by(array('dt' => 'desc'))->limit(100)->get_where($object, $where);
		if (!is_array($resp)) {
			return false;
		}

		return array_map(function($item) {
			return $this->prepareFile($item);
		}, $resp);
	}
	
	/**
	 * @param array $data
	 * @return array
	 */
	function loadFileTypeList($data) {
		$types = array_unique(array_merge(
			array_values($this->typeMap),
			array_values($this->subTypeMap)
		));
		
		usort($types, function($a, $b) {
			$la = mb_substr($a, 0, 1);
			$lb = mb_substr($b, 0, 1);
			if(ord($la) > 122 && ord($lb) > 122){
				return $a > $b ? 1 : -1;
			}
			if(ord($la) > 122 || ord($lb) > 122) {
				return $a < $b ? 1 : -1;
			}
			return $a > $b ? 1 : -1;
		});
		
		return array_map(function($type) {
			return ['name' => $type];
		}, $types);
	}
	
	/**
	 * @param array $data
	 * @return array
	 */
	function saveCall($data) {
		$this->initMongo();
		
		$object = 'videochatcall';
		
		$params = [
			'id' => $this->swmongoext->generateCode($object),
			'pmUser_iid' => $data['pmUser_iid'],
			'pmUser_ids' => $data['pmUser_ids'],
			'callType' => $data['callType'],
			'room' => $data['room'],
			'begDT' => !empty($data['begDT'])?$data['negDT']:$this->currentDT->format('Y-m-d H:i:s'),
			'endDT' => !empty($data['endDT'])?$data['endDT']:null,
		];
		
		$this->swmongodb->insert($object, $params);
		
		return [[
			'success' => true,
			'id' => $params['id']
		]];
	}
	
	/**
	 * @param array $data
	 * @return array
	 */
	function updateCall($data) {
		$this->initMongo();
		
		$object = 'videochatcall';
		
		$where = [
			'room' => $data['room']
		];
		
		$resp = $this->swmongodb->get_where($object, $where);
		if (empty($resp)) {
			return [['success' => true]];
		}
		$params = $resp[0];
		
		if (!empty($data['endDT'])) {
			$params['endDT'] = $data['endDT'];
		}
		if (!empty($data['record'])) {
			$params['records'][] = $data['record'];
		}
		
		$this->swmongodb->where($where)->update($object, $params);
		
		return [['success' => true]];
	}
	
	/**
	 * @param array $data
	 * @return array
	 */
	function saveCallRecord($data) {
		$file_link = md5($data['room'].time());

		$out_dir = IMPORTPATH_ROOT.'video_chat_record/';
		$file_path = $out_dir.$file_link.'.webm';

		$out_dir_arr = explode("/", $out_dir);
		$tmp_dir = "";
		foreach($out_dir_arr as $dir) {
			if (empty($dir)) continue;
			$tmp_dir .= $dir.'/';
			if (!file_exists($tmp_dir)) {
				mkdir($tmp_dir);
			}
		}
		
		$bytes = 0;
		
		$input_fp = fopen("php://input", "r");
		$write_fp = fopen($file_path, "w");
		
		while ($buf = fread($input_fp, 1024)) {
			$bytes += fwrite($write_fp, $buf);
		}
		
		fclose($input_fp);
		fclose($write_fp);
		
		$this->updateCall([
			'room' => $data['room'],
			'record' => $file_path
		]);
		
		return [[
			'success' => true,
			'bytes' => $bytes
		]];
	}
	
	/**
	 * @param array $data
	 * @return array
	 */
	function loadCallList($data) {
		$this->initMongo();
		
		$object = 'videochatcall';
		
		$where = [
			'pmUser_ids' => (string)$data['pmUser_id'],
		];
		
		$resp = $this->swmongodb->order_by(array('begDT' => 'desc'))->limit(100)->get_where($object, $where);
		
		return array_map(function($item) {
			return [
				'id' => $item['id'],
				'begDT' => $item['begDT'],
				'endDT' => $item['endDT'],
				'callType' => $item['callType'],
				'pmUser_iid' => $item['pmUser_iid'],
				'pmUser_ids' => $item['pmUser_ids'],
				'records' => $item['records'] ?? null,
			];
		}, $resp);
	}
}