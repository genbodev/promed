<tr class="list-item" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('EvnReanimatPeriod_{EvnReanimatPeriod_id}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('EvnReanimatPeriod_{EvnReanimatPeriod_id}_toolbar').style.display='none'">

    <td>
        <div id="EvnReanimatPeriod_{EvnReanimatPeriod_id}">
            <div id="EvnReanimatPeriod_{EvnReanimatPeriod_id}_content" class="content">
                Реанимационный период: <strong> c  {EvnReanimatPeriod_setDate}<?php if (!empty($EvnReanimatPeriod_disDate)){ ?>  по  {EvnReanimatPeriod_disDate}<?php } ?></strong>&nbsp&nbsp&nbsp
                в:  <strong>&laquo{MedService_Name}&raquo</strong> <br>
                профильное отделение: <strong>&laquo{LpuSection_Name}&raquo</strong>
				
						
				<span class="link" id="EvnReanimatPeriod_{EvnReanimatPeriod_id}_toggleDisplay">Показать</span>
            </div>
        </div>
    </td>
    <td class="toolbar">
        <div id="EvnReanimatPeriod_{EvnReanimatPeriod_id}_toolbar" class="toolbar">
	        <a id="EvnReanimatPeriod_{EvnReanimatPeriod_id}_view" class="button icon icon-view16" title="Просмотр"><span></span></a>
			<?php if (true){ ?>
            <a id="EvnReanimatPeriod_{EvnReanimatPeriod_id}_edit" class="button icon icon-edit16" title="Редактировать"><span></span></a>
		    <a id="EvnReanimatPeriod_{EvnReanimatPeriod_id}_delete" class="button icon icon-delete16" title="Удалить"><span></span></a>
			<?php } ?>
			<?php if (in_array(getRegionNick(), ['adygeya']) && !empty($DiagFinance_IsRankin) && $DiagFinance_IsRankin == 2) { ?>
			<a id="EvnReanimatPeriod_{EvnReanimatPeriod_id}_printRankinScale" class="button icon icon-print16" title="Печать шкалы Рэнкина"><span></span></a>
			<?php } ?>
        </div>
    </td>
</tr>
<tr id="EvnReanimatPeriod_{EvnReanimatPeriod_id}_Full" style="display: none;">
<td>
<!--<div id="EvnReanimatPeriod_{EvnReanimatPeriod_id}_Full" style="display: none;">	-->
	Показания для перевода в реанимацию &nbsp <strong>{ReanimReasonType_Name}</strong> <br>
	Исход пребывания в реанимации &nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp <strong>{ReanimResultType_Name}</strong> <br>
	<br>
	<strong>Регулярные наблюдения состояния</strong> <span class="link" id="EvnReanimatPeriod_{EvnReanimatPeriod_id}_ConditionList_toggleDisplay">Показать</span><br>


	<div id="EvnReanimatConditionList_{EvnReanimatPeriod_id}"  class="data-table" style="display: none;">	

	<table>
        <col class="first last" />

		{EvnReanimatCondition}

		<tr  class="list-item" >
		<td>
		<div id="EvnReanimatCondition_{EvnReanimatCondition_id}">
		  
			с <strong>{EvnReanimatCondition_setDate}&nbsp{EvnReanimatCondition_setTime}</strong> по <strong>{EvnReanimatCondition_disDate}&nbsp{EvnReanimatCondition_disTime}</strong> этап/документ <strong>{Stage_Name} {ArriveFromTxt} </strong>  &nbsp 
			состояние <strong>{Condition_Name}</strong> &nbsp 		  
			<span class="link" id="EvnReanimatCondition_{EvnReanimatCondition_id}_toggleDisplay">Показать</span>   <br>
		  
			<div id="EvnReanimatCondition_{EvnReanimatCondition_id}_Full" style="display: none;"> 
				<br>
				

				<table>
					<col class="first last" />

					<tr  class="list-item" > <td width=22%> <strong>По SOFA / APACHE</strong> </td> <td> {sofa} / {apache} </td> </tr>
					<tr  class="list-item" > <td width=22%> <strong>Температура тела</strong> </td> <td> {Temperature} </td> </tr>
					<tr  class="list-item" > <td width=22%> <strong>Жалобы</strong> </td> <td> {Complaint} </td> </tr>
					<tr  class="list-item" > <td width=22%> <strong>Кожный покров</strong> </td> <td> {SkinTxt} </td> </tr>
					<tr  class="list-item" > <td width=22%> <strong>Сознание</strong> </td> <td> {Conscious} </td> </tr>
					<tr  class="list-item" > <td width=22%> <strong>Дыхание</strong> </td> <td> {Breathing} </td> </tr>
					<tr  class="list-item" > <td width=22%> <strong>Аппарат ИВЛ <br> с параметрами</strong> </td> <td> {IVLapparatus} <br> {IVLparameter}  </td> </tr>
					<tr  class="list-item" > <td width=22%> <strong>Аускультативно </strong> </td> <td> {Auscultatory}  </td> </tr>
					<tr  class="list-item" > <td width=22%> <strong>SpO2 </strong> </td> <td> {SpO2}  </td> </tr>
					<tr  class="list-item" > <td width=22%> <strong>Тоны сердца </strong> </td> <td> {Heart_tones}  </td> </tr>
					<tr  class="list-item" > <td width=22%> <strong>ЧСС </strong> </td> <td> {Heart_frequency}  </td> </tr>
					<tr  class="list-item" > <td width=22%> <strong>Давление </strong> </td> <td> {Pressure}  </td> </tr>
					<tr  class="list-item" > <td width=22%> <strong>Гемодинамика </strong> </td> <td> {Hemodynamics}  </td> </tr>
					<tr  class="list-item" > <td width=22%> <strong>Status localis </strong> </td> <td> {Status_localis}  </td> </tr>
<!--					<tr  class="list-item" > <td width=22%> <strong>Нутритивная поддержка </strong> </td> <td> {Nutritious}  </td> </tr>  BOB - 23.09.2019 - закомментарено -->   
					<tr  class="list-item" > <td width=22%> <strong>Объём инфузии </strong> </td> <td> {InfusionVolume}  </td> </tr>
					<tr  class="list-item" > <td width=22%> <strong>Анальгезия </strong> </td> <td> {Analgesia}  </td> </tr>
					<tr  class="list-item" > <td width=22%> <strong>Диурез </strong> </td> <td> {Diuresis}  </td> </tr>
					<tr  class="list-item" > <td width=22%> <strong>Моча </strong> </td> <td> {Urine}  </td> </tr>
<!--					<tr  class="list-item" > <td width=22%> <strong>Объём диуреза </strong> </td> <td> {DiuresisVolume}  </td> </tr>-->
					<tr  class="list-item" > <td width=22%> <strong> {NevroField} </strong> </td> <td> {Neurologic_Status}  </td> </tr>
					<tr  class="list-item" > <td width=22%> <strong> Совместный осмотр </strong> </td> <td> {CollectiveSurvey}  </td> </tr>
					<tr  class="list-item" > <td width=22%> <strong> {ConclusionField} </strong> </td> <td> {Conclusion}  </td> </tr>
				</table>
				
			</div>
		</div>
		</td>
		</tr>
		
	{/EvnReanimatCondition}
	</table>

	</div>
	<br>
	
	
	
	
	<strong>Шкалы исследования состояния</strong> <span class="link" id="EvnReanimatPeriod_{EvnReanimatPeriod_id}_EvnScaleList_toggleDisplay">Показать</span><br>
	<div id="EvnScaleList_{EvnReanimatPeriod_id}"  class="data-table" style="display: none;">
	<table>
        <col class="first last" />

		{EvnScale}

		<tr  class="list-item" >
		<td>
			<div id="EvnScale_{EvnScale_id}">
				
			<strong>{EvnScale_setDate}&nbsp {EvnScale_setTime}</strong>&nbsp 

<!--			<strong>{EvnScaleType_Name} </strong> &nbsp {EvnScaleResult_Tradic} <strong>{EvnScaleResult} </strong>--> <!--			//BOB - 05.02.2018-->
			<strong>{ScaleType_Name} </strong> &nbsp {EvnScale_ResultTradic} <strong>{EvnScale_Result} </strong>  <!--			//BOB - 05.02.2018-->
			<span class="link" id="EvnScale_{EvnScale_id}_toggleDisplay">Показать</span>   <br>
			
			<div id="EvnScale_{EvnScale_id}_Full" style="display: none;"> 
				<br>
				<table>
				<col class="first last" />
				{ScaleParam}
				
					<tr  class="list-item" > <td width=45%> <strong>{ScaleParameterType_Name} </strong> </td> <td width=45%> {ScaleParameterResult_Name} </td> <td> <strong>{ScaleParameterResult_Value}</strong> </td> </tr>

				{/ScaleParam}
				</table>
			</div>

		</div>	
		</td>
		</tr>
		
	{/EvnScale}
	</table>
			
			
			

	</div>
	<br>
	
	
	
	<strong>Реанимационные мероприятия</strong> <span class="link" id="EvnReanimatPeriod_{EvnReanimatPeriod_id}_ActionList_toggleDisplay">Показать</span><br>
	
	<div id="EvnReanimatActionList_{EvnReanimatPeriod_id}"  class="data-table" style="display: none;">	

	<table>
        <col class="first last" />

		{EvnReanimatAction}

		<tr  class="list-item" >
		<td>
		<div id="EvnReanimatAction_{EvnReanimatAction_id}">
			<strong>{EvnReanimatAction_setDate}&nbsp {EvnReanimatAction_setTime}</strong> - <strong>{EvnReanimatAction_disDate}&nbsp {EvnReanimatAction_disTime}</strong>
			<strong>{ReanimatActionType_Name} </strong>  &nbsp {EvnReanimatAction_ObservValue}
			{Pokazat}
<!--			<span class="link" id="EvnReanimatAction_{EvnReanimatAction_id}_toggleDisplay">Показать</span>  -->
			<br>
		  
			<div id="EvnReanimatAction_{EvnReanimatAction_id}_Full" style="display: none;"> 
				<br>
				<strong>{EvnReanimatActionMethod_Field}</strong>  {EvnReanimatAction_MethodName}
				<strong>{PayType_Field}</strong>  {PayType_name}
				<strong>{EvnReanimatAction_MedicomentField}</strong>  {EvnReanimatAction_Medicoment}<strong>{EvnReanimatAction_DrugDoseField}</strong>  {EvnReanimatAction_DrugDose}
				{ReanimatCathetVeins}
			</div>
		</div>
		</td>
		</tr>
		
	{/EvnReanimatAction}
	</table>

	</div>
	
<!--</div>-->
</td>
</tr>

