Ext.define('common.InteractiveMapSMP.lib.swCmpCallCardBalloonGrid',{
	extend: 'Ext.grid.Panel',
	alias: 'widget.swcmpcallcardballoongrid',
	refId: 'swCmpCallCardBalloonGrid',
	currentAmbulancePlacemark: {},
	width: 335,
	columns: [
		{
			dataIndex: 'CmpCallCard_Numv',
			text: '№',
			width: 30
		},
		{
			dataIndex: 'CmpReason_Name',
			text: 'Повод',
			width: 50
		},
		{
			dataIndex: 'CmpCallCard_Urgency',
			text: 'Срочн',
			width: 30
		},
		{
			dataIndex: 'EventWaitDuration',
			text: 'В статусе',
			width: 60,
			renderer: function(value){
				hours = Math.round(value/ 60);
				minutes = value % 60;
				return hours+':'+( minutes >= 10 ? minutes : ('0' + minutes) )
			}
		},
		{
			dataIndex: 'DeliveranceTime',
			text: 'Доезд',
			width: 80,
		},
		{
			width: 30,
			xtype: 'actioncolumn',
			iconCls: 'driver16',
			handler: function(grid, rowIndex, colIndex, item, e, rec ) {
				grid.ownerCt.fireEvent('onBuildRoute', rec);
			}
		},
		{
			xtype: 'actioncolumn',
			width: 30,
			iconCls: 'active-call16',
			handler: function(grid, rowIndex, colIndex, item, e, rec) {
				grid.ownerCt.fireEvent('onSetCmpCallCard', rec);
			}
		}
	],
	initComponent: function() {
		var me = this;

		me.addEvents({
			onBuildRoute: true,
			onSetCmpCallCard: true
		});

		this.callParent(arguments);
	}
});