<?php if (isset($isTFOMSAutoInteract) && $isTFOMSAutoInteract) { ?>
<CANCEL_HOSPITALISATION>
	<HEADER>
		{OPERATIONTYPE}
		{CODE_MO}
		{CH_ID}
	</HEADER>
	<?php if($OPERATIONTYPE!='Delete'){ ?><BODY>
		{DATE}
		{HOSPITALISATION_ID}
		{BRANCH}
		{REASON}
		{CANCEL_SOURSE}
		{DATE_CANCEL}
		{CANCEL_TYPE}
		{HOSPITALISATION_DIVISION}
		{MED_CARD_NUMBER}
		{PATIENT}
	</BODY><?php } ?>
</CANCEL_HOSPITALISATION>
<?php } else { ?>
<CANCEL_HOSPITALISATION>
	<header>
		{LPU}
		{TYPE}
		{ID}
	</header>
	<?php if($TYPE!='Delete'){ ?><body>
		{DATE}
		{HOSPITALISATION_ID}
		{BRANCH}
		{REASON}
		{CANCEL_SOURSE}
		{DATE_CANCEL}
		{CANCEL_TYPE}
		{HOSPITALISATION_DIVISION}
		{MED_CARD_NUMBER}
		{PATIENT}
	</body><?php } ?>
</CANCEL_HOSPITALISATION>
<?php } ?>