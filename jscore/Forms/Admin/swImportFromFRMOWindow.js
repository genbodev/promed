/**
 * swImportFromFRMOWindow - окно импорта данных из сервиса ФРМО
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      	Admin
 * @access       	public
 * @copyright		Copyright (c) 2019 Swan Ltd.
 * @author			Stanislav Bykov (savage@swan-it.ru)
 * @version			05.2019
 */

sw.Promed.swImportFromFRMOWindow = Ext.extend(sw.Promed.BaseForm, {
	height: 460,
	id: 'swImportFromFRMOWindow',
	maximizable: true,
	modal: true,
	title: 'Импорт данных из сервиса ФРМО',
	width: 800,

	doImport: function() {
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

		win.getLoadMask('Запуск импорта данных из сервиса ФРМО').show();

		Ext.Ajax.request({
			url: '/?c=ServiceFRMO&m=runImport',
			params: params,
			callback: function(opt, success, r) {
				win.getLoadMask().hide();

				if ( !Ext.isEmpty(r.responseText) ) {
					var respObj = Ext.util.JSON.decode(r.responseText);

					if ( !Ext.isEmpty(respObj.Error_Msg) ) {
						sw.swMsg.alert('Ошибка', respObj.Error_Msg);
					}
					else {
						win.callback();
						win.hide();
					}
				}
			}
		});
	},

	toggleImportFromFRMO: function(Lpu_id) {
		var grid = this.GridPanel.getGrid();
		var record = grid.getStore().getById(Lpu_id);

		record.set('ImportFromFRMO', record.get('ImportFromFRMO') == 2 ? 1 : 2);
		record.commit();
	},

	show: function() {
		sw.Promed.swImportFromFRMOWindow.superclass.show.apply(this, arguments);

		this.callback = Ext.emptyFn;

		if ( arguments[0].callback ) {
			this.callback = arguments[0].callback;
		}

		this.GridPanel.loadData();
	},

	initComponent: function() {
		var wnd = this;

		this.checkRenderer = function(v, p, record) {
			var id = record.get('Lpu_id');
			var value = 'value="'+id+'"';
			var checked = record.get('ImportFromFRMO') == 2 ? ' checked="checked"' : '';
			var onclick = 'onClick="Ext.getCmp(\''+wnd.id+'\').toggleImportFromFRMO(this.value);"';
			var disabled = '';

			return '<input type="checkbox" '+value+' '+checked+' '+onclick+' '+disabled+'>';
		};

		this.GridPanel = new sw.Promed.ViewFrame({
			id: 'IFFW_GridPanel',
			selectionModel: 'multiselect',
			dataUrl: '/?c=ServiceFRMO&m=loadLpuListForImport',
			autoLoadData: false,
			useEmptyRecord: false,
			toolbar: false,
			noSelectFirstRowOnFocus: true,
			onLoadData: function() {
				// выбрать те, для которых ImportFromFRMO = 2
				var grid = this.getGrid();
				grid.getStore().each(function(rec) {
					if (rec.get('ImportFromFRMO') == 2) {
						var index = grid.getStore().indexOf(rec);
						grid.getSelectionModel().selectRow(index, true);
					}
				});
			},
			stringfields: [
				{name: 'Lpu_id', type: 'int', key: true},
				{name: 'ImportFromFRMO', type: 'int', hidden: true},
				{name: 'PassportToken_tid', type: 'string', hidden: true},
				{name: 'FRMOSession_endDT', type: 'string', header: 'Дата последнего успешного импорта', width: 200},
				{name: 'Lpu_Nick', type: 'string', header: 'Наименование МО', id: 'autoexpand'}
			]
		});

		Ext.apply(this,{
			buttons: [{
				handler: function () {
					this.doImport();
				}.createDelegate(this),
				id: 'IFFW_ImportButton',
				text: 'Выгрузить'
			},
			{
				text: '-'
			},
			HelpButton(this),
			{
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'cancel16',
				text: BTN_FRMCLOSE
			}],
			layout: 'fit',
			items: [
				this.GridPanel
			]
		});

		sw.Promed.swImportFromFRMOWindow.superclass.initComponent.apply(this, arguments);
	}
});