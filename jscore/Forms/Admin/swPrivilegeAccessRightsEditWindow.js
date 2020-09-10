sw.Promed.swPrivilegeAccessRightsEditWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: true,
	autoScroll: true,
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	draggable: true,
	id: 'swPrivilegeAccessRightsEditWindow',
	maximizable: false,
	modal: true,
	resizable: false,
	width: 600,

	doSave: function() {
		var base_form = this.FormPanel.getForm();
		var wnd = this;

		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					wnd.FormPanel.getFirstInvalidEl().focus(false);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		var params = new Object();
		var LpuGrid = this.LpuGrid.getGrid();
		var PrivilegeAccessRightsData = [];

		//wnd.getLoadMask("Подождите, идет сохранение...").show();

		LpuGrid.getStore().clearFilter();

		if ( LpuGrid.getStore().getCount() > 0 && LpuGrid.getStore().getAt(0).get('Lpu_id') ) {
			PrivilegeAccessRightsData = getStoreRecords(LpuGrid.getStore(), {
				exceptionFields: [
					'Lpu_Nick'
				]
			});

			LpuGrid.getStore().filterBy(function(rec) {
				return (Number(rec.get('RecordStatus_Code')) != 3);
			});
		}

		var PrivilegeAccessRights_UserGroups = base_form.findField('PrivilegeAccessRights_UserGroups').getValue();
		var RecordStatus_Code = base_form.findField('RecordStatus_Code').getValue();
		if ( PrivilegeAccessRights_UserGroups || RecordStatus_Code == 3 ) {
			var id = base_form.findField('PrivilegeAccessRights_id').getValue();
			PrivilegeAccessRightsData.push({
				PrivilegeAccessRights_id: id,
				PrivilegeAccessRights_UserGroups: PrivilegeAccessRights_UserGroups,
				RecordStatus_Code: RecordStatus_Code
			});
		}

		if ( PrivilegeAccessRightsData.length == 0 ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.WARNING,
				msg: lang['neobhodimo_ukazat_gruppu_polzovateley_libo_mo_dlya_dostupa_k_lgote_s_ogranichennyim_dostupom'],
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		params.PrivilegeAccessRightsData = Ext.util.JSON.encode(PrivilegeAccessRightsData);
		params.PrivilegeType_id = base_form.findField('PrivilegeType_id').getValue();
		if (this.action == 'add') {
			params.RecordStatus_isNewRecord = 1;
		} else {
			params.RecordStatus_isNewRecord = 0;
		}

		base_form.submit({
			/*failure: function(result_form, action) {
				sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_1]']);
				wnd.getLoadMask().hide()
			},*/
			params: params,
			success: function(result_form, action) {
				//wnd.getLoadMask().hide();

				if ( action.result ) {
					wnd.callback();
					wnd.hide();
				}
				else {
					sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_2]']);
				}
			}
		});
	},

	openPrivilegeAccessRightsLpuEditWindow: function(action){
		if ( Ext.isEmpty(action) || !action.inlist([ 'add', 'edit', 'view' ]) ) {
			return false;
		}

		if ( getWnd('swPrivilegeAccessRightsLpuEditWindow').isVisible() ) {
			sw.swMsg.alert(lang['oshibka'], lang['okno_redaktirovaniya_mo_uje_otkryito']);
			return false;
		}

		var base_form = this.FormPanel.getForm();
		var grid = this.LpuGrid.getGrid();
		var params = new Object();

		params.action = action;
		params.callback = function(data) {
			if ( typeof data != 'object' || typeof data.LpuData != 'object' ) {
				return false;
			}

			var index = grid.getStore().findBy(function(rec) { return rec.get('Lpu_id') == data.LpuData.Lpu_id; });
			var record = grid.getStore().getAt(index);

			if (typeof record == 'object') {
				sw.swMsg.alert(lang['oshibka'], lang['vyibrannoe_mo_uje_prisutstvuet_v_spiske']);
				return false;
			}

			var record = grid.getStore().getById(data.LpuData.Lpu_id);

			if ( typeof record == 'object' ) {
				var grid_fields = new Array();

				grid.getStore().fields.eachKey(function(key, item) {
					grid_fields.push(key);
				});

				for ( i = 0; i < grid_fields.length; i++ ) {
					record.set(grid_fields[i], data.LpuData[grid_fields[i]]);
				}

				record.commit(); alert(1);
			}
			else {
				if ( grid.getStore().getCount() == 1 && !grid.getStore().getAt(0).get('Lpu_id') ) {
					grid.getStore().removeAll();
				}

				data.LpuData.PrivilegeAccessRights_id = -swGenTempId(grid.getStore());

				grid.getStore().loadData({data: [ data.LpuData ]}, true);
			}
		}.createDelegate(this);

		params.formParams = new Object();

		if ( action == 'add' ) {
			params.onHide = function() {
				if ( grid.getStore().getCount() > 0 ) {
					grid.getView().focusRow(0);
				}
			};
		}
		else {
			if ( !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('Lpu_id') ) {
				return false;
			}

			var record = grid.getSelectionModel().getSelected();

			params.formParams = record.data;
			params.onHide = function() {
				grid.getView().focusRow(grid.getStore().indexOf(record));
			};
		}

		getWnd('swPrivilegeAccessRightsLpuEditWindow').show(params);
	},

	deleteGridRecord: function() {
		var wnd = this;

		if ( this.action == 'view' ) {
			return false;
		}

		question = lang['udalit_pravo_dostup_ko_lgote_dlya_mo'];

		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId, text, obj) {
				if ( buttonId == 'yes' ) {
					var grid = wnd.LpuGrid.getGrid();

					var idField = 'PrivilegeAccessRights_id';

					if ( !grid || !grid.getSelectionModel() || !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get(idField) ) {
						return false;
					}

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
			msg: question,
			title: lang['vopros']
		});
	},

	show: function() {
		sw.Promed.swPrivilegeAccessRightsEditWindow.superclass.show.apply(this, arguments);

		var action = null;
		var form = this;
		var base_form = form.FormPanel.getForm();


		if ( !arguments[0] || !arguments[0].formParams ) {
			sw.swMsg.alert(lang['soobschenie'], lang['nevernyie_parametryi'], function() { this.hide(); }.createDelegate(this) );
			return false;
		}

		if ( arguments[0].action ) {
			this.action = arguments[0].action;
		}

		if ( arguments[0].callback ) {
			this.callback = arguments[0].callback;
		}

		base_form.reset();
		base_form.setValues(arguments[0].formParams);

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT });
		loadMask.show();

		base_form.findField('PrivilegeAccessRights_UserGroups').getStore().load();

		this.LpuGrid.setActionDisabled('action_add', true);
		this.LpuGrid.setActionDisabled('action_edit', true);
		this.LpuGrid.setActionDisabled('action_view', true);
		this.LpuGrid.setActionDisabled('action_delete', true);
		//this.LpuGrid.setActionDisabled('action_refresh', true);

		this.LpuGrid.removeAll();
		this.LpuPanel.isLoaded = false;
		this.LpuPanel.expand();
		this.LpuPanel.fireEvent('expand', this.LpuPanel);

		switch ( this.action ) {
			case 'add':
				this.enableEdit(true);
				this.setTitle(lang['ogranichenie_prav_dostupa_lgota_dobavlenie']);
				this.LpuGrid.setActionDisabled('action_add', false);
				LoadEmptyRow(this.LpuGrid.getGrid(), 'data');

				base_form.clearInvalid();
				base_form.findField('PrivilegeType_id').focus(true, 250);

				loadMask.hide();

				base_form.findField('PrivilegeType_id').focus(true, 250);
				break;

			case 'edit':
			case 'view':
				var privilege_type_id = base_form.findField('PrivilegeType_id').getValue();

				if ( !privilege_type_id ) {
					loadMask.hide();
					this.hide();
					return false;
				}

				function afterFormLoad() {
					if ( form.action == 'edit' ) {
						form.setTitle(lang['ogranichenie_prav_dostupa_lgota_redaktirovanie']);
						form.enableEdit(true);
					}
					else {
						form.setTitle(lang['ogranichenie_prav_dostupa_lgota_prosmotr']);
						form.enableEdit(false);
					}
					base_form.clearInvalid();
					if ( base_form.findField('PrivilegeAccessRights_UserGroups').getValue() ){
						base_form.findField('RecordStatus_Code').setValue(1);
					}
					if ( form.action == 'edit' ) {
						form.LpuGrid.setActionDisabled('action_add', false);
						base_form.findField('PrivilegeType_id').setDisabled(true);
						base_form.findField('PrivilegeAccessRights_UserGroups').focus(true, 250);
					}
					else {
						form.buttons[form.buttons.length - 1].focus();
					}
				};

				base_form.load({
					params: {PrivilegeType_id: privilege_type_id},
					failure: function() {
						base_form.findField('PrivilegeType_id').setValue(privilege_type_id);
						loadMask.hide();

						afterFormLoad();
					},
					success: function() {
						base_form.findField('PrivilegeType_id').setValue(privilege_type_id);
						loadMask.hide();

						afterFormLoad();
					},
					url: '/?c=PrivilegeAccessRights&m=loadPrivilegeAccessRightsForm'
				});

				break;

			default:
				this.hide();
				break;
		}
	},

	initComponent: function() {
		var form = this;

		this.LpuGrid = new sw.Promed.ViewFrame({
			actions: [
				{ name: 'action_add', handler: function() { this.openPrivilegeAccessRightsLpuEditWindow('add'); }.createDelegate(this) },
				{ name: 'action_edit', handler: function() { this.openPrivilegeAccessRightsLpuEditWindow('edit'); }.createDelegate(this) },
				{ name: 'action_view', handler: function() { this.openPrivilegeAccessRightsLpuEditWindow('view'); }.createDelegate(this) },
				{ name: 'action_delete', handler: function() { this.deleteGridRecord(); }.createDelegate(this) },
				{ name: 'action_print', disabled: true, hidden: true }
			],
			autoLoadData: false,
			border: false,
			object: 'PrivilegeAccessRights',
			dataUrl: '/?c=PrivilegeAccessRights&m=loadPrivilegeAccessRightsLpuGrid',
			root: 'data',
			id: 'PARE_LpuGrid',
			onDblClick: function() {
				if ( !this.ViewActions.action_edit.isDisabled() ) {
					this.ViewActions.action_edit.execute();
				}
			},
			onEnter: function() {
				if ( !this.ViewActions.action_edit.isDisabled() ) {
					this.ViewActions.action_edit.execute();
				}
			},
			onLoadData: function() {
				//
			},
			onRowSelect: function(sm, index, record) {
				this.setActionDisabled('action_edit', true);
				this.setActionDisabled('action_view', true);
				this.setActionDisabled('action_delete', true);

				if ( typeof record != 'object' || Ext.isEmpty(record.get('Lpu_id')) ) {
					return false;
				}

				this.setActionDisabled('action_view', false);

				if ( form.action.inlist(['add', 'edit']) ) {
					if ( record.get('RecordStatus_Code') != 0 ) {
						this.setActionDisabled('action_edit', false);
					}
					this.setActionDisabled('action_delete', false);
				}
			},
			paging: false,
			region: 'center',
			stringfields: [
				{ name: 'PrivilegeAccessRights_id', type: 'int', header: 'ID', key: true },
				{ name: 'Lpu_id', type: 'int', hidden: 'true' },
				{ name: 'RecordStatus_Code', type: 'int', hidden: true },
				{ name: 'Lpu_Nick', type: 'string', header: lang['mo_s_razreshennyim_dostupom'], id: 'autoexpand' }
			]
		});

		this.FormPanel = new Ext.form.FormPanel(
			{
				bodyStyle: '{padding-top: 0.5em;}',
				border: false,
				frame: false,
				labelAlign: 'right',
				labelWidth: 150,
				layout: 'form',
				id: 'PrivilegeAccessRightsEditForm',
				url: '/?c=PrivilegeAccessRights&m=savePrivilegeAccessRights',
				autoLoad: false,
				reader: new Ext.data.JsonReader({
					success: Ext.emptyFn
				},  [
					{ name: 'PrivilegeType_id' },
					{ name: 'PrivilegeAccessRights_UserGroups' },
					{ name: 'PrivilegeAccessRights_id' }
				]),
				items: [
					{
						allowBlank: false,
						fieldLabel: lang['zakryitaya_lgota'],
						name: 'PrivilegeType_id',
						width: 350,
						xtype: 'swprivilegetypecombo'
					},
					new sw.Promed.Panel({
						autoHeight: true,
						bodyStyle: '{padding-top: 0.5em;}',
						id: 'PARE_GroupsPanel',
						title: lang['razreshennyiy_dostup'],
						isLoaded: false,
						layout: 'form',
						listeners: {
							'expand': function(panel) {
								form.syncShadow();
							},
							'collapse': function(panel) {
								form.syncShadow();
							}
						},
						items: [{
							name: 'PrivilegeAccessRights_id',
							xtype: 'hidden'
						}, {
							name: 'RecordStatus_Code',
							value: 0,
							xtype: 'hidden'
						}, {
							fieldLabel: lang['gruppa_polzovateley_s_razreshennyim_dostupom'],
							hiddenName: 'PrivilegeAccessRights_UserGroups',
							valueField: 'Group_Name',
							width: 350,
							xtype: 'swusersgroupscombo',
							listeners: {
								'select': function(combo,record,index) {
									var base_form = form.FormPanel.getForm();
									var RecordStatus_Code = base_form.findField('RecordStatus_Code').getValue();
									if ( RecordStatus_Code != 0 && record.get('Group_Name') == '' ) {
										base_form.findField('RecordStatus_Code').setValue(3);
									} else if (RecordStatus_Code != 0) {
										base_form.findField('RecordStatus_Code').setValue(2);
									}
								}
							}
						}]
					})
				]
			});

		this.LpuPanel = new sw.Promed.Panel({
			collapsible: false,
			height: 200,
			id: 'PARE_LpuPanel',
			isLoaded: false,
			layout: 'border',
			listeners: {
				'expand': function(panel) {
					if ( panel.isLoaded === false && form.action != 'add' ) {
						panel.isLoaded = true;
						form.LpuGrid.getGrid().getStore().load({
							params: {
								PrivilegeType_id: form.FormPanel.getForm().findField('PrivilegeType_id').getValue()
							}
						});
					}
					form.syncShadow();
				},
				'collapse': function(panel) {
					form.syncShadow();
				}
			},
			style: 'margin-bottom: 0.5em;',
			title: lang['mo'],

			items: [
				form.LpuGrid
			]
		});

		Ext.apply(this, {
			items: [
				this.FormPanel,
				this.LpuPanel
			],
			buttons: [{
				handler: function() {
					this.doSave();
				}.createDelegate(this),
				iconCls: 'save16',
				id: 'PARE_SaveButton',
				text: BTN_FRMSAVE
			},
				'-',
				HelpButton(this, -1),
				{
					handler: function() {
						this.hide();
					}.createDelegate(this),
					iconCls: 'cancel16',
					id: 'FCEW_CancelButton',
					text: BTN_FRMCANCEL
				}]
		});

		sw.Promed.swPrivilegeAccessRightsEditWindow.superclass.initComponent.apply(this, arguments);
	}
});
