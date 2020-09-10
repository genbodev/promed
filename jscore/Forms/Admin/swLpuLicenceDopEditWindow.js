/**
* swLpuLicenceDopEditWindow - окно редактирования/добавления приложений к лицензии ЛПУ.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2009-2014 Swan Ltd.
* @author       Samir Abakhri
* @version      05.08.2014
*/

sw.Promed.swLpuLicenceDopEditWindow = Ext.extend(sw.Promed.BaseForm, 
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
	id: 'LpuLicenceDopEditWindow',
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
        var _this = this;
		var form = this.findById('LpuLicenceDopEditForm');
        var base_form = form.getForm();

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

        /*if (!!_this.deniedLicsOperationList) {
            for ($i = 0;$i < _this.deniedLicsOperationList.length; $i++ ) {
                if ((_this.deniedLicsOperationList[$i] - base_form.findField('LpuLicenceDop_Num').getValue()) == 0 && (_this.deniedLicenceOperationDateList[$i] - base_form.findField('LpuLicenceDop_setDate').getValue()) == 0) {
                    sw.swMsg.show(
                    {
                        buttons: Ext.Msg.OK,
                        icon: Ext.Msg.WARNING,
                        msg: lang['operatsiya_nad_litsenziey_s_dannyimi_usloviyami_uje_doabvlena'],
                        title: ERR_INVFIELDS_TIT
                    });
                    return false;
                }
            }
        }*/

        var loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT_SAVE });
        loadMask.show();

        var data = {};

        data.LpuLicenceDopData = {
            'LpuLicenceDop_id': base_form.findField('LpuLicenceDop_id').getValue(),
            'LpuLicenceDop_Num': base_form.findField('LpuLicenceDop_Num').getValue(),
            'LpuLicenceDop_setDate': base_form.findField('LpuLicenceDop_setDate').getValue(),
            'LpuLicence_id': base_form.findField('LpuLicence_id').getValue()
        };

        this.formStatus = 'edit';
        loadMask.hide();

        this.callback(data);
        this.hide();
		return true;
	},
	/*enableEdit: function(enable)
	{
        var form = this.findById('LpuLicenceDopEditForm');
		if (enable)
		{
			form.getForm().findField('LpuLicenceDop_Num').enable();
			form.getForm().findField('LpuLicenceDop_setDate').enable();
			this.buttons[0].enable();
		}
		else 
		{
			form.getForm().findField('LpuLicenceDop_Num').disable();
			form.getForm().findField('LpuLicenceDop_setDate').disable();
			this.buttons[0].disable();
		}
	},*/
	show: function() 
	{
		sw.Promed.swLpuLicenceDopEditWindow.superclass.show.apply(this, arguments);
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
		this.findById('LpuLicenceDopEditForm').getForm().reset();
		this.callback = Ext.emptyFn;
		this.onHide = Ext.emptyFn;

		if (arguments[0].LpuLicenceDop_id)
			this.LpuLicenceDop_id = arguments[0].LpuLicenceDop_id;
		else
			this.LpuLicenceDop_id = null;

		/*if (arguments[0].deniedLicsOperationList) {
            this.deniedLicsOperationList = arguments[0].deniedLicsOperationList;
        }
		else
			this.deniedLicsOperationList = null;

		if (arguments[0].deniedLicenceOperationDateList) {
            this.deniedLicenceOperationDateList = arguments[0].deniedLicenceOperationDateList;
        }
		else
			this.deniedLicenceOperationDateList = null;*/

		if (arguments[0].LpuLicence_id)
			this.LpuLicence_id = arguments[0].LpuLicence_id;
		else 
			this.LpuLicence_id = null;

		if (arguments[0].LpuLicenceDop_Num)
			this.LpuLicenceDop_Num = arguments[0].LpuLicenceDop_Num;
		else
			this.LpuLicenceDop_Num = null;

        if (arguments[0].LpuLicenceDop_setDate)
			this.LpuLicenceDop_setDate = arguments[0].LpuLicenceDop_setDate;
		else
			this.LpuLicenceDop_setDate = null;
			
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
			if ( ( this.OrgRSchet_id ) && ( this.OrgRSchet_id > 0 ) )
				this.action = "edit";
			else 
				this.action = "add";
		}
		
		var form = this.findById('LpuLicenceDopEditForm');
		form.getForm().setValues(arguments[0].formParams);
		
		/*var loadMask = new Ext.LoadMask(this.getEl(),{msg: LOAD_WAIT});
		loadMask.show();*/
		switch (this.action) 
		{
			case 'add':
				this.setTitle(lang['prilojenie_k_litsenzii_dobavlenie']);
				this.enableEdit(true);
				form.getForm().clearInvalid();
                form.getForm().findField('LpuLicenceDop_setDate').setValue(new Date());
				break;
			case 'edit':
				this.setTitle(lang['prilojenie_k_litsenzii_redaktirovanie']);
				this.enableEdit(true);
				break;
			case 'view':
				this.setTitle(lang['prilojenie_k_litsenzii_prosmotr']);
				this.enableEdit(false);
				break;
		}
		
		/*if (this.action != 'add')
		{
			form.getForm().load(
			{
				params: 
				{
					LpuLicenceDop_id: current_window.LpuLicenceDop_id,
                    LpuLicence_id: current_window.LpuLicence_id,
                    LpuLicenceDop_setDate: current_window.LpuLicenceDop_setDate,
                    LpuLicenceDop_Num: current_window.LpuLicenceDop_Num
				},
				failure: function() 
				{
					loadMask.hide();
					sw.swMsg.show(
					{
						buttons: Ext.Msg.OK,
						fn: function() 
						{
							current_window.hide();
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
				url: '/?c=LpuPassport&m=loadLpuLicenceDop'
			});
		}*/
		if ( this.action != 'view' )
			form.getForm().findField('LpuLicenceDop_Num').focus(true, 100);
		else
			this.buttons[3].focus();
	},
	
	initComponent: function() 
	{
		// Форма с полями 
		this.LpuLicenceDopEditForm = new Ext.form.FormPanel(
		{
			autoHeight: true,
			bodyStyle: 'padding: 5px',
			border: false,
			buttonAlign: 'left',
			frame: true,
			id: 'LpuLicenceDopEditForm',
			labelAlign: 'right',
			labelWidth: 150,
			items: 
			[{
				name: 'LpuLicenceDop_id',
				value: 0,
				xtype: 'hidden'
			},{
				name: 'LpuLicence_id',
				value: 0,
				xtype: 'hidden'
			}, {
                anchor: '100%',
                allowBlank: false,
                fieldLabel: lang['nomer_prilojeniya'],
                name: 'LpuLicenceDop_Num',
                tabIndex: TABINDEX_LPEEW + 1,
                xtype: 'textfield'
            }, {
                fieldLabel: lang['data_vyidachi_prilojeniya'],
                xtype: 'swdatefield',
                plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
                format: 'd.m.Y',
                //disabled: true,
                name: 'LpuLicenceDop_setDate',
                tabIndex: TABINDEX_LPEEW + 5
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
				{ name: 'LpuLicenceDop_id' },
				{ name: 'LpuLicenceDop_Num' },
				{ name: 'LpuLicence_id' },
				{ name: 'LpuLicenceDop_setDate' }
			]),
			url: '/?c=LpuPassport&m=saveLpuLicenceDop'
		});
		Ext.apply(this, 
		{
			buttons: 
			[{
				handler: function() 
				{
					this.ownerCt.doSave();
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
					this.ownerCt.hide();
				},
				iconCls: 'cancel16',
				tabIndex: TABINDEX_ORSEW + 4,
				text: BTN_FRMCANCEL
			}],
			items: [this.LpuLicenceDopEditForm]
		});
		sw.Promed.swLpuLicenceDopEditWindow.superclass.initComponent.apply(this, arguments);
	}
});