/**
* Справочник РЛС: Форма выбора типа добавляемого лек.средства
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      All
* @access       public
* @autor		Dmitry Storozhev aka nekto_O, remake Alexander Kurakin
* @copyright    Copyright (c) 2011 Swan Ltd.
* @version      10.2016
*/

sw.Promed.swRlsPrepNewEditWindow = Ext.extend(sw.Promed.BaseForm,
{
	title: '',
	modal: true,
	maximizable: true,
	maximized: true,
	shim: false,
	plain: false,
	resizable: false,
	onSelect: Ext.emptyFn,
	layout: 'border',
	buttonAlign: "right",
	objectName: 'swRlsPrepNewEditWindow',
	closeAction: 'hide',
	id: 'swRlsPrepNewEditWindow',
	objectSrc: '/jscore/Forms/Rls/swRlsPrepNewEditWindow.js',
	buttons: [
		{
			handler: function()
			{
				this.ownerCt.doSave();
			},
			iconCls: 'save16',
			text: lang['sohranit']
		},
		'-',
		{
			text      : lang['otmena'],
			tabIndex  : -1,
			tooltip   : lang['otmena'],
			iconCls   : 'cancel16',
			handler   : function()
			{
				this.ownerCt.hide();
			}
		}
	],
	
	listeners: {
		hide: function(w){
			w.disableFields(false);
			w.MainForm.getForm().reset();
			w.PrepDataForm.getForm().reset();
			w.PrepDataForm2.getForm().reset();
			var descriptionTabFields = [
				'DESCTEXTES_PHARMAACTIONS',
				'DESCTEXTES_ACTONORG',
				'DESCTEXTES_COMPONENTSPROPERTIES',
				'DESCTEXTES_PHARMAKINETIC',
				'DESCTEXTES_CLINICALPHARMACOLOGY',
				'DESCTEXTES_DIRECTION',
				'DESCTEXTES_INDICATIONS',
				'DESCTEXTES_RECOMMENDATIONS',
				'DESCTEXTES_CONTRAINDICATIONS',
				'DESCTEXTES_PREGNANCYUSE',
				'DESCTEXTES_SIDEACTIONS',
				'DESCTEXTES_INTERACTIONS',
				'DESCTEXTES_OVERDOSE',
				'DESCTEXTES_SPECIALGUIDELINES',
				'DRUGLIFETIME_TEXT',
				'DRUGSTORCOND_TEXT',
				'DESCTEXTES_PRECAUTIONS',
				'DESCTEXTES_USEMETHODANDDOSES',
				'DESCTEXTES_INSTRFORPAC',
				'RLS_TN_DF_LIMP',
				'RLS_TRADENAMES_DRUGFORMS'
			];
			for (var i = 0; i < descriptionTabFields.length; i++) {
				w.findById(descriptionTabFields[i]).setValue('');
			};
			w.ImagePanel.getForm().reset();
			w.AutorPanel.getForm().reset();
			w.MainForm.hide();
			w.PrepDataForm.hide();
			w.PrepDataForm2.hide();
			w.overwriteTpl();
			w.Image.setTitle(' ');
			w.ImagePanel.find('xtype', 'fileuploadfield')[0].file_uploaded = false;
			w.PharmaGroupGrid.getGrid().getStore().removeAll();
			w.FTGGRLSGrid.getGrid().getStore().removeAll();
			w.MkbGrid.getGrid().getStore().removeAll();
			var pBar = w.ImagePanel.find('name', 'progressbar')[0];
			pBar.updateProgress(0, lang['zagrujeno_0%'], false);
			pBar.setVisible(false);
		}
	},

	show: function()
	{
		sw.Promed.swRlsPrepNewEditWindow.superclass.show.apply(this, arguments);

		if(!arguments[0] || !arguments[0].PrepType_id || !arguments[0].action){
			sw.swMsg.alert(lang['oshibka'], lang['nevernyie_parametryi']);
			this.hide();
			return false;
		}
		
		this.action = arguments[0].action;
		this.PrepType_id = arguments[0].PrepType_id;
		if(arguments[0].NTFR_id){
			this.NTFR_id = arguments[0].NTFR_id;
			if(this.NTFR_id == 2){
				this.PrepType_id = 3;
			} else if(this.NTFR_id == 215){
				this.PrepType_id = 6;
			} else if(this.NTFR_id == 216){
				this.PrepType_id = 4;
			} else if(this.NTFR_id == 217){
				this.PrepType_id = 5;
			}
		}
		this.Actmatters_flag = false;
		this.defineTitle();
		
		var win = this;

		// m.sysolin: сокращение это конечно хорошо, но не до такой степени
		// иногда лучше пожертвовать этим, чем где-то в середине формы
		// наткнуться на неведомое Б_Ф
		var b_f = this.getBaseForm();
		
		win.MainForm.show();
		win.PrepDataForm.show();

		// m.sysolin: что за PrepDataForm2?
		// за что оно отвечает? как понять по названию?
		// товарищи давайте писать внятный код!
		win.PrepDataForm2.show();
		win.PrepTabs.show();

		//b_f.findField('CLSNTFR_ID').getStore().baseParams.PrepType_id = 1;

		// m.sysolin: ниже была каша, но мы же сами себе жизнь усложняем...
		// вообще названия компонентов можно вынести в массив
		// и делать перебор в конце кейсов, через функцию, иначе можно упустить из виду что-либо

		b_f.findField('RlsClsntfr_id').disable();
		win.findById('DoseSizeFieldset').setTitle('Дозировка');

		//подсветить зеленым:
		b_f.findField('CLSDRUGFORMS_ID').setAllowBlank(false);
		b_f.findField('CLSDRUGFORMS_ID').initialAllowBlank = false;
		b_f.findField('vidLF').setAllowBlank(false);
		b_f.findField('DESCTEXTES_COMPOSITION').setAllowBlank(false);
		b_f.findField('TRADENAMES_NAME').setAllowBlank(false);
		b_f.findField('REGCERT_REGNUM').setAllowBlank(false);
		b_f.findField('REGCERT_REGDATERange').setAllowBlank(false);

		//убрать подсвечивание
		b_f.findField('Extemporal_id').setAllowBlank(true);
		b_f.findField('DrugNonpropNames_id').setAllowBlank(true);
		this.MainForm.getForm().findField('NOMEN_SPACKID').setAllowBlank(true);

		//показать компонент
		b_f.findField('MassUnits_NameLatin').showContainer();
		b_f.findField('CONCENUNITS_LAT').showContainer();
		b_f.findField('ACTUNITS_LAT').showContainer();
		b_f.findField('SIZEUNITS_LAT').showContainer();
		b_f.findField('DFSIZE_LAT').showContainer();
		b_f.findField('DESCTEXTES_COMPOSITION').showContainer();
		b_f.findField('DESCTEXTES_COMPOSITION').initialHidden = false;

		//показать компонент
		win.findById('ActmattersFieldset').show();
		win.findById('ActmattersFieldset').initialHidden = false;
		win.findById('Tradename_LatNamesSet').show();

		//скрыть компонент
		b_f.findField('Extemporal_id').hideContainer();
		b_f.findField('Composition').hideContainer();
		b_f.findField('DrugNonpropNames_id').hideContainer();
		b_f.findField('Reregdate').hideContainer();

		//подгрузить хранилище
		b_f.findField('DrugNonpropNames_id').getStore().load();

		// m.sysolin: используется в нескольких местах, можно вынести в отдельную функцию
		b_f.findField('KLCountry_id').getStore().load({

			params:{where:" where KLCountry_id in ('643','112','398') "},

			callback: function() {

				if(getRegionNick() == 'kz'){

					// m.sysolin: статические переменные можно объявить один раз в начале
					// чтобы потом не потерять их и было легче изменить (643,398)
					b_f.findField('KLCountry_id').setValue(398);
				} else {
					b_f.findField('KLCountry_id').setValue(643);
				}
			}
		});

		/*this.MainForm.find('name', 'PIECES')[0].setVisible(true);
		this.MainForm.find('name', 'CUBICUNITS')[0].setVisible(true);
		this.MainForm.find('name', 'MASSUNITS2')[0].setVisible(true);*/

        //загрузка класса НТФР по умолчанию
        if (this.PrepType_id != 3) {
            b_f.findField('RlsClsntfr_id').setValueById(1);
        }

		switch(this.PrepType_id){
			case 2:
			    break;
			case 3:
				b_f.findField('RlsClsntfr_id').getStore().removeAll();

				// m.sysolin: используется в нескольких местах, можно вынести в отдельную функцию
				b_f.findField('RlsClsntfr_id').getStore().load({
					params:{where:'where RlsClsntfr_id > 1 or RlsClsntfr_id = 2'},
					callback:function(){b_f.findField('RlsClsntfr_id').setValue(2);}
				});

				//b_f.findField('RlsClsntfr_id').getStore().baseParams.PrepType_id = 3;
				b_f.findField('RlsClsntfr_id').enable();
				win.findById('ActmattersFieldset').hide();
				win.findById('ActmattersFieldset').initialHidden = true;
				win.findById('Tradename_LatNamesSet').hide();
				b_f.findField('DrugNonpropNames_id').showContainer();
				b_f.findField('DrugNonpropNames_id').setAllowBlank(false);
				b_f.findField('CLSDRUGFORMS_ID').setAllowBlank(true);
				b_f.findField('CLSDRUGFORMS_ID').initialAllowBlank = true;
				b_f.findField('vidLF').setAllowBlank(true);
				win.findById('DoseSizeFieldset').setTitle('Размер');
				b_f.findField('MassUnits_NameLatin').hideContainer();
				b_f.findField('CONCENUNITS_LAT').hideContainer();
				b_f.findField('ACTUNITS_LAT').hideContainer();
				b_f.findField('SIZEUNITS_LAT').hideContainer();
				b_f.findField('DFSIZE_LAT').hideContainer();
				b_f.findField('DESCTEXTES_COMPOSITION').setAllowBlank(true);
				b_f.findField('DESCTEXTES_COMPOSITION').hideContainer();
				b_f.findField('DESCTEXTES_COMPOSITION').initialHidden = true;
			    break;
			case 4:
				b_f.findField('RlsClsntfr_id').setValueById(216);
				win.findById('ActmattersFieldset').hide();
				win.findById('ActmattersFieldset').initialHidden = true;
				b_f.findField('Extemporal_id').setAllowBlank(false);
				b_f.findField('Extemporal_id').showContainer();
				b_f.findField('Composition').showContainer();
				b_f.findField('REGCERT_REGDATERange').setAllowBlank(true);
				b_f.findField('REGCERT_REGNUM').setAllowBlank(true);
                break;
			case 5:
				b_f.findField('RlsClsntfr_id').setValueById(217);

				// m.sysolin: используется в нескольких местах, можно вынести в отдельную функцию
				b_f.findField('KLCountry_id').getStore().load({
					callback: function() {
						if(getRegionNick() == 'kz'){
							b_f.findField('KLCountry_id').setValue(398);
						} else {
							b_f.findField('KLCountry_id').setValue(643);
						}
					}
				});

				b_f.findField('REGCERT_REGNUM').setAllowBlank(true);
			    break;
			case 6:
				b_f.findField('RlsClsntfr_id').setValueById(215);
			    break;
			default:
				this.MainForm.getForm().findField('RlsClsntfr_id').setValue(1);
			    break;
		}
		
		if(this.PrepType_id.inlist([2,3,4,6])){
			b_f.findField('RegOwner').setAllowBlank(this.PrepType_id == 4);
			b_f.findField('Manufacturer').setAllowBlank(false);
			b_f.findField('Packer').setAllowBlank(false);
		} else {
			b_f.findField('RegOwner').setAllowBlank(true);
			b_f.findField('Manufacturer').setAllowBlank(true);
			b_f.findField('Packer').setAllowBlank(true);
		}
		this.doLayout();

		win.PrepTabs.setActiveTab(2);
		win.PrepTabs.setActiveTab(0);
		this.LsLinkGrid.removeAll({clearAll: true});
		this.LsLinkGrid.addActions({name:'action_unlink', text: 'Удалить свзяь', handler: function() {
			win.unlinkLsLink();
		}});
		this.LsLinkGrid.addActions({name:'action_link', text: 'Связать', handler: function() {
			win.linkLsLink();
		}});

		if(this.action == 'add') {
            this.PrepDataForm2.findById('RLS_TN_DF_LIMP').setValue(1);
            this.PrepDataForm2.findById('RLS_TRADENAMES_DRUGFORMS').setValue(1);
            if(this.PrepType_id == 4){
                b_f.findField('Extemporal_id').oldValue = null;
            }
            var resp_obj = {};
            resp_obj.autor_orgname_ins = getGlobalOptions().lpu_name;
            resp_obj.autor_orgname_upd = resp_obj.autor_orgname_ins;

            resp_obj.autor_username_ins = (getGlobalOptions().CurMedPersonal_FIO) ? getGlobalOptions().CurMedPersonal_FIO : '-';
            resp_obj.autor_username_upd = resp_obj.autor_username_ins;

            resp_obj.autor_date_ins = Ext.util.Format.date(Date(), 'd.m.Y H:i:s');
            resp_obj.autor_date_upd = resp_obj.autor_date_ins;

            this.AutorPanel.getForm().setValues(resp_obj);

            var measure_combo = b_f.findField('measure');
            measure_combo.setValue(1);
            measure_combo.setLinkedFieldsVisible();
        } else {
			if(!arguments[0].Nomen_id){
				sw.swMsg.alert(lang['oshibka'], lang['nevernyie_parametryi_formyi']);
				this.hide();
				return false;
			}

			// m.sysolin: но если есть в кармане паааааа-чка..си-га-рет...
			var lm = this.getLoadMask(lang['zagruzka_dannyih']);

			lm.show();
			b_f.load({
				url: '/?c=Rls&m=getPrep',
				params: {
					Nomen_id: arguments[0].Nomen_id,
					PrepType_id: arguments[0].PrepType_id
				},
				failure: function(){
					lm.hide();
				},
				success: function(frm, r){
					lm.hide();
					var resp_obj = Ext.util.JSON.decode(r.response.responseText)[0];

					win.LsLinkGrid.loadData({globalFilters: {PREP_ID: resp_obj.Prep_id, start: 0, limit: 100}});

					// m.sysolin: :-(
					var f1, f2, f3, f4, f5, f6, f7, f71, f72, f8, f9, f10;
					
					if(resp_obj.actmatters && resp_obj.actmatters[0]){
						b_f.findField('Actmatters_Names').setValue(resp_obj.actmatters[0].RUSNAME);
						b_f.findField('ACTMATTERS').setValue(resp_obj.actmatters[0].ACTMATTERS_ID);
						b_f.findField('Actmatters_LatName').setValue(resp_obj.actmatters[0].LATNAME);
						b_f.findField('Actmatters_LatNameGen').setValue(resp_obj.actmatters[0].ACTMATTERS_LatNameGen);
					}

                    // Класс НТФР
                    if (resp_obj.CLSNTFR_ID) {
                        if (win.PrepType_id == 3) {
                            b_f.findField('RlsClsntfr_id').setValue(resp_obj.CLSNTFR_ID);
                        } else {
                            b_f.findField('RlsClsntfr_id').setValueById(resp_obj.CLSNTFR_ID);
                        }
                    }
					
					// Лекарственная форма
					if(b_f.findField('CLSDRUGFORMS_ID')){
						f1 = b_f.findField('CLSDRUGFORMS_ID');
						f1.getStore().baseParams['CLSDRUGFORMS_ID'] = f1.getValue();
						f1.getStore().load({
							callback: function(store){
								f1.setValue(f1.getValue());
								b_f.findField('CLSDRUGFORMS_NameLatin').setValue(store[0].data.CLSDRUGFORMS_NameLatin);
								b_f.findField('CLSDRUGFORMS_NameLatinSocr').setValue(store[0].data.CLSDRUGFORMS_NameLatinSocr);
								f1.fireEvent('change');
							}
						});
					}

					if(b_f.findField('Extemporal_id') && b_f.findField('Extemporal_id').getValue()){
						Ext.Ajax.request({
							url: '/?c=Extemporal&m=loadExtemporal',
							params: {Extemporal_id:b_f.findField('Extemporal_id').getValue()},
							callback: function(options, success, response) {
								if ( success ) {
									var result = Ext.util.JSON.decode(response.responseText);
									if(result[0] && result[0].Composition){
										b_f.findField('Composition').setValue(result[0].Composition);
									}
								}
							}
						});
					}
					
					// Вид ЛФ
					if(b_f.findField('vidLF')) {
						var vlfcombo = b_f.findField('vidLF');
						if( resp_obj.DFMASSID != '' ) {
							vlfcombo.setValue(1);
							vlfcombo.fireEvent('select', vlfcombo, vlfcombo.getStore().getAt(0), 0);
							var mcombo = b_f.findField('DFMASSID');
							mcombo.fireEvent('change',mcombo,resp_obj.DFMASSID);
						} else if ( resp_obj.DFCONCID != '' ) {
							vlfcombo.setValue(2);
							vlfcombo.fireEvent('select', vlfcombo, vlfcombo.getStore().getAt(1), 1);
						} else if ( resp_obj.DFACTID != '' ) {
							vlfcombo.setValue(3);
							vlfcombo.fireEvent('select', vlfcombo, vlfcombo.getStore().getAt(2), 2);
						} else if ( resp_obj.DFSIZEID != '' ) {
							vlfcombo.setValue(4);
							vlfcombo.fireEvent('select', vlfcombo, vlfcombo.getStore().getAt(3), 3);
						}
					}

					// Название характеристики ЛФ
					if(b_f.findField('DFCHARID')){
						f2 = b_f.findField('DFCHARID');
						win.setFieldsValue(f2, 'DRUGFORMCHARS');
					}
					
					// Название перв. упаковки
					if(b_f.findField('NOMEN_PPACKID')){
						f3 = b_f.findField('NOMEN_PPACKID');
						win.setFieldsValue(f3, 'DRUGPACK');
					}
					
					// Ед. изм. первичной упаковки
                    var measure_combo = b_f.findField('measure');
					if(resp_obj.NOMEN_PPACKVOLUME > 0) {
                        measure_combo.setValue(2);
                    } else if(resp_obj.NOMEN_PPACKMASS > 0) {
                        measure_combo.setValue(3);
                    } else {
                        measure_combo.setValue(1);
                    }
                    measure_combo.setLinkedFieldsVisible();
					
					// Назв. комплекта к перв. упаковке
					if(b_f.findField('NOMEN_SETID')){
						f4 = b_f.findField('NOMEN_SETID');
						win.setFieldsValue(f4, 'DRUGSET');
					}
					
					// Название втор. упаковки
					if(b_f.findField('NOMEN_UPACKID')){
						f5 = b_f.findField('NOMEN_UPACKID');
						win.setFieldsValue(f5, 'DRUGPACK');
					}
					
					// Название трет. упаковки
					if(b_f.findField('NOMEN_SPACKID')){
						f6 = b_f.findField('NOMEN_SPACKID');
						win.setFieldsValue(f6, 'DRUGPACK');
					}

					// Производитель
					if(b_f.findField('Manufacturer') && resp_obj.FIRMS_ID != ''){
						f7 = b_f.findField('Manufacturer');
						f7.setValue(resp_obj.FIRMS_ID);
						win.setFieldsValue(f7, 'FIRMS');
					}

					// Упаковщик
					if(b_f.findField('Packer') && resp_obj.Packer != ''){
						f71 = b_f.findField('Packer');
						f71.setValue(resp_obj.Packer);
						win.setFieldsValue(f71, 'FIRMS');
					}

					// Владелец РУ
					if(b_f.findField('RegOwner') && resp_obj.RegOwner != ''){
						f72 = b_f.findField('RegOwner');
						f72.setValue(resp_obj.RegOwner);
						win.setFieldsValue(f72, 'FIRMS');
					}
					
					b_f.findField('REGCERT_REGDATERange').setValue(resp_obj.REGCERT_REGDATE+' - '+resp_obj.REGCERT_ENDDATE);
					if(resp_obj.REGCERT_ENDDATE != ''){
						b_f.findField('Reregdate').showContainer();
					}

					if(resp_obj.KLCountry_id > 0){
						b_f.findField('KLCountry_id').setValue(resp_obj.KLCountry_id);
					} else {
						if(getRegionNick() == 'kz'){
							b_f.findField('KLCountry_id').setValue(398);
						} else {
							b_f.findField('KLCountry_id').setValue(643);
						}
					}
						
					// Классификация МКБ-10
					/*if(b_f.findField('CLSIIC_ID')){
						f9 = b_f.findField('CLSIIC_ID');
						win.setFieldsValue(f9, 'CLSIIC');
					}*/
					//win.MkbGrid.getGrid().getStore().loadData(resp_obj.clsiics, true);

					win.PrepDataForm2.findById('DRUGLIFETIME_TEXT').setValue(resp_obj.DRUGLIFETIME_TEXT);
					win.PrepDataForm2.findById('DRUGSTORCOND_TEXT').setValue(resp_obj.DRUGSTORCOND_TEXT);
					win.PrepDataForm2.findById('DESCTEXTES_PRECAUTIONS').setValue(resp_obj.DESCTEXTES_PRECAUTIONS);
					win.PrepDataForm2.findById('DESCTEXTES_USEMETHODANDDOSES').setValue(resp_obj.DESCTEXTES_USEMETHODANDDOSES);
					win.PrepDataForm2.findById('DESCTEXTES_INSTRFORPAC').setValue(resp_obj.DESCTEXTES_INSTRFORPAC);
					win.PrepDataForm2.findById('RLS_TN_DF_LIMP').setValue(resp_obj.TN_DF_LIMP);
					win.PrepDataForm2.findById('RLS_TRADENAMES_DRUGFORMS').setValue(resp_obj.TRADENAMES_DRUGFORMS);

					
					for(var field in resp_obj) {
						if (win.PrepDataForm.findById(field))
							win.PrepDataForm.findById(field).setValue(resp_obj[field]);
					}

					// Покажем картинку (если есть)
					win.overwriteTpl(resp_obj);
					
					// Панель "Автор"
					resp_obj.autor_date_ins = Ext.util.Format.date(resp_obj.autor_date_ins, 'd.m.Y H:i:s');
					resp_obj.autor_date_upd = Ext.util.Format.date(resp_obj.autor_date_upd, 'd.m.Y H:i:s');
					win.AutorPanel.getForm().setValues(resp_obj);
					if(!win.AutorPanel.getForm().findField('autor_date_ins')){
						setTimeout(function(){
							win.AutorPanel.getForm().setValues(resp_obj);
						},3000);
					}

					if (resp_obj.clsatcs.length != 0) {
						setTimeout((function() {
							win.AtcGrid.getGrid().getStore().loadData(resp_obj.clsatcs);
						}), 3000);
					}

					if (resp_obj.pharmagroups.length != 0) {
						setTimeout((function() {
						win.PharmaGroupGrid.getGrid().getStore().loadData(resp_obj.pharmagroups);
						}), 3000);
					}

					if (resp_obj.ftggrlss.length != 0) {
						setTimeout((function() {
						win.FTGGRLSGrid.getGrid().getStore().loadData(resp_obj.ftggrlss);
						}), 3000);
					}

					if (resp_obj.clsiics.length != 0) {
						setTimeout((function() {
						win.MkbGrid.getGrid().getStore().loadData(resp_obj.clsiics);
						}), 3000);
					}

					if (win.action == 'view') {
						win.disableFields(true);
					}
					
					b_f.findField('PREPFULLNAME').disable();

					win.PrepTabs.setActiveTab(3);
					win.PrepTabs.setActiveTab(2);
					win.PrepTabs.setActiveTab(1);
					win.PrepTabs.setActiveTab(0);
				}
			});
		}
	},
	
	setFieldsValue: function(field, obj)
	{
		if(field.getValue() == null || field.getValue() == '' || field.getValue() == 0){
			field.reset();
			return false;
		}
		field.getStore().baseParams = {
			object: obj,
			stringfield: field.displayField
		};
		field.getStore().baseParams[obj+'_ID'] = field.getValue();
		field.getStore().load({
			callback: function(){
				field.setValue(field.getValue());
				field.fireEvent('change');
			}
		});
	},
	
	defineTitle: function()
	{
		var title = lang['spravochnik_medikamentov'];
		switch(this.action){
			case 'add':
				title += lang['_dobavlenie'];
			break;
			case 'edit':
				title += lang['_redaktirovanie'];
			break;
			case 'view':
				title += lang['_prosmotr'];
			break;
		}
		
		switch(this.PrepType_id){
			case 3:
				title += (' '+lang['meditsinskogo_tovara']);
			break;
			case 4:
				title += (' '+lang['lekarstvennogo_sredstva_ekstemporalnogo']);
			break;
			default:
				title += (' '+lang['lekarstvennogo_sredstva_rls']);
			break;
		}
		this.setTitle(title);
	},

	doValidate: function(form) {

		var frm  = form,
			ivalid_fields = [];

		frm.items.each(function(component){

			if (component.layout)
				component = component.items.items[0];

			if (!component.isValid())
				ivalid_fields.push(component);
		});

		return ivalid_fields;
	},

	setFormParams: function(p_data, p_list, form) {

		var frm  = form,
			params_data = p_data,
			params_list = p_list;

		params_list.forEach(function(title){

			params_data[title] = frm.findById(title).getValue();
			if(title.indexOf('RLS_') == 0){
				var correct_title = title.replace('RLS_','');
				params_data[correct_title] = params_data[title];
			}
		});

		return params_data;
	},

	// m.sysolin: сделал рефакторинг функции
	doSave: function()
	{
		var win = this,
			tabs = win.PrepTabs,
			form = this.MainForm.getForm(), // основная вкладка
			expDateAndStorageTab = this.PrepDataForm2, // вторая вкладка
			descriptionTab = this.PrepDataForm, // третья вкладка
			invalidFields =  [],
			params = {};

		//проверяем валиднось первой вкладки
		if(!form.isValid()){

			sw.swMsg.alert(lang['oshibka'], lang['ne_zapolnenyi_obyazatelnyie_polya']);
			return false;
		}

		//проверяем валиднось второй вкладки
		invalidFields = win.doValidate(expDateAndStorageTab);

		// m.sysolin: по задаче https://redmine.swan.perm.ru/issues/105434
		if (invalidFields.length > 0) {

			sw.swMsg.alert(lang['oshibka'], lang['ne_zapolnenyi_obyazatelnyie_polya']);

			tabs.setActiveTab(1); // переключаем на эту табу чтобы видеть инвалиды

			invalidFields.forEach(function(invalid_field) {

				invalid_field.setAllowBlank(false);
			});

			return false;
		}

		var emptyREGDATERange = Ext.isEmpty(form.findField('REGCERT_REGDATERange').getValue1());

		if (this.PrepType_id != 4 && this.PrepType_id != 5 && emptyREGDATERange){
			sw.swMsg.alert(lang['oshibka'], 'Дата начала периода - обязательна для поля Период действия РУ');
			return false;
		}

		var descriptionTabFields = [
			'DESCTEXTES_PHARMAACTIONS',
			'DESCTEXTES_ACTONORG',
			'DESCTEXTES_COMPONENTSPROPERTIES',
			'DESCTEXTES_PHARMAKINETIC',
			'DESCTEXTES_CLINICALPHARMACOLOGY',
			'DESCTEXTES_DIRECTION',
			'DESCTEXTES_INDICATIONS',
			'DESCTEXTES_RECOMMENDATIONS',
			'DESCTEXTES_CONTRAINDICATIONS',
			'DESCTEXTES_PREGNANCYUSE',
			'DESCTEXTES_SIDEACTIONS',
			'DESCTEXTES_INTERACTIONS',
			'DESCTEXTES_OVERDOSE',
			'DESCTEXTES_SPECIALGUIDELINES'
		];

		var expDateAndStorageTabFields = [
			'DRUGLIFETIME_TEXT',
			'DRUGSTORCOND_TEXT',
			'DESCTEXTES_PRECAUTIONS',
			'DESCTEXTES_USEMETHODANDDOSES',
			'DESCTEXTES_INSTRFORPAC',
			'RLS_TN_DF_LIMP',
			'RLS_TRADENAMES_DRUGFORMS'
		];

		params.CLSNTFR_ID = form.findField('RlsClsntfr_id').getValue();
		params = win.setFormParams(params, descriptionTabFields, descriptionTab);
		params = win.setFormParams(params, expDateAndStorageTabFields, expDateAndStorageTab);

		//из панели Классификация АТХ
		if( this.AtcGrid.getGrid().getStore().getCount() > 0 ) {
			var atcs = [];
			this.AtcGrid.getGrid().getStore().each(function(r) {
				atcs.push(r.get('CLSATC_ID'));
			});
			params.CLSATCS = escape(atcs.join('|'));
		}

		//из панели Фармакологическая группа
		if( this.PharmaGroupGrid.getGrid().getStore().getCount() > 0 ) {
			var pharmagroups = [];
			this.PharmaGroupGrid.getGrid().getStore().each(function(r) {
				pharmagroups.push(r.get('CLSPHARMAGROUP_ID'));
			});
			params.PHARMAGROUPS = escape(pharmagroups.join('|'));
		}
		//из панели Фармакотерапевтическая группа
		if( this.FTGGRLSGrid.getGrid().getStore().getCount() > 0 ) {
			var ftggrlss = [];
			this.FTGGRLSGrid.getGrid().getStore().each(function(r) {
				ftggrlss.push(r.get('FTGGRLS_ID'));
			});
			params.FTGGRLSS = escape(ftggrlss.join('|'));
		}
		// то же самое с диагнозами МКБ-10
		if( this.MkbGrid.getGrid().getStore().getCount() > 0 ) {
			var mkbs = [];
			this.MkbGrid.getGrid().getStore().each(function(r) {
				mkbs.push(r.get('CLSIIC_ID'));
			});
			params.CLSIICS = escape(mkbs.join('|'));
		}
		
		if(win.action == 'add'){
			form.findField('PrepType_id').setValue(2);
			params.PrepType_id = 2;
		} else {
			params.PrepType_id = form.findField('PrepType_id').getValue();
		}
		
		// Если файл загружен
		if(this.ImagePanel.find('xtype', 'fileuploadfield')[0].file_uploaded){
			params.file_uploaded = 1;
		}
		
		var loadMask = this.getLoadMask(lang['sohranenie']);
		loadMask.show();

		form.submit({

			params: params,

			success: function(f, resp){

				loadMask.hide();
				win.hide();
			},
			failure: function(f, resp){

				loadMask.hide();
				var response = Ext.util.JSON.decode(resp.response.responseText);
			}
		});
	},

	openActmattersEditWindow: function(action)
	{
		var 
			wnd = this,
			record = wnd.formList.List.getGrid().getSelectionModel().getSelected(),
			Actmatters_id = ( record ) ? record.get('ACTMATTERS_ID') : null
		;
		getWnd('swActmattersEditWindow').show({
			action: action,
			Actmatters_id: Actmatters_id,
			onSave: () => {
				wnd.formList.List.refreshRecords(null, 0);
			}
		});
	},
	addActmatterInGrid: function()
	{
		var wnd = this;

		var combo = this.MainForm.getForm().findField('Actmatters_Names');
		var id_combo = this.MainForm.getForm().findField('ACTMATTERS');
		var combo_lat = this.MainForm.getForm().findField('Actmatters_LatName');
		var combo_latgen = this.MainForm.getForm().findField('Actmatters_LatNameGen');

		if(!this.formList){
			this.formList = new sw.Promed.swListSearchWindow({
				title: lang['deystvuyuschee_veschestvo_poisk'],
				id: 'Actmatters_SearchWindow',
				object: 'rls.Actmatters',
				prefix: 'actmatters',
				useBaseParams: true,
				actions: [
					{name: 'action_add', handler: wnd.openActmattersEditWindow.createDelegate(wnd, ['add'])},
					{name: 'action_edit', handler: wnd.openActmattersEditWindow.createDelegate(wnd, ['edit'])},
					{name: 'action_view', handler: wnd.openActmattersEditWindow.createDelegate(wnd, ['view'])},
					{name:'action_delete', disabled: true}
				],
				store: new Ext.data.Store({
					autoLoad: false,
					baseParams: {
						object: 'ACTMATTERS',
						stringfield: 'RUSNAME',
						additionalFields: 'LATNAME,Actmatters_LatNameGen'
					},
					reader: new Ext.data.JsonReader({
						id: 'ACTMATTERS_ID'
					}, [{
						mapping: 'ACTMATTERS_ID',
						name: 'ACTMATTERS_ID',
						type: 'int'
					},{
						mapping: 'RUSNAME',
						name: 'RUSNAME',
						type: 'string'
					},{
						mapping: 'LATNAME',
						name: 'LATNAME',
						type: 'string'
					},{
						mapping: 'Actmatters_LatNameGen',
						name: 'Actmatters_LatNameGen',
						type: 'string'
					}]),
					url: '/?c=Rls&m=getDataForComboStoreWithFields'
				})
			});
		}
		this.formList.show({
			onSelect: function(data){
				combo.setValue(data.RUSNAME);
				id_combo.setValue(data.ACTMATTERS_ID);
				combo_lat.setValue(data.LATNAME);
				combo_latgen.setValue(data.Actmatters_LatNameGen);
			}
		});
	},
	
	overwriteTpl: function(obj)	{
		var win = this;
		if(!obj){
			var obj = {};
			obj.file_url = '';
		}
		this.Image.tpl = new Ext.Template(this.ImgTpl);
		if(this.Image.body){
			this.Image.tpl.overwrite(this.Image.body, obj);
		} else if(obj.file_url != '') {
			setTimeout(function(){
				if(win.Image.body){
					win.Image.tpl.overwrite(win.Image.body, obj);
				}
			},3000)
		}
	},
	
	/*showSteer: function(name, show)
	{
		var steer = this.find('name', name)[0];
		if(show){
			steer.setVisible(true);
			setTimeout(function(){ this.showSteer(name, false); }.createDelegate(this), 10000);
		} else {
			steer.setVisible(false);
		}
	},*/

	disableFields: function(isView) {
        var field_arr = [
            'Actmatters_Names'
        ];

		this.findBy(function(field){
			if(
                field.disable &&
                (
                    (field.xtype && !field.xtype.inlist(['panel', 'fieldset'])) ||
                    (field.name && field.name.inlist(field_arr))
                )
            ) {
				if(isView) {
                    field.disable();
                } else {
                    field.enable();
                }
			}
		});

        if(isView) {
            this.CLSDRUGFORMSField.disable();
        } else {
            this.CLSDRUGFORMSField.enable();
        }

		this.MkbGrid.ViewActions.action_add.setDisabled(isView);
		this.MkbGrid.ViewActions.action_delete.setDisabled(isView);
		this.buttons[0].setVisible(!isView);
	},
	
	getXHR: function () {
		var xmlhttp;
		try {
			xmlhttp = new ActiveXObject("Msxml2.XMLHTTP");
		} 
		catch (e) {
			try {
				xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
			} 
			catch (E) {
				xmlhttp = false;
			}
		}
		if (!xmlhttp && typeof XMLHttpRequest != 'undefined') {
			xmlhttp = new XMLHttpRequest();
		}
		return xmlhttp;
	},
	
	setUploadProgress: function( pg ) {
		var pBar = this.ImagePanel.find('name', 'progressbar')[0];
		if( !pBar.isVisible() )
			pBar.setVisible(true);
		pBar.updateProgress(pg/100, lang['zagrujeno']+pg+'%', true);
	},
	
	// Тупо для теста загрузки с пом-ю FileAPI интересножблеать=)
	loadFileWithFileAPI: function( cb )
	{
		var field = this.ImagePanel.find('xtype', 'fileuploadfield')[0];
		try {
			// Исходный файл
			var file = field.fileInput.dom.files[0];
			if( file.size > 2 * 1024 * 1024 ) {
				sw.swMsg.alert(lang['oshibka'], lang['nelzya_zagruzit_fayl_obyemom_bolee_2mb']);
				return true;
			}
			
			if ( typeof FileReader == 'function' ) {
				var reader = new FileReader();
				
				// После того как reader прочитал файлик
				reader.onload = function(e) {
					var xhr = this.getXHR();
					
					xhr.upload.onprogress = function(e) { //onprogress
						if( e.lengthComputable ) {
							this.setUploadProgress(Math.floor((e.loaded/e.total)*100));
						}
					}.createDelegate(this);
					xhr.upload.onload = function(e) {
						this.setUploadProgress(100);
					}.createDelegate(this);
					
					xhr.onreadystatechange = function () {
						if (xhr.readyState == 4) {
							if(xhr.status == 200) {
								cb.call(this, this.ImagePanel.getForm(), { response: xhr } );
							} else {
								return false;
							}
						}
					}.createDelegate(this);
					
					xhr.open('POST', this.ImagePanel.url, true);
					var bdy = "xxxxxxxxx"; // граница
					// формируем заголовок запроса
					xhr.setRequestHeader('Content-Type', 'multipart/form-data, boundary='+bdy);
					xhr.setRequestHeader('Cache-Control', 'no-cache');
					// формируем тело запроса
					var body = "--" + bdy + "\r\n";
					body += "Content-Disposition: form-data; name='file'; filename='" + unescape( encodeURIComponent( file.name ) ) + "'\r\n";
					body += "Content-Type: application/octet-stream\r\n\r\n";
					body += reader.result + "\r\n";
					body += "--" + bdy + "--";
					
					if( xhr.sendAsBinary ) { // для FF
						xhr.sendAsBinary(body);
					} else {
						var ords = Array.prototype.map.call(body, function(x) {
							return x.charCodeAt(0) & 0xff;
						}),
						ui8a = new Uint8Array(ords);
						xhr.send(ui8a.buffer);
					}
				}.createDelegate(this);
				this.setUploadProgress(0);
				reader.readAsBinaryString(file);
				return true;
			} else {
				return false;
			}
		} catch (E) {
			// HTML5 FileAPI не поддерживается :(
			//log('exception: '+E);
			return false;
		}
	},

	getBaseForm: function() {
		return this['MainForm'].getForm();
	},
	
	getStringValue: function(f) {
		return f['get' + (/id/ig.test(f.hiddenName || f.name) ? 'Raw' : '') + 'Value']();
	},
	
	setPrepFullName: function() {
		var form = this.getBaseForm(),
			field = form.findField('PREPFULLNAME'),
			v = '';
		
		var fields = [
			'TRADENAMES_NAME'
			,'CLSDRUGFORMS_ID'
			,'DFMASS + DFMASSID'
			,'DFCONC + DFCONCID'
			,'DFACT + DFACTID'
			,'DRUGDOSE'
			,'DFSIZE + DFSIZEID'
			,'DFCHARID'
			,'NOMEN_SETID'
			,'NOMEN_PPACKMASS + NOMEN_PPACKMASSUNID'
			,'NOMEN_PPACKVOLUME + NOMEN_PPACKCUBUNID'
			,'NOMEN_UPACKINSPACK'
			,'NOMEN_PPACKID'
			,'NOMEN_UPACKID'
			,'NOMEN_SPACKID'
			,'RegOwner'
			,'Manufacturer'
			,'Packer'
			,'NOMEN_EANCODE'
			,'REGCERT_REGNUM'
			,'REGCERT_REGDATERange'
			,'Reregdate'
			,'REGCERT_excDT'
		];
		for(var i=0; i<fields.length; i++) {
			if(form.findField(fields[i])) {
				if(fields[i] == 'Manufacturer'){
					if(!Ext.isEmpty(form.findField('Manufacturer').getValue()) && form.findField('RegOwner').getValue() != form.findField('Manufacturer').getValue()){
						v += 'Пр.:';
					} else {
						continue;
					}
				}
				if(fields[i] == 'Packer'){
					if(!Ext.isEmpty(form.findField('Packer').getValue()) && form.findField('Packer').getValue() != form.findField('Manufacturer').getValue()){
						v += 'Уп.:';
					} else {
						continue;
					}
				}
				// Имеем дело с обычным полем
				if(fields[i].inlist(['RegOwner','Manufacturer','Packer'])){
					v += form.findField(fields[i]).getRawValue();
				} else if(fields[i].inlist(['Reregdate']) && !Ext.isEmpty(form.findField(fields[i]).getValue())){
					v += Ext.util.Format.date(form.findField(fields[i]).getValue(), 'd.m.Y');
				} else if(fields[i].inlist(['REGCERT_REGDATERange'])){
					if(!Ext.isEmpty(form.findField(fields[i]).getValue1()) && form.findField(fields[i]).getValue1() != '__.__.____'){
						v += 'c ';
						v += Ext.util.Format.date(form.findField(fields[i]).getValue1(), 'd.m.Y');
					}
					if(!Ext.isEmpty(form.findField(fields[i]).getValue2()) && form.findField(fields[i]).getValue2() != '__.__.____'){
						v += ' по ';
						v += Ext.util.Format.date(form.findField(fields[i]).getValue2(), 'd.m.Y');
					}
				} else {
					v += this.getStringValue(form.findField(fields[i]));
				}
			} else if(/\+/g.test(fields[i])) {
				// Имеем дело с составным полем
				var ms = fields[i].match(/(\w+)[\s\+]+(\w+)/i);
				if(ms.length == 3) {
					for(var j=0; j<ms.length; j++) {
						if( form.findField(ms[j]) ) {
							v += this.getStringValue(form.findField(ms[j]));
						}
					}
				}
			}
			if( v != '' && i < fields.length-1 && v.charAt(v.length-2)+v.charAt(v.length-1) != ', ' ) {
				v += ', ';
			}
		}
		if( v.charAt(v.length-2)+v.charAt(v.length-1) == ', ' )
			v = v.slice(0, v.length-2);
		
		field.setValue(v);
	},
	
	onRlsClsntfrChange: function() {
		var form = this.getBaseForm(),
			rlsclsntfr = form.findField('RlsClsntfr_id'),
			rlsclsntfr_id = rlsclsntfr.getValue(),
			rlsclsntfr_pid = rlsclsntfr.getFieldValue('RlsClsntfr_pid'),
			actmatters_fieldset = this.findById('ActmattersFieldset'),
			clsdrugforms = form.findField('CLSDRUGFORMS_ID'),
			desctextes = form.findField('DESCTEXTES_COMPOSITION');
			
		if (!Ext.isEmpty(rlsclsntfr_id) && (rlsclsntfr_id == 1 || rlsclsntfr_pid == 1) && rlsclsntfr_id != 216 && !this.findById('ActmattersFieldset').initialHidden) {
			actmatters_fieldset.show();
		} else {
			actmatters_fieldset.hide();
		}
			
		if (!Ext.isEmpty(rlsclsntfr_id) && (rlsclsntfr_id == 1 || rlsclsntfr_pid == 1) && !clsdrugforms.initialAllowBlank) {
			clsdrugforms.setAllowBlank(false);
		} else {
			clsdrugforms.setAllowBlank(true);
		}
			
		if (!Ext.isEmpty(rlsclsntfr_id) && (rlsclsntfr_id == 1 || rlsclsntfr_pid == 1) && !desctextes.initialHidden) {
			desctextes.showContainer();
			desctextes.setAllowBlank(false);
		} else {
			desctextes.hideContainer();
			desctextes.setAllowBlank(true);
		}
	},
	
	addMkb: function() {
		var grid = this.MkbGrid;
		
		if( !grid.formList ) {
			grid.formList = new sw.Promed.swListSearchWindow({
				title: lang['mkb-10_poisk'],
				id: grid.id + '_SearchWindow',
				object: 'rls.CLSIIC',
				prefix: 'clsiic',
				useBaseParams: true,
				
				store: new Ext.data.Store({
					autoLoad: false,
					baseParams: {
						object: 'CLSIIC',
						stringfield: 'NAME'
					},
					reader: new Ext.data.JsonReader({
						id: 'CLSIIC_ID'
					}, [{
						mapping: 'CLSIIC_ID',
						name: 'CLSIIC_ID',
						type: 'int'
					},{
						mapping: 'NAME',
						name: 'NAME',
						type: 'string'
					}]),
					url: '/?c=Rls&m=getDataForComboStore'
				})
			
			});
		}
		
		grid.formList.show({
			onSelect: function(data) {
				if( grid.getGrid().getStore().find('CLSIIC_ID', new RegExp('^' + data.CLSIIC_ID + '$')) > -1 )
					return false;
				
				grid.getGrid().getStore().loadData([data], true);
				grid.ViewGridPanel.getView().refresh();
			}
		});
	},
	deleteAtc: function() {
		sw.swMsg.show({
			buttons:Ext.Msg.YESNO,
			fn:function (buttonId, text, obj) {
				if (buttonId == 'yes') {
					var grid = this.AtcGrid.getGrid(),
						record = grid.getSelectionModel().getSelected();
					if( !record ) return false;
					grid.getStore().remove(record);
					if (grid.getStore().getCount() < 1) {
						this.AtcGrid.ViewActions.action_delete.setDisabled(1);
					} else {
						grid.getSelectionModel().selectRow(0);
					}
				}
			}.createDelegate(this),
			icon:Ext.MessageBox.QUESTION,
			msg:langs('Вы хотите удалить запись?'),
			title:langs('Подтверждение')
		});
	},

	deletePharmaGroup: function() {
		sw.swMsg.show({
			buttons:Ext.Msg.YESNO,
			fn:function (buttonId, text, obj) {
				if (buttonId == 'yes') {
					var grid = this.PharmaGroupGrid.getGrid(),
						record = grid.getSelectionModel().getSelected();
					if( !record ) return false;
					grid.getStore().remove(record);
					if (grid.getStore().getCount() < 1) {
						this.PharmaGroupGrid.ViewActions.action_delete.setDisabled(1);
					} else {
						grid.getSelectionModel().selectRow(0);
					}
				}
			}.createDelegate(this),
			icon:Ext.MessageBox.QUESTION,
			msg:langs('Вы хотите удалить запись?'),
			title:langs('Подтверждение')
		});
	},

	deleteFTGGRLS: function() {
		sw.swMsg.show({
			buttons:Ext.Msg.YESNO,
			fn:function (buttonId, text, obj) {
				if (buttonId == 'yes') {
					var grid = this.FTGGRLSGrid.getGrid(),
						record = grid.getSelectionModel().getSelected();
					if( !record ) return false;
					grid.getStore().remove(record);
					if (grid.getStore().getCount() < 1) {
						this.FTGGRLSGrid.ViewActions.action_delete.setDisabled(1);
					} else {
						grid.getSelectionModel().selectRow(0);
					}
				}
			}.createDelegate(this),
			icon:Ext.MessageBox.QUESTION,
			msg:langs('Вы хотите удалить запись?'),
			title:langs('Подтверждение')
		});
	},

	deleteMkb: function() {
		sw.swMsg.show({
			buttons:Ext.Msg.YESNO,
			fn:function (buttonId, text, obj) {
				if (buttonId == 'yes') {
					var grid = this.MkbGrid.getGrid(),
						record = grid.getSelectionModel().getSelected();
					if( !record ) return false;
					grid.getStore().remove(record);
					if (grid.getStore().getCount() < 1) {
						this.MkbGrid.ViewActions.action_delete.setDisabled(1);
					} else {
						grid.getSelectionModel().selectRow(0);
					}
				}
			}.createDelegate(this),
			icon:Ext.MessageBox.QUESTION,
			msg:langs('Вы хотите удалить запись?'),
			title:langs('Подтверждение')
		});
	},
	openLsLinkEditWindow: function(action){
		if (!action.inlist(['add','edit','view'])) {return false;}

		var base_form = this.MainForm.getForm();
		var grid = this.LsLinkGrid.getGrid();

		var params = new Object();
		params.action = action;
		params.formParams = new Object();

		if (action != 'add') {
			var record = grid.getSelectionModel().getSelected();
			if (!record.get('LS_LINK_ID')) {return false;}
			params.formParams.LS_LINK_ID = record.get('LS_LINK_ID');
		}

		params.formParams.PREP_ID = base_form.findField('Prep_id').getValue();

		params.callback = function(){
			this.LsLinkGrid.getAction('action_refresh').execute();
		}.createDelegate(this);

		getWnd('swLsLinkEditWindow').show(params);
	},
	deleteLsLink: function(options) {
		if (!options) {
			options = {};
		}

		var win = this;
		var grid = this.LsLinkGrid.getGrid();
		var record = grid.getSelectionModel().getSelected();
		if (!record.get('LS_LINK_ID')) {return false;}

		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			msg: langs('Удалить взаимодействие ЛС?'),
			title: langs('Удаление записи'),
			fn: function (buttonId) {

				if (buttonId == 'yes') {
					win.doDeleteLsLink({
						LS_LINK_ID: record.get('LS_LINK_ID')
					});
				}
			}
		});
	},
	doDeleteLsLink: function(params) {
		var win = this;
		win.getLoadMask('Удаление взаимодействия').show();
		Ext.Ajax.request({
			url: '/?c=LsLink&m=deleteLsLink',
			params: params,
			callback: function(options, success, response) {
				win.getLoadMask().hide();
				if (success && response && response.responseText) {
					var result = Ext.util.JSON.decode(response.responseText);
					if (result.Error_Msg && result.Error_Msg == 'YesNo') {
						sw.swMsg.show({
							buttons: Ext.Msg.YESNO,
							msg: langs(result.Alert_Msg),
							title: langs('Удаление записи'),
							fn: function(buttonId) {
								if (buttonId == 'yes') {
									params.ignorePrepLs = 1;
									win.doDeleteLsLink(params);
								}
							}
						});
					} else {
						win.LsLinkGrid.getAction('action_refresh').execute();
					}
				}
			}
		});
	},
	linkLsLink: function() {
		var win = this;
		var base_form = this.MainForm.getForm();
		var grid = this.LsLinkGrid.getGrid();
		var record = grid.getSelectionModel().getSelected();
		if (!record.get('LS_LINK_ID')) {return false;}

		win.getLoadMask('Связь препарата со взаимодействием ЛС').show();
		Ext.Ajax.request({
			url: '/?c=LsLink&m=linkLsLink',
			params: {
				LS_LINK_ID: record.get('LS_LINK_ID'),
				PREP_ID: base_form.findField('Prep_id').getValue()
			},
			callback: function() {
				win.getLoadMask().hide();
				win.LsLinkGrid.getAction('action_refresh').execute();
			}
		});
	},
	unlinkLsLink: function() {
		var win = this;
		var base_form = this.MainForm.getForm();
		var grid = this.LsLinkGrid.getGrid();
		var record = grid.getSelectionModel().getSelected();
		if (!record.get('LS_LINK_ID')) {return false;}

		win.getLoadMask('Удаление связи препарата со взаимодействием ЛС').show();
		Ext.Ajax.request({
			url: '/?c=LsLink&m=unlinkLsLink',
			params: {
				LS_LINK_ID: record.get('LS_LINK_ID'),
				PREP_ID: base_form.findField('Prep_id').getValue()
			},
			callback: function() {
				win.getLoadMask().hide();
				win.LsLinkGrid.getAction('action_refresh').execute();
			}
		});
	},
	initComponent: function()
	{
		var cur_win = this;
	
		this.inT = function(){
			var ts = this.trigger.select('.x-form-trigger', true);
			//this.wrap.setStyle('overflow', 'hidden');
			var triggerField = this;
			ts.each(function(t, all, index){
				t.hide = function(){
					var w = triggerField.wrap.getWidth();
					this.dom.style.display = 'none';
					triggerField.el.setWidth(w-triggerField.trigger.getWidth());
				};
				t.show = function(){
					var w = triggerField.wrap.getWidth();
					this.dom.style.display = '';
					triggerField.el.setWidth(w-triggerField.trigger.getWidth());
				};
				var triggerIndex = 'Trigger'+(index+1);
				if(this['hide'+triggerIndex]){
					t.dom.style.display = 'none';
				}
				t.on("click", this['on'+triggerIndex+'Click'], this, {preventDefault:true});
				t.addClassOnOver('x-form-trigger-over');
				t.addClassOnClick('x-form-trigger-click');
			}, this);
			this.triggers = ts.elements;
		};
	
		this.onTrigger1Click = function(){
			if(this.isExpanded()){
				this.collapse();
				return false;
			}
			if(this.getRawValue() != ''){
				if(this.getStore().getCount()>0){
					this.focus(true);
					this.expand();
                    this.restrictHeight();
				}
			} else {
				this.focus(true);
			}
		};
		
		this.dQ = function(q, forceAll) {
			var combo = this;
			//if(q.length<1) return false;
			combo.fireEvent('beforequery', combo);
			combo.getStore().baseParams[combo.displayField] = q;
			combo.getStore().load();
		};
		
		this.steerMsg = '<p style="border: 1px solid #000; color: red; padding: 3px;">'+
			lang['vnimatelno_otnesites_k_zapolneniyu_etogo_polya_poskolku_posle_sohraneniya_v_rejime_redaktirovaniya_ono_budet_nedostuno'];

		this.ExtemporalField = new sw.Promed.SwBaseLocalCombo({
			anchor: '100%',
			store: new Ext.data.Store({
				autoLoad: true,
				listeners: {
					load: function(s) {
						var c = this.MainForm.getForm().findField('Extemporal_id');
						if(s.getCount() == 0) {
							c.reset();
							return false;
						}
					}.createDelegate(this)
				},
				baseParams: {
					lowercase: 1,
					object: 'Extemporal',
					stringfield: 'Extemporal_Name'
				},
				reader: new Ext.data.JsonReader({
					id: 'Extemporal_id'
				}, [{
					mapping: 'Extemporal_id',
					name: 'Extemporal_id',
					type: 'int'
				},{
					mapping: 'Extemporal_Name',
					name: 'Extemporal_Name',
					type: 'string'
				}]),
				url: '/?c=Rls&m=getDataForComboStore'
			}),
			initTrigger: this.inT,
			triggerConfig:	{
				tag:'span', cls:'x-form-twin-triggers', cn:[
				{tag: "img", src: Ext.BLANK_IMAGE_URL, cls: "x-form-trigger x-form-select-trigger"},
				{tag: "img", src: Ext.BLANK_IMAGE_URL, cls: "x-form-trigger x-form-search-trigger"}
			]},
			onTrigger1Click: function(){
				if(this.isExpanded()){
					this.collapse();
					return false;
				}
				if(true/*this.getRawValue() != ''*/){
					if(this.getStore().getCount()>0){
						this.focus(true);
						this.expand();
	                    this.restrictHeight();
					}
				} else {
					this.focus(true);
				}
			},
			onTrigger2Click: function(){
				var c = this;
				if(!this.formList){
					this.getStore().baseParams.stringfield = this.displayField;
					this.formList = new sw.Promed.swListSearchWindow({
						title: 'Поиск рецептуры',
						id: 'Extemporal_SearchWindow',
						object: 'rls.Extemporal',
						useBaseParams: true,
						store: this.getStore()
					});
				}
				this.formList.show({
					onSelect: function(data){
						//c.getStore().removeAll();
						c.focus(true);
						c.getStore().baseParams.Extemporal_id = data[c.hiddenName];
						c.getStore().load({
							callback: function() {
								c.setValue(data[c.hiddenName]);
								c.fireEvent('select', c, c.getStore().getAt(0), 0);
								c.collapse();
								c.focus(true, 100);
							}
						});
					}
				});
			},
			listeners: {
				select: function(c, r, i) {
					if(c.oldValue == c.getValue())
						return false;
					c.oldValue = c.getValue();
					var form = this.MainForm.getForm();
					var val = c.getValue();
					Ext.Ajax.request({
						url: '/?c=Extemporal&m=loadExtemporal',
						params: {Extemporal_id:val},
						callback: function(options, success, response) {
							if ( success ) {
								var result = Ext.util.JSON.decode(response.responseText);
								if(result[0]){
									form.setValues(result[0]);
									if(result[0].CLSDRUGFORMS_ID){
										var cls = form.findField('CLSDRUGFORMS_ID');
										cls.getStore().baseParams.CLSDRUGFORMS_ID = result[0].CLSDRUGFORMS_ID;
										cls.getStore().load({
											callback: function() {
												cls.setValue(result[0].CLSDRUGFORMS_ID);
												cls.fireEvent('select', cls, cls.getStore().getAt(0), 0);
												cls.collapse();
											}
										});
									}
									if(result[0].CLSDRUGFORMS_NAME && result[0].Composition){
										form.findField('DESCTEXTES_COMPOSITION').setValue(result[0].Composition/*+', '+result[0].CLSDRUGFORMS_NAME*/);
									}
								}
							}
						}
					}); 
					
				}.createDelegate(this),
				change: function(combo,newV){
					var form = this.MainForm.getForm();
					if(newV > 0){
						form.findField('Actmatters_Names').trigger.setOpacity(0.5);
						this.Actmatters_flag = true;
					} else {
						form.findField('Actmatters_Names').trigger.setOpacity(1);
						this.Actmatters_flag = false;
					}
				}.createDelegate(this)
			},
			oldValue: '',
            lastQuery: '',
			emptyText: 'Введите название рецептуры',
			doQuery: function(q, forceAll) {
				var combo = this;
				//if(q.length<1) return false;
				combo.fireEvent('beforequery', combo);
				var where = combo.displayField+' like %\''+q+'%\'';
				combo.getStore().baseParams[combo.valueField] = null;
				combo.getStore().baseParams.where = where;
				combo.getStore().baseParams.stringfield = combo.displayField;
				combo.getStore().load();
			},
			hiddenName: 'Extemporal_id',
			triggerAction: 'none',
			displayField: 'Extemporal_Name',
			valueField: 'Extemporal_id',
			minChars: 3,
			fieldLabel: 'Рецептура'
		});

		this.CLSDRUGFORMSField = new sw.Promed.SwBaseLocalCombo({
			anchor: '100%',
			editable:true,
			store: new Ext.data.Store({
				autoLoad: false,
				listeners: {
					load: function(s) {
						var c = this.MainForm.getForm().findField('CLSDRUGFORMS_ID');
						if(s.getCount() == 0) {
							c.reset();
							return false;
						}
					}.createDelegate(this)
				},
				baseParams: {
					object: 'CLSDRUGFORMS',
					stringfield: 'FULLNAME',
					additionalFields: 'CLSDRUGFORMS_NameLatin,CLSDRUGFORMS_NameLatinSocr'
				},
				reader: new Ext.data.JsonReader({
					id: 'CLSDRUGFORMS_ID'
				}, [{
					mapping: 'CLSDRUGFORMS_ID',
					name: 'CLSDRUGFORMS_ID',
					type: 'int'
				},{
					mapping: 'FULLNAME',
					name: 'FULLNAME',
					type: 'string'
				},{
					mapping: 'CLSDRUGFORMS_NameLatin',
					name: 'CLSDRUGFORMS_NameLatin',
					type: 'string'
				},{
					mapping: 'CLSDRUGFORMS_NameLatinSocr',
					name: 'CLSDRUGFORMS_NameLatinSocr',
					type: 'string'
				}]),
				url: '/?c=Rls&m=getDataForComboStoreWithFields'
			}),
			initTrigger: this.inT,
			triggerConfig:	{
				tag:'span', cls:'x-form-twin-triggers', cn:[
				{tag: "img", src: Ext.BLANK_IMAGE_URL, cls: "x-form-trigger x-form-select-trigger"},
				{tag: "img", src: Ext.BLANK_IMAGE_URL, cls: "x-form-trigger x-form-search-trigger"}
			]},
			onTrigger1Click: this.onTrigger1Click,
			onTrigger2Click: function(){
				var c = this;
				if(!this.formList){
					this.getStore().baseParams.stringfield = this.displayField;
					this.formList = new sw.Promed.swListSearchWindow({
						title: lang['poisk_lekarstvennoy_formyi'],
						id: 'CLSDRUGFORMS_SearchWindow',
						object: 'rls.CLSDRUGFORMS',
						//useBaseParams: true,
						//store: this.getStore(),
						stringfields: [{
							name: 'CLSDRUGFORMS_ID',
							type: 'int'
						},{
							name: 'FULLNAME',
							type: 'string',
							header: 'Наименование'
						},{
							name: 'CLSDRUGFORMS_NameLatin',
							header: 'На латинском полн.',
							type: 'string'
						},{
							name: 'CLSDRUGFORMS_NameLatinSocr',
							header: 'На латинском сокр.',
							type: 'string'
						}],
						dataUrl:'/?c=Rls&m=getDataForComboStoreWithFields' 
					});
				}
				this.formList.show({
					onSelect: function(data){
						//c.getStore().removeAll();
						c.focus(true);
						c.getStore().baseParams.CLSDRUGFORMS_ID = data[c.hiddenName];
						c.getStore().load({
							callback: function() {
								c.setValue(data[c.hiddenName]);
								c.fireEvent('select', c, c.getStore().getAt(0), 0);
								c.collapse();
								c.focus(true, 100);
							}
						});
					},
					params: {
						object: 'CLSDRUGFORMS',
						stringfield: 'FULLNAME',
						additionalFields: 'CLSDRUGFORMS_NameLatin,CLSDRUGFORMS_NameLatinSocr'
					}
				});
			},
			listeners: {
				select: function(c, r, i) {
					/*if(c.oldValue == c.getValue())
						return false;*/
					c.oldValue = c.getValue();
					var form = this.MainForm.getForm();
					if(c.getStore().getById(c.getValue()) && c.getStore().getById(c.getValue()).get('CLSDRUGFORMS_NameLatin')){
						form.findField('CLSDRUGFORMS_NameLatin').setValue(c.getStore().getById(c.getValue()).get('CLSDRUGFORMS_NameLatin'));
					}
					if(c.getStore().getById(c.getValue()) && c.getStore().getById(c.getValue()).get('CLSDRUGFORMS_NameLatinSocr')){
						form.findField('CLSDRUGFORMS_NameLatinSocr').setValue(c.getStore().getById(c.getValue()).get('CLSDRUGFORMS_NameLatinSocr'));
					}
					/*form.findField('vidLF').reset();
					form.findField('DFCONC').reset();
					form.findField('DFCONCID').reset();									
					form.findField('DFMASS').reset();
					form.findField('DFMASSID').reset();
					form.findField('DFACT').reset();
					form.findField('DFACTID').reset();
					form.findField('DRUGDOSE').reset();
					form.findField('DRUGLIFETIME_TEXT').reset();
					form.findField('DFSIZE').reset();
					form.findField('DFSIZEID').reset();
					form.findField('DFCHARID').reset();
					form.findField('NOMEN_DRUGSINPPACK').reset();
					form.findField('NOMEN_PPACKID').reset();
					form.findField('NOMEN_PPACKVOLUME').reset();
					form.findField('NOMEN_PPACKCUBUNID').reset();
					form.findField('NOMEN_SETID').reset();
					form.findField('NOMEN_PPACKINUPACK').reset();
					form.findField('NOMEN_UPACKID').reset();
					form.findField('NOMEN_UPACKINSPACK').reset();
					form.findField('NOMEN_SPACKID').reset();
					form.findField('NOMEN_PPACKMASS').reset();
					form.findField('NOMEN_PPACKMASSUNID').reset();
					form.findField('measure').reset();*/
				}.createDelegate(this),
				change: this.setPrepFullName.createDelegate(this),
				render:function(){
					this.doQuery = function(q, forceAll) {
						var combo = this;
						if(q.length<3) return false;
						combo.fireEvent('beforequery', combo);
						var where = combo.displayField+' like \''+q+'%\'';
						combo.getStore().baseParams[combo.valueField] = null;
						combo.getStore().baseParams.where = where;
						combo.getStore().baseParams.stringfield = combo.displayField;
						combo.getStore().load();log(combo.getStore());
					};
				}
			},
			oldValue: '',
            lastQuery: '',
			emptyText: lang['vvedite_nazvanie_lekarstvennoy_formyi'],
			doQuery: this.dQ,
			hiddenName: 'CLSDRUGFORMS_ID',
			allowBlank: false,
            id: 'MP_CLSDRUGFORMS_ID',
			triggerAction: 'all',
			displayField: 'FULLNAME',
			valueField: 'CLSDRUGFORMS_ID',
			additionalFields: 'CLSDRUGFORMS_NameLatin,CLSDRUGFORMS_NameLatinSocr',
			fieldLabel: 'Наименование'
		});

		this.MainForm = new Ext.form.FormPanel({
            autoHeight: true,
            region: 'center',
			bodyStyle: 'padding: 3px 0px; border-top: 0px;',
			defaults: {
				bodyStyle: 'padding: 3px;',
				labelAlign: 'right',
				border: false,
				collapsible: true
			},
			url: '/?c=Rls&m=savePrep',
			items: [{
				layout: 'form',
				width: 800,
				labelWidth: 200,
				items: [
				{
					xtype: 'hidden',
					name: 'Prep_id'
				}, {
					xtype: 'hidden',
					name: 'PrepType_id'
				}, {
					xtype: 'hidden',
					name: 'TRADENAMES_ID'
				}, {
					xtype: 'hidden',
					name: 'LATINNAMES_ID'
				}, {
					xtype: 'hidden',
					name: 'REGCERT_ID'
				}, {
					xtype: 'hidden',
					name: 'DRUGLIFETIME_ID'
				}, {
					xtype: 'hidden',
					name: 'DRUGSTORCOND_ID'
				}, {
					xtype: 'hidden',
					name: 'NOMEN_ID'
				}, {
					xtype: 'hidden',
					name: 'DESCRIPTIONS_ID'
				}, {
					xtype: 'hidden',
					name: 'IDENT_WIND_STR_id'
				}, {
					xtype: 'swntfrcombo',
					anchor: '100%',
					allowBlank: false,
					disabled: true,
					minChars: 3,
					name: 'RlsClsntfr_id',
                    setValueById: function (id) {
                        var combo = this;
                        combo.getStore().removeAll();
                        if (id > 0) {
                            combo.getStore().load({
                                params: {
                                    where: 'where RlsClsntfr_id = '+id
                                },
                                callback: function(){
                                    combo.setValue(id);
									cur_win.onRlsClsntfrChange();
                                }
                            });
                        }
                    },
					listeners: {
						change: this.onRlsClsntfrChange.createDelegate(this)
					}
				}, {
					xtype: 'textarea',
					anchor: '100%',
					//grow: true,
					readOnly: true,
					fieldLabel: lang['otobrajaemoe_nazvanie'],
					name: 'PREPFULLNAME'
				}, 
				this.ExtemporalField,
				{
					xtype: 'textarea',
					anchor: '100%',
					readOnly: true,
					grow: true,
					fieldLabel: 'Состав рецептуры',
					name: 'Composition'
				},
				{
					autoHeight: true,
					title: 'Действующее вещество / Международное непатентованное наименование (МНН)',
					xtype: 'fieldset',
					id: 'ActmattersFieldset',
					columnWidth: 1,
					labelWidth: 188,
					defaults: {
						bodyStyle: 'padding: 3px 0px;',
						labelAlign: 'right',
						border: false
					},
					items:
					[{
						xtype: 'hidden',
						name: 'ACTMATTERS'
					},
					new Ext.form.TriggerField({
						allowBlank: true,
						anchor: '100%',
						autoCreate: {tag: "input", autocomplete: "off"},
						readOnly : false,
						forceSelection: true,
						name: 'Actmatters_Names',
						fieldLabel: 'Наим. на русском языке',
						typeAhead: false,
						triggerClass: 'x-form-search-trigger',
						onTriggerClick: function() {
							if(!this.Actmatters_flag){
								this.addActmatterInGrid();
							}
						}.createDelegate(this)
					}),
					{
						layout: 'form',
						labelWidth: 170,
						fieldLabel: '',
						border: false,
						bodyStyle: 'padding: 3px 0px;',
						items: [{
							layout: 'column',
							bodyStyle: 'padding: 0px;',
							width: 772,
							border: false,
							defaults: {
								border: false,
								bodyStyle: 'padding: 0px;'
							},
							items: [
								{
									layout: 'form',
									width: 180,
									labelWidth: 170,
									items: [
										{
											xtype: 'label',
											anchor: '100%',
											style: 'text-align:right;display:block;width:100%;padding-top:3px;font-size:12px;',
											text: 'Наим. на латинском языке'
										}
									]
								},{
									layout: 'form',
									width: 296,
									labelWidth: 50,
									items: [
										{
											xtype: 'textfield',
											anchor: '100%',
											name: 'Actmatters_LatName',
											fieldLabel: 'Им.п.'
										}
									]
								},{
									layout: 'form',
									width: 296,
									labelWidth: 50,
									items: [
										{
											xtype: 'textfield',
											anchor: '100%',
											name: 'Actmatters_LatNameGen',
											fieldLabel: 'Род.п.'
										}
									]
								}
							]
						}]
					}]
				},
				{
					xtype: 'swdrugnonpropnamescombo',
					anchor: '100%',
					allowBlank: false,
					emptyText: 'Выберите значение из списка...',
					listeners: {
						'blur': function (combo, newValue, oldValue) {
							// О смысле данного кода. В поле корректно выбирать значение из числа подгруженных в стор.
							// Но пользователь мог скопировать и вставить в поле значение, отсутствующее в сторе, валидация при этом не срабатывала.
							// Для программы комбобокс оставался пустым.
							if (combo.getStore().getCount() < 1)  {
								combo.setValue('');
							}
						}
					}
				},
				{
					autoHeight: true,
					title: 'Торговое наименование (трейдмарк)',
					xtype: 'fieldset',
					columnWidth: 1,
					labelWidth: 188,
					defaults: {
						bodyStyle: 'padding: 3px 0px;',
						labelAlign: 'right',
						border: false
					},
					items:
					[
					{
						xtype: 'textfield',
						anchor: '100%',
						allowBlank: false,
						listeners: {
							change: this.setPrepFullName.createDelegate(this)
						},
						fieldLabel: 'Наим. на русском языке',
						name: 'TRADENAMES_NAME'
					},
					{
						layout: 'form',
						id: 'Tradename_LatNamesSet',
						labelWidth: 200,
						fieldLabel: '',
						bodyStyle: 'padding: 3px 0px;',
						border: false,
						items: [{
							layout: 'column',
							width: 772,
							border: false,
							defaults: {
								border: false,
								bodyStyle: 'padding: 0px;'
							},
							items: [
								{
									layout: 'form',
									width: 180,
									labelWidth: 170,
									items: [
										{
											xtype: 'label',
											anchor: '100%',
											style: 'text-align:right;display:block;width:100%;padding-top:3px;font-size:12px;',
											text: 'Наим. на латинском языке'
										}
									]
								},{
									layout: 'form',
									width: 296,
									labelWidth: 50,
									items: [
										{
											xtype: 'hidden',
											name: 'LATINNAMES_ID'
										},
										{
											xtype: 'textfield',
											anchor: '100%',
											name: 'LATINNAMES_NAME',
											listeners: {
												change: this.setPrepFullName.createDelegate(this)
											},
											fieldLabel: 'Им.п.'
										}
									]
								},{
									layout: 'form',
									width: 296,
									labelWidth: 50,
									items: [
										{
											xtype: 'textfield',
											anchor: '100%',
											name: 'LATINNAMES_NameGen',
											fieldLabel: 'Род.п.'
										}
									]
								}
							]
						}]
					}]
				},
				{
					autoHeight: true,
					title: 'Лекарственная форма / форма выпуска',
					xtype: 'fieldset',
					columnWidth: 1,
					labelWidth: 188,
					defaults: {
						bodyStyle: 'padding: 3px 0px;',
						labelAlign: 'right',
						border: false
					},
					items:
					[
					this.CLSDRUGFORMSField, 
					{
						layout: 'form',
						labelWidth: 200,
						fieldLabel: '',
						border: false,
						items: [{
							layout: 'column',
							width: 772,
							border: false,
							defaults: {
								border: false
							},
							items: [
								{
									layout: 'form',
									width: 180,
									labelWidth: 170,
									items: [
										{
											xtype: 'label',
											anchor: '100%',
											style: 'text-align:right;display:block;width:100%;padding-top:3px;font-size:12px;',
											text: 'На латинском'
										}
									]
								},{
									layout: 'form',
									width: 296,
									labelWidth: 50,
									items: [
										{
											xtype: 'textfield',
											anchor: '100%',
											name: 'CLSDRUGFORMS_NameLatin',
											fieldLabel: 'полн.'
										}
									]
								},{
									layout: 'form',
									width: 296,
									labelWidth: 50,
									items: [
										{
											xtype: 'textfield',
											anchor: '100%',
											name: 'CLSDRUGFORMS_NameLatinSocr',
											fieldLabel: 'сокр.'
										}
									]
								}
							]
						}]
					}]
				}, {
					autoHeight: true,
					title: 'Дозировка/Размер',
					xtype: 'fieldset',
					id: 'DoseSizeFieldset',
					columnWidth: 1,
					labelWidth: 188,
					defaults: {
						bodyStyle: 'padding: 3px 0px;',
						labelAlign: 'right',
						border: false
					},
					items:
					[{
						xtype: 'swbaselocalcombo',
						allowBlank: false,
						editable: false,
						triggerAction: 'all',
						mode: 'local',
						listeners: {
							render: function(c){
								c.setValue(1);
							},
							select: function(c, r, i){
								switch(c.getValue()){
									case 1:
										this.find('name', 'MASSUNITS')[0].setVisible(true);
										this.find('name', 'CONCENUNITS')[0].setVisible(false);
										this.find('name', 'ACTUNITS')[0].setVisible(false);
										this.find('name', 'SIZEUNITS')[0].setVisible(false);
									break;
									case 2:
										this.find('name', 'MASSUNITS')[0].setVisible(false);
										this.find('name', 'CONCENUNITS')[0].setVisible(true);
										this.find('name', 'ACTUNITS')[0].setVisible(false);
										this.find('name', 'SIZEUNITS')[0].setVisible(false);
									break;
									case 3:
										this.find('name', 'MASSUNITS')[0].setVisible(false);
										this.find('name', 'CONCENUNITS')[0].setVisible(false);
										this.find('name', 'ACTUNITS')[0].setVisible(true);
										this.find('name', 'SIZEUNITS')[0].setVisible(false);
									break;
									case 4:
										this.find('name', 'MASSUNITS')[0].setVisible(false);
										this.find('name', 'CONCENUNITS')[0].setVisible(false);
										this.find('name', 'ACTUNITS')[0].setVisible(false);
										this.find('name', 'SIZEUNITS')[0].setVisible(true);
									break;
								}
								this.syncSize();
								this.doLayout();
							}.createDelegate(this)
						},
						store: new Ext.data.Store({
							autoLoad: true,
							data: [ 
								[1, lang['massa_lekarstvennoy_formyi']],
								[2, lang['obyemnaya_chast_kontsentratsiya_lekarstvennoy_formyi']],
								[3, lang['kolichestvo_edinits_deystviya_lekarstvennoy_formyi']],
								[4, 'Сложный состав или размеры'] 
							],
							reader: new Ext.data.ArrayReader({
								idIndex: 0
							}, [
								{mapping: 0, name: 'id'},
								{mapping: 1, name: 'name'}
							])
						}),
						hiddenName: 'vidLF',
						displayField: 'name',
						valueField: 'id',
						anchor: '100%',
						fieldLabel: 'Вид ед.измерения'
					}, {
						layout: 'column',
						width: 772,
						name: 'MASSUNITS',
						defaults: {
							border: false
						},
						items: [
							{
								layout: 'form',
								labelWidth: 188,
								columnWidth: .55,
								items: [
									{
										xtype: 'swbaselocalcombo',
										anchor: '100%',
										store: new Ext.data.Store({
											autoLoad: true,
											baseParams: {
												object: 'MASSUNITS',
												stringfield: 'SHORTNAME',
												additionalFields: 'MassUnits_NameLatin',
												where: '1=1'
											},
											reader: new Ext.data.JsonReader({
												id: 'MASSUNITS_ID'
											}, [{
												mapping: 'MASSUNITS_ID',
												name: 'MASSUNITS_ID',
												type: 'int'
											},{
												mapping: 'SHORTNAME',
												name: 'SHORTNAME',
												type: 'string'
											},{
												mapping: 'MassUnits_NameLatin',
												name: 'MassUnits_NameLatin',
												type: 'string'
											}]),
											url: '/?c=Rls&m=getDataForComboStoreWithFields'
										}),
										listeners: {
											change: function(c){
												var form = this.MainForm.getForm();
												if(c.getStore().getById(c.getValue())){
													form.findField('MassUnits_NameLatin').setValue(c.getStore().getById(c.getValue()).get('MassUnits_NameLatin'));
												}
												this.setPrepFullName.createDelegate(this)
											}.createDelegate(this),
											render: function(c) {
											    Ext.QuickTips.register({
											        target: c.getEl(),
											        text: 'Единицы измерения количества действующего вещества / размера медизделия',
											        enabled: true,
											        showDelay: 5,
											        trackMouse: true,
											        autoShow: true
											    });
										    }
										},
										displayField: 'SHORTNAME',
										valueField: 'MASSUNITS_ID',
										hiddenName: 'DFMASSID',
										additionalFields: 'MassUnits_NameLatin',
										fieldLabel: lang['ed_izmereniya']
									}
								]
							}, {
								layout: 'form',
								columnWidth: .45,
								labelWidth: 100,
								items: [
									{
										xtype: 'textfield',
										anchor: '100%',
										decimalPrecision: 0,
										name: 'MassUnits_NameLatin',
										fieldLabel: 'на лат.'
									}
								]
							}, {
								layout: 'form',
								columnWidth: 1,
								labelWidth: 188,
								items: [
									{
										xtype: 'numberfield',
										anchor: '100%',
										allowDecimals: true,
										name: 'DFMASS',
										listeners: {
											change: this.setPrepFullName.createDelegate(this)
										},
										fieldLabel: lang['kolichestvo_ls']
									}
								]
							}
						]
					}, {
						layout: 'column',
						width: 772,
						name: 'CONCENUNITS',
						hidden: true,
						defaults: {
							border: false
						},
						items: [
							{
								layout: 'form',
								labelWidth: 188,
								columnWidth: .55,
								items: [
									{
										xtype: 'swbaselocalcombo',
										anchor: '100%',
										store: new Ext.data.Store({
											autoLoad: true,
											baseParams: {
												object: 'CONCENUNITS',
												stringfield: 'SHORTNAME',
												where: '1=1'
											},
											reader: new Ext.data.JsonReader({
												id: 'CONCENUNITS_ID'
											}, [{
												mapping: 'CONCENUNITS_ID',
												name: 'CONCENUNITS_ID',
												type: 'int'
											},{
												mapping: 'SHORTNAME',
												name: 'SHORTNAME',
												type: 'string'
											}]),
											url: '/?c=Rls&m=getDataForComboStore'
										}),
										listeners: {
											change: this.setPrepFullName.createDelegate(this),
											render: function(c) {
											    Ext.QuickTips.register({
											        target: c.getEl(),
											        text: 'Единицы измерения количества действующего вещества / размера медизделия',
											        enabled: true,
											        showDelay: 5,
											        trackMouse: true,
											        autoShow: true
											    });
										    }
										},
										displayField: 'SHORTNAME',
										valueField: 'CONCENUNITS_ID',
										hiddenName: 'DFCONCID',
										fieldLabel: lang['ed_izmereniya']
									}
								]
							}, {
								layout: 'form',
								columnWidth: .45,
								labelWidth: 100,
								items: [
									{
										xtype: 'textfield',
										anchor: '100%',
										decimalPrecision: 0,
										name: 'CONCENUNITS_LAT',
										fieldLabel: 'на лат.'
									}
								]
							}, {
								layout: 'form',
								columnWidth: 1,
								labelWidth: 188,
								items: [
									{
										xtype: 'numberfield',
										anchor: '100%',
										listeners: {
											change: this.setPrepFullName.createDelegate(this)
										},
										allowDecimals: true,
										name: 'DFCONC',
										fieldLabel: lang['kolichestvo_ls']
									}
								]
							}
						]
					}, {
						layout: 'column',
						width: 772,
						name: 'ACTUNITS',
						hidden: true,
						defaults: {
							border: false
						},
						items: [
							{
								layout: 'form',
								columnWidth: .55,
								labelWidth: 188,
								items: [
									{
										xtype: 'swbaselocalcombo',
										anchor: '100%',
										store: new Ext.data.Store({
											autoLoad: true,
											baseParams: {
												object: 'ACTUNITS',
												stringfield: 'SHORTNAME',
												where: '1=1'
											},
											reader: new Ext.data.JsonReader({
												id: 'ACTUNITS_ID'
											}, [{
												mapping: 'ACTUNITS_ID',
												name: 'ACTUNITS_ID',
												type: 'int'
											},{
												mapping: 'SHORTNAME',
												name: 'SHORTNAME',
												type: 'string'
											}]),
											url: '/?c=Rls&m=getDataForComboStore'
										}),
										listeners: {
											change: this.setPrepFullName.createDelegate(this),
											render: function(c) {
											    Ext.QuickTips.register({
											        target: c.getEl(),
											        text: 'Единицы измерения количества действующего вещества / размера медизделия',
											        enabled: true,
											        showDelay: 5,
											        trackMouse: true,
											        autoShow: true
											    });
										    }
										},
										displayField: 'SHORTNAME',
										valueField: 'ACTUNITS_ID',
										hiddenName: 'DFACTID',
										fieldLabel: lang['ed_izmereniya']
									}
								]
							}, {
								layout: 'form',
								columnWidth: .45,
								labelWidth: 100,
								items: [
									{
										xtype: 'textfield',
										anchor: '100%',
										decimalPrecision: 0,
										name: 'ACTUNITS_LAT',
										fieldLabel: 'на лат.'
									}
								]
							}, {
								layout: 'form',
								columnWidth: 1,
								labelWidth: 188,
								items: [
									{
										xtype: 'numberfield',
										anchor: '100%',
										allowDecimals: true,
										listeners: {
											change: this.setPrepFullName.createDelegate(this)
										},
										name: 'DFACT',
										fieldLabel: lang['kolichestvo_ls']
									}
								]
							}
						]
					}, {
						layout: 'column',
						width: 772,
						name: 'SIZEUNITS',
						hidden: true,
						defaults: {
							border: false
						},
						items: [
							{
								layout: 'form',
								labelWidth: 188,
								columnWidth: .55,
								items: [
									{
										xtype: 'swbaselocalcombo',
										anchor: '100%',
										store: new Ext.data.Store({
											autoLoad: true,
											baseParams: {
												object: 'SIZEUNITS',
												stringfield: 'SHORTNAME',
												where: '1=1',
												additionalFields: 'SHORTNAMELATIN'
											},
											reader: new Ext.data.JsonReader({
												id: 'SIZEUNITS_ID'
											}, [{
												mapping: 'SIZEUNITS_ID',
												name: 'SIZEUNITS_ID',
												type: 'int'
											},{
												mapping: 'SHORTNAME',
												name: 'SHORTNAME',
												type: 'string'
											},{
												mapping: 'SHORTNAMELATIN',
												name: 'SHORTNAMELATIN',
												type: 'string'
											}]),
											url: '/?c=Rls&m=getDataForComboStoreWithFields'
										}),
										listeners: {
											change: function(field, newValue, oldValue) {
                                                var form = this.MainForm.getForm();
												var lat_name = '';
                                                var idx = field.getStore().findBy(function(rec) {
                                                	return rec.get('SIZEUNITS_ID') == newValue;
                                                });
                                                if (idx > -1) {
                                                    lat_name = field.getStore().getAt(idx).get('SHORTNAMELATIN');
                                                }
                                                form.findField('SIZEUNITS_LAT').setValue(lat_name);
												this.setPrepFullName();
											}.createDelegate(this),
											render: function(c) {
											    Ext.QuickTips.register({
											        target: c.getEl(),
											        text: 'Единицы измерения количества действующего вещества / размера медизделия',
											        enabled: true,
											        showDelay: 5,
											        trackMouse: true,
											        autoShow: true
											    });
										    }
										},
										displayField: 'SHORTNAME',
										valueField: 'SIZEUNITS_ID',
										hiddenName: 'DFSIZEID',
										fieldLabel: lang['ed_izmereniya']
									}
								]
							}, {
								layout: 'form',
								columnWidth: .45,
								labelWidth: 100,
								items: [
									{
										xtype: 'textfield',
										anchor: '100%',
										decimalPrecision: 0,
										name: 'SIZEUNITS_LAT',
										fieldLabel: 'на лат.'
									}
								]
							}, {
								layout: 'form',
								columnWidth: .55,
								labelWidth: 188,
								items: [
									{
										xtype: 'textfield',
										anchor: '100%',
										listeners: {
											change: this.setPrepFullName.createDelegate(this)
										},
										name: 'DFSIZE',
										fieldLabel: 'Кол-во ед.измерения'
									}
								]
							}, {
								layout: 'form',
								columnWidth: .45,
								labelWidth: 100,
								items: [
									{
										xtype: 'textfield',
										anchor: '100%',
										listeners: {
											change: this.setPrepFullName.createDelegate(this)
										},
										decimalPrecision: 0,
										name: 'DFSIZE_LAT',
										fieldLabel: 'на лат.'
									}
								]
							}
						]
					}, {
						layout: 'column',
						width: 700,
						defaults: {
							border: false
						},
						items: [{
							layout: 'form',
							columnWidth: .5,
							labelWidth: 188,
							items: [{
								xtype: 'numberfield',
								anchor: '100%',
								name: 'DRUGDOSE',
								listeners: {
									change: this.setPrepFullName.createDelegate(this),
									render: function(c) {
									    Ext.QuickTips.register({
									        target: c.getEl(),
									        text: 'Количество доз указывается в соответствии с данными регистрационного удостоверения и инструкцией по применению',
									        enabled: true,
									        showDelay: 5,
									        trackMouse: true,
									        autoShow: true
									    });
								    }
								},
								decimalPrecision: 0,
								fieldLabel: lang['kol-vo_doz_v_upakovke']
							}]
						}]
					}]
				}, {
					xtype: 'textarea',
					anchor: '100%',
					allowBlank: false,
					name: 'DESCTEXTES_COMPOSITION',
					fieldLabel: lang['sostav_i_forma_vyipuska']
				}, {
					xtype: 'combo',
					mode: 'local',
					enableKeyEvents: true,
					emptyText: lang['vvedite_nazvanie_harakteristiki'],
					anchor: '100%',
					store: new Ext.data.Store({
						autoLoad: false,
						listeners: {
							load: function(s) {
								var c = this.MainForm.getForm().findField('DFCHARID');
								if(s.getCount() == 0) {
									c.reset();
									return false;
								}
							}.createDelegate(this)
						},
						baseParams: {
							object: 'DRUGFORMCHARS'
						},
						reader: new Ext.data.JsonReader({
							id: 'DRUGFORMCHARS_ID'
						}, [{
							mapping: 'DRUGFORMCHARS_ID',
							name: 'DRUGFORMCHARS_ID',
							type: 'int'
						},{
							mapping: 'SHORTNAME',
							name: 'SHORTNAME',
							type: 'string'
						}]),
						url: '/?c=Rls&m=getDataForComboStore'
					}),
					doQuery: function(q, forceAll) {
						var combo = this;
						if(q.length<2) return false;
						combo.fireEvent('beforequery', combo);
						var where = combo.displayField+' like \''+q+'%\'';
						combo.getStore().baseParams[combo.valueField] = null;
						combo.getStore().baseParams.where = where;
						combo.getStore().baseParams.stringfield = combo.displayField;
						combo.getStore().load();
					},
					listeners: {
						change: function(combo,newVal){
							var combo = this.MainForm.getForm().findField('DFCHARID');
							var newVal = combo.getValue();
							var index = combo.getStore().findBy(function(rec){
								return (rec.get('SHORTNAME') == newVal || rec.get('DRUGFORMCHARS_ID') == newVal);
							});
							if(index < 0){
								combo.setValue('');
							} else {
								combo.setValue(combo.getStore().getAt(index).get('DRUGFORMCHARS_ID'));
								this.setPrepFullName();
							}
						}.createDelegate(this)
					},
					hiddenName: 'DFCHARID',
					editable: true,
					triggerAction: 'none',
					displayField: 'SHORTNAME',
					valueField: 'DRUGFORMCHARS_ID',
					fieldLabel: lang['harakteristika']
				}, {
					layout: 'form',
					bodyStyle: 'padding: 3px 0px;',
					border: false,
					defaults: {
						labelAlign: 'right',
						border: false
					},
					items:[{
						autoHeight: true,
						title: 'Данные о регистрации, производителе',
						xtype: 'fieldset',
						columnWidth: 1,
						style: 'padding: 10px;',
						border: true,
						labelWidth: 188,
						defaults: {
							bodyStyle: 'padding: 3px 0px;',
							labelAlign: 'right',
							border: false
						},
						items:
						[{
							layout: 'column',
							defaults: {
								border: false
							},
							items: [
								{
									layout: 'form',
									labelWidth: 188,
									columnWidth: .55,
									items: [
										{
											xtype: 'textfield',
											anchor: '100%',
											listeners: {
												change: this.setPrepFullName.createDelegate(this)
											},
											name: 'REGCERT_REGNUM',
											allowBlank: false,
											fieldLabel: 'Рег.удостоверение №'
										}
									]
								}
							]
						}, {
							layout: 'column',
							defaults: {
								border: false
							},
							items: [
								{
									layout: 'form',
									columnWidth: .55,
									labelWidth: 188,
									items: [
										{
											format: 'd.m.Y',
											anchor: '100%',
											xtype: 'daterangefield',
											plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false) ],
											name: 'REGCERT_REGDATERange',
											fieldLabel: 'Период действия РУ',
											listeners: {
												'blur': function(c){
													this.setPrepFullName();
													var endVal = c.getValue2();
													if(!Ext.isEmpty(endVal)){
														this.MainForm.getForm().findField('Reregdate').showContainer();
													} else {
														this.MainForm.getForm().findField('Reregdate').hideContainer();
													}
												}.createDelegate(this)
											}
										}
									]
								}, {
									layout: 'form',
									labelWidth: 200,
									columnWidth: .45,
									items: [
										{
											xtype: 'swdatefield',
											name: 'Reregdate',
											fieldLabel: 'Дата перерегистрации',
											listeners: {
												'blur': function(c){
													this.setPrepFullName();
												}.createDelegate(this)
											}
										}
									]
								}
							]
						}, {
							layout: 'column',
							defaults: {
								border: false
							},
							items: [
								{
									layout: 'form',
									columnWidth: .55,
									labelWidth: 188,
									items: [
										{
											xtype: 'label',
											html: '&nbsp;',
											anchor: '100%'
										}
									]
								}, {
									layout: 'form',
									labelWidth: 200,
									columnWidth: .45,
									items: [
										{
											xtype: 'swdatefield',
											name: 'REGCERT_excDT',
											fieldLabel: 'Дата исключения'
										}
									]
								}
							]
						}, {
							layout: 'column',
							defaults: {
								border: false
							},
							items: [
								{
									layout: 'form',
									columnWidth: .55,
									labelWidth: 188,
									items: [
										{
											fieldLabel: 'Страна регистрации',
											allowBlank: false,
											hiddenName: 'KLCountry_id',
											xtype: 'swklcountrycombo'
										}
									]
								}
							]
						}, {
							xtype: 'swfirmscombo',
							fieldLabel: 'Владелец РУ',
							listeners: {
								change: this.setPrepFullName.createDelegate(this),
								'blur': function (combo, newValue, oldValue) {
									// см. комментарий к swdrugnonpropnamescombo
									if (combo.getStore().getCount() < 1)  {
										combo.setValue('');
									}
								}.createDelegate(this)

							},
							hiddenName: 'RegOwner'
						},
						{
							xtype: 'swtriplefirmscombo',
							fieldLabel: 'Производитель',
							listeners: {
								change: function(c,n){
									if(this.MainForm.getForm().findField('RegOwner').getValue() != n){
										this.setPrepFullName();
									}
									if(this.PrepType_id == 4){
										this.MainForm.getForm().findField('Packer').setValue(n);
									}
								}.createDelegate(this),
								'blur': function (combo, newValue, oldValue) {
									// см. комментарий к swdrugnonpropnamescombo
									if (combo.getStore().getCount() < 1)  {
										combo.setValue('');
									}
								}.createDelegate(this)
							},
							hiddenName: 'Manufacturer',
							onTrigger3Click: function(){
								var regown = this.MainForm.getForm().findField('RegOwner').getValue();
								var c = this.MainForm.getForm().findField('Manufacturer');
								c.getStore().removeAll();
								c.focus(true);
								c.getStore().baseParams = {FIRMS_ID: regown};
								c.getStore().load({
									callback: function(){
										c.setValue(regown);
										c.collapse();
										c.focus(true, 100);
									}
								});
							}.createDelegate(this)
						},
						{
							xtype: 'swtriplefirmscombo',
							fieldLabel: 'Упаковщик',
							listeners: {
								change: function(c,n){
									if(this.MainForm.getForm().findField('Manufacturer').getValue() != n){
										this.setPrepFullName();
									}
								}.createDelegate(this),
								'blur': function (combo, newValue, oldValue) {
									// см. комментарий к swdrugnonpropnamescombo
									if (combo.getStore().getCount() < 1)  {
										combo.setValue('');
									}
								}.createDelegate(this)
							},
							hiddenName: 'Packer',
							onTrigger3Click: function(){
								var manuf = this.MainForm.getForm().findField('Manufacturer').getValue();
								var c = this.MainForm.getForm().findField('Packer');
								c.getStore().removeAll();
								c.focus(true);
								c.getStore().baseParams = {FIRMS_ID: manuf};
								c.getStore().load({
									callback: function(){
										c.setValue(manuf);
										c.collapse();
										c.focus(true, 100);
									}
								});
							}.createDelegate(this)
						}]
					}]
				}, {
					layout: 'form',
					bodyStyle: 'padding: 0px;',
					border: false,
					defaults: {
						labelAlign: 'right',
						border: false
					},
					items:[{
						autoHeight: true,
						title: 'Данные о потребительской упаковке',
						style:'padding: 10px 10px 0px;',
						xtype: 'fieldset',
						border: true,
						columnWidth: 1,
						labelWidth: 180,
						defaults: {
							bodyStyle: 'padding: 3px;',
							labelAlign: 'right',
							border: false,
							collapsible: false
						},
						items:
						[{
							layout: 'form',
							labelWidth: 185,
							items: [
								{
									xtype: 'textfield',
									anchor: '100%',
									listeners: {
										change: this.setPrepFullName.createDelegate(this)
									},
									name: 'NOMEN_EANCODE',
									fieldLabel: lang['kod_ean']
								}
							]
						}, {
							layout: 'form',
							items: [{
								autoHeight: true,
								title: 'Первичная упаковка',
								style:'padding: 10px;',
								xtype: 'fieldset',
								labelWidth: 188,
								defaults: {
									bodyStyle: 'padding: 3px 0px;',
									labelAlign: 'right',
									border: false
								},
								items:
								[{
									layout: 'form',
									border: false,
									defaults: {
										border: false
									},
									labelWidth: 173,
									items: [
										{
											xtype: 'swbaselocalcombo',
											anchor: '100%',
											editable: true,
											store: new Ext.data.Store({
												autoLoad: false,
												listeners: {
													load: function(s) {
														var c = this.MainForm.getForm().findField('NOMEN_PPACKID');
														if(s.getCount() == 0) {
															c.reset();
															return false;
														}
													}.createDelegate(this)
												},
												baseParams: {
													object: 'DRUGPACK'
												},
												reader: new Ext.data.JsonReader({
													id: 'DRUGPACK_ID'
												}, [{
													mapping: 'DRUGPACK_ID',
													name: 'DRUGPACK_ID',
													type: 'int'
												},{
													mapping: 'FULLNAME',
													name: 'FULLNAME',
													type: 'string'
												}]),
												sortInfo: {field: 'FULLNAME'},
												url: '/?c=Rls&m=getDataForComboStore'
											}),
											listeners: {
												change: this.setPrepFullName.createDelegate(this),
												render: function(){
													this.doQuery = function(q, forceAll) {
														var combo = this;
														//if(q.length<1) return false;
														combo.fireEvent('beforequery', combo);
														var where = combo.displayField+' like \'%'+q+'%\'';
														combo.getStore().baseParams[combo.valueField] = null;
														combo.getStore().baseParams.where = where;
														combo.getStore().baseParams.stringfield = combo.displayField;
														combo.getStore().load();
													};
												}
											},
											hiddenName: 'NOMEN_PPACKID',
											triggerAction: 'all',
											displayField: 'FULLNAME',
											listWidth: 400,
											valueField: 'DRUGPACK_ID',
											fieldLabel: lang['naimenovanie']
										}, {
											xtype: 'swbaselocalcombo',
                                            displayField: 'name',
                                            valueField: 'id',
                                            hiddenName: 'measure',
                                            fieldLabel: lang['ed_izm_pervichnoy_upakovki'],
                                            anchor: '100%',
											editable: false,
											allowBlank: false,
											triggerAction: 'all',
											mode: 'local',
											store: new Ext.data.Store({
												autoLoad: true,
												data: [ 
													[1, 'Штуки'], 
													[2, lang['obyem_pervichnoy_upakovki']],
													[3, lang['massa_pervichnoy_upakovki']]
												],
												reader: new Ext.data.ArrayReader({
													idIndex: 0
												}, [
													{mapping: 0, name: 'id'},
													{mapping: 1, name: 'name'}
												])
											}),
											listeners: {
                                                select: function(c, r, i){
                                                    this.setLinkedFieldsVisible();
                                                }
                                            },
                                            setLinkedFieldsVisible: function () {
                                                switch(this.getValue()){
                                                    case 1:
                                                        cur_win.MainForm.find('name', 'PIECES')[0].setVisible(true);
                                                        cur_win.MainForm.find('name', 'CUBICUNITS')[0].setVisible(false);
                                                        cur_win.MainForm.find('name', 'MASSUNITS2')[0].setVisible(false);
                                                        cur_win.MainForm.getForm().findField('NOMEN_PPACKMASSUNID').setAllowBlank(true);
                                                        cur_win.MainForm.getForm().findField('NOMEN_PPACKCUBUNID').setAllowBlank(true);
                                                        break;
                                                    case 2:
                                                        cur_win.MainForm.find('name', 'PIECES')[0].setVisible(false);
                                                        cur_win.MainForm.find('name', 'CUBICUNITS')[0].setVisible(true);
                                                        cur_win.MainForm.find('name', 'MASSUNITS2')[0].setVisible(false);
                                                        cur_win.MainForm.getForm().findField('NOMEN_PPACKMASSUNID').setAllowBlank(true);
                                                        cur_win.MainForm.getForm().findField('NOMEN_PPACKCUBUNID').setAllowBlank(false);
                                                        break;
                                                    case 3:
                                                        cur_win.MainForm.find('name', 'PIECES')[0].setVisible(false);
                                                        cur_win.MainForm.find('name', 'CUBICUNITS')[0].setVisible(false);
                                                        cur_win.MainForm.find('name', 'MASSUNITS2')[0].setVisible(true);
                                                        cur_win.MainForm.getForm().findField('NOMEN_PPACKMASSUNID').setAllowBlank(false);
                                                        cur_win.MainForm.getForm().findField('NOMEN_PPACKCUBUNID').setAllowBlank(true);
                                                        break;
                                                }
                                                cur_win.doLayout();
                                            }
										}, {
											layout: 'form',
											name: 'CUBICUNITS',
											defaults: {
												border: false
											},
											items: [
												{
													xtype: 'numberfield',
													anchor: '100%',
													allowDecimals: true,
													listeners: {
														change: this.setPrepFullName.createDelegate(this),
														render: function(c) {
														    Ext.QuickTips.register({
														        target: c.getEl(),
														        text: 'Укажите количество ЛС в первичной упаковке: для таблеток указывается количество штук, для порошков – масса порошка в первичной упаковке, для растворов - объем первичной упаковки',
														        enabled: true,
														        showDelay: 5,
														        trackMouse: true,
														        autoShow: true
														    });
													    }
													},
													name: 'NOMEN_PPACKVOLUME',
													fieldLabel: 'Кол-во в перв.уп.'
												},
												{
													xtype: 'swbaselocalcombo',
													anchor: '100%',
													triggerAction: 'all',
													mode: 'local',
													editable: true,
													store: new Ext.data.Store({
														autoLoad: true,
														baseParams: {
															object: 'CUBICUNITS',
															stringfield: 'SHORTNAME',
															where: '1=1'
														},
														reader: new Ext.data.JsonReader({
															id: 'CUBICUNITS_ID'
														}, [{
															mapping: 'CUBICUNITS_ID',
															name: 'CUBICUNITS_ID',
															type: 'int'
														},{
															mapping: 'SHORTNAME',
															name: 'SHORTNAME',
															type: 'string'
														}]),
														url: '/?c=Rls&m=getDataForComboStore'
													}),
													listeners: {
														change: this.setPrepFullName.createDelegate(this)
													},
													hiddenName: 'NOMEN_PPACKCUBUNID',
													valueField: 'CUBICUNITS_ID',
													displayField: 'SHORTNAME',
													fieldLabel: 'Ед.измерения'
												}
											]
										}, {
											layout: 'form',
											name: 'MASSUNITS2',
											defaults: {
												border: false
											},
											items: [{
												layout: 'form',
												labelWidth: 173,
												items: [
													{
														xtype: 'numberfield',
														anchor: '100%',
														allowDecimals: true,
														listeners: {
															change: this.setPrepFullName.createDelegate(this),
															render: function(c) {
															    Ext.QuickTips.register({
															        target: c.getEl(),
															        text: 'Укажите количество ЛС в первичной упаковке: для таблеток указывается количество штук, для порошков – масса порошка в первичной упаковке, для растворов - объем первичной упаковки',
															        enabled: true,
															        showDelay: 5,
															        trackMouse: true,
															        autoShow: true
															    });
														    }
														},
														name: 'NOMEN_PPACKMASS',
														fieldLabel: 'Кол-во в перв.уп.'
													}
												]
											}, {
												layout: 'form',
												items: [{
													xtype: 'swbaselocalcombo',
													anchor: '100%',
													editable: false,
													triggerAction: 'all',
													mode: 'local',
													store: new Ext.data.Store({
														autoLoad: true,
														baseParams: {
															object: 'MASSUNITS',
															stringfield: 'SHORTNAME',
															where: '1=1'
														},
														reader: new Ext.data.JsonReader({
															id: 'MASSUNITS_ID'
														}, [{
															mapping: 'MASSUNITS_ID',
															name: 'MASSUNITS_ID',
															type: 'int'
														},{
															mapping: 'SHORTNAME',
															name: 'SHORTNAME',
															type: 'string'
														}]),
														url: '/?c=Rls&m=getDataForComboStore'
													}),
													listeners: {
														change: this.setPrepFullName.createDelegate(this)
													},
													hiddenName: 'NOMEN_PPACKMASSUNID',
													valueField: 'MASSUNITS_ID',
													displayField: 'SHORTNAME',
													fieldLabel: 'Ед.измерения'
												}]
											}]
										}, {
											layout: 'form',
											name: 'PIECES',
											defaults: {
												border: false
											},
											items: [{
												layout: 'form',
												labelWidth: 173,
												items: [{
													xtype: 'numberfield',
													anchor: '100%',
													decimalPrecision: 0,
													listeners: {
														change: this.setPrepFullName.createDelegate(this),
														render: function(c) {
														    Ext.QuickTips.register({
														        target: c.getEl(),
														        text: 'Укажите количество ЛС в первичной упаковке: для таблеток указывается количество штук, для порошков – масса порошка в первичной упаковке, для растворов - объем первичной упаковки',
														        enabled: true,
														        showDelay: 5,
														        trackMouse: true,
														        autoShow: true
														    });
													    }
													},
													name: 'NOMEN_DRUGSINPPACK',
													fieldLabel: 'Кол-во в перв.уп.'
												}]
											}]
										}, {
											layout: 'form',
											labelWidth: 203,
											items: [{
												xtype: 'swbaselocalcombo',
												anchor: '100%',
												store: new Ext.data.Store({
													autoLoad: false,
													listeners: {
														load: function(s) {
															var c = this.MainForm.getForm().findField('NOMEN_SETID');
															if(s.getCount() == 0) {
																c.reset();
																return false;
															}
														}.createDelegate(this)
													},
													baseParams: {
														object: 'DRUGSET'
													},
													reader: new Ext.data.JsonReader({
														id: 'DRUGSET_ID'
													}, [{
														mapping: 'DRUGSET_ID',
														name: 'DRUGSET_ID',
														type: 'int'
													},{
														mapping: 'SHORTNAME',
														name: 'SHORTNAME',
														type: 'string'
													}]),
													url: '/?c=Rls&m=getDataForComboStore'
												}),
												hiddenName: 'NOMEN_SETID',
												triggerAction: 'all',
												displayField: 'SHORTNAME',
												valueField: 'DRUGSET_ID',
												emptyText: lang['vvedite_nazvanie_komplekta_k_pervichnoy_upakovke'],
												fieldLabel: lang['nazv_komplekta_k_perv_upakovke'],
												editable:true,
												listeners:{
													change: this.setPrepFullName.createDelegate(this),
													render:function(){
														this.doQuery = function(q, forceAll) {
															var combo = this;
															if(q.length<1) return false;
															combo.fireEvent('beforequery', combo);
															var where = combo.displayField+' like \''+q+'%\'';
															combo.getStore().baseParams[combo.valueField] = null;
															combo.getStore().baseParams.where = where;
															combo.getStore().baseParams.stringfield = combo.displayField;
															combo.getStore().load();
														};
													}
												}
											}]
										}
									]
								}]
							}, {
								autoHeight: true,
								title: 'Вторичная упаковка',
								style:'padding: 10px;',
								xtype: 'fieldset',
								columnWidth: 1,
								labelWidth: 173,
								defaults: {
									bodyStyle: 'padding: 3px 0px;',
									labelAlign: 'right',
									border: false
								},
								items:
								[{
									layout: 'form',
									defaults: {
										border: false
									},
									items: [{
										layout: 'form',
										labelWidth: 173,
										items: [
											{
												xtype: 'combo',
												anchor: '100%',
												store: new Ext.data.Store({
													autoLoad: false,
													listeners: {
														load: function(s) {
															var c = this.MainForm.getForm().findField('NOMEN_UPACKID');
															if(s.getCount() == 0) {
																c.reset();
																return false;
															}
														}.createDelegate(this)
													},
													baseParams: {
														object: 'DRUGPACK'
													},
													reader: new Ext.data.JsonReader({
														id: 'DRUGPACK_ID'
													}, [{
														mapping: 'DRUGPACK_ID',
														name: 'DRUGPACK_ID',
														type: 'int'
													},{
														mapping: 'FULLNAME',
														name: 'FULLNAME',
														type: 'string'
													}]),
													sortInfo: {field: 'FULLNAME'},
													url: '/?c=Rls&m=getDataForComboStore'
												}),
												listeners: {
													change: function(c,n){
														if(!Ext.isEmpty(n)){
															this.MainForm.getForm().findField('NOMEN_PPACKINUPACK').setAllowBlank(false);
														} else {
															this.MainForm.getForm().findField('NOMEN_PPACKINUPACK').setAllowBlank(true);
														}
														this.setPrepFullName();
													}.createDelegate(this),
													render:function(){
														this.doQuery = function(q, forceAll) {
															var combo = this;
															//if(q.length<1) return false;
															combo.fireEvent('beforequery', combo);
															var where = combo.displayField+' like \'%'+q+'%\'';
															combo.getStore().baseParams[combo.valueField] = null;
															combo.getStore().baseParams.where = where;
															combo.getStore().baseParams.stringfield = combo.displayField;
															combo.getStore().load();
														};
													}
												},
												hiddenName: 'NOMEN_UPACKID',
												triggerAction: 'all',
												editable: true,
												listWidth: 400,
												displayField: 'FULLNAME',
												valueField: 'DRUGPACK_ID',
												fieldLabel: lang['nazvanie_vtor_upakovki']
											}
										]
									}, {
										layout: 'form',
										labelWidth: 203,
										items: [
											{
												xtype: 'numberfield',
												anchor: '100%',
												decimalPrecision: 0,
												name: 'NOMEN_PPACKINUPACK',
												fieldLabel: lang['kol-vo_perv_upakovok_vo_vtor']
											}
										]
									}]
								}]
							}, {
								autoHeight: true,
								title: 'Третичная упаковка',
								bodyStyle:'padding: 5px;',
								xtype: 'fieldset',
								columnWidth: 1,
								labelWidth: 173,
								defaults: {
									bodyStyle: 'padding: 3px 0px;',
									labelAlign: 'right',
									border: false
								},
								items:
								[{
									layout: 'form',
									defaults: {
										border: false
									},
									items: [{
										layout: 'form',
										labelWidth: 173,
										items: [{
											xtype: 'combo',
											anchor: '100%',
											store: new Ext.data.Store({
												autoLoad: false,
												listeners: {
													load: function(s) {
														var c = this.MainForm.getForm().findField('NOMEN_SPACKID');
														if(s.getCount() == 0) {
															c.reset();
															return false;
														}
													}.createDelegate(this)
												},
												baseParams: {
													object: 'DRUGPACK'
												},
												reader: new Ext.data.JsonReader({
													id: 'DRUGPACK_ID'
												}, [{
													mapping: 'DRUGPACK_ID',
													name: 'DRUGPACK_ID',
													type: 'int'
												},{
													mapping: 'FULLNAME',
													name: 'FULLNAME',
													type: 'string'
												}]),
												sortInfo: {field: 'FULLNAME'},
												url: '/?c=Rls&m=getDataForComboStore'
											}),
											listeners: {
												change: function(c,n){
													if(!Ext.isEmpty(n)){
														this.MainForm.getForm().findField('NOMEN_SPACKID').setAllowBlank(false);
													} else {
														this.MainForm.getForm().findField('NOMEN_SPACKID').setAllowBlank(true);
													}
													this.setPrepFullName();
												}.createDelegate(this),
												render: function(){
													this.doQuery = function(q, forceAll) {
														var combo = this;
														//if(q.length<1) return false;
														combo.fireEvent('beforequery', combo);
														var where = combo.displayField+' like \'%'+q+'%\'';
														combo.getStore().baseParams[combo.valueField] = null;
														combo.getStore().baseParams.where = where;
														combo.getStore().baseParams.stringfield = combo.displayField;
														combo.getStore().load();
													};
												}
											},
											hiddenName: 'NOMEN_SPACKID',
											triggerAction: 'all',
											editable: true,
											listWidth: 400,
											displayField: 'FULLNAME',
											valueField: 'DRUGPACK_ID',
											fieldLabel: lang['nazvanie_tret_upakovki']
										}]
									}, {
										layout: 'form',
										labelWidth: 203,
										items: [{
											xtype: 'numberfield',
											anchor: '100%',
											listeners: {
												change: this.setPrepFullName.createDelegate(this)
											},
											decimalPrecision: 0,
											name: 'NOMEN_UPACKINSPACK',
											fieldLabel: lang['kol-vo_vtor_upakovok_vo_tret']
										}]
									}]
								}]
							}]
						}]
					}]
				},  
				{
					layout: 'form',
					border: false,
					labelWidth: 188,
					items: [
						{
							comboSubject: 'YesNo',
							xtype: 'swcommonsprcombo',
							anchor: '100%',
							hiddenName: 'NORECIPE',
							fieldLabel: 'Отпускается по рецепту'
						}
					]
				}]
			}],
			reader: new Ext.data.JsonReader(
			{
				success: function(){}
			},
			[
				{ name: 'NOMEN_ID' },
				{ name: 'Prep_id' },
				{ name: 'Extemporal_id' },
				{ name: 'DrugNonpropNames_id' },
				{ name: 'REGCERT_ID' },
				{ name: 'TRADENAMES_ID' },
				{ name: 'TRADENAMES_NAME' },
				{ name: 'LATINNAMES_ID' },
				{ name: 'LATINNAMES_NAME' },
				{ name: 'LATINNAMES_NameGen' },
				{ name: 'CLSDRUGFORMS_ID' },
				{ name: 'DFMASS' },
				{ name: 'DFMASSID' },
				{ name: 'DFCONC' },
				{ name: 'DFCONCID' },
				{ name: 'DFACT' },
				{ name: 'DFACTID' },
				{ name: 'DRUGDOSE' },
				{ name: 'DRUGLIFETIME_ID' },
				{ name: 'DRUGLIFETIME_TEXT' },
				{ name: 'DRUGSTORCOND_ID' },
				{ name: 'DRUGSTORCOND_TEXT' },
				{ name: 'DFSIZE' },
				{ name: 'DFSIZE_LAT' },
				{ name: 'DFSIZEID' },
                { name: 'SIZEUNITS_LAT' },
				{ name: 'DFCHARID' },
				{ name: 'PrepType_id' },
				{ name: 'IDENT_WIND_STR_id' },
				
				{ name: 'NOMEN_DRUGSINPPACK' },
				{ name: 'NOMEN_PPACKID' },
				{ name: 'NOMEN_PPACKVOLUME' },
				{ name: 'NOMEN_PPACKCUBUNID' },
				{ name: 'NOMEN_PPACKMASS' },
				{ name: 'NOMEN_PPACKMASSUNID' },
				{ name: 'NOMEN_SETID' },
				{ name: 'NOMEN_PPACKINUPACK' },
				{ name: 'NOMEN_UPACKID' },
				{ name: 'NOMEN_UPACKINSPACK' },
				{ name: 'NOMEN_SPACKID' },
				{ name: 'FIRMS_ID' },
				{ name: 'Manufacturer' },
				{ name: 'Packer' },
				{ name: 'RegOwner' },
				{ name: 'NOMEN_EANCODE' },
				{ name: 'REGCERT_REGNUM' },
				{ name: 'REGCERT_REGDATE' },
				{ name: 'REGCERT_ENDDATE' },
				{ name: 'REGCERT_REGDATERange' },
				{ name: 'Reregdate' },
				{ name: 'REGCERT_excDT' },
				{ name: 'NORECIPE' },
				{ name: 'TN_DF_LIMP' },
				{ name: 'TRADENAMES_DRUGFORMS' },
				
				{ name: 'DESCRIPTIONS_ID' },
				{ name: 'DESCTEXTES_COMPOSITION' },
				{ name: 'DESCTEXTES_CHARACTERS' },
				{ name: 'DESCTEXTES_PHARMAACTIONS' },
				{ name: 'DESCTEXTES_ACTONORG' },
				{ name: 'DESCTEXTES_COMPONENTSPROPERTIES' },
				{ name: 'DESCTEXTES_PHARMAKINETIC' },
				{ name: 'DESCTEXTES_PHARMADYNAMIC' },
				{ name: 'DESCTEXTES_CLINICALPHARMACOLOGY' },
				{ name: 'DESCTEXTES_DIRECTION' },
				{ name: 'DESCTEXTES_INDICATIONS' },
				{ name: 'DESCTEXTES_RECOMMENDATIONS' },
				{ name: 'DESCTEXTES_CONTRAINDICATIONS' },
				{ name: 'DESCTEXTES_PREGNANCYUSE' },
				{ name: 'DESCTEXTES_SIDEACTIONS' },
				{ name: 'DESCTEXTES_INTERACTIONS' },
				{ name: 'DESCTEXTES_USEMETHODANDDOSES' },
				{ name: 'DESCTEXTES_INSTRFORPAC' },
				{ name: 'DESCTEXTES_OVERDOSE' },
				{ name: 'DESCTEXTES_PRECAUTIONS' },
				{ name: 'DESCTEXTES_SPECIALGUIDELINES' }
			])
		});

		this.PrepDataForm2 = new Ext.form.FormPanel({
			bodyStyle: 'padding: 10px 3px 3px 3px; border-top: 0px;',
			defaults: {
				bodyStyle: 'padding: 3px;',
				collapsible: true
			},
			labelAlign: 'right',
			labelWidth: 250,
			items: [
				{
					xtype: 'textfield',
					anchor: '95%',
					allowBlank: false,
					name: 'DRUGLIFETIME_TEXT',
					id: 'DRUGLIFETIME_TEXT',
					fieldLabel: 'Срок хранения'
				},
				{
					xtype: 'textfield',
					anchor: '95%',
					allowBlank: false,
					name: 'DRUGSTORCOND_TEXT',
					id: 'DRUGSTORCOND_TEXT',
					fieldLabel: 'Условия хранения'
				},
				{
					xtype: 'textarea',
					anchor: '95%',
					name: 'DESCTEXTES_PRECAUTIONS',
					id: 'DESCTEXTES_PRECAUTIONS',
					fieldLabel: lang['meryi_predostorojnosti']
				},
				{
					xtype: 'textarea',
					anchor: '95%',
					allowBlank: false,
					name: 'DESCTEXTES_USEMETHODANDDOSES',
					id: 'DESCTEXTES_USEMETHODANDDOSES',
					fieldLabel: lang['sposob_primeneniya_i_dozyi']
				}, {
					xtype: 'textarea',
					anchor: '95%',
					name: 'DESCTEXTES_INSTRFORPAC',
					id: 'DESCTEXTES_INSTRFORPAC',
					fieldLabel: lang['instruktsiya_dlya_patsienta']
				},
				{
					layout: 'form',
					labelWidth: 550,
					border: false,
					items:[
						{
							comboSubject: 'YesNo',
							xtype: 'swcommonsprcombo',
							anchor: '95%',
							hiddenName: 'TN_DF_LIMP',
							id: 'RLS_TN_DF_LIMP',
							allowBlank: false,
							fieldLabel: lang['otnositsya_li_k_jiznennovajnyim_lek_sredstvam_po_klassif_mz_rf_po_torg_nazvaniyam']
						}
					]
				},
				{
					layout: 'form',
					labelWidth: 550,
					border: false,
					items:[
						{
							comboSubject: 'YesNo',
							xtype: 'swcommonsprcombo',
							anchor: '95%',
							hiddenName: 'TRADENAMES_DRUGFORMS',
							id: 'RLS_TRADENAMES_DRUGFORMS',
							allowBlank: false,
							fieldLabel: lang['yavlyaetsya_li_preparatom_lgotnogo_assortimenta_cherez_torg_nazvanie_i_lek_formu']
						}
					]
				}
			]
		});

		this.LsLinkGrid = new sw.Promed.ViewFrame({
			id: this.id + '_LsLinkGrid',
			title: 'Список взаимодействий',
			actions: [
				{name: 'action_add', handler: function(){this.openLsLinkEditWindow('add');}.createDelegate(this)},
				{name: 'action_edit', handler: function(){this.openLsLinkEditWindow('edit');}.createDelegate(this)},
				{name: 'action_view', handler: function(){this.openLsLinkEditWindow('view');}.createDelegate(this)},
				{name: 'action_delete', handler: function(){this.deleteLsLink();}.createDelegate(this)},
				{name: 'action_refresh'},
				{name: 'action_print'}
			],
			dataUrl: '/?c=LsLink&m=loadLsLinkGrid',
			paging: true,
			autoLoadData: false,
			root: 'data',
			onRowSelect: function(sm,index,record) {
				this.setActionDisabled('action_link', Ext.isEmpty(record.get('LS_LINK_ID')) || record.get('HAS_PREP_LS') == 'true');
				this.setActionDisabled('action_unlink', Ext.isEmpty(record.get('LS_LINK_ID')) || record.get('HAS_PREP_LS') != 'true');
			},
			stringfields: [
				{name: 'LS_LINK_ID', type: 'int', hidden: true, key: true},
				{name: 'LS_GROUP1', type: 'string', header: langs('Группа 1 ЛС'), width: 150},
				{name: 'LS_GROUP2', type: 'string', header: langs('Группа 2 ЛС'), width: 150},
				{name: 'LS_INFLUENCE_TYPE_NAME', type: 'string', header: langs('Тип влияния'), id: 'autoexpand'},
				{name: 'LS_EFFECT_NAME', type: 'string', header: langs('Тер. эфф.'), width: 150},
				{name: 'LS_INTERACTION_CLASS_NAME', type: 'string', header: langs('Класс взаим.'), width: 150},
				{name: 'HAS_PREP_LS', type: 'checkbox', header: langs('ГРЛС'), width: 150}
			]
		});

		this.PrepDataForm = new Ext.form.FormPanel({
			bodyStyle: 'padding: 0px 3px 3px 3px; border-top: 0px;',
			defaults: {
				bodyStyle: 'padding: 3px;',
				collapsible: true
			},
			autoHeight: true,
			items: [
				{
					layout: 'form',
					border: false,
					autoHeight: true,
					defaults: {
						anchor: '100%'
					},
					labelAlign: 'right',
					width: '95%',
					labelWidth: 250,
					items: [
						{
							xtype: 'textarea',
							name: 'DESCTEXTES_PHARMAACTIONS',
							id: 'DESCTEXTES_PHARMAACTIONS',
							fieldLabel: lang['farmakologicheskoe_deystvie']
						}, {
							xtype: 'textarea',
							name: 'DESCTEXTES_ACTONORG',
							id: 'DESCTEXTES_ACTONORG',
							fieldLabel: lang['deystvie_na_organizm']
						}, {
							xtype: 'textarea',
							name: 'DESCTEXTES_COMPONENTSPROPERTIES',
							id: 'DESCTEXTES_COMPONENTSPROPERTIES',
							fieldLabel: lang['svoystva_komponentov']
						}, {
							xtype: 'textarea',
							name: 'DESCTEXTES_PHARMAKINETIC',
							id: 'DESCTEXTES_PHARMAKINETIC',
							fieldLabel: lang['farmakokinetika']
						}, {
							xtype: 'textarea',
							name: 'DESCTEXTES_CLINICALPHARMACOLOGY',
							id: 'DESCTEXTES_CLINICALPHARMACOLOGY',
							fieldLabel: lang['klinicheskaya_farmakologiya']
						}, {
							xtype: 'textarea',
							name: 'DESCTEXTES_DIRECTION',
							id: 'DESCTEXTES_DIRECTION',
							fieldLabel: lang['instruktsiya']
						}, {
							xtype: 'textarea',
							name: 'DESCTEXTES_INDICATIONS',
							id: 'DESCTEXTES_INDICATIONS',
							fieldLabel: lang['pokazaniya']
						}, {
							xtype: 'textarea',
							name: 'DESCTEXTES_RECOMMENDATIONS',
							id: 'DESCTEXTES_RECOMMENDATIONS',
							fieldLabel: lang['rekomenduetsya']
						}, {
							xtype: 'textarea',
							name: 'DESCTEXTES_CONTRAINDICATIONS',
							id: 'DESCTEXTES_CONTRAINDICATIONS',
							fieldLabel: lang['protivopokazaniya']
						}, {
							xtype: 'textarea',
							name: 'DESCTEXTES_PREGNANCYUSE',
							id: 'DESCTEXTES_PREGNANCYUSE',
							fieldLabel: lang['primen_pri_berem-ti_i_korml_grudyu']
						}, {
							xtype: 'textarea',
							name: 'DESCTEXTES_SIDEACTIONS',
							id: 'DESCTEXTES_SIDEACTIONS',
							fieldLabel: lang['pobochnyie_deystviya']
						}, {
							collapsible: true,
							title: langs('Взаимодействие'),
							layout: 'form',
							items: [{
								anchor: '100%',
								xtype: 'textarea',
								name: 'DESCTEXTES_INTERACTIONS',
								id: 'DESCTEXTES_INTERACTIONS',
								fieldLabel: lang['vzaimodeystvie']
							}, cur_win.LsLinkGrid],
							xtype: 'panel'
						}, {
							xtype: 'textarea',
							name: 'DESCTEXTES_OVERDOSE',
							id: 'DESCTEXTES_OVERDOSE',
							fieldLabel: lang['peredozirovka']
						}, {
							xtype: 'textarea',
							name: 'DESCTEXTES_SPECIALGUIDELINES',
							id: 'DESCTEXTES_SPECIALGUIDELINES',
							fieldLabel: lang['osobyie_ukazaniya']
						}
					]
				}
			]
		});

		this.ImgTpl = [
			'<div><a href="{file_url}" target="_blank"><img style="text-align: center;" height="200" width="200" src="{file_url}" /></a></div>'
		];
		
		this.Image = new Ext.form.FieldSet({
			autoHeight: true,
			id: 'imageBody',
			width: 570,
			style: 'margin-left: 10px;',
			title: ' '
		});
		
		this.ImagePanel = new Ext.form.FormPanel({
			bodyStyle: 'padding: 0px 3px 3px 3px; border-top: 0px; border-bottom: 0px;',
			defaults: {
				collapsible: true,
				bodyStyle: 'padding: 3px;'
			},
			url: '/?c=Rls&m=preuploadImage',
			fileUpload: true,
			items: [
				{
					layout: 'form',
					titleCollapse: true,
					animCollapse: false,
					title: lang['fayl_s_risunkom'],
					items: [
						{
							layout: 'column',
							border: false,
							defaults: {
								border: false
							},
							items: [
								{
									layout: 'form',
									width: 580,
									labelAlign: 'right',
									labelWidth: 130,
									items: [
										{
											xtype: 'fileuploadfield',
											anchor: '100%',
											file_uploaded: false,
											name: 'file',
											listeners: {
												fileselected: function(f, v) {
													if(f.dasabled){
														f.reset();
														return false;
													}
													var allowedFiles = ['gif', 'jpg', 'jpeg'], // допустимые расширения
														re = new RegExp(allowedFiles.join('|'), 'ig');
													if(!re.test(v)) {
														sw.swMsg.alert(lang['oshibka'], lang['dannyiy_tip_zagrujaemogo_fayla_ne_podderjivaetsya']);
														f.reset();
														return false;
													}
													var pBar = this.ImagePanel.find('name', 'progressbar')[0];
													pBar.updateProgress(0, lang['zagrujeno_0%'], false);
													
													// пока так:
													this.ImagePanel.find('xtype', 'button')[0].handler();
												}.createDelegate(this)
											},
											buttonText: lang['vyibrat'],
											fieldLabel: lang['fayl_s_risunkom']
										}
									]
								}, {
									layout: 'form',
									hidden: true,
									items: [
										{
											xtype: 'button',
											style: 'margin-left: 5px;',
											text: lang['zagruzit'],
											handler: function(){
												var form = this.ImagePanel.getForm();
												var field = this.ImagePanel.find('xtype', 'fileuploadfield')[0];
												
												var cb = function(f, r) {
													var obj = Ext.util.JSON.decode(r.response.responseText);
													this.overwriteTpl(obj);
													this.Image.setTitle(f.findField('file').getRawValue());
													field.file_uploaded = true;
												}
												
												if ( !this.loadFileWithFileAPI(cb) ) {
													form.submit({
														url: this.ImagePanel.url,
														success: cb.createDelegate(this)
													});
												}
											}.createDelegate(this)
										}
									]
								}
							]
						}, new Ext.ProgressBar({
							hidden: true,
							name: 'progressbar',
							height: 20,
							width: 570,
							text: 'text',
							style: 'margin: 10px;'
						}),
						this.Image
					]
				}
			]
		});
		
		this.AutorPanel = new Ext.form.FormPanel({
			bodyStyle: 'padding: 0px 3px 3px 3px; border-top: 0px;',
			defaults: {
				bodyStyle: 'padding: 3px;',
				collapsible: true
			},
			height: 300,
			items: [
				new sw.Promed.Panel({
					title: lang['avtor'],
					collapsed: true,
					defaults: {
						labelAlign: 'right'
					},
					items: [
						{
							xtype: 'fieldset',
							autoHeight: true,
							defaults: {
								width: 500,
								readOnly: true,
								style: 'border: 0px; background: #fff;'
							},
							title: lang['sozdanie'],
							items: [
								{
									xtype: 'textfield',
									name: 'autor_date_ins',
									fieldLabel: lang['data']
								}, {
									xtype: 'textfield',
									name: 'autor_username_ins',
									fieldLabel: lang['polzovatel']
								}, {
									xtype: 'textfield',
									name: 'autor_orgname_ins',
									fieldLabel: lang['organizatsiya']
								}
							]
						}, {
							xtype: 'fieldset',
							autoHeight: true,
							defaults: {
								width: 500,
								readOnly: true,
								style: 'border: 0px; background: #fff;'
							},
							title: lang['poslednee_izmenenie'],
							items: [
								{
									xtype: 'textfield',
									name: 'autor_date_upd',
									fieldLabel: lang['data']
								}, {
									xtype: 'textfield',
									name: 'autor_username_upd',
									fieldLabel: lang['polzovatel']
								}, {
									xtype: 'textfield',
									name: 'autor_orgname_upd',
									fieldLabel: lang['organizatsiya']
								}
							]
						}
					]
				})
			]
		});
		//Создание окон для выбора Классификации АТХ и Фармакологических групп
		//Комбобоксы для Классификации АТХ и Фармакологических групп
		this.AtcCombo = {
			xtype: 'combo',
			anchor: '95%',
			store: new Ext.data.Store({
				autoLoad: false,
				listeners: {
					load: function(s) {
						var c = this.SelectAddAtcWindow.findById('RLS_CLSATC_ID');
						if(s.getCount() == 0) {
							c.reset();
							return false;
						}
					}.createDelegate(this)
				},
				baseParams: {
					object: 'CLSATC',
					stringfield: 'NAME'
				},
				reader: new Ext.data.JsonReader({
					id: 'CLSATC_ID'
				}, [{
					mapping: 'CLSATC_ID',
					name: 'CLSATC_ID',
					type: 'int'
				},{
					mapping: 'NAME',
					name: 'NAME',
					type: 'string'
				}]),
				url: '/?c=Rls&m=getDataForComboStore'
			}),
			initTrigger: this.inT,
			triggerConfig:	{
				tag:'span', cls:'x-form-twin-triggers', cn:[
					{tag: "img", src: Ext.BLANK_IMAGE_URL, cls: "x-form-trigger x-form-select-trigger"},
					{tag: "img", src: Ext.BLANK_IMAGE_URL, cls: "x-form-trigger x-form-search-trigger"}
				]},
			onTrigger1Click: this.onTrigger1Click,
			onTrigger2Click: function(){
				var win = this;
				var c = this.SelectAddAtcWindow.findById('RLS_CLSATC_ID');
				if(!win.formListATC){
					c.getStore().baseParams.stringfield = c.displayField;
					win.formListATC = new sw.Promed.swListSearchWindow({
						title: langs('Классификация АТХ: поиск'),
						id: 'CLSATC_SearchWindow',
						object: 'rls.CLSATC',
						prefix: 'clsatc',
						useBaseParams: true,
						store: c.getStore()
					});
				}
				win.formListATC.show({
					onSelect: function(data){
						c.getStore().removeAll();
						c.focus(true);
						c.getStore().load({
							params: {
								CLSATC_ID: data[c.hiddenName]
							},
							callback: function(){
								c.setValue(data[c.hiddenName]);
								c.collapse();
								c.focus(true, 100);
							}
						});
					}
				});
			}.createDelegate(this),
			doQuery: function(q, forceAll) {
				var combo = this;
				//if(q.length<1) return false;
				combo.fireEvent('beforequery', combo);
				var where = combo.displayField+' like \'%'+q+'%\'';
				combo.getStore().baseParams[combo.valueField] = null;
				combo.getStore().baseParams.where = where;
				combo.getStore().baseParams.stringfield = combo.displayField;
				combo.getStore().load();
			},
			hiddenName: 'CLSATC_ID',
			id: 'RLS_CLSATC_ID',
			triggerAction: 'none',
			displayField: 'NAME',
			valueField: 'CLSATC_ID',
			fieldLabel: langs('Классификация АТХ')
		};

		this.PharmaGroupCombo = {
			xtype: 'combo',
			anchor: '95%',
			store: new Ext.data.Store({
				autoLoad: false,
				listeners: {
					load: function(s) {
						var c = this.SelectAddPharmaGroupWindow.findById('RLS_CLSPHARMAGROUP_ID');
						if(s.getCount() == 0) {
							c.reset();
							return false;
						}
					}.createDelegate(this)
				},
				baseParams: {
					object: 'CLSPHARMAGROUP',
					stringfield: 'NAME'
				},
				reader: new Ext.data.JsonReader({
					id: 'CLSPHARMAGROUP_ID'
				}, [{
					mapping: 'CLSPHARMAGROUP_ID',
					name: 'CLSPHARMAGROUP_ID',
					type: 'int'
				},{
					mapping: 'NAME',
					name: 'NAME',
					type: 'string'
				}]),
				url: '/?c=Rls&m=getDataForComboStore'
			}),
			initTrigger: this.inT,
			triggerConfig:	{
				tag:'span', cls:'x-form-twin-triggers', cn:[
					{tag: "img", src: Ext.BLANK_IMAGE_URL, cls: "x-form-trigger x-form-select-trigger"},
					{tag: "img", src: Ext.BLANK_IMAGE_URL, cls: "x-form-trigger x-form-search-trigger"}
				]},
			onTrigger1Click: this.onTrigger1Click,
			onTrigger2Click: function(){
				var c = this;
				if(!this.formList){
					this.getStore().baseParams.stringfield = this.displayField;
					this.formList = new sw.Promed.swListSearchWindow({
						title: langs('Фармакологическая группа: поиск'),
						id: 'CLSPHARMAGROUP_SearchWindow',
						object: 'rls.CLSPHARMAGROUP',
						prefix: 'clspharmagroup',
						useBaseParams: true,
						store: this.getStore()
					});
				}
				this.formList.show({
					onSelect: function(data){
						c.getStore().removeAll();
						c.focus(true);
						c.getStore().load({
							params: {
								CLSPHARMAGROUP_ID: data[c.hiddenName]
							},
							callback: function(){
								c.setValue(data[c.hiddenName]);
								c.collapse();
								c.focus(true, 100);
							}
						});
					}
				});
			},
			doQuery: function(q, forceAll) {
				var combo = this;
				//if(q.length<1) return false;
				combo.fireEvent('beforequery', combo);
				var where = combo.displayField+' like \'%'+q+'%\'';
				combo.getStore().baseParams[combo.valueField] = null;
				combo.getStore().baseParams.where = where;
				combo.getStore().baseParams.stringfield = combo.displayField;
				combo.getStore().load();
			},
			hiddenName: 'CLSPHARMAGROUP_ID',
			id: 'RLS_CLSPHARMAGROUP_ID',
			triggerAction: 'none',
			displayField: 'NAME',
			valueField: 'CLSPHARMAGROUP_ID',
			fieldLabel: langs('Фармакологическая группа')
		};

		this.FTGGRLSCombo = {
			xtype: 'combo',
			anchor: '95%',
			store: new Ext.data.Store({
				autoLoad: false,
				listeners: {
					load: function(s) {
						var c = this.SelectAddFTGGRLSWindow.findById('RLS_FTGGRLS_ID');
						if(s.getCount() == 0) {
							c.reset();
							return false;
						}
					}.createDelegate(this)
				},
				baseParams: {
					object: 'FTGGRLS',
					stringfield: 'NAME'
				},
				reader: new Ext.data.JsonReader({
					id: 'FTGGRLS_ID'
				}, [{
					mapping: 'FTGGRLS_ID',
					name: 'FTGGRLS_ID',
					type: 'int'
				},{
					mapping: 'NAME',
					name: 'NAME',
					type: 'string'
				}]),
				url: '/?c=Rls&m=getDataForComboStore'
			}),
			initTrigger: this.inT,
			triggerConfig:	{
				tag:'span', cls:'x-form-twin-triggers', cn:[
					{tag: "img", src: Ext.BLANK_IMAGE_URL, cls: "x-form-trigger x-form-select-trigger"},
					{tag: "img", src: Ext.BLANK_IMAGE_URL, cls: "x-form-trigger x-form-search-trigger"}
				]},
			onTrigger1Click: this.onTrigger1Click,
			onTrigger2Click: function(){
				var c = this;
				if(!this.formList){
					this.getStore().baseParams.stringfield = this.displayField;
					this.formList = new sw.Promed.swListSearchWindow({
						title: langs('Фармакологическая группа: поиск'),
						id: 'FTGGRLS_SearchWindow',
						object: 'rls.FTGGRLS',
						prefix: 'ftggrls',
						useBaseParams: true,
						store: this.getStore()
					});
				}
				this.formList.show({
					onSelect: function(data){
						c.getStore().removeAll();
						c.focus(true);
						c.getStore().load({
							params: {
								FTGGRLS_ID: data[c.hiddenName]
							},
							callback: function(){
								c.setValue(data[c.hiddenName]);
								c.collapse();
								c.focus(true, 100);
							}
						});
					}
				});
			},
			doQuery: function(q, forceAll) {
				var combo = this;
				//if(q.length<1) return false;
				combo.fireEvent('beforequery', combo);
				var where = combo.displayField+' like \'%'+q+'%\'';
				combo.getStore().baseParams[combo.valueField] = null;
				combo.getStore().baseParams.where = where;
				combo.getStore().baseParams.stringfield = combo.displayField;
				combo.getStore().load();
			},
			hiddenName: 'FTGGRLS_ID',
			id: 'RLS_FTGGRLS_ID',
			triggerAction: 'none',
			displayField: 'NAME',
			valueField: 'FTGGRLS_ID',
			fieldLabel: langs('Фармакотерапевтическая группа')
		};

		//Окна для выбора класса АТХ и фармакологических групп
		this.SelectAddAtcWindow = new Ext.Window({
			id: 'rpnew_SelectAddAtcWindow',
			closable: false,
			width : 500,
			modal: true,
			resizable: false,
			autoHeight: false,
			closeAction :'hide',
			border : false,
			plain : false,
			title: 'Выбор класса АТХ',
			items : [new Ext.form.FormPanel({
				layout : 'form',
				border : false,
				frame : true,
				labelWidth : 120,
				items : [this.AtcCombo]
			})],
			buttons : [{
				handler: function () {
					var combo = this.SelectAddAtcWindow.findById('RLS_CLSATC_ID');
					var index = combo.getStore().findBy(function(rec) { return rec.get('CLSATC_ID') == combo.value; });
					var record = combo.getStore().getAt(index);
					this.AtcGrid.getGrid().getStore().loadData([record.data], true);
					this.AtcGrid.ViewGridPanel.getView().refresh();
					combo.reset();
					this.SelectAddAtcWindow.hide();
				}.createDelegate(this),
				iconCls: 'save16',
				text: BTN_FRMSAVE
			}, {
				text: '-'
			}, {
				handler: function () {
					this.ownerCt.hide();
				},
				iconCls: 'cancel16',
				text: BTN_FRMCANCEL
			}],

		});

		this.SelectAddPharmaGroupWindow = new Ext.Window({
			id: 'rpnew_SelectAddPharmaGroupWindow',
			closable: false,
			width : 600,
			modal: true,
			resizable: false,
			autoHeight: false,
			closeAction :'hide',
			border : false,
			plain : false,
			title: 'Выбор фармакологической группы',
			items : [new Ext.form.FormPanel({
				layout : 'form',
				border : false,
				frame : true,
				labelWidth : 220,
				items : [this.PharmaGroupCombo]
			})],
			buttons : [{
				handler: function () {
					var combo = this.SelectAddPharmaGroupWindow.findById('RLS_CLSPHARMAGROUP_ID');
					var index = combo.getStore().findBy(function(rec) { return rec.get('CLSPHARMAGROUP_ID') == combo.value; });
					var record = combo.getStore().getAt(index);
					this.PharmaGroupGrid.getGrid().getStore().loadData([record.data], true);
					this.PharmaGroupGrid.ViewGridPanel.getView().refresh();
					combo.reset();
					this.SelectAddPharmaGroupWindow.hide();
				}.createDelegate(this),
				iconCls: 'save16',
				text: BTN_FRMSAVE
			}, {
				text: '-'
			}, {
				handler: function () {
					this.ownerCt.hide();
				},
				iconCls: 'cancel16',
				text: BTN_FRMCANCEL
			}],
		});

		this.SelectAddFTGGRLSWindow = new Ext.Window({
			id: 'rpnew_SelectAddFTGGRLSWindow',
			closable: false,
			width : 600,
			modal: true,
			resizable: false,
			autoHeight: false,
			closeAction :'hide',
			border : false,
			plain : false,
			title: 'Выбор фармакотерапевтической группы',
			items : [new Ext.form.FormPanel({
				layout : 'form',
				border : false,
				frame : true,
				labelWidth : 220,
				items : [this.FTGGRLSCombo]
			})],
			buttons : [{
				handler: function () {
					var combo = this.SelectAddFTGGRLSWindow.findById('RLS_FTGGRLS_ID');
					var index = combo.getStore().findBy(function(rec) { return rec.get('FTGGRLS_ID') == combo.value; });
					var record = combo.getStore().getAt(index);
					this.FTGGRLSGrid.getGrid().getStore().loadData([record.data], true);
					this.FTGGRLSGrid.ViewGridPanel.getView().refresh();
					combo.reset();
					this.SelectAddFTGGRLSWindow.hide();
				}.createDelegate(this),
				iconCls: 'save16',
				text: BTN_FRMSAVE
			}, {
				text: '-'
			}, {
				handler: function () {
					this.ownerCt.hide();
				},
				iconCls: 'cancel16',
				text: BTN_FRMCANCEL
			}],
		});

		//Содержимое вкладки "Классификация". Создаем гриды, далее панели в которых будет размещение, для Классификации АТХ, Фармакологических групп и МКБ-10
		this.AtcGrid = new sw.Promed.ViewFrame({
			autoExpandColumn: 'autoexpand',
			id: this.id + '_AtcGrid',
			autoExpandMin: 100,
			pageSize: 20,
			style: 'border: 1px solid #000;',
			actions: [
				{ name: 'action_add', handler: function(){this.SelectAddAtcWindow.show(); }.createDelegate(this) },   //вызываем окно для выбора класса АТХ
				{ name: 'action_edit', hidden: true },
				{ name: 'action_view', hidden: true },
				{ name: 'action_delete', handler: this.deleteAtc.createDelegate(this)},
				{ name: 'action_refresh', hidden: true },
				{ name: 'action_print', hidden: true }
			],
			autoLoadData: false,
			stripeRows: true,
			stringfields: [
				{ name: 'CLSATC_ID', type: 'int', hidden: true, key: true },
				{ name: 'NAME', type: 'string', header: langs('Наименование'), id: 'autoexpand'}   //sort: true, width: 200
			],
			paging: true,
			totalProperty: 'totalCount'
		});

		this.AtcPanel = new sw.Promed.Panel({
			title: langs('Классификация АТХ'),
			defaults: {
				border: false,
				labelAlign: 'right',
				collapsible: true
			},
			items: [this.AtcGrid]
		});

		this.PharmaGroupGrid = new sw.Promed.ViewFrame({
			autoExpandColumn: 'autoexpand',
			id: this.id + '_PharmaGroupGrid',
			autoExpandMin: 100,
			pageSize: 20,
			style: 'border: 1px solid #000;',
			actions: [
				{ name: 'action_add', handler: function(){this.SelectAddPharmaGroupWindow.show(); }.createDelegate(this) },
				{ name: 'action_edit', hidden: true },
				{ name: 'action_view', hidden: true },
				{ name: 'action_delete', handler: this.deletePharmaGroup.createDelegate(this)},
				{ name: 'action_refresh', hidden: true },
				{ name: 'action_print', hidden: true }
			],
			autoLoadData: false,
			stripeRows: true,
			stringfields: [
				{ name: 'CLSPHARMAGROUP_ID', type: 'int', hidden: true, key: true },                  //PHARMAACTIONS_ID
				{ name: 'NAME', type: 'string', header: langs('Наименование'), id: 'autoexpand'}
			],
			paging: true,
			totalProperty: 'totalCount'
		});

		this.PharmaGroupPanel = new sw.Promed.Panel({
			title: langs('Фармакологическая группа'),
			defaults: {
				border: false,
				labelAlign: 'right',
				collapsible: true
			},
			items: [this.PharmaGroupGrid]
		});

		this.FTGGRLSGrid = new sw.Promed.ViewFrame({
			autoExpandColumn: 'autoexpand',
			id: this.id + '_FTGGRLSGrid',
			autoExpandMin: 100,
			pageSize: 20,
			style: 'border: 1px solid #000;',
			actions: [
				{ name: 'action_add', handler: function(){this.SelectAddFTGGRLSWindow.show(); }.createDelegate(this) },
				{ name: 'action_edit', hidden: true },
				{ name: 'action_view', hidden: true },
				{ name: 'action_delete', handler: this.deleteFTGGRLS.createDelegate(this)},
				{ name: 'action_refresh', hidden: true },
				{ name: 'action_print', hidden: true }
			],
			autoLoadData: false,
			stripeRows: true,
			stringfields: [
				{ name: 'FTGGRLS_ID', type: 'int', hidden: true, key: true },
				{ name: 'NAME', type: 'string', header: langs('Наименование'), id: 'autoexpand'}
			],
			paging: true,
			totalProperty: 'totalCount'
		});

		this.FTGGRLSPanel = new sw.Promed.Panel({
			title: langs('Фармакотерапевтическая группа'),
			defaults: {
				border: false,
				labelAlign: 'right',
				collapsible: true
			},
			items: [this.FTGGRLSGrid]
		});

		this.MkbGrid = new sw.Promed.ViewFrame({
			autoExpandColumn: 'autoexpand',
			id: this.id + '_MkbGrid',
			autoExpandMin: 100,
			pageSize: 20,
			style: 'border: 1px solid #000;',
			actions: [
				{ name: 'action_add', handler: this.addMkb.createDelegate(this) },
				{ name: 'action_edit', hidden: true },
				{ name: 'action_view', hidden: true },
				{ name: 'action_delete', handler: this.deleteMkb.createDelegate(this)},
				{ name: 'action_refresh', hidden: true },
				{ name: 'action_print', hidden: true }
			],
			autoLoadData: false,
			stripeRows: true,
			stringfields: [
				{ name: 'CLSIIC_ID', type: 'int', hidden: true, key: true },
				{ name: 'NAME', type: 'string', header: langs('МКБ-10'), id: 'autoexpand' }
			],
			paging: true,
			totalProperty: 'totalCount'
		});

		this.MkbPanel = new sw.Promed.Panel({
			title: langs('Классификация МКБ-10'),
			defaults: {
				border: false,
				labelAlign: 'right',
				collapsible: true
			},
			items: [this.MkbGrid]
		});

		this.PrepTabs = new Ext.TabPanel({
			activeTab: 0,
			border: true,
			resizeTabs: true,
			region: 'center',
			enableTabScroll: true,
			minTabWidth: 120,
			tabWidth: 'auto',
			layoutOnTabChange: true,
			items:[
				{
					title: 'Основные сведения',
                    layout: 'form',
                    autoScroll: true,
					border: false,
					items: [this.MainForm]
				},
				{
					title: 'Сроки годности, условия хранения',
					layout: 'fit',
                    autoScroll: true,
					border: false,
					items: [this.PrepDataForm2]
				},
				{
					title: 'Описание и дополнительная информация',
					layout: 'form',
                    autoScroll: true,
					border: false,
					id: 'ThirdTab',
					items: [this.PrepDataForm, this.ImagePanel, this.AutorPanel]
				},
				{
					title: 'Классификация',
					layout: 'form',
					autoScroll: true,
					border: false,
					items: [this.AtcPanel, this.PharmaGroupPanel, this.FTGGRLSPanel, this.MkbPanel]
				}
			],
			listeners: {
				'tabchange': function(tab, panel) {
					tab.doLayout();
					if(panel.id == 'ThirdTab'){
						this.PrepDataForm.setHeight(1000);
					}
				}.createDelegate(this)
			}
		});
		
		Ext.apply(this,	{
			items: [this.PrepTabs]
		});
		sw.Promed.swRlsPrepNewEditWindow.superclass.initComponent.apply(this, arguments);
	}
});