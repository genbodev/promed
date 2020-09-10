/**
 * swTFOMSQueryWindow - окно просмотра запросов в ФСС
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Common
 * @access			public
 * @copyright		Copyright (c) 2018 Swan Ltd.
 * @author			Sobenin Alex aka GTP_fox
 * @version			24.10.2018
 */

/*NO PARSE JSON*/
sw.Promed.swTFOMSQueryWindow = Ext.extend(sw.Promed.BaseForm,
	{
		maximizable: false,
		maximized: true,
		height: 600,
		width: 900,
		id: 'swTFOMSQueryWindow',
		title: 'Запросы на просмотр ЭМК',
		layout: 'border',
		resizable: true,
		getSelectedTFOMSQueryRec: function(){
			var win = this,
				ret = false,
				rec = win.TFOMSQueryGrid.getGrid().getSelectionModel().getSelected();
			if(rec && rec.get('TFOMSQueryEMK_id'))
				ret = rec;
			return ret;
		},
		deleteObject: function(object) {
			var win = this,
				grid, params = {}, url = '';
			switch(object){
				case 'TFOMSQueryPerson_id':
					grid = win.QueryPersonsGrid.getGrid();
					url = '/?c=TFOMSQuery&m=deletePersonFromQuery';
					break;
				case 'TFOMSQueryEMK_id':
					grid = win.TFOMSQueryGrid.getGrid();
					url = '/?c=TFOMSQuery&m=deleteQuery';
					break
			}

			var selected = grid.getSelectionModel().getSelected();
			if(selected)
				var id = selected.get(object);
			else{
				sw.swMsg.alert(langs('Ошибка'), 'Необходимо выбрать пациента');
			}
			if (!id) {
				return false;
			}
			params[object] = id;
			sw.swMsg.show({
				buttons: Ext.Msg.YESNO,
				fn: function (buttonId, text, obj) {
					if (buttonId == 'yes') {
						Ext.Ajax.request({
							params: params,
							callback: function(opt, success, response) {
								grid.getStore().reload();
							},
							url: url
						});
					}
				},
				icon: Ext.MessageBox.QUESTION,
				msg: langs('Удалить выбранную запись?'),
				title: langs('Вопрос')
			});
		},
		openAccess: function() {
			var win = this,
				rec = win.getSelectedTFOMSQueryRec(),
				params = {};
			if (!rec) { return false; }
			params.TFOMSQueryEMK_id = rec.get('TFOMSQueryEMK_id');
			sw.swMsg.show({
				buttons: Ext.Msg.YESNO,
				fn: function (buttonId, text, obj) {
					if (buttonId == 'yes') {
						Ext.Ajax.request({
							callback: function(opt, scs, response) {
								win.TFOMSQueryGrid.loadData();
							},
							params: params,
							url: '/?c=TFOMSQuery&m=openAccessQuery'
						});
					}
				},
				icon: Ext.MessageBox.QUESTION,
				msg: 'Предоставить доступ на просмотр ЭМК по всем выбранным пациентам организации ' +rec.get('Lpu_Nick')+'?',
				title: langs('Вопрос')
			});
		},
		getNotViewEMK: function(){
			var win = this,
				store = win.QueryPersonsGrid.getGrid().getStore(),
				arr = store.getRange(),
				notView = false;
			for(var i=0;i<arr.length && !notView;i++){
				if(arr[i].get('TFOMSQueryPerson_IsView')!='true')
					notView = true;
			}
			return notView;
		},
		closeAccess: function(){
			var win = this,
				notView = win.getNotViewEMK(),
				msg = '';
			if (notView)
				msg += 'В списке есть пациенты, по которым ещё не просмотрена ЭМК. <br>';
			msg += 'Закрыть доступ на просмотр ЭМК организации ';
			this.setStatusQuery(5,msg);
		},
		sendQuery: function(){
			this.setStatusQuery(2,'Отправить запрос на просмотр ЭМК пациентов в МО ');
		},
		endView: function(){
			this.setStatusQuery(5,'После подтверждения действия доступ к просмотру ЭМК пациентов в данном запросе будет закрыт. <br>Завершить просмотр запроса в МО ');
		},
		setStatusQuery: function(TFOMSQueryStatus_id,msg) {
			var win = this,
				rec = win.getSelectedTFOMSQueryRec(),
				params = {};
			if (!rec) { return false; }
			//this.TFOMSQueryGrid.getMultiSelections().forEach(function (el){
			//if (!Ext.isEmpty(el.get('Newslatter_id')) && el.get('Newslatter_IsActive') == 'false') {records.push(el.get('Newslatter_id'));}
			//});
			params.TFOMSQueryEMK_id = rec.get('TFOMSQueryEMK_id');
			params.TFOMSQueryStatus_id = TFOMSQueryStatus_id; // статус запроса "Отправлен в МО"
			sw.swMsg.show({
				buttons: Ext.Msg.YESNO,
				fn: function (buttonId, text, obj) {
					if (buttonId == 'yes') {
						Ext.Ajax.request({
							callback: function(opt, scs, response) {
								win.TFOMSQueryGrid.loadData();
							},
							params: params,
							url: '/?c=TFOMSQuery&m=setTFOMSQuery'
						});
					}
				},
				icon: Ext.MessageBox.QUESTION,
				msg: msg+rec.get('Lpu_Nick')+'?',
				title: langs('Вопрос')
			});
		},
		openTFOMSQueryEditWindow: function(action) {
			var win = this;
			var params = new Object();

			params.formParams = new Object();

			if (action != 'add') {
				var record = win.getSelectedTFOMSQueryRec();
				if (!record) { return false; }
				params.TFOMSQueryEMK_id = record.get('TFOMSQueryEMK_id');
				if(record.get('TFOMSQueryStatus_id')!=1 && action!='setAccessDate')
					action = 'view';
			}
			params.ARMType = this.ARMType;
			params.action = action;
			params.callback = function(){
				win.TFOMSQueryGrid.getAction('action_refresh').execute();
			}.createDelegate(this);

			getWnd('swTFOMSQueryEditWindow').show(params);
		},
		doResetFilters: function() {
			var win = this,
				base_form = this.filtersPanel.getForm();
			base_form.reset();
			switch(win.ARMType){
				case 'tfoms':
				case 'smo':
					win.comboMO.enable();
					win.comboTFOMS.setValue(getGlobalOptions().org_id);
					break;
				case 'lpuadmin':
				case 'mstat':
					win.comboMO.setValue(getGlobalOptions().lpu_id);
					win.comboTFOMS.enable();
					break;
			}

			if(this.ARMType && ['lpuadmin', 'mstat'].includes(this.ARMType)) {
				base_form.findField('TFOMSQueryStatus_id').getStore().baseParams.forMO = true;
			}
		},
		reloadQueryPersonsGrid: function(TFOMSQueryEMK_id) {
			var win = this;
			if (!TFOMSQueryEMK_id) {
				return false;
			}
			var filters = {
				TFOMSQueryEMK_id : TFOMSQueryEMK_id,
				start: 0,
				limit: 100
			};
			this.QueryPersonsGrid.removeAll({ clearAll: true });
			this.QueryPersonsGrid.loadData({globalFilters: filters});
		},
		doFilter: function() {

			var base_form = this.filtersPanel.getForm();
			//var date = Date.parseDate(getGlobalOptions().date, 'd.m.Y');
			//this.dateMenu.setValue(Ext.util.Format.date(date.add('d',-30), 'd.m.Y')+' - '+Ext.util.Format.date(date));
			var filters = base_form.getValues();
			filters.start = 0;
			filters.limit = 100;
			filters.Org_id = this.comboTFOMS.getValue();
			filters.Lpu_id = this.comboMO.getValue();
			filters.TFOMSQueryEMK_insDT = this.dateMenu.getRawValue();
			if(this.ARMType && ['lpuadmin', 'mstat'].includes(this.ARMType))
				filters.forMO = true;

			this.TFOMSQueryGrid.removeAll({ clearAll: true });
			this.QueryPersonsGrid.removeAll({ clearAll: true });

			this.TFOMSQueryGrid.loadData({ globalFilters: filters });
		},
		scheduleNew: function(params)
		{
			var win = this;
			// Добавление пациента вне записи
			if (getWnd('swPersonSearchWindow').isVisible())
			{
				Ext.Msg.alert(langs('Сообщение'), langs('Окно поиска человека уже открыто'));
				return false;
			}
			//вот это я понимаю, 2-ка, простота и удобство
			var rec = win.getSelectedTFOMSQueryRec();
			if(!rec)
			{
				Ext.Msg.alert(langs('Сообщение'), langs('Необходимо выбрать запрос в который производится добавление'));
				return false;
			}
			var TFOMSQueryEMK_id = rec.get('TFOMSQueryEMK_id'),
				org_id = rec.get('Org_id'),
				lpu_id = rec.get('Lpu_id');
			var personParams = {
				onClose: function(){},
				onSelect: function(pdata)
				{
					getWnd('swPersonSearchWindow').hide();
					Ext.Ajax.request({
						params: {
							Org_id: org_id,
							Lpu_id: lpu_id,
							Person_id: pdata.Person_id,
							TFOMSQueryEMK_id: TFOMSQueryEMK_id
						},
						callback: function(opt, success, response) {
							if ( success ) {
								var obj = Ext.util.JSON.decode(response.responseText);
								if(!obj.success) {
									sw.swMsg.alert(langs('Ошибка'), obj.Error_Msg?obj.Error_Msg:'Данный пациент состоит в другом запросе');
									return false;
								}
							}
							else {
								sw.swMsg.alert(langs('Ошибка'), langs('При добавлении пациента в запрос возникли ошибки'));
							}
							win.QueryPersonsGrid.loadData();
						},
						url: '/?c=TFOMSQuery&m=addPersonToQuery'
					});
				},
				needUecIdentification: true,
				searchMode: 'all'
			};

			getWnd('swPersonSearchWindow').show(personParams);
		},
		setAccessPerson: function(rec, checked){
			var win = this,
				g = win.QueryPersonsGrid,
				recQ = win.getSelectedTFOMSQueryRec();
			if(!rec || !recQ) {
				sw.swMsg.alert(langs('Ошибка'), 'Необходимо выбрать запрос и пациента');
				return false;
			}
			win.getLoadMask(langs('Изменение доступа к ЭМК')).show();
			Ext.Ajax.request({
				params: {
					TFOMSQueryPerson_isAccess: checked?2:1,
					TFOMSQueryPerson_id: rec.get('TFOMSQueryPerson_id'),
					TFOMSQueryEMK_id: rec.get('TFOMSQueryEMK_id'),
					TFOMSQueryStatus_id: recQ.get('TFOMSQueryStatus_id')
				},
				callback: function(opt, success, response) {
					if (success) {
						var obj = Ext.util.JSON.decode(response.responseText);
						if (!obj.success) {
							sw.swMsg.alert(langs('Ошибка'), obj.Error_Msg ? obj.Error_Msg : 'Данный пациент состоит в другом запросе');
							return false;
						}
						if (obj.setStatus)
							g = win.TFOMSQueryGrid; // При смене статуса запроса требуется обновление обоих гридов
						g.loadData({callback: function(){win.getLoadMask().hide();}});
					}
					else {
						sw.swMsg.alert(langs('Ошибка'), langs('При добавлении пациента в запрос возникли ошибки'));
						win.getLoadMask().hide();
					}
				},
				url: '/?c=TFOMSQuery&m=setAccessPerson'
			});
		},
		setAccessAllPerson: function(recQ,checked){
			var win = this;
			if(Ext.isEmpty(recQ))
				recQ = win.getSelectedTFOMSQueryRec();
			if (!recQ) { return false; }
			win.getLoadMask(langs('Изменение доступа к ЭМК')).show();
			Ext.Ajax.request({
				params: {
					TFOMSQueryPerson_isAccess: checked?2:1,
					TFOMSQueryEMK_id: recQ.get('TFOMSQueryEMK_id'),
					TFOMSQueryStatus_id: recQ.get('TFOMSQueryStatus_id')
				},
				callback: function(opt, success, response) {
					if ( success ) {
						var obj = Ext.util.JSON.decode(response.responseText);
						if(!obj.success) {
							sw.swMsg.alert(langs('Ошибка'), obj.Error_Msg?obj.Error_Msg:'Данный пациент состоит в другом запросе');
							return false;
						}
					}
					else {
						sw.swMsg.alert(langs('Ошибка'), langs('При добавлении пациента в запрос возникли ошибки'));
					}
					win.TFOMSQueryGrid.loadData({callback: function(){win.getLoadMask().hide();}});
					//win.QueryPersonsGrid.loadData();

				},
				url: '/?c=TFOMSQuery&m=setAccessAllPerson'
			});
		},
		openEMK: function(){
			var win = this,
				qP = win.QueryPersonsGrid,
				btnOpenEMK = qP.getAction('action_view');
			if(btnOpenEMK.isDisabled())
				return false;

			var rec = this.QueryPersonsGrid.getGrid().getSelectionModel().getSelected();
			if ( typeof rec != 'object' || Ext.isEmpty(rec.get('Person_id')) || Ext.isEmpty(rec.get('TFOMSQueryPerson_id')) ) {
				sw.swMsg.alert(langs('Ошибка'), 'Необходимо выбрать пациента');
				return false;
			}
			if(!Ext.isEmpty(this.ARMType) && (['tfoms', 'smo'].includes(this.ARMType)))
				win.setViewPersonStatus(rec.get('TFOMSQueryPerson_id'));
			getWnd('swPersonEmkWindow').show({
				Person_id: rec.get('Person_id'),
				readOnly: true,
				ARMType: 'common'
			});
		},
		setViewPersonStatus: function(TFOMSQueryPerson_id) {
			if(Ext.isEmpty(TFOMSQueryPerson_id))
				return false;
			Ext.Ajax.request({
				callback: function(opt, scs, response) {
					win.QueryPersonsGrid.loadData();
				},
				params: {
					TFOMSQueryPerson_id: TFOMSQueryPerson_id
				},
				url: '/?c=TFOMSQuery&m=setViewPersonStatus'
			});
		},
		disabledBtn: function(rec){
			if(!rec) return false;
			var win = this,
				g = win.TFOMSQueryGrid,
				qP = win.QueryPersonsGrid,
				btnSend = g.getAction('action_query_send'),
				btnFinish = g.getAction('action_view_finish'),
				btnAvailable = g.getAction('action_make_available'),
				btnUnavailable = g.getAction('action_make_unavailable'),
				btnDelQuery = g.getAction('action_delete'),
				btnOpenQuery = g.getAction('action_add'),
				btnEditQuery = g.getAction('action_edit'),
				status = rec.get('TFOMSQueryStatus_id');

			switch(this.ARMType){
				case 'tfoms':
				case 'smo':
					btnSend.setDisabled(status != 1);
					btnFinish.setDisabled(status != 3 && status != 4);
					btnDelQuery.setDisabled(status != 1);
					break;
				case 'lpuadmin':
				case 'mstat':
					btnAvailable.setDisabled(!status.inlist([2,4,5]));
					btnUnavailable.setDisabled(status==1 || status==5);
					btnOpenQuery.disable();
					btnEditQuery.disable();
					btnDelQuery.disable();
					qP.setDisabledCheckBox(status==1 || status==5);
					break;
			}
		},
		chechPeriodAccess: function(recQ){
			if(!recQ) return false;
			var now = Ext.util.Format.date(new Date(), 'Y-m-d'),
				begDT = recQ.get('TFOMSQueryEMK_begDate'),
				endDT = recQ.get('TFOMSQueryEMK_endDate');
			/*var now = new Date().toISOString().split('T')[0],
				begDT = recQ.get('TFOMSQueryEMK_begDate'),
				endDT = recQ.get('TFOMSQueryEMK_endDate');*/
			if(Ext.isEmpty(begDT) && Ext.isEmpty(begDT)) return true;
			
			/*begDT = begDT.toISOString().split('T')[0];
			endDT = endDT.toISOString().split('T')[0];*/
			begDT = Ext.util.Format.date(begDT, 'Y-m-d');
			endDT = Ext.util.Format.date(endDT, 'Y-m-d');
			return (now>=begDT && now<=endDT);
		},
		disabledPersonBtn: function(recQ,recP){
			var win = this,
				qP = win.QueryPersonsGrid,
				btnAdd = qP.getAction('action_add'),
				btnDelPerson = qP.getAction('action_delete'),
				btnOpenEMK = qP.getAction('action_view');
			switch(this.ARMType){
				case 'tfoms':
				case 'smo':
					if(!recQ){
						recQ = win.getSelectedTFOMSQueryRec();
					}
					if(!recQ) return false;
					var status = recQ.get('TFOMSQueryStatus_id');
					// Проверка на вхождение в период доступа
					var checkDateFlag = win.chechPeriodAccess(recQ); // true если входит в период
					var checkAccessFlag = false;
					if(recP && recP.get('MultiSelectValue')){
						checkAccessFlag = true;
					}
					btnAdd.setDisabled(status!=1);
					btnDelPerson.setDisabled(status!=1);
					// Для статусов "Открыт доступ" и "Частично открыт доступ" кнопка доступна, для остальных блокируем
					// Если текущая дата не входит в период доступа, также блокируем кнопку
					// Если специалист МО не проставил признак доступа, блокируем открытие ЭМК
					btnOpenEMK.setDisabled((!(status==3 || status==4)) || !checkDateFlag || !checkAccessFlag);
					break;
				case 'lpuadmin':
				case 'mstat':
					btnAdd.disable();
					btnDelPerson.disable();
					btnOpenEMK.enable();
					break;
			}
		},
		initComponent: function()
		{
			var win = this;
			this.dateMenu = new Ext.form.DateRangeField({
				name: 'Newslatter_insDT',
				fieldLabel: langs('Дата формирования'),
				plugins: [new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
				width: 150
			});
			this.formActions = new Array();
			this.formActions.selectDate = new Ext.Action(
				{
					text: ''
				});
			this.formActions.prev = new Ext.Action(
				{
					text: langs('Предыдущий'),
					xtype: 'button',
					iconCls: 'arrow-previous16',
					handler: function()
					{
						// на один день назад
						this.prevDay();
						this.scheduleLoad('range');
					}.createDelegate(this)
				});
			this.formActions.next = new Ext.Action(
				{
					text: langs('Следующий'),
					xtype: 'button',
					iconCls: 'arrow-next16',
					handler: function()
					{
						// на один день вперед
						this.nextDay();
						this.scheduleLoad('range');
					}.createDelegate(this)
				});
			this.DoctorToolbar = new Ext.Toolbar(
				{
					items:
						[
							this.formActions.prev,
							{
								xtype : "tbseparator"
							},
							this.dateMenu,
							{
								xtype : "tbseparator"
							},
							this.formActions.next,
							{
								xtype: 'tbfill'
							}
						]
				});
			this.comboMO = new sw.Promed.SwLpuCombo({fieldLabel: 'МО',disabled: true});
			this.comboTFOMS = new sw.Promed.SwOrgSMOCombo({
				disabled: true,
				valueField: 'Org_id',
				hiddenName: 'Org_id',
				fieldLabel: 'ТФОМС/СМО'
			});
			this.filtersPanel = new Ext.FormPanel({
				region: 'north',
				labelAlign: 'right',
				layout: 'form',
				height: 300,
				autoHeight: true,
				labelWidth: 90,
				frame: true,
				border: false,
				keys: [{
					key: Ext.EventObject.ENTER,
					fn: function(e)
					{
						win.doFilter();
					},
					stopEvent: true
				}],
				items: [
					{
						xtype: 'fieldset',
						autoHeight: true,
						collapsible: true,
						listeners: {
							collapse: function(p) {
								win.doLayout();
							},
							expand: function(p) {
								win.doLayout();
							}
						},
						title: langs('Фильтр'),
						items: [
							{
								layout: 'column',
								border: false,
								items: [
									{
										layout: 'form',
										items: [this.comboTFOMS]
									},
									{
										layout: 'form',
										labelWidth: 70,
										items: [this.comboMO]
									},
									{
										layout: 'form',
										labelWidth: 125,
										items: [{
											xtype: 'swtfomsquerystatus',
											fieldLabel: 'Статус запроса'
										}]
									},
									{
										width: 200,
										style: {
											marginLeft: '10px'
										},
										xtype: 'button',
										text: BTN_FIND,
										tabIndex: TABINDEX_RRLW + 10,
										handler: function () {
											win.doFilter();
										},
										iconCls: 'search16'
									},
									{
										width: 200,
										style: {
											marginLeft: '10px'
										},
										xtype: 'button',
										text: BTN_RESETFILTER,
										tabIndex: TABINDEX_RRLW + 11,
										handler: function () {
											win.doResetFilters();
											win.doFilter();
										},
										iconCls: 'resetsearch16'
									}
								]
							}
						]
					}
				],
				bbar: this.DoctorToolbar
			});

			this.TFOMSQueryGrid = new sw.Promed.ViewFrame({
				minWidth: 700,
				width: '50%',
				region: 'center',
				layout: 'border',
				id: win.id+'TFOMSQueryGrid',
				title:'',
				object: 'TFOMSQuery',
				dataUrl: '/?c=TFOMSQuery&m=loadTFOMSQueryList',
				autoLoadData: false,
				root: 'data',
				totalProperty: 'totalCount',
				split: true,
				toolbar: true,
				paging: true,
				useEmptyRecord: false,
				notApplyStringFields: true,
				//noSelectFirstRowOnFocus: true,
				stringfields: [
					{name: 'Lpu_id', type: 'int', hidden: true},
					{name: 'Org_id', type: 'int', hidden: true},
					{name: 'TFOMSQueryEMK_begDate', hidden: true, type: 'date'},
					{name: 'TFOMSQueryEMK_endDate', hidden: true, type: 'date'},
					{name: 'TFOMSQueryEMK_id', type: 'int', header: 'ID', key: true, hidden: true},
					{name: 'TFOMSQueryEMK_insDT', header: langs('Дата формирования'), width: 100},
					{name: 'Lpu_Nick', header: langs('МО'), width: 150},
					{name: 'Org_Nick', header: langs('ТФОМС/СМО'), width: 150},
					{name: 'TFOMSQueryStatus_id', type: 'int', hidden: true},
					{name: 'TFOMSQueryStatus_Name', header: langs('Статус запроса'), width: 150},
					{name: 'TFOMSQueryEMK_Date', header: langs('Период доступа'), width: 200}
				],
				actions: [
					{name:'action_add', handler: function(){win.openTFOMSQueryEditWindow('add');}},
					{name:'action_edit', text: 'Открыть', handler: function(){win.openTFOMSQueryEditWindow('edit');}},
					{name:'action_view', disabled: true, hidden: true},
					{name:'action_delete', handler: function(){win.deleteObject('TFOMSQueryEMK_id');}},
					{name:'action_print', disabled: true, hidden: true}
				],
				onRowSelect: function(sm,index,rec) {
					win.disabledBtn(rec);
					win.disabledPersonBtn(rec);
					win.QueryPersonsGrid.removeAll({ clearAll: true });
					if (rec && rec.get('TFOMSQueryEMK_id')) {
						win.reloadQueryPersonsGrid(rec.get('TFOMSQueryEMK_id'));
					}
					win.QueryPersonsGrid.selectedQuery = rec;

				}
			});

			this.QueryPersonsGrid = new sw.Promed.ViewFrame({
				split: true,
				width: '50%',
				region: 'east',
				layout: 'border',
				id: win.id+'QueryPersonsGrid',
				selectionModel: 'multiselect2',
				title: langs('Список пациентов'),
				object: 'TFOMSQueryPerson',
				dataUrl: '/?c=TFOMSQuery&m=loadTFOMSQueryPersonList',
				autoLoadData: false,
				toolbar: true,
				root: 'data',
				useEmptyRecord: false,
				paging: true,
				//noSelectFirstRowOnFocus: true, // Выбираем и фокусимся, зачем нам эти ограничения
				notApplyStringFields: true,
				selectedQuery: false,
				stringfields: [
					{name: 'TFOMSQueryPerson_id', type: 'int', header: 'ID', key: true, hidden: true},
					{name: 'TFOMSQueryEMK_id', type: 'int', hidden: true},
					{name: 'Person_id', type: 'int', hidden: true},
					{name: 'Person_Fio', header: langs('ФИО'), width: 400},
					{name: 'Person_Birthday', header: langs('Дата рождения'), width: 200},
					{name: 'TFOMSQueryPerson_IsView', header: langs('Просмотрено'), type: 'checkbox', width: 200},
					{name: 'TFOMSQueryPerson_IsAccess', hidden: true}
				],
				//Задание одиночного значения
				onSetMultiSelectValue: function(rec,checked){
					win.setAccessPerson(rec,checked);
				},
				//Задание значения всем записям
				onSetAllMultiSelectValue: function(checked){
					win.setAccessAllPerson(this.selectedQuery,checked);
				},
				actions: [
					{name:'action_add', disabled: true, handler: function() { win.scheduleNew(); }},
					{name:'action_delete', disabled: true, handler: function() { win.deleteObject('TFOMSQueryPerson_id'); }},
					{name:'action_edit', disabled: true, hidden: true},
					{name:'action_view', disabled: true, text: 'Просмотр ЭМК', handler: function() { win.openEMK(); }},
					{name:'action_print', disabled: true, hidden: true}
				],
				onRowSelect: function(sm,index,rec) {
					win.disabledPersonBtn(this.selectedQuery,rec);
				}
			});

			this.formPanel = new Ext.Panel({
				region: 'center',
				labelAlign: 'right',
				layout: 'border',
				labelWidth: 50,
				border: false,
				items: [
					this.filtersPanel,
					this.TFOMSQueryGrid,
					this.QueryPersonsGrid
				]
			});

			Ext.apply(this, {
				items: [
					win.formPanel
				],
				buttons: [{hidden: true},{hidden: true},{
					text: '-'
				},
					HelpButton(this, TABINDEX_RRLW + 13),
					{
						iconCls: 'close16',
						tabIndex: TABINDEX_RRLW + 14,
						handler: function() {
							win.hide();
						},
						text: BTN_FRMCLOSE
					}]
			});

			sw.Promed.swTFOMSQueryWindow.superclass.initComponent.apply(this, arguments);
		},
		show: function() {
			this.ARMType = arguments[0].ARMType || null;
			var win = this;
			sw.Promed.swTFOMSQueryWindow.superclass.show.apply(this, arguments);

			this.TFOMSQueryGrid.addActions({
				hidden: !(['lpuadmin', 'mstat'].includes(this.ARMType)),
				name:'action_make_unavailable',
				iconCls: 'delete16',
				text: langs('Закрыть доступ'),
				handler: function() { win.closeAccess(); }.createDelegate(this)
			});
			this.TFOMSQueryGrid.addActions({
				hidden: !(['lpuadmin', 'mstat'].includes(this.ARMType)),
				name:'action_setDateAccess',
				text: langs('Указать период доступа'),
				handler: function() { win.openTFOMSQueryEditWindow('setAccessDate'); }.createDelegate(this)
			});
			this.TFOMSQueryGrid.addActions({
				hidden: !(['lpuadmin', 'mstat'].includes(this.ARMType)),
				name:'action_make_available',
				text: langs('Открыть доступ'),
				handler: function() { win.openAccess(); }.createDelegate(this)
			});
			this.TFOMSQueryGrid.addActions({
				hidden: !(['tfoms', 'smo'].includes(this.ARMType)),
				name:'action_view_finish',
				iconCls: 'delete16',
				text: langs('Завершить просмотр'),
				handler: function() { win.endView(); }.createDelegate(this)
			});
			this.TFOMSQueryGrid.addActions({
				hidden: !(['tfoms', 'smo'].includes(this.ARMType)),
				name:'action_query_send',
				text: langs('Отправить запрос'),
				handler: function() { win.sendQuery(); }.createDelegate(this)
			});

			win.doResetFilters();
			win.QueryPersonsGrid.setDisabledCheckBox(['tfoms', 'smo'].includes(this.ARMType));
			var date = Date.parseDate(getGlobalOptions().date, 'd.m.Y');
			win.dateMenu.setValue(Ext.util.Format.date(date.add('d',-30), 'd.m.Y')+' - '+Ext.util.Format.date(date));
			win.doFilter();
		}
	});