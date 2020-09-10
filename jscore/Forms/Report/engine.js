
/**
 * engine.js - функции движка
 *  используются как в репозитории так и пользователями
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package
 * @access       public
 * @copyright    Copyright (c) 2010 Swan Ltd.
 * @author       yunitsky
 * @version      30.04.2010
 */


Ext.ns('sw.reports.designer.ui.forms');

sw.reports.designer.ui.forms.ReportTester = Ext.extend(Ext.Window,{
    title      : langs('Отчет: '),
    height     : 500,
    width      : 600,
    resizable  : false,
    plain      : true,
    modal      : true,
    layout     : 'border',
    closeAction: 'close',
    insertControls : function(cont,data){
        var control = null;
        for(var i = 0; i < data.length; i++){
            var row = data[i];
            if(row.parameters) {
                control = new Ext.form.FieldSet({
                    style          : 'padding:10px',
                    autoHeight     : true,
                    collapsible    : true,
                    labelWidth     : this.reportData.maxLabel * 10 - 10,
                    title          : row.label
                });
                this.insertControls(control,row.parameters);
            }
            var label = (row.required == '1') ? '<b>' + row.label + '</b><span style="color:red">*</span>' : row.label;
            var width = row.length ? row.length : 300;
            var style = '{text-align:' + {
                l : 'left',
                c : 'center',
                r : 'right'
            }[row.align];
            style += (row.customStyle ? row.customStyle : '') + ';}';
//            alert(style);
            var conf = {
                fieldLabel : label,
                width      : width,
                style      : style,
                value      : row['default'] ? row['default']:null,
                hiddenName : row.reportId ? row.reportId : row.id,
                name       : row.reportId ? row.reportId : row.id,
                id         : 'rpt.engine.tester.' + row.id,
                engineId   : row.id,
                allowBlank : (row.required != '1'),
				allowcomboblank: (row.required != '1'),
				isFromReports: true
            };
			
			var confadd = {
			validateValue : function(value){
				// кастомная функция валидации для отчётов, с целью пометить неверно заполнеными поля помеченные звездочкой, где выбрано [Все]
					if (this.allowcomboblank) {
						this.clearInvalid();
						return true;
					}
					
					var rec = this.findRecord(this.displayField, value);
					if (rec) {
						var val = rec.get(this.valueField);
						if (val == -1) { 
							this.markInvalid("Нужно выбрать значение");
							return false;
						} else {
							this.clearInvalid();
							return true;
						}
					} else {
						this.markInvalid("Нужно выбрать значение");
						return false;
					}
				}
			}
			
            if(row.mask) conf['plugins'] = [new Ext.ux.InputTextMask(row.mask, false)]
            switch(row.type){
                case 'char' :
                    control = new Ext.form.TextField(Ext.apply(conf,{}))
                    break;
                case 'int' :
                    control = new Ext.form.NumberField(Ext.apply(conf,{
                        allowDecimals : false,
                        value: row.originalName.inlist(['param_pmuser_id'])?getGlobalOptions().pmuser_id:row.originalName.inlist(['param_pmuser_org_id'])?getGlobalOptions().org_id:(row.originalName.inlist(['param_pmIsSuperAdmin']) && isSuperAdmin())?'1':(row.originalName.inlist(['param_pmIsSuperAdmin']) && !isSuperAdmin())?'0':conf.value,
                        hidden: row.originalName.inlist(['param_pmuser_id','param_pmuser_org_id','param_pmIsSuperAdmin']),
                        hideLabel: row.originalName.inlist(['param_pmuser_id','param_pmuser_org_id','param_pmIsSuperAdmin']),
						listeners: // для задачи https://redmine.swan.perm.ru/issues/75929
						{
							'change': function(comp,value){
								if(comp.engineId == 'paramYear')
								{
									var base_form = comp.findForm().getForm();
									var paramBegDate = base_form.findField('paramBegDate');
									var paramEndDate = base_form.findField('paramEndDate');
									if(!Ext.isEmpty(paramBegDate) && !Ext.isEmpty(paramEndDate)){
										if(Ext.isEmpty(value))
										{
											paramBegDate.setValue('');
											paramEndDate.setValue('');
											paramBegDate.enable();
											paramEndDate.enable();
										}
										else if(value < 1970 || value > 2050)
										{
											Ext.Msg.alert(langs('Ошибка'),langs('Неверный формат года'));
										}
										else
										{
											paramBegDate.disable();
											paramEndDate.disable();
											paramBegDate.setValue('01.01.'+value);
											paramEndDate.setValue('31.12.'+value);
										}
									}
								}
							}.createDelegate(this)
						}
                    }));
                    break;
                case 'money' :
                    control = new Ext.form.NumberField(Ext.apply(conf,{
                        allowDecimals : true
                    }));
                    break;
				case 'time' :
                    control = new sw.Promed.TimeField(Ext.apply(conf,{
                        width  : 60,
						plugins: [ new Ext.ux.InputTextMask('99:99', true) ]
                    }));
                    break;	
                case 'date' :
                case 'datetime' :
                    if (conf['value'] && (conf['value'].toLowerCase()==langs('Сегодня') || conf['value'].toLowerCase()=='today')) {
                      conf['value'] = new Date();
                    }
                    control = new /*Ext.form.DateField*/sw.Promed.SwDateField(Ext.apply(conf,{
                       // format : 'd.m.Y',
                        width  : 120,
						stripCharsRe: false,
						listeners: // для задачи https://redmine.swan.perm.ru/issues/75929
						{
							'change': function(comp,value){
								var base_form = comp.findForm().getForm();
								var paramYear = base_form.findField('paramYear');

                                var begDate = '';
                                var endDate = '';
                                if(comp.engineId == 'paramBegDate'){
                                    var paramEndDate = base_form.findField('paramEndDate');
                                    if(!Ext.isEmpty(paramEndDate) && !Ext.isEmpty(paramEndDate.getValue()))
                                        endDate = new Date(paramEndDate.getValue());
                                    begDate = value;
                                }
                                if(comp.engineId == 'paramEndDate'){
                                    var paramBegDate = base_form.findField('paramBegDate');
                                    if(!Ext.isEmpty(paramBegDate) && !Ext.isEmpty(paramBegDate.getValue()))
                                        begDate = new Date(paramBegDate.getValue());
                                    endDate = value;
                                }

								if(!Ext.isEmpty(paramYear)){
									if(Ext.isEmpty(value))
									{
										paramYear.enable();
										paramYear.setValue('');
									}
									else{
										if(begDate!='' && endDate!='')
										{
											if(begDate>endDate){
												Ext.Msg.alert(langs('Ошибка'),langs('Дата окончания должна быть позже даты начала'));
												comp.setValue('');
												paramYear.setValue('');
												paramYear.enable();
											}
											else{
												paramYear.disable();
												if((begDate.getFullYear()==endDate.getFullYear()))
												{
													paramYear.setValue(begDate.getFullYear());
												}
												else
												{
													paramYear.setValue('');
												}
											}
										}
									}
								}
                                else
                                {
                                    if(begDate>endDate && Ext.isEmpty(paramEndDate) && !Ext.isEmpty(paramBegDate)){
                                        Ext.Msg.alert(langs('Ошибка'),langs('Дата окончания должна быть позже даты начала'));
                                        comp.setValue('');
                                    }
                                }

							}.createDelegate(this),
                            'render': function(comp)
                            {
                                var base_form = comp.findForm().getForm();
                                var paramYear = base_form.findField('paramYear');
                                var newDate = new Date();
                                if(Ext.isEmpty(paramYear))
                                {
                                    if(comp.engineId != 'paramSetBegDate' && comp.engineId != 'paramSetEndDate')
                                    comp.setValue(newDate);
                                }
                                var year = newDate.getFullYear(), month = newDate.getMonth();
                                var firstDay = new Date(year, month, 1);
                                var lastDay = new Date(year, month+1, 0);
                                if(comp.engineId == 'paramBegDayMonth'){
                                    comp.setValue(firstDay);
                                }
                                if(comp.engineId == 'paramEndDayMonth'){
                                    comp.setValue(lastDay);
                                }
                            }
						}
                      //  plugins: [new Ext.ux.InputTextMask('99.99.9999', false)]
                    }));
                    break;
                case 'bool' :
                    control = new Ext.form.Checkbox(conf);
                    break;
                case 'yesno' :
                    control = new Ext.form.ComboBox(Ext.apply(conf,{
                        width : 100,
                        mode  : 'local',
                        triggerAction : 'all',
                        store : [[1,langs('Да')],[0,langs('Нет')]]
                    //value : 1
                    }));
                    break;
                case 'person' :
                    control = new sw.Promed.SwPersonComboEx(conf);
                    break;
                case 'org'    :
					conf['validateValue'] = confadd['validateValue'];
                    //control = new sw.Promed.SwOrgComboEx(conf);
					control = new sw.Promed.SwOrgComboEx(Ext.apply(conf,{
						disabled: getGlobalOptions().isFarmacy || row.disabled,
						listeners:
						{
							'select': function(combo, record, index){
								var record = combo.getStore().getAt(index);
								combo.fireEvent('change', combo, record.get('Org_id'), -1);
							}.createDelegate(this),
							'render': function(cmp){
								if(getGlobalOptions().isFarmacy){
									cmp.getStore().load({
										callback: function(){
											var index = -1;
											index = cmp.getStore().findBy(function(rec) {
												return (rec.get('Org_id') == getGlobalOptions().org_id);
											});
											if ( index >= 0) {
												cmp.setValue(cmp.getStore().getAt(index).get('Org_id'));
												var record = cmp.getStore().getAt(index);
												cmp.fireEvent('change',cmp,getGlobalOptions().org_id,-1);
											}
										}
									});
								}
							}.createDelegate(this)
						}
					}));
                    break;
                case 'diag'   :
                    control = new sw.Promed.SwDiagCombo(Ext.apply(conf,{
                        // В новой версии отдаем кодификатор диагноза а не его ИД
                        valueField : 'Diag_id'
                    }));
                    break;
				case 'usluga'   :
                    control = new sw.Promed.SwUslugaCombo(conf);
                    break;
				case 'complex'  :
					control = new sw.Promed.SwUslugaComplexNewCombo(conf);
					break;
				case 'uslcat'   :
					control = new sw.Promed.SwUslugaCategoryCombo(conf);
					break;
                case 'drug'   :
					conf['validateValue'] = confadd['validateValue'];
                    control = new sw.Promed.SwDrugFarmCombo(Ext.apply(conf,{
                        valueField : 'Drug_id'
                    }));
                    break;
                case 'drugprep'   :
					conf['validateValue'] = confadd['validateValue'];
                    control = new sw.Promed.SwDrugPrepCombo(Ext.apply(conf,{
                        // В новой версии отдаем кодификатор диагноза а не его ИД
                        valueField : 'DrugPrepFas_id'
                    }));
                    //log(control);
                    break;
                case 'drugpack'   :
					conf['validateValue'] = confadd['validateValue'];
                    control = new sw.Promed.SwDrugPackCombo(Ext.apply(conf,{
                        // В новой версии отдаем кодификатор диагноза а не его ИД
                        valueField : 'Drug_id'
                    }));
                    //log(control);
                    break;
                case 'dataset' :
                    // Логика датасетов.
                    // Датасету назначается сторе, с параметрами от которых он зависит
                    // Также проверяется, есть ли уже на форме контрол от которого
                    // Зависит данный сторе, он должен быть выше данного, иначе ошибка
                    // Сторе навешивается листенером на все конторолы от которых он зависит
                    // и при изменении их, перегружает себя
                    var record = [];
                    for(var j = 0; j < row.fields.length; j++) record.push({
                        name : row.fields[j]
                    });
                    var depends = {
                        // Важно !!! серверу всегда отдаем базовый id параметра
                        __id     : row.originalName,
                        serverId : this.serverId,
						contentId: row.contentid
                    };
                    // Зависимости. Если не находим поле с идентификатором то
                    // считаем что всегда default значение
                    if(row.depends){
                        for(j = 0; j < row.depends.length; j++) {
                            //Проверяем есть ли компонент
                            var comp = cont.findById('rpt.engine.tester.' + row.depends[j].name);
                            if(!comp){
                                // Если нет компонента то записываем дефолт значение
                                depends[row.depends[j].name] = row.depends[j]['default'];
                            } else {
                                depends[row.depends[j].name] = comp.getValue();
                            }
                        }
                    }
                    // Создаем заранее префиксную запись
                    var preRecord = null;
                    if(row.prefixid){
                        preRecord = Ext.data.Record.create(record);
                        preRecord = new preRecord();
                        preRecord.data[row.idField] = row.prefixid;
                        preRecord.data[row.textField] = row.prefixtext;
                    }
					// добавляем кастомную валидейт-функцию
					conf['validateValue'] = confadd['validateValue'];
                    var isDisabled = true;
                    if(isUserGroup('OuzSpecMPC')){
						isDisabled = false;
					} else {
                    	var isParamSMO = false;
                    	if(row.originalName.inlist(['paramSMO']) && isUserGroup(['SMOUser']) && ! isAdmin){
							isParamSMO = true;
						}
						var isParamOMSSprTerrPrm = false;
                    	if(row.originalName.inlist(['paramOMSSprTerrPrm', 'paramLpu_IsOblast', 'paramLpuTerr', 'paramLpu', 'paramLpuDlo']) &&
							! (isAdmin || getGlobalOptions().isMinZdrav || isUserGroup(['ZagsUser'])) &&
							! (
								(row.originalName == 'paramLpu' || row.originalName== 'paramLpuDlo') &&
								(
									(isUserGroup(['SMOUser', 'TFOMSUser'])) ||
									(
										(getGlobalOptions().region.nick.inlist(['saratov', 'pskov', 'khak'])) &&
										(
											isUserGroup(['OuzChief','OuzUser','OuzAdmin']) ||
											(
												(getGlobalOptions().orgType=='touz') && isUserGroup(['OrgUser','OrgAdmin'])
											)
										)
									)
								)
							)
						){
							isParamOMSSprTerrPrm = true;
						}
						var isParamLpuBuildingByOrg = false;
                    	if(
							row.originalName.inlist(['paramLpuBuildingByOrg', 'paramLpuUnitByOrg','paramLpuSectionByOrg'])  && (Ext.isEmpty(getGlobalOptions().lpu_id) || (getGlobalOptions().lpu_id == 0))

						){
							isParamLpuBuildingByOrg = true;
						}
						var isParamLpuCmp = false;
                    	if(row.originalName == 'paramLpuCmp' && ! isSuperAdmin()){
							isParamLpuCmp = true;
						}
						var isParamLpuPC = false;
                    	if(row.originalName.inlist(['paramLpuPC']) && ! (isSuperAdmin() || isUserGroup('OuzSpec'))){
							isParamLpuPC = true;
						}
						var isParamLpuAndLpuDlo = false;
                    	if(
							(row.originalName == 'paramLpu' || row.originalName == 'paramLpuDlo')
							&& (getWnd('swWorkPlaceMZSpecWindow').isVisible() || getWnd('swPMWorkPlaceWindow').isVisible() || getWnd('swMiacWorkPlaceWindow').isVisible())
						){
							isParamLpuAndLpuDlo = true;
						}
                    	isDisabled = (isParamSMO || isParamOMSSprTerrPrm || isParamLpuBuildingByOrg || isParamLpuCmp || isParamLpuPC) && !isParamLpuAndLpuDlo;
					}
                    control = new sw.Promed.SwBaseLocalCombo(Ext.apply(conf,{
                        allowBlank     	: false,
                        minChars       	: 0,
                        mode           	: 'local',
                        triggerAction  	: 'all',
                        forceSelection 	: true,
                        // Добавил дисейбл для всех, кроме суперадмина и минздрава https://redmine.swan.perm.ru/issues/1120
                        // disabled		: (row.originalName.inlist(['paramOMSSprTerrPrm','paramLpu_IsOblast', 'paramLpuTerr', 'paramLpu', 'paramLpuDlo']) && !isAdmin && !getGlobalOptions().isMinZdrav),
						// http://redmine.swan.perm.ru/issues/20202
                        hidden			: (row.originalName.inlist(['paramLpuBuildingByContragent','paramLpuUnitByContragent','paramLpuSectionByContragent']) && (Ext.isEmpty(getGlobalOptions().lpu_id) || (getGlobalOptions().lpu_id == 0))),
                        hideLabel		: (row.originalName.inlist(['paramLpuBuildingByContragent','paramLpuUnitByContragent','paramLpuSectionByContragent']) && (Ext.isEmpty(getGlobalOptions().lpu_id) || (getGlobalOptions().lpu_id == 0))),
                        disabled		: isDisabled || row.disabled,
                        store          	: new Ext.data.Store({
                            defaultValue   : row['default'],
                            prefix     : preRecord,
                            baseParams : depends,
                            reader   : new Ext.data.JsonReader({
                                root     : 'items',
                                id       : row.idField,
                                totalProperty : 'total'
                            }, Ext.data.Record.create(record) ),
                            proxy    : new Ext.data.HttpProxy({
                                url      : sw.consts.url(sw.consts.actions.GET_PARAM_CONTENT)
                            })
                        }),
                        displayField 	: row.textField,
                        valueField   	: row.idField,
                        tpl          	: row.xtemplate ? row.xtemplate : '',
                        listeners    	: {
                            render : function(cmp){
                                for(var dep in cmp.store.baseParams){
                                    if(dep == '__id' || dep == 'serverId') continue;
                                    var bc = cmp.ownerCt.ownerCt.findById('rpt.engine.tester.' + dep);
                                    // Компонент есть. Если нет то всегда будет уходить
                                    // дефолт значение
                                    if(bc){
                                        bc.on('change',function(el){
                                            cmp.store.baseParams[el.engineId] = el.getValue();
                                            cmp.store.removeAll();
                                            cmp.store.load();
                                        },this);
                                    }
                                }
                                if(cmp.engineId == 'paramLpuRegionNew'){
                                    var base_form = cmp.findForm().getForm();
                                    if(!Ext.isEmpty(base_form.findField('paramLpuRegionType'))) {
                                        cmp.disable();
                                    } else {
                                        cmp.enable();
                                    }
                                }
                            },
                            change: function(cmp,value){
                                if(cmp.engineId == 'paramLpuRegionType'){
                                    var base_form = cmp.findForm().getForm();
                                    var paramLpuRegionNew = base_form.findField('paramLpuRegionNew');
                                    if(!Ext.isEmpty(paramLpuRegionNew)){
                                        if(Ext.isEmpty(value) || value==-1) {
                                            paramLpuRegionNew.disable();//.setAllowBlank(true);
                                        } else {
                                            paramLpuRegionNew.enable();//.setAllowBlank(false);
                                        }
                                    }
                                }
                            }.createDelegate(this)
                        },
                        initComponent : function(){
                            //Ext.form.ComboBox.superclass.initComponent.call(this);
                            sw.Promed.SwBaseLocalCombo.superclass.initComponent.call(this);
                            this.store.on('load',function(store){
                                if(store.prefix) store.insert(0,store.prefix);
                                store.ownerCt.setValue(this.defaultValue);
                            });
                        }
                    }));
                    control.store.ownerCt = control;
                    control.store.load();
                    /*{
                        callback : function(){
                            this.ownerCt.setValue(this.defaultValue);
                        }
                    });*/
                    break;
				case 'multidata':
                    var record = [];
                    for(var j = 0; j < row.fields.length; j++) record.push({
                        name : row.fields[j]
                    });
                    var depends = {
                        // Важно !!! серверу всегда отдаем базовый id параметра
                        __id     : row.originalName,
                        serverId : this.serverId,
                        contentId: row.contentid
                    };
                    // Зависимости. Если не находим поле с идентификатором то
                    // считаем что всегда default значение
                    if(row.depends){
                        for(j = 0; j < row.depends.length; j++) {
                            //Проверяем есть ли компонент
                            var comp = cont.findById('rpt.engine.tester.' + row.depends[j].name);
                            if(!comp){
                                // Если нет компонента то записываем дефолт значение
                                depends[row.depends[j].name] = row.depends[j]['default'];
                            } else {
                                depends[row.depends[j].name] = comp.getValue();
                            }
                        }
                    }
                    // Создаем заранее префиксную запись
                    var preRecord = null;
                    if(row.prefixid){
                        preRecord = Ext.data.Record.create(record);
                        preRecord = new preRecord();
                        preRecord.data[row.idField] = row.prefixid;
                        preRecord.data[row.textField] = row.prefixtext;
                    }
                    // добавляем кастомную валидейт-функцию
                    conf['validateValue'] = confadd['validateValue'];
                    control = new Ext.ux.Andrie.Select(Ext.apply(conf, {
                        fieldLabel: row.label,
                        multiSelect: true,
                        mode: 'local',
                        listWidth: 400,
                        width: 300,
						resizable: true,
                        store : new Ext.data.Store({
							defaultValue: row['default'],
							prefix: preRecord,
							baseParams: depends,
							reader: new Ext.data.JsonReader({
								root: 'items',
								id: row.idField,
								totalProperty: 'total'
							}, Ext.data.Record.create(record)),
							proxy: new Ext.data.HttpProxy({
								url: sw.consts.url(sw.consts.actions.GET_PARAM_CONTENT)
							})
						}),
                        codeField: row.idField,
                        valueField: row.idField,
                        displayField : row.textField,
                        //hiddenName: row.idField,
						hiddenName: row.originalName,
                        tpl          : row.xtemplate ? row.xtemplate : '',
                        listeners    : {
                            render : function(cmp){
                                for(var dep in cmp.store.baseParams){
                                    if(dep == '__id' || dep == 'serverId') continue;
                                    var bc = cmp.ownerCt.ownerCt.findById('rpt.engine.tester.' + dep);
                                    // Компонент есть. Если нет то всегда будет уходить
                                    // дефолт значение
                                    if(bc){
                                        bc.on('change',function(el){
											cmp.clearValue();
                                            cmp.store.baseParams[el.engineId] = el.getValue();
                                            cmp.store.removeAll();
                                            cmp.store.load();
                                        },this);
                                    }
                                }
                            }
                        }
                    }));
                    control.store.ownerCt = control;
                    control.store.load();
					control.setEditor();
                    break;
            }
            if(control) cont.add(control);
        }
    },
    initComponent : function(){
        var config = {};
        this.form = new Ext.FormPanel({
            url          : sw.consts.url(sw.consts.actions.CREATE_REPORT_URL),
            monitorValid : true,
            autoScroll : true,
            region : 'center',
            labelAlign : 'left',
            labelWidth : this.reportData.maxLabel * 10,
            defaults: {
                blankText  : langs('Поле обязательно для заполнения'),
                selectOnFocus : true
            },
            border: false,
            bodyStyle:'background:transparent;padding:10px;',
            listeners : {
                clientvalidation : function(form,valid){
                    var button = form.ownerCt.buttons[1];
                    if(valid){
                        button.enable();
                    } else {
                        button.disable();
                    }
                }
            }
        });
        // Генерируем контролы
        this.formatCombo = new Ext.form.ComboBox({
            store       : new Ext.data.JsonStore({
                autoLoad   : true,
                baseParams : {
                    serverId :  this.serverId,
					reportId : this.reportId
                },
                url     :  sw.consts.url(sw.consts.actions.GET_FORMATS),
                root    : 'items',
                fields  : [
                'ReportFormat_id',
                'ReportFormat_Name',
                'ReportFormat_Ext',
                'ReportFormat_Icon',
				'ReportFormat_Sort'
                ],
				sortInfo: {
					field: 'ReportFormat_Sort'
				},
                listeners   : {
                    load : function(comp){
					//выбираем первый
					comp.ownerCt.setValue(comp.getAt(0).get('ReportFormat_Ext'));
                    //comp.ownerCt.setValue('pdf');
                    }
                }
            }),
            width       : 160,
            allowBlank  : false,
            displayField: 'ReportFormat_Name',
            valueField  : 'ReportFormat_Ext',
            triggerAction : 'all',
            mode        : 'local',
            editable    : false,
            tpl         : '<tpl for="."><div class="x-combo-list-item"><img src="img/icons/rpt/{ReportFormat_Icon}.png">{ReportFormat_Name}</div></tpl>'
        });
        this.formatCombo.store.ownerCt = this.formatCombo;
        var width = this.reportData.maxLength + this.reportData.maxLabel * 10 + 80;
        Ext.apply(config,{
            bbar   : new Ext.Toolbar({
                items : [
                {
                    xtype : 'tbtext',
                    text  : ' Поля выделенные как <b>метка</b><span style="color:red">*</span> обязательны для заполнения'
                },
                '->',
                this.formatCombo
                ]
            }),
            title  : langs('Отчет - ') + this.reportData.report.Report_Title,
            width  : width > 600 ? width : 600,
            items  : [
            {
                region : 'north',
                height : 74,
                html   : '<img src="img/icons/rpt/report-icon.png" style="padding:10px;border:0;float:left">' +
                '<div style="padding:10px"><b>' +
                this.reportData.report.Report_Title + '</b><br>' +
                this.reportData.report.Report_Description + '</div>'
            },
            this.form
            ],
            buttons : [
			{
                text    : langs('Справка по отчету'),
                scope   : this,
                handler : this.helpReport
            },
            {
                text    : langs('Сформировать отчет'),
                scope   : this,
                handler : this.createReport
            },
            {
                text    : langs('Отменить'),
                scope   : this,
                handler : function(){
                    this.removeAll();
                    this.close()
                }
            }
            ]
        });
        Ext.apply(this,config);
        this.insertControls(this.form,this.reportData.params);
        sw.reports.designer.ui.forms.ReportTester.superclass.initComponent.call(this);
    },
	helpReport : function(){
        ShowHelp(langs('Отчёт ')+this.reportCaption);
	},
    createReport : function(){
        // Собираем все данные и отправляем на сервер
        // Получаем ЮРЛ для бирта
		var params = {
                reportId : this.reportId,
                serverId : this.serverId
        };
		var base_form = this.form.getForm();
		params.paramOMSSprTerrPrm = ( base_form.findField('paramOMSSprTerrPrm') && base_form.findField('paramOMSSprTerrPrm').getValue() ) || null;
		params.paramLpu_IsOblast = ( base_form.findField('paramLpu_IsOblast') && base_form.findField('paramLpu_IsOblast').getValue() ) || null;
		params.paramLpuTerr = ( base_form.findField('paramLpuTerr') && base_form.findField('paramLpuTerr').getValue() ) || null;
		params.paramLpu = ( base_form.findField('paramLpu') && base_form.findField('paramLpu').getValue() ) || null;
		params.paramLpuCmp = ( base_form.findField('paramLpuCmp') && base_form.findField('paramLpuCmp').getValue() ) || null;
		params.paramLpuDlo = ( base_form.findField('paramLpuDlo') && base_form.findField('paramLpuDlo').getValue() ) || null;
        params.paramSMO = ( base_form.findField('paramSMO') && base_form.findField('paramSMO').getValue() ) || null;
        params.paramOrgSMO = ( base_form.findField('paramOrgSMO') && base_form.findField('paramOrgSMO').getValue() ) || null;
		params.paramLpuDid = ( base_form.findField('paramLpuDid') && base_form.findField('paramLpuDid').getValue() ) || null;
		params.paramLpuPC = ( base_form.findField('paramLpuPC') && base_form.findField('paramLpuPC').getValue() ) || null;
		params.paramLpuHosp = ( base_form.findField('paramLpuHosp') && base_form.findField('paramLpuHosp').getValue() ) || null;
		
		base_form.submit({
            params  : params,
            scope   : this,
            success : function(form,action){
                var url = action.result.url;
                url += '&__format=' + this.formatCombo.getValue();
                this.openPrintWindow(url,800,600);
            },
            failure : function(form,action){
                Ext.Msg.alert(langs('Ошибка'),action.result.msg);
            }

        });
    },
    openPrintWindow : function(pageToLoad, width, height) {
        xposition=0;
        yposition=0;
        if ((parseInt(navigator.appVersion) >= 4 ) ) {
            xposition = (screen.width - width) / 2;
            yposition = (screen.height - height) / 2;
        }
        args = "width=" + width + ","
        + "height=" + height + ","
        + "location=0,"
        + "menubar=1,"
        + "resizable=1,"
        + "scrollbars=1,"
        + "status=0,"
        + "titlebar=0,"
        + "toolbar=1,"
        + "hotkeys=0,"
        + "screenx=" + xposition + "," //NN Only
        + "screeny=" + yposition + "," //NN Only
        + "left=" + xposition + "," //IE Only
        + "top=" + yposition; //IE Only
        window.open( pageToLoad, 'print', args );
    }

});

