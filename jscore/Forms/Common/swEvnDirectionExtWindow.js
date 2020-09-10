/**
 * swEvnDirectionExtWindow - окно просмотра внешних направления
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2009-2014 Swan Ltd.
 * @author       Dmitry Vlasenko
 * @version      11.2014
 *
 */

sw.Promed.swEvnDirectionExtWindow = Ext.extend(sw.Promed.BaseForm, {
	buttonAlign: 'right',
	modal: true,
	layout: 'border',
	maximized: true,
	resizable: false,
	closable: true,
	shim: false,
	width: 500,
	closeAction: 'hide',
	id: 'swEvnDirectionExtWindow',
	objectName: 'swEvnDirectionExtWindow',
	title: lang['vneshnie_napravleniya'],
	plain: true,
	buttons: [
		'-',
		{
			text: BTN_FRMHELP,
			iconCls: 'help16',
			handler: function(button, event)
			{
				ShowHelp(this.ownerCt.title);
			}
		}, {
			text      : lang['zakryit'],
			tabIndex  : -1,
			tooltip   : lang['zakryit'],
			iconCls   : 'cancel16',
			handler   : function()
			{
				this.ownerCt.hide();
			}
		}
	],
	show: function()
	{
		sw.Promed.swEvnDirectionExtWindow.superclass.show.apply(this, arguments);

		var win = this;
		var base_form = win.filtersPanel.getForm();

		base_form.reset();
		this.doSearch();
	},
	doSearch: function(clear) {
		var base_form = this.filtersPanel.getForm();

		if (clear) {
			base_form.reset();
		}

		var params = base_form.getValues();

		params.start = 0;
		params.limit = 100;

		this.GridPanel.getGrid().getStore().load({
			params: params
		});
	},
	identEvnDirectionExt: function() {
		var grid = this.GridPanel.getGrid();
		var record = grid.getSelectionModel().getSelected();
		if (record && record.get('EvnDirectionExt_id')) {
			getWnd('swPersonSearchWindow').show({
				onClose: function () {
					// ничего не делаем
				},
				onSelect: function (person_data) {
					getWnd('swPersonSearchWindow').hide();

					var params = {};
					params.Person_id = person_data.Person_id;
					params.EvnDirectionExt_id = record.get('EvnDirectionExt_id');

					Ext.Ajax.request({
						callback: function(options, success, response) {
							// обновляем грид
							grid.getStore().reload();
						},
						params: params,
						url: '/?c=EvnDirectionExt&m=identEvnDirectionExt'
					});
				},
				searchMode: 'all'
			});
		}
	},
	initComponent: function()
	{
		var win = this;

		this.GridPanel = new sw.Promed.ViewFrame({
			actions: [
				{ name: 'action_add', disabled: true, hidden: true },
				{ name: 'action_edit', text: lang['identifitsirovat_smenit_patsienta'], handler: function() { win.identEvnDirectionExt(); } },
				{ name: 'action_view', disabled: true, hidden: true },
				{ name: 'action_delete', disabled: true, hidden: true },
				{ name: 'action_print', disabled: true }
			],
			uniqueId: true,
			autoLoadData: false,
			dataUrl: '/?c=EvnDirectionExt&m=loadList',
			pageSize: 100,
			paging: true,
			root: 'data',
			totalProperty: 'totalCount',
			region: 'center',
			stringfields: [
				{ name: 'EvnDirectionExt_id', type: 'int', header: 'ID', key: true },
				{ name: 'Lpu_Nick', type: 'string', header: lang['napravivshaya_mo'], width: 150 },
				{ name: 'Person_SurName', type: 'string', header: lang['familiya'], width: 150, id: 'autoexpand' },
				{ name: 'Person_FirName', type: 'string', header: lang['imya'], width: 150 },
				{ name: 'Person_SecName', type: 'string', header: lang['otchestvo'], width: 150 },
				{ name: 'Person_BirthDay', type: 'date', header: lang['data_rojdeniya'], width: 150 },
				{ name: 'Sex_Name', type: 'string', header: lang['pol'], width: 150 },
				{ name: 'Polis_Ser', type: 'string', header: lang['seriya_polisa'], width: 150 },
				{ name: 'Polis_Num', type: 'string', header: lang['nomer_polisa'], width: 150 },
				{ name: 'LpuSectionProfile_Name', type: 'string', header: lang['profil'], width: 150 },
				{ name: 'EvnDirectionExt_NPRID', type: 'string', header: lang['nomer_napravleniya'], width: 150 },
				{ name: 'PrehospType_Name', type: 'string', header: lang['tip_napravleniya'], width: 150 },
				{ name: 'Diag_Name', type: 'string', header: lang['diagnoz'], width: 150 },
				{ name: 'EvnDirectionExt_setDT', type: 'date', header: lang['data_napravleniya'], width: 150 },
				{ name: 'Person_id', hidden: true },
				{ name: 'EvnDirectionExt_IsIdent', renderer: function(v, p, row) {
					var output = "";
					if (!Ext.isEmpty(v) && v == 2) {
						output = "<a href='#' onClick='getWnd(\"swPersonEditWindow\").show({ action: \"view\", Person_id: \"" + row.get('Person_id') + "\"});'>v</a>";
					}
					return output;
				}, header: lang['identifitsirovano'], width: 150 },
			],
			toolbar: true
		});

		this.GridPanel.getGrid().view = new Ext.grid.GridView({
			getRowClass : function (row, index)
			{
				var cls = '';

				if (row.get('RiskType_id') == 2) {
					cls = cls + 'x-grid-rowblue ';
				} else if (row.get('RiskType_id') > 2) {
					cls = cls + 'x-grid-rowred ';
				}

				return cls;
			},
			listeners:
			{
				rowupdated: function(view, first, record)
				{
					view.getRowClass(record);
				}
			}
		});

		this.filtersPanel = new Ext.form.FormPanel({
			autoHeight: true,
			region: 'north',
			layout: 'form',
			border: true,
			labelAlign: 'right',
			items: [{
				listeners: {
					collapse: function(p) {
						win.doLayout();
					},
					expand: function(p) {
						win.doLayout();
					}
				},
				frame: true,
				title: lang['najmite_na_zagolovok_chtobyi_svernut_razvernut_panel_filtrov'],
				titleCollapse: true,
				collapsible: true,
				animCollapse: false,
				floatable: false,
				autoHeight: true,
				labelWidth: 120,
				layout: 'form',
				border: false,
				defaults:{bodyStyle:'background:#DFE8F6;'},
				items: [{
					layout: 'column',
					items: [{
						layout: 'form',
						columnWidth: 0.3,
						items: [{
							xtype: 'textfield',
							anchor: '100%',
							name: 'Person_SurName',
							fieldLabel: lang['familiya']
						}, {
							xtype: 'textfield',
							anchor: '100%',
							name: 'Person_FirName',
							fieldLabel: lang['imya']
						}, {
							xtype: 'textfield',
							anchor: '100%',
							name: 'Person_SecName',
							fieldLabel: lang['otchestvo']
						}, {
							xtype: 'swdatefield',
							format:'d.m.Y',
							plugins:[ new Ext.ux.InputTextMask('99.99.9999', false) ],
							name: 'Person_BirthDay',
							fieldLabel: lang['data_rojdeniya']
						}]
					}, {
						layout: 'form',
						labelWidth: 180,
						columnWidth: 0.3,
						items: [{
							fieldLabel: lang['napravivshaya_mo'],
							anchor: '100%',
							loadParams: {params: {where: ' where Lpu_EndDate is null'}},
							hiddenName: 'NaprLpu_id',
							xtype: 'swlpusearchcombo'
						}, {
							layout: 'column',
							items: [{
								layout: 'form',
								items: [{
									xtype: 'swdatefield',
									format:'d.m.Y',
									plugins:[ new Ext.ux.InputTextMask('99.99.9999', false) ],
									name: 'EvnDirectionExt_setDT_From',
									fieldLabel: lang['data_napravleniya_ot']
								}]
							}, {
								layout: 'form',
								labelWidth: 25,
								items: [{
									xtype: 'swdatefield',
									format:'d.m.Y',
									plugins:[ new Ext.ux.InputTextMask('99.99.9999', false) ],
									name: 'EvnDirectionExt_setDT_To',
									fieldLabel: lang['do']
								}]
							}]
						},	{
							fieldLabel: lang['identifitsirovano'],
							hiddenName: 'EvnDirectionExt_IsIdent',
							xtype: 'swyesnocombo'
						}, {
							layout: 'column',
							items: [{
								layout: 'form',
								style: 'margin-left: 100px;',
								items: [{
									xtype: 'button',
									text: BTN_FRMSEARCH,
									handler: function(){
										win.doSearch();
									},
									iconCls: 'search16'
								}]
							}, {
								layout: 'form',
								style: 'margin-left: 10px;',
								items: [{
									xtype: 'button',
									text: lang['sbros'],
									handler: function(){
										win.doSearch(true);
									},
									iconCls: 'resetsearch16'
								}]
							}]
						}]
					}]
				}]
			}],
			keys: [{
				fn: function() {
					win.doSearch();
				},
				key: Ext.EventObject.ENTER,
				stopEvent: true
			}]
		});

		Ext.apply(this,
		{
			defaults:
			{
				border: false
			},
			items: [this.filtersPanel, this.GridPanel]
		});
		sw.Promed.swEvnDirectionExtWindow.superclass.initComponent.apply(this, arguments);
	}
});