/**
* swEvnPLWOWSearch - окно поиска талонов углубленных обследований ВОВ.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2009-2010 Swan Ltd.
* @author       Марков Андрей
* @version      12.03.2010
* @comment      Префикс для id компонентов evnplwsw (EvnPLWOWSearchWindow)
                firstTabIndex: 17200 - начиная с 17200 tabindex полей 
*/

sw.Promed.EvnPLWOWSearchWindow = Ext.extend(sw.Promed.BaseForm, {
	border: false,
	buttonAlign: 'left',
	closeAction: 'hide',
	height: 500,
	width: 800,
	id: 'EvnPLWOWSearchWindow',
	title: lang['obsledovaniya_vov_poisk'], 
	layout: 'border',
	maximizable: true,
	maximized: true,
	modal: false,
	plain: true,
	resizable: false,
	firstTabIndex: 17200,
	filterName: 'EvnPLWOWSearchFilterForm',
	listeners: 
	{
		beforeshow: function()
		{
			//
		},
                'resize': function (win, nW, nH, oW, oH) {
//                    log(nW);
                    win.findById('evnplwswSearchFilterTabbar').setWidth(nW - 5);
                    win.findById('EvnPLWOWSearchFilterForm').setWidth(nW - 5);
                }
	},
	keys: 
	[{
		fn: function(inp, e)
		{
			var win = Ext.getCmp('EvnPLWOWSearchWindow');
			switch (e.getKey())
			{
				case Ext.EventObject.INSERT:
					win.addEvnPLWOW();
					break;
			}
		},
		key: [Ext.EventObject.INSERT],
		stopEvent: true
	}],
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
		sw.Promed.EvnPLWOWSearchWindow.superclass.show.apply(this, arguments);
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

		form.findById('evnplwswSearchFilterTabbar').setActiveTab(0);
		form.findById('evnplwswSearchFilterTabbar').getActiveTab().fireEvent('activate', form.findById('evnplwswSearchFilterTabbar').getActiveTab());

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
	onOpenForm: function(person_data)
	{
		this.SearchGrid.setParam('Person_id',  person_data.Person_id, false);
		this.SearchGrid.setParam('PersonEvn_id',  person_data.PersonEvn_id, false);
		this.SearchGrid.setParam('Server_id',  person_data.Server_id, false);
		getWnd('swPersonSearchWindow').hide();
		this.SearchGrid.run_function_add = false;
		this.SearchGrid.runAction('action_add');
		this.getLoadMask().hide();
	},
	onCheckPerson: function (person_data)
	{
		var form = this;
		form.getLoadMask().show();
		Ext.Ajax.request(
		{
			url: '/?c=EvnPLWOW&m=checkDoublePerson',
			params: 
			{
				Person_id: person_data.Person_id
			},
			callback: function(options, success, response) 
			{
				if (success)
				{
					var result = Ext.util.JSON.decode(response.responseText);
					if (result.Error_Msg)
					{
						if (result.success==true)
						{
							sw.swMsg.show(
							{
								buttons: Ext.Msg.OK,
								fn: function() 
								{
									form.onOpenForm(person_data);
								},
								icon: Ext.Msg.WARNING,
								title: lang['vnimanie'],
								msg: result.Error_Msg
							});
						}
						else
						{
							sw.swMsg.show(
							{
								buttons: Ext.Msg.OK,
								icon: Ext.Msg.ERROR,
								title: lang['oshibka'],
								msg: result.Error_Msg
							});
							form.getLoadMask().hide();
						}
					}
					else 
					{
						form.onOpenForm(person_data);
					}
				}
				else 
				{
					this.getLoadMask().hide();
				}
			}
		});
	},
	addEvnPLWOW: function()
	{
		return true;
		var win = this;
		getWnd('swPersonSearchWindow').show(
		{
			onClose: function() 
			{
				if (win.SearchGrid.getGrid().getSelectionModel().getSelected()) 
				{
					win.SearchGrid.getGrid().getView().focusRow(win.SearchGrid.getGrid().getStore().indexOf(win.SearchGrid.getGrid().getSelectionModel().getSelected()));
				}
				else 
				{
					win.SearchGrid.focus();
				}
			}.createDelegate(this),
			onSelect: function(person_data) 
			{
				win.onCheckPerson(person_data);
			},
			searchMode: 'wow'
		});
	},
	searchInProgress: false,
	doSearch: function(params)	{
		if (this.searchInProgress) {
			log(lang['poisk_uje_vyipolnyaetsya']);
			return false;
		} else {
			this.searchInProgress = true;
		}
		var thisWindow = this;
		if ( params && params['soc_card_id'] )
			var soc_card_id = params['soc_card_id'];
		var win = this;
		var form = win.findById(this.filterName);
		
		if ( form.isEmpty() ) {
			sw.swMsg.alert(lang['oshibka'], lang['ne_zapolneno_ni_odno_pole'], function() {
			});
			thisWindow.searchInProgress = false;
			return false;
		}

		if ( !form.getForm().isValid() ) {
			this.searchInProgress = false;
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					//
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		var grid = win.SearchGrid.getGrid();

		if ( form.getForm().findField('PersonPeriodicType_id').getValue() == 2 && (typeof params != 'object' || !params.ignorePersonPeriodicType ) ) {
			sw.swMsg.show({
				buttons: Ext.Msg.YESNO,
				fn: function(buttonId, text, obj) {
					if ( buttonId == 'yes' ) {
						thisWindow.searchInProgress = false;
						this.doSearch({
							ignorePersonPeriodicType: true
						});
					}
				}.createDelegate(this),
				icon: Ext.MessageBox.QUESTION,
				msg: lang['vyibran_tip_poiska_cheloveka_po_sostoyaniyu_na_moment_sluchaya_pri_vyibrannom_variante_poisk_rabotaet_znachitelno_medlennee_hotite_prodoljit_poisk'],
				title: lang['preduprejdenie']
			});
			thisWindow.searchInProgress = false;
			return false;
		}

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
					thisWindow.searchInProgress = false;
					loadMask.hide();
				},
				params: post
			});
		}
	},
	keys: 
	[{
		fn: function(inp, e) 
		{
			// Если вдруг надо будет добавить форму добавления 
			/*
			var win = Ext.getCmp('EvnPLWOWSearchWindow');
			switch ( e.getKey() ) 
			{
				case Ext.EventObject.INSERT:
					//win.Добавить() ))
					break;
			}
			*/
		},
		key: [Ext.EventObject.INSERT],
		stopEvent: true
	}, 
	{
		alt: true,
		fn: function(inp, e) 
		{
			var win = Ext.getCmp('EvnPLWOWSearchWindow');
			var form = win.findById(this.filterName);
			var tabsf = win.findById('evnplwswSearchFilterTabbar');

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
		return Ext.getCmp('EPLWOW_SearchButton');
	},
	initComponent: function() 
	{
		var form = this;
		
		this.SearchFiltersFrame = getBaseSearchFiltersFrame(
		{
			allowPersonPeriodicSelect: true,
			id: this.filterName,
			region: 'north',
			ownerWindow: this,
			searchFormType: 'EvnPLWOW',
			tabIndexBase: this.firstTabIndex,
			tabPanelId: 'evnplwswSearchFilterTabbar',
			tabs: 
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
				title: lang['6_registr_vov'],
				items: 
				[{
					fieldLabel: lang['kategoriya'],
					id: 'evnplwswPrivilegeTypeWow_id',
					tabIndex: this.firstTabIndex + 72,
					width: 550,
					xtype: 'swprivilegetypewowcombo'
				},
					{
						fieldLabel: lang['diapazon_dat_obsledovaniy'],
						name: 'EvnPLWOW_setDate_Range',
						plugins: [
							new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)
						],
						width: 170,
						xtype: 'daterangefield'
					}]
			}]
		});
		
		this.SearchGrid = new sw.Promed.ViewFrame(
		{
			id: form.id+'SearchGrid',
			region: 'center',
			height: 203,
			title:lang['obsledovaniya_vov_spisok'],
			object: 'EvnPLWOW',
			editformclassname: 'EvnPLWOWEditWindow',
			dataUrl: '/?c=Search&m=searchData',
			paging: true,
			root: 'data',
			totalProperty: 'totalCount',
			autoLoadData: false,
			stringfields:
			[
				{ name: 'EvnPLWOW_id', type: 'int', header: 'ID', key: true },
				{ name: 'Person_id', type: 'int', hidden: true, isparams: true },
				{ name: 'PersonEvn_id', type: 'int', hidden: true, isparams: true },
				{ name: 'Server_id', type: 'int', hidden: true, isparams: true },
				{ id: 'autoexpand',  name: 'Person_Surname', type: 'string', header: lang['familiya'], width: 120 },
				{ name: 'Person_Firname', type: 'string', header: lang['imya'], width: 100 },
				{ name: 'Person_Secname', type: 'string', header: lang['otchestvo'], width: 100 },
				{ name: 'Person_Birthday', type: 'date', format: 'd.m.Y', header: lang['d_r'] },
				{ name: 'PrivilegeTypeWOW_Name', header: lang['kategoriya'], width: 250 },
				{ name: 'EvnPLWOW_setDate', type: 'date', format: 'd.m.Y', header: lang['data_nachala'] },
				{ name: 'EvnPLWOW_disDate', type: 'date', format: 'd.m.Y', header: lang['data_okonchaniya'] },
				{ name: 'EvnPLWOW_VizitCount', type: 'int', header: lang['posescheniy'] },
				{ name: 'EvnPLWOW_IsFinish', type: 'string', header: lang['zakonch'], width:50 }
				
			],
			actions:
			[
				{name:'action_add', disabled: true, func: function() {this.addEvnPLWOW();}.createDelegate(this)},
				{name:'action_edit', disabled: true},
				{name:'action_view', disabled: true},
				{name:'action_delete', disabled: true}
			],
			afterSaveEditForm: function(RegistryQueue_id, records)
			{
				var form = Ext.getCmp('EvnPLWOWSearchWindow');
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
				var form = Ext.getCmp('EvnPLWOWSearchWindow');
			}
		});
		
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
				iconCls: 'search16',
				id: 'EPLWOW_SearchButton',
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
			{
				handler: function() 
				{
					this.getFilterForm().getForm().submit();
				}.createDelegate(this),
				iconCls: 'print16',
				tabIndex: this.firstTabIndex + 111,
				text: lang['pechat_spiska']
			}, 
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
				id: 'evnplwswRightPanel',
				defaults: {split: true},
				items: [form.SearchGrid]
			}]
		});
		sw.Promed.EvnPLWOWSearchWindow.superclass.initComponent.apply(this, arguments);
	}
});