sw.ParamFactory = function(){

    var _checkCallback = null;
    var _getReportContentCallback = null;

    var _checkSql = function(server,Region_id,sql,callback){
        _checkCallback = callback;
        Ext.Ajax.request({
            method : 'POST',
            url : sw.consts.url(sw.consts.actions.CHECK_PARAMETER_SQL),
            success : _onSuccess,
            failure : _onFailure,
            params  : {
                serverId : server,
                sql    : sql,
				Region_id: Region_id
            }
        });
    };

    var _updateData = function(server,Region_id,sql,params,callback){
        _checkCallback = callback;
        var temp = {
            serverId : server,
            sql      : sql,
            Region_id: Region_id
        };
        Ext.apply(temp,params);
        Ext.Ajax.request({
            method : 'POST',
            url : sw.consts.url(sw.consts.actions.CHECK_PARAMETER_SQL),
            success : _onSuccess,
            failure : _onFailure,
            params  : temp
        });
    };

    var _onSuccess = function(result,request){
        var jsonData = Ext.util.JSON.decode(result.responseText);
        if(jsonData.success){
            _checkCallback('',jsonData.result.params.items,jsonData.result.data.items);

        } else {
            _checkCallback(jsonData.msg);
        }
    }

    var _onFailure = function(result,request){
        var jsonData = Ext.util.JSON.decode(result.responseText);
        _checkCallback(result,request);
    }

    var _onContentSuccess = function(result,request){
        var jsonData = Ext.util.JSON.decode(result.responseText);
        if(jsonData.success){
            _getReportContentCallback('',jsonData.result);

        } else {
            _getReportContentCallback(jsonData.msg);
        }
    }

    var _onContentFailure = function(result,request){
        var jsonData = Ext.util.JSON.decode(result.responseText);
        _getReportContentCallback(jsonData.msg);
    }

    var _getReportContent = function(server,reportId,callback){
        _getReportContentCallback = callback;
        Ext.Ajax.request({
            method : 'POST',
            url : sw.consts.url(sw.consts.actions.GET_REPORT_CONTENT),
            success : _onContentSuccess,
            failure : _onContentFailure,
            params  : {
                serverId : server,
                reportId : reportId
            }
        });
    }

    return {

        checkSql : function(server,Region_id,sql,callback){
            _checkSql(server,Region_id,sql,callback);
        },

        updateData : function(server,Region_id,sql,params,callback){
            _updateData(server,Region_id,sql,params,callback);
        },

        getReportContent : function(server,reportId,callback){
            _getReportContent(server,reportId,callback);
        }
    }
}();

