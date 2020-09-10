/*NO PARSE JSON*/

(function(){
	var objects = {};

	objects.onLoadPanel = function() {

		objects.resize(objects.MainPanel.findById('AccessRightsArmSmoMesPanel'));
		objects.resize(objects.MainPanel.findById('AccessRightsArmSmoEmkPanel'));
		objects.AccessRightsArmSmoMesGrid.getGrid().getStore().removeAll();
		objects.AccessRightsArmSmoEmkGrid.getGrid().getStore().removeAll();
		objects.MainPanel.findById('AccessRightsArmSmoMesPanel').isLoaded = true;
		objects.MainPanel.findById('AccessRightsArmSmoEmkPanel').isLoaded = true;
		objects.AccessRightsArmSmoMesGrid.loadData();
		objects.AccessRightsArmSmoEmkGrid.loadData();
	};

	objects.openAccessRightsArmSmoEditWindow = function(gridPanel, action){
		if ( !action.inlist(['add','edit','view']) ) {
			return false;
		}

		var grid = gridPanel.getGrid(),
		    record = grid.getSelectionModel().getSelected(),
		    key = 'Org_id',
		    params = {},
            smos = [];

        grid.getStore().each(function(rec){
            if (action != 'edit' || rec.get('Org_id') != record.get('Org_id')) {
                smos.push(rec.get('Org_id'));
            }
        });

		params.action = action;
		params.smos = smos;
		params.formParams = {AccessRightsName_Code: gridPanel.getParam('AccessRightsName_Code',false)};
		params.idPanel = gridPanel.id;

		if ( action != 'add' ) {
			params.formParams['Org_id'] = record.get('Org_id');
			params.formParams['AccessRightsOrg_id'] = record.get('AccessRightsOrg_id');
			params.formParams['AccessRightsName_Code'] = record.get('AccessRightsName_Code');
		}

		params.callback = function(data) {
			grid.getStore().load();
            Ext.Msg.alert(lang['vnimanie'], lang['proverte_nalichie_podpisannogo_dogovora_ob_oplate_uslug_dostupa_k_dopolnitelnyim_funktsiyam'])
		};

		getWnd('swAccessRightsArmSmoEditWindow').show(params);
	};

	objects.deleteAccessRightsArmSmo = function(gridPanel){
		var grid = gridPanel.getGrid();
		var idField = 'AccessRightsOrg_id';
		var url = '/?c=AccessRights&m=deleteAccessRightsArmSmo';
		var question = lang['udalit_vyibrannuyu_zapis'];

		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId, text, obj) {
				if ( buttonId == 'yes' ) {

					if ( !grid || !grid.getSelectionModel() || !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get(idField) ) {
						return false;
					}

					var record = grid.getSelectionModel().getSelected(),
					    params = {};
					params[idField] = record.get(idField);

					Ext.Ajax.request({
						callback: function(options, success, response) {
							if (success) {
								grid.getStore().load();
							} else {
								Ext.Msg.alert(lang['oshibka'], lang['oshibka_zaprosa_k_serveru_poprobuyte_povtorit_operatsiyu']);
							}
						},
						params: params,
						url: url
					});
				}
			},
			icon: Ext.MessageBox.QUESTION,
			msg: question,
			title: lang['vopros']
		});
	};

	objects.resize = function(panel, maximum) {
		var pSize, gSize;
		pSize = maximum ? 400 : 213;
		gSize = pSize-25;

		panel.setHeight(pSize);
		panel.items.itemAt(0).setHeight(gSize);
	};

	objects.AccessRightsArmSmoMesGrid = new sw.Promed.ViewFrame({
		dataUrl: '/?c=AccessRights&m=loadAccessRightsArmSmoGrid',
		id: 'AccessRightsArmSmoMesGrid',
		border: false,
		layout: 'fit',
		autoLoadData: false,
		showCountInTop: false,
		stripeRows: true,
		root: 'data',
		params: {AccessRightsName_Code: 1},
		gFilters: {AccessRightsName_Code: 1},
		stringfields: [
			{name: 'AccessRightsOrg_id', type: 'int', header: 'ID', key: true},
			{name: 'Org_id', type: 'int', header: 'ID', hidden: true},
			{name: 'AccessRightsName_Code', type: 'int', hidden: true},
			{name: 'OrgSMO_Nick', type: 'string', header: langs('Наименование СМО/ТФОМС'), id: 'autoexpand'}
		],
		actions: [
			{name:'action_add', handler: function(){objects.openAccessRightsArmSmoEditWindow(objects.AccessRightsArmSmoMesGrid, 'add');}},
			{name:'action_edit', handler: function(){objects.openAccessRightsArmSmoEditWindow(objects.AccessRightsArmSmoMesGrid, 'edit');}},
			{name:'action_view', hidden: true},
			{name:'action_delete', handler: function(){objects.deleteAccessRightsArmSmo(objects.AccessRightsArmSmoMesGrid);}},
			{name:'action_refresh', hidden: true},
			{name:'action_print', hidden: true}
		],
		onRowSelect: function(sm,index,record){
		}
	});



	objects.AccessRightsArmSmoEmkGrid = new sw.Promed.ViewFrame({
		dataUrl: '/?c=AccessRights&m=loadAccessRightsArmSmoGrid',
		id: 'AccessRightsArmSmoEmkGrid',
		border: false,
		layout: 'fit',
		autoLoadData: false,
		showCountInTop: false,
		stripeRows: true,
		root: 'data',
		params: {AccessRightsName_Code: 101},
		gFilters: {AccessRightsName_Code: '101,111'},
		stringfields: [
			{name: 'AccessRightsOrg_id', type: 'int', header: 'ID', key: true},
			{name: 'Org_id', type: 'int', header: 'ID', hidden: true},
			{name: 'AccessRightsName_Code', type: 'int', hidden: true},
			{name: 'OrgSMO_Nick', type: 'string', header: langs('Наименование СМО/ТФОМС'), id: 'autoexpand'}
		],
		actions: [
			{name:'action_add', handler: function(){objects.openAccessRightsArmSmoEditWindow(objects.AccessRightsArmSmoEmkGrid, 'add');}},
			{name:'action_edit', handler: function(){objects.openAccessRightsArmSmoEditWindow(objects.AccessRightsArmSmoEmkGrid, 'edit');}},
			{name:'action_view', hidden: true},
			{name:'action_delete', handler: function(){objects.deleteAccessRightsArmSmo(objects.AccessRightsArmSmoEmkGrid);}},
			{name:'action_refresh', hidden: true},
			{name:'action_print', hidden: true}
		],
		onRowSelect: function(sm,index,record){
		}
	});

	objects.MainPanel = new Ext.Panel({
		id: 'test1',
		layout: 'form',
		border: false,
		height: 431,
		autoScroll: true,
		bodyStyle:'background:#DFE8F6;',
		onLoadPanel: objects.onLoadPanel,
		items: [
			{
				title: lang['dostup_k_funktsionalu_spravochnik_mes'],
				id: 'AccessRightsArmSmoMesPanel',
				region: 'center',
				height: 213,
				animCollapse: false,
				border: false,
				collapsible: true,
				//collapsed: true,
				style: 'margin-bottom: 5px; border-bottom: 1px solid #99bbe8; ',
				isLoaded: false,
				listeners: {
					'expand': function(panel) {
						if ( panel.isLoaded === false ) {
							/*panel.isLoaded = true;
							objects.AccessRightsArmSmoMesGrid.getGrid().getStore().load();*/
						}
						objects.MainPanel.doLayout();
					},
					'collapse': function(panel) {
						objects.MainPanel.doLayout();
					},
					'beforeexpand': function(panel) {
						var mesPanel = panel;
						var emkPanel = objects.MainPanel.findById('AccessRightsArmSmoEmkPanel');
						emkPanel.collapsed ? objects.resize(mesPanel, true) : objects.resize(emkPanel);
					},
					'beforecollapse': function(panel) {
						var mesPanel = panel;
						var emkPanel = objects.MainPanel.findById('AccessRightsArmSmoEmkPanel');
						emkPanel.collapsed ? objects.resize(mesPanel) : objects.resize(emkPanel, true);
					}
				},
				items: [objects.AccessRightsArmSmoMesGrid]
			}, {
				title: lang['dostup_k_funktsionalu_emk'],
				id: 'AccessRightsArmSmoEmkPanel',
				region: 'center',
				height: 213,
				animCollapse: false,
				border: false,
				collapsible: true,
				//collapsed: true,
				style: 'border-top: 1px solid #99bbe8;',
				isLoaded: false,
				listeners: {
					'expand': function(panel) {
						if ( panel.isLoaded === false ) {
							/*panel.isLoaded = true;
							objects.AccessRightsArmSmoEmkGrid.getGrid().getStore().load();*/
						}
						objects.MainPanel.doLayout();
					},
					'collapse': function(panel) {
						objects.MainPanel.doLayout();
					},
					'beforeexpand': function(panel) {
						var mesPanel = objects.MainPanel.findById('AccessRightsArmSmoMesPanel');
						var emkPanel = panel;
						mesPanel.collapsed ? objects.resize(emkPanel, true) : objects.resize(mesPanel);
					},
					'beforecollapse': function(panel) {
						var mesPanel = objects.MainPanel.findById('AccessRightsArmSmoMesPanel');
						var emkPanel = panel;
						mesPanel.collapsed ? objects.resize(emkPanel) : objects.resize(mesPanel, true);
					}
				},
				items: [objects.AccessRightsArmSmoEmkGrid]
			}
		]
	});

	return objects.MainPanel;
}())