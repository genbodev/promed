<html>
<head>
	<title>{statement_template_title}</title>
	<style type="text/css">
	body { margin: 0%; padding: 0.8%; }
	p, span { font-family: verdana; font-size: 14px; }
	.bottomText { text-align: center; margin-top: -3px; font-family: verdana; font-size: 10pt; }
	.infoText { margin-top: 1.5%; font-family: verdana; font-size: 10pt; }
	ul {list-style: none; margin-bottom: -0.5%;}
	ul li:before {content: "—"; position: relative; left: -5px;}
	ol {padding-left: 0.8%;}
	.fline {margin-left: 9.5%; margin-right: 30%;}
	.sline {margin-left: 0%; margin-right: 30%;}
	.fio_line {margin-left: 2.6%; margin-right: 30%; margin-top: -0.1%;}
	.sex_line {margin-left: 1.8%; margin-right: 93%; margin-top: -0.1%;}
	.brth_line {margin-left: 6.2%; margin-right: 88%; margin-top: -0.1%;}
	.bplace_line {margin-left: 0%; margin-right: 30%; margin-top: -0.1%;}
	.pasport_line {margin-left: -0.8%; margin-right: 30%;}
	.vlive {margin-left: -0.8%; margin-right: 30%;}
	.plive {margin-left: -0.8%; margin-right: 30%;}
	.addres_reg {margin-left: -0.8%; margin-right: 30%;}
	.dreg_line {margin-left: 7.5%; margin-right: 85%; margin-top: -0.1%;}
	.cont_line {margin-left: 10%; margin-right: 30%; margin-top: -0.1%;}
	.enp_line {margin-left: 12.7%; margin-right: 65%; margin-top: -0.1%;}	
	.mo_line {margin-left: -0.8%; margin-right: 30%;}
	.snils_line {margin-left: 9%; margin-right: 65%; margin-top: -0.1%;}
	.mo_atach_line {margin-left: -0.8%; margin-right: 30%; margin-top: -0.1%;}
	.f_line {margin-left: 2.6%; margin-right: 30%; margin-top: -0.1%;}
	.rel_line {margin-left: -0.8%; margin-right: 30%; margin-top: -0.1%;}
	.reason_line {margin-left: -0.8%; margin-right: 30%; margin-top: -0.1%;}
	.doc_line {margin-left: -0.8%; margin-right: 30%; margin-top: -0.1%;}
	.pasport_line3 {margin-left: -0.8%; margin-right: 30%; margin-top: -0.1%;}
	.pcont_line {margin-left: 16.2%; margin-right: 30%; margin-top: -0.1%;}
	.sign_line {margin-right: 76.5%; margin-top: -0.1%;}
	</style>
	<style>
	@media print and (orientation: landscape) {
		body { margin: 0%; padding: 1.5%;}
		p, span { font-family: verdana; font-size: 14px; }
		.bottomText { text-align: center; margin-top: -3px; font-family: verdana; font-size: 8pt; }
		.fline {margin-left: 17.5%; margin-right: 10%;}
		.sline {margin-left: 0%; margin-right: 10%;}
		.fio_line {margin-left: 5.2%; margin-right: 50%; margin-top: -0.1%;}
		.sex_line {margin-left: 3.5%; margin-right: 85%; margin-top: -0.1%;}
		.brth_line {margin-left: 13%; margin-right: 75%; margin-top: -0.1%;}
		.bplace_line {margin-left: 0%; margin-right: 10%; margin-top: -0.1%; page-break-after: always;}
		.pasport_line {margin-left: -0.8%; margin-right: 10%;}
		.vlive {margin-left: -0.8%; margin-right: 10%;}
		.plive {margin-left: -0.8%; margin-right: 10%;}
		.addres_reg {margin-left: -0.8%; margin-right: 10%;}
		.dreg_line {margin-left: 15%; margin-right: 70%; margin-top: -0.1%;}
		.cont_line {margin-left: 20%; margin-right: 30%; margin-top: -0.1%;}
		.enp_line {margin-left: 25%; margin-right: 50%; margin-top: -0.1%;}
		.mo_line {margin-left: -0.8%; margin-right: 10%; margin-top: -0.1%;}
		.snils_line {margin-left: 18%; margin-right: 60%; margin-top: -0.1%;}
		.mo_atach_line {margin-left: -0.8%; margin-right: 10%; margin-top: -0.1%;}		
		.f_line {margin-left: 5.5%; margin-right: 10%; margin-top: -0.1%;}
		.rel_line {margin-left: -0.8%; margin-right: 10%; margin-top: -0.1%;}
		.reason_line {margin-left: -0.8%; margin-right: 10%; margin-top: -0.1%; page-break-after: always;}
		.doc_line {margin-left: -0.8%; margin-right: 10%; margin-top: -0.1%;}		
		.pasport_line3 {margin-left: -0.8%; margin-right: 10%; margin-top: -0.1%;}
		.pcont_line {margin-left: 32.5%; margin-right: 30%; margin-top: -0.1%;}
		.sign_line {margin-right: 53%; margin-top: -0.1%;}
	}
	@media print and (orientation: portrait) {
		body { margin: 0%; padding: 2.2%;}
		p, span { font-family: verdana; font-size: 12px; }
		.bottomText { text-align: center; margin-top: -3px; font-family: verdana; font-size: 8pt; }
		.fline {margin-left: 19.6%; margin-right: 5%;}
		.sline {margin-left: 0%; margin-right: 5%;}
		.fio_line {margin-left: 6%; margin-right: 50%; margin-top: -0.1%;}
		.sex_line {margin-left: 4.5%; margin-right: 80%; margin-top: -0.1%;}
		.brth_line {margin-left: 15%; margin-right: 70%; margin-top: -0.1%;}
		.bplace_line {margin-left: 0%; margin-right: 5%; margin-top: -0.1%;}
		.pasport_line {margin-left: -0.8%; margin-right: 5%;}
		.vlive {margin-left: -0.8%; margin-right: 5%;}
		.plive {margin-left: -0.8%; margin-right: 5%; page-break-after: always;}
		.addres_reg {margin-left: -0.8%; margin-right: 5%;}
		.dreg_line {margin-left: 17.5%; margin-right: 65%; margin-top: -0.1%;}
		.cont_line {margin-left: 24%; margin-right: 25%; margin-top: -0.1%;}
		.enp_line {margin-left: 29.5%; margin-right: 50%; margin-top: -0.1%;}
		.mo_line {margin-left: -0.8%; margin-right: 5%; margin-top: -0.1%;}
		.snils_line {margin-left: 21.5%; margin-right: 50%; margin-top: -0.1%;}
		.mo_atach_line {margin-left: -0.8%; margin-right: 5%; margin-top: -0.1%;}		
		.f_line {margin-left: 6%; margin-right: 5%; margin-top: -0.1%;}
		.rel_line {margin-left: -0.8%; margin-right: 5%; margin-top: -0.1%;}
		.reason_line {margin-left: -0.8%; margin-right: 5%; margin-top: -0.1%;}
		.doc_line {margin-left: -0.8%; margin-right: 5%; margin-top: -0.1%;}
		.pasport_line3 {margin-left: -0.8%; margin-right: 5%; margin-top: -0.1%;}
		.pcont_line {margin-left: 38%; margin-right: 5%; margin-top: -0.1%;}
		.sign_line {margin-right: 47%; margin-top: -0.1%;}
	}
