/**
* swReportSelectWindow - окно выбора списка отчетов для печати
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2014 Swan Ltd.
* @author       Salakhov R.
* @version      10.2014
* @comment      
*/
sw.Promed.swReportSelectWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: false,
	title: lang['vyibor_otcheta'],
	layout: 'border',
	id: 'ReportSelectWindow',
	modal: true,
	shim: false,
	width: 400,
	height: 70,
	resizable: false,
	maximizable: false,
	maximized: false,
	listeners: {
		hide: function() {
			this.onHide();
		}
	},
	onHide: Ext.emptyFn,
	onSelectDefault: function(report_array) {
		for(var i = 0; i < report_array.length; i++) {
			if (report_array[i].report_file) {
				printBirt({
					'Report_FileName': report_array[i].report_file,
					'Report_Params': report_array[i].report_params,
					'Report_Format': report_array[i].report_format
				});
			}
		}
	},
	doSelect:  function() {
		var wnd = this;
		var report_array = this.ReportPanel.getData();
		if (report_array && report_array.length > 0) {
			this.onSelect(report_array);
			this.hide();
		}
		return true;
	},	
	show: function() {
		sw.Promed.swReportSelectWindow.superclass.show.apply(this, arguments);

		this.onSelect = this.onSelectDefault;
		this.ReportData = new Object();

		if (arguments[0].onSelect && typeof arguments[0].onSelect == 'function') {
			this.onSelect = arguments[0].onSelect;
		}
		if (arguments[0].ReportData) {
			this.ReportData = arguments[0].ReportData;
			this.ReportPanel.setData(this.ReportData);
		}
		this.doLayout();
	},
	initComponent: function() {
		var wnd = this;

		this.ReportPanel = new Ext.form.FormPanel({
			region: 'center',
			layout: 'form',
			label: '',
			frame: true,
			autoHeight: true,
			counter: 0,
			addField: function(data) {
				var panel = this;
				var first = (panel.counter == 0);
				var c = new Ext.form.Checkbox({
					boxLabel: data.report_label ? ' '+data.report_label : lang['otchet_№']+(panel.counter+1),
					itemData: data
				});
				c.name = 'Field'+panel.counter;
				c.value = data.report_file ? data.report_file : null;
				c.paramWhsDocumentUcInventDrugInventory = data.paramWhsDocumentUcInventDrugInventory ? data.paramWhsDocumentUcInventDrugInventory : null;
				c.paramIsKolvoUchet = data.paramIsKolvoUchet ? data.paramIsKolvoUchet : null;
				c.fieldLabel = '';
				c.labelSeparator = '';
				if(data.fun && typeof data.fun == 'function'){
					c.addListener('check', function(rb,checked){
						data.fun(rb,checked,panel);
					});
				}

				var p = new Ext.Panel({
					height: 28,
					width: 350,
					style: 'padding-top: 2px;',
					layout: 'column',
					number: panel.counter,
					hidden: (data.elHidden) ? data.elHidden : false,
					items: [{
						layout: 'form',
						labelWidth: 5,
						style: (data.elHidden) ? 'padding-left: 25px;' : '',
						items: [c]
					}]
				});
				panel.add(p);
				panel.doLayout();
				panel.syncSize();
				panel.counter++;
			},
			setData: function(arr) {
				var panel = this;
				var height = 80;
				panel.reset(false);
				for(var i = 0; i < arr.length; i++) {
					panel.addField(arr[i]);
				}
				wnd.setHeight(height+(panel.counter*28));
			},
			getField: function(item) {
				if (item && item.number != null) {
					var c = item.find('name','Field'+item.number)
					return c[0];
				}
			},
			getData: function() {
				var panel = this;
				var res_arr = new Array();
				panel.items.each(function(item,index,length) {
					var c = panel.getField(item);
					if(c.checked && c.paramWhsDocumentUcInventDrugInventory){
						c.itemData.report_params = c.itemData.report_params+'&paramWhsDocumentUcInventDrugInventory='+c.paramWhsDocumentUcInventDrugInventory;
						var paramIsKolvoUchet = (c.paramIsKolvoUchet) ? c.paramIsKolvoUchet : 0;
						c.itemData.report_params = c.itemData.report_params+'&paramIsKolvoUchet='+paramIsKolvoUchet;
					}					
					if (c.checked) {
						var o = new Object();
						Ext.apply(o, c.itemData);
						res_arr.push(o);
					}
				});
				return res_arr;
			},
			reset: function() {
				var panel = this;
				panel.items.each(function(item,index,length) {
					panel.remove(item,true);
				});
				panel.counter = 0;
			},
			clearMarks: function(el){
				var panel = this;
				var el = el || false;
				panel.items.each(function(item,index,length) {
					var c = panel.getField(item);
					if(el && el.name != c.name) c.setValue();
				});
			},
			clearCheck: function(arr){
			}
		});

		Ext.apply(this, {
			layout: 'border',
			buttons:
			[{
				handler: function() 
				{
					this.ownerCt.doSelect();
				},
				iconCls: 'ok16',
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
			items:[wnd.ReportPanel]
		});
		sw.Promed.swReportSelectWindow.superclass.initComponent.apply(this, arguments);
	}
});