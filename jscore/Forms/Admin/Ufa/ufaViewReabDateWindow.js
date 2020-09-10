/**
 * ufaViewReabDateWindow - окно предосмотра дат анкетирования и заполнения шкал по пациенту в регистре Реабилитации
 * 
 */

sw.Promed.ufaViewReabDateWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'ufaViewReabDateWindow',
	MorbusType_id: false,
	modal: true,
	title: 'Окно предосмотра предметов наблюдения',
	height: 'auto',
	width: 'auto',
	closable: false,
	closeAction: 'hide',
	buttonAlign: 'right',
	bodyStyle: 'padding:0px;border:0px;',
	lastSelectedMorbusType_id: false,
	initComponent: function () {
		this.ViewReabDate = new sw.Promed.ViewFrame(
				{
					toolbar: false,
					id: 'ViewReabDate_id',
					autoLoadData: false,
					contextmenu: false,
					dataUrl: '/?c=ufa_Reab_Register_User&m=getListScalesDirectCurrentUser',
					width: '410',
					stringfields:
							[
								{name: 'Id', type: 'int', header: 'ID', key: true},
								//{name: 'ReabAnketa_Data', type: 'date', format: 'd.m.Y', header: lang['data'], width: 90, align: 'center'},
								{name: 'ReabAnketa_Data', type: 'string', header: lang['data'], width: 90, align: 'center'},
								{name: 'ReabPotent', type: 'string', header: 'Реабилитационный<br>потенциал', align: 'center', width: 150},
								{name: 'StageTypeSysNick', type: 'string', header: 'Этап', align: 'center', width: 50},
								{name: 'ReabOutCauseName', type: 'string', header: 'Причина завершения', align: 'center', width: 150}
							],
					focusOnFirstLoad: false,
					onDblClick: function () {
						//alert('pppppp');
						return;
					},
				});

		// Событие - клик по меню щкал
//		this.ViewReabDate.getGrid().on(
//				'rowdblclick',
//				function (grid, row) {
//					var record = grid.store.data.items[row].data;
//					Ext.getCmp('ufaViewReabDateWindow').callback1(record);
//				}
//		);

		Ext.apply(this,
				{
					items: [this.ViewReabDate],
					buttons:
							[
								{
									text: 'Закрыть',
									id: 'close',
									handler: function () {
										Ext.getCmp('ufaViewReabDateWindow').refresh();
									}
								}
							]
				}
		);
		sw.Promed.ufaViewReabDateWindow.superclass.initComponent.apply(this, arguments);
	},
	refresh: function ()
	{
		sw.codeInfo.lastObjectName = this.objectName;
		sw.codeInfo.lastObjectClass = this.objectClass;
		if (sw.Promed.Actions.loadLastObjectCode)
		{
			sw.Promed.Actions.loadLastObjectCode.setHidden(false);
			sw.Promed.Actions.loadLastObjectCode.setText('Обновить ' + this.objectName + ' ...');
		}
		// Удаляем полностью объект из DOM, функционал которого хотим обновить
		this.hide();
		this.close();
		window[this.objectName] = null;
		delete sw.Promed[this.objectName];

	},
	show: function (params) {
		Ext.getCmp('ufaViewReabDateWindow').setTitle(params.Inparams.Title);

		//Заполним GRID
		Ext.getCmp('ViewReabDate_id').getGrid().getStore().load({
			params: {
				Person_id: params.Inparams.Person_id,
				DirectType_id: params.Inparams.DirectType_id
			},
			callback: function (success) {
				console.log('success11=', success);
			}
		});

		var nRecord = Ext.getCmp('ViewReabDate_id').getGrid().getStore().data.items.length;
		if (nRecord > 0)
		{
			Ext.getCmp('ViewReabDate_id').getGrid().store.removeAll( );
		}
//		for (jj = 0; jj < params.Inparams.HeadAnketa.length; jj++)
//		{
//			//  var nRecord = Ext.getCmp('ViewReabDate_id').getGrid().getStore().data.items.length;
//			//Обработка даты
//			//console.log('cDate=', params.Inparams.HeadAnketa[jj].ReabAnketa_Data.date);
//
//			Ext.getCmp('ViewReabDate_id').getGrid().store.insert(jj, [new Ext.data.Record({
//					Id: nRecord + 1,
//					ReabAnketa_Potent: params.Inparams.HeadAnketa[jj].ReabPotent,
//					ReabAnketa_Data: params.Inparams.HeadAnketa[jj].ReabAnketa_Data.date,
//				})]);
//		}
		//Переопределение метода
		if (arguments[0].callback1)
		{
			this.callback1 = arguments[0].callback1;
			//console.log('раз');  
		} else
		{
			this.callback1 = Ext.emptyFn;
		}


		sw.Promed.ufaViewReabDateWindow.superclass.show.apply(this, arguments);
	},

	callback1: function (params) {
//      alert('pppppp');
//      console.log('callback1=',params);
	},
	listeners: {
		'hide': function () {
			if (this.refresh)
				this.onHide();
		},
		'close': function () {
			if (this.refresh)
				this.onHide();
		}
	}
});

