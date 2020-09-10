Ext6.define('usluga.components.EvnAggGrid', {
	extend: 'GreyAutoHideGridPanel',
	requires: ['usluga.components.models.EvnAggModel'],
	alias: 'widget.EvnAggGrid',

	padding: 20,

	threeDotMenu: Ext6.create('Ext6.menu.Menu', {
		items: [{
			text: 'Редактировать',
			iconCls: 'panicon-edit',
			handler: function(btn, e)
			{
				var controller = this.up('menu').controller;

				return controller ? controller.openEvnAggEditWindow(btn, e) : false;
			}
		}, {
			text: 'Удалить запись',
			iconCls: 'panicon-delete',
			handler: function(btn, e)
			{
				var rec = this.up('menu').selRecord;

				Ext6.Msg.show({
					title: 'Вопрос',
					msg: 'Вы действительно хотите удалить осложнение?',
					buttons: Ext6.Msg.YESNO,
					icon: Ext6.Msg.QUESTION,
					fn: function(btn)
					{
						if ( btn === 'yes' )
						{
							rec.erase();
						}
					}
				});
			}
		}]
	}),


	columns: [{
		text: 'Вид осложнения',
		height: 30,
		flex: 1,
		minWidth: 100,
		dataIndex: 'AggType_Name'
	},{
		text: 'Контекст осложнения',
		dataIndex: 'AggWhen_Name',
		flex: 1,
		minWidth: 100
	}, {
		text: 'Дата осложнения',
		dataIndex: 'EvnAgg_setDate',
		flex: 1,
		minWidth: 100,
		xtype:'datecolumn',
		format:'d.m.Y'
	},{
		xtype: 'actioncolumn',
		disabled: true,
		width: 35,
		sortable: false,
		menuDisabled: true,
		iconCls: 'grid-header-icon-menuItem',
		tooltip: 'Меню',
		handler: 'onMenuClick',
		bind: {
			disabled: '{editable === false}'
		}
	}],

	store: {
		model: 'usluga.components.models.EvnAggModel',
		sorters: [
			'EvnAgg_id'
		]
	}
});
