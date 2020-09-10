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

	.wrapper {overflow: hidden; border: 3px double #000; padding: 10px;}
	.wrapper p {margin:0; padding:0; line-height: 16px;}
	.wrapper .wrapper {border-color: #000; border-style: none none solid; border-width: medium medium 1px;}
	.wrapper .wrapper span {display: block; float: left; margin: 0; overflow: hidden; white-space: nowrap;}
	
	.width100 {display:block; }
	.width100 span.type1 {width:30%}
	.width100 span.type3 {width:35%}
    .width100 span.type2 {width:70%}
	.width100 span.type4 {width:10%}
	.width100 span.type5 {width:50%}
	.width100 span label {display: inline-block; width: 23%;}

    .width100 span.label {min-width: 130px; /*font-weight: bold;*/}
	
	.border {border-bottom:1px solid black}
	.wrapper .wrapper:last-child {border: medium none;}

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



<div class="wrapper" style="margin-bottom: 20px;">
    <p style="text-align: center;margin: 10px;">
        {Lpu_name}
    </p>
    <p style="text-align: center;">ТАЛОН К ВЫЗОВУ № {CmpCallCard_Numv} / {CmpCallCard_Ngod}</br>
        {AcceptDate}
    </p>
    <div class="wrapper">
        <div class="width100">
            <p>
                <span class="type2"><span class="label">ФИО</span> {Person_FIO}</span>
                <span class="type1"><span class="label">Возраст</span> {Person_AgeText}
            </p>
            <p>
                <span class="type2"><span class="label">Адрес</span> {Address_Name}</span>
                <span class="type1"><span class="label">Дом</span> {CmpCallCard_Dom}</span>
            </p>
			<p style="margin-left: 130px;">
                <!--<span class="label">&nbsp;</span>-->
                <span class="type1">Квартира {CmpCallCard_Kvar}</span>				
				<span class="type1">Подъезд {CmpCallCard_Podz}</span>
				<span class="type1">Этаж {CmpCallCard_Etaj}</span>
				<span class="type1">Код {CmpCallCard_Kodp}</span>				
                <span class="type1">Корпус {CmpCallCard_Korp}</span>
                <span class="type1">Телефон {CmpCallCard_Telf}</span>
            </p><div style="clear:both;"></div>
			<div style="width: 70%;">
				<div style="weight: 130px; float: left;"><span class="label">Доп. ориен</span></div>
				<div style="margin-left: 132px;">
					{CmpCallCard_Comm}
				</div>
				<div style="clear:both;"></div>
			</div>
            <p>
                <span class="type2"><span class="label">Повод к вызову</span> {CmpReason_Name}</span>
            </p>
            <p>
                <span class="type2"><span class="label">Время приема</span> {CmpCallCard_prmDT}</span>
            </p>
            <p>
                <span class="type2"><span class="label">Время передачи</span> {CmpCallCard_Tper}</span>
            </p>
            <p>
                <span class="type2"><span class="label">Время возвращения</span> {BackTime}</span>
            </p>
            <p>
                <span class="type2"><span class="label">Диспетчер №</span> {FeldsherAcceptName}</span>
                <span class="type1">Смена № __________________</span>
            </p>
            <p>
                <span class="type2"><span class="label">Диагноз</span> {DiagName}</span>
            </p>
            <p>
                <span class="type2"><span class="label">Место госпитализации</span> {LpuHid_Nick}</span>
            </p>
            <p>
                <span class="type2"><span class="label">Результат выезда </span>___________________________</span>
            </p>
            <p>
                <span class="type2"><span class="label">Задержка выезда </span>___________________________</span>
            </p>
            <p>
                <span class="type5"><span class="label">Бортовой номер</span> {EmergencyTeam_CarNum}</span>
                <span class="type5"><span class="label">Бригада</span> {EmergencyTeam_Num}</span>
            </p>
            <p>
                <span class="type5">&nbsp;</span>
                <span class="type5"><span class="label">Подпись диспетчера</span> ________________________</span>
            </p>
            <p>
                <span class="type5">&nbsp;</span>
                <span class="type5"><span class="label">Подпись врача</span> ________________________</span>
            </p>
        </div>
    </div>
</div>



<div class="wrapper">
    <p style="text-align: center;margin: 10px;">
        {Lpu_name}
    </p>
    <p style="text-align: center;">ТАЛОН К ВЫЗОВУ № {CmpCallCard_Numv} / {CmpCallCard_Ngod}</br>
        {AcceptDate}
    </p>
    <div class="wrapper">
        <div class="width100">
            <p>
                <span class="type2"><span class="label">ФИО</span> {Person_FIO}</span>
                <span class="type1"><span class="label">Возраст</span> {Person_AgeText}
            </p>
            <p>
                <span class="type2"><span class="label">Адрес</span> {Address_Name}</span>
                <span class="type1"><span class="label">Дом</span> {CmpCallCard_Dom}</span>
            </p>
            <p style="margin-left: 130px;">
                <span class="type1">Квартира {CmpCallCard_Kvar}</span>				
				<span class="type1">Подъезд {CmpCallCard_Podz}</span>
				<span class="type1">Этаж {CmpCallCard_Etaj}</span>
				<span class="type1">Код {CmpCallCard_Kodp}</span>				
                <span class="type1">Корпус {CmpCallCard_Korp}</span>
                <span class="type1">Телефон {CmpCallCard_Telf}</span>
            </p><div style="clear:both;"></div>
			<div style="width: 70%;">
				<div style="weight: 130px; float: left;"><span class="label">Доп. ориен</span></div>
				<div style="margin-left: 132px;">
					{CmpCallCard_Comm}
				</div>
				<div style="clear:both;"></div>
			</div>
            <p>
                <span class="type2"><span class="label">Повод к вызову</span> {CmpReason_Name}</span>
            </p>
            <p>
                <span class="type2"><span class="label">Время приема</span> {CmpCallCard_prmDT}</span>
            </p>
            <p>
				<span class="type2"><span class="label">Время передачи</span> {CmpCallCard_Tper}</span>
            </p>
            <p>
                <span class="type5"><span class="label">Бригада</span> {EmergencyTeam_Num}</span>
            </p>
        </div>
    </div>
</div>

</body>
</html>