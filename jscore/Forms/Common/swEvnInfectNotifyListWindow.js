/**
* swEvnInfectNotifyListWindow - Журнал Извещений форма №058/У
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Alexander Chebukin
* @version      
* @comment      Префикс для id компонентов EINLW (EvnInfectNotifyListWindow)
*
*/
sw.Promed.swEvnInfectNotifyListWindow = Ext.extend(sw.Promed.BaseForm, {codeRefresh: true,
	buttonAlign: 'left',
	closable: true,
	closeAction: 'hide',
	collapsible: true,
	getButtonSearch: function() {
		return Ext.getCmp('EINLW_SearchButton');
	},
	doReset: function() {
		
		var base_form = this.findById('EvnInfectNotifyListFilterForm').getForm();
		base_form.reset();
				
	},
	doSearch: function(params) {
		
		var base_form = this.findById('EvnInfectNotifyListFilterForm').getForm();
		
		if ( this.findById('EvnInfectNotifyListFilterForm').isEmpty() ) {
			sw.swMsg.alert(lang['oshibka'], lang['ne_zapolneno_ni_odno_pole'], function() {
			});
			return false;
		}
		
		var grid = this.findById('EINLW_EvnInfectNotifyListSearchGrid').getGrid();

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

		var post = getAllFormFieldValues(this.findById('EvnInfectNotifyListFilterForm'));

		post.limit = 100;
		post.start = 0;
		
		//log(post);

		if ( base_form.isValid() ) {
			this.findById('EINLW_EvnInfectNotifyListSearchGrid').ViewActions.action_refresh.setDisabled(false);
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
	openWindow: function(action) {
		if (!action || !action.toString().inlist(['view'])) {
			return false;
		}

		if (action == 'add' && getWnd('swEvnInfectNotifyEditWindow').isVisible()) {
			sw.swMsg.alert(lang['soobschenie'], lang['okno_prosmotra_uje_otkryito']);
			return false;
		}

		var grid = this.findById('EINLW_EvnInfectNotifyListSearchGrid').getGrid();
		var params = new Object();
		
		params.action = action;
		params.callback = function(data) {
			
		}
		
		if (!grid.getSelectionModel().getSelected()) {
			return false;
		}

		var selected_record = grid.getSelectionModel().getSelected();

		params.formParams = selected_record.data;
		params.EvnInfectNotify_id = selected_record.data.EvnInfectNotify_id;

		getWnd('swEvnInfectNotifyEditWindow').show(params);
		
	},
	initComponent: function() {
		
		this.EvnInfectNotifyListSearchFrame = new sw.Promed.ViewFrame({
			actions: [
				{name: 'action_add', hidden: true},
				{name: 'action_edit', hidden: true, handler: function() { this.openWindow('view') }.createDelegate(this)},
				{name: 'action_view', handler: function() { this.openWindow('view') }.createDelegate(this)},
				{name: 'action_delete', hidden: true},
				{name: 'action_refresh', handler: function() {this.findById('EINLW_EvnInfectNotifyListSearchGrid').getGrid().getStore().reload();}.createDelegate(this)},
				{name: 'action_print', handler: function() {
					var selected_record = this.EvnInfectNotifyListSearchFrame.getGrid().getSelectionModel().getSelected();
					
					if (!selected_record) {
						return false;
					}
					log(selected_record)
					var EvnInfectNotify_id = selected_record.data.EvnInfectNotify_id;
					if(!Ext.isEmpty(selected_record.data.Lpu_id)){
						Lpu_id = selected_record.data.Lpu_id
					}
					else{
						Lpu_id = getGlobalOptions().lpu_id;
					}
					if(EvnInfectNotify_id&&Lpu_id) {
						printBirt({
							'Report_FileName': 'f058u.rptdesign',
							'Report_Params': '&paramLpu=' + Lpu_id + '&paramEvnInfectNotify=' + EvnInfectNotify_id,
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
			id: 'EINLW_EvnInfectNotifyListSearchGrid',
			object: 'EvnInfectNotifyList',
			pageSize: 100,
			paging: true,
			region: 'center',
			root: 'data',
			stringfields: [
				{name: 'EvnInfectNotify_id', type: 'int', header: 'ID', key: true},
				{name: 'EvnInfectNotify_insDT', type: 'date', format: 'd.m.Y', header: lang['data_zapolneniya'], width: 120},	
				{name: 'Person_id', type: 'int', hidden: true},			
				{name: 'Person_Surname', type: 'string', header: lang['familiya'], width: 150},
				{name: 'Person_Firname', type: 'string', header: lang['imya'], width: 150},
				{name: 'Person_Secname', type: 'string', header: lang['otchestvo'], width: 150},
				{name: 'Lpu_id', type: 'int', hidden: true},	
				{name: 'Person_Birthday', type: 'date', format: 'd.m.Y', header: lang['d_r'], width: 90},
				{name: 'Lpu_Nick', type: 'string', header: lang['lpu_prikr'], width: 150},
				{name: 'SendTo', type: 'string', header: lang['kuda_napravleno'], width: 200},			
				{name: 'Diag_Name', type: 'string', header: lang['diagnoz_mkb-10'], width: 150, id: 'autoexpand'}
			],
			toolbar: true,
			totalProperty: 'totalCount', 
			onBeforeLoadData: function() {
				this.getButtonSearch().disable();
			}.createDelegate(this),
			onLoadData: function() {
				this.getButtonSearch().enable();
			}.createDelegate(this)
		});
		

		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.doSearch();
				}.createDelegate(this),
				iconCls: 'search16',
				tabIndex: TABINDEX_EINLW + 120,
				id: 'EINLW_SearchButton',
				text: BTN_FRMSEARCH
			}, {
				handler: function() {
					this.doReset();
				}.createDelegate(this),
				iconCls: 'resetsearch16',
				tabIndex: TABINDEX_EINLW + 121,
				text: BTN_FRMRESET
			}, {
                    handler: function() {
                        this.getRecordsCount();
                    }.createDelegate(this),
                    tabIndex: TABINDEX_EINLW + 123,
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
					this.findById('EINLW_SearchFilterTabbar').getActiveTab().fireEvent('activate', this.findById('EINLW_SearchFilterTabbar').getActiveTab());
				}.createDelegate(this),
				tabIndex: TABINDEX_EINLW + 124,
				text: BTN_FRMCANCEL
			}],
			getFilterForm: function() {
				if ( this.filterForm == undefined ) {
					this.filterForm = this.findById('EvnInfectNotifyListFilterForm');
				}
				return this.filterForm;
			},
			items: [ getBaseSearchFiltersFrame({
				allowPersonPeriodicSelect: true,
				id: 'EvnInfectNotifyListFilterForm',
				labelWidth: 130,
				ownerWindow: this,
				searchFormType: 'EvnInfectNotify',
				tabIndexBase: TABINDEX_EINLW,
				tabPanelHeight: 215,
				tabPanelId: 'EINLW_SearchFilterTabbar',
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
						}.createDelegate(this)
					},
					title: lang['6_izveschenie'],
					items: [{
						fieldLabel: lang['kod_diagnoza_s'],
						hiddenName: 'Diag_Code_From',
						listWidth: 620,
						valueField: 'Diag_Code',
						width: 620,
						xtype: 'swdiagcombo'
					}, {
						fieldLabel: lang['po'],
						hiddenName: 'Diag_Code_To',
						listWidth: 620,
						valueField: 'Diag_Code',
						width: 620,
						xtype: 'swdiagcombo'
					}, {
						fieldLabel: lang['data_zapolneniya_izvescheniya'],
						name: 'EvnNotifyBase_setDT_Range',
						plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
						width: 170,
						xtype: 'daterangefield'
					}]
				}]
			}),
			this.EvnInfectNotifyListSearchFrame]
		});
		
		sw.Promed.swEvnInfectNotifyListWindow.superclass.initComponent.apply(this, arguments);
		
	},
	layout: 'border',
	listeners: {
		'hide': function(win) {
			win.doReset();
		},
		'maximize': function(win) {
			win.findById('EvnInfectNotifyListFilterForm').doLayout();
		},
		'restore': function(win) {
			win.findById('EvnInfectNotifyListFilterForm').doLayout();
		},
        'resize': function (win, nW, nH, oW, oH) {
			win.findById('EINLW_SearchFilterTabbar').setWidth(nW - 5);
			win.findById('EvnInfectNotifyListFilterForm').setWidth(nW - 5);
		}
	},
	maximizable: true,
	minHeight: 550,
	minWidth: 800,
	modal: false,
	plain: true,
	resizable: true,
	scheduleOpen: function()
	{
		var form = this;
		var grid = this.EvnInfectNotifyListSearchFrame.getGrid();
		if (!grid)
		{
			Ext.Msg.alert(lang['oshibka'], lang['spisok_raspisaniy_ne_nayden']);
			return false;
		}
		else if ( !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('Person_id') )
		{
			Ext.Msg.alert(lang['oshibka'], lang['ne_vyibrana_zapis']);
			return false;
		}
		var record = grid.getSelectionModel().getSelected();
		
		var isMyOwnRecord = false;
		if (record.get('pmUser_updId') == getGlobalOptions().pmuser_id) {
			isMyOwnRecord = true;
		}
		
		getWnd('swPersonEmkWindow').show(
		{
			Person_id: record.get('Person_id'),
			Server_id: record.get('Server_id'),
			userMedStaffFact: this.userMedStaffFact,
			mode: 'workplace',
			ARMType: 'common',
			readOnly: getWnd('swWorkPlaceMZSpecWindow').isVisible()?true:false,
			callback: function()
			{
				//this.scheduleRefresh();
			}.createDelegate(this)
		});
	},
	show: function() {
		sw.Promed.swEvnInfectNotifyListWindow.superclass.show.apply(this, arguments);
		if (!arguments[0]) 
		{
			sw.swMsg.show(
			{
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.ERROR,
				msg: lang['oshibka_otkryitiya_formyi_ne_ukazanyi_nujnyie_vhodnyie_parametryi'],
				title: lang['oshibka'],
				fn: function() {
					this.hide();
				}
			});
		}
		this.ARMType=null;
		this.userMedStaffFact=null;
		if (arguments[0].ARMType) 
		{
			this.ARMType = arguments[0].ARMType;
		}
		if (arguments[0].userMedStaffFact) 
		{
			this.userMedStaffFact = arguments[0].userMedStaffFact;
		}	
		var base_form = this.findById('EvnInfectNotifyListFilterForm').getForm();
		this.EvnInfectNotifyListSearchFrame.removeAll();
		this.EvnInfectNotifyListSearchFrame.addActions(
			{
				name:'open_emk',
				text:lang['otkryit_emk'],
				tooltip: lang['otkryit_elektronnuyu_meditsinskuyu_kartu_patsienta'],
				iconCls : 'open16',
				handler: function() {
				this.scheduleOpen();
			}.createDelegate(this)}
		);
		this.restore();
		this.center();
		this.maximize();
		this.doReset();		
		this.doLayout();
	},
	title: lang['jurnal_izvescheniy_forma_№058_u'],
	width: 800
});