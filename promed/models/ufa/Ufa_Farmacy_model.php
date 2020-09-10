<?php defined('BASEPATH') or die ('No direct script access allowed');
require_once(APPPATH.'models/Farmacy_model.php');

class Ufa_Farmacy_model extends Farmacy_model {
	/**
	 * construct
	 */
	function __construct() {
		//parent::__construct();
            parent::__construct();
	}

	/**
	 * Получение списка остатков для обеспечения рецепта
	 */
	function getDrugOstatForProvide($data) {
		$where = array();

		if (!empty($data['DrugOstatRegistry_id']) && $data['DrugOstatRegistry_id'] > 0) {
			$where[] = 'dus.DrugOstatRegistry_id = :DrugOstatRegistry_id';
		}
		else {
			if (!empty($data['EvnRecept_id']) && $data['EvnRecept_id'] > 0) {
				$result = array();

				if (!empty($data['EvnReceptGeneral_id']) && $data['EvnReceptGeneral_id'] > 0) {
					$q = "
						select
							erg.Drug_id,
							erg.DrugComplexMnn_id,
							wds.WhsDocumentSupply_id,
							erg.DrugFinance_id,
							erg.WhsDocumentCostItemType_id
						from
							v_EvnReceptGeneral erg with (nolock)
							left join v_WhsDocumentSupply wds with (nolock) on wds.WhsDocumentUc_id = erg.WhsDocumentUc_id
						where
							erg.EvnReceptGeneral_id = :EvnReceptGeneral_id;
					";
					$result = $this->getFirstRowFromQuery($q, array('EvnReceptGeneral_id' => $data['EvnReceptGeneral_id']));
				}
				else {
					$q = "
						select 
							er.Drug_id,
							er.DrugFinance_id,
							--isnull(er.WhsDocumentCostItemType_id, 
							isnull(null, --  Пока статья расхода ложится не корректно
								case
									when er.ReceptFinance_id = 2 and DrugClass_id = 7 then 34
									when er.ReceptFinance_id = 2 and du.WhsDocumentCostItemType_id = 34 then 34
									else 
										--er.ReceptFinance_id
										er.WhsDocumentCostItemType_id
								end
							) WhsDocumentCostItemType_id,        
							Dr.DrugMnn_id,
							Dr.DrugTorg_id,
							Dr.Drug_Name,
							er.OrgFarmacy_id, 
							farm.Org_id,
							er.Lpu_id,
							er.EvnRecept_IsMnn 
						from
							v_EvnRecept er with (nolock)
							inner join v_Drug dr with (nolock) on dr.Drug_id = er.Drug_id
							inner join OrgFarmacy farm with (nolock) on farm.OrgFarmacy_id = er.OrgFarmacy_id
							outer apply (
								select top 1 Du.WhsDocumentCostItemType_id
								from v_DocumentUcStr dus with (nolock) 
									left join v_DocumentUc du with (nolock) on du.DocumentUc_id = dus.DocumentUc_id 
										--and du.Org_id = farm.Org_id
										and DrugDocumentType_id in (3, 6)
									left join v_Drug dr2 with (nolock) on dr2.Drug_id = dus.Drug_id        
								where dr2.DrugTorg_id = dr.DrugTorg_id
								order by  Du.WhsDocumentCostItemType_id desc
							) Du 
						where
							er.EvnRecept_id = :EvnRecept_id;
					";
					$result = $this->getFirstRowFromQuery($q, array('EvnRecept_id' => $data['EvnRecept_id']));
					//$data['EvnRecept_id'] = 492782110;  //468837123; //471831918;               
					//$result = $this->getFirstRowFromQuery2($q, array('EvnRecept_id' => $data['EvnRecept_id']));
				}
								
				// Если в параметрах передали статью расхода, то принимаем параметр
				if (isset($data['WhsDocumentCostItemType_id']))
					$result['WhsDocumentCostItemType_id'] = $data['WhsDocumentCostItemType_id'];

				if (is_array($result) && count($result) > 0) {
					// Используем рег. выражение 
					preg_match("#(игл[а-яё]{1,})#ui", $result['Drug_Name'], $m);

					if ($result['WhsDocumentCostItemType_id'] == 34) {
						// Если это спец. питание, то ничего не делаем,
						// чтобы поиск шел по статье расхода
					}                               
					else if ($result['EvnRecept_IsMnn'] == 2) {
						$where[] = 'd.DrugMnn_id = :DrugMnn_id';
						$data['DrugMnn_id'] = $result['DrugMnn_id'];
					} 
					else if(isset($m[0])) {  // Если это иглы
						$where[] = 'd.DrugMnn_id = :DrugMnn_id';
						$data['DrugMnn_id'] = $result['DrugMnn_id'];
					}
					else  { //  Если это не спецпитание
						$where[] = 'd.DrugTorg_id = :DrugTorg_id';
						$data['DrugTorg_id'] = $result['DrugTorg_id'];
					} 

					$where[] = 'dor.WhsDocumentCostItemType_id = :WhsDocumentCostItemType_id';
					$data['WhsDocumentCostItemType_id'] = $result['WhsDocumentCostItemType_id'];

					$where[]  = 'dor.Org_id =  :Org_id';
					$data['Org_id'] = $data['session']['org_id']; //  Для обеспечения берем текущую аптеку
					//$data['Org_id'] = 68320121088;

					$where[]  = '(OrgFarmI.Storage_id is null or OrgFarmI.Lpu_id = :Lpu_id)';
					$data['Lpu_id'] = $result['Lpu_id'];
				}
				else {
					$where[]  = '1 > 1';
				}
			}
			
			if (!empty($data['subAccountType_id']) && $data['subAccountType_id'] == '2') 
				$where[] = 'SubAccountType_id in (1, 2)';
			else
				 $where[] = 'SubAccountType_id in (1)';
			
			$where[] = '(dor.DrugOstatRegistry_Kolvo - isnull(res.ReservKolvo, 0)) > 0';
		}

		// @task https://redmine.swan.perm.ru/issues/120812
		// Org_id указан в основном запросе, а в параметрах передавался только 
		$data['Org_id'] = $data['session']['org_id'];
		//$data['Org_id'] = 68320121088;
		$q = "
			with res as (
				select
					dor.DrugOstatRegistry_id,
					isnull(sum(dus.DocumentUcStr_Count), 0) ReservKolvo
				from DrugOstatRegistry dor with (nolock)
					join r2.DrugOstat2DocumentUcStr r with (nolock) on r.DrugOstatRegistry_id = dor.DrugOstatRegistry_id
					join v_DocumentUcStr dus with (nolock) on dus.DocumentUcStr_id = r.DocumentUcStr_id
					join v_DocumentUc du with (nolock) on du.DocumentUc_id = dus.DocumentUc_id
						and du.DrugDocumentType_id = 11
						and du.DrugDocumentStatus_id = 1
				where dor.SubAccountType_id = 2
					and dor.DrugOstatRegistry_Kolvo > 0
					and dor.Org_id = :Org_id
					group by  dor.DrugOstatRegistry_id
			)

			select
				d.Drug_id, d.Drug_Code,
				dor.DrugOstatRegistry_id,
				--dor.storage_id,
				case
					when OrgFarmI.Storage_id is not null  --  Если препарат привязан к аптеке
						then isnull(l.Lpu_Nick, '') 
					else ''
				end as Lpu_Nick,
				convert(numeric(10, 2),
					case
						when dor.DrugOstatRegistry_Kolvo >= isnull(res.ReservKolvo, 0)
						then dor.DrugOstatRegistry_Kolvo - isnull(res.ReservKolvo, 0)
						else 0
					end
				) as DrugOstatRegistry_Kolvo,
				dor.DrugOstatRegistry_Cost,
				dus.DocumentUcStr_id,
				DrugShipment_Name,
				substring(d.Drug_Name, 0, 15)+'...' as Drug_ShortName,
				case when dor.SubAccountType_id = 2 then '(РЕЗЕРВ) ' else '' end  --  Для резерва делаем пометку
				+ d.Drug_Name as Drug_Name,
				case when dor.SubAccountType_id = 2 then '<font color=#FF00EF><b>РЕЗЕРВ</b></font> ' else '' end  --  Для резерва делаем пометку
				+ case when km.inKm = 2 then  '<font color=#fc3f33><b>' + d.Drug_Name + '</b></font> ' else d.Drug_Name end 
				as Drug_NameDop,
				d.Drug_Code as DrugNomen_Code, 
				null as PrepSeries_id,
				dus.DocumentUcStr_Ser as PrepSeries_Ser,

				convert(varchar(10), dus.DocumentUcStr_godnDate, 104) as PrepSeries_GodnDate,

				case  --  Устанавливаем 'Критичность' срока годности
					when dus.DocumentUcStr_godnDate IS Null then 1
					when DATEADD(m, -3, dus.DocumentUcStr_godnDate) < GETDATE () Then 1
					else 0	
				end as GodnDate_Ctrl,

				null PrepSeries_isDefect,
				--'СѓРїР°Рє' as Okei_NationSymbol,
				--dus.DocumentUcStr_Price,
				case when ds.DrugShipment_name = '0' then DrugOstatRegistry_Cost else dus.DocumentUcStr_Price end as DocumentUcStr_Price,
				isnull(isnds.YesNo_Code, 0) as DocumentUcStr_IsNDS,
				dus.DocumentUcStr_Sum,
				dn.DrugNds_id,
				dn.DrugNds_Code,
				dus.DocumentUcStr_SumNds,
				isnull(dus.DocumentUcStr_Sum, 0) + isnull(dus.DocumentUcStr_SumNds, 0) as DocumentUcStr_NdsSum,
				isnull(df.DrugFinance_Name, '') as DrugFinance_Name,
				isnull(wdcit.WhsDocumentCostItemType_Name, '') as WhsDocumentCostItemType_Name,
				isnull(convert(varchar, DrugOst.DocumentUc_didDate, 104), '') as DocumentUcStr_didDate,
				km.inKm
			from 
				v_DrugOstatRegistry dor with (nolock)
				-- Если резерв, то вычитаем оповещение
				left join res on res.DrugOstatRegistry_id = dor.DrugOstatRegistry_id
				--inner join v_DrugShipmentLink dsl with (nolock) on dsl.DrugShipment_id = dor.DrugShipment_id
				cross apply (
					Select top 1 DrugShipment_id, dsl.DocumentUcStr_id
					from v_DrugShipmentLink dsl with (nolock)
						inner join v_DocumentUcStr dus on dus.DocumentUcStr_id = dsl.DocumentUcStr_id
						inner join v_DocumentUc du  on du.DocumentUc_id = dus.DocumentUc_id and du.org_id = dor.org_id
					where dsl.DrugShipment_id = dor.DrugShipment_id
				) dsl
				left join v_DrugShipment ds with (nolock) on ds.DrugShipment_id = dsl.DrugShipment_id
				--inner join v_EvnRecept er with (nolock) on er.Drug_id = dor.Drug_did
				--inner join OrgFarmacy farm with (nolock) on farm.OrgFarmacy_id = er.OrgFarmacy_id --and farm.Org_id = dor.Org_id
				left join v_Drug d with(nolock) on d.Drug_id = dor.Drug_did			
				left join v_DocumentUcStr dus with (nolock) on dus.DocumentUcStr_id = dsl.DocumentUcStr_id	
				left join v_DocumentUc du with (nolock) on du.DocumentUc_id = dus.DocumentUc_id 
					--and du.Org_id = farm.Org_id
				left join v_DrugFinance df with (nolock) on df.DrugFinance_id = dus.DrugFinance_id
				left join v_DrugNds dn with (nolock) on dn.DrugNds_id = dus.DrugNds_id
				left join v_YesNo isnds with (nolock) on isnds.YesNo_id = dus.DocumentUcStr_IsNDS
				left join v_WhsDocumentCostItemType wdcit with (nolock) on wdcit.WhsDocumentCostItemType_id = dor.WhsDocumentCostItemType_id

				cross apply (
					Select top (1) dor.Storage_id, lpu_id
					from OrgFarmacyIndex OrgFarmI  with (nolock)
						inner join OrgFarmacy farm on farm.OrgFarmacy_id = OrgFarmI.OrgFarmacy_id
					where farm.Org_id = dor.Org_id
						and isnull(OrgFarmI.Storage_id, 0) = isnull(dor.storage_id, ISNULL(OrgFarmI.Storage_id, 0))
						and ISNULL(OrgFarmacyIndex_deleted, 1) = 1
				) OrgFarmI        
				left join v_Lpu l  with (nolock) on l.lpu_id = OrgFarmI.lpu_id 
				outer apply (
					Select min(DocumentUc_didDate) DocumentUc_didDate 
					from [r2].[fn_Ost4Report] (
						dor.Org_id,
						dor.Drug_did,
						dor.WhsDocumentCostItemType_id,
						dor.DrugShipment_id,
						dor.DrugOstatRegistry_Cost,
						dor.DrugOstatRegistry_Kolvo)
				) DrugOst
				outer apply (
					SElect top (1) DrugPackageBarCode_id, 2 inKm
					from v_DrugPackageBarCode bc with (nolock)
						left join v_DocumentUcStr Dus with (nolock) on dus.DocumentUcStr_id = bc.DocumentUcStr_id
						inner join v_DocumentUc du on du.DocumentUc_id = dus.DocumentUc_id
							and du.org_id = dor.Org_id
						left join DrugShipmentLink ln  with (nolock) on ln.DocumentUcStr_id = dus.DocumentUcStr_id
					where dus.drug_id = dor.Drug_did
						and ln.DrugShipment_id = dor.DrugShipment_id
						and bc.DrugPackageBarCode_FactNum is not null and  DrugPackageBarCode_FactNum > 0
				) km
		";

		if (count($where) > 0) {
			$q .= " where ".join($where, " and ");
		}
		$q .=  '
			order by DocumentUcStr_godnDate, DrugOst.DocumentUc_didDate
		';
		//echo getDebugSql($q, $data);exit;
				
		//$dbrep = $this->load->database('bdwork', true);
		//$result = $dbrep->query($q, $data);
		$result = $this->db->query($q, $data);

		if ( is_object($result) ) {
			return $result->result('array');
		}

		return false;
	}
  
	/**
	 * Обеспечение рецепта
	 */
	function provideEvnRecept($data) {
		//старт транзакции
		$this->beginTransaction();
                
		// Переменная для возможного изменения статьи расхода
		$UpdateEvnRecept = "";
		$UpdateDrugFinance2Recept = "";  //  логФайл для апдейта  DrugFinance
 
		if (empty($data['session']['Contragent_id'])) {
			return array(array('Error_Msg' => 'Отсутвуют данные контрагента.'));
		}
		//получаем данные текущего пользователя
		$org_id = $data['session']['org_id'];
		$contragent_id = $data['session']['Contragent_id'];
		//$org_id = 68320121088;
		//$contragent_id = 218;
		$cur_date = new DateTime();

		if (isset ($data['EvnRecept_otpDate'])) {//  Если передается дата отпуска
			$cur_date = $data['EvnRecept_otpDate'];
		}

		//получаем данные общего характера
		$query = "
			declare
				@SubAccountType_id bigint,
				@DrugDocumentType_id bigint,
				@DrugDocumentStatus_id bigint,
				@PacientContragent_id bigint,
				@Storage_id bigint,
				@Yes_id bigint;

			set @SubAccountType_id = (select top 1 SubAccountType_id from v_SubAccountType where SubAccountType_Code = 1); --Доступно
			set @DrugDocumentType_id = (select top 1 DrugDocumentType_id from v_DrugDocumentType where DrugDocumentType_SysNick = 'DocReal'); --Реализация
			set @DrugDocumentStatus_id = (select top 1 DrugDocumentStatus_id from v_DrugDocumentStatus where DrugDocumentStatus_Code = 4); --Исполнен
			set @PacientContragent_id = (select top 1 Contragent_id from v_Contragent where Contragent_Code = 1); --Пациент
			set @Storage_id = (select top 1 Storage_id from StorageStructLevel where MedService_id = :MedService_id);
			set @Yes_id = (select top 1 YesNo_id from v_YesNo where YesNo_Code = 1);

			select
				@SubAccountType_id as SubAccountType_id,
				@DrugDocumentType_id as DrugDocumentType_id,
				@DrugDocumentStatus_id as DrugDocumentStatus_id,
				@PacientContragent_id as PacientContragent_id,
				@Storage_id as Storage_id,
				@Yes_id as Yes_id;
		";
		$common_data = $this->getFirstRowFromQuery($query, array(
			'MedService_id' => $data['MedService_id']
		));
		if ($common_data === false) {
			return array(array('Error_Msg' => 'Не удалось получить данные.'));
		}

		//проверка текущего статуса рецепта
		$query = "
			select
				count(EvnRecept_id) as cnt
			from
				EvnRecept er with (nolock)
				left join v_ReceptDelayType rdt with (nolock) on rdt.ReceptDelayType_id = er.ReceptDelayType_id
			where
				EvnRecept_id = :EvnRecept_id and
				ReceptDelayType_Code = 0;
		";
		$params = array(
			'EvnRecept_id' => $data['EvnRecept_id']
		);
		$result = $this->getFirstResultFromQuery($query, $params);
		if ($result !== false) {
			if ($result > 0) {
				$this->rollbackTransaction();
				return array(array('Error_Msg' => 'Рецепт уже обеспечен.'));
			}
		} else {
			$this->rollbackTransaction();
			return array(array('Error_Msg' => 'Ошибка при обращении к базе данных.'));
		};

		if (!(isset($data['DocumentUc_id']) && $data['DocumentUc_id'] == 0)) { // Если это не оповещение
			//изменение статуса рецепта
			$query = "
				declare
					@ReceptDelayType_id bigint,
					@OrgFarmacy_id bigint,
					@EvnRecept_obrDT datetime,
				    @EvnRecept_otpDT datetime = :EvnRecept_otpDT;

				if @EvnRecept_otpDT is null
					set @EvnRecept_otpDT = dbo.tzGetDate();
	
				set @ReceptDelayType_id = (select top 1 ReceptDelayType_id from v_ReceptDelayType with (nolock) where ReceptDelayType_Code = 0);
				set @OrgFarmacy_id = (select top 1 OrgFarmacy_id from v_OrgFarmacy with (nolock) where Org_id = :Org_id);
				set @EvnRecept_obrDT = (select top 1 isnull(EvnRecept_obrDT, dbo.tzGetDate()) from v_EvnRecept with (nolock) where EvnRecept_id = :EvnRecept_id);
				
				if @EvnRecept_obrDT > @EvnRecept_otpDT
					set @EvnRecept_obrDT = @EvnRecept_otpDT
	
				update
					Evn
				set
					Evn_updDT = dbo.tzGetDate(),
					pmUser_updID = :pmUser_id
				where
					Evn_id = :EvnRecept_id;
	
				update
					EvnRecept
				set
					ReceptDelayType_id = @ReceptDelayType_id,
					OrgFarmacy_oid = @OrgFarmacy_id,
					EvnRecept_obrDT = @EvnRecept_obrDT,
					EvnRecept_otpDT = @EvnRecept_otpDT
				where
					EvnRecept_id = :EvnRecept_id;
			";
			$result = $this->db->query($query, array(
				'Org_id' => $data['session']['org_id'],
				'EvnRecept_id' => $data['EvnRecept_id'],
				'pmUser_id' => $data['pmUser_id'],
			    'EvnRecept_otpDT' => $data['EvnRecept_otpDate']
			));
		}
               
		// Если передан параметр, формируем переменную для  изменения статьи расхода
		if(isset($data['WhsDocumentCostItemType_id'])) {
			$UpdateDrugFinance2Recept = "
				Declare 
					@EvnRecept_id bigint = " .$data['EvnRecept_id'] .", 
					@pmUser_id bigint = " .$data['pmUser_id'] ."    
			
				insert into r2.UpdateDrugFinance2Recept
					(EvnRecept_id, UpdateDrugFinance2Recept_insDT, pmUser_insID, DrugFinance_id, WhsDocumentCostItemType_id, EvnRecept_Is7Noz,
					DrugFinance_oid, WhsDocumentCostItemType_oid, EvnRecept_Is7NozOld)
			";
			
			if ($data['WhsDocumentCostItemType_id'] == 3) {
				$UpdateEvnRecept = " 
					EvnRecept_Is7Noz = 2,
					DrugFinance_id = 3,
					WhsDocumentCostItemType_id = 3
				";    
				
				$UpdateDrugFinance2Recept .= "
					select
						EvnRecept_id, GetDate(), @pmUser_id, 3, 3, 2,
						DrugFinance_id, WhsDocumentCostItemType_id, EvnRecept_Is7Noz
					from
						EvnRecept with (nolock)
					where
						EvnRecept_id = @EvnRecept_id
				";
			} else if ($data['WhsDocumentCostItemType_id'] == 34) {
				$UpdateEvnRecept = " 
					EvnRecept_Is7Noz = 1,
					DrugFinance_id = 27,
					WhsDocumentCostItemType_id = 34
				";    
				
				$UpdateDrugFinance2Recept .= "
					select
						EvnRecept_id, GetDate(), @pmUser_id, 27, 34, 1,
						DrugFinance_id, WhsDocumentCostItemType_id, EvnRecept_Is7Noz
					from
						EvnRecept with (nolock)
					where
						EvnRecept_id = @EvnRecept_id
				";
			}
			else if ($data['WhsDocumentCostItemType_id'] == 2) {
				$UpdateEvnRecept = " 
					EvnRecept_Is7Noz = 1,
					DrugFinance_id = 27,
					WhsDocumentCostItemType_id = 2
				";
				
				$UpdateDrugFinance2Recept .= "
					select
						EvnRecept_id, GetDate(), @pmUser_id, 27, 2, 1,
						DrugFinance_id, WhsDocumentCostItemType_id, EvnRecept_Is7Noz
					from
						EvnRecept with (nolock)
					where
						EvnRecept_id = @EvnRecept_id
				";
			}
		}

		if (!(isset($data['DocumentUc_id']) && $data['DocumentUc_id'] == 0) && in_array($data['WhsDocumentCostItemType_id'], array(2, 3, 34))) { // Если это не оповещение
		    $query = "
			    update
				    Evn
			    set
				    Evn_updDT = dbo.tzGetDate(),
				    pmUser_updID = :pmUser_id
			    where
				    Evn_id = :EvnRecept_id;
				    
				{$UpdateDrugFinance2Recept}

			    update
				    EvnRecept
			    set
				    {$UpdateEvnRecept}
			    where
				    EvnRecept_id = :EvnRecept_id;
		    ";
		    $result = $this->db->query($query, array(
			    'EvnRecept_id' => $data['EvnRecept_id'],
			    'pmUser_id' => $data['pmUser_id']
		    ));
		}

		if (isset($data['DocumentUc_id'])) {
		    // Редактируем / формируем оповещение
		    $query = "
				Declare 
					@evnRecept_id bigint = :EvnRecept_id,
					@receptNotification_id bigint = null,
					@receptNotification_phone varchar(50) = null,
					@receptNotification_setDate date = getDate(),
					@pmUser_id bigint = :pmUser_id,
					@Error_Code int = null,
					@Error_Message varchar(4000) = null;
					
				Select top 1 @receptNotification_id = receptNotification_id,
					@receptNotification_phone = receptNotification_phone
					from receptNotification with (nolock)
						where evnRecept_id = @evnRecept_id
					
				if @receptNotification_id is not null
					exec p_receptNotification_upd
						@receptNotification_id = @receptNotification_id output,
						@evnRecept_id = @evnRecept_id,
						@receptNotification_phone = @receptNotification_phone,
						@receptNotification_setDate = @receptNotification_setDate,
						@pmUser_id = @pmUser_id,
						@Error_Code = @Error_Code output,
						@Error_Message = @Error_Message output;
				else
					exec p_receptNotification_ins
						@receptNotification_id = @receptNotification_id output,
						@evnRecept_id = @evnRecept_id,
						@receptNotification_phone = @receptNotification_phone,
						@receptNotification_setDate = @receptNotification_setDate,
						@pmUser_id = @pmUser_id,
						@Error_Code = @Error_Code output,
						@Error_Message = @Error_Message output;

			    Select  @receptNotification_id as receptNotification_id, @Error_Code as Error_Code, @Error_Message as Error_Mes;

		    ";
		    $result = $this->db->query($query, array(
			    'EvnRecept_id' => $data['EvnRecept_id'],
			    'pmUser_id' => $data['pmUser_id']
		    ));
			
		}

		//получаем данные рецепта
		$query = "
			select
				er.EvnRecept_id,
				er.EvnRecept_Guid,
				er.Person_id,
				ps.Person_Snils,
				er.PrivilegeType_id,
				er.Lpu_id,
				l.Lpu_Ogrn,
				er.MedPersonal_id,
				er.Diag_id,
				er.EvnRecept_Ser,
				er.EvnRecept_Num,
				convert(varchar(10), er.EvnRecept_setDT, 120) as EvnRecept_setDT,
				convert(varchar(10), er.EvnRecept_obrDT, 120)+' '+convert(varchar(10), er.EvnRecept_obrDT, 108) as EvnRecept_obrDT,
				convert(varchar(10), er.EvnRecept_otpDT, 120)+' '+convert(varchar(10), er.EvnRecept_otpDT, 108) as EvnRecept_otpDT,
				er.ReceptFinance_id,
				er.OrgFarmacy_oid,
				--er.Drug_rlsid as Drug_id,
				er.Drug_id,
				--dn.DrugNomen_Code as Drug_Code,
				er.Drug_id as Drug_Code,
				er.EvnRecept_Kolvo,
				er.ReceptDelayType_id,
				er.EvnRecept_Is7Noz,
				er.DrugFinance_id,
				er.WhsDocumentCostItemType_id,
				er.EvnRecept_Kolvo,
				er.WhsDocumentUc_id
			from
				v_EvnRecept er with (nolock)
				left join v_PersonState ps on ps.Person_id = er.Person_id
				left join v_Lpu l on l.Lpu_id = er.Lpu_id
				outer apply (
					select top 1
						DrugNomen_Code
					from
						rls.v_DrugNomen dn
					where
						dn.Drug_id = er.Drug_rlsid 
				) dn
			where
				er.EvnRecept_id = :EvnRecept_id;  
		";
		$params = array(
			'EvnRecept_id' => $data['EvnRecept_id']
		);
		$recept_data = $this->getFirstRowFromQuery($query, $params);
		//echo '<pre>' . print_r($recept_data, 1) . '</pre>';
		if ($recept_data === false) {
			$this->rollbackTransaction();
			return array(array('Error_Msg' => 'Не удалось получить данные о рецепте.'));
		}
                
		//получаем данные о выбранных сериях и строках регистра остатков
		//if (1 == 1) {
		if (isset($data['DocumentUc_id']) && $data['DocumentUc_id'] > 0) {
		      //Если это отсроченный рецепт
		    $query = "
				declare
					@dusDocumentUc_id bigint = {$data['DocumentUc_id']};
				Select
					dus.DocumentUcStr_Count Kolvo, dds.DrugOstatRegistry_id 
				From
					v_DocumentUcStr dus with (nolock)
					join r2.DrugOstat2DocumentUcStr dds with (nolock) on dds.DocumentUcStr_id = dus.DocumentUcStr_id
				where
					dus.DocumentUc_id = @dusDocumentUc_id; 
			";
		    $ser2_data = $this->db->query($query);
		    $ser_data = $ser2_data->result('array');
			$series_data = array();
			$str = '';
		    $cnt = 0;
		    for ($i = 0, $c = count($ser_data); $i < $c; $i++)
			{
				$series_data[$i] = new stdClass();
				$series_data[$i]->DrugOstatRegistry_id = $ser_data[$i]['DrugOstatRegistry_id'];
				$series_data[$i]->Kolvo = $ser_data[$i]['Kolvo'];
			}

		} else {
		    //получаем данные о выбранных сериях и строках регистра остатков
		    $series_data = array();
		    
		    if (!empty($data['DrugOstatDataJSON'])) {
			    $series_data = (array) json_decode($data['DrugOstatDataJSON']);
			    //echo '<pre>' . print_r($series_data, 1) . '</pre>'; exit;
			    $ost_kolvo = 0;
			    foreach($series_data as &$s_data) {
				    $ost_kolvo += $s_data->Kolvo;
			    }
			    /*
			     * Убрано, чтобы была возможность
			     * отпустить количество, отличное от выписанного
			    if ($recept_data['EvnRecept_Kolvo'] != $ost_kolvo) {
				    $this->rollbackTransaction();
				    return array(array('Error_Msg' => 'Суммарное количество медикамента для выбранных серий, не соответствует количеству в рецепте.'));
			    }
			    */  
		    }
		};
		if (count($series_data) < 1) {
			$this->rollbackTransaction();
			return array(array('Error_Msg' => 'Не удалось получить данные о выбранных сериях.'));
		}
		//echo '<pre>' . print_r($series_data, 1) . '</pre>'; //exit;
		$price_array = array();
		//списываем медикамент с остатков и суммируем количество по ценам

		foreach($series_data as &$s_data) {

		    $query = "
				declare
					@DrugOstatRegistry_id bigint,
					@Contragent_id bigint,
					@Org_id bigint,
					@DrugShipment_id bigint,
					@Drug_id bigint,
					@Drug_did bigint,
					@PrepSeries_id bigint,
					@SubAccountType_id bigint,
					@Okei_id bigint,
					@DrugOstatRegistry_Kolvo float,
					@DrugOstatRegistry_Sum float,
					@Storage_id bigint,
					@DrugFinance_id bigint,
					@WhsDocumentCostItemType_id bigint,
					@DrugOstatRegistry_Cost numeric(18,2),
					@ErrCode int,
					@ErrMessage varchar(4000), 
					@KOl  numeric(18,2);

					Set @DrugOstatRegistry_id = :DrugOstatRegistry_id;

				select
					@Contragent_id = Contragent_id,
					@Org_id = Org_id,
					@DrugShipment_id = DrugShipment_id,
					@Drug_id = Drug_id,
					@Drug_did = Drug_did,
					@PrepSeries_id = PrepSeries_id,
					@SubAccountType_id = SubAccountType_id,
					@Okei_id = Okei_id,
					@DrugOstatRegistry_Kolvo = DrugOstatRegistry_Kolvo,
					@DrugOstatRegistry_Sum = DrugOstatRegistry_Sum,
					@Storage_id = Storage_id,
					@DrugFinance_id = DrugFinance_id,
					@WhsDocumentCostItemType_id = WhsDocumentCostItemType_id,
					@DrugOstatRegistry_Cost = DrugOstatRegistry_Cost
				from
					v_DrugOstatRegistry with (nolock)
				where
					DrugOstatRegistry_id = @DrugOstatRegistry_id;

				set @Kol = @DrugOstatRegistry_Kolvo - :DrugOstatRegistry_Kolvo
			";
			if (!(isset($data['DocumentUc_id']) && $data['DocumentUc_id'] == 0)) {
				$query .= "
				if (@Kol >= 0)
					begin

						set @DrugOstatRegistry_Sum = Round(@KOl * @DrugOstatRegistry_Cost, 2);
						set @DrugOstatRegistry_Kolvo = @Kol

						exec dbo.p_DrugOstatRegistry_upd
							@DrugOstatRegistry_id= @DrugOstatRegistry_id,
							@Contragent_id = @Contragent_id,
							@Org_id = @Org_id,
							@DrugFinance_id = @DrugFinance_id,
							@DrugShipment_id = @DrugShipment_id,
							@Drug_id = @Drug_id,
							@WhsDocumentCostItemType_id = @WhsDocumentCostItemType_id, 
							@PrepSeries_id = @PrepSeries_id,
							@SubAccountType_id = @SubAccountType_id,
							@Okei_id = @Okei_id,
							@DrugOstatRegistry_Kolvo = @DrugOstatRegistry_Kolvo,
							@DrugOstatRegistry_Sum = @DrugOstatRegistry_Sum,
							@Storage_id = @Storage_id,
							@DrugOstatRegistry_Cost = @DrugOstatRegistry_Cost,
							@Drug_did = @Drug_did,
							@pmUser_id = :pmUser_id,
							@Error_Code = @ErrCode output,
							@Error_Message = @ErrMessage output;

					end;
					else
					begin
						set @ErrMessage = (
							select top 1
								isnull(
									d.Drug_Name+', '+
									ps.PrepSeries_Ser+', '+
									convert(varchar(10), ps.PrepSeries_GodnDate, 104)+', '+
									'№ '+ds.DrugShipment_Name+
									' – '+cast(dor.DrugOstatRegistry_Kolvo as varchar)+' шт. '+
									'недостаточно ЛП на остатках аптеки.   Рецепт не обеспечен. Выполните обеспечение рецепта с другой серией.',
									'Для обеспечения рецепта недостаточно медикаментов'
								) as msg
							from
								v_DrugOstatRegistry dor with (nolock)
								left join rls.v_Drug d with (nolock) on d.Drug_id = dor.Drug_id
								left join rls.v_PrepSeries ps with (nolock) on ps.PrepSeries_id = dor.PrepSeries_id
								left join v_DrugShipment ds with (nolock) on ds.DrugShipment_id = dor.DrugShipment_id
							where
								dor.DrugOstatRegistry_id = :DrugOstatRegistry_id
						)
					end;
				 ";
			}
			$query .= "
				select @Drug_did as Drug_id, @DrugOstatRegistry_Cost as DrugOstatRegistry_Cost, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			";

			/*
			$query = "

			    declare
					@DrugOstatRegistry_id bigint,
					@DrugOstatRegistry_Kolvo float,
					@pmUser_id bigint,
					@ErrCode int,
					@ErrMessage varchar(4000);

					Set @DrugOstatRegistry_id = :DrugOstatRegistry_id;
					Set @pmUser_id = :pmUser_id;
					Set @DrugOstatRegistry_Kolvo = :DrugOstatRegistry_Kolvo;

			    exec dbo.xp_DrugOstatRegistry_Expense
					    @DrugOstatRegistry_id= @DrugOstatRegistry_id,
					    @DrugOstatRegistry_Kolvo = @DrugOstatRegistry_Kolvo,
					    @pmUser_id = @pmUser_id,
					    @Error_Code = @ErrCode output,
					    @Error_Message = @ErrMessage output;

			    select  @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			    ";
			*/


			$params = array(
				'DrugOstatRegistry_id' => $s_data->DrugOstatRegistry_id,
				'DrugOstatRegistry_Kolvo' => $s_data->Kolvo,
				'pmUser_id' => $data['pmUser_id']
			);

			//echo getDebugSql($query, $params); exit;

			$result = $this->getFirstRowFromQuery($query, $params);
			if ($result !== false) {
				if (!empty($result['Error_Msg'])) {
					$this->rollbackTransaction();
					return array($result);
				} else { 
					$s_data->Drug_id = $result['Drug_id'];
					$s_data->DrugOstatRegistry_Cost = $result['DrugOstatRegistry_Cost'];
					if ($s_data->DrugOstatRegistry_Cost > 0) {
						if(!isset($price_array[$s_data->DrugOstatRegistry_Cost])) {
							$price_array[$s_data->DrugOstatRegistry_Cost] = array('kolvo' => 0);
						}
						$price_array[$s_data->DrugOstatRegistry_Cost]['kolvo'] += $s_data->Kolvo;
					}
				}
			} else {
				$this->rollbackTransaction();
				return array(array('Error_Msg' => 'Не удалось списать медикаменты с регистра остатков.'));
			}
		    }
		

		//создаем запись в ReceptOtov
		foreach($price_array as $price => $price_data) {
			//ищем подходящую записи в ReceptOtov
			$query = "
				select top 1
					ro.ReceptOtov_id
				from
					ReceptOtov ro with (nolock)
					left join v_ReceptDelayType rdt on rdt.ReceptDelayType_id = ro.ReceptDelayType_id
				where
					EvnRecept_id = :EvnRecept_id and
					rdt.ReceptDelayType_Code = 1 --Отложен
				order by
					ro.ReceptOtov_id;
			";
			$receptotov_id = $this->getFirstResultFromQuery($query, array(
				'EvnRecept_id' => $recept_data['EvnRecept_id']
			));

			$proc = 'p_ReceptOtov_ins';
			if ($receptotov_id > 0) {
				$proc = 'p_ReceptOtov_upd';
			} else {
				$receptotov_id = null;
			}

			$query = "
				declare
					@ReceptOtov_id bigint = :ReceptOtov_id,
					@ErrCode int,
					@ErrMessage varchar(4000);

				exec {$proc}
					@ReceptOtov_id = @ReceptOtov_id output,
					@EvnRecept_Guid = :EvnRecept_Guid,
					@Person_id = :Person_id,
					@Person_Snils = :Person_Snils,
					@PrivilegeType_id = :PrivilegeType_id,
					@Lpu_id = :Lpu_id,
					@Lpu_Ogrn = :Lpu_Ogrn,
					@MedPersonalRec_id = :MedPersonalRec_id,
					@Diag_id = :Diag_id,
					@EvnRecept_Ser = :EvnRecept_Ser,
					@EvnRecept_Num = :EvnRecept_Num,
					@EvnRecept_setDT = :EvnRecept_setDT,
					@ReceptFinance_id = :ReceptFinance_id,
					@ReceptValid_id = :ReceptValid_id,
					@OrgFarmacy_id = :OrgFarmacy_id,
					@Drug_cid = null,
					@Drug_Code = :Drug_Code,
					@EvnRecept_Kolvo = :EvnRecept_Kolvo,
					@EvnRecept_obrDate = :EvnRecept_obrDate,
					@EvnRecept_otpDate = :EvnRecept_otpDate,
					@EvnRecept_Price = :EvnRecept_Price,
					@ReceptDelayType_id = :ReceptDelayType_id,
					@ReceptOtdel_id = :ReceptOtdel_id,
					@EvnRecept_id = :EvnRecept_id,
					@EvnRecept_Is7Noz = :EvnRecept_Is7Noz,
					@DrugFinance_id = :DrugFinance_id,
					@WhsDocumentCostItemType_id = :WhsDocumentCostItemType_id,
					@ReceptStatusType_id = :ReceptStatusType_id,
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMessage output;
				select @ReceptOtov_id as ReceptOtov_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg; 
			";
			$params = array(
				'ReceptOtov_id' => $receptotov_id,
				'EvnRecept_Guid' => $recept_data['EvnRecept_Guid'],
				'Person_id' => $recept_data['Person_id'],
				'Person_Snils' => $recept_data['Person_Snils'],
				'PrivilegeType_id' => $recept_data['PrivilegeType_id'],
				'Lpu_id' => $recept_data['Lpu_id'],
				'Lpu_Ogrn' => $recept_data['Lpu_Ogrn'],
				'MedPersonalRec_id' => $recept_data['MedPersonal_id'],
				'Diag_id' => $recept_data['Diag_id'],
				'EvnRecept_Ser' => $recept_data['EvnRecept_Ser'],
				'EvnRecept_Num' => $recept_data['EvnRecept_Num'],
				'EvnRecept_setDT' => $recept_data['EvnRecept_setDT'],
				'ReceptFinance_id' => $recept_data['ReceptFinance_id'],
				'ReceptValid_id' => null,
				'OrgFarmacy_id' => $recept_data['OrgFarmacy_oid'],
				'Drug_cid' => $recept_data['Drug_id'],
				'Drug_Code' => $recept_data['Drug_Code'],
				'EvnRecept_Kolvo' => $price_data['kolvo'],
				'EvnRecept_obrDate' => $recept_data['EvnRecept_obrDT'],
				'EvnRecept_otpDate' => $recept_data['EvnRecept_otpDT'],
				'EvnRecept_Price' => $price,
				'ReceptDelayType_id' => $recept_data['ReceptDelayType_id'],
				'ReceptOtdel_id' => null,
				'EvnRecept_id' => $recept_data['EvnRecept_id'],
				'EvnRecept_Is7Noz' => $recept_data['EvnRecept_Is7Noz'],
				'DrugFinance_id' => $recept_data['DrugFinance_id'],
				'WhsDocumentCostItemType_id' => $recept_data['WhsDocumentCostItemType_id'],
				'ReceptStatusType_id' => 1, //сделано по аналогии с экспертизой РР
				'pmUser_id' => $data['pmUser_id']
			);
			$result = $this->getFirstRowFromQuery($query, $params);
			if ($result !== false) {
				if (!empty($result['Error_Msg'])) {
					$this->rollbackTransaction();
					return array($result);
				} else if ($result['ReceptOtov_id'] > 0) {
					$receptotov_id = $result['ReceptOtov_id'];
					$price_array[$price]['receptotov_id'] = $receptotov_id;
				}
			}
			if ($receptotov_id <= 0) {
				$this->rollbackTransaction();
				return array(array('Error_Msg' => 'Сохранение данных в списке отоваренных рецептов не удалось.'));
			}
		}


		//создаем документ реализации
		$DocumentUc_id = null;
		if (isset($data['DocumentUc_id'])) {
			if ($data['DocumentUc_id'] > 0) {
				//  Если оповещено
				$DocumentUc_id = $data['DocumentUc_id'];
			} else {
				/*
				 *  Создается документ со статусом "Новый" прои оповещении для отсроченных рецептов
				 */
				$common_data['DrugDocumentStatus_id'] = 1;
			}
			$common_data['SubAccountType_id'] = 2;
		}
		$doc_id = 0;
		$response = $this->saveDocumentUc(array(
			'DocumentUc_id' => $DocumentUc_id,
			'DocumentUc_Num' => $recept_data['EvnRecept_Num'],
			'DocumentUc_didDate' => $cur_date, //->format('Y-m-d'),
			'DocumentUc_setDate' => $cur_date, //->format('Y-m-d'),
			'DocumentUc_DogNum' => null,
			'DocumentUc_DogDate' => null,
			'Org_id' => $org_id,
			'Contragent_id' => $contragent_id,
			'Contragent_sid' => $contragent_id,
			'Contragent_tid' => $common_data['PacientContragent_id'],
			'DrugFinance_id' => $recept_data['DrugFinance_id'],
			'DrugDocumentType_id' => $common_data['DrugDocumentType_id'],
			'DrugDocumentStatus_id' => $common_data['DrugDocumentStatus_id'],
			'SubAccountType_sid' => $common_data['SubAccountType_id'],
			'Storage_sid' => $common_data['Storage_id'],
			'pmUser_id' => $data['pmUser_id'],
			'WhsDocumentCostItemType_id' => $recept_data['WhsDocumentCostItemType_id']
		));
		if (is_array($response) && count($response) > 0 && isset($response[0]['DocumentUc_id']) && $response[0]['DocumentUc_id'] > 0) {
			$doc_id = $response[0]['DocumentUc_id'];
		}
		if ($doc_id <= 0) {
			$this->rollbackTransaction();
			return array(0 => array('Error_Msg' => 'Не удалось создать документ реализации'));
		}

		//получение коэфицентов для рассчета суммы НДС
		$nds_koef = array();
		$query = "
			select
				DrugNds_id,
				1-(100/(100.0+DrugNds_Code)) as koef
			from
				v_DrugNds with (nolock)
		";
		$result = $this->db->query($query);
		if (is_object($result)) {
			$result = $result->result('array');
			foreach($result as $nds_data) {
				$nds_koef[$nds_data['DrugNds_id']] = $nds_data['koef'];
			}
		}
		//echo '<pre>' . print_r($series_data, 1) . '</pre>'; exit;
		if (!(isset($data['DocumentUc_id']) && $data['DocumentUc_id'] > 0)) {
			//формирование спецификации документа реализации
			foreach($series_data as &$s_data) {
				//обрабатываем дату

				$godn_date = !empty($s_data->PrepSeries_GodnDate) ? join(array_reverse(preg_split('/[.]/',$s_data->PrepSeries_GodnDate)),'-') : null;

				//рассчитываем суммы
				$sum = $s_data->DrugOstatRegistry_Cost > 0 ? $s_data->DrugOstatRegistry_Cost*$s_data->Kolvo : null;

				$dus_data = $this->saveDocumentUcStr(array(
					'DocumentUc_id' => $doc_id,
					'DocumentUcStr_oid' => $s_data->DocumentUcStr_id,
					'Drug_id' => $s_data->Drug_id, //Drug_id,
					'DrugFinance_id' => $recept_data['DrugFinance_id'],
					'DocumentUcStr_Price' => $s_data->DrugOstatRegistry_Cost,
					'DocumentUcStr_PriceR' => $s_data->DrugOstatRegistry_Cost,
					'DrugNds_id' => $s_data->DrugNds_id,
					'DocumentUcStr_Count' => $s_data->Kolvo,
					'DocumentUcStr_Sum' => round($sum, 2),
					'DocumentUcStr_SumR' => round($sum, 2),
					'DocumentUcStr_SumNds' => isset($nds_koef[$s_data->DrugNds_id]) ? round($sum*$nds_koef[$s_data->DrugNds_id], 2) : 0,
					'DocumentUcStr_SumNdsR' => isset($nds_koef[$s_data->DrugNds_id]) ? round($sum*$nds_koef[$s_data->DrugNds_id], 2) : 0,
					'DocumentUcStr_godnDate' => $godn_date,
					'DocumentUcStr_NZU' => 1,
					'DocumentUcStr_Ser' => $s_data->PrepSeries_Ser,
					//'PrepSeries_id' => $s_data->PrepSeries_id,
					'EvnRecept_id' => $recept_data['EvnRecept_id'],
					'ReceptOtov_id' => $price_array[$s_data->DrugOstatRegistry_Cost]['receptotov_id'],
					'DocumentUcStr_IsNDS' => $common_data['Yes_id'],
					'pmUser_id' => $data['pmUser_id'] //
				)); 
				//var_dump($DocumentUcStr_New [0]);

				$query = "
					Declare
						@DrugOstat2DocumentUcStr_id bigint,
						@Error_Code int,
						@Error_Message varchar(4000);
	
	
					exec r2.p_DrugOstat2DocumentUcStr_ins
						@DrugOstat2DocumentUcStr_id = @DrugOstat2DocumentUcStr_id output,
						@DrugOstatRegistry_id = :DrugOstatRegistry_id,
						@DocumentUcStr_id =  :DocumentUcStr_id,
						@pmUser_id = :pmUser_id,
						@Error_Code = @Error_Code output,
						@Error_Message = @Error_Message output
				";
				$params = array(
					'DrugOstatRegistry_id' => $s_data->DrugOstatRegistry_id,
					'DocumentUcStr_id' => $dus_data [0] ['DocumentUcStr_id'],
					'pmUser_id' => $data['pmUser_id']
				);
				//echo getDebugSql($query, $params); exit;
				$result = $this->getFirstRowFromQuery($query, $params);
			}
			
			//копирование списка штрих кодов в строки документа учета
			if (is_array($dus_data) && !empty($dus_data[0]) && !empty($dus_data[0]['DocumentUcStr_id'])) {

				if (!empty($s_data->BarCode_Data)) {
					//разбираем массив со штрих кодами на отдельные элементы и копируем в новую строку документа учета
					$bc_arr = explode(',', $s_data->BarCode_Data); //коды передаются в виде списка конструкций вида "идентификатор|код" перечисленных через запятую
					foreach ($bc_arr as $bc_item) {
						$bc_data = preg_split('/\|/', $bc_item);
						if (is_array($bc_data) && count($bc_data) == 2 && !empty($bc_data[0]) && !empty($bc_data[1])) {
							$bc_data = $this->copyObject('DrugPackageBarCode', array(
								'DrugPackageBarCode_id' => $bc_data[0],
								'DocumentUcStr_id' => $dus_data[0]['DocumentUcStr_id']
							));
						}
					}
				}
			}
			
		}
		//$this->rollbackTransaction();
		//return array(array('Error_Msg' => 'Остановлено отладкой'));

		//коммит транзакции
		$this->commitTransaction();

		return array(array('Error_Msg' => null));
	}

	/**
	 *  Функция
	 */
		function farm_loadDocumentUcView($data) {
		if (!(($data['start'] >= 0) && ($data['limit'] >= 0))) 
		{
			return false;
		}
		
		//  Получаем данные сессии
		$sp = getSessionParams();

		$params = array();
		$filter = "(1=1)";
		if  ($sp['session']['orgtype'] == 'dep')
			$sp['Contragent_id'] = 166; 
		$table = 'v_DocumentUc';
		if ($sp['Contragent_id'] == 166)
			$table = 'r2.v_DocumentUcRas';
		
		// Выбираем только документы для этой аптеки/контрагента
                //$sp['Contragent_id'] = '222';
		if ((isset($sp['Contragent_id'])) && ($sp['Contragent_id']>0))
		{
			$filter = $filter." and DocUc.Contragent_id = :Contragent_id";
			$params['Contragent_id'] = $sp['Contragent_id'];
		}
		else 
		{
			$filter = $filter." and isnull(DocUc.Lpu_id, 0) = :Lpu_id";
			$params['Lpu_id'] = $sp['Lpu_id'];
		}
		/*
		// И/или берем только один какой-то документ
		if ((isset($data['DocumentUc_id'])) && ($data['DocumentUc_id']>0))
		{
			$filter = $filter." and DocUc.DocumentUc_id = :DocumentUc_id";
			$params['DocumentUc_id'] = $data['DocumentUc_id'];
		}
		*/
		
		// Кроме того, выбираем документы только определенного типа 
		if ((isset($data['DrugDocumentType_id'])) && ($data['DrugDocumentType_id']>0))
		{
			$filter = $filter." and DocUc.DrugDocumentType_id = :DrugDocumentType_id";
			$params['DrugDocumentType_id'] = $data['DrugDocumentType_id'];
		}
		
		// Фильтры
		if ((isset($data['Contragent_tid'])) && ($data['Contragent_tid']>0)) {
			$filter .= " and DocUc.Contragent_tid = :Contragent_tid";
			$params['Contragent_tid'] = $data['Contragent_tid'];
		}
		if ((isset($data['Contragent_sid'])) && ($data['Contragent_sid']>0)) {
			$filter .= " and DocUc.Contragent_sid = :Contragent_sid";
			$params['Contragent_sid'] = $data['Contragent_sid'];
		}
		if ((isset($data['Mol_tid'])) && ($data['Mol_tid']>0)) {
			$filter .= " and DocUc.Mol_tid = :Mol_tid";
			$params['Mol_tid'] = $data['Mol_tid'];
		}
		if ((isset($data['DocumentUc_Num'])) && !empty($data['DocumentUc_Num'])) {
			$filter .= " and DocUc.DocumentUc_Num = :DocumentUc_Num";
			$params['DocumentUc_Num'] = $data['DocumentUc_Num'];
		}
		if (isset($data['DocumentUc_setDate']) && !empty($data['DocumentUc_setDate'])) {
			$filter .= " and DocUc.DocumentUc_setDate = :DocumentUc_setDate";
			$params['DocumentUc_setDate'] = $data['DocumentUc_setDate'];
		}
		if (!empty($data['DocumentUc_setDate_range'][0]) && !empty($data['DocumentUc_setDate_range'][1])) {
			$filter .= " and DocUc.DocumentUc_setDate between :begDate and :endDate";
			$params['begDate'] = $data['DocumentUc_setDate_range'][0];
			$params['endDate'] = $data['DocumentUc_setDate_range'][1];
		}
		if (isset($data['begDate']) && !empty($data['begDate']) && isset($data['endDate']) && !empty($data['endDate'])) {
			$filter .= " and DocUc.DocumentUc_setDate between :begDate and :endDate";
			$params['begDate'] = $data['begDate'];
			$params['endDate'] = $data['endDate'];
		}
		if ((isset($data['DrugFinance_id'])) && !empty($data['DrugFinance_id'])) {
			$filter .= " and DocUc.DrugFinance_id = :DrugFinance_id";
			$params['DrugFinance_id'] = $data['DrugFinance_id'];
		}
		if ((isset($data['WhsDocumentCostItemType_id'])) && !empty($data['WhsDocumentCostItemType_id'])) {
			$filter .= " and DocUc.WhsDocumentCostItemType_id = :WhsDocumentCostItemType_id";
			$params['WhsDocumentCostItemType_id'] = $data['WhsDocumentCostItemType_id'];
		}
		if (!empty($data['DrugDocumentClass_id'])) {
			$filter .= " and DocUc.DrugDocumentClass_id = :DrugDocumentClass_id";
			$params['DrugDocumentClass_id'] = $data['DrugDocumentClass_id'];
		}
		if (!empty($data['DrugDocumentStatus_id'])) {
			$filter .= " and DocUc.DrugDocumentStatus_id = :DrugDocumentStatus_id";
			$params['DrugDocumentStatus_id'] = $data['DrugDocumentStatus_id'];
		}
		
		if (!empty($data['Org_sINN'])) {
			$filter .= " and sOrg.Org_INN = :Org_sINN";
			$params['Org_sINN'] = $data['Org_sINN'];
		}
		
		if (!empty($data['Org_tINN'])) {
			$filter .= " and tOrg.Org_INN = :Org_tINN";
			$params['Org_tINN'] = $data['Org_tINN'];
		}
		
		$fields1 = "";
		$fields4 = "";
		$fields9 = "";

		Switch ($data['DrugDocumentType_id'])
		{
			case 1:
				$fields1 = "RTrim(DocUc.DocumentUc_DogNum) as DocumentUc_DogNum, convert(varchar(10), DocUc.DocumentUc_DogDate, 104) as DocumentUc_DogDate,";
				break;
			case 4:
				$fields4 = "RTrim(DocUc.DocumentUc_InvNum) as DocumentUc_InvNum, convert(varchar(10), DocUc.DocumentUc_InvDate, 104) as DocumentUc_InvDate,";
				break;
			case 9:
				$fields9 = "convert(varchar(10), DocUc.DocumentUc_planDT, 104) as DocumentUc_planDate,";
				break;
			default: 
				$order = "";
				break;
		}
		
		// Выбираем DrugFinance_id - все документы отображаются только в "своих" отделах 
		if ((isset($data['FarmacyOtdel_id'])) && ($data['FarmacyOtdel_id']>0))
		{
			$filter = $filter." and (DocUc.DrugFinance_id = :DrugFinance_id or DocUc.DrugFinance_id is null)";
			$params['DrugFinance_id'] = $data['FarmacyOtdel_id'];
		}
		
		$filter .= ' and DocUc.DrugDocumentType_id not in (16)';

		$query = "
			Select 
				-- select
				DocUc.DocumentUc_id,
				{$fields1}
				{$fields4}
				{$fields9}
				DocUc.DrugDocumentStatus_id,
				DrugDocumentStatus_Code,
				RTrim(case
					when DrugDocumentStatus_Name is null then 'Нет статуса'
					else DrugDocumentStatus_Name
				end) as DrugDocumentStatus_Name,
				DocUc.DrugDocumentType_id,
				RTrim(DrugDocumentType_Code) as DrugDocumentType_Code,
				RTrim(DrugDocumentType_Name) as DrugDocumentType_Name,
				RTrim(DocUc.DocumentUc_Num) as DocumentUc_Num,
				convert(varchar(10), DocUc.DocumentUc_setDate, 104) as DocumentUc_setDate,
				DocUc.DocumentUc_didDate,
				convert(varchar(10), DocUc.DocumentUc_didDate, 104) as DocumentUc_txtdidDate, -- постраничный вывод не понимает alias-ов
				DocUc.DrugFinance_id,
				DocUc.Contragent_tid,
				DocUc.Mol_tid,
				RTrim(T.Contragent_Name) as Contragent_tName,
				TOrg.Org_INN as Org_tINN,
				DocUc.Contragent_sid,
				DocUc.Mol_sid,
				RTrim(S.Contragent_Name) as Contragent_sName,
				SOrg.Org_INN as Org_sINN,
				RTrim(tStorage.Storage_Name) as Storage_tName,
				RTrim(sStorage.Storage_Name) as Storage_sName,
				DocumentUc_Sum,
				(
					select
						sum(
							(case
								when
									isnull(isnds.YesNo_Code, 0) = 1
								then
									isnull(dus.DocumentUcStr_Price, 0)
								else
									cast(isnull(dus.DocumentUcStr_Price, 0)*(1+(isnull(dn.DrugNds_Code, 0)/100.0)) as decimal(12,2))
							end) * isnull(isnull(DocumentUcStr_RashCount, DocumentUcStr_Count), 0)
						)
					from
						v_DocumentUcStr dus with (nolock)
						left join v_YesNo isnds with (nolock) on isnds.YesNo_id = dus.DocumentUcStr_IsNDS
						left join v_DrugNds dn with (nolock) on dn.DrugNds_id = dus.DrugNds_id
					where
						dus.DocumentUc_id = DocUc.DocumentUc_id
				) as DocumentUcStr_NdsSum,
				DocumentUc_SumR,
				DF.DrugFinance_Name,
				WDCIT.WhsDocumentCostItemType_Name,
				DocUc.SubAccountType_sid,
				sAcc.SubAccountType_Name SubAccountType_sName,
				DocUc.SubAccountType_tid,
				tAcc.SubAccountType_Name SubAccountType_tName,
				COALESCE(bc.isKM, 1) isKM
				-- end select
			from 
				-- from
				--v_DocumentUc DocUc with (nolock)
				{$table} DocUc with (nolock)
				left join Contragent T with (nolock) on T.Contragent_id = DocUc.Contragent_tid --потребитель
				left join Org TOrg  with (nolock) on TOrg.Org_id = T.Org_id
				left join Contragent S with (nolock) on S.Contragent_id = DocUc.Contragent_sid --поставщик
				left join Org SOrg  with (nolock) on SOrg.Org_id = S.Org_id
				left join v_DrugFinance DF with (nolock) on DF.DrugFinance_id = DocUc.DrugFinance_id
				left join v_WhsDocumentCostItemType WDCIT with (nolock) on WDCIT.WhsDocumentCostItemType_id = DocUc.WhsDocumentCostItemType_id
				left join v_DrugDocumentStatus DDS with (nolock) on DDS.DrugDocumentStatus_id = DocUc.DrugDocumentStatus_id
				left join v_DrugDocumentType DDT with (nolock) on DDT.DrugDocumentType_id = DocUc.DrugDocumentType_id
				left join v_Storage tStorage with (nolock) on tStorage.Storage_id = DocUc.Storage_tid
				left join v_Storage sStorage with (nolock) on sStorage.Storage_id = DocUc.Storage_sid
				left join v_SubAccountType tAcc with (nolock) on  tAcc.SubAccountType_id = DocUc.SubAccountType_sid
				left join v_SubAccountType sAcc with (nolock) on  sAcc.SubAccountType_id = DocUc.SubAccountType_sid
				outer apply (Select top 1 2 isKM, dus.DocumentUcStr_id from v_DocumentUcStr dus with (nolock) 
					inner join v_DrugPackageBarCode bc with (nolock) on bc.DocumentUcStr_id = dus.DocumentUcStr_id
					where dus.DocumentUc_id = DocUc.DocumentUc_id
					) bc
				-- end from
			where
				-- where
				{$filter}
				-- end where
			order by 
				-- order by
				DocumentUc_didDate desc  
				-- end order by
		";
		//echo getDebugSql(getLimitSQLPH($query, 0, 100), $params);exit;
                                
        //$dbrep = $this->load->database('bdwork', true);
		$dbrep = $this->db;
                
		$result = $dbrep->query(getLimitSQLPH($query, $data['start'], $data['limit']), $params);
		$result_count = $dbrep->query(getCountSQLPH($query), $params);
		if (is_object($result_count))
		{
			$cnt_arr = $result_count->result('array');
			$count = $cnt_arr[0]['cnt'];
			unset($cnt_arr);
		}
		else
		{
			$count = 0;
		}
		if (is_object($result))
		{
			$response = array();
			$response['data'] = $result->result('array');
			$response['totalCount'] = $count;
			return $response;
		}
		else
		{
			return false;
		}
	}

        	/**
	 *  Функция
	 */
	function saveDocumentUcStr($data) {
		$procedure = "";

		if ( !isset($data['DocumentUcStr_id']) ) {
			$procedure = "p_DocumentUcStr_ins";
		}
		else {
			$procedure = "p_DocumentUcStr_upd";
			// предварительно проверить есть ли связанный введенный учет по текущей партии 
			if ($this->isDocumentUcStrExistChanges($data)) {
				// и если есть - не разрешать изменять документ 
				return array(0 => array('Error_Msg' => 'Редактирование строки документа невозможно, поскольку<br/>данный медикамент используется в других документах учета!'));
			}
		}

		if (empty($data['DrugProducer_id']) && (!empty($data['DrugProducer_New']))) {
			$data['DrugProducer_id'] = $this->DrugProducerAdd($data);
		}

		if ((!isset($data['PrepSeries_id']) || $data['PrepSeries_id'] <= 0) && isset($data['DocumentUcStr_Ser']) && !empty($data['DocumentUcStr_Ser'])) {
			$data['PrepSeries_Ser'] = $data['DocumentUcStr_Ser'];
			$data['PrepSeries_GodnDate'] = $data['DocumentUcStr_godnDate'];
			//$data['PrepSeries_id'] = $this->PrepSeriesAdd($data);
		}
		
                //echo '<pre>' . print_r($data, 1) . '</pre>';
		$query = "
			declare
				@Res bigint,
				@ErrCode bigint,
				@ErrMsg varchar(4000);
			set @Res = :DocumentUcStr_id;

			exec " . $procedure . "
				@DocumentUcStr_id = @Res output,
				@DocumentUcStr_oid = :DocumentUcStr_oid,
				@DocumentUc_id = :DocumentUc_id,
				@Drug_id = :Drug_id,
				@DrugFinance_id = :DrugFinance_id,
				@DocumentUcStr_Price = :DocumentUcStr_Price,
				@DocumentUcStr_PriceR = :DocumentUcStr_PriceR,
				@DocumentUcStr_Count = :DocumentUcStr_Count,
				@DrugNds_id = :DrugNds_id,
				@DocumentUcStr_EdCount = :DocumentUcStr_EdCount,
				@DocumentUcStr_SumR = :DocumentUcStr_SumR,
				@DocumentUcStr_Sum = :DocumentUcStr_Sum,
				@DocumentUcStr_SumNds = :DocumentUcStr_SumNds,
				@DocumentUcStr_SumNdsR = :DocumentUcStr_SumNdsR,
				@DocumentUcStr_godnDate = :DocumentUcStr_godnDate,
				@DocumentUcStr_NZU = :DocumentUcStr_NZU,
				@DocumentUcStr_IsLab = :DocumentUcStr_IsLab,
				@PrepSeries_id = :PrepSeries_id,
				@DrugProducer_id = :DrugProducer_id,
				@DrugLabResult_Name = :DrugLabResult_Name,
				@DocumentUcStr_Ser = :DocumentUcStr_Ser,
				@DocumentUcStr_CertNum = :DocumentUcStr_CertNum,
				@DocumentUcStr_CertDate = :DocumentUcStr_CertDate,
				@DocumentUcStr_CertGodnDate = :DocumentUcStr_CertGodnDate,
				@DocumentUcStr_CertOrg = :DocumentUcStr_CertOrg,
				@ReceptOtov_id = :ReceptOtov_id,
				@EvnRecept_id = :EvnRecept_id,
				@DocumentUcStr_PlanPrice = :DocumentUcStr_PlanPrice,
				@DocumentUcStr_PlanKolvo = :DocumentUcStr_PlanKolvo,
				@DocumentUcStr_PlanSum = :DocumentUcStr_PlanSum,
				@Person_id = :Person_id,
				@Okei_id = :Okei_id,
				@DocumentUcStr_IsNDS = :DocumentUcStr_IsNDS,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMsg output;
			select @Res as DocumentUcStr_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg; 
		";
		//@DocumentUcStr_RashCount = :DocumentUcStr_RashCount,
				
		$queryParams = array(
			'DocumentUcStr_id' => isset($data['DocumentUcStr_id']) && !empty($data['DocumentUcStr_id']) ? $data['DocumentUcStr_id'] : null,
			'DocumentUcStr_oid' => isset($data['DocumentUcStr_oid']) && !empty($data['DocumentUcStr_oid']) ? $data['DocumentUcStr_oid'] : null,
			'DocumentUc_id' => $data['DocumentUc_id'],
			'Drug_id' => $data['Drug_id'],
			'DrugFinance_id' => !empty($data['DrugFinance_id']) ? $data['DrugFinance_id'] : null,
			'DocumentUcStr_Price' => $data['DocumentUcStr_Price'],
			'DocumentUcStr_PriceR' => isset($data['DocumentUcStr_PriceR']) && !empty($data['DocumentUcStr_PriceR']) ? $data['DocumentUcStr_PriceR'] : null,
			'DrugNds_id' => !empty($data['DrugNds_id']) ? $data['DrugNds_id'] : null,
			'DocumentUcStr_Count' => $data['DocumentUcStr_Count'],
			'DocumentUcStr_EdCount' => isset($data['DocumentUcStr_EdCount']) && !empty($data['DocumentUcStr_EdCount']) ? $data['DocumentUcStr_EdCount'] : null,
			'DocumentUcStr_Sum' => $data['DocumentUcStr_Sum'],
			'DocumentUcStr_SumR' => isset($data['DocumentUcStr_SumR']) && !empty($data['DocumentUcStr_SumR']) ? $data['DocumentUcStr_SumR'] : null,
			'DocumentUcStr_SumNds' => isset($data['DocumentUcStr_SumNds']) && !empty($data['DocumentUcStr_SumNds']) ? $data['DocumentUcStr_SumNds'] : null,
			'DocumentUcStr_SumNdsR' => isset($data['DocumentUcStr_SumNdsR']) && !empty($data['DocumentUcStr_SumNdsR']) ? $data['DocumentUcStr_SumNdsR'] : null,
			'DocumentUcStr_godnDate' => isset($data['DocumentUcStr_godnDate']) && !empty($data['DocumentUcStr_godnDate']) ? $data['DocumentUcStr_godnDate'] : null,
			'DocumentUcStr_NZU' => isset($data['DocumentUcStr_NZU']) && !empty($data['DocumentUcStr_NZU']) ? $data['DocumentUcStr_NZU'] : null,
			'DocumentUcStr_IsLab' => isset($data['DocumentUcStr_IsLab']) && !empty($data['DocumentUcStr_IsLab']) ? $data['DocumentUcStr_IsLab'] : null,
			//'DocumentUcStr_RashCount' => $data['DocumentUcStr_RashCount'],
			'PrepSeries_id' => isset($data['PrepSeries_id']) && !empty($data['PrepSeries_id']) ? $data['PrepSeries_id'] : null,
			'DrugProducer_id' => isset($data['DrugProducer_id']) && !empty($data['DrugProducer_id']) ? $data['DrugProducer_id'] : null,
			'DrugLabResult_Name' => isset($data['DrugLabResult_Name']) && !empty($data['DrugLabResult_Name']) ? $data['DrugLabResult_Name'] : null,
			'DocumentUcStr_Ser' => isset($data['DocumentUcStr_Ser']) && !empty($data['DocumentUcStr_Ser']) ? $data['DocumentUcStr_Ser'] : null,
			'DocumentUcStr_CertNum' => isset($data['DocumentUcStr_CertNum']) && !empty($data['DocumentUcStr_CertNum']) ? $data['DocumentUcStr_CertNum'] : null,
			'DocumentUcStr_CertDate' => isset($data['DocumentUcStr_CertDate']) && !empty($data['DocumentUcStr_CertDate']) ? $data['DocumentUcStr_CertDate'] : null,
			'DocumentUcStr_CertGodnDate' => isset($data['DocumentUcStr_CertGodnDate']) && !empty($data['DocumentUcStr_CertGodnDate']) ? $data['DocumentUcStr_CertGodnDate'] : null,
			'DocumentUcStr_CertOrg' => isset($data['DocumentUcStr_CertOrg']) && !empty($data['DocumentUcStr_CertOrg']) ? $data['DocumentUcStr_CertOrg'] : null,
			'ReceptOtov_id' => isset($data['ReceptOtov_id']) && !empty($data['ReceptOtov_id']) ? $data['ReceptOtov_id'] : null,
			'EvnRecept_id' => isset($data['EvnRecept_id']) && !empty($data['EvnRecept_id']) ? $data['EvnRecept_id'] : null,
			'DocumentUcStr_PlanKolvo' => !empty($data['DocumentUcStr_PlanKolvo']) ? $data['DocumentUcStr_PlanKolvo'] : null,
			'DocumentUcStr_PlanPrice' => (!empty($data['DocumentUcStr_PlanPrice']) ? $data['DocumentUcStr_PlanPrice'] : null),
			'DocumentUcStr_PlanSum' => !empty($data['DocumentUcStr_PlanSum']) ? $data['DocumentUcStr_PlanSum'] : null,
			'Person_id' => !empty($data['Person_id']) ? $data['Person_id'] : null,
			'Okei_id' => !empty($data['Okei_id']) ? $data['Okei_id'] : null,
			'DocumentUcStr_IsNDS' => !empty($data['DocumentUcStr_IsNDS']) ? $data['DocumentUcStr_IsNDS'] : null,
			'pmUser_id' => $data['pmUser_id']
		);
		/*
		echo getDebugSql($query, $queryParams);
		exit;
		*/
		$result = $this->db->query($query, $queryParams);
		
		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
                
	}
    
		/**
	 * Загрузка контрактов
	 */
	function Contract_importXml($arr_data) {
		$xml = '<RD>';
		$i = 0;
		foreach ($arr_data as $item) {
			$Contract_Num = "";
			$Contract_Date = "";
			$Contract_BegDate = "";
			$Contract_EndDate = "";
			$Org_INN = "";
			$Org_Name = "";
			$Contract_Summ = "";
			$Org_Name = "";
			if (isset($item['Org_INN'])) {
				if (isset($item['Contract_Num']))
					$Contract_Num = $item['Contract_Num'];
				if (isset($item['Contract_Date']))
					$Contract_Date = $item['Contract_Date'];
				if (isset($item['Contract_BegDate']))
					$Contract_BegDate = $item['Contract_BegDate'];
				if (isset($item['Contract_EndDate']))
					$Contract_EndDate = $item['Contract_EndDate'];
				if (isset($item['Org_INN']))
					$Org_INN = $item['Org_INN'];
				if (isset($item['Org_Name']))
					$Org_Name = str_replace(" ", "|*|", $item['Org_Name']);
				if (isset($item['Org_KPP']) && strlen($item['Org_KPP']) > 0)
					$Org_Name .= 'Org_KPP' . $item['Org_KPP'];

				if (isset($item['Contract_Summ']))
					$Contract_Summ = $item['Contract_Summ'];
				if (isset($item['Contract_Sum']))
					$Contract_Summ = $item['Contract_Sum'];
				if (isset($item['KBK']))
					$KBK = $item['KBK'];
				$i += 1;

				$xml .='<R|*|Contract_Num="' . $Contract_Num . '" 
					 |*|Contract_Date="' . $Contract_Date . '"
					 |*|Contract_BegDate="' . $Contract_BegDate . '"
					 |*|Contract_EndDate="' . $Contract_EndDate . '"
					 |*|Org_INN="' . $Org_INN . '" 
					 |*|Org_Name="' . str_replace('"', '|', $Org_Name) . '"
					 |*|Contract_Summ="' . $Contract_Summ . '"
					 |*|KBK="' . $KBK . '" ></R>';
				//}
			}
		}

		$xml .= '</RD>';

		$xml = strtr($xml, array(PHP_EOL => '', " " => ""));
		$xml = str_replace("|*|", " ", $xml);

		$params = array('xml' => (string) $xml);

		$query = "
		Declare 
		@xml nvarchar(max),
		@Error_Code int,
		@Count int,
		@Error_Message varchar(4000)

			Set @xml = :xml;
			
			--delete  tmp.farmContract;

			 exec r2.p_saveContractByFarmacy @xml, @Count output, @Error_Code output, @Error_Message output

			 Select @Count as Cnt, @Error_Code as Error_Code, @Error_Message as Error_Msg ;  
			 
				";

		//echo getDebugSql($query, $params);exit;

		$result = $this->db->query($query, $params);

		if (is_object($result)) {

			if (!is_object($result))
				return array('success' => false, 'Error_Msg' => 'Ошибка при выполнении запроса к базе данных');
			$res = $result->result('array');

			return $result->result('array');
		} else {
			return array(0 => array('success' => false, 'Error_Msg' => 'При выполнении операции возникла ошибка'));
		}
	}
	
	    /**
     * Получение списка остатков для обеспечения рецепта
     */
    function getDrugOstatForProvideFromBarcode($data) {
        $this->load->model('DocumentUc_model', 'DocumentUc_model');
        $default_goods_unit_id = $this->DocumentUc_model->getDefaultGoodsUnitId();
        $and = '';
        $and_drug = '';
        $select = '';
        $join = '';
        $nomen_join = '';

        $data['DefaultGoodsUnit_id'] = $default_goods_unit_id;

        //определяем по идентификатору рецепта оригинальный медикамент
        $data['Drug_rlsid'] = null;
        if (!empty($data['EvnRecept_id'])) {
            $query = "
                select
                    Drug_rlsid
                from
                    v_EvnRecept with (nolock)
                where
                    EvnRecept_id = :EvnRecept_id
            ";
            $data['Drug_rlsid'] = $this->getFirstResultFromQuery($query, array(
                'EvnRecept_id' => $data['EvnRecept_id']
            ));
			
			
        }
		/*
        if (!empty($data['MedService_id']) && $data['MedService_id'] > 0) {
            $and = ' and (dor.Storage_id is null or dor.Storage_id in (
					select Storage_id from StorageStructLevel with(nolock) where MedService_id = :MedService_id)
				)';
        }
		*/

        if(isset($data['Drug_ean']) && $data['Drug_ean'] != '') {
            $and_drug .= ' and d.drug_ean = :Drug_ean';
        }

        if(isset($data['Drug_id']) && $data['Drug_id'] != '') {
            $and_drug .= ' and d.Drug_id = :Drug_id';
        }

        if(isset($data['DrugFinance_id']) && $data['DrugFinance_id'] != '') {
            $and .= ' and dor.DrugFinance_id = :DrugFinance_id';
        }

        if(isset($data['WhsDocumentCostItemType']) && $data['WhsDocumentCostItemType'] != '') {
            $and .= ' and dor.WhsDocumentCostItemType_id = :WhsDocumentCostItemType';
        }

        if(!empty($data['DrugPackageBarCode_BarCode'])) {
            //проверка не выбыл ли из системы данный штрих-код
			$gtin = "'" .substr($data['DrugPackageBarCode_BarCode'], 2, 14) ."'";
			$IndNum = "'" .substr($data['DrugPackageBarCode_BarCode'], 18, 13) ."'";
			
			$select .= ', dpbc.DrugPackageBarCode_id';

            $and .= ' and gu.GoodsUnit_id = :DefaultGoodsUnit_id'; //при поиске по штрих-коду берем строки регистра только с упаковкой в качестве ед. учета
        }

		if(isset($data['SubAccountTypeIsReserve'])) {
			$and .= ' and dor.SubAccountType_id != 2';
		}
		
		$data['Org_id'] = $data['session']['org_id'];

        $sql = "
			
			Declare
				@Org_id bigint,
				@lpu_id bigint,
				@BarCode varchar (100);
				
			Set @Org_id = :Org_id;
			Set @BarCode = :DrugPackageBarCode_BarCode;
			
			with DocRash_tmp as (
			Select 
				case 
					when du.DrugDocumentType_id in (6)  
						then round(DrugPackageBarCode_nSecPack / DrugPackageBarCode_dSecPack, 3) 
					else 0
				end Pr_kol,
				case
					when du.DrugDocumentType_id in (10, 11, 17)  
							then round(DrugPackageBarCode_nSecPack / DrugPackageBarCode_dSecPack , 3) 
						else 0
				end Ras_kol,
				case
					when du.DrugDocumentType_id in (10, 11, 17)  
						then case when  du.DrugDocumentType_id = 11 then 'Рецепт' else dtp.DrugDocumentType_Name end  + ' №' + du.DocumentUc_Num + ' от ' + convert(varchar, du.DocumentUc_didDate, 104) 
					else ''
				end Сomment,
				du.DrugDocumentType_id
				from  DrugPackageBarCode bc with (nolock)
					join DocumentUcStr dus  with (nolock) on dus.DocumentUcStr_id = bc.DocumentUcStr_id
				join v_DocumentUc du  with (nolock) on du.DocumentUc_id = dus.DocumentUc_id
				join v_DrugDocumentType dtp  with (nolock) on dtp.DrugDocumentType_id = du.DrugDocumentType_id
				where bc.DrugPackageBarCode_BarCode = @BarCode
			),
			DocRash as (
			select 1 id, sum(Pr_kol) Pr_kol, sum(Ras_kol) Ras_kol,
				(SELECT t.Сomment +',' FROM DocRash_tmp t where DrugDocumentType_id in (10, 11, 17) FOR XML PATH(''))  Сomment
			 from DocRash_tmp
			 )
            select top 1
                d.Drug_id,
				dor.DrugOstatRegistry_id,
				dor.DrugOstatRegistry_Kolvo,
				dor.DrugOstatRegistry_Cost,
				dus.DocumentUcStr_id,
				COALESCE(ds.DrugShipment_Name, '') as DrugShipment_Name,
				substring(d.Drug_Name, 0, 15)+'...' as Drug_ShortName,
				d.Drug_Name,
				ps.PrepSeries_id,
				COALESCE(dus.DocumentUcStr_Ser, '') as PrepSeries_Ser,
				COALESCE(convert(varchar(10), dus.DocumentUcStr_godnDate, 104), '') as PrepSeries_GodnDate,
				COALESCE(isdef.YesNo_Code, 0) as PrepSeries_isDefect,
				'упак' as Okei_NationSymbol,
				cast(COALESCE(dus.DocumentUcStr_Price, 0) as decimal(14,2)) as DocumentUcStr_Price,
				COALESCE(isnds.YesNo_Code, 0) as DocumentUcStr_IsNDS,
				dus.DocumentUcStr_Sum,
				dn.DrugNds_id,
				dn.DrugNds_Code,
				dus.DocumentUcStr_SumNds,
				COALESCE(dus.DocumentUcStr_Sum, 0) + COALESCE(dus.DocumentUcStr_SumNds, 0) as DocumentUcStr_NdsSum,
				COALESCE(df.DrugFinance_Name, '') as DrugFinance_Name,
				COALESCE(wdcit.WhsDocumentCostItemType_Name, '') as WhsDocumentCostItemType_Name,
				df.DrugFinance_Name + ' / ' + wdcit.WhsDocumentCostItemType_Name as Finance_and_CostItem,
				COALESCE(wdssd.WhsDocumentSupplySpecDrug_Coeff, 1) as WhsDocumentSupplySpecDrug_Coeff,
				gu.GoodsUnit_id,
				gu.GoodsUnit_Nick,
				d.Drug_Fas as GoodsPackCount_Count,
				Drug_Ctrl,
				case when COALESCE(du.Org_id, -1) =  @Org_id then 2 else 1 end as Org_Ctrl,
				o.Org_Nick,
				case when COALESCE(du.DrugDocumentStatus_id, -1) in (2, 7) then 2 else 1 end as DrugDocumentStatus_Ctrl,
				'№' + du.DocumentUc_Num + ' от ' + convert(varchar, du.DocumentUc_setDate, 104) as DocumentUc_Num,  
				case when DocRash.id is null or DocRash.Pr_kol > DocRash.Ras_kol then 2 else 1 end Rash_Ctrl,
					SUBSTRING(DocRash.Сomment, 1, len(DocRash.Сomment) - 1) Rash_Comment,
				case when dor.DrugOstatRegistry_Kolvo > 0 then 2 else 1 end Ost_Ctrl,
				case when er.WhsDocumentCostItemType_id = dor.WhsDocumentCostItemType_id  then 2 else 1 end WhsDocumentCostItemType_Ctrl
				, dpbc.DrugPackageBarCode_id
				{$select}
			from
				v_DrugPackageBarCode dpbc with (nolock)
				inner join v_DocumentUcStr dus with (nolock) on dus.DocumentUcStr_id = dpbc.DocumentUcStr_id
				inner join v_DocumentUc du with (nolock) on du.DocumentUc_id = dus.DocumentUc_id
					and du.DrugDocumentType_id = 6 --and du.DrugDocumentStatus_id in (2, 7)
				inner join v_Org o  with (nolock) on o.Org_id = du.Org_id	
				inner join dbo.v_Drug d with(nolock) on d.Drug_id = dus.Drug_id
				inner join v_DrugShipmentLink dsl with (nolock) on dsl.DocumentUcStr_id = dus.DocumentUcStr_id
				left join v_DrugShipment ds with (nolock) on ds.DrugShipment_id = dsl.DrugShipment_id
				/*
				left join v_DrugOstatRegistry dor with (nolock) on dor.Drug_did = dus.Drug_id
					and dor.DrugShipment_id = dsl.DrugShipment_id
					and dor.Org_id = du.Org_id
				*/	
				outer apply (Select top 1 er.drug_id, EvnRecept_IsMnn, dr.DrugMnn_id, 
					case 
						when COALESCE(EvnRecept_IsMnn, 1) = 2 and d.DrugMnn_id = dr.DrugMnn_id 
							then 2
						when  COALESCE(EvnRecept_IsMnn, 1) = 1 and d.Drug_id = dr.Drug_id 
							then 2
						else 1
					end as Drug_Ctrl,
					 er.WhsDocumentCostItemType_id,
					 er.lpu_id
						from v_EvnRecept er
					join v_drug dr on dr.drug_id = er.Drug_id
					where 	EvnRecept_id = :EvnRecept_id 
				) er
				outer apply ( Select lpu_id, dor.* from dbo.v_DrugOstatRegistry dor with (nolock)
					inner join dbo.OrgFarmacy farm  with (nolock) on farm.Org_id = dor.Org_id
						OUTER APPLY( Select Top 1 isnull(farmI.Storage_id, 0) Storage_id, ISNULL(farmI.Lpu_id, 0) Lpu_id
						from dbo.OrgFarmacyIndex farmI with (nolock) 
							where farmI.OrgFarmacy_id = farm.OrgFarmacy_id 
								and isnull(farmI.Storage_id, 0) = isnull(dor.Storage_id, isnull(farmI.Storage_id, 0))
							) farmI
							where  dor.Drug_did = dus.Drug_id
								and dor.DrugShipment_id = dsl.DrugShipment_id
								and dor.Org_id = du.Org_id
								and (farmI.Lpu_id = er.lpu_id or dor.Storage_id is null)
				) dor		
				left join v_DrugNds dn with (nolock) on dn.DrugNds_id = dus.DrugNds_id
				left join rls.v_PrepSeries ps with (nolock) on ps.PrepSeries_id = dus.PrepSeries_id
				left join v_YesNo isdef with (nolock) on isdef.YesNo_id = ps.PrepSeries_isDefect
				left join v_YesNo isnds with (nolock) on isnds.YesNo_id = dus.DocumentUcStr_IsNDS
				left join v_DrugFinance df with (nolock) on df.DrugFinance_id = du.DrugFinance_id
				left join v_WhsDocumentCostItemType wdcit with (nolock) on wdcit.WhsDocumentCostItemType_id = du.WhsDocumentCostItemType_id
				left join v_GoodsUnit gu with (nolock) on gu.GoodsUnit_id = isnull(dor.GoodsUnit_id, '57')
				
				outer apply (
                    select top 1
                        i_wdssd.Drug_sid,
                        i_wdssd.WhsDocumentSupplySpecDrug_Coeff
                    from
                        v_WhsDocumentSupplySpec i_wdss with (nolock)
                        left join v_WhsDocumentSupplySpecDrug i_wdssd with (nolock) on i_wdssd.WhsDocumentSupplySpec_id = i_wdss.WhsDocumentSupplySpec_id
                    where
                        i_wdssd.Drug_id is NULL and  -- пока поправил так
                        i_wdssd.Drug_sid = d.Drug_id and
                        i_wdss.WhsDocumentSupply_id = ds.WhsDocumentSupply_id
                    order by
                        i_wdssd.WhsDocumentSupplySpecDrug_id
                ) wdssd
                left join DocRash on DocRash.Ras_kol >= DocRash.Pr_kol
			where
			    (1=1)
				and (dpbc.DrugPackageBarCode_BarCode = @BarCode 
					or ( dpbc.DrugPackageBarCode_GTIN = {$gtin} and dpbc.DrugPackageBarCode_IndNum = {$IndNum}))
			    --and dor.DrugOstatRegistry_Kolvo > 0
			    and isnull(isdef.YesNo_Code, 0) = 0
                {$and}
                {$and_drug}
            order by
                ps.PrepSeries_GodnDate desc
        ";
		//echo '<pre>' . print_r(getDebugSql($sql, $data), 1) . '</pre>'; ;exit;		
		
        $result = $this->db->query($sql, $data);
		$response = $result->result('array');
       
		 if (is_array($response) && count($response) > 0 ) {
			$rec = $response[0];
			if ($rec['DrugDocumentStatus_Ctrl'] == 1 || $rec['Drug_Ctrl'] == 1 || $rec['Org_Ctrl'] == 1 
					|| $rec['Rash_Ctrl'] == 1 || $rec['Ost_Ctrl'] == 1 || $rec['WhsDocumentCostItemType_Ctrl'] == 1) {
				$mess = '';
				if ($rec['Org_Ctrl'] == 1) {
					$mess = 'Препарат числится за другой аптекой (' .$rec['Org_Nick'] .')';
				}
				else if ($rec['DrugDocumentStatus_Ctrl'] == 1) {
					$mess = 'Документ прихода по данному препарату не исполнен <br />(Документ ' .$rec['DocumentUc_Num'] .')';
				}
				else if ($rec['Drug_Ctrl'] == 1) {
					$mess = 'Отпускаемый препарат не соответствует выписанному';
				}
				else if ($rec['WhsDocumentCostItemType_Ctrl'] == 1) {
					$mess = 'Отпускаемый препарат числится по другой статье расхода (' .$rec['WhsDocumentCostItemType_Name'] .')';
				}
				else if ($rec['Rash_Ctrl'] == 1) {
					$mess = 'Препарат передан получателю <br />(' .$rec['Rash_Comment'] .')';
				}
				else if ($rec['Ost_Ctrl'] == 1) {
					$mess = 'Препарата нет в остатках';
				}
				//return array(array('success' => false, array('Error_Msg' => $mess));
				return array(array('success' => false, 'Error_Msg' => $mess));
				//echo 'СООБЩЕНИЕ ' .$mess .PHP_EOL;
				//return array('success' => false, 'Error_Msg' => $mess);
			}
			$result = $result->result('array');
			$result[0]['success'] = true;
			return $result;
           // return $result->result('array');
        }
		return array(array('success' => false, 'Error_Msg' => 'Информация по данному препарату не найдена'));
    }



	/*
	 function getFirstRowFromQuery2($query, $params = array())
	{
		try {
                        $dbrep = $this->load->database('bdwork', true);
			$dbresult = $dbrep->query($query, $params);
			if (is_object($dbresult)) {
				$response = $dbresult->result('array');
				if (isset($response[0])) {
					$result = $response[0];
				} else {
					$result = false;
				}
			} else {
				$result = false;
			}
		} catch (Exception $e) {
			log_message('error', 'query fails: ' . $query . ' params: ' . implode(', ', array_values($params)) . ' error:' . $e->getMessage());
			$result = false;
		}
		return $result;
	}
	*/
}