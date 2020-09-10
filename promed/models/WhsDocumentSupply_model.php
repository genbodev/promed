<?php defined('BASEPATH') or die ('No direct script access allowed');

class WhsDocumentSupply_model extends swModel {
	private $WhsDocumentUc_pid;//WhsDocumentUc_pid
	private $WhsDocumentUc_Num;//WhsDocumentUc_Num
	private $WhsDocumentUc_Name;//WhsDocumentUc_Name
	private $WhsDocumentType_id;//WhsDocumentType_id
	private $WhsDocumentUc_Date;//WhsDocumentUc_Date
	private $Org_aid;//Org_aid
	private $Org_sid;//Org_sid
	private $Org_cid;//Org_cid
	private $Org_pid;//Org_pid
	private $Org_rid;//Org_rid
	private $WhsDocumentUc_Sum;//WhsDocumentUc_Sum
	private $WhsDocumentSupply_id;//Идентификатор
	private $WhsDocumentUc_id;//Идентификатор документа учета
	private $WhsDocumentSupply_ProtNum;//Номер протокола аукциона
	private $WhsDocumentSupply_ProtDate;//Дата протокола аукциона
	private $WhsDocumentSupplyType_id;//Тип поставки
	private $WhsDocumentSupply_BegDate;//Дата начала действия
	private $WhsDocumentSupply_ExecDate;//Дата исполнения обязательств Поставщиком
	private $DrugFinance_id;//Источник финансирования
	private $WhsDocumentCostItemType_id;//Статья расходов
	private $BudgetFormType_id;//Целевая статья
	private $WhsDocumentPurchType_id;//Вид закупа
	private $pmUser_id;//Идентификатор пользователя системы Промед
	private $WhsDocumentStatusType_id;//Статус документа (вычисляемое свойство)
	private $FinanceSource_id;//Источник оплаты
	private $DrugNds_id;//Ставка НДС

	/**
	 * Получение значения
	 */
	public function getWhsDocumentUc_pid() { return $this->WhsDocumentUc_pid;}

	/**
	 * Установка значения
	 */
	public function setWhsDocumentUc_pid($value) { $this->WhsDocumentUc_pid = $value; }

	/**
	 * Получение значения
	 */
	public function getWhsDocumentUc_Num() { return $this->WhsDocumentUc_Num;}

	/**
	 * Установка значения
	 */
	public function setWhsDocumentUc_Num($value) { $this->WhsDocumentUc_Num = $value; }

	/**
	 * Получение значения
	 */
	public function getWhsDocumentUc_Name() { return $this->WhsDocumentUc_Name;}

	/**
	 * Установка значения
	 */
	public function setWhsDocumentUc_Name($value) { $this->WhsDocumentUc_Name = $value; }

	/**
	 * Получение значения
	 */
	public function getWhsDocumentType_id() { return $this->WhsDocumentType_id;}

	/**
	 * Установка значения
	 */
	public function setWhsDocumentType_id($value) { $this->WhsDocumentType_id = $value; }

	/**
	 * Получение значения
	 */
	public function getWhsDocumentUc_Date() { return $this->WhsDocumentUc_Date;}

	/**
	 * Установка значения
	 */
	public function setWhsDocumentUc_Date($value) { $this->WhsDocumentUc_Date = $value; }

	/**
	 * Получение значения
	 */
	public function getOrg_aid() { return $this->Org_aid;}

	/**
	 * Установка значения
	 */
	public function setOrg_aid($value) { $this->Org_aid = $value; }

	/**
	 * Получение значения
	 */
	public function getOrg_sid() { return $this->Org_sid;}

	/**
	 * Установка значения
	 */
	public function setOrg_sid($value) { $this->Org_sid = $value; }

	/**
	 * Получение значения
	 */
	public function getOrg_cid() { return $this->Org_cid;}

	/**
	 * Установка значения
	 */
	public function setOrg_cid($value) { $this->Org_cid = $value; }

	/**
	 * Получение значения
	 */
	public function getOrg_pid() { return $this->Org_pid;}

	/**
	 * Установка значения
	 */
	public function setOrg_pid($value) { $this->Org_pid = $value; }

	/**
	 * Получение значения
	 */
	public function getOrg_rid() { return $this->Org_rid;}

	/**
	 * Установка значения
	 */
	public function setOrg_rid($value) { $this->Org_rid = $value; }

	/**
	 * Получение значения
	 */
	public function getWhsDocumentUc_Sum() { return $this->WhsDocumentUc_Sum;}

	/**
	 * Установка значения
	 */
	public function setWhsDocumentUc_Sum($value) { $this->WhsDocumentUc_Sum = $value; }

	/**
	 * Получение значения
	 */
	public function getWhsDocumentSupply_id() { return $this->WhsDocumentSupply_id;}

	/**
	 * Установка значения
	 */
	public function setWhsDocumentSupply_id($value) { $this->WhsDocumentSupply_id = $value; }

	/**
	 * Получение значения
	 */
	public function getWhsDocumentUc_id() { return $this->WhsDocumentUc_id;}

	/**
	 * Установка значения
	 */
	public function setWhsDocumentUc_id($value) { $this->WhsDocumentUc_id = $value; }

	/**
	 * Получение значения
	 */
	public function getWhsDocumentSupply_ProtNum() { return $this->WhsDocumentSupply_ProtNum;}

	/**
	 * Установка значения
	 */
	public function setWhsDocumentSupply_ProtNum($value) { $this->WhsDocumentSupply_ProtNum = $value; }

	/**
	 * Получение значения
	 */
	public function getWhsDocumentSupply_ProtDate() { return $this->WhsDocumentSupply_ProtDate;}

	/**
	 * Установка значения
	 */
	public function setWhsDocumentSupply_ProtDate($value) { $this->WhsDocumentSupply_ProtDate = $value; }

	/**
	 * Получение значения
	 */
	public function getWhsDocumentSupplyType_id() { return $this->WhsDocumentSupplyType_id;}

	/**
	 * Установка значения
	 */
	public function setWhsDocumentSupplyType_id($value) { $this->WhsDocumentSupplyType_id = $value; }

	/**
	 * Получение значения
	 */
	public function getWhsDocumentSupply_BegDate() { return $this->WhsDocumentSupply_BegDate;}

	/**
	 * Установка значения
	 */
	public function setWhsDocumentSupply_BegDate($value) { $this->WhsDocumentSupply_BegDate = $value; }

	/**
	 * Получение значения
	 */
	public function getWhsDocumentSupply_ExecDate() { return $this->WhsDocumentSupply_ExecDate;}

	/**
	 * Установка значения
	 */
	public function setWhsDocumentSupply_ExecDate($value) { $this->WhsDocumentSupply_ExecDate = $value; }

	/**
	 * Получение значения
	 */
	public function getDrugFinance_id() { return $this->DrugFinance_id;}

	/**
	 * Установка значения
	 */
	public function setDrugFinance_id($value) { $this->DrugFinance_id = $value; }

	/**
	 * Получение значения
	 */
	public function getWhsDocumentCostItemType_id() { return $this->WhsDocumentCostItemType_id;}

	/**
	 * Установка значения
	 */
	public function setWhsDocumentCostItemType_id($value) { $this->WhsDocumentCostItemType_id = $value; }

	/**
	 * Получение значения
	 */
	public function getBudgetFormType_id() { return $this->BudgetFormType_id;}

	/**
	 * Установка значения
	 */
	public function setBudgetFormType_id($value) { $this->BudgetFormType_id = $value; }

	/**
	 * Получение значения
	 */
	public function getWhsDocumentPurchType_id() { return $this->WhsDocumentPurchType_id;}

	/**
	 * Установка значения
	 */
	public function setWhsDocumentPurchType_id($value) { $this->WhsDocumentPurchType_id = $value; }

	/**
	 * Получение значения
	 */
	public function getpmUser_id() { return $this->pmUser_id;}

	/**
	 * Установка значения
	 */
	public function setpmUser_id($value) { $this->pmUser_id = $value; }

	/**
	 * Получение значения
	 */
	public function getWhsDocumentStatusType_id() { return $this->WhsDocumentStatusType_id;}


	/**
	 * Установка значения
	 */
	public function setWhsDocumentStatusType_id($value) { $this->WhsDocumentStatusType_id = $value;}

	/**
	 * Получение значения
	 */
	public function getFinanceSource_id() { return $this->FinanceSource_id;}

	/**
	 * Установка значения
	 */
	public function setFinanceSource_id($value) { $this->FinanceSource_id = $value; }

	/**
	 * Получение значения
	 */
	public function getDrugNds_id() { return $this->DrugNds_id;}

	/**
	 * Установка значения
	 */
	public function setDrugNds_id($value) { $this->DrugNds_id = $value; }

	/**
	 * Конструктор
	 */
	function __construct(){
		if (isset($_SESSION['pmuser_id'])) {
			$this->setpmUser_id($_SESSION['pmuser_id']);
		} else {
			throw new Exception('Значение pmuser_id не установлено в текущей сессии (не выполнен вход в Промед?)');
		}

        //установка региональной схемы
        $config = get_config();
        $this->schema = $config['regions'][getRegionNumber()]['schema'];
	}

	/**
	 * Подписание ГК
	 */
	function sign($data) {
        $suppliers_ostat_control = !empty($data['options']['drugcontrol']['suppliers_ostat_control']);

		// Стартуем транзакцию
		$this->db->trans_begin();

		$supply_data = array();
		$org_sid = null; // идентификатор организации поставщика
		$type_code = null; // код типа ГК

		// Получаем идентификатор организации соответствующей минздраву
		$mzorg_id = $this->getMinzdravDloOrgId();
		
		// Получаем информацию о ГК
		$query = "
			select
				wds.WhsDocumentSupply_id,
				wds.WhsDocumentType_id,
				wds.WhsDocumentUc_id,
				wds.WhsDocumentUc_Num,
				convert(varchar(10), wds.WhsDocumentUc_Date, 104) WhsDocumentUc_Date,
				wds.WhsDocumentStatusType_id,
				wdst.WhsDocumentStatusType_Name,
				wds.Org_sid,
				wds.Org_pid,
				wds.Org_rid,
				wds.Org_cid,
				ot_r.OrgType_id as OrgType_rCode,
				wdt.WhsDocumentType_Code
			from
				v_WhsDocumentSupply wds with (nolock)
				left join v_WhsDocumentStatusType wdst with (nolock) on wdst.WhsDocumentStatusType_id = wds.WhsDocumentStatusType_id
				left join v_WhsDocumentType wdt with (nolock) on wdt.WhsDocumentType_id = wds.WhsDocumentType_id
				left join v_Org o_r with (nolock) on  o_r.Org_id = wds.Org_rid
				left join v_OrgType ot_r with (nolock) on ot_r.OrgType_id = o_r.OrgType_id
			where
				wds.WhsDocumentSupply_id = :WhsDocumentSupply_id
		";
		
		$queryParams = array(
			'WhsDocumentSupply_id' => $data['WhsDocumentSupply_id']
		);
		
		$result = $this->db->query($query, $queryParams);
		if ( is_object($result) ) {
			// если WhsDocumentType_id = 3 (Контракт на поставку) или WhsDocumentType_id = 6 (Контракт на поставку и отпуск), то need_xp_DrugOstatRegistry = true
			$WhsDocumentSupply = $result->result('array');
			if (empty($WhsDocumentSupply[0]['WhsDocumentSupply_id'])) {
				$this->db->trans_rollback();
				return array(0 => array('Error_Msg' => 'Ошибка получения данных о ГК'));
			} else {
				if (!empty($WhsDocumentSupply[0]['WhsDocumentStatusType_id']) && !in_array($WhsDocumentSupply[0]['WhsDocumentStatusType_id'], array(1))) {
					$this->db->trans_rollback();
					return array(0 => array('Error_Msg' => 'Нельзя подписать документ со статусом '.$WhsDocumentSupply[0]['WhsDocumentStatusType_Name']));
				}

				$supply_data = $WhsDocumentSupply[0];
				$org_sid = $WhsDocumentSupply[0]['Org_sid'];
				$type_code = $WhsDocumentSupply[0]['WhsDocumentType_Code'];
			}
		} else {
			$this->db->trans_rollback();
			return array(0 => array('Error_Msg' => 'Ошибка запроса данных о ГК'));
		}
		
		// Обновляем статус документа
		$query = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec p_WhsDocumentUc_sign
				@WhsDocumentUc_id = :WhsDocumentUc_id,
				@WhsDocumentStatusType_id = 2,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		
		$queryParams = array(
			'WhsDocumentUc_id' => $WhsDocumentSupply[0]['WhsDocumentUc_id'],
			'pmUser_id' => $data['pmUser_id']
		);
		
		$result = $this->db->query($query, $queryParams);
		if (is_object($result)) {
			$WhsDocumentUcStatus = $result->result('array');
			if (!empty($WhsDocumentUcStatus[0]['Error_Msg'])) {
				$this->db->trans_rollback();
				return array(0 => array('Error_Msg' => 'Ошибка обновления статуса документа'));
			}
		} else {
			$this->db->trans_rollback();
			return array(0 => array('Error_Msg' => 'Ошибка запроса обновления статуса документа'));
		}

        //определяем для каких организаций нужно создать остатки
        $org_array = array();

        //создаются остатки поставщика, если задано настройками
        if ($suppliers_ostat_control && !empty($supply_data['Org_sid'])) {
            $org_array[] = $supply_data['Org_sid'];
        }

        //создаются остатки Минздрава, если заказчик или плательщик является Минздравом
        if (($supply_data['Org_cid'] == $mzorg_id || $supply_data['Org_cid'] == $mzorg_id) && !empty($mzorg_id)) {
            $org_array[] = $mzorg_id;
        }

        $org_array = array_unique($org_array);

		if (in_array($type_code, array(3, 6)) && count($org_array) > 0) { //дальнейшие действия производятся только для типов "Контракт на поставку" и "Контракт на поставку и отпуск", а также при условии что список организаций для которых нужно создать остатки не пуст
			//генерация уникального имени партии
            $ds_name = null;
            $query = "
                select
                    count(DrugShipment_id) as cnt
                from
                    DrugShipment ds with (nolock)
                where
                    DrugShipment_Name = :DrugShipment_Name;
            ";
            for($i = 0; $i < 5; $i++) {
                $tmp_ds_name = $WhsDocumentSupply[0]['WhsDocumentUc_Num'].($i > 0 ? '/'.$i : '').' от '.$WhsDocumentSupply[0]['WhsDocumentUc_Date'];
                $ds_count = $this->getFirstResultFromQuery($query, array(
                    'DrugShipment_Name' => $tmp_ds_name
                ));
                if ($ds_count == 0) {
                    $ds_name = $tmp_ds_name;
                    break;
                }
            }
            if (empty($ds_name)) {
                $ds_name = $WhsDocumentSupply[0]['WhsDocumentUc_Num'].'/'.$data['WhsDocumentSupply_id'].' от '.$WhsDocumentSupply[0]['WhsDocumentUc_Date'];
            }

            // 1) Создать партию по указанному ГК (dbo.DrugShipment)
			$query = "
				declare
					@Res bigint,
					@ErrCode int,
					@ErrMessage varchar(4000),
					@datetime datetime;
				set @datetime = dbo.tzGetDate();
				exec p_DrugShipment_ins
					@DrugShipment_id = @Res output,
					@DrugShipment_setDT = @datetime,
					@DrugShipment_Name = :DrugShipment_Name,
					@WhsDocumentSupply_id = :WhsDocumentSupply_id,
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMessage output;
				select @Res as DrugShipment_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			";

			$queryParams = array(
				'WhsDocumentSupply_id' => $data['WhsDocumentSupply_id'],
				'DrugShipment_Name' => $ds_name,
				'pmUser_id' => $data['pmUser_id']
			);

			$result = $this->db->query($query, $queryParams);

			if (is_object($result)) {
				$DrugShipment = $result->result('array');
				if (empty($DrugShipment[0]['DrugShipment_id'])) {
					$this->db->trans_rollback();
					return array(0 => array('Error_Msg' => 'Ошибка создания партии по ГК'));
				}
			} else {
				$this->db->trans_rollback();
				return array(0 => array('Error_Msg' => 'Ошибка запроса создания партии по ГК'));
			}

			// 2) Сформировать регистр остатков по субсчету Доступно
			// получаем строки из WhsDocumentSupplySpec
			$query = "
				select
					wdss.Drug_id,
					wdss.Okei_id,
					wdss.WhsDocumentSupplySpec_KolvoUnit,
					wdss.WhsDocumentSupplySpec_SumNDS,
					wdss.WhsDocumentSupplySpec_PriceNDS
				from
					v_WhsDocumentSupplySpec wdss with (nolock)
				where
					wdss.WhsDocumentSupply_id = :WhsDocumentSupply_id
			";

			$result = $this->db->query($query, $queryParams);
			if ( is_object($result) ) {
				$WhsDocumentSupplySpec = $result->result('array');
			} else {
			    $this->db->trans_rollback();
				return array(0 => array('Error_Msg' => 'Ошибка запроса получения данных из спецификации документа'));
			}

			// для каждой строки WhsDocumentSupplySpec вызываем xp_DrugOstatRegistry_count
			foreach ($WhsDocumentSupplySpec as $WhsDocumentSupplySpecOne) {

				if (empty($WhsDocumentSupplySpecOne['Drug_id'])) {
					$this->db->trans_rollback();
					return array(0 => array('Error_Msg' => 'Подписание не возможно, т.к. не указан медикамент'));
				}

				$query = "
					declare
						@ErrCode int,
						@ErrMessage varchar(4000);
					exec xp_DrugOstatRegistry_count
						@Contragent_id = NULL,
						@Org_id = :Org_id,
						@DrugShipment_id = :DrugShipment_id,
						@Drug_id = :Drug_id,
						@PrepSeries_id = NULL, -- используется в складском учете. При учете обязательств по контрактам не используется.
						@SubAccountType_id = 1, -- субсчёт доступно
						@Okei_id = :Okei_id,
						@DrugOstatRegistry_Kolvo = :DrugOstatRegistry_Kolvo,
						@DrugOstatRegistry_Sum = :DrugOstatRegistry_Sum,
						@DrugOstatRegistry_Cost = :DrugOstatRegistry_Cost,
						@pmUser_id = :pmUser_id,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMessage output;
					select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
				";

				$queryParams = array(
					'DrugShipment_id' => $DrugShipment[0]['DrugShipment_id'],
					'Drug_id' => $WhsDocumentSupplySpecOne['Drug_id'],
					'Okei_id' => $WhsDocumentSupplySpecOne['Okei_id'],
					'DrugOstatRegistry_Kolvo' => $WhsDocumentSupplySpecOne['WhsDocumentSupplySpec_KolvoUnit'],
					'DrugOstatRegistry_Sum' => $WhsDocumentSupplySpecOne['WhsDocumentSupplySpec_SumNDS'],
					'DrugOstatRegistry_Cost' => $WhsDocumentSupplySpecOne['WhsDocumentSupplySpec_PriceNDS'],
					'pmUser_id' => $data['pmUser_id']
				);

                //создаем остатки для организаций
                foreach($org_array as $org_id) {
                    $queryParams['Org_id'] = $org_id;
                    $result = $this->db->query($query, $queryParams);
                    if (is_object($result)) {
                        $DrugOstatRegistry = $result->result('array');
                        if (!empty($DrugOstatRegistry[0]['Error_Msg'])) {
                            $this->db->trans_rollback();
                            return array(0 => array('Error_Msg' => 'Ошибка создания регистра остатков'));
                        }
                    } else {
                        $this->db->trans_rollback();
                        return array(0 => array('Error_Msg' => 'Ошибка запроса создания регистра остатков'));
                    }
                }
			}
		}

		//если поставщик еще не зарегистрирован в системе как контрагент, добавляем новый контрагент
		$result = $this->saveContragent(array(
			'Org_id' => $supply_data['Org_sid'],
			'ContragentType_Code' => 1,
			'Server_id' => $data['Server_id'],
			'pmUser' => $data['pmUser_id']
		));
		if (empty($result['Contragent_id']) || !empty($result['Error_Msg'])) {
			$this->db->trans_rollback();
			return array(array('Error_Msg' => !empty($result['Error_Msg']) ? $result['Error_Msg'] : 'Ошибка сохранения контрагента'));
		} else {
			$result = $this->saveContragentOrg(array(
				'Org_id' => $supply_data['Org_pid'],
				'Contragent_id' => $result['Contragent_id'],
				'pmUser' => $data['pmUser_id']
			));
			if (!empty($result['Error_Msg'])) {
				$this->db->trans_rollback();
				return array(array('Error_Msg' => $result['Error_Msg']));
			}
		}

		if ($mzorg_id > 0 && $supply_data['Org_pid'] == $mzorg_id && $supply_data['OrgType_rCode'] == 5) { //если плательщик - минздрав, а получатель - РАС
			$result = $this->saveContragent(array(
				'Org_id' => $supply_data['Org_rid'],
				'ContragentType_Code' => 6, //6 - Региональный склад
				'Server_id' => $data['Server_id'],
				'pmUser' => $data['pmUser_id']
			));
			if (empty($result['Contragent_id']) || !empty($result['Error_Msg'])) {
				$this->db->trans_rollback();
				return array(array('Error_Msg' => !empty($result['Error_Msg']) ? $result['Error_Msg'] : 'Ошибка сохранения контрагента'));
			} else {
				$result = $this->saveContragentOrg(array(
					'Org_id' => $mzorg_id,
					'Contragent_id' => $result['Contragent_id'],
					'pmUser' => $data['pmUser_id']
				));
				if (!empty($result['Error_Msg'])) {
					$this->db->trans_rollback();
					return array(array('Error_Msg' => $result['Error_Msg']));
				}
			}
		}

		$this->db->trans_commit();
		return array(array('Error_Msg' => ''));
	}

