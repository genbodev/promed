Ext6.define('common.Timetable.models.LpuStructureNode', {
	extend: 'Ext.data.TreeModel',
	alias: 'model.timetablelpustructurenode',
	fields: [
		{name: 'id', type: 'string'},
		{name: 'text', type: 'string'},
		{name: 'iconCls', type: 'string'},
		{name: 'leaf', type: 'boolean', defaultValue: false}
	],
	proxy: {
		type: 'ajax',
		url: '/?c=Timetable6E&m=loadLpuStructureTree',
		reader: {
			type: 'json',
			typeProperty: 'nodeType'
		}
	}
});