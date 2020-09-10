/**
* swTreatmentCatEditWindow - окно добавления и редактирования справочников
* адресатов TreatmentRecipientType, категорий обращений TreatmentCat, способов получения обращения TreatmentMethodDispatch
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Promed
* @access       public
* @copyright    Copyright (c) 2009-2010 Swan Ltd.
* @author       Permyakov Alexander <permjakov-am@mail.ru>
* @version      16.07.2010
* @comment      Префикс для id компонентов TCEF (TreatmentCatEditForm). TABINDEX_TCEF
*/

sw.Promed.swTreatmentCatEditWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: true,
	bodyStyle: 'padding: 2px',
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closeAction: 'hide',
	draggable: false,
	enableEdit: function( enable ) {
		var form = this.findById('TreatmentCatEditForm').getForm();
	},
	id: 'TCEF_edit_window',
	initComponent: function() {		var current_window = this;
		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.ownerCt.submit();
				},
				iconCls: 'save16',
				id: 'TCEF_SaveButton',
				tabIndex: TABINDEX_TCEF + 3,
				text: BTN_FRMSAVE
			}, {
				text: '-'
			},
			HelpButton(this),
			{
				iconCls: 'cancel16',
				id: 'TCEF_CancelButton',
				handler: function() {
					this.ownerCt.hide();
				},
				tabIndex: TABINDEX_TCEF + 4,
				text: BTN_FRMCANCEL
			}],
			items: [ new Ext.form.FormPanel({
				autoHeight: true,
				bodyStyle: 'padding: 5px',
				frame: true,
				id: 'TreatmentCatEditForm',
				labelAlign: 'right',
				labelWidth: 145,
				reader: new Ext.data.JsonReader({
					success: Ext.emptyFn
				}, [
					{ name: 'TreatmentCat_id' },
					{ name: 'TreatmentCat_Code' },
					{ name: 'TreatmentCat_Name' },
					{ name: 'TreatmentCat_IsDeletes' }
				]),
				url: '/?c=TreatmentCat&m=saveItem',
				items: [{
					id: 'TCEW_TreatmentCat_id',
					name: 'TreatmentCat_id',
					xtype: 'hidden'
				}, {
					allowBlank: false,
					autoCreate: {
						maxLength: 14,
						tag: 'input',
						type: 'text'
					},
					enableKeyEvents: false,
					fieldLabel: lang['kod'],
					maskRe: /\d/,
					id: 'TCEW_TreatmentCat_Code',
					name: 'TreatmentCat_Code',
					onTriggerClick: function() {
						var Mask = new Ext.LoadMask(Ext.get('TCEF_edit_window'), { msg: "Пожалуйста, подождите, идет загрузка данных формы..." });
						Mask.show();
						Ext.Ajax.request({
							callback: function(opt, success, resp) {
								Mask.hide();

								var form = this.findById('TreatmentCatEditForm').getForm();
								var response_obj = Ext.util.JSON.decode(resp.responseText);

								if (response_obj.Code != '')
								{
									form.findField('TreatmentCat_Code').setValue(response_obj.Code);
								}
							}.createDelegate(this.ownerCt.ownerCt),
							params: {
								Object: current_window.owner.object
							},
							url: '/?c=TreatmentCat&m=getMaxItemCode'
						});
					},
					tabIndex: TABINDEX_TCEF,
					triggerAction: 'all',
					triggerClass: 'x-form-plus-trigger',
					width: 400,
					xtype: 'trigger'
				}, {
					allowBlank: false,
					fieldLabel: lang['naimenovanie'],
					id: 'TCEW_TreatmentCat_Name',
					name: 'TreatmentCat_Name',
					tabIndex: TABINDEX_TCEF + 1,
					width: 400,
					xtype: 'textfield'
				},
				{
					id: 'TCEW_TreatmentCat_IsDeletes',
					name: 'TreatmentCat_IsDeletes',
					hiddenName: 'TreatmentCat_IsDeletes',
					value: 2,
					xtype: 'hidden'
				}
				/*new sw.Promed.SwYesNoCombo({
					allowBlank: false,
					disabled: true,
					fieldLabel: lang['razreshit_udalenie'],
					id: 'TCEW_TreatmentCat_IsDeletes',
					name: 'TreatmentCat_IsDeletes',
					hiddenName: 'TreatmentCat_IsDeletes',
					value: 2,
					tabIndex: TABINDEX_TCEF + 2,
					width: 100
				})
				*/]
			})]
		});
		sw.Promed.swTreatmentCatEditWindow.superclass.initComponent.apply(this, arguments);
	},
	layout: 'fit',
	listeners: {
		'hide': function() {
			this.onHide();
		}
	},
	modal: true,
	onHide: Ext.emptyFn,
	plain: true,
	resizable: false,
	show: function() {
		sw.Promed.swTreatmentCatEditWindow.superclass.show.apply(this, arguments);

		var current_window = this;
		var form = current_window.findById('TreatmentCatEditForm');

		form.getForm().reset();

		current_window.action = null;
		treatmentspr_id = null;
		current_window.callback = Ext.emptyFn;
		current_window.owner = null;
		current_window.onHide = Ext.emptyFn;
		if ( arguments[0] ) {
			if ( arguments[0].action )
				current_window.action = arguments[0].action;

			if ( arguments[0].callback )
				current_window.callback = arguments[0].callback;

			if ( arguments[0].owner ) {
				current_window.owner = arguments[0].owner;
				//alert(current_window.owner.object + ' + ' + current_window.owner.stringfields[0].name);
			}

			if ( arguments[0].onHide )
				current_window.onHide = arguments[0].onHide;

			switch ( current_window.owner.object ) {
				case 'TreatmentCat':
					if ( arguments[0].TreatmentCat_id )
						treatmentspr_id = arguments[0].TreatmentCat_id;
					break;
				case 'TreatmentMethodDispatch':
					if ( arguments[0].TreatmentMethodDispatch_id )
						treatmentspr_id = arguments[0].TreatmentMethodDispatch_id;
					break;
				case 'TreatmentRecipientType':
					if ( arguments[0].TreatmentRecipientType_id )
						treatmentspr_id = arguments[0].TreatmentRecipientType_id;
					break;
			}

			// log(arguments[0]);
		}
		if ( treatmentspr_id )
		{			form.getForm().setValues({
				TreatmentCat_id: treatmentspr_id
			});
		}
		else
		{			form.getForm().setValues({
				TreatmentCat_id: 0
			});
		}
		if ( current_window.action ) {
			switch ( current_window.action ) {
				case 'add':
					current_window.setTitle(lang['dobavlenie_spravochnik']);
					current_window.enableEdit(true);
					form.getForm().findField('TreatmentCat_Code').focus(100, true);
					var Mask = new Ext.LoadMask(Ext.get('TCEF_edit_window'), { msg: "Пожалуйста, подождите, идет загрузка данных формы..." });
					Mask.show();
					Ext.Ajax.request({
						callback: function(opt, success, resp) {
							Mask.hide();
							var form = this.findById('TreatmentCatEditForm').getForm();
							var response_obj = Ext.util.JSON.decode(resp.responseText);
							if (response_obj.Code != '')
								form.findField('TreatmentCat_Code').setValue(response_obj.Code);
						}.createDelegate(this.ownerCt.ownerCt),
						params: {
							Object: current_window.owner.object
						},
						url: '/?c=TreatmentCat&m=getMaxItemCode'
					});
					break;

				case 'edit':
					current_window.setTitle(lang['redaktirovanie_spravochnik']);
					form.getForm().findField('TreatmentCat_Code').focus(100, true);
					var Mask = new Ext.LoadMask(Ext.get('TCEF_edit_window'), { msg: "Пожалуйста, подождите, идет загрузка данных формы..."} );
					Mask.show();
					form.getForm().load({
						failure: function() {
							sw.swMsg.alert(lang['oshibka'], lang['ne_udalos_zagruzit_dannyie_s_servera'], function() { current_window.hide(); } );
						},
						params: {
							id: treatmentspr_id,
							Object: current_window.owner.object
						},
						success: function() {
							Mask.hide();
						},
						url: '/?c=TreatmentCat&m=getItem'
					});

					break;
			}
		}
	},
	submit: function(check_double_cancel, check_code) {
		var current_window = this;
		var form = this.findById('TreatmentCatEditForm').getForm();

		if ( !form.isValid() ) {
			sw.swMsg.alert(lang['oshibka_zapolneniya_formyi'], lang['proverte_pravilnost_zapolneniya_poley_formyi']);
			return;
		}
		var params = {
			Object: current_window.owner.object
		};
		form.submit({
			failure: function (form, action) {
				if ( action.result ) {
					if ( action.result.Error_Msg ) {
						sw.swMsg.alert(lang['oshibka'], action.result.Error_Msg);
					}
					else {
						sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_1]']);
					}
				}
			}.createDelegate(this),
			params: params,
			success: function(form, action) {				this.hide();
				if (!action.result.id ) {
					sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshla_oshibka']);
					return false;
				}
				var data = new Object();
				data.ItemData = new Object();
				//data.ItemData.TreatmentCat_id = action.result.TreatmentCat_id;
				//data.ItemData.TreatmentCat_Code = this.findById('TCEW_TreatmentCat_Code').getRawValue();
				//data.ItemData.TreatmentCat_Name = this.findById('TCEW_TreatmentCat_Name').getRawValue();
				//data.ItemData.TreatmentCat_IsDeletes = this.findById('TCEW_TreatmentCat_IsDeletes').getRawValue();
				current_window.callback(data);
			}.createDelegate(this)
		});
	},
	title: lang['redaktirovanie_spravochnik'],
	width: 600
});