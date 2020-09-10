/**
* swMesSearchWindow - окно поиска МЭСов.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2009-2010 Swan Ltd.
* @author       Pshenicyn Ivan aka IVP (ipshon@rambler.ru)
* @version      16.02.2010
* @comment      Префикс для id компонентов MSW (MesSearchWindow)
*/

sw.Promed.swMesSearchWindow = Ext.extend(sw.Promed.BaseForm, {
	buttonAlign: 'left',
	closable: true,
	closeAction: 'hide',
	collapsible: true,
	deleteMes: function() {		
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

		var form = this.findById('MSW_MesSearchForm');

		/*if ( form.isEmpty() ) {
			sw.swMsg.alert(lang['oshibka'], lang['ne_zapolneno_ni_odno_pole'], function() {
				Ext.getCmp('PersonCardFilterTabPanel').setActiveTab(0);
				form.getForm().findField('Person_Surname').focus();
			});
			return false;
		}*/

		var grid = this.findById('MSW_MesSearchGrid').ViewGridPanel;
		//var params = form.getForm().getValues();
		var params = form.getForm().getValues();
		var arr = form.find('disabled', true);
		for (i = 0; i < arr.length; i++)
		{
			params[arr[i].hiddenName] = arr[i].getValue();
		}
		params.start = 0;
		params.limit = 100;

		//var arr = form.find('disabled', true);

		/*for ( i = 0; i < arr.length; i++ ) {
			params[arr[i].hiddenName] = arr[i].getValue();
		}*/

		grid.getStore().removeAll();
		grid.getStore().baseParams = params;
		grid.getStore().load({
			params: params,
			callback: function(r) {
				thisWindow.searchInProgress = false;
				if ( r.length > 0 ) {
					grid.getView().focusRow(0);
					grid.getSelectionModel().selectFirstRow();
				}
			}
		});
	},
	draggable: true,
	height: 550,
	id: 'MesSearchWindow',
	initComponent: function() {
		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.ownerCt.doSearch();
				},
				iconCls: 'search16',
				id: 'MSW_SearchButton',
				tabIndex: TABINDEX_MSW + 1,
				text: BTN_FRMSEARCH
			}, {
				text: '-'
			},
			HelpButton(this),
			{
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'close16',
				id: 'MSW_CancelButton',
				onTabAction: function () {
					//this.findById('ERPSIF_EvnRecept_Ser').focus(true, 100);
				}.createDelegate(this),
				tabIndex: TABINDEX_MSW + 2,
				text: lang['zakryit']
			}],
			items: [ new Ext.form.FormPanel({
				bodyStyle: 'padding: 5px',
				border: false,
				frame: false,
				height: (Ext.isIE) ? 160 : 150,
				id: 'MSW_MesSearchForm',
				items: [{
					border: false,
					layout: 'column',
					items:[{
						border: false,
						layout: 'form',
						items: [ new sw.Promed.SwBaseLocalCombo({
							displayField: 'MesStatus_Name',
							editable: false,
							fieldLabel: lang['status'],
							hiddenName: 'MesStatus_id',
							store: new Ext.data.SimpleStore(
							{
								key: 'MesStatus_id',
								autoLoad: true,
								fields:
								[
									{name:'MesStatus_id', type:'int'},
									{name:'MesStatus_Name', type:'string'}
								],
								data : [
									[1, lang['otkryityie+planiruemyie']],
									[2, lang['otkryityie']],
									[3, lang['zakryityie']],
									[4, lang['planiruemyie']]/*,
									[5, lang['udalennyie']]*/
								]
							}),
							tpl: '<tpl for="."><div class="x-combo-list-item">{MesStatus_Name}&nbsp;</div></tpl>',
							valueField: 'MesStatus_id',
							width: 180
						})]
					}, {
						border: false,
						layout: 'form',
						items: [{
							enableKeyEvents: true,
							listeners: {
								'keydown': function (inp, e) {
									if (e.shiftKey == false && e.getKey() == Ext.EventObject.TAB)
									{
										e.stopEvent();
										var form = Ext.getCmp('MSW_MesSearchForm');
										form.getForm().findField('MesAgeGroup_id').focus(true);
									}                            
								}
							},
							hiddenName: 'MesProf_id',
							width: 180,
							xtype: 'swmesprofcombo'
						}]
					}]
				}, {
					border: false,
					layout: 'column',
					items:[{
						border: false,
						layout: 'form',
						items: [{
							enableKeyEvents: true,
							listeners: {
								'keydown': function (inp, e) {
									if (e.shiftKey == true && e.getKey() == Ext.EventObject.TAB)
									{
										e.stopEvent();
										var form = Ext.getCmp('MSW_MesSearchForm');
										form.getForm().findField('MesProf_id').focus(true);
									}                            
								}
							},
							hiddenName: 'MesAgeGroup_id',
							width: 180,
							xtype: 'swmesagegroupcombo'
						}]
					}, {
						border: false,
						layout: 'form',
						items: [{
							fieldLabel: lang['kat_slojn'],
							hiddenName: 'MesLevel_id',
							width: 180,
							xtype: 'swmeslevelcombo'
						}]
					}]
				}, {
					hiddenName: 'OmsLpuUnitType_id',
					width: 305,
					xtype: 'swomslpuunittypecombo'
				}, {
					fieldLabel: lang['diagnoz'],
					hiddenName: 'Diag_id',
					width: 305,
					xtype: 'swdiagcombo'
				}, {
					border: false,
					layout: 'column',
					items:[{
						border: false,
						layout: 'form',
						items: [{
							xtype: 'numberfield',
							allowNegative: false,
							allowDecimals: false,
							maxLength: 3,
							autoCreate: {tag: "input", type: "text", size: "3", maxLength: "3", autocomplete: "off"},
							fieldLabel: lang['norm_srok_s'],
							name: 'Mes_KoikoDni_From',
							width: 180
						}]
					}, {
						border: false,
						layout: 'form',
						items: [{
							xtype: 'numberfield',
							allowNegative: false,
							allowDecimals: false,
							maxLength: 3,
							autoCreate: {tag: "input", type: "text", size: "3", maxLength: "3", autocomplete: "off"},
							fieldLabel: lang['po'],
							name: 'Mes_KoikoDni_To',
							width: 180
						}]
					}]
				}, {
					border: false,
					layout: 'column',
					items:[{
						border: false,
						layout: 'form',
						items: [{
							fieldLabel: lang['data_nachala'],
							name: 'Mes_begDT_Range',
							plugins: [
								new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)
							],
							width: 180,
							xtype: 'daterangefield'
						}]
					}, {
						border: false,
						layout: 'form',
						items: [{
							fieldLabel: lang['data_okonchaniya'],
							name: 'Mes_endDT_Range',
							plugins: [
								new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)
							],
							width: 180,
							xtype: 'daterangefield'
						}]
					}]
				}],
						/*new sw.Promed.SwBaseLocalCombo({
							displayField: 'MesStatus_Name',
							editable: false,
							fieldLabel: lang['status'],
							hiddenName: 'MesStatus_id',
							store: new Ext.data.SimpleStore(
							{
								key: 'MesStatus_id',
								autoLoad: true,
								fields:
								[
									{name:'MesStatus_id', type:'int'},
									{name:'MesStatus_Name', type:'string'}
								],
								data : [
									[1, lang['otkryityie+planiruemyie']],
									[2, lang['otkryityie']],
									[3, lang['zakryityie']],
									[4, lang['planiruemyie']],
									[5, lang['udalennyie']]
								]
							}),
							tpl: '<tpl for="."><div class="x-combo-list-item">{MesStatus_Name}&nbsp;</div></tpl>',
							valueField: 'MesStatus_id',
							width: 180
						}), {
							anchor: '95%',
							fieldLabel: lang['diagnoz'],
							hiddenName: 'Diag_id',
							width: 180,
							tabIndex: TABINDEX_MEW + 3,
							xtype: 'swdiagcombo'
						}, {
							fieldLabel : lang['data_nachala_deystviya_mes'],
							xtype: 'swdatefield',
							format: 'd.m.Y',
							plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
							tabIndex: TABINDEX_MEW + 6,
							name: 'Mes_begDT'
						}]
					}, {
						border: false,
						layout: 'form',
						items: [{
							anchor: '95%',
							enableKeyEvents: true,
							listeners: {
								'keydown': function (inp, e) {
									if (e.shiftKey == false && e.getKey() == Ext.EventObject.TAB)
									{
										e.stopEvent();
										var form = Ext.getCmp('MSW_MesSearchForm');
										form.getForm().findField('MesAgeGroup_id').focus(true);
									}                            
								}
							},
							hiddenName: 'MesProf_id',
							width: 180,
							tabIndex: TABINDEX_MEW + 16,
							xtype: 'swmesprofcombo'
						}, {
							anchor: '95%',
							hiddenName: 'OmsLpuUnitType_id',
							width: 180,
							tabIndex: TABINDEX_MEW + 4,
							xtype: 'swomslpuunittypecombo'
						}, {
							fieldLabel : lang['data_okonchaniya_deystviya_mes'],
							xtype: 'swdatefield',
							format: 'd.m.Y',
							plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
							tabIndex: TABINDEX_MEW + 7,
							name: 'Mes_endDT'
						}]
					}, {
						border: false,
						layout: 'form',
						items: [{
							anchor: '95%',
							enableKeyEvents: true,
							listeners: {
								'keydown': function (inp, e) {
									if (e.shiftKey == true && e.getKey() == Ext.EventObject.TAB)
									{
										e.stopEvent();
										var form = Ext.getCmp('MSW_MesSearchForm');
										form.getForm().findField('MesProf_id').focus(true);
									}                            
								}
							},
							hiddenName: 'MesAgeGroup_id',
							width: 180,
							tabIndex: TABINDEX_MEW + 1,
							xtype: 'swmesagegroupcombo'
						}, {
							anchor: '95%',
							hiddenName: 'MesLevel_id',
							width: 180,
							tabIndex: TABINDEX_MEW + 2,
							xtype: 'swmeslevelcombo'
						}, {
							xtype: 'numberfield',
							allowNegative: false,
							allowDecimals: false,
							anchor: '95%',
							maxLength: 3,
							autoCreate: {tag: "input", type: "text", size: "3", maxLength: "3", autocomplete: "off"},
							width: 190,
							fieldLabel: lang['normativnyiy_srok_lecheniya'],
							tabIndex: TABINDEX_MEW + 5,
							name: 'Mes_KoikoDni'
						}]
					}]
				}],*/
				labelAlign: 'right',
				labelWidth: 120,
				region: 'north'
			}),
			new sw.Promed.ViewFrame({
				actions: [
					{ name: 'action_add', handler: function() { this.openMesEditWindow('add'); }.createDelegate(this) },
					{ name: 'action_edit', handler: function() { this.openMesEditWindow('edit'); }.createDelegate(this) },
					{ name: 'action_view', handler: function() { this.openMesEditWindow('view'); }.createDelegate(this) },
					{ name: 'action_delete', handler: function() { this.deleteMes(); }.createDelegate(this) },
					{ name: 'action_refresh', handler: function() { this.refreshMesSearchGrid(); }.createDelegate(this) },
					{ name: 'action_print'/*, disabled: true, handler: function() { this.printMes(); }.createDelegate(this)*/ }
				],
				autoExpandColumn: 'autoexpand',
				autoExpandMin: 150,
				autoLoadData: false,
				dataUrl: '/?c=Mes&m=loadMesSearchList',
				focusOn: {
					name: 'MSW_CancelButton',
					type: 'button'
				},
				focusPrev: {
					name: 'MSW_CancelButton',
					type: 'button'
				},
				id: 'MSW_MesSearchGrid',
				onRowSelect: function( grd, ind ) {
					var grid = Ext.getCmp("MSW_MesSearchGrid");
					var row = grid.getGrid().getStore().getAt(ind);
					if ( !row || !row.get('Mes_id') )
						return;
					Ext.getCmp('MSW_Mes_DiagClinical').setValue(row.get('Mes_DiagClinical'));
					Ext.getCmp('MSW_Mes_DiagVolume').setValue(row.get('Mes_DiagVolume'));
					Ext.getCmp('MSW_Mes_Consulting').setValue(row.get('Mes_Consulting'));
					Ext.getCmp('MSW_Mes_CureVolume').setValue(row.get('Mes_CureVolume'));
					Ext.getCmp('MSW_Mes_QualityMeasure').setValue(row.get('Mes_QualityMeasure'));
					Ext.getCmp('MSW_Mes_ResultClass').setValue(row.get('Mes_ResultClass'));
					Ext.getCmp('MSW_Mes_ComplRisk').setValue(row.get('Mes_ComplRisk'));
					// запрет или разрешение удаления для планируемых
					Ext.getCmp("MSW_MesSearchGrid").getAction('action_delete').setDisabled(row.get('MesStatus')!=4);
				},
				object: 'Mes',
				pageSize: 100,
				paging: true,
				region: 'center',
				root: 'data',
				stringfields: [
					{ name: 'Mes_id', type: 'int', header: 'ID', key: true },
					{ name: 'Mes_Code', type: 'string', header: lang['kod'] + getMESAlias(), id: 'autoexpand', width: 170 },
					{ name: 'MesProf_CodeName', type: 'string', header: lang['spetsialnost'], width: 170 },
					{ name: 'MesAgeGroup_CodeName', type: 'string', header: lang['vozrastnaya_gruppa'], width: 170 },
					{ name: 'OmsLpuUnitType_CodeName', type: 'string', header: lang['tip_statsionara'], width: 170 },
					{ name: 'MesLevel_CodeName', type: 'string', header: lang['kategoriya_slojnosti'], width: 170 },
					{ name: 'Diag_CodeName', type: 'string', header: lang['diagnoz'], width: 170 },
					{ name: 'Mes_KoikoDni', type: 'string', header: lang['normativnyiy_srok'], width: 170 },
					{ name: 'Mes_begDT', type: 'date', header: lang['data_nachala'], width: 100 },
					{ name: 'Mes_endDT', type: 'date', header: lang['data_okonchaniya'], width: 100 },
					{ name: 'Mes_DiagClinical', type: 'string', hidden: true },
					{ name: 'Mes_DiagVolume', type: 'string', hidden: true },
					{ name: 'Mes_Consulting', type: 'string', hidden: true },
					{ name: 'Mes_CureVolume', type: 'string', hidden: true },
					{ name: 'Mes_QualityMeasure', type: 'string', hidden: true },
					{ name: 'Mes_ResultClass', type: 'string', hidden: true },
					{ name: 'Mes_ComplRisk', type: 'string', hidden: true },
					{ name: 'MesStatus', type: 'int', hidden: true }
				],
				toolbar: true,
				totalProperty: 'totalCount'
			}), new Ext.Panel({
				autoScroll: true,
				region: 'south',
				frame: true,
				height: 120,
				split: true,
				id: 'MSW_DetailPanel',
				items: [{
					anchor: '95%',
					readOnly: true,
					fieldLabel : lang['klinicheskiy_diagnoz'],
					id: 'MSW_Mes_DiagClinical',
					xtype: 'textarea',
					autoCreate: {tag: "textarea", autocomplete: "off"}
				}, {
					anchor: '95%',
					readOnly: true,
					fieldLabel : lang['obyem_diagnostiki'],
					id: 'MSW_Mes_DiagVolume',
					xtype: 'textarea',
					autoCreate: {tag: "textarea", autocomplete: "off"}
				}, {
					anchor: '95%',
					readOnly: true,
					fieldLabel : lang['konsultatsii'],
					id: 'MSW_Mes_Consulting',
					xtype: 'textarea',
					autoCreate: {tag: "textarea", autocomplete: "off"}
				}, {
					anchor: '95%',
					readOnly: true,
					fieldLabel : lang['obyem_lecheniya'],
					id: 'MSW_Mes_CureVolume',
					xtype: 'textarea',
					autoCreate: {tag: "textarea", autocomplete: "off"}
				}, {
					anchor: '95%',
					readOnly: true,
					fieldLabel : lang['kriterii_kachestva'],
					id: 'MSW_Mes_QualityMeasure',
					xtype: 'textarea',
					autoCreate: {tag: "textarea", autocomplete: "off"}
				}, {
					anchor: '95%',
					readOnly: true,
					fieldLabel : lang['ishod_zabolevaniya'],
					id: 'MSW_Mes_ResultClass',
					xtype: 'textarea',
					autoCreate: {tag: "textarea", autocomplete: "off"}
				}, {
					anchor: '95%',
					readOnly: true,
					fieldLabel : lang['risk_oslojneniy'],
					id: 'MSW_Mes_ComplRisk',
					xtype: 'textarea',
					autoCreate: {tag: "textarea", autocomplete: "off"}
				}],
				labelWidth: 160,
				layout: 'form'
			})]
		});

		sw.Promed.swMesSearchWindow.superclass.initComponent.apply(this, arguments);
		this.findById('MSW_MesSearchGrid').getGrid().view = new Ext.grid.GridView(
		{
			getRowClass : function (row, index)
			{
				var cls = '';
				//if (row.get('set')>0)
					//cls = cls+'x-grid-rowselect ';
				if (row.get('MesStatus')==4)
					cls = cls+'x-grid-rowblue ';
				if (row.get('MesStatus')==3)
					cls = cls+'x-grid-rowgray ';
			
				if (cls.length == 0)
					cls = 'x-grid-panel'; 
				return cls;
			}
		});
		this.findById('MSW_MesSearchGrid').addListenersFocusOnFields();
	},
	keys: [{
		fn: function(inp, e) {
			Ext.getCmp('MesSearchWindow').openMesEditWindow('add');
		},
		key: [
			Ext.EventObject.INSERT
		],
		stopEvent: true
	}, {
		alt: true,
		fn: function(inp, e) {
			Ext.getCmp('MesSearchWindow').doSearch();
		},
		key: [
			Ext.EventObject.ENTER,
			Ext.EventObject.G
		],
		stopEvent: true
	}, {
		fn: function(inp, e) {
			Ext.getCmp('MesSearchWindow').doSearch();
		},
		key: [
			Ext.EventObject.ENTER
		],
		stopEvent: true
	}, {
		alt: true,
		fn: function(inp, e) {
			Ext.getCmp('MesSearchWindow').hide();
		},
		key: [
			Ext.EventObject.P
		],
		stopEvent: true
	}],
	layout: 'border',
	maximizable: true,
	minHeight: 550,
	minWidth: 800,
	modal: true,
	openMesEditWindow: function(action) {
	
		var current_window = this;
		if ( action != 'add' && action != 'edit' && action != 'view' ) {
			return false;
		}

		if ( action == 'add' && getWnd('swMesEditWindow').isVisible() ) {
			sw.swMsg.alert(lang['soobschenie'], lang['okno_redaktirovaniya'] + getMESAlias() + lang['uje_otkryito']);
			return false;
		}

		var grid = this.findById('MSW_MesSearchGrid').getGrid();

		if ( !grid ) {
			sw.swMsg.alert(lang['oshibka'], lang['ne_udalos_nayti_spisok'] + getMESAlias());
			return false;
		}
		
		var params = new Object();
		
		if ( action != 'add' )
		{
			var current_row = grid.getSelectionModel().getSelected();

			if ( !current_row ) {
				return;
			}
			
			$mes_id = current_row.get('Mes_id');
			if ( $mes_id > 0 )
				params.Mes_id = $mes_id;
			else
				return false;
		}

		params.action = action;
		params.callback = function(data) {
			if ( !data ) {
				return false;
			}

			var record = grid.getStore().getById(data.Mes_id);

			if ( !record ) {
				if ( grid.getStore().getCount() == 1 && !grid.getStore().getAt(0).get('Mes_id') ) {
					grid.getStore().removeAll();
				}

				//grid.getStore().loadData({ 'data': [ data ]}, true);
				grid.getStore().reload();
			}
			else {
				var mes_fields = new Array();

				grid.getStore().fields.eachKey(function(key, item) {
					mes_fields.push(key);
				});

				for ( i = 0; i < mes_fields.length; i++ ) {
					record.set(mes_fields[i], data[mes_fields[i]]);
				}

				record.commit();
				
				var selected_record = grid.getSelectionModel().getSelected();
				if ( selected_record )
				{
					grid.getView().focusRow(grid.getStore().indexOf(selected_record));
				}
				else
				{
					grid.getSelectionModel().selectFirstRow();
					grid.getView().focusRow(0);
				}
				
				/*if ( grid.getStore().getCount() == 1 && !grid.getStore().getAt(0).get('Mes_id') ) {
					grid.getStore().removeAll();
				}*/

				//grid.getStore().loadData({ 'data': [ data ]}, true);
				//grid.getStore().reload();
			}
		}.createDelegate(this);
		
		params.onHide = function() {
			if ( grid.getStore().getCount() > 0 )
			{
				var selected_record = grid.getSelectionModel().getSelected();
				if ( selected_record )
				{
					grid.getView().focusRow(grid.getStore().indexOf(selected_record));
				}
				else
				{
					grid.getSelectionModel().selectFirstRow();
					grid.getView().focusRow(0);
				}
			}
		}

		getWnd('swMesEditWindow').show(params);
	},
	plain: true,
	pmUser_Name: null,
	printMes: function() {

	},
	refreshMesSearchGrid: function() {
		var grid = this.findById('MSW_MesSearchGrid').getGrid();

		grid.getSelectionModel().clearSelections();
		grid.getStore().reload();

		if ( grid.getStore().getCount() > 0 ) {
			grid.getView().focusRow(0);
			grid.getSelectionModel().selectFirstRow();
		}
	},
	resizable: false,
	show: function() {
		sw.Promed.swMesSearchWindow.superclass.show.apply(this, arguments);

		this.restore();
		this.center();
		this.maximize();

		var form = this.findById('MSW_MesSearchForm');
		form.getForm().reset();

        if (arguments[0].action && arguments[0].action == 'view') {
            this.findById('MSW_MesSearchGrid').setReadOnly(true);
        }

		this.findById('MSW_MesSearchGrid').getGrid().getStore().removeAll();
		this.findById('MSW_MesSearchGrid').addEmptyRecord(this.findById('MSW_MesSearchGrid').getGrid().getStore());
		form.getForm().findField('MesStatus_id').focus(true, 200);
		if ( !this.isActionDetOpenAdded )
		{
			this.findById('MSW_MesSearchGrid').addActions({
				name: 'action_det_open',
				text: lang['skryit_panel_detaley'],
				iconCls: 'actions16',
				handler: function() {
					if ( Ext.getCmp('MesSearchWindow').isPenelDetHidden )
					{
						Ext.getCmp('MSW_DetailPanel').setHeight(120);
						Ext.getCmp('MSW_DetailPanel').show();
						Ext.getCmp('MesSearchWindow').doLayout();
						Ext.getCmp('MesSearchWindow').isPenelDetHidden = false;
						Ext.getCmp('MSW_MesSearchGrid').getAction('action_det_open').setText(lang['skryit_panel_detaley']);
					}
					else
					{
						Ext.getCmp('MSW_DetailPanel').setHeight(1);
						Ext.getCmp('MSW_DetailPanel').hide();
						Ext.getCmp('MesSearchWindow').doLayout();
						Ext.getCmp('MesSearchWindow').isPenelDetHidden = true;
						Ext.getCmp('MSW_MesSearchGrid').getAction('action_det_open').setText(lang['otkryit_panel_detaley']);
					}
				}
			});
			this.findById('MSW_MesSearchGrid').addActions({
				name: 'action_dbf',
				text: lang['eksport_v_dbf'],
				handler: function() {
					//var id_salt = Math.random();
					//var win_id = 'dbf_export' + Math.floor(id_salt * 10000);
					//var win = window.open('/?c=Mes&m=exportMesToDbf', win_id);
					var loadMask = new Ext.LoadMask(Ext.getCmp('MesSearchWindow').getEl(), { msg: "Подождите, идет формирование архива..." });
					loadMask.show();
					var form = Ext.getCmp('MSW_MesSearchForm');
					var params = form.getForm().getValues();
					Ext.Ajax.request({
						params: params,
						callback: function(options, success, response) {
							loadMask.hide();
							if ( success ) {
								var response_obj = Ext.util.JSON.decode(response.responseText);

								if ( response_obj.success ) {
									sw.swMsg.alert('Экспорт ' + getMESAlias(), '<a target="_blank" href="' + response_obj.url + '">Скачать архив с ' + getMESAlias() + '</a>');
								}
								else {
									sw.swMsg.alert(lang['eksport'] + getMESAlias(), lang['pri_formirovanii_arhiva_proizoshli_oshibki']);
								}
							}
							else {
								sw.swMsg.alert(lang['oshibka'], lang['pri_formirovanii_arhiva_proizoshli_oshibki']);
							}
						},
						url: '/?c=Mes&m=exportMesToDbf'
					});
				}
			});
			Ext.getCmp('MSW_DetailPanel').setHeight(120);
			Ext.getCmp('MSW_DetailPanel').show();
			this.doLayout();
			this.isActionDetOpenAdded = true;
			this.isPenelDetHidden = false;
		}
		//this.doSearch();
	},
	title: getMESAlias() + lang['_prosmotr'],
	width: 800
});