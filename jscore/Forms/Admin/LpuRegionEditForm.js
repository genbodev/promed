/**
* swLpuRegionEditForm - окно просмотра и редактирования участков
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright © 2009 Swan Ltd.
* @author       Быдлокодер ©
* @version      17.06.2009
*/

sw.Promed.swLpuRegionEditForm = Ext.extend(sw.Promed.BaseForm, {
	title:lang['uchastok'],
	id: 'LpuRegionEditForm',
	layout: 'border',
	maximizable: false,
	shim: false,
	width: 560,
	height: 475,
	modal: true,
	resizable: false,
	//autoHeight: true,
	buttons:
	[{
		text: BTN_FRMSAVE,
		id: 'lrOk',
		tabIndex: TABINDEX_LREGEW + 40,
		iconCls: 'save16',
		handler: function()
		{
			this.ownerCt.doSave();
		}
	},
	{
		text:'-'
	},
	{
		text: BTN_FRMHELP,
		iconCls: 'help16',
		id: 'LREFOK',
		tabIndex: TABINDEX_LREGEW + 45,
		handler: function(button, event) {
			ShowHelp(this.ownerCt.title);
		}
	},
	{
		text: BTN_FRMCANCEL,
		id: 'lrCancel',
		tabIndex: TABINDEX_LREGEW + 50,
		iconCls: 'cancel16',
		handler: function()
		{
			this.ownerCt.hide();
		},
		onTabAction: function () {
			Ext.getCmp('lrLpuRegionType_id').focus();
		},
		onShiftTabAction: function() {
			Ext.getCmp('LREFOK').focus();
		}
	}],
	listeners: {
		hide: function() {
			this.returnFunc(this.owner, -1);
		},
		activate: function(){
			this.checkLpuRegionMod();
		}
	},
	returnFunc: function(owner) {},
	show: function(){
		sw.Promed.swLpuRegionEditForm.superclass.show.apply(this, arguments);
		var loadMask = new Ext.LoadMask(Ext.get('LpuRegionEditForm'), { msg: "Подождите, идет загрузка..." });
		loadMask.show();

		this.checkLpuSection = true;
		this.checkPost = true;
		this.checkRegType = true;
		this.checkStavka = true;
		this.checkMainMPDoubles = true;
		this.deletedRecords = [];

		// Обнуление для того чтобы фокус не ставился
		this.ViewLpuRegionMedPersonal.loadCount = -1;
		if (arguments[0].callback)
			this.returnFunc = arguments[0].callback;
		if (arguments[0].owner)
			this.owner = arguments[0].owner;
		if (arguments[0].action)
			this.action = arguments[0].action;
		if (arguments[0].LpuRegion_id)
			this.LpuRegion_id = arguments[0].LpuRegion_id;
		else
			this.LpuRegion_id = null;

		if (arguments[0].LpuRegionType_id)
			this.LpuRegionType_id = arguments[0].LpuRegionType_id;
		else
			this.LpuRegionType_id = null;

		if (arguments[0].Lpu_Name)
			this.Lpu_Name = arguments[0].Lpu_Name;
		else
			this.Lpu_Name = null;

		if (arguments[0].Lpu_id)
			this.Lpu_id = arguments[0].Lpu_id;
		else
			this.Lpu_id = null;

		if (!arguments[0]){
			Ext.Msg.alert(lang['oshibka'], lang['otsutstvuyut_neobhodimyie_parametryi']);
			this.hide();
			return false;
		}

		var form = this,
			base_form = form.findById('LpuRegionEditFormPanel').getForm(),
			regType = '',
			grid = form.ViewLpuRegionMedPersonal;

		grid.readOnly = false;

		form.findById('LpuRegionEditFormPanel').getForm().reset();
		grid.getGrid().getStore().removeAll();

		var now = new Date();
		var xdate = new Date(2017,0,10);
		if(!(getRegionNick()=='perm' && now < xdate)){
			base_form.findField('LpuRegion_begDate').maxValue = getValidDT(getGlobalOptions().date, '');
			base_form.findField('LpuRegion_endDate').minValue = undefined;
			base_form.findField('LpuRegion_endDate').maxValue = getValidDT(getGlobalOptions().date, '');
		}

		switch (this.action){
			case 'add':
				form.setTitle(lang['uchastok_dobavlenie']);
				this.enableEdit(true);
				break;
			case 'edit':
				form.setTitle(lang['uchastok_redaktirovanie']);
				this.enableEdit(true);
				break;
			case 'view':
				form.setTitle(lang['uchastok_prosmotr']);
				this.enableEdit(false);
				grid.readOnly = true;
				break;
		}

		form.findById('lrLpu_id').setValue(this.Lpu_id);
		var LpuAgeType_id = form.findById('lrLpu_id').getFieldValue('MesAgeLpuType_id');

		if (this.LpuRegionType_id)
		{
			form.findById('lrLpuRegionType_id').setValue(this.LpuRegionType_id);
		}

		if (!Ext.isEmpty(LpuAgeType_id) && LpuAgeType_id === 2) {
			regType = 'ped';
		} else {
			regType = 'ter';
		}

		var index = form.findById('lrLpuRegionType_id').getStore().findBy(function(rec){
			return (rec.get('LpuRegionType_SysNick') === regType);
		});

		if (index >= 0) {
			form.findById('lrLpuRegionType_id').setValue(form.findById('lrLpuRegionType_id').getStore().getAt(index).get('LpuRegionType_id'));
		}

		if (this.action!='add') {
			form.findById('LpuRegionEditFormPanel').getForm().load({
				url: C_GETOBJECTLIST,
				params:{
					object: 'LpuRegion',
					LpuRegion_id: form.LpuRegion_id
				},
				success: function () {
					base_form.findField('LpuRegion_begDate').fireEvent('change', base_form.findField('LpuRegion_begDate'), base_form.findField('LpuRegion_begDate').getValue());

					form.findById('lrLpuBuilding_id').getStore().load({params: {Lpu_id: form.findById('lrLpu_id').getValue(), mode: 'combo'}});
					form.findById('lrLpuSection_id').getStore().load({
							params: {
								Lpu_id: form.Lpu_id,
								mode: 'combo'
							},
							callback: function(){
								form.filterLpuSectionCombo();
								if (!Ext.isEmpty(form.findById('lrLpuSection_id').getFieldValue('LpuBuilding_id'))){
									form.findById('lrLpuBuilding_id').setValue(form.findById('lrLpuSection_id').getFieldValue('LpuBuilding_id'));
									form.filterLpuSectionCombo();
								}
								if (!Ext.isEmpty(form.LpuRegion_id)){
									grid.loadData({
										params:{
											Lpu_id: form.Lpu_id,
											LpuRegion_id: form.LpuRegion_id
										},
										globalFilters: {Lpu_id: form.Lpu_id, LpuRegion_id: form.LpuRegion_id},
										callback: function(){
											form.checkLpuRegionMod();
										}
									});
								}
								loadMask.hide();
							}}
					);
					form.onLpuRegionTypeChange();
				},
				failure: function () {
					loadMask.hide();
					Ext.Msg.alert(lang['oshibka'], lang['oshibka_zaprosa_k_serveru_poprobuyte_povtorit_operatsiyu']);
				}
			});
		} else {
			form.findById('lrLpuBuilding_id').getStore().load({params: {Lpu_id: form.findById('lrLpu_id').getValue(), mode: 'combo'}});
			form.findById('lrLpuSection_id').getStore().load({
				params: {
					Lpu_id: form.Lpu_id,
					mode: 'combo'
				},
				callback: function(){
					form.filterLpuSectionCombo();
					loadMask.hide();
				}}
			);
			form.onLpuRegionTypeChange();
		}

		form.findById('lrLpuRegion_Descr').focus(true, 400);

	},
	checkLpuRegionMod: function(){

		var grid = this.findById('lrLpuRegionMedPersonalGrid').getGrid();

		if (grid.getStore().getCount() == 0 && this.action != 'view' && !getWnd('swMedStaffRegionEditForm').isVisible()) {
			this.findById('lrLpuRegionType_id').enable();
		} else {
			this.findById('lrLpuRegionType_id').disable();
		}
	},
	recIsTrueValue: function(val){
		return (!Ext.isEmpty(val) && (val === 'true' || val === true || val === 2 || val === '2'));
	},
	isExistsMainRec: function(){
		var existsMainRec = false,
			base_form = this.findById('LpuRegionEditFormPanel').getForm(),
			_this = this,
			grid = this.findById('lrLpuRegionMedPersonalGrid').getGrid();

		grid.getStore().each(function(rec) {
			if ( !existsMainRec ) {
				if ( _this.recIsTrueValue(rec.get('MedStaffRegion_isMain')) ) {
					existsMainRec = true;
				}
			}
		});

		return existsMainRec;
	},
	isExistsMainRecOnEndDate: function(){
		var existsMainRec = false,
			base_form = this.findById('LpuRegionEditFormPanel').getForm(),
			_this = this,
			grid = this.findById('lrLpuRegionMedPersonalGrid').getGrid();

		var LpuRegion_endDate = base_form.findField('LpuRegion_endDate').getValue() || getValidDT(getGlobalOptions().date, '');

		grid.getStore().each(function(rec) {
			if ( !existsMainRec ) {
				if (
					_this.recIsTrueValue(rec.get('MedStaffRegion_isMain'))
					&& (Ext.isEmpty(rec.get('MedStaffRegion_endDate')) || rec.get('MedStaffRegion_endDate') >= LpuRegion_endDate)
				) {
					existsMainRec = true;
				}
			}
		});

		return existsMainRec;
	},
    MainRecordEdit: function(action) {
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

			if ( getWnd('swMedStaffRegionEditForm').isVisible() ) {
            sw.swMsg.alert(lang['oshibka'], lang['okno_redaktirovaniya_vracha_na_uchastke_uje_otkryito']);
            return false;
        }

        var formParams = {},
            params = {},
			_this = this,
            grid = this.findById('lrLpuRegionMedPersonalGrid').getGrid(),
			form = this.findById('LpuRegionEditFormPanel'),
			base_form = form.getForm(),
            selectedRecord;

		if (!form.getForm().isValid()) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function(){
					form.getFirstInvalidEl().focus(false);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

        params.LpuRegion_id = this.LpuRegion_id;
        params.Lpu_id = this.Lpu_id;
		params.LpuRegionType_id = base_form.findField('LpuRegionType_id').getValue();
		params.LpuRegionType_SysNick = base_form.findField('LpuRegionType_id').getFieldValue('LpuRegionType_SysNick');
		params.LpuRegion_id = base_form.findField('LpuRegion_id').getValue();
		params.LpuSection_id = base_form.findField('LpuSection_id').getValue();
		params.LpuRegion_begDate = Ext.util.Format.date(base_form.findField('LpuRegion_begDate').getValue(), 'd.m.Y');
		params.LpuRegion_endDate = Ext.util.Format.date(base_form.findField('LpuRegion_endDate').getValue(), 'd.m.Y');

        if ( grid.getSelectionModel().getSelected() && grid.getSelectionModel().getSelected().get('MedStaffRegion_id') ) {
            selectedRecord = grid.getSelectionModel().getSelected();
        }

		var MedStaffRegionData = new Array();

		grid.getStore().each(function(rec) {
			if ( !Ext.isEmpty(rec.get('MedPersonal_id')) ) {
				MedStaffRegionData.push({
					MedStaffRegion_id: rec.get('MedStaffRegion_id'),
					MedPersonal_id: rec.get('MedPersonal_id'),
					MedStaffRegion_isMain: rec.get('MedStaffRegion_isMain'),
					MedStaffRegion_begDate: rec.get('MedStaffRegion_begDate'),
					MedStaffRegion_endDate: rec.get('MedStaffRegion_endDate')
				});
			}
		});
        if ( action == 'add' ) {
            params.onHide = function() {
                if ( grid.getStore().getCount() > 0 ) {
                    grid.getView().focusRow(0);
                }
            };
        } else {
            if ( !selectedRecord ) {
                return false;
            }

            formParams = selectedRecord.data;
            params.MedStaffRegion_id = grid.getSelectionModel().getSelected().get('MedStaffRegion_id');
            params.onHide = function() {
                grid.getView().focusRow(grid.getStore().indexOf(selectedRecord));
            };

            if (params.MedStaffRegion_id < 0) {
                params.MedStaffFact_id = grid.getSelectionModel().getSelected().get('MedStaffFact_id');
                params.MedPersonal_id = grid.getSelectionModel().getSelected().get('MedPersonal_id');
                params.MedStaffRegion_id = grid.getSelectionModel().getSelected().get('MedStaffRegion_id');
                params.MedStaffRegion_endDate = grid.getSelectionModel().getSelected().get('MedStaffRegion_endDate');
                params.MedStaffRegion_begDate = grid.getSelectionModel().getSelected().get('MedStaffRegion_begDate');
            }
        }

		params.MedStaffRegionData = MedStaffRegionData;
        params.action = action;
        params.callback = function(data) {
            if ( typeof data != 'object' || typeof data.MedStaffRegionData != 'object' ) {
                return false;
            }
            var record = grid.getStore().getById(data.MedStaffRegionData.MedStaffRegion_id);

            if ( record ) {

                var grid_fields = [];

                grid.getStore().fields.eachKey(function(key, item) {
                    grid_fields.push(key);
                });

                for ( var i = 0; i < grid_fields.length; i++ ) {
                    record.set(grid_fields[i], data.MedStaffRegionData[grid_fields[i]]);
                }

				record.set('status', 2);

                record.commit();
            }
            else {
                if ( grid.getStore().getCount() == 1 && !grid.getStore().getAt(0).get('MedStaffRegion_id') ) {
                    grid.getStore().removeAll();
                }

                data.MedStaffRegionData.MedStaffRegion_id = -swGenTempId(grid.getStore());
                data.MedStaffRegionData.status = 0;

                grid.getStore().loadData([ data.MedStaffRegionData ], true);
            }
			_this.checkLpuRegionMod();
        }.createDelegate(this);

        params.formMode = 'local';
        params.formParams = formParams;

        getWnd('swMedStaffRegionEditForm').show(params);
		this.checkLpuRegionMod();
    },
    MainRecordDelete: function() {

		var _this = this;
        if ( this.action == 'view' ) {
            return false;
		}

		var grid = this.findById('lrLpuRegionMedPersonalGrid').getGrid(),
			idField = 'MedStaffRegion_id';

		if ( !grid || !grid.getSelectionModel() || !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get(idField) ) {
			return false;
		}

		 sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId, text, obj) {
				if ( buttonId == 'yes' ) {

					var record = grid.getSelectionModel().getSelected(),
						index = 0;

					if (record.get('MedStaffRegion_id') > 0) {
						index = 1;
					}

					if ( Ext.isEmpty(record.get('MedStaffRegion_endDate')) ) {
						var existEmptyDaterecord = false;

						grid.getStore().each(function(rec){
							if ( !Ext.isEmpty(rec.get('MedStaffRegion_id')) && Ext.isEmpty(rec.get('MedStaffRegion_endDate')) && rec.get('MedStaffRegion_id') != record.get('MedStaffRegion_id')){
								existEmptyDaterecord = true;
							}
						});

						if ( !existEmptyDaterecord ) {
							sw.swMsg.alert(lang['oshibka'], lang['nelzya_udalyat_vracha_bez_datyi_okonchaniya_rabotyi_esli_pri_etom_na_uchastke_net_drugogo_vracha_bez_datyi_okonchaniya_rabotyi']);
							return false;
						}
					}

					switch (index) {
						case 0:
							grid.getStore().remove(record);
							break;
						case 1:
							record.set('status', 3);
							_this.deletedRecords.push(record.get('MedStaffRegion_id'));
						break;
                    }

					grid.getStore().filterBy(function(rec){
						return !rec.get('MedStaffRegion_id').inlist(_this.deletedRecords);
					});

					if ( grid.getStore().getCount() > 0 ) {
						grid.getView().focusRow(0);
						grid.getSelectionModel().selectFirstRow();
					}

					_this.checkLpuRegionMod();
				}
			}.createDelegate(this),
			icon: Ext.MessageBox.QUESTION,
			msg: lang['vyi_deystvitelno_hotite_udalit_dannogo_vracha_s_uchastka'],
			title: lang['vopros']
		});
	},
	onLpuRegionTypeChange: function() {
		var base_form = this.findById('LpuRegionEditFormPanel').getForm();
		var LpuRegionType_SysNick = base_form.findField('LpuRegionType_id').getFieldValue('LpuRegionType_SysNick');
		if (getRegionNick() == 'penza' && LpuRegionType_SysNick.inlist(['ter', 'ped', 'vop','gin','stom','feld','psdet','pspod','psvz'])) {
			base_form.findField('LpuRegion_tfoms').showContainer();
			if( LpuRegionType_SysNick.inlist(['ter', 'ped', 'vop','feld']) ){
				base_form.findField('LpuRegion_tfoms').setAllowBlank(false);
			}else{
				base_form.findField('LpuRegion_tfoms').setAllowBlank(true);
			}
		} else {
			base_form.findField('LpuRegion_tfoms').setValue('');
			base_form.findField('LpuRegion_tfoms').hideContainer();
			base_form.findField('LpuRegion_tfoms').setAllowBlank(true);
		}
	},
	filterLpuSectionCombo: function() {
		var lpu_section_id = this.findById('lrLpuSection_id').getValue(),
			lpu_building_id = this.findById('lrLpuBuilding_id').getValue(),
			lpu_region_type = this.findById('lrLpuRegionType_id').getFieldValue('LpuRegionType_SysNick'),
			LpuSectionProfiles4FilterByRegionType = [];

		//Фильтруем по типу участка, пока только для Перьми
		if (!Ext.isEmpty(lpu_region_type)){
			switch(getRegionNick()){
				case 'perm':
					switch(lpu_region_type){
						case 'ter':
							LpuSectionProfiles4FilterByRegionType = ['1000', '1001', '1003', '1007', '1010', '1011', '97', '57'];
							break;
						case 'ped':
							LpuSectionProfiles4FilterByRegionType = ['0900', '0902', '0905', '0907', '1011', '68', '57'];
							break;
						case 'stom':
							LpuSectionProfiles4FilterByRegionType = ['1800', '1801', '1802', '1803', '1810', '1811', '1830', '85', '89', '86', '87', '171'];
							break;
						case 'gin':
							LpuSectionProfiles4FilterByRegionType = ['2500', '2509', '2510', '2514', '2517', '2518', '2519', '3', '136'];
							break;
						case 'vop':
							LpuSectionProfiles4FilterByRegionType = ['1000', '1001', '1003', '1007', '1010', '1011', '0900', '0902', '0905', '0907', '97', '68', '57'];
							break;
					}
					break;
			}
		}

		this.findById('lrLpuSection_id').setBaseFilter(function(rec){
			return (
				(Ext.isEmpty(lpu_building_id) || rec.get('LpuBuilding_id') == lpu_building_id)
				&& (Ext.isEmpty(LpuSectionProfiles4FilterByRegionType[0]) || rec.get('LpuSectionProfile_Code').inlist(LpuSectionProfiles4FilterByRegionType))
			);
		});

		if (this.findById('lrLpuSection_id').getStore().getById(lpu_section_id)) {
			this.findById('lrLpuSection_id').setValue(lpu_section_id);
		} else {
			this.findById('lrLpuSection_id').clearValue();
		}
	},
	doSave: function(options) {

		var form = this.findById('LpuRegionEditFormPanel'),
			base_form = form.getForm(),
			win = this,
			msg = '',
			mainRecCount = 0,
			showCountError = false,
			gridStore = this.findById('lrLpuRegionMedPersonalGrid').getGrid().getStore(),
			gridCount = gridStore.getCount(),
			LpuRegionType_SysNick = base_form.findField('LpuRegionType_id').getFieldValue('LpuRegionType_SysNick');

		if(options){
			var options = options;
		} else {
			var options = {};
		}

		if (!form.getForm().isValid()){
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function(){
					form.getFirstInvalidEl().focus(false);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		if (LpuRegionType_SysNick && gridCount === 0 && Ext.isEmpty(base_form.findField('LpuRegion_endDate').getValue())) {
			msg = lang['na_uchastke_doljen_byit_hotya_byi_odin_vrach'];
			switch(getRegionNick()){
				case 'perm':
					if (LpuRegionType_SysNick.inlist(['ter','ped','vop','comp','prip','feld','gin','stom'])){
						showCountError = true;
					}
					break;
				case 'khak':
					if (LpuRegionType_SysNick.inlist(['ter','ped','gin','stom','vop','feld'])){
						showCountError = true;
					}
					break;
				case 'buryatiya':
					if (LpuRegionType_SysNick.inlist(['ter','ped','gin','stom','vop','feld'])){
						showCountError = true;
					}
					break;
				default:
					if (LpuRegionType_SysNick.inlist(['ter','ped','gin','vop','feld'/*,'stom'*/])){
						showCountError = true;
					}
					break;
			}

			if (showCountError){
				sw.swMsg.alert(lang['oshibka'], msg);
				return false;
			}
		} else {
			mainRecExists = this.isExistsMainRec();

			if ( !mainRecExists ) {
				if(getRegionNick() == 'ekb'){
					if(!(options && options.ignoreMain)){
						this.formStatus = 'edit';
						sw.swMsg.show({
							buttons: Ext.Msg.YESNO,
							fn: function (buttonId, text, obj) {
								if (buttonId == 'yes') {
									options.ignoreMain = true;
									win.doSave(options);
								}
							}.createDelegate(this),
							icon: Ext.Msg.WARNING,
							msg: 'Не указан основной врач участка. Основной врач участка нужен для автоматической подстановки при вызовах на дом, а также отчетов по прикреплениям. Продолжить сохранение без указания основного врача?',
							title: 'Предупреждение'
						});
						return false;
					}
				} else {
					sw.swMsg.alert(lang['oshibka'], lang['ni_odin_vrach_ne_otmechen_kak_osnovnoy_vrach_uchastka']);
					return false;
				}
			}

			mainRecOnEndDateExists = this.isExistsMainRecOnEndDate();

			if ( !mainRecOnEndDateExists && !(getRegionNick() == 'ekb' && options && options.ignoreMain)) {
				sw.swMsg.alert('Ошибка', 'Период работы основного врача на участке закрыт. Основным врачом участка может быть врач с действующим периодом работы на участке.');
				return false;
			}
		}

		gridStore.each(function(el){
			if (el.get('MedStaffRegion_isMain') == 2){
				mainRecCount += 1;
			}
		});

		if (mainRecCount > 1){
			sw.swMsg.alert(lang['oshibka'], lang['na_uchastke_ne_mojet_byit_bolshe_odnogo_osnovnogo_vracha']);
			return false;
		}


		if ( !Ext.isEmpty(base_form.findField('LpuRegion_endDate').getValue()) ) {
			if ( base_form.findField('LpuRegion_endDate').getValue() < base_form.findField('LpuRegion_begDate').getValue()) {
				sw.swMsg.alert(lang['oshibka'], lang['data_otkryitiya_uchastka_ne_mojet_byit_bolshe_datyi_zakryitiya']);
				return false;
			}

			var errorFlag = false;

			gridStore.each(function(rec) {
				if ( Ext.isEmpty(rec.get('MedStaffRegion_endDate')) || rec.get('MedStaffRegion_endDate') > base_form.findField('LpuRegion_endDate').getValue() ) {
					errorFlag = true;
				}
			});

			if ( errorFlag == true ) {
				sw.swMsg.alert(lang['oshibka'], lang['pri_zakryitii_uchastka_vse_vrachi_uchastka_doljnyi_imet_zakryityie_periodyi_rabotyi_na_uchastke_vhodyaschie_v_period_deystviya_samogo_uchastka']);
				return false;
			}
		}

		form.ownerCt.submit({});
	},
	submit: function(options) {
		var form = this.findById('LpuRegionEditFormPanel'),
			base_form = form.getForm(),
			params = {},
			msg = '',
			_this = this;

		params.LpuRegionType_id = form.findById('lrLpuRegionType_id').getValue();
		params.LpuRegionType_SysNick = form.findById('lrLpuRegionType_id').getFieldValue('LpuRegionType_SysNick');
		params.Lpu_id = form.findById('lrLpu_id').getValue();

		params.checkMainMPDoubles = this.checkMainMPDoubles;
		params.checkPost = this.checkPost;
		params.checkRegType = this.checkRegType;
		params.checkLpuSection = this.checkLpuSection;
		params.checkStavka = this.checkStavka;

        // Собираем данные из грида врачей
        var LpuRegionMedPersonalGrid = this.findById('lrLpuRegionMedPersonalGrid').getGrid();
		LpuRegionMedPersonalGrid.getStore().clearFilter();

        if ( LpuRegionMedPersonalGrid.getStore().getCount() > 0 ) {
            var LpuRegionMedPersonalData = getStoreRecords(LpuRegionMedPersonalGrid.getStore(), {
                convertDateFields: true,
				allRecords: true,
                exceptionFields: [
                    'MedPersonal_FIO'
                ]
            });

			LpuRegionMedPersonalData.forEach(function(rec){
				if (!Ext.isEmpty(rec.MedStaffRegion_begDate) && rec.status != 3 && Ext.util.Format.date(base_form.findField('LpuRegion_begDate').getValue(), 'd.m.Y').split('.').reverse().join('.') > rec.MedStaffRegion_begDate.split('.').reverse().join('.')) {
					msg = lang['data_rabotyi_vracha_na_uchastke_ne_mojet_byit_ranshe_datyi_otkryitiya_uchastka'];
				}
			});

			if (!Ext.isEmpty(msg)) {
				Ext.Msg.alert(lang['oshibka'], msg);
				return false;
			}

            params.LpuRegionMedPersonalData = Ext.util.JSON.encode(LpuRegionMedPersonalData);
        }

		//Чтобы при сохранении не показывались удалённые записи
		LpuRegionMedPersonalGrid.getStore().filterBy(function(rec){
			return !rec.get('MedStaffRegion_id').inlist(_this.deletedRecords);
		});

		var loadMask = new Ext.LoadMask(Ext.get('LpuRegionEditForm'), { msg: "Подождите, идет сохранение..." });
		loadMask.show();
		form.getForm().submit({
			params: params,
			failure: function(result_form, action) {
				loadMask.hide();
				if (action.result) {
					if (action.result.Error_Code && action.result.Error_Msg) {
						if ( action.result.Error_Code.toString().inlist([ '1', '995', '997', '998' ]) ) {
							sw.swMsg.alert(lang['oshibka'], action.result.Error_Msg);
						}
						else {
							sw.swMsg.show({
								buttons: Ext.Msg.YESNO,
								fn: function(buttonId, text, obj) {
									if ( buttonId == 'yes' ) {
										switch (action.result.Error_Code) {
											case '991':
												_this.checkStavka = false;
												break;
											case '992':
												_this.checkLpuSection = false;
												break;
											case '993':
												_this.checkPost = false;
												break;
											case '994':
												_this.checkRegType = false;
												break;
											case '999':
												_this.checkMainMPDoubles = false;
												break;
											default:
												Ext.Msg.alert('Ошибка #100001', 'Неизвестный код ошибки.');
												break;
										}
										_this.submit(params);
									}
								}.createDelegate(this),
								icon: Ext.MessageBox.QUESTION,
								msg: action.result.Error_Msg + lang['prodoljit_sohranenie'],
								title: lang['oshibka']
							});
						}
					}
					else {
						Ext.Msg.alert('Ошибка #100002', 'При сохранении произошла ошибка!');
					}
				}
			},
			success: function(result_form, action) {
				loadMask.hide();
				if (action.result) {
					if (action.result.LpuRegion_id) {
						_this.hide();
						form.ownerCt.returnFunc(form.ownerCt.owner, action.result.LpuRegion_id);
						if (action.result.Alert_Msg) {
							showSysMsg(action.result.Alert_Msg);
						}
					} else {
						Ext.Msg.alert('Ошибка #100004', 'При сохранении произошла ошибка!');
					}
				} else {
					Ext.Msg.alert('Ошибка #100005', 'При сохранении произошла ошибка!');
				}
			}
		});
	},
	initComponent: function()
	{
		var _this = this;
		this.ViewLpuRegionMedPersonal = new sw.Promed.ViewFrame(
		{
			title:lang['vrachi_na_uchastkah'],
			object: 'MedStaffRegion',
			editformclassname: 'swMedStaffRegionEditForm',
			id: 'lrLpuRegionMedPersonalGrid',
			dataUrl: C_MSFREG_LIST,
			useEmptyRecord: false,
			autoLoadData: false,
			stringfields:
			[
				{name: 'MedStaffRegion_id', type: 'int', header: 'ID', key: true},
				{name: 'MedStaffFact_id', type: 'int', hidden: true},
				{name: 'MedPersonal_id', type: 'int', hidden: true},
				{name: 'status', type: 'int', hidden: true},
				{name: 'MedPersonal_FIO', id: 'autoexpand', type: 'string', header: lang['fio']},
				{name: 'PostMed_Name', type: 'string', header: lang['doljnost']},
				{name: 'MedStaffRegion_isMain', header: lang['osnovnoy_vrach_uchastka'], type:'checkbox'},
				{name: 'MedStaffRegion_begDate', header: lang['data_nachala'],  type: 'date'},
				{name: 'MedStaffRegion_endDate', header: lang['data_okonchaniya'],  type: 'date'}
			],
			actions:
			[
				{name:'action_add',  handler: function() { _this.MainRecordEdit('add'); }},
				{name:'action_edit',  handler: function() { _this.MainRecordEdit('edit'); }},
				{name:'action_view', handler: function() { _this.MainRecordEdit('view'); }},
				{name:'action_delete',  handler: function() { _this.MainRecordDelete(); }},
				{name:'action_refresh', hidden: true},
				{name:'action_print', hidden: true}
			],
			onRowSelect: function() {
				_this.checkLpuRegionMod();
			},
			focusOn: {name:'lrOk',type:'button'},
			focusPrev: {name:'lrLpuRegionType_id',type:'field'},
			focusOnFirstLoad: false
		});

		this.MainPanel = new sw.Promed.FormPanel({
			id:'LpuRegionEditFormPanel',
			region: 'center',
			items:
			[{
				name: 'LpuRegion_id',
				tabIndex: -1,
				xtype: 'hidden',
				id: 'lrLpuRegion_id'
			},{
				name: 'Lpu_id',
				disabled: true,
				fieldLabel: lang['mo'],
				tabIndex: TABINDEX_LREGEW + 1,
				xtype: 'swlpusearchcombo',
				id: 'lrLpu_id'
			},{
				anchor: '100%',
				allowBlank:false,
				tabIndex: TABINDEX_LREGEW + 5,
				disabled: false,
				name: 'LpuRegionType_id',
				xtype: 'swlpuregiontypecombo',
				id: 'lrLpuRegionType_id',
				listeners:{
					'change':function (combo, newValue, oldValue) {
						this.filterLpuSectionCombo();
						this.onLpuRegionTypeChange();
					}.createDelegate(this),
					'select':function (combo) {
						combo.fireEvent('change',combo);
					}.createDelegate(this)
				}
			},{
				anchor: '100%',
				allowBlank:false,
				name: 'LpuBuilding_id',
				fieldLabel: lang['podrazdelenie'],
				tabIndex: TABINDEX_LREGEW + 10,
				lastQuery:'',
				listeners:{
					'select':function (combo) {
						combo.fireEvent('change',combo);
					}.createDelegate(this),
					'change':function (combo, newValue, oldValue) {
						this.filterLpuSectionCombo();
					}.createDelegate(this)
				},
				xtype: 'swlpubuildingglobalcombo',
				id: 'lrLpuBuilding_id'
			},{
				anchor: '100%',
				allowBlank:false,
				name: 'LpuSection_id',
				fieldLabel: lang['otdelenie'],
				lastQuery:'',
				listeners:{
					'select':function (combo) {
						combo.fireEvent('change',combo, combo.getValue());
					}.createDelegate(this),
					'change':function (combo, newValue, oldValue) {
						var LpuBuildingCombo = this.findById('lrLpuBuilding_id');
						if (Ext.isEmpty(LpuBuildingCombo.getValue())){
							LpuBuildingCombo.setValue(combo.getFieldValue('LpuBuilding_id'));
							this.filterLpuSectionCombo();
						}
					}.createDelegate(this)
				},
				tabIndex: TABINDEX_LREGEW + 15,
				xtype: 'swlpusectionglobalcombo',
				id: 'lrLpuSection_id'
			},{
				anchor: '100%',
				fieldLabel: lang['№_uchastka'],
				name: 'LpuRegion_Name',
				tabIndex: TABINDEX_LREGEW + 20,
				xtype: 'numberfield',
				id: 'lrLpuRegion_Name',
				maxValue: getRegionNick() == 'ekb' ? 99999999 : 999999,
				minValue: 1,
				autoCreate: {
					tag: "input",
					size:14,
					maxLength: getRegionNick() == 'ekb' ? "8" : (getRegionNick() == 'buryatiya' ? "2" : "6"),
					autocomplete: "off"
				},
				allowBlank:false
			},{
				anchor: '100%',
				fieldLabel: '№ участка в ТФОМС',
				name: 'LpuRegion_tfoms',
				tabIndex: TABINDEX_LREGEW + 20,
				xtype: 'numberfield',
				allowDecimals: false,
				allowNegative: false
			},{
				anchor: '100%',
				fieldLabel: lang['opisanie'],
				name: 'LpuRegion_Descr',
				tabIndex: TABINDEX_LREGEW + 25,
				xtype: 'textfield',
				id: 'lrLpuRegion_Descr',
				allowBlank:true
			}, {
				fieldLabel: lang['data_sozdaniya'],
				allowBlank:false,
				xtype: 'swdatefield',
				format: 'd.m.Y',
				tabIndex: TABINDEX_LREGEW + 30,
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				name: 'LpuRegion_begDate',
				id: 'lrLpuRegion_begDate'
			}, {
				fieldLabel : lang['data_zakryitiya'],
				xtype: 'swdatefield',
				format: 'd.m.Y',
				tabIndex: TABINDEX_LREGEW + 35,
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				name: 'LpuRegion_endDate',
				id: 'lrLpuRegion_endDate',
				allowBlank:true
			},
			this.ViewLpuRegionMedPersonal
			],
			reader: new Ext.data.JsonReader(
			{
				success: function()
				{
				//alert('success');
				}
			},
			[
				{ name: 'LpuRegion_id' },
				{ name: 'Lpu_id' },
				{ name: 'LpuSection_id' },
				{ name: 'LpuRegion_Name' },
				{ name: 'LpuRegion_tfoms' },
				{ name: 'LpuRegionType_id' },
				{ name: 'LpuRegion_Descr' },
				{ name: 'LpuRegion_begDate' },
				{ name: 'LpuRegion_endDate' }
			]
			),
			url: C_LPUREGION_SAVE
		});
		Ext.apply(this,
		{
			xtype: 'panel',
			region: 'center',
			layout: 'fit',
			border: false,
			items: [this.MainPanel]
		});
		sw.Promed.swLpuRegionEditForm.superclass.initComponent.apply(this, arguments);
	}
});