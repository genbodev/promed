<?php
$isTFOMSAutoInteract = isset($isTFOMSAutoInteract)?$isTFOMSAutoInteract:false;
$region = getRegionNick();
?>

<?php if ($isTFOMSAutoInteract && $region == 'kareliya') { ?>
	<MOTION_IN_HOSPITAL>
		<ZGLV>
			{DATA}
			{TYPE}
			{ID}
		</ZGLV>
		<ZAP>
			{NOM_NAP}
			{DTA_NAP}
			{FRM_MP}
			{MCOD_STC}
			{MPODR_STC}
			{DTA_FKT}
			{DTA_END}
			{SMO_CODE}
			{FAM}
			{IM}
			{OT}
			{W}
			{DR}
			{VPOLIS}
			{SPOLIS}
			{NPOLIS}
			{USL_OK}
			{KOD_PFO}
			{KOD_PFK}
			{NHISTORY}
		</ZAP>
	</MOTION_IN_HOSPITAL>
<?php } else if ($isTFOMSAutoInteract) { ?>
<MOTION_IN_HOSPITAL>
	<HEADER>
		{OPERATIONTYPE}
		{DATA}
		{CODE_MO}
		{MIH_ID}
	</HEADER>
	<?php if($OPERATIONTYPE!='Delete'){ ?><BODY>
		{HOSPITALISATION_ID}
		{REFERRAL_NUMBER}
		{REFERRAL_DATE}
		{HOSPITALISATION_TYPE}
		{BRANCH}
		{DIVISION}
		{STRUCTURE_BED}
		{BEDPROFIL}
		{DLSB}
		{CARETYPE}
		{DATE_IN}
		{DATE_OUT}
		{MED_CARD_NUMBER}
		{HOSPITALISATION_DATE}
		{POLICY_TYPE}
		{POLIS_SERIAL}
		{POLIS_NUMBER}
		{SMO}
		{FIRST_NAME}
		{LAST_NAME}
		{FATHER_NAME}
		{SEX}
		{BIRTHDAY}
		{PATIENT}
	</BODY><?php } ?>
</MOTION_IN_HOSPITAL>
<?php } else if ($region == 'buryatiya') { ?>
<MOTION_IN_HOSPITAL>
	<header>
		{LPU}
		{TYPE}
		{ID}
	</header>
	<?php if($TYPE!='Delete'){ ?><body>
		{HOSPITALISATION_ID}
		{BRANCH}
		{DIVISION}
		{V020_STRUCTURE_BED}
		{STRUCTURE_BED}
		{DATE_IN}
		{DATE_OUT}
		{MED_CARD_NUMBER}
		{HOSPITALISATION_DATE}
		{USL_OK}
		{OUTCOME}
	</body><?php } ?>
</MOTION_IN_HOSPITAL>
<?php } else { ?>
<MOTION_IN_HOSPITAL>
	<header>
		{LPU}
		{TYPE}
		{ID}
	</header>
	<?php if($TYPE!='Delete'){ ?><body>
		{HOSPITALISATION_ID}
		{BRANCH}
		{DIVISION}
		{V020_STRUCTURE_BED}
		{STRUCTURE_BED}
		{DATE_IN}
		{DATE_OUT}
		{MED_CARD_NUMBER}
		{HOSPITALISATION_DATE}
		{USL_OK}
		{OUTCOME}
	</body><?php } ?>
</MOTION_IN_HOSPITAL>
<?php } ?>
