<ZL_LIST xmlns="OMS-D1">
	{ZGLV}
	<ZGLV>
		<VERSION>2.1</VERSION>
		<DATA><?php echo date('Y-m-d'); ?></DATA>
		{FILENAME}
		{SD_Z}
	</ZGLV>
	{/ZGLV}
	{SCHET}
	<SCHET>
		{CODE}
		{CODE_MO}
		{PODR}
		{YEAR}
		{MONTH}
		{NSCHET}
		{DSCHET}
		{PLAT}
		{SUMMAV}
		{COMENTS}
		{SUMMAP}
		{SANK_MEK}
		{SANK_MEE}
		{SANK_EKMP}
	</SCHET>
	{/SCHET}
	{ZAP}
	<ZAP>
		<N_ZAP>{N_ZAP}</N_ZAP>
		<PR_NOV>{PR_NOV}</PR_NOV>
		{PACIENT}
		<PACIENT>
			{ID_PAC}
			{VPOLIS}
			{SPOLIS}
			{NPOLIS}
			{ST_OKATO}
			{SMO}
			{SMO_OGRN}
			{SMO_OK}
			{SMO_NAM}
			{MSE}
			{NOVOR}
			{VNOV_D}
		</PACIENT>
		{/PACIENT}
		{SLUCH}
		<SLUCH>
			<IDCASE>{IDCASE}</IDCASE>
			{USL_OK}
			{VIDPOM}
			{FOR_POM}
			{VID_HMP}
			{METOD_HMP}
			{NPR_MO}
			{EXTR}
			{LPU}
			{LPU_1}
			{LPU_DEP}
			{PROFIL}
			{DET}
			{TAL_D}
			{TAL_P}
			{NHISTORY}
			{DATE_1}
			{DATE_2}
			{DS0}
			{DS1}
			{DS2_DATA}<DS2>{DS2}</DS2>{/DS2_DATA}
			{DS3_DATA}<DS3>{DS3}</DS3>{/DS3_DATA}
			{VNOV_M}
			<VNOV_M>{VNOV_M_VAL}</VNOV_M>
			{/VNOV_M}
			{RSLT}
			{ISHOD}
			{PRVS}
			{IDDOKT}
			{OS_SLUCH}
			<OS_SLUCH>{OS_SLUCH_VAL}</OS_SLUCH>
			{/OS_SLUCH}
			{IDSP}
			{ED_COL}
			{KPG}
			{KSG}
			{TARIF}
			{SUMV}
			{OPLATA}
			{SUMP}
			{COMENTSL}
			{USL}
			<USL>
				{IDSERV}
				{LPU_USL}
				{LPU_1_USL}
				{PODR_USL}
				{PROFIL_USL}
				{VID_VME}
				{DET_USL}
				{DATE_IN}
				{DATE_OUT}
				{DS}
				{CODE_USL}
				{KOL_USL}
				{TARIF_USL}
				{SUMV_USL}
				{PRVS_USL}
				{CODE_MD}
				{COMENTU}
			</USL>
			{/USL}
		</SLUCH>
		{/SLUCH}
	</ZAP>
	{/ZAP}
</ZL_LIST>