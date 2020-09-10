Ext6.define('usluga.components.OperBrigGrid', {
	extend: 'GreyAutoHideGridPanel',
	requires: ['usluga.components.models.OperBrigModel'],
	alias: 'widget.OperBrigGrid',

	padding: 20,

	threeDotMenu: Ext6.create('Ext6.menu.Menu', {
		items: [{
			text: 'Редактировать',
			iconCls: 'panicon-edit',
			handler: function(btn, e)
			{
				var controller = this.up('menu').controller;

				return controller ? controller.openEvnUslugaOperBrigEditWindow(btn, e) : false;
			}
		}, {
			text: 'Удалить запись',
			iconCls: 'panicon-delete',
			handler: function(btn, e)
			{
				var rec = this.up('menu').selRecord;


				Ext6.Msg.show({
					title: 'Вопрос',
					msg: 'Вы действительно хотите удалить врача?',
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
		text: langs('Специальность'),
		height: 30,
		flex: 2,
		minWidth: 100,
		dataIndex: 'SurgType_Name'
	},{
		text: langs('Код врача'),
		dataIndex: 'MedPersonal_Code',
		flex: 1,
		minWidth: 100
	}, {
		text: langs('ФИО врача'),
		dataIndex: 'MedPersonal_Fio',
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
		model: 'usluga.components.models.OperBrigModel',
		sorters: [
			'EvnUslugaOperBrig_id'
		]
	},

	listeners: {
		render: function ()
		{
			var grid = this;

			this.getStore().on('datachanged', function (store) {

				var vm = grid.getViewModel();

				if (vm)
				{
					vm.set('hasSurgeon', grid.brigadeHasSurgeon());
				}

				return;
			});

			return;
		}
	},

	brigadeHasSurgeon: function ()
	{
		var grid = this,
			idx = grid.getStore().findExact('SurgType_Code', 1);

		return idx !== -1;
	}
});
