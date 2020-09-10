<html>
<head>
<title>{EvnPLTemplateBlankTitle}</title>
<style type="text/css">
@page port { size: portrait }
@page land { size: landscape }
body { margin: 0px; padding: 0px; }
table { border-collapse: collapse; }
span, div, td { font-family: times, tahoma, verdana; font-size: 9pt; }
th { text-align: center; font-size: 9pt; border-collapse: collapse; border: 1px solid black; }
.cell { border: 1px solid #000; border-collapse: collapse; vertical-align: top; }
.block {height: 13pt; width: 13pt; border: 1px solid #000}
</style>

<style type="text/css" media="print">
@page port { size: portrait }
@page land { size: landscape }
body { margin: 0px; padding: 0px; }
span, div, td { font-family: times, tahoma, verdana; font-size: 9pt; }
th { text-align: center; font-size: 9pt; border-collapse: collapse; border: 1px solid black; }
.cell { border: 1px solid #000; border-collapse: collapse; vertical-align: top; }
.block {height: 13pt; width: 13pt; border: 1px solid #000}
</style>
</head>

<body>

<table style="width: 40%; text-align: center; float: left">
<tr><td style="">Министерство здравоохранения <br>и&nbsp;социального развития Российской Федерации</td></tr>
<tr><td style="">{Lpu_Name}</td></tr>
<tr><td style="">{LpuAddress}</td></tr>
<tr><td style="text-align: center;">Код ОГРН: <span style="letter-spacing: 0;">{Lpu_OGRN}</span></td></tr>
</table>

<table style="width: 30%; text-align: left; float: right">
<tr><td style=""> <br>
Медицинская документация  <br>
Форма № 025-12/у________   <br>
утверждена приказом Минздравсоцразвития России <br>
от 22 ноября 2004 г. № 255
</td></tr>
</table>

<table style="width: 30%; text-align: left;">
    <tr><td style="padding-left:80px; font-size:110%"> <br><br>{TimetableType}</td></tr>
</table>

<div style="clear: both; margin-bottom: 5px;"></div>

<table style="width: 100%; margin-bottom: 16px;">
<tr style="font-weight: bold;"><td colspan="2" style="letter-spacing: 0.1em; text-align: center;padding-bottom: 8px">ТАЛОН АМБУЛАТОРНОГО ПАЦИЕНТА</td></tr>
<tr>
<td style="width: 50%; padding-right: 2em; text-align: right;">№ медицинской карты <span style='border-bottom: 1px solid;'><b>{PersonCard_Code}</b></span></td>
<td style="width: 50%; padding-left: 2em;">
	<table style='border-collapse:collapse;'>
	 <tr>
	  <td width=150>Дата <span style='border-bottom: 1px solid;'><b>{TimetableGraf_recDate}</b></span></td>
	 </tr>
	</table>
</td>
</tr>
</tr></table>



<table style='border-collapse:collapse; margin-bottom: 5px;'>
 <tr>
  <td width=140>1.Код категории льготы</td>
  <td><b>{PrivilegeType_Code}</b></td>
 </tr>
</table>

<table style='border-collapse:collapse;margin-bottom: 5px;'>
 <tr>
  <td width=200>2.Номер страхового полиса ОМС</td>
  <td style='font-size: 14px;'><b>{Polis_Ser}&nbsp;{Polis_Num}</b></td>
	 <td style="padding:20px;">{OrgSmo_Name}</td>
 </tr>
</table>

<table style='border-collapse:collapse;margin-bottom: 5px;'>
 <tr>
  <td width=200>3.СНИЛС</td>
  <td><b>{Person_Snils}</b></td>
 </tr>
</table>

<table style='border-collapse:collapse;margin-bottom: 6px;width: 100%;'>
 <tr>
  <td width=123>4.Пациент: код <sup>1)</sup></td>
  <td class="block"></td>
  <td class="block"></td>
  <td class="block"></td>
  <td class="block"></td>
  <td class="block"></td>
  <td class="block"></td>
  <td class="block"></td>
  <td class="block"></td>
  <td class="block"></td>
  <td class="block"></td>
  <td width=40 style='padding-left: 1em;'>Ф.и.о. </td>
  <td style='border-bottom: 1px solid;'><b>{Person_Fio}</b></td>
 </tr>
</table>

<table style='border-collapse:collapse;margin-bottom: 3px;'>
 <tr>
  <td width=150>5.Пол <sup>4)</sup>:&nbsp;&nbsp;&nbsp;<b>{Sex_Name}</b></td>
  <td width=120 style='padding-left: 1em;'>6.Дата рождения</td>
  <td style='font-size: 14px;'><b>{Person_Birthday}</b></td>
 </tr>
</table>

<table style='border-collapse:collapse;margin-bottom: 3px;'>
 <tr>
  <td>7.Документ, удостоверяющий личность (название, серия и номер) <sup>4)</sup>:&nbsp;<b>{DocumentType_Name}&nbsp;{Document_Ser}&nbsp;{Document_Num}</b></td>
 </tr>
</table>

<table style='border-collapse:collapse;margin-bottom: 3px; width: 100%;'>
 <tr>
  <td width=260>8.Адрес регистрации по месту жительства <sup>4)</sup>:</td>
  <td style='border-bottom: 1px solid;'><b>{UAddress_Name}</b></td>
 </tr>
