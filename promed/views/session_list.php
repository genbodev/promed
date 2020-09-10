<html>
<head>
<title>Залогиненные пользователи на данный момент</title>
<style type="text/css">
@page port { size: portrait }
@page land { size: landscape }
body { margin: 0px; padding: 0px; }
table { border-collapse: collapse; }
span, div, td { font-family: tahoma, verdana; font-size: 10pt; }
td { vertical-align: middle; border: 1px solid #000; }
</style>

<style type="text/css" media="print">
@page port { size: portrait }
@page land { size: landscape }
body { margin: 0px; padding: 0px; }
span, div, td { font-family: tahoma, verdana; font-size: 10pt; }
td { vertical-align: middle; border: 1px solid #ccc; }
</style>
</head>

<body class="land">
<h3>Залогиненные пользователи</h3>
Залогиненных сессий: {count}<br/>
Всего сессий: {count_all}<br/>
<table style="width: 100%; border: none; border-collapse: collapse;" cellspacing="0" cellpadding="2"><tbody>
<tr style="background-color: #eee; font-weight: bold; text-align: center;">
	<td style="width: 5%;">№ п/п</td>
	<td style="width: 10%;">pmuser_id</td>
	<td style="width: 8%;">Логин</td>
	<td style="width: 10%;">Имя</td>
	<td style="width: 10%;">Описание</td>
	<td style="width: 10%;">Группы</td>
	<td style="width: 10%;">Org_id</td>
	<td style="width: 10%;">Время посл.актив.</td>
	
</tr>
<tr style="background-color: #eee; font-weight: bold; text-align: center;">
	<td>1</td>
	<td>2</td>
	<td>3</td>
	<td>4</td>
	<td>5</td>
	<td>6</td>
	<td>7</td>
	<td>8</td>
</tr>

{sessions}
<tr>
<td style="text-align: center;">{number}</td>
<td style="text-align: center;">{pmuser_id}</td>
<td style="text-align: center;">{login}</td>
<td style="text-align: center;">{surname} {firname} {secname}</td>
<td style="text-align: center;">{about}</td>
<td style="text-align: center;">{groups}</td>
<td style="text-align: center;">{org_id}</td>
<td style="text-align: center;">{time}</td>
</tr>
{/sessions}

</tbody></table>

</body>

</html>