/**
* swHIVRegistryWindow - Регистр ВИЧ-инфицированных
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       A.Markoff <markov@swan.perm.ru> & Alexander Permyakov
* @version      2012/10
* @comment      Префикс для id компонентов HIVRW (HIVRegistryWindow)
*
*/
sw.Promed.swHIVRegistryWindow = Ext.extend(sw.Promed.BaseForm, {codeRefresh: true,
	buttonAlign: 'left',
	closable: true,
	closeAction: 'hide',
	collapsible: true,
	getButtonSearch: function() {
		return Ext.getCmp('HIVRW_SearchButton');
	},
	doReset: function() {
		
		var base_form = this.getFilterForm().getForm();
		base_form.reset();
		this.HIVRegistrySearchFrame.ViewActions.open_emk.setDisabled(true);
		this.HIVRegistrySearchFrame.ViewActions.person_register_dis.setDisabled(true);
		this.HIVRegistrySearchFrame.ViewActions.action_view.setDisabled(true);
		this.HIVRegistrySearchFrame.ViewActions.action_delete.setDisabled(true);
		this.HIVRegistrySearchFrame.ViewActions.action_refresh.setDisabled(true);
		this.HIVRegistrySearchFrame.getGrid().getStore().removeAll();
		this.HIVRegistrySearchFrame.getGrid().getViewFrame().removeAll({clearAll: true});

		var el;
		el = base_form.findField('PersonRegisterType_id');// Регистр - Тип записи регистра - Все
		if(typeof(el) !== 'undefined') el.setValue(1);
		el = base_form.findField('PrivilegeStateType_id');// Льгота - Актуальность льготы - 1. Действующие льготы
		if(typeof(el) !== 'undefined') el.setValue(1);
		el = base_form.findField('PersonCardStateType_id');// Прикрепление - Актуальность прикр-я - 1. Актуальные прикрепления
		if(typeof(el) !== 'undefined') el.setValue(1);
		el = base_form.findField('AddressStateType_id');// Адрес - Тип адреса - 1. Адрес регистрации
		if(typeof(el) !== 'undefined') el.setValue(1);
	},
	doSearch: function(params) {
		
		if (typeof params != 'object') {
			params = {};
		}
		
		var base_form = this.getFilterForm().getForm();
		
		/*if ( !params.firstLoad && this.getFilterForm().isEmpty() ) {
			sw.swMsg.alert(langs('Ошибка'), langs('Не заполнено ни одно поле'), function() {
			});
			return false;
		}*/
		
		var grid = this.HIVRegistrySearchFrame.getGrid();

		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					//
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		if ( base_form.findField('PersonPeriodicType_id').getValue().toString().inlist([ '2', '3' ]) && (typeof params != 'object' || !params.ignorePersonPeriodicType ) ) {
			sw.swMsg.show({
				buttons: Ext.Msg.YESNO,
				fn: function(buttonId, text, obj) {
					if ( buttonId == 'yes' ) {
						this.doSearch({
							ignorePersonPeriodicType: true
						});
					}
				}.createDelegate(this),
				icon: Ext.MessageBox.QUESTION,
				msg: langs('Выбран тип поиска человека ') + (base_form.findField('PersonPeriodicType_id').getValue() == 2 ? langs('по состоянию на момент случая') : langs('По всем периодикам')) + langs('.<br />При выбранном варианте поиск работает <b>значительно</b> медленнее.<br />Хотите продолжить поиск?'),
				title: langs('Предупреждение')
			});
			return false;
		}

		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет поиск..."});
		loadMask.show();

		var post = getAllFormFieldValues(this.getFilterForm());
		//post.DispLpu_id = base_form.findField('DispLpu_id').getValue();

		post.limit = 100;
		post.start = 0;
		
		//log(post);

		if ( base_form.isValid() ) {
			this.HIVRegistrySearchFrame.ViewActions.action_refresh.setDisabled(false);
			grid.getStore().removeAll();
			grid.getStore().load({
				callback: function(records, options, success) {
					loadMask.hide();
				},
				params: post
			});
		}
		
	},
	getRecordsCount: function() {
		var st = this.HIVRegistrySearchFrame.getGrid().getStore();
		var noLines = false;
		if(st.totalLength == 0){
			noLines = true;
		}else if(st.totalLength == 1){
			if(typeof(st.getAt(0)) == 'undefined'){// бывает после нажатия "Обновить"
				noLines = true;
			}else if(! st.getAt(0).get('PersonRegister_id')){// если запись пустая
				noLines = true;
			}
		}
		if(noLines){
			sw.swMsg.alert('Подсчет записей', 'Найдено записей: 0');
			return;
		}

		var base_form = this.getFilterForm().getForm();

		if ( !base_form.isValid() ) {
			sw.swMsg.alert(langs('Поиск'), langs('Проверьте правильность заполнения полей на форме поиска'));
			return false;
		}

		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет подсчет записей..."});
		loadMask.show();

		var post = getAllFormFieldValues(this.getFilterForm());

		if ( post.PersonPeriodicType_id == null ) {
			post.PersonPeriodicType_id = 1;
		}

		Ext.Ajax.request({
			callback: function(options, success, response) {
				loadMask.hide();
				if ( success ) {
					var response_obj = Ext.util.JSON.decode(response.responseText);

					if ( response_obj.Records_Count != undefined ) {
						sw.swMsg.alert(langs('Подсчет записей'), langs('Найдено записей: ') + response_obj.Records_Count);
					}
					else {
						sw.swMsg.alert(langs('Подсчет записей'), response_obj.Error_Msg);
					}
				}
				else {
					sw.swMsg.alert(langs('Ошибка'), langs('При подсчете количества записей произошли ошибки'));
				}
			},
			params: post,
			url: C_SEARCH_RECCNT
		});
	},
	height: 550,
	openWindow: function(action) {
		if (!action || !action.toString().inlist(['registry_export','person_register_dis','add','view','edit'])) {
			return false;
		}
		var cur_win = this;
		var form = this.getFilterForm().getForm();
		var grid = this.HIVRegistrySearchFrame.getGrid();
		if (!grid.getSelectionModel().getSelected() && action!='add') {
			return false;
		}
		var selected_record = grid.getSelectionModel().getSelected();

		var params = new Object();
		params.userMedStaffFact = this.userMedStaffFact;
		params.action = action;
		params.callback = function(data) {
			grid.getStore().reload();
		};
		params.onHide = function() {
			grid.getView().focusRow(grid.getStore().indexOf(selected_record));
		};

		switch(action) {
			case 'person_register_dis':
				sw.Promed.personRegister.out({
					PersonRegister_id: selected_record.get('PersonRegister_id')
					,MorbusType_SysNick: 'hiv'
					,Person_id: selected_record.get('Person_id')
					,Diag_Name: selected_record.get('Diag_Name')
					,PersonRegister_setDate: selected_record.get('PersonRegister_setDate')
					,callback: function(data) {
						grid.getStore().reload();
					}
				});
				break;
			case 'add':
				sw.Promed.personRegister.add({
                    MorbusType_SysNick: 'hiv' // Регистр ВИЧ-инфицированных
                    ,viewOnly: (cur_win.editType=='onlyRegister')?true:false
					,callback: function(data) {
						form.findField('Person_Firname').setValue(data.Person_Firname);
						form.findField('Person_Secname').setValue(data.Person_Secname);
						form.findField('Person_Surname').setValue(data.Person_Surname);
						form.findField('Person_Birthday').setValue(data.Person_Birthday);
						cur_win.doSearch();
						
						//открывает окно для редактирования специфики
						var params = new Object();
						params.userMedStaffFact = cur_win.userMedStaffFact;
						params.action = 'view';
						params.callback = Ext.emptyFn;
						params.onHide = function() {
							grid.getView().focusRow(0);
						};
						if (getWnd('swMorbusHIVWindow').isVisible()) {
							getWnd('swMorbusHIVWindow').hide();
						}
						params.allowSpecificEdit = true;
						params.PersonRegister_id = data.PersonRegister_id;
						params.Person_id = data.Person_id;
						params.PersonEvn_id = data.PersonEvn_id;
						params.Server_id = data.Server_id;
						getWnd('swMorbusHIVWindow').show(params);
					},
					searchMode: getRegionNick().inlist(['astra','kaluga'])?'encryponly':'all'
				});
				break;
            case 'edit':
			case 'view':
				if (getWnd('swMorbusHIVWindow').isVisible()) {
					getWnd('swMorbusHIVWindow').hide();
				}
				if ( Ext.isEmpty(selected_record.get('Morbus_id')) ) {
					sw.swMsg.alert(langs('Сообщение'), langs('Заболевание на человека не заведено'));
					return false;
				}
				params.onHide = function(isChange) {
					if(isChange) {
						grid.getStore().reload();
					} else {
						grid.getView().focusRow(grid.getStore().indexOf(selected_record));
					}
				};

				params.allowSpecificEdit = ('edit' == action);
				params.callback = Ext.emptyFn;
				params.PersonRegister_id = selected_record.data.PersonRegister_id;
				params.Person_id = selected_record.data.Person_id;
				params.PersonEvn_id = selected_record.data.PersonEvn_id;
				params.Server_id = selected_record.data.Server_id;
				params.editType = cur_win.editType;
				params.action = cur_win.HIVRegistrySearchFrame.getAction('action_edit').isHidden()?'view':'edit';
				getWnd('swMorbusHIVWindow').show(params);
				break;
			
			case 'registry_export':
				if ( Ext.isEmpty(selected_record.get('Morbus_id')) ) {
					sw.swMsg.alert(langs('Сообщение'), langs('Заболевание на человека не заведено'));
					return false;
				}
				params.callback = Ext.emptyFn;
				params.PersonRegister_id = selected_record.data.PersonRegister_id;
				params.Person_id = selected_record.data.Person_id;
				getWnd('swRegistryExportWindow').show(params);
				break;				
		}
		

		
	},
	initComponent: function() {
		
		this.HIVRegistrySearchFrame = new sw.Promed.ViewFrame({
			actions: [
                {name: 'action_add', handler: function() { this.openWindow('add'); }.createDelegate(this)},
                {name: 'action_edit', handler: function() { this.openWindow('edit'); }.createDelegate(this)},
                {name: 'action_view', handler: function() { this.openWindow('view'); }.createDelegate(this)},
				{name: 'action_delete', handler: this.deletePersonRegister.createDelegate(this)},
				{name: 'action_refresh'},
				{name: 'action_print' }
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 150,
			autoLoadData: false,
			dataUrl: C_SEARCH,
			id: 'HIVRW_HIVRegistrySearchGrid',
			object: 'HIVRegistry',
			pageSize: 100,
			paging: true,
			region: 'center',
			root: 'data',
			stringfields: [
				{name: 'PersonRegister_id', type: 'int', header: 'ID', key: true},
				{name: 'EvnNotifyBase_id', type: 'int', hidden: true},
				{name: 'MedPersonal_iid', type: 'int', hidden: true},
				{name: 'Lpu_iid', type: 'int', hidden: true},
				{name: 'Diag_id', type: 'int', hidden: true},
				{name: 'MorbusHIV_id', type: 'int', hidden: true},
				{name: 'Morbus_id', type: 'int', hidden: true},
				{name: 'Person_id', type: 'int', hidden: true},
				{name: 'PersonEvn_id', type: 'int', hidden: true},
				{name: 'Server_id', type: 'int', hidden: true},
				{name: 'Person_Surname', type: 'string', header: langs('Фамилия'), width: 150},
				{name: 'Person_Firname', type: 'string', header: langs('Имя'), width: 150},
				{name: 'Person_Secname', type: 'string', header: langs('Отчество'), width: 150},
				{name: 'Person_Birthday', type: 'date', format: 'd.m.Y', header: langs('Дата рождения'), width: 100},
                {name: 'MorbusHIV_NumImmun', type: 'int', header: langs('№ иммуноблота'), width: 100},
                {name: 'Lpu_Nick', type: 'string', header: langs('ЛПУ прикр.'), width: 150},
				{name: 'Diag_Name', type: 'string', header: langs('Диагноз МКБ-10'), width: 150, id: 'autoexpand'},
				{name: 'PersonRegister_setDate', type: 'date', format: 'd.m.Y', header: langs('Дата включения в регистр'), width: 160},
				{name: 'PersonRegister_disDate', type: 'date', format: 'd.m.Y', header: langs('Дата исключения из регистра'), width: 170}
				,{name: 'PersonRegisterOutCause_id', type: 'int', hidden: true}
				,{name: 'PersonRegisterOutCause_Name', type: 'string', header: langs('Причина исключения из регистра'), width: 190}
			],
			toolbar: true,
			totalProperty: 'totalCount', 
			onBeforeLoadData: function() {
				this.getButtonSearch().disable();
			}.createDelegate(this),
			onLoadData: function() {
				this.getButtonSearch().enable();
			}.createDelegate(this),
			onRowSelect: function(sm,index,record) {
				this.getAction('open_emk').setDisabled( false );
				this.getAction('person_register_dis').setDisabled( Ext.isEmpty(record.get('PersonRegister_disDate')) == false );
                this.getAction('action_delete').setDisabled( Ext.isEmpty(record.get('PersonRegister_id')) );
                this.getAction('action_edit').setDisabled( Ext.isEmpty(record.get('Morbus_id')) || Ext.isEmpty(record.get('PersonRegister_disDate')) == false );
				this.getAction('action_view').setDisabled( Ext.isEmpty(record.get('Morbus_id')) );
			},
			onDblClick: function(sm,index,record) {
				this.getAction('action_view').execute();
			}
		});

		this.SearchFilters = getBaseSearchFiltersFrame({
			allowPersonPeriodicSelect: true,
			id: 'HIVRegistryFilterForm',
			labelWidth: 130,
			ownerWindow: this,
			searchFormType: 'HIVRegistry',
			tabIndexBase: TABINDEX_HIVRW,
			tabPanelHeight: 240,
			tabPanelId: 'HIVRW_SearchFilterTabbar',
			tabs: [{
				autoHeight: true,
				bodyStyle: 'margin-top: 5px;',
				border: false,
				labelWidth: 220,
				layout: 'form',
				listeners: {
					'activate': function(panel) {
						var form = this.getFilterForm().getForm();
						form.findField('PersonRegisterType_id').focus(250, true);
					}.createDelegate(this)
				},
				title: langs('<u>6</u>. Регистр'),
				items: [{
					xtype: 'swpersonregistertypecombo',
					hiddenName: 'PersonRegisterType_id',
					fieldLabel: langs('Тип записи регистра'),
					width: 200
				}, {
					fieldLabel: langs('Дата включения в регистр'),
					name: 'PersonRegister_setDate_Range',
					plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
					width: 180,
					xtype: 'daterangefield'
				}, {
					fieldLabel: langs('Дата исключения из регистра'),
					name: 'PersonRegister_disDate_Range',
					plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
					width: 180,
					xtype: 'daterangefield'
				}]
			}, {
				autoHeight: true,
				bodyStyle: 'margin-top: 5px;',
				border: false,
				layout: 'form',
				labelWidth: 180,
				listeners: {
					'activate': function(panel) {
						this.getFilterForm().getForm().findField('Diag_Code_From').focus(250, true);
					}.createDelegate(this)
				},
				title: langs('<u>7</u>. Диагнозы'),
				items: [{
					fieldLabel: langs('Диагноз с'),
					hiddenName: 'Diag_Code_From',
					valueField: 'Diag_Code',
					width: 450,
                    MorbusType_SysNick: 'hiv',
					xtype: 'swdiagcombo'
                },{
                    fieldLabel: langs('по'),
                    hiddenName: 'Diag_Code_To',
                    valueField: 'Diag_Code',
                    width: 450,
                    MorbusType_SysNick: 'hiv',
                    xtype: 'swdiagcombo'
                },{
                    fieldLabel: langs('№ иммуноблота'),
                    name: 'MorbusHIV_NumImmun',
                    width: 60,
                    allowDecimals: false,
                    allowNegative: false,
                    xtype: 'numberfield'
				/*
				}, {
					fieldLabel: langs('ЛПУ, в которой пациенту впервые установлен диагноз орфанного заболевания'),
					hiddenName: 'Lpu_sid',//Lpu_oid 
					listWidth: 620,
					width: 350,
					xtype: 'swlpucombo'
					*/
				}]
			}]
		});

		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.doSearch();
				}.createDelegate(this),
				iconCls: 'search16',
				tabIndex: TABINDEX_HIVRW + 120,
				id: 'HIVRW_SearchButton',
				text: BTN_FRMSEARCH
			}, {
				handler: function() {
					this.doReset();
					this.doSearch({firstLoad: true});
				}.createDelegate(this),
				iconCls: 'resetsearch16',
				tabIndex: TABINDEX_HIVRW + 121,
				text: BTN_FRMRESET
			}, /*{
				handler: function() {
					this.HIVRegistrySearchFrame.printRecords();
				}.createDelegate(this),
				iconCls: 'print16',
				tabIndex: TABINDEX_HIVRW + 122,
				text: langs('Печать списка')
			},*/ {
				handler: function() {
					this.getRecordsCount();
				}.createDelegate(this),
				// iconCls: 'resetsearch16',
				tabIndex: TABINDEX_HIVRW + 123,
				text: BTN_FRMCOUNT
			}, {
				text: '-'
			},
			HelpButton(this, -1),
			{
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'cancel16',
				onShiftTabAction: function() {
					this.buttons[this.buttons.length - 2].focus();
				}.createDelegate(this),
				onTabAction: function() {
					this.findById('HIVRW_SearchFilterTabbar').getActiveTab().fireEvent('activate', this.findById('HIVRW_SearchFilterTabbar').getActiveTab());
				}.createDelegate(this),
				tabIndex: TABINDEX_HIVRW + 124,
				text: BTN_FRMCLOSE
			}],
			getFilterForm: function() {
				if ( this.filterForm == undefined ) {
					this.filterForm = this.findById('HIVRegistryFilterForm');
				}
				return this.filterForm;
			},
			items: [ this.SearchFilters, this.HIVRegistrySearchFrame]
		});

		sw.Promed.swHIVRegistryWindow.superclass.initComponent.apply(this, arguments);
		
	},
	layout: 'border',
	listeners: {
		'hide': function(win) {
			win.doReset();
		},
		'maximize': function(win) {
			win.getFilterForm().doLayout();
		},
		'restore': function(win) {
			win.getFilterForm().doLayout();
		},
        'resize': function (win, nW, nH, oW, oH) {
			win.findById('HIVRW_SearchFilterTabbar').setWidth(nW - 5);
			win.getFilterForm().setWidth(nW - 5);
		}
	},
	maximizable: true,
	minHeight: 550,
	minWidth: 800,
	modal: false,
	plain: true,
	resizable: true,
	show: function() {
		sw.Promed.swHIVRegistryWindow.superclass.show.apply(this, arguments);
		var win = this;
		
		/*this.HIVRegistrySearchFrame.addActions({
			name:'action_export', 
			text:langs('Выгрузка в федеральный регистр'), 
			tooltip: langs('Выгрузка в федеральный регистр'),
			iconCls: '',
			disabled: false,
			handler: function() {
				this.openWindow('registry_export');
			}.createDelegate(this)
		});*/
		
		this.HIVRegistrySearchFrame.addActions({
			name:'person_register_dis', 
			text:langs('Исключить из регистра'), 
			tooltip: langs('Исключить из регистра'),
			iconCls: 'pers-disp16',
			handler: function() {
				this.openWindow('person_register_dis');
			}.createDelegate(this)
		});
		
		this.HIVRegistrySearchFrame.addActions({
			name:'open_emk', 
			text:langs('Открыть ЭМК'), 
			tooltip: langs('Открыть электронную медицинскую карту пациента'),
			iconCls: 'open16',
			handler: function() {
				this.emkOpen();
			}.createDelegate(this)
		});

		if(!getWnd('swWorkPlaceMZSpecWindow').isVisible() && !isUserGroup('MIACSuperAdmin')){
			var minzdrav = getGlobalOptions().isMinZdrav;
		    if(!minzdrav && getGlobalOptions().region.nick != 'perm' && !(arguments[0] && arguments[0].fromARM && arguments[0].fromARM !== null && arguments[0].fromARM.inlist(['minzdravdlo','spec_mz','mzchieffreelancer']))){
				if (String(getGlobalOptions().groups).indexOf('HIV', 0) < 0)
				{
					sw.swMsg.alert('Сообщение', 'Форма "'+ win.title +'" доступна только для пользователей, с указанной группой «Регистр по ВИЧ»');
					win.hide();
					return false;
				}
		    }
	    }
		
		var base_form = this.getFilterForm().getForm();

		this.restore();
		this.center();
		this.maximize();
		this.doReset();
		//this.findById('HIVRW_SearchFilterTabbar').setActiveTab(0);
		if (arguments[0].userMedStaffFact)
		{
			this.userMedStaffFact = arguments[0].userMedStaffFact;
		} else {
			if (sw.Promed.MedStaffFactByUser.last)
			{
				this.userMedStaffFact = sw.Promed.MedStaffFactByUser.last;
			}
			else
			{
				sw.Promed.MedStaffFactByUser.selectARM({
					ARMType: arguments[0].ARMType,
					onSelect: function(data) {
						this.userMedStaffFact = data;
					}.createDelegate(this)
				});
			}
		}
		this.editType = 'all';
		if(arguments[0] && arguments[0].editType)
		{
			this.editType = arguments[0].editType;
		}

		if(String(getGlobalOptions().groups).indexOf('HIV', 0) < 0){
			this.HIVRegistrySearchFrame.setActionHidden('action_add', true);
			this.HIVRegistrySearchFrame.setActionHidden('action_delete', true);
			this.HIVRegistrySearchFrame.setActionHidden('action_edit', true);	
			this.HIVRegistrySearchFrame.setActionHidden('person_register_dis', true);		
		}
		var minzdrav = getGlobalOptions().isMinZdrav;
		if(getRegionNick() != 'kareliya')
			base_form.findField('AttachLpu_id').setValue(getGlobalOptions().lpu_id);
		if ( String(getGlobalOptions().groups).indexOf('HIV', 0) >= 0 ||minzdrav) {
			base_form.findField('AttachLpu_id').setDisabled(false);
		} else {
			base_form.findField('AttachLpu_id').setDisabled(true);
		}
		
		this.doLayout();
		
		base_form.findField('PersonRegisterType_id').setValue(1);
		this.doSearch({firstLoad: true});
	},
	emkOpen: function()
	{
		var grid = this.HIVRegistrySearchFrame.getGrid();

		if ( !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('Person_id') )
		{
			Ext.Msg.alert(langs('Ошибка'), langs('Не выбрана запись!'));
			return false;
		}
		var record = grid.getSelectionModel().getSelected();
		
		getWnd('swPersonEmkWindow').show({
			Person_id: record.get('Person_id'),
			Server_id: record.get('Server_id'),
			PersonEvn_id: record.get('PersonEvn_id'),
			userMedStaffFact: this.userMedStaffFact,
			MedStaffFact_id: this.userMedStaffFact.MedStaffFact_id,
			LpuSection_id: this.userMedStaffFact.LpuSection_id,
			readOnly: (this.editType == 'onlyRegister')?true:false,
			ARMType: 'common',
			callback: function()
			{
				//
			}.createDelegate(this)
		});
	},
	deletePersonRegister: function() {
		var grid = this.HIVRegistrySearchFrame.getGrid();
		if ( !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('Person_id') )
		{
			Ext.Msg.alert(langs('Ошибка'), langs('Не выбрана запись!'));
			return false;
		}
		var record = grid.getSelectionModel().getSelected();
		
		Ext.Msg.show({
			title: langs('Вопрос'),
			msg: langs('Удалить выбранную запись регистра?'),
			buttons: Ext.Msg.YESNO,
			fn: function(btn) {
				if (btn === 'yes') {
					this.getLoadMask(langs('Удаление...')).show();
					Ext.Ajax.request({
						url: '/?c=PersonRegister&m=delete',
						params: {
							PersonRegister_id: record.get('PersonRegister_id')
						},
						callback: function(options, success, response) {
							this.getLoadMask().hide();
							if (success) {	
								var obj = Ext.util.JSON.decode(response.responseText);
								if( obj.success )
									grid.getStore().remove(record);
							} else {
								sw.swMsg.alert(langs('Ошибка'), langs('Ошибка при удалении записи регистра!'));
							}
						}.createDelegate(this)
					});
				}
			}.createDelegate(this),
			icon: Ext.MessageBox.QUESTION
		});
	},
	title: langs('Регистр ВИЧ-инфицированных'),
	width: 800
});

