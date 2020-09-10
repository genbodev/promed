<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title></title>
<style type="text/css">
th.swcieltitle { vertical-align: middle; text-align: center; padding: 2px; font-weight: bold; }
td.swciel { vertical-align: middle; text-align: left; padding: 2px; }
</style>

</head>

<body class="land" style="margin: 5px; padding: 5px; font-family: tahoma, verdana; font-size: 10pt; "><p>УТВЕРЖДЕНО</p>
<p style="text-align: right">Приказ Министерства
<br />здравоохранения
<br />Российской Федерации
<br />от 19.04.99 г. N 135</p>
<p>{Lpu_Name}</p>
<p style="text-align: right">Ф. N 027-2/У Утв. МЗ
<br />Российской Федерации
<br />19 апреля 1999 г. N 135
<br /></p>
<p style="text-align: center">ПРОТОКОЛ
<br />НА СЛУЧАЙ ВЫЯВЛЕНИЯ У БОЛЬНОГО ЗАПУЩЕННОЙ ФОРМЫ
<br />ЗЛОКАЧЕСТВЕННОГО НОВООБРАЗОВАНИЯ
<br />(КЛИНИЧЕСКАЯ ГРУППА IV)
<br />
<br />(составляется в 2-х экземплярах: первый остается в
<br />медицинской карте стационарного больного /амбулаторной
<br />карте/, второй пересылается в онкологический диспансер
<br />по месту жительства больного)
<br /></p>
<p>N медицинской карты стационарного больного (амбулаторной карты): {Num_Card}</p>
<p>01. Составлен лечебным учреждением (название, адрес): {LpuS_Name} {LpuS_Address}</p>
<p>02. Фамилия {Person_SurName}
<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Имя {Person_FirName}
<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Отчество {Person_SecName}</p>
<p>03. Дата рождения: {Person_BirthDay}</p>
<p>04. Пол: {Sex_Name}</p>
<p>05. Домашний адрес: {Person_Address}</p>
<p>06. Основной диагноз:
<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;06.1 Локализация опухоли: {Diag_FullName}
<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;06.2 Морфологический тип опухоли: {OnkoDiag_FullName}
<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;06.3 Стадия опухолевого процесса по системе TNM: T (0-4,х) <u>{OnkoT_Name}</u> N (0-3,х) <u>{OnkoN_Name}</u> M (0,1,х) <u>{OnkoM_Name}</u>
<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;06.4 Стадия опухолевого процесса: {TumorStage_Name}</p>
<p>07. Локализация отдаленных метастазов (при IV стадии заболевания): {TumorDepo}</p>
<p>08. Метод подтверждения диагноза: {OnkoDiagConfType_Name}</p>
<p>09. Дата установления запущенности рака: число _______________ месяц _________ год _______</p>
<p>10. Дата появления первых признаков заболевания: {MorbusOnko_firstSignDT}</p>
<p>11. Первое обращение больного за медицинской помощью по поводу заболевания: {MorbusOnko_firstVizitDT} в какое лечебное учреждение (название, адрес): {LpuF_Name} {LpuF_Address}</p>
<p>12. Дата установления первичного диагноза злокачественного новообразования: {MorbusOnko_setDiagDT} учреждение,  где впервые был установлен диагноз рака (название, адрес): {LpuD_Name} {LpuD_Address}</p>
<p>13. Указать в хронологическом порядке этапы обращения  больного  к  врачам  и  в  лечебные
учреждения по поводу данного заболевания,  о каждом лечебном учреждении необходимо отметить
следующее:
<table style="border-collapse: collapse;" width="100%" border="1px">
	<thead>
		<tr>
			<th class="swcieltitle">Наименование учреждения</th>
			<th class="swcieltitle">Дата обращения</th>
			<th class="swcieltitle">Методы исследования</th>
			<th class="swcieltitle">Поставленный диагноз</th>
			<th class="swcieltitle">Проведенное лечение</th>
		</tr>
	</thead>
	<tbody>
{CurData}
		<tr>
			<td class="swciel">{CurData_Lpu}</td>
			<td class="swciel">{CurData_Date}</td>
			<td class="swciel">{CurData_Res}</td>
			<td class="swciel">{CurData_Diag}</td>
			<td class="swciel">{CurData_Treat}</td>
		</tr>
{/CurData}
	</tbody>
</table>
</p>
<p>14. Причины поздней диагностики: {OnkoLateDiagCause_Name}</p>
<p>15. Данные клинического разбора настоящего случая: {EvnOnkoNotifyNeglected_ClinicalData}
<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Наименование учреждения, где проведена конференция: {LpuC_Name}
<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Дата конференции: {EvnOnkoNotifyNeglected_setConfDT}</p>
<p>Организационные выводы: {EvnOnkoNotifyNeglected_OrgDescr}</p>
<p style="margin-left: 50px; margin-top: 30px;">Подпись врача, составившего протокол ____________________________________________
<br />Подпись главного врача __________________________________________________________
<br />Дата составления протокола: {EvnOnkoNotifyNeglected_setNotifyDT}</p>
</body></html>