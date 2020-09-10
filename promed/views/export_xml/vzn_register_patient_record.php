<record type="{type}">
	<?php if ($type == 'create') { ?>
	{patient}
	<?php } elseif ($type == 'update') { ?>
	<updatePatient>
		<key>{snils}</key>
		{patient}
	</updatePatient>
	<?php } ?>
</record>