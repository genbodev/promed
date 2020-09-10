<style type="text/css" class="printonly">
	.ToothMap{
		width:auto !important; margin: 0 auto;font-family:Arial;
	}
	.statesRow{
		
	}
	.statesLabelTop{
		height: 35px;
		vertical-align: bottom !important;
	}
	.statesLabelBot{
		height: 35px;
		vertical-align: top !important;
	}
	.statesTooth{
		float: left; text-align: left; width:25%;line-height: 30px;
	}
	.numStatesTooth{
		font-weight: bold;
	}
	.toothTable{
		margin:0 auto;
	}
	.toothTop{
		vertical-align: bottom;
	}
	.toothBot{
		vertical-align: top;
	}
	.toothRow{
		/*vertical-align: middle !important;*/
		font-size: 10px;
		color:gray;
		height: 77px;
	}
	.toothRow div{text-align: left;
		
	}
	.verticalText{
		-o-transform: rotate(90deg);-webkit-transform: rotate(90deg);-moz-transform: rotate(90deg);writing-mode: tb-rl;
	}
	.title{
		font-weight: bold;font-size:18px; font-family:Helvetica;
	}
	.header, .footer{
		 margin: 0 auto;
		 max-width: 800px;
	}
	.patient{
		font-size:19px;
	}
	.numberLabel{
		color:gray;
	}
	.toothStates div{
		cursor: pointer;
	}
	.trTop .toothStates .ToothCode{

	}
	.trBot .toothStates .ToothCode{

	}

</style>
<div class="ToothMap">
    <div class="printonly header" style="margin-bottom: 30px; font-size: 12px;">
	    <p class="title">Зубная карта</p>
        <div style="float: left; margin-left: 10px; text-align: left;line-height: 9px;">
            <p class="patient-label">Пациент:</p>
            <p class="patient">{Person_SurName} {Person_FirName} {Person_SecName}, {Person_BirthDay} г.р.</p>
        </div>
        <div style="margin-right: 100px; text-align: left; float:right;">
            <p class="vizit-info">Врач: {MedPersonal_Fin}</p>
            <p class="vizit-info">Дата: {EvnVizitPLStom_setDate}</p>
        </div>
        <div style="clear: both;"></div>
			<hr>

    </div>
	<table class="toothTable" cellspacing="0">
			<tr>
				<td style="vertical-align: bottom;"><div style="float: left;">
			<table style="border-collapse: collapse;"><tbody>
			<tr class="statesRow" style="height: 40px;"><td></td></tr>
			<tr class="toothRow" style="height: 77px;"><td class="toothRow"><div class="toothLabel">ГЩ</div><div class="verticalText">......</div><div>Я</div></td></tr>
			<tr class="numberRow" style="height: 18px;"><td><div class="printonly numberLabel" style="text-align: left; font-size: 10pt;">№</div></td></tr>
			</tbody></table>
		</div></td>
		<td class="toothTop" style="border-right: 1px solid #cccccc;border-bottom: 1px solid #cccccc">
					<div style="float: left; margin-left: 5px;">{JawPart1}</div>
				</td>
				<td class="toothTop" style="border-bottom: 1px solid #cccccc">
					<div style="margin-left: 5px;float: left; ">{JawPart2}</div>
				</td>
			</tr>
			<tr>
				<td style="vertical-align: top;">
					<div style="float: left;">
					<table style="border-collapse: collapse;"><tbody>
					<tr class="numberRow" style="height: 18px;"><td><div class="printonly numberLabel" style="text-align: left; font-size: 10pt;">№</div></td></tr>
					<tr class="toothRow" style="height: 77px;"><td class="toothRow" ><div class="toothLabel">Я</div><div class="verticalText">......</div><div>ГЩ</div></td></tr>
					<tr class="statesRow" style="height: 40px;"><td></td></tr>
					</tbody></table>
					</div>
				</td>
				<td class="toothBot" style="border-right: 1px solid #cccccc">
					<div style="float: left; margin-left: 5px;">{JawPart4}</div>
				</td>
				<td class="toothBot">
					<div style="margin-left: 5px;float: left; ">{JawPart3}</div>
				</td>
			</tr>
		</table>

	<div class="printonly footer" style="margin-top: 30px; font-size: 10pt;">
		<hr>
	    <!--div class="statesTooth">
			<span class="numStatesTooth">{ToothStateClass_Code12}</span> — {ToothStateClass_Name12}
            <br><span class="numStatesTooth">{ToothStateClass_Code13}</span> — {ToothStateClass_Name13}
            <br><span class="numStatesTooth">{ToothStateClass_Code14}</span> — {ToothStateClass_Name14}
            <br><span class="numStatesTooth">{ToothStateClass_Code15}</span> — {ToothStateClass_Name15}
	    </div-->
	    <div class="statesTooth">
	        <span class="numStatesTooth">{ToothStateClass_Code1}</span> — {ToothStateClass_Name1}
            <br><span class="numStatesTooth">{ToothStateClass_Code10}</span> — {ToothStateClass_Name10}
            <br><span class="numStatesTooth">{ToothStateClass_Code5}</span> — {ToothStateClass_Name5}
            <br><span class="numStatesTooth">{ToothStateClass_Code2}</span> — {ToothStateClass_Name2}
	    </div>
	    <div class="statesTooth">
	        <span class="numStatesTooth">{ToothStateClass_Code3}</span> — {ToothStateClass_Name3}
            <br><span class="numStatesTooth">{ToothStateClass_Code4}</span> — {ToothStateClass_Name4}
            <br><span class="numStatesTooth">{ToothStateClass_Code6}</span> — {ToothStateClass_Name6}
	    </div>
        <div class="statesTooth">
            <span class="numStatesTooth">{ToothStateClass_Code7}</span> — {ToothStateClass_Name7}
            <br><span class="numStatesTooth">{ToothStateClass_Code8}</span> — {ToothStateClass_Name8}
            <br><span class="numStatesTooth">{ToothStateClass_Code9}</span> — {ToothStateClass_Name9}
        </div>
	    <div style="clear: both;"></div>
	</div>
</div>
