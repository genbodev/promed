<html>

<head>
<meta http-equiv=Content-Type content="text/html; charset="utf-8">
<meta name=ProgId content=Word.Document>
<meta name=Generator content="Microsoft Word 11">
<meta name=Originator content="Microsoft Word 11">

<style type="text/css">
	@page port { size: portrait }
	@page land { size: landscape }
	body { padding: 0px; }
	table {font-size: 15px; border-collapse: collapse; }
	table td {vertical-align: top;}
	.tit {font-size:10pt; text-align:center}
	.tit table {width: 140px; margin: 0 auto; line-height: 0.8}
	.border {border: solid black 1px;}
	.tdborder td {border: solid black 1px; font-size: 14px;}
	.tdborder th {border: none;}
	.under {font-size: 7pt; text-align: center; line-height: 0.8}
</style>



<style type="text/css">
	div.selector { display:none; }
	div.show_selector { display:none; }
	div.single_selector { display:inline; }
	div.cutline { border: 1px black none; border-bottom-style:dashed; border-weight:1px; text-align:center; font-size:0.8em; }
	.broken_text { display: inline; padding: 0px; margin: 0px; }
</style>

<script type="text/javascript">
	function activateSelectors() {
		var arr = document.getElementsByTagName("div");
		for(var i = 0; i < arr.length; i++) {
			if (arr[i].className == "selector") {
				var span_arr = arr[i].parentNode.getElementsByTagName("span");
				for(var j = 0; j < span_arr.length; j++) {
					if (span_arr[j].className == "val_" + arr[i].innerHTML) span_arr[j].style.textDecoration = "underline";
				}
			}
			if (arr[i].className == "show_selector") {
				var span_arr = arr[i].parentNode.getElementsByTagName("span");
				for(var j = 0; j < span_arr.length; j++) {
					if (span_arr[j].className == "val_" + arr[i].innerHTML)
						span_arr[j].style.display = "inline";
					else
						span_arr[j].style.display = "none";
				}
			}
			if (arr[i].className == "single_selector") {
				var span_arr = arr[i].getElementsByTagName("span");
				var empty = true;
				for(var j = 0; j < span_arr.length; j++) {
					if (!empty)
						span_arr[j].style.display = 'none';
					if(span_arr[j].innerHTML.replace('&nbsp;','') != '')
						empty = false;
				}
			}
			if (arr[i].id.substring(0,18) == 'broken_text_start_') {
				var obj_arr = arr[i].id.split('_');
				var start_obj = arr[i];
				var end_obj = document.getElementById('broken_text_end_'+obj_arr[3]);
				var max_len = obj_arr[4];
				var words = start_obj.innerHTML.split(' ');
				var cont = false;
				start_obj.innerHTML = '';
				end_obj.innerHTML = '';

				for (var j = 0; j < words.length; j++) {
					if (start_obj.innerHTML.length + words[j].length + 1 <= max_len && !cont)
						start_obj.innerHTML += words[j] + ' ';
					else
						cont = true;
					if (cont)
						end_obj.innerHTML += words[j] + ' ';
				}
			}
		}
	}
</script>
</head>

<body>


<!--<table width="100%">
	<tr><th width="60%"></th><th width="40%"></th></tr>
	<tr><td>&nbsp;</td><td class="border">
			<table width="100%">
				<tr>
					<th width="45%"></th>
					<th width="30%"></th>
					<th width="15%"></th>
				</tr>
				<tr>
					<td colspan="3">КҰЖЖ бойынша ұйым коды</td>
				</tr>
				<tr>
					<td>Код организации по ОКПО</td>
					<td style="border-bottom: solid black 1px;">{Org_OKPO}</td>
					<td>&nbsp;</td>
				</tr>
			</table>
		</td></tr>
</table>
-->
<table width="100%" class="tdborder">
	<tr><th width="35%"></th><th width="25%"></th><th width="40%"></th></tr>
	<tr>
		<td>
			Қазақстан Республикасы
			<br/>Денсаулық сақтау министрлігі
			<br/>Министерство здравоохранения
			<br/>Республики Казахстан
		</td>
		<td rowspan="2"></td>
		<td>
			Қазақстан Республикасы
			<br/>Денсаулық сақтау министрінің м.а. 2010 жылғы
			<br/>«23» қарашадағы № 907 бұйрығымен бекітілген
			<br/>№ 025/е нысанды
			<br/>медициналық құжаттама
		</td>
	</tr>
	<tr>
		<td>
			Ұйымның атауы
			<br/>Наименование организации
			<br/>{Lpu_Name}
		</td>
		<td>
			Медицинская документация
			<br/>Форма 025/у
			<br/>Утверждена приказом Министра здравоохранения
			<br/>Республики Казахстан «23» ноября
			<br/>2010 года № 907
		</td>
	</tr>
</table>

<p class="tit" style="margin: 2px 0; font-size: 25px;">
	АМБУЛАТОРЛЫҚ ПАЦИЕНТТІҢ МЕДИЦИНАЛЫҚ КАРТАСЫ
	<br/>МЕДИЦИНСКАЯ КАРТА АМБУЛАТОРНОГО ПАЦИЕНТА
<table>
	<tr><td>№</td><td width="120" style="border-bottom: solid black 1px;"><b>{PersonCard_Code}</b></td></tr>
	<tr><td></td><td class="under">немесе коды (или код)</td></tr>
