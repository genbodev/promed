/**
 * swPostEditWindow - окно редактирования должности
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      	Common
 * @access       	public
 * @copyright		Copyright (c) 2016 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			18.04.2016
 */
/*NO PARSE JSON*/

sw.Promed.swPostEditWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'swPostEditWindow',
	maximizable: false,
	width: 460,
	autoHeight: true,

	doSave: function() {
		var base_form = this.FormPanel.getForm();

		if ( !base_form.isValid() ){
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function()
				{
					this.FormPanel.getFirstInvalidEl().focus(true);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение..."});
		loadMask.show();

		base_form.submit({
			failure: function(result_form, action) {
				loadMask.hide();
			}.createDelegate(this),
			success: function(result_form, action) {
				loadMask.hide();
				base_form.findField('Post_id').setValue(action.result.Post_id);

				this.callback();
				this.hide();
			}.createDelegate(this)
		});
	},

	show: function() {
		sw.Promed.swPostEditWindow.superclass.show.apply(this, arguments);

		this.action = 'view';
		this.callback = Ext.emptyFn;

		var base_form = this.FormPanel.getForm();
		base_form.reset();

		if (arguments[0] && arguments[0].Post_id) {
			base_form.findField('Post_id').setValue(arguments[0].Post_id);
		}
		if (arguments[0] && arguments[0].Org_id) {
			base_form.findField('Org_id').setValue(arguments[0].Org_id);
		}
		if (arguments[0] && arguments[0].action) {
			this.action = arguments[0].action;
		}
		if (arguments[0] && arguments[0].callback) {
			this.callback = arguments[0].callback;
		}

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Подождите, идет загрузка..." });
		loadMask.show();

		switch(this.action) {
			case 'add':
				this.setTitle('Должность: Добавление');
				this.enableEdit(true);
				loadMask.hide();
				break;

			case 'edit':
			case 'view':
				this.setTitle('Должность: Добавление');
				if (this.action == 'edit') {
					this.enableEdit(true);
				} else {
					this.enableEdit(false);
				}

				base_form.load({
					url: '/?c=Post&m=loadPostForm',
					params: {Post_id: base_form.findField('Post_id').getValue()},
					success: function() {
						loadMask.hide();
					},
					failure: function() {
						loadMask.hide();
					}
				});
				break;
		}
	},

	initComponent: function() {
		this.FormPanel = new Ext.form.FormPanel({
			frame: true,
			autoHeight: true,
			labelAlign: 'right',
			url: '/?c=Post&m=savePost',
			items: [{
				xtype: 'hidden',
				name: 'Post_id'
			}, {
				xtype: 'hidden',
				name: 'Org_id'
			}, {
				allowBlank: false,
				xtype: 'textfield',
				name: 'Post_Name',
				fieldLabel: 'Наименование',
				anchor: '100%'
			}],
			reader: new Ext.data.JsonReader({
				success: function(){
					//
				}
			}, [
				{name: 'Post_id', type: 'int'},
				{name: 'Post_Name', type: 'string'}
			]),
			keys: [{
				key: Ext.EventObject.ENTER,
				fn: function(e) {
					this.doSave();
				}.createDelegate(this),
				stopEvent: true
			}]
		});

		Ext.apply(this,{
			buttons: [
				{
					handler: function() {
						this.doSave();
					}.createDelegate(this),
					iconCls: 'save16',
					text: BTN_FRMSAVE
				},
				{
					text: '-'
				},
				HelpButton(this),
				{
					handler: function() {
						this.hide();
					}.createDelegate(this),
					iconCls: 'cancel16',
					text: BTN_FRMCANCEL
				}
			],
			items: [this.FormPanel]
		});

		sw.Promed.swPostEditWindow.superclass.initComponent.apply(this, arguments);
	}
});