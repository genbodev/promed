Ext6.define('usluga.components.EvnMediaGrid', {
	extend: 'GreyAutoHideGridPanel',
	alias: 'widget.EvnMediaGrid',
	requires: ['usluga.components.models.EvnMediaModel'],
	padding: '20 20 0 20',

	plugins: {
		cellediting: {
			clicksToEdit: 1
		}
	},

	threeDotMenu: Ext6.create('Ext6.menu.Menu', {
		userCls: 'menuWithoutIcons',
		items: [{
			text: 'Удалить файл',
			handler: function(btn, e)
			{
				var rec = this.up('menu').selRecord;

				Ext6.Msg.show({
					title: 'Вопрос',
					msg: 'Вы действительно хотите удалить файл?',
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

				return;
			}
		}]
	}),

	columns: [{
		text: 'Файл',
		height: 30,
		flex: 1,
		minWidth: 100,
		dataIndex: 'EvnMediaData_Data',
		renderer: function (value, metaData, record) {
			return '<a href="' + record.get('EvnMediaData_FilePath') + '" target="blank">' + record.get('EvnMediaData_FileName') + "</a>";
		}
	},{
		text: 'Комментарий',
		flex: 1,
		tooltip: 'Добавьте комментарий к файлу',
		minWidth: 100,
		dataIndex: 'EvnMediaData_Comment',
		editor: 'textfield'
	},
		{
			xtype: 'actioncolumn',
			width: 35,
			sortable: false,
			iconCls: 'grid-header-icon-menuItem',
			tooltip: 'Меню',
			handler: 'onMenuClick',
			bind: {
				disabled: '{editable === false}'
			}
		}
	],
	disableSelection: true,
	store: {
		model: 'usluga.components.models.EvnMediaModel',
		sorters: [
			'EvnMediaData_id'
		]
	},
	// listeners: {
	// 	render: function ()
	// 	{
	// 		var grid = this;
	//
	// 		this.getStore().on('datachanged', function (store) {
	//
	// 			var vm = grid.getViewModel();
	//
	// 			if (vm)
	// 			{
	// 				vm.set('counter', store.getCount());
	// 			}
	//
	// 			return true;
	// 		});
	//
	// 		return;
	// 	}
	// }
});