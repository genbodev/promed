/**
* swInetPersonModerationWindow - окно редактирования расписания врача поликлиники
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Reg
* @access       public
* @copyright    Copyright (c) 2009-2011 Swan Ltd.
* @author       Dmitry Vlasenko
* @version      09.04.2013
*/

sw.Promed.swInetPersonModerationWindow = Ext.extend(sw.Promed.BaseForm, {
	buttonAlign: 'left',
	closable: true,
	closeAction: 'hide',
	draggable: true,
	maximized: true,
	id: 'InetPersonModerationWindow',
	title: WND_REG_INETPERSONMODERATIONWINDOW,
	doSearch: function() {
		var base_form = this.filtersPanel.getForm();
		var filters = base_form.getValues();
		filters.start = 0;
		filters.limit = 100;
		
		this.PersonGrid.loadData({ globalFilters: filters });
	},
	doResetFilters: function() {
		this.filtersPanel.getForm().reset();
	},
	moderateInetPerson: function() {
		var params = new Object();
		var wnd = this;
		var grid = this.PersonGrid.ViewGridPanel;
		var rec = grid.getSelectionModel().getSelected();
		if (!rec || !rec.get('Person_id')) {
			return false;
		}
		params.Person_id = rec.get('Person_id');
		params.onHide = function() {
			// обновить грид
			wnd.doSearch();
		};
		
		getWnd('swInetPersonModerationEditWindow').show(params);
	},
    initComponent: function() {
		var wnd = this;
		
		this.filtersPanel = getBaseFiltersFrame({
			ownerWindow: this,
			items: [{
				border: false,
				layout: 'column',
				anchor: '-10',
				items: [{
					layout: 'form',
					width: 250,
					labelWidth: 100,
					border: false,
					items: [{
						name: 'Person_Surname',
						fieldLabel: lang['familiya'],
						tabIndex: TABINDEX_IPMW + 1,
						anchor: '-10',
						xtype: 'textfield'
					}, {
						name: 'Polis_Ser',
						fieldLabel: lang['seriya_polisa'],
						tabIndex: TABINDEX_IPMW + 2,
						anchor: '-10',
						xtype: 'textfield'
					}]
				}, {
					layout: 'form',
					width: 250,
					labelWidth: 100,
					border: false,
					items: [{
						name: 'Person_Firname',
						fieldLabel: lang['imya'],
						tabIndex: TABINDEX_IPMW + 11,
						anchor: '-10',
						xtype: 'textfield'
					}, {
						name: 'Polis_Num',
						fieldLabel: lang['nomer_polisa'],
						tabIndex: TABINDEX_IPMW + 12,
						anchor: '-10',
						xtype: 'textfield'
					}]
				}, {
					layout: 'form',
					width: 250,
					labelWidth: 80,
					border: false,
					items: [{
						name: 'Person_Secname',
						fieldLabel: lang['otchestvo'],
						tabIndex: TABINDEX_IPMW + 21,
						anchor: '-10',
						xtype: 'textfield'
					}, {
						name: 'Person_Phone',
						fieldLabel: lang['telefon'],
						tabIndex: TABINDEX_IPMW + 22,
						anchor: '-10',
						xtype: 'textfield'
					}]
				}, {
					layout: 'form',
					width: 250,
					labelWidth: 80,
					border: false,
					items: [{
						hiddenName: 'ModerateType_id',
						fieldLabel: langs('Модерация'),
						tabIndex: TABINDEX_IPMW + 23,
						anchor: '-10',
						displayField: 'Moderate_Name',
						valueField: 'Moderate_id',
						value: 3,
						editable: false,
						mode: 'local',
						triggerAction: 'all',
						store: new Ext.data.SimpleStore({
							fields: [
								{name: 'Moderate_id', type: 'int'},
								{name: 'Moderate_Name', type: 'string'}
							],
							data: [
								[1, 'все'], 
								[2, 'проведена'],
								[3, 'не проведена']
							]
						}),
						xtype: 'swcombo'
				}]
			}]
			}]
		});
		
		this.PersonGrid = new sw.Promed.ViewFrame(
		{
			id: wnd.id+'PersonGrid',
			title:'',
			object: 'Person',
			dataUrl: '/?c=InetPerson&m=loadInetPersonGrid',
			autoLoadData: false,
			// selectionModel: 'multiselect',
			region: 'center',
			toolbar: true,
			paging: true,
			root: 'data',
			totalProperty: 'totalCount',
			stringfields:
			[
				{name: 'Person_id', type: 'int', header: 'Person_id', key: true, hidden: true},
				{name: 'username', header: langs('Аккаунт'), width: 100},
				{name: 'Person_Surname', header: langs('Фамилия'), width: 200},
				{name: 'Person_Firname', header: langs('Имя'), width: 100},
				{name: 'Person_Secname', header: langs('Отчество'), width: 150},
				{name: 'Person_BirthDate', type:'date', header: langs('Дата рождения'), width: 80},
				{name: 'Polis_Ser', header: langs('Серия полиса'), width: 100},
				{name: 'Polis_Num', header: langs('Номер полиса'), width: 100},
				{name: 'Address_Address', header: langs('Адрес'), width: 60, id: 'autoexpand'},
				{name: 'Person_Phone', header: langs('Телефон'), width: 80},
				{name: 'Person_insDT', type:'date', header: langs('Добавлен'), width: 80},
				{name: 'Person_IsModerated', header: langs('Модерация'),
					renderer: function(value, p, row) {
						if(!Ext.isEmpty(row.get('Person_id'))) {
							if(Ext.isEmpty(value)) {
								return 'не проведена';
							} else {
								return 'проведена';
							}
						}
						return '';
					}
				}
			],
			onDblClick: function() {
				wnd.moderateInetPerson();
			},
			actions:
			[
				{name:'action_add', disabled: true},
				{name:'action_edit', text: lang['moderirovat'], disabled: false, handler: function() {
					wnd.moderateInetPerson();
				}},
				{name:'action_print', disabled: false},
				{name:'action_view', disabled: true},
				{name:'action_delete', disabled: true}
			]
		});
		
		this.formPanel = new Ext.Panel(
		{
			region: 'center',
			labelAlign: 'right',
			layout: 'border',
			labelWidth: 50,
			border: false,
			items:
			[
				this.filtersPanel,
				this.PersonGrid
			]
		});

	    Ext.apply(this, {
			border: false,
			layout: 'border',
			items: [
				wnd.formPanel
			],
			buttons: [{
				text: BTN_FIND,
				tabIndex: TABINDEX_IPMW + 90,
				handler: function() {
					wnd.doSearch();
				},
				iconCls: 'search16'
			}, 
			{
				text: BTN_RESETFILTER,
				tabIndex: TABINDEX_IPMW + 91,
				handler: function() {
					wnd.doResetFilters();
					wnd.doSearch();
				},
				iconCls: 'resetsearch16'
			}, {
				text: '-'
			},
			HelpButton(this, TABINDEX_IPMW + 92),
			{
				iconCls: 'close16',
				tabIndex: TABINDEX_IPMW + 93,
				onTabAction: function()
				{
					wnd.filtersPanel.getForm().findField('Person_SurName').focus();
				},
				handler: function() {
					wnd.hide();
				},
				text: BTN_FRMCLOSE
			}]
	    });
	    sw.Promed.swInetPersonModerationWindow.superclass.initComponent.apply(this, arguments);
    },
	
    show: function () {
    	sw.Promed.swInetPersonModerationWindow.superclass.show.apply(this, arguments);
		
		this.doSearch();
    }
});
