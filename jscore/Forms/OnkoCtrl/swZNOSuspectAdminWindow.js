/* 
 * swZNOSuspectAdminWindow - окно для запуска заданий по актуализации данных в регистре
 */

sw.Promed.swZNOSuspectAdminWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'swZNOSuspectAdminWindow',
	MorbusType_id: false,
	modal: true,
	title: 'Администрирование ЗНО',
//	height: 'auto',
//	width: 'auto',
width: 800,
	height: 550,
	closable: false,
	closeAction: 'hide',
	buttonAlign: 'right',
	bodyStyle: 'padding:0px;border:0px;',
	//lastSelectedMorbusType_id: false,
	initComponent: function () {
		
		this.AdminZNOSuspect = new sw.Promed.ViewFrame(
				{
					//toolbar: false,
					actions: [
						{name: 'action_add', hidden: true},
						{name: 'action_view', hidden: true},
						{name: 'action_edit', hidden: true},
						{name: 'action_delete', hidden: true},
						{name: 'action_refresh', hidden: false, handler: function () {
								Ext.getCmp('ZNOSuspectAdmin').adminRefresh();
							}.createDelegate(this)},
						{name: 'action_print', hidden: true}
					],
					id: 'ZNOSuspectAdmin',
					autoLoadData: false,
					contextmenu: false,
					autoExpandColumn: 'autoexpand',
					dataUrl: '/?c=ZnoSuspectRegister_User&m=getListZNOSuspectAdmin',
					//width: '410',
					height: 320,
					stringfields:
							[
								{name: 'vID', type: 'int', header: 'ID', key: true},
								{name: 'ZNOSuspectAdmin_setDT', type: 'datetime', dateFormat: 'd.m.Y H:i', header: lang['data_i_vremya_provedeniya'],align: 'center', vertical: 'middle', width: 150},
								{name: 'ZNOSuspectAdmin_Name', type: 'string', header: 'Наименование процедуры', width: 250, align: 'center'},
								{name: 'ZNOSuspectAdmin_disDT', type: 'datetime', dateFormat: 'd.m.Y H:i', header: 'Время завершения',align: 'center', vertical: 'middle', width: 150},
								{name: 'ZNOSuspectAdmin_ErrMessage', type: 'string', header: 'Сообщение об ошибке', align: 'center', width: 450},
								{name: 'ZNOSuspectAdmin_ErrCode', type: 'int', header: 'Код ошибки', align: 'center', width: 60, id: 'autoexpand'}
							],
					focusOnFirstLoad: false,
					totalProperty: 'totalCount',
					paging: false, // навигатор
					onDblClick: function () {
						//alert('pppppp');
						return;
					},
					onRowSelect: function (sm, index, record) {

					},
					adminRefresh: function () {
						Ext.getCmp('ZNOSuspectAdminTask').show();
						Ext.getCmp('ZNOSuspectAdmin').getGrid().getStore().load(
								{
									callback: function (success) {
										if (success.length > 0)
										{
											Ext.getCmp('ZNOSuspectAdmin').getGrid().getSelectionModel().deselectRow(Ext.getCmp('ZNOSuspectAdmin').getGrid().getStore().data.items.length - 1);
										}
									}
								}
						);
					}
				});
				
		
		Ext.apply(this,
				{
					items: [
						{
							xtype: 'panel',
							layout: 'form',
							hidden: false,
							id: 'ZNOSuspectAdminTask',
							border: true,
							frame: true, //Параметры для запуска процедуры
							height: 140,
							layout: 'form',
							items: [
								new Ext.form.Label({
									text: 'Параметры для запуска процедуры',
									height: 10,
									style: 'font-style:italic;font-size:1.2em;color:blue; '
								}),
								{
									xtype: 'panel',
									frame: false,
									border: false,
									layout: 'column',
									items: [
										{
											layout: 'form',
											border: false,
											labelWidth: 70,
											items: [
												new sw.Promed.SwDateField(
														{
															fieldLabel: 'Период с',
															id: 'ZNOSuspectAdminSetDate1',
															disabled: false,
															enableKeyEvents: true,
															width: 100,
															plugins: [
																new Ext.ux.InputTextMask('99.99.9999', false)
															],
															xtype: 'swdatefield',
															format: 'd.m.Y',
															value: new Date(),
															maxValue:new Date()

														})
											]
										},
										{
											layout: 'form',
											border: false,
											labelWidth: 70,
											labelAlign : "right",
											items: [
												new sw.Promed.SwDateField(
														{
															fieldLabel: 'по ',
															id: 'ZNOSuspectAdminSetDate2',
															disabled: false,
															enableKeyEvents: true,
															plugins: [
																new Ext.ux.InputTextMask('99.99.9999', false)
															],
															xtype: 'swdatefield',
															format: 'd.m.Y',
															width: 100,
															value: new Date(),
															maxValue:new Date()

														})
											]
										}
										
										
										
 
									]
								},
								{
									layout: 'form',
									border: false,
									labelWidth: 100,
									labelAlign: 'left',
									items: [
										new Ext.form.NumberField({
											allowBlank: true,
											disabled: false,
											style: 'font-style:normal;font-size:1.1em;color:black;text-align: center;font-width: bold',
											labelStyle: 'font-size:1.1em;',
											fieldLabel: 'Person_id',
											id: 'ZNOSuspectAdminPerson_id',
											width: 120
										})
									]
								},
							],
							buttons:
									[
										{
											handler: function () {
												Ext.getCmp('swZNOSuspectAdminWindow').resetData();
											}.createDelegate(this),
											iconCls: 'resetsearch16',
											tabIndex: TABINDEX_ORW + 121,
											text: BTN_FRMRESET
										},
										{
											text: lang['vyipolnit'],
											//id: 'close',
											handler: function () {
												Ext.getCmp('swZNOSuspectAdminWindow').madeProcess();
											}
										},
										{
											text: 'Закрыть',
											handler: function () {
												Ext.getCmp('swZNOSuspectAdminWindow').closePanel();
											}
										}
									]
						},
						{
							xtype: 'panel', // Grid заданий
							layout: 'form',
							border: false,
							frame: true,
							width: 'auto',
							height: 350,
							autoScroll: true,
							items: [
								new Ext.form.Label({
									text: 'Выполненные процедуры обновления регистра',
									height: 10,
									style: 'font-style:italic;font-size:1.2em;color:blue; '
								}),
								this.AdminZNOSuspect
							]
						}
						//this.ViewReabDate
					],
					buttons:
							[
								{
									text: 'Закрыть',
									id: 'close',
									handler: function () {
										Ext.getCmp('swZNOSuspectAdminWindow').refresh();
									}
								}
							]
				}
		);
		sw.Promed.swZNOSuspectAdminWindow.superclass.initComponent.apply(this, arguments);
	},
	
	madeProcess: function () {
		
		if(Ext.getCmp('ZNOSuspectAdminPerson_id').getValue() != "")
		{
			//Пока нет процедуры
			Ext.Msg.alert(lang['soobschenie'], 'Пока нет процедуры');
			//alert('Пока нет процедуры');
			return;
		}
		//Работа с датами
		if (Ext.getCmp('ZNOSuspectAdminSetDate1').getValue() != "")
		{
			if (Ext.getCmp('ZNOSuspectAdminSetDate1').isValid() == false)
			{
				Ext.Msg.alert(lang['oshibka'], 'Не верно указана дата начала');
				return;
			}

			//работа с датой и временем
//			var rr = Ext.getCmp('ZNOSuspectAdminSetDate1').getValue();
//			rr.setHours(parseInt('00'));
//			rr.setMinutes(parseInt('00'));
//			console.log('rr=',rr);
			//Запуск p_ZNOSuspectAdmin_1 с анализом 
			
		}
		if (Ext.getCmp('ZNOSuspectAdminSetDate2').getValue() != "")
		{
			if (Ext.getCmp('ZNOSuspectAdminSetDate2').isValid() == false)
			{
				Ext.Msg.alert(lang['oshibka'], 'Не верно указана конечная дата');
				return;
			}
		}
		var loadMask = new Ext.LoadMask(this.getEl(), {msg: lang['podojdite_idet_formirovanie']});
		loadMask.show();
		Ext.Ajax.request({
				url: '?c=ZnoSuspectRegister_User&m=made_p_ZNOSuspectAdmin',
				params: {
					Person_id: Ext.getCmp('ZNOSuspectAdminPerson_id').getValue(),
					SetDate1: Ext.getCmp('ZNOSuspectAdminSetDate1').getValue(),
					SetDate2: Ext.getCmp('ZNOSuspectAdminSetDate2').getValue()
				},
				callback: function (options, success, response)
				{
//					 console.log('success=',success); 
//					 console.log('response=',response); 
					 
					loadMask.hide(); // Обязательно сделать !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
					if (success == true)
					{
						var response_obj = Ext.util.JSON.decode(response.responseText);
						if (response_obj.success == true)
						{
							//Перерисовка GRIDa
							Ext.getCmp('ZNOSuspectAdmin').adminRefresh();
						}
					} else {
						sw.swMsg.alert(lang['soobschenie'], lang['oshibka_pri_vyipolnenii_zaprosa_k_serveru']);
					}
				}
			});
			return;
	},
	//Сброс входных параметров для процедуры
	resetData: function () {
		Ext.getCmp('ZNOSuspectAdminSetDate1').setValue();
		Ext.getCmp('ZNOSuspectAdminSetDate2').setValue();
		Ext.getCmp('ZNOSuspectAdminPerson_id').setValue();
	},
	closePanel: function () {
		Ext.getCmp('ZNOSuspectAdminTask').hide();
	},
	show: function () {
		Ext.getCmp('ZNOSuspectAdminTask').hide();

		Ext.getCmp('ZNOSuspectAdmin').getGrid().getStore().load(
				{
					callback: function (success) {
						if (success.length > 0)
						{
							Ext.getCmp('ZNOSuspectAdmin').getGrid().getSelectionModel().deselectRow(Ext.getCmp('ZNOSuspectAdmin').getGrid().getStore().data.items.length - 1);
						}
					}
				}
		);

		sw.Promed.swZNOSuspectAdminWindow.superclass.show.apply(this, arguments);
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


