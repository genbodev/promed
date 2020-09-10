/**
* swDrugNormativeListEditWindow - окно редактирования нормативного перечня
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Farmacy
* @access       public
* @copyright    Copyright (c) 2012 Swan Ltd.
* @author       Salakhov R.
* @version      17.09.2012
* @comment      
*/
sw.Promed.swDrugNormativeListEditWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: false,
	title: lang['normativnyiy_perechen_redaktirovanie'],
	layout: 'border',
	id: 'DrugNormativeListEditWindow',
	modal: true,
	shim: false,
	width: 600,
	height: 180,
	resizable: false,
	maximizable: false,
	maximized: false,	
	listeners: {
		hide: function() {
			this.onHide();
		}
	},
	onHide: Ext.emptyFn,
	setDisabled: function(disable) {
		var wnd = this;
		var form = wnd.form;
		var only_date_edit = false;

		if (wnd.action == 'edit' && wnd.owner) {
			var record = wnd.owner.getGrid().getSelectionModel().getSelected();
			if (record && record.get('DrugNormativeListSpec_count') > 0) {
				only_date_edit = true;
			}
		}
		
		if (disable || only_date_edit) {
			if (only_date_edit) {
				wnd.dateMenu.enable();
				wnd.buttons[0].enable();
			} else {
				wnd.dateMenu.disable();
				wnd.buttons[0].disable();
			}
			form.findField('WhsDocumentCostItemType_id').disable();
			form.findField('PersonRegisterType_id').disable();
			form.findField('DrugNormativeList_Name').disable();
		} else {
			wnd.dateMenu.enable();
            wnd.buttons[0].enable();
            wnd.buttons[0].show();
			if (wnd.edit_after_copy) {
				form.findField('WhsDocumentCostItemType_id').disable();
				form.findField('PersonRegisterType_id').disable();
			} else {
				form.findField('WhsDocumentCostItemType_id').enable();
				form.findField('PersonRegisterType_id').enable();
			}
			form.findField('DrugNormativeList_Name').enable();
		}
	},
	doSave:  function() {
		var wnd = this;
		if (!this.form.isValid()) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					wnd.findById('slewDrugNormativeListEditForm').getFirstInvalidEl().focus(true);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
        wnd.submit();
		return true;		
	},
	submit: function() {
		var wnd = this;
		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение..."});
		loadMask.show();
		var params = new Object();
		params.action = wnd.action;
		params.DrugNormativeList_id = wnd.DrugNormativeList_id;
		params.PersonRegisterType_id = wnd.form.findField('PersonRegisterType_id').getValue();
		params.WhsDocumentCostItemType_id = wnd.form.findField('WhsDocumentCostItemType_id').getValue();
		params.DrugNormativeList_Name = wnd.form.findField('DrugNormativeList_Name').getValue();
		params.DrugNormativeList_BegDT = this.dateMenu.getValue1() ? this.dateMenu.getValue1().format('d.m.Y') : '';
		params.DrugNormativeList_EndDT = this.dateMenu.getValue2() ? this.dateMenu.getValue2().format('d.m.Y') : '';
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
				var id = 0;
				if (action.result && action.result.DrugNormativeList_id > 0) {
					id = action.result.DrugNormativeList_id;
				}
				loadMask.hide();
				wnd.callback(wnd.owner, id);
				wnd.hide();
			}
		});
	},
	show: function() {
        var wnd = this;
		sw.Promed.swDrugNormativeListEditWindow.superclass.show.apply(this, arguments);
		this.action = '';
		this.callback = Ext.emptyFn;
        if ( !arguments[0] ) {
            sw.swMsg.alert(lang['oshibka'], lang['ne_ukazanyi_vhodnyie_dannyie'], function() { wnd.hide(); });
            return false;
        }
		this.action = (arguments[0].action) ? arguments[0].action : 'add';
		if ( arguments[0].callback && typeof arguments[0].callback == 'function' ) {
			this.callback = arguments[0].callback;
		}
		if ( arguments[0].owner ) {
			this.owner = arguments[0].owner;
		}
		if ( arguments[0].DrugNormativeList_id ) {
			this.DrugNormativeList_id = arguments[0].DrugNormativeList_id;
		}

		this.edit_after_copy = arguments[0].edit_after_copy ? true : false;
		
		this.form.reset();		
		
        var loadMask = new Ext.LoadMask(this.form.getEl(), {msg:lang['zagruzka']});
        loadMask.show();
		wnd.setTitle(lang['normativnyiy_perechen']);
		switch (this.action) {
			case 'add':
				wnd.setTitle(wnd.title+lang['_dobavlenie']);
                wnd.setDisabled(false);
				loadMask.hide();
			break;
			case 'edit':				
			case 'view':
				wnd.setTitle(wnd.title+(wnd.action == 'edit' ? lang['_redaktirovanie'] : lang['_prosmotr']));
				Ext.Ajax.request({
					failure:function () {
						sw.swMsg.alert(lang['oshibka'], lang['ne_udalos_poluchit_dannyie_s_servera']);
						loadMask.hide();
						wnd.hide();
					},
					params:{
						DrugNormativeList_id: wnd.DrugNormativeList_id
					},
					success: function (response) {
						var result = Ext.util.JSON.decode(response.responseText);
						if (result[0]) {
							wnd.form.setValues(result[0]);
							if (result[0].DrugNormativeList_BegDT && result[0].DrugNormativeList_EndDT) {
								wnd.dateMenu.setValue(result[0].DrugNormativeList_BegDT + ' - ' + result[0].DrugNormativeList_EndDT);
							}
						}
						wnd.setDisabled(wnd.action == 'view');
						loadMask.hide();
					},
					url:'/?c=DrugNormativeList&m=load'
				});		
			break;	
		}
	},
	initComponent: function() {
		var wnd = this;		
		
		this.dateMenu = new Ext.form.DateRangeField({
			width: 175,
			fieldLabel: lang['period_deystviya'],
			hiddenName: 'DrugNormativeList_DateRange',
			allowBlank: false,
			plugins: [
				new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)
			]
		});
		
		var form = new Ext.Panel({
			autoScroll: false,
			bodyBorder: false,
			bodyStyle: 'padding: 0',
			border: false,			
			frame: true,
			region: 'center',
			labelAlign: 'right',
			items: [{
				xtype: 'form',
				autoHeight: true,
				id: 'slewDrugNormativeListEditForm',
				bodyStyle:'background:#DFE8F6;padding:5px;',
				border: false,
				labelWidth: 130,
				collapsible: true,
				url:'/?c=DrugNormativeList&m=save',
				items: [{
					name: 'DrugNormativeList_id',
					xtype: 'hidden'
				}, {
					fieldLabel: lang['naimenovanie'],
					name: 'DrugNormativeList_Name',
					xtype: 'textfield',
					width: 400,
					allowBlank:false
				},{
					xtype: 'swwhsdocumentcostitemtypecombo',
					fieldLabel: lang['programma_llo'],
					name: 'WhsDocumentCostItemType_id',
					width: 300,
					allowBlank:false,
					listeners:{
						'select':function() {
							Ext.Ajax.request({
								failure:function () {
									console.log(lang['oshibka'] + lang['ne_udalos_poluchit_dannyie_s_servera']);
								},
								params:{
									WhsDocumentCostItemType_id: this.value
								},
								success: function (response) {
									var result = Ext.util.JSON.decode(response.responseText);
									if (result[0]) {
										wnd.form.findField('PersonRegisterType_id').setValue(result[0]['PersonRegisterType_id'])
									}
								},
								url:'/?c=DrugNormativeList&m=getPersonRegisterTypeByWhsDocumentCostItemType'
							});

						}
					}
				}, {
					fieldLabel: lang['tip'],
					comboSubject: 'PersonRegisterType',
					id: 'dnlePersonRegisterType_id',
					name: 'PersonRegisterType_id',
					xtype: 'swcustomobjectcombo',
					width: 175,
					allowBlank:false
					},
				wnd.dateMenu]
			}]
		});
		Ext.apply(this, {
			layout: 'border',
			bodyStyle: 'padding: 7px;',
			buttons:
			[{
				handler: function() 
				{
					this.ownerCt.doSave();
				},
				iconCls: 'save16',
				text: BTN_FRMSAVE
			}, 
			{
				text: '-'
			},
			HelpButton(this, 0),//todo проставить табиндексы
			{
				handler: function() 
				{
					this.ownerCt.hide();
				},
				iconCls: 'cancel16',
				text: BTN_FRMCANCEL
			}],
			items:[form]
		});
		sw.Promed.swDrugNormativeListEditWindow.superclass.initComponent.apply(this, arguments);
		this.form = this.findById('slewDrugNormativeListEditForm').getForm();
	}	
});