
var currentCallCardId = null;
var manipulationCount = 1;
var sending=false;
var db;

//var Whistle = new Audio('/audio/mobile/Whistle.ogg');
//console.log({Whistle:Whistle});
//Whistle.load();


window.onload = function() {
	$.mobile.showPageLoadingMsg('a','Инициализация АРМ. Ждите...',true);
	
	startTimer();
	
	$('.content_div').hide();
	$('#content_call').show();
	setDisabledClassToMenuItems('call');
//	$('#content_stac').show();
//	$('#content_patient').show();
//	$('#content_closecard').show();
	$('#content_closecard .other').hide();
	// Установка статуса
	$('.statusButton').click( function () {
		if (socket.connected==true) {
			$('.statusButton').removeClass('ui-btn-active');
			$(this).addClass('ui-btn-active');
			setStatus(this.id);
			$('#statusName').text($(this).text());
		} else {
			alert('Невозможно выполнить операцию, нет связи с сервером.');
		}
	});
	
	
	
	// Отрисовка дополнительного текстового поля
	$('#content_closecard select').change( function() {
		otherClass = this.name + '_other';
		$('#content_closecard .'+otherClass).hide();
		if (this.options[(this.selectedIndex<0)?0:this.selectedIndex].value=='other') {
			$('#content_closecard #OtherDiv'+this.options[this.selectedIndex].id).show();
		}
	});
	
	//Отрисовка формы для выполненного/невыполненного вызова
//	$('input[name=CallResultView]').change( function() {
//		if (this.value == '1') {
//			$('#DeportCloseDiv').show();
//			$('#DeportFailDiv').hide();
//		} else if (this.value == '2') {
//				$('#DeportFailDiv').show();
//				$('#DeportCloseDiv').hide();
//		} else {};
//	})
	
	//Валидация ввода
	$.mask.definitions['~'] = "[_0987654321]";
	
	$.mask.definitions['d'] = "[012]";
	$.mask.definitions['m'] = "[01]";
	$.mask.definitions['y'] = '[2]';
	
	$.mask.definitions['H'] = "[012]";
	$.mask.definitions['M'] = "[0123456]";
	
	$(".timeInput").mask("d9.m9.y999 H9:M9");
	$(".timeInput").change(function(){
		GoTimeStr = $(this).val().replace(/(\d+).(\d+).(\d+) (\d+):(\d+)/, '$2/$1/$3 $4:$5');
		if ((isNaN(Date.parse(GoTimeStr)))&&($(this).val()!='__.__.____ __:__')&&($(this).val()!='')) {
			alert('Неверное значение даты');
			$(this).val('').focus();
		} else {
			defineRightTimeSequence($(this).attr('name'));
		}
		
	})
	
	$("#InputOther110").mask("через ~~~ часов");
	
	$("#InputOther242").mask("99:99");
	$("#InputOther245").mask("99:99");
	$("#InputOther246").mask("99:99");
	$("#InputOther247").mask("99:99");
	$("#InputOther248").mask("99:99");

	$('input[name=Kilo]').mask("~~~~~~~");
	$('input[name=CHD]').mask("~~~~~~~");
	$('input[name=EffCHD]').mask("~~~~~~~");
	$('input[name=Pulse]').mask("~~~~~~~");
	$('input[name=EffPulse]').mask("~~~~~~~");
	$('input[name=CHSS]').mask("~~~~~~~");
	$('input[name=EffCHSS]').mask("~~~~~~~");
	$('input[name=Temper]').mask("~~~~~~~");
	$('input[name=EffTemper]').mask("~~~~~~~");
	$('input[name=Pulsoxymetry]').mask("~~~~~~~");
	$('input[name=EffPulsoxymetry]').mask("~~~~~~~");
	$('input[name=Glucometry]').mask("~~~~~~~");
	$('input[name=EffGlucometry]').mask("~~~~~~~");
	
	//Обработка нажатия кнопки закрытия вызова
	$('#closeCallCard').click(function(){
		$('#menu_call').addClass('closecard_flag');
		showContentPage('call');
	});
	
	$('#LpuSectionProfile_select').change(function() {
		getStacInfo(this.options[this.selectedIndex].value);
	});

	$('input[name=CloseCardDiag]').autocomplete({
		minChars: 1,
		maxHeight: 250,
		width: $('input[name=CloseCardDiag_id]').width(),
		zIndex: 9999,
		deferRequestBy: 300,
		onSelect: function(data, value){
			$('#CloseCardDiag_id').html(value);
		},
		lookup: []
	});
	connect(function(result,msg){
		$.mobile.hidePageLoadingMsg()
		if (result === false) {
			disconnect();
			alert((msg)?msg:'Ошибка инициализации');
			
		}
		
	});
	
	$('input[name=ArriveTime]').change(function(){
		getSpendedTime();
	});

	$('input[name=EndTime]').change(function(){
		getSpendedTime();
	});

};
function defineRightTimeSequence(inputName) {
	var compareTimeArr = [
		{name:'GoTime',value:Date.parse($('input[name=GoTime]').val().replace(/(\d+).(\d+).(\d+) (\d+):(\d+)/, '$2/$1/$3 $4:$5')),timeName:'Время выезда на вызов'},
		{name:'ArriveTime',value:Date.parse($('input[name=ArriveTime]').val().replace(/(\d+).(\d+).(\d+) (\d+):(\d+)/, '$2/$1/$3 $4:$5')),timeName:'Время прибытия на место вызова'},
		{name:'TransportTime',value:Date.parse($('input[name=TransportTime]').val().replace(/(\d+).(\d+).(\d+) (\d+):(\d+)/, '$2/$1/$3 $4:$5')),timeName:'Время начала транспортировки больного'},
		{name:'ToHospitalTime',value:Date.parse($('input[name=ToHospitalTime]').val().replace(/(\d+).(\d+).(\d+) (\d+):(\d+)/, '$2/$1/$3 $4:$5')),timeName:'Время прибытия в медицинскую организацию'},
		{name:'EndTime',value:Date.parse($('input[name=EndTime]').val().replace(/(\d+).(\d+).(\d+) (\d+):(\d+)/, '$2/$1/$3 $4:$5')),timeName:'Время окончания вызова'},
		{name:'BackTime',value:Date.parse($('input[name=BackTime]').val().replace(/(\d+).(\d+).(\d+) (\d+):(\d+)/, '$2/$1/$3 $4:$5')),timeName:'Время возвращения на подстанцию'}
	]
	
	for (var i=0;i<5;i++) {
		if (!isNaN(compareTimeArr[i].value)) {
			if (isNaN(compareTimeArr[i+1].value)) {
				compareTimeArr[i+1] = compareTimeArr[i];
			} else {
				if (compareTimeArr[i+1].value<compareTimeArr[i].value) {
					alert(compareTimeArr[i+1].timeName+' не должно быть меньше, чем '+compareTimeArr[i].timeName);
					$('input[name='+inputName+']').val('');
					$('input[name='+inputName+']').focus();
					return false;
				}
			}
			
		}
	}
	return true;
}
function getSpendedTime() {
	if (!(($('input[name=GoTime]').val()!='')&&($('input[name=EndTime]').val()!=''))) {
		$('input[name=SummTime]').val('');
		return false;
	}
	GoTimeStr = $('input[name=GoTime]').val().replace(/(\d+).(\d+).(\d+) (\d+):(\d+)/, '$2/$1/$3 $4:$5');
	GoTime = new Date();
	GoTime.setTime(Date.parse(GoTimeStr));
	EndTimeStr = $('input[name=EndTime]').val().replace(/(\d+).(\d+).(\d+) (\d+):(\d+)/, '$2/$1/$3 $4:$5');
	EndTime = new Date();
	EndTime.setTime(Date.parse(EndTimeStr));
	spendedTime = ($.isNumeric((EndTime.getTime()-GoTime.getTime())/1000/60))?((EndTime.getTime()-GoTime.getTime())/1000/60):'';
	$('input[name=SummTime]').val(spendedTime);

}

