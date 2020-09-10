/**
* swMorbusCrazyForceTreatWindow - окно изменения (продления) принудительного лечения.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2009-2010 Swan Ltd.
* @version      20.12.2011
*/

sw.Promed.swMorbusCrazyForceTreatWindow = Ext.extend(sw.Promed.BaseForm, 
{
	action: null,
	autoHeight: true,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	draggable: true,
	formMode: 'remote',
	formStatus: 'edit',
	doSave: function() 
	{
		if ( this.formStatus == 'save' ) {
			return false;
		}

		this.formStatus = 'save';
		
		var form = this.FormPanel;
		var base_form = form.getForm();

		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					this.formStatus = 'edit';
					form.getFirstInvalidEl().focus(false);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение..."});
		loadMask.show();
		
		var crazy_force_treat_type_id = base_form.findField('CrazyForceTreatType_id').getValue();
		var crazy_force_treat_type_name = '';
		
		var crazy_force_treat_judge_decision_type_id =  base_form.findField('CrazyForceTreatJudgeDecisionType_id').getValue();
		var crazy_force_treat_judge_decision_type_name =  '';
		
		var index;
		var params = new Object();
		
		index = base_form.findField('CrazyForceTreatType_id').getStore().findBy(function(rec) {
			if ( rec.get('CrazyForceTreatType_id') == crazy_force_treat_type_id ) {
				return true;
			}
			else {
				return false;
			}
		});		

		if ( index >= 0 ) {
			crazy_force_treat_type_name = base_form.findField('CrazyForceTreatType_id').getStore().getAt(index).get('CrazyForceTreatType_Name');
		}
		
		index = base_form.findField('CrazyForceTreatJudgeDecisionType_id').getStore().findBy(function(rec) {
			if ( rec.get('CrazyForceTreatJudgeDecisionType_id') == crazy_force_treat_judge_decision_type_id ) {
				return true;
			}
			else {
				return false;
			}
		});		

		if ( index >= 0 ) {
			crazy_force_treat_judge_decision_type_name = base_form.findField('CrazyForceTreatJudgeDecisionType_id').getStore().getAt(index).get('CrazyForceTreatJudgeDecisionType_Name');
		}
		
		var data = new Object();

		switch ( this.formMode ) {
			case 'local':
				data.forceTreatData = {
					'MorbusCrazyForceTreat_id': base_form.findField('MorbusCrazyForceTreat_id').getValue(),
					'MorbusCrazyForceTreat_setDT': base_form.findField('MorbusCrazyForceTreat_setDT').getValue(),
					'CrazyForceTreatType_id': crazy_force_treat_type_id,
					'CrazyForceTreatType_Name': crazy_force_treat_type_name,
					'CrazyForceTreatJudgeDecisionType_id': crazy_force_treat_judge_decision_type_id,
					'CrazyForceTreatJudgeDecisionType_Name': crazy_force_treat_judge_decision_type_name
				};

				this.callback(data);

				this.formStatus = 'edit';
				loadMask.hide();

				this.hide();
			break;

			case 'remote':
				base_form.submit({
					failure: function(result_form, action) {
						this.formStatus = 'edit';
						loadMask.hide();

						if ( action.result ) {
							if ( action.result.Error_Msg ) {
								sw.swMsg.alert(lang['oshibka'], action.result.Error_Msg);
							}
							else {
								sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_1]']);
							}
						}
					}.createDelegate(this),
					params: params,
					success: function(result_form, action) {
						this.formStatus = 'edit';
						loadMask.hide();

						if ( action.result ) {
							if ( action.result.MorbusCrazyForceTreat_id > 0 ) {
								base_form.findField('MorbusCrazyForceTreat_id').setValue(action.result.MorbusCrazyForceTreat_id);

								data.forceTreatData = {
									'MorbusCrazyForceTreat_id': base_form.findField('MorbusCrazyForceTreat_id').getValue(),
									'MorbusCrazyForceTreat_setDT': base_form.findField('MorbusCrazyForceTreat_setDT').getValue(),
									'CrazyForceTreatJudgeDecisionType_id': crazy_force_treat_judge_decision_type_id,
									'CrazyForceTreatType_id': crazy_force_treat_type_id
								};

								this.callback(data);
								this.hide();
							}
							else {
								if ( action.result.Error_Msg ) {
									sw.swMsg.alert(lang['oshibka'], action.result.Error_Msg);
								}
								else {
									sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_3]']);
								}
							}
						}
						else {
							sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_2]']);
						}
					}.createDelegate(this)
				});
			break;

			default:
				loadMask.hide();
			break;
			
		}		
		
	},
	setFieldsDisabled: function(d) 
	{
		var form = this;
		this.FormPanel.items.each(function(f) 
		{
			if (f && (f.xtype!='hidden') && (f.xtype!='fieldset')  && (f.changeDisabled!==false))
			{
				f.setDisabled(d);
			}
		});
		form.buttons[0].setDisabled(d);
	},
	show: function() 
	{
		sw.Promed.swMorbusCrazyForceTreatWindow.superclass.show.apply(this, arguments);
		
		var current_window = this;
		if (!arguments[0]) 
		{
			sw.swMsg.show(
			{
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.ERROR,
				msg: lang['oshibka_otkryitiya_formyi_ne_ukazanyi_nujnyie_vhodnyie_parametryi'],
				title: lang['oshibka'],
				fn: function() {
					this.hide();
				}
			});
		}
		this.focus();
		this.findById('FormPanel').getForm().reset();
		
		this.center();

		var base_form = this.FormPanel.getForm();
		base_form.reset();

		this.action = null;
		this.callback = Ext.emptyFn;
		this.formMode = 'remote';
		this.formStatus = 'edit';
		this.onHide = Ext.emptyFn;
		
		if (arguments[0].MorbusCrazyForceTreat_id) 
			this.MorbusCrazyForceTreat_id = arguments[0].MorbusCrazyForceTreat_id;
		else 
			this.MorbusCrazyForceTreat_id = null;
			
		if (arguments[0].callback) 
		{
			this.callback = arguments[0].callback;
		}	
		if ( arguments[0].formMode && typeof arguments[0].formMode == 'string' && arguments[0].formMode.inlist([ 'local', 'remote' ]) ) 
		{
			this.formMode = arguments[0].formMode;
		}
		if (arguments[0].owner) 
		{
			this.owner = arguments[0].owner;
		}
		if (arguments[0].action) 
		{
			this.action = arguments[0].action;
		}
		else 
		{
			if ( ( this.MorbusCrazyForceTreat_id ) && ( this.MorbusCrazyForceTreat_id > 0 ) )
				this.action = "edit";
			else 
				this.action = "add";
		}
		
		base_form.setValues(arguments[0].formParams);
		
		var loadMask = new Ext.LoadMask(this.getEl(),{msg: LOAD_WAIT});
		loadMask.show();
		switch (this.action) 
		{
			case 'add':
				this.setTitle(lang['izmenenie_prodlenie_prinuditelnogo_lecheniya_dobavlenie']);
				this.setFieldsDisabled(false);
				break;
			case 'edit':
				this.setTitle(lang['izmenenie_prodlenie_prinuditelnogo_lecheniya_redaktirovanie']);
				this.setFieldsDisabled(false);
				break;
			case 'view':
				this.setTitle(lang['izmenenie_prodlenie_prinuditelnogo_lecheniya_prosmotr']);
				this.setFieldsDisabled(true);
				break;
		}
		
		loadMask.hide();
		
	},	
	initComponent: function() 
	{
		
		this.FormPanel = new Ext.form.FormPanel(
		{	
			autoScroll: true,
			frame: true,
			region: 'north',
			id: 'FormPanel',
			bodyStyle: 'padding: 5px',
			autoHeight: false,
			labelAlign: 'right',
			labelWidth: 180,
			items: 
			[{
				name: 'MorbusCrazyForceTreat_id',
				xtype: 'hidden'
			}, {
				fieldLabel: lang['data_izmeneniya_prodleniya'],
				name: 'MorbusCrazyForceTreat_setDT',
				allowBlank: false,
				xtype: 'swdatefield',
				plugins: [new Ext.ux.InputTextMask('99.99.9999', false)]
			}, {
				fieldLabel: lang['reshenie_suda'],
				name: 'CrazyForceTreatJudgeDecisionType_id',
				xtype: 'swcrazyforcetreatjudgedecisiontypecombo',
				width: 350
			}, {
				fieldLabel: lang['vid'],
				hiddenName: 'CrazyForceTreatType_id',
				xtype: 'swcrazyforcetreattypecombo',
				width: 350
			}],
			reader: new Ext.data.JsonReader(
			{
				success: Ext.emptyFn
			}, 
			[
				{name: 'MorbusCrazyForceTreat_id'},
				{name: 'MorbusCrazyForceTreat_setDT'},
				{name: 'CrazyForceTreatJudgeDecisionType_id'},
				{name: 'CrazyForceTreatType_id'}
			])
		});
		Ext.apply(this, 
		{
			buttons: 
			[{
				handler: function() {
					this.doSave();
				}.createDelegate(this),
				iconCls: 'save16',
				text: BTN_FRMSAVE
			}, 
			{
				text: '-'
			},
			HelpButton(this),
			{
				handler: function() 
				{
					this.ownerCt.hide();
				},
				iconCls: 'cancel16',
				text: BTN_FRMCANCEL
			}],
			items: [this.FormPanel]
		});
		sw.Promed.swMorbusCrazyForceTreatWindow.superclass.initComponent.apply(this, arguments);
	}
});