<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 *
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2009 Swan Ltd.
*/
class QueryToDbfExporter_model extends swModel {

    private $queries = array();

    const OUT_SUBDIR = 'QueryToDbf/';//Каталог для сохранения dbf-файлов. Обязательно со слешем на конце!
    const SESSION_KEY = 'QueryToDbfExporter_session';//ключ для хранения всяких временных данных в сессии

	/**
	 * Конструктор
	 */
    public function __construct(){
        parent::__construct();
        $this->load->database();
        if (!array_key_exists($this::SESSION_KEY,$_SESSION)){
            $_SESSION[$this::SESSION_KEY] = array();
        }
        if (!array_key_exists('done',$_SESSION[$this::SESSION_KEY])){
            $_SESSION[$this::SESSION_KEY]['done'] = array();
        }
        $this->workingDir = EXPORTPATH_ROOT . self::OUT_SUBDIR;
        if (!is_dir($this->workingDir)){
            if (!$this->mkdir($this->workingDir)){
                throw new Exception("can't create dir " . $this->workingDir);
            }
        }
	    $this->declareQueries();
    }

	/**
	 * Функция
	 */
    private function declareQueries(){
	    $q = 'SELECT Query_id, Query_Nick, Filename, Name, Query, Ord FROM rls.exp_Query with(nolock) where Region_id is null or Region_id = dbo.getRegion() ORDER BY ord';
	    $queries = $this->db->query($q);
	    if ( is_object($queries) ) {
		    $queries = $queries->result('array');
		    foreach ($queries as $query) {
			    $this->queries[$query['Query_Nick']] = array(
				    'filename' => $query['Filename'],
				    'name' => $query['Name'],
				    'query' => $query['Query'],
			    );
			    $q1 = 'SELECT
					DbaseStructure_id,
					Query_id,
					rtrim(ltrim(Query_ColumnName)) as Query_ColumnName,
					rtrim(ltrim(Dbase_ColumnName)) as Dbase_ColumnName,
					Dbase_ColumnType,
					Dbase_ColumnLength,
					Dbase_ColumnPrecision,
					Description,
					Ord
				FROM
					[rls].[exp_DbaseStructure]
				WHERE
					Query_id = :Query_id
				ORDER BY ORD';
			    $p1 = array('Query_id' => $query['Query_id']);
			    $cols = $this->db->query($q1, $p1);
			    if ( is_object($cols) ) {
			        $cols = $cols->result('array');
				    foreach ($cols as $col) {
					    $this->queries[$query['Query_Nick']]['dbf_structure'][] = array(
						    $col['Dbase_ColumnName'],
						    $col['Dbase_ColumnType'],
						    $col['Dbase_ColumnLength'],
						    $col['Dbase_ColumnPrecision'],
						    'source_column' => $col['Query_ColumnName'],
						    'name' => $col['Description'],
					    );
				    }
			    } else {
				    throw new Exception('Не удалось выполнить запрос: '.getDebugSQL($q1, $p1).'<br />'.PHP_EOL.var_export(sqlsrv_errors(), true));
			    }
		    }
	    }
	    else {
		    throw new Exception('Не удалось выполнить запрос: '.$q);
	    }

	    /*$this->queries['Lpu'] = array (
            'params' => array(),
            'filename' => 'lpu.dbf',
            'name' => 'справочник ЛПУ',
            'dbf_structure' => array(
                array('MCOD'    ,'C', 7  , null, 'name' => trim('Код ЛПУ в кодировке ТФОМС     ')),
                array('TF-OKATO','N', 5  , null, 'name' => trim('Код территорий по ОКАТО       ')),
                array('C_OGRN'  ,'C', 15 , null, 'name' => trim('ОГРН ЛПУ                      ')),
                array('M_NAMES' ,'C', 50 , null, 'name' => trim('Наименование ЛПУ (краткое)    ')),
                array('M_NAMEF' ,'C', 150, null, 'name' => trim('Наименование ЛПУ              ')),
                array('POST_ID' ,'N', 6  , null, 'name' => trim('Почтовый индекс адреса ЛПУ    ')),
                array('ADRES'   ,'C', 200, null, 'name' => trim('Почтовый адрес (субъект РФ - р')),
                array('FAM_GV'  ,'C', 40 , null, 'name' => trim('Фамилия главного врача        ')),
                array('IM-GV'   ,'C', 40 , null, 'name' => trim('Имя                           ')),
                array('OT-GV'   ,'C', 40 , null, 'name' => trim('Отчество                      ')),
                array('FAM-BUX' ,'C', 40 , null, 'name' => trim('Фамилия главного бухгалтера   ')),
                array('IM-BUX'  ,'C', 40 , null, 'name' => trim('Имя                           ')),
                array('OT-BUX'  ,'C', 40 , null, 'name' => trim('Отчество                      ')),
                array('TEL'     ,'C', 40 , null, 'name' => trim('Телефон (с кодом города)      ')),
                array('FAX'     ,'C', 40 , null, 'name' => trim('Факс (с кодом города)         ')),
                array('E-MAIL'  ,'C', 30 , null, 'name' => trim('Адрес электронной почты       ')),
                array('DATE-B'  ,'D',null, null, 'name' => trim('Дата включения в справочник   ')),
                array('DATE-E'  ,'D',null, null, 'name' => trim('Дата исключения из справочника')),
            ),
            'query' => array("
				SELECT
				l.Lpu_Ouz AS MCOD,
				l.Lpu_OKATO AS 'TF-OKATO',
				l.Lpu_OGRN AS C_OGRN,
				l.Lpu_Nick AS M_NAMES,
				l.Lpu_Name AS M_NAMEF,
				a.Address_Zip AS POST_ID,
				dbo.Address_Compose(
				a.KLCountry_id,
				a.KLRgn_id,
				a.KLSubRgn_id,
				a.KLCity_id,
				a.KLTown_id,
				a.KLStreet_id,
				a.Address_House,
				a.Address_Corpus,
				a.Address_Flat
				) AS ADRES,
				SUBSTRING(REPLACE(REPLACE(l.Lpu_GlVrach,'.',' '),'  ', ' '), 1, CHARINDEX(' ', REPLACE(REPLACE(l.Lpu_GlVrach,'.',' '),'  ', ' ')) - 1) AS FAM_GV,
				SUBSTRING(
				REPLACE(REPLACE(l.Lpu_GlVrach,'.',' '),'  ', ' '),
				CHARINDEX(' ', REPLACE(REPLACE(l.Lpu_GlVrach,'.',' '),'  ', ' ')) + 1,
				CHARINDEX(' ', REPLACE(REPLACE(l.Lpu_GlVrach,'.',' '),'  ', ' '), CHARINDEX(' ', REPLACE(REPLACE(l.Lpu_GlVrach,'.',' '),'  ', ' ')) + 1) - CHARINDEX(' ', REPLACE(REPLACE(l.Lpu_GlVrach,'.',' '),'  ', ' ')) - 1
				) AS 'IM-GV',
				RTRIM(SUBSTRING(
				REPLACE(REPLACE(l.Lpu_GlVrach,'.',' '),'  ', ' '),
				CHARINDEX(' ', REPLACE(REPLACE(l.Lpu_GlVrach,'.',' '),'  ', ' '), CHARINDEX(' ', REPLACE(REPLACE(l.Lpu_GlVrach,'.',' '),'  ', ' ')) + 1) + 1,
				40
				)) AS 'OT-GV',
				SUBSTRING(REPLACE(REPLACE(l.Lpu_GlBuh,'.',' '),'  ', ' '), 1, CHARINDEX(' ', REPLACE(REPLACE(l.Lpu_GlBuh,'.',' '),'  ', ' ')) - 1) AS 'FAM-BUX',
				SUBSTRING(
				REPLACE(REPLACE(l.Lpu_GlBuh,'.',' '),'  ', ' '),
				CHARINDEX(' ', REPLACE(REPLACE(l.Lpu_GlBuh,'.',' '),'  ', ' ')) + 1,
				CHARINDEX(' ', REPLACE(REPLACE(l.Lpu_GlBuh,'.',' '),'  ', ' '), CHARINDEX(' ', REPLACE(REPLACE(l.Lpu_GlBuh,'.',' '),'  ', ' ')) + 1) - CHARINDEX(' ', REPLACE(REPLACE(l.Lpu_GlBuh,'.',' '),'  ', ' ')) - 1
				) AS 'IM-BUX',
				RTRIM(SUBSTRING(
				REPLACE(REPLACE(l.Lpu_GlBuh,'.',' '),'  ', ' '),
				CHARINDEX(' ', REPLACE(REPLACE(l.Lpu_GlBuh,'.',' '),'  ', ' '), CHARINDEX(' ', REPLACE(REPLACE(l.Lpu_GlBuh,'.',' '),'  ', ' ')) + 1) + 1,
				40
				)) AS 'OT-BUX',
				l.lpu_phone AS TEL,
				NULL AS FAX,
				l.Lpu_Email AS 'E-MAIL',
				l.Lpu_begDate AS 'DATE-B',
				l.Lpu_endDate AS 'DATE-E'
				FROM v_lpu l with(nolock)
				LEFT JOIN dbo.v_Address a with(nolock) ON a.Address_id = l.PAddress_id
	    	")
        );
        $this->queries['Doctor'] = array(
            'params' => array(),
            'filename' => 'Doctor.dbf',
            'name' => 'Справочник Врачей ЛЛО',
            'dbf_structure' => array(
                array('TF_OKATO', 'N', 5, 0, 'name' => trim('Код территории по классификатору    ')),
                array('MCOD'    , 'C', 7   , 'name' => trim('Код ЛПУ в кодировке ТФОМС           ')),
                array('PCOD'    , 'C', 22  , 'name' => trim('Идентификационный номер (код) врача ')),
                array('FAM_V'   , 'C', 30  , 'name' => trim('Фамилия врача (фельдшера)           ')),
                array('IM_V'    , 'C', 20  , 'name' => trim('Имя                                 ')),
                array('OT_V'    , 'C', 20  , 'name' => trim('Отчество                            ')),
                array('C_OGRN'  , 'C', 15  , 'name' => trim('ОГРН медицинского учреждения места  ')),
                array('PRVD'    , 'N', 4, 0, 'name' => trim('Код врачебной должности             ')),
                array('D_JOB'   , 'C', 50  , 'name' => trim('Занимаемая должность                ')),
                array('D_PRIK'  , 'D', null, 'name' => trim('Дата приема на работу               ')),
                array('D_SER'   , 'D', null, 'name' => trim('Дата выдачи сертификата             ')),
                array('PRVS'    , 'C', 9   , 'name' => trim('Код специальности медицинского      ')),
                array('KV_KAT'  , 'N', 1, 0, 'name' => trim('Квалификационная категория врача*   ')),
                array('DATE_B'  , 'D', null, 'name' => trim('Дата включения в регистр врачей и   ')),
                array('DATE_E'  , 'D', null, 'name' => trim('Дата исключения из регистра врачей и')),
                array('MSG_TEXT', 'C', 100 , 'name' => trim('Примечание                          ')),
            ),
            'query' => array("
				SELECT
				l.Lpu_OKATO AS 'TF_OKATO',
				l.Lpu_Ouz AS 'MCOD',
				l.Lpu_OKATO + ' ' + p.MedPersonal_Code AS 'PCOD',
				p.Person_SurName AS 'FAM_V',
				p.Person_FirName AS 'IM_V',
				p.Person_SecName AS 'OT_V',
				l.Lpu_OGRN AS 'C_OGRN',
				p.Dolgnost_Code AS 'PRVD',
				p.Dolgnost_Name AS 'D_JOB',
				p.WorkData_begDate AS 'D_PRIK',
				c.CertificateReceipDate AS 'D_SER',
				NULL AS 'PRVS',
				NULL AS 'KV_KAT',
				NULL AS 'DATE_B',
				NULL AS 'DATE_E',
				NULL AS 'MSG_TEXT'
				FROM v_MedPersonal p with(nolock)
				INNER JOIN v_lpu l with(nolock) ON p.Lpu_id = l.Lpu_id
				LEFT JOIN persis.Certificate c with(nolock) ON c.MedWorker_id = p.MedPersonal_id
				WHERE WorkData_IsDlo = 2
			")
        );
        $this->queries['dolgn'] = array(
            'params' => array(),
            'filename' => 'dolgn.dbf',
            'name' => 'Справочник Врачей ЛЛО',
            'dbf_structure' => array(
                array('PRVD', 'N', 4, 0),
                array('NAME_VD'    , 'C', 100),
                array('MSG_TEXT'    , 'C', 100  )
            ),
            'query' => array("
				SELECT  id AS PRVD,
				name AS NAME_VD,
				NULL AS MSG_TEXT
				FROM persis.v_post with(nolock)
	    	")
        );
        $this->queries['spec'] = array(
            'params' => array(),
            'filename' => 'spec.dbf',
            'name' => 'Справочник Специальностей медицинских работников',
            'dbf_structure' => array(
                array('PRVS', 'N', 9, 0),
                array('NAME_VDS'    , 'C', 100),
                array('MSG_TEXT'    , 'C', 100  )
            ),
            'query' => array("
				SELECT
				MedSpecOms_id AS PRVS,
				MedSpecOms_Name AS  NAME_VDS,
				NULL AS MSG_TEXT
				FROM MedSpecOms with(nolock)
	    	")
        );
        $this->queries['diag'] = array(
            'params' => array(),
            'filename' => 'mkb-10.dbf',
            'name' => 'Справочник МКБ-10',
            'dbf_structure' => array(
                array('PRVS', 'C', 7),
                array('NAME_DS'    , 'C', 255)
            ),
            'query' => array("SELECT Diag_Code AS  DS, Diag_Name AS NAME_DS FROM dbo.v_Diag with(nolock) WHERE DiagLevel_id = 4")
        );
        $this->queries['Apteka'] = array(
            'params' => array(),
            'filename' => 'Apteka.dbf',
            'name' => 'Справочник Аптек',
            'dbf_structure' => array(
                array('A_COD'      , 'C', 7  , null),
                array('TF_OKATO'   , 'N', 5  , 0   ),
                array('C_OGRN'     , 'C', 15 , null),
                array('AU_NAMES'   , 'C', 50 , null),
                array('AU_NAMEF'   , 'C', 150, null),
                array('POST_ID'    , 'N', 6  , 0   ),
                array('ADRES'      , 'C', 200, null),
                array('FAM_GV'     , 'C', 40 , null),
                array('IM_GV'      , 'C', 40 , null),
                array('OT_GV'      , 'C', 40 , null),
                array('FAM_BUX'    , 'C', 40 , null),
                array('IM_BUX'     , 'C', 40 , null),
                array('OT_BUX'     , 'C', 40 , null),
                array('TEL'        , 'C', 40 , null),
                array('FAX'        , 'C', 40 , null),
                array('E_MAIL'     , 'C', 30 , null),
                array('DATE_B'     , 'D',      null),
                array('DATE_E'     , 'D',      null),
                array('kladr'     , 'N', 1  , 0   ),
                array('house'     , 'N', 1  , 0   ),
                array('license'    , 'C', 20 , null),
                array('narkotik'   , 'N', 1  , 0   ),
                array('licens_nar', 'C', 20 , null),
            ),
            'query' => array("SELECT
				c.Contragent_Code AS A_COD,
				o.Org_OKATO AS TF_OKATO,
				o.Org_OGRN AS C_OGRN,
				o.Org_Nick as AU_NAMES,
				o.Org_Name AS AU_NAMEF,
				a.Address_Zip AS POST_ID,
				dbo.Address_Compose(
					a.KLCountry_id,
					a.KLRgn_id,
					a.KLSubRgn_id,
					a.KLCity_id,
					a.KLTown_id,
					a.KLStreet_id,
					a.Address_House,
					a.Address_Corpus,
					a.Address_Flat
				) AS ADRES,
				SUBSTRING(REPLACE(REPLACE(o.Org_Rukovod,'.',' '),'  ', ' '), 1, CHARINDEX(' ', REPLACE(REPLACE(o.Org_Rukovod,'.',' '),'  ', ' ')) - 1)
				  AS FAM_GV,
				SUBSTRING(
					REPLACE(REPLACE(o.Org_Rukovod,'.',' '),'  ', ' '),
					CHARINDEX(' ', REPLACE(REPLACE(o.Org_Rukovod,'.',' '),'  ', ' ')) + 1,
					CHARINDEX(' ', REPLACE(REPLACE(o.Org_Rukovod,'.',' '),'  ', ' '), CHARINDEX(' ', REPLACE(REPLACE(o.Org_Rukovod,'.',' '),'  ', ' ')) + 1) - CHARINDEX(' ', REPLACE(REPLACE(o.Org_Rukovod,'.',' '),'  ', ' ')) - 1
				) AS 'IM_GV',
				RTRIM(SUBSTRING(
					REPLACE(REPLACE(o.Org_Rukovod,'.',' '),'  ', ' '),
					CHARINDEX(' ', REPLACE(REPLACE(o.Org_Rukovod,'.',' '),'  ', ' '), CHARINDEX(' ', REPLACE(REPLACE(o.Org_Rukovod,'.',' '),'  ', ' ')) + 1) + 1,
					40
				)) AS 'OT_GV',
				SUBSTRING(REPLACE(REPLACE(o.Org_Buhgalt,'.',' '),'  ', ' '), 1, CHARINDEX(' ', REPLACE(REPLACE(o.Org_Buhgalt,'.',' '),'  ', ' ')) - 1)
				  AS 'FAM_BUX',
				SUBSTRING(
					REPLACE(REPLACE(o.Org_Buhgalt,'.',' '),'  ', ' '),
					CHARINDEX(' ', REPLACE(REPLACE(o.Org_Buhgalt,'.',' '),'  ', ' ')) + 1,
					CHARINDEX(' ', REPLACE(REPLACE(o.Org_Buhgalt,'.',' '),'  ', ' '), CHARINDEX(' ', REPLACE(REPLACE(o.Org_Buhgalt,'.',' '),'  ', ' ')) + 1) - CHARINDEX(' ', REPLACE(REPLACE(o.Org_Buhgalt,'.',' '),'  ', ' ')) - 1
				) AS 'IM_BUX',
				RTRIM(SUBSTRING(
					REPLACE(REPLACE(o.Org_Buhgalt,'.',' '),'  ', ' '),
					CHARINDEX(' ', REPLACE(REPLACE(o.Org_Buhgalt,'.',' '),'  ', ' '), CHARINDEX(' ', REPLACE(REPLACE(o.Org_Buhgalt,'.',' '),'  ', ' ')) + 1) + 1,
					40
				)) AS 'OT_BUX',
				o.Org_Phone AS TEL,
				NULL AS FAX,
				o.Org_Email AS E_MAIL,
				o.Org_begDate AS DATE_B,
				o.Org_endDate AS DATE_E,
				NULL AS kladr,
				NULL AS house,
				NULL AS license,
				NULL AS narkotik,
				NULL AS licens_nar
				FROM v_OrgFarmacy f with(nolock)
				INNER JOIN v_Contragent c with(nolock) ON f.Org_id = c.Org_id
				INNER JOIN dbo.v_Org o with(nolock) ON f.Org_id = o.Org_id
				LEFT JOIN dbo.v_Address a with(nolock) ON o.UAddress_id = a.Address_id
	    	")
        );
        $this->queries['Lsdlo'] = array(
            'params' => array(),
            'filename' => 'Lsdlo.dbf',
            'name' => 'Номенклатурный справочник лекарственных средств',
            'dbf_structure' => array(
                array('NOMK_LS',  'N', 13,0),
                array('NAME_MED', 'C', 255),
                array('C_TRN   ', 'N', 13,0),
                array('C_MNN   ', 'N', 13,0),
                array('C_LF    ', 'N', 4,0),
                array('D_LS    ', 'C', 20 ),
                array('C_DLS   ', 'N', 3,0),
                array('N_DOZA  ', 'N', 3,0),
                array('V_LF    ', 'N', 7,3),
                array('C_VLF   ', 'N', 3,0),
                array('M_LF    ', 'N', 7,3),
                array('C_MLF   ', 'N', 3,0),
                array('N_FV    ', 'N', 5,0),
                array('NAME_FCT', 'C', 150),
                array('NAME_CNF', 'C', 25 ),
                array('NAME_PCK', 'C', 150),
                array('NAME_CNP', 'C', 25 ),
                array('COMPL   ', 'C', 170),
                array('C_FARG  ', 'N', 5,0),
                array('FLAG_KEK', 'N', 1,0),
                array('FLAG1   ', 'N', 1,0),
                array('FLAG2   ', 'N', 1,0),
                array('DATE_B  ', 'D',    ),
                array('DATE_E  ', 'D',    ),
                array('MSG_TEXT', 'C', 100),
                array('C_RLP   ', 'N', 3,0),
                array('EAN13   ', 'C', 15 ),
                array('REG_NUM ', 'C', 50 ),
            ),
            'query' => array("
	    		SELECT
					d.Drug_CodeG AS NOMK_LS,            --NOMK_LS |Num  |13    | Номенклатурный код лекарственного средства
					d.Drug_Name AS NAME_MED,            --NAME_MED|Char |255   | Наименование медикамента
					d.DrugTorg_id AS C_TRN,             --C_TRN   |Num  |13    | Код лекарственного средства по торговому наименованию
					d.DrugMnn_id AS C_MNN,              --C_MNN   |Num  |13    | Код лекарственного средства по международному непатентованному наименованию (МНН)
					d.DrugForm_id AS C_LF,              --C_LF    |Num  |4     | Код лекарственной формы
					null AS D_LS    ,                   --D_LS    |Char |20    | Дозировка действующего вещества
					null AS C_DLS   ,                   --C_DLS   |Num  |3     | Код единицы измерения дозировки
					null AS N_DOZA  ,                   --N_DOZA  |Num  |3     | Количество доз
					null AS V_LF    ,                   --V_LF    |Num  |7.3   | Объем лекарственной формы (заполняется для растворов, сиропов, суспензий, аэрозолей, мазей)
					null AS C_VLF   ,                   --C_VLF   |Num  |3     | Код единицы объема лекарственной формы (заполняется для растворов, сиропов, суспензий, аэрозолей, мазей)
					null AS M_LF    ,                   --M_LF    |Num  |7.3   | Вес лекарственной формы
					null AS C_MLF   ,                   --C_MLF   |Num  |3     | Код единицы веса лекарственной формы
					null AS N_FV    ,                   --N_FV    |Num  |5     | Фасовка (N упаковки)
					null AS NAME_FCT,                   --NAME_FCT|Char |150   | Сокращенное название производителя
					null AS NAME_CNF,                   --NAME_CNF|Char |25    | Сокращенное название страны производителя
					null AS NAME_PCK,                   --NAME_PCK|Char |150   | Сокращенное название упаковщика
					null AS NAME_CNP,                   --NAME_CNP|Char |25    | Сокращенное название страны упаковщика
					null AS COMPL   ,                   --COMPL   |Char |170   | Комплектность
					null AS C_FARG  ,                   --C_FARG  |Num  |5     | Код фармгруппы по Госреестру лекарственных средств
					null AS FLAG_KEK,                   --FLAG_KEK|Num  |1     | Признак Требуется протокол ВК
					null AS FLAG1   ,                   --FLAG1   |Num  |1     | Признак Входит в список ЖНВЛС
					null AS FLAG2   ,                   --FLAG2   |Num  |1     | Признак Запрещен к отпуску
					null AS DATE_B  ,                   --DATE_B  |Date |      | Дата включения в справочник
					null AS DATE_E  ,                   --DATE_E  |Date |      | Дата исключения из справочника
					null AS MSG_TEXT,                   --MSG_TEXT|Char |100   | Примечание
					null AS C_RLP   ,                   --C_RLP   |Num  |3     | Код раздела Перечня
					r.Drug_Ean AS EAN13   ,             --EAN13   |Char |15    | Код EAN
					r.Drug_RegNum AS REG_NUM            --REG_NUM |Char |50    | номер гос.регистрации ЛС
				FROM
					v_drug d with(nolock)
				LEFT JOIN rls.v_Drug r with(nolock) ON d.Drug_id = r.Drug_id
			")
        );
        $this->queries['MNN'] = array(
            'params' => array(),
            'filename' => 'MNN.dbf',
            'name' => 'Справочник  международных  непатентованных  наименований лекарственных средств',
            'dbf_structure' => array(
                array('C_MNN'     , 'N', 13 , 0),
                array('NAME_MNN'  , 'C', 200   ),
                array('NAME_MNN_L', 'C', 200   ),
                array('MSG_TEXT'  , 'C', 100   ),
            ),
            'query' => array("SELECT
				 m.DrugMNN_Code AS C_MNN,
				 m.DrugMNN_Name AS NAME_MNN,
				 m.DrugMNN_LatName AS NAME_MNN_L,
				 NULL AS MSG_TEXT
				FROM rls.v_DrugMnn m with(nolock)
	    	")
        );
        $this->queries['Torg'] = array(
            'params' => array(),
            'filename' => 'Torg.dbf',
            'name' => 'Справочник торговых наименований лекарственных средств',
            'dbf_structure' => array(
                array('C_TRN'     , 'N', 13 , 0),
                array('NAME_TRN'  , 'C', 255   ),
                array('NAME_TRN_L', 'C', 255   ),
                array('MSG_TEXT'  , 'C', 100   ),
            ),
            'query' => array("
				SELECT
				t.DrugTorg_CodeG as C_TRN,
				t.DrugTorg_Name AS NAME_TRN,
				t.DrugTorg_NameLat AS NAME_TRN_L,
				null as MSG_TEXT
				 FROM v_DrugTorg t with(nolock)
				")
        );
        $this->queries['person'] = array(
            'params' => array(),
            'filename' => 'person.dbf',
            'name' => 'список персон, обладающих льготами',
            'dbf_structure' => array(
                array('SS'       , 'C', 14 , 0),// СНИЛС
                array('SN_POL'   , 'C', 25 , 0),// Серия и номер полиса ОМС
                array('FAM'      , 'C', 40 , 0),// Фамилия
                array('IM'       , 'C', 40 , 0),// Имя
                array('OT'       , 'C', 40 , 0),// Отчество
                array('W'        , 'C', 1  , 0),// Пол (М/Ж)
                array('DR'       , 'C', 10 , 0),// Дата рождения (ГГГГ/ММ/ДД)
                array('SN_DOC'   , 'C', 16 , 0),// Серия и номер документа, удостоверяющего личность
                array('C_DOC'    , 'N', 2  , 0),// Тип документа, удостоверяющего личность
                array('ADRES'    , 'C', 200, 0),// Адрес по месту регистрации (субъект РФ - район - город - сельсовет - населенный пункт - улица)
                array('DOM'      , 'C', 7  , 0),// Номер дома (владение)
                array('KOR'      , 'C', 5  , 0),// Корпус/строение
                array('KV'       , 'C', 5  , 0),// Квартира/комната
                array('OKATO_REG', 'N', 5  , 0),// Код территории постоянной регистрации гражданина (по ОКАТО)
                array('S_EDV'    , 'N', 1  , 0),// Признак получения набора социальных услуг
                array('DB_EDV'   , 'C', 10 , 0),// Дата начала действия права на получение НСУ (ГГГГ/ММ/ДД)
                array('DE_EDV'   , 'C', 10 , 0),// Дата окончания действия права на получение НСУ (ГГГГ/ММ/ДД)
                array('C_KAT1'   , 'C', 3  , 0),// Код категории, по которой гражданину установлена ГСП (за исключением граждан, указанных в статье 6.7 Федерального закона от 17.07.99 N 178-ФЗ (все, кроме чернобыльцев). Нули (000) - такой категории нет
                array('C_KAT2'   , 'C', 3  , 0),// Код категории, по которой гражданину установлена ГСП (для граждан, указанных в статье 6.7 Федерального закона от 17.07.99 N 178-ФЗ (чернобыльцы и приравненные к ним). Нули (000) - такой категории нет
                array('DATE_RSB' , 'C', 10 , 0),// Дата включения в региональный сегмент Регистра: ГГГГ/ММ/ДД
                array('DATE_RSE' , 'C', 10 , 0),// Дата исключения из регионального сегмента Регистра: ГГГГ/ММ/ДД
                array('U_TYPE'   , 'N', 2  , 0),// Код изменения
                array('D_TYPE'   , 'C', 3  , 0),// Признак "Особый случай" (резервное
                array('LIVE_CODE', 'C', 255, 0),// Уникальный номер регистровой записи для включенных в регистр по ВЗН
            ),
            'query' => array("
				SELECT
				p.PersonSnils_Snils
				 as SS       ,--SS        C 14  СНИЛС
				s.Polis_Ser + ' ' + s.Polis_Num
				 as SN_POL   ,--SN_POL    C 25  Серия и номер полиса ОМС
				p.PersonSurName_id
				 as FAM      ,--FAM       C 40  Фамилия
				p.PersonFirName_FirName
				 as IM       ,--IM        C 40  Имя
				p.PersonSecName_SecName
				 as OT       ,--OT        C 40  Отчество
				CASE s.Sex_id WHEN 1 THEN 'М' WHEN 2 THEN 'Ж' END AS W,
				convert(VARCHAR, p.PersonBirthDay_BirthDay, 111)
				 as DR       ,--DR        C 10  Дата рождения (ГГГГ/ММ/ДД)
				s.Document_Ser + ' ' + s.Document_Num
				 as SN_DOC   ,--SN_DOC    C 16  Серия и номер документа, удостоверяющего личность
				s.Document_id
				 as C_DOC    ,--C_DOC     N 2   Тип документа, удостоверяющего личность
				dbo.Address_Compose(
				a.KLCountry_id,
				a.KLRgn_id,
				a.KLSubRgn_id,
				a.KLCity_id,
				a.KLTown_id,
				a.KLStreet_id,
				NULL,
				NULL,
				NULL
				)
				 as ADRES    ,--ADRES     C 200 Адрес по месту регистрации (субъект РФ - район - город - сельсовет - населенный пункт - улица)
				a.Address_House
				 as DOM      ,--DOM       C 7   Номер дома (владение)
				a.Address_Corpus
				 as KOR      ,--KOR       C 5   Корпус/строение
				a.Address_Flat
				 as KV       ,--KV        C 5   Квартира/комната
				a.KLADR_Ocatd
				 as OKATO_REG,--OKATO_REG N 5   Код территории постоянной регистрации гражданина (по ОКАТО)
				null
				 as S_EDV    ,--S_EDV     N 1   Признак получения набора социальных услуг
				null
				 as DB_EDV   ,--DB_EDV    C 10  Дата начала действия права на получение НСУ (ГГГГ/ММ/ДД)
				null
				 as DE_EDV   ,--DE_EDV    C 10  Дата окончания действия права на получение НСУ (ГГГГ/ММ/ДД)
				NULL as C_KAT1   ,--C_KAT1    C 3   Код категории, по которой гражданину установлена ГСП (за исключением граждан, указанных в статье 6.7 Федерального закона от 17.07.99 N 178-ФЗ (все, кроме чернобыльцев). Нули (000) - такой категории нет
				null as C_KAT2   ,--C_KAT2    C 3   Код категории, по которой гражданину установлена ГСП (для граждан, указанных в статье 6.7 Федерального закона от 17.07.99 N 178-ФЗ (чернобыльцы и приравненные к ним). Нули (000) - такой категории нет
				null as DATE_RSB ,--DATE_RSB  C 10  Дата включения в региональный сегмент Регистра: ГГГГ/ММ/ДД
				NULL as DATE_RSE ,--DATE_RSE  C 10  Дата исключения из регионального сегмента Регистра: ГГГГ/ММ/ДД
				null as U_TYPE   ,--U_TYPE    N 2   Код изменения
				 NULL as D_TYPE   ,--D_TYPE    C 3   Признак \"Особый случай\" (резервное
				 NULL as LIVE_CODE--LIVE_CODE C     Уникальный номер регистровой записи для включенных в регистр по ВЗН
				FROM
				dbo.v_PersonPrivilege p with(nolock)
				INNER JOIN dbo.v_PrivilegeType t with(nolock) ON p.PrivilegeType_id = t.PrivilegeType_id
				INNER JOIN dbo.v_PersonState s with(nolock) ON s.Person_id = p.Person_id
				INNER JOIN dbo.v_Address_KLADR a with(nolock) ON a.Address_id = s.UAddress_id
				WHERE t.PrivilegeType_Code > 200
				")
						);
						$this->queries['Slgot'] = array(
							'params' => array(),
							'filename' => 'Slgot.dbf',
							'name' => 'Справочник категорий льгот',
							'dbf_structure' => array(
								array('C_KAT'     , 'N', 3 , 0),
								array('NAME_KAT'  , 'C', 255   ),
								array('MSG_TEXT', 'C', 100   ),
								array('C_Finance'  , 'N', 1 ,0  ),
								array('C_discount'  , 'N', 1 ,0  ),
							),
							'query' => array("
				SELECT
				PrivilegeType_Code as C_KAT,
				PrivilegeType_Name as NAME_KAT,
				PrivilegeType_Descr as MSG_TEXT,
				ReceptFinance_id as C_Finance,
				ReceptDiscount_id as C_discount
				FROM dbo.v_PrivilegeType with(nolock)
				")
						);
						$this->queries['fin'] = array(
							'params' => array(),
							'filename' => 'fin.dbf',
							'name' => 'Справочник Источников финансирования рецепта',
							'dbf_structure' => array(
								array('C_Finl', 'C',1   ),
								array('N_Finl', 'C',100 ),
							),
							'query' => array("SELECT
					DrugFinance_Code AS C_Finl,
					DrugFinance_Name AS N_Finl
				FROM
					v_DrugFinance with(nolock)
				")
        );*/
    }

