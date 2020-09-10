/**
* swPersonSearchWindow - окно окно поиска людей.
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

sw.Promed.swPersonSearchWindow = Ext.extend(sw.Promed.BaseForm, {
	layout: 'border',
	width: 900,
	height: 600,
	formParams: null,
	modal: true,
	resizable: false,
	draggable: true,
	closeAction : 'hide',
	buttonAlign: 'left',
	title:WND_PERS_SEARCH,
	id: 'person_search_window',
	listeners: {
		'beforehide': function()
		{
			sw.Applets.uec.stopUecReader();
			sw.Applets.BarcodeScaner.stopBarcodeScaner();
		},
		'hide': function() {
			this.onWinClose();
		}
	},
	plain: true,
	searchWindowOpenMode: null,
	onWinClose: function() {},
	onPersonSelect: function() {},
	onOkButtonClick: function(callback_data) {
		sw.Applets.uec.stopUecReader();
		sw.Applets.BarcodeScaner.stopBarcodeScaner();

		var grid = this.findById('PersonSearchGrid');
		var selected_record = grid.getSelectionModel().getSelected();
		if ( selected_record )
		{
			// это для того, чтобы можно было редактировать добавленного человека
			if ( callback_data && (callback_data.Person_id > 0) )
			{
				//this.findById('person_search_form').getForm().reset();
				var data_to_return = {};
				Ext.apply(data_to_return, selected_record.data);
				data_to_return.onHide = function() {
						var index = grid.getStore().findBy(function(rec) { return rec.get('Person_id') == selected_record.data.Person_id; });
						grid.focus();
						grid.getView().focusRow(index);
						grid.getSelectionModel().selectRow(index);

						sw.Applets.uec.startUecReader();
						sw.Applets.BarcodeScaner.startBarcodeScaner();
					}
				data_to_return.Person_IsBDZ = String(selected_record.data.Person_IsBDZ);
				data_to_return.Person_IsFedLgot = String(selected_record.data.Person_IsFedLgot);
				data_to_return.Person_id = callback_data.Person_id;
				data_to_return.Server_id = callback_data.Server_id;
				data_to_return.PersonEvn_id = callback_data.PersonEvn_id;
				data_to_return.CmpLpu_id = callback_data.CmpLpu_id;
				this.onPersonSelect(data_to_return);
				return;
			}
			if (selected_record)
			{
				// this.findById('person_search_form').getForm().reset();
				// var data_to_return = {};
				// Ext.apply(data_to_return, selected_record.data);
				// data_to_return.Person_Birthday = selected_record.get('PersonBirthDay_BirthDay');
				// data_to_return.Person_Firname = String(selected_record.get('PersonFirName_FirName'));
				// data_to_return.Person_Secname = String(selected_record.get('PersonSecName_SecName'));
				// data_to_return.Person_Surname = String(selected_record.get('PersonSurName_SurName'));
				// data_to_return.Person_IsBDZ = String(selected_record.data.Person_IsBDZ);
				// data_to_return.Polis_Ser = selected_record.data.Polis_Ser;
				// data_to_return.Polis_Num = selected_record.data.Polis_Num;
				// data_to_return.Polis_EdNum = selected_record.data.Polis_EdNum;
				// data_to_return.Person_IsFedLgot = String(selected_record.data.Person_IsFedLgot);
				// data_to_return.CmpLpu_id = selected_record.data.CmpLpu_id;
				// data_to_return.onHide = function() {
						// var index = grid.getStore().find('Person_id', selected_record.data.Person_id)
						// grid.focus();
						// grid.getView().focusRow(index);
						// grid.getSelectionModel().selectRow(index);

						// sw.Applets.uec.startUecReader();
						// sw.Applets.BarcodeScaner.startBarcodeScaner();
					// }
				// this.onPersonSelect(data_to_return);
				
				var that = this;
		    function selectedFunc() {
		        // that.findById('person_search_form').getForm().reset(); // сброс формы после кнопки "Выбор"
		        var data_to_return = {};

		        myrec = selected_record;
		        Ext.apply(data_to_return, selected_record.data);
		        data_to_return.Person_Birthday = selected_record.get('PersonBirthDay_BirthDay');
		        data_to_return.Person_Firname = String(selected_record.get('PersonFirName_FirName'));
		        data_to_return.Person_Secname = String(selected_record.get('PersonSecName_SecName'));
		        data_to_return.Person_Surname = String(selected_record.get('PersonSurName_SurName'));
		        data_to_return.Person_IsBDZ = String(selected_record.data.Person_IsBDZ);
		        data_to_return.Polis_Ser = selected_record.data.Polis_Ser;
		        data_to_return.Polis_Num = selected_record.data.Polis_Num;
		        data_to_return.Polis_EdNum = selected_record.data.Polis_EdNum;
		        data_to_return.Person_IsFedLgot = String(selected_record.data.Person_IsFedLgot);
		        data_to_return.CmpLpu_id = selected_record.data.CmpLpu_id;
		        data_to_return.onHide = function () {
		            var index = grid.getStore().findBy(function(rec) { return rec.get('Person_id') == selected_record.data.Person_id; });
		            grid.focus();
		            grid.getView().focusRow(index);
		            grid.getSelectionModel().selectRow(index);

		            sw.Applets.bdz.startBdzReader();
		            sw.Applets.BarcodeScaner.startBarcodeScaner();
		        }


		        console.log('### data to return!!! ', data_to_return);
		        that.onPersonSelect(data_to_return);
		    }

		    var tfoms = !!selected_record.get('ENP');
		    var enp = selected_record.get('ENP');

		    if (tfoms) {
		        function saveTFOMS() {
		            Ext.Ajax.request({
		                url: '/?c=samara_Person&m=saveFromTFOMS',
		                params: {
		                    'ENP': enp,
		                    'Person_id': selected_record.get('Person_id')
		                },
		                callback: function (opt, success, response) {
		                    myargs = arguments;
		                    if (!success) return;

		                    var resp = Ext.util.JSON.decode(response.responseText);
	                        selected_record.set('Person_id', resp[0].Person_id);
	                        selected_record.set('PersonEvn_id', resp[0].PersonEvn_id);
							selected_record.set('PersonEvn_id', resp[0].Server_id);
	                        selectedFunc();
		                }
		            });
		        }

		        function checkPerson() {
		            Ext.Ajax.request({
		                url: '/?c=samara_Person&m=checkPersonDoublesSamara',
		                params: {
		                    'Person_SurName': selected_record.get('PersonSurName_SurName'),
		                    'Person_FirName': selected_record.get('PersonFirName_FirName'),
		                    'Person_SecName': selected_record.get('PersonSecName_SecName'),
		                    'Person_BirthDay': selected_record.get('PersonBirthDay_BirthDay').format('d.m.Y')
		                },
		                callback: function (opt, success, response) {
		                    myargs = arguments;
		                    if (success) {
		                        var resp = Ext.util.JSON.decode(response.responseText);

		                        myresp = resp;
		                        if (resp.length === 0) {
		                            saveTFOMS();
		                        } else if (resp.length) {
		                            sw.swMsg.show({
		                                title: 'Подтверждение обновления информации о человеке',
		                                msg: 'Данный пациент присутствует в локальной базе. Обновить информацию о пациенте?',
		                                buttons: Ext.Msg.YESNO,
		                                fn: function (buttonId) {
		                                    if (buttonId === 'yes') {
		                                        console.log(resp[0].Person_id);
		                                        selected_record.set('Person_id', resp[0].Person_id);
		                                        saveTFOMS();
		                                    }
		                                }
		                            });
		                        }
		                        
		                    }
		                }
		            });
		        }

		        
		        checkPerson();

                 

		    } else {
		        selectedFunc();
		    }
				
			}
			else
			{
				this.hide();
			}
		}
	},
	getDataFromUec: function(uecData, person_data) {
		var form = this.findById('person_search_form').getForm();
		if (uecData.success) {
			form.findField('PersonFirName_FirName').setValue(uecData.firName);
			form.findField('PersonSecName_SecName').setValue(uecData.SecName);
			form.findField('PersonSurName_SurName').setValue(uecData.surName);
			form.findField('PersonBirthDay_BirthDay').setValue(uecData.birthDay);
			form.findField('Polis_Num').setValue(uecData.polisNum);
			var callback = function() {
				var grid = this.findById('PersonSearchGrid');
				if ( grid.getStore().getCount() == 1 ) {
					this.onOkButtonClick();
				}
			}.createDelegate(this);
			this.doSearch(false, callback);
		}
	},
	getDataFromBarcode: function(barcodeData, person_data) {
		// sw.Applets.BarcodeScaner.stopBarcodeScaner();

		if ( person_data.Person_id ) {
			this.onPersonSelect({
				 Person_id: person_data.Person_id
				,PersonEvn_id: person_data.PersonEvn_id
				,Server_id: person_data.Server_id
				,onHide: function() {
					// sw.Applets.BarcodeScaner.startBarcodeScaner();
				}
			});
		}
	},
	show: function() {
		sw.Promed.swPersonSearchWindow.superclass.show.apply(this, arguments);

		this.center();

		// флаг того, что осуществлялся поиск
		this.isSearched = false;

		// объект с данными, передаваемыми в дочернюю форму
		// можно использовать в onSelect
		this.formParams = new Object();

		// этот параметр определяет объект, для которой был вызван поиск человека
		this.searchWindowOpenMode = null;

		// разрешение на добавление неизвестного человека
		this.allowUnknownPerson = false;

		this.PersonEditWindow = getWnd('swPersonEditWindow');

		var form = this.findById('person_search_form').getForm();
		form.reset();
		
		form.findField('PersonSurName_SurName').focus(true, 500);
		var grid = this.findById('PersonSearchGrid');

		grid.getStore().removeAll();
		//grid.getStore().baseParams = form.getValues();
		grid.getTopToolbar().items.items[1].disable();
		grid.getTopToolbar().items.items[2].disable();
		grid.getTopToolbar().items.items[4].disable();
		grid.getTopToolbar().items.items[6].el.innerHTML = '0 / 0';

		sw.Applets.BarcodeScaner.startBarcodeScaner({ callback: this.getDataFromBarcode.createDelegate(this) });

		if ( arguments[0] )
		{
			if ( arguments[0].formParams && typeof arguments[0].formParams == 'object' ) {
				this.formParams = arguments[0].formParams;
			}

			if (arguments[0].needUecIdentification) {
				sw.Applets.uec.startUecReader({callback: this.getDataFromUec.createDelegate(this)});
			}

			if ( arguments[0].personFirname )
				form.findField('PersonFirName_FirName').setRawValue(arguments[0].personFirname);

			if ( arguments[0].personSecname )
				form.findField('PersonSecName_SecName').setRawValue(arguments[0].personSecname);

			if ( arguments[0].personSurname )
				form.findField('PersonSurName_SurName').setRawValue(arguments[0].personSurname);

			if( arguments[0].PersonBirthDay_BirthDay )
				form.findField('PersonBirthDay_BirthDay').setRawValue(arguments[0].PersonBirthDay_BirthDay);
				
			if ( arguments[0].onClose )
				this.onWinClose = arguments[0].onClose;
			else
				this.onClose = Ext.emptyFn;

			if ( arguments[0].onSelect )
				this.onPersonSelect = arguments[0].onSelect;
			else
				this.onClose = Ext.emptyFn;

			if ( arguments[0].searchMode )
				this.searchMode = arguments[0].searchMode;
			else
				this.searchMode = 'all';
			
			if ( arguments[0].Year )
				this.Year = arguments[0].Year;
			else
				this.Year = null;
			
			if (this.searchMode=='wow')
			{
				this.setTitle(WND_PERS_SEARCH+' (только по регистру ВОВ)');
			}
			else
			{
				this.setTitle(WND_PERS_SEARCH);
			}
			
			if ((this.searchMode=='attachrecipients') && (!getGlobalOptions().isMinZdrav) && (!getGlobalOptions().isOnko) && (!getGlobalOptions().isOnkoGem) && (!getGlobalOptions().isPsih)  && (!getGlobalOptions().isRA))
			{
				this.setTitle(WND_PERS_SEARCH+' (прикрепленные льготники)');
			}
			else
			{
				this.setTitle(WND_PERS_SEARCH);
			}
			
			if ( arguments[0].searchWindowOpenMode ) {
				this.searchWindowOpenMode = arguments[0].searchWindowOpenMode;
			}

			if ( arguments[0].childPS ) {
				this.openChildPS = true;
			} else {
				this.openChildPS = false;
			}

			if ( arguments[0].allowUnknownPerson ) {
				this.allowUnknownPerson = arguments[0].allowUnknownPerson;
			}
		}
		
		form.findField('Person_Snils').setValue('');
		form.findField('ParentARM').setValue(arguments[0].ARMType || '');
		form.findField('PersonCard_Code').setValue('');
		form.findField('EvnPS_NumCard').setValue('');
		form.findField('Polis_Ser').setValue(arguments[0].Polis_Ser || '');
		form.findField('Polis_Num').setValue(arguments[0].Polis_Num || '');
		form.findField('Polis_EdNum').setValue(arguments[0].Polis_EdNum || '');
		form.findField('PersonAge_AgeFrom').setValue( arguments[0].Person_Age || '' );
		form.findField('PersonAge_AgeTo').setValue( arguments[0].Person_Age || '' );
		form.findField('EvnUdost_Ser').setValue('');
		form.findField('EvnUdost_Num').setValue('');
		// form.findField('PersonFirName_FirName').setValue('');
		// form.findField('PersonSecName_SecName').setValue('');
		//form.findField('PersonBirthDay_BirthDay').setValue('');
		
		grid.getStore().baseParams.searchMode = this.searchMode;
	},
	searchInProgress: false,
	doSearch: function(params, searchCallBack, tfoms) {
		if (this.searchInProgress) {
			log('Поиск уже выполняется!');
			return false;
		} else {
			this.searchInProgress = true;
		}
		var thisWindow = this;
		if ( params && params['soc_card_id'] )
			var soc_card_id = params['soc_card_id'];
		var grid = this.findById('PersonSearchGrid');
		var top_toolbar = grid.getTopToolbar();
		top_toolbar.items.items[1].disable();
		top_toolbar.items.items[2].disable();
		top_toolbar.items.items[4].disable();
		var form = this.findById('person_search_form');
		var vals = form.getForm().getValues();
		var flag = true;
		//for ( value in vals )
		//{
			//if ( vals[value] != "" )
				//flag = false;
		//}
		if (
			form.getForm().findField('Person_Snils').getValue() != ''
			|| form.getForm().findField('PersonCard_Code').getValue() != ''
			|| form.getForm().findField('EvnPS_NumCard').getValue() != ''			
			|| ( form.getForm().findField('Polis_Num').getValue() != '' )
			|| form.getForm().findField('Polis_EdNum').getValue() != ''
			|| ( form.getForm().findField('EvnUdost_Ser').getValue() != '' && form.getForm().findField('EvnUdost_Num').getValue() != '' )
			|| ( form.getForm().findField('Person_id').getValue() != '' )
			|| ( form.getForm().findField('PersonFirName_FirName').getValue() != '' && form.getForm().findField('PersonSecName_SecName').getValue() != '' && form.getForm().findField('PersonBirthDay_BirthDay').getValue() != '' )
		)
			flag = false;			
			
		if ( (flag && form.getForm().findField('PersonSurName_SurName').getValue()=='') && !soc_card_id )
		{
			var win = this;
			Ext.Msg.alert("Сообщение", "Не заполнены обязательные поля. Возможные варианты поиска:<br/>"+
				(isAdmin ? "Поиск по Person_id.<br/>" : "") +
				"Поиск по фамилии.<br/>"+
				"Поиск по совпадению имени, отчества и даты рождения.<br/>"+
				"Поиск по точному совпадению СНИЛС.<br/>"+
				"Поиск по точному совпадению номера амбулаторной карты.<br/>"+
				"Поиск по точному совпадению номера КВС.<br/>"+
				"Поиск по точному совпадению номера полиса.<br/>"+
				"Поиск по точному совпадению ЕНП.<br/>"+
				"Поиск по точному совпадению серии и номера удостоверения льготника.<br/>"+
				"Поиск по совпадению имени, отчества и даты рождения.<br/>"
				, function() {
				form.getForm().findField('PersonSurName_SurName').focus(true, 100);
			});
			thisWindow.searchInProgress = false;
			return false;
		}
		grid.getStore().removeAll();
		var Mask = new Ext.LoadMask(Ext.get('person_search_window'), {msg:SEARCH_WAIT});
		Mask.show();
		var wnd = this;
		if ( soc_card_id )
		{
			var params = {
				soc_card_id: soc_card_id/*,
				PersonSurName_SurName: '%'*/
			};
			var baseParams = params;
		}
		else
		{
			var params = form.getForm().getValues();
			var baseParams = form.getForm().getValues();
		}		
		params.searchMode = this.searchMode;		
		baseParams.searchMode = this.searchMode;
		if ( this.Year ) {
			params.Year = this.Year;
			baseParams.Year = this.Year;
		}
		grid.getStore().baseParams = baseParams;
		params.start = 0;
		params.limit = 100;
		this.isSearched = true;
		
		if (tfoms) {
		    params.SearchType = 'tfoms';
		}
		
		grid.getStore().load({
			params: params,
			failure: function(){
				console.log('back '  + arguments);
			},
			callback: function(r, opt, success) {
				thisWindow.searchInProgress = false;
				Mask.hide();
				
				if (!success) {
					Ext.Msg.alert('', 'Сервер ТФОМС временно не доступен, попробуйте позже');
					return;
				}
				if ( r.length > 0 )
				{
					var len = r.length;
					grid.getView().focusRow(0);
					grid.getSelectionModel().selectFirstRow();
					grid.getTopToolbar().items.items[6].el.innerHTML = '1 / ' + len;
				}
				else
				{
					grid.getTopToolbar().items.items[6].el.innerHTML = '0 / 0';
				}
				
				if (searchCallBack && typeof searchCallBack == 'function') {
					searchCallBack();
				}
			}
		});
	},
	initComponent: function() {
		var connObj = new Ext.data.Connection({ 
            timeout : 5000, 
            url: '/?c=samara_Person&m=getPersonSearchGrid', 
            method : 'POST' 
        });

		
		var gridStore = new Ext.data.JsonStore({
			autoLoad: false,
			root: 'data',
			totalProperty: 'totalCount',
			proxy : new Ext.data.HttpProxy(connObj), 
//			url: '/?c=samara_Person&m=getPersonSearchGrid',
			fields: [
				'Person_id',
				'Server_id',
				'PersonEvn_id',
				'Sex_id',
				'Polis_Ser',
				'Polis_Num',
				'Polis_EdNum',
				'PersonSurName_SurName',
				'PersonFirName_FirName',
				'PersonSecName_SecName',
				'CmpLpu_id',
				'ENP',
				{name: 'PersonBirthDay_BirthDay', type: 'date', dateFormat: 'd.m.Y'},
				'Lpu_Nick',
				{name: 'PersonCard_IsDms', type: 'string'},
				{name: 'Person_IsBDZ', type: 'string'},
				{ name: 'Person_IsTFOMS', type: 'string' },
				{name: 'Person_IsFedLgot', type: 'string'},
				{name: 'Person_IsRegLgot', type: 'string'},
				{name: 'Person_Is7Noz', type: 'string'},
				{name: 'Person_IsRefuse', type: 'string'},
				{name: 'Person_IsDead', type: 'string'}
			],
			listeners: {
				'load': function() {
					var grid = Ext.getCmp('PersonSearchGrid');
					if ( grid.getStore().getCount() > 0 )
					{
						for (var i=0; i<=(grid.getStore().getCount()-1); i++)
						{
							var row = grid.getStore().getAt(i);
							if ( row.data.Person_IsBDZ == 'red' )
								grid.getView().getRow(i).style.color = 'gray';
						}
					}
				}
			}
		});
		
		var current_window = this;
		
		Ext.apply(this, {
			items: [
				new Ext.form.FormPanel({
					frame: true,
					autoHeight: true,
					region: 'north',
					id: 'person_search_form',
					autoLoad: false,
					buttonAlign: 'left',
					bodyStyle:'background:#FFF;padding:0;',
					items: [
					{
						autoHeight: true,
						style: 'padding: 5px',
						layout:'form',
						labelAlign: 'top',
						labelWidth: 95,
						items: [
						{
							layout: 'column',
							items: [
							{
								layout: 'form',
								columnWidth: 1,
								items: [{
									xtype: 'fieldset',
									autoHeight: true,
									collapsible: true,
									listeners: {
										collapse: function(p) {
											current_window.doLayout();
										},
										expand: function(p) {
											current_window.doLayout();
										}
									},
									title: 'Пациент',
									style: 'padding: 2px; padding-left: 10px',
									items: [{
										layout: 'column',
										items: [{
											layout: 'form',
											columnWidth: .33,
											items: [{
												xtype: 'swtranslatedtextfield',
												fieldLabel: 'Фамилия',
												maskRe: /[^%]/,
												name: 'PersonSurName_SurName',
												anchor: '95%',
												tabIndex: TABINDEX_PERSSEARCH + 0,
												listeners: {
													'keydown': function (inp, e) {
														if (e.shiftKey == false && e.getKey() == Ext.EventObject.TAB)
														{
															e.stopEvent();
															Ext.getCmp("person_search_form").getForm().findField("PersonFirName_FirName").focus(true);
														}
													}
												}
											}]
										},{
											name: 'ParentARM',
											value: '',
											xtype: 'hidden'
										}, {
											layout: 'form',
											columnWidth: .33,
											items: [{
												xtype: 'swtranslatedtextfield',
												maskRe: /[^%]/,
												fieldLabel: 'Имя',
												name: 'PersonFirName_FirName',
												anchor: '95%',
												tabIndex: TABINDEX_PERSSEARCH + 1,
												listeners: {
													'keydown': function (inp, e) {
														if (e.shiftKey == true && e.getKey() == Ext.EventObject.TAB)
														{
															e.stopEvent();
															Ext.getCmp("person_search_form").getForm().findField("PersonSurName_SurName").focus(true);
														}
													}
												}

											}]
										},{
											layout: 'form',
											columnWidth: .33,
											items: [{
												xtype: 'swtranslatedtextfield',
												maskRe: /[^%]/,
												fieldLabel: 'Отчество',
												name: 'PersonSecName_SecName',
												anchor: '95%',
												tabIndex: TABINDEX_PERSSEARCH + 2
											}]
										}]
									},{
										layout: 'column',
										items: [{
											layout: 'form',
											columnWidth: .2,
											items: [{
												xtype: 'swdatefield',
												plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
												fieldLabel: 'Дата рождения',
												format: 'd.m.Y',
												name: 'PersonBirthDay_BirthDay',
												tabIndex: TABINDEX_PERSSEARCH + 3
											}]
										},{
											layout: 'form',
											columnWidth: .2,
											items: [{
												xtype: 'numberfield',
												fieldLabel: 'Возраст с ',
												name: 'PersonAge_AgeFrom',
												allowNegative: false,
												allowDecimals: false,
												autoCreate: {tag: "input", type: "text", size: "11", maxLength: "3", autocomplete: "off"},
												tabIndex: TABINDEX_PERSSEARCH + 4
											}]
										},{
											layout: 'form',
											columnWidth: .2,
											items: [{
												xtype: 'numberfield',
												fieldLabel: 'по',
												name: 'PersonAge_AgeTo',
												allowNegative: false,
												allowDecimals: false,
												autoCreate: {tag: "input", type: "text", size: "11", maxLength: "3", autocomplete: "off"},
												tabIndex: TABINDEX_PERSSEARCH + 5
											}]
										},{
											layout: 'form',
											columnWidth: .2,
											items: [{
												xtype: 'numberfield',
												fieldLabel: 'Год рождения с',
												name: 'PersonBirthYearFrom',
												allowNegative: false,
												allowDecimals: false,
												autoCreate: {tag: "input", type: "text", size: "11", maxLength: "4", autocomplete: "off"},
												tabIndex: TABINDEX_PERSSEARCH + 6
											}]
										},{
											layout: 'form',
											columnWidth: .2,
											items: [{
												xtype: 'numberfield',
												fieldLabel: 'по',
												name: 'PersonBirthYearTo',
												allowNegative: false,
												allowDecimals: false,
												autoCreate: {tag: "input", type: "text", size: "11", maxLength: "4", autocomplete: "off"},
												enableKeyEvents: true,
												tabIndex: TABINDEX_PERSSEARCH + 7
											}]
										}]
									}, {
										layout: 'column',
										items:
										[{
											columnWidth: .24,
											labelWidth: 50,
											layout: 'form',
											hidden: !isAdmin,
											items: [
												{
													xtype: 'swtranslatedtextfield',
													maskRe: /[\d]/,
													width: 130,
													name: 'Person_id',
													fieldLabel: 'Person_id',
													tabIndex: TABINDEX_PERSSEARCH + 8
												}
											]
										}, {
											columnWidth: .24,
											labelWidth: 110,
											layout: 'form',
											items: [
												{
													xtype: 'textfield',													
													maskRe: /\d/,
													fieldLabel: 'СНИЛС',													
													maxLength: 11,
													minLength: 11,
													autoCreate: {tag: "input", type: "text", size: "11", maxLength: "11", autocomplete: "off"},
													width: 130,
													name: 'Person_Snils',
													tabIndex: TABINDEX_PERSSEARCH + 9
												}
											]
										}]
									}]
								}, {
									xtype: 'fieldset',
									autoHeight: true,
									collapsible: true,
									listeners: {
										collapse: function(p) {
											current_window.doLayout();
										},
										expand: function(p) {
											current_window.doLayout();
										}
									},
									title: 'Полис',
									style: 'padding: 2px; padding-left: 10px',
									items: [{
										layout: 'column',
										items:
										[{
											columnWidth: .24,
											labelWidth: 50,
											layout: 'form',
											items: [
												{
													xtype: 'textfield',
													maskRe: /[^%]/,
													fieldLabel: 'Серия',
													width: 130,
													name: 'Polis_Ser',
													tabIndex: TABINDEX_PERSSEARCH + 10
												}
											]
										}, {
											columnWidth: .24,
											labelWidth: 110,
											layout: 'form',
											items: [
												{
													xtype: 'textfield',
													maskRe: /[^%]/,
													fieldLabel: 'Номер',
													width: 130,
													name: 'Polis_Num',
													tabIndex: TABINDEX_PERSSEARCH + 11
												}
											]
										}, {
											columnWidth: .24,
											labelWidth: 95,
											layout: 'form',
											items: [
												{
													xtype: 'textfield',
													maskRe: /[^%]/,
													fieldLabel: 'Единый номер',
													width: 130,
													name: 'Polis_EdNum',
													tabIndex: TABINDEX_PERSSEARCH + 12
												}
											]
										}]
									}]
								}, {
									xtype: 'fieldset',
									autoHeight: true,
									collapsible: true,
									listeners: {
										collapse: function(p) {
											current_window.doLayout();
										},
										expand: function(p) {
											current_window.doLayout();
										}
									},
									title: 'Мед. документы',
									style: 'padding: 2px; padding-left: 10px',
									items: [{
										layout: 'column',
										items:
										[{
											columnWidth: .24,
											labelWidth: 110,
											layout: 'form',
											items: [
												{
													xtype: 'textfield',
													maskRe: /[^%]/,
													fieldLabel: 'Номер амб. карты',
													width: 130,
													name: 'PersonCard_Code',
													tabIndex: TABINDEX_PERSSEARCH + 13
												}
											]
										}, {
											columnWidth: .24,
											labelWidth: 95,
											layout: 'form',
											items: [
												{
													xtype: 'textfield',
													maskRe: /[^%]/,
													fieldLabel: 'Номер КВС',
													width: 130,
													name: 'EvnPS_NumCard',
													tabIndex: TABINDEX_PERSSEARCH + 14
												}
											]
										}]
									}]
								}, {
									xtype: 'fieldset',
									autoHeight: true,
									collapsible: true,
									listeners: {
										collapse: function(p) {
											current_window.doLayout();
										},
										expand: function(p) {
											current_window.doLayout(); current_window.doLayout(); // с одного раза почему то не лэйоутится
										}
									},
									collapsed: true,
									title: 'Удостоверения',
									style: 'padding: 2px; padding-left: 10px',
									items: [{
										layout: 'column',
										items:
										[{
											columnWidth: .24,
											labelWidth: 50,
											layout: 'form',
											items: [
												{
													xtype: 'textfield',
													maskRe: /[^%]/,
													fieldLabel: 'Серия',
													width: 130,
													name: 'EvnUdost_Ser',
													tabIndex: TABINDEX_PERSSEARCH + 15
												}
											]
										}, {
											columnWidth: .24,
											labelWidth: 110,
											layout: 'form',
											items: [
												{
													xtype: 'textfield',
													maskRe: /[^%]/,
													fieldLabel: 'Номер',
													width: 130,
													name: 'EvnUdost_Num',
													tabIndex: TABINDEX_PERSSEARCH + 16
												}
											]
										}]
									}]
								}]
							},
							{
								width: 65,
								style: 'padding: 5px;',
								layout: 'form',
								items: [{											
									xtype: 'button',
									hidden: !getGlobalOptions()['card_reader_is_enable'],
									cls: 'x-btn-large',
									iconCls: 'idcard32',
									tooltip: 'Идентифицировать по карте и найти',
									handler: function() {												
										var response = getSocCardNumFromReader();
										if ( response.success == true )
											Ext.getCmp("person_search_window").doSearch({soc_card_id: response.SocCard_id});
										else
											Ext.Msg.alert("Ошибка", response.ErrorMessage);
									}
								}]									
							}]
						}]
					}],
					keys: [{
						key: Ext.EventObject.ENTER,
						fn: function(e) {
							Ext.getCmp('person_search_window').doSearch();
						},
						stopEvent: true
					}]
				}),
				new Ext.grid.EditorGridPanel({
					bbar: new Ext.PagingToolbar ({
						store: gridStore,
						pageSize: 100,
						displayInfo: true,
						displayMsg: 'Отображаемые строки {0} - {1} из {2}',
						emptyMsg: "Нет записей для отображения"
					}),
					region: 'center',
					tabIndex: TABINDEX_PERSSEARCH + 17,
					tbar: new sw.Promed.Toolbar({
						autoHeight: true,
						buttons: [{
							text: BTN_GRIDADD,
							iconCls: 'add16',
							handler: function() {
								var win = this.ownerCt.ownerCt.ownerCt;
								var form = win.findById('person_search_form').getForm();
								var grid = this.ownerCt.ownerCt;
								if ( win.isSearched /*&& grid.getStore().getCount() == 0*/ )
								{
									sw.swMsg.show({
										title: 'Подтверждение добавления человека',
										msg: 'Внимательно проверьте введенную информацию по поиску человека! Вы точно хотите добавить нового человека?',
										buttons: Ext.Msg.YESNO,
										fn: function ( buttonId ) {
											if ( buttonId == 'yes' )
											{
												getWnd('swPersonEditWindow').show({
													action: 'add',
													allowUnknownPerson: current_window.allowUnknownPerson,
													fields: {
														// не стал убирать передачу этих параметров, вдруг захотят обратно вернуть
														// :) захотели (refs #12322)
														'Person_SurName': form.findField('PersonSurName_SurName').getValue().toUpperCase(),
														'Person_FirName': form.findField('PersonFirName_FirName').getValue().toUpperCase(),
														'Person_SecName': form.findField('PersonSecName_SecName').getValue().toUpperCase(),
														'Person_BirthDay': form.findField('PersonBirthDay_BirthDay').getValue(),
														'Person_SNILS': form.findField('Person_Snils').getValue()
													},
													callback: function(callback_data) {
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
														Ext.getCmp('person_search_window').doSearch();
														getWnd('swPersonEditWindow').hide();
														win.onOkButtonClick(callback_data);
													},
													onClose: function() {
														form.findField('PersonSurName_SurName').focus(true, 500);
													}
												});
											}
											else
											{
												form.findField('PersonSurName_SurName').focus(true, 500);
											}
										}
									});
									return false;
								}
								getWnd('swPersonEditWindow').show({
									action: 'add',
									allowUnknownPerson: current_window.allowUnknownPerson,
									fields: {
										// не стал убирать передачу этих параметров, вдруг захотят обратно вернуть
										'Person_SurNameEdit': '',
										'Person_FirNameEdit': '',
										'Person_SecNameEdit': ''
									},
									callback: function(callback_data) {
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
										Ext.getCmp('person_search_window').doSearch();
										getWnd('swPersonEditWindow').hide();
										win.onOkButtonClick(callback_data);
									},
									onClose: function() {
										form.findField('PersonSurName_SurName').focus(true, 500);
									}
								});
							}
						}, {
							text: BTN_GRIDEDIT,
							iconCls: 'edit16',
							handler: function() {
								var win = this.ownerCt.ownerCt.ownerCt;
								var grid = this.ownerCt.ownerCt;
								if ((!grid.getSelectionModel().getSelected())||(grid.getStore().getCount()==0))
									return;
								var person_id = grid.getSelectionModel().getSelected().data.Person_id;
								var server_id = grid.getSelectionModel().getSelected().data.Server_id;
								getWnd('swPersonEditWindow').show({
									action: 'edit',
									Person_id: person_id,
									Server_id: server_id,
									callback: function(callback_data) {
										// обновляем грид
										if (callback_data)
										{
											grid.getStore().each(function(record){
												if ( record.data.Person_id == callback_data.Person_id )
												{
													record.set('Server_id', callback_data.Server_id);
													record.set('PersonEvn_id', callback_data.PersonEvn_id);
													record.set('PersonSurName_SurName', callback_data.PersonData.Person_SurName);
													record.set('PersonFirName_FirName', callback_data.PersonData.Person_FirName);
													record.set('PersonSecName_SecName', callback_data.PersonData.Person_SecName);
													record.set('PersonBirthDay_BirthDay', callback_data.PersonData.Person_BirthDay);
													record.commit();
												}
											});
										}
										grid.getView().focusRow(0);
									},

									onClose: function() {
										grid.getView().focusRow(0);
									}
								});
							}
						}, {
							text: BTN_GRIDVIEW,
							iconCls: 'view16',
							handler: function() {
								var win = this.ownerCt.ownerCt.ownerCt;
								var grid = this.ownerCt.ownerCt;
								if ((!grid.getSelectionModel().getSelected())||(grid.getStore().getCount()==0))
									return;
								var person_id = grid.getSelectionModel().getSelected().data.Person_id;
								var server_id = grid.getSelectionModel().getSelected().data.Server_id;
								getWnd('swPersonEditWindow').show({
									readOnly: true,
									Person_id: person_id,
									Server_id: server_id,
									callback: function(callback_data) {
										grid.getView().focusRow(0);
									},
									onClose: function() {
										grid.getView().focusRow(0);
									}
								});
							}
						}, {
							xtype: 'tbseparator'
						}, {
							text: 'Это двойник',
							iconCls: 'actions16',
							handler: function() {
								var grid = this.ownerCt.ownerCt;
								AddPersonToUnion(
									grid.getSelectionModel().getSelected(),
									function () {
										grid.getStore().reload();
									}
								);
							}
						}, {
							xtype: 'tbfill'
						}, {
							text: '0 / 0',
							xtype: 'tbtext'
						}]
					}),
					id: 'PersonSearchGrid',
					autoExpandColumn: 'autoexpand',
					autoExpandMin: 120,
					stripeRows: true,
//					plugins:checkColumn,
					store: gridStore,
					columns: [
						{dataIndex: 'Person_id', hidden: !isAdmin, hideable: false, header:'Person_id'},
						{dataIndex: 'Server_id', hidden: true, hideable: false},
						{dataIndex: 'PersonEvn_id', hidden: true, hideable: false},
						{dataIndex: 'Sex_id', hidden: true, hideable: false},
						{dataIndex: 'Polis_Ser', hidden: true, hideable: false},
						{dataIndex: 'Polis_Num', hidden: true, hideable: false},
						{dataIndex: 'Polis_EdNum', hidden: true, hideable: false},
						{dataIndex: 'Person_IsDead', hidden: true, hideable: false},
						{dataIndex: 'Person_isOftenCaller', hidden: true, hideable: false},
						{id: 'autoexpand', header: 'Фамилия', dataIndex: 'PersonSurName_SurName', sortable: true},
						{header: 'Имя', dataIndex: 'PersonFirName_FirName', sortable: true, width: 120},
						{header: 'Отчество', dataIndex: 'PersonSecName_SecName', sortable: true, width: 120},
						{header: 'Дата рождения', dataIndex: 'PersonBirthDay_BirthDay', sortable: true, width: 70, renderer: Ext.util.Format.dateRenderer('d.m.Y')},
						{ header: 'ЛПУ прикрепления', dataIndex: 'CmpLpu_id', sortable: true, width: 120 }, // Petrov Pavel
						new Ext.grid.CheckColumn({
							header: "Прикр. ДМС",
							dataIndex: 'PersonCard_IsDms',
							width: 65,
							sortable: true,
							disabled: true
						}),
						new Ext.grid.CheckColumn({
							header: "БДЗ",
							dataIndex: 'Person_IsBDZ',
							width: 35,
							sortable: true,
							disabled: true
						}),
						new Ext.grid.CheckColumn({
						    header: "ТФОМС",
						    dataIndex: 'Person_IsTFOMS',
						    width: 55,
						    sortable: true,
						    disabled: true
						}),						
						new Ext.grid.CheckColumn({
							header: "Фед. льг",
							dataIndex: 'Person_IsFedLgot',
							width: 50,
							sortable: true,
							disabled: true
						}),
						new Ext.grid.CheckColumn({
							header: "Отказ",
							dataIndex: 'Person_IsRefuse',
							width: 50,
							sortable: true,
							disabled: true
						}),
						new Ext.grid.CheckColumn({
							header: "Рег. льг",
							dataIndex: 'Person_IsRegLgot',
							width: 50,
							sortable: true,
							disabled: true
						}),
						new Ext.grid.CheckColumn({
							header: "7 ноз.",
							dataIndex: 'Person_Is7Noz',
							width: 50,
							sortable: true,
							disabled: true
						})
						/*, // Закрыл про просьбе Пермякова (по требованию минздрава(?)) - при открытии искать по полю Person_IsDead (c) Night
						new Ext.grid.CheckColumn({
							header: "Умер",
							dataIndex: 'Person_IsDead',
							width: 50,
							sortable: true,
							disabled: true
						})
						*/
					],
					sm: new Ext.grid.RowSelectionModel({
						singleSelect: true,
						listeners: {
							'rowselect': function(sm, rowIdx, r) {
								this.grid.getTopToolbar().items.items[1].enable();
								this.grid.getTopToolbar().items.items[2].enable();
								this.grid.getTopToolbar().items.items[4].enable();
								this.grid.getTopToolbar().items.items[6].el.innerHTML = String(rowIdx + 1) + ' / ' + this.grid.getStore().getCount();
							}
						}
					}),
					listeners: {
						'rowdblclick': function( grid, rowIndex )
						{
							this.ownerCt.onOkButtonClick();
						}
					},
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
						fn: function(inp, e) {
							e.stopEvent();

							if ( e.browserEvent.stopPropagation )
								e.browserEvent.stopPropagation();
							else
								e.browserEvent.cancelBubble = true;

							if ( e.browserEvent.preventDefault )
								e.browserEvent.preventDefault();
							else
								e.browserEvent.returnValue = false;

							e.browserEvent.returnValue = false;
							e.returnValue = false;

							if (Ext.isIE)
							{
								e.browserEvent.keyCode = 0;
								e.browserEvent.which = 0;
							}

							var grid = Ext.getCmp('PersonSearchGrid');

							switch (e.getKey())
							{
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
							if ( records_count > 0 && grid.getSelectionModel().getSelected() )
							{
								var selected_record = grid.getSelectionModel().getSelected();
								var params = new Object();
								params.Person_id = selected_record.get('Person_id');
								params.Server_id = selected_record.get('Server_id');
								params.Person_Birthday = Ext.util.Format.date(selected_record.get('PersonBirthDay_BirthDay'), 'd.m.Y');
								params.Person_Firname = selected_record.get('PersonFirName_FirName');
								params.Person_Secname = selected_record.get('PersonSecName_SecName');
								params.Person_Surname = selected_record.get('PersonSurName_SurName');
								params.onHide = function() {
									var index = grid.getStore().indexOf(selected_record);
									grid.focus();
									grid.getView().focusRow(index);
									grid.getSelectionModel().selectRow(index);
								}
							}
							else
							{
								return false;
							}
							if (e.getKey() == Ext.EventObject.F3) {
								if ( e.altKey ) {
									params['key_id'] = selected_record.get('Person_id');
									params['key_field'] = 'Person_id';
									if (!Ext.isEmpty(params['key_id'])) {
										getWnd('swAuditWindow').show(params);
									}
								}
							}
							if (e.getKey() == Ext.EventObject.F6)
							{
								if (!e.altKey) {
									ShowWindow('swPersonCardHistoryWindow', params);
								}
								else {
									//if (isSuperAdmin()) {
										AddPersonToUnion(
											grid.getSelectionModel().getSelected(),
											function () {
												grid.getStore().reload();
											}
										);
									//}
								}
								return false;
							}
							if (e.getKey() == Ext.EventObject.F7)
							{
								if (e.altKey) {
									if ( getGlobalOptions().region && getGlobalOptions().region.nick != 'perm' ) {
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

							if (e.getKey() == Ext.EventObject.F10)
							{
								getWnd('swPersonEditWindow').show(params);
								return false;
							}

							if (e.getKey() == Ext.EventObject.F11)
							{
								getWnd('swPersonCureHistoryWindow').show(params);
								return false;
							}

							if (e.getKey() == Ext.EventObject.F12)
							{
								if (e.ctrlKey)
								{
									getWnd('swPersonDispHistoryWindow').show(params);
								}
								else
								{
									getWnd('swPersonPrivilegeViewWindow').show(params);
								}
								return false;
							}
						},
						stopEvent: true
					}]
				})
			],
			buttons: [{
				text: BTN_FRMSEARCH,
				iconCls: 'search16',
				handler: function() { this.ownerCt.doSearch() },
				tabIndex: TABINDEX_PERSSEARCH + 18
			}, {
			    text: "Найти в ТФОМС",
			    iconCls: 'search16',
			    handler: function () {
			        this.ownerCt.doSearch(undefined,undefined,true);
			    },
			    tabIndex: TABINDEX_PERSSEARCH + 19
			},{
				text: BTN_FRMRESET,
				iconCls: 'resetsearch16',
				handler: function() {
					var form = this.ownerCt.findById('person_search_form').getForm();
					form.reset();
					form.findField('PersonSurName_SurName').focus(true, 100);
					var grid = this.ownerCt.findById('PersonSearchGrid');
					grid.getStore().removeAll();
					grid.getTopToolbar().items.items[1].disable();
					grid.getTopToolbar().items.items[2].disable();
					grid.getTopToolbar().items.items[4].disable();
					grid.getTopToolbar().items.items[6].el.innerHTML = '0 / 0';
				},
				tabIndex: TABINDEX_PERSSEARCH + 19
			},{
				iconCls: 'ok16',
				text: 'Выбрать',
				handler: function() { this.ownerCt.onOkButtonClick() },
				tabIndex: TABINDEX_PERSSEARCH + 20
			},
			{
				text: '-'
			},
			HelpButton(this, 1112),
			{
				iconCls: 'cancel16',
				text: BTN_FRMCLOSE,
				handler: function() { this.ownerCt.hide() },
				tabIndex: TABINDEX_PERSSEARCH + 21
			}
			],
			keys: [{
				key: Ext.EventObject.INSERT,
				fn: function(inp, e) {
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

					if (Ext.isIE)
					{
						e.browserEvent.keyCode = 0;
						e.browserEvent.which = 0;
					}
					Ext.getCmp('person_search_window').findById('PersonSearchGrid').getTopToolbar().items.items[0].handler();
				},
				stopEvent: true
			},{
				key: Ext.EventObject.ENTER,
				fn: function(inp, e) {
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

					if (Ext.isIE)
					{
						e.browserEvent.keyCode = 0;
						e.browserEvent.which = 0;
					}
					Ext.getCmp('person_search_window').onOkButtonClick();
				},
				stopEvent: true
			},{
				key: Ext.EventObject.F4,
				fn: function(inp, e) {
					if (e.altKey||e.ctrlKey||e.shiftKey)
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

					if (Ext.isIE)
					{
						e.browserEvent.keyCode = 0;
						e.browserEvent.which = 0;
					}
					Ext.getCmp('person_search_window').findById('PersonSearchGrid').getTopToolbar().items.items[1].handler();
				},
				stopEvent: true
			},{
				key: Ext.EventObject.F3,
				fn: function(inp, e) {
					if (e.altKey||e.ctrlKey||e.shiftKey)
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

					if (Ext.isIE)
					{
						e.browserEvent.keyCode = 0;
						e.browserEvent.which = 0;
					}
					Ext.getCmp('person_search_window').findById('PersonSearchGrid').getTopToolbar().items.items[2].handler();
				},
				stopEvent: true
			},{
				alt: true,
				fn: function(inp, e) {
					if ( e.browserEvent.stopPropagation )
						e.browserEvent.stopPropagation();
					else
						e.browserEvent.cancelBubble = true;
					if ( e.browserEvent.preventDefault )
						e.browserEvent.preventDefault();
					else
						e.browserEvent.returnValue = false;
					e.browserEvent.returnValue = false;
					e.returnValue = false;

					if (Ext.isIE)
					{
						e.browserEvent.keyCode = 0;
						e.browserEvent.which = 0;
					}

					if (e.getKey() == Ext.EventObject.J)
					{

						Ext.getCmp('person_search_window').buttons[2].handler();
						return false;
					}
					if (e.getKey() == Ext.EventObject.C)
					{
						Ext.getCmp('person_search_window').buttons[1].handler();
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
		sw.Promed.swPersonSearchWindow.superclass.initComponent.apply(this, arguments);
	}
});