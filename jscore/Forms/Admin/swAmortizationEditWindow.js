/**
* swAmortizationEditWindow - окно редактирования/добавления износа.
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

sw.Promed.swAmortizationEditWindow = Ext.extend(sw.Promed.BaseForm, 
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
	id: 'AmortizationEditWindow',
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
		    form = this.findById('AmortizationEditForm'),
            base_form = form.getForm(),
            data = {};

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

        if  (!!_this.deniedAmortizationList) {
            for (var $i = 0;$i < _this.deniedAmortizationList.length; $i++ ) {
                if ((_this.deniedAmortizationList[$i] - base_form.findField('Amortization_setDate').getValue()) == 0) {
                    sw.swMsg.show(
                    {
                        buttons: Ext.Msg.OK,
                        icon: Ext.Msg.WARNING,
                        msg: lang['na_dannuyu_datu_otsenki_uje_suschestvuyut_prametryi_iznosa'],
                        title: ERR_INVFIELDS_TIT
                    });
                    return false;
                }
            }
        }

        data.deniedAmortizationData = {
            'Amortization_id': base_form.findField('Amortization_id').getValue(),
            'Amortization_setDate': base_form.findField('Amortization_setDate').getValue(),
            'Amortization_WearPercent': base_form.findField('Amortization_WearPercent').getValue(),
            'Amortization_FactCost': base_form.findField('Amortization_FactCost').getValue(),
            'Amortization_ResidCost': base_form.findField('Amortization_ResidCost').getValue()
        };

        this.formStatus = 'edit';
        this.callback(data);
        this.hide();
		return true;
	},
	enableEdit: function(enable)
	{
        var form = this.findById('AmortizationEditForm');
		if (enable)
		{
			form.getForm().findField('Amortization_setDate').enable();
			form.getForm().findField('Amortization_FactCost').enable();
			form.getForm().findField('Amortization_WearPercent').enable();
			form.getForm().findField('Amortization_ResidCost').enable();
			this.buttons[0].enable();
		}
		else 
		{
			form.getForm().findField('Amortization_setDate').disable();
			form.getForm().findField('Amortization_FactCost').disable();
			form.getForm().findField('Amortization_WearPercent').disable();
			form.getForm().findField('Amortization_ResidCost').disable();
			this.buttons[0].disable();
		}
	},
	show: function() 
	{
		sw.Promed.swAmortizationEditWindow.superclass.show.apply(this, arguments);
		var _this = this;
		if (!arguments[0])
		{
			sw.swMsg.show(
			{
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.ERROR,
				msg: 'Ошибка открытия формы.<br/>Не указаны нужные входные параметры.',
				title: lang['oshibka'],
				fn: function() {
					this.hide();
				}
			});
		}
		this.focus();
		this.findById('AmortizationEditForm').getForm().reset();
		this.callback = Ext.emptyFn;
		this.onHide = Ext.emptyFn;

		if (arguments[0].Amortization_id)
			this.Amortization_id = arguments[0].Amortization_id;
		else
			this.Amortization_id = null;

		if (arguments[0].deniedAmortizationList) {
            this.deniedAmortizationList = arguments[0].deniedAmortizationList;
        }
		else
			this.deniedAmortizationList = null;

		if (arguments[0].Amortization_setDate) {
            this.Amortization_setDate = arguments[0].formParams.Amortization_setDate;
        }
		else
			this.Amortization_setDate = null;

		if (arguments[0].Amortization_FactCost) {
            this.Amortization_FactCost = arguments[0].formParams.Amortization_FactCost;
        }
		else
			this.Amortization_FactCost = null;

		if (arguments[0].Amortization_WearPercent) {
            this.Amortization_WearPercent = arguments[0].formParams.Amortization_WearPercent;
        }
		else
			this.Amortization_WearPercent = null;

		if (arguments[0].Amortization_ResidCost) {
            this.Amortization_ResidCost = arguments[0].formParams.Amortization_ResidCost;
        }
		else
			this.Amortization_ResidCost = null;

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
			if ( ( this.Amortization_id ) && ( this.Amortization_id > 0 ) )
				this.action = "edit";
			else 
				this.action = "add";
		}
		
		var form = this.findById('AmortizationEditForm');
		form.getForm().setValues(arguments[0].formParams);

		var loadMask = new Ext.LoadMask(this.getEl(),{msg: LOAD_WAIT});
		loadMask.show();
		switch (this.action) 
		{
			case 'add':
				this.setTitle(lang['iznos_mi_dobavlenie']);
				this.enableEdit(true);
				loadMask.hide();
				form.getForm().clearInvalid();
				break;
			case 'edit':
				this.setTitle(lang['iznos_mi_redaktirovanie']);
				this.enableEdit(true);
				break;
			case 'view':
				this.setTitle(lang['iznos_mi_prosmotr']);
				this.enableEdit(false);
				break;
		}
		
		/*if (this.action != 'add')
		{
			form.getForm().load(
			{
				params: 
				{
					Amortization_id: _this.Amortization_id
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
				url: '/?c=LpuPassport&m=loadAmortization'
			});
		}*/
		loadMask.hide();
		if ( this.action != 'view' )
			form.getForm().findField('Amortization_setDate').focus(true, 100);
		else
			this.buttons[3].focus();
	},
	
	initComponent: function() 
	{
        var _this = this;
		// Форма с полями 
		this.AmortizationEditForm = new Ext.form.FormPanel(
		{
			autoHeight: true,
			bodyStyle: 'padding: 5px',
			border: false,
			buttonAlign: 'left',
			frame: true,
			id: 'AmortizationEditForm',
			labelAlign: 'right',
			labelWidth: 180,
			items: 
			[{
				name: 'Amortization_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'MedProductCard_id',
				value: 0,
				xtype: 'hidden'
			},{
                allowBlank: false,
                xtype: 'swdatefield',
                fieldLabel: lang['data_otsenki'],
                format: 'd.m.Y',
                name: 'Amortization_setDate',
                plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ]
            },{
                allowBlank: false,
                fieldLabel: lang['fakticheskaya_stoimost'],
                name: 'Amortization_FactCost',
                width: 200,
                allowDecimals:true,
                xtype: 'numberfield'
            },{
                allowBlank: false,
                fieldLabel: lang['protsent_iznosa'],
                maxValue: 100,
                minValue: 0,
                name: 'Amortization_WearPercent',
                width: 200,
                allowDecimals:true,
                xtype: 'numberfield'
            },{
                allowBlank: false,
                fieldLabel: lang['ostatochnaya_stoimost'],
                name: 'Amortization_ResidCost',
                width: 200,
                allowDecimals:true,
                xtype: 'numberfield'
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
				{ name: 'Amortization_id' },
				{ name: 'MedProductCard_id' },
				{ name: 'Amortization_setDate' },
				{ name: 'Amortization_FactCost' },
				{ name: 'Amortization_WearPercent' },
				{ name: 'Amortization_ResidCost' }
			]),
			url: '/?c=LpuPassport&m=saveAmortization'
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
			items: [this.AmortizationEditForm]
		});
		sw.Promed.swAmortizationEditWindow.superclass.initComponent.apply(this, arguments);
	}
});