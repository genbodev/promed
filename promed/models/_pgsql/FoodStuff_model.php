<?php

defined('BASEPATH') or die ('No direct script access allowed');
/**
 * FoodStuff_model - модель для работы с продуктами питания
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Cook
 * @access			public
 * @copyright		Copyright (c) 2013 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			01.10.2013
 */
class FoodStuff_model extends SwPgModel
{
    protected $dateTimeForm104 = "'dd.mm.yyyy'";
    /**
     * Возвращает список продуктов питания
     * @param $data
     * @return array|bool
     */
    public function loadFoodStuffGrid($data)
    {
        if (!(($data['start'] >= 0) && ($data['limit'] >= 0)))
        {
            return false;
        }

        $filters = '(1=1)';
        $params = [];

        if (!empty($data['FoodStuff_Code']))
        {
            $filters .= " and FoodStuff_Code ilike :FoodStuff_Code";
            $params['FoodStuff_Code'] = $data['FoodStuff_Code'].'%';
        }

        if (!empty($data['FoodStuff_Name']))
        {
            $filters .= " and FoodStuff_Name ilike :FoodStuff_Name";
            $params['FoodStuff_Name'] = $data['FoodStuff_Name'].'%';
        }

        $query = "
			select
				-- select
				FS.FoodStuff_id as \"FoodStuff_id\",
				FS.FoodStuff_Code as \"FoodStuff_Code\",
				FS.FoodStuff_Name as \"FoodStuff_Name\",
				FS.FoodStuff_Descr as \"FoodStuff_Descr\",
				FSP.FoodStuffPrice_Price as \"FoodStuffPrice_Price\",
				FS.FoodStuff_Protein as \"FoodStuff_Protein\",
				FS.FoodStuff_Fat as \"FoodStuff_Fat\",
				FS.FoodStuff_Carbohyd as \"FoodStuff_Carbohyd\",
				FS.FoodStuff_Caloric as \"FoodStuff_Caloric\"
				-- end select
			from
				-- from
				v_FoodStuff FS
				left join lateral (
					select
					    FoodStuffPrice_Price
					from
					    v_FoodStuffPrice
					where
					    FoodStuff_id = FS.FoodStuff_id
					order by
					    FoodStuffPrice_begDate desc
				    limit 1
				) FSP on true
				-- end from
			where
				-- where
				{$filters}
				-- end where
			order by
				-- order by
				FS.FoodStuff_id
				-- end order by
			";

        $result = $this->db->query(getLimitSQLPH($query, $data['start'], $data['limit']),$params);
        $result_count = $this->db->query(getCountSQLPH($query),$params);

        if (is_object($result_count)) {
            $cnt_arr = $result_count->result('array');
            $count = $cnt_arr[0]['cnt'];
            unset($cnt_arr);
        } else {
            $count = 0;
        }
        if (!is_object($result)) {
            return false;
        }

        $response = [];
        $response['data'] = $result->result('array');
        $response['totalCount'] = $count;
        return $response;
    }

    /**
     * Возвращает данные для редактировния продукта питания
     */
    function loadFoodStuffEditForm($data)
    {
        $query = "
			select
				FS.FoodStuff_id as \"FoodStuff_id\",
				FS.FoodStuff_Code as \"FoodStuff_Code\",
				FS.FoodStuff_Name as \"FoodStuff_Name\",
				FS.FoodStuff_Descr as \"FoodStuff_Descr\",
				FS.Okei_id as \"Okei_id\",
				FS.FoodStuff_StorCond as \"FoodStuff_StorCond\",
				FS.FoodStuff_Protein as \"FoodStuff_Protein\",
				FS.FoodStuff_Fat as \"FoodStuff_Fat\",
				FS.FoodStuff_Carbohyd as \"FoodStuff_Carbohyd\",
				FS.FoodStuff_Caloric as \"FoodStuff_Caloric\"
			from
				v_FoodStuff FS
			where
				FS.FoodStuff_id = :FoodStuff_id
			";

        $result = $this->db->query($query, array('FoodStuff_id' => $data['FoodStuff_id']));

        if (!is_object($result))
            return false;


        $response = $result->result('array');
        return $response;
    }

