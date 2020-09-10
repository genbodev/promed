<html>
<head>
	<title>Печать заявления о прикреплении</title>
	<style type="text/css">
		body { margin: 0px; padding: 20px; }
		table { border-collapse: collapse; }
		span, div, td { font-family: verdana; font-size: 12px; }
		.bottomText { text-align: center; margin-top: -3px; font-family: verdana; font-size: 8pt; }
	</style>

	<style type="text/css" media="print">
		body { margin: 0px; padding: 25px; }
		span, div, td { font-family: verdana; font-size: 10px; }
		td { vertical-align: bottom; }
	</style>
</head>
<body>
<!-- /*NO PARSE JSON*/ -->

<br>
<br>
<table width="100%">
	<tr>
		<td width="50%">
		</td>
		<td>
			<table width="100%">
				<tr>
					<td width = 15%>
						Директору
					</td>
					<td width="85%" style="border-bottom: 1px solid #000; text-align: center;">
						<span>{Lpu_Name}</span>
					</td>
				</tr>
				<tr>
					<td width = 15%>
						&nbsp;
					</td>
					<td width="85%" style="text-align: center;">
						<sup>(наименование организации МО)</sup>
					</td>
				</tr>
			</table>
			<br>
			<table width="100%">
				<tr>
					<td width = 0%>
					</td>
					<td width="100%" style="border-bottom: 1px solid #000; text-align: center;">
						<span>{OrgHead_FIO}</span>
					</td>
				</tr>
				<tr>
					<td width = 0%>
						&nbsp;
					</td>
					<td width="100%" style="text-align: center;">
						<sup>(ФИО руководителя)</sup>
					</td>
				</tr>
			</table>
			<br>
			<table width="100%">
				<tr>
					<td width = 5%>
						от
					</td>
					<td width="95%" style="border-bottom: 1px solid #000; text-align: center;">
						<span>{Person_FIOFrom}</span>
					</td>
				</tr>
				<tr>
					<td width = 5%>
						&nbsp;
					</td>
					<td width="95%" style="text-align: center;">
						<sup>(фамилия, имя, отчество <span>{upperFromDep}</span>)</sup>
					</td>
				</tr>
			</table>
			<table width="100%">
				<tr>
					<td width = 40%>
						проживающий по адресу
					</td>
					<td width="60%" style="border-bottom: 1px solid #000; text-align: center;">
						&nbsp;
					</td>
				</tr>
			</table>
			<table width="100%">
				<tr>
					<td style="border-bottom: 1px solid #000; text-align: center;">
						<span style="font-size: 10px;">{Person_PAddress}</span>
					</td>
				</tr>
			</table>
			<table width="100%">
				<tr>
					<td width = 10%>
						ИНН
					</td>
					<td width="90%" style="border-bottom: 1px solid #000; text-align: center;">
						<span>{Person_Inn}</span>
					</td>
				</tr>
			</table>
			<table width="100%">
				<tr>
					<td width = 30%>
						удост. личности №
					</td>
					<td width="70%" style="border-bottom: 1px solid #000; text-align: center;">
						<span>{Document_SerNum}</span>
					</td>
				</tr>
			</table>
			<table width="100%">
				<tr>
					<td width = 15%>
						выданный
					</td>
					<td width="55%" style="border-bottom: 1px solid #000; text-align: center;">
						<span>{OrgDep_Name}</span>
					</td>
					<td width = 5%>
						от
					</td>
					<td width="20%" style="border-bottom: 1px solid #000; text-align: center;">
						<span>{Document_begDate}</span>
					</td>
					<td width = 5%>
						г.
					</td>
				</tr>
			</table>
		</td>
	</tr>
</table>
<br><br>
<h3 align="center">ЗАЯВЛЕНИЕ</h3>
<br><br>
<table width="100%">
	<tr>
		<td width="30%">
			<span style="font-size: 13px;">Прошу Вас прикрепить {Me}</span>
		</td>
		<td width="70%" style="border-bottom: 1px solid #000; text-align: center;">
			<span style="font-size: 14px;">{Person_FIO}&nbsp;&nbsp;&nbsp;{Person_Birthday} г.р.</span>
		</td>
	</tr>
	<tr>
		<td width = 30%>
			&nbsp;
		</td>
		<td width="70%" style="text-align: center;">
			<sup>(фамилия, имя, отчество прикрепляемого, дата и год рождения)</sup>
		</td>
	</tr>
</table><br>
<table width="100%" style="border-bottom: 1px solid #000;">
	<tr>
		<td width="100%">
			<span style="font-size: 14px;">к {LpuAttach_Name}</span>
		</td>
	</tr>
</table>
<br>
<hr /><br>
<hr /><br>
<br>
<br>
<br>
<table width="100%">
	<tr>
		<td width="10%">
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Дата
		</td>
		<td width="20%" style="border-bottom: 1px solid black; text-align: center;">
			{PersonCard_begDate}
		</td>
		<td width="40%">
			&nbsp;
		</td>
		<td width="10%">
			Подпись
		</td>
		<td width="20%" style="border-bottom: 1px solid black; text-align: center;">
			&nbsp;
		</td>
	</tr>
</table>

</body>
</html>