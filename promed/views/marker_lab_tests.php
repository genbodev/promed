<div class="marker_lab_tests">
	{UslugaComplex}
		<table>
			{UslugaComplex_Name}
			<tr>
				<td></td>
				{tests}
				<td><b>{test_name}</b></td>
				{/tests}
			</tr>
			{EvnUslugaPar}
			<tr><td><b>{EvnUslugaPar_setDate}</b></td>
				{results}
					<td><b>{result}</b><br>{unit}</td>
				{/results}
			</tr>
			{/EvnUslugaPar}
		</table>
		<br>
	{/UslugaComplex}
</div>