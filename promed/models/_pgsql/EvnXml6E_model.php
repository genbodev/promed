<?php

require_once('EvnXmlBase_model.php');

/**
 * Class EvnXml6E_model
 */
class EvnXml6E_model extends EvnXmlBase_model
{
    protected $dateTimeForm104 = "'dd.mm.yyyy'";
    protected $dateTimeForm108 = "'hh24:mi'";

    /**
     * @param array $data
     * @return array
     */
    public function saveEvnXml($data)
    {
        $this->load->library('swEvnXml');
        $this->load->library('swMarker');
        $this->load->library('swXmlTemplate');
        $this->load->model('XmlTemplate6E_model');

        try {
            $this->beginTransaction();

            $EvnXml_Data = json_decode($data['EvnXml_Data'], true);
            foreach ($EvnXml_Data as $key => &$value) {
                $value = swEvnXml::cleaningHtml($value);
            }
            $EvnXml_Data = swXmlTemplate::convertFormDataArrayToXml([$EvnXml_Data]);

            $params = [
                'EvnXml_id' => !empty($data['EvnXml_id']) ? $data['EvnXml_id'] : null,
                'Evn_id' => $data['Evn_id'],
                'EvnXml_Name' => !empty($data['EvnXml_Name']) ? $data['EvnXml_Name'] : null,
                'EvnXml_Data' => $EvnXml_Data,
                'XmlType_id' => $data['XmlType_id'],
                'XmlTemplate_id' => !empty($data['XmlTemplate_id']) ? $data['XmlTemplate_id'] : null,
                'XmlTemplateSettings_id' => null,
                'XmlTemplateType_id' => 6,    //???
                'XmlTemplateHtml_id' => null,
                'XmlTemplateData_id' => null,
                'XmlTemplateData_Data' => null,
                'XmlSchema_Data' => null,
                'pmUser_id' => $data['pmUser_id'],
            ];

            if (!empty($params['EvnXml_id'])) {
                $query = "
					select
						EX.Evn_id as \"Evn_id\",
						EX.EvnXml_Name as \"EvnXml_Name\",
						EX.XmlTemplateHtml_id as \"XmlTemplateHtml_id\",
						XTD.XmlTemplateData_id as \"XmlTemplateData_id\",
						XTD.XmlTemplateData_Data as \"XmlTemplateData_Data\"
					from 
						v_EvnXml EX
						left join v_XmlTemplateData XTD on XTD.XmlTemplateData_id = EX.XmlTemplateData_id
					where 
						EX.EvnXml_id = :EvnXml_id
					limit 1
				";
                $resp = $this->getFirstRowFromQuery($query, $params);
                if (!is_array($resp)) {
                    return $this->createError('', 'Ошибка при получении данных документа');
                }
                if (!empty($resp['Evn_id']) && $resp['Evn_id'] != $data['Evn_id']) {
                    throw new Exception('Обнаружена смена случая для существующего протокола осмотра, сохранение отменено');
                }
                $params = array_merge($params, $resp);
            }

            if (!empty($params['XmlTemplate_id'])) {
                $query = "
					select
						XT.XmlTemplateSettings_id as \"XmlTemplateSettings_id\",
						XTD.XmlTemplateData_id as \"XmlTemplateData_id\",
						XTD.XmlTemplateData_Data as \"XmlTemplateData_Data\"
					from 
						v_XmlTemplate XT
						left join v_XmlTemplateData XTD on XTD.XmlTemplateData_id = XT.XmlTemplateData_id
					where 
						XT.XmlTemplate_id = :XmlTemplate_id
					limit 1
				";
                $resp = $this->getFirstRowFromQuery($query, $params);
                if (!is_array($resp)) {
                    return $this->createError('', 'Ошибка при получении данных шаблона');
                }
                $params['XmlTemplateSettings_id'] = $resp['XmlTemplateSettings_id'];
                if (empty($params['XmlTemplateData_id'])) {
                    $params['XmlTemplateData_id'] = $resp['XmlTemplateData_id'];
                    $params['XmlTemplateData_Data'] = $resp['XmlTemplateData_Data'];
                }
            }

			if (empty($params['EvnXml_id'])) {
				$procedure = 'p_EvnXml_ins';
			} else {
				$procedure = 'p_EvnXml_upd';
			}
            //#160659 дублировались осмотры в посещении, должен существовать лишь один
            if (empty($params['EvnXml_id']) && $data['XmlType_id'] == 3 && $this->checkEvnXmlExists($data['Evn_id'])) {
                throw new Exception('Для данного посещения уже существует осмотр');
            }


            $resp = $this->XmlTemplate6E_model->saveXmlTemplateHtml([
                'XmlTemplateHtml_id' => $params['XmlTemplateHtml_id'],
                'XmlTemplateHtml_HtmlTemplate' => $data['XmlTemplate_HtmlTemplate'],
                'pmUser_id' => $data['pmUser_id'],
            ]);

            if (!$this->isSuccessful($resp)) {
                throw new Exception($resp[0]['Error_Msg']);
            }
            $params['XmlTemplateHtml_id'] = $resp[0]['XmlTemplateHtml_id'];

            $params['XmlTemplateData_Data'] = $this->XmlTemplate6E_model->createXmlTemplateData(
                $data['XmlTemplate_HtmlTemplate'],
                $data['EvnXml_Data'],
                $data['EvnXml_DataSettings'],
                $params['XmlTemplateData_Data']
            );
            $params['XmlTemplateData_id'] = $this->_searchInHashTable('XmlTemplateData', $params['XmlTemplateData_Data']);
            if (empty($params['XmlTemplateData_id'])) {
                $params['XmlTemplateData_id'] = $this->_insertToHashTable('XmlTemplateData', 'Data', $params['XmlTemplateData_Data']);
            }

            $query = "
            	with cte as (
            		select
			  			case when EvnXml_IsSigned = 2 then 1 else EvnXml_IsSigned end as EvnXml_IsSigned,
						pmUser_signID,
						EvnXml_signDT
					from
						v_EvnXml
					where
						EvnXml_id = :EvnXml_id
            	)
				select
				    EvnXml_id as \"EvnXml_id\",
				    (select EvnXml_IsSigned from cte) as \"EvnXml_IsSigned\",
				    Error_Code as \"Error_Code\",
				    Error_Message as \"Error_Msg\"
				from {$procedure}
				(
					EvnXml_id := :EvnXml_id,
					Evn_id := :Evn_id,
					EvnXml_Data := :EvnXml_Data,
					XmlTemplate_id := :XmlTemplate_id,
					XmlType_id := :XmlType_id,
					EvnXml_Name := :EvnXml_Name,
					XmlSchema_Data := :XmlSchema_Data,
					XmlTemplateType_id := :XmlTemplateType_id,
					XmlTemplateSettings_id := :XmlTemplateSettings_id,
					XmlTemplateHtml_id := :XmlTemplateHtml_id,
					XmlTemplateData_id := :XmlTemplateData_id,
					EvnXml_IsSigned := (select EvnXml_IsSigned from cte),
					pmUser_signID := (select pmUser_signID from cte),
					EvnXml_signDT := (select EvnXml_signDT from cte),
					pmUser_id := :pmUser_id
				)
			";

            $response = $this->queryResult($query, $params);
            if (!is_array($response)) {
                throw new Exception('Ошибка при сохранении документа');
            }
            if (!$this->isSuccessful($response)) {
                throw new Exception($response[0]['Error_Msg']);
            }

            $response[0]['XmlTemplate_id'] = $params['XmlTemplate_id'];

            $this->commitTransaction();
	        if (!empty($response[0]['EvnXml_id'])) {
		        $this->load->model('ApprovalList_model');
		        $this->ApprovalList_model->saveApprovalList(array(
			        'ApprovalList_ObjectName' => 'EvnXml',
			        'ApprovalList_ObjectId' => $response[0]['EvnXml_id'],
			        'pmUser_id' => $data['pmUser_id']
		        ));
	        }
        } catch (Exception $e) {
            return $this->createError('', $e->getMessage());
        }

        return $response;
    }

