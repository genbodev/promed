Ext6.define('usluga.components.EvnDrugGrid', {
	extend: 'GreyAutoHideGridPanel',
	requires: ['usluga.components.models.EvnDrugModel'],
	alias: 'widget.EvnDrugGrid',

	padding: 20,

	threeDotMenu: Ext6.create('Ext6.menu.Menu', {
		items: [{
			text: 'Редактировать',
			iconCls: 'panicon-edit',
			handler: function(btn, e)
			{
				var controller = this.up('menu').controller;

				return controller ? controller.openEvnDrugEditWindow(btn, e) : false;
			}
		}, {
			text: 'Удалить запись',
			iconCls: 'panicon-delete',
			handler: function(btn, e)
			{
				var rec = this.up('menu').selRecord;

				Ext6.Msg.show({
					title: 'Вопрос',
					msg: 'Вы действительно хотите удалить медикамент?',
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

	columns: [
		{
			text: langs('Дата'),
			dataIndex: 'EvnDrug_setDate',
			flex: 1,
			height: 30,
			minWidth: 100,
			xtype:'datecolumn',
			format:'d.m.Y'
		},
		{
			text: langs('Код'),
			dataIndex: 'Drug_Code',
			flex: 1,
			minWidth: 100
		},{
			text: langs('Количество'),
			dataIndex: 'EvnDrug_Kolvo',
			flex: 1,
			minWidth: 100
		},{
			text: langs('Наименование'),
			dataIndex: 'Drug_Name',
			flex: 1,
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
		model: 'usluga.components.models.EvnDrugModel',
		sorters: [
			'EvnDrug_id'
		]
	}
});
