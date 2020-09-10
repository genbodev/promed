/**
* swPersonDispTargetRateEditWindow - окно редактирования Целевых показателей
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Reg
* @access       public
* @copyright    Copyright (c) 2016 Swan Ltd.
* @author       Aleksandr Chebukin 
* @version      24.02.2016
*/

/*NO PARSE JSON*/
sw.Promed.swPersonDispTargetRateEditWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'PersonDispTargetRateEditWindow',
	layout: 'border',
	maximizable: false,
	width: 600,
	height: 350,
	modal: true,
	codeRefresh: true,
	objectName: 'swPersonDispTargetRateEditWindow',
	objectSrc: '/jscore/Forms/Polka/swPersonDispTargetRateEditWindow.js',
	deletePersonDispFactRate: function() {	
		var grid = this.FactRateGrid.getGrid();
		var	wnd = this;
		if ( !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('PersonDispFactRate_id') ) {
			return false;
		}
		var record = grid.getSelectionModel().getSelected();
		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId, text, obj) {
				if ( buttonId == 'yes' ) {
					var record = grid.getSelectionModel().getSelected();
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
			}.createDelegate(this),
			icon: Ext.MessageBox.QUESTION,
			msg: lang['udalit_znachenie'],
			title: lang['vopros']
		});	
	},
	openPersonDispFactRateEditWindow: function(action) {
		if ( typeof action != 'string' || !(action.inlist([ 'add', 'edit', 'view' ])) ) {
			return false;
		}
		if ( getWnd('swPersonDispFactRateEditWindow').isVisible() ) {
			sw.swMsg.alert(lang['oshibka'], lang['okno_redaktirovaniya_fakticheskih_znacheniy_uje_otkryito']);
			return false;
		}
		var
			formParams = new Object(),
			grid = this.FactRateGrid.getGrid(),
			params = new Object(),
			wnd = this;
		params.action = action;
		params.Lpu_id = this.Lpu_id;
		params.callback = function(data) {
			if ( typeof data != 'object' || typeof data.PersonDispFactRateData != 'object' ) {
				sw.swMsg.alert(lang['oshibka'], lang['otsutstvuyut_neobhodimyie_dannyie']);
				return false;
			}
			data.PersonDispFactRateData.RecordStatus_Code = 0;
			var index = grid.getStore().findBy(function(rec) {
				return (rec.get('PersonDispFactRate_id') == data.PersonDispFactRateData.PersonDispFactRate_id);
			});
			if ( index >= 0 ) {
				var record = grid.getStore().getAt(index);
				if ( record.get('RecordStatus_Code') == 1 ) {
					data.PersonDispFactRateData.RecordStatus_Code = 2;
				}
				var grid_fields = new Array();
				grid.getStore().fields.eachKey(function(key, item) {
					grid_fields.push(key);
				});
				for ( i = 0; i < grid_fields.length; i++ ) {
					record.set(grid_fields[i], data.PersonDispFactRateData[grid_fields[i]]);
				}
				record.commit();
			} else {
				if ( grid.getStore().getCount() == 1 && Ext.isEmpty(grid.getStore().getAt(0).get('PersonDispFactRate_id')) ) {
					grid.getStore().removeAll();
				}				
				data.PersonDispFactRateData.PersonDispFactRate_id = -swGenTempId(grid.getStore());
				grid.getStore().loadData([ data.PersonDispFactRateData ], true);
			}
			return true;
		};
		if ( action == 'add' ) {
			params.onHide = function() {
				if ( grid.getStore().getCount() > 0 ) {
					grid.getView().focusRow(0);
				}
			};
		} else {
			if ( !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('PersonDispFactRate_id') ) {
				return false;
			}

			var selectedRecord = grid.getSelectionModel().getSelected();

			formParams = selectedRecord.data;
			params.onHide = function() {
				grid.getView().focusRow(grid.getStore().indexOf(selectedRecord));
			};
		}

		params.formParams = formParams;
		getWnd('swPersonDispFactRateEditWindow').show(params);

		return true;
	},
	show: function() {		
		sw.Promed.swPersonDispTargetRateEditWindow.superclass.show.apply(this, arguments);

		var base_form = this.findById('PersonDispTargetRateEditForm').getForm();
		base_form.reset();
		this.FactRateGrid.removeAll();

		this.action = arguments[0]['action'] || 'view';
		this.RateType_id = arguments[0]['RateType_id'] || null;
		this.PersonDisp_id = arguments[0]['PersonDisp_id'] || null;
		this.returnFunc = arguments[0]['callback'] || Ext.emptyFn;
		
		switch (this.action){
			case 'edit':
				this.setTitle(lang['tselevyie_pokazateli_redaktirovanie']);
				break;
			case 'view':
				this.setTitle(lang['tselevyie_pokazateli_prosmotr']);
				break;
		}
		
		var loadMask = new Ext.LoadMask(Ext.get('PersonDispTargetRateEditForm'), { msg: "Подождите, идет загрузка..." });
		this.findById('PersonDispTargetRateEditForm').getForm().load({
			url: '/?c=PersonDisp&m=loadPersonDispTargetRate',
			params: { 
				PersonDisp_id: this.PersonDisp_id,
				RateType_id: this.RateType_id
			},
			success: function (form, action) {
				this.FactRateGrid.loadData({
					globalFilters: { 
						PersonDisp_id: this.PersonDisp_id,
						RateType_id: this.RateType_id
					}
				});
				loadMask.hide();
			},
			failure: function (form, action) {
				loadMask.hide();
				if (!action.result.success) {
					Ext.Msg.alert(lang['oshibka'], lang['oshibka_zaprosa_k_serveru_poprobuyte_povtorit_operatsiyu']);
					this.hide();
				}
			},
			scope: this
		});
		
		if (this.action=='view') {
			base_form.findField('TargetRate_Value').disable();
			this.FactRateGrid.setReadOnly(true);
			this.buttons[0].disable();
		} else {
			base_form.findField('TargetRate_Value').enable();
			this.FactRateGrid.setReadOnly(false);
			this.buttons[0].enable();
		}
		
	},
	doSave: function() 
	{
		var win = this;
		var form = this.findById('PersonDispTargetRateEditForm').getForm();
		var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Подождите, идет сохранение..." });
		
		if (!form.isValid()) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					win.MainPanel.getFirstInvalidEl().focus(false);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
		
		var params = { 
			PersonDisp_id: this.PersonDisp_id,
			RateType_id: this.RateType_id
		};
		var grid = this.FactRateGrid.getGrid();
		var PersonDispFactRateData = [];
		grid.getStore().clearFilter();
		
		if ( grid.getStore().getCount() > 0 ) {
			PersonDispFactRateData = getStoreRecords(grid.getStore());
			grid.getStore().filterBy(function(rec) {
				return (Number(rec.get('RecordStatus_Code')) != 3);
			});
		}
		
		params.PersonDispFactRateData = Ext.util.JSON.encode(PersonDispFactRateData);
		
		loadMask.show();		
		form.submit({
			params: params,
			failure: function(result_form, action) {
				loadMask.hide();
			},
			success: function(result_form, action) {
				loadMask.hide();
				if (action.result) {
					if (action.result.success) {
						win.hide();
						win.returnFunc();
					}	
				}
				else {
					Ext.Msg.alert(lang['oshibka'], lang['pri_sohranenii_pokazateley_proizoshla_oshibka']);
				}
							
			}.createDelegate(this)
		});
	},

	initComponent: function() {
	
		var win = this;
		
		this.MainPanel = new Ext.form.FormPanel({
			id:'PersonDispTargetRateEditForm',
			border: false,
			frame: true,
			autoWidth: false,
			autoHeight: false,
			bodyStyle: 'padding: 10px 5px 0',
			region: 'center',
			labelAlign: 'right',
			labelWidth: 150,
			items:
			[{
				name: 'RateValueType_SysNick',
				xtype: 'hidden'
			}, {
				fieldLabel: lang['pokazatel'],
				hiddenName: 'RateType_id',
				comboSubject: 'RateType',
				xtype: 'swcommonsprcombo',
				width: 200,
				disabled: true
			}, {
				allowBlank: false,
				fieldLabel: lang['tselevoe_znachenie'],
				name: 'TargetRate_Value',
				width: 100,
				xtype: 'numberfield'
			}],
			reader: new Ext.data.JsonReader({},[
				{ name: 'RateValueType_SysNick' },
				{ name: 'RateType_id' },
				{ name: 'TargetRate_Value' }
			]),
			url: '/?c=PersonDisp&m=savePersonDispTargetRate'
		});
		
		this.FactRateGrid =  new sw.Promed.ViewFrame({
			id: 'FactRateGrid',
			region: 'south',
			height: 200,
			dataUrl: '/?c=PersonDisp&m=loadPersonDispFactRateList',
			editformclassname: '',
			autoExpandColumn: 'autoexpand',
			useEmptyRecord: false,
			autoLoadData: false,
			saveAtOnce: false,
			stringfields: [
				{ name: 'PersonDispFactRate_id', key: true, type: 'int', hidden:true },
				{ name: 'RecordStatus_Code', hidden: true, type: 'int'},
				{ name: 'Rate_id', hidden: true, type: 'int'},
				{ name: 'PersonDispFactRate_setDT', header: lang['data_rezultata'], type: 'date', width: 150},
				{ name: 'PersonDispFactRate_Value', header: lang['fakticheskoe_znachenie'], type: 'float', id: 'autoexpand'}
			],
			actions: [
				{name: 'action_add', handler: function() { win.openPersonDispFactRateEditWindow('add'); }},
				{name: 'action_edit', handler: function() { win.openPersonDispFactRateEditWindow('edit'); }},
				{name: 'action_view', hidden: true},
				{name: 'action_delete', handler: function() { win.deletePersonDispFactRate(); }},
				{name: 'action_refresh', hidden: true},
				{name: 'action_print', hidden: true},
			],
			title: lang['fakticheskie_znacheniya']
		});
		
		Ext.apply(this, 
		{
			xtype: 'panel',
			border: false,
			items: [this.MainPanel, this.FactRateGrid],
			buttons:
			[{
				text: lang['sohranit'],
				iconCls: 'save16',
				handler: function()
				{
					this.doSave();
				}.createDelegate(this)
			},
			{
				text:'-'
			}, 
			{
				text: BTN_FRMHELP,
				iconCls: 'help16',
				handler: function(button, event) 
				{
					ShowHelp(this.title);
				}.createDelegate(this)
			},
			{
				text: BTN_FRMCANCEL,
				iconCls: 'cancel16',
				handler: function()
				{
					this.hide();
				}.createDelegate(this)
			}],
			keys: [{
				alt: true,
				fn: function(inp, e) {
					if ( e.browserEvent.stopPropagation )
						e.browserEvent.stopPropagation();
					else
						e.browserEvent.cancelBubble = true;

					if ( e.browserEvent.preventDefault )
						e.browserEvent.preventDefault();
					else
						e.browserEvent.returnValue = false;

					e.browserEvent.returnValue = false;
					e.returnValue = false;

					if (Ext.isIE) {
						e.browserEvent.keyCode = 0;
						e.browserEvent.which = 0;
					}

					if (e.getKey() == Ext.EventObject.J) {
						this.hide();
						return false;
					}

					if (e.getKey() == Ext.EventObject.C) {
						this.doSave();
						return false;
					}
				},
				key: [ Ext.EventObject.J, Ext.EventObject.C ],
				scope: this,
				stopEvent: false
			}]
		});
		sw.Promed.swPersonDispTargetRateEditWindow.superclass.initComponent.apply(this, arguments);
	}
});