function nowTime() {
	date = new Date(),
	d = date.getDate(),
	mo = date.getMonth()+1,
	y = date.getFullYear(),
	h = date.getHours(),
	m = date.getMinutes(),
	d = (d < 10) ? '0' + d : d,
	mo = (mo < 10) ? '0' + mo : mo,
	h = (h < 10) ? '0' + h : h,
	m = (m < 10) ? '0' + m : m,
	currentTime = d+'.'+mo+'.'+y+' '+h+':'+m;
	return currentTime;
}

//Генерация/удаление новых полей для указания манипуляций
//пока не надо
function ManipulationButtonClick(id){
	ManipName = $('#ManipulationName'+id).val();
	ManipQuantity = $('#ManipulationQuantity'+id).val();
	ManipButton = $('#ManipulationButton'+id);
	
	console.log({'ManipName':ManipName,'ManipQuantity':ManipQuantity,'ManipButton':ManipButton});
	
	if ($('#ManipulationButton'+id).html() == '-') {
		$('#Manipulation'+id).remove();
		return true;
	}
	
	
	if ($.trim(ManipName) == '') {
		return false;
	} else {
		//Добавляем новое поле ввода манипуляций и меняем + на -  
		manipulationCount++;
		newId = manipulationCount;
		html = 
		'<tr id="Manipulation'+newId+'">'
			+'<td>-</td>'
			+'<td class="Name"><input id="ManipulationName'+newId+'" name="ManipulationName'+newId+'" type="text"/><button data-inline="true" onclick="ManipulationButtonClick('+newId+')"id="ManipulationButton'+newId+'">+</button></td>'
			+'<td class="Count"><input id="ManipulationQuantity'+newId+'" name="ManipulationQuantity'+newId+'" type="text"/></td>'
		+'</tr>';
		$('#ManipulationTable').append(html);
		$('#ManipulationButton'+id).html('-');
		$('#Manipulation'+id+' span .ui-btn-text').html('-');
		$('#Manipulation'+newId).trigger('create');
		
	}
}


//Принять карту вызова
function acceptCallCard(CallCardId,PersonId) {
	//TODO: Загрузить информацию в комбобоксы: соцстатус, работа, пол, регистрация
		showContentPage('patient');
		$('#menu_stac').removeClass('bedBooked');
		setFormDisable(false);
		clearCloseCardForm();
		PersonId = (PersonId==0)?null:PersonId;
		currentCallCardId = CallCardId;
		$('.patientSignalInfo').html('');
		console.log(CallCardId);
		$('input[name=GoTime]').val(nowTime()).click().change();
		$('input[name=GoTime]').click().change();
		$('#closeCardPersonBirthday').html( ($('#content_call #'+currentCallCardId+' .bd').html()=='')?'':'Дата рождения:'+$('#content_call #'+currentCallCardId+' .bd').html());
		$('#callAddr').html('<p>'+$('#content_call #'+currentCallCardId+' .unclosedAddr').html()+'</p>');
		$('#callerInfo').html('<p>'+$('#content_call #'+currentCallCardId+' .unclosedCallerInfo').html()+'</p>');
		$('#CloseCardCallType').html('Тип вызова:'+$('#content_call #'+currentCallCardId+' .unclosedCallType').html());
		$('#CloseCardReasonName').html('Повод:'+$('#content_call #'+currentCallCardId+' .unclosedBirthDayAndReason .ds').html());
		$('input[name=PersonFirName]').val($('#content_call #'+currentCallCardId+' .unclosedPersonFir').html());
		$('input[name=PersonSecName]').val($('#content_call #'+currentCallCardId+' .unclosedPersonSec').html());
		$('input[name=PersonSurName]').val($('#content_call #'+currentCallCardId+' .unclosedPersonSur').html());
		if ($('#content_call #'+currentCallCardId+' .unclosedPersonSex').html()!=0) {
			$('select[name=PersonSex_id] option:selected').removeAttr('selected');
			$("select[name=PersonSex_id] [value='"+$('#content_call #'+currentCallCardId+' .unclosedPersonSex').html()+"']").attr("selected", "selected");
			$('select[name=PersonSex_id] option:selected').change();
		}

		$('select[name=AgeType_id] option:selected').removeAttr('selected');
		$("select[name=AgeType_id] [value='"+$('#content_call #'+currentCallCardId+' .unclosedPersonAgeTypeVal').html()+"']").attr("selected", "selected");
		$('select[name=AgeType_id] option:selected').change();
		$("input[name=PersonAge]").val($('#content_call #'+currentCallCardId+' .unclosedPersonAge').html());
		if (!socket.connected) {
			alert("Нет связи с с сервером!");
			return true;
		}
		$.mobile.showPageLoadingMsg('a','Загрузка сигнальной информации',true);
		socket.emit('callAccepted',PersonId,
			function(resultEvnTree, resultSignalInformation,error){
				
				if (error) {
					processServerError(error,'В процессе получения сигнальной информации на сервере произошла ошибка! Обратитесь к администратору',false)
					return;
				}
				
				if (resultSignalInformation !== null) {
					try {
						dataSignalInformation = JSON.parse(resultSignalInformation);
						console.log({'resultSignalInformation':dataSignalInformation});

						if (dataSignalInformation.success) {
								newHtml = JSON.parse(dataSignalInformation.html);
								$('#personData').html(newHtml.personData);
								$('#PersonMedHistory').html(newHtml.PersonMedHistory);
								$('#Anthropometry').html(newHtml.Anthropometry);
								$('#BloodData').html(newHtml.BloodData);
								$('#AllergHistory').html(newHtml.AllergHistory);
								$('#ExpertHistory').html(newHtml.ExpertHistory);
								$('#PersonDispInfo').html(newHtml.PersonDispInfo);
								$('#DiagList').html(newHtml.DiagList);
								$('#SurgicalList').html(newHtml.SurgicalList);
								$('input[name=PersonJobPlace]').val($('#SignalInfoPersonWorkPlace').html());
								$('input[name=DocumentNum]').val($('#SignalInfoPersonDocument').html());
						}
					}
					catch (e) {
						console.log('Ошибка чтения данных сигнальной информации');
						console.log({'error':e,'resultSignalInformation':resultSignalInformation});
					}
					
					try {
						dataEvnTree = JSON.parse(resultEvnTree);
						//console.log({'resultEvnTree':dataEvnTree});
						for (i=0; i<dataEvnTree.length; i++) {
							spanClassName = '';
							if (dataEvnTree[i].object !== 'SignalInformationAll') {
								switch (dataEvnTree[i].object) {
									case 'EvnPL':
									case 'EvnVizitPL':
										spanClassName = 'polic';
									break;
									case 'EvnPS':
										spanClassName = (dataEvnTree[i].surgery == '1')?'stac':'surg';
									break;
									default:
										spanClassName = 'emerg';
									break;
								}
								addEvnTitle(dataEvnTree[i],spanClassName);
							}
						}
					}
					catch (e) {
						alert('Ошибка чтения данных случаев лечения');						
						console.log({'error':e,'resultEvnTree':resultEvnTree});						
					}
					
					
				} else {
					$('#content_patient #personData').html('<h3> Пациент не идентифицирован </h3>');
					//TODO: Включить поля идентификации пациента в форму закрытия
					console.log('Patient isn"t defined');
				}
				$.mobile.hidePageLoadingMsg();
			}
		)
};

