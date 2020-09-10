/**
 * swPersonRegisterSuicideListWindow - Регистр по суицидам
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * @package      PersonRegister
 * @access       public
 * @copyright    Copyright (c) 2009-2015 Swan Ltd.
 * @author       Alexander Chebukin
 * @version      07.2016
 * @comment      Префикс для id компонентов PRSLW
 */
sw.Promed.swPersonRegisterSuicideListWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	title: 'Регистр лиц, совершивших суицидальные попытки',
	PersonRegisterType_SysNick: 'suicide',
	width: 800,
	buttonAlign: 'left',
	closable: true,
	closeAction: 'hide',
	collapsible: true,
	getButtonSearch: function() {
		return Ext.getCmp('PRSLW_SearchButton');
	},
	doReset: function() {
		
		var base_form = this.findById('SuicideRegistryFilterForm').getForm();
		base_form.reset();
		this.SearchFrame.ViewActions.open_emk.setDisabled(true);
		this.SearchFrame.ViewActions.person_register_dis.setDisabled(true);
		this.SearchFrame.ViewActions.action_view.setDisabled(true);
		this.SearchFrame.ViewActions.action_delete.setDisabled(true);
		this.SearchFrame.ViewActions.action_refresh.setDisabled(true);
		this.SearchFrame.getGrid().getStore().removeAll();
	},
	doSearch: function(params) {
		
		if (typeof params != 'object') {
			params = {};
		}
		
		var base_form = this.findById('SuicideRegistryFilterForm').getForm();
		
		if ( !params.firstLoad && this.findById('SuicideRegistryFilterForm').isEmpty() ) {
			sw.swMsg.alert(lang['oshibka'], lang['ne_zapolneno_ni_odno_pole'], function() {
			});
			return false;
		}

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

		var post = getAllFormFieldValues(this.findById('SuicideRegistryFilterForm'));
		//post.DispLpu_id = base_form.findField('DispLpu_id').getValue();

		post.limit = 100;
		post.start = 0;
		post.PersonRegisterType_SysNick = this.PersonRegisterType_SysNick;
		
		//log(post);
		var grid = this.SearchFrame.getGrid();

		if ( base_form.isValid() ) {
			var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет поиск..."});
			loadMask.show();
			this.SearchFrame.ViewActions.action_refresh.setDisabled(false);
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
		if (!action || !action.toString().inlist(['person_register_dis','add','view','edit'])) {
			return false;
		}
		var me = this;
		var form = this.getFilterForm().getForm();
		var grid = this.SearchFrame.getGrid();
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
		var viewOnly = false;
		if(me.editType == 'onlyRegister')
			viewOnly = true;
		switch(action) {
			case 'add':
				if (getWnd('swPersonRegisterSuicideEditWindow').isVisible()) {
					getWnd('swPersonRegisterSuicideEditWindow').hide();
				}
				if ( getWnd('swPersonSearchWindow').isVisible() ) {
					getWnd('swPersonSearchWindow').hide();
				}
				getWnd('swPersonSearchWindow').show({
					viewOnly: viewOnly,
					onSelect: function(person_data) {
						getWnd('swPersonSearchWindow').hide();
						params.Person_id = person_data.Person_id;
						getWnd('swPersonRegisterSuicideEditWindow').show(params);
					},
					searchMode: 'all'
				});
				break;
            case 'edit':
			case 'view':
			case 'person_register_dis':
				if (getWnd('swPersonRegisterSuicideEditWindow').isVisible()) {
					getWnd('swPersonRegisterSuicideEditWindow').hide();
				}
				if ( Ext.isEmpty(selected_record.get('PersonRegister_id')) ) {
					sw.swMsg.alert(lang['soobschenie'], lang['oshibka_vyibora_zapisi']);
					return false;
				}
                params.PersonRegister_id = selected_record.data.PersonRegister_id;
				params.Person_id = selected_record.data.Person_id;
				if(action != 'person_register_dis')
					params.action = me.SearchFrame.getAction('action_edit').isHidden()?'view':'edit';
				getWnd('swPersonRegisterSuicideEditWindow').show(params);
				break;
		}
	},
	initComponent: function() {
		var me = this;
		this.SearchFrame = new sw.Promed.ViewFrame({
			actions: [
				{name: 'action_add', handler: function() { me.openWindow('add'); }},
                {name: 'action_edit', handler: function() { me.openWindow('edit'); }},
                {name: 'action_view', handler: function() { me.openWindow('view'); }},
				{name: 'action_delete', handler: this.deletePersonRegister.createDelegate(this)},
				{name: 'action_refresh'},
				{name: 'action_print', hidden: true, menuConfig: null,
                    handler: function()
                    {
						me.SearchFrame.printRecords();
                    }
                }
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 150,
			autoLoadData: false,
			dataUrl: C_SEARCH,
			id: 'PRSLW_SuicideRegistrySearchGrid',
			object: 'PersonRegisterBase',
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
				{name: 'PersonRegisterType_SysNick', type: 'string', hidden: true},
				{name: 'MorbusType_SysNick', type: 'string', hidden: true},
				{name: 'Morbus_id', type: 'int', hidden: true},
				{name: 'Person_id', type: 'int', hidden: true},
				{name: 'PersonEvn_id', type: 'int', hidden: true},
				{name: 'Server_id', type: 'int', hidden: true},
				{name: 'Person_Surname', type: 'string', header: lang['familiya'], width: 150},
				{name: 'Person_Firname', type: 'string', header: lang['imya'], width: 150},
				{name: 'Person_Secname', type: 'string', header: lang['otchestvo'], width: 150},
				{name: 'Person_Birthday', type: 'date', format: 'd.m.Y', header: lang['data_rojdeniya'], width: 90},
				{name: 'Lpu_Nick', type: 'string', header: lang['mo_prikr'], width: 150},
				{name: 'Diag_Name', type: 'string', header: lang['diagnoz_mkb-10'], width: 150, id: 'autoexpand'},
				{name: 'PersonRegister_setDate', type: 'date', format: 'd.m.Y', header: lang['data_vklyucheniya_v_registr'], width: 150},
				{name: 'PersonRegister_disDate', type: 'date', format: 'd.m.Y', header: lang['data_isklyucheniya_iz_registra'], width: 170},
				{name: 'PersonRegisterOutCause_id', type: 'int', hidden: true},
				{name: 'PersonRegisterOutCause_Name', type: 'string', header: lang['prichina_isklyucheniya_iz_registra'], width: 190}
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
				this.getAction('open_emk').setDisabled( Ext.isEmpty(record.get('PersonRegister_id')) );
				this.getAction('person_register_dis').setDisabled( Ext.isEmpty(record.get('PersonRegister_id')) || !Ext.isEmpty(record.get('PersonRegister_disDate')) /*|| getGlobalOptions().date == record.get('PersonRegister_setDate').format('d.m.Y')*/);
                this.getAction('action_delete').setDisabled( Ext.isEmpty(record.get('PersonRegister_id')) );
                this.getAction('action_edit').setDisabled( Ext.isEmpty(record.get('PersonRegister_id')) || !Ext.isEmpty(record.get('PersonRegister_disDate')) );
				this.getAction('action_view').setDisabled( Ext.isEmpty(record.get('PersonRegister_id')) );
            },
            onEnter: function() {
                var record = this.getGrid().getSelectionModel().getSelected();
                if (record && record.get('PersonRegister_id')) {
                    if (Ext.isEmpty(record.get('PersonRegister_disDate')) == false) {
                        this.getAction('action_view').execute();
                    } else {
                        this.getAction('action_edit').execute();
                    }
                }
            },
            onDblClick: function() {
                this.onEnter();
			}
		});

		this.SearchFilters = getBaseSearchFiltersFrame({
			allowPersonPeriodicSelect: true,
			id: 'SuicideRegistryFilterForm',
			labelWidth: 130,
			ownerWindow: this,
			searchFormType: 'PersonRegisterBase',
			tabPanelHeight: 235,
			tabPanelId: 'PRSLW_SearchFilterTabbar',
			tabs: [{
				autoHeight: true,
				bodyStyle: 'margin-top: 5px;',
				border: false,
				layout: 'form',
				labelWidth: 280,
				listeners: {
					'activate': function(panel) {
                        me.getFilterForm().getForm().findField('PersonRegister_setDate').focus(250, true);
					}
				},
				title: '6. Регистр',
				items: [{
					xtype: 'hidden',
					name: 'PersonRegisterType_id'
				}, {
					fieldLabel: 'Дата совершения суицидальной попытки',
					name: 'PersonRegister_setDate',
					plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
					width: 180,
					xtype: 'daterangefield'
				}, {
					fieldLabel: 'Способ совершения суицидальной попытки, с',
					hiddenName: 'Diag_Code_From',
					valueField: 'Diag_Code',
					width: 450,
					PersonRegisterType_SysNick: me.PersonRegisterType_SysNick,
					xtype: 'swdiagcombo'
				},{
					fieldLabel: 'по',
					hiddenName: 'Diag_Code_To',
					valueField: 'Diag_Code',
					width: 450,
					PersonRegisterType_SysNick: me.PersonRegisterType_SysNick,
					xtype: 'swdiagcombo'
				}]
			}]
		});

		Ext.apply(this, {
			buttons: [{
				handler: function() {
                    me.doSearch();
				},
				iconCls: 'search16',
				id: 'PRSLW_SearchButton',
				text: BTN_FRMSEARCH
			}, {
				handler: function() {
                    me.doReset();
				},
				iconCls: 'resetsearch16',
				text: BTN_FRMRESET
			}, {
				handler: function() {
                    me.getRecordsCount();
				},
				hidden: true,
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
                    me.findById('PRSLW_SearchFilterTabbar').getActiveTab().fireEvent('activate', me.findById('PRSLW_SearchFilterTabbar').getActiveTab());
				},
				text: BTN_FRMCLOSE
			}],
			getFilterForm: function() {
				if ( me.filterForm == undefined ) {
                    me.filterForm = me.findById('SuicideRegistryFilterForm');
				}
				return me.filterForm;
			},
			items: [ this.SearchFilters, this.SearchFrame ]
		});

		sw.Promed.swPersonRegisterSuicideListWindow.superclass.initComponent.apply(this, arguments);
		
	},
	layout: 'border',
	listeners: {
		'hide': function(win) {
			win.doReset();
		},
		'maximize': function(win) {
			win.findById('SuicideRegistryFilterForm').doLayout();
		},
		'restore': function(win) {
			win.findById('SuicideRegistryFilterForm').doLayout();
		},
		'beforeShow': function(win) {
			/*if (false == sw.Promed.personRegister.isSuicideRegistryOperator()) {
				sw.swMsg.alert('Сообщение', 'Форма "'+ win.title +'" доступна только для пользователей, с указанной группой "Регистр по суицидам"');
				return false;
			}
			return true;*/
		},
        'resize': function (win, nW, nH, oW, oH) {
			win.findById('PRSLW_SearchFilterTabbar').setWidth(nW - 5);
			win.findById('SuicideRegistryFilterForm').setWidth(nW - 5);
		}
	},
	maximizable: true,
	minHeight: 550,
	minWidth: 800,
	modal: false,
	plain: true,
	resizable: true,
	show: function() {
		sw.Promed.swPersonRegisterSuicideListWindow.superclass.show.apply(this, arguments);
		var me = this;
		
		this.SearchFrame.addActions({
			name:'person_register_dis', 
			text:lang['isklyuchit_iz_registra'], 
			tooltip: lang['isklyuchit_iz_registra'],
			iconCls: 'pers-disp16',
			handler: function() {
				me.openWindow('person_register_dis');
			}
		});
		
		this.SearchFrame.addActions({
			name:'open_emk', 
			text:lang['otkryit_emk'], 
			tooltip: lang['otkryit_elektronnuyu_meditsinskuyu_kartu_patsienta'],
			iconCls: 'open16',
			handler: function() {
				me.emkOpen();
			}
		});
		
		var base_form = this.findById('SuicideRegistryFilterForm').getForm();
		if(sw.Promed.personRegister.isSuicideRegistryOperator())
		{
			this.SearchFrame.setActionHidden('action_add', false);
			this.SearchFrame.setActionHidden('action_edit',false);
			this.SearchFrame.setActionHidden('action_delete',false);
			this.SearchFrame.setActionHidden('person_register_dis',false);
		}
		else
		{
			this.SearchFrame.setActionHidden('action_add', true);
			this.SearchFrame.setActionHidden('action_edit',true);
			this.SearchFrame.setActionHidden('action_delete',true);
			this.SearchFrame.setActionHidden('person_register_dis',true);
		}
		this.restore();
		this.center();
		this.maximize();
		this.doReset();
		this.editType = 'all';
		
		if(arguments[0] && arguments[0].editType)
		{
			this.editType = arguments[0].editType;
		}

		if (arguments[0].userMedStaffFact) {
			this.userMedStaffFact = arguments[0].userMedStaffFact;
		} 
		else {
			if (sw.Promed.MedStaffFactByUser.last) {
				this.userMedStaffFact = sw.Promed.MedStaffFactByUser.last;
			}
			else {
				sw.Promed.MedStaffFactByUser.selectARM({
					ARMType: arguments[0].ARMType,
					onSelect: function(data) {
						me.userMedStaffFact = data;
					}
				});
			}
		}

		this.doLayout();
		
		this.SearchFrame.setActionHidden('action_edit', true);
		base_form.findField('PersonRegisterType_id').setValue(21);
		this.doSearch({firstLoad: true});
	},
	emkOpen: function()
	{
		var grid = this.SearchFrame.getGrid();

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
		var grid = this.SearchFrame.getGrid();
		if ( !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('PersonRegister_id') )
		{
			Ext.Msg.alert(lang['oshibka'], lang['ne_vyibrana_zapis']);
			return false;
		}
		var record = grid.getSelectionModel().getSelected(),
			me = this;
		
		Ext.Msg.show({
			title: lang['vopros'],
			msg: lang['udalit_vyibrannuyu_zapis_registra'],
			buttons: Ext.Msg.YESNO,
			fn: function(btn) {
				if (btn === 'yes') {
					me.getLoadMask(lang['udalenie']).show();
					Ext.Ajax.request({
						url: '/?c=PersonRegister&m=delete',
						params: {
							PersonRegisterType_SysNick: me.PersonRegisterType_SysNick,
							PersonRegister_id: record.get('PersonRegister_id')
						},
						callback: function(options, success, response) {
							me.getLoadMask().hide();
							if (success) {	
								var obj = Ext.util.JSON.decode(response.responseText);
								if( obj.success )
									grid.getStore().remove(record);
							} else {
								sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_udalenii_zapisi_registra']);
							}
						}
					});
				}
			},
			icon: Ext.MessageBox.QUESTION
		});
	}
});
