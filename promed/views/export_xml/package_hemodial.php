<HEMODIAL>
    <HEADER>
        {OPERATIONTYPE}
        {DATA}
        {CODE_MO}
        {REG_ID}
    </HEADER>
	<?php if($OPERATIONTYPE!='Delete'){ ?><BODY>
    {BDZ_ID}
    {DATE_IN}
    {DATE_OUT}
    {CAUSEOUT}
    {DIALCOMMENT}
    </BODY><?php } ?>
</HEMODIAL>