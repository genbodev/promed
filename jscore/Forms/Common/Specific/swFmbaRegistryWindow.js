/**
* swFmbaRegistryWindow - Регистр ФМБА
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2016 Swan Ltd.
* @author       Alexander Kurakin
* @version      2016
* @comment      Префикс для id компонентов FRW (FmbaRegistryWindow)
*
*/
sw.Promed.swFmbaRegistryWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	buttonAlign: 'left',
	closable: true,
	closeAction: 'hide',
	collapsible: true,
	getButtonSearch: function() {
		return Ext.getCmp('FRW_SearchButton');
	},
	doReset: function() {
		
		var base_form = this.findById('FmbaRegistryFilterForm').getForm();
		base_form.reset();
		this.FmbaRegistrySearchFrame.ViewActions.person_register_dis.setDisabled(true);
		this.FmbaRegistrySearchFrame.ViewActions.action_view.setDisabled(true);
		this.FmbaRegistrySearchFrame.ViewActions.action_delete.setDisabled(true);
		this.FmbaRegistrySearchFrame.ViewActions.action_refresh.setDisabled(true);
		this.FmbaRegistrySearchFrame.getGrid().getStore().removeAll();
		this.FmbaRegistrySearchFrame.getGrid().getViewFrame().removeAll();
	},
	doSearch: function(params) {
		
		if (typeof params != 'object') {
			params = {};
		}
		
		var base_form = this.findById('FmbaRegistryFilterForm').getForm();
		
		if ( !params.firstLoad && this.findById('FmbaRegistryFilterForm').isEmpty() ) {
			sw.swMsg.alert(lang['oshibka'], lang['ne_zapolneno_ni_odno_pole'], function() {
			});
			return false;
		}
		
		var grid = this.FmbaRegistrySearchFrame.getGrid();

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
				msg: 'Выбран тип поиска человека ' + (base_form.findField('PersonPeriodicType_id').getValue() == 2 ? 'по состоянию на момент случая' : 'по всем периодикам') + '.<br />При выбранном варианте поиск работает <b>значительно</b> медленнее.<br />Хотите продолжить поиск?',
				title: lang['preduprejdenie']
			});
			return false;
		}

		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет поиск..."});
		loadMask.show();

		var post = getAllFormFieldValues(this.findById('FmbaRegistryFilterForm'));
		//post.DispLpu_id = base_form.findField('DispLpu_id').getValue();

		post.limit = 100;
		post.start = 0;
		
		//log(post);

		if ( base_form.isValid() ) {
			this.FmbaRegistrySearchFrame.ViewActions.action_refresh.setDisabled(false);
			grid.getStore().removeAll();
			grid.getStore().load({
				callback: function(records, options, success) {
					loadMask.hide();
				},
				params: post
			});
		}
		
	},
	height: 550,
	getRecordsCount: function() {
		var st = this.FmbaRegistrySearchFrame.getGrid().getStore();
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
			sw.swMsg.alert(lang['poisk'], lang['proverte_pravilnost_zapolneniya_poley_na_forme_poiska']);
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
						sw.swMsg.alert(lang['podschet_zapisey'], lang['naydeno_zapisey'] + response_obj.Records_Count);
					}
					else {
						sw.swMsg.alert(lang['podschet_zapisey'], response_obj.Error_Msg);
					}
				}
				else {
					sw.swMsg.alert(lang['oshibka'], lang['pri_podschete_kolichestva_zapisey_proizoshli_oshibki']);
				}
			},
			params: post,
			url: C_SEARCH_RECCNT
		});
	},
	openWindow: function(action) {
		if (!action || !action.toString().inlist(['person_register_dis','add','view'])) {
			return false;
		}
		var cur_win = this;
		var form = this.getFilterForm().getForm();
		var grid = this.FmbaRegistrySearchFrame.getGrid();
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
					,MorbusType_SysNick: 'fmba'
					,PersonRegisterType_SysNick: 'fmba'
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
                    PersonRegisterType_SysNick: 'fmba'
                    ,MorbusType_SysNick: 'fmba'
                    ,registryType: 'fmba'
					,searchMode: 'fmba'
					,callback: function(data) {
						form.findField('Person_Firname').setValue(data.Person_Firname);
						form.findField('Person_Secname').setValue(data.Person_Secname);
						form.findField('Person_Surname').setValue(data.Person_Surname);
						form.findField('Person_Birthday').setValue(data.Person_Birthday);
						cur_win.doSearch();
					}
				});
				break;
			
			case 'view':
				if (getWnd('swPersonRegisterFmbaEditWindow').isVisible()) {
					getWnd('swPersonRegisterFmbaEditWindow').hide();
				}
				if ( Ext.isEmpty(selected_record.get('PersonRegister_id')) ) {
					sw.swMsg.alert(lang['soobschenie'], lang['oshibka_vyibora_zapisi']);
					return false;
				}
				params.onHide = function(isChange) {
					if (isChange) {
						grid.getStore().reload();
					} else {
						grid.getView().focusRow(grid.getStore().indexOf(selected_record));
					}
				};

				params.allowSpecificEdit = ('edit' == action);
				params.callback = Ext.emptyFn;
				params.PersonRegister_id = selected_record.data.PersonRegister_id;
				params.Person_id = selected_record.data.Person_id;
				params.MorbusType_SysNick = selected_record.data.MorbusType_SysNick;// для фильтрации диагнозов по той же нозологии
				getWnd('swPersonRegisterFmbaEditWindow').show(params);
				break;				
		}
		

		
	},
	initComponent: function() {
		
		var FmbaRegistry = ( String(getGlobalOptions().groups).indexOf('FmbaRegistry', 0) >= 0 );
		
		this.FmbaRegistrySearchFrame = new sw.Promed.ViewFrame({
			actions: [
				{name: 'action_add', handler: function() { this.openWindow('add'); }.createDelegate(this)},
				{name: 'action_edit', hidden: true},
				{name: 'action_view', handler: function() { this.openWindow('view'); }.createDelegate(this)},
				{name: 'action_delete', handler: this.deletePersonRegister.createDelegate(this)},
				{name: 'action_refresh'},
				{name: 'action_print', hidden: true}
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 150,
			autoLoadData: false,
			dataUrl: C_SEARCH,
			id: 'FRW_FmbaRegistrySearchGrid',
			object: 'FmbaRegistry',
			pageSize: 100,
			paging: true,
			region: 'center',
			root: 'data',
			stringfields: [
				{name: 'PersonRegister_id', type: 'int', header: 'ID', key: true},
				{name: 'MedPersonal_iid', type: 'int', hidden: true},
				{name: 'Lpu_iid', type: 'int', hidden: true},
				{name: 'Diag_id', type: 'int', hidden: true},
				{name: 'Person_id', type: 'int', hidden: true},
				{name: 'PersonEvn_id', type: 'int', hidden: true},
				{name: 'Server_id', type: 'int', hidden: true},
				{name: 'DrugRequest', type: 'int', hidden: true},
				{name: 'Person_Surname', type: 'string', header: lang['familiya'], width: 150},
				{name: 'Person_Firname', type: 'string', header: lang['imya'], width: 150},
				{name: 'Person_Secname', type: 'string', header: lang['otchestvo'], width: 150},
				{name: 'Person_Birthday', type: 'date', format: 'd.m.Y', header: lang['data_rojdeniya'], width: 90},
				{name: 'Lpu_id', hidden: true},
				{name: 'Lpu_Nick', type: 'string', header: lang['lpu_prikr'], width: 150},
				{name: 'Diag_Name', type: 'string', header: lang['diagnoz_mkb-10'], width: 150, id: 'autoexpand'},
				{name: 'PersonRegister_setDate', type: 'date', format: 'd.m.Y', header: lang['data_vklyucheniya_v_registr'], width: 150},
				{name: 'PersonRegister_disDate', type: 'date', format: 'd.m.Y', header: lang['data_isklyucheniya_iz_registra'], width: 170}
				,{name: 'PersonRegisterOutCause_id', type: 'int', hidden: true}
				,{name: 'PersonRegisterOutCause_Name', type: 'string', header: lang['prichina_isklyucheniya_iz_registra'], width: 190}
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
				log(record);
				this.getAction('action_delete').setDisabled( record.get('DrugRequest') > 0 || !FmbaRegistry || record.get('Lpu_id') != getGlobalOptions().lpu_id );
				this.getAction('person_register_dis').setDisabled( Ext.isEmpty(record.get('PersonRegister_disDate')) == false || !FmbaRegistry || record.get('Lpu_id') != getGlobalOptions().lpu_id );
			}
		});

		this.SearchFilters = getBaseSearchFiltersFrame({
			allowPersonPeriodicSelect: true,
			id: 'FmbaRegistryFilterForm',
			labelWidth: 130,
			ownerWindow: this,
			searchFormType: 'FmbaRegistry',
			tabIndexBase: TABINDEX_DRW,
			tabPanelHeight: 215,
			tabPanelId: 'FRW_SearchFilterTabbar',
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
				title: lang['6_registr'],
				items: [{
					xtype: 'swpersonregistertypecombo',
					hiddenName: 'PersonRegisterType_id',
					fieldLabel: lang['tip_zapisi_registra'],
					width: 200
				}, {
					fieldLabel: lang['data_vklyucheniya_v_registr'],
					name: 'PersonRegister_setDate_Range',
					plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
					width: 180,
					xtype: 'daterangefield'
				}, {
					fieldLabel: lang['data_isklyucheniya_iz_registra'],
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
				title: lang['7_diagnozyi'],
				items: [{
					fieldLabel: lang['diagnoz_s'],
					hiddenName: 'Diag_Code_From',
					valueField: 'Diag_Code',
					width: 450,
                    registryType: 'Fmba',
					xtype: 'swdiagcombo'
				},{
					fieldLabel: lang['po'],
					hiddenName: 'Diag_Code_To',
					valueField: 'Diag_Code',
					width: 450,
                    registryType: 'Fmba',
					xtype: 'swdiagcombo'
				}]
			}]
		});

		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.doSearch();
				}.createDelegate(this),
				iconCls: 'search16',
				tabIndex: TABINDEX_DRW + 120,
				id: 'FRW_SearchButton',
				text: BTN_FRMSEARCH
			}, {
				handler: function() {
					this.doReset();
				}.createDelegate(this),
				iconCls: 'resetsearch16',
				tabIndex: TABINDEX_DRW + 121,
				text: BTN_FRMRESET
			}, {
				handler: function() {
					this.FmbaRegistrySearchFrame.printRecords();
				}.createDelegate(this),
				iconCls: 'print16',
				tabIndex: TABINDEX_DRW + 122,
				text: lang['pechat_spiska']
			}, {
				handler: function() {
					this.getRecordsCount();
				}.createDelegate(this),
				// iconCls: 'resetsearch16',
				tabIndex: TABINDEX_DRW + 123,
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
					this.findById('FRW_SearchFilterTabbar').getActiveTab().fireEvent('activate', this.findById('FRW_SearchFilterTabbar').getActiveTab());
				}.createDelegate(this),
				tabIndex: TABINDEX_DRW + 124,
				text: BTN_FRMCLOSE
			}],
			getFilterForm: function() {
				if ( this.filterForm == undefined ) {
					this.filterForm = this.findById('FmbaRegistryFilterForm');
				}
				return this.filterForm;
			},
			items: [ this.SearchFilters, this.FmbaRegistrySearchFrame]
		});

		sw.Promed.swFmbaRegistryWindow.superclass.initComponent.apply(this, arguments);
		
	},
	layout: 'border',
	listeners: {
		'hide': function(win) {
			win.doReset();
		},
		'maximize': function(win) {
			win.findById('FmbaRegistryFilterForm').doLayout();
		},
		'restore': function(win) {
			win.findById('FmbaRegistryFilterForm').doLayout();
		},
        'resize': function (win, nW, nH, oW, oH) {
			win.findById('FRW_SearchFilterTabbar').setWidth(nW - 5);
			win.findById('FmbaRegistryFilterForm').setWidth(nW - 5);
		}
	},
	maximizable: true,
	minHeight: 550,
	minWidth: 800,
	modal: false,
	plain: true,
	resizable: true,
	show: function() {
		sw.Promed.swFmbaRegistryWindow.superclass.show.apply(this, arguments);
		
		this.FmbaRegistrySearchFrame.addActions({
			name:'person_register_dis', 
			text:lang['isklyuchit_iz_registra'], 
			tooltip: lang['isklyuchit_iz_registra'],
			iconCls: 'pers-disp16',
            handler: function() {
				this.openWindow('person_register_dis');
			}.createDelegate(this)
		});

		this.FmbaRegistrySearchFrame.addActions({
			name:'open_emk', 
			text:lang['otkryit_emk'], 
			tooltip: lang['otkryit_elektronnuyu_meditsinskuyu_kartu_patsienta'],
			iconCls: 'open16',
			handler: function() {
				this.emkOpen();
			}.createDelegate(this)
		});
		
		var base_form = this.findById('FmbaRegistryFilterForm').getForm();

		this.restore();
		this.center();
		this.maximize();
		this.doReset();
		//this.findById('DRW_SearchFilterTabbar').setActiveTab(0);
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
		
		var isSaratovOuzUser = getGlobalOptions().region.nick == 'saratov' && (String(getGlobalOptions().groups).indexOf('OuzAdmin', 0) >= 0 || String(getGlobalOptions().groups).indexOf('OuzChief', 0) >= 0 || String(getGlobalOptions().groups).indexOf('OuzUser', 0) >= 0);
		
		if(getRegionNick() != 'kareliya')
			base_form.findField('AttachLpu_id').setValue(getGlobalOptions().lpu_id);
		if ( isSaratovOuzUser || String(getGlobalOptions().groups).indexOf('FmbaRegistry', 0) >= 0 ) {
			base_form.findField('AttachLpu_id').setDisabled(false);
		} else {
			base_form.findField('AttachLpu_id').setDisabled(true);
		}
		
		this.doLayout();
		
		base_form.findField('PersonRegisterType_id').setValue(1);
		this.doSearch({firstLoad: true});
	},
	deletePersonRegister: function() {
		var grid = this.FmbaRegistrySearchFrame.getGrid();
		if ( !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('Person_id') )
		{
			Ext.Msg.alert(lang['oshibka'], lang['ne_vyibrana_zapis']);
			return false;
		}
		var record = grid.getSelectionModel().getSelected();
		
		Ext.Msg.show({
			title: lang['vopros'],
			msg: lang['udalit_vyibrannuyu_zapis_registra'],
			buttons: Ext.Msg.YESNO,
			fn: function(btn) {
				if (btn === 'yes') {
					this.getLoadMask(lang['udalenie']).show();
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
								sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_udalenii_zapisi_registra']);
							}
						}.createDelegate(this)
					});
				}
			}.createDelegate(this),
			icon: Ext.MessageBox.QUESTION
		});
	},
	emkOpen: function()
	{
		var grid = this.FmbaRegistrySearchFrame.getGrid();

		if ( !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('Person_id') )
		{
			Ext.Msg.alert(lang['oshibka'], lang['ne_vyibrana_zapis']);
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
			ARMType: 'common',
			callback: function()
			{
				//
			}.createDelegate(this)
		});
	},
	title: lang['registr_fmba'],
	width: 800
});
