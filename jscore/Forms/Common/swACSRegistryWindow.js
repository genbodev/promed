/**
* swACSRegistryWindow - Регистр по ОКС
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2014 Swan Ltd.
* @author       
* @version      
* @comment      Префикс для id компонентов ACSW (swACSRegistryWindow)
*
*/
sw.Promed.swACSRegistryWindow = Ext.extend(sw.Promed.BaseForm, {codeRefresh: true,
	buttonAlign: 'left',
	closable: true,
	id:'swACSRegistryWindow',
	closeAction: 'hide',
	collapsible: true,
	getButtonSearch: function() {
		return Ext.getCmp('ACSW_SearchButton');
	},
	doReset: function() {
		
		var base_form = this.findById('ACSRegistryFilterForm').getForm();
		base_form.reset();
		this.ACSRegistrySearchFrame.ViewActions.open_emk.setDisabled(true);
		this.ACSRegistrySearchFrame.ViewActions.action_view.setDisabled(true);
		this.ACSRegistrySearchFrame.ViewActions.action_delete.setDisabled(true);
		this.ACSRegistrySearchFrame.ViewActions.action_refresh.setDisabled(true);
		this.ACSRegistrySearchFrame.getGrid().getStore().removeAll();
		
		// https://redmine.swan.perm.ru/issues/41490
		// Сюда перетащил из show, чтобы отрабатывало после вызова doReset
		// Закомментировал, т.к. нужно, чтобы поле было доступно для выбора и пустым по-умолчанию
		/*var minzdrav = getGlobalOptions().isMinZdrav;
		var att_lpu_field = base_form.findField('AttachLpu_id');
		att_lpu_field.setValue(isSuperAdmin()?null:getGlobalOptions().lpu_id);*/
	},
	doSearch: function(params) {
		
		if (typeof params != 'object') {
			params = {};
		}
		
		var base_form = this.findById('ACSRegistryFilterForm').getForm();
		
		if ( !params.firstLoad && this.findById('ACSRegistryFilterForm').isEmpty() ) {
			sw.swMsg.alert(lang['oshibka'], lang['ne_zapolneno_ni_odno_pole'], function() {
			});
			return false;
		}
		
		var grid = this.ACSRegistrySearchFrame.getGrid();

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

		var post = getAllFormFieldValues(this.findById('ACSRegistryFilterForm'));
		//post.DispLpu_id = base_form.findField('DispLpu_id').getValue();

		post.limit = 100;
		post.start = 0;
		
		//log(post);
		if ( base_form.isValid() ) {
			this.ACSRegistrySearchFrame.ViewActions.action_refresh.setDisabled(false);
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
		post.SearchFormType = 'ACSRegistry';

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
		if (!action || !action.toString().inlist(['add','view','edit'])) {
			return false;
		}
		var cur_win = this;
		var form = this.getFilterForm().getForm();
		var grid = this.ACSRegistrySearchFrame.getGrid();
		if (action.toString().inlist(['view','edit']) && !grid.getSelectionModel().getSelected()) {
			return false;
		}
		var selected_record = grid.getSelectionModel().getSelected();

		var params = new Object();
		params.userMedStaffFact = this.userMedStaffFact;
		params.action = action;
		params.callback = function(data) {
			grid.getStore().reload();
		}
		params.onHide = function() {
			grid.getView().focusRow(grid.getStore().indexOf(selected_record));
		};

		switch(action) {
			case 'add':
				sw.Promed.personRegister.add({
					MorbusType_SysNick: 'acs'
					,callback: function(data) {
						/*form.findField('Person_Firname').setValue(data.Person_Firname);
						form.findField('Person_Secname').setValue(data.Person_Secname);
						form.findField('Person_Surname').setValue(data.Person_Surname);
						form.findField('Person_Birthday').setValue(data.Person_Birthday);*/
						//cur_win.doReset();
						cur_win.doSearch({firstLoad: true});
					}
				});
				break;
            case 'edit':
            case 'view':
				if (getWnd('swMorbusACSWindow').isVisible()) {
					getWnd('swMorbusACSWindow').hide();
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
				getWnd('swMorbusACSWindow').show(params);
				break;	
		}
		

		
	},
	initComponent: function() {
		
		var ACSRegistry = (isUserGroup("OKSRegistry"));
		
		this.ACSRegistrySearchFrame = new sw.Promed.ViewFrame({
			actions: [
				{name: 'action_add', handler: function() { this.openWindow('add'); }.createDelegate(this)},
                {name: 'action_edit', handler: function() { this.openWindow('edit'); }.createDelegate(this)},
                {name: 'action_view', handler: function() { this.openWindow('view'); }.createDelegate(this)},
				{name: 'action_delete', disabled: true, handler: this.deletePersonRegister.createDelegate(this)},
				{name: 'action_refresh'},
				{name: 'action_print' }
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 150,
			autoLoadData: false,
			dataUrl: C_SEARCH,
			id: 'ACSW_ACSRegistrySearchGrid',
			object: 'ACSRegistry',
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
				{name: 'MorbusACS_id', type: 'int', hidden: true},
				{name: 'Morbus_id', type: 'int', hidden: true},
				{name: 'Person_id', type: 'int', hidden: true},
				{name: 'PersonEvn_id', type: 'int', hidden: true},
				{name: 'Server_id', type: 'int', hidden: true},
				{name: 'Person_Surname', type: 'string', header: lang['familiya'], width: 150},
				{name: 'Person_Firname', type: 'string', header: lang['imya'], width: 150},
				{name: 'Person_Secname', type: 'string', header: lang['otchestvo'], width: 150},
				{name: 'Person_Birthday', type: 'date', format: 'd.m.Y', header: lang['data_rojdeniya'], width: 90},
				{name: 'Lpu_Nick', type: 'string', header: lang['lpu_prikr'], width: 150},
				{name: 'PersonRegister_setDate', type: 'date', format: 'd.m.Y', header: lang['data_vklyucheniya_v_registr'], width: 150},
				{name: 'LpuAdd_Nick', type: 'string', hidden: getRegionNick() != 'astra', header: lang['lpu_dobavivshee_v_registr'], width: 170}
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
				this.getAction('action_delete').setDisabled( !ACSRegistry || Ext.isEmpty(record.get('PersonRegister_id')) );
                this.getAction('action_edit').setDisabled( !ACSRegistry || Ext.isEmpty(record.get('Morbus_id')) || Ext.isEmpty(record.get('PersonRegister_disDate')) == false );
                this.getAction('action_view').setDisabled( Ext.isEmpty(record.get('Morbus_id')) );
			},
			onDblClick: function(sm,index,record) {
				this.getAction('action_view').execute();
			}
		});

		this.SearchFilters = getBaseSearchFiltersFrame({
			allowPersonPeriodicSelect: true,
			id: 'ACSRegistryFilterForm',
			labelWidth: 130,
			ownerWindow: this,
			searchFormType: 'ACSRegistry',
			tabIndexBase: TABINDEX_ORPHW,
			tabPanelHeight: 215,
			tabPanelId: 'ACSW_SearchFilterTabbar',
			tabs: [{
				autoHeight: true,
				bodyStyle: 'margin-top: 5px;',
				border: false,
				labelWidth: 220,
				layout: 'form',
				listeners: {
					'activate': function(panel) {
						var form = this.getFilterForm().getForm();
						form.findField('PersonRegister_setDate_Range').focus(250, true);
					}.createDelegate(this)
				},
				title: lang['6_registr'],
				items: [ {
                    border: false,
                    layout: 'column',
                    items: [{
                        layout: 'form',
                        border: false,
                        items: [{
                            fieldLabel: lang['data_vklyucheniya_v_registr'],
                            name: 'PersonRegister_setDate_Range',
                            plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
                            width: 170,
                            xtype: 'daterangefield'
                        },{
                            fieldLabel: lang['data_isklyucheniya_iz_registra'],
                            name: 'PersonRegister_disDate_Range1',
                            plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
                            width: 170,
                            xtype: 'daterangefield'
                        },{
                            fieldLabel: lang['diagnoz_po_oks'],
                            hiddenName: 'DiagACS_id',
                            width: 250,
                            listWidth: 400,
                            lastQuery: '',
                            xtype: 'swaksdiagcombo'
                        },{
                            fieldLabel: lang['mo_dobavleniya'],
                            hiddenName: 'Lpu_iid',
                            listWidth: 400,
                            width: 250,
                            xtype: 'swlpucombo'
                        }]
                    }, {
                        layout: 'form',
                        border: false,
                        labelWidth: 300,
                        items: [{
                            fieldLabel: lang['podyem_segmenta_st'],
                            hiddenName: 'MorbusACS_IsST',
                            width: 100,
                            xtype: 'swyesnocombo'
                        },{
                            fieldLabel: lang['koronaroangiografiya'],
                            hiddenName: 'MorbusACS_IsCoronary',
                            width: 100,
                            xtype: 'swyesnocombo'
                        },{
                            fieldLabel: lang['chrezkojnoe_koronarnoe_vmeshatelstvo'],
                            hiddenName: 'MorbusACS_IsTransderm',
                            width: 100,
                            xtype: 'swyesnocombo'
                        } ]
                    }]
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
				id: 'ACSW_SearchButton',
				text: BTN_FRMSEARCH
			}, {
				handler: function() {
					this.doReset();
				}.createDelegate(this),
				iconCls: 'resetsearch16',
				tabIndex: TABINDEX_ORPHW + 121,
				text: BTN_FRMRESET
			}, {
				handler: function() {
					this.ACSRegistrySearchFrame.printRecords();
				}.createDelegate(this),
				iconCls: 'print16',
				tabIndex: TABINDEX_ORPHW + 122,
				text: lang['pechat_spiska']
			}, {
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
					this.findById('ACSW_SearchFilterTabbar').getActiveTab().fireEvent('activate', this.findById('ACSW_SearchFilterTabbar').getActiveTab());
				}.createDelegate(this),
				tabIndex: TABINDEX_ORPHW + 124,
				text: BTN_FRMCLOSE
			}],
			getFilterForm: function() {
				if ( this.filterForm == undefined ) {
					this.filterForm = this.findById('ACSRegistryFilterForm');
				}
				return this.filterForm;
			},
			items: [ this.SearchFilters, this.ACSRegistrySearchFrame]
		});

		sw.Promed.swACSRegistryWindow.superclass.initComponent.apply(this, arguments);
		
	},
	layout: 'border',
	listeners: {
		'hide': function(win) {
			win.doReset();
		},
		'maximize': function(win) {
			win.findById('ACSRegistryFilterForm').doLayout();
		},
		'restore': function(win) {
			win.findById('ACSRegistryFilterForm').doLayout();
		},
		'beforeShow': function(win) {
			if (getGlobalOptions().region.nick != 'saratov') { // У Саратова своя атмосфера
				if (!isUserGroup("OKSRegistry") && getGlobalOptions().CurMedServiceType_SysNick != 'minzdravdlo')
				{
					sw.swMsg.alert('Сообщение', 'Форма "'+ win.title +'" доступна только для пользователей, с указанной группой «Регистр по ОКС заболеваниям»');
					return false;
				}				
			}
		},
        'resize': function (win, nW, nH, oW, oH) {
			win.findById('ACSW_SearchFilterTabbar').setWidth(nW - 5);
			win.findById('ACSRegistryFilterForm').setWidth(nW - 5);
		}
	},
	maximizable: true,
	minHeight: 550,
	minWidth: 800,
	modal: false,
	plain: true,
	resizable: true,
	show: function() {
		sw.Promed.swACSRegistryWindow.superclass.show.apply(this, arguments);
		
		this.ACSRegistrySearchFrame.addActions({
			name:'open_emk', 
			text:lang['otkryit_emk'], 
			tooltip: lang['otkryit_elektronnuyu_meditsinskuyu_kartu_patsienta'],
			iconCls: 'open16',
			handler: function() {
				this.emkOpen();
			}.createDelegate(this)
		});
		
		var base_form = this.findById('ACSRegistryFilterForm').getForm();

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

		/*var minzdrav = getGlobalOptions().isMinZdrav;
		var att_lpu_field = base_form.findField('AttachLpu_id');
		att_lpu_field.setFieldLabel(lang['mo_prikrepleniya']);
		att_lpu_field.setValue(isSuperAdmin()?null:getGlobalOptions().lpu_id);
		att_lpu_field.setDisabled((isSuperAdmin()||minzdrav)?false:true);*/

		this.doLayout();
		
		//base_form.findField('PersonRegisterType_id').setValue(1);
		this.doSearch({firstLoad: true});
	},
	emkOpen: function()
	{
		var grid = this.ACSRegistrySearchFrame.getGrid();

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
		var curWin = this;
		var grid = this.ACSRegistrySearchFrame.getGrid();
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
								if( obj.success ){
									grid.getStore().remove(record);
									curWin.doReset();
									curWin.doSearch({firstLoad: true});
								}
								
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
	title: lang['registr_po_oks_zabolevaniyam'],
	width: 800
});