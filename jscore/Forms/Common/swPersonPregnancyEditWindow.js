/**
 * swPersonPregnancyEditWindow - окно редактирования сведений о беременности
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      	Common
 * @access       	public
 * @copyright		Copyright (c) 2016 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			10.03.2016
 */
/*NO PARSE JSON*/

sw.Promed.swPersonPregnancyEditWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'swPersonPregnancyEditWindow',
	maximizable: false,
	maximized: true,
	layout: 'border',

	listeners: {
		'beforehide': function(wnd) {
			var categories = wnd.WizardPanel.categories;
			var category = null;
			var index = -1;

			var tryHideWindow = function() {
				if (category = categories.itemAt(++index)) {
					category.cancelCategory(category, tryHideWindow);
				} else {
					wnd.allowHide = true;
					wnd.hide();
				}
			};

			if (wnd.allowHide) {
				return true;
			} else {
				tryHideWindow();
				return false;
			}
		}
	},

	printCard: function(blank) {
		var win = this;
		if(blank){
			var personDispID = 0;
			var record = 0;
		} else {
			var record = win.PersonRegister_id;
	        if (!record) {
	            Ext.Msg.alert(lang['oshibka'], 'Отсутствует идентификатор регистра');
	            return false;
	        }
			/*
			var personDispID = win.PersonDisp_id;
			if (!personDispID) {
				Ext.Msg.alert(langs('Ошибка'), 'Отсутствует идентификатор диспансерного учета');
				return false;
		}
			*/
		}
		
    	printBirt({
			'Report_FileName': 'han_ParturientCard_f111_u.rptdesign',
			'Report_Params': '&paramPersonRegister_id=' + record,
			'Report_Format': 'pdf'
		});
		/*
		printBirt({
			'Report_FileName': 'han_ParturientCard.rptdesign',
			'Report_Params': '&paramPersonDispBirth=' + personDispID,
			'Report_Format': 'pdf'
		});
		*/
	},

	printExchangeCard: function () {
		if (Ext.isEmpty(this.PersonRegister_id)) {
			return false;
		}

		printBirt({
			'Report_FileName': 'f113u.rptdesign',
			'Report_Params': '&paramPersonRegister=' + this.PersonRegister_id,
			'Report_Format': 'pdf'
		});
	},

	printResult: function() {
		var category = this.WizardPanel.getCurrentCategory();

		if (!category || category.name != 'Result' || Ext.isEmpty(category.BirthSpecStac_id) || Ext.isEmpty(this.PersonRegister_id)) {
			return false;
		}

		printBirt({
			'Report_FileName': 'PregnancyResult_print.rptdesign',
			'Report_Params': '&paramPersonRegister=' + this.PersonRegister_id,
			'Report_Format': 'pdf'
		});

		return true;
	},

	printScreen: function() {
		var category = this.WizardPanel.getCurrentCategory();

		if (!category || category.name != 'Screen' || Ext.isEmpty(category.PregnancyScreen_id)) {
			return false;
		}

		printBirt({
			'Report_FileName': 'PregnancyScreen_Print.rptdesign',
			'Report_Params': '&paramPregnancyScreen=' + category.PregnancyScreen_id,
			'Report_Format': 'pdf'
		});

		return true;
	},

	printAnketa: function() {
		if (Ext.isEmpty(this.PersonRegister_id)) {
			return false;
		}

		printBirt({
			'Report_FileName': 'PregnantProfile.rptdesign',
			'Report_Params': '&paramPersonRegister=' + this.PersonRegister_id,
			'Report_Format': 'pdf'
		});

		return true;
	},

	refreshInfoPanel: function() {
		var actionStyle = 'margin-right: 10px;font-size: 12px;color: #000079;text-decoration: underline;cursor: pointer;';

		var editHist = '<span style="'+actionStyle+'" onclick="getWnd(\'swPersonPregnancyEditWindow\').openPersonRegisterHistEditWindow()">Изменить параметры учета</span>';
		var showHistHint = '<span style="'+actionStyle+'" onclick="getWnd(\'swPersonPregnancyEditWindow\').showInfoHistHint(Ext.get(this))">История</span>';
		var showPrintMenu = '<span style="'+actionStyle+'" onclick="getWnd(\'swPersonPregnancyEditWindow\').showInfoPrintMenu(Ext.get(this))">Печать</span>';
		var PersonDispCard = '<span style="'+actionStyle+'" onclick="getWnd(\'swPersonPregnancyEditWindow\').openPersonDispEditWindow()">Карта диспансерного наблюдения</span>';

		var actions = (this.action == 'edit') ? [editHist] : [];
		actions.push(showHistHint, showPrintMenu, PersonDispCard);

		var tplArr = ['<p><table><tr><td width="700px">{Person_Fio}, {Person_BirthDay} г.р. </td><td width="350px"> Баллы перинатального риска: {PersonPregnancy_ObRisk} </td><td width="450px">  Степень перинатального риска при оценке в баллах: {RiskType_Name} </td></table></p>'];
		if (!Ext.isEmpty(this.PersonRegister_id)) {
			tplArr.push('<p><table><tr><td width="700px">На учете с {PersonRegister_setDate} в {Lpu_Nick}, врач {MedPersonal_Fio}, инд. карта №{PersonRegister_Code} </td><td width="350px"> Степень риска с учетом ключ. факт.: {RiskType_AName} </td><td width="450px"> МО родоразрешения: {MesLevel_Name}</td></table></p>');
		}
        if (!Ext.isEmpty(this.PersonRegister_id)) {
            tplArr.push('<p><table><tr><td width="700px"> </td><td width="350px"> </td><td width="450px">{Robson}</td></table></p>');
        }
		if (actions.length > 0) {
			tplArr.push('<p style="margin: 5px 0;">'+actions.join('')+'</p>');
		}

		var tpl = new Ext.Template(tplArr);

		Ext.Ajax.request({
			url: '/?c=PersonPregnancy&m=getPersonRegisterInfo',
			params: {
				Person_id: this.Person_id,
				PersonRegister_id: this.PersonRegister_id
			},
			success: function(response) {
				var response_obj = Ext.util.JSON.decode(response.responseText);
				if (response_obj.success) {
					tpl.overwrite(this.InfoPanel.body, response_obj);
					this.syncSize();
				}
			}.createDelegate(this),
			failure: function() {

			}
		});
	},

	openPersonRegisterHistEditWindow: function() {
		if (this.action != 'edit') return;

		var params = {
			PersonRegister_id: this.PersonRegister_id
		};
		params.callback = function() {
			this.refreshInfoPanel();
			this.callback();
		}.createDelegate(this);

		getWnd('swPersonRegisterHistEditWindow').show(params);
	},

	openPersonDispEditWindow: function() {
		var formParams = {},
			params = {},
			win = this,
			Anketa = win.WizardPanel.categories.items[0];
		win.showMessage = function () {
			sw.swMsg.show({
				buttons: Ext.Msg.YESNO,
				fn: function(buttonId, text, obj) {
					if ( buttonId === 'yes' ) {
						params.PersonPregnancy_id = this.TreePanel.nodeHash.Anketa.attributes.key;
						params.action = 'add';
						params.callback = function () {
							win.PersonDisp_id = this.FormPanel.getForm().findField('PersonDisp_id').getValue();
						};
						params.formParams = formParams;
						getWnd('swPersonDispEditWindow').show(params);
					}
				}.createDelegate(this),
				icon: Ext.MessageBox.QUESTION,
				msg: 'Создать карту диспансерного наблюдения?',
				title: lang['vopros']
			});
		};

		formParams.Person_id = this.Person_id;
		formParams.Server_id = this.Server_id;
		var loadMask = win.WizardPanel.categories.items[0].wizard.getLoadMask({msg: "Загрузка..."});
		loadMask.show();
		Ext.Ajax.request({
			params: {PersonRegister_id: win.PersonRegister_id,
				BirthSpecStac_id: win.TreePanel.nodeHash.Result.attributes.key},
			url: '/?c=PersonPregnancy&m=loadBirthSpecStac',
			success: function(response) {
				loadMask.hide();
				var response_obj = Ext.util.JSON.decode(response.responseText);
				if(response_obj[0].BirthSpecStac_OutcomDate){
					params.BirthSpecStac_OutcomDate = response_obj[0].BirthSpecStac_OutcomDate;
				}

				if(win.TreePanel.nodeHash.Anketa.attributes.key){
					if(win.PersonDisp_id){
						if(win.PersonDisp_Lpu_id && (win.Lpu_id != win.PersonDisp_Lpu_id)){
							Ext.Msg.alert(lang['soobschenie'], 'Пациент состоит на диспансерном учете в другой МО');
						}
						else{
							//дата исхода
							formParams.PersonDisp_id = win.PersonDisp_id;
							params.action = 'edit';
							params.formParams = formParams;
							getWnd('swPersonDispEditWindow').show(params);
						}
					}
					else{
						win.showMessage();
					}
				}
				else{
					if(Anketa.saveCategory(Anketa) === true){
						win.showMessage();
					}
				}
			},
			failure: function(response) {
				loadMask.hide();
			}
		});
	},

	showInfoHistHint: function(el) {
		var menu = this.InfoHistHint;
		menu.removeAll();

		if (Ext.isEmpty(this.PersonRegister_id)) {
			menu.addMenuItem({text: '<span style="color: gray;">Записи в истории отсутствуют</span>'});
			menu.show(el);
			return;
		}

		var getHistText = function(hist) {
			var tpl = '';
			if (Ext.isEmpty(hist.PersonRegisterHist_endDate)) {
				tpl = 'С {PersonRegisterHist_begDate}: в {Lpu_Nick}, врач {MedPersonal_Fio}, №{PersonRegisterHist_NumCard}';
			} else {
				tpl = 'С {PersonRegisterHist_begDate} по {PersonRegisterHist_endDate}: в {Lpu_Nick}, врач {MedPersonal_Fio}, №{PersonRegisterHist_NumCard}';
			}
			return new Ext.Template(tpl).apply(hist);
		};

		Ext.Ajax.request({
			url: '/?c=PersonRegister&m=loadPersonRegisterHistList',
			params: {PersonRegister_id: this.PersonRegister_id},
			success: function(response) {
				var response_obj = Ext.util.JSON.decode(response.responseText);
				if (Ext.isArray(response_obj)) {
					menu.removeAll();
					response_obj.forEach(function(hist) {
						menu.addMenuItem({text: getHistText(hist)});
					});
					if (menu.items.getCount() == 0) {
						menu.addMenuItem({text: '<span style="color: gray;">Записи в истории отсутствуют</span>'});
					}
					menu.show(el);
				}
			},
			callback: function() {

			}
		});
	},

	showInfoPrintMenu: function(el) {
		var wnd = this;
		var menu = this.InfoPrintMenu;

		var printCard = {
			text: 'Печать индивидуальной карты беременной',
			handler: function(){wnd.printCard()}
		};
		var printBlank = {
			text: 'Печать бланка индивидуальной карты беременной',
			handler: function(){wnd.printCard(true)}
		};
		var printExchangeCard = {
			text: 'Печать обменной карты родильного дома, родильного отделения больницы',
			handler: function(){wnd.printExchangeCard()}
		};

		var actions = [];
		if (this.action == 'add') {
			actions = [printBlank, printExchangeCard];
		} else {
			actions = [printCard, printBlank, printExchangeCard];
		}

		menu.removeAll();
		actions.forEach(function(action) {
			menu.addMenuItem(action);
		});
		menu.show(el);
	},

	setAction: function(action) {
		if (!String(action).inlist(['add','edit','view'])) {
			action = 'view';
		}
		this.action = action;

		switch(this.action) {
			case 'add':
				this.setTitle('Сведения о беременности: Добавление');
				this.enableEdit(true);
				break;

			case 'edit':
			case 'view':
				if (this.action == 'edit') {
					this.setTitle('Сведения о беременности: Редактирование');
					this.enableEdit(true);
				} else {
					this.setTitle('Сведения о беременности: Просмотр');
					this.enableEdit(false);
				}
				break;
		}

		this.WizardPanel.setReadOnly(this.action == 'view');
	},

	show: function() {
		sw.Promed.swPersonPregnancyEditWindow.superclass.show.apply(this, arguments);

		var wnd = this;
		this.allowHide = false;
		this.action = 'view';
		this.callback = Ext.emptyFn;
		this.Person_id = null;
		this.PersonRegister_id = null;
		this.Evn_id = null;
		this.userMedStaffFact = sw.Promed.MedStaffFactByUser.last;
		this.Server_id = this.userMedStaffFact.Lpu_id;
		this.Lpu_id = this.userMedStaffFact.Lpu_id;
		this.LpuSection_id = this.userMedStaffFact.LpuSection_id;
		this.MedPersonal_id = this.userMedStaffFact.MedPersonal_id;
		this.MedStaffFact_id = this.userMedStaffFact.MedStaffFact_id;
		this.PersonDisp_id = null;
		this.PersonPregnancy_id = null;

		//this.WizardPanel.resetCurrentCategory(true);
		this.WizardPanel.resetCategories(true);
		this.WizardPanel.setMaskEl(this.getEl());

		if (Ext.isEmpty(arguments[0].Person_id) && Ext.isEmpty(arguments[0].PersonRegister_id)) {
			sw.swMsg.alert(lang['soobschenie'], lang['otsutstvuyut_obyazatelnyie_parametryi']);
			this.hide();
			return;
		}
		this.Person_id = arguments[0].Person_id;
		if (arguments[0].userMedStaffFact) {
			this.userMedStaffFact = arguments[0].userMedStaffFact;
		}
		if (arguments[0].PersonRegister_id) {
			this.PersonRegister_id = arguments[0].PersonRegister_id;
		}
		if (arguments[0].Evn_id) {
			this.Evn_id = arguments[0].Evn_id;
		}
		if (arguments[0].Server_id) {
			this.Server_id = arguments[0].Server_id;
		}
		if (arguments[0].Lpu_id) {
			this.Lpu_id = arguments[0].Lpu_id;
		}
		if (arguments[0].LpuSection_id) {
			this.LpuSection_id = arguments[0].LpuSection_id;
		}
		if (arguments[0].MedStaffFact_id) {
			this.MedStaffFact_id = arguments[0].MedStaffFact_id;
		}
		if (arguments[0].MedPersonal_id) {
			this.MedPersonal_id = arguments[0].MedPersonal_id;
		}
		if (arguments[0].action) {
			this.action = arguments[0].action;
		}
		if (arguments[0].callback) {
			this.callback = arguments[0].callback;
		}
		if (arguments[0].PersonDisp_id) {
			this.PersonDisp_id = arguments[0].PersonDisp_id;
			Ext.Ajax.request({
				params: {PersonDisp_id: this.PersonDisp_id},
				url: '/?c=PersonDisp&m=loadPersonDispEditForm',
				success: function(response) {
					var response_obj = Ext.util.JSON.decode(response.responseText);
					wnd.PersonDisp_Lpu_id = response_obj[0].Lpu_id;
				}
			});
		}
		if (arguments[0].PersonDisp_id) {
			this.PersonDisp_id = arguments[0].PersonDisp_id;
		}
		if (arguments[0].PersonPregnancy_id) {
			this.PersonPregnancy_id = arguments[0].PersonPregnancy_id;
		}
		if (this.action == 'add' && !Ext.isEmpty(this.PersonRegister_id)) {
			this.action = 'view';
		}

		this.refreshInfoPanel();

		this.WizardPanel.init();
		this.WizardPanel.getCategory('Anketa').loadPersonInfo();

		var node = this.TreePanel.getRootNode();
		this.TreePanel.getLoader().baseParams.PersonRegister_id = this.PersonRegister_id;
		this.TreePanel.getLoader().load(node, function(){node.firstChild.expand()});
		this.setAction(this.action);
	},

	initComponent: function() {
		var wnd = this;

		this.InfoHistHint = new Ext.menu.Menu();

		this.InfoPrintMenu = new Ext.menu.Menu();

		this.InfoPanel = new Ext.Panel({
			style: 'font-size: 12px;',
			html: ''
		});

		this.TreePanel = new Ext.tree.TreePanel({
			split: true,
			region: 'west',
			autoScroll: true,
			width: 220,
			onSelectNode: function(node, e) {
				if (e && e.getTarget('.link', this.TreePanel.body)) {
					e.stopEvent();
					return;
				}

				this.WizardPanel.resetCurrentCategory();

				if (Ext.isEmpty(this.PersonRegister_id) || (Ext.isEmpty(node.attributes.key) && !node.attributes.grid)) {
					return;
				}

				var params = {};
				switch(node.attributes.object) {
					case 'Anketa':
						params.PersonPregnancy_id = node.attributes.key;
						this.WizardPanel.getCategory('Anketa').loadParams = params;
						this.WizardPanel.getCategory('Anketa').selectPage(0);
						break;
					case 'AnketaCommonData':
						params.PersonPregnancy_id = node.attributes.key;
						this.WizardPanel.getCategory('Anketa').loadParams = params;
						this.WizardPanel.getCategory('Anketa').selectPage(0);
						break;
					case 'AnketaFatherData':
						params.PersonPregnancy_id = node.attributes.key;
						this.WizardPanel.getCategory('Anketa').loadParams = params;
						this.WizardPanel.getCategory('Anketa').selectPage(1);
						break;
					case 'AnketaAnamnesData':
						params.PersonPregnancy_id = node.attributes.key;
						this.WizardPanel.getCategory('Anketa').loadParams = params;
						this.WizardPanel.getCategory('Anketa').selectPage(2);
						break;
					case 'AnketaExtragenitalDisease':
						params.PersonPregnancy_id = node.attributes.key;
						this.WizardPanel.getCategory('Anketa').loadParams = params;
						this.WizardPanel.getCategory('Anketa').selectPage(3);
						break;
					case 'ScreenList':
						break;
					case 'Screen':
						params.PregnancyScreen_id = node.attributes.key;
						this.WizardPanel.getCategory('Screen').loadParams = params;
						this.WizardPanel.getCategory('Screen').selectPage(0);
						break;
					case 'EvnList':
						this.WizardPanel.getCategory('EvnList').selectPage(0);
						break;
					case 'ConsultationList':
						this.WizardPanel.getCategory('ConsultationList').selectPage(0);
						break;
					case 'ResearchList':
						this.WizardPanel.getCategory('ResearchList').selectPage(0);
						break;
					case 'Certificate':
						params.BirthCertificate_id = node.attributes.key;
						this.WizardPanel.getCategory('Certificate').loadParams = params;
						this.WizardPanel.getCategory('Certificate').selectPage(0);
						break;
					case 'Result':
						params.BirthSpecStac_id = node.attributes.key;
						this.WizardPanel.getCategory('Result').loadParams = params;
						this.WizardPanel.getCategory('Result').selectPage(0);
						break;
					case 'DeathMother':
						params.DeathMother_id = node.attributes.key;
						this.WizardPanel.getCategory('DeathMother').loadParams = params;
						this.WizardPanel.getCategory('DeathMother').selectPage(0);
						break;
				}

				var category = this.WizardPanel.getCurrentCategory();
				var page = this.WizardPanel.getCurrentPage();

				if (page) {
					category.setReadOnly(this.action == 'view' || node.attributes.readOnly);
					category.moveToPage(page, this.WizardPanel.afterPageChange);
				} else {
					this.WizardPanel.afterPageChange();
				}
			}.createDelegate(this),
			keys: [{
				key: Ext.EventObject.ENTER,
				fn: function(e) {
					var node = this.TreePanel.getSelectionModel().getSelectedNode();
					if ( node.id == 'root' )
					{
						if ( node.isExpanded() )
							node.collapse();
						else
							node.expand();
						return;
					}
					if ( node.isExpandable() )
					{
						if ( node.isExpanded() )
							node.collapse();
						else
							node.expand();
					}

					this.TreePanel.onSelectNode(node, e);
				}.createDelegate(this),
				stopEvent: true
			}],
			root: {
				id: 'root'
			},
			rootVisible: false,
			enableKeyEvents: true,
			listeners: {
				'beforeload': function(node) {
					var wizardId = this.WizardPanel.getId();

					this.TreePanel.getLoader().baseParams.allowCreateButton = (this.action != 'view');
					this.TreePanel.getLoader().baseParams.allowDeleteButton = (this.action != 'view');
					this.TreePanel.getLoader().baseParams.PersonRegister_id = this.PersonRegister_id || null;
					this.TreePanel.getLoader().baseParams.object = node.attributes.object || null;
					this.TreePanel.getLoader().baseParams.createCategoryMethod = "Ext.getCmp('"+wizardId+"').createCategoryController";
					this.TreePanel.getLoader().baseParams.deleteCategoryMethod = "Ext.getCmp('"+wizardId+"').deleteCategoryController";
				}.createDelegate(this),
				'click': function(node, e) {
					this.TreePanel.onSelectNode(node, e);
				}.createDelegate(this),
				'bodyresize': function() {
					var page = this.WizardPanel.getCurrentPage();
					if (page) setTimeout(function() {page.doLayout()}, 1);
				}.createDelegate(this)
			},
			loader: new Ext.tree.TreeLoader({
				url: '/?c=PersonPregnancy&m=loadPersonPregnancyTree'
			})
		});

		this.WizardPanel = new sw.Promed.PersonPregnancy.WizardFrame({
			region: 'center',
			inputData: new sw.Promed.PersonPregnancy.InputData({
				fn: function() {
					var personInfoPanel = this.WizardPanel.getCategory('Anketa').PersonInfoPanel;
					return {
						Person_id: wnd.Person_id,
						PersonRegister_id: wnd.PersonRegister_id,
						Person_SurName: personInfoPanel.getFieldValue('Person_Surname'),
						Person_FirName: personInfoPanel.getFieldValue('Person_Firname'),
						Person_SecName: personInfoPanel.getFieldValue('Person_Secname'),
						Evn_id: wnd.Evn_id,
						Server_id: wnd.Server_id,
						Lpu_id: wnd.Lpu_id,
						LpuSection_id: wnd.LpuSection_id,
						MedStaffFact_id: wnd.MedStaffFact_id,
						MedPersonal_id: wnd.MedPersonal_id,
						userMedStaffFact: wnd.userMedStaffFact
					};
				}.createDelegate(this)
			}),
			printConfig: {
				Anketa: 
				[{
						tooltip: 'Anketa',
						text: 'Печать анкеты',
						handler: function() {
							printBirt({
								'Report_FileName': 'PregnantProfile.rptdesign',
								'Report_Params': '&paramPersonRegister=' + wnd.PersonRegister_id,
								'Report_Format': 'pdf'
							});
						}
					},
					{
						tooltip: 'FormPregnancy',
						text: 'Печать формы "Сведения о беременности(полная форма)"',
						handler: function() {
							printBirt({
								'Report_FileName': 'PregnancyFullResult_print.rptdesign',
								'Report_Params': '&paramPersonRegister=' + wnd.PersonRegister_id,
								'Report_Format': 'pdf'
							});
						}
					},
					{
						tooltip: 'FormPregnancy',
						text: 'Печать формы "Сведения о беременности(сокращенная форма)"',
						handler: function() {
							printBirt({
								'Report_FileName': 'PregnancyFullResultSocr_print.rptdesign',
								'Report_Params': '&paramPersonRegister=' + wnd.PersonRegister_id,
								'Report_Format': 'pdf'
							});
						}
					}
				],				
				Screen: 
				[{
						tooltip: 'Screen',
						text: 'Печать скриннинга',
						handler: function() {
							printBirt({
								'Report_FileName': 'PregnancyScreen_Print.rptdesign',
								'Report_Params': '&paramPregnancyScreen=' + wnd.WizardPanel.categories.items[1].PregnancyScreen_id,
								'Report_Format': 'pdf'
							});
						}
					},
					{
						tooltip: 'FormPregnancy',
						text: 'Печать формы "Сведения о беременности(полная форма)"',
						handler: function() {
							printBirt({
								'Report_FileName': 'PregnancyFullResult_print.rptdesign',
								'Report_Params': '&paramPersonRegister=' + wnd.PersonRegister_id,
								'Report_Format': 'pdf'
							});
						}
					},
					{
						tooltip: 'FormPregnancy',
						text: 'Печать формы "Сведения о беременности(сокращенная форма)"',
						handler: function() {
							printBirt({
								'Report_FileName': 'PregnancyFullResultSocr_print.rptdesign',
								'Report_Params': '&paramPersonRegister=' + wnd.PersonRegister_id,
								'Report_Format': 'pdf'
							});
						}
					}					
				],
				Result: wnd.printScreen.bind(wnd)
			},
			afterSaveCategory: function(category) {
				var values = category.getForm().getValues();
				var printButton = category.wizard.PrintButton;

				if (category.name == 'Anketa') {
					wnd.PersonRegister_id = values.PersonRegister_id;
					if (wnd.action == 'add') {
						wnd.setAction('edit');
					}
				}
				if (category.name == 'Result') {
					category.needDeleteOnCancel = false;
					category.AddedPersonNewBorn_ids = [];
					category.AddedChildDeath_ids = [];
					category.ChildDeathGridPanel.reloadData();
				}

				switch(category.name) {
					case 'Anketa': printButton.setVisible(!Ext.isEmpty(values.PersonRegister_id));break;
					case 'Screen': printButton.setVisible(!Ext.isEmpty(values.PregnancyScreen_id));break;
					case 'Result': printButton.setVisible(!Ext.isEmpty(values.BirthSpecStac));break;
				}

				var node = wnd.TreePanel.getRootNode();
				wnd.TreePanel.getLoader().baseParams.PersonRegister_id = wnd.PersonRegister_id;
				wnd.TreePanel.getLoader().load(node, function(){node.firstChild.expand()});
				wnd.refreshInfoPanel();
				wnd.callback();
			},
			afterDeleteCategory: function(category, id, response) {
				var values = category.getForm().getValues();

				var node = wnd.TreePanel.getRootNode();
				if (response.deletedObjects && response.deletedObjects.PersonRegister_id == wnd.PersonRegister_id) {
					wnd.PersonRegister_id = null;
					delete wnd.TreePanel.getLoader().baseParams.PersonRegister_id;
					wnd.setAction('add');
				} else {
					wnd.TreePanel.getLoader().baseParams.PersonRegister_id = wnd.PersonRegister_id;
				}
				wnd.TreePanel.getLoader().load(node, function(){node.firstChild.expand()});
				wnd.callback();
			},
			cancelCategory: function(category, onCancel) {
				var categoryData = category.getCategoryData(category);	//Данные, полученные при загрузке раздела
				var doings = new sw.Promed.Doings();
				if (category.name == 'Result') {
					if (category.AddedPersonNewBorn_ids.length > 0 || category.AddedChildDeath_ids.length > 0) {
						var childGrid = category.ChildGridPanel.getGrid();
						var childDeathGrid = category.ChildDeathGridPanel.getGrid();
						var allowDeleteChildren = true;

						childGrid.getStore().each(function(rec) {
							if (rec.get('PersonNewBorn_id').inlist(category.AddedPersonNewBorn_ids) &&
								(!Ext.isEmpty(rec.get('ChildEvnPS_id')) || !Ext.isEmpty(rec.get('BirthSvid_id')) || !Ext.isEmpty(rec.get('PntDeathSvid_id')))
							) {
								allowDeleteChildren = false;
								return false;
							}
						});
						childDeathGrid.getStore().each(function(rec) {
							if (rec.get('ChildDeath_id').inlist(category.AddedChildDeath_ids) && !Ext.isEmpty(rec.get('PntDeathSvid_id'))) {
								allowDeleteChildren = false;
								return false;
							}
						});

						if (!allowDeleteChildren) {
							sw.swMsg.alert(lang['soobschenie'], 'Для отмены исхода беременности у детей не должно быть случаев лечения, данных наблюдений и мед. свидетельств.');
							return false;
						}
					}
					if (category.needDeleteOnCancel) {
						var loadMask = category.wizard.getLoadMask({msg: "Подождите, идет отмена добавления исхода..."});
						loadMask.show();

						doings.start('deleteBirthSpecStac');
						Ext.Ajax.request({
							params: {BirthSpecStac_id: category.BirthSpecStac_id},
							url: '/?c=PersonPregnancy&m=deleteBirthSpecStac',
							success: function(response) {
								loadMask.hide();

								if (category.wizard.getCurrentCategory() == category) {
									category.wizard.resetCurrentCategory(true);
								} else {
									category.reset(true);
								}

								var response_obj = Ext.util.JSON.decode(response.responseText);
								if (response_obj.success) {
									category.needDeleteOnCancel = false;
									category.afterDeleteCategory(category, id, response_obj);
								}
							},
							failure: function(response) {
								loadMask.hide();
							},
						});
					} else if (category.AddedPersonNewBorn_ids.length > 0) {
						var params = {
							PersonNewBorn_ids: Ext.util.JSON.encode(category.AddedPersonNewBorn_ids)
						};

						var loadMask = category.wizard.getLoadMask({msg: "Подождите, идет отмена добавления детей..."});
						loadMask.show();

						doings.start('deleteChildren');
						Ext.Ajax.request({
							url: '/?c=BirthSpecStac&m=deleteChildren',
							params: params,
							success: function(response) {
								loadMask.hide();
								var response_obj = Ext.util.JSON.decode(response.responseText);

								if (response_obj.success) {
									doings.finish('deleteChildren')
								}
							},
							failure: function(response) {
								loadMask.hide();
							}
						});
					}
				}
				doings.doLater('onCancel', onCancel);
			},
			afterPageChange: function() {
				var printButton = wnd.WizardPanel.PrintButton;
				var category = wnd.WizardPanel.getCurrentCategory();
				if (!category)
					return;

				var values = category.getForm().getValues();

				switch(category.name) {
					case 'Anketa':
						printButton.setVisible(!Ext.isEmpty(values.PersonRegister_id));
						break;
					case 'Screen':
						printButton.setVisible(!Ext.isEmpty(values.PregnancyScreen_id));
						break;
					case 'Result':
						printButton.setVisible(!Ext.isEmpty(values.BirthSpecStac));
						break;
				}
				
                var nextButton = wnd.WizardPanel.NextButton;
                var saveButton = wnd.WizardPanel.SaveButton;

                //gaf 08022018
                //находим текущую страницу, проверяем КЛЮЧ, проверяем доступность элемента последнего
                var base_form = category.getForm();

                if (typeof category.AnketaFactory != 'undefined' && getGlobalOptions().check_fullpregnancyanketa_allow&&getGlobalOptions().check_fullpregnancyanketa_allow=='1'){
                    if (typeof category.getCurrentPage().items.items[0].QuestionType_Code == 'undefined'){
						
						//поиск кнопки "присутствует" с кодом 614
						//в зависимости от статуса кнопки открываем кнопки Далее Сохранить
						var array_614 = Ext.getCmp('swPersonPregnancyEditWindow').formPanels[0].find("name","QuestionType_614");
						if (array_614.length == 1 && typeof Ext.getCmp(array_614[0].id) != 'undefined' &&  Ext.getCmp(array_614[0].id).disabled) {
                            nextButton.setDisabled(true);
                            saveButton.setDisabled(true);
			}
                    }
					var array_616 = Ext.getCmp('swPersonPregnancyEditWindow').formPanels[0].find("name","QuestionType_616");
					var array_617 = Ext.getCmp('swPersonPregnancyEditWindow').formPanels[0].find("name","QuestionType_617");
					var array_618 = Ext.getCmp('swPersonPregnancyEditWindow').formPanels[0].find("name","QuestionType_618");
					var array_619 = Ext.getCmp('swPersonPregnancyEditWindow').formPanels[0].find("name","QuestionType_619");
                    if (category.getCurrentPage().items.items[0].QuestionType_Code == 191){
                        var oobj = base_form.findField('QuestionType_195');
						if (array_616.length == 1 && typeof Ext.getCmp(array_616[0].id) != 'undefined' &&  Ext.getCmp(array_616[0].id).disabled) {
							Ext.getCmp(array_616[0].id).setDisabled(false);
							Ext.getCmp(array_617[0].id).setDisabled(false);
							Ext.getCmp(array_618[0].id).setDisabled(false);
							Ext.getCmp(array_619[0].id).setDisabled(false);
							
                        }
                    }
                    if (category.getCurrentPage().items.items[0].QuestionType_Code == 203){
                        var oobj = base_form.findField('QuestionType_209');
						//поиск кнопки "присутствует" с кодом 620,640
						//в зависимости от статуса кнопки открываем кнопки Далее Сохранить
						var array_620 = Ext.getCmp('swPersonPregnancyEditWindow').formPanels[0].find("name","QuestionType_620");
						var array_640 = Ext.getCmp('swPersonPregnancyEditWindow').formPanels[0].find("name","QuestionType_640");
						if (array_620.length == 1 && typeof Ext.getCmp(array_620[0].id) != 'undefined' &&  Ext.getCmp(array_620[0].id).disabled) {
							Ext.getCmp(array_620[0].id).setDisabled(false);
							Ext.getCmp(array_640[0].id).setDisabled(false);
                            nextButton.setDisabled(true);
                        }                                
                    }     
                    if (category.getCurrentPage().items.items[0].QuestionType_Code == 261){
                        var oobj = base_form.findField('QuestionType_263');
						//поиск кнопки "присутствует" с кодом 627,647
						//в зависимости от статуса кнопки открываем кнопки Далее Сохранить
						var array_627 = Ext.getCmp('swPersonPregnancyEditWindow').formPanels[0].find("name","QuestionType_627");
						var array_647 = Ext.getCmp('swPersonPregnancyEditWindow').formPanels[0].find("name","QuestionType_647");
						if (array_627.length == 1 && typeof Ext.getCmp(array_627[0].id) != 'undefined' &&  Ext.getCmp(array_627[0].id).disabled) {
							Ext.getCmp(array_627[0].id).setDisabled(false);
							Ext.getCmp(array_647[0].id).setDisabled(false);
                        }
                    }                                    
                }      								
			}
		});

		Ext.apply(this,{
			buttons: [
				{
					text: '-'
				},
				HelpButton(this),
				{
					handler: function()
					{
						wnd.callback();
						this.hide();
					}.createDelegate(this),
					iconCls: 'cancel16',
					text: BTN_FRMCLOSE
				}
			],
			items: [
				{
					region: 'north',
					autoHeight: true,
					frame: true,
					defaults: {
						border: false,
						bodyStyle: 'background: #DFE8F6;'
					},
					items: [this.InfoPanel/*, this.InfoActionsPanel*/]
				},
				{
					region: 'center',
					layout: 'border',
					items: [this.TreePanel, this.WizardPanel]
				}
			]
		});

		sw.Promed.swPersonPregnancyEditWindow.superclass.initComponent.apply(this, arguments);
	}
});