    /**
     * Возвращает данные о цене продукта питания для формы редактировния
     * @param $data
     * @return array|bool
     */
    public function loadFoodStuffPriceEditForm($data)
    {
        $query = "
			select
				FSP.FoodStuff_id as \"FoodStuff_id\",
				FSP.FoodStuffPrice_id as \"FoodStuffPrice_id\",
				to_char(FSP.FoodStuffPrice_begDate, {$this->dateTimeForm104}) as \"FoodStuffPrice_begDate\",
				FSP.FoodStuffPrice_Price as \"FoodStuffPrice_Price\"
			from
				v_FoodStuffPrice FSP
			where
				FSP.FoodStuffPrice_id = :FoodStuffPrice_id
			";

        $result = $this->db->query($query, ['FoodStuffPrice_id' => $data['FoodStuffPrice_id']]);

        if (!is_object($result))
            return false;

        $response = $result->result('array');
        return $response;
    }

    /**
     * Возвращает данные о цзаменителе продукта питания для формы редактировния
     * @param $data
     * @return array|bool
     */
    public function loadFoodStuffSubstitEditForm($data)
    {
        $query = "
			select
				FSS.FoodStuff_id as \"FoodStuff_id\",
				FSS.FoodStuffSubstit_id as \"FoodStuffSubstit_id\",
				FSS.FoodStuff_sid as \"FoodStuff_sid\",
				FSS.FoodStuffSubstit_Priority as \"FoodStuffSubstit_Priority\",
				FSS.FoodStuffSubstit_Coeff as \"FoodStuffSubstit_Coeff\"
			from
				v_FoodStuffSubstit FSS
			where
				FSS.FoodStuffSubstit_id = :FoodStuffSubstit_id
			";

        $result = $this->db->query($query, array('FoodStuffSubstit_id' => $data['FoodStuffSubstit_id']));

        if (!is_object($result))
            return false;

        $response = $result->result('array');
        return $response;
    }

    /**
     * Возвращает данные о микронутриентах продукта питания для формы редактировния
     * @param $data
     * @return array|bool
     */
    public function loadFoodStuffMicronutrientEditForm($data)
    {
        $query = "
			select
				FSM.FoodStuff_id as \"FoodStuff_id\",
				FSM.FoodStuffMicronutrient_id as \"FoodStuffMicronutrient_id\",
				FSM.Micronutrient_id as \"Micronutrient_id\",
				M.Micronutrient_Name as \"Micronutrient_Name\",
				FSM.FoodStuffMicronutrient_Content as \"FoodStuffMicronutrient_Content\"
			from
				v_FoodStuffMicronutrient FSM
				left join v_Micronutrient M on M.Micronutrient_id = FSM.Micronutrient_id
			where
				FSM.FoodStuffMicronutrient_id = :FoodStuffMicronutrient_id
			";

        $result = $this->db->query($query, ['FoodStuffMicronutrient_id' => $data['FoodStuffMicronutrient_id']]);

        if (!is_object($result))
            return false;

        $response = $result->result('array');
        return $response;
    }

    /**
     * Возвращает данные о пересчетном коэффициенте продукта питания для формы редактировния
     * @param $data
     * @return array|bool
     */
    public function loadFoodStuffCoeffEditForm($data)
    {
        $query = "
			select
				FSC.FoodStuff_id as \"FoodStuff_id\",
				FSC.FoodStuffCoeff_id as \"FoodStuffCoeff_id\",
				FSC.Okei_id as \"Okei_id\",
				FSC.FoodStuffCoeff_Coeff as \"FoodStuffCoeff_Coeff\",
				FSC.FoodStuffCoeff_Descr as \"FoodStuffCoeff_Descr\"
			from
				v_FoodStuffCoeff FSC
			where
				FSC.FoodStuffCoeff_id = :FoodStuffCoeff_id
			";

        $result = $this->db->query($query, array('FoodStuffCoeff_id' => $data['FoodStuffCoeff_id']));

        if (!is_object($result))
            return false;

        $response = $result->result('array');
        return $response;
    }