function addEvnTitle(data,spanClassName) {
	html = '<div data-role="collapsible" data-collapsed="true" id="'+data.object_value+'" >' +
				'<h3 class="event" onclick="showEvnData(\''+data.object_value+'\',\''+data.object+'\')"><span class="'+spanClassName+'"></span>'+data.text+'</h3>'+
				'<div class="eventContent"></div>'+
			'</div>';
	$('#eventSet').append(html);
	$('#'+data.object_value).collapsible();
};

function addStacRecord(data){
}

function showEvnData(id,objectName) {
	if ($('#'+id+' .eventContent').hasClass('loaded')) {
		return;
	}
	if (!socket.connected) {
		alert("Нет связи с с сервером!");
		return true;
	}
	$.mobile.showPageLoadingMsg('a','Загрузка информации о случае лечения',true);
	socket.emit('loadEvn',objectName,id, function (eventData,error){
		
		if (error) {
			processServerError(error,'В процессе загрузки данных случая на сервере произошла ошибка! Обратитесь к администратору',false)
			return;
		}
		
		console.log(eventData);
		try {
			data = JSON.parse(eventData);
			if (data.success == true) {
				$('#'+id+' .eventContent').html(data.html);
				$('#'+id+' .eventContent').addClass('loaded');
			}
		} catch(e) {
			alert('Неизвестный ответ от сервера, обратитесь к администратору');
		}
		$.mobile.hidePageLoadingMsg();
	})
};

function playSoundNotification() {
	var newAudioObj = $('#audioNotification').clone(true);
	newAudioObj[0].autoplay = true;
	newAudioObj[0].load();
	newAudioObj[0].addEventListener("ended", function () {
            $(this.remove());
	}, false);
}

function addNewCard(data) {
	console.log($('#audioNotification'));
	console.log({'CallCardData':data});
	var newCallObj = $('#unclosedTemplate').clone(true);
	newCallObj.removeAttr('style');
	newCallObj.attr('id',data.CmpCallCardId);
	newCallObj.find('.unclosedPersonName').html(data.PersonFIO);
	newCallObj.find('.unclosedPersonId').html(data.PersonId);	
	var cmpReasonName = '';
	if (data.CmpReasonName != '' && data.CmpReasonName != undefined) {		
		cmpReasonName = data.CmpReasonName;
	} 
	newCallObj.find('.unclosedBirthDayAndReason').html('<em class="bd">'+data.PersonBirthday+'</em><em><span class="ds">'+cmpReasonName+'</span></em>');
	newCallObj.find('.unclosedAddr').html(data.AdressName);
	newCallObj.find('.unclosedCallerInfo').html(data.CallerInfo);
	newCallObj.find('.unclosedPersonFir').html(data.PersonFir);
	newCallObj.find('.unclosedPersonSec').html(data.PersonSec);
	newCallObj.find('.unclosedPersonSur').html(data.PersonSur);
	newCallObj.find('.unclosedPersonSex').html(data.SexId);
	newCallObj.find('.unclosedPersonAgeTypeVal').html(data.AgeTypeValue);
	newCallObj.find('.unclosedPersonAge').html(data.Age);
	
	
	newCallObj.find('.unclosedCallType').html(data.CmpCallTypeName);
	newCallObj.find('.timestamp').html(data.CmpCallCardPrmDate);
	newCallObj.find('button').click( function(){acceptCallCard(data.CmpCallCardId,data.PersonId)});
	$('#UnclosedCards').prepend(newCallObj);
	$('#unclosedCardsCount').html(($('#unclosedCardsCount').html())*1+1);
	playSoundNotification();
};

