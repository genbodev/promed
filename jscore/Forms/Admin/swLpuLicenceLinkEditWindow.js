/**
* swLpuLicenceLinkEditWindow - окно редактирования/добавления операции с лицензией ЛПУ.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2009-2015 Swan Ltd.
* @author       Abakhri Samir
* @version      16.06.2015
*/

sw.Promed.swLpuLicenceLinkEditWindow = Ext.extend(sw.Promed.BaseForm, {
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
	id: 'LpuLicenceLinkEditWindow',
	listeners: {
		hide: function() {
			this.onHide();
		}
	},
	modal: true,
	onHide: Ext.emptyFn,
	plain: true,
	resizable: false,
	doSave: function() {
		var _this = this;
		var form = this.findById('LpuLicenceLinkEditForm');
		var base_form = form.getForm();

		if ( !form.getForm().isValid() ) {
			sw.swMsg.show({
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

		if  (!!_this.LpuLicenceLinkList) {
			for (var i = 0; i < _this.LpuLicenceLinkList.length; i++ ) {
				if ((_this.LpuLicenceLinkList[i] - base_form.findField('LpuSectionProfile_id').getValue()) == 0) {
					sw.swMsg.show({
						buttons: Ext.Msg.OK,
						icon: Ext.Msg.WARNING,
						msg: lang['dannyiy_profil_uje_dobavlen'],
						title: ERR_INVFIELDS_TIT
					});
					return false;
				}
			}
		}

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT_SAVE });
		loadMask.show();

		var data = new Object();

		data.LpuLicenceLinkData = {
			'LpuLicenceLink_id': base_form.findField('LpuLicenceLink_id').getValue(),
			'LpuSectionProfile_id': base_form.findField('LpuSectionProfile_id').getValue(),
			'LpuSectionProfile_Name': base_form.findField('LpuSectionProfile_id').getFieldValue('LpuSectionProfile_Name'),
			'LpuSectionProfile_Code': base_form.findField('LpuSectionProfile_id').getFieldValue('LpuSectionProfile_Code')
		};

		this.formStatus = 'edit';
		loadMask.hide();

		this.callback(data);
		this.hide();
		return true;
	},
	show: function()
	{
		sw.Promed.swLpuLicenceLinkEditWindow.superclass.show.apply(this, arguments);

		if (!arguments[0] || !arguments[0].formParams){
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

		this.findById('LpuLicenceLinkEditForm').getForm().reset();

		this.action = 'add';
		this.callback = Ext.emptyFn;
		this.formMode = 'local';
		this.onHide = Ext.emptyFn;

		if (arguments[0].LpuLicenceLinkList) {
			this.LpuLicenceLinkList = arguments[0].LpuLicenceLinkList;
		}
		else
			this.LpuLicenceLinkList = null;

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

		if (arguments[0].action){
			this.action = arguments[0].action;
		}

		var form = this.findById('LpuLicenceLinkEditForm'),
			_this = this,
			today = new Date();

		form.getForm().findField('LpuSectionProfile_id').getStore().clearFilter();
		form.getForm().findField('LpuSectionProfile_id').getStore().filterBy(function(rec){
			return ((Ext.isEmpty(rec.get('LpuSectionProfile_endDT')) || rec.get('LpuSectionProfile_endDT') >= today) && !rec.get('LpuSectionProfile_id').inlist(_this.LpuLicenceLinkList));
		});

		form.getForm().setValues(arguments[0].formParams);

		var loadMask = new Ext.LoadMask(this.getEl(),{msg: LOAD_WAIT});
		loadMask.show();
		switch (this.action)
		{
			case 'add':
				this.setTitle(lang['profil_dobavlenie']);
				this.enableEdit(true);
				form.getForm().clearInvalid();
				break;
			case 'edit':
				this.setTitle(lang['profil_redaktirovanie']);
				this.enableEdit(true);
				break;
			case 'view':
				this.setTitle(lang['profil_prosmotr']);
				this.enableEdit(false);
				break;
		}

		loadMask.hide();

		if ( this.action != 'view' )
			form.getForm().findField('LpuSectionProfile_id').focus(true, 100);
		else
			this.buttons[3].focus();
	},

	initComponent: function()
	{
		// Форма с полями
		this.LpuLicenceLinkEditForm = new Ext.form.FormPanel(
		{
			autoHeight: true,
			bodyStyle: 'padding: 5px',
			border: false,
			buttonAlign: 'left',
			frame: true,
			id: 'LpuLicenceLinkEditForm',
			labelAlign: 'right',
			labelWidth: 100,
			items:
			[{
				name: 'LpuLicenceLink_id',
				value: 0,
				xtype: 'hidden'
			},{
				name: 'LpuLicence_id',
				value: 0,
				xtype: 'hidden'
			}, {
				anchor: '100%',
				allowBlank: false,
				fieldLabel: lang['profil'],
				typeCode: 'int',
				lastQuery: '',
				hiddenName: 'LpuSectionProfile_id',
				tabIndex: TABINDEX_LSLLEW + 1,
				xtype: 'swlpusectionprofilecombo'
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
			reader: new Ext.data.JsonReader({
				success: function() {
					//
				}
			},
			[
				{ name: 'LpuSectionProfile_id' },
				{ name: 'LpuLicenceLink_id' },
				{ name: 'LpuLicence_id' }
			]),
			url: '/?c=LpuPassport&m=saveLpuLicenceLink'
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
				tabIndex: TABINDEX_LSLLEW + 5,
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
				tabIndex: TABINDEX_LSLLEW + 10,
				text: BTN_FRMCANCEL
			}],
			items: [this.LpuLicenceLinkEditForm]
		});
		sw.Promed.swLpuLicenceLinkEditWindow.superclass.initComponent.apply(this, arguments);
	}
});