/**
 * swPersonRegisterHistEditWindow - окно редактирования параметров записи регистра
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      	PersonRegister
 * @access       	public
 * @copyright		Copyright (c) 2017 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			15.03.2017
 */
/*NO PARSE JSON*/

sw.Promed.swPersonRegisterHistEditWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'swPersonRegisterHistEditWindow',
	width: 540,
	autoHeight: true,
	modal: true,
	title: 'Параметры записи регистра',

	doSave: function() {
		var base_form = this.FormPanel.getForm();

		if (!base_form.isValid()) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function()
				{
					this.FormPanel.getFirstInvalidEl().focus(true);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение..."});
		loadMask.show();

		base_form.submit({
			success: function(result_form, action) {
				loadMask.hide();
				if (action.result && action.result.success) {
					this.callback();
					this.hide();
				}
			}.createDelegate(this),
			failure: function() {
				loadMask.hide();
			}.createDelegate(this)
		});
	},

	show: function() {
		sw.Promed.swPersonRegisterHistEditWindow.superclass.show.apply(this, arguments);

		if (!arguments[0] || Ext.isArray(arguments[0].PersonRegister_id)) {
			sw.swMsg.alert(lang['soobschenie'], lang['otsutstvuyut_obyazatelnyie_parametryi']);
			this.hide();
			return;
		}

		var base_form = this.FormPanel.getForm();
		base_form.reset();

		this.PersonRegister_id = arguments[0].PersonRegister_id;
		this.callback = Ext.emptyFn;
		if (arguments[0].callback) {
			this.callback = arguments[0].callback;
		}

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Подождите, идет загрузка..." });
		loadMask.show();

		base_form.load({
			url: '/?c=PersonRegister&m=loadPersonRegisterHistForm',
			params: {PersonRegister_id: this.PersonRegister_id},
			success: function() {
				loadMask.hide();


				var lpuCombo = base_form.findField('Lpu_id');
				var lpuIndex = lpuCombo.getStore().find('Lpu_id', lpuCombo.getValue());
				var lpuRecord = lpuCombo.getStore().getAt(lpuIndex);
				lpuCombo.fireEvent('select', lpuCombo, lpuRecord, lpuIndex);
			},
			failure: function() {
				loadMask.hide();
			}
		});
	},

	initComponent: function() {
		var wnd = this;

		this.FormPanel = new Ext.FormPanel({
			frame: true,
			id: 'PRHEW_FormPanel',
			labelAlign: 'right',
			labelWidth: 100,
			url: '/?c=PersonRegister&m=createPersonRegisterHist',	//Только добавление записи в историю
			items: [{
				xtype: 'hidden',
				name: 'PersonRegisterHist_id'
			}, {
				xtype: 'hidden',
				name: 'PersonRegister_id'
			}, {
				xtype: 'textfield',
				name: 'PersonRegisterHist_NumCard',
				fieldLabel: 'Номер карты',
				width: 380
			}, {
				xtype: 'swlpucombo',
				hiddenName: 'Lpu_id',
				fieldLabel: 'МО',
				listeners: {
					'select': function(combo, record, index) {
						var base_form = this.FormPanel.getForm();
						var MedPersonalCombo = base_form.findField('MedPersonal_id');
						var MedPersonal_id = MedPersonalCombo.getValue();

						MedPersonalCombo.setValue(null);
						MedPersonalCombo.getStore().removeAll();
						if (record && !Ext.isEmpty(record.get('Lpu_id'))) {
							MedPersonalCombo.getStore().load({
								params: {
									Lpu_id: record.get('Lpu_id'),
									All_Rec: 1,
									MedPersonalNotNeeded: "true"
								},
								callback: function() {
									var rec = MedPersonalCombo.getStore().getById(MedPersonal_id);
									if (rec) {
										MedPersonalCombo.setValue(rec.get('MedPersonal_id'));
									}
								}
							});
						}
					}.createDelegate(this)
				},
				width: 380
			}, {
				xtype: 'swmedpersonalcombo',
				hiddenName: 'MedPersonal_id',
				fieldLabel: 'Врач',
				width: 380
			}, {
				allowBlank: false,
				xtype: 'swdatefield',
				name: 'PersonRegisterHist_begDate',
				fieldLabel: 'Дата с'
			}],
			reader: new Ext.data.JsonReader({
				success: function(){}
			}, [
				{name: 'PersonRegisterHist_id'},
				{name: 'PersonRegister_id'},
				{name: 'PersonRegisterHist_NumCard'},
				{name: 'Lpu_id'},
				{name: 'MedPersonal_id'},
				{name: 'PersonRegisterHist_begDate'}
			])
		});

		Ext.apply(this,{
			buttons: [
				{
					handler: function () {
						this.doSave();
					}.createDelegate(this),
					iconCls: 'save16',
					id: 'PPREW_SaveButton',
					text: BTN_FRMSAVE
				},
				{
					text: '-'
				},
				HelpButton(this),
				{
					handler: function()
					{
						this.hide();
					}.createDelegate(this),
					iconCls: 'cancel16',
					text: BTN_FRMCLOSE
				}
			],
			items: [this.FormPanel]
		});

		sw.Promed.swPersonRegisterHistEditWindow.superclass.initComponent.apply(this, arguments);
	}
});