	/**
	 * Снятие подписания с ГК
	 */
	function unsign($data) {
        //$suppliers_ostat_control = !empty($data['options']['drugcontrol']['suppliers_ostat_control']);
        $error = array();
        $supply_data = array();

		// Стартуем транзакцию
		$this->beginTransaction();

		//$supply_data = array();
		//$org_sid = null; // идентификатор организации поставщика
		//$type_code = null; // код типа ГК

		// Получаем идентификатор организации соответствующей минздраву
		//$mzorg_id = $this->getMinzdravDloOrgId();

		// Получаем информацию о ГК
		$query = "
			select
				wds.WhsDocumentSupply_id,
				wds.WhsDocumentType_id,
				wds.WhsDocumentUc_id,
				wds.WhsDocumentUc_Num,
				convert(varchar(10), wds.WhsDocumentUc_Date, 104) WhsDocumentUc_Date,
				wds.WhsDocumentStatusType_id,
				wdst.WhsDocumentStatusType_Name,
				wds.Org_sid,
				wds.Org_pid,
				wds.Org_rid,
				wds.Org_cid,
				ot_r.OrgType_id as OrgType_rCode,
				wdt.WhsDocumentType_Code
			from
				v_WhsDocumentSupply wds with (nolock)
				left join v_WhsDocumentStatusType wdst with (nolock) on wdst.WhsDocumentStatusType_id = wds.WhsDocumentStatusType_id
				left join v_WhsDocumentType wdt with (nolock) on wdt.WhsDocumentType_id = wds.WhsDocumentType_id
				left join v_Org o_r with (nolock) on  o_r.Org_id = wds.Org_rid
				left join v_OrgType ot_r with (nolock) on ot_r.OrgType_id = o_r.OrgType_id
			where
				wds.WhsDocumentSupply_id = :WhsDocumentSupply_id
		";
        $supply_data = $this->getFirstRowFromQuery($query, array(
            'WhsDocumentSupply_id' => $data['WhsDocumentSupply_id']
        ));

        if (count($supply_data) < 1) {
            $error[] = "Ошибка получения данных о ГК";
        }

        //прорверка статуса
        if (count($error) == 0 && $supply_data['WhsDocumentStatusType_id'] != 2) {
            $error[] = "Нельзя снять подписание с документа со статусом ".$supply_data['WhsDocumentStatusType_Name'];
        }

        //проверка наличия доп. соглашений, документов учета или разнарядок связанных с ГК
        if (count($error) == 0) {
            $query = "
                declare
                    @doc_cnt bigint = 0,
                    @str_cnt bigint = 0,
                    @alc_cnt bigint = 0,
                    @res_cnt bigint = 0,
                    @sup_cnt bigint = 0;

                set @doc_cnt = (
                    select
                        count(du.DocumentUc_id) as cnt
                    from
                        v_DocumentUc du with (nolock)
                    where
                        du.WhsDocumentUc_id = :WhsDocumentUc_id
                );

                set @str_cnt = (
                    select
                        count(dus.DocumentUcStr_id) as cnt
                    from
                        v_DrugShipment ds with (nolock)
                        left join v_DrugShipmentLink dsl with (nolock) on dsl.DrugShipment_id = ds.DrugShipment_id
                        inner join v_DocumentUcStr dus with (nolock) on dus.DocumentUcStr_id = dsl.DocumentUcStr_id
                    where
                        ds.WhsDocumentSupply_id = :WhsDocumentSupply_id
                );

                set @alc_cnt = (
                    select
                        count(wdoad.WhsDocumentOrderAllocationDrug_id) as cnt
                    from
                        v_WhsDocumentOrderAllocationDrug wdoad with (nolock)
                    where
                        wdoad.WhsDocumentUc_pid = :WhsDocumentUc_id
                );

                set @res_cnt = (
                    select
                        count(wdord.WhsDocumentOrderReserveDrug_id) as cnt
                    from
                        v_WhsDocumentOrderReserveDrug wdord with (nolock)
                    where
                        wdord.WhsDocumentUc_pid = :WhsDocumentUc_id
                );

                set @sup_cnt = (
                    select
                        count(wds.WhsDocumentSupply_id) as cnt
                    from
                        v_WhsDocumentSupply wds with (nolock)
                        left join v_WhsDocumentType wdt with (nolock) on wdt.WhsDocumentType_id = wds.WhsDocumentType_id
                    where
                        wds.WhsDocumentUc_pid = :WhsDocumentUc_id and
                        wdt.WhsDocumentType_Code = 13 -- 13 - Дополнительное соглашение
                );

                select
                    @doc_cnt as doc_cnt,
                    @str_cnt as str_cnt,
                    @alc_cnt as alc_cnt,
                    @res_cnt as res_cnt,
                    @sup_cnt as sup_cnt;
            ";
            $response = $this->getFirstRowFromQuery($query, array(
                'WhsDocumentUc_id' => $supply_data['WhsDocumentUc_id'],
                'WhsDocumentSupply_id' => $supply_data['WhsDocumentSupply_id']
            ));

            if (!empty($response['doc_cnt']) || !empty($response['str_cnt'])) {
                $error[] = "Снять подпись с контракта не возможно, т.к. есть документы связанные с этим контрактом";
            }

            if (!empty($response['alc_cnt'])) {
                $error[] = "Снять подпись с контракта не возможно, т.к. есть разнарядки связанные с этим контрактом.";
            }

            if (!empty($response['res_cnt'])) {
                $error[] = "Снять подпись с контракта не возможно, т.к. есть распоряжения на включение в резерв связанные с этим контрактом";
            }

            if (!empty($response['sup_cnt'])) {
                $error[] = "К контракту заключены дополнительные соглашения, снять подпись с контракта не возможно";
            }
        }

        //получение списка остатков по партиям
        if (count($error) == 0) {
            $query = "
                select
                    dor.Org_id,
                    dor.DrugShipment_id,
                    dor.Drug_id,
                    dor.Okei_id,
                    dor.DrugOstatRegistry_Kolvo,
                    dor.DrugOstatRegistry_Sum,
                    dor.DrugOstatRegistry_Cost
                from
                    v_DrugShipment ds with (nolock)
                    inner join v_DrugOstatRegistry dor with (nolock) on dor.DrugShipment_id = ds.DrugShipment_id
                    left join v_SubAccountType sat with (nolock) on sat.SubAccountType_id = dor.SubAccountType_id
                where
                    ds.WhsDocumentSupply_id = :WhsDocumentSupply_id and
                    sat.SubAccountType_Code = 1 and -- 1 - Доступно
                    dor.DrugOstatRegistry_Kolvo > 0
            ";
            $drug_array = $this->queryResult($query, array(
                'WhsDocumentSupply_id' => $supply_data['WhsDocumentSupply_id']
            ));
        }

        //перерасчет остатков
        if (count($error) == 0 && is_array($drug_array)) {
            foreach ($drug_array as $drug_data) {
                $query = "
                    declare
                        @ErrCode int,
                        @ErrMessage varchar(4000);
                    exec xp_DrugOstatRegistry_count
                        @Contragent_id = NULL,
                        @Org_id = :Org_id,
                        @DrugShipment_id = :DrugShipment_id,
                        @Drug_id = :Drug_id,
                        @PrepSeries_id = NULL, -- используется в складском учете. При учете обязательств по контрактам не используется.
                        @SubAccountType_id = 1, -- субсчёт доступно
                        @Okei_id = :Okei_id,
                        @DrugOstatRegistry_Kolvo = :DrugOstatRegistry_Kolvo,
                        @DrugOstatRegistry_Sum = :DrugOstatRegistry_Sum,
                        @DrugOstatRegistry_Cost = :DrugOstatRegistry_Cost,
                        @pmUser_id = :pmUser_id,
                        @Error_Code = @ErrCode output,
                        @Error_Message = @ErrMessage output;
                    select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
                ";
                $response = $this->getFirstRowFromQuery($query, array(
                    'Org_id' => $drug_data['Org_id'],
                    'DrugShipment_id' => $drug_data['DrugShipment_id'],
                    'Drug_id' => $drug_data['Drug_id'],
                    'Okei_id' => $drug_data['Okei_id'],
                    'DrugOstatRegistry_Kolvo' => $drug_data['DrugOstatRegistry_Kolvo']*(-1),
                    'DrugOstatRegistry_Sum' => $drug_data['DrugOstatRegistry_Sum']*(-1),
                    'DrugOstatRegistry_Cost' => $drug_data['DrugOstatRegistry_Cost'],
                    'pmUser_id' => $data['pmUser_id']
                ));
                if (!empty($response['Error_Msg'])) {
                    $error[] = $response['Error_Msg'];
                }
            }
        }


        // Обновляем статус документа
        if (count($error) == 0) {
            $save_result = $this->saveObject('WhsDocumentUc', array(
                'WhsDocumentUc_id' => $supply_data['WhsDocumentUc_id'],
                'WhsDocumentStatusType_id' => 1, //1 - Новый
                'pmUser_id' => $data['pmUser_id']
            ));

            if (empty($save_result['WhsDocumentUc_id'])) {
                $error[] = "Ошибка запроса обновления статуса документа";
            } else if (!empty($save_result['Error_Msg'])) {
                $error[] = $save_result['Error_Msg'];
            }
        }

        $result = array();

        if (count($error) > 0) {
            $result = array('Error_Msg' => $error[0]);
            $this->rollbackTransaction();
        } else {
            $result = array('Error_Msg' => null);
            $this->commitTransaction();
        }

        return array($result);
	}

