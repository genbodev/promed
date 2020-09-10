/**
* swUslugaEditWindow - Форма добавления/редактирования услуги
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package		Usluga
* @access		public
* @copyright	Copyright (c) 2009 Swan Ltd.
* @author		Stas Bykov aka Savage (savage1981@gmail.com)
* @version		15.05.2014
* @comment		Префикс для id компонентов UEF (UslugaEditForm)
*/

sw.Promed.swUslugaEditWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swUslugaEditWindow',
	objectSrc: '/jscore/Forms/Usluga/swUslugaEditWindow.js',

	action: null,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	collapsible: false,
	copyLinkedUslugaComplex: function(options) {
		if ( this.action == 'view' ) {
			return false;
		}

		if ( !options ) {
			options = {
				 callback: Ext.emptyFn
				,copyAttributes: false
				,UslugaCategory_id: null
				,UslugaComplex_id: null
			};
		}

		var usluga_complex_id;

		if ( options.UslugaComplex_id ) {
			usluga_complex_id = options.UslugaComplex_id;
		}
		else {
			if ( typeof options.callback == 'function' ) {
				options.callback();
			}

			return false;
		}

		var base_form = this.formPanel.getForm();
		var deniedCategoryList = new Array();
		var linkedGrid = this.linkedUslugaComplexGrid.getGrid();

		deniedCategoryList.push(base_form.findField('UslugaCategory_id').getValue());
		deniedCategoryList.push(options.UslugaCategory_id);

		linkedGrid.getStore().findBy(function(r) {
			if ( !r.get('UslugaCategory_id').toString().inlist(deniedCategoryList) ) {
				deniedCategoryList.push(r.get('UslugaCategory_id').toString());
			}
		});

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Получение списка связанных услуг..." });
		loadMask.show();

		// Вытаскиваем из БД список связанных услуг и грузим в грид со связанными услугами те, которые не пересекаются по категории с уже имеющимися
		Ext.Ajax.request({
			callback: function(opt, scs, response) {
				loadMask.hide();

				if ( response.responseText.length > 0 ) {
					var index;
					var rec;
					var result = Ext.util.JSON.decode(response.responseText);
					var uslugaComplexList = new Array();

					if ( typeof result == 'object' && result.length > 0 ) {
						for ( var i = 0; i < result.length; i++ ) {
							rec = result[i];

							index = linkedGrid.getStore().findBy(function(r) {
								if ( r.get('UslugaCategory_id') == rec.UslugaCategory_id ) {
									return true;
								}
								else {
									return false;
								}
							});

							if ( index == -1 ) {
								rec.RecordStatus_Code = 0;

								if ( linkedGrid.getStore().getCount() == 1 && !linkedGrid.getStore().getAt(0).get('UslugaComplex_id') ) {
									linkedGrid.getStore().removeAll();
								}

								linkedGrid.getStore().loadData([ rec ], true);

								uslugaComplexList.push(rec.UslugaComplex_id);
							}
						}

						if ( options.copyAttributes == true && uslugaComplexList.length > 0 ) {
							this.copyUslugaComplexAttributes({
								 UslugaComplex_id: usluga_complex_id
								,uslugaComplexList: Ext.util.JSON.encode(uslugaComplexList)
							});
						}
					}

					if ( typeof options.callback == 'function' ) {
						options.callback();
					}
				};
			}.createDelegate(this),
			params: {
				 deniedCategoryList: Ext.util.JSON.encode(deniedCategoryList)
				,noLPU: 1
				,UslugaComplex_id: usluga_complex_id
			},
			url: '/?c=UslugaComplex&m=loadLinkedUslugaGrid'
		});
	},
	copyUslugaComplexAttributes: function(options) {
		if ( this.action == 'view' ) {
			return false;
		}

		if ( !options ) {
			options = {
				 callback: Ext.emptyFn
				,copyFromLinked: false
				,UslugaComplex_id: null
				,uslugaComplexList: new Array()
			};
		}

		var usluga_complex_id;

		var attrGrid = this.uslugaComplexAttributeGrid.getGrid();

		if ( options.copyFromLinked == true ) {
			if ( options.UslugaComplex_id ) {
				usluga_complex_id = options.UslugaComplex_id;
			}
			else {
				if ( typeof options.callback == 'function' ) {
					options.callback();
				}

				return false;
			}
		}
		else {
			var linkGrid = this.linkedUslugaComplexGrid.getGrid();

			var record = linkGrid.getSelectionModel().getSelected();

			if ( !record || !record.get('UslugaComplex_id') ) {
				if ( typeof options.callback == 'function' ) {
					options.callback();
				}

				return false;
			}

			usluga_complex_id = record.get('UslugaComplex_id');
		}

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Получение списка атрибутов связанной услуги..." });
		loadMask.show();

		// Вытаскиваем из БД атрибуты для выбранной услуги и грузим в грид с атрибутами те, которые не пересекаются по типу с уже имеющимися
		Ext.Ajax.request({
			callback: function(opt, scs, response) {
				loadMask.hide();

				if ( response.responseText.length > 0 ) {
					var index;
					var rec;
					var result = Ext.util.JSON.decode(response.responseText);

					if ( typeof result == 'object' && result.length > 0 ) {
						for ( var i = 0; i < result.length; i++ ) {
							rec = result[i];

							index = attrGrid.getStore().findBy(function(r) {
								if ( r.get('UslugaComplexAttributeType_id') == rec.UslugaComplexAttributeType_id ) {
									return true;
								}
								else {
									return false;
								}
							});

							if ( index == -1 ) {
								rec.pmUser_Name = getGlobalOptions().pmuser_name;
								rec.RecordStatus_Code = 0;
								rec.UslugaComplexAttribute_id = -swGenTempId(attrGrid.getStore());

								if ( attrGrid.getStore().getCount() == 1 && !attrGrid.getStore().getAt(0).get('UslugaComplexAttribute_id') ) {
									attrGrid.getStore().removeAll();
								}

								attrGrid.getStore().loadData([ rec ], true);
							}
						}
					}

					if ( typeof options.callback == 'function' ) {
						options.callback();
					}
				};
			}.createDelegate(this),
			params: {
				 UslugaComplex_id: usluga_complex_id
				,uslugaComplexList: options.uslugaComplexList
			},
			url: '/?c=UslugaComplex&m=loadUslugaComplexAttributeGrid'
		});
	},
	copyUslugaComplexContents: function(options) {
		if ( this.action == 'view' ) {
			return false;
		}

		if ( !options ) {
			options = {
				 callback: Ext.emptyFn
				,copyFromLinked: false
				,UslugaComplex_id: null
			};
		}

		var usluga_complex_id;

		var contGrid = this.uslugaComplexCompositionGrid.getGrid();

		if ( options.copyFromLinked == true ) {
			if ( options.UslugaComplex_id ) {
				usluga_complex_id = options.UslugaComplex_id;
			}
			else {
				if ( typeof options.callback == 'function' ) {
					options.callback();
				}

				return false;
			}
		}
		else {
			var linkGrid = this.linkedUslugaComplexGrid.getGrid();

			var record = linkGrid.getSelectionModel().getSelected();

			if ( !record || !record.get('UslugaComplex_id') ) {
				if ( typeof options.callback == 'function' ) {
					options.callback();
				}

				return false;
			}

			usluga_complex_id = record.get('UslugaComplex_id');
		}

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Получение состава связанной услуги..." });
		loadMask.show();

		// Вытаскиваем из БД состав выбранной услуги и грузим в состав редактируемой услуги те, которых еще нет в списке
		Ext.Ajax.request({
			callback: function(opt, scs, response) {
				loadMask.hide();

				if ( response.responseText.length > 0 ) {
					var index;
					var rec;
					var result = Ext.util.JSON.decode(response.responseText);

					if ( typeof result == 'object' && result.length > 0 ) {
						for ( var i = 0; i < result.length; i++ ) {
							rec = result[i];

							index = contGrid.getStore().findBy(function(r) {
								if ( r.get('UslugaComplex_id') == rec.UslugaComplex_id ) {
									return true;
								}
								else {
									return false;
								}
							});

							if ( index == -1 ) {
								rec.RecordStatus_Code = 0;
								rec.UslugaComplexComposition_id = -swGenTempId(contGrid.getStore());

								if ( contGrid.getStore().getCount() == 1 && !contGrid.getStore().getAt(0).get('UslugaComplexComposition_id') ) {
									contGrid.getStore().removeAll();
								}

								contGrid.getStore().loadData([ rec ], true);
							}
						}
					}
				};
			}.createDelegate(this),
			params: {
				 contents: 2
				,paging: 1
				,UslugaComplex_pid: usluga_complex_id
			},
			url: '/?c=UslugaComplex&m=loadUslugaContentsGrid'
		});
	},
	copyUslugaComplexCodeAndName: function() {
		if ( this.action == 'view' ) {
			return false;
		}

		var grid = this.linkedUslugaComplexGrid.getGrid();

		var record = grid.getSelectionModel().getSelected();

		if ( !record || !record.get('UslugaComplex_id') ) {
			return false;
		}

		var base_form = this.formPanel.getForm();

		base_form.findField('UslugaComplex_Code').setValue(record.get('UslugaComplex_Code'));
		base_form.findField('UslugaComplex_Name').setValue(record.get('UslugaComplex_Name'));
	},
	deleteGridRecord: function(object) {
		var wnd = this;
		
		if ( this.action == 'view' ) {
			return false;
		}

		if ( typeof object != 'string' || !(object.inlist([ 'LinkedUslugaComplex', 'UslugaComplexComposition', 'UslugaComplexAttribute', 'UslugaComplexPlace', 'UslugaComplexTariff', 'UslugaComplexProfile' ])) ) {
			return false;
		}
		
		var question = lang['udalit'];
		
		switch(object) {
			case 'LinkedUslugaComplex':
				question = lang['udalit_svyazannuyu_uslugu'];
				break;
			case 'UslugaComplexComposition':
				question = lang['udalit_uslugu_iz_sostava'];
				break;
			case 'UslugaComplexAttribute':
				question = lang['udalit_atribut'];
				break;
			case 'UslugaComplexPlace':
				question = lang['udalit_mesto_okazaniya'];
				break;
			case 'UslugaComplexTariff':
				question = lang['udalit_tarif'];
				break;
			case 'UslugaComplexProfile':
				question = lang['udalit_profil'];
				break;
		}
		
		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId, text, obj) {
				if ( buttonId == 'yes' ) {
					var grid = this.findById('UEF_' + object + 'Grid').getGrid();

					var idField;

					if ( object == 'LinkedUslugaComplex' ) {
						idField = 'UslugaComplex_id';
					}
					else {
						idField = object + '_id';
					}

					if ( !grid || !grid.getSelectionModel() || !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get(idField) ) {
						return false;
					}

					var record = grid.getSelectionModel().getSelected();
					
					if ( wnd.action == 'lpuedit' ) {
						var params = new Object();
						var url = "";
						
						// нужно удалять только места оказания и тарифы в режиме remote.
						switch(object) {
							case 'UslugaComplexPlace':
								params.UslugaComplexPlace_id = record.get('UslugaComplexPlace_id');
								url = "/?c=UslugaComplex&m=deleteUslugaComplexPlace";
								break;
							case 'UslugaComplexTariff':
								params.UslugaComplexTariff_id = record.get('UslugaComplexTariff_id');
								if (params.UslugaComplexTariff_id > 0) {
									url = '/?c=UslugaComplex&m=deleteUslugaComplexTariff';
								}
								break;
						}
						
						if (!Ext.isEmpty(url)) {
							Ext.Ajax.request({
								callback: function(opt, scs, response) {
									if (scs) {
										grid.getStore().remove(record);
									}
								}.createDelegate(this),
								params: params,
								url: url
							});
						} else {
							grid.getStore().remove(record);
						}
						
						return true;
					}

					var removeRecord = function() {
						switch ( Number(record.get('RecordStatus_Code')) ) {
							case 0:
								grid.getStore().remove(record);
								break;

							case 1:
							case 2:
								record.set('RecordStatus_Code', 3);
								record.commit();

								grid.getStore().filterBy(function(rec) {
									if ( Number(rec.get('RecordStatus_Code')) == 3 ) {
										return false;
									}
									else {
										return true;
									}
								});
								break;
						}

						if ( grid.getStore().getCount() == 0 ) {
							// LoadEmptyRow(grid);
						}

						if ( grid.getStore().getCount() > 0 ) {
							grid.getView().focusRow(0);
							grid.getSelectionModel().selectFirstRow();
						}
					};

					if (Number(record.get('RecordStatus_Code')) != 0 && object == 'UslugaComplexTariff') {
						var params = new Object();
						params.UslugaComplexTariff_id = record.get('UslugaComplexTariff_id');
						url = "/?c=UslugaComplex&m=checkUslugaComplexTariffUsedInEvnUsluga";

						Ext.Ajax.request({
							callback: function(opt, scs, response) {
								if (!scs) {
									sw.swMsg.alert(lang['oshibka'], lang['oshibki_pri_proverke_ispolzovaniya_tarifa']);
									return false;
								}
								var response_obj = Ext.util.JSON.decode(response.responseText);
								if (response_obj.check) {
									sw.swMsg.alert(lang['preduprejdenie'], lang['tarif_ispolzovalsya_pri_okazanii_uslug_ispolzuyte_zakryitie_tarifa']);
								} else {
									removeRecord();
								}
							}.createDelegate(this),
							params: params,
							url: url
						});
					} else {
						removeRecord();
					}
				}
			}.createDelegate(this),
			icon: Ext.MessageBox.QUESTION,
			msg: question,
			title: lang['vopros']
		});
	},
	doSave: function() {
		if ( this.formStatus == 'save' ) {
			return false;
		}

		this.formStatus = 'save';

		var form = this.formPanel;
		var base_form = form.getForm();

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

		var params = new Object();

		// Собираем данные из гридов
		var linkedUslugaComplexGrid = this.linkedUslugaComplexGrid.getGrid();
		var uslugaComplexAttributeGrid = this.uslugaComplexAttributeGrid.getGrid();
		var uslugaComplexCompositionGrid = this.uslugaComplexCompositionGrid.getGrid();
		var uslugaComplexPlaceGrid = this.uslugaComplexPlaceGrid.getGrid();
		var uslugaComplexTariffGrid = this.uslugaComplexTariffGrid.getGrid();
		var uslugaComplexProfileGrid = this.uslugaComplexProfileGrid.getGrid();

		linkedUslugaComplexGrid.getStore().clearFilter();
		uslugaComplexCompositionGrid.getStore().clearFilter();
		uslugaComplexAttributeGrid.getStore().clearFilter();
		uslugaComplexPlaceGrid.getStore().clearFilter();
		uslugaComplexTariffGrid.getStore().clearFilter();
		uslugaComplexProfileGrid.getStore().clearFilter();

		if ( linkedUslugaComplexGrid.getStore().getCount() > 0 ) {
			var linkedUslugaComplexData = getStoreRecords(linkedUslugaComplexGrid.getStore(), {
				convertDateFields: true,
				exceptionFields: [
					 'UslugaCategory_Name'
					,'UslugaComplex_Code'
					,'UslugaComplex_Name'
				]
			});

			params.linkedUslugaComplexData = Ext.util.JSON.encode(linkedUslugaComplexData);

			linkedUslugaComplexGrid.getStore().filterBy(function(rec) {
				return !(Number(rec.get('RecordStatus_Code')) == 3);
			});
		}

		if ( uslugaComplexCompositionGrid.getStore().getCount() > 0 ) {
			var uslugaComplexCompositionData = getStoreRecords(uslugaComplexCompositionGrid.getStore(), {
				convertDateFields: true,
				exceptionFields: [
					 'Lpu_Name'
					,'UslugaCategory_Name'
					,'UslugaComplex_Code'
					,'UslugaComplex_Name'
				]
			});

			params.uslugaComplexCompositionData = Ext.util.JSON.encode(uslugaComplexCompositionData);

			uslugaComplexCompositionGrid.getStore().filterBy(function(rec) {
				return !(Number(rec.get('RecordStatus_Code')) == 3);
			});
		}

		if ( uslugaComplexAttributeGrid.getStore().getCount() > 0 ) {
			var uslugaComplexAttributeData = getStoreRecords(uslugaComplexAttributeGrid.getStore(), {
				convertDateFields: true,
				exceptionFields: [
					 'AttributeValueType_Name'
					,'UslugaComplexAttributeType_Name'
					,'pmUser_Name'
				]
			});

			params.uslugaComplexAttributeData = Ext.util.JSON.encode(uslugaComplexAttributeData);

			uslugaComplexAttributeGrid.getStore().filterBy(function(rec) {
				return !(Number(rec.get('RecordStatus_Code')) == 3);
			});
		}

		if ( uslugaComplexPlaceGrid.getStore().getCount() > 0 && !Ext.isEmpty(uslugaComplexPlaceGrid.getStore().getAt(uslugaComplexPlaceGrid.getStore().getCount() - 1).get('UslugaComplexPlace_id')) ) {
			var uslugaComplexPlaceData = getStoreRecords(uslugaComplexPlaceGrid.getStore(), {
				convertDateFields: true,
				exceptionFields: [
					 'Lpu_Name'
					,'LpuBuilding_Name'
					,'LpuSection_Name'
					,'LpuUnit_Name'
					,'pmUser_Name'
				]
			});

			params.uslugaComplexPlaceData = Ext.util.JSON.encode(uslugaComplexPlaceData);

			uslugaComplexPlaceGrid.getStore().filterBy(function(rec) {
				return !(Number(rec.get('RecordStatus_Code')) == 3);
			});
		}

		if ( uslugaComplexTariffGrid.getStore().getCount() > 0 && !Ext.isEmpty(uslugaComplexTariffGrid.getStore().getAt(uslugaComplexTariffGrid.getStore().getCount() - 1).get('UslugaComplexTariff_id')) ) {
			var uslugaComplexTariffData = getStoreRecords(uslugaComplexTariffGrid.getStore(), {
				convertDateFields: true,
				exceptionFields: [
					 'Lpu_Name'
					,'PayType_Name'
					,'pmUser_Name'
					,'LpuLevel_Name'
					,'LpuSectionProfile_Name'
					,'LpuUnitType_Name'
					,'MesAgeGroup_Name'
					,'Sex_Name'
					,'UslugaComplexTariffType_Name'
				]
			});

			params.uslugaComplexTariffData = Ext.util.JSON.encode(uslugaComplexTariffData);

			uslugaComplexTariffGrid.getStore().filterBy(function(rec) {
				return !(Number(rec.get('RecordStatus_Code')) == 3);
			});
		}

		if ( uslugaComplexProfileGrid.getStore().getCount() > 0 && !Ext.isEmpty(uslugaComplexProfileGrid.getStore().getAt(uslugaComplexProfileGrid.getStore().getCount() - 1).get('UslugaComplexProfile_id')) ) {
			var uslugaComplexProfileData = getStoreRecords(uslugaComplexProfileGrid.getStore(), {
				convertDateFields: true,
				exceptionFields: [
					'LpuSectionProfile_Name'
				]
			});

			params.uslugaComplexProfileData = Ext.util.JSON.encode(uslugaComplexProfileData);

			uslugaComplexProfileGrid.getStore().filterBy(function(rec) {
				return !(Number(rec.get('RecordStatus_Code')) == 3);
			});
		}

		// Проверяем даты услуги и даты тарифов и мест оказания услуги
		// https://redmine.swan.perm.ru/issues/35896
		var
			datesOk = true,
			UslugaComplex_begDate = base_form.findField('UslugaComplex_begDate').getValue(),
			UslugaComplex_endDate = base_form.findField('UslugaComplex_endDate').getValue();

		if ( !Ext.isEmpty(UslugaComplex_begDate) ) {
			uslugaComplexPlaceGrid.getStore().each(function(rec) {
				if (
					(!Ext.isEmpty(rec.get('UslugaComplexPlace_begDate')) && rec.get('UslugaComplexPlace_begDate') < UslugaComplex_begDate)
					|| (!Ext.isEmpty(rec.get('UslugaComplexPlace_endDate')) && rec.get('UslugaComplexPlace_endDate') < UslugaComplex_begDate)
				) {
					datesOk = false;
				}
			});

			if ( datesOk == false ) {
				sw.swMsg.alert(lang['oshibka'], lang['obnarujenyi_datyi_deystviya_zapisey_v_tablitse_mesta_okazaniya_kotoryie_vyihodyat_za_ramki_perioda_deystviya_uslugi_ispravte_datyi_i_povtorite_sohranenie']);
				this.formStatus = 'edit';
				return false;
			}

			uslugaComplexTariffGrid.getStore().each(function(rec) {
				if (
					(!Ext.isEmpty(rec.get('UslugaComplexTariff_begDate')) && rec.get('UslugaComplexTariff_begDate') < UslugaComplex_begDate)
					|| (!Ext.isEmpty(rec.get('UslugaComplexTariff_endDate')) && rec.get('UslugaComplexTariff_endDate') < UslugaComplex_begDate)
				) {
					datesOk = false;
				}
			});

			if ( datesOk == false ) {
				sw.swMsg.alert(lang['oshibka'], lang['obnarujenyi_datyi_deystviya_zapisey_v_tablitse_tarifyi_kotoryie_vyihodyat_za_ramki_perioda_deystviya_uslugi_ispravte_datyi_i_povtorite_sohranenie']);
				this.formStatus = 'edit';
				return false;
			}

			uslugaComplexProfileGrid.getStore().each(function(rec) {
				if (
					(!Ext.isEmpty(rec.get('UslugaComplexProfile_begDate')) && rec.get('UslugaComplexProfile_begDate') < UslugaComplex_begDate)
					|| (!Ext.isEmpty(rec.get('UslugaComplexProfile_endDate')) && rec.get('UslugaComplexProfile_endDate') < UslugaComplex_begDate)
				) {
					datesOk = false;
				}
			});

			if ( datesOk == false ) {
				sw.swMsg.alert(lang['oshibka'], lang['obnarujenyi_datyi_deystviya_zapisey_v_tablitse_profili_kotoryie_vyihodyat_za_ramki_perioda_deystviya_uslugi_ispravte_datyi_i_povtorite_sohranenie']);
				this.formStatus = 'edit';
				return false;
			}
		}

		if ( !Ext.isEmpty(UslugaComplex_endDate) ) {
			uslugaComplexPlaceGrid.getStore().each(function(rec) {
				if (
					(!Ext.isEmpty(rec.get('UslugaComplexPlace_begDate')) && rec.get('UslugaComplexPlace_begDate') > UslugaComplex_endDate)
					|| (!Ext.isEmpty(rec.get('UslugaComplexPlace_endDate')) && rec.get('UslugaComplexPlace_endDate') > UslugaComplex_endDate)
				) {
					datesOk = false;
				}
			});

			if ( datesOk == false ) {
				sw.swMsg.alert(lang['oshibka'], lang['obnarujenyi_datyi_deystviya_zapisey_v_tablitse_mesta_okazaniya_kotoryie_vyihodyat_za_ramki_perioda_deystviya_uslugi_ispravte_datyi_i_povtorite_sohranenie']);
				this.formStatus = 'edit';
				return false;
			}

			uslugaComplexTariffGrid.getStore().each(function(rec) {
				if (
					(!Ext.isEmpty(rec.get('UslugaComplexTariff_begDate')) && rec.get('UslugaComplexTariff_begDate') > UslugaComplex_endDate)
					|| (!Ext.isEmpty(rec.get('UslugaComplexTariff_endDate')) && rec.get('UslugaComplexTariff_endDate') > UslugaComplex_endDate)
				) {
					datesOk = false;
				}
			});

			if ( datesOk == false ) {
				sw.swMsg.alert(lang['oshibka'], lang['obnarujenyi_datyi_deystviya_zapisey_v_tablitse_tarifyi_kotoryie_vyihodyat_za_ramki_perioda_deystviya_uslugi_ispravte_datyi_i_povtorite_sohranenie']);
				this.formStatus = 'edit';
				return false;
			}

			uslugaComplexProfileGrid.getStore().each(function(rec) {
				if (
					(!Ext.isEmpty(rec.get('UslugaComplexProfile_begDate')) && rec.get('UslugaComplexProfile_begDate') > UslugaComplex_endDate)
					|| (!Ext.isEmpty(rec.get('UslugaComplexProfile_endDate')) && rec.get('UslugaComplexProfile_endDate') > UslugaComplex_endDate)
				) {
					datesOk = false;
				}
			});

			if ( datesOk == false ) {
				sw.swMsg.alert(lang['oshibka'], lang['obnarujenyi_datyi_deystviya_zapisey_v_tablitse_profili_kotoryie_vyihodyat_za_ramki_perioda_deystviya_uslugi_ispravte_datyi_i_povtorite_sohranenie']);
				this.formStatus = 'edit';
				return false;
			}
		}
/*
		// Пока просто смотрим, что пойдет на сервер
		log(params);
		this.formStatus = 'edit';
		return false;
*/
		var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Подождите, идет сохранение услуги..." });
		loadMask.show();
		
		base_form.submit({
			failure: function(result_form, action) {
				this.formStatus = 'edit';
				loadMask.hide();

				if ( action.result ) {
					if ( action.result.Error_Msg ) {
						sw.swMsg.alert(lang['oshibka'], action.result.Error_Msg);
					}
					else {
						sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_1]']);
					}
				}
			}.createDelegate(this),
			params: params,
			success: function(result_form, action) {
				this.formStatus = 'edit';
				loadMask.hide();

				if ( action.result ) {
					if ( action.result.UslugaComplex_id > 0 ) {
						base_form.findField('UslugaComplex_id').setValue(action.result.UslugaComplex_id);

						var data = new Object();

						data.uslugaData = [{
							'UslugaComplex_id': base_form.findField('UslugaComplex_id').getValue(),
							'accessType': 'edit'
						}];

						this.callback(data);
						this.hide();
					}
					else {
						if ( action.result.Error_Msg ) {
							sw.swMsg.alert(lang['oshibka'], action.result.Error_Msg);
						}
						else {
							sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_3]']);
						}
					}
				}
				else {
					sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_2]']);
				}
			}.createDelegate(this)
		});
	},
	draggable: true,
	enableEdit: function(enable) {
		var base_form = this.formPanel.getForm();
		var form_fields = new Array(
			 'UslugaComplex_ACode'
			,'UslugaComplex_begDate'
			,'UslugaComplex_Code'
			,'UslugaComplex_endDate'
			,'UslugaComplex_Name'
			,'UslugaComplex_Nick'
			,'UslugaComplex_UET'
			,'XmlTemplate_id'
		);
		var i = 0;

		for ( i = 0; i < form_fields.length; i++ ) {
			if ( enable ) {
				base_form.findField(form_fields[i]).enable();
			}
			else {
				base_form.findField(form_fields[i]).disable();
			}
		}

		if ( enable ) {
			this.buttons[0].show();
		} else {
			this.buttons[0].hide();
		}

        base_form.findField('UslugaComplex_isPackage').setContainerVisible(this.isAllowPackageVisible());
        base_form.findField('UslugaComplex_isPackage').setDisabled(!this.isPackageEnable());
	},
    /**
     * Пакет услуг может быть создан только в категории "Услуги ЛПУ"
     * @return {Boolean}
     */
    isAllowPackageVisible: function() {
        var base_form = this.formPanel.getForm();
        return (base_form.findField('UslugaCategory_SysNick').getValue().inlist(['lpu']));
    },
    /**
     * Чекбокс "Пакет услуг" доступен только для вновь создаваемой услуги с категорией "Услуги ЛПУ"
     * @return {Boolean}
     */
    isPackageEnable: function() {
        return (this.isAllowPackageVisible() && 'add' == this.action);
    },
	formStatus: 'edit',
	height: 550,
	id: 'UslugaEditWindow',
	sprCmp: null,
	onChangeAttributeValueType: function(AttributeValueType_id, UslugaComplexAttributeType_DBTable) {
		var win = this;
		var base_form = this.formPanel.getForm();
		this.sprCmp = null;

		win.sprPanel.items.each(function(item) {
			var itemParentNode = false;
			if (item.el.dom.parentNode && item.el.dom.parentNode.parentNode && item.el.dom.parentNode.parentNode.parentNode) {
				itemParentNode = item.el.dom.parentNode.parentNode.parentNode;
			}
			win.sprPanel.remove(item); // auto destroy child item
			if (itemParentNode) {
				Ext.fly(itemParentNode).remove(); // remove container element
			}
		});
		// win.sprPanel.removeAll();

		base_form.findField('UslugaComplexAttribute_Int').hideContainer();
		base_form.findField('UslugaComplexAttribute_Float').hideContainer();
		base_form.findField('UslugaComplexAttribute_Text').hideContainer();

		if (!Ext.isEmpty(AttributeValueType_id)) {
			switch (AttributeValueType_id) {
				case 1:
					base_form.findField('UslugaComplexAttribute_Int').showContainer();
					break;
				case 2:
					base_form.findField('UslugaComplexAttribute_Float').showContainer();
					break;
				case 6:
					// надо создать комбо со справочником из UslugaComplexAttributeType_DBTable
					this.sprCmp = new sw.Promed.SwCommonSprCombo({
						fieldLabel: lang['znachenie'],
						width: 150,
						lastQuery: '',
						comboSubject: UslugaComplexAttributeType_DBTable,
						tabIndex: TABINDEX_UCAEW + 1
					});

					this.sprPanel.add(this.sprCmp);
					this.sprCmp.getStore().load({callback: function(record){
						if (UslugaComplexAttributeType_DBTable == 'MesAgeGroup') {
							win.sprCmp.getStore().filterBy(function(rec){
								return rec.get('MesAgeGroup_Code').inlist(['1', '2']);
							});
						}
					}});

					this.formPanel.doLayout();

					break;
				default:
					base_form.findField('UslugaComplexAttribute_Text').showContainer();
					break;
			}
		}
	},
	initComponent: function() {
		var form = this;

		// Таблица "Связанные услуги"
		form.linkedUslugaComplexGrid = new sw.Promed.ViewFrame({
			actions: [
				{ name: 'action_add', handler: function() { form.openUslugaComplexLinkedEditWindow('add'); } },
				{ name: 'action_edit', disabled: true, hidden: true },
				{ name: 'action_view', disabled: true, hidden: true },
				{ name: 'action_delete', handler: function() { this.deleteGridRecord('LinkedUslugaComplex'); }.createDelegate(this) },
				{ name: 'action_refresh', disabled: true, hidden: true }
			],
			autoLoadData: false,
			border: false,
			dataUrl: '/?c=UslugaComplex&m=loadLinkedUslugaGrid',
			id: 'UEF_LinkedUslugaComplexGrid',
			onDblClick: function() {
				if ( !this.ViewActions.action_edit.isDisabled() ) {
					this.ViewActions.action_edit.execute();
				}
			},
			onEnter: function() {
				if ( !this.ViewActions.action_edit.isDisabled() ) {
					this.ViewActions.action_edit.execute();
				}
			},
			onLoadData: function() {
				//
			},
			onRowSelect: function(sm, index, record) {
				if ( record.get('UslugaComplex_id') && form.action == 'edit' ) {
					this.setActionDisabled('action_delete', false);
					this.setActionDisabled('action_copycodeandname', false);
					this.setActionDisabled('action_copyattributes', false);
					this.setActionDisabled('action_copycontents', false);
				}
				else {
					this.setActionDisabled('action_delete', true);
					this.setActionDisabled('action_copycodeandname', true);
					this.setActionDisabled('action_copyattributes', true);
					this.setActionDisabled('action_copycontents', true);
				}
			},
			paging: false,
			region: 'center',
			stringfields: [
				{ name: 'UslugaComplex_id', type: 'int', header: 'ID', key: true },
				{ name: 'RecordStatus_Code', type: 'int', hidden: true },
				{ name: 'UslugaCategory_id', type: 'int', hidden: true },
				{ name: 'UslugaCategory_SysNick', type: 'string', hidden: true },
				{ name: 'CopyAllLinked', type: 'boolean', hidden: true },
				{ name: 'CopyAttributes', type: 'boolean', hidden: true },
				{ name: 'CopyContent', type: 'boolean', hidden: true },
				{ name: 'UslugaCategory_Name', header: lang['kategoriya'], width: 150 },
				{ name: 'UslugaComplex_Code', header: lang['kod'], width: 80 },
				{ name: 'UslugaComplex_Name', header: lang['naimenovanie'], id: 'autoexpand' }
			]
		});

		// Таблица "Состав услуги"
		form.uslugaComplexCompositionGrid = new sw.Promed.ViewFrame( {
			actions: [
				{ name: 'action_add', handler: function() { form.openUslugaComplexCompositionEditWindow('add'); } },
				{ name: 'action_edit', handler: function() { form.openUslugaComplexCompositionEditWindow('edit'); } },
				{ name: 'action_view', handler: function() { form.openUslugaComplexCompositionEditWindow('view'); } },
				{ name: 'action_delete', handler: function() { form.deleteGridRecord('UslugaComplexComposition'); } },
				{ name: 'action_refresh', disabled: true, hidden: true }
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 150,
			autoLoadData: false,
			border: false,
			dataUrl: '/?c=UslugaComplex&m=loadUslugaContentsGrid',
			// editformclassname: 'swUslugaTreeEditWindow',
			// height: 300,
			id: 'UEF_UslugaComplexCompositionGrid',
			object: 'UslugaComplex',
			onDblClick: function(grid, number, object){
				if ( !this.ViewActions.action_edit.isDisabled() ) {
					this.ViewActions.action_edit.execute();
				}
			},
			onEnter: function() {
				if ( !this.ViewActions.action_edit.isDisabled() ) {
					this.ViewActions.action_edit.execute();
				}
			},
			onLoadData: function() {
				// this.getAction('action_add').setDisabled(this.getParam('RegistryStatus_id')!=3);
			},
			onRowSelect: function(sm, rowIdx, record) {
				var base_form = form.formPanel.getForm();
				
				this.setActionDisabled('action_edit', true);
				this.setActionDisabled('action_view', true);
				this.setActionDisabled('action_delete', true);

				if ( !record.get('UslugaComplex_id') ) {
					return false;
				}

				this.setActionDisabled('action_view', false);

				if ( form.action == 'edit' && (!(base_form.findField('UslugaCategory_SysNick').getValue().inlist(['gost2004','gost2011','classmedus','MedOp'])))) {
					this.setActionDisabled('action_edit', false);
					this.setActionDisabled('action_delete', false);
				}
			},
			paging: false,
			region: 'center',
			stringfields: [
				{ name: 'UslugaComplexComposition_id', type: 'int', header: 'ID', key: true },
				{ name: 'UslugaComplex_id', type: 'int', hidden: true },
				{ name: 'RecordStatus_Code', type: 'int', hidden: true },
				{ name: 'UslugaCategory_id', type: 'int', hidden: true },
				{ name: 'UslugaCategory_Name', header: lang['kategoriya'], width: 150 },
				{ name: 'UslugaComplex_Code', header: lang['kod'], width: 80 },
				{ name: 'UslugaComplex_Name', header: lang['naimenovanie'], id: 'autoexpand' },
				{ name: 'Lpu_Name', header: lang['lpu'], width: 150 }
			]
		});

		// Таблица "Атрибуты"
		form.uslugaComplexAttributeGrid = new sw.Promed.ViewFrame({
			actions: [
				{ name: 'action_add', handler: function() { this.openUslugaComplexAttributeEditWindow('add'); }.createDelegate(this) },
				{ name: 'action_edit', handler: function() { this.openUslugaComplexAttributeEditWindow('edit'); }.createDelegate(this) },
				{ name: 'action_view', handler: function() { this.openUslugaComplexAttributeEditWindow('view'); }.createDelegate(this) },
				{ name: 'action_delete', handler: function() { this.deleteGridRecord('UslugaComplexAttribute'); }.createDelegate(this) },
				{ name: 'action_refresh', disabled: true, hidden: true },
				{ name: 'action_print' }
			],
			autoLoadData: false,
			border: false,
			dataUrl: '/?c=UslugaComplex&m=loadUslugaComplexAttributeGrid',
			id: 'UEF_UslugaComplexAttributeGrid',
			onDblClick: function() {
				if ( !this.ViewActions.action_edit.isDisabled() ) {
					this.ViewActions.action_edit.execute();
				}
			},
			onEnter: function() {
				if ( !this.ViewActions.action_edit.isDisabled() ) {
					this.ViewActions.action_edit.execute();
				}
			},
			onLoadData: function() {
				//
			},
			onRowSelect: function(sm, index, record) {
				this.setActionDisabled('action_edit', true);
				this.setActionDisabled('action_view', true);
				this.setActionDisabled('action_delete', true);

				if ( !record.get('UslugaComplexAttribute_id') ) {
					return false;
				}

				this.setActionDisabled('action_view', false);

				if ( form.action == 'edit' ) {
					this.setActionDisabled('action_edit', false);
					this.setActionDisabled('action_delete', false);
				}
			},
			paging: false,
			region: 'center',
			stringfields: [
				{ name: 'UslugaComplexAttribute_id', type: 'int', header: 'ID', key: true },
				{ name: 'RecordStatus_Code', type: 'int', hidden: true },
				{ name: 'UslugaComplexAttributeType_id', type: 'int', hidden: true },
				{ name: 'UslugaComplexAttribute_Float', type: 'float', hidden: true },
				{ name: 'UslugaComplexAttribute_Int', type: 'int', hidden: true },
				{ name: 'UslugaComplexAttribute_Text', type: 'string', hidden: true },
				{ name: 'UslugaComplexAttribute_DBTableID', type: 'int', hidden: true },
				{ name: 'AttributeValueType_Name', type: 'string', header: lang['tip'], width: 70 },
				{ name: 'UslugaComplexAttribute_Value', type: 'string', header: lang['znachenie'], width: 100 },
				{ name: 'UslugaComplexAttributeType_Name', type: 'string', header: lang['naimenovanie'], id: 'autoexpand' },
				{ name: 'UslugaComplexAttribute_begDate', type: 'date', header: lang['data_nachala'], width: 100 },
				{ name: 'UslugaComplexAttribute_endDate', type: 'date', header: lang['data_okonchaniya'], width: 100 },
				{ name: 'pmUser_Name', type: 'string', header: lang['polzovatel'], width: 150 }
			]
		});

		// Таблица "Места выполнения"
		form.uslugaComplexPlaceGrid = new sw.Promed.ViewFrame({
			actions: [
				{ name: 'action_add', handler: function() { this.openUslugaComplexPlaceEditWindow('add'); }.createDelegate(this) },
				{ name: 'action_edit', handler: function() { this.openUslugaComplexPlaceEditWindow('edit'); }.createDelegate(this) },
				{ name: 'action_view', handler: function() { this.openUslugaComplexPlaceEditWindow('view'); }.createDelegate(this) },
				{ name: 'action_delete', handler: function() { this.deleteGridRecord('UslugaComplexPlace'); }.createDelegate(this) },
				{ name: 'action_refresh', disabled: true, hidden: true },
				{ name: 'action_print' }
			],
			autoLoadData: false,
			border: false,
			dataUrl: '/?c=UslugaComplex&m=loadUslugaComplexPlaceGrid',
			id: 'UEF_UslugaComplexPlaceGrid',
			onDblClick: function() {
				if ( !this.ViewActions.action_edit.isDisabled() ) {
					this.ViewActions.action_edit.execute();
				}
			},
			onEnter: function() {
				if ( !this.ViewActions.action_edit.isDisabled() ) {
					this.ViewActions.action_edit.execute();
				}
			},
			onLoadData: function() {
				//
			},
			onRowSelect: function(sm, index, record) {
				this.setActionDisabled('action_edit', true);
				this.setActionDisabled('action_view', true);
				this.setActionDisabled('action_delete', true);

				if ( !record.get('UslugaComplexPlace_id') ) {
					return false;
				}

				this.setActionDisabled('action_view', false);

				if ( form.action == 'edit' || form.action == 'lpuedit' ) {
					this.setActionDisabled('action_edit', false);
					this.setActionDisabled('action_delete', false);
				}
			},
			paging: false,
			region: 'center',
			stringfields: [
				{ name: 'UslugaComplexPlace_id', type: 'int', header: 'ID', key: true },
				{ name: 'RecordStatus_Code', type: 'int', hidden: true },
				{ name: 'Lpu_id', type: 'int', hidden: true },
				{ name: 'LpuBuilding_id', type: 'int', hidden: true },
				{ name: 'LpuSection_id', type: 'int', hidden: true },
				{ name: 'LpuUnit_id', type: 'int', hidden: true },
				{ name: 'Lpu_Name', type: 'string', header: lang['lpu'], width: 100 },
				{ name: 'LpuBuilding_Name', type: 'string', header: lang['podrazdelenie'], width: 100 },
				{ name: 'LpuUnit_Name', type: 'string', header: lang['gruppa_otdeleniy'], width: 100 },
				{ name: 'LpuSection_Name', type: 'string', header: lang['otdelenie'], width: 100 },
				{ name: 'UslugaComplexPlace_begDate', type: 'date', header: lang['data_nachala'], width: 100 },
				{ name: 'UslugaComplexPlace_endDate', type: 'date', header: lang['data_okonchaniya'], width: 100 },
				{ name: 'pmUser_Name', type: 'string', header: lang['polzovatel'], id: 'autoexpand' }
			]
		});

		// Таблица "Тарифы"
		form.uslugaComplexTariffGrid = new sw.Promed.ViewFrame({
			actions: [
				{ name: 'action_add', handler: function() { this.openUslugaComplexTariffEditWindow('add'); }.createDelegate(this) },
				{ name: 'action_edit', handler: function() { this.openUslugaComplexTariffEditWindow('edit'); }.createDelegate(this) },
				{ name: 'action_view', handler: function() { this.openUslugaComplexTariffEditWindow('view'); }.createDelegate(this) },
				{ name: 'action_delete', handler: function() { this.deleteGridRecord('UslugaComplexTariff'); }.createDelegate(this) },
				{ name: 'action_refresh', disabled: true, hidden: true },
				{ name: 'action_print' }
			],
			autoLoadData: false,
			border: false,
			dataUrl: '/?c=UslugaComplex&m=loadUslugaComplexTariffGrid',
			id: 'UEF_UslugaComplexTariffGrid',
			onDblClick: function() {
				if ( !this.ViewActions.action_edit.isDisabled() ) {
					this.ViewActions.action_edit.execute();
				}
			},
			onEnter: function() {
				if ( !this.ViewActions.action_edit.isDisabled() ) {
					this.ViewActions.action_edit.execute();
				}
			},
			onLoadData: function() {
				//
			},
			onRowSelect: function(sm, index, record) {
				this.setActionDisabled('action_edit', true);
				this.setActionDisabled('action_view', true);
				this.setActionDisabled('action_delete', true);

				if ( !record.get('UslugaComplexTariff_id') ) {
					return false;
				}

				this.setActionDisabled('action_view', false);

				if ( form.action == 'edit' || form.action == 'lpuedit' ) {
					this.setActionDisabled('action_edit', false);
					this.setActionDisabled('action_delete', false);
				}
			},
			paging: false,
			region: 'center',
			stringfields: [
				{ name: 'UslugaComplexTariff_id', type: 'int', header: 'ID', key: true },
				{ name: 'RecordStatus_Code', type: 'int', hidden: true },
				{ name: 'UslugaComplexTariffType_id', type: 'int', hidden: true },
				{ name: 'Lpu_id', type: 'int', hidden: true },
				{ name: 'LpuBuilding_id', type: 'int', hidden: true },
				{ name: 'LpuSection_id', type: 'int', hidden: true },
				{ name: 'LpuUnit_id', type: 'int', hidden: true },
				{ name: 'MedService_id', type: 'int', hidden: true },
				{ name: 'PayType_id', type: 'int', hidden: true },
				{ name: 'LpuLevel_id', type: 'int', hidden: true },
				{ name: 'LpuSectionProfile_id', type: 'int', hidden: true },
				{ name: 'LpuUnitType_id', type: 'int', hidden: true },
				{ name: 'MesAgeGroup_id', type: 'int', hidden: true },
				{ name: 'Sex_id', type: 'int', hidden: true },
				{ name: 'EvnUsluga_setDate', type: 'date', hidden: true },
				{ name: 'UslugaComplexTariff_Code', type: 'string', header: lang['kod'], width: 50 },
				{ name: 'UslugaComplexTariff_Name', type: 'string', header: lang['naimenovanie'], width: 100 },
				{ name: 'PayType_Name', type: 'string', header: lang['vid_oplatyi'], width: 80 },
				{ name: 'UslugaComplexTariffType_Name', type: 'string', header: lang['tip_tarifa'], width: 100 },
				{ name: 'LpuLevel_Name', type: 'string', header: lang['uroven_lpu'], width: 100 },
				{ name: 'Lpu_Name', type: 'string', header: lang['lpu'], width: 200 },
				{ name: 'LpuSectionProfile_Name', type: 'string', header: lang['profil'], width: 150 },
				{ name: 'LpuUnitType_Name', type: 'string', header: lang['vid_med_pomoschi'], width: 100 },
				{ name: 'MesAgeGroup_Name', type: 'string', header: lang['vozrastnaya_gruppa'], width: 100 },
				{ name: 'Sex_Name', type: 'string', header: lang['pol_patsienta'], width: 80 },
				{ name: 'UslugaComplexTariff_Tariff', type: 'float', header: lang['tarif'], width: 80 },
				{ name: 'UslugaComplexTariff_UED', type: 'float', header: lang['uet_vracha'], width: 80 },
				{ name: 'UslugaComplexTariff_UEM', type: 'float', header: lang['uet_sr_medpersonala'], width: 80 },
				{ name: 'UslugaComplexTariff_begDate', type: 'date', header: lang['data_nachala'], width: 80 },
				{ name: 'UslugaComplexTariff_endDate', type: 'date', header: lang['data_okonchaniya'], width: 80 },
				{ name: 'pmUser_Name', type: 'string', header: lang['polzovatel'], id: 'autoexpand' }
			]
		});

		// Таблица "Профили"
		form.uslugaComplexProfileGrid = new sw.Promed.ViewFrame({
			actions: [
				{ name: 'action_add', handler: function() { this.openUslugaComplexProfileEditWindow('add'); }.createDelegate(this) },
				{ name: 'action_edit', handler: function() { this.openUslugaComplexProfileEditWindow('edit'); }.createDelegate(this) },
				{ name: 'action_view', handler: function() { this.openUslugaComplexProfileEditWindow('view'); }.createDelegate(this) },
				{ name: 'action_delete', handler: function() { this.deleteGridRecord('UslugaComplexProfile'); }.createDelegate(this) },
				{ name: 'action_refresh', disabled: true, hidden: true },
				{ name: 'action_print' }
			],
			autoLoadData: false,
			border: false,
			dataUrl: '/?c=UslugaComplex&m=loadUslugaComplexProfileGrid',
			id: 'UEF_UslugaComplexProfileGrid',
			onDblClick: function() {
				if ( !this.ViewActions.action_edit.isDisabled() ) {
					this.ViewActions.action_edit.execute();
				}
			},
			onEnter: function() {
				if ( !this.ViewActions.action_edit.isDisabled() ) {
					this.ViewActions.action_edit.execute();
				}
			},
			onLoadData: function() {
				//
			},
			onRowSelect: function(sm, index, record) {
				this.setActionDisabled('action_edit', true);
				this.setActionDisabled('action_view', true);
				this.setActionDisabled('action_delete', true);

				if ( !record.get('UslugaComplexProfile_id') ) {
					return false;
				}

				this.setActionDisabled('action_view', false);

				if ( form.action == 'edit' || form.action == 'lpuedit' ) {
					this.setActionDisabled('action_edit', false);
					this.setActionDisabled('action_delete', false);
				}
			},
			paging: false,
			region: 'center',
			stringfields: [
				{ name: 'UslugaComplexProfile_id', type: 'int', header: 'ID', key: true },
				{ name: 'RecordStatus_Code', type: 'int', hidden: true },
				{ name: 'LpuSectionProfile_id', type: 'int', hidden: true },
				{ name: 'LpuSectionProfile_Name', type: 'string', header: lang['profil'], width: 300 },
				{ name: 'UslugaComplexProfile_begDate', type: 'date', header: lang['data_nachala'], width: 80 },
				{ name: 'UslugaComplexProfile_endDate', type: 'date', header: lang['data_okonchaniya'], width: 80 },
				{ name: 'pmUser_Name', type: 'string', header: lang['polzovatel'], id: 'autoexpand' }
			]
		});

		// Панель с гридом "Связанные услуги"
		form.linkedUslugaComplexPanel = new sw.Promed.Panel({
			border: true,
			collapsible: true,
			height: 200,
			id: 'UEF_LinkedUslugaComplexPanel',
			isLoaded: false,
			layout: 'border',
			listeners: {
				'expand': function(panel) {
					if ( panel.isLoaded === false ) {
						panel.isLoaded = true;
						form.linkedUslugaComplexGrid.getGrid().getStore().load({
							params: {
								UslugaComplex_id: form.formPanel.getForm().findField('UslugaComplex_id').getValue()
							}
						});
					}

					panel.doLayout();
				}
			},
			style: 'margin-bottom: 0.5em;',
			title: lang['svyazannyie_uslugi'],

			items: [
				form.linkedUslugaComplexGrid
			]
		});

		// Панель с гридом "Состав услуги"
		form.uslugaComplexCompositionPanel = new sw.Promed.Panel({
			border: true,
			collapsible: true,
			height: 200,
			id: 'UEF_UslugaComplexCompositionPanel',
			isLoaded: false,
			layout: 'border',
			listeners: {
				'expand': function(panel) {
					if ( panel.isLoaded === false ) {
						panel.isLoaded = true;
						form.uslugaComplexCompositionGrid.getGrid().getStore().load({
							params: {
								 contents: 2
								,paging: 1
								,UslugaComplex_pid: form.formPanel.getForm().findField('UslugaComplex_id').getValue()
							}
						});
					}

					panel.doLayout();
				}
			},
			style: 'margin-bottom: 0.5em;',
			title: lang['sostav_uslugi'],

			items: [
				form.uslugaComplexCompositionGrid
			]
		});

		form.sprPanel = new sw.Promed.Panel({
			border: false,
			layout: 'form',
			frame: false,
			labelWidth: 160
		});

		// Панель с гридом "Атрибуты"
		form.uslugaComplexAttributePanel = new sw.Promed.Panel({
			border: true,
			collapsible: true,
			height: 200,
			id: 'UEF_UslugaComplexAttributePanel',
			isLoaded: false,
			layout: 'border',
			listeners: {
				'expand': function(panel) {
					if ( panel.isLoaded === false ) {
						panel.isLoaded = true;
						form.uslugaComplexAttributeGrid.getGrid().getStore().load({
							params: {
								UslugaComplex_id: form.formPanel.getForm().findField('UslugaComplex_id').getValue()
							}
						});
					}

					panel.doLayout();
				}
			},
			style: 'margin-bottom: 0.5em;',
			title: lang['atributyi'],

			items: [{
				height: 30,
				region: 'north',
				bodyStyle: 'padding-top: 3px;',
				border: false,
				layout: 'column',
				items: [{
					layout: 'form',
					border: false,
					labelWidth: 100,
					items: [{
						hiddenName: 'UslugaComplexAttributeType_id',
						comboSubject: 'UslugaComplexAttributeType',
						fieldLabel: lang['tip_atributa'],
						typeCode: 'int',
						moreFields: [
							{name: 'AttributeValueType_id', mapping: 'AttributeValueType_id'},
							{name: 'UslugaComplexAttributeType_DBTable', mapping: 'UslugaComplexAttributeType_DBTable'},
							{name: 'UslugaComplexAttributeType_IsSet', mapping: 'UslugaComplexAttributeType_IsSet'}
						],
						listeners: {
							'beforeselect': function (combo, record) {
								form.onChangeAttributeValueType(record.get('AttributeValueType_id'), record.get('UslugaComplexAttributeType_DBTable'));
							}.createDelegate(this)
						},
						width: 200,
						xtype: 'swcommonsprcombo'
					}]
				}, {
					layout: 'form',
					border: false,
					labelWidth: 100,
					items: [this.sprPanel,
					{
						fieldLabel: lang['znachenie'],
						width: 150,
						allowDecimals:false,
						name: 'UslugaComplexAttribute_Int',
						xtype:'numberfield'
					},
					{
						fieldLabel: lang['znachenie'],
						width: 150,
						allowDecimals:true,
						name: 'UslugaComplexAttribute_Float',
						xtype:'numberfield'
					},
					{
						fieldLabel: lang['znachenie'],
						width: 150,
						name: 'UslugaComplexAttribute_Text',
						xtype: 'textfield'
					}]
				}, {
					layout: 'form',
					style: 'padding-left: 5px;',
					border: false,
					items: [{
						xtype: 'button',
						text: lang['nayti'],
						iconCls: 'search16',
						handler: function() {
							var base_form = form.formPanel.getForm();

							form.uslugaComplexAttributeGrid.getGrid().getStore().filterBy(function(rec) {
								var showIt = true;

								if (!Ext.isEmpty(base_form.findField('UslugaComplexAttributeType_id').getValue())) {
									if (base_form.findField('UslugaComplexAttributeType_id').getValue() != rec.get('UslugaComplexAttributeType_id')) {
										showIt = false;
									}

									switch (base_form.findField('UslugaComplexAttributeType_id').getFieldValue('AttributeValueType_id')) {
										case 1:
											if (!Ext.isEmpty(base_form.findField('UslugaComplexAttribute_Int').getValue()) && base_form.findField('UslugaComplexAttribute_Int').getValue() != rec.get('UslugaComplexAttribute_Int')) {
												showIt = false;
											}
											break;
										case 2:
											if (!Ext.isEmpty(base_form.findField('UslugaComplexAttribute_Float').getValue()) && base_form.findField('UslugaComplexAttribute_Float').getValue() != rec.get('UslugaComplexAttribute_Float')) {
												showIt = false;
											}
											break;
										case 6:
											if (form.sprCmp && form.sprCmp.getValue) {
												if (!Ext.isEmpty(form.sprCmp.getValue()) && form.sprCmp.getValue() != rec.get('UslugaComplexAttribute_DBTableID')) {
													showIt = false;
												}
											}
											break;
										default:
											if (!Ext.isEmpty(base_form.findField('UslugaComplexAttribute_Text').getValue()) && base_form.findField('UslugaComplexAttribute_Text').getValue() != rec.get('UslugaComplexAttribute_Text')) {
												showIt = false;
											}
											break;
									}
								}
								if ( Number(rec.get('RecordStatus_Code')) == 3 ) {
									return false;
								}
								else {
									return showIt;
								}
							});
						}
					}]
				}, {
					layout: 'form',
					style: 'padding-left: 5px;',
					border: false,
					items: [{
						xtype: 'button',
						text: lang['sbros'],
						iconCls: 'reset16',
						handler: function() {
							var base_form = form.formPanel.getForm();

							base_form.findField('UslugaComplexAttributeType_id').clearValue();
							base_form.findField('UslugaComplexAttribute_Int').setValue(null);
							base_form.findField('UslugaComplexAttribute_Float').setValue(null);
							base_form.findField('UslugaComplexAttribute_Text').setValue(null);
							form.onChangeAttributeValueType(null, null);

							form.uslugaComplexAttributeGrid.getGrid().getStore().filterBy(function(rec) {
								if ( Number(rec.get('RecordStatus_Code')) == 3 ) {
									return false;
								}
								else {
									return true;
								}
							});
						}
					}]
				}],
				xtype: 'panel'
			}, form.uslugaComplexAttributeGrid
			]
		});

		// Панель с гридом "Места оказания"
		form.uslugaComplexPlacePanel = new sw.Promed.Panel({
			border: true,
			collapsible: true,
			height: 200,
			id: 'UEF_UslugaComplexPlacePanel',
			isLoaded: false,
			layout: 'border',
			listeners: {
				'expand': function(panel) {
					if ( panel.isLoaded === false ) {
						panel.isLoaded = true;
						form.uslugaComplexPlaceGrid.getGrid().getStore().load({
							params: {
								UslugaComplex_id: form.formPanel.getForm().findField('UslugaComplex_id').getValue(),
								LpuEditFlag: (form.action == 'lpuedit')?1:0
							}
						});
					}

					panel.doLayout();
				}
			},
			style: 'margin-bottom: 0.5em;',
			title: lang['mesta_okazaniya'],

			items: [
				form.uslugaComplexPlaceGrid
			]
		});

		// Панель с гридом "Тарифы"
		form.uslugaComplexTariffPanel = new sw.Promed.Panel({
			border: true,
			collapsible: true,
			height: 200,
			id: 'UEF_UslugaComplexTariffPanel',
			isLoaded: false,
			layout: 'border',
			listeners: {
				'expand': function(panel) {
					if ( panel.isLoaded === false ) {
						panel.isLoaded = true;
						form.uslugaComplexTariffGrid.getGrid().getStore().load({
							params: {
								UslugaComplex_id: form.formPanel.getForm().findField('UslugaComplex_id').getValue(),
								LpuEditFlag: (form.action == 'lpuedit')?1:0
							}
						});
					}

					panel.doLayout();
				}
			},
			style: 'margin-bottom: 0.5em;',
			title: lang['tarifyi'],

			items: [
				form.uslugaComplexTariffGrid
			]
		});

		// Панель с гридом "Профили"
		form.uslugaComplexProfilePanel = new sw.Promed.Panel({
			border: true,
			collapsible: true,
			height: 200,
			id: 'UEF_UslugaComplexProfilePanel',
			isLoaded: false,
			layout: 'border',
			listeners: {
				'expand': function(panel) {
					if ( panel.isLoaded === false ) {
						panel.isLoaded = true;
						form.uslugaComplexProfileGrid.getGrid().getStore().load({
							params: {
								UslugaComplex_id: form.formPanel.getForm().findField('UslugaComplex_id').getValue(),
								LpuEditFlag: (form.action == 'lpuedit')?1:0
							}
						});
					}

					panel.doLayout();
				}
			},
			style: 'margin-bottom: 0.5em;',
			title: lang['profili'],

			items: [
				form.uslugaComplexProfileGrid
			]
		});

		form.uslugaComplexInfoPanel = new sw.Promed.Panel({
			border: true,
			collapsible: true,
			autoHeight: true,
			id: 'UEF_UslugaComplexInfoPanel',
			layout: 'form',
			style: 'margin-bottom: 0.5em;',
			bodyStyle: 'padding: 3px 7px 0;',
			title: 'Описание услуги',
			labelAlign: 'top',
			items: [{
				xtype: 'hidden',
				name: 'UslugaComplexInfo_id'
			}, {
				xtype: 'textarea',
				name: 'UslugaComplexInfo_ImportantInfo',
				fieldLabel: 'Важная информация',
				autoCreate: {tag: "textarea", maxLength: 500, autocomplete: "off"},
				anchor: '100%'
			}, {
				xtype: 'textarea',
				name: 'UslugaComplexInfo_RecipientCat',
				fieldLabel: 'Категиории получателей',
				autoCreate: {tag: "textarea", maxLength: 500, autocomplete: "off"},
				anchor: '100%'
			}, {
				xtype: 'textarea',
				name: 'UslugaComplexInfo_DocumentUsluga',
				fieldLabel: 'Документы, необходимые для получения услуги',
				autoCreate: {tag: "textarea", maxLength: 500, autocomplete: "off"},
				anchor: '100%'
			}, {
				xtype: 'textarea',
				name: 'UslugaComplexInfo_Limit',
				fieldLabel: 'Ограничения',
				autoCreate: {tag: "textarea", maxLength: 500, autocomplete: "off"},
				anchor: '100%'
			}, {
				xtype: 'textarea',
				name: 'UslugaComplexInfo_PayOrder',
				fieldLabel: 'Порядок оплаты услуги',
				autoCreate: {tag: "textarea", maxLength: 500, autocomplete: "off"},
				anchor: '100%'
			}, {
				xtype: 'textarea',
				name: 'UslugaComplexInfo_QueueType',
				fieldLabel: 'Способ записи',
				autoCreate: {tag: "textarea", maxLength: 500, autocomplete: "off"},
				anchor: '100%'
			}, {
				xtype: 'textarea',
				name: 'UslugaComplexInfo_ServiceOrder',
				fieldLabel: 'Порядок оказания услуги',
				autoCreate: {tag: "textarea", maxLength: 500, autocomplete: "off"},
				anchor: '100%'
			}, {
				xtype: 'textarea',
				name: 'UslugaComplexInfo_Duration',
				fieldLabel: 'Продолжительность',
				autoCreate: {tag: "textarea", maxLength: 500, autocomplete: "off"},
				anchor: '100%'
			}, {
				xtype: 'textarea',
				name: 'UslugaComplexInfo_Result',
				fieldLabel: 'Результат',
				autoCreate: {tag: "textarea", maxLength: 500, autocomplete: "off"},
				anchor: '100%'
			}]
		});

		form.formPanel = new Ext.form.FormPanel({
			autoScroll: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			border: false,
			frame: false,
			id: 'UslugaEditForm',
			labelAlign: 'right',
			labelWidth: 250,
			reader: new Ext.data.JsonReader({
				success: Ext.emptyFn
			},  [
				{ name: 'accessType' },
				{ name: 'UslugaCategory_id' },
				{ name: 'UslugaCategory_SysNick' },
				{ name: 'Lpu_id' },
				{ name: 'UslugaComplex_id' },
                { name: 'UslugaComplex_ACode' },
                { name: 'UslugaComplex_isPackage' },
				{ name: 'UslugaComplex_begDate' },
				{ name: 'UslugaComplex_cid' },
				{ name: 'UslugaComplex_Code' },
				{ name: 'UslugaComplex_endDate' },
				{ name: 'UslugaComplex_Name' },
				{ name: 'UslugaComplex_Nick' },
				{ name: 'UslugaComplex_pid' },
				{ name: 'UslugaComplex_UET' },
				{ name: 'XmlTemplate_id' },
				{ name: 'UslugaComplexInfo_id' },
				{ name: 'UslugaComplexInfo_ImportantInfo' },
				{ name: 'UslugaComplexInfo_RecipientCat' },
				{ name: 'UslugaComplexInfo_DocumentUsluga' },
				{ name: 'UslugaComplexInfo_Limit' },
				{ name: 'UslugaComplexInfo_PayOrder' },
				{ name: 'UslugaComplexInfo_QueueType' },
				{ name: 'UslugaComplexInfo_ServiceOrder' },
				{ name: 'UslugaComplexInfo_Duration' },
				{ name: 'UslugaComplexInfo_Result' }
			]),
			region: 'center',
			url: '/?c=UslugaComplex&m=saveUslugaComplex',

			items: [{
				name: 'accessType',
				value: '',
				xtype: 'hidden'
			}, {
				name: 'UslugaComplex_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'Lpu_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'UslugaCategory_id',
				value: -1,
				xtype: 'hidden'
			}, {
				name: 'UslugaCategory_SysNick',
				value: '',
				xtype: 'hidden'
			}, {
				name: 'UslugaComplex_cid',
				value: 0,
				xtype: 'hidden'
			}, {
				hiddenName: 'UslugaComplex_pid',
				fieldLabel: lang['verhniy_uroven'],
				width: 300,
				xtype: 'swuslugacomplexgroupcombo'
			}, {
				allowBlank: false,
				enableKeyEvents: true,
				fieldLabel: lang['kod'],
				listeners: {
					'keydown': function(inp, e) {
						switch ( e.getKey() ) {
							case Ext.EventObject.TAB:
								if ( e.shiftKey == true ) {
									e.stopEvent();
									this.buttons[this.buttons.length - 1].focus();
								}
							break;
						}
					}.createDelegate(this)
				},
				name: 'UslugaComplex_Code',
				// tabIndex: TABINDEX_UEF + 1,
				width: 300,
				xtype: 'textfield'
			}, {
				allowBlank: false,
				enableKeyEvents: true,
				fieldLabel: lang['naimenovanie'],
				name: 'UslugaComplex_Name',
				// tabIndex: TABINDEX_UEF + 1,
				width: 300,
				xtype: 'textfield'
			}, {
				allowBlank: true,
				enableKeyEvents: true,
				fieldLabel: lang['kratkoe_naimenovanie'],
				name: 'UslugaComplex_Nick',
				// tabIndex: TABINDEX_UEF + 1,
				onTriggerClick: function() {
					var base_form = form.formPanel.getForm();

					if ( base_form.findField('UslugaComplex_Nick').disabled ) {						
						return false;
					}
					
					var fullname = base_form.findField('UslugaComplex_Name').getValue();
					base_form.findField('UslugaComplex_Nick').setValue(fullname);
				}.createDelegate(this),
				triggerClass: 'x-form-equil-trigger',
				width: 300,
				xtype: 'trigger'
			}, {
                fieldLabel: lang['eto_paket_uslug'],
                //boxLabel: 'Если при оказании услуги выбирается пакет услуг, то создается столько событий оказания услуги, сколько отмечено в составе',
                name: 'UslugaComplex_isPackage',
                //autoHeight:true,
                xtype: 'checkbox',
                listeners: {
                    check: function(checkbox,checked){
                        var base_form = form.formPanel.getForm(),
                            acode_field = base_form.findField('UslugaComplex_ACode'),
                            tpl_field = base_form.findField('XmlTemplate_id');
                        //Если чекбокс отмечен, то блокируются элементы: код подстановки в шаблон, шаблон, связанные услуги, атрибуты, тарифы.
                        if (checked) {
                            acode_field.setValue(null);
                            tpl_field.setValue(null);
                            form.linkedUslugaComplexGrid.removeAll();
                            form.uslugaComplexAttributeGrid.removeAll();
                            form.uslugaComplexTariffGrid.removeAll();
                        }
                        acode_field.setDisabled(checked);
                        tpl_field.setDisabled(checked);
                        form.linkedUslugaComplexPanel.setDisabled(checked);
                        form.uslugaComplexAttributePanel.setDisabled(checked);
                        form.uslugaComplexTariffPanel.setDisabled(checked);
                        form.uslugaComplexProfilePanel.setDisabled(checked);
                        if (checked) {
                            form.linkedUslugaComplexPanel.collapse();
                            form.uslugaComplexAttributePanel.collapse();
                            form.uslugaComplexTariffPanel.collapse();
                            form.uslugaComplexProfilePanel.collapse();
                            form.uslugaComplexInfoPanel.collapse();
                        }
                    }.createDelegate(this)
                }
            }, {
				allowBlank: false,
				fieldLabel: lang['data_nachala'],
				format: 'd.m.Y',
				listeners:
				{
					'change': (getRegionNick() == 'vologda' ?
						function(field, newValue, oldValue)
						{
							var base_form = this.formPanel.getForm(),
								dtEndDate = base_form.findField('UslugaComplex_endDate'),
								endDate = dtEndDate.getValue();

							if (newValue && endDate && endDate < newValue)
								dtEndDate.setValue(newValue);
						}.createDelegate(this) :
						Ext.emptyFn)
				},
				name: 'UslugaComplex_begDate',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				selectOnFocus: true,
				// tabIndex: TABINDEX_UEF + 4,
				width: 100,
				xtype: 'swdatefield'
			}, {
				allowBlank: true,
				fieldLabel: lang['data_okonchaniya'],
				format: 'd.m.Y',
				listeners:
				{
					'change': (getRegionNick() == 'vologda' ?
						function(field, newValue, oldValue)
						{
							var base_form = this.formPanel.getForm(),
								begDate = base_form.findField('UslugaComplex_begDate').getValue();

							if (newValue && begDate && begDate > newValue)
								field.setValue(begDate);
						}.createDelegate(this) :
						Ext.emptyFn)
				},
				name: 'UslugaComplex_endDate',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				selectOnFocus: true,
				// tabIndex: TABINDEX_UEF + 4,
				width: 100,
				xtype: 'swdatefield'
			}, {
				allowBlank: true,
				allowDecimals: true,
				allowNegative: false,
				enableKeyEvents: true,
				fieldLabel: lang['uet'],
				name: 'UslugaComplex_UET',
				// tabIndex: TABINDEX_UEF + 33,
				width: 100,
				xtype: 'hidden'
				// xtype: 'numberfield'
            }, {
                allowBlank: true,
                enableKeyEvents: true,
                fieldLabel: lang['kod_podstanovki_v_shablon'],
                name: 'UslugaComplex_ACode',
                // tabIndex: TABINDEX_UEF + 1,
                width: 300,
                xtype: 'textfield'
			}, {
				fieldLabel: lang['shablon_uslugi'],
				// hideLabel: true,
				name: 'XmlTemplate_id',
				// tabIndex: TABINDEX_UCTW + 12,
				width: 500,
                EvnClass_id: null,
				xtype: 'swtemplatesparcombo'
			},
				form.linkedUslugaComplexPanel,
				form.uslugaComplexCompositionPanel,
				form.uslugaComplexAttributePanel,
				form.uslugaComplexPlacePanel,
				form.uslugaComplexTariffPanel,
				form.uslugaComplexProfilePanel,
				form.uslugaComplexInfoPanel
			]
		});

		Ext.apply(this, {
			buttons: [{
				handler: function() {
					form.doSave();
				},
				iconCls: 'save16',
				onShiftTabAction: function () {
					var base_form = form.formPanel.getForm();

					if ( form.action == 'view' ) {
						form.buttons[form.buttons.length - 1].focus(true);
					}
				},
				onTabAction: function () {
					form.buttons[1].focus(true);
				},
				// tabIndex: TABINDEX_UEF + 44,
				text: BTN_FRMSAVE
			}, {
				text: '-'
			},
			HelpButton(form, -1),
			{
				handler: function() {
					form.hide();
				},
				iconCls: 'cancel16',
				onShiftTabAction: function () {
					form.buttons[1].focus();
				},
				onTabAction: function () {
					if ( form.action == 'edit' ) {
						form.formPanel.getForm().findField('UslugaComplex_Code').focus(true);
					}
					else {
						form.buttons[1].focus(true);
					}
				},
				// tabIndex: TABINDEX_UEF + 46,
				text: BTN_FRMCANCEL
			}],
			items: [
				 form.formPanel
			],
			layout: 'border'
		});

		sw.Promed.swUslugaEditWindow.superclass.initComponent.apply(this, arguments);

		form.linkedUslugaComplexGrid.ViewToolbar.on('render', function(vt) {
			this.ViewActions['action_copycodeandname'] = new Ext.Action({ name: 'action_copycodeandname', id: 'id_action_copycodeandname', disabled: false, handler: function() { form.copyUslugaComplexCodeAndName(); }, text: lang['skopirovat_kod_i_naimenovanie'], tooltip: lang['skopirovat_kod_i_naimenovanie_svyazannoy_uslugi'], iconCls : 'x-btn-text', icon: 'img/icons/copy16.png'});
			this.ViewActions['action_copyattributes'] = new Ext.Action({ name: 'action_copyattributes', id: 'id_action_copyattributes', disabled: false, handler: function() { form.copyUslugaComplexAttributes(); }, text: lang['skopirovat_atributyi'], tooltip: lang['skopirovat_atributyi_svyazannoy_uslugi'], iconCls : 'x-btn-text', icon: 'img/icons/copy16.png'});
			this.ViewActions['action_copycontents'] = new Ext.Action({ name: 'action_copycontents', id: 'id_action_copycontents', disabled: false, handler: function() { form.copyUslugaComplexContents(); }, text: lang['skopirovat_sostav'], tooltip: lang['skopirovat_sostav_svyazannoy_uslugi'], iconCls : 'x-btn-text', icon: 'img/icons/copy16.png'});

			vt.insertButton(1, this.ViewActions['action_copycodeandname']);
			vt.insertButton(1, this.ViewActions['action_copyattributes']);
			vt.insertButton(1, this.ViewActions['action_copycontents']);

			return true;
		}, form.linkedUslugaComplexGrid);
	},
	keys: [{
		alt: true,
		fn: function(inp, e) {
			var current_window = Ext.getCmp('UslugaEditWindow');

			switch ( e.getKey() ) {
				case Ext.EventObject.C:
					current_window.doSave();
				break;

				case Ext.EventObject.J:
					current_window.hide();
				break;
			}
		},
		key: [
			Ext.EventObject.C,
			Ext.EventObject.J
		],
		stopEvent: true
	}],
	listeners: {
		'hide': function(win) {
			win.onHide();
		}
	},
	maximizable: false,
	maximized: true,
	modal: true,
	onHide: Ext.emptyFn,
	openUslugaComplexLinkedEditWindow: function(action) {
		if ( typeof action != 'string' || !(action.inlist([ 'add', 'edit', 'view' ])) ) {
			return false;
		}

		if ( getWnd('swUslugaComplexLinkedEditWindow').isVisible() ) {
			sw.swMsg.alert(lang['oshibka'], lang['okno_dobavleniya_svyazannoy_uslugi_uje_otkryito']);
			return false;
		}

		if ( this.action == 'view' ) {
			if ( action == 'add' ) {
				return false;
			}
			else if ( action == 'edit' ) {
				action = 'view';
			}
		}

		var base_form = this.formPanel.getForm();
		var deniedCategoryList = new Array();
		var formParams = new Object();
		var grid = this.linkedUslugaComplexGrid.getGrid();
		var gridComposition = this.uslugaComplexCompositionGrid.getGrid();
		var params = new Object();

		params.CompositionCount = gridComposition.getStore().getCount();
		
		// Формируем список категорий услуг, которые недоступны для добавления
		deniedCategoryList.push(base_form.findField('UslugaCategory_id').getValue());

		if ( action == 'add' ) {
			// Идентификатор услуги, для которой добавляется связанная услуга
			formParams.UslugaComplex_pid = base_form.findField('UslugaComplex_id').getValue();

			params.onHide = function() {
				if ( grid.getStore().getCount() > 0 ) {
					grid.getView().focusRow(0);
				}
			};

			grid.getStore().each(function(rec) {
				if ( rec.get('UslugaCategory_id') && rec.get('UslugaCategory_SysNick') != 'lpu' ) {
					deniedCategoryList.push(rec.get('UslugaCategory_id'));
				}
			});
		}
		else {
			if ( !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('UslugaComplex_id') ) {
				return false;
			}

			var record = grid.getSelectionModel().getSelected();

			formParams = record.data;

			params.onHide = function() {
				grid.getView().focusRow(grid.getStore().indexOf(record));
			};

			grid.getStore().each(function(rec) {
				if ( rec.get('UslugaCategory_id') && rec.get('UslugaCategory_id') != record.get('UslugaCategory_id') && rec.get('UslugaCategory_SysNick') != 'lpu' ) {
					deniedCategoryList.push(rec.get('UslugaCategory_id'));
				}
			});
		}

		params.action = action;
		params.callback = function(data) {
			if ( typeof data != 'object' || typeof data.UslugaComplexLinkedData != 'object' ) {
				return false;
			}

			data.UslugaComplexLinkedData.RecordStatus_Code = 0;

			var record = grid.getStore().getById(data.UslugaComplexLinkedData.UslugaComplex_id);

			if ( record ) {
				if ( record.get('RecordStatus_Code') == 1 ) {
					data.UslugaComplexLinkedData.RecordStatus_Code = 2;
				}

				var grid_fields = new Array();

				grid.getStore().fields.eachKey(function(key, item) {
					grid_fields.push(key);
				});

				for ( i = 0; i < grid_fields.length; i++ ) {
					record.set(grid_fields[i], data.UslugaComplexLinkedData[grid_fields[i]]);
				}

				record.commit();
			}
			else {
				if ( grid.getStore().getCount() == 1 && !grid.getStore().getAt(0).get('UslugaComplex_id') ) {
					grid.getStore().removeAll();
				}

				grid.getStore().loadData([ data.UslugaComplexLinkedData ], true);
			}

			if ( data.UslugaComplexLinkedData.CopyContent == true ) {
				// Копируем состав связываемой услуги
				this.copyUslugaComplexContents({
					callback: function() {
						// log('Вызов callback в copyUslugaComplexContents');
					},
					copyFromLinked: true,
					UslugaComplex_id: data.UslugaComplexLinkedData.UslugaComplex_id
				});
			}

			if ( data.UslugaComplexLinkedData.CopyAttributes == true ) {
				// Копируем атрибуты услуги
				this.copyUslugaComplexAttributes({
					callback: function() {
						if ( data.UslugaComplexLinkedData.CopyAllLinked == true ) {
							// Копируем связанные услуги
							this.copyLinkedUslugaComplex({
								 callback: Ext.emptyFn
								,copyAttributes: data.UslugaComplexLinkedData.CopyAttributes
								,UslugaCategory_id: data.UslugaComplexLinkedData.UslugaCategory_id
								,UslugaComplex_id: data.UslugaComplexLinkedData.UslugaComplex_id
							});
						}
					}.createDelegate(this),
					copyFromLinked: true,
					UslugaComplex_id: data.UslugaComplexLinkedData.UslugaComplex_id
				});
			}
			else if ( data.UslugaComplexLinkedData.CopyAllLinked == true ) {
				// Копируем связанные услуги
				this.copyLinkedUslugaComplex({
					 callback: Ext.emptyFn
					,copyAttributes: data.UslugaComplexLinkedData.CopyAttributes
					,UslugaCategory_id: data.UslugaComplexLinkedData.UslugaCategory_id
					,UslugaComplex_id: data.UslugaComplexLinkedData.UslugaComplex_id
				});
			}
		}.createDelegate(this);
		params.deniedCategoryList = deniedCategoryList;
		params.formMode = 'local';
		params.formParams = formParams;

		getWnd('swUslugaComplexLinkedEditWindow').show(params);
	},
	openUslugaComplexCompositionEditWindow: function(action) {
		if ( typeof action != 'string' || !(action.inlist([ 'add', 'edit', 'view' ])) ) {
			return false;
		}

		if ( this.action == 'view' ) {
			if ( action == 'add' ) {
				return false;
			}
			else if ( action == 'edit' ) {
				action = 'view';
			}
		}

		if ( getWnd('swUslugaComplexContentEditWindow').isVisible() ) {
			sw.swMsg.alert(lang['oshibka'], lang['okno_dobavleniya_suschestvuyuschey_uslugi_uje_otkryito']);
			return false;
		}

		var formParams = {},
            base_form = this.formPanel.getForm(),
            grid = this.uslugaComplexCompositionGrid.getGrid(),
            params = {},
            selectedRecord;

		if ( grid.getSelectionModel().getSelected() && grid.getSelectionModel().getSelected().get('UslugaComplexComposition_id') ) {
			selectedRecord = grid.getSelectionModel().getSelected();
		}

		if ( action == 'add' ) {
			params.onHide = function() {
				if ( grid.getStore().getCount() > 0 ) {
					grid.getView().focusRow(0);
				}
			};
		}
		else {
			if ( !selectedRecord ) {
				return false;
			}

			formParams = selectedRecord.data;

			params.onHide = function() {
				grid.getView().focusRow(grid.getStore().indexOf(selectedRecord));
			};
		}

        /*
        if (base_form.findField('UslugaComplex_isPackage').getValue()) {
            // Состав пакета услуг может быть только из услуг категории "Услуги ЛПУ"
            params.commonUslugaCategory_id = base_form.findField('UslugaCategory_id').getValue();
        }
        */
        // Состав комплексной услуги и пакета услуг
        // может быть только из услуг одной категории
        params.commonUslugaCategory_id = null;
        // Берем ту категорию, которая есть у большинства услуг
        var all = {}, id, lastCnt = 0;
        grid.getStore().each(function(rec) {
            id = rec.get('UslugaCategory_id');
            if (id) {
                if (all[id]) {
                    all[id]++;
                } else {
                    all[id] = 1;
                }
            }
        });
        for (id in all) {
            if (lastCnt < all[id]) {
                params.commonUslugaCategory_id = id;
                lastCnt = all[id];
            }
        }
		params.action = action;
		params.callback = function(data) {
			if ( typeof data != 'object' || typeof data.UslugaComplexContentData != 'object' ) {
				return false;
			}

			data.UslugaComplexContentData.RecordStatus_Code = 0;
			
			// проверка есть ли в составе уже такая услуга
			var alreadyExist = false;
			grid.getStore().each(function(rec) {
				if ( rec.get('UslugaComplex_id') && (rec.get('UslugaComplex_id') == data.UslugaComplexContentData.UslugaComplex_id)) {
					sw.swMsg.alert(lang['oshibka'], lang['usluga_uje_prisutsvuet_v_sostave'], function() { 
					}.createDelegate(this));
					alreadyExist = true;
					
					return false;
				}
			});
			
			if (alreadyExist) {
				return false;
			}
			
			var record = grid.getStore().getById(data.UslugaComplexContentData.UslugaComplexComposition_id);

			if ( record ) {
				if ( record.get('RecordStatus_Code') == 1 ) {
					data.UslugaComplexContentData.RecordStatus_Code = 2;
				}

				var grid_fields = new Array();

				grid.getStore().fields.eachKey(function(key, item) {
					grid_fields.push(key);
				});

				for ( i = 0; i < grid_fields.length; i++ ) {
					record.set(grid_fields[i], data.UslugaComplexContentData[grid_fields[i]]);
				}

				record.commit();
			}
			else {
				if ( grid.getStore().getCount() == 1 && !grid.getStore().getAt(0).get('UslugaComplexComposition_id') ) {
					grid.getStore().removeAll();
				}

				data.UslugaComplexContentData.UslugaComplexComposition_id = -swGenTempId(grid.getStore());

				grid.getStore().loadData([ data.UslugaComplexContentData ], true);
			}
		}.createDelegate(this);
		params.formMode = 'local';
		params.formParams = formParams;

		getWnd('swUslugaComplexContentEditWindow').show(params);
	},
	openUslugaComplexAttributeEditWindow: function(action) {
		if ( typeof action != 'string' || !(action.inlist([ 'add', 'edit', 'view' ])) ) {
			return false;
		}

		if ( this.action == 'view' ) {
			if ( action == 'add' ) {
				return false;
			}
			else if ( action == 'edit' ) {
				action = 'view';
			}
		}

		if ( getWnd('swUslugaComplexAttributeEditWindow').isVisible() ) {
			sw.swMsg.alert(lang['oshibka'], lang['okno_redaktirovaniya_atributa_uslugi_uje_otkryito']);
			return false;
		}

		var base_form = this.formPanel.getForm();
		var deniedAttributeTypeList = new Array();
		var formParams = new Object();
		var grid = this.uslugaComplexAttributeGrid.getGrid();
		var params = new Object();
		var selectedRecord;

		if ( grid.getSelectionModel().getSelected() && grid.getSelectionModel().getSelected().get('UslugaComplexAttribute_id') ) {
			selectedRecord = grid.getSelectionModel().getSelected();
		}

		if ( action == 'add' ) {
			params.onHide = function() {
				if ( grid.getStore().getCount() > 0 ) {
					grid.getView().focusRow(0);
				}
			};

			grid.getStore().each(function(rec) {
				if ( rec.get('UslugaComplexAttribute_id') && Ext.isEmpty(rec.get('UslugaComplexAttribute_endDate')) ) {
					deniedAttributeTypeList.push(rec.get('UslugaComplexAttributeType_id'));
				}
			});
		}
		else {
			if ( !selectedRecord ) {
				return false;
			}

			grid.getStore().each(function(rec) {
				if ( rec.get('UslugaComplexAttribute_id') && Ext.isEmpty(rec.get('UslugaComplexAttribute_endDate')) && selectedRecord.get('UslugaComplexAttributeType_id') != rec.get('UslugaComplexAttributeType_id') ) {
					deniedAttributeTypeList.push(rec.get('UslugaComplexAttributeType_id'));
				}
			});

			formParams = selectedRecord.data;
			params.onHide = function() {
				grid.getView().focusRow(grid.getStore().indexOf(selectedRecord));
			};
		}

		params.action = action;
		params.callback = function(data) {
			if ( typeof data != 'object' || typeof data.uslugaComplexAttributeData != 'object' ) {
				return false;
			}

			data.uslugaComplexAttributeData.RecordStatus_Code = 0;

			var record = grid.getStore().getById(data.uslugaComplexAttributeData.UslugaComplexAttribute_id);

			if ( record ) {
				if ( record.get('RecordStatus_Code') == 1 ) {
					data.uslugaComplexAttributeData.RecordStatus_Code = 2;
				}

				var grid_fields = new Array();

				grid.getStore().fields.eachKey(function(key, item) {
					grid_fields.push(key);
				});

				for ( i = 0; i < grid_fields.length; i++ ) {
					record.set(grid_fields[i], data.uslugaComplexAttributeData[grid_fields[i]]);
				}

				record.commit();
			}
			else {
				if ( grid.getStore().getCount() == 1 && !grid.getStore().getAt(0).get('UslugaComplexAttribute_id') ) {
					grid.getStore().removeAll();
				}

				data.uslugaComplexAttributeData.UslugaComplexAttribute_id = -swGenTempId(grid.getStore());

				grid.getStore().loadData([ data.uslugaComplexAttributeData ], true);
			}
		}.createDelegate(this);
		params.deniedAttributeTypeList = deniedAttributeTypeList;
		params.formMode = 'local';
		params.formParams = formParams;

		getWnd('swUslugaComplexAttributeEditWindow').show(params);
	},
	openUslugaComplexPlaceEditWindow: function(action) {
		if ( typeof action != 'string' || !(action.inlist([ 'add', 'edit', 'view' ])) ) {
			return false;
		}

		if ( this.action == 'view' ) {
			if ( action == 'add' ) {
				return false;
			}
			else if ( action == 'edit' ) {
				action = 'view';
			}
		}

		if ( getWnd('swUslugaComplexPlaceEditWindow').isVisible() ) {
			sw.swMsg.alert(lang['oshibka'], lang['okno_redaktirovaniya_mesta_okazaniya_uslugi_uje_otkryito']);
			return false;
		}

		var base_form = this.formPanel.getForm();
		var grid = this.uslugaComplexPlaceGrid.getGrid();
		var params = new Object();

		params.Lpu_id = base_form.findField('Lpu_id').getValue();
		params.action = action;
		params.callback = function(data) {
			if ( typeof data != 'object' || typeof data.uslugaComplexPlaceData != 'object' ) {
				return false;
			}

			data.uslugaComplexPlaceData.RecordStatus_Code = 0;

			var record = grid.getStore().getById(data.uslugaComplexPlaceData.UslugaComplexPlace_id);

			if ( record ) {
				if ( record.get('RecordStatus_Code') == 1 ) {
					data.uslugaComplexPlaceData.RecordStatus_Code = 2;
				}

				var grid_fields = new Array();

				grid.getStore().fields.eachKey(function(key, item) {
					grid_fields.push(key);
				});

				for ( i = 0; i < grid_fields.length; i++ ) {
					record.set(grid_fields[i], data.uslugaComplexPlaceData[grid_fields[i]]);
				}

				record.commit();
			}
			else {
				if ( grid.getStore().getCount() == 1 && !grid.getStore().getAt(0).get('UslugaComplexPlace_id') ) {
					grid.getStore().removeAll();
				}
				
				if (this.action != 'lpuedit') {
					data.uslugaComplexPlaceData.UslugaComplexPlace_id = -swGenTempId(grid.getStore());
				}

				grid.getStore().loadData([ data.uslugaComplexPlaceData ], true);
			}
		}.createDelegate(this);
		if ( this.action == 'lpuedit' ) {
			params.formMode = 'remote';
		} else {
			params.formMode = 'local';
		}
		params.formParams = new Object();
		params.formParams.UslugaComplex_id = base_form.findField('UslugaComplex_id').getValue();

		if ( action == 'add' ) {
			params.onHide = function() {
				if ( grid.getStore().getCount() > 0 ) {
					grid.getView().focusRow(0);
				}
			};
		}
		else {
			if ( !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('UslugaComplexPlace_id') ) {
				return false;
			}

			var record = grid.getSelectionModel().getSelected();

			params.formParams = record.data;
			params.formParams.UslugaComplex_id = base_form.findField('UslugaComplex_id').getValue();
			params.onHide = function() {
				grid.getView().focusRow(grid.getStore().indexOf(record));
			};
		}

		getWnd('swUslugaComplexPlaceEditWindow').show(params);
	},
	openUslugaComplexTariffEditWindow: function(action) {
		if ( typeof action != 'string' || !(action.inlist([ 'add', 'edit', 'view' ])) ) {
			return false;
		}

		if ( this.action == 'view' ) {
			if ( action == 'add' ) {
				return false;
			}
			else if ( action == 'edit' ) {
				action = 'view';
			}
		}

		if ( getWnd('swUslugaComplexTariffEditWindow').isVisible() ) {
			sw.swMsg.alert(lang['oshibka'], lang['okno_redaktirovaniya_tarifa_uje_otkryito']);
			return false;
		}
		var me = this;
		var base_form = this.formPanel.getForm();
		var formParams = new Object();
		formParams.UslugaComplex_id = base_form.findField('UslugaComplex_id').getValue();
		var grid = this.uslugaComplexTariffGrid.getGrid();
		var params = new Object();
		var uslugaData = {
			 UslugaComplex_id: base_form.findField('UslugaComplex_id').getValue()
			,UslugaComplex_Code: base_form.findField('UslugaComplex_Code').getValue()
			,UslugaComplex_Name: base_form.findField('UslugaComplex_Name').getValue()
			,UslugaComplex_begDate: base_form.findField('UslugaComplex_begDate').getValue()
			,UslugaComplex_endDate: base_form.findField('UslugaComplex_endDate').getValue()
		};
		var uslugaTariffData = new Array();

		if ( action == 'add' ) {
			params.onHide = function() {
				if ( grid.getStore().getCount() > 0 ) {
					grid.getView().focusRow(0);
				}
			};

			grid.getStore().each(function(rec) {
				if ( rec.get('UslugaComplexTariff_id') ) {
					uslugaTariffData.push({
						 UslugaComplexTariff_id: rec.get('UslugaComplexTariff_id')
						,UslugaComplexTariffType_id: rec.get('UslugaComplexTariffType_id')
						,PayType_id: rec.get('PayType_id')
						,UslugaComplexTariff_begDate: rec.get('UslugaComplexTariff_begDate')
						,UslugaComplexTariff_endDate: rec.get('UslugaComplexTariff_endDate')
						,Lpu_id: rec.get('Lpu_id')
						,LpuLevel_id: rec.get('LpuLevel_id')
						,LpuSection_id: rec.get('LpuSection_id')
						,LpuBuilding_id: rec.get('LpuBuilding_id')
						,LpuUnit_id: rec.get('LpuUnit_id')
						,LpuSectionProfile_id: rec.get('LpuSectionProfile_id')
						,MesAgeGroup_id: rec.get('MesAgeGroup_id')
						,Sex_id: rec.get('Sex_id')
						,LpuUnitType_id: rec.get('LpuUnitType_id')
					});
				}
			});
		}
		else {
			if ( !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('UslugaComplexTariff_id') ) {
				return false;
			}

			var record = grid.getSelectionModel().getSelected();

			formParams = record.data;
			formParams.UslugaComplex_id = base_form.findField('UslugaComplex_id').getValue();
			
			params.onHide = function() {
				grid.getView().focusRow(grid.getStore().indexOf(record));
			};

			grid.getStore().each(function(rec) {
				if ( rec.get('UslugaComplexTariff_id') && rec.get('UslugaComplexTariff_id') != record.get('UslugaComplexTariff_id') ) {
					uslugaTariffData.push({
						 UslugaComplexTariff_id: rec.get('UslugaComplexTariff_id')
						,UslugaComplexTariffType_id: rec.get('UslugaComplexTariffType_id')
						,PayType_id: rec.get('PayType_id')
						,UslugaComplexTariff_begDate: rec.get('UslugaComplexTariff_begDate')
						,UslugaComplexTariff_endDate: rec.get('UslugaComplexTariff_endDate')
						,Lpu_id: rec.get('Lpu_id')
						,LpuLevel_id: rec.get('LpuLevel_id')
						,LpuSection_id: rec.get('LpuSection_id')
						,LpuBuilding_id: rec.get('LpuBuilding_id')
						,LpuUnit_id: rec.get('LpuUnit_id')
						,LpuSectionProfile_id: rec.get('LpuSectionProfile_id')
						,MesAgeGroup_id: rec.get('MesAgeGroup_id')
						,Sex_id: rec.get('Sex_id')
						,LpuUnitType_id: rec.get('LpuUnitType_id')
					});
				}
			});
		}

		params.Lpu_id = base_form.findField('Lpu_id').getValue();
		params.action = action;
		params.callback = function(data) {
			if ( typeof data != 'object' || typeof data.uslugaComplexTariffData != 'object' ) {
				return false;
			}
			
			data.uslugaComplexTariffData.RecordStatus_Code = 0;

			if  (!Ext.isEmpty(data.uslugaComplexTariffData.needCloseTariff_id)) {
				var closeRecord = grid.getStore().getById(data.uslugaComplexTariffData.needCloseTariff_id);
				if ( closeRecord ) {
					if ( closeRecord.get('RecordStatus_Code') == 1 ) {
						closeRecord.set('RecordStatus_Code', 2);
					}
					closeRecord.set('UslugaComplexTariff_endDate',data.uslugaComplexTariffData.UslugaComplexTariff_begDate);
					closeRecord.commit();
				}
			}
			
			var record = grid.getStore().getById(data.uslugaComplexTariffData.UslugaComplexTariff_id);

			if ( record ) {
				if ( record.get('RecordStatus_Code') == 1 ) {
					data.uslugaComplexTariffData.RecordStatus_Code = 2;
				}

				var grid_fields = new Array();

				grid.getStore().fields.eachKey(function(key, item) {
					grid_fields.push(key);
				});

				for ( i = 0; i < grid_fields.length; i++ ) {
					record.set(grid_fields[i], data.uslugaComplexTariffData[grid_fields[i]]);
				}

				record.commit();
			}
			else {
				var usl = data.uslugaComplexTariffData;
				usl.UslugaComplexTariff_begDate = Ext.util.Format.date(data.uslugaComplexTariffData.UslugaComplexTariff_begDate,'d.m.Y');
				if (usl.UslugaComplexTariff_endDate!="") {
					usl.UslugaComplexTariff_endDate = Ext.util.Format.date(data.uslugaComplexTariffData.UslugaComplexTariff_endDate,'d.m.Y');     
				}
				
				Ext.Ajax.request({
				method:'post',
				failure:function () {
				sw.swMsg.alert(lang['oshibka'], lang['ne_udalos_poluchit_dannyie_s_servera']);
				return false;
				},  
				params:usl,
				success: function (response) {
				var resp = Ext.util.JSON.decode(response.responseText);
				if(resp.success==true){
				    if ( grid.getStore().getCount() == 1 && !grid.getStore().getAt(0).get('UslugaComplexTariff_id') ) {
					grid.getStore().removeAll();
				}
				
				if (me.action != 'lpuedit') {
					data.uslugaComplexTariffData.UslugaComplexTariff_id = -swGenTempId(grid.getStore());
				}
					grid.getStore().loadData([ data.uslugaComplexTariffData ], true);
				}else{
					return false;
				}
				},
				url:'/?c=UslugaComplex&m=checkUslugaComplexTariffHasDuplicate'
			});

				
			}
		}.createDelegate(this);
		if ( this.action == 'lpuedit' ) {
			params.formMode = 'remote';
		} else {
			params.formMode = 'local';
		}
		params.formParams = formParams;
		params.uslugaData = uslugaData;
		params.uslugaTariffData = uslugaTariffData;
		params.UslugaCategory_SysNick = base_form.findField('UslugaCategory_SysNick').getValue();
		getWnd('swUslugaComplexTariffEditWindow').show(params);
	},
	openUslugaComplexProfileEditWindow: function(action) {
		if ( typeof action != 'string' || !(action.inlist([ 'add', 'edit', 'view' ])) ) {
			return false;
		}

		if ( this.action == 'view' ) {
			if ( action == 'add' ) {
				return false;
			}
			else if ( action == 'edit' ) {
				action = 'view';
			}
		}

		if ( getWnd('swUslugaComplexProfileEditWindow').isVisible() ) {
			sw.swMsg.alert(lang['oshibka'], lang['okno_redaktirovaniya_profilya_uslugi_uje_otkryito']);
			return false;
		}

		var base_form = this.formPanel.getForm();
		//var deniedLpuSectionProfileList = new Array();
		var formParams = new Object();
		var grid = this.uslugaComplexProfileGrid.getGrid();
		var params = new Object();
		var selectedRecord;

		params.checkUslugaSectionProfileDates = function(data) {
			var result = true;

			if ( typeof data != 'object' ) {
				return false;
			}

			grid.getStore().each(function(rec) {
				if (
					rec.get('LpuSectionProfile_id') == data.LpuSectionProfile_id
					&& (Ext.isEmpty(data.UslugaComplexProfile_id) || rec.get('UslugaComplexProfile_id') != data.UslugaComplexProfile_id)
				) {
					if (
						Ext.isEmpty(data.UslugaComplexProfile_endDate)
						&& Ext.isEmpty(rec.get('UslugaComplexProfile_endDate'))
					) {
						log(1);
						result = false;
					}
					else if (
						!Ext.isEmpty(data.UslugaComplexProfile_endDate)
						&& !Ext.isEmpty(rec.get('UslugaComplexProfile_endDate'))
					) {
						if (
							(rec.get('UslugaComplexProfile_begDate') >= data.UslugaComplexProfile_begDate && rec.get('UslugaComplexProfile_begDate') <= data.UslugaComplexProfile_endDate)
							|| (rec.get('UslugaComplexProfile_endDate') >= data.UslugaComplexProfile_begDate && rec.get('UslugaComplexProfile_endDate') <= data.UslugaComplexProfile_endDate)
							|| (rec.get('UslugaComplexProfile_begDate') <= data.UslugaComplexProfile_begDate && rec.get('UslugaComplexProfile_endDate') >= data.UslugaComplexProfile_endDate)
							|| (rec.get('UslugaComplexProfile_begDate') >= data.UslugaComplexProfile_begDate && rec.get('UslugaComplexProfile_endDate') <= data.UslugaComplexProfile_endDate)
						) {
							log(2);
							result = false;
						}
					}
					else if (
						!Ext.isEmpty(data.UslugaComplexProfile_endDate)
						&& rec.get('UslugaComplexProfile_begDate') <= data.UslugaComplexProfile_endDate
					) {
						log(3);
						result = false;
					}
					else if (
						!Ext.isEmpty(rec.get('UslugaComplexProfile_endDate'))
						&& rec.get('UslugaComplexProfile_endDate') >= data.UslugaComplexProfile_begDate
					) {
						log(4);
						result = false;
					}
				}
			});

			return result;
		}

		if ( grid.getSelectionModel().getSelected() && grid.getSelectionModel().getSelected().get('UslugaComplexProfile_id') ) {
			selectedRecord = grid.getSelectionModel().getSelected();
		}

		if ( action == 'add' ) {
			params.onHide = function() {
				if ( grid.getStore().getCount() > 0 ) {
					grid.getView().focusRow(0);
				}
			};

			/*grid.getStore().each(function(rec) {
				if ( rec.get('UslugaComplexProfile_id') ) {
					deniedLpuSectionProfileList.push(rec.get('LpuSectionProfile_id'));
				}
			});*/
		}
		else {
			if ( !selectedRecord ) {
				return false;
			}

			/*grid.getStore().each(function(rec) {
				if ( rec.get('UslugaComplexProfile_id') && selectedRecord.get('LpuSectionProfile_id') != rec.get('LpuSectionProfile_id') ) {
					deniedLpuSectionProfileList.push(rec.get('LpuSectionProfile_id'));
				}
			});*/

			formParams = selectedRecord.data;
			params.onHide = function() {
				grid.getView().focusRow(grid.getStore().indexOf(selectedRecord));
			};
		}

		params.action = action;
		params.callback = function(data) {
			if ( typeof data != 'object' || typeof data.uslugaComplexProfileData != 'object' ) {
				return false;
			}

			data.uslugaComplexProfileData.RecordStatus_Code = 0;

			var record = grid.getStore().getById(data.uslugaComplexProfileData.UslugaComplexProfile_id);

			if ( record ) {
				if ( record.get('RecordStatus_Code') == 1 ) {
					data.uslugaComplexProfileData.RecordStatus_Code = 2;
				}

				var grid_fields = new Array();

				grid.getStore().fields.eachKey(function(key, item) {
					grid_fields.push(key);
				});

				for ( i = 0; i < grid_fields.length; i++ ) {
					record.set(grid_fields[i], data.uslugaComplexProfileData[grid_fields[i]]);
				}

				record.commit();
			}
			else {
				if ( grid.getStore().getCount() == 1 && !grid.getStore().getAt(0).get('UslugaComplexProfile_id') ) {
					grid.getStore().removeAll();
				}

				data.uslugaComplexProfileData.UslugaComplexProfile_id = -swGenTempId(grid.getStore());

				grid.getStore().loadData([ data.uslugaComplexProfileData ], true);
			}
		}.createDelegate(this);
		//params.deniedLpuSectionProfileList = deniedLpuSectionProfileList;
		params.formMode = 'local';
		params.formParams = formParams;

		getWnd('swUslugaComplexProfileEditWindow').show(params);
	},
	plain: true,
	resizable: false,
	onChangeUslugaComplexPid: function() {
		var base_form = this.formPanel.getForm();
		var UslugaComplex_pid = base_form.findField('UslugaComplex_pid').getValue();
		if (Ext.isEmpty(UslugaComplex_pid)) {
			base_form.findField('UslugaComplex_pid').hideContainer();
			base_form.findField('UslugaComplex_pid').setAllowBlank(true);
		} else {
			base_form.findField('UslugaComplex_pid').showContainer();
			base_form.findField('UslugaComplex_pid').setAllowBlank(false);
			base_form.findField('UslugaComplex_pid').getStore().baseParams.filterByUslugaComplex_id = UslugaComplex_pid;
			base_form.findField('UslugaComplex_pid').getStore().load({
				callback: function() {
					base_form.findField('UslugaComplex_pid').setValue(UslugaComplex_pid);
				},
				params: {
					filterByUslugaComplex_id: UslugaComplex_pid
				}
			})
		}
	},
    /**
     * @param {Object} params
     * @param {String} params.action inlist([ 'add', 'edit', 'view' ])
     * @param {Object} params.formParams
     * @param {Integer} params.formParams.UslugaCategory_id Обязательный параметр при добавлении услуги
     * @param {Integer} params.formParams.UslugaCategory_SysNick Обязательный параметр при добавлении услуги
     * @param {Integer} params.formParams.Lpu_id Обязательный параметр при добавлении услуги с категорий "Услуги ЛПУ"
     * @param {Integer} params.formParams.UslugaComplex_pid Обязательный параметр при добавлении услуги в состав другой услуги
     * @param {Integer} params.formParams.UslugaComplexLevel_id Параметр при добавлении услуги, непонятно для чего
     * @param {Integer} params.formParams.UslugaComplex_cid идентификатор услуги, чей состав отображается в верхнем гриде
     * @param {Integer} params.formParams.UslugaComplex_id Обязательный параметр при просмотре/редактировании услуги
     * @param {Function} params.callback
     * @param {Function} params.onHide
     * @return {Boolean}
     */
	show: function(params) {
		sw.Promed.swUslugaEditWindow.superclass.show.apply(this, arguments);

		this.linkedUslugaComplexGrid.removeAll();
		this.uslugaComplexCompositionGrid.removeAll();
		this.uslugaComplexAttributeGrid.removeAll();
		this.uslugaComplexPlaceGrid.removeAll();
		this.uslugaComplexTariffGrid.removeAll();
		this.uslugaComplexProfileGrid.removeAll();

		this.linkedUslugaComplexGrid.setActionDisabled('action_add', true);
		this.linkedUslugaComplexGrid.setActionDisabled('action_delete', true);

		this.uslugaComplexCompositionGrid.setActionDisabled('action_add', true);
		this.uslugaComplexCompositionGrid.setActionDisabled('action_edit', true);
		this.uslugaComplexCompositionGrid.setActionDisabled('action_view', true);
		this.uslugaComplexCompositionGrid.setActionDisabled('action_delete', true);

		this.uslugaComplexAttributeGrid.setActionDisabled('action_add', true);
		this.uslugaComplexAttributeGrid.setActionDisabled('action_edit', true);
		this.uslugaComplexAttributeGrid.setActionDisabled('action_view', true);
		this.uslugaComplexAttributeGrid.setActionDisabled('action_delete', true);

		this.uslugaComplexPlaceGrid.setActionDisabled('action_add', true);
		this.uslugaComplexPlaceGrid.setActionDisabled('action_edit', true);
		this.uslugaComplexPlaceGrid.setActionDisabled('action_view', true);
		this.uslugaComplexPlaceGrid.setActionDisabled('action_delete', true);

		this.uslugaComplexTariffGrid.setActionDisabled('action_add', true);
		this.uslugaComplexTariffGrid.setActionDisabled('action_edit', true);
		this.uslugaComplexTariffGrid.setActionDisabled('action_view', true);
		this.uslugaComplexTariffGrid.setActionDisabled('action_delete', true);

		this.uslugaComplexProfileGrid.setActionDisabled('action_add', true);
		this.uslugaComplexProfileGrid.setActionDisabled('action_edit', true);
		this.uslugaComplexProfileGrid.setActionDisabled('action_view', true);
		this.uslugaComplexProfileGrid.setActionDisabled('action_delete', true);

		this.linkedUslugaComplexPanel.expand();
		this.uslugaComplexCompositionPanel.expand();
		this.uslugaComplexAttributePanel.expand();
		this.uslugaComplexPlacePanel.collapse();
		this.uslugaComplexTariffPanel.collapse();
		this.uslugaComplexProfilePanel.collapse();
		this.uslugaComplexInfoPanel.collapse();

		var base_form = this.formPanel.getForm();
		base_form.reset();
        base_form.findField('XmlTemplate_id').getStore().removeAll();

		base_form.findField('UslugaComplexAttribute_Int').hideContainer();
		base_form.findField('UslugaComplexAttribute_Float').hideContainer();
		base_form.findField('UslugaComplexAttribute_Text').hideContainer();
		this.onChangeAttributeValueType(null, null);

		this.formStatus = 'edit';
		this.onHide = Ext.emptyFn;

		if ( !arguments[0] || !arguments[0].formParams ) {
			sw.swMsg.alert(lang['soobschenie'], lang['nevernyie_parametryi'], function() { this.hide(); }.createDelegate(this) );
			return false;
		}

        this.action = arguments[0].action || null;
        this.callback = arguments[0].callback || Ext.emptyFn;
        this.onHide = arguments[0].onHide || Ext.emptyFn;
        if ( this.action == 'add' && !arguments[0].formParams.UslugaCategory_SysNick ) {
            sw.swMsg.alert(lang['soobschenie'], lang['nevernyie_parametryi_2'], function() { this.hide(); }.createDelegate(this) );
            return false;
        }
        base_form.setValues(arguments[0].formParams);
		this.onChangeUslugaComplexPid();

		if ( this.action == 'add' ) {
			this.linkedUslugaComplexPanel.isLoaded = true;
			this.uslugaComplexCompositionPanel.isLoaded = true;
			this.uslugaComplexAttributePanel.isLoaded = true;
			this.uslugaComplexPlacePanel.isLoaded = true;
			this.uslugaComplexTariffPanel.isLoaded = true;
			this.uslugaComplexProfilePanel.isLoaded = true;
		}
		else {
			this.linkedUslugaComplexPanel.isLoaded = false;
			this.uslugaComplexCompositionPanel.isLoaded = false;
			this.uslugaComplexAttributePanel.isLoaded = false;
			this.uslugaComplexPlacePanel.isLoaded = false;
			this.uslugaComplexTariffPanel.isLoaded = false;
			this.uslugaComplexProfilePanel.isLoaded = false;
		}

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT });
		loadMask.show();

		switch ( this.action ) {
			case 'add':
				this.setTitle(WND_USLUGA_UEFADD);
				this.enableEdit(true);

				this.linkedUslugaComplexGrid.setActionDisabled('action_add', false);
				this.uslugaComplexCompositionGrid.setActionDisabled('action_add', false);
				this.uslugaComplexAttributeGrid.setActionDisabled('action_add', false);
				this.uslugaComplexPlaceGrid.setActionDisabled('action_add', false);
				this.uslugaComplexTariffGrid.setActionDisabled('action_add', false);
				this.uslugaComplexProfileGrid.setActionDisabled('action_add', false);

				LoadEmptyRow(this.linkedUslugaComplexGrid.getGrid());
				LoadEmptyRow(this.uslugaComplexCompositionGrid.getGrid());
				LoadEmptyRow(this.uslugaComplexAttributeGrid.getGrid());
				LoadEmptyRow(this.uslugaComplexPlaceGrid.getGrid());
				LoadEmptyRow(this.uslugaComplexTariffGrid.getGrid());
				LoadEmptyRow(this.uslugaComplexProfileGrid.getGrid());

				loadMask.hide();

				base_form.clearInvalid();

				base_form.findField('UslugaComplex_Code').focus(true, 250);
			break;

			case 'edit':
			case 'view':
				var usluga_complex_id = base_form.findField('UslugaComplex_id').getValue();

				if ( !usluga_complex_id ) {
					loadMask.hide();
					this.hide();
					return false;
				}

				base_form.load({
					failure: function() {
						loadMask.hide();
						sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_zagruzke_dannyih_formyi'], function() { this.hide(); }.createDelegate(this) );
					}.createDelegate(this),
					params: {
						'UslugaComplex_id': usluga_complex_id
					},
					success: function() {
						if ( base_form.findField('accessType').getValue() == 'view' ) {
							this.action = 'view';
						}

						this.onChangeUslugaComplexPid();
						
						if (!(base_form.findField('UslugaCategory_SysNick').getValue().inlist(['lpu'])) && !isSuperAdmin() && this.action != 'view') {
							this.action = 'lpuedit';
						}
						
						if ( this.action == 'edit' ) {
							this.setTitle(WND_USLUGA_UEFEDIT);
							this.enableEdit(true);
						}
						else {
							if ( this.action != 'lpuedit' ) {
								this.setTitle(WND_USLUGA_UEFVIEW);
							} else {
								this.setTitle(WND_USLUGA_UEFEDIT);
							}
							this.enableEdit(false);
						}

						if ( this.action == 'edit' )  {
							this.linkedUslugaComplexGrid.setActionDisabled('action_add', false);
							if (!(base_form.findField('UslugaCategory_SysNick').getValue().inlist(['gost2004','gost2011','classmedus','MedOp']))) {
								this.uslugaComplexCompositionGrid.setActionDisabled('action_add', false);
							}
							this.uslugaComplexAttributeGrid.setActionDisabled('action_add', false);
							this.uslugaComplexPlaceGrid.setActionDisabled('action_add', false);
							this.uslugaComplexTariffGrid.setActionDisabled('action_add', false);
							this.uslugaComplexProfileGrid.setActionDisabled('action_add', false);
						}

						if ( this.action == 'lpuedit' ) {
							this.linkedUslugaComplexPanel.collapse();
							this.uslugaComplexCompositionPanel.collapse();
							this.uslugaComplexAttributePanel.collapse();
							this.uslugaComplexProfilePanel.collapse();
							this.uslugaComplexPlacePanel.expand();
							this.uslugaComplexTariffPanel.expand();
							this.uslugaComplexPlaceGrid.fireEvent('expand', this.uslugaComplexPlacePanel);
							this.uslugaComplexTariffGrid.fireEvent('expand', this.uslugaComplexTariffPanel);
							this.uslugaComplexPlaceGrid.setActionDisabled('action_add', false);
							this.uslugaComplexTariffGrid.setActionDisabled('action_add', false);
						} else {
							this.linkedUslugaComplexPanel.fireEvent('expand', this.linkedUslugaComplexPanel);
							this.uslugaComplexCompositionPanel.fireEvent('expand', this.uslugaComplexCompositionPanel);
							this.uslugaComplexAttributePanel.fireEvent('expand', this.uslugaComplexAttributePanel);
						}

                        var isPackage_field = base_form.findField('UslugaComplex_isPackage');
                        isPackage_field.fireEvent('check', isPackage_field, isPackage_field.getValue());

                        base_form.findField('XmlTemplate_id').UslugaComplex_id = usluga_complex_id;
						var XmlTemplate_id = base_form.findField('XmlTemplate_id').getValue();
						if ( XmlTemplate_id ) {
							base_form.findField('XmlTemplate_id').getStore().load({
								callback: function() {
									base_form.findField('XmlTemplate_id').setValue(XmlTemplate_id);
									base_form.findField('XmlTemplate_id').fireEvent('change', base_form.findField('XmlTemplate_id'));
								},
								params: {
									XmlTemplate_id: XmlTemplate_id
								}
							});
						}
						else {
							base_form.findField('XmlTemplate_id').reset();
						}

						loadMask.hide();

						base_form.clearInvalid();

						if ( this.action == 'edit' ) {
							base_form.findField('UslugaComplex_Code').focus(true, 250);
						}
						else {
							this.buttons[this.buttons.length - 1].focus();
						}
					}.createDelegate(this),
					url: '/?c=UslugaComplex&m=loadUslugaComplexEditForm'
				});
			break;

			default:
				loadMask.hide();
				this.hide();
			break;
		}
		//alert(base_form.findField('UslugaCategory_id').getValue());
		//alert(base_form.findField('UslugaCategory_SysNick').getValue());
	},
	width: 750
});