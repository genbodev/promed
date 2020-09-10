/**
 * swRecalcKSGWindow -
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      	Admin
 * @access       	public
 * @copyright		Copyright (c) 2016 Swan Ltd.
 * @author
 * @version			28.02.2018
 */
/*NO PARSE JSON*/

sw.Promed.swRecalcKSGWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'swRecalcKSGWindow',
	title: "Переопределение КСГ",
	width: 800,
	autoHeight: true,
	modal: true,
	/*
	maximizable: true,
	maximized: true,
	layout: 'border',
	resizable: true,*/
	_timer: null,
	_timerCheckStop: null,
	enablesField: function(stopped) {
		var base_form = this.filtersPanel.getForm();
		if(stopped) {//процесс остановлен или завершился
			clearInterval(this._timer);
			base_form.findField('EvnDateRange').enable();
			base_form.findField('Lpu_id').enable();
			base_form.findField('StType').enable();
			this.buttons[0].enable();
			this.buttons[1].disable();
		} else { //процесс запущен, поля недоступны
			base_form.findField('EvnDateRange').disable();
			base_form.findField('EvnDateRange').disable();
			base_form.findField('Lpu_id').disable();
			base_form.findField('StType').disable();
			this.buttons[0].disable();
			this.buttons[1].enable();
		}
	},
	startTimer: function() {
		var win = this;
		if(!win._timer) {
			win.enablesField(false);
			win._timer = setInterval(function(){
				Ext.Ajax.request({
					url: '/?c=EvnSection&m=RecalcKSGstatus',
					async: true,
					params: null,
					callback: function (options, success, response) {
						if (success) {
							if(response.responseText) {
								var result = Ext.util.JSON.decode(response.responseText);
								
								if(result.progress==result.max) {
									clearInterval(win._timer);
									win._timer = null;
									Ext.getCmp('recalcKSGprogress').setText('');
									win.enablesField(true);
									sw.swMsg.show({
										title: langs('Переопределение КСГ'),
										msg: langs('Переопределение КСГ завершено. '),
										buttons: Ext.Msg.OK
									});
									return;
								}
								if(result.stop==1) {
									Ext.getCmp('recalcKSGprogress').setText('');
									log('Остановлено ('+result.progress+' из '+result.max+' движений)');
									win.enablesField(true);
									clearInterval(win._timer);
									win._timer = null;
								} else
								if(result.stop==0) {
									Ext.getCmp('recalcKSGprogress').setText(langs('Обработано: ')+result.progress+langs(' из ')+result.max+' '+langs('движений'));
								}
							}
						}
					}
				});
			}, 5000);
		}
	},
	doRecalcKSG: function() {
		var win = this;
		var base_form = this.filtersPanel.getForm();
		if ( !base_form.isValid() ) {
			Ext.MessageBox.show({
				title: 'Проверка данных формы',
				msg: 'Не все поля формы заполнены корректно, проверьте введенные вами данные. Некорректно заполненные поля выделены особо.',
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.WARNING
			});
			return;
		}
		win.FilterParams = getAllFormFieldValues(this.filtersPanel);
		win.FilterParams.proceed = 0;

		win.startTimer();
		Ext.getCmp('recalcKSGprogress').setText(langs('Запуск')+'...');

		Ext.Ajax.request({
			url: '/?c=EvnSection&m=RecalcKSG',
			method: 'POST',
			async: true,
			params: win.FilterParams,
			timeout: 20000, //ждем и читаем ответ только в случае если php-скрипт может быстро ответить.
							//во всем остальном слушаем что скажет win._timer
			callback: function (options, success, response) {
				if (success) {
					if (response.responseText) {
						var result = Ext.util.JSON.decode(response.responseText);
						
						if ( !Ext.isEmpty(result.Error_Msg) ) {
							sw.swMsg.alert(langs('Ошибка'), result.Error_Msg);
							clearInterval(win._timer);
							win._timer = null;
							Ext.getCmp('recalcKSGprogress').setText('');
							return false;
						}
						if(result.complete) return;
						
						if(result.in_progress == 1) {
							Ext.getCmp('recalcKSGprogress').setText('');
							sw.swMsg.show(
								{
									icon: Ext.Msg.WARNING,
									msg: langs('Процесс переопределения КСГ уже запущен, или предыдущий процесс был завершен некорректно.<br />Выполнить?'),
									title: langs('Переопределение КСГ'),
									buttons: Ext.Msg.YESNO,
									fn: function(buttonId, text, obj)
									{
										if ('yes' == buttonId)
										{
											win.FilterParams.proceed = 1;
											Ext.Ajax.request({
												url: '/?c=EvnSection&m=RecalcKSG',
												method: 'POST',
												async: true,
												params: win.FilterParams,
												timeout: 20000,
												callback: function (options, success, response) {
													if (success) {
														if (response.responseText) {
															var result = Ext.util.JSON.decode(response.responseText);
															if ( !Ext.isEmpty(result.Error_Msg) ) {
																sw.swMsg.alert(langs('Ошибка'), result.Error_Msg);
																clearInterval(win._timer);
																win._timer = null;
																Ext.getCmp('recalcKSGprogress').setText(result.Error_Msg);
																return false;
															}
															if (result.complete) return;
															if (result.count == 0)
																Ext.Msg.alert(langs('Переопределение КСГ'), langs('Движений по выбранным критериям не найдено.'));
														}
													}
												}
											});
											win.startTimer();
										} else {
											Ext.getCmp('recalcKSGprogress').setText('');
											win.enablesField(true);
										}
									}
								});
						} else
						if (result.count == 0)
							Ext.Msg.alert(langs('Переопределение КСГ'), langs('Движений по выбранным критериям не найдено.<br/>'));
					}
				}
			}
		});
	},
	doStop: function() {
		var win = this;
		Ext.Ajax.request({
			url: '/?c=EvnSection&m=RecalcKSGstop',
			params: null,
			async: true
		});
		clearInterval(win._timer);
		win._timer = null;
		Ext.getCmp('recalcKSGprogress').setText('');

		win._timerCheckStop = setTimeout(function() {
			Ext.Ajax.request({
				url: '/?c=EvnSection&m=RecalcKSGstatus',
				async: true,
				params: null,
				callback: function (options, success, response) {
					if (success) {
						if(response.responseText) {
							var result = Ext.util.JSON.decode(response.responseText);
							if(result.stop==1) {
								log('Остановлено пользователем: успешно.');
								win.enablesField(true);
								//clearInterval(win._timerCheckStop);
							} else {
								log('Действие отмены завершилось с ошибкой');
								sw.swMsg.alert(langs('Ошибка'), langs('Действие отмены завершилось с ошибкой'));
							}
						}
					}
					Ext.getCmp('recalcKSGprogress').setText('');
					win.enablesField(true);
				}
			});
		}, 5000);
	},
	initComponent: function()
	{
		var _this = this;

		this.filtersPanel = new Ext.form.FormPanel({
			layout: 'form',
			region: 'center',
			autoScroll: true,
			bodyBorder: false,
			labelAlign: 'left',
			labelWidth: 150,
			border: false,
			frame: true,
			items: [{
				disabled: false,
				allowBlank: false,
				id: 'recalcKSGdates',
				fieldLabel: langs('Диапазон дат выписки'),
				xtype: 'daterangefield',
				name: 'EvnDateRange',
				hiddenName: 'EvnDateRange',
				plugins: [new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
				//anchor: '100%'
				width: 300
			},
			new Ext.ux.Andrie.Select({
				disabled: false,
				allowBlank: true,
				id: 'recalcKSGselectorMO',
				multiSelect: true,
				mode: 'local',
				multiSelect: true,
				mode: 'local',
				allowBlank: true,
				fieldLabel: 'МО',
				hiddenName: 'Lpu_id',
				displayField: 'Lpu_Name',
				valueField: 'Lpu_id',
				anchor: '100%',
				store: new Ext.db.AdapterStore({
					autoLoad: false,
					dbFile: 'Promed.db',
					fields: [
						{name: 'Lpu_id', mapping: 'Lpu_id'},
						{name: 'Lpu_Name', mapping: 'Lpu_Name'},
						{name: 'Lpu_Nick', mapping: 'Lpu_Nick'},
						{name: 'Lpu_EndDate', mapping: 'Lpu_EndDate'}
					],
					key: 'Lpu_id',
					sortInfo: {field: 'Lpu_Name'},
					tableName: 'Lpu'
				}),
				tpl: '<tpl for="."><div class="x-combo-list-item">'+
				'{[!Ext.isEmpty(values.Lpu_EndDate) ? "<font color=#777>"+values.Lpu_Name + " (закрыто "+ (typeof values.Lpu_EndDate == "object" ? Ext.util.Format.date(values.Lpu_EndDate, "d.m.Y") : values.Lpu_EndDate) + ")</font>" : values.Lpu_Name ]}&nbsp;'+
				'</div></tpl>',
				initComponent:function(){
					this.triggerConfig = {
						tag:'span', cls:'x-form-twin-triggers', cn:[
							{tag: "img", src: Ext.BLANK_IMAGE_URL, cls: "x-form-trigger " + this.trigger1Class},
							{tag: "img", src: Ext.BLANK_IMAGE_URL, cls: "x-form-trigger " + this.trigger2Class}
						]
					};
					Ext.ux.Andrie.Select.superclass.initComponent.call(this);
					if (this.multiSelect){
						this.typeAhead = false;
						this.editable = false;
						this.triggerAction = 'all';
						this.selectOnFocus = false;
					}
					if (this.history){
						this.forceSelection = false;
					}
					if (this.value){
						this.setValue(this.value);
					}
					var SectionStore = this.getStore();

				}
			}),
			new Ext.form.ComboBox({
					id: 'recalcKSGst',
					hideEmptyRow: true,
					allowBlank: true,
					fieldLabel: langs('Вид стационара'),
					hiddenName: 'StType',
					disabled: false,
					width: 300,
					//anchor: '100%',
					triggerAction: 'all',
					value: 0,
					store: [
						[0, langs('значение не выбрано')],
						[1, langs('Круглосуточный')],
						[2, langs('Дневной')]
					]
			})]
		});

		this.formPanel = new Ext.Panel({
			region: 'center',
			labelAlign: 'right',
			labelWidth: 50,
			items: [
				this.filtersPanel,
				this.formProgress = new Ext.form.Label({
					style: 'margin-left: 7px;',
					id: 'recalcKSGprogress',
					name: 'recalcKSGprogress',
					hiddenName: 'recalcKSGprogress',
					text: ''
				})
			]
		});

		Ext.apply(this, {
			xtype: 'panel',
			items: [
				_this.formPanel
			],
			buttons: [{
				text: "Выполнить",
				handler: function () {
					_this.doRecalcKSG();
				},
				xtype: 'button'
			}, {
				text: "Отмена",
				disabled: true,
				handler: function () {
					_this.doStop();
				}
			}, {
				text: '-'
			},
			HelpButton(this, TABINDEX_RRLW + 13),
			{
				iconCls: 'close16',
				tabIndex: TABINDEX_RRLW + 14,
				handler: function() {
					_this.hide();
				},
				text: BTN_FRMCLOSE
			}]
		});

		sw.Promed.swRecalcKSGWindow.superclass.initComponent.apply(this, arguments);
	},
	show: function() {
		sw.Promed.swRecalcKSGWindow.superclass.show.apply(this, arguments);
	}
});