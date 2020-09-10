/**
 * swEvnUslugaOnkoChemEditWindow - окно редактирования "Химиотерапевтическое лечение"
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      MorbusOnko
 * @access       public
 * @copyright    Copyright (c) 2013 Swan Ltd.
 * @version      06.2013
 * @comment
 */
sw.Promed.swEvnUslugaOnkoChemEditWindow = Ext.extend(sw.Promed.BaseForm, {
	/* свойства */
	action: null,
	actionAdd: false,
	height: 700,
	buttonAlign: 'left',
	closable: true,
	closeAction: 'hide',
	disabledDatePeriods: null,
	draggable: true,
	formMode: 'remote',
	formStatus: 'edit',
	layout: 'border',
	listeners: {
		hide: function() {
			this.onHide();
		}
	},
	modal: true,
	width: 720,
	winTitle: langs('Химиотерапевтическое лечение'),

	/* методы */
	callback: Ext.emptyFn,
	doSave:  function(options) {
		var thas = this;

		if ( !this.form.isValid() )
		{
			sw.swMsg.show(
			{
				buttons: Ext.Msg.OK,
				fn: function() 
				{
					thas.findById('EvnUslugaOnkoChemEditForm').getFirstInvalidEl().focus(true);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

        if (!options || Ext.isEmpty(options.no_mod_check)) {
            var mod_exists = false;
            this.MorbusOnkoDrugFrame.getGrid().getStore().each(function(record) {
                if(record.get('MorbusOnkoDrug_id') > 0) {
                    mod_exists = true;
					return false;
                }
            });
            var ChemKindFocusEmpty = Ext.isEmpty(this.form.findField('OnkoUslugaChemKindType_id').getValue()) || Ext.isEmpty(this.form.findField('OnkoUslugaChemFocusType_id').getValue());
            if (!mod_exists) { //нет записсей в разделе "Препарат"
                sw.swMsg.show({
                    buttons: Ext.Msg.OK,
                    fn: function() {},
                    icon: Ext.Msg.WARNING,
                    msg: langs('Заполните раздел «Препарат»'),
                    title: ERR_INVFIELDS_TIT
                });
                return false;
            }
            else if (ChemKindFocusEmpty) {

            	sw.swMsg.show({
					buttons: Ext.Msg.OKCANCEL,
					fn: function(btn) {
						if (btn === 'ok') {
							thas.submit();
							return true;
						}
						return false;
					},
					icon: Ext.Msg.QUESTION,
					msg: langs('«Поля «Вид химиотерапии» и «Преимущественная направленность» не  заполнены. Продолжить сохранение?'),
					title: ERR_INVFIELDS_TIT
				});
			}
            else {
				this.submit();
				return true;
			}
		}
		else {
			this.submit();
			return true;
		}
	},
	onCancelAction: function() {
		var EvnUslugaOnkoChem_id = this.form.findField('EvnUslugaOnkoChem_id').getValue();

		if ( !Ext.isEmpty(EvnUslugaOnkoChem_id) && this.actionAdd == true ) {
			// удалить услугу
			// закрыть окно после успешного удаления
			var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Удаление лечения..."});
			loadMask.show();

			Ext.Ajax.request({
				callback: function(options, success, response) {
					loadMask.hide();

					if ( success ) {
						this.hide();
					}
					else {
						sw.swMsg.alert(langs('Ошибка'), langs('При удалении лечения возникли ошибки'));
						return false;
					}
				}.createDelegate(this),
				params: {
					Evn_id: EvnUslugaOnkoChem_id
				},
				url: '/?c=Evn&m=deleteEvn'
			});
		}
		else {
			this.hide();
		}
	},
	onHide: Ext.emptyFn,
	openMorbusOnkoDrugWindow: function(action) {
		if (!action || !action.toString().inlist(['add', 'edit', 'view'])) {
			return false;
		}

		if (getWnd('swMorbusOnkoDrugWindow').isVisible()) {
			getWnd('swMorbusOnkoDrugWindow').hide();
		}

		var thas = this;
		var win = this;
		var grid = this.MorbusOnkoDrugFrame.getGrid();
		var selected_record = grid.getSelectionModel().getSelected();
		var params = {
			EvnUsluga_setDT: this.form.findField('EvnUslugaOnkoChem_setDate').getValue(),
			EvnUsluga_disDT: this.form.findField('EvnUslugaOnkoChem_disDate').getValue()
		};
		params.action = action;

		params.callback = function(data) {
			//thas.MorbusOnkoDrugFrame.ViewActions.action_refresh.execute();
			grid.getStore().load({
				params: {Evn_id: data.Evn_id},
				globalFilters: {Evn_id: data.Evn_id}
			});
			win.onSaveDrug();
		};
		if (action == 'add') {
			this.onSave = null;
			var evn_id = thas.form.findField('EvnUslugaOnkoChem_id').getValue();
			if (evn_id) {
				params.formParams = {
					MorbusOnko_id: thas.form.findField('MorbusOnko_id').getValue(),
					Evn_id: evn_id,
					MorbusOnkoDrug_begDT: thas.form.findField('EvnUslugaOnkoChem_setDate').getValue(),
					MorbusOnkoVizitPLDop_id: this.MorbusOnkoVizitPLDop_id,
					MorbusOnkoLeave_id: this.MorbusOnkoLeave_id,
					MorbusOnkoDiagPLStom_id: this.MorbusOnkoDiagPLStom_id
				};
				getWnd('swMorbusOnkoDrugWindow').show(params);
			} else {
				this.onSave = function(evn_id){
					params.formParams = {
						MorbusOnko_id: thas.form.findField('MorbusOnko_id').getValue(),
						Evn_id: evn_id,
						MorbusOnkoDrug_begDT: thas.form.findField('EvnUslugaOnkoChem_setDate').getValue(),
						MorbusOnkoVizitPLDop_id: this.MorbusOnkoVizitPLDop_id,
						MorbusOnkoLeave_id: this.MorbusOnkoLeave_id,
						MorbusOnkoDiagPLStom_id: this.MorbusOnkoDiagPLStom_id
					};
					getWnd('swMorbusOnkoDrugWindow').show(params);
					thas.onSave = null;
				};
				this.doSave({no_mod_check: true});
			}
		} else {
			if (!selected_record) {
				return false;
			}
			params.formParams = selected_record.data;
			params.onHide = function() {
				grid.getView().focusRow(grid.getStore().indexOf(selected_record));
			};
			getWnd('swMorbusOnkoDrugWindow').show(params);
		}
		return true;
	},
	openMorbusOnkoDrugSelectWindow: function() {
		var wnd = this;
		var grid = this.MorbusOnkoDrugFrame.getGrid();

		var params = {
			MorbusOnko_id: this.form.findField('MorbusOnko_id').getValue(),
			Evn_id: this.form.findField('EvnUslugaOnkoChem_id').getValue()
		};

		params.callback = function() {
			grid.getStore().load({
				params: {Evn_id: params.Evn_id},
				globalFilters: {Evn_id: params.Evn_id}
			});
		};

		if (params.Evn_id) {
			getWnd('swMorbusOnkoDrugSelectWindow').show(params);
		} else {
			this.onSave = function(evn_id){
				params.Evn_id = evn_id;
				getWnd('swMorbusOnkoDrugSelectWindow').show(params);
				wnd.onSave = null;
			};
			this.doSave({no_mod_check: true});
		}
	},
	setAllowedDates: function() {
		var that = this;
		var set_dt_field = that.form.findField('EvnUslugaOnkoChem_setDate');
		var morbus_id = that.form.findField('Morbus_id').getValue();
        var morbusonkovizitpldop_id = that.MorbusOnkoVizitPLDop_id;
        var morbusonkoleave_id = that.MorbusOnkoLeave_id;
        var morbusonkodiagplstom_id = that.MorbusOnkoDiagPLStom_id;

		that.disabledDatePeriods = null;

		if (morbus_id) {
			var loadMask = new Ext.LoadMask(this.form.getEl(), {msg:langs('Загрузка...')});
			loadMask.show();
			Ext.Ajax.request({
				failure:function () {
					sw.swMsg.alert(langs('Ошибка'), langs('Не удалось получить данные с сервера'));
					loadMask.hide();
				},
				params: {
					Morbus_id: morbus_id,
                    MorbusOnkoVizitPLDop_id: morbusonkovizitpldop_id,
                    MorbusOnkoLeave_id: morbusonkoleave_id,
                    MorbusOnkoDiagPLStom_id: morbusonkodiagplstom_id
				},
				method: 'POST',
				success: function (response) {
					loadMask.hide();
					var result = Ext.util.JSON.decode(response.responseText);
					if (result[0] && Ext.isArray(result[0].disabledDatePeriods) && result[0].disabledDatePeriods.length > 0) {
						that.disabledDatePeriods = result[0].disabledDatePeriods;
						// в поле set_dt_field даём выбирать только те, что подходят к одному из периодов
						var disabledDates = [];
						for(var k in that.disabledDatePeriods) {
							if (typeof that.disabledDatePeriods[k] == 'object') {
								for (var k2 in that.disabledDatePeriods[k]) {
									if (typeof that.disabledDatePeriods[k][k2] == 'string') {
										disabledDates.push(that.disabledDatePeriods[k][k2]);
									}
								}
							}
						}
						set_dt_field.setAllowedDates(disabledDates);
						that.setAllowedDatesForDisField();
					} else {
						set_dt_field.setAllowedDates(null);
						that.setAllowedDatesForDisField();
					}
				},
				url:'/?c=MorbusOnkoSpecifics&m=getMorbusOnkoSpecTreatDisabledDates'
			});
		} else {
			set_dt_field.setAllowedDates(null);
			that.setAllowedDatesForDisField();
		}
	},
	setAllowedDatesForDisField: function() {
		var that = this;
		var set_dt_field = that.form.findField('EvnUslugaOnkoChem_setDate');
		var set_dt_value = null;
		if (!Ext.isEmpty(set_dt_field.getValue())) {
			set_dt_value = set_dt_field.getValue().format('d.m.Y');
		}
		var dis_dt_field = that.form.findField('EvnUslugaOnkoChem_disDate');

		dis_dt_field.setAllowedDates(null);

		if (Ext.isArray(that.disabledDatePeriods) && that.disabledDatePeriods.length > 0) {
			// в поле dis_dt_field даём выбирать только те, что подходят к одному из периодов соответствующим полю set_dt
			var disabledDates = [];
			for(var k in that.disabledDatePeriods) {
				if (typeof that.disabledDatePeriods[k] == 'object') {
					if (Ext.isEmpty(set_dt_value) || set_dt_value.inlist(that.disabledDatePeriods[k])) {
						for (var k2 in that.disabledDatePeriods[k]) {
							if (typeof that.disabledDatePeriods[k][k2] == 'string') {
								disabledDates.push(that.disabledDatePeriods[k][k2]);
							}
						}
					}
				}
			}
			dis_dt_field.setAllowedDates(disabledDates);
		}
	},
	setFieldsDisabled: function(d) {
		var form = this;
		this.form.items.each(function(f) 
		{
			if (f && (f.xtype!='hidden') && (f.xtype!='fieldset')  && (f.changeDisabled!==false))
			{
				f.setDisabled(d);
			}
		});
		form.MorbusOnkoDrugFrame.setReadOnly(d);
		form.MorbusOnkoDrugFrame.getAction('add_from_morbus').setDisabled(d);
		form.buttons[0].setDisabled(d);
	},
	setUslugaComplexFilter: function() {
		var
			base_form = this.form,
			UslugaCategory_SysNick = base_form.findField('UslugaCategory_id').getFieldValue('UslugaCategory_SysNick'),
			UslugaComplex_Date = base_form.findField('EvnUslugaOnkoChem_setDate').getValue();

		if (
			(
				Ext.isEmpty(UslugaCategory_SysNick)
				|| base_form.findField('UslugaComplex_id').getStore().baseParams.uslugaCategoryList == Ext.util.JSON.encode([ UslugaCategory_SysNick ])
			)
			&& (
				typeof UslugaComplex_Date != 'object'
				|| base_form.findField('UslugaComplex_id').getStore().baseParams.UslugaComplex_Date == Ext.util.Format.date(UslugaComplex_Date, 'd.m.Y')
			)
		) {
			return false;
		}

		base_form.findField('UslugaComplex_id').clearValue();
		base_form.findField('UslugaComplex_id').getStore().removeAll();
		base_form.findField('UslugaComplex_id').lastQuery = 'This query sample that is not will never appear';

		base_form.findField('UslugaComplex_id').getStore().baseParams.UslugaComplex_Date = Ext.util.Format.date(UslugaComplex_Date, 'd.m.Y');
		base_form.findField('UslugaComplex_id').setUslugaCategoryList([ UslugaCategory_SysNick ]);
	},
	show: function() {
		var thas = this;
		sw.Promed.swEvnUslugaOnkoChemEditWindow.superclass.show.apply(this, arguments);		

		this.action = '';
		this.actionAdd = false;
		this.callback = Ext.emptyFn;
		this.EvnUslugaOnkoChem_id = null;

		if ( !arguments[0] ) {
			sw.swMsg.alert(langs('Ошибка'), langs('Не указаны входные данные'), function() { thas.hide(); });
			return false;
		}
		if ( arguments[0].action ) {
			this.action = arguments[0].action;
		}
		if ( arguments[0].ARMType ) {
			this.ARMType = arguments[0].ARMType;
		}
		if ( arguments[0].callback && typeof arguments[0].callback == 'function' ) {
			this.callback = arguments[0].callback;
		}
		if ( arguments[0].owner ) {
			this.owner = arguments[0].owner;
		}
		if ( arguments[0].EvnUslugaOnkoChem_id ) {
			this.EvnUslugaOnkoChem_id = arguments[0].EvnUslugaOnkoChem_id;
		}
		if ( arguments[0].onSaveDrug && typeof arguments[0].onSaveDrug == 'function' ) { 
			this.onSaveDrug = arguments[0].onSaveDrug;
		} else {
			this.onSaveDrug = function() {};
		}

		if (!Ext.isEmpty(arguments[0].formParams.EvnPL_id))
			this.EvnPL_id = arguments[0].formParams.EvnPL_id;

        this.MorbusOnkoVizitPLDop_id = arguments[0].MorbusOnkoVizitPLDop_id || null;
        this.MorbusOnkoLeave_id = arguments[0].MorbusOnkoLeave_id || null;
        this.MorbusOnkoDiagPLStom_id = arguments[0].MorbusOnkoDiagPLStom_id || null;

		this.form.reset();

		if (!this.MorbusOnkoDrugFrame.getAction('add_from_morbus')) {
			this.MorbusOnkoDrugFrame.addActions({
				iconCls: 'add16',
				name: 'add_from_morbus',
				text: langs('Добавить препарат из специфики'),
				handler: function() {
					this.openMorbusOnkoDrugSelectWindow();
				}.createDelegate(this)
			});
		}

		var grid = this.MorbusOnkoDrugFrame.getGrid();
		grid.getStore().removeAll();

		this.form.findField('DrugTherapyLineType_id').setContainerVisible(getRegionNick() != 'kz');
		this.form.findField('DrugTherapyLoopType_id').setContainerVisible(getRegionNick() != 'kz');
		this.form.findField('UslugaCategory_id').setContainerVisible(getRegionNick() != 'kz');
		this.form.findField('UslugaComplex_id').setContainerVisible(getRegionNick() != 'kz');

		this.form.findField('UslugaComplex_id').setAllowedUslugaComplexAttributeList([ 'XimLech' ]);

		switch (arguments[0].action) {
			case 'add':
				this.setTitle(this.winTitle + langs(': Добавление'));
				this.setFieldsDisabled(false);
				this.actionAdd = true;
				break;
			case 'edit':
				this.setTitle(this.winTitle + langs(': Редактирование'));
				this.setFieldsDisabled(false);
				break;
			case 'view':
				this.setTitle(this.winTitle + langs(': Просмотр'));
				this.setFieldsDisabled(true);
				break;
		}
		
		var loadMask = new Ext.LoadMask(this.form.getEl(), {msg:langs('Загрузка...')});
		loadMask.show();

		switch ( arguments[0].action ) {
			case 'add':
				thas.form.setValues(arguments[0].formParams);
				thas.InformationPanel.load({
					Person_id: arguments[0].formParams.Person_id
				});
				//thas.form.findField('EvnUslugaOnkoChem_setDate').setValue(getGlobalOptions().date);
				loadMask.hide();
				thas.setAllowedDates();
				this.AggTypePanel.setValues([null]);
				if ( getRegionNick() != 'kz' ) {
					thas.form.findField('UslugaCategory_id').setFieldValue('UslugaCategory_SysNick', 'gost2011');
					thas.setUslugaComplexFilter();
				}
				break;

			case 'edit':
			case 'view':
				Ext.Ajax.request({
					failure:function () {
						sw.swMsg.alert(langs('Ошибка'), langs('Не удалось получить данные с сервера'));
						loadMask.hide();
						thas.hide();
					},
					params:{
						EvnUslugaOnkoChem_id: thas.EvnUslugaOnkoChem_id
					},
					success: function (response) {
						var result = Ext.util.JSON.decode(response.responseText);
						if (result[0]) {
							thas.form.setValues(result[0]);
							if(result[0].AggTypes){
								thas.AggTypePanel.setValues(result[0].AggTypes);
							} else {
								thas.AggTypePanel.setValues([null]);
							}
							thas.InformationPanel.load({
								Person_id: result[0].Person_id
							});

							var UslugaComplex_id = thas.form.findField('UslugaComplex_id').getValue();
							thas.setUslugaComplexFilter();

							if ( !Ext.isEmpty(UslugaComplex_id) ) {
								thas.form.findField('UslugaComplex_id').getStore().load({
									callback: function() {
										if ( thas.form.findField('UslugaComplex_id').getStore().getCount() > 0 ) {
											thas.form.findField('UslugaComplex_id').setValue(UslugaComplex_id);
										}
										else {
											thas.form.findField('UslugaComplex_id').clearValue();
										}
									}.createDelegate(this),
									params: {
										UslugaComplex_id: UslugaComplex_id
									}
								});
							}

							loadMask.hide();

							grid.getStore().load({
								params: {Evn_id: result[0].EvnUslugaOnkoChem_id},
								globalFilters: {Evn_id: result[0].EvnUslugaOnkoChem_id}
							});

							thas.setAllowedDates();
						}
					},
					url:'/?c=EvnUslugaOnkoChem&m=load'
				});				
				break;	
		}

		return true;
	},
	submit: function() {
		var thas = this;
		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение..."});
		loadMask.show();
		var params = {};
		params.action = this.action;
		if (this.EvnPL_id)
			params.EvnPL_id = this.EvnPL_id;
		var AggTypes = this.AggTypePanel.getValues();
		params.AggTypes = (AggTypes.length > 1 ? AggTypes.join(',') : AggTypes);
		this.form.submit({
			params: params,
			failure: function(result_form, action) 
			{
				loadMask.hide();
				if (action.result) 
				{
					if (action.result.Error_Code)
					{
						Ext.Msg.alert(langs('Ошибка #')+action.result.Error_Code, action.result.Error_Message);
					}
				}
			},
			success: function(result_form, action)
			{
				loadMask.hide();
				if (action.result.EvnUslugaCommon_id) {
					if (action.result.EvnUslugaCommon_id == -1) {
						showSysMsg('Добавленная услуга уже имеется в текущем случае лечения и не будет внесена повторно');
					}
					else {
						showSysMsg('Вы добавили новую услугу. Услуга скопирована в раздел "Услуги" текущего посещения / движения');
					}
				}
				if (typeof thas.onSave == 'function') {
					thas.onSave(action.result.EvnUslugaOnkoChem_id);
					thas.form.findField('EvnUslugaOnkoChem_id').setValue(action.result.EvnUslugaOnkoChem_id);
					thas.action = 'edit';
					thas.setTitle(thas.winTitle + langs(': Редактирование'));
				} else {
					thas.callback(thas.owner, action.result.EvnUslugaOnkoChem_id);
					thas.hide();
				}
			}
		});
	},

	/* конструктор */
	initComponent: function() {
		var thas = this;

		this.InformationPanel = new sw.Promed.PersonInformationPanelShort({
			region: 'north'
		});

		this.MorbusOnkoDrugFrame = new sw.Promed.ViewFrame({
			id: 'OnkoChemDrug',
			title: langs('2. Препарат'),
			collapsible: true,
			actions: [
				{name: 'action_add', handler: function() {
					thas.openMorbusOnkoDrugWindow('add');
				}},
				{name: 'action_edit', handler: function() {
					thas.openMorbusOnkoDrugWindow('edit');
				}},
				{name: 'action_view', handler: function() {
					thas.openMorbusOnkoDrugWindow('view');
				}},
				{name: 'action_delete'},
				{name: 'action_print'}
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 150,
			autoLoadData: false,
			border: true,
			dataUrl: '/?c=MorbusOnkoDrug&m=readList',
			paging: false,
			object: 'MorbusOnkoDrug',
			obj_isEvn: false,
			region: 'center',
			stringfields: [
				{name: 'MorbusOnkoDrug_id', type: 'int', header: 'ID', key: true},
				{name: 'MorbusOnko_id', type: 'int', hidden: true},
				{name: 'Evn_id', type: 'int', hidden: true},
				{name: 'MorbusOnkoDrug_begDT', type: 'date', hidden: true},
				{name: 'MorbusOnkoDrug_endDT', type: 'date', hidden: true},
				{name: 'MorbusOnkoDrug_DatePeriod', header: langs('Продолжительность'), width: 200, renderer: function(v, p, record){
					if (!record.get('MorbusOnkoDrug_begDT')) {
						return '';
					}
					var period = record.get('MorbusOnkoDrug_begDT').format('d.m.Y');
					if (record.get('MorbusOnkoDrug_endDT')) {
						period += ' - '+ record.get('MorbusOnkoDrug_endDT').format('d.m.Y');
					}
					return period;
				}},
				{name: 'DrugDictType_Name', type: 'string', header: langs('Справочник'), width: 100},
				{name: 'OnkoDrug_Name', type: 'string', header: langs('Препарат'), id: 'autoexpand'},
				{name: 'MorbusOnkoDrug_SumDose', type: 'string', header: langs('Суммарная доза'), width: 100}
			],
			toolbar: true
		});

		this.AggTypePanel = new sw.Promed.AddOnkoComplPanel({
			objectName: 'AggType',
			fieldLabelTitle: langs('Осложнение'),
			win: this,
			width: 670,
			buttonAlign: 'left',
			buttonLeftMargin: 150,
			labelWidth: 200,
			fieldWidth: 300,
			style: 'background: #DFE8F6'
		});
		
		this.formPanel = new Ext.form.FormPanel({
			bodyStyle:'background:#DFE8F6;padding:5px;',
			border: false,
			collapsible: true,
			frame: false,
			id: 'EvnUslugaOnkoChemEditForm',
			labelAlign: 'right',
			labelWidth: 200,
			layout: 'form',
			reader: new Ext.data.JsonReader({
				success: Ext.emptyFn
			}, [
				{name: 'EvnUslugaOnkoChem_id'},
				{name: 'EvnUslugaOnkoChem_pid'},
				{name: 'Server_id'},
				{name: 'Person_id'},
				{name: 'PersonEvn_id'},
				{name: 'MorbusOnko_id'},
				{name: 'Morbus_id'},
				{name: 'EvnUslugaOnkoChem_setDate'},
				{name: 'EvnUslugaOnkoChem_setTime'},
				{name: 'EvnUslugaOnkoChem_disDate'},
				{name: 'EvnUslugaOnkoChem_disTime'},
				{name: 'Lpu_uid'},
				{name: 'OnkoUslugaChemKindType_id'},
				{name: 'OnkoUslugaChemFocusType_id'},
				{name: 'OnkoTreatType_id'},
				{name: 'TreatmentConditionsType_id'}, 
				{name: 'AggType_id'},
				{name: 'DrugTherapyLineType_id'},
				{name: 'DrugTherapyLoopType_id'},
				{name: 'UslugaCategory_id'},
				{name: 'UslugaComplex_id'}
			]),
			region: 'center',
			title: langs('1. Лечение'),
			url: '/?c=EvnUslugaOnkoChem&m=save',

			items: [{
				name: 'EvnUslugaOnkoChem_id',
				xtype: 'hidden'
			}, {
				name: 'EvnUslugaOnkoChem_pid',
				xtype: 'hidden'
			}, {
				name: 'MorbusOnko_id',
				xtype: 'hidden'
			}, {
				name: 'Morbus_id',
				xtype: 'hidden'
			}, {
				name: 'Server_id',
				xtype: 'hidden'
			}, {
				name: 'Person_id',
				xtype: 'hidden'
			}, {
				name: 'PersonEvn_id',
				xtype: 'hidden'
			}, {
				bodyStyle: 'background: #DFE8F6;',
				border: false,
				layout: 'column',
				items: [{
					bodyStyle: 'background: #DFE8F6;',
					border: false,
					layout: 'form',
					items: [{
						fieldLabel: langs('Дата начала'),
						name: 'EvnUslugaOnkoChem_setDate',
						listeners: {
							'change': function(field, newValue) {
								thas.setAllowedDatesForDisField();
								thas.setUslugaComplexFilter();
							}
						},
						allowBlank: false,
						xtype: 'swdatefield',
						plugins: [new Ext.ux.InputTextMask('99.99.9999', false)]
					}]
				}, {
					bodyStyle: 'background: #DFE8F6;',
					border: false,
					labelWidth: 50,
					layout: 'form',
					items: [{
						allowBlank: false,
						fieldLabel: langs('Время'),
						listeners: {
							'keydown': function (inp, e) {
								if ( e.getKey() == Ext.EventObject.F4 ) {
									e.stopEvent();
									inp.onTriggerClick();
								}
							}
						},
						name: 'EvnUslugaOnkoChem_setTime',
						onTriggerClick: function() {
							var time_field = thas.form.findField('EvnUslugaOnkoChem_setTime');

							if ( time_field.disabled ) {
								return false;
							}

							setCurrentDateTime({
								callback: function() {
									thas.form.findField('EvnUslugaOnkoChem_setDate').fireEvent('change', thas.form.findField('EvnUslugaOnkoChem_setDate'), thas.form.findField('EvnUslugaOnkoChem_setDate').getValue());
								},
								dateField: thas.form.findField('EvnUslugaOnkoChem_setDate'),
								loadMask: true,
								setDate: true,
								setDateMaxValue: false,
								setDateMinValue: false,
								setTime: true,
								timeField: time_field,
								windowId: thas.id
							});
						},	
						plugins: [ new Ext.ux.InputTextMask('99:99', true) ],
						validateOnBlur: false,
						width: 60,
						xtype: 'swtimefield'
					}]
				}]
			}, {
				bodyStyle: 'background: #DFE8F6;',
				border: false,
				layout: 'column',
				items: [{
					bodyStyle: 'background: #DFE8F6;',
					border: false,
					layout: 'form',
					items: [{
						fieldLabel: langs('Дата окончания'),
						name: 'EvnUslugaOnkoChem_disDate',
						xtype: 'swdatefield',
						plugins: [new Ext.ux.InputTextMask('99.99.9999', false)]
					}]
				}, {
					bodyStyle: 'background: #DFE8F6;',
					border: false,
					labelWidth: 50,
					layout: 'form',
					items: [{
						fieldLabel: langs('Время'),
						listeners: {
							'keydown': function (inp, e) {
								if ( e.getKey() == Ext.EventObject.F4 ) {
									e.stopEvent();
									inp.onTriggerClick();
								}
							}
						},
						name: 'EvnUslugaOnkoChem_disTime',
						onTriggerClick: function() {
							var time_field = thas.form.findField('EvnUslugaOnkoChem_disTime');

							if ( time_field.disabled ) {
								return false;
							}

							setCurrentDateTime({
								dateField: thas.form.findField('EvnUslugaOnkoChem_disDate'),
								loadMask: true,
								setDate: true,
								setDateMaxValue: false,
								setDateMinValue: false,
								setTime: true,
								timeField: time_field,
								windowId: thas.id
							});
						},	
						plugins: [ new Ext.ux.InputTextMask('99:99', true) ],
						validateOnBlur: false,
						width: 60,
						xtype: 'swtimefield'
					}]
				}]
			}, {
				allowBlank: !getRegionNick().inlist([ 'astra', 'kareliya', 'penza', 'perm', 'ufa' ]),
				fieldLabel: langs('Категория услуги'),
				hiddenName: 'UslugaCategory_id',
				listeners: {
					'change': function(combo, newValue, oldValue) {
						var idx = combo.getStore().findBy(function(rec) {
							return rec.get('UslugaCategory_id') == newValue;
						});
						combo.fireEvent('select', combo, combo.getStore().getAt(idx), idx);
					},
					'select': function(combo, record) {
						thas.setUslugaComplexFilter();
					}
				},
				listWidth: 400,
				loadParams: {
					params: {
						where: "where UslugaCategory_SysNick in ('gost2011','lpu','tfoms')"
					}
				},
				width: 300,
				xtype: 'swuslugacategorycombo'
			}, {
				allowBlank: !getRegionNick().inlist([ 'astra', 'kareliya', 'penza', 'perm', 'ufa' ]),
				fieldLabel: langs('Название услуги'),
				hiddenName: 'UslugaComplex_id',
				listWidth: 700,
				to: 'EvnUslugaOnkoChem',
				width: 450,
				xtype: 'swuslugacomplexnewcombo'
			}, {
				fieldLabel: langs('Вид химиотерапии'),
				hiddenName: 'OnkoUslugaChemKindType_id',
				name: 'OnkoUslugaChemKindType_id',
				xtype: 'swcommonsprlikecombo',
				value: '',
				allowBlank: getRegionNick().inlist(['ekb']), //
				sortField:'OnkoUslugaChemKindType_Code',
				comboSubject: 'OnkoUslugaChemKindType',
				width: 300
			}, {
				fieldLabel: langs('Преимущественная направленность'),
				hiddenName: 'OnkoUslugaChemFocusType_id',
				name: 'OnkoUslugaChemFocusType_id',
				xtype: 'swcommonsprlikecombo',
				allowBlank: getRegionNick().inlist(['ekb']),//
				value: '',
				sortField:'OnkoUslugaChemFocusType_Code',
				comboSubject: 'OnkoUslugaChemFocusType',
				width: 300
			}, {
				fieldLabel: langs('Место выполнения'),
				autoLoad: true,
				hiddenName: 'Lpu_uid',
				allowBlank: !getRegionNick().inlist([ 'kareliya', 'perm', 'ufa' ]),
				xtype: 'swlpulocalcombo',
				width: 300
			}, {
				comboSubject: 'OnkoTreatType',
				fieldLabel: langs('Характер лечения'),
				hiddenName: 'OnkoTreatType_id',
				sortField:'OnkoTreatType_Code',
				width: 300,
				xtype: 'swcommonsprlikecombo'
			}, {
				fieldLabel: langs('Условие проведения лечения'),
				comboSubject: 'TreatmentConditionsType',
				xtype: 'swcommonsprlikecombo',
				width: 300
			}, 
			this.AggTypePanel,
			{
				//allowBlank: getRegionNick() == 'kz',
				//allowBlank: true,
				allowBlank: getRegionNick().inlist(['kz', 'ekb']),
				fieldLabel: langs('Линия лекарственной терапии'),
				comboSubject: 'DrugTherapyLineType',
				xtype: 'swcommonsprlikecombo',
				width: 300
			}, {
				//allowBlank: getRegionNick() == 'kz',
				//allowBlank: true,
				allowBlank: getRegionNick().inlist(['kz', 'ekb']),
				fieldLabel: langs('Цикл лекарственной терапии'),
				comboSubject: 'DrugTherapyLoopType',
				xtype: 'swcommonsprlikecombo',
				width: 300
			}]
		});

		Ext.apply(this, {
			buttons: [{
				handler: function() {
					thas.doSave();
				},
				iconCls: 'save16',
				text: BTN_FRMSAVE
			}, {
				text: '-'
			},
			HelpButton(this, 0),//todo проставить табиндексы
			{
				handler: function() {
					thas.onCancelAction();
				},
				iconCls: 'cancel16',
				text: BTN_FRMCANCEL
			}],
			items: [
				this.InformationPanel,
				{
					autoScroll: true,
					border: false,
					layout: 'form',
					region: 'center',
					items: [
						this.formPanel,
						this.MorbusOnkoDrugFrame
					]
				}
			]
		});

		sw.Promed.swEvnUslugaOnkoChemEditWindow.superclass.initComponent.apply(this, arguments);

		this.form = this.findById('EvnUslugaOnkoChemEditForm').getForm();
	}	
});