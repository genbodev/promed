/**
 * swEvnLabRequestEditWindow.js - Заявка на лабораторное исследование
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * @package      Assistant
 * @access       public
 * @autor		 Gabdushev I., Markoff A.
 * @copyright    Copyright (c) 2009 Swan Ltd.
 * @version      2012/12 Refactoring
 *
 */
sw.Promed.swEvnLabRequestEditWindow = Ext.extend(sw.Promed.BaseForm, {
	action:null,
	buttonAlign:'left',
	callback:Ext.emptyFn,
	onHide:Ext.emptyFn,
	layout:'border',
	modal:true,
	id:'EvnLabRequestEditWindow',
	cls: 'newStyle',
	title:langs('Заявка на лабораторное исследование'),
	maximized:true,
	params: {
		Person_id: null,
		Sex_id: null,
		RaceType_id: null,
		PersonHeight_Height: null,
		PersonHeight_setDT: null,
		PersonWeight_WeightText: null,
		PersonWeight_setDT: null
	},
	hiddenFields: [
		'RaceType_id',
		'PersonHeight_Height',
		'PersonHeight_setDT',
		'PersonWeight_WeightText',
		'PersonWeight_setDT'
	],
	listeners:{
		hide:function () {
			// Смотрим есть ли пустые пробы, если есть отправляем запрос на удаление %)
			var win = this;
			var base_form = this.LabRequestEditForm.getForm();
			for (var i in win.params) win.params[i] = null;

			if (!Ext.isEmpty(base_form.findField('EvnLabRequest_id').getValue())) {
				var isEmptySamples = false;
				this.LabSamplesStore.each(function (rec) {
					if (win.findById(win.id + '_EvnUslugaDataGrid_' + rec.get('frameId')).getGrid().getStore().getCount() == 0) {
						isEmptySamples = true;
					}
				});
				if (isEmptySamples) {
					Ext.Ajax.request({
						failure: function () {
						},
						params: {
							EvnLabRequest_id: base_form.findField('EvnLabRequest_id').getValue()
						},
						success: function (response) {
						},
						url: '/?c=EvnLabRequest&m=deleteEmptySamples'
					});
				}
			}

			base_form.findField('EvnDirection_Num').enable();
			base_form.findField('Diag_id').enable();
			base_form.findField('TumorStage_id').enable();
			base_form.findField('EvnDirection_setDT').enable();
			base_form.findField('PrehospDirect_id').enable();
			base_form.findField('Org_sid').enable();
			base_form.findField('LpuSection_id').enable();
			base_form.findField('EvnLabRequest_Ward').enable();
			base_form.findField('MedStaffFact_id').enable();
			base_form.findField('MedPersonal_Code').enable();
			base_form.findField('EvnDirection_IsCito').enable();
			base_form.findField('EvnDirection_Descr').enable();
			this.onHide();
		}
	},
	doSave: function(options) {
		if ( typeof options != 'object' ) {
            options = new Object();
        }
		
		if ( this.formStatus == 'save' || this.action == 'view' ) {
			return false;
		}
		
		this.formStatus = 'save';
		
		var win = this;
		var form = this.LabRequestEditForm;
		var base_form = form.getForm();
		
		if (this.OuterKzDirection && Ext.isEmpty(base_form.findField('MedService_id').getValue()))
			base_form.findField('MedService_id').setValue(this.MedService_id);

		if (getRegionNick() == 'kareliya' && !Ext.isEmpty(base_form.findField('PayType_id').getValue()) && base_form.findField('PayType_id').getFieldValue('PayType_SysNick') == 'oms' && Ext.isEmpty(base_form.findField('Diag_id').getValue())) {
			sw.swMsg.alert(langs('Ошибка'), 'При выборе вида оплаты «ОМС» необходимо указать диагноз');
			this.formStatus = 'edit';
			return false;
		}

		if (!base_form.isValid()) {
			sw.swMsg.show(
				{
					buttons:Ext.Msg.OK,
					fn:function () {
						this.formStatus = 'edit';
						form.getFirstInvalidEl().focus(true);
					}.createDelegate(this),
					icon:Ext.Msg.WARNING,
					msg:ERR_INVFIELDS_MSG,
					title:ERR_INVFIELDS_TIT
				});
			return false;
		}
		
		if (getRegionNick() === 'ekb')
		{
			if ( getOthersOptions().checkEvnDirectionDate)
			{
				var EvnDirection_setDT = base_form.findField('EvnDirection_setDT').getValue(),
					idx = win.LabSamplesStore.findBy(function(rec)
					{
						var dt = rec.get('EvnLabSample_setDT'),
							dt = dt ? Date.parseDate(dt, 'H:i d.m.Y') : null,
							EvnLabSample_setDT = dt instanceof Date ? (() => {dt.setHours(0,0,0,0); return dt})() : null;

						if (EvnLabSample_setDT instanceof Date &&  EvnDirection_setDT instanceof Date && EvnDirection_setDT.getTime() > EvnLabSample_setDT.getTime())
						{
							return true;
						}
					});

				if (idx != -1)
				{
					this.formStatus = 'edit';
					Ext.Msg.alert(langs('Ошибка'), langs('Дата выписки направления позже даты взятия пробы. Дата направления должна быть раньше или совпадать с датой взятия пробы. Проверьте дату взятия пробы или дату направления'));
					return false;
				}
			}
		}
		
		win.showLoadMask(langs('Подождите, идет сохранение...'));

		var params = new Object();
		params.action = win.action;

		if (base_form.findField('PrehospDirect_id').getValue() == 2 || (getRegionNick() == 'kz' && base_form.findField('PrehospDirect_id').getValue() == 9)) {
			params.Lpu_sid = base_form.findField('Org_sid').getValue();
		}

		if (base_form.findField('PayType_id').disabled) {
			params.PayType_id = base_form.findField('PayType_id').getValue();
		}

		if (base_form.findField('PrehospDirect_id').disabled) {
			params.PrehospDirect_id = base_form.findField('PrehospDirect_id').getValue();
		}

		if (base_form.findField('Diag_id').disabled) {
			params.Diag_id = base_form.findField('Diag_id').getValue();
		}

		if (options.ignoreCheckPayType) {
			params.ignoreCheckPayType = options.ignoreCheckPayType;
		}

		if (getRegionNick() == 'ufa') {
			params.CovidContingentType_id = base_form.findField('CovidContingentType_id').getValue();
		}

		base_form.submit({
			params:params,
			failure:function (result_form, action) {
				this.formStatus = 'edit';
				win.hideLoadMask();
				if ( action.result ) {
					if ( action.result.Alert_Msg ) {
						var msg = action.result.Alert_Msg;
						sw.swMsg.show({
							buttons: Ext.Msg.YESNOCANCEL,
							fn: function(buttonId, text, obj) {
								if ( buttonId == 'yes' ) {
									if (action.result.Error_Code == 101) {
										options.ignoreCheckPayType = 2;
									}

									this.doSave(options);
								} else if ( buttonId == 'no' ) {
									if (action.result.Error_Code == 101) {
										options.ignoreCheckPayType = 1;
									}

									this.doSave(options);
								}
							}.createDelegate(this),
							icon: Ext.MessageBox.QUESTION,
							msg: msg,
							title: 'Продолжить сохранение?'
						});
					} else if ( action.result.Error_Msg ) {
						sw.swMsg.alert('Ошибка', action.result.Error_Msg);
					}
					else {
						sw.swMsg.alert('Ошибка', 'При сохранении произошли ошибки [Тип ошибки: 1]');
					}
				}
			}.createDelegate(this),
			success:function (result_form, action) {
				win.hideLoadMask();
				win.EvnLabSample_saveFiles();//сохраняем в базе ссылки на файлы

				var response = Ext.util.JSON.decode(action.response.responseText);
				if (response.success) {
					base_form.findField('EvnLabRequest_id').setValue(response.EvnLabRequest_id);
					base_form.findField('EvnDirection_id').setValue(response.EvnDirection_id);

					base_form.findField('Mes_id').getStore().removeAll();
					base_form.findField('Mes_id').getStore().baseParams.EvnLabRequest_id = base_form.findField('EvnLabRequest_id').getValue();
					base_form.findField('Mes_id').getStore().baseParams.query = '';

					if (response.Alert_Msg) {
						Ext.Msg.alert(langs('Сохранение протокола исследования'), response.Alert_Msg);
					}

					var params = {EvnLabRequest_id: response.EvnLabRequest_id}

					if (options.callback && typeof options.callback === 'function') {
						params.callback = options.callback;
					}

					if (options.onSave) {

						win.formStatus = 'edit';
						options.onSave();

					} else win.hide();

					win.callback(params);
				}
				return true;
			}
		});

		return true;
	},
	getEvnDirectionNumber:function () {
		if (this.action == 'view') {
			return false;
		}

		var win = this;
		var base_form = win.LabRequestEditForm.getForm();
		var field = base_form.findField('EvnDirection_Num');

		if (field.disabled) {
			return false;
		}
		
		win.showLoadMask(langs('Получение номера направления...'));
		Ext.Ajax.request({
			params: {
				MedService_id: win.MedService_id
			},
			callback:function (options, success, response) {
				win.hideLoadMask();
				if (success) {
					var response_obj = Ext.util.JSON.decode(response.responseText);
					if (response_obj.EvnPL_NumCard) {
						field.setValue(response_obj.EvnPL_NumCard);
					}
					field.focus();
				}
				else {
					sw.swMsg.alert(langs('Ошибка'), langs('Ошибка при определении номера направления'));
				}
			},
			url:'/?c=EvnLabRequest&m=getEvnDirectionNumber'
		});
	},
	/*вычисляет контрольную сумму для EAN13*/
	calculateEan13Checksum:function (code) {
		var odd = true;
		var result = 0;
		var keys1 = [0, 1, 2, 3, 4, 5, 6, 7, 8, 9];
		var c = code.length;
		var multiplier;
		for (var i = c; i > 0; i--) {
			if (odd === true) {
				multiplier = 3;
				odd = false;
			} else {
				multiplier = 1;
				odd = true;
			}

			if (keys1.indexOf(parseInt(code[i - 1])) == -1) {
				return false;
			}

			result += keys1[parseInt(code[i - 1])] * multiplier;
		}
		result = (10 - result % 10) % 10;
		return result;
	},
	selectPrehospDirect: function (combo, record, index, first_load){
		var base_form = this.LabRequestEditForm.getForm();
		//var record = combo.getStore().getById(index);
		var org_combo = base_form.findField('Org_sid');
		var lpu_section_combo = base_form.findField('LpuSection_id');
		var medstafffact_combo = base_form.findField('MedStaffFact_id');
		var ward_field = base_form.findField('EvnLabRequest_Ward');

		var prehosp_direct_code = null;
		var lpusection_id = lpu_section_combo.getValue();
		var org_id = org_combo.getValue();
		var org_type = '';
		if (!first_load) {
			lpu_section_combo.clearValue();
			org_combo.clearValue();
			medstafffact_combo.clearValue();
			ward_field.setValue(null);
			org_id = null;
		}

		lpu_section_combo.getStore().removeAll();
		medstafffact_combo.getStore().removeAll();
		if (record) {
			prehosp_direct_code = record.get('PrehospDirect_Code');
		}
		lpu_section_combo.setAllowBlank(true);
		if(getRegionNick() == 'kz'){
			switch (parseInt(prehosp_direct_code)) {
				case 1: // ПСМП
					org_type = 'lpu';
					if (!first_load) {
						org_id = getGlobalOptions().org_id;
					}
					lpu_section_combo.enable();
					lpu_section_combo.setAllowBlank(true);
					org_combo.disable();
					medstafffact_combo.enable();
					ward_field.enable();
					break;
				case 2: // КДП
					org_type = 'lpu';
					lpu_section_combo.enable();
					lpu_section_combo.setAllowBlank(true);
					org_combo.disable();
					if (!first_load) {
						org_id = getGlobalOptions().org_id;
					}
					medstafffact_combo.enable();
					ward_field.enable();
					break;
				case 5:
					org_type = 'military';
					org_combo.enable();
					lpu_section_combo.disable();
					break;
				case 3: // Другие организации
				case 4:
				case 6:
				case 9:
					org_type = 'lpu';
					lpu_section_combo.disable();
					medstafffact_combo.disable();
					org_combo.enable();
					ward_field.disable();
					break;
				case 7:
					org_type = 'org';
					org_combo.enable();
					lpu_section_combo.disable();
					break;
				case 8:
					org_type = 'lpu';
					org_combo.disable();
					if (!first_load) {
						org_id = getGlobalOptions().org_id;
					}
					lpu_section_combo.enable();
					medstafffact_combo.enable();
					ward_field.enable();
					break;
	            default:
					lpu_section_combo.disable();
					medstafffact_combo.disable();
					org_combo.disable();
					ward_field.disable();
	                // на тестовом есть такие направления, пока не понятно что делать
	                // но комбики загрузить надо
					//if (prehosp_direct_code == null) return false;
					break;
			}
		} else {
			switch (parseInt(prehosp_direct_code)) {
				case 1: // отделение ЛПУ
					org_type = 'lpu';
					if (!first_load) {
						org_id = getGlobalOptions().org_id;
					}
					lpu_section_combo.enable();
					lpu_section_combo.setAllowBlank(false);
					org_combo.disable();
					medstafffact_combo.enable();
					ward_field.enable();
					break;
				case 2: // Другое ЛПУ
					org_type = 'lpu';
					lpu_section_combo.enable();
					org_combo.enable();
					medstafffact_combo.enable();
					ward_field.enable();
					break;
				case 4:
					org_type = 'military';
				case 3: // Другие организации
				case 5:
				case 6:
					org_type = 'org';
					lpu_section_combo.disable();
					medstafffact_combo.disable();
					org_combo.enable();
					ward_field.disable();
					break;
	            default:
					lpu_section_combo.disable();
					medstafffact_combo.disable();
					org_combo.disable();
					ward_field.disable();
	                // на тестовом есть такие направления, пока не понятно что делать
	                // но комбики загрузить надо
					//if (prehosp_direct_code == null) return false;
					break;
			}
		}

		if (org_id>0) {
			org_combo.getStore().load({
				callback:function (records, options, success) {
					if (success) {
						org_combo.setValue(org_id);
						org_combo.fireEvent('change', org_combo, org_id);
					}
				},
				params: {
					Org_id: org_id/*,
					OrgType: org_type*/
				}
			});
		}

	},
	coeffRefValues: function(rec, coeff) {
		if (!Ext.isEmpty(coeff)) {
			var UslugaTest_ResultLower = rec.get('UslugaTest_ResultLower');
			var UslugaTest_ResultUpper = rec.get('UslugaTest_ResultUpper');
			var UslugaTest_ResultLowerCrit = rec.get('UslugaTest_ResultLowerCrit');
			var UslugaTest_ResultUpperCrit = rec.get('UslugaTest_ResultUpperCrit');
			var UslugaTest_ResultValue = rec.get('UslugaTest_ResultValue');

			if ( !Ext.isEmpty(UslugaTest_ResultLower) ) {
				UslugaTest_ResultLower = UslugaTest_ResultLower.toString().replace(',', '.');
			}

			if ( !Ext.isEmpty(UslugaTest_ResultUpper) ) {
				UslugaTest_ResultUpper = UslugaTest_ResultUpper.toString().replace(',', '.');
			}

			if ( !Ext.isEmpty(UslugaTest_ResultLowerCrit) ) {
				UslugaTest_ResultLowerCrit = UslugaTest_ResultLowerCrit.toString().replace(',', '.');
			}

			if ( !Ext.isEmpty(UslugaTest_ResultUpperCrit) ) {
				UslugaTest_ResultUpperCrit = UslugaTest_ResultUpperCrit.toString().replace(',', '.');
			}

			if ( !Ext.isEmpty(UslugaTest_ResultValue) ) {
				UslugaTest_ResultValue = UslugaTest_ResultValue.toString().replace(',', '.');
			}

			if (!Ext.isEmpty(UslugaTest_ResultLower)) {
				UslugaTest_ResultLower = UslugaTest_ResultLower * coeff;
			}
			
			if (!Ext.isEmpty(UslugaTest_ResultUpper)) {
				UslugaTest_ResultUpper = UslugaTest_ResultUpper * coeff;
			}
			
			if (!Ext.isEmpty(UslugaTest_ResultLowerCrit)) {
				UslugaTest_ResultLowerCrit = UslugaTest_ResultLowerCrit * coeff;
			}
			
			if (!Ext.isEmpty(UslugaTest_ResultUpperCrit)) {
				UslugaTest_ResultUpperCrit = UslugaTest_ResultUpperCrit * coeff;
			}
			
			if (!Ext.isEmpty(UslugaTest_ResultValue) && !isNaN(parseFloat(UslugaTest_ResultValue))) {
				UslugaTest_ResultValue = UslugaTest_ResultValue * coeff;
			}
			
			rec.set('UslugaTest_ResultNorm',UslugaTest_ResultLower + ' - ' + UslugaTest_ResultUpper);
			rec.set('UslugaTest_ResultCrit',UslugaTest_ResultLowerCrit + ' - ' + UslugaTest_ResultUpperCrit);
			rec.set('UslugaTest_ResultLower',UslugaTest_ResultLower);
			rec.set('UslugaTest_ResultUpper',UslugaTest_ResultUpper);
			rec.set('UslugaTest_ResultLowerCrit',UslugaTest_ResultLowerCrit);
			rec.set('UslugaTest_ResultUpperCrit',UslugaTest_ResultUpperCrit);
			rec.set('UslugaTest_ResultValue',UslugaTest_ResultValue);
		}
	},
	setRefValues: function(rec, refvalues) {
		if (!Ext.isEmpty(refvalues.UslugaTest_ResultQualitativeNorms)) {
			rec.set('UslugaTest_ResultQualitativeNorms', refvalues.UslugaTest_ResultQualitativeNorms);
			var resp = Ext.util.JSON.decode(refvalues.UslugaTest_ResultQualitativeNorms);
			var UslugaTest_ResultNorm = '';
			for (var k1 in resp) {
				if (typeof resp[k1] != 'function') {
					if (UslugaTest_ResultNorm.length > 0) {
						UslugaTest_ResultNorm = UslugaTest_ResultNorm + ', ';
					}
					
					UslugaTest_ResultNorm = UslugaTest_ResultNorm + resp[k1];
				}
			}
			rec.set('UslugaTest_ResultNorm',UslugaTest_ResultNorm);
			rec.set('UslugaTest_ResultCrit','');
			rec.set('UslugaTest_ResultLower','');
			rec.set('UslugaTest_ResultUpper','');
			rec.set('UslugaTest_ResultLowerCrit','');
			rec.set('UslugaTest_ResultUpperCrit','');
			rec.set('UslugaTest_ResultUnit', refvalues.UslugaTest_ResultUnit);
			rec.set('UslugaTest_Comment', refvalues.UslugaTest_Comment);
			rec.set('RefValues_Name', refvalues.RefValues_Name);
			rec.set('RefValues_id', refvalues.RefValues_id);
			rec.set('Unit_id', refvalues.Unit_id);
		} else {
			rec.set('UslugaTest_ResultQualitativeNorms', '');
			// избавляемся от null'ов:								
			if (Ext.isEmpty(refvalues.UslugaTest_ResultLower)) {
				refvalues.UslugaTest_ResultLower = '';
			}
			if (Ext.isEmpty(refvalues.UslugaTest_ResultUpper)) {
				refvalues.UslugaTest_ResultUpper = '';
			}
			if (Ext.isEmpty(refvalues.UslugaTest_ResultLowerCrit)) {
				refvalues.UslugaTest_ResultLowerCrit = '';
			}
			if (Ext.isEmpty(refvalues.UslugaTest_ResultUpperCrit)) {
				refvalues.UslugaTest_ResultUpperCrit = '';
			}
													
			rec.set('UslugaTest_ResultNorm',refvalues.UslugaTest_ResultLower + ' - ' + refvalues.UslugaTest_ResultUpper);
			rec.set('UslugaTest_ResultCrit',refvalues.UslugaTest_ResultLowerCrit + ' - ' + refvalues.UslugaTest_ResultUpperCrit);
			rec.set('UslugaTest_ResultLower',refvalues.UslugaTest_ResultLower);
			rec.set('UslugaTest_ResultUpper',refvalues.UslugaTest_ResultUpper);
			rec.set('UslugaTest_ResultLowerCrit',refvalues.UslugaTest_ResultLowerCrit);
			rec.set('UslugaTest_ResultUpperCrit',refvalues.UslugaTest_ResultUpperCrit);
			rec.set('UslugaTest_ResultUnit', refvalues.UslugaTest_ResultUnit);
			rec.set('UslugaTest_Comment', refvalues.UslugaTest_Comment);
			rec.set('RefValues_Name', refvalues.RefValues_Name);
			rec.set('RefValues_id', refvalues.RefValues_id);
			rec.set('Unit_id', refvalues.Unit_id);
		}
	},
	queueUpdateEvnLabSample: [],
	processQueueUpdateEvnLabSample: function() {
		var win = this;

		// работаем с очередью
		if (win.queueUpdateEvnLabSample.length < 1) {
			return false;
		}

		// берём первые параметры из очереди
		var params = win.queueUpdateEvnLabSample[0].params;
		var o = win.queueUpdateEvnLabSample[0].o;

		// признак АРМ Лаборанта для расчетных тестов
		params.EvnLabSample_id = Number(o.record.json.EvnLabSample_id);
		params.UslugaTest_Code = o.record.json.UslugaComplex_Code;

		Ext.Ajax.request({
			url: '/?c=EvnLabSample&m=updateResult',
			params: params,
			failure: function(response, options) {
				// убираем из очереди первый элемент и снова обрабатываем
				win.queueUpdateEvnLabSample.shift();
				win.processQueueUpdateEvnLabSample();
			},
			success: function(response, action) {
			    win.queueUpdateEvnLabSample.shift();
			    win.processQueueUpdateEvnLabSample();

			    var result = Ext.util.JSON.decode(response.responseText);

			    if (result[0].Error_Code === null && result[0].Error_Msg === null) {
			        if (o.record) {
			            o.record.commit();
			        }

			        // если есть расчетные тесты
			        if(result[1] !== null)
			        {
			            // по массиву
			            o.grid.getStore().each(function(rec){
			                for(v in result[1]) {
			                	if(rec.get('UslugaComplex_Code') == result[1][v].code)
			                	{
			                	    rec.set('UslugaTest_ResultValue',result[1][v].value);
			                	    rec.set('UslugaTest_setDT', new Date());

			                	    // Автоодобрение расчетных тестов
			                	    if(rec.get('Analyzer_IsAutoOk') == 2) {
			                	        if(result[1][v].value !== '') {
			                	            rec.set('UslugaTest_ResultApproved', 2);
			                	            rec.set('UslugaTest_Status', langs('Одобрен'));
			                	        } else {
			                	            rec.set('UslugaTest_ResultApproved', 1);
			                	            rec.set('UslugaTest_Status', langs('Назначен'));
			                	        }
			                	    } else {
			                	        rec.set('UslugaTest_ResultApproved', 1);
			                	        rec.set('UslugaTest_Status', langs('Выполнен'));
			                	    }
			                	    
			                	    rec.commit();
			                	}
			                }
			            });
			        }
			    } else {
			        sw.swMsg.show({
			            icon: Ext.MessageBox.WARNING,
			            buttons: Ext.Msg.OK,
			            msg: langs('При сохранении результатов и проверке статуса заявки произошло блокирование записи другими процессами. Проверьте данные, при необходимости исправьте и повторите попытку сохранения.'),
			            title: langs('Ошибка'),
			            fn: function() {
			                if (o.grid) {
			                    o.grid.getStore().reload();
			                }
			            }
			        });
			    }
			}
		});
	},
	EvnLabSample_addFile: function(EvnUslugaPar_pid) {
		var win = this;
		var fileIndex;
		getWnd('swFileUploadWindow').show({				//вызов формы загрузки файла
			saveUrl: '/?c=EvnMediaFiles&m=uploadFile',
			enableFileDescription: true,
			callback: function(response) {
				if(win.labSampleFiles[EvnUslugaPar_pid] == undefined) {
					win.labSampleFiles[EvnUslugaPar_pid] = {
						fileIndex: 0,
						files: []
					};
					fileIndex = 0;
				} else {
					win.labSampleFiles[EvnUslugaPar_pid].fileIndex ++;
					fileIndex = win.labSampleFiles[EvnUslugaPar_pid].fileIndex;
				}
				
				var Evn_id = win.EvnDirection_id;
				var fileInfo =	JSON.parse(response)[0];
				fileInfo.state = 'add';
				win.labSampleFiles[EvnUslugaPar_pid].files[fileIndex] = fileInfo;
				
				var fileContainer = document.getElementById(win.id + 'EvnLabSample_File_text_' + EvnUslugaPar_pid);
				var fileHTML = '<span id="EvnSampleFile_'+ EvnUslugaPar_pid +'_'+ fileIndex +'">';
				fileHTML += '<a class="editResearchLink" target="_blank" href="' + fileInfo.upload_dir + fileInfo.file_name + '" style="color:#000;" >Файл '+ (fileIndex + 1) +': ' + fileInfo.orig_name + '</a> ';
				fileHTML += '<a class="editResearchLink" href="#" style="color:#000;" onClick="Ext.getCmp(\'' + win.id + '\').EvnLabSample_deleteFile('+ EvnUslugaPar_pid +', '+ fileIndex +');">Удалить</a><br>';
				fileHTML += '</span>';
				
				fileContainer.innerHTML += fileHTML;
			}
		});
	},
	EvnLabSample_saveFiles: function() {
		var win = this,
			changedData = [],
			data,
			fileIndex;

		for(var EvnUslugaPar_pid in win.labSampleFiles ) {
			fileIndex = 0;
			for(var fileInfo of win.labSampleFiles[EvnUslugaPar_pid].files) {
				data = {
					EvnMediaData_FilePath: fileInfo.file_name,
					EvnMediaData_FileName: fileInfo.orig_name,
					EvnMediaData_Comment: fileInfo.description
				};
				if(fileInfo.EvnMediaData_id) {
					data.EvnMediaData_id = fileInfo.EvnMediaData_id;
				}
				if(fileInfo.state) {
					data.state = fileInfo.state;
					changedData.push(data);
				}
			}
			if(changedData.length) {
				Ext.Ajax.request({
					url: '/?c=EvnMediaFiles&m=saveChanges' ,
					params: {
						Evn_id: EvnUslugaPar_pid,
						changedData: JSON.stringify(changedData)
					},
					callback: function(opr, success, response) {
			
					}
				});
			}
			changedData = [];
		}		
	},
	EvnLabSample_deleteFile: function(EvnUslugaPar_pid, index, saved) {
		var win = this;
		var dom_id = 'EvnSampleFile_'+ EvnUslugaPar_pid + '_' + index;
		sw.swMsg.show({
			buttons:Ext.Msg.YESNO,
			fn:function (buttonId, text, obj) {
				if (buttonId == 'yes') {
					if (saved) {
						dom_id = 'EvnSampleFile_' + index;
						for(var fileInfo of win.labSampleFiles[EvnUslugaPar_pid].files) {
							if(fileInfo.EvnMediaData_id == index) {
								fileInfo.state = 'delete';
							}
						}
					} else {
						win.labSampleFiles[EvnUslugaPar_pid].files.splice(index);
					}
					Ext.get(dom_id).remove();
				}
			},
			icon:Ext.MessageBox.QUESTION,
			msg:langs('Вы хотите удалить запись?'),
			title:langs('Подтверждение')
		});
		
		
		

		
		

	},
	updateEvnLabSample: function(params, o) {
		var win = this;

		// добавляем в очередь
		win.queueUpdateEvnLabSample.push({
			params: params,
			o: o
		});

		// если в очереди уже что то было, выходим
		if (win.queueUpdateEvnLabSample.length > 1) {
			return false;
		}

		win.processQueueUpdateEvnLabSample();
	},
	getEvnLabSampleIdByFrameId: function(frameId) {
		var win = this;
		if (win.LabSamplesStore.getById(frameId) && win.LabSamplesStore.getById(frameId).get('EvnLabSample_id')) {
			return win.LabSamplesStore.getById(frameId).get('EvnLabSample_id');
		}

		return null;
	},
	openEvnLabSampleEditWindow: function(frameId) {
		var win = this;
		var base_form = win.LabRequestEditForm.getForm();
		
		var EvnLabSample_id = win.getEvnLabSampleIdByFrameId(frameId);
		if (Ext.isEmpty(EvnLabSample_id)) {
			sw.swMsg.alert(langs('Ошибка'), langs('Для изменения параметров пробы необходимо сначала взять пробу'));
			return false;
		}
		
		// если уж загрузка до показа формы, то надо хотя бы показать, что что то делается.
		win.showLoadMask(langs('Загрузка данных пробы...'));
		Ext.Ajax.request({
			url: '/?c=EvnLabSample&m=load',
			params:{
				EvnLabSample_id: EvnLabSample_id
			},
			callback: function(opt, success, response) {
				win.hideLoadMask();
				if (success && response.responseText != '') {
					var result = Ext.util.JSON.decode(response.responseText);
					var params = new Object();
					if (win.action == 'view') {
						params.action = 'view';
					} else {
						params.action = 'edit';
					}
					params.formParams = new Object();
					params.formParams = result[0];
					params.formParams.EvnLabSample_ShortNum = params.formParams.EvnLabSample_Num.substr(-4);
					params.onHide = function() {
						// g.getView().focusRow(g.getStore().indexOf(selection));
					};

                    params.callback = function(data) {
						win.findById(win.id + '_LabSampleNumInner_' + frameId).setText('<a class="LabSampleNum" href="javascript://" onClick="Ext.getCmp(\'EvnLabRequestEditWindow\').openEvnLabSampleEditWindow(\'' + frameId + '\');">Проба № ' + data.EvnLabSample_ShortNum + '</a>', false);
						win.findById(win.id + '_LabSampleBarCodeContInner_' + frameId).getEl().query('.LabSampleBarCode')[0].innerHTML = data.EvnLabSample_BarCode;

						base_form.findField('UslugaExecutionType_id').setValue(data.UslugaExecutionType_id);
					};
					
					params.Person_id = params.formParams.Person_id;
					params.MedService_id = params.formParams.MedService_id;
					params.EvnDirection_id = base_form.findField('EvnDirection_id').getValue();
					// params.UslugaComplexTarget_id = base_form.findField('UslugaComplex_id').getValue();
					params.ARMType = win.ARMType;

					getWnd('swLabSampleEditWindow').show(params);
				}
			}
		});
	},
	showInputBarCodeField: function(frameId, element) {
		var win = this;
		var base_form = win.LabRequestEditForm.getForm();
		var EvnLabSample_id = win.getEvnLabSampleIdByFrameId(frameId);
		if (Ext.isEmpty(EvnLabSample_id)) {
			sw.swMsg.alert(langs('Ошибка'), langs('Для изменения штрих-кода пробы необходимо сначала взять пробу'));
			return false;
		}
		
		var oldBarCode = element.innerHTML;
		var inputPlace = win.id + '_LabSampleBarCode_' + frameId;
		
		Ext.get(inputPlace).setDisplayed('none');
		Ext.get(inputPlace + '_inp').setDisplayed('block');
		
		var cmp = new Ext.form.TextField({
			hideLabel: true
			,renderTo: inputPlace + '_inp'
			,width: 100
			,listeners:
			{
				blur: function(f) {
					Ext.get(inputPlace).setDisplayed('block');
					Ext.get(inputPlace + '_inp').setDisplayed('none');
					f.destroy();
				},
				render: function(f) {
					f.setValue(oldBarCode);
					f.focus(true);
				},
				change: function(f,n,o) {
					if (!Ext.isEmpty(n) && n != oldBarCode) {
						// проверить на уникальность и обновить в БД
						win.showLoadMask(langs('Сохранение штрих-кода'));
						Ext.Ajax.request({
							url: '/?c=EvnLabSample&m=saveNewEvnLabSampleBarCode',
							params: {
								EvnLabSample_id: EvnLabSample_id,
								EvnLabSample_BarCode: n
							},
							callback: function(opt, success, response) {
								win.hideLoadMask();
								if (success && response.responseText != '') {
									var result = Ext.util.JSON.decode(response.responseText);
									if (result.success) {
										element.innerHTML = n;
										var num = n.substr(-4);
										// если сохранился штрих-код, предлагаем менять номер пробы
										Ext.Msg.show({
											title: 'Внимание',
											msg: 'Штрих код изменен на №'+ n +'. Изменить номер пробы на №'+num+'?',
											buttons: Ext.Msg.YESNO,
											fn: function(btn) {
												if (btn === 'yes') {
													win.getLoadMask("Сохранение номера пробы...").show();
													Ext.Ajax.request({
														params: {
															EvnLabSample_id: EvnLabSample_id,
															EvnLabSample_ShortNum: num
														},
														url: '/?c=EvnLabSample&m=saveNewEvnLabSampleNum',
														callback: function(options, success, response) {
															win.getLoadMask().hide();
															if(success) {
																var result = Ext.util.JSON.decode(response.responseText);
																if (result.success) {
																	win.findById(win.id + '_LabSampleNumInner_' + frameId).setText('<a class="LabSampleNum" href="javascript://" onClick="Ext.getCmp(\'EvnLabRequestEditWindow\').openEvnLabSampleEditWindow(\'' + frameId + '\');">Проба № ' + num + '</a>', false);
																}
															}
														}
													});
												}
											},
											icon: Ext.MessageBox.QUESTION
										});
									}
								}
							}
						});
					}
				}
			}
		});
		
		// cmp.focus(true, 500);
	},
	takeLabSample: function(frameId) {
		var win = this;
		if (win.action == 'view') {
			return false;
		}
		var base_form = win.LabRequestEditForm.getForm();

		var EvnLabSample_id = win.getEvnLabSampleIdByFrameId(frameId);
		if (Ext.isEmpty(EvnLabSample_id)) {
			sw.swMsg.alert(langs('Ошибка'), langs('Проба не сохранена, взятие пробы невозможно'));
			return false;
		}

		// проверяем что для пробы есть назначенные тесты, иначе её нельзя взять
		var naz = false;
		win.findById(win.id + '_EvnUslugaDataGrid_' + frameId).getGrid().getStore().each(function(rec) {
			if (rec.get('UslugaTest_Status') == langs('Назначен')) {
				naz = true;
			}
		});
		if (!naz) {
			sw.swMsg.alert(langs('Ошибка'), langs('Нельзя взять пробу без назначенных тестов'));
			return false;
		}
		
		var params = {
			EvnLabRequest_id: base_form.findField('EvnLabRequest_id').getValue(),
			MedServiceType_SysNick: win.ARMType,
			MedService_did: win.MedService_id,
			EvnLabSample_id: EvnLabSample_id
		};
		
		if (getRegionNick() == 'ufa' && !win.ARMType.inlist(['pzm','reglab'])) {
			params.sendToLis = 1;
		}

		// Ajax-запрос на взятие пробы, параметры: EvnLabRequest_id
		win.showLoadMask(langs('Взятие пробы'));
		Ext.Ajax.request({
			failure:function () {
				win.hideLoadMask();
				sw.swMsg.alert(langs('Ошибка при взятии пробы'), langs('Не удалось получить данные с сервера'));
			},
			params: params,
			success:function (response) {
				win.hideLoadMask();
				var result = Ext.util.JSON.decode(response.responseText);
				if (result[0])
					result = result[0];
				if (result && result.Alert_Msg) {
					sw.swMsg.alert(langs('Ошибка отправки на анализатор'), result.Alert_Msg);
				}
				if (result && result.EvnLabSample_setDT) {
					// показываем поле результат в заявке
					if (win.ARMType != 'pzm') {
						base_form.findField('UslugaExecutionType_id').showContainer();
					}

					// если всё ок -> крутим кард панел, проставляем дату взятия, обновляем грид результатов
					win.findById(win.id + '_LabSampleSetDTCardPanel_' + frameId).getLayout().setActiveItem(1);
					win.findById(win.id + '_LabSampleSetDT_' + frameId).setText(langs('Взята') + ': ' + result.EvnLabSample_setDT);
					win.findById(win.id + '_LabSampleNumInner_' + frameId).setText('<a class="LabSampleNum" href="javascript://" onClick="Ext.getCmp(\'EvnLabRequestEditWindow\').openEvnLabSampleEditWindow(\'' + frameId + '\');">Проба № ' + result.EvnLabSample_ShortNum + '</a>', false);
					win.findById(win.id + '_LabSampleBarCodeContInner_' + frameId).setText('<div style="float: left;">Штрих-код:&nbsp;</div><div style="float: left;" id="'+win.id+'_LabSampleBarCode_'+frameId +'_inp"></div><div style="float: left;" id="'+win.id+'_LabSampleBarCode_'+frameId+'"><a class="LabSampleBarCode" href="javascript://" onClick="Ext.getCmp(\'EvnLabRequestEditWindow\').showInputBarCodeField(\''+frameId+'\', this);">' + result.EvnLabSample_BarCode + '</a></div>', false);

					win.findById(win.id + '_LabSampleNum_' + frameId).show();
					win.findById(win.id + '_LabSampleBarCodeCont_' + frameId).show();
					win.findById(win.id + '_PrintLabSample_' + frameId).items.items[0].menu.items.itemAt(0).setDisabled(false);
					
					Ext.get(win.id + '_addEvnXml_' + frameId).show();

					var labsample_rec = win.LabSamplesStore.getById(frameId);
					labsample_rec.set('EvnLabSample_setDT', result.EvnLabSample_setDT);
					labsample_rec.set('EvnLabSample_ShortNum', result.EvnLabSample_ShortNum);
					labsample_rec.set('EvnLabSample_BarCode', result.EvnLabSample_BarCode);

					var params = {
						EvnLabSample_id: EvnLabSample_id
					};
					
					win.findById(win.id + '_EvnUslugaDataGrid_' + frameId).loadData({
						params: params,
						globalFilters: params,
						noFocusOnLoad: true
					});
				}
			},
			url: '/?c=EvnLabSample&m=takeLabSample'
		});
	},
	sendToLis: function(frameId, options) {
		if ( typeof options != 'object' ) {
			options = new Object();
		}

		var win = this;
		if (win.action == 'view') {
			return false;
		}
		
		var EvnLabSample_id = win.getEvnLabSampleIdByFrameId(frameId);
		if (Ext.isEmpty(EvnLabSample_id)) {
			sw.swMsg.alert(langs('Ошибка'), langs('Для отправки на анализатор необходимо сначала взять пробу'));
			return false;
		}
		
		var params = {}
		params.EvnLabSamples = Ext.util.JSON.encode([ EvnLabSample_id ]);
		if (options.onlyNew) {
			params.onlyNew = options.onlyNew;
		}
		if (options.changeNumber) {
			params.changeNumber = options.changeNumber;
		}
		win.showLoadMask(langs('Создание заявки для анализатора'));
		Ext.Ajax.request({
			url: '/?c='+getLabController()+'&m=createRequestSelections',
			params: params,
			callback: function(opt, success, response) {
				win.hideLoadMask();
				if (success && response.responseText != '') {
					var result = Ext.util.JSON.decode(response.responseText),
						labsample_rec = win.LabSamplesStore.getById(frameId);
					if (result.success) {
						if (result.sysMsg) {
							showSysMsg(result.sysMsg);
						}
						if (result.EvnLabSample_ShortNum) {
							win.findById(win.id + '_LabSampleNumInner_' + frameId).setText('<a class="LabSampleNum" href="javascript://" onClick="Ext.getCmp(\'EvnLabRequestEditWindow\').openEvnLabSampleEditWindow(\'' + frameId + '\');">Проба № ' + result.EvnLabSample_ShortNum + '</a>', false);
							win.findById(win.id + '_LabSampleBarCodeContInner_' + frameId).setText('<div style="float: left;">Штрих-код:&nbsp;</div><div style="float: left;" id="'+win.id+'_LabSampleBarCode_'+frameId +'_inp"></div><div style="float: left;" id="'+win.id+'_LabSampleBarCode_'+frameId+'"><a class="LabSampleBarCode" href="javascript://" onClick="Ext.getCmp(\'EvnLabRequestEditWindow\').showInputBarCodeField(\''+frameId+'\', this);">' + result.EvnLabSample_BarCode + '</a></div>', false);
							labsample_rec.set('EvnLabSample_ShortNum', result.EvnLabSample_ShortNum);
							labsample_rec.set('EvnLabSample_BarCode', result.EvnLabSample_BarCode);
						}
						if (result.EvnLabSample_setDT) {
							win.findById(win.id + '_LabSampleSetDT_' + frameId).setText(langs('Взята') + ': ' + result.EvnLabSample_setDT);
							labsample_rec.set('EvnLabSample_setDT', result.EvnLabSample_DelivDT);
						}
						if (result.Alert_Code) {
							switch(result.Alert_Code) {
								case 100:
									sw.swMsg.show({
										buttons: {
											yes: langs('Только новые'),
											no: langs('Все'),
											cancel: langs('Отмена')
										},
										fn: function(buttonId, text, obj) {
											if ( buttonId == 'yes' ) {
												options.onlyNew = 2;
												win.sendToLis(frameId, options);
											} else if (buttonId == 'no') {
												options.onlyNew = 1;
												win.sendToLis(frameId, options);
											}
										}.createDelegate(this),
										icon: Ext.MessageBox.QUESTION,
										msg: result.Alert_Msg,
										title: langs('Вопрос')
									});
									break;
								case 101:
									sw.swMsg.show({
										buttons: Ext.Msg.YESNOCANCEL,
										fn: function(buttonId, text, obj) {
											if ( buttonId == 'yes' ) {
												options.changeNumber = 2;
												win.sendToLis(frameId, options);
											} else if (buttonId == 'no') {
												options.changeNumber = 1;
												win.sendToLis(frameId, options);
											}
										}.createDelegate(this),
										icon: Ext.MessageBox.QUESTION,
										msg: result.Alert_Msg,
										title: langs('Вопрос')
									});
									break;
							}
						} else {
							// g.getGrid().getStore().reload();
							showSysMsg(langs('Заявка для анализатора успешно создана'), langs('Заявка для анализатора'));
						}
					} else {
						sw.swMsg.show({
							buttons: Ext.Msg.OK,
							fn: function() {
							},
							icon: Ext.Msg.WARNING,
							msg: result.Error_Msg,
							title: langs('Заявка для анализатора')
						});
					}
				} 
			}
		});
	},
	cancelTest: function(EvnUslugaDataGrid, labsample) {
		var win = this;
		if (win.action == 'view') {
			return false;
		}
		var base_form = win.LabRequestEditForm.getForm();
		var EvnLabSample_id = win.getEvnLabSampleIdByFrameId(labsample.frameId);
		
		var params = {};
		params.EvnLabSample_id = EvnLabSample_id;
		params.EvnLabRequest_id = base_form.findField('EvnLabRequest_id').getValue();
		
		var records = EvnUslugaDataGrid.getGrid().getSelectionModel().getSelections();
		var tests = [];
		for (var i = 0; i < records.length; i++) {
			if (!Ext.isEmpty(records[i].get('UslugaComplex_id'))) {
				tests.push({
					UslugaTest_pid: records[i].get('UslugaTest_pid').toString(),
					UslugaComplex_id: records[i].get('UslugaComplex_id').toString()
				});
			}
		}

		if (!Ext.isEmpty(tests) && tests.length > 0) {
			params.tests = Ext.util.JSON.encode(tests);

			sw.swMsg.show({
				icon: Ext.MessageBox.QUESTION,
				msg: langs('Выбранные тесты будут удалены. Вы действительно хотите их отменить?'),
				title: langs('Вопрос'),
				buttons: Ext.Msg.YESNO,
				fn: function(buttonId, text, obj) {
					if ('yes' == buttonId) {
						win.showLoadMask(langs('Отмена теста'));
						Ext.Ajax.request({
							url: '/?c=EvnLabSample&m=cancelTest',
							params: params,
							failure: function(response, options) {
								win.hideLoadMask();
							},
							success: function(response, action) {
								win.hideLoadMask();
								var eu_params = {
									EvnDirection_id: base_form.findField('EvnDirection_id').getValue(),
									EvnLabSample_id: EvnLabSample_id
								};
								
								EvnUslugaDataGrid.loadData({
									params: eu_params,
									globalFilters: eu_params,
									noFocusOnLoad: true
								});
								if ( getRegionNick() == 'ufa' ) {
									win.setPersonDetailVisible();
								}
							}
						});
					}
				}
			});
		}
	},
	prescrTest: function(EvnUslugaDataGrid, labsample) {
		var win = this;
		if (win.action == 'view') {
			return false;
		}
		var base_form = win.LabRequestEditForm.getForm();
		var EvnLabSample_id = win.getEvnLabSampleIdByFrameId(labsample.frameId);

		var params = {};
		params.EvnLabSample_id = EvnLabSample_id;
		params.EvnLabRequest_id = base_form.findField('EvnLabRequest_id').getValue();

		var records = EvnUslugaDataGrid.getGrid().getSelectionModel().getSelections();
		var tests = [];
		for (var i = 0; i < records.length; i++) {
			if (!Ext.isEmpty(records[i].get('UslugaComplex_id'))) {
				tests.push({
					UslugaTest_pid: records[i].get('UslugaTest_pid').toString(),
					UslugaComplex_id: records[i].get('UslugaComplex_id').toString()
				});
			}
		}
		
		if (!Ext.isEmpty(tests) && tests.length > 0) {
			params.tests = Ext.util.JSON.encode(tests);

			win.showLoadMask(langs('Назначение теста'));
			Ext.Ajax.request({
				url: '/?c=EvnLabSample&m=prescrTest',
				params: params,
				failure: function(response, options) {
					win.hideLoadMask();
				},
				success: function(response, action) {
					win.hideLoadMask();
					var eu_params = {
						EvnDirection_id: base_form.findField('EvnDirection_id').getValue(),
						EvnLabSample_id: EvnLabSample_id
					};
					
					EvnUslugaDataGrid.loadData({
						params: eu_params,
						globalFilters: eu_params,
						noFocusOnLoad: true
					});
				}
			});
		}
	},
	// <- https://redmine.swan.perm.ru/issues/106759
    /**
     * @author Gubaidullin Robert
     * @email borisworking@gmail.com
     * @copyright Copyright (c) 2017 Emsis
     */
    JsBarcodeInit:function() {
        if(document.body.className.search("x-no-print-body") == -1) {
            document.body.classList.add('x-no-print-body');
        } else {
            document.body.classList.remove('x-no-print-body');
        }

        var elements = document.getElementsByClassName('x-js-barcode');
        while(elements.length > 0){
            elements[0].parentNode.removeChild(elements[0]);
        }
    },
    JsBarcode: function(labsample) {
	    if(typeof(jsPrintSetup) !== 'undefined')
	    {
	        var win = this;
	        win.JsBarcodeInit();
	        win.JsBarcodeData(labsample);
	    } else {
	    	sw.swMsg.alert(langs('Ошибка'), langs('Установите расширение <a href="https://addons.mozilla.org/ru/firefox/addon/js-print-setup/" target="_blank">JS Print Setup</a>'));
	    }
	},
	JsBarcodeData: function(labsample) {
		var win = this;
		var form = win.LabRequestEditForm.getForm();

		if(form.findField('LpuSection_id').getRawValue() != '') {
		    var directionTemp = form.findField('LpuSection_id').getRawValue();
		} else if(form.findField('Org_sid').getFieldValue('Org_Nick') != '') {
			var directionTemp = form.findField('Org_sid').getFieldValue('Org_Nick');
		} else if (form.findField('Org_sid').getRawValue() != '') {
		    var directionTemp = form.findField('Org_sid').getRawValue();
		} else if (form.findField('PrehospDirect_id').getRawValue() != '') {
		    var directionTemp = form.findField('PrehospDirect_id').getRawValue();
		} else {
			var directionTemp = "";
		}

		var barcodeTemp = win.findById(win.id + '_LabSampleBarCodeContInner_' + labsample.frameId).getEl().query('.LabSampleBarCode')[0].innerHTML;
		var serviceTemp = win.findById(win.id + '_LabSampleMedServiceCombo_' + labsample.frameId).getEl().getValue();
		var fioTemp 	= this.Person_ShortFio;
		var uslugaTemp 	= Ext.getCmp(win.id + '_EvnUslugaDataGrid_' + labsample.frameId).getGrid().getStore().getAt(0).get('ResearchName');

		var barcode   = barcodeTemp   !== "" ? barcodeTemp   : null;
		var service   = serviceTemp   !== "" ? serviceTemp   : null;
		var fio 	  = fioTemp 	  !== "" ? fioTemp 		 : null;
		var usluga    = uslugaTemp 	  !== "" ? uslugaTemp 	 : null;
		var direction = directionTemp !== "" ? directionTemp : null;

		var print = {
		    barcode:   barcode,
		    service:   Ext.globalOptions.lis.ZebraServicesName?service:null,
		    fio: 	   Ext.globalOptions.lis.ZebraFIO?fio:null,
		    direction: Ext.globalOptions.lis.ZebraDirect_Name?direction:null,
		    usluga:    Ext.globalOptions.lis.ZebraUsluga_Name?usluga:null
		};

		console.log('print', print);
		
		win.JsBarcodeEngine(print);
	},
	JsBarcodeEngine: function(print) {
		var win = this;
        var div = document.createElement('div');
        div.className = "x-js-barcode";
        document.body.appendChild(div);

        var option = {}, printer = {};

        printer = {top:1,bottom:1,left:1,right:1};
        printer.size = Number(Ext.globalOptions.lis.barcode_size);
        printer.height = String(Ext.globalOptions.lis.barcode_size).substring(0, 2);
        printer.width = String(Ext.globalOptions.lis.barcode_size).substring(2);
        printer.count = Ext.globalOptions.lis.ZebraPrintCount !== undefined ? Number(Ext.globalOptions.lis.ZebraPrintCount) : 1;
        option.barcodeText = Ext.globalOptions.lis.ZebraSampleNumber;
        option.barcodeFormat = Ext.globalOptions.lis.barcode_format;
        option.fontFamily = 'monospace';

		if(print.barcode !== undefined) {
            var svg = document.createElementNS("http://www.w3.org/2000/svg", "svg");
            	svg.setAttribute("id", "barcode_"+print.barcode);
            	div.appendChild(svg);

            var count = 0;
            if(print.service   !== null) count++;
            if(print.fio 	   !== null) count++;
            if(print.direction !== null) count++;
            if(print.usluga    !== null) count++;

            switch(printer.size)
            {
                case 2030:
                    option.width = 4.4; option.height = 99; option.top = 161; option.left = 65;
                    option.x = 40; option.y = 36;
                break;

                case 2040:
                    option.width = 5.8; option.height = 95; option.top = 175; option.left = 85;
                    option.x = 40; option.y = 40;
                    printer.top = 2;
                    printer.bottom = 0;
                break;

                case 2540:
                    option.width = 6.2; option.height = 154; option.top = 206; option.left = 60;
                    option.x = 0; option.y = 46;
                break;

                case 3050:
                    option.width = 8; option.height = 184; option.top = 256; option.left = 70;
                    option.x = 0; option.y = 56;
                break;

                default:
                    Ext.MessageBox.getDialog().getEl().setStyle('z-index','80000'); //Т.к окно иногда бывает на заднем фоне
                    Ext.Msg.alert('Ошибка печати', 'Для принтера Zebra необходимо указать в настройках печати штрихкода ширину и высоту наклейки, выбирая из трех значений: 20x30, 20х40, 25x40 и 30х50');
                break;
            }

            switch(count)
            {
                case 0:
                case 1:
                    option.height += option.y + option.y; option.top -= option.y + option.y;
                break;

                case 2:
                    option.height += option.y; option.top -= option.y;
                break;
            }

            if(option.barcodeText === false)
            {
                option.height += option.y;
                option.top -= option.y - option.y;
            }

            if(
            	   print.fio 	   === null 
            	|| print.service   === null 
                || print.direction === null 
                || print.usluga    === null
           	  )
            {
                option.height += option.y;
                option.top -= option.y;
            }

            if(
            	   option.barcodeText === false 
            	&& print.fio 		  === null 
                && print.service 	  === null 
                && print.direction 	  === null
                && print.usluga 	  === null
              )
            {
                option.height += option.y;
                option.top -= option.y;
            }

            option.fontSize = option.y;
            option.data = print;

            win.JsBarcodeSVG(option);
        }

		win.JsBarcodePrint(printer);
    },
    JsBarcodeSVG: function(option) {
    	var barcode = String(option.data.barcode);
        JsBarcode('#barcode_'+barcode, barcode, {
            width: option.width,
            height: option.height,
            marginTop: option.top,
            marginLeft: option.left,
            displayValue:option.barcodeText,
            textAlign: "center",
            fontSize:option.fontSize,
            fontFamily:option.fontFamily,
            format:"CODE"+option.barcodeFormat,
        });

        var y = option.y; svgNS = "http://www.w3.org/2000/svg";

        if(option.data.service !== null)
        {
            var strService = document.createElementNS(svgNS,"text");
            strService.setAttributeNS(null,"x",option.x);     
            strService.setAttributeNS(null,"y",option.y);
            strService.setAttributeNS(null,"font-family",option.fontFamily);
            strService.setAttributeNS(null,"font-size",option.fontSize);
            var textService = document.createTextNode(option.data.service);
            strService.appendChild(textService);
            document.getElementById('barcode_'+barcode).appendChild(strService);
            option.y += y;
        }

        if(option.data.fio !== null)
        {
            var strFio = document.createElementNS(svgNS,"text");
            strFio.setAttributeNS(null,"x",option.x);     
            strFio.setAttributeNS(null,"y",option.y); 
            strFio.setAttributeNS(null,"font-family",option.fontFamily);
            strFio.setAttributeNS(null,"font-size",option.fontSize);
            var textFio = document.createTextNode(option.data.fio);
            strFio.appendChild(textFio);
            document.getElementById('barcode_'+barcode).appendChild(strFio);
            option.y += y;
        }

        if(option.data.direction !== null)
        {
            var strDirection = document.createElementNS(svgNS,"text");
            strDirection.setAttributeNS(null,"x",option.x);     
            strDirection.setAttributeNS(null,"y",option.y); 
            strDirection.setAttributeNS(null,"font-family",option.fontFamily);
            strDirection.setAttributeNS(null,"font-size",option.fontSize);
            var textDirection = document.createTextNode(option.data.direction);
            strDirection.appendChild(textDirection);
            document.getElementById('barcode_'+barcode).appendChild(strDirection);
            option.y += y;
        }

        if(option.data.usluga !== null)
        {
            var strUsluga = document.createElementNS(svgNS,"text");
            strUsluga.setAttributeNS(null,"x",option.x);     
            strUsluga.setAttributeNS(null,"y",option.y); 
            strUsluga.setAttributeNS(null,"font-family",option.fontFamily);
            strUsluga.setAttributeNS(null,"font-size",option.fontSize);
            var textUsluga = document.createTextNode(option.data.usluga);
            strUsluga.appendChild(textUsluga);
            document.getElementById('barcode_'+barcode).appendChild(strUsluga);
            option.y += y;
        }
    },
    JsBarcodePrint:function(printer) {
        var win = this;
        var printerDefault = jsPrintSetup.getPrinter();
        var printers = jsPrintSetup.getPrintersList();
        var arr = printers.split(',');
        var zebra = arr.filter(function(v) {
            return v == 'ZDesigner GK420t';
        });

        if(zebra.length > 0) {
            jsPrintSetup.setPrinter('ZDesigner GK420t');
            jsPrintSetup.setOption('orientation', jsPrintSetup.kPortraitOrientation);
            jsPrintSetup.setOption('marginTop', printer.top);
            jsPrintSetup.setOption('marginBottom', printer.bottom);
            jsPrintSetup.setOption('marginLeft', printer.left);
            jsPrintSetup.setOption('marginRight', printer.right);
            jsPrintSetup.setOption('headerStrLeft', '');
            jsPrintSetup.setOption('headerStrCenter', '');
            jsPrintSetup.setOption('headerStrRight', '');
            jsPrintSetup.setOption('footerStrLeft', '');
            jsPrintSetup.setOption('footerStrCenter', '');
            jsPrintSetup.setOption('footerStrRight', '');
            jsPrintSetup.setOption('title', '');
            jsPrintSetup.setOption('paperHeight', printer.height);
            jsPrintSetup.setOption('paperWidth', printer.width);
            jsPrintSetup.setOption('shrinkToFit', 1);
            jsPrintSetup.setOption('numCopies', printer.count);
            jsPrintSetup.setShowPrintProgress(false);
            jsPrintSetup.setSilentPrint(true);
            jsPrintSetup.print();
        } else {
            sw.swMsg.alert(langs('Ошибка'), 'Принтер с возможностью автоматической печати не обнаружен');
        }

        win.JsBarcodeInit();
    },
    // https://redmine.swan.perm.ru/issues/106759 ->
	printonZebra: function(labsample) {
        //Получаем объект для печати
        var win = this;
        var form = win.LabRequestEditForm.getForm();
        var PrehospDirect_Name = '';
        if (form.findField('LpuSection_id').getRawValue( ) != '') {
            PrehospDirect_Name = form.findField('LpuSection_id').getRawValue( );
        } else if (form.findField('Org_sid').getRawValue( ) != '') {
            PrehospDirect_Name = form.findField('Org_sid').getRawValue( );
        } else if (form.findField('PrehospDirect_id').getRawValue( ) != '') {
            PrehospDirect_Name = form.findField('PrehospDirect_id').getRawValue( );
        }
        var CurMedService_Name_base = getGlobalOptions().CurMedService_Name;
        if (CurMedService_Name_base != null && CurMedService_Name_base != '') {
            this.CurMedService_Name = getGlobalOptions().CurMedService_Name;
        }
        var obj = {
                barcode_size: Ext.globalOptions.lis.barcode_size,
                Service: Ext.globalOptions.lis.ZebraServicesName? this.CurMedService_Name:null,
                FIO: Ext.globalOptions.lis.ZebraFIO?this.Person_ShortFio:null,
                BarCode: win.findById(win.id + '_LabSampleBarCodeContInner_' + labsample.frameId).getEl().query('.LabSampleBarCode')[0].innerHTML,//labsample.EvnLabSample_BarCode,
                SampleNumber: Ext.globalOptions.lis.ZebraSampleNumber,
                PrehospDirect_Name: Ext.globalOptions.lis.ZebraDirect_Name?this.PrehospDirect_Name:null,
                barcode_format: Ext.globalOptions.lis.barcode_format
                };
        //Перевод на язык принтера зебра
        var sup = 0;//счетчик пустых полей сверху штрихкода
        var sdown = 0;//счетчик пустых полей снизу штрихкода
        var str = 'Y';//необходимо или нет ставить численное значение штрихкода
        var prnstr = '^XA^CWQ,E:ARI000.FNT^XZ'; //строка вывода на принтер (вначале идет инициализация шрифта)
        var htmlstr = ''; //Вывод наименования штрихкода в окно
        switch(obj.barcode_size) {
    	    //Для этикеток 30x50
    		case 3050:
    	    	sup = 0; sdown = 0;
    	    	prnstr = prnstr + '^XA';
    	    	if (obj.Service != null && obj.Service != '') {
    	    	        prnstr = prnstr + '^FO 0,29 ^AQN,10,10,^FD'+obj.Service+'^FS';
    	    	        htmlstr = htmlstr + 'Сервис: '+obj.Service;
    	    	}
    	    	else {
    	    	        sup++;
    	    	}
    	    	if (obj.FIO != null && obj.FIO != '' && sup == 0) {
    	    	        prnstr = prnstr + '^FO 0,51 ^AQN,10,10,^FD'+obj.FIO+'^FS';
    	    	        htmlstr = htmlstr + '; Ф.И.О.: ' + obj.FIO;
    	    	}
    	    	else if (obj.FIO != null && obj.FIO != '' && sup == 1){
    	    	        prnstr = prnstr + '^FO 0,29 ^AQN,10,10,^FD'+obj.FIO+'^FS';	
    	    	        htmlstr = htmlstr + '; Ф.И.О.: ' + obj.FIO;
    	    	} else {
    	    	        sup++;
    	    	}	
    	    	if (obj.PrehospDirect_Name != null && obj.PrehospDirect_Name != '') {
    	    	        prnstr = prnstr + '^FO 0,215 ^AQN,10,10,^FD'+obj.PrehospDirect_Name+'^FS'
    	    	        htmlstr = htmlstr + '; Кем направлен: ' + obj.PrehospDirect_Name + '<br/>';
    	    	} else {
    	    	        sdown++;
    	    	}
    	    	if (obj.SampleNumber) {
    	    	        str = 'Y';
    	    	} else {
    	    	        str = 'N'; sdown++;
    	    	}
    	    	if (obj.barcode_format == 39) {
    	    	        prnstr = prnstr + '^CVY^FO 10,'+22*(-sup+3)+' ^BY2,2 ^B3N,N,'+(110+21*(sup+sdown))+','+str+',N ^FD'+obj.BarCode+'^FS ^XZ';
    	    	}
    	    	else if (obj.barcode_format == 128) {
    	    	        prnstr = prnstr + '^CVY^FO 25,'+22*(-sup+3)+' ^BY2,2 ^BCN,'+(110+21*(sup+sdown))+','+str+',N,N ^FD'+obj.BarCode+'^FS ^XZ';
    	    	}
    	    	this.fprint(prnstr,htmlstr);
    		break;
		    
		    //Для этикеток 25x40
			case 2540:
		    	sup = 0; sdown = 0;
		    	prnstr = prnstr + '^XA';
		    	if (obj.Service != null && obj.Service != '') {
		    	        prnstr = prnstr + '^FO 20,25 ^AQN,8,8,^FD'+obj.Service+'^FS';
		    	        htmlstr = htmlstr + 'Сервис: '+obj.Service;
		    	}
		    	else {
		    	        sup++;
		    	}
		    	if (obj.FIO != null && obj.FIO != '' && sup == 0) {
		    	        prnstr = prnstr + '^FO 20,44 ^AQN,8,8,^FD'+obj.FIO+'^FS';
		    	        htmlstr = htmlstr + '; Ф.И.О.: ' + obj.FIO;
		    	}
		    	else if (obj.FIO != null && obj.FIO != '' && sup == 1){
		    	        prnstr = prnstr + '^FO 20,25 ^AQN,8,8,^FD'+obj.FIO+'^FS';	
		    	        htmlstr = htmlstr + '; Ф.И.О.: ' + obj.FIO;
		    	} else {
		    	        sup++;
		    	}	
		    	if (obj.PrehospDirect_Name != null && obj.PrehospDirect_Name != '') {
		    	        prnstr = prnstr + '^FO 20,185 ^AQN,8,8,^FD'+obj.PrehospDirect_Name+'^FS'
		    	        htmlstr = htmlstr + '; Кем направлен: ' + obj.PrehospDirect_Name + '<br/>';
		    	} else {
		    	        sdown++;
		    	}
		    	if (obj.SampleNumber) {
		    	        str = 'Y';
		    	} else {
		    	        str = 'N'; sdown++;
		    	}
		    	if (obj.barcode_format == 39) {
		    	        prnstr = prnstr + '^CVY^FO 57,'+19*(-sup+3)+' ^BY1,3.0 ^B3N,N,'+(95+18*(sup+sdown))+','+str+',N ^FD'+obj.BarCode+'^FS ^XZ';
		    	}
		    	else if (obj.barcode_format == 128) {
		    	        prnstr = prnstr + '^CVY^FO 90,'+19*(-sup+3)+' ^BY1,3.0 ^BCN,'+(95+18*(sup+sdown))+','+str+',N,N ^FD'+obj.BarCode+'^FS ^XZ';
		    	}
		    	
		    	this.fprint(prnstr,htmlstr);           
			break;
            
            //Для этикеток 20x40
            case 2040:
                sup = 0; sdown = 0;
                prnstr = prnstr + '^XA';
                if (obj.Service != null && obj.Service != '') {
                        prnstr = prnstr + '^FO 20,25 ^AQN,8,8,^FD'+obj.Service+'^FS';
                        htmlstr = htmlstr + 'Сервис: '+obj.Service;
                }
                else {
                        sup++;
                }

                if (obj.FIO != null && obj.FIO != '' && sup == 0) {
                        prnstr = prnstr + '^FO 20,45 ^AQN,8,8,^FD'+obj.FIO+'^FS';
                        htmlstr = htmlstr + '; Ф.И.О.: ' + obj.FIO;
                }

                else if (obj.FIO != null && obj.FIO != '' && sup == 1){
                        prnstr = prnstr + '^FO 20,25 ^AQN,8,8,^FD'+obj.FIO+'^FS';  
                        htmlstr = htmlstr + '; Ф.И.О.: ' + obj.FIO;
                } else {
                        sup++;
                }

                if (obj.PrehospDirect_Name != null && obj.PrehospDirect_Name != '') {
                        prnstr = prnstr + '^FO 20,145 ^AQN,8,8,^FD'+obj.PrehospDirect_Name+'^FS'
                        htmlstr = htmlstr + '; Кем направлен: ' + obj.PrehospDirect_Name + '<br/>';
                } else {
                        sdown++;
                }
                if (obj.SampleNumber) {
                        str = 'Y';
                } else {
                        str = 'N'; sdown++
                }
                if (obj.barcode_format == 39) {
                        prnstr = prnstr + '^CVY^FO 45,'+19*(-sup+3)+' ^BY1,3.0 ^B3N,N,'+(60+20*(sup+sdown))+','+str+',N ^FD'+obj.BarCode+'^FS ^XZ';
                }
                else if (obj.barcode_format == 128) {
                        prnstr = prnstr + '^CVY^FO 70,'+19*(-sup+3)+' ^BY1,3.0 ^BCN,'+(60+20*(sup+sdown))+','+str+',N,N ^FD '+obj.BarCode+'^FS ^XZ';
                }

            this.fprint(prnstr,htmlstr);
            break;

            //Для этикеток 20x30
        	case 2030:
            	sup = 0; sdown = 0;
            	prnstr = prnstr + '^XA';
            	if (obj.Service != null && obj.Service != '') {
            	        prnstr = prnstr + '^FO 20,25 ^AQN,8,8,^FD'+obj.Service+'^FS';
            	        htmlstr = htmlstr + 'Сервис: '+obj.Service;
            	}
            	else {
            	        sup++;
            	}
            	if (obj.FIO != null && obj.FIO != '' && sup == 0) {
            	        prnstr = prnstr + '^FO 20,40 ^AQN,8,8,^FD'+obj.FIO+'^FS';
            	        htmlstr = htmlstr + '; Ф.И.О.: ' + obj.FIO;
            	}
            	else if (obj.FIO != null && obj.FIO != '' && sup == 1){
            	        prnstr = prnstr + '^FO 20,25 ^AQN,8,8,^FD'+obj.FIO+'^FS';	
            	        htmlstr = htmlstr + '; Ф.И.О.: ' + obj.FIO;
            	} else {
            	        sup++;
            	}	
            	if (obj.PrehospDirect_Name != null && obj.PrehospDirect_Name != '') {
            	        prnstr = prnstr + '^FO 20,145 ^AQN,8,8,^FD'+obj.PrehospDirect_Name+'^FS'
            	        htmlstr = htmlstr + '; Кем направлен: ' + obj.PrehospDirect_Name + '<br/>';
            	} else {
            	        sdown++;
            	}
            	if (obj.SampleNumber) {
            	        str = 'Y';
            	} else {
            	        str = 'N'; sdown++
            	}
            	if (obj.barcode_format == 39) {
            	        prnstr = prnstr + '^CVY^FO 33,'+19*(-sup+3)+' ^BY1,2 ^B3N,N,'+(55+15*(sup+sdown))+','+str+',N ^FD'+obj.BarCode+'^FS ^XZ';
            	}
            	else if (obj.barcode_format == 128) {
            	        prnstr = prnstr + '^CVY^FO 36,'+19*(-sup+3)+' ^BY1,2 ^BCN,'+(55+15*(sup+sdown))+','+str+',N,N ^FD'+obj.BarCode+'^FS ^XZ';
            	}
            	this.fprint(prnstr,htmlstr);
        	break;

        	default:
        		Ext.MessageBox.getDialog().getEl().setStyle('z-index','80000'); //Т.к окно иногда бывает на заднем фоне
        	    Ext.Msg.alert('Ошибка печати', 'Для принтера Zebra необходимо указать в настройках печати штрихкода ширину и высоту наклейки, выбирая из трех значений: 20x30, 25x40 и 30х50');
        	break;
        }
	},
        //Вызов аплета для печати штрихкодов.
        //html - вывод того, что идет на печать.
        fprint: function(prnstr,htmlstr) {
            //console.log('htmlstr',htmlstr);
		if (navigator.javaEnabled() ) {
                    
                if (Ext.get('Zebra_applet2') != null) Ext.get('Zebra_applet2').remove();
                    //Окно апплета вставляется под стрелку изменения дат в виде 1 пикселя (Без окна аплет не работает.)                    
                    var applet = Ext.getCmp('LabRequestEditForm').getEl().parent().createChild({
                            name: 'PrintZebra',
                            tag: 'object',
                            archive:'/documents/Zebra/PrintZebra.jar',
                            codetype: 'application/java',
                            width: 1,
                            height: 1,
                            classid: "java:PrintZebra.class",
                            id: 'Zebra_applet2',
                            children: [
                                {tag: 'param', name: 'zebracode', value: prnstr},
                                {tag: 'param', name: 'PrinterName', value: 'ZDesigner GK420t'},
                                //{tag: 'param', name: 'boxbgcolor', value: '220,230,245'}
                            ]
                    });
		} else {
			setPromedInfo('Отсутствует java машина. Работа с картами будет недоступна.<br/>Для установки java машины зайдите на сайт <a href=http://java.com/ru>http://java.com/ru</a>', 'javamashine-info');
		}

        },          
	printBarCode: function(frameId) {
		var win = this;
		
		var EvnLabSample_id = win.getEvnLabSampleIdByFrameId(frameId);
		if (Ext.isEmpty(EvnLabSample_id)) {
			sw.swMsg.alert(langs('Ошибка'), langs('Для печати штрих-кода необходимо сначала взять пробу'));
			return false;
		}
		
		var Report_Params = '&s=' + EvnLabSample_id;

		if ( Ext.globalOptions.lis ) {
			var ZebraDateOfBirth = (Ext.globalOptions.lis.ZebraDateOfBirth) ? 1 : 0;
			var ZebraUsluga_Name = (Ext.globalOptions.lis.ZebraUsluga_Name) ? 1 : 0;
			var ZebraDirect_Name = (Ext.globalOptions.lis.ZebraDirect_Name) ? 1 : 0;
			var ZebraFIO = (Ext.globalOptions.lis.ZebraFIO) ? 1 : 0;
			Report_Params = Report_Params + '&paramPrintType=1';
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

		Report_Params = Report_Params + '&paramLpu=' + getGlobalOptions().lpu_id

		printBirt({
			'Report_FileName': (Ext.globalOptions.lis.use_postgresql_lis ? 'barcodesprint_resize_pg' : 'barcodesprint_resize') + '.rptdesign',
			'Report_Params': Report_Params,
			'Report_Format': 'pdf'
		});
	},
	printLabSampleResults: function(frameId) {
		// вызвать печать грида
		var win = this;
		win.findById(win.id + '_EvnUslugaDataGrid_' + frameId).printRecords();
	},
	getLabSamplePanel: function(labsample) {
		var win = this;
		var base_form = win.LabRequestEditForm.getForm();

		if (!labsample) {
			labsample = {
				EvnLabSample_id: null,
				EvnLabSample_ShortNum: null,
				EvnLabSample_BarCode: null,
				RefMaterial_Name: null,
				EvnLabSample_setDT: null,
				MedService_id: null
			};
		}

		var LabSampleSaved = false;
		if (labsample.EvnLabSample_id && labsample.EvnLabSample_id > 0) {
			LabSampleSaved = true;
		}

		var LabSampleTaken = false;
		if (labsample.EvnLabSample_setDT && !Ext.isEmpty(labsample.EvnLabSample_setDT)) {
			LabSampleTaken = true;
		}

		if (LabSampleTaken) {
			// показываем поле результат в заявке
			if (win.ARMType != 'pzm') {
				base_form.findField('UslugaExecutionType_id').showContainer();
			}
		}

		labsample.frameId = Ext.id();

		win.LabSamplesStore.loadData([labsample], true);

		var EvnUslugaDataGrid = new sw.Promed.ViewFrame({
			useEmptyRecord: false,
			showOnlyPrescr: (getRegionNick() == 'ufa')?false:true,
			selectionModel: 'multiselect',
			noSelectFirstRowOnFocus: true,
			showCountInTop: false,
			id: win.id + '_EvnUslugaDataGrid_' + labsample.frameId,
			autoLoadData: false,
			border: false,
			gridplugins: [Ext.ux.grid.plugins.GroupCheckboxSelection],
			defaults: {border: false},
			cls: 'EvnUslugaDataGridInRequest whitePanelGrid',
			autoExpandColumn: 'autoexpand',
			object: 'EvnLabSample',
			dataUrl: '/?c=EvnLabSample&m=getLabSampleResultGrid',
			region: 'center',
			height: 150,
			width: 'auto',
			saveAtOnce: false,
			toolbar: true,
			clicksToEdit: 1,
			onBeforeEdit: function(o) {
				if (o.field && o.field == 'UslugaTest_ResultValue' && o.record) {
					var combo = Ext.getCmp(win.id + '_ResultCombo' + labsample.frameId);
					combo.getStore().removeAll();
					combo.getStore().load({
						params: {
							UslugaTest_id: o.record.get('UslugaTest_id')
						}
					});
				}

				if (o.field && o.field == 'UslugaTest_ResultUnit' && o.record) {
					var combo = Ext.getCmp(win.id + '_ResultUnitCombo' + labsample.frameId);
					combo.getStore().removeAll();
					combo.getStore().load({
						params: {
							UslugaTest_id: o.record.get('UslugaTest_id')
						}
					});
				}
				
				if (o.field && o.field == 'RefValues_Name' && o.record) {
					var combo = Ext.getCmp(win.id + '_AnalyzerTestRefValuesCombo' + labsample.frameId);
					combo.getStore().removeAll();
					combo.getStore().load({
						params: {
							UslugaTest_id: o.record.get('UslugaTest_id')
						}
					});
				}

				var ed = o.grid.getColumnModel().getCellEditor(o.column, o.row);
				if (!ed) {
					o.cancel = true;
				}

				return o;
			},
			onAfterEdit: function(o) {
				o.grid.stopEditing(true);

				var rec = o.record;
				if (o.field && o.field == 'UslugaTest_ResultValue' && rec) {
					var combo = Ext.getCmp(win.id + '_ResultCombo' + rec.get('EvnLabSample_id'));
					rec.set('UslugaTest_ResultValue', o.rawvalue);

					var isSetValue = o.rawvalue !== '';
					var isAutoOk = isSetValue && rec.get('Analyzer_IsAutoOk') == 2;
					var isAutoGood =  isAutoOk && rec.get('Analyzer_IsAutoGood') == 2;
					var isQualitativeResult = !Ext.isEmpty( rec.get('UslugaTest_ResultQualitativeNorms') );
					var value = rec.get('UslugaTest_ResultValue');
					var upperValue = rec.get('UslugaTest_ResultUpper');
					var lowerValue = rec.get('UslugaTest_ResultLower');

					getFloatResult = function(string) {
						string = string.replace(',', '.');
						if(isNaN(string)) {
							return null;
						};
						return parseFloat(string);
					};

					isPathologicalQuantitativeTest = function(value, lowerValue, upperValue) {
						if(value == null) {
							return true;
						}
						lowerValue = !lowerValue ? -Infinity : lowerValue;
						upperValue = !upperValue ? Infinity : upperValue;
						return value < lowerValue || upperValue < value;
					};

					if( isAutoGood ) {
						if (isQualitativeResult) {
							var qualitativeNorms = jsonDecode(rec.get('UslugaTest_ResultQualitativeNorms'));
						} else {
							value = getFloatResult(value);
							upperValue = getFloatResult(upperValue);
							lowerValue = getFloatResult(lowerValue);
						}
					}


					switch (true) {
						// Автоодобрение
						case isAutoOk && !isAutoGood:
							rec.set('UslugaTest_Status', langs('Одобрен'));
							rec.set('UslugaTest_ResultApproved', 2);
							rec.set('UslugaTest_setDT', new Date());
							break;

						// Автоодобрение без патологии для количественных тестов
						case isAutoOk && !isQualitativeResult:
							var isPathologic = isPathologicalQuantitativeTest(value, lowerValue, upperValue);
							rec.set('UslugaTest_Status', !isPathologic ? langs('Одобрен') : langs('Выполнен'));
							rec.set('UslugaTest_ResultApproved', !isPathologic ? 2 : 1);
							rec.set('UslugaTest_setDT', new Date());
							break;

						// Автоодобрение без патологии для качественных тестов
						case isAutoOk && isQualitativeResult:
							var qualitiveNorms = jsonDecode(rec.get('UslugaTest_ResultQualitativeNorms'));
							var isPathologic = !value.inlist(qualitiveNorms);
							rec.set('UslugaTest_Status', !isPathologic ? langs('Одобрен') : langs('Выполнен'));
							rec.set('UslugaTest_ResultApproved', !isPathologic ? 2 : 1);
							rec.set('UslugaTest_setDT', new Date());
							break;

						// Поведение без автоодобрения
						default:
							rec.set('UslugaTest_Status', isSetValue ? langs('Выполнен') :  langs('Назначен'));
							rec.set('UslugaTest_ResultApproved', 1);
							!isSetValue || rec.set('UslugaTest_setDT', new Date());
							break;
					}

					this.setActionDisabled('action_cancel', o.record.get('UslugaTest_Status') != langs('Назначен'));
					this.setActionDisabled('action_transfer', o.record.get('UslugaTest_Status') == langs('Выполнен'));
					this.onRowSelectionChange();
					
					var params = {};
					params.UslugaTest_id = o.record.get('UslugaTest_id');
					params.UslugaTest_ResultValue = o.rawvalue;
					params.updateType = 'value';
					win.updateEvnLabSample(params, o);
				}
				
				if (o.field && o.field == 'UslugaTest_ResultUnit' && o.record) {
					var combo = Ext.getCmp(win.id + '_ResultUnitCombo' + labsample.frameId);
					o.record.set('UslugaTest_ResultUnit', o.rawvalue);
					o.record.set('Unit_id', combo.getValue());
					
					win.coeffRefValues(o.record, combo.getFieldValue('Unit_Coeff'));
								
					var refvalues = {};
					refvalues.UslugaTest_ResultQualitativeNorms = o.record.get('UslugaTest_ResultQualitativeNorms');
					refvalues.UslugaTest_ResultNorm = o.record.get('UslugaTest_ResultNorm');
					refvalues.UslugaTest_ResultCrit = o.record.get('UslugaTest_ResultCrit');
					refvalues.UslugaTest_ResultLower = o.record.get('UslugaTest_ResultLower');
					refvalues.UslugaTest_ResultUpper = o.record.get('UslugaTest_ResultUpper');
					refvalues.UslugaTest_ResultLowerCrit = o.record.get('UslugaTest_ResultLowerCrit');
					refvalues.UslugaTest_ResultUpperCrit = o.record.get('UslugaTest_ResultUpperCrit');
					refvalues.UslugaTest_ResultUnit = o.record.get('UslugaTest_ResultUnit');
					refvalues.UslugaTest_Comment = o.record.get('UslugaTest_Comment');
					refvalues.RefValues_id = o.record.get('RefValues_id');
					refvalues.Unit_id = o.record.get('Unit_id');
					
					var params = {};
					params.UslugaTest_id = o.record.get('UslugaTest_id');
					params.UslugaTest_RefValues = Ext.util.JSON.encode(refvalues);
					params.UslugaTest_ResultValue = o.record.get('UslugaTest_ResultValue');
					params.updateType = 'value';
					win.updateEvnLabSample(params, o);
				}
				
				if (o.field && o.field == 'RefValues_Name' && o.record) {
					var combo = Ext.getCmp(win.id + '_AnalyzerTestRefValuesCombo' + labsample.frameId);
					win.setRefValues(o.record, {
						UslugaTest_ResultQualitativeNorms: combo.getFieldValue('UslugaTest_ResultQualitativeNorms'),
						UslugaTest_ResultUnit: combo.getFieldValue('UslugaTest_ResultUnit'),
						UslugaTest_Comment: combo.getFieldValue('UslugaTest_Comment'),
						RefValues_id: combo.getFieldValue('RefValues_id'),
						Unit_id: combo.getFieldValue('Unit_id'),
						RefValues_Name: combo.getFieldValue('RefValues_Name'),
						UslugaTest_ResultLower: combo.getFieldValue('UslugaTest_ResultLower'),
						UslugaTest_ResultUpper: combo.getFieldValue('UslugaTest_ResultUpper'),
						UslugaTest_ResultLowerCrit: combo.getFieldValue('UslugaTest_ResultLowerCrit'),
						UslugaTest_ResultUpperCrit: combo.getFieldValue('UslugaTest_ResultUpperCrit')
					});
					
					var refvalues = {};
					refvalues.UslugaTest_ResultQualitativeNorms = o.record.get('UslugaTest_ResultQualitativeNorms');
					refvalues.UslugaTest_ResultNorm = o.record.get('UslugaTest_ResultNorm');
					refvalues.UslugaTest_ResultCrit = o.record.get('UslugaTest_ResultCrit');
					refvalues.UslugaTest_ResultLower = o.record.get('UslugaTest_ResultLower');
					refvalues.UslugaTest_ResultUpper = o.record.get('UslugaTest_ResultUpper');
					refvalues.UslugaTest_ResultLowerCrit = o.record.get('UslugaTest_ResultLowerCrit');
					refvalues.UslugaTest_ResultUpperCrit = o.record.get('UslugaTest_ResultUpperCrit');
					refvalues.UslugaTest_ResultUnit = o.record.get('UslugaTest_ResultUnit');
					refvalues.UslugaTest_Comment = o.record.get('UslugaTest_Comment');
					refvalues.RefValues_id = o.record.get('RefValues_id');
					refvalues.Unit_id = o.record.get('Unit_id');
					
					var params = {};
					params.UslugaTest_id = o.record.get('UslugaTest_id');
					params.UslugaTest_RefValues = Ext.util.JSON.encode(refvalues);
					win.updateEvnLabSample(params, o);
				}
				
				if (o.field && o.field == 'UslugaTest_Comment' && o.record) {
					var combo = Ext.getCmp(win.id + '_AnalyzerTestRefValuesCombo' + labsample.frameId);
					o.record.set('UslugaTest_Comment', o.rawvalue);
					
					var params = {};
					params.UslugaTest_id = o.record.get('UslugaTest_id');
					params.UslugaTest_Comment = o.rawvalue;
					params.updateType = 'comment';
					win.updateEvnLabSample(params, o);
				}
			},
			grouping: true,
			interceptMouse : function(e){
				var editLink = e.getTarget('.editResearchLink', this.mainBody);
				var commentLink = e.getTarget('.commentResearchLink', this.mainBody);
				var hd = e.getTarget('.x-grid-group-hd', this.mainBody);
				if(hd && !editLink && !commentLink){
					e.stopEvent();
					this.toggleGroup(hd.parentNode);
				}
			},
			groupTextTpl: '<b>{[ values.rs[0].data["ResearchName"] ]}</b>&nbsp;&nbsp;&nbsp;<a class="editResearchLink" href="javascript://" onClick="Ext.getCmp(\''+win.id+'\').openResearchEditWindow(this, {[ values.rs[0].data["UslugaTest_pid"] ]});" style="color:#000;font-weight:normal;">Редактировать</a> &nbsp; ' +
				'<span id="'+win.id + '_addEvnXml_' + labsample.frameId+'" style="'+(!LabSampleTaken ? 'display: none;' : '')+'"><a class="editResearchLink" href="javascript://" onClick="Ext.getCmp(\''+win.id+'\').openEvnXmlEditWindow(this, {[ values.rs[0].data["UslugaTest_pid"] ]}, null);" style="color:#000;font-weight:normal;">Прикрепить шаблон</a> &nbsp; </span>' +
				'<span id="'+win.id + '_addEvnXml_' + labsample.frameId+'"><a class="editResearchLink" href="javascript://" onClick="Ext.getCmp(\''+win.id+'\').EvnLabSample_addFile({[ values.rs[0].data["UslugaTest_pid"] ]});" style="color:#000;font-weight:normal;">Прикрепить файл</a> &nbsp; </span>' +
				'<img class="commentResearchLink" id="'+win.id+'EvnLabSample_Comment_icon_{[ values.rs[0].data["UslugaTest_pid"] ]}" onClick="Ext.getCmp(\''+win.id+'\').editEvnLabSampleComment(this, {[ values.rs[0].data["UslugaTest_pid"] ]}, 1);" title="Добавить комментарий к исследованию" src="img/icons/comment_icon.png">' +
				'<p class="commentResearchLink" id="'+win.id+'EvnLabSample_Comment_block_{[ values.rs[0].data["UslugaTest_pid"] ]}" style="margin: 7px 0 5px -14px; color: black; font-weight:normal;" onClick="Ext.getCmp(\''+win.id+'\').editEvnLabSampleComment(this, {[ values.rs[0].data["UslugaTest_pid"] ]}, 2);"><img style="margin-right: 7px;" src="img/icons/comment_icon.png"><span style="text-overflow: ellipsis; white-space: nowrap; width: 870px; overflow: hidden; display: inline-block;" id="'+win.id+'EvnLabSample_Comment_text_{[ values.rs[0].data["UslugaTest_pid"] ]}"></span></p>' +
				'<p class="commentResearchLink" id="'+win.id+'EvnLabSample_EvnXml_block_{[ values.rs[0].data["UslugaTest_pid"] ]}" style="margin: 5px 0 5px -14px; line-height: 1.5em; color: black; font-weight:normal;"><span id="'+win.id+'EvnLabSample_EvnXml_text_{[ values.rs[0].data["UslugaTest_pid"] ]}">   </span></p>'+
				'<p class="commentResearchLink" id="'+win.id+'EvnLabSample_File_EvnXml_block_{[ values.rs[0].data["UslugaTest_pid"] ]}" style="margin: 5px 0 5px -14px; line-height: 1.5em; color: black; font-weight:normal;"><span id="'+win.id+'EvnLabSample_File_text_{[ values.rs[0].data["UslugaTest_pid"] ]}">   </span></p>',
			groupingView: {showGroupName: false, showGroupsText: true},
			stringfields:
			[
				{name: 'UslugaTest_id', type: 'int', header: 'UslugaTest_id', key: true, hidden: true},
				{name: 'EvnLabSample_id', type: 'int', hidden: true},
				{name: 'UslugaTest_pid', type: 'int', group: true, sort: true, direction: 'ASC', header: langs('Группа'), width: 200},
				{name: 'EvnUslugaPar_pComment', type: 'string', hidden: true},
				{name: 'ResearchName', type: 'string', hidden: true},
				{name: 'UslugaComplex_id', type:'int', header: 'UslugaComplex_id', hidden: true},
				{name: 'UslugaComplex_Code', type:'string', header: langs('Код'), width: 80},
				{name: 'UslugaComplex_Name', type: 'string', header: langs('Тест'), id: 'autoexpand'},
				{name: 'UslugaTest_ResultValue', editor: new sw.Promed.SwQualitativeTestAnswerAnalyzerTestCombo({
					id: win.id + '_ResultCombo' + labsample.frameId,
					editable: true,
					forceSelection: false,
					allowTextInput: true,
					useRawValueForGrid: true,
					listeners: {
						'select': function(combo, record) {
							combo.setValue(record.get('QualitativeTestAnswerAnalyzerTest_id'));
							combo.fireEvent('blur', combo);
						},
						'blur': function(combo) {
							EvnUslugaDataGrid.getGrid().stopEditing();
						}
					},
					allowBlank: true,
					listWidth: 300
				}), header: langs('Результат'), renderer: function(v, p, row){
					var type = null;
					var addit = "";
					var clr = "#000";
					var UslugaTest_ResultLower = row.get('UslugaTest_ResultLower');
					var UslugaTest_ResultUpper = row.get('UslugaTest_ResultUpper');
					var UslugaTest_ResultLowerCrit = row.get('UslugaTest_ResultLowerCrit');
					var UslugaTest_ResultUpperCrit = row.get('UslugaTest_ResultUpperCrit');
					var UslugaTest_ResultQualitativeNorms = row.get('UslugaTest_ResultQualitativeNorms');
					var UslugaTest_ResultValue = row.get('UslugaTest_ResultValue');

					// https://redmine.swan.perm.ru/issues/41725
					// Меняем запятую на точку, ибо parseFloat('4,7') = 4, а не 4.7
					if ( !Ext.isEmpty(UslugaTest_ResultLower) ) {
						UslugaTest_ResultLower = UslugaTest_ResultLower.toString().replace(',', '.');
					}

					if ( !Ext.isEmpty(UslugaTest_ResultUpper) ) {
						UslugaTest_ResultUpper = UslugaTest_ResultUpper.toString().replace(',', '.');
					}

					if ( !Ext.isEmpty(UslugaTest_ResultLowerCrit) ) {
						UslugaTest_ResultLowerCrit = UslugaTest_ResultLowerCrit.toString().replace(',', '.');
					}

					if ( !Ext.isEmpty(UslugaTest_ResultUpperCrit) ) {
						UslugaTest_ResultUpperCrit = UslugaTest_ResultUpperCrit.toString().replace(',', '.');
					}

					if ( !Ext.isEmpty(UslugaTest_ResultValue) ) {
						UslugaTest_ResultValue = UslugaTest_ResultValue.toString().replace(',', '.');
					}

					if (!Ext.isEmpty(UslugaTest_ResultValue)) {
						if (!Ext.isEmpty(UslugaTest_ResultQualitativeNorms)) {
							var resp = Ext.util.JSON.decode(UslugaTest_ResultQualitativeNorms);
							if (!UslugaTest_ResultValue.inlist(resp)) {
								clr = "#F00";
							}
						} else if (!isNaN(parseFloat(UslugaTest_ResultValue))) {
							UslugaTest_ResultValue = parseFloat(UslugaTest_ResultValue);
							UslugaTest_ResultLowerCrit = parseFloat(UslugaTest_ResultLowerCrit);
							UslugaTest_ResultUpperCrit = parseFloat(UslugaTest_ResultUpperCrit);
							UslugaTest_ResultLower = parseFloat(UslugaTest_ResultLower);
							UslugaTest_ResultUpper = parseFloat(UslugaTest_ResultUpper);
						
							// https://redmine.swan.perm.ru/issues/41725
							// Поменял на строгие неравенства, т.к. границы диапазона являются допустимыми значениями
							if (!Ext.isEmpty(UslugaTest_ResultLowerCrit) && UslugaTest_ResultValue < UslugaTest_ResultLowerCrit) {
								clr = "#F00";
								addit = "&#x25BC;&#x25BC;";
							} else if (!Ext.isEmpty(UslugaTest_ResultUpperCrit) && UslugaTest_ResultValue > UslugaTest_ResultUpperCrit) {
								clr = "#F00";
								addit = "&#x25B2;&#x25B2;";
							} else if (!Ext.isEmpty(UslugaTest_ResultLower) && UslugaTest_ResultValue < UslugaTest_ResultLower) {
								clr = "#F00";
								addit = "&#x25BC;";
							} else if (!Ext.isEmpty(UslugaTest_ResultUpper) && UslugaTest_ResultValue > UslugaTest_ResultUpper) {
								clr = "#F00";
								addit = "&#x25B2;";
							}
						}
					}
					
					if (v == null) {
						v = "";
					}
					
					if (Ext.isEmpty(v)) {
						v = "&nbsp;";
					}

					return "<span style='color:"+clr+"; float: left;'>"+v+"</span>" + "<span style='color:#F00; float: right;'>" + addit + "</span>";
				}, width: 80},
				{name: 'UslugaTest_ResultUnit', editor: new sw.Promed.SwTestUnitCombo({
					id: win.id + '_ResultUnitCombo' + labsample.frameId,
					listeners: {
						'select': function(combo, record) {
							combo.setValue(record.get('Unit_id'));
							combo.fireEvent('blur', combo);
						},
						'blur': function(combo) {
							EvnUslugaDataGrid.getGrid().stopEditing();
						}
					},
					allowBlank: true,
					listWidth: 300
				}), renderer: function(v, p, row) {
					if (!Ext.isEmpty(v)) {
						v = "<span class='canbecombobox'>" + v + "</span>";
					}
					return v;
				}, header: langs('Ед. изм.'), width: 80},
				{name: 'RefValues_id', type:'int', hidden:true},
				{name: 'Unit_id', type:'int', hidden:true},
				{name: 'UslugaTest_ResultNorm', type: 'string', header: langs('Реф. зн.'), width: 80},
				{name: 'RefValues_Name', editor: new sw.Promed.SwAnalyzerTestRefValuesCombo({
					id: win.id + '_AnalyzerTestRefValuesCombo' + labsample.frameId,
					listeners: {
						'select': function(combo, record) {
							combo.setValue(record.get('AnalyzerTestRefValues_id'));
							combo.fireEvent('blur', combo);
						},
						'blur': function(combo) {
							EvnUslugaDataGrid.getGrid().stopEditing();
						}
					},
					allowBlank: true,
					listWidth: 300
				}), renderer: function(v, p, row) {
					if (!Ext.isEmpty(v)) {
						v = "<span class='canbecombobox'>" + v + "</span>";
					}
					return v;
				}, header: langs('<b>Наименование реф. зн.</b>'), width: 110},
				{name: 'UslugaTest_ResultCrit', type: 'string', hidden: true, header: langs('Критич. диапазон'), width: 160},
				{name: 'UslugaTest_ResultLower', type: 'string', hidden: true},
				{name: 'UslugaTest_ResultUpper', type: 'string', hidden: true},
				{name: 'UslugaTest_ResultLowerCrit', type: 'string', hidden: true},
				{name: 'UslugaTest_ResultUpperCrit', type: 'string', hidden: true},
				{name: 'UslugaTest_ResultQualitativeNorms', type: 'string', hidden: true},
				{name: 'UslugaTest_Comment', header: langs('Комментарий'), width: 80, editor: new Ext.form.TextField({}), renderer: function(v,m,rec){
					if (v != null) {
						if (m)
							m.attr = 'data-qtip="'+rec.get('UslugaTest_Comment')+'"';
						return v;										
					}
				}},
				{name: 'UslugaTest_setDT', type: 'timedate', hidden: true, header: langs('Время выполнения'), width: 80},
				{name: 'UslugaTest_Status', header: langs('Статус'), width: 80, renderer: function(v, p, row) {
					if (v == langs('Не назначен')) {
						v = "<span class='notprescr'>" + v + "</span>";
					}
					return v;
				}},
				{name: 'UslugaTest_CheckDT', header: langs('Время одобрения'), width: 110, renderer: function(v, p, row) {
					if (v != null) {
						return v;
					}
				}},
				{name: 'UslugaTest_ResultApproved', hidden: true, header:langs('Признак одобрения')},
				{name: 'Analyzer_IsAutoOk', hidden: true, header:langs('Автоодобрение')},
				{name: 'Analyzer_IsAutoGood', hidden: true}
			],
			actions:
			[
				{name:'action_add', disabled: true, hidden: true},
				{name:'action_edit', disabled: true, hidden: true},
				{name:'action_view', disabled: true, hidden: true},
				{name:'action_delete', disabled: true, hidden: true},
				{name:'action_print', disabled: true, hidden: true},
				{name:'action_refresh', disabled: true, hidden: true},
				{name:'action_save', url: '/?c=EvnLabSample&m=updateResult', hidden: true }
			],
			onLoadData: function() {
				this.onRowSelectionChange();
				var store = this.getGrid().getStore();
				var UslugaTest_pid;
				
				store.each(function(rec) {
					if (!Ext.isEmpty(rec.get('UslugaTest_ResultQualitativeNorms'))) {
						var resp = Ext.util.JSON.decode(rec.get('UslugaTest_ResultQualitativeNorms'));
						var UslugaTest_ResultNorm = '';
						for (var k1 in resp) {
							if (typeof resp[k1] != 'function') {
								if (UslugaTest_ResultNorm.length > 0) {
									UslugaTest_ResultNorm = UslugaTest_ResultNorm + ', ';
								}
								
								UslugaTest_ResultNorm = UslugaTest_ResultNorm + resp[k1];
							}
						}
						rec.set('UslugaTest_ResultNorm',UslugaTest_ResultNorm);
						rec.set('UslugaTest_ResultCrit','');
						rec.set('UslugaTest_ResultLower','');
						rec.set('UslugaTest_ResultUpper','');
						rec.set('UslugaTest_ResultLowerCrit','');
						rec.set('UslugaTest_ResultUpperCrit','');
						rec.commit();
					}
					
					setTimeout(function() {
						if (!Ext.isEmpty(rec.get('EvnUslugaPar_pComment')) && Ext.get(win.id + 'EvnLabSample_Comment_text_'+rec.get('UslugaTest_pid'))) {
							Ext.get(win.id + 'EvnLabSample_Comment_text_'+rec.get('UslugaTest_pid')).update(rec.get('EvnUslugaPar_pComment'));
							Ext.get(win.id + 'EvnLabSample_Comment_text_'+rec.get('UslugaTest_pid')).setAttribute('data-qtip', rec.get('EvnUslugaPar_pComment'));
						}
						win.setEvnLabSampleCommentMode(rec.get('UslugaTest_pid'));
					}, 100);
						
					if (Ext.get(win.id + 'EvnLabSample_EvnXml_text_'+rec.get('UslugaTest_pid')) && UslugaTest_pid != rec.get('UslugaTest_pid')) {
						win.loadLabStydyResultDoc(rec.get('UslugaTest_pid'));
					}

					if (Ext.get(win.id + 'EvnLabSample_File_text_'+rec.get('UslugaTest_pid')) && UslugaTest_pid != rec.get('UslugaTest_pid')) {
						win.loadLabFileList(rec.get('UslugaTest_pid'));
					}

					UslugaTest_pid = rec.get('UslugaTest_pid');
				});
				
				if(win.findById(win.id + '_LabSampleNum_' + labsample.frameId).isVisible()) {
					setTimeout(function() {
						Ext.get(win.id + '_addEvnXml_' + labsample.frameId).show();
					}, 50);
				}

				win.filterPrescrTests(EvnUslugaDataGrid);
				if ( getRegionNick() == 'ufa' ) {
					win.setPersonDetailVisible();
				}
			},
			onRowSelectionChange: function() {
				// кнопка одобрить доступна если есть хоть одна в статусе Выполнен
				var approveFlag = true;
				// кнопка снять одобрение доступна если есть хоть одна в статусе Одобрен
				var unapproveFlag = true;
				// кнопка отменить недоступна если есть хоть одна в статусе не Назначен
				var cancelFlag = false;
				// кнопка назначить недоступна если есть хоть одна в статусе не Не назначен
				var prescrFlag = false;
				// кнопка перенести тест недоступна если есть хоть одна в статусе Выполнен или Одобрен
				var transferFlag = false;
				
				var records = this.getGrid().getSelectionModel().getSelections();
				for (var i = 0; i < records.length; i++) {
					if (records[i].get('UslugaTest_Status') == langs('Выполнен')) {
						approveFlag  = false;
					}
					if (records[i].get('UslugaTest_Status') == langs('Одобрен')) {
						unapproveFlag  = false;
					}
					if (records[i].get('UslugaTest_Status') != langs('Назначен')) {
						cancelFlag  = true;
						win.disableEvnDirectionForm();
					}
					if (records[i].get('UslugaTest_Status') != langs('Не назначен')) {
						prescrFlag  = true;
					}
					if (records[i].get('UslugaTest_Status') == langs('Выполнен') || records[i].get('UslugaTest_Status') == langs('Одобрен')) {
						transferFlag  = true;
					}
				}
				
				if (win.action == 'view') {
					approveFlag = true;
					unapproveFlag = true;
					cancelFlag = true;
					transferFlag = true;
					prescrFlag = true;
				}
				if (win.ARMType == 'microbiolab') {
					cancelFlag = true;
					prescrFlag = true;
				}

				this.setActionDisabled('action_approveone', approveFlag);
				this.setActionDisabled('action_unapproveone', unapproveFlag);
				this.setActionDisabled('action_prescr', prescrFlag);
				this.setActionDisabled('action_cancel', cancelFlag);
				this.setActionDisabled('action_transfer', transferFlag);
			},
			onRowSelect: function(sm,rowIdx,record)
			{
				this.onRowSelectionChange();
			},
			onRowDeSelect: function(sm,rowIdx,record) {
				this.onRowSelectionChange();
			},
			onRenderGrid: function() {
				if (!EvnUslugaDataGrid.getAction('action_transfer') ) {
					EvnUslugaDataGrid.addActions({
						id: Ext.id(), // уникальный ид, т.к. меню привязывается к нему, а на форме их может быть несколько.
						name:'action_transfer',
						cls: 'x-btn-with-menu id_action_transfer',
						iconCls: 'archive16',
						handler: function() {
							var transferMenu = new Ext.menu.Menu({
								width: 300
							});
							transferMenu.add(new Ext.menu.Item({
								id: -1,
								text: langs('Новая проба'),
								disabled: false,
								handler: function(item) {
									win.transferTest(EvnUslugaDataGrid, labsample);
								}
							}));

							var EvnLabSample_id = win.getEvnLabSampleIdByFrameId(labsample.frameId);

							win.LabSamplesStore.each(function(rec) {
								if (rec.get('EvnLabSample_id') && rec.get('EvnLabSample_id') != EvnLabSample_id && rec.get('EvnLabSample_id') > 0 && !Ext.isEmpty(rec.get('EvnLabSample_setDT'))) {
									transferMenu.add(new Ext.menu.Item({
										id: rec.get('EvnLabSample_id'),
										text: rec.get('EvnLabSample_ShortNum'),
										disabled: false,
										handler: function (item) {
											win.transferTest(EvnUslugaDataGrid, labsample, rec.get('frameId'));
										}
									}));
								}
							});
							transferMenu.show(this.getEl(),'tl-bl?');
						},
						text: langs('Перенести'),
						tooltip: langs('Перенести тест')
					});

					EvnUslugaDataGrid.getGrid().getTopToolbar().add({
						boxLabel: 'Показывать только назначенные',
						checked: (getRegionNick() == 'ufa')?false:true,
						listeners: {
							'check': function(field, value) {
								if (value) {
									EvnUslugaDataGrid.showOnlyPrescr = true;
								} else {
									EvnUslugaDataGrid.showOnlyPrescr = false;
								}

								win.filterPrescrTests(EvnUslugaDataGrid);
							}
						},
						xtype: 'checkbox'
					});
				}

				if (!EvnUslugaDataGrid.getAction('action_cancel') ) {
					EvnUslugaDataGrid.addActions({
						name:'action_cancel',
						cls: 'newInGridButton',
						iconCls: 'archive16',
						text: langs('Отменить'),
						tooltip: langs('Отменить тест'),
						handler: function() {
							win.cancelTest(EvnUslugaDataGrid, labsample);
						}
					});
				}
				
				if (!EvnUslugaDataGrid.getAction('action_prescr') ) {
					EvnUslugaDataGrid.addActions({
						name:'action_prescr',						
						iconCls: 'archive16',
						cls: 'newInGridButton',
						text: langs('Назначить'),
						tooltip: langs('Назначить тест'),
						handler: function() {
							win.prescrTest(EvnUslugaDataGrid, labsample);
						}
					});
				}
				
				if (!EvnUslugaDataGrid.getAction('action_unapproveone') ) {
					EvnUslugaDataGrid.addActions({
						name:'action_unapproveone',
						iconCls: 'archive16',
						cls: 'newInGridButton',
						text: langs('Снять одобрение'),
						tooltip: langs('Снять одобрение результата'),
						handler: function() {
							if ( !win.approveIsAllowed() ) return;
							
							var params = {};
							params.EvnLabSample_id = win.getEvnLabSampleIdByFrameId(labsample.frameId);;
							
							var records = EvnUslugaDataGrid.getGrid().getSelectionModel().getSelections();
							var UslugaTest_ids = [];
							for (var i = 0; i < records.length; i++) {
								if (!Ext.isEmpty(records[i].get('UslugaTest_id')) && !Ext.isEmpty(records[i].get('UslugaTest_ResultValue'))) {
									UslugaTest_ids = UslugaTest_ids.concat(records[i].get('UslugaTest_id').toString());
								}
							}
							
							if (!Ext.isEmpty(UslugaTest_ids) && UslugaTest_ids.length > 0) {
								params.UslugaTest_ids = Ext.util.JSON.encode(UslugaTest_ids);
								
								win.showLoadMask(langs('Снятие одобрения результатов'));
								Ext.Ajax.request({
									url: '/?c=EvnLabSample&m=unapproveResults',
									params: params,
									failure: function(response, options) {
										win.hideLoadMask();
									},
									success: function(response, action) {
										win.hideLoadMask();
										var eu_params = {
											EvnDirection_id: base_form.findField('EvnDirection_id').getValue(),
											EvnLabSample_id: params.EvnLabSample_id
										};
										
										EvnUslugaDataGrid.loadData({
											params: eu_params,
											globalFilters: eu_params,
											noFocusOnLoad: true
										});
										// Меняем значение поля UslugaExecutionType_id
										if (response && response.responseText) {
											var result = Ext.util.JSON.decode(response.responseText);
											if (result) {
												win.LabRequestEditForm.getForm().findField('UslugaExecutionType_id').setValue(result.UslugaExecutionType_id);
											}
										}
									}
								});
							}
						}
					});
				}
				
				if (!EvnUslugaDataGrid.getAction('action_approveone') ) {
					EvnUslugaDataGrid.addActions({
						name:'action_approveone',
						iconCls: 'archive16',
						text: langs('Одобрить'),
						tooltip: langs('Одобрить результат'),
						handler: function() {
							if ( !win.approveIsAllowed() ) return;
							
							var params = {};
							params.EvnLabSample_id = win.getEvnLabSampleIdByFrameId(labsample.frameId);
							
							var records = EvnUslugaDataGrid.getGrid().getSelectionModel().getSelections();
							var UslugaTest_ids = [];
							for (var i = 0; i < records.length; i++) {
								if (!Ext.isEmpty(records[i].get('UslugaTest_id')) && !Ext.isEmpty(records[i].get('UslugaTest_ResultValue'))) {
									UslugaTest_ids = UslugaTest_ids.concat(records[i].get('UslugaTest_id').toString());
								}
							}
							
							if (!Ext.isEmpty(UslugaTest_ids) && UslugaTest_ids.length > 0) {
								params.UslugaTest_ids = Ext.util.JSON.encode(UslugaTest_ids);
								
								win.showLoadMask(langs('Одобрение результатов'));
								Ext.Ajax.request({
									url: '/?c=EvnLabSample&m=approveResults',
									params: params,
									failure: function(response, options) {
										win.hideLoadMask();
									},
									success: function(response, action) {
										win.hideLoadMask();
										var eu_params = {
											EvnDirection_id: base_form.findField('EvnDirection_id').getValue(),
											EvnLabSample_id: params.EvnLabSample_id
										};
										
										EvnUslugaDataGrid.loadData({
											params: eu_params,
											globalFilters: eu_params,
											noFocusOnLoad: true
										});
										// Меняем значение поля UslugaExecutionType_id
										if (response && response.responseText) {
											var result = Ext.util.JSON.decode(response.responseText);
											if (result && result.UslugaExecutionType_id) {
												win.LabRequestEditForm.getForm().findField('UslugaExecutionType_id').setValue(result.UslugaExecutionType_id);
											}
										}
									}
								});
							}
						}
					});
				}
				
				EvnUslugaDataGrid.setActionHidden('action_approveone', win.ARMType == 'pzm');
				EvnUslugaDataGrid.setActionHidden('action_unapproveone', win.ARMType == 'pzm');
				EvnUslugaDataGrid.setColumnHidden('UslugaTest_CheckDT', true);
			}
		});
		
		EvnUslugaDataGrid.getGrid().getView().getRowClass = function (row, index) {
			var cls = '';
			if (row.get('UslugaTest_Status') == langs('Не назначен')) {
				cls = cls+'x-grid-rowgray ';
			}	
			return cls;
		};

		var params = {
			EvnDirection_id: base_form.findField('EvnDirection_id').getValue(),
			EvnLabSample_id: labsample.EvnLabSample_id
		};

		// если создана проба, грузим грид
		if (LabSampleSaved) {
			EvnUslugaDataGrid.loadData({
				params: params,
				globalFilters: params,
				noFocusOnLoad: true
			});
		} else {
			EvnUslugaDataGrid.hide();
		}
		
		//Выключаем форму редактирования направления если заявка не новая(т.е. есть проба)
		EvnUslugaDataGrid.getGrid().getStore().on('load', function (store, records) {
			for (var i = 0; i < records.length; i++) {
				if (!Ext.isEmpty(records[i].get('UslugaTest_id'))) {
					win.disableEvnDirectionForm();
				}
				if (records[i].get('UslugaTest_Status') === langs('Выполнен') 
					|| records[i].get('UslugaTest_Status') === langs('Одобрен')) {
					win.disableEDOtherFields();
				}
			}
		});
		
		EvnUslugaDataGrid.getGrid().getColumnModel().isCellEditable = function(colIndex, rowIndex) {
			var grid = EvnUslugaDataGrid.getGrid();
			var store = grid.getStore();
			
			if (win.ARMType.inlist(['pzm'])) {
				return false;
			}
			
			if (Ext.isEmpty(store.baseParams.EvnLabSample_id)) {
				return false;
			}
			
			var record = store.getAt(rowIndex);
			if (!record || Ext.isEmpty(record.get('UslugaTest_id'))) {
				return false;
			}
			
			return true;
		};
		
		return new sw.Promed.Panel({
			border: false,
			frameId: labsample.frameId,
			layout: 'form',
			labelWidth: 130,
			bodyStyle: 'background:#DFE8F6;',
			defaults: {bodyStyle:'background:#DFE8F6;', border: false},			
			items: [{
				layout: 'column',
				cls: 'swPanelMenu',
				defaults: {bodyStyle:'background:#DFE8F6;', border: false},
				items: [{
					layout: 'form',
				    id: win.id + '_LabSampleNum_' + labsample.frameId,
                    hidden: !LabSampleTaken,
					width: 115,
					items: [{
						xtype: 'label',
				        id: win.id + '_LabSampleNumInner_' + labsample.frameId,
						html: '<a class="LabSampleNum" href="javascript://" onClick="Ext.getCmp(\'EvnLabRequestEditWindow\').openEvnLabSampleEditWindow(\''+labsample.frameId+'\');">Проба № ' + labsample.EvnLabSample_ShortNum + '</a>'
					}]
				}, {
					layout: 'form',
					width: 80,
					items: [{
						xtype: 'label',
						html: labsample.RefMaterial_Name
					}]
				}, {/*взята*/
					layout: 'card',
					id: win.id + '_LabSampleSetDTCardPanel_' + labsample.frameId,
					cls: 'noMargin', 
					activeItem: LabSampleTaken?1:0,
					autoHeight: true,
					defaults: {bodyStyle:'background:#DFE8F6;', border: false},
					width: 150,
					items: [{
						items: [{
							xtype: 'button',	
							disabled: win.action == 'view' || !LabSampleSaved,
							iconCls: 'TakeLabSample',
							text: langs('Взять пробу'),
							id: win.id + '_TakeLabSampleAction_' + labsample.frameId,
							handler: function() 
							{
								win.takeLabSample(labsample.frameId);
							}
						}]
					}, {
						cls: 'LabSampleTaken',
						items: [{
							xtype: 'label',
							id: win.id + '_LabSampleSetDT_' + labsample.frameId,
							html: langs('Взята') + ': ' + labsample.EvnLabSample_setDT
						}]
					}]
				}, {//Штрихкод
					layout: 'form',
                    hidden: !LabSampleTaken,
                    id: win.id+'_LabSampleBarCodeCont_'+labsample.frameId,
					width: 150,
					items: [{
						xtype: 'label',
						id: win.id+'_LabSampleBarCodeContInner_'+labsample.frameId,
						html: '<div style="float: left;">Штрих-код:&nbsp;</div><div style="float: left;" id="'+win.id+'_LabSampleBarCode_'+labsample.frameId +'_inp"></div><div style="float: left;" id="'+win.id+'_LabSampleBarCode_'+labsample.frameId+'"><a class="LabSampleBarCode" href="javascript://" onClick="Ext.getCmp(\'EvnLabRequestEditWindow\').showInputBarCodeField(\''+labsample.frameId+'\', this);">' + labsample.EvnLabSample_BarCode + '</a></div>'
					}]
				}, {/*Анализатор*/
					layout: 'form',
					/*columnWidth: .20,*/
					cls: 'noMargin floatRight',
					items: [{
						xtype: 'button',
						disabled: win.action == 'view' || !LabSampleSaved,
						iconCls: 'sendToLis',
						id: win.id + '_SendToLisAction_' + labsample.frameId,
						text: langs('Отправить на анализатор'),
						hidden: (win.ARMType.inlist(['pzm'])),
						handler: function() 
						{
							win.sendToLis(labsample.frameId);
						}
					}]
				}, {/*Печать*/
					layout: 'form',					
					cls: 'noMargin floatRight',
                    id: win.id + '_PrintLabSample_' + labsample.frameId,
					items: [{
						xtype: 'button',
						iconCls: 'PrintLabSample',
						text: langs('Печатать'),
						id: win.id + '_PrintLabSampleAction_' + labsample.frameId,
						disabled: !LabSampleSaved,
						menu: [{
							text: langs('Печатать штрих-код'),
							handler: function() 
							{
							    switch(Number(Ext.globalOptions.lis.barcode_print_method))
							    {
							        // JS
							        case 1:
							            win.JsBarcode(labsample);
							        break;

							        // PDF
							        case 2:
							            win.printBarCode(labsample.frameId);
							        break;

							        // JAVA
							        case 3:
							            win.printonZebra(labsample);
							        break;

							        default:
							            sw.swMsg.alert(langs('Ошибка'), 'Выберите метод печати');
							        break;
							    }
							},
                            disabled: !LabSampleTaken
						},
						{
							text: langs('Печатать результаты тестов'),
							handler: function() 
							{
								win.printLabSampleResults(labsample.frameId);
							}
						}]
					}]
				}]
			}, {
				fieldLabel: langs('Отправить пробу в'),
				listeners: {
					'render': function() {
						if (labsample.MedService_id) {
							win.findById(win.id + '_LabSampleMedServiceCombo_' + labsample.frameId).setValue(labsample.MedService_id);
						} else {
							win.findById(win.id + '_LabSampleMedServiceCombo_' + labsample.frameId).setValue(win.MedService_id);
						}

						win.loadMedService(win.findById(win.id + '_LabSampleMedServiceCombo_' + labsample.frameId));
					},
					'change': function(combo, newValue) {
						// Для проб, предназначенных к отправке во внешнюю лабораторию на форме редактирования заявки вместо кнопки «Отправить на анализатор» отображать действие «Аутсорсинг».
						win.findById(win.id + '_SendToLisAction_' + labsample.frameId).setText(langs('Отправить на анализатор'));
						if (combo.getFieldValue('MedService_IsExternal') && combo.getFieldValue('MedService_IsExternal') == 2) {
							win.findById(win.id + '_SendToLisAction_' + labsample.frameId).setText(langs('Аутсорсинг'));
						}
					}
				},
				disabled: !win.ARMType.inlist(['pzm','reglab']) || LabSampleSaved,
				hiddenName: 'SampleMedService_id',
				id: win.id + '_LabSampleMedServiceCombo_' + labsample.frameId,
				width: 500,
				xtype:'swmedserviceglobalcombo'
			}, {
				layout: 'form',
				border: false,
				bodyStyle: 'background: #FFF;',
				items: [ EvnUslugaDataGrid, {
					style: 'position:relative; margin-left: 10px; top: 10px;',
					hidden: win.delDocsView, 
					id: win.id + '_AddNewResearchAction_' + labsample.frameId,
					html: '<div onclick="Ext.getCmp(\'EvnLabRequestEditWindow\').addNewResearch(\''+labsample.frameId+'\');" style="color: rgb(0, 0, 0); float: left; cursor: pointer; margin-bottom: 20px; margin-left: 10px; margin-top: 10px;"><img src="/img/icons/add_icon.png" style="float:left;"><div style="margin-left: 5px; float: left; text-decoration: underline;">Добавить исследование</div></div>',
					xtype: 'label'
				}]
			}]
		});
	},
	addNewResearch: function(frameId) {
		// проверяем что выбрана служба
		var win = this;
		
		if ( !win.addWithoutRegIsAllowed() ) return;

		var MedService_id = null;
		if (win.findById(win.id + '_LabSampleMedServiceCombo_' + frameId).disabled) {
			// если службу менять нельзя уже, значит и при добавлении исследования необходимо показывать услуги только данной службы
			MedService_id = win.findById(win.id + '_LabSampleMedServiceCombo_' + frameId).getValue();
			if (Ext.isEmpty(MedService_id)) {
				MedService_id = win.LabRequestEditForm.getForm().findField('MedService_id').getValue();
			}
		}

		// открываем форму добавления исследования
		getWnd('swUslugaComplexMedServiceSelectWindow').show({
			armMode: 'lis',
			MedService_sid: win.MedService_id, // служба арма
			MedService_id: MedService_id,
			callback: function(researches, MedService_id) {
				// создаём EvnUslugaPar связанный с пробой для каждого выбранного исследования
				win.findById(win.id + '_LabSampleMedServiceCombo_' + frameId).setValue(MedService_id);
				win.saveResearches(frameId, researches);
			}
		});
	},
	saveEvnLabSample: function(frameId, callback) {
		var win = this;
		if (win.action == 'view') {
			return false;
		}
		var base_form = win.LabRequestEditForm.getForm();

		// предварительно сохраняем заявку
		if (Ext.isEmpty(base_form.findField('EvnLabRequest_id').getValue())) {
			win.doSave({
				onSave: function () {
					win.saveEvnLabSample(frameId, callback);
				}
			});
			return false;
		}

		var params = {
			EvnLabRequest_id: base_form.findField('EvnLabRequest_id').getValue(),
			MedService_id: win.findById(win.id + '_LabSampleMedServiceCombo_' + frameId).getValue()
		};

		// Ajax-запрос на сохранение пробы, параметры: EvnLabRequest_id
		win.showLoadMask(langs('Сохранение пробы'));
		Ext.Ajax.request({
			failure:function () {
				win.hideLoadMask();
				sw.swMsg.alert(langs('Ошибка при сохранении пробы'), langs('Не удалось получить данные с сервера'));
			},
			params: params,
			success:function (response) {
				win.hideLoadMask();
				var result = Ext.util.JSON.decode(response.responseText);
				if (result && result.EvnLabSample_id) {
					// показываем грид с исследованиями
					win.findById(win.id + '_EvnUslugaDataGrid_' + frameId).show();
					// делаем доступными действия
					win.findById(win.id + '_SendToLisAction_' + frameId).enable();
					win.findById(win.id + '_PrintLabSampleAction_' + frameId).enable();
					win.findById(win.id + '_TakeLabSampleAction_' + frameId).enable();

					// дизаблим поле выбора службы
					win.findById(win.id + '_LabSampleMedServiceCombo_' + frameId).disable();

					var labsample_rec = win.LabSamplesStore.getById(frameId);
					labsample_rec.set('EvnLabSample_id', result.EvnLabSample_id);

					if (callback) {
						callback(result.EvnLabSample_id);
					}
				}
			},
			url:'/?c=EvnLabSample&m=saveLabSample'
		});
	},
	saveResearches: function(frameId, researches) {
		var win = this;
		if (win.action == 'view') {
			return false;
		}
		var base_form = win.LabRequestEditForm.getForm();

		// предварительно создаём пробу
		if (Ext.isEmpty(win.getEvnLabSampleIdByFrameId(frameId))) {
			win.saveEvnLabSample(frameId, function(EvnLabSample_id) {
				win.saveResearches(frameId, researches);
			});
			return false;
		}

		var params = {
			EvnLabRequest_id: base_form.findField('EvnLabRequest_id').getValue(),
			MedService_id: win.findById(win.id + '_LabSampleMedServiceCombo_' + frameId).getValue(),
			EvnLabSample_id: win.getEvnLabSampleIdByFrameId(frameId),
			researches: Ext.util.JSON.encode(researches)
		};

		win.showLoadMask(langs('Сохранение исследования'));
		Ext.Ajax.request({
			failure:function () {
				win.hideLoadMask();
				sw.swMsg.alert(langs('Ошибка при сохранении исследования'), langs('Не удалось получить данные с сервера'));
			},
			params: params,
			success:function (response) {
				win.hideLoadMask();
				var result = Ext.util.JSON.decode(response.responseText);
				if (result && result.success) {
					if (result.newLabSamples) {
						result.newLabSamples.forEach(function(EvnLabSample_id) {
							// добавляем области для каждой пробы
							var panel = win.addNewLabSampleFrame();
							var frameId = panel.frameId;
							// показываем грид с исследованиями
							win.findById(win.id + '_EvnUslugaDataGrid_' + frameId).show();
							// делаем доступными действия
							win.findById(win.id + '_SendToLisAction_' + frameId).enable();
							win.findById(win.id + '_PrintLabSampleAction_' + frameId).enable();
							win.findById(win.id + '_TakeLabSampleAction_' + frameId).enable();
							// дизаблим поле выбора службы
							win.findById(win.id + '_LabSampleMedServiceCombo_' + frameId).disable();
							var labsample_rec = win.LabSamplesStore.getById(frameId);
							labsample_rec.set('EvnLabSample_id', EvnLabSample_id);

							// обновляем грид
							var params = {
								EvnLabSample_id: win.getEvnLabSampleIdByFrameId(frameId)
							};

							win.findById(win.id + '_EvnUslugaDataGrid_' + frameId).loadData({
								params: params,
								globalFilters: params,
								noFocusOnLoad: true
							});
						});
					}

					// обновляем гриды
					win.LabSamplesStore.data.items.forEach(function(item) {
						var params = {
							EvnLabSample_id: item.data.EvnLabSample_id
						};
						win.findById(win.id + '_EvnUslugaDataGrid_' + item.id).loadData({
							params: params,
							globalFilters: params,
							noFocusOnLoad: true
						});
					});
				}
			},
			url:'/?c=EvnLabSample&m=saveLabSampleResearches'
		});
	},
	loadMedService: function(combo, callback) {
		var win = this;
		var base_form = this.LabRequestEditForm.getForm();
		var params = new Object();

		params.MedServiceTypeIsLabOrFenceStation = 1;

		if (this.ARMType.inlist(['pzm'])) {
			params.Lpu_isAll = 2;
		}

		if (this.ARMType.inlist(['lab','reglab','pzm', 'microbiolab'])) {
			// фильтруем лаборатории по MedService_id.
			params.MedService_id = this.MedService_id;
		}

		if (this.ARMType.inlist(['reglab'])) {
			// фильтруем лаборатории по доступным услугам
			params.UslugaComplex_prescid = base_form.findField('UslugaComplex_prescid').getValue();
		}

		params.ARMType = this.ARMType;

		combo.getStore().removeAll();
		win.showLoadMask(langs('Подождите, идет загрузка'));
		combo.getStore().load({
			callback:function () {
				win.hideLoadMask();

				var MedService_id = combo.getValue();
				var index = combo.getStore().findBy(function (rec) {
					if (rec.get('MedService_id') == MedService_id) {
						return true;
					}
					else {
						return false;
					}
				});

				if (index >= 0) {
					combo.setValue(MedService_id);
					combo.fireEvent('change', combo, MedService_id);
				}
				else {
					combo.clearValue();
					combo.fireEvent('change', combo, null);
				}

				if (typeof callback == 'function') {
					callback();
				}
			}.createDelegate(this),
			params: params
		});
	},
	addNewLabSampleFrame: function() {
		var win = this;

		var panel = win.getLabSamplePanel();
		win.LabSampleFrame.add(panel);
		win.LabSampleFrame.doLayout();

		return panel;
	},
	initComponent:function () {
		var win = this;

		this.LabSamplesStore = new Ext.data.Store({
			autoLoad: false,
			reader: new Ext.data.JsonReader({
				id: 'frameId'
			}, [
				{ name: 'frameId', mapping: 'frameId' },
				{ name: 'EvnLabSample_id', mapping: 'EvnLabSample_id' },
				{ name: 'EvnLabSample_ShortNum', mapping: 'EvnLabSample_ShortNum' },
				{ name: 'EvnLabSample_BarCode', mapping: 'EvnLabSample_BarCode' },
				{ name: 'RefMaterial_Name', mapping: 'RefMaterial_Name' },
				{ name: 'EvnLabSample_setDT', mapping: 'EvnLabSample_setDT' },
				{ name: 'MedService_id', mapping: 'MedService_id' }
			])
		});
		
		this.InformationPanel = new sw.Promed.PersonInfoPanel({
			id:'LREWPersonInformationFrame',
			plugins:[Ext.ux.PanelCollapsedTitle],
			titleCollapse:true,
			floatable:false,
			collapsible:true,
			collapsed:true,
			border:true,
			region:'north',
			isLis: true
		});
		
		this.LabSampleFrame = new sw.Promed.Panel({
			width: 964,
			border: false,
			bodyStyle: 'background:#DFE8F6; padding-left: 20px;',
			removeAllSamples: function() {
				// удаляем все панели с пробами
				win.LabSampleFrame.removeAll();
				win.LabSampleFrame.doLayout();
				win.LabSamplesStore.removeAll();
			},
			loadSamples: function(params) {
				win.showLoadMask(langs('Подождите, идет загрузка'));
				Ext.Ajax.request({
					failure:function () {
						win.hideLoadMask();
						sw.swMsg.alert(langs('Ошибка при загрузке проб'), langs('Не удалось получить данные с сервера'));
					},
					params: params,
					success:function (response) {
						win.hideLoadMask();

						var count = 0;
						var base_form = win.LabRequestEditForm.getForm();
						var result = Ext.util.JSON.decode(response.responseText);
						if (result) {
							for (var k in result) {
								if (typeof result[k] == 'object') {
									var labsample = result[k];

									count++;
									// добавляем на форму панель с пробой
									win.LabSampleFrame.add(win.getLabSamplePanel(labsample));
									win.LabSampleFrame.doLayout();
								}
							}
						}

						if (count == 0) {
							// добавляем пустую область
							win.addNewLabSampleFrame();
						}
					},
					url:'/?c=EvnLabSample&m=loadLabSampleFrame'
				});
			},
			items: []
		});

		win.EvnPrescrLimitGrid = new sw.Promed.ViewFrame({
			useEmptyRecord: false,
			autoLoadData: false,
			border: false,
			autoExpandColumn: 'autoexpand',
			object: 'EvnPrescrLimit',
			dataUrl: '/?c=EvnPrescrLimit&m=loadGrid',
			region: 'center',
			height: 150,
			width: 944,
			saveAtOnce: false,
			actions: [
				{ name:'action_add', hidden: true },
				{ name:'action_edit', hidden: true },
				{ name:'action_view', hidden: true },
				{ name:'action_delete', hidden: true },
				{ name:'action_print', hidden: true },
				{ name:'action_refresh' }
			],
			stringfields: [
				{ name: 'EvnPrescrLimit_id', type: 'int', key: true, hidden: true },
				{ name: 'LimitType_Name', header: langs('Наименование'), id: 'autoexpand' },
				{ name: 'limitObj', header: langs('Показатель'), width: 200 }
			],
			onLoadData: function(loaded) {
				if(!loaded) return;
				var count = this.getGrid().getStore().getCount();
				if(count) {
					win.PrescrLimitPanel.expand();
				}
			},
			checkBeforeLoadData: function() {
				return Boolean(this.getParam('EvnDirection_id'));
			}
		});

		win.PrescrLimitPanel = new sw.Promed.Panel({
			autoHeight: true,
			style: 'margin-bottom: 0.5em;',
			bodyStyle: 'background:#DFE8F6;margin-left:20px;',
			border: true,
			collapsible: true,
			region: 'north',
			layout: 'form',
			title: langs('Ограничения'),
			items: [
				win.EvnPrescrLimitGrid
			]
		});

		var EvnDirectionDetail = {
			id: win.id + 'EvnDirectionDetail',
			xtype: 'fieldset',
			autoHeight: true,
			title: 'Дополнительные сведения о пациенте',
			style: 'padding: 2; padding-left: 5px',
			items: [
				{
					xtype: 'swcommonsprcombo',
					fieldLabel: langs('Код контингента ВИЧ'),
					comboSubject: 'HIVContingentTypeFRMIS',
					hiddenName: 'HIVContingentTypeFRMIS_id',
					allowBlank: false,
					editable: true,
					ctxSerach: true,
					loadParams: { params: { where: ' where HIVContingentTypeFRMIS_Code != 100' } },
					width: 500
				}, {
					xtype: 'swcommonsprcombo',
					fieldLabel: langs('Код контингента COVID'),
					comboSubject: 'CovidContingentType',
					hiddenName: 'CovidContingentType_id',
					editable: true,
					ctxSerach: true,
					width: 500
				}, {
					xtype: 'swcommonsprcombo',
					hiddenName: 'HormonalPhaseType_id',
					comboSubject: 'HormonalPhaseType',
					fieldLabel: langs('Фаза цикла'),
					width: 500,
				}, {
					id: win.id + 'RaceType_FS',
					xtype: 'fieldset',
					layout: 'column',
					border: false,
					autoHeight: true,
					labelWidth: 130,
					style: 'margin: 2px 0 0 0; padding: 0;',
					items: [
						{
							xtype: 'panel',
							html: 'Раса: ',
							layuot: 'anchor',
							width: 150,
							style: 'margin-right: 5px;',
							bodyStyle: 'text-align: right; border: 0px; font: normal 12px tahoma, arial, helvetica, sans-serif; background: none;'
						}, {
							xtype: 'swcommonsprcombo',
							fieldLabel: langs('Раса'),
							comboSubject: 'RaceType',
							hiddenName: 'RaceType_id',
							anchor: '95%',
							disabled: true
						}, {
							xtype: 'button',
							id: win.id + 'RaceTypeAddBtn',
							style: 'margin-left: 5px;',
							text: 'Добавить',
							handler: function () {
								getWnd('swPersonRaceEditWindow').show({
									formParams: {
										PersonRace_id: 0,
										Person_id: win.params.Person_id
									},
									action: 'add',
									onHide: Ext.emptyFn,
									callback: function(data) {
										if (!data || !data.personRaceData)
											return false;
										win.formPanel.getForm()
											.findField('RaceType_id')
											.setValue(data.personRaceData.RaceType_id);
										Ext.getCmp(win.id + 'RaceTypeAddBtn').setDisabled(true);
									}
								});
							}
						}
					]
				}, {
					id: win.id + 'PersonHeight_FS',
					xtype: 'fieldset',
					layout: 'column',
					border: false,
					autoHeight: true,
					labelWidth: 130,
					style: 'margin: 2px 0 0 0; padding: 0;',
					items: [
						{
							xtype: 'panel',
							html: 'Рост (см): ',
							name: 'PersonHeight_Height_label',
							layuot: 'anchor',
							width: 150,
							style: 'margin-right: 5px;',
							bodyStyle: 'text-align: right; border: 0px; font: normal 12px tahoma,arial,helvetica,sans-serif; background: none;'
						}, {
							xtype: 'textfield',
							name: 'PersonHeight_Height',
							disabled: true
						}, {
							xtype: 'panel',
							html: ' на дату: ',
							layuot: 'anchor',
							bodyStyle: 'padding: 1px 5px 0 5px; border: 0px; font: normal 12px tahoma,arial,helvetica,sans-serif; background: none;'
						}, {
							fieldLabel : lang['okonchanie'],
							name: 'PersonHeight_setDT',
							xtype: 'swdatefield',
							disabled: true,
							format: 'd.m.Y',
							plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ]
						}, {
							xtype: 'button',
							id: win.id + 'PersonHeightAddBtn',
							text: 'Добавить',
							style: 'margin-left: 5px;',
							handler: function () {
								getWnd('swPersonHeightEditWindow').show({
									measureTypeExceptions:[1,2],
									formParams: {
										PersonHeight_id: 0,
										Person_id: win.params.Person_id
									},
									action: 'add',
									onHide: Ext.emptyFn,
									callback: function(data) {
										if (!data || !data.personHeightData)
											return false;
										win.formPanel.getForm()
											.findField('PersonHeight_Height')
											.setValue(data.personHeightData.PersonHeight_Height);
										var date = Ext.util.Format.date(new Date(data.personHeightData.PersonHeight_setDate), 'd.m.Y');
										win.formPanel.getForm()
											.findField('PersonHeight_setDT')
											.setValue(date);
									}
								});
							}
						}
					]
				}, {
					id: win.id + 'PersonWeight_FS',
					xtype: 'fieldset',
					layout: 'column',
					border: false,
					autoHeight: true,
					labelWidth: 130,
					style: 'margin: 2px 0 0 0; padding: 0;',
					items: [
						{
							xtype: 'panel',
							html: 'Масса: ',
							layuot: 'anchor',
							width: 150,
							style: 'margin-right: 5px;',
							bodyStyle: 'text-align: right; border: 0px; font: normal 12px tahoma, arial, helvetica, sans-serif; background: none;'
						}, {
							xtype: 'textfield',
							name: 'PersonWeight_WeightText',
							disabled: true
						}, {
							xtype: 'panel',
							html: ' на дату: ',
							layuot: 'anchor',
							bodyStyle: 'padding: 1px 5px 0 5px; border: 0px; font: normal 12px tahoma, arial, helvetica, sans-serif; background: none;'
						}, {
							fieldLabel : lang['okonchanie'],
							name: 'PersonWeight_setDT',
							xtype: 'swdatefield',
							disabled: true,
							format: 'd.m.Y',
							plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ]
						}, {
							xtype: 'button',
							id: win.id + 'PersonWeightAddBtn',
							text: 'Добавить',
							style: 'margin-left: 5px;',
							handler: function () {
								getWnd('swPersonWeightEditWindow').show({
									measureTypeExceptions:[1,2],
									Okei_InterNationSymbol:"kg",
									formParams: {
										PersonWeight_id: 0,
										Person_id: win.params.Person_id
									},
									action: 'add',
									onHide: Ext.emptyFn,
									callback: function(data) {
										if (!data || !data.personWeightData)
											return false;
										win.formPanel.getForm()
											.findField('PersonWeight_WeightText')
											.setValue(data.personWeightData.PersonWeight_text);
										var date = Ext.util.Format.date(new Date(data.personWeightData.PersonWeight_setDate), 'd.m.Y');
										win.formPanel.getForm()
											.findField('PersonWeight_setDT')
											.setValue(date);
									}
								});
							}
						}
					]
				}
			]
		};

		this.LabRequestEditForm = new Ext.form.FormPanel({
			autoScroll:true,
			bodyBorder:false,
			bodyStyle:'padding: 5px 5px 0',
			border:false,
			frame:false,
			region:'center',
			id:'LabRequestEditForm',
			labelAlign:'right',
			labelWidth:150,
			items:[
				new sw.Promed.Panel({
					autoHeight:true,
					style:'margin-bottom: 0.5em;',
					bodyStyle:'background:#DFE8F6;padding:5px;',
					border:true,
					collapsible:true,
					region:'north',
					layout:'form',
					title:langs('Направление'),
					items:[
						{
							name:'Person_id',
							xtype:'hidden',
							value:0
						},
						{
							name:'pmUser_insID',
							xtype:'hidden',
							value:0
						},
						{
							name:'PersonEvn_id',
							xtype:'hidden',
							value:0
						},
						{
							name:'Server_id',
							xtype:'hidden'
						},
						{
							name:'MedService_id',
							xtype:'hidden'
						},
						{
							name:'MedService_sid',
							xtype:'hidden'
						},
						{
							name:'EvnDirection_id',
							xtype:'hidden'
						},
						{
							name:'EvnUsluga_id',
							xtype:'hidden'
						},
						{
							name:'XmlTemplate_id',
							xtype:'hidden'
						},
						{
							name:'EvnLabRequest_id',
							xtype:'hidden'
						},
						{
							name: 'EvnDirection_IsReceive',
							value: 2,
							xtype: 'hidden'
						},
						{
							name:'EDPayType_id',
							xtype:'hidden'
						},
						{
							name:'UslugaComplex_prescid',
							xtype:'hidden'
						},
						{
							allowBlank:false,
							enableKeyEvents:true,
							fieldLabel:langs('Номер направления'),
							listeners:{
								'keydown':function (inp, e) {
									switch (e.getKey()) {
										case Ext.EventObject.F2:
											e.stopEvent();
											this.getEvnDirectionNumber();
											break;
										case Ext.EventObject.TAB:
											if (e.shiftKey == true) {
												e.stopEvent();
												this.buttons[this.buttons.length - 1].focus();
											}
											break;
									}
								}.createDelegate(this)
							},
							name:'EvnDirection_Num',
							onTriggerClick:function () {
								this.getEvnDirectionNumber();
							}.createDelegate(this),
							tabIndex:TABINDEX_ELREW + 1,
							triggerClass:'x-form-plus-trigger',
							validateOnBlur:false,
							width:150,
							xtype:'trigger',
							autoCreate:{tag:"input", autocomplete:"off"}
						},
						{
							fieldLabel:langs('Дата направления'),
							tabIndex:TABINDEX_ELREW + 2,
							name:'EvnDirection_setDT',
							xtype:'swdatefield',
							plugins:[new Ext.ux.InputTextMask('99.99.9999', false)],
							allowBlank: (!getRegionNick().inlist([ 'perm', 'krym', 'ekb']))
						},
						{
							fieldLabel:langs('Кем направлен'),
							tabIndex:TABINDEX_ELREW + 3,
							hiddenName:'PrehospDirect_id',
							xtype:'swprehospdirectcombo',
							listeners:{
								'select':function (combo, record, index) {
									this.selectPrehospDirect(combo, record, index);

									if (getRegionNick() == 'ekb') {
										var form = win.LabRequestEditForm.getForm();

										form.findField('MedStaffFact_id').setAllowBlank(index != 1);
										form.findField('MedPersonal_Code').setAllowBlank( !(index == 1 || index == 2) );
										form.findField('Org_sid').setAllowBlank(index != 2);

									}
								}.createDelegate(this)
							},
							width:500
						},
						{
							displayField: getRegionNick()=='ekb' ? 'Org_Nick': 'Org_Name',
							editable:false,
							enableKeyEvents:true,
							fieldLabel:langs('Организация'),
							tabIndex:TABINDEX_ELREW + 4,
							hiddenName:'Org_sid',
							mode:'local',
							onTrigger1Click: function () {
								var base_form = this.LabRequestEditForm.getForm();
								var combo = base_form.findField('Org_sid');
								if (combo.disabled) {
									return false;
								}
								var prehosp_direct_combo = base_form.findField('PrehospDirect_id');
								var prehosp_direct_id = prehosp_direct_combo.getValue();
								var record = prehosp_direct_combo.getStore().getById(prehosp_direct_id);
								if (!record) {
									return false;
								}
								var prehosp_direct_code = record.get('PrehospDirect_Code');
								var org_type = '' , OrgType_id = null, enableOrgType = false;
								if(getRegionNick() == 'kz'){
									switch (prehosp_direct_code) {
										case 3:
										case 4:
										case 6:
										case 9:
											org_type = 'lpu';
											OrgType_id = 11;
											break;
										case 5:
											org_type = 'military';
											OrgType_id = 17;
											break;
										case 7:
											org_type = 'org';
											enableOrgType = true;
											break;
										default:
											return false;
											break;
									}
								} else {
									switch (prehosp_direct_code) {
										case 2:
										case 5:
											org_type = 'lpu';
											OrgType_id = 11;
											break;
										case 4:
											org_type = 'military';
											OrgType_id = 17;
											break;
										case 3:
										case 6:
											org_type = 'org';
											enableOrgType = true;
											break;
										default:
											return false;
											break;
									}
								}
								getWnd('swOrgSearchWindow').show({
									OrgType_id: OrgType_id,
									enableOrgType: enableOrgType,
									object:org_type,
									onClose:function () {
										combo.focus(true, 200)
									},
									onSelect:function (org_data) {
										if (org_data.Org_id > 0) {
											combo.getStore().loadData([
												{
													Org_id:org_data.Org_id,
													Lpu_id: org_data.Lpu_id,
													Org_Name:org_data.Org_Name,
													Org_Nick:org_data.Org_Nick
												}
											]);
											combo.setValue(org_data.Org_id);
											getWnd('swOrgSearchWindow').hide();
											combo.fireEvent('change', combo, combo.getValue(), null);
											combo.collapse();
										}
									}
								});
							}.createDelegate(this),
							onTrigger2Click: function() { // при очистке нужно очистить и грид с пробами и состав
								if ( !this.disabled ) {
									this.clearValue();
									var lpusection_combo = win.LabRequestEditForm.getForm().findField('LpuSection_id');
									var medstafffact_combo = win.LabRequestEditForm.getForm().findField('MedStaffFact_id');
									lpusection_combo.getStore().removeAll();
									lpusection_combo.clearValue();
									medstafffact_combo.getStore().removeAll();
									medstafffact_combo.clearValue();
								}
							},
							store:new Ext.data.JsonStore({
								autoLoad:false,
								fields:[
									{name: 'Org_id', type: 'int'},
									{name: 'Lpu_id', type: 'int'},
									{name: 'Org_Name', type: 'string'},
									{name: 'Org_Nick', type: 'string'}
								],
								key:'Org_id',
								sortInfo:{
									field: getRegionNick()=='ekb' ? 'Org_Nick' : 'Org_Name'
								},
								url:C_ORG_LIST
							}),
							tpl:new Ext.XTemplate('<tpl for="."><div class="x-combo-list-item">', 
								getRegionNick()=='ekb' ? '{Org_Nick}': '{Org_Name}', 
								'</div></tpl>'),
							trigger1Class:'x-form-search-trigger',
							triggerAction:'none',
							valueField:'Org_id',
							width:500,
							xtype:'swbaseremotecombo',
							listeners: {
								'change':function (combo, newValue, oldValue) {
									var base_form = this.LabRequestEditForm.getForm();
									var LpuSection = base_form.findField('LpuSection_id');
									var LpuSection_id = LpuSection.getValue();
                                    LpuSection.getStore().removeAll();
									if (!Ext.isEmpty(base_form.findField('Org_sid').getFieldValue('Lpu_id'))) {
										LpuSection.getStore().load({
											params:{
												filterLpu_id: base_form.findField('Org_sid').getFieldValue('Lpu_id')
											},
											callback: function() {
												if (LpuSection_id>0 && LpuSection.getStore().getById(LpuSection_id)) {
													LpuSection.setValue(LpuSection_id);
												} else {
													LpuSection.clearValue();
                                                    LpuSection_id = null;
												}
												LpuSection.fireEvent('change', LpuSection, LpuSection_id, null);
											}
										});
									} else {
										LpuSection.setValue(null);
									}
								}.createDelegate(this)
							}
						},
						{
							hiddenName:'LpuSection_id',
							tabIndex:TABINDEX_ELREW + 5,
							xtype:'swlpusectioncombo',
							width:500,
							listeners:{
								'change':function (combo, newValue, oldValue) {
									var base_form = this.LabRequestEditForm.getForm();
									var MedStaffFact = base_form.findField('MedStaffFact_id');
									var MedStaffFact_id = MedStaffFact.getValue(),
										lpu_id = base_form.findField('Org_sid').getFieldValue('Lpu_id') || getGlobalOptions().lpu_id;
                                    MedStaffFact.getStore().removeAll();
                                    //debugger;
                                    //log(['change LpuSection_id', newValue, MedStaffFact_id]);
									if (newValue>0 || lpu_id) {
										MedStaffFact.getStore().load({
											params:{
												LpuSection_id: newValue,
												Lpu_id: base_form.findField('Org_sid').getFieldValue('Lpu_id') || getGlobalOptions().lpu_id
											},
											callback:function () {
												if (MedStaffFact_id>0 && MedStaffFact.getStore().getById(MedStaffFact_id)) {
													MedStaffFact.setValue(MedStaffFact_id);
												} else {
													MedStaffFact.clearValue();
												}
											}
										});
									} else {
										MedStaffFact.clearValue();
									}
								}.createDelegate(this)
							}
						},
						{
							fieldLabel:langs('Палата'),
							tabIndex:TABINDEX_ELREW + 6,
							xtype:'textfield',
							width:500,
							name:'EvnLabRequest_Ward'
						},
						{
							fieldLabel:langs('Врач'),
							tabIndex:TABINDEX_ELREW + 7,
							hiddenName:'MedStaffFact_id',
							allowBlank:true,
							xtype:'swmedstafffactglobalcombo',
							width:500,
							anchor:'auto',
							listeners: {
								'select' : function(combo) {
									if(getRegionNick()=='ekb') {
										var base_form = win.LabRequestEditForm.getForm();
										var DloCode = base_form.findField('MedPersonal_Code');
										if(combo.getValue()) {
											DloCode.setValue(combo.getFieldValue('MedPersonal_DloCode'));
										} else DloCode.setValue('');
									}
								}
							}
						}, {
							fieldLabel:langs('Код врача'),
							tabIndex:TABINDEX_ELREW + 7.5,
							hiddenName:'MedPersonal_Code',
							id: 'MedPersonal_Code',
							allowBlank:true,
							maxLength: 14,
							xtype:'numberfield',
							width: 150
						}, {
							checkAccessRights: true,
							fieldLabel: langs('Диагноз'),
							tabIndex:TABINDEX_ELREW + 8,
							hiddenName: 'Diag_id',
							hidden: true,
							xtype: 'swdiagcombo',
							width: 500,
							onChange: function() {
								win.setTumorStageVisibility();
							},
							listeners: {
								render: function(field) {
									field.hideContainer();
								}
							}
						}, 
						{
							xtype: 'swcommonsprcombo',
							comboSubject: 'TumorStage',
							hiddenName: 'TumorStage_id',
							fieldLabel: 'Стадия выявленного ЗНО',
							tabIndex: TABINDEX_ELREW + 8,
							width: 500
						},
						{
							fieldLabel: 'МЭС',
							hiddenName: 'Mes_id',
							tabIndex:TABINDEX_ELREW + 8,
							width: 500,
							forceSelection: true,
							xtype: 'swmesekbcombo'
						}, {
							name: 'EvnDirection_IsCito',
							tabIndex:TABINDEX_ELREW + 8,
							fieldLabel:'Cito!',
							xtype:'checkbox',
							checked:false
						},
						{
							fieldLabel: langs('Комментарий'),
							height: 70,
							name: 'EvnDirection_Descr',
							width:500,
							xtype: 'textarea'
						}, {
							layout: 'column',
							bodyStyle:'background:transparent;',
							defaults: {bodyStyle:'background:transparent;'},
							border: false,
							items: [{
								layout: 'form',
								labelWidth: 150,
								border: false,
								items: [{
									fieldLabel: getRegionNick() == 'kz' ? 'Источник финансирования' : 'Вид оплаты',
									allowBlank: false,
									tabIndex: TABINDEX_ELREW + 9,
									hiddenName: 'PayType_id',
									listeners: {
										'change': function (combo, newValue, oldValue) {
											win.checkPayType();
										}
									},
									xtype: 'swpaytypecombo',
									width: 500
								}]
							}, {
								layout: 'form',
								border: false,
								bodyStyle:'padding: 7px 10px; background:transparent;',
								items: [{
									id: win.id + '_PayTypeWarn',
									html: '<img ext:qtip="Вид оплаты, указанный в заявке, отличается от вида оплаты в направлении." src="/img/icons/warn_red.png" />',
									xtype: 'label'
								}]
							}]
						},
						{
							fieldLabel:langs('Регистрационный номер'),
							xtype:'textfield',
							width:500,
							maxLength: 30,
							regex: /^[^а-яА-Яa-zA-Z\s]*$/,
							regexText: langs('Номер не должен содержать буквенные символы, пробелы, пустые строки.'),
							name:'EvnLabRequest_RegNum',
							hidden: true
						},
						EvnDirectionDetail
					]
				}),
				new sw.Promed.Panel({
					autoHeight:true,
					style:'margin-bottom: 0.5em;',
					bodyStyle:'background:#DFE8F6;',
					border:true,
					collapsible:true,
					region:'north',
					layout:'form',
					title:langs('Услуги'),
					items:[
						win.LabSampleFrame,
						{
							fieldLabel:langs('Результат'),
							xtype:'swuslugaexecutiontypecombo',
							hiddenName:'UslugaExecutionType_id',
							width:350
						},
						/*{
							xtype: 'label',
							id: win.id + '_LabRequestAddComment',
							html: '<a class="LabRequestAddComment" href="javascript://" onClick="Ext.getCmp(\'EvnLabRequestEditWindow\').addComment();">Добавить комментарий</a>'
						},*/
						{
							fieldLabel:langs('Комментарий'),
							xtype:'textarea',
							name:'EvnLabRequest_Comment',
							width:350
						}
					]
				}),
				win.PrescrLimitPanel
			],
			reader:new Ext.data.JsonReader({
				success:Ext.emptyFn
			}, [
				{name:'Person_id'},
				{name:'PersonEvn_id'},
				{name:'Server_id'},
				{name:'MedService_id'},
				{name:'MedService_sid'},
				{name:'EvnDirection_id'},
				{name:'EvnLabRequest_id'},
				{name:'EvnDirection_IsReceive'},
				{name:'EDPayType_id'},
				{name:'EvnDirection_Num'},
				{name:'EvnDirection_setDT'},
				{name:'PrehospDirect_id'},
				{name:'Org_sid'},
				{name:'LpuSection_id'},
				{name:'MedStaffFact_id'},
				{name:'MedPersonal_Code'},
				{name:'EvnDirection_IsCito'},
				{name:'EvnDirection_Descr'},
				{name:'PayType_id'},
				{name:'Diag_id'},
				{name:'TumorStage_id'},
				{name:'Mes_id'},
				{name:'UslugaComplex_prescid'},
				{name:'XmlTemplate_id'},
				{name:'UslugaExecutionType_id'},
				{name:'EvnLabRequest_Comment'},
				{name:'EvnUsluga_id'},
				{name:'PersonDetailEvnDirection_id'},
				{name:'HIVContingentTypeFRMIS_id'},
				{name:'CovidContingentType_id'},
				{name:'HormonalPhaseType_id'},
				{name:'RaceType_id'},
				{name:'PersonHeight_Height'},
				{name:'PersonHeight_setDT'},
				{name:'PersonWeight_WeightText'},
				{name:'PersonWeight_setDT'},
				{name:'pmUser_insID'}
			]),
			url:'/?c=EvnLabRequest&m=save'
		});
		this.formPanel = this.LabRequestEditForm;

		this.ElectronicQueuePanel = new sw.Promed.ElectronicQueuePanel({
			ownerWindow: win,
			panelType: 2,
			region: 'south',
			//dontDisableCompleteBtn: true, // не дизаблим кнопку завершения приема
			// функция выполняющаяся при нажатии на кнопку завершить прием
			completeServiceActionFn: function(params){ win.doSave(params) }
		});

		this.LabRequestEditPanel = new sw.Promed.Panel({
			region: 'center',
			border: false,
			layout: 'border',
			items: [
				this.InformationPanel,
				this.LabRequestEditForm
			]
		});

		this.buttonsCfg = [

			{
				handler:function () {
					win.doSave();
				},
				itemId: 'actionSave',
				iconCls:'save16',
				cls: 'newInGridButton save',
				text:BTN_FRMSAVE
			},
			{
				text:'-'
			},
//				HelpButton(this, 100500),
			{
				text: BTN_FRMHELP,
				cls: 'newInGridButton help',
				iconCls: 'help16',
				handler: function(button, event) {
					ShowHelp(win.title);
				}.createDelegate(this)
			},
			{
				handler:function () {
					if ('add' == win.action) {
						var EvnDirection_id = win.LabRequestEditForm.getForm().findField('EvnDirection_id').getValue();
						if (EvnDirection_id > 0) {
							var EvnDirection_ids = [ EvnDirection_id ];
							sw.swMsg.show({
								buttons:Ext.Msg.YESNO,
								fn:function (buttonId, text, obj) {
									if (buttonId == 'yes') {
										win.showLoadMask(langs('Удаление заявки'));
										Ext.Ajax.request({
											failure:function () {
												sw.swMsg.alert(langs('Ошибка при удалении заявки'), langs('Не удалось получить данные с сервера'));
												win.hideLoadMask();
											},
											params:{
												EvnDirection_ids: Ext.util.JSON.encode(EvnDirection_ids),
												EvnStatusCause_id: 4, // неверный ввод
												EvnStatusHistory_Cause: ''
											},
											success:function (response) {
												win.callback({EvnLabRequest_id: null});
												var result = Ext.util.JSON.decode(response.responseText);
												if (result[0])
													result = result[0];
												if (!result || !result.success) {
													sw.swMsg.alert(langs('Ошибка при удалении заявки'), result.Error_Msg || 'Ошибка при отправке/получении запроса');
												} else {
													win.hide();
												}
												win.hideLoadMask();
											},
											url:'/?c=EvnLabRequest&m=cancelDirection'
										});
									}
								},
								icon:Ext.MessageBox.QUESTION,
								msg:langs('Заявка на лабораторное исследование была сохранена автоматически. Удалить ее?'),
								title:langs('Вопрос')
							});
						} else {
							win.hide();
						}
					} else {
						win.hide();
					}
				},
				iconCls:'cancel16',
				cls: 'newInGridButton close',
				text:BTN_FRMCANCEL
			}
		];

		this.btnsPanel = new sw.Promed.Panel({
			region: 'south',
			buttons: win.buttonsCfg,
			border: false,
			layout: 'border',
			items: []
		});

		this.mainPanel = new sw.Promed.Panel({
			region: 'center',
			border: false,
			layout: 'border',
			items: [
				this.LabRequestEditPanel,
				this.ElectronicQueuePanel
			]
		});

		win.medServStore = new Ext.data.Store({
			url: '/?c=MedService&m=getMedServiceData',
			baseParams: {
				Columns: 'MedService_IsShowDiag'
			},
			autoLoad: false,
			reader: new Ext.data.JsonReader({
				}, [{ mapping: 'MedService_IsShowDiag', name: 'MedService_IsShowDiag', type: 'int'}]
			),
			listeners: {
				load: function(store, records) {
					var ms = records[0].data;
					var flag = ['ekb', 'kareliya'].indexOf(getRegionNick()) !== -1;
					flag |= ms.MedService_IsShowDiag == 2;
					var field = win.LabRequestEditForm.getForm().findField('Diag_id');
					if (flag == true) field.showContainer();
					else field.hideContainer();
				}
			},
		});
		
		Ext.apply(this, {
			buttons:[],
			items:[
				this.mainPanel,
				this.btnsPanel
			]
		});

		sw.Promed.swEvnLabRequestEditWindow.superclass.initComponent.apply(this, arguments);
	},
	addComment: function() {
		var win = this;
		if (win.action == 'view') {
			return false;
		}
		
		var base_form = this.LabRequestEditForm.getForm();
		//Ext.getCmp(win.id + '_LabRequestAddComment').hide();
		base_form.findField('EvnLabRequest_Comment').showContainer();
	},
	hideComment: function() {
		var win = this;
		var base_form = this.LabRequestEditForm.getForm();
		//Ext.getCmp(win.id + '_LabRequestAddComment').show();
		base_form.findField('EvnLabRequest_Comment').hideContainer();
	},
	getDate: function() {
		var win = this;
		win.showLoadMask(langs('Получение текущей даты'));
		getCurrentDateTime({
			ARMType: 'lis',
			callback: function(r) {
				win.hideLoadMask();
				win.getEvnDirectionNumber();
				if (r.success) {
					this.LabRequestEditForm.getForm().findField('EvnDirection_setDT').setValue(r.date);
				}
			}.createDelegate(this)
		});
	},
	openResearchEditWindow: function(button, EvnUslugaPar_id) {
		var win = this;

		getWnd('swResearchEditWindow').show({
			EvnUslugaPar_id: EvnUslugaPar_id,
			callback: function (data) {
				if (data && data.EvnUslugaPar_Comment) {
					Ext.get(win.id + 'EvnLabSample_Comment_text_'+EvnUslugaPar_id).update(data.EvnUslugaPar_Comment);
					Ext.get(win.id + 'EvnLabSample_Comment_text_'+EvnUslugaPar_id).setAttribute('data-qtip', data.EvnUslugaPar_Comment);
					win.setEvnLabSampleCommentMode(EvnUslugaPar_id);
				}
			}
		});
	},
	
	openEvnXmlEditWindow: function(button, EvnUslugaPar_id, EvnXml_id) {
		var win = this,
			params = {
			title: null,
			action: 'edit',
			userMedStaffFact: null,
			EvnClass_id: null,
			XmlType_id: null,
			UslugaComplex_id: null,
			EvnXml_id: null,
			Evn_id: null,
			onBlur: function() {},
			onHide: function() {
				this.loadLabStydyResultDoc(EvnUslugaPar_id);
			}.createDelegate(this)
		};
	
		params.title = 'Результат лабораторного исследования';
		params.action = Ext.isEmpty(EvnXml_id) ? 'add' : 'edit';
		params.EvnClass_id = 47;
		params.Evn_id = EvnUslugaPar_id;
		params.EvnXml_id = EvnXml_id;
		params.XmlType_id = 7;
		params.MedService_id = win.MedService_id;
		
		if (!params.Evn_id) {
			return false;
		}
		var win = getWnd('swEvnXmlEditWindow');
		if (win.isVisible()) {
			win.hide();
		}
		win.show(params);
		return true;
	},
	
	saveEvnLabSampleComment: function(text, EvnUslugaPar_id, callback) {
		var win = this;
		var oldtext = Ext.get(win.id + 'EvnLabSample_Comment_text_'+EvnUslugaPar_id).dom.innerText;

		if(win.els_comment_edit) {
			win.els_comment_edit.remove();
			delete win.els_comment_edit;
		}

		if (oldtext == text) {
			if (callback && typeof callback == 'function') {
				callback();
			}
			return false;
		}

		Ext.get(win.id + 'EvnLabSample_Comment_text_'+EvnUslugaPar_id).update(text);
		Ext.get(win.id + 'EvnLabSample_Comment_text_'+EvnUslugaPar_id).setAttribute('data-qtip', text);
		win.setEvnLabSampleCommentMode(EvnUslugaPar_id);
		
		var params = {
			EvnUslugaPar_id: EvnUslugaPar_id,
			EvnUslugaPar_Comment: text
		};
		
		Ext.Ajax.request({
			failure: function () {
				sw.swMsg.alert('Ошибка', 'Не удалось сохранить комментарий');
			},
			params: params,
			success: function (response) {
				var result = Ext.util.JSON.decode(response.responseText);
				if (result && result.success) {
					if (callback && typeof callback == 'function') {
						callback();
					}
				}
			},
			url: '/?c=EvnLabSample&m=saveComment'
		});
	},
	setEvnLabSampleCommentMode: function(EvnUslugaPar_id) {
		var win = this;
		if (!Ext.get(win.id + 'EvnLabSample_Comment_block_'+EvnUslugaPar_id)) {
			return false;
		}
		var text = Ext.get(win.id + 'EvnLabSample_Comment_text_'+EvnUslugaPar_id).dom.innerText;
		var block = Ext.get(win.id + 'EvnLabSample_Comment_block_'+EvnUslugaPar_id);
		var icon = Ext.get(win.id + 'EvnLabSample_Comment_icon_'+EvnUslugaPar_id);
		block.setVisibilityMode(Ext.Element.DISPLAY);
		if (Ext.isEmpty(text)) {
			block.hide();
			icon.show();
		} else {
			block.show();
			icon.hide();
		}
	},
	editEvnLabSampleComment: function(button, EvnUslugaPar_id, mode) {
		
		var win = this;
		
		if (this.els_comment_edit) {
			win.saveEvnLabSampleComment(Ext.get(win.id + 'EvnLabSample_Comment_input').dom.value, this.els_comment_edit.EvnUslugaPar_id, function () {
				setTimeout(function () {
					win.editEvnLabSampleComment(button, EvnUslugaPar_id, mode);
				}, 70);
			});
			return false;
		}
		
		if (mode == 1) { // 1 - текст примечания закрыт, запуск с иконки
			var btn = Ext.get(win.id + 'EvnLabSample_Comment_block_'+EvnUslugaPar_id);
			Ext.get(win.id + 'EvnLabSample_Comment_block_'+EvnUslugaPar_id).show();
			Ext.get(win.id + 'EvnLabSample_Comment_icon_'+EvnUslugaPar_id).hide();
		} else { // 2 - текст примечания открыт, клик по полю
			var btn = Ext.get(button);
		}
		var text = Ext.get(win.id + 'EvnLabSample_Comment_text_'+EvnUslugaPar_id).dom.innerText;
		
		// никогда так больше не делайте
		// ещё больше костылей
		var v = Ext.get(win.id + 'EvnLabSample_Comment_block_'+EvnUslugaPar_id).parent('.x-grid-group-hd').getTop() - Ext.get(win.id + 'EvnLabSample_Comment_block_'+EvnUslugaPar_id).parent('.x-grid3-body').getTop();
		var x = 22;
		var y = v + 29;

		this.els_comment_edit = Ext.get(win.id + 'EvnLabSample_Comment_block_'+EvnUslugaPar_id).parent('.x-grid-group').createChild({
			html: '<div style="position: absolute; z-index: 9999; left: '+x+'px; top: '+y+'px;">' +
				'<input size="24" autocomplete="off" id="'+win.id+'EvnLabSample_Comment_input" class="x-form-text x-form-field x-form-focus" style="width: 874px;" type="text" value="'+text+'">' +
				'</div>'
		});
		this.els_comment_edit.EvnUslugaPar_id = EvnUslugaPar_id;
		var input = Ext.get(win.id + 'EvnLabSample_Comment_input');
		input.focus(true);
		input.addListener('keydown', function (e, inp) {
			if (e.getKey() == Ext.EventObject.DELETE) {
				if ( e.browserEvent.stopPropagation ) {
					e.browserEvent.stopPropagation();
				}
			}
			if (e.getKey() == Ext.EventObject.ENTER) {
				e.stopEvent();
				win.saveEvnLabSampleComment(inp.value, EvnUslugaPar_id);
			}
			if (e.getKey() == Ext.EventObject.ESC) {
				e.stopEvent();
				inp.value = text;
				win.els_comment_edit.remove();
				delete win.els_comment_edit;
				win.setEvnLabSampleCommentMode(EvnUslugaPar_id);
			}
		});
		input.addListener('blur', function (e, inp) {
			setTimeout(function () {
				if (win.els_comment_edit) {
					win.saveEvnLabSampleComment(inp.value, win.els_comment_edit.EvnUslugaPar_id);
				}
			}, 50);
		});
	},
	loadLabStydyResultDoc: function(EvnUslugaPar_pid) {
		var win = this;
		Ext.Ajax.request({
			url: '/?c=EvnUslugaPar&m=getLabStydyResultDoc',
			params: {
				EvnUslugaPar_id: EvnUslugaPar_pid
			},
			failure: function(response, options) {
			},
			success: function(response, action) {
				if (response && response.responseText) {
					var result = Ext.util.JSON.decode(response.responseText);
					if (result && result.length) {
						var text = '';
						for (i = 1; i <= result.length; i++) {
							text += '<span id="EvnLabSample_EvnXml_text_'+result[(i-1)].EvnXml_id+'">';
							text += '<a class="editResearchLink" href="#" style="color:#000;" onClick="Ext.getCmp(\''+win.id+'\').openEvnXmlEditWindow(this, \''+EvnUslugaPar_pid+'\', \''+result[(i-1)].EvnXml_id+'\');">Результат исследования '+i+'</a> ';
							text += '<a class="editResearchLink" href="#" style="color:#000;" onClick="Ext.getCmp(\''+win.id+'\').deleteEvnXml(this, \''+result[(i-1)].EvnXml_id+'\');">Удалить</a><br>';
							text += '</span>';
						}
						Ext.get(win.id + 'EvnLabSample_EvnXml_text_' + EvnUslugaPar_pid).update(text);
					}
				}
			}
		});
	},
	loadLabFileList: function(EvnUslugaPar_pid) {
		var win = this;
		Ext.Ajax.request({
			url: '/?c=EvnMediaFiles&m=loadEvnMediaFilesListGrid',
			params: {
				Evn_id: EvnUslugaPar_pid
			},
			failure: function(response, options) {
			},
			success: function(response, action) {
				if (response && response.responseText) {
					var result = Ext.util.JSON.decode(response.responseText);
					if (result && result.length) {
						var text = '';
						win.labSampleFiles[EvnUslugaPar_pid] = {};
						win.labSampleFiles[EvnUslugaPar_pid].files= [];
						for (var i = 0; i < result.length; i++) {
							win.labSampleFiles[EvnUslugaPar_pid].files[i] = {
								file_name: result[i].EvnMediaData_FilePath,
								orig_name: result[i].EvnMediaData_FileName,
								description: result[i].EvnMediaData_Comment,
								EvnMediaData_id: result[i].EvnMediaData_id
							};

							text += '<span id="EvnSampleFile_'+result[i].EvnMediaData_id+'">';
							text += '<a class="editResearchLink" target="_blank" href="/uploads/evnmedia/'+ result[i].EvnMediaData_FilePath +'" style="color:#000;margin-right:5px;">Файл '+(i + 1)+': '+ result[i].EvnMediaData_FileName +' </a> ';
							text += '<a class="editResearchLink" href="#" style="color:#000;" onClick="Ext.getCmp(\''+win.id+'\').EvnLabSample_deleteFile('+ EvnUslugaPar_pid +', '+ result[i].EvnMediaData_id +', true);">Удалить</a><br>';
							text += '</span>';
						}
						win.labSampleFiles[EvnUslugaPar_pid].fileIndex = result.length - 1;
						Ext.get(win.id + 'EvnLabSample_File_text_'+EvnUslugaPar_pid).update(text);
					}
				}
			}
		});
	},
	deleteEvnXml: function(button, EvnXml_id) {
		sw.swMsg.show({
			buttons:Ext.Msg.YESNO,
			fn:function (buttonId, text, obj) {
				if (buttonId == 'yes') {
					Ext.Ajax.request({
						url: '/?c=EvnXml&m=destroy',
						callback: function(opt, success, response) {
							if (success && response.responseText != '') {
								Ext.get('EvnLabSample_EvnXml_text_'+EvnXml_id).remove();
							}
						},
						params: {EvnXml_id: EvnXml_id}
					});
				}
			},
			icon:Ext.MessageBox.QUESTION,
			msg:langs('Удалить документ?'),
			title:langs('Вопрос')
		});
	},
	filterPrescrTests: function(EvnUslugaDataGrid) {
		if (EvnUslugaDataGrid.showOnlyPrescr) {
			EvnUslugaDataGrid.getGrid().getStore().filterBy(function(record) {
				if (record.get('UslugaTest_Status') != langs('Не назначен')) { return true; } else { return false; }
			});
		} else {
			EvnUslugaDataGrid.getGrid().getStore().filterBy(function(record) {
				return true;
			});
		}

		// можно сделать динамическую высоту окна с максимальным ограничением 15-20 записей
		var count = EvnUslugaDataGrid.getGrid().getStore().getCount();
		if (count > 15) {
			count = 15;
		}
		if (count < 1) {
			count = 1;
		}

		var groupcount = EvnUslugaDataGrid.getGrid().getEl().query(".x-grid-group").length;

		EvnUslugaDataGrid.setHeight(120+count*21+groupcount*21);
	},
	transferTest: function(EvnUslugaDataGrid, labsample, frameId) {
		var win = this;
		if (win.action == 'view') {
			return false;
		}

		var records = EvnUslugaDataGrid.getGrid().getSelectionModel().getSelections();
		var tests = [];
		for (var i = 0; i < records.length; i++) {
			if (!Ext.isEmpty(records[i].get('UslugaComplex_id'))) {
				tests.push({
					UslugaTest_pid: records[i].get('UslugaTest_pid').toString(),
					UslugaComplex_id: records[i].get('UslugaComplex_id').toString()
				});
			}
		}

		if (!Ext.isEmpty(tests) && tests.length > 0) {
			// создаём новую пробу
			if (!frameId) {
				var panel = win.addNewLabSampleFrame();
				var frameId = panel.frameId;

				// заполняем поле служба той же слжубой, что и в прежней пробе
				win.findById(win.id + '_LabSampleMedServiceCombo_' + frameId).setValue(win.findById(win.id + '_LabSampleMedServiceCombo_' + labsample.frameId).getValue());
				win.findById(win.id + '_LabSampleMedServiceCombo_' + frameId).fireEvent('change', win.findById(win.id + '_LabSampleMedServiceCombo_' + frameId), win.findById(win.id + '_LabSampleMedServiceCombo_' + frameId).getValue());

				win.saveEvnLabSample(frameId, function (EvnLabSample_id) {
					var params = {
						tests: Ext.util.JSON.encode(tests),
						EvnLabSample_oldid: win.getEvnLabSampleIdByFrameId(labsample.frameId),
						EvnLabSample_newid: EvnLabSample_id
					};
					// переносим тесты
					win.showLoadMask(langs('Перенос тестов'));
					Ext.Ajax.request({
						failure: function () {
							win.hideLoadMask();
							sw.swMsg.alert(langs('Ошибка при переносе тестов'), langs('Не удалось получить данные с сервера'));
						},
						params: params,
						success: function (response) {
							win.hideLoadMask();
							var result = Ext.util.JSON.decode(response.responseText);
							if (result && result.success) {
								// обновляем гриды
								EvnUslugaDataGrid.getGrid().getStore().reload();

								var params = {
									EvnLabSample_id: EvnLabSample_id
								};

								win.findById(win.id + '_EvnUslugaDataGrid_' + frameId).loadData({
									params: params,
									globalFilters: params,
									noFocusOnLoad: true
								});
							}
						},
						url: '/?c=EvnLabSample&m=transferLabSampleResearches'
					});
				});
			} else {
				var params = {
					tests: Ext.util.JSON.encode(tests),
					EvnLabSample_oldid: win.getEvnLabSampleIdByFrameId(labsample.frameId),
					EvnLabSample_newid: win.getEvnLabSampleIdByFrameId(frameId)
				};
				// переносим тесты
				win.showLoadMask(langs('Перенос тестов'));
				Ext.Ajax.request({
					failure: function () {
						win.hideLoadMask();
						sw.swMsg.alert(langs('Ошибка при переносе тестов'), langs('Не удалось получить данные с сервера'));
					},
					params: params,
					success: function (response) {
						win.hideLoadMask();
						var result = Ext.util.JSON.decode(response.responseText);
						if (result && result.success) {
							// обновляем гриды
							EvnUslugaDataGrid.getGrid().getStore().reload();

							var params = {
								EvnLabSample_id: win.getEvnLabSampleIdByFrameId(frameId)
							};

							win.findById(win.id + '_EvnUslugaDataGrid_' + frameId).loadData({
								params: params,
								globalFilters: params,
								noFocusOnLoad: true
							});
						}
					},
					url: '/?c=EvnLabSample&m=transferLabSampleResearches'
				});
			}
		}
	},
	checkPayType: function() {
		var win = this;
		var base_form = win.LabRequestEditForm.getForm();
		win.findById(win.id + '_PayTypeWarn').hide();
			if (!Ext.isEmpty(base_form.findField('EDPayType_id').getValue())
			&& base_form.findField('PayType_id').getValue() != base_form.findField('EDPayType_id').getValue()
			&& base_form.findField('EvnDirection_IsReceive').getValue() != 2) {
			// воскл. знак
			win.findById(win.id + '_PayTypeWarn').show();
		}

		if (getRegionNick() == 'ekb' || (getRegionNick() == 'kareliya' && !Ext.isEmpty(base_form.findField('PayType_id').getValue()) && base_form.findField('PayType_id').getFieldValue('PayType_SysNick') == 'oms')) {
			base_form.findField('Diag_id').setAllowBlank(false);
		} else {
			base_form.findField('Diag_id').setAllowBlank(true);
		}
	},
	addWithoutRegIsAllowed: function() {
		//console.log('addWithoutRegIsAllowed!');
		var win = this;
		if	(	(getGlobalOptions().CurMedServiceType_SysNick == 'lab' ||
				getGlobalOptions().CurMedServiceType_SysNick == 'pzm' ||
				getGlobalOptions().CurMedServiceType_SysNick == 'reglab') &&
				win.MedServiceMedPersonal_isNotWithoutRegRights
		) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
				},
				icon: Ext.Msg.WARNING,
				msg: langs('Добавление запрещено'),
				title: langs('Недостаточно прав')
			});
			return 0;
		} else return 1;
	},
	approveIsAllowed: function() {//Проверка прав на "Одобрение"
		var win = this;
		//console.log('approveIsAllowed:'+win.MedServiceMedPersonal_isNotApproveRights);
		if (win.MedServiceMedPersonal_isNotApproveRights) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
				},
				icon: Ext.Msg.WARNING,
				msg: langs('Одобрение запрещено'),
				title: langs('Недостаточно прав')
			});
			return 0;
		} else return 1;
	},
	setTumorStageVisibility: function() {
		var base_form = this.LabRequestEditForm.getForm();

		var
			dateX20180601 = new Date(2018, 5, 1),
			Diag_Code = base_form.findField('Diag_id').getFieldValue('Diag_Code'),
			EvnDirection_setDT = base_form.findField('EvnDirection_setDT').getValue();

		if (
			getRegionNick() == 'ekb'
			&& !Ext.isEmpty(Diag_Code) && ((Diag_Code.slice(0, 3) >= 'C00' && Diag_Code.slice(0, 5) <= 'C80.9') || Diag_Code.slice(0,3) == 'C97')
			&& typeof EvnDirection_setDT == 'object' && EvnDirection_setDT < dateX20180601
		) {
			base_form.findField('TumorStage_id').setContainerVisible(true);
			base_form.findField('TumorStage_id').setAllowBlank(false);
		}
		else {
			base_form.findField('TumorStage_id').setContainerVisible(false);
			base_form.findField('TumorStage_id').setAllowBlank(true);
			base_form.findField('TumorStage_id').clearValue();
		}
	},
	delDocsView: false,
	show:function () {
		//console.log("swEvnLabRequestEditWindow:");
		//console.log('medpersonal_id: '+getGlobalOptions().medpersonal_id);
		//console.log('MedService_id: '+arguments[0].MedService_id);

		var win = this;
		win.medServStore.baseParams.MedService_id = arguments[0].MedService_id;
		win.medServStore.load();
		win.PrescrLimitPanel.collapse();
		win.labSampleFiles = {}; //создаём объект для хранения информации о добавленных файлах

		//получаем права пользователя на одобрение заявок/проб
		Ext.Ajax.request({
			url: '/?c=MedService&m=getApproveRights',
			params:{
				MedPersonal_id: getGlobalOptions().medpersonal_id,
				MedService_id: arguments[0].MedService_id,
				armMode: 'Lis'
			},
			callback: function(opt, success, response) {
				if (success && response.responseText != '') {
					var result = Ext.util.JSON.decode(response.responseText);
					//console.log('getApproveRights:'); console.log(result[0]);
					win.MedServiceMedPersonal_isNotApproveRights = result[0].MedServiceMedPersonal_isNotApproveRights;
					win.MedServiceMedPersonal_isNotWithoutRegRights = result[0].MedServiceMedPersonal_isNotWithoutRegRights;
				}
			}
		});
		
		sw.Promed.swEvnLabRequestEditWindow.superclass.show.apply(this, arguments);
		
		this.formStatus = 'edit';
		this.action = '';
		this.onHide = Ext.emptyFn;
		this.ARMType = 'lab';
		this.callback = Ext.emptyFn;
		this.EvnDirection_id = null;
		this.EvnLabRequest_id = null;
		this.MedService_id = null;
		this.swAssistantWorkPlaceWindow = null;
		this.hideComment();
		
		if (!arguments[0]) {
			sw.swMsg.alert(langs('Ошибка'), langs('Не указаны входные данные'), function () {
				this.hide();
			}.createDelegate(this));
			return false;
		}
		if (arguments[0].action) {
			this.action = arguments[0].action;
		}
		if ( arguments[0].onHide ) {
			this.onHide = arguments[0].onHide;
		}
		if (arguments[0].ARMType) {
			this.ARMType = arguments[0].ARMType;
		}
		if (arguments[0].callback && typeof arguments[0].callback == 'function') {
			this.callback = arguments[0].callback;
		}
		if (arguments[0].EvnLabRequest_id) {
			this.EvnLabRequest_id = arguments[0].EvnLabRequest_id;
		}
		if (arguments[0].EvnDirection_id) {
			this.EvnDirection_id = arguments[0].EvnDirection_id;
		}
		if (arguments[0].MedService_id) {
			this.MedService_id = arguments[0].MedService_id;
		}
		if (arguments[0].OuterKzDirection) {
			this.OuterKzDirection = arguments[0].OuterKzDirection;
		}
		if (arguments[0].MedStaffFact_id) {
			this.MedStaffFact_id = arguments[0].MedStaffFact_id;
		}
		if (arguments[0].ExtDirection) {
			this.ExtDirection = arguments[0].ExtDirection;
		}
		if (arguments[0].swAssistantWorkPlaceWindow) {
			this.swAssistantWorkPlaceWindow = arguments[0].swAssistantWorkPlaceWindow;
		}
		if (arguments[0].EvnLabRequest_BarCode) {
			this.EvnLabRequest_BarCode = arguments[0].EvnLabRequest_BarCode;
		} else {
			this.EvnLabRequest_BarCode = null;
		}
		if (arguments[0].electronicQueueData) {
			this.electronicQueueData = arguments[0].electronicQueueData;
		}
		if (arguments[0].ElectronicTalonStatus_id) {
			this.ElectronicTalonStatus_id = arguments[0].ElectronicTalonStatus_id;
		}
		if (arguments[0].userMedStaffFact) {
			this.userMedStaffFact = arguments[0].userMedStaffFact;
		}
		if (arguments[0].delDocsView) {
			this.delDocsView = arguments[0].delDocsView;
		}
		this.params.Person_id = !Ext.isEmpty(arguments[0].Person_id) ? arguments[0].Person_id : null;

        //Для печати на принтере Zebra
		if (arguments[0].Person_ShortFio) { 
        this.Person_ShortFio = arguments[0].Person_ShortFio;
		} else if (arguments[0].Person_Surname) {
                    var f = arguments[0].Person_Surname.trim();
                    var i = arguments[0].Person_Firname?(arguments[0].Person_Firname.trim().charAt(0) + '.'):'';
                    var o = arguments[0].Person_Secname?(arguments[0].Person_Secname.trim().charAt(0) + '.'):'';
                    this.Person_ShortFio = (f + ' ' + i + o);
                }
		/*if (arguments[0].PrehospDirect_Name) { 
                    this.PrehospDirect_Name = arguments[0].PrehospDirect_Name;
		}   */  
                //
		win.showLoadMask(langs('Подождите, идет загрузка'));

		var base_form = this.LabRequestEditForm.getForm();
		// сбрасываем данные формы
		base_form.reset();
		this.LabSampleFrame.removeAllSamples();

		if (getGlobalOptions().region.nick === 'ufa') Ext.getCmp(win.id + 'EvnDirectionDetail').show();
		else Ext.getCmp(win.id + 'EvnDirectionDetail').hide();

		base_form.findField('MedPersonal_Code').setContainerVisible(getRegionNick() == 'ekb');
		base_form.findField('Mes_id').setContainerVisible(getRegionNick() == 'ekb');
		base_form.findField('Mes_id').lastQuery = 'This query sample that is not will never appear';
		base_form.findField('Mes_id').getStore().removeAll();

		// в MedService_id заявки будем писать текущую службу
		base_form.findField('MedService_id').setValue(this.MedService_id);
		if (this.ARMType == 'pzm') {
			// устанавливаем пункт забора
			base_form.findField('MedService_sid').setValue(this.MedService_id);
		}

		base_form.findField('EvnLabRequest_RegNum').setContainerVisible( win.ARMType.inlist(['lab','microbiolab']) );

		// устанавливаем параметры пришедшие на форму
		base_form.setValues(arguments[0]);
		base_form.findField('UslugaExecutionType_id').hideContainer(); // Для заявок с взятыми пробами в  АРМ лаборанта и АРМ рег. службы лаборатории становится видимым поле «Результат», но это поле не отображается в АРМ пункта забора.

		var prehosp_direct_combo = base_form.findField('PrehospDirect_id');
		var lpu_section_combo = base_form.findField('LpuSection_id');
		var org_combo = base_form.findField('Org_sid');
		lpu_section_combo.getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));

		//Проверка есть ли незакрытые заявки сроком дольше 30 дней
		Ext.Ajax.request({
			params: {
				MedService_id: win.MedService_id
			},
			failure:function () {
				sw.swMsg.alert(langs('Ошибка при запросе просроченных заявок'), langs('Не удалось получить данные с сервера'));
			},
			success:function (response) {
				var result = Ext.util.JSON.decode(response.responseText);
				if (!Ext.isEmpty(result[0]) && !Ext.isEmpty(result[0].count)) {
					showSysMsg(langs('Количество незакрытых заявок с датой создания больше месяца составляет ') + result[0].count + langs(' шт.'), langs('Внимание'));
				}
			},
			url:'/?c=EvnLabSample&m=getOverdueSamples'
		});
		
		win.enableEdit(true);
		win.checkPayType();

		base_form.findField('TumorStage_id').setContainerVisible(false);
		base_form.findField('TumorStage_id').setAllowBlank(true);

		base_form.findField('HIVContingentTypeFRMIS_id').hideContainer();
		base_form.findField('HIVContingentTypeFRMIS_id').disable();
		base_form.findField('CovidContingentType_id').hideContainer();
		base_form.findField('CovidContingentType_id').disable();

		if(win.EvnDirection_id) {
			win.EvnPrescrLimitGrid.setParam('EvnDirection_id', win.EvnDirection_id);
			win.EvnPrescrLimitGrid.loadData();
		}

		switch (this.action) {
			case 'add':
				base_form.findField('PayType_id').setFieldValue('PayType_SysNick', 'oms');
				var person_id = base_form.findField('Person_id').getValue();
				var server_id = base_form.findField('Server_id').getValue();
				if (person_id > 0) {
					this.InformationPanel.load({
						Person_id:person_id,
						Server_id:server_id,
						callback:function () {
							win.InformationPanel.setPersonTitle();
						}
					});
				}

				win.selectPrehospDirect(prehosp_direct_combo, prehosp_direct_combo.getStore().getById(prehosp_direct_combo.getValue()), null, true);
				win.addNewLabSampleFrame();

				if (getRegionNick() == 'kareliya') {
					base_form.findField('Diag_id').getStore().load({
						callback: function() {
							base_form.findField('Diag_id').getStore().each(function(record) {
								if ( record.get('Diag_Code') == 'Z01.8' ) {
									base_form.findField('Diag_id').fireEvent('select', base_form.findField('Diag_id'), record, 0);
									base_form.findField('Diag_id').onChange();
									base_form.findField('Diag_id').setValue(record.id);
								}
							});
						},
						params: { where: "where DiagLevel_id = 4 and Diag_Code = 'Z01.8'" }
					});
				}

				win.hideLoadMask();
				win.getDate();
				if ( getRegionNick() == 'ufa' ) {
					win.setPersonDetailVisible();
				}
				break;

			case 'view':
			case 'edit':
				if (win.action == 'view') {
					win.enableEdit(false);
				}
				Ext.Ajax.request({
					failure:function (response, options) {
						sw.swMsg.alert(langs('Ошибка'), langs('Не удалось получить данные с сервера'));
						win.hideLoadMask();
						win.hide();
					},
					params:{
						EvnDirection_id: win.EvnDirection_id,
						delDocsView: win.delDocsView ? 1 : 0
					},
					success:function (response, options) {
						win.hideLoadMask();
						var result = Ext.util.JSON.decode(response.responseText);
						if (!result[0]) {
							return false;
						}
						base_form.setValues(result[0]);
						if (!Ext.isEmpty(base_form.findField('EvnLabRequest_Comment').getValue())) {
							win.addComment();
						}

						base_form.findField('Mes_id').getStore().removeAll();
						base_form.findField('Mes_id').getStore().baseParams.EvnLabRequest_id = base_form.findField('EvnLabRequest_id').getValue();
						base_form.findField('Mes_id').getStore().baseParams.query = '';

						if (win.ARMType == 'pzm') {
							// устанавливаем пункт забора
							base_form.findField('MedService_sid').setValue(win.MedService_id);
						}
						
						win.selectPrehospDirect(prehosp_direct_combo, prehosp_direct_combo.getStore().getById(prehosp_direct_combo.getValue()), null, true);
						
						var person_id = base_form.findField('Person_id').getValue();
						var server_id = base_form.findField('Server_id').getValue();

						if (person_id > 0) {
							win.InformationPanel.load({
								Person_id:person_id,
								Server_id:server_id,
								callback:function () {
									win.InformationPanel.setPersonTitle();
								}

							});
						}

						//Запрещаем редактирование направления по признаку создания направления принимающей стороной
						// т.е. например если создали направление в поликлинике, то не разрешаем редактировать, если создал сам лаборант то разрешаем.
						var IsReceive = parseInt(base_form.findField('EvnDirection_IsReceive').getValue());
						if (IsReceive && IsReceive !== 2) {
							win.disableEvnDirectionForm();
						}

						if (!Ext.isEmpty(base_form.findField('Diag_id').getValue())) {
							var diag_id = base_form.findField('Diag_id').getValue();
							base_form.findField('Diag_id').getStore().load({
								callback: function() {
									base_form.findField('Diag_id').getStore().each(function(record) {
										if ( record.get('Diag_id') == diag_id ) {
											base_form.findField('Diag_id').setValue(diag_id);
											base_form.findField('Diag_id').fireEvent('select', base_form.findField('Diag_id'), record, 0);
											base_form.findField('Diag_id').onChange();
										}
									});
								},
								params: { where: "where DiagLevel_id = 4 and Diag_id = " + diag_id }
							});
						} else if (getRegionNick() == 'kareliya') {
							base_form.findField('Diag_id').getStore().load({
								callback: function() {
									base_form.findField('Diag_id').getStore().each(function(record) {
										if ( record.get('Diag_Code') == 'Z01.8' ) {
											base_form.findField('Diag_id').fireEvent('select', base_form.findField('Diag_id'), record, 0);
											base_form.findField('Diag_id').onChange();
											base_form.findField('Diag_id').setValue(record.id);
										}
									});
								},
								params: { where: "where DiagLevel_id = 4 and Diag_Code = 'Z01.8'" }
							});
						}

						if (!Ext.isEmpty(base_form.findField('Mes_id').getValue())) {
							var mes_id = base_form.findField('Mes_id').getValue();
							base_form.findField('Mes_id').clearValue();
							base_form.findField('Mes_id').getStore().load({
								params: {
									Mes_id: mes_id
								},
								callback: function () {
									if (base_form.findField('Mes_id').getStore().getCount() > 0) {
										base_form.findField('Mes_id').setValue(mes_id);
										base_form.findField('Mes_id').fireEvent('change', base_form.findField('Mes_id'), base_form.findField('Mes_id').getValue());
									}
								}
							});
						}

						// PROMEDWEB-9500 - подстановка зачения в поле "Кем направлен" при внешнем направлении
						if(win.ExtDirection && Org_sid == getGlobalOptions().org_id && !base_form.findField('PrehospDirect_id').getValue() ){
							base_form.findField('PrehospDirect_id').setValue(1);
						}else if(win.ExtDirection && !base_form.findField('PrehospDirect_id').getValue() && Org_sid != getGlobalOptions().org_id){
							base_form.findField('PrehospDirect_id').setValue(2);
						}
						
						if (Ext.isEmpty(base_form.findField('PayType_id').getValue()) && getRegionNick() == 'kz') {
							base_form.findField('PayType_id').setValue(base_form.findField('EDPayType_id').getValue())
						}

						win.LabSampleFrame.loadSamples({
							EvnLabRequest_id: base_form.findField('EvnLabRequest_id').getValue()
						});

						win.checkPayType();
						if ( getRegionNick() == 'ufa' ) {
							win.setPersonDetailVisible();
						}
					},
					url:'/?c=EvnLabRequest&m=load'
				});
				break;
		}

		this.ElectronicQueuePanel.initElectronicQueue();

		if (this.electronicQueueData
			&& this.electronicQueueData.electronicTalonStatus_id
			&& this.electronicQueueData.electronicTalonStatus_id < 4) {
			win.btnsPanel.hide(); this.ElectronicQueuePanel.show(); win.doLayout(); win.syncSize();
		} else {
			win.btnsPanel.show(); this.ElectronicQueuePanel.hide(); win.doLayout(); win.syncSize();
		}

		if(getRegionNick() == 'ufa') {
			this.loadEvnDirectionPersonDetails()
			.then((personDetails) => {
				for (var i in personDetails)
					if (win.params.hasOwnProperty(i)) win.params[i] = personDetails[i];
				win.setValueToHidden();
			});
		}
		win.btnsPanel.buttons[0].setVisible(!this.delDocsView);
	},
	approveResults: function(params) {
		var win = this;
		var base_form = win.LabRequestEditForm.getForm();
		if (Ext.util.JSON.decode(params.UslugaTest_ids).length == 0) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.WARNING,
				msg: "Отсутствуют тесты для одобрения",
				title: langs('Одобрение результатов')
			});
			return;
		}
		win.getLoadMask(langs('Одобрение результатов')).show();
		Ext.Ajax.request({
			url: '/?c=EvnLabSample&m=approveResults',
			params: params,
			failure: function(response, options) {
				win.hideLoadMask();
			},
			success: function(response, action) {
				win.hideLoadMask();
				var eu_params = {
					EvnDirection_id: base_form.findField('EvnDirection_id').getValue(),
					EvnLabSample_id: params.EvnLabSample_id
				};
				
				win.LabSamplesStore.reload();
				// Меняем значение поля UslugaExecutionType_id
				if (response && response.responseText) {
					var result = Ext.util.JSON.decode(response.responseText);
					if (result && result.UslugaExecutionType_id) {
						win.LabRequestEditForm.getForm().findField('UslugaExecutionType_id').setValue(result.UslugaExecutionType_id);
					}
				}
			}
		});
	},
	loadEvnDirectionPersonDetails: function () {
		var scope = this;
		return new Promise(function (resolve, reject) {
			var requestParams = {
				callback: function (options, success, response) {
					if (success) {
						var response_obj = Ext.util.JSON.decode(response.responseText);
						resolve(response_obj);
					} else {
						throw new Error('Ошибка при загрузке сигнальной информации');
					}
				},
				params: {
					Person_id: scope.params.Person_id
				},
				url: '/?c=PersonDetailEvnDirection&m=getOne'
			};
			Ext.Ajax.request(requestParams);
		});
	},
	loadUslugaComplexList: function () {
		var scope = this;
		return new Promise(function (resolve, reject) {
			var baseForm = scope.LabRequestEditForm.getForm();
			var EvnLabRequest_id = baseForm.findField('EvnLabRequest_id').getValue();
			if (!EvnLabRequest_id) {
				resolve([]);
			} else {
				var requestParams = {
					callback: function (options, success, response) {
						if (success) {
							var response_obj = Ext.util.JSON.decode(response.responseText);
							var UslugaComplexList = [];
							for (var i = 0; i < response_obj.length; i++) UslugaComplexList.push(response_obj[i].UslugaComplex_id);
							resolve(UslugaComplexList);
						} else {
							throw new Error('Ошибка при загрузке состава заявки');
						}
					},
					params: {
						EvnLabRequest_id: EvnLabRequest_id
					},
					url: '/?c=EvnLabRequest&m=getUslugaComplexList'
				};
				Ext.Ajax.request(requestParams);
			}
		});
	},
	loadUslugaComplexDetails: function () {
		var scope = this;
		return new Promise(function (resolve, reject) {
			var baseForm = scope.LabRequestEditForm.getForm();
			var EvnLabRequest_id = baseForm.findField('EvnLabRequest_id').getValue();
			if (!EvnLabRequest_id) {
				resolve([]);
			} else {
				var requestParams = {
					callback: function (options, success, response) {
						if (success) {
							var response_obj = Ext.util.JSON.decode(response.responseText);
							resolve(response_obj);
						} else {
							throw new Error('Ошибка при загрузке информации об атрибутах комплексной услиги');
						}
					},
					params: {
						uslugaComplexList: Ext.util.JSON.encode(scope.UslugaComplexList),
						UslugaComplex_id: 1 // заглушка
					},
					url: '/?c=UslugaComplex&m=loadUslugaComplexAttributeGrid'
				};
				Ext.Ajax.request(requestParams);
			}
		});
	},
	setPersonDetailVisible: function () {
    	var scope = this;
		this.loadUslugaComplexList()
			.then((UslugaComplexList) => {
				scope.UslugaComplexList = UslugaComplexList;
				return scope.loadUslugaComplexDetails();
			})
			.then((UslugaComplexAttributeList) => {
				scope.UslugaComplexAttributeList = UslugaComplexAttributeList;
				scope._processPersonDetailVisible();
			});
	},
	_processPersonDetailVisible: function () {
		var baseForm = this.formPanel.getForm();
		var hiddenCount = 0;

		var isUfa = getGlobalOptions().region.nick === 'ufa';
		var isLab = checkUslugaAttribute(8, this.UslugaComplexAttributeList);
		var isContingentReq = checkUslugaAttribute(224, this.UslugaComplexAttributeList);
		var isContingentCovid = isUfa && isLab && checkUslugaAttribute(227, this.UslugaComplexAttributeList);
		if (this.action != 'add') {
			isContingentReq &= (baseForm.findField('pmUser_insID').getValue() == getGlobalOptions().pmuser_id || isUserGroup('hivresearch'));
		}

		var RaceType_FS = Ext.getCmp(this.id + 'RaceType_FS');
		if (!isUfa || !isLab) {
			RaceType_FS.hide();
			hiddenCount++;
		} else {
			RaceType_FS.show();
			Ext.getCmp(this.id + 'RaceTypeAddBtn').setDisabled(!Ext.isEmpty(baseForm.findField('RaceType_id').getValue()));
		}
		var HIVContingentTypeFRMIS_id = baseForm.findField('HIVContingentTypeFRMIS_id');
		if (!isUfa || !isLab || !isContingentReq) {
			HIVContingentTypeFRMIS_id.setDisabled(true);
			HIVContingentTypeFRMIS_id.hideContainer();
			hiddenCount++;
		} else {
			HIVContingentTypeFRMIS_id.setDisabled(false);
			HIVContingentTypeFRMIS_id.showContainer();
		}
		var CovidContingentTypeField = baseForm.findField('CovidContingentType_id');
		CovidContingentTypeField.setDisabled(!isContingentCovid || CovidContingentTypeField.getValue());
		CovidContingentTypeField.setContainerVisible(isContingentCovid);
		if( !isContingentCovid ) CovidContingentTypeField.clearValue();

		var HormonalPhaseType_id = baseForm.findField('HormonalPhaseType_id');
		if (!isUfa || !isLab || !(this.params.Sex_id == 2)) {
			HormonalPhaseType_id.hideContainer();
			hiddenCount++;
		} else HormonalPhaseType_id.showContainer();
		var PersonHeight_FS = Ext.getCmp(this.id + 'PersonHeight_FS');
		var PersonWeight_FS = Ext.getCmp(this.id + 'PersonWeight_FS');
		if (!isUfa || !isLab) {
			PersonHeight_FS.hide();
			PersonWeight_FS.hide();
			hiddenCount += 2;
		} else {
			PersonHeight_FS.show();
			PersonWeight_FS.show();
		}
		if (hiddenCount == 5) Ext.getCmp(this.id + 'EvnDirectionDetail').hide();
		else Ext.getCmp(this.id + 'EvnDirectionDetail').show();
	},
	disableEvnDirectionForm: function () {
		var base_form = this.LabRequestEditForm.getForm();
		
		base_form.findField('EvnDirection_Num').disable();
		if (getRegionNick() != 'ekb') { // для Екб должно быть доступно изменение поля диагноз
			base_form.findField('Diag_id').disable();
		}
		base_form.findField('EvnDirection_setDT').disable();
		base_form.findField('PrehospDirect_id').disable();
		base_form.findField('Org_sid').disable();
		base_form.findField('LpuSection_id').disable();
		base_form.findField('EvnLabRequest_Ward').disable();
		base_form.findField('MedStaffFact_id').disable();
		base_form.findField('MedPersonal_Code').disable();
		base_form.findField('EvnDirection_IsCito').disable();
		base_form.findField('EvnDirection_Descr').disable();
	},
	disableEDOtherFields: function () {
		var base_form = this.LabRequestEditForm.getForm();

		base_form.findField('PayType_id').disable();
		base_form.findField('EvnLabRequest_RegNum').disable();
		Ext.getCmp(this.id + 'EvnDirectionDetail').setDisabled(true);
	}
});
