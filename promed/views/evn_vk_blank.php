<html>
<head>
<title>Протокол ВК</title>

<style type="text/css">
h3 { text-align: center }
</style>
</head>

<body class="land">
<!-- /*NO PARSE JSON*/ -->

<h3>Протокол ВК</h3>
<br />

№ {EvnVK_NumProtocol} от {EvnVK_setDT} <br /><br />

1. Фамилия, имя, отчество пациента: {Person_Fio} <br />
2. Дата рождения: {Person_BirthDay} <br />
3. Пол: {Person_Sex} <br />
4. Адрес места жительства гражданина (при отсутствии места жительства указывается<br /> адрес пребывания, фактического проживания на территории Российской Федерации): {Person_Address} <br />
5. Статус пациента: {PatientStatusType_Name} <br />
6. Профессия пациента: {EvnVK_Prof} <br />
7. Причина обращения: {CauseTreatmentType_Name} <br />
8. Диагноз основной: {diag1} <br />
9. Диагноз сопутствующий: {diag2} <br />
10. Вид экспертизы: {ExpertiseNameType_Name} <br />
11. Хар-ка случая экспертизы: {ExpertiseEventType_Name} <br />
12. Предмет экспертизы: {ExpertiseNameSubjectType_Name} <br />
13. Нетрудоспособность. <br />
	<div style="margin-left: 40px;">
		Больничный лист: {EvnStick_all} <br />
		Период освобождения от работы: {EvnStickWorkRelease_all} <br />
		Экспертиза временной нетрудоспособности №: {EvnVK_ExpertiseStickNumber} <br />
		Срок нетрудоспособности, дней: {EvnVK_StickPeriod} <br />
		Длительность пребывания в ЛПУ, дней: {EvnVK_StickDuration} <br />
	</div>
14 Медико-социальная экспертиза. <br />
	<div style="margin-left: 40px;">
		Дата направления в бюро МСЭ (или др. спец. учреждения): {EvnVK_DirectionDate} <br />
		Дата получения заключения МСЭ (или др. спец. учреждений): {EvnVK_ConclusionDate} <br />
		Срок действия заключения: {EvnVK_ConclusionPeriodDate} <br />
		Заключение МСЭ: {EvnVK_ConclusionDescr} <br />
		Доп. информация: {EvnVK_AddInfo} <br />
	</div>
15. Отклонение от стандартов:({EvnVK_isAberration}) {EvnVK_AberrationDescr} <br />
16. Дефекты, нарушения и ошибки:({EvnVK_isErrors}) {EvnVK_ErrorsDescr} <br />
17. Достижение результата или исхода:({EvnVK_isResult}) {EvnVK_ResultDescr} <br />
18. Заключение экспертов, рекомендации: {EvnVK_ExpertDescr} <br />
19. Решение ВК: {EvnVK_DecisionVK} <br /><br />

<table width="100%">
	<tr>
		<td style="text-align: left;" width="40%">Председатель врачебной комиссии:</td>
		<td width="20%"></td>
		<td></td>
	</tr>
	<tr>
		<td></td>
		<td style="text-align: center; font-size: 9pt;">________________<br />(подпись)</td>
		<td style="text-align: center; font-size: 9pt;"><font style="text-decoration: underline"> {vkchairman} </font><br />(расшифровка подписи)</td>
	</tr>
</table>

<br /><br />

<table width="100%">
	<tr>
		<td style="text-align: left;" width="40%">Члены врачебной комиссии:</td>
		<td width="20%"></td>
		<td></td>
	</tr>
	
	{vkexperts}
	<tr>
		<td></td>
		<td style="text-align: center; font-size: 9pt;">________________<br />(подпись)<br /><br /></td>
		<td style="text-align: center; font-size: 9pt;"><font style="text-decoration: underline"> {MP_Person_Fio} </font><br />(расшифровка подписи)<br /><br /></td>
	</tr>
	{/vkexperts}
</table>

</body>

</html>