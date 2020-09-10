sw.Promed.swPrivilegeAccessRightsLpuEditWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: true,
	autoScroll: true,
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	draggable: true,
	id: 'swPrivilegeAccessRightsLpuEditWindow',
	maximizable: false,
	modal: true,
	resizable: false,
	width: 500,


	doSave: function() {
		var base_form = this.FormPanel.getForm();
		var wnd = this;

		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					wnd.FormPanel.getFirstInvalidEl().focus(false);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		wnd.getLoadMask("Подождите, идет сохранение...").show();

		var data = new Object();

		var Lpu_Name, Lpu_Nick, Lpu_id = base_form.findField('Lpu_id').getValue();

		var index = base_form.findField('Lpu_id').getStore().findBy(function(rec) {
			return (rec.get('Lpu_id') == Lpu_id);
		});

		if ( index >= 0 ) {
			Lpu_Name = base_form.findField('Lpu_id').getStore().getAt(index).get('Lpu_Name');
			Lpu_Nick = base_form.findField('Lpu_id').getStore().getAt(index).get('Lpu_Nick');

		}
		var RecordStatus_Code = base_form.findField('RecordStatus_Code').getValue();
		data.LpuData = {
			Lpu_id: Lpu_id,
			Lpu_Name: Lpu_Name,
			Lpu_Nick: Lpu_Nick,
			RecordStatus_Code: RecordStatus_Code
		};

		wnd.formStatus = 'edit';
		wnd.getLoadMask().hide();

		wnd.callback(data);
		wnd.hide();
	},

	show: function() {
		sw.Promed.swPrivilegeAccessRightsLpuEditWindow.superclass.show.apply(this, arguments);

		var form = this;
		var base_form = form.FormPanel.getForm();

		base_form.reset();

		if ( arguments[0] && arguments[0].formParams ) {
			base_form.setValues(arguments[0].formParams);
		}

		if ( arguments[0] && arguments[0].callback ) {
			this.callback = arguments[0].callback;
		}

		if ( arguments[0] && arguments[0].action ) {
			this.action = arguments[0].action;
		}

		if ( arguments[0] && arguments[0].callback ) {
			this.callback = arguments[0].callback;
		}

		switch ( this.action ) {
			case 'add':
				this.enableEdit(true);
				this.setTitle(lang['mo_dostup_dobavlenie']);

				base_form.clearInvalid();
				base_form.findField('Lpu_id').focus(true, 250);
				break;

			case 'edit':
				this.enableEdit(true);
				this.setTitle(lang['mo_dostup_redaktirovanie']);

				base_form.findField('Lpu_id').focus(true, 250);
				break;
			case 'view':
				this.enableEdit(false);
				this.setTitle(lang['mo_dostup_prosmotr']);
				break;

			default:
				this.hide();
				break;
		}
	},

	initComponent: function() {
		var form = this;

		this.FormPanel = new Ext.form.FormPanel(
			{
				bodyStyle: '{padding-top: 0.5em;}',
				border: false,
				frame: false,
				labelAlign: 'right',
				labelWidth: 180,
				layout: 'form',
				id: 'PrivilegeAccessRightsLpuEditForm',
				url: '',
				autoLoad: false,
				items: [
					{
						name: 'RecordStatus_Code',
						value: 0,
						xtype: 'hidden'
					}, {
						allowBlank: false,
						fieldLabel: lang['mo_s_razreshennyim_dostupom'],
						hiddenName: 'Lpu_id',
						width: 250,
						xtype: 'swlpucombo'
					}
				]
			});

		Ext.apply(this, {
			items: [
				this.FormPanel
			],
			buttons: [{
				handler: function() {
					this.doSave();
				}.createDelegate(this),
				iconCls: 'save16',
				id: 'PARE_SaveButton',
				text: BTN_FRMSAVE
			},
				'-',
				HelpButton(this, -1),
				{
					handler: function() {
						this.hide();
					}.createDelegate(this),
					iconCls: 'cancel16',
					id: 'FCEW_CancelButton',
					text: BTN_FRMCANCEL
				}]
		});

		sw.Promed.swPrivilegeAccessRightsLpuEditWindow.superclass.initComponent.apply(this, arguments);
	}
});