function updateClosedCardsView() {
	createdClosedCallCards = new Array();
	$('#ClosedCards').empty();
	db.transaction(function(tx){
		tx.executeSql('SELECT id,Sended,Date,ViewData FROM CallCards', [], function (tx, results) {
			$('#closedCardsCount').html(results.rows.length);
			for (i=0;i<results.rows.length;i++) {
//				if (results.rows.item(i).sended == 1) {
				ViewData = JSON.parse(results.rows.item(i).ViewData)
				var newClosedCallObj = $('#closedTemplate').clone(true);
				newClosedCallObj.removeAttr('style');
				newClosedCallObj.attr('id',results.rows.item(i).id);
				
				newClosedCallId = results.rows.item(i).id;				newClosedCallObj.find('.closedPersonName').html(ViewData.PersonName);
				
				newClosedCallObj.find('.closedBirthDayAndReason').html(ViewData.PersonBirthdayAndReason);
				newClosedCallObj.find('.closedAddr').html(ViewData.CallAddr);
				
				newClosedCallObj.find('.timestamp').html(ViewData.timestamp);
				newClosedCallObj.find('button').attr('onclick','showClosedCallCard('+results.rows.item(i).id+')');

				$('#ClosedCards').prepend(newClosedCallObj);
//				}
			}
		},onError);
	});
}

function SetNowTimeForTimeline(timeLineName) {
	if (!$('input[name='+timeLineName+']')) return false;
	var date = new Date(),
		d = date.getDate(),
		mo = date.getMonth()+1,
		y = date.getFullYear(),
		h = date.getHours(),
		m = date.getMinutes();
	d = (d < 10) ? '0' + d : d;
	mo = (mo < 10) ? '0' + mo : mo;
	h = (h < 10) ? '0' + h : h;
	m = (m < 10) ? '0' + m : m;
	if (defineRightTimeSequence(timeLineName)) {
		$('input[name='+timeLineName+']').val(d+'.'+mo+'.'+y+' '+h+':'+m);
	}
}

function startTimer() {
		var time = document.getElementById('time');
		//таймер для постановки даты-времени
		setInterval(function(){
			var date = new Date(),
			d = date.getDate(),
			mo = date.getMonth()+1,
			y = date.getFullYear(),
			h = date.getHours(),
			m = date.getMinutes();
			d = (d < 10) ? '0' + d : d;
			mo = (mo < 10) ? '0' + mo : mo;
			h = (h < 10) ? '0' + h : h;
			m = (m < 10) ? '0' + m : m;
			time.innerHTML = d+'.'+mo+'.'+y+' <span>|</span> '+h+':'+m;
		 }, 1000);
		//таймер для попытки отправления неотправленных карт
		setInterval(function(){
			var date = new Date();
			if (typeof db == 'object') {
				db.transaction(function(tx){
					tx.executeSql('select id,Data,Sended,Date from CallCards where Sended=1', [], function (tx, results) {
						for (i=0;i<results.rows.length;i++) {
							console.log(results.rows.length);
							if (results.rows.item(i).Sended == 1) {
								if (socket.connected && !sending) {
									socket.emit('closeCallCard',JSON.parse(results.rows.item(i).Data), function(response,error){
										
										if (error) {
											processServerError(error,'В процессе закрытия карты вызова на сервере произошла ошибка! Обратитесь к администратору',false)
											return;
										}
										
										console.log(response);
										try {
											var resp = JSON.parse(response);
											console.log(resp);
											if (resp.success) {
												console.log('success:true');
												db.transaction(function(tx){
													tx.executeSql('UPDATE CallCards SET Sended=2 WHERE id=?', [resp.CmpCallCard_id], function (tx, results) {
													},onError);
												});
											} else {
												alert(resp.Error_Msg);
											}
										} catch(e) {
											console.log('success:false');
										}
									});
								}
							}
						}
					},onError);
				});
			};
		 }, 10000); 
};

function setDisabledClassToMenuItems(currentActiveMenuName) {
	var disableddMenuItemClass = 'disabledMenuItemClass';
	$('.menu_item').removeClass(disableddMenuItemClass);
	switch (currentActiveMenuName) {
		case 'call':
			if (!(($('#menu_call').hasClass('closecard_flag'))&&(currentCallCardId!=null)&&(!$('#menu_stac').hasClass('bedBooked')))) {
				$('#menu_stac').addClass(disableddMenuItemClass);
			}
			$('#menu_patient').addClass(disableddMenuItemClass);
			

		break;
		case 'patient':
			$('#menu_stac').addClass(disableddMenuItemClass);
			$('#menu_call').addClass(disableddMenuItemClass);

		break;
		case 'brig':
			if (!(($('#menu_call').hasClass('closecard_flag'))&&(currentCallCardId!=null)&&(!$('#menu_stac').hasClass('bedBooked')))) {
				$('#menu_stac').addClass(disableddMenuItemClass);
			}
			
			if ( ((currentCallCardId==null)||($('#menu_call').hasClass('closecard_flag'))) ) {
				$('#menu_patient').addClass(disableddMenuItemClass);
			} else {
				$('#menu_call').addClass(disableddMenuItemClass);
			}
		break;
		case 'stac':
			$('#menu_patient').addClass(disableddMenuItemClass);
			
		break;
		default:
			break;
	}
	$('.'+disableddMenuItemClass).each(function(idx,elem){
		console.log(elem.id);
	})
}

function setMenuActiveClass(name) {
	setDisabledClassToMenuItems(name);
	var menu=document.getElementById('menu_ul');
	for(var i=0;i<menu.childNodes.length;i++) {
		if (menu.childNodes[i].nodeType == 1) {
			menu.childNodes[i].className = menu.childNodes[i].className.replace( /(?:^|\s)active(?!\S)/ , '' );
		}
	}
	document.getElementById('menu_'+name).className +=' active';
}