    /**
     * Сохраняет запись о продукте питания
     * @param $data
     * @return array|bool
     */
    public function saveFoodStuff($data)
    {
        $success = true;

        $query = "
			select
			    COUNT(*) as \"Count\"
			from
			    v_FoodStuff FS
			where
				FS.FoodStuff_Code = :FoodStuff_Code
            or
                FS.FoodStuff_Name ilike :FoodStuff_Name
		";

        $res = $this->db->query($query, array(
            'FoodStuff_Name' => $data['FoodStuff_Name'],
            'FoodStuff_Code' => $data['FoodStuff_Code'],
        ));

        if ( !is_object($res) ) {
            $success = false;
        }
        else
        {
            $response = $res->result('array');
            if ( $response[0]['Count'] > 0 ) {
                $msg = 'Значение полей "наименования" и "код" должны быть уникальными';
                return [['Error_Msg' => $msg]];
            }
        }

        $procedure = 'p_FoodStuff_upd';
        if ( !isset($data['FoodStuff_id']) || $data['FoodStuff_id'] <= 0 ) {
            $data['FoodStuff_id'] = null;
            $procedure = 'p_FoodStuff_ins';
        }


        $query = "
			select 
			    FoodStuff_id as \"FoodStuff_id\",
			    Error_Code as \"Error_Code\",
			    Error_Message as \"Error_Msg\"
			from " . $procedure . "
			(
				FoodStuff_id := :FoodStuff_id,
				FoodStuff_Name := :FoodStuff_Name,
				FoodStuff_Code := :FoodStuff_Code,
				FoodStuff_Descr := :FoodStuff_Descr,
				Okei_id := :Okei_id,
				FoodStuff_StorCond := :FoodStuff_StorCond,
				FoodStuff_Protein := :FoodStuff_Protein,
				FoodStuff_Fat := :FoodStuff_Fat,
				FoodStuff_Carbohyd := :FoodStuff_Carbohyd,
				FoodStuff_Caloric := :FoodStuff_Caloric,
				pmUser_id := :pmUser_id
			)
		";

        $params = [
            'FoodStuff_id' => $data['FoodStuff_id'],
            'FoodStuff_Name' => $data['FoodStuff_Name'],
            'FoodStuff_Code' => $data['FoodStuff_Code'],
            'FoodStuff_Descr' => $data['FoodStuff_Descr'],
            'Okei_id' => $data['Okei_id'],
            'FoodStuff_StorCond' => $data['FoodStuff_StorCond'],
            'FoodStuff_Protein' => $data['FoodStuff_Protein'],
            'FoodStuff_Fat' => $data['FoodStuff_Fat'],
            'FoodStuff_Carbohyd' => $data['FoodStuff_Carbohyd'],
            'FoodStuff_Caloric' => $data['FoodStuff_Caloric'],
            'pmUser_id' => $data['pmUser_id']
        ];

        $res = $this->db->query($query, $params);

        if (!is_object($res))
            return false;

        return $res->result('array');
    }

    /**
     * Сохраняет запись о заменителе продукта питания
     * @param $data
     * @return array|bool
     */
    public function saveFoodStuffSubstit($data)
    {
        $success = true;
        $where = (!empty($data['FoodStuffSubstit_id'])) ? " AND FSS.FoodStuffSubstit_id <> :FoodStuffSubstit_id" : "";
        $query = "
			select
			    COUNT(*) as \"Count\"
			from
			    v_FoodStuffSubstit FSS
			where
				FSS.FoodStuff_id = :FoodStuff_id
            and
                FSS.FoodStuff_sid = :FoodStuff_sid
				{$where}
		";

        $res = $this->db->query($query, $data);

        if ( !is_object($res) ) {
            $success = false;
        }else {
            $response = $res->result('array');
            if ( $response[0]['Count'] > 0 ) {
                $msg = 'Выбранный заменитель у продукта уже имеется';
                return [0 => ['Error_Msg' => $msg]];
            }
        }

        $procedure = '';
        if ( (!isset($data['FoodStuffSubstit_id'])) || ($data['FoodStuffSubstit_id'] <= 0) ) {
            $data['FoodStuffSubstit_id'] = null;
            $procedure = 'p_FoodStuffSubstit_ins';
        }
        else {
            $procedure = 'p_FoodStuffSubstit_upd';
        }

        $query = "
			select 
			    FoodStuffSubstit_id as \"FoodStuffSubstit_id\",
			    Error_Code as \"Error_Code\",
			    Error_Message as \"Error_Msg\"
			from " . $procedure . "
			(
				FoodStuffSubstit_id := :FoodStuffSubstit_id,
				FoodStuff_id := :FoodStuff_id,
				FoodStuff_sid := :FoodStuff_sid,
				FoodStuffSubstit_Priority := :FoodStuffSubstit_Priority,
				FoodStuffSubstit_Coeff := :FoodStuffSubstit_Coeff,
				pmUser_id := :pmUser_id
			)
		";

        $params = [
            'FoodStuffSubstit_id' => $data['FoodStuffSubstit_id'],
            'FoodStuff_id' => $data['FoodStuff_id'],
            'FoodStuff_sid' => $data['FoodStuff_sid'],
            'FoodStuffSubstit_Priority' => $data['FoodStuffSubstit_Priority'],
            'FoodStuffSubstit_Coeff' => $data['FoodStuffSubstit_Coeff'],
            'pmUser_id' => $data['pmUser_id']
        ];

        $res = $this->db->query($query, $params);

        if (!is_object($res))
            return false;

        return $res->result('array');
    }

