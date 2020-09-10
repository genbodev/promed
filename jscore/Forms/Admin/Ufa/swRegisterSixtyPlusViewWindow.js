/* 
 *swRegisterSixtyPlusViewWindow - окно регистра Скрининг населения 60+
 * @author      Apaev Alexander
 * @version     07.12.2018
 */

sw.Promed.swRegisterSixtyPlusViewWindow = Ext.extend(sw.Promed.BaseForm, {
	title: 'Регистр «Скрининг населения возраста 60+»',
	width: 800,
	codeRefresh: true,
	objectName: 'swRegisterSixtyPlusViewWindow',
	id: 'swRegisterSixtyPlusViewWindow',
	objectSrc: '/jscore/Forms/Admin/Ufa/swRegisterSixtyPlusViewWindow.js',
	buttonAlign: 'left',
	closable: true,
	closeAction: 'hide',
	collapsible: true,
	height: 550,
	layout: 'border',
	maximizable: true,
	minHeight: 550,
	minWidth: 800,
	modal: false,
	plain: true,
	resizable: true,

	getButtonSearch: function () {
		return Ext.getCmp('SearchButton');
	},
	doReset: function () {

		var current_window = this;
		var filter_form = current_window.findById('RegisterSixtyPlusFilterForm'),
				AttachLpu_id = filter_form.getForm().findField('AttachLpu_id'),
				LpuAttachType_id = filter_form.getForm().findField('LpuAttachType_id');
		filter_form.getForm().reset();
		LpuAttachType_id.setValue(1);
		AttachLpu_id.setValue(getGlobalOptions().lpu_id);
		var aktivTab = Ext.getCmp('RegisterSixtyPlus_SearchFilterTabbar');
		aktivTab.setActiveTab(2);
		aktivTab.setActiveTab(5);
		if (!isSuperAdmin() || !haveArmType('spec_mz')) {
			aktivTab.setActiveTab(2);
			AttachLpu_id.setValue(getGlobalOptions().lpu_id);
			filter_form.getForm().findField('AttachLpu_id').setDisabled(true);
			filter_form.getForm().findField('LpuAttachType_id').setDisabled(true);
			aktivTab.setActiveTab(5);
		}
		current_window.findById('RegisterSixtyPlus').getGrid().getStore().removeAll();

	},

	doSearch: function (params) {
		if (typeof params != 'object') {
			params = {};
		}
		var base_form = this.findById('RegisterSixtyPlusFilterForm').getForm();

		var grid = this.RegisterSixtyPlusSearchFrame.getGrid();

		if (!base_form.isValid()) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function () {
					//
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет поиск..."});
		loadMask.show();

		var post = getAllFormFieldValues(this.findById('RegisterSixtyPlusFilterForm'));

		post.limit = 100;
		post.start = 0;

		if (base_form.isValid()) {
			this.RegisterSixtyPlusSearchFrame.ViewActions.action_refresh.setDisabled(false);
			grid.getStore().removeAll();
			grid.getStore().load({
				callback: function (records, options, success) {
					loadMask.hide();
				},
				params: post
			});

		}

	}, /*
	 doPersonRegisterSixtyPlusOut: function (gridPanel) {
	 var record = gridPanel.getGrid().getSelectionModel().getSelected();
	 
	 var params = {
	 Person_id: record.get('Person_id'),
	 callback: function () {
	 gridPanel.getAction('action_refresh').execute();
	 }
	 };
	 
	 getWnd('swRegisterSixtyPlusOutWindow').show(params);
	 },*/

	getRecordsCount: function () {

		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет подсчет записей..."});
		loadMask.show();

		var count = Ext.getCmp('RegisterSixtyPlus').getGrid().store.getCount();
		sw.swMsg.alert('Подсчет записей', 'Найдено записей: ' + count);

		loadMask.hide();

	},

	openViewWindow: function (action) {
		if (getWnd('swRegisterSixtyPlusEditWindow').isVisible()) {
			sw.swMsg.alert('Сообщение', 'Окно просмотра уже открыто');
			return false;
		}

		var grid = this.RegisterSixtyPlusSearchFrame.getGrid();
		if (!grid.getSelectionModel().getSelected()) {
			return false;
		}
		var selected_record = grid.getSelectionModel().getSelected();

		var params = {
			editType: this.editType,
			Person_id: selected_record.data.Person_id,
			Person_Age: selected_record.data.Person_Age,
			OKS: selected_record.data.RegisterSixtyPlus_isSetProfileOKS,
			BSK: selected_record.data.RegisterSixtyPlus_isSetProfileBSK,
			ONMK: selected_record.data.RegisterSixtyPlus_isSetProfileONMK,
			ZNO: selected_record.data.RegisterSixtyPlus_isSetProfileZNO,
			CD: selected_record.data.RegisterSixtyPlus_isSetProfileDiabetes,
			MSE: selected_record.data.InvalidGroupType_Name,
			LpuRegion_Descr: selected_record.data.LpuRegion_Descr,
			DU: selected_record.data.RegisterSixtyPlus_isSetPersonDisp,
			Sex_id: selected_record.data.Sex_id
		};

		params.action = action;
		getWnd('swRegisterSixtyPlusEditWindow').show(params);
	},

	listeners: {
		'hide': function (win) {
			win.doReset();
		},
		'maximize': function (win) {
			win.findById('RegisterSixtyPlusFilterForm').doLayout();
		},
		'restore': function (win) {
			win.findById('RegisterSixtyPlusFilterForm').doLayout();
		},
		'resize': function (win, nW, nH, oW, oH) {
			win.findById('RegisterSixtyPlus_SearchFilterTabbar').setWidth(nW - 5);
			win.findById('RegisterSixtyPlusFilterForm').setWidth(nW - 5);
		}
	},

	setInformation: function () {
		var wnd = this;
		Ext.Ajax.request({
			callback: function (opt, success, resp) {
				var response_obj = Ext.util.JSON.decode(resp.responseText);
				if (response_obj && response_obj[0]) {
					wnd.InformationPanel.setData('update_date', response_obj[0].RegisterSixtyPlus_insDT);
					//wnd.InformationPanel.setData('record_count', response_obj[0].Record_Count);
					wnd.InformationPanel.showData();
				}
			},
			url: '/?c=RegisterSixtyPlus&m=getupdDT'
		});
	},

	show: function () {
		sw.Promed.swRegisterSixtyPlusViewWindow.superclass.show.apply(this, arguments);

		this.RegisterSixtyPlusSearchFrame.addActions({
			name: 'open_emk',
			text: 'Открыть ЭМК',
			tooltip: 'Открыть электронную медицинскую карту пациента',
			iconCls: 'open16',
			handler: function () {
				this.emkOpen();
			}.createDelegate(this)
		});

		Ext.getCmp('swRegisterSixtyPlusViewWindow').findById('RegisterSixtyPlusFilterForm').items.items[0].items.items[0].hide();
		Ext.getCmp('swRegisterSixtyPlusViewWindow').findById('RegisterSixtyPlusFilterForm').getForm().findField('PersonCard_IsDms').hide();
		Ext.getCmp('swRegisterSixtyPlusViewWindow').findById('RegisterSixtyPlusFilterForm').getForm().findField('PersonCard_IsDms').hideLabel = true;

		//Добавление кнопок исключить из регистра
		/*this.RegisterSixtyPlusSearchFrame.addActions({
		 name: 'action_out',
		 text: langs('Исключить из регистра'),
		 handler: function () {
		 this.doPersonRegisterSixtyPlusOut(this.RegisterSixtyPlusSearchFrame);
		 }.createDelegate(this)
		 });*/


		this.restore();
		this.center();
		this.maximize();
		this.doReset();

		if (arguments[0].userMedStaffFact)
		{
			this.userMedStaffFact = arguments[0].userMedStaffFact;
		} else {
			if (sw.Promed.MedStaffFactByUser.last)
			{
				this.userMedStaffFact = sw.Promed.MedStaffFactByUser.last;
			} else
			{
				sw.Promed.MedStaffFactByUser.selectARM({
					ARMType: arguments[0].ARMType,
					onSelect: function (data) {
						this.userMedStaffFact = data;
					}.createDelegate(this)
				});
			}
		}
		this.editType = 'all';
		if (arguments[0] && arguments[0].editType)
		{
			this.editType = arguments[0].editType;
		}

		this.doLayout();

		var base_form = this.findById('RegisterSixtyPlusFilterForm').getForm();
		var aktivTab = Ext.getCmp('RegisterSixtyPlus_SearchFilterTabbar');

		aktivTab.setActiveTab(5);
		//base_form.findField('PersonRegisterType_id').setValue(2);
		//base_form.findField('YesNo_id').setValue(2); состоит на дисп учете

		this.findById('RegisterSixtyPlus_SearchFilterTabbar').setActiveTab(5);

		base_form.findField('OnkoCtrComment_id').getStore().clearFilter();
		base_form.findField('OnkoCtrComment_id').lastQuery = '';
		base_form.findField('OnkoCtrComment_id').getStore().filterBy(function (r) {
			return r.get('OnkoCtrComment_id').inlist([1, 2]);
		});
		this.setInformation();

	},

	emkOpen: function () {
		var grid = this.RegisterSixtyPlusSearchFrame.getGrid();

		if (!grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('Person_id'))
		{
			Ext.Msg.alert('Ошибка', 'Не выбрана запись!');
			return false;
		}
		var record = grid.getSelectionModel().getSelected();

		getWnd('swPersonEmkWindow').show({
			Person_id: record.get('Person_id'),
			Server_id: record.get('Server_id'),
			PersonEvn_id: record.get('PersonEvn_id'),
			userMedStaffFact: this.userMedStaffFact,
			MedStaffFact_id: this.userMedStaffFact.MedStaffFact_id,
			LpuSection_id: this.userMedStaffFact.LpuSection_id,
			ARMType: 'common',
			callback: function ()
			{
			}.createDelegate(this)
		});
	},

	initComponent: function () {
		var win = this;

		this.RegisterSixtyPlusSearchFrame = new sw.Promed.ViewFrame({
			actions: [
				{name: 'action_add', hidden: true},
				{name: 'action_edit', hidden: true},
				{name: 'action_view', handler: function () {
						this.openViewWindow('view');
					}.createDelegate(this)},
				{name: 'action_delete', hidden: true},
				{name: 'action_refresh'},
				{name: 'action_print'}
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 150,
			autoLoadData: false,
			dataUrl: C_SEARCH,
			id: 'RegisterSixtyPlus',
			object: 'RegisterSixtyPlus',
			pageSize: 100,
			paging: true,
			region: 'center',
			root: 'data',
			stringfields: [
				//{name: 'PersonRegister_id', type: 'int', header: 'ID', key: true},
				//{name: 'RegisterSixtyPlus_id', type: 'int', hidden: true},
				//{name: 'RegisterSixtyPlus_issueDate', type: 'date', format: 'd.m.Y', hidden: true},
				{name: 'Person_id', type: 'int', hidden: true},
				{name: 'Server_id', type: 'int', hidden: true},
				{name: 'PersonEvn_id', type: 'int', hidden: true},
				{name: 'Lpu_id', type: 'int', hidden: true},
				{name: 'Sex_id', type: 'int', hidden: true},
				{name: 'Person_Surname', type: 'string', header: 'Фамилия', width: 100},
				{name: 'Person_Firname', type: 'string', header: 'Имя', width: 100},
				{name: 'Person_Secname', type: 'string', header: 'Отчество', width: 100},
				{name: 'Person_Age', type: 'int', header: 'Возраст', width: 80},
				//{name: 'Person_Birthday', type: 'date', format: 'd.m.Y', header: 'Д/р', width: 90},
				//{name: 'Person_deadDT', type: 'date', format: 'd.m.Y', header: 'Дата смерти', width: 90},
				{name: 'SAD', type: 'int', header: 'САД', width: 35},
				{name: 'DAD', type: 'int', header: 'ДАД', width: 35},
				{name: 'RegisterSixtyPlus_IMTMeasure', /*type: 'float',*/header: 'ИМТ', width: 50, renderer: function (value, p, r) {
						var str = value;
						if (str !== null && str !=="") {
							var result = parseFloat(str.replace(/\s/g, "").replace(",", "."));
							if (result > 24.9) {
								return result + ' ' + '<img src="/img/icons/emk/AD-high-icon.png">';
							} else {
								return result;
							}
						} else {
							return str;
						}
					}},
				{name: 'RegisterSixtyPlus_CholesterolMeasure', /* type: 'string', */header: 'Холестерин', width: 90, renderer: function (value, p, r) {
						var BSK = r.get('RegisterSixtyPlus_isSetProfileBSK');
						var str = value;
						if (str !== null && str !=="") {
							var result = parseFloat(str.replace(/\s/g, "").replace(",", "."));
							if (BSK == 0) {
								if (result > 5.2) {
									return result + ' ' + '<img src="/img/icons/emk/AD-high-icon.png">';
								} else {
									return result;
								}
							} else if (BSK == 1)
								if (result > 3.6) {
									return result + ' ' + '<img src="/img/icons/emk/AD-high-icon.png">';
								} else {
									return result;
								}
						} else {
							return str;
						}
					}},
				{name: 'RegisterSixtyPlus_GlucoseMeasure', type: 'string', header: 'Глюкоза', width: 90, renderer: function (value, p, r) {
						var str = value;
						if (str !== null && str !=="") {
							var result = parseFloat(str.replace(/\s/g, "").replace(",", "."));
							if (result > 6.1) {
								return result + ' ' + '<img src="/img/icons/emk/AD-high-icon.png">';
							}
						} else {
							return str;
						}
					}},
				{name: 'RegisterSixtyPlus_OAKsetDate', type: 'date', format: 'd.m.Y', header: 'ОАК', width: 90},
				{name: 'RegisterSixtyPlus_OAMsetDate', type: 'date', format: 'd.m.Y', header: 'ОАМ', width: 90},
				{name: 'RegisterSixtyPlus_EKGsetDate', type: 'date', format: 'd.m.Y', header: 'ЭКГ', width: 90},
				{name: 'RegisterSixtyPlus_FluorographysetDate', type: 'date', format: 'd.m.Y', header: 'Флюрография', width: 90},
				{name: 'RegisterSixtyPlus_OnkoProfileDtBeg', header: 'Дата анкетирования', width: 90, hidden: true},
				{name: 'RegisterSixtyPlus_OnkoControlIsNeeded', header: 'Онкоконтроль', width: 120, renderer: function (v, p, r) {
						var onko_date = r.get('RegisterSixtyPlus_OnkoProfileDtBeg');
						var onko = r.get('RegisterSixtyPlus_OnkoControlIsNeeded');
						if (onko == 1 && r.get('RegisterSixtyPlus_OnkoProfileDtBeg') !== null) {
							return onko_date + ' ' + '<span style="color: limegreen; font-weight: bold">x</span>';
						} else if (onko == 0 && r.get('RegisterSixtyPlus_OnkoProfileDtBeg') !== null) {
							return onko_date + ' ' + '<img src="/img/icons/tick16-red.png" width="10" height="10"/>';
						} else if (r.get('RegisterSixtyPlus_OnkoProfileDtBeg') !== null) {
							return '';
						}
					}
				},
				{name: 'RegisterSixtyPlus_isSetProfileOKS', /*type: 'checkcolumn', */ header: 'ОКС', /*sort: true,*/ width: 40, renderer: function (val) {
						if (val == 0) {
							return '<span style="color: limegreen; font-weight: bold">x</span>';
						} else if (val == 1) {
							return '<img src="/img/icons/tick16-red.png" width="10" height="10"/>';
						} else
							return '';
					}},
				{name: 'RegisterSixtyPlus_isSetProfileBSK', /*type: 'checkcolumn',*/ header: 'БСК', width: 40, renderer: function (val) {
						if (val == 0) {
							return '<span style="color: limegreen; font-weight: bold">x</span>';
						} else if (val == 1) {
							return '<img src="/img/icons/tick16-red.png" width="10" height="10"/>';
						} else
							return '';
					}},
				{name: 'RegisterSixtyPlus_isSetProfileONMK', /*type: 'checkcolumn',*/ header: 'ОНМК', /*sort: true,*/ width: 40, renderer: function (val) {
						if (val == 0) {
							return'<span style="color: limegreen; font-weight: bold">x</span>';
						} else if (val == 1) {
							return '<img src="/img/icons/tick16-red.png" width="10" height="10"/>';
						} else
							return '';
					}},
				{name: 'RegisterSixtyPlus_isSetProfileZNO', /*type: 'checkcolumn',*/ header: 'ЗНО', /*sort: true,*/ width: 40, renderer: function (val) {
						if (val == 0) {
							return '<span style="color: limegreen; font-weight: bold">x</span>';
						} else if (val == 1) {
							return '<img src="/img/icons/tick16-red.png" width="10" height="10"/>';
						} else
							return '';
					}},
				{name: 'RegisterSixtyPlus_isSetProfileDiabetes', /*type: 'checkcolumn',*/ header: 'СД', /*sort: true,*/ width: 40, renderer: function (val) {
						if (val == 0) {
							return '<span style="color: limegreen; font-weight: bold">x</span>';
						} else if (val == 1) {
							return '<img src="/img/icons/tick16-red.png" width="10" height="10"/>';
						} else
							return '';
					}},
				{name: 'RegisterSixtyPlus_isSetPersonDisp', /*type: 'checkcolumn',*/ header: 'Дисп. учёт', /*sort: true,*/ width: 90, renderer: function (val) {
						if (val == 0) {
							return '<span style="color: limegreen; font-weight: bold">x</span>';
						} else if (val == 1) {
							return '<img src="/img/icons/tick16-red.png" width="10" height="10"/>';
						} else
							return '';
					}},
				{name: 'InvalidGroupType_Name', type: 'string', header: 'МСЭ', width: 100},
				//{name: 'Lpu_Nick', type: 'string', header: 'МО прикрепления', width: 100}
				//{name: 'LpuAttachType_Name', type: 'string', header: 'Тип прикрепления', width: 120},
				//{name: 'LpuRegionType_Name', type: 'string', header: 'Тип участка', width: 120},
				{name: 'uch', type: 'string', header: 'Участок', width: 150},
				{name: 'LpuRegion_Descr', type: 'string', header: 'ФАП', }/*,
				 {name: '', type: 'date', format: 'd.m.Y', header: 'Дата исключения', width: 90},*/

			],
			toolbar: true,
			totalProperty: 'totalCount',
			onBeforeLoadData: function () {
				this.getButtonSearch().disable();
			}.createDelegate(this),
			onLoadData: function () {
				this.getButtonSearch().enable();
			}.createDelegate(this),
			onRowSelect: function (sm, index, record) {
			},
			onDblClick: function (x, c, v) {
			}
		});

		var RegisterSixtyPlus = this.RegisterSixtyPlusSearchFrame.getGrid().on(
				'rowdblclick',
				function () {
					this.openViewWindow('view');
				}.createDelegate(this)
				);

		this.RegisterSixtyPlusSearchFrame.ViewGridPanel.view = new Ext.grid.GridView(
				{
					getRowClass: function (row, index)
					{
						var cls = '';
						var sum = 0;
						//var risk = Ext.getCmp('swRegisterSixtyPlusEditWindow').findById('RiskType_id').getValue();
						var sad = row.get('SAD');
						var dad = row.get('DAD');
						var imt = parseFloat(row.get('RegisterSixtyPlus_IMTMeasure'));
						if (row.get('RegisterSixtyPlus_CholesterolMeasure') !== null) {
							var hol = parseFloat(row.get('RegisterSixtyPlus_CholesterolMeasure').replace(",", "."));
						}
						if (row.get('RegisterSixtyPlus_GlucoseMeasure') !== null) {
							var glu = parseFloat(row.get('RegisterSixtyPlus_GlucoseMeasure').replace(",", "."));
						}
						if (imt < 25.0 || imt == null) {
							sum += 0;
						} else if (imt >= 25.0 && imt < 30.0) {
							sum += 1;
						} else if (imt >= 30.0) {
							sum += 2;
						}
						;

						if (hol < 3.7 || hol == null) {
							sum += 0;
						} else if (hol >= 3.7 && hol < 7.1) {
							sum += 1;
						} else if (hol >= 7.1) {
							sum += 2;
						}
						;

						if (glu < 6.2 || glu == null) {
							sum += 0;
						} else if (glu >= 6.2 && glu < 7.0) {
							sum += 1;
						} else if (glu >= 7.0) {
							sum += 2;
						}
						;

						if (sum >= 1 && sum <= 5) {
							cls = "x-grid-rowbackpalepink"; //cls = "x-grid-rowbackred x-grid-rowred";
						} else if (sum >= 6) {
							cls = "x-grid-rowbackred x-grid-rowred";
						} else {
							cls = "";
						}
						return cls;

					}
				});
		this.InformationPanel = new Ext.Panel({
			bodyStyle: 'padding: 0px',
			border: false,
			region: 'south',
			autoHeight: true,
			frame: true,
			labelAlign: 'right',
			title: null,
			collapsible: true,
			data: null,
			html_tpl: null,
			win: win,
			setTpl: function (tpl) {
				this.html_tpl = tpl;
			},
			setData: function (name, value) {
				if (!this.data)
					this.data = new Ext.util.MixedCollection();
				if (name && value) {
					var idx = this.data.findIndex('name', name);
					if (idx >= 0) {
						this.data.itemAt(idx).value = value;
					} else {
						this.data.add({
							name: name,
							value: value
						});
					}
				}
			},
			showData: function () {
				var html = this.html_tpl;
				if (this.data)
					this.data.each(function (item) {
						html = html.replace('{' + item.name + '}', item.value, 'gi');
					});
				html = html.replace(/{[a-zA-Z_0-9]+}/g, '');
				this.body.update(html);
				if (this.win) {
					this.win.syncSize();
					this.win.doLayout();
				}
			},
			clearData: function () {
				this.data = null;
			}
		});
		win.InformationPanel.setTpl("Дата обновления {update_date}");

		Ext.apply(this, {
			buttons: [
				{
					handler: function () {
						this.doSearch();
					}.createDelegate(this),
					iconCls: 'search16',
					tabIndex: TABINDEX_ORW + 120,
					id: 'SearchButton',
					text: BTN_FRMSEARCH
				}, {
					handler: function () {
						this.doReset();
					}.createDelegate(this),
					iconCls: 'resetsearch16',
					tabIndex: TABINDEX_ORW + 121,
					text: BTN_FRMRESET
				},
				{
					text: '-'
				},
				HelpButton(this, -1),
				{
					handler: function () {
						this.hide();
					}.createDelegate(this),
					iconCls: 'cancel16',
					onShiftTabAction: function () {
						this.buttons[this.buttons.length - 2].focus();
					}.createDelegate(this),
					onTabAction: function () {
						this.findById('RegisterSixtyPlus_SearchFilterTabbar').getActiveTab().fireEvent('activate', this.findById('RegisterSixtyPlus_SearchFilterTabbar').getActiveTab());
					}.createDelegate(this),
					tabIndex: TABINDEX_ORW + 124,
					text: BTN_FRMCLOSE
				}],
			getFilterForm: function () {
				if (this.filterForm == undefined) {
					this.filterForm = this.findById('RegisterSixtyPlusFilterForm');

				}
				return this.filterForm;
			},
			items: [getBaseSearchFiltersFrame({
					isDisplayPersonRegisterRecordTypeField: false,
					allowPersonPeriodicSelect: false,
					id: 'RegisterSixtyPlusFilterForm',
					labelWidth: 130,
					ownerWindow: this,
					searchFormType: 'RegisterSixtyPlus',
					tabIndexBase: TABINDEX_ORW,
					tabPanelHeight: 225,
					region: 'north',
					tabPanelId: 'RegisterSixtyPlus_SearchFilterTabbar',
					tabs: [{
							autoHeight: true,
							bodyStyle: 'margin-top: 5px;',
							border: false,
							labelWidth: 220,
							layout: 'form',
							listeners: {
								'activate': function () {
									//this.getFilterForm().getForm().findField('PersonRegisterType_id').focus(250, true);
									//this.getFilterForm().getForm().findField('PersonRegisterType_id').setValue(2);
								}.createDelegate(this)
							},
							title: '<u>6</u>. Регистр',
							items: [
								{
									layout: 'column',
									border: false,
									items: [
										{
											layout: 'form',
											border: false,
											items: [
												/*{
												 xtype: 'swpersonregistertypecombo',
												 hiddenName: 'PersonRegisterType_id',
												 width: 170
												 },*/
												{
													fieldLabel: 'РРЗ',
													xtype: 'swbaselocalcombo',
													id: 'RiskType',
													name: 'RiskType',
													hiddenName: 'RiskType_id',
													editable: false,
													mode: 'local',
													displayField: 'name',
													width: 170,
													valueField: 'id',
													codeField: 'selection_code',
													triggerAction: 'all',
													tpl: new Ext.XTemplate(
															'<tpl for="."><div class="x-combo-list-item">',
															'<font color="red">{selection_code}</font>&nbsp;{name}',
															'</div></tpl>'
															),
													store: new Ext.data.SimpleStore({
														autoLoad: true,
														fields: [{name: 'id', type: 'int'},
															{name: 'selection_code', type: 'int'},
															{name: 'name', type: 'string'}],
														key: 'id',
														data: [[1, 1, 'Высокий'],
															[2, 2, 'Повышенный'],
															[3, 3, 'Норма']]
													})
												},
												{
													fieldLabel: 'Профиль',
													xtype: 'swbaselocalcombo',
													id: 'Profile',
													name: 'Profile',
													hiddenName: 'ProfileData',
													editable: false,
													mode: 'local',
													displayField: 'name',
													width: 170,
													valueField: 'id',
													codeField: 'selection_code',
													triggerAction: 'all',
													tpl: new Ext.XTemplate(
															'<tpl for="."><div class="x-combo-list-item">',
															'<font color="red">{selection_code}</font>&nbsp;{name}',
															'</div></tpl>'
															),
													store: new Ext.data.SimpleStore({
														autoLoad: true,
														fields: [{name: 'id', type: 'int'},
															{name: 'selection_code', type: 'int'},
															{name: 'name', type: 'string'}],
														key: 'id',
														data: [[1, 1, 'ЗНО'],
															[2, 2, 'ОНМК'],
															[3, 3, 'ОКС'],
															[4, 4, 'БСК'],
															[5, 5, 'СД']]
													})
												},
												{
													fieldLabel: 'Холестерин',
													xtype: 'swbaselocalcombo',
													id: 'Cholesterol',
													name: 'Cholesterol',
													hiddenName: 'Cholesterol_id',
													editable: false,
													mode: 'local',
													displayField: 'name',
													width: 170,
													valueField: 'id',
													codeField: 'selection_code',
													triggerAction: 'all',
													tpl: new Ext.XTemplate(
															'<tpl for="."><div class="x-combo-list-item">',
															'<font color="red">{selection_code}</font>&nbsp;{name}',
															'</div></tpl>'
															),
													store: new Ext.data.SimpleStore({
														autoLoad: true,
														fields: [{name: 'id', type: 'int'},
															{name: 'selection_code', type: 'int'},
															{name: 'name', type: 'string'}],
														key: 'id',
														data: [[3, 1, 'Выше нормы'],
															[2, 2, 'Норма']]
													})
												},
												{
													fieldLabel: 'Глюкоза',
													xtype: 'swbaselocalcombo',
													id: 'Sugar',
													name: 'Sugar',
													hiddenName: 'Sugar_id',
													editable: false,
													mode: 'local',
													displayField: 'name',
													width: 170,
													valueField: 'id',
													codeField: 'selection_code',
													triggerAction: 'all',
													tpl: new Ext.XTemplate(
															'<tpl for="."><div class="x-combo-list-item">',
															'<font color="red">{selection_code}</font>&nbsp;{name}',
															'</div></tpl>'
															),
													store: new Ext.data.SimpleStore({
														autoLoad: true,
														fields: [{name: 'id', type: 'int'},
															{name: 'selection_code', type: 'int'},
															{name: 'name', type: 'string'}],
														key: 'id',
														data: [[3, 1, 'Выше нормы'],
															[2, 2, 'Норма']]
													})
												},
												{
													fieldLabel: 'ИМТ',
													xtype: 'swbaselocalcombo',
													id: 'IMT',
													name: 'IMT',
													hiddenName: 'IMT_id',
													editable: false,
													mode: 'local',
													displayField: 'name',
													width: 170,
													valueField: 'id',
													codeField: 'selection_code',
													triggerAction: 'all',
													tpl: new Ext.XTemplate(
															'<tpl for="."><div class="x-combo-list-item">',
															'<font color="red">{selection_code}</font>&nbsp;{name}',
															'</div></tpl>'
															),
													store: new Ext.data.SimpleStore({
														autoLoad: true,
														fields: [{name: 'id', type: 'int'},
															{name: 'selection_code', type: 'int'},
															{name: 'name', type: 'string'}],
														key: 'id',
														data: [[3, 1, 'Выше нормы'],
															[2, 2, 'Норма']]
													})
												}
											]
										},
										{
											layout: 'form',
											border: false,
											items: [
												{
													fieldLabel: 'САД',
													xtype: 'swbaselocalcombo',
													id: 'SAD',
													name: 'SAD',
													hiddenName: 'SAD_id',
													editable: false,
													mode: 'local',
													displayField: 'name',
													width: 170,
													valueField: 'id',
													codeField: 'selection_code',
													triggerAction: 'all',
													tpl: new Ext.XTemplate(
															'<tpl for="."><div class="x-combo-list-item">',
															'<font color="red">{selection_code}</font>&nbsp;{name}',
															'</div></tpl>'
															),
													store: new Ext.data.SimpleStore({
														autoLoad: true,
														fields: [{name: 'id', type: 'int'},
															{name: 'selection_code', type: 'int'},
															{name: 'name', type: 'string'}],
														key: 'id',
														data: [[3, 1, 'Выше нормы'],
															[2, 2, 'Норма'],
															[1, 3, 'Ниже нормы']]
													})
												},
												{
													fieldLabel: 'ДАД',
													xtype: 'swbaselocalcombo',
													id: 'DAD',
													name: 'DAD',
													hiddenName: 'DAD_id',
													editable: false,
													mode: 'local',
													displayField: 'name',
													width: 170,
													valueField: 'id',
													codeField: 'selection_code',
													triggerAction: 'all',
													tpl: new Ext.XTemplate(
															'<tpl for="."><div class="x-combo-list-item">',
															'<font color="red">{selection_code}</font>&nbsp;{name}',
															'</div></tpl>'
															),
													store: new Ext.data.SimpleStore({
														autoLoad: true,
														fields: [{name: 'id', type: 'int'},
															{name: 'selection_code', type: 'int'},
															{name: 'name', type: 'string'}],
														key: 'id',
														data: [[3, 1, 'Выше нормы'],
															[2, 2, 'Норма'],
															[1, 3, 'Ниже нормы']]
													})
												},
												{
													autoLoad: true,
													fieldLabel: 'Онкоконтроль',
													width: 170,
													hiddenName: 'OnkoCtrComment_id',
													xtype: 'amm_OnkoCtrCommentCombo'

												},
												{
													fieldLabel: 'Инвалидность',
													xtype: 'swbaselocalcombo',
													id: 'Disability',
													name: 'Disability',
													hiddenName: 'DisabilityData_id',
													editable: false,
													mode: 'local',
													displayField: 'name',
													width: 170,
													valueField: 'id',
													codeField: 'selection_code',
													triggerAction: 'all',
													tpl: new Ext.XTemplate(
															'<tpl for="."><div class="x-combo-list-item">',
															'<font color="red">{selection_code}</font>&nbsp;{name}',
															'</div></tpl>'
															),
													store: new Ext.data.SimpleStore({
														autoLoad: true,
														fields: [{name: 'id', type: 'int'},
															{name: 'selection_code', type: 'int'},
															{name: 'name', type: 'string'}],
														data:
																[
																	[1, 1, 'Нет'],
																	[2, 2, 'I'],
																	[3, 3, 'II'],
																	[4, 4, 'III']
																],
														key: 'id'
													})
												},
												{
													xtype: 'swcommonsprcombo',
													fieldLabel: 'Состоит на Дисп. учет',
													comboSubject: 'YesNo',
													hiddenName: 'YesNo_id',
													width: 170

												},
												{
													fieldLabel: 'Дата исключения из регистра',
													name: 'PersonRegister_disDate_Range',
													plugins: [new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
													width: 170,
													xtype: 'daterangefield',
													hidden: true,
													hideLabel: true
												}
											]
										}

									]
								}
							]
						}

					]
				}),
				win.RegisterSixtyPlusSearchFrame,
				win.InformationPanel]
		});

		sw.Promed.swRegisterSixtyPlusViewWindow.superclass.initComponent.apply(this, arguments);

	}

});
