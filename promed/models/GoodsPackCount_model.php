<?php defined('BASEPATH') or die ('No direct script access allowed');

class GoodsPackCount_model extends swModel {
    /**
     * Конструктор
     */
    function __construct(){
        parent::__construct();
    }

    /**
     * Загрузка
     */
    function load($data) {
        $result = array();

        if (!empty($data['GoodsPackCount_id'])) {
            $query = "
                select
                    gpc.GoodsPackCount_id,
                    gpc.DrugComplexMnn_id,
                    gpc.TRADENAMES_ID,
                    gpc.GoodsPackCount_Count,
                    gpc.GoodsUnit_id,
                    gpc.Org_id,
                    (case
                        when gpc.TRADENAMES_ID is not null then d.Drug_Name
                        else dcmn.DrugComplexMnnName_Name
                    end) as Drug_Name
                from
                    dbo.v_GoodsPackCount gpc with (nolock)
                    outer apply (
                        select top 1
                            i_d.Drug_Name
                        from
                            rls.v_Drug i_d with (nolock)
                        where
                            i_d.DrugComplexMnn_id = gpc.DrugComplexMnn_id and
                            (
                                gpc.TRADENAMES_ID is null or
                                i_d.DrugTorg_id = gpc.TRADENAMES_ID
                            )
                        order by
                            i_d.Drug_id
                    ) d
                    left join rls.v_DrugComplexMnn dcm with (nolock) on dcm.DrugComplexMnn_id = gpc.DrugComplexMnn_id
                    left join rls.v_DrugComplexMnnName dcmn with (nolock) on dcmn.DrugComplexMnnName_id = dcm.DrugComplexMnnName_id
                where
                    GoodsPackCount_id = :GoodsPackCount_id
            ";
            $result = $this->getFirstRowFromQuery($query, array(
                'GoodsPackCount_id' => $data['GoodsPackCount_id']
            ));
        } else if (!empty($data['Drug_id']) && !empty($data['GoodsUnit_id'])) {
            $query = "
                declare
                    @GoodsUnit_id bigint = :GoodsUnit_id,
                    @Drug_id bigint = :Drug_id,
                    @DrugComplexMnn_id bigint = null,
                    @Tradnames_id bigint = null,
                    @Drug_Name varchar(max) = null;

                if (@Drug_id is not null)
                begin
                    select
                        @Drug_Name = d.Drug_Name,
                        @DrugComplexMnn_id = d.DrugComplexMnn_id,
                        @Tradnames_id = d.DrugTorg_id
                    from
                        rls.v_Drug d with (nolock)
                    where
                        Drug_id = @Drug_id
                end;

                select top 1
                    gpc.GoodsPackCount_id,
                    gpc.DrugComplexMnn_id,
                    gpc.TRADENAMES_ID,
                    gpc.GoodsPackCount_Count,
                    gpc.GoodsUnit_id,
                    gpc.Org_id,
                    @Drug_Name as Drug_Name
                from
                    v_GoodsPackCount gpc with (nolock)
                where
                    gpc.GoodsUnit_id = :GoodsUnit_id and
                    gpc.DrugComplexMnn_id = @DrugComplexMnn_id and
                    (
                        @Tradnames_id is null or
                        gpc.TRADENAMES_ID is null or
                        gpc.TRADENAMES_ID = @Tradnames_id
                    ) and
                    (
                        gpc.Org_id is null or
                        isnull(gpc.Org_id, 0) = isnull(:UserOrg_id, 0)
                    )
                order by
                    gpc.TRADENAMES_ID desc, gpc.Org_id desc;
            ";
            $result = $this->getFirstRowFromQuery($query, array(
                'Drug_id' => $data['Drug_id'],
                'GoodsUnit_id' => $data['GoodsUnit_id'],
                'UserOrg_id' => $data['UserOrg_id']
            ));
        }

        //если ничего не нашлось, грузим хотябы данные медикамента
        if (!empty($data['Drug_id']) && empty($result['DrugComplexMnn_id'])) {
            $query = "
                select
                    d.Drug_Name,
                    d.DrugComplexMnn_id,
                    d.DrugTorg_id as TRADENAMES_ID
                from
                    rls.v_Drug d with (nolock)
                where
                    d.Drug_id = :Drug_id;
            ";
            $drug_data = $this->getFirstRowFromQuery($query, array(
                'Drug_id' => $data['Drug_id']
            ));
            if (!empty($drug_data['DrugComplexMnn_id'])) {
                $result['Drug_Name'] = $drug_data['Drug_Name'];
                $result['DrugComplexMnn_id'] = $drug_data['DrugComplexMnn_id'];
                $result['TRADENAMES_ID'] = $drug_data['TRADENAMES_ID'];
            }
        }

        if (is_array($result) && count($result) > 0) {
            return array($result);
        } else {
            return false;
        }
    }