</table>

<table style='border-collapse:collapse;margin-bottom: 3px; width: 100%;'>
 <tr>
  <td>9.Житель<sup>4)</sup>:&nbsp;&nbsp;&nbsp;<b>{KLAreaType_Name}</b></td>
 </tr>
</table>

<table style='border-collapse:collapse;margin-bottom: 3px; width: 100%;'>
 <tr>
  <td width=160>10.Социальный статус, в т.ч. занятость: <sup>4)</sup>:&nbsp;&nbsp;&nbsp;<b>{SocStatus_Name}</b></td>
 </tr>
</table>

<table style='border-collapse:collapse;margin-bottom: 3px; width: 100%;'>
 <tr>
  <td>11.Инвалидность: 1 – I гр., 2 – II гр., 3 – III гр., 4-ребенок-инвалид, 5-инвалид с детства, 6-установлена впервые в жизни, 7-снята</td>
 </tr>
</table>

<table style='border-collapse:collapse;margin-bottom: -1px;'>
 <tr>
  <td width=160>12.Специалист: код <sup>1)</sup>:{MedPersonal_TabCode}</td>
  <td width=350 style='padding-left: 1em;'>ф.и.о. <b>{MSF_Fio}</b></td>
 </tr>
</table>

<table style='border-collapse:collapse;margin-bottom: 3px;'>
 <tr>
  <td width=160>13.Специалист: код <sup>2)</sup>:</td>
  <td class="block"></td>
  <td class="block"></td>
  <td class="block"></td>
  <td class="block"></td>
  <td class="block"></td>
  <td class="block"></td>
  <td class="block"></td>
  <td class="block"></td>
  <td class="block"></td>
  <td class="block"></td>
  <td width=50 style='padding-left: 1em;'>ф.и.о.</td>
 </tr>
</table>

<table style='border-collapse:collapse;margin-bottom: 3px; width: 100%;'>
 <tr>
  <td width=160>14.Вид оплаты:</td>
  <td>1-ОМС; 2-бюджет; 3-платные услуги, в т.ч. 4-ДМС; 5-другое</td>
 </tr>
</table>

<table style='border-collapse:collapse;margin-bottom: 3px; width: 100%;'>
 <tr>
  <td width=160>15.Место обслуживания:</td>
  <td>1-поликлиника, 2-на дому, в т.ч. 3-актив.</td>
 </tr>
</table>

<table style='border-collapse:collapse;margin-bottom: 3px; width: 100%;'>
 <tr>
  <td width=160>16.Цель посещения:</td>
  <td>1-заболевание, 2-профосмотр; 3-патронаж; 4-другое</td>
 </tr>
</table>

