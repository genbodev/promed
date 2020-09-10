/**
* swEvnDirectionFuncDiagSelectWindow - окно выбора заявки.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
*/
/*NO PARSE JSON*/

sw.Promed.swEvnDirectionFuncDiagSelectWindow = Ext.extend(sw.Promed.BaseForm, {
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	draggable: true,
	width: 800,
	height: 300,
	layout: 'border',
	listeners: {
		'hide': function() {
			this.onHide();
		}
	},
	maximizable: true,
	minHeight: 300,
	minWidth: 800,
	title: langs('Выбор заявки'),
	modal: true,
	personId: null,
	plain: true,
	resizable: true,
	doNew: function() {
		getWnd('swEvnFuncRequestEditWindow').show(this.params);
		this.hide();
	},
	doSelect: function() {
		var grid = this.Grid.getGrid(),
			rec = grid.getSelectionModel().getSelected();
		
		if (!rec || !rec.get('EvnDirection_id')) return false;
		
		this.params.EvnDirection_id = rec.get('EvnDirection_id');
		this.params.OuterKzDirection = rec.get('OuterKzDirection');
		this.params.action = 'edit';
		
		getWnd('swEvnFuncRequestEditWindow').show(this.params);
		
		this.hide();
	},
	initComponent: function() {
		var win = this;

		this.Grid = new sw.Promed.ViewFrame({
			autoLoadData: false,
			border: false,
			paging: false,
			toolbar: false,
			onDblClick: function() {
				win.doSelect();
			},
			onEnter: function() {
				win.doSelect();
			},
			region: 'center',
			stringfields: [
				{name: 'EvnDirection_id', type: 'int', header: 'ID', key: true },
				{name: 'TimetableResource_begTime', type: 'string', header: 'Запись', width: 80},
				{name: 'UslugaComplex_Name', header: 'Услуга', width: 160, id: 'autoexpand'},
				{name: 'EvnDirection_IsCito', header: 'Cito!', type: 'checkbox', width: 40},
				{name: 'EvnUslugaPar_setDate', dateFormat: 'd.m.Y', type: 'date', header: 'Дата исследования', width: 120},
				{name: 'EvnDirection_Num', header: 'Направление', type: 'string', width: 80},
				{name: 'EvnDirection_setDate', dateFormat: 'd.m.Y', type: 'date', header: 'Дата направления', width: 120},
				{name: 'PrehospDirect_Name', header: 'Кем направлен', width: 160, id: 'autoexpand'},
				{name: 'OuterKzDirection', hidden: true},
			]
		});

		Ext.apply(this, {
			items: [
				win.Grid
			],
			buttons: [{
				handler: function() {
					win.doSelect();
				},
				iconCls: 'ok16',
				text: langs('Выбрать')
			}, {
				handler: function() {
					win.doNew();
				},
				iconCls: 'add16',
				text: 'Новая заявка'
			}, {
				text: '-'
			},
			HelpButton(this),
			{
				handler: function() {
					win.hide();
				},
				iconCls: 'cancel16',
				text: BTN_FRMCLOSE
			}]
		});
		sw.Promed.swEvnDirectionFuncDiagSelectWindow.superclass.initComponent.apply(this, arguments);
	},
	show: function() {
		sw.Promed.swEvnDirectionFuncDiagSelectWindow.superclass.show.apply(this, arguments);

		var win = this;
		var grid = this.Grid.getGrid();

		this.restore();
		this.center();
		
		this.params = arguments[0].params || null;
		this.dir_list = arguments[0].dir_list || [];
		
		grid.getStore().removeAll();
		grid.getStore().loadData(this.dir_list);
	}
});

