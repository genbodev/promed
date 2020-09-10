/**
 * swAccessRightsLimitUsersSelectWindow - окно выбора пользователей для предоставления доступа (к диагнозам и МО)
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Admin
 * @access			public
 * @copyright		Copyright (c) 2014 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			15.09.2014
 */

/*NO PARSE JSON*/

sw.Promed.swAccessRightsLimitUsersSelectWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'swAccessRightsLimitUsersSelectWindow',
	width: 700,
	height: 420,
	modal: true,
	border: true,

	formStatus: 'edit',
	action: 'view',
	callback: Ext.emptyFn,

	checkRenderer: function(v, p, record) {
		var id = record.get('pmUser_id');
		var value = 'value="'+id+'"';
		var checked = record.get('RecordStatus_Code') == 3 ? '' : ' checked="checked"';
		var onclick = 'onClick="getWnd(\'swAccessRightsLimitUsersSelectWindow\').checkOne(this.value);"';
		var disabled = '';

		return '<input type="checkbox" '+value+' '+checked+' '+onclick+' '+disabled+'>';
	},

	checkOne: function(id) {
		var win = this;
		var grid = this.GridPanel.getGrid();

		var record = grid.getStore().getAt(grid.getStore().findBy(function(rec) { return rec.get('pmUser_id') == id; }));
		if (record) {
			var newVal = 3;
			if (record.get('RecordStatus_Code') == 3) {
				newVal = Ext.isEmpty(record.get('AccessRightsLimit_id')) ? 0 : 1;
			}
			record.set('RecordStatus_Code', newVal);
			//record.commit();
		}

		this.refreshCheckAll();
	},

	checkAll: function(check)
	{
		if (check) {
			this.GridPanel.getGrid().getStore().each(function(record){
				record.set('RecordStatus_Code', Ext.isEmpty(record.get('AccessRightsLimit_id')) ? 0 : 1);
				//record.commit();
			});
		} else {
			this.GridPanel.getGrid().getStore().each(function(record){
				record.set('RecordStatus_Code', 3);
				//record.commit();
			});
		}
	},

	refreshCheckAll: function() {
		var grid = this.GridPanel.getGrid();
		var is_all_checked = true;

		if (grid.getStore().getCount() > 0) {
			grid.getStore().each(function(record){
				if (record.get('RecordStatus_Code') == 3) {
					is_all_checked = false;
					return false;
				}
			});
		} else {
			is_all_checked = false;
		}

		Ext.get('ARLUSW_checkAll').dom.checked = is_all_checked;
	},

	doSave: function() {
		var grid = this.GridPanel.getGrid();
		var base_form = this.FormPanel.getForm();

		var params = {AccessRightsLimitUsersData: ''};
		var data = [];

		grid.getStore().each(function(record){
			if (record.get('RecordStatus_Code') == 0 || (record.get('RecordStatus_Code') == 3 && !Ext.isEmpty(record.get('AccessRightsLimit_id')))) {
				data.push({
					AccessRightsLimit_id: record.get('AccessRightsLimit_id'),
					AccessRightsType_User: record.get('pmUser_id').toString(),
					RecordStatus_Code: record.get('RecordStatus_Code')
				});
			}
		});

		params.AccessRightsLimitUsersData = Ext.util.JSON.encode(data);

		var loadMask = new Ext.LoadMask(this.getEl(),{msg: LOAD_WAIT});
		loadMask.show();

		base_form.submit({
			url: '/?c=AccessRights&m=saveAccessRightsLimitUsers',
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

	loadLimitUsersGrid: function() {
		var grid = this.GridPanel.getGrid();
		var base_form = this.FormPanel.getForm();

		var params = {
			AccessRightsName_id: base_form.findField('AccessRightsName_id').getValue(),
			Lpu_id: base_form.findField('Lpu_id').getValue()
		};

		if (!Ext.isEmpty(params.Lpu_id)) {
			grid.getStore().load({
				params: params,
				callback: function() {this.refreshCheckAll()}.createDelegate(this)
			});
		} else {
			grid.removeAll();
		}
	},

	show: function() {
		sw.Promed.swAccessRightsLimitUsersSelectWindow.superclass.show.apply(this, arguments);

		var base_form = this.FormPanel.getForm();
		var grid = this.GridPanel.getGrid();

		grid.getStore().removeAll();
		base_form.reset();
		this.refreshCheckAll();

		if (!arguments[0] || !arguments[0].AccessRightsName_id) {
			Ext.Msg.alert(lang['oshibka'], lang['otsutstvuyut_neobhodimyie_parametryi']);
			this.hide();
			return false;
		}

		base_form.findField('AccessRightsName_id').setValue(arguments[0].AccessRightsName_id);

		if (arguments[0].title) {
			this.setTitle(arguments[0].title);
		} else {
			this.setTitle(lang['dostup_dlya_polzovateley']);
		}

		if (arguments[0].callback) {
			this.callback = arguments[0].callback;
		} else {
			this.callback = Ext.emptyFn;
		}

		base_form.findField('Lpu_id').getStore().load({
			callback: function() {
				var record = base_form.findField('Lpu_id').getStore().getAt(0);
				base_form.findField('Lpu_id').setValue(record.get('Lpu_id'));
				base_form.findField('Lpu_id').fireEvent('select', base_form.findField('Lpu_id'), record);
			}
		});
	},

	initComponent: function() {
		this.FormPanel = new Ext.form.FormPanel({
			bodyBorder: false,
			border: false,
			buttonAlign: 'left',
			frame: false,
			id: 'ARLUSW_AccessRightsDiagEditForm',
			bodyStyle: 'padding: 10px 5px 10px 20px; background:#DFE8F6;',
			labelAlign: 'right',

			items: [{
				xtype: 'hidden',
				name: 'AccessRightsName_id'
			}, {
				allowBlank: false,
				xtype: 'swlpusearchcombo',
				hiddenName: 'Lpu_id',
				fieldLabel: lang['mo'],
				width: 320,
				listeners: {
					'select': function(combo, record) {
						this.loadLimitUsersGrid();
					}.createDelegate(this)
				}
			}]
		});

		this.GridPanel = new sw.Promed.ViewFrame({
			dataUrl: '/?c=AccessRights&m=loadAccessRightsLimitUsersGrid',
			id: 'ARLUSW_LimitUsersGrid',
			height: 280,
			bodyStyle: 'margin: 5px 20px;',
			autoLoadData: false,
			showCountInTop: false,
			stripeRows: true,
			toolbar: false,
			useEmptyRecord: false,
			root: 'data',
			stringfields: [
				{name: 'pmUser_id', type: 'int', header: 'ID', key: true},
				{name: 'AccessRightsLimit_id', type: 'int', hidden: true},
				{name: 'RecordStatus_Code', type: 'int', hidden: true},
				{name: 'check', sortable: false, width: 40, renderer: this.checkRenderer,
					header: '<input type="checkbox" id="ARLUSW_checkAll" onClick="getWnd(\'swAccessRightsLimitUsersSelectWindow\').checkAll(this.checked);">'
				},
				{name: 'Person_SurName', type: 'string', header: lang['familiya'], width: 140},
				{name: 'Person_FirName', type: 'string', header: lang['imya'], width: 140},
				{name: 'Person_SecName', type: 'string', header: lang['otchestvo'], width: 140},
				{name: 'pmUser_Login', type: 'string', header: lang['login'], width: 160}
			]
		});

		Ext.apply(this, {
			items: [
				this.FormPanel,
				this.GridPanel
			],
			buttons: [
				{
					id: 'ARLUSW_ButtonSave',
					text: lang['vyibrat'],
					tooltip: lang['vyibrat'],
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
					id: 'ARLUSW_CancelButton',
					text: lang['otmenit']
				}]
		});

		sw.Promed.swAccessRightsLimitUsersSelectWindow.superclass.initComponent.apply(this, arguments);
	}
});