<html>
<head>
    <title>Печать списка протоколов ВК</title>
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
<!-- /*NO PARSE JSON*/ -->


<table style="width: 100%; border: none; border-collapse: collapse;" cellspacing="0" cellpadding="2"><tbody>

<tr style="background-color: #eee; font-weight: bold; text-align: center;">
    <td ROWSPAN=2 style="width: 2%;">№ п/п</td>
    <td COLSPAN=3>Выявлено при экспертизе</td>
    <td ROWSPAN=2 style="width: 10%;">Обоснование заключения. Заключение экспертов, рекомендации</td>
    <td ROWSPAN=2 style="width: 8%;">Дата направления в бюро МСЭ или другие (специализированные) учреждения</td>
    <td ROWSPAN=2 style="width: 10%;">Заключение МСЭ или других (специализированных) учреждений</td>
    <td ROWSPAN=2 style="width: 8%;">Дата получения заключения МСЭ или других учреждений, срок их действий</td>
    <td ROWSPAN=2 style="width: 10%;">Дополнительная информация по заключению других (специализированных) учреждений. Примечания.</td>
    <td ROWSPAN=2 style="width: 20%;">Основной состав экспертов</td>
    <td ROWSPAN=2 style="width: 4%;">Подписи экспертов</td>
</tr>
<tr style="background-color: #eee; font-weight: bold; text-align: center;">
    <td style="width: 10%;">Отклонение от стандартов</td>
    <td style="width: 10%;">Дефекты, нарушения, ошибки и др.</td>
    <td style="width: 10%;">Достижение результата этапа или исхода профилактического мероприятия</td>
</tr>
<tr style="background-color: #eee; font-weight: bold; text-align: center;">
    <td>&nbsp;</td>
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
</tr>

{search_results}
<tr>
    <td style="text-align: center;">{num}</td> <!-- № п/п -->
    <td style="text-align: center;"> {EvnVK_isAberration}<br>{EvnVK_AberrationDescr}</td> <!-- Отклонение от стандартов -->
    <td style="text-align: center;"> {EvnVK_isErrors}<br>{EvnVK_ErrorsDescr}</td> <!-- Дефекты, нарушения, ошибки и др. -->
    <td style="text-align: center;"> {EvnVK_isResult}<br>{EvnVK_ResultDescr}</td> <!-- Достижение результата этапа или исхода профилактического мероприятия -->
    <td style="text-align: center;"> {EvnVK_ExpertDescr} </td> <!-- Обоснование заключения. Заключение экспертов, рекомендации-->
    <td style="text-align: center;"> {EvnVK_DirectionDate} </td> <!-- Дата направления в бюро МСЭ или другие (специализированные) учреждения -->
    <td style="text-align: center;"> {EvnVK_ConclusionDescr}</td> <!-- Заключение МСЭ или других (специализированных0 учреждений -->
    <td style="text-align: center;"> {EvnVK_ConclusionDate}</td> <!-- Дата получения заключения МСЭ или других учреждений, срок их действий -->
    <td style="text-align: center;"> {EvnVK_AddInfo}</td> <!-- Дополнительная информация по заключению других (специализированных0 учреждений. Примечания. -->
    <td style="text-align: center;"> {MF_Person_FIO}</td> <!-- Основной состав экспертов -->
    <td style="text-align: center;"> </td> <!-- Подписи экспертов -->
</tr>
{/search_results}
</tbody></table>

</body>

</html>