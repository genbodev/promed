<?php
$isTFOMSAutoInteract = isset($isTFOMSAutoInteract)?$isTFOMSAutoInteract:false;
$region = getRegionNick();
?>

<?php if ($isTFOMSAutoInteract && $region == 'kareliya') { ?>
<EXTR_HOSPITALISATION>
	<ZGLV>
		{DATA}
		{TYPE}
		{ID}
	</ZGLV>
	<ZAP>
		{NOM_NAP}
		{DTA_NAP}
		{MCOD_STC}
		{MPODR_STC}
		{DTA_FKT}
		{TIM_FKT}
		{VPOLIS}
		{SPOLIS}
		{NPOLIS}
		{SMO_CODE}
		{ST_OKATO}
		{FAM}
		{IM}
		{OT}
		{W}
		{DR}
		{USL_OK}
		{KOD_PFO}
		{KOD_PFK}
		{NHISTORY}
		{DS}
	</ZAP>
</EXTR_HOSPITALISATION>
<?php } else if ($isTFOMSAutoInteract) { ?>
<EXTRHOSPITALISATION>
	<HEADER>
		{OPERATIONTYPE}
		{DATA}
		{CODE_MO}
		{H_ID}
	</HEADER>
	<?php if($OPERATIONTYPE!='Delete'){ ?><BODY>
		{REFERRAL_NUMBER}
		{REFERRAL_DATE}
		{REFERRAL_MO}
		{REFERRAL_BRANCH}
		{MO}
		{BRANCH}
		{DIVISION}
		{FORM_MEDICAL_CARE}
		{HOSPITALISATION_DATE}
		{HOSPITALISATION_TIME}
		{POLICY_TYPE}
		{POLIS_SERIAL}
		{POLIS_NUMBER}
		{SMO}
		{SMO_OKATO}
		{FIRST_NAME}
		{LAST_NAME}
		{FATHER_NAME}
		{SEX}
		{BIRTHDAY}
		{STRUCTURE_BED}
		{BEDPROFIL}
		{DLSB}
		{CARETYPE}
		{MED_CARD_NUMBER}
		{MKB}
		{DIAGNOSIS}
		{PATIENT}
	</BODY><?php } ?>
</EXTRHOSPITALISATION>
<?php } ?>
