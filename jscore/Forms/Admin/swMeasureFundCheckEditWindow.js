/**
* swMeasureFundCheckEditWindow - окно редактирования/добавления свидетельства о проверке.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2009-2014 Swan Ltd.
* @author       Samir Abakhri
* @version      07.08.2014
*/

sw.Promed.swMeasureFundCheckEditWindow = Ext.extend(sw.Promed.BaseForm, 
{
	action: null,
	autoHeight: true,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	draggable: true,
	split: true,
	width: 500,
	layout: 'form',
	id: 'MeasureFundCheckEditWindow',
	listeners: 
	{
		hide: function() 
		{
			this.onHide();
		}
	},
	modal: true,
	onHide: Ext.emptyFn,
	plain: true,
	resizable: false,
    doSave: function()
	{
        var _this = this,
		    form = this.findById('MeasureFundCheckEditForm'),
            base_form = form.getForm(),
            data = {};

        base_form.findField('MeasureFundCheck_Number').setValue(base_form.findField('MeasureFundCheck_Number').getValue().trim());

		if ( !form.getForm().isValid() )
		{
			sw.swMsg.show(
			{
				buttons: Ext.Msg.OK,
				fn: function()
				{
					form.getFirstInvalidEl().focus(true);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
        
        if (base_form.findField('MeasureFundCheck_setDate').getValue() > base_form.findField('MeasureFundCheck_endDate').getValue()){
            sw.swMsg.alert(lang['soobschenie'], lang['data_okonchaniya_svidetelstva_ne_mojet_byit_bolshe_datyi_nachala_sohranenie_nevozmojno']);
            return false;
        }

        if  (!!_this.deniedMeasureFundCheckList) {
            for (var $i = 0;$i < _this.deniedMeasureFundCheckList.length; $i++ ) {

                if ( _this.deniedMeasureFundCheckList[$i] - base_form.findField('MeasureFundCheck_setDate').getValue() == 0 ) {
                    sw.swMsg.show(
                    {
                        buttons: Ext.Msg.OK,
                        icon: Ext.Msg.WARNING,
                        msg: lang['svidetelstvo_o_proverke_na_ukazannuyu_datu_uje_suschestvuet'],
                        title: ERR_INVFIELDS_TIT
                    });
                    return false;
                }
            }
        }

        data.deniedMeasureFundCheckData = {
            'MeasureFundCheck_id': base_form.findField('MeasureFundCheck_id').getValue(),
            'MeasureFundCheck_Number': base_form.findField('MeasureFundCheck_Number').getValue(),
            'MeasureFundCheck_setDate': base_form.findField('MeasureFundCheck_setDate').getValue(),
            'MeasureFundCheck_endDate': base_form.findField('MeasureFundCheck_endDate').getValue()
        };

        this.formStatus = 'edit';
        this.callback(data);
        this.hide();
		return true;
	},
	enableEdit: function(enable)
	{
        var form = this.findById('MeasureFundCheckEditForm');

        this.lists = [];
        this.editFields = [];

        this.getFieldsLists(form, {
            needConstructComboLists: true,
            needConstructEditFields: true
        });

        if (enable) {

            (this.editFields).forEach(function(rec){
                rec.enable();
            });

            this.buttons[0].enable();
        } else {

            (this.editFields).forEach(function(rec){
                rec.disable();
            });

            this.buttons[0].disable();
        }

	},
	show: function() 
	{
		sw.Promed.swMeasureFundCheckEditWindow.superclass.show.apply(this, arguments);
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
		this.findById('MeasureFundCheckEditForm').getForm().reset();
		this.callback = Ext.emptyFn;
		this.onHide = Ext.emptyFn;

		if (arguments[0].MeasureFundCheck_id)
			this.MeasureFundCheck_id = arguments[0].MeasureFundCheck_id;
		else
			this.MeasureFundCheck_id = null;

		if (arguments[0].deniedMeasureFundCheckList) {
            this.deniedMeasureFundCheckList = arguments[0].deniedMeasureFundCheckList;
        }
		else
			this.deniedMeasureFundCheckList = null;

		if (arguments[0].callback) 
		{
			this.callback = arguments[0].callback;
		}
		
		if (arguments[0].owner) 
		{
			this.owner = arguments[0].owner;
		}
		
		if (arguments[0].onHide) 
		{
			this.onHide = arguments[0].onHide;
		}
		
		if (arguments[0].action) 
		{
			this.action = arguments[0].action;
		}
		else 
		{
			if ( ( this.MeasureFundCheck_id ) && ( this.MeasureFundCheck_id > 0 ) )
				this.action = "edit";
			else 
				this.action = "add";
		}
		
		var form = this.findById('MeasureFundCheckEditForm');
		form.getForm().setValues(arguments[0].formParams);
		
		var loadMask = new Ext.LoadMask(this.getEl(),{msg: LOAD_WAIT});
		loadMask.show();
		switch (this.action) 
		{
			case 'add':
				this.setTitle(lang['svidetelstvo_o_proverke_dobavlenie']);
				this.enableEdit(true);
				loadMask.hide();
				form.getForm().clearInvalid();
				break;
			case 'edit':
				this.setTitle(lang['svidetelstvo_o_proverke_redaktirovanie']);
				this.enableEdit(true);
				break;
			case 'view':
				this.setTitle(lang['svidetelstvo_o_proverke_prosmotr']);
				this.enableEdit(false);
				break;
		}

		loadMask.hide();
		if ( this.action != 'view' )
			form.getForm().findField('MeasureFundCheck_Number').focus(true, 100);
		else
			this.buttons[3].focus();
	},
	
	initComponent: function() 
	{
        var _this = this;

		this.MeasureFundCheckEditForm = new Ext.form.FormPanel(
		{
			autoHeight: true,
			bodyStyle: 'padding: 5px',
			border: false,
			buttonAlign: 'left',
			frame: true,
			id: 'MeasureFundCheckEditForm',
			labelAlign: 'right',
			labelWidth: 250,
			items: 
			[{
				name: 'MeasureFundCheck_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'MedProductCard_id',
				value: 0,
				xtype: 'hidden'
			}, {
                allowBlank: false,
                fieldLabel: lang['nomer_svidetelstva_o_proverke'],
                name: 'MeasureFundCheck_Number',
                width: 200,
                tabIndex: 1,
                xtype: 'textfield'
            }, {
                allowBlank: false,
                xtype: 'swdatefield',
                fieldLabel: lang['data_svidetelstva_o_proverke'],
                format: 'd.m.Y',
                tabIndex: 5,
                name: 'MeasureFundCheck_setDate',
                plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ]
            }, {
                allowBlank: false,
                xtype: 'swdatefield',
                fieldLabel: lang['srok_deystviya_svidetelstva'],
                format: 'd.m.Y',
                tabIndex: 10,
                name: 'MeasureFundCheck_endDate',
                plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ]
            }],
			keys: 
			[{
				alt: true,
				fn: function(inp, e) 
				{
					switch (e.getKey()) 
					{
						case Ext.EventObject.C:
							if (this.action != 'view') 
							{
								this.doSave(false);
							}
							break;
						case Ext.EventObject.J:
							this.hide();
							break;
					}
				},
				key: [ Ext.EventObject.C, Ext.EventObject.J ],
				scope: this,
				stopEvent: true
			}],
			reader: new Ext.data.JsonReader(
			{
				success: function() 
				{ 
					//
				}
			}, 
			[
				{ name: 'MeasureFundCheck_id' },
				{ name: 'MedProductCard_id' },
				{ name: 'MeasureFundCheck_Number' },
				{ name: 'MeasureFundCheck_setDate' },
				{ name: 'MeasureFundCheck_endDate' }
			]),
			url: '/?c=LpuPassport&m=saveMeasureFundCheck'
		});
		Ext.apply(this, 
		{
			buttons: 
			[{
				handler: function() 
				{
					_this.doSave();
				},
				iconCls: 'save16',
				tabIndex: TABINDEX_ORSEW + 3,
				text: BTN_FRMSAVE
			}, 
			{
				text: '-'
			},
			HelpButton(this),
			{
				handler: function() 
				{
					_this.hide();
				},
				iconCls: 'cancel16',
				tabIndex: TABINDEX_ORSEW + 4,
				text: BTN_FRMCANCEL
			}],
			items: [this.MeasureFundCheckEditForm]
		});
		sw.Promed.swMeasureFundCheckEditWindow.superclass.initComponent.apply(this, arguments);
	}
});