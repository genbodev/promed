<div id="SignalInformationAll_{Person_id}" class="frame signal-info" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('SignalInformationAll_{Person_id}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('SignalInformationAll_{Person_id}_toolbar').style.display='none'">

    {person_data}

    <div class="clear"><br /></div>

        {PersonLpuInfoPersData}

        {PalliatInfoConsentData}

    	{PersonMedHistory}
		
    <?php if ($Person_Age <= 3) { ?>

    	{Anthropometry}

        {FeedingType}

    	{BloodData}

    	{AllergHistory}

    	{ExpertHistory}

		{PersonSvidInfo}

    	{PersonDispInfo}

    	{EvnPLDispInfo}

    	{DiagList}

    	{SurgicalList}

		{DirFailList}

		{MantuReaction}

    	{Inoculation}

		{InoculationPlan}

		{EvnStickOpenInfo}

		{PersonQuarantine}

    <?php } else { ?>

    	{BloodData}

    	{AllergHistory}

    	{ExpertHistory}

		{PersonSvidInfo}

    	{PersonDispInfo}

		{EvnPLDispInfo}

    	{DiagList}

    	{SurgicalList}

		{DirFailList}

    	{Anthropometry}

        {FeedingType}

    	{MantuReaction}

		{Inoculation}

		{InoculationPlan}

		{EvnStickOpenInfo}

		{PersonQuarantine}

    <?php } ?>

	<?php if ($Person_Age >= 18) { ?>
    {PersonOnkoProfile}
	<?php } ?>

</div>