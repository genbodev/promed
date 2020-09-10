/**
* swWhsDocumentOrderAllocationDrugMoEditWindow - окно редактирования позиции разнарядки МО
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2014 Swan Ltd.
* @author       Salakhov R.
* @version      06.2014
* @comment      
*/
sw.Promed.swWhsDocumentOrderAllocationDrugMoEditWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: false,
	title: lang['raznaryadka_mo_dobavlenie'],
	layout: 'border',
	id: 'WhsDocumentOrderAllocationDrugMoEditWindow',
	modal: true,
	shim: false,
	width: 700,
	height: 375,
	resizable: false,
	maximizable: false,
	maximized: false,
	doSave:  function() {
		var wnd = this;
		if ( !this.form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					wnd.findById('WhsDocumentOrderAllocationDrugMoEditForm').getFirstInvalidEl().focus(true);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
		var data = this.form.getValues();
		var store = this.form.findField('Org_id').getStore();
		var idx = store.findBy(function(rec) { return rec.get('Org_id') == data.Org_id; });

		data.Lpu_Name = null;
		if (idx > -1) {
			var rec = store.getAt(idx);
			data.Lpu_Name = rec.get('Lpu_Name');
		}

        this.onSave(data);
		this.hide();
		return true;		
	},	
	show: function() {
        var wnd = this;
		sw.Promed.swWhsDocumentOrderAllocationDrugMoEditWindow.superclass.show.apply(this, arguments);		
		this.onSave = Ext.emptyFn;
		this.FormParams = new Object();

        if ( !arguments[0] ) {
            sw.swMsg.alert(lang['oshibka'], lang['ne_ukazanyi_vhodnyie_dannyie'], function() { wnd.hide(); });
            return false;
        }
		if ( arguments[0].onSave && typeof arguments[0].onSave == 'function' ) {
			this.onSave = arguments[0].onSave;
		}
		if ( arguments[0].FormParams ) {
			this.FormParams = arguments[0].FormParams;
		}

		this.form.reset();
		this.form.setValues(this.FormParams);
	},
	initComponent: function() {
		var wnd = this;		
		
		var form = new Ext.Panel({
			autoScroll: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			height: 95,
			border: false,			
			frame: true,
			region: 'center',
			labelAlign: 'right',
			items: [{
				xtype: 'form',
				autoHeight: true,
				id: 'WhsDocumentOrderAllocationDrugMoEditForm',
				style: 'margin-bottom: 0.5em;',
				bodyStyle:'background:#DFE8F6;padding:5px;',
				border: true,
				labelWidth: 200,
				collapsible: true,
				items: [{
					xtype: 'textfield',
					fieldLabel: 'Торговое наименование',
					name: 'Tradenames_Name',
					anchor: '100%',
					disabled: true
				}, {
					xtype: 'textfield',
					fieldLabel: 'Форма выпуска',
					name: 'DrugForm_Name',
					anchor: '100%',
					disabled: true
				}, {
					xtype: 'textfield',
					fieldLabel: 'Дозировка',
					name: 'Drug_Dose',
					anchor: '100%',
					disabled: true
				}, {
					xtype: 'textfield',
					fieldLabel: 'Фасовка',
					name: 'Drug_Fas',
					anchor: '100%',
					disabled: true
				}, {
					xtype: 'textfield',
					fieldLabel: 'РУ',
					name: 'Reg_Num',
					anchor: '100%',
					disabled: true
				}, {
					xtype: 'textfield',
					fieldLabel: 'Держатель/Владелец РУ',
					name: 'Reg_Firm',
					anchor: '100%',
					disabled: true
				}, {
					xtype: 'textfield',
					fieldLabel: 'Страна держателя/владельца РУ',
					name: 'Reg_Country',
					anchor: '100%',
					disabled: true
				}, {
					xtype: 'textfield',
					fieldLabel: 'Период действия РУ',
					name: 'Reg_Period',
					anchor: '100%',
					disabled: true
				}, {
					xtype: 'textfield',
					fieldLabel: 'Дата переоформления РУ',
					name: 'Reg_ReRegDate',
					anchor: '100%',
					disabled: true
				}, {
					fieldLabel: 'МО',
					hiddenName: 'Org_id',
					xtype: 'swbaselocalcombo',
					valueField: 'Org_id',
					displayField: 'Lpu_Name',
					allowBlank: false,
					editable: true,
					lastQuery: '',
					validateOnBlur: true,
					anchor: '100%',
					store: new Ext.data.Store({
						autoLoad: true,
						reader: new Ext.data.JsonReader({
							id: 'Org_id'
						}, [
							{name: 'Org_id', mapping: 'Org_id'},
							{name: 'Lpu_Name', mapping: 'Lpu_Name'}
						]),
						url: '/?c=WhsDocumentOrderAllocationDrug&m=loadOrgLpuCombo'
					}),
					tpl: new Ext.XTemplate(
						'<tpl for="."><div class="x-combo-list-item">',
						'<table style="border: 0;"><tr><td>{Lpu_Name}</td></tr></table>',
						'</div></tpl>'
					)
				}, {
					xtype: 'textfield',
					fieldLabel: lang['kolichestvo'],
					name: 'WhsDocumentOrderAllocationDrug_Kolvo',
					allowBlank: false
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
			items:[form]
		});
		sw.Promed.swWhsDocumentOrderAllocationDrugMoEditWindow.superclass.initComponent.apply(this, arguments);
		this.form = this.findById('WhsDocumentOrderAllocationDrugMoEditForm').getForm();
	}	
});