<ECO_REFERRAL>
    <HEADER>
        {OPERATIONTYPE}
        {DATA}
        {CODE_MO}
        {CODE_MO_TO}
        {ECO_ID}
    </HEADER>
	<?php if($OPERATIONTYPE!='Delete'){ ?><BODY>
    {BDZ_ID}
    {REFERRAL_NUMBER}
    {REFERRAL_DATE}
    {DOC_CODE}
    {PROTNUM}
    {PROTDATE}
    {PROTCOMMENT}
    {ECOCOMMENT}
    </BODY><?php } ?>
</ECO_REFERRAL>