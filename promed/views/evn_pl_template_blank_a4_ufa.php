<html>
<head>
<title>{EvnPLBlankTemplateTitle}</title>
<style type="text/css">
@page port { size: portrait }
@page land { size: landscape }
body { margin: 0px; padding: 0px; }
table { border-collapse: collapse; }
span, div, td { font-family: times, tahoma, verdana; font-size: 12px; }
td { vertical-align: top; }
th { text-align: center; font-size: 12px; border-collapse: collapse; border: 1px solid black; }
</style>

<style type="text/css" media="print">
@page port { size: portrait }
@page land { size: landscape }
body { margin: 0px; padding: 0px; }
span, div, td { font-family: times, tahoma, verdana; font-size: 12px; }
td { vertical-align: top; }
th { text-align: center; font-size: 12px; border-collapse: collapse; border: 1px solid black; }
.cell { border: 1px solid #000; border-collapse: collapse; vertical-align: top; }
</style>
</head>

<body>

<table style="width: 100%;">
<tr><td style="width: 75%;">{Lpu_Name}</td><td style="width: 25%; text-align: center;">Медицинская документация<br />Форма №025-12у</td></tr>
<tr><td style="padding-left: 5em; font-weight: bold">ТАЛОН АМБУЛАТОРНОГО ПАЦИЕНТА {EvnPL_setDate}</td><td style="text-align: center;">Утверждена приказом МЗ РБ<br />№686-Д от «28» июля 2009</td></tr>
</table>

<table style="width: 100%;"><tr>
<td style="width: 15%;">№ медкарты</td><td style="width: 15%; font-weight: bold;">{PersonCard_Code}</td>
<td style="width: 20%;">Врачебный участок</td><td style="width: 50%; font-weight: bold;">{LpuRegion_Name}</td>
</tr></table>

<table style="width: 100%;"><tr>
<td style="width: 55%; font-weight: bold; font-size: 14px;">{Person_Fio} &nbsp;&nbsp;&nbsp; {Person_Birthday} &nbsp;&nbsp;&nbsp; {Sex_Name}</td><td style="width: 20%;">Паспорт гражданина РФ</td><td style="width: 25%; font-weight: bold;">{Person_Docum}</td>
</tr></table>

<table style="width: 100%;"><tr>
<td style="width: 10%;">1. ИНН</td><td style="width: 20%; font-weight: bold;">{Person_INN}</td>
<td style="width: 10%;">2. СНИЛС</td><td style="width: 60%; font-weight: bold;">{Person_Snils}</td>
</tr></table>

<table style="width: 100%;">
<tr><td style="width: 15%;">3. Представитель</td><td style="width: 85%;">{PersonDeputy_Fio}</td></tr>
</table>

<table style="width: 100%;">
<tr><td style="width: 20%;">4. Страховая организация:<br />&nbsp;</td><td style="width: 80%; font-weight: bold;">{OrgSmo_Name}</td></tr>
</table>

<table style="width: 100%;">
<tr><td style="width: 15%;">5. Полис ОМС:</td><td style="width: 85%; font-weight: bold; font-size: 14px;">{Polis_Ser} {Polis_Num}</td></tr>
</table>

<table style="width: 100%;"><tr>
<td style="width: 25%;">6. Дата начала действия полиса:</td><td style="width: 15%; font-weight: bold;">{Polis_begDate}</td>
<td style="width: 30%;">7. Дата окончания действия полиса:</td><td style="width: 30%; font-weight: bold;">{Polis_endDate}</td>
</tr></table>

<table style="width: 100%;">
<tr><td style="width: 10%;">8. Адрес:<br />&nbsp;</td><td style="width: 80%; font-weight: bold;">{Person_Address}</td></tr>
</table>

<table style="width: 100%;">
<tr><td style="width: 20%;">9. Социальный статус:</td><td style="width: 80%; font-weight: bold;">{SocStatus_Name}</td></tr>
</table>

<table style="width: 100%;">
<tr><td style="width: 20%;">10. Место работы (учебы):</td><td style="width: 80%; font-weight: bold;">{OrgJob_Name}</td></tr>
<tr><td colspan="2">11. Инвалидность:&nbsp;&nbsp;&nbsp;<span style="{PrivilegeType_83}">1-Iгр.</span>,&nbsp;&nbsp;&nbsp;&nbsp;<span style="{PrivilegeType_82}">2-IIгр.</span>,&nbsp;&nbsp;&nbsp;<span style="{PrivilegeType_81}">3-IIIгр.</span>,&nbsp;&nbsp;&nbsp;<span style="{PrivilegeType_84}">4-ребенок инвалид</span>,&nbsp;&nbsp;&nbsp;<span style="{PrivilegeType_0}">5-инвалид с детства</span>,&nbsp;&nbsp;&nbsp;<span style="{PrivilegeType_0}">6-установлена впервые в жизни</span>,&nbsp;&nbsp;&nbsp;<span style="{PrivilegeType_0}">7-снята</span></td></tr>
<tr><td>12. Вид оплаты:</td><td style="font-weight: bold;">{PayType_Name}</td></tr>
<tr><td>13. Специалист:</td><td style="font-weight: bold;">{MedPersonal_Fio} &nbsp;&nbsp;&nbsp; {LpuSectionProfile_Code} &nbsp;&nbsp;&nbsp; {LpuSectionProfile_Name}</td></tr>
<tr><td>14. Место обслуживания:</td><td style="font-weight: bold;">{ServiceType_Name}</td></tr>
</table>

<table style="width: 100%;">
<tr><td style="width: 3%;">15.</td><td style="width: 40%;">Код вида первичной медико-санитарной помощи:</td>
<td style="width: 15%; border-bottom: 1px solid #000;">&nbsp;</td><td style="width: 42%;">&nbsp;</td></tr>
<tr><td>16.</td><td colspan="3">Цель посещения&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;:&nbsp;&nbsp;1-заболевание&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;2-профосмотр&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;3-патронаж&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;4-"Д"наблюдение</td></tr>
<tr><td colspan="4" style="padding-left: 12em;">4.1.-“Д”наблюдение по родовым сертификатам&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;5-реабилитация</td></tr>
<tr><td>17.</td><td colspan="3">Результат обращения:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;а.1-выздоровление&nbsp;&nbsp;&nbsp;2-улучшение&nbsp;&nbsp;&nbsp;3-динам.наблюдение&nbsp;&nbsp;&nbsp;4-ухудшение&nbsp;&nbsp;&nbsp;5-cмерть</td></tr>
<tr><td colspan="4" style="padding-left: 12em;">
б.Направлен:&nbsp;&nbsp;&nbsp;1-в кругл.стационар&nbsp;&nbsp;&nbsp;2-в ДнСт&nbsp;&nbsp;&nbsp;3-на консультацию&nbsp;&nbsp;&nbsp;3.1.-в т.ч.др.ЛПУ<br />
в.Оформление документации: 1-справка&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;1.1.-сан/кур.лечение&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;2-сан/кур.карта&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;3-МСЭК</td></tr>
<tr><td>18.</td><td colspan="3">Посещения:</td></tr>
</table>

<table style="width: 100%; border-collapse: collapse;"><tr>
<th style="width: 15%;">Дата</th><th style="width: 20%;">Код приема</th><th style="width: 15%;">УКЛ</th><th style="width: 15%;">Дата</th><th style="width: 20%;">Код приема</th><th style="width: 15%;">УКЛ</th></tr>
<tr><td class="cell">&nbsp;</td><td class="cell">&nbsp;</td><td class="cell">&nbsp;</td><td class="cell">&nbsp;</td><td class="cell">&nbsp;</td><td class="cell">&nbsp;</td></tr>
<tr><td class="cell">&nbsp;</td><td class="cell">&nbsp;</td><td class="cell">&nbsp;</td><td class="cell">&nbsp;</td><td class="cell">&nbsp;</td><td class="cell">&nbsp;</td></tr>
<tr><td class="cell">&nbsp;</td><td class="cell">&nbsp;</td><td class="cell">&nbsp;</td><td class="cell">&nbsp;</td><td class="cell">&nbsp;</td><td class="cell">&nbsp;</td></tr>
<tr><td class="cell">&nbsp;</td><td class="cell">&nbsp;</td><td class="cell">&nbsp;</td><td class="cell">&nbsp;</td><td class="cell">&nbsp;</td><td class="cell">&nbsp;</td></tr>
</table>

<table style="width: 100%; margin-top: 0.5em;"><tr>
<td style="width: 3%;">19.</td>
<td style="width: 32%;">Диагноз предварительный (МКБ-10)</td>
<td style="width: 10%; border-bottom: 1px solid #000;">&nbsp;</td>
<td style="width: 30%; padding-left: 7em;">Дата регистрации</td>
<td style="width: 10%; border-bottom: 1px solid #000;">&nbsp;</td>
<td style="width: 15%;">&nbsp;</td>
</tr><tr>
<td>20.</td><td>Диагноз заключительный (МКБ-10)</td><td style="border-bottom: 1px solid #000;">&nbsp;</td><td colspan="4">&nbsp;</td>
</tr><tr>
<td>20а.</td><td colspan="5">Характер основного заболевания: 0-здоров; 1-острое заболевание(+); 2-хроническое заболевание, выявленное впервые(+);</td>
</tr><tr>
<td>&nbsp;</td><td colspan="5">3-хроническое заболевание известное ранее(-); 4-обострение хронического заболевания(-); 5-отравление (+); 6-травма(+)</td>
</tr><tr>
<td>20б.</td><td colspan="5">Диспансерный учет&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;1-состоит&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;2-взят&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;3-снят</td>
</tr><tr>
<td>&nbsp;</td><td colspan="5">Причина снятия&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;1-выздоровление&nbsp;&nbsp;&nbsp;&nbsp;2-переезд&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;3-перевод&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;4-смерть</td>
</tr><tr>
<td>21.</td><td colspan="5">Травма:&nbsp;&nbsp;производственная&nbsp;&nbsp;1-промышленная&nbsp;&nbsp;2-транспортная&nbsp;&nbsp;&nbsp;2.1.-в т.ч.автодорожная&nbsp;&nbsp;&nbsp;3-сельскохоз-ная&nbsp;&nbsp;&nbsp;&nbsp;4-прочая</td>
</tr><tr>
<td>&nbsp;</td><td colspan="5" style="padding-left: 4em;">непроизводственная&nbsp;&nbsp;6-бытовая 7-уличная 8-транспортная&nbsp;&nbsp;8.1.-в т.ч.автодорожная&nbsp;&nbsp;9-школьная&nbsp;&nbsp;10-спорт&nbsp;&nbsp;11-прочая</td>
</tr><tr>
<td>22.</td><td>Диагноз сопутствующий (МКБ-10)</td><td style="border-bottom: 1px solid #000;">&nbsp;</td><td colspan="4">&nbsp;</td>
</tr><tr>
<td>23а.</td><td colspan="5">Характер сопут.заболевания: 1-острое или впервые в жизни установленное(+), 2-диагноз установлен в предыдущем году или ранее(-)</td>
</tr><tr>
<td>23б.</td><td colspan="5">Диспансерный учет&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;1-состоит&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;2-взят&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;3-снят</td>
</tr><tr>
<td>24.</td><td>Осложнение основного д-за (МКБ-10)</td><td style="border-bottom: 1px solid #000;">&nbsp;</td><td colspan="4">&nbsp;</td>
</tr><tr>
<td>24а.</td><td colspan="5">Характер осложнения: 1-острое или впервые в жизни установленное(+), 2-диагноз установлен в предыдущем году или ранее(-)</td>
</tr></table>

<table style="width: 100%; margin-top: 0.5em;"><tr>
<td style="width: 3%;">25.</td>
<td style="width: 27%;">Документ врем.нетруд-ности:</td>
<td style="width: 10%;">1.Открыт</td>
<td style="width: 10%; border-bottom: 1px solid #000;">&nbsp;</td>
<td style="width: 10%; padding-left: 1em;">2.Продлен</td>
<td style="width: 10%; border-bottom: 1px solid #000;">&nbsp;</td>
<td style="width: 10%; padding-left: 1em;">3.Закрыт</td>
<td style="width: 10%; border-bottom: 1px solid #000;">&nbsp;</td>
<td style="width: 10%;">&nbsp;</td>
</tr><tr>
<td>25а.</td><td colspan="8">Причина выдачи: 1-заболевание&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;2-по уходу&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;3-карантин&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;4-аборт&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;5-отпуск по беременности&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;6-сан/кур.лечение</td>
</tr><tr>
<td>25б.</td><td colspan="2">По уходу:&nbsp;&nbsp;&nbsp;1-мужчина&nbsp;&nbsp;&nbsp;2-женщина</td>
<td>полных лет</td><td style="border-bottom: 1px solid #000;">&nbsp;</td><td colspan="4">&nbsp;</td>
</tr><tr>
<td>26.</td><td colspan="8">Рецептурный бланк:</td>
</tr></table>

<table style="width: 100%; border-collapse: collapse;"><tr>
<th style="width: 10%;">Дата</th><th style="width: 10%;">Серия</th><th style="width: 10%;">Номер</th><th style="width: 10%;">Д-з по МКБ-10</th><th style="width: 30%;">Наименование препарата</th><th style="width: 15%;">Дозировка</th><th style="width: 5%;">Кол-во</th><th style="width: 10%;">Дата постановки на учёт</th></tr>
<tr><td class="cell">&nbsp;</td><td class="cell">&nbsp;</td><td class="cell">&nbsp;</td><td class="cell">&nbsp;</td><td class="cell">&nbsp;</td><td class="cell">&nbsp;</td><td class="cell">&nbsp;</td><td class="cell">&nbsp;</td></tr>
<tr><td class="cell">&nbsp;</td><td class="cell">&nbsp;</td><td class="cell">&nbsp;</td><td class="cell">&nbsp;</td><td class="cell">&nbsp;</td><td class="cell">&nbsp;</td><td class="cell">&nbsp;</td><td class="cell">&nbsp;</td></tr>
<tr><td class="cell">&nbsp;</td><td class="cell">&nbsp;</td><td class="cell">&nbsp;</td><td class="cell">&nbsp;</td><td class="cell">&nbsp;</td><td class="cell">&nbsp;</td><td class="cell">&nbsp;</td><td class="cell">&nbsp;</td></tr>
<tr><td class="cell">&nbsp;</td><td class="cell">&nbsp;</td><td class="cell">&nbsp;</td><td class="cell">&nbsp;</td><td class="cell">&nbsp;</td><td class="cell">&nbsp;</td><td class="cell">&nbsp;</td><td class="cell">&nbsp;</td></tr>
</table>

<div style="margin-top: 3em;">Подпись специалиста __________________________________</div>

</body>

</html>