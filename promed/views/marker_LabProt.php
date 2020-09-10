<div class="marker_lab_tests">
	{UslugaComplex}
		<table>
			{UslugaComplex_Name}
			{EvnUslugaPar}
				<tr>
					<td></td>
					<td><b>Результат</b></td>
					<td colspan="2">Норма</td>
				</tr>
				{results}
				<tr>
					<td><b>{test_name}</b></td>
					<td><b>{result}</b><br>{unit}</td>
					<td>{norm}</td>
					<td>{crit}</td>
				</tr>
				{/results}
			{/EvnUslugaPar}
		</table>
		<br>
	{/UslugaComplex}
</div>
