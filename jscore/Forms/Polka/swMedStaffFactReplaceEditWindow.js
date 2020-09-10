/**
 * swMedStaffFactReplaceEditWindow - окно редактировния замещения
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Admin
 * @access			public
 * @copyright		Copyright (c) 2017 Swan Ltd.
 * @author			Dmitrii Vlasenko
 * @version			10.09.2017
 */

/*NO PARSE JSON*/

sw.Promed.swMedStaffFactReplaceEditWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'swMedStaffFactReplaceEditWindow',
	layout: 'form',
	autoHeight: true,
	width: 600,
	action: 'view',

	doSave: function()
	{
		if ( this.formStatus == 'save' ) {
			return false;
		}
		this.formStatus = 'save';

		var win = this;
		var base_form = this.FormPanel.getForm();

		if (!base_form.isValid()) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function()
				{
					form.getFirstInvalidEl().focus(true);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			this.formStatus = 'edit';
			return false;
		}

		var params = {};

		var nowDate = Date.parseDate(getGlobalOptions().date, 'd.m.Y');
		if (base_form.findField('MedStaffFactReplace_BegDate').getValue() < nowDate) {
			sw.swMsg.alert(lang['oshibka'], 'Дата начала меньше текущей даты, исправьте данные');
			this.formStatus = 'edit';
			return false;
		}

		if (base_form.findField('MedStaffFactReplace_EndDate').getValue() < nowDate) {
			sw.swMsg.alert(lang['oshibka'], 'Дата окончания меньше текущей даты, исправьте данные');
			this.formStatus = 'edit';
			return false;
		}

		if (base_form.findField('MedStaffFactReplace_BegDate').getValue() > base_form.findField('MedStaffFactReplace_EndDate').getValue()) {
			sw.swMsg.alert(lang['oshibka'], 'Дата начала замещения больше даты окончания замещения, исправьте данные');
			this.formStatus = 'edit';
			return false;
		}

		if (base_form.findField('MedStaffFact_id').disabled) {
			params.MedStaffFact_id = base_form.findField('MedStaffFact_id').getValue();
		}

		if (base_form.findField('MedStaffFact_rid').disabled) {
			params.MedStaffFact_rid = base_form.findField('MedStaffFact_rid').getValue();
		}

		if (base_form.findField('MedStaffFactReplace_BegDate').disabled) {
			params.MedStaffFactReplace_BegDate = base_form.findField('MedStaffFactReplace_BegDate').getValue().format('d.m.Y');
		}

		win.getLoadMask(LOAD_WAIT_SAVE).show();
		base_form.submit({
			params: params,
			failure: function() {
				win.getLoadMask().hide();
				this.formStatus = 'edit';
			}.createDelegate(this),
			success: function(form, action) {
				win.getLoadMask().hide();
				this.formStatus = 'edit';
				if (action.result.success) {
					this.callback();
					this.hide();
				}
			}.createDelegate(this)
		});

		return true;
	},

	show: function(){
		sw.Promed.swMedStaffFactReplaceEditWindow.superclass.show.apply(this, arguments);

		this.formStatus = 'edit';
		this.enableEdit(false);

		var win = this;
		var base_form = this.FormPanel.getForm();
		base_form.reset();

		if (arguments[0] && arguments[0].action) {
			this.action = arguments[0].action;
		} else {
			this.action = 'view';
		}

		if (arguments[0] && arguments[0].MedStaffFactReplace_id) {
			base_form.findField('MedStaffFactReplace_id').setValue(arguments[0].MedStaffFactReplace_id);
		}

		if (arguments[0] && arguments[0].callback) {
			this.callback = arguments[0].callback;
		} else {
			this.callback = Ext.emptyFn;
		}

		this.getLoadMask(LOAD_WAIT).show();

		setMedStaffFactGlobalStoreFilter();
		base_form.findField('MedStaffFact_id').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));
		base_form.findField('MedStaffFact_rid').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));

		switch(this.action) {
			case 'add':
				this.setTitle('График замещений: Добавление');

				this.enableEdit(true);
				this.getLoadMask().hide();
				break;

			case 'edit':
			case 'view':
				if (this.action == 'edit') {
					this.setTitle('График замещений: Редактирование');
					this.enableEdit(true);
				} else {
					this.setTitle('График замещений: Просмотр');
					this.enableEdit(false);
				}

				base_form.load({
					params: {
						MedStaffFactReplace_id: base_form.findField('MedStaffFactReplace_id').getValue()
					},
					url: '/?c=MedStaffFactReplace&m=loadMedStaffFactReplaceForm',
					failure: function(){
						this.getLoadMask().hide();
					}.createDelegate(this),
					success: function() {
						this.getLoadMask().hide();

						var nowDate = Date.parseDate(getGlobalOptions().date, 'd.m.Y');
						// Если текущая дата больше периода дат графика замещений
						if (nowDate > base_form.findField('MedStaffFactReplace_EndDate').getValue()) {
							this.setTitle('График замещений: Просмотр');
							this.enableEdit(false);
						} else if (nowDate >= base_form.findField('MedStaffFactReplace_BegDate').getValue()) {
							// можно редактировать только дату окончания
							base_form.findField('MedStaffFact_id').disable();
							base_form.findField('MedStaffFact_rid').disable();
							base_form.findField('MedStaffFactReplace_BegDate').disable();
						}
					}.createDelegate(this)
				});
				break;
		}
	},

	initComponent: function() {
		var win = this;
		this.FormPanel = new sw.Promed.FormPanel({
			border: true,
			bodyStyle:'width:100%;background:#DFE8F6;padding:5px;',
			autoHeight: true,
			labelWidth: 220,
			url: '/?c=MedStaffFactReplace&m=saveMedStaffFactReplace',
			timeout: 6000,
			items: [{
				xtype: 'hidden',
				name: 'MedStaffFactReplace_id'
			}, {
				allowBlank: false,
				xtype: 'swmedstafffactglobalcombo',
				hiddenName: 'MedStaffFact_rid',
				fieldLabel: 'Сотрудник 1 (замещающий врач)',
				listWidth: 500,
				anchor: '100%'
			}, {
				allowBlank: false,
				xtype: 'swdatefield',
				name: 'MedStaffFactReplace_BegDate',
				fieldLabel: 'Дата начала',
				width: 120
			}, {
				allowBlank: false,
				xtype: 'swdatefield',
				name: 'MedStaffFactReplace_EndDate',
				fieldLabel: 'Дата окончания',
				width: 120
			}, {
				allowBlank: false,
				xtype: 'swmedstafffactglobalcombo',
				hiddenName: 'MedStaffFact_id',
				fieldLabel: 'Сотрудник 2 (замещаемый врач)',
				listWidth: 500,
				anchor: '100%'
			}],
			reader: new Ext.data.JsonReader(
			{
				success: function()
				{
					//
				}
			},
			[
				{ name: 'MedStaffFactReplace_id' },
				{ name: 'MedStaffFact_rid' },
				{ name: 'MedStaffFact_id' },
				{ name: 'MedStaffFactReplace_BegDate' },
				{ name: 'MedStaffFactReplace_EndDate' }
			])
		});

		Ext.apply(this, {
			items: [this.FormPanel],
			buttons: [
				{
					handler: function () {
						this.doSave();
					}.createDelegate(this),
					iconCls: 'save16',
					id: 'RESEW_SaveButton',
					text: BTN_FRMSAVE
				},
				{
					text: '-'
				},
				HelpButton(this, 1),
				{
					handler: function () {
						this.hide();
					}.createDelegate(this),
					iconCls: 'cancel16',
					text: BTN_FRMCLOSE
				}]
		});

		sw.Promed.swMedStaffFactReplaceViewWindow.superclass.initComponent.apply(this, arguments);
	}
});
