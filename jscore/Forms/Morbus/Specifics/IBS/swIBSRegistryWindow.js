/**
 * swIBSRegistryWindow - Регистр ИБС
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * @package      IBS
 * @access       public
 * @copyright    Copyright (c) 2009-2014 Swan Ltd.
 * @author       Alexander Permyakov
 * @version      12.2014
 * @comment      Префикс для id компонентов IBSRW
 */
sw.Promed.swIBSRegistryWindow = Ext.extend(sw.Promed.BaseForm, {codeRefresh: true,
	buttonAlign: 'left',
	closable: true,
	closeAction: 'hide',
	collapsible: true,
	getButtonSearch: function() {
		return Ext.getCmp('IBSRW_SearchButton');
	},
	doReset: function() {
		
		var base_form = this.findById('IBSRegistryFilterForm').getForm();
		base_form.reset();
		this.IBSRegistrySearchFrame.ViewActions.open_emk.setDisabled(true);
		this.IBSRegistrySearchFrame.ViewActions.person_register_dis.setDisabled(true);
		this.IBSRegistrySearchFrame.ViewActions.action_view.setDisabled(true);
		this.IBSRegistrySearchFrame.ViewActions.action_delete.setDisabled(true);
		this.IBSRegistrySearchFrame.ViewActions.action_refresh.setDisabled(true);
		this.IBSRegistrySearchFrame.getGrid().getStore().removeAll();
	},
	doSearch: function(params) {
		
		if (typeof params != 'object') {
			params = {};
		}
		
		var base_form = this.findById('IBSRegistryFilterForm').getForm();
		
		if ( !params.firstLoad && this.findById('IBSRegistryFilterForm').isEmpty() ) {
			sw.swMsg.alert(lang['oshibka'], lang['ne_zapolneno_ni_odno_pole'], function() {
			});
			return false;
		}
		
		var grid = this.IBSRegistrySearchFrame.getGrid();

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

		var post = getAllFormFieldValues(this.findById('IBSRegistryFilterForm'));
		//post.DispLpu_id = base_form.findField('DispLpu_id').getValue();

		post.limit = 100;
		post.start = 0;
		post.AttachLpu_id = base_form.findField('AttachLpu_id').getValue();
		
		//log(post);

		if ( base_form.isValid() ) {
			this.IBSRegistrySearchFrame.ViewActions.action_refresh.setDisabled(false);
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
		var grid = this.IBSRegistrySearchFrame.getGrid();
		if (!grid.getSelectionModel().getSelected() && action!='add') {
			return false;
		}
		var selected_record = grid.getSelectionModel().getSelected();

		var params = {};
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
					,MorbusType_SysNick: 'ibs'
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
					viewOnly: (cur_win.editType=='onlyRegister')?true:false,
                    MorbusType_SysNick: 'ibs'
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
				if (getWnd('swMorbusIBSWindow').isVisible()) {
					getWnd('swMorbusIBSWindow').hide();
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
				params.editType = cur_win.editType;
				getWnd('swMorbusIBSWindow').show(params);
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
		var me = this;
		this.IBSRegistrySearchFrame = new sw.Promed.ViewFrame({
			actions: [
				{name: 'action_add', handler: function() { me.openWindow('add'); }},
                {name: 'action_edit', handler: function() { me.openWindow('edit'); }},
                {name: 'action_view', handler: function() { me.openWindow('view'); }},
				{name: 'action_delete', handler: this.deletePersonRegister.createDelegate(this)},
				{name: 'action_refresh'},
				{name: 'action_print'}
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 150,
			autoLoadData: false,
			dataUrl: C_SEARCH,
			id: 'IBSRW_IBSRegistrySearchGrid',
			object: 'IBSRegistry',
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
				{name: 'MorbusIBS_id', type: 'int', hidden: true},
				{name: 'Morbus_id', type: 'int', hidden: true},
				{name: 'Person_id', type: 'int', hidden: true},
				{name: 'PersonEvn_id', type: 'int', hidden: true},
				{name: 'Server_id', type: 'int', hidden: true},
				{name: 'IBSType_Name', type: 'string', header: lang['tip_ibs'], width: 150, hidden: true},
				{name: 'Person_Surname', type: 'string', header: lang['familiya'], width: 150},
				{name: 'Person_Firname', type: 'string', header: lang['imya'], width: 150},
				{name: 'Person_Secname', type: 'string', header: lang['otchestvo'], width: 150},
				{name: 'Person_Birthday', type: 'date', format: 'd.m.Y', header: lang['data_rojdeniya'], width: 90},
				{name: 'Lpu_Nick', type: 'string', header: lang['mo_prikr'], width: 150},
				{name: 'Diag_Name', type: 'string', header: lang['diagnoz_mkb-10'], width: 150, id: 'autoexpand'},
                {name: 'MorbusIBS_IsKGFinished', type: 'checkcolumn', header: lang['provedena_kg'], width: 90},
				{name: 'PersonRegister_setDate', type: 'date', format: 'd.m.Y', header: lang['data_vklyucheniya_v_registr'], width: 150},
				{name: 'PersonRegister_disDate', type: 'date', format: 'd.m.Y', header: lang['data_isklyucheniya_iz_registra'], width: 170}
				,{name: 'PersonRegisterOutCause_id', type: 'int', hidden: true}
				,{name: 'PersonRegisterOutCause_Name', type: 'string', header: lang['prichina_isklyucheniya_iz_registra'], width: 190}
			],
			toolbar: true,
			totalProperty: 'totalCount', 
			onBeforeLoadData: function() {
                me.getButtonSearch().disable();
			},
			onLoadData: function() {
                me.getButtonSearch().enable();
			},
			onRowSelect: function(sm,index,record) {
				this.getAction('open_emk').setDisabled( false );
				this.getAction('person_register_dis').setDisabled( Ext.isEmpty(record.get('PersonRegister_disDate')) == false );
                this.getAction('action_delete').setDisabled( Ext.isEmpty(record.get('PersonRegister_id')) );
                this.getAction('action_edit').setDisabled( Ext.isEmpty(record.get('Morbus_id')) || Ext.isEmpty(record.get('PersonRegister_disDate')) == false );
				this.getAction('action_view').setDisabled( Ext.isEmpty(record.get('Morbus_id')) );
            },
            onEnter: function() {
                var record = this.getGrid().getSelectionModel().getSelected();
                if (record && record.get('Morbus_id')) {
                    if (Ext.isEmpty(record.get('PersonRegister_disDate')) == false) {
                        //this.getAction('action_view').execute();
                        this.getAction(this.getAction('action_edit').isHidden()?'action_view':'action_edit').execute();
                    } else {
                        //this.getAction('action_edit').execute();
                        this.getAction(this.getAction('action_edit').isHidden()?'action_view':'action_edit').execute();
                    }
                }
            },
            onDblClick: function() {
                this.onEnter();
			}
		});

		this.SearchFilters = getBaseSearchFiltersFrame({
			allowPersonPeriodicSelect: true,
			id: 'IBSRegistryFilterForm',
			labelWidth: 130,
			ownerWindow: this,
			searchFormType: 'IBSRegistry',
			tabPanelHeight: 235,
			tabPanelId: 'IBSRW_SearchFilterTabbar',
			tabs: [{
				autoHeight: true,
				bodyStyle: 'margin-top: 5px;',
				border: false,
				labelWidth: 220,
				layout: 'form',
				listeners: {
					'activate': function(panel) {
						var form = me.getFilterForm().getForm();
						form.findField('IBSType_id').focus(250, true);
					}
				},
				title: lang['6_registr'],
				items: [{
					hiddenName: 'IBSType_id',
					fieldLabel: lang['tip_ibs'],
                    xtype: 'swcommonsprcombo',
                    sortField:'IBSType_Code',
                    comboSubject: 'IBSType',
					width: 200
                }, {
                    fieldLabel: lang['diagnoz_s'],
                    hiddenName: 'Diag_Code_From',
                    valueField: 'Diag_Code',
                    width: 450,
                    MorbusType_SysNick: 'ibs',
                    //additQueryFilter: "(Diag_Code like '%I20.' or Diag_Code like '%I21.' or Diag_Code like '%I22.' or Diag_Code like '%I23.' or Diag_Code like '%I24.' or Diag_Code like '%I25.')",
                    //additClauseFilter: '(record["Diag_Code"].search(new RegExp("^I2[0-5]", "i"))>=0)',
                    xtype: 'swdiagcombo'
                }, {
                    fieldLabel: lang['po'],
                    hiddenName: 'Diag_Code_To',
                    valueField: 'Diag_Code',
                    width: 450,
                    MorbusType_SysNick: 'ibs',
                    //additQueryFilter: "(Diag_Code like '%I20.' or Diag_Code like '%I21.' or Diag_Code like '%I22.' or Diag_Code like '%I23.' or Diag_Code like '%I24.' or Diag_Code like '%I25.')",
                    //additClauseFilter: '(record["Diag_Code"].search(new RegExp("^I2[0-5]", "i"))>=0)',
                    xtype: 'swdiagcombo'
                }, {
                    hiddenName: 'MorbusIBS_IsKGIndication',
                    fieldLabel: lang['pokazano_provedenie_kg'],
                    width: 70,
                    xtype: 'swyesnocombo'
                }, {
                    hiddenName: 'MorbusIBS_IsKGFinished',
                    fieldLabel: lang['provedena_kg'],
                    width: 70,
                    xtype: 'swyesnocombo'
				/*}, {
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
					xtype: 'daterangefield'*/
				}]
			/*}, {
				autoHeight: true,
				bodyStyle: 'margin-top: 5px;',
				border: false,
				layout: 'form',
				labelWidth: 180,
				listeners: {
					'activate': function(panel) {
                        me.getFilterForm().getForm().findField('Diag_Code_From').focus(250, true);
					}
				},
				title: lang['7_diagnozyi'],
				items: [{
				}]*/
			}]
		});

		Ext.apply(this, {
			buttons: [{
				handler: function() {
                    me.doSearch();
				},
				iconCls: 'search16',
				id: 'IBSRW_SearchButton',
				text: BTN_FRMSEARCH
			}, {
				handler: function() {
                    me.doReset();
				},
				iconCls: 'resetsearch16',
				text: BTN_FRMRESET
			}, /*{
				handler: function() {
                    me.IBSRegistrySearchFrame.printRecords();
				},
				iconCls: 'print16',
				text: lang['pechat_spiska']
			},*/ {
				handler: function() {
                    me.getRecordsCount();
				},
				// iconCls: 'resetsearch16',
				text: BTN_FRMCOUNT
			}, {
				text: '-'
			},
			HelpButton(this, -1),
			{
				handler: function() {
                    me.hide();
				},
				iconCls: 'cancel16',
				onShiftTabAction: function() {
                    me.buttons[me.buttons.length - 2].focus();
				},
				onTabAction: function() {
                    me.findById('IBSRW_SearchFilterTabbar').getActiveTab().fireEvent('activate', me.findById('IBSRW_SearchFilterTabbar').getActiveTab());
				},
				text: BTN_FRMCLOSE
			}],
			getFilterForm: function() {
				if ( me.filterForm == undefined ) {
                    me.filterForm = me.findById('IBSRegistryFilterForm');
				}
				return me.filterForm;
			},
			items: [ this.SearchFilters, this.IBSRegistrySearchFrame]
		});

		sw.Promed.swIBSRegistryWindow.superclass.initComponent.apply(this, arguments);
		
	},
	layout: 'border',
	listeners: {
		'hide': function(win) {
			win.doReset();
		},
		'maximize': function(win) {
			win.findById('IBSRegistryFilterForm').doLayout();
		},
		'restore': function(win) {
			win.findById('IBSRegistryFilterForm').doLayout();
		},
		'beforeShow': function(win) {
			/*if (String(getGlobalOptions().groups).indexOf('IBSRegistry', 0) < 0) {
				sw.swMsg.alert('Сообщение', 'Форма "'+ win.title +'" доступна только для пользователей, с указанной группой «Регистр ИБС»');
				return false;
			}*/
		},
        'resize': function (win, nW, nH, oW, oH) {
			win.findById('IBSRW_SearchFilterTabbar').setWidth(nW - 5);
			win.findById('IBSRegistryFilterForm').setWidth(nW - 5);
		}
	},
	maximizable: true,
	minHeight: 550,
	minWidth: 800,
	modal: false,
	plain: true,
	resizable: true,
	show: function() {
		sw.Promed.swIBSRegistryWindow.superclass.show.apply(this, arguments);
		this.IBSRegistrySearchFrame.addActions({
			name:'action_export', 
			text:lang['vyigruzka_v_federalnyiy_registr'], 
			tooltip: lang['vyigruzka_v_federalnyiy_registr'],
			iconCls: '',
			hidden: true,
			disabled: true,
			handler: function() {
				this.openWindow('registry_export');
			}.createDelegate(this)
		});
		
		this.IBSRegistrySearchFrame.addActions({
			name:'person_register_dis', 
			text:lang['isklyuchit_iz_registra'], 
			tooltip: lang['isklyuchit_iz_registra'],
			iconCls: 'pers-disp16',
			handler: function() {
				this.openWindow('person_register_dis');
			}.createDelegate(this)
		});

		if (String(getGlobalOptions().groups).indexOf('IBSRegistry', 0) >= 0)
		{
			this.IBSRegistrySearchFrame.setActionHidden('action_add', false);
			this.IBSRegistrySearchFrame.setActionDisabled('action_edit',false);
			this.IBSRegistrySearchFrame.setActionHidden('action_edit',false);
			this.IBSRegistrySearchFrame.setActionHidden('person_register_dis',false);
			this.IBSRegistrySearchFrame.setActionHidden('action_delete',false);
		}
		else
		{
			this.IBSRegistrySearchFrame.setActionHidden('action_add', true);
			this.IBSRegistrySearchFrame.setActionDisabled('action_edit',true);
			this.IBSRegistrySearchFrame.setActionHidden('action_edit',true);
			this.IBSRegistrySearchFrame.setActionHidden('person_register_dis',true);
			this.IBSRegistrySearchFrame.setActionHidden('action_delete',true);
		}
		this.IBSRegistrySearchFrame.addActions({
			name:'open_emk', 
			text:lang['otkryit_emk'], 
			tooltip: lang['otkryit_elektronnuyu_meditsinskuyu_kartu_patsienta'],
			iconCls: 'open16',
			handler: function() {
				this.emkOpen();
			}.createDelegate(this)
		});
		
		var base_form = this.findById('IBSRegistryFilterForm').getForm();

		this.restore();
		this.center();
		this.maximize();
		this.doReset();
		//this.findById('IBSRW_SearchFilterTabbar').setActiveTab(0);
		
		this.editType = 'all';
		if(arguments[0] && arguments[0].editType)
		{
			this.editType = arguments[0].editType;
		}

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

		if(getRegionNick() != 'kareliya')
        	base_form.findField('AttachLpu_id').setValue(getGlobalOptions().lpu_id);
		/*if ( String(getGlobalOptions().groups).indexOf('IBSRegistry', 0) >= 0 ) {
			base_form.findField('AttachLpu_id').setDisabled(false);
		} else {
			base_form.findField('AttachLpu_id').setDisabled(true);
		}*/
		
		this.doLayout();
		
		//base_form.findField('PersonRegisterType_id').setValue(1);
		this.doSearch({firstLoad: true});
	},
	emkOpen: function()
	{
		var grid = this.IBSRegistrySearchFrame.getGrid();

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
			readOnly: (this.editType == 'onlyRegister')?true:false,
			callback: function()
			{
				//
			}.createDelegate(this)
		});
	},
	deletePersonRegister: function() {
		var grid = this.IBSRegistrySearchFrame.getGrid();
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
	title: lang['registr_ibs'],
	width: 800
});
