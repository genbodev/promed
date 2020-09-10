/**
* swAmbulanceCardSearchWindow - окно поиска по картам вызова.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Ambulance
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Pshenicyn Ivan aka IVP (ipshon@rambler.ru)
* @version      22.12.2009
* tabIndex: TABINDEX_AMBCARDSW
*/

sw.Promed.swAmbulanceCardSearchWindow = Ext.extend(sw.Promed.BaseForm, {
	addAmbulanceCard: function() {
/*		var current_window = this;
		
		if (current_window.personSearchWindow.isVisible())
		{
			current_window.showMessage(lang['soobschenie'], lang['okno_poiska_cheloveka_uje_otkryito']);
			return false;
		}

		if (current_window.ambulanceCardEditWindow.isVisible())
		{
			current_window.showMessage(lang['soobschenie'], lang['okno_redaktirovaniya_kartyi_vyizova_uje_otkryito']);
			return false;
		}
		
		var params = {};
		
		params.action = 'add';
		params.callback = function() {
			current_window.refreshAmbulanceCardSearchGrid();
		}
		params.onHide = function() {
			current_window.personSearchWindow.findById('person_search_form').getForm().findField('PersonSurName_SurName').focus(true, 500);
		}
						
		current_window.personSearchWindow.show({
			onClose: function() {
				//current_window.refreshAmbulanceCardSearchGrid();
			},
			onSelect: function(person_data) {
				params.Person_id = person_data.Person_id;
				params.PersonEvn_id = person_data.PersonEvn_id;
				params.Server_id = person_data.Server_id;
				current_window.ambulanceCardEditWindow.show(params);
			},
			searchMode: 'all'
		});
*/
	},
	buttonAlign: 'left',
	doResetAll: function() {
		var form = this.findById('AmbulanceCardViewFilterForm');
		
		if ( form.getForm().findField('PersonCardStateType_id') && form.getForm().findField('PersonCardStateType_id').rendered )
			var pcst = form.getForm().findField('PersonCardStateType_id').getValue();			
		if ( form.getForm().findField('ViewAll_id') && form.getForm().findField('ViewAll_id').rendered )
			var va = form.getForm().findField('ViewAll_id').getValue();
		if ( form.getForm().findField('PrivilegeStateType_id') && form.getForm().findField('PrivilegeStateType_id').rendered )
			var pst = form.getForm().findField('PrivilegeStateType_id').getValue();
			
		form.getForm().reset();
		
		if ( form.getForm().findField('PersonCardStateType_id') && form.getForm().findField('PersonCardStateType_id').rendered )
			form.getForm().findField('PersonCardStateType_id').setValue(pcst);
		if ( form.getForm().findField('ViewAll_id') && form.getForm().findField('ViewAll_id').rendered )
			form.getForm().findField('ViewAll_id').setValue(va);
		if ( form.getForm().findField('PrivilegeStateType_id') && form.getForm().findField('PrivilegeStateType_id').rendered )
			form.getForm().findField('PrivilegeStateType_id').setValue(pst);
		
		var grid = this.findById('AmbulanceCardSearchGrid').ViewGridPanel;
		grid.getStore().removeAll();
	},
	addAmbulanceCard: function() {
		var win = this;
		var params = new Object();
		var action = 'add';
		
		getWnd('swPersonSearchWindow').show( {
			onClose: function()  {
				if (win.SearchGrid && win.SearchGrid.getGrid() && win.SearchGrid.getGrid().getSelectionModel().getSelected())  {
					win.SearchGrid.getGrid().getView().focusRow(win.SearchGrid.getGrid().getStore().indexOf(win.SearchGrid.getGrid().getSelectionModel().getSelected()));
				} else  {
					win.SearchGrid.focus();
				}
			}.createDelegate(this),
			onSelect: function(person_data) {				
				params.Person_id 	= person_data.Person_id;
				params.PersonEvn_id = person_data.PersonEvn_id;
				params.Server_id 	= person_data.Server_id;
				params.AmbulanceCard_id = 0;

				getWnd('swAmbulanceCardEditWindow').show({
					action: action,
					formParams: params
				});
			},
			searchMode: 'all'
		});
	},
	editAmbulanceCard: function() {
		var current_window = this;
		var grid = current_window.findById('AmbulanceCardSearchGrid').ViewGridPanel;
		var current_row = grid.getSelectionModel().getSelected();
		if (!current_row)
			return;
		if (getWnd('swAmbulanceCardEditWindow').isVisible())
		{
			current_window.showMessage(lang['soobschenie'], lang['okno_redaktirovaniya_kartyi_vyizova_uje_otkryito']);
			return false;
		}
		
		var params = {};
		
		params.action = 'edit';
		params.callback = function() {
			current_window.refreshAmbulanceCardSearchGrid();
		}
		params.onHide = function() {
			grid.getView().focusRow(0);
			grid.getSelectionModel().selectFirstRow(0);
		}
		params.AmbulanceCard_id = current_row.data.AmbulanceCard_id;;
		
		getWnd('swAmbulanceCardEditWindow').show(params);
	},
	closable: true,
	closeAction: 'hide',
	collapsible: true,
	draggable: true,
	deleteAmbulanceCard: function() {
		var current_window = this;
		var grid = current_window.findById('AmbulanceCardSearchGrid').ViewGridPanel;
		var current_row = grid.getSelectionModel().getSelected();
		if (!current_row)
			return;
		sw.swMsg.show({
			title: lang['podtverjdenie_udaleniya'],
			msg: lang['vyi_deystvitelno_jelaete_udalit_etu_zapis'],
			buttons: Ext.Msg.YESNO,
			fn: function ( buttonId ) {
				if ( buttonId == 'yes' )
				{
					Ext.Ajax.request({
						url: '?c=AmbulanceCard&m=deleteAmbulanceCard',
						params: {PersonDisp_id: current_row.data.PersonDisp_id},
						callback: function() {
							current_window.doSearch();
						}
					});
				}
			}
		});
	},
	searchInProgress: false,
	doSearch: function() {
		if (this.searchInProgress) {
			log(lang['poisk_uje_vyipolnyaetsya']);
			return false;
		} else {
			this.searchInProgress = true;
		}
		var thisWindow = this;
		var grid = this.findById('AmbulanceCardSearchGrid').ViewGridPanel;
		var form = this.findById('AmbulanceCardViewFilterForm');
		
		if ( form.isEmpty() ) {
			sw.swMsg.alert(lang['oshibka'], lang['ne_zapolneno_ni_odno_pole'], function() {
			});
			thisWindow.searchInProgress = false;
			return false;
		}
		
		var params = form.getForm().getValues();
		var arr = form.find('disabled', true);				
		
		for (i = 0; i < arr.length; i++) {
			if (arr[i].xtype != "button")
				params[arr[i].hiddenName] = arr[i].getValue();
		}
		params.start = 0;
		params.limit = 100;
		params.callback = function (){
			thisWindow.searchInProgress = false;
		}
		grid.getStore().removeAll();
		grid.getStore().baseParams = params;
		grid.getStore().load({
			params: params
		});
	},
	height: 550,
	id: 'AmbulanceCardSearchWindow',
	getButtonSearch: function() {
		// TODO: правильно юзать scope кнопки
		return Ext.getCmp('ACSW_SearchButton');
	},
	initComponent: function() {
		Ext.apply(this, {
		buttons: [{
			handler: function() {
				this.ownerCt.doSearch();
			},
			iconCls: 'search16',
			id: 'ACSW_SearchButton',
			tabIndex: TABINDEX_AMBCARDSW + 197,
			text: BTN_FRMSEARCH
		}, {
			handler: function() {
				this.ownerCt.doResetAll();
			},
			iconCls: 'resetsearch16',
			tabIndex: TABINDEX_AMBCARDSW + 198,
			text: lang['cbros']
		},
		'-',
		HelpButton(this, -1),
		{
			handler: function() {
				this.ownerCt.hide();
			},
			iconCls: 'cancel16',
			tabIndex: TABINDEX_AMBCARDSW + 199,
			text: BTN_FRMCANCEL,
			onShiftTabAction: function () {
				this.ownerCt.buttons[1].focus();
			},
			onTabAction: function () {
				var current_window = this.ownerCt;
				current_window.findById('AmbulanceCardFilterTabPanel').getActiveTab().fireEvent('activate', current_window.findById('AmbulanceCardFilterTabPanel').getActiveTab());
			}
		}],
			items: [
				new Ext.Panel({
					region: 'center',
					layout: 'border',
					items: [
					getBaseSearchFiltersFrame({
						id: 'AmbulanceCardViewFilterForm',
						ownerWindow: this,
						searchFormType: 'CmpCallCard',
						tabIndexBase: TABINDEX_AMBCARDSW,
						tabPanelId: 'AmbulanceCardFilterTabPanel',
						tabs: [{
							autoHeight: true,
							bodyStyle: 'margin-top: 5px;',
							border: false,
							labelWidth: 50,
							layout: 'form',
							listeners: {
								'activate': function(panel) {
									var form = this.findById('AmbulanceCardViewFilterForm');							
									form.getForm().findField('RJON').focus(true, 100);
								}.createDelegate(this)
							},								
							title: lang['6_o1'],
							items: [{
								border: false,
								layout: 'column',
								items: [{
									border: false,
									layout: 'form',
									items:[{
										allowBlank: true,
										fieldLabel: lang['rayon'],
										hiddenName: 'RJON',
										width: 180,
										tabIndex: TABINDEX_AMBCARDSW + 56,
										xtype: 'swklareastatcombo'										
									}, {
										xtype: 'textfield',
										maskRe: /[^%]/,
										//maskRe: /\d/,
										fieldLabel: lang['punkt'],
										minLength: 0,
										autoCreate: {tag: "input", type: "text", size: "22", maxLength: "22", autocomplete: "off"},
										width: 180,
										name: 'CITY',
										tabIndex: TABINDEX_AMBCARDSW + 57
									}, {
										xtype: 'textfield',
										maskRe: /[^%]/,
										fieldLabel: lang['ulitsa'],
										minLength: 0,
										autoCreate: {tag: "input", type: "text", size: "22", maxLength: "22", autocomplete: "off"},
										width: 180,
										name: 'ULIC',
										tabIndex: TABINDEX_AMBCARDSW + 58
									}, {
										xtype: 'textfield',
										maskRe: /[^%]/,
										//maskRe: /\d/,
										fieldLabel: lang['dom'],
										minLength: 0,
										autoCreate: {tag: "input", type: "text", size: "22", maxLength: "22", autocomplete: "off"},
										width: 180,
										name: 'DOM',
										tabIndex: TABINDEX_AMBCARDSW + 59
									}]
								}, {
									border: false,
									layout: 'form',
									items:[{
										xtype: 'textfield',
										maskRe: /[^%]/,
										//maskRe: /\d/,
										fieldLabel: lang['kv'],
										minLength: 0,
										autoCreate: {tag: "input", type: "text", size: "4", maxLength: "4", autocomplete: "off"},
										width: 50,
										name: 'KVAR',
										tabIndex: TABINDEX_AMBCARDSW + 60
									}, {
										xtype: 'textfield',
										maskRe: /[^%]/,
										//maskRe: /\d/,
										fieldLabel: lang['kod'],
										minLength: 0,
										autoCreate: {tag: "input", type: "text", size: "10", maxLength: "10", autocomplete: "off"},
										width: 180,
										name: 'KODP',
										tabIndex: TABINDEX_AMBCARDSW + 61
									}, {
										xtype: 'textfield',
										maskRe: /[^%]/,
										//maskRe: /\d/,
										fieldLabel: lang['tlf'],
										minLength: 0,
										autoCreate: {tag: "input", type: "text", size: "12", maxLength: "12", autocomplete: "off"},
										width: 180,
										name: 'TELF',
										tabIndex: TABINDEX_AMBCARDSW + 62
									}, {
										allowBlank: true,
										hiddenName: 'MEST',
										width: 180,
										tabIndex: TABINDEX_AMBCARDSW + 63,
										xtype: 'swcmpplacecombo'										
									}]
								}, {
									border: false,
									layout: 'form',
									items:[{
										xtype: 'textfield',
										maskRe: /\d/,
										fieldLabel: lang['pod'],
										minLength: 0,
										autoCreate: {tag: "input", type: "text", size: "2", maxLength: "2", autocomplete: "off"},
										width: 50,
										name: 'PODZ',
										tabIndex: TABINDEX_AMBCARDSW + 64
									}]
								}, {
									border: false,
									layout: 'form',
									items:[{
										xtype: 'textfield',
										maskRe: /\d/,
										fieldLabel: lang['et'],
										minLength: 0,
										autoCreate: {tag: "input", type: "text", size: "2", maxLength: "2", autocomplete: "off"},
										width: 50,
										name: 'ETAJ',
										tabIndex: TABINDEX_AMBCARDSW + 65
									}]
								}]
							}, {
								xtype: 'textfield',
								maskRe: /[^%]/,
								//maskRe: /\d/,
								fieldLabel: '!',
								minLength: 0,
								autoCreate: {tag: "input", type: "text", size: "51", maxLength: "51", autocomplete: "off"},
								width: 180,
								name: 'COMM',
								tabIndex: TABINDEX_AMBCARDSW + 66
							}, {
								allowBlank: true,
								hiddenName: 'POVD',
								width: 180,
								tabIndex: TABINDEX_AMBCARDSW + 67,
								xtype: 'swcmpreasoncombo'
							}, {
								border: false,
								layout: 'column',
								items: [{
									border: false,
									layout: 'form',
									items:[{
										xtype: 'textfieldpmw',
										maskRe: /[^%]/,
										fieldLabel: lang['fam'],
										autoCreate: {tag: "input", type: "text", size: "15", maxLength: "15", autocomplete: "off"},
										width: 180,
										name: 'FAM',
										tabIndex: TABINDEX_AMBCARDSW + 68
									}, {
										xtype: 'textfieldpmw',
										maskRe: /[^%]/,
										fieldLabel: lang['otchestvo'],
										autoCreate: {tag: "input", type: "text", size: "15", maxLength: "15", autocomplete: "off"},
										width: 180,
										name: 'OTCH',
										tabIndex: TABINDEX_AMBCARDSW + 69
									}]
								}, {
									border: false,
									layout: 'form',
									items:[{
										xtype: 'textfieldpmw',
										maskRe: /[^%]/,
										fieldLabel: lang['imya'],
										autoCreate: {tag: "input", type: "text", size: "14", maxLength: "14", autocomplete: "off"},
										width: 180,
										name: 'IMYA',
										tabIndex: TABINDEX_AMBCARDSW + 70
									}, {
										xtype: 'textfieldpmw',
										maskRe: /[^%]/,
										fieldLabel: lang['vyizval'],
										autoCreate: {tag: "input", type: "text", size: "20", maxLength: "20", autocomplete: "off"},
										width: 180,
										name: 'KTOV',
										tabIndex: TABINDEX_AMBCARDSW + 71
									}]
								}, {
									border: false,
									layout: 'form',
									items:[{
										xtype: 'textfield',
										maskRe: /\d/,
										fieldLabel: lang['vozr'],
										minLength: 0,
										autoCreate: {tag: "input", type: "text", size: "3", maxLength: "3", autocomplete: "off"},
										width: 50,
										name: 'VOZR',
										tabIndex: TABINDEX_AMBCARDSW + 72
									}]
								}, {
									border: false,
									layout: 'form',
									items:[{
										xtype: 'swpersonsexcombo',
										hiddenName: 'POL',
										enableKeyEvents: true,
										width: 95,
										editable: false,
										codeField: 'Sex_Code',
										tabIndex: TABINDEX_AMBCARDSW + 73
									}]
								}]
							}]							
						}, {
							autoHeight: true,
							bodyStyle: 'margin-top: 5px;',
							border: false,
							labelWidth: 65,
							layout: 'form',
							listeners: {
								'activate': function(panel) {
									var form = this.findById('AmbulanceCardViewFilterForm');							
									form.getForm().findField('NUMV').focus(true, 100);
								}.createDelegate(this)
							},								
							title: lang['7_o2'],
							items: [{
								border: false,
								layout: 'column',
								items: [{
									border: false,
									layout: 'form',
									items:[{
										xtype: 'textfield',
										maskRe: /\d/,
										fieldLabel: lang['nomer'],
										minLength: 0,
										autoCreate: {tag: "input", type: "text", size: "4", maxLength: "4", autocomplete: "off"},
										width: 50,
										name: 'NUMV',
										tabIndex: TABINDEX_AMBCARDSW + 74
									}]
								}, {
									border: false,
									layout: 'form',
									items:[{
										xtype: 'textfield',
										maskRe: /\d/,
										fieldLabel: lang['skv_nom'],
										minLength: 0,
										autoCreate: {tag: "input", type: "text", size: "7", maxLength: "7", autocomplete: "off"},
										width: 50,
										name: 'NGOD',
										tabIndex: TABINDEX_AMBCARDSW + 75
									}]
								}]
							}, {
								/*xtype: 'textfield',
								maskRe: /\d/,
								fieldLabel: lang['tip_vyiz'],
								minLength: 0,
								autoCreate: {tag: "input", type: "text", size: "1", maxLength: "1", autocomplete: "off"},
								width: 50,
								name: 'POVT',
								tabIndex: TABINDEX_AMBCARDSW + 76*/
								allowBlank: true,
								hiddenName: 'POVT',
								width: 180,
								tabIndex: TABINDEX_AMBCARDSW + 76,
								xtype: 'swcmpcalltypecombo'
							}, {
								border: false,
								layout: 'column',
								items: [{
									border: false,
									layout: 'form',
									items:[{
										xtype: 'textfield',
										maskRe: /\d/,
										fieldLabel: lang['sroch'],
										minLength: 0,
										autoCreate: {tag: "input", type: "text", size: "2", maxLength: "2", autocomplete: "off"},
										width: 50,
										name: 'PRTY',
										tabIndex: TABINDEX_AMBCARDSW + 77
									}]
								}, {
									border: false,
									layout: 'form',
									items:[{
										/*xtype: 'textfield',
										maskRe: /\d/,
										fieldLabel: lang['prf'],
										minLength: 0,
										autoCreate: {tag: "input", type: "text", size: "2", maxLength: "2", autocomplete: "off"},
										width: 50,
										name: 'PROF',
										tabIndex: TABINDEX_AMBCARDSW + 78*/
										allowBlank: true,
										hiddenName: 'PROF',
										width: 180,
										tabIndex: TABINDEX_AMBCARDSW + 78,
										xtype: 'swcmpprofilecombo'
									}]
								}]
							}, {
								border: false,
								layout: 'column',
								items: [{
									border: false,
									layout: 'form',
									items:[{
										xtype: 'textfield',
										maskRe: /\d/,
										fieldLabel: lang['sekt'],
										minLength: 0,
										autoCreate: {tag: "input", type: "text", size: "4", maxLength: "4", autocomplete: "off"},
										width: 50,
										name: 'SECT',
										tabIndex: TABINDEX_AMBCARDSW + 79
									}]
								}, {
									border: false,
									layout: 'form',
									items:[{
										xtype: 'textfield',
										maskRe: /\d/,
										fieldLabel: lang['smp'],
										minLength: 0,
										autoCreate: {tag: "input", type: "text", size: "4", maxLength: "4", autocomplete: "off"},
										width: 50,
										name: 'SMPT',
										tabIndex: TABINDEX_AMBCARDSW + 80
									}]
								}, {
									border: false,
									layout: 'form',
									items:[{
										xtype: 'textfield',
										maskRe: /\d/,
										fieldLabel: lang['p_c'],
										minLength: 0,
										autoCreate: {tag: "input", type: "text", size: "2", maxLength: "2", autocomplete: "off"},
										width: 50,
										name: 'STAN',
										tabIndex: TABINDEX_AMBCARDSW + 81
									}]
								}]
							}, {
								border: false,
								layout: 'column',
								items: [{
									border: false,
									layout: 'form',
									items:[{
										xtype: 'swdatefield',
										fieldLabel: lang['data'],
										name: 'DPRM',
										minLength: 0,
										width: 100,
										plugins: [
											new Ext.ux.InputTextMask('99.99.9999', false)
										],
										tabIndex: TABINDEX_AMBCARDSW + 82
									}, {
										xtype: 'textfield',
										//maskRe: /\d:/,
										fieldLabel: 'Принят',
										minLength: 0,
										plugins: [
											new Ext.ux.InputTextMask('99:99', false)
										],
										//autoCreate: {tag: "input", type: "text", size: "5", maxLength: "5", autocomplete: "off"},
										width: 50,
										name: 'TPRM',
										tabIndex: TABINDEX_AMBCARDSW + 83
									}, {
										xtype: 'textfield',
										//maskRe: /\d:/,
										fieldLabel: lang['peredan'],
										minLength: 0,
										plugins: [
											new Ext.ux.InputTextMask('99:99', false)
										],
										//autoCreate: {tag: "input", type: "text", size: "5", maxLength: "5", autocomplete: "off"},
										width: 50,
										name: 'TPER',
										tabIndex: TABINDEX_AMBCARDSW + 84
									}]
								}, {
									border: false,
									layout: 'form',
									items:[{
										xtype: 'textfield',
										maskRe: /\d/,
										fieldLabel: lang['den'],
										minLength: 0,
										autoCreate: {tag: "input", type: "text", size: "1", maxLength: "1", autocomplete: "off"},
										width: 50,
										name: 'WDAY',
										tabIndex: TABINDEX_AMBCARDSW + 85
									}, {
										xtype: 'textfield',
										maskRe: /\d/,
										fieldLabel: lang['pult'],
										minLength: 0,
										autoCreate: {tag: "input", type: "text", size: "2", maxLength: "2", autocomplete: "off"},
										width: 50,
										name: 'LINE',
										tabIndex: TABINDEX_AMBCARDSW + 86
									}/*, {
										xtype: 'textfield',
										maskRe: /\d/,
										fieldLabel: lang['isp'],
										minLength: 0,
										autoCreate: {tag: "input", type: "text", size: "4", maxLength: "4", autocomplete: "off"},
										width: 50,
										name: 'SECT',
										tabIndex: TABINDEX_AMBCARDSW + 87
									}*/]
								}]
							}]
						}, {
							autoHeight: true,
							bodyStyle: 'margin-top: 5px;',
							border: false,
							labelWidth: 65,
							layout: 'form',
							listeners: {
								'activate': function(panel) {
									var form = this.findById('AmbulanceCardViewFilterForm');							
									if ( form.getForm().findField('KUDA').getStore().getCount() == 0 )
									{
										form.getForm().findField('KUDA').getStore().load({
											success: function() {
												form.getForm().findField('KUDA').setValue(form.getForm().findField('KUDA').getValue());
											}
										});
									}
									form.getForm().findField('REZL').focus(true, 100);
								}.createDelegate(this)
							},								
							title: lang['8_o3'],
							items: [{
								border: false,
								layout: 'column',
								items: [{
									border: false,
									layout: 'form',
									items:[{
										/*xtype: 'textfield',
										maskRe: /\d/,
										fieldLabel: lang['rez-t'],
										minLength: 0,
										autoCreate: {tag: "input", type: "text", size: "2", maxLength: "2", autocomplete: "off"},
										width: 50,
										name: 'REZL',
										tabIndex: TABINDEX_AMBCARDSW + 88*/
										allowBlank: true,
										hiddenName: 'REZL',
										width: 180,
										tabIndex: TABINDEX_AMBCARDSW + 88,
										xtype: 'swcmpresultcombo'
									}]
								}, {
									border: false,
									layout: 'form',
									items:[{
										/*xtype: 'textfield',
										maskRe: /\d/,
										fieldLabel: lang['vid'],
										minLength: 0,
										autoCreate: {tag: "input", type: "text", size: "2", maxLength: "2", autocomplete: "off"},
										width: 50,
										name: 'TRAV',
										tabIndex: TABINDEX_AMBCARDSW + 89*/
										allowBlank: true,
										hiddenName: 'TRAV',
										width: 180,
										tabIndex: TABINDEX_AMBCARDSW + 89,
										xtype: 'swcmptraumacombo'
									}]
								}]
							}, {
								border: false,
								layout: 'column',
								items: [{
									border: false,
									layout: 'form',
									items:[{
										/*xtype: 'textfield',
										maskRe: /\d/,
										fieldLabel: lang['rayon'],
										minLength: 0,
										autoCreate: {tag: "input", type: "text", size: "3", maxLength: "3", autocomplete: "off"},
										width: 50,
										name: 'RGSP',
										tabIndex: TABINDEX_AMBCARDSW + 90*/
										allowBlank: true,
										fieldLabel: lang['rayon'],
										hiddenName: 'RGSP',
										width: 180,
										tabIndex: TABINDEX_AMBCARDSW + 90,
										xtype: 'swklareastatcombo'
									}]
								}, {
									border: false,
									layout: 'form',
									items:[{
										/*xtype: 'textfield',
										//maskRe: /\d/,
										fieldLabel: lang['kuda'],
										minLength: 0,
										autoCreate: {tag: "input", type: "text", size: "36", maxLength: "36", autocomplete: "off"},
										width: 180,
										name: 'KUDA',
										tabIndex: TABINDEX_AMBCARDSW + 91*/
										allowBlank: true,
										autoLoad: true,
										fieldLabel: lang['kuda'],
										hiddenName: 'KUDA',
										width: 180,
										tabIndex: TABINDEX_AMBCARDSW + 91,
										xtype: 'swlpucombo'
									}]
								}]
							}, {
								border: false,
								layout: 'column',
								items: [{
									border: false,
									layout: 'form',
									items:[/*{
										xtype: 'textfield',
										maskRe: /\d/,
										fieldLabel: 'Ds',
										minLength: 0,
										autoCreate: {tag: "input", type: "text", size: "5", maxLength: "5", autocomplete: "off"},
										width: 50,
										name: 'DS1',
										tabIndex: TABINDEX_AMBCARDSW + 92
									}*/{
										fieldLabel: 'DS',
										hiddenName: 'DS1',
										tabIndex: TABINDEX_AMBCARDSW + 92,
										width: 180,
										xtype: 'swcmpdiagcombo'
									},/* {
										xtype: 'textfield',
										maskRe: /\d/,
										fieldLabel: 'Ds',
										minLength: 0,
										autoCreate: {tag: "input", type: "text", size: "5", maxLength: "5", autocomplete: "off"},
										width: 50,
										name: 'DS2',
										tabIndex: TABINDEX_AMBCARDSW + 93
									}*/{
										fieldLabel: 'DS',
										hiddenName: 'DS2',
										tabIndex: TABINDEX_AMBCARDSW + 93,
										width: 180,
										xtype: 'swcmpdiagcombo'
									}]
								}, {
									border: false,
									layout: 'form',
									items:[{
										/*xtype: 'textfield',
										maskRe: /\d/,
										fieldLabel: lang['alk'],
										minLength: 0,
										autoCreate: {tag: "input", type: "text", size: "1", maxLength: "1", autocomplete: "off"},
										width: 50,
										name: 'ALK',
										tabIndex: TABINDEX_AMBCARDSW + 94*/
										allowBlank: true,
										fieldLabel: lang['alk'],
										hiddenName: 'ALK',
										width: 180,
										tabIndex: TABINDEX_AMBCARDSW + 94,
										xtype: 'swyesnocombo'
									},/* {
										xtype: 'textfield',
										//maskRe: /\d/,
										fieldLabel: lang['mkb'],
										minLength: 0,
										autoCreate: {tag: "input", type: "text", size: "5", maxLength: "5", autocomplete: "off"},
										width: 50,
										name: 'MKB',
										tabIndex: TABINDEX_AMBCARDSW + 95
									}*/{
										fieldLabel: lang['mkb'],
										hiddenName: 'MKB',
										tabIndex: TABINDEX_AMBCARDSW + 95,
										width: 180,
										xtype: 'swdiagcombo'
									}]
								}]
							}]
						}, {
							autoHeight: true,
							bodyStyle: 'margin-top: 5px;',
							border: false,
							labelWidth: 65,
							layout: 'form',
							listeners: {
								'activate': function(panel) {
									var form = this.findById('AmbulanceCardViewFilterForm');							
									form.getForm().findField('NUMB').focus(true, 100);
								}.createDelegate(this)
							},								
							title: lang['9_o4'],
							items: [{
								border: false,
								layout: 'column',
								items: [{
									border: false,
									layout: 'form',
									items:[{
										xtype: 'textfield',
										maskRe: /\d/,
										fieldLabel: lang['brigada'],
										minLength: 0,
										autoCreate: {tag: "input", type: "text", size: "4", maxLength: "4", autocomplete: "off"},
										width: 50,
										name: 'NUMB',
										tabIndex: TABINDEX_AMBCARDSW + 96
									}]
								}, {
									border: false,
									layout: 'form',
									items:[{
										xtype: 'textfield',
										maskRe: /\d/,
										fieldLabel: lang['ssmp'],
										minLength: 0,
										autoCreate: {tag: "input", type: "text", size: "4", maxLength: "4", autocomplete: "off"},
										width: 50,
										name: 'SMPB',
										tabIndex: TABINDEX_AMBCARDSW + 97
									}]
								}]
							}, {
								border: false,
								layout: 'column',
								items: [{
									border: false,
									layout: 'form',
									items:[{
										xtype: 'textfield',
										maskRe: /\d/,
										fieldLabel: lang['p_s'],
										minLength: 0,
										autoCreate: {tag: "input", type: "text", size: "2", maxLength: "2", autocomplete: "off"},
										width: 50,
										name: 'STBR',
										tabIndex: TABINDEX_AMBCARDSW + 98
									}]
								}, {
									border: false,
									layout: 'form',
									items:[{
										xtype: 'textfield',
										maskRe: /\d/,
										fieldLabel: '/',
										minLength: 0,
										autoCreate: {tag: "input", type: "text", size: "2", maxLength: "2", autocomplete: "off"},
										width: 50,
										name: 'STBB',
										tabIndex: TABINDEX_AMBCARDSW + 99
									}]
								}, {
									border: false,
									layout: 'form',
									items:[{
										/*xtype: 'textfield',
										//maskRe: /\d/,
										fieldLabel: lang['prf'],
										minLength: 0,
										autoCreate: {tag: "input", type: "text", size: "2", maxLength: "2", autocomplete: "off"},
										width: 50,
										name: 'PRFB',
										tabIndex: TABINDEX_AMBCARDSW + 100*/
										allowBlank: true,
										fieldLabel: lang['prf'],
										hiddenName: 'PRFB',
										width: 180,
										tabIndex: TABINDEX_AMBCARDSW + 100,
										xtype: 'swcmpprofilecombo'
									}]
								}]
							}, {
								border: false,
								layout: 'column',
								items: [{
									border: false,
									layout: 'form',
									items:[{
										xtype: 'textfield',
										maskRe: /\d/,
										fieldLabel: lang['mashina'],
										minLength: 0,
										autoCreate: {tag: "input", type: "text", size: "4", maxLength: "4", autocomplete: "off"},
										width: 50,
										name: 'NCAR',
										tabIndex: TABINDEX_AMBCARDSW + 101
									}]
								}, {
									border: false,
									layout: 'form',
									items:[{
										xtype: 'textfield',
										//maskRe: /\d/,
										fieldLabel: lang['ratsiya'],
										minLength: 0,
										autoCreate: {tag: "input", type: "text", size: "4", maxLength: "4", autocomplete: "off"},
										width: 50,
										name: 'RCOD',
										tabIndex: TABINDEX_AMBCARDSW + 102
									}]
								}]
							}, {
								xtype: 'textfield',
								maskRe: /\d/,
								fieldLabel: lang['sb'],
								minLength: 0,
								autoCreate: {tag: "input", type: "text", size: "4", maxLength: "4", autocomplete: "off"},
								width: 50,
								name: 'TABN',
								tabIndex: TABINDEX_AMBCARDSW + 103
							}, {
								border: false,
								layout: 'column',
								items: [{
									border: false,
									layout: 'form',
									items:[{
										xtype: 'textfield',
										maskRe: /\d/,
										fieldLabel: lang['p1'],
										minLength: 0,
										autoCreate: {tag: "input", type: "text", size: "4", maxLength: "4", autocomplete: "off"},
										width: 50,
										name: 'TAB2',
										tabIndex: TABINDEX_AMBCARDSW + 104
									}]
								}, {
									border: false,
									layout: 'form',
									items:[{
										xtype: 'textfield',
										maskRe: /\d/,
										fieldLabel: lang['p2'],
										minLength: 0,
										autoCreate: {tag: "input", type: "text", size: "4", maxLength: "4", autocomplete: "off"},
										width: 50,
										name: 'TAB3',
										tabIndex: TABINDEX_AMBCARDSW + 105
									}]
								}, {
									border: false,
									layout: 'form',
									items:[{
										xtype: 'textfield',
										maskRe: /\d/,
										fieldLabel: lang['v'],
										minLength: 0,
										autoCreate: {tag: "input", type: "text", size: "4", maxLength: "4", autocomplete: "off"},
										width: 50,
										name: 'TAB4',
										tabIndex: TABINDEX_AMBCARDSW + 106
									}]
								}]
							}]
						}, {
							autoHeight: true,
							bodyStyle: 'margin-top: 5px;',
							border: false,
							labelWidth: 65,
							layout: 'form',
							listeners: {
								'activate': function(panel) {
									var form = this.findById('AmbulanceCardViewFilterForm');							
									form.getForm().findField('SMPP').focus(true, 100);
								}.createDelegate(this)
							},								
							title: lang['10_o5-1'],
							items: [{
								border: false,
								layout: 'column',
								items: [{
									border: false,
									layout: 'form',
									items:[{
										xtype: 'textfield',
										maskRe: /\d/,
										fieldLabel: lang['ssmp'],
										minLength: 0,
										autoCreate: {tag: "input", type: "text", size: "4", maxLength: "4", autocomplete: "off"},
										width: 50,
										name: 'SMPP',
										tabIndex: TABINDEX_AMBCARDSW + 107
									}]
								}, {
									border: false,
									layout: 'form',
									items:[{
										xtype: 'textfield',
										maskRe: /\d/,
										fieldLabel: lang['st_vrach'],
										minLength: 0,
										autoCreate: {tag: "input", type: "text", size: "4", maxLength: "4", autocomplete: "off"},
										width: 50,
										name: 'VR51',
										tabIndex: TABINDEX_AMBCARDSW + 108
									}]
								}, {
									border: false,
									layout: 'form',
									items:[{
										xtype: 'textfield',
										maskRe: /\d/,
										fieldLabel: lang['st_disp'],
										minLength: 0,
										autoCreate: {tag: "input", type: "text", size: "4", maxLength: "4", autocomplete: "off"},
										width: 50,
										name: 'D201',
										tabIndex: TABINDEX_AMBCARDSW + 109
									}]
								}]
							}, {
								border: false,
								layout: 'column',
								items: [{
									border: false,
									layout: 'form',
									items:[{
										xtype: 'textfield',
										maskRe: /\d/,
										fieldLabel: lang['prinyal'],
										minLength: 0,
										autoCreate: {tag: "input", type: "text", size: "4", maxLength: "4", autocomplete: "off"},
										width: 50,
										name: 'DSP1',
										tabIndex: TABINDEX_AMBCARDSW + 110
									}]
								}, {
									border: false,
									layout: 'form',
									items:[{
										xtype: 'textfield',
										maskRe: /\d/,
										fieldLabel: lang['naznachil'],
										minLength: 0,
										autoCreate: {tag: "input", type: "text", size: "4", maxLength: "4", autocomplete: "off"},
										width: 50,
										name: 'DSP2',
										tabIndex: TABINDEX_AMBCARDSW + 111
									}]
								}, {
									border: false,
									layout: 'form',
									items:[{
										xtype: 'textfield',
										maskRe: /\d/,
										fieldLabel: lang['peredal'],
										minLength: 0,
										autoCreate: {tag: "input", type: "text", size: "4", maxLength: "4", autocomplete: "off"},
										width: 50,
										name: 'DSPP',
										tabIndex: TABINDEX_AMBCARDSW + 112
									}]
								}]
							}, {
								border: false,
								layout: 'column',
								items: [{
									border: false,
									layout: 'form',
									items:[{
										xtype: 'textfield',
										maskRe: /\d/,
										fieldLabel: lang['zakryil'],
										minLength: 0,
										autoCreate: {tag: "input", type: "text", size: "4", maxLength: "4", autocomplete: "off"},
										width: 50,
										name: 'DSP3',
										tabIndex: TABINDEX_AMBCARDSW + 113
									}]
								}, {
									border: false,
									layout: 'form',
									items:[{
										xtype: 'textfield',
										maskRe: /\d/,
										fieldLabel: lang['poluchen'],
										minLength: 0,
										autoCreate: {tag: "input", type: "text", size: "1", maxLength: "1", autocomplete: "off"},
										width: 50,
										name: 'KAKP',
										tabIndex: TABINDEX_AMBCARDSW + 114
									}]
								}/*, {
									border: false,
									layout: 'form',
									items:[{
										xtype: 'textfield',
										//maskRe: /\d/,
										fieldLabel: lang['peredan'],
										minLength: 0,
										plugins: [
											new Ext.ux.InputTextMask('99:99', false)
										],
										//autoCreate: {tag: "input", type: "text", size: "5", maxLength: "5", autocomplete: "off"},
										width: 50,
										name: 'TPER',
										tabIndex: TABINDEX_AMBCARDSW + 115
									}]
								}*/]
							}, {
								border: false,
								layout: 'column',
								items: [{
									border: false,
									layout: 'form',
									items:[{
										xtype: 'textfield',
										//maskRe: /\d/,
										fieldLabel: lang['vyiezd'],
										minLength: 0,
										plugins: [
											new Ext.ux.InputTextMask('99:99', false)
										],
										//autoCreate: {tag: "input", type: "text", size: "5", maxLength: "5", autocomplete: "off"},
										width: 50,
										name: 'VYEZ',
										tabIndex: TABINDEX_AMBCARDSW + 116
									}]
								}, {
									border: false,
									layout: 'form',
									items:[{
										xtype: 'textfield',
										//maskRe: /\d/,
										fieldLabel: lang['pribyil'],
										minLength: 0,
										plugins: [
											new Ext.ux.InputTextMask('99:99', false)
										],
										//autoCreate: {tag: "input", type: "text", size: "5", maxLength: "5", autocomplete: "off"},
										width: 50,
										name: 'PRZD',
										tabIndex: TABINDEX_AMBCARDSW + 117
									}]
								}, {
									border: false,
									layout: 'form',
									items:[{
										xtype: 'textfield',
										//maskRe: /\d/,
										fieldLabel: lang['gospit'],
										plugins: [
											new Ext.ux.InputTextMask('99:99', false)
										],
										minLength: 0,
										//autoCreate: {tag: "input", type: "text", size: "5", maxLength: "5", autocomplete: "off"},
										width: 50,
										name: 'TGSP',
										tabIndex: TABINDEX_AMBCARDSW + 118
									}]
								}]
							}, {
								border: false,
								layout: 'column',
								items: [{
									border: false,
									layout: 'form',
									items:[{
										xtype: 'textfield',
										//maskRe: /\d/,
										fieldLabel: lang['v_stats'],
										plugins: [
											new Ext.ux.InputTextMask('99:99', false)
										],
										minLength: 0,
										//autoCreate: {tag: "input", type: "text", size: "5", maxLength: "5", autocomplete: "off"},
										width: 50,
										name: 'TSTA',
										tabIndex: TABINDEX_AMBCARDSW + 119
									}]
								}, {
									border: false,
									layout: 'form',
									items:[{
										xtype: 'textfield',
										//maskRe: /\d/,
										fieldLabel: lang['ispoln'],
										plugins: [
											new Ext.ux.InputTextMask('99:99', false)
										],
										minLength: 0,
										//autoCreate: {tag: "input", type: "text", size: "5", maxLength: "5", autocomplete: "off"},
										width: 50,
										name: 'TISP',
										tabIndex: TABINDEX_AMBCARDSW + 120
									}]
								}, {
									border: false,
									layout: 'form',
									items:[{
										xtype: 'textfield',
										//maskRe: /\d/,
										fieldLabel: lang['vozvr'],
										plugins: [
											new Ext.ux.InputTextMask('99:99', false)
										],
										minLength: 0,
										//autoCreate: {tag: "input", type: "text", size: "5", maxLength: "5", autocomplete: "off"},
										width: 50,
										name: 'TVZV',
										tabIndex: TABINDEX_AMBCARDSW + 121
									}]
								}]
							}, {
								border: false,
								layout: 'column',
								items: [{
									border: false,
									layout: 'form',
									items:[{
										allowDecimals: true,
										allowNegative: false,
										xtype: 'numberfield',
										fieldLabel: lang['kilometr'],
										maxValue: 9999.99,
										width: 50,
										name: 'KILO',
										tabIndex: TABINDEX_AMBCARDSW + 122
									}]
								}, {
									border: false,
									layout: 'form',
									items:[{
										xtype: 'textfield',
										maskRe: /\d/,
										fieldLabel: lang['dlit_03'],
										minLength: 0,
										autoCreate: {tag: "input", type: "text", size: "3", maxLength: "3", autocomplete: "off"},
										width: 50,
										name: 'DLIT',
										tabIndex: TABINDEX_AMBCARDSW + 123
									}]
								}, {
									border: false,
									layout: 'form',
									items:[{
										xtype: 'textfield',
										maskRe: /[^%]/,
										//maskRe: /\d/,
										fieldLabel: lang['predloj'],
										minLength: 0,
										autoCreate: {tag: "input", type: "text", size: "3", maxLength: "3", autocomplete: "off"},
										width: 50,
										name: 'PRDL',
										tabIndex: TABINDEX_AMBCARDSW + 124
									}]
								}]
							}]
						}, {
							autoHeight: true,
							bodyStyle: 'margin-top: 5px;',
							border: false,
							labelWidth: 130,
							layout: 'form',
							listeners: {
								'activate': function(panel) {
									var form = this.findById('AmbulanceCardViewFilterForm');							
									form.getForm().findField('POLI').focus(true, 100);
								}.createDelegate(this)
							},								
							title: lang['11_o5-2'],
							items: [{
								border: false,
								layout: 'column',
								items: [{
									border: false,
									layout: 'form',
									items:[{
										/*xtype: 'textfield',
										maskRe: /\d/,
										fieldLabel: lang['aktiv'],
										minLength: 0,
										autoCreate: {tag: "input", type: "text", size: "1", maxLength: "1", autocomplete: "off"},
										width: 50,
										name: 'POLI',
										tabIndex: TABINDEX_AMBCARDEW + 125*/
										allowBlank: true,
										fieldLabel: lang['aktiv'],
										hiddenName: 'POLI',
										width: 180,
										tabIndex: TABINDEX_AMBCARDSW + 125,
										xtype: 'swyesnocombo'
									}]
								}, {
									border: false,
									layout: 'form',
									items:[{
										xtype: 'textfield',
										//maskRe: /\d/,
										fieldLabel: lang['s1'],
										minLength: 0,
										autoCreate: {tag: "input", type: "text", size: "20", maxLength: "20", autocomplete: "off"},
										width: 180,
										name: 'IZV1',
										tabIndex: TABINDEX_AMBCARDSW + 126
									}]
								}, {
									border: false,
									layout: 'form',
									items:[{
										xtype: 'textfield',
										//maskRe: /\d/,
										fieldLabel: lang['vremya1'],
										plugins: [
											new Ext.ux.InputTextMask('99:99', false)
										],
										minLength: 0,
										//autoCreate: {tag: "input", type: "text", size: "4", maxLength: "4", autocomplete: "off"},
										width: 50,
										name: 'TIZ1',
										tabIndex: TABINDEX_AMBCARDSW + 127
									}]
								}]
							}, {
								border: false,
								layout: 'column',
								items: [{
									border: false,
									layout: 'form',
									items:[{
										xtype: 'textfield',
										maskRe: /[^%]/,
										//maskRe: /\d/,
										fieldLabel: lang['prichina_zaderjki'],
										minLength: 0,
										autoCreate: {tag: "input", type: "text", size: "30", maxLength: "30", autocomplete: "off"},
										width: 180,
										name: 'INF1',
										tabIndex: TABINDEX_AMBCARDSW + 128
									}]
								}, {
									border: false,
									layout: 'form',
									items:[{
										xtype: 'textfield',
										maskRe: /[^%]/,
										//maskRe: /\d/,
										fieldLabel: lang['diagnostika'],
										minLength: 0,
										autoCreate: {tag: "input", type: "text", size: "30", maxLength: "30", autocomplete: "off"},
										width: 180,
										name: 'INF2',
										tabIndex: TABINDEX_AMBCARDSW + 129
									}]
								}, {
									border: false,
									layout: 'form',
									items:[{
										xtype: 'textfield',
										maskRe: /[^%]/,
										//maskRe: /\d/,
										fieldLabel: lang['prichina_povtora'],
										minLength: 0,
										autoCreate: {tag: "input", type: "text", size: "30", maxLength: "30", autocomplete: "off"},
										width: 180,
										name: 'INF3',
										tabIndex: TABINDEX_AMBCARDSW + 130
									}]
								}]
							}, {
								border: false,
								layout: 'column',
								items: [{
									border: false,
									layout: 'form',
									items:[{
										xtype: 'textfield',
										maskRe: /[^%]/,
										//maskRe: /\d/,
										fieldLabel: lang['taktika'],
										minLength: 0,
										autoCreate: {tag: "input", type: "text", size: "20", maxLength: "20", autocomplete: "off"},
										width: 180,
										name: 'INF4',
										tabIndex: TABINDEX_AMBCARDSW + 131
									}]
								}, {
									border: false,
									layout: 'form',
									items:[{
										xtype: 'textfield',
										maskRe: /[^%]/,
										//maskRe: /\d/,
										fieldLabel: lang['oformlenie_kartyi'],
										minLength: 0,
										autoCreate: {tag: "input", type: "text", size: "20", maxLength: "20", autocomplete: "off"},
										width: 180,
										name: 'INF5',
										tabIndex: TABINDEX_AMBCARDSW + 132
									}]
								}, {
									border: false,
									layout: 'form',
									items:[{
										xtype: 'textfield',
										maskRe: /[^%]/,
										//maskRe: /\d/,
										fieldLabel: lang['otsenka'],
										minLength: 0,
										autoCreate: {tag: "input", type: "text", size: "20", maxLength: "20", autocomplete: "off"},
										width: 180,
										name: 'INF6',
										tabIndex: TABINDEX_AMBCARDSW + 133
									}]
								}]
							}]
						}, {
							autoHeight: true,
							bodyStyle: 'margin-top: 5px;',
							border: false,
							labelWidth: 100,
							layout: 'form',
							listeners: {
								'activate': function(panel) {
									var form = this.findById('AmbulanceCardViewFilterForm');							
									form.getForm().findField('MKOD').focus(true, 100);
								}.createDelegate(this)
							},								
							title: lang['12_o6'],
							items: [{
								xtype: 'textfield',
								maskRe: /\d/,
								fieldLabel: lang['kod'],
								minLength: 0,
								autoCreate: {tag: "input", type: "text", size: "4", maxLength: "4", autocomplete: "off"},
								width: 50,
								name: 'MKOD',
								tabIndex: TABINDEX_AMBCARDSW + 134
							}, {
								xtype: 'textfield',
								maskRe: /[^%]/,
								//maskRe: /\d/,
								fieldLabel: lang['naimen-e'],
								minLength: 0,
								autoCreate: {tag: "input", type: "text", size: "30", maxLength: "30", autocomplete: "off"},
								width: 180,
								name: 'MNAM',
								tabIndex: TABINDEX_AMBCARDSW + 135
							}, {
								xtype: 'textfield',
								maskRe: /[^%]/,
								//maskRe: /\d/,
								fieldLabel: lang['ed_izm'],
								minLength: 0,
								autoCreate: {tag: "input", type: "text", size: "30", maxLength: "30", autocomplete: "off"},
								width: 180,
								name: 'MEDI',
								tabIndex: TABINDEX_AMBCARDSW + 136
							}, {
								xtype: 'textfield',
								maskRe: /[^%]/,
								//maskRe: /\d/,
								fieldLabel: lang['kol-vo'],
								minLength: 0,
								allowDecimals: true,
								allowNegative: false,
								autoCreate: {tag: "input", type: "text", size: "10", maxLength: "10", autocomplete: "off"},
								width: 50,
								name: 'MKOL',
								tabIndex: TABINDEX_AMBCARDSW + 137
							}]
						}, {
							autoHeight: true,
							bodyStyle: 'margin-top: 5px;',
							border: false,
							labelWidth: 65,
							layout: 'form',
							listeners: {
								'activate': function(panel) {
									var form = this.findById('AmbulanceCardViewFilterForm');							
									form.getForm().findField('DSHS').focus(true, 100);
								}.createDelegate(this)
							},								
							title: lang['13_o7'],
							items: [/*{
								xtype: 'textfield',
								maskRe: /\d/,
								fieldLabel: 'DS',
								minLength: 0,
								autoCreate: {tag: "input", type: "text", size: "5", maxLength: "5", autocomplete: "off"},
								width: 50,
								name: 'DSHS',
								tabIndex: TABINDEX_AMBCARDSW + 138
							}*/{
								fieldLabel: 'DS',
								hiddenName: 'DSHS',
								tabIndex: TABINDEX_AMBCARDSW + 138,
								width: 180,
								xtype: 'swdiagcombo'
							}, {
								/*xtype: 'textfield',
								maskRe: /\d/,
								fieldLabel: lang['r'],
								minLength: 0,
								autoCreate: {tag: "input", type: "text", size: "1", maxLength: "1", autocomplete: "off"},
								width: 50,
								name: 'FERR',
								tabIndex: TABINDEX_AMBCARDSW + 139*/
								allowBlank: true,
								hiddenName: 'FERR',
								width: 180,
								tabIndex: TABINDEX_AMBCARDSW + 139,
								xtype: 'swcmptaloncombo'
							}, {
								xtype: 'textfield',
								maskRe: /\d/,
								fieldLabel: lang['e'],
								minLength: 0,
								autoCreate: {tag: "input", type: "text", size: "4", maxLength: "4", autocomplete: "off"},
								width: 50,
								name: 'EXPO',
								tabIndex: TABINDEX_AMBCARDSW + 140
							}]
						}]
					}),
					new sw.Promed.ViewFrame(
					{
						actions:
						[							
							{name: 'action_add', handler: function() {Ext.getCmp('AmbulanceCardSearchWindow').addAmbulanceCard(); }},
							{name: 'action_edit', handler: function() {Ext.getCmp('AmbulanceCardSearchWindow').editAmbulanceCard(); }},
							{name: 'action_view', disabled: true},
							{name: 'action_delete', disabled: true},
							{name: 'action_refresh', disabled: true},
							{name: 'action_print'}
						],
						autoLoadData: false,
						dataUrl: C_SEARCH,
						id: 'AmbulanceCardSearchGrid',
						focusOn: {name:'ACSW_SearchButton', type:'field'},
						pageSize: 100,
						paging: true,
						onEnter: function() {
							Ext.getCmp('AmbulanceCardSearchGrid').ViewActions.action_edit.execute();
						},
						onDblClick: function() {
							Ext.getCmp('AmbulanceCardSearchGrid').ViewActions.action_edit.execute();
						},
						onRowSelect: function(sm, index, record) {
							if ( record.get('IsOurLpu') == 1 )
							{
								Ext.getCmp('AmbulanceCardSearchGrid').setActionDisabled('action_add', true);
								Ext.getCmp('AmbulanceCardSearchGrid').setActionDisabled('action_edit', true);
								Ext.getCmp('AmbulanceCardSearchGrid').setActionDisabled('action_delete', true);
							}
							else
							{
								Ext.getCmp('AmbulanceCardSearchGrid').setActionDisabled('action_add', false);
								Ext.getCmp('AmbulanceCardSearchGrid').setActionDisabled('action_edit', false);
								Ext.getCmp('AmbulanceCardSearchGrid').setActionDisabled('action_delete', false);
							}
						},
						region: 'center',
						root: 'data',
						totalProperty: 'totalCount',
						onBeforeLoadData: function() {
							this.getButtonSearch().disable();
						}.createDelegate(this),
						onLoadData: function() {
							this.getButtonSearch().enable();
						}.createDelegate(this),
						stringfields:
						[
							{name: 'AmbulanceCard_id', type: 'int', header: 'ID', key: true},
							{name: 'NUMV', type: 'int', header: lang['nomer_vyizova']},
							{name: 'DPRM',  type: 'date', header: lang['data'], renderer: Ext.util.Format.dateRenderer('d.m.Y')},
							{name: 'TPRM',  type: 'string', header: 'Принят'},
							{name: 'Person_Surname',  type: 'string', header: lang['familiya'], width: 200},
							{name: 'Person_Firname',  type: 'string', header: lang['imya'], width: 200},
							{name: 'Person_Secname',  type: 'string', header: lang['otchestvo'], width: 200},
							{name: 'Person_Birthday',  type: 'string', header: lang['data_rojdeniya_patsienta'], width: 200},
							{name: 'PAddress_Address',  type: 'string', header: lang['adres'], width: 200}
						],
						toolbar: true
					})
				]})
			]

		});
		sw.Promed.swAmbulanceCardSearchWindow.superclass.initComponent.apply(this, arguments);
	},
	keys: [{
		key: Ext.EventObject.INSERT,
		fn: function(e) {Ext.getCmp("AmbulanceCardSearchWindow").addAmbulanceCard();},
		stopEvent: true
	}, {
		key: "0123456789",
		alt: true,
		fn: function(e) {Ext.getCmp("AmbulanceCardFilterTabPanel").setActiveTab(Ext.getCmp("AmbulanceCardFilterTabPanel").items.items[ e - 49 ]);},
		stopEvent: true
	}, {
		alt: true,
		fn: function(inp, e) {
			var current_window = Ext.getCmp('AmbulanceCardSearchWindow');
			switch (e.getKey())
			{
				case Ext.EventObject.J:
					current_window.hide();
				break;
				case Ext.EventObject.C:
					current_window.doResetAll();
				break;
			}
		},
		key: [ Ext.EventObject.J, Ext.EventObject.C ],
		stopEvent: true
	}],
	layout: 'border',
	maximizable: true,
	minHeight: 550,
	minWidth: 900,
	modal: false,
	plain: true,
	resizable: true,
	refreshAmbulanceCardSearchGrid: function() {
		this.doSearch();
	},
	show: function() {
		sw.Promed.swAmbulanceCardSearchWindow.superclass.show.apply(this, arguments);

		this.restore();
		this.center();
		this.maximize();

		var grid = this.findById('AmbulanceCardSearchGrid').ViewGridPanel;
		grid.getStore().removeAll();
		
		var form = this.findById('AmbulanceCardViewFilterForm');
		
		// режим отображения формы
		this.listMode = false;

		this.doResetAll();

		this.setTitle(WND_AMB_AMBCARDSEARCH);

		var tabPanel = this.findById('AmbulanceCardFilterTabPanel');
		tabPanel.setActiveTab(5);
				
		// для печати списка
		form.getForm().getEl().dom.action = "/?c=Search&m=printSearchResults";
		form.getForm().getEl().dom.method = "post";
		form.getForm().getEl().dom.target = "_blank";
		form.getForm().standardSubmit = true;
	},
	showMessage: function(title, message, fn) {
		if ( !fn )
			fn = function(){};
		Ext.MessageBox.show({
			buttons: Ext.Msg.OK,
			fn: fn,
			icon: Ext.Msg.WARNING,
			msg: message,
			title: title
		});
	},
	title: WND_AMB_AMBCARDSEARCH,
	viewAmbulanceCard: function() {
		var current_window = this;
		var grid = current_window.findById('AmbulanceCardSearchGrid').ViewGridPanel;
		var current_row = grid.getSelectionModel().getSelected();
		if (!current_row)
			return;
		if (getWnd('swAmbulanceCardEditWindow').isVisible())
		{
			current_window.showMessage(lang['soobschenie'], lang['okno_redaktirovaniya_dispansernoy_kartyi_patsienta_uje_otkryito']);
			return false;
		}
		getWnd('swAmbulanceCardEditWindow').show({
			action: 'view',
			callback: function() {
				current_window.refreshAmbulanceCardSearchGrid();
			},
			onHide: function() {
				//current_window.refreshAmbulanceCardSearchGrid();
			},
			PersonDisp_id: current_row.data.PersonDisp_id,
			Person_id: current_row.data.Person_id,
			Server_id: current_row.data.Server_id
		});
	},
	width: 900
});