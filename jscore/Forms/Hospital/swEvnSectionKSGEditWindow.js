/**
 * swEvnSectionKSGEditWindow - форма редактирования периода КСГ
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2013 Swan Ltd.
 * @author       Dmitry Vlasenko
 * @version      01.05.2019
 */

sw.Promed.swEvnSectionKSGEditWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: true,
	width: 500,
	layout: 'form',
	modal: true,
	resizable: false,
	plain: false,
	title: langs('Период КСГ'),
	onCancel: function() {},
	show: function() {
		sw.Promed.swEvnSectionKSGEditWindow.superclass.show.apply(this, arguments);

		var win = this;

		if (!arguments[0] || !arguments[0].EvnSectionKSG_id) {
			sw.swMsg.alert(langs('Сообщение'), langs('Неверные параметры'), function() {
				win.hide();
			});
			return false;
		}

		this.EvnSectionKSG_id = arguments[0].EvnSectionKSG_id;

		if (arguments[0].callback) {
			this.callback = arguments[0].callback;
		} else {
			this.callback = Ext.emptyFn;
		}

		win.center();

		var base_form = this.FormPanel.getForm();
		base_form.reset();
		win.getLoadMask(LOAD_WAIT).show();
		base_form.load({
			failure:function () {
				sw.swMsg.alert(langs('Ошибка'), langs('Не удалось получить данные с сервера'));
				win.getLoadMask().hide();
				win.hide();
			},
			params:{
				EvnSectionKSG_id: win.EvnSectionKSG_id
			},
			success: function (response) {
				win.getLoadMask().hide();
			},
			url:'/?c=EvnSection&m=loadEvnSectionKSGEditForm'
		});
	},
	callback: Ext.emptyFn,
	doSave: function() {
		var win = this;
		var base_form = this.FormPanel.getForm();

		if (!base_form.isValid()) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					win.FormPanel.getFirstInvalidEl().focus(true);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		win.getLoadMask('Сохранение периода КСГ').show();
		base_form.submit({
			failure: function() {
				win.getLoadMask().hide();
			},
			success: function() {
				win.getLoadMask().hide();
				win.hide();
				win.callback();
			}
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
		var record = this.EvnSectionKSGGrid.getGrid().getSelectionModel().getSelected();
		if (record && record.get['EvnSectionKSG_id']) {
			getWnd('swEvnSectionKSGEditWindow').show({
				EvnSectionKSG_id: record.get('EvnSectionKSG_id')
			});
		}
	},
	initComponent: function() {
		var win = this;

		this.FormPanel = new Ext.form.FormPanel({
			autoScroll: true,
			autoHeight: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			border: false,
			frame: false,
			labelAlign: 'right',
			labelWidth: 100,
			layout: 'form',
			items: [{
				name: 'EvnSectionKSG_id',
				xtype: 'hidden'
			}, {
				anchor: '100%',
				fieldLabel: langs('КСГ'),
				name: 'Mes_Name',
				readOnly: true,
				xtype: 'textfield'
			}, {
				xtype: 'swdatefield',
				fieldLabel: langs('Дата начала'),
				name: 'EvnSectionKSG_begDate',
				allowBlank: false,
				format: 'd.m.Y',
				width: 120,
				plugins: [new Ext.ux.InputTextMask('99.99.9999', false)]
			}, {
				xtype: 'swdatefield',
				fieldLabel: langs('Дата окончания'),
				name: 'EvnSectionKSG_endDate',
				allowBlank: false,
				format: 'd.m.Y',
				width: 120,
				plugins: [new Ext.ux.InputTextMask('99.99.9999', true)]
			}],
			reader: new Ext.data.JsonReader({
				success: Ext.emptyFn
			}, [
				{name: 'EvnSectionKSG_id'},
				{name: 'EvnSectionKSG_begDate'},
				{name: 'EvnSectionKSG_endDate'},
				{name: 'Mes_Name'}
			]),
			url: '/?c=EvnSection&m=saveEvnSectionKSG'
		});

		Ext.apply(this, {
			buttonAlign: 'right',
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
					win.hide();
				}
			}],
			items: [
				this.FormPanel
			]
		});

		sw.Promed.swEvnSectionKSGEditWindow.superclass.initComponent.apply(this, arguments);
	}
});