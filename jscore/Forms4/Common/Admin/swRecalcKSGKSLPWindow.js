/**
 * swRecalcKSGKSLPWindow - Переопределение КСГ/КСЛП
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common.Admin
 * @access       public
 * @copyright    Copyright (c) 2018 Swan Ltd.
 */
Ext6.define('common.Admin.swRecalcKSGKSLPWindow', {
	alias: 'widget.swRecalcKSGKSLPWindow',
	autoShow: false,
	closable: true,
	cls: 'arm-window-new',
	constrain: true,
	extend: 'base.BaseForm',
	findWindow: false,
	header: true,
	modal: true,
	layout: 'form',
	resizable: false,
	title: 'Переопределение КСГ/КСЛП',
	width: 600,
	show: function() {
		this.callParent(arguments);

		var win = this;

		win.center();
		win.enableFields(true);

		var base_form = win.FormPanel.getForm();
		base_form.reset();
	},
	enableFields: function(stopped) {
		var base_form = this.FormPanel.getForm();
		if (stopped) {//процесс остановлен или завершился
			clearInterval(this._timer);
			base_form.findField('RecalcType').enable();
			base_form.findField('EvnDateRange').enable();
			base_form.findField('Lpu_id').enable();
			base_form.findField('StType').enable();
			base_form.findField('PaidStatus').enable();
			this.recalcButton.enable();
			this.stopButton.disable();
		} else { //процесс запущен, поля недоступны
			base_form.findField('RecalcType').disable();
			base_form.findField('EvnDateRange').disable();
			base_form.findField('Lpu_id').disable();
			base_form.findField('StType').disable();
			base_form.findField('PaidStatus').disable();
			this.recalcButton.disable();
			this.stopButton.enable();
		}
	},
	startTimer: function() {
		var win = this;
		if (!win._timer) {
			win.enableFields(false);
			win._timer = setInterval(function() {
				Ext6.Ajax.request({
					url: win.recalcUrl + 'status',
					async: true,
					params: null,
					callback: function(options, success, response) {
						if (success) {
							if (response.responseText) {
								var result = Ext6.JSON.decode(response.responseText);

								if (result.progress == result.max) {
									clearInterval(win._timer);
									win._timer = null;
									win.progressPanel.setText('');
									win.enableFields(true);
									Ext6.Msg.show({
										title: langs('Переопределение'),
										msg: langs('Переопределение завершено. '),
										buttons: Ext6.Msg.OK
									});
									return;
								}
								if (result.stop == 1) {
									win.progressPanel.setText('');
									log('Остановлено (' + result.progress + ' из ' + result.max + ' движений)');
									win.enableFields(true);
									clearInterval(win._timer);
									win._timer = null;
								} else if (result.stop == 0) {
									win.progressPanel.setText(langs('Обработано: ') + result.progress + langs(' из ') + result.max + ' ' + langs('движений'));
								}
							}
						}
					}
				});
			}, 5000);
		}
	},
	doRecalc: function() {
		var win = this;
		var base_form = this.FormPanel.getForm();
		if (!base_form.isValid()) {
			Ext6.MessageBox.show({
				title: 'Проверка данных формы',
				msg: 'Не все поля формы заполнены корректно, проверьте введенные вами данные. Некорректно заполненные поля выделены особо.',
				buttons: Ext6.Msg.OK,
				icon: Ext6.Msg.WARNING
			});
			return;
		}
		win.FilterParams = base_form.getValues();
		win.FilterParams.proceed = 0;

		win.startTimer();
		win.progressPanel.setText(langs('Запуск') + '...');

		if (base_form.findField('RecalcType').getValue() == 2) {
			win.recalcUrl = '/?c=EvnSection&m=RecalcKSLP';
		} else {
			win.recalcUrl = '/?c=EvnSection&m=RecalcKSG';
		}

		Ext6.Ajax.request({
			url: win.recalcUrl,
			method: 'POST',
			async: true,
			params: win.FilterParams,
			timeout: 20000, //ждем и читаем ответ только в случае если php-скрипт может быстро ответить.
							//во всем остальном слушаем что скажет win._timer
			callback: function(options, success, response) {
				if (success) {
					if (response.responseText) {
						var result = Ext6.JSON.decode(response.responseText);

						if (!Ext6.isEmpty(result.Error_Msg)) {
							Ext6.Msg.alert(langs('Ошибка'), result.Error_Msg);
							clearInterval(win._timer);
							win._timer = null;
							win.progressPanel.setText('');
							return false;
						}
						if (result.complete) return;

						if (result.in_progress == 1) {
							win.progressPanel.setText('');
							Ext6.Msg.show(
								{
									icon: Ext6.Msg.WARNING,
									msg: langs('Процесс переопределения уже запущен, или предыдущий процесс был завершен некорректно.<br />Выполнить?'),
									title: langs('Переопределение'),
									buttons: Ext6.Msg.YESNO,
									fn: function(buttonId, text, obj) {
										if ('yes' == buttonId) {
											win.FilterParams.proceed = 1;
											Ext6.Ajax.request({
												url: win.recalcUrl,
												method: 'POST',
												async: true,
												params: win.FilterParams,
												timeout: 20000,
												callback: function(options, success, response) {
													if (success) {
														if (response.responseText) {
															var result = Ext6.JSON.decode(response.responseText);
															if (!Ext6.isEmpty(result.Error_Msg)) {
																Ext6.Msg.alert(langs('Ошибка'), result.Error_Msg);
																clearInterval(win._timer);
																win._timer = null;
																win.progressPanel.setText(result.Error_Msg);
																return false;
															}
															if (result.complete) return;
															if (result.count == 0)
																Ext6.Msg.alert(langs('Переопределение'), langs('Движений по выбранным критериям не найдено.'));
														}
													}
												}
											});
											win.startTimer();
										} else {
											win.progressPanel.setText('');
											win.enableFields(true);
										}
									}
								});
						} else if (result.count == 0)
							Ext6.Msg.alert(langs('Переопределение'), langs('Движений по выбранным критериям не найдено.<br/>'));
					}
				}
			}
		});
	},
	doStop: function() {
		var win = this;
		Ext6.Ajax.request({
			url: win.recalcUrl + 'stop',
			params: null,
			async: true
		});
		clearInterval(win._timer);
		win._timer = null;
		win.progressPanel.setText('');

		win._timerCheckStop = setTimeout(function() {
			Ext6.Ajax.request({
				url: win.recalcUrl + 'status',
				async: true,
				params: null,
				callback: function(options, success, response) {
					if (success) {
						if (response.responseText) {
							var result = Ext6.JSON.decode(response.responseText);
							if (result.stop == 1) {
								log('Остановлено пользователем: успешно.');
								win.enableFields(true);
								//clearInterval(win._timerCheckStop);
							} else {
								log('Действие отмены завершилось с ошибкой');
								Ext6.Msg.alert(langs('Ошибка'), langs('Действие отмены завершилось с ошибкой'));
							}
						}
					}
					win.progressPanel.setText('');
					win.enableFields(true);
				}
			});
		}, 5000);
	},
	initComponent: function() {
		var win = this;

		win.FormPanel = Ext6.create('Ext6.form.FormPanel', {
			autoScroll: true,
			border: false,
			bodyStyle: 'padding: 5px;',
			defaults: {
				labelAlign: 'right',
				labelWidth: 150
			},
			items: [{
					allowBlank: false,
					editable: false,
					name: 'RecalcType',
					fieldLabel: langs('Вид переопределения'),
					store: [
						[1, langs('Переопредление КСГ')],
						[2, langs('Переопредление КСЛП')]
					],
					triggerAction: 'all',
					value: 1,
					width: 500,
					xtype: 'combo'
				},
				Ext6.create('Ext6.date.RangeField', {
					allowBlank: false,
					fieldLabel: langs('Диапазон дат выписки'),
					name: 'EvnDateRange',
					width: 500
				}), {
					fieldLabel: 'МО',
					xtype: 'swTagLpu',
					name: 'Lpu_id',
					width: 500
				}, {
					allowBlank: false,
					editable: false,
					name: 'StType',
					fieldLabel: langs('Вид стационара'),
					store: [
						[0, langs('Все')],
						[1, langs('Круглосуточный')],
						[2, langs('Дневной')]
					],
					triggerAction: 'all',
					value: 0,
					width: 500,
					xtype: 'combo'
				}, {
					allowBlank: false,
					editable: false,
					name: 'PaidStatus',
					fieldLabel: langs('Статус оплаты'),
					store: [
						[0, langs('Все')],
						[1, langs('Неоплаченные')],
						[2, langs('Оплаченные')]
					],
					triggerAction: 'all',
					value: 0,
					width: 500,
					xtype: 'combo'
				},
				win.progressPanel = Ext6.create('Ext6.form.Label', {
					style: 'margin-left: 7px;',
					text: ''
				})
			]
		});

		Ext6.apply(win, {
			items: [
				win.FormPanel
			],
			buttons: ['->', win.stopButton = Ext6.create('Ext6.button.Button', {
				handler: function() {
					win.doStop();
				},
				cls: 'flat-button-secondary',
				text: 'Отмена'
			}), win.recalcButton = Ext6.create('Ext6.button.Button', {
				handler: function() {
					win.doRecalc();
				},
				cls: 'flat-button-primary',
				text: 'Выполнить'
			})]
		});

		this.callParent(arguments);
	}
});