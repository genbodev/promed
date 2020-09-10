/**
 * ЛИС: форма "Анализаторы (рабочие места ЛИС)"
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      All
 * @access       public
 * @autor        gabdushev
 * @copyright    Copyright (c) 2011 Swan Ltd.
 * @version      25.11.2012
 */

sw.Promed.swAnalyzerWindow = Ext.extend(sw.Promed.BaseForm, {
	title: lang['analizatoryi'],
	modal: true,
	height: 400,
	width: 650,
	shim: false,
	resizable: false,
	plain: true,
	onSelect: Ext.emptyFn,
	layout: 'fit',
	buttonAlign: "right",
	objectName: 'swAnalyzerWindow',
	closeAction: 'hide',
	id: 'swAnalyzerWindow',
	objectSrc: '/jscore/Forms/Lis/swAnalyzerWindow.js',
	buttons: [
		'-',
		{
			text: lang['zakryit'],
			tabIndex: -1,
			tooltip: lang['zakryit'],
			iconCls: 'cancel16',
			handler: function () {
				this.ownerCt.hide();
			}
		}
	],
	show: function () {
		var that = this;
		if (!arguments[0] || !arguments[0].MedService_id){
			sw.swMsg.alert(lang['slujba_ne_ukazana'], lang['nebohodimo_privyazat_uchetnuyu_zapis_tekuschschego_polzovatelya_so_slujboy_ili_vyipolnit_vhod_ot_imeni_polzovatelya_uchetnaya_zapis_kotorogo_svyazana_s_kakoy-libo_iz_slujb'], function() { that.hide(); });
			return false;
		}
		this.MedService_id = arguments[0].MedService_id;
		sw.Promed.swAnalyzerWindow.superclass.show.apply(this, arguments);
		this.grid.loadData({globalFilters: {MedService_id: this.MedService_id}});
		return true;
	},
	initComponent: function () {
		var that = this;
		this.grid = new sw.Promed.ViewFrame({
			actions: [
				{
					name: 'action_add',
					handler: function (){
						getWnd(that.grid.editformclassname).show({action: 'add', MedService_id: that.MedService_id, callback:
							function (){
								that.grid.loadData({globalFilters: {MedService_id: that.MedService_id}});
							}
						});
					}
				},
				{
					name: 'action_edit',
					handler: function (){
						var sel = that.grid.getGrid().getSelectionModel().getSelected();
						if (sel) {
							getWnd(that.grid.editformclassname).show({action: 'edit', Analyzer_id: sel.id, callback:
								function (){
									that.grid.loadData({globalFilters: {MedService_id: that.MedService_id}});
								}
							});
						}
					}

				},
				{name: 'action_view', hidden: true},
				{name: 'action_delete'},
				{name: 'action_print', hidden: true}
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 150,
			autoLoadData: false,
			obj_isEvn: false,
			scheme: 'lis',
			border: true,
			dataUrl: '/?c=Analyzer&m=loadList',
			height: 180,
			region: 'north',
			object: 'Analyzer',
			editformclassname: 'swAnalyzerEditWindow',
			id: 'AnalyzerGrid',
			paging: false,
			style: 'margin-bottom: 10px',
			stringfields: [
				{name: 'Analyzer_id', type: 'int', header: 'ID', key: true},
				{name: 'Analyzer_Name', type: 'string', header: lang['naimenovanie_analizatora'], width: 120},
				{name: 'Analyzer_Code', type: 'string', header: lang['kod'], width: 120},
				{name: 'AnalyzerModel_id_Name', type: 'string', header: lang['model_analizatora'], width: 120},
				{name: 'AnalyzerModel_id', type: 'int', hidden: true},
				{name: 'MedService_id_Name', type: 'string', header: lang['slujba'], hidden: true, width: 120},
				{name: 'MedService_id', type: 'int', hidden: true},
				{name: 'Analyzer_begDT', type: 'date', header: lang['data_otkryitiya'], width: 120},
				{name: 'Analyzer_endDT', type: 'date', header: lang['data_zakryitiya'], width: 120}
			],
			title: false,
			toolbar: true
		});
		Ext.apply(this, {
			bodyStyle: 'padding: 5px;',
			items: [this.grid]
		});
		sw.Promed.swAnalyzerWindow.superclass.initComponent.apply(this, arguments);
	}
});