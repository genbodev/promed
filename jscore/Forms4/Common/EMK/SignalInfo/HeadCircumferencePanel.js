/**
 * Панель "Окружность головы"
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
Ext6.define('common.EMK.SignalInfo.HeadCircumferencePanel',
{
	extend: 'swPanel',
	title: langs('Окружность головы').toUpperCase(),
	allTimeExpandable: false,
	btnAddClickEnable: true,
	collapseOnOnlyTitle: true,
	collapsed: true,
	loaded: false,
	HeadCircumferenceGrid: undefined,

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

		this.HeadCircumferenceGrid = Ext6.create('Ext6.grid.Panel',
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
						header: langs('Окружность головы (см)'),
						dataIndex: 'HeadCircumference_Head'
					},
					{
						width: 120,
						header: langs('data'),
						dataIndex: 'HeadCircumference_setDate'
					},
					{
						width: 120,
						header: langs('vid_zamera'),
						dataIndex: 'HeightMeasureType_Name'
					},
					{
						width: 40,
						dataIndex: 'HeadCircumference_Action',

						renderer: function (value, metaData, record)
						{
							if (record.get('HeightMeasureType_Code') == '1')
								return "";

							return ("<div class='x6-tool-threedots' onclick='Ext6.getCmp(\"" +
										me.HeadCircumferenceGrid.id + "\").showRecordMenu(this, " +
										record.get('HeadCircumference_id') + ");'></div>");
						}
					}
				],

				sorters:
				[
					'HeadCircumference_setDate'
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
										{ name: 'HeadCircumference_id', type: 'int' },
										{ name: 'HeadCircumference_Head', type: 'float' },
										{ name: 'HeadCircumference_setDate', type: 'string' },
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

										url: '/?c=HeadCircumference&m=loadHeadCircumferencePanel',

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
												me.openHeadCircumferenceEditWindow('edit');
											}
										},
										{
											text: langs('udalit'),

											handler: function()
											{
												me.deleteHeadCircumference();
											}
										}
									]
								}),

				showRecordMenu: function(el, hcId)
				{
					this.recordMenu.HeadCircumference_id = hcId;
					this.recordMenu.showBy(el);
				}
			});

		Ext6.apply(this,
				   {
						items: [this.HeadCircumferenceGrid],

						tools:
						[
							{
								type: 'plusmenu',
								tooltip: 'Добавить',
								minWidth: 23,

								handler: function()
								{
									me.openHeadCircumferenceEditWindow('add');
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

		this.HeadCircumferenceGrid.getStore().load(
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
		this.openHeadCircumferenceEditWindow('add');
	},

/******* openHeadCircumferenceEditWindow **************************************
 *
 ******************************************************************************/
	openHeadCircumferenceEditWindow: function(action)
	{
		var me = this,
			formParams = {};

		formParams.Person_id = me.Person_id;
		formParams.Server_id = me.Server_id;

		if (action == 'add')
			formParams.HeadCircumference_id = 0;
		else
		{
			if (!(formParams.HeadCircumference_id =
					me.HeadCircumferenceGrid.recordMenu.HeadCircumference_id))
				return false;
		}

		getWnd('swHeadCircumferenceEditWindow').show(
			{
				action: action,
				HeadCircumference_id: formParams.HeadCircumference_id,
				formParams: formParams,

				callback: function(data)
				{
					if (!data || !data.headCircumferenceData)
						return false;

					me.load();
				},

				scope: this
			});
	},

/******* deleteHeadCircumference **********************************************
 *
 ******************************************************************************/
	deleteHeadCircumference: function()
	{
		var me = this,
			hcId = me.HeadCircumferenceGrid.recordMenu.HeadCircumference_id;

		if (hcId)
			checkDeleteRecord(
				{
					callback: function()
					{
						me.mask('Удаление записи...');

						Ext6.Ajax.request(
							{
								url: '/?c=HeadCircumference&m=deleteHeadCircumference',

								params:
								{
									HeadCircumference_id: hcId
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
