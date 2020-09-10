Ext6.define('usluga.components.OperAnestGrid', {
	extend: 'GreyAutoHideGridPanel',
	requires: ['usluga.components.models.OperAnestModel'],
	alias: 'widget.OperAnestGrid',

	padding: 20,

	threeDotMenu: Ext6.create('Ext6.menu.Menu', {
		items: [{
			text: 'Редактировать',
			iconCls: 'panicon-edit',
			handler: function(btn, e)
			{
				var controller = this.up('menu').controller;

				return controller ? controller.openEvnUslugaOperAnestEditWindow(btn, e) : false;
			}
		}, {
			text: 'Удалить запись',
			iconCls: 'panicon-delete',
			handler: function(btn, e)
			{
				var rec = this.up('menu').selRecord;

				Ext6.Msg.show({
					title: 'Вопрос',
					msg: 'Вы действительно хотите удалить анестезию?',
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
		text: langs('Код'),
		height: 30,
		width: 50,
		dataIndex: 'AnesthesiaClass_Code'
	},{
		text: langs('Наименование'),
		dataIndex: 'AnesthesiaClass_Name',
		flex: 3,
		minWidth: 100
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
		model: 'usluga.components.models.OperAnestModel',
		sorters: [
			'EvnUslugaOperAnest_id'
		]
	}
});