	/**
	 * Подписание дополнительного соглашения
	 */
	function signWhsDocumentSupplyAdditional($data) {
		$result = array(
			'Error_Msg' => null
		);
        $ost_edit_enabled = false; //по умолчанию изменение регистра остатков отключено
		$suppliers_ostat_control = !empty($data['options']['drugcontrol']['suppliers_ostat_control']);

		$sat_id = $this->getObjectIdByCode('SubAccountType', 1); // 1 - Доступно
		$okei_id = $this->getObjectIdByCode('Okei', 778); // 778 - Упаковка

        // Получаем идентификатор организации соответствующей минздраву
        $mzorg_id = $this->getMinzdravDloOrgId();

		// Стартуем транзакцию
		$this->beginTransaction();

        try {
			// получение информации о доп. соглашении
			$query = "
				select
					wds.WhsDocumentSupply_id,
					wds.WhsDocumentUc_id,
					p_wds.WhsDocumentSupply_id as ParentWhsDocumentSupply_id,
					p_wds.WhsDocumentUc_pid as ParentWhsDocumentUc_pid,
					ds.DrugShipment_id,
					wdst.WhsDocumentStatusType_Code,
					(
						case
							when p_wdt.WhsDocumentType_Code = 3 then p_wds.Org_sid
							else p_wds.Org_cid
						end
					) as Org_id, -- если ГК на поставку, то по лицевому счету Поставщика; ГК на поставку и отпуск, то по лицевому счету Минздрава
					p_wds.Org_sid, -- поставщик (родительский контракт)
					p_wds.Org_cid -- заказчик (родительский контракт)
				from
					v_WhsDocumentSupply wds with (nolock)
					left join v_WhsDocumentSupply p_wds with (nolock) on p_wds.WhsDocumentUc_id = wds.WhsDocumentUc_pid
					left join v_WhsDocumentType p_wdt with (nolock) on p_wdt.WhsDocumentType_id = p_wds.WhsDocumentType_id
					left join v_WhsDocumentStatusType wdst with (nolock) on wdst.WhsDocumentStatusType_id = wds.WhsDocumentStatusType_id
					outer apply (
						select top 1
							i_ds.DrugShipment_id
						from
							v_DrugShipment i_ds with (nolock)
						where
							i_ds.WhsDocumentSupply_id = p_wds.WhsDocumentSupply_id
						order by
							DrugShipment_id desc
					) ds
				where
					wds.WhsDocumentSupply_id = :WhsDocumentSupply_id
			";
			$supply_data = $this->getFirstRowFromQuery($query,  array(
				'WhsDocumentSupply_id' => $data['WhsDocumentSupply_id']
			));
			if (!is_array($supply_data) || count($supply_data) < 1) {
				throw new Exception('Ошибка получения данных о дополнительном соглашении');
			} else if ($supply_data['Org_cid'] == $mzorg_id) { //если заказчик по контракту Минздрав региона, то при исполнении доп. соглашения, производится перерасчет остатков
				$ost_edit_enabled = true;
			}

			//определяем для каких организаций нужно отредактировать остатки
			$org_array = array();

			//редактируются остатки поставщика, если задано настройками
			if ($suppliers_ostat_control && !empty($supply_data['Org_sid'])) {
				$org_array[] = $supply_data['Org_sid'];
			}

			//редактируются остатки Минздрава, если заказчик или плательщик является Минздравом
			if (($supply_data['Org_cid'] == $mzorg_id || $supply_data['Org_cid'] == $mzorg_id) && !empty($mzorg_id)) {
				$org_array[] = $mzorg_id;
			}

			$org_array = array_unique($org_array);

			if (empty($supply_data['DrugShipment_id'])) {
				throw new Exception('Не найдена партия для родительского контракта');
			}

			if ($supply_data['WhsDocumentStatusType_Code'] == 2) {
				throw new Exception('Документ уже подписан');
			}

			// обработка списка организаций
			foreach($org_array as $org_id) {
				// получение списка различий между спецификациями контракта и доп. соглашения, также получение даныых об остатках
				$query = "
					with wdss as (
						select
							Drug_id,
							WhsDocumentSupplySpec_PriceNDS as Price,
							sum(WhsDocumentSupplySpec_KolvoUnit) as Kolvo
						from
							v_WhsDocumentSupplySpec wdss with (nolock)
						where
							wdss.WhsDocumentSupply_id = :WhsDocumentSupply_id
						group by
							Drug_id, WhsDocumentSupplySpec_PriceNDS
					),
					p_wdss as (
						select
							Drug_id,
							WhsDocumentSupplySpec_PriceNDS as Price,
							sum(WhsDocumentSupplySpec_KolvoUnit) as Kolvo
						from
							v_WhsDocumentSupplySpec with (nolock)
						where
							WhsDocumentSupply_id = :ParentWhsDocumentSupply_id
						group by
							Drug_id, WhsDocumentSupplySpec_PriceNDS
					)
					select
						isnull(wdss.Drug_id, p_wdss.Drug_id) as Drug_id,
						isnull(wdss.Price, p_wdss.Price) as Price,
						(isnull(wdss.Kolvo, 0) - isnull(p_wdss.Kolvo, 0)) as Kolvo,
						(isnull(dor.DrugOstatRegistry_Kolvo, 0) + isnull(wdss.Kolvo, 0) - isnull(p_wdss.Kolvo, 0)) as CheckKolvo,
						dor.Contragent_id,
						dor.Storage_id,
						dor.PrepSeries_id,
						dor.Okei_id
					from
						wdss
						full outer join p_wdss on wdss.Drug_id = p_wdss.Drug_id and wdss.Price = p_wdss.Price
						outer apply (
							select top 1
								i_dor.DrugOstatRegistry_Kolvo,
								i_dor.Contragent_id,
								i_dor.Storage_id,
								i_dor.PrepSeries_id,
								i_dor.Okei_id
							from
								v_DrugOstatRegistry i_dor with (nolock)
							where
								i_dor.SubAccountType_id = :SubAccountType_id and
								i_dor.DrugShipment_id = :DrugShipment_id and
								i_dor.Drug_id = p_wdss.Drug_id and
								i_dor.DrugOstatRegistry_Cost = p_wdss.Price and
								i_dor.Org_id = :Org_id
						) dor
					where
						isnull(wdss.Kolvo, 0) <> isnull(p_wdss.Kolvo, 0);
				";
				$diff_array = $this->queryResult($query, array(
					'WhsDocumentSupply_id' => $data['WhsDocumentSupply_id'],
					'ParentWhsDocumentSupply_id' => $supply_data['ParentWhsDocumentSupply_id'],
					'SubAccountType_id' => $sat_id,
					'DrugShipment_id' => $supply_data['DrugShipment_id'],
					'Org_id' => $org_id
				));

				//проверка возможности внесения изменений в регистр остатков
				if ($ost_edit_enabled) {
					foreach($diff_array as $diff) {
						if (!empty($diff['CheckKolvo']) && $diff['CheckKolvo'] < 0) {
							$err_msg = "Исполнение дополнительного соглашения невозможно: ЛС, исключаемые из контракта уже ";
							$err_msg .= ($org_id == $mzorg_id ? "выданы в разнарядку на поставку." : "поставлены.");
							throw new Exception($err_msg);
							break;
						}
					}
				}

				//редактирование регистра остатков
				if ($ost_edit_enabled) {
					foreach($diff_array as $diff) {
						$query = "
							declare
								@Error_Code int,
								@Error_Message varchar(4000);
							exec xp_DrugOstatRegistry_count
								@Contragent_id = :Contragent_id,
								@Org_id = :Org_id,
								@Storage_id = null,
								@DrugShipment_id = :DrugShipment_id,
								@Drug_id = :Drug_id,
								@PrepSeries_id = :PrepSeries_id,
								@SubAccountType_id = :SubAccountType_id,
								@Okei_id = :Okei_id,
								@DrugOstatRegistry_Kolvo = :DrugOstatRegistry_Kolvo,
								@DrugOstatRegistry_Sum = :DrugOstatRegistry_Sum,
								@DrugOstatRegistry_Cost = :DrugOstatRegistry_Cost,
								@pmUser_id = :pmUser_id,
								@Error_Code = @Error_Code output,
								@Error_Message = @Error_Message output;
		
							select @Error_Code as Error_Code, @Error_Message as Error_Msg;
						";
						$query_result = $this->getFirstRowFromQuery($query, array(
							'Contragent_id' => $diff['Contragent_id'],
							'Org_id' => $org_id,
							'DrugShipment_id' => $supply_data['DrugShipment_id'],
							'Drug_id' => $diff['Drug_id'],
							'PrepSeries_id' => $diff['PrepSeries_id'],
							'SubAccountType_id' => $sat_id,
							'Okei_id' => !empty($diff['Okei_id']) ? $diff['Okei_id'] : $okei_id,
							'DrugOstatRegistry_Kolvo' => $diff['Kolvo'],
							'DrugOstatRegistry_Sum' => $diff['Kolvo']*$diff['Price'],
							'DrugOstatRegistry_Cost' => $diff['Price'],
							'pmUser_id' => $data['pmUser_id']
						));
						if ($query_result === false || !empty($query_result['Error_Msg'])) {
							throw new Exception('При рассчете остатков произошла ошибка.');
							break;
						}
					}
				}

			}

			//получение данных спецификации контракта
			$query = "
				select
					wdss.WhsDocumentSupplySpec_id as OldSpec_id,
					add_wdss.WhsDocumentSupplySpec_id as DopSpec_id
				from
					v_WhsDocumentSupplySpec wdss with (nolock)
					outer apply (
						select top 1
							WhsDocumentSupplySpec_id
						from
							v_WhsDocumentSupplySpec i_wdss with (nolock)
						where
							i_wdss.WhsDocumentSupply_id = :WhsDocumentSupply_id and
							i_wdss.Drug_id = wdss.Drug_id and
							isnull(i_wdss.WhsDocumentSupplySpec_PriceNDS, 0) = isnull(wdss.WhsDocumentSupplySpec_PriceNDS, 0)
						order by
							WhsDocumentSupplySpec_id
					) add_wdss
				where
					wdss.WhsDocumentSupply_id = :ParentWhsDocumentSupply_id;
			";
			$supply_spec = $this->queryResult($query, array(
				'ParentWhsDocumentSupply_id' => $supply_data['ParentWhsDocumentSupply_id'],
				'WhsDocumentSupply_id' => $supply_data['WhsDocumentSupply_id']
			));

			//получение данных спецификации доп. соглашения
			$query = "
				select
					WhsDocumentSupplySpec_id,
					Drug_id,
					WhsDocumentSupplySpec_Price,
					WhsDocumentSupplySpec_PriceNDS,
					WhsDocumentSupplySpec_SuppPrice
				from
					v_WhsDocumentSupplySpec with (nolock)
				where
					WhsDocumentSupply_id = :WhsDocumentSupply_id;
			";
			$additional_spec = $this->queryResult($query, array(
				'WhsDocumentSupply_id' => $supply_data['WhsDocumentSupply_id']
			));

			//сохранение изменений спецификации контракта в архиве
			foreach($additional_spec as $spec) {
				$this->saveObject('WhsDocumentUcPriceHistory', array(
					'WhsDocumentUcPriceHistory_id' => null,
					'WhsDocumentUc_id' => $supply_data['WhsDocumentUc_id'],
					'Drug_id' => $spec['Drug_id'],
					'WhsDocumentUcPriceHistory_Price' => $spec['WhsDocumentSupplySpec_Price'],
					'WhsDocumentUcPriceHistory_PriceNDS' => $spec['WhsDocumentSupplySpec_PriceNDS'],
					'WhsDocumentUc_sid' => $supply_data['ParentWhsDocumentUc_pid'],
					'WhsDocumentUcPriceHistory_SuppPrice' => $spec['WhsDocumentSupplySpec_SuppPrice'],
					'pmUser_id' => $data['pmUser_id']
				));
				if (!empty($response['Error_Msg'])) {
					throw new Exception($response['Error_Msg']);
				}
			}

			//перезапись спецификации контракта
			//копирование спецификации из доп соглашения а контракт
			foreach($additional_spec as $spec) {
				$response = $this->copyObject('WhsDocumentSupplySpec', array(
					'WhsDocumentSupplySpec_id' => $spec['WhsDocumentSupplySpec_id'],
					'WhsDocumentSupply_id' => $supply_data['ParentWhsDocumentSupply_id'],
					'pmUser_id' => $data['pmUser_id']
				));
				//сопоставляем идентификаторы новых строк ГК со старыми
				if (!empty($response['WhsDocumentSupplySpec_id'])) {
					foreach($supply_spec as $key => $s_spec) {
						if ($s_spec['DopSpec_id'] == $spec['WhsDocumentSupplySpec_id']) {
							$supply_spec[$key]['NewSpec_id'] = $response['WhsDocumentSupplySpec_id'];
						}
					}
				}
			}

			//удаляем старую спецификацию ГК, по возможности сохраняя график поставки
			foreach($supply_spec as $spec) {
				//удаление или редактирование строки графика поставки
				//удаление или редактирование списка синонимов
				if (!empty($spec['NewSpec_id']) && $spec['NewSpec_id'] > 0) {
					$query = "
						update
							WhsDocumentDelivery
						set
							WhsDocumentSupplySpec_id = :NewWhsDocumentSupplySpec_id,
							WhsDocumentDelivery_updDT = dbo.tzGetDate(),
							pmUser_updID = :pmUser_id
						where
							WhsDocumentSupplySpec_id = :WhsDocumentSupplySpec_id;
					";
					$query_result = $this->db->query($query, array(
						'WhsDocumentSupplySpec_id' => $spec['OldSpec_id'],
						'NewWhsDocumentSupplySpec_id' => $spec['NewSpec_id'],
						'pmUser_id' => $data['pmUser_id']
					));

					$query = "
						update
							WhsDocumentSupplySpecDrug
						set
							WhsDocumentSupplySpec_id = :NewWhsDocumentSupplySpec_id,
							WhsDocumentSupplySpecDrug_updDT = dbo.tzGetDate(),
							pmUser_updID = :pmUser_id
						where
							WhsDocumentSupplySpec_id = :WhsDocumentSupplySpec_id;
					";
					$query_result = $this->db->query($query, array(
						'WhsDocumentSupplySpec_id' => $spec['OldSpec_id'],
						'NewWhsDocumentSupplySpec_id' => $spec['NewSpec_id'],
						'pmUser_id' => $data['pmUser_id']
					));
				} else {
					$query = "
						delete from
							WhsDocumentDelivery with(rowlock)
						where
							WhsDocumentSupplySpec_id = :WhsDocumentSupplySpec_id;
					";
					$query_result = $this->db->query($query, array(
						'WhsDocumentSupplySpec_id' => $spec['OldSpec_id']
					));

					$query = "
						delete from
							WhsDocumentSupplySpecDrug with(rowlock)
						where
							WhsDocumentSupplySpec_id = :WhsDocumentSupplySpec_id;
					";
					$query_result = $this->db->query($query, array(
						'WhsDocumentSupplySpec_id' => $spec['OldSpec_id']
					));
				}

				//удаление строки из спецификации ГК
				$response = $this->deleteObject('WhsDocumentSupplySpec', array(
					'WhsDocumentSupplySpec_id' => $spec['OldSpec_id'],
					'pmUser_id' => $data['pmUser_id']
				));
				if (!empty($response['Error_Msg'])) {
					throw new Exception($response['Error_Msg']);
				}
			}

			//изменение статуса доп. соглашения
			$response = $this->SaveObject('WhsDocumentSupply', array(
				'WhsDocumentSupply_id' => $supply_data['WhsDocumentSupply_id'],
				'WhsDocumentStatusType_id' => $this->getObjectIdByCode('WhsDocumentStatusType', 2), //2 - Действующий
				'pmUser_id' => $data['pmUser_id']
			));
			if (!empty($response['Error_Msg'])) {
				throw new Exception($response['Error_Msg']);
			}

			//коммит транзакции
			$this->commitTransaction();
		} catch (Exception $e) {
			$result['Error_Msg'] = $e->getMessage();
			$this->rollbackTransaction();
		}

		return $result;
	}

