sw.Promed.swResearchViewWindow = Ext.extend(sw.Promed.BaseForm, {
	title: langs('Результаты исследования'),
	id: 'swResearchViewWindow',
	buttonAlign: 'left',
	closable: true,
	closeAction: 'hide',
	layout: 'border',
	modal: false,
	plain: true,
	height: 550,
	minHeight: 550,
	minWidth: 800,
	width: 800,
	buttons: [
		'-',
		{
			text      : lang['zakryit'],
			tooltip   : lang['zakryit'],
			iconCls   : 'cancel16',
			handler   : function() { this.ownerCt.hide(); }
		}
	],
	initComponent: function () {
		var win = this;
		this.UslugaTestGrid = new sw.Promed.ViewFrame({
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 150,
			autoLoadData: false,
			uniqueId: true,
			useEmptyRecord: false,
			forcePrintMenu: true,
			border: false,
			dataUrl: '/?c=CVIRegistry&m=loadResearch',
			paging: false,
			region: 'center',
			layout: 'fit',
			groupTextTpl: '<b>{[ values.rs[0].data["UslugaComplex_Name"] ]}</b>',
			groupingView: {showGroupName: false, showGroupsText: true},
			totalProperty: 'totalCount',
			stringfields: [
				{ name: 'UslugaTest_id', type: 'int', header: 'UslugaTest_id', key: true, hidden: true },
				{ name: 'EvnUslugaPar_id', type: 'int', group: true, sort: true, direction: 'ASC', header: langs('Группа'), width: 200 },
				{ name: 'UslugaComplex_Code', type:'string', header: langs('Код'), width: 80 },
				{ name: 'UslugaComplex_Name', type: 'string', header: langs('Название теста'), id: 'autoexpand' },
				{ name: 'UslugaTest_ResultValue', type: 'string', header: langs('Результат теста'), width: 100 }
			]
		});

		Ext.apply(this, {
			items: [
				this.UslugaTestGrid
			]
		});

		sw.Promed.swResearchViewWindow.superclass.initComponent.apply(this, arguments);
	},
	show: function () {
		sw.Promed.swResearchViewWindow.superclass.show.apply(this, arguments);

		this.UslugaTestGrid.ViewToolbar.hide();
		this.UslugaTestGrid.ViewGridPanel.setHeight(this.UslugaTestGrid.getInnerHeight());

		var EvnDirection_id = arguments[0].EvnDirection_id;
		var params = {
			start: 0,
			limit: 100,
			EvnDirection_id: EvnDirection_id
		};

		var gridPanel = this.UslugaTestGrid;
		gridPanel.removeAll();
		gridPanel.getGrid().getStore().load({
			params: params
		});
	}
});