function showClosedCallCard(ClosedCardId){
	clearCloseCardForm();
	console.log(ClosedCardId);
	db.transaction(function(tx){
		tx.executeSql('SELECT Data,SelectIndexes FROM CallCards WHERE id=?', [ClosedCardId], function (tx, results) {
			$('#closedCardsCount').html(results.rows.length);
			if (results.rows.length==1) {
				var data=JSON.parse(results.rows.item(0).Data);
				selectedIndexes = JSON.parse(results.rows.item(0).SelectIndexes);
				$('#content_closecard select option').removeAttr('selected');
				$('#content_closecard [type=radio]').removeAttr('checked');
				$('#content_closecard input:checkbox:checked').removeAttr('checked');
				
				$('input[name=GoTime]').val(data.GoTime);
				$('input[name=ArriveTime]').val(data.ArriveTime);
				$('input[name=TransportTime]').val(data.TransportTime);
				$('input[name=ToHospitalTime]').val(data.ToHospitalTime);
				$('input[name=EndTime]').val(data.EndTime);
				$('input[name=BackTime]').val(data.BackTime);
				$('input[name=SummTime]').val(data.SummTime);
				
				$('input[name=PersonSurName]').val(data.Fam);
				$('input[name=PersonFirName]').val(data.Name);
				$('input[name=PersonSecName]').val(data.Middle);
				$('input[name=PersonAge]').val(data.Age);
				$("select[name=PersonSex_id] [value='"+data.Sex_id+"']").attr("selected", "selected");
				$("select[name=PersonSex_id]").change();
				
				$('input[name=PersonJobPlace]').val(data.Work);	
				$('input[name=DocumentNum]').val(data.DocumentNum);
				
				$("select[name=DruncClinic] [value='"+data.isAlco+"']").attr("selected", "selected");
				$('#Complains').val(data.Complaints);
				$('#Anamnez').val(data.Anamnez);
				
				$("select[name=MeningSigns] [value='"+data.isMenen+"']").attr("selected", "selected");
				$("select[name=nistagm] [value='"+data.isNist+"']").attr("selected", "selected");
				$("select[name=aniziocory] [value='"+data.isAnis+"']").attr("selected", "selected");
				$("select[name=reactOnLight] [value='"+data.isLight+"']").attr("selected", "selected");
				$("select[name=acrocianoz] [value='"+data.isAcro+"']").attr("selected", "selected");
				$("select[name=mramornost] [value='"+data.isMramor+"']").attr("selected", "selected");
				$("select[name=involvedInBreatheAct] [value='"+data.isHale+"']").attr("selected", "selected");
				$("select[name=stomachDisturbSymptoms] [value='"+data.isPerit+"']").attr("selected", "selected");

				$('input[name=Urinate]').val(data.Urine);
				$('input[name=Chair]').val(data.Shit);
				$('#otherSymptoms').val(data.OtherSympt);
				$('input[name=workAD]').val(data.WorkAD);
				$('input[name=AD]').val(data.AD);
				$('input[name=CHSS]').val(data.Chss);
				$('input[name=Pulse]').val(data.Pulse);
				$('input[name=Temper]').val(data.Temperature);
				$('input[name=CHD]').val(data.Chd);
				$('input[name=Pulsoxymetry]').val(data.Pulsks);
				$('input[name=Glucometry]').val(data.Gluck);
				$('#AdditionalData').val(data.LocalStatus);
				$('#EKGBefore').val(data.Ekg1);
				$('input[name=EKGBeforeTime]').val(data.Ekg1Time);
				$('#EKGAfter').val(data.Ekg2);
				$('input[name=EKGAfterTime]').val(data.Ekg2Time);
				
				$('input[name=EffAD]').val(data.EfAD);
				$('input[name=EffCHSS]').val(data.EfChss);
				$('input[name=EffPulse]').val(data.EfPulse);
				$('input[name=EffTemper]').val(data.EfTemperature);
				$('input[name=EffCHD]').val(data.EfChd);
				$('input[name=EffPulsoxymetry]').val(data.EfPulsks);
				$('input[name=EffGlucometry]').val(data.EfGluck);	

				$('input[name=Kilo]').val(data.Kilo);
				$('textarea[name=DescText]').val(data.DescText);
				
				$('#callerInfo').html(data.CallerInfo);
				$('#CloseCardCallType').html(data.CloseCardCallType);
				$('#CloseCardReasonName').html(data.CloseCardReasonName);
				$('#callAddr').html(data.CallAddr);
				$('input[name=CloseCardDiag]').val(data.DiagName);
				
				$('textarea[name=HelpPlace]').val(data.HelpPlace);
				$('textarea[name=HelpAuto]').val(data.HelpAuto);
				
				$('input[name=PatientAction]').val(data.PatientAction);
				$("input[name=stomachDisturbSymptoms] [value='"+data.isPerit+"']").attr("selected", "selected");
				console.log({'data.PatientAction':data.PatientAction,'data.CallResultView':data.CallResultView});
				$('input[name=PatientAction][value="'+data.PatientAction+'"]').attr("checked", true).checkboxradio("refresh");
				$('input[name=CallResultView][value='+data.CallResultView+']').attr("checked", true).checkboxradio("refresh").change();
//				$('input[name=CallResultView][value='+data.CallResultView+']').change();
//				$('input[type=radio]').checkboxradio("refresh");
				
				data.combos = JSON.parse(data.combos);
				data.ComboValue = JSON.parse(data.ComboValue);
				
				$('input:checkbox').each(function(i,v) {
					if (typeof data.combos[$(this).attr('name')] != 'undefined') {
						if ($.inArray($(this).attr('id'), data.combos[$(this).attr('name')])!=-1)
						$(this).attr("checked", "checked");
					}
					if (i==($('input:checkbox').length-1)) {
						$('input:checkbox:checked').checkboxradio("refresh");
					}
				});
				
				for (var key in selectedIndexes) {
					$('#content_closecard select[name='+key+'] :nth-child('+(selectedIndexes[key]*1+1)*1+')').attr('selected', 'selected');
				}
				$('#content_closecard select').change();
				
				if (data.ComboValue!=0 && typeof data.ComboValue == 'object'){
					for (i=0;i<data.ComboValue.length;i++) {
						if (data.ComboValue[i] != null) {
							$('#content_closecard #InputOther'+i).val(data.ComboValue[i]);
						}
					}
				}
				showContentPage('call');
				$('.content_div').hide();
				$('#content_closecard').show();
				setFormDisable(true);
			}
		},onError);
	});
	
	
	
	//Грузим комбобоксы и чекареа
	//data.AgeType_id = $('select[name=AgeType_id] option:selected').val();
//	data.combos = {};
//	data.comboboxes = {};
}

function showContentPage(name) {
	setDisabledClassToMenuItems(name);
	$('.menu_item').removeClass('active');
	$('#menu_'+name).addClass('active');
	$('.content_div').hide();
	$('.footer_button').hide();
	if ((name == 'call')&&($('#menu_call').hasClass('closecard_flag'))) {
		$('#content_closecard').show();
		$('#closeCall').show();
	} else {
		if (name=='patient') {
			$('#closeCallCard').show();
		}
		$('#content_'+name).show();
	}
}

