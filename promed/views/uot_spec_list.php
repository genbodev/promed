<html>
<head>
<title>{uot_spec_template_title}</title>
<style type="text/css">
body { margin: 0px; padding: 40px; }
table { border-collapse: collapse; }
span, div, td { font-family: verdana; font-size: 12px; padding: 3px; }
.bottomText { text-align: center; margin-top: -3px; font-family: verdana; font-size: 10pt; }
</style>

<style type="text/css" media="print">
body { margin: 0px; padding: 50px; }
span, div, td { font-family: verdana; font-size: 16px; }
td { vertical-align: bottom; }
</style>
</head>
<body>
<!-- /*NO PARSE JSON*/ -->


<table border="1" width="100%">
<tr>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td colspan="5">Спецификация № {Uot_Num}</td>
</tr>

<tr>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
</tr>

<tr>
<td>АУКЦИОН №</td>
<td>&nbsp;</td>
<td colspan="5"><br /><br />{SubContractText}
 </td>
</tr>

<tr>
<td>№</td>
<td width="20%">мнн</td>
<td>форма выпуска</td>
<td width="20%">тн</td>
<td>Цена</td>
<td>Количество упаковок</td>
<td>Сумма</td>
</tr>

{results}
<tr>
<td>{Spec_Num}</td>
<td>{Spec_Name}</td>
<td>{Spec_Drugform}</td>
<td>{Spec_TNName}</td>
<td>{Spec_PriceMax}</td>
<td>{Spec_Kolvo}</td>
<td>{Spec_Sum}</td>
</tr>
{/results}

<tr>
<td colspan="6"><br />Начальник отдела организации лекарственного обеспечения ___________________________________</td>
<td>&nbsp;</td>
</tr>

</table>



</body>
<html>