/**
* swMorbusTubDiagGeneralFormWindow - Генерализованные формы.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2009-2010 Swan Ltd.
* @version      08.2016
*/

sw.Promed.swMorbusTubDiagGeneralFormWindow = Ext.extend(sw.Promed.BaseForm, 
{
	action: null,
	winTitle: lang['generalizovannyie_formy'],
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
		
		var params = new Object();
		var data = new Object();

		switch ( this.formMode ) {
			case 'local':
				data.BaseData = {
					'TubDiagGeneralForm_id': base_form.findField('TubDiagGeneralForm_id').getValue(),
					'TubDiagGeneralForm_setDT': base_form.findField('TubDiagGeneralForm_setDT').getValue(),
					'Diag_id': base_form.findField('Diag_id').getValue(),
					'Diag_Name': base_form.findField('Diag_id').getRawValue()
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
							if ( action.result.TubDiagGeneralForm_id > 0 ) {
								base_form.findField('TubDiagGeneralForm_id').setValue(action.result.TubDiagGeneralForm_id);

								data.BaseData = {
									'TubDiagGeneralForm_id': base_form.findField('TubDiagGeneralForm_id').getValue(),
									'TubDiagGeneralForm_setDT': base_form.findField('TubDiagGeneralForm_setDT').getValue(),
									'Diag_id': base_form.findField('Diag_id').getValue(),
									'Diag_Name': base_form.findField('Diag_id').getRawValue()
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
		sw.Promed.swMorbusTubDiagGeneralFormWindow.superclass.show.apply(this, arguments);
		
		var that = this;
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
		
		this.center();

		var base_form = this.FormPanel.getForm();
		base_form.reset();

		this.action = null;
		this.callback = Ext.emptyFn;
		this.formMode = 'remote';
		this.formStatus = 'edit';
		this.onHide = Ext.emptyFn;
		
		if (arguments[0].TubDiagGeneralForm_id) 
			this.TubDiagGeneralForm_id = arguments[0].TubDiagGeneralForm_id;
		else 
			this.TubDiagGeneralForm_id = null;
			
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
			if ( ( this.TubDiagGeneralForm_id ) && ( this.TubDiagGeneralForm_id > 0 ) )
				this.action = "edit";
			else 
				this.action = "add";
		}
		
		base_form.setValues(arguments[0].formParams);
		
		this.getLoadMask().show();
		switch (this.action) 
		{
			case 'add':
				this.setTitle(this.winTitle +lang['_dobavlenie']);
				this.setFieldsDisabled(false);
				break;
			case 'edit':
				this.setTitle(this.winTitle +lang['_redaktirovanie']);
				this.setFieldsDisabled(false);
				break;
			case 'view':
				this.setTitle(this.winTitle +lang['_prosmotr']);
				this.setFieldsDisabled(true);
				break;
		}
		if(this.action != 'add' && this.formMode == 'remote') {
			Ext.Ajax.request({
				failure:function () {
					sw.swMsg.alert(lang['oshibka'], lang['ne_udalos_poluchit_dannyie_s_servera']);
					that.getLoadMask().hide();
				},
				params:{
					TubDiagGeneralForm_id: that.TubDiagGeneralForm_id
				},
				success: function (response) {
					that.getLoadMask().hide();
					var result = Ext.util.JSON.decode(response.responseText);
					if (!result[0]) { return false; }
					base_form.setValues(result[0]);
					if(base_form.findField('Diag_id').getStore().data.length == 0){
						var diag = result[0].Diag_id;
						var where = " where Diag_id = "+diag;
						base_form.findField('Diag_id').getStore().load({
							params:{where:where},
							callback:function(){base_form.findField('Diag_id').setValue(diag);}
						});
					}
					base_form.findField('TubDiagGeneralForm_setDT').focus(true,200);
				},
				url:'/?c=MorbusTub&m=loadMorbusTubDiagGeneralForm'
			});				
		} else {
			this.getLoadMask().hide();
			base_form.findField('TubDiagGeneralForm_setDT').focus(true,200);
		}
		
	},	
	initComponent: function() 
	{
		
		this.FormPanel = new Ext.form.FormPanel(
		{	
			autoScroll: true,
			frame: true,
			region: 'north',
			bodyStyle: 'padding: 5px',
			autoHeight: false,
			labelAlign: 'right',
			labelWidth: 200,
			items: 
			[{
				name: 'TubDiagGeneralForm_id',
				xtype: 'hidden'
			}, {
				name: 'MorbusTub_id',
				xtype: 'hidden'
			}, {
				fieldLabel: lang['data_vyiyavleniya'],
				name: 'TubDiagGeneralForm_setDT',
				xtype: 'swdatefield',
				plugins: [new Ext.ux.InputTextMask('99.99.9999', false)]
			}, {
				fieldLabel: lang['diagnoz'],
				hiddenName: 'Diag_id',
				changeDisabled: false,
				canEdit: true,
				anchor:'100%',
				MorbusType_SysNick:'tub',
				xtype: 'swdiagcombo',
				baseFilterFn: function(rec){
            		if(typeof rec.get == 'function'){
            			if(rec.get('DiagLevel_id') == '1'){
            				return (rec.get('Diag_Code').substr(0, 3) == 'A00');
            			} else {
            				return (rec.get('Diag_Code').substr(0, 3) >= 'A15' && rec.get('Diag_Code').substr(0, 3) <= 'A19');
            			}
            		} else {
            			if(rec.attributes.DiagLevel_id == '1'){
            				return (rec.attributes.Diag_Code.substr(0, 3) == 'A00');
            			} else {
            				return (rec.attributes.Diag_Code.substr(0, 3) >= 'A15' && rec.attributes.Diag_Code.substr(0, 3) <= 'A19');
            			}
            		}
            	}
			}],
			reader: new Ext.data.JsonReader(
			{
				success: Ext.emptyFn
			}, 
			[
				{name: 'TubDiagGeneralForm_id'},
				{name: 'MorbusTub_id'},
				{name: 'TubDiagGeneralForm_setDT'},
				{name: 'Diag_id'}
			]),
			url: '/?c=MorbusTub&m=saveTubDiagGeneralForm'
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
		sw.Promed.swMorbusTubDiagGeneralFormWindow.superclass.initComponent.apply(this, arguments);
	}
});