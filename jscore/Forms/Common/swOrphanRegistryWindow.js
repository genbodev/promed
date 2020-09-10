/**
* swOrphanRegistryWindow - Регистр по орфанным заболеваниям
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Alexander Permyakov
* @version      
* @comment      Префикс для id компонентов ORPHW (OrphanRegistryWindow)
*
*/
sw.Promed.swOrphanRegistryWindow = Ext.extend(sw.Promed.BaseForm, {codeRefresh: true,
	buttonAlign: 'left',
	closable: true,
	closeAction: 'hide',
	collapsible: true,
	getButtonSearch: function() {
		return Ext.getCmp('ORPHW_SearchButton');
	},
	doReset: function() {
		
		var base_form = this.findById('OrphanRegistryFilterForm').getForm();
		base_form.reset();
		this.OrphanRegistrySearchFrame.ViewActions.open_emk.setDisabled(true);
		this.OrphanRegistrySearchFrame.ViewActions.person_register_dis.setDisabled(true);
		this.OrphanRegistrySearchFrame.ViewActions.action_view.setDisabled(true);
		this.OrphanRegistrySearchFrame.ViewActions.action_delete.setDisabled(true);
		this.OrphanRegistrySearchFrame.ViewActions.action_refresh.setDisabled(true);
		this.OrphanRegistrySearchFrame.getGrid().getStore().removeAll();
		
	},
	doSearch: function(params) {
		
		if (typeof params != 'object') {
			params = {};
		}
		
		var base_form = this.findById('OrphanRegistryFilterForm').getForm();
		
		if ( !params.firstLoad && this.findById('OrphanRegistryFilterForm').isEmpty() ) {
			sw.swMsg.alert(lang['oshibka'], lang['ne_zapolneno_ni_odno_pole'], function() {
			});
			return false;
		}
		
		var grid = this.OrphanRegistrySearchFrame.getGrid();

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
				msg: lang['vyibran_tip_poiska_cheloveka'] + (base_form.findField('PersonPeriodicType_id').getValue() == 2 ? lang['po_sostoyaniyu_na_moment_sluchaya'] : lang['po_vsem_periodikam']) + lang['pri_vyibrannom_variante_poisk_rabotaet_znachitelno_medlennee_hotite_prodoljit_poisk'],
				title: lang['preduprejdenie']
			});
			return false;
		}

		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет поиск..."});
		loadMask.show();

		var post = getAllFormFieldValues(this.findById('OrphanRegistryFilterForm'));
		//post.DispLpu_id = base_form.findField('DispLpu_id').getValue();

		post.limit = 100;
		post.start = 0;
		
		//log(post);

		if ( base_form.isValid() ) {
			this.OrphanRegistrySearchFrame.ViewActions.action_refresh.setDisabled(false);
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
		var base_form = this.getFilterForm().getForm();

		if ( !base_form.isValid() ) {
			sw.swMsg.alert(lang['poisk'], lang['proverte_pravilnost_zapolneniya_poley_na_forme_poiska']);
			return false;
		}

		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет подсчет записей..."});
		loadMask.show();

		var post = getAllFormFieldValues(this.getFilterForm());
		post.SearchFormType = 'OrphanRegistry';

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
	height: 550,
	openWindow: function(action) {
		if (!action || !action.toString().inlist(['registry_export','person_register_dis','add','view','edit'])) {
			return false;
		}
		var cur_win = this;
		var form = this.getFilterForm().getForm();
		var grid = this.OrphanRegistrySearchFrame.getGrid();
		if (!grid.getSelectionModel().getSelected()) {
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
					,Person_id: selected_record.get('Person_id')
					,Diag_Name: selected_record.get('Diag_Name')
					,PersonRegister_setDate: selected_record.get('PersonRegister_setDate')
					,MorbusType_SysNick: 'orphan'
					,callback: function(data) {
						grid.getStore().reload();
					}
				});
				break;
			case 'add':
				sw.Promed.personRegister.add({
                    MorbusType_SysNick: 'orphan'
					,callback: function(data) {
						form.findField('Person_Firname').setValue(data.Person_Firname);
						form.findField('Person_Secname').setValue(data.Person_Secname);
						form.findField('Person_Surname').setValue(data.Person_Surname);
						form.findField('Person_Birthday').setValue(data.Person_Birthday);
						cur_win.doSearch();
					}
				});
				break;
            case 'edit':
            case 'view':
				if (getWnd('swMorbusOrphanWindow').isVisible()) {
					getWnd('swMorbusOrphanWindow').hide();
				}
				if ( Ext.isEmpty(selected_record.get('Morbus_id')) ) {
					sw.swMsg.alert(lang['soobschenie'], lang['zabolevanie_na_cheloveka_ne_zavedeno']);
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
				getWnd('swMorbusOrphanWindow').show(params);
				break;
			
			case 'registry_export':
				if ( Ext.isEmpty(selected_record.get('Morbus_id')) ) {
					sw.swMsg.alert(lang['soobschenie'], lang['zabolevanie_na_cheloveka_ne_zavedeno']);
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
		
		var OrphanRegistry = ( String(getGlobalOptions().groups).indexOf('Orphan', 0) >= 0 );
		
		this.OrphanRegistrySearchFrame = new sw.Promed.ViewFrame({
			actions: [
				{name: 'action_add', hidden: getWnd('swWorkPlaceSpecMEKLLOWindow').isVisible() || getWnd('swWorkPlaceAdminLLOWindow').isVisible(), disabled: !OrphanRegistry, handler: function() { this.openWindow('add'); }.createDelegate(this)},
                {name: 'action_edit', hidden: getWnd('swWorkPlaceSpecMEKLLOWindow').isVisible() || getWnd('swWorkPlaceAdminLLOWindow').isVisible(), handler: function() { this.openWindow('edit'); }.createDelegate(this)},
                {name: 'action_view', handler: function() { this.openWindow('view'); }.createDelegate(this)},
				{name: 'action_delete', hidden: getWnd('swWorkPlaceSpecMEKLLOWindow').isVisible() || getWnd('swWorkPlaceAdminLLOWindow').isVisible(), disabled: true, handler: this.deletePersonRegister.createDelegate(this)},
				{name: 'action_refresh'},
				{name: 'action_print' }
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 150,
			autoLoadData: false,
			dataUrl: C_SEARCH,
			id: 'ORPHW_OrphanRegistrySearchGrid',
			object: 'OrphanRegistry',
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
				{name: 'MorbusOrphan_id', type: 'int', hidden: true},
				{name: 'Morbus_id', type: 'int', hidden: true},
				{name: 'Person_id', type: 'int', hidden: true},
				{name: 'PersonEvn_id', type: 'int', hidden: true},
				{name: 'Server_id', type: 'int', hidden: true},
				{name: 'Person_Surname', type: 'string', header: lang['familiya'], width: 150},
				{name: 'Person_Firname', type: 'string', header: lang['imya'], width: 150},
				{name: 'Person_Secname', type: 'string', header: lang['otchestvo'], width: 150},
				{name: 'Person_Birthday', type: 'date', format: 'd.m.Y', header: lang['data_rojdeniya'], width: 90},
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
				this.getAction('open_emk').setDisabled( false );
				this.getAction('person_register_dis').setDisabled( Ext.isEmpty(record.get('PersonRegister_disDate')) == false || !OrphanRegistry );
				this.getAction('action_delete').setDisabled( !OrphanRegistry || Ext.isEmpty(record.get('PersonRegister_id')) );
                this.getAction('action_edit').setDisabled( !OrphanRegistry || Ext.isEmpty(record.get('Morbus_id')) || Ext.isEmpty(record.get('PersonRegister_disDate')) == false );
                this.getAction('action_view').setDisabled( Ext.isEmpty(record.get('Morbus_id')) );
			},
			onDblClick: function(sm,index,record) {
				this.getAction('action_view').execute();
			}
		});

		this.SearchFilters = getBaseSearchFiltersFrame({
			allowPersonPeriodicSelect: true,
			id: 'OrphanRegistryFilterForm',
			labelWidth: 130,
			ownerWindow: this,
			searchFormType: 'OrphanRegistry',
			tabIndexBase: TABINDEX_ORPHW,
			tabPanelHeight: 215,
			tabPanelId: 'ORPHW_SearchFilterTabbar',
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
					width: 170,
					xtype: 'daterangefield'
				}, {
					fieldLabel: lang['data_isklyucheniya_iz_registra'],
					name: 'PersonRegister_disDate_Range',
					plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
					width: 170,
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
					border: false,
					layout: 'column',
					items: [{
						border: false,
						layout: 'form',									
						items: [{
							fieldLabel: lang['diagnoz_s'],
							hiddenName: 'Diag_Code_From',
							listWidth: 620,
							valueField: 'Diag_Code',
							width: 290,
                            MorbusType_SysNick: 'orphan',
							xtype: 'swdiagcombo'
						}]
					}, {
						border: false,
						layout: 'form',
						labelWidth: 35,
						items: [{
							fieldLabel: lang['po'],
							hiddenName: 'Diag_Code_To',
							listWidth: 620,
							valueField: 'Diag_Code',
							width: 290,
                            MorbusType_SysNick: 'orphan',
							xtype: 'swdiagcombo'
						}]
					}]
				/*
				}, {
					fieldLabel: lang['lpu_v_kotoroy_patsientu_vpervyie_ustanovlen_diagnoz_orfannogo_zabolevaniya'],
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
				tabIndex: TABINDEX_ORPHW + 120,
				id: 'ORPHW_SearchButton',
				text: BTN_FRMSEARCH
			}, {
				handler: function() {
					this.doReset();
				}.createDelegate(this),
				iconCls: 'resetsearch16',
				tabIndex: TABINDEX_ORPHW + 121,
				text: BTN_FRMRESET
			}, /*{
				handler: function() {
					this.OrphanRegistrySearchFrame.printRecords();
				}.createDelegate(this),
				iconCls: 'print16',
				tabIndex: TABINDEX_ORPHW + 122,
				text: lang['pechat_spiska']
			},*/ {
				handler: function() {
					this.getRecordsCount();
				}.createDelegate(this),
				// iconCls: 'resetsearch16',
				tabIndex: TABINDEX_ORPHW + 123,
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
					this.findById('ORPHW_SearchFilterTabbar').getActiveTab().fireEvent('activate', this.findById('ORPHW_SearchFilterTabbar').getActiveTab());
				}.createDelegate(this),
				tabIndex: TABINDEX_ORPHW + 124,
				text: BTN_FRMCLOSE
			}],
			getFilterForm: function() {
				if ( this.filterForm == undefined ) {
					this.filterForm = this.findById('OrphanRegistryFilterForm');
				}
				return this.filterForm;
			},
			items: [ this.SearchFilters, this.OrphanRegistrySearchFrame]
		});

		sw.Promed.swOrphanRegistryWindow.superclass.initComponent.apply(this, arguments);
		
	},
	layout: 'border',
	listeners: {
		'hide': function(win) {
			win.doReset();
		},
		'maximize': function(win) {
			win.findById('OrphanRegistryFilterForm').doLayout();
		},
		'restore': function(win) {
			win.findById('OrphanRegistryFilterForm').doLayout();
		},
		'beforeShow': function(win) {
			if (getGlobalOptions().region.nick != 'saratov') { // У Саратова своя атмосфера
				if (String(getGlobalOptions().groups).indexOf('Orphan', 0) < 0 && getGlobalOptions().CurMedServiceType_SysNick != 'minzdravdlo')
				{
					sw.swMsg.alert('Сообщение', 'Форма "'+ win.title +'" доступна только для пользователей, с указанной группой «Регистр по орфанным заболеваниям»');
					return false;
				}				
			}
		},
        'resize': function (win, nW, nH, oW, oH) {
			win.findById('ORPHW_SearchFilterTabbar').setWidth(nW - 5);
			win.findById('OrphanRegistryFilterForm').setWidth(nW - 5);
		}
	},
	maximizable: true,
	minHeight: 550,
	minWidth: 800,
	modal: false,
	plain: true,
	resizable: true,
	show: function() {
		sw.Promed.swOrphanRegistryWindow.superclass.show.apply(this, arguments);
		
		this.OrphanRegistrySearchFrame.addActions({
			name:'action_export', 
			text:lang['vyigruzka_v_federalnyiy_registr'], 
			tooltip: lang['vyigruzka_v_federalnyiy_registr'],
			iconCls: '',
			disabled: false,
            hidden: getWnd('swWorkPlaceSpecMEKLLOWindow').isVisible() || getWnd('swWorkPlaceAdminLLOWindow').isVisible(),
			handler: function() {
				this.openWindow('registry_export');
			}.createDelegate(this)
		});
		
		this.OrphanRegistrySearchFrame.addActions({
			name:'person_register_dis', 
			text:lang['isklyuchit_iz_registra'], 
			tooltip: lang['isklyuchit_iz_registra'],
			iconCls: 'pers-disp16',
			disabled: (String(getGlobalOptions().groups).indexOf('Orphan', 0) < 0),
            hidden: getWnd('swWorkPlaceSpecMEKLLOWindow').isVisible() || getWnd('swWorkPlaceAdminLLOWindow').isVisible(),
            handler: function() {
				this.openWindow('person_register_dis');
			}.createDelegate(this)
		});
		
		this.OrphanRegistrySearchFrame.addActions({
			name:'open_emk', 
			text:lang['otkryit_emk'], 
			tooltip: lang['otkryit_elektronnuyu_meditsinskuyu_kartu_patsienta'],
            hidden: getWnd('swWorkPlaceSpecMEKLLOWindow').isVisible() || getWnd('swWorkPlaceAdminLLOWindow').isVisible(),
			iconCls: 'open16',
			handler: function() {
				this.emkOpen();
			}.createDelegate(this)
		});
		
		var base_form = this.findById('OrphanRegistryFilterForm').getForm();

		this.restore();
		this.center();
		this.maximize();
		this.doReset();
		//this.findById('ORPHW_SearchFilterTabbar').setActiveTab(0);
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
		
		/*
		var isSaratovOuzUser = getGlobalOptions().region.nick == 'saratov' && (String(getGlobalOptions().groups).indexOf('OuzAdmin', 0) >= 0 || String(getGlobalOptions().groups).indexOf('OuzChief', 0) >= 0 || String(getGlobalOptions().groups).indexOf('OuzUser', 0) >= 0);
		
		base_form.findField('AttachLpu_id').setValue(getGlobalOptions().lpu_id);
		if ( isSaratovOuzUser || String(getGlobalOptions().groups).indexOf('Orphan', 0) >= 0 ) {
			base_form.findField('AttachLpu_id').setDisabled(false);
		} else {
			base_form.findField('AttachLpu_id').setDisabled(true);
		}
		*/
		var minzdrav = getGlobalOptions().isMinZdrav;
		var att_lpu_field = base_form.findField('AttachLpu_id');
		att_lpu_field.setFieldLabel(lang['mo_prikrepleniya']);
		att_lpu_field.setValue(isSuperAdmin()?null:getGlobalOptions().lpu_id);
		att_lpu_field.setDisabled((isSuperAdmin()||minzdrav)?false:true);
		this.doLayout();
		
		base_form.findField('PersonRegisterType_id').setValue(1);
		this.doSearch({firstLoad: true});
	},
	emkOpen: function()
	{
		var grid = this.OrphanRegistrySearchFrame.getGrid();

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
	deletePersonRegister: function() {
		var grid = this.OrphanRegistrySearchFrame.getGrid();
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
	title: lang['registr_po_orfannyim_zabolevaniyam'],
	width: 800
});