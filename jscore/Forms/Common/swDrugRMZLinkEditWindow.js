/**
* swDrugRMZLinkEditWindow - окно редактирования связи справочника РЗН с номенклатурным справочником
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2015 Swan Ltd.
* @author       Salakhov R.
* @version      03.2015
* @comment      
*/
sw.Promed.swDrugRMZLinkEditWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: false,
	title: lang['spravochnik_medikamentov_␓_spravochnik_rzn'],
	layout: 'border',
	id: 'DrugRMZLinkEditWindow',
	modal: true,
	shim: false,
	width: 500,
	resizable: false,
	maximizable: true,
	maximized: true,
	doSave:  function() {
		var wnd = this;
		if ( !this.form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					wnd.findById('DrugRMZLinkEditForm').getFirstInvalidEl().focus(true);
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
		var params = new Object();

		params.Drug_id = wnd.Drug_id;

		wnd.getLoadMask(lang['podojdite_idet_sohranenie']).show();
		this.form.submit({
			params: params,
			failure: function(result_form, action) {
				wnd.getLoadMask().hide();
				if (action.result) {
					if (action.result.Error_Code) {
						Ext.Msg.alert(lang['oshibka_#']+action.result.Error_Code, action.result.Error_Message);
					}
				}
			},
			success: function(result_form, action) {
				wnd.getLoadMask().hide();
				wnd.loadNext();
			}
		});
	},
	loadNext: function() {
		var wnd = this;

		var Mask = new Ext.LoadMask(Ext.get(this.id), { msg: "Пожалуйста, подождите, идет загрузка данных формы..." });
		//Mask.show();

		wnd.form.reset();
		Ext.Ajax.request({
			params: {
				Drug_id: wnd.Drug_id
			},
			callback: function(opt, success, resp) {
				//Mask.hide();
				var response_obj = Ext.util.JSON.decode(resp.responseText);

				if (response_obj && response_obj[0] && response_obj[0].Drug_id) {
					wnd.form.setValues(response_obj[0]);
					wnd.Drug_id = response_obj[0].Drug_id;

					wnd.form.findField('DrugRPN_id').enable();
					wnd.buttons[0].enable();
					wnd.InformationPanel.setData('no_linked_cnt', response_obj[0].no_linked_cnt);
				} else {
					wnd.form.findField('DrugRPN_id').disable();
					wnd.buttons[0].disable();
					wnd.InformationPanel.setData('no_linked_cnt', '0');
				}

				wnd.InformationPanel.showData();
			},
			url: '/?c=DrugNomen&m=getDrugRMZLinkData'
		});
	},
	show: function() {
        var wnd = this;
		sw.Promed.swDrugRMZLinkEditWindow.superclass.show.apply(this, arguments);		

		this.Drug_id = null;
		this.loadNext();
	},
	initComponent: function() {
		var wnd = this;	
		var field_width = 350;

		this.InformationPanel = new Ext.Panel({
			bodyStyle: 'padding: 3px 3px 3px 10px',
			border: false,
			region: 'south',
			autoHeight: true,
			frame: true,
			labelAlign: 'right',
			title: null,
			collapsible: true,
			data: null,
			html_tpl: null,
			win: wnd,
			setTpl: function(tpl) {
				this.html_tpl = tpl;
			},
			setData: function(name, value) {
				if (!this.data)
					this.data = new Ext.util.MixedCollection();
				if (name && value) {
					var idx = this.data.findIndex('name', name);
					if (idx >= 0) {
						this.data.itemAt(idx).value = value;
					} else {
						this.data.add({
							name: name,
							value: value
						});
					}
				}
			},
			showData: function() {
				var html = this.html_tpl;
				if (this.data)
					this.data.each(function(item) {
						html = html.replace('{'+item.name+'}', item.value, 'gi');
					});
				html = html.replace(/{[a-zA-Z_0-9]+}/g, '');
				this.body.update(html);
				if (this.win) {
					this.win.syncSize();
					this.win.doLayout();
				}
			},
			clearData: function() {
				this.data = null;
			}
		});
		this.InformationPanel.setTpl("Кол-во записей номенклатурного справочника, не связанных с позициями справочника РЗН – {no_linked_cnt}");
		
		var form = new Ext.Panel({
			autoScroll: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			height: 70,
			border: false,			
			frame: true,
			region: 'center',
			labelAlign: 'right',
			items: [{
				xtype: 'form',
				autoHeight: true,
				id: 'DrugRMZLinkEditForm',
				style: 'margin-bottom: 0.5em;',
				bodyStyle:'background:#DFE8F6;padding:5px;',
				border: true,
				labelWidth: 200,
				collapsible: true,
				url:'/?c=DrugNomen&m=saveDrugRMZLink',
				items: [{
					layout: 'column',
					items:[{
						xtype: 'fieldset',
						style: 'padding: 3px 7px 3px 7px; margin-right: 10px;',
						autoHeight: true,
						title: lang['spravochnik_medikamentov'],
						labelWidth: 180,
						items: [{
							width: field_width,
							fieldLabel: lang['kod'],
							name: 'DrugNomen_Code',
							disabled: true,
							xtype: 'textfield'
						}, {
							width: field_width,
							fieldLabel: lang['№_ru'],
							name: 'Reg_Data',
							disabled: true,
							xtype: 'textfield'
						}, {
							name: 'Reg_Num',
							xtype: 'hidden'
						}, {
							width: field_width,
							fieldLabel: lang['kod_ean'],
							name: 'Drug_Ean',
							disabled: true,
							xtype: 'textfield'
						}, {
							width: field_width,
							fieldLabel: lang['torg_naimenovanie'],
							name: 'Tradenames_RusName',
							disabled: true,
							xtype: 'textfield'
						}, {
							width: field_width,
							fieldLabel: lang['forma_vyipuska'],
							name: 'Clsdrugforms_RusName',
							disabled: true,
							xtype: 'textfield'
						}, {
							width: field_width,
							fieldLabel: lang['dozirovka'],
							name: 'Unit_Value',
							disabled: true,
							xtype: 'textfield'
						}, {
							width: field_width,
							fieldLabel: lang['upakovka'],
							name: 'DrugPack_Name',
							disabled: true,
							xtype: 'textfield'
						}, {
							width: field_width,
							fieldLabel: lang['kol-vo_lek_form_v_upakovke'],
							name: 'Drug_Fas',
							disabled: true,
							xtype: 'textfield'
						}, {
							width: field_width,
							fieldLabel: lang['firma-proizvoditel'],
							name: 'Firm_Name',
							disabled: true,
							xtype: 'textfield'
						}, {
							width: field_width,
							fieldLabel: lang['upakovschik'],
							name: 'DrugPack_FirmName',
							disabled: true,
							xtype: 'textfield'
						}]
					}, {
						xtype: 'fieldset',
						style: 'padding: 3px 7px 3px 7px;',
						autoHeight: true,
						title: lang['spravochnik_lp_rzn'],
						labelWidth: 180,
						items: [{
							name: 'DrugRMZ_id',
							xtype: 'hidden'
						},
						new Ext.form.TwinTriggerField ({
							displayField:'DrugRPN_id',
							name: 'DrugRPN_id',
							valueField: 'DrugRPN_id',
							readOnly: true,
							width: field_width,
							trigger1Class: 'x-form-search-trigger',
							trigger2Class: 'x-form-clear-trigger',
							fieldLabel: lang['kod_rzn'],
							allowBlank: false,
							onTrigger1Click: function() {
								var searchWindow = 'swDrugRMZSearchWindow';

								if (this.disabled) {
									return false;
								}

								getWnd(searchWindow).show({
									Reg_Num: wnd.form.findField('Reg_Num').getValue(),
									Drug_Ean: wnd.form.findField('Drug_Ean').getValue(),
									onSelect: function(data) {
										wnd.form.findField('DrugRMZ_id').setValue(data.DrugRMZ_id);
										wnd.form.findField('DrugRPN_id').setValue(data.DrugRPN_id);
										wnd.form.findField('DrugRMZ_RegNum').setValue(data.DrugRMZ_RegNum);
										wnd.form.findField('DrugRMZ_EAN13Code').setValue(data.DrugRMZ_EAN13Code);
										wnd.form.findField('DrugRMZ_Name').setValue(data.DrugRMZ_Name);
										wnd.form.findField('DrugRMZ_Form').setValue(data.DrugRMZ_Form);
										wnd.form.findField('DrugRMZ_Dose').setValue(data.DrugRMZ_Dose);
										wnd.form.findField('DrugRMZ_Pack').setValue(data.DrugRMZ_Pack);
										wnd.form.findField('DrugRMZ_PackSize').setValue(data.DrugRMZ_PackSize);
										wnd.form.findField('DrugRMZ_Firm').setValue(data.DrugRMZ_Firm);
										wnd.form.findField('DrugRMZ_FirmPack').setValue(data.DrugRMZ_FirmPack);
										getWnd(searchWindow).hide();
									}
								});
							},
							onTrigger2Click: function() {
								if (this.disabled) {
									return false;
								}

								wnd.form.findField('DrugRMZ_id').setValue(null);
								wnd.form.findField('DrugRPN_id').setValue(null);
								wnd.form.findField('DrugRMZ_RegNum').setValue(null);
								wnd.form.findField('DrugRMZ_EAN13Code').setValue(null);
								wnd.form.findField('DrugRMZ_Name').setValue(null);
								wnd.form.findField('DrugRMZ_Form').setValue(null);
								wnd.form.findField('DrugRMZ_Dose').setValue(null);
								wnd.form.findField('DrugRMZ_Pack').setValue(null);
								wnd.form.findField('DrugRMZ_PackSize').setValue(null);
								wnd.form.findField('DrugRMZ_Firm').setValue(null);
								wnd.form.findField('DrugRMZ_FirmPack').setValue(null);
							}
						}), {
							width: field_width,
							fieldLabel: lang['№_ru'],
							name: 'DrugRMZ_RegNum',
							disabled: true,
							xtype: 'textfield'
						}, {
							width: field_width,
							fieldLabel: lang['kod_ean'],
							name: 'DrugRMZ_EAN13Code',
							disabled: true,
							xtype: 'textfield'
						}, {
							width: field_width,
							fieldLabel: lang['torg_naimenovanie'],
							name: 'DrugRMZ_Name',
							disabled: true,
							xtype: 'textfield'
						}, {
							width: field_width,
							fieldLabel: lang['forma_vyipuska'],
							name: 'DrugRMZ_Form',
							disabled: true,
							xtype: 'textfield'
						}, {
							width: field_width,
							fieldLabel: lang['dozirovka'],
							name: 'DrugRMZ_Dose',
							disabled: true,
							xtype: 'textfield'
						}, {
							width: field_width,
							fieldLabel: lang['upakovka'],
							name: 'DrugRMZ_Pack',
							disabled: true,
							xtype: 'textfield'
						}, {
							width: field_width,
							fieldLabel: lang['kol-vo_lek_form_v_upakovke'],
							name: 'DrugRMZ_PackSize',
							disabled: true,
							xtype: 'textfield'
						}, {
							width: field_width,
							fieldLabel: lang['firma-proizvoditel'],
							name: 'DrugRMZ_Firm',
							disabled: true,
							xtype: 'textfield'
						}, {
							width: field_width,
							fieldLabel: lang['upakovschik'],
							name: 'DrugRMZ_FirmPack',
							disabled: true,
							xtype: 'textfield'
						}]
					}]
				}]
			}]
		});
		Ext.apply(this, {
			layout: 'border',
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
				handler: function()
				{
					this.ownerCt.loadNext();
				},
				iconCls: 'actions16',
				text: lang['sleduyuschee']
			},
			{
				text: '-'
			},
			HelpButton(this, 0),
			{
				handler: function() 
				{
					this.ownerCt.hide();
				},
				iconCls: 'cancel16',
				text: BTN_FRMCANCEL
			}],
			items:[this.InformationPanel,form]
		});
		sw.Promed.swDrugRMZLinkEditWindow.superclass.initComponent.apply(this, arguments);
		this.form = this.findById('DrugRMZLinkEditForm').getForm();
	}	
});