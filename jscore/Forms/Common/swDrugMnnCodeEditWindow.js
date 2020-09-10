/**
 * swDrugMnnCodeEditWindow - окно редактирования регионального кода МНН
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
sw.Promed.swDrugMnnCodeEditWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: false,
	title: lang['spravochnik_mnn_redaktirovanie'],
	layout: 'border',
	id: 'DrugMnnCodeEditWindow',
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
			wnd.form.findField('Actmatters_id').focus(true, 50);
		}
	},
	onHide: Ext.emptyFn,
	doSave:  function() {
		var wnd = this;
		if ( !this.form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					wnd.findById('DrugMnnCodeEditForm').getFirstInvalidEl().focus(true);
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
				wnd.callback(wnd.owner, action.result.DrugMnnCode_id);
				wnd.hide();
			}
		});
	},
	enableEdit: function(enable) {
		if (enable) {
			this.form.findField('Actmatters_id').enable();
			this.form.findField('DrugMnnCode_Code').enable();
			this.buttons[0].enable();
		} else {
			this.form.findField('Actmatters_id').disable();
			this.form.findField('DrugMnnCode_Code').disable();
			this.buttons[0].disable();
		}
	},
	show: function() {
		var wnd = this;
		sw.Promed.swDrugMnnCodeEditWindow.superclass.show.apply(this, arguments);
		this.action = '';
		this.callback = Ext.emptyFn;
		this.DrugMnnCode_id = null;
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
		if ( arguments[0].DrugMnnCode_id ) {
			this.DrugMnnCode_id = arguments[0].DrugMnnCode_id;
		}
		this.form.reset();
		this.title = lang['spravochnik_mnn'];
		var loadMask = new Ext.LoadMask(this.form.getEl(), {msg:lang['zagruzka']});
		loadMask.show();
		switch (this.action) {
			case 'add':
				this.setTitle(this.title+lang['_dobavlenie']);
				this.enableEdit(true);
				this.setHeight(175);
				this.form.findField('DrugMnn_Code').ownerCt.show();
				loadMask.hide();
				break;
			case 'edit':
			case 'view':
				this.setTitle(this.title+(this.action == 'edit' ? lang['_redaktirovanie'] : lang['_prosmotr']));
				this.enableEdit(this.action == 'edit');
				this.form.findField('DrugMnn_Code').ownerCt.hide();
				this.setHeight(150);
				Ext.Ajax.request({
					failure:function () {
						sw.swMsg.alert(lang['oshibka'], lang['ne_udalos_poluchit_dannyie_s_servera']);
						loadMask.hide();
						wnd.hide();
					},
					params:{
						DrugMnnCode_id: wnd.DrugMnnCode_id
					},
					success: function (response) {
						var result = Ext.util.JSON.decode(response.responseText);
						if (!result[0]) {
							return false;
						}

						var am_combo = wnd.form.findField('Actmatters_id');
						am_combo.getStore().clearFilter();
						am_combo.lastQuery = '';

						wnd.form.setValues(result[0]);
						loadMask.hide();
						return true;
					},
					url:'/?c=DrugNomen&m=loadDrugMnnCode'
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
				id: 'DrugMnnCodeEditForm',
				style: 'margin-bottom: 0.5em;',
				bodyStyle:'background:#DFE8F6;padding:5px;',
				border: true,
				labelWidth: 150,
				labelAlign: 'right',
				collapsible: true,
				region: 'north',
				url:'/?c=DrugNomen&m=saveDrugMnnCode',
				items: [{
					name: 'DrugMnnCode_id',
					xtype: 'hidden',
					value: 0
				}, {
					fieldLabel: lang['deystvuyuschee_veschestvo'],
					hiddenName: 'Actmatters_id',
					allowBlank: false,
					xtype: 'swrlsactmatterscombo',
					anchor: '100%',
					width: 250,
					minChars: 3,
					listeners: {
						select: function(combo) {
							var code_combo = wnd.form.findField('DrugMnn_Code');
							if (combo.getRawValue() != '') {
								code_combo.getStore().load({
									params: {
										DrugMnn_Name:combo.getRawValue()
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
						hiddenName: 'DrugMnn_Code',
						valueField: 'DrugMnn_Code',
						displayField: 'DrugMnn_FullName',
						mode: 'local',
						store: new Ext.data.JsonStore({
							autoLoad: false,
							fields: [
								{ name: 'DrugMnn_id', type: 'int' },
								{ name: 'DrugMnn_Code', type: 'int' },
								{ name: 'DrugMnn_Name', type: 'string' },
								{ name: 'DrugMnn_FullName', type: 'string' }
							],
							key: 'DrugMnn_id',
							sortInfo: {
								field: 'DrugMnn_Name'
							},
							url: '/?c=DrugNomen&m=loadDboDrugMnnCodeListByName'
						}),
						tpl: new Ext.XTemplate(
							'<tpl for="."><div class="x-combo-list-item">',
							'<span style="color:red;">{DrugMnn_Code}</span> {DrugMnn_Name}',
							'</div></tpl>'
						),
						triggerAction: 'all',
						listeners: {
							select: function(combo) {
								var value = combo.getValue();
								if (value > 0) {
									wnd.form.findField('DrugMnnCode_Code').setValue(value);
								}
							}
						},
						width: 250,
						anchor: '100%',
						xtype: 'swbaseremotecombosingletrigger'
					}]
				}, {
					fieldLabel: lang['kod'],
					name: 'DrugMnnCode_Code',
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
				{name: 'DrugMnnCode_id'},
				{name: 'Actmatters_id'},
				{name: 'DrugMnnCode_Code'}
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
		sw.Promed.swDrugMnnCodeEditWindow.superclass.initComponent.apply(this, arguments);
		this.form = this.findById('DrugMnnCodeEditForm').getForm();
	}
});