/**
 * swEvnSectionKSGWindow - форма выбора КСГ для оплаты
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2013 Swan Ltd.
 * @author       Dmitry Vlasenko
 * @version      03.02.2019
 */

sw.Promed.swEvnSectionKSGWindow = Ext.extend(sw.Promed.BaseForm, {
	width: 600,
	height: 400,
	modal: true,
	resizable: false,
	autoHeight: false,
	plain: false,
	title: langs('Выбор КСГ для оплаты'),
	onCancel: function() {},
	show: function() {
		sw.Promed.swEvnSectionKSGWindow.superclass.show.apply(this, arguments);

		var win = this;

		if (!arguments[0] || !arguments[0].EvnSection_id) {
			sw.swMsg.alert(langs('Сообщение'), langs('Неверные параметры'), function() {
				win.hide();
			});
			return false;
		}

		this.EvnSection_id = arguments[0].EvnSection_id;

		if (arguments[0].callback) {
			this.callback = arguments[0].callback;
		} else {
			this.callback = Ext.emptyFn;
		}

		this.EvnSectionKSGGrid.loadData({
			globalFilters: {
				EvnSection_id: win.EvnSection_id,
				mode: 'multiKSG'
			}
		});
		this.EvnSectionKSGOneGrid.loadData({
			globalFilters: {
				EvnSection_id: win.EvnSection_id,
				mode: 'oneKSG'
			}
		});

		win.center();
	},
	callback: Ext.emptyFn,
	checkIsPaidMes: function() {
		this.checkBoxKSG.setValue(false);
		this.checkBoxKSGOne.setValue(true);

		if (this.EvnSectionKSGGrid.getGrid().getStore().getCount() > 1) {
			this.checkBoxKSG.enable();
			this.checkBoxKSGOne.enable();

			var isPaidMes = true;
			this.EvnSectionKSGGrid.getGrid().getStore().each(function(rec) {
				if (rec.get('EvnSectionKSG_IsPaidMes') != 2) {
					isPaidMes = false;
				}
			});

			if (isPaidMes) {
				this.checkBoxKSG.setValue(true);
				this.checkBoxKSGOne.setValue(false);
			}
		} else {
			this.checkBoxKSG.disable();
			this.checkBoxKSGOne.disable();
		}
	},
	doSave: function() {
		var win = this;
		win.getLoadMask('Сохранение КСГ для оплаты').show();
		Ext.Ajax.request({
			callback: function (opt, success, response) {
				win.getLoadMask().hide();
				win.hide();
				win.callback();
			},
			params: {
				EvnSection_id: win.EvnSection_id,
				mode: win.checkBoxKSG.checked ? 'multiKSG' : 'oneKSG'
			},
			url: '/?c=EvnSection&m=saveEvnSectionKSGPaid'
		});
	},
	dateRenderer: function(value, meta, record) {
		if (!value) {
			return '';
		}

		if (record.get('EvnSectionKSG_begDate') && record.get('EvnSectionKSG_endDate') && (record.get('EvnSectionKSG_endDate') - record.get('EvnSectionKSG_begDate')) / (1000*60*60*24) <= 3) {
			return "<span style='color:#FF0000;' ext:qtip='При длительности 3 дня и менее возможно применение понижающего коэффициента'>" + value.format('d.m.Y') + "</span>";
		}

		return value.format('d.m.Y');
	},
	changePeriod: function() {
		var win = this;
		var record = this.EvnSectionKSGGrid.getGrid().getSelectionModel().getSelected();
		if (record && record.get('EvnSectionKSG_id')) {
			getWnd('swEvnSectionKSGEditWindow').show({
				EvnSectionKSG_id: record.get('EvnSectionKSG_id'),
				callback: function() {
					win.EvnSectionKSGGrid.getGrid().getStore().reload();
				}
			});
		}
	},
	initComponent: function() {

		var win = this;

		this.itogKSGCoeff = new Ext.form.TextField({
			readOnly: true,
			width: 70,
			xtype: 'textfield'
		});

		this.itogKSGOneCoeff = new Ext.form.TextField({
			readOnly: true,
			width: 70,
			xtype: 'textfield'
		});

		this.checkBoxKSG = new Ext.form.Checkbox({
			listeners: {
				'change': function(checkbox, checked) {
					if (checked) {
						win.checkBoxKSGOne.setValue(false);
					} else {
						win.checkBoxKSGOne.setValue(true);
					}
				}
			}
		});

		this.checkBoxKSGOne = new Ext.form.Checkbox({
			listeners: {
				'change': function(checkbox, checked) {
					if (checked) {
						win.checkBoxKSG.setValue(false);
					} else {
						win.checkBoxKSG.setValue(true);
					}
				}
			}
		});

		this.EvnSectionKSGGrid = new sw.Promed.ViewFrame({
			actions: [
				{name: 'action_add', disabled: true, hidden: true},
				{name: 'action_edit', disabled: true, hidden: true},
				{name: 'action_view', disabled: true, hidden: true},
				{name: 'action_delete', disabled: true, hidden: true},
				{name: 'action_refresh', disabled: true, hidden: true},
				{name: 'action_print', disabled: true, hidden: true},
				{name: 'action_changeperiod', text: 'Изменить период КСГ', handler: function() {
					win.changePeriod();
				}}
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 150,
			autoLoadData: false,
			border: true,
			dataUrl: '/?c=EvnSection&m=loadEvnSectionKSGList',
			height: 200,
			paging: false,
			preToolbarItems: [{
				style: 'margin-left: 5px; margin-right: 5px;',
				text: 'Итоговый коэффициент:',
				xtype: 'label'
			}, win.itogKSGCoeff, {
				style: 'margin-left: 10px; margin-right: 5px;',
				text: 'Оплата по нескольким КСГ:',
				xtype: 'label'
			}, win.checkBoxKSG],
			region: 'center',
			onDblClick: function() {
				win.changePeriod();
			},
			onEnter: function() {
				win.changePeriod();
			},
			onLoadData: function() {
				var sum = 0;
				this.getGrid().getStore().each(function(rec){
					if (rec.get('EvnSectionKSG_ItogKSLP') > 0) {
						sum += rec.get('MesTariff_Value') * rec.get('EvnSectionKSG_ItogKSLP');
					} else {
						sum += rec.get('MesTariff_Value');
					}
				});
				win.itogKSGCoeff.setValue(sum);
				win.checkIsPaidMes();
			},
			stringfields: [
				{name: 'EvnSectionKSG_id', type: 'int', header: 'ID', key: true},
				{name: 'Mes_Code', type: 'string', header: langs('Номер КСГ'), width: 100},
				{name: 'MesOld_Num', type: 'string', header: langs('Код КСГ'), width: 100},
				{name: 'EvnSectionKSG_begDate', type: 'date', renderer: win.dateRenderer, header: langs('Дата начала'), width: 120},
				{name: 'EvnSectionKSG_endDate', type: 'date', renderer: win.dateRenderer, header: langs('Дата окончания'), width: 120},
				{name: 'MesTariff_Value', type: 'float', header: langs('КЗ'), width: 100},
				{name: 'EvnSectionKSG_ItogKSLP', type: 'float', header: langs('КСЛП'), width: 100},
				{name: 'EvnSectionKSG_IsPaidMes', type: 'int', hidden: true}
			],
			title: 'Несколько КСГ',
			toolbar: true,
			uniqueId: true
		});

		this.EvnSectionKSGOneGrid = new sw.Promed.ViewFrame({
			actions: [
				{name: 'action_add', disabled: true, hidden: true},
				{name: 'action_edit', disabled: true, hidden: true},
				{name: 'action_view', disabled: true, hidden: true},
				{name: 'action_delete', disabled: true, hidden: true},
				{name: 'action_refresh', disabled: true, hidden: true},
				{name: 'action_print', disabled: true, hidden: true}
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 150,
			autoLoadData: false,
			border: true,
			dataUrl: '/?c=EvnSection&m=loadEvnSectionKSGList',
			height: 150,
			paging: false,
			preToolbarItems: [{
				style: 'margin-left: 5px; margin-right: 5px;',
				text: 'Итоговый коэффициент:',
				xtype: 'label'
			}, win.itogKSGOneCoeff, {
				style: 'margin-left: 10px; margin-right: 5px;',
				text: 'Оплата по одной КСГ:',
				xtype: 'label'
			}, win.checkBoxKSGOne],
			region: 'south',
			onLoadData: function() {
				var sum = 0;
				this.getGrid().getStore().each(function(rec){
					if (rec.get('EvnSectionKSG_ItogKSLP') > 0) {
						sum += rec.get('MesTariff_Value') * rec.get('EvnSectionKSG_ItogKSLP');
					} else {
						sum += rec.get('MesTariff_Value');
					}
				});
				win.itogKSGOneCoeff.setValue(sum);
				win.checkIsPaidMes();
			},
			stringfields: [
				{name: 'EvnSectionKSG_id', type: 'int', header: 'ID', key: true},
				{name: 'Mes_Code', type: 'string', header: langs('Номер КСГ'), width: 100},
				{name: 'MesOld_Num', type: 'string', header: langs('Код КСГ'), width: 100},
				{name: 'EvnSectionKSG_begDate', type: 'date', renderer: win.dateRenderer, header: langs('Дата начала'), width: 120},
				{name: 'EvnSectionKSG_endDate', type: 'date', renderer: win.dateRenderer, header: langs('Дата окончания'), width: 120},
				{name: 'MesTariff_Value', type: 'float', header: langs('КЗ'), width: 100},
				{name: 'EvnSectionKSG_ItogKSLP', type: 'float', header: langs('КСЛП'), width: 100},
				{name: 'EvnSectionKSG_IsPaidMes', type: 'int', hidden: true}
			],
			title: 'Одна КСГ',
			toolbar: true,
			uniqueId: true
		});

		Ext.apply(this, {
			buttonAlign: 'right',
			layout: 'border',
			buttons: [{
				text: langs('Сохранить'),
				iconCls: 'ok16',
				handler: function(button, event) {
					win.doSave();
				}
			},	'-', HelpButton(this), {
				text: langs('Закрыть'),
				iconCls: 'close16',
				handler: function(button, event) {
					win.onCancel();
					win.hide();
				}
			}],
			items: [
				this.EvnSectionKSGGrid,
				this.EvnSectionKSGOneGrid
			]
		});

		sw.Promed.swEvnSectionKSGWindow.superclass.initComponent.apply(this, arguments);
	}
});