<table style='border-collapse:collapse;margin-bottom: 6px;width: 100%;'>
 <tr>
  <td>17.Результат обращения <sup>5)</sup>: &nbsp; случай закончен:  1-выздоровл.; 2-улучшение; 3-динамическое набл., направлен: 4-на госпитализацию, 5-в дневной стационар, <br>6-стационар на дому, 7-на консультацию, 8-на консультацию в др. ЛПУ, 9-справка для получения путевки, 10-санаторно-курортная карта
</td>
 </tr>
</table>

<small>1) – при использовании кода принятого в ЛПУ,	2) - заполняется при учете работы среднего мед. персонала<br>
3) – при оплате по посещению проставляется код посещения или стандарта медицинской помощи (СМП)</small>


<table style='border-collapse:collapse;margin-bottom: 3px;'>
 <tr>
  <td width=160>18.Диагноз код МКБ:</td>
  <td class="block"></td>
  <td class="block"></td>
  <td class="block"></td>
  <td class="block"></td>
  <td class="block"></td>
 </tr>
</table>

<table style='border-collapse:collapse;margin-bottom: 3px;'>
 <tr>
  <td width=300 colspan="8">19.Код мед. услуги (посещения, СМП, КЭС) <sup>3)</sup>:</td>
  <td class="block"></td>
  <td class="block"></td>
  <td class="block"></td>
  <td class="block"></td>
  <td class="block"></td>
  <td class="block"></td>
  <td class="block"></td>
  <td class="block"></td>
  <td class="block"></td>
  <td class="block"></td>
  <td class="block"></td>
  <td class="block"></td>
  <td class="block"></td>
  <td class="block"></td>
 </tr>
 <tr>
  <td></td>
  <td class="block"></td>
  <td class="block"></td>
  <td class="block"></td>
  <td class="block"></td>
  <td class="block"></td>
  <td class="block"></td>
  <td class="block"></td>
  <td class="block"></td>
  <td class="block"></td>
  <td class="block"></td>
  <td class="block"></td>
  <td class="block"></td>
  <td class="block"></td>
  <td class="block"></td>  
  <td class="block"></td>
  <td class="block"></td>
  <td class="block"></td>
  <td class="block"></td>
  <td class="block"></td>
  <td class="block"></td>
  <td class="block"></td>
 </tr>
 <tr>
  <td></td>
  <td class="block"></td>
  <td class="block"></td>
  <td class="block"></td>
  <td class="block"></td>
  <td class="block"></td>
  <td class="block"></td>
  <td class="block"></td>
  <td class="block"></td>
  <td class="block"></td>
  <td class="block"></td>
  <td class="block"></td>
  <td class="block"></td>
  <td class="block"></td>
  <td class="block"></td>  
  <td class="block"></td>
  <td class="block"></td>
  <td class="block"></td>
  <td class="block"></td>
  <td class="block"></td>
  <td class="block"></td>
  <td class="block"></td>
 </tr>
</table>

<table style='border-collapse:collapse;margin-bottom: 3px; width: 100%;'>
 <tr>
  <td>20.Характер заболевания: 1-острое (+), впервые в жизни установленное хроническое (+); 2 – диагноз установлен в предыдущ. году или ранее (-)
</td>
 </tr>
</table>

<table style='border-collapse:collapse;margin-bottom: 3px; width: 100%;'>
 <tr>
  <td>21.Диспансерный учет: 1-состоит,	 2 – взят, 	3 – снят, 	в.т. 4 – по выздоровлению</td>
 </tr>
</table>

<table style='border-collapse:collapse;margin-bottom: 3px; width: 100%;'>
 <tr>
  <td width=70 valign="top">22.Травма:</td>
  <td>- производственная: 1 – промышленная; 2 – транспортная, в т.ч. 3 – ДТП; 4 – селькохозяйственная; 5 – прочие <br>
  - непроизводственная: 6 – бытовая; 7 – уличная; 8 – транспортная, в т.ч. 9 – ДТП; 10 – школьная; 11 – спортивная; 12 – прочие; 13 – полученная в результате террористических действий
</td>
 </tr>
</table>

