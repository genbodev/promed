/**
* АРМ администратора СМП
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @autor		Dyomin Dmitry
* @copyright    Copyright (c) 2012 Swan Ltd.
* @version      12.2012
*/

sw.Promed.swWorkPlaceSMPAdminWindow = Ext.extend(sw.Promed.swWorkPlaceSMPDefaultWindow, {
	useUecReader: true,
	id: 'swWorkPlaceSMPAdminWindow',

	buttons: [{
			hidden: false,
			handler: function()
			{
				this.ownerCt.getReplicationInfo()
			},
			iconCls: 'ok16',
			text: langs('Актуальность данных: (неизвестно)')
		},
		'-',
		{
			text: BTN_FRMHELP,
			iconCls: 'help16',
			handler: function(button, event) {
				ShowHelp(this.ownerCt.title);
			}
		}, {
			text      : 'Закрыть',
			tabIndex  : -1,
			tooltip   : 'Закрыть',
			iconCls   : 'cancel16',
			handler   : function() {
				this.ownerCt.hide();
			}
		}
	],
	
	listeners: {
		hide: function() {
			this.stopTask();
			if(this.interval2) {
				clearInterval(this.interval2);
				delete this.interval2;
			}
		}
	},

	getReplicationInfo: function () {
		var win = this;
		if (win.buttons[0].isVisible()) {
			win.getLoadMask().show();
			getReplicationInfo('default', function(text) {
				win.getLoadMask().hide();
				win.buttons[0].setText(text);
			});
		}
	},

	buttonPanelActions: {
		
		action_messages: {
			iconCls: 'messages48',
			tooltip: 'Система сообщений',
			handler: function(){
				getWnd('swMessagesViewWindow').show();
			}
		},
		
		action_smpStacDiffDiagJournal: {
			iconCls: 'pers-cards32',
			tooltip: 'Журнал госпитализаций из СМП',
			handler: function(){
//				var formParams = new Object();
//				var params = new Object();
//				formParams.ARMType = this.ARMType;
//				params.formParams = formParams;				
				//alert(this.ARMType.toString());
				getWnd('swSmpStacDiffDiagJournal').show({
					ARMType: 'smpadmin'					
				});
			}
		},
		action_Register: {
			nn: 'action_Register',
			tooltip: langs('Регистры'),
			text: langs('Регистры'),
			iconCls: 'registry32',
			disabled: false,
			menuAlign: 'tr?',
			menu: new Ext.menu.Menu({
				items: [{
					tooltip: langs('Регистр часто обращающихся'),
					text: langs('Регистр часто обращающихся'),
					//iconCls: 'report16',
					disabled: false,
					//hidden: (getRegionNick() == 'perm'),
					handler: function () {
						if (getWnd('swOftenCallersRegisterWindow').isVisible()) {
							sw.swMsg.show({
								buttons: Ext.Msg.OK,
								fn: Ext.emptyFn,
								icon: Ext.Msg.WARNING,
								msg: langs('Окно уже открыто'),
								title: ERR_WND_TIT
							});
							return false;
						}
						getWnd('swOftenCallersRegisterWindow').show();
					}
				}, {
					tooltip: langs('Регистр случаев противоправных действий в отношении персонала СМП'),
					text: langs('Регистр случаев противоправных действий в отношении персонала СМП'),
					//iconCls: 'report32',
					disabled: false,
					handler: function () {
						if (getWnd('swSmpIllegalActWindow').isVisible()) {
							sw.swMsg.show({
								buttons: Ext.Msg.OK,
								fn: Ext.emptyFn,
								icon: Ext.Msg.WARNING,
								msg: langs('Окно уже открыто'),
								title: ERR_WND_TIT
							});
							return false;
						}
						getWnd('swSmpIllegalActWindow').show();
					}.createDelegate(this)
				}
				]
			})
		},
		/*

		action_oftenCallerRegister: {
			iconCls: 'report32',
			tooltip: 'Регистр часто обращающихся',
			handler: function(){
				getWnd('swOftenCallersRegisterWindow').show();
			}
		},
		

		// Функционал администратора МО. Доступен если Администратору СМП дать соответствующую группу.
		action_LpuPassport: {
			nn: 'action_LpuPassport',
			text: 'Паспорт МО',
			tooltip: 'Паспорт МО',
			iconCls: 'lpu-passport32',
			hidden: true, // (getRegionNick() == 'perm' || (!isAdmin && !isLpuAdmin())),
			handler: function(){
				getWnd('swLpuPassportEditWindow').show({
					action: 'edit',
					Lpu_id: getGlobalOptions().lpu_id
				});
			}
		},
		
		action_LpuStructureView: {
			nn: 'action_LpuStructureView',
			text: MM_LPUSTRUC,
			tooltip: 'Структура МО',
			iconCls : 'structure32',
			hidden: true, // (getRegionNick() == 'perm' || !isAdmin && !isLpuAdmin() && !isCadrUserView()),
			handler: function(){
				getWnd('swLpuStructureViewForm').show();
			}
		},
		
		action_OrgView: {
			nn: 'action_OrgView',
			tooltip: 'Все организации',
			text: 'Организации',
			iconCls : 'org32',
			hidden: !isLpuAdmin(),
			handler: function(){
				getWnd('swOrgViewForm').show();
			}
		},
		
		action_Documents: {
			nn: 'action_Documents',
			tooltip: 'Инструментарий',
			text: 'Инструментарий',
			iconCls : 'document32',
			hidden: !isLpuAdmin(),
			menuAlign: 'tr',
			menu: new Ext.menu.Menu({
				items: [{
					tooltip: 'Глоссарий',
					text: 'Глоссарий',
					iconCls : 'glossary16',
					handler: function(){
						getWnd('swGlossarySearchWindow').show();
					}
				},{
					tooltip: 'Шаблоны документов',
					text: 'Шаблоны документов',
					iconCls : 'test16',
					handler: function(){
						getWnd('swTemplSearchWindow').show();
					}
				},{
					tooltip: 'Список маркеров',
					text: 'Список маркеров',
					iconCls : 'test16',
					handler: function(){
						getWnd('swMarkerSearchWindow').show();
					}
				}]
			})
		},		
		

		action_UserProfile: {
			nn: 'action_UserProfile',
			text: 'Мой профиль',
			tooltip: 'Профиль пользователя',
			iconCls : 'user32',
			hidden: !isLpuAdmin(),
			handler: function(){
				getWnd('swUserProfileEditWindow').show({ action: 'edit' });
			}
		},
		
		action_OptionsView: {
			nn: 'action_OptionsView',
			text: 'Настройки',
			tooltip: 'Просмотр и редактирование настроек',
			iconCls : 'settings32',
			hidden: !isLpuAdmin(),
			handler: function(){
				getWnd('swOptionsWindow').show();
			}
		},
		
		action_IAS: {
			iconCls: 'monitoring32',
			tooltip: 'Информационно-аналитическая система',
			//#28794
			hidden: true,
			handler: function(){ return; }
		},
		/*
		action_OLAP: {
			iconCls: 'monitoring32',
			tooltip: 'Модуль отчетности OLAP',
			handler: function(){
				var opts = getGlobalOptions();
				// Псков
				if ( opts.region.number == 60 ) {
					window.open('http://service.mis.pskov.ru/olap_pskov/default.aspx','_blank');
				}
				return false;
			}
		},
		*/
		action_EmergencyTeamProposalLogic: {
			tooltip: 'Логика предложения бригады на вызов',
			iconCls : 'mp-queue32',
		    handler: function(){
				getWnd('swSmpEmergencyTeamProposalLogicWindow').show();
		    }
		},
		
		action_DecisionTreeEditWindow: {
			tooltip: 'Дерево принятия решений',
			iconCls : 'structure-vert32',
			handler: function(){
				swExt4.app.getController('smp.controllers.DecisionTree').showStucturesWindow();
			//	swExt4.app.getController('smp.controllers.DecisionTree').showEditWindow();
			}
		},

		action_Spr: {
			hidden: getRegionNick() == 'buryatiya',
			nn: 'action_Spr',
			tooltip: 'Справочники',
			text: 'Справочники',
			iconCls : 'book32',
			disabled: false,
			menuAlign: 'tr',
			menu: new Ext.menu.Menu({
				items: [
					sw.Promed.Actions.swMESOldAction,
					'-',
					sw.Promed.Actions.SprRlsAction,
					sw.Promed.Actions.swDrugDocumentSprAction
				]
			})
		},

		action_ReportEngine: {
			nn: 'action_ReportEngine',
			tooltip: 'Просмотр отчетов',
			text: 'Просмотр отчетов',
			iconCls : 'report32',
			hidden: false,//!isLpuAdmin(),
			handler: function() {
				if (sw.codeInfo.loadEngineReports)
				{
					getWnd('swReportEndUserWindow').show();
				}
				else
				{
					getWnd('reports').load(
						{
							callback: function(success)
							{
								sw.codeInfo.loadEngineReports = success;
								// здесь можно проверять только успешную загрузку
								getWnd('swReportEndUserWindow').show();
							}
						});
				}
			}
		},

		action_PatientDiffJournal: {
			hidden: getRegionNick() == 'buryatiya',
			nn: 'action_PatientDiffJournal',
			tooltip: 'Журнал расхождения пациентов в учетных документах',
			text: 'Журнал расхождения пациентов в учетных документах',
			iconCls : 'report32',
			handler: function(){
				getWnd('swPatientDiffJournalWindow').show();
			}
		},
		action_System_Monitoring: {
			hidden: !getGlobalOptions().IsSMPServer ,
			nn: 'action_System_Monitoring',
			tooltip: langs('Мониторинг системы'),
			text: langs('Мониторинг системы'),
			iconCls : 'settings32',
			handler: function(){
				getWnd('swSystemMonitorWindow').show();
			}
		},

		action_CardCallFind: {
			hidden: getRegionNick() !== 'buryatiya',
			text: langs('Карты СМП: Поиск'),
			tooltip: langs('Карты вызова СМП: Поиск'),
			iconCls: 'ambulance_search16',
			handler: function()
			{
				getWnd('swCmpCallCardSearchWindow').show();
			},
		},
	},

	deleteCmpCallCard: function() {
		var grid = this.GridPanel;
		var parentObject = this;
		if ( !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('CmpCallCard_id') ) {
			return false;
		}
		
		var record = grid.getSelectionModel().getSelected();

		var EmergencyTeamNum = record.get('EmergencyTeam_Num'); // номер назначенной бригады
		var CmpCloseCardID = record.get('CmpCloseCard_id'); // закрытая карта
		var Lpu_ppdid = record.get('Lpu_ppdid'); // МО передачи (НМП)

		if (CmpCloseCardID) {
			sw.swMsg.alert('Ошибка', 'На вызов создана Карта вызова. Удаление невозможно.');
			return;
		}

		if (EmergencyTeamNum) {
			sw.swMsg.alert('Ошибка', 'На вызов назначена бригада. Удаление невозможно.');
			return;
		}

		if (Lpu_ppdid) {
			sw.swMsg.alert('Ошибка', 'Вызов передан в службу НМП. Удаление невозможно');
			return;
		}

		//this.emitEditingEvent(record.get('CmpCallCard_id'),function(){
			sw.swMsg.show({
				buttons: Ext.Msg.YESNO,
				fn: function(buttonId, text, obj) {
					if ( buttonId == 'yes' ) {
						Ext.Ajax.request({
							callback: function(options, success, response) {
								if ( success ) {
									var response_obj = Ext.util.JSON.decode(response.responseText);

									if ( response_obj.success == false ) {
										sw.swMsg.alert('Ошибка', response_obj.Error_Msg ? response_obj.Error_Msg : 'Ошибка при удалении карты вызова');
									}
									else {
										grid.getStore().remove(record);
										parentObject.emitDeletingEvent(record.get('CmpCallCard_id'));
										parentObject.addEmptyRecord();
										grid.getStore().reload();
									}

									if ( grid.getStore().getCount() > 0 ) {
										grid.getView().focusRow(0);
										grid.getSelectionModel().selectFirstRow();
									}
								}
								else {
									sw.swMsg.alert('Ошибка', 'При удалении карты вызова возникли ошибки');
									parentObject.emitEndEditingEvent(record.get('CmpCallCard_id'));
								}
							},
							params: {
								CmpCallCard_id: record.get('CmpCallCard_id')
							},
							url: '/?c=CmpCallCard&m=deleteCmpCallCard'
						});
					} else {
						parentObject.emitEndEditingEvent(record.get('CmpCallCard_id'));
					}
				},
				icon: Ext.MessageBox.QUESTION,
				msg: 'Удалить карту вызова?',
				title: 'Вопрос'
			});
		//});
	},
	
	getLoadMask: function() {
		if ( !this.loadMask ) {
			this.loadMask = new Ext.LoadMask(this.getEl(), {msg: 'Подождите... '});
		}

		return this.loadMask;
	},
	
	openCmpCallCardEditWindow: function(action) {
		if ( !action || !action.toString().inlist([ 'add', 'edit', 'view']) ) {
			return false;
		}
		
		var wnd = 'swCmpCallCardNewShortEditWindow';

		if ( getWnd(wnd).isVisible() ) {
			sw.swMsg.alert('Сообщение', 'Окно редактирования карты вызова уже открыто');
			return false;
		}

		var formParams = new Object();
		var grid = this.GridPanel;//.getGrid();
		var params = new Object();
		var parentObject = this;
		params.action = action;
		params.callback = function(data) {
			if ( !data || !data.cmpCallCardData ) {
				return false;
			}			
			grid.getStore().reload();
			// parentObject.emitAddingEvent(data.cmpCallCardData['CmpCallCard_id']);
			parentObject.emitEditingEvent(data.cmpCallCardData['CmpCallCard_id']);
			this.autoEvent = false;
		}.createDelegate(this);

		if ( action == 'add' ) {
			formParams.CmpCallCard_id = 0;

			params.onHide = function() {
				//grid.getView().focusRow(0);
			};
			formParams.ARMType = this.ARMType;
			if (selected_record) {
				formParams.CmpCloseCard_Id = selected_record.get('CmpCloseCard_id');
			}
			params.formParams = formParams;
			getWnd(wnd).show(params);
		}
		else {

			if ( !grid.getSelectionModel().getSelected() ) {
				return false;
			}

			var selected_record = grid.getSelectionModel().getSelected();

			if ( !selected_record.get('CmpCallCard_id') ) {
				return false;
			}

			formParams.CmpCallCard_id = selected_record.get('CmpCallCard_id');
			
			params.onHide = function() {
				grid.getView().focusRow(grid.getStore().indexOf(selected_record));
				if (action == 'edit') {
					parentObject.emitEndEditingEvent(selected_record.get('CmpCallCard_id'));
				}
			};
			if (selected_record) {
				formParams.CmpCloseCard_Id = selected_record.get('CmpCloseCard_id');
			}
			formParams.ARMType = this.ARMType;
			params.formParams = formParams;
			if (action == 'edit') {
				//this.emitEditingEvent(selected_record.get('CmpCallCard_id'),function(){
					getWnd(wnd).show(params);
				//});
			} else {
				getWnd(wnd).show(params);
			}	
		}
	},
	show: function() {
		
		this.ARMType = null;
		
		if ( !arguments[0] || !arguments[0].ARMType ) {
			sw.swMsg.alert('Сообщение', 'Неверные параметры', function() {this.hide();}.createDelegate(this) );
			return false;
		}
		
		//подключение nodeJS
		connectNode(this);

		this.ARMType = arguments[0].ARMType;
		
		this.userMedStaffFact = arguments[0].userMedStaffFact || null;

		if ( !this.ARMType.toString().inlist([ 'smpadmin' ]) ) {
			sw.swMsg.alert('Сообщение', 'Неверный тип АРМ', function() {this.hide();}.createDelegate(this) );
			return false;
		}
		
		loadComboOnce(this.FilterPanel.getForm().findField('CmpLpu_id'), 'Куда доставлен');

		this.GridPanel.getAction('action_add').show();
		
		with(this.LeftPanel.actions) {
			action_RLS.setHidden(true);
			action_Mes.setHidden(true);
			action_Report.setHidden(true);
			
			//log( typeof action_Documents.menu );
			
			/*
			new Ext.Button({
				nn: 'action_Documents',
				tooltip: 'Инструментарий',
				text: 'Инструментарий',
				iconCls : 'document32',
				hidden: !isLpuAdmin(),
				menuAlign: 'tr',
				menu: new Ext.menu.Menu({
					items: [
						sw.Promed.Actions.GlossaryAction,
						sw.Promed.Actions.TemplatesAction,
						sw.Promed.Actions.MarkerAction
					]
				})
			});
			*/
		}
		// this.startTask();
		this.startTimer();
		
		sw.Promed.swWorkPlaceSMPAdminWindow.superclass.show.apply(this, arguments);

        this.getReplicationInfo();
	},
	asyncLockCmpCallCard: function(data) {
		//redactStr дублируется в asyncUnlockCmpCallCard
		var redactStr = '<img src="../img/grid/lock.png">';
		if (!data || !data['CmpCallCard_id']) {
			return false;
		}
		var idx = this.GridPanel.getStore().findBy(function(rec) { return rec.get('CmpCallCard_id') == data['CmpCallCard_id']; });
		if (idx == -1) {
			return false
		}
		var record = this.GridPanel.getStore().getAt(idx)
		if(!record) return false;
		if (record.get('Person_FIO').indexOf(redactStr)== -1){
			record.set('Person_FIO', (redactStr+record.get('Person_FIO')) );
			record.set('CmpCallCard_isLocked', 1);
		}
		record.commit();
		var sm = this.GridPanel.getSelectionModel();
		if(sm.getSelected()&&sm.getSelected().get('CmpCallCard_id')==data['CmpCallCard_id'] ) {
			var ind = this.GridPanel.getStore().indexOf(record);
			this.GridPanel.getSelectionModel().fireEvent('rowselect',sm,ind,record);
		}
		
	},
	asyncUnlockCmpCallCard: function(data) {
		//redactStr дублируется в asyncLockCmpCallCard
		var redactStr = '<img src="../img/grid/lock.png">';
		if (!data || !data['CmpCallCard_id']) {
			return false;
		}
		var idx = this.GridPanel.getStore().findBy(function(rec) { return rec.get('CmpCallCard_id') == data['CmpCallCard_id']; });
		if (idx == -1) {
			return false;
		}
		var record = this.GridPanel.getStore().getAt(idx)
		if(!record) return false;
		for (k in data) {
			if (data.hasOwnProperty(k)) {
				if (typeof record.get(k) != 'undefined') {
					record.set(k,data[k]);
				}
			}
		}

		record.set('CmpGroup_id',data['Admin_CmpGroup_id']);//ADMIN
		record.set('CmpGroupName_id',data['Admin_CmpGroupName_id']);//ADMIN
		
		record.commit();
		with(this.GridPanel.getStore()) {
			var ss = getSortState();
			sort(ss.field, ss.direction);
		}
		this.addEmptyRecord();
		record.set('Person_FIO', (record.get('Person_FIO').substring(redactStr.length)));
		record.set('CmpCallCard_isLocked', 0);
		record.commit();
		var sm = this.GridPanel.getSelectionModel();
		if(sm.getSelected()&&sm.getSelected().get('CmpCallCard_id')==data['CmpCallCard_id'] ) {
			var ind = this.GridPanel.getStore().indexOf(record);
			this.GridPanel.getSelectionModel().fireEvent('rowselect',sm,ind,record);
		}
		
		
	},
	asyncAddCmpCallCard:function(data){
		data['CmpGroup_id'] = data['Admin_CmpGroup_id'];
		data['CmpGroupName_id'] = data['Admin_CmpGroupName_id'];
		if (data['CmpGroup_id']==null) {
			return false;
		}
		var idx = this.GridPanel.getStore().find('CmpCallCard_id',data['CmpCallCard_id']);
		if (idx != -1) {
			return false
		}
		
		date = {};
		date['begDate'] = this.dateMenu.getValue1();
		date['endDate'] = this.dateMenu.getValue2();
		date['endDate'].setDate(date['endDate'].getDate() + 1);
		date['prmDate'] = new Date(data['CmpCallCard_prmDate']);
		
		
		if (!((date['begDate']<=date['prmDate'])&&(date['endDate']>date['prmDate']))) {
			return false;
		}
		
		var redactStr = '<img src="../img/grid/lock.png">';
		data['Person_FIO'] = data['Person_FIO'].substring(redactStr.length);
		data['CmpCallCard_isLocked'] = 0;
		var record = new Ext.data.Record(data);
		this.GridPanel.getStore().add(record);
		this.GridPanel.getStore().commitChanges();
		this.removeEmptyRecord();
		this.addEmptyRecord();
		with(this.GridPanel.getStore()) {
			var ss = getSortState();
			sort(ss.field, ss.direction);
		}
	},
	asyncDeleteCmpCallCard: function(data) {
		if (!data || !data['CmpCallCard_id']) {
			return false;
		}
		var idx = this.GridPanel.getStore().find('CmpCallCard_id',data['CmpCallCard_id']);
		if (idx == -1) {
			return false
		}
		var record = this.GridPanel.getStore().getAt(idx);
		this.GridPanel.getStore().remove(record);
		this.addEmptyRecord();
	},
	startTimer: function() {	
		var topTitle = this.GridPanel;
		setInterval(function(){			
			date = new Date(), 
			d = date.getDate(),
			mo = date.getMonth()+1,
			y = date.getFullYear(),
			h = date.getHours(), 
			m = date.getMinutes(), 
			s = date.getSeconds(), 
			d = (d < 10) ? '0' + d : d, 
			mo = (mo < 10) ? '0' + mo : mo, 
			h = (h < 10) ? '0' + h : h, 
			m = (m < 10) ? '0' + m : m, 
			s = (s < 10) ? '0' + s : s,			
			topTitle.setTitle('АРМ администратора СМП. ' + window.MedPersonal_FIO + ', Сегодня ' + d + '.' + mo + '.' + y + 'г. ' + h + ':' + m + ':' + s);			
		 }, 1000); 
	},	
	
	// <!-- КОСТЫЛЬ
	autoEvent: true,

	startTask: function() {
		if( !this.interval ) {
			this.interval = setInterval(this.task.run, this.task.interval);
		}
	},
	
	stopTask: function() {
		if( this.interval > 0 ) {
			clearInterval(this.interval);
			delete this.interval;
		}
	},
	
	refreshTaskTime: function(){
		if( !this.GridPanel.hideTaskTime ) {
			this.setTextTimeButton(this.task.interval/1000);
			if( this.interval2 ) {
				clearInterval(this.interval2);
				delete this.interval2;
			}
			this.interval2 = setInterval(this.setTextTimeButton.createDelegate(this), 1000);
		}

		var currentDate = new Date().format('d.m.Y');

		clearInterval(this.intervalReloadStore);

		if(this.dateMenu.getValue1().format('d.m.Y') === currentDate && this.dateMenu.getValue2().format('d.m.Y') === currentDate ){
			this.intervalReloadStore = setInterval(this.doSearch.createDelegate(this), 30000);
		}
	},
	// КОСТЫЛЬ -->
	
	setTextTimeButton: function(s) {
		var timeel = null;
		this.GridPanel.getTopToolbar().items.each(function(b) {
			if (b.id = this.GridPanel.id + '_tasktime' ) {
				timeel = b;
			}
		}.createDelegate(this));
		if( timeel.setText ) {
			timeel.seconds = s || timeel.seconds-1;
			var s = s || +timeel.seconds;
			if( s < 0 ) return;
			timeel.setText('До авторефреша осталось: ' + s + ' cек');
		}
	},
	
	getGroupName: function(id) {
		var groups = [
			'Принятые звонки',
			'Переданы в СМП',
			'Приняты СМП',
			'Обслужены в СМП',
			'Переданные в НМП',
			'Приняты в НМП',
			'Отклонены НМП',
			'Обслужены НМП',
			'Отказ',
			'Закрытые'
		];
		if( id ) {
			return groups[id-1];
		} else {
			return groups;
		}
	},
	to_clinic: function() {

	var parent_object = this,
		lpuHomeVisit_id = 0,
		selrecord = this.GridPanel.getSelectionModel().getSelected();
	log(this.GridPanel.getStore());
	if(!selrecord) return false;

	var callFromSMPToClinic = new Ext.Window({
		width:400,
		heigth:300,
		title:'Выберите поликлинику и дату вызова',
		modal: true,
		draggable:false,
		resizable:false,
		closable : false,
		items:[{
			xtype: 'form',
			bodyStyle: {padding: '10px'},
			disabledClass: 'field-disabled',
			hiddenName: 'refuse_form',
			items:
				[{
					fieldLabel: 'Выберите МО',
					listWidth: 400,
					store: new Ext.data.Store({
						autoLoad: false,
						reader: new Ext.data.JsonReader({
							fields: ['Lpu_id', 'Lpu_Nick']
							//root: 'rows'
						}),
						proxy: new Ext.data.HttpProxy({
							method: 'POST',
							url: '/?c=CmpCallCard&m=loadLpuHomeVisit'
						})
					}),
					listeners: {
						render: function() { this.getStore().load(); },
						select: function(c, r, i) {
							lpuHomeVisit_id = r.get('Lpu_id');
						}
					},
					name: 'LpuHomeVisit_id',
					xtype: 'swLpuHomeVisitStorageCombo'
				},
				{
					fieldLabel: 'Дата',
					name: 'CmpCloseCardHomeVisitDate',
					id: 'CmpCloseCardHomeVisitDate',
					value:  new Date(),
					xtype: 'swdatefield'
				}]
		}],
		buttons:[{
			text:'Ок',
			id:'save',
			handler:function(){
				
				function formatDate(date) {
					var dd = date.getDate();
					if (dd < 10) dd = '0' + dd;
					var mm = date.getMonth() + 1;
					if (mm < 10) mm = '0' + mm;
					var yy = date.getFullYear();
					return dd + '.' + mm + '.' + yy;
				}
				var homeVisitDate = formatDate(Ext.getCmp('CmpCloseCardHomeVisitDate').getValue());
				parent_object.callFromSmpToLpu(homeVisitDate, lpuHomeVisit_id);
				callFromSMPToClinic.close();
			}
		},
			{
				text: 'Отмена',
				handler: function(){
					callFromSMPToClinic.close();
				}
			}]
	})
		callFromSMPToClinic.show();


},
	callFromSmpToLpu: function(homeVisitDate, lpuHomeVisit_id){
		var record = this.GridPanel.getSelectionModel().getSelected();

		if(!record)
			sw.swMsg.alert('Ошибка', 'Выберите выберите вызов СМП');
		
		this.getLoadMask().show();
		var parentObject = this;

		Ext.Ajax.request({
			url: '/?c=CmpCallCard&m=addHomeVisitFromSMP',
			params: {
				CmpCallCard_id: record.get('CmpCallCard_id'),
				Person_id: record.get('Person_id'),
				lpuHomeVisit_id: lpuHomeVisit_id,
				homeVisitDate: homeVisitDate,
				pmUser_insID: record.get('pmUser_insID'),
				armtype: parentObject.ARMType.toString()
			},
			callback: function(o, s, r) {
				parentObject.getLoadMask().hide();
				if(s) {
					var obj = Ext.util.JSON.decode(r.responseText);
					if( obj[0].success )
						sw.swMsg.alert('Отправка данных', 'Данные успешно отправлены.');
					else
						sw.swMsg.alert('Ошибка', obj[0].Error_Msg);
				}
			}.createDelegate(this)
		});
	},

	selectLpuTransmit: function() {
		var record = this.GridPanel.getSelectionModel().getSelected();
		if(!record) return false;
		
		if (record.get('Person_Birthday') != null) {
			var personAge = swGetPersonAge(record.get('Person_Birthday'));
			/*var personBirthdayDate = new Date();
			var today = new Date();
			personBirthdayDate.setTime(Date.parse(record.get('Person_Birthday').replace(/(\d+).(\d+).(\d+)/, '$2/$1/$3'),'m/d/Y'));
			var personAge = today.getFullYear() - personBirthdayDate.getFullYear();
			var m = today.getMonth() - personBirthdayDate.getMonth();
			if (m < 0 || (m === 0 && today.getDate() < personBirthdayDate.getDate())) {
				personAge--;
			}*/

			if (!getRegionNick().inlist(['astra']) && personAge<1) {
				sw.swMsg.alert('Ошибка', 'Пациенты до года обслуживаются в СМП');
				return false;
			}
		}
		var parentObject = this;
		//this.emitEditingEvent( record.get('CmpCallCard_id'),function(){
			getWnd('swSelectLpuWithMedServiceWindow').show({
				MedServiceType_id: 18,
				MedServiceType_Name: 'cлужба неотложной помощи',
				callback: function(data) {
					parentObject.setLpuTransmit(record, data);
				},
				onCancel: function(){
					parentObject.emitEndEditingEvent( record.get('CmpCallCard_id'));
				}
			});
		//});
	},
	
	setLpuTransmit: function(selrecord, lpu_data) { 		
		var cb = this.setStatusCmpCallCard;		   

		var params = {
			Lpu_ppdid: lpu_data.Lpu_id,
			CmpCallCard_id: selrecord.get('CmpCallCard_id')
		};

		this.getLoadMask('Сохранение...').show();
		Ext.Ajax.request({
			params: params,
			url: '/?c=CmpCallCard&m=setLpuTransmit',
			callback: function(o, s, r) {
				this.getLoadMask().hide();
				if(s) {								
					var resp = Ext.util.JSON.decode(r.responseText);
					if( resp.success ) {
						selrecord.set('SendLpu_Nick', lpu_data.Lpu_Nick);
						selrecord.set('CmpGroup_id', lpu_data.Lpu_id > 0 ? 1 : 2);
						selrecord.commit();
						with(this.GridPanel.getStore()) {
							var ss = getSortState();
							sort(ss.field, ss.direction);
						}
						this.addEmptyRecord();

						if( selrecord.get('CmpGroup_id') == 5 ) {
							this.setStatusCmpCallCard( this, 0, null, null, null, 2 );
						} else {
							this.setStatusCmpCallCard( this, 1, null, null, null, 2 );
						}
					}
				} else {
					this.emitEndEditingEvent(selrecord.get('CmpCallCard_id'));
				}
			}.createDelegate(this)
		});
	},
	
	getRowCount: function(group_id) {
		var gs = [];
		this.GridPanel.getStore().each(function(r) {
			if( r.get('CmpGroupName_id') == group_id && r.get('CmpCallCard_id') != null ) {
				gs.push(r.get('CmpCallCard_id'));
			}
		});

		return gs.length;
	},
	transmitToDispatchDirect: function() {
		var record = this.GridPanel.getSelectionModel().getSelected();
		if(!record) return false;
		var parentObject = this;
		//this.emitEditingEvent(record.get('CmpCallCard_id'), function(){
			parentObject.setStatusCmpCallCard(null, 1);
		//})
	},
	setStatusCmpCallCard: function(IsOpen, StatusType_id, StatusComment, refuse_reason_id, callbackFn, npmFlag) {
		var record = this.GridPanel.getSelectionModel().getSelected();
		if(!record) return false;
		this.getLoadMask().show();
		var parentObject = this;
		if (StatusType_id != 5 || typeof(refuse_reason_id)=='undefined')  {
			refuse_reason_id = 0;
		}
		
		var nmp = (npmFlag || record.get('CmpCallCard_IsNMP') || 1);

		var params = {
			CmpCallCard_id: record.get('CmpCallCard_id'),
			CmpCallCardStatusType_id: StatusType_id,
			CmpCallCardStatus_Comment: StatusComment || null,
			CmpCallCard_IsOpen: IsOpen,
			armtype: this.ARMType.toString(),
			CmpReason_id: refuse_reason_id,
			CmpCallCard_isNMP: nmp
		};

		Ext.Ajax.request({
			url: '/?c=CmpCallCard&m=setStatusCmpCallCard',
			params: params,
			callback: function(o, s, r) {
				this.getLoadMask().hide();
				if(s) {
					var obj = Ext.util.JSON.decode(r.responseText);
					if( obj.success ) {
						if(callbackFn && typeof callbackFn == 'function')callbackFn();
						if (!parentObject.emitEndEditingEvent(record.get('CmpCallCard_id')))
							this.doSearch();
						/*
						record.set('CmpGroup_id', obj.CmpGroup_id);
						if( obj.CmpGroup_id != 5 ) {
							record.set('PPDResult', '');
						}
						record.commit();
						with(this.GridPanel.getStore()) {
							var ss = getSortState();
							sort(ss.field, ss.direction);
						}
						this.addEmptyRecord();
						*/
//					    this.doSearch();
					}
				}
			}.createDelegate(this)
		});
	},
	
	addEmptyRecord: function() {
		var groups = {},
			gs = [];
		
		for(var j=1; j<=this.getGroupName().length; j++) {
			groups[j] = [];
		}
		
		this.GridPanel.getStore().each(function(rec) {
			groups[rec.get('CmpGroup_id')].push(rec.get('CmpCallCard_id'));
		});
		for(i in groups) {
			if( groups[i].length == 0 ) {
				gs.push(+i);
			}
		}
		for(var i=0; i<gs.length; i++) {
			var data = {};
			this.GridPanel.getColumnModel().getColumnsBy(function(c) {data[c.dataIndex] = null;});
			data['CmpGroup_id'] = gs[i];
			data['CmpGroupName_id'] = (data['CmpGroup_id']==10)?('10'):('0'+data['CmpGroup_id']+'');
			this.GridPanel.getStore().add(new Ext.data.Record(data));
		}
		with(this.GridPanel.getStore()) {
			var ss = getSortState();
			sort(ss.field, ss.direction);
		}
		this.removeEmptyRecord();
	},
	
	removeEmptyRecord: function() {
		this.GridPanel.getStore().each(function(r) {
			if( r.get('CmpCallCard_id') == null && this.getRowCount(r.get('CmpGroupName_id')) > 0 ) {
				this.GridPanel.getStore().remove(r);
			}
		}.createDelegate(this));
	},

	selectEmergencyTeam: function(flag) {		
		var record = this.GridPanel.getSelectionModel().getSelected();
		if(!record) return false;
		var parentObject = this;
		//this.emitEditingEvent(record.get('CmpCallCard_id'),function(){
			getWnd('swSelectEmergencyTeamWindow').show({
				CmpCallCard: record.get('CmpCallCard_id'),
				onDoCancel: function() {
					parentObject.emitEndEditingEvent(record.get('CmpCallCard_id'));
				},
				callback: function(data) {
					parentObject.setEmergencyTeam(record, data, flag);
				},
				adress: record.get('Adress_Name')
			});
		//});
	},
	setEmergencyTeam: function(selectedRecord,EmergencyTeam_data,flag) {		
		var cb = this.setStatusCmpCallCard;		
		var cb2 = this.closeCmpCallCard;			
		this.getLoadMask('Назначение...').show();
		var parentObject = this;
		Ext.Ajax.request({
			params: {
				EmergencyTeam_id: EmergencyTeam_data.EmergencyTeam_id,
				CmpCallCard_id: selectedRecord.get('CmpCallCard_id'),
				Person_FIO: selectedRecord.get('Person_FIO'),
				Person_Firname: selectedRecord.get('Person_Firname'),
				Person_Secname: selectedRecord.get('Person_Secname'),
				Person_Surname: selectedRecord.get('Person_Surname'),
				Person_id: selectedRecord.get('Person_id'),
				Person_Birthday: selectedRecord.get('Person_Birthday'),
				CmpCallCard_prmDate: Ext.util.Format.date(selectedRecord.get('CmpCallCard_prmDate'), 'H:i | d.m.Y') ,
				CmpReason_Name: selectedRecord.get('CmpReason_Name'),
				CmpCallType_Name: selectedRecord.get('CmpCallType_Name'),
				Adress_Name: selectedRecord.get('Adress_Name')
			},
//			url: '/?c=CmpCallCard&m=setEmergencyTeam',
			url: '/?c=CmpCallCard4E&m=setEmergencyTeamWithoutSending',
			timeout: 10000,
			callback: function(o, s, r) {
				this.getLoadMask().hide();
				if(s) {								
					var resp = Ext.util.JSON.decode(r.responseText);
					if( resp.success ) {
						selectedRecord.set('EmergencyTeam_Num', EmergencyTeam_data.EmergencyTeam_Num);
						selectedRecord.commit();										
						if (flag == true) {
							cb2.call(this, 'add');
						} else {
							cb.call( this, null, 2 );
						}
					}
					else {
//						//TODO:Передать вызов по рации или повторить запрос
//						sw.swMsg.alert('Ошибка', resp.Err_Msg);
						Ext.Msg.show({
							title:'Ошибка',
							msg: resp.Err_Msg,
							buttons:  { 
								yes: "Повторить попытку",
								no: "Передать по рации",
								cancel: "Отмена"
							},
							fn: function(param){
								
								switch (param) {
									case 'yes':
										parentObject.setEmergencyTeam(selectedRecord,EmergencyTeam_data);
									break;
									case 'no':										
										Ext.Ajax.request({
											params: {
												EmergencyTeam_id: EmergencyTeam_data.EmergencyTeam_id,
												CmpCallCard_id: selectedRecord.get('CmpCallCard_id')
											},
											url: '/?c=CmpCallCard&m=setEmergencyTeamWithoutSending',
											callback: function(o, s, r) {
												if(s) {								
													var resp = Ext.util.JSON.decode(r.responseText);
													if( resp.success ) {														
														selectedRecord.set('EmergencyTeam_Num', EmergencyTeam_data.EmergencyTeam_Num);
														selectedRecord.commit();																												
														if (flag == true) {															
															cb2.call( parentObject, 'add' );
														} else {
															cb.call( parentObject, null, 2 );
														}
													} 
													else {
														sw.swMsg.alert('Ошибка', resp.Error_Msg);
													}
												}
											}
										});
									break;
									case 'cancel':
										parentObject.emitEndEditingEvent(selectedRecord.get('CmpCallCard_id'));
									break;
									default:
									return false;
								}
							},
							animEl: 'elId',
							icon: Ext.MessageBox.QUESTION
						});
					}
				}else{
					sw.swMsg.alert('Ошибка', 'Произошла сетевая ошибка при передаче или получении данных сервером');
				}
			}.createDelegate(this)
		});
	},
	
	//открытие формы аудит записи
	showAuditWindow: function(){
		var params = new Object();
		params['key_id'] = this.GridPanel.getSelectionModel().getSelected().data.CmpCallCard_id;
		params['key_field'] = 'CmpCallCard_id';
		getWnd('swAuditWindow').show(params);
	},

	// Прикрепление человека
	openPCardHistory: function() {
		var record = this.GridPanel.getSelectionModel().getSelected();
		if( !record || record.get('Person_id') == null ) return false;
		
		var params = record.data;
		params.onHide = this.focusSelectedRow.createDelegate(this);
		ShowWindow('swPersonCardHistoryWindow', params);
	},
	
	// Редактирование человека
	openPersonEdit: function() {
		var record = this.GridPanel.getSelectionModel().getSelected();
		if( !record || record.get('Person_id') == null ) return false;
		
		var params = record.data;
		params.onClose = this.focusSelectedRow.createDelegate(this);
		params.callback = this.doSearch.createDelegate(this);
		ShowWindow('swPersonEditWindow', params);
	},
	
	// История лечения
	openPCureHistory: function() {
		var record = this.GridPanel.getSelectionModel().getSelected();
		if( !record || record.get('Person_id') == null ) return false;
		
		var params = record.data;
		params.onHide = this.focusSelectedRow.createDelegate(this);
		ShowWindow('swPersonCureHistoryWindow', params);
	},
	
	focusSelectedRow: function() {
		var record = this.GridPanel.getSelectionModel().getSelected();
		if( !record ) return false;
		
		var idx = this.GridPanel.getStore().indexOf(record);
		var row = this.GridPanel.getView().getRow(idx);
		
		this.GridPanel.getView().focusRow(row);
	},
	
	printCmpCallCard: function() {		
		var grid = this.GridPanel;
		if ( !grid.getSelectionModel().getSelected() ) {
			return false;
		}
		var record = grid.getSelectionModel().getSelected();
		if (!record)
		{
			Ext.Msg.alert('Ошибка', 'Не выбрана карта.<br/>');
			return false;
		}
		var CmpCallCard_id = record.get('CmpCallCard_id');
		if ( !CmpCallCard_id )
			return false;
		if(record.get('LpuBuilding_IsPrint') > 0 ){
			var id_salt = Math.random();
			var id_salt2 = Math.random();
			var win_id = 'print_110u' + Math.floor(id_salt * 10000);
			var win_id2 = 'print_110u' + Math.floor(id_salt2 * 10000);
			var win = window.open('/?c=CmpCallCard&m=printCmpCloseCard110&page=1&CmpCallCard_id=' + CmpCallCard_id, win_id);
			var win2 = window.open('/?c=CmpCallCard&m=printCmpCloseCard110&page=2&CmpCallCard_id=' + CmpCallCard_id, win_id2);
		}else{
			var id_salt = Math.random();
			var win_id = 'print_110u' + Math.floor(id_salt * 10000);
			var win = window.open('/?c=CmpCallCard&m=printCmpCloseCard110&CmpCallCard_id=' + CmpCallCard_id, win_id);
		}

		//var url ='';
		//window.open(url, '_blank');		
	},
	
	closeCmpCallCard: function(action) {	
		if ( !action || !action.toString().inlist([ 'add', 'edit']) ) {
			return false;
		}
		
		var wnd = 'swCmpCallCardNewCloseCardWindow';

		if ( getWnd(wnd).isVisible() ) {
			sw.swMsg.alert('Сообщение', 'Окно редактирования карты вызова уже открыто');
			return false;
		}		
		var parentObject = this;
		var formParams = new Object();
		var grid = this.GridPanel;//.getGrid();
		var params = new Object();
		params.action = action;
		params.callback = function(data) {
			if ( !data || !data.cmpCloseCardData ) {
				return false;
			}
			
			// Назначаем тип
			/*this.setStatusCmpCallCard(1, 6, null, null, function(){
				if(data.cmpCloseCardData.action == 'add'){
					parentObject.socket.emit('changeCmpCallCard', data.cmpCloseCardData.CmpCallCard_id, 'closeCard', function(data){
						log('NODE emit closeCard');
					});
				}
			});*/
			
			// Обновить grid
			grid.getStore().reload();
			this.autoEvent = false;

		}.createDelegate(this);

		if ( !grid.getSelectionModel().getSelected() ) {
			return false;
		}
		var selected_record = grid.getSelectionModel().getSelected();
		if ( !selected_record.get('CmpCallCard_id') ) {
			return false;
		}
		formParams.CmpCallCard_id = selected_record.get('CmpCallCard_id');

		params.onHide = function() {
			parentObject.emitEndEditingEvent(selected_record.get('CmpCallCard_id'));
			grid.getView().focusRow(grid.getStore().indexOf(selected_record));
		};
		formParams.ARMType = this.ARMType.toString();

		params.formParams = formParams;
//		this.emitEditingEvent(selected_record.get('CmpCallCard_id'),function(){
			getWnd(wnd).show(params);
//		});		
		
	},
	
	refuseCmpCallCard: function() {
		var refuse_CmpReason_id = 0;
		var parent_object = this;
				
		var selrecord = this.GridPanel.getSelectionModel().getSelected();
		log(this.GridPanel.getStore());
		if(!selrecord) return false;

		var refuseCmpCallCardWin = new Ext.Window({
			width:400,
			heigth:300,
			title:'Отказ от вызова',
			modal: true,
			draggable:false,
			resizable:false,
			closable : false,
			items:[{
				xtype: 'form',
				bodyStyle: {padding: '10px'},
				disabledClass: 'field-disabled',
				hiddenName: 'refuse_form',
				items:
				[{
					width: 250,
					allowBlank: false,
					fieldLabel: 'Причина отмены',
					xtype:'swbaselocalcombo',
					hiddenName: 'CmpRejectionReason_id',
					id: 'CmpRejectionReason',
					store: new Ext.data.JsonStore({
						url: '/?c=CmpCallCard4E&m=getRejectionReason',
						editable: false,
						key: 'CmpRejectionReason_id',
						autoLoad: true,
						fields: [
							{name: 'CmpRejectionReason_id', type: 'int'},
							{name: 'CmpRejectionReason_code', type: 'string'},
							{name: 'CmpRejectionReason_name', type: 'string'}
						],
						sortInfo: {
							field: 'CmpRejectionReason_name'
						}
					}),
					triggerAction: 'all',
					displayField:'CmpRejectionReason_name',
					tpl: '<tpl for="."><div class="x-combo-list-item">'+
								'{CmpRejectionReason_name}'+
							'</div></tpl>',
					valueField: 'CmpRejectionReason_id'
				},
				{
					disabledClass: 'field-disabled',
					fieldLabel: 'Комментарий',
					height: 100,
					name: 'CmpCallCard_Comm',
					id: 'refuse_comment',
					width: 250,
					xtype: 'textarea',
					style: 'padding: 0; padding-top: 5px; margin-bottom: 5px;'
				}]
			}],
			buttons:[{
				text:'Сохранить',
				id:'save',
				handler:function(a,b,c){
					var refuse_comment = Ext.getCmp('refuse_comment').getValue();
					var refuse_CmpReason_id = Ext.getCmp('CmpRejectionReason').getValue();
					if(refuse_CmpReason_id){
						parent_object.setStatusCmpCallCard(null, 5, refuse_comment, refuse_CmpReason_id);
						refuseCmpCallCardWin.close();
					}else{
						sw.swMsg.show({
							buttons: Ext.Msg.OK,
							icon: Ext.Msg.WARNING,
							msg: 'Должна быть причина отмены',
							title: ERR_INVFIELDS_TIT
						});
						return false;
					}
				}
			},
			{
				text: 'Закрыть',
				handler: function(){
					refuseCmpCallCardWin.close();
				}
			}]
		})
		refuseCmpCallCardWin.show();
	},
	/*
	refuseCmpCallCard: function() {
		var refuse_CmpReason_id = 0;
		// Регулярное выражение для фильтрации по кодам отказа (Подборка из БД dbo.CmpReason). 
		var refuse_RegExp = /509|510|511|559|560|588|589|596|597|601|602|610|611|622|623|641|642|646|647|681|682|759|761|773/;
		var parent_object = this;
				
		var selrecord = this.GridPanel.getSelectionModel().getSelected();
		log(this.GridPanel.getStore());
		if(!selrecord) return false;
		
		//this.emitEditingEvent(selrecord.get('CmpCallCard_id'), function(){
			var refuseCmpCallCardWin = new Ext.Window({
				width:400,
				heigth:300,
				title:'Введите код отказа и комментарий',
				modal: true,
				draggable:false,
				resizable:false,
				closable : false,
				listeners: {
					'hide': function() {
						parent_object.emitEndEditingEvent(selrecord.get('CmpCallCard_id'));
					}
				},
				items:[{
					xtype: 'form',
					bodyStyle: {padding: '10px'},
					disabledClass: 'field-disabled',
					hiddenName: 'refuse_form',
					items:
					[{																	
					//comboSubject: 'CmpReason',
						disabledClass: 'field-disabled',
						fieldLabel: 'Повод',
						allowBlank: false,
						hiddenName: 'CmpReason_id',
						// tabIndex: TABINDEX_PEF + 5,
						width: 250,
						store: new Ext.db.AdapterStore({
							dbFile: 'Promed.db',
							fields: [
								{name: 'CmpReason_id', mapping: 'CmpReason_id'},
								{name: 'CmpReason_Code', mapping: 'CmpReason_Code'},
								{name: 'CmpReason_Name', mapping: 'CmpReason_Name'}
							],
							autoLoad: true,
							key: 'CmpReason_id',
							sortInfo: {field: 'CmpReason_Code'},
							tableName: 'CmpReason'
						}),
						mode: 'local',
						triggerAction: 'all',
						listeners: {
							//render: function() { this.getStore().load(); },
							select: function(c, r, i) {
								this.setValue(r.get('CmpReason_id'));
								this.setRawValue(r.get('CmpReason_Code')+'.'+r.get('CmpReason_Name'));
								refuse_CmpReason_id = r.get('CmpReason_id');
							},
							blur: function() {
								this.collapse();
								if ( this.getRawValue() == '' ) {
									this.setValue('');
									if ( this.onChange && typeof this.onChange == 'function' ) {
										this.onChange(this, '');
									}
								} else {
									var store = this.getStore(),
									val = this.getRawValue().toString().substr(0, 5);
									val = LetterChange(val);
									if ( val.charAt(3) != '.' && val.length > 3 ) {
										val = val.slice(0,3) + '.' + val.slice(3, 4);
									}
									val = val.replace(' ', '');
									var yes = false;
									store.each(function(r){
										if ( r.get('CmpReason_Code') == val ) {
											this.setValue(r.get(this.valueField));
											this.fireEvent('select', this, r, 0);
											this.fireEvent('change', this, r.get(this.valueField), '');
											if ( this.onChange && typeof this.onChange == 'function') {
												this.onChange(this, r.get(this.valueField));
											}
											yes = true;
											return true;
										}
									}.createDelegate(this));
								}
							}
						},
						doQuery: function(q) {
							var c = this;
							this.getStore().load({
								callback: function() {
									this.filter('CmpReason_Code', q);
									this.loadData(getStoreRecords(this));
									if( this.getCount() == 0 ) {
										c.setRawValue(q.slice(0, q.length-1));
										c.doQuery(c.getRawValue());
									}
									c[ c.expanded ? 'collapse' : 'expand' ]();
									this.filter('CmpReason_id',refuse_RegExp);
								}
							});
						},
						onTriggerClick: function() {
							this.focus();
							if( this.getStore().getCount() == 0 || this.isExpanded() ) {
								this.collapse();
								return;
							}
							if(this.getValue() > 0) {
								this[ this.isExpanded() ? 'collapse' : 'expand' ]();
							} else {
								this.doQuery(this.getRawValue());
							}
						},
						tpl: new Ext.XTemplate(
							'<tpl for="."><div class="x-combo-list-item">',
							'<font color="red">{CmpReason_Code}</font>.{CmpReason_Name}',
							'</div></tpl>'
						),
						valueField: 'CmpReason_id',
						displayField: 'CmpReason_Name',
						xtype: 'swbaselocalcombo',
						style: 'padding: 0; padding-top: 5px; margin-bottom: 5px;',
						id: 'Combobox'
					},
					{
						disabledClass: 'field-disabled',
						fieldLabel: 'Комментарий',
						height: 100,
						name: 'CmpCallCard_Comm',
						id: 'refuse_comment',
						width: 250,
						xtype: 'textarea',
						style: 'padding: 0; padding-top: 5px; margin-bottom: 5px;'
					}]
				}],
				buttons:[{
					text:'Ок',
					id:'save',
					handler:function(){
						if (refuse_CmpReason_id <= 0) {
							sw.swMsg.show({
								buttons: Ext.Msg.OK,
								icon: Ext.Msg.WARNING,
								msg: 'Должен быть выбран повод отказа',
								title: ERR_INVFIELDS_TIT
							});
							return false;
						}
						else {
							var refuse_comment = Ext.getCmp('refuse_comment').getValue();
							parent_object.setStatusCmpCallCard(null, 5, refuse_comment, refuse_CmpReason_id);
							refuseCmpCallCardWin.close();
						}
					}
				},
				{
					text: 'Отмена',
					handler: function(){
						refuseCmpCallCardWin.close();
					}
				}]
			})
			refuseCmpCallCardWin.show();
		//});
	},
	*/
	searchGrid: function(params){
		var win = this;
		win.GridPanel.getSelectionModel().clearSelections();
			var store = win.GridPanel.getStore();
			store.clearFilter();
			for(var key in params){
				if( !params[key] ) delete params[key];
			}
			if( Object.keys(params).length == 0 ) return false;
			store.filterBy(function(rec) {
				var flag = false;
				for(var key in params){
					var str = params[key];
					var elem = rec.get(key) || '';
					switch(key){
						case 'CmpCallCard_prmDate': 
						case 'Person_Birthday':
						case 'ServeDT':
							// дата
							str = Ext.util.Format.date(str, 'd.m.Y');
							elem = Ext.util.Format.date(elem, 'd.m.Y');
							break;
						default:
							str = String(params[key]).toUpperCase();
							elem = String(elem).toUpperCase();
							break;
					}

					if( elem.indexOf(str)+1 ) {
						flag = true; 
					}else{
						flag = false;
						break;
					}
				}

				return flag;
			});
	},

	doReset2: function(){
		var win = this;
		var currentDate = new Date();
		currentDate = Ext.util.Format.date(currentDate, 'd.m.Y');
		var displayDeletedCards = Ext.getCmp('displayDeletedCards');
		var periodDey = Ext.getCmp('periodDey');

		displayDeletedCards.setValue(false);
		win.dateMenu.setValue(currentDate+' - '+currentDate);
		win.comboHourSelect.clearValue();
		win.dispatchCallSelect.clearValue();
		win.LpuBuildingSelect.clearValue();
		win.emergencyTeamCombo.clearValue();

		periodDey.toggle();
		win.comboHourSelect.setDisabled(false);

		win.doSearch();
	},

	initComponent: function(){
		var win = this;
		this.filterRowReq = new Ext.ux.grid.FilterRow({
			id:'filterRowReq',
			width: 24,
			hidden: false,
			fixed: true,
			parId:win.id,
			clearFilterBtn: false,
			clearFilters: function() {
				gridStore.clearFilter();
				this._search(true);
			},
			listeners:  {
				'search':function(params){
					win.searchGrid(params);
				}
			}
		});

		Ext.apply(sw.Promed.Actions, {
			PersonUnionHistoryAction: {
				text: 'История модерации двойников',
				tooltip: 'История модерации двойников',
				iconCls: 'doubles-history16',
				handler: function(){
					getWnd('swPersonUnionHistoryWindow').show();
				}
			},
			
			swMESOldAction: {
				text: getMESAlias(),
				tooltip: 'Справочник ' + getMESAlias(),
				iconCls: 'spr-mes16',
				handler: function(){
					getWnd('swMesOldSearchWindow').show();
				},
				hidden: false // TODO: После тестирования доступ должен быть для всех
			},
			
			SprRlsAction: {
				text: getRLSTitle(),
				tooltip: getRLSTitle(),
				iconCls: 'rls16',
				handler: function(){
					getWnd('swRlsViewForm').show();
				},
				hidden: false
			},
			SprPostAction: {
				text: 'Должности',
				tooltip: 'Должности',
				iconCls: '',
				handler: function(){
					window.gwtBridge.runDictionary(getPromedUserInfo(), 'Post', main_center_panel);
				}
			},
			SprSkipPaymentReasonAction: {
				text: 'Причины невыплат',
				tooltip: 'Причины невыплат',
				iconCls: '',
				handler: function(){
					window.gwtBridge.runDictionary(getPromedUserInfo(), 'SkipPaymentReason', main_center_panel);
				}
			},
			SprWorkModeAction: {
				text: 'Режимы работы',
				tooltip: 'Режимы работы',
				iconCls: '',
				handler: function(){
					window.gwtBridge.runDictionary(getPromedUserInfo(), 'WorkMode', main_center_panel);
				}
			},
			SprSpecialityAction: {
				text: 'Специальности',
				tooltip: 'Специальности',
				iconCls: '',
				handler: function(){
					window.gwtBridge.runDictionary(getPromedUserInfo(), 'Speciality', main_center_panel);
				}
			},
			SprDiplomaSpecialityAction: {
				text: 'Дипломные специальности',
				tooltip: 'Дипломные специальности',
				iconCls: '',
				handler: function(){
					window.gwtBridge.runDictionary(getPromedUserInfo(), 'DiplomaSpeciality', main_center_panel);
				}
			},
			SprLeaveRecordTypeAction: {
				text: 'Тип записи окончания работы',
				tooltip: 'Тип записи окончания работы',
				iconCls: '',
				handler: function(){
					window.gwtBridge.runDictionary(getPromedUserInfo(), 'LeaveRecordType', main_center_panel);
				}
			},
			SprEducationTypeAction: {
				text: 'Тип образования',
				tooltip: 'Тип образования',
				iconCls: '',
				handler: function(){
					window.gwtBridge.runDictionary(getPromedUserInfo(), 'EducationType', main_center_panel);
				}
			},
			SprEducationInstitutionAction: {
				text: 'Учебное учреждение',
				tooltip: 'Учебное учреждение',
				iconCls: '',
				handler: function(){
					window.gwtBridge.runDictionary(getPromedUserInfo(), 'EducationInstitution', main_center_panel);
				}
			}
		});
		
		var form = this,
			taskInterval = 30,
			options = getGlobalOptions();
		
		// Убрать в Уфе автообновление у администратора СМП по таймеру http://redmine.swan.perm.ru/issues/49244
		// Пусть раз в сутки обновляется =)
		if ( options.region.nick == 'ufa' ) {
			taskInterval = 86400;
		}

		swExt4.app.getController('smp.controllers.DecisionTree');

		this.task = {run: this.doSearch.createDelegate(this), interval: (taskInterval*1000)};
		
		this.onKeyDown = function (inp, e) {
			if (e.getKey() == Ext.EventObject.ENTER) {
				e.stopEvent();
				this.autoEvent = false;
				this.doSearch();
			}
		}.createDelegate(this);

		this.FilterPanel = new sw.Promed.BaseWorkPlaceFilterPanel({
			owner: form,
			labelWidth: 120,
			hidden: true, // скрыть панель фильтров #114329
			filter: {
				title: 'Фильтры',
				layout: 'form',
				items: [{
					layout: 'column',
					items: [{
						layout: 'form',
						labelWidth: 120,
						items: [{
							xtype: 'textfieldpmw',
							width: 200,
							name: 'Search_SurName',
							fieldLabel: 'Фамилия',
							listeners: {
								'keydown': form.onKeyDown
							}
						}]
					}, {
						layout: 'form',
						labelWidth: 120,
						items: [{
							xtype: 'textfieldpmw',
							width: 120,
							name: 'Search_FirName',
							fieldLabel: 'Имя',
							listeners: {
								'keydown': form.onKeyDown
							}
						}]
					}, {
						layout: 'form',
						labelWidth: 120,
						items: [{
							xtype: 'textfieldpmw',
							width: 120,
							name: 'Search_SecName',
							fieldLabel: 'Отчество',
							listeners: {
								'keydown': form.onKeyDown
							}
						}]
					}, {
						layout: 'form',
						labelWidth: 120,
						items: [{
							xtype: 'swdatefield',
							format: 'd.m.Y',
							plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
							name: 'Search_BirthDay',
							fieldLabel: 'ДР',
							listeners: {
								'keydown': form.onKeyDown
							}
						}]
					}]
				}, {
					layout: 'column',
					items: [{
						layout: 'form',
						labelWidth: 120,
						items: [
							{
								fieldLabel: 'Куда доставлен',
								hiddenName: 'CmpLpu_id',
								listeners: {
									select: form.onKeyDown
								},
								listWidth: 400,
								width: 200,
								xtype: 'swlpucombo'
							}
							/*
							{
							comboSubject: 'CmpLpu',
							fieldLabel: 'Куда доставлен',
							hiddenName: 'CmpLpu_id',
							listeners: {
								'keydown': form.onKeyDown
							},
							listWidth: 400,
							width: 200,
							xtype: 'swcommonsprcombo'
							}
							*/
						]
					}, {
						layout: 'form',
						labelWidth: 120,
						items: [{
							enableKeyEvents: true,
							fieldLabel: '№ вызова за день',
							listeners: {
								'keydown': form.onKeyDown
							},
							name: 'CmpCallCard_Numv',
							width: 120,
							xtype: 'textfield'
						}]
					}, {
						hidden: (getRegionNick() == 'ufa'),
						layout: 'form',
						labelWidth: 120,
						items: [{
							enableKeyEvents: true,
							fieldLabel: '№ вызова за год',
							listeners: {
								'keydown': form.onKeyDown
							},
							name: 'CmpCallCard_Ngod',
							width: 120,
							xtype: 'textfield'
						}]
					}, {
						hidden: (getRegionNick() != 'krym'),
						layout: 'form',
						labelWidth: 120,
						items: [{
							fieldLabel: 'Карта проверена',
							comboSubject: 'YesNo',
							hiddenName: 'CheckCard',
							xtype: 'swcommonsprcombo'
						}]
					},
					{
						layout: 'form',
						labelWidth: 120,
						items: [{
							name: 'displayDeletedCards',
							labelSeparator: '',
							boxLabel: 'Показывать удаленные вызовы',
							xtype: 'checkbox'
						}]
					}]
				}, {
					layout: 'column',
					items: [{
						layout: 'form',
						items: [{
							style: "padding-left: 20px",
							xtype: 'button',
							id: form.id + 'BtnSearch',
							text: 'Найти',
							iconCls: 'search16',
							handler: function() {
								form.doSearch();
							}.createDelegate(form)
						}]
					}, {
						layout: 'form',
						items: [{
							style: "padding-left: 10px",
							xtype: 'button',
							id: form.id + 'BtnClear',
							text: 'Сброс',
							iconCls: 'reset16',
							handler: function() {
								form.doReset();
							}.createDelegate(form)
						}]
					}, {
						layout: 'form',
						items:
							[{
								style: "padding-left: 10px",
								xtype: 'button',
								text: 'Считать с карты',
								iconCls: 'idcard16',
								handler: function()
								{
									form.readFromCard();
								}
							}]
					}]
				}]
			}
		});

		var gridFields = [
			{dataIndex: 'CmpCallCard_id', header: 'ID', key: true, hidden: true, hideable: false},
			{dataIndex: 'Person_id', hidden: true, hideable: false},
			{dataIndex: 'PersonEvn_id', hidden: true, hideable: false},
			{dataIndex: 'Server_id', hidden: true, hideable: false},
			{dataIndex: 'Person_Surname', hidden: true, hideable: false},
			{dataIndex: 'Person_Firname', hidden: true, hideable: false},
			{dataIndex: 'Person_Secname', hidden: true, hideable: false},
			{dataIndex: 'Person_Age', hidden: true, hideable: false},
			{dataIndex: 'pmUser_insID', hidden: true, hideable: false},
			{dataIndex: 'CmpCallCard_isLocked', hidden: true, hideable: false},
			{dataIndex: 'CmpRejectionReason_Name', hidden: true},
			{dataIndex: 'CmpCallCard_isDeleted', header: '&nbsp;', width: 30, renderer: function(v, p, r) {
				if (v == '2') {
					return '<img src="../img/grid/deleted-card.png">';
				}
				return '';
			}},
			{
				dataIndex: 'CmpCallCard_prmDate',
				header: 'Дата время',
				renderer: function(v, p, r) {
					return (!Ext.isEmpty(getGlobalOptions().smp_call_time_format) && getGlobalOptions().smp_call_time_format == 2) ?
						Ext.util.Format.date(v, 'd.m.Y H:i') : Ext.util.Format.date(v, 'd.m.Y H:i:s');
				},
				type: 'date',
				width: 105,
				filter: new sw.Promed.SwDateField ({
					enableKeyEvents: true,
					format: 'd.m.Y',
					plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
					name: 'CmpCallCard_prmDate',
					cls: 'inputClearDatefieldsButton',
					triggerConfig: {
						tag: 'span',
						cls: 'x-field-combo-btns',
						cn: [
							{tag: 'div', cls: 'x-form-trigger x-form-date-trigger showDatefieldButton', trigger: 'date'},
							{tag: 'div', cls: 'x-form-trigger clearDatefieldsButton', trigger: 'clear'},
						]
					},
					onTriggerClick: function(a,el){
						var attribut = el.getAttribute('trigger');
						if(attribut == 'clear') {
							this.setValue();
							win.filterRowReq._search();
						}else{
							if(this.disabled){
								return;
							}
							if(this.menu == null){
								this.menu = new Ext.menu.DateMenu();
							}
							Ext.apply(this.menu.picker,  {
								minDate : this.minValue,
								maxDate : this.maxValue,
								disabledDatesRE : this.disabledDatesRE,
								disabledDatesText : this.disabledDatesText,
								disabledDays : this.disabledDays,
								disabledDaysText : this.disabledDaysText,
								format : this.format,
								showToday : this.showToday,
								minText : String.format(this.minText, this.formatDate(this.minValue)),
								maxText : String.format(this.maxText, this.formatDate(this.maxValue))
							});
							this.menu.on(Ext.apply({}, this.menuListeners, {
								scope:this
							}));
							this.menu.picker.setValue(this.getValue() || new Date());
							this.menu.show(this.el, "tl-bl?");

						}
					}
				})
			},
			{dataIndex: 'CmpCallCard_Numv',  header: '№ вызова(за день)', width: 70,
				type: 'int',
				filter: new Ext.form.TriggerField({
					enableKeyEvents: true,
					cls: 'inputClearTextfieldsButton',
					triggerConfig: {
						tag: 'span',
						cls: 'x-field-combo-btns',
						cn: [
							{tag: 'div', cls: 'x-form-trigger clearTextfieldsButton'}
						]
					},
					onTriggerClick: function (e,btn) {
						this.setValue();
						win.filterRowReq._search();
					},
					name: 'CmpCallCard_Numv',
				})
				
			},
			{dataIndex: 'CmpCallCard_Ngod', header: '№ вызова(за год)', width: 100, hidden: (getRegionNick() == 'ufa')?true:false,
				type: 'int',
				filter: new Ext.form.TriggerField({
					name:'CmpCallCard_Ngod',
					enableKeyEvents: true,
					cls: 'inputClearTextfieldsButton',
					triggerConfig: {
						tag: 'span',
						cls: 'x-field-combo-btns',
						cn: [
							{tag: 'div', cls: 'x-form-trigger clearTextfieldsButton', trigger: 'first'},
						]
					},
					onTriggerClick: function (e,btn) {
						this.setValue();
						win.filterRowReq._search();
					}
				})
			},
			{dataIndex: 'Person_FIO', header: 'Пациент', id: 'autoexpand',
				filter: new Ext.form.TriggerField({
					name:'Person_FIO',
					enableKeyEvents: true,
					cls: 'inputClearTextfieldsButton',
					triggerConfig: {
						tag: 'span',
						cls: 'x-field-combo-btns',
						cn: [
							{tag: 'div', cls: 'x-form-trigger clearTextfieldsButton'},
						]
					},
					onTriggerClick: function (e) {
						this.setValue();
						win.filterRowReq._search();
					}
				})
			},			
			{dataIndex: 'Person_Birthday', header: 'Дата рождения', width: 105, 
				renderer: Ext.util.Format.dateRenderer('d.m.Y'),
				type: 'date',
				filter: new sw.Promed.SwDateField ({
					name: 'Person_Birthday',
					format: 'd.m.Y',
					enableKeyEvents: true,
					plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
					cls: 'inputClearDatefieldsButton',
					triggerConfig: {
						tag: 'span',
						cls: 'x-field-combo-btns',
						cn: [
							{tag: 'div', cls: 'x-form-trigger x-form-date-trigger showDatefieldButton', trigger: 'date'},
							{tag: 'div', cls: 'x-form-trigger clearDatefieldsButton', trigger: 'clear'},
						]
					},
					onTriggerClick: function(a,el){
						var attribut = el.getAttribute('trigger');
						if(attribut == 'clear') {
							this.setValue();
							win.filterRowReq._search();
						}else{
							if(this.disabled){
								return;
							}
							if(this.menu == null){
								this.menu = new Ext.menu.DateMenu();
							}
							Ext.apply(this.menu.picker,  {
								minDate : this.minValue,
								maxDate : this.maxValue,
								disabledDatesRE : this.disabledDatesRE,
								disabledDatesText : this.disabledDatesText,
								disabledDays : this.disabledDays,
								disabledDaysText : this.disabledDaysText,
								format : this.format,
								showToday : this.showToday,
								minText : String.format(this.minText, this.formatDate(this.minValue)),
								maxText : String.format(this.maxText, this.formatDate(this.maxValue))
							});
							this.menu.on(Ext.apply({}, this.menuListeners, {
								scope:this
							}));
							this.menu.picker.setValue(this.getValue() || new Date());
							this.menu.show(this.el, "tl-bl?");

						}
					}
				})
			},
			{dataIndex: 'CmpCallType_Name', header: 'Тип вызова', width: 100,
				filter: new sw.Promed.SwBaseLocalCombo({
				// filter: new sw.Promed.SwPostCombo({
					codeField: 'CmpCallType_Code',
					displayField: 'CmpCallType_Name',
					hiddenName: 'CmpCallType_id',
					name: 'CmpCallType_Name',
					cls: 'inputClearDatefieldsButton',
					triggerAction: 'none',
					triggerConfig: {
						tag: 'span',
						cls: 'x-field-combo-btns',
						cn: [
							{tag: 'div', cls: 'x-form-trigger showDatefieldButton', trigger: 'list'},
							{tag: 'div', cls: 'x-form-trigger clearDatefieldsButton', trigger: 'clear'},
						]
					},
					store: new Ext.db.AdapterStore({
						autoLoad: true,
						dbFile: 'Promed.db',
						fields: [
							{name: 'CmpCallType_id', type: 'int'},
							{name: 'CmpCallType_Code', type: 'int'},
							{name: 'CmpCallType_Name', type: 'string'},
							{name: 'CmpCallType_begDate', type: 'date', dateFormat: 'd.m.Y'},
							{name: 'CmpCallType_endDate', type: 'date', dateFormat: 'd.m.Y'}
						],
						key: 'CmpCallType_id',
						sortInfo: {field: 'CmpCallType_Code'},
						tableName: 'CmpCallType'
					}),
					tpl: new Ext.XTemplate(
						'<tpl for="."><div class="x-combo-list-item">',
						'<font color="red">{CmpCallType_Code}</font>&nbsp;{CmpCallType_Name}',
						'</div></tpl>'
					),
					valueField: 'CmpCallType_Name',
					enableKeyEvents: true,
					onTriggerClick: function (a,el) {
						this.setValue();
						var attribut = el.getAttribute('trigger');
						if( attribut == 'list' ){
							if(this.disabled) return; 
							if(this.isExpanded()){
								this.collapse();
								this.el.focus();
							}else {
								this.onFocus({});
								if(this.triggerAction == 'all') {
								    this.doQuery(this.allQuery, true);
								} else {
								    this.doQuery(this.getRawValue());
								}
								this.el.focus();
							}
						}else{
							// this.setValue();
							win.filterRowReq._search();
						}
					},
				})

				
			},
			{dataIndex: 'CmpSecondReason_Name', hidden: true, hideable: false},
			{dataIndex: 'CmpReason_Name', header: 'Повод', width: 100, 
				renderer: function(value, cell, record){
					return record.get('CmpSecondReason_Name') || record.get('CmpReason_Name');
				},
				filter: new Ext.form.TriggerField({
					enableKeyEvents: true,
					name:'CmpReason_Name',
					cls: 'inputClearTextfieldsButton',
					triggerConfig: {
						tag: 'span',
						cls: 'x-field-combo-btns',
						cn: [
							{tag: 'div', cls: 'x-form-trigger clearTextfieldsButton'},
						]
					},
					onTriggerClick: function (e) {
						this.setValue();
						win.filterRowReq._search();
					}
				})
			},
			{dataIndex: 'Adress_Name', header: 'Место вызова', width: 200,
				filter: new Ext.form.TriggerField({
					enableKeyEvents: true,
					name:'Adress_Name',
					cls: 'inputClearTextfieldsButton',
					triggerConfig: {
						tag: 'span',
						cls: 'x-field-combo-btns',
						cn: [
							{tag: 'div', cls: 'x-form-trigger clearTextfieldsButton'},
						]
					},
					onTriggerClick: function (e) {
						this.setValue();
						win.filterRowReq._search();
					}
				})
			},
			{dataIndex: 'CmpLpu_Name', header: 'МО прикрепления', width: 80,
				filter: new Ext.form.TriggerField({
					enableKeyEvents: true,
					name:'CmpLpu_Name',
					cls: 'inputClearTextfieldsButton',
					triggerConfig: {
						tag: 'span',
						cls: 'x-field-combo-btns',
						cn: [
							{tag: 'div', cls: 'x-form-trigger clearTextfieldsButton'},
						]
					},
					onTriggerClick: function (e) {
						this.setValue();
						win.filterRowReq._search();
					}
				})
			},
			{dataIndex: 'SendLpu_Nick', header: 'МО передачи (НМП)', width: 80,
				filter: new Ext.form.TriggerField({
					enableKeyEvents: true,
					name:'SendLpu_Nick',
					cls: 'inputClearTextfieldsButton',
					triggerConfig: {
						tag: 'span',
						cls: 'x-field-combo-btns',
						cn: [
							{tag: 'div', cls: 'x-form-trigger clearTextfieldsButton'},
						]
					},
					onTriggerClick: function (e) {
						this.setValue();
						win.filterRowReq._search();
					}
				})
			},
			{dataIndex: 'Lpu_Nick', header: 'МО госпитализации', width: 80,
				filter: new Ext.form.TriggerField({
					enableKeyEvents: true,
					name:'Lpu_Nick',
					cls: 'inputClearTextfieldsButton',
					triggerConfig: {
						tag: 'span',
						cls: 'x-field-combo-btns',
						cn: [
							{tag: 'div', cls: 'x-form-trigger clearTextfieldsButton'},
						]
					},
					onTriggerClick: function (e) {
						this.setValue();
						win.filterRowReq._search();
					}
				})
			},
			{dataIndex: 'EmergencyTeam_Num', header: '№ бригады', width: 70, 
				type: 'int',
				filter: new Ext.form.TriggerField({
					name:'EmergencyTeam_Num',
					enableKeyEvents: true,
					cls: 'inputClearTextfieldsButton',
					triggerConfig: {
						tag: 'span',
						cls: 'x-field-combo-btns',
						cn: [
							{tag: 'div', cls: 'x-form-trigger clearTextfieldsButton'},
						]
					},
					onTriggerClick: function (e) {
						this.setValue();
						win.filterRowReq._search();
					}
				})
			},
			{dataIndex: 'CmpDiag_Name', header: 'Диагноз СМП', width: 80,
				filter: new Ext.form.TriggerField({
					enableKeyEvents: true,
					name:'CmpDiag_Name',
					cls: 'inputClearTextfieldsButton',
					triggerConfig: {
						tag: 'span',
						cls: 'x-field-combo-btns',
						cn: [
							{tag: 'div', cls: 'x-form-trigger clearTextfieldsButton'},
						]
					},
					onTriggerClick: function (e) {
						this.setValue();
						win.filterRowReq._search();
					}
				})
			},
			{dataIndex: 'StacDiag_Name', header: 'Диагноз стационара', width: 100,
				filter: new Ext.form.TriggerField({
					enableKeyEvents: true,
					name:'StacDiag_Name',
					cls: 'inputClearTextfieldsButton',
					triggerConfig: {
						tag: 'span',
						cls: 'x-field-combo-btns',
						cn: [
							{tag: 'div', cls: 'x-form-trigger clearTextfieldsButton'},
						]
					},
					onTriggerClick: function (e) {
						this.setValue();
						win.filterRowReq._search();
					}
				})
			},
			{dataIndex: 'PPDUser_Name', header: 'Принял', width: 100,
				filter: new Ext.form.TriggerField({
					enableKeyEvents: true,
					name:'PPDUser_Name',
					cls: 'inputClearTextfieldsButton',
					triggerConfig: {
						tag: 'span',
						cls: 'x-field-combo-btns',
						cn: [
							{tag: 'div', cls: 'x-form-trigger clearTextfieldsButton'},
						]
					},
					onTriggerClick: function (e) {
						this.setValue();
						win.filterRowReq._search();
					}
				})
			},
			{dataIndex: 'ServeDT', header: 'Обслужено', width: 100,
				renderer: function (a) {
					if (a) {
						var date = new Date(a);
						if (date) {
							return Ext.util.Format.date(date);
						}
					}
				},
				type: 'date',
				filter: new sw.Promed.SwDateField ({
					xtype: 'swdatefield',
					format: 'd.m.Y',
					enableKeyEvents: true,
					plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
					name: 'ServeDT',
					cls: 'inputClearDatefieldsButton',
					triggerConfig: {
						tag: 'span',
						cls: 'x-field-combo-btns',
						cn: [
							{tag: 'div', cls: 'x-form-trigger x-form-date-trigger showDatefieldButton', trigger: 'date'},
							{tag: 'div', cls: 'x-form-trigger x-form-clear-trigger clearDatefieldsButton', trigger: 'clear'},
						]
					},
					onTriggerClick: function(a,el){
						var attribut = el.getAttribute('trigger');
						if(attribut == 'clear') {
							this.setValue();
							win.filterRowReq._search();
						}else{
							if(this.disabled){
								return;
							}
							if(this.menu == null){
								this.menu = new Ext.menu.DateMenu();
							}
							Ext.apply(this.menu.picker,  {
								minDate : this.minValue,
								maxDate : this.maxValue,
								disabledDatesRE : this.disabledDatesRE,
								disabledDatesText : this.disabledDatesText,
								disabledDays : this.disabledDays,
								disabledDaysText : this.disabledDaysText,
								format : this.format,
								showToday : this.showToday,
								minText : String.format(this.minText, this.formatDate(this.minValue)),
								maxText : String.format(this.maxText, this.formatDate(this.maxValue))
							});
							this.menu.on(Ext.apply({}, this.menuListeners, {
								scope:this
							}));
							this.menu.picker.setValue(this.getValue() || new Date());
							this.menu.show(this.el, "tl-bl?");

						}
					}
				})
			},
			{dataIndex: 'PPDResult', header: 'Комментарии', width: 100,
				renderer: function(v, p, r) {
					if (v === null) {
						return '';
					}
					return '<p title="'+v+'">'+v+'</p>';
				},
				filter: new Ext.form.TriggerField({
					enableKeyEvents: true,
					name:'PPDResult',
					cls: 'inputClearTextfieldsButton',
					triggerConfig: {
						tag: 'span',
						cls: 'x-field-combo-btns',
						cn: [
							{tag: 'div', cls: 'x-form-trigger clearTextfieldsButton'},
						]
					},
					onTriggerClick: function (e) {
						this.setValue();
						win.filterRowReq._search();
					}
				})
			},
			{dataIndex: 'CmpRejectionReason_Name', header: 'Причина отказа'},
			{dataIndex: 'CmpGroup_id',  hidden: true, hideable: false},
			{dataIndex: 'CmpGroupName_id',  hidden: true, hideable: false},
//			{dataIndex: 'Owner', hidden: true, hideable: false, id: 'ownerColumn'},
			{dataIndex: 'CmpCloseCard_id',  hidden: true, hideable: false},
			{dataIndex: 'Lpu_ppdid',  hidden: true, hideable: false},
			{dataIndex: 'CmpCallCard_IsNMP',  hidden: true, hideable: false},
			{dataIndex: 'LpuBuilding_IsPrint',  hidden: true, hideable: false},
			{dataIndex: 'CmpCallCardStatusType_id',  hidden: true, hideable: false},
		],
		storeFields = [];

		for(var i=0; i<gridFields.length; i++) {
			var type = (gridFields[i].type) ? gridFields[i].type : 'string';
			storeFields.push({
				mapping: gridFields[i].dataIndex,
				name: gridFields[i].dataIndex,
				type: type
			});
		}
		
		gridFields.forEach(function(item, i){
			// добавим возможность сортировки по столбцам
			if( !item.hidden){
				item.sortable = true;
			}
		});

		var gridStore = new Ext.data.GroupingStore({
			autoLoad: false,
			sortInfo: {
				field: 'CmpGroupName_id',
				direction: 'ASC'
			},
			groupField: 'CmpGroupName_id',
			reader: new Ext.data.JsonReader({
				totalProperty: 'totalCount',
				root: 'data'
			}, storeFields),
			url: '/?c=CmpCallCard&m=loadSMPAdminWorkPlace'
		});

		this.GridPanel = new Ext.grid.GridPanel({
			firstShow: true,
			stripeRows: true,
			autoExpandColumn: 'autoexpand',
			title: ' - ',
			id: form.id + '_Grid',
			hideTaskTime: true,
			paging: false,
			plugins:[this.filterRowReq],
			keys: [{
				fn: function(inp, e) {
					switch(e.getKey()) {
						case Ext.EventObject.ENTER:
							this.GridPanel.fireEvent('rowdblclick');
							break;
						case Ext.EventObject.DELETE:
							if (keys_enable)
								this.GridPanel.ViewActions.action_delete.execute();
							break;
						case Ext.EventObject.F6:
							this.openPCardHistory();
							break;
						case Ext.EventObject.F10:
							if (keys_enable)
								this.openPersonEdit();
							break;
						case Ext.EventObject.F11:
							this.openPCureHistory();
							break;
					}
				},
				key: [
					Ext.EventObject.ENTER,
					Ext.EventObject.DELETE,
					Ext.EventObject.F6,
					Ext.EventObject.F10,
					Ext.EventObject.F11
				],
				scope: this,
				stopEvent: true
			},
			{
				key: [
					Ext.EventObject.F3
				],
				scope: this,
				stopEvent: true,
				alt: true,
				fn: function(inp, e) {
					switch ( e.getKey() ) {
						case Ext.EventObject.F3:
						{
							this.showAuditWindow();								
							break;
						}
					}
				}
			}
			],
			listeners: {
				render: function() {
					this.contextMenu = new Ext.menu.Menu();
					this.ViewActions = {};
					for(var i=0; i<this.actions.length; i++) {
						this.ViewActions[this.actions[i]['name']] = new Ext.Action(this.actions[i]);
						this.getTopToolbar().add(this.ViewActions[this.actions[i]['name']]);
						this.contextMenu.add(this.ViewActions[this.actions[i]['name']]);
					}
					
					this.getTopToolbar().addFill();
					this.getTopToolbar().addButton({
						disabled: true,
						id: this.id + '_tasktime',
						hidden: typeof this.hideTaskTime != 'undefined' ? this.hideTaskTime : true
					});
				},
				rowcontextmenu: function(grd, num, e) {
					e.stopEvent();
					this.getSelectionModel().selectRow(num);
					this.contextMenu.showAt(e.getXY());
				}
			},
			loadData: function(params) {
				win.filterRowReq.clearFilters();
				with(this.getStore()) {
					removeAll();
					baseParams = params.globalFilters;
					load();
				}
			},
			tbar: new Ext.Toolbar(),
			actions: [
				{name: 'action_add', iconCls: 'add16', text: 'Добавить', tooltip: 'Добавить карту', handler: this.openCmpCallCardEditWindow.createDelegate(this, ['add'])},
				{name: 'action_edit', iconCls: 'edit16', text: 'Изменить', tooltip: 'Изменить карту', handler: this.openCmpCallCardEditWindow.createDelegate(this, ['edit'])},
				{name: 'action_view', iconCls: 'view16', text: 'Просмотр', tooltip: 'Смотреть карту', handler: this.openCmpCallCardEditWindow.createDelegate(this, ['view'])},
				{name: 'action_delete', iconCls: 'delete16', text: 'Удалить', tooltip: 'Удалить карту', handler: this.deleteCmpCallCard.createDelegate(this)},
				{name: 'action_refresh', iconCls: 'refresh16', text: 'Обновить', handler: function(btn) {this.autoEvent = false;this.doSearch();}.createDelegate(this)},
				{name: 'action_print', iconCls: 'print16', text: 'Печать списка', 
					handler: 
						function() { 
							var params = {},
								store = this.GridPanel.getStore();
							params.selections = [];

							store.each(function(row) {
								if(!Ext.isEmpty(row.get('CmpCallCard_id'))) {
									params.selections.push(row);
								}
							})

							Ext.ux.GridPrinter.print(this.GridPanel, params);
						}.createDelegate(this) 
				},
				/*{
					name: 'action_transmit', 
					text: 'Передать диспетчеру направлений', 
					tooltip: 'Передать диспетчеру направлений', 
					handler: this.setStatusCmpCallCard.createDelegate(this, [null, 1]),
					hidden: (getRegionNick() != 'buryatiya')
				},*/
				{name: 'action_closecard', text: 'Закрыть карту вызова', tooltip: 'Закрыть карту вызова', handler: function() {
						var record = this.GridPanel.getSelectionModel().getSelected();
						if(!record) return false;
						if (getRegionNick() == 'ufa' || record.get('EmergencyTeam_Num') != null) {
							this.closeCmpCallCard('add');
						} else {
							this.selectEmergencyTeam(true);
						}
				}.createDelegate(this)},					
//				this.closeCmpCallCard.createDelegate(this, ['add'])},
				{name: 'action_closecardview', text: 'Редактировать карту вызова', tooltip: 'Редактировать карту вызова', handler: this.closeCmpCallCard.createDelegate(this, ['edit'])},
				{name: 'action_printcard', text: 'Печать 110у', tooltip: 'Печать 110у', handler: this.printCmpCallCard.createDelegate(this)},				
				{name: 'action_refuse', text: 'Отказ', tooltip: 'Отказ', handler: this.refuseCmpCallCard.createDelegate(this)},
				{name: 'action_selectlpu', text: 'Передать в НМП', tooltip: 'Выбрать МО для передачи', handler: this.selectLpuTransmit.createDelegate(this) },
				{name: 'action_showAuditWindow', text: 'Аудит записи', tooltip: 'Аудит записи', handler: this.showAuditWindow.createDelegate(this) }/*,
				{name: 'action_to_clinic', text: 'Передать в Поликлинику', tooltip: 'Создать новый вызов врача на дом', handler: this.to_clinic.createDelegate(this) },
				{name: 'action_open', hidden: true, text: 'Открыть (тест)', handler: this.setStatusCmpCallCard.createDelegate(this, [2, null])},
				{name: 'action_close', hidden: true, text: 'Закрыть (тест)', handler: this.setStatusCmpCallCard.createDelegate(this, [1, null])}*/
			],
			loadMask: {msg: 'Загрузка...'},
			region: 'center',
			colModel: new Ext.grid.ColumnModel({
				columns: gridFields
			}),
			view: new Ext.grid.GroupingView({
				//groupTextTpl: '{[values.gvalue + ". " + Ext.getCmp("'+this.id+'").getGroupName(values.gvalue)]} ({[values.rs.length]} {[values.rs.length.inlist([2,3,4]) ? "записи" : "записей"]})'
				enableGroupingMenu:false,
				groupTextTpl: '{[values.gvalue + ". " + Ext.getCmp("'+this.id+'").getGroupName(values.gvalue)]} ({["записей "+Ext.getCmp("'+this.id+'").getRowCount(values.gvalue)]})'
				
			}),
			getAction: function(action) {
				return this.ViewActions[action] || null;
			},
			setParam: function(p, v) {
				this.getStore().baseParams[p] = v;
			},
			store: gridStore
		});

		var keys_enable=true;

		this.GridPanel.getStore().on('load', function(store, rs) {
			if(store.getCount()) {
				this.getSelectionModel().selectFirstRow();
				form.focusSelectedRow();
			}
			var d = !this.getSelectionModel().hasSelection();
			this.getAction('action_edit').setDisabled(d);
			this.getAction('action_view').setDisabled(d);
			this.getAction('action_delete').setDisabled(d);
			//this.getAction('action_transmit').setDisabled(d);
			this.getAction('action_printcard').setDisabled(d);
			this.getAction('action_refuse').setDisabled(d);
			this.getAction('action_closecard').setDisabled(d);
			this.getAction('action_closecardview').setDisabled(d);
			this.getAction('action_selectlpu').setDisabled(d);
			this.getAction('action_showAuditWindow').setDisabled(d);
//			keys_enable = false;
			if( Ext.get(this.getView().getGroupId(7)) != null && this.firstShow ) {
				this.getView().toggleGroup(this.getView().getGroupId(7), false);
				this.firstShow = false;
			}

			form.addEmptyRecord();

			form.refreshTaskTime();

		}.createDelegate(this.GridPanel));
		
		this.GridPanel.getView().getRowClass = function(record, index) {
			if (record.data['CmpCallCard_isLocked']==1) {
				return 'grid-locked-row';
			}
		};
				
		
		this.GridPanel.getSelectionModel().on('rowselect', function(sm, rowIndex, rec) {
//		keys_enable = (rec.get('Owner') == 1); // определяем возможность использования функциональных клавиш			
			this.getAction('action_printcard').setDisabled( !rec.get('CmpGroup_id').inlist([10]) || rec.get('CmpCallCard_id') == null || rec.get('CmpCloseCard_id') == null);			
			//this.getAction('action_transmit').setDisabled(rec.get('CmpCallCard_isDeleted') == 2 || rec.get('CmpGroup_id') == 10 || rec.get('CmpCallCard_id') == null|| rec.get('CmpCallCard_isLocked')==1);
			this.getAction('action_refuse').setDisabled(rec.get('CmpCallCard_isDeleted') == 2 || rec.get('CmpGroup_id') == 10 || rec.get('CmpCallCard_id') == null|| rec.get('CmpCallCard_isLocked')==1);
			this.getAction('action_closecard').setDisabled(rec.get('CmpCallCard_isDeleted') == 2 || (rec.get('CmpGroup_id') == 10) || rec.get('CmpCloseCard_id') > 0 || rec.get('CmpCallCard_id') == null || rec.get('CmpCallCard_isLocked')==1 || !rec.get('CmpCallCardStatusType_id').inlist([1,2,4]));
			this.getAction('action_closecardview').setDisabled(rec.get('CmpCallCard_isDeleted') == 2 || (!rec.get('CmpCloseCard_id') > 0) || rec.get('CmpCallCard_id') == null|| rec.get('CmpCallCard_isLocked')==1);
			this.getAction('action_edit').setDisabled(rec.get('CmpCallCard_isDeleted') == 2 || rec.get('CmpCallCard_id') == null || rec.get('CmpCallCard_isLocked')==1);
	    	this.getAction('action_delete').setDisabled(rec.get('CmpCallCard_isDeleted') == 2 || rec.get('CmpCallCard_id') == null || rec.get('CmpCallCard_isLocked')==1);
//			this.getAction('action_to_clinic').setDisabled( rec.get('CmpCallCard_id') == null || rec.get('CmpCallCard_isLocked')==1);
			this.getAction('action_selectlpu').setDisabled(!rec.get('Person_id') || rec.get('CmpCallCard_isDeleted') == 2 || !rec.get('CmpGroup_id').inlist([2,7]) || rec.get('CmpCallCard_id') == null || (!getRegionNick().inlist(['astra']) && (rec.get('Person_id') == null || rec.get('Person_Age') == 0)));
		}.createDelegate(this.GridPanel));
		
		this.GridPanel.on('rowdblclick', function() {
			var noedit = this.getAction('action_edit').isDisabled();
			this.getAction( noedit ? 'action_view' : 'action_edit' ).execute();
		});

		this.displayDeletedCards = {
			name: 'displayDeletedCards',
			id: 'displayDeletedCards',
			labelSeparator: '',
			boxLabel: 'Показывать удаленные вызовы',
			xtype: 'checkbox',
			listeners: {
				check: function() {
					form.doSearch();
				}.createDelegate(form)
			}
		};

		this.BtnClearCards = 
		{
			style: "padding-left: 10px",
			xtype: 'button',
			id: form.id + 'BtnClear',
			text: 'Сброс',
			iconCls: 'reset16',
			handler: function() {
				form.doReset2();
			}.createDelegate(form)
		};

		this.BtnSearchCards = 
		{
			style: "padding-left: 10px",
			xtype: 'button',
			id: form.id + 'BtnSearch',
			text: 'Найти',
			iconCls: 'search16',
			handler: function() {
				form.doSearch();
			}.createDelegate(form)
		};

		this.readCards = 
		{
			style: "padding-left: 10px",
			xtype: 'button',
			text: 'Считать с карты',
			iconCls: 'idcard16',
			handler: function()
			{
				form.readFromCard();
			}
		};

		this.tbseparator = {
			xtype : "tbseparator"
		}

		this.tbfill = {
			xtype: 'tbfill'
		}

		this.redefinedWindowToolbar = [
			'prev',
			'tbseparator',
			'dateMenu',
			'tbseparator',
			'next',
			'tbseparator',
			'diffDiagView',
			'tbseparator',
			'comboHourSelect',
			'tbseparator',
			'tbseparator',
			'day', 
			'week', 
			'month',
			'tbseparator',
			'displayDeletedCards',
			'tbseparator',
			'tbfill',
			'dispatchCallSelect',
			'LpuBuildingSelect',
			'emergencyTeamCombo',
			'BtnClearCards',
			'readCards'
		]

		if (getGlobalOptions().region.nick !== 'buryatiya') {
			this.buttonPanelActions.action_Spr = {
				nn: 'action_Spr',
				tooltip: 'Справочники',
				text: 'Справочники',
				iconCls : 'book32',
				disabled: false,
				menuAlign: 'tr',
				menu: new Ext.menu.Menu({
					items: [
						sw.Promed.Actions.swMESOldAction,
						'-',
						sw.Promed.Actions.SprRlsAction,
						sw.Promed.Actions.swDrugDocumentSprAction,
						'-',
						{
							text: 'ЕРМП',
							tooltip: 'ЕРМП',
							iconCls: '',
							hidden: true, // getRegionNick() == 'perm',
							menu: new Ext.menu.Menu({
								items: [
									sw.Promed.Actions.SprPostAction,
									sw.Promed.Actions.SprSkipPaymentReasonAction,
									sw.Promed.Actions.SprWorkModeAction,
									sw.Promed.Actions.SprSpecialityAction,
									sw.Promed.Actions.SprDiplomaSpecialityAction,
									sw.Promed.Actions.SprLeaveRecordTypeAction,
									sw.Promed.Actions.SprEducationTypeAction,
									sw.Promed.Actions.SprEducationInstitutionAction
								]
							})
						},
						/*{
							text: 'СМП справочник срочности вызова',
							tooltip: 'СМП справочник срочности вызова',
							iconCls: 'glossary16',
							handler: function(){
								getWnd('swSmpCallRangeGlossaryWindow').show();
							}
						}*/
					]
				})
			}
		};
		
		sw.Promed.swWorkPlaceSMPAdminWindow.superclass.initComponent.apply(this, arguments);
	}
});
