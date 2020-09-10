sw.Promed.swSmpIllegalActWindow = Ext.extend(sw.Promed.BaseForm, {
	//id: 'swSmpIllegalActWindow',
	title: langs('Регистр случаев противоправных действий в отношении персонала СМП'),
	maximized: true,
	maximizable: true,
	modal: false,
	shim: false,
	plain: true,
	closable: true,
	callback: Ext.emptyFn,
	onDoCancel: Ext.emptyFn,
	listeners: {
		hide: function() {
			this.GridPanel.ViewGridPanel.getStore().removeAll();
		}
	},
	onCancel: function() {
		this.onDoCancel();
		this.hide();
	},
	
	initComponent: function() {
		
		this.GridPanel = new sw.Promed.ViewFrame(
		{
			paging: true,
			region: 'center',
			dataUrl: '/?c=CmpCallCard&m=loadCmpIllegalActList',
			toolbar: true,
			autoExpandColumn: 'autoexpand',
			//оказалось без ид никуда - чудеса
			id: this.id + 'CmpIllegalActGrid',
			autoExpandMin: 100,
			pageSize: 25,
			totalProperty: 'totalCount',
			autoLoadData: true,
			//object: 'CmpIllegalAct',
			stringfields: [
				{name: 'CmpIllegalAct_id', type: 'int', header: 'ID', key: true},
				{name: 'Lpu_Nick', header: langs('МО регистрации случая'), width: 250},
				{name: 'CmpIllegalAct_prmDT', type: 'date', format: 'd.m.Y', header: langs('Дата регистрации случая'), width: 150},
				{name: 'Person_FIO', header: langs('Пациент'), width: 250},
				{name: 'Person_BirthDay', type: 'date', header: langs('Дата рождения'), width: 100},
				{name: 'Address_Name', header: langs('Адрес вызова'), width: 300},
				{name: 'CmpCallCard_id', header: langs('Вызов'), hidden: true},
				{name: 'CmpIllegalAct_Comment', header: langs('Комментарий'), width: 250}
			],
			actions:
			[
				{name: 'action_add', iconCls: 'add16', text: langs('Добавить'), tooltip: langs('Добавить случай'), handler: this.openIllegalActEditWindow.createDelegate(this, ['add'])},
				{name: 'action_edit', iconCls: 'edit16', text: langs('Изменить'), tooltip: langs('Изменить случай'), handler: this.openIllegalActEditWindow.createDelegate(this, ['edit'])},
				{name: 'action_view', iconCls: 'edit16', text: langs('Просмотр'), tooltip: langs('Просмотреть случай'), handler: this.openIllegalActEditWindow.createDelegate(this, ['view'])},
				{name: 'action_delete', iconCls: 'delete16', text: langs('Удалить'), tooltip: langs('Удалить случай'), handler: this.deleteIllegalAct.createDelegate(this)}
			]
		});

		Ext.apply(this, {
			buttonAlign: 'right',
			layout: 'border',
			buttons: [
			{
				text: BTN_FRMHELP,
				iconCls: 'help16',
				handler: function(button, event) {
					ShowHelp(this.ownerCt.title);
				}
			},
			{
				text: langs('Закрыть'),
				iconCls: 'close16',
				handler: this.onCancel.createDelegate(this)
			}],
			items: [this.GridPanel]
		});
		
		sw.Promed.swSmpIllegalActWindow.superclass.initComponent.apply(this, arguments);
	},

	show: function() {
        sw.Promed.swSmpIllegalActWindow.superclass.show.apply(this, arguments);

		this.GridPanel.ViewGridPanel.getStore().reload();
	},

	openIllegalActEditWindow: function(action) {

		var me = this,
			selected_record = me.GridPanel.getGrid().getSelectionModel().getSelected();

		if (getWnd('swSmpIllegalActEditWindow').isVisible()) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: Ext.emptyFn,
				icon: Ext.Msg.WARNING,
				msg: langs('Окно уже открыто'),
				title: ERR_WND_TIT
			});
			return false;
		}

		getWnd('swSmpIllegalActEditWindow').show({
			action: action,
			record: selected_record ? selected_record.data : null,
			onSaveForm: function(win){
				win.hide();
				me.GridPanel.ViewGridPanel.getStore().reload();
			}
		});
	},

	deleteIllegalAct: function(){

		var selected_record = this.GridPanel.getGrid().getSelectionModel().getSelected(),
			me = this;

		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			msg: langs('Удалить запись?'),
			title: langs('Удаление записи'),
			fn: function (buttonId) {

				if (buttonId != 'yes') {
					return false;
				};

				Ext.Ajax.request({
					url: '/?c=CmpCallCard&m=deleteCmpIllegalAct',
					params: {
						CmpIllegalAct_id: selected_record.get('CmpIllegalAct_id')
					},
					success: function (response){
						var formParams = Ext.util.JSON.decode(response.responseText);
						me.GridPanel.ViewGridPanel.getStore().reload();
					},
					failure: function () {
						sw.swMsg.alert(langs('Ошибка'), langs('Ошибка при удалении данных формы'), function () {});
					}

				});
			}
		});
	}
	
});