sw.reports.designer.ui.forms.ReportPanel = Ext.extend(Ext.Panel,{
    title      : langs('Отчет'),
    layout     : 'border',
    insertControls : function(cont,data){
        var control = null;
        for(var i = 0; i < data.length; i++){
            var row = data[i];
            if(row.parameters) {
                control = new Ext.form.FieldSet({
                    style          : 'padding:10px',
                    autoHeight     : true,
                    collapsible    : true,
                    labelWidth     : this.reportData.maxLabel * 10 - 10,
                    title          : row.label
                });
                this.insertControls(control,row.parameters);
            }
            var label = (row.required == '1') ? '<b>' + row.label + '</b><span style="color:red">*</span>' : row.label;
            var width = row.length ? row.length : 300;
            var style = '{text-align:' + {
                l : 'left',
                c : 'center',
                r : 'right'
            }[row.align];
            style += (row.customStyle ? row.customStyle : '') + '; margin-left: 7px}';
//            alert(style);
            var conf = {
                fieldLabel : label,
                width      : width,
                style      : style,
                value      : row['default'] ? row['default']:null,
                hiddenName : row.reportId ? row.reportId : row.id,
                name       : row.reportId ? row.reportId : row.id,
                id         : 'rpt.engine.' + row.id,
                engineId   : row.id,
                disabled   : row.disabled,
                allowBlank : (row.required != '1'),
				allowcomboblank: (row.required != '1'),
				isFromReports: true
            };
			
			var confadd = {
				validateValue : function(value){
				// кастомная функция валидации для отчётов, с целью пометить неверно заполнеными поля помеченные звездочкой, где выбрано [Все]
					if (this.allowcomboblank) {
						this.clearInvalid();
						return true;
					}
					
					var rec = this.findRecord(this.displayField, value);
					if (rec) {
						var val = rec.get(this.valueField);
						if (val == -1) { 
							this.markInvalid("Нужно выбрать значение");
							return false;
						} else {
							this.clearInvalid();
							return true;
						}
					} else {
						this.markInvalid("Нужно выбрать значение");
						return false;
					}
				}
			}
			
            if(row.mask) conf['plugins'] = [new Ext.ux.InputTextMask(row.mask, false)]
            switch(row.type){
                case 'char' :
                    control = new Ext.form.TextField(Ext.apply(conf,{}))
                    break;
                case 'int' :
                    control = new Ext.form.NumberField(Ext.apply(conf,{
                        allowDecimals : false,
						value: row.originalName.inlist(['param_pmuser_id'])?getGlobalOptions().pmuser_id:row.originalName.inlist(['param_pmuser_org_id'])?getGlobalOptions().org_id:(row.originalName.inlist(['param_pmIsSuperAdmin']) && isSuperAdmin())?'1':(row.originalName.inlist(['param_pmIsSuperAdmin']) && !isSuperAdmin())?'0':conf.value,
						hidden: row.originalName.inlist(['param_pmuser_id','param_pmuser_org_id','param_pmIsSuperAdmin']),
						hideLabel: row.originalName.inlist(['param_pmuser_id','param_pmuser_org_id','param_pmIsSuperAdmin']),
						listeners: // для задачи https://redmine.swan.perm.ru/issues/75929
						{
							'change': function(comp,value){
								if(comp.engineId == 'paramYear')
								{
									var base_form = comp.findForm().getForm();
									var paramBegDate = base_form.findField('paramBegDate');
									var paramEndDate = base_form.findField('paramEndDate');
									if(!Ext.isEmpty(paramBegDate) && !Ext.isEmpty(paramEndDate)){
										if(Ext.isEmpty(value))
										{
											paramBegDate.setValue('');
											paramEndDate.setValue('');
											paramBegDate.enable();
											paramEndDate.enable();
										}
										else if(value < 1970 || value > 2050)
										{
											Ext.Msg.alert(langs('Ошибка'),langs('Неверный формат года'));
										}
										else
										{
											paramBegDate.disable();
											paramEndDate.disable();
											paramBegDate.setValue('01.01.'+value);
											paramEndDate.setValue('31.12.'+value);
										}
									}
								}
							}.createDelegate(this)
						}
                    }));
                    break;
                case 'money' :
                    control = new Ext.form.NumberField(Ext.apply(conf,{
                        allowDecimals : true
                    }));
                    break;
				case 'time' :
                    control = new sw.Promed.TimeField(Ext.apply(conf,{
                        width  : 60,
						plugins: [ new Ext.ux.InputTextMask('99:99', true) ]
                    }));
                    break;	
                case 'date' :
                case 'datetime' :
                    if (conf['value'] && (conf['value'].toLowerCase()==langs('Сегодня') || conf['value'].toLowerCase()=='today')) {
                      conf['value'] = new Date();
                    }
                    control = new sw.Promed.SwDateField(Ext.apply(conf,{
						width  : 120,
						stripCharsRe: false,
						//value: new Date(),
						listeners: // для задачи https://redmine.swan.perm.ru/issues/75929
						{
							'change': function(comp,value){
								var base_form = comp.findForm().getForm();
								var paramYear = base_form.findField('paramYear');

                                var begDate = '';
                                var endDate = '';
                                if(comp.engineId == 'paramBegDate'){
                                    var paramEndDate = base_form.findField('paramEndDate');
                                    if(!Ext.isEmpty(paramEndDate) && !Ext.isEmpty(paramEndDate.getValue()))
                                        endDate = new Date(paramEndDate.getValue());
                                    begDate = value;
                                }
                                if(comp.engineId == 'paramEndDate'){
                                    var paramBegDate = base_form.findField('paramBegDate');
                                    if(!Ext.isEmpty(paramBegDate) && !Ext.isEmpty(paramBegDate.getValue()))
                                        begDate = new Date(paramBegDate.getValue());
                                    endDate = value;
                                }

								if(!Ext.isEmpty(paramYear)){
									if(Ext.isEmpty(value))
									{
										paramYear.enable();
										paramYear.setValue('');
									}
									else{
										if(begDate!='' && endDate!='')
										{
											if(begDate>endDate){
												Ext.Msg.alert(langs('Ошибка'),langs('Дата окончания должна быть позже даты начала'));
												comp.setValue('');
												paramYear.setValue('');
												paramYear.enable();
											}
											else{
												paramYear.disable();
												if((begDate.getFullYear()==endDate.getFullYear()))
												{
													paramYear.setValue(begDate.getFullYear());
												}
												else
												{
													paramYear.setValue('');
												}
											}
										}
									}
								}
                                else
                                {
                                    if(begDate>endDate  && !Ext.isEmpty(paramEndDate) && !Ext.isEmpty(paramBegDate)){
                                        Ext.Msg.alert(langs('Ошибка'),langs('Дата окончания должна быть позже даты начала'));
                                        comp.setValue('');
                                    }
                                }
							}.createDelegate(this),
							'render': function(comp)
							{
								var base_form = comp.findForm().getForm();
								var paramYear = base_form.findField('paramYear');
                                var newDate = new Date();
								if(Ext.isEmpty(paramYear))
								{
									if(comp.engineId != 'paramSetBegDate' && comp.engineId != 'paramSetEndDate')
									comp.setValue(newDate);
								}

                                var year = newDate.getFullYear(), month = newDate.getMonth();
                                var firstDay = new Date(year, month, 1);
                                var lastDay = new Date(year, month+1, 0);
                                if(comp.engineId == 'paramBegDayMonth'){
                                    comp.setValue(firstDay);
                                }
                                if(comp.engineId == 'paramEndDayMonth'){
                                    comp.setValue(lastDay);
                                }
							}
						}
						//plugins: [new Ext.ux.InputTextMask('99.99.9999', false)]
                    }));
                    break;
                case 'bool' :
                    control = new Ext.form.Checkbox(conf);
                    break;
                case 'yesno' :
                    control = new Ext.form.ComboBox(Ext.apply(conf,{
                        width : 100,
                        mode  : 'local',
                        triggerAction : 'all',
                        store : [[1,langs('Да')],[0,langs('Нет')]]
                    //value : 1
                    }));
                    break;
                case 'person' :
                    control = new sw.Promed.SwPersonComboEx(conf);
                    break;
                case 'org'    :
					conf['validateValue'] = confadd['validateValue'];
					control = new sw.Promed.SwOrgComboEx(Ext.apply(conf,{
						disabled: getGlobalOptions().isFarmacy || row.disabled,
						listeners:
						{
							'select': function(combo, record, index){
								var record = combo.getStore().getAt(index);
								combo.fireEvent('change', combo, record.get('Org_id'), -1);
							}.createDelegate(this),
							'render': function(cmp){
								if(getGlobalOptions().isFarmacy){
									cmp.getStore().load({
										callback: function(){
											var index = -1;
											index = cmp.getStore().findBy(function(rec) {
												return (rec.get('Org_id') == getGlobalOptions().org_id);
											});
											if ( index >= 0) {
												cmp.setValue(cmp.getStore().getAt(index).get('Org_id'));
												var record = cmp.getStore().getAt(index);
												cmp.fireEvent('change',cmp,getGlobalOptions().org_id,-1);
											}
										}
									});
								}
							}.createDelegate(this)
						}
					}));
                    break;
                case 'diag'   :
                    control = new sw.Promed.SwDiagCombo(Ext.apply(conf,{
                        // В новой версии отдаем кодификатор диагноза а не его ИД
                        valueField : 'Diag_id'
                    }));
                    break;
				case 'usluga'   :
                    control = new sw.Promed.SwUslugaCombo(conf);
                    break;
				case 'complex'  :
					control = new sw.Promed.SwUslugaComplexNewCombo(conf);
					break;
				case 'uslcat'   :
					control = new sw.Promed.SwUslugaCategoryCombo(conf);
					break;
                case 'drug'   :
					conf['validateValue'] = confadd['validateValue'];
                    control = new sw.Promed.SwDrugFarmCombo(Ext.apply(conf,{
                        valueField : 'Drug_id'
                    }));
                    break;
                case 'drugprep'   :
					conf['validateValue'] = confadd['validateValue'];
                    control = new sw.Promed.SwDrugPrepCombo(Ext.apply(conf,{
						valueField : 'DrugPrepFas_id',
						hiddenName : 'DrugPrepFas_id',
						listeners:
						{
							'change': function(combo, newValue, oldValue) {
								var base_form = combo.findForm().getForm();
								var DrugCombo = base_form.findField('Drug_id');
								if ( newValue > 0 ) {
									var Drug_id = DrugCombo.getValue();
									DrugCombo.getStore().baseParams.DrugPrepFas_id = newValue;
								}
								else
								{
									// очистить второй комбо
									// DrugCombo.getStore().baseParams.DrugPrepFas_id = null;
								}
								DrugCombo.clearValue();
								DrugCombo.lastQuery = '';
								DrugCombo.getStore().removeAll();
								DrugCombo.getStore().load(
								{
									callback: function()
									{
										if (DrugCombo.getStore().getCount()>0)
											DrugCombo.setValue(DrugCombo.getStore().getAt(0).get('Drug_id'));
									}
								});
								return true;
							}.createDelegate(this)
						}
                    }));
                    //log(control);
                    break;
                case 'drugpack'   :
					conf['validateValue'] = confadd['validateValue'];
                    control = new sw.Promed.SwDrugPackCombo(Ext.apply(conf,{
                        // В новой версии отдаем кодификатор диагноза а не его ИД
                        valueField : 'Drug_id',
						hiddenName: 'Drug_id',
						listeners: {
							'change': function(combo, newValue, oldValue) {
								var base_form = combo.findForm().getForm();
								var DrugPrepFasCombo = base_form.findField('DrugPrepFas_id');
								if (DrugPrepFasCombo.getValue()=='' && (newValue > 0))
								{
									DrugPrepFasCombo.getStore().load(
									{
										params: {Drug_id: newValue},
										callback: function()
										{
											if (DrugPrepFasCombo.getStore().getCount()>0)
												DrugPrepFasCombo.setValue(DrugPrepFasCombo.getStore().getAt(0).get('DrugPrepFas_id'));
										}
									});
								}
								return true;
							}.createDelegate(this)
						}
                    }));
                    //log(control);
                    break;
				case 'dataset' :
                    // Логика датасетов.
                    // Датасету назначается сторе, с параметрами от которых он зависит
                    // Также проверяется, есть ли уже на форме контрол от которого
                    // Зависит данный сторе, он должен быть выше данного, иначе ошибка
                    // Сторе навешивается листенером на все конторолы от которых он зависит
                    // и при изменении их, перегружает себя
                    var record = [];
                    for(var j = 0; j < row.fields.length; j++) record.push({
                        name : row.fields[j]
                    });
                    var depends = {
                        // Важно !!! серверу всегда отдаем базовый id параметра
                        __id     : row.originalName,
                        serverId : this.serverId,
						contentId: row.contentid
                    };
					var url = sw.consts.url(sw.consts.actions.GET_PARAM_CONTENT);
                    // Зависимости. Если не находим поле с идентификатором то
                    // считаем что всегда default значение
                    if(row.depends){
                        for(j = 0; j < row.depends.length; j++) {
                            //Проверяем есть ли компонент
                            var comp = cont.findById('rpt.engine.' + row.depends[j].name);
                            if(!comp){
                                // Если нет компонента то записываем дефолт значение
                                depends[row.depends[j].name] = row.depends[j]['default'];
                            } else {
                                depends[row.depends[j].name] = comp.getValue();
                            }
                        }
                    }
                    // Создаем заранее префиксную запись
                    var preRecord = null;
                    if(row.prefixid){
                        preRecord = Ext.data.Record.create(record);
                        preRecord = new preRecord();
                        preRecord.data[row.idField] = row.prefixid;
                        preRecord.data[row.textField] = row.prefixtext;
                    }

					conf['validateValue'] = confadd['validateValue'];



                    var isDisabled = isUserGroup('OuzSpecMPC')?false:
						(
							(row.originalName.inlist(['paramSMO']) && isUserGroup(['SMOUser']) && !isAdmin) ||
							(
								row.originalName.inlist(['paramOMSSprTerrPrm','paramLpu_IsOblast', 'paramLpuTerr', 'paramLpu', 'paramLpuDlo']) &&
								!(isAdmin || getGlobalOptions().isMinZdrav || isUserGroup(['ZagsUser'])) &&
								!(
									(row.originalName == 'paramLpu' || row.originalName== 'paramLpuDlo') &&
									(
										(isUserGroup(['SMOUser','TFOMSUser'])) ||
										(
											(getGlobalOptions().region.nick.inlist(['saratov','pskov','khak'])) &&
											(
												isUserGroup(['OuzChief','OuzUser','OuzAdmin']) ||
												(
													(getGlobalOptions().orgType=='touz') &&
													isUserGroup(['OrgUser','OrgAdmin'])
												)
											)
										)
									)
								)
							) ||
							(
								row.originalName.inlist(['paramLpuBuildingByOrg', 'paramLpuUnitByOrg','paramLpuSectionByOrg'])  && (Ext.isEmpty(getGlobalOptions().lpu_id) || (getGlobalOptions().lpu_id == 0))
							) ||
							(
								row.originalName == 'paramLpuCmp' && !isSuperAdmin()
							) ||
							(
								(row.originalName.inlist([/*'paramLpuDid',*/'paramLpuPC']) && !(isSuperAdmin() || isUserGroup('OuzSpec')))
							) ||
							(
								row.originalName == 'paramOrgFarmacySokr' &&
								(
									(
										!Ext.isEmpty(getGlobalOptions().OrgFarmacy_id) && !(isSuperAdmin())
									) ||
									(
										Ext.isEmpty(getGlobalOptions().OrgFarmacy_id) && !(isSuperAdmin() || isUserGroup('OuzSpec') || isUserGroup('minzdravdlo'))
									)

								)
							)
						) && !(
							(row.originalName == 'paramLpu' || row.originalName== 'paramLpuDlo')
							&& (getWnd('swWorkPlaceMZSpecWindow').isVisible() || getWnd('swPMWorkPlaceWindow').isVisible() || getWnd('swMiacWorkPlaceWindow').isVisible())
						);

					//----------------------------------------------------------------------------------------------
					// Только для провизора и отчёта "реестр обеспеченных нельготных рецептов" сделать поле МО доступным для выбора.
					// и для армов стар врача нмп и диспетчера направлений нмп
					if(row.originalName == 'paramLpu'){
						if(
							(haveArmType('dpoint') && this.reportData.report.Report_FileName == 'ReceptGen_Otp_Registry.rptdesign')
						)
						{
							isDisabled = false
						}
						if(
                            (getGlobalOptions().curARMType && getGlobalOptions().curARMType.inlist(['nmpgranddoc', 'dispdirnmp', 'dispnmp'])) 
                            && 
                            (this.reportData.report.ReportCatalog_Name == 'НМП' || getRegionNick() == 'perm')
                        ){
							isDisabled = false;
							row['default'] = -1;
							url = '/?c=CmpCallCard4E&m=loadLpuWithNestedLpuBuildings';
						}
					}
					//----------------------------------------------------------------------------------------------



                    control = new sw.Promed.SwBaseLocalCombo(Ext.apply(conf,{
                        allowBlank     : false,
                        minChars       : 0,
                        mode           : 'local',
                        triggerAction  : 'all',
                        forceSelection : true,
                        // Добавил дисейбл для всех, кроме суперадмина и минздрава https://redmine.swan.perm.ru/issues/1120
                        //disabled: (row.originalName.inlist(['paramOMSSprTerrPrm','paramLpu_IsOblast', 'paramLpuTerr', 'paramLpu', 'paramLpuDlo']) && !isAdmin && !getGlobalOptions().isMinZdrav),
                        //http://redmine.swan.perm.ru/issues/20202
                        //disabled: isUserGroup('OuzSpecMPC')?false:(row.disabled || (row.originalName.inlist(['paramSMO','paramOrgSMO']) && isUserGroup(['SMOUser']) && !isAdmin) || (row.originalName.inlist(['paramOMSSprTerrPrm','paramLpu_IsOblast', 'paramLpuTerr', 'paramLpu', 'paramLpuDlo']) && !isAdmin && !getGlobalOptions().isMinZdrav && !((row.originalName == 'paramLpu') && ( (isUserGroup(['SMOUser','TFOMSUser', 'OuzSpec'])) || ((getGlobalOptions().region.nick.inlist(['saratov','pskov'])) && (isUserGroup(['OuzChief','OuzUser','OuzAdmin']) || ((getGlobalOptions().orgType=='touz') && isUserGroup(['OrgUser','OrgAdmin'])))))))),
                        hidden: (row.originalName.inlist(['paramLpuBuildingByContragent','paramLpuUnitByContragent','paramLpuSectionByContragent']) && (Ext.isEmpty(getGlobalOptions().lpu_id) || (getGlobalOptions().lpu_id == 0))),
                        hideLabel: (row.originalName.inlist(['paramLpuBuildingByContragent','paramLpuUnitByContragent','paramLpuSectionByContragent']) && (Ext.isEmpty(getGlobalOptions().lpu_id) || (getGlobalOptions().lpu_id == 0))),
                        disabled: isDisabled || row.disabled,
                        store          : new Ext.data.Store({
                            defaultValue   : (
                                    (isUserGroup(['TFOMSUser','SMOUser']) && row.originalName.inlist(['paramLpu'])) || 
                                    (isUserGroup(['TFOMSUser']) && row.originalName.inlist(['paramSMO','paramOrgSMO'])) ||
                                    (row.originalName.inlist([/*'paramLpuDid',*/'paramLpuPC']) && (isSuperAdmin() || isUserGroup('OuzSpec')))
                                ) ? -1 : row['default'],
                            prefix     : preRecord,
                            baseParams : depends,
                            reader   : new Ext.data.JsonReader({
                                root     : 'items',
                                id       : row.idField,
                                totalProperty : 'total'
                            }, Ext.data.Record.create(record) ),
                            proxy    : new Ext.data.HttpProxy({
                                url      : url
                            })
                        }),
                        displayField : row.textField,
                        valueField   : row.idField,
                        tpl          : row.xtemplate ? row.xtemplate : '',
                        listeners    : {
                            render : function(cmp){
                                for(var dep in cmp.store.baseParams){
                                    if(dep == '__id' || dep == 'serverId') continue;
                                    var bc = cmp.ownerCt.ownerCt.findById('rpt.engine.' + dep);
                                    // Компонент есть. Если нет то всегда будет уходить
                                    // дефолт значение
                                    if(bc){
                                        bc.on('change',function(el){
                                            cmp.store.baseParams[el.engineId] = el.getValue();
                                            cmp.store.removeAll();
											var checkParams = this.reportData.params;
											checkParams.forEach(function(el){
												if(el.required == 1){
													cmp.store.baseParams[el.id] = (!cmp.store.baseParams[el.id]) ? el.default : cmp.store.baseParams[el.id];
												}
											},cmp);
											cmp.store.load();
                                        },this);
                                    }
                                    if(cmp.engineId == 'paramLpuRegionNew'){
                                        var base_form = cmp.findForm().getForm();
                                        if(!Ext.isEmpty(base_form.findField('paramLpuRegionType')))
                                        {
                                            cmp.disable();
                                        }
                                        else
                                        {
                                            cmp.enable();
                                        }
                                    }
									if(cmp.engineId == 'paramLpuCmp')
									{
										cmp.setValue(getGlobalOptions().lpu_id);
									}
                                }
                            }.createDelegate(this),
                            change: function(cmp,value){
                                if(cmp.engineId == 'paramLpuRegionType'){
                                    var base_form = cmp.findForm().getForm();
                                    var paramLpuRegionNew = base_form.findField('paramLpuRegionNew');
                                    if(!Ext.isEmpty(paramLpuRegionNew)){
                                        if(Ext.isEmpty(value) || value==-1)
                                        {
                                            paramLpuRegionNew.disable();//.setAllowBlank(true);
                                        }
                                        else
                                        {
                                            paramLpuRegionNew.enable();//.setAllowBlank(false);
                                        }
                                    }
                                }
                            }.createDelegate(this)
                        },
                        initComponent : function(){
                            //Ext.form.ComboBox.superclass.initComponent.call(this);
                            sw.Promed.SwBaseLocalCombo.superclass.initComponent.call(this);
                            this.store.on('load',function(store){
                                if(store.prefix) store.insert(0,store.prefix);
								//var setDefaultValue = true;
								//store.each(function(record,index){
								//	if(record.id == store.ownerCt.value){ setDefaultValue = false; }
								//});
								//if(setDefaultValue)
								store.ownerCt.setValue(this.defaultValue);
                            });
                        }
                    }));
                    control.store.ownerCt = control;
                    control.store.load();
                    break;
				case 'multidata':
                    var record = [];
                    for(var j = 0; j < row.fields.length; j++) record.push({
                        name : row.fields[j]
                    });
                    var depends = {
                        // Важно !!! серверу всегда отдаем базовый id параметра
                        __id     : row.originalName,
                        serverId : this.serverId,
                        contentId: row.contentid
                    };
                    // Зависимости. Если не находим поле с идентификатором то
                    // считаем что всегда default значение
                    if(row.depends){
                        for(j = 0; j < row.depends.length; j++) {
                            //Проверяем есть ли компонент
                            var comp = cont.findById('rpt.engine.' + row.depends[j].name);
                            if(!comp){
                                // Если нет компонента то записываем дефолт значение
                                depends[row.depends[j].name] = row.depends[j]['default'];
                            } else {
                                depends[row.depends[j].name] = comp.getValue();
                            }
                        }
                    }
                    // Создаем заранее префиксную запись
                    var preRecord = null;
                    if(row.prefixid){
                        preRecord = Ext.data.Record.create(record);
                        preRecord = new preRecord();
                        preRecord.data[row.idField] = row.prefixid;
                        preRecord.data[row.textField] = row.prefixtext;
                    }
                    // добавляем кастомную валидейт-функцию
                    conf['validateValue'] = confadd['validateValue'];

                    //#178812. paramLpuList - только у СуперАдмина и возможно Минздрава была возможность выбирать значения МО
                    var isDisabled = (row.originalName == 'paramLpuList' && !isAdmin && !getGlobalOptions().isMinZdrav) ? true : false;

                    control = new Ext.ux.Andrie.Select(Ext.apply(conf,{
                        fieldLabel: row.label,
                        multiSelect: true,
                        mode: 'local',
                        listWidth: 400,
                        width: 300,
                        disabled: isDisabled,
						resizable: true,
                        store          : new Ext.data.Store({
                            defaultValue   : row['default'],
                            prefix     : preRecord,
                            baseParams : depends,
                            reader   : new Ext.data.JsonReader({
                                root     : 'items',
                                id       : row.idField,
                                totalProperty : 'total'
                            }, Ext.data.Record.create(record) ),
                            proxy    : new Ext.data.HttpProxy({
                                url      : sw.consts.url(sw.consts.actions.GET_PARAM_CONTENT)
                            })
                        }),
                        codeField: row.idField,
                        valueField: row.idField,
                        displayField : row.textField,
                        //hiddenName: row.idField,
						hiddenName: row.originalName,
                        tpl          : row.xtemplate ? row.xtemplate : '',
                        listeners    : {
                            render : function(cmp){
                                for(var dep in cmp.store.baseParams){
                                    if(dep == '__id' || dep == 'serverId') continue;
								   var bc = cmp.ownerCt.ownerCt.findById('rpt.engine.' + dep);
                                    // Компонент есть. Если нет то всегда будет уходить
                                    // дефолт значение
                                    if(bc){
                                        bc.on('change',function(el){
											cmp.clearValue();
                                            cmp.store.baseParams[el.engineId] = el.getValue();
                                            cmp.store.removeAll();
											var checkParams = this.reportData.params;
											checkParams.forEach(function(el){
												if(el.required == 1){
													cmp.store.baseParams[el.id] = (!cmp.store.baseParams[el.id]) ? el.default : cmp.store.baseParams[el.id];
												}
											},cmp);
											cmp.store.load();
                                        },this);
                                    }
                                }
                            }.createDelegate(this)
                        },
                        onTrigger1Click: function(){
                            this.clearValue();
                            /*
                            var st = this.getStore();
                            if(st && st.defaultValue && st.defaultValue == '-1'){
                                this.setValue(st.defaultValue);
                            }
                            */
                        }
                    }));
                    control.store.ownerCt = control;
					// @task https://jira.is-mis.ru/browse/PROMEDWEB-2408 добавлен paramLpuBuildingList
					// очистка значений "-1" из multidata
					// nameField - массив полей, которые нужно откорректировать, ключ - ID поля, значение - ID из стора
					var nameField = {
                        paramLpuFilialList: 'LpuFilial_id',
                        paramLpuList: 'Lpu_id',
						paramLpuBuildingList: 'LpuSection_id'
                    };
                    if(control.engineId && !Ext.isEmpty(nameField[control.engineId])){
                        control.store.on('load',function(store){
                            if( this.defaultValue != -1 && !Ext.isEmpty(nameField[store.baseParams.__id]) && store.ownerCt.findRecord(nameField[store.baseParams.__id], this.defaultValue) ){
                                store.ownerCt.setValue(this.defaultValue);
                            }else{
                                store.ownerCt.clearValue();
                            }
                        });
                    }
					control.store.load();
					control.setEditor();
                    break;
            }
            if(control) cont.add(control);
        }
    },
    initComponent : function(){
        var config = {};
        this.form = new Ext.FormPanel({
            url          : sw.consts.url(sw.consts.actions.CREATE_REPORT_URL),
            monitorValid : true,
            autoScroll : true,
            region : 'center',
            labelAlign : 'left',
            labelWidth : this.reportData.maxLabel * 5,
            defaults: {
                blankText  : langs('Поле обязательно для заполнения'),
                selectOnFocus : true
            },
            border: false,
            bodyStyle:'background:transparent;padding:10px;',
            listeners : {
                clientvalidation : function(form,valid){
                    var button = form.ownerCt.buttons[1];
                    if(valid){
                        if (!button.novalidate) {
                          button.enable();
                        }
                    } else {
                        button.disable();
                    }
                }
            }
        });
        // Генерируем контролы
        this.formatCombo = new Ext.form.ComboBox({
            store       : new Ext.data.JsonStore({
                autoLoad   : true,
                baseParams : {
                    serverId :  this.serverId,
					reportId : this.reportId
                },
                url     :  sw.consts.url(sw.consts.actions.GET_FORMATS),
                root    : 'items',
                fields  : [
					'ReportFormat_id',
					'ReportFormat_Name',
					'ReportFormat_Ext',
					'ReportFormat_Icon',
					'ReportFormat_Sort'
                ],
				sortInfo: {
					field: 'ReportFormat_Sort'
				},
                listeners   : {
                    load : function(comp){
						//выбираем первый
						comp.ownerCt.setValue(comp.getAt(0).get('ReportFormat_Ext'));
                    }
                }
            }),
            width       : 160,
            allowBlank  : false,
            displayField: 'ReportFormat_Name',
            valueField  : 'ReportFormat_Ext',
            triggerAction : 'all',
            mode        : 'local',
            editable    : false,
            tpl         : '<tpl for="."><div class="x-combo-list-item"><img src="img/icons/rpt/{ReportFormat_Icon}.png">{ReportFormat_Name}</div></tpl>'
        });
        this.formatCombo.store.ownerCt = this.formatCombo;
        var width = this.reportData.maxLength + this.reportData.maxLabel * 10 + 80;
        Ext.apply(config,{
            bbar   : new Ext.Toolbar({
                items : [
                {
                    xtype : 'tbtext',
                    text  : ' Поля выделенные как <b>метка</b><span style="color:red">*</span> обязательны для заполнения'
                },
                '->',
                this.formatCombo
                ]
            }),
            title  : langs('Отчет - ') + this.reportData.report.Report_Title,
            width  : width > 600 ? width : 600,
            items  : [
            {
                region : 'north',
                height : 74,
                html   : '<img src="img/icons/rpt/report-icon.png" style="padding:10px;border:0;float:left">' +
                '<div style="padding:10px"><b>' +
                this.reportData.report.Report_Title + '</b><br>' +
                this.reportData.report.Report_Description + '</div>'
            },
            this.form
            ],
            buttons : [
			{
				id: 'addToMyReports',
				scope   : this,
				text: langs('Включить в мои отчеты'),
				handler: this.updateMyPeports
			},
			'-',
			{
                text    : langs('Справка по отчету'),
                scope   : this,
                handler : this.helpReport
            },
            {
                text    : langs('Сформировать отчет'),
                scope   : this,
                handler :  this.createReport
            }
            ]
        });
        Ext.apply(this,config);
        this.insertControls(this.form,this.reportData.params);
        sw.reports.designer.ui.forms.ReportPanel.superclass.initComponent.call(this);
		//https://redmine.swan.perm.ru/issues/56057:
		//В зависимости от переданного mode меняем название кнопки
		Ext.getCmp('addToMyReports').setText(langs((this.myFolderMode=='add') ? 'Включить в мои отчеты' : 'Убрать из моих отчетов'));
    },
		urlencode: function (text)
		{
			var trans = [];
			for (var i=0x410; i<=0x44F; i++) trans[i] = i-0x350;
			trans[0x401] = 0xA8;
			trans[0x451] = 0xB8;
			var ret = [];
			for (var i=0; i<text.length; i++)
			{
				var n = text.charCodeAt(i);
				if(typeof trans[n] != 'undefined') n = trans[n];
				if(n <= 0xFF) ret.push(n);
			}
			return escape(String.fromCharCode.apply(null,ret));
		},
		urlencode_string: function (ar)
		{
			var r = ar.split('/');
			var rs = r[r.length-1].split('&');
			for (var i=0; i<rs.length; i++)
			{
				var record = rs[i].split('=');
				if (record[1])
					record[1] = encodeURIComponent(record[1]);
				rs[i] = record.join('=');
			}
			r[r.length-1] = rs.join('&');
			
			return r.join('/');
		},
	helpReport : function(){
		ShowHelp(langs('Отчёт ')+this.reportCaption);
	},
	updateMyPeports: function(){ //https://redmine.swan.perm.ru/issues/56057
		var that = this;
		Ext.Ajax.request({
			url: '/?c=ReportEndUser&m=UpdateMyReports',
			params: {
				Report_id: this.reportId,
				pmUser_id: getGlobalOptions().pmuser_id,
				upd_mode: this.myFolderMode
			},
			callback: function(opt, success, response) {
				if (success && response.responseText != '') {
					var tree = Ext.getCmp('ReportEndUserTree');
					var node = tree.getNodeById('#rcmyfolder');
					if(typeof tree.getNodeById('#rr'+that.reportId) == 'undefined')
					{
						tree.getRootNode().select();
					}
					else
					{
						var node_report = tree.getNodeById('#rr'+that.reportId);
						node_report.select();
					}
					node.reload();
					//Отчет добавили / удалилили, меняем название кнопки и Mode для случае, если пользователю взбредет в голову сразу же сделать обраное действие с отчетом
					var buttonUpd = Ext.getCmp('addToMyReports');
					if(that.myFolderMode=='add')
					{
						buttonUpd.setText(langs('Убрать из моих отчетов'));
						that.myFolderMode = 'del';
					}
					else
					{
						buttonUpd.setText(langs('Включить в мои отчеты'));
						that.myFolderMode = 'add';
					}
				}
			}
		});
	},
    createReport : function(){
        // Собираем все данные и отправляем на сервер
        // Получаем ЮРЛ для бирта
		
		var params = {
                reportId : this.reportId,
                serverId : this.serverId
        };
		var base_form = this.form.getForm();
		
		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					this.findById(this.form.id).getFirstInvalidEl().focus(false);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
		params.param_RegionCode = getGlobalOptions().region.number;
		params.param_pmuser_id = getGlobalOptions().pmuser_id;
		params.param_pmuser_org_id = getGlobalOptions().org_id;
		params.paramOMSSprTerrPrm = ( base_form.findField('paramOMSSprTerrPrm') && base_form.findField('paramOMSSprTerrPrm').getValue() ) || null;
		params.paramLpu_IsOblast = ( base_form.findField('paramLpu_IsOblast') && base_form.findField('paramLpu_IsOblast').getValue() ) || null;
		params.paramLpuTerr = ( base_form.findField('paramLpuTerr') && base_form.findField('paramLpuTerr').getValue() ) || null;
		params.paramLpu = ( base_form.findField('paramLpu') && base_form.findField('paramLpu').getValue() ) || null;
		params.paramLpuCmp = ( base_form.findField('paramLpuCmp') && base_form.findField('paramLpuCmp').getValue() ) || null;
		params.paramLpuDlo = ( base_form.findField('paramLpuDlo') && base_form.findField('paramLpuDlo').getValue() ) || null;
		params.paramSMO = ( base_form.findField('paramSMO') && base_form.findField('paramSMO').getValue() ) || null;
		params.paramOrgSMO = ( base_form.findField('paramOrgSMO') && base_form.findField('paramOrgSMO').getValue() ) || null;
		params.paramLpuDid = ( base_form.findField('paramLpuDid') && base_form.findField('paramLpuDid').getValue() ) || null;
		params.paramLpuPC = ( base_form.findField('paramLpuPC') && base_form.findField('paramLpuPC').getValue() ) || null;
		params.paramLpuHosp = ( base_form.findField('paramLpuHosp') && base_form.findField('paramLpuHosp').getValue() ) || null;

		params.paramLpuSection = ( base_form.findField('paramLpuSection') && base_form.findField('paramLpuSection').getValue() ) || null;
		params.paramLpuUnitStacD = ( base_form.findField('paramLpuUnitStacD') && base_form.findField('paramLpuUnitStacD').getValue() ) || null;
		params.paramLpuBuilding = ( base_form.findField('paramLpuBuilding') && base_form.findField('paramLpuBuilding').getValue() ) || null;

        //https://redmine.swan.perm.ru/issues/95436 - теперь если отчет находится в очереди, то можно его удалить и запустить "прямщас".
		var params_params = getAllFormFieldValues(this.form);
		params_params.reportId              = this.reportId;
		params_params.Report_id             = this.reportId;
		params_params.serverId              = this.serverId;
		params_params.param_RegionCode      = params.param_RegionCode;
		params_params.param_pmuser_id       = params.param_pmuser_id;
		params_params.param_pmuser_org_id   = params.param_pmuser_org_id;
		params_params.onlyParams            = true;
		params_params.paramOMSSprTerrPrm    = params.paramOMSSprTerrPrm;
		params_params.paramLpu_IsOblast     = params.paramLpu_IsOblast;
		params_params.paramLpuTerr          = params.paramLpuTerr;
		params_params.paramLpu              = params.paramLpu;
		params_params.paramLpuCmp           = params.paramLpuCmp;
		params_params.paramLpuDlo           = params.paramLpuDlo;
		params_params.paramSMO              = params.paramSMO;
		params_params.paramOrgSMO           = params.paramOrgSMO;
		params_params.paramLpuDid           = params.paramLpuDid;
		params_params.paramLpuPC           	= params.paramLpuPC;
		params_params.paramLpuHosp          = params.paramLpuHosp;

        var that = this;
        Ext.Ajax.request({
            url: '/?c=ReportEngine&m=CheckIfReportInQueue',
            params: params_params,
            scope   : this,
            callback: function(opt, success, response) {
                var ReportRun_id = Ext.util.JSON.decode(response.responseText);
                if(ReportRun_id > 0){
					if ( isSuperAdmin() ) {
						sw.swMsg.show({
							title: 'Вопрос',
							msg: 'Отчет находится в очереди отчетов. Удалить из очереди и сформировать сейчас?',
							buttons: Ext.Msg.YESNO,
							fn: function(buttonId, text, obj) {
								if ( 'yes' == buttonId ) {
									base_form.submit({
										params  : params,
										scope   : that,
										success : function(form,action){
											var url = action.result.url;
											url += '&__format=' + that.formatCombo.getValue();
											url += '&rand=' + Math.floor(Math.random() * 10000);
											url += '&reportQueue='+ReportRun_id;
											that.buttons[1].disable();
											that.buttons[1].novalidate = true;
											var win = that.openPrintWindow(that.urlencode_string(url),800,600);
											try {
											  that.timerID = setTimeout(function() {that.buttons[1].enable(); that.buttons[1].novalidate = false; clearTimeout(that.timerID);}.createDelegate(that), 15000, []);
											}
											catch (e) {
											  that.buttons[1].enable();
											  that.buttons[1].novalidate = false;
											}
										},
										failure : function(form,action){
											Ext.Msg.alert(langs('Ошибка'),action.result.msg);
										}
									});
								}
							}
						});
					}
					else {
						// Удалить из очереди
						sw.swMsg.show({
							title: 'Вопрос',
							msg: 'Отчет находится в очереди отчетов. Удалить из очереди?',
							buttons: Ext.Msg.YESNO,
							fn: function(buttonId, text, obj) {
								if ( 'yes' == buttonId ) {
									Ext.Ajax.request({
										params: {
											reportRuns_array: Ext.util.JSON.encode([ ReportRun_id ])
										},
										url:'/?c=ReportRun&m=deleteReportRuns',
										callback: function(options,success,response){
											var response_obj = Ext.util.JSON.decode(response.responseText);

											if ( response_obj.success == true ) {
												sw.swMsg.alert('Сообщение', 'Отчет успешно удален из очереди');
											}
											else {
												sw.swMsg.alert('Ошибка', 'Ошибка при удалении отчета из очереди');
											}
										}
									});
								}
							}
						});
					}
                }
                else
                {
                    base_form.submit({
                        params  : params,
                        scope   : that,
                        success : function(form,action){
                            var url = action.result.url;
                            url += '&__format=' + that.formatCombo.getValue();
                            url += '&rand=' + Math.floor(Math.random() * 10000);
                            that.buttons[1].disable();
                            that.buttons[1].novalidate = true;
                            /*var params_params = getAllFormFieldValues(that.form);
                            var reportGOT = [];
                            for(var key in params_params){
                                var elem = base_form.findField(key);
                                if(elem){
                                    reportGOT.push(elem.fieldLabel + ' : ' + elem.getRawValue() );
                                }
                            }
                            var rgot = reportGOT.join('|');
                            url += '&Report_ParamsArr='+rgot;*/
                            var win = that.openPrintWindow(that.urlencode_string(url),800,600);

                            try {
                              that.timerID = setTimeout(function() {that.buttons[1].enable(); that.buttons[1].novalidate = false; clearTimeout(that.timerID);}.createDelegate(that), 15000, []);
                            }
                            catch (e) {
                              that.buttons[1].enable();
                              that.buttons[1].novalidate = false;
                            }
                        },
                        failure : function(form,action){
                            Ext.Msg.alert(langs('Ошибка'),action.result.msg);
                        }

                    });
                }
            }
        });

        /*base_form.submit({
            params  : params,
            scope   : this,
            success : function(form,action){
                var url = action.result.url;
                url += '&__format=' + this.formatCombo.getValue();
                url += '&rand=' + Math.floor(Math.random() * 10000);
                this.buttons[1].disable();
                this.buttons[1].novalidate = true;
                var win = this.openPrintWindow(this.urlencode_string(url),800,600);
                try {
                  this.timerID = setTimeout(function() {this.buttons[1].enable(); this.buttons[1].novalidate = false; clearTimeout(this.timerID);}.createDelegate(this), 15000, []);
                }
                catch (e) {
                  this.buttons[1].enable();
                  this.buttons[1].novalidate = false;
                }
            },
            failure : function(form,action){
                Ext.Msg.alert(langs('Ошибка'),action.result.msg);
            }

        });*/
    },
    openPrintWindow : function(pageToLoad, width, height) {
        xposition=0;
        yposition=0;
        if ((parseInt(navigator.appVersion) >= 4 ) ) {
            xposition = (screen.width - width) / 2;
            yposition = (screen.height - height) / 2;
        }
        args = "width=" + width + ","
        + "height=" + height + ","
        + "location=0,"
        + "menubar=1,"
        + "resizable=1,"
        + "scrollbars=1,"
        + "status=0,"
        + "titlebar=0,"
        + "toolbar=1,"
        + "hotkeys=0,"
        + "screenx=" + xposition + "," //NN Only
        + "screeny=" + yposition + "," //NN Only
        + "left=" + xposition + "," //IE Only
        + "top=" + yposition; //IE Only
        var id_salt = Math.random();
        var win_id = 'print_' + Math.floor(id_salt * 10000);
        return window.open( pageToLoad, win_id, args );
    }

});