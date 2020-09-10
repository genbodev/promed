/**
* АРМ Специалиста МИРС
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @autor		
* @copyright    Copyright (c) 2017 Swan Ltd.
*/
sw.Promed.swWorkPlaceCommunicWindow = Ext.extend(sw.Promed.swWorkPlaceWindow, {
	id: 'swWorkPlaceCommunicWindow',
	show: function() {
		var wnd = this;
		sw.Promed.swWorkPlaceCommunicWindow.superclass.show.apply(this, arguments);
		if (this.LeftPanel.actions.action_Report) {
			this.LeftPanel.actions.action_Report.setHidden(true);
		}
		this.LeftPanel.collapse();
	},
	doSearch: function() {
		var form = this.FilterPanel.getForm();
		var grid = this.GridPanel;
		params = form.getValues();
		if(Ext.isEmpty(params.Person_id))
		{
			Ext.Msg.alert(lang['oshibka'], 'Необходимо выбрать человека');
			form.findField('Person_id').focus();
			return false;
		}
		if(Ext.isEmpty(params.MedCareCasesDate_Range))
		{
			Ext.Msg.alert(lang['oshibka'], 'Необходимо выбрать период');
			form.findField('MedCareCasesDate_Range').focus();
			return false;
		}
		grid.loadData({globalFilters:params});
	},
	doReset: function() {
		this.FilterPanel.getForm().reset();
		var grid = this.GridPanel;
		grid.removeAll();
	},
	initComponent: function() {
		var form = this;
		this.gridPanelAutoLoad = false;
		this.showToolbar = false;
		this.buttonPanelActions = {
			/*action_Search: {
				nn: 'action_Search',
				tooltip: lang['poisk_cheloveka'],
				text: lang['poisk_cheloveka'],
				iconCls : 'patient-search32',
				disabled: false,
				handler: function(){
					getWnd('swPersonSearchWindow').show({
						onSelect: function(person_data) {
							var Person_id = person_data.Person_id;
							getWnd('swPersonSearchWindow').hide();
							getWnd('swMedicalCareCasesViewWindow').show({
								Person_id: Person_id
							});
						},
						searchMode: 'all',
						viewOnly: true
					});
				}
			}*/
		};
		this.FilterPanel = new sw.Promed.BaseWorkPlaceFilterPanel({
			owner: form,
			hidden: false,
			collapsed: false,
				collapsible: false,
			filter: {
				collapsed: false,
				collapsible: false,
				title: lang['filtr'],
				layout: 'form',
				items: [
				{
					editable: false,
					hiddenName: 'Person_id',
					tabIndex: TABINDEX_PEF + 29,
					width: 400,
					xtype: 'swpersoncombo',
					fieldLabel: 'ФИО пациента',
					onTrigger1Click: function() {
						var ownerWindow = Ext.getCmp('PersonEditWindow');
						var combo = this;

						var
							autoSearch = false,
							fio = new Array();

						if ( !Ext.isEmpty(combo.getRawValue()) ) {
							fio = combo.getRawValue().split(' ');
							if ( !Ext.isEmpty(fio[0]) && !Ext.isEmpty(fio[1]) ) {
								autoSearch = true;
							}
						}

						getWnd('swPersonSearchWindow').show({
							autoSearch: autoSearch,
							viewOnly: true,
							onSelect: function(personData) {
								if ( personData.Person_id > 0 )
								{
									PersonSurName_SurName = Ext.isEmpty(personData.PersonSurName_SurName)?'':personData.PersonSurName_SurName;
									PersonFirName_FirName = Ext.isEmpty(personData.PersonFirName_FirName)?'':personData.PersonFirName_FirName;
									PersonSecName_SecName = Ext.isEmpty(personData.PersonSecName_SecName)?'':personData.PersonSecName_SecName;
									
									combo.getStore().loadData([{
										Person_id: personData.Person_id,
										Person_Fio: PersonSurName_SurName + ' ' + PersonFirName_FirName + ' ' + PersonSecName_SecName
									}]);
									combo.setValue(personData.Person_id);
									combo.collapse();
									combo.focus(true, 500);
									combo.fireEvent('change', combo);
								}
								getWnd('swPersonSearchWindow').hide();
							},
							onClose: function() {combo.focus(true, 500)},
							personSurname: !Ext.isEmpty(fio[0]) ? fio[0] : '',
							personFirname: !Ext.isEmpty(fio[1]) ? fio[1] : '',
							personSecname: !Ext.isEmpty(fio[2]) ? fio[2] : ''
						});
					},
					enableKeyEvents: true,
					listeners: {
						'change': function(combo) {
						},
						'keydown': function( inp, e ) {
							if ( e.F4 == e.getKey() )
							{
								if ( e.browserEvent.stopPropagation )
									e.browserEvent.stopPropagation();
								else
									e.browserEvent.cancelBubble = true;
								if ( e.browserEvent.preventDefault )
									e.browserEvent.preventDefault();
								else
									e.browserEvent.returnValue = false;
								e.browserEvent.returnValue = false;
								e.returnValue = false;
								if ( Ext.isIE )
								{
									e.browserEvent.keyCode = 0;
									e.browserEvent.which = 0;
								}
								inp.onTrigger1Click();
								return false;
							}
						},
						'keyup': function(inp, e) {
							if ( e.F4 == e.getKey() )
							{
								if ( e.browserEvent.stopPropagation )
									e.browserEvent.stopPropagation();
								else
									e.browserEvent.cancelBubble = true;
								if ( e.browserEvent.preventDefault )
									e.browserEvent.preventDefault();
								else
									e.browserEvent.returnValue = false;
								e.browserEvent.returnValue = false;
								e.returnValue = false;
								if ( Ext.isIE )
								{
									e.browserEvent.keyCode = 0;
									e.browserEvent.which = 0;
								}
								return false;
							}
						}
					}
				},
				{
					name : "MedCareCasesDate_Range",
					xtype : "daterangefield",
					width : 170,
					fieldLabel : lang['period'],
					plugins: [new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)]
				},
				{
					layout: 'column',
					style: 'padding: 3px;',
					items: [
						{
							layout: 'form',
							items: [
								{
									handler: function() {
										this.doSearch();
									}.createDelegate(this),
									xtype: 'button',
									iconCls: 'search16',
									text: BTN_FRMSEARCH
								}
							]
						}, {
							layout: 'form',
							style: 'margin-left: 5px;',
							items: [
								{
									handler: function() {
										this.doReset();
									}.createDelegate(this),
									xtype: 'button',
									iconCls: 'resetsearch16',
									text: lang['sbros']
								}
							]
						}
					]
				}
				]
			}
		});
		this.GridPanel = new sw.Promed.ViewFrame({
			region: 'center',
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 250,
			autoLoadData: false,
			dataUrl: '/?c=Common&m=loadMedicalCareCases',
			actions:
			[
				{name:'action_add', hidden: true },
				{name:'action_edit', hidden: true },
				{name:'action_view', hidden: true},
				{name:'action_delete', hidden: true}
			],
			paging: false,
			root: 'data',
			region: 'center',
			stringfields: [
				{ header: 'Evn_id', type: 'int', name: 'Evn_id', key: true },
				{ header: 'Дата и время события',  type: 'string', name: 'Evn_Date', width: 200},
				{ header: 'Дата и время предстоящего приема',  type: 'string', name: 'Evn_FutDate', width: 300},
				{ header: 'Цель',  type: 'string', name: 'Evn_Type', width: 300},
				{ header: 'МО', type: 'string', name: 'Evn_MO_Link', width: 450},
				{ header: 'Врач',  type: 'string', name: 'Evn_Doctor_Link', width: 250 },
				{ header: 'Направление',  type: 'string', name: 'Evn_Direction'},
				{ header: 'Способ записи',  type: 'string', name: 'Evn_RecType'}
			],
			toolbar: false
		});
		sw.Promed.swWorkPlaceCommunicWindow.superclass.initComponent.apply(this, arguments);
	}
});