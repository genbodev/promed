sw.Promed.ChildDeathGridPanel = Ext.extend(sw.Promed.ViewFrame, {
	title:'Мертворожденные',
	height:130,
	autoLoadData:false,
	focusOnFirstLoad:false,
	dataUrl:'/?c=BirthSpecStac&m=loadChildDeathGridData',
	useEmptyRecord: false,

	//методы для переопределения
	beforeChildDeathAdd: function(objectToReturn, addFn) {
		return true;
	},
	afterChildDeathAdd: function(objectToReturn) {

	},
	beforeChildDeathDelete: function(objectToReturn, deleteFn) {
		return true;
	},
	afterChildDeathDelete: function(objectToReturn) {

	},

	openChildDeathEditWindow: function(action) {
		var gridPanel = this;
		var grid = gridPanel.getGrid();

		if (!action.inlist(['add', 'edit', 'view'])) {
			return false;
		}

		var objectToReturn = gridPanel.getObjectToReturn();

		if (action == 'add') {
			var check = gridPanel.beforeChildDeathAdd(objectToReturn, function(){gridPanel.openChildDeathEditWindow(action)});
			if (!check) {
				return false;
			}
		}

		var params = {};
		params.action = action;
		params.callback = function (data) {
			if (!data || !data.birthSpecStacChildDeathData) {
				return false;
			}
			data.birthSpecStacChildDeathData.RecordStatus_Code = 0;
			// Обновить запись в grid
			var record = grid.getStore().getById(data.birthSpecStacChildDeathData.ChildDeath_id);
			if (record) {
				if (record.get('RecordStatus_Code') == 1) {
					data.birthSpecStacChildDeathData.RecordStatus_Code = 2;
				}
				var grid_fields = [];
				grid.getStore().fields.eachKey(function (key, item) {
					grid_fields.push(key);
				});
				for (var i = 0; i < grid_fields.length; i++) {
					record.set(grid_fields[i], data.birthSpecStacChildDeathData[grid_fields[i]]);
				}
				record.commit();
			}
			else {
				if (grid.getStore().getCount() == 1 && !grid.getStore().getAt(0).get('ChildDeath_id')) {
					grid.getStore().removeAll();
				}
				data.birthSpecStacChildDeathData.ChildDeath_id = -swGenTempId(grid.getStore());
				grid.getStore().loadData([data.birthSpecStacChildDeathData], true);
				grid.getView().focusRow(0);
				grid.getSelectionModel().selectFirstRow();
				objectToReturn.ChildDeath_id = data.birthSpecStacChildDeathData.ChildDeath_id;
				gridPanel.afterChildDeathAdd(objectToReturn);
			}
		}
		if (action == 'add') {
			params.formParams = {};
			//Который по счету: максимум по введенным + 1
			params.formParams['ChildDeath_Count'] = objectToReturn.BirthSpecStac_CountChild + 1;
			params.formParams['ChildTermType_id'] = objectToReturn.ChildTermType_id;
			params.onHide = function () {
				if (grid.getStore().getCount() > 0) {
					grid.getView().focusRow(0);
					grid.getSelectionModel().selectFirstRow();
				}
			};
		}
		else {
			if (!grid.getSelectionModel().getSelected()) {
				return false;
			}
			var selected_record = grid.getSelectionModel().getSelected();
			params.formParams = selected_record.data;
			params.onHide = function () {
				grid.getView().focusRow(grid.getStore().indexOf(selected_record));
				grid.getSelectionModel().selectFirstRow();
			};
		}
		getWnd('swBirthSpecStacChildDeathEditWindow').show(params);
	},

	deleteChildDeath: function() {
		var gridPanel = this;
		var grid = gridPanel.getGrid();
		var objectToReturn = gridPanel.getObjectToReturn();

		var record = grid.getSelectionModel().getSelected();
		if (!record) {
			return false;
		}
		if (record.get('PntDeathSvid_id')) {
			sw.swMsg.alert('Ошибка удаления', 'Нельзя удалить эту запись, т.к. выписано свидетельство о смерти');
			return false;
		}

		var check = gridPanel.beforeChildDeathDelete(objectToReturn, function(){gridPanel.deleteChildDeath()});
		if (!check) {
			return false;
		}

		sw.swMsg.show({
			buttons:Ext.Msg.YESNO,
			fn:function (buttonId, text, obj) {
				if (buttonId == 'yes') {
					switch (Number(record.get('RecordStatus_Code'))) {
						case 0:
							if (record.get('PntDeathSvid_id')) {
								var PntDeathSvid_id = record.get('PntDeathSvid_id');
								Ext.Ajax.request({
									callback:function (options, success, response) {
										if (!success) {
											sw.swMsg.alert('Ошибка', 'При удалении свидетельства о перинатальной смерти возникли ошибки');
										} else {
											grid.getStore().remove(record);
											gridPanel.afterChildDeathDelete(objectToReturn);
										}
									}.createDelegate(this),
									params:{
										PntDeathSvid_id: PntDeathSvid_id
									},
									url:'/?c=MedSvid&m=deleteMedSvidPntDeath'
								});
							} else {
								grid.getStore().remove(record);
								gridPanel.afterChildDeathDelete(objectToReturn);
							}
							break;

						case 1:
						case 2:
							record.set('RecordStatus_Code', 3);
							record.commit();

							grid.getStore().filterBy(function (rec) {
								if (Number(rec.get('RecordStatus_Code')) == 3) {
									return false;
								}
								else {
									return true;
								}
							});
							gridPanel.afterChildDeathDelete(objectToReturn);
							break;
					}
				}
				if (grid.getStore().getCount() > 0) {
					grid.getView().focusRow(0);
					grid.getSelectionModel().selectFirstRow();
				}
			}.createDelegate(this),
			icon:Ext.MessageBox.QUESTION,
			msg:'Вы действительно хотите удалить эту запись?',
			title:'Вопрос'
		});
	},

	openPntDeathSvid: function() {
		var gridPanel = this;
		var grid = gridPanel.getGrid();

		var record = grid.getSelectionModel().getSelected();
		if (!record || !record.get('ChildDeath_id')) {
			return false;
		}

		var data = gridPanel.getObjectToReturn();
		var formParams = {};
		var action = 'view';
		var plodCount = data.BirthSpecStac_CountChild;

		if (Ext.isEmpty(record.get('PntDeathSvid_id'))) {
			action = 'add';
			formParams.Person_id = data.Person_id;	//ИД матери
			formParams.Server_id = data.Server_id;
			formParams.LpuSection_id = data.LpuSection_id;
			formParams.MedStaffFact_id = data.MedStaffFact_id;
			formParams.PntDeathSvid_ChildBirthDT_Date = data.BirthSpecStac_OutcomDate;
			formParams.PntDeathSvid_ChildBirthDT_Time = data.BirthSpecStac_OutcomTime;
			//formParams.person_rid = f.findfield('Person_id').getValue();
			formParams.Person_r_FIO = data.Person_SurName+' '+data.Person_FirName+ ' '+data.Person_SecName;
			formParams.PntDeathSvid_PlodIndex = record.get('ChildDeath_Count');
			formParams.PntDeathSvid_PlodCount = plodCount;
			formParams.PntDeathSvid_IsMnogoplod = (plodCount > 1)?2:1;
			formParams.PntDeathTime_id = record.get('PntDeathTime_id');
			formParams.Diag_iid = record.get('Diag_id');
			formParams.Sex_id = record.get('Sex_id');
		} else {
			formParams.PntDeathSvid_id = record.get('PntDeathSvid_id');
		}

		getWnd('swMedSvidPntDeathEditWindow').show({
			action: action,
			formParams: formParams,
			focusOnfield: 'BirthSvid_ChildCount',
			callback: function (svid_id, PntDeathSvid_Num) {
				record.set('PntDeathSvid_id', svid_id);
				record.set('PntDeathSvid_Num', PntDeathSvid_Num);
				if (!record.get('RecordStatus_Code').inlist([0,3])) {
					record.set('RecordStatus_Code', 2);
				}
				record.commit();
				gridPanel.focus();
			},
			onHide:function () {
				gridPanel.focus();
			}
		});
	},

	onDblClick:function () {
		if (!this.ViewActions.action_edit.isDisabled()) {
			this.ViewActions.action_edit.execute();
		}
	},
	onEnter:function () {
		if (!this.ViewActions.action_edit.isDisabled()) {
			this.ViewActions.action_edit.execute();
		}
	},
	onLoadData:function () {
		//wnd.birthFormRecalc();
	},
	onRowSelect:function (sm, index, record) {
		if (!record || Ext.isEmpty(record.get('ChildDeath_id')) || getWnd('swWorkPlaceMZSpecWindow').isVisible()) {
			this.getAction('action_pntdethsvid').disable();
		} else {
			this.getAction('action_pntdethsvid').setDisabled(!record.get('PntDeathSvid_id') && this.readOnly);
		}
	},
	stringfields:[
		{name:'ChildDeath_id', type:'int', header:'ID', key:true},
		{name:'LpuSection_id', type:'int', hidden:true},
		{name:'MedStaffFact_id', type:'int', hidden:true},
		{name:'MedStaffFact_Name', type:'string', header:'Врач', hidden:false, width:200},
		{name:'Diag_id', type:'int', hidden:true},
		{name:'Diag_Name', type:'string', header:'Диагноз', hidden:false, id: 'autoexpand'},
		{name:'Sex_id', type:'int', hidden:true},
		{name:'Sex_Name', type:'string', header:'Пол', hidden:false, width:80},
		{name:'PntDeathTime_id', type:'int', hidden:true},
		{name:'ChildTermType_id', type:'int', hidden:true},
		{name:'ChildDeath_Weight_text', type:'string', header:'Масса', width:60},
		{name:'ChildDeath_Weight', type:'float', hidden: true, width:60},
		{name:'Okei_wid', type:'int', hidden: true, width:60},
		{name:'ChildDeath_Height', type:'float', header:'Рост (см)', width:60},
		{name:'ChildDeath_Count', type:'int', header:'Который по счету', width:105},
		{name:'BirthSvid_id', type:'int', hidden:true},
		{name:'BirthSvid_Num', type:'int', header:'Св-во о рождении', width:110, hidden:true},
		{name:'PntDeathSvid_id', type:'int', hidden:true},
		{name:'PntDeathSvid_Num', type:'string', header:'Св-во о смерти', width:110, hidden:false},
		{name:'RecordStatus_Code', type:'int', hidden:true}
	],

	initActions: function() {
		var gridPanel = this;

		gridPanel.addActions({
			name: 'action_pntdethsvid',
			text: 'Мед. св-во о перинат. смерти',
			disabled: getWnd('swWorkPlaceMZSpecWindow').isVisible(),
			handler: function () {gridPanel.openPntDeathSvid()}
		});
	},

	initComponent: function() {
		var gridPanel = this;

		gridPanel.actions = [
			{name:'action_add', handler: function(){gridPanel.openChildDeathEditWindow('add')}},
			{name:'action_edit', handler: function(){gridPanel.openChildDeathEditWindow('edit')}},
			{name:'action_view', handler: function(){gridPanel.openChildDeathEditWindow('view')}},
			{name:'action_delete', handler: function(){gridPanel.deleteChildDeath()}},
			{name:'action_refresh', disabled: false, hidden: true},
			{name:'action_print', disabled: false, hidden: true}
		];

		if (!gridPanel.getObjectToReturn) {
			gridPanel.getObjectToReturn = function() {
				return {
					BirthSpecStac_OutcomDate: null,
					BirthSpecStac_OutcomTime: null,
					BirthSpecStac_CountChild: null,
					Server_id: null,
					LpuSection_id: null,
					MedStaffFact_id: null,
					Person_id: null,
					Person_SurName: null,
					Person_FirName: null,
					Person_SecName: null,
					ChildTermType_id: null
				};
			};
		}

		sw.Promed.ChildDeathGridPanel.superclass.initComponent.apply(this, arguments);
	}
});