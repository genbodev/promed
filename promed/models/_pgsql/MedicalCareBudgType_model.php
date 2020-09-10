<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * MedicalCareBudgType_model - модель для работы с типом медпомощи
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Common
 * @access			public
 * @copyright		Copyright (c) 2018 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			27.02.2018
 */

class MedicalCareBudgType_model extends SwPgModel
{
    /**
     * Конструктор
     */
    function __construct() {
        parent::__construct();
    }

    /**
     * @param array $data
     * @return array|bool
     */
    function getMedicalCareBudgTypeList($data) {

        $params = array(
            'MedicalCareBudgTypeLink_DocumentUcType' => $data['MedicalCareBudgTypeLink_DocumentUcType'],
            'Lpu_id' => $data['Lpu_id'],
            'Person_id' => $data['Person_id'],
            'Person_Age' => !empty($data['Person_Age'])?$data['Person_Age']:null,
            'begDate' => $data['begDate'],
            'endDate' => !empty($data['endDate'])?$data['endDate']:null,
            'LpuSection_id' => !empty($data['LpuSection_id'])?$data['LpuSection_id']:null,
            'LpuSectionProfile_id' => !empty($data['LpuSectionProfile_id'])?$data['LpuSectionProfile_id']:null,
            'Diag_id' => !empty($data['Diag_id'])?$data['Diag_id']:null,
            'MedicalCareBudgTypeLink_Dlit' => !empty($data['MedicalCareBudgTypeLink_Dlit'])?$data['MedicalCareBudgTypeLink_Dlit']:null,
            'MedicalCareBudgTypeLink_Code' => !empty($data['MedicalCareBudgTypeLink_Code'])?$data['MedicalCareBudgTypeLink_Code']:null,
        );

        //Получение возраста пациента, если не передан
        if (empty($params['Person_Age'])) {
            $params['Person_Age'] = $this->getFirstResultFromQuery("
				select
				dbo.Age2(PS.Person_Birthday, :begDate) as Person_Age
				from v_PersonState PS
				where PS.Person_id = :Person_id
				limit 1
			", $params);
            if ($params['Person_Age'] === false) {
                return false;
            }
        }

        //Получение списка специализаций МО
        $Mkb10CodeList = $this->queryList("
			select Mkb.Mkb10Code_RecCode as \"Mkb10Code_RecCode\"
			from fed.v_SpecializationMO S
			inner join fed.v_Mkb10Code Mkb on Mkb.Mkb10Code_id = S.Mkb10Code_cid
			where S.Lpu_id = :Lpu_id
			limit 1
		", $params);
        if (!is_array($Mkb10CodeList)) {
            return false;
        }
        $Mkb10CodeList_str = "'".implode("','", array_merge($Mkb10CodeList, array('')))."'";

        $DopList1_str = "";
        $DopList2_str = "";

        if(empty($data['HTMedicalCareClass_id'])){
            //Получение списка дополнительных критериев
            $PalliativeType_Code = $this->getFirstResultFromQuery("select PT.PalliativeType_Code
					from v_LpuSection LS
					inner join v_PalliativeType PT on PT.PalliativeType_id = LS.PalliativeType_id
					where LS.LpuSection_id = :LpuSection_id
					limit 1", ['LpuSection_id' => $params['LpuSection_id']]);
            $Diag_Code = $this->getFirstResultFromQuery("select Diag_Code from v_Diag where Diag_id = :Diag_id limit 1", ['Diag_id' => $params['Diag_id']]);

            $query = "	
				with RECURSIVE lpu_type_tree as (
					select LT1.*
					from v_Lpu L
					inner join v_LpuType LT1 on LT1.LpuType_id = L.LpuType_id
					where L.Lpu_id = :Lpu_id
					union all
					select LT2.*
					from v_LpuType LT2
					inner join lpu_type_tree on lpu_type_tree.LpuType_pid = LT2.LpuType_id
				)
				(select 'VID_OBR1' as \"Dop\"
				where :MedicalCareBudgTypeLink_DocumentUcType in (1)
				and left('{$Diag_Code}', 1) in ('Z','W')
				and left('{$Diag_Code}', 3) <> 'Z50'
				limit 1)
				union
				(select 'VID_OBR2' as \"Dop\"
				where :MedicalCareBudgTypeLink_DocumentUcType in (1)
				and left('{$Diag_Code}', 1) not in ('Z','W')
				limit 1
				)
				union
				(select 'TYPE_MO1' as \"Dop\"
				where :MedicalCareBudgTypeLink_DocumentUcType in (2,3)
				and exists(select * from lpu_type_tree where LpuType_Code = 11)
				and '0102' in ({$Mkb10CodeList_str})
				limit 1)
				union
				(select 'TYPE_MO2' as \"Dop\"
				where :MedicalCareBudgTypeLink_DocumentUcType in (2,3)
				and exists(select * from lpu_type_tree where LpuType_Code = 11)
				and '0610' in ({$Mkb10CodeList_str})
				limit 1)
				union
				(select 'TYPE_MO3' as \"Dop\"
				where :MedicalCareBudgTypeLink_DocumentUcType in (2,3)
				and exists(select * from lpu_type_tree where LpuType_Code = 11)
				and '10' in ({$Mkb10CodeList_str})
				limit 1)
				union
				(select 'TYPE_MO4' as \"Dop\"
				where exists(select * from lpu_type_tree where LpuType_Code = 111)
				limit 1)
				/*union
				select top 1 'PERS1' as Dop
				where :MedicalCareBudgTypeLink_DocumentUcType in (1)
				and @countPersonDispOrp > 0*/
				union
				(select 'DIAG1' as \"Dop\"
				where :MedicalCareBudgTypeLink_DocumentUcType in (1,2,3)
				and left('{$Diag_Code}', 3) between 'A00' and 'D48'
				limit 1)
				union
				(select 'PALLIAT' as \"Dop\"
				where :MedicalCareBudgTypeLink_DocumentUcType in (1,2,3)".
				(!empty($PalliativeType_Code) 
				? "and {$PalliativeType_Code} is not null"
				: "")
				."limit 1)
				union
				(select 'PALLIAT1' as \"Dop\"
				where :MedicalCareBudgTypeLink_DocumentUcType in (1,2,3)".
				(!empty($PalliativeType_Code) 
				? "and {$PalliativeType_Code} <> 4"
				: "")
				."limit 1)
				union
				(select 'PALLIAT2' as \"Dop\"
				where :MedicalCareBudgTypeLink_DocumentUcType in (1,2,3)".
				(!empty($PalliativeType_Code) 
				? "and {$PalliativeType_Code} = 4"
				: "")
				."limit 1)
			";
            $DopList = $this->queryList($query, $params);
            if (!is_array($DopList)) {
                return false;
            }

            $DopList1_str = "and (
					MCTL.MedicalCareBudgTypeLink_Dop is null
					or MCTL.MedicalCareBudgTypeLink_Dop in ('".implode("','", array_merge($DopList, array('')))."')
				)";
            $DopList2_str = "and (
					MCTL.MedicalCareBudgTypeLink_Dop2 is null
					or MCTL.MedicalCareBudgTypeLink_Dop2 in ('".implode("','", array_merge($DopList, array('')))."')
				)";
        }

        $filters = array();

        if(!empty($params['MedicalCareBudgTypeLink_Code'])){
            $filters[] = "(
					MCTL.MedicalCareBudgTypeLink_Code is null
					or MCTL.MedicalCareBudgTypeLink_Code = :MedicalCareBudgTypeLink_Code
				)";
        }

        $filters = count($filters) > 0 ? "and ".implode("\nand ", $filters) : '';

        //Получение списка типов медпомощи по критериям
        $query = "
			select
				MCTL.MedicalCareBudgType_id as \"MedicalCareBudgType_id\",
				MCTL.LpuSectionProfile_id as \"LpuSectionProfile_id\",
				MCTL.Diag_id as \"Diag_id\",
				MCTL.MedicalCareBudgTypeLink_DlitTo as \"MedicalCareBudgTypeLink_DlitTo\",
				MCTL.MedicalCareBudgTypeLink_DlitFrom as \"MedicalCareBudgTypeLink_DlitFrom\",
				MCTL.MesAgeGroup_id as \"MesAgeGroup_id\",
				MCTL.MedicalCareBudgTypeLink_Dop as \"MedicalCareBudgTypeLink_Dop\",
				MCTL.MedicalCareBudgTypeLink_Dop2 as \"MedicalCareBudgTypeLink_Dop2\"
			from
				v_MedicalCareBudgTypeLink MCTL
				inner join v_MedicalCareBudgType MCBT on MCBT.MedicalCareBudgType_id = MCTL.MedicalCareBudgType_id
			where
				MCTL.MedicalCareBudgTypeLink_DocumentUcType = :MedicalCareBudgTypeLink_DocumentUcType
				and (
					MCTL.LpuSectionProfile_id is null or
					MCTL.LpuSectionProfile_id = :LpuSectionProfile_id
				)
				and (
					MCTL.Diag_id is null or 
					MCTL.Diag_id = :Diag_id
				)
				and (
					MCTL.MedicalCareBudgTypeLink_DlitTo is null or
					:MedicalCareBudgTypeLink_Dlit between MCTL.MedicalCareBudgTypeLink_DlitTo and MCTL.MedicalCareBudgTypeLink_DlitFrom
				)
				and (
					MCTL.MesAgeGroup_id is null or
					MCTL.MesAgeGroup_id = case when :Person_Age >= 18 then 1 else 2 end
				)
				{$DopList1_str}
				{$DopList2_str}
				and MCTL.MedicalCareBudgTypeLink_begDate <= :endDate
				and (MCTL.MedicalCareBudgTypeLink_endDate is null or MCTL.MedicalCareBudgTypeLink_endDate > :endDate)
				{$filters}
		";

        return $this->queryResult($query, $params);
    }

    /**
     * @param array $data
     * @return array
     */
    function getMedicalCareBudgTypeId($data) {
        $list = $this->getMedicalCareBudgTypeList($data);
        if (!is_array($list)) {
            return $this->createError('','Ошибка при получении типа мед. помощи');
        }

        $id = null;

        $criterias = array(
            'LpuSectionProfile_id' => 1,
            'Diag_id' => 1,
            'MedicalCareBudgTypeLink_DlitTo' => 1,
            'MesAgeGroup_id' => 1,
            'MedicalCareBudgTypeLink_Dop' => 1000,
            'MedicalCareBudgTypeLink_Dop2' => 10000
        );

        if (count($list) == 1) {
            $id = $list[0]['MedicalCareBudgType_id'];
        } else {
            $maxCriteriasWeight = 0;
            $listByCriteriasWeight = array();

            foreach($list as $key => $item) {
                $weight = 0;
                foreach($criterias as $criteriaName => $criteriaWeight) {
                    if (!empty($item[$criteriaName])) {
                        $weight += $criteriaWeight;
                    }
                }
                if ($maxCriteriasWeight < $weight) {
                    $maxCriteriasWeight = $weight;
                }
                $listByCriteriasWeight[$weight][] = $item;
            }

            $maxCriteriasList = array();
            if ($maxCriteriasWeight) {
                $maxCriteriasList = $listByCriteriasWeight[$maxCriteriasWeight];
            }
            if (count($maxCriteriasList) > 0) {
                $id = $maxCriteriasList[0]['MedicalCareBudgType_id'];
            }
        }

        return array(array(
            'success' => true,
            'MedicalCareBudgType_id' => $id
        ));
    }
}