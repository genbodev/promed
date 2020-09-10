/**
* swMorbusTubStudyDrugResultWindow - Тест на лекарственную чувствительность.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2009-2010 Swan Ltd.
* @version      28.12.2012
*/

sw.Promed.swMorbusTubStudyDrugResultWindow = Ext.extend(sw.Promed.BaseForm, 
{
	action: null,
	winTitle: lang['test_na_lekarstvennuyu_ustoichivost'],
	autoHeight: true,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	draggable: true,
	formMode: 'remote',
	formStatus: 'edit',
	modal: true,
    isMDR: false,
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
					'MorbusTubStudyDrugResult_id': base_form.findField('MorbusTubStudyDrugResult_id').getValue(),
					'MorbusTubStudyResult_id': base_form.findField('MorbusTubStudyResult_id').getValue(),
					'MorbusTubStudyDrugResult_setDT': base_form.findField('MorbusTubStudyDrugResult_setDT').getValue(),
					'MorbusTubStudyDrugResult_IsResult': base_form.findField('MorbusTubStudyDrugResult_IsResult').getValue(),
					'MorbusTubStudyDrugResult_IsResult_Name': base_form.findField('MorbusTubStudyDrugResult_IsResult').getRawValue(),
					'TubDrug_id': base_form.findField('TubDrug_id').getValue(),
					'TubDrug_Name': base_form.findField('TubDrug_id').getRawValue()
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
							if ( action.result.MorbusTubStudyDrugResult_id > 0 ) {
								base_form.findField('MorbusTubStudyDrugResult_id').setValue(action.result.MorbusTubStudyDrugResult_id);

								data.BaseData = {
									'MorbusTubStudyDrugResult_id': base_form.findField('MorbusTubStudyDrugResult_id').getValue(),
									'MorbusTubStudyResult_id': base_form.findField('MorbusTubStudyResult_id').getValue(),
									'MorbusTubStudyDrugResult_setDT': base_form.findField('MorbusTubStudyDrugResult_setDT').getValue(),
									'MorbusTubStudyDrugResult_IsResult': base_form.findField('MorbusTubStudyDrugResult_IsResult').getValue(),
									'MorbusTubStudyDrugResult_IsResult_Name':  base_form.findField('MorbusTubStudyDrugResult_IsResult').getRawValue(),
									'TubDrug_id': base_form.findField('TubDrug_id').getValue(),
									'TubDrug_Name': base_form.findField('TubDrug_id').getRawValue()
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
		sw.Promed.swMorbusTubStudyDrugResultWindow.superclass.show.apply(this, arguments);
		
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
		this.formMode = 'remote';
		this.formStatus = 'edit';
		this.onHide = Ext.emptyFn;
		
		this.MorbusTubStudyDrugResult_id = arguments[0].MorbusTubStudyDrugResult_id || null;
        this.callback = arguments[0].callback || Ext.emptyFn;
        this.isMDR = arguments[0].isMDR || false;
		
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
			if ( ( this.MorbusTubStudyDrugResult_id ) && ( this.MorbusTubStudyDrugResult_id > 0 ) )
				this.action = "edit";
			else 
				this.action = "add";
		}
		var tubdrug_combo = base_form.findField('TubDrug_id');
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
					MorbusTubStudyDrugResult_id: that.MorbusTubStudyDrugResult_id
				},
				success: function (response) {
					that.getLoadMask().hide();
					var result = Ext.util.JSON.decode(response.responseText);
					if (!result[0]) { return false; }
					base_form.setValues(result[0]);
                    if (tubdrug_combo.isMDR != that.isMDR) {
                        tubdrug_combo.isMDR = that.isMDR;
                        tubdrug_combo.reload();
                    }
					base_form.findField('MorbusTubStudyDrugResult_setDT').focus(true,200);
				},
				url:'/?c=MorbusTub&m=loadMorbusTubStudyDrugResult'
			});				
		} else {
			this.getLoadMask().hide();
            if (tubdrug_combo.isMDR != this.isMDR) {
                tubdrug_combo.isMDR = this.isMDR;
                tubdrug_combo.reload();
            }
			base_form.findField('MorbusTubStudyDrugResult_setDT').focus(true,200);
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
				name: 'MorbusTubStudyDrugResult_id',
				xtype: 'hidden'
			}, {
				name: 'MorbusTubStudyResult_id',
				xtype: 'hidden'
			}, {
				fieldLabel: lang['data'],
				name: 'MorbusTubStudyDrugResult_setDT',
				allowBlank: false,
				xtype: 'swdatefield',
				plugins: [new Ext.ux.InputTextMask('99.99.9999', false)]
			}, {
                fieldLabel: lang['preparat'],
                sortField:'TubDrug_Code',
                comboSubject: 'TubDrug',
                anchor:'100%',
                hiddenName: 'TubDrug_id',
                xtype: 'swtubcommonsprcombo',
                isMDR: false,
				allowBlank: false
			}, {
				fieldLabel: lang['rezultat_testa'],
				xtype: 'swyesnocombo',
				width: 80,
				hiddenName: 'MorbusTubStudyDrugResult_IsResult'
			}],
			reader: new Ext.data.JsonReader(
			{
				success: Ext.emptyFn
			}, 
			[
				{name: 'MorbusTubStudyDrugResult_id'},
				{name: 'MorbusTubStudyResult_id'},
				{name: 'MorbusTubStudyDrugResult_setDT'},
				{name: 'MorbusTubStudyDrugResult_IsResult'},
				{name: 'TubDrug_id'}
			]),
			url: '/?c=MorbusTub&m=saveMorbusTubStudyDrugResult'
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
		sw.Promed.swMorbusTubStudyDrugResultWindow.superclass.initComponent.apply(this, arguments);
	}
});