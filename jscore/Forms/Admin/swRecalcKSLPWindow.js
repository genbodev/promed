/**
 * swRecalcKSLPWindow - пересчет КСЛП
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      	Admin
 * @access       	public
 * @copyright		Copyright (c) 2019 Swan Ltd.
 * @author
 * @version			04.05.2019
 */
sw.Promed.swRecalcKSLPWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'swRecalcKSLPWindow',
	title: "Переопределение КСЛП",
	width: 800,
	autoHeight: true,
	modal: true,
	_timer: null,
	_timerCheckStop: null,
	enableFields: function(stopped) {
		var base_form = this.filtersPanel.getForm();
		if(stopped) {//процесс остановлен или завершился
			clearInterval(this._timer);
			base_form.findField('EvnDateRange').enable();
			base_form.findField('Lpu_id').enable();
			base_form.findField('PaidStatus').enable();
			this.buttons[0].enable();
			this.buttons[1].disable();
		} else { //процесс запущен, поля недоступны
			base_form.findField('EvnDateRange').disable();
			base_form.findField('Lpu_id').disable();
			base_form.findField('PaidStatus').disable();
			this.buttons[0].disable();
			this.buttons[1].enable();
		}
	},
	startTimer: function() {
		var win = this;
		if(!win._timer) {
			win.enableFields(false);
			win._timer = setInterval(function(){
				Ext.Ajax.request({
					url: '/?c=EvnSection&m=RecalcKSLPstatus',
					async: true,
					params: null,
					callback: function (options, success, response) {
						if (success) {
							if(response.responseText) {
								var result = Ext.util.JSON.decode(response.responseText);
								
								if(result.progress==result.max) {
									clearInterval(win._timer);
									win._timer = null;
									Ext.getCmp('recalcKSLPprogress').setText('');
									win.enableFields(true);
									sw.swMsg.show({
										title: langs('Переопределение КСЛП'),
										msg: langs('Переопределение КСЛП завершено. '),
										buttons: Ext.Msg.OK
									});
									return;
								}
								if(result.stop==1) {
									Ext.getCmp('recalcKSLPprogress').setText('');
									log('Остановлено ('+result.progress+' из '+result.max+' движений)');
									win.enableFields(true);
									clearInterval(win._timer);
									win._timer = null;
								} else
								if(result.stop==0) {
									Ext.getCmp('recalcKSLPprogress').setText(langs('Обработано: ')+result.progress+langs(' из ')+result.max+' '+langs('движений'));
								}
							}
						}
					}
				});
			}, 5000);
		}
	},
	doRecalcKSLP: function() {
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
		Ext.getCmp('recalcKSLPprogress').setText(langs('Запуск')+'...');

		Ext.Ajax.request({
			url: '/?c=EvnSection&m=RecalcKSLP',
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
							Ext.getCmp('recalcKSLPprogress').setText('');
							return false;
						}
						if(result.complete) return;
						
						if(result.in_progress == 1) {
							Ext.getCmp('recalcKSLPprogress').setText('');
							sw.swMsg.show(
								{
									icon: Ext.Msg.WARNING,
									msg: langs('Процесс переопределения КСЛП уже запущен, или предыдущий процесс был завершен некорректно.<br />Выполнить?'),
									title: langs('Переопределение КСЛП'),
									buttons: Ext.Msg.YESNO,
									fn: function(buttonId, text, obj)
									{
										if ('yes' == buttonId)
										{
											win.FilterParams.proceed = 1;
											Ext.Ajax.request({
												url: '/?c=EvnSection&m=RecalcKSLP',
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
																Ext.getCmp('recalcKSLPprogress').setText(result.Error_Msg);
																return false;
															}
															if (result.complete) return;
															if (result.count == 0)
																Ext.Msg.alert(langs('Переопределение КСЛП'), langs('Движений по выбранным критериям не найдено.'));
														}
													}
												}
											});
											win.startTimer();
										} else {
											Ext.getCmp('recalcKSLPprogress').setText('');
											win.enableFields(true);
										}
									}
								});
						} else
						if (result.count == 0)
							Ext.Msg.alert(langs('Переопределение КСЛП'), langs('Движений по выбранным критериям не найдено.<br/>'));
					}
				}
			}
		});
	},
	doStop: function() {
		var win = this;
		Ext.Ajax.request({
			url: '/?c=EvnSection&m=RecalcKSLPstop',
			params: null,
			async: true
		});
		clearInterval(win._timer);
		win._timer = null;
		Ext.getCmp('recalcKSLPprogress').setText('');

		win._timerCheckStop = setTimeout(function() {
			Ext.Ajax.request({
				url: '/?c=EvnSection&m=RecalcKSLPstatus',
				async: true,
				params: null,
				callback: function (options, success, response) {
					if (success) {
						if(response.responseText) {
							var result = Ext.util.JSON.decode(response.responseText);
							if(result.stop==1) {
								log('Остановлено пользователем: успешно.');
								win.enableFields(true);
								//clearInterval(win._timerCheckStop);
							} else {
								log('Действие отмены завершилось с ошибкой');
								sw.swMsg.alert(langs('Ошибка'), langs('Действие отмены завершилось с ошибкой'));
							}
						}
					}
					Ext.getCmp('recalcKSLPprogress').setText('');
					win.enableFields(true);
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
				allowBlank: false,
				fieldLabel: langs('Диапазон дат выписки'),
				name: 'EvnDateRange',
				plugins: [new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
				width: 180,
				xtype: 'daterangefield'
			},
			new Ext.ux.Andrie.Select({
				disabled: false,
				allowBlank: true,
				multiSelect: true,
				mode: 'local',
				fieldLabel: 'МО',
				hiddenName: 'Lpu_id',
				displayField: 'Lpu_Nick',
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
					'{[!Ext.isEmpty(values.Lpu_EndDate) ? "<font color=#777>"+values.Lpu_Name + " (закрыто "+ (typeof values.Lpu_EndDate == "object" ? Ext.util.Format.date(values.Lpu_EndDate, "d.m.Y") : values.Lpu_EndDate) + ")</font>" : values.Lpu_Nick ]}&nbsp;'+
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
				allowBlank: true,
				fieldLabel: langs('Статус оплаты'),
				hiddenName: 'PaidStatus',
				hideEmptyRow: true,
				store: [
					[0, langs('Все')],
					[1, langs('Неоплаченные')],
					[2, langs('Оплаченные')]
				],
				triggerAction: 'all',
				value: 0,
				width: 180
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
					id: 'recalcKSLPprogress',
					name: 'recalcKSLPprogress',
					hiddenName: 'recalcKSLPprogress',
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
					_this.doRecalcKSLP();
				},
				xtype: 'button'
			}, {
				text: "Отмена",
				handler: function () {
					_this.doStop();
				}
			}, {
				text: '-'
			},
			HelpButton(this),
			{
				iconCls: 'close16',
				handler: function() {
					_this.hide();
				},
				text: BTN_FRMCLOSE
			}]
		});

		sw.Promed.swRecalcKSLPWindow.superclass.initComponent.apply(this, arguments);
	},
	show: function() {
		sw.Promed.swRecalcKSLPWindow.superclass.show.apply(this, arguments);

		var win= this;

		win.enableFields(true);

		Ext.Ajax.request({
			url: '/?c=EvnSection&m=RecalcKSLPstatus',
			async: true,
			params: null,
			callback: function (options, success, response) {
				if (success) {
					if(response.responseText) {
						var result = Ext.util.JSON.decode(response.responseText);

						if (result.stop === 0 && result.max !== 0) {
							win._timer = null;
							win.startTimer();
						}
					}
				}
			}
		});
	}
});