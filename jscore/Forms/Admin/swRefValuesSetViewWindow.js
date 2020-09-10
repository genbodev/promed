/**
 * swRefValuesSetViewWindow - окно загрузки/сохранения наборов референсных значений
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2014 Swan Ltd.
 * @author       Dmitry Vlasenko
 * @version      19.01.2014
 * @comment
 */
sw.Promed.swRefValuesSetViewWindow = Ext.extend(sw.Promed.BaseForm,	{
	maximized: false,
	objectName: 'swRefValuesSetViewWindow',
	objectSrc: '/jscore/Forms/Admin/swRefValuesSetViewWindow.js',
	title: lang['referensnyie_znacheniya'],
	layout: 'border',
	id: 'RefValuesSetViewWindow',
	modal: true,
	shim: false,
	width: 700,
	height: 400,
	resizable: false,
	show: function()
	{
		var win = this;
		sw.Promed.swRefValuesSetViewWindow.superclass.show.apply(this, arguments);
		
		this.RefValuesSet_id = null;
		
		if ( !arguments[0] || !arguments[0].RefValuesSet_id ) {
			sw.swMsg.alert(lang['oshibka'], lang['ne_ukazanyi_vhodnyie_dannyie'], function() { win.hide(); });
			return false;
		}
		
		this.AnalyzerTest_IsTest = 2;
		if (arguments[0].AnalyzerTest_IsTest) {
			this.AnalyzerTest_IsTest = arguments[0].AnalyzerTest_IsTest;
		}

		if (this.AnalyzerTest_IsTest == 1) {
			win.buttons[0].hide();
			win.buttons[1].hide();
			win.AnalyzerTestRefValuesGrid.setColumnHidden('AnalyzerTest_Name', false);
		} else {
			win.buttons[0].show();
			win.buttons[1].show();
			win.AnalyzerTestRefValuesGrid.setColumnHidden('AnalyzerTest_Name', true);
		}
		
		this.records = arguments[0].records || null;
		this.loadRefValuesSet(arguments[0].RefValuesSet_id, arguments[0].RefValuesSet_Name);
	},
	loadRefValuesSet: function(RefValuesSet_id, RefValuesSet_Name)
	{
		var win = this;
		win.RefValuesSet_id = RefValuesSet_id;
		win.RefValuesSet_Name = RefValuesSet_Name;
		win.setTitle(lang['referensnyie_znacheniya'] + RefValuesSet_Name);
		win.AnalyzerTestRefValuesGrid.loadData({params:{RefValuesSet_id: RefValuesSet_id}, globalFilters:{RefValuesSet_id: RefValuesSet_id}});
		win.processPrevNextButtons();
	},
	processPrevNextButtons: function() {
		var win = this;
		var last = false;
		var first = false;
		var firstrec = true;
		for(var k in win.records) {
			if (!Ext.isEmpty(win.records[k].RefValuesSet_id)) {
				last = false;
				if (win.records[k].RefValuesSet_id == win.RefValuesSet_id) {
					if (firstrec) {
						first = true;
					}
					last = true;
				}
				firstrec = false;
			}
		}
		
		if (first) {
			win.buttons[0].disable();
		} else {
			win.buttons[0].enable();
		}
		
		if (last) {
			win.buttons[1].disable();
		} else {
			win.buttons[1].enable();
		}
	},
	initComponent: function()
	{
		var win = this;
		
		// референсные значения
		this.AnalyzerTestRefValuesGrid = new sw.Promed.ViewFrame({
			focusOnFirstLoad: false,
			actions: [
				{name: 'action_add', hidden: true, disabled: true},
				{name: 'action_edit', hidden: true, disabled: true},
				{name: 'action_view', hidden: true, disabled: true},
				{name: 'action_delete', hidden: true, disabled: true},
				{name: 'action_print', hidden: true, disabled: true}
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 150,
			autoLoadData: false,
			border: false,
			dataUrl: '/?c=RefValuesSet&m=loadRefValuesSetRefValues',
			height: 200,
			object: 'AnalyzerTestRefValues',
			uniqueId: true,
			editformclassname: 'swAnalyzerTestRefValuesEditWindow',
			scheme: 'lis',
			paging: false,
			style: 'margin-bottom: 10px',
			stringfields: [
				{name: 'RefValuesSetRefValues_id', type: 'int', header: 'ID', key: true},
				{name: 'RefValues_id', type: 'int', hidden: true},
				{name: 'AnalyzerTest_Name', type: 'string', header: lang['test'], width: 150},
				{name: 'RefValues_Name', type: 'string', header: lang['naimenovanie'], autoexpand: true},
				{name: 'RefValues_Limit', header: lang['norm_znacheniya'], type: 'string', width: 100},
				{name: 'RefValues_CritValue', header: lang['krit_znacheniya'], type: 'string', width: 100},
				{name: 'Unit_Name', header: lang['ed_izm'], type: 'string', width: 100},
				{name: 'RefValues_Description', header: lang['kommentariy'], type: 'string', width: 150},
				{name: 'Sex_Name', header: lang['pol'], type: 'string', width: 100},
				{name: 'RefValues_Age', header: lang['vozrast'], type: 'string', width: 100},
				{name: 'HormonalPhaseType_Name', header: lang['faza_tsikla'], type: 'string', width: 100},
				{name: 'RefValues_Pregnancy', header: lang['beremennost'], type: 'string', width: 100},
				{name: 'RefValues_TimeOfDay', header: lang['vremya_sutok_chas'], type: 'string', width: 100}
			],
			title: '',
			toolbar: false
		});
		
		var form = new Ext.Panel({
			autoScroll: true,
			region: 'center',
			bodyBorder: false,
			border: false,
			layout: 'border',
			frame: false,
			labelAlign: 'right',
			items: [
				win.AnalyzerTestRefValuesGrid
			]
		});

		Ext.apply(this, {
			buttons: [{
				handler: function()
				{
					var newrecord = false;
					for(var k in win.records) {
						if (!Ext.isEmpty(win.records[k].RefValuesSet_id)) {
							if (win.records[k].RefValuesSet_id == win.RefValuesSet_id) {
								break;
							}
							newrecord = win.records[k];
						}
					}
					
					if (newrecord) {
						win.loadRefValuesSet(newrecord.RefValuesSet_id, newrecord.RefValuesSet_Name);
					}
				},
				text: lang['predyiduschiy_nabor']
			}, {
				handler: function()
				{
					var found = false
					var newrecord = false;
					for(var k in win.records) {
						if (!Ext.isEmpty(win.records[k].RefValuesSet_id)) {
							if (found) {
								newrecord = win.records[k];
								break;
							}
							if (win.records[k].RefValuesSet_id == win.RefValuesSet_id) {
								found = true;
							}
						}
					}
					
					if (newrecord) {
						win.loadRefValuesSet(newrecord.RefValuesSet_id, newrecord.RefValuesSet_Name);
					}
				},
				text: lang['sleduyuschiy_nabor']
			}, {
				text: '-'
			},
			HelpButton(this),
			{
				handler: function()
				{
					win.hide();
				},
				iconCls: 'cancel16',
				text: lang['zakryit']
			}],
			items:[form]
		});
		
		sw.Promed.swRefValuesSetViewWindow.superclass.initComponent.apply(this, arguments);
	}
});