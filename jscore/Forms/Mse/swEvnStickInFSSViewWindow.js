/**
 * swEvnStickInFSSViewWindow - окно состояния ЭЛН в ФСС
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package            Admin
 * @access            public
 * @copyright        Copyright (c) 2017 Swan Ltd.
 * @author            Dmitrii Vlasenko
 * @version            18.08.2017
 */

/*NO PARSE JSON*/

sw.Promed.swEvnStickInFSSViewWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'swEvnStickInFSSViewWindow',
	layout: 'form',
	autoHeight: true,
	width: 600,
	title: 'Состояние ЭЛН в ФСС',
	modal: true,
	show: function () {
		sw.Promed.swEvnStickInFSSViewWindow.superclass.show.apply(this, arguments);

		var win = this;
		var base_form = this.FormPanel.getForm();
		base_form.reset();
		this.WorkReleaseGrid.removeAll({clearAll: true});

		if (arguments[0] && arguments[0].EvnStickInFSSData) {
			var data = arguments[0].EvnStickInFSSData;
			base_form.setValues(data);
			this.WorkReleaseGrid.getGrid().getStore().loadData([
				{ WorkRelease_id: 1, WorkRelease_Value: data.FirstEvnStickWorkRelease_begDT + ' - ' + data.FirstEvnStickWorkRelease_endDT},
				{ WorkRelease_id: 2, WorkRelease_Value: data.SecondEvnStickWorkRelease_begDT + ' - ' + data.SecondEvnStickWorkRelease_endDT},
				{ WorkRelease_id: 3, WorkRelease_Value: data.ThirdEvnStickWorkRelease_begDT + ' - ' + data.ThirdEvnStickWorkRelease_endDT}
			])
		}
	},

	initComponent: function () {
		var win = this;

		this.WorkReleaseGrid = new sw.Promed.ViewFrame({
			toolbar: false,
			height: 100,
			actions: [
				{name: 'action_add', hidden: true, disabled: true},
				{name: 'action_edit', hidden: true, disabled: true},
				{name: 'action_view', hidden: true, disabled: true},
				{name: 'action_delete', hidden: true, disabled: true},
				{name: 'action_refresh', hidden: true, disabled: true}
			],
			autoLoadData: false,
			uniqueId: true,
			stringfields: [
				{name: 'WorkRelease_id', type: 'int', header: '', width: 30, hidden: false, key: true},
				{
					name: 'WorkRelease_Value',
					type: 'string',
					header: "Периоды освобождения от работы",
					id: 'autoexpand',
					width: 150
				}
			]
		});

		this.FormPanel = new sw.Promed.FormPanel({
			border: true,
			bodyStyle: 'width:100%;background:#DFE8F6;padding:5px;',
			autoHeight: true,
			defaults: {
				anchor: '100%',
				readOnly: true
			},
			labelWidth: 200,
			items: [{
				fieldLabel: 'Пациент',
				name: 'Person_Fio',
				xtype: 'textfield'
			}, {
				fieldLabel: 'Дата рождения',
				name: 'Person_BirthDay',
				xtype: 'textfield'
			}, {
				fieldLabel: 'СНИЛС',
				name: 'Person_Snils',
				xtype: 'textfield'
			}, {
				fieldLabel: 'Номер ЭЛН',
				name: 'EvnStick_Num',
				xtype: 'textfield'
			}, {
				fieldLabel: 'Дата выдачи',
				name: 'EvnStick_setDate',
				xtype: 'textfield'
			}, {
				fieldLabel: 'МО',
				name: 'Lpu_Info',
				xtype: 'textfield'
			}, {
				fieldLabel: 'Причина нетрудоспособности',
				name: 'StickCause_Name',
				xtype: 'textfield'
			}, win.WorkReleaseGrid, {
				fieldLabel: 'Исход',
				name: 'StickLeaveType_Name',
				xtype: 'textfield'
			}, {
				fieldLabel: '№ ЭЛН продолжения',
				name: 'StickFSSDataGet_StickNextNum',
				xtype: 'textfield'
			}, {
				fieldLabel: 'Дата исхода',
				name: 'EvnStick_disDate',
				xtype: 'textfield'
			}, {
				fieldLabel: 'Приступить к работе',
				name: 'EvnStick_returnDate',
				xtype: 'textfield'
			}, {
				fieldLabel: 'Статус ЭЛН',
				name: 'StickFSSType_Name',
				xtype: 'textfield'
			}]
		});

		Ext.apply(this, {
			items: [this.FormPanel],
			buttons: [{
				text: '-'
			}, HelpButton(this, 1), {
				handler: function () {
					this.hide();
				}.createDelegate(this),
				iconCls: 'cancel16',
				text: BTN_FRMCLOSE
			}]
		});

		sw.Promed.swEvnStickInFSSViewWindow.superclass.initComponent.apply(this, arguments);
	}
});
