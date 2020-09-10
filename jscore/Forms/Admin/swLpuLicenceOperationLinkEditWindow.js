/**
* swLpuLicenceOperationLinkEditWindow - окно редактирования/добавления операции с лицензией ЛПУ.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2009-2010 Swan Ltd.
* @author       Chebukin Alexander
* @version      17.01.2011
*/

sw.Promed.swLpuLicenceOperationLinkEditWindow = Ext.extend(sw.Promed.BaseForm, 
{
	action: null,
	autoHeight: true,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	draggable: true,
	split: true,
	width: 400,
	layout: 'form',
	id: 'LpuLicenceOperationLinkEditWindow',
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
		var form = this.findById('LpuLicenceOperationLinkEditForm');
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

        if  (!!_this.deniedLicsOperationList) {
            for ($i = 0;$i < _this.deniedLicsOperationList.length; $i++ ) {
                if ((_this.deniedLicsOperationList[$i] - base_form.findField('LicsOperation_id').getValue()) == 0 && (_this.deniedLicenceOperationDateList[$i] - base_form.findField('LpuLicenceOperationLink_Date').getValue()) == 0) {
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
        }

        var loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT_SAVE });
        loadMask.show();

        var data = new Object();

        data.LpuLicenceOperationLinkData = {
            'LpuLicenceOperationLink_id': base_form.findField('LpuLicenceOperationLink_id').getValue(),
            'LicsOperation_id': base_form.findField('LicsOperation_id').getValue(),
            'LpuLicenceOperationLink_Date': base_form.findField('LpuLicenceOperationLink_Date').getValue(),
            'LicsOperation_Name': base_form.findField('LicsOperation_id').getFieldValue('LicsOperation_Name'),
            'LpuLicence_id': base_form.findField('LpuLicence_id').getValue()
        };

        this.formStatus = 'edit';
        loadMask.hide();

        this.callback(data);
        this.hide();
		return true;
	},
	enableEdit: function(enable)
	{
        var form = this.findById('LpuLicenceOperationLinkEditForm');
		if (enable)
		{
			form.getForm().findField('LicsOperation_id').enable();
			form.getForm().findField('LpuLicenceOperationLink_Date').enable();
			this.buttons[0].show();
		}
		else 
		{
			form.getForm().findField('LicsOperation_id').disable();
			form.getForm().findField('LpuLicenceOperationLink_Date').disable();
			this.buttons[0].hide();
		}
	},
	show: function() 
	{
		sw.Promed.swLpuLicenceOperationLinkEditWindow.superclass.show.apply(this, arguments);

		if (!arguments[0] || !arguments[0].formParams)
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

		this.findById('LpuLicenceOperationLinkEditForm').getForm().reset();

		this.action = 'add';
		this.callback = Ext.emptyFn;
		this.formMode = 'local';
		this.onHide = Ext.emptyFn;

		if (arguments[0].deniedLicsOperationList) {
            this.deniedLicsOperationList = arguments[0].deniedLicsOperationList;
        }
		else
			this.deniedLicsOperationList = null;

		if (arguments[0].deniedLicenceOperationDateList) {
            this.deniedLicenceOperationDateList = arguments[0].deniedLicenceOperationDateList;
        }
		else
			this.deniedLicenceOperationDateList = null;

		if (arguments[0].callback && typeof arguments[0].callback == 'function' )
		{
			this.callback = arguments[0].callback;
		}
		
		if ( arguments[0].formMode && arguments[0].formMode == 'remote' ) {
			this.formMode = 'remote';
		}

		if (arguments[0].owner) 
		{
			this.owner = arguments[0].owner;
		}
		
		if (arguments[0].onHide && typeof arguments[0].onHide == 'function') 
		{
			this.onHide = arguments[0].onHide;
		}
		
		if (arguments[0].action) 
		{
			this.action = arguments[0].action;
		}
		
		var form = this.findById('LpuLicenceOperationLinkEditForm');
		form.getForm().setValues(arguments[0].formParams);
		
		var loadMask = new Ext.LoadMask(this.getEl(),{msg: LOAD_WAIT});
		loadMask.show();
		switch (this.action) 
		{
			case 'add':
				this.setTitle(lang['operatsiya_dobavlenie']);
				this.enableEdit(true);
				loadMask.hide();
				form.getForm().clearInvalid();
                form.getForm().findField('LpuLicenceOperationLink_Date').setValue(new Date());
				break;
			case 'edit':
				this.setTitle(lang['operatsiya_redaktirovanie']);
				this.enableEdit(true);
				break;
			case 'view':
				this.setTitle(lang['operatsiya_prosmotr']);
				this.enableEdit(false);
				break;
		}
		
		loadMask.hide();
		
		if ( this.action != 'view' )
			form.getForm().findField('LicsOperation_id').focus(true, 100);
		else
			this.buttons[3].focus();
	},
	
	initComponent: function() 
	{
		// Форма с полями 
		this.LpuLicenceOperationLinkEditForm = new Ext.form.FormPanel(
		{
			autoHeight: true,
			bodyStyle: 'padding: 5px',
			border: false,
			buttonAlign: 'left',
			frame: true,
			id: 'LpuLicenceOperationLinkEditForm',
			labelAlign: 'right',
			labelWidth: 100,
			items: 
			[{
				name: 'LpuLicenceOperationLink_id',
				value: 0,
				xtype: 'hidden'
			},{
				name: 'LpuLicence_id',
				value: 0,
				xtype: 'hidden'
			}, {
                anchor: '100%',
                allowBlank: false,
                comboSubject: 'LicsOperation',
                fieldLabel: lang['naimenovanie_operatsii'],
				loadParams: {
					params: {
						where: 'where LicsOperation_Code not in (1, 2, 3)'
					}
				},
                hiddenName: 'LicsOperation_id',
                tabIndex: TABINDEX_LPEEW + 1,
                xtype: 'swcommonsprcombo'
            }, {
                allowBlank: false,
                fieldLabel: lang['data_operatsii'],
                xtype: 'swdatefield',
                plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
                format: 'd.m.Y',
                //disabled: true,
                name: 'LpuLicenceOperationLink_Date',
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
				{ name: 'LpuLicenceOperationLink_id' },
				{ name: 'LicsOperation_id' },
				{ name: 'LpuLicence_id' },
				{ name: 'LpuLicenceOperationLink_Date' }
			]),
			url: '/?c=LpuPassport&m=saveLpuLicenceOperationLink'
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
			items: [this.LpuLicenceOperationLinkEditForm]
		});
		sw.Promed.swLpuLicenceOperationLinkEditWindow.superclass.initComponent.apply(this, arguments);
	}
});