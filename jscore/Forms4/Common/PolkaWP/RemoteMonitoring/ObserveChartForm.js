


Ext6.define('common.PolkaWP.RemoteMonitoring.ObserveChartForm', {
	extend: 'Ext6.Panel',
	requires: [
		'common.PolkaWP.RemoteMonitoring.ObserveChartMeasures'
	],
	cls: 'person_chart',
	region: 'center',
	border: false,
	layout: 'anchor',
	msgParams: {
		Chart_id: 0,
		start: 0,
		limit: 7
	},
	get: function(id) {//вспомогательная функция получения элемента формы
		if(this.queryById(id)) return this.queryById(id);
		else {
			var el = this.query('[name='+id+']');
			if(el.length>0) return el[0];
		}
		return false;
	},
	getRateByTypeId: function(id) {
		var me = this;
		return me.rates.find(function(el) { return (el.RateType_id==id); });
	},
	recalcIMT: function() {
		var me = this;
			w = me.queryById('PersonWeight').getValue(),
			h = me.queryById('PersonHeight').getValue();
		if(w && h) {
			imt = w/(Math.pow(h/100,2));
			me.queryById('imt').setValue(imt.toFixed(2));
			me.queryById('imt').show();
		} else {
			me.queryById('imt').hide();
			me.queryById('imt').reset();
		}
	},
	setFeedback: function(id) {
		var me = this;
		me.get('email').setAllowBlank(id!=3);
		me.get('sms').setAllowBlank(id!=2);
		me.get('voice').setAllowBlank(id!=1);
		me.FeedbackMethod_id = id;
		//~ me.toolMenu.queryById('menuSendNotice').setDisabled(false);
		//~ me.toolMenu.queryById('menuSendMsg').setDisabled(false);
		
		if(me.activeForm.findField('feedback') && me.activeForm.findField('feedback').nosave) return;

		Ext6.Ajax.request({
			params: {
				Chart_id: me.Chart_id,
				FeedbackMethod_id: id
			},
			success: function(response, opts) {
				if(response.responseText!='') {
					var res = JSON.parse(response.responseText);
					if(res.success) {
						var row = me.ownerWin.grid.getSelection()[0];
						if(row) {
							row.set('FeedbackMethod_Name', res.FeedbackMethod_Name);
							row.set('FeedbackMethod_id', res.FeedbackMethod_id);
							row.commit();
						}
					}
				}
			},
			url: '/?c=PersonDisp&m=savePersonChartFeedback'
		});
	},
	savePersonModel: function() {
		var me = this;
		var PersonModel_id = me.get('PersonModel_id').getValue();
		Ext6.Ajax.request({
			params: {
				Chart_id: me.Chart_id,
				PersonModel_id: PersonModel_id
			},
			callback: function(options, success, response) {
				if (success) {
					var resp = Ext6.JSON.decode(response.responseText);

				}
			},
			url: '/?c=PersonDisp&m=savePersonChartModel'
		});
	},
	saveChartInfo: function(field) {
		var	me = this,
			ownerWin = me.ownerWin,
			fieldname = field.name;
		if(!field.allowBlank && !field.isValid()) return false;
		params = {Chart_id: me.Chart_id};
		params[field.name] = field.getValue();
		if(params[field.name].replace) params[field.name] = params[field.name].replace(/[ \(\)]/g,'');

		Ext6.Ajax.request({
			params: params,
			success: function(response, opts) {
				if(response.responseText!='') {
					var res = JSON.parse(response.responseText);
					if(res.success) {
						var row = me.ownerWin.grid.getSelection()[0];
						if(row) {
							if(field.name == 'PersonModel_id') {
								row.set('PersonModel_id', field.getValue());
								row.set('Status', 20+field.getValue());
							}
							if(field.name == 'sms' || field.name == 'voice') {
								row.set('Chart_Phone', field.getValue());
							}
							if(field.name == 'email') {
								row.set('Chart_Email', field.getValue());
							}
							row.commit();
						}
					}
				}
			},
			url: '/?c=PersonDisp&m=savePersonChartInfo'
		});
		
		if(field.name == 'PersonModel_id') {
			row = me.ownerWin.grid.getSelection()[0];
			row.set('PersonModel_id', field.getValue());
			row.set('Status', 20+field.getValue());
		}
	},
	saveChartRate: function(el) {
		if(!el.isValid()) return;
		var me = this;
		var column = el.ownerCt.ownerCt;
		
		if(el.getValue()) {
			if(el.itemId=='min') {
				el.ownerCt.queryById('max').setMinValue(el.getValue());
				if(el.ownerCt.queryById('max').isValid()) {
					el.setMaxValue(el.ownerCt.queryById('max').getValue());
				}
			}
			else if(el.itemId=='max') {
				el.ownerCt.queryById('min').setMaxValue(el.getValue());
				if(el.ownerCt.queryById('min').isValid()) {
					el.setMinValue(el.ownerCt.queryById('min').getValue());
				}
			}
		}
														
		if(	!column.queryById('min').saved ||
			!column.queryById('max').saved
		) {
			Ext6.Ajax.request({
				params: {
					Chart_id: me.Chart_id,
					LabelRate_id: column.LabelRate_id,
					LabelRateMin: column.queryById('min').getValue(),
					LabelRateMax: column.queryById('max').getValue()
				},
				success: function(response){
					var res = Ext6.JSON.decode(response.responseText);
					if(!Ext6.isEmpty(res.Error_Msg)) {
						Ext6.Msg.alert(langs('Ошибка'), res.Error_Msg);
					} else {
						column.queryById('min').saved = true;
						column.queryById('max').saved = true;
						
						var iRate = me.measures.rates.findIndex(function(r) { return(r.RateType_id==column.ratetype_id); } );
						if(iRate>=0) {
							me.measures.rates[iRate].ChartRate_Min = column.queryById('min').getValue();
							me.measures.rates[iRate].ChartRate_Max = column.queryById('max').getValue();
						}
						
						me.measures.updateMinMaxLabel({
							RateType_id: column.ratetype_id,
							ChartRate_Min: column.queryById('min').getValue(),
							ChartRate_Max: column.queryById('max').getValue()
						});
						me.measures.load();
						
						if(me.ownerWin) {
							var sel = me.ownerWin.grid.getSelection();
							if(sel.length>0) {
								var rec = sel[0];
								rec.set('Rate'+column.LabelRate_id+'_Min', column.queryById('min').getValue());
								rec.set('Rate'+column.LabelRate_id+'_Max', column.queryById('max').getValue());
							}
						}
					}
				},
				url: '/?c=PersonDisp&m=saveLabelObserveChartRate'
			});
		}
	},
	setVisibleMenuItems: function() {
		var me = this,
			isOpenedChart = me.data.Chart_id && Ext6.isEmpty(me.data.Chart_endDate);
		me.toolMenu.queryById('menuSendNotice').setVisible(isOpenedChart);
		me.toolMenu.queryById('menuSendMsg').setVisible(isOpenedChart);
		me.toolMenu.queryById('menuRemoveFromMonitoring').setVisible(isOpenedChart);
		me.toolMenu.queryById('menuPrintConsent').setVisible(isOpenedChart);
		
	},
	remindPerson: function() {
		var me = this,
			list = [];
		list.push({
			Person_SurName: me.Person_SurName,
			Person_FirName: me.Person_FirName,
			Person_SecName: me.Person_SecName,
			Person_id: me.Person_id,
			Chart_id: me.Chart_id,
			email: me.activeForm.findField('email').getValue(),
			phone: me.activeForm.findField('sms').getValue(),
			FeedbackMethod_id: me.FeedbackMethod_id
		});
		
		Ext6.Ajax.request({
			url: '/?c=PersonDisp&m=RemindToMonitoring',
			params: {
				Persons: Ext6.util.JSON.encode(list),
				LpuSection_id: getGlobalOptions().CurLpuSection_id,
			},
			callback: function(options, success, response) {
				if (success) {
					resp = Ext6.JSON.decode(response.responseText);
					if(resp.Error_Msg=='')
						Ext6.Msg.alert(langs('Сообщение'),langs('Напоминание успешно отправлено'));
					else
						Ext6.Msg.alert(langs('Ошибка'),resp.Error_Msg);
				}
			}
		});
	},
	sendMessagePerson: function() {
		var me = this;
		if(Ext6.isEmpty(me.FeedbackMethod_id)) {
			Ext6.Msg.alert(langs('Сообщение'),langs('У пациента не указан предпочтительный канал связи'));
			return;
		}
		getWnd('swRemoteMonitoringMessageWindow').show({
			Person_id: me.Person_id,
			Chart_id: me.Chart_id,
			PersonFio: me.Person_SurName+' '+me.Person_FirName+' '+me.Person_SecName,
			BirthDay: me.BirthDay,
			Age: me.PersonAge,
			FeedbackMethod_id: me.FeedbackMethod_id,
			email: me.activeForm.findField('email').getValue(),
			phone: me.activeForm.findField('sms').getValue(),
			callback: function() {
				me.msgGrid.load();
			}
		});
	},
	updateEmk: function() {
		var me = this;
		var emks = Ext6.ComponentQuery.query('[refId=common]');
		emks.forEach(function(emk){
			if(emk.Person_id==me.Person_id) {
				var emkpanel = emk.queryById('ObserveChartPanel');
				if(emk.isVisible()) {
					if(!Ext6.isEmpty(emkpanel)) {
						emkpanel = emkpanel.ownerWin.down('[refId=ObserveChartPanel]');
						if(!Ext6.isEmpty(emkpanel)) emkpanel.reload();
					}
					emk.PersonInfoPanel.load({Person_id:me.Person_id});
				}
			}
		});
	},
	open: function(data) {
		var me = this;
		if(Ext6.isEmpty(data) || Ext6.isEmpty(data.action) || Ext6.isEmpty(data.Label_id) ) {
			Ext6.Msg.alert(langs('Сообщение'), langs('Неверные параметры'));
			return false;
		}
		me.PersonTitleToolPanel.show();
		me.action = data.action;
		me.Label_id = data.Label_id;
		me.PersonLabel_id = data.PersonLabel_id;
		if(me.action=='add') {
			me.Tabs.setActiveTab(0);
			Ext6.Ajax.request({
				url: '?c=PersonDisp&m=createLabelObserveChart',
				params: {
					PersonDisp_id: data.PersonDisp_id,
					MedStaffFact_id: getGlobalOptions().CurMedStaffFact_id,
					PersonLabel_id: data.PersonLabel_id,
					Label_id: data.Label_id,
					Person_Phone: data.Person_Phone,
					allowMailing: data.allowMailing,
					dateConsent: data.dateConsent
				},
				success: function(response){
					var res = Ext6.JSON.decode(response.responseText);
					if(!Ext6.isEmpty(res.Error_Msg)) {
						Ext6.Msg.alert(langs('Ошибка'), res.Error_Msg);
					} else {
						if(!Ext6.isEmpty(res.LabelObserveChart_id)) {
							if(me.ownerWin.grid && me.ownerWin.grid.getSelection().length>0) {
								//TAG: запись в строку таблицы пациентов (добавлен)
								var record = me.ownerWin.grid.getSelection()[0];
								record.set('Chart_id', res[0].LabelObserveChart_id);
								record.set('Chart_endDate', null);
								record.set('Status', 20);
								record.set('StatusNick', 'on');
								record.set('Chart_Phone', data.Person_Phone);
								record.commit();
							}
							data.StatusNick = 'on';
							data.Chart_id = res[0].LabelObserveChart_id;
							me.load(data);
							me.updateEmk();
						} else {
							Ext6.Msg.alert(langs('Ошибка'), 'Ошибка при создании карты наблюдения');
						}
					}
				}
			});
		} else {
			if(!me.showed) { me.Tabs.setActiveTab(1); me.showed=true; }
			me.load(data);
		}
		me.showed = true;
	},
	load: function(data) {
		var me = this;
		me.data = data;
		var isChart = data.StatusNick=='on';
				
		me.NewPersonPanel.setVisible(!isChart);
		me.Panel.setVisible(isChart);
		
		me.FeedbackMethod_id = null;
		//~ me.toolMenu.queryById('menuSendNotice').setDisabled(true);
		//~ me.toolMenu.queryById('menuSendMsg').setDisabled(true);
		
		me.queryById('Chart_begDate').setMaxValue(Date.now());

		me.activeForm = isChart ? me.PersonFullInfo.getForm() : me.PersonInfo.getForm();
		me.activePanel = isChart ? me.PersonFullInfo : me.PersonInfo;

		if(isChart && Ext6.isEmpty(data.Chart_id)) {
			Ext6.Msg.alert(langs('Сообщение'),langs('Не указан идентификатор карты'));
			return false;
		} else {
			me.Chart_id = data.Chart_id;
			me.msgParams.Chart_id = me.Chart_id;
			me.msgGrid.getStore().removeAll();
		}
		
		if(Ext6.isEmpty(data.Person_id)) {
			Ext6.Msg.alert(langs('Сообщение'),langs('Не указан идентификатор пациента'));
			return false;
		} else {
			me.Person_id = data.Person_id;
			me.PersonFio = data.PersonFio ? data.PersonFio : '';
			me.Person_SurName = data.Person_SurName;
			me.Person_FirName = data.Person_FirName;
			me.Person_SecName = data.Person_SecName;
		}

		if(Ext6.isEmpty(me.LoadMask)) {
			me.LoadMask = new Ext6.LoadMask(me, {msg: LOAD_WAIT});
		}
		//~ me.LoadMask.show();

		//общий заголовок - фио пациента, возраст
		var tpl = new Ext6.XTemplate(
			"<div class=''><b class='personpanel_sex person_{PersonSex}'></b> <span class='person_fio'>{PersonFio} <span class='person-birthday'>{BirthDayFormatted} ({PersonAge})</span></span></div>"
		);

		me.BirthDay = data.BirthDayFormatted;
		data.PersonAge = getAgeStringY(data);
		me.PersonAge = data.PersonAge;
		tpl.overwrite(me.PersonTitle.body, data);
		
		//~ me.PersonTitleToolPanel.setVisible(isChart);
		
		me.setVisibleMenuItems();
		var isTemperature = me.data.Label_id=="7";
		me.activeForm.findField('PersonInfoDisp').setVisible(!isTemperature);
		me.queryById('Master').setVisible(!isTemperature);
		me.toolMenu.queryById('menuPrintConsent').setVisible(!isTemperature);
		
		if(!isChart) {
			var labelInOut = me.queryById('labelPersonInOutMonitoring');
			labelInOut.setHtml('');

			var tplMsgNotInProgram = new Ext6.XTemplate(
				"<div class='not-in-program-message'><div class='not-in-program-message-icon'></div><p style='overflow: auto;'>Пациент не состоит в программе дистанционного мониторинга</p></div>"
			);
			var tplMsgRemoveFromProgram = new Ext6.XTemplate(
				"<div class='not-in-program-message'><div class='not-in-program-message-icon'></div><p style='overflow: auto; color:red;'>Пациент исключен из программы {endDate} по причине: \"{Reason}\"</p></div>"
			);

			var endDate = data.Chart_endDate;
			var Reason = data.DispOutType_Name;
			if(data.StatusNick=='new') {
				labelInOut.setHtml(tplMsgNotInProgram.apply({}));
			}
			if(data.StatusNick=='off') {
				labelInOut.setHtml(tplMsgRemoveFromProgram.apply({endDate:endDate, Reason:Reason}));
			}

			if(data.LabelInviteStatus_Date && data.LabelInviteStatus_Date.length>15)
				data.LabelInviteStatus_Date = Ext6.Date.format(Date.parse(data.LabelInviteStatus_Date), 'd.m.Y') + data.LabelInviteStatus_Date.slice(10,15);
			else data.LabelInviteStatus_Date = '';
			data.me = 'Ext6.getCmp(\''+me.id+'\')';
			me.queryById('Master').setHtml(me.tplMaster.apply(data));
			//~ me.LoadMask.hide();
		}
		else
		{
			if(me.measures.cardPanel.getLayout().getActiveItem().itemId=='grafcard') me.measures.loadchart();
			me.msgParams.start=0;
			me.msgGrid.load();
			me.query('radiofield').forEach(function(radio) {
				radio.setBoxLabel('');
			});
			if(Ext6.isEmpty(data.Chart_id) || Ext6.isEmpty(data.Person_id)) {
				Ext6.Msg.alert(langs('Ошибка'), langs('Что-то пошло не так'));
				return false;
			}
			me.activeForm.findField('PersonModel_id').setVisible(!isTemperature);
			me.LoadMask.show();
			Ext6.Ajax.request({
				url: '/?c=PersonDisp&m=getPersonChartInfo',
				params: {
					Person_id: data.Person_id,
					Chart_id: data.Chart_id
				},
				callback: function(options, success, response) {
					//TAG: получены данные для вкладки Пациент
					if (success) {
						var rdata = Ext6.JSON.decode(response.responseText);

						me.activeForm.getFields().items.forEach(function(el) {el.suspendEvents(); });

						me.activeForm.reset();

						me.activeForm.setValues(rdata.info);
						me.MailingConsDT = rdata.info.MailingConsDT;
						me.recalcIMT();
						me.activeForm.findField('sms').setValue(!Ext6.isEmpty(rdata.info.ChartPhone) ? rdata.info.ChartPhone : rdata.info.PersonPhone);
						me.activeForm.findField('voice').setValue(!Ext6.isEmpty(rdata.info.ChartPhone) ? rdata.info.ChartPhone : rdata.info.PersonPhone);
						me.rates = [];
						
						if(rdata.rates.length>0) {
							me.rates = rdata.rates;
							//TAG: вывод контрольных показателей
							me.get('CP').query('[ratetype_id]').forEach(function(column) {
								column.hide();
							});
							
							me.rates.forEach(function(rate) {
								var els = me.get('CP').query('[ratetype_id='+rate.RateType_id+']');
								if(els.length) {
									els[0].queryById('min').setValue(rate.ChartRate_Min);
									els[0].queryById('min').setMaxValue(rate.ChartRate_Max);
									els[0].queryById('min').isValid();
									els[0].queryById('min').saved=true;
									els[0].queryById('max').setValue(rate.ChartRate_Max);
									els[0].queryById('max').setMinValue(rate.ChartRate_Min);
									els[0].queryById('max').isValid();
									els[0].queryById('max').saved=true;
									els[0].show();
								}
							});
						}
						
						me.measures.setRates(rdata.rates);

						me.setCommonInfo(data);
						//TAG: вывод данных с портала и моб.приложения
						me.activeForm.findField('application').setValue1(false);
						me.activeForm.findField('portal').setValue1(rdata.portal);
						if(rdata.portal) {
							if(Ext6.isEmpty(me.activeForm.findField('email')) && rdata.portal.email) {
								me.activeForm.findField('email').setValue(rdata.portal.email);
							}
							if(rdata.portal.app) {
								me.activeForm.findField('application').setValue1(true);
							}
						}

						me.activeForm.getFields().items.forEach(function(el) {el.resumeEvents(); });

						if(rdata.info.FeedbackMethod_id) {
							var fb = me.activeForm.findField('feedback');
							fb.nosave = true;
							fb.setValue(rdata.info.FeedbackMethod_id);
							fb.nosave = false;
						}
						
						me.measures.grid.params = {
							Chart_id: me.Chart_id,
							start: 0,
							limit: 7*2
						};
						
						me.measures.setParams({
							Person_id: data.Person_id,
							Chart_id: me.Chart_id,
							Label_id: me.Label_id
						});

						me.measures.load();
					}
				}
			});
		}
		me.setCommonInfo(data);
	},
	setCommonInfo: function(data) {
		var me = this;
		//Диагноз
		//~ me.activeForm.findField('PersonInfoDiag').setValue('<font color="black">'+data.Diag_Code+'</font>');
		//льготы
		var getPrivIcons = function(data) {
			var s = '';
			var addClass = "";
			var isRefuse = false;
			if (data.Person_IsRefuse && data.Person_IsRefuse == 1) {
				addClass += " lgot_refuse";
				isRefuse = true;
			}
			if (data.Person_IsFedLgot && data.Person_IsFedLgot == 1 ) {
				s += "<span class='lgot_fl" + addClass + "' data-qtip='" + (isRefuse ? "Пациент отказался от федеральной льготы" : "Федеральная льгота") + "'>ФЛ</span>";
			}
			if (data.Person_IsRegLgot && data.Person_IsRegLgot == 1 ) {
				s += "<span class='lgot_rl" + "' data-qtip='" + "Региональная льгота" + "'>РЛ</span>";
			}
			return s;
		};
		if(me.activeForm.findField('PersonInfoPrivilege')) {
			me.activeForm.findField('PersonInfoPrivilege').setValue(getPrivIcons(data));
		}
		//Прикрепление
		if(me.activeForm.findField('PersonInfoAttach')) {
			var attach = '';
			if(!Ext6.isEmpty(data.Lpu_Nick)) {
				attach+=data.Lpu_Nick;
				if(!Ext6.isEmpty(data.AttachNum)) {
					if(data.Lpu_Nick.slice(-1)!='.') attach+='.';
					attach+=' Уч. '+data.AttachNum;
					if(!Ext6.isEmpty(data.AttachDate)) {
						attach+=' ('+data.AttachDate+')';
					}
				}
			}
			me.activeForm.findField('PersonInfoAttach').setValue(attach);
		}
		//Карта дисп.наблюдения
		var dispInfo = '';
		if(data.Diag_Name && data.PersonDisp_begDate) {
			var tpl = new Ext6.XTemplate(
				"<a href='#' onClick='Ext6.getCmp(\""+me.id+"\").openPersonDispEditWindow();'>{Diag_Code} {Diag_Name}<br>{[values.PersonDisp_begDate ? 'Взят на учет '+values.PersonDisp_begDate : '' ]}</a>"
			);
			dispInfo = tpl.apply(data);
		} else dispInfo = '';
		me.activeForm.findField('PersonInfoDisp').setValue(dispInfo);
	},
	openPersonDispEditWindow: function() {
		var me=this;
		var params = {
			action: 'edit',
			formParams: {
				Person_id: me.data.Person_id,
				Server_id: me.data.Server_id,
				PersonDisp_id: me.data.PersonDisp_id
			},
			callback: function() {
				if(me.ownerWin) {
					me.ownerWin.Person_id=me.data.Person_id;
					me.ownerWin.doSearch(0,0);
				}
			}
		};
		getWnd('swPersonDispEditWindow'+(getGlobalOptions().client == 'ext2' ? '':'Ext6' )).show(params);
	},
	initComponent: function() {
		var me = this;

		me.PersonInfo = new Ext6.form.Panel({
			margin: '10 10 10 30',
			border: false,

			items: [{
					xtype: 'displayfield',
					fieldLabel: 'Карта дисп. наблюдения',
					itemId: 'PersonInfoDisp',
					name: 'PersonInfoDisp',
					value: ""
				}, /*{
					xtype: 'displayfield',
					fieldLabel: 'Диагноз',
					itemId: 'NewPersonInfoDiag',
					name: 'PersonInfoDiag',
					value: ''
				},*/ {
					xtype: 'displayfield',
					fieldLabel: 'Льготы',
					itemId: 'PersonInfoPrivilege',
					name: 'PersonInfoPrivilege',
					value: ''
				}, {
					xtype: 'displayfield',
					fieldLabel: 'Прикрепление',
					itemId: 'NewPersonInfoAttach',
					name: 'PersonInfoAttach',
					value: ''
				}]
		});

		me.PersonFullInfo = new Ext6.form.Panel({ //информационный блок без полей ввода
			bodyPadding: '10 5 0 6',
			height: '100%',
			border: false,

			defaults: {
				padding: '0 0 0 25',
				labelWidth: 106
			},
			items: [
				{
					xtype: 'displayfield',
					fieldLabel: 'Карта дисп. наблюдения',
					itemId: 'PersonInfoDisp',
					name: 'PersonInfoDisp',
					value: ""
				}, /*{
					xtype: 'displayfield',
					fieldLabel: 'Диагноз',
					itemId: 'PersonInfoDiag',
					name: 'PersonInfoDiag',
					value: ''
				},*/ {
					xtype: 'displayfield',
					fieldLabel: 'Льготы',
					itemId: 'PersonInfoPrivilege',
					name: 'PersonInfoPrivilege',
					value: ''
				}, {
					xtype: 'displayfield',
					fieldLabel: 'Прикрепление',
					itemId: 'PersonInfoAttach',
					name: 'PersonInfoAttach',
					value: ''
				}, {
					fieldLabel: 'Дата согласия',
					width: 193+111,
					//~ xtype: 'swDateField',
					xtype: 'datefield',
					format: 'd.m.Y',
					startDay: 1,
					name: 'Chart_begDate',
					itemId: 'Chart_begDate',
					allowBlank: false,
					listeners: {
						blur: function(field) {
							me.saveChartInfo(field);
						}
					},
					invalidText: 'Неправильная дата',
					formatText: null,
					plugins: [ new Ext6.ux.InputTextMask('99.99.9999', true) ]
				}, {
					fieldLabel: 'Группа',
					width: 193+111,
					name: 'PersonModel_id',
					itemId: 'PersonModel_id',
					xtype: 'swPersonModelCombo',
					listeners: {
						change: function(field) {
							me.saveChartInfo(field);
						}
					}
				}, {
					name: 'PersonHeight',
					itemId: 'PersonHeight',
					xtype: 'numberfield',
					fieldLabel: 'Рост (см)',
					width: 48+106+5,
					labelWidth: 106,
					hideTrigger: true,
					listeners: {
						change: function(field, newVal, oldVal) {
							me.recalcIMT();
						}
					}
				}, {
					layout: 'hbox',
					xtype: 'panel',
					border: false,
					items: [
						{
							name: 'PersonWeight',
							itemId: 'PersonWeight',
							xtype: 'numberfield',
							fieldLabel: 'Вес (кг)',
							width: 48+106+5,
							labelWidth: 106,
							hideTrigger: true,
							listeners: {
								change: function(field, newVal, oldVal) {
									me.recalcIMT();
								}
							}
						}, {
							xtype: 'displayfield',
							width: 200,
							labelWidth: 50,
							itemId: 'imt',
							fieldLabel: 'ИМТ',
							labelAlign: 'right',
							value: ''
						}
					]
				}, {
					xtype: 'fieldset',
					collapsible: true,
					itemId: 'KS',
					width: '100%',
					cls: 'fieldset-default',
					title: langs('Каналы связи'),
					padding: '0 0 0 0',

					defaults: {
						padding: '0 0 0 25'
					},

					items: [
						{
							layout: 'column',
							border: false,
							items: [{
								layout: 'vbox',
								border: false,
								items: [{
									fieldLabel: 'Приложение',
									name: 'application',
									labelWidth: 90,
									width: 192+90,
									xtype: 'textfield',
									disabled: true,
									value: 'Нет информации',
									setValue1: function(x) {
										x = x ? 'Установлено' : 'Не установлено';
										this.setValue(x);
									},
									listeners: {
										render: function (field) {
											if(field.disabled){ //если поле application не установлено, то дисейблим radiofield
												var col = field.findParentByType('panel');
												var panel = field.ownerCt.ownerCt;
												var radiofieldsCol = panel.query('radiofield');
												radiofieldsCol[0].setDisabled(true);
											}
										}
									}
								}, {
									boxLabel: 'Напоминания Push',
									xtype: 'checkbox',
									disabled: true,
									rowspan: 2,
									padding: '0 0 0 95'
								}]
							}, {
								xtype: 'radiofield',
								boxLabel  : '',
								name : 'feedback',
								inputValue: 5,
								padding: '0 0 0 10'
							}]
						}, {
							layout: 'column',
							border: false,
							items: [{
								fieldLabel: 'Сайт',
								name: 'portal',
								labelWidth: 90,
								width: 192+90,
								xtype: 'textfield',
								disabled: true,
								value: 'Не используется',
								setValue1: function(x) {
									x = x ? 'Используется' : 'Не используется';
									this.setValue(x);
								},
								listeners: {
									render: function (field) {
										if(field.disabled){ //если поле application не установлено, то дисейблим radiofield
											var col = field.findParentByType('panel');
											var panel = field.ownerCt.ownerCt;
											var radiofieldsCol = panel.query('radiofield');
											radiofieldsCol[1].setDisabled(true);
										}

										//debugger;
									}
								}
							}, {
								xtype: 'radiofield',
								boxLabel  : '',
								name : 'feedback',
								inputValue: 4,
								padding: '0 0 0 10'
							}]
						}, {
							layout: 'column',
							border: false,
							items: [{
								fieldLabel: 'Эл.почта',
								labelWidth: 90,
								width: 192+90,
								name: 'email',
								xtype: 'textfield',
								listeners: {
									'blur': function(field, event, eOpts) {
										me.saveChartInfo(field);
									}
								}
							}, {
								xtype: 'radiofield',
								boxLabel  : '',
								name : 'feedback',
								inputValue: 3,
								padding: '0 0 0 10'
							}]
						}, {
							layout: 'column',
							border: false,
							items: [{
								fieldLabel: 'СМС',
								labelWidth: 90,
								width: 192+90,
								name: 'sms',
								xtype: 'textfield',
								getValue: function() {
									var v = this.getRawValue();
									if(v && v.length>0) {
										v = v.replace(/[ \(\)_]/g,'');
										if(v.length==12 && v.slice(0,2)=='+7') return v;
										else return '';
									} else return '';
								},
								setValue: function(x) {
									if(!x) { this.setRawValue(''); return '';}
									var regexp = /^(\+?7)?[\s\-]?\(?(\d{3})\)?[\s\-]?(\d{3})[\s\-]?(\d{2})[\s\-]?(\d{2})$/;
									x = x.replace(/[ \(\)_]/g,'');
									if ( !regexp.test(x) ) {
										this.setRawValue('');
									} else {
										this.setRawValue(x.replace(regexp,'+7 $2 $3 $4 $5'));
									}
								},
								plugins: [ new Ext6.ux.InputTextMask('+7 999 999 99 99', true) ],
								listeners: {
									'focus': function(field, event, eOpts) {
										setTimeout(function() {
											var pos=0;
											var s=field.getValue();
											if(s && s.length) {
												pos=s.indexOf('_');
												if(pos<0) pos=s.length;
											}
											document.getElementById(field.getInputId()).selectionStart = pos;
											document.getElementById(field.getInputId()).selectionEnd = pos;
										}, 10);
									},
									'blur': function(field, event, eOpts ) {
										if(!field.allowBlank) setTimeout(function() { field.setAllowBlank(false);}, 10);
										me.saveChartInfo(field);
										me.get('voice').setValue(me.get('sms').getValue());
									}
								}
							}, {
								xtype: 'radiofield',
								boxLabel  : '',
								name : 'feedback',
								inputValue: 2,
								padding: '0 0 0 10'
							}]
						}, {
							layout: 'column',
							border: false,
							items: [{
								fieldLabel: 'Голос',
								labelWidth: 90,
								width: 192+90,
								name: 'voice',
								xtype: 'textfield',
								getValue: function() {
									var v = this.getRawValue();
									if(v && v.length>0) {
										v = v.replace(/[ \(\)_]/g,'');
										if(v.length==12 && v.slice(0,2)=='+7') return v; 
										else return '';
									} else return '';
								},
								setValue: function(x) {
									if(!x) { this.setRawValue(''); return '';}
									var regexp = /^(\+?7)?[\s\-]?\(?(\d{3})\)?[\s\-]?(\d{3})[\s\-]?(\d{2})[\s\-]?(\d{2})$/;

									if ( !regexp.test(x) ) {
										this.setRawValue('');
									} else {
										this.setRawValue(x.replace(regexp,'+7 $2 $3 $4 $5'));
									}
								},
								plugins: [ new Ext6.ux.InputTextMask('+7 999 999 99 99', true) ],
								listeners: {
									'focus': function(field, event, eOpts) {
										setTimeout(function() {
											var pos=0;
											var s=field.getValue();
											if(s && s.length) {
												pos=s.indexOf('_');
												if(pos<0) pos=s.length;
											}
											document.getElementById(field.getInputId()).selectionStart = pos;
											document.getElementById(field.getInputId()).selectionEnd = pos;
										}, 10);
									},
									'blur': function(field, event, eOpts ) {
										if(!field.allowBlank) setTimeout(function() { field.setAllowBlank(false);}, 10);
										me.saveChartInfo(field);
										me.get('sms').setValue(me.get('voice').getValue());
									}
								}
							}, {
								xtype: 'radiofield',
								boxLabel  : '',
								name : 'feedback',
								inputValue: 1,
								padding: '0 0 0 10',
							}]
						}
					]
				}, //--каналы связи
				{
					xtype: 'fieldset',
					collapsible: true,
					itemId: 'CP',
					cls: 'fieldset-default',
					width: '100%',
					title: langs('Целевые показатели'),
					padding: '0 0 0 0',

					defaults: {
						padding: '0 0 0 25'
					},

					items: [
						{
							layout: 'column',
							border: false,
							ratetype_id: 53,
							LabelRate_id: 1,
							items: [
								{
									xtype: 'displayfield',
									labelSeparator: '',
									fieldLabel: 'САД',
									labelAlign: 'left',
									labelWidth: 25,
									width: 25
								}, {
									layout: 'column',
									border: false,
									defaults: {
										width: 150,
										labelWidth: 50,
										padding: '0 0 5 0',
										labelAlign: 'right',
										hideTrigger: true,
										saved: true,
											minValue: 0,
											maxValue: 10000,
											allowDecimals: false,
											listeners: {
												'blur': function() {
													me.saveChartRate(this);
												},
												'change': function() {
													this.saved = false;
												}
											}
									},
									items: [
										{
											xtype: 'numberfield',
											itemId: 'min',
											fieldLabel: 'От',
											itemId: 'min'
										}, {
											xtype: 'numberfield',
											fieldLabel: 'До',
											itemId: 'max'
										}
									]
								}
							]
						}, {
							layout: 'column',
							border: false,
							ratetype_id: 54,
							LabelRate_id: 2,
							items: [
								{
									xtype: 'displayfield',
									labelSeparator: '',
									fieldLabel: 'ДАД',
									labelAlign: 'left',
									labelWidth: 25,
									width: 25
								}, {
									layout: 'column',
									border: false,
									defaults: {
										width: 150,
										labelWidth: 50,
										padding: '0 0 5 0',
										labelAlign: 'right',
										hideTrigger: true,
										saved: true,
										minValue: 0,
										maxValue: 10000,
										allowDecimals: false,
										listeners: {
											'blur': function(field) {
												me.saveChartRate(field);
											},
											'change': function() {
												this.saved = false;
											}
										}
									},
									items: [
										{
											xtype: 'numberfield',
											fieldLabel: 'От',
											itemId: 'min',
										}, {
											xtype: 'numberfield',
											fieldLabel: 'До',
											itemId: 'max'
										}
									]
								}
							]
						}, {
							layout: 'column',
							border: false,
							ratetype_id: 38,
							LabelRate_id: 3,
							items: [
								{
									xtype: 'displayfield',
									labelSeparator: '',
									fieldLabel: 'ЧСС',
									labelAlign: 'left',
									labelWidth: 25,
									width: 25
								}, {
									layout: 'column',
									border: false,
									
									defaults: {
										width: 150,
										labelWidth: 50,
										padding: '0 0 5 0',
										labelAlign: 'right',
										hideTrigger: true,
										saved: true,
										minValue: 0,
										maxValue: 10000,
										allowDecimals: false,
										listeners: {
											'blur': function(field) {
												me.saveChartRate(field);
											},
											'change': function() {
												this.saved = false;
											}
										}
									},
									items: [
										{
											xtype: 'numberfield',
											fieldLabel: 'От',
											itemId: 'min'
										}, {
											xtype: 'numberfield',
											fieldLabel: 'До',
											itemId: 'max'
										}
									]
								}
							]
						}, {
							layout: 'column',
							border: false,
							ratetype_id: 203,
							LabelRate_id: 4,
							items: [
								{
									xtype: 'displayfield',
									labelSeparator: '',
									fieldLabel: 'Температура',
									labelAlign: 'left',
									labelWidth: 85,
									width: 85
								}, {
									layout: 'column',
									border: false,
									
									defaults: {
										width: 150,
										labelWidth: 50,
										padding: '0 0 5 0',
										labelAlign: 'right',
										hideTrigger: true,
										saved: true,
										minValue: 0,
										maxValue: 10000,
										allowDecimals: true,
										listeners: {
											'blur': function(field) {
												me.saveChartRate(field);
											},
											'change': function() {
												this.saved = false;
											}
										}
									},
									items: [
										{
											xtype: 'numberfield',
											fieldLabel: 'От',
											itemId: 'min'
										}, {
											xtype: 'numberfield',
											fieldLabel: 'До',
											itemId: 'max'
										}
									]
								}
							]
						}
					]
				}
			]
		});

		me.measures = Ext6.create('common.PolkaWP.RemoteMonitoring.ObserveChartMeasures', {
			border: false,
			ownerWin: me
		});
		
		me.msgGrid = new Ext6.grid.Panel({
			border: false,
			cls: 'remote-monitoring-simple-grid',
			columns: [
				{	header: langs('Дата'), dataIndex: 'MessageDate', 
					type: 'datecolumn', 
					format: 'd.m.Y', 
					width: 105, minWidth: 105,
					renderer: function (value, metaData, record) {
						var v=value;
						if(typeof v=='object') v=Ext6.Date.format(v, 'd.m.Y');
						var dt = Date.now();
						dt.setDate(dt.getDate()-1);
						if(v==Ext6.Date.format(Date.now(), 'd.m.Y')) return "Сегодня";
						else if(v==Ext6.Date.format(dt, 'd.m.Y')) return "Вчера";
						else return v;
					},
				},
				{	header: langs('Текст сообщения'), dataIndex: 'LabelMessage_Text', type: 'string', flex: 1,
					
				},
				{	header: langs('Канал связи'), dataIndex: 'FeedbackMethod_Name', type: 'string', width: 100	}
			],
			load: function() {
				Ext6.Ajax.request({
					url: '/?c=PersonDisp&m=loadLabelMessages',
					params: me.msgParams,
					callback: function(options, success, response)
					{
						if ( success )
						{
							//TAG: загрузка таблицы измерений
							var data = Ext6.util.JSON.decode(response.responseText);

							if(me.msgParams.start==0) {
								me.msgGrid.getStore().removeAll();
							}
							var n = data.data.messages.totalCount;
							me.queryById('tabMessages').setTitle('Сообщения <span class="number-indicator">'+(n>0 ? n : '')+'</span>');
							
							me.msgGrid.getStore().add_data(data.data);
							me.queryById('nextMessages').setVisible(data.data.messages.totalCount > me.msgGrid.getStore().getCount());
						}
					}
				});
			},
			store: new Ext6.data.SimpleStore({
				autoLoad: false,
				fields: [],
				data: [],
					sorters: [{
						property: 'MessageDate',
						direction: 'DESC'
					}
				],
				add_data: function(data) {
					if (typeof(data) != 'object') return false;
					messages = data.messages.data;
					
					var record = null;
					for (var i = 0; i < messages.length; i++) {
						//TAG: заполнение Store
						record = new Ext6.data.Record(messages[i]);
						
						record.set('MessageDate', Date.parse(record.get('MessageDate')));
						
						record.commit();
						this.add(record);

					}
				}
			})
		});
		
		me.msgPanel = new Ext6.form.Panel({ //вкладка с сообщениями

			height: '100%',
			border: false,
			items: [
				me.msgGrid,
				{
					layout: {
								type: 'vbox',
								align: 'center'
							},
					border: false,
					padding: 14,
					items: [
						{
							xtype: 'button',
							text: 'ПОКАЗАТЬ ЕЩЁ 7 СООБЩЕНИЙ',
							userCls: 'button-next',
							width: 310,
							padding: 4,
							hidden: true,
							itemId: 'nextMessages',
							handler: function() {
								me.queryById('nextMessages').hide();
								me.msgParams.start+=me.msgParams.limit;
								me.msgGrid.load();
							}
						}
					]
				}
			]
		});

		me.Tabs = new Ext6.tab.Panel({
			border: false,
			cls: 'custom_tab_bar',
			tabBar: {
				cls: 'white-tab-bar',
				border: false,
				
				defaults: {
					cls: 'simple-tab'
				},
				items: [
				{ xtype: 'tbfill' },
				{
					xtype: 'button',
					itemId: 'buttonAddMeasure',
					iconCls: 'panicon-add',
					height: 25,
					text: '',
					padding: "13px 10px",
					userCls: 'button-without-frame',
					tooltip: 'Добавить замер',
					handler: function() {
						if(me.measures.cardPanel.layout.getActiveItem().itemId=='grafcard') {
							me.measures.cardPanel.setActiveItem(0);
							me.queryById('segmentedbuttonTabGraf').setValue(0);
						}
						me.measures.gridToolbar.items.getAt(0).click();
					}
				},
				{
					xtype: 'segmentedbutton',
					itemId: 'segmentedbuttonTabGraf',
					userCls: 'segmentedButtonGroup segmentedButtonGroupTabGraf',
					height: 25,
					padding: '0 10 0 10',
					items: [{
						//~ text: 'Таблица',
						tooltip: 'Таблица',
						width: 40,
						iconCls: 'distmonitoring-is-grid',
						pressed: true,
						handler: function() {
							me.measures.cardPanel.setActiveItem(0);
						}
					}, {
						//~ text: 'График',
						tooltip: 'График',
						width: 40,
						iconCls: 'distmonitoring-is-graf',
						handler: function() {
							me.measures.loadchart();
							me.measures.cardPanel.setActiveItem(1);
						}
					}]
				}
				]
			},
			items: [
				{
					title: 'Пациент',
					border: false,
					items: [
						me.PersonFullInfo
					],
					listeners: {
						activate: function (tab_id, flag) {
							me.queryById('buttonAddMeasure').hide();
							me.queryById('segmentedbuttonTabGraf').hide();
						}
					}
				}, {
					title: 'Показания',
					itemId: 'tabMeasures',
					border: false,
					items: [
						me.measures
					],
					listeners: {
						activate: function (tab_id, flag) {
							me.queryById('buttonAddMeasure').show();
							me.queryById('segmentedbuttonTabGraf').show();
						}
					}
				}, {
					title: 'Сообщения',
					itemId: 'tabMessages',
					border: false,
					items: [
						me.msgPanel
					],
					listeners: {
						activate: function (tab_id, flag) {
							me.queryById('buttonAddMeasure').hide();
							me.queryById('segmentedbuttonTabGraf').hide();
						}
					}
				}
			]
		});

		me.Panel = new Ext6.Panel({
			border: false,
			hidden: true,
			region: 'center',
			items: [
				me.Tabs
			]
		});
		
		me.tplMaster = new Ext6.XTemplate(
`<div class="invite-block">
<div class="steps-item {[values.LabelInvite_id ? 'step-hidden' : '']}"><a href="#" class="steps-icon"></a><span class="step-hypertext step-invite {[values.enableToInvite ? '' : 'step-text-disabled']}" onClick="{me}.ownerWin.inviteSelectedPerson();">Пригласить пациента</span></div>

<div class="steps-item {[values.LabelInviteStatus_id == 1 ? '' : 'step-hidden']}"><a href="#" class="steps-icon step-accepted"></a><span class="step-text step-invite-sent">Приглашение отправлено {LabelInviteStatus_Date}</span></div>

<div class="steps-item {[values.LabelInviteStatus_id == 2 ? '' : 'step-hidden']}"><a href="#" class="steps-icon step-accepted"></a><span class="step-hypertext step-invite-accepted" onClick="{me}.ownerWin.changeStatusSelectedPerson();">Пациент принял приглашение</span></div>

<div class="steps-item {[values.LabelInviteStatus_id == 3 ? '' : 'step-hidden']}"><a href="#" class="steps-icon step-denied"></a><span class="step-alerttext step-invite-reject">Приглашение отклонено {LabelInviteStatus_Date}<br>Причина: "{LabelInvite_RefuseCause}" <a hreg='#' onClick="{me}.ownerWin.inviteSelectedPerson();">Повторить приглашение</a></span> </div>

<div class="steps-item {[false ? '' : 'step-hidden']}"><a href="#" class="steps-icon"></a><span class="step-text step-priem">Назначить прием</span></div>

<div class="steps-item {[false ? '' : 'step-hidden']}"><a href="#" class="steps-icon"></a><span class="step-text step-osmotr">Принять пациента. Провести осмотр.</span></div>

<div class="steps-item"><a href="#" class="steps-icon"></a><span class="step-hypertext step-add {[values.enableToAddProgram ? '' : 'step-text-disabled']}" onClick="{me}.ownerWin.addToProgram();">Добавить в программу</span></div>
</ul>
</div>`
		);	
		
		me.NewPersonPanel = new Ext6.Panel({
			border: false,
			hidden: true,
			region: 'center',
			items: [
				me.PersonInfo,
				{
					xtype: 'panel',
					border: false,
					padding: '0 0 0 20',
					itemId: 'labelPersonInOutMonitoring',
					html: '',
				},
				{
					itemId: 'Master',
					border: false,
					html: '',
					items: []
				}
			]
		});
		
		me.toolMenu = Ext6.create('Ext6.menu.Menu', {
			userCls: 'menuWithoutIcons',
			items: [{
				text: 'Отправить напоминание',
				itemId: 'menuSendNotice',
				handler: function() {
					me.remindPerson();
				}
			}, {
				text: 'Отправить сообщение',
				itemId: 'menuSendMsg',
				handler: function() {
					me.sendMessagePerson();
				}
			}, {
				text: 'Исключить пациента из программы',
				itemId: 'menuRemoveFromMonitoring',
				handler: function() {
					me.ownerWin.delFromProgram();
				}
			}, {
				text: 'Печатать лист измерений',
				itemId: 'menuPrintMeasures',
				hidden: true, //нет в ТЗ
				handler: function() {
				}
			}, {
				text: 'Печатать согласие участия',
				itemId: 'menuPrintConsent',
				handler: function() {
					var 
						formdate = me.queryById('Chart_begDate').getValue().dateFormat('d.m.Y'),
						MailingConsDT = me.MailingConsDT;
					
					printBirt({
						'Report_FileName': 'DistMonitoringConsent.rptdesign',
						'Report_Format': 'pdf',
						'Report_Params': 
							'&paramPerson=' + me.data.Person_id +
							'&paramMedStaffFact=' + getGlobalOptions().CurMedStaffFact_id +
							'&paramLpu=' + getGlobalOptions().lpu_id +
							'&paramPhone=' + me.activeForm.findField('voice').getValue() + 
							'&paramDate=' + formdate + 
							'&paramFlag=' + (MailingConsDT ? '2' : '1')
					});
				}
			}, {
				text: 'Посмотреть историю',
				itemId: 'menuHistory',
				handler: function() {
					getWnd('swRemoteMonitoringInviteHistoryWindow').show({PersonLabel_id: me.PersonLabel_id, PersonFio: me.PersonFio});
				}
			}]
		});
		
		me.PersonTitleToolPanel = Ext6.create('Ext6.Toolbar', {
			region: 'east',
			style: 'background-color: #fff;',
			cls: 'remote-monitor-persontitle-toolbar',
			border: false,
			width:80,
			right: 0,
			padding: '0 0 0 0',
			hidden: true,
			items: [{
				xtype: 'tbspacer',
				width: 10
			}, {
				userCls: 'button-without-frame',
				iconCls: 'panicon-theedots',
				padding: "0 6px 0 0px",
				tooltip: langs('Меню'),
				handler : function() {
					me.toolMenu.showBy(this);
				}
			}, {
				xtype: 'button',
				padding: "0 6px 0 0px",
				userCls: 'button-without-frame',
				iconCls: 'icon-maximize',
				itemId: 'buttonMaximize',
				tooltip: langs('Открыть во весь экран'),
				handler: function (butt) {
					me.ownerWin.viewtype = 1-me.ownerWin.viewtype;
					me.ownerWin.toggleView(me.ownerWin.viewtype);
				}
			}]
		});
		
		me.PersonTitle = new Ext6.Panel({
			border: false,
			header: false,
			//~ cls: 'persontitle',
			itemId: 'persontitle',
			region: 'north',
			layout: 'border',
			height: 32,
			items: [
				{
					border: false,
					itemId: 'persontitlehtml',
					html: '',
					region: 'west',
					height: 32
				},
				me.PersonTitleToolPanel
			]
		});

		Ext6.apply(this, {
			border: false,
			items: [
				me.PersonTitle,
				me.NewPersonPanel,
				me.Panel
			]
		});

		me.callParent(arguments);

		me.query('radiofield').forEach(function(radio) {
			radio.addListener('change', function(field, newVal, oldVal) {
				if(newVal) {
					field.setBoxLabel('Предпочтительный');
					me.setFeedback(field.inputValue);
				} else field.setBoxLabel('');
			})
		});
	}
});