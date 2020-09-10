/**
* swSelectEvnDiagPLStomWindow - окно выбора стом заболевания
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @access       public
* @copyright    Copyright (c) 2014 Swan Ltd.
* @author       Kurakin A
* @version      08.2016
* @comment      
*/
sw.Promed.swSelectEvnDiagPLStomWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: false,
	title: 'Необходимо выбрать заболевание для копируемого случая',
	layout: 'border',
	id: 'SelectEvnDiagPLStomWindow',
	modal: true,
	shim: false,
	width: 600,
	resizable: false,
	maximizable: true,
	maximized: false,
	doReset: function() {
		var wnd = this;
		wnd.SearchGrid.removeAll();
	},	
	show: function() {
        var wnd = this;
		sw.Promed.swSelectEvnDiagPLStomWindow.superclass.show.apply(this, arguments);		
		this.doReset();
		if(!arguments[0] ){
			sw.swMsg.alert(lang['oshibka'], lang['nevernyie_parametryi']);
			this.hide();
			return false;
		}

		if(arguments[0].store){
			this.SearchGrid.getGrid().getStore().loadData(arguments[0].store);
		}
		if(arguments[0].callback && typeof arguments[0].callback == 'function'){
			this.callback = arguments[0].callback;
		}
		setTimeout(
			function(){
				wnd.SearchGrid.getGrid().getSelectionModel().clearSelections();
				var records = new Array();
				wnd.SearchGrid.getGrid().getStore().each(function(rec){
					if(rec.get('inThisVizit') == 1){
						records.push(rec);
					}
				});
				if(records.length > 0){
					wnd.SearchGrid.getGrid().getSelectionModel().selectRecords(records);
				}
			},
			1000
		);
	},
	initComponent: function() {
		var wnd = this;

		this.SearchGrid = new sw.Promed.ViewFrame({
			actions: [
				{name: 'action_add', disabled:true, hidden:true},
				{name: 'action_edit', disabled:true, hidden:true},
				{name: 'action_view', disabled:true, hidden:true},
				{name: 'action_delete', disabled:true, hidden:true},
				{name: 'action_print', disabled:true, hidden:true},
				{name: 'action_refresh', disabled:true, hidden:true}
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 125,
			autoLoadData: false,
			selectionModel: 'multiselect',
			border: true,
			height: 180,
			paging: false,
			style: 'margin-bottom: 10px',
			stringfields: [
				{ name: 'EvnDiagPLStom_id', type: 'int', header: 'ID', key: true },
				{ name: 'EvnDiagPLStom_setDate', type: 'date', header: 'Дата начала заболевания', width:80 },
				{ name: 'EvnDiagPLStom_disDate', type: 'date', header: 'Дата окончания заболевания', width:80 },
				{ name: 'diag', type: 'string', header: 'Диагноз', id: 'autoexpand' },
				{ name: 'Tooth_Code', type: 'string', header: 'Номер зуба', width:80 },
				{ name: 'DeseaseType_Name', type: 'string', header: 'Характер', width:80 },
				{ name: 'inThisVizit', type: 'int', hidden: true }
			],
			title: null,
			toolbar: true,
			onLoadData:function(){
				this.getGrid().getSelectionModel().clearSelections();
				var records = new Array();
				this.getGrid().getStore().each(function(rec){
					if(rec.get('inThisVizit') == 1){
						records.push(rec);
					}
				});
				if(records.length > 0){
					this.getGrid().getSelectionModel().selectRecords(records);
				}
			}
		});

		Ext.apply(this, {
			layout: 'border',
			buttons:
			[{
				handler: function() 
				{
					var records = this.SearchGrid.getGrid().getSelectionModel().getSelections();
					if(records){
						this.callback(records);
						this.hide();
					} else {
						Ext.Msg.alert(lang['soobschenie'], 'Заболевание не выбрано');
						return false;
					}
					
				}.createDelegate(this),
				iconCls: 'ok16',
				text: 'Продолжить'
			}, {
				text: '-'
			},
			{
				handler: function() 
				{
					this.ownerCt.hide();
				},
				iconCls: 'cancel16',
				text: BTN_FRMCANCEL
			}],
			items:[
				{
					border: false,
					region: 'center',
					layout: 'border',
					items:[{
						border: false,
						region: 'center',
						layout: 'fit',
						items: [this.SearchGrid]
					}]
				}
			]
		});
		sw.Promed.swSelectEvnDiagPLStomWindow.superclass.initComponent.apply(this, arguments);
	}	
});