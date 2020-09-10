/**
 * swPersonPregnancyOutWindow - окно для исключения записи из регистра беременных
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      	Common
 * @access       	public
 * @copyright		Copyright (c) 2016 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			15.09.2016
 */
/*NO PARSE JSON*/

sw.Promed.swPersonPregnancyOutWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'swPersonPregnancyOutWindow',
	width: 500,
	autoHeight: true,
	modal: true,
	title: 'Исключение из регистра беременных',

	doSave: function() {
		var base_form = this.FormPanel.getForm();

		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
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
			failure: function(result_form, action) {
				loadMask.hide();
			}.createDelegate(this),
			success: function(result_form, action) {
				loadMask.hide();

				if (action.result && action.result.success) {
					this.callback(action.result);
					this.hide();
				}
			}.createDelegate(this)
		});
	},

	doFilterPersonRegisterOutCause: function() {
		var base_form = this.FormPanel.getForm();
		var cause_combo = base_form.findField('PersonRegisterOutCause_id');

		base_form.findField('PersonRegisterOutCause_id').lastQuery = '';
		base_form.findField('PersonRegisterOutCause_id').getStore().clearFilter();

		cause_combo.getStore().filterBy(function(record){
			return record.get('PersonRegisterOutCause_Code').inlist(['2','7']);
		});
	},

	show: function() {
		sw.Promed.swPersonPregnancyOutWindow.superclass.show.apply(this, arguments);

		this.callback = Ext.emptyFn;

		var base_form = this.FormPanel.getForm();
		base_form.reset();

		base_form.items.each(function(f){f.validate()});

		if (Ext.isEmpty(arguments[0].PersonRegister_id) || Ext.isEmpty(arguments[0].Person_id)) {
			sw.swMsg.alert(lang['soobschenie'], lang['otsutstvuyut_obyazatelnyie_parametryi']);
			this.hide();
			return;
		}

		this.PersonInfoPanel.load({Person_id: arguments[0].Person_id});

		base_form.findField('PersonRegister_id').setValue(arguments[0].PersonRegister_id);
		if (arguments[0].Lpu_did) {
			base_form.findField('Lpu_did').setValue(arguments[0].Lpu_did);
		}
		if (arguments[0].MedPersonal_did) {
			base_form.findField('MedPersonal_did').setValue(arguments[0].MedPersonal_did);
		}
		if (arguments[0].callback) {
			this.callback = arguments[0].callback;
		}

		base_form.findField('PersonRegister_disDate').setValue(new Date());
	},

	initComponent: function() {
		this.PersonInfoPanel = new sw.Promed.PersonInformationPanelShort({
			bodyStyle: 'border-bottom: 0',
			border: true
		});

		this.FormPanel = new Ext.FormPanel({
			frame: true,
			id: 'PPOW_FormPanel',
			labelAlign: 'right',
			labelWidth: 140,
			url:'/?c=PersonPregnancy&m=doPersonPregnancyOut',
			items: [{
				xtype: 'hidden',
				name: 'PersonRegister_id'
			}, {
				xtype: 'hidden',
				name: 'Lpu_did'
			}, {
				xtype: 'hidden',
				name: 'MedPersonal_did'
			}, {
				allowBlank: false,
				xtype: 'swdatefield',
				name: 'PersonRegister_disDate',
				fieldLabel: lang['data_isklyucheniya'],
				width: 100
			}, {
				allowBlank: false,
				xtype: 'swcommonsprcombo',
				allowSysNick: true,
				comboSubject: 'PersonRegisterOutCause',
				hiddenName: 'PersonRegisterOutCause_id',
				sortField:'PersonRegisterOutCause_Code',
				fieldLabel: lang['prichina_isklyucheniya'],
				onLoadStore: function() {
					this.doFilterPersonRegisterOutCause();
				}.createDelegate(this),
				width: 280
			}]
		});

		Ext.apply(this,{
			buttons: [
				{
					handler: function () {
						this.doSave();
					}.createDelegate(this),
					iconCls: 'save16',
					id: 'PPOW_SaveButton',
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
			items: [this.PersonInfoPanel, this.FormPanel]
		});

		sw.Promed.swPersonPregnancyOutWindow.superclass.initComponent.apply(this, arguments);
	}
});