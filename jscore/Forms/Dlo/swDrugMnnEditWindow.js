/**
* swDrugMnnEditWindow - окно редактирования МНН.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      DLO
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Stas Bykov aka Savage (savage1981@gmail.com)
* @version      0.001-23.09.2009
* @comment      Префикс для id компонентов DMEF (DrugMnnEditForm)
*               tabIndex: ???
*
*
* @input data: action - действие (add, edit, view)
*              DrugMnn_id - ID МНН
*/

sw.Promed.swDrugMnnEditWindow = Ext.extend(sw.Promed.BaseForm, {
	action: null,
	autoHeight: true,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	doSave: function() {
		if (this.action != 'edit')
		{			return false;
		}

		var current_window = this;
		var form = current_window.findById('DrugMnnEditForm');

		if ( !form.getForm().isValid() )
		{
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					form.getFirstInvalidEl().focus(true);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		var loadMask = new Ext.LoadMask(Ext.get('DrugMnnEditWindow'), { msg: "Подождите, идет сохранение..." });
		loadMask.show();

		form.getForm().submit({
			failure: function(result_form, action) {
				loadMask.hide();

				if (action.result)
				{
					if (action.result.Error_Msg)
					{
						sw.swMsg.alert(lang['oshibka'], action.result.Error_Msg);
					}
					else
					{
						sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_3]']);
					}
				}
			},
			success: function(result_form, action) {
				loadMask.hide();

				if (action.result)
				{
					if (action.result.DrugMnn_id > 0)
					{
						var drug_mnn_id = action.result.DrugMnn_id;
						var response = new Object();

						form.findById('DMEF_DrugMnn_id').setValue(drug_mnn_id);

						response.DrugMnn_Code = form.findById('DMEF_DrugMnn_Code').getValue();
						response.DrugMnn_id = drug_mnn_id;
						response.DrugMnn_Name = form.findById('DMEF_DrugMnn_Name').getValue();
						response.DrugMnn_NameLat = form.findById('DMEF_DrugMnn_NameLat').getValue();

						current_window.callback({ DrugMnnData: response });
						current_window.hide();
					}
					else
					{
						if (action.result.Error_Msg)
						{
							sw.swMsg.alert(lang['oshibka'], action.result.Error_Msg);
						}
						else
						{
							sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_1]']);
						}
					}
				}
				else
				{
					sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_2]']);
				}
			}
		});
	},
	draggable: true,
	enableEdit: function(enable) {
		var form = this.findById('DrugMnnEditForm');

		if (enable)
		{
			form.findById('DMEF_DrugMnn_NameLat').enable();
			this.buttons[0].enable();
		}
		else
		{
			form.findById('DMEF_DrugMnn_NameLat').disable();
			this.buttons[0].disable();
		}
	},
	id: 'DrugMnnEditWindow',
    initComponent: function() {
        Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.ownerCt.doSave();
				},
				iconCls: 'save16',
				// tabIndex: 204,
				text: BTN_FRMSAVE
			}, {				text: '-'
			},
			HelpButton(this/*, 206*/),
			{
				handler: function() {
					this.ownerCt.hide();
				},
				iconCls: 'cancel16',
				onTabElement: 'DMEF_DrugMnn_NameLat',
				// tabIndex: 207,
				text: BTN_FRMCANCEL
			}],
			items: [ new Ext.form.FormPanel({
				autoHeight: true,
				bodyBorder: false,
				bodyStyle: 'padding: 5px 5px 0',
				border: false,
				frame: false,
				id: 'DrugMnnEditForm',
				labelAlign: 'right',
				labelWidth: 150,
				items: [{
					id: 'DMEF_DrugMnn_id',
					name: 'DrugMnn_id',
					value: 0,
					xtype: 'hidden'
				}, {					disabled: true,
					fieldLabel: lang['kod'],
					id: 'DMEF_DrugMnn_Code',
					name: 'DrugMnn_Code',
					width: 150,
					xtype: 'textfield'
				}, {
					disabled: true,
					fieldLabel: lang['naimenovanie'],
					id: 'DMEF_DrugMnn_Name',
					name: 'DrugMnn_Name',
					width: 450,
					xtype: 'textfield'
				}, {
					fieldLabel: lang['latinskoe_naimenovanie'],
					id: 'DMEF_DrugMnn_NameLat',
					maskRe: new RegExp("^[0-9a-zA-Z\-\.\,\+ ]*$"),
					name: 'DrugMnn_NameLat',
					width: 450,
					xtype: 'textfield'
				}],
				keys: [{
					alt: true,
					fn: function(inp, e) {
						switch (e.getKey())
						{
							case Ext.EventObject.C:
								this.doSave();
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
					success: Ext.emptyFn
				}, [
					{ name: 'DrugMnn_Code' },
					{ name: 'DrugMnn_id' },
					{ name: 'DrugMnn_Name' },
					{ name: 'DrugMnn_NameLat' }
				]),
				url: '/?c=Drug&m=saveDrugMnnLatinName'
			})]
		});
		sw.Promed.swDrugMnnEditWindow.superclass.initComponent.apply(this, arguments);
	},
	layout: 'form',
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
		sw.Promed.swDrugMnnEditWindow.superclass.show.apply(this, arguments);

		var current_window = this;
		var form = current_window.findById('DrugMnnEditForm');
		form.getForm().reset();

		current_window.action = 'view';
		current_window.callback = Ext.emptyFn;
		current_window.onHide = Ext.emptyFn;

		if (!arguments[0])
		{
			sw.swMsg.alert(lang['soobschenie'], lang['nevernyie_parametryi'], function() { current_window.hide(); });
			return false;
		}

		var loadMask = new Ext.LoadMask(Ext.get('DrugMnnEditWindow'), { msg: LOAD_WAIT });
		loadMask.show();

		form.getForm().setValues(arguments[0]);

		if (arguments[0].callback)
		{
			current_window.callback = arguments[0].callback;
		}

		if (arguments[0].onHide)
		{
			current_window.onHide = arguments[0].onHide;
		}

		if (Ext.globalOptions.globals.groups && Ext.globalOptions.globals.groups.toString().indexOf('Admin') != -1)
		{			current_window.action = 'edit';
		}

		switch (current_window.action)
		{
			case 'edit':
        	    current_window.setTitle(WND_DLO_DRUGMNNEDIT);
				current_window.enableEdit(true);
				form.findById('DMEF_DrugMnn_NameLat').focus(true, 250);
                break;

			case 'view':
				current_window.setTitle(WND_DLO_DRUGMNNVIEW);
				current_window.enableEdit(false);
				current_window.buttons[3].focus();
				break;
		}

		loadMask.hide();
	},
	width: 700
});