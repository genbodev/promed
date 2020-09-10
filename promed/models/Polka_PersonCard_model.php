<?php
/**
* Polka_PersonCard_model - модель, для работы с таблицей Personcard
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Pshenitcyn Ivan aka IvP (ipshon@rambler.ru)
* @version      03.06.2009
*/

class Polka_PersonCard_model extends swModel {
	/**
	 *	Конструктор
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 *
	 * @param type $data
	 * @return type
	 */
	function checkPersonCard($data){
		$CloseDT = '';
		$params = array(
			'Person_id'=>$data['Person_id'],
			'Person_id'=>$data['Person_id'],
			'LPU_CODE' => $data['LPU_CODE']
		);
		if(isset($data['LPUDX'])){
			$CloseDT = " or ISNULL(convert(varchar(10),cast(PersonCard_endDate as datetime),20),'') = ISNULL(:LPUDX,'')";
			$params['LPUDX']=$data['LPUDX'];
		}

		$query = "
			declare @DT datetime;
			set @DT =(select dbo.tzGetDate())

				select top 1 lp.Lpu_Nick,pc.PersonCard_id
				from v_Lpu lp with(nolock)
				outer apply(
				select top 1 PersonCard_id
					from
					v_PersonCard with (nolock)
				where
					Person_id = :Person_id
					and (PersonCard_endDate is null or PersonCard_endDate <=@DT ".$CloseDT." )
					and Lpu_id = lp.Lpu_id
					and LpuAttachType_id = 1
				order by
					PersonCard_begDate desc
				) pc
				where lp.Lpu_f003mcod = :LPU_CODE

		";
		//echo getDebugSQL($query, $params);
		$res = $this->db->query($query, $params);
		if ( is_object($res) )
			return $res->result('array');
		else
			return false;
	}

	/**
	 * Проверка номера карты на уникальность
	 */
	function checkPersonCardUniqueness($data)
	{
		// проверка уникальности номера карты

		$queryParams = array();

		$PersonCard_idFilter = "";
		if ( $data['PersonCard_id'] != NULL ) {
			$PersonCard_idFilter = " and PersonCard_id <> :PersonCard_id";
			$queryParams['PersonCard_id'] = $data['PersonCard_id'];
		}
		$queryParams['Lpu_id'] = $data['Lpu_id'];
		$queryParams['PersonCard_Code'] = $data['PersonCard_Code'];
		$queryParams['Person_id'] = $data['Person_id'];
		$sql = "
			SELECT
				count(*) as chck
			FROM PersonCardState with (nolock)
			WHERE
				Lpu_id = :Lpu_id
				and PersonCardState_Code = :PersonCard_Code
				and Person_id != :Person_id
				{$PersonCard_idFilter}";
		//echo getDebugSql($sql, $queryParams); die();
        $result = $this->db->query($sql, $queryParams);
        if (is_object($result))
        {
            $sel = $result->result('array');
			if ( $sel[0]["chck"] > 0 )
			{
				return array( 0 => array('Error_Code' => 7, 'Error_Msg'=>'Номер карты совпадает с номером уже существующей карты.') );
			}
        }
        else
        {
        	return array( 0 => array('Error_Code' => 666, 'Error_Msg'=>'Не удалось проверить номер карты, попробуйте сохранить еще раз.') );
        }
		return true;
	}

	/**
	 *	Получение данных по последней карте
	 */
	function getOldPersonCard($data)
	{
		$query = "
			select top 1
				Lpu_id,
				rtrim(IsNull(Person_SurName,'')) + ' ' + rtrim(IsNull(Person_FirName,'')) + ' ' + rtrim(isnull(Person_SecName, '')) as Person_FIO
			from
				v_PersonCard with (nolock)
			where
				Person_id = :Person_id
			order by
				PersonCard_begDate desc
		";

		$res = $this->db->query($query, $data);
		if ( is_object($res) )
			return $res->result('array');
		else
			return false;
	}

	/**
	 *	Получение количества прикреплений пациента к МО
	 */
	function getCountAttachPersonInLpu($data)
	{
		$query = "
			select
				COUNT(distinct PersonCard_id) as countAttachment
			from
				v_PersonCard with (nolock)
			where
				Lpu_id = :Lpu_id
				and PersonCard_begDate < dbo.tzGetDate()
				and PersonCard_begDate is not null
				and PersonCard_endDate is null
		";

		$res = $this->db->query($query, $data);
		if ( is_object($res) ) {
			$res = $res->result('array');
			return $res[0]['countAttachment'];
		} else {
			return false;
		}
	}

	/**
	 *	Получение чего-то
	 */
	function getCountDetachPersonInLpu($data)
	{
		$query = "
			select
				COUNT(distinct PersonCard_id) as countDetachment
			from
				v_PersonCard with (nolock)
			where
				Lpu_id = :Lpu_id
				and PersonCard_endDate < dbo.tzGetDate()
				and PersonCard_endDate is not null
		";

		$res = $this->db->query($query, $data);
		if ( is_object($res) ) {
			$res = $res->result('array');
			return $res[0]['countDetachment'];
		} else {
			return false;
		}
	}

	/**
	* Возвращает список карт по заданным фильтрам
	*/
	function getPersonCardHistoryList($data)
	{
		$queryParams = array();
		// проверяем какой тип участка
		$attach_type_filter = "";
		switch ( $data['AttachType'] )
		{
			case 'common_region':
				$attach_type_filter = " and pc.LpuAttachType_id = 1 ";
			break;
			case 'ginecol_region':
				$attach_type_filter = " and pc.LpuAttachType_id = 2 ";
			break;
			case 'stomat_region':
				$attach_type_filter = " and pc.LpuAttachType_id = 3 ";
			break;
			case 'service_region':
				$attach_type_filter = " and pc.LpuAttachType_id = 4 ";
				if ( !isSuperadmin() )
					$attach_type_filter .= " and pc.Lpu_id = :Lpu_id ";
			break;
			case 'dms_region':
				$attach_type_filter = " and pc.LpuAttachType_id = 5 ";
			break;
		}

		$queryParams['Lpu_id'] = $data['Lpu_id'];
		$queryParams['Person_id'] = $data['Person_id'];
		$sql = "
			Declare @gdate as datetime = dbo.tzGetDate();
			SELECT
				pc.Person_id,
				pc.PersonCard_id,
				pc.PersonCard_Code,
				pc.Server_id,
				--convert(varchar(10),cast(pc.PersonCard_insDT as datetime),126) as PersonCard_insDate,
				convert(varchar,cast(PCard.PersonCard_insDT as datetime),104) as PersonCard_insDate,
				convert(varchar,cast(pc.PersonCard_begDate as datetime),104) as PersonCard_begDate,
				pc.PersonCard_begDate as sort,
				convert(varchar,cast(pc.PersonCard_endDate as datetime),104) as PersonCard_endDate,
				rtrim(pc.LpuRegionType_Name) as LpuRegionType_Name,
				ccc.CardCloseCause_id,
				ccc.CardCloseCause_SysNick,
				rtrim(ccc.CardCloseCause_Name) as CardCloseCause_Name,
				rtrim(pc.LpuRegion_Name) as LpuRegion_Name,
				rtrim(pc.LpuRegion_FapName) as LpuRegion_FapName,
				lpu.Lpu_id,
				rtrim(lpu.Lpu_Nick) as Lpu_Nick,
				PACLT.AmbulatCardLocatType_Name,
				PACLT.PersonAmbulatCard_id,
				case when isnull(pc.PersonCard_IsAttachCondit, 1) = 1 then 'false' else 'true' end as PersonCard_IsAttachCondit,
				CASE WHEN PCard.PersonCardAttach_id IS NULL then 'false' else 'true' end as PersonCardAttach,
				CASE WHEN lpu.Lpu_id = :Lpu_id THEN 'true' else 'false' END as Is_OurLpu,
				CASE WHEN ps.Server_pid = 0	THEN 'true' ELSE 'false' END as [Person_IsBDZ],
				CASE WHEN fedl.Person_id is not null then 'true' else 'false' end as [Person_IsFedLgot],
				CASE WHEN exists (
					select
						PersonCard_id
					from
						v_PersonCard with (nolock)
					where
						Person_id = ps.Person_id
						and LpuAttachType_id = 5
						and PersonCard_endDate >= @gdate
						and CardCloseCause_id is null
				) THEN 'true' ELSE 'false' END as PersonCard_IsDmsForCheck,
				CASE WHEN exists (
					select
						PersonCard_id
					from
						v_PersonCard with (nolock)
					where
						Person_id = ps.Person_id
						and LpuAttachType_id = 5
						and pc.LpuAttachType_id = 1
						and cast(convert(varchar(10), pc.PersonCard_begDate, 112) as datetime) = cast(convert(varchar(10), PersonCard_begDate, 112) as datetime)
				) or pc.LpuAttachType_id = 5 THEN 'true' ELSE 'false' END as PersonCard_IsDms,
				CASE WHEN (
					select
						PersonCardAttach_id
					from
						PersonCard with(nolock)
					where
						PersonCard_id = pc.PersonCard_id
						and Person_id = pc.Person_id
						and Lpu_id = pc.Lpu_id
						and LpuAttachType_id = pc.LpuAttachType_id
						and ( PersonCard_IsAttachCondit = 1 or PersonCard_IsAttachCondit is null )
				) is not null THEN 'true' ELSE 'false' END as isPersonCardAttach,
				case when pl.Polis_begDate <= @gdate and (IsNull(pl.Polis_endDate,@gdate+1) > @gdate) then 'true' else 'false' end as Person_HasPolis,
				-- наличие полиса ДМС в чуждом ЛПУ
				CASE WHEN exists (
					select
						PersonCard_id
					from
						v_PersonCard with (nolock)
					where
						Person_id = ps.Person_id
						and LpuAttachType_id = 5
						and PersonCard_endDate >= @gdate
						and CardCloseCause_id is null
						and Lpu_id != :Lpu_id
				) THEN 'true' ELSE 'false' END as Person_HasDmsOtherLpu
				,CONVERT(varchar(10),PCA.PersonCardAttach_insDT, 104) as PersonCardAttach_insDT
			FROM v_PersonCard_all pc with (nolock)
			INNER JOIN v_PersonState ps with (nolock) on ps.Person_id = pc.Person_id
			left join PersonCard PCard with(nolock) on PCard.PersonCard_id = pc.PersonCard_id
			LEFT JOIN v_Lpu lpu with (nolock) on pc.Lpu_id=lpu.Lpu_id
			LEFT JOIN v_Polis pl with(nolock) on pl.Polis_id = ps.Polis_id
			LEFT JOIN v_CardCloseCause ccc with (nolock) on ccc.CardCloseCause_id=pc.CardCloseCause_id
			Left JOIN v_PersonCardAttach PCA with(nolock) on PCA.PersonCardAttach_id=pc.PersonCardAttach_id
			outer apply(
				select top 1 ACLT.AmbulatCardLocatType_Name,PACL.PersonAmbulatCard_id from v_PersonAmbulatCardLocat PACL with(nolock)
				left join AmbulatCardLocatType ACLT with(nolock) on ACLT.AmbulatCardLocatType_id=PACL.AmbulatCardLocatType_id
				left join v_PersonAmbulatCardLink PACLink with(nolock) on PACLink.PersonAmbulatCard_id = PACL.PersonAmbulatCard_id
				where pc.PersonCard_id=PACLink.PersonCard_id
				order by PACL.PersonAmbulatCardLocat_begDate desc
			)PACLT
			outer apply (
					select top 1 Person_id
					from v_personprivilege pp with (nolock)
					inner join v_PrivilegeType t2 with (nolock) on t2.PrivilegeType_id = pp.PrivilegeType_id
					where
					pp.person_id = ps.person_id
					and t2.ReceptFinance_id = 1
					and pp.personprivilege_begdate <= @gdate
					and (IsNull(pp.personprivilege_enddate, @gdate) >= cast(convert(char(10), @gdate, 112) as datetime))
				) as fedl
			WHERE
				pc.Person_id = :Person_id {$attach_type_filter}
			ORDER BY
				sort
		";
		//echo getDebugSQL($sql,$queryParams); die;
		$res=$this->db->query($sql, $queryParams);
		if ( is_object($res) )
			return $res->result('array');
		else
			return false;
	}

	/**
	 *	Получение какой-то информации
	 */
	function checkIfPersonCardIsExists($data)
	{
		$sql = "
			SELECT
				count(*) as cnt
			FROM
				v_PersonCard with (nolock)
			WHERE
				Person_id = ?
				and Lpu_id = ?
				and cast(convert(varchar(10), PersonCard_begDate, 112) as datetime) <= ?
				and LpuRegionType_id in (1,2,4)
		";
		$res=$this->db->query($sql, array($data['Person_id'], $data['Lpu_id'], str_replace("'", "", $data['PersonDisp_begDate'])));
		if ( is_object($res) )
			return $res->result('array');
		else
			return false;
	}

