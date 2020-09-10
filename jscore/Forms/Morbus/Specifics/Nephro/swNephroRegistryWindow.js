/**
 * swNephroRegistryWindow - Регистр по нефрологии
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * @package      Nephro
 * @access       public
 * @copyright    Copyright (c) 2009-2014 Swan Ltd.
 * @author       Alexander Permyakov
 * @version      11.2014
 * @comment      Префикс для id компонентов NFRW
 */
sw.Promed.swNephroRegistryWindow = Ext.extend(sw.Promed.BaseForm, {codeRefresh: true,
	buttonAlign: 'left',
	closable: true,
	closeAction: 'hide',
	collapsible: true,
	getButtonSearch: function() {
		return Ext.getCmp('NFRW_SearchButton');
	},
	doReset: function() {

		var base_form = this.findById('NephroRegistryFilterForm').getForm();
		base_form.reset();
		this.NephroRegistrySearchFrame.ViewActions.open_emk.setDisabled(true);
		this.NephroRegistrySearchFrame.ViewActions.person_register_dis.setDisabled(true);
		this.NephroRegistrySearchFrame.ViewActions.action_view.setDisabled(true);
		this.NephroRegistrySearchFrame.ViewActions.action_delete.setDisabled(true);
		this.NephroRegistrySearchFrame.ViewActions.action_refresh.setDisabled(true);
		this.NephroRegistrySearchFrame.getGrid().getStore().removeAll();
	},
	doSearch: function(params) {

		if (typeof params != 'object') {
			params = {};
		}

		var base_form = this.findById('NephroRegistryFilterForm').getForm();

		if ( !params.firstLoad && this.findById('NephroRegistryFilterForm').isEmpty() ) {
			sw.swMsg.alert(lang['oshibka'], lang['ne_zapolneno_ni_odno_pole'], function() {
			});
			return false;
		}

		var grid = this.NephroRegistrySearchFrame.getGrid();

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

		var post = getAllFormFieldValues(this.findById('NephroRegistryFilterForm'));
		//post.DispLpu_id = base_form.findField('DispLpu_id').getValue();

		post.limit = 100;
		post.start = 0;

		//log(post);

		if ( base_form.isValid() ) {
			this.NephroRegistrySearchFrame.ViewActions.action_refresh.setDisabled(false);
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
		var grid = this.NephroRegistrySearchFrame.getGrid();
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
					,MorbusType_SysNick: 'nephro'
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
					MorbusType_SysNick: 'nephro'
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
				if (getWnd('swMorbusNephroWindow').isVisible()) {
					getWnd('swMorbusNephroWindow').hide();
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
				params.action = cur_win.NephroRegistrySearchFrame.getAction('action_edit').isHidden()?'view':'edit';
				getWnd('swMorbusNephroWindow').show(params);
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
		this.NephroRegistrySearchFrame = new sw.Promed.ViewFrame({
			actions: [
				{name: 'action_add', handler: function() { me.openWindow('add'); }},
				{name: 'action_edit', handler: function() { me.openWindow('edit'); }},
				{name: 'action_view', handler: function() { me.openWindow('view'); }},
				{name: 'action_delete', handler: this.deletePersonRegister.createDelegate(this)},
				{name: 'action_refresh'},
				{name: 'action_print',
					menuConfig: {
						printObject: {
							name: 'printObject', text: lang['pechat'], handler: function () {
								me.NephroRegistrySearchFrame.printObject()
							}
						},
						printObjectList: {
							name: 'printObjectList',
							text: lang['pechat_tekuschey_stranitsyi'],
							handler: function () {
								me.NephroRegistrySearchFrame.printObjectList()
							}
						},
						printObjectListFull: {
							name: 'printObjectListFull',
							text: lang['pechat_vsego_spiska'],
							handler: function () {
								me.NephroRegistrySearchFrame.printObjectListFull()
							}
						}
					}
				}
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 150,
			autoLoadData: false,
			dataUrl: C_SEARCH,
			id: 'NFRW_NephroRegistrySearchGrid',
			object: 'NephroRegistry',
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
				{name: 'MorbusNephro_id', type: 'int', hidden: true},
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
				{name: 'Diab_Diag_Code', type: 'string', header: lang['saharnyiy_diabet_perviy_vtoroy_tip'], width: 150},
				{name: 'PersonRegister_setDate', type: 'date', format: 'd.m.Y', header: lang['data_vklyucheniya_v_registr'], width: 150},
				{name: 'lastVizitNefroDate', type: 'date', format: 'd.m.Y', header: lang['data_priema_nefrologa'], width: 170},
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
				if(getRegionNick() == 'perm') {
					this.getAction('person_register_dis').setDisabled(Ext.isEmpty(record.get('PersonRegister_disDate')) == false);
				}

				isEmptyPR_id = Ext.isEmpty(record.get('PersonRegister_id'));
				isDisabledDel = !getRegionNick().inlist(['ufa','buryatiya']) ? isEmptyPR_id : !isUserGroup('SuperAdmin') || isEmptyPR_id
				this.getAction('action_delete').setDisabled( isDisabledDel );

				this.getAction('action_edit').setDisabled( Ext.isEmpty(record.get('Morbus_id')) || Ext.isEmpty(record.get('PersonRegister_disDate')) == false );
				this.getAction('action_view').setDisabled( Ext.isEmpty(record.get('Morbus_id')) );
			},
			onEnter: function() {
				var record = this.getGrid().getSelectionModel().getSelected();
				if (record && record.get('Morbus_id')) {
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
			id: 'NephroRegistryFilterForm',
			labelWidth: 130,
			ownerWindow: this,
			searchFormType: 'NephroRegistry',
			tabPanelHeight: 235,
			tabPanelId: 'NFRW_SearchFilterTabbar',
			tabs: [{
				autoHeight: true,
				bodyStyle: 'margin-top: 5px;',
				border: false,
				labelWidth: 220,
				layout: 'column',
				listeners: {
					'activate': function(panel) {
						var form = me.getFilterForm().getForm();
						form.findField('PersonRegisterType_id').focus(250, true);
					}
				},
				title: lang['6_registr'],
				items: [{
					layout: 'form',
					border: false,
					items: [{
						xtype: 'swpersonregistertypecombo',
						hiddenName: 'PersonRegisterType_id',
						fieldLabel: lang['tip_zapisi_registra'],
						width: 200
					}, {
						fieldLabel: lang['data_vklyucheniya_v_registr'],
						name: 'PersonRegister_setDate_Range',
						plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
						width: 200,
						xtype: 'daterangefield'
					}, {
						fieldLabel: lang['data_isklyucheniya_iz_registra'],
						name: 'PersonRegister_disDate_Range',
						plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
						width: 200,
						xtype: 'daterangefield'
					}, {
						xtype: 'swcommonsprcombo',
						comboSubject: 'NephroCRIType',
						fieldLabel: lang['stadiya_hbp'],
						width: 200
					}, {
						xtype: 'daterangefield',
						fieldLabel: lang['poseschenie_nefrologa_s'],
						name: 'PersonVisit_Date_Range',
						plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
						width: 200,
						listeners: {
							render: function () {
								if(getRegionNick() != 'perm') {
									this.setContainerVisible(false);
								}
							}
						}
					}, {
						hiddenName: 'MonthsWithoutNefroVisit',
						fieldLabel: lang['net_visitov_k_nefrologu'],
						width: 200,
						xtype: 'swmonthswithoutnefrovisitcombo',
						listeners: {
							render: function () {
								if(getRegionNick() != 'perm') {
									this.setContainerVisible(false);
								}
							}
						}
					}]
				}, {
					layout: 'form',
					border: false,
					labelWidth: 260,
					hidden: getRegionNick() != 'ufa',
					defaults: {
						disabled:   getRegionNick() != 'ufa'
					},
					items: [{
						xtype: 'swlpucombo',
						name: 'DialysisCenter_id',
						hiddenName: 'DialysisCenter_id',
						fieldLabel: langs('Диализный центр прикрепления'),
						listWidth: 300,
						listeners: {
							'render': function() {
								this.store.insert(0, [new Ext.data.Record({Lpu_id: -1, Lpu_Nick: 'Не указано'})]);
							}
						}
					}, new sw.Promed.SwCommonSprCombo({
						fieldLabel: langs('Статус пациента'),
						name: 'NephroPersonStatus_id',
						comboSubject: 'NephroPersonStatus',
						listWidth: 300
					}), { //#142318
						fieldLabel: langs('Пациенты, включенные в регистр, на  дату'),
						xtype:      'swdatefield',
						maxValue:   new Date(),
						name:       'PersonCountAtDate'
					}, {
						fieldLabel: langs('Дата начала диализа'),
						xtype: 'daterangefield',
						plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false) ],
						width: 180,
						name: 'MorbusNephro_DialDate_Range'
					}, {
						fieldLabel: langs('Дата окончания диализа'),
						xtype: 'daterangefield',
						plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false) ],
						width: 180,
						name: 'MorbusNephro_DialEndDate_Range'
					}]
				}]
			}, {
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
					fieldLabel: lang['diagnoz_s'],
					hiddenName: 'Diag_Code_From',
					valueField: 'Diag_Code',
					width: 450,
					MorbusType_SysNick: 'nephro',
					xtype: 'swdiagcombo'
				},{
					fieldLabel: lang['po'],
					hiddenName: 'Diag_Code_To',
					valueField: 'Diag_Code',
					width: 450,
					MorbusType_SysNick: 'nephro',
					xtype: 'swdiagcombo'
				},
				{
					fieldLabel: lang['saharnyiy_diabet_s'],
					hiddenName: 'Diab_Diag_Code_From',
					valueField: 'Diag_Code',
					width: 450,
					MorbusType_SysNick: 'diabetes',
					xtype: 'swdiagcombo',
					baseFilterFn: function(rec){
						//только диагнозы группы Е10 (сах. диабет I типа) или Е11 (сах. диабет II типа).
						var Diag_Code = rec.attributes ? rec.attributes.Diag_Code : rec.get('Diag_Code');
						return ((Diag_Code.substr(0, 3) == 'E10' || Diag_Code.substr(0, 3) == 'E11'));
					},
					listeners: {
						render: function () {
							if(getRegionNick() != 'perm') {
								this.setContainerVisible(false);
							}
						}
					}
				}, {
					fieldLabel: lang['po'],
					hiddenName: 'Diab_Diag_Code_To',
					valueField: 'Diag_Code',
					width: 450,
					MorbusType_SysNick: 'diabetes',
					xtype: 'swdiagcombo',
					baseFilterFn: function(rec){
						//только диагнозы группы Е10 (сах. диабет I типа) или Е11 (сах. диабет II типа).
						var Diag_Code = rec.attributes ? rec.attributes.Diag_Code : rec.get('Diag_Code');
						return ((Diag_Code.substr(0, 3) == 'E10' || Diag_Code.substr(0, 3) == 'E11'));
					},
					listeners: {
						render: function () {
							if(getRegionNick() != 'perm') {
								this.setContainerVisible(false);
							}
						}
					}
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
					me.doSearch();
				},
				iconCls: 'search16',
				id: 'NFRW_SearchButton',
				text: BTN_FRMSEARCH
			}, {
				handler: function() {
					me.doReset();
				},
				iconCls: 'resetsearch16',
				text: BTN_FRMRESET
			}, /*{
				handler: function() {
					me.NephroRegistrySearchFrame.printRecords();
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
					me.findById('NFRW_SearchFilterTabbar').getActiveTab().fireEvent('activate', me.findById('NFRW_SearchFilterTabbar').getActiveTab());
				},
				text: BTN_FRMCLOSE
			}],
			getFilterForm: function() {
				if ( me.filterForm == undefined ) {
					me.filterForm = me.findById('NephroRegistryFilterForm');
				}
				return me.filterForm;
			},
			items: [ this.SearchFilters, this.NephroRegistrySearchFrame]
		});

		sw.Promed.swNephroRegistryWindow.superclass.initComponent.apply(this, arguments);

	},
	layout: 'border',
	listeners: {
		'hide': function(win) {
			win.doReset();
		},
		'maximize': function(win) {
			win.findById('NephroRegistryFilterForm').doLayout();
		},
		'restore': function(win) {
			win.findById('NephroRegistryFilterForm').doLayout();
		},
		'resize': function (win, nW, nH, oW, oH) {
			win.findById('NFRW_SearchFilterTabbar').setWidth(nW - 5);
			win.findById('NephroRegistryFilterForm').setWidth(nW - 5);
		}
	},
	maximizable: true,
	minHeight: 550,
	minWidth: 800,
	modal: false,
	plain: true,
	resizable: true,
	show: function() {
		sw.Promed.swNephroRegistryWindow.superclass.show.apply(this, arguments);
		this.NephroRegistrySearchFrame.addActions({
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

		this.NephroRegistrySearchFrame.addActions({
			name:'person_register_dis',
			text:lang['isklyuchit_iz_registra'],
			tooltip: lang['isklyuchit_iz_registra'],
			iconCls: 'pers-disp16',
			handler: function() {
				this.openWindow('person_register_dis');
			}.createDelegate(this)
		});

		this.NephroRegistrySearchFrame.addActions({
			name:'open_emk',
			text:lang['otkryit_emk'],
			tooltip: lang['otkryit_elektronnuyu_meditsinskuyu_kartu_patsienta'],
			iconCls: 'open16',
			handler: function() {
				this.emkOpen();
			}.createDelegate(this)
		});
		var win = this;
		if(!getWnd('swWorkPlaceMZSpecWindow').isVisible() && !isUserGroup('MIACSuperAdmin') && !(arguments[0] && arguments[0].fromARM && arguments[0].fromARM !== null && arguments[0].fromARM.inlist(['minzdravdlo','spec_mz','mzchieffreelancer']))){
			if (String(getGlobalOptions().groups).indexOf('NephroRegistry', 0) < 0) {
				sw.swMsg.alert('Сообщение', 'Форма "'+ win.title +'" доступна только для пользователей, с указанной группой «Регистр по нефрологии»');
				return false;
			}
		}

		var base_form = this.findById('NephroRegistryFilterForm').getForm();

		if(String(getGlobalOptions().groups).indexOf('NephroRegistry', 0) >= 0){
			this.NephroRegistrySearchFrame.setActionHidden('action_add', false);
			this.NephroRegistrySearchFrame.setActionDisabled('action_edit',false);
			this.NephroRegistrySearchFrame.setActionHidden('action_edit',false);
			this.NephroRegistrySearchFrame.setActionHidden('action_delete',false);
			if(getRegionNick() == 'perm') {
				this.NephroRegistrySearchFrame.setActionHidden('person_register_dis', false);
			}
		}
		else
		{
			this.NephroRegistrySearchFrame.setActionHidden('action_add', true);
			this.NephroRegistrySearchFrame.setActionDisabled('action_edit',true);
			this.NephroRegistrySearchFrame.setActionHidden('action_edit',true);
			this.NephroRegistrySearchFrame.setActionHidden('action_delete',true);
			this.NephroRegistrySearchFrame.setActionHidden('person_register_dis',true);
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
		//this.findById('NFRW_SearchFilterTabbar').setActiveTab(0);
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

		/*base_form.findField('AttachLpu_id').setValue(getGlobalOptions().lpu_id);
		if ( String(getGlobalOptions().groups).indexOf('NephroRegistry', 0) >= 0 ) {
			base_form.findField('AttachLpu_id').setDisabled(false);
		} else {
			base_form.findField('AttachLpu_id').setDisabled(true);
		}*/

		this.doLayout();

		base_form.findField('PersonRegisterType_id').setValue(1);
//		this.doSearch({firstLoad: true});

		if(getRegionNick() == 'ufa') {
			this.SearchFilters.getForm().findField('PersonRegisterType_id').store = new Ext.data.SimpleStore({
				data: [
					['1',langs('Все')],
					['2',langs('Додиализный регистр')],
					['3',langs('Диализный регистр')],
					['4',langs('Регистр трансплантации')],
					['5',langs('Исключенные из регистра')]
				],
				editable: false,
				key: 'PersonRegisterType_id',
				autoLoad: false,
				fields: [
					{name: 'PersonRegisterType_id', type:'int'},
					{name: 'PersonRegisterType_Name', type:'string'}
				]
			});
		}
	},
	emkOpen: function()
	{
		var grid = this.NephroRegistrySearchFrame.getGrid();

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
		var grid = this.NephroRegistrySearchFrame.getGrid();
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
	title: lang['registr_po_nefrologii'],
	width: 800
});