	/**
	 * Функция
	 */
    private function getDoneFilesList(){
        return $_SESSION[$this::SESSION_KEY]['done'];
    }

	/**
	 * Функция
	 */
    private function addDoneFile($filename){
        $_SESSION[$this::SESSION_KEY]['done'][] = $filename;
    }

	/**
	 * Функция
	 */
    public function resetDoneFilesList(){
        $_SESSION[$this::SESSION_KEY]['done'] = array();
        return array('success' => true, 'Error_Msg' => '');
    }

    /**
     * Рекурсивное создание пути.
     *
     * @param $dir
     * @param int $mode
     * @return bool
     */
    private function mkdir($dir, $mode = 0755){
        if (is_dir($dir) || @mkdir($dir,$mode)) return TRUE;
        if (!$this->mkdir(dirname($dir),$mode)) return FALSE;
        return @mkdir($dir,$mode);
    }

    /**
     * Получение списка доступных запросов
     */
    public function getQueryList(){
        $result = array();
        foreach($this->queries as $query_nick => $query) {
            $result[] = array(
                'query_nick' => $query_nick,
                'query_name' => $query['name']
            );
        }
        return $result;
    }

    /**
     * Выполнение запроса и экспорт его результатов в dbf-файл согласно настройкам
     *
     * @param $query_name Название запроса, результаты которого надо экспортировать
     * @param array $params Параметры для запроса (если требуются)
     * @return bool
     * @throws Exception
     */
    public function export($query_name, $params = array()){
        if (array_key_exists($query_name, $this->queries)) {
            //todo проверить что параметры переданы array_key_exists
            $data2export = $this->runQuery($this->queries[$query_name]['query'], $params);
            if (is_array($data2export)) {
                $dbf_full_name = $this->workingDir . $this->queries[$query_name]['filename'];
                $this->array2dbase($dbf_full_name, $this->queries[$query_name]['dbf_structure'], $data2export);
                $this->addDoneFile($dbf_full_name);
            } else {
                throw new Exception("Ошибка выполнения запроса \"$query_name\"");
            }
        } else {
            throw new Exception("Запрошен экспорт несуществующего запроса \"$query_name\"");
        }
        return array('success' => true, 'Error_Msg' => '');
    }

