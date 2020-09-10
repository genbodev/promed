/**
 * swHeadNurseWorkPlaceWindow - окно рабочего места старшей медсестры
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Common
 * @access			public
 * @copyright		Copyright (c) 2013 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			15.11.2013
 */

/*NO PARSE JSON*/

sw.Promed.swHeadNurseWorkPlaceWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swHeadNurseWorkPlaceWindow',
	objectSrc: '/jscore/Forms/Common/swHeadNurseWorkPlaceWindow.js',
	closable: true,
	closeAction: 'hide',
	maximized: true,
	title: lang['rabochee_mesto_starshey_medsestryi'],
	iconCls: 'workplace-mp16',
	id: 'swHeadNurseWorkPlaceWindow',
	readOnly: false,
	//тип АРМа, определяется к каким функциям будет иметь доступ врач через ЭМК
	ARMType: null,
	curDate: null,
	curTime: null,
	userName: null,
	//объект с параметрами рабочего места, с которыми была открыта форма
	userMedStaffFact: null,
	firstLoad: true,
	//вариант отображения периода (день, неделя, месяц) по умолчанию
	mode: 'day',
	// Хранение идентификатора контрагента отделения
	Contragent_id: null,
	// Хранение данных по лпу
	LpuSectionData: null,
	// Хранение параметров для формирования отчетов
	ReportParams: null,
	// создание различного вида меню
	setMenu: function() {
		var menu;
		menu = sw.Promed.MedPersonal.getMenu({
			LpuSection_id: this.findById('HNWPW_Search_LpuSection_id').getValue(),
			id: 'ListMenuMedPersonal',
			getParams: function(){
				var params = {};
				var node = this.Tree.getSelectionModel().selNode;
				if (node && node.attributes && node.attributes.EvnSection_id) {
					params.LpuSection_id = this.findById('HNWPW_Search_LpuSection_id').getValue();
					params.EvnSection_id = node.attributes.EvnSection_id;
					params.EvnSection_pid = null;
					params.Person_id = node.attributes.Person_id;
					params.PersonEvn_id = node.attributes.PersonEvn_id;
					params.Server_id = node.attributes.Server_id;
					params.MedPersonalCur_id = node.attributes.MedPersonal_id;
				}
				return params;
			}.createDelegate(this),
			onSuccess: function(){
				this.reloadTree();
			}.createDelegate(this)
		});
		this.getTreeAction('update_doctor').each(function(cmp){
			cmp.menu = menu;
		}, this);
		this.createListLeaveType();
	},

	getTreeAction: function(name) {
		if( this.treeActions[name] ) {
			return this.treeActions[name];
		}
		var actions = this.treeActions['actions'].menu;
		return actions[name] || null;
	},

	setContragentId: function(lpu_section_id,callback) {
		var wnd = this;

		Ext.Ajax.request({
			failure: function() {
				wnd.Contragent_id = null;
                if (!Ext.isEmpty(getGlobalOptions().Contragent_id)) {
                    wnd.Contragent_id = getGlobalOptions().Contragent_id;
                }
                if (Ext.isEmpty(wnd.Contragent_id)) {
                    wnd.PanelActions.action_Medication.disable();
                }
				sw.swMsg.alert(lang['oshibka'], lang['ne_udalos_poluchit_dannyie_kontragenta_po_otdeleniyu_lpu']);
			},
			params: {
				LpuSection_id: lpu_section_id
			},
			success: function(response) {
				var response_obj = Ext.util.JSON.decode(response.responseText);
                wnd.Contragent_id = null;
				if (response_obj.data) {
					wnd.Contragent_id = response_obj.data.Contragent_id;
				} else if (!Ext.isEmpty(getGlobalOptions().Contragent_id)) {
                    wnd.Contragent_id = getGlobalOptions().Contragent_id;
                }
                if (Ext.isEmpty(wnd.Contragent_id)) {
                    wnd.PanelActions.action_Medication.disable();
                }
				if (callback) {
					callback();
				}
			},
			url: '/?c=Farmacy&m=getLpuSectionContragent'
		});
	},
	printCmp_f009u: function() {
		getWnd('swTransfusionMediaJournalForm').show();
	},
	setLpuSectionData: function(lpu_section_id,callback) {
		var wnd = this;

		Ext.Ajax.request({
			failure: function() {
				wnd.PanelActions.action_Reports.disable();
				sw.swMsg.alert(lang['oshibka'], lang['ne_udalos_poluchit_dannyie_po_lpu']);
			},
			params: {
				LpuSection_id: lpu_section_id
			},
			success: function(response) {
				var response_obj = Ext.util.JSON.decode(response.responseText);
				if (response_obj.data) {
					wnd.LpuSectionData = response_obj.data;
				} else {
					wnd.PanelActions.action_Reports.disable();
				}
				if (callback) {
					callback();
				}
			},
			url: '/?c=LpuStructure&m=getLpuSectionData'
		});
	},

	openDocumentUcAddWindow: function(type_code) {
		var params = new Object();
		var edit_window_name = 'swNewDocumentUcEditWindow';

		switch(type_code) {
			case 2: //2 - Документ списания медикаментов
				params.DrugDocumentType_id = 2;
				params.FormParams = {
					Contragent_sid: getGlobalOptions().Contragent_id
				};
				break
			case 3: //3 - Документ ввода остатков
				params.DrugDocumentType_id = 3;
				params.FormParams = {
					Contragent_tid: getGlobalOptions().Contragent_id
				};
				break
			case 6: //6 - Приходная накладная
				params.DrugDocumentType_id = 6;
				params.FormParams = {
					Contragent_tid: getGlobalOptions().Contragent_id
				};
				params.isSmpMainStorage = false;
				break
			case 15: //15 - Накладная на внутреннее перемещение
				params.DrugDocumentType_id = 15;
				params.FormParams = {
					Contragent_sid: getGlobalOptions().Contragent_id
				};
				params.isSmpMainStorage = false;
				break
		}

		if (!Ext.isEmpty(params.DrugDocumentType_id)) {
			params.DrugDocumentType_Code = type_code;
			params.callback = function() { this.hide(); };
			params.action = 'add';
			params.userMedStaffFact = this.userMedStaffFact;

			getWnd(edit_window_name).show(params);
		}
	},

	show: function()
	{
		sw.Promed.swHeadNurseWorkPlaceWindow.superclass.show.apply(this, arguments);
		// Проверяем права пользователя открывшего форму

		if ((!arguments[0]) || (!arguments[0].userMedStaffFact) || (!arguments[0].userMedStaffFact.ARMType))
		{
			this.hide();
			Ext.Msg.alert('Ошибка открытия формы', 'Ошибка открытия формы "'+this.title+'".<br/>Не указан тип АРМа врача.');
			return false;
		} else {
			this.ARMType = arguments[0].userMedStaffFact.ARMType;
			this.userMedStaffFact = arguments[0].userMedStaffFact;
		}
		var lpusection_combo = Ext.getCmp('HNWPW_Search_LpuSection_id');
		if ( lpusection_combo.getStore().getCount() == 0 ) {
			setLpuSectionGlobalStoreFilter({
				allowLowLevel: 'yes',
				isStac: true
			});
			lpusection_combo.getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));
		}
		lpusection_combo.setValue(this.userMedStaffFact.LpuSection_id);
		lpusection_combo.fireEvent('change', lpusection_combo, this.userMedStaffFact.LpuSection_id, null);
		lpusection_combo.disable();

		var medstafffact_combo = this.findById('HNWPW_Search_MedStaffFact_id');
		medstafffact_combo.getStore().removeAll();
		setMedStaffFactGlobalStoreFilter({
			allowLowLevel: 'yes',
			isStac: true,
			LpuSection_id: this.userMedStaffFact.LpuSection_id
		});
		medstafffact_combo.getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));
		//medstafffact_combo.setValue(this.userMedStaffFact.MedStaffFact_id);

		this.setContragentId(this.userMedStaffFact.LpuSection_id);
		this.setLpuSectionData(this.userMedStaffFact.LpuSection_id,function(){
			if (this.LpuSectionData){
				this.ReportParams = {
					paramLpu: this.LpuSectionData.Lpu_id,
					paramLpuBuilding: this.LpuSectionData.LpuBuilding_id,
					paramLpuUnitStacD: this.LpuSectionData.LpuUnit_id,
					paramLpuSection: this.LpuSectionData.LpuSection_id
				};
			}
		}.createDelegate(this));

		// На случай работы этой формы в разных режимах.
		if ( true )
		{
			// Создаем свой заголовок, единый для всех армов, на основании данных пришедших с сервера ( из User_model)
			sw.Promed.MedStaffFactByUser.setMenuTitle(this, this.userMedStaffFact);
			//this.setTitle(WND_WPMP + '. Отделение: ' + this.userMedStaffFact.LpuSection_Name + '. Врач: ' + this.userMedStaffFact.MedPersonal_FIO);
			this.TopPanel.show();

		}
		this.firstLoad = true;
		// Меню для кнопок
		this.setMenu();
		// Переключатель
		this.syncSize();
		this.getCurrentDateTime();
		// Кол-во коек
		//this.loadNumberBeds();

	}, // end show()

	addPatient: function()
	{
		if (getWnd('swPersonSearchWindow').isVisible()) {
			Ext.Msg.alert(lang['soobschenie'], lang['okno_poiska_cheloveka_uje_otkryito']);
			return false;
		}
		sw.Applets.uec.stopUecReader();
		getWnd('swPersonSearchWindow').show({
			onSelect: function(pdata) {
				getWnd('swPersonSearchWindow').hide();
				//нужно проверить сущестование открытых КВС на этого пациента
				Ext.Ajax.request({
					url: '/?c=EvnPS&m=beforeOpenEmk',
					params: {Person_id: pdata.Person_id},
					failure: function() {
						showSysMsg(lang['pri_poluchenii_dannyih_dlya_proverok_proizoshla_oshibka']);
					},
					success: function(response)
					{
						if (response.responseText) {
							var answer = Ext.util.JSON.decode(response.responseText);
							if(!Ext.isArray(answer) || !answer[0])
							{
								showSysMsg(lang['pri_poluchenii_dannyih_dlya_proverok_proizoshla_oshibka_nepravilnyiy_otvet_servera']);
								return false;
							}
							if (answer[0].countOpenEvnPS > 0)
							{
								showSysMsg(lang['sozdanie_novyih_kvs_nedostupno'],lang['u_patsienta_imeyutsya_otkryityie_kvs_v_dannnom_lpu_kolichestvo_otkryityih_kvs']+ answer[0].countOpenEvnPS);
								return false;
							}
							getWnd('swEvnPSEditWindow').show({
								action: 'add',
								Person_id: pdata.Person_id,
								PersonEvn_id: pdata.PersonEvn_id,
								Server_id: pdata.Server_id,
								LpuSection_id: this.userMedStaffFact.LpuSection_id,
								MedPersonal_id: this.userMedStaffFact.MedPersonal_id,
								onHide: function() {
									this.reloadTree();
									this.emptyevndoc();
								}.createDelegate(this)
								//чтобы создавалось движение при заполнении LpuSection_id
								,form_mode: 'arm_stac_add_patient'
								,EvnPS_setDate: getGlobalOptions().date
							});
							return true;
						}
						else {
							showSysMsg(lang['pri_poluchenii_dannyih_dlya_proverok_proizoshla_oshibka_otsutstvuet_otvet_servera']);
							return false;
						}
					}.createDelegate(this)
				});
			}.createDelegate(this),
			needUecIdentification: true,
			searchMode: 'all'
		});
	},

	listeners:
	{
		hide: function()
		{
			this.emptyevndoc();
			this.findById('HNWPW_Search_F').setValue(null);
			this.findById('HNWPW_Search_I').setValue(null);
			this.findById('HNWPW_Search_O').setValue(null);
			this.findById('HNWPW_PSNumCard').setValue(null);
			this.findById('HNWPW_Search_BirthDay').setValue(null);
			this.findById('HNWPW_Search_MedStaffFact_id').setValue(null);
		}
	},

	menuLpuSectionWard: null,
	/** Создание меню палат
	 */
	createListLpuSectionWard: function() {
		sw.Promed.LpuSectionWard.createListLpuSectionWard({
			LpuSection_id: this.findById('HNWPW_Search_LpuSection_id').getValue(),
			date: Ext.util.Format.date(Ext.getCmp('HNWPW_Search_date').getValue(), 'd.m.Y'),
			id: 'ListMenuWard',
			getParams: function(){
				var params = {};
				var node = this.Tree.getSelectionModel().selNode;
				if (node && node.attributes && node.attributes.EvnSection_id) {
					params.LpuSection_id = this.findById('HNWPW_Search_LpuSection_id').getValue();
					params.EvnSection_id = node.attributes.EvnSection_id;
					params.ignore_sex = false;
					params.Sex_id = node.attributes.Sex_id;
					params.Person_id = node.attributes.Person_id;
					params.LpuSectionWardCur_id = node.attributes.LpuSectionWard_id;
				}
				return params;
			}.createDelegate(this),
			callback: function(menu){
				this.menuLpuSectionWard = menu;
				this.getTreeAction('update_ward').each(function(cmp){
					cmp.menu = menu;
				}, this);
			}.createDelegate(this),
			onSuccess: function(params){
				this.reloadTree({
					beforeRestorePosition: function(){
						if(params && Ext.isArray(this.position) && this.position.length == 4) {
							//правим id ноды с палатой
							this.position[1] = (params.LpuSectionWard_id > 0)?('LpuSectionWard_id_'+params.LpuSectionWard_id):'noward';
						}
					}.createDelegate(this)
				});
				//также обновляем меню палат LpuSectionWard_id_10
				this.createListLpuSectionWard();
			}.createDelegate(this)
		});
	},

	/** Создание меню исходов госпитализации
	*/
	createListLeaveType: function() {
		var win = this;
		var menu = sw.Promed.Leave.getMenu({
			ownerWindow: win,
			id: 'ListLeaveTypeMenu',
			getParams: function(){
				var node = win.Tree.getSelectionModel().selNode;
				var age = swGetPersonAge(Date.parseDate(node.attributes.Person_BirthDay, 'd.m.Y'), Date.parseDate(node.attributes.EvnSection_setDate, 'd.m.Y'));
				return {
					Person_id: node.attributes.Person_id,
					PersonEvn_id: node.attributes.PersonEvn_id,
					Server_id: node.attributes.Server_id,
					EvnPS_id: node.attributes.EvnPS_id,
					EvnSection_disDT: Date.parseDate((win.curDate+' '+win.curTime.substr(0,5)), 'd.m.Y H:i'),
					EvnSection_id: node.attributes.EvnSection_id,
					childPS: age === 0
				};
			},
			onHideEditWindow: Ext.emptyFn,
			callbackEditWindow: function(){
				win.reloadTree();
			},
			onCreate: function(m){
				win.treeActions['actions'].menu.leave.items[0].menu = m;
				win.treeActions['actions'].menu.leave.items[1].menu = m;
			}
		});
	},
	printLpuSectionPacients: function()
	{
		this.getLoadMask(LOAD_WAIT).show();
		var params = {
			Lpu_id: getGlobalOptions().lpu_id,
			LpuSection_id: this.findById('HNWPW_Search_LpuSection_id').getValue(),
			date: Ext.getCmp('HNWPW_Search_date').getValue()
		};

		Ext.Ajax.request({
			url: '/?c=EvnSection&m=printPatientList',
			params: params,
			callback: function(options, success, response)
			{
				Ext.getCmp('swHeadNurseWorkPlaceWindow').getLoadMask(LOAD_WAIT).hide();
				if(success)
				{
					if(response.responseText != '')
					{
						openNewWindow(response.responseText);
					}
					else
					{
						sw.swMsg.alert(lang['soobschenie'], lang['net_ni_odnogo_patsienta']);
					}
				}
			}
		});
	},

	printAddressLeaf: function(leaf_type) {
		var node = this.Tree.getSelectionModel().selNode;

		if (!leaf_type || !leaf_type.inlist(['arrival','departure']) ) {
			return false;
		}

		var Person_id = node.attributes.Person_id;
		if ( Ext.isEmpty(Person_id) ) {
			sw.swMsg.alert(lang['oshibka'], lang['ne_ukazan_patsient']);
			return false;
		}

		var Lpu_id = getGlobalOptions().lpu_id;
		if ( Ext.isEmpty(Lpu_id) ) {
			sw.swMsg.alert(lang['oshibka'], lang['ne_ukazano_lpu']);
			return false;
		}

		var tpl = '';
		if (leaf_type == 'arrival') {
			tpl = 'LeafArrival.rptdesign'
		} else
		if (leaf_type == 'departure') {
			tpl = 'LeafDeparture.rptdesign'
		}

		printBirt({
			'Report_FileName': tpl,
			'Report_Params': '&paramPerson_id='+Person_id+'&paramLpu='+Lpu_id,
			'Report_Format': 'pdf'
		});
	},

	printCmp_f005u: function() {
		var node = this.Tree.getSelectionModel().selNode;

		var EvnPS_id = node.attributes.EvnPS_id;
		if ( Ext.isEmpty(EvnPS_id) ) {
			return false;
		}

		printBirt({
			'Report_FileName': 'f005u.rptdesign',
			'Report_Params': '&paramEvnPs='+EvnPS_id,
			'Report_Format': 'pdf'
		});
	},

	//Для очистки правой панели от интерактивного документа
	emptyevndoc: function()
	{
		var tp = [];
		this.RightPanel.tpl = new Ext.Template(tp);
		this.RightPanel.tpl.overwrite(this.RightPanel.body, tp);
	},
	//Метод, вызывающий форму редактирования КВС
	openEvnPSEditWindow: function()
	{
		this.emptyevndoc();
		var node = this.Tree.getSelectionModel().selNode;

		if (getWnd('swEvnPSEditWindow').isVisible())
		{
			sw.swMsg.alert(lang['soobschenie'], lang['okno_redaktirovaniya_kartyi_vyibyivshego_iz_statsionara_uje_otkryito']);
			return false;
		}

		var params = {
			EvnPS_id: node.attributes.EvnPS_id,
			Person_id: node.attributes.Person_id,
			Server_id: node.attributes.Server_id,
			onChangeLpuSectionWard: function(params){
				this.reloadTree({
					beforeRestorePosition: function(){
						if(params && Ext.isArray(this.position) && this.position.length == 4) {
							//правим id ноды с палатой
							this.position[1] = (params.LpuSectionWard_id > 0)?('LpuSectionWard_id_'+params.LpuSectionWard_id):'noward';
						}
					}.createDelegate(this)
				});
			}.createDelegate(this),
			onHide: function() {
				this.reloadTree();
				//также обновляем меню палат
				this.createListLpuSectionWard();
			}.createDelegate(this),
			action: 'edit'
		};
		getWnd('swEvnPSEditWindow').show(params);
	},

	printEvnDoc: function()
	{
		var data = this.findById('RightPanel').body.dom.innerHTML;
		if(data != '')
		{
			openNewWindow(data);
		}
	},
	printEvnStick: function(EvnStick_id) {
		window.open('/?c=Stick&m=printEvnStick&evnStickType=1&EvnStick_id='+EvnStick_id, '_blank');
	},
	getCurrentDateTime: function()
	{
		var frm = this;
		frm.getLoadMask(LOAD_WAIT).show();
		Ext.Ajax.request(
			{
				url: C_LOAD_CURTIME,
				callback: function(opt, success, response)
				{
					if (success && response.responseText != '')
					{
						var result  = Ext.util.JSON.decode(response.responseText);
						frm.curDate = result.begDate;
						frm.curTime = result.begTime;
						frm.userName = result.pmUser_Name;
						frm.userName = result.pmUser_Name;
						// Проставляем время
						frm.currentDay();
						frm.Tree.getRootNode().select();
						frm.getLoadMask().hide();
						frm.loadTree();
					}
				}
			});
	},
	stepDay: function(day)
	{
		var datefield = Ext.getCmp('HNWPW_Search_date');
		var date = (datefield.getValue() || Date.parseDate(this.curDate, 'd.m.Y')).add(Date.DAY, day).clearTime();
		datefield.setValue(Ext.util.Format.date(date, 'd.m.Y'));
		datefield.fireEvent('change', datefield, date, null);
	},
	prevDay: function ()
	{
		this.stepDay(-1);
	},
	nextDay: function ()
	{
		this.stepDay(1);
	},
	currentDay: function ()
	{
		var date = Date.parseDate(this.curDate, 'd.m.Y');
		var datefield = Ext.getCmp('HNWPW_Search_date');
		datefield.setValue(Ext.util.Format.date(date, 'd.m.Y'));
		datefield.fireEvent('change', datefield, date, null);
	},
	loadTree: function (period_mode)
	{
		var tree_loader = this.Tree.getLoader();
		if (period_mode != undefined)
		{
			//tree_loader.baseParams.= ;
		}
		var node = this.Tree.getSelectionModel().selNode;
		if (node)
		{
			if (node.parentNode)
			{
				node = node.parentNode;
			}
		}
		else
			node = this.Tree.getRootNode();
		if (node)
		{
			node.collapse();
			tree_loader.load(node);
			node.expand();
		}
	},
	reloadTree: function(option) {
		if(typeof option != 'object') {
			option = {};
		}
		this.savePosition(option);

		var root = this.Tree.getRootNode();
		this.Tree.getLoader().load(root,function(tl,n){
			this.restorePosition(option);
		}.createDelegate(this));
		root.expand();
	},
	/** Сохраняет состояние дерева перед перезагрузкой:
	 *  идeнтификатор выделенной ноды, идентификаторы раскрытых нод
	 */
	savePosition: function(option)
	{
		this.position = null;
		var savePathToSelNode = function (node){
			if (node)
			{
				if( !Ext.isArray(this.position) )
					this.position = [];
				this.position.push(node.attributes.id);
				savePathToSelNode(node.parentNode);
			}
		}.createDelegate(this);

		savePathToSelNode(this.Tree.getSelectionModel().selNode);

		var saveExpandedNodes = function (node){
			var expanded_nodes = [];
			if (node && node.childNodes)
			{
				var childNodes = node.childNodes;
				for(var i=0;i<childNodes.length;i++){
					if (childNodes[i].isExpanded()) {
						expanded_nodes.push({
							id: childNodes[i].attributes.id
							,child: saveExpandedNodes(childNodes[i])
						});
					}
				}
			}
			return expanded_nodes;
		}.createDelegate(this);

		this.expandedNodes = saveExpandedNodes(this.Tree.getRootNode());

		// log(['savePosition',this.position, this.expandedNodes]);
	},
	restorePosition: function(option)
	{
		var selNode = function (node){
			this.Tree.getSelectionModel().select(node);
			this.Tree.fireEvent('select', node);
			this.Tree.fireEvent('click', node);
		}.createDelegate(this);

		var node;
		if(typeof option.beforeRestorePosition == 'function') {
			option.beforeRestorePosition();
		}
		if (Ext.isArray(this.position))
		{
			node = this.Tree.getNodeById(this.position[0]);
			if (node)
			{
				selNode(node);
			}
			else
			{ // была выделена койка
				var restorePositionToSelNode = function (parent_node, path, i){
					var node = parent_node.findChild('id', path[i]);
					//log(['restorePositionToSelNode',node,parent_node, path, i]);
					if (node)
					{
						if(i == 3)
						{
							selNode(node);
						}
						else if( !node.isExpanded() )
						{
							node.expand(false,false,function(n){
								restorePositionToSelNode(n,path,(i+1));
							});
						}
						else
						{
							restorePositionToSelNode(node,path,(i+1));
						}
					}
				};
				this.position.reverse();
				// 0 root
				// 1 группа
				// 2 палата
				// 3 койка
				if(this.position[1] && this.position[2] && this.position[3])
				{
					restorePositionToSelNode(this.Tree.getRootNode(),this.position,1);
				}
			}
		}

		var restoreExpandedNodes = function (parent_node, list){
			var enode;
			if( Ext.isArray(list) ) {
				for(var i=0;i<list.length;i++){
					var data = list[i];
					enode = parent_node.findChild('id', data.id);
					if( enode && !enode.isExpanded() ) {
						enode.expand(false,false,function(n){
							restoreExpandedNodes(n,data.child);
						});
					}
				}
			}
		};
		restoreExpandedNodes(this.Tree.getRootNode(),this.expandedNodes);
	},
	/**
	 * Обработчик клика по меню группировки
	 * @param group_by string "по палатам|по режимам"
	 */
	loadGrouped: function(group_by, title){
		if(!('string' === typeof(group_by) && group_by.inlist(['po_palatam', 'po_rejimam', 'po_statusam']))) return;

		this.GroupByButton.setText('Группировать: ' + title);

		var actionGroupBy = this.treeActions['group_by'];
		if(actionGroupBy){
			for(var elname in actionGroupBy.menu){
				actionGroupBy.menu[elname].setIconClass('');
			}
			actionGroupBy.menu[group_by].setIconClass('grouping_by');
		}

		this.emptyevndoc();// закрыть открытый документ

		var root = this.Tree.getRootNode();
		var loader = this.Tree.getLoader();

		loader.baseParams.group_by = group_by;// #181814 группировать: по палатам, по режимам
		this.firstLoad = true;
		loader.baseParams.level = 0;// перезагрузка всего
		//loader.baseParams.object_value = -3;
		loader.load(root);
		root.expand();
		//this.reloadTree();
	},

	openNodeSearch: function()
	{
		var lm = this.getLoadMask(LOAD_WAIT);
		lm.show();
		var datefield = this.dateMenu;
		var params =
		{
			LpuSection_id: this.findById('HNWPW_Search_LpuSection_id').getValue(),
			level: 1,
			object_value: -3,
			date: Ext.util.Format.date(datefield.getValue(), 'd.m.Y'),
			filter_Person_F: this.findById('HNWPW_Search_F').getValue(),
			filter_Person_I: this.findById('HNWPW_Search_I').getValue(),
			filter_Person_O: this.findById('HNWPW_Search_O').getValue(),
			filter_PSNumCard: this.findById('HNWPW_PSNumCard').getValue(),
			filter_Person_BirthDay: Ext.util.Format.date(this.findById('HNWPW_Search_BirthDay').getValue(), 'd.m.Y'),
			filter_MedStaffFact_id: this.findById('HNWPW_Search_MedStaffFact_id').getValue()
		};
		Ext.Ajax.request({
			url: '/?c=EvnSection&m=getSectionTreeData',
			params: params,
			callback: function(options, success, response)
			{
				lm.hide();
				obj = Ext.util.JSON.decode(response.responseText);
				if(obj[0])
				{
					var node;
					if(obj[0].LpuSectionWard_id != 0)
					{
						node = Ext.getCmp('HNWPW_Tree').getNodeById('LpuSectionWard_id_'+obj[0].LpuSectionWard_id);
					}
					else
					{
						node = Ext.getCmp('HNWPW_Tree').getNodeById('-1');
					}

					if ( node ) {
						node.SearchSign = true;
						node.expand();
					}
				}
				else
				{
					sw.swMsg.alert(lang['soobschenie'], lang['ne_naydeno_ni_odnogo_patsienta']);
				}
			}
		});
	},
	/**
	 *
	 * @return {Boolean}
	 */
	acceptFromOtherSection: function()
	{
		var form = this;
		var node = this.Tree.getSelectionModel().selNode;
		if (!node)
		{
			sw.swMsg.alert(lang['vnimanie'], lang['vyi_ne_vyibrali_element_koechnoy_strukturyi_otdeleniya']);
			return false;
		}

		var request = function (params) {
			// создаём движение
			Ext.Ajax.request({
				url: '/?c=EvnSection&m=saveEvnSectionFromOtherLpu',
				params: params,
				callback: function(options, success, response)
				{
					if (success) {
						var answer = Ext.util.JSON.decode(response.responseText);
						if (answer.success) {
							form.reloadTree();
						} else if (answer.Error_Code) {
							Ext.Msg.alert(lang['oshibka_#']+answer.Error_Code, answer.Error_Message);
						} else if (answer.Alert_Msg) {
							sw.swMsg.show({
								icon: Ext.MessageBox.QUESTION,
								msg: answer.Alert_Msg + lang['prodoljit'],
								title: lang['vopros'],
								buttons: Ext.Msg.YESNO,
								fn: function(buttonId)
								{
									if ('yes' == buttonId)
									{
										params.ignore_sex = 1;
										request(params);
									}
								}
							});
						}
					}
				}
			});
		};

		getWnd('swEvnSectionParamsSelectWindow').show({
			params: {
				Sex_id: node.attributes.Sex_id,
				EvnSection_id: node.attributes.EvnSection_id,
				MedStaffFact_id: form.userMedStaffFact.MedStaffFact_id,
				LpuSection_id: form.userMedStaffFact.LpuSection_id,
				MedPersonal_id: form.userMedStaffFact.MedPersonal_id
			},
			onHide: null,
			onSelect: function(params) {
				params.ignore_sex = 0;
				request(params);
			}
		});
		return true;
	},
	OpenEMK: function()
	{
		var form = this;
		var node = this.Tree.getSelectionModel().selNode;
		if (!node)
		{
			sw.swMsg.alert(lang['vnimanie'], lang['vyi_ne_vyibrali_element_koechnoy_strukturyi_otdeleniya']);
			return false;
		}
		if (getWnd('swPersonEmkWindow').isVisible())
		{
			sw.swMsg.alert(lang['soobschenie'], lang['forma_elektronnoy_istorii_bolezni_emk_v_dannyiy_moment_otkryita']);
			return false;
		}
		var Person_id = node.attributes.Person_id;
		var Server_id = node.attributes.Server_id;
		var PersonEvn_id = node.attributes.PersonEvn_id;
		if (!Person_id && !Server_id && !PersonEvn_id)
		{
			sw.swMsg.alert(lang['oshibka'], lang['vyi_ne_vyibrali_patsienta']);
			return false;
		}
		var searchNodeObj = false;
		if(node.attributes.EvnPS_id) {
			searchNodeObj = {
				parentNodeId: 'root',
				last_child: false,
				disableLoadViewForm: false,
				EvnClass_SysNick: 'EvnPS',
				Evn_id: node.attributes.EvnPS_id
			};
		}

		var emk_params = {
			Person_id: Person_id,
			Server_id: Server_id,
			PersonEvn_id: PersonEvn_id,
			userMedStaffFact: this.userMedStaffFact,
			MedStaffFact_id: this.userMedStaffFact.MedStaffFact_id,
			LpuSection_id: this.userMedStaffFact.LpuSection_id,
			ARMType: 'headnurse',
			addStacActions: ['action_New_EvnPS', 'action_StacSvid'],
			searchNodeObj: searchNodeObj,
			onChangeLpuSectionWard: function(params){
				this.reloadTree({
					beforeRestorePosition: function(){
						if(params && Ext.isArray(this.position) && this.position.length == 4) {
							//правим id ноды с палатой
							this.position[1] = (params.LpuSectionWard_id > 0)?('LpuSectionWard_id_'+params.LpuSectionWard_id):'noward';
						}
					}.createDelegate(this)
				});
			}.createDelegate(this),
			callback: function()
			{
				this.reloadTree();
				//также обновляем меню палат
				this.createListLpuSectionWard();
			}.createDelegate(this)
		};
		Ext.Ajax.request({
			url: '/?c=EvnPS&m=beforeOpenEmk',
			params: {Person_id: Person_id},
			failure: function(response, options) {
				showSysMsg(lang['pri_poluchenii_dannyih_dlya_proverok_proizoshla_oshibka']);
			},
			success: function(response, action)
			{
				if (response.responseText) {
					var answer = Ext.util.JSON.decode(response.responseText);
					if(!Ext.isArray(answer) || !answer[0])
					{
						showSysMsg(lang['pri_poluchenii_dannyih_dlya_proverok_proizoshla_oshibka_nepravilnyiy_otvet_servera']);
						return false;
					}
					if (answer[0].countOpenEvnPS > 0)
					{
						//showSysMsg('Создание новых КВС недоступно','У пациента имеются открытые КВС в даннном ЛПУ! Количество открытых КВС: '+ answer[0].countOpenEvnPS);
						emk_params.addStacActions = ['action_StacSvid']; //лочить кнопку создания случая лечения, если есть незакрытые КВС в данном ЛПУ #13272
					}
					getWnd('swPersonEmkWindow').show(emk_params);
				}
				else {
					showSysMsg(lang['pri_poluchenii_dannyih_dlya_proverok_proizoshla_oshibka_otsutstvuet_otvet_servera']);
				}
			}
		});
	},

	initComponent: function()
	{
		var current_window = this;

		this.filtersPanel = new Ext.FormPanel(
			{
				xtype: 'form',
				labelAlign: 'right',
				labelWidth: 50,
				style: 'margin-top: 5px',
				items:
					[{
						layout: 'column',
						items:
							[{
								layout: 'form',
								items:
									[{
										hiddenName: 'LpuSection_id',
										id: 'HNWPW_Search_LpuSection_id',
										emptyText: lang['otdelenie'],
										hideLabel: true,
										lastQuery: '',
										linkedElements: [
											'HNWPW_Search_MedStaffFact_id'
										],
										listWidth: 250,
										width: 250,
										xtype: 'swlpusectionglobalcombo',
										listeners:
										{
											'keydown': function (inp, e)
											{
												var form = Ext.getCmp('swHeadNurseWorkPlaceWindow');
												if (e.getKey() == Ext.EventObject.ENTER)
												{
													e.stopEvent();
													form.loadTree();
													form.openNodeSearch();
												}
											}.createDelegate(this)
										}
									}]
							},
								{
									layout: 'form',
									style: 'padding-left: 20px',
									items:
										[{
											id: 'HNWPW_Search_MedStaffFact_id',
											parentElementId: 'HNWPW_Search_LpuSection_id',
											emptyText: lang['vrach'],
											hideLabel: true,
											hiddenName: 'MedStaffFact_id',
											lastQuery: '',
											listWidth: 350,
											//tabIndex: ,
											width: 300,
											xtype: 'swmedstafffactglobalcombo',
											tpl: new Ext.XTemplate(
												'<tpl for="."><div class="x-combo-list-item">',
												'<table style="border: 0;">',
												'<td style="width: 45px;"><font color="red">{MedPersonal_TabCode}&nbsp;</font></td>',
												'<td><span style="font-weight: bold;">{MedPersonal_Fio}</span></td>',
												'</tr></table>',
												'</div></tpl>'
											),
											listeners:
											{
												'keydown': function (inp, e)
												{
													var form = Ext.getCmp('swHeadNurseWorkPlaceWindow');
													if (e.getKey() == Ext.EventObject.ENTER)
													{
														e.stopEvent();
														form.loadTree();
														form.openNodeSearch();
													}
												}
											}
										}]
								}]
					},
						{
							xtype: 'fieldset',
							autoHeight: true,
							title: lang['patsient'],
							collapsible: true,
							layout: 'column',
							style: 'margin: 5px 0 0 0',
							listeners: {
								collapse: function(p) {
									current_window.doLayout();
								},
								expand: function(p) {
									current_window.doLayout();
								}
							},

							items:
								[{
									layout: 'form',
									labelWidth: 60,
									width: 200,
									items:
										[{
											xtype: 'textfieldpmw',
											anchor: '100%',
											id: 'HNWPW_Search_F',
											fieldLabel: lang['familiya'],
											listeners:
											{
												'keydown': function (inp, e)
												{
													var form = Ext.getCmp('swHeadNurseWorkPlaceWindow');
													if (e.getKey() == Ext.EventObject.ENTER)
													{
														e.stopEvent();
														form.loadTree();
														form.openNodeSearch();
													}
												}
											}
										}]
								},
									{
										layout: 'form',
										width: 200,
										items: [{
											xtype: 'textfieldpmw',
											anchor: '100%',
											id: 'HNWPW_Search_I',
											fieldLabel: lang['imya'],
											listeners:
											{
												'keydown': function (inp, e)
												{
													var form = Ext.getCmp('swHeadNurseWorkPlaceWindow');
													if (e.getKey() == Ext.EventObject.ENTER)
													{
														e.stopEvent();
														form.loadTree();
														form.openNodeSearch();
													}
												}
											}
										}]
									},
									{
										layout: 'form',
										labelWidth: 80,
										width: 220,
										items: [{
											xtype: 'textfieldpmw',
											anchor: '100%',
											id: 'HNWPW_Search_O',
											fieldLabel: lang['otchestvo'],
											listeners:
											{
												'keydown': function (inp, e)
												{
													var form = Ext.getCmp('swHeadNurseWorkPlaceWindow');
													if (e.getKey() == Ext.EventObject.ENTER)
													{
														e.stopEvent();
														form.loadTree();
														form.openNodeSearch();
													}
												}
											}
										}]
									},
									{
										layout: 'form',
										items:
											[{
												xtype: 'swdatefield',
												//renderer: Ext.util.Format.dateRenderer('d.m.Y'),
												format: 'd.m.Y',
												plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
												id: 'HNWPW_Search_BirthDay',
												fieldLabel: lang['dr'],
												listeners:
												{
													'keydown': function (inp, e)
													{
														var form = Ext.getCmp('swHeadNurseWorkPlaceWindow');
														if (e.getKey() == Ext.EventObject.ENTER)
														{
															e.stopEvent();
															form.loadTree();
															form.openNodeSearch();
														}
													}
												}
										}]
									},
									{
										layout: 'form',
										labelWidth: 75,
										hidden: getRegionNick() != 'msk',
										items:
											[{
												xtype: 'textfieldpmw',
												width: 60,
												margins: '20 0',
												id: 'HNWPW_Search_PSNumCard',
												name: 'PSNumCard',
												fieldLabel: lang['nomer_kvs'],
												listeners:
													{
														'keydown': function (inp, e) {
															var form = Ext.getCmp('swHeadNurseWorkPlaceWindow');
															if (e.getKey() == Ext.EventObject.ENTER) {
																e.stopEvent();
																form.loadTree();
																form.openNodeSearch();
															}
														}
													}
											}]
									},
									{
										layout: 'form',
										items:
											[{
												style: "padding-left: 20px",
												xtype: 'button',
												id: 'HNWPW_BtnSearch',
												text: lang['nayti'],
												iconCls: 'search16',
												handler: function()
												{
													var form = Ext.getCmp('swHeadNurseWorkPlaceWindow');
													var tpl = new Ext.XTemplate(' ');
													tpl.overwrite(current_window.RightPanel.body, {});
													form.loadTree();
													form.openNodeSearch();
												}
											}]
									},
									{
										layout: 'form',
										items:
											[{
												style: "padding-left: 20px",
												xtype: 'button',
												id: 'HNWPW_BtnClear',
												text: lang['sbros'],
												iconCls: 'resetsearch16',
												handler: function()
												{
													var form = Ext.getCmp('swHeadNurseWorkPlaceWindow');
													form.findById('HNWPW_Search_F').setValue(null);
													form.findById('HNWPW_Search_I').setValue(null);
													form.findById('HNWPW_Search_O').setValue(null);
													form.findById('HNWPW_Search_PSNumCard').setValue(null);
													form.findById('HNWPW_Search_BirthDay').setValue(null);
													form.findById('HNWPW_Search_MedStaffFact_id').setValue(null);
													form.loadTree();
												}
											}]
									}]
						}]
			})

		// Конфиги экшенов для контекстного меню и тулзбара грида списка больных
		var Actions =
			[
				{name:'open_stac_emk', disabled: true, text:lang['otkryit_emk'], tooltip: lang['otkryit_elektronnuyu_meditsinskuyu_kartu_patsienta'], iconCls : 'open16', handler: function() {this.OpenEMK();}.createDelegate(this)},
				{name:'refresh', text:BTN_GRIDREFR, tooltip: BTN_GRIDREFR, iconCls : 'refresh16', handler: function() {this.reloadTree();this.emptyevndoc();}.createDelegate(this)},
				{
					name:'print', text:BTN_GRIDPRINT, tooltip: BTN_GRIDPRINT, iconCls : 'print16',  menu:
					new Ext.menu.Menu([
						{text: lang['spisok'], handler: function () {this.printLpuSectionPacients();}.createDelegate(this)},
						{text: lang['listok_pribyitiya'], hidden: getRegionNick()!='khak', handler: function () {this.printAddressLeaf('arrival');}.createDelegate(this)},
						{text: lang['listok_ubyitiya'], hidden: getRegionNick()!='khak', handler: function () {this.printAddressLeaf('departure');}.createDelegate(this)},
						{text: 'Лист регистрации переливания трансфузионных сред (005/у)', handler: function () {this.printCmp_f005u();}.createDelegate(this), hidden: getRegionNick()=='kz'},
						{text: langs('Журнал регистрации переливания трансфузионных сред (009/у)'), hidden: getRegionNick()=='kz', handler: function () {this.printCmp_f009u();}.createDelegate(this)}
					])
				},

				{name:'update_section', disabled: true, text:lang['perevod_v_otdelenie'], tooltip: lang['perevesti_patsienta_v_drugoe_otdelenie'], iconCls : 'update-section16', handler: function() {return false;}.createDelegate(this)},

				{name:'actions', key: 'actions', text:lang['deystviya'], menu: [
					{name:'accept', disabled: true, text:lang['prinyat_v_otdelenie'], tooltip: lang['prinyat_patsienta_iz_drugogo_otdeleniya'], iconCls : 'update-ward16', handler: function() {this.acceptFromOtherSection();}.createDelegate(this)},
					{name:'kvs_edit', disabled: true, text:lang['redaktirovat_kvs'], tooltip: lang['redaktirovat_kvs'], iconCls : 'edit16', handler: function() {this.openEvnPSEditWindow();}.createDelegate(this)},
					{name:'update_ward', disabled: true, text:lang['perevod_v_palatu'], tooltip: lang['perevesti_patsienta_v_druguyu_palatu'], iconCls : 'update-ward16', menu: new Ext.menu.Menu({id:'ListMenuWard'})},
					{name:'update_doctor', disabled: true, text:lang['izmenit_vracha'], tooltip: lang['izmenit_lechaschego_vracha_patsienta'], iconCls : 'update-doctor16', menu: new Ext.menu.Menu({id:'ListMenuMedPersonal'})},
					{name:'leave', disabled: true, text:lang['vyipisat'], tooltip: lang['vyipisat_patsienta'], iconCls : 'leave16', menu: new Ext.menu.Menu({id:'ListLeaveTypeMenu'})},
					{name:'add_patient', text: lang['dobavit_patsienta'], tooltip: lang['dobavit_patsienta'], iconCls: 'add16', handler: this.addPatient.createDelegate(this) }
				], tooltip: lang['deystviya'], iconCls : 'x-btn-text', icon: 'img/icons/actions16.png', handler: function() {}},
				{name: 'group_by',	key: 'group_by', text: langs('Группировать'), menu: [
						{name: 'po_palatam', text: langs('По палатам'), tooltip: langs('По палатам'), handler: this.loadGrouped.createDelegate(this, ['po_palatam', 'по палатам'])},
						{name: 'po_rejimam', text: langs('По режимам'), tooltip: langs('По режимам'), handler: this.loadGrouped.createDelegate(this, ['po_rejimam', 'по режимам'])},
						{name: 'po_statusam', text: langs('По статусам'), tooltip: langs('По статусам'), handler: this.loadGrouped.createDelegate(this, ['po_statusam', 'по статусам'])}
					], tooltip: langs('Группировать')}

			];
		this.treeActions = new Array();
		for (i=0; i < Actions.length; i++) {
			this.treeActions[Actions[i]['name']] = new Ext.Action(Actions[i]);
			if( Actions[i].menu ) {
				this.treeActions[Actions[i]['name']]['menu'] = {};
				for(var j=0; j<Actions[i].menu.length; j++) {
					this.treeActions[Actions[i]['name']]['menu'][Actions[i].menu[j]['name']] = new Ext.Action(Actions[i].menu[j]);
				}
			}
		}
		delete(Actions);

		this.GroupByButton = new Ext.Button({
			key: 'group_by',
			text: langs('Группировать'),
			tooltip: langs('Группировать'),
			iconCls : 'x-btn-text',
			hidden: false,
			menu: [
				this.treeActions['group_by'].menu.po_palatam,
				this.treeActions['group_by'].menu.po_rejimam,
				this.treeActions['group_by'].menu.po_statusam
			]
		});

		this.treeToolbar = new Ext.Toolbar(
			{
				id: 'HNWPW_Toolbar',
				items:
					[
						this.treeActions.open_stac_emk,
						{
							xtype : "tbseparator"
						},
						this.treeActions.refresh,
						{
							xtype : "tbseparator"
						},
						this.treeActions.print,
						{
							xtype : "tbseparator"
						},
						{
							text: lang['deystviya'],
							tooltip: lang['deystviya'],
							iconCls : 'x-btn-text',
							icon: 'img/icons/actions16.png',
							menu: [
								this.treeActions.actions.menu.accept,
								this.treeActions.actions.menu.kvs_edit,
								this.treeActions.actions.menu.update_ward,
								this.treeActions.actions.menu.update_doctor,
								this.treeActions.actions.menu.leave,
								this.treeActions.actions.menu.add_patient
							]
						},
						this.GroupByButton,
						{
							xtype : "tbseparator"
						},
						{
							xtype : "tbfill"
						}
					]
			});

		this.contextMenuOtherLpu = new Ext.menu.Menu(
			{
				items: [
					//this.treeActions.open_stac_emk,
					this.treeActions.actions.menu.accept
				],
				listeners: {
					show: function(c) {
						//c.items.items[0].enable();
						//c.items.items[1].enable();
						//this.getTreeAction('open_stac_emk').enable();
						this.getTreeAction('accept').enable();
					}.createDelegate(this),
					hide: function(c) {
						//log(c);
					}
				}
			});

		this.contextMenu  = new Ext.menu.Menu(
			{
				items: [
					this.treeActions.open_stac_emk,
					this.treeActions.refresh,
					this.treeActions.print,
					{
						text: lang['deystviya'],
						tooltip: lang['deystviya'],
						iconCls : 'x-btn-text',
						icon: 'img/icons/actions16.png',
						menu: [
							//this.treeActions.actions.menu.accept,
							this.treeActions.actions.menu.kvs_edit,
							this.treeActions.actions.menu.update_ward,
							this.treeActions.actions.menu.update_doctor,
							this.treeActions.actions.menu.leave,
							this.treeActions.actions.menu.add_patient
						]
					}
				],
				listeners: {
					show: function(c) {
						this.getTreeAction('open_stac_emk').enable();
						this.getTreeAction('refresh').enable();
						this.getTreeAction('print').enable();
						this.getTreeAction('kvs_edit').enable();
						this.getTreeAction('update_ward').enable();
						this.getTreeAction('update_doctor').enable();
						this.getTreeAction('leave').enable();
					}.createDelegate(this),
					hide: function(c) {
						//log(c);
					}
				}
			});

		this.Tree = new Ext.tree.TreePanel(
			{
				id: 'HNWPW_Tree',
				region: 'center',
				animate:false,
				width: 800,
				enableDD: false,
				autoScroll: true,
				autoLoad:false,
				border: false,
				//rootVisible: false,
				split: true,
				tbar: current_window.treeToolbar,
				contextMenu: this.contextMenu,
				listeners:
				{
					contextmenu: function(node, e)
					{

						if (node.attributes.Person_id && !node.attributes.AnotherSection)
						{
							node.getOwnerTree().contextMenu = current_window.contextMenu; // меню
							var c = node.getOwnerTree().contextMenu;
							c.contextNode = node;
							c.showAt(e.getXY());
							node.select();
						} else if (node.attributes.Person_id && node.attributes.AnotherSection) {
							node.getOwnerTree().contextMenu = current_window.contextMenuOtherLpu; // меню для переведенных (Открыть ЭМК / Принять в отделение)
							var c = node.getOwnerTree().contextMenu;
							c.contextNode = node;
							c.showAt(e.getXY());
							node.select();
						}
					},
					load: function(node)
					{
						//
					}
				},
				root:
				{
					nodeType: 'async',
					text: lang['koechnaya_struktura_otdeleniya'],
					id:'root',
					expanded: false
				},
				rootVisible: false,
				loader: new Ext.tree.TreeLoader(
					{
						listeners:
						{
							load: function(loader, node, response)
							{
								callback:
								{
									current_window.getLoadMask(LOAD_WAIT).hide();
									if(node.getDepth() == 0 && current_window.firstLoad)
									{
										cns = node.childNodes;
										for(var i = 0; i < cns.length; i++) {
											if(cns[i].attributes.object && cns[i].attributes.object == 'LpuSectionWard')
											{
												cns[i].expand();
											}
										}
										current_window.firstLoad = false;
									}
									if(node.getDepth() > 0 && node.SearchSign)
									{
										cn = node.childNodes[0];
										if(!Ext.isEmpty(cn))
											cn.fireEvent('click', cn);
									}
								}
							},
							loadexception: function(node)
							{
								current_window.getLoadMask(LOAD_WAIT).hide();
							},
							beforeload: function (tl, node)
							{
								current_window.getLoadMask(LOAD_WAIT).show();
								//запрещаем загрузку при инициализации до получения текущей даты
								if (!current_window.curDate)
								{
									return false;
								}
								var LpuSection_id = current_window.findById('HNWPW_Search_LpuSection_id').getValue();
								if (node.getDepth()==0)
								{
									tl.baseParams.object = 'LpuSection';
									tl.baseParams.object_id = 'LpuSection_id';
									tl.baseParams.object_value = LpuSection_id;
								}
								else
								{
									tl.baseParams.object = node.attributes.object;
									tl.baseParams.object_id = node.attributes.object_id;
									tl.baseParams.object_value = node.attributes.object_value;
									tl.baseParams.group = node.attributes.group;
								}
								tl.baseParams.level = node.getDepth();
								tl.baseParams.LpuSection_id = LpuSection_id;
								tl.baseParams.ARMType = current_window.ARMType;
								var datefield  = Ext.getCmp('HNWPW_Search_date');
								tl.baseParams.date = Ext.util.Format.date(datefield.getValue(), 'd.m.Y');
								tl.baseParams.filter_Person_F = current_window.findById('HNWPW_Search_F').getValue();
								tl.baseParams.filter_Person_I = current_window.findById('HNWPW_Search_I').getValue();
								tl.baseParams.filter_Person_O = current_window.findById('HNWPW_Search_O').getValue();
								tl.baseParams.filter_PSNumCard = current_window.findById('HNWPW_Search_PSNumCard').getValue();
								tl.baseParams.filter_Person_BirthDay = Ext.util.Format.date(current_window.findById('HNWPW_Search_BirthDay').getValue(), 'd.m.Y');
								tl.baseParams.filter_MedStaffFact_id = current_window.findById('HNWPW_Search_MedStaffFact_id').getValue();
							}
						},
						dataUrl:'/?c=EvnSection&m=getSectionTreeData'
					}),
				selModel: new Ext.tree.KeyHandleTreeSelectionModel()
			});

		this.Tree.on('dblclick', function(node)
		{
			if(node.attributes.Person_id)
			{
				Ext.getCmp('swHeadNurseWorkPlaceWindow').OpenEMK();
			}
		});

		var NumberBedsMark = [
			'<table width="800" style="font-size: 10pt;">'+
				'<tr><td width="100">На <b>'+new Date().format('d.m.Y')+'</b></td><td align="right" width="220">Количество мест в отделении: <b>{LpuSection_BedCount}</b></td><td align="center" width="120">из них:</td><td width="140"><img align="left" style="margin-right: 2px; width: 16px; height: 16px;" src="/img/icons/male16.png" border="0">мужских - <b>{LpuSection_BedCount_men}</b></td><td width="140"><img align="left" style="margin-right: 2px; width: 16px; height: 16px;" src="/img/icons/female16.png" border="0">женских - <b>{LpuSection_BedCount_women}</b></td><td></td></tr>'+
				'<tr><td></td><td align="right">Свободно: <b>{free_BedCount}</b></td><td align="center">из них:</td><td><img align="left" style="margin-right: 2px; width: 16px; height: 16px;" src="/img/icons/male16.png" border="0">мужских - <b>{free_BedCount_men}</b></td><td><img align="left" style="margin-right: 2px; width: 16px; height: 16px;" src="/img/icons/female16.png" border="0">женских - <b>{free_BedCount_women}</b></td><td></td></tr>'+
				'</table>'
		];
		this.NumberBedsTpl = new Ext.Template(NumberBedsMark);

		this.dateMenu = new sw.Promed.SwDateField(
			{
				fieldLabel: lang['data'],
				id: 'HNWPW_Search_date',
				plugins:
					[
						new Ext.ux.InputTextMask('99.99.9999', false)
					],
				xtype: 'swdatefield',
				format: 'd.m.Y',
				hideLabel: true,
				listeners:
				{
					'keydown': function (inp, e)
					{
						var form = Ext.getCmp('swHeadNurseWorkPlaceWindow');
						if (e.getKey() == Ext.EventObject.ENTER)
						{
							e.stopEvent();
							form.loadTree();
						}
					},
					'change': function (field, newValue, oldValue)
					{
						var form = Ext.getCmp('swHeadNurseWorkPlaceWindow');
						form.createListLpuSectionWard();
					}
				}
			});

		this.formActions = new Array();
		this.formActions.prev = new Ext.Action(
			{
				text: lang['predyiduschiy'],
				xtype: 'button',
				iconCls: 'arrow-previous16',
				handler: function()
				{
					// на один день назад
					this.prevDay();
					this.loadTree();
					//this.loadNumberBeds();
				}.createDelegate(this)
			});
		this.formActions.next = new Ext.Action(
			{
				text: lang['sleduyuschiy'],
				xtype: 'button',
				iconCls: 'arrow-next16',
				handler: function()
				{
					// на один день вперед
					this.nextDay();
					this.loadTree();
					//this.loadNumberBeds();
				}.createDelegate(this)
			});

		this.RightPanel = new Ext.Panel({
			region: 'east',
			title: ' ',
			animCollapse: false,
			id: 'RightPanel',
			bodyStyle: 'background-color: #e3e3e3',
			autoScroll: true,
			minSize: 400,
			listeners:
			{
				render: function(p) {
					var body_width = Ext.getBody().getViewSize().width;
					p.setWidth(body_width * (1/2));
				}
			},
			layoutConfig:
			{
				titleCollapse: true,
				animate: true,
				activeOnTop: false,
				style: 'border 0px'
			},
			collapsible: true,
			split: true
		});

		this.NumberBedsPanel = new Ext.Panel(
			{
				id: 'NumberBedsPanel',
				bodyStyle: 'padding:5px',
				layout: 'fit',
				width: '100%',
				region: 'center',
				border: false,
				frame: false,
				height: 45,
				maxSize: 45,
				html: ''
			});

		var DocumentStac = [
			'<div id="rightEmkPanel" style="font-family: tahoma,arial,helvetica,sans-serif; font-size: 13px;" onMouseOver="document.getElementById(&quot;toolbar&quot;).style.display=&quot;block&quot;" onMouseOut="document.getElementById(&quot;toolbar&quot;).style.display=&quot;none&quot;">'+
				'<div class="frame evn_pl">'+
				'<div style="width: 0px; margin: 0px; display: none; float: right;" class="columns" id="toolbar"><div class="right"><div class="toolbar"><a onClick="Ext.getCmp(&quot;'+this.id+'&quot;).printEvnDoc();" class="button icon icon-print16" title="Печатать документ"><span></span></a></div></div></div>'+
				'<h1 style="font-size: 12pt;" align="center">{Person_Fio}</h1><br />'+
				'<table border="0" width="100%"><tr>'+
				'<td width="5%" style="vertical-align: top;"><!--<img height="106" width="68" src="/img/{sex_img}" />--></td>'+
				'<td style="font-size: 10pt;">'+
				'<b>Пол:</b> {Sex}<br />'+
				'<b>Дата рождения:</b> {Person_BirthDay}<br />'+
				'<b>Соц. статус:</b> {SocStatus_Name}, <b>СНИЛС:</b> {Person_Snils}<br />'+
				'<b>Регистрация:</b> {Address_Address}<br />'+
				'<b>Полис:</b> {Polis}<br />'+
				'<b>Основное прикрепление:</b> {Lpu_data}<br /><br />'+
				'<b>Отделение:</b> {LpuSection_FullName}<br />'+
				'<b>Лечащий врач:</b> {MPFio}<br /><br />'+
				'<b>Дата и время поступления:</b> {setDT}<br />'+
				'<b>Дата и время выписки:</b> {disDT}<br /><br />'+
				'<b>Нетрудоспособность:</b> {Sticks}<br /><br />'+
				'<b>Диагноз:</b> {diag_FullName}<br /><br />'+
				'<b>' + getMESAlias() + ':</b> {Mes}<br /><br />'+
				'<b>Оперативное лечение:</b><br />{Surgery}'+
				'</td>'+
				'</tr></table>'+
				'</div></div>'
		];

		this.Tree.getSelectionModel().on('selectionchange', function(sm, node) {
			// log(node.attributes);
			var print_menu = this.getTreeAction('print').initialConfig.menu;

			if(node && node.attributes.Person_id && !node.attributes.AnotherSection) {
				this.getTreeAction('accept').disable();
				this.getTreeAction('open_stac_emk').enable();
				this.getTreeAction('refresh').enable();
				this.getTreeAction('update_ward').enable();
				this.getTreeAction('update_section').enable();
				this.getTreeAction('update_doctor').enable();
				this.getTreeAction('leave').enable();
				this.getTreeAction('kvs_edit').enable();

				print_menu.items.itemAt(3).enable();

				if(this.menuLpuSectionWard) {
					this.menuLpuSectionWard.items.each(function(item,i,l) {
						if(item.Sex_id)
							item.setVisible(item.Sex_id==node.attributes.Sex_id);
					});
				}
			}
			else if (node && node.attributes.Person_id && node.attributes.AnotherSection) {
				this.getTreeAction('accept').enable();
				this.getTreeAction('open_stac_emk').enable();
				this.getTreeAction('refresh').enable();
				this.getTreeAction('update_ward').disable();
				this.getTreeAction('update_section').disable();
				this.getTreeAction('update_doctor').disable();
				this.getTreeAction('leave').disable();
				this.getTreeAction('kvs_edit').disable();

				print_menu.items.itemAt(3).disable();
			} else {
				this.getTreeAction('accept').disable();
				this.getTreeAction('open_stac_emk').disable();
				this.getTreeAction('refresh').enable();
				this.getTreeAction('update_ward').disable();
				this.getTreeAction('update_section').disable();
				this.getTreeAction('update_doctor').disable();
				this.getTreeAction('leave').disable();
				this.getTreeAction('kvs_edit').disable();

				print_menu.items.itemAt(3).disable();
			}
		}.createDelegate(this));

		this.Tree.on('click', function(node){
			if(node.attributes.Person_id)
			{
				var params =
				{
					Person_id: node.attributes.Person_id,
					Server_id: node.attributes.Server_id,
					EvnSection_id: node.attributes.EvnSection_id,
					LpuSection_id: node.attributes.LpuSection_id,
					date: Ext.util.Format.date(Ext.getCmp('HNWPW_Search_date').getValue(), 'Y.m.d')
				}
				var lm = new Ext.LoadMask(Ext.get('RightPanel'), {msg: 'Идёт загрузка документа...'});
				lm.show();

				Ext.Ajax.request({
					url: '/?c=EvnSection&m=getEvnDocumentStac',
					params: params,
					callback: function(options, success, response)
					{
						lm.hide();
						if ( success )
						{
							var response_obj = Ext.util.JSON.decode(response.responseText);

							response_obj.setDT = (response_obj.EvnSection_setDate+response_obj.EvnSection_setTime != '')?response_obj.EvnSection_setDate+' '+response_obj.EvnSection_setTime:'';
							response_obj.disDT = (response_obj.EvnSection_disDate+response_obj.EvnSection_disTime != '')?response_obj.EvnSection_disDate+' '+response_obj.EvnSection_disTime:'';

							response_obj.Lpu_data = ((response_obj.Lpu_Nick != '')?'ЛПУ: '+response_obj.Lpu_Nick:'')+((response_obj.Lpu_Nick != '' && response_obj.LpuRegion_Name != '')?', ':'')+((response_obj.LpuRegion_Name != '')?'участок №: '+response_obj.LpuRegion_Name:'')+((response_obj.PersonCardState_begDate !='')?', дата прикрепления: '+response_obj.PersonCardState_begDate:'');
							if(response_obj.Sex_id=='2') {
								response_obj.Lpu_data += '<br /><b>Гинекологическое прикрепление:</b> '+((response_obj.WLpu_Nick != '')?'ЛПУ: '+response_obj.WLpu_Nick:'<span style="color: #666;">нет данных</span>')+((response_obj.WLpu_Nick != '' && response_obj.WLpuRegion_Name != '')?', ':'')+((response_obj.WLpuRegion_Name != '')?'участок №: '+response_obj.WLpuRegion_Name:'')+((response_obj.WPersonCardState_begDate !='')?', дата прикрепления: '+response_obj.WPersonCardState_begDate:'');
							}
							response_obj.Polis_Full = (response_obj.Polis_Ser+response_obj.Polis_Num != '')?response_obj.Polis_Ser+' '+response_obj.Polis_Num+', ':'';
							response_obj.Polis = response_obj.Polis_Full+'Выдан: '+response_obj.Polis_begDate+((response_obj.Polis_endDate != '')?', Закрыт:'+response_obj.Polis_endDate:'');
							response_obj.sex_img = (response_obj.Sex_id == '1')?'men.jpg':'women.jpg';
							response_obj.Sex = (response_obj.Sex_id == '1')?'мужской':'женский';

							response_obj.Sticks = '';
							for(i in response_obj.sticks)
							{
								if(response_obj.sticks[i].EvnStick_Ser + response_obj.sticks[i].EvnStick_Num != '' && typeof(response_obj.sticks[i].EvnStick_Ser) != 'undefined' && typeof(response_obj.sticks[i].EvnStick_Num) != 'undefined')
								{
									response_obj.Sticks += 'ЛВН <a title="Показать документ о нетрудоспособности" href="javascript:">'+response_obj.sticks[i].EvnStick_Ser+' № '+response_obj.sticks[i].EvnStick_Num+'</a>';
								}
								if(response_obj.sticks[i].EvnStick_begDate != '' && typeof(response_obj.sticks[i].EvnStick_begDate) != 'undefined')
								{
									response_obj.Sticks += ', выдан: '+response_obj.sticks[i].EvnStick_begDate+'г.';
								}
								if(response_obj.sticks[i].EvnStick_disDT != '' && typeof(response_obj.sticks[i].EvnStick_disDT) != 'undefined')
								{
									response_obj.Sticks += ' по '+response_obj.sticks[i].EvnStick_disDT+'г.'
								}
								if(typeof(response_obj.sticks[i].EvnStick_Ser) != 'undefined')
								{
									response_obj.Sticks += '<br />';
								}
							}

							response_obj.Surgery = '';
							for(i in response_obj.surgery)
							{
								if(response_obj.surgery[i].EvnUsluga_setDate != '' && typeof(response_obj.surgery[i].EvnUsluga_setDate) != 'undefined')
								{
									response_obj.Surgery += '<a href="javascript:">'+response_obj.surgery[i].EvnUsluga_setDate+'</a>';
								}
								if(response_obj.surgery[i].Usluga_Name != '' && typeof(response_obj.surgery[i].Usluga_Name) != 'undefined')
								{
									response_obj.Surgery += ': '+response_obj.surgery[i].Usluga_Name;
								}
								if(typeof(response_obj.surgery[i].Usluga_Name) != 'undefined')
								{
									response_obj.Surgery += '<br />';
								}
							}

							response_obj.Mes = '';
							if(response_obj.Mes_Code != '')
							{
								response_obj.Mes += '<a href="javascript:">'+response_obj.Mes_Code+'</a><br />Норматив - '+response_obj.KoikoDni+' койко/дней<br />';//, фактические койкодни: '+response_obj.EvnSecdni+'
								var otkl = Math.floor((response_obj.EvnSecdni/response_obj.KoikoDni)*100);
								//var razn = otkl - 100;
								/*response_obj.Mes += '<table cellspacing="0" width="50%"><tr><td align="left">Отклонение:&nbsp;</td>';

								 response_obj.Mes += '<td width="'+otkl+'%" style="border-top: 1px solid #000; border-right: 0px; border-left: 1px solid #000; border-bottom: 1px solid #000; background: #fcdd76;"></td>';
								 response_obj.Mes += '<td width="'+(((100-otkl) > 0)?100-otkl:0)+'%" style="border-left: 0px; border-top: 1px solid #000; border-bottom: 1px solid #000; border-right: 1px solid #000; background: blue;"></td>';

								 response_obj.Mes += '<td align="center">&nbsp;('+razn+'%)</td></tr></table>';
								 */
								var background;
								if (otkl < 85)
								{
									background = '#ff0000;';
								}
								else if (otkl >= 85 && otkl < 100)
								{
									background = '#fcdd76;';
								}
								else if (otkl >= 100)
								{
									background = 'green;';
								}


								response_obj.Mes += '<table style="text-align: left; font-family: tahoma,arial,helvetica,sans-serif; font-size: 13px;" height="30" width="400" cellspacing="0">';
								response_obj.Mes += '<tr valign="bottom"><td width="80" rowspan="2" valign="bottom">Выполнение:&nbsp;</td><td width="55" style="font-size: 8pt;"><div style="float: left; margin-left: -1px;">0%<br />|</div></td><td width="55" style="font-size: 8pt;"><div style="float: left;">25%<br />|</div></td><td width="55" style="font-size: 8pt;"><div style="float: left;">50%<br />|</div></td><td width="55" style="font-size: 8pt;"><div style="float: left;">75%<br />|</div></td><td width="30" rowspan="2" style="font-size: 8pt;" valign="top"><div style="float: left; margin-left: -2px;">100%<br />|</div></td><td valign="bottom" rowspan="2">('+otkl+'%)</td></tr>';
								response_obj.Mes += '<tr height="6"><td colspan="4">';

								response_obj.Mes += '<table cellspacing="0" height="6" width="100%" style="border-top: 1px solid #000; border-bottom: 1px solid #000; border-left: 1px solid #000; border-right: 1px solid #000;"><tr>';


								response_obj.Mes += '<td width="'+otkl+'%" style="background: '+background+'"></td>';
								response_obj.Mes += '<td width="'+(((100 - otkl) >= 0)?100 - otkl:0)+'%"></td>';



								response_obj.Mes += '</tr></table>';

								response_obj.Mes += '</td></tr>';
								response_obj.Mes += '</table>';
							}

							for(i in response_obj)
							{
								if(response_obj[i] == '')
								{
									response_obj[i] = '<span style="color: #666;">нет данных</span>';
								}
							}

							current_window = Ext.getCmp('swHeadNurseWorkPlaceWindow');
							current_window.RightPanel.tpl = new Ext.Template(DocumentStac);
							current_window.RightPanel.tpl.overwrite(current_window.RightPanel.body, response_obj);
						}
						else
						{
							Ext.getCmp('swHeadNurseWorkPlaceWindow').emptyevndoc();
							sw.swMsg.alert(lang['oshibka'], lang['ne_udalos_nayti_dannyih_po_etomu_patsientu']);
						}
					}
				});
			}
			else
			{
				Ext.getCmp('swHeadNurseWorkPlaceWindow').emptyevndoc();
			}
		});

		var form = this;
		// Конфиги акшенов для левой панели
		var configActions =
		{
			action_JourEvnPrescr: {nn:'action_JourEvnPrescr', text:lang['jurnal_naznacheniy'], tooltip: lang['otkryit_jurnal_naznacheniy'], iconCls : 'therapy-plan32', handler: function() {getWnd('swEvnPrescrJournalWindow').show({userMedStaffFact: this.userMedStaffFact});}.createDelegate(this)},
			action_SkladMO: {nn:'action_SkladMO', text:lang['sklad_ostatkov_mo'], tooltip: lang['sklad_ostatkov_mo'], iconCls : 'manufacturer32', handler: function() {getWnd('swSkladMO').show({action:'view'});}.createDelegate(this)},
			action_Medication:
			{
				nn:'action_Medication',
				text:lang['medikamentyi'],
				tooltip:lang['medikamentyi'],
				iconCls:'rls-torg32',
				menuAlign:'tr',
				hidden: !(getRegionNick() != 'vologda' || (getRegionNick() == 'vologda' && getDrugControlOptions().drugcontrol_module == '1')),
				menu: new Ext.menu.Menu({
					items: [
						{
							text: lang['zayavki_na_medikamentyi'],
							tooltip: lang['zayavki_na_medikamentyi'],
							iconCls: 'dlo16',
							handler: function()
							{
								getWnd('swDocZayavViewWindow').show({Contragent_tid: form.Contragent_id});
							}
						},
						{
							text: lang['dokumentyi_ucheta_medikamentov'],
							tooltip: lang['dokumentyi_ucheta_medikamentov'],
							iconCls: 'doc-uch16',
							handler: function()
							{
								getWnd('swDokUcLpuViewWindow').show({Contragent_tid: form.Contragent_id});
							}
						},
						{
							text: lang['aktyi_spisaniya_medikamentov'],
							tooltip: lang['aktyi_spisaniya_medikamentov'],
							iconCls: 'doc-spis16',
							handler: function()
							{
								getWnd('swDokSpisViewWindow').show({Contragent_sid: form.Contragent_id});
							}
						},
						{
							text: lang['dokumentyi_vvoda_ostatkov'],
							tooltip: lang['dokumentyi_vvoda_ostatkov'],
							iconCls: 'doc-ost16',
							handler: function()
							{
								getWnd('swDokOstViewWindow').show({Contragent_tid: form.Contragent_id});
							}
						},
						{
							text: lang['inventarizatsionnyie_vedomosti'],
							tooltip: lang['inventarizatsionnyie_vedomosti'],
							iconCls: 'farm-inv16',
							handler: function()
							{
								getWnd('swDokInvViewWindow').show({Contragent_sid: form.Contragent_id});
							}
						},
						{
							tooltip: lang['ostatki_medikamentov_po_kontragentam'],
							text: lang['ostatki_medikamentov_po_kontragentam'],
							iconCls : 'drug-sklad16',
							handler: function(){
								getWnd('swMedOstatSearchWindow').show({Contragent_id: form.Contragent_id});
							}
						}
						
					]
				})
			},
			action_CreateDoc: {
				nn: 'action_CreateDoc',
				tooltip: langs('Создать документ учета медикаментов'),
				text: langs('Создать документ учета медикаментов'),
				iconCls : 'document32',
				hidden: !(getRegionNick() == 'vologda' && getDrugControlOptions().drugcontrol_module == '2'),
				menuAlign: 'tr',
				menu: new Ext.menu.Menu({
					items: [{
						text: langs('Заявка-требование'),
						tooltip: langs('Заявка-требование'),
						iconCls: 'document16',
						handler: function() {
							var wnd = Ext.getCmp('swHeadNurseWorkPlaceWindow');

							getWnd('swWhsDocumentUcEditWindow').show({
								action: 'add',
								WhsDocumentClass_id: 2,
								WhsDocumentClass_Code: 2,
								userMedStaffFact: wnd.userMedStaffFact
							});
						}
					}, {
						text: langs('Перемещение'),
						tooltip: langs('Перемещение'),
						iconCls: 'document16',
						handler: function() {
							var wnd = Ext.getCmp('swHeadNurseWorkPlaceWindow');
							wnd.openDocumentUcAddWindow(15); //15 - Накладная на внутреннее перемещение
						}
					}, {
						text: langs('Списание'),
						tooltip: langs('Списание'),
						iconCls: 'document16',
						handler: function() {
							var wnd = Ext.getCmp('swHeadNurseWorkPlaceWindow');
							wnd.openDocumentUcAddWindow(2); //2 - Документ списания медикаментов
						}
					}, {
						text: langs('Инвентаризация'),
						tooltip: langs('Инвентаризация'),
						iconCls: 'invent16',
						menuAlign: 'tr?',
						menu: new Ext.menu.Menu({
							items: [{
								tooltip: langs('Приказы на проведение инвентаризации'),
								text: langs('Приказы на проведение инвентаризации'),
								iconCls : 'document16',
								handler: function() {
									getWnd('swWhsDocumentUcInventOrderViewWindow').show({
										ARMType: 'merch'
									});
								}
							}, {
								tooltip: langs('Инвентаризационные ведомости'),
								text: langs('Инвентаризационные ведомости'),
								iconCls : 'document16',
								disabled: false,
								handler: function() {
									var wnd = getWnd('swHeadNurseWorkPlaceWindow');
									var wndParams = {
										ARMType: 'merch',
										MedService_id: wnd.userMedStaffFact.MedService_id,
										Lpu_id: wnd.userMedStaffFact.Lpu_id,
										LpuSection_id: wnd.userMedStaffFact.LpuSection_id,
										LpuBuilding_id: wnd.userMedStaffFact.LpuBuilding_id
									};
									if(getGlobalOptions().orgtype != 'lpu' && wnd.userMedStaffFact.MedService_id > 0){
										Ext.Ajax.request({
											params:{MedService_id:wnd.userMedStaffFact.MedService_id},
											callback: function(options, success, response) {
												if (success) {
													var response_obj = Ext.util.JSON.decode(response.responseText);
													if(response_obj[0] && response_obj[0].OrgStruct_id) {
														wndParams.OrgStruct_id = response_obj[0].OrgStruct_id;
													}
												}
												getWnd('swWhsDocumentUcInventViewWindow').show(wndParams);
											},
											url: '/?c=MedService&m=loadEditForm'
										});
									} else {
										getWnd('swWhsDocumentUcInventViewWindow').show(wndParams);
									}
								}
							}]
						})
					}, {
						text: langs('Приход'),
						tooltip: langs('Приход'),
						iconCls: 'document16',
						handler: function() {
							var wnd = Ext.getCmp('swHeadNurseWorkPlaceWindow');
							wnd.openDocumentUcAddWindow(6); //6 - Приходная накладная
						}
					}, {
						text: langs('Ввод остатков'),
						tooltip: langs('Ввод остатков'),
						iconCls: 'document16',
						handler: function() {
							var wnd = Ext.getCmp('swHeadNurseWorkPlaceWindow');
							wnd.openDocumentUcAddWindow(3); //3 - Документ ввода остатков
						}
					}]
				})
			},
			action_Spr:
			{
				nn: 'action_Spr',
				tooltip: lang['spravochniki'],
				text: lang['spravochniki'],
				iconCls : 'book32',
				menuAlign: 'tr',
				menu: new Ext.menu.Menu({
					items: [
						{
							text: lang['spravochnik_kontragentyi'],
							tooltip: lang['spravochnik_kontragentyi'],
							iconCls: 'farm-partners16',
							handler: function()
							{
								getWnd('swContragentViewWindow').show();
							}
						},
						sw.Promed.Actions.swDrugDocumentSprAction,
						{
							text: getRLSTitle(),
							tooltip: getRLSTitle(),
							iconCls: 'rls16',
							handler: function()
							{
								getWnd('swRlsViewForm').show();
							},
							hidden: false
						}, {
							text: getMESAlias(),
							tooltip: lang['spravochnik'] + getMESAlias(),
							iconCls: 'spr-mes16',
							handler: function()
							{
								getWnd('swMesOldSearchWindow').show();
							},
							hidden: false // TODO: После тестирования доступ должен быть для всех
						}, {
							name: 'action_PriceJNVLP',
							text: lang['tsenyi_na_jnvlp'],
							iconCls : 'dlo16',
							handler: function() {
								getWnd('swJNVLPPriceViewWindow').show();
							}
						}, {
							name: 'action_DrugMarkup',
							text: lang['predelnyie_nadbavki_na_jnvlp'],
							iconCls : 'lpu-finans16',
							handler: function() {
								getWnd('swDrugMarkupViewWindow').show({readOnly: true});
							}
						}
					]
				})
			},
			action_JourNotice: {nn:'action_JourNotice', text:lang['jurnal_uvedomleniy'], tooltip: lang['otkryit_jurnal_uvedomleniy'], iconCls : 'notice32', handler: function() {getWnd('swMessagesViewWindow').show();}.createDelegate(this)},
			action_Reports:
			{
				nn: 'action_Report',
				tooltip: lang['prosmotr_otchetov'],
				text: lang['prosmotr_otchetov'],
				iconCls: 'report32',
				handler: function() {
					if (sw.codeInfo.loadEngineReports)
					{
						getWnd('swReportEndUserWindow').show({ReportParams: form.ReportParams, ARMType:'headnurse'});
					}
					else
					{
						getWnd('reports').load(
							{
								callback: function(success)
								{
									sw.codeInfo.loadEngineReports = success;
									// здесь можно проверять только успешную загрузку
									getWnd('swReportEndUserWindow').show({ReportParams: form.ReportParams, ARMType:'headnurse'});
								}
							});
					}
				}
			},
			action_JourLeave: {nn:'action_JourLeave', text:lang['jurnal_vyibyivshih'], tooltip: lang['otkryit_jurnal_vyibyivshih'], iconCls : 'mp-region32', handler: function() {getWnd('swJournalLeaveWindow').show({userMedStaffFact: this.userMedStaffFact});}.createDelegate(this)},

			// #175117. Кнопка для открытия формы "Журнал учета рабочего времени сотрудников":
			action_TimeJournal:
			{
				nn: 'action_TimeJournal',
				text: langs('Журнал учета рабочего времени сотрудников'),
				tooltip: langs('Открыть журнал учета рабочего времени сотрудников'),
				iconCls: 'report32',
				disabled: false,

				handler:
					function()
					{
						var cur = sw.Promed.MedStaffFactByUser.current;

						getWnd('swTimeJournalWindow').show(
							{
								ARMType: (cur ? cur.ARMType : undefined),
								MedStaffFact_id: (cur ? cur.MedStaffFact_id : undefined),
								Lpu_id: (cur ? cur.Lpu_id : undefined)
							});
					}
			},

			action_ScheduleMaster:
			{
				tooltip: langs('Работа с расписанием'),
				text: langs('Работа с расписанием'),
				iconCls: 'schedule32',
				hidden: !getRegionNick().inlist(['vologda', 'ufa']),
				handler: function() {
					var params = {
						LpuSection_id: form.userMedStaffFact.LpuSection_id,
						MedServiceType_SysNick: 'prock', // Процедурный кабинет
						MedServiceOnly: true // загружать только службы
					};
					getWnd('swScheduleEditMasterWindow').show(params);
				}
			},
			action_DutyScheduleMiddle:
				{
					tooltip: lang['duty_schedule_middle'],
					text: lang['duty_schedule'],
					iconCls: 'sched-16',
					handler: () => getWnd('swWorkGraphMiddleWindow').show({ userMedStaffFact: this.userMedStaffFact })
				}
		};
		// Копируем все действия для создания панели кнопок
		form.PanelActions = {};
		for(var key in configActions)
		{
			var iconCls = configActions[key].iconCls;//.replace(/16/g, '32');
			var z = Ext.applyIf({cls: 'x-btn-large', iconCls: iconCls, text: ''}, configActions[key]);
			this.PanelActions[key] = new Ext.Action(z);
		}

		var actions_list = ['action_JourEvnPrescr','action_Medication','action_CreateDoc','action_Spr','action_JourNotice','action_SkladMO','action_Reports','action_JourLeave', 'action_TimeJournal' /* 175117 */, 'action_ScheduleMaster', 'action_DutyScheduleMiddle'];

		// Создание кнопок для панели
		form.BtnActions = new Array();
		var i = 0;
		for(var key in form.PanelActions)
		{
			if (key.inlist(actions_list))
			{
				form.BtnActions.push(new Ext.Button(form.PanelActions[key]));
				i++;
			}
		}
		this.leftMenu = new Ext.Panel(
			{
				region: 'center',
				border: false,
				id: form.id + '_hhd',
				layout:'form',
				layoutConfig:
				{
					titleCollapse: true,
					animate: true,
					activeOnTop: false
				},
				items: form.BtnActions
			});
		this.leftPanel =
		{
			animCollapse: false,
			width: 60,
			minSize: 60,
			maxSize: 120,
			id: 'HNWPW_LeftPanel',
			region: 'west',
			floatable: false,
			collapsible: true,
			layoutConfig:
			{
				titleCollapse: true,
				animate: true,
				activeOnTop: false
			},
			listeners:
			{
				collapse: function()
				{
					return;
				},
				resize: function (p,nW, nH, oW, oH)
				{
					var el = null;
					el = form.findById(form.id + '_slid');
					if(el)
						el.setHeight(this.body.dom.clientHeight-42);
					return;
				}

			},
			border: true,
			title: ' ',
			items: [
				new Ext.Button(
					{
						cls:'upbuttonArr',
						iconCls:'uparrow',
						disabled: false,
						handler: function()
						{
							var el = form.findById(form.id + '_hhd');
							var d = el.body.dom;
							d.scrollTop -=38;
						}
					}),
				{
					border: false,
					layout:'border',
					id: form.id + '_slid',
					height:100,
					items:[this.leftMenu]
				},
				new Ext.Button(
					{
						cls:'upbuttonArr',
						iconCls:'downarrow',
						style:{width:'48px'},
						disabled: false,
						handler: function()
						{
							var el = form.findById(form.id + '_hhd');
							var d = el.body.dom;
							d.scrollTop +=38;

						}
					})
			]
		};

		this.DoctorToolbar = new Ext.Toolbar(
			{
				id: 'DoctorToolbar',
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
						this.formActions.next
					]
			});

		// Элементы верхней панели (период, поиск)
		this.TopPanel = new Ext.Panel(
			{
				region: 'north',
				frame: true,
				border: false,
				autoHeight: true,
				tbar: this.DoctorToolbar,
				items:
					[
						this.filtersPanel
					]
			});

		Ext.apply(this,
			{
				layout: 'border',
				items:
					[
						this.TopPanel,
						this.leftPanel,
						{
							layout: 'border',
							region: 'center',
							id: 'HNWPW_SchedulePanel',
							items:
								[
									this.Tree,
									this.RightPanel
								]
						}
					],
				buttons:
					[{
						text: '-'
					},
						HelpButton(this, TABINDEX_MPSCHED + 98),
						{
							iconCls: 'cancel16',
							text: BTN_FRMCLOSE,
							handler: function() {this.hide();}.createDelegate(this)
						}]
			});
		sw.Promed.swHeadNurseWorkPlaceWindow.superclass.initComponent.apply(this, arguments);
	}
});
