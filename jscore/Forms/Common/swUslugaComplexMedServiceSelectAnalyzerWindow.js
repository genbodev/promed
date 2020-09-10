/**
* swUslugaComplexMedServiceSelectAnalyzerWindow - форма выбора анализатора
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2013 Swan Ltd.
* @author       Dmitry Vlasenko
* @version      11.12.2013
*/

sw.Promed.swUslugaComplexMedServiceSelectAnalyzerWindow = Ext.extend(sw.Promed.BaseForm, {
	width: 800,
	height: 600,
	id: 'swUslugaComplexMedServiceSelectAnalyzerWindow',
	modal: true,
	resizable: false,
	autoHeight: false,
	plain: false,
	onCancel: function() {},	
	show: function() {
        sw.Promed.swUslugaComplexMedServiceSelectAnalyzerWindow.superclass.show.apply(this, arguments);
		
		var win = this;
		
		if( arguments[0].callback && getPrimType(arguments[0].callback) == 'function' ) {
			this.callback = arguments[0].callback;
		}
		
		if (arguments[0].onCancel && getPrimType(arguments[0].onCancel) == 'function' ) {
			this.onCancel = arguments[0].onCancel;
		}
		
		this.MedService_id = null;
		this.UslugaComplexMedService_ids = [];
		
		if (arguments[0].MedService_id) {
			this.MedService_id = arguments[0].MedService_id;
		}
		
		if (arguments[0].UslugaComplexMedService_ids) {
			this.UslugaComplexMedService_ids = arguments[0].UslugaComplexMedService_ids;
		}
		
		this.setTitle(lang['vyiberite_analizator_dlya_svyazi_uslug']);
		
		this.AnalyzerGrid.removeAll();
		this.AnalyzerGrid.getGrid().getStore().load({
			params: {
				MedService_id: win.MedService_id
			}
		});
	
		this.center();
	},
	
	callback: Ext.emptyFn,
	
	LisSelectEquipment: function() {
		var win = this;
		var record = this.AnalyzerGrid.getGrid().getSelectionModel().getSelected();
		if (!record || Ext.isEmpty(record.get('Analyzer_id'))) {
			return false;
		}
		
		win.getLoadMask('Пожалуйста подождите, идёт связь услуг с анализатором...').show();
		Ext.Ajax.request({
			url: '/?c=AnalyzerTest&m=linkUslugaComplexMedService',
			params: {
				MedService_id: win.MedService_id,
				UslugaComplexMedService_ids: win.UslugaComplexMedService_ids,
				Analyzer_id: record.get('Analyzer_id')
			},
			callback: function(options, success, response) {
				win.getLoadMask().hide();
				win.callback();
				win.hide();
			}
		});
	},
	
	initComponent: function() {
    	
		var win = this;
		
		this.AnalyzerGrid = new sw.Promed.ViewFrame({
			id: this.id + '_Grid',
			toolbar: false,
			onEnter: this.LisSelectEquipment.createDelegate(this),
			actions: [
				{ name: 'action_add', hidden: true, disabled: true },
				{ name: 'action_edit', hidden: true, disabled: true },
				{ name: 'action_view', hidden: true, disabled: true },
				{ name: 'action_delete', hidden: true, disabled: true }
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 150,
			border: false,
			autoLoadData: false,
			stripeRows: true,
			stringfields: [
				{name: 'Analyzer_id', type: 'int', hidden: true, key: true},
				{name: 'Analyzer_Code', header: lang['kod'], type: 'string', width: 100},
				{name: 'Analyzer_Name', header: lang['naimenovanie'], type: 'string', id: 'autoexpand'}
			],
			dataUrl: '/?c=Analyzer&m=loadList',
			totalProperty: 'totalCount'
		});
		
		this.AnalyzerGrid.getGrid().on('rowdblclick', this.LisSelectEquipment.createDelegate(this));
		
		Ext.apply(this, {
			buttonAlign: 'right',
			layout: 'fit',
			buttons: [{
				text: lang['vyibrat'],
				iconCls: 'ok16',
				handler: this.LisSelectEquipment.createDelegate(this)
			}, 
			'-',
			{
				text: lang['zakryit'],
				iconCls: 'close16',
				handler: function(button, event) {
					win.onCancel();
					win.hide();
				}
			}],
			items: [this.AnalyzerGrid]

		});
		
		sw.Promed.swUslugaComplexMedServiceSelectAnalyzerWindow.superclass.initComponent.apply(this, arguments);
	}
});