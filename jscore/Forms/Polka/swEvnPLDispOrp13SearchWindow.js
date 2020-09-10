/**
* swEvnPLDispOrp13SearchWindow - окно поиска карты по диспасеризации детей-сирот
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package    Polka
* @access     public
* @copyright  Copyright (c) 2009 Swan Ltd.
* @author     Марков 
* @version    май 2010
* @comment    Префикс для id компонентов EPLDO13SW (EvnPLDispOrp13SearchWindow)
*             tabIndex: TABINDEX_EPLDO13SW = 9200;
*
*
* Использует: окно редактирования карты по диспасеризации детей-сирот (swEvnPLDispOrp13EditWindow)
*             окно поиска человека (swPersonSearchWindow)
*/
/*NO PARSE JSON*/
sw.Promed.swEvnPLDispOrp13SearchWindow = Ext.extend(sw.Promed.BaseForm, 
{
	/* */
	codeRefresh: true,
	objectName: 'swEvnPLDispOrp13SearchWindow',
	objectSrc: '/jscore/Forms/Polka/swEvnPLDispOrp13SearchWindow.js',
	setEvnIsTransit: function() {
		if ( !lpuIsTransit() ) {
			return false;
		}

		var grid = this.findById('EPLDO13SWEvnPLDispOrpSearchGrid').getGrid();

		if ( !grid || !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('EvnPLDispOrp_id') || grid.getSelectionModel().getSelected().get('EvnPLDispOrp_IsTransit') == 2 ) {
			return false;
		}

		var record = grid.getSelectionModel().getSelected();
		var Evn_IsTransit = 2;

		var params = {
			Evn_id: record.get('EvnPLDispOrp_id'),
			Evn_IsTransit: Evn_IsTransit
		}

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: lang['ustanovka_priznaka_perehodnyiy_sluchay_mejdu_mo'] });
		loadMask.show();

		Ext.Ajax.request({
			callback: function(options, success, response) {
				loadMask.hide();

				if ( success ) {
					var response_obj = Ext.util.JSON.decode(response.responseText);

					if ( response_obj.success == false ) {
						sw.swMsg.alert(lang['oshibka'], response_obj.Error_Msg ? response_obj.Error_Msg : lang['oshibka_pri_ustanovke_priznaka_perehodnyiy_sluchay_mejdu_mo']);
					}
					else {
						record.set('EvnPLDispOrp_IsTransit', Evn_IsTransit);
						record.commit();
						this.findById('EPLDO13SWEvnPLDispOrpSearchGrid').onRowSelect(null, null, record);
					}
				}
				else {
					sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_ustanovke_priznaka_perehodnyiy_sluchay_mejdu_mo']);
				}
			}.createDelegate(this),
			params: params,
			url: C_SETEVNISTRANSIT
		});
	},
	addEvnPLDD: function() 
	{
		var frm = this;

		if (getWnd('swPersonSearchWindow').isVisible())
		{
			Ext.Msg.alert(lang['soobschenie'], lang['okno_poiska_cheloveka_uje_otkryito']);
			return false;
		}

		if (getWnd((frm.stage == 2)?'swEvnPLDispOrp13SecEditWindow':'swEvnPLDispOrp13EditWindow').isVisible())
		{
			Ext.Msg.alert(lang['soobschenie'], lang['okno_redaktirovaniya_kartyi_dispaserizatsii_detey-sirot_uje_otkryito']);
			return false;
		}
		var Year = this.findById('EPLDO13SWYearCombo').getValue();
		
		getWnd('swPersonSearchWindow').show({
			onClose: function() {
				// Установить фокус на первое поле текущей вкладки
				var panel = Ext.getCmp('EPLDO13SWSearchFilterTabbar').getActiveTab();
				var els=panel.findByType('textfield', false);
				if (els==undefined)
					els=panel.findByType('combo', false);
				var el=els[0];
				if (el!=undefined && el.focus)
					el.focus(true, 200);
				var filter_form = frm.findById('EPLDO13_EvnPLDispOrpSearchFilterForm');
				
				if ( !filter_form.isEmpty() ) {
					frm.refreshEvnPLDDList();
				}
			},
			onSelect: function(person_data) {
				// сначала проверим, можем ли мы добавлять карту на этого человека
				Ext.Ajax.request({
					url: '/?c=EvnPLDispOrp13&m=checkIfEvnPLDispOrpExists',
					callback: function(opt, success, response) {
						if (success && response.responseText != '')
						{
							var response_obj = Ext.util.JSON.decode(response.responseText);

							if (!Ext.isEmpty(response_obj.Error_Msg)) {
								sw.swMsg.alert("Ошибка", response_obj.Error_Msg,
									function () {
									}
								);
								return;
							}

							if (frm.stage == 2 && Ext.isEmpty(response_obj.EvnPLDispOrp_fid)) {
								sw.swMsg.alert("Ошибка", "На данного пациента ещё не заведена карта диспансеризации первого этапа");
								return;
							}

							if (response_obj.isEvnPLDispOrpExists) {
								sw.swMsg.alert("Ошибка", "На данного пациента уже заведена карта диспансеризации несовершеннолетнего",
									function () {
									}
								);
								return;
							}

							if (response_obj.isEvnPLDispTeenProfExists) {
								sw.swMsg.alert("Ошибка", "На выбранного пациента в текущем году уже сохранена карта профилактического осмотра несовершеннолетнего.",
									function () {
									}
								);
								return;
							}

							getWnd((frm.stage == 2) ? 'swEvnPLDispOrp13SecEditWindow' : 'swEvnPLDispOrp13EditWindow').show({
								action: 'add',
								DispClass_id: (frm.stage == 2) ? ((response_obj.CategoryChildType == 'orpadopted') ? 8 : 4) : ((response_obj.CategoryChildType == 'orpadopted') ? 7 : 3),
								EvnPLDispOrp_fid: response_obj.EvnPLDispOrp_fid,
								Person_id: person_data.Person_id,
								PersonEvn_id: person_data.PersonEvn_id,
								Server_id: person_data.Server_id,
								Year: Year
							});
							return;
						}
					},
					params: { Person_id: person_data.Person_id, stage: frm.stage, Year: Year }
				});				
			},
			searchMode: (frm.stage == 2)?'ddorpsec':'ddorp',
			Year: Year
		});
	},
	begDate: null,
	begTime: null,
	buttonAlign: 'left',
	closable: true,
	closeAction: 'hide',
	collapsible: true,
	doDeleteEvnPLDD: function(options) {
		if (typeof options != 'object') {
			options = new Object();
		}

		var win = this;
		var grid = win.findById('EPLDO13SWEvnPLDispOrpSearchGrid').getGrid();

		if ( !grid || !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('EvnPLDispOrp_id') ) {
			return false;
		}

		var record = grid.getSelectionModel().getSelected();
		var evn_pl_dd_id = record.get('EvnPLDispOrp_id');

		var params = {
			EvnPLDispOrp_id: evn_pl_dd_id
		};

		if (options.ignoreCheckRegistry) {
			params.ignoreCheckRegistry = 1;
		}

		Ext.Ajax.request({
			callback: function(options, success, response) {
				if ( success ) {
					var response_obj = Ext.util.JSON.decode(response.responseText);

					if ( response_obj.success == false ) {
						sw.swMsg.alert(lang['oshibka'], response_obj.Error_Msg ? response_obj.Error_Msg : lang['oshibka_pri_udalenii_kartyi_dd']);
					}
					else if (response_obj.Alert_Msg) {
						sw.swMsg.show({
							icon: Ext.MessageBox.QUESTION,
							msg: response_obj.Alert_Msg + ' Продолжить?',
							title: lang['podtverjdenie'],
							buttons: Ext.Msg.YESNO,
							fn: function(buttonId, text, obj) {
								if ('yes' == buttonId) {
									options.ignoreCheckRegistry = true;
									win.doDeleteEvnPLDD(options);
								}
							}
						});
					}
					else {
						grid.getStore().remove(record);

						if ( grid.getStore().getCount() == 0 ) {
							LoadEmptyRow(grid);
						}
					}

					grid.getView().focusRow(0);
					grid.getSelectionModel().selectFirstRow();
				}
				else {
					sw.swMsg.alert(lang['oshibka'], lang['pri_udalenii_kartyi_dd_voznikli_oshibki']);
				}
			},
			params: params,
			url: '/?c=EvnPLDispOrp13&m=deleteEvnPLDispOrp'
		});
	},
	deleteEvnPLDD: function() {
		var win = this;
		var grid = win.findById('EPLDO13SWEvnPLDispOrpSearchGrid').getGrid();

		if ( !grid || !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('EvnPLDispOrp_id') ) {
			return false;
		}

		var record = grid.getSelectionModel().getSelected();
		var evn_pl_dd_id = record.get('EvnPLDispOrp_id');

		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId, text, obj) {
				if ( buttonId == 'yes' ) {
					win.doDeleteEvnPLDD();
				}
			},
			icon: Ext.MessageBox.QUESTION,
			msg: lang['udalit_kartu_dd'],
			title: lang['vopros']
		});
	},
	doReset: function() {
		var frm = this;
		var filter_form = frm.findById('EPLDO13_EvnPLDispOrpSearchFilterForm');
		filter_form.getForm().reset();
		frm.findById('EPLDO13SWEvnPLDispOrpSearchGrid').getGrid().getStore().removeAll();
	},
	searchInProgress: false,
	doSearch: function(params) {
		if (this.searchInProgress) {
			log(lang['poisk_uje_vyipolnyaetsya']);
			return false;
		} else {
			this.searchInProgress = true;
		}
		var thisWindow = this;

		if ( params && params['soc_card_id'] )
			var soc_card_id = params['soc_card_id'];
	
		if ( this.isStream )
		{
			this.doStreamInputSearch();
			return true;
		}
		var frm = this;
		var filter_form = frm.findById('EPLDO13_EvnPLDispOrpSearchFilterForm');
		
		if ( filter_form.isEmpty() ) {
			thisWindow.searchInProgress = false;
			sw.swMsg.alert(lang['oshibka'], lang['ne_zapolneno_ni_odno_pole'], function() {
			});
			return false;
		}
		
		var evnpldispdop_grid = frm.findById('EPLDO13SWEvnPLDispOrpSearchGrid').ViewGridPanel;

		var arr = filter_form.find('disabled', true);
		var params = filter_form.getForm().getValues();

		for (i = 0; i < arr.length; i++)
		{
			if (arr[i].getValue)
			{
				if (arr[i].hiddenName != undefined)
					params[arr[i].hiddenName] = arr[i].getValue();
				else if (arr[i].name != undefined)
					params[arr[i].name] = arr[i].getValue();
			}
		}
		
		// фильтрация по году 
		var Year = Ext.getCmp('EPLDO13SWYearCombo').getValue();
		if (Year>0)
		{
			if (filter_form.getForm().findField('EvnPLDispOrp_setDate_Range').getValue1()=='')
			{
				params['EvnPLDispOrp_setDate_Range'] = ('01.01.'+Year+' - '+'31.12.'+Year);
			}
		}
		
		if (filter_form.getForm().isValid())
		{
			if ( soc_card_id )
			{
				var params = {
					soc_card_id: soc_card_id,
					SearchFormType: params.SearchFormType
				};
			}
			params.start = 0;
			params.limit = 100;

			if (!Ext.isEmpty(params.autoLoadArchiveRecords)) {
				frm.findById('EPLDO13SWEvnPLDispOrpSearchGrid').showArchive = true;
			} else {
				frm.findById('EPLDO13SWEvnPLDispOrpSearchGrid').showArchive = false;
			}

			params.SearchFormType = (this.stage == 2)?"EvnPLDispOrpSec":"EvnPLDispOrp",
			evnpldispdop_grid.getStore().removeAll();
			evnpldispdop_grid.getStore().baseParams = params;
			evnpldispdop_grid.getStore().load({
				params: params,
				callback: function (){
					thisWindow.searchInProgress = false;
				}
			});
		}
		else {
			thisWindow.searchInProgress = false;
			sw.swMsg.alert('Поиск', 'Проверьте правильность заполнения полей на форме поиска');
		}
	},
	doStreamInputSearch: function() {
		var grid = this.findById('EPLDO13SWEvnPLDispOrpSearchGrid').ViewGridPanel;
		var form = this.findById('EPLDO13_EvnPLDispOrpSearchFilterForm');
		var thisWindow = this;
		var params = {};
		params.EvnPLDispOrpStream_begDate = this.begDate;
		params.EvnPLDispOrpStream_begTime = this.begTime;
		if ( !params.EvnPLDispOrpStream_begDate && !params.EvnPLDispOrpStream_begTime )
			this.getBegDateTime();
		else
		{
			params.start = 0;
			params.limit = 100;
			params.SearchFormType = (this.stage == 2)?"EvnPLDispOrpSecSteam":"EvnPLDispOrpStream",
			grid.getStore().removeAll();
			grid.getStore().baseParams = params;
			grid.getStore().load({
				callback: function (){
					thisWindow.searchInProgress = false;
				},
				params: params
			});
		}
	},
	draggable: true,
	getBegDateTime: function() {
		var frm = this;
		Ext.Ajax.request({
			url: C_LOAD_CURTIME,
			callback: function(opt, success, response) {
				if (success && response.responseText != '')
				{
					frm.searchInProgress = false;
					var response_obj = Ext.util.JSON.decode(response.responseText);

					frm.begDate = response_obj.begDate;
					frm.begTime = response_obj.begTime;
					if ( frm.isStream ) {
						frm.doStreamInputSearch();
					}
					frm.findById('EPLDO13SWStream_begDateTime').setValue(response_obj.begDate + ' ' + response_obj.begTime);
				}
			}
		});
	},
	height: 550,
	id: 'EvnPLDispOrp13SearchWindow',
	getButtonSearch: function() {
		// TODO: правильно юзать scope кнопки
		return Ext.getCmp('EPLDO13SWSearchButton');
	},
	printCost: function() {
		var grid = this.findById('EPLDO13SWEvnPLDispOrpSearchGrid').getGrid();
		var selected_record = grid.getSelectionModel().getSelected();
		if (selected_record && selected_record.get('EvnPLDispOrp_id')) {
			getWnd('swCostPrintWindow').show({
				Evn_id: selected_record.get('EvnPLDispOrp_id'),
				type: 'EvnPLDispOrp',
				callback: function() {
					grid.getStore().reload();
				}
			});
		}
	},
	checkPrintCost: function() {
		// Печать справки только для закрытых случаев
		var grid = this.findById('EPLDO13SWEvnPLDispOrpSearchGrid').getGrid();
		var menuPrint = this.findById('EPLDO13SWEvnPLDispOrpSearchGrid').getAction('action_print').menu;
		if (menuPrint && menuPrint.printCost) {
			menuPrint.printCost.setDisabled(true);
			var selected_record = grid.getSelectionModel().getSelected();
			if (selected_record && selected_record.get('EvnPLDispOrp_id')) {
				menuPrint.printCost.setDisabled(selected_record.get('EvnPLDispOrp_is') != lang['da']);
			}
		}
	},
	initComponent: function() 
	{
		var win = this;

		this.EditForm = new Ext.Panel(
		{
			autoHeight: true,
			id: 'EPLDO13SWSearchFilterPanel',
			region: 'north',
			layout: 'form',
			border: false,
			labelWidth: 120,
			items: 
			[{
				bodyStyle:'padding:3px; padding-left:5px;',
				layout: 'form',
				xtype: 'panel',
				border: false,
				//frame: true,
				labelWidth: 125,
				items:
				[{
					xtype: 'swbaselocalcombo',
					mode: 'local',
					fieldLabel: lang['god'],
					id: 'EPLDO13SWYearCombo',
					hiddenName: 'EvnPLDisp_Year',
					store: new Ext.data.JsonStore(
					{
						key: 'EvnPLDisp_Year',
						autoLoad: false,
						fields:
						[
							{name:'EvnPLDisp_Year',type: 'int'},
							{name:'count', type: 'int'}
						],
						url: C_EPLD_LOAD_YEARS
					}),
					tpl:'<tpl for="."><div class="x-combo-list-item"><table border="0" width="100%"><tr><td style="width: 40px; color: red">{EvnPLDisp_Year}</td>'+
							'<td style="text-align: right"><b>{count}<b></td></tr></table>'+
							'</div></tpl>',
					region: 'north',
					valueField: 'EvnPLDisp_Year',
					displayField: 'EvnPLDisp_Year',
					editable: false,
					tabIndex: TABINDEX_EPLDO13SW+95,
					enableKeyEvents: true,
					listeners: 
					{
						'keydown': function(combo, e)
						{
							if ( e.getKey() == Ext.EventObject.ENTER )
							{
								e.stopEvent();
								var frm = Ext.getCmp('EvnPLDispOrp13SearchWindow');
								frm.doSearch();
							}
							if (e.shiftKey == false && e.getKey() == Ext.EventObject.TAB)
							{
								e.stopEvent();
								if ( Ext.getCmp('EvnPLDispOrp13SearchWindow').isStream )
								{
									Ext.TaskMgr.start(
									{
										run : function() 
										{
											Ext.TaskMgr.stopAll();
											Ext.getCmp('EPLDO13SWEvnPLDispOrpSearchGrid').focus();
										},
										interval : 200
									});
									return true;
								}
								var panel = Ext.getCmp('EPLDO13SWSearchFilterTabbar').getActiveTab();
								var els=panel.findByType('textfield', false);
								if (els==undefined)
									els=panel.findByType('combo', false);
								var el=els[0];
								if (el!=undefined && el.focus)
									el.focus(true, 200);
							}
						}
					}
				},
				{
					disabled: true,
					fieldLabel: lang['data_nachala_vvoda'],
					id: 'EPLDO13SWStream_begDateTime',
					width: 165,
					xtype: 'textfield',
					tabIndex: TABINDEX_EPLDO13SW+57
				}]
			},
			getBaseSearchFiltersFrame(
			{
				useArchive: 1,
				id: 'EPLDO13_EvnPLDispOrpSearchFilterForm',
				ownerWindow: this,
				region: 'north',
				searchFormType: 'EvnPLDispOrp',
				tabIndexBase: TABINDEX_EPLDO13SW,
				tabPanelId: 'EPLDO13SWSearchFilterTabbar',
				tabGridId: 'EPLDO13SWEvnPLDispOrpSearchGrid',
				tabPanelHeight: 300,
				tabs: 
				[{
					autoHeight: true,
					bodyStyle: 'margin-top: 5px;',
					border: false,
					labelWidth: 200,
					id: 'EPLDO13_FirstTab',
					layout: 'form',
					listeners: 
					{
						'activate': function(panel) 
						{
							var form = this.findById('EPLDO13_EvnPLDispOrpSearchFilterForm');
							form.getForm().findField('EvnPLDispOrp_setDate').focus(400, true);
						}.createDelegate(this)
					},
					title: '<u>6</u>. Дисп. детей-сирот',
					items: [{
						hidden: !getRegionNick().inlist([ 'buryatiya', 'krym' ]),
						layout: 'form',
						border: false,
						items: [{
							hiddenName: 'UslugaComplex_id',
							width: 400,
							fieldLabel: lang['usluga_dispanserizatsii'],
							dispOnly: true,
							nonDispOnly: false,
							xtype: 'swuslugacomplexnewcombo'
						}]
					}, {
						border: false,
						layout: 'column',
						items: [{
							border: false,
							layout: 'form',
							items: [{
								fieldLabel: lang['data_nachala'],
								name: 'EvnPLDispOrp_setDate',
								plugins: [
									new Ext.ux.InputTextMask('99.99.9999', false)
								],
								tabIndex: TABINDEX_EPLDO13SW + 59,
								width: 100,
								xtype: 'swdatefield'
							}, {
								fieldLabel: lang['data_okonchaniya'],
								name: 'EvnPLDispOrp_disDate',
								plugins: [
									new Ext.ux.InputTextMask('99.99.9999', false)
								],
								tabIndex: TABINDEX_EPLDO13SW + 61,
								width: 100,
								xtype: 'swdatefield'
							}]
						}, {
							border: false,
							layout: 'form',
							labelWidth: 225,
							items: [{
								fieldLabel: lang['diapazon_dat_nachala'],
								name: 'EvnPLDispOrp_setDate_Range',
								tabIndex: TABINDEX_EPLDO13SW + 60,
								plugins: [
									new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)
								],
								width: 170,
								xtype: 'daterangefield'
							}, {
								fieldLabel: lang['diapazon_dat_okonchaniya'],
								name: 'EvnPLDispOrp_disDate_Range',
								tabIndex: TABINDEX_EPLDO13SW + 62,
								plugins: [
									new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)
								],
								width: 170,
								xtype: 'daterangefield'
							}]
						}]
					}, {
						autoHeight: true,
						xtype: 'fieldset',
						style: 'padding-left: 0px;',
						layout: 'form',
						items: [{
							border: false,
							layout: 'column',
							items: [{
								border: false,
								layout: 'form',
								items: [{
									allowDecimal: false,
									allowNegative: false,
									fieldLabel: lang['kolichestvo_posescheniy'],
									name: 'EvnPLDispOrp_VizitCount',
									tabIndex: TABINDEX_EPLDO13SW + 63,
									width: 100,
									xtype: 'numberfield'
								}]
							}, {
								border: false,
								layout: 'form',
								labelWidth: 225,
								items: [{
									allowDecimal: false,
									allowNegative: false,
									fieldLabel: lang['kolichestvo_posescheniy_ot'],
									name: 'EvnPLDispOrp_VizitCount_From',
									tabIndex: TABINDEX_EPLDO13SW + 64,
									width: 100,
									xtype: 'numberfield'
								}]
							}, {
								border: false,
								layout: 'form',
								labelWidth: 225,
								items: [{
									allowDecimal: false,
									allowNegative: false,
									fieldLabel: lang['kolichestvo_posescheniy_do'],
									name: 'EvnPLDispOrp_VizitCount_To',
									tabIndex: TABINDEX_EPLDO13SW + 65,
									width: 100,
									xtype: 'numberfield'
								}]
							}]
						}]
					}, {
						border: false,
						layout: 'column',
						items: [{
							border: false,
							layout: 'form',
							items: [
								{
									allowBlank: true,
									editable: false,
									displayField: 'UslugaComplex_Name',
									fieldLabel: lang['usluga'],
									hiddenName: 'EvnPLDisp_UslugaComplex',
									store: new Ext.data.Store({
										autoLoad: false,
										reader: new Ext.data.JsonReader({
											id: 'UslugaComplex_id'
										}, [
											{ name: 'UslugaComplex_id', mapping: 'UslugaComplex_id'},
											{ name: 'UslugaComplex_Code', mapping: 'UslugaComplex_Code'},
											{ name: 'UslugaComplex_Name', mapping: 'UslugaComplex_Name'}
										]),
										url: '/?c=Common&m=loadDispUslugaComplex'
									}),
									tpl: new Ext.XTemplate(
										'<tpl for="."><div class="x-combo-list-item">',
										'<table style="border: 0;"><td><h3>{UslugaComplex_Code}</h3></td><td>&nbsp;&nbsp;{UslugaComplex_Name}</td></tr></table>',
										'</div></tpl>'
									),
									triggerAction: 'all',
									hideTrigger: false,
									valueField: 'UslugaComplex_id',
									width: 300,
									listWidth: 700,
									xtype: 'swbaselocalcombo'
								},
							{
								enableKeyEvents: true,
								fieldLabel: lang['sluchay_zakonchen'],
								hiddenName: 'EvnPLDispOrp_IsFinish',
								tabIndex: TABINDEX_EPLDO13SW + 66,
								width: 100,
								xtype: 'swyesnocombo'
							}, {
								fieldLabel: lang['obslujen_mobilnoy_brigadoy'],
								hiddenName: 'EvnPLDispOrp_isMobile',
								tabIndex: TABINDEX_EPLDO13SW + 69,
								width: 100,
								xtype: 'swyesnocombo'
							}, {
								xtype: 'swlpubuildingglobalcombo',
								hiddenName: 'Disp_LpuBuilding_id',
								width: 300
							},
							{
								xtype: 'swlpusectionglobalcombo',
								hiddenName: 'Disp_LpuSection_id',
								width: 300
							},
							{
								xtype: 'swmedstafffactglobalcombo',
								hiddenName: 'Disp_MedStaffFact_id',
								width: 300
							}]
						}, {
							border: false,
							layout: 'form',
							labelWidth: 225,
							items: [{
								fieldLabel: lang['otkaz_ot_prohozhdeniya'],
								hiddenName: 'EvnPLDispOrp_IsRefusal',
								tabIndex: TABINDEX_EPLDD13SW + 66,
								width: 100,
								xtype: 'swyesnocombo'
							}, {
								enableKeyEvents: true,
								fieldLabel: lang['napravlen_na_2_etap'],
								hiddenName: 'EvnPLDispOrp_IsTwoStage',
								tabIndex: TABINDEX_EPLDO13SW + 67,
								width: 100,
								xtype: 'swyesnocombo'
							}, {
								allowBlank: true,
								enableKeyEvents: true,
								fieldLabel: lang['gruppa_zdorovya'],
								hiddenName: 'EvnPLDispOrp_HealthKind_id',
								validateOnBlur: false,
								listeners: {
									'keydown': function(combo, e) {
										if ( !e.shiftKey && e.getKey() == e.TAB )
										{
											e.stopEvent();
											Ext.TaskMgr.start({
												run : function() {
													Ext.TaskMgr.stopAll();
													Ext.getCmp('EPLDO13SWEvnPLDispOrpSearchGrid').focus();
												},
												interval : 200
											});
										}
									}
								},
								tabIndex: TABINDEX_EPLDO13SW + 70,
								width: 100,
								xtype: 'swhealthkindcombo'
							},
                                {
                                    allowBlank: true,
                                    comboSubject: 'ChildStatusType',
                                    fieldLabel: 'Статус ребёнка',
                                    hiddenName: 'EvnPLDispOrp_ChildStatusType_id',
                                    lastQuery: '',
                                    width: 250,
                                    xtype: 'swcommonsprcombo'
                                }
                            ]
						}, {
							border: false,
							layout: 'form',
							labelWidth: 225,
							items: [{
								fieldLabel: lang['sluchay_oplachen'],
								tabIndex: TABINDEX_EPLDO13SW + 68,
								hiddenName: 'EvnPLDispOrp_isPaid',
								width: 100,
								xtype: 'swyesnocombo'
							}]
						}]
					}]
				}]
			})]
		});
	
		Ext.apply(this, 
		{
			split: true,
			items: 
			[
				this.EditForm,
				new sw.Promed.ViewFrame({
					useArchive: 1,
					actions: [
						{ name: 'action_add', handler: function() { Ext.getCmp('EvnPLDispOrp13SearchWindow').addEvnPLDD(); } },
						{ name: 'action_edit', handler: function() { Ext.getCmp('EvnPLDispOrp13SearchWindow').openEvnPLDDEditWindow('edit'); } },
						{ name: 'action_view', handler: function() { Ext.getCmp('EvnPLDispOrp13SearchWindow').openEvnPLDDEditWindow('view'); } },
						{ name: 'action_delete', handler: function() { Ext.getCmp('EvnPLDispOrp13SearchWindow').deleteEvnPLDD(); } },
						{ name: 'action_refresh', handler: function() { Ext.getCmp('EvnPLDispOrp13SearchWindow').refreshEvnPLDDList(); } },
						{ name: 'action_print', menuConfig: {
							printCost: {name: 'printCost', text: langs('Справка о стоимости лечения'), hidden: ! getRegionNick().inlist(['perm', 'kz', 'ufa']), handler: function () { win.printCost() }}
						}}
					],
					autoExpandColumn: 'autoexpand',
					autoLoadData: false,
					dataUrl: C_SEARCH,
					focusOn: {
						name: 'EPLDO13SWSearchButton', type: 'field'
					},
					id: 'EPLDO13SWEvnPLDispOrpSearchGrid',
					layout: 'fit',
					object: 'EvnPLDD',
					cls: 'DispOrpSearchGrid-custome',
					pageSize: 100,
					paging: true,
					region: 'center',
					root: 'data',
					totalProperty: 'totalCount', 
					onBeforeLoadData: function() {
						this.getButtonSearch().disable();
					}.createDelegate(this),
					onLoadData: function() {
						this.getButtonSearch().enable();
					}.createDelegate(this),
					title: '',
					toolbar: true,
					stringfields: [
						{ name: 'EvnPLDispOrp_id', type: 'int', header: 'ID', key: true },
						{ name: 'EvnPLDispOrp_IsTransit', type: 'int', hidden: true },
						{ name: 'Person_id', type: 'int', hidden: true },
						{ name: 'Server_id', type: 'int', hidden: true },
						{ name: 'UslugaComplex_Name', type: 'string', hidden: !getRegionNick().inlist([ 'buryatiya', 'krym' ]), header: langs('Услуга диспансеризации'), width: 150 },
						{ name: 'Person_Surname', id: 'autoexpand', type: 'string', header: langs('Фамилия'), width: 150 },
						{ name: 'Person_Firname', type: 'string', header: langs('Имя'), width: 150 },
						{ name: 'Person_Secname', type: 'string', header: langs('Отчество'), width: 150 },
						{ name: 'Sex_Name', type: 'string', header: langs('Пол'), width: 80 },
						{ name: 'Person_Birthday', type: 'date', format: 'd.m.Y', header: langs('Д/р') },
						{ name: 'EvnPLDispOrp_setDate', type: 'date', format: 'd.m.Y', header: langs('Дата начала') },
						{ name: 'EvnPLDispOrp_disDate', type: 'date', format: 'd.m.Y', header: langs('Дата окончания') },
						{ name: 'EvnPLDispOrp_IsFinish', type: 'string', header: langs('Закончен'), width:80 },
						{ name: 'EvnPLDispOrp_HealthKind_Name', type: 'string', header: langs('Группа здоровья') },
						{ name: 'EvnPLDispOrp_IsTwoStage', type: 'string', header: langs('Направлен на 2 этап'), width:80 },
						{ name: 'EvnCostPrint_setDT', type: 'date', header: langs('Дата выдачи справки/отказа'), width: 150, hidden: ! getRegionNick().inlist(['perm', 'kz', 'ufa']) },
						{ name: 'EvnCostPrint_IsNoPrintText', type: 'string', hidden: ! getRegionNick().inlist(['perm', 'kz', 'ufa']), header: langs('Справка о стоимости лечения'), width: 150 }
					],
					onRowSelect: function(sm, index, record) {
						if(win.viewOnly == true){
							this.getAction('action_edit').setDisabled(true);
							this.getAction('action_delete').setDisabled(true);
						}
						else
						{
							// Запретить редактирование/удаление архивных записей
							if (getGlobalOptions().archive_database_enable) {
								this.getAction('action_edit').setDisabled(record.get('archiveRecord') == 1);
								this.getAction('action_delete').setDisabled(record.get('archiveRecord') == 1);
							}

							if ( record.get('EvnPLDispOrp_id') ) {
								this.setActionDisabled('action_setevnistransit', !(record.get('EvnPLDispOrp_IsTransit') == 1));
							}
							else {
								this.setActionDisabled('action_setevnistransit', true);
							}
						}
					}
				})
			],
			buttons: [{
				handler: function() {
					this.doSearch();
				}.createDelegate(this),
				iconCls: 'search16',
				id: 'EPLDO13SWSearchButton',
				tabIndex: TABINDEX_EPLDO13SW+90,
				text: BTN_FRMSEARCH
			}, {
				handler: function() {
					this.doReset();
				}.createDelegate(this),
				iconCls: 'resetsearch16',
				tabIndex: TABINDEX_EPLDO13SW+91,
				text: BTN_FRMRESET
			}, {
				id: 'mode_button',
				handler: function() {
					if ( this.ownerCt.isStream == false )
					{
						this.ownerCt.setStreamInputMode();
					}
					else
					{
						this.ownerCt.setSearchMode();
					}
				},
				tabIndex: TABINDEX_EPLDO13SW+92,
				text: "Режим потокового ввода"
			},
			{
				text: '-'
			},
			HelpButton(this, -1),
			{
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'cancel16',
				tabIndex: TABINDEX_EPLDO13SW+94,
				text: BTN_FRMCANCEL,
				onTabAction: function() {
					Ext.getCmp('EPLDO13SWYearCombo').focus(true, 200);
				},
				onShiftTabAction: function() {
					Ext.getCmp('mode_button').focus(true, 200);
				}
			}
			]
		});
		sw.Promed.swEvnPLDispOrp13SearchWindow.superclass.initComponent.apply(this, arguments);
	},
	isStream: null, // потоковый ввод или поиск?
	keys: [{
		alt: true,
		fn: function(inp, e) {
			var frm = Ext.getCmp('EvnPLDispOrp13SearchWindow');
			var search_filter_tabbar = frm.findById('EPLDO13SWSearchFilterTabbar');

			switch (e.getKey())
			{
				case Ext.EventObject.C:
					frm.doReset();
					break;
				case Ext.EventObject.J:
					frm.hide();
					break;

				case Ext.EventObject.NUM_ONE:
				case Ext.EventObject.ONE:
					search_filter_tabbar.setActiveTab(0);
					break;

				case Ext.EventObject.NUM_TWO:
				case Ext.EventObject.TWO:
					search_filter_tabbar.setActiveTab(1);
					break;

				case Ext.EventObject.NUM_THREE:
				case Ext.EventObject.THREE:
					search_filter_tabbar.setActiveTab(2);
					break;

				case Ext.EventObject.NUM_FOUR:
				case Ext.EventObject.FOUR:
					search_filter_tabbar.setActiveTab(3);
					break;

				case Ext.EventObject.NUM_FIVE:
				case Ext.EventObject.FIVE:
					search_filter_tabbar.setActiveTab(4);
					break;

				case Ext.EventObject.NUM_SIX:
				case Ext.EventObject.SIX:
					search_filter_tabbar.setActiveTab(5);
					break;
			}
		},
		key: [
			Ext.EventObject.C,
			Ext.EventObject.FIVE,
			Ext.EventObject.FOUR,
			Ext.EventObject.J,
			Ext.EventObject.NUM_FIVE,
			Ext.EventObject.NUM_FOUR,
			Ext.EventObject.NUM_ONE,
			Ext.EventObject.NUM_SIX,
			Ext.EventObject.NUM_TWO,
			Ext.EventObject.NUM_THREE,
			Ext.EventObject.ONE,
			Ext.EventObject.SIX,
			Ext.EventObject.TWO,
			Ext.EventObject.THREE
		],
		stopEvent: true
	}, {
		fn: function(inp, e) {
			var frm = Ext.getCmp('EvnPLDispOrp13SearchWindow');
			switch (e.getKey())
			{
				case Ext.EventObject.INSERT:
					frm.addEvnPLDD();
					break;
			}
		},
		key: [
			Ext.EventObject.INSERT	
		],
		stopEvent: true
	}],
	layout: 'border',
	loadYearsCombo: function (stage) {
		var years_combo = this.findById('EPLDO13SWYearCombo');
		if ( years_combo.getStore().getCount() == 0 ) {
			years_combo.getStore().load({
				params: {
					DispClass_id: (stage == 2 ? 4 : 3)
				},
				callback: function() {
					var date = new Date();
					var year = date.getFullYear();
					years_combo.setValue(year);
					years_combo.focus(true, 500);
				}
			});
		}
		else
		{
			var date = new Date();
					var year = date.getFullYear();
					years_combo.setValue(year);
					years_combo.focus(true, 500);
		}
	},
	maximizable: true,
	minHeight: 550,
	minWidth: 800,
	modal: false,
	openEvnPLDDEditWindow: function(action) {
		var frm = this;
		var evnpldispdop_grid = frm.findById('EPLDO13SWEvnPLDispOrpSearchGrid').ViewGridPanel;

		if (getWnd((frm.stage == 2)?'swEvnPLDispOrp13SecEditWindow':'swEvnPLDispOrp13EditWindow').isVisible())
		{
			Ext.Msg.alert(lang['soobschenie'], lang['okno_redaktirovaniya_kartyi_po_dispaserizatsii_detey-sirot_uje_otkryito']);
			return false;
		}

		if (!evnpldispdop_grid.getSelectionModel().getSelected())
		{
			return false;
		}

		var record = evnpldispdop_grid.getSelectionModel().getSelected();

		var evnpldispdop_id = record.data.EvnPLDispOrp_id;
		var person_id = record.data.Person_id;
		var server_id = record.data.Server_id;

		if (evnpldispdop_id > 0 && person_id > 0 && server_id >= 0)
		{
			var params = {
				action: action,
				EvnPLDispOrp_id: evnpldispdop_id,
				onHide: Ext.emptyFn,
				callback: function() {
					frm.refreshEvnPLDDList();
				},
				Person_id: person_id,
				Server_id: server_id
			};
			if (getGlobalOptions().archive_database_enable) {
				params.archiveRecord = record.get('archiveRecord');
			}
			getWnd((frm.stage == 2)?'swEvnPLDispOrp13SecEditWindow':'swEvnPLDispOrp13EditWindow').show(params);
		}
	},
	plain: true,
	refreshEvnPLDDList: function(action) {
		var frm = this;
		var evnpldispdop_grid = frm.findById('EPLDO13SWEvnPLDispOrpSearchGrid').ViewGridPanel;
		if ( this.isStream ) {
			this.doStreamInputSearch();
			this.loadYearsCombo(this.stage);
		}
		else {
			this.doSearch();
		}
	},
	resizable: true,
	setSearchMode: function()
	{
		var button = this.buttons[2];
		this.buttons[0].enable();
		this.buttons[1].enable();
		button.setText(lang['rejim_potokovogo_vvoda']);
		this.setTitle(lang['karta_dispanserizatsii_nesovershennoletnego_-'] + (this.stage == 2?'2':'1') + lang['etap_poisk']);
		Ext.getCmp('EPLDO13_EvnPLDispOrpSearchFilterForm').setHeight(280);
		this.findById('EPLDO13_EvnPLDispOrpSearchFilterForm').show();	
		this.doLayout();		
		this.isStream = false;
		/*if ( this.findById('EPLDO13SWYearCombo').getStore().getCount() > 0 )
			this.findById('EPLDO13SWYearCombo').focus(true, 100);*/

	},
	getFilterForm: function() {
		return this.findById('EPLDO13_EvnPLDispOrpSearchFilterForm');
	},
	setStreamInputMode: function()
	{
		var button = this.buttons[2];
		this.buttons[0].disable();
		this.buttons[1].disable();		
		button.setText('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Режим поиска&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;');
		var grid = this.findById('EPLDO13SWEvnPLDispOrpSearchGrid').ViewGridPanel;
		grid.getStore().removeAll();
		this.setTitle(lang['karta_dispanserizatsii_nesovershennoletnego_-'] + (this.stage == 2?'2':'1') + lang['etap_potochnyiy_vvod']);
		this.findById('EPLDO13_EvnPLDispOrpSearchFilterForm').hide();
		Ext.getCmp('EPLDO13_EvnPLDispOrpSearchFilterForm').setHeight(25);
		this.doLayout();
		this.isStream = true;
		this.doStreamInputSearch();
		if ( this.findById('EPLDO13SWYearCombo').getStore().getCount() > 0 )
			this.findById('EPLDO13SWYearCombo').focus(true, 100);
	},
	show: function() {
		sw.Promed.swEvnPLDispOrp13SearchWindow.superclass.show.apply(this, arguments);
		
		this.stage = 1;
		if ( arguments[0] && arguments[0].stage ) {
			this.stage = arguments[0].stage;
		}

		if ( arguments[0] != undefined && arguments[0].mode != undefined )
		{
			if ( arguments[0].mode == 'stream' )
			{
				this.setSearchMode();
				this.setStreamInputMode();
			}
			else
				this.setSearchMode();
		}
		else
			this.setSearchMode();
			
		this.findById('EPLDO13SWSearchFilterTabbar').setActiveTab('EPLDO13_FirstTab');
		this.getBegDateTime();

		if ( !this.findById('EPLDO13SWEvnPLDispOrpSearchGrid').getAction('action_setevnistransit') ) {
			this.findById('EPLDO13SWEvnPLDispOrpSearchGrid').addActions({
				disabled: true,
				handler: function() {
					this.setEvnIsTransit();
				}.createDelegate(this),
				iconCls: 'actions16',
				id: this.id + 'action_setevnistransit',
				name: 'action_setevnistransit',
				text: lang['perehodnyiy_sluchay']
			});
		}

		this.findById('EPLDO13SWEvnPLDispOrpSearchGrid').setActionHidden('action_setevnistransit', !lpuIsTransit());

		var filter_form = this.findById('EPLDO13_EvnPLDispOrpSearchFilterForm').getForm();
		if (this.stage == 2) {
			// скрыть EvnPLDispOrp_IsTwoStage в фильтрах и в гриде
			filter_form.findField('EvnPLDispOrp_IsTwoStage').hideContainer();
			this.findById('EPLDO13SWEvnPLDispOrpSearchGrid').setColumnHidden('EvnPLDispOrp_IsTwoStage', true);

			filter_form.findField('UslugaComplex_id').lastQuery = 'This query sample that is not will never appear';
			filter_form.findField('UslugaComplex_id').getStore().baseParams.DispClass_idList = Ext.util.JSON.encode([4,8]);
		} else {
			filter_form.findField('EvnPLDispOrp_IsTwoStage').showContainer();
			this.findById('EPLDO13SWEvnPLDispOrpSearchGrid').setColumnHidden('EvnPLDispOrp_IsTwoStage', false);

			filter_form.findField('UslugaComplex_id').lastQuery = 'This query sample that is not will never appear';
			filter_form.findField('UslugaComplex_id').getStore().baseParams.DispClass_idList = Ext.util.JSON.encode([3,7]);
		}
		filter_form.findField('LpuRegionType_id').getStore().filterBy( //https://redmine.swan.perm.ru/issues/78988
			function(record)
			{
				//if (record.data.LpuRegionType_SysNick.inlist(['feld']) && getRegionNick() == 'perm')
				if (record.data.LpuRegionType_SysNick.inlist(['feld']) && getRegionNick().inlist(['perm','buryatiya','kareliya','khak','krym','ekb','ufa']))
					return false;
				else
					return true;
			}
		);
		this.restore();
		this.center();
		this.maximize();
		this.doReset();

		this.viewOnly = false;
		if(arguments[0])
		{
			if(arguments[0].viewOnly)
				this.viewOnly = arguments[0].viewOnly;
		}
		this.findById('EPLDO13SWEvnPLDispOrpSearchGrid').setActionDisabled('action_add', this.viewOnly);

		filter_form.findField('EvnPLDispOrp_HealthKind_id').getStore().clearFilter();
		filter_form.findField('EvnPLDispOrp_HealthKind_id').lastQuery = '';
		filter_form.findField('EvnPLDispOrp_HealthKind_id').getStore().filterBy(function(rec) {
			if(!rec.get('HealthKind_Code').inlist(['6','7']))
			{
				return true;
			}
			return false;
		});

		this.findById('EPLDO13SWYearCombo').getStore().removeAll();
		this.loadYearsCombo(this.stage);
		var UslugaCombo = filter_form.findField('EvnPLDisp_UslugaComplex');
		UslugaCombo.getStore().removeAll();
		UslugaCombo.getStore().baseParams = {
			DispClass_id: 3
		}
		UslugaCombo.getStore().load();
		if(swLpuBuildingGlobalStore.data.length == 0){
			swLpuBuildingGlobalStore.load();
		}
		if(swLpuSectionGlobalStore.data.length == 0){
			swLpuSectionGlobalStore.load();
		}
		if(swMedStaffFactGlobalStore.data.length == 0){
			swMedStaffFactGlobalStore.load();
		}
		swLpuBuildingGlobalStore.clearFilter();
		swLpuSectionGlobalStore.clearFilter();
		swMedStaffFactGlobalStore.clearFilter();
		filter_form.findField('Disp_LpuBuilding_id').getStore().loadData(getStoreRecords(swLpuBuildingGlobalStore));
		filter_form.findField('Disp_LpuSection_id').getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));
		filter_form.findField('Disp_MedStaffFact_id').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));
	},
	title: WND_POL_EPLDO13SEARCH,
	width: 800
});
