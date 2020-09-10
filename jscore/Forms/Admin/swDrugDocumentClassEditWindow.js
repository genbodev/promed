/**
 * swDrugDocumentClassEditWindow - окно редактирования вида заявки на медикаменты
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Admin
 * @access			public
 * @copyright		Copyright (c) 2013 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			21.01.2014
 */

/*NO PARSE JSON*/

sw.Promed.swDrugDocumentClassEditWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'swDrugDocumentClassEditWindow',
	width: 640,
	autoHeight: true,
	//height: 600,
	layout: 'form',
	callback: Ext.emptyFn,
	modal: true,
	title: lang['vid_zayavki_na_medikamentyi'],

	action: 'view',

	doSave: function(options)
	{
		var wnd = this;
		var base_form = wnd.FormPanel.getForm();

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

		base_form.submit({
			failure: function(result_form, action) {
				wnd.getLoadMask().hide()
			},
			success: function(result_form, action) {
				wnd.getLoadMask().hide();
				if (action.result)
				{
					wnd.callback();
					wnd.hide();
				}
				else
				{
					Ext.Msg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_2]']);
				}
			}
		});
	},

	show: function()
	{
		sw.Promed.swDrugDocumentClassEditWindow.superclass.show.apply(this, arguments);

		this.center();

		var wnd = this;
		var base_form = wnd.FormPanel.getForm();

		base_form.reset();
		base_form.clearInvalid();

		if (arguments[0] && arguments[0].action) {
			this.action = arguments[0].action;
		}

		if (arguments[0] && arguments[0].callback) {
			this.callback = arguments[0].callback;
		}

		if (arguments[0] && arguments[0].DrugDocumentClass_id) {
			base_form.findField('DrugDocumentClass_id').setValue(arguments[0].DrugDocumentClass_id);
		}

		wnd.getLoadMask(lang['zagruzka_dannyih_formyi']).show();
		switch(wnd.action) {
			case 'add':
				wnd.enableEdit(true);
				wnd.getLoadMask().hide();
				wnd.setTitle(lang['vid_zayavki_na_medikamentyi_dobavlenie']);
				break;

			case 'edit':
			case 'view':
				if (wnd.action == 'view') {
					wnd.setTitle(lang['vid_zayavki_na_medikamentyi_prosmotr']);
					wnd.enableEdit(false);
				} else {
					wnd.setTitle(lang['vid_zayavki_na_medikamentyi_redaktirovanie']);
					wnd.enableEdit(true);
				}

				base_form.load({
					failure:function () {
						wnd.getLoadMask().hide();
						wnd.hide();
					},
					params:{
						DrugDocumentClass_id: base_form.findField('DrugDocumentClass_id').getValue()
					},
					success: function (response) {
						wnd.getLoadMask().hide();
					},
					url:'/?c=DrugDocument&m=loadDrugDocumentClassForm'
				});

				break;
		}
	},

	initComponent: function()
	{
		var wnd = this;

		wnd.FormPanel = new Ext.form.FormPanel({
			autoScroll: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px 20px 0',
			border: false,
			frame: true,
			labelAlign: 'right',
			labelWidth: 140,
			id: 'DDCEW_FormPanel',
			region: 'center',

			items: [{
				name: 'DrugDocumentClass_id',
				xtype: 'hidden'
			}, {
				allowBlank:false,
				allowNegative: false,
				fieldLabel: lang['kod'],
				name: 'DrugDocumentClass_Code',
				xtype: 'numberfield',
				width: 80
			}, {
				allowBlank: false,
				fieldLabel: lang['naimenovanie'],
				name: 'DrugDocumentClass_Name',
				xtype: 'textfield',
				width: 380
			}, {
				fieldLabel: lang['kratkoe_naimenovanie'],
				name: 'DrugDocumentClass_Nick',
				xtype: 'textfield',
				width: 380
			}],
			reader: new Ext.data.JsonReader(
				{
					success: function()
					{
						//
					}
				},
				[
					{ name: 'DrugDocumentClass_id' },
					{ name: 'DrugDocumentClass_Code' },
					{ name: 'DrugDocumentClass_Name' },
					{ name: 'DrugDocumentClass_Nick' }
				]
			),
			url: '/?c=DrugDocument&m=saveDrugDocumentClass'
		});

		Ext.apply(this, {
			items: [
				wnd.FormPanel
			],
			buttons: [
				{
					handler: function() {
						this.doSave();
					}.createDelegate(this),
					iconCls: 'save16',
					id: 'DDCEW_SaveButton',
					text: BTN_FRMSAVE
				},
				{
					text: '-'
				},
				HelpButton(this, 1),
				{
					handler: function () {
						this.hide();
					}.createDelegate(this),
					iconCls: 'cancel16',
					id: 'DDCEW_CancelButton',
					text: lang['otmena']
				}]
		});

		sw.Promed.swDrugDocumentClassEditWindow.superclass.initComponent.apply(this, arguments);
	}
});