<?php defined('BASEPATH') or die ('No direct script access allowed');

require_once('XmlTemplateBase_model.php');

/**
 * XmlTemplate6E - модель для работы с шаблонами из форм на ExtJS6
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Common
 * @access			public
 * @copyright		Copyright (c) 2018 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			17.03.2018
 *
 * @property EvnXmlBase_model EvnXmlBase_model
 */

class XmlTemplate6E_model extends XmlTemplateBase_model {
	/**
	 * construct
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * Получение списка шаблонов
	 * @param array $data
	 * @return array|bool
	 */
	function loadXmlTemplateList($data) {

		$this->load->model('XmlTemplateCatDefault_model');
		$filters = array('1=1');
		$params = array();
		$order = "XT.XmlTemplate_Caption";
		$limit = 1000;

		$baseTemplateFilter = "not exists(
			select top 1 1 from v_XmlTemplateBase XTB with(nolock)
			where XTB.XmlTemplate_id = XT.XmlTemplate_id
		)";

		$filters[] = "isnull(XT.XmlTemplate_IsDeleted, 1) = 1";

		if (!empty($data['XmlType_id'])) {
			$filters[] = "XT.XmlType_id = :XmlType_id";
			$params['XmlType_id'] = $data['XmlType_id'];
		}
		if (!empty($data['EvnClass_id'])) {
			$filters[] = "XT.EvnClass_id = :EvnClass_id";
			$params['EvnClass_id'] = $data['EvnClass_id'];
		}
		/*if (!empty($data['MedPersonal_id'])) {
			$filters[] = "XT.MedPersonal_id = :MedPersonal_id";
			$params['MedPersonal_id'] = $data['MedPersonal_id'];
		}*/

		$params['Lpu_id'] = !empty($data['Lpu_id'])?$data['Lpu_id']:null;
		$params['MedStaffFact_id'] = !empty($data['MedStaffFact_id'])?$data['MedStaffFact_id']:null;
		$params['MedPersonal_id'] = !empty($data['MedPersonal_id'])?$data['MedPersonal_id']:null;
		$params['pmUser_id'] = $data['pmUser_id'];

