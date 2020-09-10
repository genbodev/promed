/**
 * Created with JetBrains PhpStorm.
 * User: Shorev
 * Date: 25.03.16
 * Time: 11:14
 * To change this template use File | Settings | File Templates.
 */


sw.Promed.swWorkGraphLpuSectionWindow = Ext.extend(sw.Promed.BaseForm, {
	height: 150,
	layout: 'border',
	modal: true,
	plain: true,
	resizable: false,
	listeners: {
		'hide': function() {
			this.onHide();
		}
	},
	show: function() {
		var wnd = this;
		this.WorkGraph_id = null;
		this.onDate = '';
		this.action = '';
		if (!arguments[0]) {
			this.hide();
			return false;
		}
		wnd.form.reset();
		if (arguments[0].WorkGraph_id && arguments[0].WorkGraph_id > 0) {
			this.WorkGraph_id = arguments[0].WorkGraph_id;
			wnd.form.findField('WorkGraph_id').setValue(this.WorkGraph_id);
		}

		if(arguments[0].action)
		{
			this.action = arguments[0].action;
		}

		if(arguments[0].onDate)
		{
			this.onDate = arguments[0].onDate;
		}

		if (arguments[0].callback)
			this.returnFunc = arguments[0].callback;
		sw.Promed.swWorkGraphLpuSectionWindow.superclass.show.apply(wnd, arguments);

		if ( wnd.form.findField('LpuBuilding_id').getStore().getCount() == 0 ) {
			swLpuBuildingGlobalStore.clearFilter();
			wnd.form.findField('LpuBuilding_id').getStore().loadData(getStoreRecords(swLpuBuildingGlobalStore));
		}

		setLpuSectionGlobalStoreFilter({
			isOnlyStac: true
		});

		swLpuSectionGlobalStore.clearFilter();

		wnd.form.findField('LpuBuilding_id').getStore().filterBy(function(rec_building){
			var rec_LpuBuilding_id = rec_building.get('LpuBuilding_id');
			var stac_exists = false;
			swLpuUnitGlobalStore.each(function(rec_unit){
				if(rec_unit.get('LpuBuilding_id') == rec_LpuBuilding_id && rec_unit.get('LpuUnitType_id') == '1'){
					//stac_exists = true;
					var rec_LpuUnit_id = rec_unit.get('LpuUnit_id');
					swLpuSectionGlobalStore.each(function(rec_section){
						if(rec_section.get('LpuUnit_id') == rec_LpuUnit_id)
							stac_exists = true;
					})
				}
			});
			return stac_exists;
		});

		/*if ( wnd.form.findField('LpuSection_id').getStore().getCount() == 0 ) {
			wnd.form.findField('LpuSection_id').getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));
		}*/
		var lpuSectionFilter = {
			onDate: Ext.util.Format.date(this.onDate, 'd.m.Y'),
			arrayLpuUnitTypeId: [ 1 ]
		}
		setLpuSectionGlobalStoreFilter(lpuSectionFilter);
		wnd.form.findField('LpuSection_id').getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));
	},
	title: 'График дежурств: Отделения',
	width: 610,
	initComponent: function() {
		var wnd = this;
		this.onKeyDown = function (inp, e) {
			if (e.getKey() == Ext.EventObject.ENTER) {
				e.stopEvent();
			}
		}.createDelegate(this);
		var form = new Ext.form.FormPanel({
			region: 'center',
			autoScroll: true,
			bodyStyle: 'padding: 7px; background:#DFE8F6;',
			autoHeight: true,
			border: false,
			frame: false,
			id: 'WorkGraphLpuSectionWindow',
			items: [
				{
					//id: 'WorkGraph_id',
					name: 'WorkGraph_id',
					value: 0,
					xtype: 'hidden'
				},
				{
					id: 'WorkGraphLpuSection_id',
					name: 'WorkGraphLpuSection_id',
					value: 0,
					xtype: 'hidden'
				},
				{
					autoHeight: true,
					style: 'padding: 7px;',
					anchor: '100%',
					xtype: 'fieldset',
					items: [
						{
							hiddenName: 'LpuBuilding_id',
							fieldLabel: 'Подразделение',
							allowBlank: false,
							id: 'WGLS_LpuBuildingCombo',
							lastQuery: '',
							linkedElements: [
								'WGLS_LpuSectionCombo'
							],
							listWidth: 700,
							tabIndex: TABINDEX_EPLSW + 81,
							width: 450,
							xtype: 'swlpubuildingglobalcombo'
						},
						{
							layout: 'column',
							bodyStyle: 'padding: 0px; background:#DFE8F6;',
							border: false,
							items:[{
								bodyStyle: 'padding: 0px; background:#DFE8F6;',
								layout: 'form',
								border: false,
								items:[{
									hiddenName: 'LpuSection_id',
									id: 'WGLS_LpuSectionCombo',
									lastQuery: '',
									parentElementId: 'WGLS_LpuBuildingCombo',
									listWidth: 700,
									tabIndex: TABINDEX_EPLSW + 82,
									width: 450,
									xtype: 'swlpusectionglobalcombo'
								}]
							}]
						}
					]
				}
			],
			reader: new Ext.data.JsonReader(
				{
					success: function()
					{
						alert('success');
					}
				},
				[
					{ name: 'WorkGraph_id' },
					{ name: 'LpuBuilding_id' },
					{ name: 'LpuSection_id' }
				]
			),
			url: C_WORKGRAPH_LPUSEC_SAVE
		});

		Ext.apply(this, {
			layout: 'border',
			buttons: [{
				handler: function() {
					wnd.doSave();
				}.createDelegate(this),
				iconCls: 'add16',
				text: lang['sohranit']
			}, {
				text: '-'
			},
				HelpButton(this),
				{
					handler: function() {
						this.hide();
					}.createDelegate(this),
					iconCls: 'cancel16',
					text: BTN_FRMCANCEL
				}],
			items: [
				form
			]
		});

		this.form = form.getForm();

		sw.Promed.swWorkGraphLpuSectionWindow.superclass.initComponent.apply(this, arguments);
	},
	doSave: function()
	{
		//alert('1');
		//log(Ext.getCmp('WGEWindow').del_ids);
		//alert('2');
		//Если перед этим удалили это отделение, то пересохранять его не надо, достаточно убрать из del_ids и добавить в new_ids
		//myArray.splice(myArray.indexOf('MenuB'),1);
		/**
		 * if(rec.get('WorkGraphLpuSection_id').inlist(wnd.del_ids))
		 wnd.GridPanel.getGrid().getStore().remove(rec);
		 * @type {*}
		 */
		var that = this;
		var wnd = this.findById('WorkGraphLpuSectionWindow');
		var form = this.findById('WorkGraphLpuSectionWindow').getForm();
		var sections = new Array();
		wnd.form.findField('LpuSection_id').getStore().each(function (rec){
			sections.push(rec.id);
			log(rec.id);
		});
		if(sections.length == 0){
			Ext.Msg.alert('Ошибка', 'В данном подразделении отсутствуют отделения круглосуточного стационара!');
			return false;
		}
		var post = {
			LpuSectionList: Ext.util.JSON.encode(sections)
		};

		if(!Ext.isEmpty(wnd.form.findField('LpuSection_id').getValue())){
			Ext.Ajax.request({
				callback: function(options,success,response) {
					if(success){
						var response_obj = Ext.util.JSON.decode(response.responseText);
						if(!Ext.isEmpty(response_obj) && !Ext.isEmpty(response_obj[0]))
						{
							var WorkGraphLpuSection_id = response_obj[0].WorkGraphLpuSection_id;
							if(WorkGraphLpuSection_id.inlist(Ext.getCmp('WGEWindow').del_ids)){
								Ext.getCmp('WGEWindow').del_ids.splice(Ext.getCmp('WGEWindow').del_ids.indexOf(WorkGraphLpuSection_id),1);
								wnd.ownerCt.returnFunc('');
								wnd.ownerCt.hide();
							}
						}
						else{
							var loadMask = new Ext.LoadMask(that.getEl(), { msg: "Сохранение записи..." });
							loadMask.show();
							form.submit(
								{
									params: post,
									failure: function(result_form, action)
									{
										if (action.result)
										{
											if (action.result.Error_Code)
											{
												Ext.Msg.alert(lang['oshibka_#']+action.result.Error_Code, action.result.Error_Message);
											}
											else
											{
												//Ext.Msg.alert('Ошибка #100003', 'При сохранении произошла ошибка!');
											}
										}

										loadMask.hide();
									},
									success: function(result_form, action)
									{
										loadMask.hide();
										if (action.result)
										{
											if (action.result.WorkGraphLpuSection_id)
											{
												wnd.ownerCt.returnFunc(action.result);
												wnd.ownerCt.hide();
											}
											else
												Ext.Msg.alert(lang['oshibka_#100004'], lang['pri_sohranenii_proizoshla_oshibka']);
										}
										else
											Ext.Msg.alert(lang['oshibka_#100005'], lang['pri_sohranenii_proizoshla_oshibka']);
									}
								});
						}
					}
					else
					{

					}
				},
				params: {
					WorkGraph_id: that.WorkGraph_id,
					LpuSection_id: wnd.form.findField('LpuSection_id').getValue()
				},
				url: '/?c=Common&m=LoadWorkGraphLpuSection'
			});
		}
		else
		{
			var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Сохранение записи..." });
			loadMask.show();
			form.submit(
			{
				params: post,
				failure: function(result_form, action)
				{
					if (action.result)
					{
						if (action.result.Error_Code)
						{
							Ext.Msg.alert(lang['oshibka_#']+action.result.Error_Code, action.result.Error_Message);
						}
						else
						{
							//Ext.Msg.alert('Ошибка #100003', 'При сохранении произошла ошибка!');
						}
					}

					loadMask.hide();
				},
				success: function(result_form, action)
				{
					loadMask.hide();
					if (action.result)
					{
						if (action.result.WorkGraphLpuSection_id)
						{
							wnd.ownerCt.returnFunc(action.result);
							wnd.ownerCt.hide();
						}
						else
							Ext.Msg.alert(lang['oshibka_#100004'], lang['pri_sohranenii_proizoshla_oshibka']);
					}
					else
						Ext.Msg.alert(lang['oshibka_#100005'], lang['pri_sohranenii_proizoshla_oshibka']);
				}
			});
		}
	}
});