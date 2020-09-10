/**
* Форма Состав комиссии
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
*/

sw.Promed.swVoteListVKWindow = Ext.extend(sw.Promed.BaseForm, {
	title: 'Состав комиссии',
	modal: true,
	resizable: false,
	maximized: false,
	width: 900,
	height: 400,
	shim: false,
	plain: true,
	layout: 'fit',
	buttonAlign: "right",
	closeAction: 'hide',
	
	doSave: function() {
		
		var win = this,
			loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT_SAVE })
			params = {},
			chairman_cnt = 0;
		
		params.GridData = getStoreRecords(this.Grid.getGrid().getStore(), {clearFilter: true});
		this.Grid.getGrid().getStore().filterBy(function(rec){
			return (rec.get('Record_Status') != 3);
		});
		
		params.GridData.forEach(function (item) {
			if (item.EvnVKExpert_IsChairman == 2) chairman_cnt++;
		});
		
		if (chairman_cnt != 1) {
			sw.swMsg.alert('Ошибка', 'В составе комиссии должен и может быть только один эксперт с ролью «Председатель ВК»');
			return false;
		}
		
		params.EvnPrescrVK_id = this.EvnPrescrVK_id;
		params.GridData = Ext.util.JSON.encode(params.GridData);

		var lm = this.getLoadMask(LOAD_WAIT_SAVE);
		lm.show();
		Ext.Ajax.request({
			url: '/?c=VoteListVK&m=save',
			params: params,
			method: 'post',
			callback: function(opt, success, response) {
				lm.hide();
				var response_obj = Ext.util.JSON.decode(response.responseText);
				if (response_obj.success) {
					win.callback();
					win.hide();
				} 
				else if (response_obj.Error_Message) {
					sw.swMsg.alert('Ошибка', response_obj.Error_Message);
				}
			}
		});
	},
	
	show: function() {
		sw.Promed.swVoteListVKWindow.superclass.show.apply(this, arguments);
			
		if (!arguments.length) arguments = [{}];
		
		if (!arguments[0].EvnPrescrVK_id) {
			sw.swMsg.alert('Ошибка', 'Не указан идентификатор направления на ВК');
			this.hide();
			return false;
		}
		
		this.EvnPrescrVK_id = arguments[0].EvnPrescrVK_id;
		this.EvnStatusVK_id = arguments[0].EvnStatusVK_id || 47;
		this.callback = arguments[0].callback || Ext.emptyEn;
		
		var grid = this.Grid.getGrid();
		grid.getStore().baseParams = {EvnPrescrVK_id: this.EvnPrescrVK_id};
		grid.getStore().load();
		this.Grid.setReadOnly(!this.EvnStatusVK_id.inlist([47, 49, 50]));
	},
	
	deleteEvnVKExpert: function() {
		var grid = this.Grid.getGrid();
		var record = grid.getSelectionModel().getSelected();
		if (!record) return false;
		switch ( Number(record.get('RecordStatus_Code')) ) {
			case 0:
				grid.getStore().remove(record);
				break;

			case 1:
			case 2:
				record.set('RecordStatus_Code', 3);
				record.commit();
				grid.getStore().filterBy(function(rec) {
					return (Number(rec.get('RecordStatus_Code')) != 3);
				});
				break;
		}

		if ( grid.getStore().getCount() > 0 ) {
			grid.getView().focusRow(0);
			grid.getSelectionModel().selectFirstRow();
		}
	},	
	
	openEvnVKExpertWindow: function(action) {
		
		var win = this,
			grid = this.Grid.getGrid(),
			rec = grid.getSelectionModel().getSelected();
			
		if(!rec && action == 'edit')
			return false;
		
		var params = ( action == 'edit' ) ? rec.data : {};
		params.MedService_id = this.MedService_id;
		params.fromEvnVK = false;
		getWnd('swClinExWorkSelectExpertWindow').show({
			action: action,
			params: params,
			onHide: function(data) {
				if (!data) return false;
					
				data.RecordStatus_Code = 0;
					
				var record = grid.getStore().getById(data.VoteExpertVK_id);
				if (record) {
					if (record.get('RecordStatus_Code') == 1) {
						data.RecordStatus_Code = 2;
					}
					var grid_fields = [];
					grid.getStore().fields.eachKey(function(key, item) {
						grid_fields.push(key);
					});
					for (i = 0; i < grid_fields.length; i++) {
						record.set(grid_fields[i], data[grid_fields[i]]);
					}
					record.commit();
				} else {
					if (grid.getStore().getCount() == 1 && !grid.getStore().getAt(0).get('VoteExpertVK_id')) {
						grid.getStore().removeAll();
					}
					data.VoteExpertVK_id = -swGenTempId(grid.getStore());
					grid.getStore().loadData([ data ], true);
				}
			}
		});
	},
	
	initComponent: function() {
		var win = this;
		
		this.Grid = new sw.Promed.ViewFrame({
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 100,
			anchor: '100%',
			region: 'center',
			border: false,
			enableColumnHide: false,
			useEmptyRecord: false,
			autoLoadData: false,
			object: 'VoteExpertVK',
			actions: [
				{ name: 'action_add', handler: this.openEvnVKExpertWindow.createDelegate(this, ['add']) },
				{ name: 'action_edit', handler: this.openEvnVKExpertWindow.createDelegate(this, ['edit']) },
				{ name: 'action_view', hidden: true },
				{ name: 'action_delete', handler: this.deleteEvnVKExpert.createDelegate(this)},
				{ name: 'action_refresh', hidden: true },
				{ name: 'action_print', hidden: true }
			],
			stringfields: [
				{ name: 'VoteExpertVK_id', type: 'int', hidden: true, key: true },
				{ name: 'RecordStatus_Code', type: 'int', hidden: true },
				{ name: 'MedServiceMedPersonal_id', type: 'int', hidden: true },
				{ name: 'EvnVKExpert_isApproved', type: 'int', hidden: true },
				{ name: 'VoteExpertVK_isInternalRequest', type: 'int', hidden: true },
				{ name: 'ExpertMedStaffType_id', type: 'int', hidden: true },
				{ name: 'MF_Person_FIO', type: 'string',  header: 'Врач ВК', width: 180 },
				{ name: 'EvnVKExpert_IsChairman', type: 'checkbox', header: 'Председатель ВК', width: 110 },
				{ name: 'VoteExpertVK_VoteDate', type: 'string', header: 'Срок вынесения решения', width: 130},
				{ name: 'EvnVKExpert_isApprovedName', type: 'string',  header: 'Решение эксперта', width: 120, hidden: getRegionNick() != 'vologda' },
				{ name: 'EvnVKExpert_Descr', type: 'string',  header: 'Комментарий', width: 300, hidden: getRegionNick() != 'vologda', id: (getRegionNick() == 'vologda' ? 'autoexpand' : null) },
				{ name: 'VoteExpertVK_updDT', type: 'string', header: 'Дата вынесения решения', width: 130}
			],
			onRowSelect: function(sm, rowIdx, rec) {
				win.Grid.getAction('action_add').setDisabled(!win.EvnStatusVK_id.inlist([47, 49, 50]));
				win.Grid.getAction('action_edit').setDisabled(!win.EvnStatusVK_id.inlist([47, 49, 50]) || !!rec.get('EvnVKExpert_isApproved') || !!rec.get('VoteExpertVK_isInternalRequest'));
				win.Grid.getAction('action_delete').setDisabled(!win.EvnStatusVK_id.inlist([47, 49, 50]) || !!rec.get('EvnVKExpert_isApproved') || !!rec.get('VoteExpertVK_isInternalRequest'));
			},
			paging: false,
			dataUrl: '/?c=VoteListVK&m=loadList',
			totalProperty: 'totalCount'
		});
		
		Ext.apply(this,	{
			layout: 'border',
			items: [
				this.Grid
			],
			buttons: [{
				handler: function() {
					this.doSave();
				}.createDelegate(this),
				iconCls: 'save16',
				text: 'Сохранить'
			},
			'-', 
			HelpButton(this, -1), 
			{
				handler: function () {
					this.hide();
				}.createDelegate(this),
				iconCls: 'close16',
				text: BTN_FRMCANCEL
			}]
		});
		
		sw.Promed.swVoteListVKWindow.superclass.initComponent.apply(this, arguments);
	}
});