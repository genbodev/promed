/**
* swDrugRequestPeriodViewWindow - окно просмотра списка рабочих периодов
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Dlo
* @access       public
* @copyright    Copyright (c) 2012 Swan Ltd.
* @author       Salakhov Rustam
* @version      07.2012
* @comment      
*/
sw.Promed.swDrugRequestPeriodViewWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: false,
	title: lang['spravochnik_rabochie_periodyi'],
	layout: 'border',
	id: 'drpvDrugRequestPeriodViewWindow',
	modal: true,
	onHide: Ext.emptyFn,
	onSelect:  Ext.emptyFn,
	shim: false,
	width: 400,
	resizable: false,
	maximizable: true,
	maximized: true,
	listeners:{
		hide:function () {
			this.onHide();
		}
	},	
	show: function() {		
		sw.Promed.swDrugRequestPeriodViewWindow.superclass.show.apply(this, arguments);
		var wnd = this;
		wnd.onHide = Ext.emptyFn;
		if (arguments[0] && arguments[0].onHide) {
			wnd.onHide = arguments[0].onHide;
		}
		if (arguments[0] && arguments[0].onSelect) {
			wnd.onSelect = arguments[0].onSelect;
			wnd.buttons[0].show();
			wnd.mode = 'select';			
		} else {
			wnd.onSelect = Ext.emptyFn;
			wnd.buttons[0].hide();
			wnd.mode = 'view';
		}
		this.SearchGrid.loadData();
		this.SearchGrid.getGrid().getStore().sort('DrugRequestPeriod_Sort', 'DESC');

		if (arguments[0] && arguments[0].onlyView){
			wnd.setOnlyView(arguments[0].onlyView);
		} else {
			wnd.setOnlyView(false);
		}
	},
	setOnlyView: function(onlyView) {
		if (onlyView) {
			this.SearchGrid.getAction('action_add').hide();
			this.SearchGrid.getAction('action_edit').hide();
			this.SearchGrid.getAction('action_delete').hide();
		} else {
			this.SearchGrid.getAction('action_add').show();
			this.SearchGrid.getAction('action_edit').show();
			this.SearchGrid.getAction('action_delete').show();
		}
		this.SearchGrid.setReadOnly(onlyView);
	},
	initComponent: function() {
		var wnd = this;
		this.SearchGrid = new sw.Promed.ViewFrame({
			actions: [
				{name: 'action_add'},
				{name: 'action_edit'},
				{name: 'action_view', hidden: true},
				{name: 'action_delete', url: '/?c=DrugRequest&m=deleteDrugRequestPeriod'},
				{name: 'action_print'}
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 150,
			autoLoadData: false,
			border: true,
			dataUrl: '/?c=DrugRequest&m=loadDrugRequestPeriodList',
			height: 180,
			region: 'center',
			object: 'DrugRequestPeriod',
			editformclassname: 'swDrugRequestPeriodEditWindow',
			id: 'drpvDrugRequestPeriodGrid',
			paging: false,
			style: 'margin-bottom: 10px',
			stringfields: [
				{name: 'DrugRequestPeriod_id', type: 'int', header: 'ID', key: true},
				{name: 'DrugRequestPeriod_TimeRange', hidden: true},
				{name: 'DrugRequestPeriod_Sort', header: lang['period'], width: 175, renderer: function(v, p, record) {return record.get('DrugRequestPeriod_TimeRange');}},
				{name: 'DrugRequestPeriod_Name', type: 'string', header: lang['naimenovanie'], width: 250}
			],
			title: null,
			toolbar: true,
			onDblClick: function() {
				if (wnd.mode == 'select') {
					wnd.onSelect();
				} else if (wnd.mode == 'view') {
					this.ViewActions.action_edit.execute();
				}
			}
		});
		
		Ext.apply(this, {
			layout: 'border',
			buttons:
			[{
				handler: function() {
					this.ownerCt.onSelect();					
				},
		        iconCls: 'ok16',
				text: lang['vyibrat']
			}, {
				text: '-'
			},
			HelpButton(this, 0),//todo проставить табиндексы
			{
				handler: function()  {
					this.ownerCt.hide();
				},
				iconCls: 'cancel16',
				text: BTN_FRMCANCEL
			}],
			tbar: this.WindowToolbar,
			items:[{
				border: false,
				xtype: 'panel',
				region: 'center',
				layout: 'border',
				id: 'drpvGridPanel',
				items: [wnd.SearchGrid]
			}]
		});
		sw.Promed.swDrugRequestPeriodViewWindow.superclass.initComponent.apply(this, arguments);
	}	
});