/**
* swMzDrugRequestCopyOptionsWindow - окно настройки параметров копирования заявки
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Farmacy
* @access       public
* @copyright    Copyright (c) 2014 Swan Ltd.
* @author       Salakhov R.
* @version      02.2014
* @comment      
*/
sw.Promed.swMzDrugRequestCopyOptionsWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: false,
	title: lang['vyibor_zayavki'],
	layout: 'border',
	id: 'MzDrugRequestCopyOptionsWindow',
	modal: true,
	shim: false,
	width: 800,
	height: 125,
	resizable: false,
	maximizable: false,
	maximized: false,
	listeners: {
		hide: function() {
			this.onHide();
		}
	},
	onHide: Ext.emptyFn,
	doSelect: function() {
		var params = new Object();
		params.DrugRequest_id = this.form.findField('DrugRequest_id').getValue();
		this.onSelect(params)
		this.hide();
	},
	show: function() {
		var wnd = this;

		sw.Promed.swMzDrugRequestCopyOptionsWindow.superclass.show.apply(this, arguments);

		this.onSelect = Ext.emptyFn;
		this.DrugRequest_id = null;
		if (!arguments[0]) {
			sw.swMsg.alert(lang['soobschenie'], lang['nevernyie_parametryi'], function() {this.hide();}.createDelegate(this));
			return false;
		}

		if (arguments[0].onSelect && typeof(arguments[0].onSelect) == 'function') {
			this.onSelect = arguments[0].onSelect;
		}

		if (arguments[0].DrugRequest_id) {
			this.DrugRequest_id = arguments[0].DrugRequest_id;
		}

		this.form.reset();
        var loadMask = new Ext.LoadMask(this.form.getEl(), {msg:lang['zagruzka']});
        loadMask.show();
		this.form.findField('DrugRequest_id').getStore().load({
			params: {
				DrugRequest_id: wnd.DrugRequest_id
			},
			callback: function() {
				loadMask.hide();
			}
		});
	},
	initComponent: function() {
		var form = new Ext.Panel({
			autoScroll: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			height: 110,
			border: false,			
			frame: true,
			region: 'center',
			labelAlign: 'right',
			items: [{
				xtype: 'form',
				autoHeight: true,
				id: 'MzDrugRequestCopyOptionsForm',
				style: 'margin-bottom: 0.5em;',
				bodyStyle:'background:#DFE8F6;padding:5px;',
				border: true,
				labelWidth: 50,
				collapsible: true,
				items: [{
					fieldLabel: lang['zayavka'],
					hiddenName: 'DrugRequest_id',
					xtype: 'swbaselocalcombo',
					valueField: 'DrugRequest_id',
					displayField: 'DrugRequest_Name',
					allowBlank: false,
					editable: false,
					lastQuery: '',
					validateOnBlur: true,
					anchor: '100%',
					store: new Ext.data.Store({
						autoLoad: false,
						reader: new Ext.data.JsonReader({
							id: 'DrugRequest_id'
						}, [
							{name: 'DrugRequest_id', mapping: 'DrugRequest_id'},
							{name: 'DrugRequest_Name', mapping: 'DrugRequest_Name'},
							{name: 'DrugRequest_Sum', mapping: 'DrugRequest_Sum'},
							{name: 'DrugRequestStatus_Name', mapping: 'DrugRequestStatus_Name'}
						]),
						url: '/?c=MzDrugRequest&m=loadSourceDrugRequestCombo'
					}),
					tpl: new Ext.XTemplate(
						'<tpl for="."><div class="x-combo-list-item">',
						'<table style="border: 0;"><tr><td>{DrugRequest_Name}</td><td>&nbsp;({DrugRequestStatus_Name})</td></tr></table>',
						'</div></tpl>'
					)
				}]
			}]
		});
		Ext.apply(this, {
			layout: 'border',
			buttons:
			[{
				handler: function() 
				{
					this.ownerCt.doSelect();
				},
				iconCls: 'save16',
				text: lang['vyibrat']
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
		sw.Promed.swMzDrugRequestCopyOptionsWindow.superclass.initComponent.apply(this, arguments);
		this.base_form = this.findById('MzDrugRequestCopyOptionsForm');
		this.form = this.base_form.getForm();
	}
});