	/**
	 *	Возвращает тип ЛПУ по возрасту (справочник MesAgeLpuType)
	 */
	function getLpuAgeType($data) {
		$query = "
			select top 1
				isnull(MesAgeLpuType_id, 3) as MesAgeLpuType_id
			from
				v_Lpu with(nolock)
			where
				Lpu_id = :Lpu_id
		";

		$res=$this->db->query($query, array(
			'Lpu_id' => $data['Lpu_id']
		));
		if ( is_object($res) ) {
			$res = $res->result('array');
			if( count($res) > 0 ) {
				return $res[0]['MesAgeLpuType_id'];
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	/**
	 *	Возвращает идентификатор типа участка
	 */
	function getLpuRegionType($data) {
		if(empty($data['LpuRegion_id'])) return false;
		$query = "
			select top 1
				LpuRegionType_id
			from
				v_LpuRegion with(nolock)
			where
				LpuRegion_id = :LpuRegion_id
		";

		$res=$this->db->query($query, array(
			'LpuRegion_id' => $data['LpuRegion_id']
		));
		if ( is_object($res) ) {
			$res = $res->result('array');
			if( count($res) > 0 ) {
				return $res[0]['LpuRegionType_id'];
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	/**
	 * Проверка наличия активного прикрепления в другом ЛПУ (для задачи https://redmine.swan.perm.ru/issues/62755)
	 */
	function checkAttachExists($data)
	{
		$query = "
			select top 1 PersonCard_id
			from PersonCardState with (nolock)
			where Person_id = :Person_id
			and Lpu_id <> :Lpu_id
			and LpuAttachType_id = :LpuAttachType_id
			and CardCloseCause_id is null
		";
		$params = array(
			'Person_id' 	=> $data['Person_id'],
			'Lpu_id'		=> $data['Lpu_id'],
			'LpuAttachType_id' => $data['LpuAttachType_id']
		);
		$result = $this->db->query($query,$params);
		if(is_object($result))
		{
			$res = $result->result('array');
			if(count($res)>0)
			{
				return true;
			}
			else
			{
				return false;
			}
		}
		else
		{
			return false;
		}
	}

    /**
     * Проверка на фондодержание
     */
    function checkLpuFondHolder ($data)
    {
        $query = "
            select top 1
              LFH.LpuPeriodFondHolder_id
            from v_LpuPeriodFondHolder LFH with (nolock)
            where (1=1)
            and LFH.Lpu_id = :Lpu_id--10010833
            and LpuRegionType_id = :LpuRegionType_id
            and (LFH.LpuPeriodFondHolder_endDate is null or LFH.LpuPeriodFondHolder_endDate > :PersonCard_date)
        ";

        $params = array(
            'Lpu_id'		    => $data['Lpu_id'],
            'PersonCard_date'   => $data['PersonCard_begDate'],
            'LpuRegionType_id'  => $data['LpuRegionType_id']
        );
        //echo getDebugSQL($query,$params);die;
        $result = $this->db->query($query,$params);
        if(is_object($result))
        {
            $res = $result->result('array');
            if(count($res)>0)
            {
                return true;
            }
            else
            {
                return false;
            }
        }
        else
        {
            return false;
        }
    }

	/**
	 * @param $data
	 * @return bool
	 * Проверка возможности добавить льготу пациенту https://redmine.swan.perm.ru/issues/60393
	 */
	function allowAddPrivilegeChild($data)
	{

		$params = array(
			'Person_id' => $data['Person_id']
		);
		// Проверим, БДЗ-шный ли пациент (если Server_pid == 0, то БДЗ-шный), т.к. для Перми, в противном случае, льготу добавлять нельзя - https://redmine.swan.perm.ru/issues/82521
		if($this->getRegionNick() == 'perm')
		{
			$query = "
				select top 1 Person_id
				from v_PersonState p with (nolock)
				where Person_id = :Person_id
				and Server_pid > 0
			";

			$result = $this->db->query($query, $params);
			if ( !is_object($result) ) {
				return false;
			}

			$res = $result->result('array');

			if ( is_array($res) && count($res) > 0 && !empty($res[0]['Person_id']) ) {
				return false;
			}
		}
		// Проверяем, есть ли у пациента прикрепление (т.к. льготу добавляем только при добавлении ПЕРВОГО прикрепления)
		$query = "
			select top 1 PersonCard_id
			from v_PersonCard with (nolock)
			where Person_id = :Person_id
		";

		$result = $this->db->query($query, $params);

		if ( !is_object($result) ) {
			return false;
		}

		$res = $result->result('array');

		if ( is_array($res) && count($res) > 0 && !empty($res[0]['PersonCard_id']) ) {
			return false;
		}

		// Проверяем, есть ли уже у пациента льгота "Дети первых 3 лет"
		$query = "
			select top 1 pp.PersonPrivilege_id
			from v_PersonPrivilege pp with (nolock)
				inner join v_PrivilegeType pt with (nolock) on pt.PrivilegeType_id = pp.PrivilegeType_id
			where pp.Person_id = :Person_id
				and pt.PrivilegeType_SysNick = 'child_und_three_year'
		";

		$result = $this->db->query($query, $params);

		if ( !is_object($result) ) {
			return false;
		}

		$res = $result->result('array');

		return !(is_array($res) && count($res) > 0 && !empty($res[0]['PersonPrivilege_id']));
	}

	/**
	 *	Проверка возможности прикрепления
	 */
	function checkAttachPosible($data) {
		if(empty($data['PersonAge']) && $data['PersonAge'] != 0){
			//DieWithError("Поле Возраст человека обязательно для заполнения");
			//return false;
		}
		else{
			$isPersonBaby = $data['PersonAge'] < 18;
			// Проверка на тип ЛПУ по возрасту (детское, взрослое или смешанное)
			$lpuAgeType = $this->getLpuAgeType($data);
			if( $lpuAgeType === false ) {
				DieWithError("Не удалось определить тип ЛПУ по возрасту!");
				return false;
			}

			if( in_array($lpuAgeType, array(1, 2)) ) {
				if( $lpuAgeType == 1 && $isPersonBaby) {
					return array(
						array('Error_Code' => 1, 'Error_Msg' => 'Нельзя прикрепить пациентов детского возраста к взрослому ЛПУ!' )
					);
				}
				if( $lpuAgeType == 2 && !$isPersonBaby) {
					return array(
						array('Error_Code' => 1, 'Error_Msg' => 'Нельзя прикрепить пациентов взрослого возраста к детскому ЛПУ!' )
					);
				}
			}

			// Проверка на тип участка (пациентов младше 18 лет нельзя прикреплять к терап. участкам, а 18 лет и старше нельзя к педиатр участкам)
			if( $data['LpuRegionType_id'] == 1 && $isPersonBaby ) {
				return array(
					array('Error_Code' => 1, 'Error_Msg' => 'Нельзя прикрепить пациентов детского возраста к терапевтическому участку!' )
				);
			}
			if( $data['LpuRegionType_id'] == 2 && !$isPersonBaby ) {
				return array(
					array('Error_Code' => 1, 'Error_Msg' => 'Нельзя прикрепить пациентов взрослого возраста к педиатрическому участку!' )
				);
			}
		}

		/*$sql = "
			select top 1
				datediff(YEAR,PersonCard_begDate,dbo.tzGetDate()) as YearCount,
				pc.Lpu_id
			from v_PersonCard_all pc with (nolock)
			   cross apply (
					select
						Lpu_id
					from
						v_PersonCard with (nolock)
					where
						Person_id = :Person_id
						and LpuAttachType_id = :LpuAttachType_id
						and isnull(PersonCard_IsAttachCondit, 1) = 1
			   ) as lpupc
			where
				Person_id = :Person_id
				and lpupc.Lpu_id = pc.Lpu_id
				and LpuAttachType_id = :LpuAttachType_id
				and (IsNull(CardCloseCause_id, 4) = 4)
				and isnull(PersonCard_IsAttachCondit, 1) = 1 -- за исключением условно прикр. (#8185)
			order by
				PersonCard_begDate desc
		";*/

        //Переписал в рамках задачи https://redmine.swan.perm.ru/issues/71289
        //Нужно пробежаться по всем прикреплениям, начиная с конца. Если последнее (по дате) прикрепление имеет ту же ЛПУ, что и наше, то прикреплять можно
        //В противном случае, если последнее прикрепление - НЕ в нашей МО, то пошагово ищем изначальное прикрепление к такой МО и смотрим на его дату.
        //Если оно создано больше года назад, то сохранить можно, иначе - шагаем дальше до проверок на смену адреса
        //Пример: есть прикрепление к МО "ДКБ" в 2014 году, и есть смена участка в этом МО в 2015 году. Код сработает так, что за основу возьмется не последняя запись (2015г), а первая (2014)
        //При этом нужно шагать по массиву только до тех пор, пока не встретим (если встретим) другое МО. Тогда нужно остановиться и взять дату из предыдущей итерации.
		//Условные прикрепления при этом игнорируем!
		//if($this->getRegionNick() != 'astra')
		//{
		$sql = "
				select datediff(YEAR,pc.PersonCard_begDate,dbo.tzGetDate()) as YearCount,
					pc.Lpu_id
				from v_PersonCard_all pc with(nolock)
				where pc.Person_id = :Person_id
				and LpuAttachType_id = :LpuAttachType_id
				and isnull(PersonCard_IsAttachCondit, 1) = 1
				order by pc.PersonCard_begDate desc
		";

		//die(getDebugSQL($sql, array('Person_id' => $data['Person_id'],'LpuAttachType_id' => $data['LpuAttachType_id'])));
		$result = $this->db->query($sql, array(
				'Person_id' => $data['Person_id'],
				'LpuAttachType_id' => $data['LpuAttachType_id']
			)
		);
		if (is_object($result)) {
			$sel = $result->result('array');
			$lpu_prev = 0;
			if ( count($sel) > 0 ) {
				$lpu_prev = $sel[0]['Lpu_id']; //Взяли ЛПУ из последнего по дате прикрепления
				$years_count = $sel[0]['YearCount'];
				//$years_count = 0;
				$continue = 1;
				if($sel[0]['Lpu_id'] == $data['Lpu_id'])
					$year_check = true;
				else
				{
					if(count($sel)==1)
					{
						$years_count = $sel[0]['YearCount'];
					}
					else
					{
						for ($i=1; $i<count($sel) && $continue==1; $i++){
							if($sel[$i]['Lpu_id'] <> $lpu_prev) //Как только встретили другое МО, останавливаемся и берем YearCount из предыдущей итерации, которая станет последней для МО.
							{
								$continue = 0;
								$years_count = $sel[$i-1]['YearCount'];
							}
							else
							{
								$continue = 1;
								$years_count = $sel[$i]['YearCount'];
							}
						}
					}
					if($years_count > 0)
						$year_check = true;
					else
						$year_check = false;
				}
			}
			else
				$year_check = true;
		} else {
			return array( 0 => array('Error_Code' => 666, 'Error_Msg'=>'Не удалось проверить дату последнего прикрепления.') );
		}

		if ( $year_check === true )
			return true;
		//}
		// Проверка на "Переход из детской сети во взрослую" (#8185)
		$query = "
			select top 1
				LpuRegionType_id
			from
				v_PersonCard with (nolock)
			where
				Person_id = :Person_id
			order by
				PersonCard_LpuBegDate desc
		";

		$res = $this->db->query($query, $data);
		if (is_object($res)) {
			$res = $res->result('array');
			if( count($res) > 0 ) {
				if( $res[0]['LpuRegionType_id'] == 2 && $data['LpuRegionType_id'] == 1 )
					return true;
			}
		} else {
			return array( 0 => array('Error_Code' => 1, 'Error_Msg'=>'Не удалось проверить переход из детской сети во взрослую.') );
		}


		// получаем адрес, который был на момент прикрепления
		$sql = "
		select top 1
			a.Address_id,
			a.KLCountry_id,
			a.KLRgn_id,
			a.KLSubRgn_id,
			a.KLCity_id,
			a.KLTown_id,
			a.KLStreet_id,
			a.Address_House,
			a.Address_Corpus,
			a.Address_Flat
		from v_PersonPAddress a with (nolock)
			inner join v_PersonCard pc with (nolock) on pc.Person_id = :Person_id
				and LpuAttachType_id = :LpuAttachType_id
				and PersonCard_IsAttachCondit != 2 --исключаем условные
				and cast(PersonCard_begDate as datetime) >= cast(PersonPAddress_insDate as datetime)
		where
			a.Person_id = :Person_id
		order by
			PersonPAddress_insDate desc";

		$result = $this->db->query($sql,
		array(
			'Person_id' => $data['Person_id'],
			'LpuAttachType_id' => $data['LpuAttachType_id']
		));
		if (is_object($result)) {
			$addr_old = $result->result('array');
		} else {
			return array( 0 => array('Error_Code' => 1, 'Error_Msg'=>'Не удалось проверить изменение адреса проживания.') );
		}

		$sql = "
		select top 1
			a.Address_id,
			a.KLCountry_id,
			a.KLRgn_id,
			a.KLSubRgn_id,
			a.KLCity_id,
			a.KLTown_id,
			a.KLStreet_id,
			a.Address_House,
			a.Address_Corpus,
			a.Address_Flat
		from
			v_PersonState p with (nolock)
			left join Address a with(nolock) on a.Address_id = p.PAddress_id
		where
			Person_id = :Person_id";

		$result = $this->db->query($sql,
		array(
			'Person_id' => $data['Person_id'],
			'LpuAttachType_id' => $data['LpuAttachType_id']
		));
		if (is_object($result)) {
			$addr_new = $result->result('array');
		} else {
			return array( 0 => array('Error_Code' => 1, 'Error_Msg'=>'Не удалось проверить изменение адреса проживания.') );
		}

		if ( count($addr_new) == 0 || empty($addr_new[0]['Address_id']) )
			return array( 0 => array('Error_Code' => 1, 'Error_Msg'=>'У пациента не указан адрес!') );

		if ( count($addr_old) == 1 && count($addr_new) == 1
			&& $addr_old[0]['KLCountry_id'] == $addr_new[0]['KLCountry_id']
			&& $addr_old[0]['KLRgn_id'] == $addr_new[0]['KLRgn_id']
			&& $addr_old[0]['KLSubRgn_id'] == $addr_new[0]['KLSubRgn_id']
			&& $addr_old[0]['KLCity_id'] == $addr_new[0]['KLCity_id']
			&& $addr_old[0]['KLTown_id'] == $addr_new[0]['KLTown_id']
			&& $addr_old[0]['KLStreet_id'] == $addr_new[0]['KLStreet_id']
			&& $addr_old[0]['Address_House'] == $addr_new[0]['Address_House']
			&& $addr_old[0]['Address_Corpus'] == $addr_new[0]['Address_Corpus']
			&& $addr_old[0]['Address_Flat'] == $addr_new[0]['Address_Flat']
		) {
			return array(
				array('Error_Code' => 1, 'Error_Msg' => 'Нельзя прикреплять пациента чаще 1 раза в год.')
			);
		}

		return true;
	}

	/**
	 *	Сохранение амбулаторной карты пациента
	 */
	function savePersonCard($data, $api=false)
	{
		$isAutoImport = $data['isAutoImport'] ?? false; // импорт РПН из файла
		//$api = флаг, что метод вызван для API
		//----------https://redmine.swan.perm.ru/issues/87348 Очередная костылина. Здесь проверям соответствие участка той МО, к которой прикрепляем.
		$lpuRegion_check = 0;
		$quury_check_lpuRegion = "
			select LpuRegion_id
			from LpuRegion with(nolock)
			where Lpu_id = :Lpu_id
			and LpuRegion_id = :LpuRegion_id
		";
		if(isset( $data['LpuRegion_id']) &&  $data['LpuRegion_id'] > 0){
			$result_check_lpuRegion = $this->db->query(
				$quury_check_lpuRegion,
				array(
					'Lpu_id' => $data['Lpu_id'],
					'LpuRegion_id' => $data['LpuRegion_id']
				)
			);
			if(is_object($result_check_lpuRegion)){
				$result_check_lpuRegion = $result_check_lpuRegion->result('array');
				if(count($result_check_lpuRegion) > 0)
					$lpuRegion_check = 1;
			}
			if($lpuRegion_check==0){
				$this->db->trans_rollback();
				return array( 0 => array('Error_Code' => 333, 'Error_Msg'=>'Прикрепление невозможно. Данный участок не относится к выбранной Вами МО.') );
			}
		}
		//---------конец костылины

		//После долгих мучений решил втыкнуть отдельный кусок для задачи https://redmine.swan.perm.ru/issues/26087, т.к. в рамках нее никакие проверки не нужны
		if(!$api && $data['PersonCard_id'] > 0){//Если открываем карту на изменение
			//Проверяем, что изменилось - если всего лишь поставили галочку на "Заявлении", то апдейтим это прикрепление с учетом PersonCardAttach_id
			//И да простят меня потомки за этот убогий костыль. Но иначе я уже не соображаю, как это сделать.
			if($data['PersonCardAttach_id'] > 0) {
				//Проверяем, не изменилось ли чего-нибудь еще
				$params = array(
					'PersonCard_id' => $data['PersonCard_id'],
					'PersonCard_Code' => $data['PersonCard_Code'],
					//'Lpu_id' => $data['Lpu_id'],
					'LpuRegion_id' => $data['LpuRegion_id'],
                    'LpuRegion_Fapid' => $data['LpuRegion_Fapid'],
					'LpuAttachType_id' => $data['LpuAttachType_id'],
					'PersonCard_begDate' => $data['PersonCard_begDate'],
					'PersonCard_endDate' => $data['PersonCard_endDate']
				);
				$sql = "
					SELECT *
					FROM PersonCard with(nolock)
					WHERE PersonCard_id = :PersonCard_id
					AND PersonCard_Code = :PersonCard_Code
					AND LpuRegion_id = :LpuRegion_id
					AND LpuRegion_fapid = :LpuRegion_Fapid
					AND LpuAttachType_id = :LpuAttachType_id
					AND PersonCardAttach_id IS NULL
					AND ISNULL(convert(varchar(10),cast(PersonCard_begDate as datetime),20),'') = ISNULL(:PersonCard_begDate,'')
					AND ISNULL(convert(varchar(10),cast(PersonCard_endDate as datetime),20),'') = ISNULL(:PersonCard_endDate,'')
				";
				//echo getDebugSQL($sql,$params);
				//die;
				$result = $this->db->query($sql, $params);
				if (is_object($result)){
					$resp = $result->result('array');
					if (count($resp)>0){
						$queryParams = array();
						$queryParams['PersonCard_id'] = $resp[0]['PersonCard_id'];
						$queryParams['PersonCardAttach_id'] = $data['PersonCardAttach_id'];
						$query = "
						UPDATE PersonCard SET PersonCardAttach_id = :PersonCardAttach_id WHERE PersonCard_id = :PersonCard_id
						";
						//echo getDebugSQL($query, $queryParams);
						//die;
						$result = $this->db->query($query, $queryParams);
						if($result){
							$this->db->trans_commit();
							return array(array('PersonCard_id' => $queryParams['PersonCard_id'], 'Error_Msg' => ''));
						}
					}
				}
			}
		}

		$current_date = date('Y-m-d'); // текущая дата

		$procedure = '';

        /*if ( $data['action'] == 'add' && empty($data['PersonCard_id']) )
        {
        	$data['PersonCard_id'] = NULL;
            $procedure = 'p_PersonCard_ins';
        }
        else if ( $data['action'] == 'edit' && $data['PersonCard_id'] > 0 )
        {
            $procedure = 'p_PersonCard_upd';
        }
        else
        {
            return array(array('Error_Msg' => 'Неверное значение PersonCard_id'));
        }*/

		if (in_array($data['LpuAttachType_id'], array(1,2,3)) && empty($data['LpuRegionType_id']) && $this->regionNick != 'vologda') {
			return array(array('Error_Code' => 1, 'Error_Msg' => 'Не определен тип участка прикрепления'));
		}
		if (in_array($data['LpuAttachType_id'], array(1)) && empty($data['LpuRegion_id']) && $this->regionNick != 'vologda') {
			return array(array('Error_Code' => 1, 'Error_Msg' => 'Не определен участок прикрепления'));
		}

		// стартуем транзакцию
		$this->db->trans_begin();
		$pc_id = null;
		$LpuRegion_fapid = null;
		// проверка проверка уже существующей карты по этому типу прикрепления или в своем ЛПУ (тогда не даем вообще сохранить) или в чужом ЛПУ,
		// тогда вообще закрываем карту в другом ЛПУ,
		// или человек прикреплен уже сегодня, тогда не даем сохранять карту вообще
		$PersonCard_idFilter = '';
		$queryParams = array();
		// текущую карту не проверяем
		if ( isset($data['PersonCard_id']) ) {
			$PersonCard_idFilter = ' and PC_all.PersonCard_id <> :PersonCard_id';
			$queryParams['PersonCard_id'] = $data['PersonCard_id'];
		}
		$queryParams['Person_id'] = $data['Person_id'];
		$queryParams['LpuAttachType_id'] = $data['LpuAttachType_id'];
		$sql = "
			SELECT
				PC_all.PersonCard_id,
				PC_all.Person_id,
				PC_all.Server_id,
				PC_all.Lpu_id,
				PC_all.LpuRegion_id,
				PC_all.LpuRegion_fapid,
				PC_all.LpuAttachType_id,
				PC_all.PersonCard_Code,
				PC_all.PersonCard_IsAttachCondit,
				convert(varchar,cast(PC_all.PersonCard_begDate as datetime),112) as PersonCard_begDate,
				PC_all.CardCloseCause_id,
				PC_all.PersonCardAttach_id
			FROM v_PersonCard_all PC_all with (nolock)
			WHERE
				PC_all.Person_id = :Person_id
				and PC_all.LpuAttachType_id = :LpuAttachType_id
				and (PC_all.PersonCard_endDate is null or cast(PC_all.PersonCard_endDate as date) > cast(dbo.tzGetDate() as date))
				{$PersonCard_idFilter}";

		//echo getDebugSQL($sql, $queryParams);die;
		$result = $this->db->query($sql, $queryParams);
		$checkPolisChanged = false; //https://redmine.swan.perm.ru/issues/98974
		$CardCloseCause_new = null;
		if (is_object($result))
        {
			$sel = $result->result('array');
			if (count($sel) > 0 )
			{
				if ( ($sel[0]["PersonCard_begDate"] == date('Ymd')) && !in_array($queryParams['LpuAttachType_id'], array(4)) )
				{
					$this->db->trans_rollback();
					return array( 0 => array('Error_Code' => 666, 'Error_Msg'=>'Новое прикрепление пациента можно добавлять не чаще одного раза в день. Если пациент прикреплен к Вашему ЛПУ, то прикрепление может быть удалено или изменен участок только в течение даты прикрепления.') );
				}

				// карта открыта в своем ЛПУ
				if ( ($sel[0]["Lpu_id"] == $data["Lpu_id"]) && ( empty($data['PersonCard_id']) ) /*&& !in_array($queryParams['LpuAttachType_id'], array(4))*/ )
				{
					// если пытаются сохранить, а человек уже прикреплен к данному участку
					if ( $sel[0]['LpuRegion_id'] == $data['LpuRegion_id'] )
					{
                        if($this->getRegionNick() != 'perm' && !in_array($queryParams['LpuAttachType_id'], array(4)))
                        {
                            if($sel[0]['PersonCard_IsAttachCondit'] != 2) { //https://redmine.swan.perm.ru/issues/24847
                                $this->db->trans_rollback();
                                return array( 0 => array('Error_Code' => 333, 'Error_Msg'=>'Пациент уже прикреплен к данному участку.') );
                            }
                        }
                        else
                        {

                            if( !empty($data['LpuRegion_Fapid']) && !empty($sel[0]['LpuRegion_fapid']) && $data['LpuRegion_Fapid'] == $sel[0]['LpuRegion_fapid'])
                            {
                            	if($this->getRegionNick() != 'perm' && $this->getRegionNick() != 'penza')
                            	{
	                                if($sel[0]['PersonCard_IsAttachCondit'] != 2  && !in_array($queryParams['LpuAttachType_id'], array(4))) { //https://redmine.swan.perm.ru/issues/24847
	                                    $this->db->trans_rollback();
	                                    return array( 0 => array('Error_Code' => 333, 'Error_Msg'=>'Пациент уже прикреплен к данному участку.') );
	                                }
                                }
                                else
                                	$checkPolisChanged = true; //https://redmine.swan.perm.ru/issues/98974
                            }
                        }

					}
				}
				$pc_id = $sel[0]['PersonCard_id'];
				if($checkPolisChanged) //https://redmine.swan.perm.ru/issues/98974
		        {
		        	$CardCloseCause_new = 8;//9; //По дефолту считаем "Смена основного врача на участке" - точнее, считали до задачи https://redmine.swan.perm.ru/issues/100477. щас считаем "Иное/По требованию МО"
		        	$query_change = "
		        		select PP.PersonPolis_id
		        		from v_PersonPolis PP
		        		where
		        			PP.Person_id = :Person_id
		        		and
		        			PP.PersonPolis_begDT > :begDate
		        	";
		        	$result_change = $this->db->query($query_change, array('Person_id' => $data['Person_id'], 'begDate' => $sel[0]["PersonCard_begDate"]));
		        	if(is_object($result_change)){
		        		$result_change = $result_change->result('array');
		        		if(count($result_change) > 0)
		        		{
		        			$CardCloseCause_new = 10; //Смена действующего полиса
		        		}
		        	}
		        }
			}
        }
        else
        {
        	$this->db->trans_rollback();
			return array( 0 => array('Error_Code' => 666, 'Error_Msg'=>'Не удалось проверить наличие уже существующего прикрепления, попробуйте сохранить еще раз.') );
        }

		//Проверка при редактировании карты предыдущей карты.
		//Удостоверяемся что у редактируемой карты не ставим тот же участок что у предыдущей
		if ( isset($data['PersonCard_id']) && !in_array($queryParams['LpuAttachType_id'], array(4)) )
		{
			$sql = "
				SELECT TOP 1
					LpuRegion_id
			FROM v_PersonCard_all with (nolock)
			WHERE
				Person_id = :Person_id
				and LpuAttachType_id = :LpuAttachType_id
				and PersonCard_id <> :PersonCard_id
				and (PersonCard_endDate is null or cast(PersonCard_endDate as date) > cast(dbo.tzGetDate() as date))
			ORDER BY PersonCard_begDate desc";

			$result = $this->db->query(
				$sql,
				array(
					'Person_id' => $data['Person_id'],
					'LpuAttachType_id' => $data['LpuAttachType_id'],
					'PersonCard_id' => $data['PersonCard_id']
				));
			if (is_object($result))
			{
				$sel = $result->result('array');
				if ( count($sel) > 0 )
				{
					if ( $sel[0]['LpuRegion_id'] == $data['LpuRegion_id'] )
					{
						$this->db->trans_rollback();
						return array( 0 => array("Error_Code" => 6, "Error_Msg"=>"Пациент прикреплен к данному участку в предыдущей карте.") );
					}
				}
			}
		}

        $allowCheckPersonCardUniqueness = empty($data['OverrideCardUniqueness']);
		$allowCheckLpuRegion = (isset($data['PersonCard_id']) && !in_array($data['LpuAttachType_id'], array(4)));
		// проверка даты прикрепления редактируемой карты
		// если дата прикрепления сегодняшняя, то просто сохраняем
		// если дата прикрепления старая, то просто не даем редактировать карту и выводим соответствующее сообщение
		if ( isset($data['PersonCard_id']) )
		{
			$sql = "
				SELECT
					pc.PersonCard_id,
					pc.Person_id,
					pc.Server_id,
					pc.Lpu_id,
					pc.LpuRegionType_id,
					pc.LpuRegion_id,
					pc.LpuRegion_fapid,
					pc.LpuAttachType_id,
					pc.PersonCard_Code,
					convert(varchar,cast(pc.PersonCard_begDate as datetime),104) as PersonCard_begDate,
					convert(varchar,cast(pc.PersonCardBeg_insDT as datetime),104) as PersonCardBeg_insDT,
					convert(varchar,cast(pc.PersonCard_endDate as datetime),104) as PersonCard_endDate,
					p.Person_IsBDZ,
					pc.PersonCard_IsAttachCondit,
					pc.CardCloseCause_id,
					pc.PersonCardAttach_id,
					ISNULL(pc.PersonCard_IsAttachAuto,'') as PersonCard_IsAttachAuto
				FROM v_PersonCard_all pc with (nolock)
					inner join v_PersonState_all p with (nolock) on p.person_id=pc.person_id
				WHERE
					pc.PersonCard_id = ?;";
			$result = $this->db->query($sql, array($data['PersonCard_id']));
			if (is_object($result))
			{
				$sel = $result->result('array');
				if ( count($sel) > 0 )
				{
					/*
					номер карты можно редактировать в любом случае, остальная логика должна остаться без изменений
					*/
					$is_change_only_personcard_code = false;
					$is_change_other = false;
					$is_auto_attach = false;
					// дату открытия оставляем той же
					if($this->getRegionNick() != 'ekb')
						$data['PersonCard_begDate'] = ConvertDateFormat($sel[0]['PersonCard_begDate']);
					// проверяем изменился ли номер карты
					if ( $sel[0]['PersonCard_Code'] != $data['PersonCard_Code'] )
					{
						$is_change_only_personcard_code = true;
					}
					else
					{
						//если номер карты не изменился, то соотв.проверка не нужна
						$allowCheckPersonCardUniqueness = false;
					}
					//если участок не изменился, то соотв.проверка не нужна
					if( $sel[0]['LpuRegion_id'] == $data['LpuRegion_id'] && $sel[0]['LpuRegionType_id'] == $data['LpuRegionType_id'])
					{
						$allowCheckLpuRegion = false;
					}
					else
					{
						$is_change_other = true;
						$is_change_only_personcard_code = false;
					}
					if( (empty($sel[0]['Person_IsBDZ']) || isSuperadmin() || ($this->getRegionNick() == 'ufa' && isLpuAdmin($data['Lpu_id'])) || ($this->getRegionNick() != 'perm' && havingGroup('CardCloseUser'))) && !empty($data['PersonCard_endDate']))
					{
						$is_change_other = true;
						$is_change_only_personcard_code = false;
						//нужно снять с учета
						/*if(empty($sel[0]['PersonCard_endDate']))
						{
							$data['PersonCard_endDate'] = $current_date;
						}*/
						$data['CardCloseCause_id'] = (empty($data['CardCloseCause_id'])?null:$data['CardCloseCause_id']);
					}
                    if (($sel[0]['PersonCard_Code'] == $data['PersonCard_Code']) && ($sel[0]['LpuRegionType_id'] == $data['LpuRegionType_id']) && ($sel[0]['LpuRegion_id'] == $data['LpuRegion_id']) && ($sel[0]['LpuAttachType_id'] == $data['LpuAttachType_id']))
                        $is_change_other = false;
					//Добавил этот кусок с проверкой PersonCardAttach_id в рамках задачи https://redmine.swan.perm.ru/issues/26087
					if($sel[0]['PersonCardAttach_id'] == $data['PersonCardAttach_id']){
						$is_change_attach_id = false;
					}
					else{
						$is_change_attach_id = true;
					}
                    //Проверим, не поставили ли дату закрытия https://redmine.swan.perm.ru/issues/46054
                    $is_change_endDate = false;
                    if($sel[0]['PersonCard_endDate'] <> $data['PersonCard_endDate']){
                        $is_change_endDate = true;
                    }
                    $is_change_begDate = false;
                    if(ConvertDateFormat($sel[0]['PersonCard_begDate']) <> $data['PersonCard_begDate'] && ($this->getRegionNick() == 'ekb'))
                    	$is_change_begDate = true;
                    $is_change_fap = false;
                    if($sel[0]['LpuRegion_fapid'] <> $data['LpuRegion_Fapid']){
                        $is_change_fap = true;
                    }
                    if($sel[0]['PersonCard_IsAttachAuto'] && $sel[0]['PersonCard_IsAttachAuto'] == 2 && $this->getRegionNick() == 'astra')
                    	$is_auto_attach = true;
					if(!$is_change_only_personcard_code && !$is_change_other && !$is_change_attach_id && !$is_change_endDate && !$is_change_fap && !$is_change_begDate && !$is_auto_attach)
					{
						//нет изменений, отваливаем
						$this->db->trans_rollback();
						return array( 0 => array('PersonCard_id' => $sel[0]['PersonCard_id'],'success'=>true, 'Error_Code' => null, 'Error_Msg'=>'') );
					}
					if(!$is_change_only_personcard_code && $is_change_other && !$is_auto_attach)
					{
						// так же проверяем дату
						if ( $sel[0]['PersonCardBeg_insDT'] != date('d.m.Y') && !in_array($queryParams['LpuAttachType_id'], array(4)) && empty($data['PersonCard_endDate']) )
						{
							$this->db->trans_rollback();
							return array(array('Error_Code' => 666,'Error_Msg'=>'Редактирование карты возможно только в течение даты открытия.'));
						}
					}
				}
			}
		}
		else
		{
			if (empty($data['ignorePersonDead']) || !$data['ignorePersonDead']) {
				$sql = "
					SELECT TOP 1
						P.Person_id
					FROM
						v_Person P with (nolock)
					WHERE
						P.Person_id = :Person_id
						and (P.Person_IsDead = 2 or p.Person_deadDT is not null)
				";

				$result = $this->db->query(
					$sql,
					array(
						'Person_id' => $data['Person_id']
					));
				if (is_object($result))
				{
					$sel = $result->result('array');
					if ( count($sel) > 0 )
					{
						$this->db->trans_rollback();
						return array( 0 => array('Error_Code' => 666, 'Error_Msg'=>'Пациент умер, прикрепление невозможно!') );
					}
				}
			}
		}

 		//Проверка предыдущей карты при редактировании карты.
		//Удостоверяемся что у редактируемой карты не ставим тот же участок что у предыдущей. Нужно ли and (PersonCard_endDate is null or cast(PersonCard_endDate as date) > cast(dbo.tzGetDate() as date)) ?

		if ( $data['action']!='edit' && $allowCheckLpuRegion )
		{
			$sql = "
				SELECT TOP 1
					LpuRegion_id
			FROM v_PersonCard_all with (nolock)
			WHERE
				Person_id = :Person_id
				and LpuAttachType_id = :LpuAttachType_id
				and PersonCard_id <> :PersonCard_id
			ORDER BY PersonCard_begDate desc";

			$result = $this->db->query(
				$sql,
				array(
					'Person_id' => $data['Person_id'],
					'LpuAttachType_id' => $data['LpuAttachType_id'],
					'PersonCard_id' => $data['PersonCard_id']
				));
			if (is_object($result))
			{
				$sel = $result->result('array');
				if ( count($sel) > 0 )
				{
					if ( $sel[0]['LpuRegion_id'] == $data['LpuRegion_id'] )
					{
						$this->db->trans_rollback();
						return array( 0 => array('Error_Code' => 6, 'Error_Msg'=>'Пациент уже прикреплен к данному участку в предыдущей карте.') );
					}
				}
			}
		}

		if(isset($data['PersonCard_id']) && $data['PersonCard_id'] > 0){
			$procedure = 'p_PersonCard_upd';

            //Да здравствует костыльное программирование! Очередная затычка для задачи https://redmine.swan.perm.ru/issues/74657,
			//так как хз когда согласятся сделать по-человечески
			if(!empty($data['PersonCard_endDate'])) //Если закрываем прикрепление
			{
				//Сначала апдейтим у старого прикрепления PersonCard_Code, причем в PersonCard и PersonCardState
				$query_upd_pc = "
					update PersonCard
					set PersonCard_Code = :PersonCard_Code
					where PersonCard_id = :PersonCard_id
				";
				$result_upd_pc = $this->db->query($query_upd_pc,array(
					'PersonCard_Code' => $data['PersonCard_Code'],
					'PersonCard_id' => $data['PersonCard_id']
				));
				$query_upd_pc = "
				update PersonCardState
					set PersonCardState_Code = :PersonCard_Code
					where PersonCard_id = :PersonCard_id
				";
				$result_upd_pc = $this->db->query($query_upd_pc,array(
					'PersonCard_Code' => $data['PersonCard_Code'],
					'PersonCard_id' => $data['PersonCard_id']
				));
				if(!empty($data['PersonCardAttach_id'])) //Если есть аттач, то апдейтим аналогично предыдущему куску
				{
					$query_upd_pc = "
					update PersonCard
					set PersonCardAttach_id = :PersonCardAttach_id
					where PersonCard_id = :PersonCard_id
					";
					$result_upd_pc = $this->db->query($query_upd_pc,array(
						'PersonCardAttach_id' => $data['PersonCardAttach_id'],
						'PersonCard_id' => $data['PersonCard_id']
					));
					$query_upd_pc = "
						update PersonCardState
						set PersonCardAttach_id = :PersonCardAttach_id
						where PersonCard_id = :PersonCard_id
					";
					$result_upd_pc = $this->db->query($query_upd_pc,array(
						'PersonCardAttach_id' => $data['PersonCardAttach_id'],
						'PersonCard_id' => $data['PersonCard_id']
					));
				}
			}
		}
		else{
			if(isset($pc_id) && $pc_id > 0)
			{
				$data['PersonCard_id'] = $pc_id;
				$procedure = 'p_PersonCard_upd';
			}
			else{
				$data['PersonCard_id'] = null;
				$procedure = 'p_PersonCard_ins';
			}
		}

		//Проверим дату прикрепления для задачи https://redmine.swan.perm.ru/issues/65246
		if($data['action'] == 'add' && getRegionNick() != 'kz' && !$isAutoImport){
			$query_check_date = "
				select top 1
					--PersonCard_begDate,
					--PersonCard_endDate
					convert(varchar(10),cast(PersonCard_begDate as datetime),126) as PersonCard_begDate,
					ISNULL(convert(varchar(10),cast(PersonCard_endDate as datetime),126),'') as PersonCard_endDate
				from v_PersonCard with(nolock)
				where LpuAttachType_id = :LpuAttachType_id
				and Person_id = :Person_id
				order by PersonCard_begDate desc
			";
			$result_check_date = $this->db->query($query_check_date,array('LpuAttachType_id' => $data['LpuAttachType_id'],'Person_id' => $data['Person_id']));
			if(is_object($result_check_date)){
				$result_check_date = $result_check_date->result('array');
                //$this->db->trans_rollback();var_dump($result_check_date);die;
                if(is_array($result_check_date) && count($result_check_date)>0)
                {
                    if(isset($result_check_date[0]['PersonCard_endDate']) && $result_check_date[0]['PersonCard_endDate'] <> '')
                    {
                        $date_checked = $result_check_date[0]['PersonCard_endDate'];
                    }
                    else {
                        $date_checked = $result_check_date[0]['PersonCard_begDate'];
                    }
                    if($data['PersonCard_begDate'] < $date_checked)
                    {
                        $this->db->trans_rollback();
                        return array( 0 => array('Error_Code' => 8, 'Error_Msg'=>'Дата начала должна быть позднее '.date('d.m.Y',strtotime($date_checked))) );
                    }
                }
			}
		}
        //if($LpuRegion_fapid <> $data['LpuRegion_Fapid'])
            //$data['PersonCard_begDate'] = $current_date;
		$queryParams = array();

		//Костылина для задачи https://redmine.swan.perm.ru/issues/83202 - тут надо предварительно пробежаться по всем данным из v_PersonCard по служебному типу и посмотреть,
		//есть ли активное прикрепление к передаваемой здесь МО. Если есть, то закрываем его (вызывая на апдейт), если нет - то вызываем инсерт
		if($data['LpuAttachType_id'] == 4 && $data['action'] == 'add'){
			$procedure = 'p_PersonCard_ins'; //Сначала считаем, что это новое служебное (предыдущих по введенному ЛПУ нет), и вызываем инсерт
			$data['PersonCard_id'] = NULL;
			$query_check_slug = "
				select top 1 PersonCard_id
				from v_PersonCard (nolock)
				where Person_id = :Person_id
				and Lpu_id = :Lpu_id
				and LpuAttachType_id = 4
			";
			$params_check_slug = array(
				'Person_id' => $data['Person_id'],
				'Lpu_id'	=> $data['Lpu_id']
			);
			$result_check_slug = $this->db->query($query_check_slug,$params_check_slug);
			if(is_object($result_check_slug)){
				$result_check_slug = $result_check_slug->result('array');
				if(count($result_check_slug) > 0) //Если нашлось служебное по этому же ЛПУ, то его надо закрыть, поэтому вызываем upd с новыми параметрами и старым PersonCard_id
				{
					$data['PersonCard_id'] = $result_check_slug[0]['PersonCard_id'];
					$procedure = 'p_PersonCard_upd';
				}
			}
		}

		$queryParams['PersonCard_id'] = $data['PersonCard_id'];
		$queryParams['Lpu_id'] = $data['Lpu_id'];
		$queryParams['Server_id'] = $data['Server_id'];
		$queryParams['Person_id'] = $data['Person_id'];
		$queryParams['PersonCard_begDate'] = $data['PersonCard_begDate'];
		$queryParams['PersonCard_endDate'] = !empty($data['PersonCard_endDate'])?$data['PersonCard_endDate']:null;
		$queryParams['PersonCard_Code'] = $data['PersonCard_Code'];
		$queryParams['PersonCard_IsAttachCondit'] = !empty($data['setIsAttachCondit'])?$data['setIsAttachCondit']:1;//После редактирования карты с условным прикреплением она должна перестать быть условной.
		$queryParams['LpuRegionType_id'] = $data['LpuRegionType_id'];
		$queryParams['LpuRegion_id'] = $data['LpuRegion_id'];
		$queryParams['MedStaffFact_id'] = !empty($data['MedStaffFact_id'])?$data['MedStaffFact_id']:null;
        $queryParams['LpuRegion_fapid'] = !empty($data['LpuRegion_Fapid'])?$data['LpuRegion_Fapid']:null;
		$queryParams['LpuAttachType_id'] = $data['LpuAttachType_id'];
		$queryParams['CardCloseCause_id'] = !empty($data['CardCloseCause_id'])?$data['CardCloseCause_id']:null;
		$queryParams['PersonCardAttach_id'] = !empty($data['PersonCardAttach_id'])?$data['PersonCardAttach_id']:null;
		$queryParams['PersonCard_IsAttachAuto'] = !empty($data['PersonCard_IsAttachAuto'])?$data['PersonCard_IsAttachAuto']:null;
		$queryParams['pmUser_id'] = $data['pmUser_id'];
		$declare = "";
		$exec = "@Error_Message = @ErrMessage output;";
		$select = " select @Res as PersonCard_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg";
		if($procedure=='p_PersonCard_upd')
		{
			$declare = "
				@PersonCard_cid bigint,
            	@PersonCard_nid bigint,";
			$exec = "
				@Error_Message = @ErrMessage output,
                @PersonCard_cid = @PersonCard_cid output,
                @PersonCard_nid = @PersonCard_nid output;
			";
			$select = "select ISNULL(@PersonCard_nid,@Res) as PersonCard_id, @PersonCard_cid as cid, @ErrCode as Error_Code, @ErrMessage as Error_Msg";
			//if($data['session'][''])
		}
		if($checkPolisChanged) //https://redmine.swan.perm.ru/issues/98974
		{
			$queryParams['CardCloseCause_id'] = $CardCloseCause_new;
			//В этом случае новое будет условным
			//$queryParams['PersonCard_IsAttachCondit'] = 2;
			//$queryParams['PersonCardAttach_id'] = null;
			$select .=", 1 as disable_print;";
		}
		else{
			if($this->getRegionNick() == 'astra' && $data['LpuAttachType_id'] == 1)
				$select .=", 1 as disable_print;";
			else
				$select .=", 0 as disable_print;";
		}
		$query = "
            declare
                @Res bigint,
                ".$declare."
                @ErrCode int,
                @ErrMessage varchar(4000);
            set @Res = :PersonCard_id;
            exec " . $procedure . "
                @PersonCard_id = @Res output,
                @Lpu_id = :Lpu_id,
                @Server_id = :Server_id,
                @Person_id = :Person_id,
                @PersonCard_begDate = :PersonCard_begDate,
                @PersonCard_endDate = :PersonCard_endDate,
                @PersonCard_Code = :PersonCard_Code,
                @PersonCard_IsAttachAuto = :PersonCard_IsAttachAuto,
				@PersonCard_IsAttachCondit = :PersonCard_IsAttachCondit,
				@LpuRegionType_id = :LpuRegionType_id,
                @LpuRegion_id = :LpuRegion_id,
                @MedStaffFact_id = :MedStaffFact_id,
                @LpuRegion_fapid = :LpuRegion_fapid,
				@LpuAttachType_id = :LpuAttachType_id,
                @CardCloseCause_id = :CardCloseCause_id,
				@PersonCardAttach_id = :PersonCardAttach_id,
                @pmUser_id = :pmUser_id,
                @Error_Code = @ErrCode output,
                ".$exec. $select;//."
            //select @PersonCard_nid as PersonCard_id, @PersonCard_cid as cid, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
        //";


		/*echo getDebugSQL($query, $queryParams);
		$this->db->trans_rollback();
		return false;*/

		//PersonAmbulatCard_id
        $result = $this->db->query($query, $queryParams);

        if (is_object($result))
        {
            $sel = $result->result('array');
			if ( strlen($sel[0]['Error_Msg']) > 0 )
			{
				$this->db->trans_rollback();
				return $sel;
			}
			else
			{
				$this->db->trans_commit();
				$query_get_PACL = 'select top 1 PersonAmbulatCardLink_id from v_PersonAmbulatCardLink with(nolock) where PersonCard_id = ?';
				$response = $this->getFirstRowFromQuery($query_get_PACL, array($data['PersonCard_id']));
				if(isset($data['PersonAmbulatCard_id']) && $data['PersonAmbulatCard_id'] > 0 ){
					if($procedure=='p_PersonCard_ins' || !isset($response['PersonAmbulatCardLink_id'])){
						$query = "
						declare
							@ErrCode int,
							@ErrMessage varchar(4000);
						exec p_PersonAmbulatCardLink_ins
							@PersonAmbulatCardLink_id = null,
							@PersonAmbulatCard_id = :PersonAmbulatCard_id,
							@PersonCard_id = :PersonCard_id,
							@pmUser_id = :pmUser_id,
							@Error_Code = @ErrCode output,
							@Error_Message = @ErrMessage output;
						select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
						";
						$this->db->query($query, array(
							'PersonAmbulatCard_id' => $data['PersonAmbulatCard_id'],
							'PersonCard_id' => $sel[0]['PersonCard_id'],
							'pmUser_id' => $data['pmUser_id']
						));
						$sql="select Lpu_id from PersonAmbulatCard with(nolock) where PersonAmbulatCard_id = :PersonAmbulatCard_id";
						$res = $this->db->query($sql, array('PersonAmbulatCard_id'=>$data['PersonAmbulatCard_id']));
						$res = $res->result('array');
						if(count($res)>0&&$res[0]['Lpu_id']!=$data['Lpu_id']){
							$sql = 'UPDATE PersonAmbulatCard SET Lpu_id = :Lpu_id WHERE PersonAmbulatCard_id = :PersonAmbulatCard_id';
							$this->db->query($sql, array('PersonAmbulatCard_id'=>$data['PersonAmbulatCard_id'],'Lpu_id'=>$data['Lpu_id']));
						}
					}else{

						/*$query = 'select top 1 PersonAmbulatCardLink_id from v_PersonAmbulatCardLink with(nolock) where PersonCard_id = ?';
						$response = $this->getFirstRowFromQuery($query, array($data['PersonCard_id']));*/

						//if(isset($response['PersonAmbulatCardLink_id']) && $response['PersonAmbulatCardLink_id'] > 0)

						$query = "
							declare
								@ErrCode int,
								@ErrMessage varchar(4000);
							exec p_PersonAmbulatCardLink_upd
								@PersonAmbulatCardLink_id = :PersonAmbulatCardLink_id,
								@PersonAmbulatCard_id = :PersonAmbulatCard_id,
								@PersonCard_id = :PersonCard_id,
								@pmUser_id = :pmUser_id,
								@Error_Code = @ErrCode output,
								@Error_Message = @ErrMessage output;
							select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
						";

						$this->db->query($query, array(
							'PersonAmbulatCardLink_id' => $response['PersonAmbulatCardLink_id'],
							'PersonAmbulatCard_id' => $data['PersonAmbulatCard_id'],
							'PersonCard_id' => isset($sel[0]['PersonCard_id'])?$sel[0]['PersonCard_id']:$data['PersonCard_id'],
							'pmUser_id' => $data['pmUser_id']
						));
					}
				}

				//$this->db->trans_commit();
				// Для Казахстана делаем сохранение в сервисе РПН
				/*if ($this->regionNick == 'kz' && (!isset($data['noTransferService']))) {
					$this->load->model("ServiceRPN_model");
					unset($data['session']);
					$this->ServiceRPN_model->setPersonRPN($data, $sel);
				}*/
				return $sel;
			}
        }
        else
        {
        	$this->db->trans_rollback();
			return false;
        }
	}

    /**
     * Сохранение участка для прикрепления
     */
    function savePersonCardLpuRegion($data)
    {
        $params = array();
        $params['Lpu_id'] = $data['Lpu_id'];
        $params['LpuRegionType_id'] = $data['LpuRegionType_id'];
        $params['LpuRegion_id'] = $data['LpuRegion_id'];
        $params['PersonCard_id'] = $data['PersonCard_id'];
		$params['pmUser_id'] = $data['pmUser_id'];
        $query = "
            update PersonCard set LpuRegion_id = :LpuRegion_id, pmUser_updID=:pmUser_id, PersonCard_updDT=getdate() where PersonCard_id = :PersonCard_id
        ";
        $result_pc = $this->db->query($query, $params);
        if($result_pc)
        {
			$query = "
				update PersonCardState set LpuRegion_id = :LpuRegion_id, pmUser_updID=:pmUser_id, PersonCardState_updDT=getdate() where PersonCard_id = :PersonCard_id
			";
			$result_pcs = $this->db->query($query,$params);
			if($result_pcs){
				return array(array('PersonCard_id' => $params['PersonCard_id'], 'Error_Msg' => ''));
			}
			else
				return false;
        }
        else
            return false;
    }

	/**
	 *	Сохранение амбулаторной карты пациента по ДМС
	 */
	function savePersonCardDms($data)
	{
		$current_date = date('Ymd'); // текущая дата

		$procedure = '';

        if (!isset($data['PersonCard_id']))
        {
        	$data['PersonCard_id'] = NULL;
            $procedure = 'p_PersonCard_ins';
        }
        else if ( $data['PersonCard_id'] > 0 )
        {
            $procedure = 'p_PersonCard_upd';
        }
        else
        {
            return array(0 => array('Error_Msg' => 'Неверное значение PersonCard_id'));
        }

		// при добавлении
		if ( !($data['PersonCard_id'] > 0) )
		{
			/*
			 * 1. Если есть активное ДМС прикрепление, то выводить сообщение:
			 * «Невозможно создать новое ДМС прикрепление, так как уже существует ДМС прикрепление с действующим договором».
			 * ОК. Сохранение отменить.
			 */
			$sql = "
				SELECT
					count(*) as cnt
				FROM v_PersonCard with (nolock)
				WHERE
					Person_id = ?
					and LpuAttachType_id = 5
					and PersonCard_endDate > dbo.tzGetDate()
					and CardCloseCause_id is null
			";
			//die(пуе);
			$result = $this->db->query($sql, array($data['Person_id']));
			$res = $result->result('array');
			if ( $res[0]['cnt'] > 0 )
			{
				return array( 0 => array('Error_Code' => 666, 'Error_Msg'=>'Невозможно создать новое ДМС прикрепление, так как уже существует ДМС прикрепление с действующим договором.') );
			}
			/*
			 * проверяем ЛПУ основного прикрепления
			 */
			/*
			$common_region_lpu_id = $data['Lpu_id'];
			$sql = "
				SELECT
					Lpu_id,
					PersonCard_id
				FROM v_PersonCard with (nolock)
				WHERE
					Person_id = ?
					and LpuAttachType_id = 1
			";
			$result = $this->db->query($sql, array($data['Person_id']));
			$res = $result->result('array');
			$count_res = count($res);
			if ( count($res) == 1 )
			{
				$common_region_lpu_id = $res[0]['Lpu_id'];
				// закрываем там основное прикрепление
				if ( $common_region_lpu_id != $data['Lpu_id'] )
				{
					$sql = "
						SELECT
							PersonCard_id,
							rtrim(PersonCard_Code) as PersonCard_Code,
							Person_id,
							Server_id,
							LpuAttachType_id,
							LpuRegionType_id,
							convert(varchar,cast(PersonCard_begDate as datetime),112) as PersonCard_begDate,
							convert(varchar,cast(PersonCard_endDate as datetime),112) as PersonCard_endDate,
							CardCloseCause_id,
							Lpu_id,
							LpuRegion_id
						FROM
							v_PersonCard with (nolock)
						WHERE
							PersonCard_id = ?
					";
					$result = $this->db->query($sql, array($res[0]['PersonCard_id']));

					if (is_object($result))
					{
						$res = $result->result('array');
						$queryParams = array(
							$res[0]['PersonCard_id'],
							$res[0]['Lpu_id'],
							$res[0]['Server_id'],
							$res[0]['Person_id'],
							$res[0]['PersonCard_begDate'],
							date('Ymd'),
							$res[0]['PersonCard_Code'],
							$res[0]['LpuRegion_id'],
							$res[0]['LpuAttachType_id'],
							$data['pmUser_id']
						);

						$sql = "
							declare
								@Res bigint,
								@ErrCode int,
								@ErrMessage varchar(4000);
							set @Res = ?;
							exec p_PersonCard_upd
								@PersonCard_id = @Res output,
								@Lpu_id = ?,
								@Server_id = ?,
								@Person_id = ?,
								@PersonCard_begDate = ?,
								@PersonCard_endDate = ?,
								@PersonCard_Code = ?,
								@LpuRegion_id = ?,
								@LpuAttachType_id = ?,
								@CardCloseCause_id = 1,
								@PersonCard_IsAttachCondit = null,
								@PersonCard_IsAttachAuto = null,
								@PersonCard_AttachAutoDT = null,
								@pmUser_id = ?,
								@Error_Code = @ErrCode output,
								@Error_Message = @ErrMessage output;
							select @Res as PersonCard_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
						";
						$result = $this->db->query($sql, $queryParams);
						if (!is_object($result))
						{
							return array(array('success'=>false, 'Error_Msg'=>'Ошибка закрытия прикрепления.'));
						}
					}
					else
					{
						return array(array('success'=>false, 'Error_Msg'=>'Ошибка закрытия прикрепления.'));
					}
				}
			}
			if ( ($common_region_lpu_id != $data['Lpu_id']) || ($count_res == 0) )
			{
				// просто создаем основное прикрепление
				$sql = "
					declare
						@Res bigint,
						@ErrCode int,
						@ErrMessage varchar(4000);
					set @Res = null;
					exec p_PersonCard_ins
						@PersonCard_id = @Res output,
						@Lpu_id = ?,
						@Server_id = ?,
						@Person_id = ?,
						@PersonCard_begDate = ?,
						@PersonCard_Code = null,
						@PersonCard_IsAttachCondit = 2,
						@LpuRegion_id = null,
						@LpuAttachType_id = 1,
						@CardCloseCause_id = null,
						@pmUser_id = ?,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMessage output;
					select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
				";

				$result = $this->db->query($sql, array(
					$data['Lpu_id'],
					$data['Server_id'],
					$data['Person_id'],
					$data['PersonCard_begDate'],
					$data["pmUser_id"]
				));
				$sel = $result->result('array');
				if ( strlen($sel[0]['Error_Msg'] ) > 0)
				{
					return array( 0 => array('Error_Code' => 1, 'Error_Msg'=>'Не удалось создать основное прикрепление.') );
				}
			}
			*/
			// если есть неактивное ДМС прикрепление, то закрываем его
			$sql = "
				SELECT
					PersonCard_id,
					rtrim(PersonCard_Code) as PersonCard_Code,
					Person_id,
					Server_id,
					LpuAttachType_id,
					LpuRegionType_id,
					convert(varchar,cast(PersonCard_begDate as datetime),112) as PersonCard_begDate,
					convert(varchar,cast(PersonCard_endDate as datetime),112) as PersonCard_endDate,
					CardCloseCause_id,
					Lpu_id,
					LpuRegion_id,
					PersonCard_DmsPolisNum,
					convert(varchar,cast(PersonCard_DmsBegDate as datetime),112) as PersonCard_DmsBegDate,
					convert(varchar,cast(PersonCard_DmsEndDate as datetime),112) as PersonCard_DmsEndDate,
					OrgSMO_id
				FROM v_PersonCard with (nolock)
				WHERE
					Person_id = ?
					and LpuAttachType_id = 5
					and PersonCard_endDate <= dbo.tzGetDate()
					and CardCloseCause_id is null
			";

			$result = $this->db->query($sql, array($data['Person_id']));

			if (is_object($result))
			{
				$res = $result->result('array');
				if ( count($res) > 0 )
				{
					$queryParams = array(
						$res[0]['PersonCard_id'],
						$res[0]['Lpu_id'],
						$res[0]['Server_id'],
						$res[0]['Person_id'],
						$res[0]['PersonCard_begDate'],
						$res[0]['PersonCard_endDate'],
						$res[0]['PersonCard_Code'],
						$res[0]['LpuRegion_id'],
						5,
						6,
						$res[0]['PersonCard_DmsPolisNum'],
						$res[0]['PersonCard_DmsBegDate'],
						$res[0]['PersonCard_DmsEndDate'],
						$res[0]['OrgSMO_id'],
						$data['pmUser_id']
					);

					$sql = "
						declare
							@Res bigint,
							@ErrCode int,
							@ErrMessage varchar(4000);
						set @Res = ?;
						exec p_PersonCard_upd
							@PersonCard_id = @Res output,
							@Lpu_id = ?,
							@Server_id = ?,
							@Person_id = ?,
							@PersonCard_begDate = ?,
							@PersonCard_endDate = ?,
							@PersonCard_Code = ?,
							@LpuRegion_id = ?,
							@LpuAttachType_id = ?,
							@CardCloseCause_id = ?,
							@PersonCard_IsAttachCondit = null,
							@PersonCard_IsAttachAuto = null,
							@PersonCard_AttachAutoDT = null,
							@PersonCard_DmsPolisNum = ?,
							@PersonCard_DmsBegDate = ?,
							@PersonCard_DmsEndDate = ?,
							@OrgSMO_id = ?,
							@pmUser_id = ?,
							@Error_Code = @ErrCode output,
							@Error_Message = @ErrMessage output;
						select @Res as PersonCard_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
					";
					$result = $this->db->query($sql, $queryParams);
					if (!is_object($result))
					{
						return array(array('success'=>false, 'Error_Msg'=>'Ошибка редактирования прикрепления.'));
					}
				}
			}
			else
			{
				return array(array('success'=>false, 'Error_Msg'=>'Ошибка редактирования прикрепления.'));
			}

			// создаем прикрепление
			$queryParams = array();
			$queryParams[] = null;
			$queryParams[] = $data['Lpu_id'];
			$queryParams[] = $data['Server_id'];
			$queryParams[] = $data['Person_id'];
			$queryParams[] = $data['PersonCard_begDate'];
			$queryParams[] = $data['PersonCard_endDate'];
			$queryParams[] = null;
			$queryParams[] = $data['PersonCard_DmsPolisNum'];
			$queryParams[] = $data['PersonCard_DmsBegDate'];
			$queryParams[] = $data['PersonCard_DmsEndDate'];
			$queryParams[] = $data['OrgSMO_id'];
			$queryParams[] = null;
			$queryParams[] = 5;
			$queryParams[] = null;
			$queryParams[] = $data['pmUser_id'];
			$query = "
				declare
					@Res bigint,
					@ErrCode int,
					@ErrMessage varchar(4000);
				set @Res = ?;
				exec p_PersonCard_ins
					@PersonCard_id = @Res output,
					@Lpu_id = ?,
					@Server_id = ?,
					@Person_id = ?,
					@PersonCard_begDate = ?,
					@PersonCard_endDate = ?,
					@PersonCard_Code = ?,
					@PersonCard_DmsPolisNum = ?,
					@PersonCard_DmsBegDate = ?,
					@PersonCard_DmsEndDate = ?,
					@OrgSMO_id = ?,
					@PersonCard_IsAttachCondit = null,
					@LpuRegion_id = ?,
					@LpuAttachType_id = ?,
					@CardCloseCause_id = ?,
					@pmUser_id = ?,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMessage output;
				select @Res as PersonCard_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			";
			$result = $this->db->query($query, $queryParams);
			if (is_object($result))
			{
				$sel = $result->result('array');
				if ( strlen($sel[0]['Error_Msg']) > 0 )
				{
					return $sel;
				}
				else
				{
					return $sel;
				}
			}
			else
			{
				return false;
			}
		}
		else
		{
			$sql = "
				SELECT
					PersonCard_id,
					rtrim(PersonCard_Code) as PersonCard_Code,
					Person_id,
					Server_id,
					LpuAttachType_id,
					LpuRegionType_id,
					convert(varchar,cast(PersonCard_begDate as datetime),112) as PersonCard_begDate,
					convert(varchar,cast(PersonCard_endDate as datetime),112) as PersonCard_endDate,
					CardCloseCause_id,
					Lpu_id,
					LpuRegion_id,
					PersonCard_DmsPolisNum,
					convert(varchar,cast(PersonCard_DmsBegDate as datetime),112) as PersonCard_DmsBegDate,
					convert(varchar,cast(PersonCard_DmsEndDate as datetime),112) as PersonCard_DmsEndDate,
					OrgSMO_id
				FROM
					v_PersonCard_all with (nolock)
				WHERE
					PersonCard_id = ?
			";
			$result = $this->db->query($sql, array($data['PersonCard_id']));

			if (is_object($result))
			{
				$res = $result->result('array');
				$queryParams = array(
					$res[0]['PersonCard_id'],
					$res[0]['Lpu_id'],
					$res[0]['Server_id'],
					$res[0]['Person_id'],
					$res[0]['PersonCard_begDate'],
					$data['PersonCard_endDate'],
					$res[0]['PersonCard_Code'],
					$res[0]['LpuRegion_id'],
					5,
					isset($data['CardCloseCause_id']) && $data['CardCloseCause_id'] ? $data['CardCloseCause_id'] : null,
					$data['PersonCard_DmsPolisNum'],
					$data['PersonCard_DmsBegDate'],
					$data['PersonCard_DmsEndDate'],
					$data['OrgSMO_id'],
					$data['pmUser_id']
				);

				$sql = "
					declare
						@Res bigint,
						@ErrCode int,
						@ErrMessage varchar(4000);
					set @Res = ?;
					exec p_PersonCard_upd
						@PersonCard_id = @Res output,
						@Lpu_id = ?,
						@Server_id = ?,
						@Person_id = ?,
						@PersonCard_begDate = ?,
						@PersonCard_endDate = ?,
						@PersonCard_Code = ?,
						@LpuRegion_id = ?,
						@LpuAttachType_id = ?,
						@CardCloseCause_id = ?,
						@PersonCard_IsAttachCondit = null,
						@PersonCard_IsAttachAuto = null,
						@PersonCard_AttachAutoDT = null,
						@PersonCard_DmsPolisNum = ?,
						@PersonCard_DmsBegDate = ?,
						@PersonCard_DmsEndDate = ?,
						@OrgSMO_id = ?,
						@pmUser_id = ?,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMessage output;
					select @Res as PersonCard_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
				";
				$result = $this->db->query($sql, $queryParams);
				if (!is_object($result))
				{
					return array(array('success'=>false, 'Error_Msg'=>'Ошибка редактирования прикрепления.'));
				}
			}
			else
			{
				return array(array('success'=>false, 'Error_Msg'=>'Ошибка редактирования прикрепления.'));
			}
		}

		$response = $result->result('array');
		return array( 0 => array('PersonCard_id' => $response[0]['PersonCard_id'], 'Error_Code' => 0, 'Error_Msg'=>'') );
	}

	/**
	 * Открепление, только если пациент не из БДЗ и прикрепление соответствует ЛПУ текущего пользователя.
	function closePersonCardNotBdz($data)
	{
		$sql = "
			SELECT
				rtrim(pc.PersonCard_Code) as PersonCard_Code,
				pc.Person_id,
				pc.LpuAttachType_id,
				pc.LpuRegionType_id,
   				convert(varchar,cast(pc.PersonCard_begDate as datetime),112) as PersonCard_begDate,
   				convert(varchar,cast(pc.PersonCard_endDate as datetime),112) as PersonCard_endDate,
				pc.CardCloseCause_id,
				pc.Lpu_id,
				pc.LpuRegion_id,
				pc.PersonCard_IsAttachCondit,
				--case when pl.Polis_begDate <= dbo.tzGetDate() and (pl.Polis_endDate is null or pl.Polis_endDate > dbo.tzGetDate()) then 'true' else 'false' end as Person_HasPolis,
				case when ps.Person_deadDT is null then 'false' else 'true' end as Person_IsDead,
				CASE WHEN ps.Server_pid = 0 THEN 'true' ELSE 'false' END as Person_IsBDZ
			FROM
				v_PersonCard pc with (nolock)
				LEFT JOIN v_PersonState ps with(nolock) on ps.Person_id = pc.Person_id
				--LEFT JOIN v_Polis pl with(nolock) on pl.Polis_id = ps.Polis_id
			WHERE
				pc.PersonCard_id = ?
				and pc.Lpu_id = ?
		";
        $result = $this->db->query($sql, array($data['PersonCard_id'], $data['Lpu_id']));

        if (is_object($result))
        {
            $res = $result->result('array');
			if ( count($res) == 0 )
			{
				return array(array('success'=>false, 'Error_Msg'=>'В вашем ЛПУ нет этой карты.'));
			}
			if ( $res[0]['Person_IsBDZ'] == 'true' )
			{
				return array(array('success'=>false, 'Error_Msg'=>'Нельзя открепить, т.к. пациент в БДЗ.'));
			}
			$queryParams = array(
				$data['PersonCard_id'],
				$data['Lpu_id'],
				$data['Server_id'],
				$res[0]['Person_id'],
				$res[0]['PersonCard_begDate'],
				date('Ymd'),
				$res[0]['PersonCard_Code'],
				$res[0]['LpuRegion_id'],
				$res[0]['LpuAttachType_id'],
				($res[0]['Person_IsDead'] == 'true') ? 2 : 5,//CardCloseCause_id
				$res[0]['PersonCard_IsAttachCondit'],//После открепления карты с условным прикреплением , прикрепление тоже  должно перестать быть условным?
				$data['pmUser_id']
			);

			$sql = "
				declare
					@Res bigint,
					@ErrCode int,
					@ErrMessage varchar(4000);
				set @Res = ?;
				exec p_PersonCard_upd
					@PersonCard_id = @Res output,
					@Lpu_id = ?,
					@Server_id = ?,
					@Person_id = ?,
					@PersonCard_begDate = ?,
					@PersonCard_endDate = ?,
					@PersonCard_Code = ?,
					@LpuRegion_id = ?,
					@LpuAttachType_id = ?,
					@CardCloseCause_id = ?,
					@PersonCard_IsAttachCondit = ?,
					@PersonCard_IsAttachAuto = null,
					@PersonCard_AttachAutoDT = null,
					@pmUser_id = ?,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMessage output;
				select @Res as PersonCard_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			";
			$result = $this->db->query($sql, $queryParams);
			if (is_object($result))
			{
				return $result->result('array');
			}
			else
			{
				return false;
			}
        }
        else
        {
        	return false;
        }
	}
	 */

	/**
	 *	Открепление пациента
	 */
	function closePersonCard($data)
	{
		// проверяем на заявку
		if ( !isset($data['cancelDrugRequestCheck']) || ($data['cancelDrugRequestCheck'] != 2) )
		{
			$sql = "
				select
					count(*) as cnt
				from
					v_DrugRequestRow DRR with (NOLOCK)
					inner join DrugRequest DR  with (NOLOCK) on DR.DrugRequest_id = DRR.DrugRequest_id and DR.Lpu_id = ? and DRR.Person_id = (SELECT
						top 1 Person_id
						FROM
							v_PersonCard with (nolock)
						WHERE
							PersonCard_id = ?
					and Lpu_id = ?)
					inner join DrugRequestPeriod DRP  with (NOLOCK) on DRP.DrugRequestPeriod_id = DR.DrugRequestPeriod_id
					outer apply (
						select MAX(DrugRequestPeriod_begDate) max_date
						from DrugRequestPeriod with (NOLOCK)
					) DRP_MD
				where
					DRP.DrugRequestPeriod_begDate = DRP_MD.max_date
			";
			$result = $this->db->query($sql, array($data['Lpu_id'], $data['PersonCard_id'], $data['Lpu_id']));

			if (is_object($result))
			{
				$res = $result->result('array');
				if ( $res[0]['cnt'] != 0 )
				{
					return array(array('success'=>false, 'Error_Msg'=>'На данного пациента заявлены медикаменты в Вашем ЛПУ. Возможно после открепления пациент централизованно вновь будет условно прикреплен к Вашей ЛПУ. Открепить пациента?', 'Error_Code'=>666));
				}
			}
		}
		$sql = "
			SELECT
				rtrim(pc.PersonCard_Code) as PersonCard_Code,
				pc.Person_id,
				pc.LpuAttachType_id,
				pc.LpuRegionType_id,
   				convert(varchar,cast(pc.PersonCard_begDate as datetime),112) as PersonCard_begDate,
   				convert(varchar,cast(pc.PersonCard_endDate as datetime),112) as PersonCard_endDate,
				pc.CardCloseCause_id,
				pc.Lpu_id,
				pc.LpuRegion_id,
				case when pl.Polis_begDate <= dbo.tzGetDate() and (pl.Polis_endDate is null or pl.Polis_endDate > dbo.tzGetDate()) then 'true' else 'false' end as Person_HasPolis,
				case when ps.Person_deadDT is null then 'false' else 'true' end as Person_IsDead,
				pc.PersonCardAttach_id,
				lat.LpuAttachType_SysNick,
				ccc.CardCloseCause_SysNick
			FROM
				v_PersonCard pc with (nolock)
				LEFT JOIN v_PersonState ps with(nolock) on ps.Person_id = pc.Person_id
				LEFT JOIN v_Polis pl with(nolock) on pl.Polis_id = ps.Polis_id
				LEFT JOIN v_LpuAttachType lat with(nolock) on lat.LpuAttachType_id = pc.LpuAttachType_id
				LEFT JOIN v_CardCloseCause ccc with(nolock) on ccc.CardCloseCause_id = pc.CardCloseCause_id
			WHERE
				pc.PersonCard_id = ?
				and pc.Lpu_id = ?
		";
        $result = $this->db->query($sql, array($data['PersonCard_id'], $data['Lpu_id']));

        if (is_object($result))
        {
            $res = $result->result('array');
			if ( count($res) == 0 )
			{
				return array(array('success'=>false, 'Error_Msg'=>'В вашем ЛПУ нет этой карты.'));
			}

			$close_date = date('Ymd');

			//закрытие активных льгот
			if ($res[0]['LpuAttachType_SysNick'] == 'main' && $res[0]['CardCloseCause_SysNick'] == 'deregister') { //если прикрепление основное и причина закрытия "Изменение регистрации (выезд в другой регион)", то закрываем все активные льготы пациента
				$this->load->model('Privilege_model', 'Privilege_model');
				$priv_close_result = $this->Privilege_model->closeAllActivePrivilegesForPerson(array(
					'Person_id' => $res[0]['Person_id'],
					'PersonPrivilege_endDate' => $close_date,
					'PrivilegeCloseType_id' => $this->getObjectIdByCode('PrivilegeCloseType', '2') //2 - Переезд в другой регион
				));
				if (!empty($priv_close_result['Error_Msg'])) {
					return array(array('success '=> false, 'Error_Msg' => !empty($priv_close_result['Error_Msg']) ? $priv_close_result['Error_Msg'] : 'При закрытии льго произошла ошибка'));
				}
			}

			$queryParams = array(
				$data['PersonCard_id'],
				$data['Lpu_id'],
				$data['Server_id'],
				$res[0]['Person_id'],
				$res[0]['PersonCard_begDate'],
				$close_date,
				$res[0]['PersonCard_Code'],
                $res[0]['PersonCardAttach_id'],
				$res[0]['LpuRegion_id'],
				$res[0]['LpuAttachType_id'],
				($res[0]['Person_IsDead'] == 'true') ? 2 : 5,
				$data['pmUser_id']
			);

			$sql = "
				declare
					@Res bigint,
					@ErrCode int,
					@ErrMessage varchar(4000);
				set @Res = ?;
				exec p_PersonCard_upd
					@PersonCard_id = @Res output,
					@Lpu_id = ?,
					@Server_id = ?,
					@Person_id = ?,
					@PersonCard_begDate = ?,
					@PersonCard_endDate = ?,
					@PersonCard_Code = ?,
					@PersonCardAttach_id = ?,
					@LpuRegion_id = ?,
					@LpuAttachType_id = ?,
					@CardCloseCause_id = ?,
					@PersonCard_IsAttachCondit = null,
					@PersonCard_IsAttachAuto = null,
					@PersonCard_AttachAutoDT = null,
					@pmUser_id = ?,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMessage output;
				select @Res as PersonCard_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			";
			$result = $this->db->query($sql, $queryParams);
			if (is_object($result))
			{
				return $result->result('array');
			}
			else
			{
				return false;
			}
        }
        else
        {
        	return false;
        }
	}

	/**
	 *	Получение данных для печати амбулаторной карты
	 */
	function getMedCard($data)
	{
		$filterList = array('(1 = 1)');
		$queryParams = array();

		$queryParams['Lpu_id'] = $data['Lpu_id'];

		if ( !empty($data['Person_id'])) {
			$filterList[] = 'PS.Person_id = :Person_id';
			$queryParams['Person_id'] = $data['Person_id'];
		}

		if ( !empty($data['PersonCard_id'])) {
			$filterList[] = 'PC.PersonCard_id = :PersonCard_id';
			$queryParams['PersonCard_id'] = $data['PersonCard_id'];
		}

		$sql = "
			SELECT top 1
				PS.Person_id,
				PC.PersonCard_id,
				PC.Lpu_id,
				PC.LpuAttachType_id,
				rtrim(isnull(PC.LpuRegion_Name, '')) as LpuRegion_Name,
				LpuAttachType.LpuAttachType_Name,
				convert(varchar,cast(PS.Person_BirthDay as datetime),104) as Person_BirthDay,
				ISNULL(E.Ethnos_Name,'') as Ethnos_Name,
				PS.Person_SurName,
				PS.Person_FirName,
				PS.Person_SecName,
				ISNULL(PS.Person_Inn,'') as Person_Inn,
				Sex.Sex_Name,
				case when Sex.Sex_Code = 3 then 1 else Sex.Sex_Code end as Sex_Code,
				case when PC.Lpu_id = :Lpu_id then PC.PersonCard_Code else null end as PersonCard_Code,
                LpuCur.Lpu_Name as Lpu_Name,
                LpuCur.Lpu_OGRN as Lpu_OGRN,
                OrgCur.Org_OKPO as Org_OKPO,
                AdrCur.Address_Address as Address_Address,
                /*
                case when PC.PersonCard_id is not null then Lpu.Lpu_Name else LpuCur.Lpu_Name end as Lpu_Name,
                case when PC.PersonCard_id is not null then Lpu.Lpu_OGRN else LpuCur.Lpu_OGRN end as Lpu_OGRN,
                case when PC.PersonCard_id is not null then Adr.Address_Address else AdrCur.Address_Address end as Address_Address,
                */
				PS.Person_Snils,
				OrgSmo.OrgSMO_Nick,
				case when Polis.PolisType_id = 4 then '' else PS.Polis_Ser end as Polis_Ser,
				case when Polis.PolisType_id = 4 then PS.Person_EdNum else PS.Polis_Num end as Polis_Num,
				PAdr.Address_Address as PAddress_Address,
				UAdr.Address_Address as UAddress_Address,
				convert(varchar,KLAType.KLAreaType_Code,104) + ': ' + KLAType.KLAreaType_Name as KLAreaType_Name,
				DocumentType.DocumentType_Name,
				PS.Document_Num,
				PS.Document_Ser,
				PS.Person_Phone,
				job.Org_Name as Job_Name,
				OrgUnion.OrgUnion_Name,
				Post.Post_Name,
				EU.EvnUdost_Ser,
				EU.EvnUdost_Num,
				convert(varchar(10), EU.EvnUdost_setDate, 104) as EvnUdost_Date,
				SSt.SocStatus_Name,
				InvalidGroupType.InvalidGroupType_Name
			FROM
				v_PersonState PS with (nolock)
				left join v_PersonCard_all PC with (nolock) on PS.Person_id = PC.Person_id
				left join v_PersonInfo PI with (nolock) on PI.Person_id = PS.Person_id
				left join v_Ethnos E with (nolock) on E.Ethnos_id = PI.Ethnos_id
				left join v_PersonJob pjob with (nolock) on PS.Job_id = pjob.Job_id
				left join v_Org job with (nolock) on job.Org_id = pjob.Org_id
				left join v_OrgUnion OrgUnion with (nolock) on OrgUnion.OrgUnion_id = pjob.OrgUnion_id
				left join v_Post Post with (nolock) on Post.Post_id = pjob.Post_id
				left join v_EvnUdost EU with (nolock) on EU.Person_id = PC.Person_id
				left join v_LpuAttachType LpuAttachType with (nolock) on LpuAttachType.LpuAttachType_id  = PC.LpuAttachType_id
				left join v_Polis Polis with (nolock) on PS.Polis_id  = Polis.Polis_id
				left join v_OrgSmo OrgSmo with (nolock) on OrgSmo.OrgSmo_id  = Polis.OrgSmo_id
				left join v_Sex Sex with (nolock) on Sex.Sex_id  = PS.Sex_id
				left join v_Document Document with (nolock) on PS.Document_id  = Document.Document_id
				left join v_DocumentType DocumentType with (nolock) on Document.DocumentType_id  = DocumentType.DocumentType_id
				left join v_Lpu Lpu with (nolock) on Lpu.Lpu_id = PC.Lpu_id
				left join v_Org Org with (nolock) on Org.Org_id = Lpu.Org_id
				left join v_Lpu LpuCur with (nolock) on LpuCur.Lpu_id = :Lpu_id
				left join v_Org OrgCur with (nolock) on OrgCur.Org_id = LpuCur.Org_id
				left join v_Address Adr with (nolock) on Adr.Address_id  = IsNull(Org.UAddress_id, Org.PAddress_id)
				left join v_Address AdrCur with (nolock) on AdrCur.Address_id  = IsNull(OrgCur.UAddress_id, OrgCur.PAddress_id)
				left join v_Address PAdr with (nolock) on PAdr.Address_id  = PS.PAddress_id
				left join v_Address UAdr with (nolock) on UAdr.Address_id  = PS.UAddress_id
				left join v_KLAreaType KLAType with (nolock) on KLAType.KLAreaType_id = PAdr.KLAreaType_id
				left join v_SocStatus SSt with (nolock) on SSt.SocStatus_id = PS.SocStatus_id
				outer apply(
					select top 1 IGT.InvalidGroupType_Code, IGT.InvalidGroupType_Name
					from v_EvnMse EM with(nolock)
						inner join v_InvalidGroupType IGT with(nolock) on  IGT.InvalidGroupType_id = EM.InvalidGroupType_id
					where EM.PersonEvn_id = PS.PersonEvn_id
					order by EM.EvnMse_setDT
				) as InvalidGroupType
			WHERE
				" . implode(' and ', $filterList) . "
		";
		// echo getDebugSQL($sql, $queryParams);
		$result = $this->db->query($sql, $queryParams);

        if (is_object($result))
        {

			$res = $result->result('array');

			if ( is_array($res) && count($res) > 0 ) {
				$sql = "
					SELECT
						isnull(PT.PrivilegeType_VCode, cast(PT.PrivilegeType_Code as varchar)) as PrivilegeType_Code
					FROM
						v_PersonPrivilege PP with (nolock)
						inner join v_PrivilegeType PT with (nolock) on PT.PrivilegeType_id = PP.PrivilegeType_id
					WHERE
						PP.Person_id = :Person_id
						and PT.ReceptFinance_id = 1
						--and PT.PrivilegeType_Code < 500
					ORDER BY
						PP.PrivilegeType_Code ASC
				";

				$result = $this->db->query($sql, array('Person_id' => $res[0]['Person_id']));

				if (is_object($result))
				{
					$code = $result->result('array');

					$codes = array();
					foreach ($code as $c)
						$codes[] = $c['PrivilegeType_Code'];

					$res = array_merge($res, array('1' => $codes));
				}
			}

            return $res;

        }
        else
        {
        	return false;
        }

	}

	/**
	 *	Получение данных по амбулаторной карте
	 */
	function getPersonCard($data)
	{
		$sql = "
			SELECT
				case when " . (isSuperadmin() ? '(1 = 1)' : '(1 = 0)') . " or pall.Lpu_id = :Lpu_id then 'edit' else 'view' end as accessType,
				pall.PersonCard_id,
				rtrim(rtrim(pall.PersonCard_Code)) as PersonCard_Code,
				pall.Person_id,
				pall.LpuAttachType_id,
				pall.PersonCard_IsAttachCondit,
				pall.LpuRegionType_id,
   				convert(varchar,cast(pall.PersonCard_begDate as datetime),104) as PersonCard_begDate,
   				convert(varchar,cast(pall.PersonCardBeg_insDT as datetime),104) as PersonCardBeg_insDT,
   				convert(varchar,cast(pall.PersonCard_endDate as datetime),104) as PersonCard_endDate,
   				convert(varchar,cast(pall.PersonCardEnd_insDT as datetime),104) as PersonCardEnd_insDT,
				pall.CardCloseCause_id,
				pall.Lpu_id,
				pall.LpuRegion_id,
				pc.MedStaffFact_id,
				pall.LpuRegion_fapid as LpuRegion_Fapid,
				--'77300002041' as LpuRegion_Fapid,
				pall.PersonCard_DmsPolisNum,
				convert(varchar,cast(pall.PersonCard_DmsBegDate as datetime),104) as PersonCard_DmsBegDate,
				convert(varchar,cast(pall.PersonCard_DmsEndDate as datetime),104) as PersonCard_DmsEndDate,
				pall.OrgSMO_id,
				pc.PersonCardAttach_id,
				PACLink.PersonAmbulatCard_id,
				PCA.PersonCardAttach_IsSMS-1 as PersonCardAttach_IsSMS,
				--PCA.PersonCardAttach_SMS,
				CASE WHEN PCA.PersonCardAttach_SMS is null then null else ('+7 '+SUBSTRING(PCA.PersonCardAttach_SMS,1,3)+' '+SUBSTRING(PCA.PersonCardAttach_SMS,4,10)) end as PersonCardAttach_SMS,
				PCA.PersonCardAttach_IsEmail-1 as PersonCardAttach_IsEmail,
				PCA.PersonCardAttach_Email,
				ISNULL(pc.PersonCard_IsAttachAuto,'') as PersonCard_IsAttachAuto
			FROM
				v_PersonCard_all pall with (nolock)
				outer apply(
					select top 1
						PersonCardAttach_id,
						MedStaffFact_id,
						PersonCard_IsAttachAuto
					from
						PersonCard with(nolock)
					where
						PersonCard_id = pall.PersonCard_id
						and Person_id = pall.Person_id
						and Lpu_id = pall.Lpu_id
						and LpuAttachType_id = pall.LpuAttachType_id
				) as pc
				left join v_PersonCardAttach PCA with(nolock) on PCA.PersonCardAttach_id = pc.PersonCardAttach_id
				outer apply(
					select top 1 pac.PersonAmbulatCard_id from v_PersonAmbulatCard pac with(nolock)
					left join v_PersonAmbulatCardLink PACLink with(nolock) on PACLink.PersonAmbulatCard_id = pac.PersonAmbulatCard_id
					where PACLink.PersonCard_id=pall.PersonCard_id) PACLink
			WHERE
				pall.PersonCard_id = :PersonCard_id
		";

		$params = array('PersonCard_id' => $data['PersonCard_id'], 'Lpu_id' => $data['Lpu_id']);
		//die(getDebugSQL($sql, $params));
		$result = $this->queryResult($sql, $params);

		if (!is_array($result)) {
			return false;
		}

		if (count($result) > 0) {
			$data['PersonCardAttach_id'] = 0;
			if( isset($result[0]['PersonCardAttach_id']) && !empty($result[0]['PersonCardAttach_id']) ) {
				//$result[0]['files'] = $this->getFilesOnPersonCardAttach($result[0]);
				$data['PersonCardAttach_id'] = $result[0]['PersonCardAttach_id'];
			}
			$result[0]['files'] = $this->getFilesOnPersonCardAttach($data);
		}
        return $result;
	}

	/**
	 * Получение номера участка, в рамках задачи 9295
	 */
	function getLpuRegion ($data) {
		$sql = "
				declare
				@AttachLpu_id bigint,
				@ErrCode int,
				@LpuRegion_id bigint,
				@ErrMessage varchar(4000);

			exec xp_PersonAttach
				@Person_id = :Person_id,
				@LpuAttachType_id = :LpuAttachType_id,
				@LpuRegion_id = @LpuRegion_id output,
				@Lpu_id = @AttachLpu_id output;

			select @LpuRegion_id as LpuRegion_id;
		";
		$result = $this->db->query($sql, array('Person_id' => $data['Person_id'], 'LpuAttachType_id' => $data['LpuAttachType_id']));
		if( !is_object($result)) {
			return false;
		}
		$result = $result->result('array');
		return $result;
	}

	/**
	* Получение участка по адресу человека
	*/
	function getLpuRegionByAddress($data){
		$params = array(
			'Person_id' => $data['Person_id'],
			'Lpu_id' => $data['Lpu_id'],
			'LpuRegionType_id' => $data['LpuRegionType_id']
		);
		$queryAddress = "
			select top 1 LR.LpuRegion_id
			from v_LpuRegion LR
			inner join LpuRegionStreet LRS on LRS.LpuRegion_id = LR.LpuRegion_id
			outer apply(
				select A.KLStreet_id, A.Address_House, A.Address_Corpus
				from v_PersonState PS
				inner join Address A on A.Address_id = PS.PAddress_id
				where PS.Person_id = :Person_id

			) as PersonAddress
			where (1=1)
			and LR.Lpu_id = :Lpu_id
			and LR.LpuRegionType_id = :LpuRegionType_id
			and LRS.KLStreet_id = PersonAddress.KLStreet_id
			and dbo.GetHouse(isnull(LRS.LpuRegionStreet_HouseSet,'  '),(LTRIM(RTRIM(PersonAddress.Address_House))+(CASE WHEN PersonAddress.Address_Corpus IS NOT NULL THEN '/'+RTRIM(LTRIM(PersonAddress.Address_Corpus)) ELSE '' end))) = 1
		";
		$result = $this->db->query($queryAddress, $params);
		if( !is_object($result)) {
			return false;
		}
		$result = $result->result('array');
		return $result;
	}

	/**
	 *	Получение списка файлов, прикрепленных к карте
	 */
	function getFilesOnPersonCardAttach($data) {
		$query = "
			select
				pmMediaData_id
				,pmMediaData_ObjectID as PersonCardAttach_id
				,pmMediaData_FileName as name
				,pmMediaData_FilePath as url
				,pmMediaData_FilePath as tmp_name
				,pmMediaData_Comment as sizeinfo
			from
				pmMediaData with(nolock)
			where
				pmMediaData_ObjectID = :pmMediaData_ObjectID
			union
			select
				pmMediaData_id
				,pmMediaData_ObjectID as PersonCardAttach_id
				,pmMediaData_FileName as name
				,pmMediaData_FilePath as url
				,pmMediaData_FilePath as tmp_name
				,pmMediaData_Comment as sizeinfo
			from
				pmMediaData with(nolock)
			where
				pmMediaData_ObjectID = :pmMediaData_ObjectIDPCard
		";
        //echo getDebugSQL($query, array('pmMediaData_ObjectID' => $data['PersonCardAttach_id'],'pmMediaData_ObjectIDPCard' => $data['PersonCard_id']));die;
		$result = $this->db->query($query, array('pmMediaData_ObjectID' => $data['PersonCardAttach_id'],'pmMediaData_ObjectIDPCard' => $data['PersonCard_id']));
        if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 *	Получение номера карты
	 */
	function getPersonCardCode($data)
	{
		$regionsNotFond = array('ufa', 'kareliya', 'astra'); // регионы, для которых не требуется проверка на фондодержание (shorev: Добавил Астрахань в рамках задачи http://redmine.swan.perm.ru/issues/24366)
		$regionNick = $this->getRegionNick();
		if ( (!$regionNick) || (!in_array($regionNick, $regionsNotFond)) ) {
			// проверяем, можем ли мы прикрепить человека к этому ЛПУ по признаку фондододержания
			if ( isset($data['CheckFond']) )
			{
				$sql = "
					select
						Person_Age
					from
						v_PersonState_all Person with(nolock)
						inner join v_Polis Polis with(nolock) on Person.Polis_id = Polis.Polis_id and ( cast(convert(varchar(10), Polis.Polis_begDate, 112) as datetime) < dbo.tzGetDate() and (Polis.Polis_endDate is null or cast(convert(varchar(10), Polis.Polis_endDate, 112) as datetime) > dbo.tzGetDate()))
					where
						Person_id = ?
						and Person_IsBDZ = 1
				";
				$res = $this->db->query($sql, array($data['Person_id']));
				if ( is_object($res) )
				{
					$sel = $res->result('array');
					if ( count($sel) > 0 )
					{
						$age = $sel[0]['Person_Age'];
						/*if ( $age >= 18  )
							$type_filter = ' and lfh.LpuRegionType_id = 1 ';
						else
							$type_filter = ' and lfh.LpuRegionType_id = 2 ';

						 */
						$sql = "
							select
								LpuRegionType_id
							from
								v_LpuPeriodFondHolder with (nolock)
							where
								Lpu_id = :Lpu_id
								and ISNULL(LpuRegionType_id, 1) in (1, 2)
								and LpuPeriodFondHolder_begDate <= dbo.tzGetDate()
								and (LpuPeriodFondHolder_endDate is null or LpuPeriodFondHolder_endDate > dbo.tzGetDate())
						";
						$res = $this->db->query($sql, array('Lpu_id' => $data['Lpu_id']));
						if ( is_object($res) )
						{
							$sel = $res->result('array');

							if ( is_array($sel) && count($sel) > 0 ) {
								$all = false;
								$child = false;
								$old = false;

								foreach ( $sel as $val ) {
									if ( empty($val['LpuRegionType_id']) ) {
										$all = true;
									}
									else if ( $val['LpuRegionType_id'] == 1 ) {
										$old = true;
									}
									else if ( $val['LpuRegionType_id'] == 2 ) {
										$child = true;
									}
								}

								if ( $all === false ) {
									if ( $age >= 18 && !$old && $child ) {
										return array(array('success' => false, 'Error_Msg' => 'Взрослый человек не может быть прикреплен к данному ЛПУ, так как ЛПУ является фондодержателем детского населения.', 'Cancel_Error_Handle' => true));
									}

									if ( $age < 18 && !$child && $old ) {
										return array(array('success' => false, 'Error_Msg' => 'Человек до 18 лет не может быть прикреплен к данному ЛПУ, так как ЛПУ является фондодержателем взрослого населения.', 'Cancel_Error_Handle' => true));
									}

									if ( !($age >= 18 && $old) && !($age < 18 && $child) ) {
										return array(array('success' => false, 'Error_Msg' => 'Человек не может быть прикреплен к данному ЛПУ, так как ЛПУ не является фондодержателем.', 'Cancel_Error_Handle' => true));
									}
								}
							}
							else {
								// ни одного фонда
								return array(array('success' => false, 'Error_Msg' => 'Человек не может быть прикреплен к данному ЛПУ, так как ЛПУ не является фондодержателем.', 'Cancel_Error_Handle' => true));
							}
						}
						else {
							return array(array('success' => false, 'Error_Msg' => 'Ошибка при выполнении запроса к базе данных (проверка фондодержания).', 'Cancel_Error_Handle' => true));
						}
					}
				}
			}
		}


		// Сначала проверяем есть ли предыдущие карты на человека по ЛПУ


		$sql = "
			declare @ObjID bigint;
			exec xp_GenpmID
				@ObjectName = 'PersonCard',
				@Lpu_id = ?,
				@ObjectID = @ObjID output;
			select @ObjID as PersonCard_Code;
		";
		$result = $this->db->query($sql, array($data['Lpu_id']));
		if (is_object($result))
		{
			$personcard_result = $result->result('array');
			$personcard_result[0]['success'] = true;
			return $personcard_result;
			//return $result->result('array');
		}
		else
		{
			return false;
		}


	}

    /**
     * Сохранение прикрепления для формы автоприкрепления
     */
    function SavePersonCardAuto($data)
    {
        $params = array();
        //Получим ФИО пациента и наименование ЛПУ и участка
        $query_pers = "
            select ISNULL(PS.Person_SurName,'') + ' ' + ISNULL(PS.Person_FirName,'') + ' ' + ISNULL(PS.Person_SecName,'') as Person_FIO
            from v_PersonState PS with (nolock)
            where Person_id = :Person_id
        ";
        $query_lpu = "
            select Lpu_Nick
            from v_Lpu with (nolock)
            where Lpu_id = :Lpu_id
        ";
        $query_lpuregion = "
            select (LpuRegion_Name + ' (' + LpuRegionType_Name + ')') as LpuRegion_Name
            from v_LpuRegion with (nolock)
            where LpuRegion_id = :LpuRegion_id
        ";

		$query_lpuregiontype = "
			select LpuRegionType_SysNick
			from v_LpuRegionType with (nolock)
			where LpuRegionType_id = :LpuRegionType_id
		";

		$query_personage = "
			select ISNULL(dbo.Age2(Person_Birthday, dbo.tzGetDate()),0) as Person_Age
			from v_PersonState with (nolock)
			where Person_id = :Person_id
		";

        $params['Person_id'] = $data['Person_id'];
        $params['Lpu_id'] = $data['Lpu_id'];
        $params['LpuRegion_id'] = $data['LpuRegion_id'];
		$params['LpuRegionType_id'] = $data['LpuRegionType_id'];

        $pers_name_resp = $this->db->query($query_pers,$params);
        $lpu_nick_resp = $this->db->query($query_lpu,$params);
        $lpuregion_name_resp = $this->db->query($query_lpuregion,$params);
        $params['LpuRegion_id'] = $data['LpuRegion_Fapid'];
        $lpuregion_Fapname_resp = $this->db->query($query_lpuregion,$params);
        $pers = $pers_name_resp->result('array');
        $lpu = $lpu_nick_resp->result('array');
        $lpuregion = $lpuregion_name_resp->result('array');
        $lpuregionfap = $lpuregion_Fapname_resp->result('array');
        $pers_name = $pers[0]['Person_FIO'];

		$lpuregion_type = $this->db->query($query_lpuregiontype,$params);
		$lpuregion_type = $lpuregion_type->result('array');
		$lpuregion_type_nick = $lpuregion_type[0]['LpuRegionType_SysNick'];
		//var_dump($lpuregion_type);die;
        $lpu_nick = $lpu[0]['Lpu_Nick'];
        $lpuregion_name = $lpuregion[0]['LpuRegion_Name'];

		if(in_array($this->getRegionNick(), array('perm','buryatiya','kareliya','khak','krym','ekb','ufa','penza')))
        //if ($this->getRegionNick() == 'perm')
        {
            if(count($lpuregionfap) > 0 && isset($lpuregionfap[0]['LpuRegion_Name']))
                $lpuregion_name .= ' ФАП - ' . $lpuregionfap[0]['LpuRegion_Name'];
        }

		//----------https://redmine.swan.perm.ru/issues/87348 Очередная костылина. Здесь проверям соответствие участка той МО, к которой прикрепляем.
		$lpuRegion_check = 0;
		$quury_check_lpuRegion = "
			select LpuRegion_id
			from v_LpuRegion with(nolock)
			where Lpu_id = :Lpu_id
			and LpuRegion_id = :LpuRegion_id
		";
		$result_check_lpuRegion = $this->db->query(
			$quury_check_lpuRegion,
			array(
				'Lpu_id' => $data['Lpu_id'],
				'LpuRegion_id' => $data['LpuRegion_id']
			)
		);
		if(is_object($result_check_lpuRegion)){
			$result_check_lpuRegion = $result_check_lpuRegion->result('array');
			if(count($result_check_lpuRegion) > 0)
				$lpuRegion_check = 1;
		}
		if($lpuRegion_check==0){
			$personcard_result[0]['string'] = 'Пациент '.$pers_name.' НЕ ПРИКРЕПЛЕН к ЛПУ '.$lpu_nick.' к участку '.$lpuregion_name.'! Причина - данный участок не относится к выбранной Вами МО.';
			return $personcard_result;
		}
		//---------конец костылины

		$person_age = $this->db->query($query_personage,$params);
		$person_age = $person_age->result('array');
		$person_age = $person_age[0]['Person_Age'];
		if($lpuregion_type_nick == 'ped' && $person_age >= 18){
			$personcard_result[0]['string'] = 'Пациент '.$pers_name.' НЕ ПРИКРЕПЛЕН к ЛПУ '.$lpu_nick.' к участку '.$lpuregion_name.'! Причина - нельзя прикреплять к педиатрическому участку пациентов 18 лет и старше.';
			return $personcard_result;
		}
		else if ($lpuregion_type_nick == 'ter' && $person_age < 18) {
			$personcard_result[0]['string'] = 'Пациент '.$pers_name.' НЕ ПРИКРЕПЛЕН к ЛПУ '.$lpu_nick.' к участку '.$lpuregion_name.'! Причина - нельзя прикреплять к терапевтическому участку пациентов младше 18.';
			return $personcard_result;
		}

        //Сначала проверим, есть ли у этого человека прикрепления
        $query_check = "
            select *
            from v_PersonCard with (nolock)
            where Person_id = :Person_id
            and LpuAttachType_id = 1
        ";
        $personcard_result = array();
        $result_check = $this->db->query($query_check,$params);
        $PersonAmbulatCard_id = null;
        $change_lpu = 0;
        $checkPolisChanged = false;
        $CardCloseCause_new = null;
        if(is_object($result_check)){
            $res_check = $result_check->result('array');
            if ( is_array($res_check) && count($res_check) > 0 && isset($res_check[0]['PersonCard_id']) ){ //Если есть, то вызывает p_PersonCard_upd с новыми параметрами и старым PersonCard_Code
                if($data['LpuRegion_id'] == $res_check[0]['LpuRegion_id'] && $data['LpuRegion_Fapid'] == $res_check[0]['LpuRegion_fapid'])
                {
                	/*if($this->getRegionNick() != 'perm')
                	{
                    	$personcard_result[0]['string'] = 'Пациент '.$pers_name.' НЕ ПРИКРЕПЛЕН к ЛПУ '.$lpu_nick.' к участку '.$lpuregion_name.'! Причина - пациент уже прикреплен к данному участку.';
                    	return $personcard_result;
                	}
                	else {
                		$checkPolisChanged = true;
                	}*/
                    //check_polis
					if($this->getRegionNick() == 'perm' && !empty($res_check[0]['PersonCard_begDate']) && date_format($res_check[0]['PersonCard_begDate'], 'Ymd') == date('Ymd')){
						$personcard_result[0]['string'] = 'Пациент '.$pers_name.' НЕ ПРИКРЕПЛЕН к ЛПУ '.$lpu_nick.' к участку '.$lpuregion_name.'! Причина - новое прикрепление пациента можно добавлять не чаще одного раза в день.';
						return $personcard_result;
					}elseif($this->getRegionNick() != 'perm'){
						$personcard_result[0]['string'] = 'Пациент '.$pers_name.' НЕ ПРИКРЕПЛЕН к ЛПУ '.$lpu_nick.' к участку '.$lpuregion_name.'! Причина - пациент уже прикреплен к данному участку.';
						return $personcard_result;
					}else{
						$checkPolisChanged = true;
					}
                }
                $personCard_Code = $res_check[0]['PersonCard_Code'];
                //АК. Если новое ЛПУ не совпадает со старым, то добавляем новую АК.
                if($res_check[0]['Lpu_id'] != $data['Lpu_id'])
                {
                    //Сначала добавляем новый PersonAmbulatCard
                    $params_PersonAmbulatCard = array();
                    $params_PersonAmbulatCard['PersonAmbulatCard_id'] = null;
                    $params_PersonAmbulatCard['Server_id'] = $data['Server_id'];
                    $params_PersonAmbulatCard['Person_id'] = $data['Person_id'];
                    $params_PersonAmbulatCard['PersonAmbulatCard_Num'] = $this->getPersonCardCode($data);
                    $params_PersonAmbulatCard['PersonAmbulatCard_Num'] = $params_PersonAmbulatCard['PersonAmbulatCard_Num'][0]['PersonCard_Code'];
                    $personCard_Code = $params_PersonAmbulatCard['PersonAmbulatCard_Num'];
                    $params_PersonAmbulatCard['Lpu_id'] = $data['Lpu_id'];
                    $params_PersonAmbulatCard['PersonAmbulatCard_CloseCause'] = null;
                    $params_PersonAmbulatCard['PersonAmbulatCard_endDate'] = null;
                    $params_PersonAmbulatCard['pmUser_id'] = $data['pmUser_id'];
                    $query_PersonAmbulatCard = "
                        declare
                            @Res bigint,
                            @ErrCode int,
                            @time datetime,
                            @ErrMessage varchar(4000);

                        set @Res = :PersonAmbulatCard_id;
                        set @time = (select dbo.tzGetDate());
                        exec p_PersonAmbulatCard_ins
                            @Server_id = :Server_id,
                            @PersonAmbulatCard_id = @Res output,
                            @Person_id = :Person_id,
                            @PersonAmbulatCard_Num = :PersonAmbulatCard_Num,
                            @Lpu_id = :Lpu_id,
                            @PersonAmbulatCard_CloseCause =:PersonAmbulatCard_CloseCause,
                            @PersonAmbulatCard_endDate = :PersonAmbulatCard_endDate,
                            @PersonAmbulatCard_begDate = @time,
                            @pmUser_id = :pmUser_id,
                            @Error_Code = @ErrCode output,
                            @Error_Message = @ErrMessage output;

                        select @Res as PersonAmbulatCard_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
                    ";
                    $result_PersonAmbulatCard = $this->db->query($query_PersonAmbulatCard,$params_PersonAmbulatCard);
                    if(is_object($result_PersonAmbulatCard)){
                        $result_PersonAmbulatCard = $result_PersonAmbulatCard->result('array');
                        $change_lpu = 1;
                        //Теперь добавляем PersonAmbulatCardLocat - движение амбулаторной карты
                        $PersonAmbulatCard_id = $result_PersonAmbulatCard[0]['PersonAmbulatCard_id'];
                        $params_PersonAmbulatCardLocat = array();
                        $params_PersonAmbulatCardLocat['PersonAmbulatCardLocat_id'] = null;
                        $params_PersonAmbulatCardLocat['Server_id'] = $data['Server_id'];
                        $params_PersonAmbulatCardLocat['PersonAmbulatCard_id'] = $PersonAmbulatCard_id;
                        $params_PersonAmbulatCardLocat['AmbulatCardLocatType_id'] = 1;
                        $params_PersonAmbulatCardLocat['MedStaffFact_id'] = null;
                        $params_PersonAmbulatCardLocat['PersonAmbulatCardLocat_begDate'] = date('Y-m-d H:i');
                        $params_PersonAmbulatCardLocat['PersonAmbulatCardLocat_Desc'] = null;
                        $params_PersonAmbulatCardLocat['PersonAmbulatCardLocat_OtherLocat'] = null;
                        $params_PersonAmbulatCardLocat['pmUser_id'] = $data['pmUser_id'];
                        $query_PersonAmbulatCardLocat = "
                            declare
                                @Res bigint,
                                @ErrCode int,
                                @ErrMessage varchar(4000);

                            set @Res = :PersonAmbulatCardLocat_id;
                            exec p_PersonAmbulatCardLocat_ins
                                @Server_id = :Server_id,
                                @PersonAmbulatCardLocat_id = @Res output,
                                @PersonAmbulatCard_id = :PersonAmbulatCard_id,
                                @AmbulatCardLocatType_id = :AmbulatCardLocatType_id,
                                @MedStaffFact_id = :MedStaffFact_id,
                                @PersonAmbulatCardLocat_begDate = :PersonAmbulatCardLocat_begDate,
                                @PersonAmbulatCardLocat_Desc = :PersonAmbulatCardLocat_Desc,
                                @PersonAmbulatCardLocat_OtherLocat =:PersonAmbulatCardLocat_OtherLocat,
                                @pmUser_id = :pmUser_id,
                                @Error_Code = @ErrCode output,
                                @Error_Message = @ErrMessage output;

                            select @Res as PersonAmbulatCardLocat_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
                        ";
                        $result_PersonAmbulatCardLocat = $this->db->query($query_PersonAmbulatCardLocat,$params_PersonAmbulatCardLocat);
                        if(is_object($result_PersonAmbulatCardLocat)){
                            //Добавили движение амбулаторной карты. Тут больше ничего не делаем.
                        }
                        else
                        {
                            $personcard_result[0]['string'] = 'Пациент '.$pers_name.' НЕ ПРИКРЕПЛЕН к ЛПУ '.$lpu_nick.' к участку '.$lpuregion_name.'! Причина - ошибка добавления движения амбулаторной карты.';
                            return $personcard_result;
                        }
                    }
                    else
                    {
                        $personcard_result[0]['string'] = 'Пациент '.$pers_name.' НЕ ПРИКРЕПЛЕН к ЛПУ '.$lpu_nick.' к участку '.$lpuregion_name.'! Причина - ошибка добавления амбулаторной карты.';
                        return $personcard_result;
                    }
                }
                if($checkPolisChanged)
                {
                	$CardCloseCause_new = 8;//9; //По дефолту считаем "Смена основного врача на участке" - точнее, считали до задачи https://redmine.swan.perm.ru/issues/100477. щас считаем "Иное/По требованию МО"
		        	$query_change = "
		        		select PP.PersonPolis_id
		        		from v_PersonPolis PP
		        		where
		        			PP.Person_id = :Person_id
		        		and
		        			PP.PersonPolis_begDT > :begDate
		        	";
		        	$result_change = $this->db->query($query_change, array('Person_id' => $res_check[0]['Person_id'], 'begDate' => $res_check[0]["PersonCard_begDate"]));
		        	if(is_object($result_change)){
		        		$result_change = $result_change->result('array');
		        		if(count($result_change) > 0)
		        		{
		        			$CardCloseCause_new = 10; //Смена действующего полиса
		        		}
		        	}
                }
                $upd_params = array();
                $beg_date = date('Y-m-d H:i:00.000');
                $upd_params['PersonCard_id'] = $res_check[0]["PersonCard_id"];
                $upd_params['Lpu_id'] = $data["Lpu_id"];
                $upd_params['Server_id'] = $data["Server_id"];
                $upd_params['Person_id'] = $data["Person_id"];
                $upd_params['PersonCard_IsAttachCondit'] = (isset($data['IsAttachCondit']) && $data['IsAttachCondit'] == 1)?2:null;
                $upd_params['BegDate'] = $beg_date;
                $upd_params['EndDate'] = null;
                $upd_params['CardCloseCause_id'] = null;
                $upd_params['pmUser_id'] = $data['pmUser_id'];
                $upd_params['PersonCard_Code'] = $personCard_Code;//$res_check[0]["PersonCard_Code"];
                $upd_params['LpuRegion_id'] = $data["LpuRegion_id"];
                $upd_params['LpuRegion_Fapid'] = $data['LpuRegion_Fapid'];
                $upd_params['LpuAttachType_id'] = $res_check[0]["LpuAttachType_id"];
                $upd_params['PersonCardAttach_id'] = $data["PersonCardAttach_id"];
                if($checkPolisChanged)
                {
                	$upd_params['CardCloseCause_id'] = $CardCloseCause_new;
                	//$upd_params['PersonCard_IsAttachCondit'] = 2;
                	//$upd_params['PersonCardAttach_id'] = null;
                }
                $sql = "
						declare
							@Res bigint,
							@ErrCode int,
							@ErrMessage varchar(4000);
						set @Res = :PersonCard_id;
						exec p_PersonCard_upd
							@PersonCard_id = @Res output,
							@Lpu_id = :Lpu_id,
							@Server_id = :Server_id,
							@Person_id = :Person_id,
							@PersonCard_begDate = :BegDate,
							@PersonCard_endDate = :EndDate,
							@PersonCard_Code = :PersonCard_Code,
							@PersonCard_IsAttachCondit = :PersonCard_IsAttachCondit,
							@LpuRegion_id = :LpuRegion_id,
							@LpuRegion_fapid = :LpuRegion_Fapid,
							@LpuAttachType_id = :LpuAttachType_id,
							@CardCloseCause_id = :CardCloseCause_id,
							@PersonCardAttach_id = :PersonCardAttach_id,
							@pmUser_id = :pmUser_id,
							@Error_Code = @ErrCode output,
							@Error_Message = @ErrMessage output;
						select @Res as PersonCard_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
					";
                $result = $this->db->query($sql, $upd_params);

                //echo getDebugSQL($sql, $upd_params);die;
                $sel = $result->result('array');
                if ( strlen($sel[0]['Error_Msg'] ) > 0)
                {
                    if($change_lpu == 1) //Если сменили МО, то появилась новая АК (и движение АК) (описано выше), и нужно связать ее с новым PersonCard_id
                    {
                        $params_PersonAmbulatCardLink = array();
                        $params_PersonAmbulatCardLink['PersonAmbulatCard_id'] = $PersonAmbulatCard_id;
                        $params_PersonAmbulatCardLink['PersonCard_id'] = $sel[0]['PersonCard_id'];
                        $params_PersonAmbulatCardLink['pmUser_id'] = $data['pmUser_id'];
                        $query_PersonAmbulatCardLink = "
                            declare
                                @ErrCode int,
                                @ErrMessage varchar(4000);
                            exec p_PersonAmbulatCardLink_ins
                                @PersonAmbulatCardLink_id = null,
                                @PersonAmbulatCard_id = :PersonAmbulatCard_id,
                                @PersonCard_id = :PersonCard_id,
                                @pmUser_id = :pmUser_id,
                                @Error_Code = @ErrCode output,
                                @Error_Message = @ErrMessage output;
                            select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
                        ";
                        $result_PersonAmbulatCardLink = $this->db->query($query_PersonAmbulatCardLink,$params_PersonAmbulatCardLink);
                        if(!is_object($result_PersonAmbulatCardLink))
                        {
                            $personcard_result[0]['string'] = 'Пациент '.$pers_name.' НЕ ПРИКРЕПЛЕН к ЛПУ '.$lpu_nick.' к участку '.$lpuregion_name.'! Причина - ошибка добавления связи амбулаторной карты с прикреплением.';
                            return $personcard_result;
                        }
                    }
                    $personcard_result[0]['string'] = 'Пациент '.$pers_name.' НЕ ПРИКРЕПЛЕН к ЛПУ '.$lpu_nick.' к участку '.$lpuregion_name.'! Причина - '.$sel[0]['Error_Msg'].'.';
                    return $personcard_result;
                }
                $personcard_result[0]['string'] = 'Пациент '.$pers_name.' успешно прикреплен к ЛПУ '.$lpu_nick.' к участку '.$lpuregion_name;
                if($checkPolisChanged)
                	$personcard_result[0]['string'] .= ' prev_params';
                return $personcard_result;
            }
            else{ //Иначе - добавляем новое прикрепление
                 //Сначала добавляем новый PersonAmbulatCard
                $params_PersonAmbulatCard = array();
                $params_PersonAmbulatCard['PersonAmbulatCard_id'] = null;
                $params_PersonAmbulatCard['Server_id'] = $data['Server_id'];
                $params_PersonAmbulatCard['Person_id'] = $data['Person_id'];
                $params_PersonAmbulatCard['PersonAmbulatCard_Num'] = $this->getPersonCardCode($data);
                $params_PersonAmbulatCard['PersonAmbulatCard_Num'] = $params_PersonAmbulatCard['PersonAmbulatCard_Num'][0]['PersonCard_Code'];
                $personCard_Code = $params_PersonAmbulatCard['PersonAmbulatCard_Num'];
                $params_PersonAmbulatCard['Lpu_id'] = $data['Lpu_id'];
                $params_PersonAmbulatCard['PersonAmbulatCard_CloseCause'] = null;
                $params_PersonAmbulatCard['PersonAmbulatCard_endDate'] = null;
                $params_PersonAmbulatCard['pmUser_id'] = $data['pmUser_id'];
                $query_PersonAmbulatCard = "
                    declare
                        @Res bigint,
                        @ErrCode int,
                        @time datetime,
                        @ErrMessage varchar(4000);

                    set @Res = :PersonAmbulatCard_id;
                    set @time = (select dbo.tzGetDate());
                    exec p_PersonAmbulatCard_ins
                        @Server_id = :Server_id,
                        @PersonAmbulatCard_id = @Res output,
                        @Person_id = :Person_id,
                        @PersonAmbulatCard_Num = :PersonAmbulatCard_Num,
                        @Lpu_id = :Lpu_id,
                        @PersonAmbulatCard_CloseCause =:PersonAmbulatCard_CloseCause,
                        @PersonAmbulatCard_endDate = :PersonAmbulatCard_endDate,
                        @PersonAmbulatCard_begDate = @time,
                        @pmUser_id = :pmUser_id,
                        @Error_Code = @ErrCode output,
                        @Error_Message = @ErrMessage output;

                    select @Res as PersonAmbulatCard_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
                ";
                $result_PersonAmbulatCard = $this->db->query($query_PersonAmbulatCard,$params_PersonAmbulatCard);
                if(is_object($result_PersonAmbulatCard)){
                    $result_PersonAmbulatCard = $result_PersonAmbulatCard->result('array');
                    //Теперь добавляем PersonAmbulatCardLocat - движение амбулаторной карты
                    $PersonAmbulatCard_id = $result_PersonAmbulatCard[0]['PersonAmbulatCard_id'];
                    $params_PersonAmbulatCardLocat = array();
                    $params_PersonAmbulatCardLocat['PersonAmbulatCardLocat_id'] = null;
                    $params_PersonAmbulatCardLocat['Server_id'] = $data['Server_id'];
                    $params_PersonAmbulatCardLocat['PersonAmbulatCard_id'] = $PersonAmbulatCard_id;
                    $params_PersonAmbulatCardLocat['AmbulatCardLocatType_id'] = 1;
                    $params_PersonAmbulatCardLocat['MedStaffFact_id'] = null;
                    $params_PersonAmbulatCardLocat['PersonAmbulatCardLocat_begDate'] = date('Y-m-d H:i');
                    $params_PersonAmbulatCardLocat['PersonAmbulatCardLocat_Desc'] = null;
                    $params_PersonAmbulatCardLocat['PersonAmbulatCardLocat_OtherLocat'] = null;
                    $params_PersonAmbulatCardLocat['pmUser_id'] = $data['pmUser_id'];
                    $query_PersonAmbulatCardLocat = "
                        declare
                            @Res bigint,
                            @ErrCode int,
                            @ErrMessage varchar(4000);

                        set @Res = :PersonAmbulatCardLocat_id;
                        exec p_PersonAmbulatCardLocat_ins
                        @Server_id = :Server_id,
                        @PersonAmbulatCardLocat_id = @Res output,
                        @PersonAmbulatCard_id = :PersonAmbulatCard_id,
                        @AmbulatCardLocatType_id = :AmbulatCardLocatType_id,
                        @MedStaffFact_id = :MedStaffFact_id,
                        @PersonAmbulatCardLocat_begDate = :PersonAmbulatCardLocat_begDate,
                        @PersonAmbulatCardLocat_Desc = :PersonAmbulatCardLocat_Desc,
                        @PersonAmbulatCardLocat_OtherLocat =:PersonAmbulatCardLocat_OtherLocat,
                        @pmUser_id = :pmUser_id,
                        @Error_Code = @ErrCode output,
                        @Error_Message = @ErrMessage output;

                        select @Res as PersonAmbulatCardLocat_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
                    ";
                    $result_PersonAmbulatCardLocat = $this->db->query($query_PersonAmbulatCardLocat,$params_PersonAmbulatCardLocat);
                    if(is_object($result_PersonAmbulatCardLocat)){
                        //Добавили движение амбулаторной карты. Тут больше ничего не делаем.
                    }
                    else
                    {
                        $personcard_result[0]['string'] = 'Пациент '.$pers_name.' НЕ ПРИКРЕПЛЕН к ЛПУ '.$lpu_nick.' к участку '.$lpuregion_name.'! Причина - ошибка добавления движения амбулаторной карты.';
                        return $personcard_result;
                    }
                }
                else
                {
                    $personcard_result[0]['string'] = 'Пациент '.$pers_name.' НЕ ПРИКРЕПЛЕН к ЛПУ '.$lpu_nick.' к участку '.$lpuregion_name.'! Причина - ошибка добавления амбулаторной карты.';
                    return $personcard_result;
                }
                $beg_date = date('Y-m-d H:i:00.000');
                $ins_params['Lpu_id'] = $data["Lpu_id"];
                $ins_params['Server_id'] = $data["Server_id"];
                $ins_params['Person_id'] = $data["Person_id"];
                $ins_params['PersonCard_IsAttachCondit'] = (isset($data['IsAttachCondit']) && $data['IsAttachCondit'] == 1)?2:null;
                $ins_params['PersonCard_begDate'] = $beg_date;
                $ins_params['PersonCard_Code'] = $personCard_Code;
                $ins_params['EndDate'] = null;
                $ins_params['pmUser_id'] = $data['pmUser_id'];
                $ins_params['LpuRegion_id'] = $data["LpuRegion_id"];
                $ins_params['LpuRegion_Fapid'] = $data['LpuRegion_Fapid'];
                $ins_params['PersonCardAttach_id'] = $data["PersonCardAttach_id"];
                $sql = "
                    declare
                        @Res bigint,
                        @ErrCode int,
                        @ErrMessage varchar(4000);
                    set @Res = null;
                    exec p_PersonCard_ins
                        @PersonCard_id = @Res output,
                        @Lpu_id = :Lpu_id,
                        @Server_id = :Server_id,
                        @Person_id = :Person_id,
                        @PersonCard_begDate = :PersonCard_begDate,
                        @PersonCard_Code = :PersonCard_Code,
                        @PersonCard_IsAttachCondit = :PersonCard_IsAttachCondit,
                        @LpuRegion_id = :LpuRegion_id,
                        @LpuRegion_fapid = :LpuRegion_Fapid,
                        @LpuAttachType_id = 1,
                        @CardCloseCause_id = null,
                        @PersonCardAttach_id = :PersonCardAttach_id,
                        @pmUser_id = :pmUser_id,
                        @Error_Code = @ErrCode output,
                        @Error_Message = @ErrMessage output;
                    select @Res as PersonCard_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
                ";
                $result = $this->db->query($sql, $ins_params);
                $sel = $result->result('array');
                if ( strlen($sel[0]['Error_Msg'] ) > 0)
                {
                    $personcard_result[0]['string'] = 'Пациент '.$pers_name.' НЕ ПРИКРЕПЛЕН к ЛПУ '.$lpu_nick.' к участку '.$lpuregion_name.'! Причина - '.$sel[0]['Error_Msg'].'.';
                    return $personcard_result;
                }
                $params_PersonAmbulatCardLink = array();
                $params_PersonAmbulatCardLink['PersonAmbulatCard_id'] = $PersonAmbulatCard_id;
                $params_PersonAmbulatCardLink['PersonCard_id'] = $sel[0]['PersonCard_id'];
                $params_PersonAmbulatCardLink['pmUser_id'] = $data['pmUser_id'];
                $query_PersonAmbulatCardLink = "
                            declare
                                @ErrCode int,
                                @ErrMessage varchar(4000);
                            exec p_PersonAmbulatCardLink_ins
                                @PersonAmbulatCardLink_id = null,
                                @PersonAmbulatCard_id = :PersonAmbulatCard_id,
                                @PersonCard_id = :PersonCard_id,
                                @pmUser_id = :pmUser_id,
                                @Error_Code = @ErrCode output,
                                @Error_Message = @ErrMessage output;
                            select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
                        ";
                $result_PersonAmbulatCardLink = $this->db->query($query_PersonAmbulatCardLink,$params_PersonAmbulatCardLink);
                if(!is_object($result_PersonAmbulatCardLink))
                {
                    $personcard_result[0]['string'] = 'Пациент '.$pers_name.' НЕ ПРИКРЕПЛЕН к ЛПУ '.$lpu_nick.' к участку '.$lpuregion_name.'! Причина - ошибка добавления связи амбулаторной карты с прикреплением.';
                    return $personcard_result;
                }
                    $personcard_result[0]['string'] = 'Пациент '.$pers_name.' успешно прикреплен к ЛПУ '.$lpu_nick.' к участку '.$lpuregion_name;
                    return $personcard_result;
            }
        }
        else{
            $personcard_result[0]['string'] = 'Пациент '.$pers_name.' НЕ ПРИКРЕПЛЕН к ЛПУ '.$lpu_nick.' к участку '.$lpuregion_name.'! Причина - ошибка проверки наличия прикрепления у пациента.';
            return $personcard_result;
        }
    }

    /**
	 *	Проверка номера карты
	 */
	function checkPersonCardCode($data)
	{
		$sql = "
			SELECT
				CASE WHEN count(*) = 0
					THEN 'true'
					ELSE 'false'
				END as chck
			FROM v_PersonCard with (nolock)
			WHERE
				Lpu_id = ?
				and PersonCard_Code = ?
				and PersonCard_id <> ?
		";
        $result = $this->db->query($sql, array($data['Lpu_id'], $data['PersonCard_Code'], $data['PersonCard_id']));
        if (is_object($result))
        {
            return $result->result('array');
        }
        else
        {
        	return false;
        }
	}

	/**
	 *	Удаление карты
	 */
	function deletePersonCard($data, $api=false)
	{
        $godMode = 0;
        if(isSuperadmin() || isLpuAdmin() || (isset($data['isLastAttach']) && $data['isLastAttach']==2)){
            $godMode = 1;
		}
		if (!$api && $godMode == 0 )
		{
			$sql = "
				select
					case when (convert(varchar(10),cast(PersonCard_insDT as datetime),104)) = (convert(varchar(10),cast(dbo.tzGetDate() as datetime),104)) then 1 else 0 end as IsToday
				from
					PersonCard with(nolock)
				where
					PersonCard_id = ?
			";
			$result = $this->db->query($sql, array($data['PersonCard_id']));
			if ( is_object( $result ) ) {
				$sel = $result->result('array');
				if ( !(count($sel) > 0) || $sel[0]['IsToday'] != 1 )
					return false;
			}
			else
				return false;
		}
		// ему можно все
		$god = "";
		if ( $api || $godMode == 1 ){
			$god = "
				@del_GodMode = 1,
			";
		}

		$sql = "
			declare @Err_Msg varchar(1000), @Err_Code int
			exec p_PersonCard_del
				@PersonCard_id = ?,
				" . $god . "
				@Error_Code = @Err_Code output,
				@Error_Message = @Err_Msg output
			select @Err_Code as Error_Code, @Err_Msg as Error_Msg;
		";
		//echo getDebugSQL($sql, array($data['PersonCard_id']));die();
		$result = $this->db->query($sql, array($data['PersonCard_id']));

		if ( is_object($result) )
			return $result->result('array');
		else
			return false;
	}

	/**
	 *	Удаление карты (ДМС)
	 */
	function deleteDmsPersonCard($data)
	{
		/*$sql = "
			select
				PersonCard_id
			from
				v_PersonCard with(nolock)
			where
				Person_id = (select top 1 Person_id from v_PersonCard with(nolock) where PersonCard_id = ?)
				and LpuAttachType_id = 1
				and PersonCard_endDate is null
				and cast(convert(varchar(10), PersonCard_begDate, 112) as datetime) = cast(convert(varchar(10), dbo.tzGetDate(), 112) as datetime)
		";
		$result = $this->db->query($sql, array($data['PersonCard_id']));
		if ( is_object( $result ) ) {
			$sel = $result->result('array');
			if ( count($sel) > 0 )
			{
				// удаляем основное прикрепление
				$sql = "
					declare @Err_Msg varchar(1000), @Err_Code int
					exec p_PersonCard_del
						@PersonCard_id = ?,
						@Error_Code = @Err_Code output,
						@Error_Message = @Err_Msg output
					select @Err_Code as Error_Code, @Err_Msg as Error_Msg;
				";

				$result = $this->db->query($sql, array($sel[0]['PersonCard_id']));
			}
		}
		else
			return false;
		*/
		$sql = "
			declare @Err_Msg varchar(1000), @Err_Code int
			exec p_PersonCard_del
				@PersonCard_id = ?,
				@Error_Code = @Err_Code output,
				@Error_Message = @Err_Msg output
			select @Err_Code as Error_Code, @Err_Msg as Error_Msg;
		";
		//echo getDebugSQL($sql, array($data['PersonCard_id']));
		$result = $this->db->query($sql, array($data['PersonCard_id']));

		if ( is_object($result) )
			return $result->result('array');
		else
			return false;
	}

	/**
	 *	Получение данных для журнала движения
	 *
	 *	Рефакторинг: 2013-10-10, задача https://redmine.swan.perm.ru/issues/21183
	 */
	function getPersonCardStateGrid($data) {
		$filters = array();
		$queryParams = array();

		$filters[] = "pc.Lpu_id = @Lpu_id";

		$queryParams['begDate'] = $data['Period'][0];
		$queryParams['endDate'] = $data['Period'][1];
		$queryParams['FromLpu_id'] = (!empty($data['FromLpu_id']) && $data['FromLpu_id'] > 0 ? $data['FromLpu_id'] : NULL);
		$queryParams['Lpu_id'] = $data['Lpu_id'];
		$queryParams['LpuAttachType_id'] = (!empty($data['LpuAttachType_id']) ? $data['LpuAttachType_id'] : NULL);
		$queryParams['LpuRegion_id'] = (!empty($data['LpuRegion_id']) && $data['LpuRegion_id'] > 0 ? $data['LpuRegion_id'] : NULL);
		$queryParams['LpuRegionType_id'] = (!empty($data['LpuRegionType_id']) ? $data['LpuRegionType_id'] : NULL);
		$queryParams['ToLpu_id'] = (!empty($data['ToLpu_id']) && $data['ToLpu_id'] > 0 ? $data['ToLpu_id'] : NULL);

		if ( !empty($data['LpuRegionType_id']) && is_numeric($data['LpuRegionType_id']) ) {
			$filters[] = "lr.LpuRegionType_id = @LpuRegionType_id";
		}

		if ( !empty($data['LpuRegion_id']) ) {
			if ( $data['LpuRegion_id'] == -1 ) {
				$filters[] = "lr.LpuRegion_id is null";
			}
			else {
				$filters[] = "lr.LpuRegion_id = @LpuRegion_id";
			}
		}

		if ( !empty($data['LpuAttachType_id']) ) {
			$filters[] = "pc.LpuAttachType_id = @LpuAttachType_id";
		}

		if ( !empty($queryParams['begDate']) ) {
			$filters[] = "(pc.PersonCard_begDate is null or cast(pc.PersonCard_begDate as date) <= @endDate)";
		}

		if ( !empty($queryParams['endDate']) ) {
			$filters[] = "(pc.PersonCard_endDate is null or cast(pc.PersonCard_endDate as date) >= @begDate)";
		}

		$select_part = "
			CASE WHEN lr.LpuRegion_Name is null THEN 'Без участка' ELSE lr.LpuRegion_Name END as LpuRegion_Name,
            CASE WHEN lr.LpuRegionType_Name is null THEN 'Без участка' ELSE lr.LpuRegionType_Name END as LpuRegionType_Name,
			lr.LpuRegion_id,
			convert(varchar(10), cast(@begDate as datetime), 104) as StartDate,
			convert(varchar(10), cast(@endDate as datetime), 104) as EndDate,
			--количество на начало периода
			COUNT(distinct CASE WHEN cast(pc.PersonCard_begDate as date) < @begDate
				and (spc.PersonCard_endDate is null or cast(spc.PersonCard_endDate as date) >= @begDate)
				and ((pc.LpuRegion_id is null and lr.LpuRegion_id is null)
					or (pc.LpuRegion_id is not null and lr.LpuRegion_id is not null)
				) THEN pc.PersonCard_id END
			) as BegCount,
			--количество на начало периода из БДЗ с действующим полисом
			COUNT(distinct CASE WHEN cast(pc.PersonCard_begDate as date) < @begDate
				and (spc.PersonCard_endDate is null or cast(spc.PersonCard_endDate as date) >= @begDate)
				and ((pc.LpuRegion_id is null and lr.LpuRegion_id is null)
					or (pc.LpuRegion_id is not null and lr.LpuRegion_id is not null)
				)
				and PolisBeg.Polis_id is not null THEN pc.PersonCard_id END
			) as BegCountBDZ,
			--количество на начало периода не из БДЗ
			COUNT(distinct CASE WHEN cast(pc.PersonCard_begDate as date) < @begDate
				and (spc.PersonCard_endDate is null or cast(spc.PersonCard_endDate as date) >= @begDate)
				and ((pc.LpuRegion_id is null and lr.LpuRegion_id is null)
					or (pc.LpuRegion_id is not null and lr.LpuRegion_id is not null)
				)
				and PolisBeg.Polis_id is null THEN pc.PersonCard_id END
			) as BegCountNotInBDZ,
			--количество на конец периода
			COUNT(distinct CASE WHEN cast(pc.PersonCard_begDate as date) <= @endDate
				and (spc.PersonCard_endDate is null or cast(spc.PersonCard_endDate as date) > @endDate)
				and ((pc.LpuRegion_id is null and lr.LpuRegion_id is null)
					or (pc.LpuRegion_id is not null and lr.LpuRegion_id is not null)
				) THEN pc.PersonCard_id END
			) as EndCount,
			--количество на конец периода из БДЗ с действующим полисом
			COUNT(distinct CASE WHEN cast(pc.PersonCard_begDate as date) <= @endDate
				and (spc.PersonCard_endDate is null or cast(spc.PersonCard_endDate as date) > @endDate)
				and ((pc.LpuRegion_id is null and lr.LpuRegion_id is null)
					or (pc.LpuRegion_id is not null and lr.LpuRegion_id is not null)
				)
				and PolisEnd.Polis_id is not null THEN pc.PersonCard_id END
			) as EndCountBDZ,
			--количество на конец периода не из БДЗ
			COUNT(distinct CASE WHEN cast(pc.PersonCard_begDate as date) <= @endDate
				and (spc.PersonCard_endDate is null or cast(spc.PersonCard_endDate as date) > @endDate)
				and ((pc.LpuRegion_id is null and lr.LpuRegion_id is null)
					or (pc.LpuRegion_id is not null and lr.LpuRegion_id is not null)
				)
				and PolisEnd.Polis_id is null THEN pc.PersonCard_id END
			) as EndCountNotInBDZ
		";

		$addit_filter = "";

		if ( isset($data['FromLpu_id']) && (int)$data['FromLpu_id'] > 0 ) {
			$addit_filter .= " and LastCard.Lpu_id is not NULL and LastCard.Lpu_id = @FromLpu_id ";
		}

		if ( isset($data['ToLpu_id']) && (int)$data['ToLpu_id'] > 0 ) {
			$addit_filter .= " and NextCard.Lpu_id is not NULL and NextCard.Lpu_id = @ToLpu_id ";
		}

		if ( $data['LpuMotion_id'] == 2 ) {
			$addit_filter .= " and LastCard.Lpu_id is not NULL and LastCard.Lpu_id != pc.Lpu_id ";
		}
		else if ( $data['LpuMotion_id'] == 3 ) {
			$addit_filter .= " and LastCard.Lpu_id is not NULL and LastCard.Lpu_id = pc.Lpu_id ";
		}

		/*if ( $data['ToLpu_id'] && (int)$data['ToLpu_id'] > 0 ) {
			$select_part = "
				CASE WHEN lr.LpuRegion_Name is null THEN 'Без участка' ELSE lr.LpuRegion_Name END as LpuRegion_Name,
				CASE WHEN lr.LpuRegionType_Name is null THEN 'Без участка' ELSE lr.LpuRegionType_Name END as LpuRegionType_Name,
				lr.LpuRegion_id,
				convert(varchar(10), cast(@begDate as datetime), 104) as StartDate,
				convert(varchar(10), cast(@endDate as datetime), 104) as EndDate,
				--количество на начало периода
				0 as BegCount,
				--количество на начало периода из БДЗ с действующим полисом
				0 as BegCountBDZ,
				0 as BegCountNotInBDZ,
				--количество на конец периода
				0 as EndCount,
				--количество на конец периода из БДЗ с действующим полисом
				0 as EndCountBDZ,
				0 as EndCountNotInBDZ
			";
		}*/

		$sql = "
			declare
				 @begDate datetime = :begDate
				,@endDate datetime = :endDate
				,@FromLpu_id bigint = :FromLpu_id
				,@Lpu_id bigint = :Lpu_id
				,@LpuAttachType_id bigint = :LpuAttachType_id
				,@LpuRegion_id bigint = :LpuRegion_id
				,@LpuRegionType_id bigint = :LpuRegionType_id
				,@ToLpu_id bigint = :ToLpu_id;

			with SelLpuRegions(LpuRegion_id, LpuRegionType_id, LpuRegion_Name, LpuRegionType_Name) as (
				select
					lr.LpuRegion_id,
					lr.LpuRegionType_id,
					rtrim(lr.LpuRegion_Name) as LpuRegion_Name,
					rtrim(lrt.LpuRegionType_Name) as LpuRegionType_Name
				from
					LpuRegion lr with (nolock)
					left join LpuRegionType lrt with (nolock) on lr.LpuRegionType_id = lrt.LpuRegionType_id
				where
					Lpu_id = @Lpu_id
			),
			SelPersonCards(person_id,Lpu_id,Server_id,LpuAttachType_id,LpuRegion_id,Personcard_id,PersonCard_begDate,PersonCard_endDate) as (
				select
					pc.Person_id,
					pc.Lpu_id,
					pc.Server_id,
					pc.LpuAttachType_id,
					pc.LpuRegion_id,
					pc.PersonCard_id,
					pc.PersonCard_begDate,
					pc.PersonCard_endDate
				from v_PersonCard_all pc with (nolock)
					left join v_LpuRegion lr with(nolock) on lr.LpuRegion_id = pc.LpuRegion_id
				" . ImplodeWhere($filters) . "
			)

			select
				{$select_part}
			from
				PersonCard pc with (nolock)
				inner join v_PersonState ps with (nolock) on ps.Person_id = pc.Person_id
				outer apply (
					Select top 1
						Polis.Polis_id
					from
						v_Person_all Person with (nolock)
						left join v_Polis Polis with (nolock) on Person.Polis_id = Polis.Polis_id
					where
						Person.Person_id = pc.Person_id
						--and Person.Server_pid = 0
						and cast(Polis.Polis_begDate as date) < @begDate
						and ( Polis.Polis_endDate is null or (cast(Polis.Polis_endDate as date) >= @begDate) )
				) as PolisBeg
				outer apply (
					Select top 1
						Polis.Polis_id
					from
						v_Person_all Person with (nolock)
						left join v_Polis Polis with (nolock) on Person.Polis_id = Polis.Polis_id
					where
						Person.Person_id = pc.Person_id
						--and Person.Server_pid = 0
						and cast(Polis.Polis_begDate as date) <= @endDate
						and ( Polis.Polis_endDate is null or (cast(Polis.Polis_endDate as date) > @endDate) )
				) as PolisEnd
				outer apply (
					select top 1
						pclast.PersonCard_id,
						pclast.Lpu_id
					from
						PersonCard pclast with (nolock)
					where
						pc.Person_id = pclast.Person_id and
						pclast.PersonCard_id < pc.PersonCard_id and
						pclast.LpuAttachType_id = pc.LpuAttachType_id
					order by
						pclast.PersonCard_id desc
				) as LastCard
				outer apply (
					select top 1
						pclast.PersonCard_id,
						pclast.Lpu_id,
						pclast.PersonCard_begDate
					from
						PersonCard pclast with (nolock)
					where
						pc.Person_id = pclast.Person_id and
						pclast.PersonCard_id >= pc.PersonCard_id and
						pclast.LpuAttachType_id = pc.LpuAttachType_id
					order by
						pclast.PersonCard_id asc
				) as NextCard
				inner join SelPersonCards spc with (nolock) on spc.PersonCard_id = pc.PersonCard_id
				left join SelLpuRegions lr with (nolock) on pc.LpuRegion_id = lr.LpuRegion_id
			where
				(1=1) {$addit_filter}
			group by
				lr.LpuRegion_id,
				lr.LpuRegion_Name,
				lr.LpuRegionType_Name
		";
		//die(getDebugSQL($sql, $queryParams));
		$result = $this->db->query($sql, $queryParams);

		if ( !is_object($result) ) {
			return false;
		}

		$ret = $result->result('array');

		$itog = array(
			'LpuRegion_Name' =>'<b>Итог:</b>',
			'LpuRegionType_Name' => '',
			'LpuRegion_id' => '',
			'StartDate' => '',
			'EndDate' => '',
			'BegCount' => 0,
			'BegCountBDZ' => 0,
			'BegCountNotInBDZ' => 0,
			'EndCount' => 0,
			'EndCountBDZ' => 0,
			'EndCountNotInBDZ' => 0
		);

		foreach ( $ret as $sel_row ) {
			$itog['BegCount'] += $sel_row['BegCount'];
			$itog['BegCountBDZ'] += $sel_row['BegCountBDZ'];
			$itog['BegCountNotInBDZ'] += $sel_row['BegCountNotInBDZ'];
			$itog['EndCount'] += $sel_row['EndCount'];
			$itog['EndCountBDZ'] += $sel_row['EndCountBDZ'];
			$itog['EndCountNotInBDZ'] += $sel_row['EndCountNotInBDZ'];
		}

		$itog['BegCount'] = '<b>' . $itog['BegCount'] . '</b>';
		$itog['BegCountBDZ'] = '<b>' . $itog['BegCountBDZ'] . '</b>';
		$itog['BegCountNotInBDZ'] = '<b>' . $itog['BegCountNotInBDZ'] . '</b>';
		$itog['EndCount'] =  '<b>' . $itog['EndCount'] . '</b>';
		$itog['EndCountBDZ'] =  '<b>' . $itog['EndCountBDZ'] . '</b>';
		$itog['EndCountNotInBDZ'] =  '<b>' . $itog['EndCountNotInBDZ'] . '</b>';

		if ( count($ret) > 0 ) {
			$ret[] = $itog;
		}

		return $ret;
	}

	/**
	 *	Получение каких-то данных
	 */
	function getPersonCardDetailList($data)
	{

		$queryParams['begDate'] = ArrayVal($data,'StartDate');
		$queryParams['endDate'] = ArrayVal($data,'EndDate');
		$queryParams['Lpu_id'] = $data['session']['lpu_id'];

		if (ArrayVal($data,'LpuRegion_id')!= '' ) {
			$queryParams['LpuRegion_id'] = ArrayVal($data, 'LpuRegion_id');
 			$lpu_region_filter = " and pc.LpuRegion_id = :LpuRegion_id ";
		}
		else
		{
			$lpu_region_filter = " and pc.LpuRegion_id is null and pc.Lpu_id = :Lpu_id ";
		}



		$dates_filter = "";
		switch ( $data['mode'] )
		{
			case 'BegCount':
				$dates_filter .= "
					cast(convert(varchar(10), pc.PersonCard_begDate, 112) as datetime) < :begDate
					and (pc.PersonCard_endDate is null or pc.PersonCard_endDate >= :begDate)
				";
			break;
			case 'BegCountBDZ':
				$dates_filter .= "
					cast(convert(varchar(10), pc.PersonCard_begDate, 112) as datetime) < :begDate
					and (pc.PersonCard_endDate is null or pc.PersonCard_endDate >= :begDate)
				";
			break;
			case 'EndCount':
				$dates_filter .= "
					cast(convert(varchar(10), pc.PersonCard_begDate, 112) as datetime) <= :endDate
					and (pc.PersonCard_endDate is null or cast(convert(varchar(10), pc.PersonCard_endDate, 112) as datetime) > :endDate)
				";
			break;
			case 'AttachCount':
				$dates_filter .= "
					cast(convert(varchar(10), pc.PersonCard_begDate, 112) as datetime) between :begDate and :endDate
				";
			break;
			case 'DettachCount':
				$dates_filter .= "
					cast(convert(varchar(10), pc.PersonCard_endDate, 112) as datetime) between :begDate and :endDate
				";
			break;
		}

		$sql = "SELECT
			pc.PersonCard_Code,
			pc.PersonCard_id,
			pc.Person_id,
			pc.Server_id,
			rtrim(pc.Person_SurName) as Person_Surname,
			rtrim(pc.Person_FirName) as Person_Firname,
			rtrim(pc.Person_SecName) as Person_Secname,
			convert(varchar,cast(pc.Person_BirthDay as datetime),104) as Person_BirthDay,
			convert(varchar,cast(pc.PersonCard_begDate as datetime),104) as PersonCard_begDate,
			convert(varchar,cast(pc.PersonCard_endDate as datetime),104) as PersonCard_endDate,
			pc.LpuRegionType_Name,
			pc.LpuRegion_Name,
			isnull(ccc.CardCloseCause_Name, '') as CardCloseCause_Name,
			case when isnull(pc.PersonCard_IsAttachCondit, 1) = 1 then 'false' else 'true' end as PersonCard_IsAttachCondit,
			isnull(pc1.LpuRegion_Name, '') as ActiveLpuRegion_Name,
			isnull(rtrim(lp.Lpu_Nick), '') as ActiveLpu_Nick,
			isnull(rtrim(Address.Address_Address), '') as PAddress_Address
		FROM
			v_PersonCard_All pc with (nolock)
			left join CardCloseCause ccc with (nolock) on ccc.CardCloseCause_id = pc.CardCloseCause_id
			left join v_PersonCard pc1 with (nolock) on pc.Person_id=pc1.Person_id and pc.LpuAttachType_id=pc1.LpuAttachType_id
			left join v_Lpu lp with (nolock) on pc1.Lpu_id=lp.Lpu_id
			left join PersonState ps with (nolock) on ps.Person_id = pc.Person_id
			left join Address with (nolock) on ps.PAddress_id = Address.Address_id

		WHERE
			{$dates_filter}
			{$lpu_region_filter}
		ORDER BY
			pc.Person_SurName, pc.Person_FirName, pc.Person_SecName
		";

		$res=$this->db->query($sql, $queryParams);
		if ( is_object($res) )
			return $res->result('array');
		else
			return false;
	}

	/**
	 * Получение количества людей прикрепленных к ЛПУ
	 */
	function getPersonCardCount($data) {
		$sql = "
			SELECT
				count(PC.PersonCard_id) as PersonCard_Count
			FROM
				v_PersonCard PC with (nolock)
				inner join v_PersonState PS with(nolock) on PS.Person_id = PC.Person_id
			WHERE
				PC.Lpu_id = ?
				and (PersonCard_endDate is null or cast(PersonCard_endDate as datetime) >= cast(convert(char(10), dbo.tzGetDate(), 112) as datetime)) and LpuAttachType_id = 1
		";
		$res=$this->db->query($sql, array($data['Lpu_id']));
		if ( is_object($res) )
			return $res->result('array');
		else
			return false;
	}

	/**
	 *	Получение данных для экспорта амбулаторных карт в DBF
	 */
	function ExportPCToDBF($data) {
		$fields = '';
		$joins = '';
		if(getRegionNick() == 'ufa'){
			$fields .= ',att.LpuAttachType_Name as AttachType';
			$joins .= 'left join v_LpuAttachType att with (nolock) on att.LpuAttachType_id = pc.LpuAttachType_id';
			$fields .= " ,case when pc.PersonCard_IsAttachCondit = 2 then 'Да' else 'Нет' end as Condit";
		}
		$sql = "
			select
				pc.PersonCardState_Code,
				Person_Surname,
				Person_Firname,
				Person_Secname,
				cast(datepart(yyyy, Person_BirthDay) as varchar(4)) + Right('0'+cast(datepart(mm,Person_BirthDay) as varchar(2)),2) + Right('0'+cast(datepart(dd,Person_BirthDay) as varchar(2)),2) as Person_BirthDay,
				rtrim(a.Address_Address) as UAddress_Address,
				rtrim(a1.Address_Address) as PAddress_Address,
				case when pol.PolisType_id = 4 then '' else pol.Polis_Ser end as Polis_Ser,
				case when pol.PolisType_id = 4 then p.Person_EdNum else pol.Polis_Num end as Polis_Num,
				OrgSmo_Name,
				LpuRegion_Name,
				cast(datepart(yyyy, PersonCardState_begDate) as varchar(4)) + Right('0'+cast(datepart(mm,PersonCardState_begDate) as varchar(2)),2) + Right('0'+cast(datepart(dd,PersonCardState_begDate) as varchar(2)),2) as PersonCard_begDate,
				cast(datepart(yyyy, PersonCardState_begDate) as varchar(4)) + Right('0'+cast(datepart(mm,PersonCardState_begDate) as varchar(2)),2) + Right('0'+cast(datepart(dd,PersonCardState_begDate) as varchar(2)),2) as PersonCard_endDate,
				ss.SocStatus_Code,
				ss.SocStatus_Name,
				case when p.Person_IsBDZ = 1 then 'Да' else 'Нет' end as BDZ
				{$fields}
			from PersonCardState pc with (nolock)
			inner join v_PersonState_all p with (nolock) on p.person_id=pc.person_id
			left join SocStatus ss with (nolock) on ss.SocStatus_id = p.SocStatus_id
			left join Polis pol with (nolock) on p.Polis_id = pol.Polis_id
			left join v_OrgSmo os with (nolock) on os.OrgSMO_id = pol.OrgSmo_id
			left join [address] a with (nolock) on p.uaddress_id=a.address_id
			left join [address] a1 with (nolock) on p.paddress_id=a1.address_id
			left join v_lpuregion lr with (nolock) on lr.LpuRegion_id=pc.LpuRegion_id
			{$joins}
			where pc.Lpu_id = :Lpu_id
			order by
				Person_Surname,
				Person_Firname,
				Person_Secname
		";
		$queryParams = array();
		$queryParams['Lpu_id'] = $data['Lpu_id'];

		$result = $this->db->query($sql, $queryParams);

		if (is_object($result))
		{
			return $result->result('array');
		}
		else
		{
			return false;
		}
	}

	/**
	 * Поиск участка прикрепления человека по указанному в картотеке участку в заданной ЛПУ
	 */
	function FindAddressRegionsIDByPersonCard( $Person_id, $Lpu_id )
	{
		$arRegions = array();

		$sql = "
			select
				LpuRegion_id
			from v_PersonCard with(nolock)
			where
				Person_id = :Person_id
				and Lpu_id = :Lpu_id
				and LpuRegion_id is not null";

		$queryParams = array(
			'Person_id' => $Person_id,
			'Lpu_id' => $Lpu_id
		);
		$result = $this->db->query($sql, $queryParams);

		if (is_object($result))
		{
			$res = $result->result('array');
			foreach($res as $row) {
				if ( isset($row['LpuRegion_id']) )
					$arRegions[] = $row['LpuRegion_id'];
			}
		}
		return $arRegions;
	}

	/**
	 * Поиск участка прикрепления человека по адресу проживания в заданной ЛПУ
	 */
	function FindAddressRegionsIDByAddress( $sStreet, $sHouse, $Lpu_id ){
		$arRegions = array();

		$sql = "
			select
				LpuRegionStreet_HouseSet,
				LpuRegion_id
			from LpuRegionStreet with(nolock)
			where
				LpuRegion_id in ( select LpuRegion_id from v_LpuRegion with(nolock) where Lpu_id = :Lpu_id )
				and (KLStreet_id = :Street or :Street = '')";

		$queryParams = array(
			'Lpu_id' => $Lpu_id,
			'Street' => $sStreet
		);

		$result = $this->db->query($sql, $queryParams);

		if (is_object($result))
		{
			$res = $result->result('array');
			foreach($res as $row) {
				if( ( $sHouse == '' ) || HouseMatchRange( trim( $sHouse ), trim( $row['LpuRegionStreet_HouseSet'] ) ) )
					$arRegions[] = $row['LpuRegion_id'];
			}
		}
		return $arRegions;
	}

	/**
	 * Получение массива номеров участко, которые обслуживают человека с заданным адресом в данном ЛПУ
	 */
	function getPersonRegionList($Person_id, $Lpu_id, $KLStreet_id, $Address_House)
	{
		$Region = array();
		$arRegions = $this->FindAddressRegionsIDByPersonCard($Person_id, $Lpu_id );

		If (count($arRegions) == 0){
			$arRegions = $this->FindAddressRegionsIDByAddress( $KLStreet_id, $Address_House, $Lpu_id );
		}

		If (count($arRegions) > 0) {
			$sql = "
				select
					LR.LpuRegion_Name
				from v_LpuRegion LR with(nolock)
				left join v_LpuRegionType LRT with(nolock) on LR.LpuRegionType_id=LRT.LpuRegionType_id
				where
					LR.LpuRegion_Id in( ".implode( ", ", $arRegions )." )".(getRegionNick() != 'vologda' ?
					"and LRT.LpuRegionType_sysNick in ('ter','ped','gin')" : "");
			

			$result = $this->db->query($sql);

			if (is_object($result))
			{
				$res = $result->result('array');
				foreach($res as $row) {
					$Region[] = trim($row['LpuRegion_Name']);
				}
			}
		}
		return $Region;
	}

	/**
	 *	Получение перс. данных пациента
	 */
	function getPersonData($data) {
		$filter = "PS.Person_id = :Person_id";
		$query = "
			select top 1
				PS.Polis_id
				,PS.PAddress_id
				,PS.UAddress_id
				,PC.Lpu_id
				,PS.Person_id
				,RTRIM(PS.Person_SurName) + ' ' + RTRIM(PS.Person_FirName) + ' ' + ISNULL(RTRIM(PS.Person_SecName),'') as Person_FIO
				,RTRIM(PS.Person_SurName) as Person_SurName
				,RTRIM(PS.Person_FirName) as Person_FirName
				,ISNULL(RTRIM(PS.Person_SecName),'') as Person_SecName
				,convert(varchar(10), PS.Person_BirthDay, 120) as Person_BirthDay
				,ISNULL(PS.Person_Snils,'') as Person_Snils
				,ORG.Org_Email as OrgSmo_Email
				,convert(varchar, cast(PC.PersonCard_begDate as datetime),104) as PersonCard_begDate
			from
				v_PersonState PS with(nolock)
				outer apply(
					select top 1
						Lpu_id, PersonCard_begDate
					from v_PersonCard with(nolock)
					where
						Person_id = PS.Person_id
						and LpuAttachType_id = :LpuAttachType_id
					order by
						PersonCard_begDate desc
				) as PC
				left join v_Polis Polis with(nolock) on Polis.Polis_id = PS.Polis_id
				left join v_OrgSmo SMO with(nolock) on SMO.OrgSmo_id = Polis.OrgSmo_id
				left join v_Org Org with(nolock) on Org.Org_id = SMO.Org_id
			where
				{$filter}
		";
		//echo getDebugSql($query, $data); die();
		$result = $this->db->query($query, $data);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	*	Сохранение заявления о выборе МО
	*/
	function savePersonCardAttach($data) {
		$proc = "p_PersonCardAttach_" . (empty($data['PersonCardAttach_id']) ? "ins" : "upd");

		$data['PersonCardAttach_IsSMS'] = $data['PersonCardAttach_IsSMS']+1;
		$data['PersonCardAttach_IsEmail'] = $data['PersonCardAttach_IsEmail']+1;
		$data['PersonCardAttach_SMS'] = str_replace(' ','',substr($data['PersonCardAttach_SMS'],3));
		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @Res = :PersonCardAttach_id;
			exec {$proc}
				@PersonCardAttach_id = @Res output,
				@PersonCardAttach_setDate = :PersonCardAttach_setDate,
				@Lpu_id = :Lpu_id,
				@Lpu_aid = :Lpu_aid,
				@Address_id = :Address_id,
				@Polis_id = :Polis_id,
				@Person_id = :Person_id,
				@PersonCardAttach_IsSMS = :PersonCardAttach_IsSMS,
				@PersonCardAttach_SMS = :PersonCardAttach_SMS,
				@PersonCardAttach_IsEmail = :PersonCardAttach_IsEmail,
				@PersonCardAttach_Email = :PersonCardAttach_Email,
				@PersonCardAttach_IsHimself = :PersonCardAttach_IsHimself,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @Res as PersonCardAttach_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		//		--@PersonAmbulatCard_id = :PersonAmbulatCard_id,
		//echo getDebugSql($query, $data); die();
		$result = $this->db->query($query, $data);
		if ( is_object($result) ) {
			$result = $result->result('array');
			$statusTypes = array(1,2);
			foreach($statusTypes as $statusType) {
				$this->savePersonCardAttachStatus(array(
					'PersonCardAttachStatus_id' => null
					,'PersonCardAttach_id' => $result[0]['PersonCardAttach_id']
					,'PersonCardAttachStatusType_id' => $statusType
					,'PersonCardAttachStatus_setDate' => $data['PersonCardAttach_setDate']
					,'pmUser_id' => $data['pmUser_id']
				));
			}
			return $result;
		} else {
			return false;
		}
	}

	/**
	*	Сохранение статуса заявления о выборе МО
	*/
	function savePersonCardAttachStatus($data) {
		if (!empty($data['PersonCardAttachStatusType_Code'])) {
			$data['PersonCardAttachStatusType_id'] = $this->getFirstResultFromQuery("
				select top 1 PersonCardAttachStatusType_id
				from v_PersonCardAttachStatusType with(nolock)
				where PersonCardAttachStatusType_Code = :PersonCardAttachStatusType_Code
			", $data);
			if (empty($data['PersonCardAttachStatusType_id'])) {
				return $this->createError('','Ошибка при получении идентификатора статуса заявления о выборе МО');
			}
		}

		$params = array(
			'PersonCardAttachStatus_id' => !empty($data['PersonCardAttachStatus_id'])?$data['PersonCardAttachStatus_id']:null,
			'PersonCardAttach_id' => $data['PersonCardAttach_id'],
			'PersonCardAttachStatusType_id' => $data['PersonCardAttachStatusType_id'],
			'PersonCardAttachStatus_setDate' => !empty($data['PersonCardAttachStatus_setDate'])?$data['PersonCardAttachStatus_setDate']:null,
			'pmUser_id' => $data['pmUser_id'],
		);
		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000),
				@setDate datetime = :PersonCardAttachStatus_setDate;
			set @Res = :PersonCardAttachStatus_id;
			if @setDate is null set @setDate = (select dbo.tzGetDate())
			exec p_PersonCardAttachStatus_ins
				@PersonCardAttachStatus_id = @Res output,
				@PersonCardAttach_id = :PersonCardAttach_id,
				@PersonCardAttachStatusType_id = :PersonCardAttachStatusType_id,
				@PersonCardAttachStatus_setDate = @setDate,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @Res as PersonCardAttachStatus_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		//echo getDebugSql($query, $params); die();
		$result = $this->queryResult($query, $params);
		if (!is_array($result)) {
			return $this->createError('','Ошибка при сохраняении статуса заявления о выборе МО');
		}
		return $result;
 	}

	/**
	 * Сохранение данных об отказе от видов медицинского вмешательства
	 */
	function savePersonCardMedicalInterventData($data) {
		$PersonCardMedicalInterventData = json_decode($data['PersonCardMedicalInterventData'], true);

		foreach($PersonCardMedicalInterventData as $record) {
			$params = array(
				'PersonCardMedicalIntervent_id' => null,
				'PersonCard_id' => $data['PersonCard_id'],
				'MedicalInterventType_id' => $record['MedicalInterventType_id'],
				'pmUser_id' => $data['pmUser_id']
			);

			if ($record['PersonCardMedicalIntervent_id'] <= 0 && $record['PersonMedicalIntervent_IsRefuse'])
			{
				$response = $this->savePersonCardMedicalIntervent($params);
			} else
			if ($record['PersonCardMedicalIntervent_id'] > 0 && !$record['PersonMedicalIntervent_IsRefuse'])
			{
				$params['PersonCardMedicalIntervent_id'] = $record['PersonCardMedicalIntervent_id'];
				$response = $this->deletePersonCardMedicalIntervent($params);
			} else
			if($record['PersonCardMedicalIntervent_id'] > 0 && $record['PersonMedicalIntervent_IsRefuse'])
			{
				$params['PersonCardMedicalIntervent_id'] = $record['PersonCardMedicalIntervent_id'];
				$response = $this->deletePersonCardMedicalIntervent($params);
				if (empty($response[0]['Error_Msg'])) {
					$params['PersonCardMedicalIntervent_id'] = null;
					$response = $this->savePersonCardMedicalIntervent($params);
				}
			}
			if (!empty($response[0]['Error_Msg'])) {
				return array($response);
			}
		}
		return array(array('Error_Msg' => ''));
 	}

	/**
	 * Сохранение отказа от видов медицинского вмешательства
	 */
	function savePersonCardMedicalIntervent($data) {
		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @Res = :PersonCardMedicalIntervent_id;
			exec p_PersonCardMedicalIntervent_ins
				@PersonCardMedicalIntervent_id = @Res output,
				@PersonCard_id = :PersonCard_id,
				@MedicalInterventType_id = :MedicalInterventType_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @Res as PersonCardMedicalIntervent_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$result = $this->db->query($query, $data);

		if (!is_object($result)) {
			return array(array('Error_Msg' => 'Ошибка при сохранении отказа от видов медицинского вмешательства!'));
		}
		return $result->result('array');
 	}

	/**
	 * Удаление всех отказов от мед.вмешательств по PersonCard_id
	 */
	function deleteAllPersonCardMedicalIntervent($data) {
		$params = array('PersonCard_id' => $data['PersonCard_id']);
		$query = "
			select PCMI.PersonCardMedicalIntervent_id
			from v_PersonCardMedicalIntervent PCMI with(nolock)
			where PCMI.PersonCard_id = :PersonCard_id
		";
		$result = $this->db->query($query, $data);

		if (!is_object($result)) {
			return array(array('Error_Msg' => 'Ошибка при удалении отказа от видов медицинского вмешательства!'));
		}
		$resp = $result->result('array');
		if (count($resp) > 0) {
			foreach($resp as $item) {
				$response = $this->deletePersonCardMedicalIntervent(array(
					'PersonCardMedicalIntervent_id' => $item['PersonCardMedicalIntervent_id']
				));
				if (!empty($response[0]['Error_Msg'])) {
					return array(array('success'=> false, 'Error_Msg' => $response[0]['Error_Msg']));
				}
			}
		}

		return array(array('Error_Msg' => ''));
	}

	/**
	 * Удаление отказа от видов медицинского вмешательства
	 */
	function deletePersonCardMedicalIntervent($data) {
		$query = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec p_PersonCardMedicalIntervent_del
				@PersonCardMedicalIntervent_id = :PersonCardMedicalIntervent_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$result = $this->db->query($query, $data);

		if (!is_object($result)) {
			return array(array('Error_Msg' => 'Ошибка при удалении отказа от видов медицинского вмешательства!'));
		}
		return $result->result('array');
 	}

	/**
	 *	Получение доп. информации по карте
	 */
	function getPersonCardAttachOnPersonCard($data) {
		$queryParams = array();
		$queryParams['PersonCard_id'] = $data['PersonCard_id'];
		$query = "
			select top 1
				PC.PersonCardAttach_id,
				PC.Person_id,
				PC.LpuAttachType_id,
				PC.PersonCard_endDate,
				PCA.PersonCardAttach_IsEmail,
				PCA.PersonCardAttach_Email,
				PCA.PersonCardAttach_IsSms,
				PCA.PersonCardAttach_Sms
			from
				PersonCard PC with(nolock)
				left join v_PersonCardAttach PCA with(nolock) on PCA.PersonCardAttach_id = PC.PersonCardAttach_id
			where
				PC.PersonCard_id = :PersonCard_id
		";
		$result = $this->db->query($query, $queryParams);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	*	Получение значения поля персональной инф-ции из портала "к варчу"
	*/
	function getPersonInfoKVRACHU($data) {
		/* Специально все сломал, так как подход совершенно неправильный
		у нас может быть несколько аккаунтов на сайте, на которые добавлен один человек,
		нельзя просто так взять телефон с одного аккаунта и отправить СМС  на него.
		К тому же нельзя это делать без согласия пользователя.
		К тому же по коду сообщение отправляется даже на неактивированный телефон.
		В общем надо все переосмысливать и переделывать, такое не прокатит.
		*/
		return false;
		/*
		$query = "
			Select top 1
				User_id as user_id,
				--Person_mainId as Person_id,
				UserNotify_Phone as PersonCardAttach_SMS,
				UserNotify_NotifyIsSMS as PersonCardAttach_IsSMS, -- 0, null = нет, 1-да
				UserNotify_NotifyIsEmail as PersonCardAttach_IsEmail,
				Users.email as PersonCardAttach_Email
			from
				UserNotify with (nolock)
				left join Users with (nolock) on Users.id = UserNotify.User_id
				inner join Person with (nolock) on Person.pmUser_id = UserNotify.User_id
			where
				Person_mainId = :Person_id
			order by
				last_login desc
		";
		$result = $this->db->query($query, array(
			'Person_id' => $data['Person_id']
		));
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}*/
	}

	/**
	 * Получение последнего статуса заявления
	 */
	function getPersonCardAttachStatus($data) {
		$params = array('PersonCardAttach_id' => $data['PersonCardAttach_id']);
		return $this->getFirstRowFromQuery("
			select top 1
				PCAST.PersonCardAttachStatusType_id,
				PCAST.PersonCardAttachStatusType_Code,
				PCAST.PersonCardAttachStatusType_Name
			from
				v_PersonCardAttachStatus PCAS with(nolock)
				left join v_PersonCardAttachStatusType PCAST with(nolock) on PCAST.PersonCardAttachStatusType_id = PCAS.PersonCardAttachStatusType_id
			where
				PCAS.PersonCardAttach_id = :PersonCardAttach_id
			order by
				PCAS.PersonCardAttachStatus_setDate desc,
				PCAS.PersonCardAttachStatus_id desc
		", $params);
	}

	/**
	*	Получение списка всех статусов заявления
	*/
	function getPersonCardAttachStatusesHistory($data) {
		$filter = "1=1";
		$filter .= " and PersonCardAttach_id = :PersonCardAttach_id";
		$query = "
			select
				PersonCardAttachStatus_id
			from
				v_PersonCardAttachStatus with(nolock)
			where
				{$filter}
		";
		$result = $this->db->query($query, $data);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	*	Удаление заявления о выборе МО
	*/
	function deletePersonCardAttach($data) {
		$statuses = $this->getPersonCardAttachStatusesHistory($data);
		if( is_array($statuses) && count($statuses) > 0 ) {
			foreach($statuses as $status) {
				$this->deletePersonCardAttachStatus($status);
			}
		}
		//https://redmine.swan.perm.ru/issues/95832 - у старых записей могут быть случаи, когда PersonCardAttach_id есть в другом PersonCard
		//В этом случае не удаляем PersonCardAttach
		$query_check = "
			select count(PersonCard_id) as cntPC
			from PersonCard
			where PersonCardAttach_id = :PersonCardAttach_id
		";
		$result_check = $this->db->query($query_check,$data);
		$result_check = $result_check->result('array');
		if(isset($result_check[0]) && $result_check[0]['cntPC'] > 0)
		{
			return array( 0 => array('Error_Code' => 0, 'Error_Msg'=>'') );
		}
		else
		{
			$query = "
				declare
					@Err_Msg varchar(1000),
					@Err_Code int
				exec p_PersonCardAttach_del
					@PersonCardAttach_id = :PersonCardAttach_id,
					@Error_Code = @Err_Code output,
					@Error_Message = @Err_Msg output
				select @Err_Code as Error_Code, @Err_Msg as Error_Msg;
			";
			$result = $this->db->query($query, $data);
			if ( is_object($result) ) {
				return $result->result('array');
			} else {
				return false;
			}
		}
	}

	/**
	*	Удаление статуса заявления о выборе МО из журнала статусов
	*/
	function deletePersonCardAttachStatus($data) {
		$query = "
			declare
				@Err_Msg varchar(1000),
				@Err_Code int
			exec p_PersonCardAttachStatus_del
				@PersonCardAttachStatus_id = :PersonCardAttachStatus_id,
				@Error_Code = @Err_Code output,
				@Error_Message = @Err_Msg output
			select @Err_Code as Error_Code, @Err_Msg as Error_Msg;
		";
		$result = $this->db->query($query, $data);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	*	Получение списка заявлений о выборе МО
	*/
	function loadPersonCardAttachGrid($data) {
		$filter = "1=1";
		$queryParams = array();
		if( !empty($data['Person_SurName']) ) {
			$filter .= " and PS.Person_SurName like :Person_SurName + '%'";
			$queryParams['Person_SurName'] = rtrim($data['Person_SurName']);
		}

		if( !empty($data['Person_FirName']) ) {
			$filter .= " and PS.Person_FirName like :Person_FirName + '%'";
			$queryParams['Person_FirName'] = rtrim($data['Person_FirName']);
		}

		if( !empty($data['Person_SecName']) ) {
			$filter .= " and PS.Person_SecName like :Person_SecName + '%'";
			$queryParams['Person_SecName'] = rtrim($data['Person_SecName']);
		}

		if( !empty($data['Lpu_id']) ) {
			$filter .= " and (PCA.Lpu_id = :Lpu_id or PCA.Lpu_aid = :Lpu_id)";
			$queryParams['Lpu_id'] = $data['Lpu_id'];
		}

		if( !empty($data['PersonCardAttachStatusType_id']) ) {
			$filter .= " and PCAST.PersonCardAttachStatusType_id = :PersonCardAttachStatusType_id";
			$queryParams['PersonCardAttachStatusType_id'] = $data['PersonCardAttachStatusType_id'];
		}

		$query = "
			select
				-- select
				PCA.PersonCardAttach_id
				,convert(varchar, cast(PCA.PersonCardAttach_setDate as datetime),104) as PersonCardAttach_setDate
				,RTRIM(ISNULL(PS.Person_SurName, ''))+' '+RTRIM(ISNULL(PS.Person_FirName, ''))+' '+RTRIM(ISNULL(PS.Person_SecName, '')) as Person_Fio
				,LPU_N.Lpu_Nick as Lpu_N_Nick
				,LPU_O.Lpu_Nick as Lpu_O_Nick
				,PCAST.PersonCardAttachStatusType_Name
				-- end select
			from
				-- from
				v_PersonCardAttach PCA (nolock)
				inner join v_PersonState as PS (nolock) on PS.Person_id = PCA.Person_id
				left join v_Lpu LPU_N (nolock) on LPU_N.Lpu_id = PCA.Lpu_aid
				left join v_Lpu LPU_O (nolock) on LPU_O.Lpu_id = PCA.Lpu_id
				outer apply(
					select top 1
						PersonCardAttachStatusType_id
					from
						v_PersonCardAttachStatus (nolock)
					where
						PersonCardAttach_id = PCA.PersonCardAttach_id
					order by
						PersonCardAttachStatus_id desc
				) as PCAS
				left join v_PersonCardAttachStatusType PCAST (nolock) on PCAST.PersonCardAttachStatusType_id = PCAS.PersonCardAttachStatusType_id
				-- end from
			where
				-- where
				{$filter}
				-- end where
			order by
				-- order by
				PCA.PersonCardAttach_setDate desc
				-- end order by
		";

		return $this->getPagingResponse($query, $queryParams, $data['start'], $data['limit'], true);
	}

	/**
	 * Получение данных для редактирования заявления на прикрепление
	 */
	function loadPersonCardAttachForm($data) {
		$params = array('PersonCardAttach_id' => $data['PersonCardAttach_id']);

		$query = "
			select top 1
				PCA.PersonCardAttach_id,
				PCA.Lpu_aid,
				convert(varchar(10), PCA.PersonCardAttach_setDate, 104) as PersonCardAttach_setDate,
				LR.LpuRegion_id,
				LRT.LpuRegionType_id,
				P.Person_id,
				GA.GetAttachmentCase_id,
				GA.GetAttachment_IsCareHome,
				GA.GetAttachment_Number
			from
				v_PersonCardAttach PCA with(nolock)
				cross apply(
					select top 1 Object_sid
					from ObjectSynchronLog with(nolock)
					where ObjectSynchronLogService_id = 2 and Object_Name = 'PersonCardAttach'
						and Object_id = PCA.PersonCardAttach_id
					order by Object_setDT desc
				) OSL_Attach
				inner join r101.v_GetAttachment GA with(nolock) on GA.GetAttachment_id = OSL_Attach.Object_sid
				cross apply(
					select top 1 Object_id
					from ObjectSynchronLog with(nolock)
					where ObjectSynchronLogService_id = 2 and Object_Name = 'LpuRegion'
						and Object_sid = GA.GetTerrService_id
					order by Object_setDT desc
				) OSL_LpuRegion
				inner join v_LpuRegion LR with(nolock) on LR.LpuRegion_id = OSL_LpuRegion.Object_id
				inner join v_LpuRegionType LRT with(nolock) on LRT.Region_id = 101 and LRT.LpuRegionType_Code = GA.GetTerrServiceProfile_id
				inner join Person P with(nolock) on P.BDZ_id = GA.Person_id
			where
				PCA.PersonCardAttach_id = :PersonCardAttach_id
		";


		echo getDebugSQL($query, $params);die;
		$result = $this->queryResult($query, $params);

		if (isset($result[0]) && !empty($result[0]['PersonCardAttach_id'])) {
			$files = $this->getFilesOnPersonCardAttach(array(
				'PersonCardAttach_id' => $result[0]['PersonCardAttach_id'],
				'PersonCard_id' => null,
			));
			if (!$files) {
				$this->createError('Ошибка при получении списка прикрепленных файлов');
			}
			$result[0]['files'] = $files;
		}

		return $result;
	}

    /**
     * @param $data
     * @return bool
     */
    function loadPersonCardMedicalInterventGrid($data)
	{
		$params = array('PersonCard_id' => $data['PersonCard_id']);

		$query = "
			select
				isnull(PCMI.PersonCardMedicalIntervent_id, -1) as PersonCardMedicalIntervent_id,
				MIT.MedicalInterventType_id,
				MIT.MedicalInterventType_Code,
				MIT.MedicalInterventType_Name,
				(CASE WHEN PCMI.PersonCardMedicalIntervent_id is null THEN 0 ELSE 1 END) as PersonMedicalIntervent_IsRefuse
			from
				v_MedicalInterventType MIT with(nolock)
				outer apply(
					select top 1 t.PersonCardMedicalIntervent_id
					from v_PersonCardMedicalIntervent t with(nolock)
					where
						(t.PersonCard_id = :PersonCard_id or t.PersonCard_id is null)
						and t.MedicalInterventType_id = MIT.MedicalInterventType_id
				) as PCMI
		";

		$result = $this->db->query($query, $params);

		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

    /**
     *	Список прикрепленного населения к указанной СМО на указанную дату
     */
    function loadAttachedList($data)
    {
        $filterList = array();
        $queryParams = array();

        if ( !empty($data['AttachLpu_id']) ) {
            $filterList[] = 'PC.Lpu_id = :Lpu_id';
            $queryParams['Lpu_id'] = $data['AttachLpu_id'];
        }

        $query = "
			select
				PC.PersonCard_Code as ID_PAC, -- Номер истории болезни
				rtrim(upper(PS.Person_SurName)) as FAM, -- Фамилия
				rtrim(upper(PS.Person_FirName)) as IM, -- Имя
				isnull(rtrim(Upper(case when Replace(PS.Person_Secname,' ','')='---'  or PS.Person_Secname = '' then 'НЕТ' else PS.Person_Secname end)), 'НЕТ') as OT, -- Отчество
				PS.Sex_id as W, -- Пол застрахованного
				convert(varchar(10), PS.Person_BirthDay, 120) as DR, -- Дата рождения застрахованного
				PT.PolisType_CodeF008 as VPOLIS,
				rtrim(case when PLS.PolisType_id = 4 then '' else PLS.Polis_Ser end) as SPOLIS,
				rtrim(case when PLS.PolisType_id = 4 then PS.Person_EdNum else PLS.Polis_Num end) as NPOLIS
			from
				v_PersonState PS with (nolock)
				inner join v_PersonCard PC with (nolock) on PC.Person_id = PS.Person_id
				inner join v_Polis PLS with (nolock) on PLS.Polis_id = ps.Polis_id
				inner join v_PolisType PT with (nolock) on PT.PolisType_id = PLS.PolisType_id
			where PC.LpuAttachType_id = 1
				and (PC.CardCloseCause_id is null or PC.CardCloseCause_id <> 4)
				and (PLS.Polis_endDate is null or PLS.Polis_endDate > dbo.tzGetDate())
				and (
					(PLS.PolisType_id = 4 and dbo.getRegion() <> 2 and PS.Person_EdNum is not null)
					or ((PLS.PolisType_id <> 4 or dbo.getRegion() = 2) and PLS.Polis_Num is not null)
				)
				and PT.PolisType_CodeF008 is not null
				" . (count($filterList) > 0 ? "and " . implode(' and ', $filterList) : "") . "
		";
        //echo getDebugSQL($query, $queryParams); die();
        $result = $this->db->query($query, $queryParams);

        if ( !is_object($result) ) {
            return false;
        }

		$PERS = $result->result('array');

        if ( !is_array($PERS) || count($PERS) == 0) {
            return array(
                'Error_Code' => 1, 'Error_Msg' => 'Список выгрузки пуст!'
            );
        }

		$ZGLV = array(
			array(
				 'CODE_MO' => ''
				,'SMO' => ''
				,'ZAP' => 0
			)
		);

		// Получаем код МО
        if ( !empty($data['AttachLpu_id']) ) {
			$query = "
				select top 1
					 Lpu_f003mcod as CODE_MO
					,null as SMO
				from v_Lpu with (nolock)
				where Lpu_id = :Lpu_id
			";
			$result = $this->db->query($query, $queryParams);

			if ( !is_object($result) ) {
				return false;
			}

			$ZGLV = $result->result('array');

			if ( !is_array($ZGLV) || count($ZGLV) == 0) {
				return array(
					'Error_Code' => 1, 'Error_Msg' => 'Ошибка при получении кода МО!'
				);
			}
		}

        $data = array();
        $data['Error_Code'] = 0;

		$ZGLV[0]['ZAP'] = count($PERS);

		$data['PERS'] = $PERS;
        $data['ZGLV'] = $ZGLV;

        return $data;
    }












	// =================================================================================================================
	// START loadAttachedListCSV
	// =================================================================================================================

    private $Lpu_RegNum = '000000';

    private $dirExport = '';

	private $isFopenCSV = false;
	private $isFcloseCSV = false;
	private $fpCSV = null;
	private $isExistFileCSV = false;
	private $fileNameCSV = '';
	private $filePathCSV = '';

	private $isFopenError = false;
	private $isFcloseError = false;
	private $fpError = null;
	private $isExistFileError = false;
	private $fileNameError = '';
	private $filePathError = '';

	private $dataOrgSMO = null;
	private $dataPolisType = null;

	//https://redmine.swan.perm.ru/issues/134507
	// Псков. Хотят все значения заключать в кавычки. Функция fputcsv() в кавычки заключает только данные со смешанными типами данных.
	// Чтобы реализовать этот костыль решил подставлять в каждое поле некоторые фиктивные строковые символы с пробелом ($csvDummyStrCharacters), а затем удалив их после создания файла
	private $csvFrameIsQuote = false;
	private $csvDummyStrCharacters = "#@ @#";


	/**
	 *	Список прикрепленного населения к указанной СМО на указанную дату
	 */
	function loadAttachedListCSV($data){


		$this->_resetAttachedListCSV();

		$filterList = array();
		$queryParams = array();
		$this->csvFrameIsQuote = ($this->getRegionNick() == 'pskov') ? true : false;

		if ( ! empty($data['AttachLpu_id']) ) {

			$this->_createLpu_RegNum($data['AttachLpu_id']);

			$filterList[] = 'PC.Lpu_id = :AttachLpu_id';
			$queryParams['AttachLpu_id'] = $data['AttachLpu_id'];
		}

		// Если нужно выбрать от даты до текущей
		if ( ! empty($data['AttachPeriod']) && $data['AttachPeriod'] == 2 && ! empty($data['AttachPeriod_FromDate'])) {
			$filterList[] = '
				(
					(PC.PersonCard_begDate >= :AttachPeriod_FromDate AND PC.PersonCard_begDate <= @curDT) OR
					(PC.PersonCard_endDate >= :AttachPeriod_FromDate AND PC.PersonCard_endDate <= @curDT)
				)
			';
			$queryParams['AttachPeriod_FromDate'] = $data['AttachPeriod_FromDate'];
		}


		$where = ' PC.LpuAttachType_id = 1 ';


		if($this->getRegionNick() == 'pskov'){
			// •	Не идентифицированные пациенты (без признака БДЗ) с прикреплением по заявлению («Способ прикрепления» = 1).
			//•	Идентифицированные пациенты (с признаком БДЗ) с прикреплением по заявлению или с условным прикреплением («Способ прикрепления» = 1, 2).
			$where = ' ((PC.LpuAttachType_id = 1 and PS.Person_IsBDZ = 0) or ((PC.LpuAttachType_id = 1 or PC.LpuAttachType_id = 2) and PS.Person_IsBDZ = 1)) ';
            array_push($filterList , '
                (
                    PLS.PolisType_id != 2
                )
            ');
		}


		$dataOrgSMO = $this->_getDataOrgSMO();
		$dataPolisType = $this->_getDataPolisType();

		$query = "
			declare @curDT datetime = dbo.tzGetDate();

			with

			UDocument as (
				SELECT
					D.Document_id,
					DT.DocumentType_id,
					DT.DocumentType_Code,
					D.Document_begDate,
					D.OrgDep_id,
					Org.Org_Name
				FROM
					Document D with (nolock)
					inner join v_PersonState PS with (nolock) on D.Document_id = PS.Document_id
					left join DocumentType DT with (nolock)  on DT.DocumentType_id = D.DocumentType_id
					left join v_OrgDep OD with (nolock)  on D.OrgDep_id = OD.OrgDep_id
					left join v_Org Org with (nolock) on Org.Org_id = OD.Org_id
				" . ((!empty($data['AttachLpu_id'])) ? "WHERE PS.Lpu_id = :AttachLpu_id" : "") . "
			),

			UPolis as (
				SELECT
					Polis_id,
					OrgSMO_id,
					PolisType_id,
					Polis_Ser,
					Polis_Num,
					Polis_endDate
				FROM
					v_Polis PLS  with (nolock)
			)

			SELECT

				case when PLS.PolisType_id IN (1,3) and ISNULL(PLS.Polis_endDate, @curDT) >= @curDT then null else PS.Person_edNum end as Person_edNum,
				PS.Person_SurName,
				PS.Person_FirName,
				PS.Person_Secname,
				PS.Person_BirthDay as DR,
				PS.Document_Ser,
				PS.Document_Num,
				PS.Person_Snils,
				PS.Person_id,
				PS.Document_id,

				PC.PersonCardAttach_id,
				PC.PersonCard_begDate,
				PC.PersonCard_endDate,
				PC.Person_id,
				PC.Lpu_id,
				PC.LpuRegion_id,
				PC.LpuAttachType_id,
				PC.CardCloseCause_id,

				PLS.Polis_id,
				PLS.PolisType_id,
				PLS.Polis_Ser,
				PLS.Polis_Num,
				PLS.Polis_endDate,
				PLS.OrgSMO_id,

				PToken.PassportToken_tid,
				LR.LpuRegion_Name,
				PKind.code,
				PRDR.Person_Birthplace,
				D.DocumentType_id,
				D.DocumentType_Code,
				D.Document_begDate,
				D.Org_Name,
				L.Lpu_f003mcod,
				PersonCard_IsAttachCondit,
				MPSnils.Person_Snils as MedPersonal_Snils

			FROM
				v_PersonState_all PS with (nolock)

				inner join v_PersonCard PC with (nolock) on PC.Person_id = PS.Person_id
				left join UPolis PLS on PLS.Polis_id = PS.Polis_id

				left join UDocument D on D.Document_id = PS.Document_id

				outer apply (
					SELECT TOP 1
						PRDR.Person_Birthplace
					FROM
						erz.v_PersonRequestDataResult PRDR with (nolock)
						left join erz.v_PersonRequestData PRD with (nolock) on PRD.Person_id = PS.Person_id
					WHERE
						PRDR.PersonRequestData_id = PRD.PersonRequestData_id
				) PRDR

				left join v_Lpu L  with (nolock) on L.Lpu_id = PC.Lpu_id
				left join fed.v_PassportToken PToken with (nolock) on L.Lpu_id = PToken.Lpu_id
				left join v_LpuRegion LR with (nolock)  on LR.LpuRegion_id = PC.LpuRegion_id
				left join v_LpuSection LS with (nolock) on LR.LpuSection_id = LS.LpuSection_id

				outer apply (
					select 1 as PolisFormType_Code
				) PFT

				outer apply (
					select top 1
						MP.Person_Snils
					from
						v_MedPersonal MP with (nolock)
						inner join v_MedStaffRegion MSR with (nolock) on MSR.LpuRegion_id = LR.LpuRegion_id and MSR.MedPersonal_id = MP.MedPersonal_id
					order by
						MSR.MedStaffRegion_endDate
				) as MPSnils

				outer apply (
					select top 1
						PK.code as code
					from
						v_MedPersonal MP with (nolock)
						inner join v_MedStaffRegion MSR with (nolock) on MSR.LpuRegion_id = LR.LpuRegion_id and MSR.MedPersonal_id = MP.MedPersonal_id
						inner join v_MedStaffFact MSF with (nolock) on MSR.MedStaffFact_id = MSF.MedStaffFact_id
						inner join persis.v_PostKind PK with (nolock)  on PK.id = MSF.PostKind_id
				) as PKind
			WHERE
				".($where)."
				" . (count($filterList) > 0 ? "and " . implode(' and ', $filterList) : "") . "
		";



		$result = $this->db->query($query, $queryParams);

		if( ! isset($result->result_id)){
			return false;
		}


		$curDate = new DateTime();

		// Создаем и открываем файл CSV для записи
		$this->_doFopenCSV();

		// Создаем и открываем файл ошибок для записи
		$this->_doFopenError();

		$i_error = 0;
		$errors_person = array();
		$csv_person = array();
		while($row = sqlsrv_fetch_array($result->result_id, SQLSRV_FETCH_ASSOC)) {

			$doContinue = false;

			if ($row['CardCloseCause_id'] == 4) {
				$doContinue = true;
			}

			if ($doContinue != true) {
				if (empty($row['Polis_id'])) {
					$doContinue = true;
				}
			}

			if ($doContinue != true) {
				// PLS.Polis_endDate is null or PLS.Polis_endDate > dbo.tzGetDate()
				if (!empty($row['Polis_endDate']) && $row['Polis_endDate'] <= $curDate) {
					$doContinue = true;
				}
			}

			if ($doContinue != true) {
				if (!isset($dataPolisType[$row['PolisType_id']])) {
					$doContinue = true;
				}
			}

			if ($doContinue != true) {
				$KLRgn_id = '';
				if (isset($dataOrgSMO[$row['OrgSMO_id']]) && !empty($dataOrgSMO[$row['OrgSMO_id']]) && isset($dataOrgSMO[$row['OrgSMO_id']]['KLRgn_id']) && !empty($dataOrgSMO[$row['OrgSMO_id']]['KLRgn_id'])) {
					$KLRgn_id = $dataOrgSMO[$row['OrgSMO_id']]['KLRgn_id'];
				}

				if ($KLRgn_id != $this->getRegionNumber() && empty($row['DocumentType_id'])) {
					$doContinue = true;
				}
			}

			if ($doContinue) {
				continue;
			}

			// Перенес подготовку данных из SQL в PHP для ускорения выполнения запроса
			$data = $this->_processingRowToFile($row, $dataPolisType);

			// На экспорт
			if( ! empty($row['MedPersonal_Snils'])){

				unset($data['polis_info']);

				// убираем возможные дубли в файле выгрузки
				// ВАЖНО!!! если использовать in_array(), то время выполнения значительно увеличивается!!!ВАЖНО!!! ВАЖНО!!! ВАЖНО!!! ВАЖНО!!!
				if(isset($csv_person[$row['Person_id']])){
					continue;
				}

				// Записываем в файл CSV каждую строку отдельно, чтобы PHP не съел всю выделенную память
				$this->_putRowToFileCSV($data);

				$csv_person[$row['Person_id']] = true;
			}
			else {

				// убираем возможные дубли в файле ошибок
				// ВАЖНО!!! если использовать in_array(), то время выполнения значительно увеличивается!!!ВАЖНО!!! ВАЖНО!!! ВАЖНО!!! ВАЖНО!!!
				if(isset($errors_person[$row['Person_id']])){
					continue;
				}

				$i_error += 1;

				// error (string)
				$error = $this->_processingRowToFileError($data, $i_error);

				// В файл ошибок
				$this->_putRowToFileError($error);

				$errors_person[$row['Person_id']] = true;
			}
		}

		unset($errors_person);

		if($i_error != 0){
			$this->_renameFileError($i_error);
		}


		$attached_list_dir = $this->_getDirExport();

		$attached_list_file_name = $this->_getFileNameCSV();
		$attached_list_file_path = $this->_getFilePathCSV();
		$this->_doFcloseCSV();


		$attached_list_errors_file_name = $this->_getFileNameError();
		$attached_list_errors_file_path = $this->_getFilePathError();
		$this->_doFcloseError();


		// забываем данные экспорта
		$this->_resetAttachedListCSV();

		$arrData = array(

			'attached_list_dir' => $attached_list_dir,

			// CSV
			'attached_list_file_name' => $attached_list_file_name,
			'attached_list_file_path' => $attached_list_file_path,

			// Error
			'attached_list_errors_file_name' => $attached_list_errors_file_name,
			'attached_list_errors_file_path' => $attached_list_errors_file_path
		);
		if($this->csvFrameIsQuote){
			// удаляем фиктивные строки если добавляли для Пскова для обрамления кавычками всех данных
			$res = $this->delDummyStrCharacters($arrData);
		}

		return $arrData;
	}

	/**
	 * Обработка данных для экспорта в файлы
	 * @param $row
	 * @param array $dataPolisType
	 * @return array
	 */
	private function _processingRowToFile($row, $dataPolisType = array()){
		// -------------------------------------------------------------------------
		$row['PolisFormType_Code'] = 1;
		if ($row['PolisFormType_Code'] === NULL) {
			$row['PolisFormType_Code'] = 0;
		}

		$PolisType_SysNick = $dataPolisType[$row['PolisType_id']]['PolisType_SysNick'];
		$PolisType_Name = $dataPolisType[$row['PolisType_id']]['PolisType_Name'];
		// -------------------------------------------------------------------------

		$data = array();


		// Атрибут "Действие"
		$data['action'] = 'Р';



		/*
			Атрибут "Код типа ДПФС:"
			П - Бумажный полис ОМС единого образца
			Э - Электронный полис ОМС единого образца
			В – Временное свидетельство
			С – Полис старого образца
			К – В составе УЭК
		*/
		if ($PolisType_SysNick == 'OMS') {
			$data['DFPSType'] = 'С';
		} else if ($PolisType_SysNick == 'vremsvid') {
			$data['DFPSType'] = 'В';
		} else if ($PolisType_SysNick == 'OMS (new)' && in_array((int)$row['PolisFormType_Code'], array(0, 1))) {
			$data['DFPSType'] = 'П';
		} else if ($PolisType_SysNick == 'OMS (new)' && (int)$row['PolisFormType_Code'] == 2) {
			$data['DFPSType'] = 'Э';
		} else if ($PolisType_SysNick == 'OMS (new)' && (int)$row['PolisFormType_Code'] == 3) {
			$data['DFPSType'] = 'К';
		}





		// Серия и номер ДПФС
		if (in_array($PolisType_SysNick, array('OMS', 'vremsvid'))) {

			$data['ID_Polis'] = rtrim($row['Polis_Ser']) . ' № ' . rtrim($row['Polis_Num']);

			if($this->getRegionNick() == 'pskov'){
				if($PolisType_SysNick == 'vremsvid'){
					$data['ID_Polis'] = rtrim($row['Polis_Ser']) . rtrim($row['Polis_Num']);
				}
			}


		} else {
			$data['ID_Polis'] = '';
		}



		// polis_info
		$data['polis_info'] = $PolisType_Name . ' ' . rtrim($row['Polis_Ser']) . ' № ' . rtrim($row['Polis_Num']);

		// Единый номер полиса ОМС
		$data['Person_edNum'] = $row['Person_edNum'];


		// Фамилия застрахованного лица
		$data['FAM'] = rtrim($row['Person_SurName']);//rtrim(strtoupper($row['Person_SurName']));
		// Имя застрахованного лица
		$data['IM'] = rtrim($row['Person_FirName']);//rtrim(strtoupper($row['Person_FirName']));
		// Отчество застрахованного лица
		$data['OT'] = rtrim($row['Person_Secname']);//rtrim(strtoupper($row['Person_Secname']));


		// Дата рождения застрахованного лица.
		$DR = '';
		if (!empty($row['DR'])) {
			$DR = $row['DR']->format('Ymd');
		}
		$data['DR'] = $DR;


		// Место рождения застрахованного лица.
		$data['Person_Birthplace'] = $row['Person_Birthplace'];


		// Тип документа, удостоверяющего личность.
		$data['DocumentType_Code'] = $row['DocumentType_Code'];


		// Номер или серия и номер документа, удостоверяющего личность.
		$data['Document_SerNum'] = rtrim($row['Document_Ser']) . ' № ' . rtrim($row['Document_Num']);
		if($this->getRegionNick() == 'pskov'){
			if(empty($row['DocumentType_Code'])){
				$data['Document_SerNum'] = '';
			}elseif ($data['DocumentType_Code'] == 14) {
				$ser = rtrim($row['Document_Ser']);
				$data['Document_SerNum'] = substr($ser,0,2).' '. substr($ser, -2, 2). ' № ' . rtrim($row['Document_Num']);
			}
		}

		// Дата выдачи документа, удостоверяющего личность.
		$Document_begDate = '';
		if (!empty($row['Document_begDate'])) {
			$Document_begDate = $row['Document_begDate']->format('Ymd');
		}
		$data['Document_begDate'] = $Document_begDate;


		// Наименование органа, выдавшего документ
		$data['OrgDep_Name'] = $row['Org_Name'];

		// СНИЛС застрахованного лица.
		$data['Person_Snils'] = $row['Person_Snils'];

		// Идентификатор МО
		$data['Lpu_f003mcod'] = $row['Lpu_f003mcod'];


		// Способ прикрепления
		if (!empty($row['PersonCardAttach_id'])) {
			$data['AttachSposob'] = 2;
		} else if ($row['PersonCard_IsAttachCondit'] == 2) {
			$data['AttachSposob'] = 1;
		} else {
			$data['AttachSposob'] = 0;
		}


		// Тип прикрепления
		$data['AttachType'] = '';

		// Дата прикрепления
		$PersonCard_begDate = '';
		if (!empty($row['PersonCard_begDate'])) {
			$PersonCard_begDate = $row['PersonCard_begDate']->format('Ymd');
		}
		$data['PersonCard_begDate'] = $PersonCard_begDate;


		// Дата открепления
		$PersonCard_endDate = '';
		if (!empty($row['PersonCard_endDate'])) {
			$PersonCard_endDate = $row['PersonCard_endDate']->format('Ymd');
		}
		$data['PersonCard_endDate'] = $PersonCard_endDate;


		// ОИД МО – уникальный идентификатор медицинской организации в реестре МО.
		$data['PassportToken_tid'] = $row['PassportToken_tid'];


		// Код подразделения
		$data['LpuSection_Code'] = '0';

		// Номер (код) участка
		$data['LpuRegion_Name'] = $row['LpuRegion_Name'];

		// СНИЛС медицинского работника
		$data['MedPersonal_Snils'] = $row['MedPersonal_Snils'];

		// Тип должности медицинского работника
		$data['code'] = $row['code'];



		//return $data;
		return $this->addDummyStrCharacters($data);
	}

	/**
	 * добавляем фиктивные строковые символы
	 */
	function addDummyStrCharacters($data){
		$dummyStrCharacters = $this->csvDummyStrCharacters;
		if(!$this->csvFrameIsQuote) return $data;

		// добавляем фиктивные строки к данным, что приведет к цитированию в функции fputcsv()
		foreach ($data as $key => $value) {
			$data[$key] = $value.$dummyStrCharacters;
		}
		return $data;
	}

	/**
	 * удаляем фиктивные строковые символы из файла
	 */
	function delDummyStrCharacters($data){
		if(!$this->csvFrameIsQuote) return true;
		if(empty($data['attached_list_file_path']) || !file_exists($data['attached_list_file_path'])){
			return false;
		}

		$dummyStrCharacters = $this->csvDummyStrCharacters;
		$filename = $data['attached_list_file_path'];
		$contents = file_get_contents($filename);
		if($contents){
			$contents = str_replace($dummyStrCharacters, "", $contents);
			return file_put_contents($filename, $contents);
		}else{
			return false;
		}
	}

	/**
	 * @return string
	 */
	private function _getDirExport(){
		if(empty($this->dirExport)){
			$this->_createDirExport();
		}
		return $this->dirExport;
	}

	/**
	 * каталог в котором лежат выгружаемые файлы
	 * @return string
	 */
	private function _createDirExport(){

		if ( ! file_exists(EXPORTPATH_ATACHED_LIST)){
			mkdir( EXPORTPATH_ATACHED_LIST );
		}

		$this->dirExport = EXPORTPATH_ATACHED_LIST."csv_".time()."_"."attachedList";

		mkdir($this->dirExport);

		return $this->dirExport;
	}

	/**
	 * @return string
	 */
	private function _getFileNameCSV(){
		return $this->fileNameCSV;
	}

	/**
	 * @return string
	 */
	private function _getFilePathCSV(){
		return $this->filePathCSV;
	}

	/**
	 * @return string
	 */
	private function _getFileNameError(){
		return $this->fileNameError;
	}

	/**
	 * @return string
	 */
	private function _getFilePathError(){
		return $this->filePathError;
	}

	/**
	 * Забываем данные экспорта
	 * @return bool
	 */
	private function _resetAttachedListCSV(){
		$this->_resetAttachedListFileCSV();
		$this->_resetAttachedListFileError();
		$this->Lpu_RegNum = '000000';
		$this->dataOrgSMO = null;
		$this->dataPolisType = null;

		return true;
	}

	/**
	 * Забываем данные файла CSV
	 * @return bool
	 */
	private function _resetAttachedListFileCSV(){
		$this->isFopenCSV = false;
		$this->isFcloseCSV = false;
		$this->fpCSV = null;
		$this->fileNameCSV = '';
		$this->filePathCSV = '';
		$this->isExistFileCSV = false;

		return true;
	}

	/**
	 * Забываем данные файла ошибок
	 * @return bool
	 */
	private function _resetAttachedListFileError(){
		$this->isFopenError = false;
		$this->isFcloseError = false;
		$this->fpError = null;
		$this->fileNameError = '';
		$this->filePathError = '';
		$this->isExistFileError = false;

		return true;
	}

	/**
	 * @return array|bool|null
	 */
	private function _getDataOrgSMO(){

		if( ! empty($this->dataOrgSMO)){
			return $this->dataOrgSMO;
		}

		$query = "SELECT OrgSMO_id, KLRgn_id FROM v_OrgSMO";
		$result = $this->db->query($query);

		if( ! isset($result->result_id)){
			return false;
		}

		$dataOrgSMO = array();
		while( $row = sqlsrv_fetch_array( $result->result_id, SQLSRV_FETCH_ASSOC) ) {
			$dataOrgSMO[$row['OrgSMO_id']] = $row;
		}
		$result->result_id = null;
		unset($result->result_id);
		$result = null;
		unset($result);

		$this->dataOrgSMO = $dataOrgSMO;
		unset($dataOrgSMO);

		return $this->dataOrgSMO;
	}


	/**
	 * @return array|bool|null
	 */
	private function _getDataPolisType(){

		if( ! empty($this->dataPolisType)){
			return $this->dataPolisType;
		}

		$query = "SELECT PolisType_id, PolisType_SysNick, PolisType_Name FROM v_PolisType";
		$result = $this->db->query($query);

		if( ! isset($result->result_id)){
			return false;
		}

		$dataPolisType = array();
		while( $row = sqlsrv_fetch_array( $result->result_id, SQLSRV_FETCH_ASSOC) ) {
			$dataPolisType[$row['PolisType_id']] = $row;
		}

		$result->result_id = null;
		unset($result->result_id);
		$result = null;
		unset($result);

		$this->dataPolisType = $dataPolisType;
		unset($dataPolisType);

		return $this->dataPolisType;

	}



	// -----------------------------------------------------------------------------------------------------------------
	// Lpu_RegNum

	/**
	 * @param $AttachLpu_id
	 * @return bool
	 */
	private function _createLpu_RegNum($AttachLpu_id){

		$Lpu_RegNum = '000000';

		if ( ! empty($AttachLpu_id) ) {
			$Lpu_RegNum = $this->getFirstResultFromQuery('select top 1 Lpu_f003mcod from v_Lpu (nolock) where Lpu_id = :AttachLpu_id', array('AttachLpu_id' => $AttachLpu_id));
		}

		$this->Lpu_RegNum = $Lpu_RegNum;

		return true;
	}

	/**
	 * @return string
	 */
	private function _getLpu_RegNum(){
		return $this->Lpu_RegNum;
	}
	// -----------------------------------------------------------------------------------------------------------------




	// -----------------------------------------------------------------------------------------------------------------
    // Файл экспорта "CSV" (основной файл)

	/**
	 * @return string
	 */
	private function _createFileCSV(){

    	if($this->isExistFileCSV != true){

			$Lpu_RegNum = $this->_getLpu_RegNum();

			$this->fileNameCSV = "MO2".$Lpu_RegNum.date('Ymd', time());

			// Создаем директорию
			$dirExport = $this->_getDirExport();
			$this->filePathCSV = $dirExport."/".$this->fileNameCSV.".csv";

			$this->isExistFileCSV = true;
		}

		return true;
	}

	/**
	 * @return bool|null|resource
	 */
	private function _doFopenCSV(){

    	if($this->isFopenCSV != true){
			$this->_createFileCSV();
			$attached_list_file_path = $this->_getFilePathCSV();
			$this->fpCSV = fopen($attached_list_file_path, 'w');
			$this->isFopenCSV = true;
		}

		return $this->fpCSV;
	}

	/**
	 * @return bool
	 */
	private function _doFcloseCSV(){

		fclose($this->fpCSV);
		$this->isFcloseCSV = true;

		return true;
	}

	/**
	 * @param $data
	 * @param bool $convertUTF8ToWin1251
	 * @return bool
	 */
	private function _putRowToFileCSV($data, $convertUTF8ToWin1251 = true){

    	if($convertUTF8ToWin1251 == true){
			array_walk_recursive($data, 'ConvertFromUTF8ToWin1251', true);
		}

		$fp = $this->_doFopenCSV();

		fputs($fp, '"' . implode('";"', toAnsi($data)) . '"' . "\r\n");
		//fputcsv($fp, toAnsi($data), ';');


		return true;
	}
	// -----------------------------------------------------------------------------------------------------------------




	// -----------------------------------------------------------------------------------------------------------------
	// Файл экспорта "Ошибки"

	/**
	 * @param $row
	 * @param int $i
	 * @return string
	 */
	private function _processingRowToFileError($row, $i = 0){

		//array_walk_recursive($row, 'ConvertFromUTF8ToWin1251', true);

		//$row['FAM'] = iconv('windows-1251', 'utf-8//IGNORE', $row['FAM']);
		//$row['IM'] = iconv('windows-1251', 'utf-8//IGNORE', $row['IM']);
		//$row['OT'] = iconv('windows-1251', 'utf-8//IGNORE', $row['OT']);
		//$row['polis_info'] = iconv('windows-1251', 'utf-8//IGNORE', $row['polis_info']);

		$error = "\r\n№ {$i}\r\n".
			'ФИО: '.$row['FAM'].' '.$row['IM'].' '.$row['OT']."\r\n".
			'ДР: '.$row['DR']."\r\n".
			'СНИЛС: '.$row['Person_Snils']."\r\n".
			'Полис: '.$row['polis_info']."\r\n".
			'Дата прикрепления: '.$row['PersonCard_begDate']."\r\n".
			'Дата открепления: '.$row['PersonCard_endDate']."\r\n".
			'Номер участка: '.$row['LpuRegion_Name']."\r\n"
		;

		return $error;//iconv('utf-8', 'windows-1251//IGNORE', $error);
	}

	/**
	 * @return bool|null|resource
	 */
	private function _doFopenError(){

		if($this->isFopenError != true){
			$this->_createFileError();
			$attached_list_errors_file_path = $this->_getFilePathError();
			$this->fpError = fopen($attached_list_errors_file_path, 'w');
			$this->isFopenError = true;
		}

		return $this->fpError;
	}

	/**
	 * @return bool
	 */
	private function _doFcloseError(){

		fclose($this->fpError);
		$this->isFcloseError = true;

		return true;
	}

	/**
	 * Создаем файл ошибок
	 * @return bool
	 */
	private function _createFileError(){

		if($this->isExistFileError != true){

			// количество записей, но при добавлении построчно мы не можем узнать кол-во без доп. запроса
			// как вариант - переименовать файл после окончания записи, но так ли это важно????? пока оставим так
			$i = 0;

			$Lpu_RegNum = $this->_getLpu_RegNum();


			$this->fileNameError = "MO2".$Lpu_RegNum.date('Ymd', time()).'_0_'.iconv('utf-8', 'cp866', 'ошибки');

			$dirExport = $this->_getDirExport();
			$this->filePathError = $dirExport."/".$this->fileNameError.".txt";

			$this->isExistFileError = true;
		}

		return true;
	}

	/**
	 * Записываем ошибки в файл
	 * @param $error
	 * @return bool
	 */
	private function _putRowToFileError($error){

		$fp = $this->_doFopenError();

		fwrite($fp, $error);

		return true;
	}

	/**
	 * Переименовываем файл ошибок для того, чтобы название содержало кол-во ошибок
	 * @param int $count_errors
	 * @return bool
	 */
	private function _renameFileError($count_errors = 0){

		$fileName = $this->_getFileNameError();
		$filePath = $this->_getFilePathError();

		if( ! file_exists($filePath)){
			return false;
		}

		$fileNameNew = str_replace('_0_', '_'.($count_errors).'_', $fileName);
		$this->fileNameError = $fileNameNew;


		$filePathNew = str_replace('_0_', '_'.($count_errors).'_', $filePath);
		$this->filePathError = $filePathNew;


		file_put_contents($filePathNew, file_get_contents($filePath));

		// почему-то нет прав доступа???
		// unlink($filePath);

		return true;
	}
	// -----------------------------------------------------------------------------------------------------------------




	// END loadAttachedListCSV
	// =================================================================================================================














    /**
     *	Берет название СМО для печати списка прикрепленного населения
     */
    function printAttachedList($data)
    {
        $params = array
        (
            'OrgSMO_id' => $data['OrgSMO_id'],
        );
        //var_dump($data);
        $query = "
            select
                OrgSMO_Name
            from
                v_OrgSmo with (nolock)
            where
                OrgSMO_id = :OrgSMO_id
        ";
        //echo getDebugSQL($query, $data); exit();
        $result = $this->db->query($query, $params);
        if ( is_object($result) ) {
            return $result->result('array');
        } else {
            return false;
        }
    }

	/**
	 * Запрос для выгрузки списка прикрепленного населения за период
	 */
	function exportPersonAttaches($data)
	{
		if(isset($data['ExportDateRange'][0])){
			$begDate = $data['ExportDateRange'][0];
		}
		if(isset($data['ExportDateRange'][1])){
			$endDate = $data['ExportDateRange'][1];
		}
		if(isset($data['AttachesLpu_id'])){
			$AttachesLpu_id = $data['AttachesLpu_id'];
		}
		$query = "
			select
				ISNULL(FAM,'') as FAM,
				ISNULL(IM,'') as IM,
				ISNULL(OT,'') as OT,
				convert(varchar, cast(DR as datetime),104) as DR,
				W,
				ISNULL(SPOL,'') as SPOL,
				ISNULL(NPOL,'') as NPOL,
				ISNULL(Q,'') as Q,
				ISNULL(LPU,'') as LPU,
				convert(varchar, cast(LPUDZ as datetime),104) as LPUDZ,
				convert(varchar, cast(LPUDT as datetime),104) as LPUDT,
				convert(varchar, cast(LPUDX as datetime),104) as LPUDX,
				LPUTP,
				ISNULL(OKATO,'') as OKATO,
				ISNULL(RNNAME,'') as RNNAME,
				ISNULL(NPNAME,'') as NPNAME,
				ISNULL(UL,'') as UL,
				ISNULL(DOM,'') as DOM,
				ISNULL(KORP,'') as KORP,
				ISNULL(KV,'') as KV,
				ISNULL(STATUS,'') as STATUS,
				ISNULL(STATUS,'') as ERR,
				ISNULL(STATUS,'') as RSTOP
		from [r3].[PersonCard_List](".$AttachesLpu_id.",'".$begDate."','".$endDate."')";
		$result = $this->db->query($query, array());
		return $result;
	}

	/**
	 * @param $data
	 * @return mixed
	 */
	function getInfoForAttachesFile($data)
	{
		$params = array();
		if(isset($data['AttachesLpu_id']))
			$params['AttachesLpu_id'] = $data['AttachesLpu_id'];
		$query = "
			select
				ISNULL(Lpu_f003mcod,'') as Lpu_f003mcod,
				REPLACE(convert(varchar(10), dbo.tzGetDate(), 126), '-', '') as file_date
			from v_Lpu with(nolock)
			where Lpu_id = :AttachesLpu_id
		";
		$result = $this->db->query($query,$params);
		return $result->result('array');
	}

	/**
	 * Проверка наличия дисп карт в другом МО (по гинекологии) https://redmine.swan.perm.ru/issues/72643
	 */
	function checkPersonDisp($data)
	{
		$params = array(
			'Lpu_id' => $data['Lpu_id'],
			'Person_id' => $data['Person_id']
		);
		$sql = "
			SELECT count(PersonDisp_id) as ctn
			FROM dbo.PersonDisp AS PD WITH (NOLOCK)
			INNER JOIN dbo.v_MedStaffFact AS MSF WITH (NOLOCK) ON MSF.LpuSection_id = PD.LpuSection_id AND MSF.MedPersonal_id = PD.MedPersonal_id
			INNER JOIN dbo.v_MedSpecOms AS MSO WITH (NOLOCK) ON MSO.MedSpecOms_id = MSF.MedSpecOms_id AND MSO.MedSpec_id=10
			WHERE
				PD.Person_id=:Person_id
				AND PD.PersonDisp_endDate IS NULL
				AND PD.Lpu_id<>:Lpu_id
		";
		$result = $this->db->query($sql,$params);
		if(is_object($result))
		{
			return $result->result('array');
		}
		return false;
	}

	/**
	 * Получение данных для рассылки уведомлений
	 */
	function getDataForMessages($person_id, $old_lpu_id, $new_lpu_id){
		//http://promed/?c=PersonCard&m=sendMessages&Person_id=1376&Lpu_old_id=10010892&Lpu_new_id=591805
		$result = array();
		$lpu_data_query = "
			select
				L.Org_Nick,
				isnull(L.Lpu_f003mcod,'') as Lpu_f003mcod,
				RTRIM(O.Org_Email) as Org_Email
			from v_Lpu L (nolock)
			left join v_Org O (nolock) on O.Org_id = L.Org_id
			where L.Lpu_id = :Lpu_id
		";
		$person_data_query = "
			select top 1
				PS.Polis_id
				,PS.PAddress_id
				,PS.UAddress_id
				,PC.Lpu_id
				,RTRIM(PS.Person_SurName) + ' ' + RTRIM(PS.Person_FirName) + ' ' + ISNULL(RTRIM(PS.Person_SecName),'') as Person_FIO
				,RTRIM(PS.Person_SurName) as Person_SurName
				,RTRIM(PS.Person_FirName) as Person_FirName
				,ISNULL(RTRIM(PS.Person_SecName),'') as Person_SecName
				,convert(varchar(10), PS.Person_BirthDay, 120) as Person_BirthDay
				,ISNULL(PS.Person_Snils,'') as Person_Snils
				,ORG.Org_Email as OrgSmo_Email
				,convert(varchar, cast(PC.PersonCard_begDate as datetime),104) as PersonCard_begDate
				,ISNULL(PC.PersonCard_Code,'') as PersonCard_Code
			from
				v_PersonState PS with(nolock)
				outer apply(
					select top 1
						PC.Lpu_id,
						PC.PersonCard_begDate,
						ISNULL(PAC.PersonAmbulatCard_Num,PC.PersonCard_Code) as PersonCard_Code
					from v_PersonCard PC with(nolock)
					left join v_PersonAmbulatCardLink PCAL (nolock) on PCAL.PersonCard_id = PC.PersonCard_id
					left join v_PersonAmbulatCard PAC (nolock) on PAC.PersonAmbulatCard_id = PCAL.PersonAmbulatCard_id
					where
						PC.Person_id = PS.Person_id
						and PC.LpuAttachType_id = 1
					order by
						PC.PersonCard_begDate desc
				) as PC
				left join v_Polis Polis with(nolock) on Polis.Polis_id = PS.Polis_id
				left join v_OrgSmo SMO with(nolock) on SMO.OrgSmo_id = Polis.OrgSmo_id
				left join v_Org Org with(nolock) on Org.Org_id = SMO.Org_id
				where PS.Person_id = :Person_id
		";

		if($old_lpu_id != 0){
			$old_lpu_data = $this->db->query($lpu_data_query,array('Lpu_id' => $old_lpu_id));
			if(is_object($old_lpu_data))
				$result['old_lpu_data'] = $old_lpu_data->result('array');
		}

		$new_lpu_data = $this->db->query($lpu_data_query,array('Lpu_id' => $new_lpu_id));
		if(is_object($new_lpu_data))
			$result['new_lpu_data'] = $new_lpu_data->result('array');


		$person_data = $this->db->query($person_data_query,array('Person_id' => $person_id));
		if(is_object($person_data))
			$result['person_data'] = $person_data->result('array');
		return $result;
	}

	/**
	 * Выгрузка списка прикрепленного населения за период
	 */
	function exportPersonCardForPeriod($data) {
		$query = "
			declare
				@BegDT datetime = :ExportDateRange_0,
				@EndDT datetime = :ExportDateRange_1,
				@Lpu_id bigint = :Lpu_id;

			select
				ps.Person_EdNum as Enp,
				ps.Person_Surname as fam,
				ps.Person_Firname as im,
				ps.Person_Secname as ot,
				ps.Person_Birthday as dr,
				sx.Sex_fedid as w,
				dt.DocumentType_Code as doctype,
				d.Document_Ser as docser,
				d.Document_Num as docnum,
				ps.Person_Snils as snils,
				pt.PolisType_CodeF008 as vpolis,
				pls.Polis_Ser as spolis,
				pls.Polis_Num as npolis,
				l.Lpu_f003mcod as codmof,
				case
					when pc.PersonCardAttach_id is not null then 2 -- по заявлению застрахованного лица
					else 1 -- по территориально-участковому принципу
				end as attach_type,
				pc.PersonCard_begDate as attach_dt_mo,
				null as detach_codmof,
				pc.PersonCard_endDate as detach_dt_mo,
				ccc.CardCloseCause_Code as detach_mo_cause,
				BCODE.AttributeValue_ValueString as cod_podr,
				LCODE.AttributeValue_ValueString as cod_otd,
				lr.LpuRegion_Name as cod_uch,
				null as cod_pun,
				msr.typeD as typed_vr1,
				msr.Person_Snils as snils_vr1,
				pc.PersonCard_begDate as attach_dt_vr1,
				BFCODE.AttributeValue_ValueString as cod_podr_f,
				LFCODE.AttributeValue_ValueString as cod_otd_f,
				lrf.LpuRegion_Name as cod_uch_f,
				null as cod_pun_f,
				msrf.typeD as typed_vr2,
				msrf.Person_Snils as snils_vr2,
				case when pc.LpuRegion_fapid is not null then pc.PersonCard_begDate end as attach_dt_vr2,
				case when pc.LpuRegion_fapid is not null then pc.PersonCard_endDate end as detach_dt_vr2,
				ccc.CardCloseCause_Code as detach_vr2_cause
			from dbo.v_PersonCard_all pc with (nolock)
				inner join dbo.v_PersonState ps with (nolock) on ps.Person_id = pc.Person_id
				inner join dbo.v_Sex sx with (nolock) on sx.Sex_id = ps.Sex_id
				inner join dbo.v_Lpu l with (nolock) on pc.Lpu_id = l.Lpu_id
				inner join dbo.v_Document d with (nolock) on d.Document_id = ps.Document_id
				inner join dbo.v_DocumentType dt with (nolock) on dt.DocumentType_id = d.DocumentType_id
				inner join dbo.v_Polis pls with (nolock) on pls.Polis_id = ps.Polis_id
				inner join dbo.v_PolisType pt with (nolock) on pt.PolisType_id = pls.PolisType_id
				left join dbo.v_LpuRegion lr with (nolock) on lr.LpuRegion_id = pc.LpuRegion_id
				left join dbo.v_LpuRegionType lrt with (nolock) on lrt.LpuRegionType_id = pc.LpuRegionType_id
				outer apply (
					select top 1
						AV.AttributeValue_ValueString
					from
						v_AttributeValue AV with(nolock)
						inner join v_AttributeSignValue ASV with(nolock) on AV.AttributeSignValue_id = ASV.AttributeSignValue_id
						inner join v_AttributeSign [AS] with(nolock) on [AS].AttributeSign_id = ASV.AttributeSign_id
						inner join v_Attribute A with(nolock) on A.Attribute_id = AV.Attribute_id
					where
						[AS].AttributeSign_TableName like 'dbo.LpuSection'
						and ASV.AttributeSignValue_TablePKey = lr.LpuSection_id
						and [AS].AttributeSign_id = 1
						and a.Attribute_SysNick = 'Section_Code'
				) LCODE
				outer apply (
					select top 1
						AV.AttributeValue_ValueString
					from
						v_AttributeValue AV with(nolock)
						inner join v_AttributeSignValue ASV with(nolock) on AV.AttributeSignValue_id = ASV.AttributeSignValue_id
						inner join v_AttributeSign [AS] with(nolock) on [AS].AttributeSign_id = ASV.AttributeSign_id
						inner join v_Attribute A with(nolock) on A.Attribute_id = AV.Attribute_id
					where
						[AS].AttributeSign_TableName like 'dbo.LpuSection'
						and ASV.AttributeSignValue_TablePKey = lr.LpuSection_id
						and [AS].AttributeSign_id = 1
						and a.Attribute_SysNick = 'Building_Code'
				) BCODE
				left join dbo.v_CardCloseCause ccc with (nolock) on pc.CardCloseCause_id = ccc.CardCloseCause_id
				left join dbo.v_LpuRegion lrf with (nolock) on lrf.LpuRegion_id = pc.LpuRegion_fapid-- участок фап
				outer apply (
					select top 1
						AV.AttributeValue_ValueString
					from
						v_AttributeValue AV with(nolock)
						inner join v_AttributeSignValue ASV with(nolock) on AV.AttributeSignValue_id = ASV.AttributeSignValue_id
						inner join v_AttributeSign [AS] with(nolock) on [AS].AttributeSign_id = ASV.AttributeSign_id
						inner join v_Attribute A with(nolock) on A.Attribute_id = AV.Attribute_id
					where
						[AS].AttributeSign_TableName like 'dbo.LpuSection'
						and ASV.AttributeSignValue_TablePKey = lrf.LpuSection_id
						and [AS].AttributeSign_id = 1
						and a.Attribute_SysNick = 'Section_Code'
				) LFCODE
				outer apply (
					select top 1
						AV.AttributeValue_ValueString
					from
						v_AttributeValue AV with(nolock)
						inner join v_AttributeSignValue ASV with(nolock) on AV.AttributeSignValue_id = ASV.AttributeSignValue_id
						inner join v_AttributeSign [AS] with(nolock) on [AS].AttributeSign_id = ASV.AttributeSign_id
						inner join v_Attribute A with(nolock) on A.Attribute_id = AV.Attribute_id
					where
						[AS].AttributeSign_TableName like 'dbo.LpuSection'
						and ASV.AttributeSignValue_TablePKey = lrf.LpuSection_id
						and [AS].AttributeSign_id = 1
						and a.Attribute_SysNick = 'Building_Code'
				) BFCODE
				outer apply (
					select top 1
						p.Person_Snils,
						typeD = case when ms.MedSpec_pid = 196 then 2 when isnull(ms.MedSpec_pid, 0) <> 196 then 1 end
					from v_MedStaffRegion MedStaffRegion with (nolock)
						inner join v_MedStaffFact MedStaffFact with (nolock) on MedStaffFact.MedStaffFact_id = MedStaffRegion.MedStaffFact_id
						left join MedSpecOms mso on mso.MedSpecOms_id = MedStaffFact.MedSpecOms_id
						left join fed.MedSpec ms on ms.MedSpec_id = mso.MedSpec_id
						inner join v_PersonState p on p.Person_id = MedStaffFact.Person_id
					where
						MedStaffFact.Lpu_id = pc.Lpu_id
						and MedStaffRegion.LpuRegion_id = pc.LpuRegion_id
						and cast(MedStaffRegion.MedStaffRegion_begDate as date) <= @EndDT
						and ISNULL(MedStaffRegion.MedStaffRegion_endDate, @EndDT) >= @BegDT
					order by
						MedStaffRegion.MedStaffRegion_isMain desc,
						MedStaffRegion.MedStaffRegion_begDate desc
				) msr
				outer apply (
					select top 1
						p.Person_Snils,
						2 as typeD
					from v_MedStaffRegion MedStaffRegion with (nolock)
						inner join v_MedStaffFact MedStaffFact with (nolock) on MedStaffFact.MedStaffFact_id = MedStaffRegion.MedStaffFact_id
						left join MedSpecOms mso on mso.MedSpecOms_id = MedStaffFact.MedSpecOms_id
						left join fed.MedSpec ms on ms.MedSpec_id = mso.MedSpec_id
							and ms.MedSpec_pid = 196
						inner join v_PersonState p on p.Person_id = MedStaffFact.person_id
					where
						MedStaffFact.Lpu_id = pc.Lpu_id
						and MedStaffRegion.LpuRegion_id = pc.LpuRegion_fapid
						and cast(MedStaffRegion.MedStaffRegion_begDate as date) <= @EndDT
						and ISNULL(MedStaffRegion.MedStaffRegion_endDate, @EndDT) >= @BegDT
					order by
						MedStaffRegion.MedStaffRegion_isMain desc,
						MedStaffRegion.MedStaffRegion_begDate desc
				) msrf
			where pc.LpuAttachType_id = 1
				and pc.PersonCard_begDate between @BegDT and @EndDT
				and l.Lpu_id = @Lpu_id
				--and pc.PersonCard_endDate is null
		";

		$result = $this->db->query($query, array(
			'ExportDateRange_0' => $data['ExportDateRange'][0],
			'ExportDateRange_1' => $data['ExportDateRange'][1],
			'Lpu_id' => $data['Lpu_id']
		));

		if ( !is_object($result) ) {
			return false;
		}

		return $result;
	}

	/**
	 * Возвращает список прикреплений пациента
	 * @module API
	 * @task https://redmine.swan.perm.ru/issues/106230
	 */
	public function getPersonAttach($data) {
		// Данные по пациенту
		$query = "
			select top 1
				ps.Person_Snils as PersonSnils_Snils,
				j.Org_id,
				j.Post_id
			from
				v_PersonState ps with (nolock)
				left join v_Job j with (nolock) on j.Job_id = ps.Job_id
			where
				ps.Person_id = :Person_id
		";
		$res = $this->db->query($query, array(
			'Person_id' => $data['Person_id'],
		));

		if ( !is_object($res) ) {
			return false;
		}

		$response = $res->result('array');

		if ( !is_array($response) || count($response) == 0 ) {
			return array();
		}

		// Список прикреплений пациента
		$filterList = array();
		$queryParams = array();

		$queryParams['Person_id'] = $data['Person_id'];
		$filterList[] = "pca.Person_id = :Person_id";

		if ( !empty($data['Lpu_id']) ) {
			$queryParams['Lpu_id'] = $data['Lpu_id'];
			$filterList[] = "pca.Lpu_id = :Lpu_id";
		}

		if ( !empty($data['LpuRegion_id']) ) {
			$queryParams['LpuRegion_id'] = $data['LpuRegion_id'];
			$filterList[] = "pca.LpuRegion_id = :LpuRegion_id";
		}

		if ( !empty($data['LpuAttachType_id']) ) {
			$queryParams['LpuAttachType_id'] = $data['LpuAttachType_id'];
			$filterList[] = "pca.LpuAttachType_id = :LpuAttachType_id";
		}

		$query = "
			select
				pca.PersonCard_id,
				convert(varchar(10), pca.PersonCard_begDate, 120) as PersonCard_begDate,
				convert(varchar(10), pca.PersonCard_endDate, 120) as PersonCard_endDate,
				pca.PersonCard_Code,
				pca.Lpu_id,
				pca.LpuRegion_id,
				pca.LpuAttachType_id,
				pc.PersonCard_isAttachAuto,
				pca.CardCloseCause_id,
				convert(varchar(10), pc.PersonCard_AttachAutoDT, 120) as PersonCard_AttachAutoDT,
				pca.PersonCard_isAttachCondit
			from
				v_PersonCard_All pca with (nolock)
				left join PersonCard pc with (nolock) on pc.PersonCard_id = pca.PersonCard_id
			where
				" . implode(' and ', $filterList) . "
		";
		$res = $this->db->query($query, $queryParams);

		if ( !is_object($res) ) {
			return false;
		}

		$response[0]['attach_data'] = $res->result('array');

		return $response;
	}

	/**
	 * Возвращает список изменений прикреплений пациентов за период
	 * @module API
	 * @task https://redmine.swan.perm.ru/issues/131648
	 */
	public function getPersonAttachList($data) {
		$begDate = DateTime::createFromFormat('Y-m-d', $data['begDate']);
		$endDate = DateTime::createFromFormat('Y-m-d', $data['endDate']);

		if ( $begDate > $endDate ) {
			return false;
		}

		$currentHour = intval(date('H'));
		$dateDiff = $endDate->diff($begDate);
		$filterList = array();

		if ( $currentHour >= 7 && $currentHour <= 20 ) {
			// Дневной режим, ограничение периода - 1 день
			if ( $dateDiff->days > 1 ) {
				$endDate = DateTime::createFromFormat('Y-m-d', $data['begDate']);
				$endDate->add(new DateInterval('P1D'));
			}
		}
		else {
			// Ночной режим, ограничение периода - 32 дня
			if ( $dateDiff->days > 32 ) {
				$endDate = DateTime::createFromFormat('Y-m-d', $data['begDate']);
				$endDate->add(new DateInterval('P32D'));
			}
		}

		$data['begDate'] = $begDate->format('Y-m-d');
		$data['endDate'] = $endDate->format('Y-m-d');

		if ( !empty($data['LpuAttachType_id']) ) {
			$filterList[] = "t1.LpuAttachType_id = :LpuAttachType_id";
		}

		if ( isset($data['PersonCardAttach']) && in_array($data['PersonCardAttach'], array(0, 1)) ) {
			switch ( $data['PersonCardAttach'] ) {
				case 0:
					$filterList[] = "t1.PersonCardAttach_id is null";
					break;

				case 1:
					$filterList[] = "t1.PersonCardAttach_id is not null";
					break;
			}
		}

		$tmpTableName = "#tmp" . time();

		$query = "

		-- variables
			set nocount on;

			declare
				@AttributeSign_id bigint = (select top 1 AttributeSign_id from v_AttributeSign with (nolock) where AttributeSign_Code = 1 and AttributeSign_TableName = 'dbo.LpuSection'),
				@curDate datetime = dbo.tzGetDate(),
				@begDate datetime = :begDate,
				@endDate datetime = :endDate;

			IF OBJECT_ID(N'tempdb..{$tmpTableName}', N'U') IS NOT NULL
				DROP TABLE {$tmpTableName};

			create table {$tmpTableName} (
				PersonCard_id bigint,
				PersonCard_begDate datetime,
				PersonCard_endDate datetime,
				CardCloseCause_id bigint,
				PersonCard_isAttachCondit bigint,
				PersonCardAttach_id bigint,
				Person_id bigint,
				Lpu_id bigint,
				LpuRegion_id bigint,
				LpuRegion_fapid bigint,
				PersonCard_updDate datetime
			);

			with LpuRegionChanges as (
				select LpuRegion_id, cast(LpuRegion_updDT as date) as PersonCard_updDate
				from v_LpuRegion with (nolock)
				where cast(LpuRegion_updDT as date) between @begDate and @endDate
			)

			insert into {$tmpTableName} (PersonCard_id, PersonCard_begDate, PersonCard_endDate, CardCloseCause_id, PersonCard_isAttachCondit, PersonCardAttach_id,
				Person_id, Lpu_id, LpuRegion_id, LpuRegion_fapid, PersonCard_updDate)

			select t1.PersonCard_id, t1.PersonCard_begDate, t1.PersonCard_endDate, t1.CardCloseCause_id, t1.PersonCard_isAttachCondit, t1.PersonCardAttach_id,
				t1.Person_id, t1.Lpu_id, t1.LpuRegion_id, t1.LpuRegion_fapid, cast(t1.PersonCardBeg_updDT as date) as PersonCard_updDate
			from v_PersonCard_all t1 with (nolock)
			where cast(t1.PersonCardBeg_updDT as date) between @begDate and @endDate
				" . (count($filterList) > 0 ? 'and ' . implode(' and ', $filterList) : '') . "

			union

			select t1.PersonCard_id, t1.PersonCard_begDate, t1.PersonCard_endDate, t1.CardCloseCause_id, t1.PersonCard_isAttachCondit, t1.PersonCardAttach_id,
				t1.Person_id, t1.Lpu_id, t1.LpuRegion_id, t1.LpuRegion_fapid, cast(t1.PersonCardEnd_updDT as date) as PersonCard_updDate
			from v_PersonCard_all t1 with (nolock)
			where cast(t1.PersonCardEnd_updDT as date) between @begDate and @endDate
				" . (count($filterList) > 0 ? 'and ' . implode(' and ', $filterList) : '') . "

			union

			select t1.PersonCard_id, t1.PersonCard_begDate, t1.PersonCard_endDate, t1.CardCloseCause_id, t1.PersonCard_isAttachCondit, t1.PersonCardAttach_id,
				t1.Person_id, t1.Lpu_id, t1.LpuRegion_id, t1.LpuRegion_fapid, t2.PersonCard_updDate
			from v_PersonCard t1 with (nolock)
				inner join LpuRegionChanges t2 on t2.LpuRegion_id = t1.LpuRegion_id
			" . (count($filterList) > 0 ? 'where ' . implode(' and ', $filterList) : '') . "

			union

			select t1.PersonCard_id, t1.PersonCard_begDate, t1.PersonCard_endDate, t1.CardCloseCause_id, t1.PersonCard_isAttachCondit, t1.PersonCardAttach_id,
				t1.Person_id, t1.Lpu_id, t1.LpuRegion_id, t1.LpuRegion_fapid, t2.PersonCard_updDate
			from v_PersonCard t1 with (nolock)
				inner join LpuRegionChanges t2 on t2.LpuRegion_id = t1.LpuRegion_fapid
			" . (count($filterList) > 0 ? 'where ' . implode(' and ', $filterList) : '') . ";

			set nocount off;
		-- end variables
			select
		-- select
				p.BDZ_id,
				ps.Person_id,
				ps.Person_SurName as PersonSurName_SurName,
				ps.Person_FirName as PersonFirName_FirName,
				ps.Person_SecName as PersonSecName_SecName,
				convert(varchar(10), ps.Person_BirthDay, 120) as PersonBirthDay_BirthDay,
				ps.Sex_id as Person_Sex_id,
				ps.Person_Snils as PersonSnils_Snils,

				-- Документ, удостоверяющий личность
				d.Document_id,
				d.DocumentType_id,
				d.Document_Ser,
				d.Document_Num,

				-- Полис
				pls.Polis_id,
				pls.PolisType_id,
				pls.Polis_Ser,
				pls.Polis_Num,
				os.OrgSMO_f002smocod as OrgSmoCode,
				convert(varchar(10), pls.Polis_begDate, 120) as Polis_BegDate,
				convert(varchar(10), pls.Polis_endDate, 120) as Polis_EndDate,
				ps.Person_EdNum as ENP,
				pcc.PolisCloseCause_id,
				pcc.PolisCloseCause_Name,

				-- Данные прикрепления
				pc.PersonCard_id,
				convert(varchar(10), pc.PersonCard_begDate, 120) as PersonCard_begDate,
				convert(varchar(10), pc.PersonCard_endDate, 120) as PersonCard_endDate,
				ccc.CardCloseCause_id,
				ccc.CardCloseCause_Name,
				case when pc.PersonCardAttach_id is not null then 1 else 2 end as PersonCard_isAttachCondit,
				l.Lpu_id,
				l.Lpu_f003mcod as Lpu_Code,
				lr.LpuRegion_id,
				lr.LpuRegion_Name,
				lr.LpuRegion_tfoms,
				ls.LpuSection_id,
				lb.LpuBuilding_id,
				lstc.LpuSection_CodeTFOMS,
				lbtc.LpuBuilding_CodeTFOMS,
				coalesce(lrmsf.Person_Snils, lrmsf2.Person_Snils) as Doc_Snils,
				case
					when lrmsf.PostKind_id = 1 then 1
					when lrmsf.PostKind_id = 6 then 2
					else null
				end as Doc_Type,
				lrf.LpuRegion_id as LpuRegionF_id,
				lrf.LpuRegion_Name as LpuRegionF_Name,
				lrf.LpuRegion_tfoms as LpuRegionF_tfoms,
				lsf.LpuSection_id as LpuSectionF_id,
				lbf.LpuBuilding_id as LpuBuildingF_id,
				lsftc.LpuSectionF_CodeTFOMS,
				lbftc.LpuBuildingF_CodeTFOMS,
				lrmsff.Person_Snils as DocF_Snils,
				case
					when lrmsff.PostKind_id = 1 then 1
					when lrmsff.PostKind_id = 6 then 2
					else null
				end as DocF_Type
		-- end select
			from
		-- from
				{$tmpTableName} pc with (nolock)
				inner join v_Lpu l with (nolock) on l.Lpu_id = pc.Lpu_id
				left join v_CardCloseCause ccc (nolock) on ccc.CardCloseCause_id = pc.CardCloseCause_id
				cross apply (
					select top 1 *
					from v_Person_all with (nolock)
					where Person_id = pc.Person_id
						and PersonEvn_insDT <= pc.PersonCard_updDate
					order by
						PersonEvn_insDT desc
				) ps
				inner join v_Person p with (nolock) on p.Person_id = ps.Person_id
				left join v_Document d with (nolock) on d.Document_id = ps.Document_id
				left join v_Polis pls with (nolock) on pls.Polis_id = ps.Polis_id
				left join v_OrgSMO os with (nolock) on os.OrgSMO_id = pls.OrgSMO_id
				left join v_PolisCloseCause pcc with (nolock) on pcc.PolisCloseCause_id = pls.PolisCloseCause_id
				left join v_LpuRegion lr with (nolock) on lr.LpuRegion_id = pc.LpuRegion_id
				left join v_LpuSection ls with(nolock) on ls.LpuSection_id = lr.LpuSection_id
				left join v_LpuBuilding lb with(nolock) on lb.LpuBuilding_id = ls.LpuBuilding_id
				outer apply (
					select top 1 AV.AttributeValue_ValueString as LpuSection_CodeTFOMS
					from
						v_AttributeValue AV with (nolock)
						inner join v_AttributeSignValue ASV on ASV.AttributeSignValue_id = AV.AttributeSignValue_id
						inner join v_Attribute A on A.Attribute_id = AV.Attribute_id
					where
						ASV.AttributeSign_id = @AttributeSign_id
						and ASV.AttributeSignValue_TablePKey = ls.LpuSection_id
						and A.Attribute_SysNick = 'Section_Code'
				) lstc
				outer apply (
					select top 1 AV.AttributeValue_ValueString as LpuBuilding_CodeTFOMS
					from
						v_AttributeValue AV with (nolock)
						inner join v_AttributeSignValue ASV on ASV.AttributeSignValue_id = AV.AttributeSignValue_id
						inner join v_Attribute A on A.Attribute_id = AV.Attribute_id
					where
						ASV.AttributeSign_id = @AttributeSign_id
						and ASV.AttributeSignValue_TablePKey = ls.LpuSection_id
						and A.Attribute_SysNick = 'Building_Code'
				) lbtc
				outer apply (
					select top 1 t2.Person_Snils, t3.PostKind_id
					from v_MedStaffRegion t1 with(nolock)
						inner join v_MedStaffFact t3 with (nolock) on t3.MedStaffFact_id = t1.MedStaffFact_id
						inner join v_MedPersonal t2 with(nolock) on t2.MedPersonal_id = t3.MedPersonal_id
					where t1.LpuRegion_id = pc.LpuRegion_id
						and t2.Person_Snils is not null
						and t3.Lpu_id = pc.Lpu_id
						and (t3.WorkData_begDate is null or t3.WorkData_begDate <= @curDate)
						and (t3.WorkData_endDate is null or t3.WorkData_endDate >= @curDate)
						and (t1.MedStaffRegion_begDate is null or t1.MedStaffRegion_begDate <= @curDate)
						and (t1.MedStaffRegion_endDate is null or t1.MedStaffRegion_endDate >= @curDate)
					order by t3.PostKind_id
				) lrmsf
				outer apply (
					select top 1
						MP.Person_Snils
					from
						v_MedPersonal MP (nolock)
						inner join v_MedStaffRegion MSR (nolock) on MSR.LpuRegion_id = pc.LpuRegion_id and MSR.MedPersonal_id = MP.MedPersonal_id
					where
						MSR.MedStaffRegion_IsMain = 2
						and MSR.MedStaffRegion_endDate is null or MSR.MedStaffRegion_endDate >= @curDate
						and MSR.MedStaffRegion_begDate is null or MSR.MedStaffRegion_begDate <= @curDate
					order by
						MSR.MedStaffRegion_endDate
				) lrmsf2
				left join v_LpuRegion lrf with (nolock) on lrf.LpuRegion_id = pc.LpuRegion_fapid
				left join v_LpuSection lsf with(nolock) on lsf.LpuSection_id = lrf.LpuSection_id
				left join v_LpuBuilding lbf with(nolock) on lbf.LpuBuilding_id = lsf.LpuBuilding_id
				outer apply (
					select top 1 AV.AttributeValue_ValueString as LpuSectionF_CodeTFOMS
					from
						v_AttributeValue AV with (nolock)
						inner join v_AttributeSignValue ASV on ASV.AttributeSignValue_id = AV.AttributeSignValue_id
						inner join v_Attribute A on A.Attribute_id = AV.Attribute_id
					where
						ASV.AttributeSign_id = @AttributeSign_id
						and ASV.AttributeSignValue_TablePKey = lsf.LpuSection_id
						and A.Attribute_SysNick = 'Section_Code'
				) lsftc
				outer apply (
					select top 1 AV.AttributeValue_ValueString as LpuBuildingF_CodeTFOMS
					from
						v_AttributeValue AV with (nolock)
						inner join v_AttributeSignValue ASV on ASV.AttributeSignValue_id = AV.AttributeSignValue_id
						inner join v_Attribute A on A.Attribute_id = AV.Attribute_id
					where
						ASV.AttributeSign_id = @AttributeSign_id
						and ASV.AttributeSignValue_TablePKey = lsf.LpuSection_id
						and A.Attribute_SysNick = 'Building_Code'
				) lbftc
				outer apply (
					select top 1 t2.Person_Snils, t3.PostKind_id
					from v_MedStaffRegion t1 with(nolock)
						inner join v_MedStaffFact t3 with (nolock) on t3.MedStaffFact_id = t1.MedStaffFact_id
						inner join v_MedPersonal t2 with(nolock) on t2.MedPersonal_id = t3.MedPersonal_id
					where t1.LpuRegion_id = pc.LpuRegion_fapid
						and t2.Person_Snils is not null
						and t3.Lpu_id = pc.Lpu_id
						and (t3.WorkData_begDate is null or t3.WorkData_begDate <= @curDate)
						and (t3.WorkData_endDate is null or t3.WorkData_endDate >= @curDate)
						and (t1.MedStaffRegion_begDate is null or t1.MedStaffRegion_begDate <= @curDate)
						and (t1.MedStaffRegion_endDate is null or t1.MedStaffRegion_endDate >= @curDate)
					order by t3.PostKind_id
				) lrmsff
		-- end from

			order by
		-- order by
				p.BDZ_id,
				ps.Person_id
		-- end order by

		-- option
			set nocount on;
			DROP TABLE {$tmpTableName};
			set nocount off;
		-- end option
		";

		$limit = 10000;
		$start = ($data['PageNum'] - 1) * $limit;

		$result = $this->queryResult(getLimitSQLPH($query, $start, $limit), $data, true);
		$count = count($result);

		return array(
			'data' => $result,
			'ZAP' => $count
		);
	}

	/**
	 * Получение согласия на обработку персональных данных
	 * @module API
	 * @task https://redmine.swan.perm.ru/issues/106230
	 */
	public function getPersonLpuInfoIsAgree($data) {
		$filterList = array();
		$queryParams = array();

		$queryParams['Person_id'] = $data['Person_id'];
		$filterList[] = "pli.Person_id = :Person_id";

		if ( !empty($data['Lpu_id']) ) {
			$queryParams['Lpu_id'] = $data['Lpu_id'];
			$filterList[] = "pli.Lpu_id = :Lpu_id";
		}

		$query = "
			select top 1
				case when pli.PersonLpuInfo_IsAgree = 2 then 1 else 0 end as PersonLpuInfo_IsAgree
			from
				v_PersonLpuInfo pli (nolock)
			where
				" . implode(' and ', $filterList) . "
			order by
				pli.PersonLpuInfo_setDT desc
		";

		return $this->queryResult($query, $queryParams);
	}

	/**
	 * Получение списка прикреплений.  метод для API
	 */
	function getPersonCardAPI($data){

		$filters = '';

		if(!empty($data['Person_id'])){
			$filters .= ' AND pca.Person_id = :Person_id';
		}

		if(!empty($data['Lpu_id'])){
			$filters .= ' AND pca.Lpu_id = :Lpu_id';
		}

		if(!empty($data['LpuRegion_id'])){
			$filters .= ' AND pca.LpuRegion_id = :LpuRegion_id';
		}

		if(!empty($data['LpuRegion_fapid'])){
			$filters .= ' AND pca.LpuRegion_fapid = :LpuRegion_fapid';
		}

		if(!empty($data['Date_DT'])){
			$filters .= ' AND cast(convert(varchar(10), pc.PersonCard_begDate, 112) as datetime) < :Date_DT
					and (pc.PersonCard_endDate is null or pc.PersonCard_endDate >= :Date_DT)';
		}

		// если включен режим оффлайна
		if (!empty($data['isOffline'])) {

			$select = "
				pca.Person_id
			";

			$filters .= "
				and pca.PersonCard_endDate is null
			";

		} else {
			$select = "
				pca.Person_id,
				pca.PersonCardAttach_id,
				pca.PersonCard_id,
				pca.Lpu_id,
				pca.LpuRegion_id,
				pca.LpuAttachType_id,
				pca.PersonCard_Code,
				convert(varchar(10), pca.PersonCard_begDate, 104) as PersonCard_begDate,
				convert(varchar(10), pca.PersonCard_endDate, 104) as PersonCard_endDate,
				pca.CardCloseCause_id,
				pca.PersonCard_IsAttachCondit,
				pc.PersonCard_IsAttachAuto,
				pc.PersonCard_AttachAutoDT,
				pca.PersonCard_DmsPolisNum,
				pca.PersonCard_DmsBegDate,
				pca.PersonCard_DmsEndDate,
				pca.OrgSMO_id,
				pca.LpuRegion_fapid,
				pca.LpuRegionType_id,
				pca.MedStaffFact_id
			";
		}

		$sql = "
			SELECT
				{$select}
			FROM
				v_PersonCard_All pca with (nolock)
				left join PersonCard pc with (nolock) on pc.PersonCard_id = pca.PersonCard_id
			WHERE (1=1)
				AND pca.LpuAttachType_id = :LpuAttachType_id
				{$filters}
		";
		//echo getDebugSQL($sql, $data);die();
		$res = $this->db->query($sql, $data);

		if ( !is_object($res) ) return false;
		return $res->result('array');
	}

	/**
	 * Справочник RecMethodType
	 */
	function getRecMethodTypeCombo()
	{
		return $this->queryResult("
			select RecMethodType_id, RecMethodType_Code, RecMethodType_Name from dbo.RecMethodType with (nolock) where RecMethodType_id in (1,3,14,15,16)
		");
	}
}