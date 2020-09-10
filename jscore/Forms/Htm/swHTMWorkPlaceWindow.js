/**
 * swHTMWorkPlaceWindow - окно рабочего места работника ВМП
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Common
 * @access			public
 * @copyright		Copyright (c) 2013 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			29.07.2014
 */

sw.Promed.swHTMWorkPlaceWindow = Ext.extend(sw.Promed.swWorkPlaceWindow, {
	enableDefaultActions: false,
	id: 'swHTMWorkPlaceWindow',
	showToolbar: true,
	gridPanelAutoLoad: false,
	buttonPanelActions: {
		action_Timetable: {
			text: lang['rabota_s_raspisaniem'],
			tooltip: lang['rabota_s_raspisaniem'],
			iconCls: 'mp-timetable32',
			handler: function() {
				var wnd = Ext.getCmp('swHTMWorkPlaceWindow');
				getWnd('swTTMSScheduleEditWindow').show({
					MedService_id: wnd.MedService_id,
					MedService_Name: wnd.MedService_Name,
					userClearTimeMS: function() {
						wnd.getLoadMask(lang['osvobojdenie_zapisi']).show();
						Ext.Ajax.request({
							url: '/?c=EvnDirectionHTM&m=clearTimeMSOnEvnDirectionHTM',
							params: {
								TimetableMedService_id: wnd.TimetableMedService_id
							},
							callback: function(o, s, r) {
								wnd.getLoadMask().hide();
								if(s) {
									wnd.loadSchedule();
								}
							}
						});
					}
				});
			}
		},
		action_EvnDirectionHTMRegistry:
		{
			text: lang['HTM_registry'],
			tooltip: lang['HTM_registry'],
			iconCls: 'doc-reg16',hidden: getRegionNick().inlist(['ufa', 'kz']),
			handler: function() {
				var wnd = Ext.getCmp('swHTMWorkPlaceWindow');
				getWnd('swEvnDirectionHTMRegistryWindow').show({ARMType: 'htm',
					userMedStaffFact: wnd.userMedStaffFact
				});
			}
		}
	},

	scheduleNew: function() {
		var win = this;
		// Добавление пациента вне записи
		if (getWnd('swPersonSearchWindow').isVisible()) {
			sw.Msg.alert(lang['soobschenie'], lang['okno_poiska_cheloveka_uje_otkryito']);
			getWnd('swPersonSearchWindow').hide();
			return false;
		}
		getWnd('swPersonSearchWindow').show({
			onSelect: function(pdata) {
				if (pdata.Person_IsDead != 'true') {
					getWnd('swPersonSearchWindow').hide();
					getWnd('swDirectionOnHTMEditForm').show({
						action: 'add',
						Person_id: pdata.Person_id,
						PersonEvn_id: pdata.PersonEvn_id,
						Server_id: pdata.Server_id,
						MedService_id: win.MedService_id,
						LpuSection_id: getGlobalOptions().CurLpuSection_id,
						LpuSection_did: getGlobalOptions().CurLpuSection_id,
						onHide: function() {
							win.GridPanel.getAction('action_refresh').execute();
						}
					});
				} else if (pdata.Person_IsDead == 'true') {
					sw.swMsg.alert(lang['oshibka'], lang['zapis_nevozmojna_v_svyazi_so_smertyu_patsienta']);
				}
			},
			searchMode: 'all'
		});
	},

	scheduleOpen: function() {
		var record = this.GridPanel.getGrid().getSelectionModel().getSelected();
		if (!record || Ext.isEmpty(record.get('EvnDirectionHTM_id'))) {
			return false;
		}
		getWnd('swDirectionOnHTMEditForm').show({
			EvnDirectionHTM_id: record.get('EvnDirectionHTM_id'),
			action: 'edit',
			ARMType: 'htm'
		});
	},

	show: function()
	{
		sw.Promed.swHTMWorkPlaceWindow.superclass.show.apply(this, arguments);

		with ( this.LeftPanel.actions ) {
			action_RLS.setHidden(true);
			action_Mes.setHidden(true);
			action_Report.setHidden(true);
		}

		this.MedService_Name = null;
		if (arguments[0]['MedService_Name']) {
			this.MedService_Name = arguments[0]['MedService_Name'];
		}

		this.GridPanel.addActions({
			name:'action_export',
			text: lang['eksport'],
			disabled: true,
			iconCls: '',
			handler: function() {
				sw.swMsg.alert(lang['vnimanie'],lang['dannyiy_funktsional_v_tekuschee_vremya_nedostupen_poskolku_nahoditsya_v_stadii_razrabotki']);
			}
		});

		this.searchParams = {MedService_id: this.MedService_id};
		this.GridPanel.setParam('start', 0);
		this.doSearch('day');
	},

	doSearch: function(mode){
		var win = this;
		if( getRegionNick() != 'kz' ){
			win.searchParams.dateType = Ext.getCmp('radio_dateType_var_1').getGroupValue();
		}
		sw.Promed.swHTMWorkPlaceWindow.superclass.doSearch.apply(this, arguments);
	},

	initComponent: function()
	{
		var win = this;
		
		this.on('render', function() {
			var fset = this.FilterPanel.fieldSet;
			var financeField = {
				layout: 'form',
				items: [{
					xtype: 'swcommonsprcombo',
					name: 'HTMFinance_id',
					comboSubject: 'HTMFinance',
					hiddenName: 'HTMFinance_id',
					fieldLabel: lang['vid_oplatyi'],
					width: 150,
					labelWidth: 300,
					listeners: {
						render: function() {
							if(this.getStore().getCount()==0)
								this.getStore().load();
						}
					},
				}]
			};
			fset.insert(4, financeField);
		});

		this.GridPanel = new sw.Promed.ViewFrame({
			actions: [
				{name: 'action_add', text: lang['bez_zapisi'], handler: function(){this.scheduleNew();}.createDelegate(this)},
				{name: 'action_edit', text: lang['otkryit'], iconCls: 'open16', handler: function(){this.scheduleOpen();}.createDelegate(this)},
				{name: 'action_view', disabled: true, hidden: true},
				{name: 'action_delete', disabled: true, hidden: true},
				{name: 'action_print'}
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 150,
			autoLoadData: false,
			dataUrl: '/?c=EvnDirectionHTM&m=loadEvnDirectionHTMGrid',
			id: 'HWPW_EvnDirectionHTMGrid',
			object: 'EvnDirectionHTM',
			paging: false,
			region: 'center',
			/*root: 'data',
			totalProperty: 'totalCount',*/
			//title: 'Направление на ВМП',
			stringfields: [
				{ name: 'EvnDirectionHTM_setDate', group: true, sort: true, direction: 'ASC', header: lang['data'], hideable: false },
				{ name: 'EvnDirectionHTM_id', type: 'int',  header: 'ID', key: true },
				{ name: 'TimetableMedService_id', type: 'int', hidden: true },
				{ name: 'Person_id', type: 'int', hidden: true },
				{ name: 'PersonEvn_id', type: 'int', hidden: true },
				{ name: 'Server_id', type: 'int', hidden: true },
				{ name: 'EvnDirectionHTM_IsHTM', type: 'int', hidden: true },
				{ name: 'LpuAttach_id', type: 'int', hidden: true },
				{ name: 'TimetableMedService_begTime', type: 'string', header: lang['zapis'], width: 80 },
				{ name: 'EvnDirectionHTM_IsHTMName', type: 'string', header: lang['napravlenie'], width: 100 },
				{ name: 'Person_FIO', type: 'string', header: lang['fio'], width: 280 },
				{ name: 'Person_BirthDay', type: 'date', header: lang['dr'], width: 100 },
				{ name: 'LpuAttach_Nick', type: 'string', header: lang['mo_prikrepleniya'], id: 'autoexpand' },
				{ name: 'EvnDirectionHTM_IsExport', type: 'checkbox', header: lang['federalnyiy_portal'], width: 120 }
			]
		});

		if( getRegionNick() != 'kz' ) {
			this.winToolbarDateRadio_v1 = {
				xtype: 'radio',
				hideLabel: true,
				boxLabel: 'По направлениям',
				inputValue: 'direction',
				id: 'radio_dateType_var_1',
				name: 'dateType',
				checked: true,
				listeners: {
					'check': function (field, value) {
						win.doSearch('period');
					}
				},
			};
			this.winToolbarDateRadio_v2 = {
				xtype: 'radio',
				hideLabel: true,
				boxLabel: 'По талонам',
				inputValue: 'issue',
				id: 'radio_dateType_var_2',
				name: 'dateType',
			};

			this.tbseparator = {
				xtype: "tbseparator"
			}

			this.tbfill = {
				xtype: 'tbfill'
			}

			this.redefinedWindowToolbar = [
				'prev',
				'tbseparator',
				'dateMenu',
				'tbseparator',
				'next',
				'tbseparator',
				'winToolbarDateRadio_v1',
				'tbseparator',
				'winToolbarDateRadio_v2',
				'tbseparator',
				'tbfill',
				'tbseparator',
				'day',
				'week',
				'month',
				'tbseparator',
			];
		}
		
		sw.Promed.swHTMWorkPlaceWindow.superclass.initComponent.apply(this, arguments);

	}
});