    /**
     * Сохранение массива в dbase
     *
     * @param $dbf_full_name Имя конечного DBF-файла
     * @param $fields_dbf Описание полей
     * @param $response array Результаты выполнения запроса
     * @throws Exception
     */
    private function array2dbase($dbf_full_name, $fields_dbf, $response) {
        if (is_file($dbf_full_name)){
            unlink($dbf_full_name);
        }
        $h = dbase_create($dbf_full_name, $fields_dbf);
        if (!$h) {
            throw new Exception('dbase_create() fails ' . $dbf_full_name);
        }
        $add_ok = true;
        $cnt = 0;
        @trigger_error('');//сброс ошибки
        foreach ($response as $record) {
            $record = array_change_key_case($record, CASE_UPPER);
            foreach ($fields_dbf as $column) {
                $column[0] = strtoupper($column[0]);
	            if (empty($column['source_column'])) {
		            $column['source_column'] = $column[0];
	            } else {
		            $column['source_column'] = strtoupper($column['source_column']);
	            }
                switch ($column[1]) {
                    case 'D':
                        if (!empty($record[$column['source_column']])) {
                            if ($record[$column['source_column']] instanceOf DateTime) {
                                /**
                                 * @var $dt Datetime
                                 */
                                $dt = $record[$column['source_column']];
                                $record[$column[0]] = $dt->format('Ymd');
                            } else {
                                throw new Exception('Неверная дата в записи (' . implode(', ', $record) . ')');
                            }
                        }
                        break;
                    case 'C':
                        if (!empty($record[$column['source_column']])) {
	                        if ('object' != gettype($record[$column['source_column']])){
		                        ConvertFromUtf8ToCp866($record[$column['source_column']]);
		                        $record[$column[0]] = $record[$column['source_column']];
	                        } else {
		                        throw new Exception('Поле '.$column['source_column'].' в запросе для экспорта содержит данные типа '.get_class($record[$column['source_column']]).', а поле назначения '.$column[0].' в структуре DBF имеет тип '.$column[1]);
	                        }
                        }
                        break;
                    default:
                        if (array_key_exists($column[0], $record)) {
                            if (is_object($record[$column[0]])) {
                                throw new Exception('Попытка записать объект без предварительного преобразования в строку. DBF-файл '.$dbf_full_name.' (Данные записи: ' . var_export($record, true) . ')');
                            }
	                        $record[$column[0]] = $record[$column['source_column']];
                        } else {
                            throw new Exception("В результатах выполнения запроса для выгрузки в файл $dbf_full_name отсутствует столбец {$column[0]}, описанный в структуре таблицы");
                        }

                }
            }
            $add_ok = $add_ok && dbase_add_record($h, array_values($record));
            if (!$add_ok) {
                $err = error_get_last();
                if ('' !== $err['message']) {
                    $err = 'Текст ошибки: '.$err['message'].', ';
                } else {
                    $err = '';
                }
                throw new Exception('Ошибка добавления записи в DBF-файл '.$dbf_full_name.' ('.$err.'Данные записи: ' . var_export($record, true) . ')');
            } else {
                $cnt++;
            }
        }
        log_message('debug', 'Записей добавлено в '.$dbf_full_name.': ' . $cnt);
        if (!dbase_close($h)) {
            throw new Exception('Не удалось сохранить изменения в ' . $dbf_full_name);
        }
    }

