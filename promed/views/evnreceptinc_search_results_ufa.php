<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Печать списка рецептов</title>
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

<table style="width: 3000px; border: none; border-collapse: collapse;" cellspacing="0" cellpadding="2"><tbody>
<tr style="background-color: #eee; font-weight: bold; text-align: center;">
	<td style="width: 30px">№ п/п</td>
	<td  style="width: 120px">ФИО пациента</td>
	<td  style="width: 80px">Дата рождения</td>
	<td  style="width: 20px">Пол</td>
	<td style="width: 80px">СНИЛС</td>
	<td  style="width: 200px">Адрес проживания</td>
	<td  style="width: 30px">Серия</td>
	<td style="width: 70px" >Номер</td>
	<td  style="width: 120px">Статья расхода</td>
	<td  style="width: 120px">Врач</td>
	<td  style="width: 50px">Код МКБ-10</td>
	<td  style="width: 200px">Наименование МКБ-10</td>
	<td  style="width: 30px"></td>
	<td  style="width: 200px">Медикамент: выписано</td>
	<td  style="width: 80px">кол-во уп., выписанных МО</td>
	<td  style="width: 100px">Код препарата, отпущенного АУ</td>
	<td  style="width: 200px">Медикамент: выдано</td>
	<td  style="width: 80px">кол-во уп., отпущенных АУ</td> 
	<td  style="width: 80px">сумма уп., отпущенных АУ</td>
	<td  style="width: 120px">АУ, отпустившее по рецепту</td>
	<td  style="width: 80px">Дата выписки</td>
	<td  style="width: 80px">Дата обращения</td>
	<td  style="width: 80px">Дата отоваривания</td>
	<td  style="width: 50px">Время обращения</td>
	<td  style="width: 50px">Время отоваривания</td>
	<td  style="width: 50px">Время отсрочки</td>
	<td  style="width: 80px">Статус рецепта</td>


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
	<td>9</td>
	<td>10</td>
	<td>11</td>
	<td>12</td>
	<td>13</td>
	<td>14</td>
	<td>15</td>
	<td>16</td>
	<td>17</td>
	<td>18</td>
	<td>19</td>
	<td>20</td>
	<td>21</td>
	<td>22</td>
	<td>23</td>
	<td>24</td>
	<td>25</td>
	<td>26</td>
	<td>27</td>

</tr>

{search_results}
<tr>
<td style="text-align: right;">{Record_Num}</td>
<td>{Person_FIO}</td>
<td style="text-align: center;">{Person_Birthday}</td>
<td style="text-align: center;">{Sex}</td>
<td style="text-align: center;">{Person_Snils}</td>
<td style="text-align: center;">{Address_Address}</td>
<td style="text-align: center;">{EvnRecept_Ser}</td>
<td style="text-align: center;">{EvnRecept_Num}</td>
<td style="text-align: center;">{WhsDocumentCostItemType_Name}</td>
<td style="text-align: center;">{MedPersonal_Fio}</td> 
<td style="text-align: center;">{Diag_Code}</td>
<td style="text-align: center;">{Diag_Name}</td>
<td style="text-align: center;">{EvnRecept_IsMnn}</td>
<td style="text-align: center;">{DrugMnn_Name}</td>
<td style="text-align: center;">{EvnRecept_firKolvo}</td>
<td style="text-align: center;">{Drug_Code}</td>
<td style="text-align: center;">{Drug_Name}</td>
<td style="text-align: center;">{EvnRecept_Kolvo}</td>
<td style="text-align: center;">{EvnRecept_Sum}</td>
<td style="text-align: center;">{OrgFarmacy_Name}</td>
<td style="text-align: center;">{EvnRecept_setDate}</td>
<td style="text-align: center;">{EvnRecept_obrDate}</td>
<td style="text-align: center;">{EvnRecept_otpDate}</td>
<td style="text-align: right;">{EvnRecept_obrDay}</td>
<td style="text-align: right;">{EvnRecept_otovDay}</td>
<td style="text-align: right;">{EvnRecept_otsDay}</td>
<td style="text-align: center;">{ReceptDelayType_Name}</td>

</tr>
{/search_results}

</tbody></table>

</body>

</html>