/**
 * swDrugMarkupEditWindow - окно редактирования "Величины надбавок"
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2014 Swan Ltd.
 * @author       Salakhov R.
 * @version      01.2014
 * @comment
 */
sw.Promed.swDrugMarkupEditWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: false,
	title: lang['predelnyie_nadbavki_na_jnvlp'],
	layout: 'border',
	id: 'DrugMarkupEditWindow',
	modal: true,
	shim: false,
	width: 800,
	height: 390,
	resizable: false,
	maximizable: false,
	maximized: false,
	listeners: {
		hide: function() {
			this.onHide();
		}
	},
	onHide: Ext.emptyFn,
	doSave:  function() {
		var wnd = this;
		if ( !this.form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					wnd.findById('DrugMarkupEditForm').getFirstInvalidEl().focus(true);
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

				var id = action.result.DrugMarkup_id;
				wnd.FileUploadPanel.listParams = {
					ObjectName: 'DrugMarkup',
					ObjectID: id
				};
				wnd.FileUploadPanel.saveChanges();

				wnd.callback(wnd.owner, id);
				wnd.hide();
			}
		});
	},
	enableEdit: function(enable) {
		if (enable) {
			this.form.findField('DrugMarkup_begDT').enable();
			this.form.findField('DrugMarkup_endDT').enable();
			this.form.findField('DrugMarkup_MinPrice').enable();
			this.form.findField('DrugMarkup_MaxPrice').enable();
			this.form.findField('DrugMarkup_Wholesale').enable();
			this.form.findField('DrugMarkup_Retail').enable();
			this.form.findField('DrugMarkup_IsNarkoDrug').enable();
			this.form.findField('Drugmarkup_Delivery').enable();
			this.FileUploadPanel.enable();
			this.buttons[0].enable();
		} else {
			this.form.findField('DrugMarkup_begDT').disable();
			this.form.findField('DrugMarkup_endDT').disable();
			this.form.findField('DrugMarkup_MinPrice').disable();
			this.form.findField('DrugMarkup_MaxPrice').disable();
			this.form.findField('DrugMarkup_Wholesale').disable();
			this.form.findField('DrugMarkup_Retail').disable();
			this.form.findField('DrugMarkup_IsNarkoDrug').disable();
			this.form.findField('Drugmarkup_Delivery').disable();
			this.FileUploadPanel.disable();
			this.buttons[0].disable();
		}
	},
	show: function() {
		var wnd = this;
		sw.Promed.swDrugMarkupEditWindow.superclass.show.apply(this, arguments);
		this.action = '';
		this.callback = Ext.emptyFn;
		this.DrugMarkup_id = null;
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
		if ( arguments[0].DrugMarkup_id ) {
			this.DrugMarkup_id = arguments[0].DrugMarkup_id;
		}
		this.form.reset();
		this.FileUploadPanel.reset();
		this.title = lang['predelnyie_nadbavki_na_jnvlp'];
		var loadMask = new Ext.LoadMask(this.form.getEl(), {msg:lang['zagruzka']});
		loadMask.show();
		switch (this.action) {
			case 'add':
				this.setTitle(this.title+lang['_dobavlenie']);
				this.enableEdit(true);
				this.FileUploadPanel.listParams = {
					ObjectName: 'DrugMarkup',
					ObjectID: null
				};
				loadMask.hide();
				break;
			case 'edit':
			case 'view':
				this.setTitle(this.title+(this.action == 'edit' ? lang['_redaktirovanie'] : lang['_prosmotr']));

				wnd.enableEdit(wnd.action == 'edit');
				//загружаем файлы
				this.FileUploadPanel.listParams = {
					ObjectName: 'DrugMarkup',
					ObjectID: wnd.DrugMarkup_id,
					callback: function() {
						wnd.enableEdit(wnd.action == 'edit');
					}
				};
				this.FileUploadPanel.loadData();

				Ext.Ajax.request({
					failure:function () {
						sw.swMsg.alert(lang['oshibka'], lang['ne_udalos_poluchit_dannyie_s_servera']);
						loadMask.hide();
						wnd.hide();
					},
					params:{
						DrugMarkup_id: wnd.DrugMarkup_id
					},
					success: function (response) {
						var result = Ext.util.JSON.decode(response.responseText);
						if (!result[0]) {
							return false;
						}
						wnd.form.setValues(result[0]);
						loadMask.hide();
						return true;
					},
					url:'/?c=DrugMarkup&m=load'
				});
				break;
		};
		return true;
	},
	initComponent: function() {
		this.FileUploadPanel = new sw.Promed.FileUploadPanel({
			win: this,
			//width: 1000,
			buttonAlign: 'left',
			buttonLeftMargin: 100,
			labelWidth: 150,
			limitCountCombo: 1,
			commentTextfieldWidth: 220,
			commentTextColumnWidth: .45,
			uploadFieldColumnWidth: .5,
			folder: 'pmmedia/',
			fieldsPrefix: 'pmMediaData',
			id: this.id+'FileUploadPanel',
			style: 'background: transparent',
			dataUrl: '/?c=PMMediaData&m=loadpmMediaDataListGrid',
			saveUrl: '/?c=PMMediaData&m=uploadFile',
			saveChangesUrl: '/?c=PMMediaData&m=saveChanges',
			deleteUrl: '/?c=PMMediaData&m=deleteFile'
		});

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
				id: 'DrugMarkupEditForm',
				style: 'margin-bottom: 0.5em;',
				bodyStyle:'background:#DFE8F6;padding:5px;',
				border: true,
				labelWidth: 195,
				labelAlign: 'right',
				collapsible: true,
				region: 'north',
				url:'/?c=DrugMarkup&m=save',
				items: [{
					name: 'DrugMarkup_id',
					xtype: 'hidden',
					value: 0
				}, {
					allowBlank:true,
					fieldLabel: lang['data_nachala_deystviya'],
					name: 'DrugMarkup_begDT',
					width: 100,
					xtype: 'swdatefield',
					format: 'd.m.Y',
					plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ]
				}, {
					allowBlank:true,
					fieldLabel: lang['data_okonchaniya_deystviya'],
					name: 'DrugMarkup_endDT',
					width: 100,
					xtype: 'swdatefield',
					format: 'd.m.Y',
					plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ]
				}, {
					allowDecimals:true,
					allowNegative:true,
					allowBlank:false,
					fieldLabel: lang['minimalnaya_otpusknaya_tsena'],
					name: 'DrugMarkup_MinPrice',
					width: 100,
					xtype:'numberfield'
				}, {
					allowDecimals:true,
					allowNegative:true,
					allowBlank:false,
					fieldLabel: lang['maksimalnaya_otpusknaya_tsena'],
					name: 'DrugMarkup_MaxPrice',
					width: 100,
					xtype:'numberfield'
				}, {
					allowDecimals:true,
					allowNegative:true,
					allowBlank:true,
					fieldLabel: lang['predelnaya_opt_nadbavka_%'],
					name: 'DrugMarkup_Wholesale',
					width: 100,
					xtype:'numberfield'
				}, {
					allowDecimals:true,
					allowNegative:true,
					allowBlank:true,
					fieldLabel: lang['predelnaya_rozn_nadbavka_%'],
					name: 'DrugMarkup_Retail',
					width: 100,
					xtype:'numberfield'
				}, {
					fieldLabel: lang['narkoticheskiy_preparat'],
					hiddenName: 'DrugMarkup_IsNarkoDrug',
					allowBlank:true,
					xtype: 'swyesnocombo',
					width: 100
				}, {
					fieldLabel: lang['zona_dostavki'],
					name: 'Drugmarkup_Delivery',
					allowBlank:true,
					xtype: 'textfield',
					width: 250
				}]
			}, {
				xtype: 'fieldset',
				autoHeight: true,
				title: lang['dokument'],
				style: 'padding: 3px; margin-bottom: 2px; display:block;',
				items: [this.FileUploadPanel]
			}],
			reader: new Ext.data.JsonReader({
				success: Ext.emptyFn
			}, [
				{name: 'DrugMarkup_id'},
				{name: 'DrugMarkup_begDT'},
				{name: 'DrugMarkup_endDT'},
				{name: 'DrugMarkup_MinPrice'},
				{name: 'DrugMarkup_MaxPrice'},
				{name: 'DrugMarkup_Wholesale'},
				{name: 'DrugMarkup_Retail'},
				{name: 'DrugMarkup_IsNarkoDrug'},
				{name: 'Drugmarkup_Delivery'}
			]),
			url: '/?c=DrugMarkup&m=save'
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
		sw.Promed.swDrugMarkupEditWindow.superclass.initComponent.apply(this, arguments);
		this.form = this.findById('DrugMarkupEditForm').getForm();
	}
});