    /**
     * Сохраняет запись о цене продукта питания
     * @param $data
     * @return array|bool
     */
    public function saveFoodStuffPrice($data)
    {
        $success = true;

        if ( (!isset($data['FoodStuffPrice_id'])) || ($data['FoodStuffPrice_id'] <= 0) ) {
            $query = "
				select
				    COUNT(*) as \"Count\"
				from
				    v_FoodStuffPrice FSP
				where
					FSP.FoodStuff_id = :FoodStuff_id
                and
                    FSP.FoodStuffPrice_begDate = :FoodStuffPrice_begDate
			";

            $res = $this->db->query($query, [
                'FoodStuff_id' => $data['FoodStuff_id'],
                'FoodStuffPrice_begDate' => $data['FoodStuffPrice_begDate'],
            ]);

            if ( !is_object($res) ) {
                $success = false;
            } else {
                $response = $res->result('array');
                if ( $response[0]['Count'] > 0 ) {
                    $msg = 'На одну дату может быть только одна цена';
                    return [['Error_Msg' => $msg]];
                }
            }
        }

        $procedure = '';
        if ( (!isset($data['FoodStuffPrice_id'])) || ($data['FoodStuffPrice_id'] <= 0) ) {
            $data['FoodStuffPrice_id'] = null;
            $procedure = 'p_FoodStuffPrice_ins';
        } else {
            $procedure = 'p_FoodStuffPrice_upd';
        }

        $query = "
		    select
		        FoodStuffPrice_id as \"FoodStuffPrice_id\",
		        Error_Code as \"Error_Code\",
		        Error_Message as \"Error_Msg\"
			from " . $procedure . "
			(
				FoodStuffPrice_id := :FoodStuffPrice_id,
				FoodStuff_id := :FoodStuff_id,
				FoodStuffPrice_begDate := :FoodStuffPrice_begDate,
				FoodStuffPrice_Price := :FoodStuffPrice_Price,
				pmUser_id := :pmUser_id
			)
		";

        $params = [
            'FoodStuffPrice_id' => $data['FoodStuffPrice_id'],
            'FoodStuff_id' => $data['FoodStuff_id'],
            'FoodStuffPrice_begDate' => $data['FoodStuffPrice_begDate'],
            'FoodStuffPrice_Price' => $data['FoodStuffPrice_Price'],
            'pmUser_id' => $data['pmUser_id']
        ];

        $res = $this->db->query($query, $params);

        if (!is_object($res))
            return false;

        return $res->result('array');
    }

    /**
     * Сохраняет запись о заменителе продукта питания
     * @param $data
     * @return array|bool
     */
    public function saveFoodStuffMicronutrient($data)
    {
        $success = true;

        $query = "
			select
			    COUNT(*) as \"Count\"
			from
			    v_FoodStuffMicronutrient FSM
			where
				FSM.FoodStuff_id = :FoodStuff_id
            and
                FSM.Micronutrient_id = :Micronutrient_id
		";

        $res = $this->db->query($query, [
            'FoodStuff_id' => $data['FoodStuff_id'],
            'Micronutrient_id' => $data['Micronutrient_id'],
        ]);

        if ( !is_object($res) ) {
            $success = false;
        } else {
            $response = $res->result('array');
            if ( $response[0]['Count'] > 0 ) {
                $msg = 'Выбранный микронутриент у продукта уже имеется';
                return [['Error_Msg' => $msg]];
            }
        }

        $procedure = 'p_FoodStuffMicronutrient_upd';
        if ( (!isset($data['FoodStuffMicronutrient_id'])) || ($data['FoodStuffMicronutrient_id'] <= 0) ) {
            $data['FoodStuffMicronutrient_id'] = null;
            $procedure = 'p_FoodStuffMicronutrient_ins';
        }

        $query = "
		    select
		        FoodStuffMicronutrient_id as FoodStuffMicronutrient_id,
		        Error_Code as \"Error_Code\",
		        Error_Message as \"Error_Msg\"
		    from " . $procedure . "
		    (
		        FoodStuffMicronutrient_id := :FoodStuffMicronutrient_id,
				FoodStuff_id := :FoodStuff_id,
				Micronutrient_id := :Micronutrient_id,
				FoodStuffMicronutrient_Content := :FoodStuffMicronutrient_Content,
				pmUser_id := :pmUser_id
		    )
		";

        $params = [
            'FoodStuffMicronutrient_id' => $data['FoodStuffMicronutrient_id'],
            'FoodStuff_id' => $data['FoodStuff_id'],
            'Micronutrient_id' => $data['Micronutrient_id'],
            'FoodStuffMicronutrient_Content' => $data['FoodStuffMicronutrient_Content'],
            'pmUser_id' => $data['pmUser_id']
        ];

        $res = $this->db->query($query, $params);

        if (!is_object($res))
            return false;

        return $res->result('array');
    }

