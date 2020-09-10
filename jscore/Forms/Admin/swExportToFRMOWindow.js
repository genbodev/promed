/**
 * swExportToFRMOWindow - окно экспорта данных в сервис ФРМО
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      	Admin
 * @access       	public
 * @copyright		Copyright (c) 2017 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			08.2017
 */
/*NO PARSE JSON*/

sw.Promed.swExportToFRMOWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'swExportToFRMOWindow',
	width: 800,
	height: 460,
	modal: true,
	maximizable: true,
	title: 'Передача данных в сервис ФРМО',

	doExport: function() {
		var win = this;
		var grid = this.GridPanel.getGrid();

		var LpuList = [];

		this.GridPanel.getMultiSelections().forEach(function(rec) {
			if (rec.get('Lpu_id')) {
				LpuList.push(rec.get('Lpu_id'));
			}
		});

		var params = {
			LpuList: Ext.util.JSON.encode(LpuList)
		};

		win.getLoadMask('Запуск передачи данных в сервис ФРМО').show();
		Ext.Ajax.request({
			url: '/?c=ServiceFRMO&m=runExport',
			params: params,
			callback: function() {
				win.getLoadMask().hide();
				win.hide();
			}
		});
	},

	toggleExportToFRMO: function(Lpu_id) {
		var grid = this.GridPanel.getGrid();
		var record = grid.getStore().getById(Lpu_id);

		record.set('ExportToFRMO', record.get('ExportToFRMO') == 2 ? 1 : 2);
		record.commit();
	},

	show: function() {
		sw.Promed.swExportToFRMOWindow.superclass.show.apply(this, arguments);

		this.GridPanel.loadData();
	},

	initComponent: function() {
		var wnd = this;

		this.checkRenderer = function(v, p, record) {
			var id = record.get('Lpu_id');
			var value = 'value="'+id+'"';
			var checked = record.get('ExportToFRMO') == 2 ? ' checked="checked"' : '';
			var onclick = 'onClick="Ext.getCmp(\''+wnd.id+'\').toggleExportToFRMO(this.value);"';
			var disabled = '';

			return '<input type="checkbox" '+value+' '+checked+' '+onclick+' '+disabled+'>';
		};

		this.GridPanel = new sw.Promed.ViewFrame({
			id: 'ETFW_GridPanel',
			selectionModel: 'multiselect',
			dataUrl: '/?c=ServiceFRMO&m=loadLpuListForExport',
			autoLoadData: false,
			useEmptyRecord: false,
			toolbar: false,
			noSelectFirstRowOnFocus: true,
			onLoadData: function() {
				// выбрать те, для которых ExportToFRMO = 2
				var grid = this.getGrid();
				grid.getStore().each(function(rec) {
					if (rec.get('ExportToFRMO') == 2) {
						var index = grid.getStore().indexOf(rec);
						grid.getSelectionModel().selectRow(index, true);
					}
				});
			},
			stringfields: [
				{name: 'Lpu_id', type: 'int', key: true},
				{name: 'ExportToFRMO', type: 'int', hidden: true},
				{name: 'PassportToken_tid', type: 'string', hidden: true},
				{name: 'ServiceListPackage_insDT', type: 'string', header: 'Дата последней успешной выгрузки', width: 200},
				{name: 'Lpu_Nick', type: 'string', header: 'Наименование МО', id: 'autoexpand'}
			]
		});

		Ext.apply(this,{
			buttons: [
				{
					handler: function () {
						this.doExport();
					}.createDelegate(this),
					//iconCls: 'save16',
					id: 'ETFW_ExportButton',
					text: 'Выгрузить'
				},
				{
					text: '-'
				},
				HelpButton(this),
				{
					handler: function()
					{
						this.hide();
					}.createDelegate(this),
					iconCls: 'cancel16',
					text: BTN_FRMCLOSE
				}
			],
			layout: 'fit',
			items: [this.GridPanel]
		});

		sw.Promed.swExportToFRMOWindow.superclass.initComponent.apply(this, arguments);
	}
});