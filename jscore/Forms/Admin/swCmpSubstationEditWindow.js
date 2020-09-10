/**
 * swCmpSubstationEditWindow - окно редактирвания справочной информации о подстанции
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      	Admin
 * @access       	public
 * @copyright		Copyright (c) 2014 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			05.06.2015
 */
/*NO PARSE JSON*/

sw.Promed.swCmpSubstationEditWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'swCmpSubstationEditWindow',
	maximizable: true,
	maximized: false,
	modal: true,
	layout: 'border',

	doSave: function(options) {
		options = options || {};

		var base_form = this.FormPanel.getForm();
		var grid = this.CmpEmergencyTeamGrid.getGrid();

		if ( !base_form.isValid() ){
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function()
				{
					this.FormPanel.getFirstInvalidEl().focus(true);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		var lpu_structure_combo = base_form.findField('LpuStructure_id');
		var record = lpu_structure_combo.getStore().getById(lpu_structure_combo.getValue());

		var params = {
			Lpu_uid: record.get('Lpu_id'),
			LpuBuilding_id: record.get('LpuBuilding_id'),
			LpuUnit_id: record.get('LpuUnit_id'),
			LpuSection_id: record.get('LpuSection_id')
		};

		grid.getStore().clearFilter();
		if ( grid.getStore().getCount() > 0 ) {
			var CmpEmergencyTeamData = getStoreRecords(grid.getStore(),{exceptionFields: ['Profil_Name']});

			for(var i=0; i<CmpEmergencyTeamData.length; i++) {
				if (base_form.findField('CmpSubstation_id').getValue() != CmpEmergencyTeamData[i].CmpSubstation_id) {
					CmpEmergencyTeamData[i].CmpSubstation_id = base_form.findField('CmpSubstation_id').getValue();
					if (CmpEmergencyTeamData[i].RecordStatus_Code == 1) {
						CmpEmergencyTeamData[i].RecordStatus_Code = 2;
					}
				}
			}

			params.CmpEmergencyTeamData = Ext.util.JSON.encode(CmpEmergencyTeamData);

			grid.getStore().filterBy(function(rec) {
				return (Number(rec.get('RecordStatus_Code')) != 3);
			});
		}

		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение..."});
		loadMask.show();

		base_form.submit({
			params: params,
			failure: function(result_form, action)
			{
				loadMask.hide();
			}.createDelegate(this),
			success: function(result_form, action)
			{
				loadMask.hide();
				base_form.findField('CmpSubstation_id').setValue(action.result.CmpSubstation_id);
				grid.getStore().baseParams.CmpSubstation_id = action.result.CmpSubstation_id;

				this.hide();
				Ext.getCmp('LpuPassportEditWindow').findById('LPEW_CmpSubstationGrid').loadData();
			}.createDelegate(this)
		});
	},

	openCmpEmergencyTeamEditWindow: function(action) {
		if ( !action.inlist(['add','edit','view']) ) {
			return false;
		}
		var grid = this.CmpEmergencyTeamGrid.getGrid();
		var base_form = this.FormPanel.getForm();


		if (action != 'add') {
			var record = grid.getSelectionModel().getSelected();
			if (!record || Ext.isEmpty(record.get('CmpEmergencyTeam_id'))) {
				return false;
			}
		}

		var params = new Object();

		params.action = action;
		params.callback = function(data) {
			if ( typeof data != 'object' || typeof data.CmpEmergencyTeamData != 'object' ) {
				return false;
			}
			data.CmpEmergencyTeamData.RecordStatus_Code = 0;

			var index = grid.getStore().findBy(function(rec) { return rec.get('CmpEmergencyTeam_id') == data.CmpEmergencyTeamData.CmpEmergencyTeam_id; });
			var record = grid.getStore().getAt(index);

			if ( typeof record == 'object' ) {
				if ( record.get('RecordStatus_Code') == 1 ) {
					data.CmpEmergencyTeamData.RecordStatus_Code = 2;
				}

				var grid_fields = new Array();

				grid.getStore().fields.eachKey(function(key, item) {
					grid_fields.push(key);
				});

				for (var i = 0; i < grid_fields.length; i++ ) {
					record.set(grid_fields[i], data.CmpEmergencyTeamData[grid_fields[i]]);
				}

				record.commit();
			} else {
				if ( grid.getStore().getCount() == 1 && !grid.getStore().getAt(0).get('CmpEmergencyTeam_id') ) {
					grid.getStore().removeAll();
				}
				data.CmpEmergencyTeamData.CmpEmergencyTeam_id = -swGenTempId(grid.getStore());

				var newRecord = new Ext.data.Record(data.CmpEmergencyTeamData);
				grid.getStore().loadRecords({records: [newRecord]}, {add: true}, true);
			}
		}.createDelegate(this);

		params.disallowCmpProfileIds = [];

		params.formParams = new Object();

		if ( action == 'add' ) {
			params.formParams.CmpSubstation_id = base_form.findField('CmpSubstation_id').getValue();
			params.onHide = function() {
				if ( grid.getStore().getCount() > 0 ) {
					grid.getView().focusRow(0);
				}
			};
			grid.getStore().each(function(rec){
				if (rec.get('RecordStatus_Code') != 3) {
					params.disallowCmpProfileIds.push(rec.get('CmpProfile_id'));
				}
			});
		} else {
			if ( !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('CmpEmergencyTeam_id') ) {
				return false;
			}

			var record = grid.getSelectionModel().getSelected();
			grid.getStore().each(function(rec){
				if (rec.get('RecordStatus_Code') != 3 && rec.get('CmpEmergencyTeam_id') != record.get('CmpEmergencyTeam_id')) {
					params.disallowCmpProfileIds.push(rec.get('CmpProfile_id'));
				}
			});

			params.formParams = record.data;
			params.onHide = function() {
				grid.getView().focusRow(grid.getStore().indexOf(record));
			};
		}

		getWnd('swCmpEmergencyTeamEditWindow').show(params);
	},

	deleteCmpEmergencyTeam: function() {
		var grid = this.CmpEmergencyTeamGrid.getGrid();
		var record = grid.getSelectionModel().getSelected();
		if (!record || Ext.isEmpty(record.get('CmpEmergencyTeam_id'))) {
			return false;
		}

		sw.swMsg.show({
			buttons:Ext.Msg.YESNO,
			fn:function (buttonId, text, obj) {
				if (buttonId == 'yes') {

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
				}
			},
			icon:Ext.MessageBox.QUESTION,
			msg:'Вы хотите удалить запись?',
			title:'Подтверждение'
		});
	},

	show: function() {
		sw.Promed.swCmpSubstationEditWindow.superclass.show.apply(this, arguments);

		var base_form = this.FormPanel.getForm();
		var grid = this.CmpEmergencyTeamGrid.getGrid();

		base_form.reset();
		grid.getStore().removeAll();

		if (!arguments[0] || !arguments[0].action || !arguments[0].Lpu_id) {
			Ext.Msg.alert('Ошибка', 'Отсутствуют необходимые параметры');
			this.hide();
			return false;
		}
		this.action = arguments[0].action;
		base_form.findField('Lpu_uid').setValue(arguments[0].Lpu_id);

		if (arguments[0].CmpSubstation_id) {
			base_form.findField('CmpSubstation_id').setValue(arguments[0].CmpSubstation_id);
		}

		base_form.items.each(function(f){f.validate();});

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Подождите, идет загрузка..." });
		loadMask.show();

		switch(this.action) {
			case 'add':
				this.setTitle('Подстанция СМП: Добавление');
				this.enableEdit(true);

				base_form.findField('LpuStructure_id').getStore().load({
					params: {Lpu_id: base_form.findField('Lpu_uid').getValue()}
				});

				loadMask.hide();
				break;

			case 'edit':
			case 'view':
				if (this.action=='edit') {
					this.setTitle('Подстанция СМП: Редактирование');
					this.enableEdit(true);
					this.CmpEmergencyTeamGrid.setReadOnly(false);
				} else {
					this.setTitle('Подстанция СМП: Просмотр');
					this.enableEdit(false);
					this.CmpEmergencyTeamGrid.setReadOnly(true);
				}

				base_form.load({
					url: '/?c=LpuPassport&m=loadCmpSubstationForm',
					params: {CmpSubstation_id: base_form.findField('CmpSubstation_id').getValue()},
					success: function (cmp, frm)
					{
						var result = frm.result.data;
						
						base_form.findField('CMPSubstation_IsACS').setValue(result.CMPSubstation_IsACS==2);
						
						base_form.findField('LpuStructure_id').getStore().load({
							params: {Lpu_id: base_form.findField('Lpu_uid').getValue()},
							callback: function() {	
								base_form.findField('LpuStructure_id').setValue(base_form.findField('LpuStructure_id').getValue());								
							}
						});

						grid.getStore().load({
							params: {CmpSubstation_id: base_form.findField('CmpSubstation_id').getValue()}
						});

						loadMask.hide();
					}.createDelegate(this),
					failure: function (form,action)
					{
						loadMask.hide();
						Ext.Msg.alert('Ошибка', 'Ошибка запроса к серверу. Попробуйте повторить операцию.');
					}.createDelegate(this)
				});
				break;
		}
	},

	initComponent: function() {
		this.FormPanel = new Ext.FormPanel({
			frame: true,
			id: 'CSEW_FormPanel',
			region: 'north',
			autoHeight: true,
			labelAlign: 'right',
			url: '/?c=LpuPassport&m=saveCmpSubstation',
			labelWidth: 270,
			items: [{
				xtype: 'hidden',
				name: 'CmpSubstation_id'
			}, {
				xtype: 'hidden',
				name: 'Lpu_uid'
			}, {
				autoCreate: {tag: "input", maxLength: "9", autocomplete: "off"},
				allowBlank: false,
				allowNegative: false,
				allowDecimals: false,
				xtype: 'numberfield',
				name: 'CmpSubstation_Code',
				fieldLabel: 'Код'
			}, {
				allowBlank: false,
				xtype: 'textfield',
				name: 'CmpSubstation_Name',
				fieldLabel: 'Наименование',
				width: 300
			}, {
				allowBlank: false,
				xtype: 'swlpustructureelementcombo',
				hiddenName: 'LpuStructure_id',
				fieldLabel: 'Уровень структуры МО',
				width: 300
			},
			{
				allowBlank: false,
				xtype: 'swcommonsprcombo',
				comboSubject: 'CmpStationCategory',
				hiddenName: 'CmpStationCategory_id',
				fieldLabel: 'Категорийность станции',
				width: 260
			},
			{
				xtype: 'checkbox',
				fieldLabel: 'Оснащена АСУ приема и обработки вызова',
				name:'CMPSubstation_IsACS'
			},
			],
			reader: new Ext.data.JsonReader({
				success: function(){
					//
				}
			}, [
				{name: 'CmpSubstation_id'},
				{name: 'Lpu_id'},
				{name: 'CmpSubstation_Code'},
				{name: 'CmpSubstation_Name'},
				{name: 'LpuStructure_id'},
				{name: 'CmpStationCategory_id'},
				{name: 'CMPSubstation_IsACS'}
			])
		});

		this.CmpEmergencyTeamGrid = new sw.Promed.ViewFrame({
			title: 'Бригады СМП',
			id: 'CSEW_CmpEmergencyTeamGrid',
			dataUrl: '/?c=LpuPassport&m=loadCmpEmergencyTeamGrid',
			border: true,
			autoLoadData: false,
			useEmptyRecord: false,
			root: 'data',
			region: 'center',
			stringfields: [
				{name: 'CmpEmergencyTeam_id', type: 'int', header: 'ID', key: true},
				{name: 'RecordStatus_Code', type: 'int', hidden: true},
				{name: 'CmpSubstation_id', type: 'int', hidden: true},
				{name: 'CmpProfile_id', type: 'int', hidden: true},
				{name: 'CmpProfileTFOMS_id', type: 'int', hidden: true},
				{name: 'CmpProfile_Name', header: 'Профиль бригады', type: 'string', id: 'autoexpand'},
				{name: 'CmpProfileTFOMS_Name', header: 'Профиль бригады ТФОМС', type: 'string', id: 'autoexpand', width: 180},
				{name: 'CmpEmergencyTeam_Count', header: 'Число выездных бригад', type: 'float', width: 180}
			],
			actions: [
				{name:'action_add', handler: function(){this.openCmpEmergencyTeamEditWindow('add');}.createDelegate(this)},
				{name:'action_edit', handler: function(){this.openCmpEmergencyTeamEditWindow('edit');}.createDelegate(this)},
				{name:'action_view', handler: function(){this.openCmpEmergencyTeamEditWindow('view');}.createDelegate(this)},
				{name:'action_delete', handler: function(){this.deleteCmpEmergencyTeam();}.createDelegate(this)},
				{name:'action_refresh', disabled: true, hidden: true}
			]
		});

		Ext.apply(this, {
			buttons: [
				{
					handler: function () {
						this.doSave();
					}.createDelegate(this),
					iconCls: 'save16',
					id: 'CSEW_SaveButton',
					text: BTN_FRMSAVE
				},
				{
					text: '-'
				},
				HelpButton(this),
				{
					handler: function()
					{
						this.hide();
					}.createDelegate(this),
					iconCls: 'cancel16',
					text: BTN_FRMCLOSE
				}
			],
			items: [this.FormPanel, this.CmpEmergencyTeamGrid]
		});

		sw.Promed.swCmpSubstationEditWindow.superclass.initComponent.apply(this, arguments);
	}
});