    /**
     * Сохраняет запись о пересчетных коэффициентах продукта питания
     * @param $data
     * @return array|bool
     */
    public function saveFoodStuffCoeff($data)
    {
        $success = true;

        $query = "
			select
			    COUNT(*) as \"Count\"
			from
			    v_FoodStuffCoeff FSС
			where
                FSС.FoodStuff_id = :FoodStuff_id
            and
                FSС.Okei_id = :Okei_id
		";

        $res = $this->db->query($query, [
            'FoodStuff_id' => $data['FoodStuff_id'],
            'Okei_id' => $data['Okei_id'],
        ]);

        if ( !is_object($res) ) {
            $success = false;
        } else {
            $response = $res->result('array');
            if ( $response[0]['Count'] > 0 ) {
                $msg = 'Выбранная единица измерения уже указана для продукта';
                return [['Error_Msg' => $msg]];
            }
        }

        $procedure = 'p_FoodStuffCoeff_upd';
        if ( (!isset($data['FoodStuffCoeff_id'])) || ($data['FoodStuffCoeff_id'] <= 0) ) {
            $data['FoodStuffCoeff_id'] = null;
            $procedure = 'p_FoodStuffCoeff_ins';
        }

        $query = "
			select
			    FoodStuffCoeff_id as \"FoodStuffCoeff_id\",
			    ErrCode as \"Error_Code\",
			    ErrMessage as \"Error_Msg\"
			from " . $procedure . "
			(
				FoodStuffCoeff_id := :FoodStuffCoeff_id,
				FoodStuff_id := :FoodStuff_id,
				Okei_id := :Okei_id,
				FoodStuffCoeff_Coeff := :FoodStuffCoeff_Coeff,
				FoodStuffCoeff_Descr := :FoodStuffCoeff_Descr,
				pmUser_id := :pmUser_id
			)
		";

        $params = [
            'FoodStuffCoeff_id' => $data['FoodStuffCoeff_id'],
            'FoodStuff_id' => $data['FoodStuff_id'],
            'Okei_id' => $data['Okei_id'],
            'Okei_id' => $data['Okei_id'],
            'FoodStuffCoeff_Coeff' => $data['FoodStuffCoeff_Coeff'],
            'FoodStuffCoeff_Descr' => $data['FoodStuffCoeff_Descr'],
            'pmUser_id' => $data['pmUser_id']
        ];

        $res = $this->db->query($query, $params);

        if (!is_object($res))
            return false;

        return $res->result('array');
    }

    /**
     * Возвращает список заменителей продукта питания
     * @param $data
     * @return array|bool
     */
    public function loadFoodStuffSubstitGrid($data)
    {
        $query = "
			select
				FSS.FoodStuffSubstit_id as \"FoodStuffSubstit_id\",
				FSS.FoodStuff_id as \"FoodStuff_id\",
				FSS.FoodStuff_sid as \"FoodStuff_sid\",
				FSS.FoodStuffSubstit_Priority as \"FoodStuffSubstit_Priority\",
				FS.FoodStuff_Name as \"FoodStuff_Name\",
				FSS.FoodStuffSubstit_Coeff as \"FoodStuffSubstit_Coeff\"
			from
				v_FoodStuffSubstit FSS
				left join v_FoodStuff FS on FS.FoodStuff_id = FSS.FoodStuff_sid
			where
				FSS.FoodStuff_id = :FoodStuff_id
			";

        $result = $this->db->query($query, ['FoodStuff_id' => $data['FoodStuff_id']]);

        if (!is_object($result))
            return false;

        $response = $result->result('array');
        return $response;
    }

