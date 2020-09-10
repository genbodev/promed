/**
* swMorbusHepatitisCureEffMonitoringWindow - Мониторинг эффективности лечения.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2009-2010 Swan Ltd.
* @version      24.05.2012
*/

sw.Promed.swMorbusHepatitisCureEffMonitoringWindow = Ext.extend(sw.Promed.BaseForm, 
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
	modal: true,
	doSave: function() 
	{
		if ( this.formStatus == 'save' ) {
			return false;
		}
		
		this.formStatus = 'save';
		
		var win = this;
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

		var hepatitis_cure_period_type_id = base_form.findField('HepatitisCurePeriodType_id').getValue();
		var hepatitis_cure_period_type_name = '';
		
		var index;
		var params = new Object();
		
		index = base_form.findField('HepatitisCurePeriodType_id').getStore().findBy(function(rec) {
			if ( rec.get('HepatitisCurePeriodType_id') == hepatitis_cure_period_type_id ) {
				return true;
			}
			else {
				return false;
			}
		});		

		if ( index >= 0 ) {
			hepatitis_cure_period_type_name = base_form.findField('HepatitisCurePeriodType_id').getStore().getAt(index).get('HepatitisCurePeriodType_Name');
		}

		var hepatitis_qual_analysis_type_id = base_form.findField('HepatitisQualAnalysisType_id').getValue();
		var hepatitis_qual_analysis_type_name = '';
		
		index = base_form.findField('HepatitisQualAnalysisType_id').getStore().findBy(function(rec) {
			if ( rec.get('HepatitisQualAnalysisType_id') == hepatitis_qual_analysis_type_id ) {
				return true;
			}
			else {
				return false;
			}
		});		

		if ( index >= 0 ) {
			hepatitis_qual_analysis_type_name = base_form.findField('HepatitisQualAnalysisType_id').getStore().getAt(index).get('HepatitisQualAnalysisType_Name');
		}

		var data = new Object();

		switch ( this.formMode ) {
			case 'local':
				data.BaseData = {
					'MorbusHepatitisCureEffMonitoring_id': base_form.findField('MorbusHepatitisCureEffMonitoring_id').getValue(),
					'HepatitisCurePeriodType_id': hepatitis_cure_period_type_id,
					'HepatitisCurePeriodType_Name': hepatitis_cure_period_type_name,
					'HepatitisQualAnalysisType_id': hepatitis_qual_analysis_type_id,
					'HepatitisQualAnalysisType_Name': hepatitis_qual_analysis_type_name,
					'MorbusHepatitisCureEffMonitoring_VirusStress': base_form.findField('MorbusHepatitisCureEffMonitoring_VirusStress').getValue()			
				};

				this.callback(data);

				this.formStatus = 'edit';
				loadMask.hide();

				this.hide();
			break;

			case 'remote':
				base_form.submit({
					failure: function(result_form, action) 
					{
						loadMask.hide();
						if (action.result) 
						{
							if (action.result.Error_Code)
							{
								Ext.Msg.alert(lang['oshibka_#']+action.result.Error_Code, action.result.Error_Message);
							}
						}
					},
					success: function(result_form, action) 
					{
						loadMask.hide();
						win.callback();
						win.hide();
					}
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
		sw.Promed.swMorbusHepatitisCureEffMonitoringWindow.superclass.show.apply(this, arguments);
		
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
		
		if (arguments[0].MorbusHepatitisCureEffMonitoring_id) 
			this.MorbusHepatitisCureEffMonitoring_id = arguments[0].MorbusHepatitisCureEffMonitoring_id;
		else 
			this.MorbusHepatitisCureEffMonitoring_id = null;
			
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
			if ( ( this.MorbusHepatitisCureEffMonitoring_id ) && ( this.MorbusHepatitisCureEffMonitoring_id > 0 ) )
				this.action = "edit";
			else 
				this.action = "add";
		}
		
		base_form.setValues(arguments[0].formParams);
		
		var loadMask = new Ext.LoadMask(this.getEl(),{msg: LOAD_WAIT});
		loadMask.show();
		
		if ( this.formMode != 'local' && this.action != 'add' ) {
			Ext.Ajax.request({
				failure:function (response, options) {
					sw.swMsg.alert(lang['oshibka'], lang['ne_udalos_poluchit_dannyie_s_servera']);
				},
				params:{
					MorbusHepatitisCureEffMonitoring_id: this.MorbusHepatitisCureEffMonitoring_id
				},
				success:function (response, options) {
					var result = Ext.util.JSON.decode(response.responseText);
					//log(result[0]);
					base_form.setValues(result[0]);
					loadMask.hide();
				}.createDelegate(this),
				url:'/?c=MorbusHepatitisCureEffMonitoring&m=load'
			});	
		}
		
		switch (this.action) 
		{
			case 'add':
				this.setTitle(lang['monitoring_effektivnosti_lecheniya_dobavlenie']);
				this.setFieldsDisabled(false);
				break;
			case 'edit':
				this.setTitle(lang['monitoring_effektivnosti_lecheniya_redaktirovanie']);
				this.setFieldsDisabled(false);
				break;
			case 'view':
				this.setTitle(lang['monitoring_effektivnosti_lecheniya_prosmotr']);
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
			labelWidth: 200,
			url:'/?c=MorbusHepatitisCureEffMonitoring&m=save',
			items: 
			[{
				name: 'MorbusHepatitisCureEffMonitoring_id',
				xtype: 'hidden'
			}, {
				name: 'MorbusHepatitisCure_id',
				xtype: 'hidden'
			}, {
				allowBlank: false,
				name: 'HepatitisCurePeriodType_id',
                comboSubject: 'HepatitisCurePeriodType',
                fieldLabel: lang['srok_lecheniya'],
				xtype: 'swcommonsprcombo',
				width: 450
			}, {
				allowBlank: false,
				name: 'HepatitisQualAnalysisType_id',
                comboSubject: 'HepatitisQualAnalysisType',
                fieldLabel: lang['kachestvennyiy_analiz'],
				xtype: 'swcommonsprcombo',
				width: 450
			}, {
				allowBlank: false,
				name: 'MorbusHepatitisCureEffMonitoring_VirusStress',
                fieldLabel: lang['virusnaya_nagruzka'],
				autoCreate: {tag: "input", size:4, maxLength: "4", autocomplete: "off"},
				maskRe: /[0-9]/,
				xtype: 'textfield',
				width: 120
			}],
			reader: new Ext.data.JsonReader(
			{
				success: Ext.emptyFn
			}, 
			[
				{name: 'MorbusHepatitisCureEffMonitoring_id'},
				{name: 'HepatitisCurePeriodType_id'},
				{name: 'HepatitisQualAnalysisType_id'},
				{name: 'MorbusHepatitisCureEffMonitoring_VirusStress'}
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
		sw.Promed.swMorbusHepatitisCureEffMonitoringWindow.superclass.initComponent.apply(this, arguments);
	}
});