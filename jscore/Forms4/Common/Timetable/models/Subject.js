Ext6.define('common.Timetable.models.Subject', {
	extend: 'Ext.data.Model',
	alias: 'model.timetablesubject',
	fields: [
		{name: 'id', type: 'string'},
		{name: 'name', type: 'string'},
		{name: 'place', type: 'string'},
		{name: 'count', type: 'int'}
	],
	proxy: {
		type: 'ajax',
		url: '/?c=Timetable6E&m=loadSubjectList',
		reader: {type: 'json'}
	}
});