/**
* swEvnNotifyTubListWindow - Журнал Извещений о больном
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       A.Markoff <markov@swan.perm.ru> & Alexander Permyakov
* @version      2012/11
* @comment      Префикс для id компонентов ENTLW (EvnNotifyTubListWindow)
*
*/
sw.Promed.swEvnNotifyTubListWindow = Ext.extend(sw.Promed.BaseForm, {codeRefresh: true,
	buttonAlign: 'left',
	closable: true,
	closeAction: 'hide',
	collapsible: true,
	getButtonSearch: function() {
		return Ext.getCmp('ENTLW_SearchButton');
	},
	doReset: function() {
		
		var base_form = this.getFilterForm().getForm();
		base_form.reset();
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
		if ( !params.firstLoad && this.findById('EvnNotifyTubListFilterForm').isEmpty() ) {
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

		var post = getAllFormFieldValues(this.findById('EvnNotifyTubListFilterForm'));

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
		
		this.RootViewFrame = new sw.Promed.ViewFrame({
			actions: [
				{
					name: 'action_add', 
					handler: function() { this.addEvnNotifyTub(); }.createDelegate(this), 
					hidden: (!isTubRegistryUser()), 
					disabled: (!isTubRegistryUser())
				},
				{name: 'action_edit', handler: function() {
					this.openEditWindow('edit');
				}.createDelegate(this)},
				{name: 'action_view', handler: function() {
					this.openEditWindow('view');
				}.createDelegate(this)},
				{name: 'action_delete', handler: function() {
					this.deleteNotify();
				}.createDelegate(this)},
				{name: 'action_refresh'},
				{name: 'action_print', handler: function() {
					var selected_record = this.RootViewFrame.getGrid().getSelectionModel().getSelected();
					if (!selected_record) {
						return false;
					}
					var EvnNotifyTub_id = selected_record.data.EvnNotifyTub_id;
					if(EvnNotifyTub_id) {
						printBirt({
							'Report_FileName': 'EvnNotifyTub.rptdesign',
							'Report_Params': '&paramEvnNotifyTub=' + EvnNotifyTub_id,
							'Report_Format': 'pdf'
						});
						return false;
					}
					return true;
				}.createDelegate(this) }
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 150,
			autoLoadData: false,
			dataUrl: C_SEARCH,
			id: 'ENTLW_EvnNotifyTubListSearchGrid',
			object: 'EvnNotifyTubList',
			pageSize: 100,
			paging: true,
			region: 'center',
			root: 'data',
			stringfields: [
				{name: 'EvnNotifyTub_id', type: 'int', header: 'ID', key: true},
				{name: 'EvnNotifyTub_pid', type: 'int', hidden: true},
				{name: 'EvnNotifyTub_setDT', type: 'date', format: 'd.m.Y', header: lang['data_sozdaniya'], width: 120},	
				{name: 'Person_id', type: 'int', hidden: true},			
				{name: 'Server_id', type: 'int', hidden: true},			
				{name: 'PersonEvn_id', type: 'int', hidden: true},	
				{name: 'Morbus_id', type: 'int', hidden: true},
				{name: 'Person_Surname', type: 'string', header: lang['familiya'], width: 120},
				{name: 'Person_Firname', type: 'string', header: lang['imya'], width: 120},
				{name: 'Person_Secname', type: 'string', header: lang['otchestvo'], width: 120},
				{name: 'Person_Birthday', type: 'date', format: 'd.m.Y', header: lang['data_rojdeniya'], width: 90},
				{name: 'Lpu_Nick', type: 'string', header: lang['lpu_prikr'], width: 150},
				{name: 'Diag_Name', type: 'string', header: lang['diagnoz_mkb-10'], width: 150, id: 'autoexpand'},
				{name: 'Diag_id', type: 'int', hidden: true},
				{name: 'PersonRegister_setDate', type: 'date', format: 'd.m.Y', header: lang['data_vkl_nevkl_v_registr'], width: 180},
				{name: 'PersonRegister_id', type: 'int', hidden: true},	
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
				this.getAction('action_view').setDisabled( Ext.isEmpty(record.get('Person_id')));

				var disable = (Ext.isEmpty(record.get('Person_id')) || !Ext.isEmpty(record.get('PersonRegister_setDate')) || isRegisterAutoInclude('tub'));
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
				tabIndex: TABINDEX_ENTLW + 120,
				id: 'ENTLW_SearchButton',
				text: BTN_FRMSEARCH
			}, {
				handler: function() {
					this.doReset();
				}.createDelegate(this),
				iconCls: 'resetsearch16',
				tabIndex: TABINDEX_ENTLW + 121,
				text: BTN_FRMRESET
			}, {
				handler: function() {
					this.RootViewFrame.printRecords();
				}.createDelegate(this),
				iconCls: 'print16',
				tabIndex: TABINDEX_ENTLW + 122,
				text: lang['pechat_spiska']
			}, {
				handler: function() {
					this.getRecordsCount();
				}.createDelegate(this),
				// iconCls: 'resetsearch16',
				tabIndex: TABINDEX_ENTLW + 123,
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
					this.findById('ENTLW_SearchFilterTabbar').getActiveTab().fireEvent('activate', this.findById('ENTLW_SearchFilterTabbar').getActiveTab());
				}.createDelegate(this),
				tabIndex: TABINDEX_ENTLW + 124,
				text: BTN_FRMCLOSE
			}],
			getFilterForm: function() {
				if ( this.filterForm == undefined ) {
					this.filterForm = this.findById('EvnNotifyTubListFilterForm');
				}
				return this.filterForm;
			},
			items: [ getBaseSearchFiltersFrame({
				allowPersonPeriodicSelect: true,
				id: 'EvnNotifyTubListFilterForm',
				labelWidth: 130,
				ownerWindow: this,
				searchFormType: 'EvnNotifyTub',
				tabIndexBase: TABINDEX_ENTLW,
				tabPanelHeight: 235,
				tabPanelId: 'ENTLW_SearchFilterTabbar',
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
							form.findField('isNotifyProcessed').setContainerVisible(!isRegisterAutoInclude('tub'));
						}.createDelegate(this)
					},
					title: lang['6_izveschenie'],
					items: [{
						fieldLabel: lang['kod_diagnoza_s'],
						hiddenName: 'Diag_Code_From',
						valueField: 'Diag_Code',
						width: 450,
                        MorbusType_SysNick: 'tub',
						xtype: 'swdiagcombo'
					}, {
						fieldLabel: lang['po'],
						hiddenName: 'Diag_Code_To',
						valueField: 'Diag_Code',
						width: 450,
                        MorbusType_SysNick: 'tub',
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
						hiddenName: 'EvnNotifyTub_IsFirstDiag',
						fieldLabel: lang['tip_izvescheniya'],
						xtype: 'swbaselocalcombo',
						displayField: 'name',
						valueField: 'code',
						editable: false,
						store: new Ext.data.SimpleStore({
							id: 0,
							fields: ['code','name'],
							data: [
								[2, lang['vpervyie_v_jizni']],
								[1, lang['retsidiv']]
							]
						}),
						value: 0,
						width: 180
					}, {
						fieldLabel: lang['soputstvuyuschie_zabolevaniya'],
						hiddenName: 'TubDiagSop_id',
						comboSubject: 'TubDiagSop',
						width: 450,
						xtype: 'swcommonsprcombo'
					}, {
						fieldLabel: lang['kategoriya_naseleniya'],
						width: 450,
						hiddenName: 'PersonCategoryType_id',
						xtype: 'swcommonsprcombo',
						comboSubject: 'PersonCategoryType'
					}, {
						fieldLabel: lang['vyiyavlen_iz_nablyudaemyih_v_tubuchrejdeniyah_grupp'],
						width: 180,
						hiddenName: 'TubSurveyGroupType_id',
						xtype: 'swyesnocombo',
						comboSubject: 'TubSurveyGroupType'
					}]
				}]
			}),
			this.RootViewFrame]
		});
		
		sw.Promed.swEvnNotifyTubListWindow.superclass.initComponent.apply(this, arguments);
		
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
			if (String(getGlobalOptions().groups).indexOf('Tub', 0) < 0)
			{
				if(!getWnd('swWorkPlaceMZSpecWindow').isVisible() && !isUserGroup('MIACSuperAdmin'))
				{
					sw.swMsg.alert('Сообщение', 'Форма "'+ win.title +'" доступна только для пользователей, с указанной группой «Регистр по туберкулезным заболеваниям»');
					return false;
				}
			}
		},
		'restore': function(win) {
			win.getFilterForm().doLayout();
		},
        'resize': function (win, nW, nH, oW, oH) {
			win.findById('ENTLW_SearchFilterTabbar').setWidth(nW - 5);
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
		sw.Promed.swEvnNotifyTubListWindow.superclass.show.apply(this, arguments);

		this.RootViewFrame.addActions({
			name:'person_register_not_include', 
			text:lang['ne_vklyuchat_v_registr'], 
			tooltip: lang['ne_vklyuchat_v_registr'],
			iconCls: 'reset16',
			disabled: true,
			hidden: (String(getGlobalOptions().groups).indexOf('Tub', 0) < 0),
			menu: new Ext.menu.Menu({id:'ENTLW_personRegisterNotIncludeMenu'})
		});
		
		this.RootViewFrame.addActions({
			name:'person_register_include', 
			text:lang['vklyuchit_v_registr'], 
			tooltip: lang['vklyuchit_v_registr'],
			iconCls: 'ok16',
			disabled: true,
			hidden: (String(getGlobalOptions().groups).indexOf('Tub', 0) < 0),
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
			id: 'ENTLW_personRegisterNotIncludeMenu',
			ownerWindow: this,
			getParams: function(){
				var record = this.RootViewFrame.getGrid().getSelectionModel().getSelected();
				if ( !record || !record.get('Person_id') )
				{
					Ext.Msg.alert(lang['oshibka'], lang['ne_vyibrana_zapis']);
					return false;
				}
				return {
					EvnNotifyBase_id: record.get('EvnNotifyTub_id')
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
			EvnNotifyBase_id: record.get('EvnNotifyTub_id'),
			Person_id: record.get('Person_id'),
			Diag_id: record.get('Diag_id'),
            MorbusType_SysNick: 'tub',
			Morbus_id: record.get('Morbus_id'),
			ownerWindow: this,
			callback: function () {
				grid.getStore().reload();
			}
		};
		sw.Promed.personRegister.include(params);
	},
	/** Извещение: Добавление
	 */
	addEvnNotifyTub: function()
	{
		var win = getWnd('swEvnNotifyTubEditWindow');
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
		getWnd('swPersonSearchWindow').show({
			onSelect: function(personData) {
				if ( personData.Person_id > 0 ) {
					Ext.Ajax.request({
						url: '/?c=EvnNotifyTub&m=checkTubRegistryEntry',
						params: {Person_id: personData.Person_id},
						callback: function(options, success, response) {
							function showNotifyWnd(){
								win.show({
									action: 'add',
									saveFromJournal: 1,
									formParams: {
										PersonEvn_id: personData.PersonEvn_id,
										Server_id: personData.Server_id,
										Person_id: personData.Person_id,
										MedPersonal_id: getGlobalOptions().CurMedPersonal_id
									},
									callback : function() {
										grid.getStore().reload();
									}
								});
							}
							if ( success ) {
								var result = Ext.util.JSON.decode(response.responseText);
								if(result[0] && result[0].PersonRegister_id){
									sw.swMsg.show({
										buttons: Ext.Msg.YESNO,
										fn: function(buttonId) {
											if ( buttonId == 'yes' ){
												showNotifyWnd();
											}
										},
										icon: Ext.Msg.QUESTION,
										msg: 'Выбранный пациент уже включен в регистр по туберкулезу. Создать Извещение?',
										title: lang['vopros']
									});
								} else {
									showNotifyWnd();
								}
							}
						}
					});
				}
				getWnd('swPersonSearchWindow').hide();
			}
		});
		return true;
	},
	/** Извещение: Просмотр и редактирование
	 */
	openEditWindow: function(action)
	{
		if (!action.inlist([ 'edit', 'view' ])) {
			return false;
		}
		var grid = this.RootViewFrame.getGrid();
		var record = grid.getSelectionModel().getSelected();
		if ( !record || !record.get('Person_id') )
		{
			Ext.Msg.alert(lang['oshibka'], lang['ne_vyibrana_zapis']);
			return false;
		}
		if ( getWnd('swEvnNotifyTubEditWindow').isVisible() ) {
			getWnd('swEvnNotifyTubEditWindow').hide();
		}
		getWnd('swEvnNotifyTubEditWindow').show({
			action: action, 
			EvnNotifyTub_id: record.get('EvnNotifyTub_id'), 
			saveFromJournal: 1,
			callback : function() {
				grid.getStore().reload();
			}
		});
	},
	deleteNotify: function () {
		
		var form = this;
		var grid = this.RootViewFrame.getGrid();
		var record = grid.getSelectionModel().getSelected();
		if (!record || !record.get('Person_id')) {
			return false;
		}
		
		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId) {
				if ( buttonId == 'yes' ){
					Ext.Ajax.request({
						url: '/?c=EvnNotifyTub&m=del',
						params: {
							EvnNotifyTub_id: record.get('EvnNotifyTub_id'),
							PersonRegister_id: record.get('PersonRegister_id')
						},
						callback: function(options, success, response) {
							if (success) {
								form.RootViewFrame.loadData();
							}
						}
					});
				}
			},
			icon: Ext.Msg.QUESTION,
			msg: lang['udalyat_vyibrannoe_izveschenie'],
			title: lang['vopros']
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
	title: lang['jurnal_izvescheniy_po_tuberkuleznyim_zabolevaniyam'],
	width: 800
});