</style>
</head>
<body>
<!-- /*NO PARSE JSON*/ -->
<table width="100%">
<tr>
	<td width="60%">
	</td>
	<td>
		<p align="left" style="margin-bottom: -0.35%;"><b>Руководителю медицинской организации:</b><br>
		{Lpu_Nick}, {Ruk_MO_FIO}</p>
		<hr>
		<p class="bottomText">(наименование, ФИО руководителя медицинской организации) </p>
		<p align="left" style="margin-bottom: -0.35%;"><b>от:</b> {Person_FIO}</p>
		<hr>
		<p class="bottomText">(ФИО гражданина полностью)</p>
	</td>
</tr>
</table>

<h3 align="center">ЗАЯВЛЕНИЕ №<br>
о выборе медицинской организации</h3>

<p style="margin-left: 2%; margin-bottom: -3px;">Прошу прикрепить: {Person_FIO}</p>
<hr class="fline">
<p class="bottomText"> (ФИО гражданина полностью или ФИО гражданина, законным представителем* которого я являюсь полностью)</p>

<p style="margin-bottom: -0.35%;">к медицинской организации:<br>{Lpu_Name}; {Lpu_Address}</p>
<hr class="sline">
<p class="bottomText">(полное наименование медицинской организации, фактический адрес)</p>

