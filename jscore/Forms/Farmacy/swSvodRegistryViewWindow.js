/**
 * swSvodRegistryViewWindow - окно просмотра сводных реестров рецептов
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Farmacy
 * @access       public
 * @copyright    Copyright (c) 2012 Swan Ltd.
 * @author       Salakhov R.
 * @version      06.2013
 * @comment
 */
sw.Promed.swSvodRegistryViewWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: false,
	title: lang['svodnyie_reestryi_retseptov_spisok'],
	layout: 'border',
	id: 'SvodRegistryViewWindow',
	modal: true,
	shim: false,
	width: 400,
	resizable: false,
	maximizable: false,
	maximized: true,
	listeners: {
		hide: function() {
			this.onHide();
		}
	},
	onHide: Ext.emptyFn,
	setOrg: function(combo, data) { //вспомогательная функция для комбобоксов для выбора организации
		if (data && data['Org_id'] > 0) {
			combo.setValue(data['Org_id']);
			combo.setRawValue(data['Org_Name']);
		}
	},
	doSearch: function() {
		var filters = this.form.getValues();
		filters.start = 0;
		filters.limit = 100;
		this.RegistryGrid.loadData({ globalFilters: filters });
	},
	doResetSearch: function() {
		this.form.reset();
	},
	show: function() {
		var wnd = this;
		sw.Promed.swSvodRegistryViewWindow.superclass.show.apply(this, arguments);

		this.RegistryGrid.addActions({
			name:'action_fin_edit',
			hidden: false,
			text:lang['redaktirovat_dannyie_po_oplate'],
			iconCls: 'edit16',
			handler: function() {
				var record = wnd.RegistryGrid.getGrid().getSelectionModel().getSelected();
				if (record && record.get('Registry_id') > 0) {
					if (['2','3','4'].indexOf(record.get('RegistryStatus_Code')) == -1) { //Редактирование платежной информации доступно только для реестров со статусами "К оплате", "В работе", "Оплаченные"
						Ext.Msg.alert(lang['oshibka'], lang['status_reestra_ne_pozvolyaet_redaktirovat_platejnuyu_informatsiyu']);
						return false;
					}

					getWnd('swFinDocumentEditWindow').show({
						Registry_id: record.get('Registry_id'),
						FinDocument_id: record.get('FinDocument_id'),
						callback: function(owner, params) {
							if (params.RefreshGrid) {
								wnd.RegistryGrid.refreshRecords(null,0);
							} else {
								record.set('FinDocument_id', id);
								record.set('FinDocument_id', params.FinDocument_id);
								record.set('FinDocument_Number', params.FinDocument_Number);
								record.set('FinDocument_Date', params.FinDocument_Date);
								record.set('FinDocument_Sum', params.FinDocument_Sum);
								record.set('FinDocumentSpec_Sum', params.FinDocumentSpec_Sum);

								if(params.RegistryStatus_id)
									record.set('RegistryStatus_id', params.RegistryStatus_id);
								if(params.RegistryStatus_Code)
									record.set('RegistryStatus_Code', params.RegistryStatus_Code+'');
								if(params.RegistryStatus_Name)
									record.set('RegistryStatus_Name', params.RegistryStatus_Name);
							}
						}
					});
				}
			}
		});

		this.RegistryGrid.params = {
			onHide: function() {
				if (arguments[0] && arguments[0].isChanged)
					wnd.RegistryGrid.refreshRecords(null,0);
			}
		};

		this.doResetSearch();
		this.doSearch();
	},
	initComponent: function() {
		var wnd = this;

		var form = new Ext.Panel({
			autoScroll: true,
			bodyBorder: false,
			bodyStyle: 'padding: 0px 5px',
			height: 102,
			title: lang['poisk'],
			collapsible: true,
			border: false,
			frame: true,
			region: 'north',
			labelAlign: 'right',
			items: [{
				xtype: 'form',
				autoHeight: true,
				id: 'SvodRegistryViewForm',
				style: 'margin-bottom: 0.5em;',
				bodyStyle:'background:#DFE8F6;padding:5px;',
				border: true,
				labelWidth: 70,
				collapsible: true,
				items: [{
					layout: 'column',
					labelAlign: 'right',
					labelWidth: 120,
					items: [{
						layout: 'form',
						items: [{
							fieldLabel: lang['status'],
							hiddenName: 'RegistryStatus_id',
							xtype: 'swcommonsprcombo',
							sortField:'RegistryStatus_Code',
							comboSubject: 'RegistryStatus',
							width: 250
						}]
					}, {
						layout: 'form',

						items: [{
							xtype: 'swdatefield',
							fieldLabel: lang['data'],
							name: 'Registry_Date'
						}]
					}, {
						layout: 'form',
						items: [{
							xtype: 'swdatefield',
							fieldLabel: lang['data_ekspertizyi'],
							name: 'Registry_insDT'
						}]
					}]
				}, {
					layout: 'column',
					labelAlign: 'right',
					labelWidth: 120,
					items: [{
						layout: 'form',
						items: [{
							xtype: 'sworgcombo',
							fieldLabel : lang['postavschik'],
							hiddenName: 'Org_id',
							id: 'srvOrg_id',
							width: 250,
							editable: false,
							onTrigger1Click: function() {
								if (this.disabled)
									return false;
								var combo = this;
								var win = Ext.getCmp('WhsDocumentSupplyViewWindow');
								if (!this.formList) {
									this.formList = new sw.Promed.swListSearchWindow({
										title: lang['poisk_organizatsii'],
										id: 'OrgSearch_' + this.id,
										object: 'Org',
										prefix: 'lsssrv',
										editformclassname: 'swOrgEditWindow',
										store: this.getStore()
									});
								}
								this.formList.show({
									onSelect: function(data) {
										wnd.setOrg(combo, data);
									}
								});
								return false;
							},
							allowBlank: true,
							enableKeyEvents: true,
							listeners: {
								'keydown': function(f, e) {
									if(e.getKey() == Ext.EventObject.ENTER) {
										e.stopEvent();
										wnd.doSearch();
									}
								}
							}
						}]
					}, {
						layout: 'form',
						items: [{
							fieldLabel: lang['vid_lgotyi'],
							hiddenName: 'WhsDocumentCostItemType_id',
							xtype: 'swcommonsprcombo',
							sortField:'WhsDocumentCostItemType_Code',
							comboSubject: 'WhsDocumentCostItemType',
							width: 313,
							allowBlank: true,
							enableKeyEvents: true,
							listeners: {
								'keydown': function(f, e) {
									if(e.getKey() == Ext.EventObject.ENTER) {
										e.stopEvent();
										wnd.doSearch();
									}
								}
							}
						}]
					}]
				}]
			}]
		});

		wnd.RegistryGrid = new sw.Promed.ViewFrame({
			actions: [
				{name: 'action_add', hidden: true},
				{name: 'action_edit', hidden: true},
				{name: 'action_view', disabled: true},
				{name: 'action_delete', hidden: true},
				{name: 'action_print'}
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 150,
			autoLoadData: false,
			schema: 'r64',
			obj_isEvn: false,
			border: true,
			dataUrl: '/?c=SvodRegistry&m=loadList',
			height: 180,
			region: 'center',
			object: 'Registry',
			editformclassname: 'swSvodRegistryDataReceptViewWindow',
			id: 'RegistryGrid',
			paging: false,
			style: 'margin-bottom: 10px',
			stringfields: [
				{name: 'Registry_id', type: 'int', header: 'ID', key: true},
				{name: 'FinDocument_id', type: 'int', hidden: true},
				{name: 'Registry_Num', type: 'string', header: lang['nomer_reestra'], width: 120},
				{name: 'Org_Name', type: 'string', header: lang['postavschik'], width: 120, id: 'autoexpand'},
				{name: 'Registry_begDate', type: 'date', header: lang['nachalo_perioda'], width: 120, hidden: true},
				{name: 'Registry_endDate', type: 'date', header: lang['okonchanie_perioda'], width: 120, hidden: true},
				{name: 'KatNasel_id_Name', type: 'string', header: lang['kategoriya_naseleniya'], width: 120, hidden: true},
				{name: 'KatNasel_id', type: 'int', hidden: true},
				{name: 'WhsDocumentSupply_Num', type: 'string', header: lang['nomer_kontrakta'], width: 120},
				{name: 'RegistryStatus_Name', type: 'string', header: lang['status'], width: 120},
				{name: 'RegistryStatus_Code', type: 'string', hidden: true},
				{name: 'RegistryStatus_id', type: 'int', hidden: true},
				{name: 'Registry_RecordCount', type: 'int', header: lang['kolichestvo_retseptov'], width: 125},
				{name: 'Registry_Sum', type: 'money', header: lang['summa'], width: 100},
				{name: 'Registry_SumPaid', type: 'money', header: lang['cumma_k_oplate'], width: 100},
				{name: 'FinDocument_Number', type: 'string', header: lang['nomer_scheta'], width: 100},
				{name: 'FinDocument_Date', type: 'string', header: lang['data_scheta'], width: 100},
				{name: 'FinDocument_Sum', type: 'money', header: lang['summa_scheta'], width: 100},
				{name: 'FinDocumentSpec_Sum', type: 'money', header: lang['summa_oplatyi'], width: 100}
			],
			toolbar: true,
			onRowSelect: function(sm,rowIdx,record) {
				if (record.get('Registry_id') > 0 && !this.readOnly) {
					if (['2','3','4'].indexOf(record.get('RegistryStatus_Code')) != -1) { //Редактирование платежной информации доступно только для реестров со статусами "К оплате", "В работе", "Оплаченные"
						this.ViewActions.action_fin_edit.setDisabled(false);
					} else {
						this.ViewActions.action_fin_edit.setDisabled(true);
					}
					this.ViewActions.action_view.setDisabled(false);
				} else {
					this.ViewActions.action_fin_edit.setDisabled(true);
					this.ViewActions.action_view.setDisabled(true);
				}
			}
		});

		Ext.apply(this, {
			layout: 'border',
			buttons:
				[{
					text: BTN_FIND,
					handler: function() {
						wnd.doSearch();
					},
					iconCls: 'search16'
				},
				{
					text: BTN_RESETFILTER,
					handler: function() {
						wnd.doResetSearch();
						wnd.doSearch();
					},
					iconCls: 'resetsearch16'
				},
				{
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
				form,
				wnd.RegistryGrid
			]
		});
		sw.Promed.swSvodRegistryViewWindow.superclass.initComponent.apply(this, arguments);
		this.form = this.findById('SvodRegistryViewForm').getForm();
	}
});