	/**
	 * Загрузка данных ГК
	 */
	function load() {
		$q = "
			select
				wds.WhsDocumentUc_pid,
				wds.WhsDocumentUc_Num,
				wds.WhsDocumentUc_Name,
				wds.WhsDocumentType_id,
				wdt.WhsDocumentType_Name,
				wds.WhsDocumentUc_Date,
				wds.Org_sid,
				wds.Org_cid,
				wds.Org_pid,
				wds.Org_rid,
				cast(wds.WhsDocumentUc_Sum as decimal(16,2)) as WhsDocumentUc_Sum,
				wds.WhsDocumentSupply_id,
				wds.WhsDocumentUc_id,
				wds.WhsDocumentSupply_ProtNum,
				wds.WhsDocumentSupply_ProtDate,
				wds.WhsDocumentSupplyType_id,
				wds.WhsDocumentSupply_BegDate,
				wds.WhsDocumentSupply_ExecDate,
				wds.DrugFinance_id,
				wds.WhsDocumentCostItemType_id,
				wds.BudgetFormType_id,
				wds.FinanceSource_id,
				wds.WhsDocumentPurchType_id,
				ISNULL(wds.WhsDocumentStatusType_id, 1) as WhsDocumentStatusType_id,
				isnull(nds.Code, dnds.DrugNds_Code) as DrugNds_Code,
				cast((wds.WhsDocumentUc_Sum/(100+nds.Code))*nds.Code as decimal(16,2)) as Nds_Sum,
				wds.DrugNds_id,
				case when wdu.WhsDocumentUc_ImportDT is null then 'false' else 'true' end as ImportDT_Exists
			from
				v_WhsDocumentSupply wds with (nolock)
				left join WhsDocumentType wdt with (nolock) on wdt.WhsDocumentType_id = wds.WhsDocumentType_id
				left join v_DrugNds dnds with (nolock) on dnds.DrugNds_id = wds.DrugNds_id
				outer apply (
					select top 1
						cast(WhsDocumentSupplySpec_NDS as decimal(4,0)) as Code
					from
						v_WhsDocumentSupplySpec wdss with(nolock)
					where
						wdss.WhsDocumentSupply_id = wds.WhsDocumentSupply_id
				) nds
				left join v_WhsDocumentUc wdu (nolock) on wdu.WhsDocumentUc_id = wds.WhsDocumentUc_id
			where
				WhsDocumentSupply_id = :WhsDocumentSupply_id
		";
		//echo getDebugSQL($q, array('WhsDocumentSupply_id' => $this->WhsDocumentSupply_id));die;
		$r = $this->db->query($q, array('WhsDocumentSupply_id' => $this->WhsDocumentSupply_id));
		if ( is_object($r) ) {
			$r = $r->result('array');
			if (isset($r[0])) {
				$this->WhsDocumentUc_pid = $r[0]['WhsDocumentUc_pid'];
				$this->WhsDocumentUc_Num = $r[0]['WhsDocumentUc_Num'];
				$this->WhsDocumentUc_Name = $r[0]['WhsDocumentUc_Name'];
				$this->WhsDocumentType_id = $r[0]['WhsDocumentType_id'];
				$this->WhsDocumentUc_Date = $r[0]['WhsDocumentUc_Date'];
				$this->Org_sid = $r[0]['Org_sid'];
				$this->Org_cid = $r[0]['Org_cid'];
				$this->Org_pid = $r[0]['Org_pid'];
				$this->Org_rid = $r[0]['Org_rid'];
				$this->WhsDocumentUc_Sum = $r[0]['WhsDocumentUc_Sum'];
				$this->WhsDocumentSupply_id = $r[0]['WhsDocumentSupply_id'];
				$this->WhsDocumentUc_id = $r[0]['WhsDocumentUc_id'];
				$this->WhsDocumentSupply_ProtNum = $r[0]['WhsDocumentSupply_ProtNum'];
				$this->WhsDocumentSupply_ProtDate = $r[0]['WhsDocumentSupply_ProtDate'];
				$this->WhsDocumentSupplyType_id = $r[0]['WhsDocumentSupplyType_id'];
				$this->WhsDocumentSupply_BegDate = $r[0]['WhsDocumentSupply_BegDate'];
				$this->WhsDocumentSupply_ExecDate = $r[0]['WhsDocumentSupply_ExecDate'];
				$this->DrugFinance_id = $r[0]['DrugFinance_id'];
				$this->WhsDocumentCostItemType_id = $r[0]['WhsDocumentCostItemType_id'];
				$this->BudgetFormType_id = $r[0]['BudgetFormType_id'];
				$this->WhsDocumentPurchType_id = $r[0]['WhsDocumentPurchType_id'];
				$this->WhsDocumentStatusType_id = $r[0]['WhsDocumentStatusType_id'];
				$this->FinanceSource_id = $r[0]['FinanceSource_id'];
				$this->DrugNds_id = $r[0]['DrugNds_id'];
				return $r;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	/**
	 * Загрузка данных дополнительного соглашения
	 */
	function loadWhsDocumentSupplyAdditional($data) {
		$q = "
			select
				wds.WhsDocumentUc_id,
				wds.WhsDocumentUc_pid,
				wds.WhsDocumentUc_Num,
				wds.WhsDocumentUc_Name,
				wds.WhsDocumentType_id,
				wds.WhsDocumentUc_Date,
				wds.WhsDocumentSupply_id,
				wds.WhsDocumentUc_id,
				wds.WhsDocumentStatusType_id,
				wdst.WhsDocumentStatusType_Code,
				case when wdu.WhsDocumentUc_ImportDT is null then 'false' else 'true' end as ImportDT_Exists,
				ISNULL(convert(varchar(16), wdu.WhsDocumentUc_ImportDT, 120),'') as ImportDT
			from
				v_WhsDocumentSupply wds with (nolock)
				left join v_WhsDocumentStatusType wdst with (nolock) on wdst.WhsDocumentStatusType_id = wds.WhsDocumentStatusType_id
				left join v_WhsDocumentUc wdu (nolock) on wdu.WhsDocumentUc_id = wds.WhsDocumentUc_id
			where
				WhsDocumentSupply_id = :WhsDocumentSupply_id
		";
		$r = $this->db->query($q, array('WhsDocumentSupply_id' => $data['WhsDocumentSupply_id']));
		if ( is_object($r) ) {
			$r = $r->result('array');
			if (isset($r[0])) {
				return $r;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	/**
	 * Получение списка ГК
	 */
	function loadList($filter) {
		$select = array();
		$join = array();
		$where = array();
		$params = array();

        $region = $_SESSION['region']['nick'];

        //колонки "Отгружено" и "Оплачено"
        if ($region == 'saratov') {
            $select[] = "registry_sum.FinDocument_Sum";
            $select[] = "registry_sum.RegistryDataRecept_Sum";
            $join[] = "
                outer apply (
                    select
                        sum(i_fin_doc.FinDocument_Sum) as FinDocument_Sum,
                        sum(i_rec_data.RegistryDataRecept_Sum) as RegistryDataRecept_Sum
                    from
                        {$this->schema}.RegistryLLO i_rllo with (nolock)
                        outer apply (
                            select
                                sum(i_fd.FinDocument_Sum) as FinDocument_Sum
                            from
                                {$this->schema}.v_RegistryLLOFinDocument i_rllo_fd with (nolock)
                                left join {$this->schema}.FinDocument i_fd with (nolock) on i_fd.FinDocument_id = i_rllo_fd.FinDocument_id
                            where
                                 i_rllo_fd.RegistryLLO_id = i_rllo.RegistryLLO_id and
                                 i_fd.FinDocumentType_id = :PayFinDocumentType_id
                        ) i_fin_doc
                        outer apply (
                            select
                                sum(i_rdr.RegistryDataRecept_Sum) as RegistryDataRecept_Sum
                            from
                                {$this->schema}.v_RegistryDataRecept i_rdr with (nolock)
                            where
                                 i_rdr.RegistryLLO_id = i_rllo.RegistryLLO_id
                        ) i_rec_data
                    where
                        i_rllo.RegistryLLO_id in (
                            select
                                i_rdr.RegistryLLO_id
                            from
                                v_WhsDocumentSupply i_wds with (nolock)
                                left join v_DrugShipment i_ds with (nolock) on i_ds.WhsDocumentSupply_id = i_wds.WhsDocumentSupply_id
                                left join v_DrugShipmentlink i_dsl with (nolock) on i_dsl.DrugShipment_id = i_ds.DrugShipment_id
                                left join v_DocumentUcStr i_dus with (nolock) on i_dus.DocumentUcStr_oid = i_dsl.DocumentUcStr_id
                                left join v_DocumentUc i_du with (nolock) on i_du.DocumentUc_id = i_dus.DocumentUc_id
                                left join {$this->schema}.v_RegistryDataRecept i_rdr with (nolock) on i_rdr.ReceptOtov_id = i_dus.ReceptOtov_id
                            where
                                i_wds.WhsDocumentSupply_id = v_WhsDocumentSupply.WhsDocumentSupply_id and
                                i_du.DrugDocumentType_id = :DocRealDrugDocumentType_id and
                                i_rdr.ReceptOtov_id is not null
                        )
                ) registry_sum
            ";
            $params['PayFinDocumentType_id'] = $this->getObjectIdByCode('FinDocumentType', 2); //2 - платежное поручение
            $params['DocRealDrugDocumentType_id'] = $this->getObjectIdByCode('DrugDocumentType', 11); //11 - Реализация
        }

		if (isset($filter['WhsDocumentUc_pid']) && $filter['WhsDocumentUc_pid']) {
			$where[] = 'v_WhsDocumentSupply.WhsDocumentUc_pid = :WhsDocumentUc_pid';
			$params['WhsDocumentUc_pid'] = $filter['WhsDocumentUc_pid'];
		}
		if (isset($filter['WhsDocumentUc_Num']) && $filter['WhsDocumentUc_Num']) {
			$where[] = "v_WhsDocumentSupply.WhsDocumentUc_Num like '%'+:WhsDocumentUc_Num+'%'";
			$params['WhsDocumentUc_Num'] = $filter['WhsDocumentUc_Num'];
		}
		if (isset($filter['WhsDocumentUc_Name']) && $filter['WhsDocumentUc_Name']) {
			$where[] = 'v_WhsDocumentSupply.WhsDocumentUc_Name = :WhsDocumentUc_Name';
			$params['WhsDocumentUc_Name'] = $filter['WhsDocumentUc_Name'];
		}
		if (isset($filter['WhsDocumentType_id']) && $filter['WhsDocumentType_id']) {
			$where[] = 'v_WhsDocumentSupply.WhsDocumentType_id = :WhsDocumentType_id';
			$params['WhsDocumentType_id'] = $filter['WhsDocumentType_id'];
		}
		if (!empty($filter['WhsDocumentType_Code'])) {
			$where[] = 'WhsDocumentType_ref.WhsDocumentType_Code = :WhsDocumentType_Code';
			$params['WhsDocumentType_Code'] = $filter['WhsDocumentType_Code'];
		} else {
			$where[] = 'WhsDocumentType_ref.WhsDocumentType_Code <> 13'; //исключаем доп соглашения из списка
		}
		if (isset($filter['WhsDocumentUc_Date']) && $filter['WhsDocumentUc_Date']) {
			$where[] = 'v_WhsDocumentSupply.WhsDocumentUc_Date = :WhsDocumentUc_Date';
			$params['WhsDocumentUc_Date'] = $filter['WhsDocumentUc_Date'];
		}
		if (isset($filter['WhsDocumentUc_DateRange']) && !empty($filter['WhsDocumentUc_DateRange'][0]) && !empty($filter['WhsDocumentUc_DateRange'][1])) {
			$where[] = 'v_WhsDocumentSupply.WhsDocumentUc_Date >= cast(:WhsDocumentUc_Date_startdate as date)';
			$where[] = 'v_WhsDocumentSupply.WhsDocumentUc_Date <= cast(:WhsDocumentUc_Date_enddate as date)';
			$params['WhsDocumentUc_Date_startdate'] = $filter['WhsDocumentUc_DateRange'][0];
			$params['WhsDocumentUc_Date_enddate'] = $filter['WhsDocumentUc_DateRange'][1];
		}
		if (!empty($filter['begDate'])) {
			// дата конца должна быть больше
			$where[] = '(v_WhsDocumentSupply.WhsDocumentSupply_ExecDate >= cast(:begDate as date) OR v_WhsDocumentSupply.WhsDocumentSupply_ExecDate IS NULL)';
			$params['begDate'] = $filter['begDate'];
		}
		if (!empty($filter['endDate'])) {
			// дата начала должна быть меньше
			$where[] = 'v_WhsDocumentSupply.WhsDocumentUc_Date <= cast(:endDate as date)';
			$params['endDate'] = $filter['endDate'];
		}

		if (!empty($filter['mode']) && $filter['mode'] == 'supplier') {
			$params['Org_sid'] = isset($filter['session']['org_id']) ? $filter['session']['org_id'] : null;
			// только где текущая организация - поставщик
			$where[] = 'v_WhsDocumentSupply.Org_sid = :Org_sid';
		}

		if (isset($filter['Org_sid']) && $filter['Org_sid']) {
			$where[] = 'v_WhsDocumentSupply.Org_sid = :Org_sid';
			$params['Org_sid'] = $filter['Org_sid'];
		}
		if (isset($filter['Org_cid']) && $filter['Org_cid']) {
			$where[] = 'v_WhsDocumentSupply.Org_cid = :Org_cid';
			$params['Org_cid'] = $filter['Org_cid'];
		}
		if (isset($filter['Org_pid']) && $filter['Org_pid']) {
			$where[] = 'v_WhsDocumentSupply.Org_pid = :Org_pid';
			$params['Org_pid'] = $filter['Org_pid'];
		}
		if (isset($filter['Org_rid']) && $filter['Org_rid']) {
			$where[] = 'v_WhsDocumentSupply.Org_rid = :Org_rid';
			$params['Org_rid'] = $filter['Org_rid'];
		}
		if (isset($filter['WhsDocumentUc_Sum']) && $filter['WhsDocumentUc_Sum']) {
			$where[] = 'v_WhsDocumentSupply.WhsDocumentUc_Sum = :WhsDocumentUc_Sum';
			$params['WhsDocumentUc_Sum'] = $filter['WhsDocumentUc_Sum'];
		}
		if (isset($filter['WhsDocumentSupply_id']) && $filter['WhsDocumentSupply_id']) {
			$where[] = 'v_WhsDocumentSupply.WhsDocumentSupply_id = :WhsDocumentSupply_id';
			$params['WhsDocumentSupply_id'] = $filter['WhsDocumentSupply_id'];
		}
		if (isset($filter['WhsDocumentUc_id']) && $filter['WhsDocumentUc_id']) {
			$where[] = 'v_WhsDocumentSupply.WhsDocumentUc_id = :WhsDocumentUc_id';
			$params['WhsDocumentUc_id'] = $filter['WhsDocumentUc_id'];
		}
		if (isset($filter['WhsDocumentSupply_ProtNum']) && $filter['WhsDocumentSupply_ProtNum']) {
			$where[] = 'v_WhsDocumentSupply.WhsDocumentSupply_ProtNum = :WhsDocumentSupply_ProtNum';
			$params['WhsDocumentSupply_ProtNum'] = $filter['WhsDocumentSupply_ProtNum'];
		}
		if (isset($filter['WhsDocumentSupply_ProtDate']) && $filter['WhsDocumentSupply_ProtDate']) {
			$where[] = 'v_WhsDocumentSupply.WhsDocumentSupply_ProtDate = :WhsDocumentSupply_ProtDate';
			$params['WhsDocumentSupply_ProtDate'] = $filter['WhsDocumentSupply_ProtDate'];
		}
		if (isset($filter['WhsDocumentSupplyType_id']) && $filter['WhsDocumentSupplyType_id']) {
			$where[] = 'v_WhsDocumentSupply.WhsDocumentSupplyType_id = :WhsDocumentSupplyType_id';
			$params['WhsDocumentSupplyType_id'] = $filter['WhsDocumentSupplyType_id'];
		}
		if (isset($filter['WhsDocumentSupply_ExecDate']) && $filter['WhsDocumentSupply_ExecDate']) {
			$where[] = 'v_WhsDocumentSupply.WhsDocumentSupply_ExecDate = :WhsDocumentSupply_ExecDate';
			$params['WhsDocumentSupply_ExecDate'] = $filter['WhsDocumentSupply_ExecDate'];
		}
		if (isset($filter['DrugFinance_id']) && $filter['DrugFinance_id']) {
			$where[] = 'v_WhsDocumentSupply.DrugFinance_id = :DrugFinance_id';
			$params['DrugFinance_id'] = $filter['DrugFinance_id'];
		}
		if (isset($filter['FinanceSource_id']) && $filter['FinanceSource_id']) {
			$where[] = 'v_WhsDocumentSupply.FinanceSource_id = :FinanceSource_id';
			$params['FinanceSource_id'] = $filter['FinanceSource_id'];
		}
		if (isset($filter['WhsDocumentCostItemType_id']) && $filter['WhsDocumentCostItemType_id']) {
			$where[] = 'v_WhsDocumentSupply.WhsDocumentCostItemType_id = :WhsDocumentCostItemType_id';
			$params['WhsDocumentCostItemType_id'] = $filter['WhsDocumentCostItemType_id'];
		}
		if (isset($filter['WhsDocumentStatusType_id']) && $filter['WhsDocumentStatusType_id']) {
			$where[] = 'v_WhsDocumentSupply.WhsDocumentStatusType_id = :WhsDocumentStatusType_id'; //заменить на нормальную проверку, как только появится признак подписания договора
			$params['WhsDocumentStatusType_id'] = $filter['WhsDocumentStatusType_id'];
		}
		if (!empty($filter['BudgetFormType_id'])) {
			$where[] = 'v_WhsDocumentSupply.BudgetFormType_id = :BudgetFormType_id';
			$params['BudgetFormType_id'] = $filter['BudgetFormType_id'];
		}
		if (!empty($filter['WhsDocumentPurchType_id'])) {
			$where[] = 'v_WhsDocumentSupply.WhsDocumentPurchType_id = :WhsDocumentPurchType_id';
			$params['WhsDocumentPurchType_id'] = $filter['WhsDocumentPurchType_id'];
		}
		if (!empty($filter['WhsDocumentUc_KBK'])) {
			$where[] = 'v_WhsDocumentSupply.WhsDocumentSupply_KBK = :WhsDocumentUc_KBK';
			$params['WhsDocumentUc_KBK'] = $filter['WhsDocumentUc_KBK'];
		}

		$select_clause = count($select) > 0 ? ', '.implode(', ', $select) : '';
		$join_clause = implode(' ', $join);
        $where_clause = implode(' and ', $where);
		if (strlen($where_clause)) {
			$where_clause = "
				where
					-- where
					{$where_clause}
					-- end where
			";
		}
		
		$query = "
			select
				-- select
				v_WhsDocumentSupply.WhsDocumentUc_pid,
				v_WhsDocumentSupply.WhsDocumentUc_Num,
				v_WhsDocumentSupply.WhsDocumentUc_Name,
				v_WhsDocumentSupply.WhsDocumentType_id,
				convert(varchar(10), v_WhsDocumentSupply.WhsDocumentUc_Date, 104) WhsDocumentUc_Date,
				v_WhsDocumentSupply.Org_sid,
				v_WhsDocumentSupply.Org_cid,
				v_WhsDocumentSupply.Org_pid,
				v_WhsDocumentSupply.Org_rid,
				Org_sid_ref.Org_Name Org_sid_Name,
				isnull(Org_sid_ref.Org_Nick, Org_sid_ref.Org_Name) Org_sid_Nick,
				Org_cid_ref.Org_Name Org_cid_Name,
				Org_pid_ref.Org_Name Org_pid_Name,
				Org_rid_ref.Org_Name Org_rid_Name,
				WhsDocumentStatusType_ref.WhsDocumentStatusType_Name WhsDocumentStatusType_Name,
				(convert(varchar(10), isnull(v_WhsDocumentSupply.WhsDocumentSupply_BegDate, v_WhsDocumentSupply.WhsDocumentUc_Date), 104) + ' - ' + isnull(convert(varchar(10), v_WhsDocumentSupply.WhsDocumentSupply_ExecDate, 104), '')) as ActualDateRange,
				(case
					when (select count(WhsDocumentDelivery_id) from WhsDocumentDelivery with (nolock) where WhsDocumentSupply_id = v_WhsDocumentSupply.WhsDocumentSupply_id) > 0
					then v_WhsDocumentSupply.WhsDocumentSupply_id
					else null
				end) as GraphLink,
				null FinanceInf,
				isnull(WhsDocumentSupply_ProtNum + ' ', '') + isnull('(' + convert(varchar(10), v_WhsDocumentSupply.WhsDocumentSupply_ProtDate, 104) + ')', '') as ProtInf,
				parentDoc.WhsDocumentUc_Name+isnull(' / '+SvodDrugRequest.DrugRequest_Name, '') as WhsDocumentUc_pName,
				rtrim(
				    isnull('Лот № '+parentDoc.WhsDocumentUc_Num, '')+
				    isnull(' от '+convert(varchar(10), parentDoc.WhsDocumentUc_Date, 104), '')+
				    ' ' + isnull(SvodDrugRequest.WhsDocumentProcurementRequestSpec_Name,'')
				) as WhsDocumentProcurementRequest_Name,
				SvodDrugRequest.DrugRequest_Name,
				cast(v_WhsDocumentSupply.WhsDocumentUc_Sum as decimal(15,2)) as WhsDocumentUc_Sum,
				v_WhsDocumentSupply.WhsDocumentSupply_id,
				v_WhsDocumentSupply.WhsDocumentUc_id,
				v_WhsDocumentSupply.WhsDocumentSupply_ProtNum,
				v_WhsDocumentSupply.WhsDocumentSupply_ProtDate,
				v_WhsDocumentSupply.WhsDocumentSupplyType_id,
				v_WhsDocumentSupply.WhsDocumentSupply_ExecDate,
				v_WhsDocumentSupply.DrugFinance_id,
				v_WhsDocumentSupply.WhsDocumentCostItemType_id,
				v_WhsDocumentSupply.WhsDocumentStatusType_id,
				v_WhsDocumentSupply.WhsDocumentUc_Name WhsDocumentUc_pid_Name,
				WhsDocumentSupplyType_ref.WhsDocumentSupplyType_Name,
				DrugFinance_ref.DrugFinance_Name,
				case when FinanceSource_ref.FinanceSource_Name is not null and DrugFinance_ref.DrugFinance_Name is not null
					then rtrim(isnull(DrugFinance_ref.DrugFinance_Name,'') + ', ' + isnull(FinanceSource_ref.FinanceSource_Nick,FinanceSource_ref.FinanceSource_Name))
					else rtrim(isnull(DrugFinance_ref.DrugFinance_Name,'') + isnull(FinanceSource_ref.FinanceSource_Nick,''))
				end as DrugFinanceSource_Name,
				WhsDocumentCostItemType_ref.WhsDocumentCostItemType_Name,
				WhsDocumentType_ref.WhsDocumentType_Name,
				BudgetFormType_ref.BudgetFormType_Name,
				co.CommercialOffer_id,
				v_WhsDocumentSupply.WhsDocumentSupply_KBK,
				case when WhsDocumentUc.WhsDocumentUc_ImportDT is  NULL then 1 else 2 end isImport
				$select_clause
				-- end select
			from
				-- from
				dbo.v_WhsDocumentSupply with (nolock)
				left join dbo.v_WhsDocumentSupplyType WhsDocumentSupplyType_ref with (nolock) on WhsDocumentSupplyType_ref.WhsDocumentSupplyType_id = v_WhsDocumentSupply.WhsDocumentSupplyType_id
				left join dbo.v_DrugFinance DrugFinance_ref with (nolock) on DrugFinance_ref.DrugFinance_id = v_WhsDocumentSupply.DrugFinance_id
				left join dbo.v_FinanceSource FinanceSource_ref with (nolock) on FinanceSource_ref.FinanceSource_id = v_WhsDocumentSupply.FinanceSource_id
				left join dbo.v_WhsDocumentCostItemType WhsDocumentCostItemType_ref with (nolock) on WhsDocumentCostItemType_ref.WhsDocumentCostItemType_id = v_WhsDocumentSupply.WhsDocumentCostItemType_id
				left join dbo.v_Org Org_sid_ref with (nolock) on Org_sid_ref.Org_id = v_WhsDocumentSupply.Org_sid
				left join dbo.v_Org Org_cid_ref with (nolock) on Org_cid_ref.Org_id = v_WhsDocumentSupply.Org_cid
				left join dbo.v_Org Org_pid_ref with (nolock) on Org_pid_ref.Org_id = v_WhsDocumentSupply.Org_pid
				left join dbo.v_Org Org_rid_ref with (nolock) on Org_rid_ref.Org_id = v_WhsDocumentSupply.Org_rid
				left join dbo.v_WhsDocumentStatusType WhsDocumentStatusType_ref with (nolock) on WhsDocumentStatusType_ref.WhsDocumentStatusType_id = isnull(v_WhsDocumentSupply.WhsDocumentStatusType_id, 1)
				left join dbo.v_WhsDocumentType WhsDocumentType_ref with (nolock) on WhsDocumentType_ref.WhsDocumentType_id = v_WhsDocumentSupply.WhsDocumentType_id
				left join dbo.WhsDocumentUc parentDoc with (nolock) on parentDoc.WhsDocumentUc_id = v_WhsDocumentSupply.WhsDocumentUc_pid
				left join dbo.WhsDocumentUc  with (nolock) on WhsDocumentUc.WhsDocumentUc_id = v_WhsDocumentSupply.WhsDocumentUc_id
				left join dbo.v_BudgetFormType BudgetFormType_ref with (nolock) on BudgetFormType_ref.BudgetFormType_id = v_WhsDocumentSupply.BudgetFormType_id
				left join dbo.v_CommercialOffer co with (nolock) on co.CommercialOffer_id = v_WhsDocumentSupply.CommercialOffer_id
				outer apply (
					select top 1
                        i_dr.DrugRequest_Name,
                        i_wdprs.WhsDocumentProcurementRequestSpec_Name
                    from
                        v_WhsDocumentProcurementRequest i_wdpr with (nolock)
                        left join v_WhsDocumentProcurementRequestSpec i_wdprs with (nolock) on i_wdprs.WhsDocumentProcurementRequest_id = i_wdpr.WhsDocumentProcurementRequest_id
                        left join v_DrugRequestPurchaseSpec i_drps with (nolock) on i_drps.DrugRequestPurchaseSpec_id = i_wdprs.DrugRequestPurchaseSpec_id
                        left join v_DrugRequest i_dr with (nolock) on i_dr.DrugRequest_id = i_drps.DrugRequest_id
                    where
					    i_wdpr.WhsDocumentUc_id = v_WhsDocumentSupply.WhsDocumentUc_pid
					order by
					    i_dr.DrugRequest_id
				) SvodDrugRequest
				$join_clause
				-- end from
			$where_clause
			order by
				-- order by
				v_WhsDocumentSupply.WhsDocumentUc_Num
				-- end order by
		";
		
		if (!empty($filter['limit'])) {
			$result = $this->db->query(getLimitSQLPH($query, $filter['start'], $filter['limit']), $params);
			$count = $this->getFirstResultFromQuery(getCountSQLPH($query), $params);
			if (is_object($result) && $count !== false) {
				return array(
					'data' => $result->result('array'),
					'totalCount' => $count
				);
			} else {
				return false;
			}
		} else {
			$result = $this->db->query($query, $params);
			if ( is_object($result) ) {
				return $result->result('array');
			} else {
				return false;
			}
		}
	}


	/**
	 * Сохранение
	 */
	function save() {
		$procedure = 'p_WhsDocumentSupply_ins';
		if ( $this->WhsDocumentSupply_id > 0 ) {
			$procedure = 'p_WhsDocumentSupply_upd';
		}
		$q = "
			declare
				@WhsDocumentSupply_id bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @WhsDocumentSupply_id = :WhsDocumentSupply_id;
			exec dbo." . $procedure . "
				@WhsDocumentUc_pid = :WhsDocumentUc_pid,
				@WhsDocumentUc_Num = :WhsDocumentUc_Num,
				@WhsDocumentUc_Name = :WhsDocumentUc_Name,
				@WhsDocumentType_id = :WhsDocumentType_id,
				@WhsDocumentUc_Date = :WhsDocumentUc_Date,
				@WhsDocumentStatusType_id = :WhsDocumentStatusType_id,
				@Org_aid = :Org_aid,
				@Org_sid = :Org_sid,
				@Org_cid = :Org_cid,
				@Org_pid = :Org_pid,
				@Org_rid = :Org_rid,			
				@WhsDocumentUc_Sum = :WhsDocumentUc_Sum,
				@WhsDocumentSupply_id = @WhsDocumentSupply_id".($this->WhsDocumentSupply_id > 0 ? "" :" output").",
				@WhsDocumentUc_id = :WhsDocumentUc_id,
				@WhsDocumentSupply_ProtNum = :WhsDocumentSupply_ProtNum,
				@WhsDocumentSupply_ProtDate = :WhsDocumentSupply_ProtDate,
				@WhsDocumentSupplyType_id = :WhsDocumentSupplyType_id,
				@WhsDocumentSupply_BegDate = :WhsDocumentSupply_BegDate,
				@WhsDocumentSupply_ExecDate = :WhsDocumentSupply_ExecDate,
				@DrugFinance_id = :DrugFinance_id,
				@WhsDocumentCostItemType_id = :WhsDocumentCostItemType_id,
				@BudgetFormType_id = :BudgetFormType_id,
				@WhsDocumentPurchType_id = :WhsDocumentPurchType_id,
				@FinanceSource_id = :FinanceSource_id,
				@DrugNds_id = :DrugNds_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @WhsDocumentSupply_id as WhsDocumentSupply_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$p = array(
			'WhsDocumentUc_pid' => $this->WhsDocumentUc_pid,
			'WhsDocumentUc_Num' => $this->WhsDocumentUc_Num,
			'WhsDocumentUc_Name' => $this->WhsDocumentUc_Name,
			'WhsDocumentType_id' => $this->WhsDocumentType_id,
			'WhsDocumentUc_Date' => $this->WhsDocumentUc_Date,
			'WhsDocumentStatusType_id' => $this->WhsDocumentStatusType_id,
			'Org_aid' => $this->Org_aid,
			'Org_sid' => $this->Org_sid,
			'Org_cid' => $this->Org_cid,
			'Org_pid' => $this->Org_pid,
			'Org_rid' => $this->Org_rid,
			'WhsDocumentUc_Sum' => $this->WhsDocumentUc_Sum,
			'WhsDocumentSupply_id' => $this->WhsDocumentSupply_id,
			'WhsDocumentUc_id' => $this->WhsDocumentUc_id,
			'WhsDocumentSupply_ProtNum' => $this->WhsDocumentSupply_ProtNum,
			'WhsDocumentSupply_ProtDate' => $this->WhsDocumentSupply_ProtDate,
			'WhsDocumentSupplyType_id' => $this->WhsDocumentSupplyType_id,
			'WhsDocumentSupply_BegDate' => $this->WhsDocumentSupply_BegDate,
			'WhsDocumentSupply_ExecDate' => $this->WhsDocumentSupply_ExecDate,
			'DrugFinance_id' => $this->DrugFinance_id,
			'WhsDocumentCostItemType_id' => $this->WhsDocumentCostItemType_id,
			'BudgetFormType_id' => $this->BudgetFormType_id,
			'WhsDocumentPurchType_id' => $this->WhsDocumentPurchType_id,
			'FinanceSource_id' => $this->FinanceSource_id,
			'DrugNds_id' => $this->DrugNds_id,
			'pmUser_id' => $this->pmUser_id
		);
		//echo getDebugSQL($q, $p);exit;
		$r = $this->db->query($q, $p);
		if ( is_object($r) ) {
		    $result = $r->result('array');
		    $this->WhsDocumentSupply_id = $result[0]['WhsDocumentSupply_id'];
		} else {
			log_message('error', var_export(array('q' => $q, 'p' => $p, 'e' => sqlsrv_errors()), true));
			$result = array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных'));
		}
		return $result;
	}

	/**
	 * Удаление
	 */
	function delete() {
		$q = "
			delete from
				WhsDocumentDelivery with(rowlock)
			where
				WhsDocumentSupply_id = :WhsDocumentSupply_id
		";
		$r = $this->db->query($q, array(
			'WhsDocumentSupply_id' => $this->WhsDocumentSupply_id
		));
		
		$q = "
			delete from
				WhsDocumentSupplySpecDrug with(rowlock)
			where
				WhsDocumentSupplySpec_id in (
				    select
				        WhsDocumentSupplySpec_id
                    from
                        WhsDocumentSupplySpec with(rowlock)
                    where
                        WhsDocumentSupply_id = :WhsDocumentSupply_id
				)
		";
		$r = $this->db->query($q, array(
			'WhsDocumentSupply_id' => $this->WhsDocumentSupply_id
		));;

		$q = "
			delete from
				WhsDocumentSupplySpec with(rowlock)
			where
				WhsDocumentSupply_id = :WhsDocumentSupply_id
		";
		$r = $this->db->query($q, array(
			'WhsDocumentSupply_id' => $this->WhsDocumentSupply_id
		));

		$q = "
			delete from
				WhsDocumentUcPriceHistory with(rowlock)
			where
				WhsDocumentUc_id = :WhsDocumentSupply_id
		";
		$r = $this->db->query($q, array(
			'WhsDocumentSupply_id' => $this->WhsDocumentSupply_id
		));

		//удаляем дополнительные соглашения
		$q = "
			delete from
				WhsDocumentUc with(rowlock)
			where
				WhsDocumentUc_pid = :WhsDocumentSupply_id
		";
		$r = $this->db->query($q, array(
			'WhsDocumentSupply_id' => $this->WhsDocumentSupply_id
		));
	
		$q = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec dbo.p_WhsDocumentSupply_del
				@WhsDocumentSupply_id = :WhsDocumentSupply_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$r = $this->db->query($q, array(
			'WhsDocumentSupply_id' => $this->WhsDocumentSupply_id,
			'pmUser_id' => $this->pmUser_id
		));
		if ( is_object($r) ) {
			return $r->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Получение списка лотов
	 */
	function loadWhsDocumentProcurementRequestList($filter) {
		$where_clause = 'where WhsDocumentUc_pid is not null';
		
		if (isset($filter['WhsDocumentSupply_id']) && $filter['WhsDocumentSupply_id']) {
			$where_clause .= ' and WhsDocumentSupply_id <> :WhsDocumentSupply_id';
		}

		$q = "
			SELECT
				wdpr.WhsDocumentProcurementRequest_id,
				wdpr.WhsDocumentUc_Name,
				wdpr.DrugFinance_id,
				wdpr.WhsDocumentCostItemType_id,
				wdpr.BudgetFormType_id,
				wdprsi.WhsDocumentProcurementRequestSpec_Name,
				wdpr.Org_aid as Org_id,
				orgt.OrgType_SysNick
			FROM
				dbo.v_WhsDocumentProcurementRequest wdpr WITH (NOLOCK)
				outer apply (
					select
						count(WhsDocumentProcurementRequestSpec_id) as Count
					from
						WhsDocumentProcurementRequestSpec wdprs with (nolock)
					where
						wdprs.WhsDocumentProcurementRequest_id = wdpr.WhsDocumentProcurementRequest_id
				) spec_count
				left join v_WhsDocumentProcurementRequestSpec wdprsi with (nolock) on wdprsi.WhsDocumentProcurementRequest_id = wdpr.WhsDocumentProcurementRequest_id
				left join Org org with (nolock) on org.Org_id = wdpr.Org_aid
				left join OrgType orgt with (nolock) on orgt.OrgType_id = org.OrgType_id
			WHERE
				wdpr.WhsDocumentUc_id not in (select WhsDocumentUc_pid from v_WhsDocumentSupply with(nolock) $where_clause) and
				spec_count.Count > 0
		";
		$result = $this->db->query($q, $filter);
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Получение спецификации лота
	 */
	function loadWhsDocumentProcurementRequestSpecList($filter) {
		$q = "
			SELECT
				v_WhsDocumentProcurementRequestSpec.WhsDocumentProcurementRequestSpec_id,
				v_WhsDocumentProcurementRequestSpec.WhsDocumentProcurementRequest_id,
				v_WhsDocumentProcurementRequestSpec.DrugComplexMnn_id,
				v_WhsDocumentProcurementRequestSpec.Drug_id,
				v_WhsDocumentProcurementRequestSpec.Okei_id,
				v_WhsDocumentProcurementRequestSpec.WhsDocumentProcurementRequestSpec_Kolvo,
				v_WhsDocumentProcurementRequestSpec.WhsDocumentProcurementRequestSpec_PriceMax,
				v_WhsDocumentProcurementRequestSpec.pmUser_insID,
				v_WhsDocumentProcurementRequestSpec.pmUser_updID,
				v_WhsDocumentProcurementRequestSpec.WhsDocumentProcurementRequestSpec_insDT,
				v_WhsDocumentProcurementRequestSpec.WhsDocumentProcurementRequestSpec_updDT
			FROM
				dbo.v_WhsDocumentProcurementRequestSpec WITH (NOLOCK)
			WHERE
				v_WhsDocumentProcurementRequestSpec.WhsDocumentProcurementRequest_id = :WhsDocumentProcurementRequest_id
		";
		$result = $this->db->query($q, $filter);
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Получение графика поставки
	 */
	function loadWhsDocumentDeliveryList($filter) {
		$where = array();
		if (isset($filter['WhsDocumentSupply_id']) && $filter['WhsDocumentSupply_id']) {
			$where[] = 'v_WhsDocumentDelivery.WhsDocumentSupply_id = :WhsDocumentSupply_id';
		}
		$where_clause = implode(' AND ', $where);
		if (strlen($where_clause)) {
			$where_clause = 'WHERE '.$where_clause;
		}
		$q = "
			SELECT
				v_WhsDocumentDelivery.WhsDocumentDelivery_id, v_WhsDocumentDelivery.WhsDocumentSupply_id, v_WhsDocumentDelivery.WhsDocumentSupplySpec_id, v_WhsDocumentDelivery.WhsDocumentDelivery_setDT, v_WhsDocumentDelivery.Okei_id, v_WhsDocumentDelivery.WhsDocumentDelivery_Kolvo
				,Okei_id_ref.Okei_Name Okei_id_Name
			FROM
				dbo.v_WhsDocumentDelivery WITH (NOLOCK)
				LEFT JOIN dbo.v_WhsDocumentSupply WhsDocumentSupply_id_ref WITH (NOLOCK) ON WhsDocumentSupply_id_ref.WhsDocumentSupply_id = v_WhsDocumentDelivery.WhsDocumentSupply_id
				LEFT JOIN dbo.v_WhsDocumentSupplySpec WhsDocumentSupplySpec_id_ref WITH (NOLOCK) ON WhsDocumentSupplySpec_id_ref.WhsDocumentSupplySpec_id = v_WhsDocumentDelivery.WhsDocumentSupplySpec_id
				LEFT JOIN dbo.v_Okei Okei_id_ref WITH (NOLOCK) ON Okei_id_ref.Okei_id = v_WhsDocumentDelivery.Okei_id
			$where_clause
			ORDER BY
				v_WhsDocumentDelivery.WhsDocumentSupplySpec_id, v_WhsDocumentDelivery.WhsDocumentDelivery_setDT
		";
		$result = $this->db->query($q, $filter);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Сохраненеи графика поставки
	 */
	function saveWhsDocumentDelivery() {
		$procedure = 'p_WhsDocumentDelivery_ins';
		if ( $this->WhsDocumentDelivery_id > 0 ) {
			$procedure = 'p_WhsDocumentDelivery_upd';
		}
		$q = "
			declare
				@WhsDocumentDelivery_id bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @WhsDocumentDelivery_id = :WhsDocumentDelivery_id;
			exec dbo." . $procedure . "
				@WhsDocumentDelivery_id = @WhsDocumentDelivery_id output,
				@WhsDocumentSupply_id = :WhsDocumentSupply_id,
				@WhsDocumentSupplySpec_id = :WhsDocumentSupplySpec_id,
				@WhsDocumentDelivery_setDT = :WhsDocumentDelivery_setDT,
				@Okei_id = :Okei_id,
				@WhsDocumentDelivery_Kolvo = :WhsDocumentDelivery_Kolvo,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @WhsDocumentDelivery_id as WhsDocumentDelivery_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$p = array(
			'WhsDocumentDelivery_id' => $this->WhsDocumentDelivery_id,
			'WhsDocumentSupply_id' => $this->WhsDocumentSupply_id,
			'WhsDocumentSupplySpec_id' => $this->WhsDocumentSupplySpec_id,
			'WhsDocumentDelivery_setDT' => $this->WhsDocumentDelivery_setDT,
			'Okei_id' => $this->Okei_id,
			'WhsDocumentDelivery_Kolvo' => $this->WhsDocumentDelivery_Kolvo,
			'pmUser_id' => $this->pmUser_id,
		);
		$r = $this->db->query($q, $p);
		if ( is_object($r) ) {
		    $result = $r->result('array');
		    $this->WhsDocumentDelivery_id = $result[0]['WhsDocumentDelivery_id'];
		}
		else {
			log_message('error', var_export(array('q' => $q, 'p' => $p, 'e' => sqlsrv_errors()), true));
			$result = array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных'));
		}
		return $result;
	}

	/**
	 * Удаление графика поставки
	 */
	function deleteWhsDocumentDelivery() {
		$q = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec dbo.p_WhsDocumentDelivery_del
				@WhsDocumentDelivery_id = :WhsDocumentDelivery_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$r = $this->db->query($q, array(
			'WhsDocumentDelivery_id' => $this->WhsDocumentDelivery_id
		));
		if ( is_object($r) ) {
			return $r->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Загрузка спецификации ГК
	 */
	function loadWhsDocumentSupplyList($filter) {
		if ( $filter['WhsDocumentType_id'] == 12 ) { // Документ на включение
            $query = "
				select
					wds.WhsDocumentUc_id,
					wds.WhsDocumentUc_Num,
					wds.WhsDocumentUc_Name,
				    fin_year.yr as WhsDocumentSupply_Year
				from
					v_WhsDocumentSupply wds with (nolock)
					inner join v_DrugShipment ds with (nolock) on ds.WhsDocumentSupply_id = wds.WhsDocumentSupply_id
					inner join v_DrugOstatRegistry dor with (nolock) on dor.DrugShipment_id = ds.DrugShipment_id
					outer apply (
                        select
                            datepart(year, isnull(max(i_wdd.WhsDocumentDelivery_setDT), wds.WhsDocumentUc_Date)) as yr
                        from
                            v_WhsDocumentDelivery i_wdd with (nolock)
                        where
                            i_wdd.WhsDocumentSupply_id = wds.WhsDocumentSupply_id
                    ) fin_year
				where
					dor.DrugOstatRegistry_Kolvo > 0 and
					dor.Drug_id is not null
				group by
					wds.WhsDocumentUc_id,
					wds.WhsDocumentUc_Num,
					wds.WhsDocumentUc_Name,
					fin_year.yr
			";
		} elseif ( $filter['WhsDocumentType_id'] == 13 ) { // Документ на исключение
            $query = "
				select
					wds.WhsDocumentUc_id,
					wds.WhsDocumentUc_Num,
					wds.WhsDocumentUc_Name,
				    fin_year.yr as WhsDocumentSupply_Year
				from
					v_WhsDocumentSupply wds with (nolock)
					inner join v_WhsDocumentOrderReserveDrug wdord with (nolock) on wdord.WhsDocumentUc_pid = wds.WhsDocumentSupply_id
					outer apply (
                        select
                            datepart(year, isnull(max(i_wdd.WhsDocumentDelivery_setDT), wds.WhsDocumentUc_Date)) as yr
                        from
                            v_WhsDocumentDelivery i_wdd with (nolock)
                        where
                            i_wdd.WhsDocumentSupply_id = wds.WhsDocumentSupply_id
                    ) fin_year
				where
					wdord.WhsDocumentOrderReserveDrug_Kolvo > 0
				group by
					wds.WhsDocumentUc_id,
					wds.WhsDocumentUc_Num,
					wds.WhsDocumentUc_Name,
					fin_year.yr
			";
		}
	
		$result = $this->db->query($query, $filter);
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Загрузка списка доп. соглашений
	 */
	function loadWhsDocumentSupplyAdditionalList($filter) {
		$where = array();
		$params = array();

		$where[] = 'wdt.WhsDocumentType_Code = 13'; //доп соглашения
		
		if (isset($filter['ParentWhsDocumentSupply_id']) && $filter['ParentWhsDocumentSupply_id']) {
			$where[] = "p_wds.WhsDocumentSupply_id = :ParentWhsDocumentSupply_id";
			$params['ParentWhsDocumentSupply_id'] = $filter['ParentWhsDocumentSupply_id'];
		}
		if (isset($filter['WhsDocumentUc_Num']) && $filter['WhsDocumentUc_Num']) {
			$where[] = "p_wds.WhsDocumentUc_Num like '%'+:WhsDocumentUc_Num+'%'";
			$params['WhsDocumentUc_Num'] = $filter['WhsDocumentUc_Num'];
		}
		if (isset($filter['WhsDocumentUc_DateRange']) && !empty($filter['WhsDocumentUc_DateRange'][0]) && !empty($filter['WhsDocumentUc_DateRange'][1])) {
			$where[] = 'p_wds.WhsDocumentUc_Date >= cast(:WhsDocumentUc_Date_startdate as date)';
			$where[] = 'p_wds.WhsDocumentUc_Date <= cast(:WhsDocumentUc_Date_enddate as date)';
			$params['WhsDocumentUc_Date_startdate'] = $filter['WhsDocumentUc_DateRange'][0];
			$params['WhsDocumentUc_Date_enddate'] = $filter['WhsDocumentUc_DateRange'][1];
		}
		if (isset($filter['Org_sid']) && $filter['Org_sid']) {
			$where[] = 'p_wds.Org_sid = :Org_sid';
			$params['Org_sid'] = $filter['Org_sid'];
		}
		if (isset($filter['DrugFinance_id']) && $filter['DrugFinance_id']) {
			$where[] = 'p_wds.DrugFinance_id = :DrugFinance_id';
			$params['DrugFinance_id'] = $filter['DrugFinance_id'];
		}
		if (isset($filter['WhsDocumentCostItemType_id']) && $filter['WhsDocumentCostItemType_id']) {
			$where[] = 'p_wds.WhsDocumentCostItemType_id = :WhsDocumentCostItemType_id';
			$params['WhsDocumentCostItemType_id'] = $filter['WhsDocumentCostItemType_id'];
		}
		if (isset($filter['WhsDocumentStatusType_id']) && $filter['WhsDocumentStatusType_id']) {
			$where[] = 'wds.WhsDocumentStatusType_id = :WhsDocumentStatusType_id'; //заменить на нормальную проверку, как только появится признак подписания договора
			$params['WhsDocumentStatusType_id'] = $filter['WhsDocumentStatusType_id'];
		}

        //фильтр по организации
        $org_filter_fields = array(
            'OrgFilter_Org_sid',
            'OrgFilter_Org_cid',
            'OrgFilter_Org_pid'
        );
        $org_filter_exists = false;
        foreach($org_filter_fields as $of_field) {
            if (!empty($filter[$of_field])) {
                $org_filter_exists = true;
                break;
            }
        }
        if ($org_filter_exists) {
            $of_type = $filter['OrgFilter_Type'] == 'or' ? 'or' : 'and';
            $of_filter = array();
            foreach($org_filter_fields as $of_field) { //сборка условия по конкретному фильтру
                $of_id_array = explode(',', $filter[$of_field]); //если переденно енсколько идентификаторов через запятую, то разбиваем строку на массив идентификаторов
                $of_sub_filter = array();
                foreach($of_id_array as $of_id) {
                    if (!empty($of_id) && $of_id > 0) { //если идентификатор не пустой, то собираем фрагмент условия
                        $of_sub_filter[] = 'p_wds.'.preg_replace('/OrgFilter_/', '', $of_field).' = '.$of_id;
                    }
                }
                //собираем условия по одному фильтру (всегда собираем через 'или')
                if (count($of_sub_filter) > 0) {
                    $of_filter[] = count($of_sub_filter) > 1 ? '('.join(' or ', $of_sub_filter).')' : join(' or ', $of_sub_filter);
                }
            }
            //собираем условие по всем фильтрам организации
            $where[] = '('.join(' '.$of_type.' ', $of_filter).')';
        }
		
		$where_clause = implode(' and ', $where);
		if (strlen($where_clause)) {
			$where_clause = "
				where
			 		-- where
			 		{$where_clause}
			 		-- end where
			";
		}
		
		$query = "
			select
				-- select
				wds.WhsDocumentUc_id,
				wds.WhsDocumentSupply_id,
				wds.WhsDocumentUc_Num,
				wds.WhsDocumentUc_Name,
				convert(varchar(10), wds.WhsDocumentUc_Date, 104) as WhsDocumentUc_Date,
				(isnull(p_wds.WhsDocumentUc_Num, '') + ' ' + isnull(convert(varchar(10), p_wds.WhsDocumentUc_Date, 104), '')) as WhsDocumentUc_pNum,
				isnull(org_s.Org_Nick, org_s.Org_Name) as Org_Nick,
				wds.WhsDocumentStatusType_id,
				wdst.WhsDocumentStatusType_Name,
				--(convert(varchar(10), p_wds.WhsDocumentUc_Date, 104) + ' - ' + isnull(convert(varchar(10), p_wds.WhsDocumentSupply_ExecDate, 104), '')) as ActualDateRange,
				convert(varchar(10), wds.WhsDocumentUc_Date, 104) as ActualDateRange,
				isnull(p_wds.WhsDocumentSupply_ProtNum + ' ', '') + isnull('(' + CONVERT(varchar(10), p_wds.WhsDocumentSupply_ProtDate, 104) + ')', '') as ProtInf,
				ppDoc.WhsDocumentUc_Name+isnull(' / '+SvodDrugRequest.DrugRequest_Name, '') as WhsDocumentUc_ppName,
				df.DrugFinance_Name,
				wdcit.WhsDocumentCostItemType_Name
				-- end select
			from
				-- from
				dbo.v_WhsDocumentSupply wds with (nolock)
				left join dbo.v_WhsDocumentSupply p_wds with (nolock) on p_wds.WhsDocumentUc_id = wds.WhsDocumentUc_pid
				left join dbo.v_DrugFinance df with (nolock) on df.DrugFinance_id = p_wds.DrugFinance_id
				left join dbo.v_WhsDocumentCostItemType wdcit with (nolock) on wdcit.WhsDocumentCostItemType_id = p_wds.WhsDocumentCostItemType_id
				left join dbo.v_Org org_s with (nolock) on org_s.Org_id = p_wds.Org_sid
				left join dbo.v_WhsDocumentType wdt with (nolock) on wdt.WhsDocumentType_id = wds.WhsDocumentType_id
				left join dbo.v_WhsDocumentStatusType wdst with (nolock) on wdst.WhsDocumentStatusType_id = isnull(wds.WhsDocumentStatusType_id, 1)
				left join dbo.WhsDocumentUc ppDoc with (nolock) on ppDoc.WhsDocumentUc_id = p_wds.WhsDocumentUc_pid
				outer apply (
					select top 1
						dr.DrugRequest_Name
					from
						DrugRequestPurchaseSpec drps with (nolock)
						left join DrugRequest dr with (nolock) on dr.DrugRequest_id = drps.DrugRequest_id
					where
						drps.WhsDocumentUc_id = ppDoc.WhsDocumentUc_id
				) SvodDrugRequest
				-- end from
			$where_clause
			order by
				-- order by
				wds.WhsDocumentUc_Num
				-- end order by
		";

		if (!empty($filter['limit'])) {
			$result = $this->db->query(getLimitSQLPH($query, $filter['start'], $filter['limit']), $params);
			$count = $this->getFirstResultFromQuery(getCountSQLPH($query), $params);
			if (is_object($result) && $count !== false) {
				return array(
					'data' => $result->result('array'),
					'totalCount' => $count
				);
			} else {
				return false;
			}
		} else {
			$result = $this->db->query($query, $params);
			if ( is_object($result) ) {
				return $result->result('array');
			} else {
				return false;
			}
		}
	}

	/**
	 * Загрузка списка доп. соглашений (используется в гриде на форме редактирования контракта)
	 */
	function loadWhsDocumentSupplyAdditionalShortList($filter) {
		// Документ на включение
		$q = "
			SELECT
				WhsDocumentUc_id,
				WhsDocumentUc_Num,
				WhsDocumentUc_Name,
				CONVERT(varchar(10), WhsDocumentUc_Date, 104) as WhsDocumentUc_Date
			FROM
				WhsDocumentUc WITH (NOLOCK)
			WHERE
				WhsDocumentUc_pid = :WhsDocumentSupply_id;
		";

		$result = $this->db->query($q, $filter);
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Загрузка данных для комбобокса "Номер ГК"
	 */
	function loadWhsDocumentSupplyCombo($data) 
	{		
		$filter = "(1=1)";
		$WhsDocumentType_ids = implode(',',json_decode($data['WhsDocumentType_ids']));
		$filter .= " and wds.WhsDocumentType_id IN ({$WhsDocumentType_ids})";
		
		if (!empty($data['WhsDocumentSupply_id'])) {
			$filter .= " and wds.WhsDocumentSupply_id = :WhsDocumentSupply_id";
		}
		else {
		
			if (!empty($data['Org_id'])) {
				$filter .= " and (wds.Org_cid = :Org_id OR wds.Org_pid = :Org_id OR wds.Org_sid = :Org_id)";
			}

			if ($data['WhsDocumentCostItemType_id'] > 0) {
				$filter .= " and wds.WhsDocumentCostItemType_id = :WhsDocumentCostItemType_id";
			}

			if ($data['DrugFinance_id'] > 0) {
				$filter .= " and wds.DrugFinance_id = :DrugFinance_id";
			}

			if (!empty($data['query'])) {
				$data['query'] = "%{$data['query']}%";
				$filter .= " and wds.WhsDocumentUc_Num like :query";
			}
			
		}
		
		$query = "
			SELECT
				wds.WhsDocumentSupply_id,
				wds.WhsDocumentUc_id,
				wds.WhsDocumentUc_Num,
				wds.WhsDocumentUc_Name,
                sup.Org_Name as Supplier_Name
			FROM
				v_WhsDocumentSupply wds WITH (NOLOCK)
                left join v_Org sup with(nolock) on sup.Org_id = wds.Org_sid
			where 
				{$filter}
		";
	
		$result = $this->db->query($query, $data);
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Загрузка данных для комбобокса "Контракт"
	 */
	function loadWhsDocumentSupplySecondCombo($data) {
		$filter = "(1=1)";
		$params = array();

		$WhsDocumentType_ids = implode(',',json_decode($data['WhsDocumentType_ids']));
		$filter .= " and WDS.WhsDocumentType_id IN ({$WhsDocumentType_ids})";

		if ($data['WhsDocumentSupply_id']) {
			$filter .= " and WDS.WhsDocumentSupply_id = :WhsDocumentSupply_id";
			$params['WhsDocumentSupply_id'] = $data['WhsDocumentSupply_id'];
		} else {
			if (!empty($data['Org_cid'])) {
				$filter .= " and WDS.Org_cid = :Org_cid";
				$params['Org_cid'] = $data['Org_cid'];
			}
			if (!empty($data['DrugFinance_id'])) {
				$filter .= " and WDS.DrugFinance_id = :DrugFinance_id";
				$params['DrugFinance_id'] = $data['DrugFinance_id'];
			}
			if (!empty($data['WhsDocumentCostItemType_id'])) {
				$filter .= " and WDS.WhsDocumentCostItemType_id = :WhsDocumentCostItemType_id";
				$params['WhsDocumentCostItemType_id'] = $data['WhsDocumentCostItemType_id'];
			}
			if (!empty($data['query'])) {
				$filter .= " and WDS.WhsDocumentUc_Num like :WhsDocumentUc_Num+'%'";
				$params['WhsDocumentUc_Num'] = $data['query'];
			}
		}

		$query = "
			select
				WDS.WhsDocumentSupply_id,
				WDS.WhsDocumentUc_Num as WhsDocumentSupply_Num,
				convert(varchar(10), WDS.WhsDocumentUc_Date, 104) as WhsDocumentSupply_Date,
				WDS.WhsDocumentSupply_ProtNum,
				WDPR.WhsDocumentProcurementRequest_id,
				WDPR.WhsDocumentUc_Name as WhsDocumentProcurementRequest_Name,
				DR.DrugRequest_id,
				DR.DrugRequest_Name
			from
				v_WhsDocumentSupply WDS with(nolock)
				left join v_WhsDocumentProcurementRequest WDPR with(nolock) on WDPR.WhsDocumentUc_id = WDS.WhsDocumentUc_pid
				outer apply(
					select top 1 DrugRequest_id
					from v_DrugRequestPurchaseSpec with(nolock)
					where WhsDocumentUc_id = WDPR.WhsDocumentUc_id
				) DRPS
				left join v_DrugRequest DR with(nolock) on DR.DrugRequest_id = DRPS.DrugRequest_id
			where
				{$filter}
		";
		//echo getDebugSQL($query, $params);exit;
		return $this->queryResult($query, $params);
	}

	/**
	 * Сохранение доп. соглашений из JSON
	 */
	function saveSupplyAdditionalFromJSON($data) {
		if (!empty($data['json_str']) && $data['WhsDocumentSupply_id'] > 0) {
			ConvertFromWin1251ToUTF8($data['json_str']);
			$dt = (array) json_decode($data['json_str']);

			foreach($dt as $record) {
				$record = (array) $record;
				array_walk($record, 'ConvertFromUTF8ToWin1251');

				$record['WhsDocumentUc_Date'] = !empty($record['WhsDocumentUc_Date']) ? join('-', array_reverse(explode('.', $record['WhsDocumentUc_Date']))) : 'null';
				$record['pmUser_id'] = $data['pmUser_id'];
				$record['WhsDocumentUc_pid'] = $data['WhsDocumentSupply_id'];
				$record['WhsDocumentType_id'] = 14;
				switch($record['state']) {
					case 'add':
						$q = "
							declare
								@WhsDocumentUc_id int,
								@Error_Code int,
								@Error_Message varchar(4000);
							begin try
								set nocount on;
								insert into WhsDocumentUc
								(
									WhsDocumentUc_pid,
									WhsDocumentUc_Num,
									WhsDocumentUc_Name,
									WhsDocumentType_id,
									WhsDocumentUc_Date,
									pmUser_insID,
									pmUser_updID,
									WhsDocumentUc_insDT,
									WhsDocumentUc_updDT
								)
								values (
									:WhsDocumentUc_pid,
									:WhsDocumentUc_Num,
									:WhsDocumentUc_Name,
									:WhsDocumentType_id,
									:WhsDocumentUc_Date,
									:pmUser_id,
									:pmUser_id,
									dbo.tzGetDate(),
									dbo.tzGetDate()
								)
								set nocount off;
								set @WhsDocumentUc_id = SCOPE_IDENTITY();
							end try

							begin catch
								set @Error_Code = ERROR_NUMBER();
								set @Error_Message = ERROR_MESSAGE();
							end catch

							select @WhsDocumentUc_id as WhsDocumentUc_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
						";
						/*$q = "
							insert into WhsDocumentUc (
								WhsDocumentUc_pid,
								WhsDocumentUc_Num,
								WhsDocumentUc_Name,
								WhsDocumentType_id,
								WhsDocumentUc_Date,
								pmUser_insID,
								pmUser_updID,
								WhsDocumentUc_insDT,
								WhsDocumentUc_updDT
							) values (
								:WhsDocumentUc_pid,
								:WhsDocumentUc_Num,
								:WhsDocumentUc_Name,
								:WhsDocumentType_id,
								:WhsDocumentUc_Date,
								:pmUser_id,
								:pmUser_id,
								dbo.tzGetDate(),
								dbo.tzGetDate()
							)
						";*/
						$result = $this->db->query($q, $record);
						$arr = $result->result('array');
						if (!empty($arr[0]['WhsDocumentUc_id'])) {
							$this->updateSupplyAdditionalPriceHistoryLink(array(
								'WhsDocumentUc_id' => $record['WhsDocumentUc_pid'],
								'WhsDocumentUc_sid' => $arr[0]['WhsDocumentUc_id'],
								'pmUser_id' => $record['pmUser_id']
							));
						}
						break;
					case 'edit':
						$q = "
							update
								WhsDocumentUc
							set
								WhsDocumentUc_Num = :WhsDocumentUc_Num,
								  WhsDocumentUc_Name = :WhsDocumentUc_Name,
								  WhsDocumentUc_Date = :WhsDocumentUc_Date,
								  pmUser_updID = :pmUser_id,
								  WhsDocumentUc_updDT = dbo.tzGetDate()
							where
								WhsDocumentUc_id = :WhsDocumentUc_id;
						";
						$this->db->query($q, $record);
						$response['updated'][] = $record['WhsDocumentUc_id'];
						break;
					case 'delete':
						$this->clearSupplyAdditionalPriceHistoryLink(array(
							'WhsDocumentUc_sid' => $record['WhsDocumentUc_id'],
							'pmUser_id' => $record['pmUser_id']
						));
						$q = "
							delete from
								WhsDocumentUc with(rowlock)
							where
								WhsDocumentUc_id = :WhsDocumentUc_id;
						";
						$this->db->query($q, $record);
						break;
				}
				//$this->db->query($q, $record);
			}
		}
	}

	/**
	 * Обновление ссылки на доп. соглашение в периодике цен по ГК
	 */
	function updateSupplyAdditionalPriceHistoryLink($data) {
		$query = "
			update
				WhsDocumentUcPriceHistory
			set
				WhsDocumentUc_sid = :WhsDocumentUc_sid,
				pmUser_updID = :pmUser_id,
				WhsDocumentUcPriceHistory_updDT = dbo.tzGetDate()
			where
				WhsDocumentUc_id = :WhsDocumentUc_id
				and convert(date,WhsDocumentUcPriceHistory_begDT) = convert(date,dbo.tzGetDate());
		";
		$this->db->query($query, $data);
	}

	/**
	 * Очистка ссылок на доп. соглашение в периодике цен по ГК
	 */
	function clearSupplyAdditionalPriceHistoryLink($data) {
		if (!empty($data['WhsDocumentUc_sid'])) {
			$query = "
			update
				WhsDocumentUcPriceHistory
			set
				WhsDocumentUc_sid = null,
				pmUser_updID = :pmUser_id
			where
				WhsDocumentUc_sid = :WhsDocumentUc_sid
		";
			$this->db->query($query, $data);
		}
	}

	/**
	 * Получение предельной цены (с учетом НДС) для конкретного медикамента
	 */
	function getMaxSalePrice($data) {
		$q = "
			declare
				@price float = null, -- цена производителя
				@is_narko int = null, -- является ли накркотиком (id из спр. YesNo)
				@wholesale float = null, -- процент оптовой наценки
				@retail float = null, -- процент розничной наценки
				@max_retail_price float = null, -- предельная розничная цена без НДС
				@max_wholesale_price float = null, -- предельная оптовая цена без НДС
				@supply_date date = :WhsDocumentUc_Date, -- дата гос контракта
				@drug_id bigint = :Drug_id, -- id медикамента (rls.Drug)
				@price_date varchar(10) = null; -- дата регистрации цены производителя

			if (@supply_date is null)
			begin
				set @supply_date = dbo.tzGetDate();
			end;

			-- получаем цену производителя
			set @price = (
			    select top 1
                    DrugSalePrice_Price
                from
                    rls.v_DrugSalePrice with(nolock)
                where
                    Drug_id = @drug_id and
                    (
                        DrugSalePrice_relDT is null or
                        DrugSalePrice_relDT <= @supply_date
                    )
                order by
                    DrugSalePrice_relDT desc
			);

			-- получаем дату регистрации цены производителя
			set @price_date = (
			    select top 1
                    convert(varchar(10),DrugSalePrice_relDT,104) as DrugSalePrice_relDT
                from
                    rls.v_DrugSalePrice with(nolock)
                where
                    Drug_id = @drug_id and
                    (
                        DrugSalePrice_relDT is null or
                        DrugSalePrice_relDT <= @supply_date
                    )
                order by
                    DrugSalePrice_relDT desc
			);

			-- выясняем является ли медикамент наркотиком
			select
				@is_narko = YesNo_id
			from
				YesNo with(nolock)
			where
				YesNo_Code = (
					select
						case
							when isnull(am.NARCOGROUPID, 0) > 0 then 1
							else 0
						end
					from
						rls.v_Drug d with (nolock)
						left join rls.v_DrugComplexMnn dcm with (nolock) on dcm.DrugComplexMnn_id = d.DrugComplexMnn_id
						left join rls.v_DrugComplexMnnName dcmn with (nolock) on dcmn.DrugComplexMnnName_id = dcm.DrugComplexMnnName_id
						left join rls.ACTMATTERS am with (nolock) on am.ACTMATTERS_ID = dcmn.ActMatters_id
					where
						Drug_id = @drug_id
				);

			-- получаем наценки
			select
				@wholesale = DrugMarkup_Wholesale,
				@retail = DrugMarkup_Retail
			from
				v_DrugMarkup with(nolock)
			where
				@price between DrugMarkup_MinPrice and DrugMarkup_MaxPrice and
				DrugMarkup_IsNarkoDrug = @is_narko and
				DrugMarkup_begDT <= @supply_date and
				(
					DrugMarkup_endDT is null or
					DrugMarkup_endDT >= @supply_date
				);

			-- расчитываем предельную цену с НДС (10%)
			set @max_retail_price = round((@price + (@price*@wholesale/100) + (@price*@retail/100))*1.1, 2);
			set @max_wholesale_price = round((@price + (@price*@wholesale/100))*1.1, 2);

			select
				@price as MakerPrice,
				@price_date as MakerPriceDate,
				@max_retail_price as MaxRetailPriceNDS,
				@max_wholesale_price as MaxWholeSalePriceNDS;
		";
		$r = $this->db->query($q, $data);
		if ( is_object($r) ) {
			$r = $r->result('array');
			if (isset($r[0])) {
				return $r;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	/**
	 * Сохранение контрагента, в том случае, если подобного ему еще нет в БД.
	 * Используется при подписании ГК.
	 */
	function saveContragent($data) {
		if (!isset($data['Org_id']) || !isset($data['ContragentType_Code'])) {
			return false;
		}
		$error = array();
		$contragent_id = null;
		$mzorg_id = null;

		//ищем контрагент
		$query = "
			select top 1
				Contragent_id
			from
				v_Contragent c with (nolock)
				left join v_ContragentType ct with(nolock) on ct.ContragentType_id = c.ContragentType_id
			where
				ct.ContragentType_Code = :ContragentType_Code
				and Org_id = :Org_id
				and Lpu_id is null
			order by
				Contragent_insDT desc;
		";
		$result = $this->getFirstResultFromQuery($query, array(
			'ContragentType_Code' => $data['ContragentType_Code'],
			'Org_id' => $data['Org_id']
		));
		if (!empty($result) && $result > 0) {
			$contragent_id = $result;
		}

		//если не находим контрагента, то добавляем его в бд
		if (count($error) == 0 && $contragent_id <= 0) {
			$query = "
				declare
					@Contragent_id bigint = null,
					@ContragentType_id bigint,
					@Contragent_Code int,
					@Contragent_Name varchar(100),
					@ErrCode int,
					@ErrMessage varchar(4000);

				set @ContragentType_id = (select ContragentType_id from v_ContragentType with(nolock) where ContragentType_Code = :ContragentType_Code);
				set @Contragent_Code = (select isnull(max(Contragent_Code),10)+1 from v_Contragent with(nolock));
				set @Contragent_Name = (select isnull(Org_Name,'Контрагент') from v_Org with(nolock) where Org_id = :Org_id);

				exec p_Contragent_ins
					@Server_id = :Server_id,
					@Contragent_id = @Contragent_id output,
					@Lpu_id = null,
					@ContragentType_id = @ContragentType_id,
					@Contragent_Code = @Contragent_Code,
					@Contragent_Name = @Contragent_Name,
					@Org_id = :Org_id,
					@OrgFarmacy_id = null,
					@LpuSection_id = null,
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMessage output;

				select @Contragent_id as Contragent_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			";
			$result = $this->getFirstRowFromQuery($query, array(
				'Server_id' => $data['Server_id'],
				'ContragentType_Code' => $data['ContragentType_Code'],
				'Org_id' => $data['Org_id'],
				'pmUser_id' => $this->getpmUser_id()
			));

			if (!empty($result['Contragent_id'])) {
				$contragent_id = $result['Contragent_id'];
			} else {
				$error[] = !empty($result['Error_Msg']) ? $result['Error_Msg'] : 'Ошибка при сохранении контрагента';
			}
		}

		return array(
			'Contragent_id' => $contragent_id,
			'Error_Msg' => count($error) > 0 ? $error[0] : null
		);
	}

	/**
	 * Сохранение связи контрагента с организацией, в том случае если еще нет в БД
	 * Используется при подписании ГК.
	 */
	function saveContragentOrg($data) {
		if (!isset($data['Org_id']) || !isset($data['Contragent_id'])) {
			return false;
		}
		$error = array();
		$contragentorg_id = null;

		//проверяем связь контрагента с минздравом
		if ($data['Org_id'] > 0 && $data['Contragent_id'] > 0) {
			//ищем существующую связь
			$query = "
				select
					count(ContragentOrg_id) as cnt
				from
					v_ContragentOrg with (nolock)
				where
					Contragent_id = :Contragent_id and
					Org_id = :Org_id
			";
			$mz_link = $this->getFirstResultFromQuery($query, array(
				'Contragent_id' => $data['Contragent_id'],
				'Org_id' => $data['Org_id']
			));

			if (empty($mz_link) || $mz_link < 1) {
				//добавляем связь
				$query = "
					declare
						@ContragentOrg_id bigint,
						@Error_Code bigint,
						@Error_Message varchar(4000);
					exec p_ContragentOrg_ins
						@ContragentOrg_id = @ContragentOrg_id output,
						@Contragent_id = :Contragent_id,
						@Org_id = :Org_id,
						@pmUser_id = :pmUser_id,
						@Error_Code = @Error_Code output,
						@Error_Message = @Error_Message output;
					select @ContragentOrg_id as ContragentOrg_id, @Error_Code as Error_Code, @Error_Message as Error_Msg
				";
				$result = $this->getFirstRowFromQuery($query, array(
					'Contragent_id' => $data['Contragent_id'],
					'Org_id' => $data['Org_id'],
					'pmUser_id' => $this->getpmUser_id()
				));

				if (empty($result['ContragentOrg_id'])) {
					$error[] = !empty($result['Error_Msg']) ? $result['Error_Msg'] : 'Ошибка при сохранении связи организации и контрагента';
				} else {
					$contragentorg_id = $result['ContragentOrg_id'];
				}
			}
		}

		return array(
			'ContragentOrg_id' => $contragentorg_id,
			'Error_Msg' => count($error) > 0 ? $error[0] : null
		);
	}

	/**
	 *  Генерация номера для ГК
	 */
	function generateNum($data) {
		$query = "
			select
				isnull(max(cast(WhsDocumentUc_Num as bigint)),0) + 1 as WhsDocumentUc_Num
			from
				v_WhsDocumentUc with(nolock)
			where
				WhsDocumentUc_Num not like '%.%' and
				WhsDocumentUc_Num not like '%,%' and
				isnumeric(WhsDocumentUc_Num) = 1 and
				len(WhsDocumentUc_Num) <= 18 and
				WhsDocumentType_id in (
					select WhsDocumentType_id from with(nolock) v_WhsDocumentType where WhsDocumentType_Code in (3,6,18) -- 3 - Контракт на поставку; 6 - Контракт на поставку и отпуск; 18 - Контракт ввода остатков.
				);
		";

		$result = $this->db->query($query);
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 *	Получение списка параметров хранимой процедуры
	 */
	function getStoredProcedureParamsList($sp, $schema) {
		$query = "
			select
				ps.[name],
				ps.[is_output],
				t.[name] as types_name,
				t.[max_length] as types_max_length,
				t.[precision] as types_precision,
				t.[scale] as types_scale
			from
				sys.all_parameters ps with(nolock)
				left join sys.types t with(nolock) on t.system_type_id = ps.system_type_id and t.user_type_id = ps.user_type_id
			where
				ps.[object_id] = (
					select
						top 1 [object_id]
					from
						sys.objects with(nolock)
					where
						[type_desc] = 'SQL_STORED_PROCEDURE' and
						[name] = :name and
						(
							:schema is null or
							[schema_id] = (select top 1 [schema_id] from sys.schemas where [name] = :schema)
						)
				) and
				ps.[name] not in ('@pmUser_id', '@Error_Code', '@Error_Message', '@isReloadCount') and
				t.[is_user_defined] = 0;
		";

		$queryParams = array(
			'name' => $sp,
			'schema' => $schema
		);

		$result = $this->db->query($query, $queryParams);

		if ( !is_object($result) ) {
			return false;
		}

		$outputData = array();
		$response = $result->result('array');

		foreach ( $response as $row ) {
			$name = str_replace('@', '', $row['name']);
			$type = $row['types_name'];

			switch($row['types_name']) {
				case 'varchar':
					$type .= "({$row['types_max_length']})";
					break;
				case 'numeric':
				case 'decimal':
					$type .= "({$row['types_precision']},{$row['types_scale']})";
					break;
			}

			$outputData[$name] = array(
				'is_output' => ($row['is_output'] == 1),
				'type' => $type
			);
		}

		return $outputData;
	}

	/**
	 * Сохранение произвольного обьекта (без повреждения предыдущих данных).
	 */
	function saveObject($object_name, $data) {
		$schema = "dbo";

		//при необходимости выделяем схему из имени обьекта
		$name_arr = explode('.', $object_name);
		if (count($name_arr) > 1) {
			$schema = $name_arr[0];
			$object_name = $name_arr[1];
		}

		$key_field = !empty($data['key_field']) ? $data['key_field'] : "{$object_name}_id";

		if (!isset($data[$key_field])) {
			$data[$key_field] = null;
		}

		$action = $data[$key_field] > 0 ? "upd" : "ins";
		$proc_name = "p_{$object_name}_{$action}";
		$params_list = $this->getStoredProcedureParamsList($proc_name, $schema);
		$save_data = array();

		$query_declare_part = "";
		$query_params_part = "";
		$query_select_part = "";

		//получаем существующие данные если апдейт
		if ($action == "upd") {
			$query = "
				select
					*
				from
					{$schema}.v_{$object_name} with (nolock)
				where
					{$key_field} = :id;
			";
			$result = $this->getFirstRowFromQuery($query, array(
				'id' => $data[$key_field]
			));
			if (is_array($result)) {
				foreach($result as $key => $value) {
					if (array_key_exists($key, $params_list)) {
						$save_data[$key] = $value;
					}
				}
			}
		}

		foreach($data as $key => $value) {
			if (array_key_exists($key, $params_list)) {
				$save_data[$key] = $value;
			}
		}

		foreach($save_data as $key => $value) {
			if (array_key_exists($key, $params_list)) {
				//перобразуем даты в строки
				if (is_object($save_data[$key]) && get_class($save_data[$key]) == 'DateTime') {
					$save_data[$key] = $save_data[$key]->format('Y-m-d H:i:s');
				}

				if ($params_list[$key]['is_output']) {
					$query_declare_part .= "@{$key} {$params_list[$key]['type']} = :{$key}, ";
					$query_params_part .= "@{$key} = @{$key} output, ";
					$query_select_part .= "@{$key} as {$key}, ";
				} else {
					$query_params_part .= "@{$key} = :{$key}, ";
				}
			}
		}

		if (!empty($key_field) && $params_list[$key_field] && !$params_list[$key_field]['is_output']) {
			$query_select_part .= ":{$key_field} as {$key_field}, ";
		}

		$save_data['pmUser_id'] = isset($data['pmUser_id']) ? $data['pmUser_id'] : null;

		$query = "
			declare
				{$query_declare_part}
				@Error_Code int,
				@Error_Message varchar(4000);

			execute {$schema}.{$proc_name}
				{$query_params_part}
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;

			select {$query_select_part} @Error_Code as Error_Code, @Error_Message as Error_Msg;
		";

		if (isset($data['debug_query'])) {
			print getDebugSQL($query, $save_data);
		}
		$result = $this->getFirstRowFromQuery($query, $save_data);
		if ($result && is_array($result)) {
			if(empty($result['Error_Msg'])) {
				$result['success'] = true;
			}
			return $result;
		} else {
			return array('Error_Msg' => 'При сохранении произошла ошибка');
		}
	}

	/**
	 * Копирование произвольного обьекта.
	 */
	function copyObject($object_name, $data) {
		$schema = "dbo";

		//при необходимости выделяем схему из имени обьекта
		$name_arr = explode('.', $object_name);
		if (count($name_arr) > 1) {
			$schema = $name_arr[0];
			$object_name = $name_arr[1];
		}

		$key_field = !empty($data['key_field']) ? $data['key_field'] : "{$object_name}_id";

		if (!isset($data[$key_field])) {
			return array('Error_Message' => 'Не указано значение ключевого поля');
		}

		$proc_name = "p_{$object_name}_ins";
		$params_list = $this->getStoredProcedureParamsList($proc_name, $schema);
		$save_data = array();
		$query_part = "";

		//получаем данные оригинала
		$query = "
			select
				*
			from
				{$schema}.v_{$object_name} with (nolock)
			where
				{$key_field} = :id;
		";
		$result = $this->getFirstRowFromQuery($query, array(
			'id' => $data[$key_field]
		));
		if (is_array($result)) {
			foreach($result as $key => $value) {
				if (array_key_exists($key, $params_list)) {
					$save_data[$key] = $value;
				}
			}
		}


		foreach($data as $key => $value) {
			if (array_key_exists($key, $params_list)) {
				$save_data[$key] = $value;
			}
		}

		foreach($save_data as $key => $value) {
			if (array_key_exists($key, $params_list) && $key != $key_field) {
				//перобразуем даты в строки
				if (is_object($save_data[$key]) && get_class($save_data[$key]) == 'DateTime') {
					$save_data[$key] = $save_data[$key]->format('Y-m-d H:i:s');
				}
				$query_part .= "@{$key} = :{$key}, ";
			}
		}

		$save_data['pmUser_id'] = isset($data['pmUser_id']) ? $data['pmUser_id'] : null;

		$query = "
			declare
				@{$key_field} bigint = null,
				@Error_Code int,
				@Error_Message varchar(4000);

			execute {$schema}.{$proc_name}
				@{$key_field} = @{$key_field} output,
				{$query_part}
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;

			select @{$key_field} as {$key_field}, @Error_Code as Error_Code, @Error_Message as Error_Msg;
		";

		if (isset($data['debug_query'])) {
			print getDebugSQL($query, $save_data);
		}
		$result = $this->getFirstRowFromQuery($query, $save_data);
		if ($result && is_array($result)) {
			if($result[$key_field] > 0) {
				$result['success'] = true;
			}
			return $result;
		} else {
			return array('Error_Msg' => 'При копировании произошла ошибка');
		}
	}

	/**
	 * Удаление произвольного обьекта.
	 */
	function deleteObject($object_name, $data) {
		$query = "
			declare
				@Error_Code int,
				@Error_Message varchar(4000);

			execute dbo.p_{$object_name}_del
				@{$object_name}_id = :{$object_name}_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;

			select @Error_Code as Error_Code, @Error_Message as Error_Message;
		";

		$result = $this->getFirstRowFromQuery($query, $data);
		if ($result && is_array($result)) {
			if(empty($result['Error_Message'])) {
				$result['success'] = true;
			}
			return $result;
		} else {
			return array('Error_Message' => 'При удалении произошла ошибка');
		}
	}

	/**
	 * Получение идентификатора обьекта по коду
	 */
	function getObjectIdByCode($object_name, $code) {
		$query = "
			select top 1
				{$object_name}_id
			from
				v_{$object_name} with (nolock)
			where
				{$object_name}_Code = :code;
		";
		$result = $this->getFirstResultFromQuery($query, array(
			'code' => $code
		));

		return $result && $result > 0 ? $result : false;
	}

	/**
	 *  Получение идентификатора организации соответствующей Минздраву.
	 */
	function getMinzdravDloOrgId() {
		$query = "select dbo.GetMinzdravDloOrgId() as Org_id;";
		$org_id = $this->getFirstResultFromQuery($query);
		return $org_id;
	}

    /**
     * Загрузка синонима
     */
    function loadWhsDocumentSupplySpecDrug($data) {
        $query = "
			select
                wdssd.WhsDocumentSupplySpecDrug_id,
                wdss.WhsDocumentSupplySpec_id,
                wdss.WhsDocumentSupply_id,
                wdssd.Drug_id,
                wdssd.Drug_sid,
                wdssd.WhsDocumentSupplySpecDrug_Coeff,
                wdssd.WhsDocumentSupplySpecDrug_Price,
                wdssd.WhsDocumentSupplySpecDrug_PriceSyn
            from
                v_WhsDocumentSupplySpecDrug wdssd with (nolock)
                left join v_WhsDocumentSupplySpec wdss with (nolock) on wdss.WhsDocumentSupplySpec_id = wdssd.WhsDocumentSupplySpec_id
            where
                wdssd.WhsDocumentSupplySpecDrug_id = :WhsDocumentSupplySpecDrug_id
		";

        $result = $this->db->query($query, $data);
        if ( is_object($result) ) {
            return $result->result('array');
        } else {
            return false;
        }
    }

    /**
     * Получение списка синонимов
     */
    function loadWhsDocumentSupplySpecDrugList($filter) {
        $where = array();
        $params = array();

        if ($filter['WhsDocumentSupply_id'] > 0) {
            $where[] = 'wdss.WhsDocumentSupply_id = :WhsDocumentSupply_id';
            $params['WhsDocumentSupply_id'] = $filter['WhsDocumentSupply_id'];
        }

        if ($filter['WhsDocumentSupplySpec_id'] > 0) {
            $where[] = 'wdssd.WhsDocumentSupplySpec_id = :WhsDocumentSupplySpec_id';
            $params['WhsDocumentSupplySpec_id'] = $filter['WhsDocumentSupplySpec_id'];
        }

        $where_clause = implode(' and ', $where);
        if (strlen($where_clause) > 0) {
            $where_clause = "
				where
				    -- where
					{$where_clause}
					-- end where
			";
        }

        $query = "
			select
			    -- select
                wdssd.WhsDocumentSupplySpecDrug_id,
                wds.WhsDocumentUc_Num,
                d.Drug_Name,
                wdss.WhsDocumentSupplySpec_KolvoUnit,
                wdssd.WhsDocumentSupplySpecDrug_Price,
                wdssd.WhsDocumentSupplySpecDrug_Coeff,
                d_s.Drug_Name as Drug_NameSyn,
                isnull(wdss.WhsDocumentSupplySpec_KolvoUnit * wdssd.WhsDocumentSupplySpecDrug_Coeff, 0) as WhsDocumentSupplySpecDrug_KolvoUnit,
                wdssd.WhsDocumentSupplySpecDrug_PriceSyn
                -- end select
            from
                -- from
                v_WhsDocumentSupplySpecDrug wdssd with (nolock)
                left join v_WhsDocumentSupplySpec wdss with (nolock) on wdss.WhsDocumentSupplySpec_id = wdssd.WhsDocumentSupplySpec_id
                left join v_WhsDocumentSupply wds with (nolock) on wds.WhsDocumentSupply_id = wdss.WhsDocumentSupply_id
                left join rls.v_Drug d with(nolock) on d.Drug_id = wdssd.Drug_id
                left join rls.v_Drug d_s with(nolock) on d_s.Drug_id = wdssd.Drug_sid
                -- end from
            {$where_clause}
            order by
                -- order by
                wds.WhsDocumentUc_Num
                -- end order by
		";

        $result = $this->db->query(getLimitSQLPH($query, $filter['start'], $filter['limit']), $params);
        $count = $this->getFirstResultFromQuery(getCountSQLPH($query), $params);
        if (is_object($result) && $count !== false) {
            return array(
                'data' => $result->result('array'),
                'totalCount' => $count
            );
        } else {
            return false;
        }
    }

    /**
     * Получение сопутствующих данных для синонима
     */
    function getWhsDocumentSupplySpecDrugContext($data) {
        $query = "
			declare
                @Date date = :Date;

            if (@Date is null)
            begin
                set @Date = dbo.tzGetDate();
            end;


            select
                d.DrugComplexMnn_id,
                isnull(dn.DrugNomen_Code, 'Нет') as DrugNomen_Code,
                am.RUSNAME as Actmatters_Name,
                (case when dsp.Price is not null then 1 else 0 end) as IsJnvlp,
                dsp.Price as MakerPrice,
                cast(round((dsp.Price + (dsp.Price*dm.Wholesale/100) + (dsp.Price*dm.Retail/100))*1.1, 2) as decimal(10,2)) as MaxRetailPriceNDS,
                cast(round((dsp.Price + (dsp.Price*dm.Wholesale/100))*1.1, 2) as decimal(10,2)) as MaxWholeSalePriceNDS
            from
                rls.v_Drug d with(nolock)
                left join rls.v_Nomen n with(nolock) on n.NOMEN_ID = d.Drug_id
                left join rls.v_DrugComplexMnn dcm with(nolock) on dcm.DrugComplexMnn_id = d.DrugComplexMnn_id
                left join rls.v_DrugComplexMnnName dcmn with(nolock) on dcmn.DrugComplexMnnName_id = dcm.DrugComplexMnnName_id
                left join rls.v_ACTMATTERS am with(nolock) on am.ACTMATTERS_ID = dcmn.ACTMATTERS_id
                outer apply (
                    select top 1
                        i_dn.DrugNomen_Code
                    from
                        rls.v_DrugNomen i_dn with (nolock)
                    where
                        i_dn.Drug_id = d.Drug_id
                    order by
                        i_dn.DrugNomen_id
                ) dn
                outer apply (
                    select top 1
                        i_dsp.DrugSalePrice_Price as Price
                    from
                        rls.v_DrugSalePrice i_dsp with (nolock)
                    where
                        i_dsp.Drug_id = d.Drug_id and
                        (
                            i_dsp.DrugSalePrice_relDT is null or
                            i_dsp.DrugSalePrice_relDT <= @Date
                        )
                    order by
                        i_dsp.DrugSalePrice_relDT desc
                ) dsp
                outer apply (
                    select (case when isnull(am.NARCOGROUPID, 0) = 2 then 1 else 0 end) as Code
                ) IsNarko
                outer apply (
                    select
                        DrugMarkup_Wholesale as Wholesale,
                        DrugMarkup_Retail as Retail
                    from
                        v_DrugMarkup dm with(nolock)
                        left join v_YesNo is_narko with(nolock) on is_narko.YesNo_id = DrugMarkup_IsNarkoDrug
                    where
                        dsp.Price between DrugMarkup_MinPrice and DrugMarkup_MaxPrice and
                        isnull(is_narko.YesNo_Code, 0) = IsNarko.Code and
                        (
                            dsp.Price is null or (
                                DrugMarkup_begDT <= @Date and
                                (
                                    DrugMarkup_endDT is null or
                                    DrugMarkup_endDT >= @Date
                                )
                            )
                        )
                ) dm
            where
                d.Drug_id = :Drug_id;
		";

        //print getDebugSQL($query, $data);
        $result = $this->db->query($query, $data);
        if ( is_object($result) ) {
            return $result->result('array');
        } else {
            return false;
        }
    }

    /**
     * Загрузка данных для комбобокса "Контракт" на формах ввода синонимов
     */
    function loadSynonymSupplyCombo($filter) {
        $where = array();
        $params = array();

        if ($filter['WhsDocumentSupply_id'] > 0) {
            $where[] = 'wds.WhsDocumentSupply_id = :WhsDocumentSupply_id';
            $params['WhsDocumentSupply_id'] = $filter['WhsDocumentSupply_id'];
        } else {
            if (!empty($filter['query'])) {
                $where[] = 'wds.WhsDocumentUc_Name like :WhsDocumentUc_Name';
                $params['WhsDocumentUc_Name'] = '%'.$filter['query'].'%';
            }
        }

        $where_clause = implode(' and ', $where);
        if (strlen($where_clause) > 0) {
            $where_clause = "
				where
					{$where_clause}
			";
        }

        $query = "
            select top 100
                wds.WhsDocumentSupply_id,
                wds.WhsDocumentUc_Name,
                isnull(df.DrugFinance_Name, '') as DrugFinance_Name,
                isnull(wdcit.WhsDocumentCostItemType_Name, '') as WhsDocumentCostItemType_Name,
                isnull(o_s.Org_Name, '') as Supplier_Name,
                convert(varchar(10), wds.WhsDocumentUc_Date, 104) as WhsDocumentUc_Date
            from
                v_WhsDocumentSupply wds with (nolock)
                left join v_Drugfinance df with(nolock) on df.DrugFinance_id = wds.DrugFinance_id
                left join v_WhsDocumentCostItemType wdcit with(nolock) on wdcit.WhsDocumentCostItemType_id = wds.WhsDocumentCostItemType_id
                left join v_Org o_s with(nolock) on o_s.Org_id = wds.Org_sid
            {$where_clause}
            order by
                WhsDocumentUc_Name
		";

        $result = $this->db->query($query, $params);
        if ( is_object($result) ) {
            return $result->result('array');
        } else {
            return false;
        }
    }

    /**
     * Загрузка данных для комбобокса "Медикамент (из контракта)" на формах ввода синонимов
     */
    function loadSynonymSupplySpecCombo($filter) {
        $where = array();
        $params = array();

        if ($filter['WhsDocumentSupplySpec_id'] > 0) {
            $where[] = 'wdss.WhsDocumentSupplySpec_id = :WhsDocumentSupplySpec_id';
            $params['WhsDocumentSupplySpec_id'] = $filter['WhsDocumentSupplySpec_id'];
        } else {
            $where[] = 'wdss.Drug_id is not null';
            if ($filter['WhsDocumentSupply_id'] > 0) {
                $where[] = 'wdss.WhsDocumentSupply_id = :WhsDocumentSupply_id';
                $params['WhsDocumentSupply_id'] = $filter['WhsDocumentSupply_id'];
            }
            if (!empty($filter['query'])) {
                $where[] = 'd.Drug_Name like :Drug_Name';
                $params['Drug_Name'] = '%'.$filter['query'].'%';
            }
        }

        $where_clause = implode(' and ', $where);
        if (strlen($where_clause) > 0) {
            $where_clause = "
				where
					{$where_clause}
			";
        }

        $query = "
            select top 100
                wdss.WhsDocumentSupplySpec_id,
                d.Drug_id,
                d.Drug_Name,
                dcmn.Actmatters_id,
                wdss.WhsDocumentSupplySpec_PriceNDS,
                wdss.WhsDocumentSupplySpec_KolvoUnit
            from
                v_WhsDocumentSupplySpec wdss with (nolock)
                left join rls.v_Drug d with (nolock) on d.Drug_id = wdss.Drug_id
                left join rls.v_DrugComplexMnn dcm with (nolock) on dcm.DrugComplexMnn_id = d.DrugComplexMnn_id
                left join rls.v_DrugComplexMnnName dcmn with (nolock) on dcmn.DrugComplexMnnName_id = dcm.DrugComplexMnnName_id
            {$where_clause}
            order by
                d.Drug_Name
		";

        $result = $this->db->query($query, $params);
        if ( is_object($result) ) {
            return $result->result('array');
        } else {
            return false;
        }
    }

    /**
     * Загрузка данных для комбобокса "Медикамент" на формах ввода синонимов
     */
    function loadSynonymDrugCombo($filter) {
        $where = array();
        $params = array();

        if ($filter['Drug_id'] > 0) {
            $where[] = 'd.Drug_id = :Drug_id';
            $params['Drug_id'] = $filter['Drug_id'];
        } else {
            if (!empty($filter['query'])) {
                $where[] = 'd.Drug_Name like :Drug_Name';
                $params['Drug_Name'] = $filter['query'].'%';
            }
            if (!empty($filter['Actmatters_id'])) {
                $where[] = 'dcmn.Actmatters_id = :Actmatters_id';
                $params['Actmatters_id'] = $filter['Actmatters_id'];
            }
        }

        $where_clause = implode(' and ', $where);
        if (strlen($where_clause) > 0) {
            $where_clause = "
				where
					{$where_clause}
			";
        }

        $query = "
            select top 250
                d.Drug_id,
                d.Drug_Name
            from
                rls.v_Drug d with (nolock)
                left join rls.v_DrugComplexMnn dcm with (nolock) on dcm.DrugComplexMnn_id = d.DrugComplexMnn_id
                left join rls.v_DrugComplexMnnName dcmn with (nolock) on dcmn.DrugComplexMnnName_id = dcm.DrugComplexMnnName_id
            {$where_clause}
            order by
                d.Drug_Name
		";

        $result = $this->db->query($query, $params);
        if ( is_object($result) ) {
            return $result->result('array');
        } else {
            return false;
        }
    }

    /**
     *  Определение факта использования синонима в документах учета или рецептах
     * Возвращает количество документов/рецептов, а также данные первого документа или рецепта
     */
    function checkSynonymUsage($data) {
        $doc_data = array(
            'cnt' => 0
        );
        $supply_id = null;
        $drug_id = null;

        if (!empty($data['WhsDocumentSupplySpecDrug_id'])) {
            $query = "
                select
                    wdss.WhsDocumentSupply_id,
                    wdssd.Drug_sid
                from
                    v_WhsDocumentSupplySpecDrug wdssd with (nolock)
                    left join v_WhsDocumentSupplySpec wdss with (nolock) on wdss.WhsDocumentSupplySpec_id = wdssd.WhsDocumentSupplySpec_id
                where
                    wdssd.WhsDocumentSupplySpecDrug_id = :WhsDocumentSupplySpecDrug_id;
            ";
            $result = $this->getFirstRowFromQuery($query< array(
                'WhsDocumentSupplySpecDrug_id' => $data['WhsDocumentSupplySpecDrug_id']
            ));
            if (!empty($result['WhsDocumentSupply_id'])) {
                $supply_id = $result['WhsDocumentSupply_id'];
                $drug_id = $result['Drug_sid'];
            }
        }

        if ($supply_id > 0 && $drug_id > 0) {
            //получение списка документов и рецептов в которых используется данный синоним
            $query = "
                select
                    convert(varchar(10), du.DocumentUc_setDate, 104) as Date,
                    du.DocumentUc_Num as Num,
                    o.Org_Name as Name
                from
                    v_DocumentUcStr dus with (nolock)
                    left join v_DocumentUc du with (nolock) on du.DocumentUc_id = dus.DocumentUc_id
                    left join v_DrugDocumentType ddt with (nolock) on ddt.DrugDocumentType_id = du.DrugDocumentType_id
                    left join v_DrugShipmentLink dsl with (nolock) on dsl.DocumentUcStr_id = dus.DocumentUcStr_id
                    left join v_DrugShipment ds with (nolock) on ds.DrugShipment_id = dsl.DrugShipment_id
                    left join v_Org o with (nolock) on o.Org_id = du.Org_id
                where
                    dus.Drug_id = :Drug_id and
                    WhsDocumentSupply_id = :WhsDocumentSupply_id
                union all
                select
                    convert(varchar(10), ro.EvnRecept_setDate, 104) as Date,
                    (
                        isnull(ro.EvnRecept_Ser, '') +
                        isnull(' ' + ro.EvnRecept_Num, '')
                    ) as Num,
                    (
                        isnull(ps.Person_SurName, '') +
                        isnull(' ' + ps.Person_FirName, '') +
                        isnull(' ' + ps.Person_SecName, '')
                    ) as Name
                from
                    v_DocumentUcStr dus with (nolock)
                    left join v_DocumentUcStr dus2 with (nolock) on dus2.DocumentUcStr_id = dus.DocumentUcStr_oid
                    left join v_DocumentUc du with (nolock) on du.DocumentUc_id = dus.DocumentUc_id
                    left join v_DrugDocumentType ddt with (nolock) on ddt.DrugDocumentType_id = du.DrugDocumentType_id
                    left join v_DrugShipmentLink dsl with (nolock) on dsl.DocumentUcStr_id = dus2.DocumentUcStr_id
                    left join v_DrugShipment ds with (nolock) on ds.DrugShipment_id = dsl.DrugShipment_id
                    left join v_ReceptOtovUnSub ro with (nolock) on ro.ReceptOtov_id = dus.ReceptOtov_id
                    left join v_PersonState_all ps with (nolock) on ps.Person_id = ro.Person_id
                where
                    dus.Drug_id = :Drug_id and
                    ddt.DrugDocumentType_SysNick = 'DocReal' and
                    ds.WhsDocumentSupply_id = :WhsDocumentSupply_id and
                    ro.ReceptOtov_id is not null

            ";
            $result = $this->db->query($query, array(
                'WhsDocumentSupply_id' => $supply_id,
                'Drug_id' => $drug_id
            ));
            if (is_object($result)) {
                $doc_array = $result->result('array');
                if (count($doc_array) > 0) {
                    $doc_data['cnt'] = count($doc_array);
                    $doc_data['date'] = $doc_array[0]['Date'];
                    $doc_data['num'] = $doc_array[0]['Num'];
                    $doc_data['name'] = $doc_array[0]['Name'];
                }
            }
        }

        return $doc_data;
    }

    /**
     * Загрузка списка позиций лота
     */
    function loadWhsDocumentProcurementRequestSpecCombo($filter) {
        $where = array();
        $params = array();

        if ($filter['WhsDocumentProcurementRequestSpec_id'] > 0) {
            $where[] = 'wdprs.WhsDocumentProcurementRequestSpec_id = :WhsDocumentProcurementRequestSpec_id';
            $params['WhsDocumentProcurementRequestSpec_id'] = $filter['WhsDocumentProcurementRequestSpec_id'];
        } else {
            if (!empty($filter['query'])) {
                $where[] = 'dn.Drug_Name like :Drug_Name';
                $params['Drug_Name'] = $filter['query'].'%';
            }
            if (!empty($filter['WhsDocumentProcurementRequest_id'])) {
                $where[] = 'wdprs.WhsDocumentProcurementRequest_id = :WhsDocumentProcurementRequest_id';
                $params['WhsDocumentProcurementRequest_id'] = $filter['WhsDocumentProcurementRequest_id'];
            }
            if (!empty($filter['Org_id'])) {
                $where[] = 'wdpr.Org_aid = :Org_id';
                $params['Org_id'] = $filter['Org_id'];
            }
        }

        $where_clause = implode(' and ', $where);
        if (strlen($where_clause) > 0) {
            $where_clause = "
				where
					{$where_clause}
			";
        }

        $query = "
            select top 250
                wdprs.WhsDocumentProcurementRequestSpec_id,
                dn.Drug_Name,
                wdprs.DrugComplexMnn_id,
                tn.TRADENAMES_ID as Tradenames_id,
                tn.NAME as Tradenames_Name,
                wdprs.Okei_id,
                wdprs.WhsDocumentProcurementRequestSpec_Kolvo,
                wdprs.GoodsUnit_id,
                wdprs.WhsDocumentProcurementRequestSpec_Count,
                ct.NAME as Country_Name
            from
                v_WhsDocumentProcurementRequestSpec wdprs with (nolock)
                left join rls.v_DrugComplexMnn dcm with (nolock) on dcm.DrugComplexMnn_id = wdprs.DrugComplexMnn_id
                left join rls.v_TRADENAMES tn with (nolock) on tn.TRADENAMES_ID = wdprs.Tradenames_id
                left join rls.v_COUNTRIES ct with (nolock) on ct.COUNTRIES_ID = wdprs.COUNTRIES_ID
                left join v_WhsDocumentProcurementRequest wdpr with (nolock) on wdprs.WhsDocumentProcurementRequest_id = wdpr.WhsDocumentProcurementRequest_id
                outer apply (
                    select
                        (case
                            when wdprs.DrugComplexMnn_id is not null then dcm.DrugComplexMnn_RusName
                            else tn.NAME
                        end) as Drug_Name
                ) dn
            {$where_clause}
            order by
                dn.Drug_Name
		";

        $result = $this->db->query($query, $params);
        if ( is_object($result) ) {
            return $result->result('array');
        } else {
            return false;
        }
    }

    /**
     * Загрузка списка МНН
     */
    function loadActmattersCombo($filter) {
        $where = array();
        $params = array();

        //$filter['DrugComplexMnn_id'] = 458654;
        //$filter['DrugComplexMnn_id'] = 476796;

        if (!empty($filter['Actmatters_id'])) {
            $where[] = "am.ACTMATTERS_id = :Actmatters_id";
            $params['Actmatters_id'] = $filter['Actmatters_id'];
        } else {
            if (!empty($filter['query'])) {
                $where[] = "am.RUSNAME like :RUSNAME";
                $params['RUSNAME'] = '%'.$filter['query'].'%';
            }
        }

        if (!empty($filter['DrugComplexMnn_id']) && empty($filter['Actmatters_id'])) {
            $query = "
                select
                    dcm.DrugComplexMnn_id,
                    dcmn.ACTMATTERS_id as Actmatters_id,
                    am.RUSNAME as Actmatters_Name
                from
                    rls.v_DrugComplexMnn dcm with (nolock)
                    left join rls.v_DrugComplexMnnName dcmn with (nolock) on dcmn.DrugComplexMnnName_id = dcm.DrugComplexMnnName_id
                    left join rls.ACTMATTERS am with (nolock) on am.ACTMATTERS_ID = dcmn.ACTMATTERS_id
                where
                    dcm.DrugComplexMnn_id = :DrugComplexMnn_id;
            ";
            $mnn_data =  $this->getFirstRowFromQuery($query, array(
                'DrugComplexMnn_id' => $filter['DrugComplexMnn_id']
            ));

            $where_str = "";

            //если наименование МНН сложное (есть + в названии), делим его на части и добавляем в список условий
            if (!empty($mnn_data['Actmatters_Name']) && strpos($mnn_data['Actmatters_Name'], '+') !== false) {
                $part_array = preg_split('/\+|\[|\]/', $mnn_data['Actmatters_Name']); //делим строку по символам "+", "[", "]"
                for ($i = 0; $i < count($part_array); $i++) {
                    $part_array[$i] = trim(preg_replace('/\*/', '', $part_array[$i]));
                    if (!empty($part_array[$i])) {
                        $part_array[$i] = "amn.TrimName = '{$part_array[$i]}'";
                    } else {
                        unset($part_array[$i]);
                    }
                }
                $where_str = join($part_array, " or ");
            }

            //если есть конкретный идентификатор МНН, добавляем его в список условий
            if (!empty($mnn_data['Actmatters_id'])) {
                if (!empty($where_str)) {
                    $where_str .= " or ";
                }
                $where_str .= "am.ACTMATTERS_ID = :Actmatters_id";
                $params['Actmatters_id'] = $mnn_data['Actmatters_id'];
            }

            if (!empty($where_str)) {
                $where[] = "({$where_str})";
            }
        }

        $where_clause = implode(' and ', $where);
        if (strlen($where_clause) > 0) {
            $where_clause = "
				where
					{$where_clause}
			";
        }

        $query = "
            select
                p.Actmatters_id,
                p.Actmatters_Name
            from (
                select top 250
                    am.ACTMATTERS_ID as Actmatters_id,
                    am.RUSNAME as Actmatters_Name
                from
                    rls.v_Actmatters am with (nolock)
                    outer apply (
                        select
                            ltrim(
                                rtrim(
                                    replace(
                                        am.RUSNAME, '*', ''
                                    )
                                )
                            ) as TrimName
                    ) amn
                {$where_clause}
                order by
                    am.RUSNAME
                	union all
                select
                    0,
                    'Нет'
            ) p
		";

        $result = $this->db->query($query, $params);
        if ( is_object($result) ) {
            return $result->result('array');
        }


        return false;
    }

    /**
     * Загрузка списка медикаментов
     */
    function loadDrugCombo($filter) {
        $where = array();
        $params = array();

        if ($filter['Drug_id'] > 0) {
            $where[] = 'd.Drug_id = :Drug_id';
            $params['Drug_id'] = $filter['Drug_id'];
        } else {
            if ($filter['DrugComplexMnn_id'] > 0) {
                $where[] = 'd.DrugComplexMnn_id = :DrugComplexMnn_id';
                $params['DrugComplexMnn_id'] = $filter['DrugComplexMnn_id'];
            } else if (!empty($filter['Actmatters_id']) || $filter['Actmatters_id'] === '0') {
                $where[] = $filter['Actmatters_id'] > 0 ? 'dcmn.Actmatters_id = :Actmatters_id' : 'dcmn.Actmatters_id is null';
                $params['Actmatters_id'] = $filter['Actmatters_id'];
            } else {
                return false;
            }

            if (!empty($filter['query'])) {
                $where[] = 'd.Drug_Name like :Drug_Name';
                $params['Drug_Name'] = preg_replace('/\s/', '%', $filter['query']).'%';
            }
        }

        $where_clause = implode(' and ', $where);
        if (strlen($where_clause) > 0) {
            $where_clause = "
				where
					{$where_clause}
			";
        }

        $query = "
            select top 250
                d.Drug_id,
                d.Drug_Name,
                dn.DrugNomen_Code,
                dn.DrugNds_id,
                dcm.DrugComplexMnn_RusName,
                fn.NAME as Firm_Name, --Производитель
                c.NAME as Country_Name, --Страна производителя
				rc.REGNUM as Reg_Num, --№ РУ
				rceff.FULLNAME as Reg_Firm, --Держатель/Владелец РУ
				rceffc.NAME as Reg_Country, --Страна владельца
				(convert(varchar, rc.REGDATE, 104)+isnull(' - '+convert(varchar, rc.ENDDATE, 104), '')) as Reg_Period, --Период действия
				convert(varchar, rc.Reregdate, 104) as Reg_ReRegDate, --Дата переоформления
				d.DrugForm_Name, -- Лек.форма
				d.Drug_Fas, -- Фасовка
				d.Drug_Dose, -- Дозировка
				d.DrugTorg_Name, -- торг.наименование
				d.DrugComplexMnn_id
            from
                rls.v_Drug d with (nolock)
                left join rls.v_DrugComplexMnn dcm with (nolock) on dcm.DrugComplexMnn_id = d.DrugComplexMnn_id
                left join rls.v_DrugComplexMnnName dcmn with (nolock) on dcmn.DrugComplexMnnName_id = dcm.DrugComplexMnnName_id
                left join rls.PREP p with (nolock) on p.Prep_id = d.DrugPrep_id
				left join rls.REGCERT rc with (nolock) on rc.REGCERT_ID = p.REGCERTID
				left join rls.FIRMS f with (nolock) on f.FIRMS_ID = p.FIRMID
				left join rls.FIRMNAMES fn with (nolock) on fn.FIRMNAMES_ID = f.NAMEID
				left join rls.v_COUNTRIES c with (nolock) on c.COUNTRIES_ID = f.COUNTID
				left join rls.REGCERT_EXTRAFIRMS rcef with (nolock) on rcef.CERTID = rc.REGCERT_ID
				left join rls.v_FIRMS rceff with (nolock) on rceff.FIRMS_ID = rcef.FIRMID
                left join rls.v_COUNTRIES rceffc with (nolock) on rceffc.COUNTRIES_ID = rceff.COUNTID
                outer apply (
                    select top 1
                        i_dn.DrugNomen_Code,
                        i_dn.DrugNds_id
                    from
                        rls.v_DrugNomen i_dn with (nolock)
                    where
                        i_dn.Drug_id = d.Drug_id
                    order by
                        i_dn.DrugNomen_id
                ) dn
            {$where_clause}
            order by
                d.Drug_Name
		";

        $result = $this->db->query($query, $params);
        if ( is_object($result) ) {
            return $result->result('array');
        } else {
            return false;
        }
    }
}
?>