    /**
     * @param array $data
     * @return array
     */
    public function createEmptyEvnXml($data)
    {
        $params = [
            'Evn_id' => $data['Evn_id'],
            'XmlType_id' => $data['XmlType_id'],
            'XmlTemplate_id' => $data['XmlTemplate_id'],
            'pmUser_id' => $data['pmUser_id'],
        ];

        $this->load->model('XmlTemplate6E_model');

        $resp = $this->XmlTemplate6E_model->getParamsByXmlTemplateOrEvnXml($data);
        if (!$this->isSuccessful($resp)) {
            return $resp;
        }

        $params = array_merge($resp[0]['params'], $params);

        $this->load->library('swXmlTemplate');
        $params['EvnXml_Data'] = swXmlTemplate::transformEvnXmlDataToArr($params['EvnXml_Data']);
        $params['EvnXml_Data'] = json_encode($params['EvnXml_Data']);

        return $this->saveEvnXml($params);
    }

    /**
     * @param array $data
     * @return array|bool
     */
    public function updateEvnXmlData($data)
    {
        return $this->swUpdate('EvnXml', [
            'EvnXml_id' => $data['EvnXml_id'],
            'EvnXml_Data' => $data['EvnXml_Data'],
            'XmlSchema_Data' => !empty($data['XmlSchema_Data']) ? $data['XmlSchema_Data'] : null,
            'pmUser_id' => !empty($data['pmUser_id']) ? $data['pmUser_id'] : $this->promedUserId,
        ]);
    }