</table>
</p>

<table width="100%">
	<tr><th width="35%"></th><th width="65%"></th></tr>
	<tr style="border-top: solid black 1px;">
		<td>Тегі, аты, әкесінің аты <b>(Фамилия, имя, отчество)</b></td>
		<td style="border-bottom: solid black 1px;"><b>{Person_SurName} {Person_FirName} {Person_SecName}</b></td>
	</tr>
	<tr>
		<td colspan="2">
			Жынысы: <span class="val_01">ер</span>, <span class="val_02">әйел</span> (астын сызыңыз)<div class="selector">0{Sex_Code}</div>
			(Пол: <span class="val_01">мужской</span>, <span class="val_02">женский</span> (подчеркнуть))<div class="selector">0{Sex_Code}</div>
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            Национальность <b>{Ethnos_Name}</b>
        </td>
	</tr>
</table>

<table width="100%">
	<tr><th width="20%"></th><th width="37%"></th><th width="8%"></th><th width="35%"></th></tr>
	<tr>
		<td>Туған күні (Дата рождения)</td>
		<td style="border-bottom: solid black 1px;"><b>{Person_BirthDay}</b></td>
		<td><b>Телефон</b></td>
		<td style="border-bottom: solid black 1px;"><b>{Person_Phone}</b></td>
	</tr>
	<tr>
		<td></td>
		<td class="under">күні, айы, жылы (день, месяц, год)</td>
		<td></td>
		<td class="under">үйінің, қызмет тел.(домашний, служебный)</td>
	</tr>
</table>

<table width="100%">
	<tr><th width="40%"></th><th width="17%"></th><th width="23%"></th><th width="20%"></th></tr>
	<tr>
		<td>Науқастың мекенжайы (Адрес больного): облыс (область)</td>
		<td style="border-bottom: solid black 1px;"><b>{Region}</b></td>
		<td>елді мекен (населенный пункт)</td>
		<td style="border-bottom: solid black 1px;"><b>{City}</b></td>
	</tr>
</table>

<table width="100%">
	<tr><th width="13%"></th><th width="30%"></th><th width="25%"></th><th width="32%"></th></tr>
	<tr>
		<td>Ауданы <b>(район)</b></td>
		<td style="border-bottom: solid black 1px;"><b>{TerrDop}</b></td>
		<td>көшесі (орамы) <b>(улица)</b></td>
		<td style="border-bottom: solid black 1px;"><b>{Street}</b></td>
	</tr>
</table>

<table width="100%">
	<tr>
		<th width="13%"></th>
		<th width="17%"></th>
		<th width="8%"></th>
		<th width="22%"></th>
		<th width="16%"></th>
		<th width="24%"></th>
	</tr>
	<tr>
		<td>үй № (дом №)</td>
		<td style="border-bottom: solid black 1px;"><b>{House}</b></td>
		<td>корпус</td>
		<td style="border-bottom: solid black 1px;"><b>{Corpus}</b></td>
		<td>пәтер (квартира) №</td>
		<td style="border-bottom: solid black 1px;"><b>{Flat}</b></td>
	</tr>
</table>

<table width="100%">
	<tr><th width="33%"></th><th width="40%"></th><th width="18%"></th><th width="9%"></th></tr>
	<tr>
		<td>Қызмет, жұмыс орны (Место службы, работы)</td>
		<td style="border-bottom: solid black 1px;"><b>{Job_Name}</b></td>
		<td>бөлімше (отделение), цех</td>
		<td style="border-bottom: solid black 1px;">{OrgUnion_Name}</td>
	</tr>
	<tr>
		<td></td>
		<td class="under">(өндірістің атауы мен сипаттамасы) (наименование и характер производства)</td>
		<td></td>
		<td></td>
	</tr>
</table>

<table width="100%">
	<tr><th width="30%"></th><th width="70%"></th></tr>
	<tr>
		<td>Кәсібі, лауазымы (Профессия, должность)</td>
		<td style="border-bottom: solid black 1px;">{Post_Name}</td>
	</tr>
</table>

<table width="100%">
    <tr>
        <td width="50%">
            Жеке куәлiк (удостоверение личности) <b>№{Document_Num}</b>
        </td>
        <td width="50%">
            <b>ИИН {Person_Inn}</b>
        </td>

    </tr>
</table>

<table width="100%">
    <tr>
        <td>«Емхананы  таңдаумен келісемін» <b>{Lpu_Name}</b></td>
    </tr>
</table>

<table width="100%">
	<tr>
		<th width="3%"></th>
		<th width="12%"></th>
		<th width="12%"></th>
		<th width="24%"></th>
		<th width="12%"></th>
		<th width="12%"></th>
		<th width="15%"></th>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td colspan="2">&nbsp;</td>
		<td>&nbsp;</td>
		<td colspan="2">«С выбором поликлиники согласен»</td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td>Пациенттің қолы</td>
		<td style="border-bottom: solid black 1px;">&nbsp;</td>
		<td>&nbsp;</td>
		<td>Подпись пациента</td>
		<td style="border-bottom: solid black 1px;">&nbsp;</td>
		<td>&nbsp;</td>
	</tr>
</table>

<script type="text/javascript">activateSelectors();</script>
</body>
</html>