/**
* swPersonDispViewWindow - окно просмотра по диспансерному учету.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Polka
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Pshenicyn Ivan aka IVP (ipshon@rambler.ru)
* @version      25.06.2009
* tabIndex: 2400
*/

/*NO PARSE JSON*/
sw.Promed.swPersonDispViewWindow = Ext.extend(sw.Promed.BaseForm,
{
	codeRefresh: true,
	objectName: 'swPersonDispViewWindow',
	objectSrc: '/jscore/Forms/Polka/swPersonDispViewWindow.js',
	maximized: true,
	//maximizable: true,      
	addPersonDisp: function() {
		if ( getWnd('swPersonSearchWindow').isVisible() ) {
			sw.swMsg.alert(lang['soobschenie'], lang['okno_poiska_cheloveka_uje_otkryito']);
			return false;
		}

		if ( getWnd('swPersonDispEditWindow').isVisible() ) {
			sw.swMsg.alert(lang['soobschenie'], lang['okno_redaktirovaniya_dispansernoy_kartyi_patsienta_uje_otkryito']);
			return false;
		}
		
		var params = new Object();
		var formParams = new Object();

		var tree_panel = this.findById('PDVW_filter_tree');
		var selected_node = tree_panel.getSelectionModel().getSelectedNode();

		if ( selected_node && selected_node.attributes.object ) {
			// проверяем выбран ли диагноз
			if ( selected_node.attributes.object == 'Diag' ) {
				params.DiagFilter_id = selected_node.attributes.object_id;
				params.DiagLevelFilter_id = selected_node.attributes.DiagLevel_id;
			}

			// тип зпболевания
			if ( selected_node.attributes.object == 'Common' ) {
				params.Sickness_id = 100500;
			}

			if ( selected_node.attributes.object == 'Sickness' ) {
				params.Sickness_id = selected_node.attributes.object_id;
			}					
		}
		
		params.action = 'add';
		if(this.ARMType){
			params.ARMType = this.ARMType;
		}
		params.callback = function() {
			this.doUpdatePage();
			//this.refreshPersonDispViewGrid();// #139486 не обновляем весь список. но надо бы, наверное, сделать фокус на добавленном?
		}.createDelegate(this);
		params.onHide = function() {
			getWnd('swPersonSearchWindow').findById('person_search_form').getForm().findField('PersonSurName_SurName').focus(true, 500);
		}.createDelegate(this);
		var that = this;
		getWnd('swPersonSearchWindow').show({
			onClose: function() {
				//this.refreshPersonDispViewGrid();// #139486 не обновляем весь список после поиска
			}.createDelegate(this),
			onSelect: function(person_data) {
				formParams.Person_id = person_data.Person_id;
				formParams.PersonEvn_id = person_data.PersonEvn_id;
				formParams.Server_id = person_data.Server_id;
                formParams.MedPersonal_id = that.MedPersonal_id;

				params.formParams = formParams;

				getWnd('swPersonDispEditWindow').show(params);
				getWnd('swPersonSearchWindow').hide();
			}.createDelegate(this),
			searchMode: 'all'
		});
	},
	buttonAlign: 'left',
	doResetAll: function() {
		var grid = this.findById('PersonDispViewGrid').ViewGridPanel,
			DispMedPersonalCombo = this.findById('PDVW_DispMedPersonalCombo'),
			HistMedPersonalCombo = this.findById('PDVW_HistMedPersonalCombo');
		DispMedPersonalCombo.reset();
		HistMedPersonalCombo.reset();
		grid.getStore().removeAll();
	},
	editPersonDisp: function() {
		var grid = this.findById('PersonDispViewGrid').ViewGridPanel;
		var current_row = grid.getSelectionModel().getSelected();

		if ( !current_row ) {
			return false;
		}

		if ( getWnd('swPersonDispEditWindow').isVisible() ) {
			sw.swMsg.alert(lang['soobschenie'], lang['okno_redaktirovaniya_dispansernoy_kartyi_patsienta_uje_otkryito']);
			return false;
		}
		
		var formParams = new Object();
		var params = new Object();
		
		// проверяем выбран ли диагноз
		var tree_panel = this.findById('PDVW_filter_tree');
		var selected_node = tree_panel.getSelectionModel().getSelectedNode();

		if ( selected_node && selected_node.attributes.object ) {
			// проверяем выбран ли диагноз
			if ( selected_node.attributes.object == 'Diag' ) {
				params.DiagFilter_id = selected_node.attributes.object_id;
				params.DiagLevelFilter_id = selected_node.attributes.DiagLevel_id;
			}

			// тип зпболевания
			if ( selected_node.attributes.object == 'Common' ) {
				params.Sickness_id = 100500;
			}

			if ( selected_node.attributes.object == 'Sickness' ) {
				params.Sickness_id = selected_node.attributes.object_id;
			}
		}

		formParams.Person_id = current_row.data.Person_id;
		formParams.PersonDisp_id = current_row.data.PersonDisp_id;
		formParams.Server_id = current_row.data.Server_id;

		params.action = 'edit';
		if(this.ARMType){
			params.ARMType = this.ARMType;
		}
		params.callback = function() {
			this.doUpdatePage();
			//this.refreshPersonDispViewGrid();// #139486 не нужно обновлять весь список
		}.createDelegate(this);
		params.formParams = formParams;
		params.onHide = function() {
			//
		}.createDelegate(this);
		
		getWnd('swPersonDispEditWindow').show(params);
	},
	closable: true,
	closeAction: 'hide',
	collapsible: true,
	draggable: true,
    doPrintPersonDispCard: function() {
        var current_window = this;
        var grid = current_window.findById('PersonDispViewGrid').ViewGridPanel;
        var current_row = grid.getSelectionModel().getSelected();
        if (!current_row)
            return;
        var paramPersonDisp = current_row.data.PersonDisp_id;
		printBirt({
			'Report_FileName': 'PersonDispCard.rptdesign',
			'Report_Params': '&paramPersonDisp=' + paramPersonDisp,
			'Report_Format': 'pdf'
		});
    },
	deletePersonDisp: function() {
		var current_window = this;
		var grid = current_window.findById('PersonDispViewGrid').ViewGridPanel;
		var current_row = grid.getSelectionModel().getSelected();
		if (!current_row)
			return;
		// TODO: WTF?
		if (getWnd('swPersonDispEditWindow').isVisible())
		{
			current_window.showMessage(lang['soobschenie'], lang['okno_redaktirovaniya_dispansernoy_kartyi_patsienta_uje_otkryito']);
			return false;
		}
		sw.swMsg.show({
			title: lang['podtverjdenie_udaleniya'],
			msg: lang['vyi_deystvitelno_jelaete_udalit_etu_zapis'],
			buttons: Ext.Msg.YESNO,
			fn: function ( buttonId ) {
				if ( buttonId == 'yes' )
				{
					Ext.Ajax.request({
						url: '?c=PersonDisp&m=deletePersonDisp',
						params: {PersonDisp_id: current_row.data.PersonDisp_id},
						callback: function() {
							//current_window.doSearch();
							current_window.doUpdatePage();// #139486 обновить только текущую страницу
						}
					});
				}
			}
		});
	},
	print030: function(){
		var current_window = this;
		var grid = current_window.findById('PersonDispViewGrid').ViewGridPanel;
		var current_row = grid.getSelectionModel().getSelected();
		if (!current_row || !current_row.get('PersonDisp_id')){
			sw.swMsg.alert(lang['oshibka'], 'Не выбрана запись');
			return;
		}
		printBirt({
			'Report_FileName': 'f030_4u.rptdesign',
			'Report_Params': '&paramPersonDisp=' + current_row.get('PersonDisp_id'),
			'Report_Format': 'pdf'
		});
	},
	onSelectNode: function(node) {
		var object = node.attributes.object;
		if ( object == undefined || object == "razdel" ) return;
		
			/*
			if ( object == 'MedPersonal' ) {
				Ext.getCmp('PDVW_view_mp_combo').enable();
				Ext.getCmp('PDVW_view_mp_combo').showContainer();
				Ext.getCmp('PDVW_view_mp_combo').setValue(1);
				
				Ext.getCmp('PDVW_DispMedPersonalCombo').hideContainer();
				Ext.getCmp('PDVW_DispMedPersonalCombo').setValue('');
				Ext.getCmp('PDVW_HistMedPersonalCombo').hideContainer();
				Ext.getCmp('PDVW_HistMedPersonalCombo').setValue('');
				Ext.getCmp('PDVW_checkMPHistory').hideContainer();
				Ext.getCmp('PDVW_checkMPHistory').setValue(false);
				this.findById('PDVW_checkMPHistory').disable();
			} else {
				Ext.getCmp('PDVW_view_mp_combo').setValue('');
				Ext.getCmp('PDVW_view_mp_combo').disable();
				Ext.getCmp('PDVW_view_mp_combo').hideContainer();
				
				Ext.getCmp('PDVW_view_mp_onDate').setValue('');
				Ext.getCmp('PDVW_view_mp_onDate').hideContainer();
				
				Ext.getCmp('PDVW_DispMedPersonalCombo').showContainer();
				Ext.getCmp('PDVW_HistMedPersonalCombo').showContainer();
				Ext.getCmp('PDVW_checkMPHistory').showContainer();
				if(Ext.isEmpty(Ext.getCmp('PDVW_HistMedPersonalCombo').getValue()))
					this.findById('PDVW_checkMPHistory').disable();
				else
					this.findById('PDVW_checkMPHistory').enable();
			}
			*/
			Ext.getCmp('PDVW_view_mp_combo').setValue('');
			Ext.getCmp('PDVW_view_mp_combo').disable();
			Ext.getCmp('PDVW_view_mp_combo').hideContainer();
			
			Ext.getCmp('PDVW_view_mp_onDate').setValue('');
			Ext.getCmp('PDVW_view_mp_onDate').hideContainer();
			
			Ext.getCmp('PDVW_DispMedPersonalCombo').showContainer();
			Ext.getCmp('PDVW_HistMedPersonalCombo').showContainer();
			Ext.getCmp('PDVW_checkMPHistory').showContainer();

			var dispMedPersonalCombo = Ext.getCmp('PDVW_DispMedPersonalCombo');
			var histMedPersonalCombo = Ext.getCmp('PDVW_HistMedPersonalCombo');
			var curMedStaffFact_id = getGlobalOptions().CurMedStaffFact_id;
			if(isAdmin || !this.view_one_doctor){
				dispMedPersonalCombo.setValue();
				histMedPersonalCombo.setValue();
			}else{
				if(curMedStaffFact_id && histMedPersonalCombo.findRecord('MedStaffFact_id', curMedStaffFact_id)){
					histMedPersonalCombo.setValue(curMedStaffFact_id);
				}
				if(curMedStaffFact_id && dispMedPersonalCombo.findRecord('MedStaffFact_id', curMedStaffFact_id)){
					dispMedPersonalCombo.setValue(curMedStaffFact_id);
				}
			}
			if(Ext.isEmpty(Ext.getCmp('PDVW_HistMedPersonalCombo').getValue())){
				this.findById('PDVW_checkMPHistory').disable();
			}
			else{
				this.findById('PDVW_checkMPHistory').enable();
			}

		var grid = this.findById('PersonDispViewGrid').ViewGridPanel;
		var params = {
			object: object,
			id: node.attributes.object_id
		};
		if ( object == 'Diag' )
			params.DiagLevel_id = node.attributes.DiagLevel_id;
		params.view_all_id = Ext.getCmp('PDVW_view_all_combo').getValue();
		params.view_mp_id = Ext.getCmp('PDVW_view_mp_combo').getValue();
		params.view_mp_onDate = Ext.getCmp('PDVW_view_mp_onDate').getValue();
		params.start = 0;
		params.limit = 100;
		grid.getStore().removeAll();
		grid.getStore().baseParams = params;
		grid.getStore().load({
			params: params,
			callback: function(list,params,success) {
				node.select();
				node.getUI().getAnchor().focus();
				var wnd = Ext.getCmp('PersonDispViewWindow');
				var row_index = -1;
				if(wnd.Person_id)
				{
					for (var i=0; i < list.length; i++)
					{
						if(list[i].get('Person_id') == wnd.Person_id)
						{
							row_index = grid.getStore().indexOf(list[i]);
							break;
						}
					}
					if (row_index > -1)
					{
						grid.getSelectionModel().selectRow(row_index);
						grid.getView().focusRow(row_index);
						return true;
					}
				}
				if(list.length > 0)
				{
					grid.getSelectionModel().selectRow(0);
					grid.getView().focusRow(0);	
					if(wnd.replacingResponsibleDoctor) wnd.findById('PersonDispViewGrid').clearMultiSelections();		
				}else{
					grid.getStore().removeAll();
					if(wnd.replacingResponsibleDoctor)  wnd.findById('PersonDispViewGrid').ViewActions.action_editList.setDisabled(true);
				}
			}
		});
	},
	doSearch: function() {
		var grid = this.findById('PersonDispViewGrid').ViewGridPanel;
		var tree_panel = Ext.getCmp('PDVW_filter_tree');
		var node = tree_panel.getSelectionModel().getSelectedNode();
		var object = node.attributes.object;

		if (Ext.isEmpty(Ext.getCmp('PDVW_DispMedPersonalCombo').getFieldValue('MedPersonal_id'))
			&& Ext.isEmpty(Ext.getCmp('PDVW_HistMedPersonalCombo').getFieldValue('MedPersonal_id'))) {
			if ( node == undefined || node.id == 'root' )
				return;
			// var object = node.attributes.object;
			if ( object == undefined || object == 'razdel' )
				return;
			var wnd = Ext.getCmp('PersonDispViewWindow');
		}

		var params = {
			object: object,
			id: node.attributes.object_id
		};
		if ( object == 'Diag' )
			params.DiagLevel_id = node.attributes.DiagLevel_id;
		
		params.view_all_id = Ext.getCmp('PDVW_view_all_combo').getValue();
		params.view_mp_id = Ext.getCmp('PDVW_view_mp_combo').getValue();
		params.view_mp_onDate = Ext.getCmp('PDVW_view_mp_onDate').getValue();
		
		params.disp_med_personal = Ext.getCmp('PDVW_DispMedPersonalCombo').getFieldValue('MedPersonal_id');
		params.hist_med_personal = Ext.getCmp('PDVW_HistMedPersonalCombo').getFieldValue('MedPersonal_id');
		params.check_mph = Ext.getCmp('PDVW_checkMPHistory').getValue() ? 1 : 0;

		if (!Ext.isEmpty(params.hist_med_personal))
			params.check_mph = 1;

		params.start = 0;
		params.limit = 100;
		grid.getStore().removeAll();
		grid.getStore().baseParams = params;
		grid.getStore().load({
			params: params
		});
	},
	doUpdatePage: function(){
		var grid = this.findById('PersonDispViewGrid').ViewGridPanel;
		grid.getStore().reload();
	},
	height: 550,
	id: 'PersonDispViewWindow',
	initComponent: function() {
		var form = this;
		Ext.apply(this, {
		buttons: [{
			handler: function() {
				this.ownerCt.doSearch();
			},
			iconCls: 'search16',
			id: 'PDVW_SearchButton',
			onShiftTabAction: function( button ) {
				var grid = Ext.getCmp('PersonDispViewGrid').ViewGridPanel;
				if ( grid.getStore().getCount() > 0 )
				{
					grid.getSelectionModel().selectFirstRow();
					grid.getView().focusRow(0);
				}
				else
				{
					Ext.getCmp('PDVW_CancelButton').focus();
				}
			},
			onTabAction: function( button ) {
				Ext.getCmp('PDVW_ResetButton').focus();
			},
			tabIndex: 2032,
			text: BTN_FRMSEARCH
		}, {
			handler: function() {
				this.ownerCt.doResetAll();
			},
			iconCls: 'resetsearch16',
			id: 'PDVW_ResetButton',
			tabIndex: 2033,
			text: lang['cbros']
		},
		'-',
		HelpButton(this, -1),
		{
			handler: function() {
				this.ownerCt.hide();
			},
			iconCls: 'cancel16',
			id: 'PDVW_CancelButton',
			onShiftTabAction: function( button ) {
				Ext.getCmp('PDVW_ResetButton').focus();
			},
			onTabAction: function( button ) {
/*				var grid = Ext.getCmp('PersonDispViewGrid').ViewGridPanel;
				if ( grid.getStore().getCount() > 0 )
				{
					grid.getSelectionModel().selectFirstRow();
					grid.getView().focusRow(0);
				}*/
				var node = Ext.getCmp('PDVW_filter_tree').getSelectionModel().selNode;
				node.select();
				node.getUI().getAnchor().focus();				
			},
			tabIndex: 2033,
			text: BTN_FRMCLOSE
		}
		],
			items: [
				new Ext.tree.TreePanel({
					autoScroll: true,
					collapsible: true,
					split: true,
					id: 'PDVW_filter_tree',
					keys: [{
						key: Ext.EventObject.ENTER,
						fn: function(e) {
							var node = Ext.getCmp('PDVW_filter_tree').getSelectionModel().selNode;
							if ( node.id == 'root' )
							{
								if ( node.isExpanded() )
									node.collapse();
								else
									node.expand();
								return;
							}
							if ( node.isExpandable() )
							{
								if ( node.isExpanded() )
									node.collapse();
								else
									node.expand();
							}
							
							Ext.getCmp('PersonDispViewWindow').onSelectNode(node);
						},
						stopEvent: true
					}, {
						key: Ext.EventObject.TAB,
						stopEvent: true,
						fn: function() {
							Ext.getCmp('PDVW_view_all_combo').focus();
						}
					}],
					root: {
						id: 'root',
						text: Ext.globalOptions.globals.lpu_nick
					},
					selModel: new Ext.tree.DefaultSelectionModel({
						listeners: {
							'beforeselect': function(sm, node)
							{
								/*if ( node.id == 'root' )
									return;
								var object = node.attributes.object;
								if ( object == undefined || object == "razdel" )
									return;
								var wnd = Ext.getCmp('PersonDispViewWindow');
								var grid = wnd.findById('PersonDispViewGrid').ViewGridPanel;
								var params = {
									object: object,
									id: node.attributes.object_id
								};
								if ( object == 'Diag' )
									params.DiagLevel_id = node.attributes.DiagLevel_id;
								params.view_all_id = Ext.getCmp('PDVW_view_all_combo').getValue();
								params.start = 0;
								params.limit = 100;
								grid.getStore().removeAll();
								grid.getStore().baseParams = '';
								grid.getStore().load({
									params: params
									
								});*/
							}
						}
					}),
					title: lang['lpu'] + Ext.globalOptions.globals.lpu_nick,
					enableKeyEvents: true,
					listeners: {
						'beforeload': function( node ) {
							this.getLoader().baseParams = {};							
							var object = node.attributes.object;
							var object_id = node.attributes.object_id;							
							if ( object != undefined )
							{
								this.getLoader().baseParams.object = object;								
							}
							if ( object_id != undefined )
							{
								this.getLoader().baseParams.object_id = object_id;
							}
							if ( node.id == 'razdel_medpersonal' && form.view_one_doctor )
							{
								this.getLoader().baseParams.view_one_doctor = 1;
								this.getLoader().baseParams.MedPersonal_id = form.MedPersonal_id;
							}
						},
						'beforeclick': function(node) {
							if ( node.id == 'root' )
							{							
								return;
							}
							
							Ext.getCmp('PersonDispViewWindow').onSelectNode(node);
						}
					},
					loader: new Ext.tree.TreeLoader({
						url: '?c=PersonDisp&m=GetFilterTree'
					}),
					width: 200,
					region: 'west'
				}),
				new Ext.Panel({
					region: 'center',
					layout: 'border',
					items: [
					new Ext.form.FormPanel({
						keys: [{
							fn: function(e) {
								this.doSearch();
							}.createDelegate(this),
							key: Ext.EventObject.ENTER,
							stopEvent: true
						}],
						//autoHeight: 'true',
						height: 110,
						bodyStyle: 'padding: 5px 5px 5px 5px',
						items: [
							{
								layout: 'form',
								border: false,
								labelWidth: 280,
								items:[{
									layout: 'form',
									border: false,
									style: 'padding-left:10px',
									items:[
										new sw.Promed.SwBaseLocalCombo({
											displayField: 'view_all_name',
											enableKeyEvents: true,
											fieldLabel: lang['otobrajat_kartyi_du'],
											hiddenName: 'ViewAll_id',
											hideEmptyRow: true,
											id: 'PDVW_view_all_combo',
											listeners: {
												'select': function() {
													Ext.getCmp('PDVW_DispMedPersonalCombo').setFieldValue('MedPersonal_id', null);
													Ext.getCmp('PDVW_HistMedPersonalCombo').setFieldValue('MedPersonal_id', null);
													Ext.getCmp("PersonDispViewWindow").doSearch();	
												},
												'keypress': function(inp, e) {
													if ( e.shiftKey == false && e.getKey() == Ext.EventObject.TAB )
													{
														var grid = Ext.getCmp('PersonDispViewGrid').ViewGridPanel;
														if ( grid.getStore().getCount() > 0 )
														{
															grid.getSelectionModel().selectFirstRow();
															grid.getView().focusRow(0);
															e.stopEvent();
														}
													}
													if ( e.shiftKey == true && e.getKey() == Ext.EventObject.TAB )
													{
														var node = Ext.getCmp('PDVW_filter_tree').getSelectionModel().selNode;
														node.select();
														node.getUI().getAnchor().focus();				
													}
												}
											},
											//region: 'north',
											store: new Ext.data.SimpleStore(
											{
												key: 'view_all_id',
												autoLoad: true,
												fields:
												[
													{name:'view_all_id', type:'int'},
													{name:'view_all_name', type:'string'}
												],
												data : [[1, lang['tolko_aktualnyie']], [2, lang['vklyuchaya_ne_aktualnyie']]]
											}),
											valueField: 'view_all_id',
											width:225
										})
									]
								},


								//---------------------------------------
								{
									layout: 'form',
									border: false,
									labelWidth: 150,
									style: 'padding-left:10px',
									items: [
										{
											hiddenName: 'DispMedStaffFact_id',
											id: 'PDVW_DispMedPersonalCombo',
											lastQuery: '',
											fieldLabel: lang['postavivshiy_vrach'],
											listWidth: 700,
											tabIndex: TABINDEX_PERSDISPSW + 58,
											width: 500,
											xtype: 'swmedstafffactglobalcombo'
										}
									]
								},
								{
									layout: 'form',
									border: false,
									labelWidth: 150,
									style: 'padding-left:10px',
									items: [
										{
											hiddenName: 'HistMedStaffFact_id',
											id: 'PDVW_HistMedPersonalCombo',
											lastQuery: '',
											fieldLabel: 'Ответственный врач',
											listWidth: 700,
											tabIndex: TABINDEX_PERSDISPSW + 58,
											width: 500,
											xtype: 'swmedstafffactglobalcombo',
											listeners: {
												'change': function(combo,value)
												{
													if(!Ext.isEmpty(value) && value > 0)
														form.findById('PDVW_checkMPHistory').enable();
													else
													{
														form.findById('PDVW_checkMPHistory').disable();
														form.findById('PDVW_checkMPHistory').setValue(false);
													}
												}.createDelegate(this)
											}
										}
									]
								},
								{
									layout: 'form',
									border: false,
									labelWidth: 250,
									style: 'padding-left:10px',
									items: [
										{
											fieldLabel: 'Учитывать историю ответственных врачей',
											name: 'checkMPHistory',
											disabled: true,
											id: 'PDVW_checkMPHistory',
											xtype: 'checkbox'
										}
									]
								},
								//---------------------------------------
								
								{
									layout: 'form',
									border: false,
									labelWidth: 100,
									style: 'padding-left:10px',
									items:[
										new sw.Promed.SwBaseLocalCombo({
											displayField: 'view_mp_name',
											enableKeyEvents: true,
											fieldLabel: lang['vrach_yavlyaetsya'],
											hiddenName: 'ViewMp_id',
											hideEmptyRow: true,
											editable:false,
											id: 'PDVW_view_mp_combo',
											listeners: {
												'select': function(c,newRec) {
													if(newRec && newRec.get('view_mp_id') == 3)	{
														var today = new Date();
														this.findById('PDVW_view_mp_onDate').setValue(today);
														this.findById('PDVW_view_mp_onDate').showContainer();
													} else {
														this.findById('PDVW_view_mp_onDate').setValue('');
														this.findById('PDVW_view_mp_onDate').hideContainer();
													}
													Ext.getCmp("PersonDispViewWindow").doSearch();
												}.createDelegate(this),
												'keypress': function(inp, e) {
													if ( e.shiftKey == false && e.getKey() == Ext.EventObject.TAB )
													{
														var grid = Ext.getCmp('PersonDispViewGrid').ViewGridPanel;
														if ( grid.getStore().getCount() > 0 )
														{
															grid.getSelectionModel().selectFirstRow();
															grid.getView().focusRow(0);
															e.stopEvent();
														}
													}
													if ( e.shiftKey == true && e.getKey() == Ext.EventObject.TAB )
													{
														var node = Ext.getCmp('PDVW_filter_tree').getSelectionModel().selNode;
														node.select();
														node.getUI().getAnchor().focus();				
													}
												}
											},
											store: new Ext.data.SimpleStore(
											{
												key: 'view_mp_id',
												autoLoad: true,
												fields:
												[
													{name:'view_mp_id', type:'int'},
													{name:'view_mp_name', type:'string'}
												],
												data : [
													[1, lang['postavivshim_ili_otvetstvennim']], 
													[2, lang['postavivshim']],
													[3, lang['otvetstvennim']]
												]
											}),
											valueField: 'view_mp_id',
											width: 230
										})
									]
								}, {
									layout: 'form',
									border: false,
									style: 'padding-left:10px',
									labelWidth: 60,
									items:[{
										fieldLabel: lang['na_datu'],
										id: 'PDVW_view_mp_onDate',
										width: 100,
										plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
										xtype: 'swdatefield',
										listeners: {
											'change': function() {
												Ext.getCmp("PersonDispViewWindow").doSearch();
											}
										}
									}]
								}]
							}
							
						],
						labelWidth: 150,
						region: 'north',
						tpl: '<tpl for="."><div class="x-combo-list-item">{view_all_name}&nbsp;</div></tpl>'
					}),
					new sw.Promed.ViewFrame(
					{
						actions:
						[
							{name: 'action_add', handler: function() {Ext.getCmp('PersonDispViewWindow').addPersonDisp(); }},
							{name: 'action_edit', handler: function() {Ext.getCmp('PersonDispViewWindow').editPersonDisp(); }},
							{name: 'action_view', handler: function() {Ext.getCmp('PersonDispViewWindow').viewPersonDisp(); }},
							{name: 'action_delete', handler: function() {Ext.getCmp('PersonDispViewWindow').deletePersonDisp(); }},
							{name: 'action_refresh', handler: function() {Ext.getCmp('PersonDispViewWindow').doSearch(); }},
							//{name: 'action_print'}
                            {
                                name: 'action_print',
                                menuConfig: {
                                    printObject: {text: lang['pechat_kontrolnoy_kartyi_disp_nablyudeniya'], handler: function(){ this.doPrintPersonDispCard(); }.createDelegate(this)},
                                    print030: {text: 'Печать формы №030-4/у', name: 'print030', handler: function(){this.print030();}.createDelegate(this)}
                                }
                            }
						],
						autoLoadData: false,
						dataUrl: '/?c=PersonDisp&m=GetListByTree',
						id: 'PersonDispViewGrid',
						focusOn: {name:'PDVW_SearchButton', type:'field'},
						focusPrev: {name:'PDVW_CancelButton', type:'field'},
						pageSize: 100,
						paging: true,
						selectionModel: (this.replacingResponsibleDoctor) ? 'multiselect' : 'row',
						region: 'center',
						root: 'data',
						totalProperty: 'totalCount',
						stringfields:
						[
							{name: 'PersonDisp_id', type: 'int', header: 'ID', key: true},
							{name: 'Person_id', type: 'int', hidden: true},
							{name: 'Server_id', type: 'int', hidden: true},
							{name: 'Person_SurName',  type: 'string', header: lang['familiya'], width: 200},
							{name: 'Person_FirName',  type: 'string', header: lang['imya'], width: 200},
							{name: 'Person_SecName',  type: 'string', header: lang['otchestvo'], width: 200},
							{name: 'Person_BirthDay',  type: 'date', header: lang['data_rojdeniya'], renderer: Ext.util.Format.dateRenderer('d.m.Y')},
							{name: 'Diag_Code',  type: 'string', header: lang['diagnoz']},
							{name: 'PersonDisp_begDate',  type: 'date', header: lang['vzyat'], renderer: Ext.util.Format.dateRenderer('d.m.Y')},
							{name: 'PersonDisp_endDate',  type: 'date', header: lang['snyat'], renderer: Ext.util.Format.dateRenderer('d.m.Y')},
							{name: 'PersonDisp_NextDate',  type: 'date', header: lang['data_sled_yavki'], renderer: Ext.util.Format.dateRenderer('d.m.Y')},
							{name: 'LpuSection_Name',  type: 'string', header: lang['otdelenie']},
							{name: 'MedPersonal_FIO',  type: 'string', header: lang['postavivshiy_vrach'], width: 120},
							{name: 'MedPersonalHist_FIO', type: 'string', header: 'Ответственный врач', width: 120},
                            {name: 'LpuRegion_Name',  type: 'string', header: lang['uchastok']},
							{name: 'Sickness_Name',  type: 'string', header: lang['zabolevanie']},
							{name: 'Is7Noz',  type: 'checkbox', header: lang['7_noz']}
						],
						toolbar: true,
						onRowDeSelect: function() {
							var personDispViewGrid = Ext.getCmp('PersonDispViewGrid');
							if(personDispViewGrid.selectionModel == 'multiselect'){
								var selections = personDispViewGrid.getMultiSelections();
								personDispViewGrid.ViewActions.action_editList.setDisabled( (selections.length==0) );
							}
						},
						onRowSelect: function(sm, index, record) {
							var personDispViewGrid = Ext.getCmp('PersonDispViewGrid');
							if(record.get('Diag_Code') && record.get('Diag_Code').substr(0,3) >= 'A15' && record.get('Diag_Code').substr(0,3) <= 'A19'){
								personDispViewGrid.ViewActions.action_print.menu.print030.setDisabled(false);
							} else {
								personDispViewGrid.ViewActions.action_print.menu.print030.setDisabled(true);
							}
							if(personDispViewGrid.selectionModel == 'multiselect'){
								var selections = personDispViewGrid.getMultiSelections();
								personDispViewGrid.ViewActions.action_editList.setDisabled( (selections.length==0) );
							}
						},
					})
				]})
			]

		});
		sw.Promed.swPersonDispViewWindow.superclass.initComponent.apply(this, arguments);
	},
	keys: [{
		key: Ext.EventObject.INSERT,
		fn: function(e) {Ext.getCmp("PersonDispViewWindow").addPersonDisp();},
		stopEvent: true
	}, {
		alt: true,
		fn: function(inp, e) {
			var current_window = Ext.getCmp('PersonDispViewWindow');
			switch (e.getKey())
			{
				case Ext.EventObject.J:
					current_window.hide();
				break;
				case Ext.EventObject.C:
					current_window.doResetAll();
				break;
			}
		},
		key: [ Ext.EventObject.J, Ext.EventObject.C ],
		stopEvent: true
	}],
	layout: 'border',
	maximizable: true,
	minHeight: 550,
	minWidth: 900,
	modal: false,
	plain: true,
	resizable: true,
	replacingResponsibleDoctor: (getRegionNick() != 'kz' && isUserGroup(['PersonDispHistEdit']) && isUserGroup(['LpuAdmin'])) ? true : false,
	refreshPersonDispViewGrid: function() {
		this.doSearch();
	},
	view_one_doctor: null,
	show: function() {
		sw.Promed.swPersonDispViewWindow.superclass.show.apply(this, arguments);

		/*this.restore();
		this.center();
		this.maximize();*/

		this.viewOnly = false;
		this.ARMType = null;
		if(arguments[0])
		{
			if(arguments[0].viewOnly)
				this.viewOnly = arguments[0].viewOnly;

			if(arguments[0].ARMType){
				this.ARMType = arguments[0].ARMType;
			}
		}

		this.findById('PersonDispViewGrid').setActionDisabled('action_add', this.viewOnly);
		this.findById('PersonDispViewGrid').setActionDisabled('action_edit', this.viewOnly);
		this.findById('PersonDispViewGrid').setActionDisabled('action_delete', this.viewOnly);

		if(this.replacingResponsibleDoctor){
			this.findById('PersonDispViewGrid').addActions(
				{
					name : 'action_editList',
					text: 'Заменить ответственного врача',
					tooltip: 'Заменить ответственного врача',
					disabled: true,
					handler: function() {
						this.replaceResponsibleDoctor();
					}.createDelegate(this),
					iconCls: 'replace16'
				}
			);
		}

		var grid = this.findById('PersonDispViewGrid').ViewGridPanel;
		grid.getStore().removeAll();
		
		Ext.getCmp('PDVW_view_all_combo').setValue(1);
		
		var form = this;

		// загружать всех врачей ЛПУ или только одного врача АРМа
		form.view_one_doctor = (arguments[0] && arguments[0].view_one_doctor);
		// пациент из АРМа полки
		form.Person_id = arguments[0] && arguments[0].Person_id;
		// врач из АРМ полки
		form.MedPersonal_id = getGlobalOptions().medpersonal_id || null;
		if (arguments[0] && arguments[0].MedPersonal_id) {
			form.MedPersonal_id = arguments[0].MedPersonal_id;
		}
		log([form.MedPersonal_id, arguments[0].MedPersonal_id]);

		var root_node_expand_callback = function() {
			if ( form.view_one_doctor )
			{
				var node_medpers = Ext.getCmp('PDVW_filter_tree').getNodeById('razdel_medpersonal');
				if ( node_medpers )
				{
					// Под суперадмином в раздел razdel_medpersonal будут загружены все врачи этого ЛПУ, под врачом - только он сам.
					if ( isAdmin )
						form.view_one_doctor = false;
					node_medpers.expand(false, false, function() {
						var node_doctor = Ext.getCmp('PDVW_filter_tree').getNodeById('MedPersonal_' + form.MedPersonal_id);
						if (node_doctor)
						{
							node_doctor.select();
							node_doctor.getUI().getAnchor().focus();
							node_doctor.fireEvent('beforeclick', node_doctor);
						}
					});
				}
			}
			else
			{
				Ext.getCmp('PDVW_filter_tree').root.firstChild.select();
				Ext.getCmp('PDVW_filter_tree').root.firstChild.getUI().getAnchor().focus();
			}
		};
		/*if ( !this.isNotFirstShow )
		{
			this.currentLpuId = Ext.globalOptions.globals.lpu_id;
			Ext.getCmp('PDVW_filter_tree').focus();		
			Ext.getCmp('PDVW_filter_tree').root.expand(false, false, root_node_expand_callback);
			this.isNotFirstShow = true;
		}
		else
		{
			if ( this.currentLpuId != Ext.globalOptions.globals.lpu_id )
			{
				Ext.getCmp('PDVW_filter_tree').root.setText(Ext.globalOptions.globals.lpu_nick);		
				Ext.getCmp('PDVW_filter_tree').root.getOwnerTree().loader.load(Ext.getCmp('PDVW_filter_tree').root, function() {
					Ext.getCmp('PDVW_filter_tree').root.expand(false, false, root_node_expand_callback);
				});
			}
			else
			{
				Ext.TaskMgr.start({
					run : function() {
						root_node_expand_callback();
						Ext.TaskMgr.stopAll();
					},
					interval : 1000
				});
			}
		}*/
		if ( this.currentLpuId != Ext.globalOptions.globals.lpu_id )
		{
			Ext.getCmp('PDVW_filter_tree').root.setText(Ext.globalOptions.globals.lpu_nick);
			Ext.getCmp('PDVW_filter_tree').root.getOwnerTree().loader.load(Ext.getCmp('PDVW_filter_tree').root, function() {
				Ext.getCmp('PDVW_filter_tree').root.expand(false, false, root_node_expand_callback);
			});
		}
		else
		{
			Ext.TaskMgr.start({
				run : function() {
					root_node_expand_callback();
					Ext.TaskMgr.stopAll();
				},
				interval : 1000
			});
		}
		//this.findById('PDVW_view_mp_combo').disable();
		
		this.findById('PDVW_view_mp_combo').disable();
		this.findById('PDVW_view_mp_combo').hideContainer();
		this.findById('PDVW_view_mp_onDate').hideContainer();
		
		setMedStaffFactGlobalStoreFilter();
		//form.getForm().findField('DispMedStaffFact_id').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));
		//form.getForm().findField('HistMedStaffFact_id').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));
		setMedStaffFactGlobalStoreFilter({
			Lpu_id: getGlobalOptions().lpu_id
		});
		this.findById('PDVW_DispMedPersonalCombo').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));
		this.findById('PDVW_HistMedPersonalCombo').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));
		
		this.findById('PDVW_DispMedPersonalCombo').showContainer();
		this.findById('PDVW_HistMedPersonalCombo').showContainer();
		this.findById('PDVW_checkMPHistory').showContainer();
		this.findById('PDVW_checkMPHistory').disable();
		this.findById('PDVW_checkMPHistory').setValue(false);
		
		/*this.restore();
		this.center();
		this.maximize();*/
		// режим отображения формы
		this.listMode = true;

		this.doResetAll();

		this.setTitle(WND_POL_PERSDISPSEARCHVIEW);
	},
	showMessage: function(title, message, fn) {
		if ( !fn )
			fn = function(){};
		Ext.MessageBox.show({
			buttons: Ext.Msg.OK,
			fn: fn,
			icon: Ext.Msg.WARNING,
			msg: message,
			title: title
		});
	},
	title: WND_POL_PERSDISPSEARCHVIEW,
	viewPersonDisp: function() {
		var grid = this.findById('PersonDispViewGrid').ViewGridPanel;
		var current_row = grid.getSelectionModel().getSelected();

		if ( !current_row ) {
			return false;
		}

		if ( getWnd('swPersonDispEditWindow').isVisible() ) {
			sw.swMsg.alert(lang['soobschenie'], lang['okno_redaktirovaniya_dispansernoy_kartyi_patsienta_uje_otkryito']);
			return false;
		}
		
		var formParams = new Object();
		var params = new Object();
		
		formParams.Person_id = current_row.data.Person_id;
		formParams.PersonDisp_id = current_row.data.PersonDisp_id;
		formParams.Server_id = current_row.data.Server_id;

		if(this.ARMType){
			params.ARMType = this.ARMType;
		}

		params.action = 'view';
		params.callback = function() {
			//this.refreshPersonDispViewGrid();// #139486 не обновлять весь список
		}.createDelegate(this);
		params.formParams = formParams;
		params.onHide = function() {
			//
		}.createDelegate(this);
		
		getWnd('swPersonDispEditWindow').show(params);
	},
	replaceResponsibleDoctor: function(){
		// Заменить ответственного врача
		var personDispArr = [];
		var selections = this.findById('PersonDispViewGrid').getMultiSelections();
		if(selections.length > 0){
			selections.forEach(function(rec) {
				if (rec.get('PersonDisp_id')) {
					personDispArr.push(rec.get('PersonDisp_id'));
				}
			});
			if(personDispArr.length > 0) getWnd('swResponsibleReplacementOptionsDoctorWindow').show({personDispArr: personDispArr});
		}else{
			Ext.Msg.alert('Ошибка','Не выбраны записи для замены ответственного Врача');
		}
	},
	width: 900
});

