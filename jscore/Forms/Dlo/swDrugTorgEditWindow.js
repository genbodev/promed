/**
* swDrugTorgEditWindow - окно редактирования торгового наименования медикамента.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      DLO
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Stas Bykov aka Savage (savage1981@gmail.com)
* @version      0.001-06.10.2009
* @comment      Префикс для id компонентов DTEF (DrugTorgEditForm)
*               tabIndex: ???
*
*
* @input data: action - действие (add, edit, view)
*              DrugTorg_id - ID торгового наименования медикамента
*/

sw.Promed.swDrugTorgEditWindow = Ext.extend(sw.Promed.BaseForm, {
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
		var form = current_window.findById('DrugTorgEditForm');

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

		var loadMask = new Ext.LoadMask(Ext.get('DrugTorgEditWindow'), { msg: "Подождите, идет сохранение..." });
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
					if (action.result.DrugTorg_id > 0)
					{
						var drug_torg_id = action.result.DrugTorg_id;
						var response = new Object();

						form.findById('DTEF_DrugTorg_id').setValue(drug_torg_id);

						response.DrugTorg_Code = form.findById('DTEF_DrugTorg_Code').getValue();
						response.DrugTorg_id = drug_torg_id;
						response.DrugTorg_Name = form.findById('DTEF_DrugTorg_Name').getValue();
						response.DrugTorg_NameLat = form.findById('DTEF_DrugTorg_NameLat').getValue();

						current_window.callback({ DrugTorgData: response });
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
		var form = this.findById('DrugTorgEditForm');

		if (enable)
		{
			form.findById('DTEF_DrugTorg_NameLat').enable();
			this.buttons[0].enable();
		}
		else
		{
			form.findById('DTEF_DrugTorg_NameLat').disable();
			this.buttons[0].disable();
		}
	},
	id: 'DrugTorgEditWindow',
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
				onTabElement: 'DTEF_DrugTorg_NameLat',
				// tabIndex: 207,
				text: BTN_FRMCANCEL
			}],
			items: [ new Ext.form.FormPanel({
				autoHeight: true,
				bodyBorder: false,
				bodyStyle: 'padding: 5px 5px 0',
				border: false,
				frame: false,
				id: 'DrugTorgEditForm',
				labelAlign: 'right',
				labelWidth: 150,
				items: [{
					id: 'DTEF_DrugTorg_id',
					name: 'DrugTorg_id',
					value: 0,
					xtype: 'hidden'
				}, {					disabled: true,
					fieldLabel: lang['kod'],
					id: 'DTEF_DrugTorg_Code',
					name: 'DrugTorg_Code',
					width: 150,
					xtype: 'textfield'
				}, {
					disabled: true,
					fieldLabel: lang['naimenovanie'],
					id: 'DTEF_DrugTorg_Name',
					name: 'DrugTorg_Name',
					width: 450,
					xtype: 'textfield'
				}, {
					fieldLabel: lang['latinskoe_naimenovanie'],
					id: 'DTEF_DrugTorg_NameLat',
					maskRe: new RegExp("^[0-9a-zA-Z\-\.\,\+ ]*$"),
					name: 'DrugTorg_NameLat',
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
					{ name: 'DrugTorg_Code' },
					{ name: 'DrugTorg_id' },
					{ name: 'DrugTorg_Name' },
					{ name: 'DrugTorg_NameLat' }
				]),
				url: '/?c=Drug&m=saveDrugTorgLatinName'
			})]
		});
		sw.Promed.swDrugTorgEditWindow.superclass.initComponent.apply(this, arguments);
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
		sw.Promed.swDrugTorgEditWindow.superclass.show.apply(this, arguments);

		var current_window = this;
		var form = current_window.findById('DrugTorgEditForm');
		form.getForm().reset();

		current_window.action = 'view';
		current_window.callback = Ext.emptyFn;
		current_window.onHide = Ext.emptyFn;

		if ( !arguments[0] )
		{
			sw.swMsg.alert(lang['soobschenie'], lang['nevernyie_parametryi'], function() { current_window.hide(); });
			return false;
		}

		var loadMask = new Ext.LoadMask(Ext.get('DrugTorgEditWindow'), { msg: LOAD_WAIT });
		loadMask.show();

		form.getForm().setValues(arguments[0]);

		if ( arguments[0].callback )
		{
			current_window.callback = arguments[0].callback;
		}

		if ( arguments[0].onHide )
		{
			current_window.onHide = arguments[0].onHide;
		}

		if ( Ext.globalOptions.globals.groups && Ext.globalOptions.globals.groups.toString().indexOf('Admin') != -1)
		{			current_window.action = 'edit';
		}

		switch (current_window.action)
		{
			case 'edit':
        	    current_window.setTitle(WND_DLO_DRUGTORGEDIT);
				current_window.enableEdit(true);
				form.findById('DTEF_DrugTorg_NameLat').focus(true, 250);
                break;

			case 'view':
				current_window.setTitle(WND_DLO_DRUGTORGVIEW);
				current_window.enableEdit(false);
				current_window.buttons[3].focus();
				break;
		}

		loadMask.hide();
	},
	width: 700
});