function getStacInfo(){
	console.log($('#content_stac #LpuSectionProfile_select').val());
	$('#content_stac #bookingOptions').empty();
	socket.emit('getStacInfo',$('#content_stac #LpuSectionProfile_select').val(),function(response, error){
		
		if (error) {
			processServerError(error,'В процессе получения информации о койках на сервере произошла ошибка! Обратитесь к администратору',false)
			return;
		}
		
		result = JSON.parse(response);
		console.log({data:result.data,type: (typeof result.data)});
		lpuIdArray = new Array();
		lpuInfoArray = new Array();
		totalBedCount = 0;
		if ((result.success)&&(result.data.length >0)) {
			for (i=0;i<result.data.length;i++){
				totalBedCount += result.data[i].EmergencyBedFree;
				if (result.data[i].EmergencyBedFree!=0) {
					if ($.inArray(result.data.Lpu_id, lpuIdArray)==-1){

							lpuIdArray.push(result.data.Lpu_id)
							newLpu = {};
							newLpu.Lpu_id = result.data[i].Lpu_id;
							newLpu.Lpu_Name = result.data[i].Lpu_Name;
							newLpu.Lpu_freeBed = result.data[i].EmergencyBedFree;
							newLpu.Lpu_Addr = result.data[i].Address_Address
							newLpu.Lpu_sections = [{LpuSection_Name:result.data[i].LpuSection_Name,LpuSection_id:result.data[i].LpuSection_id,freeBedCount:result.data[i].EmergencyBedFree}];
							lpuInfoArray.push(newLpu);

					} else {
						found = false;
						for (k=0;((!found)&&(k<lpuInfoArray.length));k++) {
							if (lpuInfoArray[k].Lpu_id == result.data[i].Lpu_id) {
								found = true;
								lpuInfoArray[k].Lpu_freeBed += result.data[i].EmergencyBedFree;
								lpuInfoArray[k].Lpu_sections.push({LpuSection_Name:result.data[i].LpuSection_Name,LpuSection_id:result.data[i].LpuSection_id,freeBedCount:result.data[i].EmergencyBedFree});
							}
						}
					}
				}
			}
//			console.log({lpuInfoArray:lpuInfoArray});
			for (i=0;i<lpuInfoArray.length;i++) {
				htmlCollapsible =	'<div data-role="collapsible" data-collapsed="true" id="Lpu'+lpuInfoArray[i].Lpu_id+'">'+
							'<h3>'+lpuInfoArray[i].Lpu_Name+'<span>(<strong class="red">'+lpuInfoArray[i].Lpu_freeBed+'</strong> мест)</span><br/>'+
									'<small>'+lpuInfoArray[i].Lpu_Addr+'</small>'+
								'</h3>'+
								'<div class="paddding30 LpuSections">'	+
								'</div>'+
						'</div>';
				$('#content_stac #bookingOptions').append(htmlCollapsible);
//				console.log({lpuInfoArray:lpuInfoArray,i:i});
				for (k=0;k<lpuInfoArray[i].Lpu_sections.length;k++){
					section = lpuInfoArray[i].Lpu_sections[k];
					console.log(section)
					htmlButton ='<p>'+
									'<a href="#" id="Section'+section.LpuSection_id+'" data-role="button" data-inline="true" '+
										'onclick="bookEmergencyBed('+
											$('#UnclosedCards #'+currentCallCardId+' .unclosedPersonId').html()+
											','+
											section.LpuSection_id+
											','+
											lpuInfoArray[i].Lpu_id+	
											','+
											$('#content_stac #LpuSectionProfile_select').val()+
										')">'+
										section.LpuSection_Name+
										'<strong class="red">   '+section.freeBedCount+'</strong>'+
									'</a>'+
								'</p>';
					console.log(htmlButton);	
					$('#content_stac #bookingOptions #Lpu'+lpuInfoArray[i].Lpu_id+' .LpuSections').append(htmlButton);
					console.log({lpuInfoArray:lpuInfoArray,i:i});
				}
				console.log({' .LpuSections':($('#content_stac #Lpu'+lpuInfoArray[i].Lpu_id+' .LpuSections').html())});
				$('#content_stac #Lpu'+lpuInfoArray[i].Lpu_id+' .LpuSections').trigger('create');
				$('#content_stac #Lpu'+lpuInfoArray[i].Lpu_id).collapsible();
					
			}
			
			
		}
		$('#content_stac #totalBedCount').html(totalBedCount);
		$('#content_stac #totalLpuCount').html(lpuIdArray.length);
	})
}

function bookEmergencyBed(PersonId,LpuSectionId,LpuId,LpuSectionProfileCode){
	if (!socket.connected) {
		alert('К сожалнию, операция недоступна. Нет связи. Попробуйте позже');
		return false;
	}
	$.mobile.showPageLoadingMsg('a','Бронирование койки, пожалуйста, ждите...',true);
	if (PersonId == 0 || $('#CloseCardDiag_id').html()=='') {
		msg = (PersonId == 0)? 'Пациент не идентифицирован. Сообщите о бронировании койки по рации':'Пожалуйста, заполните поле:"Диагноз"';
		alert(msg);
		$('#menu_stac').addClass('bedBooked');
		showContentPage('call');
		$.mobile.hidePageLoadingMsg();
		return false;
	}
	bookBedData = {
		'Person_id':PersonId,
		'LpuSection_id':LpuSectionId,
		'Lpu_id':LpuId,
		'LpuSectionProfile_Code':LpuSectionProfileCode,
		'emergencyBedCount':1,
		'CmpCallCard_id':currentCallCardId,
		'Diag_id':$('#CloseCardDiag_id').html(),
		'EmergencyData_BrigadeNum':$('#EmergencyTeam_Num').html()
	};
	socket.emit('bookEmergencyBed',bookBedData,function(result,error){
		
		if (error) {
			processServerError(error,'В процессе бронирования койки на сервере произошла ошибка! Обратитесь к администратору',false)
			return;
		}
		
		console.log(result);
		res = JSON.parse(result);
		console.log(res);
		if ((typeof res.success!='undefined')&&(res.success)) {
			$('#menu_stac').addClass('bedBooked');
			showContentPage('call');
			$.mobile.hidePageLoadingMsg();
		} else if ((typeof res.Error_Msg != 'undefined')&&( res.Error_Msg.length !=0)) {
			alert(res.Error_Msg);
			$.mobile.hidePageLoadingMsg();
		}
	});
}

