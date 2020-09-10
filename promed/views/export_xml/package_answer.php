<ANSWER>
	<HEADER>
		{QUEUE_NAME}
		{TYPE}
		{MESSAGE_ID}
	</HEADER>
	<BODY>
		<?php if($RESULT=='OK' && !empty($PERSON) && is_array($PERSON) && count($PERSON) > 0){ ?>
		{PERSON}
		<PERSON>
			{PERSON_ID}
			{BDZ_ID}
			<?php if ($TYPE == 'CHECKPERS') { ?>
			{FAM}
			{IM}
			{OT}
			{DR}
			{W}
			{DOCTYPE}
			{DOCSER}
			{DOCNUM}
			{SNILS}
			{ENP}
			{POLIS_DATA}<POLIS_DATA>
				{POLISTYPE}
				{POLISSER}
				{POLISNUM}
				{POLISBEGDT}
				{POLISENDDT}
			</POLIS_DATA>{/POLIS_DATA}
			<?php } ?>
		</PERSON>
		{/PERSON}
		<?php } ?>
		<RESULTS>
			{RESULT}
			<?php if($RESULT=='ERROR') { ?>{ERROR_RESULT}<ERROR_RESULT>
				{RESULT_CODE}
				{RESULT_NAME}
			</ERROR_RESULT>{/ERROR_RESULT}<?php } ?>
		</RESULTS>
	</BODY>
</ANSWER>