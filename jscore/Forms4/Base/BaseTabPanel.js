Ext6.define('base.BaseTabPanel', {
	extend: 'Ext6.panel.Panel',
	alias: 'widget.BaseTabPanel',
	border: false,
	defaults: {
		tabConfig: {
			margin: 0,
			cls: 'evn-pl-tab-panel-items'
		}
	},
	items: [
		{
			title: 'Выписанные из стационара',
			border: false,
			html:'',
		}
	],
	listeners: {
		beforeload: 'beforeload'
	}
});