    /**
     * Сохранение
     */
    function save($data) {
        $save_data = $data;

        $gpc_id = null;
        $result = array();
        $rec_data = array();
        $save_enabled = false;

        //ищем существующую запись с заданными параметрами
        $query = "
            select
                gpc.GoodsPackCount_id,
                gpc.DrugComplexMnn_id,
                gpc.TRADENAMES_ID,
                gpc.GoodsPackCount_Count,
                gpc.GoodsUnit_id,
                gpc.Org_id
            from
                v_GoodsPackCount gpc with (nolock)
            where
                gpc.DrugComplexMnn_id = :DrugComplexMnn_id and
                isnull(gpc.TRADENAMES_ID, 0) = isnull(:TRADENAMES_ID, 0) and
                gpc.GoodsUnit_id = :GoodsUnit_id and
                isnull(gpc.Org_id, 0) = isnull(:Org_id, 0);
        ";
        $rec_data = $this->getFirstRowFromQuery($query, $save_data);


        if (!empty($rec_data['GoodsPackCount_Count'])) { //если данные найдены, сравниваем их с тем что пришло с формы
            $diff_exists = false;

            //приводим количества к одному виду
            $save_data['GoodsPackCount_Count'] *= 1;
            $rec_data['GoodsPackCount_Count'] *= 1;

            //сверка данных
            foreach(array_keys($rec_data) as $key) {
                if ($key != 'GoodsPackCount_id' && (!isset($save_data[$key]) || $save_data[$key] != $rec_data[$key])) {
                    $diff_exists = true;
                    break;
                }
            }

            //если данные отличаются, вносим изменения в БД
            if ($diff_exists) {
                //если организация и ед. измерения в БД совпадает с организацией пользователя и переданной ед. измерения, редактируем запись, в противном случае создаем новую
                $save_data['GoodsPackCount_id'] = $rec_data['Org_id'] == $data['Org_id'] ? $rec_data['GoodsPackCount_id'] : null;
                $save_data['Org_id'] = $data['UserOrg_id'];
                $save_enabled = true;
            } else {
                $gpc_id = $rec_data['GoodsPackCount_id'];
            }
        } else { //иначе создаем новую запись
            $save_data['GoodsPackCount_id'] = null;
            $save_data['Org_id'] = $data['UserOrg_id'];
            $save_enabled = true;
        }

        //сохраняем или редактируем данные
        if ($save_enabled) {
            $save_result = $this->saveObject('GoodsPackCount', $save_data);
            if (!empty($save_result['GoodsPackCount_id'])) {
                $gpc_id = $save_result['GoodsPackCount_id'];
            }
        }

        //получаем данные для возврата
        if (!empty($gpc_id)) {
            $query = "
                select
                    gpc.GoodsPackCount_id,
                    gpc.GoodsPackCount_Count,
                    gpc.GoodsUnit_id
                from
                    v_GoodsPackCount gpc with (nolock)
                where
                    gpc.GoodsPackCount_id = :GoodsPackCount_id
            ";
            $result = $this->getFirstRowFromQuery($query, array(
                'GoodsPackCount_id' => $gpc_id
            ));
            $result['success'] = true;
        }

        return $result;
    }
}