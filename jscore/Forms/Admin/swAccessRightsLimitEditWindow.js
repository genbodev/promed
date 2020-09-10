/**
 * swAccessRightsLimitEditWindow - окно для предоставления доступа (к диагнозам и МО)
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Admin
 * @access			public
 * @copyright		Copyright (c) 2014 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			16.09.2014
 */

/*NO PARSE JSON*/

sw.Promed.swAccessRightsLimitEditWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'swAccessRightsLimitEditWindow',
	autoHeight: true,
	modal: true,
	width: 560,

	formStatus: 'edit',
	action: 'view',
	callback: Ext.emptyFn,

	doSave: function() {
		var base_form = this.FormPanel.getForm();

		if ( !base_form.isValid() ) {
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

		var params = {};

		var loadMask = new Ext.LoadMask(this.getEl(),{msg: LOAD_WAIT});
		loadMask.show();

		var values = base_form.getValues();
		if(this.AccessRightsLimitType == 'lpu' && values.LimitLpu_id){
			var LpuBuildingPanel = this.findById('ARLEW_LpuBuildingPanel');
			var combosLBA = LpuBuildingPanel.find('xtype', 'swlpubuildingcombo');
			if(combosLBA.length>0){
				var arrLpuBuilding = [];
				combosLBA.forEach(function(item, i, combosLBA){
					var LpuBuilding_id = item.getValue();
					if(LpuBuilding_id){
						arrLpuBuilding.push(LpuBuilding_id);
					}
				}, this);
				if(arrLpuBuilding.length>0) params.LpuBuildings = Ext.util.JSON.encode(arrLpuBuilding);
			}
		}

		base_form.submit({
			url: '/?c=AccessRights&m=saveAccessRightsLimit',
			params: params,
			failure: function() {
				loadMask.hide();
			}.createDelegate(this),
			success: function(form, action) {
				loadMask.hide();
				if (action.result.success) {
					this.callback();
					this.hide();
				}
			}.createDelegate(this)
		});
	},

	setAccessRightsLimitType: function(type) {
		var base_form = this.FormPanel.getForm();
		this.AccessRightsLimitType = type;

		var combos = {
			lpu: base_form.findField('LimitLpu_id'),
			post: base_form.findField('LimitPost_id'),
			usergroups:  base_form.findField('AccessRightsType_UserGroups')
		};

		for (var key in combos) {
			combos[key].setAllowBlank(true);
			combos[key].hideContainer();
		}

		combos[type].setAllowBlank(false);
		combos[type].showContainer();

		if (type == 'usergroups') {
			combos[type].getStore().load();
		}

		this.syncShadow();
	},

	show: function() {
		sw.Promed.swAccessRightsLimitEditWindow.superclass.show.apply(this, arguments);

		var base_form = this.FormPanel.getForm();

		base_form.reset();

		if (!arguments[0] || !arguments[0].AccessRightsName_id || !arguments[0].type) {
			Ext.Msg.alert(lang['oshibka'], lang['otsutstvuyut_neobhodimyie_parametryi']);
			this.hide();
			return false;
		}

		base_form.findField('AccessRightsName_id').setValue(arguments[0].AccessRightsName_id);

		if (arguments[0].title) {
			this.setTitle(arguments[0].title);
		} else {
			this.setTitle('');
		}

		if (arguments[0].callback) {
			this.callback = arguments[0].callback;
		} else {
			this.callback = Ext.emptyFn;
		}

		this.lpuBuildingNum = 0;
		this.LpuBuildingState = {};
		this.LpuBuildingArr = [];

		var blockLpuBuilding = this.findById('blockLpuBuilding');
		blockLpuBuilding.hide();
		// this.findById('blockLpuBuildingDeleteButton').setDisabled(true);
		// this.findById('blockLpuBuildingAddButton').setDisabled(true);

		this.setAccessRightsLimitType(arguments[0].type);
	},
	deleteLpuBuildingField: function(){
		var wnd = this;
		var base_form = this.FormPanel.getForm();

		var LpuBuildingPanel = this.findById('ARLEW_LpuBuildingPanel');
		var num = LpuBuildingPanel.items.length;
		if(num < 1) return false;

		var fromBuilding = LpuBuildingPanel.items.items[num-1];

		if(!fromBuilding) return false;
		fromBuilding.items.each(function(item) {
			wnd.FormPanel.getForm().items.removeKey(item.id);
		});
		LpuBuildingPanel.remove(fromBuilding.id);
		wnd.LpuBuildingArr.splice(num);

		wnd.doLayout();
		wnd.syncShadow();
		wnd.FormPanel.initFields();

		var combosLBA = LpuBuildingPanel.find('xtype', 'swlpubuildingcombo');
		if(combosLBA.length == 0){
			this.findById('blockLpuBuildingDeleteButton').hide();
		}
	},
	addLpuBuildingFieldSet: function(){
		var wnd = this;
		var base_form = this.FormPanel.getForm();
		var lpu_id = base_form.findField('LimitLpu_id').getValue();
		if(!lpu_id) return false;
		var LpuBuildingPanel = this.findById('ARLEW_LpuBuildingPanel');


		wnd.lpuBuildingNum++;
		var num = wnd.lpuBuildingNum;

		wnd.LpuBuildingState[num] = {
			status: 0,
			AccessRightsLpuBuilding_id: null,
			origValues: {LpuBuilding_id: null}
		}

		var config = {
			layout: 'form',
			id: 'lpuBuilding_'+num,
			autoHeight: true,
			cls: 'AccessRigthsFieldSet',
			width: 534,
			style: 'border: 1px solid #CCC; padding: 10px; margin-top: 10px;',
			items: []
		};

		config.items = [
			{
				xtype: 'hidden',
				name: 'AccessRightsLpuBuilding_id_'+num
			}, {
				allowBlank: false,
				xtype: 'swlpubuildingcombo',
				fieldLabel: 'Подразделение: ',
				labelSeparator: '',
				hiddenName: 'LpuBuilding_'+num,
				anchor: '94%',
				listeners: {
					'beforeselect': function( combo, record, index ){
						var LpuBuildingPanel = Ext.getCmp('ARLEW_LpuBuildingPanel');
						var combosLBA = LpuBuildingPanel.find('xtype', 'swlpubuildingcombo');
						var existenceRecord = false;

						var id = record.id;
						combosLBA.forEach(function(item, i, combosLBA){
							if(item.id != this.id && id == item.value){
								existenceRecord = true;
							}
						}, this);
						if(existenceRecord) {
							combo.collapse();
							sw.swMsg.show({
								buttons: Ext.Msg.OK,
								icon: Ext.Msg.WARNING,
								msg: 'Подразделение "' + record.get('LpuBuilding_Name') + '"" уже выбрано в группе',
								title: 'выбор подразделения'
							});
							return false;
						}
					}
				}
			}
		];

		var LpuBuildingFieldSet = LpuBuildingPanel.add(config);
		wnd.LpuBuildingArr[num] = 'LpuBuilding_'+num;
		this.doLayout();
		this.syncSize();
		this.FormPanel.initFields();

		var lpu_id = base_form.findField('LimitLpu_id').getValue();
		var buildingCombo = base_form.findField('LpuBuilding_'+num);

		buildingCombo.clearValue();
		
		// swLpuBuildingGlobalStore.clearFilter();
		// swLpuBuildingGlobalStore.filterBy(function(rec) {
		// 	return (rec.get('Lpu_id') == lpu_id);
		// });
		buildingCombo.getStore().loadData(getStoreRecords(swLpuBuildingGlobalStore));

		//покажем кнопку удалить
		var deleteButton = this.findById('blockLpuBuildingDeleteButton');
		if(!deleteButton.isVisible()){
			deleteButton.show();
		}
	},
	listeners: {
		'hide': function() {
			this.deleteLpuBuildingFieldsAll();
		}
	},
	deleteLpuBuildingFieldsAll: function(){
		var base_form = this.FormPanel.getForm();
		var LpuBuildingPanel = this.findById('ARLEW_LpuBuildingPanel');
		LpuBuildingPanel.items.each(function(fieldSet){
			fieldSet.items.each(function(item) {
				base_form.items.removeKey(item.id);
			});
		});
		LpuBuildingPanel.removeAll();

		var lpu_id = base_form.findField('LimitLpu_id').getValue();
		var deleteButton = this.findById('blockLpuBuildingDeleteButton');
		if(!lpu_id) {
			var blockLpuBuilding = this.findById('blockLpuBuilding');
			blockLpuBuilding.hide();
		}
		deleteButton.hide();

		this.FormPanel.initFields();
		this.syncShadow();
	},
	initComponent: function() {
		var wnd = this;
		this.FormPanel = new Ext.form.FormPanel({
			bodyBorder: false,
			border: false,
			buttonAlign: 'left',
			frame: true,
			id: 'ARLEW_AccessRightsDiagEditForm',
			bodyStyle: 'padding: 10px 0;',
			labelAlign: 'right',
			labelWidth: 140,

			items: [{
				xtype: 'hidden',
				name: 'AccessRightsName_id'
			}, 
			{
				xtype: 'swlpusearchcombo',
				hiddenName: 'LimitLpu_id',
				fieldLabel: lang['mo'],
				width: 320,
				oldID: '',
				listeners: {
					select: function(combo,record){
						var base_form = Ext.getCmp('swAccessRightsLimitEditWindow');
						var blockLpuBuilding = base_form.findById('blockLpuBuilding');

						if(!record.id) {
							blockLpuBuilding.hide();
							return false;
						}
						if(combo.oldID && combo.oldID != record.id){
							base_form.deleteLpuBuildingFieldsAll();
						}
						blockLpuBuilding.show();
					},
					'change': function (combo, newValue, oldValue) {
						combo.oldID = newValue;
						if(!newValue) return false;
						var lpu_id = newValue;
						swLpuBuildingGlobalStore.clearFilter();
						swLpuBuildingGlobalStore.filterBy(function(rec) {
							return (rec.get('Lpu_id') == lpu_id);
						});
					}
				}
			},
			{
				layout: 'form',
				id: 'ARLEW_LpuBuildingPanel',
				cls: 'AccessRigthsPanel',
				autoHeight: true,
				items: []
			}, 
			{
				layout: 'column',
				id: 'blockLpuBuilding',
				hiddenName: 'blockLpuBuilding',
				cls: 'AccessRigthsFieldSet',
				hidden: true,
				height: 25,
				style: 'margin-left: 100px; margin-top: 10px;',
				items: [
				
					{
						layout: 'form',
						style: 'margin-left: 20px;',
						items: [{
							xtype: 'button',
							iconCls:'add16',
							id: 'blockLpuBuildingAddButton',
							text: langs('Добавить подразделение'),
							handler: function() {
								this.addLpuBuildingFieldSet();
							}.createDelegate(this)
						}]
					}, 
				
					{
						layout: 'form',
						style: 'margin-left: 0px',
						items: [{
							xtype: 'button',
							iconCls:'delete16',
							id: 'blockLpuBuildingDeleteButton',
							hidden: true,
							text: langs('Удалить подразделение'),
							handler: function() {
								this.deleteLpuBuildingField();
							}.createDelegate(this)
						}]
					}
				]
			}
			, {
				xtype: 'swpostmedlocalcombo',
				hiddenName: 'LimitPost_id',
				fieldLabel: lang['doljnost_vracha'],
				tpl: '<tpl for="."><div class="x-combo-list-item">{PostMed_Name}&nbsp;</div></tpl>',
				width: 320
			}, {
				xtype: 'swusersgroupscombo',
				hiddenName: 'AccessRightsType_UserGroups',
				valueField: 'Group_Name',
				fieldLabel: lang['gruppa_polzovateley'],
				tpl: '<tpl for="."><div class="x-combo-list-item">{Group_Desc}&nbsp;</div></tpl>',
				width: 320
			}]
		});

		Ext.apply(this, {
			items: [
				this.FormPanel
			],
			buttons: [
				{
					id: 'ARLEW_ButtonSave',
					text: lang['dobavit'],
					tooltip: lang['dobavit'],
					//iconCls: 'save16',
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
					id: 'ARLEW_CancelButton',
					text: lang['otmenit']
				}]
		});

		sw.Promed.swAccessRightsLimitEditWindow.superclass.initComponent.apply(this, arguments);
	}
});