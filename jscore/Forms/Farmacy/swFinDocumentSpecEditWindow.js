/**
 * swFinDocumentSpecEditWindow - окно редактирования cписка платежных поручений
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Farmacy
 * @access       public
 * @copyright    Copyright (c) 2013 Swan Ltd.
 * @author       Salakhov R.
 * @version      06.2013
 * @comment
 */
sw.Promed.swFinDocumentSpecEditWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: false,
	title: lang['platejnoe_poruchenie'],
	layout: 'border',
	id: 'FinDocumentSpecEditWindow',
	modal: true,
	shim: false,
	width: 470,
	height: 165,
	resizable: false,
	maximizable: false,
	maximized: false,
	doSave:  function() {
		var wnd = this;
		if ( !this.form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					wnd.findById('FinDocumentSpecEditForm').getFirstInvalidEl().focus(true);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
		var params = this.form.getValues();
		this.callback(params);
		this.hide();
		return true;
	},
	setDisabled: function(disable) {
		var wnd = this;
		var form = this.form;
		var field_arr = [
			'FinDocument_Number',
			'FinDocument_Date',
			'FinDocument_Sum'
		];

		for (var i in field_arr) if (form.findField(field_arr[i])) {
			if (disable)
				form.findField(field_arr[i]).disable();
			else
				form.findField(field_arr[i]).enable();
		}

		if (disable) {
			wnd.buttons[0].disable();
		} else {
			wnd.buttons[0].enable();
		}
	},
	show: function() {
		var wnd = this;
		sw.Promed.swFinDocumentSpecEditWindow.superclass.show.apply(this, arguments);
		this.action = '';
		this.callback = Ext.emptyFn;
		this.FinDocumentSpec_id = null;
		if ( !arguments[0] ) {
			sw.swMsg.alert(lang['oshibka'], lang['ne_ukazanyi_vhodnyie_dannyie'], function() { wnd.hide(); });
			return false;
		}
		if ( arguments[0].action ) {
			this.action = arguments[0].action;
		}
		if ( arguments[0].callback && typeof arguments[0].callback == 'function' ) {
			this.callback = arguments[0].callback;
		}
		if ( arguments[0].owner ) {
			this.owner = arguments[0].owner;
		}
		if ( arguments[0].FinDocumentSpec_id ) {
			this.FinDocumentSpec_id = arguments[0].FinDocumentSpec_id;
		}
		this.form.reset();
		this.setTitle(lang['platejnoe_poruchenie']);
		var loadMask = new Ext.LoadMask(this.form.getEl(), {msg:lang['zagruzka']});
		loadMask.show();
		switch (arguments[0].action) {
			case 'add':
				this.setDisabled(false);
				this.setTitle(this.title+lang['_dobavlenie']);
				loadMask.hide();
				break;
			case 'edit':
			case 'view':
				this.setTitle(this.title+(this.action == 'view' ? lang['_prosmotr'] : lang['_redaktirovanie']));
				this.setDisabled(this.action == 'view');
				if (arguments[0].params) {
					this.form.setValues(arguments[0].params);
				}
				loadMask.hide();
				break;
		}
		return true;
	},
	initComponent: function() {
		var wnd = this;

		var form = new Ext.form.FormPanel({
            id: 'FinDocumentSpecEditForm',
            labelWidth: 70,
			autoScroll: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			border: false,
			frame: true,
			region: 'center',
			labelAlign: 'right',
			items: [{
                name: 'FinDocument_id',
                xtype: 'hidden',
                value: 0
            }, {
                fieldLabel: '№',
                name: 'FinDocument_Number',
                allowBlank: false,
                maxLength: 50,
                xtype: 'textfield',
                width: 350
            }, {
                fieldLabel: 'Дата',
                name: 'FinDocument_Date',
                allowBlank: false,
                xtype: 'swdatefield'
            }, {
                fieldLabel: 'Сумма',
                name: 'FinDocument_Sum',
                width:100,
                allowDecimals: true,
                allowNegative: false,
                allowBlank: false,
                xtype:'numberfield'
			}],
			reader: new Ext.data.JsonReader({
				success: Ext.emptyFn
			}, [
				{name: 'FinDocument_id'},
				{name: 'FinDocument_Number'},
				{name: 'FinDocument_Date'},
				{name: 'FinDocument_Sum'}
			])
		});
		Ext.apply(this, {
			layout: 'border',
			buttons: [
				{
					handler: function() {
						this.ownerCt.doSave();
					},
					iconCls: 'save16',
					text: BTN_FRMSAVE
				},
				{
					text: '-'
				},
				HelpButton(this, 0),
				{
					handler: function() {
						this.ownerCt.hide();
					},
					iconCls: 'cancel16',
					text: BTN_FRMCANCEL
				}
			],
			items:[form]
		});
		sw.Promed.swFinDocumentSpecEditWindow.superclass.initComponent.apply(this, arguments);
		this.form = this.findById('FinDocumentSpecEditForm').getForm();
	}
});