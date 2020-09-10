/**
 * swAccessRightsDiagEditWindow - окно редактирования групп диагнозов для ограничения доступа
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Admin
 * @access			public
 * @copyright		Copyright (c) 2014 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			18.08.2014
 */

/*NO PARSE JSON*/

sw.Promed.swAccessRightsDiagEditWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'swAccessRightsDiagEditWindow',
	width: 640,
	autoHeight: true,
	modal: true,

	formStatus: 'edit',
	action: 'view',
	callback: Ext.emptyFn,

	listeners: {
		'hide': function() {
			var base_form = this.FormPanel.getForm();
			var DiagPanel = this.findById('ARDEW_DiagPanel');
			DiagPanel.items.each(function(fieldSet){
				fieldSet.items.each(function(item) {
					base_form.items.removeKey(item.id);
				});
			});
			DiagPanel.removeAll();
			this.FormPanel.initFields();
			this.syncShadow();
		}
	},

	doSave: function(options) {
		options = options || {};
		var base_form = this.FormPanel.getForm();
		var DiagPanel = this.findById('ARDEW_DiagPanel');

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
		if ( DiagPanel.items.getCount() == 0 ) {
			sw.swMsg.show(
				{
					buttons: Ext.Msg.OK,
					icon: Ext.Msg.WARNING,
					msg: lang['doljen_byit_ukazan_hotya_byi_odin_diagnoz'],
					title: ERR_INVFIELDS_TIT
				});
			return false;
		}

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT_SAVE });
		//loadMask.show();

		var values = base_form.getValues();
		var AccessRightsData = [];
		var DiagCodes = [];
		var index = 0;

		for (var num in this.DiagState) {
			var diag_state = this.DiagState[num];

			if (diag_state.status == 1) {
				for (var field in diag_state.origValues) {
					if (values[field+'_'+num] && values[field+'_'+num] != diag_state.origValues[field]) {
						diag_state.status = 2;
						break;
					}
				}
			}

			/*DiagCodes.push([]);
			DiagCodes[index].push(base_form.findField('Diag_fid_'+num).getFieldValue('Diag_Code'));
			if (values['Diag_tid_'+num]!=undefined) {
				DiagCodes[index].push(base_form.findField('Diag_tid_'+num).getFieldValue('Diag_Code'));
			}
			index++;*/

			switch (diag_state.status) {
				case 0:
				case 2:
					AccessRightsData.push({
						AccessRightsDiag_id: values['AccessRightsDiag_id_'+num],
						Diag_fid: values['Diag_fid_'+num],
						Diag_tid: values['Diag_tid_'+num]==undefined ? null : values['Diag_tid_'+num],
						RecordStatus_Code: diag_state.status
					});
					break;
				case 3:
					AccessRightsData.push({
						AccessRightsDiag_id: diag_state.AccessRightsDiag_id,
						Diag_fid: null,
						Diag_tid: null,
						RecordStatus_Code: diag_state.status
					});
					break;
			}

		}

		/*var str = '';
		DiagCodes.forEach(function(item, i) {
			if (i > 0) {str += ', '}
			if (item.length == 1) {
				str += item[0];
			} else if (item.length == 2) {
				str += item[0] + ' - '+ item[1];
			}
		});*/

		var data = {
			AccessRightsName_id: values.AccessRightsName_id,
			AccessRightsName_Name: values.AccessRightsName_Name,
			AccessRightsType_id: 1,
			AccessRightsData: Ext.util.JSON.encode(AccessRightsData)
		};

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

	addDiagFieldSet: function(options) {
		var wnd = this;
		var base_form = this.FormPanel.getForm();
		var DiagPanel = this.findById('ARDEW_DiagPanel');

		this.DiagLastNum++;
		var num = this.DiagLastNum;

		this.DiagState[num] = {
			status: 0,
			AccessRightsDiag_id: null,
			origValues: {Diag_fid: null, Diag_tid: null}
		};

		var config = {
			layout: 'form',
			id: 'DiagFieldSet_'+num,
			autoHeight: true,
			cls: 'AccessRigthsFieldSet',
			width: 570,
			items: []
		};

		if (options && options.isRange) {
			config.items = [{
				html: '<div id="DiagHeader_'+num+'" class="AccessRightsFieldSetHeader">' +
					'<div class="AccessRightsFieldSetBlock"></div>' +
					'<div class="AccessRightsFieldSetLabel">Диапазон диагнозов</div>' +
					'<div class="AccessRightsFieldSetLine" style="width: 361px;"></div>' +
					'</div>',
				style: 'margin-bottom: 5px;'
			}, {
				xtype: 'hidden',
				name: 'AccessRightsDiag_id_'+num
			}, {
				allowBlank: false,
				xtype: 'swdiagcombo',
				fieldLabel: lang['ot'],
				hiddenName: 'Diag_fid_'+num,
				anchor: '98%'
			}, {
				allowBlank: false,
				xtype: 'swdiagcombo',
				fieldLabel: lang['do'],
				hiddenName: 'Diag_tid_'+num,
				anchor: '98%'
			}];
		} else {
			config.items = [{
				html: '<div id="DiagHeader_'+num+'" class="AccessRightsFieldSetHeader">' +
					'<div class="AccessRightsFieldSetBlock"></div>' +
					'<div class="AccessRightsFieldSetLabel">Диагноз</div>' +
					'<div class="AccessRightsFieldSetLine" style="width: 430px;"></div>' +
					'</div>',
				style: 'margin-bottom: 5px;'
			}, {
				xtype: 'hidden',
				name: 'AccessRightsDiag_id_'+num
			}, {
				allowBlank: false,
				xtype: 'swdiagcombo',
				fieldLabel: '',
				labelSeparator: '',
				hiddenName: 'Diag_fid_'+num,
				anchor: '98%'
			}];
		}

		var DiagFieldSet = DiagPanel.add(config);
		this.doLayout();
		this.syncSize();
		this.FormPanel.initFields();

		if (options && options.data) {
			this.DiagState[num].status = options.data.RecordStatus_Code;
			this.DiagState[num].AccessRightsDiag_id = options.data.AccessRightsDiag_id;
			this.DiagState[num].origValues.Diag_fid = options.data.Diag_fid;
			this.DiagState[num].origValues.Diag_tid = options.data.Diag_tid;

			base_form.findField('AccessRightsDiag_id_'+num).setValue(options.data.AccessRightsDiag_id);
			this.setDiagByName('Diag_fid_'+num, options.data.Diag_fid);
			if (options.isRange) {
				this.setDiagByName('Diag_tid_'+num, options.data.Diag_tid);
			}
		}

		var delButton = new Ext.Button({
			iconCls:'delete16',
			text: lang['udalit'],
			style: 'display: inline-block; vertical-align: middle;',
			handler: function()
			{
				if (wnd.DiagState[num].status != 0) {
					wnd.DiagState[num].status = 3;
				} else {
					delete wnd.DiagState[num];
				}

				DiagFieldSet.items.each(function(item) {
					wnd.FormPanel.getForm().items.removeKey(item.id);
				});
				DiagPanel.remove(DiagFieldSet.id);
				wnd.doLayout();
				wnd.syncShadow();
				wnd.FormPanel.initFields();
			}
		});
		delButton.render('DiagHeader_'+num);
	},

	setDiagByName: function(name, value) {
		var field = this.FormPanel.getForm().findField(name);

		field.getStore().load({
			params: { where: "where DiagLevel_id = 4 and Diag_id = " + value },
			callback: function() {
				field.getStore().each(function(record) {
					if ( record.get('Diag_id') == value ) {
						field.setValue(value);
						field.fireEvent('select', field, record, 0);
						field.fireEvent('change', field, value);
					}
				});
			}
		});
	},

	show: function() {
		sw.Promed.swAccessRightsDiagEditWindow.superclass.show.apply(this, arguments);

		var base_form = this.FormPanel.getForm();

		base_form.reset();

		this.DiagLastNum = 0;
		this.DiagState = {};
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

		var loadMask = new Ext.LoadMask(this.getEl(),{msg: LOAD_WAIT});
		loadMask.show();
		switch (this.action)
		{
			case 'add':
				this.setTitle(lang['gruppa_diagnozov_dobavlenie']);
				this.enableEdit(true);

				loadMask.hide();
			break;

			case 'edit':
			case 'view':
				if (this.action == 'edit') {
					this.setTitle(lang['gruppa_diagnozov_redaktirovanie']);
					this.enableEdit(true);
				} else {
					this.setTitle(lang['gruppa_diagnozov_prosmotr']);
					this.enableEdit(false);
				}

				base_form.load({
					url: '/?c=AccessRightsDiag&m=loadAccessRightsForm',
					params: {AccessRightsName_id: base_form.findField('AccessRightsName_id').getValue()},
					failure: function() {
						loadMask.hide();
						//
					},
					success: function(form, action) {
						if (action.result.data.AccessRightsData) {
							var DiagData = Ext.util.JSON.decode(action.result.data.AccessRightsData);

							for (var i = 0; i < DiagData.length; i++) {
								this.addDiagFieldSet({
									data: DiagData[i],
									isRange: Ext.isEmpty(DiagData[i].Diag_tid) ? false : true
								});
							}
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
			id: 'ARDEW_AccessRightsDiagEditForm',
			url: '/?c=AccessRightsDiag&m=saveAccessRights',
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
				id: 'ARDEW_DiagPanel',
				cls: 'AccessRigthsPanel',
				autoHeight: true,
				items: []
			}, {
				layout: 'column',
				id: 'ARDEW_ButtonDiagPanel',
				cls: 'AccessRigthsFieldSet',
				height: 25,
				style: 'margin-left: 100px; margin-top: 10px;',
				items: [{
					layout: 'form',
					items: [{
						xtype: 'button',
						iconCls:'add16',
						text: lang['dobavit_diapazon_diagnozov'],
						handler: function() {
							this.addDiagFieldSet({isRange: true});
						}.createDelegate(this)
					}]
				}, {
					layout: 'form',
					style: 'margin-left: 10px',
					items: [{
						xtype: 'button',
						iconCls:'add16',
						text: lang['dobavit_diagnoz'],
						handler: function() {
							this.addDiagFieldSet({isRange: false});
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

		sw.Promed.swAccessRightsDiagEditWindow.superclass.initComponent.apply(this, arguments);
	}
});