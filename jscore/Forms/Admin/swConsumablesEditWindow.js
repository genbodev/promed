/**
* swConsumablesEditWindow - окно редактирования/добавления расходных материалов.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2009-2014 Swan Ltd.
* @author       Samir Abakhri
* @version      24.05.2014
*/

sw.Promed.swConsumablesEditWindow = Ext.extend(sw.Promed.BaseForm,
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
	id: 'ConsumablesEditWindow',
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
		    form = this.findById('ConsumablesEditForm'),
            base_form = form.getForm(),
            data = {};

        base_form.findField('Consumables_Name').setValue(base_form.findField('Consumables_Name').getValue().trim());

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

        if  (!!_this.deniedConsumablesList) {
            for (var $i = 0;$i < _this.deniedConsumablesList.length; $i++ ) {
                if (_this.deniedConsumablesList[$i] == base_form.findField('Consumables_Name').getValue()) {
                    sw.swMsg.show(
                    {
                        buttons: Ext.Msg.OK,
                        icon: Ext.Msg.WARNING,
                        msg: lang['rashodnyiy_material_s_dannyim_naimenovaniem_uje_dobavlen'],
                        title: ERR_INVFIELDS_TIT
                    });
                    return false;
                }
            }
        }

        data.deniedConsumablesData = {
            'Consumables_id': base_form.findField('Consumables_id').getValue(),
            'Consumables_Name': base_form.findField('Consumables_Name').getValue()
        };

        this.formStatus = 'edit';
        this.callback(data);
        this.hide();
		return true;
	},
	enableEdit: function(enable)
	{
        var form = this.findById('ConsumablesEditForm');
		if (enable)
		{
			form.getForm().findField('Consumables_Name').enable();
			this.buttons[0].enable();
		}
		else 
		{
			form.getForm().findField('Consumables_Name').disable();
			this.buttons[0].disable();
		}
	},
	show: function() 
	{
		sw.Promed.swConsumablesEditWindow.superclass.show.apply(this, arguments);
		var _this = this;
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
		this.findById('ConsumablesEditForm').getForm().reset();
		this.callback = Ext.emptyFn;
		this.onHide = Ext.emptyFn;

		if (arguments[0].Consumables_id)
			this.Consumables_id = arguments[0].Consumables_id;
		else
			this.Consumables_id = null;

		if (arguments[0].deniedConsumablesList) {
            this.deniedConsumablesList = arguments[0].deniedConsumablesList;
        }
		else
			this.deniedConsumablesList = null;

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
			if ( ( this.Consumables_id ) && ( this.Consumables_id > 0 ) )
				this.action = "edit";
			else 
				this.action = "add";
		}
		
		var form = this.findById('ConsumablesEditForm');
		form.getForm().setValues(arguments[0].formParams);
		
		var loadMask = new Ext.LoadMask(this.getEl(),{msg: LOAD_WAIT});
		loadMask.show();
		switch (this.action) 
		{
			case 'add':
				this.setTitle(lang['rashodnyiy_material_dobavlenie']);
				this.enableEdit(true);
				loadMask.hide();
				form.getForm().clearInvalid();
				break;
			case 'edit':
				this.setTitle(lang['rashodnyiy_material_redaktirovanie']);
				this.enableEdit(true);
				break;
			case 'view':
				this.setTitle(lang['rashodnyiy_material_prosmotr']);
				this.enableEdit(false);
				break;
		}
		
		/*if (this.action != 'add')
		{
			form.getForm().load(
			{
				params: 
				{
					Consumables_id: _this.Consumables_id
				},
				failure: function() 
				{
					loadMask.hide();
					sw.swMsg.show(
					{
						buttons: Ext.Msg.OK,
						fn: function() 
						{
							_this.hide();
						},
						icon: Ext.Msg.ERROR,
						msg: lang['oshibka_zaprosa_k_serveru_poprobuyte_povtorit_operatsiyu'],
						title: lang['oshibka']
					});
				},
				success: function() 
				{
					loadMask.hide();
				},
				url: '/?c=LpuPassport&m=loadConsumables'
			});
		}*/
		loadMask.hide();
		if ( this.action != 'view' )
			form.getForm().findField('Consumables_Name').focus(true, 100);
		else
			this.buttons[3].focus();
	},
	
	initComponent: function() 
	{
        var _this = this;
		// Форма с полями 
		this.ConsumablesEditForm = new Ext.form.FormPanel(
		{
			autoHeight: true,
			bodyStyle: 'padding: 5px',
			border: false,
			buttonAlign: 'left',
			frame: true,
			id: 'ConsumablesEditForm',
			labelAlign: 'right',
			labelWidth: 250,
			items: 
			[{
				name: 'Consumables_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'MedProductCard_id',
				value: 0,
				xtype: 'hidden'
			}, {
                allowBlank: false,
                fieldLabel: lang['naimenovanie_rashodnogo_materaiala'],
                name: 'Consumables_Name',
                width: 200,
                xtype: 'textfield'
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
				{ name: 'Consumables_id' },
				{ name: 'Consumables_Name' }
			]),
			url: '/?c=LpuPassport&m=saveConsumables'
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
			items: [this.ConsumablesEditForm]
		});
		sw.Promed.swConsumablesEditWindow.superclass.initComponent.apply(this, arguments);
	}
});