    /**
     * Выполнение запроса
     *
     * @param $query
     * @param array $params
     * @return bool|array
     */
    private function runQuery($query, $params = array()){
        $result = $this->db->query($query, $params);
        if ( is_object($result) ) {
            return $result->result('array');
        }
        else {
            log_message('error', __METHOD__.': query fails: ', getDebugSql($query, $params));
            return false;
        }
    }

    /**
     * Упаковка результатов (выполненных за сессию запросов) в zip-архив
     */
    public function packResult(){
        $files = $this->getDoneFilesList();
        if (is_array($files) && count($files)){
            do {
                $archivefilename = tempnam($this->workingDir, 'exp');
                unlink($archivefilename);
            } while (is_file($archivefilename.'.zip'));
            $archivefilename = $archivefilename.'.zip';
            $result = true;
            $zip = new ZipArchive();
            $result = $result && $zip->open($archivefilename, ZIPARCHIVE::CREATE);
            if (!$result) {
                throw new Exception('Ошибка создания архива');
            }
            foreach ($files as $filename) {
                $result = $result && $zip->addFile($filename, basename($filename));
                if (!$result) {
                    throw new Exception('Ошибка добавления файла в архив');
                }
            }
            $result = $result && $zip->close();
            if (!$result) {
                throw new Exception('Ошибка сохранения архива');
            }
        } else {
            throw new Exception('Упаковщик ZIP: в этой сессии нет выгруженных файлов');
        }
        if ($result) {
            $this->resetDoneFilesList();
            $arch_base_name = pathinfo($archivefilename, PATHINFO_BASENAME);
            $result = array('success' => true, 'filename' => $this->workingDir.$arch_base_name, 'Error_Msg' => '');
        } else {
            $result = array('success' => false, 'Error_Msg' => 'Непредвиденная ошибка при архивации результатов экспорта');
        }
        return $result;
    }

	/**
	 * @return array
	 */
	public function getQueries()
	{
		return $this->queries;
	}


}