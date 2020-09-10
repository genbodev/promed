/**
 * swPersonSearchWindow4DrugRequest - окно окно поиска людей для заявок
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      DLO
 * @access       public
 * @copyright    Copyright (c) 2009 Swan Ltd.
 * @author       Pshenicyn Ivan aka IVP (ipshon@rambler.ru)
 * @version      10.03.2009
 */

sw.Promed.swPersonSearchWindow4DrugRequest = Ext.extend(sw.Promed.BaseForm, {
	/* свойства */

	// allowDuplicateOpening: true,
	buttonAlign: 'left',
	closeAction: 'hide',
	draggable: true,
	formParams: null,
	height: 600,
	id: 'swPersonSearchWindow4DrugRequest',
	layout: 'border',
	listeners: {
		'show': function () {
			var tooltipElements = [
				'PersonSurName_SurName',
				'PersonFirName_FirName',
				'PersonSecName_SecName'
			];

			Ext.each(tooltipElements, function () {
				Ext.select("[name=" + this + "]").on('keydown', function (e, el) {
					var keyCode = e.getCharCode();

					if (keyCode == 173 || keyCode == 189) {
						$(this).hint({
							title: 'возможен ввод символов на доп. клавиатуре',
							text: 'Тире (длинное) —	&mdash;	Alt + 0151 Короткое (среднее) тире - &ndash; Alt + 0150',
							delay: 2000
						});
					}
				});
			});
		},
		'beforehide': function () {
			sw.Applets.commonReader.stopReaders();
		},
		'hide': function () {
			this.onWinClose();
		}
	},
	modal: true,
	plain: true,
	resizable: false,
	searchInProgress: false,
	searchWindowOpenMode: null,
	title: WND_PERS_SEARCH,
	width: 900,
	/* методы */

	doSearch: function (params, searchCallBack) {
		if (this.searchInProgress) {
			log(langs('Поиск уже выполняется!'));
			return false;
		} else {
			this.searchInProgress = true;
		}
		var win = this;
		if (params && params['soc_card_id'])
			var soc_card_id = params['soc_card_id'];
		var grid = this.PersonSearchViewFrame.getGrid();
		var form = this.FilterPanel;
		var vals = form.getForm().getValues();
		var flag = true;
		//for ( value in vals )
		//{
		//if ( vals[value] != "" )
		//flag = false;
		//}
		var is_ekb = (getRegionNick() == 'ekb');
		var is_kz = (getRegionNick() == 'kz');
		if (
				form.getForm().findField('Person_Snils').getValue() != ''
				|| (is_ekb && form.getForm().findField('PersonCard_id').getValue() != '')
				|| (is_kz && form.getForm().findField('Person_Inn').getValue() != '')
				|| form.getForm().findField('PersonCard_Code').getValue() != ''
				|| form.getForm().findField('EvnPS_NumCard').getValue() != ''
				|| (form.getForm().findField('Polis_Num').getValue() != '')
				|| form.getForm().findField('Polis_EdNum').getValue() != ''
				|| (form.getForm().findField('EvnUdost_Ser').getValue() != '' && form.getForm().findField('EvnUdost_Num').getValue() != '')
				|| (form.getForm().findField('Person_id').getValue() != '')
				|| (form.getForm().findField('PersonFirName_FirName').getValue() != '' && form.getForm().findField('PersonSecName_SecName').getValue() != '' && form.getForm().findField('PersonBirthDay_BirthDay').getValue() != '')
				)
			flag = false;

		if ((flag && form.getForm().findField('PersonSurName_SurName').getValue() == '') && !soc_card_id && win.searchList != 1) {
			var win = this;

			Ext.Msg.alert("Сообщение", "Не заполнены обязательные поля. Возможные варианты поиска:<br/>" +
					(isAdmin || isLpuAdmin() ? "Поиск по Person_id.<br/>" : "") +
					"Поиск по фамилии.<br/>" +
					"Поиск по совпадению имени, отчества и даты рождения.<br/>" +
					"Поиск по точному совпадению СНИЛС.<br/>" +
					(is_kz ? "Поиск по точному совпадению ИИН.<br/>" : "") +
					(is_ekb ? "Поиск по PersonCard_id.<br/>" : "") +
					"Поиск по точному совпадению номера амбулаторной карты.<br/>" +
					"Поиск по точному совпадению номера КВС.<br/>" +
					(!is_kz ? "Поиск по точному совпадению номера полиса.<br/>" : "") +
					"Поиск по точному совпадению ЕНП.<br/>" +
					"Поиск по точному совпадению серии и номера удостоверения льготника.<br/>" +
					"Поиск по совпадению имени, отчества и даты рождения.<br/>"
					, function () {
						form.getForm().findField('PersonSurName_SurName').focus(true, 100);
					});
			win.searchInProgress = false;
			return false;
		}
		grid.getStore().removeAll();
		if (win.searchList == 1) {
			var idx = this.PersonSearchViewFrame.getColumnModel().findColumnIndex('check');
			Ext.getCmp('swPersonSearchWindow4DrugRequest').PersonSearchViewFrame.getGrid().getColumnModel().setColumnHeader(idx, '<input type="checkbox" id="PSPCAW_checkAll" onClick="getWnd(\'swPersonSearchWindow4DrugRequest\').checkAll(this.checked, this);">');
		}

		win.getLoadMask(SEARCH_WAIT).show();
		var wnd = this;
		if (soc_card_id) {
			var params = {
				soc_card_id: soc_card_id/*,
				 PersonSurName_SurName: '%'*/
			};
			var baseParams = params;
		} else {
			var params = form.getForm().getValues();
			var baseParams = form.getForm().getValues();
		}
		params.searchMode = this.searchMode;
		baseParams.searchMode = this.searchMode;
		if (this.Year) {
			params.Year = this.Year;
			baseParams.Year = this.Year;
		}
		if (this.PersonRegisterType_id) {
			params.PersonRegisterType_id = this.PersonRegisterType_id;
			baseParams.PersonRegisterType_id = this.PersonRegisterType_id;
		}
		if (this.DrugRequestPeriod_id) {
			params.DrugRequestPeriod_id = this.DrugRequestPeriod_id;
			baseParams.DrugRequestPeriod_id = this.DrugRequestPeriod_id;
		}
		if (this.LpuRegion_id) {
			params.LpuRegion_id = this.LpuRegion_id;
			baseParams.LpuRegion_id = this.LpuRegion_id;
		}
		if (this.PersonRefuse_IsRefuse) {
			params.PersonRefuse_IsRefuse = this.PersonRefuse_IsRefuse;
			baseParams.PersonRefuse_IsRefuse = this.PersonRefuse_IsRefuse;
		}
		if (this.getPersonWorkFields == true) {
			params.getPersonWorkFields = 1;
			baseParams.getPersonWorkFields = 1;
		}

		//BOB - 21.03.2017
		if (this.Person_ids) {
			params.Person_ids = this.Person_ids;
			baseParams.Person_ids = this.Person_ids;
		}
		//BOB - 21.03.2017

		grid.getStore().baseParams = baseParams;
		params.start = 0;
		params.limit = 100;
		params.armMode = win.armMode;
		this.isSearched = true;
		grid.getStore().load({
			params: params,
			callback: function (r) {
				win.searchInProgress = false;
				win.getLoadMask().hide();
				if (r.length > 0) {
					var len = r.length;
					grid.getView().focusRow(0);
					grid.getSelectionModel().selectFirstRow();
				}

				if (searchCallBack && typeof searchCallBack == 'function') {
					searchCallBack();
				}
			}
		});
	},
	getDataFromBarcode: function (barcodeData, person_data) {
		// sw.Applets.BarcodeScaner.stopBarcodeScaner();

		if (person_data.Person_id) {
			this.onPersonSelect({
				Person_id: person_data.Person_id
				, PersonEvn_id: person_data.PersonEvn_id
				, Server_id: person_data.Server_id
				, onHide: function () {
					// sw.Applets.BarcodeScaner.startBarcodeScaner();
				}
			});
		}
	},
	getDataFromBdz: function (bdzData, person_data) {
		var form = this.FilterPanel.getForm();

		if (bdzData.success) {
			form.findField('PersonFirName_FirName').setValue(bdzData.firName);
			form.findField('PersonSecName_SecName').setValue(bdzData.secName);
			form.findField('PersonSurName_SurName').setValue(bdzData.surName);
			form.findField('PersonBirthDay_BirthDay').setValue(bdzData.birthDay);

			if (getRegionNick().inlist(['ufa'])) {
				form.findField('Polis_Num').setValue(bdzData.polisNum);
			} else {
				form.findField('Polis_EdNum').setValue(bdzData.polisNum);
			}

			var callback = function () {
				var grid = this.PersonSearchViewFrame.getGrid();

				if (grid.getStore().getCount() == 1 && grid.getStore().getAt(0).get('Person_id')) {
					this.onOkButtonClick();
				}
			}.createDelegate(this);

			this.doSearch(false, callback);
		}
	},
	getDataFromIEMK: function (type) {
		// получение данных из ИЭМК
		var win = this;

		var grid = this.PersonSearchViewFrame.getGrid();
		var selected_record = grid.getSelectionModel().getSelected();

		if (selected_record && selected_record.get('Person_id')) {
			win.getLoadMask('Получение данных из ИЭМК').show();
			Ext.Ajax.request({
				url: '/?c=MisRb&m=getPersonFromIEMK',
				params: {
					Person_id: selected_record.get('Person_id'),
					type: type
				},
				callback: function (options, success, response) {
					win.getLoadMask().hide();
					if (success) {
						var result = Ext.util.JSON.decode(response.responseText);
						// todo обработка
					}
				}.createDelegate(this)
			});
		}
	},
	getDataFromUec: function (uecData, person_data) {
		var form = this.FilterPanel.getForm();

		if (uecData.success) {
			form.findField('PersonFirName_FirName').setValue(uecData.firName);
			form.findField('PersonSecName_SecName').setValue(uecData.secName);
			form.findField('PersonSurName_SurName').setValue(uecData.surName);
			form.findField('PersonBirthDay_BirthDay').setValue(uecData.birthDay);

			if (getRegionNick().inlist(['ufa'])) {
				form.findField('Polis_Num').setValue(uecData.polisNum);
			} else {
				form.findField('Polis_EdNum').setValue(uecData.polisNum);
			}

			var callback = function () {
				var grid = this.PersonSearchViewFrame.getGrid();

				if (grid.getStore().getCount() == 1 && grid.getStore().getAt(0).get('Person_id')) {
					this.onOkButtonClick();
				}
			}.createDelegate(this);

			this.doSearch(false, callback);
		}
	},
	/**
	 * Callback функция вызываемая для возвращения данных пользователя.
	 *
	 * Я немного дополнил ее. При добавлении и редактировании человека набор,
	 * возвращаемых данных, существенно отличался. При редактировании данные,
	 * беруться из грида и содержат более полные сведения, в отличии от данных,
	 * возвращаемых при добавлении. Поэтому при добавлении я сделал вызов функции
	 * после загрузки грида.
	 *
	 * @param object callback_data
	 * @returns {undefined}
	 */
	onOkButtonClick: function (callback_data) {
		sw.Applets.commonReader.stopReaders();

		var data_to_return = {},
				apply_grid_selected = true,
				grid = this.PersonSearchViewFrame.getGrid();

		// callback_data передается только при добавлении
		if (callback_data && callback_data.Person_id > 0) {
			Ext.apply(data_to_return, callback_data.PersonData);
			data_to_return.afterAdd = true;

			// неплохо бы разработчикам начать оставлять описание
			// непонятно что делает эта функция
			data_to_return.onHide = function () {
				var index = grid.getStore().findBy(function (rec) {
					return rec.get('Person_id') == callback_data.Person_id;
				});
				grid.focus();
				grid.getView().focusRow(index);
				grid.getSelectionModel().selectRow(index);
				sw.Applets.commonReader.startReaders();
			}

			// При добавлении человека, запускается поиск по некоторым указанным
			// параметрам, но он не обеспечивает 100% попадания, поэтому в гриде
			// может быть более одной записи и выбранной может оказаться любая,
			// поэтому нам надо найти и выбрать запись которая будет соответстовать
			// на 100%

			var person_index = grid.getStore().findBy(function (rec) {
				return rec.get('Person_id') == callback_data.Person_id;
			});
			// В гриде нашлась нужная запись?
			if (person_index != -1) {
				// ... выбираем ее
				grid.getSelectionModel().selectRow(person_index);
			} else {
				// ... иначе не используем данные из грида
				apply_grid_selected = false;
			}
		}

		if (apply_grid_selected) {
			var selected = grid.getSelectionModel().getSelected();

			// Добавим только недостающие данные
			Ext.applyIf(data_to_return, selected.data);

			// Если запись была просто выбрана в гриде, в ней будет не хватать ключей
			Ext.applyIf(data_to_return, {
				Person_Birthday: selected.get('PersonBirthDay_BirthDay'),
				Person_Firname: selected.get('PersonFirName_FirName'),
				Person_Secname: selected.get('PersonSecName_SecName'),
				Person_Surname: selected.get('PersonSurName_SurName')
			});

			// неплохо бы разработчикам начать оставлять описание
			// непонятно что делает эта функция
			data_to_return.onHide = function () {
				var index = grid.getStore().findBy(function (rec) {

					return rec.get('Person_id') == selected.data.Person_id;
				});

				grid.focus();

				grid.getView().focusRow(index);
				grid.getSelectionModel().selectRow(index);

				sw.Applets.commonReader.startReaders();
			}
		}

		log({
			swPersonSearchWindow4DrugRequest: 'onOkButtonClick',
			data_to_return: data_to_return
		});

		this.FilterPanel.getForm().reset();

		this.onPersonSelect(data_to_return);
	},
	onPersonSelect: Ext.emptyFn,
	onWinClose: Ext.emptyFn,
	revivePerson: function (person_id, callback) {
		if (!person_id) {
			return false;
		}

		Ext.Ajax.request({
			url: '/?c=Person&m=revivePerson',
			params: {Person_id: person_id},
			callback: function (options, success, response) {
				if (success) {
					var result = Ext.util.JSON.decode(response.responseText);

					if (result.success) {
						callback();
					}
				}
			}.createDelegate(this)
		});
	},
	barcodePrinting: function () {
		var grid = this.PersonSearchViewFrame.getGrid();
		var selected = grid.getSelectionModel().getSelected();

		var s = (selected) ? selected.get('Person_id') : null;
		if (!Ext.isEmpty(s)) {
			var Report_Params = '&s=' + s;

			if (Ext.globalOptions.lis) {
				var ZebraDateOfBirth = (Ext.globalOptions.lis.ZebraDateOfBirth) ? 1 : 0;
				var ZebraUsluga_Name = (Ext.globalOptions.lis.ZebraUsluga_Name) ? 1 : 0;
				var ZebraDirect_Name = (Ext.globalOptions.lis.ZebraDirect_Name) ? 1 : 0;
				var ZebraFIO = (Ext.globalOptions.lis.ZebraFIO) ? 1 : 0;
				Report_Params = Report_Params + '&paramPrintType=2';
				Report_Params = Report_Params + '&marginTop=' + Ext.globalOptions.lis.labsample_barcode_margin_top;
				Report_Params = Report_Params + '&marginBottom=' + Ext.globalOptions.lis.labsample_barcode_margin_bottom;
				Report_Params = Report_Params + '&marginLeft=' + Ext.globalOptions.lis.labsample_barcode_margin_left;
				Report_Params = Report_Params + '&marginRight=' + Ext.globalOptions.lis.labsample_barcode_margin_right;
				Report_Params = Report_Params + '&width=' + Ext.globalOptions.lis.labsample_barcode_width;
				Report_Params = Report_Params + '&height=' + Ext.globalOptions.lis.labsample_barcode_height;
				Report_Params = Report_Params + '&barcodeFormat=' + Ext.globalOptions.lis.barcode_format;
				Report_Params = Report_Params + '&ZebraDateOfBirth=' + ZebraDateOfBirth;
				Report_Params = Report_Params + '&ZebraUsluga_Name=' + ZebraUsluga_Name;
				Report_Params = Report_Params + '&paramFrom=' + ZebraDirect_Name;
				Report_Params = Report_Params + '&paramFIO=' + ZebraFIO;
			}

			//Report_Params = Report_Params + '&paramLpu=' + getGlobalOptions().lpu_id

			printBirt({
				'Report_FileName': 'barcodesprint_resize.rptdesign',
				'Report_Params': Report_Params,
				'Report_Format': 'pdf'
			});
		}
		return false;
	},
	show: function () {
		sw.Promed.swPersonSearchWindow4DrugRequest.superclass.show.apply(this, arguments);
		var win = this;
		this.center();

		// Признак необходимости автоматически запускать поиск при открытии формы
		this.autoSearch = false;

		// Признак необходимости вытаскивать наименование организации
		this.getPersonWorkFields = false;


		this.editOnly = false; //блокирует добавление при значении true
		this.viewOnly = false;
		// флаг того, что осуществлялся поиск
		this.isSearched = false;
		this.openChildPS = false;
		// объект с данными, передаваемыми в дочернюю форму
		// можно использовать в onSelect
		this.formParams = new Object();

		// этот параметр определяет объект, для которой был вызван поиск человека
		this.searchWindowOpenMode = null;

		// разрешение на добавление неизвестного человека
		this.allowUnknownPerson = false;

		this.afterTryAdd = Ext.emptyFn;

		this.forObject = null;

		this.PersonEditWindow = getWnd('swPersonEditWindow');

		if (!this.PersonSearchViewFrame.getAction('action_getfromiemk')) {
			this.PersonSearchViewFrame.addActions({
				id: this.id + 'action_getfromiemk',
				name: 'action_getfromiemk',
				text: 'Получить данные из ИЭМК',
				disabled: true,
				menu: [{
						id: this.id + 'action_getfromiemk_reg',
						text: 'Региональная ИЭМК',
						handler: function () {
							win.getDataFromIEMK('reg');
						}
					}, {
						id: this.id + 'action_getfromiemk_fed',
						text: 'Федеральная ИЭМК',
						handler: function () {
							win.getDataFromIEMK('fed');
						}
					}],
				hidden: getRegionNick() != 'buryatiya'
			});
		}

		if (!this.PersonSearchViewFrame.getAction('action_revive')) {
			this.PersonSearchViewFrame.addActions({
				id: this.id + 'action_revive',
				name: 'action_revive',
				text: langs('Удалить признак смерти'),
				iconCls: 'actions16',
				disabled: true,
				handler: function () {
					var grid = win.PersonSearchViewFrame.getGrid();

					win.revivePerson(
							grid.getSelectionModel().getSelected().get('Person_id'),
							function () {
								grid.getStore().reload();
							}
					);
				}
			});
		}

		if (!this.PersonSearchViewFrame.getAction('action_double')) {
			this.PersonSearchViewFrame.addActions({
				id: this.id + 'action_double',
				name: 'action_double',
				text: langs('Это двойник'),
				iconCls: 'actions16',
				disabled: true,
				handler: function () {
					var grid = win.PersonSearchViewFrame.getGrid();
					var PersonUnionWindow = getWnd('swPersonUnionWindow');
					var params = {
						successFn: function () {
							grid.getStore().reload();
						},
						selRec: grid.getSelectionModel().getSelected(),
						clearGrid: !PersonUnionWindow.isVisible() ? true : false
					};
					PersonUnionWindow.show(params);
				}
			});
		}

		var form = this.FilterPanel.getForm();
		form.reset();
		//Стираем все из таблицы при открывании
		form.findField('PersonSurName_SurName').focus(true, 100);
		win.PersonSearchViewFrame.removeAll({clearAll: true});
		win.PersonSearchViewFrame.onRowSelect();

		form.findField('PersonSurName_SurName').focus(true, 500);
		var grid = this.PersonSearchViewFrame.getGrid();
		this.PersonSearchViewFrame.onRowSelect();

		if (isSuperAdmin()) {
			this.PersonSearchViewFrame.setActionHidden('action_revive', false);
		} else {
			this.PersonSearchViewFrame.setActionHidden('action_revive', true);
		}

		sw.Applets.commonReader.startReadersAdvanced({
			uec: {callback: this.getDataFromUec.createDelegate(this)},
			barcode: {callback: this.getDataFromBarcode.createDelegate(this)},
			bdz: {callback: this.getDataFromBdz.createDelegate(this)}
		});

		if (arguments[0]) {
			if (!Ext.isEmpty(arguments[0].armMode)) {
				this.armMode = arguments[0].armMode;
			} else {
				delete this.armMode;
			}

			if (arguments[0].autoSearch && arguments[0].autoSearch == true) {
				this.autoSearch = true;
			}
			if (arguments[0].editOnly && arguments[0].editOnly == true) {
				this.editOnly = true;
			}
			if (arguments[0].getPersonWorkFields && arguments[0].getPersonWorkFields == true) {
				this.getPersonWorkFields = true;
			}

			if (arguments[0].formParams && typeof arguments[0].formParams == 'object') {
				this.formParams = arguments[0].formParams;
			}

			if (arguments[0].personFirname)
				form.findField('PersonFirName_FirName').setRawValue(arguments[0].personFirname);

			if (arguments[0].personSecname)
				form.findField('PersonSecName_SecName').setRawValue(arguments[0].personSecname);

			if (arguments[0].personSurname)
				form.findField('PersonSurName_SurName').setRawValue(arguments[0].personSurname);

			if (arguments[0].PersonBirthDay_BirthDay)
				form.findField('PersonBirthDay_BirthDay').setRawValue(arguments[0].PersonBirthDay_BirthDay);

			if (arguments[0].onClose)
				this.onWinClose = arguments[0].onClose;
			else
				this.onWinClose = Ext.emptyFn;

			if (arguments[0].onSelect)
				this.onPersonSelect = arguments[0].onSelect;
			else
				this.onPersonSelect = Ext.emptyFn;

			if (arguments[0].onSelectList)
				this.onPersonSelectList = arguments[0].onSelectList;
			else
				this.onPersonSelectList = Ext.emptyFn;

			if (arguments[0].afterTryAdd)
				this.afterTryAdd = arguments[0].afterTryAdd;

			if (arguments[0].forObject)
				this.forObject = arguments[0].forObject;

			if (arguments[0].searchMode)
				this.searchMode = arguments[0].searchMode;
			else
				this.searchMode = 'all';

			if (arguments[0].Year)
				this.Year = arguments[0].Year;
			else
				this.Year = null;

			if (arguments[0].PersonRegisterType_id)
				this.PersonRegisterType_id = arguments[0].PersonRegisterType_id;
			else
				this.PersonRegisterType_id = null;

			if (arguments[0].DrugRequestPeriod_id)
				this.DrugRequestPeriod_id = arguments[0].DrugRequestPeriod_id;
			else
				this.DrugRequestPeriod_id = null;

			if (arguments[0].LpuRegion_id)
				this.LpuRegion_id = arguments[0].LpuRegion_id;
			else
				this.LpuRegion_id = null;

			if (arguments[0].PersonRefuse_IsRefuse)
				this.PersonRefuse_IsRefuse = arguments[0].PersonRefuse_IsRefuse;
			else
				this.PersonRefuse_IsRefuse = null;

			// @task https://redmine.swan.perm.ru/issues/64058
			switch (this.searchMode) {
				case 'wow':
					this.setTitle(WND_PERS_SEARCH + langs(' (только регистр ВОВ)'));
					break;

				case 'ddorpperiod':
					this.setTitle(WND_PERS_SEARCH + langs(' (только регистр периодических осмотров несовершеннолетних)'));
					break;

				case 'ddorp':
				case 'ddorpsec':
					this.setTitle(WND_PERS_SEARCH + langs(' (только регистр детей-сирот)'));
					break;

				case 'attachrecipients':
					if ((!getGlobalOptions().isMinZdrav) && (!getGlobalOptions().isOnko) && (!getGlobalOptions().isOnkoGem) && (!getGlobalOptions().isPsih) && (!getGlobalOptions().isRA)) {
						this.setTitle(WND_PERS_SEARCH + langs(' (прикрепленные льготники)'));
					} else {
						this.setTitle(WND_PERS_SEARCH);
					}
					break;

				default:
					this.setTitle(WND_PERS_SEARCH);
					break;
			}

			switch (this.searchMode) {
				case 'wow':
				case 'ddorpperiod':
				case 'ddorp':
				case 'ddorpsec':
					this.editOnly = true;
					break;
			}

			if (arguments[0].searchWindowOpenMode) {
				this.searchWindowOpenMode = arguments[0].searchWindowOpenMode;
			}

			if (arguments[0].childPS) {
				this.openChildPS = true;
			} else {
				this.openChildPS = false;
			}

			if (arguments[0].allowUnknownPerson) {
				this.allowUnknownPerson = arguments[0].allowUnknownPerson;
			}

			if (arguments[0].viewOnly) {
				this.viewOnly = arguments[0].viewOnly;
			}
			//BOB - 21.03.2017
			if (arguments[0].Person_ids) {
				this.Person_ids = arguments[0].Person_ids;
			} else {
				if (this.Person_ids)
					this.Person_ids = null;
			}
			//BOB - 21.03.2017

		}

		var idx = this.PersonSearchViewFrame.getColumnModel().findColumnIndex('check');
		if (arguments[0].searchList) {
			this.searchList = arguments[0].searchList; // Возможность выбора списка
			this.checkAll(false);

			Ext.getCmp('swPersonSearchWindow4DrugRequest').PersonSearchViewFrame.getGrid().getColumnModel().setColumnHeader(idx, '<input type="checkbox" id="PSPCAW_checkAll" onClick="getWnd(\'swPersonSearchWindow4DrugRequest\').checkAll(this.checked, this);">');
			Ext.getCmp('PersonSearchSelectList').show();
		} else {
			this.searchList = 0;
			Ext.getCmp('PersonSearchSelectList').hide();
		}

		this.PersonSearchViewFrame.getColumnModel().setHidden(idx, this.searchList != 1);
		//Ext.getCmp('PersonSearchSelectList').disable(this.searchList != 1);

		if (this.viewOnly || getWnd('swWorkPlaceMZSpecWindow').isVisible() || arguments[0].Person_ids) {
			this.PersonSearchViewFrame.setActionDisabled('action_add', true);
		} else {
			this.PersonSearchViewFrame.setActionDisabled('action_add', false);
		}
		form.findField('PersonBirthDay_BirthDay').on('focus', function () {
			form.findField('PersonBirthDay_BirthDay').triggerBlur()
		})
		//form.findField('PersonBirthDay_BirthDay').triggerBlur();
		form.findField('Person_Snils').setValue('');
		form.findField('ParentARM').setValue(arguments[0].ARMType || '');
		form.findField('PersonCard_Code').setValue('');
		form.findField('EvnPS_NumCard').setValue('');
		form.findField('Polis_Ser').setValue(arguments[0].Polis_Ser || '');
		form.findField('Polis_Num').setValue(arguments[0].Polis_Num || '');
		form.findField('Polis_EdNum').setValue(arguments[0].Polis_EdNum || '');
		form.findField('PersonAge_AgeFrom').setValue(arguments[0].Person_Age || '');
		form.findField('PersonAge_AgeTo').setValue(arguments[0].Person_Age || '');
		form.findField('EvnUdost_Ser').setValue('');
		form.findField('EvnUdost_Num').setValue('');

		grid.getStore().baseParams.searchMode = this.searchMode;

		if (this.autoSearch == true) {
			this.doSearch();
		}

	},
	checkRenderer: function (v, p, record) {
		var id = record.get('Person_id');
		var value = 'value="' + id + '"';
		var checked = record.get('Is_Checked') != 0 ? ' checked="checked"' : '';
		//var onclick = 'onClick="getWnd(\'swPersonSearchWindow4DrugRequest\').checkOne(this.value);"';
		var onclick = 'onClick="getWnd(\'swPersonSearchWindow4DrugRequest\').checkOne(this.value);"';
		return '<input type="checkbox" ' + value + ' ' + checked + ' ' + onclick + '>';

	},
	checkAll: function (check, obj)
	{
		var form = this;
		var array_index = -1;
		var val = check ? 1 : 0;
		this.PersonSearchViewFrame.getGrid().getStore().each(function (record) {
			record.set('Is_Checked', val);
			record.commit();
		});
	},
	checkOne: function (id) {
		var form = this;
		var grid = form.PersonSearchViewFrame.getGrid();
		var record = grid.getSelectionModel().getSelected();
		record.set('Is_Checked', record.data.Is_Checked == 0 ? 1 : 0);
		record.commit();
	},
	/* конструктор */
	initComponent: function () {
		var win = this;

		win.FilterPanel = new Ext.form.FormPanel({
			autoHeight: true,
			autoLoad: false,
			bodyStyle: 'background:#FFF;padding:0;',
			buttonAlign: 'left',
			frame: true,
			id: /*win.id +*/ 'person_search_form',
			keys: [{
					key: Ext.EventObject.ENTER,
					fn: function (e) {
						win.doSearch();
					},
					stopEvent: true
				}],
			region: 'north',
			items: [{
					autoHeight: true,
					style: 'padding: 5px',
					layout: 'form',
					labelAlign: 'top',
					labelWidth: 95,
					width: 880,
					items: [{
							layout: 'column',
							items: [{
									columnWidth: 1,
									layout: 'form',
									items: [{
											autoHeight: true,
											xtype: 'fieldset',
											collapsible: true,
											listeners: {
												collapse: function (p) {
													win.doLayout();
												},
												expand: function (p) {
													win.doLayout();
												}
											},
											title: langs('Пациент'),
											style: 'padding: 2px; padding-left: 10px',
											items: [{
													layout: 'column',
													items: [{
															layout: 'form',
															columnWidth: .33,
															items: [{
																	xtype: (getRegionNick() == 'kz') ? 'textfield' : 'swtranslatedtextfieldwithapostrophe',
																	fieldLabel: langs('Фамилия'),
																	maskRe: /[^%]/,
																	name: 'PersonSurName_SurName',
																	anchor: '95%',
																	tabIndex: TABINDEX_AUTOINC++,
																	listeners: {
																		'keydown': function (inp, e) {
																			if (e.shiftKey == true && e.getKey() == Ext.EventObject.TAB) {
																				e.stopEvent();
																				win.buttons[win.buttons.length - 1].focus();
																			}
																		}
																	}
																}]
														}, {
															name: 'ParentARM',
															value: '',
															xtype: 'hidden'
														}, {
															layout: 'form',
															columnWidth: .33,
															items: [{
																	xtype: (getRegionNick() == 'kz') ? 'textfield' : 'swtranslatedtextfieldwithapostrophe',
																	maskRe: /[^%]/,
																	fieldLabel: langs('Имя'),
																	name: 'PersonFirName_FirName',
																	anchor: '95%',
																	tabIndex: TABINDEX_AUTOINC++,
																	listeners: {
																		'keydown': function (inp, e) {
																			if (e.shiftKey == true && e.getKey() == Ext.EventObject.TAB) {
																				e.stopEvent();
																				win.FilterPanel.getForm().findField("PersonSurName_SurName").focus(true);
																			}
																		}
																	}
																}]
														}, {
															layout: 'form',
															columnWidth: .33,
															items: [{
																	xtype: (getRegionNick() == 'kz') ? 'textfield' : 'swtranslatedtextfieldwithapostrophe',
																	maskRe: /[^%]/,
																	fieldLabel: langs('Отчество'),
																	name: 'PersonSecName_SecName',
																	anchor: '95%',
																	tabIndex: TABINDEX_AUTOINC++
																}]
														}]
												}, {
													layout: 'column',
													items: [{
															layout: 'form',
															columnWidth: .2,
															items: [{
																	xtype: 'swdatefield',
																	plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
																	fieldLabel: langs('Дата рождения'),
																	format: 'd.m.Y',
																	name: 'PersonBirthDay_BirthDay',
																	tabIndex: TABINDEX_AUTOINC++
																}]
														}, {
															layout: 'form',
															columnWidth: .2,
															items: [{
																	xtype: 'numberfield',
																	fieldLabel: langs('Возраст с'),
																	name: 'PersonAge_AgeFrom',
																	allowNegative: false,
																	allowDecimals: false,
																	autoCreate: {
																		tag: "input",
																		type: "text",
																		size: "11",
																		maxLength: "3",
																		autocomplete: "off"
																	},
																	tabIndex: TABINDEX_AUTOINC++
																}]
														}, {
															layout: 'form',
															columnWidth: .2,
															items: [{
																	xtype: 'numberfield',
																	fieldLabel: langs('по'),
																	name: 'PersonAge_AgeTo',
																	allowNegative: false,
																	allowDecimals: false,
																	autoCreate: {
																		tag: "input",
																		type: "text",
																		size: "11",
																		maxLength: "3",
																		autocomplete: "off"
																	},
																	tabIndex: TABINDEX_AUTOINC++
																}]
														}, {
															layout: 'form',
															columnWidth: .2,
															items: [{
																	xtype: 'numberfield',
																	fieldLabel: langs('Год рождения с'),
																	name: 'PersonBirthYearFrom',
																	allowNegative: false,
																	allowDecimals: false,
																	autoCreate: {
																		tag: "input",
																		type: "text",
																		size: "11",
																		maxLength: "4",
																		autocomplete: "off"
																	},
																	tabIndex: TABINDEX_AUTOINC++
																}]
														}, {
															layout: 'form',
															columnWidth: .2,
															items: [{
																	xtype: 'numberfield',
																	fieldLabel: langs('по'),
																	name: 'PersonBirthYearTo',
																	allowNegative: false,
																	allowDecimals: false,
																	autoCreate: {
																		tag: "input",
																		type: "text",
																		size: "11",
																		maxLength: "4",
																		autocomplete: "off"
																	},
																	enableKeyEvents: true,
																	tabIndex: TABINDEX_AUTOINC++
																}]
														}]
												}, {
													layout: 'column',
													items: [{
															columnWidth: .24,
															labelWidth: 50,
															layout: 'form',
															hidden: !(isAdmin || (getRegionNick() == 'ufa' && isLpuAdmin()) || (getRegionNick() == 'ekb' && (isUserGroup('LpuUser') || isLpuAdmin()))),
															items: [{
																	xtype: 'swtranslatedtextfield',
																	maskRe: /[\d]/,
																	width: 130,
																	name: 'Person_id',
																	fieldLabel: 'ИД пациента', //((getRegionNick() == 'ekb' && (isUserGroup('LpuUser') || isLpuAdmin()))) ? 'ИД пациента' : 'Person_id',
																	tabIndex: TABINDEX_AUTOINC++
																}]
														}, {
															columnWidth: .25,
															labelWidth: 50,
															layout: 'form',
															hidden: getRegionNick() == 'kz',
															items: [{
																	xtype: 'swsnilsfield',
																	fieldLabel: langs('СНИЛС'),
																	fieldWidth: 130,
																	hiddenName: 'Person_Snils',
																	tabIndex: TABINDEX_AUTOINC++
																}]
														},
														{
															columnWidth: .33,
															labelWidth: 110,
															layout: 'form',
															hidden: getRegionNick() != 'kz',
															items: [{
																	allowBlank: true,
																	fieldLabel: (getRegionNick() == 'kz' ? langs('ИИН') : langs('ИНН')),
																	maskRe: /\d/,
																	name: 'Person_Inn',
																	autoCreate: {
																		tag: "input",
																		type: "text",
																		size: "30",
																		maxLength: "12",
																		autocomplete: "off"
																	},
																	tabIndex: TABINDEX_AUTOINC++,
																	width: 150,
																	maxLength: 12,
																	minLength: 12,
																	xtype: 'textfield'
																}]
														}]
												}]
										}, {
											xtype: 'fieldset',
											autoHeight: true,
											collapsible: true,
											listeners: {
												collapse: function (p) {
													win.doLayout();
												},
												expand: function (p) {
													win.doLayout();
												}
											},
											title: langs('Полис'),
											hidden: getRegionNick() == 'kz',
											style: 'padding: 2px; padding-left: 10px',
											items: [{
													layout: 'column',
													items: [{
															columnWidth: .24,
															labelWidth: 50,
															layout: 'form',
															items: [{
																	xtype: 'textfield',
																	maskRe: /[^%]/,
																	fieldLabel: langs('Серия'),
																	width: 130,
																	name: 'Polis_Ser',
																	tabIndex: TABINDEX_AUTOINC++
																}]
														}, {
															columnWidth: .24,
															labelWidth: 110,
															layout: 'form',
															items: [{
																	xtype: 'textfield',
																	maskRe: /[^%]/,
																	fieldLabel: langs('Номер'),
																	width: 130,
																	name: 'Polis_Num',
																	tabIndex: TABINDEX_AUTOINC++
																}]
														}, {
															columnWidth: .24,
															labelWidth: 95,
															layout: 'form',
															items: [{
																	xtype: 'textfield',
																	maskRe: /[^%]/,
																	fieldLabel: langs('Единый номер'),
																	width: 130,
																	name: 'Polis_EdNum',
																	tabIndex: TABINDEX_AUTOINC++
																}]
														}]
												}]
										}, {
											xtype: 'fieldset',
											autoHeight: true,
											collapsible: true,
											listeners: {
												collapse: function (p) {
													win.doLayout();
												},
												expand: function (p) {
													win.doLayout();
												}
											},
											title: langs('Мед. документы'),
											style: 'padding: 2px; padding-left: 10px',
											items: [{
													layout: 'column',
													items: [{
															columnWidth: .24,
															labelWidth: 110,
															layout: 'form',
															hidden: getRegionNick() != 'ekb',
															items: [{
																	xtype: 'textfield',
																	fieldLabel: langs('Штрихкод амб. карты'),
																	width: 130,
																	name: 'PersonCard_id',
																	tabIndex: TABINDEX_AUTOINC++
																}]
														}, {
															columnWidth: .24,
															labelWidth: 110,
															layout: 'form',
															items: [{
																	xtype: 'textfield',
																	maskRe: /[^%]/,
																	fieldLabel: langs('Номер амб. карты'),
																	width: 130,
																	name: 'PersonCard_Code',
																	tabIndex: TABINDEX_AUTOINC++
																}]
														}, {
															columnWidth: .24,
															labelWidth: 95,
															layout: 'form',
															items: [{
																	xtype: 'textfield',
																	maskRe: /[^%]/,
																	fieldLabel: langs('Номер КВС'),
																	width: 130,
																	name: 'EvnPS_NumCard',
																	tabIndex: TABINDEX_AUTOINC++
																}]
														}]
												}]
										}, {
											xtype: 'fieldset',
											autoHeight: true,
											collapsible: true,
											listeners: {
												collapse: function (p) {
													win.doLayout();
												},
												expand: function (p) {
													win.doLayout();
													win.doLayout(); // с одного раза почему то не лэйоутится
												}
											},
											collapsed: true,
											title: langs('Удостоверения'),
											style: 'padding: 2px; padding-left: 10px',
											items: [{
													layout: 'column',
													items: [{
															columnWidth: .24,
															labelWidth: 50,
															layout: 'form',
															hidden: getRegionNick() == 'kz',
															items: [{
																	xtype: 'textfield',
																	maskRe: /[^%]/,
																	fieldLabel: langs('Серия'),
																	width: 130,
																	name: 'EvnUdost_Ser',
																	tabIndex: TABINDEX_AUTOINC++
																}]
														}, {
															columnWidth: .24,
															labelWidth: 110,
															layout: 'form',
															items: [{
																	xtype: 'textfield',
																	maskRe: /[^%]/,
																	fieldLabel: langs('Номер'),
																	width: 130,
																	name: 'EvnUdost_Num',
																	tabIndex: TABINDEX_AUTOINC++
																}]
														}]
												}]
										}]
								}, {
									width: 65,
									style: 'padding: 5px;',
									layout: 'form',
									items: [{
											xtype: 'button',
											hidden: !getGlobalOptions()['card_reader_is_enable'],
											cls: 'x-btn-large',
											iconCls: 'idcard32',
											tooltip: langs('Считать с карты'),
											handler: function () {
												win.readFromCard();
											}
										}, {
											xtype: 'button',
											style: 'padding-top: 5px;',
											hidden: false,
											cls: 'x-btn-large',
											iconCls: 'barcode_printing32',
											id: 'buttonBarcodePrinting',
											tooltip: langs('Печать штрих-кода пациента'),
											handler: function () {
												win.barcodePrinting();
											}
										}]
								}]
						}]
				}]
		});

		win.PersonSearchViewFrame = new sw.Promed.ViewFrame({
			id: /*win.id +*/ 'DrugRequest_PersonSearchViewFrame',
			paging: true,
			pageSize: 100,
			uniqueId: true,
			region: 'center',
			root: 'data',
			autoLoadData: false,
			totalProperty: 'totalCount',
			dataUrl: C_PERSON_SEARCH,
			tabIndex: TABINDEX_AUTOINC++,
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 120,
			stripeRows: true,
			actions: [
				{name: 'action_add', handler: function () {
						if (this.disabled) {
							return false;
						}

						var form = win.FilterPanel.getForm();
						var grid = win.PersonSearchViewFrame.getGrid();
						if (win.isSearched /*&& grid.getStore().getCount() == 0*/) {
							sw.swMsg.show({
								title: langs('Подтверждение добавления человека'),
								msg: langs('Внимательно проверьте введенную информацию по поиску человека! Вы точно хотите добавить нового человека?'),
								buttons: Ext.Msg.YESNO,
								fn: function (buttonId) {
									if (buttonId == 'yes') {
										getWnd('swPersonEditWindow').show({
											action: 'add',
											allowUnknownPerson: win.allowUnknownPerson,
											fields: {
												// не стал убирать передачу этих параметров, вдруг захотят обратно вернуть
												// :) захотели (refs #12322)
												'Person_SurName': form.findField('PersonSurName_SurName').getValue().toUpperCase(),
												'Person_FirName': form.findField('PersonFirName_FirName').getValue().toUpperCase(),
												'Person_SecName': form.findField('PersonSecName_SecName').getValue().toUpperCase(),
												'Person_BirthDay': form.findField('PersonBirthDay_BirthDay').getValue(),
												'Person_SNILS': form.findField('Person_Snils').getValue()
											},
											forObject: win.forObject,
											callback: function (callback_data) {
												if (callback_data.PersonData) {
													if (callback_data.PersonData.Person_FirName) {
														form.findField('PersonFirName_FirName').setValue(callback_data.PersonData.Person_FirName);
													}
													if (callback_data.PersonData.Person_SurName) {
														form.findField('PersonSurName_SurName').setValue(callback_data.PersonData.Person_SurName);
													}
													if (callback_data.PersonData.Person_SecName) {
														form.findField('PersonSecName_SecName').setValue(callback_data.PersonData.Person_SecName);
													}
													if (callback_data.PersonData.Person_BirthDay) {
														form.findField('PersonBirthDay_BirthDay').setValue(callback_data.PersonData.Person_BirthDay);
													}
													if (callback_data.PersonData.Person_Snils) {
														form.findField('Person_Snils').setValue(callback_data.PersonData.Person_Snils);
													}
												}
												win.doSearch({}, function () {
													win.onOkButtonClick(callback_data);
												});
												getWnd('swPersonEditWindow').hide();
											},
											onClose: function () {
												form.findField('PersonSurName_SurName').focus(true, 500);
												//win.doSearch();
											},
											afterTryAdd: win.afterTryAdd
										});
									} else {
										form.findField('PersonSurName_SurName').focus(true, 500);
									}
								}
							});
							return false;
						}
						getWnd('swPersonEditWindow').show({
							action: 'add',
							allowUnknownPerson: win.allowUnknownPerson,
							fields: {
								// не стал убирать передачу этих параметров, вдруг захотят обратно вернуть
								'Person_SurNameEdit': '',
								'Person_FirNameEdit': '',
								'Person_SecNameEdit': ''
							},
							forObject: win.forObject,
							callback: function (callback_data) {
								if (callback_data.PersonData) {
									if (callback_data.PersonData.Person_FirName) {
										log(callback_data.PersonData.Person_FirName);
										form.findField('PersonFirName_FirName').setValue(callback_data.PersonData.Person_FirName);
										log(form.findField('PersonFirName_FirName').getValue());
									}
									if (callback_data.PersonData.Person_SurName) {
										form.findField('PersonSurName_SurName').setValue(callback_data.PersonData.Person_SurName);
									}
									if (callback_data.PersonData.Person_SecName) {
										form.findField('PersonSecName_SecName').setValue(callback_data.PersonData.Person_SecName);
									}
									if (callback_data.PersonData.Person_BirthDay) {
										form.findField('PersonBirthDay_BirthDay').setValue(callback_data.PersonData.Person_BirthDay);
									}
									if (callback_data.PersonData.Person_Snils) {
										form.findField('Person_Snils').setValue(callback_data.PersonData.Person_Snils);
									}
								}
								win.doSearch({}, function () {
									if (Ext.isEmpty(win.armMode) || (win.armMode.toLowerCase() != 'lis')) {
										win.onOkButtonClick(callback_data);
									} else {
										Ext.Msg.alert('Добавление человека', 'Добавленный человек появится в течение пары минут.');
									}
								});
								getWnd('swPersonEditWindow').hide();
							},
							onClose: function () {
								form.findField('PersonSurName_SurName').focus(true, 500);
							},
							afterTryAdd: win.afterTryAdd
						});
					}},
				{name: 'action_edit', handler: function () {
						var grid = win.PersonSearchViewFrame.getGrid();
						if ((!grid.getSelectionModel().getSelected()) || (grid.getStore().getCount() == 0))
							return;
						var person_id = grid.getSelectionModel().getSelected().data.Person_id;
						var server_id = grid.getSelectionModel().getSelected().data.Server_id;
						getWnd('swPersonEditWindow').show({
							action: 'edit',
							Person_id: person_id,
							Server_id: server_id,
							forObject: win.forObject,
							callback: function (callback_data) {
								// обновляем грид
								if (callback_data) {
									grid.getStore().each(function (record) {
										if (record.data.Person_id == callback_data.Person_id) {
											record.set('Server_id', callback_data.Server_id);
											record.set('PersonEvn_id', callback_data.PersonEvn_id);
											record.set('PersonSurName_SurName', callback_data.PersonData.Person_SurName);
											record.set('PersonFirName_FirName', callback_data.PersonData.Person_FirName);
											record.set('PersonSecName_SecName', callback_data.PersonData.Person_SecName);
											record.set('PersonBirthDay_BirthDay', callback_data.PersonData.Person_BirthDay);
											record.set('Person_Age', callback_data.PersonData.Person_Age);
											record.set('Person_Phone', callback_data.PersonData.Person_Phone);
											record.set('Person_Work_id', callback_data.PersonData.Person_Work_id);
											record.set('Post_id', callback_data.PersonData.Post_id);
											record.set('Person_Work', callback_data.PersonData.Person_Work);
											record.set('Document_Ser', callback_data.PersonData.Document_Ser);
											record.set('Document_Num', callback_data.PersonData.Document_Num);
											record.set('Polis_Ser', callback_data.PersonData.Polis_Ser);
											record.set('Polis_Num', callback_data.PersonData.Polis_Num);
											record.set('Polis_EdNum', callback_data.PersonData.Polis_EdNum);
											record.set('Person_Snils', callback_data.PersonData.Person_Snils);
											if (callback_data.PersonData.Lpu_Nick !== undefined) {
												record.set('Lpu_Nick', callback_data.PersonData.Lpu_Nick);
											}
											record.commit();
										}
									});
								}
								grid.getView().focusRow(0);
							},
							onClose: function () {
								grid.getView().focusRow(0);
							}
						});
					}},
				{name: 'action_view', handler: function () {
						var grid = win.PersonSearchViewFrame.getGrid();
						if ((!grid.getSelectionModel().getSelected()) || (grid.getStore().getCount() == 0))
							return;
						var person_id = grid.getSelectionModel().getSelected().data.Person_id;
						var server_id = grid.getSelectionModel().getSelected().data.Server_id;
						getWnd('swPersonEditWindow').show({
							readOnly: true,
							Person_id: person_id,
							Server_id: server_id,
							forObject: win.forObject,
							callback: function (callback_data) {
								grid.getView().focusRow(0);
							},
							onClose: function () {
								grid.getView().focusRow(0);
							}
						});
					}},
				{name: 'action_delete', hidden: true, disabled: true}
			],
			onDblClick: function () {
				win.onOkButtonClick();
			},
			onEnter: function () {
				win.onOkButtonClick();
			},
			onRowSelect: function (sm, index, record) {
				this.setActionDisabled('action_edit', true);
				this.setActionDisabled('action_getfromiemk', true);
				this.setActionDisabled('action_revive', true);
				this.setActionDisabled('action_double', true);
				this.setActionDisabled('action_view', true);
				var buttonBarcodePrinting = Ext.getCmp('buttonBarcodePrinting');
				if ((win.viewOnly == true) || getWnd('swWorkPlaceMZSpecWindow').isVisible()) {
					this.setActionDisabled('action_view', false);
				}
				if (record && record.get('Person_id') && win.viewOnly == false && !getWnd('swWorkPlaceMZSpecWindow').isVisible()) {
					if (record.get('accessType') == 'edit') {
						this.setActionDisabled('action_edit', false);
						this.setActionDisabled('action_getfromiemk', false);
					}
					if (record.get('accessType').inlist(['edit', 'view'])) {
						this.setActionDisabled('action_view', false);
					}
					this.setActionDisabled('action_double', false);
					if (record.get('Person_id') && record.get('Person_IsDead') && record.get('Person_IsDead') == 'true') {
						this.setActionDisabled('action_revive', false);
					}

					buttonBarcodePrinting.setVisible(true);
				} else {
					buttonBarcodePrinting.setVisible(false);
				}
			},
			stringfields: [
				{
					name: 'Person_id',
					//hidden: !(isAdmin || (getRegionNick() == 'ufa' && isLpuAdmin())),
					header: 'Person_id'
				},
				{name: 'Server_id', hidden: true, hideable: false},
				{name: 'PersonEvn_id', hidden: true, hideable: false},
				{name: 'Sex_id', hidden: true, hideable: false},
				{name: 'Polis_Ser', hidden: true, hideable: false},
				{name: 'Polis_EdNum', hidden: true, hideable: false},
				{name: 'Polis_endDate', hidden: true, hideable: false, type: 'date'},
				{name: 'Person_IsDead', hidden: true, hideable: false},
				{name: 'Person_Snils', hidden: true, hideable: false},
				{name: 'UAddress_AddressText', hidden: true, hideable: false},
				{name: 'PAddress_AddressText', hidden: true, hideable: false},
				{name: 'accessType', hidden: true, hideable: false},
				{name: 'Person_isOftenCaller', hidden: true, hideable: false},
				{name: 'Document_Ser', hidden: true, hideable: false},
				{name: 'Document_Num', hidden: true, hideable: false},
				{name: 'Person_Age', hidden: true, hideable: false},
				{name: 'Person_Phone', hidden: true, hideable: false},
				{name: 'Person_Work_id', hidden: true, hideable: false},
				{name: 'Post_id', hidden: true, hideable: false},
				{name: 'Person_Work', hidden: true, hideable: false},
				{name: 'MedWorker_id', hidden: true, hideable: false},
				{name: 'check', sortable: false, width: 40, renderer: this.checkRenderer, header: '<input type="checkbox" id="PSPCAW_checkAll" onClick="getWnd(\'swPersonSearchWindow4DrugRequest\').checkAll(this.checked, this);">'

				},
				{name: 'Is_Checked', type: 'int', header: 'is_checked', hidden: true},
				{
					//id: 'autoexpand',
					header: 'ИД пациента', //((getRegionNick() == 'ekb' && (isUserGroup('LpuUser') || isLpuAdmin()))) ? 'ИД пациента' : 'Person_id',
					name: 'Person_id',
					hidden: !(
							(getRegionNick() == 'ekb' && ((isUserGroup('LpuUser')) || isLpuAdmin())) ||
							(isAdmin || (getRegionNick() == 'ufa' && isLpuAdmin()))
							),
					sortable: true,
					width: 90
				},
				{
					id: 'autoexpand',
					header: langs('Фамилия'),
					name: 'PersonSurName_SurName',
					sortable: true
				},
				{header: langs('Имя'), name: 'PersonFirName_FirName', sortable: true, width: 120},
				{header: langs('Отчество'), name: 'PersonSecName_SecName', sortable: true, width: 120},
				{
					header: langs('Дата рождения'),
					name: 'PersonBirthDay_BirthDay',
					sortable: true,
					width: 70,
					type: 'date'
				},
				{
					header: (getAppearanceOptions().language == 'kz') ? 'Қайтыс болған уақыты' : langs('Дата смерти'),
					name: 'Person_deadDT',
					sortable: true,
					width: 70,
					type: 'date'
				},
				{
					header: langs('Номер полиса'),
					name: 'Polis_Num',
					sortable: true,
					width: 100,
					hidden: getRegionNick() == 'kz'
				},
				{header: langs('МО прикрепления'), name: 'Lpu_Nick', sortable: true, width: 120},
				{header: langs('Номер амб. карты'), name: 'PersonCard_Code', sortable: true, width: 120},
				new Ext.grid.CheckColumn({
					header: "Прикр. ДМС",
					name: 'PersonCard_IsDms',
					width: 65,
					sortable: true,
					disabled: true
				}),
				new Ext.grid.CheckColumn({
					header: langs('БДЗ'),
					name: 'Person_IsBDZ',
					width: 35,
					sortable: true,
					disabled: true,
					qtip: function (value, metadata, record) {
						var tooltip = {
							'true': 'Человек идентифицирован в РС ЕРЗ',
							'false': 'Человек идентифицирован в ЦС ЕРЗ',
							'yellow': 'Требуется уточнение данных страхования',
							'red': (getRegionNick() == 'kz') ? 'Требуется уточнение данных в РПН' : 'У человека указана дата смерти',
							'blue': 'Человек не идентифицирован в ЦС ЕРЗ',
							'orange': ''
						};
						if (getRegionNick() == 'penza') {
							var tooltip = {
								'true': 'Человек идентифицирован в РС ЕРЗ',
								'orange': 'Человек не идентифицирован в РС ЕРЗ'
							}
						}
						return tooltip[value];
					},
					//,hidden: getRegionNick() == 'kz'
				}),
				new Ext.grid.CheckColumn({
					header: (getRegionNick().inlist(['kz']) ? langs("Льгота") : "Фед. льг"),
					name: 'Person_IsFedLgot',
					width: 50,
					sortable: true,
					disabled: true
				}),
				new Ext.grid.CheckColumn({
					header: langs('Отказ'),
					name: 'Person_IsRefuse',
					width: 50,
					sortable: true,
					disabled: true
				}),
				new Ext.grid.CheckColumn({
					header: "Рег. льг",
					name: 'Person_IsRegLgot',
					width: 50,
					sortable: true,
					disabled: true,
					hideable: !getRegionNick().inlist(['kz']),
					hidden: getRegionNick().inlist(['kz'])
				}),
				new Ext.grid.CheckColumn({
					header: "7 ноз.",
					name: 'Person_Is7Noz',
					width: 50,
					sortable: true,
					disabled: true
				})
			],
			keys: [{
					key: [
						Ext.EventObject.END,
						Ext.EventObject.HOME,
						Ext.EventObject.PAGE_DOWN,
						Ext.EventObject.PAGE_UP,
						Ext.EventObject.F3,
						Ext.EventObject.F6,
						Ext.EventObject.F7,
						Ext.EventObject.F10,
						Ext.EventObject.F11,
						Ext.EventObject.F12
					],
					fn: function (inp, e) {
						e.stopEvent();

						if (e.browserEvent.stopPropagation)
							e.browserEvent.stopPropagation();
						else
							e.browserEvent.cancelBubble = true;

						if (e.browserEvent.preventDefault)
							e.browserEvent.preventDefault();
						else
							e.browserEvent.returnValue = false;

						e.browserEvent.returnValue = false;
						e.returnValue = false;

						if (Ext.isIE) {
							e.browserEvent.keyCode = 0;
							e.browserEvent.which = 0;
						}

						var grid = win.PersonSearchViewFrame.getGrid();

						switch (e.getKey()) {
							case Ext.EventObject.END:
								GridEnd(grid);
								break;

							case Ext.EventObject.HOME:
								GridHome(grid);
								break;

							case Ext.EventObject.PAGE_DOWN:
								GridPageDown(grid, 'Person_id');
								break;

							case Ext.EventObject.PAGE_UP:
								GridPageUp(grid, 'Person_id');
								break;
						}
						// формы журналов
						var records_count = grid.getStore().getCount();
						if (records_count > 0 && grid.getSelectionModel().getSelected()) {
							var selected_record = grid.getSelectionModel().getSelected();
							var params = new Object();
							params.Person_id = selected_record.get('Person_id');
							params.Server_id = selected_record.get('Server_id');
							params.Person_Birthday = Ext.util.Format.date(selected_record.get('PersonBirthDay_BirthDay'), 'd.m.Y');
							params.Person_Firname = selected_record.get('PersonFirName_FirName');
							params.Person_Secname = selected_record.get('PersonSecName_SecName');
							params.Person_Surname = selected_record.get('PersonSurName_SurName');
							params.onHide = function () {
								var index = grid.getStore().indexOf(selected_record);
								grid.focus();
								grid.getView().focusRow(index);
								grid.getSelectionModel().selectRow(index);
							}
						} else {
							return false;
						}
						if (e.getKey() == Ext.EventObject.F3) {
							if (e.altKey) {
								params['key_id'] = selected_record.get('Person_id');
								params['key_field'] = 'Person_id';
								if (!Ext.isEmpty(params['key_id'])) {
									getWnd('swAuditWindow').show(params);
								}
							}
						}
						if (e.getKey() == Ext.EventObject.F6) {
							if (!e.altKey) {
								ShowWindow('swPersonCardHistoryWindow', params);
							} else {
								AddPersonToUnion(
										grid.getSelectionModel().getSelected(),
										function () {
											grid.getStore().reload();
										}
								);
							}
							return false;
						}
						if (e.getKey() == Ext.EventObject.F7) {
							if (e.altKey) {
								if (getGlobalOptions().region && getGlobalOptions().region.nick != 'perm') {
									AddPersonToUnion(
											grid.getSelectionModel().getSelected(),
											function () {
												grid.getStore().reload();
											}
									);
								}
							}
							return false;
						}

						if (e.getKey() == Ext.EventObject.F10) {
							getWnd('swPersonEditWindow').show(params);
							return false;
						}

						if (e.getKey() == Ext.EventObject.F11) {
							getWnd('swPersonCureHistoryWindow').show(params);
							return false;
						}

						if (e.getKey() == Ext.EventObject.F12) {
							if (e.ctrlKey) {
								getWnd('swPersonDispHistoryWindow').show(params);
							} else {
								getWnd('swPersonPrivilegeViewWindow').show(params);
							}
							return false;
						}
					},
					stopEvent: true
				}]
		});

		win.PersonSearchViewFrame.getGrid().view = new Ext.grid.GridView({
			getRowClass: function (row, index) {
				var cls = '';

				if (row.get('Person_deadDT')) {
					cls = cls + 'x-grid-rowgray ';
				}
				if (row.get('Is_Checked') == 1) {
					cls = cls + 'x-grid-rowbold ';
					cls = cls + 'x-grid-rowgreen ';
				}

				return cls;
			},
			listeners: {
				rowupdated: function (view, first, record) {
					view.getRowClass(record);
				}
			}
		});

		Ext.apply(win, {
			items: [
				win.FilterPanel,
				win.PersonSearchViewFrame
			],
			buttons: [{
					text: BTN_FRMSEARCH,
					iconCls: 'search16',
					handler: function () {
						win.doSearch()
					},
					tabIndex: TABINDEX_AUTOINC++
				}, {
					text: (getAppearanceOptions().language == 'kz') ? 'Қалпына келтіру' : BTN_FRMRESET,
					iconCls: 'resetsearch16',
					handler: function () {
						var form = win.FilterPanel.getForm();
						form.reset();
						form.findField('PersonSurName_SurName').focus(true, 100);
						win.PersonSearchViewFrame.removeAll({clearAll: true});
						win.PersonSearchViewFrame.onRowSelect();
					},
					tabIndex: TABINDEX_AUTOINC++
				}, {
					iconCls: 'ok16',
					text: langs('Выбрать'),
					handler: function () {
						win.onOkButtonClick();
					},
					tabIndex: TABINDEX_AUTOINC++
				}, {
					iconCls: 'ok16',
					id: 'PersonSearchSelectList',
					text: langs('Выбрать список'),
					handler: function () {
						if (win.searchList == 1) {
							var persons = '';
							var grid = win.PersonSearchViewFrame;
							Cnt = grid.ViewGridPanel.getStore().data.items.length;
							for (var r = 0; r <= Cnt - 1; r++) {

								record = grid.ViewGridPanel.getStore().data.items[r].data;
								if (record.Is_Checked == 1) {
									grid.getGrid().getSelectionModel().selectRow(r);
									record = grid.getGrid().getSelectionModel().getSelected();
									persons += record.get('Person_id') + ', ';
									win.checkOne(record.get('Person_id'))
								}
							}
						}
						var idx = win.PersonSearchViewFrame.getColumnModel().findColumnIndex('check');
						Ext.getCmp('swPersonSearchWindow4DrugRequest').PersonSearchViewFrame.getGrid().getColumnModel().setColumnHeader(idx, '<input type="checkbox" id="PSPCAW_checkAll" onClick="getWnd(\'swPersonSearchWindow4DrugRequest\').checkAll(this.checked, this);">');

						win.onPersonSelectList(persons);
					},
					tabIndex: TABINDEX_AUTOINC++
				},
				{
					text: '-'
				},
				HelpButton(win, TABINDEX_AUTOINC++),
				{
					handler: function () {
						win.hide()
					},
					iconCls: 'cancel16',
					onShiftTabAction: function () {
						win.buttons[win.buttons.length - 2].focus();
					},
					onTabAction: function () {
						win.FilterPanel.getForm().findField('PersonSurName_SurName').focus(true, 100);
					},
					text: BTN_FRMCLOSE,
					tabIndex: TABINDEX_AUTOINC++
				}],
			keys: [{
					key: Ext.EventObject.INSERT,
					fn: function (inp, e) {
						e.stopEvent();

						if (e.browserEvent.stopPropagation)
							e.browserEvent.stopPropagation();
						else
							e.browserEvent.cancelBubble = true;

						if (e.browserEvent.preventDefault)
							e.browserEvent.preventDefault();
						else
							e.browserEvent.returnValue = false;

						e.browserEvent.returnValue = false;
						e.returnValue = false;

						if (Ext.isIE) {
							e.browserEvent.keyCode = 0;
							e.browserEvent.which = 0;
						}
						win.PersonSearchViewFrame.getAction('action_add').handler();
					},
					stopEvent: true
				}, {
					key: Ext.EventObject.ENTER,
					fn: function (inp, e) {
						e.stopEvent();

						if (e.browserEvent.stopPropagation)
							e.browserEvent.stopPropagation();
						else
							e.browserEvent.cancelBubble = true;

						if (e.browserEvent.preventDefault)
							e.browserEvent.preventDefault();
						else
							e.browserEvent.returnValue = false;

						e.browserEvent.returnValue = false;
						e.returnValue = false;

						if (Ext.isIE) {
							e.browserEvent.keyCode = 0;
							e.browserEvent.which = 0;
						}
						win.onOkButtonClick();
					},
					stopEvent: true
				}, {
					key: Ext.EventObject.F4,
					fn: function (inp, e) {
						if (e.altKey || e.ctrlKey || e.shiftKey)
							return true;
						e.stopEvent();

						if (e.browserEvent.stopPropagation)
							e.browserEvent.stopPropagation();
						else
							e.browserEvent.cancelBubble = true;

						if (e.browserEvent.preventDefault)
							e.browserEvent.preventDefault();
						else
							e.browserEvent.returnValue = false;

						e.browserEvent.returnValue = false;
						e.returnValue = false;

						if (Ext.isIE) {
							e.browserEvent.keyCode = 0;
							e.browserEvent.which = 0;
						}
						win.PersonSearchViewFrame.getAction('action_edit').handler();
					},
					stopEvent: true
				}, {
					key: Ext.EventObject.F3,
					fn: function (inp, e) {
						if (e.altKey || e.ctrlKey || e.shiftKey)
							return true;
						e.stopEvent();

						if (e.browserEvent.stopPropagation)
							e.browserEvent.stopPropagation();
						else
							e.browserEvent.cancelBubble = true;

						if (e.browserEvent.preventDefault)
							e.browserEvent.preventDefault();
						else
							e.browserEvent.returnValue = false;

						e.browserEvent.returnValue = false;
						e.returnValue = false;

						if (Ext.isIE) {
							e.browserEvent.keyCode = 0;
							e.browserEvent.which = 0;
						}
						win.PersonSearchViewFrame.getAction('action_view').handler();
					},
					stopEvent: true
				}, {
					alt: true,
					fn: function (inp, e) {
						if (e.browserEvent.stopPropagation)
							e.browserEvent.stopPropagation();
						else
							e.browserEvent.cancelBubble = true;
						if (e.browserEvent.preventDefault)
							e.browserEvent.preventDefault();
						else
							e.browserEvent.returnValue = false;
						e.browserEvent.returnValue = false;
						e.returnValue = false;

						if (Ext.isIE) {
							e.browserEvent.keyCode = 0;
							e.browserEvent.which = 0;
						}

						if (e.getKey() == Ext.EventObject.J) {

							win.buttons[2].handler();
							return false;
						}
						if (e.getKey() == Ext.EventObject.C) {
							win.buttons[1].handler();
							return false;
						}
					},
					key: [
						Ext.EventObject.C,
						Ext.EventObject.J,
						Ext.EventObject.NUM_ONE,
						Ext.EventObject.NUM_TWO,
						Ext.EventObject.ONE,
						Ext.EventObject.TWO
					],
					scope: this,
					stopEvent: false
				}]
		});

		sw.Promed.swPersonSearchWindow4DrugRequest.superclass.initComponent.apply(this, arguments);
	}
});