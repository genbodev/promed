/**
 * swEvnUslugaCopyWindow - копировние услуг
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      	Common
 * @access       	public
 * @copyright		Copyright (c) 2018 Swan Ltd.
 */
/*NO PARSE JSON*/

sw.Promed.swEvnUslugaCopyWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'swEvnUslugaCopyWindow',
	layout: 'border',
	title: 'Копирование услуг из случая лечения',
	maximizable: true,
	maximized: false,
	width:600,
	height:300,
	show: function() {
		sw.Promed.swEvnUslugaCopyWindow.superclass.show.apply(this, arguments);
		
		this.Evn_id = arguments[0].Evn_id || null;
		this.Morbus_id = arguments[0].Morbus_id || null;
		this.callback = arguments[0].callback || Ext.emptyFn;

		this.EvnUslugaGrid.loadData({
			params: {
				pid: this.Evn_id,
				isMorbusOnko: 2
			},
			globalFilters: {
				pid: this.Evn_id,
				isMorbusOnko: 2
			}
		});
	},
	doCopy: function() {
		var win = this,
			grid = this.EvnUslugaGrid.getGrid(),
			records = grid.getSelectionModel().getSelections();
			
		if (!records) return false;
		
		var ids = [];
		for(var record in records){
			if(records[record].data && records[record].data['EvnUsluga_id']) {
				ids.push(records[record].data['EvnUsluga_id']);
			}
		}
		
		ids = Ext.util.JSON.encode(ids);
		
		win.getLoadMask("Подождите, идет копирование...").show();
		Ext.Ajax.request({
			url: '/?c=EvnUsluga&m=copyEvnUsluga',
			callback: function(options, success, response)  {
				win.getLoadMask().hide();
				if (success) {
					var response_obj = Ext.util.JSON.decode(response.responseText);
					win.callback();
					win.hide();
				}
			},
			params: {
				ids: ids,
				Morbus_id: this.Morbus_id,
			}
		});
		
	},
	initComponent: function() {
		this.EvnUslugaGrid = new sw.Promed.ViewFrame({
			id: this.id + 'EvnUslugaGrid',
			dataUrl: '/?c=EvnUsluga&m=loadEvnUslugaGrid',
			border: false,
			autoLoadData: false,
			selectionModel:'multiselect',
			useEmptyRecord: false,
			region: 'center',
			toolbar: false,
			stringfields: [
				{name: 'EvnUsluga_id', type: 'int', header: 'ID', key: true},
				{name: 'Usluga_Code', header: 'Код услуги', type: 'string', width: 120},
				{name: 'Usluga_Name', header: 'Наименование услуги', type: 'string', id: 'autoexpand'},
				{name: 'UslugaComplexAttributeType_Name', header: 'Вид услуги', type: 'string', width: 120}
			],
			actions: [
				{name:'action_add', hidden: true},
				{name:'action_edit', hidden: true},
				{name:'action_view', hidden: true},
				{name:'action_delete', hidden: true}
			]
		});

		Ext.apply(this, {
			layout: 'border',
			buttons: [{
				handler: function()
				{
					this.doCopy();
				}.createDelegate(this),
				iconCls: 'copy16',
				text: 'Копировать'
			}, {
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
			}],
			items: [
				this.EvnUslugaGrid
			]
		});

		sw.Promed.swEvnUslugaCopyWindow.superclass.initComponent.apply(this, arguments);
	}
});