/**
 * swBedDowntimeJournalWindow - журнал простоя коек.
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Admin
 * @access			public
 * @copyright		Copyright (c) 2020 Swan Ltd.
 * @author			Borisov Igor
 * @version			18.04.2020
 */

sw.Promed.swBedDowntimeJournalWindow = Ext.extend(sw.Promed.swWorkPlaceWindow,/*sw.Promed.BaseForm,*/ {
	codeRefresh: true,
	objectName: 'swBedDowntimeJournalWindow',
	objectSrc: '/jscore/Forms/Admin/Msk/swBedDowntimeJournalWindow.js',
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	collapsible: false,
	draggable: true,
	height: 550,
	id: 'BedDowntimeJourna',
	initComponent: function () {
		var thas = this;

		this.FilterPanel = new sw.Promed.BaseWorkPlaceFilterPanel({
			owner: this,
			keys: [{
				key: Ext.EventObject.ENTER,
				fn: function (e) {
					this.doSearch();
				},
				scope: this,
				stopEvent: true
			}],
			filter: {
				title: lang['filtr'],
				collapsed: false,
				layout: 'form',
				border: false,
				defaults: {
					border: false
				},
				items: [
					{
						layout: 'column',
						items: [
							{
								layout: 'form',
								labelWidth: 130,
								items: [
									{
										name: 'LpuSection_Name',
										disabled: true,
										fieldLabel: lang['otdelenie'],
										tabIndex: 1,
										xtype: 'swcommonsprcombo',
										id: 'lspefLpuSection_Name'
									}, {
										comboSubject: 'LpuSectionBedProfile',
										fieldLabel: lang['profil_koek'],
										hiddenName: 'LpuSectionBedProfile_oid',
										lastQuery: '',
										xtype: 'swcommonsprcombo',
										id: 'LpuBedProfile_id'
									}
								]
							}
						]
					}, {
						layout: 'column',
						style: 'padding: 3px;',
						items: [
							{
								layout: 'form',
								items: [
									{
										handler: function () {
											thas.doSearch();
										},
										xtype: 'button',
										iconCls: 'search16',
										text: BTN_FRMSEARCH
									}
								]
							}, {
								layout: 'form',
								style: 'margin-left: 5px;',
								items: [
									{
										handler: function () {
											thas.doReset();
											thas.doSearch();
										},
										xtype: 'button',
										iconCls: 'resetsearch16',
										text: lang['sbros']
									}
								]
							}
						]
					}
				]
			}
		});

		this.GridPanel = new sw.Promed.ViewFrame({
			toolbar: true,
			autoLoadData: false,
			dataUrl: '/?c=BedDowntimeLog&m=loadBedDowntimeJournal',
			pageSize: 100,
			paging: true,
			totalProperty: 'totalCount',
			root: 'data',
			sortInfo: {field: 'BedDowntimeLog_id'},
			region: 'center',
			actions: [
				{
					name: 'action_add',
					handler: this.openBedDowntimeLogEditWindow.createDelegate(this, ['add'])
				},
				{
					name: 'action_edit',
					handler: this.openBedDowntimeLogEditWindow.createDelegate(this, ['edit'])
				},
				{
					name: 'action_view',
					handler: this.openBedDowntimeLogEditWindow.createDelegate(this, ['view'])
				},
				{name: 'action_delete', handler: this.deleteBedDowntimeRecord.createDelegate(this)},
				{name: 'action_print', hidden: true},
				{name: 'action_refresh', hidden: true},
			],
			stringfields: [
				{name: 'BedDowntimeLog_id', type: 'int', header: 'ID', key: true},
				{name: 'BedProfile_id', type: 'int', header: 'Профиль коек'},
				{name: 'begDate', type: 'date', dateFormat: 'd.m.Y', header: 'Дата начала'},
				{name: 'endDate', type: 'date', dateFormat: 'd.m.Y', header: 'Дата окончания'},
				{name: 'durationOfPeriod', type: 'int', header: 'Длительность периода'},
				{name: 'plainBeds', type: 'int', header: 'Простой коек, КД'},
				{name: 'BedDowntimeLog_RepairCount', type: 'int', header: 'Из них на ремонте, КД'},
				{name: 'BedDowntimeLog_ReasonsCount', type: 'int', header: 'По другим причинам, КД'},
				{name: 'BedDowntimeLog_Reasons', type: 'string', header: 'Причины'},
			],
			onBeforeLoadData: function () {
			},
			onLoadData: function () {
				this.getGrid().getStore().sort('BedDowntimeLog_id', 'ASC');
			},
			onRowSelect: function (sm, index, record) {
			},
			onDblClick: function (sm, index, record) {
				this.getAction('action_view').execute();
			}
		});

		this.LeftPanel = new sw.Promed.BaseWorkPlaceButtonsPanel({
			collapsible: true,
			titleCollapse: true,
			floatable: false,
			animCollapse: false,
			region: 'west',
			enableDefaultActions: true,
			panelActions: this.buttonPanelActions
		});

		Ext.apply(this, {
			buttons: [{
				handler: function () {
					this.doSearch();
				}.createDelegate(this),
				iconCls: 'search16',
				text: BTN_FRMSEARCH
			}, {
				handler: function () {
					this.doReset();
					this.doSearch();
				}.createDelegate(this),
				iconCls: 'resetsearch16',
				text: lang['sbros']
			}, {
				text: '-'
			},
				{
					text: BTN_FRMHELP,
					iconCls: 'help16',
					handler: function () {
						ShowHelp('Журнал простоя коек');

					}
				},
				{
					handler: function () {
						this.hide();
					}.createDelegate(this),
					iconCls: 'cancel16',
					onShiftTabAction: function () {
					}.createDelegate(this),
					onTabAction: function () {
					}.createDelegate(this),
					text: BTN_FRMCLOSE
				}],
			items: [
				this.leftPanel,
				this.FilterPanel,
				this.GridPanel
			],
			layout: 'border'
		});

		sw.Promed.swBedDowntimeJournalWindow.superclass.initComponent.apply(this, arguments);
	},
	keys: [{
		alt: true,
		fn: function (inp, e) {
			var current_window = Ext.getCmp('BedDowntimeJourna');

			switch (e.getKey()) {
				case Ext.EventObject.J:
					current_window.hide();
					break;
			}
		},
		key: [
			Ext.EventObject.J
		],
		scope: this,
		stopEvent: false
	}],
	layout: 'border',
	listeners: {
		'beforehide': function (win) {
			//
		},
		'hide': function (win) {
			win.onHide();
		},
		'maximize': function (win) {
			//
		},
		'restore': function (win) {
			//
		}
	},
	deleteBedDowntimeRecord: function () {
		var record = this.GridPanel.getGrid().getSelectionModel().getSelected();
		if (!record) {
			return false;
		}
		var win = this;
		var loadMask = new Ext.LoadMask(this.getEl(), {msg: LOAD_WAIT});

		sw.swMsg.show({
			title: lang['podtverjdenie_udaleniya'],
			msg: 'Вы действительно хотите удалить выбранную запись?',
			buttons: Ext.Msg.YESNO,
			fn: function (buttonId) {
				if (buttonId === 'yes') {
					loadMask.show();
					Ext.Ajax.request({
						url: '/?c=BedDowntimeLog&m=deleteBedDowntimeRecord',
						params: {
							BedDowntimeLog_id: record.get('BedDowntimeLog_id')
						},
						callback: function (o, s, r) {
							if (s) {
								win.GridPanel.getGrid().getStore().remove(record);
								loadMask.hide();
							}
						},
						success: function () {
							sw.swMsg.show({
								title: 'Сообщение',
								msg: 'запись удалена',
								icon: Ext.Msg.INFO,
								buttons: Ext.Msg.OK
							});
							win.GridPanel.getGrid().getStore().reload();
						}
					});
				}
			}
		});
	},
	openBedDowntimeLogEditWindow: function (action) {
		if (!action || !action.toString().inlist(['add', 'edit', 'view'])) {
			return false;
		}

		var wnd = 'swBedDowntimeLogEditWindow';
		var params = {};
		params.action = action;
		if (action !== 'add') {
			var record = this.GridPanel.getGrid().getSelectionModel().getSelected();
			params.BedDowntimeLog_id = record.get('BedDowntimeLog_id');
		}

		if (this.userMedStaffFact && !Ext.isEmpty(this.userMedStaffFact.LpuSection_id)) {
			params.LpuSection_id = this.userMedStaffFact.LpuSection_id;
		} else {
			params.LpuSection_id = sw.Promed.MedStaffFactByUser.current.LpuSection_id;
		}
		params.LpuSection = this.findById('lspefLpuSection_Name').getValue();

		if (getWnd(wnd).isVisible()) {
			sw.swMsg.alert(lang['soobschenie'], lang['okno_redaktirovaniya_brigadyi_smp_uje_otkryito']);
			return false;
		}

		getWnd(wnd).show(params);
	},
	exportRegistryToXLS: function () {
		var form = this;
		var fd = 'swExportBedDowntimeLogToXLSWindow';
		var sort = this.GridPanel.getGrid().getStore().getSortState();

		var params = {};
		params.begDate = Ext.util.Format.date(this.dateMenu.getValue1(), 'd.m.Y');
		params.endDate = Ext.util.Format.date(this.dateMenu.getValue2(), 'd.m.Y');
		params.sortField = sort.field;
		params.sortDirection = sort.direction;
		if (this.userMedStaffFact && !Ext.isEmpty(this.userMedStaffFact.LpuSection_id)) {
			params.LpuSection_id = this.userMedStaffFact.LpuSection_id;
		} else {
			params.LpuSection_id = sw.Promed.MedStaffFactByUser.current.LpuSection_id;
		}
		params.BedProfile_id = form.findById('LpuBedProfile_id').getValue();

		getWnd(fd).show(params);
	},
	doSearch: function (mode) {
		var params = {};
		var btn = this.getPeriodToggle(mode);

		if (btn) {
			if (mode != 'range') {
				if (this.mode == mode) {
					btn.toggle(true);
					if (mode != 'day') // чтобы при повторном открытии тоже происходила загрузка списка записанных на этот день
						return false;
				} else {
					this.mode = mode;
				}
			} else {
				btn.toggle(true);
				this.mode = mode;
			}
		}

		var form = this;

		params.begDate = Ext.util.Format.date(this.dateMenu.getValue1(), 'd.m.Y');
		params.endDate = Ext.util.Format.date(this.dateMenu.getValue2(), 'd.m.Y');
		params.limit = 100;
		params.start = 0;
		if (this.userMedStaffFact && !Ext.isEmpty(this.userMedStaffFact.LpuSection_id)) {
			params.LpuSection_id = this.userMedStaffFact.LpuSection_id;
		} else {
			params.LpuSection_id = sw.Promed.MedStaffFactByUser.current.LpuSection_id;
		}
		params.BedProfile_id = form.findById('LpuBedProfile_id').getValue();

		this.GridPanel.removeAll({addEmptyRecord: false, clearAll: true});
		this.GridPanel.getGrid().getStore().load({
			callback: function (records, options, success) {
			},
			params: params
		});
	},
	doReset: function () {
		var form = this;
		var lspefLpuSection_Name = form.findById('lspefLpuSection_Name').getValue();

		this.FilterPanel.getForm().reset();
		form.findById('lspefLpuSection_Name').setValue(lspefLpuSection_Name);
	},
	loadMask: null,
	maximizable: true,
	maximized: false,
	gridPanelAutoLoad: false,
	minHeight: 550,
	minWidth: 750,
	modal: true,
	onHide: Ext.emptyFn,
	plain: true,
	resizable: true,

	show: function () {
		sw.Promed.swBedDowntimeJournalWindow.superclass.show.apply(this, arguments);
		this.isClose = 1;
		this.restore();
		this.center();
		this.maximize();

		if (!arguments[0].userMedStaffFact) {
			arguments[0].userMedStaffFact = {};
		}

		if (arguments[0].userMedStaffFact.LpuSection_Name) {
			this.setTitle(WND_BED_DOWNTIME_REG + ' - ' + arguments[0].userMedStaffFact.LpuSection_Name);
			this.userMedStaffFact = arguments[0].userMedStaffFact;
		} else {
			this.setTitle(WND_BED_DOWNTIME_REG);
			this.userMedStaffFact = null;
		}

		var base_form = this.FilterPanel.getForm();

		var LpuSection_id = null;
		if (this.userMedStaffFact && !Ext.isEmpty(this.userMedStaffFact.LpuSection_id)) {
			LpuSection_id = this.userMedStaffFact.LpuSection_id;
		} else {
			LpuSection_id = sw.Promed.MedStaffFactByUser.current.LpuSection_id;
		}

		this.FilterPanel.fieldSet.expand();

		if (arguments[0].userMedStaffFact.ARMType) {
			switch (arguments[0].userMedStaffFact.ARMType) {
				case 'stacnurse':
					this.formMode = 'workplace';
					//this.setTitle('АРМ постовой медсестры - ' + this.userMedStaffFact.LpuSection_Name);
					sw.Promed.MedStaffFactByUser.setMenuTitle(this, this.userMedStaffFact);
					this.LeftPanel.setVisible(true);

					this.createListLpuSectionWard();

					break;
				default://'common','stac',null
					this.formMode = 'journal';
					this.LeftPanel.setVisible(false);
					break;
			}
		} else if (arguments[0].MedService_id && arguments[0].MedService_id > 0) {
			sw.Promed.MedStaffFactByUser.setMenuTitle(this, {
				MedService_id: arguments[0].MedService_id,
				MedService_Name: arguments[0].MedService_Name,
				MedPersonal_id: getGlobalOptions().CurMedPersonal_id
			});
			this.LeftPanel.setVisible(true);
		}

		this.callback = Ext.emptyFn;
		this.onHide = Ext.emptyFn;

		this.doReset();

		this.GridPanel.ViewGridStore.sortData = function (f, direction) {
			direction = direction || 'ASC';
			var st = this.fields.get(f).sortType;
			var multipleSortInfo = this.fields.get(f).multipleSortInfo;
			var caseInsensitively = this.fields.get(f).caseInsensitively;
			if (typeof direction == 'object') {
				multipleSortInfo = direction;
				direction = 'ASC';
			} else {
				var fn = function (r1, r2) {
					var v1 = st(r1.data[f]), v2 = st(r2.data[f]);
					if (caseInsensitively !== undefined && v1.toLowerCase) {
						v1 = v1.toLowerCase();
						v2 = v2.toLowerCase();
					}
					var ret = v1 > v2 ? 1 : (v1 < v2 ? -1 : 0);
					if (multipleSortInfo !== undefined) {
						ret = 0;
					}
					for (i = 0; (multipleSortInfo !== undefined && ret == 0 && i < multipleSortInfo.length); i++) {
						var x1 = r1.data[multipleSortInfo[i].field], x2 = r2.data[multipleSortInfo[i].field];
						var dir = (direction != multipleSortInfo[i].direction) ? direction.toggle("ASC", "DESC") : direction;
						ret = (x1 > x2) ? 1 : ((x1 < x2) ? -1 : 0);
						if (dir === 'DESC') ret = -ret;
					}

					return ret;
				};
			}

			this.data.sort(direction, fn);
			if (this.snapshot && this.snapshot != this.data) {
				this.snapshot.sort(direction, fn);
			}
		};

		this.syncSize();
		this.doLayout();

		var form = this;
		form.findById('lspefLpuSection_Name').setValue(arguments[0].userMedStaffFact.LpuSection_Name);

		this.dateMenu.setValue(Ext.util.Format.date(new Date(new Date().getFullYear(), 0, 1), 'd.m.Y') + ' - ' + Ext.util.Format.date(new Date.now(), 'd.m.Y'));

		this.doSearch();

		this.GridPanel.addActions({
			name: 'action_export',
			text: langs('экспорт'),
			handler: function () {
				this.exportRegistryToXLS();
			}.createDelegate(this)
		});
	},
	title: WND_BED_DOWNTIME_REG,
	width: 850
});