<table style='border-collapse:collapse;margin-bottom: 3px;'>
 <tr>
  <td width=160>23.Диагноз код</td>
  <td class="block"></td>
  <td class="block"></td>
  <td class="block"></td>
  <td class="block"></td>
  <td class="block"></td>
 </tr>
</table>

<table style='border-collapse:collapse;margin-bottom: 3px;'>
 <tr>
  <td width=300 colspan="8">24.Код мед. услуги (посещения, СМП, КЭС) <sup>3)</sup>:</td>
  <td class="block"></td>
  <td class="block"></td>
  <td class="block"></td>
  <td class="block"></td>
  <td class="block"></td>
  <td class="block"></td>
  <td class="block"></td>
  <td class="block"></td>
  <td class="block"></td>
  <td class="block"></td>
  <td class="block"></td>
  <td class="block"></td>
  <td class="block"></td>
  <td class="block"></td>
 </tr>
 <tr>
  <td></td>
  <td class="block"></td>
  <td class="block"></td>
  <td class="block"></td>
  <td class="block"></td>
  <td class="block"></td>
  <td class="block"></td>
  <td class="block"></td>
  <td class="block"></td>
  <td class="block"></td>
  <td class="block"></td>
  <td class="block"></td>
  <td class="block"></td>
  <td class="block"></td>
  <td class="block"></td>  
  <td class="block"></td>
  <td class="block"></td>
  <td class="block"></td>
  <td class="block"></td>
  <td class="block"></td>
  <td class="block"></td>
  <td class="block"></td>
 </tr>
</table>

<table style='border-collapse:collapse;margin-bottom: 3px; width: 100%;'>
 <tr>
  <td>25. Характер заболевания: 1-острое (+), впервые в жизни установленное хроническое (+); 2 – диагноз установлен в предыдущ. году или ранее</td>
 </tr>
</table>

<table style='border-collapse:collapse;margin-bottom: 3px; width: 100%;'>
 <tr>
  <td>26.Диспансерный учет:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;1-состоит,&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;2 – взят,&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;3 – снят,&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;в.т. 4 – по выздоровлению</td>
 </tr>
</table>

<table style='border-collapse:collapse;margin-bottom: 3px;'>
 <tr>
  <td colspan=8>27.Заполняется только при изменении диагноза: ранее зарегистрированный диагноз</td>
</tr>
<tr>
  <td></td>
  <td width=70 colspan=2>Код МКБ-10</td>
  <td class="block"></td>
  <td class="block"></td>
  <td class="block"></td>
  <td class="block"></td>
  <td class="block"></td>
</tr>
<tr>
  <td>Дата регистрации изменяемого диагноза:</td>
  <td></td>
  <td class="block"></td>
  <td class="block"></td>
  <td class="block"></td>
  <td class="block"></td>
  <td class="block"></td>
  <td class="block"></td>
</tr>
</table>

<table style='border-collapse:collapse;margin-bottom: 3px; width: 100%;'>
 <tr>
  <td>28.Документ временной нетрудоспособности: 1 – открыт; 2 – закрыт</td>
 </tr>
</table>

<table style='border-collapse:collapse;margin-bottom: 3px;'>
 <tr>
  <td width=120 valign="top">29.Причина выдачи:</td>
  <td rowspan=2  style='padding-right: 1em;'>1 – заболевание; 2 – по уходу; 3 – карантин; 4 – прерывание беременности;<br>
  5 – отпуск по беременности и родам; 6 – санаторно-курортное лечение,<br>
  29.1 по уходу: пол 1 – муж; 2 – жен.&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(возраст лица, получившего документ в/н)
  <td></td>
</td>
</tr>
<tr>
  <td></td>
  <td class="block"></td>
  <td class="block"></td>
</tr>
</table>

<table style='border-collapse:collapse;margin-bottom: 3px;'>
<tr>
  <td width=280 valign="top">30.Рецептурный бланк серия и №, дата выписки:</td>
  <td>30.1 ___________________________; 30.2 ___________________________;<br>
  30.3 ___________________________; 30.4 ___________________________;</td>
</tr>
</table>

</body>

</html>