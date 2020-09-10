/**
* swEvnNotifyOrphanListWindow - Журнал Извещений/Направлений об орфанных заболеваниях
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
* @comment      Префикс для id компонентов ENOLW (EvnNotifyOrphanListWindow)
*
*/
sw.Promed.swEvnNotifyOrphanListWindow = Ext.extend(sw.Promed.BaseForm, {codeRefresh: true,
	buttonAlign: 'left',
	closable: true,
	closeAction: 'hide',
	collapsible: true,
	getButtonSearch: function() {
		return Ext.getCmp('ENOLW_SearchButton');
	},
	doReset: function() {
		
		var base_form = this.getFilterForm().getForm();
		base_form.reset();
		base_form.findField('EvnNotifyType_SysNick').setValue('all');
		this.RootViewFrame.ViewActions.open_emk.setDisabled(true);
		this.RootViewFrame.ViewActions.action_view.setDisabled(true);
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
		if ( !params.firstLoad && this.findById('EvnNotifyOrphanListFilterForm').isEmpty() ) {
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

		var post = getAllFormFieldValues(this.findById('EvnNotifyOrphanListFilterForm'));

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
		post.SearchFormType = 'EvnNotifyOrphan';

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
		
		this.RootViewFrame = new sw.Promed.ViewFrame({
			actions: [
				{name: 'action_add', hidden: true},
				{name: 'action_edit', hidden: true},
				{name: 'action_view', handler: function() {
					this.openViewWindow();
				}.createDelegate(this)},
				{name: 'action_delete', hidden: true},
				{name: 'action_refresh'},
				{name: 'action_print', handler: function() {
					var selected_record = this.RootViewFrame.getGrid().getSelectionModel().getSelected();
					if (!selected_record) {
						return false;
					}
					var EvnNotifyOrphan_id = selected_record.data.EvnNotifyOrphan_id;
					if (EvnNotifyOrphan_id) {
						switch (selected_record.data.EvnNotifyType_SysNick) {		
						
							case 'EvnNotifyOrphan':							
								printBirt({
									'Report_FileName': 'han_EvnNotifyOrphan.rptdesign',
									'Report_Params': '&paramEvnNotifyOrphan=' + EvnNotifyOrphan_id,
									'Report_Format': 'pdf'
								});
								return false;							
							break;			
							
							case 'EvnDirectionOrphan':							
								printBirt({
									'Report_FileName': 'han_EvnDirectionOrphan.rptdesign',
									'Report_Params': '&paramEvnDirectionOrphan=' + EvnNotifyOrphan_id,
									'Report_Format': 'pdf'
								});
								return false;							
							break;			
							
							case 'EvnNotifyOrphanOut':						
								printBirt({
									'Report_FileName': 'han_EvnNotifyOrphanOut.rptdesign',
									'Report_Params': '&paramEvnNotifyOrphanOut=' + EvnNotifyOrphan_id,
									'Report_Format': 'pdf'
								});
								return false;					
							break;						
						}					
					}
					return true;
				}.createDelegate(this)}
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 220,
			autoLoadData: false,
			dataUrl: C_SEARCH,
			id: 'ENOLW_EvnNotifyOrphanListSearchGrid',
			object: 'EvnNotifyOrphanList',
			pageSize: 100,
			paging: true,
			region: 'center',
			root: 'data',
			stringfields: [
				{name: 'EvnNotifyOrphan_id', type: 'int', header: 'ID', key: true},
				{name: 'EvnNotifyOrphan_pid', type: 'int', hidden: true},
				{name: 'EvnNotifyType_Name', type: 'string', header: lang['tip_izvescheniya'], width: 120},
				{name: 'EvnNotifyType_SysNick', type: 'string', hidden: true},			
				{name: 'EvnNotifyOrphan_setDT', type: 'date', format: 'd.m.Y', header: lang['data_sozdaniya'], width: 120},	
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
				{name: 'MedPersonal_Name', type: 'string', header: lang['spetsialist_sozdavshiy_napravlenie_izveschenie'], width: 290},
				{name: 'MedPersonal_id', type: 'int', hidden: true},
				{name: 'pmUser_updId', type: 'int', hidden: true}
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
				this.getAction('open_emk').setDisabled( Ext.isEmpty(record.get('Person_id')));
				this.getAction('action_view').setDisabled( Ext.isEmpty(record.get('Person_id')) || 'EvnNotifyOrphan' != record.get('EvnNotifyType_SysNick') );

				var disable = (Ext.isEmpty(record.get('Person_id')) || !Ext.isEmpty(record.get('PersonRegister_setDate')) || isRegisterAutoInclude('orphan'));
				this.getAction('person_register_include').setDisabled(disable);
				this.getAction('person_register_not_include').setDisabled(disable);
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
					this.doSearch();
				}.createDelegate(this),
				iconCls: 'search16',
				tabIndex: TABINDEX_ENOLW + 120,
				id: 'ENOLW_SearchButton',
				text: BTN_FRMSEARCH
			}, {
				handler: function() {
					this.doReset();
				}.createDelegate(this),
				iconCls: 'resetsearch16',
				tabIndex: TABINDEX_ENOLW + 121,
				text: BTN_FRMRESET
			}, {
				handler: function() {
					getWnd('swEvnNotifyOrphanListPrintWindow').show();
				}.createDelegate(this),
				iconCls: 'print16',
				tabIndex: TABINDEX_ENOLW + 122,
                hidden: getGlobalOptions().region.nick == 'ufa',
				text: lang['pechat_spiska']
			}, {
				handler: function() {
					this.getRecordsCount();
				}.createDelegate(this),
				// iconCls: 'resetsearch16',
				tabIndex: TABINDEX_ENOLW + 123,
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
					this.findById('ENOLW_SearchFilterTabbar').getActiveTab().fireEvent('activate', this.findById('ENOLW_SearchFilterTabbar').getActiveTab());
				}.createDelegate(this),
				tabIndex: TABINDEX_ENOLW + 124,
				text: BTN_FRMCLOSE
			}],
			getFilterForm: function() {
				if ( this.filterForm == undefined ) {
					this.filterForm = this.findById('EvnNotifyOrphanListFilterForm');
				}
				return this.filterForm;
			},
			items: [ getBaseSearchFiltersFrame({
				allowPersonPeriodicSelect: true,
				id: 'EvnNotifyOrphanListFilterForm',
				labelWidth: 130,
				ownerWindow: this,
				searchFormType: 'EvnNotifyOrphan',
				tabIndexBase: TABINDEX_ENOLW,
				tabPanelHeight: 235,
				tabPanelId: 'ENOLW_SearchFilterTabbar',
				tabs: [{
					autoHeight: true,
					bodyStyle: 'margin-top: 5px;',
					border: false,
					labelWidth: 220,
					layout: 'form',
					listeners: {
						'activate': function(panel) {
							var form = this.getFilterForm().getForm();
							form.findField('Diag_Code_From').focus(250, true);
							form.findField('isNotifyProcessed').setContainerVisible(!isRegisterAutoInclude('orphan'));
						}.createDelegate(this)
					},
					title: lang['6_izveschenie'],
					items: [{
						border: false,
						layout: 'column',
						items: [{
							border: false,
							layout: 'form',									
							items: [{
								fieldLabel: lang['kod_diagnoza_s'],
								hiddenName: 'Diag_Code_From',
								listWidth: 620,
								valueField: 'Diag_Code',
								width: 290,
                                PersonRegisterType_SysNick: 'orphan',
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
                                PersonRegisterType_SysNick: 'orphan',
                                MorbusType_SysNick: 'orphan',
								xtype: 'swdiagcombo'
							}]
						}]
					}, {
						fieldLabel: lang['mo_v_kotoroy_patsientu_vpervyie_ustanovlen_diagnoz_orfannogo_zabolevaniya'],
						hiddenName: 'Lpu_sid',//Lpu_oid 
						listWidth: 620,
						ctCls: 'ct-vertical-align-bottom',
						itemCls: 'item-vertical-align-bottom',
						//style: 'vertical-align: bottom',
						width: 350,
						xtype: 'swlpucombo'
					}, {
						fieldLabel: lang['data_zapolneniya_izvescheniya_napravleniya'],
						name: 'EvnNotifyBase_setDT_Range',
						plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
						width: 170,
						xtype: 'daterangefield'
					}, {
						fieldLabel: lang['napravlenie_na_vklyuchenie_v_registr_po_orfannyim_zabolevaniyam_obrabotano'],
						xtype: 'swyesnocombo',
						hiddenName: 'isNotifyProcessed'
					}, {
						fieldLabel: lang['tip_izvescheniya'],
						xtype: 'swstoreinconfigcombo',
						valueField: 'EvnNotifyType_SysNick',
						displayField: 'EvnNotifyType_Name',
						value: 'all',
						comboData: [
							['all',lang['vse']],
							['EvnNotifyOrphan',lang['napravlenie_na_vklyuchenie_v_registr']],
							['EvnDirectionOrphan',lang['napravlenie_na_vnesenie_izmeneniy_v_registr']],
							['EvnNotifyOrphanOut',lang['izveschenie_na_isklyuchenie_iz_registra']]
						],
						comboFields: [
							{name: 'EvnNotifyType_SysNick', type: 'string'},
							{name: 'EvnNotifyType_Name', type: 'string'}
						],
						recordIdIsValue: true
					}]
				}]
			}),
			this.RootViewFrame]
		});
		
		sw.Promed.swEvnNotifyOrphanListWindow.superclass.initComponent.apply(this, arguments);
		
	},
	layout: 'border',
	listeners: {
		'hide': function(win) {
			win.doReset();
		},
		'maximize': function(win) {
			win.getFilterForm().doLayout();
		},
		'beforeShow': function(win) {
			if (String(getGlobalOptions().groups).indexOf('Orphan', 0) < 0)
			{
				sw.swMsg.alert('Сообщение', 'Форма "'+ win.title +'" доступна только для пользователей, с указанной группой «Регистр по орфанным заболеваниям»');
				return false;
			}
		},
		'restore': function(win) {
			win.getFilterForm().doLayout();
		},
        'resize': function (win, nW, nH, oW, oH) {
			win.findById('ENOLW_SearchFilterTabbar').setWidth(nW - 5);
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
		sw.Promed.swEvnNotifyOrphanListWindow.superclass.show.apply(this, arguments);

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
								EvnNotifyBase_id: record.get('EvnNotifyOrphan_id'),
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
			//menu: new Ext.menu.Menu({id:'ENOLW_personRegisterNotIncludeMenu'})
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
			
		var att_lpu_field = base_form.findField('AttachLpu_id');
		att_lpu_field.setFieldLabel(lang['mo_prikrepleniya']);
		att_lpu_field.setValue(isSuperAdmin()?null:getGlobalOptions().lpu_id);
		att_lpu_field.setDisabled(!isSuperAdmin());
		if(getWnd('swWorkPlaceMZSpecWindow').isVisible())
		{
			att_lpu_field.setValue(null);
			att_lpu_field.setDisabled(false);
		}
		this.doLayout();
		this.setMenu(true);
		this.doSearch({firstLoad: true});
	},
	/** Создание меню
	 */
	setMenu: function(is_first) {
		if (is_first) {
			//this.createPersonRegisterFailIncludeCauseMenu();
		}
	},
	/** Создание меню причин не включения в регистр
	 */
	createPersonRegisterFailIncludeCauseMenu: function() {
		sw.Promed.personRegister.createPersonRegisterFailIncludeCauseMenu({
			id: 'ENOLW_personRegisterNotIncludeMenu',
			ownerWindow: this,
			getParams: function(){
				var record = this.RootViewFrame.getGrid().getSelectionModel().getSelected();
				if ( !record || !record.get('Person_id') )
				{
					Ext.Msg.alert(lang['oshibka'], lang['ne_vyibrana_zapis']);
					return false;
				}
				return {
					EvnNotifyBase_id: record.get('EvnNotifyOrphan_id')
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
			EvnNotifyBase_id: record.get('EvnNotifyOrphan_id'),
			Person_id: record.get('Person_id'),
			Diag_id: record.get('Diag_id'),
            MorbusType_SysNick: 'orphan',
			Morbus_id: record.get('Morbus_id'),
			ownerWindow: this,
			question: lang['vklyuchit_dannyie_po_vyibrannomu_napravleniyu_v_registr'],
			callback: function () {
				grid.getStore().reload();
			}
		};
		sw.Promed.personRegister.include(params);
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
		if ( getWnd('swEvnNotifyOrphanEditWindow').isVisible() ) {
			getWnd('swEvnNotifyOrphanEditWindow').hide();
		}
		getWnd('swEvnNotifyOrphanEditWindow').show({action: 'view', EvnNotifyOrphan_id: record.get('EvnNotifyOrphan_id')});
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
			callback: function()
			{
				//
			}.createDelegate(this)
		});
	},
	title: lang['jurnal_izvescheniy_napravleniy_ob_orfannyih_zabolevaniyah'],
	width: 800
});