function filledReauiredFields() {
	var emptyField = '';
	if ($('input[name=GoTime]').val()=='') {
		alert('Пожалуйста, заполните поле: "Выезд на вызов"');
		return false;
	}
	if ($('input[name=ArriveTime]').val()=='') {
		alert('Пожалуйста, заполните поле: "Прибытие на место вызова"');
		return false;
	}
	if ($('input[name=EndTime]').val()=='') {
		alert('Пожалуйста, заполните поле:"Выезд на вызов"');
		return false;
	}
	if ($('#CloseCardDiag_id').html()=='') {
		alert('Пожалуйста, заполните поле: "Диагноз"');
		return false;
	}
	if ($('input[name=CloseCardDiag]').val()=='') {
		alert('Пожалуйста, заполните поле: "Диагноз"');
		return false;
	}
	

	return true;
}

function closeCard(){
	$.mobile.showPageLoadingMsg('a','Закрытие карты вызова. Ждите...',true);
	try {	
		var data = {};
		data.CmpCallCard_id = currentCallCardId;
		if (!filledReauiredFields()) {
			$.mobile.hidePageLoadingMsg();
			return false;
		}
		data.GoTime = $('input[name=GoTime]').val();
		data.ArriveTime = $('input[name=ArriveTime]').val();
		data.TransportTime = $('input[name=TransportTime]').val();
		data.ToHospitalTime = $('input[name=ToHospitalTime]').val();
		data.EndTime = $('input[name=EndTime]').val();
		data.BackTime = $('input[name=BackTime]').val();
		data.SummTime = $('input[name=SummTime]').val();

		data.Fam = $('input[name=PersonSurName]').val();
		data.Name = $('input[name=PersonFirName]').val();
		data.Middle = $('input[name=PersonSecName]').val();
		data.Age = $('input[name=PersonAge]').val();
		data.Sex_id = $('select[name=PersonSex_id]').val();

		data.Work = $('input[name=PersonJobPlace]').val();	
		data.DocumentNum = $('input[name=DocumentNum]').val();

		data.isAlco = $('select[name=DruncClinic]').val();
		data.Complaints =  $('#Complains').val();
		data.Anamnez = $('#Anamnez').val();

		data.isMenen = $('select[name=MeningSigns]').val();
		data.isNist = $('select[name=nistagm]').val();
		data.isAnis = $('select[name=aniziocory]').val();
		data.isLight = $('select[name=reactOnLight]').val();
		data.isAcro = $('select[name=acrocianoz]').val();
		data.isMramor = $('select[name=mramornost]').val();
		data.isHale = $('select[name=involvedInBreatheAct]').val();
		data.isPerit = $('select[name=stomachDisturbSymptoms]').val();

		data.Urine= $('input[name=Urinate]').val();
		data.Shit= $('input[name=Chair]').val();
		data.OtherSympt= $('#otherSymptoms').val();
		data.WorkAD= $('input[name=workAD]').val();
		data.AD= $('input[name=AD]').val();
		data.Chss= $('input[name=CHSS]').val();
		data.Pulse= $('input[name=Pulse]').val();
		data.Temperature= $('input[name=Temper]').val();
		data.Chd= $('input[name=CHD]').val();
		data.Pulsks= $('input[name=Pulsoxymetry]').val();
		data.Gluck= $('input[name=Glucometry]').val();
		data.LocalStatus= $('#AdditionalData').val();
		data.Ekg1= $.trim($('#EKGBefore').val());
		data.Ekg1Time= $('input[name=EKGBeforeTime]').val();
		data.Ekg2= $.trim($('#EKGAfter').val());
		data.Ekg2Time= $('input[name=EKGAfterTime]').val();

		data.EfAD= $('input[name=EffAD]').val();
		data.EfChss= $('input[name=EffCHSS]').val();
		data.EfPulse= $('input[name=EffPulse]').val();
		data.EfTemperature= $('input[name=EffTemper]').val();
		data.EfChd= $('input[name=EffCHD]').val();
		data.EfPulsks= $('input[name=EffPulsoxymetry]').val();
		data.EfGluck= $('input[name=EffGlucometry]').val();	

		data.Kilo= $('input[name=Kilo]').val();
		data.DescText= $.trim($('textarea[name=DescText]').val());

		data.Diag_id = $('#CloseCardDiag_id').html();

		data.HelpPlace = $.trim($('textarea[name=HelpPlace]').val());
		data.HelpAuto = $.trim($('textarea[name=HelpAuto]').val());

		//Грузим комбобоксы и чекареа
		//data.AgeType_id = $('select[name=AgeType_id] option:selected').val();
		data.combos = {};
	//	data.comboboxes = {};

		$('input:checkbox:checked').each(function() {
			if (typeof data.combos[$(this).attr('name')] == 'undefined') {
				data.combos[$(this).attr('name')] = new Array();
			}
			data.combos[$(this).attr('name')].push($(this).attr('id'));
		});
		data.ComboValue = new Array();
		//Сохраняем дополнительные поля из чекраеа (не из комбо)
		if ($('#InputOther100').val()!='') data.ComboValue[100] = $('#InputOther100').val();
		if ($('#InputOther172').val()!='') data.ComboValue[172] = $('#InputOther172').val();
		var selectedIndexes={};

		var comboArray = $('#content_closecard .combo');

		for (var i=0;i<comboArray.length;i++) {
			if (comboArray[i].options[comboArray[i].selectedIndex].value == 'other') {
				if (comboArray[i].name == 'ResultUfa_id' || comboArray[i].name == 'Patient_id') {
					data.combos[comboArray[i].name] = comboArray[i].options[comboArray[i].selectedIndex].value;
				}
				var additionalFields = $('#OtherDiv'+comboArray[i].options[comboArray[i].selectedIndex].id+' input');
				for (var k=0;k<additionalFields.length;k++) {
					id = additionalFields[k].id.substring(10,additionalFields[0].id.length);
					data.ComboValue[id]=$('#'+additionalFields[k].id).val();
				}
			} else {
				data.combos[comboArray[i].name] = comboArray[i].options[comboArray[i].selectedIndex].value;
			}
			selectedIndexes[comboArray[i].name] = comboArray[i].selectedIndex;
		}

		data.combos = JSON.stringify(data.combos);
		data.ComboValue = JSON.stringify(data.ComboValue);

		data.CallerInfo = $('#callerInfo').html();
		data.CloseCardCallType =$('#CloseCardCallType').html();
		data.CloseCardReasonName = $('#CloseCardReasonName').html();
		data.CallAddr = $('#callAddr').html();
		data.DiagName = $('input[name=CloseCardDiag]').val();
		data.PatientAction = $('input[name=PatientAction]:checked').val();
		data.CallResultView = $('input[name=CallResultView]:checked').val();

		currentDate = new Date();
		var ViewData = {};
		ViewData.PersonName = $('#content_call #'+currentCallCardId+' .unclosedPersonName').html();
		ViewData.PersonBirthdayAndReason = $('#content_call #'+currentCallCardId+' .unclosedBirthDayAndReason').html();
		ViewData.CallAddr=$('#callAddr').html();
		ViewData.timestamp = $('#content_call #'+currentCallCardId+' .timestamp').html();
		console.log(selectedIndexes);

		db.transaction(function(tx){
			tx.executeSql('DELETE FROM CallCards WHERE id = ?', [currentCallCardId], function (tx, results) {
			},onError);
			tx.executeSql('insert into CallCards (id,Data,ViewData,Sended,Date, SelectIndexes) VALUES (?,?,?,?,?,?)', [currentCallCardId,JSON.stringify(data),JSON.stringify(ViewData),1,currentDate.toString(), JSON.stringify(selectedIndexes)], function (tx, results) {
			},onError);
		});

		updateClosedCardsView();
		sending = true;
		callCardId = data.CmpCallCard_id;
		socket.emit('closeCallCard',data, function(response,error){
			
			if (error) {
				processServerError(error,'В процессе закрытия карты вызова на сервере произошла ошибка! Обратитесь к администратору',false)
				return;
			}
			
			console.log(response);
			try {
				resp = JSON.parse(response);
				console.log(resp);
				if (resp.success) {
					console.log('success:true');
					$('#UnclosedCards #'+currentCallCardId).remove();
					db.transaction(function(tx){
						tx.executeSql('UPDATE CallCards SET Sended=2 WHERE id=?', [callCardId], function (tx, results) {
							sending  =false;
						},onError);
					});
					currentCallCardId = null;
					$('#menu_call').removeClass('closecard_flag');
					showContentPage('call');
					$('.footer_button').hide();
					$('#unclosedCardsCount').html(($('#unclosedCardsCount').html())*1-1);
					updateClosedCardsView();
					$.mobile.hidePageLoadingMsg();
				} else {
					alert(resp.Error_Msg);
					$.mobile.hidePageLoadingMsg();
				}
			} catch(e) {
				$('#UnclosedCards #'+currentCallCardId).remove();
				sending  =false;
				console.log('success:false');
				currentCallCardId = null;
				$('#menu_call').removeClass('closecard_flag');
				showContentPage('call');
				$.mobile.hidePageLoadingMsg();
				$('.footer_button').hide();
			}
		});	
	} catch (e) {
		$.mobile.hidePageLoadingMsg();
		alert('Во время сохранения карты вызова произошла ошибка. Обратитесь к администратору');
	}
	
}