    /**
     * @param array $data
     * @return array|bool
     */
    public function getSurveyHeaderData($data)
    {
        $query = "
			select
				to_char(E.Evn_setDT, {$this->dateTimeForm104}) as \"Evn_setDate\",
				to_char(E.Evn_setDT, {$this->dateTimeForm108}) as \"Evn_setTime\",
				PS.Person_SurName as \"Person_SurName\",
				PS.Person_FirName as \"Person_FirName\",
				PS.Person_SecName as \"Person_SecName\",
				to_char(PS.Person_BirthDay, {$this->dateTimeForm104}) as \"Person_BirthDay\",
				dbo.Age2(PS.Person_BirthDay, dbo.tzGetdate()) as \"Person_Age\",
				PAddress.Address_Nick as \"PAddress_Nick\",
				L.Lpu_Nick as \"Lpu_Nick\",
				LS.LpuSection_Name as \"LpuSection_Name\",
				LSP.LpuSectionProfile_Name as \"LpuSectionProfile_Name\",
				MP.Person_Fio as \"MedPersonal_Fio\"
			from
				v_Evn E
				left join v_EvnVizitPL EVPL on EVPL.EvnVizitPL_id = E.Evn_id
				left join v_Person_all PS on PS.PersonEvn_id = E.PersonEvn_id and PS.Server_id = E.Server_id
				left join v_Lpu L on L.Lpu_id = E.Lpu_id
				left join v_LpuSection LS on LS.LpuSection_id = EVPL.LpuSection_id
				left join v_LpuSectionProfile LSP on LSP.LpuSectionProfile_id = coalesce (EVPL.LpuSectionProfile_id, LS.LpuSectionProfile_id)
				left join v_MedPersonal MP on MP.MedPersonal_id = EVPL.MedPersonal_id
				left join v_Address PAddress on PAddress.Address_id = PS.PAddress_id
			where
				E.Evn_id = :Evn_id
			limit 1
		";

        return $this->getFirstRowFromQuery($query, $data);
    }

