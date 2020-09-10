/**
 * swAccessRightslpuEditWindow - окно редактирования групп МО для ограничения доступа
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Admin
 * @access			public
 * @copyright		Copyright (c) 2014 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			19.09.2014
 */

/*NO PARSE JSON*/

sw.Promed.swAccessRightsLpuEditWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'swAccessRightsLpuEditWindow',
	width: 640,
	autoHeight: true,
	modal: true,

	formStatus: 'edit',
	action: 'view',
	callback: Ext.emptyFn,

	listeners: {
		'hide': function() {
			var base_form = this.FormPanel.getForm();
			var LpuPanel = this.findById('ARDEW_LpuPanel');
			LpuPanel.items.each(function(fieldSet){
				fieldSet.items.each(function(item) {
					if(item.buildingPanel){
						Ext.getCmp('swAccessRightsLpuEditWindow').deleteLpuBuildingFieldsAll(item.buildingPanel, true);
					}
					base_form.items.removeKey(item.id);
				});
			});
			LpuPanel.removeAll();
			this.FormPanel.initFields();
			this.syncShadow();
		}
	},

	doSave: function(options) {
		options = options || {};
		var base_form = this.FormPanel.getForm();
		var LpuPanel = this.findById('ARDEW_LpuPanel');

		if ( !base_form.isValid() ) {
			sw.swMsg.show(
				{
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
		if ( LpuPanel.items.getCount() == 0 ) {
			sw.swMsg.show(
				{
					buttons: Ext.Msg.OK,
					icon: Ext.Msg.WARNING,
					msg: lang['doljna_byit_ukazana_hotya_byi_odna_mo'],
					title: ERR_INVFIELDS_TIT
				});
			return false;
		}

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT_SAVE });
		//loadMask.show();

		var values = base_form.getValues();
		var AccessRightsData = [];
		var LpuCodes = [];
		var index = 0;

		for (var num in this.LpuState) {
			var lpu_state = this.LpuState[num];

			if (lpu_state.status == 1) {
				for (var field in lpu_state.origValues) {
					if (values[field+'_'+num] && values[field+'_'+num] != lpu_state.origValues[field]) {
						lpu_state.status = 2;
						break;
					}
				}
			}

			// найдем подразделения
			var lpuBuildingPanel = LpuPanel.findById('ARDEW_LpuBuildingPanel_'+num);
			var arrLpuBuilding = [];
			var combosLBA = (lpuBuildingPanel) ? lpuBuildingPanel.find('xtype', 'swlpubuildingglobalcombo') : [];
			if(combosLBA.length>0){
				combosLBA.forEach(function(item, i, combosLBA){
					var LpuBuilding_id = item.getValue();
					if(LpuBuilding_id){
						arrLpuBuilding.push(LpuBuilding_id);
					}
				}, this);
			}

			switch (lpu_state.status) {
				case 0:
				case 1:
					AccessRightsData.push({
						AccessRightsLpu_id: values['AccessRightsLpu_id_'+num],
						Lpu_id: values['Lpu_id_'+num],
						RecordStatus_Code: lpu_state.status,
						AccessRightsLpuBuildingData: Ext.util.JSON.encode(arrLpuBuilding)
					});
					break;
				case 2:
					AccessRightsData.push({
						AccessRightsLpu_id: values['AccessRightsLpu_id_'+num],
						Lpu_id: values['Lpu_id_'+num],
						RecordStatus_Code: lpu_state.status,
						AccessRightsLpuBuildingData: Ext.util.JSON.encode(arrLpuBuilding)
					});
					break;
				case 3:
					AccessRightsData.push({
						AccessRightsLpu_id: lpu_state.AccessRightsLpu_id,
						Lpu_id: null,
						RecordStatus_Code: lpu_state.status
					});
					break;
			}

		}

		var data = {
			AccessRightsName_id: values.AccessRightsName_id,
			AccessRightsName_Name: values.AccessRightsName_Name,
			AccessRightsType_id: 2,
			AccessRightsData: Ext.util.JSON.encode(AccessRightsData)
		};

		if (options.allowIntersection) {
			data.allowIntersection = options.allowIntersection;
		}

		Ext.Ajax.request({
			params: data,
			url: this.FormPanel.url,
			failure: function() {
				loadMask.hide();
			},
			success: function(response) {
				loadMask.hide();
				var responseObj = Ext.util.JSON.decode(response.responseText);
				if (responseObj.success) {
					this.callback();
					this.hide();
				} else if (responseObj.Alert_Msg) {
					sw.swMsg.show({
						buttons: Ext.Msg.OKCANCEL,
						fn: function ( buttonId ) {
							if ( buttonId == 'ok' ) {
								switch ( responseObj.Alert_Code ) {
									case 1:
										options.allowIntersection = 1;
										break;
								}
								this.doSave(options);
							}
						}.createDelegate(this),
						msg: responseObj.Alert_Msg,
						title: lang['vopros']
					});
				} else if (responseObj.Error_Msg) {
					sw.swMsg.alert(lang['oshibka'], responseObj.Error_Msg);
				}
			}.createDelegate(this)
		});
	},
	deleteLpuBuildingFieldsAll: function(id_num, hide){
		var hide = hide || false;
		if(!id_num) return false;
		var num = id_num;
		var formLpu = this.FormPanel.getForm();
		var LpuBuildingPanel = this.findById('ARDEW_LpuBuildingPanel_'+num);
		LpuBuildingPanel.items.each(function(fieldSet){
			formLpu.items.each(function(item) {
				formLpu.items.removeKey(item.id);
			});
		});
		var combos = LpuBuildingPanel.find('xtype', 'swlpubuildingglobalcombo');
		var lba = this.LpuState[num]['LpuBuildingArr'];
		if(combos.length>0){
			combos.forEach(function(item, i, combos){
				if(item.hiddenName && this.LpuBuildingArr){
					this.LpuBuildingArr.splice(this.LpuBuildingArr.indexOf(item.hiddenName), 1); //даляем из массива значений
					this.LpuBuildingNum--;
				}
			}, this);
		}
		LpuBuildingPanel.removeAll();
		if(!hide){
			this.FormPanel.initFields();
			this.syncShadow();
		}
	},
	deleteLpuBuildingField: function(id_num) {
		if(!id_num) return false;
		var wnd = this;
		var base_form = this.FormPanel.getForm();
		var LpuBuildingPanel = this.findById('ARDEW_LpuBuildingPanel_'+id_num);
		if(!LpuBuildingPanel) return false;

		var elCombos = LpuBuildingPanel.find('xtype', 'swlpubuildingglobalcombo');
		var num = elCombos.length;
		if(num < 1) return false;
		
		var fromBuilding = LpuBuildingPanel.items.items[num-1];
		
		if(!fromBuilding) return false;
		fromBuilding.items.each(function(item) {
			wnd.FormPanel.getForm().items.removeKey(item.id);
		});

		this.LpuBuildingArr.splice(this.LpuBuildingArr.indexOf(elCombos[num-1].hiddenName), 1); //даляем из массива значений

		LpuBuildingPanel.remove(fromBuilding.id);
		wnd.LpuBuildingNum--;
		wnd.doLayout();
		wnd.syncShadow();
		wnd.FormPanel.initFields();
	},
	addLpuBuildingFieldSet: function(id_num, lpuBuilding_id) {
		if(!id_num) return false;
		var wnd = this;
		var base_form = this.FormPanel.getForm();
		var lpu_id = base_form.findField('Lpu_id_'+id_num).getValue();
		if(!lpu_id) return false;
		var LpuBuildingPanel = this.findById('ARDEW_LpuBuildingPanel_'+id_num);
		var accessRightsLpu_id = base_form.findField('AccessRightsLpu_id_'+id_num).getValue();
		if(!LpuBuildingPanel) return false;

		wnd.LpuBuildingNum++;
		var num = wnd.LpuBuildingNum;

		var config = {
			layout: 'form',
			id: 'lpuBuilding_'+num,
			autoHeight: true,
			cls: 'AccessRigthsFieldSet',
			width: 534,
			style: 'padding: 5px; margin-top: 5px;',
			items: []
		};

		config.items = [
			{
				xtype: 'hidden',
				name: 'AccessRightsLpuBuilding_id_'+num
			}, {
				allowBlank: false,
				xtype: 'swlpubuildingglobalcombo',
				fieldLabel: 'Подразделение: ',
				labelSeparator: '',
				hiddenName: 'LpuBuilding_'+num,
				accessRightsLpu_id: (accessRightsLpu_id) ? accessRightsLpu_id : '',
				anchor: '94%'
			}
		];

		var LpuBuildingFieldSet = LpuBuildingPanel.add(config);
		this.doLayout();
		this.syncSize();
		this.FormPanel.initFields();

		var buildingCombo = base_form.findField('LpuBuilding_'+num);

		swLpuBuildingGlobalStore.clearFilter();
		swLpuBuildingGlobalStore.filterBy(function(rec) {
			return (rec.get('Lpu_id') == lpu_id);
		});
		buildingCombo.getStore().loadData(getStoreRecords(swLpuBuildingGlobalStore));

		this.LpuBuildingArr[num]  = 'LpuBuilding_'+num;
		if(lpuBuilding_id) buildingCombo.setValue(lpuBuilding_id);
	},

	addLpuFieldSet: function(options) {
		var wnd = this;
		var base_form = this.FormPanel.getForm();
		var LpuPanel = this.findById('ARDEW_LpuPanel');

		this.LpuLastNum++;
		var num = this.LpuLastNum;

		this.LpuState[num] = {
			status: 0,
			AccessRightsLpu_id: null,
			origValues: {Lpu_id: null}
		};

		var config = {
			layout: 'form',
			id: 'LpuFieldSet_'+num,
			autoHeight: true,
			cls: 'AccessRigthsFieldSet',
			width: 570,
			style: 'border: 1px solid #CCC; padding: 5px; margin-top: 10px;',
			items: []
		};

		config.items = [{
			html: '<div id="LpuHeader_'+num+'" class="AccessRightsFieldSetHeader">' +
				'<div class="AccessRightsFieldSetBlock"></div>' +
				'<div class="AccessRightsFieldSetLabel">Медицинская организация</div>' +
				'<div class="AccessRightsFieldSetLine" style="width: 325px;"></div>' +
				'</div>',
			style: 'margin-bottom: 5px;'
		}, {
			xtype: 'hidden',
			name: 'AccessRightsLpu_id_'+num
		}, {
			allowBlank: false,
			xtype: 'swlpusearchcombo',
			fieldLabel: '',
			labelSeparator: '',
			hiddenName: 'Lpu_id_'+num,
			anchor: '98%',
			num: num,
			oldID: '',
			listeners: {
				'beforeselect': function( combo, record, index ){
					var base_form = Ext.getCmp('swAccessRightsLpuEditWindow');
					var existenceRecord = false;
					var id = record.id;
					var lpuCombos = base_form.find('xtype', 'swlpusearchcombo');
					lpuCombos.forEach(function(item, i, lpuCombos){
						if(item.id != this.id && id == item.value){
							existenceRecord = true;
						}
					}, this);
					if(existenceRecord) {
						combo.collapse();
						sw.swMsg.show({
							buttons: Ext.Msg.OK,
							icon: Ext.Msg.WARNING,
							msg: 'Медицинская организация "' + record.get('Lpu_Name') + '"" уже выбрана в группе',
							title: 'выбор медицинской организации'
						});
						return false;
					}
				},
				select: function(combo,record){
					var num = this.num;
					var base_form = Ext.getCmp('swAccessRightsLpuEditWindow');

					var elLpuFieldSet = this.ownerCt;
					if(elLpuFieldSet){
						var addButton = elLpuFieldSet.findById('blockLpuBuildingAddButton_'+num);
						var deleteButton = elLpuFieldSet.findById('blockLpuBuildingDeleteButton_'+num);
						if (!record.id) {
							base_form.deleteLpuBuildingFieldsAll(num);
							addButton.setDisabled(true);
							deleteButton.setDisabled(true);
							this.oldID ='';
							return false;
						}
						if(record.id != this.oldID){
							base_form.deleteLpuBuildingFieldsAll(num);
							this.oldID = record.id;
						}
						addButton.setDisabled(false);
						deleteButton.setDisabled(false);
					}
				},
				'change': function (combo, newValue, oldValue) {
					this.oldValue = newValue;
				}
			}
		},{
			layout: 'form',
			id: 'ARDEW_LpuBuildingPanel_'+num,
			buildingPanel: num,
			cls: 'AccessRigthsPanel',
			autoHeight: true,
			style: 'padding: 5px;',
			items: []
		},{
			layout: 'column',
			id: 'blockLpuBuilding_'+num,
			hiddenName: 'blockLpuBuilding',
			cls: 'AccessRigthsFieldSet',
			height: 25,
			style: 'margin-left: 100px; margin-top: 10px;',
			items: [
			
				{
					layout: 'form',
					style: 'margin-left: 20px;',
					items: [{
						xtype: 'button',
						iconCls:'add16',
						id: 'blockLpuBuildingAddButton_'+num,
						text: langs('Добавить подразделение'),
						handler: function(c) {
							this.addLpuBuildingFieldSet(num);
						}.createDelegate(this)
					}]
				}, 
			
				{
					layout: 'form',
					style: 'margin-left: 0px',
					items: [{
						xtype: 'button',
						iconCls:'delete16',
						id: 'blockLpuBuildingDeleteButton_'+num,
						text: langs('Удалить подразделение'),
						handler: function() {
							this.deleteLpuBuildingField(num);
						}.createDelegate(this)
					}]
				}
			]
		}];

		var LpuFieldSet = LpuPanel.add(config);
		this.doLayout();
		this.syncSize();
		this.FormPanel.initFields();
		base_form.findField('Lpu_id_'+num).getStore().loadData(getStoreRecords(this.LpuStore));

		if (options && options.data) {
			this.LpuState[num].status = options.data.RecordStatus_Code;
			this.LpuState[num].AccessRightsLpu_id = options.data.AccessRightsLpu_id;
			this.LpuState[num].origValues.Lpu_id = options.data.Lpu_id;

			base_form.findField('AccessRightsLpu_id_'+num).setValue(options.data.AccessRightsLpu_id);
			this.setLpuByName('Lpu_id_'+num, options.data.Lpu_id);
		}

		var delButton = new Ext.Button({
			iconCls:'delete16',
			text: lang['udalit'],
			style: 'display: inline-block; vertical-align: middle;',
			handler: function()
			{
				if (wnd.LpuState[num].status != 0) {
					wnd.LpuState[num].status = 3;
				} else {
					delete wnd.LpuState[num];
				}

				LpuFieldSet.items.each(function(item) {
					wnd.FormPanel.getForm().items.removeKey(item.id);
				});
				LpuPanel.remove(LpuFieldSet.id);
				wnd.doLayout();
				wnd.syncShadow();
				wnd.FormPanel.initFields();
			}
		});
		delButton.render('LpuHeader_'+num);

		//подразделения
		if(options && options.data.AccessRightsLpuBuildingData){
			var lbArr = options.data.AccessRightsLpuBuildingData;
			lbArr.forEach(function(item, i, lbArr){
				this.addLpuBuildingFieldSet(num, item);
			}, this);
		}
		wnd.doLayout();
	},

	setLpuByName: function(name, value) {
		var field = this.FormPanel.getForm().findField(name);
		field.setValue(value);
	},

	show: function() {
		sw.Promed.swAccessRightsLpuEditWindow.superclass.show.apply(this, arguments);

		var base_form = this.FormPanel.getForm();

		base_form.reset();

		this.LpuLastNum = 0;
		this.LpuBuildingNum = 0;
		this.LpuState = {};
		this.LpuBuildingArr = [];
		this.callback = Ext.emptyFn;
		this.action = 'view';

		if (arguments[0].action) {
			this.action = arguments[0].action;
		}
		if (arguments[0].callback) {
			this.callback = arguments[0].callback;
		}
		if (arguments[0].formParams) {
			base_form.setValues(arguments[0].formParams);
		}

		this.LpuStore = new sw.Promed.SwLpuSearchCombo().getStore();

		var loadMask = new Ext.LoadMask(this.getEl(),{msg: LOAD_WAIT});
		loadMask.show();
		switch (this.action)
		{
			case 'add':
				this.setTitle(langs('Группа МО/Подразделений МО: Добавление'));
				this.enableEdit(true);

				this.LpuStore.load();

				loadMask.hide();
				break;

			case 'edit':
			case 'view':
				if (this.action == 'edit') {
					this.setTitle(langs('Группа МО/Подразделений МО: Редактирование'));
					this.enableEdit(true);
				} else {
					this.setTitle(langs('Группа МО/Подразделений МО: Просмотр'));
					this.enableEdit(false);
				}

				base_form.load({
					url: '/?c=AccessRightsLpu&m=loadAccessRightsForm',
					params: {AccessRightsName_id: base_form.findField('AccessRightsName_id').getValue()},
					failure: function() {
						loadMask.hide();
						//
					},
					success: function(form, action) {
						if (action.result.data.AccessRightsData) {
							var LpuData = Ext.util.JSON.decode(action.result.data.AccessRightsData);

							this.LpuStore.load({
								callback: function(){
									for (var i = 0; i < LpuData.length; i++) {
										this.addLpuFieldSet({data: LpuData[i]});
									}
								}.createDelegate(this)
							});
						}
						loadMask.hide();
					}.createDelegate(this)
				});
				break;
		}
	},

	initComponent: function() {
		this.FormPanel = new Ext.form.FormPanel({
			bodyBorder: false,
			border: false,
			buttonAlign: 'left',
			frame: true,
			id: 'ARDEW_AccessRightsLpuEditForm',
			url: '/?c=AccessRightsLpu&m=saveAccessRights',
			bodyStyle: 'padding: 10px 5px 10px 20px;',
			labelAlign: 'right',
			items: [{
				xtype: 'hidden',
				name: 'AccessRightsName_id'
			}, {
				allowBlank: false,
				xtype: 'textfield',
				fieldLabel: lang['nazvanie_gruppyi'],
				name: 'AccessRightsName_Name',
				width: 320
			}, {
				layout: 'form',
				id: 'ARDEW_LpuPanel',
				cls: 'AccessRigthsPanel',
				autoHeight: true,
				items: []
			}, {
				layout: 'column',
				id: 'ARDEW_ButtonLpuPanel',
				cls: 'AccessRigthsFieldSet',
				height: 25,
				style: 'margin-top: 10px; margin-right: 20px;',
				items: [{
					layout: 'form',
					style: 'margin-left: 10px',
					items: [{
						xtype: 'button',
						iconCls:'add16',
						text: lang['dobavit_mo'],
						handler: function() {
							this.addLpuFieldSet();
						}.createDelegate(this)
					}]
				}]
			}],
			reader: new Ext.data.JsonReader({
				success: function() {
					//
				}
			}, [
				{name: 'AccessRightsName_id'},
				{name: 'AccessRightsName_Name'},
				{name: 'AccessRightsData'}
			]),
			keys: [{
				fn: function(e) {
					this.doSave();
				}.createDelegate(this),
				key: Ext.EventObject.ENTER,
				stopEvent: true
			}]
		});

		Ext.apply(this, {
			items: [
				this.FormPanel
			],
			buttons: [
				{
					text: BTN_FRMSAVE,
					id: 'ARDEW_ButtonSave',
					tooltip: lang['sohranit'],
					iconCls: 'save16',
					handler: function()
					{
						this.doSave();
					}.createDelegate(this)
				}, {
					text: '-'
				},
				HelpButton(this, 1),
				{
					handler: function () {
						this.hide();
					}.createDelegate(this),
					iconCls: 'cancel16',
					id: 'ARDEW_CancelButton',
					text: lang['otmenit']
				}]
		});

		sw.Promed.swAccessRightsLpuEditWindow.superclass.initComponent.apply(this, arguments);
	}
});