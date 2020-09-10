/*NO PARSE JSON*/

(function(){
	var objects = {};

	objects.onLoadPanel = function() {
		objects.GridPanel.loadData();
	};

	objects.doSave = function(options) {
		var grid = objects.GridPanel.getGrid();

		var params = {};
		grid.getStore().each(function(record){
			var key = record.get('DataStorage_Name');
			params[key] = record.get('DataStorage_Value');
		});

		Ext.Ajax.request({
			url: '/?c=Options&m=savePersonRegisterAutoIncludeOptions',
			params: params,
			failure: function() {},
			success: function() {
				options.callback();
			}
		});
	};

	objects.checkRenderer = function(v, p, record) {
		var name = record.get('DataStorage_Name');
		var value = 'value="'+name+'"';
		var checked = record.get('DataStorage_Value') == 1 ? ' checked="checked"' : '';
		var onclick = 'onClick="Ext.getCmp(\'PersonRegisterIncludeOptions\').checkOne(this.value);"';
		var disabled = '';

		return '<input type="checkbox" '+value+' '+checked+' '+onclick+' '+disabled+'>';
	};

	objects.checkOne = function(name) {
		var grid = objects.GridPanel.getGrid();

		var record = grid.getStore().getAt(grid.getStore().findBy(function(rec) { return rec.get('DataStorage_Name') == name; }));
		if (record) {
			record.set('DataStorage_Value', record.get('DataStorage_Value') == 1 ? 0 : 1);
			record.commit();
		}
	};

	objects.GridPanel = new sw.Promed.ViewFrame({
		dataUrl: '/?c=Options&m=loadPersonRegisterAutoIncludeGrid',
		border: true,
		layout: 'fit',
		autoLoadData: false,
		toolbar: false,
		root: 'data',
		stringfields: [
			{name: 'DataStorage_id', type: 'int', header: 'ID', key: true},
			{name: 'DataStorage_Value', type: 'int', hidden: true},
			{name: 'DataStorage_Name', type: 'string', hidden: true},
			{name: 'PersonRegisterType_SysNick', type: 'string', hidden: true},
			{name: 'PersonRegisterType_Name', header: lang['tip_registra'], type: 'string', id: 'autoexpand'},
			{name: 'check', header: lang['avtomaticheskoe_vklyuchenie_v_registr'], width: 220, renderer: objects.checkRenderer}
		],
		actions: [
			{name:'action_add', hidden:true, disabled: true},
			{name:'action_edit', hidden:true, disabled: true},
			{name:'action_view', hidden:true, disabled: true},
			{name:'action_delete', hidden:true, disabled: true}
		]
	});

	objects.MainPanel = new Ext.Panel({
		id: 'PersonRegisterIncludeOptions',
		layout: 'form',
		border: false,
		height: 431,
		autoScroll: true,
		bodyStyle:'padding: 5px; background:#DFE8F6;',
		onLoadPanel: objects.onLoadPanel,
		doSave: objects.doSave,
		checkOne: objects.checkOne,
		items: [{
			xtype: 'fieldset',
			title: lang['tip_vklyucheniya_v_registr_po_szz'],
			autoHeight: true,
			items: [objects.GridPanel]
		}]
	});

	return objects.MainPanel;
}())