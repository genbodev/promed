/**
* swDrugRMZViewWindow - окно просмотра справочника ЛП Росздравназдора
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2015 Swan Ltd.
* @author       Salakhov R.
* @version      03.2015
* @comment
*/
sw.Promed.swDrugRMZViewWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: false,
	title: lang['spravochnik_lp_roszdravnadzora'],
	layout: 'border',
	id: 'DrugRMZViewWindow',
	modal: true,
	shim: false,
	width: 400,
	resizable: false,
	maximizable: true,
	maximized: true,
	doSearch: function() {
		var wnd = this;
		var form = wnd.FilterPanel.getForm();
		var params = new Object();

		params = form.getValues();
		params.no_rls = form.findField('no_rls').checked ? 1 : 0;
		params.start = 0;
		params.limit = 100;

		wnd.SearchGrid.loadData({params: params, globalFilters: params});
	},
	doReset: function() {
		var wnd = this;
		var form = wnd.FilterPanel.getForm();
		form.reset();
		wnd.SearchGrid.removeAll({clearAll: true});
		wnd.SearchGrid.getGrid().getStore().removeAll();
	},
	doImport: function() {
		var wnd = this;
		var params = new Object();

		params.title = lang['obnovlenie_spravochnika_lp_roszdravnazdora'];
		params.format_message = lang['dlya_obnovleniya_ukajite_fayl_formata_csv'];
		params.import_btn_text = lang['vyipolnit_obnovlenie'];
		params.upload_url = '/?c=DrugNomen&m=importDrugRMZFromCsv';
		params.max_file_size = 30;
		params.callback = function(data) {
			wnd.setDrugRMZInformation();
			Ext.Msg.alert(lang['obnovlenie'], lang['obnovlenie_spravochnika_uspeshno_zaversheno']);
		};

		getWnd('swCustomImportWindow').show(params);
	},
	doLink: function() {
		getWnd('swDrugRMZLinkEditWindow').show();
	},
	doExport: function() {
		getWnd('swDrugRMZExportWindow').show();
	},
	setDrugRMZInformation: function() {
		var wnd = this;
		Ext.Ajax.request({
			callback: function(opt, success, resp) {
				var response_obj = Ext.util.JSON.decode(resp.responseText);
				if (response_obj && response_obj[0]) {
					wnd.InformationPanel.setData('update_date', response_obj[0].LastUpdate_Date);
					wnd.InformationPanel.setData('record_count', response_obj[0].Record_Count);
					wnd.InformationPanel.showData();
				}
			},
			url: '/?c=DrugNomen&m=getDrugRMZInformation'
		});
	},
	show: function() {
        var wnd = this;
		sw.Promed.swDrugRMZViewWindow.superclass.show.apply(this, arguments);

		wnd.action = 'edit';
		if(arguments[0] && arguments[0].action)
			wnd.action = arguments[0].action;
		if(!wnd.SearchGrid.getAction('action_drv_actions')) {
			wnd.SearchGrid.addActions({
				name:'action_drv_actions',
				text:lang['deystviya'],
				menu: [{
					name: 'refresh_spr',
					iconCls: 'refresh16',
					text: lang['obnovit_spravochnik_rzn'],
					handler: wnd.doImport.createDelegate(wnd)
				}, {
					name: 'link_spr',
					iconCls: 'edit16',
					disabled: (wnd.action == 'view'),
					text: lang['svyazat_spravochniki_medikamentov'],
					handler: wnd.doLink.createDelegate(wnd)
				}, {
					name: 'export_spr',
					iconCls: 'add16',
					text: 'Экспорт «ОНЛС, ВЗН: остатки»',
					handler: wnd.doExport.createDelegate(wnd)
				}],
				iconCls: 'actions16'
			});
		}

		this.doReset();
		this.setDrugRMZInformation();
	},
	initComponent: function() {
		var wnd = this;

		this.FilterCommonPanel = new sw.Promed.Panel({
			layout: 'form',
			autoScroll: true,
			bodyBorder: false,
			labelAlign: 'right',
			labelWidth: 140,
			border: false,
			frame: true,
			items: [{
				xtype: 'checkbox',
				fieldLabel: lang['ne_svyazanyi_s_rls'],
				name: 'no_rls'
			}, {
				xtype: 'textfield',
				fieldLabel: lang['mnn'],
				name: 'DrugRMZ_MNN'
			}, {
				xtype: 'textfield',
				fieldLabel: lang['torg_naim'],
				name: 'DrugRMZ_Name'
			}, {
				xtype: 'textfield',
				fieldLabel: lang['forma_vyipuska'],
				name: 'DrugRMZ_Form'
			}, {
				xtype: 'textfield',
				fieldLabel: lang['dozirovka'],
				name: 'DrugRMZ_Dose'
			}, {
				xtype: 'textfield',
				fieldLabel: lang['fasovka'],
				name: 'DrugRMZ_PackSize'
			}, {
				xtype: 'textfield',
				fieldLabel: lang['ru'],
				name: 'DrugRMZ_RegNum'
			}, {
				xtype: 'textfield',
				fieldLabel: lang['proizvoditel'],
				name: 'DrugRMZ_Firm'
			}]
		});

		this.FilterButtonsPanel = new sw.Promed.Panel({
			autoScroll: true,
			bodyBorder: false,
			border: false,
			frame: true,
			items: [{
				layout: 'column',
				items: [{
					layout:'form',
					items: [{
						style: "padding-left: 10px",
						xtype: 'button',
						text: lang['poisk'],
						iconCls: 'search16',
						minWidth: 100,
						handler: function() {
							wnd.doSearch();
						}
					}]
				}, {
					layout:'form',
					items: [{
						style: "padding-left: 10px",
						xtype: 'button',
						text: lang['ochistit'],
						iconCls: 'reset16',
						minWidth: 100,
						handler: function() {
							wnd.doReset();
						}
					}]
				}]
			}]
		});

		this.FilterPanel = getBaseFiltersFrame({
			region: 'north',
			defaults: {bodyStyle:'background:#DFE8F6;width:100%;'},
			ownerWindow: wnd,
			toolBar: this.WindowToolbar,
			items: [
				this.FilterCommonPanel,
				this.FilterButtonsPanel
			]
		});

		this.InformationPanel = new Ext.Panel({
			bodyStyle: 'padding: 0px',
			border: false,
			region: 'south',
			autoHeight: true,
			frame: true,
			labelAlign: 'right',
			title: null,
			collapsible: true,
			data: null,
			html_tpl: null,
			win: wnd,
			setTpl: function(tpl) {
				this.html_tpl = tpl;
			},
			setData: function(name, value) {
				if (!this.data)
					this.data = new Ext.util.MixedCollection();
				if (name && value) {
					var idx = this.data.findIndex('name', name);
					if (idx >= 0) {
						this.data.itemAt(idx).value = value;
					} else {
						this.data.add({
							name: name,
							value: value
						});
					}
				}
			},
			showData: function() {
				var html = this.html_tpl;
				if (this.data)
					this.data.each(function(item) {
						html = html.replace('{'+item.name+'}', item.value, 'gi');
					});
				html = html.replace(/{[a-zA-Z_0-9]+}/g, '');
				this.body.update(html);
				if (this.win) {
					this.win.syncSize();
					this.win.doLayout();
				}
			},
			clearData: function() {
				this.data = null;
			}
		});
		this.InformationPanel.setTpl("Дата обновления {update_date}, кол-во записей – {record_count}");

		this.SearchGrid = new sw.Promed.ViewFrame({
			actions: [
				{name: 'action_add', disabled: true, hidden: true},
				{name: 'action_edit', disabled: true, hidden: true},
				{name: 'action_view', disabled: true, hidden: true},
				{name: 'action_delete', disabled: true, hidden: true},
				{name: 'action_print'}
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 125,
			autoLoadData: false,
			border: true,
			dataUrl: '/?c=DrugNomen&m=loadDrugRMZList',
			height: 180,
			object: 'DrugRMZ',
			editformclassname: null,
			id: 'DrugRMZGrid',
			paging: true,
			pageSize: 100,
			root: 'data',
			totalProperty: 'totalCount',
			style: 'margin-bottom: 10px',
			stringfields: [
				{ name: 'DrugRMZ_id', type: 'int', header: 'ID', key: true },
				{ name: 'DrugRPN_id', type: 'string', header: lang['kod_rzn'], width: 100 },
				{ name: 'DrugRMZ_EAN13Code', type: 'string', header: lang['kod_ean'], width: 100 },
				{ name: 'DrugRMZ_CodeRZN', type: 'string', header: lang['kod_upakovki'], width: 100 },
				{ name: 'DrugRMZ_RegNum', type: 'string', header: lang['nomer_ru'], width: 100 },
				{ name: 'DrugRMZ_RegDate', type: 'string', header: lang['data_reg'], width: 100 },
				{ name: 'DrugRMZ_MNN', type: 'string', header: lang['mnn'], width: 100 },
				{ name: 'DrugRMZ_Name', type: 'string', header: lang['torgovoe_naimenovanie'], width: 100, id: 'autoexpand' },
				{ name: 'DrugRMZ_Form', type: 'string', header: lang['forma_vyipuska'], width: 100 },
				{ name: 'DrugRMZ_Dose', type: 'string', header: lang['dozirovka'], width: 100 },
				{ name: 'DrugRMZ_PackSize', type: 'string', header: lang['fasovka'], width: 100 },
				{ name: 'DrugRMZ_Pack', type: 'string', header: lang['upakovka'], width: 100 },
				{ name: 'DrugRMZ_Firm', type: 'string', header: lang['proizvoditel'], width: 100 },
				{ name: 'DrugRMZ_Country', type: 'string', header: lang['strana'], width: 100 },
				{ name: 'DrugRMZ_FirmPack', type: 'string', header: lang['upakovschik'], width: 100 },
				{ name: 'DrugRMZ_CountryPack', type: 'string', header: lang['strana_upakovki'], width: 100 },
				{ name: 'DrugRMZ_GodnDate', type: 'string', header: lang['srok_godnosti'], width: 100 },
				{ name: 'DrugRMZ_GodnDateDay', type: 'string', header: lang['srok_godnosti_v_dnyah'], width: 100 }
		],
			title: null,
			toolbar: true
		});

		Ext.apply(this, {
			layout: 'border',
			buttons:
			[{
				text: '-'
			},
			HelpButton(this, 0),
			{
				handler: function() 
				{
					this.ownerCt.hide();
				},
				iconCls: 'cancel16',
				text: BTN_FRMCANCEL
			}],
			items:[
				wnd.FilterPanel,
				{
					border: false,
					region: 'center',
					layout: 'border',
					items:[{
						border: false,
						region: 'center',
						layout: 'fit',
						items: [this.SearchGrid]
					}]
				},
				wnd.InformationPanel
			]
		});
		sw.Promed.swDrugRMZViewWindow.superclass.initComponent.apply(this, arguments);
	}	
});