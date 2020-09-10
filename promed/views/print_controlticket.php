<html>
<head>
<meta http-equiv=Content-Type content="text/html; charset=<?php echo (defined('USE_UTF') && USE_UTF === true ? "utf-8" : 'windows-1251'); ?>">
<title>Печать контрольного талона</title>

<style>
	body{
		font-family: Tahoma, Geneva, sans-serif;	
		margin:0; padding:0;
		font-size:12px;
	}
	table{
		font-size: 12px;
	}
	.wrapper {overflow: hidden; border: 3px double #000;}
	.wrapper p {margin:0; padding:0;}
	.wrapper .wrapper {border-color: #000; border-style: none none solid; border-width: medium medium 1px;}
	.wrapper .wrapper span {display: inline-block; margin: 0; overflow: hidden; white-space: nowrap;}
	.width70 {border-right: 1px solid #000; float: left; margin: 0 -1px 0 0; width: 70%;}
	.width30 {border-left: 1px solid #000; float: left; left: 1px; margin: 0 0 0 -1px; position: relative; width: 30%;}
	.width70 span.type1 {width: 50%;}
	.width70 span.type2 {width: 33%;}
	.width70 span.type3 {width: 16%;}
	.width70 span.type4 {width: 40%;}
	.width70 span.type5 {width: 80%;}

	.width30 span.type1 {width: 30%;}
	.width30 span.type2 {overflow: hidden; white-space: nowrap; width: 70%;}
	.width30 span.type3 {width:50%}
	.width30 span.type4 {}
	
	.width100 {display:block}
	.width100 span.type1 {width:33%}
	.width100 span.type2 {}
	.width100 span.type3 {}
	.width100 span.type4 {}
	.width100 span label {display: inline-block; width: 23%;}
	
	.border {border-bottom:1px solid black}
	.wrapper .wrapper:last-child {border: medium none;}
	
	.timer .width30 {border-left:none; border-right:1px solid #000; width:20%}
	.timer .width70 {width:80%; border-right:none}
	.timer .width30 span label {display: inline-block; width: 23%;}
	.timer .width30 span {float: left; width: 50%;}
	.wrapper.drugs {border-bottom:none}
	.drugs span {width: 10%;}
	.drugs label {width:70%}
	.drugs em, .drugs strong {width: 10%; font-weight:normal; font-style:normal;}
	.drugs em, .drugs strong, .drugs span, .drugs label {float:Left}
	.drugs p {clear:both}
	.wrapper.drugs p:first-child * {font-weight:bold}
	
	
</style>

</head>

<body lang=RU link=blue vlink=purple style='tab-interval:36.0pt;text-justify-trim:
punctuation'>
<script type="text/javascript">
	window.onload = function()
	{
		window.print();
		window.onfocus=function(){
			
		}
	}
	
	window.onafterprint = function() {
		if (confirm("Закрыть окно печати?")) {
				window.close();
			}
	};
	
</script>

<div class="wrapper">
	<div class="wrapper">
		<div class="adres width70">
			<p><span class="type1">Район {KLRgn_Name}</span><span class="type3">Квартира {CmpCallCard_Kvar}</span><span class="type3">Подъезд {CmpCallCard_Podz}</span><span class="type3">Этаж {CmpCallCard_Etaj}</span></p>
			<p><span class="type1">Пункт {KLCity_Name}</span><span class="type2">Код {CmpCallCard_Kodp}</span></p>
			<p><span class="type1">Улица {streetName}</span><span class="type2">Тлф {CmpCallCard_Telf}</span></p>
			<p>
				<span class="type1">Дом <?php
					if ( !empty($secondStreetName)) { ?>
						{secondStreetName}
					<?php } else{ ?>
						{CmpCallCard_Dom}
					<?php } ?>
				</span>
				<span class="type2">Где {CmpCallPlaceType_Code} {CmpCallPlaceType_Name}</span></p>
			<p></p>
			<p>Повод {CmpReason_Code} {CmpReason_Name}</p>
			<p><span class="type2">Фам {Person_SurName}</span><span class="type2">Имя {Person_FirName}</span><span class="type3">возраст {Person_Age}</span><span class="type3">Пол {Sex_Name}</span></p>
			<p><span class="type4">Отчество {Person_SecName}</span>Вызвал {CmpCallCard_Ktov}</p>
            <p>Дополнительная информация {CmpCallCard_Comm}</p>
        </div>
		<div class="right width30">
			<div class="border">
				<p>Номер {CmpCallCard_Numv} ({CmpCallCard_Ngod})</p>
				<p>Тип вызова {CallType}</p>
				<p><span class="type1">Сроч. {CmpCallCard_Urgency}</span><span class="type2">Прф. {EmergencyTeamSpecInfo}</p>
				<p>{MedService_Nick}</p>
			</div>
			<div>
				<p><span class="type3">Дата {date_Prm}</span><span class="type3">День {dayOfWeek}</span></p>
				<p><span class="type3">Принят {CmpCallCard_ImcomeTime}</span><span class="type3"></span></p>
				<p><span class="type3">Передан {CmpCallCard_OutcomeTime}</span><span class="type3">Исполнен</span></p>
			</div>
		</div>
	</div>
	<div class="wrapper">
		<div class="result width70">
			<p><span class="type1">Результат</span><span class="type2">Вид</span></p>
			<p></p>
			<p><span class="type1">Ds1</span><span class="type3">Алк</span></p>
			<p><span class="type1">Ds2</span><span class="type3">МКБ</span></p>
		</div>
		<div class="right width30">
			<p><span class="type3">Бригада {EmergencyTeam_Num}</span><span class="type3">{EmergencyTeam_Lpu_Nick}</span></p>
			<p><span class="type3">{EmergencyTeam_MedService_Nick}</span><span class="type3">ПРФ {EmergencyTeamSpecInfo}</span></p>
			<p><span class="type3">Машина {EmergencyTeam_CarNum}</span><span class="type3">Рация {RadioEnabled}</span></p>
			<p><span>СБ {HeadShift}</span></p>
			<p><span class="type3">П1 {EmergencyTeam_Assistant1}</span><span class="type3">П2 {EmergencyTeam_Assistant2}</span></p>
		</div>
	</div>
</div>

<!--
<table border="0" cellpadding="3" cellspacing="0" align="center" style="height: 200px; ; width: 900px; border: 4px double; line-height: 10px;">
<tbody>
<tr>
<td>Район</td>
<td></td>
<td colspan="6">Кв {CmpCallCard_Kvar}  Под {CmpCallCard_Podz}  Эт {CmpCallCard_Etaj}</td>
<td class="left-br">Номер</td>
<td>{CmpCallCard_Numv}</td>
<td>({CmpCallCard_Ngod})</td>
<td>&nbsp;</td>
</tr>
<tr>
<td>Пункт</td>
<td></td>
<td>Код Д/Ф</td>
<td>{CmpCallCard_Kodp}</td>
<td colspan="3"></td>
<td></td>
<td  class="left-br">Тип выз</td>
<td colspan="3">{CallType}</td>
</tr>
<tr>
<td>Улица</td>
<td>{streetName}</td>
<td>Тлф</td>
<td>{CmpCallCard_Telf}</td>
<td colspan="3"></td>
<td></td>
<td class="left-br">Сроч</td>
<td>{ReasonRangeValue}</td>
<td>Прф</td>
<td>{EmergencyTeamSpecInfo}</td>
</tr>
<tr>
<td>Дом</td>
<td>{CmpCallCard_Dom}</td>
<td>Где</td>
<td></td>
<td></td>
<td></td>
<td></td>
<td></td>
<td class="left-br bottom-br">&nbsp;</td>
<td class="bottom-br">Пермь ГССМП</td>
<td class="bottom-br">П/с</td>
<td class="bottom-br">{LpuBuilding_Name}&nbsp;</td>
</tr>
<tr>
<td></td>
<td></td>
<td>Дата</td>
<td>{CmpCallCard_prmDT}</td>
<td colspan="4"></td>
<td class="left-br">Дата</td>
<td>{date_Prm}</td>
<td>день</td>
<td>{dayOfWeek}</td>
</tr>
<tr>
<td>Повод</td>
<td>{CmpReason_Code}</td>
<td colspan="6">{CmpReason_Name}</td>
<td class="left-br">Принят</td>
<td>{CmpCallCard_ImcomeTime}</td>
<td></td>
<td></td>
</tr>
<tr>
<td>Фам</td>
<td>{Person_SurName}</td>
<td>Имя</td>
<td>{Person_FirName}</td>
<td>Возр</td>
<td>{Person_Age}</td>
<td>Пол</td>
<td>{Sex_Name}</td>
<td class="left-br">Передан</td>
<td>{CmpCallCard_OutcomeTime}</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
</tr>
<tr>
<td class="bottom-br">Отчество</td>
<td class="bottom-br">{Person_SecName}</td>
<td class="bottom-br">Вызвал </td>
<td class="bottom-br">Бригада {EmergencyTeam_Num}</td>
<td class="bottom-br">&nbsp;</td>
<td class="bottom-br">&nbsp;</td>
<td class="bottom-br">&nbsp;</td>
<td class="bottom-br">&nbsp;</td>
<td class="bottom-br left-br">&nbsp;</td>
<td class="bottom-br">&nbsp;</td>
<td class="bottom-br">&nbsp;</td>
<td class="bottom-br">&nbsp;</td>
</tr>
<tr>
<td></td>
<td></td>
<td></td>
<td></td>
<td></td>
<td></td>
<td></td>
<td></td>
<td></td>
<td></td>
<td></td>
<td></td>
</tr>
<tr>
<td>Рез-т</td>
<td></td>
<td></td>
<td></td>
<td>Вид</td>
<td></td>
<td></td>
<td></td>
<td>Бригада</td>
<td>{EmergencyTeam_Num}</td>
<td></td>
<td></td>
</tr>
<tr>
<td>район</td>
<td></td>
<td>Куда</td>
<td></td>
<td></td>
<td></td>
<td></td>
<td></td>
<td>п/с {EmergencyTeam_BaseStationNum}</td>
<td></td>
<td>прф</td>
<td>{EmergencyTeamSpecInfo}</td>
</tr>
<tr>
<td></td>
<td></td>
<td></td>
<td></td>
<td></td>
<td></td>
<td></td>
<td></td>
<td>Машина</td>
<td>{EmergencyTeam_CarNum}</td>
<td>Рация</td>
<td>{RadioEnabled}</td>
</tr>
<tr>
<td>Ds</td>
<td></td>
<td></td>
<td></td>
<td>Алк</td>
<td></td>
<td></td>
<td></td>
<td>СБ</td>
<td colspan="3">{HeadShift}</td>
</tr>
<tr>
<td>Ds</td>
<td></td>
<td></td>
<td></td>
<td>Мкб</td>
<td></td>
<td></td>
<td></td>
<td>П1</td>
<td>{EmergencyTeam_Assistant1}</td>
<td>П2</td>
<td>{EmergencyTeam_Assistant2}</td>
</tr>
</tbody>
</table>

-->

</body>
</html>