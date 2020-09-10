/**
* swEvnDiagPLStomEditWindow - окно редактирования/добавления стоматологического диагноза.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Polka
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Stas Bykov aka Savage (savage@swan.perm.ru)
* @version      0.002-15.08.2010
* @comment      Префикс для id компонентов EDPLStomEF (EvnDiagPLStomEditForm)
*
*
* @input data: action - действие (add, edit, view)
*              EvnDiagPLStom_pid - ID родительского события
*              Person_id - ID человека
*              PersonEvn_id - ID состояния человека
*              Server_id - ID сервера
*/

sw.Promed.swEvnDiagPLStomEditWindow = Ext.extend(sw.Promed.BaseForm, {
	action: null,
	baseTitle: WND_POL_EDPLS_ALT,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	deleteEvent: function(event) {
		var base_form = this.FormPanel.getForm();

		if ( this.action == 'view' || base_form.findField('EvnDiagPLStom_IsClosed').getValue() ) {
			return false;
		}

		if ( Ext.isEmpty(event) || !event.inlist([ 'EvnDiagPLStomSop', 'EvnUsluga' ]) ) {
			return false;
		}

		var
			error = '',
			grid,
			question = '',
			params = new Object(),
			url = '';

		switch ( event ) {
			case 'EvnDiagPLStomSop':
				error = 'При удалении сопутствующего диагноза возникли ошибки';
				grid = this.findById('EDPLSEF_EvnDiagPLStomSopGrid');
				question = 'Удалить сопутствующий диагноз?';
				url = '/?c=EvnDiag&m=deleteEvnDiag';
			break;

			case 'EvnUsluga':
				error = 'При удалении услуги возникли ошибки';
				grid = this.findById('EDPLSEF_EvnUslugaStomGrid');
				question = 'Удалить услугу?';
				url = '/?c=EvnUsluga&m=deleteEvnUsluga';
			break;
		}

		if ( !grid || !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get(event + '_id') ) {
			return false;
		}

		var selected_record = grid.getSelectionModel().getSelected();

		if ( event == 'EvnUsluga' && selected_record.get('EvnUsluga_pid') != this.evnVizitData.EvnVizitPLStom_id ) {
			return false;
		}

		switch ( event ) {
			case 'EvnDiagPLStomSop':
				params['class'] = 'EvnDiagPLStomSop';
				params['id'] = selected_record.get('EvnDiagPLStomSop_id');
			break;

			case 'EvnUsluga':
				params['class'] = 'EvnUslugaStom';
				params['id'] = selected_record.get('EvnUsluga_id');
			break;
		}

		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId, text, obj) {
				if ( buttonId == 'yes' ) {
					var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Удаление записи..." });
					loadMask.show();

					Ext.Ajax.request({
						failure: function(response, options) {
							loadMask.hide();
							sw.swMsg.alert('Ошибка', error);
						},
						params: params,
						success: function(response, options) {
							loadMask.hide();

							var response_obj = Ext.util.JSON.decode(response.responseText);

							if ( response_obj.success == false ) {
								sw.swMsg.alert('Ошибка', response_obj.Error_Msg ? response_obj.Error_Msg : error);
							}
							else {
								grid.getStore().remove(selected_record);

								if ( event == 'EvnUsluga' ) {
									this.EvnUslugaGridIsModified = true;
									this.uetValuesRecount();
								}

								if ( grid.getStore().getCount() == 0 ) {
									grid.getTopToolbar().items.items[1].disable();
									grid.getTopToolbar().items.items[2].disable();
									grid.getTopToolbar().items.items[3].disable();
									LoadEmptyRow(grid);
								}
							}

							grid.getView().focusRow(0);
							grid.getSelectionModel().selectFirstRow();
							this.loadSpecificsTree();
						}.createDelegate(this),
						url: url
					});
				}
				else {
					grid.getView().focusRow(0);
					grid.getSelectionModel().selectFirstRow();
				}
			}.createDelegate(this),
			icon: Ext.MessageBox.QUESTION,
			msg: question,
			title: 'Вопрос'
		});
	},
	doSave: function(options) {
		// options @Object
		// options.isAutoCreate @Boolean Признак автоматического создания заболевания
		// options.openChildWindow @Function Открыть дочернее окно после сохранения

		if ( this.formStatus == 'save' || this.action == 'view' ) {
			return false;
		}

		if ( typeof options != 'object' ) {
			options = new Object();
		}

		this.formStatus = 'save';

		var win = this;
		var form = this.FormPanel;
		var base_form = form.getForm();
		var tree = this.findById('EDPLSEF_SpecificsTree');
		var diag_grid = this.findById('EDPLSEF_EvnDiagPLStomSopGrid');

		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					this.formStatus = 'edit';
					form.getFirstInvalidEl().focus(false);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		var
			desease_type_id = base_form.findField('DeseaseType_id').getValue(),
			diag_code = base_form.findField('Diag_id').getFieldValue('Diag_Code'),
			diag_id = base_form.findField('Diag_id').getValue(),
			diag_name = base_form.findField('Diag_id').getFieldValue('Diag_Name');

		if (
			!Ext.isEmpty(base_form.findField('EvnDiagPLStom_disDate').getValue())
			&& base_form.findField('EvnDiagPLStom_disDate').getValue() < base_form.findField('EvnDiagPLStom_setDate').getValue()
		) {
			this.formStatus = 'edit';
			sw.swMsg.alert('Ошибка', 'Дата окончания не может быть меньше даты начала');
			return false;
		}

		if (
			base_form.findField('Mes_id').getFieldValue('MesOld_IsNeedTooth')
			&& base_form.findField('Mes_id').getFieldValue('MesOld_IsNeedTooth') == 2
			&& Ext.isEmpty(base_form.findField('Tooth_Code').getValue())
		) {
			this.formStatus = 'edit';
			sw.swMsg.alert('Ошибка', 'Для выбранной КСГ обязательно указание номера зуба.');
			return false;
		}

		if ( getRegionNick().inlist(['ekb']) ) {
			var index = win.findById('EDPLSEF_EvnUslugaStomGrid').getStore().findBy(function(rec) {
				return (!Ext.isEmpty(rec.get('Usluga_Code')) && rec.get('Usluga_Code').inlist(win.ksgStomUslugaList));
			});

			if ( index >= 0 ) {
				if ( Ext.isEmpty(base_form.findField('EvnDiagPLStom_KPU').getValue()) ) {
					win.formStatus = 'edit';
					sw.swMsg.alert('Ошибка', 'Поле «Индекс КПУ» обязательно к заполнению');
					return false;
				}
				else if ( Ext.isEmpty(base_form.findField('EvnDiagPLStom_CarriesTeethCount').getValue()) ) {
					win.formStatus = 'edit';
					sw.swMsg.alert('Ошибка', 'Поле «Количество нелеченых незапломбированных кариозных поражений зубов» обязательно к заполнению');
					return false;
				}
			}

			// к исключениям относятся КСГ 5.1, 5.2, 6.1, 6.2, 7.1, 7.2, 10.1, 10.2, 9.1, 9.2, 20.1, 20.2, 19.2, 19.1 (refs #136871)
			var index = win.findById('EDPLSEF_EvnUslugaStomGrid').getStore().findBy(function(rec) {
				return (!Ext.isEmpty(rec.get('Usluga_Code')) && rec.get('Usluga_Code').inlist(win.ksgStomUslugaList) && !rec.get('Usluga_Code').inlist(['5.1', '5.2', '6.1', '6.2', '7.1', '7.2', '10.1', '10.2', '9.1', '9.2', '20.1', '20.2', '19.2', '19.1']));
			});

			if ( index >= 0 ) {
				if (
					Ext.isEmpty(base_form.findField('ToothSurfaceType_id_list').getValue())
					&& base_form.findField('EvnDiagPLStom_HalfTooth').getValue() == false
				) {
					win.formStatus = 'edit';
					sw.swMsg.alert('Ошибка', 'Одно из полей «Разрушение коронки зуба более 50%» или «Поверхность зуба» обязательно к заполнению');
					return false;
				}
			}
		}
		
		// проверяем, есть ли незаполненные специфики
		var root = tree.getRootNode();
		var isMorbusOnkoBlank = false;
		root.eachChild(function(child) {
			if (child.attributes.id = 'MorbusOnko') {
				child.eachChild(function(cld) {
					if (Ext.isEmpty(cld.attributes.Morbus_id)) {
						isMorbusOnkoBlank = true;
					}
				});
			}
		});
		var diag_code = base_form.findField('Diag_id').getFieldValue('Diag_Code');
		var isSopDiagOnko = false;
		diag_grid.getStore().each(function(rec) {
			if (
				(rec.get('Diag_Code').substr(0, 3).toUpperCase() >= 'C00' && rec.get('Diag_Code').substr(0, 3).toUpperCase() <= 'C97')
				|| (rec.get('Diag_Code').substr(0, 3).toUpperCase() >= 'D00' && rec.get('Diag_Code').substr(0, 3).toUpperCase() <= 'D09')
			) {
				isSopDiagOnko = true;
			}
		});
		
		if( getRegionNick() != 'kz' && base_form.findField('EvnDiagPLStom_IsClosed').getValue() && isMorbusOnkoBlank && (
			(diag_code.substr(0, 3).toUpperCase() >= 'C00' && diag_code.substr(0, 3).toUpperCase() <= 'C97')
			|| (diag_code.substr(0, 3).toUpperCase() >= 'D00' && diag_code.substr(0, 3).toUpperCase() <= 'D09')
			|| isSopDiagOnko
		) && !(
			getRegionNick() == 'krym'
			&& base_form.findField('EvnDiagPLStom_IsZNO').getValue() == true
		)) {
			sw.swMsg.alert('Ошибка', 'В случае лечения установлен диагноз из диапазона С00-C97 или D00-D09. Заполните раздел "Специфика (онкология)" или проверьте корректность заполнения обязательных полей данного раздела. Обязательные поля раздела отмечены символом *.');
			this.findById('EDPLSEF_SpecificsPanel').expand();
			this.formStatus = 'edit';
			return false;
		}

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT_SAVE });
		loadMask.show();

		var params = new Object();

		params.ToothSurfaceType_id_list = base_form.findField('ToothSurfaceType_id_list').getValue();
		params.EvnDiagPLStom_setDate = typeof base_form.findField('EvnDiagPLStom_setDate').getValue() == 'object'?base_form.findField('EvnDiagPLStom_setDate').getValue().format('d.m.Y'):base_form.findField('EvnDiagPLStom_setDate').getValue();
		params.EvnDiagPLStom_disDate = typeof base_form.findField('EvnDiagPLStom_disDate').getValue() == 'object'?base_form.findField('EvnDiagPLStom_disDate').getValue().format('d.m.Y'):base_form.findField('EvnDiagPLStom_disDate').getValue();

		if (base_form.findField('EvnDiagPLStom_IsZNO').disabled && base_form.findField('EvnDiagPLStom_IsZNO').getValue() == true) {
			params.EvnDiagPLStom_IsZNO = 'on';
		}

		if (base_form.findField('Diag_spid').disabled) {
			params.Diag_spid = base_form.findField('Diag_spid').getValue();
		}

		if (base_form.findField('Diag_id').disabled) {
			params.Diag_id = base_form.findField('Diag_id').getValue();
		}

		if (base_form.findField('DeseaseType_id').disabled) {
			params.DeseaseType_id = base_form.findField('DeseaseType_id').getValue();
		}

		if (base_form.findField('Tooth_Code').disabled) {
			params.Tooth_Code = base_form.findField('Tooth_Code').getValue();
		}

		if (base_form.findField('EvnDiagPLStom_KPU').disabled) {
			params.EvnDiagPLStom_KPU = base_form.findField('EvnDiagPLStom_KPU').getValue();
		}

		if (base_form.findField('EvnDiagPLStom_CarriesTeethCount').disabled) {
			params.EvnDiagPLStom_CarriesTeethCount = base_form.findField('EvnDiagPLStom_CarriesTeethCount').getValue();
		}

		if (base_form.findField('EvnDiagPLStom_HalfTooth').disabled && base_form.findField('EvnDiagPLStom_HalfTooth').getValue() == true) {
			params.EvnDiagPLStom_HalfTooth = 'on';
		}

		if (base_form.findField('Mes_id').disabled) {
			params.Mes_id = base_form.findField('Mes_id').getValue();
		}

		if (base_form.findField('PainIntensity_id').disabled) {
			params.PainIntensity_id = base_form.findField('PainIntensity_id').getValue();
		}

		if ( options.isAutoCreate ) {
			params.isAutoCreate = 1;
		}

		if ( options.ignoreEmptyKsg ) {
			params.ignoreEmptyKsg = 1;
		}

		if ( options.ignoreUetSumInNonMorbusCheck ) {
			params.ignoreUetSumInNonMorbusCheck = 1;
		}

		if ( options.ignoreMorbusOnkoDrugCheck ) {
			params.ignoreMorbusOnkoDrugCheck = 1;
		}

		if ( options.ignoreCheckKSGPeriod ) {
			params.ignoreCheckKSGPeriod = 1;
		}

		if ( options.ignoreCheckTNM ) {
			params.ignoreCheckTNM = 1;
		}

		if ( options.ignoreCheckMorbusOnko ) {
			params.ignoreCheckMorbusOnko = 1;
		}

		// передадим список доступных КСГ, чтобы проверить есть ли подходящие.
		var KSGlist = [];
		base_form.findField('Mes_id').getStore().each(function(record) {
			KSGlist.push(record.get('Mes_id'));
		});
		params.KSGlist = Ext.util.JSON.encode(KSGlist);

		params.CurMedStaffFact_id = getGlobalOptions().CurMedStaffFact_id;//yl:не факт что есть на сервере

		base_form.submit({
			failure: function(result_form, action) {
				this.formStatus = 'edit';
				loadMask.hide();

				if ( action.result ) {
					if ( action.result.Error_Msg ) {
						sw.swMsg.alert('Ошибка', action.result.Error_Msg);
					}
					else {
						sw.swMsg.alert('Ошибка', 'При сохранении произошли ошибки [Тип ошибки: 3]');
					}
				}
			}.createDelegate(this),
			params: params,
			success: function(result_form, action) {
				this.formStatus = 'edit';
				loadMask.hide();

				if ( action.result && action.result.EvnDiagPLStom_id > 0 ) {
					base_form.findField('EvnDiagPLStom_id').setValue(action.result.EvnDiagPLStom_id);

					if ( options && typeof options.openChildWindow == 'function' ) {
						options.openChildWindow();
					}
					else {
						var data = new Object();

						data.evnDiagPLStomData = [{
							'accessType': 'edit',
							'EvnDiagPLStom_id': base_form.findField('EvnDiagPLStom_id').getValue(),
							'EvnDiagPLStom_pid': base_form.findField('EvnDiagPLStom_pid').getValue(),
							'EvnDiagPLStom_setDate': base_form.findField('EvnDiagPLStom_setDate').getValue(),
							'EvnDiagPLStom_disDate': base_form.findField('EvnDiagPLStom_disDate').getValue(),
							'Tooth_Code': base_form.findField('Tooth_Code').getValue(),
							'Mes_Code': base_form.findField('Mes_id').getFieldValue('Mes_Code'),
							'Mes_Name': base_form.findField('Mes_id').getFieldValue('Mes_Name'),
							'Diag_Code': diag_code,
							'Diag_Name': diag_name
						}];

						this.callback(data);
						this.hide();
					}
				} else if (action.result && action.result.Alert_Msg ) {
					if (action.result.Error_Code && action.result.Error_Code > 0) {
						sw.swMsg.show({
							fn: function(buttonId, text, obj) {
								if ( buttonId == 'yes' ) {
									switch(parseInt(action.result.Error_Code)) {
										case 106:
											options.ignoreMorbusOnkoDrugCheck = true;
											break;
										case 129:
											options.ignoreUetSumInNonMorbusCheck = true;
											break;
										case 130:
											options.ignoreCheckKSGPeriod = true;
											break;
										case 181:
											options.ignoreCheckTNM = true;
											break;
										case 289:
											options.ignoreCheckMorbusOnko = true;
											break;
									}
									win.doSave(options);
								}
							}.createDelegate(this),
							icon: Ext.MessageBox.QUESTION,
							msg: action.result.Alert_Msg,
							title: 'Продолжить сохранение?',
							buttons: {
								yes: {text: langs('Да')},
								no: {text: langs('Нет')}
							}
						});
					}
					else {
						sw.swMsg.show({
							buttons: {
								yes: {text: langs('Выбрать КСГ')},
								no: {text: langs('Сохранить')}
							},
							fn: function ( buttonId ) {
								if ( buttonId == 'no' ) {
									options.ignoreEmptyKsg = true;
									win.doSave(options);
								}
							},
							msg: action.result.Alert_Msg,
							title: 'Подтверждение'
						});
					}
				}
				else {
					sw.swMsg.alert('Ошибка', 'При сохранении произошли ошибки [Тип ошибки: 2]');
				}
			}.createDelegate(this)
		});
	},
	draggable: true,
	enableEdit: function(enable) {
		var base_form = this.FormPanel.getForm();

		if ( enable ) {
			if (
				getRegionNick() == 'krym' 
				|| Ext.isEmpty(base_form.findField('Diag_id').getFieldValue('Diag_Code'))
				|| (
					base_form.findField('Diag_id').getFieldValue('Diag_Code').substr(0, 1) != 'C'
					&& base_form.findField('Diag_id').getFieldValue('Diag_Code').substr(0, 2) != 'D0'
				)
			) {
				base_form.findField('EvnDiagPLStom_IsZNO').enable();
			}
			else {
				base_form.findField('EvnDiagPLStom_IsZNO').disable();
			}

			if ( base_form.findField('EvnDiagPLStom_IsZNO').getValue() == true ) {
				base_form.findField('Diag_spid').enable();
			}
			else {
				base_form.findField('Diag_spid').disable();
			}

			base_form.findField('DeseaseType_id').enable();
			base_form.findField('Diag_id').enable();
			if ( base_form.findField('Mes_id').getStore().getCount() > 0 ) {
				base_form.findField('Mes_id').enable();
			}
			base_form.findField('ToothSurfaceType_id_list').enable();
			base_form.findField('Tooth_Code').enable();
			base_form.findField('EvnDiagPLStom_KPU').enable();
			base_form.findField('EvnDiagPLStom_CarriesTeethCount').enable();
			base_form.findField('EvnDiagPLStom_HalfTooth').enable();
			base_form.findField('PainIntensity_id').enable();

			this.findById('EDPLSEF_EvnDiagPLStomSopGrid').getTopToolbar().items.items[0].enable();
			this.findById('EDPLSEF_EvnUslugaStomGrid').getTopToolbar().items.items[0].enable();

			if (getRegionNick() != 'ekb') {
				var mes_id = base_form.findField('Mes_id').getValue();
				if (this.action != 'view' && !base_form.findField('EvnDiagPLStom_IsClosed').getValue() && mes_id) {
					this.findById('EDPLSEF_EvnUslugaStomGrid').getTopToolbar().items.items[4].enable();
				}
			}

			this.buttons[0].show();
		}
		else {
			base_form.findField('EvnDiagPLStom_IsZNO').disable();
			base_form.findField('Diag_spid').disable();
			base_form.findField('DeseaseType_id').disable();
			base_form.findField('Diag_id').disable();
			base_form.findField('Mes_id').disable();
			base_form.findField('ToothSurfaceType_id_list').disable();
			base_form.findField('Tooth_Code').disable();
			base_form.findField('EvnDiagPLStom_KPU').disable();
			base_form.findField('EvnDiagPLStom_CarriesTeethCount').disable();
			base_form.findField('EvnDiagPLStom_HalfTooth').disable();
			base_form.findField('PainIntensity_id').disable();

			this.findById('EDPLSEF_EvnDiagPLStomSopGrid').getTopToolbar().items.items[0].disable();
			this.findById('EDPLSEF_EvnUslugaStomGrid').getTopToolbar().items.items[0].disable();
			this.findById('EDPLSEF_EvnUslugaStomGrid').getTopToolbar().items.items[4].disable();

			this.buttons[0].hide();
		}

		this.findById('EDPLSEF_EvnDiagPLStomSopGrid').getSelectionModel().fireEvent('rowselect', this.findById('EDPLSEF_EvnDiagPLStomSopGrid').getSelectionModel());
		this.findById('EDPLSEF_EvnUslugaStomGrid').getSelectionModel().fireEvent('rowselect', this.findById('EDPLSEF_EvnUslugaStomGrid').getSelectionModel());
	},
	loadSpecificsTree: function() {
		var tree = this.findById('EDPLSEF_SpecificsTree');
		var root = tree.getRootNode();
		var win = this;
		
		if (win.specLoading) {
			clearTimeout(win.specLoading);
		};
		
		win.specLoading = setTimeout(function() {
			
			var base_form = this.FormPanel.getForm();
			
			var Diag_ids = [];
			if (this.FormPanel.getForm().findField('Diag_id').getValue() && this.FormPanel.getForm().findField('Diag_id').getFieldValue('Diag_Code')) {
				Diag_ids.push([this.FormPanel.getForm().findField('Diag_id').getValue(), 1, this.FormPanel.getForm().findField('Diag_id').getFieldValue('Diag_Code'), '']);
			}
			this.findById('EDPLSEF_EvnDiagPLStomSopGrid').getStore().each(function(record) {
				if(record.get('Diag_id')) {
					Diag_ids.push([record.get('Diag_id'), 0, record.get('Diag_Code'), record.get('EvnDiagPLStomSop_id').toString()]);
				}
			});
			tree.getLoader().baseParams.Diag_ids = Ext.util.JSON.encode(Diag_ids);
			tree.getLoader().baseParams.Person_id = base_form.findField('Person_id').getValue();
			tree.getLoader().baseParams.EvnDiagPLStom_id = base_form.findField('EvnDiagPLStom_id').getValue();
			tree.getLoader().baseParams.allowCreateButton = (this.action != 'view');
			tree.getLoader().baseParams.allowDeleteButton = (this.action != 'view');
			
			if (!root.expanded) {
				root.expand();
			} else {
				var spLoadMask = new Ext.LoadMask(this.getEl(), { msg: "Загрузка специфик..." });
				spLoadMask.show();
				tree.getLoader().load(root, function() {
					spLoadMask.hide();
				});
			}
			
			if (this.findById('EDPLSEF_SpecificsPanel').collapsed) {
				this.findById('EDPLSEF_SpecificsPanel').expand();
				this.findById('EDPLSEF_SpecificsPanel').collapse();
			}
		}.createDelegate(this), 100);
	},
	EvnUslugaGridIsModified: false,
	evnVizitData: {},
	formStatus: 'edit',
	height: 550,
	id: 'EvnDiagPLStomEditWindow',
	checkZNO: function(options){
		if(getRegionNick()!='ekb') return;
		var win = this,
			base_form = win.FormPanel.getForm(),
			person_id = base_form.findField('Person_id'),
			Evn_id = base_form.findField('EvnDiagPLStom_pid');

		var params = new Object();
		params.object = 'EvnDiagPLStom';

		if ( !Ext.isEmpty(person_id.getValue()) ) {
			params.Person_id = person_id.getValue();
		}
		
		if ( !Ext.isEmpty(Evn_id.getValue()) && Evn_id.getValue()!=0 ) {
			params.Evn_id = Evn_id.getValue();
		}
		
		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Проверка признака на подозрение ЗНО..."});
        loadMask.show();
        Ext.Ajax.request({
            callback: function(opts, success, response) {
                loadMask.hide();

                if ( success ) {
                    var data = Ext.util.JSON.decode(response.responseText);
                    win.lastzno = data.iszno;
                    win.lastznodiag = data.Diag_spid;
                    if(win.lastzno==2 && options.action=='add') {
						base_form.findField('EvnDiagPLStom_IsZNO').setValue(true);
						if(!Ext.isEmpty(data.Diag_spid)) {
							base_form.findField('Diag_spid').getStore().load({
								callback:function () {
									base_form.findField('Diag_spid').getStore().each(function (rec) {
										if (rec.get('Diag_id') == data.Diag_spid) {
											base_form.findField('Diag_spid').fireEvent('select', base_form.findField('Diag_spid'), rec, 0);
										}
									});
								},
								params:{where:"where DiagLevel_id = 4 and Diag_id = " + data.Diag_spid}
							});
						}
					}
                }
                else {
                    sw.swMsg.alert('Ошибка', 'Ошибка при определении признака на подозрение ЗНО');
                }
            },
			params: params,
            url: '/?c=Person&m=checkEvnZNO_last'
        });
        
        win.checkBiopsyDate(options.action);
	},
	
	checkBiopsyDate: function(formAction) {
		if(getRegionNick()!='ekb') return;
		var win = this,
			base_form = win.FormPanel.getForm(),
			person_id = base_form.findField('Person_id');
			
		if(base_form.findField('EvnDiagPLStom_IsZNORemove').getValue() == '2') {
			Ext.getCmp('EDPLSEF_BiopsyDatePanel').show();
			if(formAction=='add' && Ext.isEmpty(base_form.findField('EvnDiagPLStom_BiopsyDate').getValue()) ) {
				var params = new Object();
				params.object = 'EvnDiagPLStom';
				params.Person_id = person_id.getValue();
				Ext.Ajax.request({
					url: '/?c=Person&m=getEvnBiopsyDate',
					params: params,
					callback:function (options, success, response) {
						if (success) {
							var response_obj = Ext.util.JSON.decode(response.responseText);
							if (response_obj.success && response_obj.data) {
								base_form.findField('EvnDiagPLStom_BiopsyDate').setValue(response_obj.data);
							}
						}
					}
				});
			}
		} else Ext.getCmp('EDPLSEF_BiopsyDatePanel').hide();
	},
	
	changeZNO: function(options){
		if(getRegionNick()!='ekb') return;
		var win = this,
			base_form = win.FormPanel.getForm(),
			person_id = base_form.findField('Person_id'),
			Evn_id = base_form.findField('EvnDiagPLStom_pid'),
			params = new Object();
		
		params.object = 'EvnDiagPLStom';
		params.Evn_id = Evn_id;
		if(Ext.isEmpty(options.isZNO)) return; else params.isZNO = options.isZNO ? 2 : 1;
		
		base_form.findField('EvnDiagPLStom_IsZNORemove').setValue(options.isZNO ? 1 : 2);
		
		win.checkBiopsyDate( !options.isZNO ? 'add' : '' );
		
		if(!Ext.isEmpty(params.Evn_id) && params.Evn_id>0) {
			var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Запись признака ЗНО..."});
			loadMask.show();
			Ext.Ajax.request({
				url: '/?c=Person&m=changeEvnZNO',
				params: params,
				callback:function (options, success, response) {
					loadMask.hide();

					if (success) {
						var response_obj = Ext.util.JSON.decode(response.responseText);
						if (response_obj.success) {
							
						}
					}
				}
			});
		}
	},
	initComponent: function() {
		var win = this;

		var createKSGField = function(nearDiag) {
			var showNearDiag = getRegionNick().inlist(['vologda']);
			return {
				fieldLabel: 'КСГ',
				hiddenName: (showNearDiag?nearDiag:!nearDiag)?'Mes_id':'Mes_undefined',
				listeners: {
					'change': function (combo, newValue, oldValue) {
						var index = combo.getStore().findBy(function(rec) {
							return (rec.get('Mes_id') == newValue);
						});

						win.checkChangeMesId(combo, newValue, oldValue, true);
						combo.fireEvent('select', combo, combo.getStore().getAt(index));

						return true;
					},
					'select': function (combo, record) {
						var addByMesBtn = win.findById('EDPLSEF_EvnUslugaStomGrid').getTopToolbar().items.items[4];
						var base_form = win.FormPanel.getForm();

						if ( record && typeof record == 'object' ) {
							base_form.findField('EvnDiagPLStom_UetMes').setRawValue(record.get('Mes_KoikoDni'));
						}
						else {
							base_form.findField('EvnDiagPLStom_UetMes').setRawValue('');
						}

						if (getRegionNick() != 'ekb') {
							addByMesBtn.setDisabled(win.action == 'view' || base_form.findField('EvnDiagPLStom_IsClosed').getValue() || !combo.getValue());
						}

						return true;
					}
				},
				mode: 'local',
				tabIndex: win.tabIndexBase++,
				width: 450,
				listWidth: 600,
				xtype: 'swmescombo'
			};
		};

		this.FormPanel = new Ext.form.FormPanel({
			autoScroll: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			border: false,
			buttonAlign: 'left',
			frame: false,
			id: 'EvnDiagPLStomEditForm',
			labelAlign: 'right',
			labelWidth: 170,
			keys: [{
				alt: true,
				fn: function(inp, e) {
					switch ( e.getKey() ) {
						case Ext.EventObject.C:
							if ( this.action != 'view' ) {
								this.doSave();
							}
						break;

						case Ext.EventObject.J:
							this.onCancelAction();
						break;
					}
				},
				key: [ Ext.EventObject.C, Ext.EventObject.J ],
				scope: this,
				stopEvent: true
			}],
			reader: new Ext.data.JsonReader({
				success: function() { }
			}, [
				{ name: 'accessType' },
				{ name: 'EvnDiagPLStom_id' },
				{ name: 'EvnDiagPLStom_rid' },
				{ name: 'EvnDiagPLStom_pid' },
				{ name: 'EvnVizitPLStom_setDate' },
				{ name: 'Person_id' },
				{ name: 'PersonEvn_id' },
				{ name: 'Server_id' },
				{ name: 'Diag_id' },
				{ name: 'Diag_spid' },
				{ name: 'DeseaseType_id' },
				{ name: 'EvnDiagPLStom_setDate' },
				{ name: 'EvnDiagPLStom_disDate' },
				{ name: 'EvnDiagPLStom_IsClosed' },
				{ name: 'EvnDiagPLStom_IsZNO' },
				{ name: 'EvnDiagPLStom_IsZNORemove' },
				{ name: 'EvnDiagPLStom_HalfTooth' },
				{ name: 'LpuSection_id' },
				{ name: 'MedStaffFact_id' },
				{ name: 'MedPersonal_id' },
				{ name: 'Mes_id' },
				{ name: 'PainIntensity_id' },
				{ name: 'EvnDiagPLStom_KSKP' },
				{ name: 'Tooth_Code' },
				{ name: 'ToothSurfaceType_id_list' },
				{ name: 'Tooth_id' },
				{ name: 'EvnDiagPLStom_KPU'},
				{ name: 'EvnDiagPLStom_CarriesTeethCount'},
				{ name: 'BlackClass_id'}
			]),
			region: 'center',
			url: '/?c=EvnDiagPLStom&m=saveEvnDiagPLStom',

			items: [{
				name: 'accessType',
				value: '',
				xtype: 'hidden'
			}, {
				name: 'EvnDiagPLStom_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'EvnDiagPLStom_pid',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'EvnDiagPLStom_rid',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'EvnVizitPLStom_setDate', // дата начала посещения, из которого добавлено заболевание
				xtype: 'hidden'
			}, {
				name: 'Person_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'PersonEvn_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'Server_id',
				value: -1,
				xtype: 'hidden'
			}, {
				name: 'LpuSection_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'MedStaffFact_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'MedPersonal_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'EvnDiagPLStom_IsZNORemove',
				xtype: 'hidden'
			}, {
				allowBlank: false,
				disabled: true,
				fieldLabel: 'Дата установки',
				format: 'd.m.Y',
				name: 'EvnDiagPLStom_setDate',
				listeners: {
					'change': function(field, newValue, oldValue) {
						blockedDateAfterPersonDeath('personpanelid', 'EDPLStomEF_PersonInformationFrame', field, newValue, oldValue);

						var base_form = this.FormPanel.getForm();

						if ( !Ext.isEmpty(newValue) ) {
							base_form.findField('EvnDiagPLStom_disDate').setMinValue(Ext.util.Format.date(newValue, 'd.m.Y'));
						}
						else {
							base_form.findField('EvnDiagPLStom_disDate').setMinValue(undefined);
						}

						this.refreshFieldsVisibility(['PainIntensity_id']);

						var treatBeginDate = base_form.findField('EvnDiagPLStom_setDate').getValue();
						var treatEndDate = base_form.findField('EvnDiagPLStom_disDate').getValue();
						base_form.findField('DeseaseType_id').getStore().clearFilter();
						base_form.findField('DeseaseType_id').getStore().filterBy(function(rec) {
							return (
								(!rec.get('DeseaseType_begDT') || rec.get('DeseaseType_begDT') <= treatBeginDate || rec.get('DeseaseType_begDT') <= treatEndDate)
								&& (!rec.get('DeseaseType_endDT') || rec.get('DeseaseType_endDT') >= treatBeginDate || rec.get('DeseaseType_endDT') >= treatEndDate)
							);
						});
						base_form.findField('DeseaseType_id').lastQuery = '';

					}.createDelegate(this)
				},
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				tabIndex: win.tabIndexBase++,
				width: 100,
				xtype: 'swdatefield'
			}, {
				fieldLabel: 'Дата окончания',
				disabled: true,
				format: 'd.m.Y',
				listeners: {
					'change': function(field, newValue, oldValue) {
						blockedDateAfterPersonDeath('personpanelid', 'EDPLStomEF_PersonInformationFrame', field, newValue, oldValue);

						var base_form = this.FormPanel.getForm();

						this.loadMesCombo({
							p: 'change_EvnDiagPLStom_disDate',
							Diag_id: base_form.findField('Diag_id').getValue(),
							EvnVizit_setDate: base_form.findField('EvnDiagPLStom_disDate').getValue(),
							EvnVizitPLStom_id: this.evnVizitData.EvnVizitPLStom_id,
							LpuSection_id: this.evnVizitData.LpuSection_id,
							MedStaffFact_id: this.evnVizitData.MedStaffFact_id
						});

						if ( !Ext.isEmpty(newValue) ) {
							base_form.findField('EvnDiagPLStom_setDate').setMaxValue(Ext.util.Format.date(newValue, 'd.m.Y'));
						}
						else {
							base_form.findField('EvnDiagPLStom_setDate').setMaxValue(undefined);
						}
					}.createDelegate(this)
				},
				name: 'EvnDiagPLStom_disDate',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				tabIndex: win.tabIndexBase++,
				width: 100,
				xtype: 'swdatefield'
			}, {
				fieldLabel: 'Заболевание закрыто',
				listeners: {
					'check': function(checkbox, checked) {
						var base_form = this.FormPanel.getForm();

						if (checked) {
							// все поля дизаблятся
							win.enableEdit(false);
							if (win.action != 'view') {
								// кроме чекбокса
								checkbox.enable();
								// и сохранить
								win.buttons[0].show();
							}
						} else {
							// все поля енаблятся
							if (win.action != 'view') {
								win.enableEdit(true);
								base_form.findField('EvnDiagPLStom_HalfTooth').fireEvent('check', base_form.findField('EvnDiagPLStom_HalfTooth'), base_form.findField('EvnDiagPLStom_HalfTooth').getValue());
							}
						}
					}.createDelegate(this)
				},
				name: 'EvnDiagPLStom_IsClosed',
				xtype: 'checkbox'
			}, {
				allowDecimals: true,
				allowNegative: false,
				disabled: true,
				fieldLabel: 'УЕТ (факт)',
				name: 'EvnDiagPLStom_Uet',
				tabIndex: win.tabIndexBase++,
				xtype: 'numberfield'
			}, {
				allowDecimals: true,
				allowNegative: false,
				disabled: true,
				fieldLabel: 'УЕТ (факт по ОМС)',
				name: 'EvnDiagPLStom_UetOMS',
				tabIndex: win.tabIndexBase++,
				xtype: 'numberfield'
			}, {
				allowDecimals: true,
				allowNegative: false,
				disabled: true,
				enableKeyEvents: true,
				fieldLabel: 'УЕТ (норматив по КСГ)',
				name: 'EvnDiagPLStom_UetMes',
				tabIndex: win.tabIndexBase++,
				xtype: 'numberfield'
			}, {
				border: false,
				layout: 'form',
				hidden: getRegionNick().inlist([ 'kz' ]),
				items: [{
					fieldLabel: 'Подозрение на ЗНО',
					listeners: {
						'change': function(checkbox, value) {
							if(getRegionNick()!='ekb' || checkbox.disabled) return;
							var base_form = win.FormPanel.getForm(),
								DiagSpid = base_form.findField('Diag_spid'),
								diagcode = base_form.findField('Diag_id').getFieldValue('Diag_Code');
							if(!value && win.lastzno == 2 && (Ext.isEmpty(diagcode) || diagcode.search(new RegExp("^(C|D0)", "i"))<0)) {
								var pframe = win.findById('EDPLStomEF_PersonInformationFrame');
								sw.swMsg.show({
									buttons: Ext.Msg.YESNO,
									fn: function (buttonId, text, obj) {
										if (buttonId == 'yes') {
											win.changeZNO({isZNO: false});
										} else {
											checkbox.setValue(true);
											if(!Ext.isEmpty(DiagSpid.lastvalue))
												DiagSpid.setValue(DiagSpid.lastvalue);
										}
									}.createDelegate(this),
									icon: Ext.MessageBox.QUESTION,
									msg: 'По пациенту '+
										pframe.getFieldValue('Person_Surname')+' '+
										pframe.getFieldValue('Person_Firname')+' '+
										pframe.getFieldValue('Person_Secname')+
										' ранее установлено подозрение на ЗНО. Снять признак подозрения?',
									title: 'Вопрос'
								});
							}
							if(value) {
								if(Ext.isEmpty(DiagSpid.getValue()) && !Ext.isEmpty(win.lastznodiag)) {
									DiagSpid.getStore().load({
										callback:function () {
											DiagSpid.getStore().each(function (rec) {
												if (rec.get('Diag_id') == win.lastznodiag) {
													DiagSpid.fireEvent('select', DiagSpid, rec, 0);
												}
											});
										},
										params:{where:"where DiagLevel_id = 4 and Diag_id = " + win.lastznodiag}
									});
								}
								win.changeZNO({isZNO: true});
							}
						},
						'check': function(checkbox, checked) {
							var base_form = this.FormPanel.getForm(),
								DiagSpid = base_form.findField('Diag_spid');

							if (checked) {
								DiagSpid.setAllowBlank(!getRegionNick().inlist([ 'astra', 'perm' ]));
								DiagSpid.showContainer();

								if ( this.action != 'view' && base_form.findField('EvnDiagPLStom_IsClosed').getValue() != true ) {
									DiagSpid.enable();
								}
								else {
									DiagSpid.disable();
								}
							}
							else {
								DiagSpid.lastvalue = DiagSpid.getValue();
								DiagSpid.clearValue();
								DiagSpid.disable();
								DiagSpid.hideContainer();
								DiagSpid.setAllowBlank(true);
							}
						}.createDelegate(this)
					},
					name: 'EvnDiagPLStom_IsZNO',
					xtype: 'checkbox'
				}, {
					additQueryFilter: "(Diag_Code like 'C%' or Diag_Code like 'D0%')",
					baseFilterFn: function(rec){
						if(typeof rec.get == 'function') {
							return (rec.get('Diag_Code').substr(0,1) == 'C' || rec.get('Diag_Code').substr(0,2) == 'D0');
						} else if (rec.attributes && rec.attributes.Diag_Code) {
							return (rec.attributes.Diag_Code.substr(0,1) == 'C' || rec.attributes.Diag_Code.substr(0,2) == 'D0');
						} else {
							return true;
						}
					},
					fieldLabel: 'Подозрение на диагноз',
					hiddenName: 'Diag_spid',
					tabIndex: win.tabIndexBase++,
					width: 450,
					xtype: 'swdiagcombo'
				}, {
					layout: 'form',
					border: false,
					id: 'EDPLSEF_BiopsyDatePanel',
					hidden: getRegionNick()!='ekb',
					items: [{
						fieldLabel: 'Дата взятия биопсии',
						format: 'd.m.Y',
						name: 'EvnDiagPLStom_BiopsyDate',
						plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
						width: 100,
						xtype: 'swdatefield'
					}]
				}]
			},
			new sw.Promed.Panel({
				autoHeight: true,
				bodyStyle: 'padding-top: 0.5em;',
				border: true,
				collapsible: true,
				id: 'EDPLSEF_DiagPanel',
				layout: 'form',
				style: 'margin-bottom: 0.5em;',
				title: '1. Основной диагноз',
				setAllowBlankFalseIfUslugaIsKsg: function (store) {

					if ( ! getRegionNick().inlist(['ekb']) )
					{
						return true;
					}


					var UslugaCodeList = this.ksgStomUslugaList,
						isKsgStom = false,
						base_form = this.FormPanel.getForm(),
						fields = ['EvnDiagPLStom_CarriesTeethCount', 'EvnDiagPLStom_KPU'],
						fieldObj;


					store.each(function (rec) {

						if (rec.get('Usluga_Code').inlist(UslugaCodeList))
						{
							isKsgStom = true;
							return false; // для завершения итерации
						}
					});

					if (isKsgStom === true)
					{
						Ext.each(fields, function (field) {

							fieldObj = base_form.findField(field);
							fieldObj.setAllowBlank(false);
						} );

					} else
					{
						Ext.each(fields, function (field) {

							fieldObj = base_form.findField(field);
							fieldObj.setAllowBlank(true);
						} );
					}

					return true;
				},
				items: [{
					allowBlank: false,
					hiddenName: 'Diag_id',
					onChange: function (combo, value) {
						var base_form = this.FormPanel.getForm();

						this.loadMesCombo({
							p: 'change_Diag_id',
							Diag_id: value,
							EvnVizit_setDate: base_form.findField('EvnDiagPLStom_disDate').getValue(),
							EvnVizitPLStom_id: this.evnVizitData.EvnVizitPLStom_id,
							LpuSection_id: this.evnVizitData.LpuSection_id,
							MedStaffFact_id: this.evnVizitData.MedStaffFact_id
						});
						
						var diag_code = base_form.findField('Diag_id').getFieldValue('Diag_Code');
						if (diag_code) {
							if ( !Ext.isEmpty(diag_code) && diag_code.substr(0, 1).toUpperCase() == 'Z') {
								base_form.findField('DeseaseType_id').clearValue();
								base_form.findField('DeseaseType_id').disable();
								base_form.findField('DeseaseType_id').setAllowBlank(true);
							} else {
								if (win.action != 'view') {
									base_form.findField('DeseaseType_id').enable();
								}
								base_form.findField('DeseaseType_id').setAllowBlank(false);
							}

							if (
								getRegionNick() != 'krym'
								&& (
									(diag_code.substr(0, 3).toUpperCase() >= 'C00' && diag_code.substr(0, 3).toUpperCase() <= 'C97')
									||(diag_code.substr(0, 3).toUpperCase() >= 'D00' && diag_code.substr(0, 3).toUpperCase() <= 'D09')
								)
							) {
								base_form.findField('EvnDiagPLStom_IsZNO').setValue(false);
								base_form.findField('EvnDiagPLStom_IsZNO').disable();
							}
							else {
								if (win.action != 'view') {
									base_form.findField('EvnDiagPLStom_IsZNO').enable();

									if (getRegionNick() == 'buryatiya') {
										base_form.findField('EvnDiagPLStom_IsZNO').setValue(diag_code == 'Z03.1');
									}
								}
							}
						}
						else {
							if (win.action != 'view') {
								base_form.findField('EvnDiagPLStom_IsZNO').enable();
							}
						}
						
						base_form.findField('EvnDiagPLStom_IsZNO').fireEvent('check', base_form.findField('EvnDiagPLStom_IsZNO'), base_form.findField('EvnDiagPLStom_IsZNO').getValue());

						this.loadSpecificsTree();
						this.refreshFieldsVisibility(['PainIntensity_id']);
					}.createDelegate(this),
					tabIndex: win.tabIndexBase++,
					width: 450,
					xtype: 'swdiagcombo'
				}, {
					border: false,
					layout: 'form',
					hidden: !getRegionNick().inlist([ 'vologda' ]),
					items: [createKSGField(true)]
				}, {
					allowBlank: false,
					hiddenName: 'DeseaseType_id',
					tabIndex: win.tabIndexBase++,
					width: 450,
					xtype: 'swdeseasetypecombo'
				}, {
					name: 'Tooth_id',
					xtype: 'hidden'
				}, {
					tabIndex: win.tabIndexBase++,
					listeners: {
						change: function(field, newValue) {
							var base_form = this.FormPanel.getForm();
							field.applyChangeTo(this,
								base_form.findField('Tooth_id'),
								base_form.findField('ToothSurfaceType_id_list'),
								Ext.isEmpty(newValue) || base_form.findField('EvnDiagPLStom_IsClosed').getValue() || base_form.findField('EvnDiagPLStom_HalfTooth').getValue()
							);
						}.createDelegate(this)
					},
					name: 'Tooth_Code',
					xtype: 'swtoothfield'
				}, {
					border: false,
					hidden: !getRegionNick().inlist(['ekb']),
					layout: 'form',
					items: [{
						allowBlank: true,
						allowDecimals: false,
						allowNegative: false,
						fieldLabel: 'Индекс КПУ',
						name: 'EvnDiagPLStom_KPU',
						xtype: 'numberfield',
						width: 50,
						minValue: 0,
						maxValue: 32,
						tabIndex: win.tabIndexBase++,
						autoCreate: {tag: "input", maxLength: "2", autocomplete: "off"}
					}, {
						allowBlank: true,
						allowDecimals: false,
						allowNegative: false,
						fieldLabel: 'Количество нелеченых незапломбированных кариозных поражений зубов',
						name: 'EvnDiagPLStom_CarriesTeethCount',
						xtype: 'numberfield',
						width: 50,
						minValue: 0,
						maxValue: 32,
						tabIndex: win.tabIndexBase++,
						autoCreate: {tag: "input", maxLength: "2", autocomplete: "off"}
					},  {
						fieldLabel: 'Разрушение коронки зуба более 50%',
						listeners: {
							'check': function(checkbox, checked) {
								var base_form = this.FormPanel.getForm();

								base_form.findField('Tooth_Code').applyChangeTo(this,
									base_form.findField('Tooth_id'),
									base_form.findField('ToothSurfaceType_id_list'),
									Ext.isEmpty(base_form.findField('Tooth_id').getValue()) || base_form.findField('EvnDiagPLStom_IsClosed').getValue() || checked
								);
							}.createDelegate(this)
						},
						name: 'EvnDiagPLStom_HalfTooth',
						xtype: 'checkbox'
					}]
				}, {
					name: 'ToothSurfaceType_id_list',
					xtype: 'swtoothsurfacetypecheckboxgroup',
					allowBlank: true
				}, {
					comboSubject: 'BlackClass',
					fieldLabel: 'Класс по Блэку',
					hiddenName: 'BlackClass_id',
					xtype: 'swcommonsprcombo'
				}, {
					name: 'MesEkb_id',
					xtype: 'hidden'
				}, {
					border: false,
					layout: 'form',
					hidden: !getRegionNick().inlist([ 'perm', 'astra' ]),
					items: [createKSGField(false), {
						name: 'EvnDiagPLStom_KSKP',
						hidden: !getRegionNick().inlist([ 'perm' ]),
						hideLabel: !getRegionNick().inlist([ 'perm' ]),
						readOnly: true,
						fieldLabel: 'КСКП',
						xtype: 'textfield'
					}]
				}, {
					comboSubject: 'PainIntensity',
					fieldLabel: langs('Интенсивность боли'),
					hiddenName: 'PainIntensity_id',
					tabIndex: win.tabIndexBase++,
					width: 450,
					xtype: 'swcommonsprcombo'
				}]
			}),
			new sw.Promed.Panel({
				border: true,
				collapsible: true,
				height: 145,
				id: 'EDPLSEF_EvnDiagPLStomSopPanel',
				isLoaded: false,
				layout: 'border',
				listeners: {
					'expand': function(panel) {
						if ( panel.isLoaded === false ) {
							panel.isLoaded = true;
							panel.findById('EDPLSEF_EvnDiagPLStomSopGrid').getStore().load({
								params: {
									EvnDiagPLStomSop_pid: this.FormPanel.getForm().findField('EvnDiagPLStom_id').getValue()
								}
							});
						}

						panel.doLayout();
					}.createDelegate(this)
				},
				setFocusOnLoad: false,
				style: 'margin-bottom: 0.5em;',
				title: '2. Сопутствующие диагнозы',
				items: [ new Ext.grid.GridPanel({
					autoExpandColumn: 'autoexpand_diagsop',
					autoExpandMin: 100,
					border: false,
					columns: [{
						dataIndex: 'EvnDiagPLStomSop_setDate',
						header: 'Дата установки',
						hidden: false,
						renderer: Ext.util.Format.dateRenderer('d.m.Y'),
						resizable: false,
						sortable: true,
						width: 130
					}, {
						dataIndex: 'Diag_Code',
						header: 'Код',
						hidden: false,
						resizable: false,
						sortable: true,
						width: 80
					}, {
						dataIndex: 'Diag_Name',
						header: 'Наименование',
						hidden: false,
						id: 'autoexpand_diagsop',
						sortable: true
					}, {
						dataIndex: 'DeseaseType_Name',
						header: 'Характер',
						hidden: false,
						resizable: false,
						sortable: true,
						width: 250
					}],
					frame: false,
					id: 'EDPLSEF_EvnDiagPLStomSopGrid',
					keys: [{
						key: [
							Ext.EventObject.DELETE,
							Ext.EventObject.END,
							Ext.EventObject.ENTER,
							Ext.EventObject.F3,
							Ext.EventObject.F4,
							Ext.EventObject.HOME,
							Ext.EventObject.INSERT,
							Ext.EventObject.PAGE_DOWN,
							Ext.EventObject.PAGE_UP,
							Ext.EventObject.TAB
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

							e.returnValue = false;

							if ( Ext.isIE ) {
								e.browserEvent.keyCode = 0;
								e.browserEvent.which = 0;
							}

							var grid = this.findById('EDPLSEF_EvnDiagPLStomSopGrid');

							switch ( e.getKey() ) {
								case Ext.EventObject.DELETE:
									this.deleteEvent('EvnDiagPLStomSop');
								break;

								case Ext.EventObject.END:
									GridEnd(grid);
								break;

								case Ext.EventObject.ENTER:
								case Ext.EventObject.F3:
								case Ext.EventObject.F4:
								case Ext.EventObject.INSERT:
									if ( !grid.getSelectionModel().getSelected() ) {
										return false;
									}

									var action = 'add';

									if ( e.getKey() == Ext.EventObject.F3 ) {
										action = 'view';
									}
									else if ( e.getKey() == Ext.EventObject.F4 || e.getKey() == Ext.EventObject.ENTER ) {
										action = 'edit';
									}

									this.openEvnDiagPLStomSopEditWindow(action);
								break;

								case Ext.EventObject.HOME:
									GridHome(grid);
								break;

								case Ext.EventObject.PAGE_DOWN:
									GridPageDown(grid);
								break;

								case Ext.EventObject.PAGE_UP:
									GridPageUp(grid);
								break;
							}
						}.createDelegate(this),
						scope: this,
						stopEvent: true
					}],
					layout: 'fit',
					listeners: {
						'rowdblclick': function(grid, number, obj) {
							this.openEvnDiagPLStomSopEditWindow('edit');
						}.createDelegate(this)
					},
					loadMask: true,
					region: 'center',
					sm: new Ext.grid.RowSelectionModel({
						listeners: {
							'rowselect': function(sm, rowIndex, record) {
								var id = null;
								var base_form = this.FormPanel.getForm();
								var selected_record = sm.getSelected();
								var toolbar = this.findById('EDPLSEF_EvnDiagPLStomSopGrid').getTopToolbar();

								if ( selected_record ) {
									id = selected_record.get('EvnDiagPLStomSop_id');
								}

								toolbar.items.items[1].disable();
								toolbar.items.items[3].disable();

								if ( id ) {
									toolbar.items.items[2].enable();

									if (
										this.action != 'view'
										&& !base_form.findField('EvnDiagPLStom_IsClosed').getValue()
									) {
										toolbar.items.items[1].enable();
										toolbar.items.items[3].enable();
									}
								}
								else {
									toolbar.items.items[2].disable();
								}
							}.createDelegate(this)
						}
					}),
					stripeRows: true,
					store: new Ext.data.Store({
						autoLoad: false,
						listeners: {
							'load': function(store, records, index) {
								if ( store.getCount() == 0 ) {
									LoadEmptyRow(this.findById('EDPLSEF_EvnDiagPLStomSopGrid'));
								}

								if ( this.findById('EDPLSEF_EvnDiagPLStomSopPanel').setFocusOnLoad == true ) {
									this.findById('EDPLSEF_EvnDiagPLStomSopGrid').getView().focusRow(0);
									this.findById('EDPLSEF_EvnDiagPLStomSopGrid').getSelectionModel().selectFirstRow();

									this.findById('EDPLSEF_EvnDiagPLStomSopPanel').setFocusOnLoad = false;
								}
							}.createDelegate(this)
						},
						reader: new Ext.data.JsonReader({
							id: 'EvnDiagPLStomSop_id'
						}, [{
							mapping: 'EvnDiagPLStomSop_id',
							name: 'EvnDiagPLStomSop_id',
							type: 'int'
						}, {
							mapping: 'EvnDiagPLStomSop_pid',
							name: 'EvnDiagPLStomSop_pid',
							type: 'int'
						}, {
							mapping: 'Person_id',
							name: 'Person_id',
							type: 'int'
						}, {
							mapping: 'PersonEvn_id',
							name: 'PersonEvn_id',
							type: 'int'
						}, {
							mapping: 'Server_id',
							name: 'Server_id',
							type: 'int'
						}, {
							dateFormat: 'd.m.Y',
							mapping: 'EvnDiagPLStomSop_setDate',
							name: 'EvnDiagPLStomSop_setDate',
							type: 'date'
						}, {
							mapping: 'Diag_id',
							name: 'Diag_id',
							type: 'int'
						}, {
							mapping: 'Diag_Code',
							name: 'Diag_Code',
							type: 'string'
						}, {
							mapping: 'Diag_Name',
							name: 'Diag_Name',
							type: 'string'
						}, {
							mapping: 'DeseaseType_Name',
							name: 'DeseaseType_Name',
							type: 'string'
						}]),
						url: '/?c=EvnDiag&m=loadEvnDiagPLStomSopGrid'
					}),
					tbar: new sw.Promed.Toolbar({
						buttons: [{
							handler: function() {
								this.openEvnDiagPLStomSopEditWindow('add');
							}.createDelegate(this),
							iconCls: 'add16',
							text: BTN_GRIDADD,
							tooltip: BTN_GRIDADD_TIP
						}, {
							handler: function() {
								this.openEvnDiagPLStomSopEditWindow('edit');
							}.createDelegate(this),
							iconCls: 'edit16',
							text: BTN_GRIDEDIT,
							tooltip: BTN_GRIDEDIT_TIP
						}, {
							handler: function() {
								this.openEvnDiagPLStomSopEditWindow('view');
							}.createDelegate(this),
							iconCls: 'view16',
							text: BTN_GRIDVIEW,
							tooltip: BTN_GRIDVIEW_TIP
						}, {
							handler: function() {
								this.deleteEvent('EvnDiagPLStomSop');
							}.createDelegate(this),
							iconCls: 'delete16',
							text: BTN_GRIDDEL,
							tooltip: BTN_GRIDDEL_TIP
						}]
					})
				})]
			}),
			new sw.Promed.Panel({
				border: true,
				collapsible: true,
				height: 200,
				id: 'EDPLSEF_EvnUslugaStomPanel',
				isLoaded: false,
				layout: 'border',
				listeners: {
					'expand': function(panel) {
						if ( panel.isLoaded === false ) {
							panel.isLoaded = true;
							panel.findById('EDPLSEF_EvnUslugaStomGrid').getStore().load({
								params: {
									isEvnDiagPLStom: 1,
									mid: this.FormPanel.getForm().findField('EvnDiagPLStom_id').getValue(),
									pid: this.FormPanel.getForm().findField('EvnDiagPLStom_pid').getValue(),
									rid: this.FormPanel.getForm().findField('EvnDiagPLStom_rid').getValue()
								}
							});
						}
						panel.doLayout();
					}.createDelegate(this)
				},
				style: 'margin-bottom: 0.5em;',
				title: '3. Услуги',
				items: [ new Ext.grid.GridPanel({
					autoExpandColumn: 'autoexpand_usluga_diagstom',
					autoExpandMin: 100,
					border: false,
					columns: [{
						dataIndex: 'EvnUsluga_setDate',
						header: 'Дата',
						hidden: false,
						renderer: Ext.util.Format.dateRenderer('d.m.Y'),
						resizable: false,
						sortable: true,
						width: 100
					}, {
						dataIndex: 'Usluga_Code',
						header: 'Код',
						hidden: false,
						resizable: false,
						sortable: true,
						width: 100
					}, {
						dataIndex: 'Usluga_Name',
						header: 'Наименование',
						hidden: false,
						id: 'autoexpand_usluga_diagstom',
						resizable: true,
						sortable: true
					}, {
						dataIndex: 'EvnUsluga_Price',
						header: 'Цена (УЕТ)',
						hidden: false,
						resizable: true,
						sortable: true,
						renderer: twoDecimalsRenderer,
						width: 100
					}, {
						dataIndex: 'EvnUsluga_Kolvo',
						header: 'Количество',
						hidden: false,
						resizable: true,
						sortable: true,
						width: 100
					}, {
						dataIndex: 'EvnUsluga_Summa',
						header: 'Сумма (УЕТ)',
						hidden: false,
						renderer: twoDecimalsRenderer,
						resizable: true,
						sortable: true,
						width: 100
					}, {
						align: 'center',
						dataIndex: 'EvnUsluga_IsMes',
						header: 'По КСГ',
						hidden: !getRegionNick().inlist(['perm','vologda']),
						resizable: true,
						sortable: true,
						width: 75
					}, {
						align: 'center',
						dataIndex: 'EvnUsluga_IsAllMorbus',
						header: 'Для всех заболеваний',
						hidden: getRegionNick() != 'perm',
						resizable: true,
						sortable: true,
						width: 75
					}, {
						align: 'center',
						dataIndex: 'EvnUsluga_IsRequired',
						header: 'Обязательная',
						hidden: !getRegionNick().inlist(['perm','vologda']),
						resizable: true,
						sortable: true,
						width: 75
					}],
					frame: false,
					id: 'EDPLSEF_EvnUslugaStomGrid',
					keys: [{
						key: [
							Ext.EventObject.DELETE,
							Ext.EventObject.END,
							Ext.EventObject.ENTER,
							Ext.EventObject.F3,
							Ext.EventObject.F4,
							Ext.EventObject.HOME,
							Ext.EventObject.INSERT,
							Ext.EventObject.PAGE_DOWN,
							Ext.EventObject.PAGE_UP,
							Ext.EventObject.TAB
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

							e.returnValue = false;

							if ( Ext.isIE ) {
								e.browserEvent.keyCode = 0;
								e.browserEvent.which = 0;
							}

							var grid = this.findById('EDPLSEF_EvnUslugaStomGrid');

							switch ( e.getKey() ) {
								case Ext.EventObject.DELETE:
									this.deleteEvent('EvnUsluga');
								break;

								case Ext.EventObject.END:
									GridEnd(grid);
								break;

								case Ext.EventObject.ENTER:
								case Ext.EventObject.F3:
								case Ext.EventObject.F4:
									if ( !grid.getSelectionModel().getSelected() ) {
										return false;
									}

									var action = 'edit';

									if ( e.getKey() == Ext.EventObject.F3 ) {
										action = 'view';
									}

									this.openEvnUslugaStomEditWindow(action);
								break;

								case Ext.EventObject.HOME:
									GridHome(grid);
								break;

								case Ext.EventObject.INSERT:
									this.openEvnUslugaStomEditWindow('add');
								break;

								case Ext.EventObject.PAGE_DOWN:
									GridPageDown(grid);
								break;

								case Ext.EventObject.PAGE_UP:
									GridPageUp(grid);
								break;
							}
						}.createDelegate(this),
						scope: this,
						stopEvent: true
					}],
					listeners: {
						'rowdblclick': function(grid, number, obj) {
							this.openEvnUslugaStomEditWindow('edit');
						}.createDelegate(this)
					},
					loadMask: true,
					region: 'center',
					sm: new Ext.grid.RowSelectionModel({
						listeners: {
							'rowselect': function(sm, rowIndex, record) {
								var base_form = this.FormPanel.getForm();
								var selected_record = sm.getSelected();
								var toolbar = this.findById('EDPLSEF_EvnUslugaStomGrid').getTopToolbar(),
									addByMesBtn = toolbar.items.items[4];

								toolbar.items.items[1].disable();
								toolbar.items.items[2].disable();
								toolbar.items.items[3].disable();

								if ( selected_record && selected_record.get('EvnUsluga_id') ) {
									toolbar.items.items[2].enable();

									if (
										this.action != 'view'
										&& !base_form.findField('EvnDiagPLStom_IsClosed').getValue()
										&& selected_record.get('EvnUsluga_pid') == this.evnVizitData.EvnVizitPLStom_id
										&& (
											selected_record.get('EvnUsluga_IsAllMorbus') != 'X'
											|| selected_record.get('EvnDiagPLStom_id') == base_form.findField('EvnDiagPLStom_id').getValue()
										)
									) {
										toolbar.items.items[1].enable();
										toolbar.items.items[3].enable();
									}
								}

								if (getRegionNick() != 'ekb') {
									var mes_id = base_form.findField('Mes_id').getValue();
									addByMesBtn.setDisabled(this.action == 'view' || base_form.findField('EvnDiagPLStom_IsClosed').getValue() || !mes_id);
								}
							}.createDelegate(this)
						}
					}),
					stripeRows: true,
					store: new Ext.data.Store({
						autoLoad: false,
						baseParams: {
							'class': 'EvnUslugaStom',
							'parent': 'EvnDiagPLStom'
						},
						listeners: {
							'load': function(store, records, index) {
								if ( store.getCount() == 0 ) {
									LoadEmptyRow(this.findById('EDPLSEF_EvnUslugaStomGrid'));
								}

								this.uetValuesRecount();
							}.createDelegate(this),

							datachanged: Ext.getCmp('EDPLSEF_DiagPanel').setAllowBlankFalseIfUslugaIsKsg.createDelegate(this)

						},
						reader: new Ext.data.JsonReader({
							id: 'EvnUsluga_id'
						}, [{
							mapping: 'EvnUsluga_id',
							name: 'EvnUsluga_id',
							type: 'int'
						}, {
							mapping: 'EvnUsluga_pid',
							name: 'EvnUsluga_pid',
							type: 'int'
						}, {
							mapping: 'EvnDiagPLStom_id',
							name: 'EvnDiagPLStom_id',
							type: 'int'
						}, {
							mapping: 'PayType_id',
							name: 'PayType_id',
							type: 'int'
						}, {
							dateFormat: 'd.m.Y',
							mapping: 'EvnUsluga_setDate',
							name: 'EvnUsluga_setDate',
							type: 'date'
						}, {
							mapping: 'Usluga_Code',
							name: 'Usluga_Code',
							type: 'string'
						}, {
							mapping: 'Usluga_Name',
							name: 'Usluga_Name',
							type: 'string'
						}, {
							mapping: 'EvnUsluga_Price',
							name: 'EvnUsluga_Price',
							type: 'float'
						}, {
							mapping: 'EvnUsluga_Kolvo',
							name: 'EvnUsluga_Kolvo',
							type: 'float'
						}, {
							mapping: 'EvnUsluga_Summa',
							name: 'EvnUsluga_Summa',
							type: 'float'
						}, {
							mapping: 'EvnUsluga_IsMes',
							name: 'EvnUsluga_IsMes',
							type: 'string'
						}, {
							mapping: 'EvnUsluga_IsRequired',
							name: 'EvnUsluga_IsRequired',
							type: 'string'
						}, {
							mapping: 'EvnUsluga_IsAllMorbus',
							name: 'EvnUsluga_IsAllMorbus',
							type: 'string'
						}]),
						url: '/?c=EvnUsluga&m=loadEvnUslugaGrid'
					}),
					tbar: new sw.Promed.Toolbar({
						buttons: [{
							handler: function() {
								this.openEvnUslugaStomEditWindow('add');
							}.createDelegate(this),
							iconCls: 'add16',
							text: 'Добавить'
						}, {
							handler: function() {
								this.openEvnUslugaStomEditWindow('edit');
							}.createDelegate(this),
							iconCls: 'edit16',
							text: 'Изменить'
						}, {
							handler: function() {
								this.openEvnUslugaStomEditWindow('view');
							}.createDelegate(this),
							iconCls: 'view16',
							text: 'Просмотр'
						}, {
							handler: function() {
								this.deleteEvent('EvnUsluga');
							}.createDelegate(this),
							iconCls: 'delete16',
							text: 'Удалить'
						}, {
							handler: function() {
								this.openEvnUslugaStomEditWindow('addByMes');
							}.createDelegate(this),
							iconCls: 'add16',
							hidden: !getRegionNick().inlist(['perm','vologda','ekb']),
							text: getRegionNick().inlist(['perm','vologda'])?'Добавить все услуги по КСГ':'Добавить все услуги по МЭС'
						}]
					})
				})]
			}),
			new sw.Promed.Panel({
				border: true,
				collapsible: true,
				collapsed: true,
				height: 200,
				id: 'EDPLSEF_SpecificsPanel',
				isLoaded: false,
				layout: 'border',
				style: 'margin-bottom: 0.5em;',
				title: '4. Специфика',
				items: [
					{
						autoScroll:true,
						border:false,
						collapsible:false,
						wantToFocus:false,
						id: 'EDPLSEF_SpecificsTree',
						listeners:{
							'bodyresize': function(tree) {
								
							}.createDelegate(this),
							'beforeload': function(node) {
								
							}.createDelegate(this),
							'click':function (node, e) {
								var base_form = this.FormPanel.getForm();
								var win = this;
								
								var params = {};
								params.onHide = function(isChange) {
									win.loadSpecificsTree();
								};
								params.EvnDiagPLStom_id = node.attributes.value;
								params.Morbus_id = node.attributes.Morbus_id;
								params.MorbusOnko_pid = base_form.findField('EvnDiagPLStom_id').getValue();
								params.Person_id = base_form.findField('Person_id').getValue();
								params.PersonEvn_id = base_form.findField('PersonEvn_id').getValue();
								params.Server_id = base_form.findField('Server_id').getValue();
								params.EvnDiagPLStomSop_id = node.attributes.EvnDiagPLStomSop_id;
								params.allowSpecificEdit = true;
								params.action = (this.action != 'view') ? 'edit' : 'view';
								// всегда пересохраняем, чтобы в специфику ушли актуальные данные
								this.doSave({
									isAutoCreate: true,
									openChildWindow: function() {
										params.EvnDiagPLStom_id = base_form.findField('EvnDiagPLStom_id').getValue();
										params.MorbusOnko_pid = base_form.findField('EvnDiagPLStom_id').getValue();
										getWnd('swMorbusOnkoWindow').show(params);
									}.createDelegate(this)
								});
							}.createDelegate(this),
							contextmenu: function(node, e) {
								if (!!node.leaf) {
									var c = new Ext.menu.Menu({
									items: [{
										id: 'print',
										text: langs('Печать КЛУ при ЗНО'),
										disabled: !node.attributes.Morbus_id,
										icon: 'img/icons/print16.png',
										iconCls : 'x-btn-text'
									},{
										id: 'printOnko',
										text: langs('Печать выписки при онкологии'),
										disabled: !(node.attributes.Morbus_id && getRegionNick() == 'ekb'),
										hidden: getRegionNick() != 'ekb',
										icon: 'img/icons/print16.png',
										iconCls : 'x-btn-text'
									}],
									listeners: {
										itemclick: function(item) {
											switch (item.id) {
												case 'print': 
													var n = item.parentMenu.contextNode;
													printBirt({
														'Report_FileName': 'CheckList_MedCareOnkoPatients.rptdesign',
														'Report_Params': '&Evn_id=' + (n.attributes.EvnDiagPLStomSop_id ? n.attributes.EvnDiagPLStomSop_id : n.attributes.EvnDiagPLStom_id),
														'Report_Format': 'pdf'
													});
													break;
												case 'printOnko':
													var n = item.parentMenu.contextNode;
													printBirt({
														'Report_FileName': 'WritingOut_MedCareOnkoPatients.rptdesign',
														'Report_Params': '&Evn_id=' + (n.attributes.EvnDiagPLStomSop_id ? n.attributes.EvnDiagPLStomSop_id : n.attributes.EvnDiagPLStom_id),
														'Report_Format': 'pdf'
													});
													break;
											}
										}
									}
									});
									c.contextNode = node;
									c.showAt(e.getXY());
								}
							}
						},
						loader:new Ext.tree.TreeLoader({
							dataUrl:'/?c=Specifics&m=getStomSpecificsTree'
						}),
						region:'west',
						root:{
							draggable:false,
							id:'specifics_tree_root',
							nodeType:'async',
							text:'Специфика',
							value:'root'
						},
						rootVisible:false,
						split:true,
						useArrows:true,
						width:250,
						xtype:'treepanel'
					},
					{
						border:false,
						layout:'border',
						region:'center',
						xtype:'panel',
						items:[
							{
								autoHeight:true,
								border:false,
								labelWidth:150,
								split:true,
								items:[
								
								],
								layout:'form',
								region:'north',
								xtype:'panel'
							},
							{
								autoHeight:true,
								border:false,
								id:this.id + '_SpecificFormsPanel',
								items:[

								],
								layout:'fit',
								region:'center',
								xtype:'panel'
							}
						]
					}
				]
			})]
		});

		this.PersonInfo = new sw.Promed.PersonInformationPanelShort({
			id: 'EDPLStomEF_PersonInformationFrame',
			region: 'north'
		});

		Ext.apply(this, {
			buttons: [{
				handler: function() {
					if ( this.action != 'view' ) {
						this.doSave();
					}
				}.createDelegate(this),
				iconCls: 'save16',
				tabIndex: win.tabIndexBase++,
				text: BTN_FRMSAVE
			}, {
				text: '-'
			},
			HelpButton(this, -1),
			{
				handler: function() {
					this.onCancelAction();
				}.createDelegate(this),
				iconCls: 'cancel16',
				onShiftTabAction: function () {
					if ( this.action != 'view' ) {
						this.buttons[0].focus();
					}
				}.createDelegate(this),
				onTabAction: function () {
					if ( this.action != 'view' ) {
						this.FormPanel.getForm().findField('Diag_id').focus(true, 100);
					}
				}.createDelegate(this),
				tabIndex: win.tabIndexBase++,
				text: BTN_FRMCANCEL
			}],
			items: [
				this.PersonInfo,
				this.FormPanel
			]
		});

		sw.Promed.swEvnDiagPLStomEditWindow.superclass.initComponent.apply(this, arguments);
	},
	ksgStomUslugaList: [
		'1.1', '1.2', '2.1', '2.2', '3.1', '3.2', '4.1', '4.2', '5.1',
		'5.2', '10.1', '10.2', '12.1', '12.2', '13.1', '13.2', '14.1',
		'14.2', '15.1', '15.2', '16.1', '16.2', '17.1', '17.2', '18.1', '18.2'
	],
	layout: 'border',
	listeners: {
		'hide': function(win) {
			win.onHide({
				EvnUslugaGridIsModified: win.EvnUslugaGridIsModified
			});
		},
		'maximize': function(win) {
			win.findById('EDPLSEF_DiagPanel').doLayout();
			win.findById('EDPLSEF_EvnDiagPLStomSopPanel').doLayout();
			win.findById('EDPLSEF_EvnUslugaStomPanel').doLayout();
		},
		'restore': function(win) {
			win.findById('EDPLSEF_DiagPanel').doLayout();
			win.findById('EDPLSEF_EvnDiagPLStomSopPanel').doLayout();
			win.findById('EDPLSEF_EvnUslugaStomPanel').doLayout();
		}
	},
	loadMesCombo:function (changed) {
		var win = this;
		if ( this.disabledLoadMesCombo ) {
			return false;
		}

		this.disabledLoadMesCombo = true;

		var base_form = this.FormPanel.getForm();

		var
			Diag_id = changed.Diag_id || null,
			EvnVizit_id = base_form.findField('EvnDiagPLStom_pid').getValue(),
			EvnVizit_setDate = changed.EvnVizit_setDate || null,
			EvnVizitPLStom_id = changed.EvnVizitPLStom_id || null,
			LpuSection_id = changed.LpuSection_id || null,
			MedStaffFact_id = changed.MedStaffFact_id || null,
			Mes_id = base_form.findField('Mes_id').getValue(),
			Person_id = base_form.findField('Person_id').getValue();

		base_form.findField('Mes_id').disable();
		base_form.findField('Mes_id').getStore().removeAll();
		base_form.findField('Mes_id').setAllowBlank(true);

		if ( !Diag_id || !EvnVizit_setDate || !LpuSection_id || !MedStaffFact_id || !Person_id ) {
			this.disabledLoadMesCombo = false;
			return false;
		}

		base_form.findField('Mes_id').getStore().load({
			callback: function () {
				this.disabledLoadMesCombo = false;

				var index, record;

				if ( base_form.findField('Mes_id').getStore().getCount() > 0 ) {
					if ( this.action != 'view' && !base_form.findField('EvnDiagPLStom_IsClosed').getValue() ) {
						base_form.findField('Mes_id').enable();
					}
					
					if(getRegionNick() == 'astra') {
						base_form.findField('Mes_id').setAllowBlank(false);
					}

					if ( (this.windowIsJustOpened == false || this.action == 'add') && base_form.findField('Mes_id').getStore().getCount() == 1 ) {
						index = 0;
					}
					else if ( !Ext.isEmpty(Mes_id) ) {
						index = base_form.findField('Mes_id').getStore().findBy(function(rec) {
							return (rec.get('Mes_id') == Mes_id);
						});
					}

					record = base_form.findField('Mes_id').getStore().getAt(index);

					if ( typeof record == 'object' ) {
						base_form.findField('Mes_id').setValue(record.get('Mes_id'));
						win.checkChangeMesId(base_form.findField('Mes_id'), record.get('Mes_id'), Mes_id, false);
						base_form.findField('Mes_id').fireEvent('select', base_form.findField('Mes_id'), record);
					}
					else {
						base_form.findField('Mes_id').clearValue();
						win.checkChangeMesId(base_form.findField('Mes_id'), null, Mes_id, false);
						base_form.findField('Mes_id').fireEvent('select', base_form.findField('Mes_id'), null);
					}
				}
				else {
					base_form.findField('Mes_id').clearValue();
					base_form.findField('Mes_id').fireEvent('select', base_form.findField('Mes_id'), null);
				}

				this.windowIsJustOpened = false;
			}.createDelegate(this),
			params: {
				 Diag_id: Diag_id
				,EvnVizit_id: EvnVizit_id
				,EvnVizit_setDate: Ext.util.Format.date(EvnVizit_setDate, 'd.m.Y')
				,EvnVizitPLStom_id: EvnVizitPLStom_id
				,LpuSection_id: LpuSection_id
				,MedStaffFact_id: MedStaffFact_id
				,mode: 'morbus'
				,Person_id: Person_id
			}
		});
	},
	maximizable: true,
	minHeight: 550,
	minWidth: 700,
	modal: true,
	onCancelAction: function() {
		var me = this,
			evn_diag_pl_stom_id = this.FormPanel.getForm().findField('EvnDiagPLStom_id').getValue(),
			person_id = this.PersonInfo.getFieldValue('Person_id');
			
		Ext.Ajax.request({
			callback: function(options, success, response) {
				if ( success ) {
					if (response && response.responseText) {
						var response_obj = Ext.util.JSON.decode(response.responseText);
						if (response_obj && response_obj.success) {
							// при успешном выполнении ничего не требуется
						}
					}
				} else {
					sw.swMsg.alert('Ошибка', 'При удалении заболевания возникли ошибки');
				}
			},
			params: {
				Person_id: person_id
			},
			url: '/?c=MorbusOnkoSpecifics&m=clearMorbusOnkoSpecifics'
		});

		if ( evn_diag_pl_stom_id > 0 && this.action == 'add' ) {
			var deleteEvnDiagPLStom = function() {
				// удалить заболевание
				// закрыть окно после успешного удаления
				var loadMask = new Ext.LoadMask(me.getEl(), { msg: "Удаление заболевания..." });
				loadMask.show();
				Ext.Ajax.request({
					callback: function(options, success, response) {
						loadMask.hide();
						if ( success ) {
							if (response && response.responseText) {
								var response_obj = Ext.util.JSON.decode(response.responseText);
								if (response_obj && response_obj.success) {
									me.hide();
								}
							}
						} else {
							sw.swMsg.alert('Ошибка', 'При удалении заболевания возникли ошибки');
						}
					},
					params: {
						Evn_id: evn_diag_pl_stom_id
					},
					url: '/?c=Evn&m=deleteEvn'
				});
			};
			deleteEvnDiagPLStom();
		}
		else {
			this.hide();
		}
	},
	onHide: Ext.emptyFn,
	openEvnDiagPLStomSopEditWindow: function(action) {
		if ( Ext.isEmpty(action) || !action.inlist([ 'add', 'edit', 'view' ]) ) {
			return false;
		}

		var
			base_form = this.FormPanel.getForm(),
			grid = this.findById('EDPLSEF_EvnDiagPLStomSopGrid');

		if ( this.action == 'view' || base_form.findField('EvnDiagPLStom_IsClosed').getValue() ) {
			if ( action == 'add') {
				return false;
			}
			else if ( action == 'edit' ) {
				action = 'view';
			}
		}

		if ( getWnd('swEvnDiagPLStomSopEditWindow').isVisible() ) {
			sw.swMsg.alert('Сообщение', 'Окно редактирования сопутствующего диагноза уже открыто');
			return false;
		}

		if ( action == 'add' && base_form.findField('EvnDiagPLStom_id').getValue() == 0 ) {
			this.doSave({
				isAutoCreate: true,
				openChildWindow: function() {
					this.openEvnDiagPLStomSopEditWindow(action);
				}.createDelegate(this)
			});
			return false;
		}

		var
			formParams = new Object(),
			params = new Object();

		params.action = action;
		params.callback = function(data) {
			if ( !data || !data.evnDiagPLStomSopData ) {
				return false;
			}

			var record = grid.getStore().getById(data.evnDiagPLStomSopData[0].EvnDiagPLStomSop_id);

			if ( !record ) {
				if ( grid.getStore().getCount() == 1 && !grid.getStore().getAt(0).get('EvnDiagPLStomSop_id') ) {
					grid.getStore().removeAll();
				}

				grid.getStore().loadData(data.evnDiagPLStomSopData, true);
			}
			else {
				var evn_diag_pl_stom_fields = new Array();
				var i = 0;

				grid.getStore().fields.eachKey(function(key, item) {
					evn_diag_pl_stom_fields.push(key);
				});

				for ( i = 0; i < evn_diag_pl_stom_fields.length; i++ ) {
					record.set(evn_diag_pl_stom_fields[i], data.evnDiagPLStomSopData[0][evn_diag_pl_stom_fields[i]]);
				}

				record.commit();
			}
			
			this.loadSpecificsTree();
		}.createDelegate(this);
		params.onHide = function() {
			grid.getView().focusRow(0);
			grid.getSelectionModel().selectFirstRow();
		}.createDelegate(this);
		params.Person_id = this.PersonInfo.getFieldValue('Person_id');
		params.Person_Birthday = this.PersonInfo.getFieldValue('Person_Birthday');
		params.Person_Firname = this.PersonInfo.getFieldValue('Person_Firname');
		params.Person_Secname = this.PersonInfo.getFieldValue('Person_Secname');
		params.Person_Surname = this.PersonInfo.getFieldValue('Person_Surname');

		var evn_diag_pl_stom_set_date = base_form.findField('EvnDiagPLStom_setDate').getValue();

		if ( !evn_diag_pl_stom_set_date ) {
			sw.swMsg.alert('Сообщение', 'Не заданы обязательные параметры заболевания');
			return false;
		}

		if ( action == 'add' ) {
			formParams.EvnDiagPLStomSop_id = 0;
			formParams.EvnDiagPLStomSop_pid = base_form.findField('EvnDiagPLStom_id').getValue();
			formParams.EvnDiagPLStomSop_setDate = evn_diag_pl_stom_set_date;
			formParams.Person_id = base_form.findField('Person_id').getValue();
			formParams.PersonEvn_id = base_form.findField('PersonEvn_id').getValue();
			formParams.Server_id = base_form.findField('Server_id').getValue();
		}
		else {
			var selected_record = grid.getSelectionModel().getSelected();

			if ( !selected_record || !selected_record.get('EvnDiagPLStomSop_id') ) {
				return false;
			}

			formParams.EvnDiagPLStomSop_id = selected_record.get('EvnDiagPLStomSop_id');
		}

		params.formParams = formParams;
		params.archiveRecord = this.archiveRecord;

		getWnd('swEvnDiagPLStomSopEditWindow').show(params);
	},
	openEvnUslugaStomEditWindow: function(action) {
		if ( Ext.isEmpty(action) || !action.inlist([ 'add', 'addByMes', 'edit', 'view' ]) ) {
			return false;
		}

		var wnd = this;

		var
			base_form = this.FormPanel.getForm(),
			grid = this.findById('EDPLSEF_EvnUslugaStomGrid');

		if ( this.action == 'view' || base_form.findField('EvnDiagPLStom_IsClosed').getValue() ) {
			if ( action == 'add' || action == 'addByMes') {
				return false;
			}
			else if ( action == 'edit' ) {
				action = 'view';
			}
		}

		if ( (action == 'add' || action == 'addByMes') && base_form.findField('EvnDiagPLStom_id').getValue() == 0 ) {
			this.doSave({
				isAutoCreate: true,
				openChildWindow: function() {
					this.openEvnUslugaStomEditWindow(action);
				}.createDelegate(this)
			});
			return false;
		}

		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					form.getFirstInvalidEl().focus(false);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		var params = new Object();

		var
			mes_count = base_form.findField('EvnDiagPLStom_UetOMS').getValue(),
			mes_id = base_form.findField('Mes_id').getValue(),
			person_id = this.PersonInfo.getFieldValue('Person_id'),
			person_birthday = this.PersonInfo.getFieldValue('Person_Birthday'),
			person_firname = this.PersonInfo.getFieldValue('Person_Firname'),
			person_secname = this.PersonInfo.getFieldValue('Person_Secname'),
			person_surname = this.PersonInfo.getFieldValue('Person_Surname');

		if (getRegionNick() == 'ekb') {
			mes_id = base_form.findField('MesEkb_id').getValue();
		}

		if ( action == 'add' || action == 'addByMes' ) {
			params.EvnUslugaStom_id = 0;
			params.EvnUslugaStom_rid = base_form.findField('EvnDiagPLStom_rid').getValue(); // ТАП
			params.EvnUslugaStom_pid = this.evnVizitData.EvnVizitPLStom_id; // Посещение то, из которого открыто заболевание
			params.EvnDiagPLStom_id = base_form.findField('EvnDiagPLStom_id').getValue(); // Заболевание
			params.EvnUslugaStom_setDate = this.evnVizitData.EvnVizitPLStom_setDate;
			params.MedStaffFact_id = this.evnVizitData.MedStaffFact_id;
			params.LpuSection_uid = this.evnVizitData.LpuSection_id;
			params.LpuSectionProfile_id = this.evnVizitData.LpuSectionProfile_id;
			params.PayType_id = this.evnVizitData.PayType_id;
			params.Person_id = base_form.findField('Person_id').getValue();
			params.PersonEvn_id = base_form.findField('PersonEvn_id').getValue();
			params.Server_id = base_form.findField('Server_id').getValue();
		}
		else {
			var selected_record = grid.getSelectionModel().getSelected();

			if ( !selected_record || !selected_record.get('EvnUsluga_id') ) {
				return false;
			}

			params.EvnUslugaStom_id = selected_record.get('EvnUsluga_id');

			if ( selected_record.get('EvnUsluga_IsAllMorbus') == 'X' && selected_record.get('EvnDiagPLStom_id') != base_form.findField('EvnDiagPLStom_id').getValue() ) {
				action = 'view';
			}
		}

		var winParams = {
			action: action,
			callback: function(data) {
				if (data && data.evnUslugaData && data.evnUslugaData.clearKSGField) {
					// очищаем поле КСГ
					base_form.findField('Mes_id').clearValue();
					wnd.getLoadMask(LOAD_WAIT_SAVE).show();
					Ext.Ajax.request({
						url: '/?c=EvnDiagPLStom&m=updateMesId',
						params: {
							Mes_id: null,
							EvnDiagPLStom_id: base_form.findField('EvnDiagPLStom_id').getValue()
						},
						callback: function (options, success, response) {
							wnd.getLoadMask().hide();

							// перезагружаем грид услуг
							grid.getStore().load({
								params: {
									isEvnDiagPLStom: 1,
									mid: base_form.findField('EvnDiagPLStom_id').getValue(),
									pid: base_form.findField('EvnDiagPLStom_pid').getValue(),
									rid: base_form.findField('EvnDiagPLStom_rid').getValue(),
									Mes_id: null
								}
							});
						}
					});
				} else {
					grid.getStore().load({
						params: {
							isEvnDiagPLStom: 1,
							mid: base_form.findField('EvnDiagPLStom_id').getValue(),
							pid: base_form.findField('EvnDiagPLStom_pid').getValue(),
							rid: base_form.findField('EvnDiagPLStom_rid').getValue()
						},
						callback: function () {
							this.uetValuesRecount();
						}.createDelegate(this)
					});
				}
				this.EvnUslugaGridIsModified = true;
			}.createDelegate(this),
			formMode: this.formMode,
			formParams: params,
			Mes_id: mes_id,
			Mes_Spend: (mes_count ? (mes_count) : 0),
			Mes_Total: base_form.findField('Mes_id').getFieldValue('Mes_KoikoDni'),
			onHide: function() {
				grid.getView().focusRow(0);
				grid.getSelectionModel().selectFirstRow();
			}.createDelegate(this),
			Person_id: person_id,
			Person_Birthday: person_birthday,
			Person_Firname: person_firname,
			Person_Secname: person_secname,
			Person_Surname: person_surname,
			EvnDiagPLStom_Title: (Ext.isEmpty(base_form.findField('EvnDiagPLStom_setDate').getValue())?'':base_form.findField('EvnDiagPLStom_setDate').getValue().format('d.m.Y')) + ' / Диагноз ' + base_form.findField('Diag_id').getRawValue() + ' / Номер зуба ' + (Ext.isEmpty(base_form.findField('Tooth_Code').getValue())?'0':base_form.findField('Tooth_Code').getValue())
		};

		var win = (action == 'addByMes' ? getWnd('swEvnUslugaStomByMesInputWindow') : getWnd('swEvnUslugaStomEditWindow'));

		if ( win.isVisible() ) {
			sw.swMsg.alert('Сообщение', 'Окно выполнения стоматологической услуги уже открыто');
			return false;
		}

		winParams.archiveRecord = this.archiveRecord;
		win.show(winParams);
	},
	payTypeStore: new Ext.db.AdapterStore({
		autoLoad: false,
		dbFile: 'Promed.db',
		fields: [
			{ name: 'PayType_id', type: 'int', mapping: 'PayType_id' },
			{ name: 'PayType_Code', type: 'int', mapping: 'PayType_Code' },
			{ name: 'PayType_Name', type: 'string', mapping: 'PayType_Name' },
			{ name: 'PayType_SysNick', type: 'string', mapping: 'PayType_SysNick' }
		],
		key: 'PayType_id',
		params: { object: 'PayType', order_by_field: 'PayType_Code' },
		sortInfo: {
			field: 'PayType_Code'
		},
		tableName: 'PayType'
	}),
	plain: true,
	refreshFieldsVisibility: function(fieldNames) {
		var allowedFields = [
			'PainIntensity_id'
		];
		var win = this;
		var base_form = win.FormPanel.getForm();
		if (typeof fieldNames == 'string') fieldNames = [fieldNames];

		var action = win.action;
		var Region_Nick = getRegionNick();

		base_form.items.each(function(field){
			if (!Ext.isEmpty(fieldNames) && !field.getName().inlist(fieldNames)) return;
			if (!field.getName().inlist(allowedFields)) return;

			var value = field.getValue();
			var allowBlank = null;
			var visible = null;
			var enable = null;
			var filter = null;

			var dateX20181101 = new Date(2018, 10, 1); // 01.11.2018
			var Diag_Code = base_form.findField('Diag_id').getFieldValue('Diag_Code');
			var EvnDiagPLStom_setDate = base_form.findField('EvnDiagPLStom_setDate').getValue();

			var diag_code_full = !Ext.isEmpty(Diag_Code)?String(Diag_Code).slice(0, 3):'';

			switch(field.getName()) {
				case 'PainIntensity_id':
					visible = (
						Region_Nick.inlist(['penza'])
						&& (
							(diag_code_full >= 'C00' && diag_code_full <= 'C97')
							|| (diag_code_full >= 'D00' && diag_code_full <= 'D09')
						)
						&& !Ext.isEmpty(EvnDiagPLStom_setDate)
						&& EvnDiagPLStom_setDate >= dateX20181101
					);
					enable = visible;
					allowBlank = !visible;
					if (Ext.isEmpty(value) && visible === true) {
						value = 1;
					}
					break;
			}

			if (visible === false && win.formLoaded) {
				value = null;
			}
			if (value != field.getValue()) {
				field.setValue(value);
				field.fireEvent('change', field, value);
			}
			if (allowBlank !== null) {
				field.setAllowBlank(allowBlank);
			}
			if (visible !== null) {
				field.setContainerVisible(visible);
			}
			if (enable !== null) {
				field.setDisabled(!enable || action == 'view');
			}
			if (typeof filter == 'function' && field.store) {
				field.lastQuery = '';
				if (typeof field.setBaseFilter == 'function') {
					field.setBaseFilter(filter);
				} else {
					field.store.filterBy(filter);
				}
			}
		});
	},
	resizable: true,
	checkChangeMesId: function(combo, newValue, oldValue, canChangeBack) {
		var win = this;
		var base_form = win.FormPanel.getForm();

		var index = combo.getStore().findBy(function(rec) {
			return (rec.get('Mes_id') == newValue);
		});
		// проверяем изменится ли список обязательных услуг
		// Если хотя бы одна заведенная услуга по старому КСГ с атрибутом «обязательная» не предусмотрена в новом КСГ (или является необязательной),
		if (!Ext.isEmpty(base_form.findField('EvnDiagPLStom_id').getValue()) && base_form.findField('EvnDiagPLStom_id').getValue() > 0) {
			win.getLoadMask(LOAD_WAIT).show();
			Ext.Ajax.request({
				url: '/?c=EvnUsluga&m=checkChangeEvnUslugaIsNeed',
				params: {
					oldMes_id: oldValue,
					newMes_id: newValue,
					mid: base_form.findField('EvnDiagPLStom_id').getValue(),
					pid: base_form.findField('EvnDiagPLStom_pid').getValue(),
					rid: base_form.findField('EvnDiagPLStom_rid').getValue()
				},
				callback: function(options, success, response) {
					win.getLoadMask().hide();

					if (success) {
						var result = Ext.util.JSON.decode(response.responseText);
						if (result.needUslugaChange) {
							// то выводить предупреждение «При изменении КСГ изменится список обязательных услуг. Изменить/Отмена ».
							if (canChangeBack) {
								sw.swMsg.show({
									buttons: Ext.Msg.YESNO,
									fn: function (buttonId) {
										if (buttonId == 'yes') {
											// При нажатии «Изменить» изменить атрибуты («Обязательная», «По КСГ») в списке услуг. При нажатии «Отмена» сообщение закрыть, изменение КСГ отменить.
											// сохраняем КСГ и обновляем услуги
											win.getLoadMask(LOAD_WAIT_SAVE).show();
											Ext.Ajax.request({
												url: '/?c=EvnDiagPLStom&m=updateMesId',
												params: {
													Mes_id: newValue,
													EvnDiagPLStom_id: base_form.findField('EvnDiagPLStom_id').getValue()
												},
												callback: function (options, success, response) {
													win.getLoadMask().hide();

													// перезагружаем грид услуг
													win.findById('EDPLSEF_EvnUslugaStomGrid').getStore().load({
														params: {
															isEvnDiagPLStom: 1,
															mid: base_form.findField('EvnDiagPLStom_id').getValue(),
															pid: base_form.findField('EvnDiagPLStom_pid').getValue(),
															rid: base_form.findField('EvnDiagPLStom_rid').getValue(),
															Mes_id: newValue
														}
													});
												}
											});
										} else {
											combo.setValue(oldValue);
											combo.fireEvent('select', combo, combo.getStore().getAt(index));
										}
									},
									msg: 'При изменении КСГ изменится список обязательных услуг. Изменить КСГ?',
									title: 'Подтверждение'
								});
							} else {
								sw.swMsg.alert('Сообщение', 'При изменении КСГ изменился список обязательных услуг');
								win.getLoadMask(LOAD_WAIT_SAVE).show();
								Ext.Ajax.request({
									url: '/?c=EvnDiagPLStom&m=updateMesId',
									params: {
										Mes_id: newValue,
										EvnDiagPLStom_id: base_form.findField('EvnDiagPLStom_id').getValue()
									},
									callback: function (options, success, response) {
										win.getLoadMask().hide();

										// перезагружаем грид услуг
										win.findById('EDPLSEF_EvnUslugaStomGrid').getStore().load({
											params: {
												isEvnDiagPLStom: 1,
												mid: base_form.findField('EvnDiagPLStom_id').getValue(),
												pid: base_form.findField('EvnDiagPLStom_pid').getValue(),
												rid: base_form.findField('EvnDiagPLStom_rid').getValue(),
												Mes_id: newValue
											}
										});
									}
								});
							}
						} else {
							// а иначе всё равно надо обновить атрибуты услуг
							win.getLoadMask(LOAD_WAIT_SAVE).show();
							Ext.Ajax.request({
								url: '/?c=EvnDiagPLStom&m=updateMesId',
								params: {
									Mes_id: newValue,
									EvnDiagPLStom_id: base_form.findField('EvnDiagPLStom_id').getValue()
								},
								callback: function (options, success, response) {
									win.getLoadMask().hide();

									// перезагружаем грид услуг
									win.findById('EDPLSEF_EvnUslugaStomGrid').getStore().load({
										params: {
											isEvnDiagPLStom: 1,
											mid: base_form.findField('EvnDiagPLStom_id').getValue(),
											pid: base_form.findField('EvnDiagPLStom_pid').getValue(),
											rid: base_form.findField('EvnDiagPLStom_rid').getValue(),
											Mes_id: newValue
										}
									});
								}
							});
						}
					}
				}
			});
		}
	},
	show: function() {
		sw.Promed.swEvnDiagPLStomEditWindow.superclass.show.apply(this, arguments);

		this.restore();
		this.center();
		this.maximize();

		this.findById('EDPLSEF_EvnDiagPLStomSopPanel').collapse();

		var win = this;
		var base_form = this.FormPanel.getForm();
		base_form.reset();

		if (getRegionNick() != 'perm') {
			base_form.findField('EvnDiagPLStom_UetMes').setContainerVisible(false);
		} else {
			base_form.findField('EvnDiagPLStom_UetMes').setContainerVisible(true);
		}

		this.action = null;
		this.callback = Ext.emptyFn;
		this.EvnUslugaGridIsModified = false;
		this.evnVizitData = new Object();
		this.formMode = 'classic';
		this.formStatus = 'edit';
		this.onHide = Ext.emptyFn;
		this.windowIsJustOpened = true;

		if ( !arguments[0] || typeof arguments[0].formParams != 'object' || typeof arguments[0].evnVizitData != 'object' ) {
			sw.swMsg.alert('Сообщение', 'Неверные параметры');
			this.hide();
			return false;
		}

		base_form.setValues(arguments[0].formParams);
		this.evnVizitData = arguments[0].evnVizitData;

		if ( arguments[0].action ) {
			this.action = arguments[0].action;
		}

		if ( typeof arguments[0].callback == 'function' ) {
			this.callback = arguments[0].callback;
		}

		if ( arguments[0].formMode ) {
			this.formMode = arguments[0].formMode;
		}

		if ( typeof arguments[0].onHide == 'function' ) {
			this.onHide = arguments[0].onHide;
		}
		if(arguments[0].Person_id) {
			base_form.setValues({'Person_id':arguments[0].Person_id});
		}

		this.PersonInfo.load({
			Person_id: (arguments[0].Person_id ? arguments[0].Person_id : ''),
			Person_Birthday: (arguments[0].Person_Birthday ? arguments[0].Person_Birthday : ''),
			Person_Firname: (arguments[0].Person_Firname ? arguments[0].Person_Firname : ''),
			Person_Secname: (arguments[0].Person_Secname ? arguments[0].Person_Secname : ''),
			Person_Surname: (arguments[0].Person_Surname ? arguments[0].Person_Surname : ''),
			callback: function() {
				var field = base_form.findField('EvnDiagPLStom_setDate');
				clearDateAfterPersonDeath('personpanelid', 'EDPLStomEF_PersonInformationFrame', field);
			}
		});

		this.findById('EDPLSEF_EvnUslugaStomPanel').isLoaded = false;

		if ( this.action == 'add' ) {
			this.findById('EDPLSEF_EvnDiagPLStomSopPanel').isLoaded = true;
		}
		else {
			this.findById('EDPLSEF_EvnDiagPLStomSopPanel').isLoaded = false;
		}

		this.findById('EDPLSEF_EvnDiagPLStomSopGrid').getStore().removeAll();
		this.findById('EDPLSEF_EvnDiagPLStomSopGrid').getTopToolbar().items.items[0].enable();
		this.findById('EDPLSEF_EvnDiagPLStomSopGrid').getTopToolbar().items.items[1].disable();
		this.findById('EDPLSEF_EvnDiagPLStomSopGrid').getTopToolbar().items.items[2].disable();
		this.findById('EDPLSEF_EvnDiagPLStomSopGrid').getTopToolbar().items.items[3].disable();

		this.findById('EDPLSEF_EvnUslugaStomGrid').getStore().removeAll();
		this.findById('EDPLSEF_EvnUslugaStomGrid').getTopToolbar().items.items[0].enable();
		this.findById('EDPLSEF_EvnUslugaStomGrid').getTopToolbar().items.items[1].disable();
		this.findById('EDPLSEF_EvnUslugaStomGrid').getTopToolbar().items.items[2].disable();
		this.findById('EDPLSEF_EvnUslugaStomGrid').getTopToolbar().items.items[3].disable();
		this.findById('EDPLSEF_EvnUslugaStomGrid').getTopToolbar().items.items[4].disable();

		base_form.findField('EvnDiagPLStom_setDate').setMaxValue(undefined);
		base_form.findField('EvnDiagPLStom_setDate').setMinValue(undefined);
		base_form.findField('EvnDiagPLStom_disDate').setMaxValue(undefined);
		base_form.findField('EvnDiagPLStom_disDate').setMinValue(undefined);
		base_form.findField('BlackClass_id').setContainerVisible(getRegionNick() == 'ekb');

		if (getRegionNick() == 'ekb' && this.evnVizitData && !Ext.isEmpty(this.evnVizitData.MesEkb_id)) {
			base_form.findField('MesEkb_id').setValue(this.evnVizitData.MesEkb_id);
			var addByMesBtn = this.findById('EDPLSEF_EvnUslugaStomGrid').getTopToolbar().items.items[4];
			addByMesBtn.setDisabled(false);
		}

		var
			diag_combo = base_form.findField('Diag_id'),
			tooth_field = base_form.findField('Tooth_Code'),
			ToothSurface_group = base_form.findField('ToothSurfaceType_id_list');

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT });
		loadMask.show();

		LoadEmptyRow(this.findById('EDPLSEF_EvnDiagPLStomSopGrid'));
		LoadEmptyRow(this.findById('EDPLSEF_EvnUslugaStomGrid'));
		

		switch ( this.action ) {
			case 'add':
				this.setTitle(this.baseTitle + ': ' + FRM_ACTION_ADD);
				this.enableEdit(true);

				this.findById('EDPLSEF_EvnUslugaStomPanel').fireEvent('expand', this.findById('EDPLSEF_EvnUslugaStomPanel'));

				// Панели с основным диагнозом и списком услуг по умолчанию развернуты
				this.findById('EDPLSEF_DiagPanel').expand();
				this.findById('EDPLSEF_EvnUslugaStomPanel').expand();


				base_form.findField('EvnDiagPLStom_setDate').fireEvent('change', base_form.findField('EvnDiagPLStom_setDate'), base_form.findField('EvnDiagPLStom_setDate').getValue());
				base_form.findField('EvnDiagPLStom_disDate').fireEvent('change', base_form.findField('EvnDiagPLStom_disDate'), base_form.findField('EvnDiagPLStom_disDate').getValue());

				loadMask.hide();

				tooth_field.setValue(null);
				tooth_field.fireEvent('change', tooth_field, null);

				this.uetValuesRecount();

				base_form.findField('Diag_id').focus(true, 250);
			break;

			case 'edit':
			case 'view':
				var evn_diag_pl_stom_id = base_form.findField('EvnDiagPLStom_id').getValue();

				if ( Ext.isEmpty(evn_diag_pl_stom_id) ) {
					loadMask.hide();
					this.hide();
					return false;
				}

				base_form.load({
					failure: function() {
						loadMask.hide();
						sw.swMsg.alert('Ошибка', 'Ошибка при загрузке данных формы', function() { this.hide(); }.createDelegate(this) );
					}.createDelegate(this),
					params: {
						EvnDiagPLStom_id: evn_diag_pl_stom_id
					},
					success: function(f, act) {
						// В зависимости от accessType переопределяем this.action
						if ( base_form.findField('accessType').getValue() == 'view' ) {
							this.action = 'view';
						}

						if ( this.action == 'edit' ) {
							this.setTitle(this.baseTitle + ': ' + FRM_ACTION_EDIT);
							this.enableEdit(true);
						}
						else {
							this.setTitle(this.baseTitle + ': ' + FRM_ACTION_VIEW);
							this.enableEdit(false);
						}

						var
							diag_id = act.result.data.Diag_id,
							diag_spid = act.result.data.Diag_spid;

						base_form.findField('EvnDiagPLStom_IsClosed').fireEvent('check', base_form.findField('EvnDiagPLStom_IsClosed'), base_form.findField('EvnDiagPLStom_IsClosed').getValue());
						base_form.findField('EvnDiagPLStom_IsZNO').fireEvent('check', base_form.findField('EvnDiagPLStom_IsZNO'), base_form.findField('EvnDiagPLStom_IsZNO').getValue());

						this.findById('EDPLSEF_EvnUslugaStomPanel').fireEvent('expand', this.findById('EDPLSEF_EvnUslugaStomPanel'));

						base_form.findField('EvnDiagPLStom_setDate').fireEvent('change', base_form.findField('EvnDiagPLStom_setDate'), base_form.findField('EvnDiagPLStom_setDate').getValue());
						base_form.findField('EvnDiagPLStom_disDate').fireEvent('change', base_form.findField('EvnDiagPLStom_disDate'), base_form.findField('EvnDiagPLStom_disDate').getValue());


						if ( !Ext.isEmpty(diag_id) ) {
							diag_combo.getStore().load({
								callback: function() {
									diag_combo.getStore().each(function(record) {
										if ( record.get('Diag_id') == diag_id ) {
											diag_combo.fireEvent('select', diag_combo, record, 0);
											win.loadSpecificsTree();
											win.refreshFieldsVisibility();
										}
									});
								},
								params: { where: "where DiagLevel_id = 4 and Diag_id = " + diag_id }
							});
						}

						if ( !Ext.isEmpty(diag_spid) ) {
							base_form.findField('Diag_spid').getStore().load({
								callback: function() {
									base_form.findField('Diag_spid').getStore().each(function (record) {
										if ( record.get('Diag_id') == diag_spid ) {
											base_form.findField('Diag_spid').fireEvent('select', base_form.findField('Diag_spid'), record, 0);
										}
									});
								},
								params: { where:"where DiagLevel_id = 4 and Diag_id = " + diag_spid }
							});
						}

						loadMask.hide();

						var
							tooth_code = tooth_field.getValue(),
							ToothSurfaceType_id_list = ToothSurface_group.getValue();

						if ( !tooth_field.hasCode(tooth_code) ) {
							tooth_code = null;
							tooth_field.setValue(tooth_code);
						}
						if(getRegionNick()=='ekb')
							this.checkBiopsyDate();

						tooth_field.fireEvent('change', tooth_field, tooth_code);
						ToothSurface_group.setValue(ToothSurfaceType_id_list);

						if ( this.action == 'edit' ) {
							base_form.findField('Diag_id').focus(true, 250);
						}
						else {
							this.buttons[this.buttons.length - 1].focus();
						}
						
						this.findById('EDPLSEF_EvnDiagPLStomSopGrid').getStore().load({
							params: {
								EvnDiagPLStomSop_pid: this.FormPanel.getForm().findField('EvnDiagPLStom_id').getValue()
							},
							callback: function() {
								this.loadSpecificsTree();
							}.createDelegate(this)
						});
					}.createDelegate(this),
					url: '/?c=EvnDiagPLStom&m=loadEvnDiagPLStomEditForm'
				});
			break;

			default:
				loadMask.hide();
				this.hide();
			break;
		}
		if(getRegionNick()=='ekb') {
			this.checkZNO({action: this.action });
			Ext.QuickTips.register({
				target: base_form.findField('EvnDiagPLStom_BiopsyDate').getEl(),
				text: 'Дата взятия биопсии, по результатам которой снимается подозрение на ЗНО',
				enabled: true,
				showDelay: 5,
				trackMouse: true,
				autoShow: true
			});
		}
	},
	tabIndexBase: TABINDEX_EDPLSTOMEF,
	uetValuesRecount: function() {
		var
			base_form = this.FormPanel.getForm(),
			grid = this.findById('EDPLSEF_EvnUslugaStomGrid');

		var
			evn_usluga_stom_uet = 0,
			evn_usluga_stom_uet_oms = 0,
			PayType_id;

		if ( this.payTypeStore.getCount() == 0 ) {
			var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Загрузка справочника видов оплаты..." });
			loadMask.show();

			this.payTypeStore.load({
				callback: function() {
					loadMask.hide();
					this.uetValuesRecount();
				}.createDelegate(this)
			});
			return false;
		}

		var index = this.payTypeStore.findBy(function(rec) {
			return (rec.get('PayType_SysNick') == getPayTypeSysNickOms());
		});

		if ( index >= 0 ) {
			PayType_id = this.payTypeStore.getAt(index).get('PayType_id');
		}

		var minSetDate = null;
		if (!Ext.isEmpty(base_form.findField('EvnVizitPLStom_setDate').getValue())) {
			//Дата посещения, из которого заболевание было создано
			minSetDate = getValidDT(base_form.findField('EvnVizitPLStom_setDate').getValue(), '');
		}

		var maxSetDate = null;
		if (!Ext.isEmpty(this.evnVizitData.EvnVizitPLStom_setDate)) {
			//Дата посещения, из которого была открыта форма редактирования заболевания
			maxSetDate = this.evnVizitData.EvnVizitPLStom_setDate;
		}

		grid.getStore().each(function(record) {
			if ( record.get('PayType_id') == PayType_id ) {
				evn_usluga_stom_uet_oms = evn_usluga_stom_uet_oms + Number(record.get('EvnUsluga_Summa'));
			}

			evn_usluga_stom_uet = evn_usluga_stom_uet + Number(record.get('EvnUsluga_Summa'));

			// даты считаем только по услугам данного заболевания.
			if (record.get('EvnDiagPLStom_id') == base_form.findField('EvnDiagPLStom_id').getValue()) {
				if (Ext.isEmpty(minSetDate) || record.get('EvnUsluga_setDate') < minSetDate) {
					minSetDate = record.get('EvnUsluga_setDate');
				}
				if (Ext.isEmpty(maxSetDate) || record.get('EvnUsluga_setDate') > maxSetDate) {
					maxSetDate = record.get('EvnUsluga_setDate');
				}
			}
		});

		base_form.findField('EvnDiagPLStom_Uet').setValue(evn_usluga_stom_uet.toFixed(2));
		base_form.findField('EvnDiagPLStom_UetOMS').setValue(evn_usluga_stom_uet_oms.toFixed(2));

		if (!Ext.isEmpty(minSetDate)) {
			base_form.findField('EvnDiagPLStom_setDate').setValue(minSetDate);
		}

		if (!Ext.isEmpty(maxSetDate)) {
			base_form.findField('EvnDiagPLStom_disDate').setValue(maxSetDate);
		}
		else if (!Ext.isEmpty(base_form.findField('EvnDiagPLStom_setDate').getValue())) {
			base_form.findField('EvnDiagPLStom_disDate').setValue(base_form.findField('EvnDiagPLStom_setDate').getValue());
		}
		
		var treatBeginDate = base_form.findField('EvnDiagPLStom_setDate').getValue();
		var treatEndDate = base_form.findField('EvnDiagPLStom_disDate').getValue();
		base_form.findField('DeseaseType_id').getStore().clearFilter();
		base_form.findField('DeseaseType_id').getStore().filterBy(function(rec) {
			return (
				(!rec.get('DeseaseType_begDT') || rec.get('DeseaseType_begDT') <= treatBeginDate || rec.get('DeseaseType_begDT') <= treatEndDate)
				&& (!rec.get('DeseaseType_endDT') || rec.get('DeseaseType_endDT') >= treatBeginDate || rec.get('DeseaseType_endDT') >= treatEndDate)
			);
		});
		base_form.findField('DeseaseType_id').lastQuery = '';
	},
	width: 700
});