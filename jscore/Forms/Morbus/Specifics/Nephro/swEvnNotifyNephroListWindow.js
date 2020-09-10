/**
 * swEvnNotifyNephroListWindow - Журнал Извещений по нефрологии
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * @package      Nephro
 * @access       public
 * @copyright    Copyright (c) 2009-2014 Swan Ltd.
 * @author       Alexander Permyakov
 * @version      11.2014
 * @comment      Префикс для id компонентов ENNFLW
 */
sw.Promed.swEvnNotifyNephroListWindow = Ext.extend(sw.Promed.BaseForm, {codeRefresh: true,
	buttonAlign: 'left',
	closable: true,
	closeAction: 'hide',
	collapsible: true,
	getButtonSearch: function() {
		return Ext.getCmp('ENNFLW_SearchButton');
	},
	doReset: function() {
		
		var base_form = this.getFilterForm().getForm();
		base_form.reset();
		this.RootViewFrame.ViewActions.open_emk.setDisabled(true);
        this.RootViewFrame.ViewActions.action_view.setDisabled(true);
        this.RootViewFrame.ViewActions.action_delete.setDisabled(true);
		this.RootViewFrame.ViewActions.person_register_include.setDisabled(true);
		this.RootViewFrame.ViewActions.person_register_not_include.setDisabled(true);
		this.RootViewFrame.ViewActions.action_refresh.setDisabled(true);
		this.RootViewFrame.getGrid().getStore().removeAll();
				
	},
	doSearch: function(params) {
		
		var base_form = this.getFilterForm().getForm();
		
		if (typeof params != 'object') {
			params = {};
		}
		if ( !params.firstLoad && this.findById('EvnNotifyNephroListFilterForm').isEmpty() ) {
			sw.swMsg.alert(lang['oshibka'], lang['ne_zapolneno_ni_odno_pole'], function() {
			});
			return false;
		}
		
		var grid = this.RootViewFrame.getGrid();

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

		var post = getAllFormFieldValues(this.findById('EvnNotifyNephroListFilterForm'));

		post.limit = 100;
		post.start = 0;
		
		//log(post);

		if ( base_form.isValid() ) {
			this.RootViewFrame.ViewActions.action_refresh.setDisabled(false);
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
	initComponent: function() {
		var me = this;
		this.RootViewFrame = new sw.Promed.ViewFrame({
			actions: [
				{name: 'action_add', hidden: true},
				{name: 'action_edit', hidden: true},
				{name: 'action_view', handler: function() {
                    me.openViewWindow();
				}},
				{name: 'action_delete'},
				{name: 'action_refresh'},
				{name: 'action_print', handler: function() {
					var selected_record = me.RootViewFrame.getGrid().getSelectionModel().getSelected();
					if (!selected_record) {
						return false;
					}
					var EvnNotifyNephro_id = selected_record.data.EvnNotifyNephro_id;
					if (EvnNotifyNephro_id) {
                        var s = '/?c=MorbusNephro&m=doPrintEvnNotifyNephro'
                            + '&EvnNotifyNephro_id=' + EvnNotifyNephro_id;
                        window.open(s, '_blank');
                        return true;
					}
                    return false;
				} }
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 150,
			autoLoadData: false,
			dataUrl: C_SEARCH,
			id: 'ENNFLW_EvnNotifyNephroListSearchGrid',
			object: 'EvnNotifyNephro',
			pageSize: 100,
			paging: true,
			region: 'center',
			root: 'data',
			stringfields: [
				{name: 'EvnNotifyNephro_id', type: 'int', header: 'ID', key: true},
				{name: 'EvnNotifyNephro_pid', type: 'int', hidden: true},
				{name: 'EvnNotifyNephro_setDT', type: 'date', format: 'd.m.Y', header: lang['data_sozdaniya'], width: 120},	
				{name: 'Person_id', type: 'int', hidden: true},			
				{name: 'Server_id', type: 'int', hidden: true},			
				{name: 'PersonEvn_id', type: 'int', hidden: true},	
				{name: 'Morbus_id', type: 'int', hidden: true},
				{name: 'Person_Surname', type: 'string', header: lang['familiya'], width: 120},
				{name: 'Person_Firname', type: 'string', header: lang['imya'], width: 120},
				{name: 'Person_Secname', type: 'string', header: lang['otchestvo'], width: 120},
				{name: 'Person_Birthday', type: 'date', format: 'd.m.Y', header: lang['data_rojdeniya'], width: 90},
				{name: 'Lpu_Nick', type: 'string', header: lang['mo_prikr'], width: 150},
				{name: 'Diag_Name', type: 'string', header: lang['diagnoz_mkb-10'], width: 150, id: 'autoexpand'},
				{name: 'Diag_id', type: 'int', hidden: true},
				{name: 'PersonRegister_setDate', type: 'date', format: 'd.m.Y', header: lang['data_vkl_nevkl_v_registr'], width: 180},	
				{name: 'MedPersonal_id', type: 'int', hidden: true},
				{name: 'pmUser_updId', type: 'int', hidden: true}
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
				this.getAction('open_emk').setDisabled( Ext.isEmpty(record.get('Person_id')));
				this.getAction('action_view').setDisabled( Ext.isEmpty(record.get('Person_id')));

				var disable = (Ext.isEmpty(record.get('Person_id')) || !Ext.isEmpty(record.get('PersonRegister_setDate')) || false == me.isNephroRegistryUser());
				this.getAction('person_register_include').setDisabled(disable);
				this.getAction('person_register_not_include').setDisabled(disable);
                this.getAction('action_delete').setDisabled(disable);
			},
			onDblClick: function(sm,index,record) {
				if(!Ext.isEmpty(record.get('Person_id'))) {
					this.getAction('action_view').execute();
				}
			}
		});
		

		Ext.apply(this, {
			buttons: [{
				handler: function() {
                    me.doSearch();
				},
				iconCls: 'search16',
				id: 'ENNFLW_SearchButton',
				text: BTN_FRMSEARCH
			}, {
				handler: function() {
                    me.doReset();
				},
				iconCls: 'resetsearch16',
				text: BTN_FRMRESET
			}, {
				handler: function() {
                    me.RootViewFrame.printRecords();
				},
				iconCls: 'print16',
				text: lang['pechat_spiska']
			}, {
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
                    me.findById('ENNFLW_SearchFilterTabbar').getActiveTab().fireEvent('activate', me.findById('ENNFLW_SearchFilterTabbar').getActiveTab());
				},
				text: BTN_FRMCLOSE
			}],
			getFilterForm: function() {
				if ( me.filterForm == undefined ) {
                    me.filterForm = me.findById('EvnNotifyNephroListFilterForm');
				}
				return me.filterForm;
			},
			items: [ getBaseSearchFiltersFrame({
				allowPersonPeriodicSelect: true,
				id: 'EvnNotifyNephroListFilterForm',
				labelWidth: 130,
				ownerWindow: this,
				searchFormType: 'EvnNotifyNephro',
				tabPanelHeight: 235,
				tabPanelId: 'ENNFLW_SearchFilterTabbar',
				tabs: [{
					autoHeight: true,
					bodyStyle: 'margin-top: 5px;',
					border: false,
					labelWidth: 220,
					layout: 'form',
					listeners: {
						'activate': function(panel) {
							var form = me.getFilterForm().getForm();
							form.findField('Diag_Code_From').focus(250, true);
						}
					},
					title: lang['6_izveschenie'],
					items: [{
						fieldLabel: lang['kod_diagnoza_s'],
						hiddenName: 'Diag_Code_From',
						valueField: 'Diag_Code',
						width: 450,
                        MorbusType_SysNick: 'nephro',
						xtype: 'swdiagcombo'
					}, {
						fieldLabel: lang['po'],
						hiddenName: 'Diag_Code_To',
						valueField: 'Diag_Code',
						width: 450,
                        MorbusType_SysNick: 'nephro',
						xtype: 'swdiagcombo'
					}, {
						fieldLabel: lang['data_zapolneniya_izvescheniya'],
						name: 'EvnNotifyBase_setDT_Range',
						plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
						width: 180,
						xtype: 'daterangefield'
					}, {
						fieldLabel: lang['izveschenie_obrabotano'],
						xtype: 'swyesnocombo',
						width: 120,
						hiddenName: 'isNotifyProcessed'
					}, {
						fieldLabel: lang['neyavka_pacienta_v_techeniye_mesyaca'],
						xtype: 'swyesnocombo',
						width: 120,
						hiddenName: 'isNotVizitMonth',
						hidden: (getRegionNick() != 'perm')
					}]
				}]
			}),
			this.RootViewFrame]
		});
		
		sw.Promed.swEvnNotifyNephroListWindow.superclass.initComponent.apply(this, arguments);
		
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
			win.findById('ENNFLW_SearchFilterTabbar').setWidth(nW - 5);
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
		sw.Promed.swEvnNotifyNephroListWindow.superclass.show.apply(this, arguments);

		this.RootViewFrame.addActions({
			name:'person_register_not_include', 
			text:lang['ne_vklyuchat_v_registr'], 
			tooltip: lang['ne_vklyuchat_v_registr'],
			iconCls: 'reset16',
			disabled: true,
			menu: new Ext.menu.Menu({id:'ENNFLW_personRegisterNotIncludeMenu'})
		});
		
		this.RootViewFrame.addActions({
			name:'person_register_include', 
			text:lang['vklyuchit_v_registr'], 
			tooltip: lang['vklyuchit_v_registr'],
			iconCls: 'ok16',
			disabled: true,
			handler: function() {
				this.personRegisterInclude();
			}.createDelegate(this)
		});
		
		this.RootViewFrame.addActions({
			name:'open_emk', 
			text:lang['otkryit_emk'], 
			tooltip: lang['otkryit_elektronnuyu_meditsinskuyu_kartu_patsienta'],
			iconCls: 'open16',
			handler: function() {
				this.emkOpen();
			}.createDelegate(this)
		});
		
		var base_form = this.getFilterForm().getForm();

		this.restore();
		this.center();
		this.maximize();
		this.doReset();		
		
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
        var attach_lpu_combo = base_form.findField('AttachLpu_id');
        if (false == this.isNephroRegistryUser()) {
            attach_lpu_combo.setValue(getGlobalOptions().lpu_id);
        } else {
            attach_lpu_combo.setValue(null);
        }
        attach_lpu_combo.setDisabled(false == this.isNephroRegistryUser());
        
        if(getWnd('swWorkPlaceMZSpecWindow').isVisible())
        {
        	attach_lpu_combo.setValue(null);
        	attach_lpu_combo.setDisabled(false);
        }

		this.doLayout();
		this.setMenu(true);
//		this.doSearch({firstLoad: true});
    },
    isNephroRegistryUser: function() {
        var isNotGroup = (String(getGlobalOptions().groups).indexOf('NephroRegistry', 0) < 0);
        return (false == isNotGroup);
	},
	/** Создание меню
	 */
	setMenu: function(is_first) {
		if (is_first) {
			this.createPersonRegisterFailIncludeCauseMenu();
		}
	},
	/** Создание меню причин не включения в регистр
	 */
	createPersonRegisterFailIncludeCauseMenu: function() {
		sw.Promed.personRegister.createPersonRegisterFailIncludeCauseMenu({
			id: 'ENNFLW_personRegisterNotIncludeMenu',
			ownerWindow: this,
			getParams: function(){
				var record = this.RootViewFrame.getGrid().getSelectionModel().getSelected();
				if ( !record || !record.get('Person_id') )
				{
					Ext.Msg.alert(lang['oshibka'], lang['ne_vyibrana_zapis']);
					return false;
				}
				return {
					EvnNotifyBase_id: record.get('EvnNotifyNephro_id')
				};
			}.createDelegate(this),
			onCreate: function(menu){
				var a = this.RootViewFrame.getAction('person_register_not_include');
				a.items[0].menu = menu;
				a.items[1].menu = menu;
			}.createDelegate(this),
			callback: function(){
				this.RootViewFrame.getAction('action_refresh').execute();
			}.createDelegate(this)
		});
	},
	/** Включить в регистр
	 */
	personRegisterInclude: function()
	{
		var grid = this.RootViewFrame.getGrid();
		var record = grid.getSelectionModel().getSelected();
		if ( !record || !record.get('Person_id') )
		{
			Ext.Msg.alert(lang['oshibka'], lang['ne_vyibrana_zapis']);
			return false;
		}
		var params = {
			EvnNotifyBase_id: record.get('EvnNotifyNephro_id'),
			Person_id: record.get('Person_id'),
			Diag_id: record.get('Diag_id'),
            MorbusType_SysNick: 'nephro',
			Morbus_id: record.get('Morbus_id'),
			ownerWindow: this,
            question: lang['vklyuchit_patsienta_po_vyibrannomu_izvescheniyu_v_registr'],
			callback: function () {
				grid.getStore().reload();
			}
		};
		sw.Promed.personRegister.include(params);
	},
	/** +lang['izveschenie']+ +lang['prosmotr']+
	 */
	openViewWindow: function()
	{
		var grid = this.RootViewFrame.getGrid();
		var record = grid.getSelectionModel().getSelected();
		if ( !record || !record.get('Person_id') )
		{
			Ext.Msg.alert(lang['oshibka'], lang['ne_vyibrana_zapis']);
			return false;
		}
		if ( getWnd('swEvnNotifyNephroEditWindow').isVisible() ) {
			getWnd('swEvnNotifyNephroEditWindow').hide();
		}
		getWnd('swEvnNotifyNephroEditWindow').show({
            action: 'view',
            EvnNotifyNephro_id: record.get('EvnNotifyNephro_id'),
            formParams: {
                EvnNotifyNephro_id: record.get('EvnNotifyNephro_id')
            }
        });
	},
	emkOpen: function()
	{
		var grid = this.RootViewFrame.getGrid();

		var record = grid.getSelectionModel().getSelected();
		if ( !record || !record.get('Person_id') )
		{
			Ext.Msg.alert(lang['oshibka'], lang['ne_vyibrana_zapis']);
			return false;
		}
		
		getWnd('swPersonEmkWindow').show({
			Person_id: record.get('Person_id'),
			Server_id: record.get('Server_id'),
			PersonEvn_id: record.get('PersonEvn_id'),
			userMedStaffFact: this.userMedStaffFact,
			MedStaffFact_id: this.userMedStaffFact.MedStaffFact_id,
			LpuSection_id: this.userMedStaffFact.LpuSection_id,
			readOnly: getWnd('swWorkPlaceMZSpecWindow').isVisible()?true:false,
			ARMType: 'common',
			callback: function()
			{
				//
			}.createDelegate(this)
		});
	},
	title: lang['jurnal_izvescheniy_po_nefrologii'],
	width: 800
});
