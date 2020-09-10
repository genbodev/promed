/**
 * swMedStaffFactReplaceViewWindow - окно просмотра графика замещений
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Polka
 * @access			public
 * @copyright		Copyright (c) 2017 Swan Ltd.
 * @author			Dmitrii Vlasenko
 * @version			10.09.2017
 */

/*NO PARSE JSON*/

sw.Promed.swMedStaffFactReplaceViewWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'swMedStaffFactReplaceViewWindow',
	width: 640,
	height: 800,
	maximized: true,
	maximizable: false,
	layout: 'border',
	title: 'График замещений',

	searchMedStaffFactReplace: function(reset, callback) {
		var base_form = this.MedStaffFactReplaceFilter.getForm();
		if (reset) {
			base_form.reset();
			var date = Date.parseDate(getGlobalOptions().date, 'd.m.Y');
			base_form.findField('MedStaffFactReplace_DateRange').setValue(Ext.util.Format.date(date.add('d',-30), 'd.m.Y')+' - '+Ext.util.Format.date(date));
		}
		var params = {globalFilters: base_form.getValues()};
		params.callback = (typeof callback == 'function') ? callback : Ext.emptyFn;
		this.MedStaffFactReplaceGrid.loadData(params);
	},
	show: function(){
		sw.Promed.swMedStaffFactReplaceViewWindow.superclass.show.apply(this, arguments);

		var win = this;
		var base_form = this.MedStaffFactReplaceFilter.getForm();

		setMedStaffFactGlobalStoreFilter();
		base_form.findField('MedStaffFact_id').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));
		base_form.findField('MedStaffFact_rid').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));

		this.searchMedStaffFactReplace(true);
	},

	openMedStaffFactReplaceEditWindow: function(action) {
		if (!action.inlist(['add','edit','view'])) {
			return false;
		}

		var grid = this.MedStaffFactReplaceGrid.getGrid();
		var params = {action: action};

		if (action != 'add') {
			params.MedStaffFactReplace_id = grid.getSelectionModel().getSelected().get('MedStaffFactReplace_id');
		}

		params.callback = function() {
			this.MedStaffFactReplaceGrid.getAction('action_refresh').execute();
		}.createDelegate(this);

		getWnd('swMedStaffFactReplaceEditWindow').show(params);
	},

	initComponent: function() {
		var win = this;

		this.MedStaffFactReplaceFilter = new sw.Promed.FormPanel({
			keys:
			[{
				key: Ext.EventObject.ENTER,
				fn: function(e)
				{
					win.searchMedStaffFactReplace();
				},
				stopEvent: true
			}],
			items: [{
				layout: 'column',
				items: [{
					layout: 'form',
					items: [{
						xtype: 'swmedstafffactglobalcombo',
						hiddenName: 'MedStaffFact_rid',
						fieldLabel: 'Сотрудник 1 (замещающий)',
						listWidth: 500,
						width: 250
					}]
				}, {
					layout: 'form',
					items: [{
						xtype: 'daterangefield',
						plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
						name: 'MedStaffFactReplace_DateRange',
						fieldLabel: 'Дата замещения',
						width: 180
					}]
				}, {
					layout: 'form',
					items: [{
						xtype: 'swmedstafffactglobalcombo',
						hiddenName: 'MedStaffFact_id',
						fieldLabel: 'Сотрудник 2 (замещаемый)',
						listWidth: 500,
						width: 250
					}]
				}, {
					layout: 'form',
					style: 'margin-left: 20px',
					items: [{
						xtype: 'button',
						handler: function () {
							this.searchMedStaffFactReplace();
						}.createDelegate(this),
						iconCls: 'search16',
						text: lang['nayti'],
						minWidth: 80
					}]
				}, {
					layout: 'form',
					style: 'margin-left: 10px',
					items: [{
						xtype: 'button',
						handler: function () {
							this.searchMedStaffFactReplace(true);
						}.createDelegate(this),
						iconCls: 'resetsearch16',
						text: lang['sbrosit']
					}]
				}]
			}]
		});

		this.MedStaffFactReplaceGrid = new sw.Promed.ViewFrame({
			title: 'Список замещений',
			dataUrl: '/?c=MedStaffFactReplace&m=loadMedStaffFactReplaceGrid',
			uniqueId: true,
			border: true,
			autoLoadData: false,
			object: 'MedStaffFactReplace',
			region: 'center',
			stringfields: [
				{name: 'MedStaffFactReplace_id', type: 'int', header: 'MedStaffFactReplace_id', key: true, hidden: true},
				{name: 'MedStaffFact_rDesc', type: 'string', header: 'Место работы замещающего сотрудника', width: 300},
				{name: 'MedStaffFactReplace_BegDate', type: 'date', header: 'Дата начала замещения', width: 150},
				{name: 'MedStaffFactReplace_EndDate', type: 'date', header: 'Дата окончания замещения', width: 150},
				{name: 'MedStaffFact_Desc', type: 'string', header: 'Место работы замещаемого сотрудника', width: 300},
				{name: 'pmUser_Name', type: 'string', header: 'ФИО пользователя, добавившего запись', width: 250, id: 'autoexpand'}
			],
			actions: [
				{name:'action_add', handler: function(){this.openMedStaffFactReplaceEditWindow('add')}.createDelegate(this)},
				{name:'action_edit', handler: function(){this.openMedStaffFactReplaceEditWindow('edit')}.createDelegate(this)},
				{name:'action_view', handler: function(){this.openMedStaffFactReplaceEditWindow('view')}.createDelegate(this)},
				{name:'action_delete', url: '/?c=MedStaffFactReplace&m=deleteMedStaffFactReplace'},
				{name:'action_refresh'},
				{name:'action_print'}
			],
			onRowSelect: function(sm,index,record){

			}.createDelegate(this)
		});

		Ext.apply(this, {
			items: [{
				border: false,
				layout: 'border',
				region: 'center',
				items: [
					{
						autoHeight: true,
						frame: true,
						region: 'north',
						items: [this.MedStaffFactReplaceFilter]
					},
					this.MedStaffFactReplaceGrid
				]
			}],
			buttons: [
			{
				text: '-'
			},
			HelpButton(this, 1),
			{
				handler: function () {
					this.hide();
				}.createDelegate(this),
				iconCls: 'cancel16',
				text: lang['zakryit']
			}]
		});

		sw.Promed.swMedStaffFactReplaceViewWindow.superclass.initComponent.apply(this, arguments);
	}
});
