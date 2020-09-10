/**
 * swDrugTorgCodeEditWindow - окно редактирования регионального кода Торгового наименования
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2014 Swan Ltd.
 * @author       Salakhov R.
 * @version      09.2014
 * @comment
 */
sw.Promed.swDrugTorgCodeEditWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: false,
	title: lang['spravochnik_torgovyih_naimenovaniy_redaktirovanie'],
	layout: 'border',
	id: 'DrugTorgCodeEditWindow',
	modal: true,
	shim: false,
	width: 500,
	height: 150,
	resizable: false,
	maximizable: false,
	maximized: false,
	listeners: {
		hide: function() {
			this.onHide();
		},
		show: function(wnd) {
			wnd.form.findField('Tradenames_id').focus(true, 50);
		}
	},
	onHide: Ext.emptyFn,
	doSave:  function() {
		var wnd = this;
		if ( !this.form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					wnd.findById('DrugTorgCodeEditForm').getFirstInvalidEl().focus(true);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
		this.submit();
		return true;
	},
	submit: function() {
		var wnd = this;
		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение..."});
		loadMask.show();
		var params = {};
		params.action = wnd.action;
		this.form.submit({
			params: params,
			failure: function(result_form, action) {
				loadMask.hide();
				if (action.result) {
					if (action.result.Error_Code) {
						Ext.Msg.alert(lang['oshibka_#']+action.result.Error_Code, action.result.Error_Message);
					}
				}
			},
			success: function(result_form, action) {
				loadMask.hide();
				wnd.callback(wnd.owner, action.result.DrugTorgCode_id);
				wnd.hide();
			}
		});
	},
	enableEdit: function(enable) {
		if (enable) {
			this.form.findField('Tradenames_id').enable();
			this.form.findField('DrugTorgCode_Code').enable();
			this.buttons[0].enable();
		} else {
			this.form.findField('Tradenames_id').disable();
			this.form.findField('DrugTorgCode_Code').disable();
			this.buttons[0].disable();
		}
	},
	show: function() {
		var wnd = this;
		sw.Promed.swDrugTorgCodeEditWindow.superclass.show.apply(this, arguments);
		this.action = '';
		this.callback = Ext.emptyFn;
		this.DrugTorgCode_id = null;
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
		if ( arguments[0].DrugTorgCode_id ) {
			this.DrugTorgCode_id = arguments[0].DrugTorgCode_id;
		}
		this.form.reset();
		this.title = lang['spravochnik_torgovyih_naimenovaniy'];
		var loadMask = new Ext.LoadMask(this.form.getEl(), {msg:lang['zagruzka']});
		loadMask.show();
		switch (this.action) {
			case 'add':
				this.setTitle(this.title+lang['_dobavlenie']);
				this.enableEdit(true);
				this.setHeight(175);
				this.form.findField('DrugTorg_Code').ownerCt.show();
				loadMask.hide();
				break;
			case 'edit':
			case 'view':
				this.setTitle(this.title+(this.action == 'edit' ? lang['_redaktirovanie'] : lang['_prosmotr']));
				this.enableEdit(this.action == 'edit');
				this.form.findField('DrugTorg_Code').ownerCt.hide();
				this.setHeight(150);
				Ext.Ajax.request({
					failure:function () {
						sw.swMsg.alert(lang['oshibka'], lang['ne_udalos_poluchit_dannyie_s_servera']);
						loadMask.hide();
						wnd.hide();
					},
					params:{
						DrugTorgCode_id: wnd.DrugTorgCode_id
					},
					success: function (response) {
						var result = Ext.util.JSON.decode(response.responseText);
						if (!result[0]) {
							return false;
						}

						var tn_combo = wnd.form.findField('Tradenames_id');
						tn_combo.getStore().clearFilter();
						tn_combo.lastQuery = '';

						wnd.form.setValues(result[0]);
						loadMask.hide();
						return true;
					},
					url:'/?c=DrugNomen&m=loadDrugTorgCode'
				});
				break;
		};
		return true;
	},
	initComponent: function() {
		var wnd = this;

		var form = new Ext.Panel({
			autoScroll: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			border: false,
			frame: true,
			region: 'center',
			items: [{
				xtype: 'form',
				autoHeight: true,
				id: 'DrugTorgCodeEditForm',
				style: 'margin-bottom: 0.5em;',
				bodyStyle:'background:#DFE8F6;padding:5px;',
				border: true,
				labelWidth: 150,
				labelAlign: 'right',
				collapsible: true,
				region: 'north',
				url:'/?c=DrugNomen&m=saveDrugTorgCode',
				items: [{
					name: 'DrugTorgCode_id',
					xtype: 'hidden',
					value: 0
				}, {
					fieldLabel: lang['torgovoe_naimenovanie'],
					hiddenName: 'Tradenames_id',
					allowBlank: false,
					xtype: 'swrlstradenamescombo',
					anchor: '100%',
					width: 250,
					minChars: 3,
					listeners: {
						select: function(combo) {
							var code_combo = wnd.form.findField('DrugTorg_Code');
							if (combo.getRawValue() != '') {
								code_combo.getStore().load({
									params: {
										DrugTorg_Name:combo.getRawValue()
									}
								});
							}
						}
					}
				}, {
					layout: 'form',
					items: [{
						enableKeyEvents: true,
						editable: false,
						fieldLabel: lang['kod_iz_spravochnika'],
						hiddenName: 'DrugTorg_Code',
						valueField: 'DrugTorg_Code',
						displayField: 'DrugTorg_FullName',
						mode: 'local',
						store: new Ext.data.JsonStore({
							autoLoad: false,
							fields: [
								{ name: 'DrugTorg_id', type: 'int' },
								{ name: 'DrugTorg_Code', type: 'int' },
								{ name: 'DrugTorg_Name', type: 'string' },
								{ name: 'DrugTorg_FullName', type: 'string' }
							],
							key: 'DrugTorg_id',
							sortInfo: {
								field: 'DrugTorg_Name'
							},
							url: '/?c=DrugNomen&m=loadDboDrugTorgCodeListByName'
						}),
						tpl: new Ext.XTemplate(
							'<tpl for="."><div class="x-combo-list-item">',
							'<span style="color:red;">{DrugTorg_Code}</span> {DrugTorg_Name}',
							'</div></tpl>'
						),
						triggerAction: 'all',
						listeners: {
							select: function(combo) {
								var value = combo.getValue();
								if (value > 0) {
									wnd.form.findField('DrugTorgCode_Code').setValue(value);
								}
							}
						},
						width: 250,
						anchor: '100%',
						xtype: 'swbaseremotecombosingletrigger'
					}]
				}, {
					fieldLabel: lang['kod'],
					name: 'DrugTorgCode_Code',
					allowBlank: false,
					maxLength: 50,
					maskRe: /[0-9]/,
					xtype: 'textfield',
					anchor: '100%',
					width: 250
				}]
			}],
			reader: new Ext.data.JsonReader({
				success: Ext.emptyFn
			}, [
				{name: 'DrugTorgCode_id'},
				{name: 'Tradenames_id'},
				{name: 'DrugTorgCode_Code'}
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
		sw.Promed.swDrugTorgCodeEditWindow.superclass.initComponent.apply(this, arguments);
		this.form = this.findById('DrugTorgCodeEditForm').getForm();
	}
});