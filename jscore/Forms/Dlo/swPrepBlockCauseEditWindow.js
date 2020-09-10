/**
 * swPrepBlockCauseEditWindow - окно редактирования причины блокировки оборота серий выпуска ЛС
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      	Dlo
 * @access       	public
 * @copyright		Copyright (c) 2014 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			20.03.2015
 */
/*NO PARSE JSON*/

sw.Promed.swPrepBlockCauseEditWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'swPrepBlockCauseEditWindow',
	width: 450,
	autoHeight: true,
	modal: true,

	doSave: function() {
		var base_form = this.FormPanel.getForm();

		if ( !base_form.isValid() ) {
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


		var params = {};

		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение..."});
		loadMask.show();

		base_form.submit({
			params: params,
			success: function(result_form, action) {
				loadMask.hide();

				if (action.result){
					if (action.result.PrepBlockCause_id ){
						this.callback();
						this.hide();
					} else {
						sw.swMsg.show({
							buttons: Ext.Msg.OK,
							fn: function()
							{
								this.hide();
							}.createDelegate(this),
							icon: Ext.Msg.ERROR,
							msg: lang['proizoshla_oshibka_pojaluysta_povtorite_popyitku_pozje'],
							title: lang['oshibka']
						});
					}
				}
			}.createDelegate(this),
			failure: function(result_form, action) {
				loadMask.hide();
			}.createDelegate(this)
		});
	},

	getPrepBlockCauseCode: function() {
		var base_form = this.FormPanel.getForm();

		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Получение кода..."});
		loadMask.show();

		Ext.Ajax.request({
			url: '/?c=RlsDrug&m=getPrepBlockCauseCode',
			success: function(response, options) {
				loadMask.hide();

				var responseObj = Ext.util.JSON.decode(response.responseText);
				if (!Ext.isEmpty(responseObj.PrepBlockCause_Code)) {
					base_form.findField('PrepBlockCause_Code').setValue(responseObj.PrepBlockCause_Code);
				}
			},
			failure: function(response, options) {
				loadMask.hide();
			}
		});
	},

	show: function() {
		sw.Promed.swPrepBlockCauseEditWindow.superclass.show.apply(this, arguments);

		this.action = 'view';
		this.callback = Ext.emptyFn;

		var base_form = this.FormPanel.getForm();
		base_form.reset();

		if (arguments[0] && arguments[0].action) {
			this.action = arguments[0].action;
		}

		if (arguments[0] && arguments[0].callback) {
			this.callback = arguments[0].callback;
		}

		if (arguments[0] && arguments[0].formParams) {
			base_form.setValues(arguments[0].formParams);
		}

		base_form.items.each(function(f){f.validate()});

		switch(this.action) {
			case 'add':
				this.setTitle(lang['prichina_blokirovki_dobavlenie']);
				this.enableEdit(true);
				break;

			case 'edit':
			case 'view':
				if (this.action == 'edit') {
					this.setTitle(lang['prichina_blokirovki_redaktirovanie']);
					this.enableEdit(true);
				} else {
					this.setTitle(lang['prichina_blokirovki_prosmotr']);
					this.enableEdit(false);
				}

				base_form.load({
					params: {
						PrepBlockCause_id: base_form.findField('PrepBlockCause_id').getValue()
					},
					url: '/?c=RlsDrug&m=loadPrepBlockCauseForm',
					success: function() {

					}.createDelegate(this)
				});

				break;
		}
	},

	initComponent: function() {
		this.FormPanel = new Ext.FormPanel({
			frame: true,
			id: 'PBCEW_FormPanel',
			autoHeight: true,
			labelAlign: 'right',
			labelWidth: 130,
			url: '/?c=RlsDrug&m=savePrepBlockCause',
			items: [{
				xtype: 'hidden',
				name: 'PrepBlockCause_id'
			}, {
				allowBlank: false,
				xtype: 'trigger',
				name: 'PrepBlockCause_Code',
				fieldLabel: lang['kod_prichinyi'],
				width: 280,
				listeners: {
					'keydown': function(inp, e) {
						switch ( e.getKey() ) {
							case Ext.EventObject.F2:
							case Ext.EventObject.F4:
								e.stopEvent();
								this.getPrepBlockCauseCode();
								break;

							case Ext.EventObject.TAB:
								if ( e.shiftKey == true ) {
									e.stopEvent();
									this.buttons[this.buttons.length - 1].focus();
								}
								break;
						}
					}.createDelegate(this)
				},
				onTriggerClick: function() {
					this.getPrepBlockCauseCode();
				}.createDelegate(this),
				triggerClass: 'x-form-plus-trigger'
			}, {
				allowBlank: false,
				xtype: 'textfield',
				name: 'PrepBlockCause_Name',
				fieldLabel: lang['prichina_blokirovki'],
				width: 280
			}],
			reader: new Ext.data.JsonReader({
				success: function(){
					//
				}
			}, [
				{name: 'PrepBlockCause_id'},
				{name: 'PrepBlockCause_Code'},
				{name: 'PrepBlockCause_Name'}
			])
		});

		Ext.apply(this,
		{
			buttons: [
				{
					handler: function () {
						this.doSave();
					}.createDelegate(this),
					iconCls: 'save16',
					id: 'PBECW_SaveButton',
					text: BTN_FRMSAVE
				},
				{
					text: '-'
				},
				HelpButton(this),
				{
					handler: function()
					{
						this.hide();
					}.createDelegate(this),
					iconCls: 'cancel16',
					text: BTN_FRMCLOSE
				}
			],
			items: [this.FormPanel]
		});

		sw.Promed.swPrepBlockCauseEditWindow.superclass.initComponent.apply(this, arguments);
	}
});