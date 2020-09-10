Ext.define('common.InteractiveMapSMP.swInteractiveMapWorkPlace', {
	extend: 'Ext.window.Window',
	alias: 'widget.swInteractiveMapWorkPlace',
	maximized: true,
	constrain: true,
	itemId: 'swInteractiveMapWorkPlace',
	refId : 'smpinteractivemap',
	renderTo: Ext.getCmp('inPanel').body,
	closable: true,
	closeAction: 'hide',
	baseCls: 'arm-window',
	onEsc: Ext.emptyFn,
	title: 'АРМ интерактивной карты СМП',
	header: false,
	layout: {
		type: 'fit'
	},
	requires: [
		'common.InteractiveMapSMP.lib.swCmpCallCardBalloonGrid',
		'common.InteractiveMapSMP.lib.swInteractiveMapPanel'
	],
	border: false,
	items: [
		{
			dockedItems: [{
				xtype: 'toolbar',
				dock: 'top',
				refId: 'filterToolbar',
				border: false,
				items: [

					{
						fieldLabel: 'Статус бригады',
						xtype: 'swEmergencyTeamStatuses',
						labelWidth: 100,
						width: 280
					},
					'|',
					{
						fieldLabel: 'Тип вызова',
						xtype: 'swCmpCallTypeCombo',
						labelWidth: 100,
						width: 250
					},
					{
						fieldLabel: 'Статус вызова',
						xtype: 'swCmpCallCardStatusTypeCombo',
						labelWidth: 100,
						width: 350
					}
				]
			}],
			header: false,
			showMapHeader: false,
			border: false,
			xtype: 'swsmpinteractivemappanel'
		}
	]
});