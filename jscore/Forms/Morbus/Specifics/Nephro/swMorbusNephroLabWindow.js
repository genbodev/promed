/**
 * swMorbusNephroLabWindow - Лабораторные исследования.
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * @package      Nephro
 * @access       public
 * @copyright    Copyright (c) 2009-2014 Swan Ltd.
 * @author       Alexander Permyakov
 * @version      11.2014
 */
sw.Promed.swMorbusNephroLabWindow = Ext.extend(sw.Promed.BaseForm, 
{
	action: null,
	winTitle: lang['laboratornyie_issledovaniya'],
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
        var me = this;
		if ( me.formStatus == 'save' ) {
			return false;
		}

        me.formStatus = 'save';
		
		var form = me.FormPanel;
		var base_form = form.getForm();

		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
                    me.formStatus = 'edit';
					form.getFirstInvalidEl().focus(false);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		var loadMask = new Ext.LoadMask(me.getEl(), {msg: "Подождите, идет сохранение..."});
		loadMask.show();
		
		var params = {};
		var data = {};

		switch ( this.formMode ) {
			case 'local':
				data.BaseData = {
					'MorbusNephroLab_id': base_form.findField('MorbusNephroLab_id').getValue(),
					'MorbusNephroLab_Date': base_form.findField('MorbusNephroLab_Date').getValue(),
                    'Rate_id': base_form.findField('Rate_id').getValue(),
					'Rate_ValueStr': base_form.findField('Rate_ValueStr').getValue(),
                    'RateType_id': base_form.findField('RateType_id').getValue(),
					'RateType_Name': base_form.findField('RateType_id').getRawValue()
				};
                me.callback(data);
                me.formStatus = 'edit';
				loadMask.hide();
                me.hide();
			break;
			case 'remote':
				base_form.submit({
					failure: function(result_form, action) {
                        me.formStatus = 'edit';
						loadMask.hide();
						if ( action.result ) {
							if ( action.result.Error_Msg ) {
								sw.swMsg.alert(lang['oshibka'], action.result.Error_Msg);
							}
							else {
								sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_1]']);
							}
						}
					},
					params: params,
					success: function(result_form, action) {
                        me.formStatus = 'edit';
						loadMask.hide();
						if ( action.result ) {
							if ( action.result.MorbusNephroLab_id > 0 ) {
								base_form.findField('MorbusNephroLab_id').setValue(action.result.MorbusNephroLab_id);

								data.BaseData = {
									'MorbusNephroLab_id': base_form.findField('MorbusNephroLab_id').getValue(),
									'MorbusNephroLab_Date': base_form.findField('MorbusNephroLab_Date').getValue(),
                                    'Rate_id': base_form.findField('Rate_id').getValue(),
									'Rate_ValueStr': base_form.findField('Rate_ValueStr').getValue(),
									'RateType_id': base_form.findField('RateType_id').getValue(),
									'RateType_Name': base_form.findField('RateType_id').getRawValue()
								};
                                me.callback(data);
                                me.hide();
							} else {
								if ( action.result.Error_Msg ) {
									sw.swMsg.alert(lang['oshibka'], action.result.Error_Msg);
								}
								else {
									sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_3]']);
								}
							}
						} else {
							sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_2]']);
						}
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
		sw.Promed.swMorbusNephroLabWindow.superclass.show.apply(this, arguments);
		
		var that = this;
		if (!arguments[0] || !arguments[0].formParams) {
			sw.swMsg.show(
			{
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.ERROR,
				msg: lang['oshibka_otkryitiya_formyi_ne_ukazanyi_nujnyie_vhodnyie_parametryi'],
				title: lang['oshibka'],
				fn: function() {
                    that.hide();
				}
			});
		}
		this.focus();
		
		this.center();

		var base_form = this.FormPanel.getForm();
		base_form.reset();

		this.formMode = 'remote';
		this.formStatus = 'edit';
        this.action = arguments[0].action || null;
        this.MorbusNephroLab_id = arguments[0].MorbusNephroLab_id || null;
        this.owner = arguments[0].owner || null;
        this.callback = arguments[0].callback || Ext.emptyFn;
        this.onHide = arguments[0].onHide || Ext.emptyFn;
		if ( arguments[0].formMode
            && typeof arguments[0].formMode == 'string'
            && arguments[0].formMode.inlist([ 'local', 'remote' ])
        ) {
			this.formMode = arguments[0].formMode;
		}
		if (!this.action) {
            if ( ( this.MorbusNephroLab_id ) && ( this.MorbusNephroLab_id > 0 ) )
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
		if (this.action != 'add' && this.formMode == 'remote') {
			Ext.Ajax.request({
				failure:function () {
					sw.swMsg.alert(lang['oshibka'], lang['ne_udalos_poluchit_dannyie_s_servera']);
					that.getLoadMask().hide();
				},
				params:{
					MorbusNephroLab_id: that.MorbusNephroLab_id
				},
				success: function (response) {
					that.getLoadMask().hide();
					var result = Ext.util.JSON.decode(response.responseText);
					if (!result[0]) { return false; }
					base_form.setValues(result[0]);
					base_form.findField('MorbusNephroLab_Date').focus(true,200);
				},
				url:'/?c=MorbusNephro&m=doLoadEditFormMorbusNephroLab'
			});				
		} else {
			this.getLoadMask().hide();
			base_form.findField('MorbusNephroLab_Date').focus(true,200);
		}
	},	
	initComponent: function() 
	{
		var me = this;
		this.FormPanel = new Ext.form.FormPanel(
		{	
			autoScroll: true,
			frame: true,
			region: 'north',
			bodyStyle: 'padding: 5px',
			autoHeight: false,
			labelAlign: 'right',
			labelWidth: 120,
			items: 
			[{
				name: 'MorbusNephroLab_id',
				xtype: 'hidden'
			}, {
				name: 'MorbusNephro_id',
				xtype: 'hidden'
			}, {
                name: 'Rate_id',
                xtype: 'hidden'
            }, {
				fieldLabel: lang['data'],
				name: 'MorbusNephroLab_Date',
				allowBlank: false,
				xtype: 'swdatefield',
				plugins: [new Ext.ux.InputTextMask('99.99.9999', false)]
			}, {
				fieldLabel: lang['pokazatel'],
				isDinamic: 1,
				allowBlank: false,
				anchor:'100%',
				xtype: 'swnephroratetypecombo'
			}, {
				name: 'Rate_ValueStr',
                allowBlank: false,
				fieldLabel: lang['znachenie'],
				width: 150,
				maxLength: 50,
				xtype: 'textfield'
			}],
			reader: new Ext.data.JsonReader(
			{
				success: Ext.emptyFn
			}, 
			[
				{name: 'MorbusNephroLab_id'},
                {name: 'MorbusNephro_id'},
                {name: 'Rate_id'},
				{name: 'MorbusNephroLab_Date'},
				{name: 'Rate_ValueStr'},
				{name: 'RateType_id'}
			]),
			url: '/?c=MorbusNephro&m=doSaveMorbusNephroLab'
		});
		Ext.apply(this, 
		{
			buttons: 
			[{
				handler: function() {
                    me.doSave();
				},
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
                    me.hide();
				},
				iconCls: 'cancel16',
				text: BTN_FRMCANCEL
			}],
			items: [this.FormPanel]
		});
		sw.Promed.swMorbusNephroLabWindow.superclass.initComponent.apply(this, arguments);
	}
});