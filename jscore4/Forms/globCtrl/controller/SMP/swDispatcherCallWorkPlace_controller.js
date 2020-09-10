/*
 * Контроллер АРМа диспетчера вызовов
 */


Ext.define('SMP.swDispatcherCallWorkPlace_controller', {
    extend: 'SMP.swSMPDefaultController_controller',

	models: [
        'common.DispatcherCallWP.model.AddressMod'
		,'common.DispatcherCallWP.model.PersonMod'
		,'common.DispatcherStationWP.model.CmpCallCard'
    ],

    stores: [
        'common.DispatcherCallWP.store.Address'
		,'common.DispatcherCallWP.store.Person'
		,'common.DispatcherStationWP.store.CmpCallsStore'
    ],
	ident: true,
	time: new Date().getTime(),
	isUndefinded: false,
	cmpDublicateFlag: false,  // флаг найденого дублирующего вызова
	registrationFailure: false, // флаг отказа от вызова
	saveAndContinue: false, // флаг "Сохранить и продолжить"
	isSaveMode: false, //форма в режиме сохранения
	IsUsingMicrophone: true,
	requires: [
		//'sw.tools.swSimplifyRejectSmpCallCardWindow',
		'sw.tools.swDeteriorationSmpCallCardWindow',
		'sw.CmpCallsList',
		'sw.CmpCalls112List',
		//'sw.tools.swSelectFirstSmpCallCard'
    ],

	changeFormDependingResponse: function(params, code){

		var cntr = this,
		baseForm = cntr.baseForm;
		// Родительский вызов
		baseForm.findField('CmpCallCard_rid').setValue(params.CmpCallCard_rid);
		// Номер за год
		// baseForm.findField('CmpCallCard_YearNumberRid').setValue(params.CmpCallCard_YearNumberRid).enable();
		baseForm.findField('CmpCallCard_DayNumberRid').setValue(params.CmpCallCard_DayNumberRid).enable();
		var CmpCallType_id = baseForm.findField('CmpCallType_id');
		if (CmpCallType_id) {
			// Находим запись соответствующую дублирующему вызову
			var rec = CmpCallType_id.findRecord('CmpCallType_Code', code);
			if (rec) {
				CmpCallType_id.setValue(rec.data.CmpCallType_id);
			}
		}
	},

	checkDuplicateByAddress: function(fromCmpCallType, cb){
		var cntr = this,
			//filterParams = {},
			baseForm = cntr.baseForm,
			filterParams = baseForm.getValues(),
			streetsCombo = baseForm.findField('dStreetsCombo'),
			secondStreetCombo = baseForm.findField('secondStreetCombo'),
			cityCombo = baseForm.findField('dCityCombo'),
			streetsDomKv = false;

		//если тип редактирование вызова не по умолчанию выходим
		if(baseForm.findField('typeEditCard').getValue() != 'default') return false;

		// Существует повторный и дублирующий вызов
		if (cntr.oneCheckDuplicate && cntr.oneCheckRecall ) return;

		if(typeof filterParams.CmpCallerType_id == 'string')
		{
			filterParams.CmpCallCard_Ktov = filterParams.CmpCallerType_id;
			filterParams.CmpCallerType_id = null;
		}

		filterParams.checkBy = 'address';
		var old_fP = filterParams;
		// Данные выбранного города/наспункта
		var rec = streetsCombo.bigStore.findRecord('StreetAndUnformalizedAddressDirectory_id', streetsCombo.getValue());
		if (rec){
			filterParams.StreetAndUnformalizedAddressDirectory_id = rec.get('StreetAndUnformalizedAddressDirectory_id');
			filterParams.KLStreet_id = rec.get('KLStreet_id');
			filterParams.UnformalizedAddressDirectory_id = rec.get('UnformalizedAddressDirectory_id');
		}

		var secStreetRec = secondStreetCombo.findRecord('StreetAndUnformalizedAddressDirectory_id', secondStreetCombo.getValue());

		if (secStreetRec){
			filterParams.CmpCallCard_UlicSecond = secStreetRec.get('KLStreet_id');
		}

		//отсечка по улице и дому или неформ. адресу и еще по перекрестку
		if(
			(filterParams.KLStreet_id/* && filterParams.CmpCallCard_Kvar*/ && (filterParams.CmpCallCard_Dom || filterParams.secondStreetCombo || filterParams.CmpCallCard_Korp) )
			|| filterParams.UnformalizedAddressDirectory_id
		){
			streetsDomKv = true;
		}
		// если данных нет, то и искать смысла нет
		if( !streetsDomKv ) return;

		if (typeof cityCombo.store.proxy.reader.jsonData !== 'undefined' && cityCombo.store.getAt(0)){
			var city = cityCombo.store.getAt(0).data;

			if(city.KLAreaLevel_id==4){
				filterParams.KLTown_id = city.Town_id;

				//если региона нет тогда нас пункт не относится к городу
				if(city.Region_id){
					filterParams.KLSubRgn_id = city.Area_pid;
				} else{
					filterParams.KLCity_id = city.Area_pid;
				}
			} else{
				filterParams.KLCity_id = city.Town_id;
				//если город верхнего уровня, то район сохранять не надо
				if(city.KLAreaStat_id!=0)
				{filterParams.KLSubRgn_id = city.Area_pid;}
			}

			filterParams.KLAreaStat_idEdit = city.KLAreaStat_id;
			filterParams.KLRgn_id = city.Region_id;
		}

		filterParams.ARMType = sw.Promed.MedStaffFactByUser.getArmButton().current.ARMType;
		filterParams.Area_id = cntr.baseForm.findField('Area_id').getValue();
		//filterParams.Street_id = cntr.baseForm.findField('Street_id').getValue();
		filterParams.CmpCallCard_prmDate = baseForm.findField('CmpCallCard_prmDate').getRawValue();
		filterParams.CmpCallCard_prmTime = baseForm.findField('CmpCallCard_prmTime').getRawValue();

		function chkzero(num){
			var str = num.toString();
			if (str.length==1) {
				return '0'+str;
			} else {
				return str;
			}
		}
		var milisecondsDifferent = ( new Date() - cntr.datetimeStartEdit ),
			h = chkzero(Math.floor(milisecondsDifferent/3600000)),
			m = chkzero(Math.floor(milisecondsDifferent%3600000/60000)),
			s = chkzero(Math.floor((milisecondsDifferent%100000/1000)%60));

		if( milisecondsDifferent > 0 ){
			filterParams.CmpCallCard_DiffTime = h+':'+m+':'+s;
		}


		cntr.checkDuplicateSmpCallCard(filterParams,function(success,params){
			if(params.DuplicateCount != undefined || params.DuplicateCount == 0) return;
			if ( !success )
			{
				Ext.getCmp('saveBtn').enable();
				Ext.ComponentQuery.query('[refId=saveContinueBtn]')[0].enable();
				if ((filterParams.typeSave != undefined && filterParams.typeSave == 'failure') || params.typeSave == 'failure')
				{
					cntr.getSimplifyRejectCallCardWnd(params);
					return;
				}else if(params.typeSave == 'deterioration'){
					cntr.getDeteriorationSmpCallCardWindow(params);
					return;
				}
			}

			if(filterParams.typeSave == 'double' || params.typeSave == 'double')
				cntr.changeFormDependingResponse(params, 14); // 14 - код дублирующего вызова

			if(params.recdata){
				cntr.insertValues(params.recdata);
				if(!params.CmpCallCard_rid && params.recdata.CmpCallCard_rid){
					params.CmpCallCard_rid = params.recdata.CmpCallCard_rid;
				}
			}


			if(fromCmpCallType && params.DuplicateCount != 0)
				cb = false;

			//"а теперь банановый" (реклама)
			//а теперь башкиры захотели фокус на комментарий
			if(params.DuplicateCount != 0 && !fromCmpCallType)
				Ext.defer(function(){baseForm.findField('CmpCallCard_Comm').focus();},600);

		});
	},

	//проверка адреса в реестре случаев противоправных действий
	//@todo потом подумать - может стоить обернуть полную проверку в 1 метод. есть свои плюсы и минусы
	checkCmpIllegelActAddress: function(){
		var cntr = this,
			baseForm = cntr.baseForm,
			params = baseForm.getValues(),
			streetCombo = cntr.baseForm.findField('dStreetsCombo'),
			requestParams = {
				KLRgn_id : params.KLRgn_id,
				KLSubRGN_id : params.KLSubRGN_id,
				KLCity_id : params.KLCity_id,
				KLTown_id : params.KLTown_id,
				Address_House : params.CmpCallCard_Dom,
				Address_Corpus : params.CmpCallCard_Korp,
				Address_Flat : params.CmpCallCard_Kvar
			};

		var rec = streetCombo.bigStore.findRecord('StreetAndUnformalizedAddressDirectory_id', streetCombo.getValue());
		if (rec){
			requestParams.KLStreet_id = rec.get('KLStreet_id');
		};

		if(+params.Person_id){
			requestParams.Person_id = params.Person_id
		};

		Ext.Ajax.request({
			url: '/?c=CmpCallCard&m=findCmpIllegalAct',
			params: requestParams,
			success: function(response, opts){
				if(response){
					var response_obj = Ext.JSON.decode(response.responseText);

					cntr.setIllegalActMessage(response_obj);
				}
			}
		});
	},

	setIllegalActMessage: function(data, action){

		var illegalActPanel = this.armWindow.down('panel[refId=illegalActPanel]');

		if(data && data.CmpIllegalAct_Comment){
			var dangerUrl = 'extjs4/resources/images/danger.png';

			if(data.Person_id){
				var infoText = '<div style="height: 16px; float: left;'+
					'padding-left: 23px; margin: 0 10px 0 100px; background-image: url('+dangerUrl+'); background-repeat: no-repeat">' +
					'По данному пациенту ' + data.CmpIllegalAct_prmDT + ' зарегистрирован случай противоправного действия в отношении персонала СМП.</br> Комментарий: ' + data.CmpIllegalAct_Comment +
					'</div>';
				illegalActPanel.mode = 'byPerson';
			}else{
				var infoText = '<div style="height: 16px; float: left;'+
					'padding-left: 23px; margin: 0 10px 0 100px; background-image: url('+dangerUrl+'); background-repeat: no-repeat">' +
					'По данному адресу вызова ' + data.CmpIllegalAct_prmDT + ' зарегистрирован случай противоправного действия в отношении персонала СМП.</br> Комментарий: ' + data.CmpIllegalAct_Comment +
					'</div>';
				illegalActPanel.mode = 'byAddress';
			}


			illegalActPanel.el.setHTML(infoText);
			illegalActPanel.show();
		}else{
			//если есть установка по адресу не сбрасывать при сбросе персона
			if(action && action == 'personReset' && illegalActPanel.mode == 'byAddress'){
				return;
			}

			illegalActPanel.el.setHTML('');
			illegalActPanel.hide();
			illegalActPanel.mode = null;
		}
	},

	showCmpReasonTree: function(){
		var cntr = this,
			baseForm = cntr.baseForm,
			CmpReasonfield = baseForm.findField('CmpReason_id');
			
		//если тип редактирование вызова не по умолчанию выходим
		//if(cntr.baseForm.findField('typeEditCard').getValue() == 'forSpecialEmergencyTeam') return false;

        if(!cntr.TreeData || cntr.TreeStore.isLoading()) return;

       // if(cntr.tree && getRegionNick().inlist(['perm'])) return cntr.tree.show();

		cntr.tree = Ext.create('sw.tools.swDesigionTreeWindow', {
			treestore1: Ext.getStore('desigionTreePreStore'),
			treedata1: cntr.TreeData,
			closeAction: getRegionNick().inlist(['perm']) ? 'hide' : 'close',
			listeners: {
				selectReason: function(CmpReason_id, CmpReason_Name, AmbulanceDecigionTree_id){
					var baseForm = cntr.baseForm,
						win = this;

					if (CmpReason_id > 1) {
						baseForm.findField('CmpReason_id').setValue(CmpReason_id);
						baseForm.findField('CmpReason_id').fireEvent('blur');
						//baseForm.findField('CmpReason_Name').setValue(CmpReason_Name);

						if (getRegionNick().inlist(['pskov']) && /^(352|353)\. /.test(CmpReason_Name)) {
							baseForm.findField('Diag_uid').show();
							baseForm.findField('Diag_uid').focus(0, 500);
						} else {
							baseForm.findField('Diag_uid').hide();
							baseForm.findField('Person_Surname').focus(0, 500);
						}
					} else {
						baseForm.findField('Person_Surname').focus(0, 500);
					}

					if(AmbulanceDecigionTree_id && cntr.LpuBuildingOptions.SmpUnitParam_IsSaveTreePath == 'true'){
						baseForm.findField('AmbulanceDecigionTree_id').setValue(AmbulanceDecigionTree_id);
					}

                    CmpReasonfield.inputEl.removeCls( 'x-form-focus' );

					if(CmpReason_id==759){
						var rwin = cntr.getRejectCallCardWnd();
						rwin.on('close', function(){
							win.close();
						})
					}
					else{
						Ext.defer(function(){win.close();}, 200);

					}
				}
			}
		});
		cntr.tree.show();
	},

	insertValues: function(params){
		if(!params)
			return false;
		var cntr = this,
			baseForm = cntr.baseForm;
		// Заполняем только необходимые записи
		this.baseForm.setValues({
			//CmpCallCard_Comm: params.CmpCallCard_Comm,
			CmpCallCard_Dom: params.CmpCallCard_Dom,
			CmpCallCard_Korp: params.CmpCallCard_Korp,
			CmpCallCard_Etaj: params.CmpCallCard_Etaj,
			CmpCallCard_Kodp: params.CmpCallCard_Kodp,
			CmpCallCard_Kvar: params.CmpCallCard_Kvar,
			CmpCallCard_Podz: params.CmpCallCard_Podz,
			Sex_id: params.Sex_id,

			CmpReason_Name: params.CmpReason_Name,
			CmpReason_id: params.CmpReason_id,
			CmpCallCard_Telf: params.CmpCallCard_Telf,
			Person_Birthday_YearAge: params.Person_Age,
			PersonEvn_id: params.PersonEvn_id,
			Person_Birthday: params.Person_Birthday,
			Person_FIO: params.Person_FIO,
			Person_Firname: params.Person_Firname,
			Person_Secname: params.Person_Secname,
			Person_Surname: params.Person_Surname,
			Person_id: params.Person_id,
			CmpCallPlaceType_id: params.CmpCallPlaceType_id,
			lpuLocalCombo: params.lpuLocalCombo,
			selectNmpCombo: params.MedService_id,
			LpuBuilding_id: params.lpuLocalCombo?null:params.LpuBuilding_id,
			CmpCallCard_IsPoli: params.CmpCallCard_IsPoli,
			CmpCallCard_IsExtra: params.CmpCallCard_IsExtra
		});
		this.convertPersonBirthdayToAge();
		/*
		if(params.lpuLocalCombo){
			Ext.defer(function(){
				cntr.baseForm.findField('LpuBuilding_id').setDisabled(true);
				cntr.baseForm.findField('lpuLocalCombo').setDisabled(false);
			},600);
		}
		*/
		//улица
		if(params.StreetAndUnformalizedAddressDirectory_id){
			// порой загрузка занимает некоторое время, предотвратим телодвижения пользователя
			//var Mask = new Ext.LoadMask(Ext.getCmp('DispatchCallWorkPlace'), {msg: "Пожалуйста, подождите..."});
			//Mask.show();
			// город может быть выбран и стор улиц станет иной
			var cityCombo = cntr.baseForm.findField('dCityCombo');

			if(params){
				cityCombo.store.getProxy().extraParams = {
					'KLRgn_id' : params.KLRgn_id,
					'KLCity_id' : params.KLCity_id,
					'KLTown_id' : params.KLTown_id
				};
			}
			cityCombo.getStore().load({
				callback: function(rec){
					try {
						if ( this.getCount() != 1 ) {
							//Mask.hide();
							return;
						}
						cityCombo.setValue(rec[0].get('Town_id'));
						if(!params.StreetAndUnformalizedAddressDirectory_id) return;

						var streetCombo = cntr.baseForm.findField('dStreetsCombo'),
                            secondStreetCombo = cntr.baseForm.findField('secondStreetCombo');

						streetCombo.bigStore.getProxy().extraParams = {
							'town_id' : rec[0].get('Town_id'),
							'Lpu_id' : sw.Promed.MedStaffFactByUser.current.Lpu_id
						};
						streetCombo.bigStore.load({
							callback: function(records){
								secondStreetCombo.bigStore.loadData(records);
								//Mask.hide();
								cntr.baseForm.findField('dStreetsCombo').setValue(params.StreetAndUnformalizedAddressDirectory_id);
							} 
						});
					} catch (e) {
						//Mask.hide();
					}
				}
			});
			// cntr.baseForm.findField('dStreetsCombo').setValue(params.StreetAndUnformalizedAddressDirectory_id)
		}
		// Родительский вызов
		cntr.baseForm.findField('CmpCallCard_rid').setValue(params.CmpCallCard_rid);

		//cntr.baseForm.findField('CmpCallCard_YearNumberRid').setValue(params.CmpCallCard_Ngod).enable();
		cntr.baseForm.findField('CmpCallCard_DayNumberRid').setValue(params.CmpCallCard_Numv).enable();
		//Кто вызывает
		if(params.CmpCallerType_id)
			cntr.baseForm.findField('CmpCallerType_id').setValue(params.CmpCallerType_id);
		else
			cntr.baseForm.findField('CmpCallerType_id').setValue(params.CmpCallCard_Ktov);

		if(params.CmpCallCard_IsExtra){
			baseForm.findField('CmpCallCard_IsExtra').setValue(parseInt(params.CmpCallCard_IsExtra));
		}
		if(params.lpuLocalCombo){
			baseForm.findField('lpuLocalCombo').setValue(params.lpuLocalCombo);
		}
		if(params.MedService_id){
			baseForm.findField('selectNmpCombo').setValue(params.MedService_id);
		}
		if(params.CmpCallCard_IsPoli){
			baseForm.findField('CmpCallCard_IsPoli').setValue(params.CmpCallCard_IsPoli);
		}
	},

	checkDuplicateByFIO: function(fromCmpCallType, cb){

		var cntr = this,
			baseForm = cntr.baseForm,
			allParams = baseForm.getValues(),
			filterParams = {},
			focusField = 'LpuBuilding_id';



		filterParams.CmpCallCard_prmDate = baseForm.findField('CmpCallCard_prmDate').getRawValue();
		filterParams.CmpCallCard_prmTime = baseForm.findField('CmpCallCard_prmTime').getRawValue();
		filterParams.checkBy = 'fio';

		filterParams.Person_id = baseForm.findField('Person_id').getValue();
		if(!filterParams.Person_id ||(cntr.oneCheckRecall && cntr.oneCheckDuplicate)) return;

		cntr.checkDuplicateSmpCallCard(filterParams,function(success,params){
			if(params.DuplicateCount != undefined || params.DuplicateCount == 0) return;

			if (filterParams.typeSave == 'failure' || params.typeSave == 'failure')
			{

				cntr.changeFormDependingResponse(params, 17); // 17 - код типа вызова "Отмена вызова"
				cntr.getSimplifyRejectCallCardWnd(params);
				Ext.getCmp('saveBtn').enable();
				Ext.ComponentQuery.query('[refId=saveContinueBtn]')[0].enable();
			}

			if(filterParams.typeSave == 'double' || params.typeSave == 'double')
				cntr.changeFormDependingResponse(params, 14); // 14 - код дублирующего вызова

			if(params.recdata){
				cntr.insertValues(params.recdata);
				var CmpCallType_id = baseForm.findField('CmpCallType_id');
				if(!params.CmpCallCard_rid && params.recdata.CmpCallCard_rid){
					params.CmpCallCard_rid = params.recdata.CmpCallCard_rid;
				}
			}

			if(fromCmpCallType && params.DuplicateCount == 0)
				Ext.Msg.alert('Ошибка','По введенным данным дублей не было найдено');

			if(params.DuplicateCount != 0)
			{
				if(!getRegionNick().inlist(['ufa', 'krym', 'kz']))
					Ext.defer(function(){CmpCallType_id.focus();},600);
				else
				{
					if(cntr.baseForm.findField(focusField).disabled)
						focusField = 'lpuLocalCombo';
					Ext.defer(function(){cntr.baseForm.findField(focusField).focus();},600);
				}
			}


		});



	},
	getSimplifyRejectCallCardWnd: function(paramCmpCallCard, callback) {
		var cntr = this;
		// var params = paramCmpCallCard || false;
		cntr.paramCmpCallCard = paramCmpCallCard || false;

        var simplifyRejectSmpCallCardWindow = Ext.create('sw.tools.swSimplifyRejectSmpCallCardWindow');

        //var simplifyRejectSmpCallCardWindow = Ext.ComponentQuery.query('window[refId=swSimplifyRejectSmpCallCardWindow]')[0];
		//if( simplifyRejectSmpCallCardWindow.isVisible() ) return;
		//simplifyRejectSmpCallCardWindow.show();

        simplifyRejectSmpCallCardWindow.show();

		var params = cntr.paramCmpCallCard;

		simplifyRejectSmpCallCardWindow.on('saveRejectReason', callback?callback:function(data) {

				if( !simplifyRejectSmpCallCardWindow.isVisible() ) return;

				if(data.cancelBtn)
				{
					var baseForm = cntr.baseForm;
					// Родительский вызов
					baseForm.findField('CmpCallCard_rid').reset();
					// Номер за год
					//baseForm.findField('CmpCallCard_YearNumberRid').disable().reset();
					// Номер за день
					baseForm.findField('CmpCallCard_DayNumberRid').disable().reset();
				}
				else
				{
					var params = cntr.paramCmpCallCard;

					cntr.changeFormDependingResponse(params, 17); // 17 - код типа вызова "Отмена вызова"
					cntr.baseForm.findField('CmpCallCardStatus_Comment').setValue(data.CmpCallCardStatus_Comment);
					cntr.baseForm.findField('CmpRejectionReason_id').setValue(data.CmpRejectionReason_id);
					cntr.baseForm.findField('CmpCallCard_Comm').setValue(data.CmpRejectionReason_Name+'. '+data.CmpCallCardStatus_Comment);

					if(params.recdata){
						cntr.insertValues(params.recdata);
						if(!params.CmpCallCard_rid && params.recdata.CmpCallCard_rid){
							params.CmpCallCard_rid = params.recdata.CmpCallCard_rid;
						}
					}

					if(typeof params.recdata == 'object' && params.recdata.EmergencyTeam_id && cntr.armWindow && cntr.armWindow.socket){
						var cmpCallCardParam = {
							CmpCallCard_id: params.CmpCallCard_rid,
							EmergencyTeam_id: params.recdata.EmergencyTeam_id,
							Comment: data.CmpRejectionReasonName
						}
						// получим настройкив в структуре МО, нам надо учитывать флаг "Отменяющие вызовы" в структуре МО
						Ext.Ajax.request({
							url: '/?c=CmpCallCard4E&m=getSettingsChallengesRequiringTheDecisionOfSeniorDoctor',
							params: {
								LpuBuilding_id: params.recdata.LpuBuilding_id
							},
							success: function(response, opts){
								if(response){
									var response_obj = Ext.JSON.decode(response.responseText);
									// нам надо учитывать флаг "Отменяющие вызовы" в структуре МО
									if(response_obj.LpuBuilding_IsCallCancel == 'false'){
										// если в настройках не стоит флаг решения старшего врача
										// создадим в объекте переменную, по ней, при сохранении карты, определим отправку сообщения в нод об отмене вызова
										// за одним и параметры для передачи определим, не собирать повторно
										cntr.registrationFailure = cmpCallCardParam;
									}else{
										cntr.registrationFailure = false;
									}
								}
							}
						});
					}

					var CmpCallCard_IsExtra = parseInt(params.recdata.CmpCallCard_IsExtra,10);
					if(CmpCallCard_IsExtra == 2){
						cntr.baseForm.findField('CmpCallCard_IsExtra').setValue(CmpCallCard_IsExtra);
						if(!params.recdata.LpuBuilding_id && params.recdata.MedService_id){
							Ext.defer(function(){ cntr.baseForm.findField('selectNmpCombo').setValue(params.recdata.MedService_id); },700);
						}
					}

				}

				simplifyRejectSmpCallCardWindow.close();
			}
		);
	},

	getDeteriorationSmpCallCardWindow: function(paramCmpCallCard){
		var cntr = this;
		cntr.paramCmpCallCard = paramCmpCallCard || false;
		var deteriorationSmpCallCardWindow = Ext.ComponentQuery.query('window[refId=swDeteriorationSmpCallCardWindow]')[0];
		if( deteriorationSmpCallCardWindow.isVisible() ) return;
		deteriorationSmpCallCardWindow.show();

		deteriorationSmpCallCardWindow.on('saveCmpReason', function(data) {
				if( !deteriorationSmpCallCardWindow.isVisible() ) return;
				var params = cntr.paramCmpCallCard;

				cntr.changeFormDependingResponse(params, 14); // 14 - дублирующий

				if(params.recdata){
					cntr.insertValues(params.recdata);
					if(!params.CmpCallCard_rid && params.recdata.CmpCallCard_rid){
						params.CmpCallCard_rid = params.recdata.CmpCallCard_rid;
					}
				}

				cntr.baseForm.findField('CmpReason_id').setValue(data.CmpReason_id);
				if(params.CmpCallCard_IsDeterior)
					cntr.baseForm.findField('CmpCallCard_IsDeterior').setValue(params.CmpCallCard_IsDeterior);

				if(params.recdata.CmpCallCard_IsExtra != undefined && params.recdata.CmpCallCard_IsExtra == 2){
					cntr.baseForm.findField('CmpCallCard_IsExtra').setValue(params.recdata.CmpCallCard_IsExtra);
					if(!params.recdata.LpuBuilding_id && params.recdata.MedService_id){
						Ext.defer(function(){ cntr.baseForm.findField('selectNmpCombo').setValue(params.recdata.MedService_id); },700);
					}
				}

				cntr.deteriorationField = {
					EmergencyTeam_id: (params.recdata.EmergencyTeam_id) ? params.recdata.EmergencyTeam_id : ''
				};
				deteriorationSmpCallCardWindow.close();
			}
		);
	},

	getRejectCallCardWnd: function() {
		var cntr = this;
		var wnd = Ext.create('sw.tools.swRejectSmpCallCardWindow', {
			callback: function(data) {
				if ( !data.CmpReason_id && !data.CmpCallCard_id ) {
					return false;
				}

				var params = {
					CmpCallCardStatusType_id:5, //Отказ
					CmpCallCard_id:	data.CmpCallCard_id ,
					CmpReason_id: data.CmpReason_id,
					CmpCallCardStatus_Comment: data.CmpCallCardStatus_Comment||null
				};

				Ext.Ajax.request({
					url: '/?c=CmpCallCard4E&m=setStatusCmpCallCard',
					params: params,
					success: function(response, opts){
						var obj = Ext.decode(response.responseText);
						if ( obj.success ) {
							Ext.Msg.alert('Информация','Вызов на пациента '+data.Person_FIO+' успешно отменен', function(){
								cntr.baseForm.findField('dStreetsCombo').focus(false, 100);
							});
						} else {
							Ext.Msg.alert('Ошибка','Во время создания талона отказа произошла ошибка, обратитесь к администратору');
						}
					},
					failure: function(response, opts){
						Ext.Msg.alert('Ошибка','Во время создания талона отказа произошла ошибка, обратитесь к администратору');
					}
				});
			}
		});
		wnd.show();
		return wnd;
	},

	onArmClose: function(evt) { //TODO: CHANGE FUNC NAME;
    	var cntr = this,
			mainTabPanel = cntr.win.down('panel[refId=mainTabPanelDW]'),
			showByDpMode = (cntr.armWindow.additionalParams && cntr.armWindow.additionalParams.showByDP)?true:false;

		if(showByDpMode){
			// если окно открыто из АРМ ДП
			if(evt) evt.stopEvent();
			cntr.saveCallAudio();
			cntr.armWindow.close();
		}else{
			var dialWindow = Ext.ComponentQuery.query('[refId=changeIsExtraByLogicWindow]')[0];
			if (dialWindow) return;

			if(evt) evt.stopEvent();
			Ext.MessageBox.show({
				title: 'Внимание',
				msg: 'АРМ будет заблокирован до следующего принятия вызова. Данные буду потеряны.',
				buttons: Ext.Msg.YESNO,
				buttonText :
					{
						yes : 'Ок',
						no : 'Отмена'
					},
				fn: function(btn){
					if (btn == 'yes'){
						if(cntr.selected112Cards)cntr.setStatus112Cards(cntr.selected112Cards, 1);
						var takeCall112Window = Ext.ComponentQuery.query('[refId=takeCall112Window]')[0],
							newCallTab = Ext.ComponentQuery.query('[refId=dispatchCallForm]')[0].tab;
						if(takeCall112Window)takeCall112Window.close();
						mainTabPanel.setActiveTab(newCallTab.card);
						cntr.reloadDispatcherWorkPlace('newCall');


					}
					else
						return false;
				}
			});
		}
	},

	get112AllertWindow: function() { // Окно приема вызова из 112
    	var cntr = this,
			old112AllertWindow = Ext.ComponentQuery.query('window[refId=takeCall112Window]'); // Чтобы не открывалось два окна
		if(old112AllertWindow && old112AllertWindow[0]){
			old112AllertWindow[0].destroy();
		}
		return Ext.create('Ext.window.Window', {
			modal: true,
			title: 'Прием вызова из 112',
			height: 100,
			width: 300,
			refId: 'takeCall112Window',
			layout: 'form',
			closable: false,
			bodyBorder: false,
			defaultFocus: '[name=operatorNum]',
			listeners: {
				show: function(){
					var DCWP_OperatorNum = Ext.ComponentQuery.query('textfield[name=operatorNum]')[0];
					DCWP_OperatorNum.focus(false, 300, function(){
						DCWP_OperatorNum.selectText(DCWP_OperatorNum.getValue().length);
					});
					DCWP_OperatorNum.selectText(DCWP_OperatorNum.getValue().length);
				},
				close: function(){
					cntr.setDefaultValues();
					//cntr.armWindow.BaseForm.el.unmask();
				},
				afterRender: function(thisForm, options){
					if(getRegionNick().inlist(['kareliya'])) {
						this.keyNav = Ext.create('Ext.util.KeyNav', this.el, {
							enter: function() {
								var win = this,
									value = win.down('textfield[name=operatorNum]').getValue();
								cntr.takeCall112SubmitValidation(value, win);
							},
							scope: this
						});
					}
				}
			},
			items: [
				{
					xtype: 'textfield',
					labelWidth: 150,
					id: 'DCWP_OperatorNum',
					name: 'operatorNum',
					labelAlign: 'right',
					style: 'margin-rigth: 10px;',
					listeners: {
						render: function () {
							if (getRegionNick() == 'ufa') {
								this.setValue('112');
								this.selectText(this.getValue().length);
							}
							this.focus();
						}
					},
					fieldLabel: 'Номер оператора 112'
				},
				{
					xtype: 'container',
					layout: {
						type: 'hbox',
						align: 'middle',
						pack: 'center'
					},
					items: [
						{
							text: 'OK',
							handler: function() {
								var window = this.up('window[refId=takeCall112Window]'),
									value = window.down('textfield[name=operatorNum]').getValue();
								cntr.takeCall112SubmitValidation(value, window);
							},
							width: 100,
							style: 'margin-top: 10px;',
							xtype: 'button'
						},
						{
							text: 'Отмена',
							handler: function() {
								var window = this.up('window[refId=takeCall112Window]');
								window.close();
								cntr.getNewCallWindow().show();
							},
							width: 100,
							style: 'margin-top: 10px; margin-left: 10px;',
							xtype: 'button'
						}
					]
				},

			]
		});
	},

	checkShowNewCallWindow:function(){ //Проверка показа окна ожидания принятия нового вызова
		var cntr= this,
			newCallWindow = cntr.getNewCallWindow(),
			globals = getGlobalOptions(),
			callCardWindow = Ext.ComponentQuery.query('[refId=dispatchCallForm]')[0].el,
			takeCall112Button = Ext.ComponentQuery.query('[refId=takeCall112]')[0],
			dispatchCallForm = Ext.ComponentQuery.query('[refId=dispatchCallForm]')[0],
			tab112 = Ext.ComponentQuery.query('[refId=calls112ListDW]')[0].tab;

		if(cntr.LpuBuildingOptions && (cntr.LpuBuildingOptions.SmpUnitParam_IsCall112 || cntr.isNmpArm)
			&& dispatchCallForm.isFormComponentsLoaded == true){
			switch (ARMType) {
				case 'smpdispatchstation': {
					if (cntr.LpuBuildingOptions.SmpUnitParam_IsCall112 == 'true'
						&& globals.smp_show_112_indispstation != '1') {
						callCardWindow.mask();
						newCallWindow.show();
						if(takeCall112Button) takeCall112Button.show();
					} else {
						cntr.checkUsingMicrophone();
						if (tab112) tab112.hide();
					}
					break;
				}
				case 'smpdispatchcall': {
					if (cntr.LpuBuildingOptions.SmpUnitParam_IsCall112 == 'true'){
						if(takeCall112Button) takeCall112Button.show();
					} else {
						if(takeCall112Button) takeCall112Button.hide();
						if (tab112) tab112.hide();
					}
					callCardWindow.mask();
					newCallWindow.show();
					break;
				}
				case 'dispdirnmp':
				case 'dispcallnmp':{
					callCardWindow.mask();
					newCallWindow.show();
					if(takeCall112Button) takeCall112Button.hide();
				}

			}
		}
	},

	getNewCallWindow: function() { // Окно ожидания принятия нового вызова
    	var cntr = this,
			oldNewCallWindow = Ext.ComponentQuery.query('window[refId=newCallMessage]'); // Чтобы не открывалось два окна
		if(oldNewCallWindow && oldNewCallWindow[0]){
			oldNewCallWindow[0].destroy();
		}
		return Ext.create('Ext.window.Window', {
			renderTo: cntr.armWindow.BaseForm.el,
			height: 100,
			width: 350,
			header: false,
			refId: 'newCallMessage',
			hideInToolbar: true,
			title: 'Ожидание принятия нового вызова',
			modal: false,
			closeAction: 'destroy',
			resizable: false,
			layout: {
				type: 'vbox',
				align: 'center',
				pack: 'center'
			},
			onEsc: function(){
				return;
			},
			items: [
				{
					xtype: 'container',
					height: 60,
					layout: {
						type: 'hbox',
						align: 'middle',
						pack: 'center'
					},
					items: [
						{
							xtype: 'label',
							text: 'Ожидание принятия нового вызова'
						}
					]
				},
				{
					xtype: 'container',
					flex: 1,
					items: [
						{
							xtype: 'button',
							refId: 'AcceptCallBtn',
							text: 'Принять новый вызов (F7)',
							listeners: {
								'click': function(){
									var window = this.up('window'),
										cityCombo = cntr.baseForm.findField('dCityCombo');

									cntr.clearAllFields();
									cntr.setDefaultValues();
									cntr.armWindow.BaseForm.el.unmask();
									Ext.ComponentQuery.query('[name=showCard112]')[0].setVisible(false);
									window.close();
									cityCombo.focus(false, 300);
									cntr.checkUsingMicrophone();
								}
							}
						}, {
							xtype: 'button',
							text: 'Принять новый вызов из 112 (F8)',
							refId: 'takeCall112',
							hidden: (cntr.LpuBuildingOptions ? !(cntr.LpuBuildingOptions.SmpUnitParam_IsCall112 == 'true') : true),
							listeners: {
								'click': function(){
									var window = this.up('window');

									cntr.get112AllertWindow().show().focus(false, 300);
									Ext.ComponentQuery.query('button[name=showCard112]')[0].setVisible(false);
									window.destroy();
								}
							}
						}
					]
				}
			],
			listeners: {
				show: function(win) {
					win.focus();
				}
			},
			binding: [
				{
					// F7
					key: [118],
					fn: function (c, evt) {
						evt.stopEvent();
						cntr.setDefaultValues();
						cntr.armWindow.BaseForm.el.unmask();
						Ext.ComponentQuery.query('[name=showCard112]')[0].setVisible(false);
					}
				}, {
					// F8
					key: [Ext.EventObject.F8],
					fn: function (c, evt) {
						evt.stopEvent();
						if (cntr.LpuBuildingOptions ? (cntr.LpuBuildingOptions.SmpUnitParam_IsCall112 == 'true') : true) {
							Ext.ComponentQuery.query('[name=showCard112]')[0].setVisible(false);
						}
					}
				}, {
					key: [Ext.EventObject.ESC],
					fn: function () {
						return;
					}
				}
			]
		});
	},

	reloadDispatcherWorkPlace: function(type) { // Загрузка формы после действий
    	var cntr = this;
			//mask = new Ext.LoadMask(cntr.armWindow, {msg: "Пожалуйста, подождите..."});

    	//mask.show();  // Маску на форму

		if(type == 'newCall') {  // Новый вызов F7;
//			cntr.clearAllFields(); // Очищаем форму
			cntr.time = new Date().getTime();
			Ext.ComponentQuery.query('[refId=calls112ListDW]')[0].tab.hide();

			cntr.armWindow.BaseForm.el.mask(); // Маска на форму

			//mask.hide(); // Убираем маску "Пожалуйста, подождите"

			cntr.getNewCallWindow().show();

		} else if(type=='saveAndContinue') { // Сохрнаить и продолжить ALT + F10

			cntr.clearAllFields(type);  // Очищаем только нужные поля(ТЗ)

		//	mask.hide();

		}
		return false;

	},

	setStatusAll112Cards: function(){
		//установка статусов "новая"
		var CmpCallCard112_ids = [];
		Ext.Ajax.request({
			url: '/?c=CmpCallCard4E&m=setStatusCmpCallCardList112',
			params: {
				allCards: 'all',
				CmpCallCard112_ids: Ext.encode(CmpCallCard112_ids)
			},
			callback: function(opt, success, response) {
				if (success){}
			}
		});
	},
	setStatus112Cards: function(arrayRecs, statusID){
		//установка статусов в обработке
		Ext.Ajax.request({
			url: '/?c=CmpCallCard4E&m=setStatusCmpCallCardList112',
			params: {
				CmpCallCard112_ids: Ext.encode(arrayRecs),
				CmpCallCard112StatusType_id: statusID
			},
			callback: function(opt, success, response) {
				if (success){}
			}
		});
		/*
		Ext.Ajax.request({
			url: '/?c=CmpCallCard4E&m=setStatusCmpCallCard112',
			params: {
				CmpCallCard112_id: 110,
				CmpCallCard112StatusType_id: 2
			},
			callback: function(opt, success, response) {
				if (success){
					var response_obj = Ext.JSON.decode(response.responseText);
				}
			}
		});
		*/
	},

	showAddressInfoWin: function(){
		var cntr = this,
			cityComboVal = cntr.baseForm.findField('dCityCombo').getValue(),
			streetCombo = cntr.baseForm.findField('dStreetsCombo'),
			callTypeCombo = cntr.baseForm.findField('CmpCallType_id'),
			domFieldVal = cntr.baseForm.findField('CmpCallCard_Dom').getValue(),
			streetComboVal;

		var rec = streetCombo.bigStore.findRecord('StreetAndUnformalizedAddressDirectory_id', streetsCombo.getValue());
		if (rec){ streetComboVal = rec.get('KLStreet_id'); };

		var currentCallTypeRec = callTypeCombo.getStore().findRecord('CmpCallType_id',callTypeCombo.getValue());

		if(cityComboVal  && domFieldVal && currentCallTypeRec && (currentCallTypeRec.data.CmpCallType_Code == 15) ){
			var addressInfoWin = Ext.create('common.DispatcherCallWP.tools.swCmpCallCardAddressAttachInfoWindow',
				{
					KLCity_id: cityComboVal,
					KLStreet_id: streetComboVal,
					domNum: domFieldVal
				}
			);
		}
	},

	showSelectFirstSmpCallCard: function(cmpCalls, checkBy, callIsOverdue){

        var cntr = this,
            filterCmpCalls = [],
            baseForm = cntr.baseForm,
			allFields = baseForm.getAllFields(),
			allParams = baseForm.getAllValues(),
            CmpCallCard_rid = allFields.CmpCallCard_rid,
            CmpCallCard_DayNumberRid = allFields.CmpCallCard_DayNumberRid,
            CmpCallCard_IsExtra = allFields.CmpCallCard_IsExtra,
            dStreetsCombo = allFields.dStreetsCombo,
			secondStreetCombo = allFields.secondStreetCombo,
            dCityCombo = allFields.dCityCombo,
            CmpReason_Name = allFields.CmpReason_Name,
			CmpCallerType_id = allFields.CmpCallerType_id,
			CmpCallCard_storDateShow = allFields.CmpCallCard_storDateShow,
			CmpCallCard_storTimeShow = allFields.CmpCallCard_storTimeShow,
			cmpDublicateWindow = Ext.ComponentQuery.query('window[refId=swSelectFirstSmpCallCard]')[0];

        if(cmpCalls && cmpCalls.length){
            cmpCalls.forEach(function(el){
                filterCmpCalls.push(el.CallCard_id);
            });
        };

        //var deteriorationSmpCallCardWindow = Ext.ComponentQuery.query('window[refId=swDeteriorationSmpCallCardWindow]')[0];

        // предотвратим повторной поиск дублирующих вызовов если окно уже открыто
        if(cmpDublicateWindow && cmpDublicateWindow.isVisible() ) {
            return false;
        }

		var selectFirstCardWin = Ext.create('sw.tools.swSelectFirstSmpCallCard', {
            //autoShow: true,
            params: {
                checkBy: checkBy,
                filterCmpCalls: filterCmpCalls,
                filterByName: allParams.Person_Firname,
                filterByFamily: allParams.Person_Surname,
                filterBySecName: allParams.Person_Secname,
                recCityCombo: dCityCombo.getSelectedRecord() ? dCityCombo.getSelectedRecord().get('Town_id'): null,
                recStreetCombo: dStreetsCombo.bigStore.findRecord('StreetAndUnformalizedAddressDirectory_id', dStreetsCombo.getValue()),
                recSecondStreetCombo: secondStreetCombo.getSelectedRecord() ? secondStreetCombo.getSelectedRecord().get('StreetAndUnformalizedAddressDirectory_id'): null,
                domField: allParams.CmpCallCard_Dom,
                korpField: allParams.CmpCallCard_Korp,
                kvarField: allParams.CmpCallCard_Kvar,
				callIsOverdue: callIsOverdue
            },
			listeners:{
				rejectCall: function(rec){
					// Оформить отказ
					if( !rec.data ) return;

					var recCmp = rec.data;

                    cntr.getSimplifyRejectCallCardWnd(recCmp, function(data) {
                       // if( !simplifyRejectSmpCallCardWindow.isVisible() ) return;
                        cntr.cmpDublicateFlag = !data.cancelBtn;
                        //если выбран то true

                        if(data.cancelBtn)
                        {
                            // Родительский вызов
                            CmpCallCard_rid.reset();
                            // Номер за день
                            CmpCallCard_DayNumberRid.disable().reset();
                        }
                        else
                        {
                            recCmp.CmpCallCard_rid = recCmp.CmpCallCard_id;
                            var params = {
                                CmpCallCard_rid : recCmp.CmpCallCard_id,
                                CmpCallCard_DayNumberRid : recCmp.CmpCallCard_Numv
                            }
                            cntr.changeFormDependingResponse(params, 17); // 17 - код типа вызова "Отмена вызова"
							allFields.CmpCallCardStatus_Comment.setValue(data.CmpCallCardStatus_Comment);
							allFields.CmpRejectionReason_id.setValue(data.CmpRejectionReason_id);
							allFields.CmpCallCard_Comm.setValue(data.CmpRejectionReason_Name+'. '+data.CmpCallCardStatus_Comment);
                            recCmp.lpuLocalCombo = recCmp.Lpu_ppdid;

							cntr.setDefaultValues({
								CmpCallCard_Dom: recCmp.CmpCallCard_Dom,
								CmpCallCard_Korp: recCmp.CmpCallCard_Korp,
								CmpCallCard_Etaj: recCmp.CmpCallCard_Etaj,
								CmpCallCard_Kodp: recCmp.CmpCallCard_Kodp,
								CmpCallCard_Kvar: recCmp.CmpCallCard_Kvar,
								CmpCallCard_Podz: recCmp.CmpCallCard_Podz,
								CmpGroupName_id: recCmp.CmpGroupName_id,
								CmpGroup_id: recCmp.CmpGroup_id,
								Sex_id: recCmp.Sex_id,
								CmpReason_Name: recCmp.CmpReason_Name,
								CmpReason_id: recCmp.CmpReason_id,
								KLCity_Name: recCmp.KLCity_Name,
								KLCity_id: recCmp.KLCity_id,
								KLRgn_id: recCmp.KLRgn_id,
								KLStreet_FullName: recCmp.KLStreet_FullName,
								KLStreet_id: recCmp.KLStreet_id,
								KLSubRgn_id: recCmp.KLSubRgn_id,
								KLTown_Name: recCmp.KLTown_Name,
								KLTown_id: recCmp.KLTown_id,
								Town_id: recCmp.KLTown_id,
								//CmpCallCard_Telf: recCmp.CmpCallCard_Telf,
								CmpCallerType_id: recCmp.CmpCallerType_id,
								CmpCallCard_Ktov: recCmp.CmpCallCard_Ktov,
								CmpCallType_Code: 17,
								PersonEvn_id: recCmp.PersonEvn_id,
								Person_Birthday: recCmp.Person_Birthday,
								Person_FIO: recCmp.Person_FIO,
								Person_Firname: recCmp.Person_Firname,
								Person_Secname: recCmp.Person_Secname,
								Person_Surname: recCmp.Person_Surname,
								Person_id: recCmp.Person_id,
								Person_IsUnknown: recCmp.Person_IsUnknown,
								UnformalizedAddressDirectory_Dom: recCmp.UnformalizedAddressDirectory_Dom,
								UnformalizedAddressDirectory_Name: recCmp.UnformalizedAddressDirectory_Name,
								UnformalizedAddressDirectory_id: recCmp.UnformalizedAddressDirectory_id,
								UnformalizedAddressType_id: recCmp.UnformalizedAddressType_id,
								StreetAndUnformalizedAddressDirectory_id: recCmp.StreetAndUnformalizedAddressDirectory_id,
								LpuBuilding_id: recCmp.LpuBuilding_id,
								Lpu_ppdid: recCmp.Lpu_ppdid,
								MedService_id: recCmp.MedService_id,
								CmpCallCard_IsExtra: recCmp.CmpCallCard_IsExtra,
								CmpCallCard_IsPoli: recCmp.CmpCallCard_IsPoli,
								Lpu_smpid: recCmp.Lpu_smpid,
								CmpCallCard_IsPassSSMP: recCmp.CmpCallCard_IsPassSSMP,
								CmpCallPlaceType_id: recCmp.CmpCallPlaceType_id
							});

							if (rec.raw.CmpCallCard_storDT != null) {
								var deferDate = new Date(rec.raw.CmpCallCard_storDT.date),
									date = new Date(),
									deferTimeValue = Ext.Date.format(deferDate, 'H.i.s'),
									deferDateValue = Ext.Date.format(deferDate, 'd.m.Y');

								if (date < deferDate) {
									CmpCallCard_storDateShow.setVisible(true);
									CmpCallCard_storTimeShow.setVisible(true);
									CmpCallCard_storDateShow.setValue(deferDateValue);
									CmpCallCard_storTimeShow.setValue(deferTimeValue);
								}
							}

							if(typeof recCmp == 'object' && recCmp.CmpCallCard_id && recCmp.EmergencyTeam_id && cntr.armWindow && cntr.armWindow.socket){
								var cmpCallCardParam = {
                                    CmpCallCard_id: recCmp.CmpCallCard_id,
                                    EmergencyTeam_id: recCmp.EmergencyTeam_id,
                                    Comment: data.CmpRejectionReasonName
                                }
								if(cntr.isNmpArm){
									// НМП
									// создадим в объекте переменную, по ней, при сохранении карты, определим отправку сообщения в нод об отмене вызова (внесем в нее параметры для передачи)
									cntr.registrationFailure = cmpCallCardParam;
								}else if(recCmp.LpuBuilding_id){
									// СМП
									Ext.Ajax.request({
	                                    url: '/?c=CmpCallCard4E&m=getSettingsChallengesRequiringTheDecisionOfSeniorDoctor',
	                                    params: {
	                                        LpuBuilding_id: recCmp.LpuBuilding_id
	                                    },
	                                    success: function(response, opts){
	                                        if(response){
	                                            var response_obj = Ext.JSON.decode(response.responseText);
	                                            // нам надо учитывать флаг "Отменяющие вызовы" в структуре МО
	                                            if(response_obj.LpuBuilding_IsCallCancel == 'false'){
	                                                // если в настройках не стоит флаг решения старшего врача
	                                                // создадим в объекте переменную, по ней, при сохранении карты, определим отправку сообщения в нод об отмене вызова
	                                                // за одним и параметры для передачи определим, не собирать повторно
	                                                cntr.registrationFailure = cmpCallCardParam;
	                                            }else{
	                                                cntr.registrationFailure = false;
	                                            }
	                                        }
	                                    }
	                                });
								}
							}
                        };

                        this.close();
                    });
				},
				
				
				doublenewCall: function(rec){
					var data = rec.data;

					// Заполняем только необходимые записи
					cntr.setDefaultValues({
						//CmpCallCard_Comm: data.CmpCallCard_Comm,
						CmpCallCard_Dom: data.CmpCallCard_Dom,
						CmpCallCard_Korp: data.CmpCallCard_Korp,
						CmpCallCard_Etaj: data.CmpCallCard_Etaj,
						CmpCallCard_Kodp: data.CmpCallCard_Kodp,
						CmpCallCard_Kvar: data.CmpCallCard_Kvar,
						CmpCallCard_Podz: data.CmpCallCard_Podz,
						CmpGroupName_id: data.CmpGroupName_id,
						CmpGroup_id: data.CmpGroup_id,
						Sex_id: data.Sex_id,
						CmpReason_Name: data.CmpReason_Name,
						CmpReason_id: data.CmpReason_id,
						KLCity_Name: data.KLCity_Name,
						KLCity_id: data.KLCity_id,
						KLRgn_id: data.KLRgn_id,
						KLStreet_FullName: data.KLStreet_FullName,
						KLStreet_id: data.KLStreet_id,
						KLSubRgn_id: data.KLSubRgn_id,
						KLTown_Name: data.KLTown_Name,
						KLTown_id: data.KLTown_id,
						Town_id: data.KLTown_id,
						//CmpCallCard_Telf: data.CmpCallCard_Telf,
						CmpCallerType_id: data.CmpCallerType_id,
						CmpCallCard_Ktov: data.CmpCallCard_Ktov,
						CmpCallType_Code: 14,
						PersonEvn_id: data.PersonEvn_id,
						Person_Birthday: data.Person_Birthday,
						Person_FIO: data.Person_FIO,
						Person_Firname: data.Person_Firname,
						Person_Secname: data.Person_Secname,
						Person_Surname: data.Person_Surname,
						Person_id: data.Person_id,
						Person_IsUnknown: data.Person_IsUnknown,
						UnformalizedAddressDirectory_Dom: data.UnformalizedAddressDirectory_Dom,
						UnformalizedAddressDirectory_Name: data.UnformalizedAddressDirectory_Name,
						UnformalizedAddressDirectory_id: data.UnformalizedAddressDirectory_id,
						UnformalizedAddressType_id: data.UnformalizedAddressType_id,
						StreetAndUnformalizedAddressDirectory_id: data.StreetAndUnformalizedAddressDirectory_id,
						LpuBuilding_id: data.LpuBuilding_id,
						Lpu_ppdid: data.Lpu_ppdid,
						MedService_id: data.MedService_id,
						CmpCallCard_IsExtra: data.CmpCallCard_IsExtra,
						CmpCallCard_IsPoli: data.CmpCallCard_IsPoli,
						Lpu_smpid: data.Lpu_smpid,
						CmpCallCard_IsPassSSMP: data.CmpCallCard_IsPassSSMP,
						CmpCallPlaceType_id: data.CmpCallPlaceType_id
					});

					if (rec.raw.CmpCallCard_storDT != null) {
						var deferDate = new Date(rec.raw.CmpCallCard_storDT.date),
							date = new Date(),
							deferTimeValue = Ext.Date.format(deferDate, 'H.i.s'),
							deferDateValue = Ext.Date.format(deferDate, 'd.m.Y');

						if (date < deferDate) {
							CmpCallCard_storDateShow.setVisible(true);
							CmpCallCard_storTimeShow.setVisible(true);
							CmpCallCard_storDateShow.setValue(deferDateValue);
							CmpCallCard_storTimeShow.setValue(deferTimeValue);
						}
					}

					if(getRegionNick() == 'ufa') {
						var storePerson = this.storePerson;
						storePerson.removeAll();

						if(rec.get('Person_IsUnknown') == 1) {
							storePerson.getProxy().extraParams = {
								Person_id: rec.get('Person_id')
							};
							storePerson.load({
								callback: function(rec, operation, success) {
									if(success && storePerson.getCount() == 1) {
										var person = storePerson.getAt(0).getData();
										cntr.setPatient(person, true);
									}
								}
							});
						} else {
							storePerson.add({
								Person_id: rec.get('Person_id'),
								PersonFirName_FirName: rec.get('Person_Firname'),
								PersonSecName_SecName: rec.get('Person_Secname'),
								PersonSurName_SurName: rec.get('Person_Surname'),
								Person_Birthday: rec.get('Person_Birthday'),
								Person_Age: rec.get('Person_Age'),
								Sex_id: rec.get('Sex_id')
							});
						}
					}

					// Родительский вызов
                    CmpCallCard_rid.setValue(rec.get('CmpCallCard_id'));
                    CmpCallCard_DayNumberRid.setValue(rec.data.CmpCallCard_Numv).enable();
                    cntr.cmpDublicateFlag = true;
                    selectFirstCardWin.close();
				}.bind(this),
				
				
				informCall: function(rec){
					var data = rec.data;

					// Заполняем только необходимые записи
					cntr.setDefaultValues({
						//CmpCallCard_Comm: data.CmpCallCard_Comm,
						CmpCallCard_Dom: data.CmpCallCard_Dom,
						CmpCallCard_Korp: data.CmpCallCard_Korp,
						CmpCallCard_Etaj: data.CmpCallCard_Etaj,
						CmpCallCard_Kodp: data.CmpCallCard_Kodp,
						CmpCallCard_Kvar: data.CmpCallCard_Kvar,
						CmpCallCard_Podz: data.CmpCallCard_Podz,
						CmpGroupName_id: data.CmpGroupName_id,
						CmpGroup_id: data.CmpGroup_id,
						Sex_id: data.Sex_id,
						CmpReason_Name: data.CmpReason_Name,
						CmpReason_id: data.CmpReason_id,
						KLCity_Name: data.KLCity_Name,
						KLCity_id: data.KLCity_id,
						KLRgn_id: data.KLRgn_id,
						KLStreet_FullName: data.KLStreet_FullName,
						KLStreet_id: data.KLStreet_id,
						KLSubRgn_id: data.KLSubRgn_id,
						KLTown_Name: data.KLTown_Name,
						KLTown_id: data.KLTown_id,
						Town_id: data.KLTown_id,
						//CmpCallCard_Telf: data.CmpCallCard_Telf,
						CmpCallerType_id: data.CmpCallerType_id,
						CmpCallCard_Ktov: data.CmpCallCard_Ktov,
						CmpCallType_Code: 15,
						PersonEvn_id: data.PersonEvn_id,
						Person_Birthday: data.Person_Birthday,
						Person_FIO: data.Person_FIO,
						Person_Firname: data.Person_Firname,
						Person_Secname: data.Person_Secname,
						Person_Surname: data.Person_Surname,
						Person_id: data.Person_id,
						Person_IsUnknown: data.Person_IsUnknown,
						UnformalizedAddressDirectory_Dom: data.UnformalizedAddressDirectory_Dom,
						UnformalizedAddressDirectory_Name: data.UnformalizedAddressDirectory_Name,
						UnformalizedAddressDirectory_id: data.UnformalizedAddressDirectory_id,
						UnformalizedAddressType_id: data.UnformalizedAddressType_id,
						StreetAndUnformalizedAddressDirectory_id: data.StreetAndUnformalizedAddressDirectory_id,
						LpuBuilding_id: data.LpuBuilding_id,
						Lpu_ppdid: data.Lpu_ppdid,
						MedService_id: data.MedService_id,
						CmpCallCard_IsExtra: data.CmpCallCard_IsExtra,
						CmpCallCard_IsPoli: data.CmpCallCard_IsPoli,
						Lpu_smpid: data.Lpu_smpid,
						CmpCallCard_IsPassSSMP: data.CmpCallCard_IsPassSSMP,
						CmpCallPlaceType_id: data.CmpCallPlaceType_id
					});

					// Родительский вызов
                    CmpCallCard_rid.setValue(rec.get('CmpCallCard_id'));
                    CmpCallCard_DayNumberRid.setValue(rec.data.CmpCallCard_Numv).enable();

					if (rec.raw.CmpCallCard_storDT != null) {
						var deferDate = new Date(rec.raw.CmpCallCard_storDT.date),
							date = new Date(),
							deferTimeValue = Ext.Date.format(deferDate, 'H.i.s'),
							deferDateValue = Ext.Date.format(deferDate, 'd.m.Y');

						if (date < deferDate) {
							CmpCallCard_storDateShow.setVisible(true);
							CmpCallCard_storTimeShow.setVisible(true);
							CmpCallCard_storDateShow.setValue(deferDateValue);
							CmpCallCard_storTimeShow.setValue(deferTimeValue);
						}
					}

					// после закрытия окна выбора повторного вызова, нужно выставить фокус
					if(!getRegionNick().inlist(['ufa', 'krym', 'kz']))
						cntr.ufa_changeFocusCrutches();
					else
                        CmpReason_Name.focus();

                    cntr.cmpDublicateFlag = true;
				}.bind(this),
				doubleCall: function(rec){
					var data = rec.data,
						ridBtn = baseForm.owner.query('[name=cmpcallcard_ridBtn]')[0];

					if (getRegionNick() == 'kareliya') {
						ridBtn.setVisible(true);
					}

					// Заполняем только необходимые записи
					cntr.setDefaultValues({
						StreetAndUnformalizedAddressDirectory_id: data.StreetAndUnformalizedAddressDirectory_id,
						//CmpCallCard_Comm: data.CmpCallCard_Comm,
						CmpCallCard_Dom: data.CmpCallCard_Dom,
						CmpCallCard_Etaj: data.CmpCallCard_Etaj,
						CmpCallCard_Kodp: data.CmpCallCard_Kodp,
						CmpCallCard_Kvar: data.CmpCallCard_Kvar,
						CmpCallCard_Podz: data.CmpCallCard_Podz,
						CmpGroupName_id: data.CmpGroupName_id,
						CmpGroup_id: data.CmpGroup_id,
						Sex_id: data.Sex_id,
						CmpReason_Name: data.CmpReason_Name,
						CmpReason_id: data.CmpReason_id,
						KLCity_Name: data.KLCity_Name,
						KLCity_id: data.KLCity_id,
						KLRgn_id: data.KLRgn_id,
						KLStreet_FullName: data.KLStreet_FullName,
						KLStreet_id: data.KLStreet_id,
						KLSubRgn_id: data.KLSubRgn_id,
						KLTown_Name: data.KLTown_Name,
						KLTown_id: data.KLTown_id,
						//CmpCallCard_Telf: data.CmpCallCard_Telf,
						PersonEvn_id: data.PersonEvn_id,
						Person_Birthday: data.Person_Birthday,
						Person_FIO: data.Person_FIO,
						Person_Firname: data.Person_Firname,
						Person_Secname: data.Person_Secname,
						Person_Surname: data.Person_Surname,
						Person_id: data.Person_id,
						Person_IsUnknown: data.Person_IsUnknown,
						UnformalizedAddressDirectory_Dom: data.UnformalizedAddressDirectory_Dom,
						UnformalizedAddressDirectory_Name: data.UnformalizedAddressDirectory_Name,
						UnformalizedAddressDirectory_id: data.UnformalizedAddressDirectory_id,
						UnformalizedAddressType_id: data.UnformalizedAddressType_id,
						LpuBuilding_id: data.LpuBuilding_id,
						Lpu_ppdid: data.Lpu_ppdid,
						MedService_id: data.MedService_id,
						CmpCallCard_IsExtra: data.CmpCallCard_IsExtra,
						CmpCallCard_IsPoli: data.CmpCallCard_IsPoli,
						Lpu_smpid: data.Lpu_smpid,
						CmpCallCard_IsPassSSMP: data.CmpCallCard_IsPassSSMP,
						CmpCallType_Code: 2,
						CmpCallPlaceType_id: data.CmpCallPlaceType_id
					});

					//Расчет возраста
					cntr.convertPersonBirthdayToAge();

					// Родительский вызов
                    CmpCallCard_rid.setValue(rec.get('CmpCallCard_id'));
                    CmpCallCard_DayNumberRid.setValue(data.CmpCallCard_Numv).enable();

					if (rec.raw.CmpCallCard_storDT != null) {
						var deferDate = new Date(rec.raw.CmpCallCard_storDT.date),
							date = new Date(),
							deferTimeValue = Ext.Date.format(deferDate, 'H.i.s'),
							deferDateValue = Ext.Date.format(deferDate, 'd.m.Y');

						if (date < deferDate) {
							CmpCallCard_storDateShow.setVisible(true);
							CmpCallCard_storTimeShow.setVisible(true);
							CmpCallCard_storDateShow.setValue(deferDateValue);
							CmpCallCard_storTimeShow.setValue(deferTimeValue);
						}
					}

					//Кто вызывает

					if(data.CmpCallerType_id)
                        CmpCallerType_id.setValue(rec.get('CmpCallerType_id'));
					else
                        CmpCallerType_id.setValue(rec.get('CmpCallCard_Ktov'));

					//Вид вызова
					if(rec.get('CmpCallCard_IsExtra')){
                        CmpCallCard_IsExtra.setValue(rec.get('CmpCallCard_IsExtra'))
					}

                    cntr.cmpDublicateFlag = true;

					Ext.defer(function(){ selectFirstCardWin.close(); }.bind(this), 200);
					// после закрытия окна выбора повторного вызова, нужно выставить фокус
					if(!getRegionNick().inlist(['ufa', 'krym', 'kz']))
						cntr.ufa_changeFocusCrutches();
					else
                        CmpReason_Name.focus();

				}.bind(this),
				helpCall: function(rec){
                    CmpCallCard_rid.setValue(rec.get('CmpCallCard_id'));
					Ext.defer(function() {
                        selectFirstCardWin.close();
					}.bind(this), 200);
					// после закрытия окна выбора повторного вызова, нужно выставить фокус
					if(!getRegionNick().inlist(['ufa', 'krym', 'kz']))
						cntr.ufa_changeFocusCrutches();
					else
                        CmpReason_Name.focus();

                    cntr.cmpDublicateFlag = true;

				}.bind(this),
				boostCall: function(rec){
					Ext.Ajax.request({
						url: '/?c=CmpCallCard4E&m=setCmpCallCardBoostTime',
						params: {
							CmpCallCard_id: rec.get('CmpCallCard_id')
						},
						callback: function(opt, success, response) {
							if (success){
								var response_obj = Ext.JSON.decode(response.responseText);

								if (response_obj.success) {
									Ext.Msg.alert('Сохранение', 'Карта вызова отмечена для ускорения');
									cntr.armWindow.socket.emit('changeCmpCallCard', response_obj.CmpCallCard_id, 'boostCall', function(data){
										log('NODE emit boostCall');
									});
                                    selectFirstCardWin.close();
								} else {
									var error_msg = (response_obj.Error_Msg)?response_obj.Error_Msg:'Ошибка сохранения карты вызова';
									Ext.Msg.alert('Ошибка', error_msg);
                                    selectFirstCardWin.close();
								}

                                cntr.cmpDublicateFlag = true;
								if(!getRegionNick().inlist(['ufa', 'krym', 'kz']))
									cntr.ufa_changeFocusCrutches();
								else
                                    CmpReason_Name.focus();
							}
						}.bind(this)
					})
				}.bind(this),
				feelBadlyCall: function(rec){
					// Состояние ухудшилось
					if( !rec.data ) return;

					var recCmp = rec.getData(),
						deteriorationSmpCallCardWindow = Ext.ComponentQuery.query('window[refId=swDeteriorationSmpCallCardWindow]')[0];

					if( deteriorationSmpCallCardWindow && deteriorationSmpCallCardWindow.isVisible() ) return;
					deteriorationSmpCallCardWindow.show({
						recCmp: recCmp
					});

					deteriorationSmpCallCardWindow.on('selectCmpReason', function(data, recCmp) {
						recCmp.CmpReason_id = data.CmpReason_id; // подставим значение поля «Повод» формы «Ухудшение состояния»;
						if( !deteriorationSmpCallCardWindow.isVisible() ) return;
						recCmp.CmpCallCard_rid = recCmp.CmpCallCard_id;
						var params = {
							CmpCallCard_rid : recCmp.CmpCallCard_id,
							CmpCallCard_DayNumberRid : recCmp.CmpCallCard_Numv
						}

						cntr.changeFormDependingResponse(params, 14); // 14 - дублирующий
						delete(recCmp.CmpCallCard_id);
						delete(recCmp.CmpCallCard_prmDT);
						delete(recCmp.CmpCallCard_prmDate);
						delete(recCmp.CmpCallCardStatusType_id);

						cntr.setDefaultValues(recCmp);

						allFields.CmpCallCard_IsDeterior.setValue(2);

						if(recCmp.CmpCallCard_IsExtra != undefined && recCmp.CmpCallCard_IsExtra == 2){
                            CmpCallCard_IsExtra.setValue(recCmp.CmpCallCard_IsExtra);
							if(!recCmp.LpuBuilding_id && recCmp.MedService_id){
								Ext.defer(function(){ allFields.selectNmpCombo.setValue(recCmp.MedService_id); },700);
							}
						}

						cntr.deteriorationField = {
							EmergencyTeam_id: (recCmp.EmergencyTeam_id) ? recCmp.EmergencyTeam_id : ''
						};

						if (rec.raw.CmpCallCard_storDT != null) {
							var deferDate = new Date(rec.raw.CmpCallCard_storDT.date),
								date = new Date(),
								deferTimeValue = Ext.Date.format(deferDate, 'H.i.s'),
								deferDateValue = Ext.Date.format(deferDate, 'd.m.Y');

							if (date < deferDate) {
								CmpCallCard_storDateShow.setVisible(true);
								CmpCallCard_storTimeShow.setVisible(true);
								CmpCallCard_storDateShow.setValue(deferDateValue);
								CmpCallCard_storTimeShow.setValue(deferTimeValue);
							}
						}

						deteriorationSmpCallCardWindow.close();
                        cntr.cmpDublicateFlag = true;
						cntr.checkUrgencyAndProfile();
					});
				},
				thirdPersonCall: function(rec){
					var data = rec.data;

					cntr.setDefaultValues({
						CmpCallCard_Comm: data.CmpCallCard_Comm,
						CmpCallCard_Dom: data.CmpCallCard_Dom,
						CmpCallCard_Korp: data.CmpCallCard_Korp,
						CmpCallCard_Etaj: data.CmpCallCard_Etaj,
						CmpCallCard_Kodp: data.CmpCallCard_Kodp,
						CmpCallCard_Kvar: data.CmpCallCard_Kvar,
						CmpCallCard_Podz: data.CmpCallCard_Podz,
						CmpGroupName_id: data.CmpGroupName_id,
						CmpGroup_id: data.CmpGroup_id,
						KLCity_Name: data.KLCity_Name,
						KLCity_id: data.KLCity_id,
						KLRgn_id: data.KLRgn_id,
						KLStreet_FullName: data.KLStreet_FullName,
						KLStreet_id: data.KLStreet_id,
						KLSubRgn_id: data.KLSubRgn_id,
						KLTown_Name: data.KLTown_Name,
						KLTown_id: data.KLTown_id,
						Town_id: data.KLTown_id,
						CmpCallCard_Telf: data.CmpCallCard_Telf,
						CmpCallerType_id: data.CmpCallerType_id,
						CmpCallCard_Ktov: data.CmpCallCard_Ktov,
						CmpCallType_Code: 1,
						UnformalizedAddressDirectory_Dom: data.UnformalizedAddressDirectory_Dom,
						UnformalizedAddressDirectory_Name: data.UnformalizedAddressDirectory_Name,
						UnformalizedAddressDirectory_id: data.UnformalizedAddressDirectory_id,
						UnformalizedAddressType_id: data.UnformalizedAddressType_id,
						StreetAndUnformalizedAddressDirectory_id: data.StreetAndUnformalizedAddressDirectory_id,
						LpuBuilding_id: data.LpuBuilding_id,
						Lpu_ppdid: data.Lpu_ppdid,
						MedService_id: data.MedService_id,
						CmpCallCard_IsExtra: data.CmpCallCard_IsExtra,
						CmpCallCard_IsPoli: data.CmpCallCard_IsPoli,
						Lpu_smpid: data.Lpu_smpid,
						CmpCallCard_IsPassSSMP: data.CmpCallCard_IsPassSSMP,
						CmpCallPlaceType_id: data.CmpCallPlaceType_id
					});

					var formFields = cntr.baseForm.getAllFields();

					formFields.CmpCallCard_DayNumberRid.setValue('');
					formFields.CmpCallCard_DayNumberRid.setDisabled(true);
					formFields.CmpReason_id.setValue('');
					formFields.CmpReason_Name.setValue('');
					formFields.CmpCallCard_IsActiveCall.setValue('');
					formFields.CmpCallCard_rid.setValue('');
					formFields.CmpCallCard_rid.setVisible(false);
					formFields.Sex_id.setValue('');

					// после закрытия окна выбора повторного вызова, нужно выставить фокус
					if(!getRegionNick().inlist(['ufa', 'krym', 'kz']))
						cntr.ufa_changeFocusCrutches();
					else
						CmpReason_Name.focus();

					cntr.cmpDublicateFlag = true;
				}.bind(this)
			}
		}).show();

        /*
		// подстановка данных в фильтры
		var win = this.armWindow.selectFirstCardWin,
			form = win.down('form').getForm(),
			baseForm = cntr.baseForm,
			nameField = form.findField('filterByName'),
			famField = form.findField('filterByFamily'),
			secNameField = form.findField('filterBySecName'),
			addressField = form.findField('filterByAddress'),
			cityCombo = cntr.baseForm.findField('dCityCombo'),
			recCityCombo = cityCombo.getStore().findRecord('Town_id', cityCombo.getValue()),
			streetsCombo = cntr.baseForm.findField('dStreetsCombo'),
			recStreetCombo = streetsCombo.getStore().findRecord('StreetAndUnformalizedAddressDirectory_id', streetsCombo.getValue()),
			domField = cntr.baseForm.findField('CmpCallCard_Dom'),
			korpField = cntr.baseForm.findField('CmpCallCard_Korp'),
			kvarField = cntr.baseForm.findField('CmpCallCard_Kvar'),
			address_name = '',
			filterCmpCalls = [];

		win.swSelectFirstSmpCallCard.getStore().clearFilter();
		addressField.getStore().clearFilter();

		if(checkBy != 'address'){
			nameField.setValue(baseForm.findField('Person_Firname').getValue());
			famField.setValue(baseForm.findField('Person_Surname').getValue());
			secNameField.setValue(baseForm.findField('Person_Secname').getValue());
		}
		if(checkBy != 'fio'){
			if(recCityCombo){
				switch(recCityCombo.get('KLAreaLevel_id')){
					case 3:
						address_name += 'г.' + recCityCombo.get('Town_Name')
						break;
					case 4:
						address_name += recCityCombo.get('Town_Name') + ' ' + recCityCombo.get('Socr_Nick');
						break;
				}
			}
			if(recStreetCombo && (recStreetCombo.get('UnformalizedAddressDirectory_id') == 0)){
				address_name += ', ' + recStreetCombo.get('StreetAndUnformalizedAddressDirectory_Name') + ' ' + recStreetCombo.get('Socr_Nick');
			}

			address_name += (domField && domField.getValue()) ? ', д.' + domField.getValue() : '';
			address_name += (korpField && korpField.getValue()) ? ', к.' + korpField.getValue() : '';
			address_name += (kvarField && kvarField.getValue()) ? ', кв.' + kvarField.getValue() : '';
			addressField.setValue(address_name);
		}

		if(data && data.length){
			data.forEach(function(el){
				filterCmpCalls.push(el.CallCard_id);
			});
			var filterFn = {
				filterFn: function(item){
					return item.get('CmpCallCard_id').inlist(filterCmpCalls);
				}
			};

			win.swSelectFirstSmpCallCard.getStore().addFilter(filterFn);

			addressField.getStore().addFilter(filterFn);
		}
		*/

	},
	
	refreshFieldsVisibility: function(fieldNames) {
		var cntr = this;
		var baseForm = this.baseForm;

		var CmpCallTypeField = baseForm.findField('CmpCallType_id'),
			idx = CmpCallTypeField.getStore().find('CmpCallType_id', CmpCallTypeField.getValue()),
			CallTypeCodesWithoutLpuBuilding = [6, 15, 16], // Коды типов вызовов: Консультативный, Справка, Абонент отключился
			codeCallType = (idx != -1) ? CmpCallTypeField.getStore().getAt(idx).get('CmpCallType_Code') : '',
			CodeWithoutLpuBuilding = codeCallType.inlist(CallTypeCodesWithoutLpuBuilding);

		if (Ext.isString(fieldNames)) fieldNames = [fieldNames];
		baseForm.getFields().each(function(field){
			if (!Ext.isEmpty(fieldNames) && !field.getName().inlist(fieldNames)) return;

			var enable = null;
			var visible = null;
			var reset = true;

			switch(field.getName()) {
				case 'secondStreetCombo':
					var crossRoadsMode = (!Ext.isEmpty(field.getValue()));

					baseForm.findField('secondStreetCombo').setVisible(crossRoadsMode);
					baseForm.findField('CmpCallCard_Dom').setVisible(!crossRoadsMode);

					baseForm.findField('CmpCallCard_Korp').setVisible(!crossRoadsMode);
					baseForm.findField('CmpCallCard_Kvar').setVisible(!crossRoadsMode);
					baseForm.findField('CmpCallCard_Podz').setVisible(!crossRoadsMode);

					baseForm.findField('CmpCallCard_Etaj').setDisabled(crossRoadsMode);
					baseForm.findField('CmpCallCard_Kodp').setDisabled(crossRoadsMode);

					break;
				case 'CmpCallCard_IsPoli':
                    enable = (baseForm.findField('CmpCallCard_IsExtra').getValue() == 2);
					break;
				case 'lpuLocalCombo':
					enable = (
						cntr.isAutoSelectNmpSmp == true || (
							baseForm.findField('CmpCallCard_IsExtra').getValue() == 2 &&
							//Блокируем поле для НМП кроме ЕДЦ
							!(
								cntr.isNmpArm &&
								getGlobalOptions().SmpUnitType_Code &&
								getGlobalOptions().SmpUnitType_Code != 4
							)
							//baseForm.findField('CmpCallCard_IsPassSSMP').getValue() == false &&
							//baseForm.findField('CmpCallCard_IsPoli').getValue() == false &&
							//Ext.isEmpty(baseForm.findField('LpuBuilding_id').getValue())
						)

					);
					reset = false;
					break;
				case 'selectNmpCombo':
					enable = (
						cntr.isAutoSelectNmpSmp == true || (
							//baseForm.findField('CmpCallCard_IsExtra').getValue() == 2 &&
							//baseForm.findField('CmpCallCard_IsPassSSMP').getValue() == false &&
							//baseForm.findField('CmpCallCard_IsPoli').getValue() == false &&
							!Ext.isEmpty(baseForm.findField('lpuLocalCombo').getValue())
						)
					);

					field.allowBlank = !(enable && cntr.isNmpArm);
					break;
				case 'LpuBuilding_id':
					enable = true;
					/*
					enable = (
						//cntr.isAutoSelectNmpSmp == true ||
						baseForm.findField('CmpCallCard_IsExtra').getValue() == 1 || (
							baseForm.findField('CmpCallCard_IsExtra').getValue() == 2 &&
							baseForm.findField('CmpCallCard_IsPoli').getValue() == false &&
							Ext.isEmpty(baseForm.findField('lpuLocalCombo').getValue())// &&
							//cntr.isAutoSelectNmpSmp != true
						)
					);
					*/
					field.allowBlank = (CodeWithoutLpuBuilding || !(baseForm.findField('CmpCallCard_IsExtra').getValue() == 1 &&
						baseForm.findField('CmpCallCard_IsPassSSMP').getValue() == false));
						
					break;
				case 'CmpCallCard_deferred':{
					if(cntr.isNmpArm){
						enable = (
							!Ext.isEmpty(baseForm.findField('lpuLocalCombo').getValue()) &&
							!Ext.isEmpty(baseForm.findField('selectNmpCombo').getValue()) &&
							codeCallType.inlist([1,2,3,19])
						);
					}
					else{
						enable = (
							!Ext.isEmpty(baseForm.findField('LpuBuilding_id').getValue()) &&
							codeCallType.inlist([1,2,3,19])
						);
					}
					break;
				}
				case 'CmpCallCard_storDate':
					enable = (baseForm.findField('CmpCallCard_deferred').getValue() == true)
					field.allowBlank = !(baseForm.findField('CmpCallCard_deferred').getValue() == true);
					visible = (baseForm.findField('CmpCallCard_deferred').getValue() == true);
					break;
				case 'CmpCallCard_storTime':
					enable = (baseForm.findField('CmpCallCard_deferred').getValue() == true)
					field.allowBlank = !(baseForm.findField('CmpCallCard_deferred').getValue() == true);
					visible = (baseForm.findField('CmpCallCard_deferred').getValue() == true);
					break;
				case 'CmpCallCard_defCom':
					enable = (baseForm.findField('CmpCallCard_deferred').getValue() == true)
					visible = (baseForm.findField('CmpCallCard_deferred').getValue() == true);
					break;
				case 'Lpu_smpid':
					enable = (baseForm.findField('CmpCallCard_IsPassSSMP').getValue());
					visible = (baseForm.findField('CmpCallCard_IsPassSSMP').getValue());
					field.allowBlank = !(baseForm.findField('CmpCallCard_IsPassSSMP').getValue());
					break;
				case 'UnformalizedAddressDirectory_wid':
					var CmpReasonField = baseForm.findField('CmpReason_id'),
						rec = CmpReasonField.findRecord('CmpReason_id',CmpReasonField.getValue());
						if(rec){
							switch(getRegionNick()){
								case 'perm':
								case 'krym':
								case 'kz':
									enable = rec.get('CmpReason_Code').substring(0,2).inlist(['40','41','42','43']);
									visible = rec.get('CmpReason_Code').substring(0,2).inlist(['40','41','42','43']);
									field.allowBlank = !rec.get('CmpReason_Code').substring(0,2).inlist(['41','42','43']);
									break;
								case 'ufa':

									var arrCodesVisible = ['СМ216','СЛ194','СМ209','СМ210','СМ211','СМ212','СМ213','СМ214','СМ215','ПР1','ПР2','ПР3'],
									 	arrCodesAllowBlank = ['СМ216','СЛ194','СМ209','СМ210','СМ211','СМ212','СМ213','СМ214','СМ215'];

									enable = rec.get('CmpReason_Code').inlist(arrCodesVisible);
									visible = rec.get('CmpReason_Code').inlist(arrCodesVisible);
									//field.allowBlank = !rec.get('CmpReason_Code').inlist(arrCodesAllowBlank);

									break;

							}

						}

						break;
			}

			if (enable === true) {
				field.enable();
			}
			if (enable === false) {
				field.disable();
			}
			if (visible === true) {
				field.show();
			}
			if (visible === false) {
				field.hide();
			}
			if (reset && (enable === false || visible === false)) {
				field.reset();
			}
			field.validate();
		});
	},
	checkUrgencyAndProfile: function() {

		var getAge = function (dateString) {
			var today = new Date();

			if (dateString.length<8) {
				return false;
			}


			var datesFormats = ['d.m.Y','d.m.y','d,m,Y','d,m,y','d/m/Y','d/m/y'],
			birthDate;

			for (var i = 0; i<datesFormats.length && !birthDate; i++) {
				birthDate = Ext.Date.parse(dateString,datesFormats[i]);
			}
			if (!birthDate) {
				return false;
			}
//			var birthDate = Ext.Date.parse(dateString,'d.m.Y') || Ext.Date.parse(dateString,'d.m.y') || ;
			var age = today.getFullYear() - birthDate.getFullYear();
			var m = today.getMonth() - birthDate.getMonth();
			if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
				age--;
			}
			return (age<0)?(100 - age):age;
		};

		var cntr = this,
			wnd = this.armWindow,
			baseForm = this.baseForm,
			reason = baseForm.findField('CmpReason_id').getValue(),
			//reasonName = baseForm.findField('CmpReason_Name').getValue(),
			callplace_field = baseForm.findField('CmpCallPlaceType_id'),
			callplace_value = callplace_field.getValue(),
			callplace_rawValue = callplace_field.getRawValue(),
			ageVal = baseForm.findField('Person_Birthday_YearAge').getValue(),
			person_id = baseForm.findField('Person_id').getValue(),
			person_age = baseForm.findField('Person_Age').getValue(),
			age = false,
			urgency_label,
			profile_label,
			//cmpCallCardIsNMPField = baseForm.findField('CmpCallCard_IsNMP'),
			cmpCallCardIsNMPField = baseForm.findField('CmpCallCard_IsExtra'),
			lpuLocalCombo = baseForm.findField('lpuLocalCombo'),
			smpUnitsCombo = baseForm.findField('LpuBuilding_id'),
			cmpCallCardStatusType_id = baseForm.findField('CmpCallCardStatusType_id'),
			callTypeCombo = baseForm.findField('CmpCallType_id'),
			flagOrArrayCodesIsNMP = false,
			ageUnit = baseForm.findField('ageUnit').getValue(),
			isUfa = getRegionNick() == 'ufa';

		//если не указан возраст и повод
		if(getRegionNick() == 'ufa' && (Ext.isEmpty(ageVal) || Ext.isEmpty(ageUnit) || Ext.isEmpty(reason))) {
			return;
		}

		//если в режиме сохранения то пропускаем проверку
		if(cntr.isSaveMode) return false;

		var currentCallTypeRec = callTypeCombo.getStore().findRecord('CmpCallType_id',callTypeCombo.getValue());
		if(!currentCallTypeRec ){
			return false;
		}

		if (person_id != '0') {
			age = person_age;
		} else if ( ageVal && (ageVal.toString().length!=0) ) {
			if (/^\d{1,3}$/.test(ageVal)) {
				age = ageVal;
			} else {
				age = getAge(ageVal);
			}
		}

		if (this.baseForm.findField('ageUnit').getValue() != 1) {
			age = 0;
		}

		//urgency_label = Ext.getCmp('DispatchCallWorkPlace_UrgencyLabel').setText('СР:');
		urgency_label = Ext.getCmp('DispatchCallWorkPlace_UrgencyLabel');
		//profile_label = Ext.getCmp('DispatchCallWorkPlace_ProfileLabel').setText('ПР:');
		profile_label = Ext.getCmp('DispatchCallWorkPlace_ProfileLabel');

		if ( (isUfa || !this.saveAndContinue) && (!reason || reason=='0' || (callplace_value===null) || age===false || callplace_value == callplace_rawValue)) {

			cmpCallCardIsNMPField.setValue(cntr.isNmpArm ? 2 : 1);
			/*
			lpuLocalCombo.setDisabled(true);
			if (!getRegionNick().inlist(['perm', 'ekb'])) {
				smpUnitsCombo.setDisabled(false);
			}
			*/
			return;
		}

		if ( (isUfa || !this.saveAndContinue) && reason) {
			//if (!getRegionNick().inlist(['krym']))
			//if (!getRegionNick().inlist(['perm', 'ekb']))
			//{
				var url = '/?c=CmpCallCard4E&m=getCallUrgencyAndProfile';
				cntr.abortRequestByUrl(url);
				
				Ext.Ajax.request({
					url: url,
					 // - autoAbort : true, не подгружаются город и улица подумать
					callback: function(opt, success, response){

						if ( success )
						{
							var response_obj = Ext.JSON.decode(response.responseText),
								type_service_reason = response_obj.CmpCallCardAcceptor_id;

							if (response_obj.Error_Msg)
							{
								Ext.Msg.alert('Ошибка', response_obj.Error_Msg);
							}
							else
							{
								//запомним вид вызова описанный в логике бригады
								if (type_service_reason) {
									if(type_service_reason == 7) {
										type_service_reason = cntr.isNmpArm ? 1 : 2;
									}
									cntr.CmpCallCard_IsExtra = type_service_reason;
									this.setEnabledNmpSmpCombo(type_service_reason);
								}

								if (!getRegionNick().inlist(['krym'])){
									urgency_label.setValue(response_obj.CmpUrgencyAndProfileStandart_Urgency); //не используется, приводит к ошибке
								};

								profile_label.setValue(response_obj.EmergencyTeamSpec_Code||'?');
							}
						}

					}.bind(this),
					params:{
						CmpReason_id:reason,
						Person_Age:age,
						CmpCallPlaceType_id:callplace_value
					}
				});
				/*
			}
			else
			{
				//определяем здесь CmpReason_Code
				var reasonCombo = baseForm.findField('CmpReason_id'),
					selReasonRec = reasonCombo.getSelectedRecord();
				//console.warn('selReasonRec', selReasonRec, selReasonRec.get('CmpReason_Code'))
				//незадокументированное свойство комбобокса lastSelection
				if (selReasonRec) {
					var cmpFieldReasonCode = selReasonRec.get('CmpReason_Code');
					flagOrArrayCodesIsNMP = cmpFieldReasonCode.inlist(['04Г', '04Д', '09Я', '11Л', '11Я', '12Г', '12К', '12Р', '12У', '12Э', '12Я', '13Л', '13М', '15Н', '17А', '13С', '40Ц']);
				}
				this.setEnabledNmpSmpCombo(flagOrArrayCodesIsNMP);

			}
			*/
		}
	},
	//«Функция определения службы НМП для обслуживания вызова
	getNmpMedService: function() {
		var cntr = this;
		var baseForm = this.baseForm;

		var nmpCombo = baseForm.findField('selectNmpCombo');
		var smpCombo = baseForm.findField('LpuBuilding_id');
		var lpuLocalCombo = baseForm.findField('lpuLocalCombo');
		var callTypeCombo = baseForm.findField('CmpCallType_id');
		var cityCombo = baseForm.findField('dCityCombo');
		var KLArea_id;

		var streetCombo = baseForm.findField('dStreetsCombo');
		var streetRec = streetCombo.bigStore.findRecord('StreetAndUnformalizedAddressDirectory_id', streetsCombo.getValue());

		var isExtra = baseForm.findField('CmpCallCard_IsExtra').getValue();
		var age = baseForm.findField('Person_Birthday_YearAge').getValue();
		var prmDate = baseForm.findField('CmpCallCard_prmDate').getRawValue(),
			prmTime = baseForm.findField('CmpCallCard_prmTime').getRawValue(),
			CmpCallCard_prmDT = Ext.Date.parse(prmDate + " " + prmTime,'d.m.Y H:i:s');

		if(!Ext.isDate(CmpCallCard_prmDT)){
			return;
		}

	//	if(this.saveAndContinue) return;

		if (baseForm.findField('ageUnit').getValue() != 1) {
			age = 0;
		}

		// Данные выбранного города/наспункта
		if (typeof cityCombo.store.proxy.reader.jsonData !== 'undefined' && cityCombo.store.getAt(0)){
			var city = cityCombo.store.getAt(0).data;
			KLArea_id = city.Town_id;
		}
		if( !KLArea_id ) return false;
		var params = {
			KLStreet_id: streetRec?streetRec.get('KLStreet_id'):null,
			CmpCallCard_Dom: baseForm.findField('CmpCallCard_Dom').getValue(),
			CmpCallCard_Korp: baseForm.findField('CmpCallCard_Korp').getValue(),
			CmpCallCard_prmDate: prmDate,
			CmpCallCard_prmTime: prmTime,
			Person_Age: age,
			KLArea_id: ( KLArea_id ) ? KLArea_id : null,
		};

		var primaryCallType_Code = 1;
		if(getRegionNick() == 'krym'){
			primaryCallType_Code = 0;
		}
		var currentCallTypeRec = callTypeCombo.getStore().findRecord('CmpCallType_id',callTypeCombo.getValue());
		if(!currentCallTypeRec || currentCallTypeRec.data.CmpCallType_Code != primaryCallType_Code ){
			return false;
		}
		if (isExtra != 2 ||
			Ext.isEmpty(params.KLStreet_id) ||
			Ext.isEmpty(params.CmpCallCard_Dom) ||
			Ext.isEmpty(params.CmpCallCard_prmDate) ||
			Ext.isEmpty(params.CmpCallCard_prmTime) ||
			Ext.isEmpty(params.Person_Age) ||
			(
				cntr.isNmpArm &&
				getGlobalOptions().SmpUnitType_Code &&
				getGlobalOptions().SmpUnitType_Code != 4
			)
		) {
			return false;
		}

		var loadMask = new Ext.LoadMask(cntr.getMap(), {msg: "Определение службы НМП..."});
		loadMask.show();

		Ext.Ajax.request({
			url: '/?c=CmpCallCard4E&m=getNmpMedService',
			params: params,
			success: function(response) {
				loadMask.hide();
				var obj = Ext.decode(response.responseText);

				if (!obj.success) {
					if (!Ext.isEmpty(obj.Error_Msg) && !getGlobalOptions().curARMType.inlist(["dispnmp","dispdirnmp", "dispcallnmp"])) {
						Ext.Msg.alert('Ошибка', obj.Error_Msg);
					} else {
						Ext.Msg.alert('Ошибка', 'Ошибка при определении службы НМП');
					}
				} else {
					if (!Ext.isEmpty(obj.Alert_Msg)) {

                        cntr.showYellowMsg(obj.Alert_Msg, 3000);
					}
					if(obj.Lpu_id){
						lpuLocalCombo.getStore().clearFilter();
						lpuLocalCombo.select(parseInt(obj.Lpu_id));
					}
					else{
						lpuLocalCombo.setValue(null);
					}

					if (!Ext.isEmpty(obj.MedService_id)) {
						smpCombo.setValue(null);
						nmpCombo.getStore().getProxy().extraParams = {
							'Lpu_ppdid': obj.Lpu_id,
							'isClose': 1
						};
						nmpCombo.getStore().load();
						nmpCombo.setValue(Number(obj.MedService_id));
						nmpCombo.enable();
					} else {
						nmpCombo.setValue(null);
						nmpCombo.disable();
						if(!cntr.isNmpArm){
							cntr.changeLpuBuildingByFormAddress();
						}

					}
					
					cntr.isAutoSelectNmpSmp = true;

					//nmpCombo.enable();
					//smpCombo.enable();
					lpuLocalCombo.enable();
					cntr.setAllowBlankDependingCallType();

				}
			},
			failure: function() {
				loadMask.hide();
				sw.swMsg.alert('Ошибка', 'Ошибка при определении службы НМП');
			}
		});

		return true;
	},

    showYellowMsg: function(msg, delay){
        var div = document.createElement('div');

            div.style.width='300px';
            div.style.height='50px';
            div.style.background='#edcd4b';
            div.style.border='solid 2px #efefb3';
            div.style.position='absolute';
            div.style.padding='10px';
            div.style.zIndex='99999';
            div.innerHTML = msg;
            div.style.right = 0;
            div.style.bottom = '50px';
            document.body.appendChild(div);

            setTimeout(function(){
                div.parentNode.removeChild(div);
            }, delay);
    },

	setEnabledNmpSmpCombo: function(type_service_reason){
		var cntr = this,
			baseForm = this.baseForm,
			lpuLocalCombo = baseForm.findField('lpuLocalCombo'),
			cmpReasonIsNmp = baseForm.findField('Reason_isNMP'),
			smpUnitsCombo = baseForm.findField('LpuBuilding_id'),
			lpuSmp = baseForm.findField('Lpu_smpid'),
			nmpCombo = baseForm.findField('selectNmpCombo'),
			cmpCallType = baseForm.findField('CmpCallType_id'),
			cmpCallCardIsPoli = baseForm.findField('CmpCallCard_IsPoli'),
			cmpCallCardIsNMPField = baseForm.findField('CmpCallCard_IsExtra'),
			isExtraValue = cntr.isNmpArm ? 2 : 1,
			CmpCallCard_rid = baseForm.findField('CmpCallCard_rid');
			//cmpCallCardIsNMPField = baseForm.findField('CmpCallCard_IsNMP');

		if(type_service_reason){
			type_service_reason = parseInt(type_service_reason);
			switch(type_service_reason){
				case 1:
				case 2:
					isExtraValue = type_service_reason;
					break;
				case 4:
				case 5:
					isExtraValue = type_service_reason-1;
					break;
			}

			var isExtraRec = cmpCallCardIsNMPField.findRecord('CmpCallTypeIsExtraType_id',isExtraValue);
			if(isExtraRec){
				cmpCallCardIsNMPField.select(isExtraRec)
			}

		}else{
			isExtraValue = cmpCallCardIsNMPField.getValue()
		}

		if(this.isNmpArm){
			if(cmpCallCardIsNMPField.getValue() != 1){
				cmpCallType.enable();
				//cmpCallType.setValue(2);
				//Ставим свое МО для НМП кроме ЕДЦ
				if(getGlobalOptions().SmpUnitType_Code && getGlobalOptions().SmpUnitType_Code != 4){
					cntr.setCurrentPPDLpu();
				}else{
					lpuLocalCombo.enable();
					if(!nmpCombo.getValue())
						nmpCombo.disable();
				}

				lpuLocalCombo.allowBlank = false;
				lpuLocalCombo.validate();
			}
			else{
				lpuLocalCombo.reset();
				nmpCombo.reset();
				nmpCombo.disable();
				lpuLocalCombo.disable();

				if(cntr.LpuBuildingOptions && cntr.LpuBuildingOptions.LpuBuilding_eid) {
					lpuSmp.setValue(parseInt(cntr.LpuBuildingOptions.Lpu_eid));
					smpUnitsCombo.setValue(parseInt(cntr.LpuBuildingOptions.LpuBuilding_eid));
					cntr.getCmpCallCardNumber(parseInt(cntr.LpuBuildingOptions.Lpu_eid));
				}else{
					if(CmpCallCard_rid.getValue() > 0 && baseForm.findField('CmpCallCard_IsDeterior').getValue() == 2){
						//cmpCallType.setValue(14);
						//дублирующий
						cmpCallType.setValueByCode(14);
					}else{
						//cmpCallType.setValue(7);
						//консультативный
						cmpCallType.setValueByCode(6);
					}

					cmpCallType.disable();
				}
			}
			//@todo тут что-то придумать
			return;
		}

		lpuLocalCombo.reset();


		if(isExtraValue == 2)
		{
			cmpReasonIsNmp.setValue('nmp');
			//lpuLocalCombo.setDisabled(false);
			//smpUnitsCombo.setDisabled(true);
			//smpUnitsCombo.reset();
			this.getNmpMedService();
			
		}
		else
		{
			cmpReasonIsNmp.setValue('');
			//lpuLocalCombo.reset();
			//lpuLocalCombo.setDisabled(true);
			//smpUnitsCombo.setDisabled(false);
			this.changeLpuBuildingByFormAddress();
		}
		this.refreshFieldsVisibility([
			'CmpCallCard_IsPoli',
			'lpuLocalCombo',
			'selectNmpCombo',
			'LpuBuilding_id'
		]);
	},
	clearPersonFields: function(){
		var baseForm = this.baseForm,
			wnd = this.armWindow,
			f = baseForm.findField('Person_Surname'),
			i = baseForm.findField('Person_Firname'),
			o = baseForm.findField('Person_Secname'),
			lpuLocalCombo = baseForm.findField('lpuLocalCombo'),
			smpUnitsCombo = baseForm.findField('LpuBuilding_id'),
			pacientSearchResText = wnd.down('panel[refId=pacientSearchResText]'),
            pacientSearchResTextContainer = wnd.down('container[refId=pacientButtonHistory]');

		this.setIllegalActMessage(null, 'personReset');

		f.reset();
		i.reset();
		o.reset();
		baseForm.findField('Person_Birthday').reset();
		baseForm.findField('Person_IsUnknown').reset();
        pacientSearchResTextContainer.removeAll();

		if(!getRegionNick().inlist(['ufa', 'krym', 'kz'])) baseForm.findField('Polis_Number_fake').reset();
		baseForm.findField('Sex_id').reset();
		baseForm.findField('Person_id').reset();
		baseForm.findField('Person_Birthday_YearAge').reset();

		/*if(!this.saveAndContinue){
			lpuLocalCombo.reset();
		}*/

		/*
		lpuLocalCombo.disable(true);
		smpUnitsCombo.setDisabled(false);
		if (Ext.fly('st').getStyle('visibility') != 'hidden'){
			Ext.fly(lpuLocalCombo.getId()).select('.small-tip').setVisible(false, true)
		}
		*/
		pacientSearchResText.el.setHTML('');

		f.setDisabled(false);
		i.setDisabled(false);
		o.setDisabled(false);

		if(!getRegionNick().inlist(['ufa', 'krym', 'kz'])) baseForm.findField('Polis_Number_fake').disable();
		baseForm.findField('Sex_id').enable();
		baseForm.findField('Person_Birthday_YearAge').enable();
		this.checkUrgencyAndProfile();
	},

	//Расчет возвраста на основе даты вызова и дня рождения
	convertPersonBirthdayToAge: function() {
		var birthday = Ext.Date.parse(this.baseForm.findField('Person_Birthday').getValue(), 'd.m.Y');
		var date = this.baseForm.findField('CmpCallCard_prmDate').getValue();

		var ageField = this.baseForm.findField('Person_Birthday_YearAge');
		var unitField = this.baseForm.findField('ageUnit');

		if(ageField.getValue()) return;

		if (Ext.isEmpty(birthday)) {
			ageField.setValue(null);
			unitField.setValue(1);
		} else {
			var years = swGetPersonAge(birthday, date);

			if (years > 0) {
				ageField.setValue(years);
				unitField.setValue(1);
				ageField.setMaxValue(150);
			} else {
				var days = Math.floor(Math.abs((date - birthday)/(1000 * 3600 * 24)));
				var months = Math.floor(Math.abs(date.getMonthsBetween(birthday)));

				if (months > 0) {
					ageField.setValue(months);
					ageField.setMaxValue(12);
					unitField.setValue(2);
				} else {
					ageField.setValue(days);
					unitField.setValue(3);
					ageField.setMaxValue(31);
				}
			}
			ageField.validate();
		}
	},

	/*converterPersonBirthdayToAge: function(personDateAge){
		var personBirthYearFrom, personBirthYearTo,
			ageUnit = this.baseForm.findField('ageUnit').getValue(),
			personAge = this.baseForm.findField('Person_Birthday_YearAge').getValue(),
			personYearsInterval,
			currentYear = Ext.Date.format(new Date,'Y');

		if (personDateAge){
			switch(ageUnit){
				case 1: {
					personBirthYearFrom = currentYear - personDateAge - 1;
					personBirthYearTo = currentYear - personDateAge;
					break;
				}
				default: {
					personBirthYearFrom = currentYear - 1;
					personBirthYearTo = currentYear;
					break;
				}
			}
			/*if (Ext.Date.parse(personDateAge,'Y'))
			{
			//указан год рождения
				personBirthYearFrom = personDateAge;
				personBirthYearTo = personDateAge;
//				storePerson.filter({
//					filterFn: function(item) {
//						var s = Ext.Date.parse(item.get('PersonBirthDay_BirthDay'),'d.m.Y'),
//							d = Ext.Date.format(new Date(), 'd.m'),
//							sd = Ext.Date.format(s, 'd.m');
//						return (Ext.Date.parse(d, 'd.m') -  Ext.Date.parse(sd, 'd.m') > 0)
//					}
//				})
			}
			else if(Ext.Date.parse(personDateAge,'d.m.Y'))
			{
				//указана дата
				var date = Ext.Date.parse(personDateAge, "d.m.Y");
				personBirthYearFrom = Ext.Date.format(date, 'Y');
				personBirthYearTo = Ext.Date.format(date, 'Y');
			}
			else
			{
			//указан возраст
				personBirthYearFrom = currentYear - personDateAge-1;
				personBirthYearTo = currentYear - personDateAge;
			}
			*/
			/*return [parseInt(personBirthYearFrom), parseInt(personBirthYearTo)];
		} else {
			return null;
		}
	},*/

	searchPersonByAddress: function(){
		var cntr = this,
			storePerson =  this.storePerson,
			baseForm = this.baseForm,
			allParams = baseForm.getFieldValues(),
			parms = {},
			cityCombo = baseForm.findField('dCityCombo'),
			streetsCombo = baseForm.findField('dStreetsCombo'),
			selectedCityRec = cityCombo.getStore().findRecord('Town_id', cityCombo.getValue()),
			selectedStreetRec =	streetsCombo.bigStore.findRecord('StreetAndUnformalizedAddressDirectory_id', streetsCombo.getValue());

			if(!allParams.CmpCallCard_Dom && !allParams.CmpCallCard_Dom) return false;

			parms = {
				'Area_pid' : selectedCityRec?selectedCityRec.get('Region_id'):null,
				'Town_id' : selectedCityRec?selectedCityRec.get('Town_id'):null,
				'KLStreet_id' : selectedStreetRec?selectedStreetRec.get('KLStreet_id'):null,
				'Address_House': allParams.CmpCallCard_Dom,
				'Address_Corpus': allParams.CmpCallCard_Korp,
				'Address_Flat': allParams.CmpCallCard_Kvar,
				'isNotDead': true
			}

			if (cntr.personSearchByAddressStore.loading && cntr.personSearchByAddressStore.lastOperation) {
			var requests = Ext.Ajax.requests;
			  for (id in requests)
				if (requests.hasOwnProperty(id) && requests[id].options == cntr.personSearchByAddressStore.lastOperation.request) {
				  Ext.Ajax.abort(requests[id]);
				}
			}

			cntr.personSearchByAddressStore.removeAll();
			cntr.personSearchByAddressStore.getProxy().extraParams = parms;
			cntr.personSearchByAddressStore.load();
	},
	
	abortRequestByUrl: function(url){
		var requests = Ext.Ajax.requests;
		
		for (id in requests){
			if (requests.hasOwnProperty(id) && requests[id].options && requests[id].options.url == url) {
				Ext.Ajax.abort(requests[id]);
			}
		}
			
	},

	showPersonByAddressGrid: function(){
		var personsByAddressStore = Ext.data.StoreManager.lookup('personSearchByAddress'),
			cntr = this,
			baseForm = this.baseForm,
			surnameField = baseForm.findField('Person_Surname'),
			cmpCallTypeField = baseForm.findField('CmpCallType_id'),
			isExtraCombo = baseForm.findField('CmpCallCard_IsExtra');

		//если тип редактирование вызова не по умолчанию выходим
		if(cntr.baseForm.findField('typeEditCard').getValue() != 'default') return false;

		if(personsByAddressStore.count()==0/* && !personsByAddressStore.isLoading()*/) return false;

		var formatPersonData = function(rec){
			if(!rec)return false;

			var persData = rec.data,
				pers = {
				PersonSurName_SurName: persData.Person_SurName,
				PersonFirName_FirName: persData.Person_FirName,
				PersonSecName_SecName: persData.Person_SecName,
				PersonBirthDay_BirthDay: persData.PersonBirthDay_BirthDay,
				Person_isOftenCaller: persData.Person_isOftenCaller,
				Person_Age: persData.Person_Age,
				Polis_Num: persData.Polis_Num,
				Polis_Ser: persData.Polis_Ser,
				Polis_EdNum: persData.Polis_EdNum,
				Sex_id: persData.Sex_id,
				CmpLpu_id: persData.CmpLpu_id,
				Lpu_Nick: persData.Lpu_Nick,
				Person_id: persData.Person_id,
				countCallCards: persData.countCallCards,
				Person_deadDT: persData.Person_deadDT
			}

			return pers;
		}

		var wndPers = Ext.create('Ext.window.Window', {
			title: 'Проживают по указанному адресу',
			width: 730,
			layout: 'fit',
			resizable: false,
			refId: 'personsLivedInThRoom',
			modal: true,
			defaultFocus: 'personsLivedInThRoomGrid',
			onEsc: function(){
				wndPers.close();
				personsByAddressStore.removeAll();
				surnameField.focus(false, 100);
			},
			items: {
				xtype: 'grid',
				refId: 'personsLivedInThRoomGrid',
				border: false,
				maxHeight: 250,
				viewConfig: {
					itemId: 'personsLivedInThRoomGrid',
					loadingText: 'Загрузка'
				},
				columns: [
					{dataIndex: 'Person_id', text: 'Person_id', key: true, hidden: true, hideable: false },
					{text: 'Фамилия',  dataIndex: 'Person_SurName', width: 200, renderer: function(value) {return Ext.String.format('<span style="font-size:16px;">{0}</span>', value, value);}},
					{text: 'Имя', dataIndex: 'Person_FirName', width: 200, renderer: function(value) {return Ext.String.format('<span style="font-size:16px;">{0}</span>', value, value);}},
					{text: 'Отчество', dataIndex: 'Person_SecName', width: 200, renderer: function(value) {return Ext.String.format('<span style="font-size:16px;">{0}</span>', value, value);}},
					{text: 'Дата рождения', dataIndex: 'PersonBirthDay_BirthDay', width: 100, renderer: function(value) {return Ext.String.format('<span style="font-size:16px;">{0}</span>', value, value);}}
				],
				store: personsByAddressStore,
				listeners: {
					itemClick: function(cmp, record, item, index, e, eOpts ){
						cntr.clearPersonFields();
						cntr.setPatient(formatPersonData(record), false);
						wndPers.close();
						cmpCallTypeField.focus(false, 100);

						if(isExtraCombo.getValue() == 2){
							cntr.getNmpMedService()
						}

						cntr.storePerson.removeAll();
						cntr.storePerson.add({
							'PersonSurName_SurName' : record.get('Person_SurName'),
							'PersonFirName_FirName' : record.get('Person_FirName'),
							'PersonSecName_SecName' : record.get('Person_SecName'),
							'PersonBirthDay_BirthDay' : record.get('PersonBirthDay_BirthDay'),
							'Person_isOftenCaller' : record.get('Person_isOftenCaller'),
							'countCallCards' : record.get('countCallCards'),
							'Person_Age' : record.get('Person_Age'),
							'Sex_id' : record.get('Sex_id'),
							'Person_id' : record.get('Person_id')
						});

					},
					cellkeydown: function(cmp, td, cellIndex, record, tr, rowIndex, e, eOpts){
						switch(e.getKey()){
							case 13: {
								cntr.clearPersonFields();
								cntr.setPatient(formatPersonData(record), false);
								if(isExtraCombo.getValue() == 2){
									cntr.getNmpMedService()
								}
								wndPers.close();
								cmpCallTypeField.focus(false, 100);
								break;
							}
						}
					},
					celldblclick: function( cmp, td, cellIndex, record, tr, rowIndex, e, eOpts ){
						cntr.clearPersonFields();
						cntr.setPatient(formatPersonData(record), false);
						wndPers.close();
						cmpCallTypeField.focus(false, 100);
					}
				}
			},
			listeners: {
				show: function(w){
					w.down('grid').getSelectionModel().select(0);
				},
				close: function(){
					//cntr.searchPerson();
					personsByAddressStore.removeAll();
					surnameField.focus(false, 100);

				}
			}
		}).show();
	},

	takeCall112SubmitValidation: function(operatorNumValue, alertBox112Window) {

		var cntr = this,
			show112CardButton = Ext.ComponentQuery.query('swDispatcherCallWorkPlace button[name=showCard112]')[0],
			DispatchCallWorkPlace = cntr.win,
			Mask = new Ext.LoadMask(DispatchCallWorkPlace, {msg: "Пожалуйста, подождите, идет поиск"});

		//if (Ext.isEmpty(operatorNumValue)) {
		//	Ext.Msg.alert('Внимание', 'Введите номер оператора 112');
		//	return false;
		//}

		cntr.clearAllFields();
		cntr.setDefaultValues();

		// o	По Номеру оператора 112 (CmpCallCard112.AcceptOperatorStr) производится поиск Карточки 112 в статусе «Новая».
		Mask.show();
		Ext.Ajax.request({
			url: '/?c=CmpCallCard4E&m=findCmpCallCard112',
			params: {
				Ier_AcceptOperatorStr: operatorNumValue
			},
			callback: function(opt, success, response){
				Mask.hide();

				if(response){
					var response_obj = Ext.JSON.decode(response.responseText);
					if (response_obj.success) {

						var mainTabPanel = cntr.win.down('panel[refId=mainTabPanelDW]'),
							calls112Panel = mainTabPanel.down('[refId=CmpCalls112List]'),
							formCalls112Panel = calls112Panel.down('form').getForm(),
							Ier_AcceptOperatorStrField = formCalls112Panel.findField('Ier_AcceptOperatorStr'),
							callListDateRangeField = formCalls112Panel.findField('callListDateRange'),
							CmpCallCard112StatusTypeField = formCalls112Panel.findField('CmpCallCard112StatusType_id');

						switch(response_obj.cnt) {
							case 0:

								//Если НЕ найдено ни одной Карточки 112, то автоматически производятся следующие действия:
								//	Форма «Прием вызова из 112» закрывается.
								//	Открывается форма «Журнал карточек 112» для выбора Карточек 112

								DispatchCallWorkPlace.BaseForm.el.unmask();
								Ext.ComponentQuery.query('[refId=calls112ListDW]')[0].tab.show();
								mainTabPanel.setActiveTab(2); // Журнал карточек 112
								callListDateRangeField.clearDate();
								Ier_AcceptOperatorStrField.reset();
								CmpCallCard112StatusTypeField.setValue(1);
								alertBox112Window.close();


								break;
							case 2:
								// o	Если найдено несколько Карточек 112, то автоматически производятся следующие действия:


								DispatchCallWorkPlace.BaseForm.el.unmask();
								Ext.ComponentQuery.query('[refId=calls112ListDW]')[0].tab.show();
								mainTabPanel.setActiveTab(2); // Журнал карточек 112
								Ier_AcceptOperatorStrField.setValue(operatorNumValue);
								callListDateRangeField.shadowSetValue(new Date(response_obj.minDate), new Date());
								CmpCallCard112StatusTypeField.setValue(1);
								calls112Panel.searchCmpCallCard112();

								Ext.create('sw.lib.Toaster', {
									bodyStyle: 'background-color: #efefb3;',
									style: 'background: #edcd4b; border-color: #efefb3;',
									managerAlignment: 'tr-br',
									paddingX: - 30,
									paddingY: - 120,
									width: 300,
									frame: false,
									border: false,
									resizable: false,
									closable: false,
									autoCloseDelay: 5000,
									listeners: {
										close: function(){

										}
									},
									html: 'Внимание. По введенному номеру оператора 112 найдено несколько необработанных карточек 112. Выберите карточки для редактирования параметров'
								}).show();

								alertBox112Window.close();
								/*
								 Ext.Msg.alert('Внимание', 'По введенному номеру оператора 112 найдено несколько необработанных карточек 112. Выберите карточки для редактирования параметров', function() {
								 alertBox.close();
								 var mainTabPanel = cntr.win.down('panel[refId=mainTabPanelDW]');
								 mainTabPanel.setActiveTab(2); // Журнал карточек 112
								 });
								 */
								// todo
								break;
							case 1:
								if(response_obj.minDate == 'toDay'){
									alertBox112Window.close();
									DispatchCallWorkPlace.BaseForm.el.unmask();
									cntr.setStatus112Cards([response_obj.CmpCallCard112_id], 2);
									cntr.selected112Cards = [response_obj.CmpCallCard112_id];
									cntr.selectedCards = [response_obj.CmpCallCard_id];
									cntr.loadCmpCard(response_obj.CmpCallCard_id);
									show112CardButton.setVisible(true);

									// o	Если найдена одна Карточка 112 за сегодняшнее число, то автоматически производятся следующие действия:
									// 	На форме приема вызова подставляются параметры вызова, связанного с найденной Карточкой 112.
									// 	Статус Карточки 112 меняется на «В обработке».
									// 	Если форма приема вызова редактируется и сохраняется, то производятся следующие действия:
									// •	Вызов сохраняется с новыми параметрами, введенными на форме приема вызова;
									// •	Статус Карточки 112 меняется на «Обработана».
									// 	Если форма приема вызова закрывается БЕЗ сохранения изменений, то статус Карточки 112 меняется на «Новая».
									// todo
								}else{
									DispatchCallWorkPlace.BaseForm.el.unmask();
									Ext.ComponentQuery.query('[refId=calls112ListDW]')[0].tab.show();
									mainTabPanel.setActiveTab(2); // Журнал карточек 112
									Ier_AcceptOperatorStrField.setValue(operatorNumValue);
									callListDateRangeField.shadowSetValue(new Date(response_obj.minDate), new Date());
									CmpCallCard112StatusTypeField.setValue(1);
									calls112Panel.searchCmpCallCard112();

									Ext.create('sw.lib.Toaster', {
										bodyStyle: 'background-color: #efefb3;',
										style: 'background: #edcd4b; border-color: #efefb3;',
										managerAlignment: 'tr-br',
										paddingX: - 30,
										paddingY: - 120,
										width: 300,
										frame: false,
										border: false,
										resizable: false,
										closable: false,
										autoCloseDelay: 5000,
										listeners: {
											close: function(){

											}
										},
										html: 'Внимание. По введенному номеру оператора 112 найдена необработанная карточка 112'
									}).show();

									alertBox112Window.close();
								}

								break;
						}
					}
				}
			}
		});
	},

    setUnknownPerson: function(){
        var cntr = this;

        Ext.Ajax.abortAll();
        cntr.storePerson.removeAll();
        this.clearPersonFields();

        var f = cntr.baseForm.findField('Person_Surname'),
            i = cntr.baseForm.findField('Person_Firname'),
            o = cntr.baseForm.findField('Person_Secname'),
            s = cntr.baseForm.findField('Sex_id');

        f.setValue('НЕИЗВЕСТЕН');
        i.setValue('НЕИЗВЕСТЕН');
        o.setValue('НЕИЗВЕСТЕН');
        this.isUndefinded = true;
        s.setValue(3);
        //f.disable();
        //i.disable();
        //o.disable();
        cntr.baseForm.findField('Person_Birthday_YearAge').focus();
    },

	revertUnknownByFIO: function () {
		var cntr = this,
			form = cntr.baseForm,
			values = form.getAllValues();
		
		if (values.Person_Firname != 'НЕИЗВЕСТЕН' ||
			values.Person_Secname != 'НЕИЗВЕСТЕН' ||
			values.Person_Surname != 'НЕИЗВЕСТЕН') {
			this.isUndefinded = false;
		}
	},

	checkAddreessFindBtn: function() {
		var cntr = this;
			form = cntr.baseForm,
			fields = form.getAllFields(),
			cityComboRec = fields.dCityCombo.getSelectedRecord(),
			streetsComboRec = fields.dStreetsCombo.getSelectedRecord(),
			CmpCallCard_Dom = fields.CmpCallCard_Dom.getValue(),
			addrFindBtn = cntr.win.down('button[refId=searchByAddressBtn]');

		if(cityComboRec.data && streetsComboRec.data && CmpCallCard_Dom) {
			addrFindBtn.enable();
			return true;
		} else {
			addrFindBtn.disable();
			return false;
		}
	},

    showPersonByAddress: function(){
        var cntr = this;

        if(!cntr.checkAddreessFindBtn()) return;

        var personsByAddressStore = Ext.data.StoreManager.lookup('personSearchByAddress'),
			loadMask = new Ext.LoadMask(Ext.getCmp('DispatchCallWorkPlace'), {msg: "Поиск людей, проживающих по адресу..."});

        if(personsByAddressStore.count() == 0){
            loadMask.show();
            cntr.searchPersonByAddress();
            cntr.personSearchByAddressStore.load({
                callback:function(recs){
                    loadMask.hide();
                    var ContainerEl = Ext.getCmp('DispatchCallWorkPlace').el,
                        messageWin = Ext.getCmp('noPersonMessage');
                    if(recs && recs.length == 0 && !messageWin){
                        var alertBox = Ext.create('Ext.window.Window', {
                            title: 'Сообщение',
                            height: 50,
                            width: 250,
                            id: 'noPersonMessage',
                            constrain: true,
                            header: false,
                            constrainTo: ContainerEl,
                            layout: {
                                type: 'hbox',
                                align: 'middle'
                            },
                            bodyBorder: false,
                            items: [
                                {
                                    xtype: 'label',
                                    flex: 1,
                                    html: "Люди, проживающие по адресу вызова, не найдены"
                                }
                            ]
                        });
                        alertBox.showAt([ContainerEl.getWidth()-ContainerEl.getLocalX()-250, ContainerEl.getHeight()-40]);

                        setTimeout(function(){alertBox.close()},3000)
                    }else{
                        cntr.showPersonByAddressGrid();
                    }

                }
            })
        }else{
            cntr.showPersonByAddressGrid();
        }

    },

    showSearchPersonWnd: function(){
        var cntr = this;

        Ext.Ajax.abortAll();
        Ext.create('sw.tools.subtools.swPersonWinSearch',
            {
                personform: cntr.baseForm,
                storePerson: cntr.storePerson,
                forObject: 'CmpCallCard',
                callback: function(person_data, success, response) {
                    cntr.findWindow = true;

                    cntr.baseForm.findField('Person_Surname').focus(false, 500);

                    if(person_data) {
						cntr.clearPersonFields();
						cntr.setPatient(person_data,false);
					}
                    //cntr.checkDuplicateByFIO();
                    //cntr.checkRecall('person');
                }
                //caller: this
            }).show()
    },

	searchPerson: function(){
		var storePerson =  this.storePerson,
			baseForm = this.baseForm,
			allParams = baseForm.getFieldValues(),
			personBirtDay = Ext.Date.parse(allParams.Person_Birthday, 'd.m.Y'),
			personDateAge, PersonBirthYearFrom, PersonBirthYearTo, dayFromBirthday,
			personBirtDayFrom, personBirtDayTo,
			deltaDate = 5, // +/- 5 лет
			wnd = this.armWindow;

		//если тип редактирование вызова не по умолчанию выходим
		//if(baseForm.findField('typeEditCard').getValue() != 'default') return false;

		//дней от рождения понадобится для вычисления детей менее 3 месяцев
		if(allParams.Person_Birthday){
			dayFromBirthday = (new Date - Ext.Date.parse(allParams.Person_Birthday,'d.m.Y'))/1000/60/60/24;
		}

		if(
			allParams.Person_Surname &&
			(allParams.Person_Firname || dayFromBirthday < 93)
			&& allParams.Person_Birthday_YearAge
			&& personBirtDay
		)
		{
			if(this.isUndefinded === true){
				return false;
			}

			//стоп...проверочка а вдруг мы уже определили этого персонажа
			if(allParams.Person_id && storePerson.getCount()==1){
				var personInfo = storePerson.getAt(0).data;
				if (
					(personInfo.PersonSurName_SurName == allParams.Person_Surname)
					&& (personInfo.PersonFirName_FirName == allParams.Person_Firname)
					&& (personInfo.PersonSecName_SecName == allParams.Person_Secname)
					&& (personInfo.Person_Age == swGetPersonAge(personBirtDay, allParams.CmpCallCard_prmDate) )
					&& (personInfo.Sex_id == allParams.Sex_id)
				){ return false; }
			};

			storePerson.clearFilter();

			baseForm.findField('Person_Age').setValue(0);
			baseForm.findField('Person_Birthday').reset();
			baseForm.findField('Person_id').setValue(0);
			baseForm.findField('Person_isOftenCaller').setValue(1);

			/*personDateAge = allParams.Person_Birthday_YearAge;
			if (personDateAge >1000) personDateAge = Ext.Date.format(new Date,'Y') - personDateAge;
			personDateAge = this.converterPersonBirthdayToAge(personDateAge);*/
			this.convertPersonBirthdayToAge();

			switch(allParams.ageUnit){
				case 1: {
					PersonBirthYearFrom = personBirtDay.getFullYear() - deltaDate;
					PersonBirthYearTo = personBirtDay.getFullYear() + deltaDate;
					//лет
					break;
				}
				case 2:
				case 3: {
					personBirtDayFrom =  Ext.Date.format(new Date(personBirtDay.setMonth(personBirtDay.getMonth()-1)), 'd.m.Y');
					personBirtDayTo =  Ext.Date.format(new Date(personBirtDay.setMonth(personBirtDay.getMonth()+1)), 'd.m.Y');
					break;
				}
				default: {
					break;
				}
			}

			storePerson.getProxy().extraParams = {
				'PersonSurName_SurName' : allParams.Person_Surname,
				'PersonFirName_FirName' : allParams.Person_Firname,
				'PersonSecName_SecName' : allParams.Person_Secname,
				'personBirtDayFrom' : personBirtDayFrom,
				'personBirtDayTo' : personBirtDayTo,
				'PersonBirthDay_BirthDay': Ext.Date.format(allParams.Person_Birthday, 'd.m.Y'),
				'PersonBirthYearFrom': PersonBirthYearFrom ? PersonBirthYearFrom : null,
				'PersonBirthYearTo': PersonBirthYearTo ? PersonBirthYearTo : null,
				'Sex_id': allParams.Sex_id,
				/*пока убрал оптимизацию, тк подставлялся другой человек, возможно, надо пересмотреть концепцию
				'search_type': 'identification'*/
				'oneQuery': 2,
				'isNotDead':2
			};

			var saveContinueBtn = wnd.down('button[refId=saveContinueBtn]');
			var saveBtn = wnd.down('button[refId=saveBtn]');

			saveContinueBtn.disable();
			saveBtn.disable();

			this.showPersonSearchMessage(msg='Идентификация пациента...', status='load');
			storePerson.load({
				callback: function(rec, operation, success) {
					if ( !success ) {
						this.showPersonSearchMessage(msg='Пациент не идентифицирован', status='noone');
						if(!getRegionNick().inlist(['ufa', 'krym', 'kz'])) baseForm.findField('Polis_Number_fake').setValue('');
						return;
					}
					if (storePerson.getCount() == 0) {
						this.showPersonSearchMessage('Пациент не идентифицирован', 'noone');

						if(!getRegionNick().inlist(['ufa', 'krym', 'kz'])) baseForm.findField('Polis_Number_fake').disable();

						if(!getRegionNick().inlist(['ufa', 'krym', 'kz'])) baseForm.findField('Polis_Number_fake').setValue('');
						baseForm.findField('Sex_id').enable();
					} else if (storePerson.getCount() == 1) {
						var unoPacient = storePerson.getAt(0).getData();
						this.setPatient(unoPacient, true);

					} else if (storePerson.getCount() > 1) {
						this.showPersonSearchMessage('Найдено '+ storePerson.getCount()+' пациентов', 'many');
						if(getRegionNick().inlist(['ufa', 'krym', 'kz'])){
							baseForm.findField('Polis_Number_fake').disable();
							baseForm.findField('Polis_Number_fake').setValue('');
						}
						baseForm.findField('Sex_id').enable();
					}

					if (this.findWindow == true) {
						baseForm.findField('CmpCallType_id').focus();
						this.findWindow = false;
					}
					saveContinueBtn.enable();
					saveBtn.enable();
				}.bind(this)
			});
		}
	},

	setPatient: function(personInfo, setVisibleText){
		var baseForm = this.baseForm,
		wnd = this.armWindow,
			isNotDead = true,
		pacientSearchResText = wnd.down('panel[refId=pacientSearchResText]');

		//this.selectLpuTransmit(personInfo)

		if (personInfo.Person_id && personInfo.Person_deadDT) {
			var deadDT = this.checkDead(personInfo.Person_deadDT);

			if(deadDT && deadDT < new Date(baseForm.findField('CmpCallCard_prmDate').getValue()) ){
				Ext.Msg.alert('Ошибка', 'Человек на дату приема вызова является умершим. Выбор невозможен');
				if(setVisibleText)
					this.showPersonSearchMessage('Пациент не идентифицирован', 'noone');
				else
					pacientSearchResText.el.setHTML('');
				isNotDead = false;
			}
		}
		if(isNotDead) {
			baseForm.findField('Person_isOftenCaller').setValue(personInfo.Person_isOftenCaller);

			if (personInfo.PersonSurName_SurName) {
				baseForm.findField('Person_Surname').setValue(personInfo.PersonSurName_SurName);
			}
			if (personInfo.PersonFirName_FirName) {
				baseForm.findField('Person_Firname').setValue(personInfo.PersonFirName_FirName);
			}
			if (personInfo.PersonSecName_SecName) {
				baseForm.findField('Person_Secname').setValue(personInfo.PersonSecName_SecName);
			}
			if (personInfo.Person_Age){
				baseForm.findField('Person_Birthday_YearAge').setValue(personInfo.Person_Age);
			};
			if (personInfo.PersonBirthDay_BirthDay) {
				baseForm.findField('Person_Birthday').setValue(personInfo.PersonBirthDay_BirthDay);
				this.convertPersonBirthdayToAge();
			}
//		if (personInfo.PersonAge_AgeFrom){baseForm.findField('Person_Age_From').setValue(personInfo.PersonAge_AgeFrom)}
//		if (personInfo.PersonAge_AgeTo){baseForm.findField('Person_Age_To').setValue(personInfo.PersonAge_AgeTo)}
			if (personInfo.Polis_Num) {
				var Polis_Num = personInfo.Polis_Num;
				if (personInfo.Polis_Ser) {
					Polis_Num = personInfo.Polis_Ser + ' ' + Polis_Num;
				}
				if (!getRegionNick().inlist(['ufa', 'krym', 'kz'])) baseForm.findField('Polis_Number_fake').setValue(Polis_Num);
			} else if (personInfo.Polis_EdNum) {
				if (!getRegionNick().inlist(['ufa', 'krym', 'kz'])) baseForm.findField('Polis_Number_fake').setValue(personInfo.Polis_EdNum);
			}

			if (personInfo.Sex_id) {
				baseForm.findField('Sex_id').setValue(personInfo.Sex_id)
			}

			if (personInfo.CmpLpu_id) {
				baseForm.findField('CmpLpu_id').setValue(personInfo.CmpLpu_id);
			}
			if (personInfo.Lpu_Nick) {
				baseForm.findField('CmpLpu_Name').setValue(personInfo.Lpu_Nick);
			}
			if (personInfo.Person_id) {
				baseForm.findField('Person_id').setValue(personInfo.Person_id);
			}
			if (personInfo.Person_IsUnknown) {
				baseForm.findField('Person_IsUnknown').setValue(1);
			}
			if (personInfo.Person_Age) {
				baseForm.findField('Person_Age').setValue(personInfo.Person_Age);
			}
			if (personInfo.Person_deadDT) {
				baseForm.findField('Person_deadDT').setValue(personInfo.Person_deadDT);
			}


			if (personInfo.Person_id) {
				this.storePerson.filter('Person_id', personInfo.Person_id);
				this.showPersonSearchMessage('Пациент идентифицирован', 'uno', personInfo);
				baseForm.findField('Person_IsUnknown').setValue(1);
			} else {
				this.showPersonSearchMessage('Пациент не идентифицирован', 'noone');
			}

			if(!this.cmpDublicateFlag){
				this.checkUrgencyAndProfile();
				this.checkRecall();
				this.checkDuplicateByFIO();
			};

			// вынес проверку полсе проверки дублирующего вызова
			// this.checkRecall('person');
		}
		else
			return false;

	},

	//показать историю орбращений
	showPersonCallsHistory: function(person_id){
		var baseForm = this.baseForm,
			persField = baseForm.findField('Person_id');

		if(!person_id && persField.getValue()==0 )return false;

		var pacientCallsHistory = Ext.create('Ext.window.Window', {
			height: 250,
			modal: true,
			callback: Ext.emptyFn,
			width: 925,
			layout: 'fit',
			title: 'История обращений',
			bbar: [
				{ xtype: 'tbfill' },
				{
					xtype: 'button',
					text: 'Помощь',
					margin: '0 5 0 0',
					iconCls   : 'help16',
					handler   : function()
					{
						ShowHelp(this.up('window').title);
					}
				},
				{
					xtype: 'button',
					//id: 'cancelEmergencyTeamDutyTimeGrid',
					iconCls: 'cancel16',
					text: 'Закрыть',
					handler: function(){
						this.up('window').close()
					}
				}
			],
			items: {
				xtype: 'grid',
				//id: 'personsList',

				border: false,
				renderIcon: function(val) {
					var swtcher = (val==2)?'on':'off';
					return '<div class="x-grid3-check-'+swtcher+' x-grid3-cc-ext-gen2118"></div>';
				},
				columns: [
					{text: 'Принят', dataIndex: 'AcceptTime', width: 120,
						//renderer: Ext.util.Format.dateRenderer('d.m.Y H:i'),
						renderer: function(value) {

							value = new Date(value);
							return Ext.Date.format(value,'d.m.Y H:i');
						}
					},
					{text: 'Фамилия',  dataIndex: 'Person_SurName', width: 90},
					{text: 'Имя', dataIndex: 'Person_FirName', width: 80},
					{text: 'Отчество', dataIndex: 'Person_SecName', width: 100},
					{text: 'Дата рождения', dataIndex: 'Person_BirthDay', width: 90},
					{text: 'Адрес', dataIndex: 'Address_Name', flex: 1},
					{text: 'Диагноз', dataIndex: 'CmpDiag_Name', width: 100},
					{text: 'Повод', dataIndex: 'CmpReason_Name', width: 100},
					{text: 'Госп.', dataIndex: 'Hospitalized', width: 40, renderer: function(value){return this.renderIcon(value);}}
				],
				store: Ext.create('Ext.data.Store', {
					extend: 'Ext.data.Store',
					storeId: 'personCallsHistory',
					autoLoad: true,
					stripeRows: true,
					fields: [
						{name: 'Person_id', type: 'int'},
						{name: 'AcceptTime', type: 'string'},
						{name: 'Person_SurName', type: 'string'},
						{name: 'Person_FirName', type: 'string'},
						{name: 'Person_SecName', type: 'string'},
						{name: 'Person_BirthDay', type: 'string'},
						{name: 'Address_Name', type: 'string'},
						{name: 'CmpReason_Name', type: 'string'},
						{name: 'CmpDiag_Name', type: 'string'},
						{name: 'Hospitalized', type: 'string'}
					],
					proxy: {
						type: 'ajax',
						url: '/?c=Person4E&m=getPersonCallsHistory',
						reader: {
							type: 'json',
							successProperty: 'success',
							root: 'data'
						},
						actionMethods: {
							create : 'POST',
							read   : 'POST',
							update : 'POST',
							destroy: 'POST'
						},
						extraParams: {
							'Person_id' : person_id?person_id:persField.getValue()
							//'Person_id' : personInfo.Person_id
						}
					},
					sorters: {
						property: 'AcceptTime',
						direction: 'ASC'
					}
				})
			}
		});
		pacientCallsHistory.show();
	},
	getCountCallByPersonId: function(Person_id){
		var wnd = this.armWindow,
			cntr = this,
			pacientSearchResText = wnd.down('container[refId=pacientButtonHistory]');
			//pacientSearchResText = wnd.down('panel[refId=pacientSearchResText]');

		if(Person_id)
		{
			var count = Ext.Ajax.request({
				url: '/?c=Person4E&m=getCountCallByPersonId',
				params: {
					Person_id:Person_id
				},
				success: function(response, opts){

					if(response)
					{
						var obj = Ext.decode(response.responseText),
							disabled_butt = true,
							butt_History = Ext.get('historyCalls');
						if(obj[0]['CountCard'] > 0)
							disabled_butt = false;
						if(butt_History == null)
						{
                            pacientSearchResText.items.removeAll();

                            pacientSearchResText.items.add(
                                Ext.create('Ext.Button', {
                                    id: 'historyCalls',
                                    disabled: disabled_butt,
                                    text: 'История обращений(F8) '+obj[0]['CountCard'],
                                    renderTo: pacientSearchResText.el,
                                    //style: 'margin: -3px 6px;',
                                    handler: function() {
                                        cntr.showPersonCallsHistory(Person_id)
                                    }.bind(this)
                                })
                            );

                            pacientSearchResText.doLayout();

						}
						
						var callHistoryBtn=cntr.win.down('button[refId=callHistoryBtn]');
						if( !disabled_butt ){
							callHistoryBtn.enable();
						}else{
							callHistoryBtn.disable();
						}
					}

					return obj[0]['CountCard']

				},
				failure: function(response, opts){
					return false;
				}
			});
		}
	},
	showPersonSearchMessage: function(msg, status, personInfo) {
		var baseForm = this.baseForm,
			wnd = this.armWindow,
			cntr = this,
			lpuLocalCombo = baseForm.findField('lpuLocalCombo'),
			pacientSearchResText = wnd.down('panel[refId=pacientSearchResText]'),
			pacientButtonHistory = wnd.down('container[refId=pacientButtonHistory]'),
			src = null,
			smpUnitsCombo = baseForm.findField('LpuBuilding_id'),
			dopPanel = '',
			parentWdth = 200,
			storePerson = this.storePerson,
			countCallCards = (personInfo && personInfo.countCallCards) ? personInfo.countCallCards : 0;

		pacientSearchResText.setVisible(true);

		if (personInfo && personInfo.Person_id) {
			cntr.getCountCallByPersonId(personInfo.Person_id);
			cntr.getDiagnosesPersonOnDisp(personInfo.Person_id);
		}
		else{
			cntr.clearDiagnosesPersonOnDispText();
			pacientButtonHistory.removeAll();
			pacientButtonHistory.doLayout();
		}

		cntr.checkCmpIllegelActAddress(personInfo);


		switch (status) {
			case 'load':
			{
				src = 'extjs4/resources/themes/images/default/grid/loading.gif';
				break
			}
			case 'noone':
			{
				src = 'extjs4/resources/themes/images/default/grid/drop-no.gif';
				break
			}
			case 'uno':
			{
				src = 'extjs4/resources/themes/images/default/grid/drop-yes.gif';
				break
			}
			case 'many':
			{
				src = 'extjs4/resources/themes/images/default/grid/columns.gif';
				break
			}

		}

		if (personInfo && personInfo.Person_isOftenCaller == 2) {
			dopPanel += '<div style="height: 16px; float: left;' +
				'padding-left: 23px; margin: 0 10px; background-image: url("extjs4/resources/themes/images/default/shared/warning.gif");' +
				'background-repeat: no-repeat">Часто обращающийся</div>'
			parentWdth = 350;
		};

		if (msg)
		{
			var dopPanelWrapper = '<div class="clientDopInfo" style="margin: 10px; width: ' + parentWdth + 'px;">' +
				'<div style="height: 16px; float: left;' +
				'padding-left: 23px;  background-image: url(' + src + ');' +
				'background-repeat: no-repeat">' + msg + '</div>' + dopPanel +
				'</div>';

			pacientSearchResText.el.setHTML(dopPanelWrapper);
		};

		if (status == 'many')
		{
			this.storePerson.clearFilter();
			Ext.create('Ext.Button', {
				text: 'Выбрать',
				renderTo: pacientSearchResText.el,
				style: 'margin: -3px 6px;',
				handler: function() {
					var pacientSearchRes = Ext.create('Ext.window.Window', {
						title: 'Выбор пациента',
						alias: 'widget.pacientSearchRes',
						height: 250,
						modal: true,
						width: 925,
						layout: 'fit',
						items: {
							xtype: 'grid',
							border: false,
							renderIcon: function(val) {
								if (val != 'false'){
									if (val=='true'){val='on'}
									return '<div class="x-grid3-check-'+val+' x-grid3-cc-ext-gen2118"></div>'
								}
							},
							columns: [
								{ text: 'ID',  dataIndex: 'Person_id', width: 60, hidden: true },
								{ text: 'Фамилия',  dataIndex: 'PersonSurName_SurName', flex: 1 },
								{ text: 'Имя', dataIndex: 'PersonFirName_FirName', width: 80 },
								{ text: 'Отчество', dataIndex: 'PersonSecName_SecName', width: 100 },
								{ text: 'Дата рождения', dataIndex: 'PersonBirthDay_BirthDay', width: 90 },
								{ text: 'Дата смерти', dataIndex: 'Person_deadDT', width: 90, /*renderer: function(value){return this.renderIcon(value);}*/},
								{ text: 'Адрес регистрации', dataIndex: 'UAddress_AddressText', width: 140 },
								{ text: 'Адрес проживания', dataIndex: 'PAddress_AddressText', width: 140 },
								{ text: 'ЛПУ прикрепления', dataIndex: 'Lpu_Nick', width: 90 }
							],
							store: storePerson,
							listeners: {
								beforecellclick: function( grid, td, cellIndex, record, tr, rowIndex, e, eOpts )
								{

									//baseForm.findField('CmpCallType_id').focus(false, 100);
									cntr.clearPersonFields();
									this.storePerson.filter('Person_id', record.get('Person_id'));
									this.setPatient(record.getData(), true);
									pacientSearchRes.close();

								}.bind(this)
							}
						}
					}).show()
				}.bind(this)
			})
		}
	},

	setDiagnosesPersonOnDispText: function(DiagList) {
		var diagnosesPersonOnDispText = this.armWindow.down('panel[refId=diagnosesPersonOnDispText]'),
			msg = 'Пациент состоит на диспансерном учете по диагнозам:',
			content = '',
			lineHeight = 14;
			cntr = this;

		if (!DiagList || DiagList.length == 0) {
			cntr.clearDiagnosesPersonOnDispText();
			return;
		}

		var hrefStyle = 'style="display:none;position:absolute;bottom:0;right:10px;padding:5px;background:#79f594;border-radius:30%"';
		var aHref = '<a href="#" id="expandDiagnosesPersonOnDispText" '+hrefStyle+' title="показать весь список">ещё</a>';
		for(var i=0; i<DiagList.length; i++){
			// if(i==2) content += '<a href="#" id="expandDiagnosesPersonOnDispText">Еще</a>';
			content += "<div style='line-height:"+lineHeight+"px'><strong>" + DiagList[i].Diag_Code+'</strong> ' + DiagList[i].Diag_Name + "</div>";
		}

		diagnosesPersonOnDispText.setHeight(74);
		diagnosesPersonOnDispText.setVisible(true);

		diagnosesPersonOnDispText.el.setHTML(
			'<div class="diagnosesPersonOnDisp" style="margin: 10px 0 0 80px;">' +
			'<div style="height: 16px;'+
			'padding-left: 23px;  background-image: url(extjs4/resources/images/alert.png);' +
			'background-repeat: no-repeat">'+ msg+'</div>' +
			// '<div style="padding-left: 23px; white-space: nowrap;" id="DiagnosesPersonContent">'+content+'</div>' +
			'<div style="padding-left: 23px;" id="DiagnosesPersonContent">'+content+'</div>' +
			'</div>' + aHref			
		);
		/*
		оставлю на потом
		var el = new Ext.Element(document.createElement('b')); el.update('appendChild');
		Ext.get('ex22').appendChild(el);
		*/
		var el = Ext.get('expandDiagnosesPersonOnDispText');
		if(el){
			el.on('click', function(){
				diagnosesPersonOnDispText.setHeight(150);
				this.destroy();
			})
		}

		// показать "Ещё"
		var ee = Ext.get('DiagnosesPersonContent');
		if( ee.getHeight()>(lineHeight*3) ){
			el.setVisible(true);
		}
	},

	clearDiagnosesPersonOnDispText: function() {
		var diagnosesPersonOnDispText = this.armWindow.down('panel[refId=diagnosesPersonOnDispText]');

		diagnosesPersonOnDispText.el.setHTML('');
		diagnosesPersonOnDispText.setVisible(false);
	},

	//функция проверки диагнозов человека на диспансерном учете
	getDiagnosesPersonOnDisp: function(personId){
		
		if(!personId) return false;

		var diagnosesPersonOnDispText = this.armWindow.down('panel[refId=diagnosesPersonOnDispText]'),
			cntr = this,
			url = '/?c=Person&m=getDiagnosesPersonOnDisp';
		
		cntr.abortRequestByUrl(url);
		
		Ext.Ajax.request({
			url: url,
			params: {Person_id : personId, actualForToday: true},
			//autoAbort: true,
			callback: function(opt, success, response) {
				if (success){
					var res = Ext.JSON.decode(response.responseText);
					
					cntr.setDiagnosesPersonOnDispText(res);
				}
			}
		});
	},

	/*
	закрываю метод, тк в тз нет описания
	selectLpuTransmit: function(personInfo) {
		var baseForm = this.baseForm,
			storePerson =  this.storePerson,
			cmpReason = baseForm.findField('CmpReason_id'),
			lpuLocalCombo = baseForm.findField('lpuLocalCombo'),
			cmpFieldReasonIsNmp = baseForm.findField('Reason_isNMP'),
			//cmpFieldReasonCode = baseForm.findField('CmpReason_Name'),
			flagOrArrayCodesIsNMP = false;

		if (!getRegionNick().inlist(['perm', 'ekb']))
			flagOrArrayCodesIsNMP = ("nmp" == cmpFieldReasonIsNmp.getValue());
		else
		{
			//определяем здесь CmpReason_Code
			var reasonCombo = baseForm.findField('CmpReason_id'),
				selReasonRec = reasonCombo.getSelectedRecord();
			
			//незадокументированное свойство комбобокса lastSelection
			if (selReasonRec) {
				var cmpFieldReasonCode = selReasonRec.get('CmpReason_Code');
				flagOrArrayCodesIsNMP = cmpFieldReasonCode.inlist(['04Г', '04Д', '09Я', '11Л', '11Я', '12Г', '12К', '12Р', '12У', '12Э', '12Я', '13Л', '13М', '15Н', '17А', '13С', '40Ц']);
			}
		}

		if(typeof personInfo == 'undefined'){
			personInfo = []
			personInfo['CmpLpu_id'] = baseForm.findField('CmpLpu_id').getValue(),
			personInfo['CmpLpu_Name'] = baseForm.findField('CmpLpu_Name').getValue(),
			personInfo['Person_id'] = baseForm.findField('Person_id').getValue(),
			personInfo['Person_Age'] = baseForm.findField('Person_Age').getValue();
		}


			if ( cmpReason.getValue() != null ){
				if	(	flagOrArrayCodesIsNMP &&
						( parseInt(personInfo['Person_Age']) ) &&
						( parseInt(personInfo['Person_id']) )
						//&& ( storePerson.count()>0 )
					)
				{
					//lpuLocalCombo.enable(true);
					if (lpuLocalCombo.store.findRecord('Lpu_id', parseInt(personInfo['CmpLpu_id'])))
						{
							lpuLocalCombo.setValue(parseInt(personInfo['CmpLpu_id']));
							if (Ext.fly('st').getStyle('visibility') != 'visible'){
								Ext.fly(lpuLocalCombo.getId()).select('.small-tip').setVisible(true, true)
							}
						}

					else {
						lpuLocalCombo.reset();
						if (Ext.fly('st').getStyle('visibility') != 'hidden'){
							Ext.fly(lpuLocalCombo.getId()).select('.small-tip').setVisible(false, true)
						}
						if((getRegionNick().inlist(['ufa', 'krym', 'kz'])) && lpuLocalCombo.getStore().getCount() == 1 ){
							var lpuIndex = lpuLocalCombo.getStore().getAt(0);
							lpuLocalCombo.setValue(lpuIndex);
						}
					}
				}
				else
				{
					lpuLocalCombo.reset();
					//lpuLocalCombo.disable(true);
					if (Ext.fly('st').getStyle('visibility') != 'hidden'){
						Ext.fly(lpuLocalCombo.getId()).select('.small-tip').setVisible(false, true)
					}
					if(lpuLocalCombo.getStore().getCount() == 1 ){
						var lpuIndex = lpuLocalCombo.getStore().getAt(0);
						lpuLocalCombo.setValue(lpuIndex);
					}
				}
			}
//		}
	},
	*/

	clearAllFields: function(param){
		var storePerson =  Ext.data.StoreManager.lookup('common.DispatcherCallWP.store.Person'),
			storePersonSearchByAddressStore =  Ext.data.StoreManager.lookup('personSearchByAddress'),
			baseForm = this.baseForm,
			allCmps = baseForm.getFields(),
			lpuLocalCombo = baseForm.findField('lpuLocalCombo'),
			pacientSearchResText = this.armWindow.down('panel[refId=pacientSearchResText]'),
			illegalActPanel = this.armWindow.down('panel[refId=illegalActPanel]'),
			ctrl = this,
			isUfa = getRegionNick() == 'ufa';
		ctrl.saveAndContinue = (param == 'saveAndContinue');

		allCmps.filterBy(function(o, k){
			if (!ctrl.saveAndContinue) {
				o.reset();
				if(o.clearValue) o.clearValue();
			} else {
				baseForm.findField('CmpCallCard_sid').setValue(baseForm.findField('CmpCallCard_sid').getValue());
				baseForm.findField('CmpCallType_id').setValueByCode(1); // Первичный
				baseForm.findField('CmpCallCard_prmTime').setValue(Ext.Date.format(new Date(ctrl.serverTime), "H:i:s"));
				baseForm.findField('CmpCallCard_prmDate').setValue(Ext.Date.format(new Date(ctrl.serverTime), "d.m.Y"));
				ctrl.getCmpCallCardNumber();
			}
		});
		ctrl.clearPersonFields(ctrl);
		ctrl.clearDiagnosesPersonOnDispText();
		baseForm.findField('CmpCallCard_storTimeShow').setVisible(false);
		baseForm.findField('CmpCallCard_storDateShow').setVisible(false);
		baseForm.findField('CmpCallCard_DayNumberRid').disable();
		baseForm.findField('Person_Surname').enable();
		baseForm.findField('Person_Firname').enable();
		baseForm.findField('Person_Secname').enable();
		storePerson.removeAll();

		if (!ctrl.saveAndContinue) storePersonSearchByAddressStore.removeAll();
		pacientSearchResText.el.setHTML('');
		illegalActPanel.el.setHTML('');
		if (ctrl.saveAndContinue) {
			baseForm.findField('Person_Surname').focus(false, 100);
		}
		ctrl.checkUrgencyAndProfile();
		ctrl.checkCrossRoadsFields();

		if(isUfa) ctrl.clearApplicationCVI();

		if (param == null){
			Ext.getCmp('MainForm').unmask();
		}
	},
	
	loadCmpCard: function(cmpCallCard_id, CallUnderControl, cbFunct){
		if (CallUnderControl == undefined) CallUnderControl = false;
		if (cbFunct == undefined) cbFunct = Ext.emptyFn;

		var ctrl = this,
			baseForm = ctrl.baseForm;
		Ext.Ajax.request({
			url: '/?c=CmpCallCard4E&m=loadCmpCallCardEditForm',
			params: {CmpCallCard_id: cmpCallCard_id},
			callback: function(opt, success, response) {
				if (success){
					var res = Ext.JSON.decode(response.responseText);
					
					if(res[0]){
						var cardDate = res[0];
						
						cardDate.CmpCallCard_prmDate = (Ext.Date.format(new Date(), "d.m.Y"));
						cardDate.CmpCallCard_prmTime = (Ext.Date.format(new Date(), "H:i:s"));
						cardDate.Person_Surname = cardDate.Person_SurName;
						cardDate.Person_Firname = cardDate.Person_FirName;
						cardDate.Person_Secname = cardDate.Person_SecName;
						cardDate.CmpCallCard_IsPassSSMP = (cardDate.CmpCallCard_IsPassSSMP == 2)?true:false;
						if(CallUnderControl) {
							cardDate.typeEditCard = 'CallUnderControl';
							cardDate.CmpCallCard_rid = cmpCallCard_id;
							cardDate.CmpCallCard_id = '';
						}

						ctrl.setDefaultValues(cardDate);
						cbFunct();
					}
					
				}
			}
		});
	},

	setDefaultValues: function(extraLoadParams)
	{
		var globals = getGlobalOptions(),
			baseForm = this.baseForm,
			cntr = this, regionInfo,
			lpuLocalCombo = baseForm.findField('lpuLocalCombo'),
			cityCombo = baseForm.findField('dCityCombo'),
			streetsCombo = baseForm.findField('dStreetsCombo'),
			secondStreetCombo = baseForm.findField('secondStreetCombo'),
			dateTimeCmp = baseForm.findField('CmpCallCard_prmDate'),
			timeTimeCmp = baseForm.findField('CmpCallCard_prmTime'),
			datetime = new Date(),
			callTypeCombo = baseForm.findField('CmpCallType_id'),
			callerTypeCombo = baseForm.findField('CmpCallerType_id'),
			typeEditCard = baseForm.findField('typeEditCard'),
			nmpCombo = baseForm.findField('selectNmpCombo'),
			isPoliField = baseForm.findField('CmpCallCard_IsPoli'),
			isActiveCall = baseForm.findField('CmpCallCard_IsActiveCall'),
			CmpCallType_Code = extraLoadParams?extraLoadParams.CmpCallType_Code:1,
			callDetailFormFields = baseForm.getFields().items,
			setDisableFields = function(makeDisable, exclude){
				for(i in callDetailFormFields){
					if(exclude.indexOf(callDetailFormFields[i].name)!=-1){
						continue;
					}
					callDetailFormFields[i].setReadOnly(makeDisable);
				}
			};

		var callUnderControl = false;
		cntr.cmpDublicateFlag = false;

		if(extraLoadParams){
			callUnderControl = (extraLoadParams.typeEditCard && extraLoadParams.typeEditCard == 'CallUnderControl');
			cntr.cmpDublicateFlag = !Ext.isEmpty(extraLoadParams.cmpDublicateFlag) ? extraLoadParams.cmpDublicateFlag : false;
			if (extraLoadParams.CmpCallPlaceType_id) {
				baseForm.findField('CmpCallPlaceType_id').setValue(extraLoadParams.CmpCallPlaceType_id);
			}
		}

		if(cntr.serverTime && Ext.isDate(cntr.serverTime))
			datetime = new Date(cntr.serverTime);
		var LpuBuilding_id = baseForm.findField('LpuBuilding_id');

		//Ставим свое МО для НМП кроме ЕДЦ
		if(cntr.isNmpArm && globals.SmpUnitType_Code && globals.SmpUnitType_Code != 4){
			cntr.setCurrentPPDLpu();
		}

		var currentLpuBuilding = sw.Promed.MedStaffFactByUser.current.LpuBuilding_id ||
			(typeof sw.Promed.MedStaffFactByUser.last == 'object' && sw.Promed.MedStaffFactByUser.last.LpuBuilding_id);

		if(extraLoadParams && (extraLoadParams.KLCity_id || extraLoadParams.KLTown_id)){
			currentLpuBuilding = extraLoadParams.LpuBuilding_id;
			regionInfo = {
				//KLRgn_id: extraLoadParams.KLRgn_id,
				//KLSubRgn_id: extraLoadParams.KLSubRgn_id,
				KLCity_id: extraLoadParams.KLCity_id,
				KLTown_id: extraLoadParams.KLTown_id
				//LpuBuilding_id : currentLpuBuilding
			};
		};

		baseForm.setValues(regionInfo);

		Ext.getCmp('saveBtn').enable();
		Ext.ComponentQuery.query('[refId=saveContinueBtn]')[0].enable();

		//устанавливаем значения текущей ЛПУ
		baseForm.findField('CmpLpu_id').setValue(globals.lpu_id);
		baseForm.findField('CmpLpu_Name').setValue(globals.lpu_nick);


		//если приходит в параметрах время, устанавливаем его
		if(extraLoadParams && extraLoadParams.CmpCallCard_prmDT){
			timeTimeCmp.setValue(Ext.Date.format(new Date(extraLoadParams.CmpCallCard_prmDT), "H:i:s"));
			dateTimeCmp.setValue(Ext.Date.format(new Date(extraLoadParams.CmpCallCard_prmDT), "d.m.Y"));
		}
		else{
			var serverTime = {};

			serverTime.callback = function(response) {
				if (response.date) {
					response.data = Ext.Date.parse(response.date, 'd.m.Y');
					dateTimeCmp.setValue(Ext.Date.format(response.data, "d.m.Y"));
				}
				if (response.time) {
					response.time = Ext.Date.parse(response.time, 'H:i');
					var seconds = Ext.Date.format(new Date(), 's');
					timeTimeCmp.setValue(Ext.Date.format(response.time, "H:i")+':'+seconds);
				}
				cntr.getCmpCallCardNumber();
			};

			getCurrentDateTime(serverTime);
		}


		cntr.datetimeStartEdit = new Date();

		//тип обращения
		// Если вызов на контроле, значит тип обращения не проставляем, проставится через форму выбора первичного вызова
		if(!callUnderControl) {
			if (callTypeCombo.getStore().count()) {
				var rec = callTypeCombo.getStore().findRecord('CmpCallType_Code', parseInt(CmpCallType_Code));
				if (rec) {
					callTypeCombo.setValueByCode(rec.data.CmpCallType_Code);
					if(extraLoadParams){
						extraLoadParams.CmpCallType_id = rec.data.CmpCallType_id;
					}
				}
			}
			else {
				callTypeCombo.getStore().on('load', function () {
					this.sort('CmpCallType_Code', 'ASC');
					var rec = callTypeCombo.getStore().findRecord('CmpCallType_Code', parseInt(CmpCallType_Code));
					if (rec) {
						callTypeCombo.setValueByCode(rec.data.CmpCallType_Code);
						if(extraLoadParams){
							extraLoadParams.CmpCallType_id = rec.data.CmpCallType_id;
						}
					}
					;
				});
			}
		}
		else {
			isActiveCall.setValue(2);
		}

		var loadCityCombo = function(info){
			/*загрузка города и улицы*/
			cityCombo.store.getProxy().extraParams = info;

			cityCombo.store.load({
				callback: function(rec, operation, success){

					if ( this.getCount() < 1 ) {
						return;
					}
					cityCombo.setValue(((rec && rec[0]) && !rec[1])? rec[0].get('Town_id'): null);
					var takeCall112Window = Ext.ComponentQuery.query('[refId=takeCall112Window]')[0];

					if(takeCall112Window && takeCall112Window.isVisible()){
						var DCWP_OperatorNum = Ext.ComponentQuery.query('textfield[name=operatorNum]')[0];

						DCWP_OperatorNum.focus(false, 300, function(){
							DCWP_OperatorNum.selectText(DCWP_OperatorNum.getValue().length);
						});
						DCWP_OperatorNum.selectText(DCWP_OperatorNum.getValue().length);
					}
					else{
						//cityCombo.focus(false, 200);
						cityCombo.selectText( 0, cityCombo.getRawValue().length );
					}

					if((rec && rec[0])){
						streetsCombo.bigStore.getProxy().extraParams = {
							town_id: rec[0].get('Town_id'),
							Lpu_id: sw.Promed.MedStaffFactByUser.current.Lpu_id
						};

						//var mask = new Ext.LoadMask(Ext.getCmp('MainForm'), {msg: "Пожалуйста, подождите..."});
						//mask.show();

						streetsCombo.bigStore.load({
							callback: function (recs, operation, success) {
								if (!getRegionNick().inlist(['ufa', 'kz'])) {
									streetsCombo.allowBlank = !(recs && recs.length);
								}
									secondStreetCombo.bigStore.loadData(recs);

								if(!extraLoadParams) {
								//	mask.hide();
									return;
								}

								var rec = streetsCombo.bigStore.findRecord('StreetAndUnformalizedAddressDirectory_id', extraLoadParams.StreetAndUnformalizedAddressDirectory_id);
								if (rec) {
									streetsCombo.reset();
									streetsCombo.store.removeAll();
									streetsCombo.store.add(rec);
									streetsCombo.setValue(rec.get('StreetAndUnformalizedAddressDirectory_id'));
								}

								var secrec = streetsCombo.bigStore.findRecord('KLStreet_id', extraLoadParams.CmpCallCard_UlicSecond);

								if(secrec){
									secondStreetCombo.store.removeAll();
									secondStreetCombo.store.add(secrec);
									secondStreetCombo.setValue(secrec.get('StreetAndUnformalizedAddressDirectory_id'));
								}
								cntr.checkCrossRoadsFields();

							//	if ((rec == null) && (streetsCombo.getValue() == null)){
								//	mask.hide();
								//} else if (streetsCombo.getValue() != null) {
								//	mask.hide();
							//	}
							}
						});
					}
				}
			});
		};

		if(!extraLoadParams){

			Ext.Ajax.request({
				url: '/?c=Address4E&m=getAddressFromLpuBuildingID',
				params: {LpuBuilding_id : currentLpuBuilding},
				success: function(response, opts) {

					if(!response.responseText) return false;

					var res = Ext.JSON.decode(response.responseText);
						if(res.address && res.LpuBuilding_setDefaultAddressCity && (res.LpuBuilding_setDefaultAddressCity == 2)){
							var regionInfo = {
								'region_id' : res.address.KLRgn_id,
								'KLSubRGN_id' : res.address.KLSubRGN_id,
								'KLCity_id' : res.address.KLCity_id,
								'KLTown_id' : res.address.KLTown_id
							};
							loadCityCombo(regionInfo);
						}
				}
			});

			baseForm.findField('CmpCallCard_IsExtra').setValue(cntr.isNmpArm ? 2 : 1);

			if(cntr.isNmpArm && cntr.LpuBuildingOptions) {
				baseForm.findField('Lpu_smpid').setValue(parseInt(cntr.LpuBuildingOptions.Lpu_eid));
				baseForm.findField('LpuBuilding_id').setValue(parseInt(cntr.LpuBuildingOptions.LpuBuilding_eid));
			}

		}
		else{
			loadCityCombo(regionInfo);

			extraLoadParams.CmpCallCard_IsPoli = (!Ext.isEmpty(extraLoadParams.CmpCallCard_IsPoli) && extraLoadParams.CmpCallCard_IsPoli == 2) ? true : false;
			extraLoadParams.CmpCallCard_IsPassSSMP = (!Ext.isEmpty(extraLoadParams.CmpCallCard_IsPassSSMP) && extraLoadParams.CmpCallCard_IsPassSSMP == 2) ? true : false;

			extraLoadParams.lpuLocalCombo = extraLoadParams.Lpu_ppdid;
			extraLoadParams.selectNmpCombo = extraLoadParams.MedService_id;


			baseForm.setValues(extraLoadParams);

			if(extraLoadParams.Person_Birthday){
				baseForm.findField('Person_Birthday').setValue(extraLoadParams.Person_Birthday);
			}else{
				baseForm.findField('Person_Birthday_YearAge').setValue(extraLoadParams.Person_Age);
			}

			if(extraLoadParams.CmpCallCard_sid){
				baseForm.findField('CmpCallCard_sid').setValue(extraLoadParams.CmpCallCard_sid);
			}
			if(extraLoadParams.Lpu_ppdid){
				// lpuLocalCombo - МО передачи (НМП)
				// selectNmpCombo - Служба НМП

				lpuLocalCombo.getStore().load({
					callback: function () {
						lpuLocalCombo.setValue(+extraLoadParams.Lpu_ppdid);
					}
				});

				if(extraLoadParams.MedService_id){
					nmpCombo.getStore().getProxy().extraParams = {
						'Lpu_ppdid': extraLoadParams.Lpu_ppdid,
						'isClose': 1
					};
					nmpCombo.getStore().load({
						callback: function () {
							nmpCombo.setValue(+extraLoadParams.MedService_id)
						}
					});
				};
			};

			//по идее CmpCallCard_Ktov текстовое поле, но почему-то иногда туда пишется id CmpCallCard_Ktov
			//пока оставлю здесь, потом уберу
			//@todo потом убрать
			if(extraLoadParams.CmpCallerType_id || +extraLoadParams.CmpCallCard_Ktov)
				baseForm.findField('CmpCallerType_id').setValue(+extraLoadParams.CmpCallerType_id || +extraLoadParams.CmpCallCard_Ktov);
			else
				baseForm.findField('CmpCallerType_id').setValue(extraLoadParams.CmpCallCard_Ktov);
		}

		//установка значения "Квартира" по умолчанию для поля Тип места вызова (refs #88079)

		if(getRegionNick()=='ufa') {
			var PlaceTypeDefaultValue = baseForm.findField('CmpCallPlaceType_id').store.findRecord('CmpCallPlaceType_Code', 1);
		} else {
			var PlaceTypeDefaultValue = baseForm.findField('CmpCallPlaceType_id').store.findRecord('CmpCallPlaceType_Code', 2);
		}

		if (PlaceTypeDefaultValue && !extraLoadParams) {
			baseForm.findField('CmpCallPlaceType_id').setValue(PlaceTypeDefaultValue.get('CmpCallPlaceType_id'));
		}

		//nmpCombo.getStore().load();

		cntr.convertPersonBirthdayToAge();

		cntr.isAutoSelectNmpSmp = false;
		cntr.refreshFieldsVisibility();
	},

	checkDuplicateSmpCallCard: function(params,cb) {
		// поиск дублирующих вызовов отталкивается от даты текущего вызова
		// иначе запрос вернет результаты за все годы
		if( !params.CmpCallCard_prmDate ) return false;

		var cntr = this;
		var byUrl = '';
		var cmpDublicateWindow = Ext.ComponentQuery.query('window[refId=swSelectFirstSmpCallCard]')[0];
		//var deteriorationSmpCallCardWindow = Ext.ComponentQuery.query('window[refId=swDeteriorationSmpCallCardWindow]')[0];

		// предотвратим повторной поиск дублирующих вызовов если окно уже открыто
		if(cmpDublicateWindow &&  cmpDublicateWindow.isVisible() ) {
			return false;
		}

		var CmpCallType_id = this.baseForm.findField('CmpCallType_id'),
			PrimaryCallType;
		switch (getGlobalOptions().region.nick) {
			case 'ufa':
				PrimaryCallType = 33;
				break;
			case 'pskov':
				PrimaryCallType = 16;
				break;
			case 'krym':
				PrimaryCallType = 57;
				break;
			default :
				PrimaryCallType = 2;
				break;
		}
		if (CmpCallType_id.getValue() != PrimaryCallType) {
			return;
		}
		switch(params.checkBy){
			case 'address':
				byUrl = '/?c=CmpCallCard4E&m=checkDuplicateCmpCallCardByAddress';
				break;
			case 'fio':
				byUrl = '/?c=CmpCallCard4E&m=checkDuplicateCmpCallCardByFIO';
				break;
			default: cb(true,params); // вынес ответ для успешного прохождения проверки (эффект плацебо) проверку прошел, а проверки не было
		}
		
		cntr.abortRequestByUrl(byUrl);

        if(cntr.cmpDublicateFlag) return;
		
		Ext.Ajax.request({
			//autoAbort: true,
			params: params,
			callback: function(opt, success, response) {
				if ( success ) {
					// предотвратим появление двух форм
					//if(cntr.cmpDublicateFlag) return;

					var response_obj = Ext.JSON.decode(response.responseText);
					if (response_obj.data) {

						if ( response_obj.data.length > 0) {
							cntr.showSelectFirstSmpCallCard(response_obj.data, params.checkBy);
							cntr.oneCheckDuplicate = true;
							//cntr.cmpDublicateFlag = true;
						}
						else {
							params.DuplicateCount = 0;
							cntr.cmpDublicateFlag = false;
							cb(true,params);
							// вот тут проверим повторный вызов
							cntr.checkRecall('address_person');
						}
					} else {
						if (response_obj.Error_Msg) {
							Ext.Msg.alert('Ошибка', response_obj.Error_Msg);
						}
					}
				}
				else {
					// Ext.Msg.alert('Ошибка', 'Ошибка при проверке дублирования вызова');
					log('Ошибка при проверке дублирования вызова');
				}
			}.bind(this),
			url: byUrl
		});
		return false;

	},
	setAllowBlankDependingCallType: function(){
		var cntr = this,
			requiredFieldsByCallType = {},
			baseForm = cntr.baseForm,
			CmpCallType_Code = baseForm.findField('CmpCallType_id'),
			lpuBuildingField = baseForm.findField('LpuBuilding_id'),
			lpuLocalCombo = baseForm.findField('lpuLocalCombo'),
			streetsCombo = baseForm.findField('dStreetsCombo'),
			CmpCallCard_Telf = baseForm.findField('CmpCallCard_Telf'),
			reasonCombo = baseForm.findField('CmpReason_id'),
			selReasonRec = reasonCombo.getSelectedRecord(),
			store = CmpCallType_Code.getStore(),
			nmpCall = (baseForm.findField('CmpCallCard_IsExtra').getValue() == 2)?true:false,
			idx = store.find('CmpCallType_id', CmpCallType_Code.getValue()),
			CallTypeCodesWithoutLpuBuilding = [6, 15, 16], // Коды типов вызовов: Консультативный, Справка, Абонент отключился
			ufaCallTypeCodesWithoutTelf = [4, 6, 9, 15, 16]; // Коды типов вызовов: Консультативный, Попутный, Для Спец. Бр. СМП, Справка, Абонент отключился

		if (idx == -1)
		{
			CmpCallType_Code.allowBlank = false;
			//Ext.Msg.alert('Ошибка', "Поле \"Тип вызова\" заполнено некорректно");
		}
		else {
			var code = store.getAt(idx).get('CmpCallType_Code');
			CmpCallType_Code.allowBlank = true;
			/**
			 http://redmine.swan.perm.ru/issues/80059


			 CmpCallType_Code / CmpCallType_Name / старое значение привязанное к коду
			 0 / Ошибка
			 1 / Первичное
			 2 / Повторное
			 3 / Активный / Дублирующее
			 4 / Попутный / Консультативное
			 5 / Админ. решение / Справка
			 6 / Консультативный / Абонент отключился
			 7 / Контрольный / Отмена вызова
			 8 / Совместный / Попутное
			 9 / Для усиления (Для спец.бр.СМП) / Активный выезд
			 10 / В помощь
			 11 / Амбулаторный
			 12 / ЧС
			 13 / ОБСЛ/ПОП
			 14 / Дублирующий
			 15 / Справка
			 16 / Абонент отключился
			 17 / Отмена вызова
			 139 / 138

			 **/
				// Массив обязательных полей в зависимости от типа вызова, выраженного кодом, расшифровка выше
				// <код_поля>: [<массив_кодов_типа_вызова_в_котором_это_поле_обязательно_для_заполнения>]

			requiredFieldsByCallType = {
				dCityCombo: [1, 2, 4, 9, 14, 17, 19], // Населенный пункт
				CmpCallPlaceType_id: [1, 2, 4, 9, 14, 17], // Тип места вызова
				CmpCallCard_Telf: [1, 2, 14, 17], // Телефон
				CmpCallerType_id: [1, 2, 4, 9, 14, 17], // Кто вызывает
				CmpReason_id: [1, 2, 3, 4, 9, 14, 17, 19], // Повод вызова
				//CmpReason_Name: [1, 2, 4, 9], // Повод вызова
				Person_Surname: [1, 2, 4, 9, 19], // Фамилия
				Person_Birthday_YearAge: [1, 2, 4, 9, 14, 19], // Возраст
				ageUnit: [1, 2, 4, 9, 14, 19], // Ед.измерения возраста
				Sex_id: [1, 2, 4, 9, 19], // Пол
				//lpuLocalCombo: [1, 2, 4, 9, 14, 17], // «МО передачи» или «Подстанция СМП» (НМП)
				CmpCallCard_rid: [14, 17], // «Первичное обращение» (Родительский вызов)
				CmpCallCard_DayNumberRid: [14, 17] // «Первичное обращение» (Родительский вызов номер за день)
			};

			/* Для перми с поводом решение старшего врача обязательны только 2 поля
			* */
			if(selReasonRec && selReasonRec.get('CmpReason_Code')
				&& getRegionNick().inlist(['perm'])
				&& selReasonRec.get('CmpReason_Code').inlist(['02?', '06?', '09?', '10?', '11?', '12?', '13?', '15?', '16?', '40?', '999'])
			){
				baseForm.getFields().each(function (f) {
					f.allowBlank = !(f.name.inlist(['LpuBuilding_id', 'CmpReason_id']));
				});
			}
			else{
				baseForm.getFields().each(function (f) {
					if (f.name in requiredFieldsByCallType){
						f.allowBlank = !(code.inlist(requiredFieldsByCallType[f.name]));
					}

				});

				// Подразделение СМП LpuBuilding_id необязательно для заполнения при типах вызовов "Консультативное", "Справка"," Абонент отключился"
				// Или есть значение в поле НМП
				//Если Вид вызова «Неотложный», то одно из полей («Подразделение СМП» или «МО передачи (НМП)») обязательно для заполнения.
				if(code.inlist(CallTypeCodesWithoutLpuBuilding)){
					lpuBuildingField.allowBlank = true;
					if (!getRegionNick().inlist(['ufa', 'kz'])){
						streetsCombo.allowBlank = true;
					}
				}
				else{
					lpuBuildingField.allowBlank = nmpCall;
					if(cntr.isNmpArm && !getRegionNick().inlist(['ufa', 'kz'])){
						streetsCombo.allowBlank = !streetsCombo.getStore();
					}

				}

				// Телефон CmpCallCard_Telf необязательно для заполнения при типах вызовов Консультативное», «Справка», «Абонент отключился», «Попутный», «Для спец.бр.СМП»
				CmpCallCard_Telf.allowBlank = ('ufa' == getRegionNick() && code.inlist(ufaCallTypeCodesWithoutTelf));

				if(getRegionNick().inlist(['perm', 'ekb'])){
					baseForm.findField('CmpCallCard_Telf').allowBlank = true;
					baseForm.findField('CmpCallerType_id').allowBlank = true;
				};

				if (requiredFieldsByCallType.CmpCallCard_DayNumberRid = 14){
					baseForm.findField('CmpCallCard_DayNumberRid').allowBlank = true;
				}
			};

			cntr.baseForm.isValid();

		}

	},
	/**
	 * Возвращает все элементы формы не прошедшие валидацию
	 * Пример использования:
	 * ```js
	 * cntr.getInvalid()[0].focus();
	 * ```
	 */
	getInvalid: function(){
		var result = [];

		function appendInvalid( items ){
			var cnt = items.length,
				i,
				item;

			for(i=0; i<cnt; i++){
				item=items[i];
				//if ( !(item.allowBlank) && !(item.wasValid) && (typeof item.wasValid !== 'undefined')) {
				if(!item.isValid()){
					result.push(item);
				}

			}
		};

		appendInvalid( this.baseForm.getFields().items );
		return result;
	},
	/**
	 * Проверка на повторный вызов по адресу или персонажу
	 */
	checkRecall: function(type){
		var cntr = this,
			form = this.baseForm,
			params = form.getValues();

		// Существует повторный и дублирующий вызов
		if (!params.Person_Surname || (cntr.oneCheckRecall && cntr.oneCheckDuplicate)) return;

		var CmpCallType_id = form.findField('CmpCallType_id'),
			PrimaryCallType;
		switch (getGlobalOptions().region.nick) {
			case 'ufa':
				PrimaryCallType = 33;
				break;
			case 'pskov':
				PrimaryCallType = 16;
				break;
			default :
				PrimaryCallType = 2;
				break;
		}
		//if (CmpCallType_id.getValue() != PrimaryCallType) {
		//	return;
		//}
		// Устанавливает в поле "Тип вызова": "2. Повторный"
		var setCmpCallTypeCode2 = function(){
			var form = cntr.baseForm,
				CmpCallType_id = form.findField('CmpCallType_id');
			if (CmpCallType_id) {
				// Находим запись соответствующую повторному вызову
				var rec = CmpCallType_id.findRecord('CmpCallType_Code', 2);
				if (rec) {
					CmpCallType_id.setValue(rec.data.CmpCallType_id);
				}
			}
		}
		// теперь условие повторного вызова и по адресу, и по ФИО одновременно.
		// старые условия удалять не стал, а то вдруг передумают
		var type = 'address_person';

		// предотвратим появление двух форм, повторного вызова и дублирующего
		if(cntr.cmpDublicateFlag) return;

		switch(type){
			case 'address':
				/*if (!params.CmpCallCard_Kvar) {
					return;
				}*/

				// Получим нормальное значение для улицы
				var street_rec = this.baseForm.findField('dStreetsCombo').bigStore.findRecord('StreetAndUnformalizedAddressDirectory_id', this.baseForm.findField('dStreetsCombo').getValue());
				if (!street_rec) {
					return;
				}
				params.KLStreet_id = street_rec.get('KLStreet_id');

				Ext.Ajax.request({
					url: '/?c=CmpCallCard4E&m=checkLastDayClosedCallsByAddress',
					params: params,
					success: function(response){
						if(cntr.cmpDublicateFlag) return;
						var data = Ext.JSON.decode(response.responseText);
						if (data.length > 0) {

							cntr.showSelectFirstSmpCallCard(data);
							/*var cmpLastDayClosedWindow = Ext.ComponentQuery.query('window[refId=SmpCallCardCheckLastDayClosedWindow]')[0];
							if(!cmpLastDayClosedWindow.isVisible()){
								cmpLastDayClosedWindow.show({closedCards: data});

								cmpLastDayClosedWindow.on('selectLastDayClosedCall', function (success, pp, rec) {

									var params = {};
									params.CmpCallCard_DayNumberRid = pp.CmpCallCard_DayNumberRid;
									params.typeSave = pp.typeSave;

									if (pp.CmpCallCard_IsDeterior) {
										params.CmpCallCard_IsDeterior = pp.CmpCallCard_IsDeterior;
									}
									if (rec) {
										params.recdata = rec.data;
										params.recdata.CmpCallCard_rid = pp.CmpCallCard_rid;
									}
									if(params.recdata){
										cntr.insertValues(params.recdata);
									}
									setCmpCallTypeCode2();
									cmpLastDayClosedWindow.close();
								});
							}
							*/


						}
					}
				});
				break;

			case 'person':
				if (!params.Person_id) {
					return;
				}

				Ext.Ajax.request({
					url: '/?c=CmpCallCard4E&m=checkLastDayClosedCallsByPersonId',
					params: {
						Person_id: params.Person_id
					},
					success: function(response){
						if(cntr.cmpDublicateFlag) return;
						var data = Ext.JSON.decode(response.responseText);
						if (data.length > 0) {

							cntr.showSelectFirstSmpCallCard(data);
							/*var cmpLastDayClosedWindow = Ext.ComponentQuery.query('window[refId=SmpCallCardCheckLastDayClosedWindow]')[0];

							cmpLastDayClosedWindow.show({closedCards: data});

							cmpLastDayClosedWindow.on('selectLastDayClosedCall', function (success, pp, rec) {

								var params = {};
								params.CmpCallCard_DayNumberRid = pp.CmpCallCard_DayNumberRid;
								params.typeSave = pp.typeSave;

								if (pp.CmpCallCard_IsDeterior) {
									params.CmpCallCard_IsDeterior = pp.CmpCallCard_IsDeterior;
								}
								if (rec) {
									params.recdata = rec.data;
									params.recdata.CmpCallCard_rid = pp.CmpCallCard_rid;
								}
								if(params.recdata){
									cntr.insertValues(params.recdata);
								}
								setCmpCallTypeCode2();
								cmpLastDayClosedWindow.close();
							});
							*/
						}
					}
				});
				break;
			case 'address_person':
				// поиск и по адресу, и по пациенту
				var street_rec = this.baseForm.findField('dStreetsCombo').bigStore.findRecord('StreetAndUnformalizedAddressDirectory_id', this.baseForm.findField('dStreetsCombo').getValue());
				if (!street_rec) return;

				if(street_rec.get('KLStreet_id') != 0)
					params.KLStreet_id = street_rec.get('KLStreet_id');
				else
					params.UnformalizedAddressDirectory_id = street_rec.get('UnformalizedAddressDirectory_id');

				var secondStreetCombo = this.baseForm.findField('secondStreetCombo');

				var secStreetRec = secondStreetCombo.findRecord('StreetAndUnformalizedAddressDirectory_id', secondStreetCombo.getValue());

				if (secStreetRec){
					params.CmpCallCard_UlicSecond = secStreetRec.get('KLStreet_id');
				}

				if (params.CmpCallCard_Kvar || params.CmpCallCard_Dom || params.CmpCallCard_Korp || params.UnformalizedAddressDirectory_id || params.KLStreet_id) {
					Ext.Ajax.request({
						url: '/?c=CmpCallCard4E&m=checkLastDayClosedCallsByAddressAndPersonId',
						params: params,
						success: function(response){
							//if( cmpDublicateWindow.isVisible() ) return;
							var data = Ext.JSON.decode(response.responseText);
							if (data.length > 0) {
								cntr.oneCheckRecall = true;
								cntr.showSelectFirstSmpCallCard(data);
							}
						}
					});
				}
				break;
		}
	},

	/**
	 * Устанавливает подразделение по указанному в форме адресу
	 */
	changeLpuBuildingByFormAddress: function(allRegion){
		var nmpCombo = this.baseForm.findField('selectNmpCombo');
		var smpCombo = this.baseForm.findField('LpuBuilding_id');
		var cityCombo = this.baseForm.findField('dCityCombo');
		var City_id, Town_id, Area_pid;

		//если тип редактирование вызова не по умолчанию выходим
		if(this.baseForm.findField('typeEditCard').getValue() != 'default') return false;

		var street_rec = this.baseForm.findField('dStreetsCombo').bigStore.findRecord('StreetAndUnformalizedAddressDirectory_id', this.baseForm.findField('dStreetsCombo').getValue());
		if (!street_rec) {
			return;
		}

		var house = this.baseForm.findField('CmpCallCard_Dom').getValue();
		/*if (!house) { //скрыл #119331
			return;
		}*/
		if(this.saveAndContinue) return;

		// Данные выбранного города/наспункта
		if (typeof cityCombo.store.proxy.reader.jsonData !== 'undefined' && cityCombo.store.getAt(0)){
			var city = cityCombo.store.getAt(0).data;
			Area_pid = city.Area_pid;
			if(city.KLAreaLevel_id==4){
				Town_id = city.Town_id;
			} else{
				City_id = city.Town_id;
			}
		}

		var me = this;

		if(street_rec.get('UnformalizedAddressDirectory_id')){
			me.baseForm.findField('LpuBuilding_id').setValue(parseInt(street_rec.get('LpuBuilding_id')));
			//return;
		}

		Ext.Ajax.request({
			url: '/?c=TerritoryService&m=getLpuBuildingIdByAddress',
			params: {
				KLStreet_id: street_rec.get('KLStreet_id'),
				house: house,
				building: this.baseForm.findField('CmpCallCard_Korp').getValue(),
				city: ( City_id ) ? City_id : null,
				town: ( Town_id ) ? Town_id : null,
				Area_pid: ( Area_pid ) ? Area_pid : null,
				allRegion: allRegion ? allRegion : null
				// city: this.baseForm.findField('dCityCombo').getValue()
			},
			success: function(response){
				var data = Ext.JSON.decode(response.responseText);
				if (!data.length || !data[0] || !data[0].LpuBuilding_id) {
					if(allRegion || getGlobalOptions().smp_allow_transfer_of_calls_to_another_MO != 1){

					}else{
						me.changeLpuBuildingByFormAddress(true)
					}
					return false;
				}
				me.baseForm.findField('LpuBuilding_id').setValue(parseInt(data[0].LpuBuilding_id));

				me.refreshFieldsVisibility([
					nmpCombo.getName(),
					smpCombo.getName()
				]);
			}
		});
	},

	//региональные костыли
	regionalCrutches:function(){
		var region = getGlobalOptions().region.nick,
			bf = this.baseForm,
			prmDateField = bf.findField('CmpCallCard_prmDate'),
			numDayField = bf.findField('CmpCallCard_Numv'),
			prmTimeField = bf.findField('CmpCallCard_prmTime'),
			numYearField = bf.findField('CmpCallCard_Ngod');

		switch(region){
			case 'ufa' :{
				prmTimeField.disable();
				numDayField.disable();
				numYearField.hide();
				break;
			}
			case 'krym' :
			case 'kz' :
			{
				//уфа хочет читабельными 4 верхних поля?
				//сделано чтобы лихо менять по решению уфимцев
				//да - true
				var readOnlyField = true;
				//prmDateField.setReadOnly(readOnlyField);
				//prmDateField.setReadOnly(readOnlyField);
				//prmDateField.setReadOnly(readOnlyField);
				//prmDateField.setReadOnly(readOnlyField);
				//захотели убрать поле номер за год - пжалуйста
				numYearField.hide();

				break;
			}
			case 'astra' :{
				//астра захотели убрать поле номер за год - пжалуйста
				numYearField.hide();
				//астра захотели убрать поле номер за день - пжалуйста
				numDayField.hide();
				prmTimeField.disable();
				break;
			}
		}
	},
	// выставление фокуса в зависимости от доступности поля Подразделение СМП или НМП
	ufa_changeFocusCrutches: function(){
		var cntr = this;
		if(cntr.baseForm.findField('LpuBuilding_id').disabled) {
			cntr.baseForm.findField('lpuLocalCombo').focus();
		} else {
			cntr.baseForm.findField('LpuBuilding_id').focus();
		}
	},
	getMap: function(){
        var cntr = this,
		storePerson =  Ext.data.StoreManager.lookup('common.DispatcherCallWP.store.Person'),
		globals = getGlobalOptions(),
		bf = this.baseForm;
		lpuLocalCombo = bf.findField('lpuLocalCombo'),
		cityCombo = bf.findField('dCityCombo'),
		streetsCombo = bf.findField('dStreetsCombo'),
		houseNum =  bf.findField('CmpCallCard_Dom'),
		ltdField = bf.findField('CmpCallCard_CallLtd'),
		lngField = bf.findField('CmpCallCard_CallLng');
		if(Ext.ComponentQuery.query('window[refId=swDispatcherCallWorkPlaceMapWindow]').length>0){
			return Ext.ComponentQuery.query('window[refId=swDispatcherCallWorkPlaceMapWindow]')[0];
		}
		var mapWindow = Ext.create('Ext.window.Window', {
			alias: 'widget.cmpCallCardMapWindow',
			refId: 'swDispatcherCallWorkPlaceMapWindow',
			extend: 'Ext.window.Window',
			title: 'Карта',
			height: 600,
			width: 800,
			showCar: false,
			modal: true,
			closeAction: 'hide',
			listeners: {
				'close': function(){
					bf.findField('CmpCallCard_Kvar').focus(true, 500);
				}
			},
			layout: {
				type: 'fit'
			},
			maximizable: true,
			items:[ Ext.create('sw.MapPanel',{
					layout: {
						align: 'stretch',
						pack: 'center',
						type: 'hbox'
					},
					callMarker: null,
					// @todo Проверить необходимость использования карт Google для ДВ.
					defMapType: 'yandex',
					typeList: ['yandex'],
					addMarkByClick: true,
					toggledButtons: true,
					header: false,
					point: null,
					klPoint: {},
					klTextAddtess: {},
					listeners: {
						mapClick: function(e){

							var mapWrapper = this;

                            mapWrapper.removeMarkers([mapWrapper.findMarkerBy('type', 'placementCursor')]);

							this.klPoint = {};
							this.klTextAddtess = {};
							this.point = e.point;

							ltdField.setValue(e.point[0]);
							lngField.setValue(e.point[1]);

							this.getAddressFromLatLng(e.point, function(results){
								var reqestdata = {};
								if(results.countryShortName.toLowerCase()!='ru' && getRegionNick() !=='kz'){
									Ext.MessageBox.alert('Ошибка', 'Адрес за пределами РФ');
								}
								if(getRegionNick() ==='kz' && results.countryShortName.toLowerCase()!='kz' ){
									Ext.MessageBox.alert('Ошибка', 'Адрес за пределами Казахстана');
								}

								for (var key in results) {
									switch(key){
										//case 'streetNum' : {reqestdata.streetNumber = results[key] ;break;}
										case 'streetLongName' : {reqestdata.streetName = results[key] ;break;}
										//case 'regionName' : {reqestdata.regionName = results[key] ;break;}
										case 'areaShortName' : {reqestdata.cityName = results[key] ;break;}
										//case 'areaShortName': {reqestdata.areaShortName = results[key] ;break;}
										//case 'cityShortName' : {reqestdata.cityShortName = results[key] ;break;}
									}
								}

								var Mask = new Ext.LoadMask(mapWindow, {msg: "Пожалуйста, подождите"});
								Mask.show();

								Ext.Ajax.request({
								url: '/?c=CmpCallCard4E&m=getUnformalizedAddressStreetKladrParams',
								params: reqestdata,
									callback: function(opt, success, response) {
										if (success){
											var res = Ext.JSON.decode(response.responseText);

											if (res[0]){
												var data = res[1][0];

												//bf.findField('Street_id').setValue(data.KLStreet_id);
												bf.findField('Area_id').setValue(data.KLArea_id);
												bf.findField('CmpCallCard_Dom').setValue(results.streetNumber);
												bf.findField('CmpCallCard_Korp').setValue(results.buildingNumber);

												//cityCombo.setRawValue(data.KLSocr_Nick+' '+data.KLArea_Name);

                                                cityCombo.getStore().proxy.extraParams = {
                                                    region_id: getGlobalOptions().region.number,
                                                    KLCity_id:null,
                                                    KLTown_id: data.KLArea_id
                                                };

                                                cityCombo.store.load({
                                                    callback: function(){

                                                        cityCombo.setValue(+data.KLArea_id);

                                                        streetsCombo.bigStore.getProxy().extraParams = {
                                                            town_id: data.KLArea_id,
                                                            Lpu_id: sw.Promed.MedStaffFactByUser.current.Lpu_id
                                                        };

                                                        streetsCombo.bigStore.load({
                                                            callback: function (rec, operation, success) {

                                                                var rec = streetsCombo.bigStore.findRecord('KLStreet_id', data.KLStreet_id);

                                                                if (rec) {
																	streetsCombo.reset();
                                                                    streetsCombo.store.removeAll();
                                                                    streetsCombo.store.add(rec);
                                                                    streetsCombo.setValue(data.KLStreet_id);
                                                                }
                                                            }
                                                        });
                                                    }
                                                });

                                                /*
												streetsCombo.store.removeAll();
												streetsCombo.store.add(streetsCombo.bigStore.query('KLStreet_id', data.KLStreet_id, true, false, true).items);
												var rec = streetsCombo.getStore().findRecord('UnformalizedAddressDirectory_id',0);
												if (rec) {
													streetsCombo.setValue(rec.get(streetsCombo.valueField));
												} else {
													//Пусть пока будет так, хотя маловероятно
													streetsCombo.focus();
													streetsCombo.setRawValue(data.KLStreet_Name);
													streetsCombo.onTypeAhead();
												}
*/
												var marker = [{
													point:e.point,
													baloonContent:'Возможное местоположение',
													imageHref: '/img/googlemap/firstaid.png',
													imageSize: [30,35],
													imageOffset: [-16,-37],
													additionalInfo: {type:'placementCursor'},
													center: true
												}];
												mapWrapper.setMarkers(marker);
											}
											else{
												Ext.Msg.alert('Ошибка', 'Невозможно определить адрес')
												function hide_message() {
													Ext.defer(function() {
														Ext.MessageBox.hide();
													}, 3500);
												}
												hide_message();
											}
											Mask.hide();
										}
									}
								});

							})

						},

						afterMapRender: function(){
							var rawPlace = cityCombo.getRawValue()+' '+streetsCombo.getRawValue()+' '+houseNum.getValue(),
								mapWrapper = this,
								currMarker = mapWrapper.getCurrentMarker();

							if(currMarker){mapWrapper.removeMarkers([currMarker])};

							if( streetsCombo.store.getCount() > 0 ){
								var rec, lat, lng;

								rec = streetsCombo.bigStore.findRecord('StreetAndUnformalizedAddressDirectory_id', streetsCombo.getValue());
								if (rec){
									lat = rec.get('lat'),
									lng = rec.get('lng');
								}
								else{
									//Ext.Msg.alert('Координаты не найдены', 'Местоположение не определено.');
								}

								if( lat && lng ){
									//неформ адрес
									var marker = [{
										point:[lat,lng],
										baloonContent:'Возможное местоположение',
										imageHref: '/img/googlemap/firstaid.png',
										imageSize: [30,35],
										imageOffset: [-16,-37],
										additionalInfo: {type:'placementCursor'},
										center: true
									}];
									mapWrapper.setMarkers(marker);
								}
								else{
									//обычный адрес
									this.geocode(rawPlace, function(coords)
									{
										if(!coords || !Array.isArray(coords)){
											Ext.Msg.alert('Координаты не найдены', 'Местоположение не определено.');
										}

										var marker = [{
											point:[coords[0],coords[1]],
											baloonContent:'Возможное местоположение',
											imageHref: '/img/googlemap/firstaid.png',
											imageSize: [30,35],
											imageOffset: [-16,-37],
											additionalInfo: {type:'placementCursor'},
											center: true
										}]

										mapWrapper.setMarkers(marker);
									})
								}
							}
							else{
								//Ext.Msg.alert('Координаты не найдены', 'Местоположение не определено.');
							}
						}
					}
				})
				]
			/*items:[
				this.mapContainer
			]*/
		});

		return mapWindow;
		//mapWindow.show();
	},

	saveCallAudio: function(CmpCallCard_id){
		var cntr = this;
		
		var activeWin = Ext.WindowManager.getActive();
		
		if (
			(cntr.armWindow !== activeWin) ||
			(activeWin.cfg && activeWin.cfg.refId && activeWin.cfg.refId !== 'globalExitMsg')
		){
			Ext.getCmp('saveBtn').enable();
			Ext.ComponentQuery.query('[refId=saveContinueBtn]')[0].enable();
			return false
		}

	//	var Mask = new Ext.LoadMask(Ext.getCmp('DispatchCallWorkPlace'), {msg: "Пожалуйста, подождите, идет сохранение аудиозвонка"});
	//	Mask.show();

		var messageBox = Ext.ComponentQuery.query('messagebox'),
			messageBoxYesBtn = Ext.ComponentQuery.query('messagebox button[itemId=yes]');

		if(messageBox && messageBox[0] && messageBox[0].isVisible() && messageBoxYesBtn && messageBoxYesBtn[0]){
			messageBoxYesBtn[0].disable();
		};

		cntr.getLpuBuildingByCurrentMedService(function(LpuBuilding_id){
			function blobToDataURL(blob, callback) {
				var a = new FileReader();
				a.onload = function (e) {
					callback(e.target.result);
				}
				a.readAsDataURL(blob);
			};
			
			cntr.recorder.stop();
			
			cntr.recorder.getMp3Blob(function (blob) {
				if(blob.size<50000){
					
					if(messageBox && messageBox[0] && messageBox[0].isVisible() && messageBoxYesBtn && messageBoxYesBtn[0]){
						messageBoxYesBtn[0].enable();
					};
					
				//	Mask.hide();
					
					return false;
				};

				var blobUrl = window.URL.createObjectURL(blob);
					
				blobToDataURL(blob, function(url){
					
					Ext.Ajax.request({
						url: '/?c=CmpCallCard4E&m=saveCallAudio',
						params: {
							callAudio: url,
							CmpCallCard_id: CmpCallCard_id?CmpCallCard_id:null,
							LpuBuilding_id: LpuBuilding_id
						},
						callback: function(opt, success, response) {
							//Mask.hide();
							if(messageBox && messageBox[0] && messageBox[0].isVisible() && messageBoxYesBtn && messageBoxYesBtn[0]){
								Ext.getCmp('saveBtn').enable();
								Ext.ComponentQuery.query('[refId=saveContinueBtn]')[0].enable();
								messageBoxYesBtn[0].enable();
							};
						}
					});
					//console.log('url', url);
				});
			}, function (e) {
				alert('We could not retrieve your message');
				Ext.getCmp('saveBtn').enable();
				Ext.ComponentQuery.query('[refId=saveContinueBtn]')[0].enable();
			});
		});

	},

	checkDublicateByAddressForUfa: function() {
		if (getRegionNick() != 'ufa') return;
		var cntr = this;

		setTimeout(function(){
			var fieldList = Ext.ComponentQuery.query('swDispatcherCallWorkPlace field[mainAddressField=true]');
			var hasFocus = fieldList.some(function(field) {return field.hasFocus});

			if (!hasFocus) {
				cntr.checkDuplicateByAddress();
				// вынес проверку полсе проверки дублирующего вызова
				// cntr.checkRecall('address');
			}
		}, 100);
	},

    init: function() {

		var cntr = this;

		window.onbeforeunload = function (evt) {
			var message = "Document 'foo' is not saved. You will lost the changes if you leave the page.";
			if (typeof evt == "undefined") {
				evt = window.event;
			}
			if (evt) {
				evt.returnValue = message;
			}

			cntr.saveCallAudio();
			return message;
		}



        this.control({
			'boundlist': {
				beforeshow: function(picker){
					var w = Ext.WindowManager.getActive();
					if(picker.up('window') && picker.up('window').id !=w.id && w.xtype != 'quicktip'){return false;}
				}
			},

			'swDispatcherCallWorkPlace': {
				beforerender: function(cmp){

					//прикручиваем ноджс
					connectNode(cmp);

					//прикручиваем аудиозапись
					cntr.recorder = new MP3Recorder({ bitRate: 128 });
				},
				render: function(cmp){
					var me = cmp,
						baseForm = me.down('form').getForm(),
						storePerson =  Ext.data.StoreManager.lookup('common.DispatcherCallWP.store.Person'),
						globals = getGlobalOptions(),
						lpuLocalCombo = baseForm.findField('lpuLocalCombo'),
						cityCombo = baseForm.findField('dCityCombo'),
						streetsCombo = baseForm.findField('dStreetsCombo'),
						houseNum =  baseForm.findField('CmpCallCard_Dom'),
						saveBtn = cmp.down('button[refId=saveBtn]');
						//mask = new Ext.LoadMask(me, {msg: "Пожалуйста, подождите..."});


					// Блок для сохранения времени на сервере, так как клиент не идеален
					var params = {};
					params.callback = function(response){
						var dt;
						if(!Ext.isDate(Ext.Date.parse(response.date+' '+response.time,'d.m.Y H:i'))){
							dt = new Date;
						}
						else
							dt = Ext.Date.parse(response.date+' '+response.time,'d.m.Y H:i')
						cntr.serverTime = dt;
						var timerId = setInterval(function() {
							var t = cntr.serverTime;
							t.setSeconds(t.getSeconds() + 2); // снизим нагрузку хотя бы в два раза
							cntr.serverTime = t;
						}, 2000);
					};
					getCurrentDateTime(params);

					cntr.armWindow = cmp;
					cntr.isNmpArm = cmp.isNmpArm;
					cntr.storePerson = storePerson;
					cntr.baseForm = baseForm;
					cntr.win = me;

					cntr.mapWindow = cntr.getMap();
					cntr.mapWindow.down('swmappanel').showDefaultMap()//.getPanelByType('google').loadMap();

					cntr.personSearchByAddressStore = Ext.create('Ext.data.Store', {
						extend: 'Ext.data.Store',
						storeId: 'personSearchByAddress',
						autoLoad: false,
						stripeRows: true,
						fields: [
							{name: 'Person_id', type: 'int'},
							{name: 'Address_Full', type: 'string'},
							{name: 'Person_SurName', type: 'string'},
							{name: 'Person_FirName', type: 'string'},
							{name: 'Person_SecName', type: 'string'},
							{name: 'Polis_Ser', type: 'string'},
							{name: 'Polis_Num', type: 'string'},
							{name: 'Polis_EdNum', type: 'string'},
							{name: 'Person_Age', type: 'string'},
							{name: 'PersonBirthDay_BirthDay', type: 'string'},
							{name: 'Person_isOftenCaller', type: 'string'},
							{name: 'Person_deadDT', type: 'string'},
							{name: 'Lpu_Nick', type: 'string'},
							{name: 'CmpLpu_id', type: 'int'},
							{name: 'Sex_id', type: 'int'},
							{name: 'countCallCards',type: 'int'}
						],
						proxy: {
							type: 'ajax',
							url: '/?c=Person4E&m=getPersonByAddress',
							reader: {
								type: 'json',
								successProperty: 'success',
								root: 'data'
							},
							actionMethods: {
								create : 'POST',
								read   : 'POST',
								update : 'POST',
								destroy: 'POST'
							}
						},
						listeners: {
							beforeload: function(store, operation) {
								store.lastOperation = operation;
							}
						}
					});

					cntr.TreeData = null;
					cntr.TreeStore = Ext.create('Ext.data.TreeStore',{
						idProperty: 'AmbulanceDecigionTree_id',
						storeId: 'desigionTreePreStore',
						fields: [
							{name: 'AmbulanceDecigionTree_id', type: 'int'},
							{name: 'AmbulanceDecigionTree_nodeid', type: 'int'},
							{name: 'AmbulanceDecigionTree_nodepid', type: 'int'},
							{name: 'AmbulanceDecigionTree_Type', type: 'int'}, //1 - вопрос, 2 - ответ
							{name: 'AmbulanceDecigionTree_Text', type: 'string'},
							{name: 'CmpReason_id', type: 'int'},
							{name: 'leaf'}
						],
						root:{
							leaf: false,
							expanded: true
						},
						sorters: [{
							property: 'AmbulanceDecigionTree_Text',
							direction: 'ACS'
						}],
						autoLoad:false,
						proxy: {
							limitParam: undefined,
							startParam: undefined,
							paramName: undefined,
							pageParam: undefined,
							type: 'ajax',
							url: '/?c=CmpCallCard&m=getDecigionTree',
							reader: {
								type: 'json'
							},
							actionMethods: {
								create : 'POST',
								read   : 'POST',
								update : 'POST',
								destroy: 'POST'
							}
						},
						listeners: {
							load: function(cmp, node, recs){
								cntr.TreeData = recs;
							}
						}
					});


					var pressedkey = new Ext.util.KeyMap({
						target: cmp.getEl(),
						binding: [
							{
								key: [Ext.EventObject.TAB],
								fn: function(c, evt){
									if(Ext.get('MainForm').el.isMasked()) Ext.ComponentQuery.query('[refId=newCallMessage]')[0].focus()
								}
							},
							{
								//f2
								key: [113],
								fn: function(){
									if(Ext.get('MainForm').el.isMasked()) return false;
                                    cntr.setUnknownPerson();
									//unknowPersonBtn.fireEvent('click');
								}
							},
							{
								//f3
								key: [114],
								fn: function(c, evt){
									if(Ext.get('MainForm').el.isMasked()) return false;
									evt.stopEvent();
                                    cntr.showSearchPersonWnd();
									//evt.stopEvent();
									//searchPersonBtn.fireEvent('click');
									//cntr.typeBlockHide();
								}
							},
							{
								//f4
								key: [115],
								fn: function(){
									if(Ext.get('MainForm').el.isMasked()) return false;
									cntr.getMap().show();
								}
							},
							{
								//f5
								key: [Ext.EventObject.F5],
								fn: function(c, evt){
									if(Ext.get('MainForm').el.isMasked()) return false;
									//if(getRegionNick().inlist(['perm', 'ekb', 'ufa'])) {
										evt.stopEvent();
										cntr.showCmpReasonTree();
									//}
								}
							},
							{
								//f6
								key: [117],
								fn: function(c, evt){
									if(Ext.get('MainForm').el.isMasked()) return false;
									evt.stopEvent();
                                    cntr.showPersonByAddress();
									//personByAddressBtn.fireEvent('click');
									//cntr.typeBlockHide();
								}
							},
							{
								// esc
								key: [Ext.EventObject.ESC],
								fn: function(c, evt){
									cntr.onArmClose(evt);
								}
							},
							{
								// F7
								key: [118],
								fn: function(c, evt){

									if (Ext.ComponentQuery.query('[refId=newCallMessage]')[0]){
										var AcceptCallBtn = Ext.ComponentQuery.query('[refId=AcceptCallBtn]')[0];

										AcceptCallBtn.fireEvent('click', AcceptCallBtn);
										return;
									}

									if(Ext.get('MainForm').el.isMasked()) return false;

									evt.stopEvent();

									//resetForm();
									//cntr.setDefaultValues(extraparams); //переменная не определена


									if(getRegionNick().inlist(['perm'])) {
										return;
									}

									if(getRegionNick().inlist(['ufa', 'krym', 'kz', 'buryatiya'])) {
										var callTypeCombo = cntr.baseForm.findField('CmpCallType_id'),
											callRec = callTypeCombo.getStore().findRecord('CmpCallType_Code',15),
											cityComboVal = cntr.baseForm.findField('dCityCombo').getValue(),
											streetCombo = cntr.baseForm.findField('dStreetsCombo'),
											domFieldVal = cntr.baseForm.findField('CmpCallCard_Dom').getValue(),
											streetComboVal;

										var rec = streetCombo.bigStore.findRecord('StreetAndUnformalizedAddressDirectory_id', streetsCombo.getValue());
										if (rec){ streetComboVal = rec.get('KLStreet_id'); };
										if (callRec) { callTypeCombo.setValue(callRec.data.CmpCallType_id); };
										if(cityComboVal && domFieldVal ){
											cntr.showAddressInfoWin();
										}else{
											streetCombo.focus();
										}
										return;
									}
									evt.stopEvent();
									//cntr.showSelectFirstSmpCallCard();
								}
							},
							{
								key: [Ext.EventObject.F8],
								fn: function(c, evt){

									if (Ext.ComponentQuery.query('[refId=newCallMessage]')[0]){
										var takeCall112 = Ext.ComponentQuery.query('[refId=takeCall112]')[0];

										takeCall112.fireEvent('click', takeCall112);
										return;
									}

									if(Ext.get('MainForm').el.isMasked()) return false;

									/*
									var showCard112 = Ext.getCmp('showCard112');

									if(showCard112.isVisible()){
										showCard112.fireEvent('click');
										evt.stopEvent();
										evt.preventDefault();
									}
									*/									
									var butt_History = Ext.ComponentQuery.query('#historyCalls')[0];
									if ( butt_History && !(butt_History.isDisabled()) ) {
										cntr.showPersonCallsHistory();
										evt.stopEvent();
										evt.preventDefault();
									}									
								}
							},
							{
								key: [Ext.EventObject.F9],
								fn: function(c, evt){
									if(Ext.get('MainForm').el.isMasked()) return false;

									//evt.stopEvent();
									// cntr.getRejectCallCardWnd();

									// выбор первичного вызова
									cntr.showSelectFirstSmpCallCard();
								}
							},
							{
                                //f10
								key: [121],
								alt: false,
								fn: function(c, evt){
									if(Ext.get('MainForm').el.isMasked()) return false;

									var newCallMessage = Ext.ComponentQuery.query('[refId=newCallMessage]');
									if(newCallMessage && newCallMessage[0] && newCallMessage[0].isVisible()){
										return false;
									};
									evt.stopEvent();

									cntr.saveBtnHandler();
								}
							},
							{
                                //ALT + f10
								key: [121],
								alt: true,
								fn: function(c, evt){
									if(Ext.get('MainForm').el.isMasked()) return false;

									evt.stopEvent();

									cntr.saveBtnHandler(true);
								}
							},
							{
								key: [Ext.EventObject.F1],
								fn: function(c, evt){
									ShowHelp(cntr.win.title);
								}
							},
							{
								key: "\c",
								alt:true,
								fn: function(c, evt){
									if(Ext.get('MainForm').el.isMasked()) return false;

									cntr.saveBtnHandler();
								},
								scope: this,
								defaultEventAction: "stopEvent"
							}
						]
				   });
/*
					Ext.fly(lpuLocalCombo.getId()).select('.x-form-trigger-input-cell').createChild('<span id="st" class="small-tip" style="opacity: 1; visibility: hidden;">(прикреплен)</span>')
					if (Ext.fly('st').getStyle('visibility') == 'visible'){
						Ext.fly(lpuLocalCombo.getId()).select('.small-tip').setVisible(false)
					}
*/
					baseForm.findField('CmpCallPlaceType_id').store.on('load',function(){
						var PlaceTypeDefaultCode = 1, // Квартира
							PlaceTypeDefaultValue = baseForm.findField('CmpCallPlaceType_id').store.findRecord('CmpCallPlaceType_Code', PlaceTypeDefaultCode);

						if(PlaceTypeDefaultValue){
							baseForm.findField('CmpCallPlaceType_id').setValue(PlaceTypeDefaultValue.get('CmpCallPlaceType_id'));
						}
					})

					//lpuLocalCombo.disable(true);
					cntr.regionalCrutches();
				},
				show: function(cmp){

					var mainTabPanel = cmp.down('panel[refId=mainTabPanelDW]'),
						baseForm = this.baseForm,
						cityCombo = baseForm.findField('dCityCombo'),
						showByDpMode = (cntr.armWindow.additionalParams && cntr.armWindow.additionalParams.showByDP)?true:false,
						cmpCallsListDW = Ext.ComponentQuery.query('[refId=callsListDW]')[0],
						mainToolbar = Ext.getCmp('Mainviewport_Toolbar'),
						mpfield = (cmpCallsListDW)?cmpCallsListDW.down('swmedpersonalcombo') : false,
						lastArguments = (arguments[0])?arguments[0].lastArguments : false,
						newCallTab = Ext.ComponentQuery.query('[refId=dispatchCallForm]')[0].tab,
						takeCall112Window = Ext.ComponentQuery.query('[refId=takeCall112Window]')[0],
						mask = new Ext.LoadMask(cmp, {msg: "Пожалуйста, подождите..."}),
						params;

					cntr.selected112Cards = null;
					cntr.LpuBuildingOptions = null;
					cntr.isSaveMode = false;
					cntr.oneCheckRecall = false;
					cntr.oneCheckDuplicate = false;
					cntr.setStatusAll112Cards();
					cntr.checkHavingLpuBuilding();
					cntr.TreeStore.load();
					if(baseForm.isFormComponentsLoaded == true){
						mask.show();
					}
					Ext.Ajax.request({
						url: '/?c=CmpCallCard4E&m=getLpuBuildingOptions',
						params: {
							LpuBuilding_id: sw.Promed.MedStaffFactByUser.current.LpuBuilding_id
						},
						callback: function(opt, success, response){

							if(response){
								var response_obj = Ext.JSON.decode(response.responseText);

								if(!response_obj || !response_obj[0]){
									Ext.Msg.alert('Ошибка', 'Не удалось получить настройки подразделения');
									mask.hide();
								}

								if(response_obj[0].SmpUnitParam_IsShowAllCallsToDP == 'true'){
									mpfield.setReadOnly(false);
									mpfield.reset();
								}
								else{
									mpfield.setReadOnly(true);
								}
								// настройки «Использовать микрофон для записи вызова»
								if(response_obj && response_obj[0]){
									cntr.LpuBuildingOptions = response_obj[0];
									if(response_obj[0].LpuBuilding_IsUsingMicrophone){
										cntr.IsUsingMicrophone = (response_obj[0].LpuBuilding_IsUsingMicrophone == 'false') ? false : true;
									}
								}

								if (!params) {
									cntr.checkShowNewCallWindow();
								}
							}
							mask.hide();


						}
					});

					if (lastArguments) {

						/*if (ARMType == 'smpdispatchcall'){
							cntr.reloadDispatcherWorkPlace('newCall');
						}*/

						if(lastArguments.showByDP){
							//вызов из ДП
							//Ext.get('MainForm').el.unmask();
							var w = Ext.ComponentQuery.query('[refId=newCallMessage]')[0];
							if(w) w.close();
							cntr.clearAllFields();
							cntr.armWindow.additionalParams = {
								showByDP: lastArguments.showByDP
							};
							cntr.selectedCards = null;
							showByDpMode = true;

							if(lastArguments.params){
								//вызов с параметрами
								params = lastArguments.params;
								cntr.cmpDublicateFlag = params.cmpDublicateFlag ? true : false;

								if(params.typeEditCard && params.typeEditCard == 'CallUnderControl') {
									var CmpCalls = [];

									cntr.armWindow.BaseForm.el.unmask();
									CmpCalls.push({CallCard_id: params.CmpCallCard_rid});
									cntr.showSelectFirstSmpCallCard(CmpCalls,'CallUnderControl', params.callIsOverdue);
								}

								if(!(takeCall112Window && takeCall112Window.isVisible())) {
									cityCombo.focus(false, 100);
									cityCombo.selectText(0, cityCombo.getRawValue().length);
								}

							}else{
								//новый вызов без параметров
								mainTabPanel.setActiveTab(newCallTab.card);
								if(cmpCallsListDW){
									//Скрывам журнал вызовов
									cmpCallsListDW.tab.hide();
								}
								params = null;
							//	var mask = new Ext.LoadMask(Ext.getCmp('DispatchCallWorkPlace'), {msg: "Пожалуйста подождите"});
								// mask.show();
							}
						}

						if (lastArguments.onSaveByDp) {
							cmp.on('saveByDp', lastArguments.onSaveByDp);
						}

						if (lastArguments.onClose) {
							cmp.on('close', lastArguments.onClose);
						}

						if (lastArguments.formData) {
							//Установка значений при ошибке 901
							params = lastArguments.formData;

							if (params.dCityCombo) {
								params.Town_id = params.dCityCombo;
							}
							if (params.dStreetsCombo) {
								params.StreetAndUnformalizedAddressDirectory_id = params.dStreetsCombo;
							}

							setTimeout(function(){
								bf.clearInvalid(); // костыль, чтобы поле зелёным не светилось :(
							}, 1000);
						}
					}

					if(mpfield && !showByDpMode){
						cmpCallsListDW.tab.show();
						
						mainTabPanel.setActiveTab(newCallTab.card);

						//mpfield.setReadOnly(true);
						mpfield.getStore().on('load', function (store){
							var index = store.findBy(function(rec){return (rec.get('MedPersonal_id') == getGlobalOptions().CurMedPersonal_id)}),
								record = store.getAt(index);

							if(record && mpfield.readOnly)
								mpfield.setValue(record)
							cmpCallsListDW.down('CmpCallsList').searchCmpCalls();
						})
					}

					mainToolbar.down('button[refId=globalExit]').on(
						'click', function(){							
							cntr.saveCallAudio();
						}
					);
					mainToolbar.down('button[refId=buttonChooseArm]').on(
						'click', function(){
							cntr.saveCallAudio();
						}
					);
					mainToolbar.down('button[refId=buttonChooseArm]').on(
						'menushow', function(){
							var activeWin = Ext.WindowManager.getActive();

							if (cntr.armWindow == activeWin){
								cntr.saveCallAudio();
							}
						}
					);

					if (params){
						cntr.setDefaultValues(params);
					} else cntr.setDefaultValues();
					if (ARMType == 'dispnmp') {
						baseForm.findField('lpuLocalCombo').setDisabled(false);
						baseForm.findField('lpuLocalCombo').setValue();
					}
				},
				close: function(cmp){

					if(cntr.selected112Cards)cntr.setStatus112Cards(cntr.selected112Cards, 1);
					
					//сброс параметров
					cmp.setTitle('АРМ диспетчера по приему вызовов');					
					cntr.armWindow.additionalParams = null;
				}
			},
			'swDispatcherCallWorkPlace field[mainAddressField=true]': {
				blur: function(c, e) {
					cntr.checkDublicateByAddressForUfa();
				}
			},
			'swDispatcherCallWorkPlace BaseForm':{
				formLoaded: function () {
					var mainTabPanel = Ext.ComponentQuery.query('[refId=mainTabPanelDW]')[0];

					if(mainTabPanel.getActiveTab().refId == 'dispatchCallForm'){
						cntr.checkShowNewCallWindow();
					}
				}
			},
			'swDispatcherCallWorkPlace combobox[name=dCityCombo]'	:{
				change: function (c, newValue, oldValue, eOpts)
				{
					var lastSel = c.lastSelection;
					c.suspendLayouts();

					if (Ext.isEmpty(newValue)){
						if(!getRegionNick().inlist(['ufa'])) {
							c.reset();
						}
					} else {
						if (newValue.toString().length > 0){
							c.store.getProxy().extraParams = {
								'city_default' : null,
								'region_id' : getGlobalOptions().region.number
							}
						}

						if(getRegionNick().inlist(['ufa']) && lastSel.length > 0 && (!c.store.getCount() || !c.value)){
							c.select(lastSel[0])
						}
					}
					cntr.checkAddreessFindBtn();

				},
				keydown: function(combo, e) {
					if (e.getCharCode() == e.BACKSPACE || e.getCharCode() == e.TAB) {
						if( !combo.getValue() ) {
							combo.lastQuery='';
							combo.clearValue();
							combo.store.clearFilter();
						}
					}
					if(e.getCharCode() == e.BACKSPACE){
						combo.lastQuery='';
						combo.clearValue();
						combo.store.clearFilter();
					}

				},
				keypress: function(c, e, o){
					if ( (e.getKey()==13))
					{
						c.nextNode().focus();
					}
				},
				collapse: function(c, r){

					Ext.defer(function(){
						c.nextNode().focus();
						//cntr.baseForm.findField('dStreetsCombo').focus();
						},600);
				},
				select: function(cmp, recs){
					var town,
						cityRec = recs[0];
					if(cityRec)
						town = cityRec.get('Town_id');
					var streetCombo = cntr.baseForm.findField('dStreetsCombo'),
					    secondStreetCombo = cntr.baseForm.findField('secondStreetCombo');

					streetCombo.store.removeAll();
					streetCombo.bigStore.removeAll();
					streetCombo.reset();

					streetCombo.bigStore.getProxy().extraParams = {
						'town_id' : (town)?town:0,
						'Lpu_id' : sw.Promed.MedStaffFactByUser.current.Lpu_id
					}
					streetCombo.bigStore.load({
                        callback: function(recs){
                        	if (!getRegionNick().inlist(['ufa', 'kz'])) {
								streetCombo.allowBlank = !(recs && recs.length);
							}
								secondStreetCombo.bigStore.loadData(recs);

                        	if (getRegionNick().inlist(['kareliya']) && (cityRec.get('KLAreaLevel_id') == 4)) {
                        		streetCombo.store.loadData(recs);
								streetCombo.expand()
							}
						}
                    });
				},
				blur: function(){
					/*
					все кроме уфы взрываются
					if(!getRegionNick().inlist(['ufa'])) {
						if (!combo.getStore().count()) {
							combo.lastQuery = '';
							combo.clearValue();
							combo.store.clearFilter();
						}
					}
					*/
				}
			},
//			'swDispatcherCallWorkPlace combobox[name=typeOfAddress]'	:{
//				change: function (c, newValue, oldValue, eOpts)
//				{
//					if (!newValue){
//						var streetCombo = cntr.baseForm.findField('dStreetsCombo');
//						streetCombo.bigStore.getProxy().extraParams.UnformalizedAddressType_id = null;
//						streetCombo.bigStore.load();
//					}
//				},
//				select: function(c, r){
//					var streetCombo = cntr.baseForm.findField('dStreetsCombo');
//					//streetCombo.bigStore.filter('UnformalizedAddressType_id', r[0].get('UnformalizedAddressType_id'));
//					streetCombo.bigStore.getProxy().extraParams.UnformalizedAddressType_id = r[0].get('UnformalizedAddressType_id');
//					streetCombo.bigStore.load();
//					//streetCombo.bigStore.load();
//				}
//			},

			'swDispatcherCallWorkPlace combobox[name=dStreetsCombo]'	:{
				collapse: function(c, r){
					var cntr = this;
					cntr.creatingAnObjectSMP(c);
				},
				change: function (c, newValue, oldValue, eOpts)
				{
					c.lastSel = c.lastSelection;

					c.suspendLayouts();

					var cityCombo = cntr.baseForm.findField('dCityCombo');

					if (Ext.isEmpty(newValue)){
						if(!getRegionNick().inlist(['ufa'])) {
							c.reset();
						}
					} else {
						if (cityCombo.getValue() && (c.store.getCount()!=1)) {
							c.store.getProxy().extraParams = {
								'town_id' : cityCombo.getValue(),
								'Lpu_id' : sw.Promed.MedStaffFactByUser.current.Lpu_id
							}
						}
					}
					cntr.checkAddreessFindBtn();

				},
				keypress: function(c, e, o){
					if (e.getKey()==27)
					{
						c.previousNode().focus();
					}
				},
				beforeselect: function(combo, val, index){
					if(val.get('UnformalizedAddressDirectory_id')){
						// если выбран объект
						var CmpCallCard_Comm = Ext.ComponentQuery.query('[refId=CmpCallCard_Comm]')[0];
						var address = val.get('StreetAndUnformalizedAddressDirectory_Name') + ' - ' + val.get('AddressOfTheObject') + ', ' + val.get('UnformalizedAddressDirectory_StreetDom');
						CmpCallCard_Comm.setValue(address);
					}
				},
				keydown: function(combo, e) {
					if (!getRegionNick().inlist(['ufa'])) {
						if (e.getCharCode() == e.BACKSPACE || e.getCharCode() == e.TAB) {
							if (!combo.getValue()) {
								combo.lastQuery = '';
								combo.clearValue();
								combo.store.clearFilter();
							}
						}
						if(e.getCharCode() == e.BACKSPACE){
							combo.lastQuery='';
							combo.clearValue();
							combo.store.clearFilter();
						}
					}
				},
				select: function(cmp, recs){
					var streetRec = recs[0];

					if(!streetRec && cmp.forceSelection){
						cmp.reset();
						return false;
					}

					var cityCombo = cntr.baseForm.findField('dCityCombo'),
						isExtra = cntr.baseForm.findField('CmpCallCard_IsExtra');

					var LpuBuilding_id = streetRec.get('LpuBuilding_id');
					var LpuBuildingField = cntr.baseForm.findField('LpuBuilding_id'),
						CmpCallCard_Dom = cntr.baseForm.findField('CmpCallCard_Dom'),
						secondStreetCombo = cntr.baseForm.findField('secondStreetCombo');

					var KLRgn_id = streetRec.get('KLRgn_id');
					var KLCity_id = streetRec.get('KLCity_id');
					var KLTown_id = streetRec.get('KLTown_id');
					if(KLRgn_id || KLCity_id || KLTown_id){
						// при наличии неформализованного адресса может не быть параметров для загрузки cityCombo
						cityCombo.store.getProxy().extraParams = {
							'KLRgn_id' : streetRec.get('KLRgn_id'),
							'KLCity_id' : streetRec.get('KLCity_id'),
							'KLTown_id' : streetRec.get('KLTown_id')
						};
						cityCombo.store.load({
							callback: function(rec, operation, success){
								if ( this.getCount() != 1 ) {
									return;
								}
								cityCombo.setValue(rec[0].get('Town_id'));

								Ext.defer(function(){

									if(CmpCallCard_Dom.isVisible()) {
										CmpCallCard_Dom.focus();
									}
									else{
										secondStreetCombo.focus();
									}

									cmp.fireEvent('blur', cmp);
								},600);
							}
						});
					}

					if(LpuBuilding_id){
						LpuBuildingField.setValue(LpuBuilding_id);
					}

					if (isExtra.getValue() == 2) {
						cntr.getNmpMedService();
					} else {
						cntr.changeLpuBuildingByFormAddress();
					}
				},
				blur: function(combo,b,c){
					if (!getRegionNick().inlist(['ufa'])) {
						if (!combo.getStore().count()) {
							combo.lastQuery = '';
							combo.clearValue();
							combo.store.clearFilter();
						}
					} else {
						if (!combo.getStore().count() && combo.lastSel) {
							combo.store.add(combo.lastSel);
							combo.select(combo.lastSel);
						}
					}
					combo.inputEl.removeCls( 'x-form-focus' );
				}
			},

			'swDispatcherCallWorkPlace combobox[name=secondStreetCombo]'	:{
				select: function(cmp, recs){
					Ext.defer(function(){
						cntr.baseForm.findField('CmpCallCard_Korp').focus();
						cmp.fireEvent('blur', cmp);
					},600);
				},
				keyup: function(c, e, o){
					cntr.checkCrossRoadsFields(true, e);
				},
				change: function(cmp, newVal, oldVal){
					//cntr.checkCrossRoadsFields(true);
				},
				blur: function(a,b,c){
					cntr.checkCrossRoadsFields();
					cntr.checkDuplicateByAddress();
					var cityCombo = this.baseForm.findField('dCityCombo'),
						streetsCombo = this.baseForm.findField('dStreetsCombo'),
						ltdField = this.baseForm.findField('CmpCallCard_CallLtd'),
						lngField = this.baseForm.findField('CmpCallCard_CallLng'),
						secondStreetCombo =  this.baseForm.findField('secondStreetCombo'),
						rawPlace = cityCombo.getRawValue()+' '+streetsCombo.getRawValue()+' ПЕРЕСЕЧЕНИЕ '+secondStreetCombo.getRawValue(),
						mapWrapper = cntr.mapWindow.down('swmappanel');

						mapWrapper.getCrossRoadsCoords(rawPlace, function(coords){
							if(coords){
								lngField.setValue(coords[0]);
								ltdField.setValue(coords[1]);
							}
						});
				}
			},

			'swDispatcherCallWorkPlace textfield[name=CmpCallCard_Dom]': {
				change: function(field, newValue, oldValue){
                    //cntr.checkCrossRoadsFields(true);
					//cntr.getLpuBuildingByAddress();
					//здесь сбрасываются значения при предустановленных параметрах подстанции СМП / НМП
					//cntr.changeLpuBuildingByFormAddress();
					//this.baseForm.findField('CmpCallCard_Kvar').focus();
					cntr.checkAddreessFindBtn();
				},
				keyup: function(c, e, o){
					cntr.checkCrossRoadsFields(true, e);
				},
				keypress: function(c, e, o){
					if ( e.getKey() == 27 )
					{
						c.previousNode().focus();
					}
					/*
					if ( (e.getKey()==13) )
					{
						c.nextNode().focus();
					}
					*/
				},
				blur: function(){
					var cityCombo = this.baseForm.findField('dCityCombo'),
						streetsCombo = this.baseForm.findField('dStreetsCombo'),
						ltdField = this.baseForm.findField('CmpCallCard_CallLtd'),
						lngField = this.baseForm.findField('CmpCallCard_CallLng'),
						houseNum =  this.baseForm.findField('CmpCallCard_Dom'),
						isExtra = this.baseForm.findField('CmpCallCard_IsExtra').getValue(),
						rawPlace = cityCombo.getRawValue()+' '+streetsCombo.getRawValue()+' '+houseNum.getValue(),
						mapWrapper = cntr.mapWindow.down('swmappanel'),
						ltd, lng;

					//cntr.searchPersonByAddress();
					//бывают случаи когда стор пустой а значение в комбике есть
					var selStreetRec = streetsCombo.bigStore.findRecord('StreetAndUnformalizedAddressDirectory_id', streetsCombo.getValue());
					if (selStreetRec) {
						ltd = selStreetRec.get('lat');
						lng = selStreetRec.get('lng');
						if (ltd && lng) {
							ltdField.setValue(ltd);
							lngField.setValue(lng);
						} else {
							mapWrapper.geocode(rawPlace, function(coords){
								if(Array.isArray(coords)){
									ltdField.setValue(coords[0]);
									lngField.setValue(coords[1]);
								}
							});
						}
						//cntr.showAddressInfoWin();
					}

					if (isExtra == 2) {
						cntr.getNmpMedService();
					} else {
						cntr.changeLpuBuildingByFormAddress();
					}
					cntr.checkDuplicateByAddress();
					cntr.checkCmpIllegelActAddress();
				}
			},
			'swDispatcherCallWorkPlace textfield[name=CmpCallCard_Korp]'	:{
				keypress: function(c, e, o){
					if (e.getKey()==27)
					{
						c.previousNode().focus();
					}
					/*
					if ( (e.getKey()==13) )
					{
						cntr.baseForm.findField('CmpCallCard_Kvar').focus();
						//c.nextNode().focus();
					}
					*/
				},
				blur: function(){
					var cityCombo = this.baseForm.findField('dCityCombo'),
						streetsCombo = this.baseForm.findField('dStreetsCombo'),
						ltdField = this.baseForm.findField('CmpCallCard_CallLtd'),
						lngField = this.baseForm.findField('CmpCallCard_CallLng'),
						houseNum =  this.baseForm.findField('CmpCallCard_Dom'),
						corp = this.baseForm.findField('CmpCallCard_Korp'),
						isExtra = this.baseForm.findField('CmpCallCard_IsExtra').getValue(),
						rawPlace = cityCombo.getRawValue()+' '+streetsCombo.getRawValue()+' '+houseNum.getValue(),
						mapWrapper = cntr.mapWindow.down('swmappanel'),
						checkCorp = corp.getValue()? true: false,
						ltd, lng;

					if (checkCorp) {
						rawPlace = rawPlace + ' к.' + corp.getValue();
					}

					var selStreetRec = streetsCombo.bigStore.findRecord('StreetAndUnformalizedAddressDirectory_id', streetsCombo.getValue());
					if (selStreetRec) {
						ltd = selStreetRec.get('lat');
						lng = selStreetRec.get('lng');
						if (ltd && lng) {
							ltdField.setValue(ltd);
							lngField.setValue(lng);
						} else {
							mapWrapper.geocode(rawPlace, function(coords){
								if(Array.isArray(coords)){
									ltdField.setValue(coords[0]);
									lngField.setValue(coords[1]);
								}
							});
						}
						//cntr.showAddressInfoWin();
					}

					if (isExtra == 2) {
						cntr.getNmpMedService();
					} else {
						cntr.changeLpuBuildingByFormAddress();
					}
					cntr.checkDuplicateByAddress();
					cntr.checkCmpIllegelActAddress();
				}
			},
			'swDispatcherCallWorkPlace textfield[name=CmpCallCard_Kvar]'	:{
				keypress: function(c, e, o){
					if ( e.getKey() == 27 )
					{
						c.previousNode().focus();
					}
				},
				blur: function(){
					cntr.searchPersonByAddress();
					cntr.checkDuplicateByAddress();
					cntr.checkCmpIllegelActAddress();
				}
			},
			'swDispatcherCallWorkPlace textfield[name=CmpCallCard_Podz]'	:{
				keypress: function(c, e, o){
					if ( e.getKey() == 27 )
					{
						c.previousNode().focus();
					}
				},
				blur: function(){
					cntr.checkCmpIllegelActAddress();
				}
			},
			'swDispatcherCallWorkPlace textfield[name=CmpCallCard_Etaj]'	:{
				keypress: function(c, e, o){
					if ( e.getKey() == 27 )
					{
						c.previousNode().focus();
					}
				}
			},
			'swDispatcherCallWorkPlace textfield[name=CmpCallCard_Kodp]'	:{
				keypress: function(c, e, o){
					if ( e.getKey() == 27 )
					{
						c.previousNode().focus();
					}
				},
				blur: function()
				{
					cntr.checkCmpIllegelActAddress();
				}
			},
			'swDispatcherCallWorkPlace textfield[name=Person_Surname]' : {
				blur: function(cmp){
					if (cmp.getValue()){
						this.searchPerson();
						cntr.checkRecall();
					}
				},
				keypress: function(c, e, o){
					if ( e.getKey() == 27 )
					{
						cntr.baseForm.findField('CmpCallCard_Kodp').focus();
					}
					/*
					if ( e.getKey() == 13 )
					{
						c.nextNode().focus();
					}
					*/
				},
				focus: function(cmp, event, eOpts){
					if (cmp.getValue()){
						this.searchPerson();
					}
					cntr.showPersonByAddressGrid();
				},
				change: function (cmp) {
					cntr.revertUnknownByFIO()
				}
			},
			/*
			почему 2 обработчика на name = CmpReason_id?
			'swDispatcherCallWorkPlace hidden[name=CmpReason_id]' : {
				change: function(combo, newValue, oldValue, eOpts){
					this.checkUrgencyAndProfile();
				},
				blur: function(cmp){
				}
			},
			*/
			'swDispatcherCallWorkPlace hidden[name=Person_Age]' : {
				blur: function(combo, newValue, oldValue, eOpts){
//					this.checkUrgencyAndProfile();
				}
			},
			'swDispatcherCallWorkPlace textfield[name=Person_Firname]' : {
				blur: function(cmp){
					if (cmp.getValue()){
						this.searchPerson();
						cntr.checkRecall();
					}
				},
				keypress: function(c, e, o){
					if ( e.getKey() == 27 )
					{
						c.previousNode().focus();
					}
					/*
					if ( (e.getKey()==13) )
					{
						c.nextNode().focus();
					}
					*/
				},
				change: function (cmp) {
					cntr.revertUnknownByFIO()
				}
			},
			'swDispatcherCallWorkPlace textfield[name=Person_Secname]' : {
				blur: function(cmp){
					if (cmp.getValue()){
						this.searchPerson();
						cntr.checkRecall();
					}
				},
				keypress: function(c, e, o){

					if ( e.getKey() == 27 )
					{
						c.previousNode().focus();
					}
					/*
					if ( e.getKey()==13 )
					{
						cntr.baseForm.findField('Person_Birthday_YearAge').focus();
					}
					*/
				},
				change: function (cmp) {
					cntr.revertUnknownByFIO()
				}
			},
			/*'swDispatcherCallWorkPlace datefield[name=Person_Birthday]' : {
				blur: function(cmp){
					if (!cmp.isValid()){
						cmp.reset()
					}
					else{
						if(cmp.getValue())
						this.searchPerson()
					}

				},
				keypress: function(c, e, o){
					if ( e.getKey() == 27 )
					{
						cntr.baseForm.findField('Person_Secname').focus();
					}
					if ( (e.getKey()==13) )
					{
						cntr.baseForm.findField('Sex_id').focus();
					}
				}
			},*/
			'swDispatcherCallWorkPlace numberfield[name=Person_Birthday_YearAge]' : {
				keypress: function(c, e, o){
					if ( e.getKey() == 27 )
					{
						cntr.baseForm.findField('Person_Secname').focus();
					}
					/*
					if ( (e.getKey()==13) )
					{
						cntr.baseForm.findField('ageUnit').focus();
					}
					*/
				},
				change: function(combo, newValue, oldValue, eOpts){
					//this.checkUrgencyAndProfile();
					/*
					if (combo.getValue()){
						this.searchPerson();
					}
					*/
				},
				blur: function(cmp){
					var ageUnit = cntr.baseForm.findField('ageUnit'),
						isExtra = cntr.baseForm.findField('CmpCallCard_IsExtra').getValue();

					this.revertUnknownByFIO();

					if(cmp.getValue() > 1900){
						var personYear = Ext.Date.format(new Date,'Y') - cmp.getValue();
						if(personYear < 0){
							cmp.markInvalid();
							cmp.reset();
						}
						else{
							cmp.setValue(personYear);
							ageUnit.setValue(1);
						}
					}

					switch(ageUnit.getValue()){
						case 1: {
							cmp.setMaxValue(150);
							break;
						}
						case 2: {
							cmp.setMaxValue(12);
							break;
						}
						case 3: {
							cmp.setMaxValue(31);
							break;
						}
						default:{
							cmp.setMaxValue(120);
						}
					};

					cmp.validate();

					ageUnit.fireEvent('change', ageUnit, ageUnit.getValue(), 0, null);

					if(cmp.isValid()){
						this.searchPerson();
					}

					this.checkUrgencyAndProfile();

					if (isExtra == 2) {
						cntr.getNmpMedService();
					}
				}
			},
//			'swDispatcherCallWorkPlace numberfield[name=Person_Age_From]' : {
//				keypress: function(c, e, o){
//					if ( e.getKey() == 27 )
//					{
//						cntr.baseForm.findField('Person_Birthday').focus();
//					}
//					if ( (e.getKey()==13) )
//					{
//						c.nextNode().focus()
//					}
//				}
//				//blur: function(){this.searchPerson()}
//			},
//			'swDispatcherCallWorkPlace numberfield[name=Person_Age_To]' : {
//				keypress: function(c, e, o){
//					if ( e.getKey() == 27 )
//					{
//						c.previousNode().focus();
//					}
//					if ( (e.getKey()==13) )
//					{
//						c.nextNode().focus()
//					}
//				}
//			},


			'swDispatcherCallWorkPlace combobox[name=ageUnit]' : {
				change: function(c, newValue, oldValue, eOpts){
					var yearField = cntr.baseForm.findField('Person_Birthday_YearAge'),
						personBirthdayField = cntr.baseForm.findField('Person_Birthday'),
						count_units = yearField.getValue(),
						now = new Date();

					if(!yearField.getValue()) return;

					switch(newValue){
						case 1: {
							yearField.setMaxValue(150);

							now.setYear(now.getFullYear() - count_units);
							now.setDate(1);
							break;
						}
						case 2: {
							yearField.setMaxValue(12);

							now.setMonth(now.getMonth() - count_units);
							now.setDate(1);
							break;
						}
						case 3: {
							yearField.setMaxValue(31);

							now.setDate(now.getDate() - count_units);
							break;
						}
						default:{
							yearField.setMaxValue(120);
							return;
						}
					}

					personBirthdayField.setValue(cntr.formatDate(now));

					//this.convertPersonBirthdayToAge();
					this.searchPerson();
				},
				blur: function(cmp){
					if(!cmp) cmp = cntr.baseForm.findField('ageUnit');
					if (cmp && !cmp.isValid()){
						cmp.reset()
					}
					else{
						if(cmp.getValue()) {
							this.searchPerson();
							this.checkUrgencyAndProfile();
						}
					}

				},
				collapse: function(cmp, r){
					cmp.fireEvent('blur');
					cntr.baseForm.findField('Sex_id').focus();
				},
				keypress: function(c, e, o){
					if ( e.getKey() == 27 )
					{
						cntr.baseForm.findField('Person_Birthday_YearAge').focus();
					}
					/*
					if ( (e.getKey()==13) )
					{
						this.searchPerson();
						cntr.baseForm.findField('Sex_id').focus();
					}
					*/
				}
			},

			'swDispatcherCallWorkPlace combobox[name=Sex_id]'	: {
                keypress: function (c, e, o) {
                    if (e.getKey() == 27) {
                        cntr.baseForm.findField('ageUnit').focus();
                    }
                },
				change: function(c){
					this.searchPerson();
				},
                /*collapse: function (c, r) {
                    if (!getRegionNick().inlist(['ufa', 'krym', 'kz']))
                        cntr.baseForm.findField('CmpCallType_id').focus();
                    else
                        cntr.ufa_changeFocusCrutches();
                },*/
				focus: function(combo){
					//там у комбика св-во есть autoFilter
					// combo.store.clearFilter();
					combo.expand();
				},
                blur: function(){
                    this.searchPerson();
                }
            },

//			'swDispatcherCallWorkPlace textfield[name=Polis_Ser]' : {
//				blur: function(cmp){
//					if (cmp.getValue())
//					this.searchPerson()
//				},
//				keypress: function(c, e, o){
//					if ( e.getKey() == 27 )
//					{
//						c.previousNode().focus();
//					}
//					if ( (e.getKey()==13) )
//					{
//						c.nextNode().focus()
//					}
//				}
//			},
//			'swDispatcherCallWorkPlace numberfield[name=Polis_Num]' : {
//				blur: function(cmp){
//					if (cmp.getValue())
//					this.searchPerson()
//				},
//				keypress: function(c, e, o){
//					if ( e.getKey() == 27 )
//					{
//						c.previousNode().focus();
//					}
//					if ( (e.getKey()==13) )
//					{
//						c.nextNode().focus()
//					}
//				}
//			},
//			'swDispatcherCallWorkPlace numberfield[name=Polis_EdNum]' : {
//				blur: function(cmp){
//					if (cmp.getValue())
//					this.searchPerson()
//				},
//				keypress: function(c, e, o){
//					if ( e.getKey() == 27 )
//					{
//						c.previousNode().focus();
//					}
//					if ( (e.getKey()==13) )
//					{
//						cntr.baseForm.findField('CmpReason_Name').focus();
//					}
//				}
//			},

			'swDispatcherCallWorkPlace numberfield[name=Polis_Number_fake]' : {
				blur: function(cmp){
					if (cmp.getValue())
					this.searchPerson()
				},
				keypress: function(c, e, o){
					if ( e.getKey() == 27 )
					{
						c.previousNode().focus();
					}
					/*
					if ( (e.getKey()==13) )
					{
						var cmpreasonName = cntr.baseForm.findField('CmpReason_Name');
						if(cmpreasonName.isHidden()){
							cntr.baseForm.findField('CmpReason_id').focus();
						}
						else{
							cmpreasonName.focus();
						}
					}
					*/
				}
			},

			'swDispatcherCallWorkPlace button[name=clearPersonFields]' :{
				click: function(){
					Ext.Ajax.abortAll();
					cntr.storePerson.removeAll();
					//cntr.baseForm.findField('CmpCallCard_YearNumberRid').disable().reset();
					//cntr.baseForm.findField('CmpCallCard_DayNumberRid').disable().reset();
					//cntr.baseForm.findField('CmpCallCard_rid').hide().reset();
					cntr.baseForm.findField('CmpReason_id').reset();
					cntr.baseForm.findField('CmpReason_Name').reset();
					//if(getRegionNick() == 'ufa') {
						cntr.baseForm.findField('CmpCallCard_IsQuarantine').reset();
					//}
					cntr.clearDiagnosesPersonOnDispText();
					this.clearPersonFields(this);
				}
			},
			'swDispatcherCallWorkPlace button[refId=ApplicationCVIBtn]' : {
				click: function() {
					cntr.showApplicationCVI(true)
				}
			},
/*
			'swDispatcherCallWorkPlace button[name=searchPersonBtn]' :{
				click: function(){
					Ext.Ajax.abortAll();
					Ext.create('sw.tools.subtools.swPersonWinSearch',
					{
						personform: cntr.baseForm,
						storePerson: cntr.storePerson,
						forObject: 'CmpCallCard',
						callback: function(person_data, success, response) {
							cntr.findWindow = true;

							cntr.baseForm.findField('Person_Surname').focus(false, 500);
							cntr.clearPersonFields();
							if(person_data)
								cntr.setPatient(person_data,false);
							//cntr.checkDuplicateByFIO();
							//cntr.checkRecall('person');
						}
						//caller: this
					}).show()
				}
			},
			*/
            /*
			'swDispatcherCallWorkPlace button[name=unknowPersonBtn]' :{
				click: function(){
					Ext.Ajax.abortAll();
					cntr.storePerson.removeAll();
					this.clearPersonFields();

					var f = cntr.baseForm.findField('Person_Surname'),
						i = cntr.baseForm.findField('Person_Firname'),
						o = cntr.baseForm.findField('Person_Secname'),
						s = cntr.baseForm.findField('Sex_id');

					f.setValue('НЕИЗВЕСТЕН');
					i.setValue('НЕИЗВЕСТЕН');
					o.setValue('НЕИЗВЕСТЕН');
					this.isUndefinded = true;
					s.setValue(3);
					//f.disable();
					//i.disable();
					//o.disable();
					cntr.baseForm.findField('Person_Birthday_YearAge').focus();
				}
			},

			'swDispatcherCallWorkPlace button[name=personByAddressBtn]' :{
				click: function(){
					var personsByAddressStore = Ext.data.StoreManager.lookup('personSearchByAddress'),
						loadMask = new Ext.LoadMask(Ext.getCmp('DispatchCallWorkPlace'), {msg: "Поиск людей, проживающих по адресу..."});

					if(personsByAddressStore.count() == 0){
						loadMask.show();
						cntr.searchPersonByAddress();
						cntr.personSearchByAddressStore.load({
							callback:function(recs){
								loadMask.hide();
								var ContainerEl = Ext.getCmp('DispatchCallWorkPlace').el,
									messageWin = Ext.getCmp('noPersonMessage');
								if(recs && recs.length == 0 && !messageWin){
									var alertBox = Ext.create('Ext.window.Window', {
										title: 'Сообщение',
										height: 50,
										width: 250,
										id: 'noPersonMessage',
										constrain: true,
										header: false,
										constrainTo: ContainerEl,
										layout: {
											type: 'hbox',
											align: 'middle'
										},
										bodyBorder: false,
										items: [
											{
												xtype: 'label',
												flex: 1,
												html: "Люди, проживающие по адресу вызова, не найдены"
											}
										]
									});
									alertBox.showAt([ContainerEl.getWidth()-ContainerEl.getLocalX()-250, ContainerEl.getHeight()-40]);

									setTimeout(function(){alertBox.close()},3000)
								}else{
									cntr.showPersonByAddressGrid();
								}

							}
						});
					}else{
						cntr.showPersonByAddressGrid();
					}


				}
			},
			*/
			'swDispatcherCallWorkPlace button[name=mapBtn]' :{
				click: function(){
					cntr.getMap().show();
				}
			},
			'swDispatcherCallWorkPlace button[name=showCard112]' :{
				click: function(){
					var callcard112 = Ext.create('sw.tools.swCmpCallCard112',{
						view: 'view',
						card_id: cntr.selectedCards[0]
					});
					callcard112.show();
				}
			},
			'swDispatcherCallWorkPlace textfield[name=CmpReason_Name]' : {
				blur: function(cmp){
					//this.selectLpuTransmit()
				},
				change: function(){
					//this.selectLpuTransmit()
				},
				keypress: function(c, e, o){
					if ( e.getKey() == 27 )
					{
						c.previousNode().focus();
					}
					/*
					if ( (e.getKey()==13) )
					{
						c.nextNode().focus()
					}
					*/
				},
				focus: function(cmp){
					if(!cmp.getValue())
						this.showCmpReasonTree();
				}
			},
			'swDispatcherCallWorkPlace textfield[name=CmpReason_id]' : {
				blur: function(cmp){
					//this.selectLpuTransmit();
					this.checkUrgencyAndProfile();
				},
				change: function(cmp,val){
					if(val){
						cntr.refreshFieldsVisibility('UnformalizedAddressDirectory_wid');
					}

					//if(getRegionNick() === 'ufa' ) {
						var isQuarantineField = cntr.baseForm.findField('CmpCallCard_IsQuarantine'),
							callTypeField = cntr.baseForm.findField('CmpCallType_id');
						if( cmp.isCVIReason() ) {
							var isNewOrRepeatCall = callTypeField.isCodeIn([1,2]);
							!isNewOrRepeatCall || isQuarantineField.setValue(true);
							isQuarantineField.setDisabled(isNewOrRepeatCall);
							cntr.showApplicationCVI(isNewOrRepeatCall);
						}
						else {
							cntr.hideApplicationCVI();
						}
					//}
				},
				keypress: function(c, e, o){
					if ( e.getKey() == 27 )
					{
						c.previousNode().focus();
					}
					/*
					if ( (e.getKey()==13) )
					{
						c.nextNode().focus()
					}
					*/
				},
				expand: function(){
				},
				focus: function(cmp, evt, opts){
					if(!cmp.getValue()){
						cntr.showCmpReasonTree();
					};

				}
			},
			'swDispatcherCallWorkPlace combobox[name=lpuLocalCombo]' : {
				change: function(c, newValue){

					var baseForm = cntr.baseForm;
					if(newValue){
						baseForm.findField('LpuBuilding_id').clearValue();
						baseForm.findField('Lpu_smpid').clearValue();
						// CmpCallCard_IsPassSSMP.setValue(false);
						baseForm.findField('CmpCallCard_IsPassSSMP').setValue(false);
						if(cntr.isNmpArm){
							cntr.getCmpCallCardNumber(newValue);
						}

					}

				},
				select: function(cmb, recs){
					var baseForm = cntr.baseForm,
						selectNmpCombo = baseForm.findField('selectNmpCombo');

					if(recs[0]){
						selectNmpCombo.getStore().getProxy().extraParams = {
							'Lpu_ppdid': recs[0].get('Lpu_id'),
							'isClose': 1
						};
                        selectNmpCombo.reset();
						selectNmpCombo.getStore().load();
					}

					cntr.refreshFieldsVisibility(['selectNmpCombo']);
					/*
					var storePerson = cntr.storePerson,
						baseForm = cntr.baseForm,
						//нмп
						lpuLocalCombo = baseForm.findField('lpuLocalCombo'),
						//смп
						lpuBuildingField = baseForm.findField('LpuBuilding_id');

					lpuLocalCombo.enable(true);
					lpuBuildingField.reset();
					lpuBuildingField.disable();
					*/
				},
				keypress: function(c, e, o){
					if ( e.getKey() == 27 )
					{
						c.previousNode().focus();
					}
					if ( (e.getKey()==13) )
					{
						Ext.getCmp('saveBtn').focus();
					}
				},
				collapse: function(c, r){
					//Ext.getCmp('saveBtn').focus();
				},
				enable: function(){

					cntr.setAllowBlankDependingCallType();
				},
				blur: function(cmp){
					if(!cmp.findRecordByValue(cmp.getValue())){
						cmp.reset()
					}
					cntr.refreshFieldsVisibility(['selectNmpCombo']);
				},
				focus: function(combo){
					//combo.store.clearFilter();
					combo.expand();
				}
			},
/**/		'swDispatcherCallWorkPlace textfield[name=CmpCallCard_Ktov]' : {
				keypress: function(c, e, o){
					if ( e.getKey() == 27 )
					{
						if(c.previousNode().disabled) cntr.baseForm.findField('CmpReason_Name').focus();
						else c.previousNode().focus();
					}
					/*
					if ( (e.getKey()==13) )
					{
						c.nextNode().focus()
					}
					*/
				}
			},
			'swDispatcherCallWorkPlace textfield[name=CmpCallCard_Telf]' : {
				keypress: function(c, e, o){
					if ( e.getKey() == 27 )
					{
						c.previousNode().focus();
					}
					/*
					if ( (e.getKey()==13) )
					{
						c.nextNode().focus()
					}
					*/
				}
			},
			'swDispatcherCallWorkPlace textfield[name=CmpCallCard_Comm]' : {
				keypress: function(c, e, o){
					if ( e.getKey() == 27 )
					{
						c.previousNode().focus();
					}
					/*
					if ( (e.getKey()==13) )
					{
						if(getRegionNick().inlist(['ufa', 'krym', 'kz'])) cntr.baseForm.findField('CmpReason_Name').focus();
						else{
							cntr.baseForm.findField('CmpReason_id').focus();
						}
					}
					*/
				}
			},
			'swDispatcherCallWorkPlace combobox[name=CmpCallPlaceType_id]' : {
				keypress: function(c, e, o){
					if ( e.getKey() == 27 )
					{
						c.previousNode().focus();
					}
					/*
					if ( (e.getKey()==13) )
					{
						c.nextNode().focus()
					}
					*/

					//если тип редактирование вызова не по умолчанию выходим
					if(cntr.baseForm.findField('typeEditCard').getValue() != 'default') return false;

					//var k = String.fromCharCode(e.getCharCode());
					//rec = c.store.findRecord('CmpCallPlaceType_Code', k);
					//if (rec){c.setValue(rec.get('CmpCallPlaceType_id'))}

				},
				change: function(combo, newValue, oldValue, eOpts){
					//this.checkUrgencyAndProfile();
				},
				collapse: function(c, r){

				},
				blur: function( combo, evt, eOpts ){
					var val = combo.getValue(),
						rawVal = combo.getRawValue();
					if (val == rawVal) {
						combo.setValue(null);
						this.checkUrgencyAndProfile();
					}
				},
				focus: function(combo){
					combo.store.clearFilter();
					combo.expand();
				}
			},
			'swDispatcherCallWorkPlace combobox[name=CmpCallCard_IsNMP]' : {
				change: function(combo, newValue, oldValue, eOpts){
					if(newValue && newValue.inlist([1,2,3,4])){
						this.setEnabledNmpSmpCombo(newValue);
					}else{
						this.setEnabledNmpSmpCombo(cntr.isNmpArm ? 2 : 1);
					}
				}
			},
			'swDispatcherCallWorkPlace combobox[name=CmpCallCard_IsExtra]' : {
				change: function(combo, newValue, oldValue, eOpts){
					if(newValue && newValue.inlist([1,2,3,4])){
						this.setEnabledNmpSmpCombo();
					}else{
						this.setEnabledNmpSmpCombo(cntr.isNmpArm ? 2 : 1);
					}
					if (ARMType == 'dispnmp' && (newValue == 2)) {
						cntr.baseForm.findField('lpuLocalCombo').setDisabled(false);
						cntr.baseForm.findField('lpuLocalCombo').setValue();
					}
				},
				select: function(){
					this.detectActiveCallLpuLocalCombo();
				},
				focus: function(combo){
					var cmpCallTypRec = cntr.baseForm.findField('CmpCallType_id').getSelectedRecord();

					combo.store.clearFilter();

					if(cmpCallTypRec && cmpCallTypRec.get("CmpCallType_Code").inlist([4,19])){
						cntr.baseForm.findField('CmpCallCard_IsExtra').store.filterBy(function(rec){
							return (rec.get('CmpCallTypeIsExtraType_id').inlist([2, 3]));
						});
					};

					combo.expand();
				}
			},
			'swDispatcherCallWorkPlace checkbox[name=CmpCallCard_IsPoli]' : {
				change: function(c, val){
					cntr.isAutoSelectNmpSmp = false;
					
					var lpuLocalCombo = cntr.baseForm.findField('lpuLocalCombo'),
						selectNmpCombo = cntr.baseForm.findField('selectNmpCombo'),
						CmpCallCard_IsPassSSMP = cntr.baseForm.findField('CmpCallCard_IsPassSSMP'),
						callCardIsExtra = cntr.baseForm.findField('CmpCallCard_IsExtra');

					if(val){
						if (getRegionNick() == 'ufa') {
							if ((callCardIsExtra? callCardIsExtra.getValue(): null) != 2) {
								CmpCallCard_IsPassSSMP.setValue(false);
								lpuLocalCombo.clearValue();
								selectNmpCombo.clearValue();
							}
						} else {
							CmpCallCard_IsPassSSMP.setValue(false);
							lpuLocalCombo.clearValue();
							selectNmpCombo.clearValue();
						}
					}
					else{
						lpuLocalCombo.getStore().getProxy().extraParams = {
							'MedServiceType_id': 18
						};
					}					
					
					lpuLocalCombo.getStore().load();
					cntr.refreshFieldsVisibility(['LpuBuilding_id']);
					/*
					cntr.refreshFieldsVisibility([
						'lpuLocalCombo',
						'selectNmpCombo',
						'LpuBuilding_id'
					]);
					*/
				}
			},
			'swDispatcherCallWorkPlace combobox[name=selectNmpCombo]' : {
				select: function(){
					cntr.isAutoSelectNmpSmp = false;
					cntr.refreshFieldsVisibility(['LpuBuilding_id', 'CmpCallCard_deferred']);
					cntr.setAllowBlankDependingCallType();
				},
				change: function(){
					cntr.isAutoSelectNmpSmp = false;
					cntr.refreshFieldsVisibility(['LpuBuilding_id', 'CmpCallCard_deferred']);
					cntr.setAllowBlankDependingCallType();
				},
				blur: function(cmp){
					if(!cmp.findRecordByValue(cmp.getValue())){
						cmp.reset()
					}
				},
				focus: function(combo){
					combo.store.clearFilter();
					combo.expand();
				}
			},
			'swDispatcherCallWorkPlace combobox[name=CmpCallType_id]' : {
				keypress: function(c, e, o){
					if ( e.getKey() == 27 )
					{
						c.previousNode().focus();
					}
				},
				/*collapse: function(c){
					var CmpReasonNameField = cntr.baseForm.findField('CmpReason_Name');
					if(!getRegionNick().inlist(['ufa', 'krym', 'kz']))
						cntr.ufa_changeFocusCrutches();
					else
					{
						if(!CmpReasonNameField.getValue())
							cntr.baseForm.findField('CmpReason_Name').focus();
					}
				},*/
				select: function(c, r){
					var callback = true;
					if(typeof r !== 'undefined' && typeof r[0].data.CmpCallType_Code !== 'undefined')
					{
						var cmpCallType_code = r[0].data.CmpCallType_Code,
							cmpCallCard_DayNumberRid = cntr.baseForm.findField('CmpCallCard_DayNumberRid'),
							cmpCallCard_rid = cntr.baseForm.findField('CmpCallCard_rid'),
							ridBtn = cntr.baseForm.owner.query('[name=cmpcallcard_ridBtn]')[0];
						
						switch(cmpCallType_code){ // для (кода) типа вызова соответственно "Повторный", "Дублирующий", "Отмена вызова"
							case 2:
								cmpCallCard_DayNumberRid.enable(); // подсвечиваем поле "№ первичного обращения", но все равно оставляем readOnly
								if(!getRegionNick().inlist(['ufa', 'krym', 'kz']))
									cntr.showSelectFirstSmpCallCard();
								else
									cntr.checkRecall('person');
								break;
							//Если Тип вызова – «Попутный»
							case 4:
								cntr.baseForm.findField('CmpCallCard_IsExtra').store.filterBy(function(rec){
									return (rec.get('CmpCallTypeIsExtraType_id').inlist([2, 3]));//Неотложный, Вызов врача на дом

								});
								break;
							case 14:
							case 15:
								//cntr.showAddressInfoWin();
								break;
							case 17:
								cntr.checkDuplicateByAddress(true, callback);
								if(callback)
									cntr.checkDuplicateByFIO(true, callback);
								cmpCallCard_DayNumberRid.enable(); // подсвечиваем поле "№ первичного обращения", но все равно оставляем readOnly
								break;
							case 19:
								cntr.baseForm.findField('CmpCallCard_IsExtra').store.filterBy(function(rec){
									return (rec.get('CmpCallTypeIsExtraType_id').inlist([2, 3]));
								});

								break;
							default:
								cmpCallCard_DayNumberRid.disable().reset();
								cmpCallCard_rid.reset();
						}
						cntr.cmpDublicateFlag = false;

						if ((getRegionNick() == 'kareliya') && !cmpCallCard_rid.getValue()) {
							ridBtn.setVisible(false);
						}
					}
				},
				autoSelect: function(c, r){
					if(typeof r !== 'undefined' && typeof r[0].data.CmpCallType_Code !== 'undefined')
					{
						var //cmpCallType_id = r[0].data.CmpCallType_id,
							cmpCallType_code = r[0].data.CmpCallType_Code;
						if (cmpCallType_code == 2)
						{
							if(!getRegionNick().inlist(['ufa', 'krym', 'kz']))
								this.showSelectFirstSmpCallCard();
	//						this.armWindow.selectFirstCardWin = Ext.create('sw.tools.swSelectFirstSmpCallCard').show();
	//						this.armWindow.selectFirstCardWin.on('selectFirstCard', function(rec){
	//							firstCallField.setValue(rec.get('CmpCallCard_id'));
	//						})
						}
					}
				},
				change: function(t, n, o){
					if(o !== undefined && o !== null){
						cntr.setAllowBlankDependingCallType();
						cntr.refreshFieldsVisibility(['CmpCallCard_deferred'])
					}
					//if( getRegionNick() == 'ufa' ) {
					var quarantineField = cntr.baseForm.findField('CmpCallCard_IsQuarantine'),
						reasonField = cntr.baseForm.findField('CmpReason_id');
					quarantineField.setDisabled(t.isCodeIn([1,2]));
					if (t.isCodeIn([1, 2]) && reasonField.isCVIReason() ) quarantineField.setValue(true);
					//};
				},
				focus: function(combo){
					combo.store.clearFilter();
					combo.expand();
				}
			},
			'button[name=cmpcallcard_ridBtn]': {
				click: function () {
					var card_id = cntr.baseForm.owner.query('field[name=CmpCallCard_rid]')[0].getValue();

					if (!card_id) return;

					cntr.showWndFromExt2('swCmpCallCardNewShortEditWindow',card_id);
				}
			},
			'swDispatcherCallWorkPlace combobox[name=CmpCallerType_id]' : {
				keypress: function(c, e, o){
					//console.log(e.getKey());
					if ( e.getKey() == 27 )
					{
						c.previousNode().focus();
					}
					/*
					if ( (e.getKey()==13) )
					{
						c.nextNode().focus()
					}
					*/
				},
				/*collapse: function(c, r){
					//c.nextNode().focus();
				},
				select: function(c, r, o){
					//console.log('r', r);
					//Ext.defer(function() { c.nextNode().focus(); }, 100);
					//c.nextNode().focus();
				},*/
				change: function(combo, newValue, oldValue, eOpts){
					//this.checkUrgencyAndProfile();
				},
				blur: function(){
					this.checkUrgencyAndProfile();
				},
				focus: function(combo){
					combo.store.clearFilter();
					combo.expand();
				}
			},

			'swDispatcherCallWorkPlace combobox[name=UnformalizedAddressDirectory_wid]' : {
				focus: function(combo){
					combo.store.clearFilter();
					combo.expand();
				}
			},
			'swDispatcherCallWorkPlace combobox[name=LpuBuilding_id]' : {
				keypress: function(c, e, o){
					if ( e.getKey() == 27 )
					{
						c.previousNode().focus();
					}
					/*
					if ( (e.getKey()==13) )
					{
						Ext.getCmp('saveBtn').focus();
					}
					*/
				},
				collapse: function(c, r){
					Ext.getCmp('saveBtn').focus();
				},
				select: function(){
					//var storePerson = cntr.storePerson,
						//baseForm = cntr.baseForm,
						//нмп
						//lpuLocalCombo = baseForm.findField('lpuLocalCombo'),
						//смп
						//lpuBuildingField = baseForm.findField('LpuBuilding_id');

					//lpuLocalCombo.disable(false);
					//lpuLocalCombo.reset();
					//lpuBuildingField.enable();
					cntr.isAutoSelectNmpSmp = false;
					cntr.refreshFieldsVisibility(['selectNmpCombo', 'lpuLocalCombo','LpuBuilding_id']);
				},
				change: function(cmb, val){
					if(!Ext.isEmpty(val)){
						cntr.baseForm.findField('CmpCallCard_IsPoli').setValue(false);
						cntr.baseForm.findField('lpuLocalCombo').clearValue();
					}else{
						cntr.baseForm.findField('CmpCallCard_deferred').setValue(false);
					}
					cntr.isAutoSelectNmpSmp = false;
					if(!cntr.isNmpArm) {
						cntr.refreshFieldsVisibility(['selectNmpCombo', 'lpuLocalCombo', 'LpuBuilding_id', 'CmpCallCard_deferred']);
					}
				},
				focus: function(combo){
					combo.store.clearFilter();
					combo.expand()
				}
			},
			'swDispatcherCallWorkPlace combobox[name=Lpu_smpid]' : {
				focus: function(combo){
					combo.store.clearFilter();
					combo.expand();
				}
			},
			'swDispatcherCallWorkPlace checkbox[name=CmpCallCard_deferred]' : {
				change: function(cmp, newVal){
					cntr.refreshFieldsVisibility([
						'CmpCallCard_storDate',
						'CmpCallCard_storTime',
						'CmpCallCard_defCom'
					]);
					if(newVal){
						cntr.baseForm.findField('CmpCallCard_Numv').reset();
						cntr.baseForm.findField('CmpCallCard_Ngod').reset();
					}else{
						cntr.getCmpCallCardNumber();
					}

					cntr.baseForm.findField('CmpCallCard_storDate').setValue(Ext.Date.format(new Date(), "d.m.Y"));
				}
			},
			'swDispatcherCallWorkPlace checkbox[name=CmpCallCard_IsPassSSMP]' : {
				change: function(cmp, val){
					if(val){
						cntr.baseForm.findField('CmpCallCard_IsPoli').setValue(false);
						cntr.baseForm.findField('lpuLocalCombo').clearValue();
						cntr.baseForm.findField('selectNmpCombo').clearValue();
					}

					cntr.refreshFieldsVisibility(['Lpu_smpid','lpuLocalCombo','selectNmpCombo','LpuBuilding_id']);
				}
			},

			'swDispatcherCallWorkPlace datefield[name=CmpCallCard_prmDate]' : {
				change: function(cmp, n, o){
					if(cntr.baseForm.findField('Person_deadDT').getValue())
					{
						var deadDT = cntr.checkDead(cntr.baseForm.findField('Person_deadDT').getValue());

						if(deadDT && deadDT < cmp.getValue() ){
							Ext.Msg.alert('Ошибка', 'Человек на дату приема вызова является умершим. Выбор даты невозможен.');
							cmp.markInvalid('Человек на дату приема вызова является умершим. Выбор даты невозможен');
							cmp.setValue(Ext.Date.format(o, "d.m.Y"));
							return false;
						}
					}
					cmp.clearInvalid();
				},
				blur: function(){
					cntr.getCmpCallCardNumber()
				}
				/*
				keypress: function(c, e, o){
					if ( e.getKey() == 27 )
					{
						c.previousNode().focus();
					}
					if ( (e.getKey()==13) )
					{
						cntr.baseForm.findField('CmpCallCard_Numv').focus();
					}
				}
				*/
			},
//
//
//			'swDispatcherCallWorkPlace numberfield[name=CmpCallCard_Numv]' : {
//				keypress: function(c, e, o){
//					if ( e.getKey() == 27 )
//					{
//						c.previousNode().focus();
//					}
//					if ( (e.getKey()==13) )
//					{
//						cntr.baseForm.findField('CmpCallCard_prmTime').focus();
//					}
//				}
//			},
//
			'swDispatcherCallWorkPlace datefield[name=CmpCallCard_prmTime]' : {
				blur: function(){
					cntr.getCmpCallCardNumber()
				},
				//keypress: function(c, e, o){
				//	if ( e.getKey() == 27 )
				//	{
				//		c.previousNode().focus();
				//	}
				//	if ( (e.getKey()==13) )
				//	{
				//		cntr.baseForm.findField('CmpCallCard_Ngod').focus();
				//	}
				//}
			},
//
//			'swDispatcherCallWorkPlace numberfield[name=CmpCallCard_Ngod]' : {
//				keypress: function(c, e, o){
//					if ( e.getKey() == 27 )
//					{
//						c.previousNode().focus();
//					}
//					if ( (e.getKey()==13) )
//					{
//						cntr.baseForm.findField('dCityCombo').focus();
//					}
//				}
//			},
			'swDispatcherCallWorkPlace button[refId=cancelBtn]' : {
				click: function(c, evt){
					cntr.onArmClose(evt);
				}
			},
			
			// Неизвестный пациент

            'swDispatcherCallWorkPlace button[refId=unknownBtn]' : {
				click: function(){
                    /*
                    var unknowPersonBtn = cntr.win.down('button[name=unknowPersonBtn]');
					unknowPersonBtn.fireEvent('click');
					*/
                    cntr.setUnknownPerson();
				}
			},

			// Поиск пациента

			'swDispatcherCallWorkPlace button[refId=searchBtn]' : {
				click: function(){
					if (sw.lostConnection) {
						lostConnectionAlert();
						return false;
					}

                    cntr.showSearchPersonWnd();
					/*
                    var searchPersonBtn = cntr.win.down('button[name=searchPersonBtn]');
					searchPersonBtn.fireEvent('click');
					*/
				}
			},

			// карта
			'swDispatcherCallWorkPlace button[refId=mapBtn]' : {
				click: function(){
					cntr.getMap().show();
				}
			},
			
			// поиск по адресу
			'swDispatcherCallWorkPlace button[refId=searchByAddressBtn]' : {
				click: function(){
					if (sw.lostConnection) {
						lostConnectionAlert();
						return false;
					}

                    cntr.showPersonByAddress();
					/*
                    var personByAddressBtn = cntr.win.down('button[name=personByAddressBtn]');
					personByAddressBtn.fireEvent('click');
					*/
				}
			},
			
			// выбор первичного вызова
			'swDispatcherCallWorkPlace button[refId=callSearchBtn]' : {
				click: function(){
					cntr.showSelectFirstSmpCallCard();
				}
			},

			// История обращений
			'swDispatcherCallWorkPlace button[refId=callHistoryBtn]' : {
				click: function(){
					var butt_History = Ext.ComponentQuery.query('#historyCalls')[0];
					if ( butt_History && !(butt_History.isDisabled()) ) {
						cntr.showPersonCallsHistory();
					}
				}
			},
			
			// МО обслуживания
			'swDispatcherCallWorkPlace button[refId=MOserviceBtn]' : {
				click: function(){
					if(getRegionNick().inlist(['ufa','buryatiya'])) {
						var callTypeCombo = cntr.baseForm.findField('CmpCallType_id'),
							callRec = callTypeCombo.getStore().findRecord('CmpCallType_Code',15),
							cityComboVal = cntr.baseForm.findField('dCityCombo').getValue(),
							streetCombo = cntr.baseForm.findField('dStreetsCombo'),
							domFieldVal = cntr.baseForm.findField('CmpCallCard_Dom').getValue(),
							streetComboVal;

						var rec = streetCombo.bigStore.findRecord('StreetAndUnformalizedAddressDirectory_id', streetsCombo.getValue());
						if (rec){ streetComboVal = rec.get('KLStreet_id'); };
						if (callRec) { callTypeCombo.setValue(callRec.data.CmpCallType_id); };
						if(cityComboVal && domFieldVal ){
							cntr.showAddressInfoWin();
						}else{
							streetCombo.focus();
						}
					}
				}
			},

			'swDispatcherCallWorkPlace [refId=CmpCalls112List]' : {
				selectCalls: function(recs){
					var mainTabPanel = cntr.win.down('panel[refId=mainTabPanelDW]'),
						newCallTab = Ext.ComponentQuery.query('[refId=dispatchCallForm]')[0].tab,
						CmpCallCard_ids = [],
						CmpCallCard112_ids = [];
					
					mainTabPanel.setActiveTab(newCallTab.card);
					Ext.ComponentQuery.query('[refId=calls112ListDW]')[0].tab.hide();
					
					for(var i=0; i<recs.length; i++){
						CmpCallCard112_ids.push(recs[i].get('CmpCallCard112_id'));
						CmpCallCard_ids.push(recs[i].get('CmpCallCard_id'));
					}
					
					cntr.setStatus112Cards(CmpCallCard112_ids, 2);
					
					cntr.selected112Cards = CmpCallCard112_ids;
					cntr.selectedCards = CmpCallCard_ids;
					
					cntr.loadCmpCard(recs[0].get('CmpCallCard_id'));
					Ext.getCmp("showCard112").setVisible(true);
					var w = Ext.ComponentQuery.query('[refId=newCallMessage]')[0];
					if(w) w.close();
					//Ext.get('MainForm').el.unmask();
				}
			},

			'swDispatcherCallWorkPlace button[refId=saveBtn]' : {
				focus: function(){
					cntr.saveBtnHandler()

				},
				click: function(){

				}
			},

			'swDispatcherCallWorkPlace button[refId=saveContinueBtn]' : {
				focus: function(){
					cntr.saveBtnHandler(true)

				},
				click: function(){

				}
			},
			'swDispatcherCallWorkPlace #helpBtn' : {
				click: function(){

				}
			}
		})
    },

	ApplicationCVIFields: ['PlaceArrival_id','KLCountry_id','OMSSprTerr_id','ApplicationCVI_arrivalDate','ApplicationCVI_flightNumber','ApplicationCVI_isHighTemperature','Cough_id','Dyspnea_id','ApplicationCVI_isContact','ApplicationCVI_Other', 'isSavedCVI'],
	isEmptyApplicationCVI: function() {
		return this.baseForm.findField('CmpCallCard_IsQuarantine').getValue()
			&& this.baseForm.findField('CmpReason_id').isCVIReason()
			&& !this.baseForm.findField('isSavedCVI').getValue();
	},
	showApplicationCVI: function (showWindow) {
		!showWindow || this.showApplicationCVIWindow();
		this.win.down('[name=CmpCallCard_IsQuarantine]').show();
		this.win.down('[refId=ApplicationCVIBtn]').show();
	},
	hideApplicationCVI: function () {
		this.clearApplicationCVI();
		this.win.down('[name=CmpCallCard_IsQuarantine]').hide();
		this.win.down('[name=CmpCallCard_IsQuarantine]').setValue(false);
		this.win.down('[refId=ApplicationCVIBtn]').hide();
	},
	clearApplicationCVI: function() {
		var cntr = this;
		cntr.ApplicationCVIFields.forEach( function(fieldName) {
			try { cntr.baseForm.findField(fieldName).reset(); }
			catch (e) { log('Поле ' + fieldName + ' не найдено'); }
		});
	},
	showApplicationCVIWindow: function(saveCallback) {
		var cntr = this,
			allParams = cntr.baseForm.getAllValues(),
			formParams = { CmpCallCard_id: allParams['CmpCallCard_id'] };

		cntr.ApplicationCVIFields.forEach( function(fieldName) {
			formParams[fieldName] = allParams[fieldName];
		});

		Ext.create('sw.tools.swApplicationCVIWindow', {
			listeners: {
				saveForm: function (saveParams) {
					cntr.baseForm.setValues(saveParams);
					if (typeof saveCallback === 'function')
						saveCallback();
				}
			}
		}).show(formParams);
	},

	saveCard: function(data, btn, reloadDispatcherWorkPlace, connectedCall, selectHeadDocReason, cb){

		var Mask = new Ext.LoadMask(Ext.getCmp('DispatchCallWorkPlace'), {msg: "Пожалуйста, подождите, идет сохранение"}),
			cntr = this;
			Mask.show();

		cntr.oneCheckRecall = false;
		cntr.oneCheckDuplicate = false;

		if (ARMType.inlist(["dispnmp","dispdirnmp", "dispcallnmp"]) && data.CmpCallType_Code.inlist([6]) && data.CmpCallCard_IsExtra.inlist([1]) && lpuLocalCombo.getValue() == null && !data.MedService_id){
			data.CmpCallCardStatusType_id = 6;
		}

		Ext.ComponentQuery.query('button[refId=saveBtn]')[0].enable();
		Ext.ComponentQuery.query('[refId=saveContinueBtn]')[0].enable();

		//Форма все еще валидна?
		if (!cntr.baseForm.isValid() && !selectHeadDocReason){
			Mask.hide();
			cntr.isSaveMode = false;
			return;
		};

		Ext.Ajax.request({
			url: '/?c=CmpCallCard4E&m=saveCmpCallCard',
			params: data,
			failure: function(response, opts) {
				//console.log('server-side failure with status code ' + response.status);
				Ext.Msg.alert('<div>Ошибка','Соединение с сервером потеряно.</div><div>Ошибка: ' + response.status + ' ' + response.statusText + '</div>');
				Ext.ComponentQuery.query('button[refId=saveBtn]')[0].enable();
				cntr.isSaveMode = false;
				Ext.ComponentQuery.query('[refId=saveContinueBtn]')[0].enable();
				return false;
			},
			callback: function(opt, success, response){
				if ( !success ){
					Mask.hide();
					cntr.isSaveMode = false;
					return;
				}

				var response_obj = Ext.JSON.decode(response.responseText);

				if ( response_obj.Error_Code && response_obj.Error_Code == '901' ) {
					Mask.hide();
					Ext.Msg.show({
						title: 'Подтверждение',
						msg: 'Форма ввода изменилась, необходимо обновить форму, чтобы продолжить сохранение. Обновить?',
						buttons: Ext.Msg.YESNO,
						fn: function(btn) {
							if (btn === 'yes') {
								var formData = cntr.baseForm.getValues();
								cntr.win.refreshCodeWithDependecies(formData);
							}
						},
						scope: this,
						icon: Ext.MessageBox.QUESTION
					});
					return;
				}

				if (response_obj.success) {

					//Сообщение о том, что что-то пошло не так, но это не критично
					//В данном случае произошла подмена параметров
					if(response_obj.saveWarningMsg){
						alert('Внимание: ' + response_obj.saveWarningMsg);
					}

					cntr.saveCallAudio(response_obj.CmpCallCard_id);

					Mask.hide();
					cntr.isSaveMode = false;
					cntr.isUndefinded = false;

					if(ARMType == 'smpheaddoctor') {
						Ext.data.StoreManager.lookup('common.HeadDoctorWP.store.CmpCallsStore').reload()
					}

					var showByDpMode = (cntr.armWindow.additionalParams && cntr.armWindow.additionalParams.showByDP)?true:false;

					var armWindowEl = cntr.armWindow.getEl(),
						alertBox = Ext.create('Ext.window.Window', {
						title: 'Сохранено',
						height: 50,
						width: 300,
						layout: 'fit',
						constrain: true,
						cls: 'waitingForAnswerHeadDoctorWindow',
						header: false,
						constrainTo: armWindowEl,
						layout: {
							type: 'hbox',
							align: 'middle'
						},
						items: [
							{
								xtype: 'label',
								flex: 1,
								html: "<a>Все изменения сохранены</a>"
							},
							{
								xtype: 'button',
								text: 'Закрыть',
								renderTpl: [
									'<span id="{id}-btnWrap" class="{baseCls}-wrap closeBtn',
										'<tpl if="splitCls"> {splitCls}</tpl>',
										'{childElCls}" unselectable="on">',
										'<span id="{id}-btnEl" class="{baseCls}-button">',
											'X',
									'</span>'
								],
								handler: function(){
									alertBox.close();
								}
							}
						]
					});

					alertBox.showAt([armWindowEl.getWidth(), 20]);
					
					if(cntr.armWindow && cntr.armWindow.socket){
						if (data.CmpCallCard_IsDeterior==2 && cntr.deteriorationField.EmergencyTeam_id) {
							// если ухудшение состояния
							var paramsi = {
								//CmpCallCard_id:	allParams.CmpCallCard_rid,
								CmpCallCard_id:	cntr.baseForm.findField('CmpCallCard_rid').getValue(),
								EmergencyTeam_id: cntr.deteriorationField.EmergencyTeam_id,
								CmpCallCard_IsDeterior: data.CmpCallCard_IsDeterior,
								//cmpReason: baseForm.findField('CmpReason_id').getRawValue(),
								cmpReason: cntr.baseForm.findField('CmpReason_id').getRawValue(),
								CmpCallCard_Numv: data.CmpCallCard_Numv,
								FIO: data.Person_SurName + ' ' + data.Person_FirName + ' ' + data.Person_SecName,
								isExtra: cntr.baseForm.findField('CmpCallCard_IsExtra').getValue()
							};

							cntr.armWindow.socket.emit('isDeterior', paramsi, function(data){
								log('NODE emit isDeterior : apk='+data);
							});

							// cntr.armWindow.socket.on('isDeterior', function (data) {
							// 	log('NODE on isDeterior');
							// });
						}else if(cntr.registrationFailure && typeof cntr.registrationFailure == 'object'){
							// при отказе от вызова создается переменная cntr.registrationFailure в которой сохранены параметры для передачи в нод
							// если был отказ от вызова
							cntr.armWindow.socket.emit('registrationFailure', cntr.registrationFailure, function(data){
								log('NODE emit registrationFailure : apk='+data);
							});

							// cntr.armWindow.socket.on('registrationFailure', function(data){
							// 	log('NodeJS ON registrationFailure');
							// });
							cntr.registrationFailure=false;
						}else{
							//log('NODE emit addCall');
							cntr.armWindow.socket.emit('changeCmpCallCard', response_obj.CmpCallCard_id, 'addCall', function(data){
								log('NODE emit addCall : apk='+data);
							});
						}
					}

					if(showByDpMode){
						/*
						var t = Ext.Msg.alert('Сохранение', 'Карта вызова успешно сохранена', function () {
							cntr.armWindow.fireEvent('saveByDp', response_obj.CmpCallCard_id);
						});
						*/
						if(btn == 'no'){ //Сохранить и продолжить при сохранении из ДП
							cntr.reloadDispatcherWorkPlace('saveAndContinue');
						}else{
							cntr.armWindow.fireEvent('saveByDp', response_obj.CmpCallCard_id);
							var grid = Ext.ComponentQuery.query('[id=swDispatcherStationWorkPlace_callsGrid]')[0];
							if(grid) grid.getStore().reload();
						}
						Ext.defer(function() {
							alertBox.hide();
						}, 1000);

						//return false;
					}
					else{
						Ext.defer(function() {
							alertBox.hide();
						}, 1000);
					}

					if(reloadDispatcherWorkPlace){
						if (btn == 'yes') {
							cntr.reloadDispatcherWorkPlace('newCall');
						} else {
							cntr.reloadDispatcherWorkPlace('saveAndContinue');
						}
					}

					//Для множественного вызова сохраним id
					//Если id уже есть - не изменяем
					if(connectedCall){
						if(!cntr.baseForm.findField('CmpCallCard_sid').getValue()){
							cntr.baseForm.findField('CmpCallCard_sid').setValue(response_obj.CmpCallCard_id)
						}
					}else{
						cntr.baseForm.findField('CmpCallCard_sid').reset();
					}

					if(cb){
						cb();
					}

				} else {
					Mask.hide();
					var error_msg = (response_obj.Error_Msg) ? response_obj.Error_Msg : 'Ошибка сохранения карты вызова';
					Ext.Msg.alert('Ошибка', error_msg);
					cntr.isSaveMode = false;
					function hide_message(){
						Ext.defer(function() {
							Ext.ComponentQuery.query('button[refId=saveBtn]')[0].enable();
							Ext.ComponentQuery.query('[refId=saveContinueBtn]')[0].enable();
							Ext.MessageBox.hide();
						}, 3500);
					}
					hide_message();
				}
			}
		});
	},

	creatingAnObjectSMP: function(combo){
		var value = combo.getValue();
		var streetCombo = combo;
		var nameOfTheProperty = combo.lastQuery;
		var flag = streetCombo.getStore().findExact('StreetAndUnformalizedAddressDirectory_Name', nameOfTheProperty, 0);
		var selectStreet = streetCombo.bigStore.findRecord('StreetAndUnformalizedAddressDirectory_id', value);
		
		if(nameOfTheProperty && !selectStreet && flag<0 && !getRegionNick().inlist(['ufa', 'krym'])){
			// создание объекта СМП
			streetCombo.reset();
			combo.lastQuery='';
			var msg = 'Не найдена ни улица, ни объект СМП с таким названием.<br>Создать объект СМП &laquo;<b>'+nameOfTheProperty+'</b>&raquo; ?';
			Ext.Msg.show({
				title: 'Внимание',
				msg: msg,
				buttons: Ext.Msg.YESNO,
				fn: function(btn) {
					var Mask = new Ext.LoadMask(Ext.getCmp('DispatchCallWorkPlace'), {msg: "создание объекта СМП ..."});
					if (btn === 'yes') {
						Mask.show();
						var dirty_records = [];

						var param = {
							UnformalizedAddressDirectory_Name: nameOfTheProperty,
							UnformalizedAddressType_id: 29,
							UnformalizedAddressDirectory_lat: 0,
							UnformalizedAddressDirectory_lng: 0
						}

						dirty_records.push(param);
						Ext.Ajax.request({
							params: {
								addresses: Ext.encode(dirty_records)
							},
							url: '/?c=CmpCallCard4E&m=saveUnformalizedAddress',
							callback: function(options, success, response) {
								if(success) {
									streetCombo.bigStore.load({
										callback: function(rec, operation, success){
											var newObj = streetCombo.bigStore.findRecord('StreetAndUnformalizedAddressDirectory_Name', nameOfTheProperty);
											Mask.hide();
											if(newObj){
												streetCombo.setValue(newObj);
												streetCombo.store.load();
												var MaskSuccess = new Ext.LoadMask(Ext.getCmp('DispatchCallWorkPlace'), {msg: "объект СМП создан"});
												MaskSuccess.show();
												setTimeout(function(){
													MaskSuccess.hide();
												}, 1000);
											}
										}
									});
								}else{
									Mask.hide();
									Ext.Msg.alert('Ошибка','Во время создания объекта СМП произошла ошибка');
								}
							},
							failure: function(){
								Mask.hide();
								Ext.Msg.alert('Ошибка','Во время создания объекта СМП произошла ошибка, обратитесь к администратору');
							}
						});
						
					}
				},
				scope: this,
				icon: Ext.MessageBox.QUESTION
			});
		}
    },
	getCmpCallCardNumber: function(lpu_id){
		var baseForm = this.baseForm,
			prmDate = baseForm.findField('CmpCallCard_prmDate').getRawValue(),
			prmTime = baseForm.findField('CmpCallCard_prmTime').getRawValue(),
		 	CmpCallCard_prmDT = prmDate + " " + prmTime,
			lpu_id = lpu_id ? lpu_id : null;

		if(!Ext.isDate(Ext.Date.parse(CmpCallCard_prmDT,'d.m.Y H:i:s'))){
			return;
		}

		Ext.Ajax.request({
			url: '/?c=CmpCallCard&m=getCmpCallCardNumber',
			params: {
				CmpCallCard_prmDT : CmpCallCard_prmDT,
				Lpu_id: lpu_id
			},
			callback: function(opt, success, response) {
				if (success){
					var res = Ext.JSON.decode(response.responseText),
						num = res[0],
						ccNumWeekCmp = baseForm.findField('CmpCallCard_Numv'),
						ccNumYearCmp = baseForm.findField('CmpCallCard_Ngod');

					ccNumWeekCmp.setValue(num.CmpCallCard_Numv);
					ccNumYearCmp.setValue(num.CmpCallCard_Ngod);
				}
			}
		});
	},

	checkUsingMicrophone: function(){
		var cntr = this;

		if(cntr.IsUsingMicrophone) {
			cntr.recorder.start(function () {}, function () {
				alert('Микрофон не подключен, аудиовызов не может быть записан');
				cntr.recorder.stop();
			});
		}
	},

	// функция отображения и фокусов полей для перекрестков
	// changeFocus - ставить фокус или нет
    checkCrossRoadsFields: function(changeFocus, e) {

		if(e && (e.getCharCode() == e.SHIFT)){return false;}

		var baseForm = this.baseForm,
            cmpCallCard_Dom = baseForm.findField('CmpCallCard_Dom'),
            secondStreetCombo = baseForm.findField('secondStreetCombo'),
			CmpCallCard_Korp = baseForm.findField('CmpCallCard_Korp'),
			CmpCallCard_Kvar = baseForm.findField('CmpCallCard_Kvar'),
			CmpCallCard_Podz = baseForm.findField('CmpCallCard_Podz'),
			CmpCallCard_Etaj = baseForm.findField('CmpCallCard_Etaj'),
			CmpCallCard_Kodp = baseForm.findField('CmpCallCard_Kodp'),
            crossRoadsMode = ((cmpCallCard_Dom.getValue() == '/' && !secondStreetCombo.isVisible()) || (secondStreetCombo.getValue()));

		//начали вводить улицу - слэш удалили
		if(secondStreetCombo.getValue()) cmpCallCard_Dom.reset();

		//проверка на существующий режим
		if((crossRoadsMode && secondStreetCombo.isVisible()) || (!crossRoadsMode && !secondStreetCombo.isVisible())) return;

    	secondStreetCombo.setVisible(crossRoadsMode);
    	cmpCallCard_Dom.setVisible(!crossRoadsMode);

		CmpCallCard_Korp.setVisible(!crossRoadsMode);
		CmpCallCard_Kvar.setVisible(!crossRoadsMode);
		CmpCallCard_Podz.setVisible(!crossRoadsMode);

		CmpCallCard_Etaj.setDisabled(crossRoadsMode);
		CmpCallCard_Kodp.setDisabled(crossRoadsMode);

		if(changeFocus){
			if(crossRoadsMode){
				secondStreetCombo.focus();

				cmpCallCard_Dom.reset();
				CmpCallCard_Korp.reset();
				CmpCallCard_Kvar.reset();
				CmpCallCard_Podz.reset();
				CmpCallCard_Etaj.reset();
				CmpCallCard_Kodp.reset();
			}
			else{
				cmpCallCard_Dom.focus();
				cmpCallCard_Dom.reset();

				CmpCallCard_Etaj.setDisabled(crossRoadsMode);
				CmpCallCard_Kodp.setDisabled(crossRoadsMode);
				if(secondStreetCombo.getPicker() && secondStreetCombo.getPicker().isVisible()){
					secondStreetCombo.collapse();
				}
			}
		}
    },

	//Функция определения МО обслуживания активного вызова
	detectActiveCallLpuLocalCombo: function(){
		var cntr = this,
			lpuLocalCombo = cntr.baseForm.findField('lpuLocalCombo'),
			selectNmpCombo = cntr.baseForm.findField('selectNmpCombo'),
			CmpCallTypeCombo = cntr.baseForm.findField('CmpCallType_id'),
			streetCombo = cntr.baseForm.findField('dStreetsCombo'),
			cmpCallTypeComboSelRec = CmpCallTypeCombo.getSelectedRecord(),
			CmpCallCard_IsExtraCombo = cntr.baseForm.findField('CmpCallCard_IsExtra'),
			streetComboSelRec = streetCombo.getSelectedRecord();

		if(
			CmpCallCard_IsExtraCombo.getValue().inlist([3])
			&& streetComboSelRec
			&& cmpCallTypeComboSelRec
			&& cmpCallTypeComboSelRec.get('CmpCallType_Code').inlist([1,19])
		)
		{
			Ext.Ajax.request({
				url: '/?c=LpuStructure&m=getLpuAddress',
				params: {
					KLHome: cntr.baseForm.findField('CmpCallCard_Dom').getValue(),
					//KLCity_id: (data.KLCity_idEdit) ? data.KLCity_idEdit : '',
					//KLCountry_id: (data.KLCountry_idEdit) ? data.KLCountry_idEdit : '',
					//KLRgn_id: streetComboSelRec.get('KLRgn_id'),
					KLStreet_id: streetComboSelRec.get('KLStreet_id'),
					KLSubRGN_id: streetComboSelRec.get('KLSubRGN_id'),
					KLTown_id: streetComboSelRec.get('KLTown_id'),
					Person_Age: cntr.baseForm.findField('Person_Age').getValue()
				},
				success: function(response, opts){
					var res = Ext.JSON.decode(response.responseText);

					if(res && res[0] && res[0].Lpu_id){
						lpuLocalCombo.store.clearFilter();
						Ext.Ajax.request({
							url: '/?c=MedService4E&m=getLpusWithMedService',
							params: {ConcreteLpu_id: res[0].Lpu_id},
							callback: function (opts, success, response) {
								if (success) {
									var resp = Ext.JSON.decode(response.responseText)[0];

									if (!lpuLocalCombo.store.getAt(lpuLocalCombo.store.find('Lpu_id', resp.Lpu_id))) {
										lpuLocalCombo.store.add(resp);
									}

									lpuLocalCombo.setValue(+res[0].Lpu_id);
								}
							}
						});

						selectNmpCombo.getStore().getProxy().extraParams = {
							'Lpu_ppdid': res[0].Lpu_id,
							'isClose': 1
						};
						selectNmpCombo.reset();
						selectNmpCombo.getStore().load();
					}
				},
				failure: function(response, opts){
					return false;
				}
			});
		}
	},

	//Сбор параметров и вызов метода сохранения
	saveBtnHandler: function(withoutConfirm){
		var cntr = this,
			baseForm = cntr.baseForm,
			allFields = baseForm.getAllFields(),
			allParams = baseForm.getAllValues(),
			nowDate = (cntr.serverTime && Ext.isDate(cntr.serverTime)) ? new Date(cntr.serverTime): new Date(),
			activeElement = Ext.ComponentQuery.query('combo[hasFocus=true]')[0],
			selReasonRecCode = allFields.CmpReason_id.getSelectedRecord() ? allFields.CmpReason_id.getSelectedRecord().get('CmpReason_Code') : false,
			cmpCallTypeCode = allFields.CmpCallType_id.getSelectedRecord() ? allFields.CmpCallType_id.getSelectedRecord().get('CmpCallType_Code') : false,
			codeWithoutLpuBuilding = cmpCallTypeCode ? cmpCallTypeCode.inlist([6,15,16]) : false,
			prmDate = allParams.CmpCallCard_prmDate,
			saveBtn = Ext.ComponentQuery.query('button[refId=saveBtn]')[0],
			saveContinueBtn = Ext.ComponentQuery.query('button[refId=saveContinueBtn]')[0],
			selectHeadDocReason = (
				selReasonRecCode
				&& getRegionNick().inlist(['perm'])
				&& selReasonRecCode.inlist(['02?', '06?', '09?', '10?', '11?', '12?', '13?', '15?', '16?', '40?', '999'])
				&& !allParams.LpuBuilding_id
				&& !allParams.CmpCallType_id
			),
			streetsCombo = allFields.dStreetsCombo,
			cityCombo = allFields.dCityCombo,
			streetRec = streetsCombo.getSelectedRecord(),
			secStreetRec = allFields.secondStreetCombo.getSelectedRecord();

		allParams.CmpCallCard_Dlit = Math.round((new Date().getTime() - cntr.time) / 1000);
		allParams.CmpCallCard_Dom = (allParams.CmpCallCard_Dom != '/') ? allParams.CmpCallCard_Dom : '';
		// Находим запись соответствующую повторному вызову
		allParams.CmpCallType_Code = cmpCallTypeCode;

		if (cntr.isSaveMode) return false;

		// Уфа (повод НГ1 && Карантин) и анкета не заполнена
		if(/*getRegionNick() === 'ufa'&& */ cntr.isEmptyApplicationCVI() ) {
			Ext.MessageBox.confirm('Сообщение', 'Для вызова должна быть заполнена анкета по КВИ. Заполнить анкету?', function(btn){
				var saveCallback = function() { cntr.saveBtnHandler(); };
				if ( btn !== 'yes' ) { return; }
				cntr.showApplicationCVIWindow(saveCallback);
			});
			return;
		}

		cntr.isSaveMode = true;

		if(activeElement) {
			if(activeElement.getName()!== "LpuBuilding_id") activeElement.assertValue();
			if(activeElement.getName() === 'dStreetsCombo'){
				streetsCombo.fireEvent('blur',streetsCombo);
				streetRec = streetsCombo.getSelectedRecord();
			}
		}

		cntr.setAllowBlankDependingCallType(); // валидация полей в зависимости от типа вызова
		cntr.refreshFieldsVisibility(['LpuBuilding_id']);



		//если выбрано решение старшего врача, поля не проверять на заполение вообще #122519
		if (!baseForm.isValid() && !selectHeadDocReason){

			var invalid = cntr.getInvalid()[0],
				errorText = '';

			if(invalid){

				if(invalid.regexText || invalid.getActiveErrors(0)){
					errorText = 'Поле - "'+ invalid.fieldLabel + '" заполненно некорректно. ';
					errorText += invalid.regexText || invalid.getActiveErrors(0)[0];
				}

				if (invalid.hidden) {
					errorText = 'Заполните поле - "'+ invalid.fieldLabel + '"';
				}

				Ext.Msg.alert('Проверка данных формы', errorText);
			}
			else Ext.Msg.alert('Проверка данных формы', 'Не все поля формы заполнены. Незаполненные поля выделены особо.');

			cntr.isSaveMode = false;
			return;
		}

		if(
			( allParams.CmpCallCard_IsExtra == 2 )
			&& !codeWithoutLpuBuilding
			&& ( allParams.CmpCallCard_IsPassSSMP == false )
			&& ( Ext.isEmpty(allParams.LpuBuilding_id) )
			&& ( Ext.isEmpty(allParams.lpuLocalCombo))
		){
			Ext.Msg.alert('Проверка данных формы', 'Если вызов неотложный, то хотя бы одно из полей «МО передачи (НМП)» или «Подразделение СМП» должно быть заполнено');
			cntr.isSaveMode = false;
			return false;
		}

		//проверка на умершего пациента
		if((allParams.Person_deadDT != 0) && prmDate)
		{
			var deadDT = cntr.checkDead(allParams.Person_deadDT);
			if(deadDT && deadDT < prmDate){
				Ext.Msg.alert('Ошибка', 'Человек на дату приема вызова является умершим. Сохранение невозможно.');
				cntr.isSaveMode = false;
				return false;
			}
		}

		if(prmDate > nowDate)
		{
			Ext.Msg.alert('Ошибка', 'Дата приема вызова не может быть больше текущей даты. Сохранение невозможно.');
			cntr.isSaveMode = false;
			return false;
		}

		switch (allParams.CmpCallCard_IsExtra) {
			case 3:
			case 4: {
				var errText =  allParams.CmpCallCard_IsExtra == 3 ? 'Вызов врача на дом может быть создан только для идентифицированного пациента' : 'Обращение в поликлинику может быть создано только для идентифицированного пациента';

				if (!(allParams.Person_id > 0)){
					Ext.Msg.alert('Ошибка', errText);
					cntr.isSaveMode = false;
					return false;
				}
			}
			break;
		}

		if (allParams.CmpCallCard_storDate){
			if (new Date(allParams.CmpCallCard_storDate).getFullYear() > new Date().getFullYear()+1) {
				Ext.Msg.alert('Ошибка', 'Нельзя откладывать вызов более, чем на 1 год');
				cntr.isSaveMode = false;
				return false;
			}
			allParams.CmpCallCard_storDT = Ext.Date.format(Ext.Date.parse(allParams.CmpCallCard_storDate, 'Y-m-d H:i:s'), 'Y-m-d') + ' ' + Ext.Date.format(Ext.Date.parse(allParams.CmpCallCard_storTime, 'Y-m-d H:i:s'), 'H:i:s');
		}

		saveBtn.setDisabled(true);
		saveContinueBtn.setDisabled(true);

		// Данные выбранного города/наспункта

		if (streetRec) {
			allParams.StreetAndUnformalizedAddressDirectory_id = streetRec.get('StreetAndUnformalizedAddressDirectory_id');
			allParams.KLStreet_id = streetRec.get('KLStreet_id');
			allParams.UnformalizedAddressDirectory_id = streetRec.get('UnformalizedAddressDirectory_id');
		}
		else {
			if(getRegionNick().inlist(['krym'])) allParams.CmpCallCard_Ulic = streetsCombo.getRawValue();
			else streetsCombo.reset();
		}

		if (secStreetRec){
			allParams.CmpCallCard_UlicSecond = secStreetRec.get('KLStreet_id');
		}

		if (cityCombo.getSelectedRecord() && cityCombo.getValue()){

			var city = cityCombo.getSelectedRecord().data;

			if(city.KLAreaLevel_id==4){
				allParams.KLTown_id = city.Town_id;

				//если региона нет тогда нас пункт не относится к городу
				if(city.Region_id){
					allParams.KLSubRgn_id = city.Area_pid;
				} /*else{
					allParams.KLCity_id = city.Area_pid;
				}*/
			} else{
				allParams.KLCity_id = city.Town_id;
				//если город верхнего уровня, то район сохранять не надо
				if(city.KLAreaStat_id!=0){
					allParams.KLSubRgn_id = city.Area_pid;
				}
			}

			allParams.KLAreaStat_idEdit = city.KLAreaStat_id;
			allParams.KLRgn_id = city.Region_id;
		}

		if (!allFields.CmpCallerType_id.getSelectedRecord()){
			allParams.CmpCallCard_Ktov = allParams.CmpCallerType_id;
			allParams.CmpCallerType_id = 0;
		}

		allParams.LpuTransmit_id = allParams.lpuLocalCombo;
		allParams.Lpu_ppdid = allParams.lpuLocalCombo;
		allParams.MedService_id = allParams.selectNmpCombo;
		allParams.Person_FirName = allParams.Person_Firname;
		allParams.Person_SecName = allParams.Person_Secname;
		allParams.Person_SurName = allParams.Person_Surname;
		allParams.CmpCallCard_DiffTime = null;
		allParams.Reason_isNMP = ("nmp" == allParams.Reason_isNMP)?2:1;
		allParams.CmpCallCard_Tper = allParams.CmpCallCard_DateTper;
		allParams.CmpCallCard_Vyez = allParams.CmpCallCard_DateVyez;
		allParams.CmpCallCard_Przd = allParams.CmpCallCard_DatePrzd;
		allParams.CmpCallCard_Tgsp = allParams.CmpCallCard_DateTgsp;
		allParams.CmpCallCard_Tsta = allParams.CmpCallCard_DateTsta;
		allParams.CmpCallCard_Tisp = allParams.CmpCallCard_DateTisp;
		allParams.CmpCallCard_Tvzv = allParams.CmpCallCard_DateTvzv;
		allParams.CmpCallCard_Urgency = allParams.UrgencyLabel;
		allParams.CmpCallCard_IsNMP = allParams.CmpCallCard_IsExtra.inlist([1,2]) ? allParams.CmpCallCard_IsExtra : null;

		if (allFields.UnformalizedAddressDirectory_wid.getSelectedRecord() && allParams.UnformalizedAddressDirectory_wid){
			allParams.Lpu_hid = allFields.UnformalizedAddressDirectory_wid.getSelectedRecord().get('Lpu_aid');
		}

		function checkZero(num) {
			var numStr = num.toString();
			if (numStr.length == 1){
				return '0'+numStr;
			}
			else {
				return numStr;
			}
		}

		var milisecondsDifferent = new Date( new Date() - cntr.datetimeStartEdit ),
			h = checkZero(milisecondsDifferent.getUTCHours()),
			m = checkZero(milisecondsDifferent.getUTCMinutes()),
			s = checkZero(milisecondsDifferent.getUTCSeconds());

		allParams.CmpCallCard_DiffTime = h+':'+m+':'+s;

		

		var count_units = allParams.Person_Birthday_YearAge;

		switch(allParams.ageUnit){
			case 1: {
				allParams.Person_Age = allParams.Person_Birthday_YearAge;
				if(!allParams.Person_Birthday)
				{
					nowDate.setYear(nowDate.getFullYear() - count_units);
					nowDate.setDate(1);
					allParams.Person_Birthday = cntr.formatDate(nowDate);
				}
				break;
			}
			case 2: {
				if(!allParams.Person_Birthday)
				{
					nowDate.setMonth(nowDate.getMonth() - count_units);
					nowDate.setDate(1);
					allParams.Person_Birthday = cntr.formatDate(nowDate);
				}
				break;
			}
			case 3: {
				if(!allParams.Person_Birthday)
				{
					nowDate.setDate(nowDate.getDate() - count_units);
					allParams.Person_Birthday = cntr.formatDate(nowDate);
				}
				break;
			}
			default: {
				allParams.Person_Age = 0;
				break;
			}
		}

		//Проверим, является ли поле возрастом или датой рождения
		if(allParams.Person_Age> 1900){
			allParams.Person_Age = Ext.Date.format(new Date,'Y') - allParams.Person_Age;
		}

		if (sw.FormHashes && sw.FormHashes['swWorkPlaceSMPDispatcherCallWindow'] && !cntr.isNmpArm) {
			allParams.formHash = sw.FormHashes['swWorkPlaceSMPDispatcherCallWindow'];
			allParams.formClass = 'swWorkPlaceSMPDispatcherCallWindow';
		}

		if(withoutConfirm){
			var cityCombo = baseForm.findField('dCityCombo'),
				streetsCombo = baseForm.findField('dStreetsCombo'),
				ltdField = baseForm.findField('CmpCallCard_CallLtd'),
				lngField = baseForm.findField('CmpCallCard_CallLng'),
				houseNum = baseForm.findField('CmpCallCard_Dom'),
				rawPlace = cityCombo.getRawValue()+' '+streetsCombo.getRawValue()+' '+houseNum.getValue(),
				mapWrapper = cntr.mapWindow.down('swmappanel'),
				ltd, lng,
				selStreetRec = streetsCombo.bigStore.findRecord('StreetAndUnformalizedAddressDirectory_id', streetsCombo.getValue());

			if(!allParams.CmpCallCard_CallLng || !allParams.CmpCallCard_CallLtd) {
				if (selStreetRec) {
					ltd = selStreetRec.get('lat');
					lng = selStreetRec.get('lng');
					if (ltd && lng) {
						allParams.CmpCallCard_CallLng = lng;
						allParams.CmpCallCard_CallLtd = ltd;
						cntr.saveCard(allParams, 'no', true, true, selectHeadDocReason);
					} else {
						mapWrapper.geocode(rawPlace, function(coords){
							if(Array.isArray(coords)){
								allParams.CmpCallCard_CallLng = coords[1];
								allParams.CmpCallCard_CallLtd = coords[0];
							}
							if( getRegionNick() == 'ufa' ) {
								var cback = function() {
									cntr.saveCard(allParams, 'no', true, true, selectHeadDocReason);
								};
								cntr.compareExtraValueFormAndTeamLogic(allParams,cback);
							} else {
								cntr.saveCard(allParams, 'no', true, true, selectHeadDocReason);
							}
						});
					}
				}else {
					cntr.saveCard(allParams, 'no', true, true, selectHeadDocReason);
				}
			} else {
				if( getRegionNick() == 'ufa' ) {
					var cback = function() {
						cntr.saveCard(allParams, 'no', true, true, selectHeadDocReason);
					};
					cntr.compareExtraValueFormAndTeamLogic(allParams,cback);
				} else {
					cntr.saveCard(allParams, 'no', true, true, selectHeadDocReason);
				}
			}
			return true;
		}

		if(cntr.selected112Cards && cntr.selected112Cards.length > 0){

			allParams.CmpCallCardStatusType_id = 1;

			if(cntr.selected112Cards.length > 1){
				//Ext.defer(function() {
				Ext.Msg.show({
					title: 'Сообщение',
					modal: true,
					msg: 'Применить изменения для всех выбранных карточек 112?',
					buttons: Ext.Msg.YESNO,
					buttonText: {
						yes: 'Применить для всех',
						no: 'Применить для одной и открыть следующую'
					},
					icon: Ext.Msg.QUESTION,
					fn: function(btn){
						if (btn == 'yes'){
							//•	Все вызовы, связанные с выбранными Карточками 112, сохраняются с отредактированными параметрами.
							cntr.selectedCards.splice(0,1);

							allParams.copyParamsToOthersCards = Ext.encode({
								recipientCards: Ext.encode(cntr.selectedCards)
								//paramsList: Ext.encode(cntr.changedByUserFields)
							});

							//filterParams.copyParamsToOthersCards = Ext.encode(cntr.selectedCards);

							cntr.saveCard(allParams, 'yes', true, false, selectHeadDocReason, function(){
								//•	Статус Карточек 112 меняется на «Обработана».
								cntr.setStatus112Cards(cntr.selected112Cards, 3);
								cntr.selected112Cards = [];
								cntr.selectedCards = [];
							});
						}
						else if (btn == 'no') {
							cntr.saveCard(allParams, 'yes', false, false, selectHeadDocReason, function(){
								//•	Текущий вызов сохраняется с отредактированными параметрами.
								//•	Статус текущей Карточки 112 меняется на «Обработана».
								//•	На форму приема вызова подставляются параметры вызова, связанного со следующей выбранной Карточкой 112.
								cntr.setStatus112Cards([cntr.selected112Cards[0]], 3);
								//удаляем текущую 112 из выбранных
								cntr.selected112Cards.splice(0,1);
								cntr.selectedCards.splice(0,1);
								cntr.setStatus112Cards([cntr.selected112Cards[0]], 2);
								cntr.clearAllFields();
								cntr.loadCmpCard(cntr.selectedCards[0]);
							});

						}
						saveBtn.enable();
						saveContinueBtn.enable();
						cntr.isSaveMode = false;
					}
				});
				//}, 1000);
			}
			else{
				//	Если форма приема вызова редактируется и сохраняется - Статус Карточки 112 меняется на «Обработана».
				//Если форма приема вызова закрывается БЕЗ сохранения изменений, то статус Карточки 112 меняется на «Новая».
				Ext.Msg.show({
					title: 'Сообщение',
					msg: 'Сохранить вызов?',
					buttons: Ext.Msg.YESNO,
					buttonText: {
						yes: 'Сохранить',
						no: 'Отмена'
					},
					icon: Ext.Msg.QUESTION,
					fn: function(btn){
						if (btn == 'yes'){
							cntr.saveCard(allParams, 'yes', true, false, selectHeadDocReason, function(){
								cntr.setStatus112Cards(cntr.selected112Cards, 3);
								cntr.selected112Cards = [];
								cntr.selectedCards = [];
							});

						}
						cntr.isSaveMode = false;

						saveBtn.enable();
						saveContinueBtn.enable();
					}
				});
			}
		}else{
			var showMsgBox = function() {
				Ext.MessageBox.show({
					title: 'Сохранение',
					msg: 'Сохранить вызов?',
					buttons: Ext.Msg.YESNO,
					buttonText :
						{
							yes : 'Сохранить',
							no : 'Сохранить и продолжить',
							cancel : 'Отмена'
						},
					fn: function(butn){
						if (butn == 'cancel'){
							saveBtn.enable();
							saveContinueBtn.enable();
							cntr.isSaveMode = false;
							return false;
						}
						allParams.CmpCallCard_prmDT = Ext.Date.format(Ext.Date.parse(allParams.CmpCallCard_prmDate, 'Y-m-d H:i:s'), 'Y-m-d') + ' ' + Ext.Date.format(Ext.Date.parse(allParams.CmpCallCard_prmTime, 'Y-m-d H:i:s'), 'H:i:s');
						
						if (getRegionNick().inlist(['ufa', 'astra'])) {
							allParams.CmpCallCard_prmDT = Ext.Date.format(new Date(cntr.serverTime), 'Y-m-d H:i:s')
						}

						//бывают случаи когда стор пустой а значение в комбике есть
						if((!allParams.CmpCallCard_CallLng || !allParams.CmpCallCard_CallLtd) && !cntr.isNmpArm) {
							var cityCombo = baseForm.findField('dCityCombo'),
								streetsCombo = baseForm.findField('dStreetsCombo'),
								ltdField = baseForm.findField('CmpCallCard_CallLtd'),
								lngField = baseForm.findField('CmpCallCard_CallLng'),
								houseNum = baseForm.findField('CmpCallCard_Dom'),
								rawPlace = cityCombo.getRawValue()+' '+streetsCombo.getRawValue()+' '+houseNum.getValue(),
								mapWrapper = cntr.mapWindow.down('swmappanel'),
								ltd, lng,
								selStreetRec = streetsCombo.bigStore.findRecord('StreetAndUnformalizedAddressDirectory_id', streetsCombo.getValue());

							if (selStreetRec) {
								ltd = selStreetRec.get('lat');
								lng = selStreetRec.get('lng');
								if (ltd && lng) {
									allParams.CmpCallCard_CallLng = lng;
									allParams.CmpCallCard_CallLtd = ltd;
									cntr.saveCard(allParams, butn, true, (butn == 'no'), selectHeadDocReason);
								} else {
									mapWrapper.geocode(rawPlace, function(coords){
										if(Array.isArray(coords)){
											allParams.CmpCallCard_CallLng = coords[1];
											allParams.CmpCallCard_CallLtd = coords[0];
										}
										cntr.saveCard(allParams, butn, true, (butn == 'no'), selectHeadDocReason);
									});
								}
							} else {
								cntr.saveCard(allParams, butn, true, true, selectHeadDocReason);
							}
						} else {
							cntr.saveCard(allParams, butn, true, (butn == 'no'), selectHeadDocReason);
						}
					}
				});
			};

			if( getRegionNick() == 'ufa' ) {
				cntr.compareExtraValueFormAndTeamLogic(allParams,showMsgBox);
			} else {
				showMsgBox();
			}
		}
	},


	//сравниваем поле CmpCallCard_isExtra со значением заданным в логике бригады
	compareExtraValueFormAndTeamLogic: function(params,callback) {
		var cntr = this,
			baseForm = this.baseForm,
			combo = baseForm.findField('CmpCallCard_IsExtra');


		// cntr.CmpCallCard_IsExtra значение полученное для поля "Вид вызова" при указании повода (в функции checkUrgencyAndProfile)
		if( cntr.CmpCallCard_IsExtra && combo.getValue() != cntr.CmpCallCard_IsExtra ) {

			var rec = combo.store.findRecord('CmpCallTypeIsExtraType_id',cntr.CmpCallCard_IsExtra);

			if(rec) {
				cntr.armWindow.BaseForm.el.mask(); //.BaseForm.mask();
				var msg = 'Указан вид вызова "' + combo.getRawValue() + '". Изменить на "'
						+ rec.get('CmpCallCardIsExtraType_Name') + '" согласно поводу?';

				var dialWindow = Ext.create('Ext.window.Window', {
					renderTo: Ext.get('MainForm').el,
					refId: 'changeIsExtraByLogicWindow',
					title: 'Сообщение',
					height: 100,
					width: 500,
					header: false,
					hideInToolbar: true,
					//modal: true,
					layout: {
						type: 'vbox',
						align: 'center',
						pack: 'center'
					},
					listeners: {
						close: function() {
							cntr.armWindow.BaseForm.el.unmask();
							callback();
						},
					},
					items: [ {
						xtype: 'container',
						height: 60,
						layout: {
							type: 'hbox',
							align: 'middle',
							pack: 'center'
						},
						items: [ {
							xtype: 'label',
							text: msg
						}]
					}, {
						xtype: 'container',
						flex: 1,
						items: [ {
							xtype: 'button',
							width: 100,
							text: 'Да',
							handler: function() {
								combo.select(rec);
								params.CmpCallCard_IsExtra = cntr.CmpCallCard_IsExtra;
								dialWindow.close();
							}
						}, {
							xtype: 'button',
							width: 100,
							text: 'Нет',
							refId: 'takeCall112',
							handler: function(){
								dialWindow.close();
							}
						} ]
					} ]
				});
				dialWindow.show();

			} else {
				callback();
			}
		} else {
			callback();
		}
	},
	setCurrentPPDLpu: function(){
		var cntr = this,
			lpuLocalCombo = cntr.baseForm.findField('lpuLocalCombo'),
			nmpCombo = cntr.baseForm.findField('selectNmpCombo');

		lpuLocalCombo.setValue(+getGlobalOptions().lpu_id);
		nmpCombo.getStore().getProxy().extraParams = {
			'Lpu_ppdid': getGlobalOptions().lpu_id,
			'isClose': 1
		};
		nmpCombo.getStore().load();
	},
	checkDead: function (deadDate) {
		var opts = getGlobalOptions(),
			deadDT = Ext.Date.parse(deadDate, 'd.m.Y');

		function addDays(date, days) {
			var result = new Date(date);
			result.setDate(result.getDate() + days);
			return result;
		}
		if(!Ext.isEmpty(opts.limit_days_after_death_to_create_call) && parseInt(opts.limit_days_after_death_to_create_call,10)>0)
			deadDT = addDays(deadDT,parseInt(opts.limit_days_after_death_to_create_call,10));

		return deadDT;
	},
	formatDate: function (date) {
		return Ext.Date.format(date, 'd.m.Y');
	},
	showWndFromExt2: function(wnd, card_id){

		if(Ext.isEmpty(wnd) || Ext.isEmpty(card_id)){
			return;
		}
		var title = 'Талон вызова',
			action = 'view';

		new Ext.Window({
			title: title,
			header: false,
			extend: 'sw.standartToolsWindow',
			toFrontOnShow: true,
			style: {
				'z-index': 90000
			},
			layout: {
				type: 'fit',
				align: 'stretch'
			},
			maximized: true,
			constrain: true,
			renderTo: Ext.getCmp('inPanel').body,
			items : [{
				xtype : "component",
				autoEl : {
					tag : "iframe",
					src : "/?c=promed&getwnd=" + wnd + "&act=" + action + "&showTop=1&cccid="+card_id
				}
			}]
		}).show();
	}
});

