/**
* swPersonPrivilegeWOWSearch - окно поиска по регистру ВОВ.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2009-2010 Swan Ltd.
* @author       Марков Андрей
* @version      09.03.2010
* @comment      Префикс для id компонентов ppwows (PersonPrivilegeWOWSearchWindow)
                firstTabIndex: 17100 - начиная с 17100 tabindex полей 
*/

sw.Promed.swPersonPrivilegeWOWSearchWindow = Ext.extend(sw.Promed.BaseForm, {
	border: false,
	buttonAlign: 'left',
	closeAction: 'hide',
	height: 500,
	width: 800,
	id: 'PersonPrivilegeWOWSearchWindow',
	title: (getRegionNick().inlist(['ufa','ekb','penza','astra'])) ? langs('Регистр инвалидов, подлежащих ДВН: Поиск') : langs('Регистр ВОВ: Поиск'),
	layout: 'border',
	maximizable: true,
	maximized: true,
	modal: false,
	plain: true,
	resizable: false,
	firstTabIndex: 17100,
	filterName: 'PersonWOWSearchFilterForm',
	listeners: 
	{
		beforeshow: function()
		{
			//this.findById('ppwowsRightPanel').setVisible(false);
		},
                'resize': function (win, nW, nH, oW, oH) {
//                    log(nW);
                    win.findById('ppwowsSearchFilterTabbar').setWidth(nW - 5);
                    win.findById('PersonWOWSearchFilterForm').setWidth(nW - 5);
                }
	},
	getLoadMask: function()
	{
		if (!this.loadMask)
		{
			this.loadMask = new Ext.LoadMask(Ext.get(this.id), {msg: lang['podojdite']});
		}
		return this.loadMask;
	},
	show: function() 
	{
		sw.Promed.swPersonPrivilegeWOWSearchWindow.superclass.show.apply(this, arguments);
		this.getLoadMask().show();

		var bf = this.findById(this.filterName).getForm();
		
		this.restore();
		this.center();
		this.maximize();
		this.doReset();
		
		bf.getEl().dom.action = "/?c=Search&m=printSearchResults";
		bf.getEl().dom.method = "post";
		bf.getEl().dom.target = "_blank";
		bf.standardSubmit = true;
		this.getLoadMask().hide();
	},
	getFilterForm: function() 
	{
		if ( this.filterForm == undefined ) 
		{
			this.filterForm = this.findById(this.filterName);
		}
		return this.filterForm;
	},
	doReset: function() 
	{
		var win = this;
		var form = win.findById(this.filterName);
		form.getForm().reset();

		if ( form.getForm().findField('AttachLpu_id') != null ) 
		{
			form.getForm().findField('AttachLpu_id').fireEvent('change', form.getForm().findField('AttachLpu_id'), 0, 1);
		}

		if ( form.getForm().findField('LpuRegion_id') != null ) 
		{
			form.getForm().findField('LpuRegion_id').lastQuery = '';
			form.getForm().findField('LpuRegion_id').getStore().clearFilter();
		}

		if ( form.getForm().findField('PrivilegeType_id') != null ) 
		{
			form.getForm().findField('PrivilegeType_id').lastQuery = '';
			form.getForm().findField('PrivilegeType_id').getStore().filterBy(function(record) 
			{
				if ( record.get('PrivilegeType_Code') <= 500 ) 
				{
					return true;
				}
				else 
				{
					return false;
				}
			});
		}

		if ( form.getForm().findField('LpuRegionType_id') != null ) 
		{
			form.getForm().findField('LpuRegionType_id').getStore().clearFilter();
		}

		if ( form.getForm().findField('DirectClass_id') != null ) 
		{
			form.getForm().findField('DirectClass_id').fireEvent('change', form.getForm().findField('DirectClass_id'), null, 1);
		}

		if ( form.getForm().findField('PersonCardStateType_id') != null ) 
		{
			form.getForm().findField('PersonCardStateType_id').fireEvent('change', form.getForm().findField('PersonCardStateType_id'), 1, 0);
		}

		if ( form.getForm().findField('PrivilegeStateType_id') != null ) 
		{
			form.getForm().findField('PrivilegeStateType_id').fireEvent('change', form.getForm().findField('PrivilegeStateType_id'), 1, 0);
		}

		form.findById('ppwowsSearchFilterTabbar').setActiveTab(0);
		form.findById('ppwowsSearchFilterTabbar').getActiveTab().fireEvent('activate', form.findById('ppwowsSearchFilterTabbar').getActiveTab());

		this.SearchGrid.removeAll();
	},
	getRecordsCount: function() 
	{
		var form = this.getFilterForm();
		if (!form.getForm().isValid()) 
		{
			sw.swMsg.alert(lang['poisk'], lang['proverte_pravilnost_zapolneniya_poley_na_forme_poiska']);
			return false;
		}
		var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Подождите, идет подсчет записей..." });
		loadMask.show();
		var post = getAllFormFieldValues(form);
		if ( post.PersonCardStateType_id == null ) 
		{
			post.PersonCardStateType_id = 1;
		}
		if ( post.PrivilegeStateType_id == null ) 
		{
			post.PrivilegeStateType_id = 1;
		}
		Ext.Ajax.request(
		{
			callback: function(options, success, response) 
			{
				loadMask.hide();
				if ( success ) 
				{
					var response_obj = Ext.util.JSON.decode(response.responseText);
					if ( response_obj.Records_Count != undefined ) 
					{
						sw.swMsg.alert(lang['podschet_zapisey'], lang['naydeno_zapisey'] + response_obj.Records_Count);
					}
					else 
					{
						sw.swMsg.alert(lang['podschet_zapisey'], response_obj.Error_Msg);
					}
				}
				else 
				{
					sw.swMsg.alert(lang['oshibka'], lang['pri_podschete_kolichestva_zapisey_proizoshli_oshibki']);
				}
			},
			params: post,
			url: C_SEARCH_RECCNT
		});
	},
    addPersonPrivilegeWOW: function()
    {
        var frm = this;
        if (getWnd('swPersonSearchWindow').isVisible())
        {
            sw.swMsg.alert(lang['soobschenie'], lang['okno_poiska_cheloveka_uje_otkryito']);
            return false;
        }

        getWnd('swPersonSearchWindow').show(
            {
                onClose: function()
                {
                    frm.refreshPersonPrivilegeWOWViewGrid();
                },
                onSelect: function(person_data)
                {
                    getWnd('swPersonSearchWindow').hide();
                    // вместо frm.addPersonDo(person_data); открываем форму Регистр детей-сирот (с 2013г.): Добавление / Редактирование
                    getWnd('swPersonPrivilegeWOWEditWindow').show({
                        action: 'add',
                        //CategoryChildType: frm.CategoryChildType,
                        callback: function() {
                            frm.SearchGrid.runAction('action_refresh');
                        },
                        formParams: {
                           // PersonDispOrp_Year: Ext.getCmp('pdo13swYearCombo').getValue(),
                            Person_id: person_data.Person_id,
                            Server_id: person_data.Server_id
                        }
                    });
                }
                //searchMode: (this.CategoryChildType == 'orpadopted' ? 'att' : 'all')
            });
    },
    editPersonPrivilegeWOW: function() {
        var grid = this.SearchGrid.ViewGridPanel;
        var current_row = grid.getSelectionModel().getSelected();
        if (!current_row)
            return;
        if ((!grid.getSelectionModel().getSelected())||(grid.getStore().getCount()==0))
            return;
        var person_id = grid.getSelectionModel().getSelected().data.Person_id;
        var server_id = grid.getSelectionModel().getSelected().data.Server_id;
        var PersonPrivilegeWOW_id = grid.getSelectionModel().getSelected().data.PersonPrivilegeWOW_id;
        getWnd('swPersonPrivilegeWOWEditWindow').show({
            action: 'edit',
            formParams: {
                Person_id: person_id,
                PersonPrivilegeWOW_id: PersonPrivilegeWOW_id,
                Server_id: server_id
            },
            callback: function(callback_data) {
                grid.getStore().reload();
            },
            onClose: function() {
            }
        });
    },
    viewPersonPrivilegeWOW: function() {
        var grid = this.SearchGrid.ViewGridPanel;
        var current_row = grid.getSelectionModel().getSelected();
        if (!current_row)
            return;
        if ((!grid.getSelectionModel().getSelected())||(grid.getStore().getCount()==0))
            return;
        var person_id = grid.getSelectionModel().getSelected().data.Person_id;
        var server_id = grid.getSelectionModel().getSelected().data.Server_id;
        var PersonPrivilegeWOW_id = grid.getSelectionModel().getSelected().data.PersonPrivilegeWOW_id;
        getWnd('swPersonPrivilegeWOWEditWindow').show({
            action: 'view',
            formParams: {
                Person_id: person_id,
                PersonPrivilegeWOW_id: PersonPrivilegeWOW_id,
                Server_id: server_id
            },
            callback: function(callback_data) {
                grid.getStore().reload();
            },
            onClose: function() {
            }
        });

    },
    deletePersonPrivilegeWOW: function() {
        var current_window = this;
        var grid = current_window.SearchGrid.ViewGridPanel;
        var current_row = grid.getSelectionModel().getSelected();
        if (!current_row)
            return;
        if ( !current_row.get('PersonPrivilegeWOW_id') || current_row.get('PersonPrivilegeWOW_id') == '' )
            return;
        sw.swMsg.show({
            title: lang['podtverjdenie_udaleniya'],
            msg: lang['vyi_deystvitelno_jelaete_udalit_etu_zapis'],
            buttons: Ext.Msg.YESNO,
            fn: function ( buttonId ) {
                if ( buttonId == 'yes' )
                {
                    Ext.Ajax.request({
                        url: '?c=PersonPrivilegeWOW&m=deletePersonPrivilegeWOW',
                        params: {PersonPrivilegeWOW_id: current_row.data.PersonPrivilegeWOW_id},
                        callback: function() {
                            grid.getStore().reload();
                        }
                    });
                }
            }
        });
    },
    refreshPersonPrivilegeWOWViewGrid: function()
    {
        // так как у нас грид не обновляется, то просто ставим фокус в первое поле ввода формы
        if ( this.is_potok )
            this.doStreamInputSearch();
        var panel = this.findById('ppwowsSearchFilterTabbar').getActiveTab();
        var els=panel.findByType('textfield', false);
        if (els==undefined)
            els=panel.findByType('combo', false);
        var el=els[0];
        if (el!=undefined && el.focus)
            el.focus(true, 200);
    },
	doSearch: function(params) 
	{
		if ( params && params['soc_card_id'] )
			var soc_card_id = params['soc_card_id'];
		
		var win = this;
		var form = win.findById(this.filterName);
		
		if ( form.isEmpty() ) {
			sw.swMsg.alert(lang['oshibka'], lang['ne_zapolneno_ni_odno_pole'], function() {
			});
			return false;
		}
		
		var grid = win.SearchGrid.getGrid();

		var loadMask = new Ext.LoadMask(win.getEl(), { msg: "Подождите, идет поиск..." });
		loadMask.show();

		var post = getAllFormFieldValues(form);
		
		if ( soc_card_id )
		{
			var post = {
				soc_card_id: soc_card_id,
				SearchFormType: post.SearchFormType
			};
		}
		
		post.limit = 100;
		post.start = 0;

		grid.getStore().baseParams = post;

		if ( form.getForm().isValid() ) 
		{
			win.SearchGrid.ViewActions.action_refresh.setDisabled(false);
			grid.getStore().removeAll();
			//grid.getStore().baseParams = '';
			grid.getStore().load(
			{
				callback: function(records, options, success) 
				{
					loadMask.hide();
				},
				params: post
			});
		}
	},
	keys: 
	[{
        key: Ext.EventObject.INSERT,
        fn: function(e) {Ext.getCmp('PersonPrivilegeWOWSearchWindow').addPersonPrivilegeWOW();},
        stopEvent: true
    },
	{
		alt: true,
		fn: function(inp, e) 
		{
			var win = Ext.getCmp('PersonPrivilegeWOWSearchWindow');
			var form = win.findById(this.filterName);
			var tabsf = win.findById('ppwowsSearchFilterTabbar');

			switch (e.getKey()) 
			{
				case Ext.EventObject.C:
					current_window.doReset();
				break;

				case Ext.EventObject.J:
					current_window.hide();
				break;

				case Ext.EventObject.NUM_ONE:
				case Ext.EventObject.ONE:
					tabsf.setActiveTab(0);
				break;

				case Ext.EventObject.NUM_TWO:
				case Ext.EventObject.TWO:
					tabsf.setActiveTab(1);
				break;

				case Ext.EventObject.NUM_THREE:
				case Ext.EventObject.THREE:
					tabsf.setActiveTab(2);
				break;

				case Ext.EventObject.NUM_FOUR:
				case Ext.EventObject.FOUR:
					tabsf.setActiveTab(3);
				break;

				case Ext.EventObject.NUM_FIVE:
				case Ext.EventObject.FIVE:
					tabsf.setActiveTab(4);
				break;

				case Ext.EventObject.NUM_SIX:
				case Ext.EventObject.SIX:
					tabsf.setActiveTab(5);
				break;

				case Ext.EventObject.NUM_SEVEN:
				case Ext.EventObject.SEVEN:
					tabsf.setActiveTab(6);
				break;

				case Ext.EventObject.NUM_EIGHT:
				case Ext.EventObject.EIGHT:
					tabsf.setActiveTab(7);
				break;

				case Ext.EventObject.NUM_NINE:
				case Ext.EventObject.NINE:
					tabsf.setActiveTab(8);
				break;
			}
		},
		key: 
		[
			Ext.EventObject.C,
			Ext.EventObject.EIGHT,
			Ext.EventObject.FIVE,
			Ext.EventObject.FOUR,
			Ext.EventObject.J,
			Ext.EventObject.NINE,
			Ext.EventObject.NUM_EIGHT,
			Ext.EventObject.NUM_FIVE,
			Ext.EventObject.NUM_FOUR,
			Ext.EventObject.NUM_NINE,
			Ext.EventObject.NUM_ONE,
			Ext.EventObject.NUM_SEVEN,
			Ext.EventObject.NUM_SIX,
			Ext.EventObject.NUM_TWO,
			Ext.EventObject.NUM_THREE,
			Ext.EventObject.ONE,
			Ext.EventObject.SEVEN,
			Ext.EventObject.SIX,
			Ext.EventObject.TWO,
			Ext.EventObject.THREE
		],
		stopEvent: true
	}],
	getButtonSearch: function() {
		// TODO: правильно юзать scope кнопки
		return Ext.getCmp('PPWSW_SearchButton');
	},
	initComponent: function() 
	{
		var form = this;
		
		this.SearchFiltersFrame = getBaseSearchFiltersFrame(
		{
			id: this.filterName,
			region: 'north',
			ownerWindow: this,
			searchFormType: 'PersonPrivilegeWOW',
			tabIndexBase: this.firstTabIndex,
			tabPanelId: 'ppwowsSearchFilterTabbar',
			tabs: ((!getRegionNick().inlist(['ufa','ekb','penza','astra']))?
			[{
				autoHeight: true,
				bodyStyle: 'margin-top: 5px;',
				border: false,
				layout: 'form',
				listeners:
				{
					'activate': function(panel)
					{
						this.getFilterForm().getForm().findField('PrivilegeTypeWow_id').focus(250, true);
					}.createDelegate(this)
				},
				title: langs('<u>6</u>. Регистр инвалидов, подлежащих ДВН'),
				items:
				[{
					fieldLabel: lang['kategoriya'],
					id: 'ppwowsPrivilegeTypeWow_id',
					tabIndex: this.firstTabIndex + 72,
					width: 550,
					xtype: 'swprivilegetypewowcombo'
				}]
			}]:[])
		});
		
		this.SearchGrid = new sw.Promed.ViewFrame(
		{
			id: form.id+'SearchGrid',
			region: 'center',
			height: 203,
			title:langs('Регистр инвалидов, подлежащих ДВН: Список'),
			object: '',
            editformclassname: 'PersonPrivilegeWOWEditWindow',
			dataUrl: '/?c=Search&m=searchData',
			paging: true,
			root: 'data',
			totalProperty: 'totalCount',
			autoLoadData: false,
			stringfields:
			[
				{ name: 'PersonPrivilegeWOW_id', type: 'int', header: 'ID', key: true },
				{ name: 'Person_id', type: 'int', hidden: true },
				{ name: 'PersonEvn_id', type: 'int', hidden: true },
				{ name: 'Server_id', type: 'int', hidden: true },
				{ id: 'autoexpand',  name: 'Person_Surname', type: 'string', header: langs('Фамилия'), width: 150 },
				{ name: 'Person_Firname', type: 'string', header: langs('Имя'), width: 150 },
				{ name: 'Person_Secname', type: 'string', header: langs('Отчество'), width: 150 },
				{ name: 'Person_Birthday', type: 'date', format: 'd.m.Y', header: langs('Д/р') },
				{ name: 'ua_name', type: 'string', header: langs('Адрес регистрации')},
				{ name: 'pa_name', type: 'string', header: langs('Адрес проживания')},
				{ name: 'PrivilegeTypeWOW_Name', header: langs('Категория'), width: 350,  hidden: (getRegionNick().inlist(['ufa','ekb','penza','astra'])) }
				
			],
			actions:
			[
                {name:'action_add', disabled: !(isSuperAdmin() || getRegionNick().inlist(['ufa','ekb','penza','astra'])), handler: function() {this.addPersonPrivilegeWOW();}.createDelegate(this)},
                {name:'action_edit', disabled: !(isSuperAdmin() || getRegionNick().inlist(['ufa','ekb','penza','astra'])), handler: function() {this.editPersonPrivilegeWOW();}.createDelegate(this)},
                {name:'action_view', disabled: !(isSuperAdmin() || getRegionNick().inlist(['ufa','ekb','penza','astra'])), handler: function() {this.viewPersonPrivilegeWOW();}.createDelegate(this)},
                {name:'action_delete', disabled: !(isSuperAdmin() || getRegionNick().inlist(['ufa','ekb','penza','astra'])), handler: function() {this.deletePersonPrivilegeWOW();}.createDelegate(this)},
                {name: 'action_refresh', disabled: !(isSuperAdmin() || getRegionNick().inlist(['ufa','ekb','penza','astra'])), handler: function() {this.refreshPersonPrivilegeWOWViewGrid();}.createDelegate(this)}
			],
			afterSaveEditForm: function(RegistryQueue_id, records)
			{
				var form = Ext.getCmp('PersonPrivilegeWOWSearchWindow');
			},
			onBeforeLoadData: function() {
				this.getButtonSearch().disable();
			}.createDelegate(this),
			onLoadData: function() {
				this.getButtonSearch().enable();
			}.createDelegate(this),
			onRowSelect: function(sm,index,record)
			{
				//log(this.id+'.onRowSelect');
				var form = Ext.getCmp('PersonPrivilegeWOWSearchWindow');
			}
		});
		/*
		this.SearchGrid.ViewGridPanel.view = new Ext.grid.GridView(
		{
			getRowClass : function (row, index)
			{
				var cls = '';
				if (row.get('Registry_IsActive')==2)
					cls = cls+'x-grid-rowselect ';
				if (row.get('Registry_IsProgress')==1)
					cls = cls+'x-grid-rowgray ';
				if (cls.length == 0)
					cls = 'x-grid-panel'; 
				return cls;
			}
		});
		var RegTplMark = 
		[
			'<div style="padding:4px;font-weight:bold;">Реестр № {Registry_Num}</div>'+
			'<div style="padding:4px;">Дата формирования: {Registry_accDate}</div>'+
			'<div style="padding:4px;">Дата начала периода: {Registry_begDate}</div>'+
			'<div style="padding:4px;">Дата окончания периода: {Registry_endDate}</div>'+
			'<div style="padding:4px;">Количество записей в реестре: {Registry_Count}</div>'+
			'<div style="padding:4px;">Сумма к оплате: {Registry_Sum}</div>'
		];
		this.RegistryTpl = new Ext.Template(RegTplMark);
		*/
		Ext.apply(this, 
		{
			layout:'border',
			defaults: {split: true},
			buttons: 
			[{
				handler: function() 
				{
					this.doSearch();
				}.createDelegate(this),
				id: 'PPWSW_SearchButton',
				iconCls: 'search16',
				tabIndex: this.firstTabIndex + 109,
				text: BTN_FRMSEARCH
			}, 
			{
				handler: function() 
				{
					this.doReset();
				}.createDelegate(this),
				iconCls: 'resetsearch16',
				tabIndex: this.firstTabIndex + 110,
				text: BTN_FRMRESET
			},
			/*{
				handler: function() 
				{
					this.getFilterForm().getForm().submit();
				}.createDelegate(this),
				iconCls: 'print16',
				tabIndex: this.firstTabIndex + 111,
				text: lang['pechat_spiska']
			},*/
			{
				handler: function() 
				{
					this.getRecordsCount();
				}.createDelegate(this),
				// iconCls: 'resetsearch16',
				tabIndex: this.firstTabIndex + 112,
				text: BTN_FRMCOUNT
			},
			{
				text: '-'
			},
			HelpButton(this),
			{
				handler: function() 
				{
					this.ownerCt.hide()
				},
				iconCls: 'close16',
				text: BTN_FRMCLOSE
			}],
			items: 
			[form.SearchFiltersFrame,
			{
				border: false,
				xtype: 'panel',
				region: 'center',
				layout:'border',
				id: 'ppwowsRightPanel',
				defaults: {split: true},
				items: [form.SearchGrid]
			}]
		});
		sw.Promed.swPersonPrivilegeWOWSearchWindow.superclass.initComponent.apply(this, arguments);
	}
});