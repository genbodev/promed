/**
* swMorbusTubStudyMicrosResultWindow - Микроскопия.
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

sw.Promed.swMorbusTubStudyMicrosResultWindow = Ext.extend(sw.Promed.BaseForm, 
{
	action: null,
	winTitle: lang['mikroskopiya'],
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
					'MorbusTubStudyMicrosResult_id': base_form.findField('MorbusTubStudyMicrosResult_id').getValue(),
					'MorbusTubStudyMicrosResult_setDT': base_form.findField('MorbusTubStudyMicrosResult_setDT').getValue(),
					'MorbusTubStudyMicrosResult_NumLab': base_form.findField('MorbusTubStudyMicrosResult_NumLab').getValue(),
					'MorbusTubStudyMicrosResult_EdResult': base_form.findField('MorbusTubStudyMicrosResult_EdResult').getValue(),
					'TubMicrosResultType_id': base_form.findField('TubMicrosResultType_id').getValue(),
					'TubMicrosResultType_Name': base_form.findField('TubMicrosResultType_id').getRawValue()
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
							if ( action.result.MorbusTubStudyMicrosResult_id > 0 ) {
								base_form.findField('MorbusTubStudyMicrosResult_id').setValue(action.result.MorbusTubStudyMicrosResult_id);

								data.BaseData = {
									'MorbusTubStudyMicrosResult_id': base_form.findField('MorbusTubStudyMicrosResult_id').getValue(),
									'MorbusTubStudyMicrosResult_setDT': base_form.findField('MorbusTubStudyMicrosResult_setDT').getValue(),
									'MorbusTubStudyMicrosResult_NumLab': base_form.findField('MorbusTubStudyMicrosResult_NumLab').getValue(),
									'MorbusTubStudyMicrosResult_EdResult': base_form.findField('MorbusTubStudyMicrosResult_EdResult').getValue(),
									'TubMicrosResultType_id': base_form.findField('TubMicrosResultType_id').getValue(),
									'TubMicrosResultType_Name': base_form.findField('TubMicrosResultType_id').getRawValue()
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
		sw.Promed.swMorbusTubStudyMicrosResultWindow.superclass.show.apply(this, arguments);
		
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
		
		this.MorbusTubStudyMicrosResult_id = arguments[0].MorbusTubStudyMicrosResult_id || null;
		this.callback = arguments[0].callback || Ext.emptyFn;
		
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
			if ( ( this.MorbusTubStudyMicrosResult_id ) && ( this.MorbusTubStudyMicrosResult_id > 0 ) )
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
					MorbusTubStudyMicrosResult_id: that.MorbusTubStudyMicrosResult_id
				},
				success: function (response) {
					that.getLoadMask().hide();
					var result = Ext.util.JSON.decode(response.responseText);
					if (!result[0]) { return false; }
					base_form.setValues(result[0]);
					base_form.findField('MorbusTubStudyMicrosResult_setDT').focus(true,200);
				},
				url:'/?c=MorbusTub&m=loadMorbusTubStudyMicrosResult'
			});				
		} else {
			this.getLoadMask().hide();
			base_form.findField('MorbusTubStudyMicrosResult_setDT').focus(true,200);
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
				name: 'MorbusTubStudyMicrosResult_id',
				xtype: 'hidden'
			}, {
				name: 'MorbusTubStudyResult_id',
				xtype: 'hidden'
			}, {
				fieldLabel: lang['data'],
				name: 'MorbusTubStudyMicrosResult_setDT',
				allowBlank: false,
				xtype: 'swdatefield',
				plugins: [new Ext.ux.InputTextMask('99.99.9999', false)]
			}, {
				name: 'MorbusTubStudyMicrosResult_NumLab',
				fieldLabel: lang['№_obraztsa_mokrotyi'],
				width: 100,
				maxLength: 10,
				xtype: 'textfield'
			}, {
				fieldLabel: lang['rezultat'],
				hiddenName: 'TubMicrosResultType_id',
				allowBlank: false,
				anchor:'100%',
				xtype: 'swcommonsprcombo',
				sortField:'TubMicrosResultType_Code',
				typeCode: 'int',
				comboSubject: 'TubMicrosResultType'
			}, {
				name: 'MorbusTubStudyMicrosResult_EdResult',
				fieldLabel: lang['chislo_mikobakteriy'],
				width: 100,
				allowDecimals: true,
				allowNegative: false,
				xtype: 'numberfield'
			}],
			reader: new Ext.data.JsonReader(
			{
				success: Ext.emptyFn
			}, 
			[
				{name: 'MorbusTubStudyMicrosResult_id'},
				{name: 'MorbusTubStudyResult_id'},
				{name: 'MorbusTubStudyMicrosResult_setDT'},
				{name: 'MorbusTubStudyMicrosResult_NumLab'},
				{name: 'MorbusTubStudyMicrosResult_EdResult'},
				{name: 'TubMicrosResultType_id'}
			]),
			url: '/?c=MorbusTub&m=saveMorbusTubStudyMicrosResult'
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
		sw.Promed.swMorbusTubStudyMicrosResultWindow.superclass.initComponent.apply(this, arguments);
	}
});