		if (!empty($data['mode'])) {
			switch($data['mode']) {
				case 'favorite':
					$filters[] = "fav.v = 2";
					break;
				case 'last5Days':
					$filters[] = "XT.Lpu_id = :Lpu_id";
					$filters[] = "XT.pmUser_updID = :pmUser_id";
					$filters[] = "cast(XT.XmlTemplate_updDT as date) between dateadd(day, -4, @date) and @date";

					$order = "XT.XmlTemplate_updDT desc, XT.XmlTemplate_Caption";
					break;
				case 'last10Templates':
					$limit = 10;

					$filters[] = "XT.Lpu_id = :Lpu_id";
					$filters[] = "XT.pmUser_updID = :pmUser_id";

					$order = "XT.XmlTemplate_updDT desc, XT.XmlTemplate_Caption";
					break;
				case 'base':
					$baseTemplateFilter = "exists(
						select top 1 1 from v_XmlTemplateBase with(nolock)
						where XmlTemplate_id = XT.XmlTemplate_id
					)";
					if(!empty($data['XmlTemplateCat_id'])){
						$filters[] = 'XT.XmlTemplateCat_id = :XmlTemplateCat_id';
						$params['XmlTemplateCat_id'] = $data['XmlTemplateCat_id'];
					}
					break;
				case 'shared':
					$filters[] = "XTSh.XmlTemplateShared_id is not null";
					break;
				case 'own':
					$filters[] = "XT.Lpu_id = :Lpu_id";
					$filters[] = "XT.pmUser_insID = :pmUser_id";

					if (!empty($data['XmlTemplateCat_id'])) {
						if ($data['XmlTemplateCat_id'] == 'root') {
							$filters[] = 'XT.XmlTemplateCat_id is null';
						} else {
							$filters[] = 'XT.XmlTemplateCat_id = :XmlTemplateCat_id';
							$params['XmlTemplateCat_id'] = $data['XmlTemplateCat_id'];
						}
					}
					break;
				default:
					$resp = $this->XmlTemplateCatDefault_model->getPath(array(
						'XmlType_id' => $data['XmlType_id'],
						'EvnClass_id' => $data['EvnClass_id'],
						'MedStaffFact_id' => $data['MedStaffFact_id'],
						'MedService_id' => $data['MedService_id'],
						'MedPersonal_id' => $data['MedPersonal_id'],
						'LpuSection_id' => $data['LpuSection_id'],
						'session' => $data['session'],
					));
					if (is_array($resp) && isset($resp[0]) && !empty($resp[0]['XmlTemplateCat_id'])) {
						$filters[] = "XT.XmlTemplateCat_id = :XmlTemplateCat_id";
						$params['XmlTemplateCat_id'] = $resp[0]['XmlTemplateCat_id'];
					}
					break;
			}
		}
		if (!empty($data['query'])) {
			$filters[] = "XT.XmlTemplate_Caption like :query+'%'";
			$params['query'] = $data['query'];
			$order = "XT.XmlTemplate_Caption";
		}

		$filters[] = $baseTemplateFilter;

		$filters_str = implode("\nand ", $filters);

		$query = "
			-- variables
			declare @date date = dbo.tzGetDate();
			-- end variables
			select
				-- select
				XT.XmlTemplate_id,
				XT.XmlTemplate_Caption,
				XT.XmlTemplate_Descr,
				XT.XmlTemplateCat_id,
				ISNULL(fav.v, 1) as XmlTemplate_IsFavorite,
				Author.pmUser_id as Author_id,
				(
					rtrim(Author.PMUser_SurName)+
					isnull(' '+left(Author.PMUser_FirName, 1)+'.', '')+
					isnull(' '+left(Author.PMUser_SecName, 1)+'.', '')
				) as Author_Fin,
				XType.XmlType_id,
				XType.XmlType_Name,
				EC.EvnClass_id,
				EC.EvnClass_SysNick,
				EC.EvnClass_Name,
				XTS.XmlTemplateScope_id,
				XTS.XmlTemplateScope_Name,
				XTSh.XmlTemplateShared_id,
				XTSh.XmlTemplateShared_IsReaded
				-- end select
			from
				-- from
				v_XmlTemplate XT with(nolock)
				outer apply (
					select top 1 2 as v from v_XmlTemplateSelected XTSe with(nolock)
					where XTSe.XmlTemplate_id = XT.XmlTemplate_id
					and XTSe.MedStaffFact_id = :MedStaffFact_id
					and XTSe.MedPersonal_id = :MedPersonal_id
					and XTSe.pmUser_insID = :pmUser_id
				) fav
				left join v_PMUserCache Author with(nolock) on Author.pmUser_id = XT.pmUser_insID
				left join v_XmlType XType with(nolock) on XType.XmlType_id = XT.XmlType_id
				left join v_EvnClass EC with(nolock) on EC.EvnClass_id = XT.EvnClass_id
				left join v_XmlTemplateScope XTS with(nolock) on XTS.XmlTemplateScope_id = XT.XmlTemplateScope_id
				left join v_XmlTemplateShared XTSh with(nolock) on XTSh.XmlTemplate_id = XT.XmlTemplate_id and XTSh.pmUser_getID = :pmUser_id and XTSh.Lpu_gid = :Lpu_id
				-- end from
			where
				-- where
				{$filters_str}
				-- end where
			order by
				-- order by
				{$order}
				-- end order by
		";
		//echo getDebugSQL($query, $params);exit;
		return $this->queryResult(getLimitSQLPH($query, 0, $limit), $params);
	}

	/**
	 * @param arrray $data
	 * @return array|false
	 */
	function loadXmlTemplateComboList($data) {
		$filters = array('1=1');
		$params = array();

		$params['LpuSection_id'] = !empty($data['LpuSection_id'])?$data['LpuSection_id']:null;
		$params['Lpu_id'] = $data['Lpu_id'];
		$params['pmUser_id'] = $data['pmUser_id'];

		$filters[] = "isnull(XT.XmlTemplate_IsDeleted, 1) = 1";
		$filters[] = "XT.Lpu_id = :Lpu_id";
		$filters[] = "(
			XTB.XmlTemplateBase_id is not null
			or XTSh.XmlTemplateShared_id is not null
			or XT.pmUser_insID = :pmUser_id 
			or (
				XT.XmlTemplateScope_id in (3,4)
				and XT.LpuSection_id is not null
				and (XT.XmlTemplateScope_id <> 4 or XT.LpuSection_id = :LpuSection_id)
			)
		)";

		/*if (!empty($data['query'])) {
			$filters[] = "XT.XmlTemplate_Caption like :query+'%'";
			$params['query'] = $data['query'];
		}*/

		if (!empty($data['query'])) {
			$filters[] = "XT.XmlTemplate_Caption like '%'+:query+'%'";
			$params['query'] = $data['query'];
		}


		if (!empty($data['mode']) && $data['mode'] == 'own') {
			$filters[] = "Section.Nick in ('own','shared')";
		} else if (!empty($data['mode']) && $data['mode'] != 'all') {
			$filters[] = "Section.Nick like :mode";
			$params['mode'] = $data['mode'];
		}
		if (!empty($data['EvnClass_id'])) {
			$filters[] = "XT.EvnClass_id = :EvnClass_id";
			$params['EvnClass_id'] = $data['EvnClass_id'];
		}
		if (!empty($data['XmlType_id'])) {
			$filters[] = "XT.XmlType_id = :XmlType_id";
			$params['XmlType_id'] = $data['XmlType_id'];
		}

		$filters_str = implode("\nand ", $filters);

		$query = "
			declare @pmUser_id bigint = :pmUser_id;
			select
				XT.XmlTemplate_id,
				XT.XmlTemplate_Caption,
				null as XmlTemplate_Path,
				null as XmlTemplate_PathText,
				XT.XmlTemplateCat_id,
				XTSh.XmlTemplateShared_id,
				LS.LpuSection_id,
				LS.LpuSection_Name,
				Section.Nick as SectionNick,
				Section.Name as SectionName
			from
				v_XmlTemplate XT with(nolock)
				left join v_XmlTemplateBase XTB with(nolock) on XTB.XmlTemplate_id = XT.XmlTemplate_id
				left join v_LpuSection LS with(nolock) on LS.LpuSection_id = XT.LpuSection_id
				outer apply (
					select top 1 XTSh.XmlTemplateShared_id
					from v_XmlTemplateShared XTSh with(nolock)
					where XTSh.XmlTemplate_id = XT.XmlTemplate_id
					and XTSh.pmUser_getID = @pmUser_id
					and XTSh.Lpu_gid = :Lpu_id
				) XTSh
				outer apply (
					select top 1 case
						when XTB.XmlTemplateBase_id is not null then 'base'
						when XTSh.XmlTemplateShared_id is not null then 'shared'
						when XT.pmUser_insID = @pmUser_id then 'own'
						else 'common'
					end as Nick,
					case
						when XTB.XmlTemplateBase_id is not null then 'Базовые'
						when XTSh.XmlTemplateShared_id is not null then 'Входящие шаблоны'
						when XT.pmUser_insID = @pmUser_id then 'Мои'
						else 'Общие'
					end as Name
				) Section
			where
				{$filters_str}
			order by
				XT.XmlTemplate_Caption
		";

		//echo getDebugSQL($query, $params);exit;
		$templates = $this->queryResult($query, $params);
		if (!is_array($templates)) {
			return false;
		}
		if (count($templates) == 0) {
			return array();
		}

		$ids = array();
		foreach($templates as $template) {
			$idName = 'XmlTemplateCat_id';
			if (!empty($template[$idName]) && $template['SectionNick'] != 'shared') {
				$ids[] = $template[$idName];
			}
		}

		$ids_str = implode(",", $ids);

		$query = "
			with tree as (
				select
					XTC1.XmlTemplateCat_id,
					XTC1.XmlTemplateCat_pid,
					XTC1.XmlTemplateCat_id as XmlTemplateCat_tid,
					XTC1.XmlTemplateCat_Name,
					XTC1.pmUser_insID as Author_id,
					1 as level
				from v_XmlTemplateCat XTC1 with(nolock)
				where XTC1.XmlTemplateCat_id in ({$ids_str})
				union all
				select
					XTC2.XmlTemplateCat_id,
					XTC2.XmlTemplateCat_pid,
					tree.XmlTemplateCat_tid,
					XTC2.XmlTemplateCat_Name,
					XTC2.pmUser_insID as Author_id,
					level+1 as level
				from v_XmlTemplateCat XTC2 with(nolock)
				inner join tree on tree.XmlTemplateCat_pid = XTC2.XmlTemplateCat_id
			)
			select
				tree.XmlTemplateCat_id,
				tree.XmlTemplateCat_pid,
				tree.XmlTemplateCat_tid,
				tree.XmlTemplateCat_Name,
				tree.Author_id
			from tree
			order by tree.level desc
		";

		$folders = array();

		if (count($ids) > 0) {
			$folders = $this->queryResult($query);
		}
		if (!is_array($folders)) {
			return false;
		}

		$foldersByTid = array();
		foreach($folders as $folder) {
			$key = $folder['XmlTemplateCat_tid'];
			$foldersByTid[$key][] = $folder;
		}

		foreach($templates as $index => &$template) {
			$tid = $template['XmlTemplateCat_id'];
			$path = "/root";
			$pathText = $template['SectionName'];

			if ($template['SectionNick'] == 'shared') {
				$pathText = 'Мои / '.$template['SectionName'];

				if (empty($data['mode']) || $data['mode'] == 'all') {
					$path .= '/own';
				}
				$path .= '/'.$template['SectionNick'];
			} else if (empty($data['mode']) || $data['mode'] == 'all') {
				$path .= '/'.$template['SectionNick'];
			}
			if ($template['SectionNick'] == 'common') {
				$path .= "/lpuSection-{$template['LpuSection_id']}";
				$pathText .= ' / '.$template['LpuSection_Name'];
			}

			if (!empty($tid) && !in_array($template['SectionNick'], array('base','shared'))) {
				$templateFolders = $foldersByTid[$tid];

				if ($template['SectionNick'] == 'own') {
					$isOwnFolders = true;
					foreach($templateFolders as $folder) {
						if ($folder['Author_id'] != $data['pmUser_id']) {
							$isOwnFolders = false;
							break;
						}
					}
					if (!$isOwnFolders) {
						unset($templates[$index]);
						continue;
					}
				}

				if ($template['SectionNick'] == 'common') {
					$path .= '/'.implode('/', array_map(function($folder) use($template) {
							return "lpuSection-{$template['LpuSection_id']}-folder-{$folder['XmlTemplateCat_id']}";
						}, $templateFolders));
				} else {
					$path .= '/'.implode('/', array_map(function($folder) {
							return "folder-{$folder['XmlTemplateCat_id']}";
						}, $templateFolders));
				}

				$pathText .= ' / '.implode(' / ', array_map(function($folder) {
						return $folder['XmlTemplateCat_Name'];
					}, $templateFolders));
			}

			$path .= "/template-{$template['XmlTemplate_id']}";

			$template['XmlTemplate_Path'] = $path;
			$template['XmlTemplate_PathText'] = $pathText;
		}

		return $templates;
	}

	/**
	 * Получение списка папок шаблонов
	 * @param array $data
	 * @return array|false
	 */
	function loadXmlTemplateCatList($data) {
		$params = array();
		$filters = array('1=1');
		$folderFilters = array('1=1');
		$templateFilters = array('1=1');

		$templateFilters[] = "not exists(
			select * from v_XmlTemplateBase XTB with(nolock)
			where XTB.XmlTemplate_id = XT.XmlTemplate_id
		)";

		if (!empty($data['XmlTemplateCat_pid'])) {
			if ($data['XmlTemplateCat_pid'] == 'root') {
				$filters[] = "XTC.XmlTemplateCat_pid is null";
			} else {
				$filters[] = "XTC.XmlTemplateCat_pid = :XmlTemplateCat_pid";
				$params['XmlTemplateCat_pid'] = $data['XmlTemplateCat_pid'];
			}
		} else if (!empty($data['query'])) {
			$filters[] = "XTC.XmlTemplateCat_Name like :query+'%'";
			$params['query'] = $data['query'];
		}

		if (!empty($data['XmlTemplateCat_ids']) && is_array($data['XmlTemplateCat_ids'])) {
			$ids_str = implode(",", $data['XmlTemplateCat_ids']);
			$filters[] = "XTC.XmlTemplateCat_id in ({$ids_str})";
		}

		if (!empty($data['EvnClass_id'])) {
			$templateFilters[] = "XT.EvnClass_id = :EvnClass_id";
			$params['EvnClass_id'] = $data['EvnClass_id'];
		}
		if (!empty($data['XmlType_id'])) {
			$templateFilters[] = "XT.XmlType_id = :XmlType_id";
			$params['XmlType_id'] = $data['XmlType_id'];
		}
		/*if (!empty($data['MedPersonal_id'])) {
			$templateFilters[] = "XT.MedPersonal_id = :MedPersonal_id";
			$params['MedPersonal_id'] = $data['MedPersonal_id'];
		}*/
		if (!empty($data['Lpu_id'])) {
			$filters[] = "XTC.Lpu_id = :Lpu_id";
			$folderFilters[] = "XTCChild.Lpu_id = :Lpu_id";
			$templateFilters[] = "XT.Lpu_id = :Lpu_id";
			$params['Lpu_id'] = $data['Lpu_id'];
		}
		if (!empty($data['pmUser_id'])) {
			$filters[] = "XTC.pmUser_insID = :pmUser_id";
			$folderFilters[] = "XTCChild.pmUser_insID = :pmUser_id";
			$templateFilters[] = "XT.pmUser_insID = :pmUser_id";
			$params['pmUser_id'] = $data['pmUser_id'];
		}

		$filtersStr = implode("\nand ", $filters);
		$folderFiltersStr = implode("\nand ", $folderFilters);
		$templateFiltersStr = implode("\nand ", $templateFilters);

		$query = "
			select
				XTC.XmlTemplateCat_id,
				XTC.XmlTemplateCat_pid,
				XTC.XmlTemplateCat_Name,
				ChildrenFolders.Count as childrenFoldersCount,
				ChildrenTemplates.Count as childrenTemplatesCount
			from
				v_XmlTemplateCat XTC with(nolock)
				outer apply (
					select top 1 count(*) as Count
					from v_XmlTemplateCat XTCChild with(nolock)
					where XTCChild.XmlTemplateCat_pid = XTC.XmlTemplateCat_id
					and {$folderFiltersStr}
				) ChildrenFolders
				outer apply (
					select top 1 count(*) as Count
					from v_XmlTemplate XT with(nolock)
					where XT.XmlTemplateCat_id = XTC.XmlTemplateCat_id
					and {$templateFiltersStr}
				) ChildrenTemplates
			where {$filtersStr}
		";

		//echo getDebugSQL($query, $params);exit;
		return $this->queryResult($query, $params);
	}

	/**
	 * @param array $data
	 * @return array|bool
	 */
	function getXmlTemplateCommonTree($data) {
		$params = $data;
		$folders = array();
		$templates = array();
		$filters = array('1=1');
		$response = array(
			'folders' => array(),
			'templates' => array(),
		);

		if (!empty($data['XmlType_id'])) {
			$filters[] = "XT.XmlType_id = :XmlType_id";
		}
		if (!empty($data['EvnClass_id'])) {
			$filters[] = "XT.EvnClass_id = :EvnClass_id";
		}
		if (!empty($data['pmUser_id'])) {
			$filters[] = "XT.pmUser_insID <> :pmUser_id";
		}

		if (!empty($data['XmlTypeKind_id']))
			$filters[] = "XT.XmlTypeKind_id = :XmlTypeKind_id";

		$getQuery = function($fields, $filters) {
			$filtresStr = implode("\nand ", $filters);
			return "
				select distinct
					{$fields}
				from
					v_XmlTemplate XT with(nolock)
					inner join v_Lpu L with(nolock) on L.Lpu_id = XT.Lpu_id
					left join v_XmlTemplateCat XTC with(nolock) on XTC.XmlTemplateCat_id = XT.XmlTemplateCat_id
					left join v_LpuSection LS with(nolock) on LS.Lpu_id = L.Lpu_id and LS.LpuSection_id = isnull(XT.LpuSection_id, XTC.LpuSection_id)
					left join v_pmUserCache Author with(nolock) on Author.pmUser_id = XT.pmUser_insID
					left join v_XmlType XType with(nolock) on XType.XmlType_id = XT.XmlType_id
					left join v_EvnClass EC with(nolock) on EC.EvnClass_id = XT.EvnClass_id
					left join v_XmlTemplateScope XTS with(nolock) on XTS.XmlTemplateScope_id = XT.XmlTemplateScope_id
					left join v_MedPersonal MP with(nolock) on MP.MedPersonal_id = XT.MedPersonal_id
					outer apply (
						select top 1 case when exists(
							select * from v_XmlTemplateSelected 
							where XmlTemplate_id = XT.XmlTemplate_id
							and MedStaffFact_id = :MedStaffFact_id
							and MedPersonal_id = :MedPersonal_id
							and pmUser_id = :pmUser_id
						) then 2 else 1 end as value
					) IsFavorite
				where
					isnull(XT.XmlTemplate_IsDeleted, 1) = 1
					and XT.Lpu_id = :Lpu_id
					and XT.XmlTemplateScope_id in (2,3,4)
					and (XT.XmlTemplateScope_id <> 4 or XT.LpuSection_id = :LpuSection_id)
					and {$filtresStr}
					and not exists(
						select * from v_XmlTemplateBase XTB with(nolock)
						where XTB.XmlTemplate_id = XT.XmlTemplate_id
					)
			";
		};

		if (empty($data['id']) && empty($data['LpuSection_sid']) && empty($data['XmlTemplateCat_id'])) {
			$sql = "SELECT Lpu_Nick FROM v_Lpu with(nolock) where Lpu_id = {$data['Lpu_id']}";
			$res = $this->db->query($sql)->result('array');
			$folders[] = array(
				'XmlTemplateCat_Name' => $res[0]['Lpu_Nick'],
				'id' => 'lpu-common',
				'nodeType' => 'FolderNode',
				'node' => 'common',
				'childrenTemplatesCount' => 1,
				'sort' => 0
			);

			$filters[] = 'LS.LpuSection_id is not null';

			$sectionFilters = array();

			if (!empty($data['query'])) {
				$sectionFilters[] = "LS.LpuSection_Name like :query+'%'";
			}

			$query = $getQuery("
				LS.LpuSection_id,
				LS.LpuSection_Name
			", array_merge(
				$filters,
				$sectionFilters
			));

			$resp = $this->queryResult($query, $params);
			if (!is_array($resp)) {
				return false;
			}
			$LpuSectionFolders = array();
			foreach($resp as $item) {
				$key = $item['LpuSection_id'];
				$item['id'] = "lpuSection-{$key}";
				$item['nodeType'] = "LpuSectionNode";
				$item['node'] = 'common';

				$folders[] = $item;
			}

			$response['folders'] = array_merge($response['folders'], $folders);
			if (empty($data['query'])) {
				return $response;
			}
		}

		$filters[] = !empty($data['LpuSection_sid']) ? "LS.LpuSection_id = :LpuSection_sid" : "LS.LpuSection_id is null";

		if (!empty($data['LpuSection_sid']) && empty($data['MedPersonal_sid']) && empty($data['XmlTemplateCat_id'])) {
			$query = $getQuery("
				LS.LpuSection_id,
				MP.MedPersonal_id,
				MP.Person_FirName,
				MP.Person_SecName,
				LEFT(MP.Person_SurName, 1) as Person_SurName
			", array_merge($filters, array('MP.MedPersonal_id is not null')));

			$resp = $this->queryResult($query, $params);
			if (!is_array($resp)) {
				return false;
			}

			foreach($resp as $item) {
				$item['LpuSection_id'] = $data['LpuSection_sid'];
				$item['MedPersonal_FIO'] = $item['Person_FirName'].' '.$item['Person_SecName'].' '.$item['Person_SurName'];
				$item['id'] = "lpuSection-{$item['LpuSection_id']}-medPersonal-{$item['MedPersonal_id']}";
				$item['nodeType'] = "MedPersonalNode";
				$item['node'] = 'common';
				
				$folders[] = $item;
			}

			$response['folders'] = array_merge($response['folders'], $folders);
		}
		
		$filters[] = !empty($data['MedPersonal_sid']) ? 'MP.MedPersonal_id = :MedPersonal_sid':'MP.MedPersonal_id is null';

		$folderFilters = array();
		$templateFilters = array();

		if (empty($data['XmlTemplateCat_id'])) {
			if (empty($data['query'])) {
				$folderFilters[] = "XTC.XmlTemplateCat_id is not null";
				$folderFilters[] = "XTC.XmlTemplateCat_pid is null";
				$templateFilters[] = "XT.XmlTemplateCat_id is null";
			} else {
				$folderFilters[] = "XTC.XmlTemplateCat_id is not null";
				$folderFilters[] = "XTC.XmlTemplateCat_Name like :query+'%'";
				$templateFilters[] = "XT.XmlTemplate_Caption like :query+'%'";
			}
		} else {
			$folderFilters[] = "XTC.XmlTemplateCat_pid = :XmlTemplateCat_id";
			$templateFilters[] = "XT.XmlTemplateCat_id = :XmlTemplateCat_id";
		}

		$folderFiltersStr = implode("\nand ", $folderFilters);
		$query = "
			with list as (
				{$getQuery("
					XTC.XmlTemplateCat_id,
					XTC.XmlTemplateCat_pid,
					LS.LpuSection_id,
					MP.MedPersonal_id,
					1 as level
				", $filters)}
				union all
				select
					XTC.XmlTemplateCat_id,
					XTC.XmlTemplateCat_pid,
					list.LpuSection_id,
					list.MedPersonal_id,
					level+1 as level
				from v_XmlTemplateCat XTC with(nolock)
				inner join list on list.XmlTemplateCat_pid = XTC.XmlTemplateCat_id and list.XmlTemplateCat_id <> list.XmlTemplateCat_pid
				where XTC.LpuSection_id is not null
			)
			select distinct
				XTC.XmlTemplateCat_id,
				XTC.XmlTemplateCat_pid,
				XTC.XmlTemplateCat_Name,
				list.LpuSection_id,
				list.MedPersonal_id,
				list.level,
				ChildrenFolders.Count as childrenFoldersCount,
				ChildrenTemplates.Count as childrenTemplatesCount
			from list
			inner join v_XmlTemplateCat XTC with(nolock) on XTC.XmlTemplateCat_id = list.XmlTemplateCat_id
			outer apply (
				select top 1
					count(*) as Count
				from (
					{$getQuery("XTC.XmlTemplateCat_id", $filters)}
					and XTC.XmlTemplateCat_pid = list.XmlTemplateCat_id
				) T
			) ChildrenFolders
			outer apply (
				select top 1
					count(*) as Count
				from (
					{$getQuery("XT.XmlTemplate_id", $filters)}
					and XT.XmlTemplateCat_id = list.XmlTemplateCat_id
				) T
			) ChildrenTemplates
			where {$folderFiltersStr}
			order by level, XTC.XmlTemplateCat_pid
			option (maxrecursion 0)
		";
		//echo getDebugSQL($query, $params);exit;
		$resp = $this->queryResult($query, $params);
		if (!is_array($resp)) {
			return false;
		}

		foreach($resp as $item) {
			$key = $item['XmlTemplateCat_id'];
			$lpuSectionKey = $item['LpuSection_id'];

			$item['id'] = "folder-{$key}";
			if(!empty($lpuSectionKey)) $item['id'] .= "-lpuSection-{$lpuSectionKey}";
			if(!empty($item['MedPersonal_id'])) $item['id'] .= "-medPersonal-{$item['MedPersonal_id']}";
			$item['nodeType'] = 'FolderNode';
			$item['node'] = 'common';

			$folders[] = $item;
		}

		$query = $getQuery("
			XT.XmlTemplate_id,
			XT.XmlTemplate_Caption,
			XT.XmlTemplateCat_id,
			LS.LpuSection_id,
			MP.MedPersonal_id,
			IsFavorite.value as XmlTemplate_IsFavorite,
			Author.pmUser_id as Author_id,
			(
				rtrim(Author.PMUser_SurName)+
				isnull(' '+left(Author.PMUser_FirName, 1)+'.', '')+
				isnull(' '+left(Author.PMUser_SecName, 1)+'.', '')
			) as Author_Fin,
			XType.XmlType_id,
			XType.XmlType_Name,
			EC.EvnClass_id,
			EC.EvnClass_SysNick,
			EC.EvnClass_Name,
			XTS.XmlTemplateScope_id,
			XTS.XmlTemplateScope_Name
		", array_merge(
			$filters,
			$templateFilters
		));
		$resp = $this->queryResult($query, $params);
		if (!is_array($resp)) {
			return false;
		}

		foreach($resp as $item) {
			$key = $item['XmlTemplate_id'];
			$folderKey = $item['XmlTemplateCat_id'];
			$lpuSectionKey = $item['LpuSection_id'];

			$item['id'] = "template-{$key}";
			$item['nodeType'] = 'TemplateNode';
			$item['node'] = 'common';

			$templates[] = $item;
		}

		$response['folders'] = array_merge($response['folders'], $folders);
		$response['templates'] = array_merge($response['templates'], $templates);
		return $response;
	}

	/**
	 * @param array $data
	 * @return array|bool
	 */
	function loadXmlTemplateTree($data) {
		$folders = array();
		$templates = array();
		$mode = !empty($data['mode'])?$data['mode']:null;
		$onlyFolders = (!empty($data['onlyFolders']) && $data['onlyFolders']);

		$params = function($arg = array()) use($data) {
			return array_merge(array(
				'query' => !empty($data['query'])?$data['query']:null,
				'MedStaffFact_id' => !empty($data['MedStaffFact_id'])?$data['MedStaffFact_id']:null,
				'MedPersonal_id' => !empty($data['MedPersonal_id'])?$data['MedPersonal_id']:null,
				'LpuSection_id' => !empty($data['LpuSection_id'])?$data['LpuSection_id']:null,
				'EvnClass_id' => !empty($data['EvnClass_id'])?$data['EvnClass_id']:null,
				'XmlType_id' => !empty($data['XmlType_id'])?$data['XmlType_id']:null,
				'Lpu_id' => $data['Lpu_id'],
				'pmUser_id' => $data['pmUser_id'],
			), $arg);
		};

		switch($data['node']) {
			case 'root':
				$modeSectionsMap = array(
					'all' => array('own','base','common'),
					'own' => array('own'),
					'base' => array('base'),
					'common' => array('common'),
				);
				$sections = $modeSectionsMap[$mode];

				if (!empty($data['query'])) {
					$response = array();
					foreach($sections as $section) {
						$data['node'] = $section;
						$resp = $this->loadXmlTemplateTree($data);
						if (!is_array($resp)) {
							return $resp;
						} else {
							$response = array_merge($response, $resp);
						}
					}
					return $response;
				}

				$folders = array(
					array(
						'id' => 'own',
						'text' => 'Мои',
						'nodeType' => 'SectionNode',
						'sort' => 1,
					),
					array(
						'id' => 'base',
						'text' => 'Базовые',
						'nodeType' => 'SectionNode',
						'sort' => 2,
					),
					array(
						'id' => 'common',
						'text' => 'Общие',
						'nodeType' => 'SectionNode',
						'sort' => 3,
					),
				);

				$folders = array_filter($folders, function($folder) use($sections) {
					return in_array($folder['id'], $sections);
				});
				break;

			case 'own':
				if (!empty($data['XmlTemplateCat_id'])) {
					$query = null;
					$XmlTemplateCat_id = $data['XmlTemplateCat_id'];
				} else {
					$query = !empty($data['query'])?$data['query']:null;
					$XmlTemplateCat_id = empty($data['query'])?'root':null;
				}

				$folders = $this->loadXmlTemplateCatList($params(array(
					'XmlTemplateCat_pid' => $XmlTemplateCat_id,
					'query' => $query,
				)));
				if (!is_array($folders)) {
					return false;
				}

				if (!$onlyFolders) {
					$templates = $this->loadXmlTemplateList($params(array(
						'mode' => 'own',
						'XmlTemplateCat_id' => $XmlTemplateCat_id,
						'query' => $query,
					)));
					if (!is_array($templates)) {
						return false;
					}
				}

				foreach($folders as &$folder) {
					$folder['id'] = "folder-{$folder['XmlTemplateCat_id']}";
					$folder['nodeType'] = 'FolderNode';
					$folder['node'] = 'own';
				}
				foreach($templates as &$template) {
					$template['id'] = "template-{$template['XmlTemplate_id']}";
					$template['nodeType'] = 'TemplateNode';
					$template['node'] = 'own';
				}

				if (empty($data['XmlTemplateCat_id']) && !$onlyFolders) {
					$count = $this->getFirstResultFromQuery("
						select top 1 count(*) as cnt
						from v_XmlTemplateShared with(nolock)
						where pmUser_getID = :pmUser_id
						and Lpu_gid = :Lpu_id
					", $params());

					$folders[] = array(
						'id' => 'shared',
						'text' => 'Входящие шаблоны',
						'nodeType' => 'SharedNode',
						'childrenFoldersCount' => $count,
						'sort' => 1,
					);
				}
				break;

			case 'shared':
				$templates = $this->loadXmlTemplateList($params(array(
					'mode' => 'shared',
					'query' => !empty($data['query'])?$data['query']:null,
				)));
				if (!is_array($templates)) {
					return false;
				}

				foreach($templates as &$template) {
					$template['id'] = "template-{$template['XmlTemplate_id']}";
					$template['nodeType'] = 'TemplateNode';
					$template['node'] = 'shared';
				}
				break;

			case 'base':
				$templates = $this->loadXmlTemplateList($params(array(
					'mode' => 'base',
					'XmlTemplateCat_id' => !empty($data['XmlTemplateCat_id'])?$data['XmlTemplateCat_id']:null,
				)));
				if (!is_array($templates)) {
					return false;
				}
				foreach($templates as &$template) {
					$template['id'] = "template-{$template['XmlTemplate_id']}";
					$template['nodeType'] = 'TemplateNode';
					$template['node'] = 'base';
				}
				break;

			case 'common':
				$resp = $this->getXmlTemplateCommonTree($params(array(
					'LpuSection_sid' => !empty($data['LpuSection_sid'])?$data['LpuSection_sid']:null,
					'MedPersonal_sid' => !empty($data['MedPersonal_sid'])?$data['MedPersonal_sid']:null,
					'XmlTemplateCat_id' => !empty($data['XmlTemplateCat_id'])?$data['XmlTemplateCat_id']:null,
					'id' => !empty($data['id'])?$data['id']:null,
				)));
				if (!$resp) return false;
				$folders = $resp['folders'];
				$templates = $resp['templates'];
				break;
		};

		return array_merge($folders, $templates);
	}

	/**
	 * @param array $data
	 * @return array
	 */
	function getParamsForEmptyXmlTemplate($data) {
		if (empty($data['XmlType_id']) || empty($data['EvnClass_id'])) {
			return $this->createError('', 'Отсутствуют необходимые параметры');
		}
		if (empty($data['Evn_id'])) {
			$data['Evn_id'] = null;
		}

		$query = "
			select top 1
				null as EvnXml_id,
				'' as EvnXml_Name,
				1 as EvnXml_IsSigned,
				'<data></data>' as EvnXml_Data,
				null as XmlTemplate_id,
				6 as XmlTemplateType_id,
				XType.XmlType_id,
				XType.XmlType_Name,
				'' as XmlTemplate_Caption,
				'' as XmlTemplate_HtmlTemplate,
				'' as XmlTemplate_Settings,
				'' as XmlTemplate_Data,
				Author.pmUser_id as Author_id,
				(
					rtrim(Author.PMUser_SurName)+
					isnull(' '+left(Author.PMUser_FirName, 1)+'.', '')+
					isnull(' '+left(Author.PMUser_SecName, 1)+'.', '')
				) as Author_Fin,
				null as XmlTemplateSettings_id,
				5 as XmlTemplateScope_id,
				null as XmlTemplateCat_id,
				'' as XmlTemplate_Descr,
				E.Evn_id,
				E.Evn_pid,
				E.Evn_rid,
				EC.EvnClass_id,
				EC.EvnClass_Name,
				L.Lpu_id,
				L.Lpu_Nick
			from 
				v_pmUserCache Author with(nolock)
				left join v_XmlType XType with(nolock) on XType.XmlType_id = :XmlType_id
				left join v_EvnClass EC with(nolock) on EC.EvnClass_id = :EvnClass_id
				left join v_Lpu L with(nolock) on L.Lpu_id = :Lpu_id
				left join v_Evn E with(nolock) on E.Evn_id = :Evn_id
			where 
				Author.pmUser_id = :pmUser_id
		";
		$params = $this->getFirstRowFromQuery($query, $data);
		if (!is_array($params)) {
			return $this->createError('', 'Ошибка при получении данных документа');
		}

		$params['EvnXml_DataSettings'] = array();

		return array(array(
			'success' => true,
			'params' => $params
		));
	}

	/**
	 * @param array $data
	 * @return array
	 */
	function getParamsByXmlTemplateOrEvnXml($data) {
		$params = array(
			'Person_id' => !empty($data['Person_id'])?$data['Person_id']:null,
			'Evn_id' => !empty($data['Evn_id'])?$data['Evn_id']:null,
			'XmlTemplate_id' => !empty($data['XmlTemplate_id'])?$data['XmlTemplate_id']:null,
			'EvnXml_id' => !empty($data['EvnXml_id'])?$data['EvnXml_id']:null,
			'XmlType_id' => !empty($data['XmlType_id'])?$data['XmlType_id']:null,
			'EvnClass_id' => !empty($data['EvnClass_id'])?$data['EvnClass_id']:null,
			'Author_id' => null,
			'XmlTemplateCat_id' => null,
			'XmlTemplateScope_id' => null,
			'XmlTemplateSettings_id' => null,
			'XmlTemplate_Caption' => null,
			'XmlTemplate_Descr' => null,
			'EvnXml_Name' => null,
			'EvnXml_IsSigned' => null,
			'EvnXml_Data' => null,
		);

		$response = array(
			'success' => true,
			'params' => null,
		);

		$reset = (!empty($data['reset']) && $data['reset']);

		if (empty($params['EvnXml_id']) && empty($params['XmlTemplate_id'])) {
			return $this->createError('','Необходим идентификатор документа или шаблона');
		}

		if (!empty($data['EvnXml_id'])) {
			$query = "
				select top 1
					EX.EvnXml_id,
					EX.EvnXml_Name,
					EX.EvnXml_IsSigned,
					EX.EvnXml_Data,
					EX.XmlTemplate_id,
					EX.XmlTemplateType_id,
					XType.XmlType_id,
					XType.XmlType_Name,
					EX.EvnXml_Name as XmlTemplate_Caption,
					XTH.XmlTemplateHtml_HtmlTemplate as XmlTemplate_HtmlTemplate,
					XTS.XmlTemplateSettings_Settings as XmlTemplate_Settings,
					XTD.XmlTemplateData_Data as XmlTemplate_Data,
					Author.pmUser_id as Author_id,
					(
						rtrim(Author.PMUser_SurName)+
						isnull(' '+left(Author.PMUser_FirName, 1)+'.', '')+
						isnull(' '+left(Author.PMUser_SecName, 1)+'.', '')
					) as Author_Fin,
					EX.XmlTemplateSettings_id,
					XT.XmlTemplateScope_id,
					XT.XmlTemplateCat_id,
					XT.XmlTemplate_Descr,
					E.Evn_id,
					E.Evn_pid,
					E.Evn_rid,
					EC.EvnClass_id,
					EC.EvnClass_Name,
					L.Lpu_id,
					L.Lpu_Nick
				from
					v_EvnXml EX with(nolock)
					left join v_XmlTemplate XT with(nolock) on XT.XmlTemplate_id = EX.XmlTemplate_id
					left join v_XmlTemplateHtml XTH with(nolock) on XTH.XmlTemplateHtml_id = EX.XmlTemplateHtml_id
					left join v_XmlTemplateSettings XTS with(nolock) on XTS.XmlTemplateSettings_id = EX.XmlTemplateSettings_id
					left join v_XmlTemplateData XTD with(nolock) on XTD.XmlTemplateData_id = EX.XmlTemplateData_id
					left join v_Evn E with(nolock) on E.Evn_id = EX.Evn_id
					left join v_pmUserCache Author with(nolock) on Author.pmUser_id = XT.pmUser_insID
					left join v_EvnClass EC with(nolock) on EC.EvnClass_id = E.EvnClass_id
					left join v_XmlType XType with(nolock) on XType.XmlType_id = EX.XmlType_id
					left join v_Lpu L with(nolock) on L.Lpu_id = XT.Lpu_id
				where
					EX.EvnXml_id = :EvnXml_id
			";

			$resp = $this->getFirstRowFromQuery($query, $params);

			if (!is_array($resp)) {
				return $this->createError('', 'Ошибка при получении данных документа');
			}
			if (empty($params['XmlTemplate_id']) && !empty($resp['XmlTemplate_id'])) {
				$params['XmlTemplate_id'] = $resp['XmlTemplate_id'];
			}
			if ($params['XmlTemplate_id'] == $resp['XmlTemplate_id'] && !$reset) {
				$params = array_merge($params, $resp);
			} else {
				$params['EvnXml_id'] = null;	//Если изменили шаблон, то сохраненные данные не актуальны
			}
			if (!empty($data['Evn_id'])) {
				$params['Evn_id'] = $data['Evn_id'];
			}
		}

		if (!empty($params['XmlTemplate_id']) && empty($params['EvnXml_id'])) {
			$query = "
				select top 1
					XType.XmlType_id,
					XType.XmlType_Name,
					XT.XmlTemplateType_id,
					XT.XmlTemplate_Caption,
					XT.XmlTemplate_Descr,
					XT.XmlTemplateScope_id,
					XT.XmlTemplateCat_id,
					XT.XmlTemplateSettings_id,
					Author.pmUser_id as Author_id,
					(
						rtrim(Author.PMUser_SurName)+
						isnull(' '+left(Author.PMUser_FirName, 1)+'.', '')+
						isnull(' '+left(Author.PMUser_SecName, 1)+'.', '')
					) as Author_Fin,
					XTH.XmlTemplateHtml_HtmlTemplate as XmlTemplate_HtmlTemplate,
					XTS.XmlTemplateSettings_Settings as XmlTemplate_Settings,
					XTD.XmlTemplateData_Data as XmlTemplate_Data,
					E.Evn_id,
					E.Evn_pid,
					E.Evn_rid,
					EC.EvnClass_id,
					EC.EvnClass_Name,
					L.Lpu_id,
					L.Lpu_Nick
				from
					v_XmlTemplate XT with(nolock)
					left join v_XmlTemplateHtml XTH with(nolock) on XTH.XmlTemplateHtml_id = XT.XmlTemplateHtml_id
					left join v_XmlTemplateSettings XTS with(nolock) on XTS.XmlTemplateSettings_id = XT.XmlTemplateSettings_id
					left join v_XmlTemplateData XTD with(nolock) on XTD.XmlTemplateData_id = XT.XmlTemplateData_id
					left join v_Evn E with(nolock) on E.Evn_id = :Evn_id
					left join v_pmUserCache Author with(nolock) on Author.pmUser_id = XT.pmUser_insID
					left join v_EvnClass EC with(nolock) on EC.EvnClass_id = XT.EvnClass_id
					left join v_XmlType XType with(nolock) on XType.XmlType_id = XT.XmlType_id
					left join v_Lpu L with(nolock) on L.Lpu_id = XT.Lpu_id
				where
					XT.XmlTemplate_id = :XmlTemplate_id
			";
			$resp = $this->getFirstRowFromQuery($query, $params);

			if (!is_array($resp)) {
				return $this->createError('', 'Ошибка при получении данных шаблона');
			}

			$params = array_merge($params, $resp);
		}

		$this->load->library('swXmlTemplate');

		if (empty($params['EvnXml_id'])) {
			$params['EvnXml_id'] = !empty($data['EvnXml_id'])?$data['EvnXml_id']:null;
			$params['EvnXml_Name'] = $params['XmlTemplate_Caption'];
			$params['EvnXml_Data'] = swXmlTemplate::createEvnXmlData($params['XmlTemplate_Data'], false);
		}

		$params['EvnXml_DataSettings'] = swXmlTemplate::getXmlTemplateSettings($params['XmlTemplate_Data']);

		if (!empty($data['XmlTemplate_HtmlTemplate']) && isset($data['EvnXml_Data'])) {
			$params['XmlTemplate_HtmlTemplate'] = $data['XmlTemplate_HtmlTemplate'];
			$EvnXml_Data = is_array($data['EvnXml_Data'])?$data['EvnXml_Data']:json_decode($data['EvnXml_Data'], true);
			$rootKey = swXmlTemplate::EVN_XML_DATA_ROOT_ELEMENT;
			$params['EvnXml_Data'] = "<{$rootKey}>";
			foreach($EvnXml_Data as $key => $value) {
				$params['EvnXml_Data'] .= "<{$key}>".htmlspecialchars($value)."</{$key}>";
			}
			$params['EvnXml_Data'] .= "</{$rootKey}>";
		}

		$response['params'] = $params;

		return array($response);
	}

	/**
	 * Получение шаблона для отображения отображения в документе
	 * @param array $data
	 * @return array
	 */
	function getXmlTemplateForEvnXml($data) {
		$loadEmpty = (!empty($data['loadEmpty']) && $data['loadEmpty']);

		if ($loadEmpty || (empty($data['EvnXml_id']) && empty($data['XmlTemplate_id']))) {
			$resp = $this->getParamsForEmptyXmlTemplate($data);
		} else {
			$resp = $this->getParamsByXmlTemplateOrEvnXml($data);
		}
		if (!$this->isSuccessful($resp)) {
			return $resp;
		}
		$params = $resp[0]['params'];

		$response = array(
			'success' => true,
			'xmlData' => null,
			'xmlDataSettings' => $params['EvnXml_DataSettings'],
			'template' => null,
			'XmlDataSections' => null,
			'ParameterValues' => null,
			'SpecMarkers' => null,
			'AnketMarkers' => null,
			'EvnXml_Name' => $params['EvnXml_Name'],
			'EvnXml_IsSigned' => $params['EvnXml_IsSigned'],
			'XmlTemplate_Caption' => $params['XmlTemplate_Caption'],
			'XmlTemplate_Descr' => $params['XmlTemplate_Descr'],
			'XmlTemplateScope_id' => $params['XmlTemplateScope_id'],
			'XmlTemplateCat_id' => $params['XmlTemplateCat_id'],
			'XmlTemplateSettings_id' => $params['XmlTemplateSettings_id'],
			'Author_id' => $params['Author_id'],
			'Author_Fin' => $params['Author_Fin'],
			'XmlType_id' => $params['XmlType_id'],
			'XmlType_Name' => $params['XmlType_Name'],
			'EvnClass_id' => $params['EvnClass_id'],
			'EvnClass_Name' => $params['EvnClass_Name'],
			'Lpu_id' => $params['Lpu_id'],
			'Lpu_Nick' => $params['Lpu_Nick'],
		);

		$this->load->library('swEvnXml');
		$this->load->library('swXmlTemplate');

		$response['xmlData'] = swXmlTemplate::transformEvnXmlDataToArr($params['EvnXml_Data']);
		if (count($response['xmlData']) > 0) {
			$response['XmlDataSections'] = $this->loadXmlDataSectionList(array(
				'EvnXml_DataSettings' => $params['EvnXml_DataSettings']
			));
			$response['ParameterValues'] = $this->loadParameterValueList(array(
				'EvnXml_DataSettings' => $params['EvnXml_DataSettings']
			));
		}

		$xml_data = array($params);
		$parse_data = array();
		$object_data = array();

		try {
			$response['template'] = swEvnXml::doHtmlView($xml_data, $parse_data, $object_data, false, false);
		} catch(Exception $e) {
			return $this->createError('',$e->getMessage());
		}

		$convertResponse = $this->convertTemplateBlocks(
			$params['EvnXml_id'],
			$params['XmlType_id'],
			$params['EvnClass_id'],
			$response['template'],
			$response['xmlData'],
			$response['xmlDataSettings'],
			$response['ParameterValues']
		);

		$response['originalXmlData'] = $response['xmlData'];
		$response['xmlData'] = $convertResponse['xmlData'];
		$response['xmlDataSettings'] = $convertResponse['xmlDataSettings'];
		$response['template'] = $convertResponse['template'];

		$response['SpecMarkers'] = array();
		$specMarkersTemplate = $response['template'].implode(' ', array_values($response['xmlData']));
		if (preg_match_all("/@#@([а-яА-ЯЁё0-9]+)/u", $specMarkersTemplate, $matches, PREG_PATTERN_ORDER)) {
			$response['SpecMarkers'] = $this->loadSpecMarkerList(array(
				'EvnClass_id' => $params['EvnClass_id'],
				'names' => count($matches[1]) > 0 ? $matches[1] : null,
			));
		}
		
		$AnketMarkersIds = array();
		if (preg_match_all("/@#@anketa_([0-9]+)/u", $specMarkersTemplate, $matches, PREG_PATTERN_ORDER)) {
			$AnketMarkersIds = $matches[1];
			
			$response['AnketMarkers'] = $this->loadAnketMarkerList(array(
				'ids' => count($matches[1]) > 0 ? $matches[1] : null,
			));
		}
		
		if (count($AnketMarkersIds) > 0) {
			$resp = $this->getAnketMarkerContent($AnketMarkersIds);
			if (!$this->isSuccessful($resp)) {
				return $resp;
			}

			foreach($resp[0]['data'] as $item) {
				$key = "AnketMarker_{$item['id']}";
				$response['originalXmlData'][$key] = json_encode($item['content']);
				$response['xmlData'][$key] = json_encode($item['content']);
			}
		}
		
		//Получение содержимого маркеров
		if (!empty($params['Evn_id'])) {
			$SpecMarkerIds = array();
			foreach($response['SpecMarkers'] as $SpecMarker) {
				$key = "specMarker_{$SpecMarker['id']}";
				if (!isset($response['xmlData'][$key])) {
					$SpecMarkerIds[] = $SpecMarker['id'];
				}
			}

			if (count($SpecMarkerIds) > 0) {
				$resp = $this->getSpecMarkerContent(array(
					'SpecMarkerIds' => $SpecMarkerIds,
					'Evn_id' => $params['Evn_id'],
				));
				if (!$this->isSuccessful($resp)) {
					return $resp;
				}

				foreach($resp[0]['data'] as $item) {
					$key = "specMarker_{$item['id']}";
					$response['originalXmlData'][$key] = $item['content'];
					$response['xmlData'][$key] = $item['content'];
				}
			}

			if (preg_match_all("/{xmltemplatemarkerblock data=\"(.*)\"}/u" , $response['template'], $matches, PREG_PATTERN_ORDER)) {
				foreach($matches[1] as $markerDataStr) {
					$markerData = json_decode($markerDataStr, true);

					$_markerData = array();
					foreach($markerData as $key => $item) {
						$_markerData[$key] = $item['value'];
					}

					$markerKey = $this->getMarkerKey($_markerData);

					if (isset($response['xmlData'][$markerKey])) {
						continue;
					}

					$resp = $this->getMarkerContent(array(
						'markerData' => $_markerData,
						'Evn_id' => $params['Evn_id'],
					));
					if (!$this->isSuccessful($resp)) {
						return $resp;
					}

					$response['originalXmlData'][$markerKey] = $resp[0]['content'];
					$response['xmlData'][$markerKey] = $resp[0]['content'];
				}
			}
		}

		return array($response);
	}

	/**
	 * @param string $html
	 * @return DOMDocument
	 * @throws Exception
	 */
	function loadHtmlDom($html, $xmlData = array()) {
		libxml_use_internal_errors(true);
		$ignore_error_codes = array(
			801, 	//неразпознанный тег
			513,	//повторяющийся идентификатор
			76,		//Unexpected end tag
			201,	//Namespace prefix o is not defined
			68,		//error parsing attribute name
		);

		if (strpos($html, '<html>') === false) {
			$html = "
			<html>
				<head>
					<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"/>
				</head>
				<body>
						{$html}
				</body>
			</html>
			";
		}

		$dom = new DOMDocument();
		@$dom->loadHTML($html, LIBXML_PARSEHUGE);

		$alerts = array();
		$errors = array();
		$alredyDefinedIds = array();

		foreach (libxml_get_errors() as $error) {
			if ($error->code == '513') {
				preg_match('/^ID (.+) already defined$/', $error->message, $match);
				$id = $match[1];
				if (!in_array($id, $alredyDefinedIds)) {
					$alredyDefinedIds[] = $id;
				}
			}
			if (!in_array($error->code, $ignore_error_codes)) {
				$errors[] = "{$error->code}: {$error->message}";
			}
		}
		libxml_clear_errors();

		return array(
			'alerts' => $alerts,
			'errors' => $errors,
			'dom' => $dom,
		);
	}

	/**
	 * Конвертация шаблона для редактора на Ext6
	 * @param array $data
	 * @return string
	 */
	function convertTemplateBlocks($id, $xmlType, $class, $template, $xmlData, $xmlDataSettings, $ParameterValues = array()) {
		$existsEl = function($el) {
			return $el && !empty($el->nodeName);
		};
		$removeEl = function($el) {
			if ($el->nextSibling && $el->nextSibling->nodeName == 'br') {
				$el->parentNode->removeChild($el->nextSibling);
			}
			$el->parentNode->removeChild($el);
		};
		$removeText = function($dom, $text) use($removeEl) {
			$xpath = new DOMXPath($dom);
			$nodes = $xpath->query('//text()');
			foreach($nodes as $node) {
				if (strpos($node->nodeValue, $text) !== false) {
					$nodeValue = str_replace($text, '', $node->nodeValue);
					$checkNodeValue = trim($nodeValue);
					if (empty($checkNodeValue)) {
						$removeEl($node);
					} else {
						$node->nodeValue = $nodeValue;
					}
				}
			}
		};

		$resp = $this->loadHtmlDom($template, $xmlData);

		if (count($resp['errors']) > 0) {
			throw new Exception(implode("<br/>", $resp['errors']));
		}
		if (count($resp['alerts']) > 0) {
			$this->_setAlertMsg(implode("<br/>", $resp['alerts']));
		}

		$d = $resp['dom'];

		foreach($xmlData as $key => $value) {
			$block_el = $d->getElementById("block_{$key}");
			$block_caption_el = $d->getElementById("caption_{$key}");
			$block_data_el = $d->getElementById("data_{$key}");

			if (!$existsEl($block_el) && $id) {
				$block_el = $d->getElementById("block_{$key}_{$id}");
				$block_caption_el = $d->getElementById("caption_{$key}_{$id}");
				$block_data_el = $d->getElementById("data_{$key}_{$id}");
			}

			$new_block_el = null;

			if ($existsEl($block_el)/* && $block_caption_el && $block_data_el*/) {
				$xmlDataSettings[$key]['fieldLabel'] = "<strong>{$xmlDataSettings[$key]['fieldLabel']}</strong>";
				$new_block_el = $d->createTextNode("{xmltemplateinputblock_$key}");
				$block_el->parentNode->replaceChild($new_block_el, $block_el);
			} else if ($existsEl($block_caption_el)) {
				$xmlDataSettings[$key]['fieldLabel'] = "<strong>{$xmlDataSettings[$key]['fieldLabel']}</strong>";
				$new_block_el = $d->createTextNode("{xmltemplateinputblock_$key}");
				$block_caption_el->parentNode->replaceChild($new_block_el, $block_caption_el);
			}

			if ($existsEl($block_data_el)) {
				$removeEl($block_data_el);
			}
			if ($existsEl($new_block_el)) {
				$removeText($d, "{{$key}}");
			}
		}

		$body = $d->getElementsByTagName('body')->item(0);

		$needRefreshContentWrap = true;
		$html = '';
		$contentNode = null;

		if ($body instanceof DOMElement) {
			$contentNode = $body;
			foreach($body->getElementsByTagName('div') as $node) {
				if ($node->getAttribute('class') == 'sw-editor-page-content') {
					$skip = false;
					$parentNode = $node->parentNode;
					while ($parentNode && $parentNode->nodeType == XML_ELEMENT_NODE) {
						if ($parentNode->getAttribute('class') == 'sw-editor-page-content') {
							$skip = true;
							break;
						}
						$parentNode = $parentNode->parentNode;
					}
					if (!$skip) {
						if (!is_array($contentNode)) {
							$contentNode = [];
						}
						$contentNode[] = $node;
						$needRefreshContentWrap = false;
					}
				}
			}

			foreach($body->childNodes as $item) {
				$html .= trim($d->saveHTML($item));
			}
			
			if ($xmlType == 3) foreach($body->getElementsByTagName('div') as $node) {
				if ($node->getAttribute('class') == 'sw-editor-page-header' && (
					strpos($node->nodeValue, '@#@ШапкаОсмотра') === false
				)) {
					$needRefreshContentWrap = true;
					break;
				}
				if ($node->getAttribute('class') == 'sw-editor-page-footer' && (
					strpos($node->nodeValue, '@#@СписокЛВН') === false || 
					strpos($node->nodeValue, '@#@Назначения') === false
				)) {
					$needRefreshContentWrap = true;
					break;
				}
			}
		}

		$html = str_replace('\\\\', '\\', $html);

		if ($needRefreshContentWrap) {
			$content = $html;
			
			if (!empty($contentNode)) {
				$content = '';
				$contentNode = is_array($contentNode)?$contentNode:[$contentNode];
				foreach($contentNode as $contentNodeItem) {
					foreach ($contentNodeItem->childNodes as $item) {
						$content .= trim($d->saveHTML($item));
					}
				}
			}
		
			$arr = array(
				'<div class="sw-editor-page-content">',
					!empty($content)?$content:'<p><br data-mce-bogus="1"></p>',
				'</div>',
			);

			if ($xmlType == 3) {
				$arr = array(
					'<div class="sw-editor-page-header" contenteditable="false">',
						'@#@ШапкаОсмотра',
					'</div>',
					'<div class="sw-editor-page-content">',
						!empty($content)?$content:'<p><br data-mce-bogus="1"></p>',
					'</div>',
					'<div class="sw-editor-page-footer" contenteditable="false">',
						'@#@СписокЛВН',
						'@#@Назначения',
					'</div>',
				);
			}

			$html = implode("", $arr);
		}

		$sysNicks = array_keys($xmlData);

		//Конвертация xmlData для параметров
		if (is_array($ParameterValues)) foreach($ParameterValues as $item) {
			$sysNick = $item['ParameterValue_SysNick'];
			$marker = $item['ParameterValue_Marker'];

			if (in_array($sysNick, $sysNicks)) {
				$number = 1;

				if (preg_match_all("/{$sysNick}_(\d)+/", implode(",", $sysNicks), $matches)) {
					$number = max($matches[1]) + 1;
				}

				$xmlData[$sysNick.'_'.$number] = $xmlData[$sysNick];
				unset($xmlData[$sysNick]);

				if (preg_match('/(@#@_\d+)([А-яЁё][А-яЁё0-9]+)/u', $marker, $match1) && preg_match("/{$marker}/u", $html, $match2)) {
					$marker1 = "{$match1[1]}_{$number}{$match1[2]}";
					$html = preg_replace("/{$marker}/u", $marker1, $html);
				}
			}
		}

		return array(
			'template' => $html,
			'xmlData' => $xmlData,
			'xmlDataSettings' => $xmlDataSettings
		);
	}

	/**
	 * Получение списка областей ввода данных для шаблона
	 * @param array $data
	 * @return array
	 */
	function loadXmlDataSectionList($data) {
		$_sysNicks = array();
		$filters = array('1=1');
		$params = array();

		if (!empty($data['EvnXml_DataSettings'])) {
			$_sysNicks = array_keys($data['EvnXml_DataSettings']);
		}
		if (!empty($data['sysNicks'])) {
			$_sysNicks = $data['sysNicks'];
		}

		if (!empty($_sysNicks) && is_array($_sysNicks) && count($_sysNicks) > 0) {
			$sysNicks = "'".implode("','", $_sysNicks)."'";
			$autoname = (strpos($sysNicks, 'autoname') !== false)?"or XDS.XmlDataSection_SysNick = 'autoname'":"";
			$filters[] = "(XDS.XmlDataSection_SysNick in ({$sysNicks}) $autoname)";
		}

		$filters_str = implode("\nand ", $filters);

		$query = "
			select
				XDS.XmlDataSection_id,
				XDS.XmlDataSection_Code,
				case when XDS.XmlDataSection_Code = 0 
					then 'Автоматически именованное поле' 
					else XDS.XmlDataSection_Name
				end as XmlDataSection_Name,
				XDS.XmlDataSection_SysNick
			from v_XmlDataSection XDS with(nolock)
			where {$filters_str}
		";
		return $this->queryResult($query, $params);
	}

	/**
	 * Получение параметров для вставки в шаблон
	 * @param array $data
	 * @return array|false
	 */
	function loadParameterValueList($data) {
		$_sysNicks = array();
		$filters = array('1=1');
		$params = array();

		if (!empty($data['EvnXml_DataSettings'])) {
			$_sysNicks = array_keys($data['EvnXml_DataSettings']);
		}
		if (!empty($data['sysNicks'])) {
			$_sysNicks = array_keys($data['sysNicks']);
		}

		$this->load->model('ParameterValue_model');

		$types = array();
		foreach($this->ParameterValue_model->TypeList as $type) {
			$types[$type['ParameterValueListType_id']] = $type;
		}

		if (!empty($_sysNicks) && is_array($_sysNicks) && count($_sysNicks) > 0) {
			$sysNicks = array();
			foreach($_sysNicks as $item) {
				if (preg_match('/(parameter\d+)_\d+/', $item, $matches)) {
					$sysNicks[] = $matches[1];
				} else {
					$sysNicks [] = $item;
				}
			}
			$sysNicks = "'".implode("','", $sysNicks)."'";
			$filters[] = "isnull(pPV.ParameterValue_SysNick, PV.ParameterValue_SysNick) in ({$sysNicks})";
		}

		if (!empty($data['ParameterValue_Name'])) {
			$filters[] = "PV.ParameterValue_Name like :ParameterValue_Name+'%'";
			$params['ParameterValue_Name'] = $data['ParameterValue_Name'];
		}

		$filters[] = "PV.ParameterValue_pid is null";
		$filters[] = "nullif(rtrim(PV.ParameterValue_Alias),'') is not null";

		$filters_str = implode("\nand ", $filters);

		$query = "
			select
				(
					cast(PV.ParameterValue_id as varchar)+'_'+
					cast(PV.ParameterValueListType_id as varchar)
				) as id,
				PV.ParameterValue_id,
				PV.ParameterValue_pid,
				PV.ParameterValue_Name,
				PV.ParameterValue_Alias,
				PV.ParameterValue_SysNick,
				Author.pmUser_id as Author_id,
				(
					rtrim(Author.PMUser_SurName)+
					isnull(' '+left(Author.PMUser_FirName, 1)+'.', '')+
					isnull(' '+left(Author.PMUser_SecName, 1)+'.', '')
				) as Author_Fin,
				PV.ParameterValueListType_id
			from 
				v_ParameterValue PV with(nolock)
				left join v_pmUserCache Author with(nolock) on Author.pmUser_id = PV.pmUser_insID
				left join v_ParameterValue pPV with(nolock) on pPV.ParameterValue_id = PV.ParameterValue_pid
			where
				{$filters_str}
		";

		$response = $this->queryResult($query, $params);
		if (!is_array($response) || count($response) == 0) {
			return $response;
		}

		$ids = array_map(function($item) {
			return $item['ParameterValue_id'];
		}, $response);

		$ids_str = implode(",", $ids);

		$query = "
			select
				PV.ParameterValue_id,
				PV.ParameterValue_pid,
				PV.ParameterValue_Name
			from
				v_ParameterValue PV with(nolock)
			where
				PV.ParameterValue_pid in ({$ids_str})
		";
		$resp = $this->queryResult($query);
		if (!is_array($resp)) {
			return false;
		}

		$ParameterValueList = array();
		foreach($resp as $item) {
			$key = $item['ParameterValue_pid'];
			$ParameterValueList[$key][] = $item;
		}

		$xtypeMap = array(
			'swparametervaluecombo' => 1,
			'swparametervaluecheckboxgroup' => 2,
			'swparametervalueradiogroup' => 3,
		);

		$additResponse = array();
		if (!empty($data['EvnXml_DataSettings'] )) {
			foreach($response as $item) {
				$key = $item['ParameterValue_id'];
				foreach($data['EvnXml_DataSettings'] as $sysNick => $settings) {
					if (preg_match('/parameter(\d+)_\d+/', $sysNick, $matches) &&
						$matches[1] == $key &&
						$item['ParameterValueListType_id'] != $xtypeMap[$settings['xtype']]
					) {
						$additResponse[] = array_merge($item, array(
							'id' => $item['ParameterValue_id'].'_'.$xtypeMap[$settings['xtype']],
							'ParameterValueListType_id' => $xtypeMap[$settings['xtype']],
						));
					}
				}
			}
		}

		$response = array_merge($response, $additResponse);

		foreach($response as &$item) {
			$key = $item['ParameterValue_id'];
			$values = isset($ParameterValueList[$key])?$ParameterValueList[$key]:array();
			$item['ParameterValueList'] = json_encode($values);
			$item['ParameterValue_Marker'] = $this->ParameterValue_model->getParameterMarker(
				$item['ParameterValue_id'],
				$item['ParameterValueListType_id'],
				$item['ParameterValue_Alias']
			);
			$item = array_merge($item, $types[$item['ParameterValueListType_id']]);
		}

		return $response;
	}

	/**
	 * Получение данных параметра для редактирования
	 * @param array $data
	 * @return array|bool
	 */
	function loadParameterValueForm($data) {
		$params = array(
			'ParameterValue_id' => $data['ParameterValue_id'],
		);

		$query = "
			select top 1
				PV.ParameterValue_id,
				PV.ParameterValue_Name,
				PV.ParameterValue_Alias,
				PV.ParameterValueListType_id,
				PV.XmlTemplateScope_id
			from
				v_ParameterValue PV with(nolock)
			where
				PV.ParameterValue_id = :ParameterValue_id
		";
		$parameter = $this->getFirstRowFromQuery($query, $params);
		if (!is_array($parameter)) {
			return false;
		}

		$query = "
			select
				PV.ParameterValue_id,
				PV.ParameterValue_pid,
				PV.ParameterValue_Name,
				1 as RecordStatus_Code
			from 
				v_ParameterValue PV with(nolock)
			where
				PV.ParameterValue_pid = :ParameterValue_id
		";
		$values = $this->queryResult($query, $params);
		if (!is_array($values)) {
			return false;
		}

		$parameter['ParameterValueList'] = json_encode($values);

		return array($parameter);
	}

	/**
	 * Сохрание данных параметра
	 * @param array $data
	 * @return mixed
	 */
	function saveParameterValue($data) {
		$this->load->model('ParameterValue_model');
		$this->ParameterValue_model->setScenario(swModel::SCENARIO_DO_SAVE);

		$values_change = json_decode($data['ParameterValueList'], true);
		$statusMap = array('inserted', 'saved', 'changed', 'deleted');

		$values_change = array_map(function($item) use($statusMap) {
			$item['ParameterValue_status'] = $statusMap[$item['RecordStatus_Code']];
			return $item;
		}, $values_change);

		$params = array_merge($data, array(
			'XmlTemplateScope_eid' => $data['XmlTemplateScope_id'],
			'values_change' => json_encode($values_change)
		));

		return $this->ParameterValue_model->doSave($params);
	}

	/**
	 * Удаление параметра
	 * @param array $data
	 * @return mixed
	 */
	function deleteParameterValue($data) {
		$this->load->model('ParameterValue_model');
		return $this->ParameterValue_model->doDelete($data);
	}

	/**
	 * Получение списка спецмаркеров
	 * @param array $data
	 * @return array|bool
	 */
	function loadSpecMarkerList($data) {
		$filters = array('1=1');
		$params = array();
		$limit = 100;

		$filters[] = "EvnClass0.EvnClass_id = :EvnClass_id";
		$params['EvnClass_id'] = $data['EvnClass_id'];

		if (!empty($data['names']) && is_array($data['names']) && count($data['names']) > 0) {
			$names = "'".implode("','", $data['names'])."'";
			$filters[] = "FDM.FreeDocMarker_Name in ({$names})";
			$limit = 1000;
		}

		if (!empty($data['mode'])) {
			switch($data['mode']) {
				case 'favorite':
					$filters[] = "isFavorite.value = 2";
					break;
				case 'last':

					break;
			}
		}
		if (!empty($data['query'])) {
			$filters[] = "FDM.FreeDocMarker_Name like :query+'%'";
			$params['query'] = $data['query'];
		}

		$filters_str = implode("\nand ", $filters);

		$query = "
			select
				-- select
				FDM.FreeDocMarker_id as id,
				FDM.FreeDocMarker_Name as name,
				FDM.FreeDocMarker_Description as description,
				FDM.FreeDocMarker_TableAlias as alias,
				FDM.FreeDocMarker_Field as field,
				FDM.FreeDocMarker_Query as query,
				FDM.FreeDocMarker_IsTableValue as isTable,
				FDM.FreeDocMarker_Options as options,
				IsFavorite.value as isFavorite
				-- end select
			from
				-- from
				EvnClass EvnClass0 with (nolock)
				left join EvnClass EvnClass1 with (NOLOCK) on EvnClass1.EvnClass_id = EvnClass0.EvnClass_pid
				left join EvnClass EvnClass2 with (NOLOCK) on EvnClass2.EvnClass_id = EvnClass1.EvnClass_pid
				left join EvnClass EvnClass3 with (NOLOCK) on EvnClass3.EvnClass_id = EvnClass2.EvnClass_pid
				left join EvnClass EvnClass4 with (NOLOCK) on EvnClass4.EvnClass_id = EvnClass3.EvnClass_pid
				left join EvnClass EvnClass5 with (NOLOCK) on EvnClass5.EvnClass_id = EvnClass4.EvnClass_pid
				left join EvnClass EvnClass6 with (NOLOCK) on EvnClass6.EvnClass_id = EvnClass5.EvnClass_pid
				inner join FreeDocMarker FDM with (nolock) on FDM.EvnClass_id in (
					EvnClass0.EvnClass_id,
					EvnClass1.EvnClass_id,
					EvnClass2.EvnClass_id,
					EvnClass3.EvnClass_id,
					EvnClass4.EvnClass_id,
					EvnClass5.EvnClass_id,
					EvnClass6.EvnClass_id
				)
				outer apply (
					select top 1
						1 as value
				) IsFavorite
				-- end from
			where
				-- where
				{$filters_str}
				-- end where
			order by
				-- order by
				FDM.FreeDocMarker_Name
				-- end order by
		";

		return $this->queryResult(getLimitSQLPH($query, 0, $limit, 'distinct'), $params);
	}
	
	/**
	 * Получить список анкет в документе осмотра
	 */
	function loadAnketMarkerList($data) {
		$filters = array('1=1');
		$params = array();
		$limit = 100;

		if (!empty($data['ids']) && is_array($data['ids']) && count($data['ids']) > 0) {
			$ids = "'".implode("','", $data['ids'])."'";
			$filters[] = "MFP.MedicalFormPerson_id in ({$ids})";
			$limit = 1000;
		} else return null;


		$filters_str = implode("\nand ", $filters);

		$query = "
			select
				-- select
				MFP.MedicalFormPerson_id,
				MFP.MedicalForm_id,
				MFP.Person_id,
				MFP.MedicalFormPerson_setDT,
				MF.MedicalForm_Name,
				MF.MedicalForm_Description,
				MF.PersonAgeGroup_id,
				MF.MedicalFormType_id,
				MF.Sex_id,
				MF.Region_id
				-- end select
			from
				-- from
				v_MedicalFormPerson MFP with(nolock)
				inner join v_MedicalForm MF with(nolock) on MF.MedicalForm_id = MFP.MedicalForm_id
				-- end from
			where
				-- where
				{$filters_str}
				-- end where
		";
		return $this->queryResult($query, $params);
	}

	/**
	 * Получение контента для спецмаркеров
	 * @param array $data
	 * @return array
	 */
	function getSpecMarkerContent($data) {
		$this->load->library('swMarker');

		$ids = implode(",", $data['SpecMarkerIds']);
		$query = "
			select 	
				FreeDocMarker_id as id,
				FreeDocMarker_Name as name
			from FreeDocMarker with(nolock)
			where FreeDocMarker_id in ({$ids})
			
		";
		$markers = $this->queryResult($query);
		if (!is_array($markers)) {
			return $this->createError('','Ошибка при получении маркеров');
		}

		$responseData = array();
		foreach($markers as $marker) {
			$content = swMarker::processingTextWithMarkers('@#@'.$marker['name'], $data['Evn_id'], array(
				'isPrint' => false,
				'From_Evn_id' => $data['Evn_id'],
			));
			$responseData[] = array(
				'id' => $marker['id'],
				'content' => $content,
			);
		}

		return array(array(
			'success' => true,
			'data' => $responseData
		));
	}
	
	/**
	 * Получение содержимого анкет
	 */
	function getAnketMarkerContent($ids) {
		$this->load->model('MedicalForm_model');

		$responseData = array();
		foreach($ids as $id) {
			$params = array('MedicalFormPerson_id'=>$id);
			$query = "select MedicalForm_id from v_MedicalFormPerson with(nolock) where MedicalFormPerson_id = :MedicalFormPerson_id";
			$MedicalForm_id = $this->getFirstResultFromQuery($query, $params);
			$response = $this->MedicalForm_model->loadMedicalForm(array('MedicalForm_id'=>$MedicalForm_id));

			$MedicalForm = $this->MedicalForm_model->buildTree($response);

			$MedicalFormData = $this->MedicalForm_model->loadMedicalFormData(array('MedicalFormPerson_id'=>$id));

			$responseData[] = array(
				'id' => $id,
				'content' => array(
						'MedicalForm' => $MedicalForm,
						'MedicalFormData' => $MedicalFormData
					),
			);
		}
		return array(array(
			'success' => true,
			'data' => $responseData
		));
	}

	/**
	 * @param string|array $_markerData
	 * @return string
	 */
	function getMarkerKey($_markerData) {
		$markerData = is_string($_markerData)
			?json_decode($_markerData, true):$_markerData;

		$markerKey = array();
		foreach($markerData as $key => $value) {
			$markerKey[] = $key.'-'.$value;
		}

		return 'marker_'.md5(implode('-', $markerKey));
	}

	/**
	 * Получение контента маркера документа
	 * @param array $data
	 * @return array
	 */
	function getMarkerContent($data) {
		$markerData = is_string($data['markerData'])
			?json_decode($data['markerData'], true)
			:$data['markerData'];

		$this->load->model('EvnXmlBase_model');

		$content = '';
		if (!empty($data['Evn_id'])) {
			try {
				$resp = $this->EvnXmlBase_model->buildAndExeQuery($data['Evn_id'], $markerData['XmlMarkerType_Code'], array($markerData));
			} catch(Exception $e) {
				return $this->createError('', $e->getMessage());
			}
			$content = !empty($resp)?$resp[0]:'';
		}

		return array(array(
			'success' => true,
			'content' => $content,
			'key' => $this->getMarkerKey($markerData),
		));
	}

	/**
	 * Получение названия для нового шаблона
	 * @param array $data
	 * @return array
	 */
	function getNewXmlTemplateCaption($data) {
		$defultCaption = 'Новый шаблон ';

		$params = array(
			'pmUser_id' => $data['pmUser_id'],
			'defultCaption' => $defultCaption
		);

		$query = "
			declare @str varchar(100) = :defultCaption
			declare @len int = len(@str) + 1
			select top 1
				isnull(max(cast(SubStr.Value as int)), 0) + 1 as Number
			from 
				v_XmlTemplate XT
				outer apply (
					select substring(
						XT.XmlTemplate_Caption, 
						@len, 
						len(XT.XmlTemplate_Caption)
					) as Value
				) SubStr
			where
				isnull(XT.XmlTemplate_IsDeleted, 1) = 1
				and XT.pmUser_insID = :pmUser_id
				and XT.XmlTemplate_Caption like @str+'%'
				and isnumeric(SubStr.Value+'.e0') = 1
		";

		$number = $this->getFirstResultFromQuery($query, $params);
		if (empty($number)) {
			return $this->createError('','Ошибка при получении номера шаблона');
		}

		return array(array(
			'success' => true,
			'caption' => $defultCaption.$number
		));
	}

	/**
	 * Получение названия для новой папки
	 * @param array $data
	 * @return array
	 */
	function getNewXmlTemplateCatName($data) {
		$defultCaption = 'Новая папка ';

		$params = array(
			'XmlTemplateCat_pid' => !empty($data['XmlTemplateCat_pid'])?$data['XmlTemplateCat_pid']:null,
			'defultCaption' => $defultCaption
		);

		$query = "
			declare @str varchar(100) = :defultCaption
			declare @len int = len(@str) + 1
			select top 1
				isnull(max(cast(SubStr.Value as int)), 0) + 1 as Number
			from 
				v_XmlTemplateCat XTC
				outer apply (
					select substring(
						XTC.XmlTemplateCat_Name, 
						@len, 
						len(XTC.XmlTemplateCat_Name)
					) as Value
				) SubStr
			where
				XTC.XmlTemplateCat_Name like @str+'%'
				and isnumeric(SubStr.Value+'.e0') = 1
				and isnull(XTC.XmlTemplateCat_pid, 0) = isnull(:XmlTemplateCat_pid, 0)
		";

		$number = $this->getFirstResultFromQuery($query, $params);
		if (empty($number)) {
			return $this->createError('','Ошибка при получении номера шаблона');
		}

		return array(array(
			'success' => true,
			'caption' => $defultCaption.$number
		));
	}

	/**
	 * @param $template
	 * @param $xmlData
	 * @param array $xmlDataSettings
	 * @param string|null $useDefaultValues
	 * @return string
	 */
	function createXmlTemplateData($template, $xmlData, $xmlDataSettings = array(), $useDefaultValues = null) {
		$this->load->library('swMarker');
		$this->load->library('swXmlTemplate');
		$this->load->model('ParameterValue_model');

		$defaultXmlData = null;
		$xmlData = is_array($xmlData)?$xmlData:json_decode($xmlData, true);
		$xmlDataSettings = is_array($xmlDataSettings)?$xmlDataSettings:json_decode($xmlDataSettings, true);
		$markers = swMarker::foundParameterMarkers($template);

		if (empty($xmlDataSettings)) {
			$xmlDataSettings = array();
		}

		foreach($xmlData as $key => $value) {
			$xmlData[$key] = htmlspecialchars(stripslashes($value), ENT_COMPAT, 'UTF-8');
		}
		foreach($xmlDataSettings as $key => &$settings) {
			if (!empty($settings['fieldLabel'])) {
				$settings['fieldLabel'] = htmlspecialchars(stripslashes($settings['fieldLabel']), ENT_COMPAT, 'UTF-8');
			}
		}
		if (!empty($useDefaultValues)) {
			$defaultXmlData = swXmlTemplate::getXmlTemplateValues($useDefaultValues);
		}

		$fieldData = array_merge(
			$this->getXmlTemplateFieldData($xmlData),
			$this->ParameterValue_model->getXmlTemplateFieldData($markers, $xmlData)
		);

		foreach($fieldData as &$field) {
			$key = $field['id'];
			if (isset($xmlDataSettings[$key]) && !empty($xmlDataSettings[$key]['fieldLabel'])) {
				$field['fieldLabel'] = $xmlDataSettings[$key]['fieldLabel'];
			}
			if (isset($defaultXmlData[$key])) {
				$field['defaultValue'] = $defaultXmlData[$key];
			}
		}

		return swXmlTemplate::createXmlTemplateData($fieldData);
	}

	/**
	 * Сохранение шаблона
	 * @param array $data
	 * @return array
	 */
	function saveXmlTemplate($data) {
		try {
			$this->beginTransaction();

			$params = array(
				'XmlTemplate_id' => !empty($data['XmlTemplate_id'])?$data['XmlTemplate_id']:null,
				'XmlTemplate_Caption' => $data['XmlTemplate_Caption'],
				'XmlTemplate_Descr' => !empty($data['XmlTemplate_Descr'])?$data['XmlTemplate_Descr']:null,
				'XmlTemplateCat_id' => !empty($data['XmlTemplateCat_id'])?$data['XmlTemplateCat_id']:null,
				'XmlTemplateType_id' => 6,
				'XmlType_id' => $data['XmlType_id'],
				'EvnClass_id' => $data['EvnClass_id'],
				'Lpu_id' => $data['Lpu_id'],
				'LpuSection_id' => !empty($data['LpuSection_id'])?$data['LpuSection_id']:null,
				'MedPersonal_id' => !empty($data['MedPersonal_id'])?$data['MedPersonal_id']:null,
				'MedStaffFact_id' => !empty($data['MedStaffFact_id'])?$data['MedStaffFact_id']:null,
				'XmlTemplateScope_id' => $data['XmlTemplateScope_id'],
				'XmlTemplateScope_eid' => $data['XmlTemplateScope_id'],
				'XmlTemplateSettings_id' => !empty($data['XmlTemplateSettings_id'])?$data['XmlTemplateSettings_id']:null,
				'XmlTemplateHtml_id' => null,
				'XmlTemplateData_id' => null,
				'XmlTemplateCatType_id' => 2,
				'pmUser_id' => $data['pmUser_id'],
			);

			if ($data['mode'] == 'template' || !empty($data['XmlTemplate_HtmlTemplate'])) {
				$size = strlen($data['XmlTemplate_HtmlTemplate']) / 1024 / 1024;
				if ($size > 5) {
					throw new Exception('Размер шаблона не должен превышать 5 МБ');
				}

				if (!empty($params['XmlTemplate_id'])) {
					$query = "
						select top 1
						XmlTemplateHtml_id
						from v_XmlTemplate with(nolock)
						where XmlTemplate_id = :XmlTemplate_id
					";
					$resp = $this->getFirstRowFromQuery($query, $params);
					if (!is_array($resp)) {
						return $this->createError('','Ошибка при получении данных шаблона');
					}
					$params = array_merge($params, $resp);
				}

				//Проверка верстки
				$resp = $this->loadHtmlDom($data['XmlTemplate_HtmlTemplate']);
				$errors = array_merge($resp['alerts'], $resp['errors']);
				if (count($errors) > 0) {
					throw new Exception("<br/>", $errors);
				}

				$resp = $this->saveXmlTemplateHtml(array(
					'XmlTemplateHtml_id' => $params['XmlTemplateHtml_id'],
					'XmlTemplateHtml_HtmlTemplate' => $data['XmlTemplate_HtmlTemplate'],
					'pmUser_id' => $data['pmUser_id'],
				));
				if (!$this->isSuccessful($resp)) {
					throw new Exception($resp[0]['Error_Msg']);
				}
				$params['XmlTemplateHtml_id'] = $resp[0]['XmlTemplateHtml_id'];
			}

			if ($data['mode'] == 'template' || (!empty($data['XmlTemplate_HtmlTemplate']) && !empty($data['EvnXml_Data']))) {
				$XmlTemplateData_Data = $this->createXmlTemplateData(
					$data['XmlTemplate_HtmlTemplate'],
					$data['EvnXml_Data'],
					$data['EvnXml_DataSettings']
				);
				$params['XmlTemplateData_id'] = $this->_searchInHashTable('XmlTemplateData', $XmlTemplateData_Data);
				if (empty($params['XmlTemplateData_id'])) {
					$params['XmlTemplateData_id'] = $this->_insertToHashTable('XmlTemplateData', 'Data', $XmlTemplateData_Data);
				}
			}

			if ($data['mode'] == 'properties' && !empty($params['XmlTemplate_id'])) {
				$query = "
					select top 1
					XmlTemplateHtml_id,
					XmlTemplateData_id,
					XmlTemplateSettings_id
					from v_XmlTemplate with(nolock)
					where XmlTemplate_id = :XmlTemplate_id
				";

				$resp = $this->getFirstRowFromQuery($query, $params);
				if (empty($resp)) {
					throw new Exception('Ошибка при получени данных шаблона');
				}

				foreach($resp as $key => $value) {
					if (empty($params[$key])) {
						$params[$key] = $value;
					}
				}
			}

			if (empty($params['XmlTemplate_id'])) {
				$procedure = 'p_XmlTemplate_ins';
			} else {
				$procedure = 'p_XmlTemplate_upd';
			}

			$query = "
				declare
					@Res bigint,
					@ErrCode int,
					@ErrMessage varchar(4000);
				set @Res = :XmlTemplate_id;
				exec {$procedure}
					@XmlTemplate_id = @Res output,
					@XmlTemplate_Caption = :XmlTemplate_Caption,
					@XmlTemplate_Descr = :XmlTemplate_Descr,
					@XmlTemplateCat_id = :XmlTemplateCat_id,
					@XmlTemplateType_id = :XmlTemplateType_id,
					@XmlType_id = :XmlType_id,
					@EvnClass_id = :EvnClass_id,
					@Lpu_id = :Lpu_id,
					@LpuSection_id = :LpuSection_id,
					@MedPersonal_id = :MedPersonal_id,
					@MedStaffFact_id = :MedStaffFact_id,
					@XmlTemplateScope_id = :XmlTemplateScope_id,
					@XmlTemplateScope_eid = :XmlTemplateScope_eid,
					@XmlTemplateSettings_id = :XmlTemplateSettings_id,
					@XmlTemplateHtml_id = :XmlTemplateHtml_id,
					@XmlTemplateData_id = :XmlTemplateData_id,
					@XmlTemplateCatType_id = :XmlTemplateCatType_id,
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMessage output;
				select @Res as XmlTemplate_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			";

			$response = $this->queryResult($query, $params);

			$this->commitTransaction();
		} catch(Exception $e) {
			return $this->createError('', $e->getMessage());
		}

		return $response;
	}

	/**
	 * @param array $data
	 * @return array
	 */
	function saveXmlTemplateCat($data) {
		$params = array(
			'XmlTemplateCat_id' => !empty($data['XmlTemplateCat_id'])?$data['XmlTemplateCat_id']:null,
			'XmlTemplateCat_pid' => !empty($data['XmlTemplateCat_pid'])?$data['XmlTemplateCat_pid']:null,
			'XmlTemplateCat_Name' => $data['XmlTemplateCat_Name'],
			'Lpu_id' => $data['Lpu_id'],
			'LpuSection_id' => !empty($data['LpuSection_id'])?$data['LpuSection_id']:null,
			'Server_id' => $data['Server_id'],
			'pmUser_id' => $data['pmUser_id'],
		);

		$query = "
			select count(*) as cnt
			from v_XmlTemplateCat with(nolock)
			where XmlTemplateCat_pid = :XmlTemplateCat_pid
			and XmlTemplateCat_id <> isnull(:XmlTemplateCat_id,0)
			and XmlTemplateCat_Name like :XmlTemplateCat_Name
		";
		$count = $this->getFirstResultFromQuery($query, $params);
		if ($count === false) {
			return $this->createError('Ошибка при проверке существования папки');
		}
		if ($count > 0) {
			return $this->createError('Папка с таким названием уже существует');
		}

		if (empty($params['XmlTemplateCat_id'])) {
			$procedure = 'p_XmlTemplateCat_ins';
		} else {
			$procedure = 'p_XmlTemplateCat_upd';
		}

		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @Res = :XmlTemplateCat_id;
			exec {$procedure}
				@XmlTemplateCat_id = @Res output,
				@XmlTemplateCat_pid = :XmlTemplateCat_pid,
				@XmlTemplateCat_Code = 1,
				@XmlTemplateCat_Name = :XmlTemplateCat_Name,
				@XmlTemplateScope_id = 1,
				@XmlTemplateScope_eid = 1,
				@Lpu_id = :Lpu_id,
				@LpuSection_id = :LpuSection_id,
				@Server_id = :Server_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @Res as XmlTemplateCat_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		//echo getDebugSQL($query, $params);exit;
		$response = $this->queryResult($query, $params);
		if (!is_array($response)) {
			return $this->createError('Ошибка при сохранении папки');
		}
		if (!$this->isSuccessful($response)) {
			return $response;
		}

		return array(array(
			'success' => true,
			'XmlTemplateCat_id' => $response[0]['XmlTemplateCat_id']
		));
	}

	/**
	 * @param array $data
	 * @return mixed
	 */
	function deleteXmlTemplate($data, $allowTransaction = true) {
		$this->load->model('XmlTemplateBase_model');
		return $this->doDelete($data, $allowTransaction);
	}

	/**
	 * @param $data
	 */
	function deleteXmlTemplateCat($data, $allowTransaction = true) {
		$this->load->model('XmlTemplateCat_model');
		return $this->XmlTemplateCat_model->doDelete($data, $allowTransaction);
	}

	/**
	 * @param array $data
	 * @return array
	 */
	function deleteXmlTemplateCatWithChildren($data) {
		$response = array(
			'success' => true,
		);
		$params = array(
			'XmlTemplateCat_id' => $data['XmlTemplateCat_id'],
		);

		$query = "
			with tree as (
				select
					XTC1.XmlTemplateCat_id, 
					XTC1.XmlTemplateCat_pid,
					1 as level
				from v_XmlTemplateCat XTC1 with(nolock)
				where XTC1.XmlTemplateCat_id = :XmlTemplateCat_id
				union all
				select 
					XTC2.XmlTemplateCat_id, 
					XTC2.XmlTemplateCat_pid,
					level+1 as level
				from v_XmlTemplateCat XTC2 with(nolock)
				inner join tree on tree.XmlTemplateCat_id = XTC2.XmlTemplateCat_pid
			)
			select
				1 as type,
				'XmlTemplateCat' as object,
				XTC.XmlTemplateCat_id as id,
				tree.level,
				XTCD.list as except_list
			from 
				v_XmlTemplateCat XTC with(nolock)
				inner join tree on tree.XmlTemplateCat_id = XTC.XmlTemplateCat_id
				outer apply (
					select top 1 stuff((
						select ','+cast(XTCD.XmlTemplateCatDefault_id as varchar) as [text()]
						from v_XmlTemplateCatDefault XTCD with(nolock)
						where XTCD.XmlTemplateCat_id = XTC.XmlTemplateCat_id
						for XML PATH ('')
					), 1, 1, '') as list
				) XTCD
			union all
			select
				2 as type,
				'XmlTemplate' as object,
				XT.XmlTemplate_id as id,
				tree.level,
				null as except_list
			from
				v_XmlTemplate XT with(nolock)
				inner join tree on tree.XmlTemplateCat_id = XT.XmlTemplateCat_id
			order by
				type desc, 
				level desc
		";

		try {
			$this->beginTransaction();

			$items = $this->queryResult($query, $params);
			if (!is_array($items)) {
				throw new Exception('Ошибка при получении дочерних объектов');
			}

			foreach($items as $item) {
				$params = array_merge($data, array(
					$item['object'].'_id' => $item['id'],
					'except_list' => $item['except_list'],
				));
				if ($item['object'] == 'XmlTemplate') {
					$resp = $this->deleteXmlTemplate($params, false);
				}
				if ($item['object'] == 'XmlTemplateCat') {
					$resp = $this->deleteXmlTemplateCat($params, false);
				}
				if (!empty($resp['Error_Msg'])) {
					throw new Exception($resp['Error_Msg']);
				}
			}

			$this->commitTransaction();
		} catch(Exception $e) {
			return $this->createError('', $e->getMessage());
		}

		return array($response);
	}

	/**
	 * @param array $data
	 * @return array
	 */
	function moveXmlTemplate($data) {
		$params = array(
			'XmlTemplate_id' => $data['XmlTemplate_id'],
			'XmlTemplateCat_id' => !empty($data['XmlTemplateCat_id'])?$data['XmlTemplateCat_id']:null,
			'pmUser_id' => $data['pmUser_id'],
		);

		$query = "
			declare @Error_Code bigint = null;
			declare @Error_Message varchar(4000) = '';
			declare @dt datetime = (select top 1 dbo.tzGetDate());
			set nocount on;
			begin try
				update XmlTemplate with(rowlock)
				set
					XmlTemplateCat_id = :XmlTemplateCat_id,
					XmlTemplate_updDT = @dt,
					pmUser_updID = :pmUser_id
				where XmlTemplate_id = :XmlTemplate_id
			end try
			begin catch
				set @Error_Code = error_number()
				set @Error_Message = error_message()
			end catch
			set nocount off;
			select @Error_Code as Error_Code, @Error_Message as Error_Msg;		
		";

		$response = $this->queryResult($query, $params);
		if (!is_array($response)) {
			return $this->createError('','Ошибка при перемещении шаблона');
		}

		return $response;
	}

	/**
	 * @param array $data
	 * @return array
	 */
	function copyXmlTemplate($data) {
		$params = array(
			'XmlTemplate_id' => $data['XmlTemplate_id'],
			'XmlTemplateCat_id' => $data['XmlTemplateCat_id'],
			'pmUser_id' => $data['pmUser_id'],
		);

		$resp = $this->copyObject('XmlTemplate', $params);

		return array($resp);
	}

	/**
	 * @param array $data
	 * @return array
	 */
	function renameXmlTemplateItem($data) {
		$object = null;

		if (!empty($data['XmlTemplate_id'])) {
			$object = 'XmlTemplate';
			$params = array(
				'XmlTemplate_id' => $data['XmlTemplate_id'],
				'XmlTemplate_Caption' => $data['name'],
				'pmUser_id' => $data['pmUser_id'],
			);
		} else if (!empty($data['XmlTemplateCat_id'])) {
			$object = 'XmlTemplateCat';
			$params = array(
				'XmlTemplateCat_id' => $data['XmlTemplateCat_id'],
				'XmlTemplateCat_Name' => $data['name'],
				'pmUser_id' => $data['pmUser_id'],
			);
		}

		if (empty($object)) {
			return $this->createError('','Не определен объект для переименования');
		}

		return $this->swUpdate($object, $params);
	}

	/**
	 * @param array $data
	 * @return array|false
	 */
	function loadXmlTemplateProperties($data) {
		$params = array(
			'XmlTemplate_id' => $data['XmlTemplate_id']
		);

		$query = "
			select top 1
				XT.XmlTemplate_id,
				XT.XmlTemplateCat_id,
				XT.MedStaffFact_id,
				XT.MedPersonal_id,
				XT.LpuSection_id,
				XT.XmlTemplate_Caption,
				XT.XmlTemplate_Descr,
				XT.XmlTemplate_Descr,
				XT.EvnClass_id,
				XT.XmlType_id,
				XT.XmlTemplateScope_id,
				XT.pmUser_insID as Author_id,
				'properties' as mode
			from
				v_XmlTemplate XT with(nolock)
			where
				XT.XmlTemplate_id = :XmlTemplate_id
		";

		$resp = $this->queryResult($query, $params);
		if (!is_array($resp)) {
			return false;
		}
		$resp[0]['success'] = true;

		return $resp;
	}

	/**
	 * @param array $data
	 * @return array
	 */
	function getXmlTemplateCatPath($data) {
		if (empty($data['XmlTemplate_id']) && empty($data['XmlTemplateCat_id'])) {
			return $this->createError('','Не передан идентификатор шаблона или папки');
		}

		$params = array(
			'XmlTemplateCat_id' => !empty($data['XmlTemplateCat_id'])?$data['XmlTemplateCat_id']:null,
		);

		if (!empty($data['XmlTemplate_id'])) {
			$query = "
				select top 1 XT.XmlTemplateCat_id
				from v_XmlTemplate XT with(nolock)
				where XT.XmlTemplate_id = :XmlTemplate_id
			";
			$params['XmlTemplateCat_id'] = $this->getFirstResultFromQuery($query, $data, true);
			if ($params['XmlTemplateCat_id'] === false) {
				return $this->createError('','Ошибка при получении данных шаблона');
			}
		}

		$query = "
			with tree as (
				select 
					XTC1.XmlTemplateCat_id, 
					XTC1.XmlTemplateCat_pid,
					1 as level
				from v_XmlTemplateCat XTC1 with(nolock)
				where XTC1.XmlTemplateCat_id = :XmlTemplateCat_id
				union all
				select 
					XTC2.XmlTemplateCat_id, 
					XTC2.XmlTemplateCat_pid,
					level+1 as level
				from v_XmlTemplateCat XTC2 with(nolock)
				inner join tree on tree.XmlTemplateCat_pid = XTC2.XmlTemplateCat_id
			)
			select
				tree.XmlTemplateCat_id,
				tree.XmlTemplateCat_pid
			from tree
			order by level desc
		";

		$resp = $this->queryResult($query, $params);
		if (!is_array($resp)) {
			return $this->createError('','Ошибка при получении пути до папки');
		}

		$node = !empty($data['node'])?$data['node']:null;

		$path = '/root';
		$path .= !empty($node)?"/{$node}":'';
		foreach($resp as $item) {
			if ($node == 'common' && !empty($data['LpuSection_id'])) {
				$path .= "/lpuSection-{$data['LpuSection_id']}-folder-{$item['XmlTemplateCat_id']}";
			} else {
				$path .= "/folder-{$item['XmlTemplateCat_id']}";
			}
		}
		if (!empty($data['XmlTemplate_id'])) {
			$path .= "/template-{$data['XmlTemplate_id']}";
		}

		return array(array(
			'success' => true,
			'path' => $path,
		));
	}

	/**
	 * @param array $data
	 * @return array
	 */
	function getXmlTemplatePath($data) {
		$params = array(
			'XmlTemplate_id' => $data['XmlTemplate_id'],
			'pmUser_id' => $data['pmUser_id'],
		);

		$query = "
			declare @pmUser_id bigint = :pmUser_id;
			select top 1
				XT.XmlTemplate_id,
				XT.XmlTemplateCat_id,
				XT.LpuSection_id,
				Section.Nick as SectionNick
			from
				v_XmlTemplate XT with(nolock)
				left join v_XmlTemplateBase XTB with(nolock) on XTB.XmlTemplate_id = XT.XmlTemplate_id
				outer apply (
					select top 1 case
						when XTB.XmlTemplateBase_id is not null then 'base'
						when XT.pmUser_insID = @pmUser_id then 'own'
						else 'common'
					end as Nick
				) Section
			where
				XT.XmlTemplate_id = :XmlTemplate_id
		";
		$template = $this->getFirstRowFromQuery($query, $params);
		if (!is_array($template)) {
			return $this->createError('','Ошибка при получении данных шаблона');
		}

		$query = "
			with tree as (
				select
					XTC1.XmlTemplateCat_id,
					XTC1.XmlTemplateCat_pid,
					XTC1.XmlTemplateCat_id as XmlTemplateCat_tid,
					XTC1.XmlTemplateCat_Name,
					XTC1.pmUser_insID as Author_id,
					1 as level
				from v_XmlTemplateCat XTC1 with(nolock)
				where XTC1.XmlTemplateCat_id = :XmlTemplateCat_id
				union all
				select
					XTC2.XmlTemplateCat_id,
					XTC2.XmlTemplateCat_pid,
					tree.XmlTemplateCat_tid,
					XTC2.XmlTemplateCat_Name,
					XTC2.pmUser_insID as Author_id,
					level+1 as level
				from v_XmlTemplateCat XTC2 with(nolock)
				inner join tree on tree.XmlTemplateCat_pid = XTC2.XmlTemplateCat_id
			)
			select
				tree.XmlTemplateCat_id,
				tree.XmlTemplateCat_pid,
				tree.XmlTemplateCat_tid,
				tree.XmlTemplateCat_Name,
				tree.Author_id
			from tree
			order by tree.level desc
		";
		$folders = $this->queryResult($query, $template);
		if (!is_array($template)) {
			return $this->createError('','Ошибка при получении данных шаблона');
		}

		$tid = $template['XmlTemplateCat_id'];
		$path = "/root";

		if (empty($data['mode']) || $data['mode'] == 'all') {
			$path .= '/'.$template['SectionNick'];
		}
		if ($template['SectionNick'] == 'common') {
			$path .= "/lpuSection-{$template['LpuSection_id']}";
		}

		if (!empty($tid) && $template['SectionNick'] != 'base') {
			if ($template['SectionNick'] == 'common') {
				$path .= '/'.implode('/', array_map(function($folder) use($template) {
					return "lpuSection-{$template['LpuSection_id']}-folder-{$folder['XmlTemplateCat_id']}";
				}, $folders));
			} else {
				$path .= '/'.implode('/', array_map(function($folder) {
					return "folder-{$folder['XmlTemplateCat_id']}";
				}, $folders));
			}
		}

		$path .= "/template-{$template['XmlTemplate_id']}";

		return array(array(
			'success' => true,
			'path' => $path,
		));
	}

	/**
	 * @param array $data
	 * @return mixed
	 */
	function setXmlTemplateDefault($data) {
		$data['checkSetDefault'] = empty($data['ignoreCheckSetDefault']);
		$this->load->model('XmlTemplateDefault_model');
		return $this->XmlTemplateDefault_model->save($data);
	}

	/**
	 * @param array $data
	 * @return array
	 */
	function unsetXmlTemplateDefault($data) {
		$params = array(
			'XmlTemplateDefault_id' => $data['XmlTemplateDefault_id'],
		);

		$query = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec p_XmlTemplateDefault_del
				@XmlTemplateDefault_id = :XmlTemplateDefault_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$resp = $this->queryResult($query, $params);
		if (!is_array($resp)) {
			return $this->createError('','Ошибка при снятии шаблона по умолчанию');
		}
		return array(array(
			'success' => 'true'
		));
	}

	/**
	 * @param array $data
	 * @return array
	 */
	function saveXmlTemplateHtml($data) {
		$params = array(
			'XmlTemplateHtml_id' => !empty($data['XmlTemplateHtml_id'])?$data['XmlTemplateHtml_id']:null,
			'XmlTemplateHtml_HtmlTemplate' => !empty($data['XmlTemplateHtml_HtmlTemplate'])?$data['XmlTemplateHtml_HtmlTemplate']:'',
			'pmUser_id' => $data['pmUser_id'],
		);

		if (!empty($params['XmlTemplateHtml_id'])) {
			$query = "
				select top 1 
					XTH.XmlTemplateHtml_HashData as Hash,
					(XT.Count + EX.Count) as Count
				from 
					v_XmlTemplateHtml XTH with(nolock)
					outer apply(
						select top 1 count(*) as Count
						from v_XmlTemplate with(nolock)
						where XmlTemplateHtml_id = XTH.XmlTemplateHtml_id
					) XT
					outer apply(
						select top 1 count(*) as Count
						from v_EvnXml with(nolock)
						where XmlTemplateHtml_id = XTH.XmlTemplateHtml_id
					) EX
				where
					XTH.XmlTemplateHtml_id = :XmlTemplateHtml_id
			";

			$resp = $this->getFirstRowFromQuery($query, $params, true);
			if (!is_array($resp)) {
				return $this->createError('','Ошибка при проверке данных верстки документа');
			}

			if (!empty($resp['Hash']) || $resp['Count'] > 1) {
				$params['XmlTemplateHtml_id'] = null;
			}
		}

		if (empty($params['XmlTemplateHtml_id'])) {
			$procedure = 'p_XmlTemplateHtml_ins';
		} else {
			$procedure = 'p_XmlTemplateHtml_upd';
		}

		$query = "
			declare
				@XmlTemplateHtml_id bigint = :XmlTemplateHtml_id,
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec {$procedure}
				@XmlTemplateHtml_id = @XmlTemplateHtml_id output,
				@XmlTemplateHtml_HtmlTemplate = :XmlTemplateHtml_HtmlTemplate,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @XmlTemplateHtml_id as XmlTemplateHtml_id,  @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$response = $this->queryResult($query, $params);
		if (!is_array($response)) {
			return $this->createError('','Ошибка при сохранении верстки документа');
		}
		if ($this->isSuccessful($response) && !empty($params['XmlTemplateHtml_id'])) {
			$response[0]['XmlTemplateHtml_id'] = $params['XmlTemplateHtml_id'];
		}

		return $response;
	}

	/**
	 * @param array $data
	 * @return array
	 */
	function getXmlTemplateSelectedId($data) {
		$params = array(
			'XmlTemplate_id' => $data['XmlTemplate_id'],
			'MedStaffFact_id' => $data['MedStaffFact_id'],
			'MedPersonal_id' => $data['MedPersonal_id'],
			'pmUser_id' => $data['pmUser_id'],
		);
		$query = "
			select top 1 XmlTemplateSelected_id
			from v_XmlTemplateSelected with(nolock)
			where XmlTemplate_id = :XmlTemplate_id
			and MedStaffFact_id = :MedStaffFact_id
			and MedPersonal_id = :MedPersonal_id
			and pmUser_insID = :pmUser_id
		";
		$XmlTemplateSelected_id = $this->getFirstResultFromQuery($query, $params, true);
		if ($XmlTemplateSelected_id === false) {
			return $this->createError('','Ошибка при получении идентификатора записи избранного шаблона');
		}
		return array(array(
			'success' => true,
			'XmlTemplateSelected_id' => $XmlTemplateSelected_id,
		));
	}

	/**
	 * @param array $data
	 * @return array
	 */
	function toggleXmlTemplateSelected($data) {
		$params = array(
			'XmlTemplateSelected_id' => null,
			'XmlTemplate_id' => $data['XmlTemplate_id'],
			'MedStaffFact_id' => $data['MedStaffFact_id'],
			'MedPersonal_id' => $data['MedPersonal_id'],
			'pmUser_id' => $data['pmUser_id'],
		);

		$resp = $this->getXmlTemplateSelectedId($params);
		if (!$this->isSuccessful($resp)) {
			return $resp;
		}
		$params['XmlTemplateSelected_id'] = $resp[0]['XmlTemplateSelected_id'];

		if ((empty($params['XmlTemplateSelected_id']) && empty($data['operation'])) || $data['operation'] == 'select') {
			$response = $this->setXmlTemplateSelected($params);
			$response[0]['operation'] = 'select';
		} else {
			$response = $this->unsetXmlTemplateSelected($params);
			$response[0]['operation'] = 'unselect';
		}

		return $response;
	}

	/**
	 * @param array $data
	 * @return array
	 */
	function setXmlTemplateSelected($data) {
		$params = array(
			'XmlTemplate_id' => $data['XmlTemplate_id'],
			'MedStaffFact_id' => $data['MedStaffFact_id'],
			'MedPersonal_id' => $data['MedPersonal_id'],
			'pmUser_id' => $data['pmUser_id'],
		);

		if (!array_key_exists('XmlTemplateSelected_id', $data)) {
			$query = "
				select top 1 XmlTemplateSelected_id
				from v_XmlTemplateSelected with(nolock)
				where XmlTemplate_id = :XmlTemplate_id
				and MedStaffFact_id = :MedStaffFact_id
				and MedPersonal_id = :MedPersonal_id
				and pmUser_insID = :pmUser_id
			";
			$XmlTemplateSelected_id = $this->getFirstResultFromQuery($query, $params, true);
			if ($XmlTemplateSelected_id === false) {
				return $this->createError('','Ошибка при проверке статуса шаблона');
			}
		} else {
			$XmlTemplateSelected_id = $data['XmlTemplateSelected_id'];
		}
		if (!empty($XmlTemplateSelected_id)) {
			return array(array(
				'success' => true,
				'XmlTemplateSelected_id' => $XmlTemplateSelected_id,
			));
		}

		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec p_XmlTemplateSelected_ins
				@XmlTemplateSelected_id = @Res output,
				@XmlTemplate_id = :XmlTemplate_id,
				@MedStaffFact_id = :MedStaffFact_id,
				@MedPersonal_id = :MedPersonal_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @Res as XmlTemplateSelected_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		//echo getDebugSQL($query, $params);exit;
		$response = $this->queryResult($query, $params);
		if (!is_array($response)) {
			return $this->createError('','Ошибка пометке шаблона как избранного');
		}

		return $response;
	}

	/**
	 * @param array $data
	 * @return array
	 */
	function unsetXmlTemplateSelected($data) {
		$params = array(
			'XmlTemplate_id' => $data['XmlTemplate_id'],
			'MedStaffFact_id' => $data['MedStaffFact_id'],
			'MedPersonal_id' => $data['MedPersonal_id'],
			'pmUser_id' => $data['pmUser_id'],
		);

		if (!array_key_exists('XmlTemplateSelected_id', $data)) {
			$query = "
				select top 1 XmlTemplateSelected_id
				from v_XmlTemplateSelected with(nolock)
				where XmlTemplate_id = :XmlTemplate_id
				and MedStaffFact_id = :MedStaffFact_id
				and MedPersonal_id = :MedPersonal_id
				and pmUser_insID = :pmUser_id
			";
			$XmlTemplateSelected_id = $this->getFirstResultFromQuery($query, $params, true);
			if ($XmlTemplateSelected_id === false) {
				return $this->createError('','Ошибка при проверке статуса шаблона');
			}
		} else {
			$XmlTemplateSelected_id = $data['XmlTemplateSelected_id'];
		}
		if (empty($XmlTemplateSelected_id)) {
			return array(array('success' => true));
		}

		$params = array(
			'XmlTemplateSelected_id' => $XmlTemplateSelected_id,
		);

		$query = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec p_XmlTemplateSelected_del
				@XmlTemplateSelected_id = :XmlTemplateSelected_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		//echo getDebugSQL($query, $params);exit;
		$response = $this->queryResult($query, $params);
		if (!is_array($response)) {
			return $this->createError('','Ошибка пометке шаблона как избранного');
		}

		return $response;
	}

	/**
	 * @param array $data
	 * @return array|false
	 */
	function loadPMUserForShareList($data) {

		$params = array(
			'XmlTemplate_id' => $data['XmlTemplate_id'],
			'Lpu_id' => $data['Lpu_id'],
			'pmUser_id' => $data['pmUser_id'],
		);
		$filters = array('');

		if (!empty($data['query'])) {
			$filters[] = "(
				U.pmUser_Name like :query+'%'
				or U.pmUser_Login like :query+'%'
			)";
			$params['query'] = $data['query'];
		}

		$filters_str = implode(" \nand ", $filters);

		$query = "
			select top 200
				cast(U.pmUser_id as varchar)+'_'+cast(L.Lpu_id as varchar) as id,
				U.pmUser_id,
				rtrim(U.pmUser_Login) as  pmUser_Login,
				case 
					when MP.MedPersonal_id is not null 
					then rtrim(MP.Person_Fio) else rtrim(U.pmUser_Name)
				end as pmUser_Name,
				L.Lpu_id,
				L.Lpu_Nick
			from
				v_pmUserCache U with(nolock)
				inner join v_pmUserCacheOrg UO with(nolock) on UO.pmUserCache_id = U.pmUser_id
				inner join v_Lpu L with(nolock) on L.Org_id = UO.Org_id
				outer apply (
					select top 1 *
					from v_MedPersonal with(nolock)
					where MedPersonal_id = U.MedPersonal_id
				) MP
			where
				(
					L.Lpu_id <> :Lpu_id 
					or (L.Lpu_id = :Lpu_id and U.pmUser_id <> :pmUser_id)
				) 
				and not exists(
					select * from v_XmlTemplateShared with(nolock)
					where XmlTemplate_id = :XmlTemplate_id
					and pmUser_getID = U.pmUser_id
					and Lpu_gid = L.Lpu_id
				)
				{$filters_str}
		";
		//echo getDebugSQL($query, $params);exit;
		$response = $this->queryResult($query, $params);
		if (!is_array($response)) {
			return false;
		}

		$sort = array();
		$listById = array();

		foreach($response as $item) {
			$item['compareField'] = null;

			if (!empty($params['query'])) {
				$query = mb_strtolower($params['query']);
				$length = mb_strlen($query);
				$name = mb_strtolower(mb_substr($item['pmUser_Name'], 0, $length));
				$login = mb_strtolower(mb_substr($item['pmUser_Login'], 0, $length));

				if ($name == $query) {
					$item['compareField'] = 'pmUser_Name';
				} else if ($login == $query) {
					$item['compareField'] = 'pmUser_Login';
				}
			}

			$sort[$item['id']] = !empty($item['compareField'])
				?mb_strtolower($item[$item['compareField']])
				:$item['pmUser_Name'];

			$listById[$item['id']] = $item;
		}

		asort($sort);
		$response = array();
		foreach($sort as $id => $value) {
			$response[] = $listById[$id];
		}

		return $response;
	}

	/**
	 * @param array $data
	 * @return array
	 */
	function shareXmlTemplate($data) {
		$shareTo = json_decode($data['shareTo'], true);

		$this->beginTransaction();

		foreach ($shareTo as $item) {
			$item['XmlTemplate_id'] = $data['XmlTemplate_id'];
			$item['pmUser_sendID'] = $data['pmUser_id'];
			$item['Lpu_sid'] = $data['Lpu_id'];
			$item['pmUser_id'] = $data['pmUser_id'];

			$resp = $this->createXmlTemplateShared($item);

			if (!$this->isSuccessful($resp) && $resp[0]['Error_Code'] != 101) {
				$this->rollbackTransaction();
				return $resp;
			} else if (!empty($resp[0]['Error_Code']) && $resp[0]['Error_Code'] == 101) {
				return $resp[0];
			}
		}

		$this->commitTransaction();

		return array(array(
			'success' => true
		));
	}

	/**
	 * @param array $data
	 * @return array
	 */
	function createXmlTemplateShared($data) {
		$params = array(
			'XmlTemplateShared_IsReaded' => !empty($data['XmlTemplateShared_IsReaded'])
				?$data['XmlTemplateShared_IsReaded']:1,
			'XmlTemplate_id' => $data['XmlTemplate_id'],
			'pmUser_sendID' => $data['pmUser_sendID'],
			'Lpu_sid' => $data['Lpu_sid'],
			'pmUser_getID' => $data['pmUser_getID'],
			'Lpu_gid' => $data['Lpu_gid'],
			'pmUser_id' => $data['pmUser_id'],
		);


		$count = $this->getFirstResultFromQuery("
			select top 1 count(*) as cnt
			from v_XmlTemplateShared with(nolock)
			where XmlTemplate_id = :XmlTemplate_id
			and pmUser_getID = :pmUser_getID
			and Lpu_gid = :Lpu_gid
		", $params);

		if ($count === false) {
			return $this->createError('','Ошибка при проверке отправляемого шаблона');
		}
		if ($count > 0) {
			return $this->createError(101,'Шаблон уже был отправлен пользователю');
		}

		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec p_XmlTemplateShared_ins
				@XmlTemplateShared_id = @Res output,
				@XmlTemplateShared_IsReaded = :XmlTemplateShared_IsReaded,
				@XmlTemplate_id = :XmlTemplate_id,
				@pmUser_sendID = :pmUser_sendID,
				@Lpu_sid = :Lpu_sid,
				@pmUser_getID = :pmUser_getID,
				@Lpu_gid = :Lpu_gid,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @Res as XmlTemplateShared_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		//echo getDebugSQL($query, $params);exit;
		$resp = $this->queryResult($query, $params);
		if (!is_array($resp)) {
			return $this->createError('','Ошибка при сохранении записи об отправленном шаблоне');
		}

		return $resp;
	}

	/**
	 * @param array $data
	 * @return array
	 */
	function deleteXmlTemplateShared($data) {
		$params = array(
			'XmlTemplateShared_id' => $data['XmlTemplateShared_id'],
		);

		$query = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec p_XmlTemplateShared_del
				@XmlTemplateShared_id = :XmlTemplateShared_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$resp = $this->queryResult($query, $params);
		if (!is_array($resp)) {
			return $this->createError('','Ошибка при удалении ссылки на отправленный шаблон');
		}

		return $resp;
	}

	/**
	 * @param $data
	 * @return array
	 */
	function getXmlTemplateSharedUnreadCount($data) {
		$params = array(
			'Lpu_id' => $data['Lpu_id'],
			'pmUser_id' => $data['pmUser_id'],
		);

		$query = "
			select top 1 count(*) as cnt
			from v_XmlTemplateShared with(nolock)
			where Lpu_gid = :Lpu_id
			and pmUser_getID = :pmUser_id
			and isnull(XmlTemplateShared_IsReaded, 1) = 1
		";

		$count = $this->getFirstResultFromQuery($query, $params);
		if ($count === false) {
			return $this->createError('','Ошибка при получении количества не просмотренных переданных шаблонов');
		}

		return array(array(
			'success' => true,
			'count' => $count,
		));
	}

	/**
	 * @param array $data
	 * @return array|bool
	 */
	function setXmlTemplateSharedIsReaded($data) {
		$params = array(
			'XmlTemplate_id' => $data['XmlTemplate_id'],
			'Lpu_id' => $data['Lpu_id'],
			'pmUser_id' => $data['pmUser_id'],
		);

		$query = "
			select top 1 XmlTemplateShared_id
			from v_XmlTemplateShared with(nolock)
			where XmlTemplate_id = :XmlTemplate_id
			and Lpu_gid = :Lpu_id
			and pmUser_getID = :pmUser_id
		";

		$XmlTemplateShared_id = $this->getFirstResultFromQuery($query, $params);
		if (empty($XmlTemplateShared_id)) {
			return $this->createError('','Ошибка при получении идентификатор записи');
		}

		$params = array(
			'XmlTemplateShared_id' => $XmlTemplateShared_id,
			'XmlTemplateShared_IsReaded' => 2,
			'pmUser_id' => $data['pmUser_id'],
		);

		return $this->swUpdate('XmlTemplateShared', $params);
	}
	
	function mSearchXmlTemplate($data) {
		$params = array(
			'XmlType_id' => $data['XmlType_id'],
			'EvnClass_id' => $data['EvnClass_id'],
			'pmUser_id' => $data['pmUser_id'],
			'LpuSection_id'=>$data['LpuSection_id'],
			'Lpu_id'=>$data['Lpu_id']
		);
		$template = $this->queryResult("
		select
			'template' as type,
			XT.LpuSection_id,
			LS.LpuSection_Name,
			XT.XmlTemplate_id as id,
			XT.XmlTemplate_Caption as name
		from v_XmlTemplate XT
		left join v_LpuSection LS with(nolock) on LS.LpuSection_id = XT.LpuSection_id
		where XT.pmUser_insID <> :pmUser_id
		and EvnClass_id = :EvnClass_id
		and XmlType_id = :XmlType_id
		and isnull(XT.XmlTemplate_IsDeleted, 1) = 1
		and XT.Lpu_id = :Lpu_id
		and XT.XmlTemplateScope_id in (2, 3, 4)
		and (XT.XmlTemplateScope_id <> 4 or XT.LpuSection_id = :LpuSection_id)
		and XT.XmlTemplate_Caption like '%{$data['query']}%'
		and not exists(
		select * from v_XmlTemplateBase XTB with(nolock)
		where XTB.XmlTemplate_id = XT.XmlTemplate_id
		)",$params);
		
		$folder = $this->queryResult("
		select
			'folder' as type,
			XT.LpuSection_id,
			LS.LpuSection_Name,
			XTC.XmlTemplateCat_id as id,
			XTC.XmlTemplateCat_Name as name
		from v_XmlTemplate XT
		left join v_XmlTemplateCat XTC with(nolock) on XTC.XmlTemplateCat_id = XT.XmlTemplateCat_id
		left join v_LpuSection LS with(nolock) on LS.LpuSection_id = isnull(XT.LpuSection_id, XTC.LpuSection_id)
		where XT.pmUser_insID <> :pmUser_id
		and EvnClass_id = :EvnClass_id
		and XmlType_id = :XmlType_id
		and isnull(XT.XmlTemplate_IsDeleted, 1) = 1
		and XT.Lpu_id = :Lpu_id
		and XT.XmlTemplateScope_id in (2, 3, 4)
		and (XT.XmlTemplateScope_id <> 4 or XT.LpuSection_id = :LpuSection_id)
		and XTC.XmlTemplateCat_Name like '%{$data['query']}%'
		and not exists(
		select * from v_XmlTemplateBase XTB with(nolock)
		where XTB.XmlTemplate_id = XT.XmlTemplate_id
		)",$params);
		if(empty($data['search'])) {
			$result = array_merge($template, $folder);
		} else {
			$result = $data['search'] == "folder" ? $folder : $template;
		}
		
		return $result;
	}
}