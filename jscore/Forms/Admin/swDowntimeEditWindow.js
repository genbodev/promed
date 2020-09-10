/**
* swDowntimeEditWindow - окно редактирования/добавления износа.
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

sw.Promed.swDowntimeEditWindow = Ext.extend(sw.Promed.BaseForm, 
{
	action: null,
	autoHeight: true,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	draggable: true,
	split: true,
	width: 700,
	layout: 'form',
	id: 'DowntimeEditWindow',
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
		    form = this.findById('DowntimeEditForm'),
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

        if (!Ext.isEmpty(base_form.findField('Downtime_endDate').getValue()) && base_form.findField('Downtime_begDate').getValue() > base_form.findField('Downtime_endDate').getValue()){
            sw.swMsg.alert(lang['soobschenie'], lang['data_kontsa_prostoya_ne_mojet_byit_bolshe_datyi_nachala_sohranenie_nevozmojno']);
            return false;
        }

        if  (!!_this.deniedDowntimeList) {
            for (var $i = 0;$i < _this.deniedDowntimeList.length; $i++ ) {
                if ((_this.deniedDowntimeList[$i][0] - base_form.findField('Downtime_begDate').getValue()) == 0 && _this.deniedDowntimeList[$i][1] == base_form.findField('DowntimeCause_id').getValue()) {
                    sw.swMsg.show(
                    {
                        buttons: Ext.Msg.OK,
                        icon: Ext.Msg.WARNING,
                        msg: lang['na_vyibrannuyu_datu_uje_suschestvuet_prichina_prostoya_s_vyibrannyim_tipom_sohranenie_nevozmojno'],
                        title: ERR_INVFIELDS_TIT
                    });
                    return false;
                }
            }
        }

        data.deniedDowntimeData = {
            'Downtime_id': base_form.findField('Downtime_id').getValue(),
            'Downtime_begDate': base_form.findField('Downtime_begDate').getValue(),
            'Downtime_endDate': base_form.findField('Downtime_endDate').getValue(),
            'DowntimeCause_id': base_form.findField('DowntimeCause_id').getValue(),
            'DowntimeCause_Name': base_form.findField('DowntimeCause_Name').getValue()
        };

        this.formStatus = 'edit';
        this.callback(data);
        this.hide();
		return true;
	},
    enableEdit: function(enable)
    {
        var form = this.DowntimeEditForm.getForm();
        this.lists = [];
        this.editFields = [];

        this.getFieldsLists(form, {
            needConstructComboLists: true,
            needConstructEditFields: true
        });

        if (enable)
        {
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
		sw.Promed.swDowntimeEditWindow.superclass.show.apply(this, arguments);
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
		this.findById('DowntimeEditForm').getForm().reset();
		this.callback = Ext.emptyFn;
		this.onHide = Ext.emptyFn;

		if (arguments[0].Downtime_id)
			this.Downtime_id = arguments[0].Downtime_id;
		else
			this.Downtime_id = null;

		if (arguments[0].deniedDowntimeList) {
            this.deniedDowntimeList = arguments[0].deniedDowntimeList;
        }
		else
			this.deniedDowntimeList = null;

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
			if ( ( this.Downtime_id ) && ( this.Downtime_id > 0 ) )
				this.action = "edit";
			else 
				this.action = "add";
		}
		
		var form = this.findById('DowntimeEditForm');
		form.getForm().setValues(arguments[0].formParams);

		var loadMask = new Ext.LoadMask(this.getEl(),{msg: LOAD_WAIT});
		loadMask.show();
		switch (this.action)
		{
			case 'add':
				this.setTitle(lang['prostoi_mi_dobavlenie']);
				this.enableEdit(true);
				loadMask.hide();
				form.getForm().clearInvalid();
				break;
			case 'edit':
				this.setTitle(lang['prostoi_mi_redaktirovanie']);
				this.enableEdit(true);
				break;
			case 'view':
				this.setTitle(lang['prostoi_mi_prosmotr']);
				this.enableEdit(false);
				break;
		}

		loadMask.hide();
		if ( this.action != 'view' )
			form.getForm().findField('Downtime_begDate').focus(true, 100);
		else
			this.buttons[3].focus();
	},
	
	initComponent: function()
	{
        var _this = this;

		// Форма с полями 
		this.DowntimeEditForm = new Ext.form.FormPanel(
		{
			autoHeight: true,
			bodyStyle: 'padding: 5px',
			border: false,
			buttonAlign: 'left',
			frame: true,
			id: 'DowntimeEditForm',
			labelAlign: 'right',
            labelWidth: 170,
			items: 
			[{
				name: 'Downtime_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'MedProductCard_id',
				value: 0,
				xtype: 'hidden'
			},{
				id: 'DEW_DowntimeCause_id',
				name: 'DowntimeCause_id',
				xtype: 'hidden'
			},{
                allowBlank: false,
                fieldLabel: lang['data_nachala_prostoya'],
                format: 'd.m.Y',
                width: 100,
                name: 'Downtime_begDate',
                plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
                xtype: 'swdatefield'
            },{
                // allowBlank: false,
                fieldLabel: lang['data_vozobnovleniya_rabotyi'],
                name: 'Downtime_endDate',
                width: 100,
                format: 'd.m.Y',
                xtype: 'swdatefield',
                plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ]
            },{
                allowBlank: false,
                scheme: 'passport',
                anchor: '100%',
                useNameWithPath: false,
                fieldLabel: lang['prichina_prostoya'],
                id: 'DTEW_DowntimeCause_id',
                name: 'DowntimeCause_Name',
                object: 'DowntimeCause',
                selectionWindowParams: {
                    height: 500,
                    title: lang['prichina_prostoya'],
                    width: 600
                },
                valueFieldId: 'DEW_DowntimeCause_id',
                xtype: 'swtreeselectionfield'
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
				{ name: 'Downtime_id' },
				{ name: 'MedProductCard_id' },
				{ name: 'Downtime_begDate' },
				{ name: 'Downtime_endDate' },
				{ name: 'DowntimeCause_id' }
			]),
			url: '/?c=LpuPassport&m=saveDowntime'
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
			items: [this.DowntimeEditForm]
		});
		sw.Promed.swDowntimeEditWindow.superclass.initComponent.apply(this, arguments);
	}
});