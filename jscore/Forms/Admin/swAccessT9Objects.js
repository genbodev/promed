/*NO PARSE JSON*/

(function(){
	var objects = {};

	objects.doSave = function(options) {
		var m = [];
		objects.grid.getStore().data.items.forEach(function(rec) {
			m.push(rec.data);
		});
		
		var params = {
			Org_id: getGlobalOptions().org_id,
			groups: Ext.util.JSON.encode(m)
		};
		
		Ext.Ajax.request({
			callback: function(opt, success, response) {
				var response_obj = Ext.util.JSON.decode(response.responseText);
				if(response_obj[0] && response_obj[0].Error_Msg) {
					Ext.Msg.alert('Ошибка', response_obj[0].Error_Msg);
				} else options.callback();
			},
			params: params,
			url: '/?c=AccessRights&m=saveAccessT9Grid'
		});
	}
	objects.onLoadPanel = function() {
		var params = {};
		params['Org_id'] = getGlobalOptions().org_id;
		objects.grid.getStore().load({
			params: params
		});
		var combo = Ext.getCmp('AccessRightsT9LimitOptions_groupscom');
		if(combo) combo.getStore().load();
	};
	objects.check = function(val) {
		var grid = objects.grid;
		var record = grid.getStore().getAt(grid.getStore().findBy(function(rec) { return rec.get('Group_Code') == val; }));
		if(record) {
			var newval = 1-record.get('isAllowed');
			if (record) {
				record.set('isAllowed', newval);
				record.commit();
			}
		}
	};

	objects.checkRenderer = function(v, p, record) {
		var checked = record.get('isAllowed') == 1 ? ' checked="checked"' : '';
		var onclick = 'onClick="Ext.getCmp(\'AccessRightsT9LimitOptions\').check(this.value);"';
		var value = 'value="'+record.get('Group_Code')+'"';
		return '<input disabled type="checkbox" '+checked+' '+onclick+' '+value+'>';
	};

	objects.ToolPanel = new Ext.Panel({
		layout: 'column',
		height: 34,
		bodyStyle:'background:#DFE8F6; padding: 5px;',
		items: [
			{
				layout: 'form',
				labelWidth: 55,
				border: false,
				id: 'form-column-t9option',
				items: [{
					xtype : 'swusersgroupscombo',
					fieldLabel: 'Группы',
					id: 'AccessRightsT9LimitOptions_groupscom',
					width: 230
				}]
			},
			{
				xtype: 'button',
				iconCls:'add16',
				text: 'Добавить',
				style: 'padding: 0 3px 0 3px;',
				handler: function() {
					var combo = Ext.getCmp('AccessRightsT9LimitOptions_groupscom');
					var grid = objects.grid;
					var val = combo.getValue();
					var index = combo.getStore().findBy(function(rec) {return rec.get('Group_id')==val;});
					if(index>=0) {
						var record = combo.getStore().getAt(index);
						var i = grid.getStore().findBy(function(rec) {
							return rec.get('Group_Code')==record.get('Group_Name');
						});
						if(i<0) {
							r = new Ext6.data.Record();
							r.set('isAllowed', 1);
							r.set('Group_Name', record.get('Group_Desc'));
							r.set('Group_Code', record.get('Group_Name'));
							r.set('Group_id', record.get('Group_id'));
							grid.getStore().insert(0, r);
						}
					}
				}
			},
			{
				xtype: 'button',
				iconCls:'delete16',
				text: 'Удалить',
				handler: function() {
					var grid = objects.grid;
					var rec = grid.selModel.getSelected();
					if(rec)	grid.getStore().remove(rec);
				}
			}
		]
	});

	objects.grid = new Ext.grid.GridPanel({
		region: 'center',
		height: 431,
		tbar: objects.ToolPanel,
		store: new Ext.data.JsonStore({
				fields : [{
					name : 'Group_id'
				}, {
					name : 'Group_Name'
				}, {
					name : 'Group_Code'
				}, {
					name : 'isAllowed'
				}],
				autoLoad : false,
				url : '/?c=AccessRights&m=loadAccessT9Grid'
			}),
		columns : [{
				dataIndex : 'Group_Code',
				header : "Группа",
				width : 130,
			}, {
				header : "Наименование",
				width: 270,
				dataIndex : 'Group_Name'
			}, {
				width : 120,
				header : "Доступ разрешен",
				dataIndex : 'isAllowed',
				renderer: objects.checkRenderer,
				style: 'text-align: center;'
			}]
		});
	objects.MainPanel = new Ext.Panel({
		id: 'AccessRightsT9LimitOptions',
		layout: 'anchor',
		height: 431,
		bodyStyle:'background:#DFE8F6;',
		onLoadPanel: objects.onLoadPanel,
		doSave: objects.doSave,
		check: objects.check,
		items: [
			objects.grid
		]
	});

	return objects.MainPanel;
}())