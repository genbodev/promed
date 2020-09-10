/**
 * swEvnNotifyProfAlreadyExistsWindow - Предупреждение о сохранённости извещения по профзаболеванию
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * @package      Prof
 * @access       public
 * @copyright    Copyright (c) 2009-2014 Swan Ltd.
 * @author       Dmitriy Vlasenko
 * @version      12.2014
 */
sw.Promed.swEvnNotifyProfAlreadyExistsWindow = Ext.extend(sw.Promed.BaseForm, {codeRefresh: true,
	buttonAlign: 'left',
	closable: true,
	closeAction: 'hide',
	collapsible: true,
	height: 400,
	id: 'EvnNotifyProfAlreadyExistsWindow',
	initComponent: function() {
		var win = this;
		this.RootViewFrame = new sw.Promed.ViewFrame({
			actions: [
				{name: 'action_add', hidden: true, disable: true},
				{name: 'action_edit', hidden: true, disable: true},
				{name: 'action_view', hidden: true, disable: true},
				{name: 'action_delete', hidden: true, disable: true},
				{name: 'action_refresh', hidden: true, disable: true},
				{name: 'action_print'}
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 150,
			autoLoadData: false,
			dataUrl: C_SEARCH,
			object: 'EvnNotifyProf',
			region: 'center',
			stringfields: [
				{name: 'EvnNotifyProf_id', type: 'int', header: 'ID', key: true},
				{name: 'EvnNotifyProf_setDate', type: 'date', format: 'd.m.Y', header: lang['data_zapolneniya'], width: 120},
				{name: 'MorbusProfDiag_Name', type: 'string', header: lang['zabolevanie'], width: 120},
				{name: 'Org_Name', type: 'string', header: lang['organizatsiya'], width: 120},
				{name: 'EvnNotifyProf_Section', type: 'string', header: lang['naimenovanie_tseha'], width: 120},
				{name: 'Post_name', type: 'string', header: lang['professiya'], width: 120},
				{name: 'Lpu_Name', type: 'string', header: lang['mo_ustanovivshaya_diagnoz'], width: 150, id: 'autoexpand'}
			],
			toolbar: true
		});
		

		Ext.apply(this, {
			buttons: [{
				handler: function() {
					_processingResponseCheckEvnNotify(win.EvnData, win.callback, 'checkEvnNotify');
					win.hide();
				},
				iconCls: 'add16',
				text: lang['sozdat_izveschenie']
			}, {
				text: '-'
			},
			HelpButton(this, -1),
			{
				handler: function() {
					win.hide();
				},
				iconCls: 'cancel16',
				text: lang['otmena']
			}],
			items: [{
				frame: true,
				height: 40,
				items: [{
					html: '',
					id: win.id + '_NotifyLabel',
					xtype: 'label'
				}],
				region: 'north',
				border: false,
				xtype: 'panel'
			}, this.RootViewFrame ]
		});
		
		sw.Promed.swEvnNotifyProfAlreadyExistsWindow.superclass.initComponent.apply(this, arguments);
		
	},
	layout: 'border',
	maximizable: true,
	minHeight: 400,
	minWidth: 800,
	modal: false,
	plain: true,
	resizable: true,
	show: function() {
		sw.Promed.swEvnNotifyProfAlreadyExistsWindow.superclass.show.apply(this, arguments);

		var win = this;

		this.RootViewFrame.getGrid().getStore().removeAll();

		if (arguments[0] && arguments[0].result && arguments[0].result.Records) {
			this.RootViewFrame.getGrid().getStore().loadData(arguments[0].result.Records);
		}

		this.callback = Ext.emptyFn();
		if (arguments[0] && arguments[0].callback) {
			this.callback = arguments[0].callback;
		}

		this.EvnData = null;
		var Diag_Name = '';
		if (arguments[0] && arguments[0].result && arguments[0].result.EvnData) {
			this.EvnData = arguments[0].result.EvnData;
			Diag_Name = arguments[0].result.EvnData.Diag_Name;
		}
		this.findById(win.id + '_NotifyLabel').setText('<b>На данного пациента уже сохранено извещение с диагнозом '+Diag_Name+' учетного документа</b>', false);
	},
	title: lang['preduprejdenie_o_sohranennosti_izvescheniya_po_profzabolevaniyu'],
	width: 800
});
