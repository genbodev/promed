<?php
$isTFOMSAutoInteract = isset($isTFOMSAutoInteract)?$isTFOMSAutoInteract:false;
$region = getRegionNick();
?>

<?php if ($isTFOMSAutoInteract && $region == 'kareliya') { ?>
<HOSPITALISATION_REFERRAL>
	<ZGLV>
		{DATA}
		{TYPE}
		{ID}
	</ZGLV>
	<ZAP>
		{NOM_NAP}
		{DTA_NAP}
		{FRM_MP}
		{MCOD_NAP}
		{MPODR_NAP}
		{MCOD_STC}
		{MPODR_STC}
		{USL_OK}
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
		{TLF}
		{DS}
		{KOD_PFO}
		{KOD_PFK}
		{KOD_DCT}
		{DTA_PLN}
	</ZAP>
</HOSPITALISATION_REFERRAL>
<?php } else if ($isTFOMSAutoInteract) { ?>
<HOSPITALISATION_REFERRAL>
	<HEADER>
		{OPERATIONTYPE}
		{DATA}
		{CODE_MO}
		{CODE_MO_TO}
		{HR_ID}
	</HEADER>
	<?php if($OPERATIONTYPE!='Delete'){ ?><BODY>
		{REFERRAL_NUMBER}
		{REFERRAL_DATE}
		{HOSPITALISATION_DATE}
		{HOSPITALISATION_TYPE}
		{BRANCH_TO}
		{DIVISION_TO}
		{BEDPROFIL}
		{STRUCTURE_BED}
		{DLSB}
		{CARETYPE}
		{BRANCH_FROM}
		{MKB}
		{DIAGNOSIS}
		{PLANNED_DATE}
		{DOC_CODE}
		{POLICY_TYPE}
		{POLIS_SERIAL}
		{POLIS_NUMBER}
		{SMO}
		{FIRST_NAME}
		{LAST_NAME}
		{FATHER_NAME}
		{SEX}
		{BIRTHDAY}
		{PHONE}
		{PATIENT}
		{ANOTHER_REGION}
	</BODY><?php } ?>
</HOSPITALISATION_REFERRAL>
<?php } else if ($region == 'buryatiya') { ?>
<HOSPITALISATION_REFERRAL>
	<header>
		{LPU}
		{TYPE}
		{LPU_TO}
		{ID}
	</header>
	<body>
		{REFERRAL_NUMBER}
		{REFERRAL_DATE}
		{HOSPITALISATION_DATE}
		{HOSPITALISATION_TYPE}
		{BRANCH_TO}
		{DIVISION_TO}
		{V020_STRUCTURE_BED}
		{STRUCTURE_BED}
		{BRANCH_FROM}
		{MKB}
		{DIAGNOSIS}
		{PLANNED_DATE}
		{USL_OK}
		{DOC_CODE}
		{POLICY_TYPE}
		{POLIS_SERIAL}
		{POLIS_NUMBER}
		{SMO}
		{FIRST_NAME}
		{LAST_NAME}
		{FATHER_NAME}
		{SEX}
		{BIRTHDAY}
		{PHONE}
		{PATIENT}
	</body>
</HOSPITALISATION_REFERRAL>
<?php } else { ?>
<HOSPITALISATION_REFERRAL>
	<header>
		{LPU}
		{TYPE}
		{LPU_TO}
		{ID}
	</header>
	<body>
		{REFERRAL_NUMBER}
		{REFERRAL_DATE}
		{HOSPITALISATION_DATE}
		{HOSPITALISATION_TYPE}
		{BRANCH_TO}
		{DIVISION_TO}
		{V020_STRUCTURE_BED}
		{STRUCTURE_BED}
		{BRANCH_FROM}
		{MKB}
		{DIAGNOSIS}
		{PLANNED_DATE}
		{USL_OK}
		{DOC_CODE}
		{POLICY_TYPE}
		{POLIS_SERIAL}
		{POLIS_NUMBER}
		{SMO}
		{FIRST_NAME}
		{LAST_NAME}
		{FATHER_NAME}
		{SEX}
		{BIRTHDAY}
		{PHONE}
		{PATIENT}
		{ANOTHER_REGION}
	</body>
</HOSPITALISATION_REFERRAL>
<?php } ?>