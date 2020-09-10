/**
 * swAccessRightsPrivilegeTypeEditWindow - окно редактирования льгот для ограничения доступа
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Admin
 * @access			public
 * @copyright		Copyright (c) 2014 Swan Ltd.
 */

/*NO PARSE JSON*/

sw.Promed.swAccessRightsPrivilegeTypeEditWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'swAccessRightsPrivilegeTypeEditWindow',
	width: 600,
	autoHeight: true,
	modal: true,
	resizable: false,
	formStatus: 'edit',
	action: 'view',
	callback: Ext.emptyFn,

	doSave: function(options) {
		options = options || {};
		var base_form = this.FormPanel.getForm();

		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					this.FormPanel.getFirstInvalidEl().focus(true);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT_SAVE });
		//loadMask.show();

		base_form.submit({
			failure: function() {
				loadMask.hide();
			},
			success: function(form, action) {
				loadMask.hide();
				var responseObj = Ext.util.JSON.decode(action.response.responseText);
				if (responseObj.success) {
					this.callback();
					this.hide();
				} else if (responseObj.Error_Msg) {
					sw.swMsg.alert('Ошибка', responseObj.Error_Msg);
				}
			}.createDelegate(this)		
		});
	},
	
	show: function() {
		sw.Promed.swAccessRightsPrivilegeTypeEditWindow.superclass.show.apply(this, arguments);

		var base_form = this.FormPanel.getForm();

		base_form.reset();

		this.DiagLastNum = 0;
		this.DiagState = {};
		this.callback = Ext.emptyFn;
		this.action = 'view';

		if (arguments[0].action) {
			this.action = arguments[0].action;
		}
		if (arguments[0].callback) {
			this.callback = arguments[0].callback;
		}
		if (arguments[0].formParams) {
			base_form.setValues(arguments[0].formParams);
		}

		var loadMask = new Ext.LoadMask(this.getEl(),{msg: LOAD_WAIT});
		loadMask.show();
		switch (this.action)
		{
			case 'add':
				this.setTitle('Ограничение прав доступа. Льгота: Добавление');
				this.enableEdit(true);

				loadMask.hide();
			break;

			case 'edit':
			case 'view':
				if (this.action == 'edit') {
					this.setTitle('Ограничение прав доступа. Льгота: Редактирование');
					this.enableEdit(true);
				} else {
					this.setTitle('Ограничение прав доступа. Льгота: Просмотр');
					this.enableEdit(false);
				}

				base_form.load({
					url: '/?c=AccessRightsPrivilegeType&m=loadAccessRightsForm',
					params: {AccessRightsName_id: base_form.findField('AccessRightsName_id').getValue()},
					failure: function() {
						loadMask.hide();
					},
					success: function(form, action) {
						loadMask.hide();
					}.createDelegate(this)
				});
			break;
		}
	},

	initComponent: function() {
		this.FormPanel = new Ext.form.FormPanel({
			bodyBorder: false,
			border: false,
			buttonAlign: 'left',
			frame: true,
			id: 'ARPTEW_AccessRightsPrivilegeTypeEditForm',
			url: '/?c=AccessRightsPrivilegeType&m=saveAccessRights',
			bodyStyle: 'padding: 10px 5px 10px 20px;',
			labelAlign: 'right',
			labelWidth: 125,
			items: [{
				xtype: 'hidden',
				name: 'AccessRightsPrivilegeType_id'
			}, {
				xtype: 'hidden',
				name: 'AccessRightsName_id'
			}, {
				allowBlank: false,
				fieldLabel: 'Закрытая льгота',
				name: 'PrivilegeType_id',
				width: 400,
				xtype: 'swprivilegetypecombo'
			}],
			reader: new Ext.data.JsonReader({
				success: function() {
					//
				}
			}, [
				{name: 'AccessRightsPrivilegeType_id'},
				{name: 'AccessRightsName_id'},
				{name: 'PrivilegeType_id'}
			]),
			keys: [{
				fn: function(e) {
					this.doSave();
				}.createDelegate(this),
				key: Ext.EventObject.ENTER,
				stopEvent: true
			}]
		});

		Ext.apply(this, {
			items: [
				this.FormPanel
			],
			buttons: [
				{
					text: BTN_FRMSAVE,
					id: 'ARPTEW_ButtonSave',
					tooltip: 'Сохранить',
					iconCls: 'save16',
					handler: function()
					{
						this.doSave();
					}.createDelegate(this)
				}, {
					text: '-'
				},
				HelpButton(this, 1),
				{
					handler: function () {
						this.hide();
					}.createDelegate(this),
					iconCls: 'cancel16',
					id: 'ARPTEW_CancelButton',
					text: 'Отменить'
				}]
		});

		sw.Promed.swAccessRightsPrivilegeTypeEditWindow.superclass.initComponent.apply(this, arguments);
	}
});