/**
 * swTemplateRefValuesViewWindow - окно записи
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/projects/promed
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2009-2010, Swan.
 * @author       Марков Андрей
 * @prefix       trvvw
 * @tabindex     TABINDEX_TRVVW
 * @version      декабрь 2010 
 */
 
/*NO PARSE JSON*/

sw.Promed.swTemplateRefValuesViewWindow = Ext.extend(sw.Promed.BaseForm,
{
	codeRefresh: true,
	objectName: 'swTemplateRefValuesViewWindow',
	objectSrc: '/jscore/Forms/Common/swTemplateRefValuesViewWindow.js',
	
	closable: true,
	closeAction: 'hide',
	maximized: true,
	title: WND_TRVVW,
	iconCls: 'sprav16',
	id: 'swTemplateRefValuesViewWindow',
	loadGrid: function(mode)
	{
		// значения фильтра
		this.params.RefValues_Name = this.getFilterField('RefValues_Name').getValue();
		this.params.RefValuesType_id = this.getFilterField('RefValuesType_id').getValue();
		this.params.RefCategory_id = this.getFilterField('RefCategory_id').getValue();
		this.params.RefMaterial_id = this.getFilterField('RefMaterial_id').getValue();
		
		this.params.start = 0; 
		this.params.limit = 100;

		this.ListGrid.removeAll(true);
		this.ListGrid.loadData({globalFilters:this.params});
	},

	getFilterButton: function(id)
	{
		return this.Filters.getBottomToolbar().items.get(id);
	},
	getFilterField: function(field)
	{
		return this.Filters.findById('trvvw'+field);
	},
	getLoadMask: function(MSG)
	{
		if (MSG) 
		{
			delete(this.loadMask);
		}
		if (!this.loadMask)
		{
			this.loadMask = new Ext.LoadMask(Ext.get(this.id), { msg: MSG });
		}
		return this.loadMask;
	},

	show: function()
	{
		sw.Promed.swTemplateRefValuesViewWindow.superclass.show.apply(this, arguments);

		//this.Filters.findById('trvvwLpuSectionProfile_id').focus(true, 100);
		// Инициируем переменные для работы 
		this.params = {};
		// Обнулим значения фильтра
		this.Filters.clear();
	},
  initComponent: function()
	{
		// фильтры для быстрого поиска референтного значения
		this.Filters = new Ext.Panel(
		{
			region: 'north',
			border: false,
			frame: true,
			//defaults: {bodyStyle:'background:#DFE8F6;'},
			autoHeight: true,
			style: 'padding: 5px;',
			bbar:
			[{
				tabIndex: TABINDEX_TRVVW+5,
				xtype: 'button',
				id: 'trvvwBtnSearch',
				text: lang['nayti'],
				iconCls: 'search16',
				handler: function()
				{
					var form = Ext.getCmp('swTemplateRefValuesViewWindow');
					form.loadGrid();
				}
			},
			{
				tabIndex: TABINDEX_TRVVW+6,
				xtype: 'button',
				id: 'trvvwBtnClear',
				text: lang['sbros'],
				iconCls: 'resetsearch16',
				handler: function()
				{
					var form = Ext.getCmp('swTemplateRefValuesViewWindow');
					// Очистка полей фильтра И перезагрузка
					form.Filters.clear(true);
				}
			}],
			items:
			[{
				xtype: 'form',
				autoHeight: true,
				layout: 'column',
				items: 
				[{
					layout: 'form',
					columnWidth: .20,
					labelAlign: 'right',
					labelWidth: 100,
					items: 
					[{
						fieldLabel: lang['naimenovanie'],
						xtype: 'textfield',
						anchor:'100%',
						tabIndex: TABINDEX_TRVVW+1,
						name: 'RefValues_Name',
						id: 'trvvwRefValues_Name',
						enableKeyEvents: true,
						listeners: 
						{
							'keydown': function (inp, e) 
							{
								var form = Ext.getCmp('swTemplateRefValuesViewWindow');
								if (e.getKey() == Ext.EventObject.ENTER)
								{
									e.stopEvent();
									form.loadGrid();
								}
							}
						}
					}]
				}, 
				{
					layout: 'form',
					columnWidth: .20,
					labelAlign: 'right',
					labelWidth: 60,
					items: 
					[{
						allowBlank: true,
						disabled: false,
						fieldLabel: lang['tip'],
						tabIndex: TABINDEX_TRVVW+2,
						comboSubject: 'RefValuesType',
						sortField: 'RefValuesType_id',
						id: 'trvvwRefValuesType_id',
						anchor:'100%',
						xtype: 'swcustomobjectcombo',
						listeners: 
						{
							'keypress': function (inp, e) 
							{
								var form = Ext.getCmp('swTemplateRefValuesViewWindow');
								log('asdas');
								if (e.getKey() == Ext.EventObject.ENTER)
								{
									e.stopEvent();
									form.loadGrid();
								}
							}
						}
					}]
				},
				{
					layout: 'form',
					columnWidth: .20,
					labelAlign: 'right',
					labelWidth: 80,
					items: 
					[{
						allowBlank: true,
						disabled: false,
						fieldLabel: lang['kategoriya'],
						tabIndex: TABINDEX_TRVVW+3,
						comboSubject: 'RefCategory',
						sortField: 'RefCategory_id',
						id: 'trvvwRefCategory_id',
						anchor:'100%',
						xtype: 'swcustomobjectcombo',
						listeners: 
						{
							'keypress': function (inp, e) 
							{
								var form = Ext.getCmp('swTemplateRefValuesViewWindow');
								if (e.getKey() == Ext.EventObject.ENTER)
								{
									e.stopEvent();
									form.loadGrid();
								}
							}
						}
					}]
				},
				{
					layout: 'form',
					columnWidth: .20,
					labelAlign: 'right',
					labelWidth: 80,
					items: 
					[{
						allowBlank: true,
						disabled: false,
						fieldLabel: lang['material'],
						tabIndex: TABINDEX_TRVVW+3,
						comboSubject: 'RefMaterial',
						sortField: 'RefMaterial_id',
						id: 'trvvwRefMaterial_id',
						anchor:'100%',
						xtype: 'swcustomobjectcombo',
						listeners: 
						{
							'keypress': function (inp, e) 
							{
								var form = Ext.getCmp('swTemplateRefValuesViewWindow');
								if (e.getKey() == Ext.EventObject.ENTER)
								{
									e.stopEvent();
									form.loadGrid();
								}
							}
						}
					}]
				}]
			}],
			clear: function(loadGrid)
			{
				// возвращаемся к состоянию при открытии формы
				var form = Ext.getCmp('swTemplateRefValuesViewWindow');
				var filters = this;
				form.ListGrid.removeAll({clearAll: true});
				form.params.RefValues_Name = '';
				form.params.RefValuesType_id = '';
				form.params.RefCategory_id = '';
				form.getFilterField('RefValues_Name').setValue(null);
				form.getFilterField('RefValuesType_id').setValue(null);
				form.getFilterField('RefCategory_id').setValue(null);
				//form.params.Lpu_id = getGlobalOptions().lpu_id;
				if (loadGrid)
					form.loadGrid();
			}
		});
		
		// грид для отображения значения
		this.ListGrid = new sw.Promed.ViewFrame(
		{
			id: 'trvvwRefValuesGrid',
			region: 'center',
			object: 'RefValues',
			border: true,
			dataUrl: '/?c=Template&m=loadRefValues',
			toolbar: true,
			autoLoadData: false,
			paging: true,
			root: 'data',
			editformclassname: 'swTemplateRefValuesEditWindow',
			totalProperty: 'totalCount',
			stringfields:
			[
				{name: 'RefValues_id', type: 'int', header: 'ID', key: true},
				{name: 'Lpu_id', hidden: true, isparams: true},
				//{name: 'RefValues_OPMUCode', width: 80, header: 'Код ОПМУ'},
				//{name: 'RefValues_OPMUCode', width: 80, header: 'Код'},
				//{name: 'RefValues_LocalCode', width: 80, header: 'Код'},
				{name: 'RefValues_Name', width: 2000, header: lang['naimenovanie'], id: 'autoexpand'},
				{name: 'RefValues_Nick', width: 160, header: lang['klinicheskoe_nazvanie']},
				{name: 'RefValuesType_id', hidden: true, isparams: true},
				{name: 'RefValuesType_Name', width: 160, header: lang['tip']},
				{name: 'RefCategory_id', hidden: true, isparams: true},
				{name: 'RefCategory_Name',  width: 180, header: lang['kategoriya']},
				{name: 'RefValues_LowerLimit', type: 'float', width: 80, header: lang['verh_granitsa']},
				{name: 'RefValues_UpperLimit', type: 'float', width: 80, header: lang['nijn_granitsa']},
				{name: 'RefValuesUnit_Name', width: 150, header: lang['ed_izmereniya']},
				{name: 'RefMaterial_Name', width: 80, header: lang['material']}
			],
			actions:
			[
				{name:'action_add'},
				{name:'action_edit'},
				{name:'action_view'},
				{name:'action_delete'}
			],
			onRowSelect: function (sm,index,record)
			{
				//var form = Ext.getCmp('swTemplateRefValuesViewWindow');
			}
		});
		
		Ext.apply(this, 
		{
			layout: 'border',
			defaults: {split: true},
			items: 
			[
				this.Filters,
				this.ListGrid
			],
			buttons: 
			[{
				text: '-'
			}, 
			HelpButton(this, TABINDEX_TRVVW + 98), 
			{
				iconCls: 'cancel16',
				text: BTN_FRMCLOSE,
				handler: function() { this.hide(); }.createDelegate(this)
			}]
		});
		sw.Promed.swTemplateRefValuesViewWindow.superclass.initComponent.apply(this, arguments);
	}
});