    /**
     * Проверяет существование осмотра у посещения
     * @param integer $Evn_id
     * @return bool
     */
    public function checkEvnXmlExists($Evn_id)
    {
        if (empty($Evn_id)) {
            return true;
        }
        $query = "
			select EX.EvnXml_id as \"EvnXml_id\"
			from v_EvnXml EX 
			where EX.Evn_id = :Evn_id 
			and XmlType_id = 3
			limit 1
		";
        $result = $this->db->query($query, ['Evn_id' => $Evn_id]);

        if (is_object($result)) {
            $EvnXml = $result->result('array');
            if (is_array($EvnXml) && isset($EvnXml[0]) && isset($EvnXml[0]['EvnXml_id'])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Загрузка предыдущих документов
     * @param $data
     * @return array|false
     */
    public function loadEvnXmlList($data)
    {

        $filter = '';

        if (isset($data['EvnXml_insDT']) && !empty($data['EvnXml_insDT'][0])) {
            $data['EvnXml_insBegDate'] = $data['EvnXml_insDT'][0];
            $data['EvnXml_insEndDate'] = $data['EvnXml_insDT'][1];
            $filter .= " and cast(ex.EvnXml_insDT as date) between :EvnXml_insBegDate and :EvnXml_insEndDate ";
        }

        if (!empty($data['Diag_id'])) {
            $filter .= " and d.Diag_id = :Diag_id ";
        }

        if (!empty($data['cnt'])) {
            $query = "
				select
				    count(*) as cnt
				from
				    v_EvnXml ex 
				where 
					ex.Evn_id != :Evn_id
				and 
					ex.pmUser_insID in (
						select
						    pm.pmUser_id 
						from
						    v_pmUserCache pm
						    inner join v_pmUserCache pmc on pm.MedPersonal_id = pmc.MedPersonal_id
						where
						    pmc.pmUser_id = :pmUser_id
					)
				and 
					ex.XmlType_id = 3
			";
        } else {
            $query = "
				select
					ex.EvnXml_id as \"EvnXml_id\",
					coalesce(ex.EvnXml_Name, ' ') as \"EvnXml_Name\",
					ex.XmlTemplate_id as \"XmlTemplate_id\",
					to_char(ex.EvnXml_insDT, {$this->dateTimeForm104}) as \"EvnXml_insDate\",
					to_char(ex.EvnXml_insDT, {$this->dateTimeForm108}) as \"EvnXml_insTime\",
					d.Diag_Code as \"Diag_Code\",
					d.Diag_Name as \"Diag_Name\",
					lsp.LpuSectionProfile_Name as \"LpuSectionProfile_Name\"
				from
				    v_EvnXml ex 
                    inner join v_Evn evn on ex.Evn_id = evn.Evn_id
                    left join v_EvnVizitPL EVPL on EVPL.EvnVizitPL_id = evn.Evn_id
                    left join v_EvnSection ES on ES.EvnSection_id = evn.Evn_id
                    left join v_Diag d on d.Diag_id = coalesce (EVPL.Diag_id, ES.Diag_id)
                    left join v_LpuSection ls on ls.LpuSection_id = coalesce (EVPL.LpuSection_id, ES.LpuSection_id)
                    left join v_LpuSectionProfile lsp on lsp.LpuSectionProfile_id = ls.LpuSectionProfile_id
				where 
					ex.Evn_id != :Evn_id
                and 
					ex.XmlTemplate_id is not null
				and
					ex.pmUser_insID in (
						select
						    pm.pmUser_id 
						from
						    v_pmUserCache pm
						    inner join v_pmUserCache pmc on pm.MedPersonal_id = pmc.MedPersonal_id
						where
						    pmc.pmUser_id = :pmUser_id
					)
                and
					ex.XmlType_id = 3
					{$filter}
				order by 
					ex.EvnXml_id desc
				limit 20
			";
        }


        return $this->queryResult($query, $data);
    }

    /**
     * Создание документа путём копирования
     *
     * @param $data
     * @return array
     */
    public function copyEvnXml($data)
    {
        $params = [
            'Evn_id' => $data['Evn_id'],
            'XmlType_id' => $data['XmlType_id'],
            'XmlTemplate_id' => $data['XmlTemplate_id'],
            'pmUser_id' => $data['pmUser_id'],
        ];

        $this->load->model('XmlTemplate6E_model');

        $resp = $this->XmlTemplate6E_model->getParamsByXmlTemplateOrEvnXml(['XmlTemplate_id' => $data['XmlTemplate_id']]);
        if (!$this->isSuccessful($resp)) {
            return $resp;
        }

        $params = array_merge($resp[0]['params'], $params);
        $query = "
            select
                EvnXml_id as \"EvnXml_id\"
            from
                v_EvnXml
            where
                Evn_id = :Evn_id
            limit 1
        ";

        $params['EvnXml_id'] = $this->getFirstResultFromQuery($query, $data, true);

        $this->load->library('swXmlTemplate');
        $params['EvnXml_Data'] = swXmlTemplate::transformEvnXmlDataToArr($params['EvnXml_Data']);
        $params['EvnXml_Data'] = json_encode($params['EvnXml_Data']);

        return $this->saveEvnXml($params);
    }
}