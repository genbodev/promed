/**
 * swEvnNotifyProfListWindow - Журнал Извещений по нефрологии
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * @package      Prof
 * @access       public
 * @copyright    Copyright (c) 2009-2014 Swan Ltd.
 * @author       Dmitriy Vlasenko
 * @version      12.2014
 * @comment      Префикс для id компонентов ENNFLW
 */
sw.Promed.swEvnNotifyProfListWindow = Ext.extend(sw.Promed.BaseForm, {codeRefresh: true,
	buttonAlign: 'left',
	closable: true,
	PersonRegisterType_SysNick: 'prof',
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
		if ( !params.firstLoad && this.findById('EvnNotifyProfListFilterForm').isEmpty() ) {
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

		var post = getAllFormFieldValues(this.findById('EvnNotifyProfListFilterForm'));

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
					var EvnNotifyProf_id = selected_record.data.EvnNotifyProf_id;
					if (EvnNotifyProf_id) {
                        var s = '/?c=MorbusProf&m=doPrintEvnNotifyProf'
                            + '&EvnNotifyProf_id=' + EvnNotifyProf_id;
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
			id: 'ENNFLW_EvnNotifyProfListSearchGrid',
			object: 'EvnNotifyProfList',
			pageSize: 100,
			paging: true,
			region: 'center',
			root: 'data',
			stringfields: [
				{name: 'EvnNotifyProf_id', type: 'int', header: 'ID', key: true},
				{name: 'EvnNotifyProf_pid', type: 'int', hidden: true},
				{name: 'EvnNotifyProf_setDT', type: 'date', format: 'd.m.Y', header: lang['data_sozdaniya'], width: 120},	
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

				var disable = (Ext.isEmpty(record.get('Person_id')) || !Ext.isEmpty(record.get('PersonRegister_setDate')) || false == me.isProfRegistryUser());
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
                    me.filterForm = me.findById('EvnNotifyProfListFilterForm');
				}
				return me.filterForm;
			},
			items: [ getBaseSearchFiltersFrame({
				allowPersonPeriodicSelect: true,
				id: 'EvnNotifyProfListFilterForm',
				labelWidth: 130,
				ownerWindow: this,
				searchFormType: 'EvnNotifyProf',
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
						fieldLabel: lang['mo_sozdaniya_izvescheniya'],
						hiddenName: 'Lpu_did',
						valueField: 'Lpu_id',
						width: 310,
						xtype: 'swlpusearchcombo'
					}, {
						fieldLabel: lang['kod_diagnoza_s'],
						hiddenName: 'Diag_Code_From',
						valueField: 'Diag_Code',
						width: 450,
                        MorbusType_SysNick: 'prof',
						xtype: 'swdiagcombo'
					}, {
						fieldLabel: lang['po'],
						hiddenName: 'Diag_Code_To',
						valueField: 'Diag_Code',
						width: 450,
                        MorbusType_SysNick: 'prof',
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
						fieldLabel: lang['mesto_rabotyi'],
						hiddenName: 'OrgWork_id',
						onTrigger1Click: function() {
							var combo = this;
							if (combo.disabled) {
								return false;
							}

							getWnd('swOrgSearchWindow').show({
								enableOrgType: true,
								onSelect: function(orgData) {
									if ( orgData.Org_id > 0 )
									{
										combo.getStore().load({
											params: {
												Object:'Org',
												Org_id: orgData.Org_id,
												Org_Name:''
											},
											callback: function()
											{
												combo.setValue(orgData.Org_id);
												combo.focus(true, 500);
												combo.fireEvent('change', combo);
											}
										});
									}
									getWnd('swOrgSearchWindow').hide();
								},
								onClose: function() {combo.focus(true, 200)}
							});
						},
						width: 400,
						xtype: 'sworgcombo'
					}]
				}]
			}),
			this.RootViewFrame]
		});
		
		sw.Promed.swEvnNotifyProfListWindow.superclass.initComponent.apply(this, arguments);
		
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
		sw.Promed.swEvnNotifyProfListWindow.superclass.show.apply(this, arguments);

		this.RootViewFrame.addActions({
			name:'person_register_not_include', 
			text:lang['ne_vklyuchat_v_registr'], 
			tooltip: lang['ne_vklyuchat_v_registr'],
			iconCls: 'reset16',
			disabled: true,
			handler: function() {
				var record = this.RootViewFrame.getGrid().getSelectionModel().getSelected();
				if ( !record || !record.get('Person_id') )
				{
					Ext.Msg.alert(lang['oshibka'], lang['ne_vyibrana_zapis']);
					return false;
				}
				sw.swMsg.show(
					{
						buttons: Ext.Msg.YESNO,
						fn: function( buttonId )
						{
							if ( buttonId == 'yes' )
							{
								var option = {
									EvnNotifyBase_id: record.get('EvnNotifyProf_id'),
									PersonRegisterFailIncludeCause_id: 2, // Решение оператора
									ownerWindow: this,
									callback: function(){
										this.RootViewFrame.getAction('action_refresh').execute();
									}.createDelegate(this)
								};
								sw.Promed.personRegister.notInclude(option);
							}
						}.createDelegate(this),
						msg: lang['ne_vklyuchat_dannyie_po_vyibrannomu_napravleniyu_v_registr'],
						title: lang['vopros']
					});
			}.createDelegate(this)
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
        var lpu_combo = base_form.findField('Lpu_did');
        if (false == this.isProfRegistryUser()) {
			lpu_combo.setValue(getGlobalOptions().lpu_id);
        } else {
			lpu_combo.setValue(null);
        }
		lpu_combo.setDisabled(false == this.isProfRegistryUser());

		this.doLayout();
		this.doSearch({firstLoad: true});
    },
    isProfRegistryUser: function() {
        var isNotGroup = (String(getGlobalOptions().groups).indexOf('ProfRegistry', 0) < 0);
        return (false == isNotGroup);
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
		/*if ( 1 != record.get('NotifyType_id') ) {
			Ext.Msg.alert(lang['oshibka'], lang['oshibka_vyibora_zapisi']);
			return false;
		}*/
		/*var params = {
			EvnNotifyBase_id: record.get('EvnNotifyProf_id'),
			Person_id: record.get('Person_id'),
			Diag_id: record.get('Diag_id'),
            MorbusType_SysNick: 'prof',
			Morbus_id: record.get('Morbus_id'),
			ownerWindow: this,
			callback: function () {
				grid.getStore().reload();
			}
		};
		sw.Promed.personRegister.include(params);*/

		sw.Promed.personRegister.doInclude({
			EvnNotifyBase_id: record.get('EvnNotifyProf_id'),
			Person_id: record.get('Person_id'),	
            PersonRegisterType_SysNick: 'prof',
			Diag_id: record.get('Diag_id') || null,
			Morbus_id: record.get('Morbus_id') || null,
			ownerWindow: this,
            question: lang['vklyuchit_dannyie_po_vyibrannomu_napravleniyu_v_registr'],
			callback: function () {
				grid.getStore().reload();
			}
		});
	},
	/** Извещение: Просмотр
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
		if ( getWnd('swEvnNotifyProfEditWindow').isVisible() ) {
			getWnd('swEvnNotifyProfEditWindow').hide();
		}
		getWnd('swEvnNotifyProfEditWindow').show({
            action: 'view',
            EvnNotifyProf_id: record.get('EvnNotifyProf_id'),
            formParams: {
                EvnNotifyProf_id: record.get('EvnNotifyProf_id')
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
			ARMType: 'common',
			readOnly: getWnd('swWorkPlaceMZSpecWindow').isVisible()?true:false,
			callback: function()
			{
				//
			}.createDelegate(this)
		});
	},
	title: lang['jurnal_izvescheniy_po_profzabolevaniyam'],
	width: 800
});
