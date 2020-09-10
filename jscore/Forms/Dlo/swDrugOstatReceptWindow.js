/**
* swDrugOstatReceptWindow - окно просмотра наличия медикамента в аптеках.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      DLO
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Ivan Pshenitcyn aka IVP (ipshon@rambler.ru)
* @version      21.04.2009
*/

sw.Promed.swDrugOstatReceptWindow = Ext.extend(sw.Promed.BaseForm, {
	buttonAlign: 'left',
	closable: true,
	closeAction: 'hide',
	draggable: true,
	drugId: null,
	drugName: null,
	height: 400,
	id: 'DrugOstatReceptWindow',
	initComponent: function() {
		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.ownerCt.onOkButtonClick();
				},
				iconCls: 'ok16',
				text: lang['vyibrat']
			}, {
				text: '-'
			},
			HelpButton(this),
			{
				handler: function() {
					this.ownerCt.hide();
				},
				iconCls: 'close16',
				text: '<u>З</u>акрыть'
			}],
			items: [ new Ext.DataView({
				border: false,
				height: 35,
				frame: false,
				id: 'DORW_DrugInfo',
				itemSelector: 'div',
				region: 'north',
				store: new Ext.data.JsonStore({
					autoLoad: false,
					fields: [
						{ name: 'Drug_Name' }
					]
				}),
				style: 'padding: 1em;',
				tpl: new Ext.XTemplate(
					'<tpl for=".">',
					'<div>Медикамент: <font style="color: blue; font-weight: bold;">{Drug_Name}</font></div>',
					'</tpl>'
				)
			}),
			new Ext.grid.GridPanel({
				autoExpandColumn: 'autoexpand',
				autoExpandMin: 200,
				bodyBorder: false,
				border: false,
				columns: [{
					dataIndex: 'OrgFarmacy_Name',
					header: lang['apteka'],
					width: 350,
					sortable: true
				}, {
					dataIndex: 'OrgFarmacy_HowGo',
					header: lang['adres'],
					id: 'autoexpand',
					sortable: true
				}, {
					dataIndex: 'DrugOstat_Kolvo',
					header: lang['ostatki'],
					sortable: true,
					width: 80
				}],
				id: 'DORW_DrugOstatReceptGrid',
				keys: [{
					key: [
						Ext.EventObject.END,
						Ext.EventObject.ENTER,
						Ext.EventObject.HOME,
						Ext.EventObject.PAGE_DOWN,
						Ext.EventObject.PAGE_UP
					],
					fn: function(inp, e) {
						e.stopEvent();

						if ( e.browserEvent.stopPropagation )
							e.browserEvent.stopPropagation();
						else
							e.browserEvent.cancelBubble = true;

						if ( e.browserEvent.preventDefault )
							e.browserEvent.preventDefault();
						else
							e.browserEvent.returnValue = false;

						e.returnValue = false;

						if (Ext.isIE)
						{
							e.browserEvent.keyCode = 0;
							e.browserEvent.which = 0;
						}

						var grid = Ext.getCmp('DORW_DrugOstatReceptGrid');

						switch (e.getKey())
						{
							case Ext.EventObject.END:
								if (grid.getStore().getCount() > 0)
								{
									grid.getView().focusRow(grid.getStore().getCount() - 1);
									grid.getSelectionModel().selectLastRow();
								}
								break;

							case Ext.EventObject.ENTER:
								grid.ownerCt.onOkButtonClick();
								break;

							case Ext.EventObject.HOME:
								if (grid.getStore().getCount() > 0)
								{
									grid.getView().focusRow(0);
									grid.getSelectionModel().selectFirstRow();
								}
								break;

							case Ext.EventObject.PAGE_DOWN:
								var records_count = grid.getStore().getCount();

								if (records_count > 0 && grid.getSelectionModel().getSelected())
								{
									var index = grid.getStore().indexOf(grid.getSelectionModel().getSelected());

									if (index + 10 <= records_count - 1)
									{
										index = index + 10;
									}
									else
									{
										index = records_count - 1;
									}

									grid.getView().focusRow(index);
									grid.getSelectionModel().selectRow(index);
								}
								break;

							case Ext.EventObject.PAGE_UP:
								var records_count = grid.getStore().getCount();

								if (records_count > 0 && grid.getSelectionModel().getSelected())
								{
									var index = grid.getStore().indexOf(grid.getSelectionModel().getSelected());

									if (index - 10 >= 0)
									{
										index = index - 10;
									}
									else
									{
										index = 0;
									}

									grid.getView().focusRow(index);
									grid.getSelectionModel().selectRow(index);
								}
								break;
						}
					},
					stopEvent: true
				}],
				listeners: {
					'rowdblclick': function(grid, number, obj) {
						grid.ownerCt.onOkButtonClick();
					}
				},
				loadMask: true,
				region: 'center',
				sm: new Ext.grid.RowSelectionModel({
					listeners: {
						'rowselect': function(sm, rowIndex, record) {
							// this.grid.getTopToolbar().items.items[3].el.innerHTML = String(rowIndex + 1) + ' / ' + this.grid.getStore().getCount();
						}
					}
				}),
				store: new Ext.data.JsonStore({
					autoLoad: false,
					key: 'OrgFarmacy_id',
					fields: [
						'OrgFarmacy_id',
						'OrgFarmacyIndex_id',
						'OrgFarmacy_Name',
						'OrgFarmacy_HowGo',
						'OrgFarmacy_IsFarmacy',
						'DrugOstat_Kolvo',
						'OMSSprTerr_Code'
					],
					listeners: {
						'load': function(store, records, options) {
							var grid = Ext.getCmp('DORW_DrugOstatReceptGrid');

							if (store.getCount() == 0)
							{
								LoadEmptyRow(grid);
							}
							else
							{
								var row;
								for (var i = 0; i < store.getCount(); i++)
								{
									row = grid.getStore().getAt(i);
									if (row.get('OrgFarmacyIndex_id') == null && row.get('OrgFarmacy_id') != 1) {
										grid.getView().getRow(i).style.color = 'gray';
									}
									else {
										grid.getView().getRow(i).style.color = 'black';
									}
								}
							}

							// grid.getTopToolbar().items.items[3].el.innerHTML = '0 / ' + store.getCount();

							grid.getView().focusRow(0);
							grid.getSelectionModel().selectFirstRow();
						}
					},
					sortInfo: {
						direction: 'DESC',
						field: 'OrgFarmacyIndex_id'
					},
					url: C_DRUG_OSTAT
				}),
				stripeRows: true/*,
				tbar: new sw.Promed.Toolbar({
					buttons: [{
						handler: function() {
							this.disable();
							this.ownerCt.items.items[1].enable();

							var current_window = Ext.getCmp('DrugOstatReceptWindow');
							var grid = current_window.findById('DORW_DrugOstatReceptGrid');

							grid.getStore().removeAll();
							grid.getStore().load();
						},
						text: lang['apteki_s_ostatkami']
					}, {
						handler: function() {
							this.disable();
							this.ownerCt.items.items[0].enable();

							var current_window = Ext.getCmp('DrugOstatReceptWindow');
							var grid = current_window.findById('DORW_DrugOstatReceptGrid');

							grid.getStore().removeAll();
							grid.getStore().load({
								params: {
									mode: 'all'
								}
							});
						},
						text: lang['vse_apteki']
					}, {
						xtype: 'tbfill'
					}, {
						text: '0 / 0',
						xtype: 'tbtext'
					}]
				})*/
			})/*,
			new Ext.DataView({
				border: false,
				height: 35,
				frame: false,
				id: 'DORW_DrugOstatSkladInfo',
				itemSelector: 'div',
				region: 'south',
				store: new Ext.data.JsonStore({
					autoLoad: false,
					fields: [
						{ name: 'DrugOstat_Value' }
					],
					url: '/?c=Drug&m=checkDrugOstatOnSklad'
				}),
				style: 'padding: 1em;',
				tpl: new Ext.XTemplate(
					'<tpl for=".">',
					//'<div>Остатки на аптечном складе: <font style="color: blue; font-weight: bold;">{DrugOstat_Value}</font></div>',
					'</tpl>'
				)
			})*/]
		});
		sw.Promed.swDrugOstatReceptWindow.superclass.initComponent.apply(this, arguments);
	},
	keys: [{
		alt: true,
		fn: function(inp, e) {
			Ext.getCmp('DrugOstatReceptWindow').hide();
		},
		key: [ Ext.EventObject.P ],
		stopEvent: true
	}],
	layout: 'border',
	listeners: {
		'hide': function() {
			this.onHide();
		}
	},
	maximizable: true,
	minHeight: 400,
	minWidth: 700,
	onHide: Ext.emptyFn,
	onOkButtonClick: function() {
		var current_window = this;
		var grid = current_window.findById('DORW_DrugOstatReceptGrid');

		if ( !grid.getSelectionModel().getSelected() ) {
			return false;
		}

		var selected_record = grid.getSelectionModel().getSelected();

		current_window.onSelect(selected_record.data);
		current_window.hide();
	},
	onSelect: Ext.emptyFn,
	plain: true,
	receptFinanceCode: null,
	receptTypeCode: null,
	resizable: true,
	show: function() {
		sw.Promed.swDrugOstatReceptWindow.superclass.show.apply(this, arguments);

		this.drugId = null;
		this.drugName = null;
		this.onHide = Ext.emptyFn;
		this.onSelect = Ext.emptyFn;
		this.receptFinanceCode = null;
		this.receptTypeCode = null;

		if ( arguments[0] ) {
			if ( arguments[0].Drug_id ) {
				this.drugId = arguments[0].Drug_id;
			}

			if ( arguments[0].Drug_Name ) {
				this.drugName = arguments[0].Drug_Name;
			}

			if ( arguments[0].onHide ) {
				this.onHide = arguments[0].onHide;
			}

			if ( arguments[0].onSelect ) {
				this.onSelect = arguments[0].onSelect;
			}

			if ( arguments[0].ReceptFinance_Code ) {
				this.receptFinanceCode = arguments[0].ReceptFinance_Code;
			}

			if ( arguments[0].ReceptType_Code ) {
				this.receptTypeCode = arguments[0].ReceptType_Code;
			}
		}

		this.findById('DORW_DrugInfo').getStore().removeAll();
		this.findById('DORW_DrugOstatReceptGrid').getStore().removeAll();
		this.findById('DORW_DrugOstatReceptGrid').getStore().baseParams.Drug_id = null;
/*
		this.findById('DORW_DrugOstatReceptGrid').getTopToolbar().items.items[0].disable();
		this.findById('DORW_DrugOstatReceptGrid').getTopToolbar().items.items[1].disable();
		this.findById('DORW_DrugOstatReceptGrid').getTopToolbar().items.items[3].el.innerHTML = '0 / 0';
*/
		//this.findById('DORW_DrugOstatSkladInfo').getStore().removeAll();

		this.restore();
		this.center();

		if ( this.drugId && this.drugName && this.receptFinanceCode ) {
			this.findById('DORW_DrugInfo').getStore().loadData([{ Drug_Name: this.drugName }]);

			this.findById('DORW_DrugOstatReceptGrid').getStore().baseParams.Drug_id = this.drugId;
			this.findById('DORW_DrugOstatReceptGrid').getStore().baseParams.mode = 'all';
			this.findById('DORW_DrugOstatReceptGrid').getStore().baseParams.ReceptFinance_Code = this.receptFinanceCode;
			this.findById('DORW_DrugOstatReceptGrid').getStore().baseParams.ReceptType_Code = this.receptTypeCode;

			this.findById('DORW_DrugOstatReceptGrid').getStore().load();

			/*this.findById('DORW_DrugOstatSkladInfo').getStore().load({
				callback: function(records, options, success) {
					if ( !success ) {
						this.findById('DORW_DrugOstatSkladInfo').loadData([{ DrugOstat_Value: lang['net'] }]);
					}
				}.createDelegate(this),
				params: {
					Drug_id: this.drugId,
					ReceptFinance_Code: this.receptFinanceCode
				}
			});*/
		}
	},
	title: WND_DLO_MEDAPT,
	width: 700
});
