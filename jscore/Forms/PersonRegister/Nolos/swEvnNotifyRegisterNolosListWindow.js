/**
 * swEvnNotifyRegisterNolosListWindow - Журнал Извещений/Направлений по ВЗН (7 нозологиям)
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * @package      PersonRegister
 * @access       public
 * @copyright    Copyright (c) 2009-2014 Swan Ltd.
 * @author       Alexander Permyakov
 * @version      02.2015
 * @comment      Префикс для id компонентов ENRNLW
 */
sw.Promed.swEvnNotifyRegisterNolosListWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	title: lang['jurnal_izvescheniy_napravleniy_po_vzn'],
	PersonRegisterType_SysNick: 'nolos',
	width: 800,
	buttonAlign: 'left',
	closable: true,
	closeAction: 'hide',
	collapsible: true,
	getButtonSearch: function() {
		return Ext.getCmp('ENRNLW_SearchButton');
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
		base_form.findField('NotifyType_id').setValue('0');
	},
	doSearch: function(params) {
		
		var base_form = this.getFilterForm().getForm();
		
		if (typeof params != 'object') {
			params = {};
		}
		if ( !params.firstLoad && this.findById('EvnNotifyRegisterNolosListFilterForm').isEmpty() ) {
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

		var post = getAllFormFieldValues(this.findById('EvnNotifyRegisterNolosListFilterForm'));

		post.limit = 100;
		post.start = 0;
		post.PersonRegisterType_SysNick = this.PersonRegisterType_SysNick;
		
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
	height: 550,
	initComponent: function() {
		var me = this;
		this.RootViewFrame = new sw.Promed.ViewFrame({
			actions: [
				{name: 'action_add', tooltip: lang['sozdat_napravlenie_na_vklyuchenie_v_registr'], handler: function() {
                    me.openEvnNotifyRegisterIncludeWindow();
				}},
				{name: 'action_edit',  handler: function() {
                    me.openEvnNotifyRegisterIncludeWindow('edit');
				}},
				{name: 'action_view', hidden: true, disabled: true},
				{name: 'action_delete', handler: function() {
                    me.delEvnNotifyRegister();
				}},
				{name: 'action_refresh'},
				{name: 'action_print', handler: function() {
					var selected_record = me.RootViewFrame.getGrid().getSelectionModel().getSelected();
					if (!selected_record) {
						return false;
					}
					var id = selected_record.data.EvnNotifyRegister_id;
					if (id) {
                        /*var s = '/?c=PersonRegister&m=printEvnNotifyRegister'
                            + '&EvnNotifyRegister_id=' + id
                            + '&NotifyType_id=' + selected_record.data.NotifyType_id
                            + '&PersonRegisterType_SysNick=' + selected_record.data.PersonRegisterType_SysNick;
                        window.open(s, '_blank');*/
						var Report_FileName = (selected_record.data.NotifyType_id==3)?'pan_EvnNotifyRegister_f02_FR.rptdesign':'pan_EvnNotifyRegister_f01_FR.rptdesign';
						var Report_Format = '';
						if(selected_record.data.NotifyType_id==3){
							if(getPrintOptions().register_f02_extension==1)
								Report_Format = 'pdf';
							else if(getPrintOptions().register_f02_extension==2)
								Report_Format = 'html';
							else if(getPrintOptions().register_f02_extension==3)
								Report_Format = 'doc';
						}
						else
						{
							if(getPrintOptions().register_f01_extension==1)
								Report_Format = 'pdf';
							else if(getPrintOptions().register_f01_extension==2)
								Report_Format = 'html';
							else if(getPrintOptions().register_f01_extension==3)
								Report_Format = 'doc';
						}
						var Report_Params = '&paramEvnNotifyRegister_id='+id+'&paramNotifyType_id='+selected_record.data.NotifyType_id+'&paramPersonRegisterType_id=49';
						printBirt({
							'Report_FileName': Report_FileName,
							'Report_Params': Report_Params,
							'Report_Format': Report_Format//'pdf'
						});
                        return true;
					}
                    return false;
				} }
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 150,
			autoLoadData: false,
			disableActions:false,
			dataUrl: C_SEARCH,
			id: 'ENRNLW_EvnNotifyRegisterNolosListSearchGrid',
			object: 'EvnNotifyRegister',
			pageSize: 100,
			paging: true,
			region: 'center',
			root: 'data',
			stringfields: [
				{name: 'EvnNotifyRegister_id', type: 'int', header: 'ID', key: true},
				{name: 'EvnNotifyRegister_pid', type: 'int', hidden: true},
				{name: 'NotifyType_Name', type: 'string', header: lang['tip_izvescheniya'], width: 120},
				{name: 'NotifyType_id', type: 'string', hidden: true},
				{name: 'EvnNotifyRegister_Num', type: 'string', header: lang['№_izvescheniya'], width: 80},
				{name: 'Lpu_did', type: 'int', hidden: true},
				{name: 'PersonRegisterType_SysNick', type: 'string', hidden: true},// не может быть пустым для регистров по новой схеме, если пусто значит работа с регистром в регионе недоступна
				{name: 'MorbusType_SysNick', type: 'string', hidden: true},// для фильтрации диагнозов, не может быть пустым для извещений по заболеванию и некоторых регистров (ВЗН)
				{name: 'EvnNotifyRegister_setDT', type: 'date', format: 'd.m.Y', header: lang['data_zapolneniya'], width: 120},	
				{name: 'Person_id', type: 'int', hidden: true},			
				{name: 'Server_id', type: 'int', hidden: true},			
				{name: 'PersonEvn_id', type: 'int', hidden: true},	
				{name: 'Morbus_id', type: 'int', hidden: true},// не может быть пустым для извещений по заболеванию
				{name: 'Person_Surname', type: 'string', header: lang['familiya'], width: 120},
				{name: 'Person_Firname', type: 'string', header: lang['imya'], width: 120},
				{name: 'Person_Secname', type: 'string', header: lang['otchestvo'], width: 120},
				{name: 'Person_Birthday', type: 'date', format: 'd.m.Y', header: lang['data_rojdeniya'], width: 90},
				{name: 'AttachLpu_Nick', type: 'string', header: lang['mo_prikr'], width: 150},
				{name: 'Diag_Name', type: 'string', header: lang['diagnoz_mkb-10'], width: 150, id: 'autoexpand'},
				{name: 'Diag_id', type: 'int', hidden: true},
				{name: 'PersonRegister_setDate', type: 'date', format: 'd.m.Y', header: lang['data_vkl_nevkl_v_registr'], width: 180},
				{name: 'Lpu_Nick', type: 'string', header: lang['mo_sozdaniya'], width: 150},
				{name: 'MedPersonal_Name', type: 'string', header: lang['spetsialist_sozdavshiy_napravlenie_izveschenie'], width: 290},
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
                this.getAction('action_add').setDisabled(me.fromARM !== null && me.fromARM.inlist(['minzdravdlo','spec_mz','mzchieffreelancer']) && false == sw.Promed.personRegister.isVznRegistryOperator());
			},
			onRowSelect: function(sm,index,record) {
				this.getAction('open_emk').setDisabled( Ext.isEmpty(record.get('Person_id')) || (me.fromARM !== null && me.fromARM.inlist(['minzdravdlo','spec_mz','mzchieffreelancer']) && false == sw.Promed.personRegister.isVznRegistryOperator()));
				
				if(me.fromARM == 'spec_mz' && sw.Promed.personRegister.isVznRegistryOperator() == false)
					this.getAction('open_emk').setDisabled(false);

				this.getAction('action_print').setDisabled( Ext.isEmpty(record.get('EvnNotifyRegister_id')));

				var disable = (Ext.isEmpty(record.get('EvnNotifyRegister_id')) || !Ext.isEmpty(record.get('PersonRegister_setDate')) || false == sw.Promed.personRegister.isVznRegistryOperator());
				this.getAction('person_register_include').setDisabled(1 != record.get('NotifyType_id') || disable);
				this.getAction('action_edit').setDisabled(1 != record.get('NotifyType_id') || disable);
				this.getAction('person_register_not_include').setDisabled(1 != record.get('NotifyType_id') || disable);
                this.getAction('action_delete').setDisabled(disable);
			},
			onDblClick: function(sm,index,record) {
				this.getAction('action_print').execute();
			}
		});
		
		Ext.apply(this, {
			buttons: [{
				handler: function() {
                    me.doSearch();
				},
				iconCls: 'search16',
				id: 'ENRNLW_SearchButton',
				text: BTN_FRMSEARCH
			}, {
				handler: function() {
                    me.doReset();
				},
				iconCls: 'resetsearch16',
				text: BTN_FRMRESET
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
                    me.findById('ENRNLW_SearchFilterTabbar').getActiveTab().fireEvent('activate', me.findById('ENRNLW_SearchFilterTabbar').getActiveTab());
				},
				text: BTN_FRMCLOSE
			}, {
				handler: function() {
                    getWnd('swPersonRegisterNolosExportWindow').show({ExportMod: '03-FR'})
				},
				iconCls: 'print16',
				text: lang['jurnal_napravleniy_forma_n_03-fr']
			}],
			getFilterForm: function() {
				if ( me.filterForm == undefined ) {
                    me.filterForm = me.findById('EvnNotifyRegisterNolosListFilterForm');
				}
				return me.filterForm;
			},
			items: [ getBaseSearchFiltersFrame({
				allowPersonPeriodicSelect: true,
				id: 'EvnNotifyRegisterNolosListFilterForm',
				labelWidth: 130,
				ownerWindow: this,
				searchFormType: 'EvnNotifyRegister',
				tabPanelHeight: 235,
				tabPanelId: 'ENRNLW_SearchFilterTabbar',
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
						PersonRegisterType_SysNick: me.PersonRegisterType_SysNick,
						xtype: 'swdiagcombo'
					}, {
						fieldLabel: lang['po'],
						hiddenName: 'Diag_Code_To',
						valueField: 'Diag_Code',
						width: 450,
						PersonRegisterType_SysNick: me.PersonRegisterType_SysNick,
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
						fieldLabel: lang['tip_izvescheniya'],
						xtype: 'swstoreinconfigcombo',
						valueField: 'NotifyType_id',
						displayField: 'NotifyType_Name',
						allowBlank: false,
						value: '0',
						comboData: [
							['0',lang['vse']],
							['1',lang['napravlenie_na_vklyuchenie_v_registr']],
							['2',lang['napravlenie_na_vnesenie_izmeneniy_v_registr']],
							['3',lang['izveschenie_na_isklyuchenie_iz_registra']]
						],
						comboFields: [
							{name: 'NotifyType_id', type: 'string'},
							{name: 'NotifyType_Name', type: 'string'}
						],
						recordIdIsValue: true
					}]
				}, {
					autoHeight: true,
					bodyStyle: 'margin-top: 5px;',
					border: false,
					labelWidth: 220,
					layout: 'form',
					listeners: {
						'activate': function(panel) {
							var form = me.getFilterForm().getForm();
							form.findField('Diag_Code_Group').focus(250, true);
						}
					},
					title: '7. Группа диагнозов',
					items: [{
						fieldLabel: lang['gruppa_diagnozov'],
						hiddenName: 'Diag_Code_Group',
						width: 450,
						isVZN: true,
						PersonRegisterType_SysNick: me.PersonRegisterType_SysNick,
						xtype: 'swdiaggroupscombo'
					}]
				}]
			}),
			this.RootViewFrame]
		});
		
		sw.Promed.swEvnNotifyRegisterNolosListWindow.superclass.initComponent.apply(this, arguments);
		
	},
	layout: 'border',
	listeners: {
		'beforeShow': function(win) {
			if (false == sw.Promed.personRegister.isAllow(win.PersonRegisterType_SysNick)) {
				return false;
			}
			return true;
		},
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
			win.findById('ENRNLW_SearchFilterTabbar').setWidth(nW - 5);
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
		sw.Promed.swEvnNotifyRegisterNolosListWindow.superclass.show.apply(this, arguments);
		var me = this;
		this.RootViewFrame.addActions({
			name:'person_register_not_include', 
			text:lang['ne_vklyuchat_v_registr'], 
			tooltip: lang['ne_vklyuchat_v_registr'],
			iconCls: 'reset16',
			disabled: true,
			menu: new Ext.menu.Menu({id:'ENRNLW_personRegisterNotIncludeMenu'})
		});
		
		this.RootViewFrame.addActions({
			name:'person_register_include', 
			text:lang['vklyuchit_v_registr'], 
			tooltip: lang['vklyuchit_v_registr'],
			iconCls: 'ok16',
			disabled: true,
			handler: function() {
				me.personRegisterInclude();
			}
		});
		
		this.RootViewFrame.addActions({
			name:'open_emk', 
			text:lang['otkryit_emk'], 
			tooltip: lang['otkryit_elektronnuyu_meditsinskuyu_kartu_patsienta'],
			iconCls: 'open16',
			handler: function() {
				me.emkOpen();
			}
		});
		
		var base_form = this.getFilterForm().getForm();

		this.restore();
		this.center();
		this.maximize();
		this.doReset();		
		this.fromARM = null;
		if(arguments[0] && arguments[0].fromARM)
		{
			this.fromARM = arguments[0].fromARM;
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
        var attach_lpu_combo = base_form.findField('AttachLpu_id');
        if (false == sw.Promed.personRegister.isVznRegistryOperator()) {
            attach_lpu_combo.setValue(getGlobalOptions().lpu_id);
        } else {
            attach_lpu_combo.setValue(null);
        }
        attach_lpu_combo.setDisabled(false == sw.Promed.personRegister.isVznRegistryOperator());

 		if(getWnd('swWorkPlaceMZSpecWindow').isVisible())
        {
        	attach_lpu_combo.setValue(null);
        	attach_lpu_combo.setDisabled(false);
        }
        
		this.doLayout();
		this.setMenu(true);
		this.doSearch({firstLoad: true});
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
			id: 'ENRNLW_personRegisterNotIncludeMenu',
			ownerWindow: this,
			getParams: function(){
				var record = this.RootViewFrame.getGrid().getSelectionModel().getSelected();
				if ( !record || !record.get('Person_id') ) {
					Ext.Msg.alert(lang['oshibka'], lang['ne_vyibrana_zapis']);
					return false;
				}
				if ( 1 != record.get('NotifyType_id') ) {
					Ext.Msg.alert(lang['oshibka'], lang['oshibka_vyibora_zapisi']);
					return false;
				}
				return {
					PersonRegisterType_SysNick: record.get('PersonRegisterType_SysNick'),
					EvnNotifyBase_id: record.get('EvnNotifyRegister_id'),
					MedPersonal_id: record.get('MedPersonal_id'),
					Lpu_id: record.get('Lpu_did')
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
		if ( !record || !record.get('Person_id') ) {
			Ext.Msg.alert(lang['oshibka'], lang['ne_vyibrana_zapis']);
			return false;
		}
		if ( 1 != record.get('NotifyType_id') ) {
			Ext.Msg.alert(lang['oshibka'], lang['oshibka_vyibora_zapisi']);
			return false;
		}
		sw.Promed.personRegister.doInclude({
			EvnNotifyBase_id: record.get('EvnNotifyRegister_id'),
			Person_id: record.get('Person_id'),	
            PersonRegisterType_SysNick: record.get('PersonRegisterType_SysNick'),
			Diag_id: record.get('Diag_id') || null,		
            MorbusType_SysNick: record.get('MorbusType_SysNick') || null,
			Morbus_id: record.get('Morbus_id') || null,
			Lpu_did:record.get('Lpu_did')|| getGlobalOptions().lpu_id,
			ownerWindow: this,
            question: lang['vklyuchit_dannyie_po_vyibrannomu_napravleniyu_v_registr'],
            MedPersonal_id: record.get('MedPersonal_id'),
			callback: function () {
				grid.getStore().reload();
			}
		});
	},
	/** Удаление направления/извещения
	 */
	delEvnNotifyRegister: function()
	{
		var me = this;
		var grid = me.RootViewFrame.getGrid();
		var record = grid.getSelectionModel().getSelected();
		if ( !record || !record.get('EvnNotifyRegister_id') ) {
			Ext.Msg.alert(lang['oshibka'], lang['ne_vyibrana_zapis']);
			return false;
		}	
		var delMessage = lang['vyi_hotite_udalit'];
		sw.swMsg.show(
		{
			icon: Ext.MessageBox.QUESTION,
			msg: delMessage,
			title: lang['podtverjdenie'],
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId, text, obj)
			{
				if ('yes' == buttonId)
				{
					var loadMask = new Ext.LoadMask(me.RootViewFrame.getEl(), {msg:lang['udalenie']});
					loadMask.show();

					Ext.Ajax.request(
					{
						url: '/?c=PersonRegister&m=deleteEvnNotifyRegister',
						params: {
							EvnNotifyRegister_id: record.get('EvnNotifyRegister_id'),
							NotifyType_id: record.get('NotifyType_id'),
							PersonRegisterType_SysNick: record.get('PersonRegisterType_SysNick'),
							Lpu_did: record.get('Lpu_did')
						},
						failure: function(response, options)
						{
							loadMask.hide();
							Ext.Msg.alert(lang['oshibka'], lang['pri_udalenii_proizoshla_oshibka']);
						},
						success: function(response, action)
						{
							loadMask.hide();
							if (response.responseText)
							{
								var answer = Ext.util.JSON.decode(response.responseText);
								if (answer.success)
								{
									grid.getStore().reload();
								}
							}
							else
							{
								Ext.Msg.alert(lang['oshibka'], lang['oshibka_pri_udalenii_otsutstvuet_otvet_servera']);
							}
						}
					});
				}
				else
				{
					if (grid.getStore().getCount()>0)
					{
						grid.getView().focusRow(0);
					}
				}
			}
		});
		return true;
	},
	/** Направление на включение в регистр
	 */
	openEvnNotifyRegisterIncludeWindow: function(action)
	{
		var win = getWnd('swEvnNotifyRegisterNolosIncludeWindow');
		var grid = this.RootViewFrame.getGrid();
		
		
		if ( win.isVisible() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: Ext.emptyFn,
				icon: Ext.Msg.WARNING,
				msg: lang['okno_uje_otkryito'],
				title: ERR_WND_TIT
			});
			return false;
		}
		if(action=='edit'){
			var record = grid.getSelectionModel().getSelected();
			if ( !record || !record.get('EvnNotifyRegister_id') ) {
				Ext.Msg.alert(lang['oshibka'], lang['ne_vyibrana_zapis']);
				return false;
			}	
			win.show({
				action:'edit',
				formParams: {
					EvnNotifyRegister_id: record.get('EvnNotifyRegister_id')
				},
				callback : function() {
					grid.getStore().reload();
				}
			});
			return false;
		}
		getWnd('swPersonSearchWindow').show({
			onSelect: function(personData) {
				if ( personData.Person_id > 0 ) {
					win.show({
						formParams: {
							PersonEvn_id: personData.PersonEvn_id,
							Server_id: personData.Server_id,
							Person_id: personData.Person_id
						},
						callback : function() {
							grid.getStore().reload();
						}
					});
				}
				getWnd('swPersonSearchWindow').hide();
			}
		});
		return true;
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
				//grid.getStore().reload();
			}
		});
	}
});
