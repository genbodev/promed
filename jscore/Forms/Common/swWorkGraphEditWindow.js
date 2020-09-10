/**
 * Created with JetBrains PhpStorm.
 * User: Shorev
 * Date: 25.03.16
 * Time: 11:14
 * To change this template use File | Settings | File Templates.
 */


sw.Promed.swWorkGraphEditWindow = Ext.extend(sw.Promed.BaseForm, {
	height: 500,
	//autoHeight: true,
	//id: 'WorkGraphEditWindow',
	layout: 'border',
	modal: true,
	plain: true,
	id: 'WGEWindow',
	resizable: false,
	isSave: false,
	listeners: {
		'hide': function() {
			//this.onHide();
			var win = this;
			if(win.isSave){
				win.isSave=false;
				win.onHide();
			}else{
				this.closeFn(function(){
					win.onHide();
				});
			}
		}
	},
	deleteWorkGraphLpuSection: function() {
		var grid = this.GridPanel.getGrid();

		if ( !grid ) {
			sw.swMsg.alert(lang['oshibka'], lang['При удаленни строки графика дежурств возникли ошибки']);
			return false;
		}
		else if ( !grid.getSelectionModel().getSelected() ) {
			sw.swMsg.alert(lang['oshibka'],'Не выбрана строка графика дежурств');
			return false;
		}

		var selected_record = grid.getSelectionModel().getSelected();
		var id = selected_record.get('WorkGraphLpuSection_id');

		if ( !id ) {
			return false;
		}

		this.del_ids.push(id);
		grid.getStore().remove(selected_record);
		if ( grid.getStore().getCount() == 0 ) {
			LoadEmptyRow(grid);
		}
		grid.getView().focusRow(0);
		grid.getSelectionModel().selectFirstRow();

	},
	enableEdit: function(enabled)
	{
		if(enabled == true){
			this.form.findField('MedStaffFact_id').enable();
			this.form.findField('WorkGraph_begDate').enable();
			this.form.findField('WorkGraph_endDate').enable();
			this.buttons[0].show();
			this.GridPanel.getAction('action_add').setDisabled(false);
			this.GridPanel.getAction('action_delete').setDisabled(false);
		}
		else
		{
			this.form.findField('MedStaffFact_id').disable();
			this.form.findField('WorkGraph_begDate').disable();
			this.form.findField('WorkGraph_endDate').disable();
			this.buttons[0].hide();
			this.GridPanel.getAction('action_add').setDisabled(true);
			this.GridPanel.getAction('action_delete').setDisabled(true);
		}
	},
	show: function() {
		var wnd = this;
		this.WorkGraph_id = -1;
		wnd.isSave=false;
		wnd.form.findField('WorkGraph_id').setValue(-1);
		wnd.GridPanel.getGrid().getStore().baseParams.WorkGraph_id = -1;
		this.action = '';
		if (!arguments[0]) {
			this.hide();
			return false;
		}

		wnd.new_ids = new Array();
		wnd.del_ids = new Array();
		wnd.form.reset();
		if (arguments[0].WorkGraph_id && arguments[0].WorkGraph_id > 0) {
			this.WorkGraph_id = arguments[0].WorkGraph_id;
			wnd.form.findField('WorkGraph_id').setValue(this.WorkGraph_id);
		}

		if(arguments[0].action)
		{
			this.action = arguments[0].action;
		}
		wnd.GridPanel.getGrid().getStore().removeAll();
		//wnd.form.reset();
		if(this.action == 'view'){
			wnd.enableEdit(false);
			wnd.setTitle('Графики дежурств: Просмотр');
		}
		else
		{
			wnd.enableEdit(true);
		}

		if(this.action == 'add'){
			wnd.setTitle('Графики дежурств: Добавление');
		}
		if(this.action == 'edit'){
			wnd.setTitle('Графики дежурств: Редактирование');
		}
		if (arguments[0].callback)
			this.returnFunc = arguments[0].callback;
		sw.Promed.swWorkGraphEditWindow.superclass.show.apply(wnd, arguments);

		var WorkGraph_id = this.WorkGraph_id;

		var opt = getGlobalOptions();
		var cur_date = Date.parseDate(opt['date'], 'd.m.Y').format('Y-m-d');
		cur_date = Ext.util.Format.date(cur_date, 'd.m.Y');
		setMedStaffFactGlobalStoreFilter({
			isOnlyStac: true,
			onEndDate: cur_date //Исключаем уволенных на текущую дату
		});

		wnd.form.findField('MedStaffFact_id').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));
		
		var begD = wnd.form.findField('WorkGraph_begDate');
		var endD = wnd.form.findField('WorkGraph_endDate');
		if(begD.minValue){
			endD.setMinValue(begD.minValue);
		}

		if(this.action.inlist(['edit','view']))
		{
			Ext.Ajax.request({
				callback: function(options,success,response) {
					if(success){
						var response_obj = Ext.util.JSON.decode(response.responseText);
						wnd.form.findField('MedStaffFact_id').setValue(response_obj[0].MedStaffFact_id);
						wnd.form.findField('WorkGraph_begDate').setValue(response_obj[0].WorkGraph_begDate);
						wnd.form.findField('WorkGraph_endDate').setValue(response_obj[0].WorkGraph_endDate);
						wnd.form.findField('WorkGraph_id').setValue(WorkGraph_id);
						wnd.GridPanel.getGrid().getStore().baseParams.WorkGraph_id = WorkGraph_id;
						wnd.GridPanel.getGrid().getStore().load();
						var options = getGlobalOptions();
						var date = Date.parseDate(options['date'], 'd.m.Y').format('Y-m-d');
						var beg_date = Date.parseDate(response_obj[0].WorkGraph_begDate, 'd.m.Y').format('Y-m-d');
						var end_date = Date.parseDate(response_obj[0].WorkGraph_endDate, 'd.m.Y').format('Y-m-d');
						if(wnd.action == 'edit'){
							if(date < beg_date){
								wnd.enableEdit(true);
							}
							else{
								wnd.enableEdit(false);
								if(date <= end_date){
									wnd.form.findField('WorkGraph_endDate').enable();
									endD.setMinValue(begD.getValue());
									wnd.buttons[0].show();
								}
							}
						}
					}
					else
					{
						//alert('Ккк');
					}
				},
				params: {
					WorkGraph_id: WorkGraph_id
				},
				url: '/?c=Common&m=LoadWorkGraphData'
			});
		}
		else{
			if ( wnd.GridPanel.getGrid().getStore().getCount() == 0 ) {
				LoadEmptyRow(wnd.GridPanel.getGrid());
			}
		}
	},
	title: 'График дежурств',
	width: 900,
	initComponent: function() {
		var wnd = this;
		this.onKeyDown = function (inp, e) {
			if (e.getKey() == Ext.EventObject.ENTER) {
				e.stopEvent();
			}
		}.createDelegate(this);
		var form = new Ext.form.FormPanel({
			region: 'north',
			autoScroll: true,
			bodyStyle: 'padding: 7px; background:#DFE8F6;',
			autoHeight: true,
			border: false,
			frame: false,
			id: 'WorkGraphEditWindow',
			items: [
				{
					//id: 'WorkGraph_id',
					name: 'WorkGraph_id',
					value: -1,
					xtype: 'hidden'
				},
				{
					autoHeight: true,
					style: 'padding: 7px;',
					anchor: '100%',
					xtype: 'fieldset',
					items: [
						{
							fieldLabel: lang['sotrudnik'],
							hiddenName: 'MedStaffFact_id',
							id: 'WG_MedPersonalCombo',
							allowBlank: false,
							lastQuery: '',
							listWidth: 500,
							tabIndex: TABINDEX_ESTWREF + 4,
							width: 500,
							xtype: 'swmedstafffactglobalcombo'
						},
						{
							layout: 'column',
							bodyStyle: 'padding: 0px; background:#DFE8F6;',
							border: false,
							items:[{
								bodyStyle: 'padding: 0px; background:#DFE8F6;',
								layout: 'form',
								border: false,
								//labelWidth: 120,
								items:[{
									xtype: 'swdatefield',
									width: 100,
									disabled: false,
									name: 'WorkGraph_begDate',
									allowBlank: false,
									id: 'WorkGraph_begDate',
									fieldLabel: 'Дата начала',
									minValue: new Date(),
									listeners: {
										change: function(field, newValue, oldValue){
											var endD = Ext.getCmp('WorkGraph_endDate');
											var endDVal = endD.getValue();
											if(newValue) {
												if(endDVal && newValue > endDVal) endD.setValue();
												endD.setMinValue(newValue);
											}
										}
									}
								}]
							}, {
								bodyStyle: 'padding-left: 20px; background:#DFE8F6;',
								layout: 'form',
								border: false,
								//labelWidth: 80,
								items:[{
									xtype: 'swdatefield',
									width: 100,
									disabled: false,
									allowBlank: false,
									name: 'WorkGraph_endDate',
									id: 'WorkGraph_endDate',
									fieldLabel: 'Дата окончания'
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
						{ name: 'WorkGraph_id'},
						{ name: 'Lpu_id' },
						{ name: 'MedStaffFact_id' },
						{ name: 'WorkGraph_begDate' },
						{ name: 'WorkGraph_endDate' }
					]
			),
			url: C_WORKGRAPH_SAVE
		});

		this.GridPanel = new sw.Promed.ViewFrame({
			region: 'center',
			id: this.id + 'ViewFrame',
			actions: [
				{
					name: 'action_add',
					hidden: false,
					disabled:true,
					handler: function() {
						wnd.GridPanel.editGrid('add');
					}
				},
				{name: 'action_edit', disabled: true, hidden: true},
				{name: 'action_view', disabled: true, hidden: true},
				{
					name: 'action_delete',
					hidden: false,
					disabled:true,
					handler: function() {
						wnd.deleteWorkGraphLpuSection();
					}
				}
			],

			autoExpandColumn: 'autoexpand',
			autoExpandMin: 150,
			autoLoadData: false,
			dataUrl: '/?c=Common&m=loadWorkGraphLpuSectionGrid',
			border: true,
			height: 180,
			paging: false,
			root: 'data',
			stringfields: [
				//{name: 'WorkGraph_id', type: 'int', hidden:'true'},
				{name: 'WorkGraphLpuSection_id', type: 'int', header: 'ID', key: true},
				{name: 'LpuBuilding_Name', width: 210, header: 'Наименование подразделения', type: 'string'},
				{name: 'LpuSection_Code', width: 100, header: 'Код отделения', type: 'string'},
				{name: 'LpuSection_Name', width: 574, header: 'Наименование отделения', type: 'string'}
			],
			title: null,
			toolbar: true,
			editing: true,
			onLoadData: function() {
				wnd.GridPanel.getGrid().getStore().each(function (rec){
					if(!Ext.isEmpty(rec.get('WorkGraphLpuSection_id')))
						if(rec.get('WorkGraphLpuSection_id').inlist(wnd.del_ids))
							wnd.GridPanel.getGrid().getStore().remove(rec);
				});

			},
			editGrid: function (action) {
				var form = wnd.form;
				var grid = wnd.GridPanel.getGrid();
				if(wnd.form.findField('WorkGraph_id').getValue() == -1){
					form.submit(
							{
								failure: function(result_form, _action)
								{
								},
								success: function(result_form, _action)
								{
									if (_action.result)
									{
										if (_action.result.WorkGraph_id)
										{
											var WorkGraph_id = _action.result.WorkGraph_id;
											//form.findField('WorkGraph_id').setValue(WorkGraph_id);
											wnd.form.findField('WorkGraph_id').setValue(WorkGraph_id);
											var params = new Object();
											params.action = 'add';
											params.WorkGraph_id = WorkGraph_id;
											params.onDate = wnd.form.findField('WorkGraph_begDate').getValue();
											grid.getStore().baseParams.WorkGraph_id = WorkGraph_id;
											params.callback = function(data) {
												grid.getStore().reload({
													callback: function(){
														var i = 0;
														if(!Ext.isEmpty(data.new_ids))
															for (i=0;i<data.new_ids.length;i++){
																wnd.new_ids.push(data.new_ids[i]);
															}
														grid.getView().focusRow(0);
														grid.getSelectionModel().selectFirstRow();
													}
												});
											}.createDelegate(this);
											getWnd('swWorkGraphLpuSectionWindow').show(params);
										}
									}
								}
							});
				}
				else {
					var WorkGraph_id = wnd.form.findField('WorkGraph_id').getValue();
					var params = new Object();
					params.action = action;
					params.WorkGraph_id = WorkGraph_id;
					params.callback = function(data) {
						grid.getStore().reload({
							callback: function(){
								var i = 0;
								if(!Ext.isEmpty(data.new_ids))
									for (i=0;i<data.new_ids.length;i++){
										wnd.new_ids.push(data.new_ids[i]);
									}
								grid.getView().focusRow(0);
								grid.getSelectionModel().selectFirstRow();
							}
						});
					}.createDelegate(this);
					getWnd('swWorkGraphLpuSectionWindow').show(params);
				}
			}
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
						/*
						var that = this;
						if(this.action=='add' && this.form.findField('WorkGraph_id').getValue() > -1){
							var WorkGraph_id = this.form.findField('WorkGraph_id').getValue();
							var params = new Object();
							params.WorkGraph_id = WorkGraph_id;
							Ext.Ajax.request({
								failure: function(response, options) {
									sw.swMsg.alert(lang['oshibka'], error);
								},
								params: params,
								success: function(response, options) {
									that.hide();
								}.createDelegate(this),
								url: C_WORKGRAPH_DEL
							});
						}
						else{
							if(this.form.findField('WorkGraph_id').getValue() > -1 && that.new_ids.length > 0){
								var WorkGraph_id = this.form.findField('WorkGraph_id').getValue();
								var params = new Object()
								params.WorkGraph_id = WorkGraph_id;
								params.new_ids = new Array();
								params.new_ids = Ext.util.JSON.encode(that.new_ids);
								Ext.Ajax.request({
									params: params,
									url: C_WORKGRAPHLPUSECTIONARR_DEL,
									success: function(response, options){
										that.hide();
									}
								});
							}
							else{
								that.hide();
							}
						}
						*/
					}.createDelegate(this),
					iconCls: 'cancel16',
					text: BTN_FRMCANCEL
				}],
			items: [
				form,
				this.GridPanel
			]
		});

		this.form = form.getForm();

		sw.Promed.swWorkGraphEditWindow.superclass.initComponent.apply(this, arguments);
	},
	doSave: function()
	{
		var win =this;
		var wnd = this.findById('WorkGraphEditWindow');
		var form = this.findById('WorkGraphEditWindow').getForm();
		var options = getGlobalOptions();
		var date = Date.parseDate(options['date'], 'd.m.Y').format('Y-m-d');

		var beg_date = form.findField('WorkGraph_begDate').getValue().format('Y-m-d');
		var end_date = form.findField('WorkGraph_endDate').getValue().format('Y-m-d');
		if(beg_date > end_date){
			Ext.Msg.alert('Ошибка', 'Дата окончания не должна быть меньше даты начала!');
			return false;
		}
		if(this.action=='edit' && form.findField('WorkGraph_begDate').disabled){
			if(date > end_date){
				Ext.Msg.alert('Ошибка', 'Дата окончания не должна быть меньше текущей!');
				return false;
			}
		}else if(date > beg_date){
			Ext.Msg.alert('Ошибка', 'Дата начала не должна быть меньше текущей даты!');
			return false;
		}
		var post = {
			MedStaffFact_id: form.findField('MedStaffFact_id').getValue(),
			WorkGraph_begDate: form.findField('WorkGraph_begDate').getValue(),
			WorkGraph_endDate: form.findField('WorkGraph_endDate').getValue(),
			del_ids: this.del_ids
		};
		post.del_ids = new Array();
		post.del_ids = Ext.util.JSON.encode(this.del_ids);

		if(Ext.isEmpty(this.GridPanel.getGrid().getSelectionModel().getSelected().get('WorkGraphLpuSection_id')))
		{
			Ext.Msg.alert('Ошибка', 'В списке графика дежурств должно быть хотя бы одно отделение!');
			return false;
		}
		var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Сохранение записи..." });
		loadMask.show();
		win.isSave = true;
		form.submit(
			{
				params: post,
				failure: function(result_form, action)
				{
					loadMask.hide();
				},
				success: function(result_form, action)
				{
					loadMask.hide();
					if (action.result)
					{
						if (action.result.WorkGraph_id)
						{
							wnd.ownerCt.returnFunc(action.result.WorkGraph_id);
							wnd.ownerCt.hide();
						}
						else{
							Ext.Msg.alert(lang['oshibka_#100004'], lang['pri_sohranenii_proizoshla_oshibka']);
							win.isSave = false;
						}
					}
					else{
						Ext.Msg.alert(lang['oshibka_#100005'], lang['pri_sohranenii_proizoshla_oshibka']);
						win.isSave = false;
					}
				}
			}
		);
	},
	closeFn: function(fn){
		var that = this;
		if(this.action=='add' && this.form.findField('WorkGraph_id').getValue() > -1){
			var WorkGraph_id = this.form.findField('WorkGraph_id').getValue();
			var params = new Object();
			params.WorkGraph_id = WorkGraph_id;
			Ext.Ajax.request({
				failure: function(response, options) {
					sw.swMsg.alert(langs('Ошибка'), error);
				},
				params: params,
				success: function(response, options) {
					if(fn && typeof fn === 'function') fn();
				}.createDelegate(this),
				url: C_WORKGRAPH_DEL
			});
		}
		else{
			if(this.form.findField('WorkGraph_id').getValue() > -1 && that.new_ids.length > 0){
				var WorkGraph_id = this.form.findField('WorkGraph_id').getValue();
				var params = new Object()
				params.WorkGraph_id = WorkGraph_id;
				params.new_ids = new Array();
				params.new_ids = Ext.util.JSON.encode(that.new_ids);
				Ext.Ajax.request({
					params: params,
					url: C_WORKGRAPHLPUSECTIONARR_DEL,
					success: function(response, options){
						if(fn && typeof fn === 'function') fn();
					}
				});
			}
			else{
				if(fn && typeof fn === 'function') fn();
			}
		}
	}
});