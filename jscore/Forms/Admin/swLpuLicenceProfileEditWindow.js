/**
* swLpuLicenceProfileEditWindow - окно редактирования/добавления операции с лицензией ЛПУ.
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

sw.Promed.swLpuLicenceProfileEditWindow = Ext.extend(sw.Promed.BaseForm, 
{
	action: null,
	autoHeight: true,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	draggable: true,
	split: true,
	width: 600,
    formStatus: 'edit',
	layout: 'form',
	id: 'LpuLicenceProfileEditWindow',
	listeners: 
	{
		hide: function() {
			this.onHide();
		},
        resize: function(el) {
            this.syncShadow();
        }
	},
	modal: true,
	onHide: Ext.emptyFn,
	plain: true,
	resizable: true,
	doSave: function()
	{
		var form = this.findById('LpuLicenceProfileEditForm');
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

        if (base_form.findField('LpuLicenceProfileType_id').getValue().inlist(this.deniedProfileTypeList)) {
            sw.swMsg.alert(lang['soobschenie'], lang['dannyiy_vid_litsenzii_uje_dobavlen_v_spisok_povtornoe_dobavlenie_nevozmojno']);
            return false;
        }

        var loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT_SAVE });
        loadMask.show();

        var data = {};

        data.LpuLicenceProfileData = {
            'LpuLicenceProfile_id': base_form.findField('LpuLicenceProfile_id').getValue(),
            'LpuLicenceProfileType_Name': base_form.findField('LpuLicenceProfileType_Name').getValue() || this.LpuLicenceProfileType_Name,
            'LpuLicenceProfileType_Code': base_form.findField('LpuLicenceProfileType_Name').code || this.LpuLicenceProfileType_Code,
            'LpuLicenceProfileType_id': base_form.findField('LpuLicenceProfileType_id').getValue()
        };
        
        this.formStatus = 'edit';
        loadMask.hide();

        this.callback(data);
        this.hide();
		return true;
	},
	enableEdit: function(enable)
	{
        var form = this.findById('LpuLicenceProfileEditForm');

		if (enable) {
			form.getForm().findField('LpuLicenceProfileType_id').enable();
			this.buttons[0].enable();
		} else {
			form.getForm().findField('LpuLicenceProfileType_id').disable();
			this.buttons[0].disable();
		}
	},
	show: function() {
		sw.Promed.swLpuLicenceProfileEditWindow.superclass.show.apply(this, arguments);
        var form = this.findById('LpuLicenceProfileEditForm').getForm();
        
		if (!arguments[0]) {
			sw.swMsg.show({
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
		this.findById('LpuLicenceProfileEditForm').getForm().reset();
		this.callback = Ext.emptyFn;
		this.onHide = Ext.emptyFn;

        if ( arguments[0].deniedProfileTypeList ) {
                this.deniedProfileTypeList = arguments[0].deniedProfileTypeList;
        }

		if (arguments[0].LpuLicenceProfile_id)
			this.LpuLicenceProfile_id = arguments[0].LpuLicenceProfile_id;
		else 
			this.LpuLicenceProfile_id = null;
		
		if (arguments[0].LpuLicence_id)
			this.LpuLicence_id = arguments[0].LpuLicence_id;
		else 
			this.LpuLicence_id = null;

        if (arguments[0].formParams.LpuLicenceProfileType_id)
			this.LpuLicenceProfileType_id = arguments[0].formParams.LpuLicenceProfileType_id;
		else
			this.LpuLicenceProfileType_id = null;

        if (arguments[0].formParams.LpuLicenceProfileType_Code)
			this.LpuLicenceProfileType_Code = arguments[0].formParams.LpuLicenceProfileType_Code;
		else
			this.LpuLicenceProfileType_Code = null;

        if (arguments[0].formParams.LpuLicenceProfileType_Name)
			this.LpuLicenceProfileType_Name = arguments[0].formParams.LpuLicenceProfileType_Name;
		else
			this.LpuLicenceProfileType_Name = null;

		if (arguments[0].callback){
			this.callback = arguments[0].callback;
		}
		
		if (arguments[0].owner) {
			this.owner = arguments[0].owner;
		}
		
		if (arguments[0].onHide) {
			this.onHide = arguments[0].onHide;
		}
		
		if (arguments[0].action) {
			this.action = arguments[0].action;
		} else {
			if ( ( this.OrgRSchet_id ) && ( this.OrgRSchet_id > 0 ) )
				this.action = "edit";
			else 
				this.action = "add";
		}
        
		form.setValues(arguments[0]);

		var loadMask = new Ext.LoadMask(this.getEl(),{msg: LOAD_WAIT});

		switch (this.action) {
			case 'add':
				this.setTitle(lang['vid_litsenzii_po_profilyu_dobavlenie']);
				this.enableEdit(true);
				loadMask.hide();
				form.clearInvalid();
				break;
			case 'edit':
				this.setTitle(lang['vid_litsenzii_po_profilyu_redaktirovanie']);
				this.enableEdit(true);
				break;
			case 'view':
				this.setTitle(lang['vid_litsenzii_po_profilyu_prosmotr']);
				this.enableEdit(false);
				break;
		}
		
		if (this.action != 'add') {
            form.findField('LpuLicenceProfileType_id').setValue(this.LpuLicenceProfileType_id);
            form.findField('LpuLicenceProfileType_Name').setNameWithPath(' ');
		}
 	},
	initComponent: function()
	{
		// Форма с полями 
		this.LpuLicenceProfileEditForm = new Ext.form.FormPanel(
		{
			autoHeight: true,
			bodyStyle: 'padding: 5px',
			border: false,
			buttonAlign: 'left',
			frame: true,
			id: 'LpuLicenceProfileEditForm',
			labelAlign: 'right',
			labelWidth: 100,
			items: 
			[{
                id: 'LLPEW_LpuLicenceProfileType_id',
                name: 'LpuLicenceProfileType_id',
                xtype: 'hidden'
            },{
				name: 'LpuLicenceProfile_id',
				value: 0,
				xtype: 'hidden'
			},{
				name: 'LpuLicence_id',
				value: 0,
				xtype: 'hidden'
			}, {
                allowBlank: true,
				allowLowLevelRecordsOnly: false,
                anchor:'100%',
                scheme: 'fed',
                fieldLabel: lang['vid_litsenzii'],
                id: 'LLPEW_LpuLicenceProfileType_Name',
                name: 'LpuLicenceProfileType_Name',
                object: 'LpuLicenceProfileType',
                selectionWindowParams: {
                    height: 500,
                    title: lang['vid_litsenzii'],
					separator: ' ',
                    width: 600
                },
                valueFieldId: 'LLPEW_LpuLicenceProfileType_id',
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
				{ name: 'LpuLicenceProfile_id' },
				{ name: 'LpuLicence_id' },
				{ name: 'LpuLicenceProfileType_id' }
			]),
			url: '/?c=LpuPassport&m=saveLpuLicenceProfile'
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
			items: [this.LpuLicenceProfileEditForm]
		});
		sw.Promed.swLpuLicenceProfileEditWindow.superclass.initComponent.apply(this, arguments);
	}
});