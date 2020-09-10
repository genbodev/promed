<div id="Anthropometry_{Person_id}" class="data-table component" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('Anthropometry_{Person_id}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('Anthropometry_{Person_id}_toolbar').style.display='none'">

    <div class="caption">
        <h2>Антропометрические данные</h2>
        <div id="Anthropometry_{Person_id}_toolbar" class="toolbar">
            <a id="Anthropometry_{Person_id}_printAnthropometry" class="button icon icon-print16" title="Печать"><span></span></a>
        </div>
    </div>

	<?php if (getRegionNick() == 'ufa') { ?>
	{PersonRace}
	<br />
	<?php } ?>

    {PersonHeight}    

    <br />

    {PersonWeight}

    <br />

    {HeadCircumference}

    <br />

    {ChestCircumference}

    <br />

    {PersonPPT}

</div>
