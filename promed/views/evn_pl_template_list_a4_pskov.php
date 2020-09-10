<html>
<head>
<title>{EvnPLTemplateTitle}</title>
<style type="text/css">
@page port { size: portrait }
@page land { size: landscape }
body { margin: 0px; padding: 10px; width: 800px }
table { border-collapse: collapse; }
span, div, td { font-family: arial, tahoma, verdana; font-size: 12px; }
th { text-align: center; font-size: 11px; border-collapse: collapse; border: 1px solid black; }
.block {height: 13pt; width: 13pt; border: 1px solid #000}
td {vertical-align: top}
small {font-size: 11px}
</style>

<style type="text/css" media="print">
@page port { size: portrait }
@page land { size: landscape }
body { margin: 0px; padding: 0px; }
span, div, td { font-family: arial, tahoma, verdana; font-size: 12px; }
td { vertical-align: bottom; }
th { text-align: center; font-size: 11px; border-collapse: collapse; border: 1px solid black; }
.cell { border: 1px solid #000; border-collapse: collapse; vertical-align: top; }
.block {height: 13pt; width: 13pt; border: 1px solid #000}
td {vertical-align: top}
small {font-size: 11px}
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

<table style="width: 100%;">
<tr>
	<td style="width: 40%;">
		<div style="text-align:center; margin-bottom: 10px">Министерство здравоохранения и социального<br />развития Российской Федерации
			<b><i><u><br />{Lpu_Name}
			<br />{Lpu_Address}</u></i></b>
		</div>
		Код ОГРН: <b>{Lpu_OGRN}</b><br>
		СНИЛС: <b>{Person_Snils}</b><br>
		№ медицинской карты: <b>{PersonCard_Code}</b>
	</td>
	<td style="text-align: right;">
		Медицинская документация<br />Форма № 025-12/у _______<br />утв. приказом Минздравсоцразвития России<br />от 22.11.2004г. № 255<br>
		<div style="font-size: 14px; margin: 7px"><b>ТАЛОН АМБУЛАТОРНОГО ПАЦИЕНТА</b></div>
		Дата: <b>{EvnPL_setDate}</b>
	</td>
</tr>
</table>
	
<table style="width: 100%;">
<tr><td style="width: 40%;">1. Код категории льгот: </td></tr>
<tr><td style="text-align: left; font-size: 14px;">2. Номер страхового полиса ОМС: <b>{OrgSmo_Name} {Polis_Ser} {Polis_Num}</b></td></tr>
<tr><td style="text-align: left;">3. Место работы: <b>{OrgJob_Name} / {OrgUnion_Name} / {Post_Name}</b></td></tr>
</table>
	

<table style="width: 100%;">
	<tr>
		<td style="border: 1px solid">4. Пациент: Ф.И.О. <b><span style="font-size: 14px;">{Person_Fio}</span></b></td>
	</tr>
	<tr>
		<td style="border: 1px solid; font-size: 14px;">
			5. Пол: <b>{Sex_Name}-{Sex_id}</b> &nbsp;
			6. Дата рождения: <b>{Person_Birthday}</b>
		</td>
	</tr>
	<tr>
		<td style="border: 1px solid">7. Документ, удостоверяющий личность: <b>{DocumentType_Name} {Document_Ser} {Document_Num} {Document_begDate}</b></td>
	</tr>
	<tr>
		<td style="border: 1px solid">
			8. Адрес регистрации по месту жительства: <b>{UAddress_Name}</b><br>
			9. Житель: <b>{KLAreaType_Code} - {KLAreaType_Name}</b>
		</td>
	
	</tr>
	<tr>
		<td style="border: 1px solid">10. Социальный статус: <b>{SocStatus_Name} ({SocStatus_Code})</b></td>
	</tr>
	<tr>
		<td style="border: 1px solid">11. Инвалидность: <b>{PrivilegeType_Name}</b></td>
	</tr>
</table>
	

<table style="width: 100%; margin-top: 3px;">
	<tr>
		<td style="border: 1px solid">12. Специалист: код </td>
		{MedPersonal_TabCode}
		<td style="border: 1px solid">Ф.И.О. <b>{MedPersonal_Fio}</b></td>
	</tr>		
	<tr>
		<td style="border: 1px solid">13. Специалист: код</td>
		{MedPersonal2_TabCode}
		<td style="border: 1px solid">Ф.И.О. <b>{MedPersonal2_Fio}</b></td>
	</tr>	
	<tr>
		<td style="border: 1px solid" colspan="12">14. Вид оплаты: 1 - <span class="val_1">ОМС</span>, 2 - <span class="val_3">бюджет</span>, 3 - <span class="val_5">платные услуги</span>, в т.ч. 4 - <span class="val_2">ДМС</span>, 5 - <span class="val_6">другое</span> <div class="selector">{PayType_Code}</div></td>
	</tr>	
	<tr>
		<td colspan="12" style="border: 1px solid">15. Место обслуживания: 1 - <span class="val_1">поликлиника</span>, 2 - <span class="val_2">на дому</span>, в т.ч.  3 - <span class="val_3">актив</span> <div class="selector">{ServiceType_Code}</div></td>
	</tr>
	<tr>
		<td colspan='12' style="border: 1px solid">
			16. Цель посещения: <b>
				1 - <span class="val_1">Лечебно-диагностический</span>, 
				2 - <span class="val_2">Консультативный</span>, 
				3 - <span class="val_3">Диспансерное наблюдение</span>, 
				4 - <span class="val_4">Профилактический</span>, 
				5 - <span class="val_5">Профосмотр</span>, 
				6 - <span class="val_6">Реабилитационный</span>, 
				7 - <span class="val_7">Зубопротезный</span>, 
				8 - <span class="val_8">Протезно-ортопедический</span>, 
				10 - <span class="val_10">Доп.диспансеризация</span>, 
				11 - <span class="val_11">Медико-социальный</span>, 
				9 - <span class="val_9">Прочий</span></b> 
			<div class="selector">{VizitType_Code}</div></td>
	</tr>
	<tr>
		<td colspan='12' style="border: 1px solid">17. Законченность случая: <span class="val_1">закончен</span> - 1, <span class="val_0">не закончен</span> - 2, продолжение СПО - 3<div class="selector">{EvnPL_IsFinish}</div></td>
	</tr>
	<tr>
		<td colspan='12' style="border: 1px solid">
			<small>
				17a. 0 - текущий случай, 1 - <span class="val_1">выписан с выздоровлением</span>, 2 - <span class="val_2">выписан с улучшением</span>, 3 - <span class="val_3">динамическое наблюдение (онкол.б-ные)</span>, 4 - направлен на госпитализацию, 5 - направлен в дневной стационар, 6 - направлен в дневной стационар на дому, 7 - переведен к специалисту др. профиля внутри ЛПУ, 8 - направлен в др. ЛПУ, 9 - выдача справки для получения путевки,
				10 - оформление сан.кур. карты, 15 - неявка на повторный прием, 16 - выписка пациента по собственному желанию, нарушение режима, 17 - профилактический осмотр, 18 - консультация специалистов (по показ.) в стационаре, 19 - <span class="val_4">летальный исход</span>, 20 - переведен к др. участковому терапевту, 21 - <span class="val_5">продолжает болеть</span> (> 4 мес.), 22 - диспансерное наблюдение.<div class="selector">{ResultClass_Code}</div></td>
			</small>
		</td>
	</tr>
</table>

<table style="width: 100%; margin-top: 3px;">
	<tr>
		<td style="border: 1px solid; width: 200px;">18. Диагноз МКБ-10: </td>
		<td style="border: 1px solid; width: 200px;" >{FinalDiag_Code}</td>
		<td style="border: 1px solid"></td>
	</tr>		
</table>

<table style="width: 100%; margin-top: 3px;">
	<tr>
		<td style="border: 1px solid; width: 200px;" colspan="19">19. Код мед.услуги (посещение, СМП, КЭС)</td>
		<td class="block"></td><td class="block"></td>
		<td class="block" style="border-width: 2px">{c11}</td>
		<td class="block">{c12}</td><td class="block">{c13}</td><td class="block">{c14}</td>
		<td class="block">{c15}</td><td class="block">{c16}</td><td class="block">{c17}</td>
		<td class="block" style="border-width: 2px">{c21}</td>
		<td class="block">{c22}</td><td class="block">{c23}</td><td class="block">{c24}</td>
		<td class="block">{c25}</td><td class="block">{c26}</td><td class="block">{c27}</td>
		<td class="block" style="border-width: 2px">{c31}</td>
		<td class="block">{c32}</td><td class="block">{c33}</td><td class="block">{c34}</td>
		<td class="block">{c35}</td><td class="block">{c36}</td><td class="block">{c37}</td>
		<td class="block" style="border-width: 2px"></td>
		<td class="block"></td>
	</tr>
	<tr>
		<td class="block" style="border-width: 2px">{c41}</td>
		<td class="block">{c42}</td><td class="block">{c43}</td><td class="block">{c44}</td>
		<td class="block">{c45}</td><td class="block">{c46}</td><td class="block">{c47}</td>
        <td class="block" style="border-width: 2px">{c51}</td>
        <td class="block">{c52}</td><td class="block">{c53}</td><td class="block">{c54}</td>
        <td class="block">{c55}</td><td class="block">{c56}</td><td class="block">{c57}</td>
        <td class="block" style="border-width: 2px">{c61}</td>
        <td class="block">{c62}</td><td class="block">{c63}</td><td class="block">{c64}</td>
        <td class="block">{c65}</td><td class="block">{c66}</td><td class="block">{c67}</td>
        <td class="block" style="border-width: 2px">{c71}</td>
        <td class="block">{c72}</td><td class="block">{c73}</td><td class="block">{c74}</td>
        <td class="block">{c75}</td><td class="block">{c76}</td><td class="block">{c77}</td>
        <td class="block" style="border-width: 2px">{c81}</td>
        <td class="block">{c82}</td><td class="block">{c83}</td><td class="block">{c84}</td>
        <td class="block">{c85}</td><td class="block">{c86}</td><td class="block">{c87}</td>
        <td class="block" style="border-width: 2px">{c91}</td>
        <td class="block">{c92}</td><td class="block">{c93}</td><td class="block">{c94}</td>
        <td class="block">{c95}</td><td class="block">{c96}</td><td class="block">{c97}</td>
		<td class="block" style="border-width: 2px"></td>
		<td class="block"></td>
	</tr>	
	<tr>
        <td class="block" style="border-width: 2px">{c101}</td>
        <td class="block">{c102}</td><td class="block">{c103}</td><td class="block">{c104}</td>
        <td class="block">{c105}</td><td class="block">{c106}</td><td class="block">{c107}</td>
        <td class="block" style="border-width: 2px">{c111}</td>
        <td class="block">{c112}</td><td class="block">{c113}</td><td class="block">{c114}</td>
        <td class="block">{c115}</td><td class="block">{c116}</td><td class="block">{c117}</td>
        <td class="block" style="border-width: 2px">{c121}</td>
        <td class="block">{c122}</td><td class="block">{c123}</td><td class="block">{c124}</td>
        <td class="block">{c125}</td><td class="block">{c126}</td><td class="block">{c127}</td>
        <td class="block" style="border-width: 2px">{c131}</td>
        <td class="block">{c132}</td><td class="block">{c133}</td><td class="block">{c134}</td>
        <td class="block">{c135}</td><td class="block">{c136}</td><td class="block">{c137}</td>
        <td class="block" style="border-width: 2px">{c141}</td>
        <td class="block">{c142}</td><td class="block">{c143}</td><td class="block">{c144}</td>
        <td class="block">{c145}</td><td class="block">{c146}</td><td class="block">{c147}</td>
        <td class="block" style="border-width: 2px">{c151}</td>
        <td class="block">{c152}</td><td class="block">{c153}</td><td class="block">{c154}</td>
        <td class="block">{c155}</td><td class="block">{c156}</td><td class="block">{c157}</td>
        <td class="block" style="border-width: 2px"></td>
        <td class="block"></td>
	</tr>	
	<tr>
        <td class="block" style="border-width: 2px">{c161}</td>
        <td class="block">{c162}</td><td class="block">{c163}</td><td class="block">{c164}</td>
        <td class="block">{c165}</td><td class="block">{c166}</td><td class="block">{c167}</td>
        <td class="block" style="border-width: 2px">{c171}</td>
        <td class="block">{c172}</td><td class="block">{c173}</td><td class="block">{c174}</td>
        <td class="block">{c175}</td><td class="block">{c176}</td><td class="block">{c177}</td>
        <td class="block" style="border-width: 2px">{c181}</td>
        <td class="block">{c182}</td><td class="block">{c183}</td><td class="block">{c184}</td>
        <td class="block">{c185}</td><td class="block">{c186}</td><td class="block">{c187}</td>
        <td class="block" style="border-width: 2px">{c191}</td>
        <td class="block">{c192}</td><td class="block">{c193}</td><td class="block">{c194}</td>
        <td class="block">{c195}</td><td class="block">{c196}</td><td class="block">{c197}</td>
        <td class="block" style="border-width: 2px">{c201}</td>
        <td class="block">{c202}</td><td class="block">{c203}</td><td class="block">{c204}</td>
        <td class="block">{c205}</td><td class="block">{c206}</td><td class="block">{c207}</td>
        <td class="block" style="border-width: 2px">{c211}</td>
        <td class="block">{c212}</td><td class="block">{c213}</td><td class="block">{c214}</td>
        <td class="block">{c215}</td><td class="block">{c216}</td><td class="block">{c217}</td>
        <td class="block" style="border-width: 2px"></td>
        <td class="block"></td>
	</tr>	
	<tr>
        <td class="block" style="border-width: 2px">{c221}</td>
        <td class="block">{c222}</td><td class="block">{c223}</td><td class="block">{c224}</td>
        <td class="block">{c225}</td><td class="block">{c226}</td><td class="block">{c227}</td>
        <td class="block" style="border-width: 2px">{c231}</td>
        <td class="block">{c232}</td><td class="block">{c233}</td><td class="block">{c234}</td>
        <td class="block">{c235}</td><td class="block">{c236}</td><td class="block">{c237}</td>
        <td class="block" style="border-width: 2px">{c241}</td>
        <td class="block">{c242}</td><td class="block">{c243}</td><td class="block">{c244}</td>
        <td class="block">{c245}</td><td class="block">{c246}</td><td class="block">{c247}</td>
        <td class="block" style="border-width: 2px">{c251}</td>
        <td class="block">{c252}</td><td class="block">{c253}</td><td class="block">{c254}</td>
        <td class="block">{c255}</td><td class="block">{c256}</td><td class="block">{c257}</td>
        <td class="block" style="border-width: 2px">{c261}</td>
        <td class="block">{c262}</td><td class="block">{c263}</td><td class="block">{c264}</td>
        <td class="block">{c265}</td><td class="block">{c266}</td><td class="block">{c267}</td>
        <td class="block" style="border-width: 2px">{c271}</td>
        <td class="block">{c272}</td><td class="block">{c273}</td><td class="block">{c274}</td>
        <td class="block">{c275}</td><td class="block">{c276}</td><td class="block">{c277}</td>
        <td class="block" style="border-width: 2px"></td>
        <td class="block"></td>
	</tr>	
	<tr>
        <td class="block" style="border-width: 2px">{c281}</td>
        <td class="block">{c282}</td><td class="block">{c283}</td><td class="block">{c284}</td>
        <td class="block">{c285}</td><td class="block">{c286}</td><td class="block">{c287}</td>
        <td class="block" style="border-width: 2px">{c291}</td>
        <td class="block">{c292}</td><td class="block">{c293}</td><td class="block">{c294}</td>
        <td class="block">{c295}</td><td class="block">{c296}</td><td class="block">{c297}</td>
        <td class="block" style="border-width: 2px">{c301}</td>
        <td class="block">{c302}</td><td class="block">{c303}</td><td class="block">{c304}</td>
        <td class="block">{c305}</td><td class="block">{c306}</td><td class="block">{c307}</td>
        <td class="block" style="border-width: 2px">{c311}</td>
        <td class="block">{c312}</td><td class="block">{c313}</td><td class="block">{c314}</td>
        <td class="block">{c315}</td><td class="block">{c316}</td><td class="block">{c317}</td>
        <td class="block" style="border-width: 2px">{c321}</td>
        <td class="block">{c322}</td><td class="block">{c323}</td><td class="block">{c324}</td>
        <td class="block">{c325}</td><td class="block">{c326}</td><td class="block">{c327}</td>
        <td class="block" style="border-width: 2px">{c331}</td>
        <td class="block">{c332}</td><td class="block">{c333}</td><td class="block">{c334}</td>
        <td class="block">{c335}</td><td class="block">{c336}</td><td class="block">{c337}</td>
        <td class="block" style="border-width: 2px"></td>
        <td class="block"></td>
	</tr>	
	<tr>
        <td class="block" style="border-width: 2px">{c341}</td>
        <td class="block">{c342}</td><td class="block">{c343}</td><td class="block">{c344}</td>
        <td class="block">{c345}</td><td class="block">{c346}</td><td class="block">{c347}</td>
        <td class="block" style="border-width: 2px">{c351}</td>
        <td class="block">{c352}</td><td class="block">{c353}</td><td class="block">{c354}</td>
        <td class="block">{c355}</td><td class="block">{c356}</td><td class="block">{c357}</td>
        <td class="block" style="border-width: 2px">{c361}</td>
        <td class="block">{c362}</td><td class="block">{c363}</td><td class="block">{c364}</td>
        <td class="block">{c365}</td><td class="block">{c366}</td><td class="block">{c367}</td>
        <td class="block" style="border-width: 2px">{c371}</td>
        <td class="block">{c372}</td><td class="block">{c373}</td><td class="block">{c374}</td>
        <td class="block">{c375}</td><td class="block">{c376}</td><td class="block">{c377}</td>
        <td class="block" style="border-width: 2px">{c381}</td>
        <td class="block">{c382}</td><td class="block">{c383}</td><td class="block">{c384}</td>
        <td class="block">{c385}</td><td class="block">{c386}</td><td class="block">{c387}</td>
        <td class="block" style="border-width: 2px">{c391}</td>
        <td class="block">{c392}</td><td class="block">{c393}</td><td class="block">{c394}</td>
        <td class="block">{c395}</td><td class="block">{c396}</td><td class="block">{c397}</td>
        <td class="block" style="border-width: 2px"></td>
        <td class="block"></td>
	</tr>	
	<tr>
        <td class="block" style="border-width: 2px">{c401}</td>
        <td class="block">{c402}</td><td class="block">{c403}</td><td class="block">{c404}</td>
        <td class="block">{c405}</td><td class="block">{c406}</td><td class="block">{c407}</td>
        <td class="block" style="border-width: 2px">{c411}</td>
        <td class="block">{c412}</td><td class="block">{c413}</td><td class="block">{c414}</td>
        <td class="block">{c415}</td><td class="block">{c416}</td><td class="block">{c417}</td>
        <td class="block" style="border-width: 2px">{c421}</td>
        <td class="block">{c422}</td><td class="block">{c423}</td><td class="block">{c424}</td>
        <td class="block">{c425}</td><td class="block">{c426}</td><td class="block">{c427}</td>
        <td class="block" style="border-width: 2px">{c431}</td>
        <td class="block">{c432}</td><td class="block">{c433}</td><td class="block">{c434}</td>
        <td class="block">{c435}</td><td class="block">{c436}</td><td class="block">{c437}</td>
        <td class="block" style="border-width: 2px">{c441}</td>
        <td class="block">{c442}</td><td class="block">{c443}</td><td class="block">{c444}</td>
        <td class="block">{c445}</td><td class="block">{c446}</td><td class="block">{c447}</td>
        <td class="block" style="border-width: 2px">{c451}</td>
        <td class="block">{c452}</td><td class="block">{c453}</td><td class="block">{c454}</td>
        <td class="block">{c455}</td><td class="block">{c456}</td><td class="block">{c457}</td>
        <td class="block" style="border-width: 2px"></td>
        <td class="block"></td>
	</tr>	
	<tr>
        <td class="block" style="border-width: 2px">{c461}</td>
        <td class="block">{c462}</td><td class="block">{c463}</td><td class="block">{c464}</td>
        <td class="block">{c465}</td><td class="block">{c466}</td><td class="block">{c467}</td>
        <td class="block" style="border-width: 2px">{c471}</td>
        <td class="block">{c472}</td><td class="block">{c473}</td><td class="block">{c474}</td>
        <td class="block">{c475}</td><td class="block">{c476}</td><td class="block">{c477}</td>
        <td class="block" style="border-width: 2px">{c481}</td>
        <td class="block">{c482}</td><td class="block">{c483}</td><td class="block">{c484}</td>
        <td class="block">{c485}</td><td class="block">{c486}</td><td class="block">{c487}</td>
        <td class="block" style="border-width: 2px">{c491}</td>
        <td class="block">{c492}</td><td class="block">{c493}</td><td class="block">{c494}</td>
        <td class="block">{c495}</td><td class="block">{c496}</td><td class="block">{c497}</td>
        <td class="block" style="border-width: 2px">{c501}</td>
        <td class="block">{c502}</td><td class="block">{c503}</td><td class="block">{c504}</td>
        <td class="block">{c505}</td><td class="block">{c506}</td><td class="block">{c507}</td>
        <td class="block" style="border-width: 2px">{c511}</td>
        <td class="block">{c512}</td><td class="block">{c513}</td><td class="block">{c514}</td>
        <td class="block">{c515}</td><td class="block">{c516}</td><td class="block">{c517}</td>
        <td class="block" style="border-width: 2px"></td>
        <td class="block"></td>
	</tr>	
</table>

<table style="width: 100%; margin-top: 3px;">
	<tr>
		<td style="border: 1px solid; border-right: none; width: 160px;">20. Характер заболевания: </td>
		<td style="border: 1px solid; border-left: none;">
			1 - <span class="val_1">острое</span> (+), <span class="val_2">впервые в жизни установленное хроническое</span> (+)<br>
			2 - <span class="val_3">диагноз установлен в прошлом году или ранее</span> (-)
			<div class="selector">{DeseaseTypeSop_Code}</div>
		</td>
	</tr>		
	<tr>
		<td style="border: 1px solid; border-right: none; width: 160px;">21. Диспансерный учёт: </td>
		<td style="border: 1px solid; border-left: none;">
			1 - состоит, 2 - взят, 3 - снят, в т.ч. 1 - выздоровление, 2 - переезд, 3 - перевод, 4 - смерть,<br>
			5 - прочее
		</td>
	</tr>		
</table>
	
<table style="width: 100%; margin-top: -1px;">
	<tr>
		<td style="border: 1px solid; border-right: none; width: 200px;">22. Травма - производственная: </td>
		<td style="border: 1px solid; border-left: none;">
			1 - <span class="val_1">промышленная</span>, 
			2 - на строительстве, 
			3 - <span class="val_3">дорожно-транспортная</span><br>
			4 - <span class="val_4">сельскохозяйственная</span>, 
			5 - <span class="val_5">прочие</span>
			<div class="selector">{PrehospTrauma_Code}</div>
		</td>	
	</tr>
	<tr>
		<td style="border: 1px solid; border-right: none; width: 200px;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; непроизводственная: </td>
		<td style="border: 1px solid; border-left: none;">
			6 - <span class="val_6">бытовая</span>, 
			7 - <span class="val_7">уличная</span>, 
			8 - <span class="val_8">дорожно-транспортная</span>, 
			9 - <span class="val_10">в школе</span>, 
			10 - <span class="val_11">спортивная</span>,<br>
			11 - <span class="val_12">прочие</span>
			<div class="selector">{PrehospTrauma_Code}</div>
		</td>
	</tr>	
	<tr>
		<td style="border: 1px solid;" colspan="2">
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 13 - полученная в результате террористических действий
		</td>
	</tr>		
</table>

<table style="width: 100%; margin-top: 3px;">
	<tr>
		<td style="border: 1px solid;" colspan="2">
			23. Документ временной нетрудоспособности: 1-<span class="val_1">открыт</span>, 2-<span class="val_2">закрыт</span> <div class="selector">{EvnStick_Open}</div>;
			серия ____________ № _________________<br/>
			б/лист [&nbsp;&nbsp;&nbsp;&nbsp;]<br/>
			справка [&nbsp;&nbsp;&nbsp;&nbsp;]<br/>
			<table style="width: 100%; margin-top: -20px;">
				<tr>
					<td style="width: 20%;"></td>
					<td style="width: 20%;">{EvnStick_begDate}</td>
					<td style="width: 20%;">{EvnStick_endDate}</td>
				</tr>
				<tr>
					<td style="width: 20%;"></td>
					<td style="width: 20%;"><small>дата выдачи</small></td>
					<td style="width: 20%;"><small>дата закрытия</small></td>
					<td style="width: 20%;"><small>врач открывший</small></td>
					<td style="width: 20%;"><small>врач закрывший</small></td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td style="border: 1px solid; border-right: none; width: 120px;">23. Причина выдачи: </td>
		<td style="border: 1px solid; border-left: none;">
			1 - <span class="val_1">заболевание</span>, 2 - <span class="val_2">по уходу</span>, 3 - <span class="val_3">карантин</span>, 4 - <span class="val_4">прерывание беременности</span>,<br/>
			5 - <span class="val_5">отпуск по беременности и родам</span>, 6 - <span class="val_6">санаторно-курортное лечение</span><div class="selector">{StickCause}</div>
		</td>
	</tr>	
	</tr>
	<tr>
		<td style="border: 1px solid" colspan="2">
			24.1  по уходу: пол 1-<span class="val_1">муж</span>, 2-<span class="val_2">жен</span><div class="selector">{EvnStick_Sex}</div> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; (возраст лица получившего документ в/н) {EvnStick_Age}
		</td>
	</tr>	
</table>

<script type="text/javascript">activateSelectors();</script>

</body>

</html>