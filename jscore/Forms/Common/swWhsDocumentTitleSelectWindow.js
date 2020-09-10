/**
 * swWhsDocumentTitleSelectWindow - форма выбора правоустанавливающего документа
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2015 Swan Ltd.
 * @author       Dmitry Vlasenko
 */

sw.Promed.swWhsDocumentTitleSelectWindow = Ext.extend(sw.Promed.BaseForm, {
	width: 900,
	height: 400,
	modal: true,
	resizable: false,
	autoHeight: false,
	plain: false,
	title: 'Выбор правоустанавливающего документа',
	onCancel: function() {},
	show: function() {
		sw.Promed.swWhsDocumentTitleSelectWindow.superclass.show.apply(this, arguments);

		var win = this;

		this.WhsDocumentSupply_id = null;

		if (arguments[0]) {
			if (arguments[0].callback && typeof arguments[0].callback == 'function') {
				this.callback = arguments[0].callback;
			}

			if (arguments[0].WhsDocumentSupply_id) {
				this.WhsDocumentSupply_id = arguments[0].WhsDocumentSupply_id;
			}

			if (arguments[0].onCancel && typeof arguments[0].onCancel == 'function') {
				this.onCancel = arguments[0].onCancel;
			}
		}

		this.WhsDocumentTitleGrid.removeAll();
		this.WhsDocumentTitleGrid.loadData({
			globalFilters: {
				WhsDocumentSupply_id: win.WhsDocumentSupply_id
			},
			callback: function() {
				if (win.WhsDocumentTitleGrid.getGrid().getStore().getCount() == 1) {
					// если один сразу выбираем
					win.WhsDocumentTitleSelect();
				} else if (win.WhsDocumentTitleGrid.getGrid().getStore().getCount() == 0) {
					win.hide();

					sw.swMsg.alert('Внимание', 'Список аптек к контракту не установлен.');
				}
			}
		});

		this.center();
	},

	callback: Ext.emptyFn,

	WhsDocumentTitleSelect: function() {
		var record = this.WhsDocumentTitleGrid.getGrid().getSelectionModel().getSelected();
		if (!record || Ext.isEmpty(record.get('WhsDocumentTitle_id'))) {
			return false;
		}

		this.callback(record.data);
		this.hide();
	},

	initComponent: function() {

		var win = this;

		this.WhsDocumentTitleGrid = new sw.Promed.ViewFrame({
			uniqueId: true,
			toolbar: false,
			useEmptyRecord: false,
			onEnter: this.WhsDocumentTitleSelect.createDelegate(this),
			actions: [
				{ name: 'action_add', hidden: true, disabled: true },
				{ name: 'action_edit', hidden: true, disabled: true },
				{ name: 'action_view', hidden: true, disabled: true },
				{ name: 'action_delete', hidden: true, disabled: true }
			],
			dataUrl: '/?c=WhsDocumentTitle&m=loadList',
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 150,
			border: false,
			autoLoadData: false,
			stripeRows: true,
			stringfields: [
				{name: 'WhsDocumentTitle_id', type: 'int', hidden: true, key: true},
				{name: 'WhsDocumentTitle_Name', type: 'string', header: 'Наименование документа', width: 120, id: 'autoexpand'},
				{name: 'WhsDocumentTitleType_id_Name', type: 'string', header: 'Тип документа', width: 250},
				{name: 'WhsDocumentStatusType_id', type: 'string', header: 'Статус', hidden: true},
				{name: 'WhsDocumentStatusType_id_Name', type: 'string', header: 'Статус', width: 120},
				{name: 'WhsDocumentTitle_begDate', type: 'date', header: 'Дата начала действия', width: 150},
				{name: 'WhsDocumentTitle_endDate', type: 'date', header: 'Дата окончания действия', width: 150},
				{name: 'WhsDocumentTitleTariff_id', hidden: true},
				{name: 'UslugaComplexTariff_Name', type: 'string', header: 'Тариф', width: 250}
			]
		});

		this.WhsDocumentTitleGrid.getGrid().on('rowdblclick', this.WhsDocumentTitleSelect.createDelegate(this));

		Ext.apply(this, {
			buttonAlign: 'right',
			layout: 'fit',
			buttons: [{
				text: 'Выбрать',
				iconCls: 'ok16',
				handler: this.WhsDocumentTitleSelect.createDelegate(this)
			},
				'-',
				{
					text: 'Закрыть',
					iconCls: 'close16',
					handler: function(button, event) {
						win.onCancel();
						win.hide();
					}
				}],
			items: [this.WhsDocumentTitleGrid]

		});

		sw.Promed.swWhsDocumentTitleSelectWindow.superclass.initComponent.apply(this, arguments);
	}
});