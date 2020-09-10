/**
 * swEvnCourseTreatTimeEntryWindow - окно ввода времени приема в курс лек. обеспечения
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Admin
 * @access       public
 * @author       Нигматуллин Тагир
 * @version     апрель 2019
 */

sw.Promed.swEvnCourseTreatTimeEntryWindow = Ext.extend(sw.Promed.BaseForm, {
	title: "Ввод времени приема",
	text: "Ввод времени приема",
	id: 'swEvnCourseTreatTimeEntryWindow', // tew
	border: false,
	codeRefresh: true,
	width: 500,
	height: 400,
	maximizable: true,
	modal: true,
	callback: Ext.emptyFn,
	closeAction: 'hide',
	onHide: Ext.emptyFn,
	calculationCountDay: function () {
		var kol = 0;
		for (i = 0; i < 24; i++) {
			id = 'tew_Check' + (i);
			if (Ext.getCmp(id).checked)
				kol += 1;
			Ext.getCmp('tew_CountDay').setValue(kol);
		}

	},
	createCheckbox: function (panel, index) {
		var form = this;
		var id = index;
		var name = 'Время';
		var val = 0;

		var element = {
			border: false,
			layout: 'column',
			items: [{
					border: false,
					labelWidth: 50,
					layout: 'form',
					items: [{
							xtype: 'swcheckbox',
							height: 24,
							oId: id,
							border: false,
							tabIndex: TABINDEX_TEW + (2 * id + 1),
							id: 'tew_Check' + id,
							checked: false,
							labelSeparator: '',
							listeners: {
								'change': function (obj, newValue, oldValue) {
									if (newValue != oldValue) {
										form.calculationCountDay();
									}
								}
							}
						}]
				},
				{
					border: false,
					labelWidth: 5,
					layout: 'form',
					items: [{
							allowBlank: false,
							disabled: true,
							id: 'tew_timefield' + id,
							tabIndex: TABINDEX_TEW + (2 * id + 2),
							plugins: [new Ext.ux.InputTextMask('99:99', true)],
							validateOnBlur: false,
							width: 60,
							xtype: 'swtimefield',
							labelSeparator: '',
							onTriggerClick: function () {
								if (!Ext.getCmp('tew_timefield' + id).disabled) {
									if (id != 0) {
										var idTmp = 'tew_timefield' + (id - 1);
										var val = Ext.getCmp(idTmp).getValue();
										v1 = val.substring(0, 2) * 1 + 1;
										v1 = v1 <= 9 ? '0' + v1 : v1;
										val = v1 + val.substring(2, 5);
										this.setValue(val)
									}
								}
							}
						}]
				}]
		};

		var wrapper = {
			layout: 'form',
			labelWidth: 10,
			items: [element]
		};
		panel.add(wrapper);
		panel.add({height: 5, border: false});
		panel.doLayout();

		Ext.getCmp('tew_Check' + id).setValue(val);
	},
	loadQuestions: function () {
		var form = this;

		Ext.getCmp('tew_fieldsetForm').removeAll();
		Ext.getCmp('tew_fieldsetForm2').removeAll();
		Ext.getCmp('tew_fieldsetForm3').removeAll();

		var i;

		for (i = 0; i < 8; i++) {
			form.createCheckbox(Ext.getCmp('tew_fieldsetForm'), i);
			var val = i <= 9 ? '0' + i : i;
			val = val + ':00';
			Ext.getCmp('tew_timefield' + i).setValue(val);

		}
		for (i = 8; i < 16; i++) {
			form.createCheckbox(Ext.getCmp('tew_fieldsetForm2'), i);
			var val = i <= 9 ? '0' + i : i;
			val = val + ':00';
			//log('val = ', i, val);
			Ext.getCmp('tew_timefield' + i).setValue(val);
		}
		for (i = 16; i < 24; i++) {
			form.createCheckbox(Ext.getCmp('tew_fieldsetForm3'), i);
			var val = i <= 9 ? '0' + i : i;
			val = val + ':00';
			Ext.getCmp('tew_timefield' + i).setValue(val);
		}
	},
	initComponent: function () {
		var form = this;

		Ext.apply(this, {
			frame: true,
			labelWidth: 150,
			bodyBorder: true,
			layout: "form",
			cls: 'tg-label',
			autoHeight: true,
			items: [
				new Ext.form.FormPanel({
					frame: true,
					id: 'tew_FormPlanPanel',
					//layout: 'form',
					labelWidth: 180,
					items: [
						{
							height: 10,
							border: false,
							cls: 'tg-label'
						},
						{
							autoLoad: false,
							fieldLabel: langs('Количество приемов в сутки'),
							id: 'tew_CountDay',
							width: 50,
							disabled: true,
							tabIndex: TABINDEX_TEW,
							xtype: 'textfield'
						},
						{
							height: 10,
							border: false
						}
					]
				}),
				new Ext.form.FormPanel({
					border: true,
					title: langs('Время приема'),
					labelWidth: 50,
					frame: true,
					items: [
						{
							border: false,
							layout: 'column',
							items: [//{
								{
									autoHeight: true,
									id: 'tew_fieldsetForm',
									xtype: 'fieldset',
									border: false,
									labelWidth: 200,
									items: []
								},
								{
									autoHeight: true,
									id: 'tew_fieldsetForm2',
									xtype: 'fieldset',
									border: false,
									labelWidth: 200,
									items: []
								},
								{
									autoHeight: true,
									id: 'tew_fieldsetForm3',
									xtype: 'fieldset',
									border: false,
									labelWidth: 200,
									items: []
								}
							]}
					]
				})
			],
			buttons: [
				{
					text: langs('Сохранить'),
					id: 'tew_Save',
					conCls: 'save16',
					tabIndex: TABINDEX_TEW + 50,
					handler: function () {
						var params = new Object();
						params.countDay = Ext.getCmp('tew_CountDay').getValue();
						var arr_time = new Array();
						var id;
						var val;
						for (i = 0; i < 24; i++) {
							id = 'tew_Check' + i;
							val = new Object();
							//val = new Array();
							if (Ext.getCmp(id).getValue()) {
								val.idx = i;
								val.time = Ext.getCmp('tew_timefield' + i).getValue();
								arr_time.push(val);
							}
						}
						params.arr_time = arr_time;
						Ext.getCmp(this.baseParams.parent_id).fireEvent('success', 'swEvnCourseTreatTimeEntryWindow', params)
						this.hide();
					}.createDelegate(this),
				},
				{
					text: '-'
				},
				HelpButton(this, TABINDEX_TEW + 51),
				{
					handler: function () {
						for (i = 0; i < 24; i++) {
							Ext.getCmp('tew_Check' + i).setValue(false);
						}
						Ext.getCmp('tew_CountDay').setValue(0);
					},
					iconCls: 'reset16',
					id: 'tew_Reset',
					text: langs('Сброс'),
					tabIndex: TABINDEX_TEW + 52,
				},
				{
					handler: function () {
						this.hide();
					}.createDelegate(this),
					iconCls: 'close16',
					id: 'tew_CancelButton',
					text: '<u>О</u>тменить',
					tabIndex: TABINDEX_TEW + 53,
					onTabAction: function () {
						Ext.getCmp('tew_Save').focus(true, 50);
					}.createDelegate(this)
				}]
		}
		);

		sw.Promed.swEvnCourseTreatTimeEntryWindow.superclass.initComponent.apply(this, arguments);
	},
	show: function (record) {
		sw.Promed.swEvnCourseTreatTimeEntryWindow.superclass.show.apply(this, arguments);

		var form = this;
		form.baseParams = new Object();
		form.baseParams.parent_id = arguments[0].parent_id;
		form.baseParams.countDay = arguments[0].countDay;
		if (arguments[0].arr_time)
			form.baseParams.arr_time = arguments[0].arr_time;
		else
			form.baseParams.arr_time = [];
		//log('baseParams', form.baseParams);

		Ext.getCmp('tew_CountDay').setValue(0);

		form.loadQuestions();

		for (i = 0; i < form.baseParams.arr_time.length; i++) {
			idx = form.baseParams.arr_time[i].idx;
			Ext.getCmp('tew_Check' + idx).setValue(true);
			Ext.getCmp('tew_timefield' + idx).setValue(form.baseParams.arr_time[i].time);
		}
		form.calculationCountDay();
		Ext.getCmp('tew_Save').focus(true, 50);
	},
	listeners: {
		'hide': function (win) {
			win.onHide(win);
		}
	}
});

