

Ext6.define('common.PolkaWP.RemoteMonitoring.RemoteMonitoringWindow', {
	paging: 0, //true/false
	requires: [
		'common.PolkaWP.RemoteMonitoring.ObserveChartForm'
	],
	layout: 'border',
	addHelpButton: Ext6.emptyFn,
	addCodeRefresh: Ext6.emptyFn,
	header: false,
	alias: 'widget.swRemoteMonitoringWindow',
	maximized: true,
	closeToolText: 'Закрыть',
	closable: true,
	closeAction: 'hide',
	width: 1000,
	cls: 'arm-window-new remote-monitor-window',
	extend: 'base.BaseForm',
	renderTo: main_center_panel.body.dom,
	title: 'Дистанционный мониторинг',
	header: getGlobalOptions().client == 'ext2',
	viewtype : 0,
	params: {
		status: '',
		MonitorLpu_id: 0,
		Diags: '',
		paging: 0,
		fio: ''
	},
	toggleView: function(viewtype) { //0 - обычный режим, 1 - карта наблюдения во все окно
		var me = this;
		var btnMax = me.ChartPanel.queryById('buttonMaximize');
		if(viewtype) {
			me.ChartPanelWidth = me.ChartPanel.getWidth();
			
			me.MainPanel.items.items[1].setWidth(0);
			me.grid.hide();
			me.get('tabs').hide();
			me.toolbar.hide();
			
			me.ChartPanel.setWidth(me.getWidth());
			
			btnMax.setIconCls('icon-restore');
			btnMax.setTooltip(langs('Восстановить размер'));
		} else {
			me.MainPanel.items.items[1].setWidth(10);
			me.grid.show();
			me.get('tabs').show();
			me.toolbar.show();
			me.ChartPanel.setWidth(me.ChartPanelWidth);
			
			btnMax.setIconCls('icon-maximize');
			btnMax.setTooltip(langs('Открыть во весь экран'));
		}
	},
	constrain: true,
	userMedStaffFact: null,
	status: 'on', //текущее значение переключателя-таба "статус" / new, on, off, all
	setTabStatus: function(statusId) {
		var statusNick = ['new','on','off','all'];
		if(Ext6.isEmpty(statusNick[statusId])) statusId=1;
		this.status = statusNick[statusId];
		this.queryById('labelDateFilter').setVisible( this.status=='off' || this.status=='all' );
		this.queryById('datefilter').setVisible( this.status=='off' || this.status=='all' );
		this.queryById('datefilter').setAllowBlank( !(this.status=='off' || this.status=='all') );
		this.queryById('DispOutType_id').setVisible( this.status=='off' || this.status=='all' );
		this.queryById('LabelInviteStatus_id').setVisible( this.status=='new' || this.status=='all' );
		
		this.doSearch(false,false);
	},
	normReg: function(s) {
		if(!s || Ext6.isEmpty(s) || s.length==0) return '';
		return s.slice(0,1).toUpperCase()+s.slice(1).toLowerCase();
	},
	getFio: function(rec) {
		return this.normReg(rec.get('Person_SurName'))+' '+this.normReg(rec.get('Person_FirName'))+' '+this.normReg(rec.get('Person_SecName'));
		/* //первоначальный вариант
		if(Ext6.isEmpty(s) || s.length==0) return '';
		var r='';
		s.toLowerCase().split(' ').forEach(function(e) {r+=e.slice(0,1).toUpperCase()+e.slice(1)+' ';});
		return r;*/
	},
	get: function(id) {//вспомогательная функция получения элемента формы
		if(this.queryById(id)) return this.queryById(id);
		else {
			var el = this.query('[name='+id+']');
			if(el.length>0) return el[0];
		}
		return false;
	},
	checkFilterStatus: function() {
		var me = this,
			clear = true;

		if(me.get('diags').getValue() && me.get('diags').getValue().length>0 ) clear = false;

		if(	me.dateMenu.isVisible() && me.dateMenu.getDates().length==2 && me.dateMenu.defaultDates && me.dateMenu.defaultDates.length==2
		&& (
			me.dateMenu.defaultDates[0].dateFormat('d.m.Y')!=me.dateMenu.getDates()[0].dateFormat('d.m.Y') ||
			me.dateMenu.defaultDates[1].dateFormat('d.m.Y')!=me.dateMenu.getDates()[1].dateFormat('d.m.Y')
			)
		) clear = false;
		if(me.get('labelFilter').getValue() != 1) clear = false;
		if(me.get('DispOutType_id').isVisible() && me.get('DispOutType_id').getValue()>0 ) clear = false;
		if(me.get('LabelInviteStatus_id').isVisible() && me.get('LabelInviteStatus_id').getValue()>0 ) clear = false;

		if(clear)
			me.get('filterButton').removeCls('filter-active');
		else
			me.get('filterButton').addCls('filter-active');

	},
	isEnableToAdd: function(record) {//проверяет по данной записи возможность добавить в программу мониторинга
		var enableToAdd = true;
		
		if(record.get('Lpu_id')!=getGlobalOptions().lpu_id) { 
			enableToAdd = false;
			log('Нельзя добавить в программу, т.к. у человека нет открытого основного прикрепления на текущую дату к МО Пользователя');
		}
		if(! Ext6.isEmpty(record.get('PersonLabel_disDate')) ) {
			enableToAdd = false;
			log('Нельзя добавить в программу, т.к. у человека нет метки с открытой записью'); //ТЗ: 2. У человека есть запись о наличии метки (запись открыта)
		}
		if(record.get('Label_id')==1) {
			if(!(!Ext6.isEmpty(record.get('PersonDisp_id')) && Ext6.isEmpty(record.get('PersonDisp_endDate')))) {
				enableToAdd = false;
				log('Нельзя добавить в программу, т.к. у человека нет открытой контрольной карты диспансерного наблюдения в МО Пользователя по тому же диагнозу что и метка');
			}
		}
		if(!Ext6.isEmpty(record.get('Chart_id')) && Ext6.isEmpty(record.get('Chart_endDate')) ) {
			enableToAdd = false;
			log('Нельзя добавить в программу, т.к. у человека есть открытая карта наблюдений в МО Пользователя');
		}
		return enableToAdd;
	},
	loadPersonLabelCounts: function() {
		var me = this;
		var params = { MonitorLpu_id: getGlobalOptions().lpu_id , Label_id: me.queryById('labelFilter').getValue() };
		if(me.queryById('datefilter').getValue()) {
			params.outBegDate = me.queryById('datefilter').getDateFrom();
			outEndDate = me.queryById('datefilter').getDateTo() ? me.queryById('datefilter').getDateTo() : me.queryById('datefilter').getDateFrom();
			params.outEndDate = new Date(outEndDate.getTime());
			params.outEndDate.setDate(params.outEndDate.getDate()+1);
		}

		Ext6.Ajax.request({
			url: '/?c=PersonDisp&m=getPersonLabelCounts',
			params: params,
			callback: function(options, success, response)
			{
				if ( success )
				{
					var data = Ext6.util.JSON.decode(response.responseText);
					me.queryById('tabNew').setTitle('Новые <span class="number-indicator">'+(data.new>0 ? data.new : '')+'</span>');
					me.queryById('tabOn').setTitle('Включенные <span class="number-indicator">'+(data.on>0 ? data.on : '')+'</span>');
					me.queryById('tabOff').setTitle('Выбывшие <span class="number-indicator">'+(data.off>0 ? data.off : '')+'</span>');
					me.queryById('tabAll').setTitle('Все пациенты <span class="number-indicator">'+(data.all>0 ? data.all : '')+'</span>');
				}
			}
		});
	},
	openSelectedChart: function() {
		var me = this;
		if(me.grid.getSelection().length==0) return false;
		var record = me.grid.getSelection()[0],
			enableToAddProgram = me.isEnableToAdd(record),
			enableToInvite = !((!Ext6.isEmpty(record.get('Chart_id')) && Ext6.isEmpty(record.get('Chart_endDate')))
				|| !Ext6.isEmpty(record.get('PersonLabel_disDate')));
		var params = {
			action: 'edit',
			Label_id: record.get('Label_id'),
			PersonLabel_id: record.get('PersonLabel_id'),
			StatusNick: record.get('StatusNick'),
			Person_id: record.get('Person_id'),
			Server_id: record.get('Server_id'),
			Diag_Code: record.get('Diag_Code'),
			Diag_Name: record.get('Diag_Name'),
			PersonDisp_id: record.get('PersonDisp_id'),
			PersonDisp_begDate: record.get('PersonDisp_begDate'),
			Chart_id: record.get('Chart_id'),
			Chart_endDate: record.get('Chart_endDate'),
			DispOutType_Name: record.get('DispOutType_Name'),
			PersonModel_id: record.get('PersonModel_id'),
			Person_BirthDay: record.get('Person_BirthDay'),
			Person_DeadDT: record.get('Person_DeadDT'),
			BirthDayFormatted: Ext6.Date.format(Date.parse(record.get('Person_BirthDay')), 'd.m.Y'),
			PersonFio: me.getFio(record),
			Person_SurName: me.normReg(record.get('Person_SurName')),
			Person_FirName: me.normReg(record.get('Person_FirName')),
			Person_SecName: me.normReg(record.get('Person_SecName')),
			PersonSex: record.get('Sex_id')==1 ? 'man' : 'woman',
			Person_IsRefuse: record.get('Person_IsRefuse'),
			Person_IsFedLgot: record.get('Person_IsFedLgot'),
			Person_IsRegLgot: record.get('Person_IsRegLgot'),
			Lpu_Nick: record.get('Lpu_Nick'),
			AttachNum: record.get('AttachNum'),
			AttachDate: record.get('AttachDate'),
			enableToAddProgram: enableToAddProgram, //доступность кнопки "добавить" в мастере
			enableToInvite: enableToInvite, //доступность кнопки "пригласить" в мастере
			LabelInvite_id: record.get('LabelInvite_id'),
			LabelInviteStatus_id: record.get('LabelInviteStatus_id'),
			LabelInviteStatus_Date: record.get('LabelInviteStatus_Date'),
			LabelInvite_RefuseCause: record.get('LabelInvite_RefuseCause')
		};
		me.ChartForm.open(params);
		me.ChartForm.show();
	},
	openPersonEmkWindow: function() {
		var me=this;
		if (me.grid.getSelectionModel().hasSelection()) {
			var record = me.grid.getSelectionModel().getSelection()[0];
			if (typeof record != 'object' || Ext6.isEmpty(record.get('Person_id'))) return false;
			if(getGlobalOptions().client == 'ext2') {
				getWnd('swPersonEmkWindow').show({
					Person_id: record.get('Person_id'),
					ARMType: 'common',
					readOnly: false,

					callback: function ()
					{
						me.Person_id=record.get('Person_id');
						me.doSearch(0,0);
					}.createDelegate(this)
				});
			} else {
				getWnd('swPersonEmkWindowExt6').show({
					Person_id: record.get('Person_id'),
					Server_id: record.get('Server_id'),
					PersonEvn_id: record.get('PersonEvn_id'),
					userMedStaffFact: me.userMedStaffFact,
					MedStaffFact_id: getGlobalOptions().CurMedStaffFact_id,
					LpuSection_id: getGlobalOptions().CurLpuSection_id,
					TimetableGraf_id: null,
					EvnDirectionData: null,
					ARMType: me.ARMType,
					callback: function(retParams) {
						me.Person_id=record.get('Person_id');
						me.doSearch(0,0);
					}
				});
			}
		}
	},
	doSearch: function(doReset, doValidate) {
		var me = this;
		var fil = me.FilterPanel.getForm();
		if (doReset) {
			fil.reset();
			me.dateMenu.setDates(me.dateMenu.defaultDates);
		}
		if(doValidate && !me.FilterPanel.isValid()) {
			Ext6.Msg.alert(langs('Сообщение'), langs('Не заполнены обязательные поля'));
			return;
		}

		/*var params = new Object();
		params = {
			status: me.status,
			//MedStaffFact_id: getGlobalOptions().CurMedStaffFact_id,
			Lpu_id: getGlobalOptions().lpu_id,
			Diags: Ext6.util.JSON.encode(me.queryById('diags').getValue()),
			paging: me.paging
		};*/
		me.params.status = me.status;
		me.params.MonitorLpu_id = getGlobalOptions().lpu_id;
		me.params.Diags = Ext6.util.JSON.encode(me.queryById('diags').getValue());
		me.params.paging = me.paging;
		var fiofilter = Ext6.ComponentQuery.query('[itemId=fiofilter]')[0];
		if(fiofilter)
			me.params.fio = fiofilter.getValue().toUpperCase();
		me.params.Label_id = me.queryById('labelFilter').getValue();
		if(me.queryById('DispOutType_id').isVisible()) me.params.DispOutType_id = me.queryById('DispOutType_id').getValue();
		if(me.queryById('LabelInviteStatus_id').isVisible()) me.params.LabelInviteStatus_id = me.queryById('LabelInviteStatus_id').getValue();
		if(me.queryById('datefilter').isVisible() && me.queryById('datefilter').getValue()) {
			me.params.outBegDate = me.queryById('datefilter').getDateFrom();
			outEndDate = me.queryById('datefilter').getDateTo() ? me.queryById('datefilter').getDateTo() : me.queryById('datefilter').getDateFrom();
			me.params.outEndDate = new Date(outEndDate.getTime());
			me.params.outEndDate.setDate(me.params.outEndDate.getDate()+1);
		}
		me.grid.getStore().removeAll();
		
		me.params.Person_id = null;
		if(!Ext6.isEmpty(me.Label_id)) {//аргумент при открытии формы
			me.params.Label_id = me.Label_id;
			me.Label_id = null; 
			me.queryById('labelFilter').setValue(me.params.Label_id);
		}
		
		me.grid.columnManager.getHeaderByDataIndex('Rate1_Value').setVisible(me.params.Label_id==1);
		me.grid.columnManager.getHeaderByDataIndex('Rate4_Value').setVisible(me.params.Label_id==7);
		
		me.grid.getStore().getProxy().setExtraParams(me.params);
		if(me.paging)
			me.grid.getStore().loadPage(1);
		else
			me.grid.getStore().load({
				callback: function(records, operation, success) {
					if (!success) {//ошибка - попробуем еще раз
						me.grid.getStore().load({
							callback: function(records, operation, success) {
								if (!success) {
									Ext6.Msg.alert('Ошибка','Ошибка запроса к серверу. Пожалуйста, повторите попытку чуть позже.')
								}           
							}
						});
					}           
				}
			});
		me.queryById('tabs').disable();
	},
	addToProgram: function() {
		var me = this;
		if(me.grid.getSelection().length>0) {
			var record = me.grid.getSelection()[0];
			//на всякий случай проверяем и здесь:
			if(me.isEnableToAdd(record)) {
				var params = {
					action: 'add',
					Label_id: record.get('Label_id'),
					StatusNick: record.get('StatusNick'),
					Person_id: record.get('Person_id'),
					Chart_id: record.get('Chart_id'),
					PersonModel_id: record.get('PersonModel_id'),
					Person_BirthDay: record.get('Person_BirthDay'),
					Person_DeadDT: record.get('Person_DeadDT'),
					BirthDayFormatted: Ext6.Date.format(Date.parse(record.get('Person_BirthDay')), 'd.m.Y'),
					AgeFormatted: getAgeString(record), //me.ChartForm.getAge(record),
					PersonFio: me.getFio(record),
					PersonSex: record.get('PersonSex')==1 ? 'man' : 'woman',
					Diag_Code: record.get('Diag_Code'),
					Diag_Name: record.get('Diag_Name'),
					Person_IsRefuse: record.get('Person_IsRefuse'),
					Person_IsFedLgot: record.get('Person_IsFedLgot'),
					Person_IsRegLgot: record.get('Person_IsRegLgot'),
					Lpu_Nick: record.get('Lpu_Nick'),
					AttachNum: record.get('AttachNum'),
					AttachDate: record.get('AttachDate'),
					ChartPersonDisp_id: record.get('ChartPersonDisp_id'),
					PersonDisp_id: record.get('PersonDisp_id'),
					PersonDisp_begDate: record.get('PersonDisp_begDate'),
					PersonLabel_id: record.get('PersonLabel_id'),
					Person_Phone: record.get('Person_Phone')
				};
				params.callback = function(data) {
					params.callback = null;
					params.Person_Phone = data.Person_Phone;
					params.allowMailing = data.allowMailing;
					params.dateConsent = data.dateConsent;
					me.ChartForm.open(params);
					me.queryById('btnAddToProgram').setDisabled(true);
					me.queryById('btnRemoveFromProgram').setDisabled(false);
					me.loadPersonLabelCounts();
					var row = me.grid.getSelection()[0];
					if(row && me.status!='all') {
						me.grid.getStore().remove(row);
					}
				}
				
				getWnd('swRemoteMonitoringConsentWindow').show(params);
			}
		}
	},
	delFromProgram: function() {
		var me = this;
		if(me.grid.getSelection().length) {
			var rec = me.grid.getSelection()[0];
			var personinfo = me.getFio(rec)
				+ ' Д/Р: ' + Ext6.Date.format(Date.parse(rec.get('Person_BirthDay')), 'd.m.Y')
				+ ' (' + getAgeString(rec)+')';

			getWnd('swRemoteMonitoringRemoveWindow').show({
				Person_id: rec.get('Person_id'),
				PersonInfo: personinfo,
				Chart_id: rec.get('Chart_id'),
				Label_id: rec.get('Label_id'),
				PersonLabel_id: rec.get('PersonLabel_id'),
				callback: function(data) {
					if(data.deleted) {
						var row = me.grid.getSelection()[0];
						if(row) {
							//TAG: запись в строку таблицы пациентов (исключен)
							row.set('DispOutType_Name', data.DispOutType_Name);
							row.set('Status', 30);
							row.set('StatusNick', 'off');
							row.set('Chart_endDate', data.endDate.dateFormat('d.m.Y'));
							row.set('Chart_id', null);
							row.commit();
							me.queryById('btnRemoveFromProgram').setDisabled(true);
							//~ if(me.isEnableToAdd(row))
								//~ Ext6.get('RemoteMonitorWin_add').dom.classList.remove('step-text-disabled');
							//~ else
								//~ Ext6.get('RemoteMonitorWin_add').dom.classList.add('step-text-disabled');
							me.queryById('btnAddToProgram').setDisabled(!me.isEnableToAdd(row));
							me.openSelectedChart();
							
							if(me.status!='all') {
								me.grid.getStore().remove(row);
							}
							me.loadPersonLabelCounts();
						}
					}
				}
			});
		}
	},
	inviteSelectedPerson: function() {
		var me = this,
			btnInviteEnable = false,
			list = [];
		
		me.grid.getSelection().forEach(function(person) {
			list.push(person.data);
			if( 
				(Ext6.isEmpty(person.get('Chart_id')) || !Ext6.isEmpty(person.get('Chart_endDate')))
				&& Ext6.isEmpty(person.get('PersonLabel_disDate')) 
			)
				btnInviteEnable = true;
		});
		
		if(btnInviteEnable) getWnd('swRemoteMonitoringInviteWindow').show({
			persons: list,
			callback: function() {
				me.doSearch(false,false);
			}
		});
	},
	changeStatusSelectedPerson: function() {
		var me = this;
		
		var params = {
			LabelInvite_id: me.grid.getSelection()[0].get('LabelInvite_id'),
			LabelInviteStatus_id: me.grid.getSelection()[0].get('LabelInviteStatus_id'),
			callback: function() {
				me.doSearch(false,false);
			}
		};
		getWnd('swRemoteMonitoringStatusWindow').show(params);
	},
	sendMessage: function() {
		var me = this,
			rec = me.grid.getSelection()[0];
		if(Ext6.isEmpty(rec.get('FeedbackMethod_id'))) {
			Ext6.Msg.alert(langs('Сообщение'),langs('У пациента не указан предпочтительный канал связи'));
			return;
		}
		getWnd('swRemoteMonitoringMessageWindow').show({
			Person_id: rec.get('Person_id'),
			Chart_id: rec.get('Chart_id'),
			PersonFio: me.getFio(rec),
			BirthDay: Ext6.Date.format(Date.parse(rec.get('Person_BirthDay')), 'd.m.Y'),
			Age: getAgeStringY(rec),
			FeedbackMethod_id: rec.get('FeedbackMethod_id'),
			email: rec.get('Person_Email') ? rec.get('Person_Email') : (rec.get('Chart_Email') ? rec.get('Chart_Email') : ''),
			phone: rec.get('Person_Phone') ? rec.get('Person_Phone') : (rec.get('Chart_Phone') ? rec.get('Chart_Phone') : '' ),
			callback: function() {
				me.ChartForm.msgGrid.load();
			}
		});
	},
	remindSelectedPerson: function() {
		var me = this,
			list = [];
		if(me.grid.getSelection().length==1) {
			var rec1 = me.grid.getSelection()[0];
			if(Ext6.isEmpty(rec1.get('FeedbackMethod_id'))) {
				Ext6.Msg.alert(langs('Сообщение'),langs('У пациента не указан предпочтительный канал связи'));
				return;
			}
		}
		me.grid.getSelection().forEach(function(person) {
			var phonenumber=person.get('Person_Phone') ? person.get('Person_Phone') : (person.get('Chart_Phone') ? person.get('Chart_Phone') : '' );
			//~ var regexp = /^(\+?7)?[\s\-]?\(?(\d{3})\)?[\s\-]?(\d{3})[\s\-]?(\d{2})[\s\-]?(\d{2})$/;
			//~ phonenumber = phonenumber.replace(/[ \(\)_]/g,'');
			//~ if ( !regexp.test(phonenumber) ) {
				//~ phonenumber = '';
			//~ } else {
				//~ phonenumber = phonenumber.replace(regexp,'($2)-$3-$4-$5');
			//~ }
			list.push({
				Person_SurName: me.normReg(person.get('Person_SurName')),
				Person_FirName: me.normReg(person.get('Person_FirName')),
				Person_SecName: me.normReg(person.get('Person_SecName')),
				Person_id: person.get('Person_id'),
				Label_id: person.get('Label_id'),
				Chart_id: person.get('Chart_id'),
				email: person.get('Person_Email') ? person.get('Person_Email') : (person.get('Chart_Email') ? person.get('Chart_Email') : ''),
				phone: phonenumber,
				FeedbackMethod_id: person.get('FeedbackMethod_id')
			});
		});
		if(!me.RemindMask) {
			me.RemindMask = new Ext6.LoadMask(me, {msg: langs('Отправляется напоминание')});
		}
		me.RemindMask.show();
		Ext6.Ajax.request({
			url: '/?c=PersonDisp&m=RemindToMonitoring',
			params: {
				Persons: Ext6.util.JSON.encode(list),
				LpuSection_id: getGlobalOptions().CurLpuSection_id,
			},
			callback: function(options, success, response) {
				me.RemindMask.hide();
				me.ChartForm.msgGrid.load();
				if (success) {
					resp = Ext6.JSON.decode(response.responseText);
					if(resp.Error_Msg=='')
						Ext6.Msg.alert(langs('Сообщение'),langs('Напоминание отправлено'));
					else
						Ext6.Msg.alert(langs('Ошибка'),resp.Error_Msg);
				} else Ext6.Msg.alert(langs('Ошибка'),langs('Не удалось отправить напоминание'));
			}
		});
	},
	onSelectRow: function(model) {
		var me = this,
			isOne = model.selected.length == 1,
			record = {},
			enableToAddProgram = false;
		if(isOne) {
			record = model.selected.getAt(0);
			enableToAddProgram = me.isEnableToAdd(record);
		}
		
		me.toolbar.queryById('btnOpenEMK').setDisabled(!isOne);//кнопка "Открыть ЭМК"
		me.toolbar.queryById('btnWithoutRecord').setDisabled(!isOne);//кнопка "Без записи"

		me.toolbar.queryById('btnAddToProgram').setDisabled(!isOne || !enableToAddProgram);
		me.toolbar.queryById('btnRemoveFromProgram').setDisabled(!isOne || !(record.get('Status') >= 20 && record.get('Status')<30 ));

		//Доступность кнопки "Пригласить"
		var btnInviteEnable = false,
			btnSendMessage = false;//Доступность кнопок "Отправить напоминание" и "Отправить сообщение"
		// Человек прикреплен к МО пользователя
		// У человека отсутствует открытая карта наблюдений в МО Пользователя
		// метка человека открыта (без даты закрытия)
		model.selected.items.forEach(function(row) {
			if( 
				(Ext6.isEmpty(row.get('Chart_id')) || !Ext6.isEmpty(row.get('Chart_endDate')))
				&& Ext6.isEmpty(row.get('PersonLabel_disDate')) )
				btnInviteEnable = true;
			if( !Ext6.isEmpty(row.get('Chart_id')) && Ext6.isEmpty(row.get('Chart_endDate')) )
				btnSendMessage = true;
		});
		me.toolbar.queryById('btnInvite').setDisabled( !btnInviteEnable );
		me.toolbar.queryById('btnRemind').setDisabled( !btnSendMessage );
		me.toolbar.queryById('btnSendMessage').setDisabled( !isOne || !btnSendMessage || record.get('Label_id') != 1 );
		//Доступность кнопки "Изменить статус"
		me.toolbar.queryById('btnChangeStatus').setDisabled(
			!isOne || record.get('PersonLabel_disDate') || !record.get('LabelInviteStatus_id')
		);
		
		if (isOne) {
			me.openSelectedChart();
		}
	},
	show: function() {
		this.callParent(arguments);
		var me = this;
		me.dt = Date.now();//для таблицы
		me.dt.setDate(me.dt.getDate()-1);
		me.dt.setHours(0,0,0,0);
		//работа с входными параметрами
		if(arguments[0]) {
			me.action = arguments[0].action ? arguments[0].action : null;
			me.userMedStaffFact = arguments[0].userMedStaffFact ? arguments[0].userMedStaffFact : (sw.Promed.MedStaffFactByUser.last ? sw.Promed.MedStaffFactByUser.last : null);
			me.ARMType = arguments[0].ARMType ? arguments[0].ARMType : 'common';
			
			if(!Ext6.isEmpty(arguments[0].Label_id)) me.Label_id = arguments[0].Label_id;
			if(!Ext6.isEmpty(arguments[0].Person_id)) me.Person_id = arguments[0].Person_id;
		}
		
		//очистить фильтры и запомнить чистое состояние
		me.FilterPanel.getForm().reset();

		me.showed=true;
		
		if(me.queryById('tabs').getActiveTab().itemId=='tabOn') me.doSearch(true,false);
		else me.queryById('tabs').setActiveTab(1);

		d = Date.now();
		d.setDate(d.getDate() - 31);

		me.dateMenu.setDates([d,Date.now()]);
		me.dateMenu.getMenu().getRight().setMaxDate(Date.now());
		me.dateMenu.defaultDates = me.dateMenu.getDates();
		
		me.get('diags').getTrigger('search').hide();
	},
	showLabels: function(e, labels) {
		if(!Ext6.isEmpty(labels)) {
			labels = labels.split('|');
			if(!this.labeltip && labels.length>0) {
				this.labeltip = Ext6.create('Ext6.tip.ToolTip', {
					html: labels.join('<br>'),
					autoHide: true,
					closable: false
				});
			}
			if(this.labeltip) {
				this.labeltip.showBy(e);
			}
		}
	},
	initComponent: function() {
		var me = this;
		me.gridmodel = new Ext6.create('Ext6.data.Model',{
			//convertOnSet: true,
			fields: [
				{ name: 'Person_id', type: 'int' },
				{ name: 'Server_id', type: 'int' },
				{ name: 'PersonFio', type: 'string' },
				{ name: 'PersonAge', type: 'string' },
				{ name: 'PersonSex', type: 'int' },
				{ name: 'Chart_id', type: 'int' },
				{ name: 'PersonModel_id', type: 'int' },
				{ name: 'PersonModel_Name', type: 'string' },
				{ name: 'Label_id', type: 'int' },
				{ name: 'Chart_begDate', type: 'date', dateFormat: 'd.m.Y' },
				{ name: 'Chart_endDate', type: 'date', dateFormat: 'd.m.Y' },
				{ name: 'Chart_IsAutoClose', type: 'boolean'},
				{ name: 'Status', type: 'int' },
				{ name: 'StatusNick', type: 'string' },
				{ name: 'Person_IsRefuse', type: 'int' },
				{ name: 'Person_IsFedLgot', type: 'int' },
				{ name: 'Person_IsRegLgot', type: 'int' },
				{ name: 'DispOutType_Code', type: 'int' },

				{ name: 'Lpu_id', type: 'int' },
				{ name: 'Lpu_Nick', type: 'string' },
				{ name: 'AttachNum', type: 'int' },
				{ name: 'Attach', type: 'string' },
				{ name: 'AttachDate', type: 'date', dateFormat: 'd.m.Y' },

				{ name: 'Person_BirthDay', type: 'date', dateFormat: 'd.m.Y' },
				{ name: 'Person_DeadDT', type: 'date', dateFormat: 'd.m.Y' },
				{ name: 'ChartPersonDisp_id', type: 'int' },
				{ name: 'lastObserveDate', type: 'date', dateFormat: 'd.m.Y' },
				{ name: 'Rate1_Value', type: 'int' },
				{ name: 'Rate2_Value', type: 'int' },

				{ name: 'Chart_in_MO', type: 'int' },
			]
		});
		me.gridcolumns = [
		{	header: langs('Дата последнего измерения'), dataIndex: 'lastObserveDate', type: 'string', hidden:true },

		{	header: langs('Д/Р'), dataIndex: 'Person_BirthDay', type: 'string', hidden:true },
		{	header: langs('Статус'), dataIndex: 'StatusNick', type: 'string', hidden:true },

		{	header: langs('Идентификатор карты наблюдения'), dataIndex: 'Chart_id', type: 'int', hidden:true },

		{	header: langs('Идентификатор прикрепления'), dataIndex: 'Lpu_id', type: 'int', hidden:true },
		{	header: langs('Прикрепление'), dataIndex: 'Lpu_Nick', type: 'string', hidden:true },
		{	header: langs('Номер участка'), dataIndex: 'AttachNum', type: 'int', hidden:true },
		{	header: langs('Дата прикрепления'), dataIndex: 'AttachDate', type: 'date', formatter: 'date("d.m.Y")', hidden: true },

		{	header: langs('Код причины закрытия'), dataIndex:'DispOutType_Code', type:'int', hidden: true },
		{	header: langs('Идентификатор модели'), dataIndex: 'PersonModel_id', type: 'int', hidden: true },
		{	header: langs('Идентификатор пациента'), dataIndex: 'Person_id', type: 'int', hidden: true },
		{	header: langs('Идентификатор диспансерной карты'), dataIndex: 'ChartPersonDisp_id', type: 'int', hidden: true },
		{	header: langs('Пол'), dataIndex: 'PersonSex', type: 'int', hidden: true },
		{
			text: '', width: 300, minWidth: 300, maxWidth: 380, dataIndex: 'Person_SurName', tdCls: 'fio-column', flex: 1,
			renderer: function(value, metaData, record) {
				/*value = value.toUpperCase();
				s = me.getFio(value);
				val = Ext6.ComponentQuery.query('[itemId=fiofilter]')[0].getValue();
				if(val.length>0) {
				if(!Ext6.isEmpty(record.get('foundtext')) && record.get('foundtext').length>1) {
					pos = value.search(); //record.get('foundpos');
					s = s.slice(0,pos) + '<font color=red>' + s.slice(pos,pos+record.get('foundtext').length) + '</font>' + s.slice(pos+record.get('foundtext').length);
				}*/
				return me.getFio(record);
			},
			filter:
			{
				xtype: 'textfield',
				itemId: 'fiofilter',
				cls: 'remote-monitor-fiofilter',
				padding: '0 10 5 10',
				anchor: '-30',
				enableKeyEvents: true,
				refreshTrigger: function() {
					var isEmpty = Ext6.isEmpty(this.getValue());
					this.triggers.clear.setVisible(!isEmpty);
					this.triggers.search.setVisible(isEmpty);
				},
				delaySearchId: null,
				delaySearch: function(delay) {
					var _this = this;
					if (this.delaySearchId) {
						clearTimeout(this.delaySearchId);
					}
					me.delaySearchId = setTimeout(function() {
						
						if(me.paging) {
							me.params.fio = _this.value.toUpperCase();
							me.grid.getStore().getProxy().setExtraParams(me.params);
							me.grid.getStore().load();
						} else {
							me.grid.store.addFilter(function(rec) {
								var s = rec.get('Person_SurName')+' '+rec.get('Person_FirName')+' '+rec.get('Person_SecName');
								pos = s.toUpperCase().search(_this.value.toUpperCase());
								if(pos>=0) {
									//~ rec.set('foundpos', pos);
									//~ rec.set('foundtext', _this.value);
									return true;
								} else {
									//~ rec.set('foundpos', -1);
									//~ rec.set('foundtext', '');
									return false;
								}
							});
						}
						
						this.delaySearchId = null;
					}, delay);
					
					if(me.grid.store.filters.length) {
						me.grid.store.clearFilter();
					}
				},
				triggers: {
					search: {
						cls: 'x6-form-search-trigger',
						handler: function() {
							//?
						}
					},
					clear: {
						cls: 'x6-form-clear-trigger',
						hidden: true,
						handler: function() {
							this.setValue('');
							me.grid.store.clearFilter();
							this.refreshTrigger();
						}
					}
				},
				listeners: { 
					keyup: function(field, e) {
						this.refreshTrigger();
						this.delaySearch(300);
					}
				},
				emptyText: 'ФИО'
			}
		},
		{	header: langs('День рождения'), dataIndex: 'Person_BirthDay',hidden: true },
		{	header: langs(''), dataIndex: 'Person_DeadDT',hidden: true },
		{	header: langs('Возраст'), dataIndex: 'PersonAge', width: 80,
			renderer: function (value, metaData, record) {
				return getAgeString(record);
			}
		},
		{
			header: langs('Статус'), dataIndex:'Status', type:'int', width: 120,
			renderer: function (value, metaData, record) {
				var s = '';

				var labels = record.get('PersLabels');
				if(!Ext6.isEmpty(labels)) {
					s+='<b class="person-labels" id="person-'+record.get('Person_id')+'-labels" onClick="Ext6.getCmp(\''+me.id+'\').showLabels(this,\''+labels+'\');"></b>';
				}

				if(value>=10 && value<14) {
					var dt = Date.parse(record.get('LabelInviteStatus_Date'));
					if(dt) dt = dt.dateFormat('d.m.Y'); else dt = '';
					switch(Number(value)) {
						case 11: s='Приглашен '+dt;break;
						case 12: s='Приглашение принято '+dt;break;
						case 13: s='Приглашение отклонено '+dt;break;
						case 10: s='Новый';break;
					}
				} else if(value>=20 && value<=30) {
					var active = '';
					switch(Number(record.get('Label_id'))) {
						case 1:
							if(Ext6.isEmpty(record.get('Chart_endDate'))) active='-active';
							s+='<b class="dm-status-label dm-status-label-ad'+active+'" data-qtip="'+(active ? 'Состоит в регистре А/Д' : 'Исключен из регистра А/Д '+record.get('Chart_endDate')+' '+record.get('DispOutType_Name'))+'"></b>';
							break;
					}
					var sg='&nbsp;';
					if(value==30) {
						sg+='Исключен';
						if(!Ext6.isEmpty(record.get('Chart_endDate'))) sg+=' ' + record.get('Chart_endDate');
					} else {
						switch(Number(record.get('PersonModel_id'))) {
							case 1: sg+='гр.I'; break;
							case 2: sg+='гр.II'; break;
							case 3: sg+='гр.III'; break;
						}
					}
					if(sg!='') s+='<span class="dm-status-name">'+sg+'</span>';
				}
				return s;
			}
		},
		{
			header: langs('А/Д'), dataIndex:'Rate1_Value', type:'int', width: 100,
			renderer: function(val, metaData, record) {
				if(record.get('Label_id')!=1) return '';
				if(record.get('Status')>=20 && record.get('Status')<=30) {
					if(Date.parse(record.get('lastObserveDate')) < me.dt) {
						metaData.tdCls = "observe-chart-rate-nothing";
						return '<span class="AD-icon" data-qtip="пропущены замеры за предыдущий день"></span>';//нет измерения за последний день
					}
					else
					if(
						!Ext6.isEmpty(record.get('Rate1_Value')) && !Ext6.isEmpty(record.get('Rate2_Value'))
					) {
						var rate1val = parseInt(record.get('Rate1_Value')),
							rate2val = parseInt(record.get('Rate2_Value')),
							rate1max = parseInt(record.get('Rate1_Max')),
							rate2max = parseInt(record.get('Rate2_Max')),
							rate1min = parseInt(record.get('Rate1_Min')),
							rate2min = parseInt(record.get('Rate2_Min'));
							
						if(	rate1val > rate1max || rate2val > rate2max )
						{	//Показатели завышены
							if(	rate1val-rate1max>4 || rate2val-rate2max>4 )
							{	//Показатели значительно завышены
								metaData.tdCls = "observe-chart-rate-overtoo";
							} else
								metaData.tdCls = "observe-chart-rate-over";
						}
						else //т.к. приоритет у повышенных показателей
						if(	rate1val < rate1min || rate2val < rate2min )
						{	//Показатели занижены
							if(	rate1min - rate1val>4 || rate2min - rate2val>4 )
							{	//Показатели значительно занижены
								metaData.tdCls = "observe-chart-rate-overtoo-low";
							} else
								metaData.tdCls = "observe-chart-rate-over-low";
						}

						return record.get('Rate1_Value')+'/'+record.get('Rate2_Value');
					} else {
						metaData.tdCls = "observe-chart-rate-nothing";
						return '<span class="AD-icon" data-qtip="неполные данные в последнем измерении"></span>';//неполные данные в последнем измерении
						return '';
					}
				} else return '';
			}
		},
		{
			header: langs('Температура'), dataIndex:'Rate4_Value', type:'int', width: 100,
			renderer: function(val, metaData, record) {
				if(record.get('Label_id')!=7) return '';
				
				if(Date.parse(record.get('lastObserveDate')) < me.dt) {
					metaData.tdCls = "observe-chart-rate-nothing";
					return '<span class="AD-icon" data-qtip="пропущены замеры за предыдущий день"></span>';//нет измерения за последний день
				}
				else
				if(
					!Ext6.isEmpty(record.get('Rate4_Value'))
				) {
					var rate4val = parseFloat(record.get('Rate4_Value')),
						rate4max = parseFloat(record.get('Rate4_Max')),
						rate4min = parseFloat(record.get('Rate4_Min'));
					if(	rate4val > rate4max)
					{	//Показатели завышены
						if(	rate4val-rate4max>4 )
						{	//Показатели значительно завышены
							metaData.tdCls = "observe-chart-rate-overtoo";
						} else
							metaData.tdCls = "observe-chart-rate-over";
					}
					else //т.к. приоритет у повышенных показателей
					if(	rate4val < rate4min )
					{	//Показатели занижены
						if(	rate4min - rate4val>4 )
						{	//Показатели значительно занижены
							metaData.tdCls = "observe-chart-rate-overtoo-low";
						} else
							metaData.tdCls = "observe-chart-rate-over-low";
					}

					return record.get('Rate4_Value');
				} else {
					metaData.tdCls = "observe-chart-rate-nothing";
					return '<span class="AD-icon" data-qtip="неполные данные в последнем измерении"></span>';//неполные данные в последнем измерении
					return '';
				}
			}
		},
		{text: 'Льготы', dataIndex: 'Person_Privilege', width: 80,
			renderer: function(val, metaData, record) {
				var s = '';
				var addClass = "";
				var isRefuse = false;
				if (record.get('Person_IsRefuse') && record.get('Person_IsRefuse') == 1) {
					addClass += " lgot_refuse";//Отказ может быть только от Фед льготы
					isRefuse = true;
				}
				if (record.get('Person_IsFedLgot') && record.get('Person_IsFedLgot') == 1 ) {
					s += "<span class='lgot_fl" + addClass + "' data-qtip='" + (isRefuse ? "Пациент отказался от федеральной льготы" : "Федеральная льгота") + "'>ФЛ</span>";
				}
				if (record.get('Person_IsRegLgot') && record.get('Person_IsRegLgot') == 1 ) {//Региональная или есть, или нет.
					s += "<span class='lgot_rl" + "' data-qtip='" + "Региональная льгота" + "'>РЛ</span>";
				}
				return s;
			},
			sorter: function(a, b) {
				x = Number(a.get('Person_IsFedLgot'))*(100-99*Number(a.get('Person_IsRefuse'))) + Number(a.get('Person_IsRegLgot'))*10;
				y = Number(b.get('Person_IsFedLgot'))*(100-99*Number(b.get('Person_IsRefuse'))) + Number(b.get('Person_IsRegLgot'))*10;
				return x>y ? 1 : (x<y ? -1 : 0);
			}
		}, {
			text: 'Канал связи', dataIndex: 'FeedbackMethod_id', width: 140,
			renderer: function(val, metaData, record) {
				var s = '', name = record.get('FeedbackMethod_Name');
				switch(Number(record.get('FeedbackMethod_id'))) {
					case 1:
					case 2: 
						var regexp = /^(\+?7)?[\s\-]?\(?(\d{3})\)?[\s\-]?(\d{3})[\s\-]?(\d{2})[\s\-]?(\d{2})$/;
						if ( regexp.test(record.get('Chart_Phone')) ) {
							s = Ext6.isEmpty(record.get('Chart_Phone')) ? record.get('Person_Phone') : record.get('Chart_Phone');
							s = s.replace(regexp,'+7 $2 $3 $4 $5');
							s += ' '+record.get('FeedbackMethod_Name');
						}
						break;
					case 3: s = record.get('Chart_Email') ? record.get('Chart_Email')+' Email' : '';
						break;
					case 4: name = 'Рег. портал'; break;
					case 5: name = 'Моб. приложение'; break;
				}
				return s ? s : name;
			},
			sorter: function(a, b) {
				if(a.get('FeedbackMethod_id') > b.get('FeedbackMethod_id')) return 1;
				if(a.get('FeedbackMethod_id') < b.get('FeedbackMethod_id')) return -1;
				
				switch(Number(a.get('FeedbackMethod_id'))) {
					case 1:
					case 2: return a.get('Chart_Phone') > b.get('Chart_Phone') ? 1 : (a.get('Chart_Phone') < b.get('Chart_Phone') ? -1 : 0);
					case 3: return a.get('Chart_Email') > b.get('Chart_Email') ? 1 : (a.get('Chart_Email') < b.get('Chart_Email') ? -1 : 0);
					case 4: 
					case 5: return a.get('Person_Fio') > b.get('Person_Fio') ? 1 : (a.get('Person_Fio') < b.get('Person_Fio') ? -1 : 0);
				}
			}
		}, {
			text: 'Диагноз', dataIndex: 'Diag_Code', width: 80,
		}, {
			text: 'Прикрепление', dataIndex: 'Attach', minWidth: 300, flex:1,
			renderer: function(val, metaData, record) {
				var attach = '',
					lpu = record.get('Lpu_Nick'),
					num = record.get('AttachNum'),
					dt = record.get('AttachDate');
				if(!Ext6.isEmpty(lpu)) {
					attach+=lpu;
					if(!Ext6.isEmpty(num)) {
						if(lpu.slice(-1)!='.') attach+='.';
						attach+=' Уч. '+num;
						if(!Ext6.isEmpty(dt)) {
							attach+=' ('+dt+')';
						}
					}
				}
				return attach;
			}
		}
		];
		//--end datacolumns
		me.toolbar = Ext6.create('Ext6.toolbar.Toolbar', {
			cls: 'grid-toolbar',
			width: '100%',
			border: false,
			defaults: {
				margin: '0 4 0 0',
				padding: '4 10',
				disabled: true
			},
			items:[{
				xtype: 'button',
				margin: '0 0 0 6',
				text: langs('Открыть ЭМК'),
				itemId: 'btnOpenEMK',
				iconCls: 'action_openemk',
				handler: function() {
					me.openPersonEmkWindow();
				}
			}, {
				xtype: 'button',
				text: langs('Принять без записи'),
				itemId: 'btnWithoutRecord',
				iconCls: 'action_withoutrecord',
				hidden: true,
				handler: function() {

				}
			}, {
				xtype: 'button',
				text: langs('Пригласить'),
				itemId: 'btnInvite',
				iconCls: 'action_invite',
				//~ disabled: true,
				handler: function() {
					me.inviteSelectedPerson();
				}
			}, {
				xtype: 'button',
				text: langs('Изменить статус'),
				itemId: 'btnChangeStatus',
				//~ iconCls: 'action_changestatus',
				handler: function() {
					me.changeStatusSelectedPerson();
					/*var params = {
						LabelInvite_id: me.grid.getSelection()[0].get('LabelInvite_id'),
						LabelInviteStatus_id: me.grid.getSelection()[0].get('LabelInviteStatus_id'),
					};
					getWnd('swRemoteMonitoringStatusWindow').show(params);*/
				}
			}, {
				xtype: 'button',
				text: langs('Назначить прием'),
				hidden: true,
				itemId: 'btnAddDate',
				iconCls: 'action_addDate',
				handler: function() {

				}
			}, {
				xtype: 'button',
				text: langs('Добавить в программу'),
				iconCls: 'action_addinprogram',
				itemId: 'btnAddToProgram',
				handler: function() {
					me.addToProgram();
				}
			}, {
				xtype: 'button',
				text: langs('Отправить напоминание'),
				itemId: 'btnRemind',
				iconCls: 'action_sendmessage',
				handler: function() {
					me.remindSelectedPerson();
				}
			}, {
				xtype: 'button',
				text: langs('Отправить сообщение'),
				itemId: 'btnSendMessage',
				iconCls: 'action_sendmessage',
				handler: function() {
					me.sendMessage();
				}
			}, {
				xtype: 'button',
				text: langs('Исключить из программы'),
				iconCls: 'action_deleteinprogram',
				itemId: 'btnRemoveFromProgram',
				handler: function() {
					me.delFromProgram();
				}
			}, {
				xtype: 'button',
				itemId: 'btnPrint',
				hidden: false,
				text: langs('Печать'),
				iconCls: 'action_print',
				handler: function() {

				},
				menu: [{
					text:'Menu Item 1'
				},{
					text:'Menu Item 2'
				},{
					text:'Menu Item 3'
				}]
			}]
		});

		me.gridStore = new Ext6.data.Store({
			autoLoad: false,
			model: me.gridmodel,
			pageSize: 100, //TAG: постраничный вывод
			proxy: {
				type: 'ajax',
				actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
				url: '/?c=PersonDisp&m=loadPersonLabelList',
				reader: {
					type: 'json',
					rootProperty: 'data',
					totalProperty: 'totalCount'
				}
			},
			sorters: [{
					property: 'Status',
					direction: 'ASC'
				}, {
					property: 'Person_SurName',
					direction: 'ASC'
				}, {
					property: 'Person_FirName',
					direction: 'ASC'
				}, {
					property: 'Person_SecName',
					direction: 'ASC'
				}
			],
			listeners: {
				load: function(data) {
					if(me.grid.getStore().getCount()>0) //TAG: загрузка первой записи
						me.grid.setSelection(me.grid.getStore().getAt(0));
					else {
						me.ChartForm.hide();
						// Если выбранный список пустой, отключаем доступность кнопок. Иначе доступность определяется в onSelectRow.
						me.toolbar.queryById('btnOpenEMK').setDisabled( true );
						// me.toolbar.queryById('btnWithoutRecord').setDisabled( true );
						me.toolbar.queryById('btnInvite').setDisabled( true );
						me.toolbar.queryById('btnChangeStatus').setDisabled( true );
						// me.toolbar.queryById('btnAddDate').setDisabled( true );
						me.toolbar.queryById('btnAddToProgram').setDisabled( true );
						me.toolbar.queryById('btnRemind').setDisabled( true );
						me.toolbar.queryById('btnSendMessage').setDisabled( true );
						me.toolbar.queryById('btnRemoveFromProgram').setDisabled( true );
						me.toolbar.queryById('btnPrint').setDisabled( true );
					}
					me.loadPersonLabelCounts(); //количество записей во вкладках
					me.queryById('tabs').enable();
					if(me.Person_id) {
						var i = me.grid.getStore().findBy(function(rec){return rec.get('Person_id')==me.Person_id});
						if(i>=0) {
							me.grid.setSelection(me.grid.getStore().getAt(i));
						}
						me.Person_id=null;
					}
				}
			}
		});

		me.grid = new Ext6.grid.Panel({
			xtype: 'grid',
			cls: 'remote-monitor-grid',
			flex: 100,
			region: 'center',
			border: true,
			requires: [
				'Ext6.ux.GridHeaderFilters'
			],
			plugins: [
				Ext6.create('Ext6.grid.filters.Filters', {
					showMenu: false
				}),
				Ext6.create('Ext6.ux.GridHeaderFilters', {
					enableTooltip: false,
					reloadOnChange: false
				})
			],
			dockedItems: me.paging ? [{ //TAG: постраничный вывод
				displayInfo: true,
				dock: 'bottom',
				store: me.GridStore,
				xtype: 'pagingtoolbar'
			}] : null,
			store: me.gridStore,
			columns: me.gridcolumns,
			
			selModel: {
				type: 'checkboxmodel',
				headerWidth: 40,
				listeners: {
					select: function(model, record, index) {
						if (model.selected.length == model.store.data.length){
							model.column.getEl().removeCls('selected-item');

						}else {
							model.column.getEl().addCls('selected-item');
						}
						
						me.onSelectRow(model);
					},
					deselect: function (model, record, index) {
						if (model.selected.length != 0 && model.selected.length < model.store.data.length){
							model.column.getEl().addCls('selected-item');
						}
						if (model.selected.length == 0){
							model.column.getEl().removeCls('selected-item');
							me.toolbar.query('button').forEach(function (el, index, arr) {
								el.disable()
							});
						}
						me.onSelectRow(model);
					}
				}
			},
		});

		me.dateMenu = Ext6.create('Ext6.date.RangeField', {
			hideLabel: true,
			autoWidth: true,
			itemId: 'datefilter',
			padding: '0 20 10 0',
			margin: 0,
			width: 500,
			listeners: {
				change: function() {
					me.checkFilterStatus();
				}
			}
		});

		me.dateMenu.getTrigger('dateMenu').handler = function() {
			if (this.disabled) { return; }

			this.getMenu(true).showBy(this, "tl-bl?");
			this.getMenu()._handlePickerSetValue();
			this.getMenu().getRight().setMaxDate(Date.now());//в функции строки выше - скопированы из метода в самом Ext6.date.RangeField
		};

		me.diagstore = Ext6.create('swTagDiagStore', {
			loadParams : {
				object: 'Diag',
				where: 'where Diag_id in (5378,5379,5380,5381,5382,5383,5384,5385,5386,5387,5388,5389,5390,11742)'
			}
		});
		me.diagstore.load({params: {where: 'where Diag_id in (5378,5379,5380,5381,5382,5383,5384,5385,5386,5387,5388,5389,5390,11742)' }});

		me.FilterPanel = new Ext6.form.FormPanel({
			region: 'north',
			layout: 'column',
			cls: 'searchDiags',
			border: false,
			hidden: true,
			bodyPadding: '10 10 0 20',
			bodyStyle: 'background-color: #fff',
			defaults: {
				padding: '0 20 10 0'
			},
			items: [
				{
					fieldLabel: 'Диагнозы',
					idDefaultListFilter: '5378,5379,5380,5381,5382,5383,5384,5385,5386,5387,5388,5389,5390,11742',//TODO:стянуть из бд или заставить mongo_getwhere обрабатывать " Diag_id in select (...) "
					idDefaultArrayFilter: [5378,5379,5380,5381,5382,5383,5384,5385,5386,5387,5388,5389,5390,11742],//TODO:стянуть из бд или заставить mongo_getwhere обрабатывать " Diag_id in select (...) "
					name: 'diags',
					itemId: 'diags',
					width: 425+75, //335,
					labelWidth: 75,
					xtype: 'swDiagTagCombo',
					//loadParams: {params: {where: ' where Diag_id in (select LD.Diag_id from LabelDiag LD where LD.Label_id=1)', object: 'Diag',}},
					cls: 'diagnoz-tag-input-field',
					highlightSearchResults: false,
					listConfig:{
						cls: 'choose-bound-list-menu update-scroller diagnoz-tag-input-field-list',

						loadingText: 'Загружаем диагнозы',
						emptyText: '<span style="color:red;font: normal 12px/17px Roboto, tahoma, arial, verdana, sans-serif;">Диагнозов не найдено</span>',
						getInnerTpl: function() {
							return '{[values.Diag_Display]}\u00a0';
						}
					},
					listeners: {
						change: function(field, newVal, oldVal) {
							if(newVal.indexOf(-1)>-1) {
								newVal.remove(-1);
								
								field.suspendEvents();
								//делаем такой финт чтобы store смог правильно добавить тэги
								//(т.к. записей из idDefaultListFilter скорее всего нет в store)
								me.diagstore.data.items.forEach(function(rec) {
									field.getStore().add(rec);
								});
								field.setValue(newVal.concat(this.idDefaultArrayFilter));
								field.resumeEvents();
								
								newVal = newVal.concat(this.idDefaultArrayFilter);
								field.inputEl.dom.value='';//убрать набранный текст т.к. выбор сделан
							}
							me.checkFilterStatus();
						}
					}
				},
				{
					xtype: 'baseCombobox',
					fieldLabel: 'Метка',
					itemId: 'labelFilter',
					allowBlank: false,
					valueField: 'id',
					displayField: 'name',
					value: 1,
					labelWidth: 50,
					width: 300,
					store: new Ext6.data.SimpleStore({
						autoLoad: false,
						fields: [
							{ name: 'id', type: 'int' },
							{ name: 'name', type: 'string' }
						],
						data: [
							[1, langs('Заболевание АГ')], 
							[7, langs('Наблюдения температуры')]
						]
					})
				},
				{
					xtype: 'label',
					itemId: 'labelDateFilter',
					padding: '0',
					margin: '5 5 0 0',
					text: 'Период исключения: '
				},
				me.dateMenu,
				{
					xtype: 'swDispOutTypeCombo',
					itemId: 'DispOutType_id',
					labelWidth: 140,
					width: 350,
					listeners: {
						change: function() {
							me.checkFilterStatus();
						}
					}
				},
				{
					xtype: 'swLabelInviteStatusCombo',
					itemId: 'LabelInviteStatus_id',
					fieldLabel: 'Статус приглашения',
					labelWidth: 150,
					width: 343,
					listeners: {
						change: function() {
							me.checkFilterStatus();
						}
					}
				},
				{
					xtype: 'container',
					padding: '0 10 10 0',
					layout: {
						type: 'hbox',
						align: 'left'
					},
					items: [{
						margin: '0 5 0 0',
						text: langs('Найти'),
						cls: 'button-primary',
						width: 100,
						xtype: 'button',
						handler: function () {
							me.doSearch(false,true);
						}
					}, {
						text: langs('Очистить'),
						cls: 'button-secondary',
						width: 100,
						xtype: 'button',
						handler: function () {
							me.doSearch(true,false);
						}
					}]
				}

			]
		});

		me.ChartForm = Ext6.create('common.PolkaWP.RemoteMonitoring.ObserveChartForm', {
			region: 'north',
			border: false,
			ownerWin: me
		});

		me.ChartPanel = new Ext6.Panel({
			title: 'Настройки и измерения пациента',
			minWidth: 473,
			cls: 'person-chart-panel',
			border: false,
			scrollable: true,
			header: false,
			region: 'east',
			layout: 'border',
			collapsible: true,
			split: true,
			flex: 70,
			floatable: false,
			items: [
				me.ChartForm
			]
		});

		me.MainPanel = new Ext6.Panel({
			layout: 'border',
			region: 'center',
			border: false,
			dockedItems: [ me.toolbar ],
			collapseDirection: 'right',
			items: [
				me.grid,
				me.ChartPanel
			]
		});
		
		me.MainMainPanel = new Ext6.Panel({
			layout: 'border',
			region: 'center',
			border: false,
			items: [
				{
					region: 'north',
					itemId: 'tabs',
					xtype: 'tabpanel',
					cls: 'panel-cust',
					border: false,
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
							text: 'Фильтры',
							itemId: 'filterButton',
							menu: [],
							iconCls: 'icon-filters',
							userCls: 'button-without-frame button-filters',
							style: 'text-transform: none !important;',
							enableToggle: true,
							toggleHandler: function (button, pressed, eOpts) {
								if (me.FilterPanel.hidden == true){
									me.FilterPanel.show();
									this.addCls('filter-panel-expanded');
								}else {
									me.FilterPanel.hide();
									this.removeCls('filter-panel-expanded');
								}
							}
						}
						]
					},
					items: [
						{
							title: 'Новые',
							itemId: 'tabNew',
							listeners: {
								activate: function (tab_id, flag) {
									if(me.showed) me.setTabStatus(0);
								}
							}
						}, {
							title: 'Включенные',
							itemId: 'tabOn',
							listeners: {
								activate: function (tab_id, flag) {
									me.setTabStatus(1);
								}
							}
						}, {
							title: 'Выбывшие',
							itemId: 'tabOff',
							listeners: {
								activate: function (tab_id, flag) {
									me.setTabStatus(2);
								}
							}
						}, {
							title: 'Все пациенты',
							itemId: 'tabAll',
							listeners: {
								activate: function (tab_id, flag) {
									me.setTabStatus(3);
								}
							}
						}
					]
				},
				me.FilterPanel,
				me.MainPanel	
			]
		});

		Ext6.apply(me, {
			border: false,
			items: [
				me.MainMainPanel
			]
		});

		this.callParent(arguments);

		this.get('diags').getStore().addListener('load', function() {
			rec = new Ext6.data.Record();
			rec.set('Diag_id', -1);
			rec.set('Diag_Code', ' Все диагнозы с повышенным АД');
			rec.set('Diag_Name', 'Все диагнозы с повышенным АД');
			rec.set('Diag_Display', 'Все диагнозы с повышенным АД');
			this.add(rec);
		});
	}
});
