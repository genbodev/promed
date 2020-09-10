/**
 * swRegistryESViewWindow - окно просмотра реестров ЛВН
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Mse
 * @access			public
 * @copyright		Copyright (c) 2014 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			26.09.2014
 */

/*NO PARSE JSON*/

sw.Promed.swRegistryESViewWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'swRegistryESViewWindow',
	width: 640,
	height: 800,
	maximized: true,
	maximizable: false,
	layout: 'border',
	title: lang['reestryi_lvn'],

	searchRegistry: function(reset, callback) {
		var base_form = this.RegistryFilter.getForm();
		if (reset) {
			base_form.reset();
			var date = Date.parseDate(getGlobalOptions().date, 'd.m.Y');
			base_form.findField('RegistryES_DateRange').setValue(Ext.util.Format.date(date.add('d',-30), 'd.m.Y')+' - '+Ext.util.Format.date(date));
		}
		var params = {globalFilters: base_form.getValues()};
		params.callback = (typeof callback == 'function') ? callback : Ext.emptyFn;
		this.RegistryGrid.loadData(params);
	},

	searchRegistryData: function(RegistryES_id, reset) {
		if (!RegistryES_id) {
			return false;
		}

		var base_form = this.RegistryDataFilter.getForm();
		if (reset) {
			base_form.reset();
		}

		var params = base_form.getValues();
		params.RegistryES_id = RegistryES_id;

		var grid = null;
		switch(this.DetailsTabPanel.getActiveTab().id) {
			case 'RegistryData':
				grid = this.RegistryDataGrid;
				break;
			case 'RegistryErrorFLK':
				grid = this.RegistryErrorFLKGrid;
				params.RegistryESErrorStageType_Code = 1;
				break;
			case 'RegistryErrorFSS':
				grid = this.RegistryErrorFSSGrid;
				params.RegistryESErrorStageType_Code = 2;
				break;
		}

		if (grid) {
			if (params.RegistryES_id > 0) {
				grid.loadData({globalFilters: params});
			} else {
				grid.getGrid().getStore().removeAll();
			}
		}
	},

	onRegistrySelect: function(record) {
		if (record && (Ext.isEmpty(record.get('RegistryESStatus_Code')) || !record.get('RegistryESStatus_Code').inlist([1, 5, 7, 8, 11, 12, 13, 14, 15]))) {
			this.RegistryGrid.getAction('action_edit').enable();
			this.RegistryGrid.getAction('action_delete').enable();
		} else {
			this.RegistryGrid.getAction('action_edit').disable();
			this.RegistryGrid.getAction('action_delete').disable();
		}

		if (!record || record.get('RegistryES_id') < 0) {
			this.RegistryGrid.getAction('action_view').disable();
			this.RegistryGrid.getAction('action_edit').disable();
			this.RegistryGrid.getAction('action_delete').disable();
		}

		if (Ext.getCmp('RESVW_RegistryRequestToFSS')) {
			Ext.getCmp('RESVW_RegistryRequestToFSS').disable();
			// доступен для реестров с типом: «Электронные ЛН» или «ЛН на удаление»
			if (record && !Ext.isEmpty(record.get('RegistryESType_id')) && record.get('RegistryESType_id').inlist([1,3]) && record.get('RegistryESStatus_Code') && record.get('RegistryESStatus_Code') == 3) {
				Ext.getCmp('RESVW_RegistryRequestToFSS').enable();
			}

			Ext.getCmp('RESVW_RegistryExportToXML').disable();
			Ext.getCmp('RESVW_RegistryImportFromFSS').disable();
			// доступен для реестров с типом: «Обычные ЛН»
			if (record && record.get('RegistryESType_id') == 2 && record.get('RegistryESStatus_EnableManualActions') && record.get('RegistryESStatus_EnableManualActions') == 1) {
				Ext.getCmp('RESVW_RegistryExportToXML').enable();
				Ext.getCmp('RESVW_RegistryImportFromFSS').enable();
			}
		}

		if (this.RegistryGrid.getAction('action_dig_sign')) {
			this.RegistryGrid.getAction('action_dig_sign').disable();
			// доступен для реестров с типом: «Электронные ЛН» и статусом "В ожидании ЭП физ лица"
			if (record && !Ext.isEmpty(record.get('RegistryESType_id')) && record.get('RegistryESType_id').inlist([1]) && record.get('RegistryESStatus_Code') && record.get('RegistryESStatus_Code') == 9) {
				this.RegistryGrid.getAction('action_dig_sign').enable();
			}
		}

		if (record && record.get('RegistryES_id')) {
			switch (this.DetailsTabPanel.getActiveTab().id) {
				case 'RegistryDetails':
					this.overwriteRegistryTpl(record);
					break;

				case 'RegistryData':
					this.searchRegistryData(record.get('RegistryES_id'));
					break;

				case 'RegistryErrorFLK':
					this.searchRegistryData(record.get('RegistryES_id'));
					break;

				case 'RegistryErrorFSS':
					this.searchRegistryData(record.get('RegistryES_id'));
					break;
			}
		} else {
			this.overwriteRegistryTpl();
		}
	},

	onRegistryDataSelect: function() {
		var RegRecord = this.RegistryGrid.getGrid().getSelectionModel().getSelected();

		if (this.RegistryDataGrid.getAction('action_delete')) {
			if (RegRecord && RegRecord.get('RegistryESStatus_Code').inlist([3, 9])) {// реестр готов к отправке или в ожидании подписи
				this.RegistryDataGrid.getAction('action_delete').enable();
			} else {
				this.RegistryDataGrid.getAction('action_delete').disable();
			}
		}
	},

	getRegistryESId: function() {
		var record = this.RegistryGrid.getGrid().getSelectionModel().getSelected();
		if (!record || !record.get('RegistryES_id')) {
			return undefined;
		} else {
			return record.get('RegistryES_id');
		}
	},

	getReplicationInfo: function () {
		var win = this;
		if (win.buttons[0].isVisible()) {
			win.getLoadMask().show();
			getReplicationInfo('registry', function(text) {
				win.getLoadMask().hide();
				win.buttons[0].setText(text);
			});
		}
	},

	overwriteRegistryTpl: function(record){
		var sparams = {
			RegistryES_Num: '',
			RegistryES_begDate: '',
			RegistryES_RecordCount: 0,
			Lpu_Nick: '',
			Lpu_FSSRegNum: '',
			Lpu_INN: '',
			Lpu_OGRN: '',
			RegistryES_UserFIO: '',
			RegistryES_UserPhone: '',
			RegistryES_UserEmail: '',
			RegistryES_Export: '',
			RegistryES_SuccessCount: 0,
			RegistryES_ErrorCount: 0
		};
		if (record) {
			sparams = Ext.apply(sparams, record.data);
			sparams.RegistryES_begDate = Ext.util.Format.date(sparams.RegistryES_begDate, 'd.m.Y');
			sparams.RegistryES_Export = sparams.RegistryES_Export.split('/').pop();
		}
		this.RegistryTpl.overwrite(this.RegistryPanel.body, sparams);
	},
	hex2bin: function (hex) {
		var bytes = [], str;
		for (var i = 0; i < hex.length - 1; i += 2)
			bytes.push(parseInt(hex.substr(i, 2), 16));
		return String.fromCharCode.apply(String, bytes);
	},
	listeners: {
		'resize': function (win, nW, nH, oW, oH) {
			win.DetailsTabPanel.setHeight(Math.round(nH/3));
		}
	},
	singXMLsAndSendToFSS: function(options) {
		var win = this;
		if (!options.signedItems) {
			options.signedItems = [];
		}
		if (options.items.length > 0) {
			var one = options.items.shift();
			if (options.doc_signtype && options.doc_signtype == 'authapplet') {
				// хотим подписать с помощью AuthApplet
				sw.Applets.AuthApplet.signText({
					text: one.Base64ToSign,
					Cert_Thumbprint: options.Cert_Thumbprint,
					callback: function (sSignedData) {
						one.signType = options.doc_signtype;
						one.Hash = one.Hash;
						one.SignedData = sSignedData;
						one.xml = one.xml;
						options.signedItems.push(one);
						win.singXMLsAndSendToFSS(options);
					}
				});
			} else if (options.doc_signtype && options.doc_signtype.inlist(['authapi', 'authapitomee'])) {
				// хотим подписать с помощью AuthApi
				sw.Applets.AuthApi.signText({
					win: win,
					text: one.Base64ToSign,
					Cert_Thumbprint: options.Cert_Thumbprint,
					callback: function (sSignedData) {
						one.signType = options.doc_signtype;
						one.Hash = one.Hash;
						one.SignedData = sSignedData;
						one.xml = one.xml;
						options.signedItems.push(one);
						win.singXMLsAndSendToFSS(options);
					}
				});
			} else {
				// подписываем файл экспорта с помощью КриптоПро
				sw.Applets.CryptoPro.signXML({
					xml: one.xml,
					Cert_Thumbprint: options.Cert_Thumbprint,
					callback: function(sSignedData) {
						one.signType = 'cryptopro';
						one.xml = sSignedData;
						options.signedItems.push(one);
						win.singXMLsAndSendToFSS(options);
					}
				});
			}
		} else {
			options.callback(options.signedItems);
		}
	},
	deleteRegistryESData: function() {
		var win = this;
		var records = [];
		var registryRec = this.RegistryGrid.getGrid().getSelectionModel().getSelected();

		this.RegistryDataGrid.getMultiSelections().forEach(function (el){
			if (el.get('RegistryES_id') && el.get('Evn_id')) {
				records.push(el);
			}
		});

		if (!records.length) {
			return false;
		}
		
		var nums = records.map(function(item) {
			return '№ ' + item.get('EvnStick_Num');
		});
		var recs = records.map(function(item) {
			return {Evn_id: item.get('Evn_id'), RegistryES_id: item.get('RegistryES_id')};
		});
		
		if (records.length == 1) {
			var msg = 'Внимание! При удалении ЭЛН из реестра, изменения ЭЛН  не будут переданы  в систему учета ЭЛН ФСС РФ. Продолжить?';
		} else {
			var msg = 'Внимание! Вы собираетесь удалить ЭЛН '+nums.join(', ')+'. При удалении ЭЛН  из реестра, изменения ЭЛН  не будут переданы  в систему учета ЭЛН ФСС РФ.';
		}
		
		sw.swMsg.show({
			buttons: {
				yes: 'Продолжить',
				cancel: 'Отмена'
			},
			fn: function(buttonId, text, obj) {
				if ( buttonId == 'yes' ) {
					win.getLoadMask('Удаление записей из реестра').show();
					Ext.Ajax.request({
						params: {
							RegistryES_ids: Ext.util.JSON.encode(recs)
						},
						url: '/?c=RegistryES&m=deleteRegistryESData',
						callback: function (options, success, response) {
							win.getLoadMask().hide();
							//делаем проверку ФЛК т.к. статус реестра мог измениться после удаления ЭЛН (только для статусов готов и ожидание подписи)
							if (success && registryRec.get('RegistryESStatus_id') && registryRec.get('RegistryESStatus_id').inlist([9, 3])) {
								win.getLoadMask('Повторная проверка ФЛК...').show();
								Ext.Ajax.request({
									params: {
										RegistryES_id: registryRec.get('RegistryES_id')
									},
									url: '/?c=RegistryES&m=doFLKControl',
									callback: function(options, success, response) {
										win.getLoadMask().hide();
										win.RegistryGrid.getGrid().getStore().reload();
									}
								});
							}
						}
					});
				}
			}.createDelegate(this),
			icon: Ext.MessageBox.QUESTION,
			msg: msg,
			title: 'Вопрос'
		});
	},
	EvnStickInFSSData: [],
	showEvnStickFromFSS: function(EvnStick_Num) {
		var win = this;
		if (win.EvnStickInFSSData[EvnStick_Num]) {
			getWnd('swEvnStickInFSSViewWindow').show({
				EvnStickInFSSData: win.EvnStickInFSSData[EvnStick_Num]
			});
		}
	},
	checkRegistryESDataInFSSList: function(grid) {
		var win = this;
		var records = [];
		var RegistryES_id = grid.getGrid().getStore().baseParams.RegistryES_id;
		grid.getMultiSelections().forEach(function (el) {
			if (el.get('RegistryES_id') && el.get('Evn_id')) {
				records.push(el);
			}
		});
		
		if (!records.length) {
			return false;
		}
		
		var recs = records.map(function(item) {
			return {Evn_id: item.get('Evn_id'), RegistryES_id: item.get('RegistryES_id')};
		});
		
		var params = {
			RegistryES_Data: Ext.util.JSON.encode(recs)
		};

		var doc_signtype = getOthersOptions().doc_signtype;

		// выбираем сертификат
		getWnd('swCertSelectWindow').show({
			signType: doc_signtype,
			callback: function (cert) {
				params.certbase64 = cert.Cert_Base64;

				if (doc_signtype && doc_signtype.inlist(['authapplet', 'authapi', 'authapitomee'])) {
					params.needHash = 1;
				}

				// тут можно отправить пачкой
				win.getLoadMask('Экспорт данных для проверки в ФСС').show();
				Ext.Ajax.request({
					params: params,
					url: '/?c=RegistryES&m=exportRegistryESDataForCheckInFSSList',
					callback: function (options, success, response) {
						win.getLoadMask().hide();

						if (success) {
							var result = Ext.util.JSON.decode(response.responseText);
							if (result.length > 0) {
								win.singXMLsAndSendToFSS({
									doc_signtype: doc_signtype,
									Cert_Thumbprint: cert.Cert_Thumbprint,
									items: result,
									callback: function(signedItems) {
										// если всё подписалось гуд, то отправляем сообщение в СМЭВ/ФСС
										params.xmls = Ext.util.JSON.encode(signedItems);
										params.RegistryES_id = RegistryES_id;
										// 3. пробуем отправить в ФСС
										win.getLoadMask(langs('Выполняется обмен с ФСС, пожалуйста подождите...')).show();
										Ext.Ajax.request({
											params: params,
											url: '/?c=RegistryES&m=checkRegistryESDataInFSS',
											callback: function (options, success, response) {
												win.getLoadMask().hide();
												if (success) {
													var result = Ext.util.JSON.decode(response.responseText);
													if (result && result.success) {
														if (result.StickData) {
															win.EvnStickInFSSData[result.EvnStick_Num] = result.StickData;
															sw.swMsg.alert('Внимание', 'Данные по ЭЛН №' + result.EvnStick_Num + ' в ФСС отличаются. <a href="#" onClick="getWnd(\'swRegistryESViewWindow\').showEvnStickFromFSS(' + result.EvnStick_Num + ');">Просмотреть ЭЛН, полученный от ФСС.</a>');
														} else {
															sw.swMsg.alert('Внимание', 'Данные успешно получены из ФСС');
														}
													}
													grid.getStore().reload();
												}
											}
										});
									}
								});
							}
						}
					}
				});
			}
		});
	},
	checkRegistryESDataInFSS: function(grid) {
		var win = this;
		var record = grid.getSelectionModel().getSelected();
		var RegistryES_id = grid.getStore().baseParams.RegistryES_id;
		if (RegistryES_id && record.get('Evn_id')) {
			var params = {
				RegistryES_id: RegistryES_id,
				Evn_id: record.get('Evn_id')
			};

			var EvnStick_Num = record.get('EvnStick_Num');

			var doc_signtype = getOthersOptions().doc_signtype;

			// выбираем сертификат
			getWnd('swCertSelectWindow').show({
				signType: doc_signtype,
				callback: function (cert) {
					params.certbase64 = cert.Cert_Base64;

					if (doc_signtype && doc_signtype.inlist(['authapplet', 'authapi', 'authapitomee'])) {
						params.needHash = 1;
					}

					win.getLoadMask('Экспорт данных для проверки в ФСС').show();
					Ext.Ajax.request({
						params: params,
						url: '/?c=RegistryES&m=exportRegistryESDataForCheckInFSS',
						callback: function (options, success, response) {
							win.getLoadMask().hide();

							if (success) {
								var result = Ext.util.JSON.decode(response.responseText);
								if (result.length > 0) {
									win.singXMLsAndSendToFSS({
										doc_signtype: doc_signtype,
										Cert_Thumbprint: cert.Cert_Thumbprint,
										items: result,
										callback: function(signedItems) {
											// если всё подписалось гуд, то отправляем сообщение в СМЭВ/ФСС
											params.xmls = Ext.util.JSON.encode(signedItems);
											// 3. пробуем отправить в ФСС
											win.getLoadMask(lang['vyipolnyaetsya_obmen_s_fss_pojaluysta_podojdite']).show();
											Ext.Ajax.request({
												params: params,
												url: '/?c=RegistryES&m=checkRegistryESDataInFSS',
												callback: function (options, success, response) {
													win.getLoadMask().hide();
													if (success) {
														var result = Ext.util.JSON.decode(response.responseText);
														if (result && result.success) {
															if (result.StickData) {
																win.EvnStickInFSSData[EvnStick_Num] = result.StickData;
																sw.swMsg.alert('Внимание', 'Данные по ЭЛН №' + EvnStick_Num + ' в ФСС отличаются. <a href="#" onClick="getWnd(\'swRegistryESViewWindow\').showEvnStickFromFSS(' + EvnStick_Num + ');">Просмотреть ЭЛН, полученный от ФСС.</a>');
															} else {
																sw.swMsg.alert('Внимание', 'Для ЭЛН №' + EvnStick_Num + ' успешно получены данные из ФСС');
															}
														}
														grid.getStore().reload();
													}
												}
											});
										}
									});
								}
							}
						}
					});
				}
			});
		}
	},
	show: function(){
		sw.Promed.swRegistryESViewWindow.superclass.show.apply(this, arguments);

		var win = this;
		var base_form = this.RegistryFilter.getForm();

		if ( getRegionNick() != 'astra' ) {
			base_form.findField('RegistryESType_id').getStore().filterBy(function(rec) {
				return (!Ext.isEmpty(rec.get('RegistryESType_Code')) && rec.get('RegistryESType_Code').inlist([ 1, 3 ]));
			});
		}

		if(getRegionNick().inlist(['perm', 'vologda', 'ufa', 'ekb'])) {
			this.getReplicationInfo();
		}

		base_form.findField('LpuBuilding_id').getStore().loadData(getStoreRecords(swLpuBuildingGlobalStore));
		base_form.findField('LpuSection_id').getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));
		
		this.RegistryGrid.addActions({ // загрузка XML в StickFssDataGet
			name: 'action_upload_xml',
			text: 'Загрузить XML в БД',
			hidden: !IS_DEBUG,//только для тестов
			handler: function() {
				var params = {
					saveUrl: '/?c=RegistryES&m=UploadXml',
					ignoreCheckData: true
				}

				getWnd('swFileUploadWindow').show(params);
			}
		});


		this.RegistryDataGrid.addActions({
			name: 'action_check_fss',
			text: 'Проверить в ФСС',
			handler: function() {
				var grid = win.RegistryDataGrid;
				win.checkRegistryESDataInFSSList(grid);
			}
		});

		this.RegistryErrorFLKGrid.addActions({
			name: 'action_check_fss',
			text: 'Проверить в ФСС',
			handler: function() {
				var grid = win.RegistryErrorFLKGrid.getGrid();
				win.checkRegistryESDataInFSS(grid);
			}
		});

		this.RegistryErrorFSSGrid.addActions({
			name: 'action_check_fss',
			text: 'Проверить в ФСС',
			handler: function() {
				var grid = win.RegistryErrorFSSGrid.getGrid();
				win.checkRegistryESDataInFSS(grid);
			}
		});

		this.RegistryGrid.addActions({
			name:'action_dig_sign',
			text:'Подписать',
			iconCls: 'digital-sign16',
			handler: function() {
				var RegistryES_id = win.getRegistryESId();
				if (RegistryES_id) {
					getWnd('swRegistryESSignWindow').show({
						RegistryES_id: RegistryES_id,
						callback: function() {
							win.searchRegistry(false, function(){
								var record = win.RegistryGrid.getGrid().getSelectionModel().getSelected();
								if (record) {
									win.overwriteRegistryTpl(record);
								}
							});
						}
					});
				}
			}
		});

		this.RegistryGrid.addActions({
			name:'action_new',
			text:lang['deystviya'],
			iconCls: 'actions16',
			menu: new Ext.menu.Menu({
				id:'RESVW_RegistryMenu',
				items: [{
					text: lang['eksport_v_xml'],
					id: 'RESVW_RegistryExportToXML',
					hidden: getRegionNick() != 'astra',
					//disabled: true,
					handler: function(){
						var grid = this.RegistryGrid.getGrid();
						var record = grid.getSelectionModel().getSelected();
						if (record && record.get('RegistryES_id')) {
							var params = {RegistryES_id: record.get('RegistryES_id')};
							params.callback = function () {
				 				win.searchRegistry(false, function(){
									record = grid.getSelectionModel().getSelected();
				 					win.overwriteRegistryTpl(record);
								});
							};
							getWnd('swRegistryESExportToXMLWindow').show(params);
						}
					}.createDelegate(this)
				}, {
					text: lang['import_otveta_ot_fss'],
					id: 'RESVW_RegistryImportFromFSS',
					hidden: getRegionNick() != 'astra',
					//disabled: true,
					handler: function(){
						var grid = this.RegistryGrid.getGrid();
						var record = grid.getSelectionModel().getSelected();
						if (record && record.get('RegistryES_id')) {
							var params = {RegistryES_id: record.get('RegistryES_id')};
							params.callback = function () {
				 				win.searchRegistry(false, function(){
									record = grid.getSelectionModel().getSelected();
				 					win.overwriteRegistryTpl(record);
								});
							};
							getWnd('swRegistryESImportXMLWindow').show(params);
						}
					}.createDelegate(this)
				}, {
					text: lang['otpravit_v_fss_i_zagruzit_otvet'],
					id: 'RESVW_RegistryRequestToFSS',
					handler: function() {
						var grid = this.RegistryGrid.getGrid();
						var record = grid.getSelectionModel().getSelected();
						if (record && record.get('RegistryES_id')) {
							var params = {RegistryES_id: record.get('RegistryES_id')};

							var doc_signtype = getOthersOptions().doc_signtype;

							// выбираем сертификат
							getWnd('swCertSelectWindow').show({
								signType: doc_signtype,
								callback: function (cert) {
									params.certbase64 = cert.Cert_Base64;

									if (doc_signtype && doc_signtype.inlist(['authapplet', 'authapi', 'authapitomee'])) {
										params.needHash = 1;
									}

									win.getLoadMask(lang['vyipolnyaetsya_eksport_reestra_pojaluysta_podojdite']).show();
									Ext.Ajax.request({
										params: params,
										url: '/?c=RegistryES&m=exportRegistryESToXml',
										callback: function (options, success, response) {
											win.getLoadMask().hide();

											if (success) {
												var result = Ext.util.JSON.decode(response.responseText);
												if (result.length > 0) {
													win.singXMLsAndSendToFSS({
														doc_signtype: doc_signtype,
														Cert_Thumbprint: cert.Cert_Thumbprint,
														items: result,
														callback: function(signedItems) {
															// если всё подписалось гуд, то отправляем сообщение в СМЭВ/ФСС
															params.xmls = Ext.util.JSON.encode(signedItems);
															// 3. пробуем отправить в ФСС
															win.getLoadMask(lang['vyipolnyaetsya_obmen_s_fss_pojaluysta_podojdite']).show();
															Ext.Ajax.request({
																params: params,
																url: '/?c=RegistryES&m=requestRegistryESToFss',
																callback: function (options, success, response) {
																	win.getLoadMask().hide();
																	if (success) {
																		win.searchRegistry(false, function () {
																			record = grid.getSelectionModel().getSelected();
																			win.overwriteRegistryTpl(record);
																		});
																	}
																}
															});
														}
													});
												}
											}
										}
									});
								}
							});
						}
					}.createDelegate(this)
				}]
			})
		});

		if (getEvnStickOptions().enable_sign_evnstick_auth_person) {
			this.RegistryGrid.getAction('action_dig_sign').setHidden(false);
		} else {
			this.RegistryGrid.getAction('action_dig_sign').setHidden(true);
		}

		this.searchRegistry(true);
	},

	openRegistryESEditWindow: function(action) {
		if (!action.inlist(['add','edit','view'])) {
			return false;
		}

		var grid = this.RegistryGrid.getGrid();
		var params = {action: action};

		if (action != 'add') {
			params.RegistryES_id = grid.getSelectionModel().getSelected().get('RegistryES_id');
		}

		params.callback = function() {
			this.RegistryGrid.getAction('action_refresh').execute();
		}.createDelegate(this);

		getWnd('swRegistryESEditWindow').show(params);
	},

	initComponent: function() {
		var win = this;

		this.RegistryFilter = new sw.Promed.FormPanel({
			keys:
			[{
				key: Ext.EventObject.ENTER,
				fn: function(e)
				{
					win.searchRegistry();
				},
				stopEvent: true
			}],
			items: [{
				layout: 'column',
				items: [{
					layout: 'form',
					items: [{
						xtype: 'daterangefield',
						plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
						name: 'RegistryES_DateRange',
						fieldLabel: lang['data_reestra'],
						width: 180
					}, {
						xtype: 'swcommonsprcombo',
						comboSubject: 'RegistryESStatus',
						hiddenName: 'RegistryESStatus_id',
						fieldLabel: lang['status'],
						width: 220
					}]
				}, {
					layout: 'form',
					width: 320,
					labelWidth: 140,
					items: [{
						xtype: 'swcommonsprcombo',
						comboSubject: 'RegistryESType',
						hiddenName: 'RegistryESType_id',
						lastQuery: '',
						anchor: '100%',
						fieldLabel: lang['tip_reestra']
					}, {
						xtype: 'numberfield',
						allowNegative: false,
						allowDecimals: false,
						name: 'EvnStick_Num',
						anchor: '100%',
						fieldLabel: 'Номер ЛВН'
					}]
				}, {
					layout: 'form',
					width: 320,
					labelWidth: 140,
					items: [{
						xtype: 'numberfield',
						allowNegative: false,
						allowDecimals: false,
						name: 'RegistryES_Num',
						anchor: '100%',
						fieldLabel: lang['nomer_reestra']
					}, {
						layout: 'column',
						items: [{
							layout: 'form',
							style: 'margin-left: 50px',
							items: [{
								xtype: 'button',
								handler: function () {
									this.searchRegistry();
								}.createDelegate(this),
								iconCls: 'search16',
								id: 'RESVW_RegistrySearchButton',
								text: lang['nayti'],
								minWidth: 80
							}]
						}, {
							layout: 'form',
							style: 'margin-left: 10px',
							items: [{
								xtype: 'button',
								handler: function () {
									this.searchRegistry(true);
								}.createDelegate(this),
								iconCls: 'resetsearch16',
								id: 'RESVW_RegistryResetButton',
								text: lang['sbrosit']
							}]
						}]
					}]
				}, {
					//kukuzapa begin
					hidden: getRegionNick() == 'kz',
					layout: 'form',
					width: 320,
					labelWidth: 140,
					items: [{
						hiddenName: 'LpuBuilding_id',
						fieldLabel: 'Подразделение',
						id: 'K_LpuBuildingCombo',
						lastQuery: '',
						linkedElements: [
							'K_LpuSectionCombo'
						],
						listWidth: 700,
						anchor: '100%',
						xtype: 'swlpubuildingglobalcombo'
					}]

				}, {
					layout: 'form',
					hidden: getRegionNick() == 'kz',
					width: 320,
					labelWidth: 140,
					items: [{
						hiddenName: 'LpuSection_id',
						id: 'K_LpuSectionCombo',
						lastQuery: '',
						parentElementId: 'K_LpuBuildingCombo',
						listWidth: 700,
						anchor: '100%',
						xtype: 'swlpusectionglobalcombo'
					}]

				}]
			}]
		});

		this.RegistryGrid = new sw.Promed.ViewFrame({
			dataUrl: '/?c=RegistryES&m=loadRegistryESGrid',
			id: 'RESVW_RegistryGrid',
			border: true,
			autoLoadData: false,
			object: 'RegistryES',
			root: 'data',
			region: 'center',
			stringfields: [
				{name: 'RegistryES_id', type: 'int', header: 'Registry_id', key: true, hidden:!isSuperAdmin()},
				{name: 'Lpu_id', type: 'int', hidden: true},
				{name: 'Lpu_FSSRegNum', type: 'string', hidden: true},
				{name: 'Lpu_INN', type: 'int', hidden: true},
				{name: 'Lpu_OGRN', type: 'int', hidden: true},
				{name: 'RegistryESType_id', type: 'int', hidden: true},
				{name: 'RegistryES_UserFIO', type: 'string', hidden: true},
				{name: 'RegistryES_UserPhone', type: 'string', hidden: true},
				{name: 'RegistryES_UserEmail', type: 'string', hidden: true},
				{name: 'RegistryES_Export', type: 'string', hidden: true},
				{name: 'RegistryES_ErrorCount', type: 'int', hidden: true},
				{name: 'RegistryES_SuccessCount', type: 'int', hidden: true},
				{name: 'RegistryESStatus_id', type: 'int', hidden: true},
				{name: 'RegistryESStatus_EnableManualActions', type: 'int', hidden: true},
				{name: 'RegistryESStatus_Code', type: 'int', hidden: true},
				{name: 'RegistryES_Num', type: 'int', header: lang['nomer_reestra'], width: 120},
				{name: 'RegistryESType_Name', type: 'string', header: lang['tip_reestra'], width: 120},
				{name: 'RegistryES_begDate', type: 'date', header: lang['data_reestra'], width: 120},
				{name: 'RegistryES_RecordCount', type: 'int', header: lang['kolichestvo'], width: 120},
				{name: 'Lpu_Nick', type: 'string', header: lang['mo'], id: 'autoexpand'},
				{name: 'LpuBuilding_Name', type: 'string', header: 'Подразделение', width: 120},
				{name: 'LpuSection_Name', type: 'string', header: 'Отделение', width: 120},
				{name: 'RegistryES_insDT', type: 'date', header: lang['data_formirovaniya'], width: 120},
				{name: 'RegistryES_updDT', type: 'date', header: lang['data_izmeneniya'], width: 120},
				{name: 'RegistryESStatus_Name', type: 'string', header: lang['status'], width: 120},
				{name: 'RegistryES_showLogs', renderer: function(v, p, r) {
					if (r.get('RegistryES_id') && r.get('RegistryES_id') > 0) {
						return "<a href='/?c=RegistryES&m=showFiles&RegistryES_id=" + r.get('RegistryES_id') + "' target='_blank'>Просмотреть</a>";
					} else {
						return "";
					}
				}, header: 'Логи отправок', width: 120}
			],
			actions: [
				{name:'action_add', handler: function(){this.openRegistryESEditWindow('add')}.createDelegate(this)},
				{name:'action_edit', handler: function(){this.openRegistryESEditWindow('edit')}.createDelegate(this)},
				{name:'action_view', handler: function(){this.openRegistryESEditWindow('view')}.createDelegate(this)},
				{name:'action_delete', url: '/?c=RegistryES&m=deleteRegistryES'},
				{name:'action_refresh'},
				{name:'action_print'}
			],
			onRowSelect: function(sm,index,record){
				this.onRegistrySelect(record);
			}.createDelegate(this)
		});

		this.RegistryTpl = new Ext.XTemplate([
			'<div style="padding:2px;font-size: 12px;"><b>Номер: {RegistryES_Num}</b></div>',
			'<div style="padding:2px;font-size: 12px;"><b>Дата: {RegistryES_begDate}</b></div>',
			'<div style="padding:2px;font-size: 12px;"><b>Кол-во ЛВН: {RegistryES_RecordCount}</b></div>',
			'<div style="padding:2px;font-size: 12px;"><b>МО: {Lpu_Nick}</b></div>',
			'<div style="padding:2px;font-size: 12px;"><b>Рег. номер МО в ФСС: {Lpu_FSSRegNum}</b></div>',
			'<div style="padding:2px;font-size: 12px;"><b>ИНН МО: {Lpu_INN}</b></div>',
			'<div style="padding:2px;font-size: 12px;"><b>ОГРН МО: {Lpu_OGRN}</b></div>',
			'<div style="padding:2px;font-size: 12px;"><b>Фио автора: {RegistryES_UserFIO}</b></div>',
			'<div style="padding:2px;font-size: 12px;"><b>Телефон автора: {RegistryES_UserPhone}</b></div>',
			'<div style="padding:2px;font-size: 12px;"><b>E-mail автора: {RegistryES_UserEmail}</b></div>',
			'<div style="padding:2px;font-size: 12px;"><b>Название файла реестра: {RegistryES_Export}</b></div>',
			'<div style="padding:2px;font-size: 12px;"><b>Кол-во принятых ЛВН: {RegistryES_SuccessCount}</b></div>',
			'<div style="padding:2px;font-size: 12px;"><b>Кол-во ЛВН с ошибкой: {RegistryES_ErrorCount}</b></div>'
		]);
		this.RegistryPanel = new Ext.Panel({
			id: 'RESVW_RegistryPanel',
			bodyStyle:'background:#DFE8F6;padding:5px;',
			border: false,
			layout: 'fit',
			height: 28,
			maxSize: 28,
			html: ''
		});

		this.RegistryDataFilter = new Ext.FormPanel({
			border: true,
			bodyStyle:'width:100%;background:#DFE8F6;padding:3px;',
			region: 'north',
			height: 30,
			keys: [{
				key: Ext.EventObject.ENTER,
				fn: function(e) {
					this.searchRegistryData(this.getRegistryESId());
				}.createDelegate(this),
				stopEvent: true
			}],
			items: [{
				border: false,
				labelAlign: 'right',
				bodyStyle:'width:100%;background:#DFE8F6;',
				defaults: {
					border: false,
					bodyStyle: 'background:#DFE8F6;'
				},
				layout: 'column',
				items: [{
					layout: 'form',
					width: 200,
					labelWidth: 90,
					items: [{
						xtype: 'textfield',
						name: 'EvnStick_Num',
						fieldLabel: 'Номер ЛВН',
						width: 100
					}]
				}, {
					layout: 'form',
					width: 190,
					labelWidth: 50,
					items: [{
						xtype: 'textfield',
						name: 'Person_Fio',
						fieldLabel: lang['fio'],
						width: 130
					}]
				}, {
					layout: 'form',
					width: 190,
					labelWidth: 70,
					items: [{
						xtype: 'swbaselocalcombo',
						hiddenName: 'RegistryESType_id',
						fieldLabel: lang['tap_kvs'],
						displayField: 'Name',
						valueField: 'id',
						tpl: new Ext.XTemplate(
							'<tpl for="."><div class="x-combo-list-item">',
							'{Name}&nbsp;',
							'</div></tpl>'
						),
						store: new Ext.data.SimpleStore({
							key: 'id',
							autoLoad: false,
							fields: [
								{name: 'id', type: 'int'},
								{name: 'Name', type: 'string'},
								{name: 'SysNick', type: 'string'}
							],
							data: [
								[1, lang['tap'], 'EvnPL'],
								[2, lang['kvs'], 'EvnPS']
							]
						}),
						width: 80
					}]
				}, {
					layout: 'form',
					width: 240,
					labelWidth: 80,
					items: [{
						xtype: 'swcommonsprcombo',
						hiddenName: 'RegistryESDataStatus_id',
						comboSubject: 'RegistryESDataStatus',
						fieldLabel: 'Статус ЛВН',
						listWidth: 120,
						width: 120
					}]
				}, {
					layout: 'form',
					style: 'margin-left: 20px',
					items: [{
						xtype: 'button',
						handler: function () {
							this.searchRegistryData(this.getRegistryESId());
						}.createDelegate(this),
						iconCls: 'search16',
						text: lang['nayti'],
						minWidth: 80
					}]
				}, {
					layout: 'form',
					style: 'margin-left: 10px',
					items: [{
						xtype: 'button',
						handler: function () {
							this.searchRegistryData(this.getRegistryESId(), true);
						}.createDelegate(this),
						iconCls: 'resetsearch16',
						text: lang['sbrosit']
					}]
				}]
			}]
		});

		this.RegistryDataGrid = new sw.Promed.ViewFrame({
			dataUrl: '/?c=RegistryES&m=loadRegistryESDataGrid',
			id: 'RESVW_RegistryDataGrid',
			selectionModel: 'multiselect2',
			border: false,
			autoLoadData: false,
			root: 'data',
			region: 'center',
			toolbar: true,
			stringfields: [
				{name: 'Evn_id', type: 'int', header: 'ID', key: true},
				{name: 'RegistryES_id', type: 'int', hidden: true},
				{name: 'delAccess', type: 'int', hidden: true},
				{name: 'EvnStick_Num', type: 'int', header: lang['nomer_lvn'], width: 120},
				{name: 'Lpu_Nick', type: 'string', header: 'МО, в который был выдан ЛВН', width: 120},
				{name: 'Person_Fio', type: 'string', header: lang['fio_patsienta'], width: 120},
				{name: 'Person_BirthDay', type: 'date', header: lang['data_rojdeniya'], width: 120},
				{name: 'Org_Nick', type: 'string', header: lang['mesto_rabotyi'], width: 120},
				{name: 'MedPersonal_fFio', type: 'string', header: lang['vrach_vyidavshiy_lvn'], width: 120},
				{name: 'MedPersonal_dFio', type: 'string', header: lang['vrach_zakonchivshiy_lvn'], width: 120},
				{name: 'EvnStickWorkRelease_begDate', type: 'date', header: lang['osvobojdenie_ot_rabotyi_nachalo'], width: 120},
				{name: 'EvnStickWorkRelease_endDate', type: 'date', header: lang['osvobojdenie_ot_rabotyi_okonchanie'], width: 120},
				{name: 'RegistryESType_Name', type: 'string', header: lang['tap_kvs'], width: 120},
				{name: 'Evn_rNum', type: 'string', header: lang['nomer_tap_kvs'], width: 120},
				{name: 'RegistryESDataStatus_Name', type: 'string', header: 'Статус ЛВН в реестре', width: 120},
				{name: 'StickFSSType_Name', type: 'string', header: 'Состояние ЛВН в ФСС', width: 120},
				{name: 'EvnStick_deleted', type: 'string', header: 'ЛВН удален', width: 100}
			],
			onLoadData: function(sm, index, record) {
				this.getAction('action_print').menu.printObjectListSelected.show();
			},
			onRowSelect: function(sm,index,record){
				this.onRegistryDataSelect(record);
			}.createDelegate(this),
			actions: [
				{name:'action_add', hidden: true, disabled: true},
				{name:'action_edit', hidden: true, disabled: true},
				{name:'action_view', hidden: true, disabled: true},
				{name:'action_delete', hidden: false, handler: function() {
					win.deleteRegistryESData();
				}},
				{name:'action_refresh'},
				{name:'action_print', menuConfig: {
					printObjectListSelected: {name: 'printObjectListSelected', text: langs('Печать списка выбранных'), handler: function(){
						var params = {};
						params.selections = [];
						win.RegistryDataGrid.getMultiSelections().forEach(function (el){
							params.selections.push(el);
						});
						Ext.ux.GridPrinter.print(win.RegistryDataGrid, params);
					}}
				}}
			]
		});

		this.RegistryErrorFLKGrid = new sw.Promed.ViewFrame({
			dataUrl: '/?c=RegistryES&m=loadRegistryESErrorGrid',
			id: 'RESVW_RegistryErrorFLKGrid',
			border: false,
			autoLoadData: false,
			root: 'data',
			region: 'center',
			toolbar: true,
			onRowSelect: function(sm, index, record) {
				if (record && record.get('RegistryESError_id')) {
					this.setActionDisabled('action_check_fss', false);
				} else {
					this.setActionDisabled('action_check_fss', true);
				}
			},
			stringfields: [
				{name: 'RegistryESError_id', type: 'int', header: 'ID', key: true},
				{name: 'Evn_id', type: 'int', hidden: true},
				{name: 'existRegistryESFiles', type: 'int', hidden: true},
				{name: 'RegistryESError_Code', type: 'string', header: lang['kod'], width: 100},
				{name: 'RegistryESErrorType_Name', type: 'string', header: 'Тип ошибки', width: 150},
				{name: 'RegistryESError_Descr', type: 'string', header: 'Описание ошибки', id: 'autoexpand'},
				{name: 'EvnStick_Num', type: 'int', header: lang['nomer_lvn'], width: 120},
				{name: 'Person_Fio', type: 'string', header: lang['fio_patsienta'], width: 120},
				{name: 'Person_BirthDay', type: 'string', header: lang['data_rojdeniya'], width: 120},
				{name: 'MedPersonal_fFio', type: 'string', header: lang['vrach_vyidavshiy_lvn'], width: 120},
				{name: 'MedPersonal_dFio', type: 'string', header: lang['vrach_zakonchivshiy_lvn'], width: 120},
				{name: 'RegistryESType_Name', type: 'string', header: lang['tap_kvs'], width: 120},
				{name: 'Evn_rNum', type: 'string', header: lang['nomer_tap_kvs'], width: 120},
				{name: 'RegistryESDataStatus_Code', type: 'string', header: 'Код статуса ЛВН в реестре', hidden: true},
				{name: 'RegistryESDataStatus_Name', type: 'string', header: 'Статус ЛВН в реестре', width: 120},
				{name: 'StickFSSType_Name', type: 'string', header: 'Состояние ЛВН в ФСС', width: 120},
				{name: 'RegistryESData_showLogs', renderer: function(v, p, r) {
					if (r.get('Evn_id') && r.get('existRegistryESFiles')) {
						return "<a href='/?c=RegistryES&m=showFiles&RegistryES_id=" + win.RegistryErrorFLKGrid.getGrid().getStore().baseParams.RegistryES_id + "&Evn_id=" + r.get('Evn_id') + "' target='_blank'>Просмотреть</a>";
					} else {
						return "";
					}
				}, header: 'Логи отправок', width: 120},
				{name: 'EvnStick_deleted', type: 'string', header: 'ЛВН удален', width: 100}
			],
			actions: [
				{name:'action_add', hidden: true, disabled: true},
				{name:'action_edit', hidden: true, disabled: true},
				{name:'action_view', hidden: true, disabled: true},
				{name:'action_delete', hidden: true, disabled: true},
				{name:'action_refresh'},
				{name:'action_print'}
			]
		});

		this.RegistryErrorFSSGrid = new sw.Promed.ViewFrame({
			dataUrl: '/?c=RegistryES&m=loadRegistryESErrorGrid',
			id: 'RESVW_RegistryErrorFSSGrid',
			border: false,
			autoLoadData: false,
			root: 'data',
			region: 'center',
			toolbar: true,
			onRowSelect: function(sm, index, record) {
				if (record && record.get('RegistryESError_id')) {
					this.setActionDisabled('action_check_fss', false);
				} else {
					this.setActionDisabled('action_check_fss', true);
				}

				if (record && record.get('delAccess') == 1) {
					this.setActionDisabled('action_delete', false);
				} else {
					this.setActionDisabled('action_delete', true);
				}
			},
			stringfields: [
				{name: 'RegistryESError_id', type: 'int', header: 'ID', key: true},
				{name: 'Evn_id', type: 'int', hidden: true},
				{name: 'existRegistryESFiles', type: 'int', hidden: true},
				{name: 'RegistryESError_Code', type: 'string', header: lang['kod'], width: 120},
				{name: 'RegistryESError_Descr', type: 'string', header: lang['naimenovanie'], id: 'autoexpand'},
				{name: 'EvnStick_Num', type: 'int', header: lang['nomer_lvn'], width: 120},
				{name: 'Person_Fio', type: 'string', header: lang['fio_patsienta'], width: 120},
				{name: 'Person_BirthDay', type: 'string', header: lang['data_rojdeniya'], width: 120},
				{name: 'MedPersonal_fFio', type: 'string', header: lang['vrach_vyidavshiy_lvn'], width: 120},
				{name: 'MedPersonal_dFio', type: 'string', header: lang['vrach_zakonchivshiy_lvn'], width: 120},
				{name: 'RegistryESType_Name', type: 'string', header: lang['tap_kvs'], width: 120},
				{name: 'Evn_rNum', type: 'string', header: lang['nomer_tap_kvs'], width: 120},
				{name: 'RegistryESDataStatus_Code', type: 'string', header: 'Код статуса ЛВН в реестре', hidden: true},
				{name: 'RegistryESDataStatus_Name', type: 'string', header: 'Статус ЛВН в реестре', width: 120},
				{name: 'StickFSSType_Name', type: 'string', header: 'Состояние ЛВН в ФСС', width: 120},
				{name: 'RegistryESData_showLogs', renderer: function(v, p, r) {
					if (r.get('Evn_id') && r.get('existRegistryESFiles')) {
						return "<a href='/?c=RegistryES&m=showFiles&RegistryES_id=" + win.RegistryErrorFSSGrid.getGrid().getStore().baseParams.RegistryES_id + "&Evn_id=" + r.get('Evn_id') + "' target='_blank'>Просмотреть</a>";
					} else {
						return "";
					}
				}, header: 'Логи отправок', width: 120}
			],
			actions: [
				{name:'action_add', hidden: true, disabled: true},
				{name:'action_edit', hidden: true, disabled: true},
				{name:'action_view', hidden: true, disabled: true},
				{name:'action_delete', hidden: true, disabled: true},
				{name:'action_refresh'},
				{name:'action_print'}
			]
		});

		this.DetailsTabPanel = new Ext.TabPanel({
			border: false,
			autoHeight: true,
			activeTab: 0,
			split: true,
			id: 'RESVW_DetailsTabPanel',
			layoutOnTabChange: true,
			region: 'south',
			items: [{
				border: false,
				frame: false,
				id: 'RegistryDetails',
				title: lang['0_reestr'],
				items: []
			}, {
				border: false,
				frame: false,
				id: 'RegistryData',
				title: lang['1_dannyie'],
				items: []
			}, {
				border: false,
				frame: false,
				id: 'RegistryErrorFLK',
				title: lang['2_obschie_oshibki'],
				items: []
			}, {
				border: false,
				frame: false,
				id: 'RegistryErrorFSS',
				title: lang['3_itogi_proverki_fss'],
				items: []
			}],
			listeners:
			{
				tabchange: function(panel, tab) {
					win.RegistryDataFilter.hide();
					if (tab.id.inlist(['RegistryData', 'RegistryErrorFLK', 'RegistryErrorFSS'])) {
						win.RegistryDataFilter.show();
					}

					log(win.DetailsCardPanel.getLayout());
					switch(tab.id) {
						case 'RegistryDetails':
							win.DetailsCardPanel.getLayout().setActiveItem(0);
							break;
						case 'RegistryData':
							win.DetailsCardPanel.getLayout().setActiveItem(1);
							break;
						case 'RegistryErrorFLK':
							win.DetailsCardPanel.getLayout().setActiveItem(2);
							break;
						case 'RegistryErrorFSS':
							win.DetailsCardPanel.getLayout().setActiveItem(3);
							break;
					}
					win.DetailsDataPanel.doLayout();

					var record = this.RegistryGrid.getGrid().getSelectionModel().getSelected();
					this.onRegistrySelect(record);
				}.createDelegate(this)
			}
		});

		win.DetailsCardPanel = new sw.Promed.Panel({
			title: '',
			layout: 'card',
			region: 'center',
			height: 200,
			activeItem: 0,
			border: false,
			items: [
				win.RegistryPanel,
				win.RegistryDataGrid,
				win.RegistryErrorFLKGrid,
				win.RegistryErrorFSSGrid
			]
		});

		win.DetailsDataPanel = new sw.Promed.Panel({
			region: 'center',
			border: false,
			layout: 'border',
			items: [
				this.RegistryDataFilter,
				this.DetailsCardPanel
			]
		});

		Ext.apply(this, {
			items: [{
				border: false,
				layout: 'border',
				region: 'center',
				items: [
					{
						autoHeight: true,
						frame: true,
						region: 'north',
						items: [this.RegistryFilter]
					},
					this.RegistryGrid
				]
			}, {
				border: false,
				layout: 'border',
				region: 'south',
				height: 340,
				split: true,
				items: [
					{
						autoHeight: true,
						frame: true,
						region: 'north',
						items: [
							this.DetailsTabPanel
						]
					},
					win.DetailsDataPanel
				]
			}],
			buttons: [
			{
				hidden: !getRegionNick().inlist(['perm', 'vologda', 'ufa', 'ekb']),
				handler: function()
				{
					win.getReplicationInfo();
				},
				iconCls: 'ok16',
				text: 'Актуальность данных: (неизвестно)'
			},
			{
				text: '-'
			},
			HelpButton(this, 1),
			{
				handler: function () {
					this.hide();
				}.createDelegate(this),
				iconCls: 'cancel16',
				id: 'RESVW_CancelButton',
				text: lang['zakryit']
			}]
		});

		sw.Promed.swRegistryESViewWindow.superclass.initComponent.apply(this, arguments);
	}
});
