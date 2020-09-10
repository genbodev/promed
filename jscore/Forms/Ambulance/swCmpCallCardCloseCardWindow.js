/**
* swCmpCallCardCloseCardWindow - окно закрытия карты вызова
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Ambulance
* @access       public
* @copyright    Copyright (c) 2010 Swan Ltd.
* @author		Popkov
* @version      апрель.2012
*/
/*NO PARSE JSON*/

sw.Promed.swCmpCallCardCloseCardWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swCmpCallCardCloseCardWindow',
	objectSrc: '/jscore/Forms/Ambulance/swCmpCallCardCloseCardWindow.js',

	action: null,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	collapsible: false,

	save_form: function( base_form, params_out){

		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет закрытие карты вызова..."});
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
						sw.swMsg.alert(lang['oshibka'], lang['pri_zakryitii_proizoshli_oshibki_[tip_oshibki_1]']);
					}
				}
			}.createDelegate(this),
			params: params_out,
			success: function(result_form, action) {
				this.formStatus = 'edit';
				loadMask.hide();

				if ( action.result ) {
					if ( action.result.CmpCallCard_id > 0 ) {
//						base_form.findField('CmpCallCard_id').setValue(action.result.CmpCallCard_id);
//						var data = new Object();
//						var index;
//						var person_fio = '';
//						var record;
//
//						var CmpReason_id = base_form.findField('CmpReason_id').getValue();
//						var CmpCallType_id = base_form.findField('CmpCallType_id').getValue();
//
//						var CmpReason_Name = '';
//						var CmpCallType_Name = '';
//
//						index = base_form.findField('CmpReason_id').getStore().findBy(function(rec) {
//							if ( rec.get('CmpReason_id') == CmpReason_id ) {
//								return true;
//							}
//							else {
//								return false;
//							}
//						});
//						record = base_form.findField('CmpReason_id').getStore().getAt(index);
//
//						if ( record ) {
//							CmpReason_Name = record.get('CmpReason_Name');
//						}
//
//						if ( base_form.findField('Person_Surname').getValue() ) {
//							person_fio = person_fio + base_form.findField('Person_Surname').getValue();
//						}
//						if ( base_form.findField('Person_Firname').getValue() ) {
//							person_fio = person_fio + ' ' + base_form.findField('Person_Firname').getValue();
//						}
//						if ( base_form.findField('Person_Secname').getValue() ) {
//							person_fio = person_fio + ' ' + base_form.findField('Person_Secname').getValue();
//						}
//						data.cmpCallCardData = {
//							'accessType': 'edit'
//							,'CmpCallCard_id': base_form.findField('CmpCallCard_id').getValue()
//							,'CmpCallCard_prmDate': base_form.findField('CmpCallCard_prmDate').getValue()
//							,'Person_id': base_form.findField('Person_id').getValue()
//							,'Person_Surname': base_form.findField('Person_Surname').getValue()
//							,'Person_Firname': base_form.findField('Person_Firname').getValue()
//							,'Person_Secname': base_form.findField('Person_Secname').getValue()
//							,'Person_Birthday': (typeof base_form.findField('Person_Birthday').getValue() == 'object' ? base_form.findField('Person_Birthday').getValue() : getValidDT(base_form.findField('Person_Birthday').getValue(), ''))
//							,'Person_FIO': person_fio
//							,'CmpReason_Name': CmpReason_Name
//						};
//
//						this.callback(data);
//						this.hide();
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

	
	doSave: function() {	
		
		if ( this.formStatus == 'save' ) {
			return false;
		}

		this.formStatus = 'save';

		var base_form = this.FormPanel.getForm();

		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					this.formStatus = 'edit';
					this.FormPanel.getFirstInvalidEl().focus(false);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		var params = new Object();		

		params.CmpCallCard_id = base_form.findField('CmpCallCard_id').getValue();
		
		if ( typeof base_form.findField('Person_Birthday').getValue() == 'object' ) {
			params.Person_Birthday = Ext.util.Format.date(base_form.findField('Person_Birthday').getValue(), 'd.m.Y');
		}
		else if ( typeof base_form.findField('Person_Birthday').getValue() == 'string' ) {
			params.Person_Birthday = base_form.findField('Person_Birthday').getValue();
		}
		if (base_form.findField('CmpCallCard_Numv').disabled) { 
			params.CmpCallCard_Numv = base_form.findField('CmpCallCard_Numv').getValue();
		}
		//if (base_form.findField('CmpCallCard_Ngod').disabled) { 
		//	params.CmpCallCard_Ngod = base_form.findField('CmpCallCard_Ngod').getValue();
		//}
		if ( base_form.findField('CmpCallCard_prmDate').disabled ) {
			params.CmpCallCard_prmDate = Ext.util.Format.date(base_form.findField('CmpCallCard_prmDate').getValue(), 'd.m.Y');
		}
		if ( base_form.findField('CmpCallCard_prmTime').disabled ) {
			params.CmpCallCard_prmTime =base_form.findField('CmpCallCard_prmTime').getValue();
		}
		if ( base_form.findField('Person_Age').disabled ) {
			params.Person_Age = base_form.findField('Person_Age').getValue();
		}
		if ( base_form.findField('Person_Firname').disabled ) {
			params.Person_Firname = base_form.findField('Person_Firname').getValue();
		}
		if ( base_form.findField('Person_Secname').disabled ) {
			params.Person_Secname = base_form.findField('Person_Secname').getValue();
		}
		if ( base_form.findField('Person_Surname').disabled ) {
			params.Person_Surname = base_form.findField('Person_Surname').getValue();
		}
		
		if ( base_form.findField('Sex_id').disabled ) {
			params.Sex_id = base_form.findField('Sex_id').getValue();
		}
		
		if ( base_form.findField('Polis_Ser').disabled ) {
			params.Polis_Ser = base_form.findField('Polis_Ser').getValue();
		}
		
		if ( base_form.findField('Polis_Num').disabled ) {
			params.Polis_Num = base_form.findField('Polis_Num').getValue();
		}
		
		if ( base_form.findField('Polis_EdNum').disabled ) {
			params.Polis_EdNum = base_form.findField('Polis_EdNum').getValue();
		}
		if ( base_form.findField('CmpCallerType_id').disabled ) {
			params.CmpCallerType_id = base_form.findField('CmpCallerType_id').getValue();
		}
		
		
		
		if ( base_form.findField('KLAreaStat_idEdit').disabled ) {
			params.KLAreaStat_idEdit = base_form.findField('KLAreaStat_idEdit').getValue();
		}
		if ( base_form.findField('KLSubRgn_id').disabled ) {
			params.KLSubRgn_id = base_form.findField('KLSubRgn_id').getValue();
		}
		if ( base_form.findField('KLCity_id').disabled ) {
			params.KLCity_id = base_form.findField('KLCity_id').getValue();
		}
		if ( base_form.findField('KLTown_id').disabled ) {
			params.KLTown_id = base_form.findField('KLTown_id').getValue();
		}
		if ( base_form.findField('KLStreet_id').disabled ) {
			params.KLStreet_id = base_form.findField('KLStreet_id').getValue();
		}
		if ( base_form.findField('CmpCallCard_Dom').disabled ) {
			params.CmpCallCard_Dom = base_form.findField('CmpCallCard_Dom').getValue();
		}
		if ( base_form.findField('CmpCallCard_Kvar').disabled ) {
			params.CmpCallCard_Kvar = base_form.findField('CmpCallCard_Kvar').getValue();
		}
		if ( base_form.findField('CmpCallCard_Podz').disabled ) {
			params.CmpCallCard_Podz = base_form.findField('CmpCallCard_Podz').getValue();
		}
		if ( base_form.findField('CmpCallCard_Etaj').disabled ) {
			params.CmpCallCard_Etaj = base_form.findField('CmpCallCard_Etaj').getValue();
		}
		if ( base_form.findField('CmpCallCard_Kodp').disabled ) {
			params.CmpCallCard_Kodp = base_form.findField('CmpCallCard_Kodp').getValue();
		}
		
		if ( base_form.findField('CmpCallType_id').disabled ) {
			params.CmpCallType_id = base_form.findField('CmpCallType_id').getValue();
		}
		
		
		
		this.save_form(base_form, params) ;
		
	},
	draggable: true,
	enableEdit: function(enable) {
		var base_form = this.FormPanel.getForm();
		var form_fields = new Array(
			//'CmpCallCard_City',
			//'CmpCallCard_Comm',
			//'CmpCallCard_Dom',
			//'CmpCallCard_Etaj',
			//'CmpCallCard_Kodp',
			//'CmpCallCard_Ktov',
			//'CmpCallCard_Kvar',
//			'CmpCallCard_Ngod',
//			'CmpCallCard_Numv',
			//'CmpCallCard_Podz',
			//'CmpCallCard_Telf',
			//'CmpCallCard_Ulic',
			//'CmpReason_id',
			//'CmpCallType_id',
			//'Person_Age',
			//'Person_Firname',
			//'Person_Secname',
			//'Person_Surname',
			//'Person_Birthday',
			//'Sex_id',
			//'Polis_Ser',
			//'Polis_Num',
			//'Polis_EdNum',
			//'KLAreaStat_idEdit',
			//'KLSubRgn_id',
			//'KLCity_id',
			//'KLTown_id',
			//'KLStreet_id'
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
			// this.findById('CCCSEF_PersonIdentBtn').show();
			this.findById('CCCSEF_PersonResetBtn').show();
			this.findById('CCCSEF_PersonSearchBtn').show();

			this.buttons[0].show();
		}
		else {
			// this.findById('CCCSEF_PersonIdentBtn').hide();
			this.findById('CCCSEF_PersonResetBtn').hide();
			this.findById('CCCSEF_PersonSearchBtn').hide();

			this.buttons[0].hide();
		}
	},
	formStatus: 'edit',
	getCmpCallCardNumber: function() {
		var base_form = this.FormPanel.getForm();

		this.getLoadMask().show();

		Ext.Ajax.request({
			callback: function(opt, success, response) {
				this.getLoadMask().hide();

				if ( success ) {
					var response_obj = Ext.util.JSON.decode(response.responseText);
					log(response_obj);
					base_form.findField('CmpCallCard_Ngod').setValue(response_obj[0].CmpCallCard_Ngod);
					base_form.findField('CmpCallCard_Numv').setValue(response_obj[0].CmpCallCard_Numv);

					base_form.findField('CmpCallCard_Numv').focus(true);
				}
				else {
					sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_opredelenii_nomera_vyizova'], function() {base_form.findField('CmpCallCard_Numv').focus(true);}.createDelegate(this) );
				}
			}.createDelegate(this),
			url: '/?c=CmpCallCard&m=getCmpCallCardNumber'
		});
	},
	getLoadMask: function() {
		if ( !this.loadMask ) {
			this.loadMask = new Ext.LoadMask(this.getEl(), {msg: lang['podojdite']});
		}

		return this.loadMask;
	},
	height: 550,
	id: 'CmpCallCardCloseCardWindow',
	
	reloadAllFields: function(data) {
		var frm = this.FormPanel.getForm(),
			rc = null;
		frm.findField('KLAreaStat_idEdit').getStore().each(function(r) {
			if( r.get('KLSubRGN_id') > 0 ) {
				if( data.KLSubRegion_id > 0 && data.KLSubRegion_id == r.get('KLSubRGN_id') ) {
					rc = r;
				}
			}
			else if( r.get('KLCity_id') > 0 ) {
				if( data.KLCity_id > 0 && data.KLCity_id == r.get('KLCity_id') ) {
					rc = r;
				}
			}
		});
		if( rc != null ) {
			frm.findField('KLAreaStat_idEdit').setValue(rc.get('KLAreaStat_id'));
			frm.findField('KLAreaStat_idEdit').fireEvent('beforeselect', frm.findField('KLAreaStat_idEdit'), rc);
		}
		
		frm.findField('KLTown_id').getStore().load({
			params: {city_id: data.KLSubRegion_id > 0 ? data.KLSubRegion_id : data.KLCity_id},
			callback: function() {
				this.each(function(r) {
					if( data.KLTown_id && data.KLTown_id == r.get('Town_id') ) {
						frm.findField('KLTown_id').setValue(r.get('Town_id'));
						frm.findField('KLTown_id').fireEvent('beforeselect', frm.findField('KLTown_id'), r);
					}
				});
			}
		});
	},
	
	
	deleteProc: function(event) {
		var proc_grid = this.findById('ProcGrid').getGrid();

		if ( !proc_grid.getSelectionModel().getSelected() || !proc_grid.getSelectionModel().getSelected().get('Usluga_id') ) {
			return false;
		}

		proc_grid.getStore().remove(proc_grid.getSelectionModel().getSelected());

		if ( proc_grid.getStore().getCount() > 0 ) {
			proc_grid.getSelectionModel().selectFirstRow();
		}
	},
	
	
	openProcedureEditWindow: function(action) {			
		if ( typeof action != 'string' || !(action.inlist([ 'add', 'edit' ])) ) {
			return false;
		}

		if ( getWnd('swProcedureEditWindow').isVisible() ) {
			sw.swMsg.alert(lang['oshibka'], lang['okno_redaktirovaniya_manipulyatsii_uje_otkryito']);
			return false;
		}

		var base_form = this.FormPanel.getForm();
		var grid = this.findById('ProcGrid').getGrid();
		var params = new Object();

		params.action = action;
		params.callback = function(data) {
			if ( typeof data != 'object' || typeof data.evnProcedureData != 'object' ) {
				return false;
			}

			var record = grid.getStore().getById(data.evnProcedureData.Usluga_id);
			
			if ( record ) {
				var grid_fields = new Array();
				grid.getStore().fields.eachKey(function(key, item) {
					grid_fields.push(key);
				});

				for ( i = 0; i < grid_fields.length; i++ ) {
					record.set(grid_fields[i], data.evnProcedureData[grid_fields[i]]);
				}

				record.commit();
			}
			else {
				
				if ( grid.getStore().getCount() == 1 && !grid.getStore().getAt(0).get('Usluga_id') ) {
					grid.getStore().removeAll();
				}
				data.evnProcedureData.Usluga_id = -swGenTempId(grid.getStore());
				grid.getStore().loadData([ data.evnProcedureData ], true);
			}
		}.createDelegate(this);
		params.formParams = new Object();

		if ( action == 'add' ) {
			params.onHide = function() {
				if ( grid.getStore().getCount() > 0 ) {
					grid.getView().focusRow(0);
				}
			};
		}
		else {
			if ( !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('Usluga_id') ) {
				return false;
			}

			var record = grid.getSelectionModel().getSelected();

			params.formParams = record.data;
			params.onHide = function() {
				grid.getView().focusRow(grid.getStore().indexOf(record));
			};
		}
		getWnd('swProcedureEditWindow').show(params);

//		var win,title,formParams = new Object(),params = new Object(),frm = this.FormPanel.getForm();		
//		win = 'swProcedureEditWindow';
//		title = 'Выбор процедры';
//		formParams.EvnPrescrTreat_id = 0;		
//		formParams.Server_id = 0;
//		formParams.EvnPrescrTreat_pid = 0;	
//		formParams.PersonEvn_id = 0;
//		formParams.EvnPrescrTreat_setDate = frm.findField('CmpCallCard_prmDate').getValue();
//		formParams.EvnPrescrTreat_setTime = frm.findField('CmpCallCard_prmTime').getValue();
//		formParams.PrescriptionStatusType_id = 1;
//		params.action = 'add';
//		params.callback = function(data) {			
//			this.loadNodeViewForm(this.Tree.getNodeById(this.node.id));	
//		}.createDelegate(this);
//		params.formParams = formParams;
//		getWnd('swProcedureEditWindow').show(params);		
	},
	
	
	deleteDrug: function() {
		var drug_grid = this.findById('EPREF_DrugGrid').getGrid();

		if ( !drug_grid.getSelectionModel().getSelected() || !drug_grid.getSelectionModel().getSelected().get('EvnPrescrTreatDrug_id') ) {
			return false;
		}

		drug_grid.getStore().remove(drug_grid.getSelectionModel().getSelected());

		if ( drug_grid.getStore().getCount() > 0 ) {
			drug_grid.getSelectionModel().selectFirstRow();
		}
	},
	
	openEvnPrescrTreatDrugEditWindow: function(action) {
		if ( typeof action != 'string' || !(action.inlist([ 'add', 'edit' ])) ) {
			return false;
		}

		if ( getWnd('swEvnPrescrTreatDrugEditWindow').isVisible() ) {
			sw.swMsg.alert(lang['oshibka'], lang['okno_redaktirovaniya_naznachaemogo_medikamenta_uje_otkryito']);
			return false;
		}

		var base_form = this.FormPanel.getForm();
		var grid = this.findById('EPREF_DrugGrid').getGrid();
		var params = new Object();

		params.action = action;
		params.callback = function(data) {
			if ( typeof data != 'object' || typeof data.evnPrescrTreatDrugData != 'object' ) {
				return false;
			}

			var record = grid.getStore().getById(data.evnPrescrTreatDrugData.EvnPrescrTreatDrug_id);

			if ( record ) {
				var grid_fields = new Array();
				grid.getStore().fields.eachKey(function(key, item) {
					grid_fields.push(key);
				});

				for ( i = 0; i < grid_fields.length; i++ ) {
					record.set(grid_fields[i], data.evnPrescrTreatDrugData[grid_fields[i]]);
				}

				record.commit();
			}
			else {				
				if ( grid.getStore().getCount() == 1 && !grid.getStore().getAt(0).get('EvnPrescrTreatDrug_id') ) {					
					grid.getStore().removeAll();
				}
				data.evnPrescrTreatDrugData.EvnPrescrTreatDrug_id = -swGenTempId(grid.getStore());
				grid.getStore().loadData([ data.evnPrescrTreatDrugData ], true);
			}
		}.createDelegate(this);
		params.formParams = new Object();

		if ( action == 'add' ) {
			params.onHide = function() {
				if ( grid.getStore().getCount() > 0 ) {
					grid.getView().focusRow(0);
				}
			};
		}
		else {
			if ( !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('EvnPrescrTreatDrug_id') ) {
				return false;
			}

			var record = grid.getSelectionModel().getSelected();

			params.formParams = record.data;
			params.onHide = function() {
				grid.getView().focusRow(grid.getStore().indexOf(record));
			};
		}

		getWnd('swEvnPrescrTreatDrugEditWindow').show(params);
	},
	
	setStatusIdentification: function(e) {
		var statuspanel = this.FormPanel.find('name', 'status_panel')[0],
			statusfield = statuspanel.find('name', 'status_field')[0],
			bf = this.FormPanel.getForm();
		
		if(
			(
				( bf.findField('Person_Surname').getValue() != '' && bf.findField('Person_Firname').getValue() != '' && bf.findField('Person_Secname').getValue() != '' )
				&& ( bf.findField('Person_Birthday').getValue() != '' || bf.findField('Person_Age').getValue() != '' )
			)
			||
			(
				bf.findField('Polis_Ser').getValue() != '' && bf.findField('Polis_Num').getValue() != ''
			)
			||
			bf.findField('Polis_EdNum').getValue() != ''
		) {
			if( !statuspanel.isVisible() ) {
				statuspanel.setVisible(true);
			}
			var src = '/extjs/resources/images/default/grid/loading.gif';
			statusfield.getEl().update('<div style="margin-left: 50px; width: 200px; height: 16px; background-image: url('+src+'); background-repeat: no-repeat">Идентификация пациента...</div>');
			Ext.Ajax.request({
				//url: '/?c=CmpCallCard&m=identifiPerson',
				url: '/?c=Person&m=getPersonSearchGrid',
				params: {
					/*
					Person_Surname: bf.findField('Person_Surname').getValue()
					,Person_Firname: bf.findField('Person_Firname').getValue()
					,Person_Secname: bf.findField('Person_Secname').getValue()
					,Person_Birthday: bf.findField('Person_Birthday').getValue() != '' ? bf.findField('Person_Birthday').getValue().format('d.m.Y') : null
					,Person_Age: bf.findField('Person_Age').getValue()
					,Polis_Ser: bf.findField('Polis_Ser').getValue()
					,Polis_Num: bf.findField('Polis_Num').getValue()
					,Sex_id: bf.findField('Sex_id').getValue()
					*/
					PersonFirName_FirName: bf.findField('Person_Firname').getValue()
					,PersonSecName_SecName: bf.findField('Person_Secname').getValue()
					,PersonSurName_SurName: bf.findField('Person_Surname').getValue()
					,PersonAge_AgeFrom: bf.findField('Person_Age').getValue()
					,PersonAge_AgeTo: bf.findField('Person_Age').getValue()
					,PersonBirthDay_BirthDay: bf.findField('Person_Birthday').getValue() != '' ? bf.findField('Person_Birthday').getValue().format('d.m.Y') : null
					,Polis_Num: bf.findField('Polis_Num').getValue()
					,Polis_Ser: bf.findField('Polis_Ser').getValue()
					,limit: 100
					,searchMode: 'all'
					,start: 0
					,Sex_id: bf.findField('Sex_id').getValue()
					,Polis_EdNum: bf.findField('Polis_EdNum').getValue()
				},
				callback: function(o, s, r) {
					if( s ) {
						var resp = Ext.util.JSON.decode(r.responseText),
							msg = '';
						if( resp.Error_Msg ) {
							statusfield.getEl().update(resp.Error_Msg);
							return false;
						} else {
							if( resp.totalCount == 1 ) {
								msg += lang['patsient_identifitsirovan'];
								
								var data = resp.data[0];
								data['Person_Firname'] = data['PersonFirName_FirName'];
								data['Person_Secname'] = data['PersonSecName_SecName'];
								data['Person_Surname'] = data['PersonSurName_SurName'];
								data['Person_Birthday'] = data['PersonBirthDay_BirthDay'];
								
								bf.setValues(data);
							} else if( resp.totalCount > 1 ) {
								msg += lang['naydeno'] + resp.totalCount + lang['patsientov_najmite_knopku_poisk_dlya_identifikatsii_patsienta'];
							} else {
								msg += lang['patsient_ne_nayden'];
							}
							statusfield.getEl().update(msg);
						}
					}
				}.createDelegate(this)
			});
		} else {
			statusfield.getEl().update('');
			statuspanel.setVisible(false);
		}
	},
	
	initComponent: function() {
				
		this.FormPanel = new Ext.form.FormPanel({
			autoScroll: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px',
			border: false,
			frame: true,
			id: 'CmpCallCardEditForm',
			labelAlign: 'right',
			labelWidth: 220,
			reader: new Ext.data.JsonReader({
				success: Ext.amptyFn
			},  [
				{name: 'accessType'},
				{name: 'CmpCallCard_id'},
				{name: 'CmpArea_gid'},
				{name: 'CmpArea_id'},
				{name: 'CmpArea_pid'},
				{name: 'CmpCallCard_City'},
				{name: 'CmpCallCard_Comm'},
				{name: 'CmpCallCard_D201'},
				{name: 'CmpCallCard_Dlit'},
				{name: 'CmpCallCard_Dokt'},
				{name: 'CmpCallCard_Dom'},
				{name: 'CmpCallCard_Dsp1'},
				{name: 'CmpCallCard_Dsp2'},
				{name: 'CmpCallCard_Dsp3'},
				{name: 'CmpCallCard_Dspp'},
				{name: 'CmpCallCard_Etaj'},
				{name: 'CmpCallCard_Expo'},
				{name: 'CmpCallCard_IsAlco'},
				{name: 'CmpCallCard_IsPoli'},
				{name: 'CmpCallCard_Izv1'},
				{name: 'CmpCallCard_Kakp'},
				{name: 'CmpCallCard_Kilo'},
				{name: 'CmpCallCard_Kodp'},
				{name: 'CmpCallerType_id'},
				{name: 'CmpCallCard_Kvar'},
				{name: 'CmpCallCard_Line'},
				{name: 'CmpCallCard_Ncar'},
				{name: 'CmpCallCard_Ngod'},
				{name: 'CmpCallCard_Numb'},
				{name: 'CmpCallCard_Numv'},
				{name: 'CmpCallCard_PCity'},
				{name: 'CmpCallCard_PDom'},
				{name: 'CmpCallCard_PKvar'},
				{name: 'CmpCallCard_Podz'},
				{name: 'CmpCallCard_Prdl'},
				{name: 'CmpCallCard_prmDate'},
				{name: 'CmpCallCard_prmTime'},
				{name: 'CmpCallCard_Prty'},
				{name: 'CmpCallCard_Przd'},
				{name: 'CmpCallCard_PUlic'},
				{name: 'CmpCallCard_RCod'},
				{name: 'CmpCallCard_Sect'},
				{name: 'CmpCallCard_Smpb'},
				{name: 'CmpCallCard_Smpp'},
				{name: 'CmpCallCard_Smpt'},
				{name: 'CmpCallCard_Stan'},
				{name: 'CmpCallCard_Stbb'},
				{name: 'CmpCallCard_Stbr'},
				{name: 'CmpCallCard_Tab2'},
				{name: 'CmpCallCard_Tab3'},
				{name: 'CmpCallCard_Tab4'},
				{name: 'CmpCallCard_TabN'},
				{name: 'CmpCallCard_Telf'},
				{name: 'CmpCallCard_Tgsp'},
				{name: 'CmpCallCard_Tisp'},
				{name: 'CmpCallCard_Tiz1'},
				{name: 'CmpCallCard_Tper'},
				{name: 'CmpCallCard_Tsta'},
				{name: 'CmpCallCard_Tvzv'},
				{name: 'CmpCallCard_Ulic'},
				{name: 'CmpCallCard_Vr51'},
				
				{name: 'CmpCallCard_Inf1'},
				{name: 'CmpCallCard_Inf2'},
				{name: 'CmpCallCard_Inf3'},
				{name: 'CmpCallCard_Inf4'},				
				{name: 'CmpCallCard_Inf5'},
				{name: 'CmpCallCard_Inf6'},
				
				{name: 'CmpCallCard_Vyez'},
				{name: 'CmpCallType_id'},
				{name: 'CmpDiag_aid'},
				{name: 'CmpDiag_oid'},
				{name: 'CmpLpu_id'},
				{name: 'CmpPlace_id'},
				{name: 'CmpProfile_bid'},
				{name: 'CmpProfile_cid'},
				{name: 'CmpReason_id'},
				{name: 'CmpResult_id'},
				{name: 'ResultDeseaseType_id'},
				{name: 'CmpTalon_id'},
				{name: 'CmpTrauma_id'},
				{name: 'Diag_sid'},
				{name: 'Diag_uid'},
				{name: 'Lpu_oid'},
				{name: 'Person_Age'},
				{name: 'Person_Birthday'},
				{name: 'Person_Firname'},
				{name: 'Person_id'},
				{name: 'Polis_Ser'},
				{name: 'Polis_Num'},
				{name: 'Polis_EdNum'},
				{name: 'Person_Secname'},
				{name: 'Person_Surname'},
				{name: 'Sex_id'},
				{name: 'Lpu_id'}, 
				{name: 'ARMType'},
				{name: 'KLRgn_id'},
				{name: 'KLSubRgn_id'},
				{name: 'KLCity_id'},
				{name: 'KLTown_id'},
				{name: 'KLStreet_id'},
				//{name: 'ResultClass_id'}
			]),
			region: 'center',
			url: '/?c=CmpCallCard&m=saveCmpCallCloseCard',

			items: [{
				name: 'accessType',
				value: '',
				xtype: 'hidden'
			}, {
				name: 'CmpCallCard_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'Person_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'CmpCallCard_PCity',
				value: '',
				xtype: 'hidden'
			}, {
				name: 'CmpCallCard_PUlic',
				value: '',
				xtype: 'hidden'
			}, {
				name: 'CmpCallCard_PDom',
				value: '',
				xtype: 'hidden'
			}, {
				name: 'CmpCallCard_PKvar',
				value: '',
				xtype: 'hidden'
			}, {
				name: 'CmpArea_gid',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'CmpLpu_id',
				value: 0,
				xtype: 'hidden'
			},  {
				name: 'CmpTrauma_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'Diag_sid',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'CmpTalon_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'CmpCallCard_Expo',
				value: '',
				xtype: 'hidden'
			}, {
				name: 'CmpCallCard_IsPoli',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'CmpCallCard_Smpt',
				value: '',
				xtype: 'hidden'
			}, {
				name: 'CmpCallCard_Line',
				value: '',
				xtype: 'hidden'
			}, {
				name: 'CmpCallCard_Prty',
				value: '',
				xtype: 'hidden'
			}, {
				name: 'CmpCallCard_Sect',
				value: '',
				xtype: 'hidden'
			}, {
				name: 'CmpProfile_cid',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'CmpCallCard_Stan',
				value: '',
				xtype: 'hidden'
			},  {
				name: 'CmpPlace_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'CmpCallCard_Smpp',
				value: '',
				xtype: 'hidden'
			}, {
				name: 'CmpCallCard_Vr51',
				value: '',
				xtype: 'hidden'
			}, {
				name: 'CmpCallCard_D201',
				value: '',
				xtype: 'hidden'
			}, {
				name: 'CmpCallCard_Dlit',
				value: '',
				xtype: 'hidden'
			}, {
				name: 'CmpCallCard_Prdl',
				value: '',
				xtype: 'hidden'
			}, {
				name: 'CmpCallCard_Tiz1',
				value: '',
				xtype: 'hidden'
			}, {
				name: 'CmpCallCard_Izv1',
				value: '',
				xtype: 'hidden'
			},  {
				name: 'CmpCallCard_Stbr',
				value: '',
				xtype: 'hidden'
			}, {
				name: 'CmpCallCard_Ncar',
				value: '',
				xtype: 'hidden'
			}, {
				name: 'CmpCallCard_Dokt',
				value: '',
				xtype: 'hidden'
			}, {
				name: 'ARMType',
				value: '',
				xtype: 'hidden'
			}, {
				name: 'CmpReason_id',
				value: '',
				xtype: 'hidden'						
			}, {
				name: 'CmpCallCard_Telf',
				value: '',
				xtype: 'hidden'			
			}, {
				name: 'CmpCallCard_Comm',
				value: '',
				xtype: 'hidden'			
			},
			/*{
				disabled: true,
				name: 'Person_Birthday',
				value: '',
				xtype: 'hidden'
			}, */
			
			/*
			{
				border: false,
				layout: 'column',
				items: [{
					border: false,
					layout: 'form',
					items: [{
						allowBlank: false,
						disabled: true,
						fieldLabel: lang['data_vyizova'],
						format: 'd.m.Y',
						name: 'CmpCallCard_prmDate',
						plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
						selectOnFocus: true,
						// tabIndex: TABINDEX_EVPLEF + 1,
						width: 100,
						xtype: 'swdatefield'
					}, {
						disabledClass: 'field-disabled',
						fieldLabel: lang['№_vyizova_za_den'],
						name: 'CmpCallCard_Numv',
						// tabIndex: TABINDEX_PEF + 6,
						width: 100,
						xtype: 'textfield',
						disabled: true
					}]
				}, {
					border: false,
					labelWidth: 120,
					layout: 'form',
					items: [{
						disabled: true,
						fieldLabel: lang['vremya'],
						name: 'CmpCallCard_prmTime',
						plugins: [ new Ext.ux.InputTextMask('99:99', true) ],					
						validateOnBlur: false,
						width: 60,
						xtype: 'swtimefield'
					}, {
						disabledClass: 'field-disabled',
						fieldLabel: lang['№_vyizova_za_god'],
						name: 'CmpCallCard_Ngod',					
						width: 100,
						xtype: 'textfield',
						disabled: true
					}]
				}]
			}, 
			*/
			{
				border: false,
				layout: 'column',
				items:[
				{
					border: false,
					layout: 'form',
					items:[
						{
						autoHeight: true,
						style: 'padding: 0; padding-top: 5px; margin-bottom: 5px;',
						title: lang['pasportnyie_dannyie_i_mesto_vyizova'],
						xtype: 'fieldset',				
						labelAlign: 'right',
						//width: '49%',
						items: [{
							border: false,
							layout: 'column',
							style: 'padding: 0px;',
							width: 1000,					
							items: [{
								border: false,
								layout: 'form',
								width:500,						
								style: 'padding: 0px',
								items: [{
									disabledClass: 'field-disabled',
									disabled: true,
									fieldLabel: lang['familiya'],							
									name: 'Person_Surname',							
									toUpperCase: true,
									width: 180,
									xtype: 'textfieldpmw'
								}, {
									disabled: true,
									disabledClass: 'field-disabled',
									fieldLabel: lang['imya'],							
									name: 'Person_Firname',												
									toUpperCase: true,
									width: 180,
									xtype: 'textfieldpmw'
								}, {
									disabled: true,
									disabledClass: 'field-disabled',
									fieldLabel: lang['otchestvo'],
									listeners: {
										change: this.setStatusIdentification.createDelegate(this)
									},
									name: 'Person_Secname',						
									toUpperCase: true,
									width: 180,
									xtype: 'textfieldpmw'
								}, {
									disabled: true,
									name: 'Person_Birthday',
									maxValue: (new Date()),
									plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],							
									fieldLabel: lang['data_rojdeniya'],
									xtype: 'swdatefield'
								}, {
									disabled: true,
									allowDecimals: false,
									allowNegative: false,
									disabledClass: 'field-disabled',
									fieldLabel: lang['vozrast'],							
									name: 'Person_Age',							
									toUpperCase: true,
									width: 180,
									xtype: 'numberfield'
								}, {
									disabled: true,
									comboSubject: 'Sex',
									disabledClass: 'field-disabled',
									fieldLabel: lang['pol'],							
									hiddenName: 'Sex_id',						
									width: 130,
									xtype: 'swcommonsprcombo'
								}, {
									disabled: true,							
									xtype: 'textfield',
									width: 180,							
									name: 'Polis_Ser',
									fieldLabel: lang['seriya_polisa']
								}, {
									disabled: true,
									xtype: 'numberfield',
									width: 180,							
									name: 'Polis_Num',
									fieldLabel: lang['nomer_polisa']
								}, {
									disabled: true,
									xtype: 'numberfield',
									width: 180,							
									name: 'Polis_EdNum',
									fieldLabel: lang['edinyiy_nomer']
								}, {
									xtype: 'swcommonsprcombo',
									fieldLabel: lang['kto_vyizyivaet'],
									comboSubject: 'CmpCallerType',
									hiddenName: 'CmpCallerType_id',
									displayField: 'CmpCallerType_Name',
									disabledClass: 'field-disabled',
									width: 350
								}]
							}, {
								border: false,
								layout: 'form',
								style: 'padding-left: 10px;',
								width: 500,
								items: [{
									disabled: true,
									enableKeyEvents: true,
									hiddenName: 'KLAreaStat_idEdit',										
									width: 180,
									xtype: 'swklareastatcombo'
								}, {
									disabled: true,
									hiddenName: 'KLSubRgn_id',									
									width: 180,
									xtype: 'swsubrgncombo'
								}, {
									disabled: true,
									hiddenName: 'KLCity_id',								
									width: 180,
									xtype: 'swcitycombo'
								}, {
									disabled: true,
									enableKeyEvents: true,
									hiddenName: 'KLTown_id',
									width: 250,
									xtype: 'swtowncombo'
								}, {
									disabled: true,
									xtype: 'swstreetcombo',
									fieldLabel: lang['ulitsa'],					
									hiddenName: 'KLStreet_id',
									width: 250,
									editable: true
								}, {
									disabled: true,
									disabledClass: 'field-disabled',
									fieldLabel: lang['dom'],
									name: 'CmpCallCard_Dom',					
									width: 100,
									xtype: 'textfield'
								}, {
									disabled: true,
									disabledClass: 'field-disabled',
									fieldLabel: lang['kvartira'],
									maxLength: 5,
									autoCreate: {tag: "input", size:14, maxLength: "5", autocomplete: "off"},
									//maskRe: /^([а-яА-Я0-9]{1,5})$/,
									name: 'CmpCallCard_Kvar',					
									width: 100,
									xtype: 'textfieldpmw'
								}, {
									disabled: true,
									disabledClass: 'field-disabled',
									fieldLabel: lang['podyezd'],
									name: 'CmpCallCard_Podz',					
									width: 100,
									xtype: 'textfield'
								}, {
									disabled: true,
									disabledClass: 'field-disabled',
									fieldLabel: lang['etaj'],
									name: 'CmpCallCard_Etaj',					
									width: 100,
									xtype: 'textfield'
								}, {
									disabled: true,
									disabledClass: 'field-disabled',
									fieldLabel: lang['kod_zamka_v_podyezde_domofon'],
									name: 'CmpCallCard_Kodp',			
									width: 100,
									xtype: 'textfield'
								}]
							}, {
								border: false,
								width: 100,
								layout: 'form',
								items: [
									{
										xtype: 'panel',
										frame: true,
										border: true,
										hidden: true,
										name: 'status_panel',
										style: 'margin-left: 5px; margin-bottom: 5px;',
										bodyStyle: 'padding: 3px;',
										items: [{
											html: '',
											style: 'text-align: center;',
											name: 'status_field'
										}]
									}
								]
							}]
						}]
					}, {
						autoHeight: true,
						style: 'padding: 0; padding-top: 5px; margin-bottom: 5px;',
						title: lang['rezultat_vyizova'],
						xtype: 'fieldset',				
						labelAlign: 'right',				
						items: [{
							border: false,
							layout: 'column',					
							width: 1000,
							items: [
							{
								border: false,
								layout: 'form',
								width: 500,
								items: [
//								{
//									fieldLabel: 'Повод',									
//									hiddenName: 'CmpReason_id',
//									width: 250,
//									disabled: true,
//									valueField: 'CmpReason_id',
//									displayField: 'CmpReason_Name',
//									xtype: 'swbaselocalcombo'
//								}, 
								{
									enableKeyEvents: true,
									//hiddenName: 'ResultClass_id',
									hiddenName: 'CmpResult_id',
									width: 250,
									fieldLabel: lang['rezultat'],
									xtype: 'swresultclasscombo'
								}, {
									hiddenName: 'ResultDeseaseType_id',
									loadParams: {params: {where: ' where ResultDeseaseType_Code in (401, 402, 403)'}},
									width: 250,
									fieldLabel: lang['ishod'],
									xtype: 'swresultdeseasetypefedcombo'
								}, {
									fieldLabel: lang['diagnoz_osnovnoy'],
									hiddenName: 'CmpDiag_oid',									
									//displayField: 'Diag_Name',									
									width: 250,						
									xtype: 'swdiagcombo'					
								}, {
									fieldLabel: lang['diagnoz_dopolnitelnyiy'],
									hiddenName: 'CmpDiag_aid',			
									//displayField: 'Diag_Name',									
									width: 250,						
									xtype: 'swdiagcombo'
								}, {
									fieldLabel: lang['alk'],
									hiddenName: 'CmpCallCard_IsAlco',
									width: 100,
									comboSubject: 'YesNo',
									xtype: 'swcommonsprcombo'					
								}
								]
							}, 
							{
								border: false,
								layout: 'column',
								width: 500,
								items: [{
									border: false,
									layout: 'form',
									width: 500,
									items: [{
										disabledClass: 'field-disabled',
										fieldLabel: lang['rayon'],
										hiddenName: 'CmpArea_pid',
										width: 250,
										xtype: 'swklareastatcombo'								
									}, {
										valueField: 'Lpu_id',
										allowBlank: false,
										autoLoad: true,
										disabled: true,
										width: 250,
										listWidth: 250,
										fieldLabel: lang['peredan'],
										disabledClass: 'field-disabled',
										hiddenName: 'Lpu_id',
										displayField: 'Lpu_Nick',
										medServiceTypeId: 18,
										xtype: 'swlpuwithmedservicecombo'
									}]
								}]				
							}]
						}]
					}, {

						// Грид с лекарствами
						// =======================
						border: false,
						height: 150,
						id: 'EPREF_DrugGridPanel',
						layout: 'border',						
						items: [ new sw.Promed.ViewFrame({
							actions: [
								{ name: 'action_add', handler: function() { this.openEvnPrescrTreatDrugEditWindow('add'); }.createDelegate(this) },
								{ name: 'action_edit', handler: function() { this.openEvnPrescrTreatDrugEditWindow('edit'); }.createDelegate(this) },
								{ name: 'action_view', disabled: true, hidden: true },
								{ name: 'action_delete', handler: function() { this.deleteDrug(); }.createDelegate(this), tooltip: lang['udalit_lekarstvennoe_sredstvo_iz_spiska'] },
								{ name: 'action_refresh', disabled: true, hidden: true },
								{ name: 'action_print', disabled: true, hidden: true }
							],
							autoLoadData: false,
							border: true,
							id: 'EPREF_DrugGrid',
							region: 'center',
							stringfields: [
								{ name: 'EvnPrescrTreatDrug_id', type: 'int', header: 'ID', key: true },
								{ name: 'Drug_id', type: 'int', hidden: true },
								{ name: 'DrugPrepFas_id', type: 'int', hidden: true },
								{ name: 'EvnPrescrTreatDrug_KolvoEd', type: 'float', hidden: true },
								{ name: 'EvnPrescrTreatDrug_Kolvo_Show', type: 'float', hidden: true },
								{ name: 'Drug_Name', header: lang['lekarstvennoe_sredstvo'], type: 'string', id: 'autoexpand' },
								{ name: 'EvnPrescrTreatDrug_Kolvo', header: lang['kolichestvo'], type: 'float', width: 150 }
							],
							style: 'margin-bottom: 0.5em;',
							title: lang['medikamentyi'],
							toolbar: true											
						})]

					}, {

						// Грид с манипуляциями
						// =======================
						border: false,
						height: 150,
						id: 'ProcGridPanel',
						layout: 'border',						
						items: [ new sw.Promed.ViewFrame({
							actions: [
								{ name: 'action_add', handler: function() { this.openProcedureEditWindow('add'); }.createDelegate(this) },
								//{ name: 'action_edit', handler: function() { this.openProcedureEditWindow('edit');  }.createDelegate(this) },
								{ name: 'action_edit', disabled: true, hidden: true },
								{ name: 'action_view', disabled: true, hidden: true },
								{ name: 'action_delete', handler: function() { this.deleteProc(); }.createDelegate(this), tooltip: lang['udalit_protseduru_iz_spiska'] },
								{ name: 'action_refresh', disabled: true, hidden: true },
								{ name: 'action_print', disabled: true, hidden: true }
							],
							autoLoadData: false,
							border: true,
							id: 'ProcGrid',
							region: 'center',
							stringfields: [
								{ name: 'Usluga_id', type: 'int', header: 'UID', key: true },
								{ name: 'Procedure_Id', type: 'int', header: 'ID', key: true },
								{ name: 'Procedure_Name', header: lang['protsedura'], type: 'string', id: 'autoexpand' }						
							],
							style: 'margin-bottom: 0.5em;',
							title: lang['manipulyatsii'],
							toolbar: true						
						})]				

					}]
				},
				
				
				
				// ПРАВЫЙ СТОЛБИК
				{
					border: true,
					autoHeight: true,					
					layout: 'form',
					style: 'padding-left: 5px;',
					width: '600',
					items: [{
						autoHeight: true,						
						style: 'padding: 0; padding-top: 5px; padding-left: 5px; margin-bottom: 5px;',
						title: lang['dannyie_brigadyi'],
						xtype: 'fieldset',				
						labelAlign: 'left',
						items: [
						{
							autoCreate: {tag: "input", type: "text", size: "20", autocomplete: "off",  maxlength: '12'},
							disabledClass: 'field-disabled',
							fieldLabel: lang['№_vyizova_za_den'],
							maskRe: /[0-9]/,
							maxLength: 12,
							name: 'CmpCallCard_Numv',							
							width: 100,
							xtype: 'textfield',
							disabled: true
						}, {
							disabledClass: 'field-disabled',
							fieldLabel: lang['tip_vyizova'],
							disabled: true,
							listeners: {
								change: this.setStatusIdentification.createDelegate(this)
							},
							hiddenName: 'CmpCallType_id',
							displayField: 'CmpCallType_Name',
							width: 300,
							xtype: 'swcmpcalltypecombo'
						},{		
							comboSubject: 'CmpProfile',
							//disabled: true,
							disabledClass: 'field-disabled',
							fieldLabel: lang['profil'],							
							hiddenName: 'CmpProfile_bid',
							displayField: 'CmpProfile_bid',
							loadParams: {params: {where: getRegionNick().inlist([ 'krym' ]) ? ' where Region_id = ' + getRegionNumber() : ''}},
							moreFields: [
								{ name: 'Region_id', type: 'int' }
							],
							width: 300,
							xtype: 'swcommonsprcombo'
						}, {							
							disabledClass: 'field-disabled',
							fieldLabel: lang['smp'],
							name: 'CmpCallCard_Smpb',					
							width: 100,
							xtype: 'textfield'
						}, {							
							disabledClass: 'field-disabled',
							fieldLabel: lang['p_s'],
							name: 'CmpCallCard_Stbb',					
							width: 100,
							xtype: 'textfield'
						}, {
							allowBlank: false,							
							fieldLabel: lang['data_vyizova'],
							format: 'd.m.Y',
							name: 'CmpCallCard_prmDate',
							plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
							selectOnFocus: true,							
							width: 100,
							listeners: {
								'change': function(field, newValue, oldValue) {
									var base_form = this.FormPanel.getForm();

									base_form.findField('CmpCallType_id').setFilterByDate(newValue);
								}.createDelegate(this)
							},
							xtype: 'swdatefield'
						}, {
							disabled: true,
							fieldLabel: lang['vremya_vyizova'],
							name: 'CmpCallCard_prmTime',
							plugins: [ new Ext.ux.InputTextMask('99:99', true) ],					
							validateOnBlur: false,
							width: 60,
							xtype: 'swtimefield'
						}, {							
							fieldLabel: lang['peredan_vremya'],
							name: 'CmpCallCard_Tper',
							plugins: [ new Ext.ux.InputTextMask('99:99', true) ],					
							validateOnBlur: false,
							width: 60,
							xtype: 'swtimefield'
						}, {							
							fieldLabel: lang['ispolnen_vremya'],
							name: 'CmpCallCard_Tisp',
							plugins: [ new Ext.ux.InputTextMask('99:99', true) ],					
							validateOnBlur: false,
							width: 60,
							xtype: 'swtimefield'
						}, {							
							//fieldLabel: 'Пульт',
							//name: '',
							//plugins: [ new Ext.ux.InputTextMask('99:99', true) ],					
							//validateOnBlur: false,
							//width: 60,
							//xtype: 'swtimefield'
						}, {							
							disabledClass: 'field-disabled',
							fieldLabel: lang['nomer_brigadyi'],
							name: 'CmpCallCard_Numb',					
							width: 100,
							xtype: 'textfield'
						}, {							
							disabledClass: 'field-disabled',
							fieldLabel: lang['starshiy_brigadyi'],
							name: 'CmpCallCard_TabN',					
							width: 100,
							xtype: 'textfield'
						}, {							
							disabledClass: 'field-disabled',
							fieldLabel: lang['feldsher_1'],
							name: 'CmpCallCard_Tab2',					
							width: 100,
							xtype: 'textfield'
						}, {							
							disabledClass: 'field-disabled',
							fieldLabel: lang['feldsher_2'],
							name: 'CmpCallCard_Tab3',					
							width: 100,
							xtype: 'textfield'
						}, {							
							disabledClass: 'field-disabled',
							fieldLabel: lang['voditel'],
							name: 'CmpCallCard_Tab4',					
							width: 100,
							xtype: 'textfield'
						}, {							
						//	disabledClass: 'field-disabled',
						//	fieldLabel: 'GPS / Глонас',
						//	name: 'CmpCallCard_Inf5',					
						//	width: 100,
						//	xtype: 'textfield'
						}, {							
							disabledClass: 'field-disabled',
							fieldLabel: lang['ratsiya'],
							name: 'CmpCallCard_RCod',					
							width: 100,
							xtype: 'textfield'
						},
						//==================================
						{							
							disabledClass: 'field-disabled',
							fieldLabel: lang['kak_peredan'],
							name: 'CmpCallCard_Kakp',					
							width: 100,
							xtype: 'textfield'
						}, {							
							fieldLabel: lang['vyiezd'],
							name: 'CmpCallCard_Vyez',
							plugins: [ new Ext.ux.InputTextMask('99:99', true) ],					
							validateOnBlur: false,
							width: 60,
							xtype: 'swtimefield'
						}, {							
							fieldLabel: lang['pribyitie'],
							name: 'CmpCallCard_Przd',
							plugins: [ new Ext.ux.InputTextMask('99:99', true) ],					
							validateOnBlur: false,
							width: 60,
							xtype: 'swtimefield'
						}, {							
							fieldLabel: lang['gospitalizatsiya'],
							name: 'CmpCallCard_Tgsp',
							plugins: [ new Ext.ux.InputTextMask('99:99', true) ],					
							validateOnBlur: false,
							width: 60,
							xtype: 'swtimefield'
						}, {							
							fieldLabel: lang['v_statsionare'],
							name: 'CmpCallCard_Tsta',
							plugins: [ new Ext.ux.InputTextMask('99:99', true) ],					
							validateOnBlur: false,
							width: 60,
							xtype: 'swtimefield'
						}, {
							allowDecimals: true,
							allowNegative: false,
							disabledClass: 'field-disabled',
							fieldLabel: lang['kilometraj'],
							maxValue: 9999.99,
							name: 'CmpCallCard_Kilo',												
							width: 100,
							xtype: 'numberfield'
						}, {							
							fieldLabel: lang['vozvrat'],
							name: 'CmpCallCard_Tvzv',
							plugins: [ new Ext.ux.InputTextMask('99:99', true) ],					
							validateOnBlur: false,
							width: 60,
							xtype: 'swtimefield'
						}, {							
							disabledClass: 'field-disabled',
							fieldLabel: lang['prinyal'],
							name: 'CmpCallCard_Dsp1',					
							width: 100,
							xtype: 'textfield'
						}, {							
							disabledClass: 'field-disabled',
							fieldLabel: lang['peredal'],
							name: 'CmpCallCard_Dspp',					
							width: 100,
							xtype: 'textfield'
						}, {							
							disabledClass: 'field-disabled',
							fieldLabel: lang['naznachil'],
							name: 'CmpCallCard_Dsp2',					
							width: 100,
							xtype: 'textfield'
						}, {							
							disabledClass: 'field-disabled',
							fieldLabel: lang['zakryil'],
							name: 'CmpCallCard_Dsp3',					
							width: 100,
							xtype: 'textfield'
						}, {
							border: false,
							layout: 'column',					
							width: 500,
							items: [{
								border: false,
								layout: 'form',
								labelWidth: 120,
								items: [{
									disabledClass: 'field-disabled',
									fieldLabel: lang['dop_info_1'],
									name: 'CmpCallCard_Inf1',					
									width: 100,
									xtype: 'textfield'
								}, {							
									disabledClass: 'field-disabled',
									fieldLabel: lang['dop_info_2'],
									name: 'CmpCallCard_Inf2',					
									width: 100,
									xtype: 'textfield'
								}, {							
									disabledClass: 'field-disabled',
									fieldLabel: lang['dop_info_3'],
									name: 'CmpCallCard_Inf3',					
									width: 100,
									xtype: 'textfield'
								}]
							}, {
								border: false,								
								layout: 'form',								
								labelWidth: 120,
								items: [{
									disabledClass: 'field-disabled',
									fieldLabel: lang['dop_info_4'],
									name: 'CmpCallCard_Inf4',					
									width: 100,
									xtype: 'textfield'
								}, {							
									disabledClass: 'field-disabled',
									fieldLabel: lang['dop_info_5'],
									name: 'CmpCallCard_Inf5',					
									width: 100,
									xtype: 'textfield'
								}, {							
									disabledClass: 'field-disabled',
									fieldLabel: lang['dop_info_6'],
									name: 'CmpCallCard_Inf6',					
									width: 100,
									xtype: 'textfield'
								}]
							}]							
						}]
					}]
				}]	
			}]
		});



		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.doSave();
				}.createDelegate(this),
				iconCls: 'save16',
				onShiftTabAction: function() {
//					if ( !this.FormPanel.getForm().findField('CmpCallCard_Comm').disabled ) {
//						this.FormPanel.getForm().findField('CmpCallCard_Comm').focus(true);
//					}
//					else {
//						this.buttons[this.buttons.length - 1].focus();
//					}
				}.createDelegate(this),
				onTabAction: function() {
					this.buttons[this.buttons.length - 1].focus();
				}.createDelegate(this),
				// tabIndex: TABINDEX_CCCEF + 15,
				text: BTN_FRMSAVE
			}, {
				text: '-'
			},
			HelpButton(this, -1),
			{
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'cancel16',
				onShiftTabAction: function() {
					if ( this.action != 'view' ) {
						this.buttons[0].focus();
					}
				}.createDelegate(this),
				onTabAction: function() {
					if ( !this.FormPanel.getForm().findField('Person_Surname').disabled ) {
						this.FormPanel.getForm().findField('Person_Surname').focus(true);
					}
				}.createDelegate(this),
				// tabIndex: TABINDEX_CCCEF + 16,
				text: BTN_FRMCANCEL
			}],
			items: [
				this.FormPanel
			],
			layout: 'border'
		});

		sw.Promed.swCmpCallCardCloseCardWindow.superclass.initComponent.apply(this, arguments);
	},
	keys: [{
		alt: true,
		fn: function(inp, e) {
			var current_window = Ext.getCmp('CmpCallCardCloseCardWindow');
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
	layout: 'form',
	listeners: {
		'beforehide': function(win) {
			win.onCancelAction();
		},
		'hide': function(win) {
			win.onHide();
		},
		'maximize': function(win) {
			win.doLayout();
		},
		'restore': function(win) {
			win.fireEvent('maximize', win);
		}
	},
	maximizable: true,
	maximized: false,
	minHeight: 550,
	minWidth: 750,
	modal: true,
	onCancelAction: Ext.emptyFn,
	onHide: Ext.emptyFn,
	plain: true,
	resizable: false,
	show: function() {
		sw.Promed.swCmpCallCardCloseCardWindow.superclass.show.apply(this, arguments);
		this.doLayout();
		this.restore();
		this.center();
		this.maximize();
		var base_form = this.FormPanel.getForm();		
		base_form.reset();
		
		this.action = null;
		this.callback = Ext.emptyFn;
		this.formStatus = 'edit';
		this.onHide = Ext.emptyFn;

		if ( !arguments[0] || !arguments[0].formParams ) {
			sw.swMsg.alert(lang['soobschenie'], lang['nevernyie_parametryi'], function() {this.hide();}.createDelegate(this) );
			return false;
		}

		base_form.setValues(arguments[0].formParams);

		if ( arguments[0].action ) {
			this.action = arguments[0].action;
		}

		if ( arguments[0].callback ) {
			this.callback = arguments[0].callback;
		}
		
		if ( arguments[0].onHide ) {
			this.onHide = arguments[0].onHide;
		}
		
		var statuspanel = this.FormPanel.find('name', 'status_panel')[0],
			statusfield = statuspanel.find('name', 'status_field')[0];
			statusfield.getEl().update('');
			statuspanel.setVisible(false);

		var loadMask = new Ext.LoadMask(this.getEl(), {msg: LOAD_WAIT});
		loadMask.show();
				
		//var diag_oid = base_form.findField('CmpDiag_oid').getValue();
		//var diag_oid = arguments[0].formParams['CmpDiag_oid'];
		
		
		

		
//		if ( diag_oid != null && diag_oid.toString().length > 0 ) {
//			
//			base_form.findField('CmpDiag_oid').getStore().load({
//				callback: function() {
//					base_form.findField('CmpDiag_oid').getStore().each(function(record) {
//						if ( record.get('Diag_id') == diag_oid ) {
//							base_form.findField('CmpDiag_oid').fireEvent('select', base_form.findField('CmpDiag_oid'), record, 0);
//						}
//					});
//				},
//				params: { where: "where DiagLevel_id = 4 and Diag_id = " + diag_oid }
//			});
//		}
		
		switch ( this.action ) {
			case 'add':
				this.setTitle(WND_AMB_CCCEFADD);
				this.enableEdit(true);
				loadMask.hide();

				setCurrentDateTime({
					dateField: base_form.findField('CmpCallCard_prmDate'),
					loadMask: true,
					setDate: true,
					setDateMaxValue: true,
					setDateMinValue: false,
					setTime: true,
					timeField: base_form.findField('CmpCallCard_prmTime'),
					windowId: this.id,
					callback: function(date) {
						base_form.findField('CmpCallType_id').setFilterByDate(date);
					}
				});

				base_form.clearInvalid();
				base_form.findField('ARMType').setValue(arguments[0].ARMType);
				base_form.findField('KLAreaStat_idEdit').setValue(43); // г.Пермь
				
				var idx = base_form.findField('KLAreaStat_idEdit').getStore().findBy(function(rec) { return rec.get('KLAreaStat_id') == base_form.findField('KLAreaStat_idEdit').getValue(); }),
					record = base_form.findField('KLAreaStat_idEdit').getStore().getAt(idx);
				if( record ) {
					base_form.findField('KLAreaStat_idEdit').fireEvent('beforeselect', base_form.findField('KLAreaStat_idEdit'), record);
				} else {
					base_form.findField('KLAreaStat_idEdit').getStore().load({
						callback: function() {
							base_form.findField('KLAreaStat_idEdit').setValue(base_form.findField('KLAreaStat_idEdit').getValue());
							var idx = this.findBy(function(rec) { return rec.get('KLAreaStat_id') == base_form.findField('KLAreaStat_idEdit').getValue(); });
							base_form.findField('KLAreaStat_idEdit').fireEvent('beforeselect', base_form.findField('KLAreaStat_idEdit'), this.getAt(idx));
						}
					});
				}
				
				this.getCmpCallCardNumber();
			break;

			case 'edit':
			case 'view':								
				var cmp_call_card_id = base_form.findField('CmpCallCard_id').getValue();
				
				if ( !cmp_call_card_id ) {
					loadMask.hide();
					this.hide();
					return false;
				}
				
				base_form.load({
					failure: function() {
						loadMask.hide();
						sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_zagruzke_dannyih_formyi'], function() {this.hide();}.createDelegate(this) );
					}.createDelegate(this),
					params: {
						CmpCallCard_id: cmp_call_card_id
					},
					success: function() {
						loadMask.hide();
						if ( base_form.findField('accessType').getValue() == 'view' ) {
							this.action = 'view';
						}


				var diag_oid = base_form.findField('CmpDiag_oid').getValue();
				if ( diag_oid ) {
					base_form.findField('CmpDiag_oid').getStore().load({
						callback: function() {
							base_form.findField('CmpDiag_oid').getStore().each(function(record) {
								if ( record.get('Diag_id') == diag_oid ) {
									base_form.findField('CmpDiag_oid').fireEvent('select', base_form.findField('CmpDiag_oid'), record, 0);
								}
							});
						},
						params: { where: "where Diag_id = " + diag_oid }
					});
				}
				var diag_did = base_form.findField('CmpDiag_aid').getValue();
				if ( diag_did ) {
					base_form.findField('CmpDiag_aid').getStore().load({
						callback: function() {
							base_form.findField('CmpDiag_aid').getStore().each(function(record) {
								if ( record.get('Diag_id') == diag_did ) {
									base_form.findField('CmpDiag_aid').fireEvent('select', base_form.findField('CmpDiag_aid'), record, 0);
								}
							});
						},
						params: { where: "where Diag_id = " + diag_did }
					});
				}
				
						if ( this.action == 'edit' ) {
							this.setTitle(WND_AMB_CCCEFCLOSE);
							this.enableEdit(true);
						}
						else {
							this.setTitle(WND_AMB_CCCEFVIEW);
							this.enableEdit(false);
						}

						var index;
						var person_id = base_form.findField('Person_id').getValue();
						var record;

						if ( this.action == 'edit' && Number(person_id) > 0 ) {
							base_form.findField('Person_Age').disable();
							base_form.findField('Person_Birthday').disable();
							base_form.findField('Person_Firname').disable();
							base_form.findField('Person_Secname').disable();
							base_form.findField('Person_Surname').disable();
							base_form.findField('Sex_id').disable();
							base_form.findField('Polis_Ser').disable();
							base_form.findField('Polis_Num').disable();
							base_form.findField('Polis_EdNum').disable();
							base_form.findField('Sex_id').disable();
						}

						base_form.clearInvalid();

						if ( this.action == 'edit' ) {
							if ( !base_form.findField('CmpCallCard_Numv').disabled ) {
								base_form.findField('CmpCallCard_Numv').focus(true, 250);
							}
							else {
								this.buttons[this.buttons.length - 1].focus();
							}
						}
						else {
							this.buttons[this.buttons.length - 1].focus();
						}
						
						if( base_form.findField('KLSubRgn_id').getValue() != null ) {
							base_form.findField('KLSubRgn_id').getStore().load({
								params: {
									'no':true
								},
								callback: function() {
									base_form.findField('KLSubRgn_id').setValue(base_form.findField('KLSubRgn_id').getValue());
									
								}
							})
						}
						
						if( base_form.findField('KLCity_id').getValue() != null ) {
							base_form.findField('KLCity_id').getStore().load({
								params: {
									subregion_id: (base_form.findField('KLSubRgn_id').getValue() > 0) ? base_form.findField('KLSubRgn_id').getValue() : 59
								},
								callback: function() {
									base_form.findField('KLCity_id').setValue(base_form.findField('KLCity_id').getValue());
								}
							})
						}
																		
						if( Number(base_form.findField('KLStreet_id').getRawValue()) > 0 ) {
							base_form.findField('KLStreet_id').getStore().load({
								params: {
									town_id: (base_form.findField('KLSubRgn_id').getValue() > 0) ? base_form.findField('KLSubRgn_id').getValue() : base_form.findField('KLCity_id').getValue(),
									showSocr: 1
								},
								callback: function() {
									base_form.findField('KLStreet_id').setValue(base_form.findField('KLStreet_id').getValue());
								}
							})
						}

						base_form.findField('CmpCallType_id').setFilterByDate(base_form.findField('CmpCallCard_prmDate').getValue());
						
						//if( Number(base_form.findField('CmpReason_id').getRawValue()) > 0 ) {
						//	base_form.findField('CmpReason_id').getStore().load({
						//		callback: function() {
						//			base_form.findField('CmpReason_id').setValue(base_form.findField('CmpReason_id').getRawValue());
						//		}
						//	});
						//}
						
					
					}.createDelegate(this),
					url: '/?c=CmpCallCard&m=loadCmpCallCardEditForm'
				});
				
			break;

			default:
				loadMask.hide();
				this.hide();
			break;
		}		
	},
	width: 750	
});