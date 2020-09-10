/**
 * swVaccinationNoticeWindow - окно извещения о профилактической привике 
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 */

sw.Promed.swVaccinationNoticeWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'swVaccinationNoticeWindow',
	width: 550,
	autoHeight: true,
	callback: Ext.emptyFn,
	layout: 'form',
	modal: true,
	title: 'Профилактическая прививка. Извещение',
	cbFn: null,
	doSave: function() {
		var wnd = this;
		var base_form = wnd.FormPanel.getForm();

		if (!base_form.isValid())
		{
			sw.swMsg.show(
				{
					buttons: Ext.Msg.OK,
					fn: function()
					{
						wnd.FormPanel.getFirstInvalidEl().focus(true);
					},
					icon: Ext.Msg.WARNING,
					msg: ERR_INVFIELDS_MSG,
					title: ERR_INVFIELDS_TIT
				});
			return;
		}

		var params = base_form.getValues();
		params.vacJournalAccount_id = this.vacJournalAccount_id;
		params.MedPersonal_id = getGlobalOptions().medpersonal_id;
		if(this.NotifyReaction_id) params.NotifyReaction_id = this.NotifyReaction_id;

		Ext.Ajax.request({
			params: params,
			url: '/?c=VaccineCtrl&m=saveVaccinationNotice',
			success: function(response) {
				var response_obj = Ext.util.JSON.decode(response.responseText);
				if(this.cbFn) this.cbFn(true);
				this.hide();
			}.createDelegate(this),
			failure: function() {
				Ext.Msg.alert('Ошибка', 'При получении извещения');
			}
		});
	},

	show: function()
	{
		sw.Promed.swVaccinationNoticeWindow.superclass.show.apply(this, arguments);

		this.vacJournalAccount_id = (arguments && arguments[0].vacJournalAccount_id) ? arguments[0].vacJournalAccount_id : null;
		this.NotifyReaction_id = (arguments && arguments[0].NotifyReaction_id) ? arguments[0].NotifyReaction_id : null;
		this.action =  (arguments && arguments[0].action) ? arguments[0].action : 'edit';
		this.cbFn = (arguments && arguments[0].cbFn && typeof arguments[0].cbFn == 'function') ? arguments[0].cbFn : null;
		var wnd = this;
		
		var base_form = wnd.FormPanel.getForm();

		base_form.reset();
		wnd.setView();

		this.getLoadMask("идет загрузка...").show();
		Ext.Ajax.request({
			params: {
				vacJournalAccount_id: this.vacJournalAccount_id,
				NotifyReaction_id: this.NotifyReaction_id
			},
			url: '/?c=VaccineCtrl&m=loadVaccinationNotice',
			success: function(response) {
				this.getLoadMask().hide();
				var response_obj = Ext.util.JSON.decode(response.responseText);
				if (!response_obj[0].vacJournalAccount_id) {
					Ext.Msg.alert('Ошибка', 'При получении извещения');
				}
				var base_form = this.FormPanel.getForm();
				base_form.setValues(response_obj[0]);
			}.createDelegate(this),
			failure: function() {
				this.getLoadMask().hide();
				Ext.Msg.alert('Ошибка', 'При получении извещения');
			}
		});

	},
	setView: function(){
		var base_form = this.FormPanel.getForm();
		base_form.findField('Date_Vac').disable();
		base_form.findField('vac_name').disable();
		base_form.findField('Seria').disable();
		base_form.findField('VACCINE_DOZA').disable();
		base_form.findField('WAY_PLACE').disable();
		base_form.findField('Lpu_Nick').disable();
		base_form.findField('MedPersonal_Name').disable();
		if(this.action == 'view'){
			base_form.findField('NotifyReaction_createDate').disable();
			base_form.findField('NotifyReaction_confirmDate').disable();
			base_form.findField('NotifyReaction_Descr').disable();
			this.buttons[0].hide();
		}else{
			base_form.findField('NotifyReaction_createDate').enable();
			base_form.findField('NotifyReaction_confirmDate').enable();
			base_form.findField('NotifyReaction_Descr').enable();
			this.buttons[0].show();
		}
	},
	initComponent: function(){
		var wnd = this;

		wnd.FormPanel = new Ext.form.FormPanel({
			bodyBorder: false,
			bodyStyle: 'padding: 5px;',
			border: false,
			frame: true,
			// id: 'PPEW_FormPanel',
			id: 'VNW_FormPanel',
			labelAlign: 'right',
			labelWidth: 180,
			region: 'center',
			items: 
			[
				{
					allowBlank: true,
					fieldLabel: 'Дата исполнения',
					name: 'Date_Vac',
					plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
					width: 120,					
					xtype: 'swdatefield'
				},
				{
					allowBlank: true,
					fieldLabel: 'Вакцина',
					name: 'vac_name',
					width: 230,					
					xtype: 'textfield'
				},
				{
					allowBlank: true,
					fieldLabel: 'Серия',
					name: 'Seria',
					width: 230,					
					xtype: 'textfield'
				},
				{
					allowBlank: true,
					fieldLabel: 'Доза',
					name: 'VACCINE_DOZA',
					width: 230,					
					xtype: 'textfield'
				},
				{
					allowBlank: true,
					fieldLabel: 'Место введения',
					name: 'WAY_PLACE',
					width: 230,					
					xtype: 'textfield'
				},
				{
					allowBlank: true,
					fieldLabel: 'МО исполнения',
					name: 'Lpu_Nick',
					width: 230,					
					xtype: 'textfield'
				},
				{
					allowBlank: true,
					fieldLabel: 'Исполнил',
					name: 'MedPersonal_Name',
					width: 230,					
					xtype: 'textfield'
				},
				/*---------------------*/
				{
					allowBlank: false,
					fieldLabel: 'Дата создания извещения',
					name: 'NotifyReaction_createDate',
					plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
					width: 120,
					xtype: 'swdatefield'
				},
				{
					allowBlank: false,
					fieldLabel: 'Дата подтверждения неблагоприятной реакции',
					name: 'NotifyReaction_confirmDate',
					plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
					width: 120,
					xtype: 'swdatefield'
				},
				{
					allowBlank: false,
					xtype: 'textarea',
					fieldLabel: 'Описание неблагоприятной реакции',
					name: 'NotifyReaction_Descr',
					height: 100,
					width: 230
				},
			]
		});

		Ext.apply(this, {
			items: [
				wnd.FormPanel
			],
			buttons: [
				{
					handler: function () {
						wnd.doSave();
					}.createDelegate(this),
					iconCls: 'save16',
					id: 'VMW_ExportButton',
					text: 'Сохранить'
				},{
					text: '-'
				},
				{
					handler: function () {
						this.hide();
					}.createDelegate(this),
					iconCls: 'close16',
					id: 'VMW_CancelButton',
					text: 'Отмена'
				}]
		});

		sw.Promed.swVaccinationNoticeWindow.superclass.initComponent.apply(this, arguments);
	}
});