function setFormDisable(data) {
	if (typeof data == 'undefined') {
		return false;
	}
	console.log($('input:radio[name=CallResultView]:checked').val());
	if (data) {
//		$('#content_closecard textarea').disable();
		$('#content_closecard input').attr('disabled','disabled');
		$('#content_closecard select').attr('disabled','disabled');
		$('#content_closecard textarea').attr('disabled','disabled');
		$('#content_closecard .ui-radio').addClass('ui-disabled');
	} else {
		$('#content_closecard input').removeAttr('disabled');
		$('#content_closecard .ui-checkbox').removeClass('ui-disabled');
		$('#content_closecard .ui-radio').removeClass('ui-disabled');
		$('#content_closecard select').removeAttr('disabled');
		$('#content_closecard textarea').removeAttr('disabled');
	}
	console.log($('input:radio[name=CallResultView]:checked').val());
}

function clearCloseCardForm() {
	console.log($('input:radio[name=CallResultView]:checked').val());
	$('#content_closecard select option').removeAttr('selected');
	$('#content_closecard input:checkbox:checked').removeAttr('checked');
	$('#content_closecard textarea').val('');
	$('#content_closecard input[type=text]').val('');
	
	
	$('#content_closecard input[type=radio][name=CallResultView][value=1]').click();
	$('#content_closecard input[type=radio][name=PatientAction][value=1]').click();
	$('#content_closecard select :nth-child('+1+')').attr('selected', 'selected');
	$('#content_closecard select').change();
	$('#content_closecard input:checkbox').checkboxradio("refresh");
	console.log($('input:radio[name=CallResultView]:checked').val());
	
	$('#CloseCardDiag_id').html('');
}

function menu_click(name) {
	switch (name) {
		case 'call':
			if ($('#menu_call').hasClass('closecard_flag')||currentCallCardId==null) {
				window_call = ($('#menu_call').hasClass('closecard_flag'))?'#content_closecard':'#content_call';
				$('.content_div').hide();
				$(window_call).show();
				setMenuActiveClass(name);
				console.log('true');
			}
		break;
		case 'patient':
			if (!$('#menu_call').hasClass('closecard_flag')&&currentCallCardId!=null) {
				showContentPage(name);
			}
		break;
		case 'brig':
			$('.content_div').hide();
			$('#content_brig').show();
			setMenuActiveClass(name);
		break;
		case 'stac':
			if ((($('#menu_call').hasClass('active'))||(($('#menu_brig').hasClass('active'))))&&($('#menu_call').hasClass('closecard_flag'))&&(!$('#menu_stac').hasClass('bedBooked'))) {
				$('.content_div').hide();
				$('#content_stac').show();
				setMenuActiveClass(name);
				getStacInfo();
			}
		break;
	}
//	var contents = document.getElementById('content');
//		for(var i=0;i<contents.childNodes.length;i++) {
//		if (contents.childNodes[i].nodeType == 1) {
//			contents.childNodes[i].className = contents.childNodes[i].className.replace( /(?:^|\s)hidden-class(?!\S)/ , '' );
//		}
//	}
//	document.getElementById('content_'+name).replace( /(?:^|\s)hidden-class(?!\S)/ , '' );
//	
//	$('.content_div').hide();
	
//	$('#content_'+name).show();
	//document.getElementById(name).className += 'active';
	return true;
};