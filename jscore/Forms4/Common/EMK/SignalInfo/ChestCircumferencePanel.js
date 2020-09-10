/**
 * Панель "Окружность груди"
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Polka
 * @access       public
 * @copyright    Copyright (c) 2019 Swan Ltd.
 *
 */
Ext6.define('common.EMK.SignalInfo.ChestCircumferencePanel',
{
	extend: 'swPanel',
	title: langs('Окружность груди').toUpperCase(),
	allTimeExpandable: false,
	btnAddClickEnable: true,
	collapseOnOnlyTitle: true,
	collapsed: true,
	loaded: false,
	ChestCircumferenceGrid: undefined,

	listeners:
	{
		'expand':
			function()
			{
				if (!this.loaded)
				this.load();
			}
	},

/******* initComponent ********************************************************
 *
 ******************************************************************************/
	initComponent: function()
	{
		var me = this;

		this.ChestCircumferenceGrid = Ext6.create('Ext6.grid.Panel',
			{
				border: true,
				cls: 'EmkGrid',
				padding: 10,
				disableSelection: true,

				viewConfig:
				{
					minHeight: 33
				},

				columns:
				[
					{
						width: 200,
						header: langs('Окружность груди (см)'),
						dataIndex: 'ChestCircumference_Chest'
					},
					{
						width: 120,
						header: langs('data'),
						dataIndex: 'ChestCircumference_setDate'
					},
					{
						width: 120,
						header: langs('vid_zamera'),
						dataIndex: 'HeightMeasureType_Name'
					},
					{
						width: 40,
						dataIndex: 'ChestCircumference_Action',

						renderer: function (value, metaData, record)
						{
							if (record.get('HeightMeasureType_Code') == '1')
								return "";

							return ("<div class='x6-tool-threedots' onclick='Ext6.getCmp(\"" +
										me.ChestCircumferenceGrid.id + "\").showRecordMenu(this, " +
										record.get('ChestCircumference_id') + ");'></div>");
						}
					}
				],

				sorters:
				[
					'ChestCircumference_setDate'
				],

				listeners:
				{
					'load': function(store, records)
					{
						me.setTitleCounter(records.length);
					}
				},

				store:
					Ext6.create('Ext6.data.Store',
								{
									fields:
									[
										{ name: 'ChestCircumference_id', type: 'int' },
										{ name: 'ChestCircumference_Chest', type: 'float' },
										{ name: 'ChestCircumference_setDate', type: 'string' },
										{ name: 'HeightMeasureType_Name', type: 'string' },
										{ name: 'HeightMeasureType_Code', type: 'string' }
									],

									proxy:
									{
										type: 'ajax',

										actionMethods:
										{
											create: "POST",
											read: "POST",
											update: "POST",
											destroy: "POST"
										},

										url: '/?c=ChestCircumference&m=loadChestCircumferencePanel',

										reader:
										{
											type: 'json',
											rootProperty: 'data',
											totalProperty: 'totalCount'
										}
									}
								}),

				recordMenu:
					Ext6.create('Ext6.menu.Menu',
								{
									items:
									[
										{
											text: langs('redaktirovat'),

											handler: function()
											{
												me.openChestCircumferenceEditWindow('edit');
											}
										},
										{
											text: langs('udalit'),

											handler: function()
											{
												me.deleteChestCircumference();
											}
										}
									]
								}),

				showRecordMenu: function(el, ccId)
				{
					this.recordMenu.ChestCircumference_id = ccId;
					this.recordMenu.showBy(el);
				}
			});

		Ext6.apply(this,
				   {
						items: [this.ChestCircumferenceGrid],

						tools:
						[
							{
								type: 'plusmenu',
								tooltip: 'Добавить',
								minWidth: 23,

								handler: function()
								{
									me.openChestCircumferenceEditWindow('add');
								}
							}
						]
					});

		this.callParent(arguments);
	},

/******* setParams ************************************************************
 *
 ******************************************************************************/
	setParams: function(params)
	{
		var me = this;

		me.Person_id = params.Person_id;
		me.Server_id = params.Server_id;
		me.loaded = false;

		if (!me.collapsed)
			me.load();
	},

/******* load *****************************************************************
 *
 ******************************************************************************/
	load: function()
	{
		var me = this;

		this.loaded = true;

		this.ChestCircumferenceGrid.getStore().load(
			{
				params:
				{
					Person_id: me.Person_id
				}
			});
	},

/******* onBtnAddClick ********************************************************
 *
 ******************************************************************************/
	onBtnAddClick: function()
	{
		this.openChestCircumferenceEditWindow('add');
	},

/******* openChestCircumferenceEditWindow *************************************
 *
 ******************************************************************************/
	openChestCircumferenceEditWindow: function(action)
	{
		var me = this,
			formParams = {};

		formParams.Person_id = me.Person_id;
		formParams.Server_id = me.Server_id;

		if (action == 'add')
			formParams.ChestCircumference_id = 0;
		else
		{
			if (!(formParams.ChestCircumference_id =
					me.ChestCircumferenceGrid.recordMenu.ChestCircumference_id))
				return false;
		}

		getWnd('swChestCircumferenceEditWindow').show(
			{
				action: action,
				ChestCircumference_id: formParams.ChestCircumference_id,
				formParams: formParams,

				callback: function(data)
				{
					if (!data || !data.chestCircumferenceData)
						return false;

					me.load();
				},

				scope: this
			});
	},

/******* deleteChestCircumference *********************************************
 *
 ******************************************************************************/
	deleteChestCircumference: function()
	{
		var me = this,
			ccId = me.ChestCircumferenceGrid.recordMenu.ChestCircumference_id;

		if (ccId)
			checkDeleteRecord(
				{
					callback: function()
					{
						me.mask('Удаление записи...');

						Ext6.Ajax.request(
							{
								url: '/?c=ChestCircumference&m=deleteChestCircumference',

								params:
								{
									ChestCircumference_id: ccId
								},

								callback: function ()
								{
									me.unmask();
									me.load();
								}
						})
					}
				});
	}
});
