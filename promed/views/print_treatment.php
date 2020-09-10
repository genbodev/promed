<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Журнал обращений: печать</title>
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

<div style="font-size: 16pt; font-weight: bold; margin-bottom: 1em; text-align: center;">Журнал обращений: печать</div>

<div style="margin-bottom: 2em;">
	<div><span style="font-weight: bold;">Номер регистрации:</span> {Treatment_Reg}</div>
	<div><span style="font-weight: bold;">Дата регистрации:</span> {Treatment_DateReg}</div>
	<div><span style="font-weight: bold;">Срочность:</span> {TreatmentUrgency}</div>
	<div><span style="font-weight: bold;">Кратность:</span> {TreatmentMultiplicity}</div>
	<div><span style="font-weight: bold;">Тип инициатора обращения:</span> {TreatmentSenderType}</div>
	<div><span style="font-weight: bold;">Инициатор обращения:</span> {Treatment_SenderDetails}</div>
	<div><span style="font-weight: bold;">Тип обращения:</span> {TreatmentType}</div>
	<div><span style="font-weight: bold;">Категория обращения:</span> {TreatmentCat}</div>
	<div><span style="font-weight: bold;">Адресат обращения:</span> {TreatmentRecipientType}</div>
	<div><span style="font-weight: bold;">ЛПУ адресат обращения:</span> {Lpu_r}</div>
	<div><span style="font-weight: bold;">Субъект обращения:</span> {TreatmentSubjectType}</div>
	<div><span style="font-weight: bold;">Организация субъект обращения:</span> {Org}</div>
	<div><span style="font-weight: bold;">ЛПУ субъект обращения:</span> {Lpu_s}</div>
	<div><span style="font-weight: bold;">Врач субъект обращения:</span> {MedPersonal}</div>
	<div><span style="font-weight: bold;">Место работы врача субъекта обращения:</span> {Lpu_m}</div>
	<div><span style="font-weight: bold;">Текст обращения:</span> {Treatment_Text}</div>
	<div><span style="font-weight: bold;">Способ получения обращения:</span> {TreatmentMethodDispatch}</div>
	<div><span style="font-weight: bold;">Примечание:</span> {Treatment_Comment}</div>
	<div><span style="font-weight: bold;">Результат рассмотрения:</span> {TreatmentReview}</div>
	<div><span style="font-weight: bold;">Дата рассмотрения:</span> {Treatment_DateReview}</div>
</div>

</body></html>