    /**
     * Возвращает список цен продукта питания
     * @param $data
     * @return array|bool
     */
    public function loadFoodStuffPriceGrid($data)
    {
        $query = "
			select
				FSP.FoodStuff_id as \"FoodStuff_id\",
				FSP.FoodStuffPrice_id as \"FoodStuffPrice_id\",
				to_char(FSP.FoodStuffPrice_begDate, {$this->dateTimeForm104}) as \"FoodStuffPrice_begDate\",
				FSP.FoodStuffPrice_Price as \"FoodStuffPrice_Price\"
			from
				v_FoodStuffPrice FSP
			where
				FSP.FoodStuff_id = :FoodStuff_id
			";

        $result = $this->db->query($query, ['FoodStuff_id' => $data['FoodStuff_id']]);

        if (!is_object($result))
            return false;

        $response = $result->result('array');
        return $response;
    }

    /**
     * Возвращает список микронутриентов продукта питания
     * @param $data
     * @return array|bool
     */
    public function loadFoodStuffMicronutrientGrid($data)
    {
        $query = "
			select
				FSM.FoodStuff_id as \"FoodStuff_id\",
				FSM.FoodStuffMicronutrient_id as \"FoodStuffMicronutrient_id\",
				M.Micronutrient_Name as \"Micronutrient_Name\",
				FSM.FoodStuffMicronutrient_Content as \"FoodStuffMicronutrient_Content\"
			from
				v_FoodStuffMicronutrient FSM
				left join v_Micronutrient M on M.Micronutrient_id = FSM.Micronutrient_id
			where
				FSM.FoodStuff_id = :FoodStuff_id
			";

        $result = $this->db->query($query, ['FoodStuff_id' => $data['FoodStuff_id']]);

        if (!is_object($result))
            return false;

        $response = $result->result('array');
        return $response;
    }

    /**
     * Возвращает список пересчетных коэффициентов продукта питания
     * @param $data
     * @return array|bool
     */
    public function loadFoodStuffCoeffGrid($data)
    {
        $query = "
			select
				FSC.FoodStuff_id as \"FoodStuff_id\",
				FSC.FoodStuffCoeff_id as \"FoodStuffCoeff_id\",
				FSC.Okei_id as \"Okei_id\",
				O.Okei_Name as \"Okei_Name\",
				FSC.FoodStuffCoeff_Coeff as \"FoodStuffCoeff_Coeff\",
				FSC.FoodStuffCoeff_Descr as \"FoodStuffCoeff_Descr\"
			from
				v_FoodStuffCoeff FSC
				left join v_Okei O on O.Okei_id = FSC.Okei_id
			where
				FSC.FoodStuff_id = :FoodStuff_id
			";

        $result = $this->db->query($query, ['FoodStuff_id' => $data['FoodStuff_id']]);

        if (!is_object($result))
            return false;

        $response = $result->result('array');
        return $response;
    }

    /**
     * Возвращает список продуктов питания
     * @param $data
     * @return array|bool
     */
    public function loadFoodStuffList($data)
    {
        $filters = '(1=1)';
        $params = [];

        if (!empty($data['FoodStuff_id'])) {
            $filters .= " and FoodStuff_id = :FoodStuff_id";
            $params['FoodStuff_id'] = $data['FoodStuff_id'];
        }
        else {
            if (!empty($data['query'])) {
                $filters .= " and FoodStuff_Name ilike :FoodStuff_Name";
                $params['FoodStuff_Name'] = $data['query'].'%';
            }
        }

        $query = "
			select 
				FS.FoodStuff_id as \"FoodStuff_id\",
				FS.FoodStuff_Code as \"FoodStuff_Code\",
				FS.FoodStuff_Name as \"FoodStuff_Name\",
				FS.FoodStuff_Protein as \"FoodStuff_Protein\",
				FS.FoodStuff_Fat as \"FoodStuff_Fat\",
				FS.FoodStuff_Carbohyd as \"FoodStuff_Carbohyd\",
				FS.FoodStuff_Caloric as \"FoodStuff_Caloric\"
			from
				v_FoodStuff FS
			where
				{$filters}
			order by
				FS.FoodStuff_Name
			limit 100
        ";

        $result = $this->db->query($query, $params);

        if (!is_object($result))
            return false;

        $response = $result->result('array');
        return $response;
    }

}