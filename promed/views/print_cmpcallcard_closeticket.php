<html>
<head>
<meta http-equiv=Content-Type content="text/html; charset=<?php echo (defined('USE_UTF') && USE_UTF === true ? "utf-8" : 'windows-1251'); ?>">
<link rel="stylesheet" type="text/css" href="/css/print_cmpcallcard_closeticket.css" media="screen" />
<title>Печать талона закрытого вызова</title>
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

<body lang=RU link=blue vlink=purple style='tab-interval:36.0pt;text-justify-trim:punctuation'>
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
			<p><span class="type1">Пункт {CmpCallCard_PunktViezd}</span><span class="type2">Код {CmpCallCard_Kodp}</span></p>
			<p><span class="type1">Улица {CmpCallCard_Street}</span><span class="type2">Тлф {CmpCallCard_Telf}</span></p>
			<p><span class="type1">Дом {CmpCallCard_Dom}</span><span class="type2">Где {CmpCallPlaceType_Code} {CmpCallPlaceType_Name}</span></p>
			<p>Доп.инфо {CmpCallCard_Comm} </p>
			<p>Повод {CmpReason_Code} {CmpReason_Name}</p>
			<p><span class="type2">Фам {Person_Surname}</span><span class="type2">Имя {Person_Firname}</span><span class="type3">Возраст {Person_Age}</span><span class="type3">Пол {Sex_Code}</span></p>
			<p><span class="type4">Отчество {Person_Secname}</span><span>Вызвал {CmpCallCard_Ktov}</span></p>
		</div>
		<div class="right width30">
			<div class="border">
				<p>Номер {CmpCallCard_Numv} ({CmpCallCard_Ngod})</p>
				<p>Тип вызова {CmpCallType_Name}</p>
				<p><span class="type1">Сроч. {CmpCallCard_Urgency}</span><span class="type2">Прф. {CmpProfile_Code} {CmpProfile_Name}</p>
				<p>{MedService_Nick}</p>
			</div>
			<div>
				<p><span class="type3">Дата {CmpCallCard_prmDate}</span><span class="type3">День {CmpCallCard_Weekday}</span></p>
				<p><span class="type3">Принят {CmpCallCard_prmTime}</span><span class="type3"></span></p>
				<p><span class="type3">Передан {CmpCallCard_Tper}</span><span class="type3">Исполнен {CmpCallCard_Tisp}</span></p>
			</div>
		</div>
	</div>
	<div class="wrapper">
		<div class="result width70">
			<p><span class="type1">Результат {CmpCallReasonType_Code} {CmpCallReasonType_Name}</span><span class="type1">Вид {CmpDiseaseAndAccidentType_Code} {CmpDiseaseAndAccidentType_Name}</span></p>
			<p><span>Куда {LpuTransmit_Nick}</span></p>
			<p><span class="type5">Ds1 {CmpDiagFirst_Code}.{CmpDiagFirst_Name}</span><span class="type3">Алк {CmpDiagSecond_isAlco}</span></p>
			<p><span class="type5">Ds2 {CmpDiagSecond_Code}.{CmpDiagSecond_Name}</span><span class="type3">МКБ {CmpDiagFirst_Code}</span></p>
		</div>
		<div class="right width30">
			<p><span class="type3">Бригада {EmergencyTeam_Num}</span><span class="type3">{EmergencyTeam_Lpu_Nick}</span></p>
			<p><span class="type3">ПС {EmergencyTeam_MedService_Nick}</span><span class="type3">ПРФ {EmergencyTeamSpec_Code} {EmergencyTeamSpec_Name}</span></p>
			<p><span class="type3">Машина {EmergencyTeam_CarNum}</span><span class="type3">Рация {EmergencyTeam_PortRadioNum}</span></p>
			<p><span>СБ {EmergencyTeam_HeadShift_Code} {EmergencyTeam_HeadShift_FIO}</span></p>
			<p><span class="type3">П1 {EmergencyTeam_Assistant1_Code}</span><span class="type3">П2 {EmergencyTeam_Assistant2_Code}</span></p>
		</div>
	</div>
</div>
<div class="wrapper">
	<div class="wrapper">
		<div class="width100">
			<p><span class="type1">Ст. врач</span><span class="type1">Ст. дисп</span><span class="type1">Получен: </span>{CmpCallCard_Kakp}</p>
			<p><span class="type1"><label>Принял {DispatchCall_MedPersonal_Code}</label>{DispatchCall_MedPersonal_FIO}</span><span class="type1"><label>Назначил {DispatchDirect_MedPersonal_Code}</label>{DispatchDirect_MedPersonal_FIO}</span><span class="type1">/1 - на руки/</span></p>
			<p><span class="type1"><label>Передал {DispatchDirect_MedPersonal_Code}</label>{DispatchDirect_MedPersonal_FIO}</span><span class="type1"><label>Закрыл {DispatchStation_MedPersonal_Code}</label>{DispatchStation_MedPersonal_FIO}</span><span class="type1">/0 - по рации/интернет/</span></p>
		</div>
	</div>
	<div class="timer wrapper">
		<div class="width30">
			<p><span>Перед</span>{CmpCallCard_Tper}</p>
			<p><span>Выезд</span>{CmpCallCard_Vyez}</p>
			<p><span>Прибыт</span>{CmpCallCard_Przd}</p>
			<p><span>Госпит</span>{CmpCallCard_Tgsp}</p>
			<p><span>В стац</span>{CmpCallCard_Tsta}</p>
			<p><span>Исполн</span>{CmpCallCard_Tisp}</p>
			<p><span>Возвр</span>{CmpCallCard_Tvzv}</p>
			<p><span>Километр</span>{CmpCallCard_Kilo}</p>
		</div>
		<div class="width70">
			<div class="wrapper">
				<p><span class="type1">Прожив. р-н {PersonAddress_RgnName}</span><span class="type1">Пункт {PersonAddress_Punkt}</span></p>
				<p><span class="type1">Ул {PersonAddress_StreetName}</span><span class="type3">Дом {PersonAddress_House}</span><span class="type3">Кв {PersonAddress_Kvar}</span></p>
				<p><span class="type1">Персон.код</span><span class="type1">Код2</span></p>
				<p><span class="type1">Пол-ка {Person_LpuAttach_Nick}</span><span class="type1">Полис (ед.номер) {Person_EdNum}</span></p>
			</div>
		</div>
	</div>
</div>
<div class="wrapper drugs">
	<p><span>Код</span><label>Наименование</label><em>Ед.изм</em><strong>Кол-во</strong></p>
	
	<?php 

	foreach ($Drugs as $key => $value) {
		?>
		
		<p><span> <?echo $value['Drug_Code']?></span><label><?echo $value['DrugTorg_Name']?></label><em><?echo $value['DrugForm_Name']?></em><strong><?echo $value['EmergencyTeamDrugPackMove_Quantity']?></strong></p>
		
		<?php
	}

?>
</div>

</body>
</html>