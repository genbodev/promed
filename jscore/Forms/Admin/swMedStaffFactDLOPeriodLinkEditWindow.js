/**
 * swMedStaffFactDLOPeriodLinkEditWindow - выбор кода дло для сотрудника
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Admin
 * @access       public
 * @copyright    Copyright (c) 2014 Swan Ltd.
 * @author       Dmitry Vlasenko
 * @version      19.10.2019
 *
 */

sw.Promed.swMedStaffFactDLOPeriodLinkEditWindow = Ext.extend(sw.Promed.BaseForm, {
	action: null,
	autoHeight: true,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: false,
	closeAction: 'hide',
	collapsible: true,
	title: langs('Внешний код врача ЛЛО'),
	doSave: function() {
		var win = this;

		if ( this.formStatus == 'save' || this.action == 'view' ) {
			return false;
		}

		this.formStatus = 'save';

		var base_form = this.FormPanel.getForm();

		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					this.formStatus = 'edit';
					this.FormPanel.getFirstInvalidEl().focus(false);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		win.getLoadMask(LOAD_WAIT_SAVE).show();

		base_form.submit({
			failure: function(result_form, action) {
				win.formStatus = 'edit';
				win.getLoadMask().hide();
			},
			success: function(result_form, action) {
				win.formStatus = 'edit';
				win.getLoadMask().hide();

				if ( action.result ) {
					win.callback();
					win.hide();
				}
				else {
					sw.swMsg.alert(langs('Ошибка'), langs('При сохранении произошла ошибка'));
				}
			}
		});
	},
	draggable: true,
	id: 'MedStaffFactDLOPeriodLinkEditWindow',
	initComponent: function() {
		var win = this;

		this.FormPanel = new Ext.form.FormPanel({
			autoHeight: true,
			autoScroll: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			border: false,
			frame: false,
			labelAlign: 'right',
			labelWidth: 130,
			layout: 'form',
			reader: new Ext.data.JsonReader({
				success: Ext.emptyFn
			}, [
				{name: 'MedStaffFact_id'},
				{name: 'MedPersonalDLOPeriod_id'},
				{name: 'MedstaffFactDLOPeriodLink_begDate'},
				{name: 'MedstaffFactDLOPeriodLink_endDate'}
			]),
			region: 'center',
			url: '/?c=MedPersonal&m=saveMedStaffFactDLOPeriodLink',
			items: [{
				name: 'MedStaffFact_id',
				value: '',
				xtype: 'hidden'
			}, {
				allowBlank: false,
				mode: 'local',
				triggerAction: 'all',
				hiddenName: 'MedPersonalDLOPeriod_id',
				store: new Ext.data.JsonStore({
					autoLoad: false,
					url: '/?c=MedPersonal&m=loadMedPersonalDLOPeriod',
					fields: [
						{name: 'MedPersonalDLOPeriod_id', type: 'int'},
						{name: 'MedPersonalDLOPeriod_PCOD', type: 'string'},
						{name: 'MedPersonalDLOPeriod_MCOD', type: 'string'},
						{name: 'MedPersonalDLOPeriod_Fio', type: 'string'},
						{name: 'MedPersonalDLOPeriod_begDate', type: 'date', dateFormat: 'd.m.Y'},
						{name: 'MedPersonalDLOPeriod_endDate', type: 'date', dateFormat: 'd.m.Y'}
					],
					key: 'MedPersonalDLOPeriod_id',
					sortInfo: {
						field: 'MedPersonalDLOPeriod_PCOD'
					}
				}),
				valueField: 'MedPersonalDLOPeriod_id',
				displayField: 'MedPersonalDLOPeriod_PCOD',
				tpl: new Ext.XTemplate(
					'<table cellpadding="0" cellspacing="0" style="width: 100%;"><tr style="font-family: tahoma; font-size: 10pt; font-weight: bold;">',
					'<td style="padding: 2px; width: 10%;">PCOD</td>',
					'<td style="padding: 2px; width: 10%;">MCOD</td>',
					'<td style="padding: 2px; width: 30%;">Период действия</td>',
					'<td style="padding: 2px; width: 50%;">ФИО врача</td>',
					'<tpl for="."><tr class="x-combo-list-item" style="white-space: normal; overflow: auto; text-overflow: clip;">',
					'<td style="padding: 2px;">{MedPersonalDLOPeriod_PCOD}&nbsp;</td>',
					'<td style="padding: 2px;">{MedPersonalDLOPeriod_MCOD}&nbsp;</td>',
					'<td style="padding: 2px;">{[Ext.util.Format.date(values.MedPersonalDLOPeriod_begDate, "d.m.Y")]} - {[Ext.util.Format.date(values.MedPersonalDLOPeriod_endDate, "d.m.Y")]}</td>',
					'<td style="padding: 2px;">{MedPersonalDLOPeriod_Fio}&nbsp;</td>',
					'</tr></tpl>',
					'</table>'
				),
				anchor: '100%',
				allowBlank: false,
				fieldLabel: langs('Код ЛЛО'),
				listWidth: 700,
				xtype: 'swbaseremotecombo'
			}, {
				allowBlank: false,
				fieldLabel: 'Дата начала',
				name: 'MedstaffFactDLOPeriodLink_begDate',
				xtype: 'swdatefield',
				format: 'd.m.Y',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ]
			}, {
				fieldLabel: 'Дата окончания',
				name: 'MedstaffFactDLOPeriodLink_endDate',
				xtype: 'swdatefield',
				format: 'd.m.Y',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ]
			}]
		});

		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.doSave();
				}.createDelegate(this),
				iconCls: 'save16',
				onShiftTabAction: function () {
					this.buttons[this.buttons.length - 1].focus();
				}.createDelegate(this),
				onTabAction: function () {
					this.buttons[this.buttons.length - 1].focus();
				}.createDelegate(this),
				text: BTN_FRMSAVE
			}, {
				text: '-'
			},
				HelpButton(this, -1),
				{
					handler: function() {
						this.hide();
					}.createDelegate(this),
					iconCls: 'cancel16',
					onShiftTabAction: function () {
						if ( this.action != 'view' ) {
							this.buttons[0].focus();
						}
					}.createDelegate(this),
					onTabAction: function () {
						if ( this.action != 'view' ) {
							this.buttons[0].focus();
						}
					}.createDelegate(this),
					text: BTN_FRMCANCEL
				}],
			items: [ this.FormPanel ]
		});

		sw.Promed.swMedStaffFactDLOPeriodLinkEditWindow.superclass.initComponent.apply(this, arguments);
	},
	layout: 'form',
	listeners: {
		'hide': function(win) {
			win.onHide();
		}
	},
	maximizable: true,
	modal: true,
	onHide: Ext.emptyFn,
	parentClass: null,
	plain: true,
	resizable: false,
	show: function() {
		sw.Promed.swMedStaffFactDLOPeriodLinkEditWindow.superclass.show.apply(this, arguments);

		var win = this;

		this.restore();
		this.center();

		if ( !arguments[0] || !arguments[0].MedStaffFact_id ) {
			sw.swMsg.alert(langs('Сообщение'), langs('Неверные параметры'), function() {this.hide();}.createDelegate(this) );
			return false;
		}

		if (arguments[0].callback) {
			win.callback = arguments[0].callback;
		} else {
			win.callback = Ext.emptyFn;
		}

		var MedStaffFact_id = arguments[0].MedStaffFact_id;
		var base_form = this.FormPanel.getForm();
		base_form.reset();

		this.syncSize();
		this.syncShadow();

		win.getLoadMask(LOAD_WAIT).show();
		base_form.load({
			failure: function() {
				win.getLoadMask().hide();
				sw.swMsg.alert(langs('Ошибка'), langs('Ошибка при загрузке данных формы'), function() {this.hide();}.createDelegate(this) );
			}.createDelegate(this),
			params: {
				'MedStaffFact_id': MedStaffFact_id
			},
			success: function() {
				win.getLoadMask().hide();

				var MedPersonalDLOPeriod_id = base_form.findField('MedPersonalDLOPeriod_id').getValue();
				base_form.findField('MedPersonalDLOPeriod_id').getStore().load({
					params: {
						'MedStaffFact_id': MedStaffFact_id
					},
					callback: function() {
						var index = base_form.findField('MedPersonalDLOPeriod_id').getStore().findBy(function(rec) {
							return (rec.get('MedPersonalDLOPeriod_id') == MedPersonalDLOPeriod_id);
						});

						if ( index >= 0 ) {
							base_form.findField('MedPersonalDLOPeriod_id').setValue(MedPersonalDLOPeriod_id);
						} else {
							base_form.findField('MedPersonalDLOPeriod_id').clearValue();
						}
					}
				});
			}.createDelegate(this),
			url: '/?c=MedPersonal&m=loadMedStaffFactDLOPeriodLinkEditForm'
		});
	},
	width: 500
});