<p style="margin-bottom: -0.7%;">в связи с <i>(нужное выделить знаком «V»):</i></p>
	<ul>
		<li><span>первичным выбором медицинской организации;</span></li>
		<li><span>выбором медицинской организации в соответствии с правом замены один раз в течение календарного года;</span></li>
		<li><span>выбором медицинской организации в связи со сменой места жительства;</span></li>
		<li><span>прекращением деятельности медицинской организации;</span></li>
		<li><span>откреплением от медицинской организации, оказывающей первичную медико-санитарную помощь детскому населению, в связи с достижением 18-летнего возраста.</span></li>
	</ul>
<p style="margin-left: 1.8%; margin-bottom: -0.6%;">Сведения о застрахованном лице:</p>
<ol>
	<li><span>Ф.И.О. {Person_FIO}</span>
		<hr class="fio_line">
	</li>	
	<li><span>Пол: {Sex_Name}</span>
		<hr class="sex_line">
	</li>	
	<li><span>Дата рождения: {Person_BirthYear}</span>
		<hr class="brth_line">
	</li>	
	<li><span>Место рождения:<br>{Person_BAddress}<br></span>
		<hr class="bplace_line">
	</li>	
	<li><span>Паспорт (другой документ, удостоверяющий личность):<br>{PersonDoc}<br></span>
		<hr class="pasport_line">
		<p class="bottomText">(серия, номер, дата и место выдачи документа)</p>
	</li>
	<li><span>Вид на жительство (для иностранных граждан, постоянно проживающих в Российской Федерации):<br>{PersonDocVid}<br></span>
		<hr class="vlive">
		<p class="bottomText">(серия, номер, дата и место выдачи документа)</p>
	</li>	
	<li><span>Место жительства (адрес для оказания медицинской помощи на дому при вызове медицинского работника):<br>{Person_PAddress}</span>
		<hr class="plive">
	</li> 
	<li><span>Адрес регистрации (по постоянному месту жительства, по месту проживания, отсутствие регистрации - нужное подчеркнуть):<br>{Person_RAddress}</span>
		<hr class="addres_reg">
	</li> 
	<li><span>Дата регистрации:</span>
		<hr class="dreg_line">
	</li>
	<li><span>Контактная информация: {Person_Phone}</span>
		<hr class="cont_line">
		<p class="bottomText">(телефон, e-mail)</p>
	</li>	
	<li><span>Страховой медицинский полис: {Polis}</span>
		<hr class="enp_line">
	</li>
	<li><span>Страховая медицинская организация:<br>{OrgSmo_Name}</span>
		<hr class="mo_line">
	</li>
	<li><span>СНИЛС (при наличии): {Person_Snils}</span>
		<hr class="snils_line">
	</li>
	<li><span>Прикреплен к медицинской организации:<br>{LpuAtachName}</span>
		<hr class="mo_atach_line">
		<p class="bottomText">(прежнее прикрепление)</p>
	</li>	
</ol>
<p>

<p style="margin-left: 1.8%; margin-bottom: -0.6%;">Сведения о представителе застрахованного лица (заполняется при подаче заявления представителем застрахованного лица):</p>
<ol>
	<li><span>Ф.И.О. {Deputy_Fio}</span>
		<hr class="f_line">
	</li>
	<li><span>Отношение к гражданину: {DeputyFatherOrMather} (нужное подчеркнуть) или другое (указать):<br>{DeputyKind_Name}</span>
		<hr class="rel_line">
	</li>
	<li><span>Основания для представления интересов застрахованного лица: {DeputyReason} (нужное подчеркнуть) или другое (указать):<br><br></span>
		<hr class="reason_line">
	</li>
	<li><span>Документ, подтверждающий право законного представителя:<br><br></span>
		<hr class="doc_line"><br>
	</li>
	<li><span>Паспорт (другой документ, удостоверяющий личность):<br>{DeputyDoc}<br></span>
		<hr class="pasport_line3">
		<p class="bottomText">(серия, номер, дата и место выдачи документа)</p>
	</li>	
	<li><span>Контактная информация представителя: {DeputyPhone}</span>
		<hr class="pcont_line">
		<p class="bottomText">(телефон, email)</p>
	</li>
</ol>
<p>Подпись застрахованного лица (законного представителя):</p><br>
<hr class="sign_line"><br>
<p style="margin-top: -1%;">&laquo;<u>{CurentDateDay}</u>&raquo; &laquo;<u>{CurentDateMonth}</u>&raquo; &laquo;<u>{CurentDateYear}</u>&raquo; г.</p>
<p class="infoText">* для ребенка до достижения им совершеннолетия, либо до приобретения им дееспособности в полном объеме до достижения совершеннолетия - его родителями или другими законными представителями, для недееспособных граждан - опекунами.</p>
</body>
</html>