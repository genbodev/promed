sw.Promed.swVolRequestWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'swVolRequestWindow',
	maximizable: true,
	maximized: true,
	layout: 'form',
	title: 'Заявка',
	callback: Ext.emptyFn,
        Lpu_id: null,
        SprVidMp_id: null,
        VolRequest_id: null,
        RequestList_id: null,
        PlanYear: null,
        SprPlanCat_id: null,
        url_method: null,
        isCatInfo: false,
        StatusName: null,
        Status_id: null,
        css: "background-color: #ECF7FE;",
        isStat: false,
        isBoss: false,
        isMZ: false,
        resizable: false,
        searchParams: {},
        updateParams: {},
        allowPlanVisible: true,
        extraFieldsVisible: false,
        currentCategory: 0,
        c_plnkp_a: 'в т.ч. взрослые',
        listeners:{
            hide:function () 
            {
                this.onHide();
            }
        },
        infoPanelAppProfFieldsSetHidden: function (vidMp_id) {
            var fields = [
                Ext.getCmp('labelDispNabKP'),
                Ext.getCmp('labelDispNabKPValue'),
                Ext.getCmp('labelDispNabKPDev'),
                Ext.getCmp('labelDispNabKPDevValue'),
                Ext.getCmp('labelRazObrCountKP'),
                Ext.getCmp('labelRazObrCountKPValue'),
                Ext.getCmp('labelRazObrCountKPDev'),
                Ext.getCmp('labelRazObrCountKPDevValue'),
                Ext.getCmp('labelMidMedStaffKP'),
                Ext.getCmp('labelMidMedStaffKPValue'),
                Ext.getCmp('labelMidMedStaffKPDev'),
                Ext.getCmp('labelMidMedStaffKPDevValue'),
                Ext.getCmp('labelOtherPurpKP'),
                Ext.getCmp('labelOtherPurpKPValue'),
                Ext.getCmp('labelOtherPurpKPDev'),
                Ext.getCmp('labelOtherPurpKPDevValue')
            ]

            fields.forEach(
                function (item, i, fields) {
                    item.setVisible(vidMp_id == 18);
                }
            );
        },
	show: function()
        {
            var win = this;
            sw.Promed.swVolRequestWindow.superclass.show.apply(this, arguments);
            
            this.maximize();
            this.AgeTabPanel.setActiveTab(0);
            
            this.doResetFiltersVolumeTypeGrid();
            this.doFilterCat();
            this.SprVidMp_id = arguments[0].SprVidMp_id;
            if (this.SprVidMp_id == 2 || this.SprVidMp_id == 4)
            {
                Ext.getCmp('row4').show();
            }
            else
            {
                Ext.getCmp('row4').hide(); 
            }
            this.VolRequest_id = arguments[0].VolRequest_id;
            this.RequestList_id = arguments[0].VolRequestList_id;
            this.Lpu_id = arguments[0].Lpu_id;
            this.PlanYear = arguments[0].PlanYear;
            this.StatusName = arguments[0].StatusName;
            this.Status_id = arguments[0].Status_id;
            
            var grid = Ext.getCmp('idCatPlanGrid').getGrid();
            var gridCm = grid.getColumnModel();
            
            if (this.StatusName == 'Сформирована по КП' || this.StatusName == 'Утверждена по КП')
            {
                Ext.getCmp('btnUtverdit').hide();
            }
            else
            {
                Ext.getCmp('btnUtverdit').show();
            }
            
            switch (arguments[0].functionality) 
            {
                case 'stat':
                    this.isStat = true;
                    Ext.getCmp('btnUtverdit').hide();
                    break;
                case 'boss':
                    this.isBoss = true;
                    break;
                case 'mz':
                    this.isMZ = true;
                    break;
            }

            this.VolumeTypeGridFilters.items.items[0].collapse();
            this.VolumeTypeAttributeValueGridFilters.items.items[0].collapse();
            this.VolumeTypeGrid.getGrid().getStore().sort('VolCode', 'ASC');
            
            var fieldsToHide = [
                'DispNab',
                'DispNabAdults',
                'DispNabKids',
                'RazObrCount',
                'RazObrCountAdults',
                'RazObrCountKids',
                'MedReab',
                'MedReabAdults',
                'MedReabKids',
                'OtherPurp',
                'OtherPurpAdults',
                'OtherPurpKids',
                'PlanCatData_DispNabKP',
                'PlanCatData_DispNabKPAdults',
                'PlanCatData_DispNabKPKids',
                'PlanCatData_RazObrKP',
                'PlanCatData_RazObrKPAdults',
                'PlanCatData_RazObrKPKids',
                'PlanCatData_MidMedStaffKP',
                'PlanCatData_MidMedStaffKPAdults',
                'PlanCatData_MidMedStaffKPKids',
                'PlanCatData_OtherPurpKP',
                'PlanCatData_OtherPurpKPAdults',
                'PlanCatData_OtherPurpKPKids'
            ];

            fieldsToHide.forEach(
                function (item, i, fields) {
                    var idx = gridCm.findColumnIndex(item);

                    if (idx >= 0) {
                        gridCm.setHidden(idx, true);
                    }
                }
            );
            this.infoPanelAppProfFieldsSetHidden(this.SprVidMp_id);
            // скроем доп. поля (в т.ч. взрослые и в т.ч. дети) согласно задаче https://redmine.swan-it.ru/issues/185272
            fieldsToHide = [
                'fVolCount1Old',
                'fVolCount1Young',
                'fVolCount2Old',
                'fVolCount2Young',
                'fVolCount3Old',
                'fVolCount3Young',
                'VolCountOld',
                'VolCountYoung',
                'DispNabAdults',
                'DispNabKids',
                'RazObrCountAdults',
                'RazObrCountKids',
                'MedReabAdults',
                'MedReabKids',
                'OtherPurpAdults',
                'OtherPurpKids',
                'PlanCatData_PlanKpAdults',
                'PlanCatData_PlanKpKids'
            ]

            fieldsToHide.forEach(
                function (item, i, fields) {
                    var idx = gridCm.findColumnIndex(item);

                    if (idx >= 0) {
                        gridCm.setHidden(idx, win.SprVidMp_id == 18);
                    }
                }
            );
            gridCm.setHidden(gridCm.findColumnIndex('PlanCatData_KpEmer'), this.SprVidMp_id != 6);
            
            switch(this.SprVidMp_id) 
            {
                case 6:
                    gridCm.setHidden(gridCm.findColumnIndex('PlanCatData_KpEmer'), false);
                    gridCm.setHidden(gridCm.findColumnIndex('PlanCatData_PlanKP'), false);
                    gridCm.setHidden(gridCm.findColumnIndex('PlanCatData_PlanKpAdults'), false);
                    gridCm.setHidden(gridCm.findColumnIndex('PlanCatData_PlanKpKids'), false);

                    gridCm.setHidden(gridCm.findColumnIndex('PlanCatData_KP'), true);
                    gridCm.setHidden(gridCm.findColumnIndex('PlanCatData_KpKids'), true);
                    gridCm.setColumnHeader(gridCm.findColumnIndex('PlanCatData_KpAdults'), 'КП');
                break;
                case 13:
                    gridCm.setHidden(gridCm.findColumnIndex('PlanCatData_KP'), true);
                    gridCm.setHidden(gridCm.findColumnIndex('PlanCatData_KpKids'), true);
                    gridCm.setColumnHeader(gridCm.findColumnIndex('PlanCatData_KpAdults'), 'КП');
                break;
                case 18:
                    gridCm.setHidden(gridCm.findColumnIndex('PlanCatData_KP'), true);
                    gridCm.setHidden(gridCm.findColumnIndex('PlanCatData_KpKids'), true);
                    gridCm.setColumnHeader(gridCm.findColumnIndex('PlanCatData_KpAdults'), 'КП');
                    gridCm.setColumnHeader(gridCm.findColumnIndex('MedReab'), 'В т.ч. средний МП');
                    gridCm.setHidden(gridCm.findColumnIndex('DispNab'), false);
                    //gridCm.setHidden(gridCm.findColumnIndex('DispNabAdults'), false);
                    //gridCm.setHidden(gridCm.findColumnIndex('DispNabKids'), false);
                    gridCm.setHidden(gridCm.findColumnIndex('RazObrCount'), false);
                    //gridCm.setHidden(gridCm.findColumnIndex('RazObrCountAdults'), false);
                    //gridCm.setHidden(gridCm.findColumnIndex('RazObrCountKids'), false);
                    gridCm.setHidden(gridCm.findColumnIndex('MedReab'), false);
                    //gridCm.setHidden(gridCm.findColumnIndex('MedReabAdults'), false);
                    //gridCm.setHidden(gridCm.findColumnIndex('MedReabKids'), false);
                    gridCm.setHidden(gridCm.findColumnIndex('OtherPurp'), false);
                    //gridCm.setHidden(gridCm.findColumnIndex('OtherPurpAdults'), false);
                    //gridCm.setHidden(gridCm.findColumnIndex('OtherPurpKids'), false);

                    gridCm.setHidden(gridCm.findColumnIndex('PlanCatData_DispNabKP'), false);
                    gridCm.setHidden(gridCm.findColumnIndex('PlanCatData_RazObrKP'), false);
                    gridCm.setHidden(gridCm.findColumnIndex('PlanCatData_MidMedStaffKP'), false);
                    gridCm.setHidden(gridCm.findColumnIndex('PlanCatData_OtherPurpKP'), false);

                    gridCm.setHidden(gridCm.findColumnIndex('PlanCatData_DispNabKPAdults'), true);
                    gridCm.setHidden(gridCm.findColumnIndex('PlanCatData_DispNabKPKids'), true);
                    gridCm.setHidden(gridCm.findColumnIndex('PlanCatData_RazObrKPAdults'), true);
                    gridCm.setHidden(gridCm.findColumnIndex('PlanCatData_RazObrKPKids'), true);
                    gridCm.setHidden(gridCm.findColumnIndex('PlanCatData_MidMedStaffKPAdults'), true);
                    gridCm.setHidden(gridCm.findColumnIndex('PlanCatData_MidMedStaffKPKids'), true);
                    gridCm.setHidden(gridCm.findColumnIndex('PlanCatData_OtherPurpKPAdults'), true);
                    gridCm.setHidden(gridCm.findColumnIndex('PlanCatData_OtherPurpKPKids'), true);
                break;
                default:
                    this.c_plnkp_a = 'КП';
                    gridCm.setHidden(gridCm.findColumnIndex('PlanCatData_KP'), true);
                    gridCm.setHidden(gridCm.findColumnIndex('PlanCatData_KpAdults'), true);
                    gridCm.setHidden(gridCm.findColumnIndex('PlanCatData_KpKids'), true);
                break;
            }
            
                
            this.setTitle(arguments[0].openMode == 'view' ? 'Заявка МО: Просмотр' : 'Заявка МО. ' + arguments[0].NameVidMp + ': Редактирование');
                     
            this.getInfo(this.RequestList_id);
            
            gridCm.setColumnHeader(gridCm.findColumnIndex('fVolCount1'), ('Факт ' + (this.PlanYear-3) ) );
            gridCm.setColumnHeader(gridCm.findColumnIndex('fVolCount1Old'), 'в т.ч.взрослые' );
            gridCm.setColumnHeader(gridCm.findColumnIndex('fVolCount1Young'), 'в т.ч дети' );
            gridCm.setColumnHeader(gridCm.findColumnIndex('fVolCount2'), ('Факт ' + (this.PlanYear-2) ) );
            gridCm.setColumnHeader(gridCm.findColumnIndex('fVolCount2Old'), 'в т.ч.взрослые' );
            gridCm.setColumnHeader(gridCm.findColumnIndex('fVolCount2Young'), 'в т.ч дети' );
            gridCm.setColumnHeader(gridCm.findColumnIndex('fVolCount3'), ('Оценка ' + ' (по 5 мес. ' + (this.PlanYear - 1) + ')') );
            gridCm.setColumnHeader(gridCm.findColumnIndex('fVolCount3Old'),'в т.ч.взрослые' );
            gridCm.setColumnHeader(gridCm.findColumnIndex('fVolCount3Young'),'в т.ч дети' );
            
            //gridCm.setColumnHeader(gridCm.findColumnIndex('PlanCatData_KpAdults'),this.c_plnkp_a );
            gridCm.setColumnHeader(gridCm.findColumnIndex('PlanCatData_PlanKP'), 'План ' + this.PlanYear + ' по КП' );
            
            gridCm.setColumnHeader(12,('Заявка ' + this.PlanYear) );
            
            Ext.getCmp('swVolRequestWindow').doSearch(); 
            
            this.DoLayout;
	},
        
        hideRouteFields: function()
        {
            var gr = Ext.getCmp('idObjGrid').getGrid();
            var grCm = gr.getColumnModel();
            
            var fields = ['SluchCountOwn1','SluchCountZone1','Own1Adults','Zone1Adults','Own1Kids','Zone1Kids','SluchCountOwn1Adults','SluchCountZone1Adults','SluchCountOwn1Kids','SluchCountZone1Kids'];
            var fieldsIdx = [];
            
            if ([43111,43112,43123].includes(this.currentCategory))
            {
                fields.forEach(
                    function(item, i, fields)
                        {
                            if (grCm.findColumnIndex(item) >=0)
                            {
                                fieldsIdx.push(grCm.findColumnIndex(item));
                            }
                        }
                );
        
                fieldsIdx.forEach(
                    function(item, i, fieldsIdx)
                        {
                            grCm.setHidden(item, true);
                        }
                );
            }
        },

        hideMedReabFields: function ()
        {
            var fields = [
                'MedReab',
                'MedReabAdults',
                'MedReabKids'
            ];

            var grCm = Ext.getCmp('idObjGrid').getGrid().getColumnModel();

            if ([43111, 43112, 43123].includes(this.currentCategory)) {
                fields.forEach(
                    function (item, i, fields) {
                        var idx = grCm.findColumnIndex(item);

                        if (idx >= 0) {
                            grCm.setHidden(idx, true);
                        }
                    }
                );
            }
        },

        hideAgeFields: function () {
            var fields = [
                'DispNabPlanKPAdults',
                'DispNabPlanKPKids',
                'RazObrCountPlanKPAdults',
                'RazObrCountPlanKPKids',
                'MidMedStaffPlanKPAdults',
                'MidMedStaffPlanKPKids',
                'OtherPurpPlanKPAdults',
                'OtherPurpPlanKPKids'
            ];

            var grCm = Ext.getCmp('idObjGrid').getGrid().getColumnModel();

            if ([43111, 43112, 43123].includes(this.currentCategory)) {
                fields.forEach(
                    function (item, i, fields) {
                        var idx = grCm.findColumnIndex(item);

                        if (idx >= 0) {
                            grCm.setHidden(idx, true);
                        }
                    }
                );
            }
        },

        checkKP: function(grid)
        {
            var win = this;
            var gridCm = grid.getColumnModel();
            var vid = win.SprVidMp_id;
            var cat = win.currentCategory;
            var tab = win.AgeTabPanel.getActiveTab().id;
            
            if (cat !== 1 & cat !== 43118)
            {
                // https://redmine.swan.perm.ru/issues/144423
                if (vid == 6)
                {
                    if (![43131, 43132, 43133].includes(cat))
                    {
                        if (tab == 'tab_all')
                        {
                            gridCm.setHidden(gridCm.findColumnIndex('RequestData_KpAdults'), true);
                            gridCm.setHidden(gridCm.findColumnIndex('RequestData_KpKids'), true);
                            gridCm.setColumnHeader(gridCm.findColumnIndex('RequestData_KP'), 'КП травмпункт');
                        }
                        else
                        {
                            gridCm.setHidden(gridCm.findColumnIndex('RequestData_KP'), true);
                        }
                    }
                }

                // https://redmine.swan.perm.ru/issues/144483
                if (vid == 7)
                {
                    gridCm.setHidden(gridCm.findColumnIndex('RequestData_KP'), true);
                    gridCm.setHidden(gridCm.findColumnIndex('RequestData_KpKids'), true);
                    gridCm.setColumnHeader(gridCm.findColumnIndex('RequestData_KpAdults'), 'КП');
                }
            }
        },
        
        selectCat: function(record)
        {
            if (record.data.SprPlanCat_id)
            {
                // В зависимости от выбранной категории планирования, формируем грид для объектов категории планирования
                var win = Ext.getCmp('swVolRequestWindow');

                var fields = null;
                var url = '';
                var toGroup = false;
                var tabPanel = Ext.getCmp('idAgeTabPanel');
                tabPanel.unhideTabStripItem(0);
                var fv1 = 'Факт ' + (win.PlanYear - 3);
                var fv2 = 'Факт ' + (win.PlanYear - 2);
                var fv3 = 'Оценка ' + (win.PlanYear - 1) + ' <br>(по 5 мес. ' + (win.PlanYear - 1) + ')';
                var fv4 = 'Факт 6 мес.' + (win.PlanYear - 1);
                var pln = 'Заявка ' + win.PlanYear;
                var plnkp = 'План ' + win.PlanYear + ' <br>по КП';

                tabPanel.show();
                var filterAllowPlan = Ext.getCmp('idFilterAllowPlan');
                filterAllowPlan.setDisabled(false);

                if (win.currentCategory != record.data.SprPlanCat_id)
                {
                    win.AgeTabPanel.setActiveTab(0)
                }

                win.currentCategory = record.data.SprPlanCat_id;

                win.isCatInfo = false;
                win.searchParams = {};
                win.searchParams.SprPlanCat_id = win.currentCategory;

                win.searchParams.RequestList_id = win.RequestList_id;

                switch (win.AgeTabPanel.getActiveTab().id) {
                    case 'tab_adults':
                        win.searchParams.MesAgeGroup_id = 1;
                        win.allowPlanVisible = true;
                    break;
                    case 'tab_kids':
                        win.searchParams.MesAgeGroup_id = 2;
                        win.allowPlanVisible = true;
                    break;
                    case 'tab_all':
                        win.searchParams.MesAgeGroup_id = 3;
                        win.allowPlanVisible = false;
                        win.extraFieldsVisible = true;
                    break;
                }

                // Определяем выбранную категорию
                switch (win.currentCategory/*record.data.SprPlanCat_id*/) 
                {
                    // Справочная информация
                    case 1:
                        win.isCatInfo = true;
                        url = '/?c=VolPeriods&m=loadSprInfo';
                        tabPanel.hide();
                        filterAllowPlan.setDisabled(false);
                        var durationFieldHidden = false;

                        if (win.SprVidMp_id == 1)
                        {
                            durationFieldHidden = false;
                        }
                        else
                        {
                            durationFieldHidden = true;
                        }

                        fields = 
                        [
                            {name: 'SprInfo_id', type: 'int', header: 'ID', key: true},
                            {name: 'MesAgeGroup_id', header: 'Ид возрастной <br/>группы', type: 'int', width: 120, hidden: true},
                            {name: 'SprPlanObj_Code', header: 'Код', type: win.SprVidMp_id == 8 ? 'string' : 'float', width: win.SprVidMp_id == 8 ? 152 : 45},
                            {name: 'SprPlanObj_Name', header: 'Наименование', type: 'string', autoexpand: false,width: 410},
                            {name: 'Duration', header: 'Cредняя <br/>длительность', type: 'float', hidden: durationFieldHidden, width: 140, editor: new Ext.form.NumberField(), css : win.css},
                            {name: 'EstabPostCount', header: 'Штатные <br/>должности', type: 'float', hidden: (win.SprVidMp_id==10), width: 100, editor: new Ext.form.NumberField(), css : win.css},
                            {name: 'ActivePostCount', header: 'Занятые <br/>должности', type: 'float', hidden: (win.SprVidMp_id==10), editor: new Ext.form.NumberField(), css : win.css},
                            {name: 'IndividCount', header: 'Физ. лица', type: 'int', hidden: (win.SprVidMp_id==10), editor: new Ext.form.NumberField(), css : win.css},
                            {name: 'LpuLicence_id', header: 'Ид лицензии', type: 'string', hidden:true},
                            {name: 'TeamCount', header: 'Кол-во бригад', type: 'int', hidden: !(win.SprVidMp_id==10), editor: new Ext.form.NumberField(), css : win.css},
                            {name: 'LpuLicence_Num', header: '№ лицензии', type: 'string', hidden: (win.SprVidMp_id==10), editor: new Ext.form.ComboBox(
                                {   id: 'idCbLic',
                                    mode: 'local',
                                    typeCode: 'string',
                                    orderby: 'LpuLicence_id',
                                    editable: false,
                                    allowBlank: true,
                                    triggerAction: 'all', 
                                    displayField: 'LpuLicence_Num',
                                    valueField: 'LpuLicence_Num',
                                    listWidth: 300,
                                    listeners: 
                                    {
                                        'select': function(combo, record, index) 
                                        {
                                            var wnd = Ext.getCmp('swVolRequestWindow');

                                            var grid = Ext.getCmp('idObjGrid');
                                            var rec = grid.getGrid().getSelectionModel().getSelected();
                                            rec.set('LpuLicence_Num',record.data.LpuLicence_Num);
                                            rec.set('LpuLicence_id',record.data.LpuLicence_id);
                                            rec.set('LpuLicence_begDate',record.data.LpuLicence_begDate);
                                            this.collapse();
                                        }
                                    },
                                    store: new Ext.data.Store({
                                        autoLoad: true,
                                        reader: new Ext.data.JsonReader(
                                            {id: 'LpuLicence_id'},
                                            [
                                                {name: 'LpuLicence_id', mapping: 'LpuLicence_id'},
                                                {name: 'Lpu_id', mapping: 'Lpu_id'},
                                                {name: 'LpuLicence_Ser', mapping: 'LpuLicence_Ser'},
                                                {name: 'LpuLicence_Num', mapping: 'LpuLicence_Num'},
                                                {name: 'LpuLicence_setDate', mapping: 'LpuLicence_setDate'},
                                                {name: 'LpuLicence_RegNum', mapping: 'LpuLicence_RegNum'},
                                                {name: 'VidDeat_id', mapping: 'VidDeat_id'},
                                                {name: 'LpuLicence_begDate', mapping: 'LpuLicence_begDate'},
                                                {name: 'LpuLicence_endDate', mapping: 'LpuLicence_endDate'}
                                            ]
                                        ),                                                                        
                                        url: '/?c=VolPeriods&m=loadLicenceList&lpu_id=' + win.Lpu_id
                                    }),
                                    tpl: new Ext.XTemplate(
                                                    '<tpl for="."><div class="x-combo-list-item">',
                                                    '<table style="border: 0;">',
                                                    '<td style="width: 45px;"><font color="red">{LpuLicence_id}&nbsp;</font></td>',
                                                    //'<td style="width: 45px;">{Lpu_id}&nbsp;</td>',
                                                    '<td>',
                                                            '<div style="font-weight: bold;">{[Ext.isEmpty(values.LpuLicence_Ser)?"":"Серия: " + values.LpuLicence_Ser]}</div>',
                                                            '<div style="font-weight: bold;">{[Ext.isEmpty(values.LpuLicence_Num)?"":"Номер: " + values.LpuLicence_Num]}</div>',
                                                                    //'<div style="font-size: 10px;">{PostMed_Name}{[!Ext.isEmpty(values.LpuLicence_begDate) ? ", ст." : ""]} {LpuLicence_begDate}</div>',
                                                            '<div style="font-size: 10px;">{[!Ext.isEmpty(values.LpuLicence_begDate) ? "Дата начала лицензии: " + values.LpuLicence_begDate:""]} </div>',
                                                            '<div style="font-size: 10px;">{[!Ext.isEmpty(values.LpuLicence_endDate) ? "Дата окончания лицензии: " + values.LpuLicence_endDate:""]} </div>',
                                                            //'<div style="font-size: 10px;">{[!Ext.isEmpty(values.Lpu_id) && values.Lpu_id != getGlobalOptions().lpu_id?values.Lpu_Name:""]}</div>',
                                                    '</td>',
                                                    '</tr></table>',
                                                    '</div></tpl>',
                                                    {
                                                            formatWorkDataEndDate: function(endDate) {
    //                                                                                                                                                        var fixed = (typeof endDate == 'object' ? Ext.util.Format.date(endDate, 'd.m.Y') : endDate);
    //                                                                                                                                                        return fixed;
                                                            }
                                                    })

                                    }
                                ), css : win.css, width: 120},
                            {name: 'LpuLicence_begDate', header: 'Дата выдачи <br/>лицензии', type: 'string', hidden: (win.SprVidMp_id==10), css : win.css},
                            {name: 'SpecCertif_Num', header: '№ сертификата <br/>специалиста', type: 'int', hidden: (win.SprVidMp_id==10), editor: new Ext.form.TextField(), css : win.css, width: 120},
                            {name: 'SpecCertif_endDate', header: 'Срок окончания <br/>сертификата',  type: 'date', hidden: (win.SprVidMp_id==10), editor: new Ext.form.DateField({format: 'd.m.Y'}), css : win.css},
                            //{name: 'RazObrCount', header: 'Кол-во посещений в <br>обращении из Заявки <br>на ' + win.PlanYear +  ' г. (кратн. не менее 2)',  type: 'int', hidden: !(win.SprVidMp_id==13), editor: new Ext.form.NumberField(), css : win.css, width: 170},
                            {name: 'Comment', header: 'Комментарий', type: 'string', hidden: (win.SprVidMp_id==10), editor: new Ext.form.TextField(), css : win.css, width: 400}
                        ];
                    break;

                    // КСГ
                    case 2:
                        toGroup = true;

                        fields = 
                        [
                            
                            {name: 'RequestData_id', type: 'int', header: 'ID', key: true},
                            {name: 'RN', type: 'int', header: ' ', width: 1, key: false, hidden: true},
                            {name: 'MesAgeGroup_id', header: 'Ид возрастной группы', type: 'int', width: 120, hidden: true},
                            {name: 'SprPlanObj_Code', header: 'Код КПГ', type: 'int', hidden: true},
                            {name: 'SprPlanObj_Name', header: 'Наименование <br>КПГ', type: 'string', width: 207, group:true, hidden: true},
                            {name: 'KpgCurKf', header: 'Коэф. <br>КПГ', type: 'string', hidden: true},
                            {name: 'KsgCur', header: 'Код КСГ', type: 'float'},
                            {name: 'KsgCurName', header: 'Наименование <br>КСГ', type: 'string', width: 307},
                            {name: 'KsgCurKf', header: 'Коэф. <br>КСГ', type: 'float'},
                            {name: 'KsgNew', header: 'КСГ ' + win.PlanYear, type: 'float'},
                            {name: 'KsgNewName', header: 'Наименование <br>КСГ ' + win.PlanYear, type: 'string', width: 207},
                            {name: 'KsgKsz', header: 'Коэф. <br>КСГ ' + win.PlanYear, type: 'string', width: 207},
                            {name: 'RequestData_AllowPlan', header: 'Разрешить <br>план.', type: win.isMZ ? 'checkcolumnedit' : 'checkbox', width: 100, hidden: !win.allowPlanVisible},
                            {name: 'VolCount1', header: fv1, type: 'int', editor: new Ext.form.NumberField()},
                            {name: 'VolCount2', header: fv2, type: 'int', editor: new Ext.form.NumberField()},
                            {name: 'VolCount3', header: fv3, type: 'int', width: 170, editor: new Ext.form.NumberField()},
                            {name: 'VolCount4', header: fv4, type: 'int', width: 120, editor: new Ext.form.NumberField()},
                            {name: 'RequestData_Plan', header: pln, type: 'int',  editor: new Ext.form.NumberField(), css : win.css},
                            {name: 'RequestData_PlanOld', header: 'В т.ч взрослые', type: 'int', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_PlanYoung', header: 'В т.ч дети', type: 'int', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_KP', header: 'КП', type: 'float',  editor: new Ext.form.NumberField(), css : win.css},
                            {name: 'RequestData_KpAdults', header: 'в т.ч взрослые', type: 'float', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_KpKids', header: 'в т.ч дети', type: 'float', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_PlanKP', header: plnkp, type: 'int',  editor: new Ext.form.NumberField(), css : win.css},
                            {name: 'RequestData_PlanKPOld', header: 'В т.ч взрослые', type: 'int', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_PlanKPYoung', header: 'В т.ч дети', type: 'int', hidden: !win.extraFieldsVisible}
                        ];

                        switch (win.SprVidMp_id)
                        {
                            case 2:
                                url = '/?c=VolPeriods&m=loadRequestDataStacKsg';
                            break;
                            case 4:
                                url = '/?c=VolPeriods&m=loadRequestDataDSKSG';
                            break;
                        }
                    break;

                    // Кол-во случаев госпитализаций(без ВМП)
                    case 3:
                         switch (win.SprVidMp_id)
                        {
                            case 1:
                                toGroup = false;
                                url = '/?c=VolPeriods&m=loadRequestDataStac';
                            break;
                        }

                        fields = 
                        [
                            {name: 'RequestData_id', type: 'int', header: 'ID', key: true},
                            {name: 'MesAgeGroup_id', header: 'Ид возрастной группы', type: 'int', width: 120, hidden: true},
                            {name: 'SprPlanObj_Code', header: 'Код', type: 'float', width: 45},
                            {name: 'SprPlanObj_Name', header: 'Наименование', type: 'string', width: 300 , autoexpand: true},
                            {name: 'RequestData_AllowPlan', header: 'Разрешить <br>план.', type: win.isMZ == true ? 'checkcolumnedit' : 'checkbox', width: 100, hidden: !win.allowPlanVisible},
                            {name: 'VolCount1', header: fv1, type: 'int', editor: new Ext.form.NumberField()},
                            {name: 'VolCount2', header: fv2, type: 'int', editor: new Ext.form.NumberField()},
                            {name: 'VolCount3', header: fv3, type: 'int', width: 170, editor: new Ext.form.NumberField()},
                            {name: 'VolCount4', header: fv4, type: 'int', width: 120, editor: new Ext.form.NumberField()},
                            {name: 'RequestData_Plan', header: pln, type: 'int',  editor: new Ext.form.NumberField(), css : win.css},
                            {name: 'RequestData_PlanOld', header: 'В т.ч <br>взрослые', type: 'int', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_PlanYoung', header: 'В т.ч <br>дети', type: 'int', hidden: !win.extraFieldsVisible},
                            {name: 'SluchCountOwn1', header: 'Кол-во по <br>маршруту для МО', type: 'int', width: 150,  editor: new Ext.form.NumberField(), css : win.css},
                            {name: 'SluchCountZone1', header: 'Кол-во по <br>маршруту для <br>зоны ответств.', type: 'int', width: 150,  editor: new Ext.form.NumberField(), css : win.css},
                            {name: 'SluchCountOwn2', header: 'Кол-во на <br>ММЦ для МО', type: 'int', width: 150,  editor: new Ext.form.NumberField(), css : win.css},
                            {name: 'SluchCountZone2', header: 'Кол-во ММЦ <br>для зоны ответств.', type: 'int', width: 150,  editor: new Ext.form.NumberField(), css : win.css},
                            {name: 'RequestData_AvgDur', header: 'Средняя <br>продолжительность', type: 'float', /*width: 140, */ editor: new Ext.form.NumberField(), css : win.css},
                            {name: 'RequestData_BedCount', header: 'Количество <br>коек', type: 'int', width: 110,  editor: new Ext.form.NumberField(), css : win.css},
                            {name: 'RequestData_KP', header: 'КП', type: 'float',  editor: new Ext.form.NumberField(), css : win.css},
                            {name: 'RequestData_KpAdults', header: 'в т.ч взрослые', type: 'float', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_KpKids', header: 'в т.ч дети', type: 'float', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_PlanKP', header: plnkp, type: 'int',  editor: new Ext.form.NumberField(), css : win.css},
                            {name: 'RequestData_PlanKPOld', header: 'В т.ч <br>взрослые', type: 'int', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_PlanKPYoung', header: 'В т.ч <br>дети', type: 'int', hidden: !win.extraFieldsVisible}
                        ];
                    break;

                    // ВМП
                    case 4:
                        switch (win.SprVidMp_id)
                        {
                            case 1:
                                toGroup = false;
                                url = '/?c=VolPeriods&m=loadRequestDataStac';
                                //win.extraFieldsVisible = true;
                                fields = [
                                
                                {name: 'RequestData_id', type: 'int', header: 'ID', key: false},
                                {name: 'RN', type: 'int', header: ' ', width: 1, key: false, hidden: true},
                                {name: 'MesAgeGroup_id', header: 'Ид возрастной группы', type: 'int', width: 120, hidden: true},
                                {name: 'SprPlanObj_Code', header: 'Код', type: 'float', width: 45},
                                {name: 'SprPlanObj_Name', header: 'Наименование', type: 'string', group:toGroup, hidden: false, width: 410 , autoexpand: false},
                                {name: 'RequestData_AllowPlan', header: 'Разрешить план.', type: win.isMZ == true ? 'checkcolumnedit' : 'checkbox', width: 100, hidden: !win.allowPlanVisible},
                                {name: 'VolCount1', header: fv1, type: 'int', editor: new Ext.form.NumberField()},
                                {name: 'VolCount2', header: fv2, type: 'int', editor: new Ext.form.NumberField()},
                                {name: 'VolCount3', header: fv3, type: 'int', width: 170, editor: new Ext.form.NumberField()},
                                {name: 'VolCount4', header: fv4, type: 'int', width: 120, editor: new Ext.form.NumberField()},
                                {name: 'RequestData_Plan', header: pln, type: 'int',  editor: new Ext.form.NumberField(), css : win.css},
                                {name: 'RequestData_PlanOld', header: 'В т.ч взрослые', type: 'int', hidden: !win.extraFieldsVisible},
                                {name: 'RequestData_PlanYoung', header: 'В т.ч дети', type: 'int', hidden: !win.extraFieldsVisible},
                                {name: 'RequestData_KP', header: 'КП', type: 'float',  editor: new Ext.form.NumberField(), css : win.css},
                                {name: 'RequestData_KpAdults', header: 'в т.ч взрослые', type: 'float', hidden: !win.extraFieldsVisible},
                                {name: 'RequestData_KpKids', header: 'в т.ч дети', type: 'float', hidden: !win.extraFieldsVisible},
                                {name: 'RequestData_PlanKP', header: plnkp, type: 'int',  editor: new Ext.form.NumberField(), css : win.css},
                                {name: 'RequestData_PlanKPOld', header: 'В т.ч взрослые', type: 'int', hidden: !win.extraFieldsVisible},
                                {name: 'RequestData_PlanKPYoung', header: 'В т.ч дети', type: 'int', hidden: !win.extraFieldsVisible}
                                ];
                            break;
                            case 5:
                                toGroup = true;
                                url = '/?c=VolPeriods&m=loadRequestDataVmp';
                                fields = [
                                
                                {name: 'RequestData_id', type: 'int', header: 'ID', key: false},
                                {name: 'RN', type: 'int', header: ' ', width: 1, key: false, hidden: true},
                                {name: 'MesAgeGroup_id', header: 'Ид возрастной группы', type: 'int', width: 120, hidden: true},
                                {name: 'SprPlanObj_Code', header: 'Код группы', type: 'int', width: 120},
                                {name: 'GroupCodenew', header: 'Новый код группы', type: 'int', width: 120},
                                {name: 'SprPlanObj_Name', header: 'Группа ВМП', type: 'string', id: 'autoexpand', group:toGroup, hidden: true},
                                {name: 'RequestData_AllowPlan', header: 'Разрешить план.', type: win.isMZ == true ? 'checkcolumnedit' : 'checkbox', width: 100, hidden: !win.allowPlanVisible},
                                {name: 'VolCount1', header: fv1, type: 'int', editor: new Ext.form.NumberField()},
                                {name: 'VolCount2', header: fv2, type: 'int', editor: new Ext.form.NumberField()},
                                {name: 'VolCount3', header: fv3, type: 'int', width: 170, editor: new Ext.form.NumberField()},
                                {name: 'VolCount4', header: fv4, type: 'int', width: 120, editor: new Ext.form.NumberField()},
                                {name: 'RequestData_Plan', header: pln, type: 'int',  editor: new Ext.form.NumberField(), css : win.css},
                                {name: 'RequestData_KP', header: 'КП', type: 'float',  editor: new Ext.form.NumberField(), css : win.css},
                                {name: 'RequestData_KpAdults', header: 'в т.ч взрослые', type: 'float', hidden: !win.extraFieldsVisible},
                                {name: 'RequestData_KpKids', header: 'в т.ч дети', type: 'float', hidden: !win.extraFieldsVisible},
                                {name: 'RequestData_PlanKP', header: plnkp, type: 'int',  editor: new Ext.form.NumberField(), css : win.css}
                            ];
                            break;
                        };


                    break;

                    // Кол-во вызовов тромболизис
                    case 5:
                        tabPanel.setActiveTab(1);
                        tabPanel.hide();

                        url = '/?c=VolPeriods&m=loadRequestDataSmp';

                        fields = 
                        [
                            {name: 'RequestData_id', type: 'int', header: 'ID', key: true},
                            {name: 'MesAgeGroup_id', header: 'Ид возрастной группы', type: 'int', width: 120, hidden: true},
                            {name: 'SprPlanObj_Code', header: 'Код', type: 'float', width: 45},
                            {name: 'SprPlanObj_Name', header: 'Наименование', type: 'string', width: 410 , autoexpand: false},
                            {name: 'RequestData_AllowPlan', header: 'Разрешить план.', type: win.isMZ == true ? 'checkcolumnedit' : 'checkbox', width: 100, hidden: !win.allowPlanVisible},
                            {name: 'VolCount1', header: fv1, type: 'int', editor: new Ext.form.NumberField()},
                            {name: 'VolCount2', header: fv2, type: 'int', editor: new Ext.form.NumberField()},
                            {name: 'VolCount3', header: fv3, type: 'int', width: 170, editor: new Ext.form.NumberField()},
                            {name: 'VolCount4', header: fv4, type: 'int', width: 120, editor: new Ext.form.NumberField()},
                            {name: 'RequestData_Plan', header: pln, type: 'int',  editor: new Ext.form.NumberField(), css : win.css},
                            {name: 'RequestData_PlanOld', header: 'В т.ч взрослые', type: 'int', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_PlanYoung', header: 'В т.ч дети', type: 'int', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_KP', header: 'КП', type: 'float',  editor: new Ext.form.NumberField(), css : win.css},
                            {name: 'RequestData_KpAdults', header: 'в т.ч взрослые', type: 'float', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_KpKids', header: 'в т.ч дети', type: 'float', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_PlanKP', header: plnkp, type: 'int',  editor: new Ext.form.NumberField(), css : win.css},
                            {name: 'RequestData_PlanKPOld', header: 'В т.ч взрослые', type: 'int', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_PlanKPYoung', header: 'В т.ч дети', type: 'int', hidden: !win.extraFieldsVisible}
                        ];
                    break;

                    // Количество вызовов без тромболизиса
                    case 6:
                        tabPanel.setActiveTab(1);
                        tabPanel.hide();

                        url = '/?c=VolPeriods&m=loadRequestDataSmp';

                        fields = 
                        [
                            {name: 'RequestData_id', type: 'int', header: 'ID', key: true},
                            {name: 'MesAgeGroup_id', header: 'Ид возрастной группы', type: 'int', width: 120, hidden: true},
                            {name: 'SprPlanObj_Code', header: 'Код', type: 'float', width: 45},
                            {name: 'SprPlanObj_Name', header: 'Наименование', type: 'string', width: 410 , autoexpand: false},
                            {name: 'RequestData_AllowPlan', header: 'Разрешить план.', type: win.isMZ == true ? 'checkcolumnedit' : 'checkbox', width: 100, hidden: !win.allowPlanVisible},
                            {name: 'VolCount1', header: fv1, type: 'int', editor: new Ext.form.NumberField()},
                            {name: 'VolCount2', header: fv2, type: 'int', editor: new Ext.form.NumberField()},
                            {name: 'VolCount3', header: fv3, type: 'int', width: 170, editor: new Ext.form.NumberField()},
                            {name: 'VolCount4', header: fv4, type: 'int', width: 120, editor: new Ext.form.NumberField()},
                            {name: 'RequestData_Plan', header: pln, type: 'int',  editor: new Ext.form.NumberField(), css : win.css},
                            {name: 'RequestData_PlanOld', header: 'В т.ч взрослые', type: 'int', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_PlanYoung', header: 'В т.ч дети', type: 'int', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_KP', header: 'КП', type: 'float',  editor: new Ext.form.NumberField(), css : win.css},
                            {name: 'RequestData_KpAdults', header: 'в т.ч взрослые', type: 'float', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_KpKids', header: 'в т.ч дети', type: 'float', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_PlanKP', header: plnkp, type: 'int',  editor: new Ext.form.NumberField(), css : win.css},
                            {name: 'RequestData_PlanKPOld', header: 'В т.ч взрослые', type: 'int', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_PlanKPYoung', header: 'В т.ч дети', type: 'int', hidden: !win.extraFieldsVisible}
                        ];
                    break;

                    // ЛДИ
                    case 7:
                        url = '/?c=VolPeriods&m=loadRequestDataLdi'

                        fields = 
                        [
                            {name: 'RequestData_id', type: 'int', header: 'ID', key: true},
                            {name: 'MesAgeGroup_id', header: 'Ид возрастной группы', type: 'int', width: 120, hidden: true},
                            {name: 'SprPlanObj_Code', header: 'Код', type: 'float', width: 45},
                            {name: 'SprPlanObj_Name', header: 'Наименование', type: 'string', width: 410 , autoexpand: false},
                            {name: 'RequestData_AllowPlan', header: 'Разрешить <br>план.', type: win.isMZ == true ? 'checkcolumnedit' : 'checkbox', width: 100, hidden: !win.allowPlanVisible},
                            {name: 'VolCount1', header: fv1, type: 'int', editor: new Ext.form.NumberField()},
                            {name: 'VolCount2', header: fv2, type: 'int', editor: new Ext.form.NumberField()},
                            {name: 'VolCount3', header: fv3, type: 'int', width: 170, editor: new Ext.form.NumberField()},
                            {name: 'VolCount4', header: fv4, type: 'int', width: 120, editor: new Ext.form.NumberField()},
                            {name: 'RequestData_Plan', header: pln, type: 'int',  editor: new Ext.form.NumberField(), css : win.css},
                            {name: 'RequestData_PlanOld', header: 'В т.ч <br>взрослые', type: 'int', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_PlanYoung', header: 'В т.ч <br>дети', type: 'int', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_KP', header: 'КП', type: 'float',  editor: new Ext.form.NumberField(), css : win.css},
                            {name: 'RequestData_KpAdults', header: 'в т.ч взрослые', type: 'float', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_KpKids', header: 'в т.ч дети', type: 'float', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_PlanKP', header: plnkp, type: 'int',  editor: new Ext.form.NumberField(), css : win.css},
                            {name: 'RequestData_PlanKPOld', header: 'В т.ч <br>взрослые', type: 'int', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_PlanKPYoung', header: 'В т.ч <br>дети', type: 'int', hidden: !win.extraFieldsVisible}
                        ];
                    break;

                    // ДС при АПП (при поликлинике) 
                    case 8:
                        url = '/?c=VolPeriods&m=loadRequestDataDS'

                        fields = 
                        [
                            {name: 'RequestData_id', type: 'int', header: 'ID', key: true},
                            {name: 'MesAgeGroup_id', header: 'Ид возрастной группы', type: 'int', width: 120, hidden: true},
                            {name: 'SprPlanObj_Code', header: 'Код', type: 'float', width: 45},
                            {name: 'SprPlanObj_Name', header: 'Наименование', type: 'string', width: 410 , autoexpand: false},
                            {name: 'RequestData_AllowPlan', header: 'Разрешить <br>план.', type: win.isMZ == true ? 'checkcolumnedit' : 'checkbox', width: 100, hidden: !win.allowPlanVisible},
                            {name: 'VolCount1', header: fv1, type: 'int', editor: new Ext.form.NumberField()},
                            {name: 'VolCount2', header: fv2, type: 'int', editor: new Ext.form.NumberField()},
                            {name: 'VolCount3', header: fv3, type: 'int', width: 170, editor: new Ext.form.NumberField()},
                            {name: 'VolCount4', header: fv4, type: 'int', width: 120, editor: new Ext.form.NumberField()},
                            {name: 'ShiftCount', header: 'Кол-во смен <br>работы в день', type: 'int',  editor: new Ext.form.NumberField(), css : win.css},
                            {name: 'PlaceCount', header: 'Кол-во мест', type: 'int',  editor: new Ext.form.NumberField(), css : win.css},
                            {name: 'RequestData_Plan', header: pln, type: 'int',  editor: new Ext.form.NumberField(), css : win.css},
                            {name: 'RequestData_PlanOld', header: 'В т.ч <br>взрослые', type: 'int', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_PlanYoung', header: 'В т.ч <br>дети', type: 'int', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_KP', header: 'КП', type: 'float',  editor: new Ext.form.NumberField(), css : win.css},
                            {name: 'RequestData_KpAdults', header: 'в т.ч взрослые', type: 'float', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_KpKids', header: 'в т.ч дети', type: 'float', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_PlanKP', header: plnkp, type: 'int',  editor: new Ext.form.NumberField(), css : win.css},
                            {name: 'RequestData_PlanKPOld', header: 'В т.ч <br>взрослые', type: 'int', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_PlanKPYoung', header: 'В т.ч <br>дети', type: 'int', hidden: !win.extraFieldsVisible}
                        ];
                    break;

                    // ДС при АПП (на дому)
                    case 9:
                        url = '/?c=VolPeriods&m=loadRequestDataDS'

                        fields = 
                        [
                            {name: 'RequestData_id', type: 'int', header: 'ID', key: true},
                            {name: 'MesAgeGroup_id', header: 'Ид возрастной группы', type: 'int', width: 120, hidden: true},
                            {name: 'SprPlanObj_Code', header: 'Код', type: 'float', width: 45},
                            {name: 'SprPlanObj_Name', header: 'Наименование', type: 'string', width: 410 , autoexpand: false},
                            {name: 'RequestData_AllowPlan', header: 'Разрешить <br>план.', type: win.isMZ == true ? 'checkcolumnedit' : 'checkbox', width: 100, hidden: !win.allowPlanVisible},
                            {name: 'VolCount1', header: fv1, type: 'int', editor: new Ext.form.NumberField()},
                            {name: 'VolCount2', header: fv2, type: 'int', editor: new Ext.form.NumberField()},
                            {name: 'VolCount3', header: fv3, type: 'int', width: 170, editor: new Ext.form.NumberField()},
                            {name: 'VolCount4', header: fv4, type: 'int', width: 120, editor: new Ext.form.NumberField()},
                            {name: 'ShiftCount', header: 'Кол-во смен <br>работы в день', type: 'int',  editor: new Ext.form.NumberField(), css : win.css},
                            {name: 'PlaceCount', header: 'Кол-во мест', type: 'int',  editor: new Ext.form.NumberField(), css : win.css},
                            {name: 'RequestData_Plan', header: pln, type: 'int',  editor: new Ext.form.NumberField(), css : win.css},
                            {name: 'RequestData_PlanOld', header: 'В т.ч <br>взрослые', type: 'int', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_PlanYoung', header: 'В т.ч <br>дети', type: 'int', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_KP', header: 'КП', type: 'float',  editor: new Ext.form.NumberField(), css : win.css},
                            {name: 'RequestData_KpAdults', header: 'в т.ч взрослые', type: 'float', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_KpKids', header: 'в т.ч дети', type: 'float', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_PlanKP', header: plnkp, type: 'int',  editor: new Ext.form.NumberField(), css : win.css},
                            {name: 'RequestData_PlanKPOld', header: 'В т.ч <br>взрослые', type: 'int', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_PlanKPYoung', header: 'В т.ч <br>дети', type: 'int', hidden: !win.extraFieldsVisible}
                        ];
                    break;

                    // Стационар дневного пребывания при стационаре
                    case 10:
                        url = '/?c=VolPeriods&m=loadRequestDataDS'

                        fields = 
                        [
                            {name: 'RequestData_id', type: 'int', header: 'ID', key: true},
                            {name: 'MesAgeGroup_id', header: 'Ид возрастной группы', type: 'int', width: 120, hidden: true},
                            {name: 'SprPlanObj_Code', header: 'Код', type: 'float', width: 45},
                            {name: 'SprPlanObj_Name', header: 'Наименование', type: 'string', width: 410 , autoexpand: false},
                            {name: 'RequestData_AllowPlan', header: 'Разрешить <br>план.', type: win.isMZ == true ? 'checkcolumnedit' : 'checkbox', width: 100, hidden: !win.allowPlanVisible},
                            {name: 'VolCount1', header: fv1, type: 'int', editor: new Ext.form.NumberField()},
                            {name: 'VolCount2', header: fv2, type: 'int', editor: new Ext.form.NumberField()},
                            {name: 'VolCount3', header: fv3, type: 'int', width: 170, editor: new Ext.form.NumberField()},
                            {name: 'VolCount4', header: fv4, type: 'int', width: 120, editor: new Ext.form.NumberField()},
                            {name: 'ShiftCount', header: 'Кол-во смен <br>работы в день', type: 'int',  editor: new Ext.form.NumberField(), css : win.css},
                            {name: 'PlaceCount', header: 'Кол-во мест', type: 'int',  editor: new Ext.form.NumberField(), css : win.css},
                            {name: 'RequestData_BedCount', header: 'Среднегодовая <br>занятость <br>стационарозамещающих <br>коек', width: 150, type: 'int',  editor: new Ext.form.NumberField()},// css : win.css},
                            {name: 'RequestData_AvgDur', header: 'Средняя <br>длительность <br>пребывания', width: 95, type: 'float',  editor: new Ext.form.NumberField()},// css : win.css},
                            {name: 'RequestData_Plan', header: pln, type: 'int',  editor: new Ext.form.NumberField()},// css : win.css},
                            {name: 'RequestData_PlanOld', header: 'В т.ч <br>взрослые', type: 'int', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_PlanYoung', header: 'В т.ч <br>дети', type: 'int', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_KP', header: 'КП', type: 'float',  editor: new Ext.form.NumberField(), css : win.css},
                            {name: 'RequestData_KpAdults', header: 'в т.ч взрослые', type: 'float', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_KpKids', header: 'в т.ч дети', type: 'float', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_PlanKP', header: plnkp, type: 'int',  editor: new Ext.form.NumberField(), css : win.css},
                            {name: 'RequestData_PlanKPOld', header: 'В т.ч <br>взрослые', type: 'int', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_PlanKPYoung', header: 'В т.ч <br>дети', type: 'int', hidden: !win.extraFieldsVisible}
                        ];
                    break;

                    // АПП ММОЦ
                    case 43102:
                        url = '/?c=VolPeriods&m=loadRequestDataApp';

                        fields = [
                            {name: 'RequestData_id', type: 'int', header: 'ID', key: true},
                            {name: 'MesAgeGroup_id', header: 'Ид возрастной группы', type: 'int', width: 120, hidden: true},
                            {name: 'SprPlanObj_Code', header: 'Код', type: 'float', width: 45},
                            {name: 'SprPlanObj_Name', header: 'Наименование', type: 'string', width: 410 , autoexpand: false},
                            {name: 'RequestData_AllowPlan', header: 'Разрешить <br>план.', type: win.isMZ == true ? 'checkcolumnedit' : 'checkbox', width: 100, hidden: !win.allowPlanVisible},
                            {name: 'VolCount1', header: fv1, type: 'int', editor: new Ext.form.NumberField()},
                            {name: 'VolCount2', header: fv2, type: 'int', editor: new Ext.form.NumberField()},
                            {name: 'VolCount3', header: fv3, type: 'int', width: 170, editor: new Ext.form.NumberField()},
                            {name: 'VolCount4', header: fv4, type: 'int', width: 120, editor: new Ext.form.NumberField()},
                            {name: 'RequestData_Plan', header: pln, type: 'int',  editor: new Ext.form.NumberField(), css : win.css},
                            {name: 'RequestData_PlanOld', header: 'В т.ч <br>взрослые', type: 'int', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_PlanYoung', header: 'В т.ч <br>дети', type: 'int', hidden: !win.extraFieldsVisible},
                            {name: 'RazObrCount', header: 'Кол-во посещений в <br>обращении из Заявки <br>на ' + win.PlanYear +  ' г. (кратн. не менее 2)',  type: 'int', hidden: false/*!(win.SprVidMp_id==13)*/, editor: new Ext.form.NumberField(), css : win.css, width: 170},
                            {name: 'RazObrCountAdults', header: 'В т.ч <br>взрослые',  type: 'int', hidden: !win.extraFieldsVisible, width: 170},
                            {name: 'RazObrCountKids', header: 'В т.ч <br>дети',  type: 'int', hidden: !win.extraFieldsVisible, width: 170},
                            {name: 'RequestData_KP', header: 'КП', type: 'float',  editor: new Ext.form.NumberField(), css : win.css},
                            {name: 'RequestData_KpAdults', header: 'в т.ч взрослые', type: 'float', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_KpKids', header: 'в т.ч дети', type: 'float', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_PlanKP', header: plnkp, type: 'int',  editor: new Ext.form.NumberField(), css : win.css},
                            {name: 'RequestData_PlanKPOld', header: 'В т.ч <br>взрослые', type: 'int', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_PlanKPYoung', header: 'В т.ч <br>дети', type: 'int', hidden: !win.extraFieldsVisible}
                            ];
                    break;

                    // АПП Обращения МО, не имеющих прикрепленное население
                    case 43103:
                        url = '/?c=VolPeriods&m=loadRequestDataApp';

                        fields = [
                            {name: 'RequestData_id', type: 'int', header: 'ID', key: true},
                            {name: 'MesAgeGroup_id', header: 'Ид возрастной группы', type: 'int', width: 120, hidden: true},
                            {name: 'SprPlanObj_Code', header: 'Код', type: 'float', width: 45},
                            {name: 'SprPlanObj_Name', header: 'Наименование', type: 'string', width: 410 , autoexpand: false},
                            {name: 'RequestData_AllowPlan', header: 'Разрешить <br>план.', type: win.isMZ == true ? 'checkcolumnedit' : 'checkbox', width: 100, hidden: !win.allowPlanVisible},
                            {name: 'VolCount1', header: fv1, type: 'int', editor: new Ext.form.NumberField()},
                            {name: 'VolCount2', header: fv2, type: 'int', editor: new Ext.form.NumberField()},
                            {name: 'VolCount3', header: fv3, type: 'int', width: 170, editor: new Ext.form.NumberField()},
                            {name: 'VolCount4', header: fv4, type: 'int', width: 120, editor: new Ext.form.NumberField()},
                            {name: 'RequestData_Plan', header: pln, type: 'int',  editor: new Ext.form.NumberField(), css : win.css},
                            {name: 'RequestData_PlanOld', header: 'В т.ч <br>взрослые', type: 'int', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_PlanYoung', header: 'В т.ч <br>дети', type: 'int', hidden: !win.extraFieldsVisible},
                            {name: 'RazObrCount', header: 'Кол-во посещений в <br>обращении из Заявки <br>на ' + win.PlanYear +  ' г. (кратн. не менее 2)',  type: 'int', hidden: false/*!(win.SprVidMp_id==13)*/, editor: new Ext.form.NumberField(), css : win.css, width: 170},
                            {name: 'RazObrCountAdults', header: 'В т.ч <br>взрослые',  type: 'int', hidden: !win.extraFieldsVisible, width: 170},
                            {name: 'RazObrCountKids', header: 'В т.ч <br>дети',  type: 'int', hidden: !win.extraFieldsVisible, width: 170},
                            {name: 'RequestData_KP', header: 'КП', type: 'float',  editor: new Ext.form.NumberField(), css : win.css},
                            {name: 'RequestData_KpAdults', header: 'в т.ч взрослые', type: 'float', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_KpKids', header: 'в т.ч дети', type: 'float', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_PlanKP', header: plnkp, type: 'int',  editor: new Ext.form.NumberField(), css : win.css},
                            {name: 'RequestData_PlanKPOld', header: 'В т.ч <br>взрослые', type: 'int', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_PlanKPYoung', header: 'В т.ч <br>дети', type: 'int', hidden: !win.extraFieldsVisible}
                            ];
                    break;

                    // АПП Обращения МО, имеющих прикрепленное население (по реестрам)
                    case 43104:
                        url = '/?c=VolPeriods&m=loadRequestDataApp';

                        fields = [
                            {name: 'RequestData_id', type: 'int', header: 'ID', key: true},
                            {name: 'MesAgeGroup_id', header: 'Ид возрастной группы', type: 'int', width: 120, hidden: true},
                            {name: 'SprPlanObj_Code', header: 'Код', type: 'float', width: 45},
                            {name: 'SprPlanObj_Name', header: 'Наименование', type: 'string', width: 410 , autoexpand: false},
                            {name: 'RequestData_AllowPlan', header: 'Разрешить <br>план.', type: win.isMZ == true ? 'checkcolumnedit' : 'checkbox', width: 100, hidden: !win.allowPlanVisible},
                            {name: 'VolCount1', header: fv1, type: 'int', editor: new Ext.form.NumberField()},
                            {name: 'VolCount2', header: fv2, type: 'int', editor: new Ext.form.NumberField()},
                            {name: 'VolCount3', header: fv3, type: 'int', width: 170, editor: new Ext.form.NumberField()},
                            {name: 'VolCount4', header: fv4, type: 'int', width: 120, editor: new Ext.form.NumberField()},
                            {name: 'RequestData_Plan', header: pln, type: 'int',  editor: new Ext.form.NumberField(), css : win.css},
                            {name: 'RequestData_PlanOld', header: 'В т.ч <br>взрослые', type: 'int', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_PlanYoung', header: 'В т.ч <br>дети', type: 'int', hidden: !win.extraFieldsVisible},
                            {name: 'RazObrCount', header: 'Кол-во посещений в <br>обращении из Заявки <br>на ' + win.PlanYear +  ' г. (кратн. не менее 2)',  type: 'int', hidden: false/*!(win.SprVidMp_id==13)*/, editor: new Ext.form.NumberField(), css : win.css, width: 170},
                            {name: 'RazObrCountAdults', header: 'В т.ч <br>взрослые',  type: 'int', hidden: !win.extraFieldsVisible, width: 170},
                            {name: 'RazObrCountKids', header: 'В т.ч <br>дети',  type: 'int', hidden: !win.extraFieldsVisible, width: 170},
                            {name: 'SluchCountOwn1', header: 'в т.ч. по <br>маршрутизации <br>(на свое прикрепл. население)', type: 'int',  editor: new Ext.form.NumberField(), css : win.css},
                            {name: 'Own1Adults', header: 'В т.ч <br>взрослые', type: 'int', css : win.css, hidden: !win.extraFieldsVisible},
                            {name: 'Own1Kids', header: 'В т.ч <br>дети', type: 'int', css : win.css, hidden: !win.extraFieldsVisible},
                            {name: 'SluchCountZone1', header: 'в т.ч. по <br>маршрутизации <br>(на населен. по зоне ответств.)', type: 'int',  editor: new Ext.form.NumberField(), css : win.css},
                            {name: 'Zone1Adults', header: 'В т.ч <br>взрослые', type: 'int', css : win.css, hidden: !win.extraFieldsVisible},
                            {name: 'Zone1Kids', header: 'В т.ч <br>дети', type: 'int', css : win.css, hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_KP', header: 'КП', type: 'float',  editor: new Ext.form.NumberField(), css : win.css},
                            {name: 'RequestData_KpAdults', header: 'в т.ч взрослые', type: 'float', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_KpKids', header: 'в т.ч дети', type: 'float', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_PlanKP', header: plnkp, type: 'int',  editor: new Ext.form.NumberField(), css : win.css},
                            {name: 'RequestData_PlanKPOld', header: 'В т.ч <br>взрослые', type: 'int', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_PlanKPYoung', header: 'В т.ч <br>дети', type: 'int', hidden: !win.extraFieldsVisible}
                            ];
                    break;

                    // АПП посещения с профилактической целью
                    case 43105:
                        url = '/?c=VolPeriods&m=loadRequestDataAppCons';

                        fields = [
                            {name: 'RequestData_id', type: 'int', header: 'ID', key: true},
                            {name: 'MesAgeGroup_id', header: 'Ид возрастной группы', type: 'int', width: 120, hidden: true},
                            {name: 'SprPlanObj_Code', header: 'Код', type: 'float', width: 45},
                            {name: 'SprPlanObj_Name', header: 'Наименование', type: 'string', width: 410 , autoexpand: false},
                            {name: 'RequestData_AllowPlan', header: 'Разрешить план.', type: win.isMZ == true ? 'checkcolumnedit' : 'checkbox', width: 100, hidden: !win.allowPlanVisible},
                            {name: 'VolCount1', header: fv1, type: 'int', editor: new Ext.form.NumberField()},
                            {name: 'VolCount2', header: fv2, type: 'int', editor: new Ext.form.NumberField()},
                            {name: 'VolCount3', header: fv3, type: 'int', width: 170, editor: new Ext.form.NumberField()},
                            {name: 'VolCount4', header: fv4, type: 'int', width: 120, editor: new Ext.form.NumberField()},
                            {name: 'RequestData_Plan', header: pln, type: 'int',  editor: new Ext.form.NumberField(), css : win.css},
                            {name: 'RequestData_PlanOld', header: 'В т.ч <br>взрослые', type: 'int', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_PlanYoung', header: 'В т.ч <br>дети', type: 'int', hidden: !win.extraFieldsVisible},
                            {name: 'RazObrCount', header: 'в т.ч. разовые <br>посещения в связи <br>с заболеваниями', type: 'int', hidden: /*(win.AgeTabPanel.getActiveTab().id == 'tab_all')*/ false,  editor: new Ext.form.NumberField(), css : win.css, width: 115},
                            {name: 'RazObrCountAdults', header: 'В т.ч <br>взрослые', type: 'int', hidden: !win.extraFieldsVisible, width: 115},
                            {name: 'RazObrCountKids', header: 'В т.ч <br>дети', type: 'int', hidden: !win.extraFieldsVisible, width: 115},
                            {name: 'RequestData_KP', header: 'КП', type: 'float',  editor: new Ext.form.NumberField(), css : win.css},
                            {name: 'RequestData_KpAdults', header: 'в т.ч взрослые', type: 'float', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_KpKids', header: 'в т.ч дети', type: 'float', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_PlanKP', header: plnkp, type: 'int',  editor: new Ext.form.NumberField(), css : win.css},
                            {name: 'RequestData_PlanKPOld', header: 'В т.ч <br>взрослые', type: 'int', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_PlanKPYoung', header: 'В т.ч <br>дети', type: 'int', hidden: !win.extraFieldsVisible}
                        ];
                    break;

                    // АПП посещения по неотложной медицинской помощи
                    case 43106:
                        url = '/?c=VolPeriods&m=loadRequestDataAppNmp';

                        fields = [
                            {name: 'RequestData_id', type: 'int', header: 'ID', key: true},
                            {name: 'MesAgeGroup_id', header: 'Ид возрастной группы', type: 'int', width: 120, hidden: true},
                            {name: 'SprPlanObj_Code', header: 'Код', type: 'float', width: 45},
                            {name: 'SprPlanObj_Name', header: 'Наименование', type: 'string', width: 410 , autoexpand: false},
                            {name: 'RequestData_AllowPlan', header: 'Разрешить <br>план.', type: win.isMZ == true ? 'checkcolumnedit' : 'checkbox', width: 100, hidden: !win.allowPlanVisible},
                            {name: 'VolCount1', header: fv1, type: 'int', editor: new Ext.form.NumberField()},
                            {name: 'VolCount2', header: fv2, type: 'int', editor: new Ext.form.NumberField()},
                            {name: 'VolCount3', header: fv3, type: 'int', width: 170, editor: new Ext.form.NumberField()},
                            {name: 'VolCount4', header: fv4, type: 'int', width: 120, editor: new Ext.form.NumberField()},
                            {name: 'RequestData_Plan', header: pln, type: 'int',  editor: new Ext.form.NumberField(), css : win.css},                         
                            {name: 'RequestData_PlanOld', header: 'В т.ч <br>взрослые', type: 'int', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_PlanYoung', header: 'В т.ч <br>дети', type: 'int', hidden: !win.extraFieldsVisible},
                            {name: 'EmerRoom', header: 'Травмпункт', type: 'int',  editor: new Ext.form.NumberField(), css : win.css},
                            {name: 'EmerRoomAdults', header: 'в т.ч. <br>взрослые', type: 'int',  editor: new Ext.form.NumberField(), css : win.css, hidden: !win.extraFieldsVisible},
                            {name: 'EmerRoomKids', header: 'в т.ч. <br>дети', type: 'int',  editor: new Ext.form.NumberField(), css : win.css, hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_KP', header: 'КП', type: 'float',  editor: new Ext.form.NumberField(), css : win.css},
                            {name: 'RequestData_KpAdults', header: 'в т.ч взрослые', type: 'float', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_KpKids', header: 'в т.ч дети', type: 'float', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_PlanKP', header: plnkp, type: 'int',  editor: new Ext.form.NumberField(), css : win.css},
                            {name: 'RequestData_PlanKPOld', header: 'В т.ч <br>взрослые', type: 'int', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_PlanKPYoung', header: 'В т.ч <br>дети', type: 'int', hidden: !win.extraFieldsVisible}
                        ];
                    break;

                    // АПП Обращения МО, имеющих прикрепленное население (ПДН)
                    case 43107:
                        url = '/?c=VolPeriods&m=loadRequestDataApp';

                        fields = [
                            {name: 'RequestData_id', type: 'int', header: 'ID', key: true},
                            {name: 'MesAgeGroup_id', header: 'Ид возрастной группы', type: 'int', width: 120, hidden: true},
                            {name: 'SprPlanObj_Code', header: 'Код', type: 'float', width: 45},
                            {name: 'SprPlanObj_Name', header: 'Наименование', type: 'string', width: 410 , autoexpand: false},
                            {name: 'RequestData_AllowPlan', header: 'Разрешить <br>план.', type: win.isMZ == true ? 'checkcolumnedit' : 'checkbox', width: 100, hidden: !win.allowPlanVisible},
                            {name: 'VolCount1', header: fv1, type: 'int', editor: new Ext.form.NumberField()},
                            {name: 'VolCount2', header: fv2, type: 'int', editor: new Ext.form.NumberField()},
                            {name: 'VolCount3', header: fv3, type: 'int', width: 170, editor: new Ext.form.NumberField()},
                            {name: 'VolCount4', header: fv4, type: 'int', width: 120, editor: new Ext.form.NumberField()},
                            {name: 'RequestData_Plan', header: pln, type: 'int',  editor: new Ext.form.NumberField(), css : win.css},
                            {name: 'RequestData_PlanOld', header: 'В т.ч <br>взрослые', type: 'int', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_PlanYoung', header: 'В т.ч <br>дети', type: 'int', hidden: !win.extraFieldsVisible},
                            {name: 'RazObrCount', header: 'Кол-во посещений в <br>обращении из Заявки <br>на ' + win.PlanYear +  ' г. (кратн. не менее 2)',  type: 'int', hidden: false/*!(win.SprVidMp_id==13)*/, editor: new Ext.form.NumberField(), css : win.css, width: 170},
                            {name: 'RazObrCountAdults', header: 'В т.ч <br>взрослые',  type: 'int', hidden: !win.extraFieldsVisible, width: 170},
                            {name: 'RazObrCountKids', header: 'В т.ч <br>дети',  type: 'int', hidden: !win.extraFieldsVisible, width: 170},
                            {name: 'RequestData_KP', header: 'КП', type: 'float',  editor: new Ext.form.NumberField(), css : win.css},
                            {name: 'RequestData_KpAdults', header: 'в т.ч взрослые', type: 'float', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_KpKids', header: 'в т.ч дети', type: 'float', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_PlanKP', header: plnkp, type: 'int',  editor: new Ext.form.NumberField(), css : win.css},
                            {name: 'RequestData_PlanKPOld', header: 'В т.ч <br>взрослые', type: 'int', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_PlanKPYoung', header: 'В т.ч <br>дети', type: 'int', hidden: !win.extraFieldsVisible}
                            ];
                    break;

                    // АПП Диспансеризация взрослого населения 1 этап
                    case 43108:
                        url = '/?c=VolPeriods&m=loadRequestDataAppDisp';

                        fields = [
                            {name: 'RequestData_id', type: 'int', header: 'ID', key: true},
                            {name: 'MesAgeGroup_id', header: 'Ид возрастной группы', type: 'int', width: 120, hidden: true},
                            {name: 'SprPlanObj_Code', header: 'Код', type: 'float', width: 45},
                            {name: 'SprPlanObj_Name', header: 'Наименование', type: 'string', width: 410 , autoexpand: false},
                            {name: 'RequestData_AllowPlan', header: 'Разрешить план.', type: win.isMZ == true ? 'checkcolumnedit' : 'checkbox', width: 100, hidden: !win.allowPlanVisible},
                            {name: 'VolCount1', header: fv1, type: 'int', editor: new Ext.form.NumberField()},
                            {name: 'VolCount2', header: fv2, type: 'int', editor: new Ext.form.NumberField()},
                            {name: 'VolCount3', header: fv3, type: 'int', width: 170, editor: new Ext.form.NumberField()},
                            {name: 'VolCount4', header: fv4, type: 'int', width: 120, editor: new Ext.form.NumberField()},
                            {name: 'RequestData_Plan', header: pln, type: 'int',  editor: new Ext.form.NumberField(), css : win.css},
                            {name: 'RequestData_PlanOld', header: 'В т.ч взрослые', type: 'int', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_PlanYoung', header: 'В т.ч дети', type: 'int', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_KP', header: 'КП', type: 'float',  editor: new Ext.form.NumberField(), css : win.css},
                            {name: 'RequestData_KpAdults', header: 'в т.ч взрослые', type: 'float', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_KpKids', header: 'в т.ч дети', type: 'float', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_PlanKP', header: plnkp, type: 'int',  editor: new Ext.form.NumberField(), css : win.css},
                            {name: 'RequestData_PlanKPOld', header: 'В т.ч взрослые', type: 'int', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_PlanKPYoung', header: 'В т.ч дети', type: 'int', hidden: !win.extraFieldsVisible}
                            ];
                    break;

                    // АПП Диспансеризация взрослого населения 2 этап
                    case 43109:
                        url = '/?c=VolPeriods&m=loadRequestDataAppDisp';

                        fields = [
                            {name: 'RequestData_id', type: 'int', header: 'ID', key: true},
                            {name: 'MesAgeGroup_id', header: 'Ид возрастной группы', type: 'int', width: 120, hidden: true},
                            {name: 'SprPlanObj_Code', header: 'Код', type: 'float', width: 45},
                            {name: 'SprPlanObj_Name', header: 'Наименование', type: 'string', width: 410 , autoexpand: false},
                            {name: 'RequestData_AllowPlan', header: 'Разрешить план.', type: win.isMZ == true ? 'checkcolumnedit' : 'checkbox', width: 100, hidden: !win.allowPlanVisible},
                            {name: 'VolCount1', header: fv1, type: 'int', editor: new Ext.form.NumberField()},
                            {name: 'VolCount2', header: fv2, type: 'int', editor: new Ext.form.NumberField()},
                            {name: 'VolCount3', header: fv3, type: 'int', width: 170, editor: new Ext.form.NumberField()},
                            {name: 'VolCount4', header: fv4, type: 'int', width: 120, editor: new Ext.form.NumberField()},
                            {name: 'RequestData_Plan', header: pln, type: 'int',  editor: new Ext.form.NumberField(), css : win.css},
                            {name: 'RequestData_PlanOld', header: 'В т.ч взрослые', type: 'int', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_PlanYoung', header: 'В т.ч дети', type: 'int', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_KP', header: 'КП', type: 'float',  editor: new Ext.form.NumberField(), css : win.css},
                            {name: 'RequestData_KpAdults', header: 'в т.ч взрослые', type: 'float', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_KpKids', header: 'в т.ч дети', type: 'float', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_PlanKP', header: plnkp, type: 'int',  editor: new Ext.form.NumberField(), css : win.css},
                            {name: 'RequestData_PlanKPOld', header: 'В т.ч взрослые', type: 'int', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_PlanKPYoung', header: 'В т.ч дети', type: 'int', hidden: !win.extraFieldsVisible}
                            ];
                    break;

                    // АПП Диспансеризация детей-сирот
                    case 43110:
                        url = '/?c=VolPeriods&m=loadRequestDataAppDisp';

                        fields = [
                            {name: 'RequestData_id', type: 'int', header: 'ID', key: true},
                            {name: 'MesAgeGroup_id', header: 'Ид возрастной группы', type: 'int', width: 120, hidden: true},
                            {name: 'SprPlanObj_Code', header: 'Код', type: 'float', width: 45},
                            {name: 'SprPlanObj_Name', header: 'Наименование', type: 'string', width: 410 , autoexpand: false},
                            {name: 'RequestData_AllowPlan', header: 'Разрешить план.', type: win.isMZ == true ? 'checkcolumnedit' : 'checkbox', width: 100, hidden: !win.allowPlanVisible},
                            {name: 'VolCount1', header: fv1, type: 'int', editor: new Ext.form.NumberField()},
                            {name: 'VolCount2', header: fv2, type: 'int', editor: new Ext.form.NumberField()},
                            {name: 'VolCount3', header: fv3, type: 'int', width: 170, editor: new Ext.form.NumberField()},
                            {name: 'VolCount4', header: fv4, type: 'int', width: 120, editor: new Ext.form.NumberField()},
                            {name: 'RequestData_Plan', header: pln, type: 'int',  editor: new Ext.form.NumberField(), css : win.css},
                            {name: 'RequestData_PlanOld', header: 'В т.ч взрослые', type: 'int', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_PlanYoung', header: 'В т.ч дети', type: 'int', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_KP', header: 'КП', type: 'float',  editor: new Ext.form.NumberField(), css : win.css},
                            {name: 'RequestData_KpAdults', header: 'в т.ч взрослые', type: 'float', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_KpKids', header: 'в т.ч дети', type: 'float', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_PlanKP', header: plnkp, type: 'int',  editor: new Ext.form.NumberField(), css : win.css},
                            {name: 'RequestData_PlanKPOld', header: 'В т.ч взрослые', type: 'int', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_PlanKPYoung', header: 'В т.ч дети', type: 'int', hidden: !win.extraFieldsVisible}
                            ];
                    break;

                    // АПП Обращения МО, имеющих прикрепленное население (по реестрам)
                    case 43111:
                        url = '/?c=VolPeriods&m=loadRequestDataAppProfAttach';

                        fields = [
                            {name: 'RequestData_id', type: 'int', header: 'ID', key: true},
                            {name: 'MesAgeGroup_id', header: 'Ид возрастной группы', type: 'int', width: 120, hidden: true},
                            {name: 'SprPlanObj_Code', header: 'Код', type: 'float', width: 45},
                            {name: 'SprPlanObj_Name', header: 'Наименование', type: 'string', width: 410 , autoexpand: false},
                            {name: 'RequestData_AllowPlan', header: 'Разрешить план.', type: win.isMZ == true ? 'checkcolumnedit' : 'checkbox', width: 100, hidden: !win.allowPlanVisible},
                            {name: 'VolCount1', header: fv1, type: 'int', editor: new Ext.form.NumberField()},
                            {name: 'VolCount2', header: fv2, type: 'int', editor: new Ext.form.NumberField()},
                            {name: 'VolCount3', header: fv3, type: 'int', width: 170, editor: new Ext.form.NumberField()},
                            {name: 'VolCount4', header: fv4, type: 'int', width: 120, editor: new Ext.form.NumberField()},
                            {name: 'RequestData_Plan', header: pln, type: 'int',  /*editor: new Ext.form.NumberField(), css : win.css*/},
                            {name: 'RequestData_PlanOld', header: 'В т.ч взрослые', type: 'int', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_PlanYoung', header: 'В т.ч дети', type: 'int', hidden: !win.extraFieldsVisible},
                            {name: 'DispNab', header: 'В т.ч. для проведения <br>диспансерного <br>наблюдения', type: 'int', editor: new Ext.form.NumberField(), css : win.css,  width: 115},
                            {name: 'DispNabAdults', header: 'В т.ч взрослые', type: 'int', hidden: !win.extraFieldsVisible,  width: 115},
                            {name: 'DispNabKids', header: 'В т.ч дети', type: 'int', hidden: !win.extraFieldsVisible,  width: 115},
                            {name: 'RazObrCount', header: 'в т.ч. разовые <br>посещения в связи <br>с заболеваниями', type: 'int', hidden: /*(win.AgeTabPanel.getActiveTab().id == 'tab_all')*/ false,  editor: new Ext.form.NumberField(), css : win.css, width: 115},
                            {name: 'RazObrCountAdults', header: 'В т.ч взрослые', type: 'int', hidden: !win.extraFieldsVisible,  width: 115},
                            {name: 'RazObrCountKids', header: 'В т.ч дети', type: 'int', hidden: !win.extraFieldsVisible,  width: 115},
                            {name: 'MedReab', header: 'В т.ч. посещения <br>по медицинской <br>реабилитации', type: 'int', editor: new Ext.form.NumberField(), css : win.css,  width: 115},
                            {name: 'MedReabAdults', header: 'В т.ч взрослые', type: 'int', hidden: !win.extraFieldsVisible,  width: 115},
                            {name: 'MedReabKids', header: 'В т.ч дети', type: 'int', hidden: !win.extraFieldsVisible,  width: 115},
                            {name: 'OtherPurp', header: 'В т.ч. посещения <br>с другой <br>целью', type: 'int', editor: new Ext.form.NumberField(), css : win.css,  width: 115},
                            {name: 'OtherPurpAdults', header: 'В т.ч взрослые', type: 'int', hidden: !win.extraFieldsVisible,  width: 115},
                            {name: 'OtherPurpKids', header: 'В т.ч дети', type: 'int', hidden: !win.extraFieldsVisible,  width: 115},
                            {name: 'SluchCountOwn1', header: 'в т.ч. по <br>маршрутизации <br>(на свое прикрепл. население)', type: 'int',  editor: new Ext.form.NumberField(), css : win.css},
                            {name: 'SluchCountOwn1Adults', header: 'В т.ч взрослые', type: 'int', css : win.css, hidden: !win.extraFieldsVisible},
                            {name: 'SluchCountOwn1Kids', header: 'В т.ч дети', type: 'int', css : win.css, hidden: !win.extraFieldsVisible},
                            {name: 'SluchCountZone1', header: 'в т.ч. по <br>маршрутизации <br>(на населен. по зоне ответств.)', type: 'int',  editor: new Ext.form.NumberField(), css : win.css},
                            {name: 'SluchCountZone1Adults', header: 'В т.ч взрослые', type: 'int', css : win.css, hidden: !win.extraFieldsVisible},
                            {name: 'SluchCountZone1Kids', header: 'В т.ч дети', type: 'int', css : win.css, hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_KP', header: 'КП', type: 'float',  editor: new Ext.form.NumberField(), css : win.css},
                            {name: 'RequestData_KpAdults', header: 'в т.ч взрослые', type: 'float', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_KpKids', header: 'в т.ч дети', type: 'float', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_PlanKP', header: plnkp, type: 'int',  editor: new Ext.form.NumberField(), css : win.css},
                            {name: 'RequestData_PlanKPOld', header: 'В т.ч взрослые', type: 'int', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_PlanKPYoung', header: 'В т.ч дети', type: 'int', hidden: !win.extraFieldsVisible},

                            {name: 'DispNabPlanKP', header: 'В т.ч. для ДН <br>по КП', type: 'int', editor: new Ext.form.NumberField(), css: win.css},
                            {name: 'DispNabPlanKPAdults', header: 'В т.ч взрослые', type: 'int', hidden: !win.extraFieldsVisible},
                            {name: 'DispNabPlanKPKids', header: 'В т.ч дети', type: 'int', hidden: !win.extraFieldsVisible},
                            {name: 'RazObrCountPlanKP', header: 'В т.ч. разовые <br>по заболеванию <br>по КП', type: 'int', editor: new Ext.form.NumberField(), css: win.css},
                            {name: 'RazObrCountPlanKPAdults', header: 'В т.ч взрослые', type: 'int', hidden: !win.extraFieldsVisible},
                            {name: 'RazObrCountPlanKPKids', header: 'В т.ч дети', type: 'int', hidden: !win.extraFieldsVisible},
                            {name: 'MidMedStaffPlanKP', header: 'В т.ч. средний <br>МП', type: 'int', editor: new Ext.form.NumberField(), css: win.css},
                            {name: 'MidMedStaffPlanKPAdults', header: 'В т.ч взрослые', type: 'int', hidden: !win.extraFieldsVisible},
                            {name: 'MidMedStaffPlanKPKids', header: 'В т.ч дети', type: 'int', hidden: !win.extraFieldsVisible},
                            {name: 'OtherPurpPlanKP', header: 'В т.ч. с другими <br>целями по КП', type: 'int', editor: new Ext.form.NumberField(), css: win.css},
                            {name: 'OtherPurpPlanKPAdults', header: 'В т.ч взрослые', type: 'int', hidden: !win.extraFieldsVisible},
                            {name: 'OtherPurpPlanKPKids', header: 'В т.ч дети', type: 'int', hidden: !win.extraFieldsVisible}
                            ];
                    break;

                    // АПП Обращения МО, имеющих прикрепленное население (ПДН)
                    case 43112:
                        url = '/?c=VolPeriods&m=loadRequestDataAppProfAttach';

                        fields = [
                            {name: 'RequestData_id', type: 'int', header: 'ID', key: true},
                            {name: 'MesAgeGroup_id', header: 'Ид возрастной группы', type: 'int', width: 120, hidden: true},
                            {name: 'SprPlanObj_Code', header: 'Код', type: 'float', width: 45},
                            {name: 'SprPlanObj_Name', header: 'Наименование', type: 'string', width: 410 , autoexpand: false},
                            {name: 'RequestData_AllowPlan', header: 'Разрешить план.', type: win.isMZ == true ? 'checkcolumnedit' : 'checkbox', width: 100, hidden: !win.allowPlanVisible},
                            {name: 'VolCount1', header: fv1, type: 'int', editor: new Ext.form.NumberField()},
                            {name: 'VolCount2', header: fv2, type: 'int', editor: new Ext.form.NumberField()},
                            {name: 'VolCount3', header: fv3, type: 'int', width: 170, editor: new Ext.form.NumberField()},
                            {name: 'VolCount4', header: fv4, type: 'int', width: 120, editor: new Ext.form.NumberField()},
                            {name: 'RequestData_Plan', header: pln, type: 'int',  /*editor: new Ext.form.NumberField(), css : win.css*/},
                            {name: 'RequestData_PlanOld', header: 'В т.ч взрослые', type: 'int', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_PlanYoung', header: 'В т.ч дети', type: 'int', hidden: !win.extraFieldsVisible},
                            {name: 'DispNab', header: 'В т.ч. для проведения <br>диспансерного <br>наблюдения', type: 'int', editor: new Ext.form.NumberField(), css : win.css,  width: 115},
                            {name: 'DispNabAdults', header: 'В т.ч взрослые', type: 'int', hidden: !win.extraFieldsVisible,  width: 115},
                            {name: 'DispNabKids', header: 'В т.ч дети', type: 'int', hidden: !win.extraFieldsVisible,  width: 115},
                            {name: 'RazObrCount', header: 'в т.ч. разовые <br>посещения в связи <br>с заболеваниями', type: 'int', hidden: /*(win.AgeTabPanel.getActiveTab().id == 'tab_all')*/ false,  editor: new Ext.form.NumberField(), css : win.css, width: 115},
                            {name: 'RazObrCountAdults', header: 'В т.ч взрослые', type: 'int', hidden: !win.extraFieldsVisible,  width: 115},
                            {name: 'RazObrCountKids', header: 'В т.ч дети', type: 'int', hidden: !win.extraFieldsVisible,  width: 115},
                            {name: 'MedReab', header: /*'В т.ч. посещения <br>по медицинской <br>реабилитации'*/ 'В т.ч. средний МП', type: 'int', editor: new Ext.form.NumberField(), css : win.css,  width: 115},
                            {name: 'MedReabAdults', header: 'В т.ч взрослые', type: 'int', hidden: !win.extraFieldsVisible,  width: 115},
                            {name: 'MedReabKids', header: 'В т.ч дети', type: 'int', hidden: !win.extraFieldsVisible,  width: 115},
                            {name: 'OtherPurp', header: 'В т.ч. посещения <br>с другой <br>целью', type: 'int', editor: new Ext.form.NumberField(), css : win.css,  width: 115},
                            {name: 'OtherPurpAdults', header: 'В т.ч взрослые', type: 'int', hidden: !win.extraFieldsVisible,  width: 115},
                            {name: 'OtherPurpKids', header: 'В т.ч дети', type: 'int', hidden: !win.extraFieldsVisible,  width: 115},
                            {name: 'SluchCountOwn1', header: 'в т.ч. по <br>маршрутизации <br>(на свое прикрепл. население)', type: 'int',  editor: new Ext.form.NumberField(), css : win.css},
                            {name: 'Own1Adults', header: 'В т.ч взрослые', type: 'int', css : win.css, hidden: !win.extraFieldsVisible},
                            {name: 'Own1Kids', header: 'В т.ч дети', type: 'int', css : win.css, hidden: !win.extraFieldsVisible, width: 115},
                            {name: 'SluchCountZone1', header: 'в т.ч. по <br>маршрутизации <br>(на населен. по зоне ответств.)', type: 'int',  editor: new Ext.form.NumberField(), css : win.css},
                            {name: 'Zone1Adults', header: 'В т.ч взрослые', type: 'int', css : win.css, hidden: !win.extraFieldsVisible},
                            {name: 'Zone1Kids', header: 'В т.ч дети', type: 'int', css : win.css, hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_KP', header: 'КП', type: 'float',  editor: new Ext.form.NumberField(), css : win.css},
                            {name: 'RequestData_KpAdults', header: 'в т.ч взрослые', type: 'float', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_KpKids', header: 'в т.ч дети', type: 'float', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_PlanKP', header: plnkp, type: 'int',  editor: new Ext.form.NumberField(), css : win.css},
                            {name: 'RequestData_PlanKPOld', header: 'В т.ч взрослые', type: 'int', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_PlanKPYoung', header: 'В т.ч дети', type: 'int', hidden: !win.extraFieldsVisible},

                            {name: 'DispNabPlanKP', header: 'В т.ч. для ДН <br>по КП', type: 'int', editor: new Ext.form.NumberField(), css: win.css},
                            {name: 'DispNabPlanKPAdults', header: 'В т.ч взрослые', type: 'int', hidden: !win.extraFieldsVisible},
                            {name: 'DispNabPlanKPKids', header: 'В т.ч дети', type: 'int', hidden: !win.extraFieldsVisible},
                            {name: 'RazObrCountPlanKP', header: 'В т.ч. разовые <br>по заболеванию <br>по КП', type: 'int', editor: new Ext.form.NumberField(), css: win.css},
                            {name: 'RazObrCountPlanKPAdults', header: 'В т.ч взрослые', type: 'int', hidden: !win.extraFieldsVisible},
                            {name: 'RazObrCountPlanKPKids', header: 'В т.ч дети', type: 'int', hidden: !win.extraFieldsVisible},
                            {name: 'MidMedStaffPlanKP', header: 'В т.ч. средний <br>МП', type: 'int', editor: new Ext.form.NumberField(), css: win.css},
                            {name: 'MidMedStaffPlanKPAdults', header: 'В т.ч взрослые', type: 'int', hidden: !win.extraFieldsVisible},
                            {name: 'MidMedStaffPlanKPKids', header: 'В т.ч дети', type: 'int', hidden: !win.extraFieldsVisible},
                            {name: 'OtherPurpPlanKP', header: 'В т.ч. с другими <br>целями по КП', type: 'int', editor: new Ext.form.NumberField(), css: win.css},
                            {name: 'OtherPurpPlanKPAdults', header: 'В т.ч взрослые', type: 'int', hidden: !win.extraFieldsVisible},
                            {name: 'OtherPurpPlanKPKids', header: 'В т.ч дети', type: 'int', hidden: !win.extraFieldsVisible}
                            ];                                                                                                            
                    break;

                    // АПП Профилактические осмотры взрослого населения
                    case 43113:
                        url = '/?c=VolPeriods&m=loadRequestDataAppProf';

                        fields = [
                            {name: 'RequestData_id', type: 'int', header: 'ID', key: true},
                            {name: 'MesAgeGroup_id', header: 'Ид возрастной группы', type: 'int', width: 120, hidden: true},
                            {name: 'SprPlanObj_Code', header: 'Код', type: 'float', width: 45},
                            {name: 'SprPlanObj_Name', header: 'Наименование', type: 'string', width: 410 , autoexpand: false},
                            {name: 'RequestData_AllowPlan', header: 'Разрешить план.', type: win.isMZ == true ? 'checkcolumnedit' : 'checkbox', width: 100, hidden: !win.allowPlanVisible},
                            {name: 'VolCount1', header: fv1, type: 'int', editor: new Ext.form.NumberField()},
                            {name: 'VolCount2', header: fv2, type: 'int', editor: new Ext.form.NumberField()},
                            {name: 'VolCount3', header: fv3, type: 'int', width: 170, editor: new Ext.form.NumberField()},
                            {name: 'VolCount4', header: fv4, type: 'int', width: 120, editor: new Ext.form.NumberField()},
                            {name: 'RequestData_Plan', header: pln, type: 'int',  editor: new Ext.form.NumberField(), css : win.css},
                            {name: 'RequestData_PlanOld', header: 'В т.ч взрослые', type: 'int', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_PlanYoung', header: 'В т.ч дети', type: 'int', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_KP', header: 'КП', type: 'float',  editor: new Ext.form.NumberField(), css : win.css},
                            {name: 'RequestData_KpAdults', header: 'в т.ч взрослые', type: 'float', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_KpKids', header: 'в т.ч дети', type: 'float', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_PlanKP', header: plnkp, type: 'int',  editor: new Ext.form.NumberField(), css : win.css},
                            {name: 'RequestData_PlanKPOld', header: 'В т.ч взрослые', type: 'int', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_PlanKPYoung', header: 'В т.ч дети', type: 'int', hidden: !win.extraFieldsVisible}
                            ];
                    break;

                    // АПП Профилактические осмотры несовершеннолетних
                    case 43114:
                        url = '/?c=VolPeriods&m=loadRequestDataAppProf';

                        fields = [
                            {name: 'RequestData_id', type: 'int', header: 'ID', key: true},
                            {name: 'MesAgeGroup_id', header: 'Ид возрастной группы', type: 'int', width: 120, hidden: true},
                            {name: 'SprPlanObj_Code', header: 'Код', type: 'float', width: 45},
                            {name: 'SprPlanObj_Name', header: 'Наименование', type: 'string', width: 410 , autoexpand: false},
                            {name: 'RequestData_AllowPlan', header: 'Разрешить план.', type: win.isMZ == true ? 'checkcolumnedit' : 'checkbox', width: 100, hidden: !win.allowPlanVisible},
                            {name: 'VolCount1', header: fv1, type: 'int', editor: new Ext.form.NumberField()},
                            {name: 'VolCount2', header: fv2, type: 'int', editor: new Ext.form.NumberField()},
                            {name: 'VolCount3', header: fv3, type: 'int', width: 170, editor: new Ext.form.NumberField()},
                            {name: 'VolCount4', header: fv4, type: 'int', width: 120, editor: new Ext.form.NumberField()},
                            {name: 'RequestData_Plan', header: pln, type: 'int',  editor: new Ext.form.NumberField(), css : win.css},
                            {name: 'RequestData_PlanOld', header: 'В т.ч взрослые', type: 'int', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_PlanYoung', header: 'В т.ч дети', type: 'int', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_KP', header: 'КП', type: 'float',  editor: new Ext.form.NumberField(), css : win.css},
                            {name: 'RequestData_KpAdults', header: 'в т.ч взрослые', type: 'float', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_KpKids', header: 'в т.ч дети', type: 'float', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_PlanKP', header: plnkp, type: 'int',  editor: new Ext.form.NumberField(), css : win.css},
                            {name: 'RequestData_PlanKPOld', header: 'В т.ч взрослые', type: 'int', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_PlanKPYoung', header: 'В т.ч дети', type: 'int', hidden: !win.extraFieldsVisible}
                            ];
                    break;

                    // ДС при АПП 
                    case 43115:
                        tabPanel.hideTabStripItem(0);
                        url = '/?c=VolPeriods&m=loadRequestDataDS'

                        fields = 
                        [
                            {name: 'RequestData_id', type: 'int', header: 'ID', key: true},
                            {name: 'MesAgeGroup_id', header: 'Ид возрастной группы', type: 'int', width: 120, hidden: true},
                            {name: 'SprPlanObj_Code', header: 'Код', type: 'float', width: 45},
                            {name: 'SprPlanObj_Name', header: 'Наименование', type: 'string', width: 410 , autoexpand: false},
                            {name: 'RequestData_AllowPlan', header: 'Разрешить <br>план.', type: win.isMZ == true ? 'checkcolumnedit' : 'checkbox', width: 100, hidden: !win.allowPlanVisible},
                            {name: 'RequestData_BedCount', header: 'Среднегодовая занятость <br>стационарозамещающих <br>коек', width: 180, type: 'int',  editor: new Ext.form.NumberField(), css : win.css},
                            {name: 'RequestData_AvgDur', header: 'Средняя <br>длительность <br>пребывания', width: 95, type: 'float',  editor: new Ext.form.NumberField(), css : win.css}

                        ];
                    break;

                    // АПП Первичное посещение
                    case 43116:
                        url = '/?c=VolPeriods&m=loadRequestDataApp';

                        fields = [
                            {name: 'RequestData_id', type: 'int', header: 'ID', key: true},
                            {name: 'MesAgeGroup_id', header: 'Ид возрастной группы', type: 'int', width: 120, hidden: true},
                            {name: 'SprPlanObj_Code', header: 'Код', type: 'float', width: 45},
                            {name: 'SprPlanObj_Name', header: 'Наименование', type: 'string', width: 410 , autoexpand: false},
                            {name: 'RequestData_AllowPlan', header: 'Разрешить <br>план.', type: win.isMZ == true ? 'checkcolumnedit' : 'checkbox', width: 100, hidden: !win.allowPlanVisible},
                            {name: 'VolCount1', header: fv1, type: 'int', editor: new Ext.form.NumberField()},
                            {name: 'VolCount2', header: fv2, type: 'int', editor: new Ext.form.NumberField()},
                            {name: 'VolCount3', header: fv3, type: 'int', width: 170, editor: new Ext.form.NumberField()},
                            {name: 'VolCount4', header: fv4, type: 'int', width: 120, editor: new Ext.form.NumberField()},
                            {name: 'RequestData_Plan', header: pln, type: 'int',  editor: new Ext.form.NumberField(), css : win.css},
                            {name: 'RequestData_PlanOld', header: 'В т.ч <br>взрослые', type: 'int', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_PlanYoung', header: 'В т.ч <br>дети', type: 'int', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_KP', header: 'КП', type: 'float',  editor: new Ext.form.NumberField(), css : win.css},
                            {name: 'RequestData_KpAdults', header: 'в т.ч взрослые', type: 'float', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_KpKids', header: 'в т.ч дети', type: 'float', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_PlanKP', header: plnkp, type: 'int',  editor: new Ext.form.NumberField(), css : win.css},
                            {name: 'RequestData_PlanKPOld', header: 'В т.ч <br>взрослые', type: 'int', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_PlanKPYoung', header: 'В т.ч <br>дети', type: 'int', hidden: !win.extraFieldsVisible}
                            ];
                    break;

                    // АПП Посещение для динамического наблюдения
                    case 43117:
                        url = '/?c=VolPeriods&m=loadRequestDataApp';

                        fields = [
                            {name: 'RequestData_id', type: 'int', header: 'ID', key: true},
                            {name: 'MesAgeGroup_id', header: 'Ид возрастной группы', type: 'int', width: 120, hidden: true},
                            {name: 'SprPlanObj_Code', header: 'Код', type: 'float', width: 45},
                            {name: 'SprPlanObj_Name', header: 'Наименование', type: 'string', width: 410 , autoexpand: false},
                            {name: 'RequestData_AllowPlan', header: 'Разрешить <br>план.', type: win.isMZ == true ? 'checkcolumnedit' : 'checkbox', width: 100, hidden: !win.allowPlanVisible},
                            {name: 'VolCount1', header: fv1, type: 'int', editor: new Ext.form.NumberField()},
                            {name: 'VolCount2', header: fv2, type: 'int', editor: new Ext.form.NumberField()},
                            {name: 'VolCount3', header: fv3, type: 'int', width: 170, editor: new Ext.form.NumberField()},
                            {name: 'VolCount4', header: fv4, type: 'int', width: 120, editor: new Ext.form.NumberField()},
                            {name: 'RequestData_Plan', header: pln, type: 'int',  editor: new Ext.form.NumberField(), css : win.css},
                            {name: 'RequestData_PlanOld', header: 'В т.ч <br>взрослые', type: 'int', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_PlanYoung', header: 'В т.ч <br>дети', type: 'int', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_KP', header: 'КП', type: 'float',  editor: new Ext.form.NumberField(), css : win.css},
                            {name: 'RequestData_KpAdults', header: 'в т.ч взрослые', type: 'float', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_KpKids', header: 'в т.ч дети', type: 'float', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_PlanKP', header: plnkp, type: 'int',  editor: new Ext.form.NumberField(), css : win.css},
                            {name: 'RequestData_PlanKPOld', header: 'В т.ч <br>взрослые', type: 'int', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_PlanKPYoung', header: 'В т.ч <br>дети', type: 'int', hidden: !win.extraFieldsVisible}
                            ];
                    break;
                    
                    // Ответственные лица
                    case 43118:
                        tabPanel.hide();
                        url = '/?c=VolPeriods&m=loadRequestData';
                        
                        fields = [
                            {name: 'RequestData_id', type: 'int', header: 'ID', key: true},
                            {name: 'MesAgeGroup_id', header: 'Ид возрастной группы', type: 'int', width: 120, hidden: true},
                            {name: 'SprPlanObj_Code', header: 'Код', type: 'float', width: 45},
                            {name: 'SprPlanObj_Name', header: 'Наименование', type: 'string', width: 410 , autoexpand: false},
                            {name: 'Post', header: 'Должность', type: 'string', width: 170, editor: new Ext.form.TextField(), css : win.css},
                            {name: 'FIO', header: 'ФИО (полностью)', type: 'string', width: 170, editor: new Ext.form.TextField(), hidden: false, css : win.css},
//                            {name: 'Fam', header: 'Фамилия', type: 'string', width: 170, editor: new Ext.form.TextField(), css : win.css},
//                            {name: 'Nam', header: 'Имя', type: 'string', width: 170, editor: new Ext.form.TextField(), css : win.css},
//                            {name: 'Fnam', header: 'Отчество', type: 'string', width: 170, editor: new Ext.form.TextField(), css : win.css},
                            {name: 'Phone', header: 'Номер телефона', type: 'string', width: 120,  editor: new Ext.form.TextField({
                                        plugins: 
                                            [
                                                new Ext.ux.InputTextMask('+9-999-999-99-99', false)
                                            ]
                                        }), css : win.css},
                            {name: 'Email', header: 'Электронная почта', type: 'string', width: 120, editor: new Ext.form.TextField(), css : win.css}
                            ];
                    break;
                    
                    // АПП консультативные посещения
                    case 43122:
                        url = '/?c=VolPeriods&m=loadRequestDataApp';

                        fields = [
                            {name: 'RequestData_id', type: 'int', header: 'ID', key: true},
                            {name: 'MesAgeGroup_id', header: 'Ид возрастной группы', type: 'int', width: 120, hidden: true},
                            {name: 'SprPlanObj_Code', header: 'Код', type: 'float', width: 155},
                            {name: 'SprPlanObj_Name', header: 'Наименование', type: 'string', width: 410 , autoexpand: false},
                            {name: 'RequestData_AllowPlan', header: 'Разрешить <br>план.', type: win.isMZ == true ? 'checkcolumnedit' : 'checkbox', width: 100, hidden: !win.allowPlanVisible},
                            {name: 'VolCount1', header: fv1, type: 'int', editor: new Ext.form.NumberField()},
                            {name: 'VolCount2', header: fv2, type: 'int', editor: new Ext.form.NumberField()},
                            {name: 'VolCount3', header: fv3, type: 'int', width: 170, editor: new Ext.form.NumberField()},
                            {name: 'VolCount4', header: fv4, type: 'int', width: 120, editor: new Ext.form.NumberField()},
                            {name: 'RequestData_Plan', header: pln, type: 'int',  editor: new Ext.form.NumberField(), css : win.css},
                            {name: 'RequestData_PlanOld', header: 'В т.ч <br>взрослые', type: 'int', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_PlanYoung', header: 'В т.ч <br>дети', type: 'int', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_KP', header: 'КП', type: 'float',  editor: new Ext.form.NumberField(), css : win.css},
                            {name: 'RequestData_KpAdults', header: 'в т.ч взрослые', type: 'float', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_KpKids', header: 'в т.ч дети', type: 'float', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_PlanKP', header: plnkp, type: 'int',  editor: new Ext.form.NumberField(), css : win.css},
                            {name: 'RequestData_PlanKPOld', header: 'В т.ч <br>взрослые', type: 'int', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_PlanKPYoung', header: 'В т.ч <br>дети', type: 'int', hidden: !win.extraFieldsVisible}
                            ];
                    break;
                    
                    // АПП посещения МО, не имеющих прикреп. населения
                    case 43123:
                        url = '/?c=VolPeriods&m=loadRequestDataApp';

                        fields = [
                            {name: 'RequestData_id', type: 'int', header: 'ID', key: true},
                            {name: 'MesAgeGroup_id', header: 'Ид возрастной группы', type: 'int', width: 120, hidden: true},
                            {name: 'SprPlanObj_Code', header: 'Код', type: 'float', width: 45},
                            {name: 'SprPlanObj_Name', header: 'Наименование', type: 'string', width: 410 , autoexpand: false},
                            {name: 'RequestData_AllowPlan', header: 'Разрешить план.', type: win.isMZ == true ? 'checkcolumnedit' : 'checkbox', width: 100, hidden: !win.allowPlanVisible},
                            {name: 'VolCount1', header: fv1, type: 'int', editor: new Ext.form.NumberField()},
                            {name: 'VolCount2', header: fv2, type: 'int', editor: new Ext.form.NumberField()},
                            {name: 'VolCount3', header: fv3, type: 'int', width: 170, editor: new Ext.form.NumberField()},
                            {name: 'VolCount4', header: fv4, type: 'int', width: 120, editor: new Ext.form.NumberField()},
                            {name: 'RequestData_Plan', header: pln, type: 'int',  /*editor: new Ext.form.NumberField(), css : win.css*/},
                            {name: 'RequestData_PlanOld', header: 'В т.ч <br>взрослые', type: 'int', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_PlanYoung', header: 'В т.ч <br>дети', type: 'int', hidden: !win.extraFieldsVisible},
                            {name: 'DispNab', header: 'В т.ч. для проведения <br>диспансерного <br>наблюдения', type: 'int', editor: new Ext.form.NumberField(), css : win.css,  width: 115},
                            {name: 'DispNabAdults', header: 'В т.ч взрослые', type: 'int', hidden: !win.extraFieldsVisible,  width: 115},
                            {name: 'DispNabKids', header: 'В т.ч дети', type: 'int', hidden: !win.extraFieldsVisible,  width: 115},
                            {name: 'RazObrCount', header: 'в т.ч. разовые <br>посещения в связи <br>с заболеваниями', type: 'int', hidden: /*(win.AgeTabPanel.getActiveTab().id == 'tab_all')*/ false,  editor: new Ext.form.NumberField(), css : win.css, width: 115},
                            {name: 'RazObrCountAdults', header: 'В т.ч взрослые', type: 'int', hidden: !win.extraFieldsVisible, width: 115},
                            {name: 'RazObrCountKids', header: 'В т.ч дети', type: 'int', hidden: !win.extraFieldsVisible, width: 115},
                            {name: 'MedReab', header: /*'В т.ч. посещения <br>по медицинской <br>реабилитации'*/ 'В т.ч. средний МП', type: 'int', editor: new Ext.form.NumberField(), css : win.css,  width: 115},
                            {name: 'MedReabAdults', header: 'В т.ч взрослые', type: 'int', hidden: !win.extraFieldsVisible,  width: 115},
                            {name: 'MedReabKids', header: 'В т.ч дети', type: 'int', hidden: !win.extraFieldsVisible,  width: 115},
                            {name: 'OtherPurp', header: 'В т.ч. посещения <br>с другой <br>целью', type: 'int', editor: new Ext.form.NumberField(), css : win.css,  width: 115},
                            {name: 'OtherPurpAdults', header: 'В т.ч взрослые', type: 'int', hidden: !win.extraFieldsVisible,  width: 115},
                            {name: 'OtherPurpKids', header: 'В т.ч дети', type: 'int', hidden: !win.extraFieldsVisible,  width: 115},
                            {name: 'RequestData_KP', header: 'КП', type: 'float',  editor: new Ext.form.NumberField(), css : win.css},
                            {name: 'RequestData_KpAdults', header: 'в т.ч взрослые', type: 'float', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_KpKids', header: 'в т.ч дети', type: 'float', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_PlanKP', header: plnkp, type: 'int' },//,  editor: new Ext.form.NumberField(), css : win.css},
                            {name: 'RequestData_PlanKPOld', header: 'В т.ч <br>взрослые', type: 'int', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_PlanKPYoung', header: 'В т.ч <br>дети', type: 'int', hidden: !win.extraFieldsVisible },

                            {name: 'DispNabPlanKP', header: 'В т.ч. для ДН <br>по КП', type: 'int', editor: new Ext.form.NumberField(), css: win.css},
                            {name: 'DispNabPlanKPAdults', header: 'В т.ч взрослые', type: 'int', hidden: !win.extraFieldsVisible },
                            {name: 'DispNabPlanKPKids', header: 'В т.ч дети', type: 'int', hidden: !win.extraFieldsVisible },
                            {name: 'RazObrCountPlanKP', header: 'В т.ч. разовые <br>по заболеванию <br>по КП', type: 'int', editor: new Ext.form.NumberField(), css: win.css },
                            {name: 'RazObrCountPlanKPAdults', header: 'В т.ч взрослые', type: 'int', hidden: !win.extraFieldsVisible },
                            {name: 'RazObrCountPlanKPKids', header: 'В т.ч дети', type: 'int', hidden: !win.extraFieldsVisible },
                            {name: 'MidMedStaffPlanKP', header: 'В т.ч. средний <br>МП', type: 'int', editor: new Ext.form.NumberField(), css: win.css },
                            {name: 'MidMedStaffPlanKPAdults', header: 'В т.ч взрослые', type: 'int', hidden: !win.extraFieldsVisible },
                            {name: 'MidMedStaffPlanKPKids', header: 'В т.ч дети', type: 'int', hidden: !win.extraFieldsVisible },
                            {name: 'OtherPurpPlanKP', header: 'В т.ч. с другими <br>целями по КП', type: 'int', editor: new Ext.form.NumberField(), css: win.css },
                            {name: 'OtherPurpPlanKPAdults', header: 'В т.ч взрослые', type: 'int', hidden: !win.extraFieldsVisible },
                            {name: 'OtherPurpPlanKPKids', header: 'В т.ч дети', type: 'int', hidden: !win.extraFieldsVisible }
                            ];
                    break;
                    
                    // АПП Экстренные пациенты с ОПН
                    case 43124:
                        url = '/?c=VolPeriods&m=loadRequestDataZpt';

                        fields = [
                            {name: 'RequestData_id', type: 'int', header: 'ID', key: true},
                            {name: 'MesAgeGroup_id', header: 'Ид возрастной группы', type: 'int', width: 120, hidden: true},
                            {name: 'SprPlanObj_Code', header: 'Код', type: 'string', width: 155},
                            {name: 'SprPlanObj_Name', header: 'Наименование', type: 'string', width: 410 , autoexpand: false},
                            {name: 'RequestData_AllowPlan', header: 'Разрешить <br>план.', type: win.isMZ == true ? 'checkcolumnedit' : 'checkbox', width: 100, hidden: !win.allowPlanVisible},
                            {name: 'VolCount1', header: fv1, type: 'int', editor: new Ext.form.NumberField()},
                            {name: 'VolCount2', header: fv2, type: 'int', editor: new Ext.form.NumberField()},
                            {name: 'VolCount3', header: fv3, type: 'int', width: 170, editor: new Ext.form.NumberField()},
                            {name: 'VolCount4', header: fv4, type: 'int', width: 120, editor: new Ext.form.NumberField()},
                            {name: 'RequestData_Plan', header: pln, type: 'int',  editor: new Ext.form.NumberField(), css : win.css},
                            {name: 'RequestData_PlanOld', header: 'В т.ч <br>взрослые', type: 'int', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_PlanYoung', header: 'В т.ч <br>дети', type: 'int', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_KP', header: 'КП', type: 'float',  editor: new Ext.form.NumberField(), css : win.css},
                            {name: 'RequestData_KpAdults', header: 'в т.ч взрослые', type: 'float', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_KpKids', header: 'в т.ч дети', type: 'float', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_PlanKP', header: plnkp, type: 'int',  editor: new Ext.form.NumberField(), css : win.css},
                            {name: 'RequestData_PlanKPOld', header: 'В т.ч <br>взрослые', type: 'int', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_PlanKPYoung', header: 'В т.ч <br>дети', type: 'int', hidden: !win.extraFieldsVisible}
                            ];
                    break;
                    
                    // ДС пациенты с ХПН
                    case 43125:
                        url = '/?c=VolPeriods&m=loadRequestDataZpt';

                        fields = [
                            {name: 'RequestData_id', type: 'int', header: 'ID', key: true},
                            {name: 'MesAgeGroup_id', header: 'Ид возрастной группы', type: 'int', width: 120, hidden: true},
                            {name: 'SprPlanObj_Code', header: 'Код', type: 'string', width: 155},
                            {name: 'SprPlanObj_Name', header: 'Наименование', type: 'string', width: 410 , autoexpand: false},
                            {name: 'RequestData_AllowPlan', header: 'Разрешить <br>план.', type: win.isMZ == true ? 'checkcolumnedit' : 'checkbox', width: 100, hidden: !win.allowPlanVisible},
                            {name: 'VolCount1', header: fv1, type: 'int', editor: new Ext.form.NumberField()},
                            {name: 'VolCount2', header: fv2, type: 'int', editor: new Ext.form.NumberField()},
                            {name: 'VolCount3', header: fv3, type: 'int', width: 170, editor: new Ext.form.NumberField()},
                            {name: 'VolCount4', header: fv4, type: 'int', width: 120, editor: new Ext.form.NumberField()},
                            {name: 'RequestData_Plan', header: pln, type: 'int',  editor: new Ext.form.NumberField(), css : win.css},
                            {name: 'RequestData_PlanOld', header: 'В т.ч <br>взрослые', type: 'int', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_PlanYoung', header: 'В т.ч <br>дети', type: 'int', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_KP', header: 'КП', type: 'float',  editor: new Ext.form.NumberField(), css : win.css},
                            {name: 'RequestData_KpAdults', header: 'в т.ч взрослые', type: 'float', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_KpKids', header: 'в т.ч дети', type: 'float', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_PlanKP', header: plnkp, type: 'int',  editor: new Ext.form.NumberField(), css : win.css},
                            {name: 'RequestData_PlanKPOld', header: 'В т.ч <br>взрослые', type: 'int', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_PlanKPYoung', header: 'В т.ч <br>дети', type: 'int', hidden: !win.extraFieldsVisible}
                            ];
                    break;
                    
                    // КС экстренные пациенты с ОПН
                    case 43126:
                        url = '/?c=VolPeriods&m=loadRequestDataZpt';

                        fields = [
                            {name: 'RequestData_id', type: 'int', header: 'ID', key: true},
                            {name: 'MesAgeGroup_id', header: 'Ид возрастной группы', type: 'int', width: 120, hidden: true},
                            {name: 'SprPlanObj_Code', header: 'Код', type: 'string', width: 155},
                            {name: 'SprPlanObj_Name', header: 'Наименование', type: 'string', width: 410 , autoexpand: false},
                            {name: 'RequestData_AllowPlan', header: 'Разрешить <br>план.', type: win.isMZ == true ? 'checkcolumnedit' : 'checkbox', width: 100, hidden: !win.allowPlanVisible},
                            {name: 'VolCount1', header: fv1, type: 'int', editor: new Ext.form.NumberField()},
                            {name: 'VolCount2', header: fv2, type: 'int', editor: new Ext.form.NumberField()},
                            {name: 'VolCount3', header: fv3, type: 'int', width: 170, editor: new Ext.form.NumberField()},
                            {name: 'VolCount4', header: fv4, type: 'int', width: 120, editor: new Ext.form.NumberField()},
                            {name: 'RequestData_Plan', header: pln, type: 'int',  editor: new Ext.form.NumberField(), css : win.css},
                            {name: 'RequestData_PlanOld', header: 'В т.ч <br>взрослые', type: 'int', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_PlanYoung', header: 'В т.ч <br>дети', type: 'int', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_KP', header: 'КП', type: 'float',  editor: new Ext.form.NumberField(), css : win.css},
                            {name: 'RequestData_KpAdults', header: 'в т.ч взрослые', type: 'float', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_KpKids', header: 'в т.ч дети', type: 'float', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_PlanKP', header: plnkp, type: 'int',  editor: new Ext.form.NumberField(), css : win.css},
                            {name: 'RequestData_PlanKPOld', header: 'В т.ч <br>взрослые', type: 'int', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_PlanKPYoung', header: 'В т.ч <br>дети', type: 'int', hidden: !win.extraFieldsVisible}
                            ];
                    break;
                    
                    // АПП пациенты с ХПН
                    case 43127:
                        url = '/?c=VolPeriods&m=loadRequestDataZpt';

                        fields = [
                            {name: 'RequestData_id', type: 'int', header: 'ID', key: true},
                            {name: 'MesAgeGroup_id', header: 'Ид возрастной группы', type: 'int', width: 120, hidden: true},
                            {name: 'SprPlanObj_Code', header: 'Код', type: 'string', width: 155},
                            {name: 'SprPlanObj_Name', header: 'Наименование', type: 'string', width: 410 , autoexpand: false},
                            {name: 'RequestData_AllowPlan', header: 'Разрешить <br>план.', type: win.isMZ == true ? 'checkcolumnedit' : 'checkbox', width: 100, hidden: !win.allowPlanVisible},
                            {name: 'VolCount1', header: fv1, type: 'int', editor: new Ext.form.NumberField()},
                            {name: 'VolCount2', header: fv2, type: 'int', editor: new Ext.form.NumberField()},
                            {name: 'VolCount3', header: fv3, type: 'int', width: 170, editor: new Ext.form.NumberField()},
                            {name: 'VolCount4', header: fv4, type: 'int', width: 120, editor: new Ext.form.NumberField()},
                            {name: 'RequestData_Plan', header: pln, type: 'int',  editor: new Ext.form.NumberField(), css : win.css},
                            {name: 'RequestData_PlanOld', header: 'В т.ч <br>взрослые', type: 'int', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_PlanYoung', header: 'В т.ч <br>дети', type: 'int', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_KP', header: 'КП', type: 'float',  editor: new Ext.form.NumberField(), css : win.css},
                            {name: 'RequestData_KpAdults', header: 'в т.ч взрослые', type: 'float', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_KpKids', header: 'в т.ч дети', type: 'float', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_PlanKP', header: plnkp, type: 'int',  editor: new Ext.form.NumberField(), css : win.css},
                            {name: 'RequestData_PlanKPOld', header: 'В т.ч <br>взрослые', type: 'int', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_PlanKPYoung', header: 'В т.ч <br>дети', type: 'int', hidden: !win.extraFieldsVisible}
                            ];
                    break;
                    
                    // КС пациенты с ХПН
                    case 43128:
                        url = '/?c=VolPeriods&m=loadRequestDataZpt';

                        fields = [
                            {name: 'RequestData_id', type: 'int', header: 'ID', key: true},
                            {name: 'MesAgeGroup_id', header: 'Ид возрастной группы', type: 'int', width: 120, hidden: true},
                            {name: 'SprPlanObj_Code', header: 'Код', type: 'string', width: 155},
                            {name: 'SprPlanObj_Name', header: 'Наименование', type: 'string', width: 410 , autoexpand: false},
                            {name: 'RequestData_AllowPlan', header: 'Разрешить <br>план.', type: win.isMZ == true ? 'checkcolumnedit' : 'checkbox', width: 100, hidden: !win.allowPlanVisible},
                            {name: 'VolCount1', header: fv1, type: 'int', editor: new Ext.form.NumberField()},
                            {name: 'VolCount2', header: fv2, type: 'int', editor: new Ext.form.NumberField()},
                            {name: 'VolCount3', header: fv3, type: 'int', width: 170, editor: new Ext.form.NumberField()},
                            {name: 'VolCount4', header: fv4, type: 'int', width: 120, editor: new Ext.form.NumberField()},
                            {name: 'RequestData_Plan', header: pln, type: 'int',  editor: new Ext.form.NumberField(), css : win.css},
                            {name: 'RequestData_PlanOld', header: 'В т.ч <br>взрослые', type: 'int', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_PlanYoung', header: 'В т.ч <br>дети', type: 'int', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_KP', header: 'КП', type: 'float',  editor: new Ext.form.NumberField(), css : win.css},
                            {name: 'RequestData_KpAdults', header: 'в т.ч взрослые', type: 'float', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_KpKids', header: 'в т.ч дети', type: 'float', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_PlanKP', header: plnkp, type: 'int',  editor: new Ext.form.NumberField(), css : win.css},
                            {name: 'RequestData_PlanKPOld', header: 'В т.ч <br>взрослые', type: 'int', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_PlanKPYoung', header: 'В т.ч <br>дети', type: 'int', hidden: !win.extraFieldsVisible}
                            ];
                    break;
                    
                    // ЭКО
                    case 43129:
                        url = '/?c=VolPeriods&m=loadRequestDataEco';

                        fields = [
                            {name: 'RequestData_id', type: 'int', header: 'ID', key: true},
                            {name: 'MesAgeGroup_id', header: 'Ид возрастной группы', type: 'int', width: 120, hidden: true},
                            {name: 'SprPlanObj_Code', header: 'Код', type: 'string', width: 155},
                            {name: 'SprPlanObj_Name', header: 'Наименование', type: 'string', width: 410 , autoexpand: false},
                            {name: 'RequestData_AllowPlan', header: 'Разрешить <br>план.', type: win.isMZ == true ? 'checkcolumnedit' : 'checkbox', width: 100, hidden: !win.allowPlanVisible},
                            {name: 'VolCount1', header: fv1, type: 'int', editor: new Ext.form.NumberField()},
                            {name: 'VolCount2', header: fv2, type: 'int', editor: new Ext.form.NumberField()},
                            {name: 'VolCount3', header: fv3, type: 'int', width: 170, editor: new Ext.form.NumberField()},
                            {name: 'VolCount4', header: fv4, type: 'int', width: 120, editor: new Ext.form.NumberField()},
                            {name: 'RequestData_Plan', header: pln, type: 'int',  editor: new Ext.form.NumberField(), css : win.css},
                            {name: 'RequestData_PlanOld', header: 'В т.ч <br>взрослые', type: 'int', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_PlanYoung', header: 'В т.ч <br>дети', type: 'int', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_KP', header: 'КП', type: 'float',  editor: new Ext.form.NumberField(), css : win.css},
                            {name: 'RequestData_KpAdults', header: 'в т.ч взрослые', type: 'float', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_KpKids', header: 'в т.ч дети', type: 'float', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_PlanKP', header: plnkp, type: 'int',  editor: new Ext.form.NumberField(), css : win.css},
                            {name: 'RequestData_PlanKPOld', header: 'В т.ч <br>взрослые', type: 'int', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_PlanKPYoung', header: 'В т.ч <br>дети', type: 'int', hidden: !win.extraFieldsVisible}
                            ];
                    break;

                    // АПП Неотложка Травматологические пункты
                    case 43131:
                        url = '/?c=VolPeriods&m=loadRequestDataApp';

                        fields = [
                            {name: 'RequestData_id', type: 'int', header: 'ID', key: true},
                            {name: 'MesAgeGroup_id', header: 'Ид возрастной группы', type: 'int', width: 120, hidden: true},
                            {name: 'SprPlanObj_Code', header: 'Код', type: 'float', width: 155},
                            {name: 'SprPlanObj_Name', header: 'Наименование', type: 'string', width: 410 , autoexpand: false},
                            {name: 'RequestData_AllowPlan', header: 'Разрешить <br>план.', type: win.isMZ == true ? 'checkcolumnedit' : 'checkbox', width: 100, hidden: !win.allowPlanVisible},
                            {name: 'VolCount1', header: fv1, type: 'int', editor: new Ext.form.NumberField()},
                            {name: 'VolCount2', header: fv2, type: 'int', editor: new Ext.form.NumberField()},
                            {name: 'VolCount3', header: fv3, type: 'int', width: 170, editor: new Ext.form.NumberField()},
                            {name: 'VolCount4', header: fv4, type: 'int', width: 120, editor: new Ext.form.NumberField()},
                            {name: 'RequestData_Plan', header: pln, type: 'int',  editor: new Ext.form.NumberField(), css : win.css},
                            {name: 'RequestData_PlanOld', header: 'В т.ч <br>взрослые', type: 'int', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_PlanYoung', header: 'В т.ч <br>дети', type: 'int', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_KP', header: 'КП', type: 'float',  editor: new Ext.form.NumberField(), css : win.css},
                            {name: 'RequestData_KpAdults', header: 'в т.ч взрослые', type: 'float', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_KpKids', header: 'в т.ч дети', type: 'float', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_PlanKP', header: plnkp, type: 'int',  editor: new Ext.form.NumberField(), css : win.css},
                            {name: 'RequestData_PlanKPOld', header: 'В т.ч <br>взрослые', type: 'int', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_PlanKPYoung', header: 'В т.ч <br>дети', type: 'int', hidden: !win.extraFieldsVisible}
                            ];
                    break;

                    // АПП Посещения НМП, имеющие прикрепленное население
                    case 43132:
                        url = '/?c=VolPeriods&m=loadRequestDataApp';

                        fields = [
                            {name: 'RequestData_id', type: 'int', header: 'ID', key: true},
                            {name: 'MesAgeGroup_id', header: 'Ид возрастной группы', type: 'int', width: 120, hidden: true},
                            {name: 'SprPlanObj_Code', header: 'Код', type: 'float', width: 155},
                            {name: 'SprPlanObj_Name', header: 'Наименование', type: 'string', width: 410 , autoexpand: false},
                            {name: 'RequestData_AllowPlan', header: 'Разрешить <br>план.', type: win.isMZ == true ? 'checkcolumnedit' : 'checkbox', width: 100, hidden: !win.allowPlanVisible},
                            {name: 'VolCount1', header: fv1, type: 'int', editor: new Ext.form.NumberField()},
                            {name: 'VolCount2', header: fv2, type: 'int', editor: new Ext.form.NumberField()},
                            {name: 'VolCount3', header: fv3, type: 'int', width: 170, editor: new Ext.form.NumberField()},
                            {name: 'VolCount4', header: fv4, type: 'int', width: 120, editor: new Ext.form.NumberField()},
                            {name: 'RequestData_Plan', header: pln, type: 'int',  editor: new Ext.form.NumberField(), css : win.css},
                            {name: 'RequestData_PlanOld', header: 'В т.ч <br>взрослые', type: 'int', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_PlanYoung', header: 'В т.ч <br>дети', type: 'int', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_KP', header: 'КП', type: 'float',  editor: new Ext.form.NumberField(), css : win.css},
                            {name: 'RequestData_KpAdults', header: 'в т.ч взрослые', type: 'float', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_KpKids', header: 'в т.ч дети', type: 'float', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_PlanKP', header: plnkp, type: 'int',  editor: new Ext.form.NumberField(), css : win.css},
                            {name: 'RequestData_PlanKPOld', header: 'В т.ч <br>взрослые', type: 'int', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_PlanKPYoung', header: 'В т.ч <br>дети', type: 'int', hidden: !win.extraFieldsVisible}
                            ];
                    break;

                    // АПП Посещения НМП, не имеющие прикрепленное население
                    case 43133:
                        url = '/?c=VolPeriods&m=loadRequestDataApp';

                        fields = [
                            {name: 'RequestData_id', type: 'int', header: 'ID', key: true},
                            {name: 'MesAgeGroup_id', header: 'Ид возрастной группы', type: 'int', width: 120, hidden: true},
                            {name: 'SprPlanObj_Code', header: 'Код', type: 'float', width: 155},
                            {name: 'SprPlanObj_Name', header: 'Наименование', type: 'string', width: 410 , autoexpand: false},
                            {name: 'RequestData_AllowPlan', header: 'Разрешить <br>план.', type: win.isMZ == true ? 'checkcolumnedit' : 'checkbox', width: 100, hidden: !win.allowPlanVisible},
                            {name: 'VolCount1', header: fv1, type: 'int', editor: new Ext.form.NumberField()},
                            {name: 'VolCount2', header: fv2, type: 'int', editor: new Ext.form.NumberField()},
                            {name: 'VolCount3', header: fv3, type: 'int', width: 170, editor: new Ext.form.NumberField()},
                            {name: 'VolCount4', header: fv4, type: 'int', width: 120, editor: new Ext.form.NumberField()},
                            {name: 'RequestData_Plan', header: pln, type: 'int',  editor: new Ext.form.NumberField(), css : win.css},
                            {name: 'RequestData_PlanOld', header: 'В т.ч <br>взрослые', type: 'int', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_PlanYoung', header: 'В т.ч <br>дети', type: 'int', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_KP', header: 'КП', type: 'float',  editor: new Ext.form.NumberField(), css : win.css},
                            {name: 'RequestData_KpAdults', header: 'в т.ч взрослые', type: 'float', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_KpKids', header: 'в т.ч дети', type: 'float', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_PlanKP', header: plnkp, type: 'int',  editor: new Ext.form.NumberField(), css : win.css},
                            {name: 'RequestData_PlanKPOld', header: 'В т.ч <br>взрослые', type: 'int', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_PlanKPYoung', header: 'В т.ч <br>дети', type: 'int', hidden: !win.extraFieldsVisible}
                            ];
                    break;

                    // АПП Объемы обращений по заболеваниям на долечивание в травматологических пунктах
                    case 43134:
                        url = '/?c=VolPeriods&m=loadRequestDataApp';

                        fields = [
                            {name: 'RequestData_id', type: 'int', header: 'ID', key: true},
                            {name: 'MesAgeGroup_id', header: 'Ид возрастной группы', type: 'int', width: 120, hidden: true},
                            {name: 'SprPlanObj_Code', header: 'Код', type: 'float', width: 45},
                            {name: 'SprPlanObj_Name', header: 'Наименование', type: 'string', width: 410 , autoexpand: false},
                            {name: 'RequestData_AllowPlan', header: 'Разрешить <br>план.', type: win.isMZ == true ? 'checkcolumnedit' : 'checkbox', width: 100, hidden: !win.allowPlanVisible},
                            {name: 'VolCount1', header: fv1, type: 'int', editor: new Ext.form.NumberField()},
                            {name: 'VolCount2', header: fv2, type: 'int', editor: new Ext.form.NumberField()},
                            {name: 'VolCount3', header: fv3, type: 'int', width: 170, editor: new Ext.form.NumberField()},
                            {name: 'VolCount4', header: fv4, type: 'int', width: 120, editor: new Ext.form.NumberField()},
                            {name: 'RequestData_Plan', header: pln, type: 'int',  editor: new Ext.form.NumberField(), css : win.css},
                            {name: 'RequestData_PlanOld', header: 'В т.ч <br>взрослые', type: 'int', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_PlanYoung', header: 'В т.ч <br>дети', type: 'int', hidden: !win.extraFieldsVisible},
                            {name: 'RazObrCount', header: 'Кол-во посещений в <br>обращении из Заявки <br>на ' + win.PlanYear +  ' г. (кратн. не менее 2)',  type: 'int', hidden: false/*!(win.SprVidMp_id==13)*/, editor: new Ext.form.NumberField(), css : win.css, width: 170},
                            {name: 'RazObrCountAdults', header: 'В т.ч <br>взрослые',  type: 'int', hidden: !win.extraFieldsVisible, width: 170},
                            {name: 'RazObrCountKids', header: 'В т.ч <br>дети',  type: 'int', hidden: !win.extraFieldsVisible, width: 170},
                            {name: 'RequestData_KP', header: 'КП', type: 'float',  editor: new Ext.form.NumberField(), css : win.css},
                            {name: 'RequestData_KpAdults', header: 'в т.ч взрослые', type: 'float', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_KpKids', header: 'в т.ч дети', type: 'float', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_PlanKP', header: plnkp, type: 'int',  editor: new Ext.form.NumberField(), css : win.css},
                            {name: 'RequestData_PlanKPOld', header: 'В т.ч <br>взрослые', type: 'int', hidden: !win.extraFieldsVisible},
                            {name: 'RequestData_PlanKPYoung', header: 'В т.ч <br>дети', type: 'int', hidden: !win.extraFieldsVisible}
                            ];
                    break;
                }
                win.addObjGrid(fields, win.searchParams, url, toGroup);
                win.hideRouteFields();
                //win.hideMedReabFields();
                win.hideAgeFields();
            }
        },
        
        doResetFiltersVolumeTypeGrid: function () 
        {
            var filtersForm = this.VolumeTypeGridFilters.getForm();
            filtersForm.reset();
	},
        
        doResetFiltersObjGrid: function () 
        {
            var filtersForm = this.VolumeTypeAttributeValueGridFilters.getForm();
            filtersForm.reset();
	},
        
        doSearch: function()
        {
            var params = {};
            params.SprVidMp_id = this.SprVidMp_id;
            params.RequestList_id = this.RequestList_id;

            this.VolumeTypeGrid.removeAll();
            this.VolumeTypeGrid.loadData({globalFilters: params});
        },
        
        doFilterCat: function()
        {
            var win = this;
            var catCode = Ext.getCmp('idFilter1Code').getValue();
            var catName = Ext.getCmp('idFilter1Name').getValue();
            var catNoVol = Ext.getCmp('idCBNoVol').getValue();
            
            var params = {};
            params.SprVidMp_id = this.SprVidMp_id;
            params.RequestList_id = this.RequestList_id;
            
            if (catCode)
            {
                params.catCode = catCode;
            }
            
            if (catName)
            {
                params.catName = catName;
            }
            
            params.catNoVol = catNoVol.toString();
            
            win.VolumeTypeGrid.removeAll();
            win.VolumeTypeGrid.getGrid().getStore().baseParams = {};
            win.VolumeTypeGrid.loadData({globalFilters: params});
        },
        
        doFilterObj: function()
        {
            var win = this;
            var objName = Ext.getCmp('idFilter2Name').getValue();
            var allowPlan = Ext.getCmp('idFilterAllowPlan').getValue();
            
            win.searchParams.RequestList_id = this.RequestList_id;
            switch (win.AgeTabPanel.getActiveTab().id) 
            {
                case 'tab_adults':
                    win.searchParams.MesAgeGroup_id = 1;
                    if (win.searchParams.Osmotr == 1) {
                        win.searchParams.VolumeType_id = 44;
                    }
                break;
                case 'tab_kids':
                    win.searchParams.MesAgeGroup_id = 2;
                    if (win.searchParams.Osmotr == 1) {
                        win.searchParams.VolumeType_id = 45;
                    }
                break;
                case 'tab_all':
                    win.searchParams.MesAgeGroup_id = 3;
                    if (win.searchParams.Osmotr == 1) {
                        win.searchParams.VolumeType_id = 45;
                    }
                break;
            }
            //params_.MesAgeGroup_id = win.searchParams.MesAgeGroup_id;
            

            if (objName.length > 0) 
            {
                win.searchParams.objName = objName;
            }
            else
            {
                delete win.searchParams.objName;
            }
            
            if (allowPlan > 0) 
            {
                switch (allowPlan)
                {
                    case '1':
                        win.searchParams.allowPlan = '1';
                    break;
                    case '2':
                        win.searchParams.allowPlan = '0';
                    break;
                    case '3':
                        delete win.searchParams.allowPlan;
                    break;
                }
            }
            else
            {
                delete win.searchParams.allowPlan;
            }
                    
            var grid = Ext.getCmp('idObjGrid')
            grid.removeAll();
            grid.getGrid().getStore().baseParams = {};
            grid.loadData(
            {
                globalFilters: win.searchParams,
                callback: function() 
                {

                }                    
            });
        },
        
        setObjEditable: function(grid, record, category, fields)
        {
            var win = this;
            var allowPlan = ['true',1].includes(record.get('RequestData_AllowPlan')) && 
                            ![1,43118].includes(category);
            
            var editable = ((win.StatusName == 'В работе' || 
                                   win.StatusName == 'Отклонена') && 
                                   allowPlan);
                           
            var editableDS = ((win.StatusName == 'В работе' || 
                                win.StatusName == 'Отклонена' || 
                                win.StatusName == 'Рассчитан КП') && 
                                allowPlan);       

            var editableFact = (win.StatusName == 'В работе' || 
                                win.StatusName == 'Отклонена' ||
                                win.StatusName == 'Новая'
                                );
                        
            var editablePlanKp = ((win.StatusName == 'Рассчитан КП' ||
                                    win.StatusName == 'В работе') && 
                                    allowPlan
                                );
            
            var editableSprInf = !(win.StatusName == 'Утверждена' ||
                                  win.StatusName == 'Сформирована'
                                  );
                        
            var cols = [];
            
            // отберем редактируемые поля
            fields.forEach(
                function(item, i, fields)
                {
                    if (item.editor)
                    {
                        cols.push(item);
                    }
                }
            );
            
            switch(category)
            {
                case 1: // Справочная информация
                    cols.forEach(
                        function(item, i, cols)
                        {
                            grid.getColumnModel().setEditable(grid.getColumnModel().findColumnIndex(item.name), editableSprInf);
                        }
                    );
                    break;
                case 43111:
                case 43112:
                case 43123:
                    cols.forEach(
                        function (item, i, cols) {
                            grid.getColumnModel().setEditable(grid.getColumnModel().findColumnIndex(item.name), editablePlanKp);
                        }
                    );
                    break;
                case 43118: // Ответственные лица
                    cols.forEach(
                        function(item, i, cols)
                        {
                            grid.getColumnModel().setEditable(grid.getColumnModel().findColumnIndex(item.name), true);
                        }
                    );
                    break;
                case 8: // ДС при АПП (при поликлинике)
                case 10: // Стационар дневного пребывания при стационаре
                    cols.forEach(
                        function(item, i, cols)
                        {
                            grid.getColumnModel().setEditable(grid.getColumnModel().findColumnIndex(item.name), editableDS);
                        }
                    );
                    break;
                default:
                    cols.forEach(
                        function(item, i, cols)
                        {
                            grid.getColumnModel().setEditable(grid.getColumnModel().findColumnIndex(item.name), editable);
                        }
                    );
            
                    switch (win.SprVidMp_id)
                    {
                        case 10:
                            var ret;
                            Ext.Ajax.request({
                                failure:function () {
                                    sw.swMsg.alert(lang['oshibka'], lang['ne_udalos_poluchit_dannyie_s_servera']);
                                },
                                params:{
                                    RequestList_id: win.RequestList_id,
                                    SprPlanObj_Code: record.data.SprPlanObj_Code
                                },
                                success: function (response) {
                                    var result = Ext.util.JSON.decode(response.responseText);
                                    ret = result[0].rslt;
                                    if (ret == 0)
                                    {
                                        sw.swMsg.alert('Ошибка', 'Не указано количество бригад<br><br>Для ввода плана укажите значение<br>количества бригад в категории "Справочная информация"');
                                        grid.getColumnModel().setEditable(sm.grid.getColumnModel().findColumnIndex('RequestData_Plan'), false);
                                    }
                                    else
                                    {
                                        grid.getColumnModel().setEditable(sm.grid.getColumnModel().findColumnIndex('RequestData_Plan'), editable);
                                    }
                                },
                                url:'/?c=VolPeriods&m=checkSmpTeamExists'
                            });
                            break;
                    }
            }
            grid.getColumnModel().setEditable(grid.getColumnModel().findColumnIndex('VolCount1'), win.isMZ && editableFact);
            grid.getColumnModel().setEditable(grid.getColumnModel().findColumnIndex('VolCount2'), win.isMZ && editableFact);
            grid.getColumnModel().setEditable(grid.getColumnModel().findColumnIndex('VolCount3'), win.isMZ && editableFact);
            grid.getColumnModel().setEditable(grid.getColumnModel().findColumnIndex('VolCount4'), win.isMZ && editableFact);
            grid.getColumnModel().setEditable(grid.getColumnModel().findColumnIndex('RequestData_KP'), win.isMZ);
            grid.getColumnModel().setEditable(grid.getColumnModel().findColumnIndex('RequestData_PlanKP'), editablePlanKp && ![43111,43112,43123].includes(category));
        },
        
        addObjGrid: function(fields, s_params, url, group) 
        {
            
            var win = this;
            var grid = null;
            var gridCm = null;
            var st = null;
            var panel = Ext.getCmp('idGridPanel');
            
            panel.removeAll();
            panel.add(new sw.Promed.ViewFrame(
            {
                id: 'idObjGrid',
                editing: true,
                dataUrl: url,
                enableKeyEvents: true,
                title: 'Объекты категорий планирования',
                border: false,
                region: 'center',
                cls: 'txtwrap',
                autoLoadData: false,
                readOnly: false,
                setReadOnly: false,
                grouping: true,
                stringfields: fields,
                layout: 'fit',
                saveAtOnce: false,
                selectionModel: 'row',
                noSelectFirstRowOnFocus: true,
                totalProperty: 'totalCount',
                onDblClick: function()
                {
                    return;
                },
                onRowSelect: function(sm, index, record) 
                {
                    win.setObjEditable(sm.grid,record,win.currentCategory,fields);
                    win.updateParams.LpuLicence_id_o = Ext.getCmp('idObjGrid').getGrid().getSelectionModel().getSelected().data.LpuLicence_id;
                },
                onCellSelect: function(sm, ri, ci) 
                {
                    var grid = Ext.getCmp('idObjGrid').getGrid();
                    var rec = sm.selection.record;
                    var smpNoTeam = null;
                    if ((win.currentCategory !=2) && (win.currentCategory !=4))
                    {
                        smpNoTeam = win.checkSmpTeamExists(grid, rec, win.RequestList_id);
                    }
                    else
                    {
                        smpNoTeam = true;
                    }

                    if ((win.SprVidMp_id == 10) && (win.currentCategory != 1))
                    {
                        win.checkSmpTeamExists(grid, rec, win.RequestList_id);
                    }
                    if (win.SprVidMp_id == 1)
                    {
                        
                    }
                },
                onAfterEdit: function(o)
                {
                    if (o.value < 0)
                    {
                        sw.swMsg.show(
                            {
                                buttons: Ext.Msg.OK,
                                icon: Ext.Msg.WARNING,
                                msg: 'Значение не может быть отрицательным',
                                title: 'Внимание!'
                            }
                        );

                        win.VolumeTypeGrid.loadData();
                        return;
                    }
                    o.grid.stopEditing();
                    var grid = o.grid;
                    var currentRow = grid.getSelectionModel().last;
                    var targetRow = 0;
                    var rowCount = grid.store.totalLength;
                    if (o.field != 'RequestData_AllowPlan')
                    {
                        for (currentRow; currentRow < rowCount-1; currentRow++)
                        {
							if (grid.getStore().data.items[currentRow + 1] && (grid.getStore().data.items[currentRow + 1].data.RequestData_AllowPlan == 'true' || grid.getStore().data.items[currentRow + 1].data.RequestData_AllowPlan == 1) )
                            {
                                o.row = currentRow + 1;
                                targetRow = currentRow + 1;
                                grid.stopEditing();
                                break;
                            }
                        }
                    }
                    var wnd = Ext.getCmp('swVolRequestWindow');
                    var saveUrl = '';
                    var cell = o;

                    win.updateParams[cell.field+'_o'] = cell.originalValue;
                    if (cell.value != '' || cell.rawvalue != "")
                    {
                        win.updateParams[cell.field] = cell.value;
                    }
                    
                    if (cell.field == 'LpuLicence_Num')
                    {
                        win.updateParams.LpuLicence_id = Ext.getCmp('idObjGrid').getGrid().getSelectionModel().getSelected().data.LpuLicence_id;
                    }
                    else
                    {
                        delete win.updateParams.LpuLicence_id_o;
                    }

                    if (win.isCatInfo)
                    {
                        win.updateParams.SprInfo_id = cell.record.data.SprInfo_id;
                        saveUrl = '/?c=VolPeriods&m=saveSprInfo';
                    }
                    else
                    {
                        win.updateParams.RequestData_id = cell.record.data.RequestData_id;
                        saveUrl = '/?c=VolPeriods&m=saveRequestData';
                    }

                    Ext.Ajax.request({
                        failure: function(response, options) {
                            sw.swMsg.alert(lang['oshibka'], 'Возникли проблемы при сохранении записи');
                        },
                        params: win.updateParams,
                        success: function(response, options) 
                        {
                            
                        },
                        url: saveUrl
                    });
                    delete win.updateParams[cell.field];

                    if ((win.SprVidMp_id == 10) && (win.currentCategory != 1))
                    {
                        win.checkSmpTeamExists(Ext.getCmp('idObjGrid'), o.record, win.RequestList_id);
                    }
                    win.getInfo(win.RequestList_id);
                    win.getPlan(win.RequestList_id);
                    o.grid.stopEditing();
                    grid.getSelectionModel().clearSelections();
                    win.updateParams = {};
                },
                actions: 
                [
                    {name:'action_add', disabled: !isSuperAdmin(), handler: function() { this.openAttributeValueEditWindow('add', 'VolumeType'); }.createDelegate(this), hidden: true},
                    {name:'action_edit', disabled: !isSuperAdmin(), handler: function() { this.openAttributeValueEditWindow('edit', 'VolumeType'); }.createDelegate(this), hidden: true},
                    {name:'action_view', handler: function() { this.openAttributeValueEditWindow('view', 'VolumeType'); }.createDelegate(this), hidden: true},
                    {name:'action_delete', disabled: true, handler: function() { this.deleteAttributeValue('VolumeType'); }.createDelegate(this), hidden: true},
                    {name:'action_save', disabled: true,  hidden: true},
                    {name:'action_print', disabled: false,  hidden: false}
                ]
            }));
            
            grid = Ext.getCmp('idObjGrid');
            gridCm = grid.getGrid().getColumnModel();
            
            if (!win.isMZ && win.currentCategory !=1 && win.currentCategory !=43118)
            {
                gridCm.setEditable(gridCm.findColumnIndex('RequestData_AllowPlan'), false);
                
            }
            
            st = grid.getGrid().getStore();
            
            if (group)
            {
                st.groupField = 'SprPlanObj_Name';
                st.groupSortInfo = {field: 'RN', direction: "ASC"};
                //st.groupSortInfo = {field: 'SprPlanObj_Code', direction: "ASC"};
                grid.getGrid().view = new Ext.grid.GroupingView(
                {
                    enableGroupingMenu: false,
                    enableNoGroups: false,
                    showGroupName: false,
                    showGroupsText: true,
                    //groupByText: 'SprPlanObj_Name',
                    groupTextTpl: '{text} ({[values.rs.length]} {[values.rs.length > 1 ? (values.rs.length>4 ? "записей" : "записи") : "запись"]})',
                    //groupTextTpl: '{text}'
                });
                
                this.doLayout();
                grid.removeAll();
                grid.loadData({globalFilters: this.searchParams});
                grid.getGrid().getStore().sort('KsgCur', 'ASC');
            }
            else
            {
                this.doLayout();
                grid.removeAll();
                grid.loadData({globalFilters: this.searchParams});
                grid.getGrid().getStore().sort('SprPlanObj_Code', 'ASC');
            }
            if ((win.AgeTabPanel.getActiveTab().id == 'tab_all') & (this.currentCategory != 1)) 
            {
                var ColumnCount = Ext.getCmp('idObjGrid').getGrid().getColumnModel().getColumnCount(false);
                for (var i = 0; i < ColumnCount; i++)
                {
                    //так почему-то не работает. Работает только через getCmp()
                    //grid.getGrid().getColumnModel().setEditable(i,false);
                    Ext.getCmp('idObjGrid').getGrid().getColumnModel().setEditable(i,false);
                }  
            }
            win.checkKP(grid.getGrid());
            
            win.hideRouteFields();
        },

        doFilterVolumeTypeAttributeValueGrid: function () 
        {
            var win = this;
            var grid = win.VolumeTypeGrid;
            grid.loadData({globalFilters: win.searchParams});
	    },
        
        setRequestStatus: function(RequestList_id, Status_id)
        {
            var wnd = this;
            var msg = '';
            switch(Status_id)
            {
                case 3:
                    msg = 'Заявка сформирована'
                    break;
                case 4:
                    msg = 'Заявка утверждена'
                    break;
            }
            
            if (wnd.StatusName == 'Рассчитан КП')
            {
                Status_id = 8;
            }
            
            Ext.Ajax.request(
                {
                    url: '/?c=VolPeriods&m=setRequestStatus',
                    params: {
                        RequestList_id: RequestList_id,
                        SprRequestStatus_id: Status_id
                    },
                    callback: function(o, s, r) 
                    {
                        
                    },
                    success: function(response, options) 
                    {
                        if (msg != '')
                        {
                            wnd.getInfo(this.RequestList_id);
                            
                            sw.swMsg.show(
                            {
                                buttons: Ext.Msg.OK,
                                icon: Ext.Msg.INFO,
                                msg: msg,
                                title: 'Информация'
                            });
                        }
                    }
                }
            );
        },
        
        doControl: function(RequestList_id, param /* 3 сформировать, 4 утвердить*/)
        {
            var wnd = this;
            Ext.Ajax.request({
                failure:function () {
                    sw.swMsg.alert(lang['oshibka'], lang['ne_udalos_poluchit_dannyie_s_servera']);
                },
                params:{
                    RequestList_id: RequestList_id
                },
                success: function (response) {
                    var result = Ext.util.JSON.decode(response.responseText);
                    
                    if (result[0].rslt != '')
                    {
                        var params = {};
                        params.text = result[0].rslt;
                        getWnd('swVolWarningWnd').show(params);
                    }
                    else
                    {
                        wnd.setRequestStatus(wnd.RequestList_id, param);
                        Ext.getCmp('idVolRequestsGrid2').getGrid().getStore().reload();
//                        sw.swMsg.show(
//                        {
//                            buttons: Ext.Msg.OK,
//                            icon: Ext.Msg.INFO,
//                            msg: 'Заявка утверждена',
//                            title: 'Информация'
//                        });
                    }
                },
                url:'/?c=VolPeriods&m=doControl'
            });
        },
        
        getInfo: function(RequestList_id)
        {
            var wnd = this;
            Ext.Ajax.request({
                failure:function () {
                    sw.swMsg.alert(lang['oshibka'], lang['ne_udalos_poluchit_dannyie_s_servera']);
                },
                params:{
                    RequestList_id: wnd.RequestList_id
                },
                success: function (response) {
                    var result = Ext.util.JSON.decode(response.responseText);
                    var red = "red";
                    var blue = "blue";
                    
                    
                    Ext.getCmp('labelMoValue').setText(result[0].Lpu_Nick);
                    Ext.getCmp('labelMoValue').getEl().setStyle(
                        {
                            "font-size": "12px",
                            "font-weight":"bolder",
                            "margin": "2px 2px 2px 2px",
                            "color": blue
                        }
                    );
                    Ext.getCmp('LevelMoValue').setText(result[0].LevelType_Code);
                    Ext.getCmp('LevelMoValue').getEl().setStyle(
                        {
                            "font-size": "12px",
                            "font-weight":"bolder",
                            "margin": "2px 2px 2px 2px",
                            "color": blue
                        }
                    );
                    Ext.getCmp('labelPacCountValue').setText(result[0].PacCount);
                    Ext.getCmp('labelPacCountValue').getEl().setStyle(
                        {
                            "font-size": "12px",
                            "font-weight":"bolder",
                            "margin": "2px 2px 2px 2px",
                            "color": blue
                        }
                    );
                    Ext.getCmp('labelPlanYearValue').setText(result[0].Request_Year);
                    Ext.getCmp('labelPlanYearValue').getEl().setStyle(
                        {
                            "font-size": "12px",
                            "font-weight":"bolder",
                            "margin": "2px 2px 2px 2px",
                            "color": blue
                        }
                    );
                    Ext.getCmp('labelRequestStatusValue').setText(result[0].SprRequestStatus_Name);
                    Ext.getCmp('labelRequestStatusValue').getEl().setStyle(
                        {
                            "font-size": "12px",
                            "font-weight":"bolder",
                            "margin": "2px 2px 2px 2px",
                            "color": blue
                        }
                    );
            
                    if ((result[0].SprRequestStatus_Name != 'В работе') && (result[0].SprRequestStatus_Name != 'Отклонена'))
                    {
                        Ext.getCmp('btnFormirovat').hide();
                    }
                    else
                    {
                        Ext.getCmp('btnFormirovat').show();
                    }
                    
                    Ext.getCmp('labelKfLimitValue').setText(result[0].KfLimit);
                    Ext.getCmp('labelKfLimitValue').getEl().setStyle(
                        {
                            "font-size": "12px",
                            "font-weight":"bolder",
                            "margin": "2px 2px 2px 2px",
                            "color": blue
                        }
                    );
                    Ext.getCmp('labelKfPlanValue').setText(result[0].MesTariffMaxPlan_Value);
                    Ext.getCmp('labelKfPlanValue').getEl().setStyle(
                        {
                            "font-size": "12px",
                            "font-weight":"bolder",
                            "margin": "2px 2px 2px 2px",
                            "color": blue
                        }
                    );
                    Ext.getCmp('labelKfDeviationValue').setText(result[0].IsBadValue);
                    Ext.getCmp('labelKfDeviationValue').getEl().setStyle(
                        {
                            "font-size": "12px",
                            "font-weight":"bolder",
                            "margin": "2px 2px 2px 2px",
                            "color": red
                        }
                    );
                    Ext.getCmp('labelVolPlanValue').setText(result[0].Volume);
                    Ext.getCmp('labelVolPlanValue').getEl().setStyle(
                        {
                            "font-size": "12px",
                            "font-weight":"bolder",
                            "margin": "2px 2px 2px 2px",
                            "color": blue
                        }
                    );
                    Ext.getCmp('labelVolPlanOldValue').setText(result[0].VolumeOld);
                    Ext.getCmp('labelVolPlanOldValue').getEl().setStyle(
                        {
                            "font-size": "12px",
                            "font-weight":"bolder",
                            "margin": "2px 2px 2px 2px",
                            "color": blue
                        }
                    );
                    Ext.getCmp('labelVolPlanYoungValue').setText(result[0].VolumeYoung);
                    Ext.getCmp('labelVolPlanYoungValue').getEl().setStyle(
                        {
                            "font-size": "12px",
                            "font-weight":"bolder",
                            "margin": "2px 2px 2px 2px",
                            "color": blue
                        }
                    );
                    Ext.getCmp('labelDeviationVolValue').setText(result[0].IsBadVolume);
                    Ext.getCmp('labelDeviationVolValue').getEl().setStyle(
                        {
                            "font-size": "12px",
                            "font-weight":"bolder",
                            "margin": "2px 2px 2px 2px",
                            "color": red
                        }
                    );
                    Ext.getCmp('labelKPValue').setText(result[0].Kp);
                    Ext.getCmp('labelKPValue').getEl().setStyle(
                        {
                            "font-size": "12px",
                            "font-weight":"bolder",
                            "margin": "2px 2px 2px 2px",
                            "color": blue
                        }
                    );
                    Ext.getCmp('labelKpAdultsValue').setText(result[0].KpAdults);
                    Ext.getCmp('labelKpAdultsValue').getEl().setStyle(
                        {
                            "font-size": "12px",
                            "font-weight":"bolder",
                            "margin": "2px 2px 2px 2px",
                            "color": blue
                        }
                    );
                    Ext.getCmp('labelKpKidsValue').setText(result[0].KpKids);
                    Ext.getCmp('labelKpKidsValue').getEl().setStyle(
                        {
                            "font-size": "12px",
                            "font-weight":"bolder",
                            "margin": "2px 2px 2px 2px",
                            "color": blue
                        }
                    );
                    Ext.getCmp('labelDeviationVolKpValue').setText(result[0].IsBadVolKP);
                    Ext.getCmp('labelDeviationVolKpValue').getEl().setStyle(
                        {
                            "font-size": "12px",
                            "font-weight":"bolder",
                            "margin": "2px 2px 2px 2px",
                            "color": red
                        }
                    );
                    Ext.getCmp('labelVolKpValue').setText(result[0].VolumeKP);
                    Ext.getCmp('labelVolKpValue').getEl().setStyle(
                        {
                            "font-size": "12px",
                            "font-weight":"bolder",
                            "margin": "2px 2px 2px 2px",
                            "color": blue
                        }
                    );
                    Ext.getCmp('labelVolKpOldValue').setText(result[0].VolumeKPOld);
                    Ext.getCmp('labelVolKpOldValue').getEl().setStyle(
                        {
                            "font-size": "12px",
                            "font-weight":"bolder",
                            "margin": "2px 2px 2px 2px",
                            "color": blue
                        }
                    );
                    Ext.getCmp('labelVolKpYoungValue').setText(result[0].VolumeKPYoung);
                    Ext.getCmp('labelVolKpYoungValue').getEl().setStyle(
                        {
                            "font-size": "12px",
                            "font-weight":"bolder",
                            "margin": "2px 2px 2px 2px",
                            "color": blue
                        }
                    );
                    Ext.getCmp('labelDispNabKPValue').setText(result[0].DispNabKP);
                    Ext.getCmp('labelDispNabKPValue').getEl().setStyle(
                        {
                            "font-size": "12px",
                            "font-weight": "bolder",
                            "margin": "2px 2px 2px 2px",
                            "color": blue
                        }
                    );
                    Ext.getCmp('labelDispNabKPDevValue').setText(result[0].IsBadVolKPDispNab);
                    Ext.getCmp('labelDispNabKPDevValue').getEl().setStyle(
                        {
                            "font-size": "12px",
                            "font-weight": "bolder",
                            "margin": "2px 2px 2px 2px",
                            "color": result[0].IsBadVolKPDispNab > 0 ? blue : red
                        }
                    );
                    Ext.getCmp('labelRazObrCountKPValue').setText(result[0].RazObrCountKP);
                    Ext.getCmp('labelRazObrCountKPValue').getEl().setStyle(
                        {
                            "font-size": "12px",
                            "font-weight": "bolder",
                            "margin": "2px 2px 2px 2px",
                            "color": blue
                        }
                    );
                    Ext.getCmp('labelRazObrCountKPDevValue').setText(result[0].IsBadVolKPRazObrCount);
                    Ext.getCmp('labelRazObrCountKPDevValue').getEl().setStyle(
                        {
                            "font-size": "12px",
                            "font-weight": "bolder",
                            "margin": "2px 2px 2px 2px",
                            "color": result[0].IsBadVolKPRazObrCount > 0 ? blue : red
                        }
                    );
                    Ext.getCmp('labelMidMedStaffKPValue').setText(result[0].MidMedStaffKP);
                    Ext.getCmp('labelMidMedStaffKPValue').getEl().setStyle(
                        {
                            "font-size": "12px",
                            "font-weight": "bolder",
                            "margin": "2px 2px 2px 2px",
                            "color": blue
                        }
                    );
                    Ext.getCmp('labelMidMedStaffKPDevValue').setText(result[0].IsBadVolKPMidMedStaff);
                    Ext.getCmp('labelMidMedStaffKPDevValue').getEl().setStyle(
                        {
                            "font-size": "12px",
                            "font-weight": "bolder",
                            "margin": "2px 2px 2px 2px",
                            "color": result[0].IsBadVolKPMidMedStaff > 0 ? blue : red
                        }
                    );
                    Ext.getCmp('labelOtherPurpKPValue').setText(result[0].OtherPurpKP);
                    Ext.getCmp('labelOtherPurpKPValue').getEl().setStyle(
                        {
                            "font-size": "12px",
                            "font-weight": "bolder",
                            "margin": "2px 2px 2px 2px",
                            "color": blue
                        }
                    );
                    Ext.getCmp('labelOtherPurpKPDevValue').setText(result[0].IsBadVolKPOtherPurp);
                    Ext.getCmp('labelOtherPurpKPDevValue').getEl().setStyle(
                        {
                            "font-size": "12px",
                            "font-weight": "bolder",
                            "margin": "2px 2px 2px 2px",
                            "color": result[0].IsBadVolKPOtherPurp > 0 ? blue : red
                        }
                    );
                },
                url:'/?c=VolPeriods&m=loadRequestInfo'
            });
        },
        
        getPlan: function(RequestList_id)
        {
            var wnd = this;
            Ext.Ajax.request({
                failure:function () {
                    sw.swMsg.alert(lang['oshibka'], lang['ne_udalos_poluchit_dannyie_s_servera']);
                },
                params:{
                    RequestList_id: RequestList_id
                },
                success: function (response) {
                    var result = Ext.util.JSON.decode(response.responseText);
                    
                    if (!result[0])
                    { 
                        return false
                    }
                    var color = "black";
                    
                },
                url:'/?c=VolPeriods&m=getPlanByMo'
            });
        },
        
        checkSmpTeamExists: function(grid, record, RequestList_id) 
        {
            var ret;
            var win = this;
            Ext.Ajax.request({
                failure:function () {
                    sw.swMsg.alert(lang['oshibka'], lang['ne_udalos_poluchit_dannyie_s_servera']);
                },
                params:{
                    RequestList_id: RequestList_id,
                    SprPlanObj_Code: record.data.SprPlanObj_Code
                },
                success: function (response) {
                    var result = Ext.util.JSON.decode(response.responseText);
                    ret = result[0].rslt;
                    if (ret == 0 && win.SprVidMp_id == 10 && win.currentCategory != 1)
                    {
                        sw.swMsg.alert('Ошибка', 'Не указано количество бригад<br><br>Для ввода плана укажите значение<br>количества бригад в категории "Справочная информация"');
                        grid.getColumnModel().setEditable(grid.getColumnModel().findColumnIndex('RequestData_Plan'), false);
                    }
                    else
                    {
                        
                        Ext.getCmp('idObjGridGrid').getColumnModel().setEditable(Ext.getCmp('idObjGridGrid').getColumnModel().findColumnIndex('RequestData_Plan'), false);
                    }
                },
                url:'/?c=VolPeriods&m=checkSmpTeamExists'
            });
        },
        
        checkAllowPlan: function(grid, record, smpnoteam) 
        {
            var wnd = this;
            var allowPlan = record.get('RequestData_AllowPlan');
            this.setColEditable(grid, allowPlan);
        },
        
        makeSearchParams: function() 
        {
            this.searchParams = {};
            this.searchParams.Request_id = this.VolRequest_id;
            this.searchParams.Lpu_id = this.Lpu_id;
            this.searchParams.VidMp_id = this.SprVidMp_id;
        },
        
        openAttributeValueEditWindow: function(action, mode) 
        {
            var attrviewframe, mainviewframe, object;

            switch ( mode ) 
            {
                case 'TariffClass':
                    attrviewframe = this.TariffClassAttributeValueGrid;
                    mainviewframe = this.TariffClassGrid;
                    object = 'TariffClass';
                    break;

                case 'VolumeType':
                    attrviewframe = this.VolumeTypeAttributeValueGrid;
                    mainviewframe = this.VolumeTypeGrid;
                    object = 'VolumeType';
                    break;

                default:
                    return false;
                    break;
            }

            var grid = attrviewframe.getGrid();

            var params = new Object(), record;
            params.action = action;

            if (action != 'add') 
            {
                record = grid.getSelectionModel().getSelected();
                if (!record.get('AttributeValue_id')) { return false; }
                params.AttributeValue_id = record.get('AttributeValue_id');
            }

            params.callback = function()
            {
                attrviewframe.getAction('action_refresh').execute();
            }.createDelegate(this);

            record = mainviewframe.getGrid().getSelectionModel().getSelected();
            if (!record.get(object + '_id')) 
            {
                return false;
            }

            params.AttributeVision_TableName = 'dbo.' + object;
            params.AttributeVision_TablePKey = record.get(object + '_id');

            getWnd('swAttributeValueEditWindow').show(params);
	},
        
	initComponent: function() {
            var win = this;
            
            this.InfoPanel = new Ext.Panel(
            {
                xtype: 'form',
                title: 'Информационная панель',
                collapsible: true,
                autoScroll: true,
                bodyBorder: false,
                id: 'idInfoPanelCat',
                bodyStyle: 'padding: 0',
                border: true,
                frame: true,
                layout: 'form',
                autoHeight: true,
                labelAlign: 'right',
                listeners: 
                {
                    'render': function(panel) 
                    {
                        if (panel.header)
                        {
                            panel.header.on({
                                'click': {
                                    fn: this.toggleCollapse,
                                    scope: panel
                                }
                            });
                        }
                    },
                    'collapse': function(panel) 
                    {
                        win.doLayout();                        
                    },
                    'expand': function(panel) 
                    {
                        win.doLayout();                        
                    }
                },
                items: [
                    {
                        xtype: 'panel',
                        id: 'row1',
                        name: 'mo',
                        style: 'font-size: 12px;font-weight:normal;margin: 2px 2px 2px 2px;',
                        items: [
                            { 
                                xtype: 'label', 
                                text: 'Медицинская организация: ',
                                name: 'labelmo', 
                                id: 'labelMo',
                                style: 'font-size: 12px;font-weight:normal;margin: 2px 2px 2px 2px;'
                            },
                            {
                                fieldLabel: 'Год планирования',
                                id: 'idKostilCat1',
                                name: 'plan_year',
                                width: 40,
                                xtype: 'textfield',
                                plugins: [new Ext.ux.InputTextMask('9999',false)],
                                value: (new Date().getFullYear() + 1),
                                hidden: true,
                                hideLabel: true
                            },
                            { 
                                xtype: 'label', 
                                text: '', 
                                name: 'lpu_name', 
                                id: 'labelMoValue', 
                                style: 'font-size: 12px;font-weight:bolder;margin: 2px 2px 2px 2px;'
                            },
                            {
                                fieldLabel: 'Год планирования',
                                id: 'idKostilCat',
                                name: 'plan_year',
                                width: 40,
                                xtype: 'textfield',
                                plugins: [new Ext.ux.InputTextMask('9999',false)],
                                value: (new Date().getFullYear() + 1),
                                hidden: true,
                                hideLabel: true
                            },
                            { 
                                xtype: 'label', 
                                text: 'Уровень МО: ', 
                                name: 'lpu_name', 
                                id: 'LevelMo', 
                                style: 'font-size: 12px;font-weight:normal;margin: 2px 2px 2px 10px;'
                            },
                            {
                                fieldLabel: 'Год планирования',
                                id: 'idKostilCat',
                                name: 'plan_year',
                                width: 40,
                                xtype: 'textfield',
                                plugins: [new Ext.ux.InputTextMask('9999',false)],
                                value: (new Date().getFullYear() + 1),
                                hidden: true,
                                hideLabel: true
                            },
                            { 
                                xtype: 'label', 
                                text: '', 
                                name: 'lpu_name', 
                                id: 'LevelMoValue', 
                                style: 'font-size: 12px;font-weight:bolder;margin: 2px 2px 2px 2px;'
                            }
                        ] 
                    },
                    {
                        xtype: 'panel',
                        id: 'row2',
                        name: 'paccount',
                        style: 'font-size: 12px;font-weight:normal;margin: 2px 2px 2px 2px;',
                        items: [
                            { 
                                xtype: 'label', 
                                text: 'Численность застрахованных лиц, прикрепленных к МО, по состоянию на 01.04:',
                                name: 'labelmo', 
                                id: 'labelPacCount', 
                                style: 'font-size: 12px;font-weight:normal;margin: 2px 2px 2px 2px;'
                            },
                            {
                                fieldLabel: 'Год планирования',
                                id: 'idKostilCat1',
                                name: 'plan_year',
                                width: 40,
                                xtype: 'textfield',
                                plugins: [new Ext.ux.InputTextMask('9999',false)],
                                value: (new Date().getFullYear() + 1),
                                hidden: true,
                                hideLabel: true
                            },
                            { 
                                xtype: 'label', 
                                text: '', 
                                name: 'lpu_name', 
                                id: 'labelPacCountValue', 
                                style: 'font-size: 12px;font-weight:bolder;margin: 2px 2px 2px 2px;'
                            }
                        ] 
                    },
                    {
                        xtype: 'panel',
                        id: 'row3',
                        name: 'paccount',
                        style: 'font-size: 12px;font-weight:bolder;margin: 2px 2px 2px 2px;',
                        items: [
                            { 
                                xtype: 'label', 
                                text: 'Год планирования:',
                                name: 'labelmo', 
                                id: 'labelPlanYear', 
                                style: 'font-size: 12px;font-weight:normal;margin: 2px 2px 2px 2px;'
                            },
                            { 
                                xtype: 'label', 
                                text: '', 
                                name: 'lpu_name', 
                                id: 'labelPlanYearValue', 
                                style: 'font-size: 12px;font-weight:bolder;margin: 2px 2px 2px 2px;'
                            },
                            { 
                                xtype: 'label', 
                                text: 'Статус заявки: ',
                                name: 'labelmo', 
                                id: 'labelRequestStatus', 
                                style: 'font-size: 12px;font-weight:normal;margin: 2px 2px 2px 10px;'
                            },
                            { 
                                xtype: 'label', 
                                text: '', 
                                name: 'lpu_name', 
                                id: 'labelRequestStatusValue', 
                                style: 'font-size: 12px;font-weight:bolder;margin: 2px 2px 2px 2px;'
                            },
                            {
                                xtype: 'label',
                                text: 'КП для проведения диспансерного наблюдения: ',
                                name: 'labelmo',
                                id: 'labelDispNabKP',
                                style: 'font-size: 12px;font-weight:normal;margin: 2px 2px 2px 187px;'
                            },
                            {
                                xtype: 'label',
                                text: '',
                                name: 'labelmo',
                                id: 'labelDispNabKPValue',
                                style: 'font-size: 12px;font-weight:normal;margin: 2px 2px 2px 2px;'
                            },
                            {
                                xtype: 'label',
                                text: 'Отклонение от КП: ',
                                name: 'labelmo',
                                id: 'labelDispNabKPDev',
                                style: 'font-size: 12px;font-weight:normal;margin: 2px 2px 2px 10px;'
                            },
                            {
                                xtype: 'label',
                                text: '',
                                name: 'labelmo',
                                id: 'labelDispNabKPDevValue',
                                style: 'font-size: 12px;font-weight:normal;margin: 2px 2px 2px 2px;'
                            }
                        ] 
                    },
                    {
                        xtype: 'panel',
                        id: 'row4',
                        name: 'paccount',
                        style: 'font-size: 12px;font-weight:bolder;margin: 2px 2px 2px 2px;',
                        items: [
                            { 
                                xtype: 'label', 
                                text: 'Предельный КЗ: ',
                                name: 'labelmo', 
                                id: 'labelKfLimit', 
                                style: 'font-size: 12px;font-weight:normal;margin: 2px 2px 2px 2px;'
                            },
                            {
                                fieldLabel: 'костыль',
                                id: 'idKostilCat1',
                                name: 'plan_year',
                                width: 40,
                                xtype: 'textfield',
                                plugins: [new Ext.ux.InputTextMask('9999',false)],
                                value: (new Date().getFullYear() + 1),
                                hidden: true,
                                hideLabel: true
                            },
                            { 
                                xtype: 'label', 
                                text: '', 
                                name: 'lpu_name', 
                                id: 'labelKfLimitValue', 
                                style: 'font-size: 12px;font-weight:bolder;margin: 2px 2px 2px 2px;'
                            },
                            {
                                fieldLabel: 'костыль',
                                id: 'idKostilCat1',
                                name: 'plan_year',
                                width: 40,
                                xtype: 'textfield',
                                plugins: [new Ext.ux.InputTextMask('9999',false)],
                                value: (new Date().getFullYear() + 1),
                                hidden: true,
                                hideLabel: true
                            },
                            { 
                                xtype: 'label', 
                                text: 'Планируемый КЗ: ',
                                name: 'labelmo', 
                                id: 'labelKfPlan', 
                                style: 'font-size: 12px;font-weight:normal;margin: 2px 2px 2px 10px;'
                            },
                            {
                                fieldLabel: 'костыль',
                                id: 'idKostilCat1',
                                name: 'plan_year',
                                width: 40,
                                xtype: 'textfield',
                                plugins: [new Ext.ux.InputTextMask('9999',false)],
                                value: (new Date().getFullYear() + 1),
                                hidden: true,
                                hideLabel: true
                            },
                            { 
                                xtype: 'label', 
                                text: '', 
                                name: 'lpu_name', 
                                id: 'labelKfPlanValue', 
                                style: 'font-size: 12px;font-weight:bolder;margin: 2px 2px 2px 2px;'
                            },
                            {
                                fieldLabel: 'костыль',
                                id: 'idKostilCat1',
                                name: 'plan_year',
                                width: 40,
                                xtype: 'textfield',
                                plugins: [new Ext.ux.InputTextMask('9999',false)],
                                value: (new Date().getFullYear() + 1),
                                hidden: true,
                                hideLabel: true
                            },
                            { 
                                xtype: 'label', 
                                text: 'Отклонение: ',
                                name: 'labelmo', 
                                id: 'labelKfDeviation', 
                                style: 'font-size: 12px;font-weight:normal;margin: 2px 2px 2px 10px;'
                            },
                            {
                                fieldLabel: 'Год планирования',
                                id: 'idKostilCat1',
                                name: 'plan_year',
                                width: 40,
                                xtype: 'textfield',
                                plugins: [new Ext.ux.InputTextMask('9999',false)],
                                value: (new Date().getFullYear() + 1),
                                hidden: true,
                                hideLabel: true
                            },
                            { 
                                xtype: 'label', 
                                text: '', 
                                name: 'lpu_name', 
                                id: 'labelKfDeviationValue', 
                                style: 'font-size: 12px;font-weight:bolder;margin: 2px 2px 2px 2px;'
                            }
                        ] 
                    },
                    {
                        xtype: 'panel',
                        id: 'row5',
                        name: 'paccount',
                        style: 'font-size: 12px;font-weight:bolder;margin: 2px 2px 2px 2px;',
                        items: [
                            { 
                                xtype: 'label', 
                                text: 'Планируемый объем: ',
                                name: 'VolPlan',
                                id: 'labelVolPlan', 
                                style: 'font-size: 12px;font-weight:normal;margin: 2px 2px 2px 2px;'
                            },
                            { 
                                xtype: 'label', 
                                text: '', 
                                name: 'VolPlanValue',
                                id: 'labelVolPlanValue', 
                                style: 'font-size: 12px;font-weight:bolder;margin: 2px 2px 2px 2px;'
                            },
                            { 
                                xtype: 'label', 
                                text: 'В т.ч. Взрослые: ',
                                name: 'VolPlanOld',
                                id: 'labelVolPlanOld', 
                                style: 'font-size: 12px;font-weight:normal;margin: 2px 2px 2px 10px;'
                            },
                            { 
                                xtype: 'label', 
                                text: '', 
                                name: 'VolPlanOldValue',
                                id: 'labelVolPlanOldValue', 
                                style: 'font-size: 12px;font-weight:bolder;margin: 2px 2px 2px 2px;'
                            },
                            { 
                                xtype: 'label', 
                                text: 'Дети: ',
                                name: 'VolPlanYoung',
                                id: 'labelVolPlanYoung', 
                                style: 'font-size: 12px;font-weight:normal;margin: 2px 2px 2px 10px;'
                            },
                            { 
                                xtype: 'label', 
                                text: '', 
                                name: 'VolPlanYoungValue',
                                id: 'labelVolPlanYoungValue', 
                                style: 'font-size: 12px;font-weight:bolder;margin: 2px 2px 2px 2px;'
                            },
                            { 
                                xtype: 'label', 
                                text: 'Отклонение: ',
                                name: 'DeviationVol',
                                id: 'labelDeviationVol', 
                                style: 'font-size: 12px;font-weight:normal;margin: 2px 2px 2px 10px;'
                            },
                            { 
                                xtype: 'label', 
                                text: '', 
                                name: 'DeviationVolValue',
                                id: 'labelDeviationVolValue', 
                                style: 'font-size: 12px;font-weight:bolder;margin: 2px 2px 2px 2px;'
                            },
                            {
                                xtype: 'label',
                                text: 'КП для разовых посещений в связи с заболеваниями: ',
                                name: 'RazObrCountKP',
                                id: 'labelRazObrCountKP',
                                style: 'font-size: 12px;font-weight:normal;margin: 2px 2px 2px 85px;'
                            },
                            {
                                xtype: 'label',
                                text: '',
                                name: 'RazObrCountKPValue',
                                id: 'labelRazObrCountKPValue',
                                style: 'font-size: 12px;font-weight:bolder;margin: 2px 2px 2px 2px;'
                            },
                            {
                                xtype: 'label',
                                text: 'Отклонение от КП: ',
                                name: 'RazObrCountKPDev',
                                id: 'labelRazObrCountKPDev',
                                style: 'font-size: 12px;font-weight:normal;margin: 2px 2px 2px 10px;'
                            },
                            {
                                xtype: 'label',
                                text: '',
                                name: 'RazObrCountKPDevValue',
                                id: 'labelRazObrCountKPDevValue',
                                style: 'font-size: 12px;font-weight:bolder;margin: 2px 2px 2px 2px;'
                            }
                        ] 
                    },
                    {
                        xtype: 'panel',
                        id: 'row6',
                        name: 'paccount',
                        style: 'font-size: 12px;font-weight:bolder;margin: 2px 2px 2px 2px;',
                        items: [
                            { 
                                xtype: 'label', 
                                text: 'Контрольный показатель: ',
                                name: 'labelmo', 
                                id: 'labelKP', 
                                style: 'font-size: 12px;font-weight:normal;margin: 2px 2px 2px 2px;'
                            },
                            { 
                                xtype: 'label', 
                                text: '', 
                                name: 'lpu_name', 
                                id: 'labelKPValue', 
                                style: 'font-size: 12px;font-weight:bolder;margin: 2px 2px 2px 5px;'
                            },
                            {
                                fieldLabel: 'Год планирования',
                                id: 'idKostilCat1',
                                name: 'plan_year',
                                width: 40,
                                xtype: 'textfield',
                                plugins: [new Ext.ux.InputTextMask('9999',false)],
                                value: (new Date().getFullYear() + 1),
                                hidden: true,
                                hideLabel: true,
                                style: 'font-size: 12px;font-weight:bolder;margin: 2px 2px 2px 5px;'
                            },
                            { 
                                xtype: 'label', 
                                text: 'в т.ч. взрослые: ',
                                name: 'labelmo', 
                                id: 'labelKPAdults', 
                                style: 'font-size: 12px;font-weight:normal;margin: 2px 2px 2px 10px;'
                            },
                            {
                                fieldLabel: 'Год планирования',
                                id: 'idKostilCat1',
                                name: 'plan_year',
                                width: 40,
                                xtype: 'textfield',
                                plugins: [new Ext.ux.InputTextMask('9999',false)],
                                value: (new Date().getFullYear() + 1),
                                hidden: true,
                                hideLabel: true,
                                style: 'font-size: 12px;font-weight:normal;margin: 2px 2px 2px 10px;'
                            },
                            { 
                                xtype: 'label', 
                                text: '', 
                                name: 'lpu_name', 
                                id: 'labelKpAdultsValue', 
                                style: 'font-size: 12px;font-weight:bolder;margin: 2px 2px 2px 5px;'
                            },
                            {
                                fieldLabel: 'Год планирования',
                                id: 'idKostilCat1',
                                name: 'plan_year',
                                width: 40,
                                xtype: 'textfield',
                                plugins: [new Ext.ux.InputTextMask('9999',false)],
                                value: (new Date().getFullYear() + 1),
                                hidden: true,
                                hideLabel: true,
                                style: 'font-size: 12px;font-weight:bolder;margin: 2px 2px 2px 5px;'
                            },
                            { 
                                xtype: 'label', 
                                text: 'в т.ч. дети: ',
                                name: 'labelmo', 
                                id: 'labelKPKids', 
                                style: 'font-size: 12px;font-weight:normal;margin: 2px 2px 2px 10px;'
                            },
                            {
                                fieldLabel: 'Год планирования',
                                id: 'idKostilCat1',
                                name: 'plan_year',
                                width: 40,
                                xtype: 'textfield',
                                plugins: [new Ext.ux.InputTextMask('9999',false)],
                                value: (new Date().getFullYear() + 1),
                                hidden: true,
                                hideLabel: true,
                                style: 'font-size: 12px;font-weight:normal;margin: 2px 2px 2px 10px;'
                            },
                            { 
                                xtype: 'label', 
                                text: '', 
                                name: 'lpu_name', 
                                id: 'labelKpKidsValue', 
                                style: 'font-size: 12px;font-weight:bolder;margin: 2px 2px 2px 5px;'
                            },
                            {
                                fieldLabel: 'Год планирования',
                                id: 'idKostilCat1',
                                name: 'plan_year',
                                width: 40,
                                xtype: 'textfield',
                                plugins: [new Ext.ux.InputTextMask('9999',false)],
                                value: (new Date().getFullYear() + 1),
                                hidden: true,
                                hideLabel: true,
                                style: 'font-size: 12px;font-weight:bolder;margin: 2px 2px 2px 5px;'
                            },
                            { 
                                xtype: 'label', 
                                text: 'Отклонение от КП: ',
                                name: 'labelmo', 
                                id: 'labelDeviationVolKp', 
                                style: 'font-size: 12px;font-weight:normal;margin: 2px 2px 2px 10px;'
                            },
                            { 
                                xtype: 'label', 
                                text: '', 
                                name: 'lpu_name', 
                                id: 'labelDeviationVolKpValue', 
                                style: 'font-size: 12px;font-weight:bolder;margin: 2px 2px 2px 5px;'
                            },
                            {
                                xtype: 'label',
                                text: 'КП для посещений среднего МП: ',
                                name: 'labelmo',
                                id: 'labelMidMedStaffKP',
                                style: 'font-size: 12px;font-weight:normal;margin: 2px 2px 2px 10px;'
                            },
                            {
                                xtype: 'label',
                                text: '',
                                name: 'lpu_name',
                                id: 'labelMidMedStaffKPValue',
                                style: 'font-size: 12px;font-weight:bolder;margin: 2px 2px 2px 2px;'
                            },
                            {
                                xtype: 'label',
                                text: 'Отклонение от КП: ',
                                name: 'labelmo',
                                id: 'labelMidMedStaffKPDev',
                                style: 'font-size: 12px;font-weight:normal;margin: 2px 2px 2px 120px;'
                            },
                            {
                                xtype: 'label',
                                text: '',
                                name: 'lpu_name',
                                id: 'labelMidMedStaffKPDevValue',
                                style: 'font-size: 12px;font-weight:bolder;margin: 2px 2px 2px 2px;'
                            }
                        ] 
                    },
                    {
                        xtype: 'panel',
                        id: 'row7',
                        name: 'paccount',
                        style: 'font-size: 12px;font-weight:bolder;margin: 2px 2px 2px 2px;',
                        items: [
                            { 
                                xtype: 'label', 
                                text: 'Планируемый объем по КП: ',
                                name: 'labelmo', 
                                id: 'labelVolKp', 
                                style: 'font-size: 12px;font-weight:normal;margin: 2px 2px 2px 2px;'
                            },
                            { 
                                xtype: 'label', 
                                text: '', 
                                name: 'lpu_name', 
                                id: 'labelVolKpValue', 
                                style: 'font-size: 12px;font-weight:bolder;margin: 2px 2px 2px 2px;'
                            },
                            { 
                                xtype: 'label', 
                                text: 'В т.ч. взрослые: ',
                                name: 'labelmo', 
                                id: 'labelVolKpOld', 
                                style: 'font-size: 12px;font-weight:normal;margin: 2px 2px 2px 10px;'
                            },
                            { 
                                xtype: 'label', 
                                text: '', 
                                name: 'lpu_name', 
                                id: 'labelVolKpOldValue', 
                                style: 'font-size: 12px;font-weight:bolder;margin: 2px 2px 2px 2px;'
                            },
                            { 
                                xtype: 'label', 
                                text: 'В т.ч. дети: ',
                                name: 'labelmo', 
                                id: 'labelVolKpYoung', 
                                style: 'font-size: 12px;font-weight:normal;margin: 2px 2px 2px 2px;'
                            },
                            { 
                                xtype: 'label', 
                                text: '', 
                                name: 'lpu_name', 
                                id: 'labelVolKpYoungValue', 
                                style: 'font-size: 12px;font-weight:bolder;margin: 2px 2px 2px 2px;'
                            },
                            {
                                xtype: 'label',
                                text: 'КП для посещений с другими целями: ',
                                name: 'labelmo',
                                id: 'labelOtherPurpKP',
                                style: 'font-size: 12px;font-weight:normal;margin: 2px 2px 2px 120px;'
                            },
                            {
                                xtype: 'label',
                                text: '',
                                name: 'lpu_name',
                                id: 'labelOtherPurpKPValue',
                                style: 'font-size: 12px;font-weight:bolder;margin: 2px 2px 2px 2px;'
                            },
                            {
                                xtype: 'label',
                                text: 'Отклонение от КП: ',
                                name: 'labelmo',
                                id: 'labelOtherPurpKPDev',
                                style: 'font-size: 12px;font-weight:normal;margin: 2px 2px 2px 110px;'
                            },
                            {
                                xtype: 'label',
                                text: '',
                                name: 'lpu_name',
                                id: 'labelOtherPurpKPDevValue',
                                style: 'font-size: 12px;font-weight:bolder;margin: 2px 2px 2px 2px;'
                            }
                        ] 
                    }                    
                ]
            });
            
            this.VolumeTypeAttributeValueGridDynamicFilters = new sw.Promed.Panel(
            {
                border: false,
                labelWidth: 120,
                items: []
            });
            
            this.VolumeTypeGridFilters = new Ext.form.FormPanel(
            {
                xtype: 'form',
                region: 'north',
                //labelAlign: 'top',
                layout: 'form',
                autoHeight: true,
                labelWidth: 50,
                frame: true,
                border: false,
                keys:
                    [{
                        key: Ext.EventObject.ENTER,
                        fn: function(e)
                        {

                        },
                        stopEvent: true
                    }],
                items: [{
                        listeners: {
                            collapse: function (p) {
                                //win.recountGridHeight();
                                win.doLayout();
                            },
                            expand: function (p) {
                                //win.recountGridHeight();
                                win.doLayout();
                            }
                        },
                    xtype: 'fieldset',
                    style: 'margin: 0px 5px',
                    title: lang['filtryi'],
                    collapsible: true,
                    //collapsed: true,
                    autoHeight: true,
                    labelWidth: 100,
                    anchor: '-10',
                    layout: 'form',
                    items: 
                    [ 
                        {
                            
                            layout: 'column',
                            border: false,
                            labelAlign: 'left',
                            items: 
                            [
                                {
                                    xtype: 'panel',
                                    id: 'filter1row1',
                                    name: 'mo',
                                    style: 'font-size: 12px;font-weight:normal;margin: 2px 2px 2px 0px;',
                                    items: 
                                        [
                                            { 
                                                xtype: 'label', 
                                                text: 'Код: ',
                                                name: 'labelmo', 
                                                id: 'idFilter1CodeLabel',
                                                style: 'font-size: 12px;font-weight:normal;margin: 2px 2px 2px 0px;'
                                            },
                                            {
                                                xtype: 'numberfield',
                                                id: 'idFilter1Code',
                                                width : 250,
                                                fieldLabel: 'Код',
                                                style: 'font-size: 12px;font-weight:normal;margin: 2px 2px 2px 2px;'
                                            },
                                            { 
                                                xtype: 'label', 
                                                text: 'Наименование: ',
                                                name: 'labelmo', 
                                                id: 'idFilter1NameLabel',
                                                style: 'font-size: 12px;font-weight:normal;margin: 2px 2px 2px 20px;'
                                            },
                                            {
                                                xtype: 'textfield',
                                                id: 'idFilter1Name',
                                                width : 250,
                                                fieldLabel: 'Наименование',
                                                style: 'font-size: 12px;font-weight:normal;margin: 2px 2px 2px 2px;'
                                            },
                                            { 
                                                xtype: 'label', 
                                                text: 'Без объема: ',
                                                name: 'labelmo', 
                                                id: 'idFilter1NoVolLabel',
                                                style: 'font-size: 12px;font-weight:normal;margin: 2px 2px 2px 20px;'
                                            }
                                        ]
                                },
                                {
                                    xtype: 'panel',
                                    id: 'filter1row12',
                                    name: 'mo',
                                    layout: 'column',
                                    style: 'font-size: 12px;font-weight:normal;margin: 6px 2px 2px 2px;',
                                    items: [
                                            {
                                                xtype: 'checkbox',
                                                id: 'idCBNoVol',
                                                width: 118,
                                                name: 'NoVol',
                                                fieldLabel: 'Без объема'
                                            }]
                                }
                            ]
                        },
                        
                        {
                        border: false,
                        layout: 'column',
                        anchor: '-10',
                        items: [ {
                                    layout: 'form',
                                    border: false,
                                    items: [{
                                            text: BTN_FILTER,
                                            xtype: 'button',
                                            handler: function () {
                                                    win.doFilterCat();
                                            },
                                            iconCls: 'search16'
                                    }]
                            }, {
                                    layout: 'form',
                                    bodyStyle: 'padding-left: 5px;',
                                    border: false,
                                    items: [{
                                            text: BTN_RESETFILTER,
                                            xtype: 'button',
                                            handler: function () {
                                                    win.doResetFiltersVolumeTypeGrid();
                                                    win.doFilterCat();
                                            },
                                            iconCls: 'resetsearch16'
                                    }]
                            }]
                    }
                    ]
                }]
        });   
        
            this.VolumeTypeGrid = new sw.Promed.ViewFrame(
            {
                id: 'idCatPlanGrid',
                //collapsible: true,
                height: 200,
                dataUrl: '/?c=VolPeriods&m=loadCatList',
                //title: 'Категории планирования',
                uniqueId: true,
                border: false,
                totalProperty: 'totalCount',
                autoLoadData: false,
                object: 'CatPlanGrid',
                saveAtOnce: false,
                stringfields: 
                [
                    {name: 'SprPlanCat_id', type: 'int', header: 'ID', key: true},
                    {name: 'VolCode', header: lang['kod'], type: 'int', width: 120},
                    {name: 'SprPlanCat_Name', header: lang['naimenovanie'], type: 'string', autoexpand: true},
                    {name: 'fVolCount1', header: 'Объём ед. (План)', type: 'int', width: 120},
                    {name: 'fVolCount1Old', header: 'В т.ч. взрослые', type: 'int', width: 120},
                    {name: 'fVolCount1Young', header: 'В т.ч. дети', type: 'int', width: 120},
                    {name: 'fVolCount2', header: 'Объём ед. (План)', type: 'int', width: 120},
                    {name: 'fVolCount2Old', header: 'В т.ч. взрослые', type: 'int', width: 120},
                    {name: 'fVolCount2Young', header: 'В т.ч. дети', type: 'int', width: 120},
                    {name: 'fVolCount3', header: 'Объём ед. (План)', type: 'int', width: 120},
                    {name: 'fVolCount3Old', header: 'В т.ч. взрослые', type: 'int', width: 120},
                    {name: 'fVolCount3Young', header: 'В т.ч. дети', type: 'int', width: 120},
                    {name: 'VolCount', header: 'Объём ед. (План)', type: 'int', width: 120},
                    {name: 'VolCountOld', header: 'В т.ч. взрослые', type: 'int', width: 120},
                    {name: 'VolCountYoung', header: 'В т.ч. дети', type: 'int', width: 120},
                    {name: 'DispNab', header: 'В т.ч. для проведения <br>диспансерного <br>наблюдения', type: 'int', width: 120},
                    {name: 'DispNabAdults', header: 'В т.ч. взрослые', type: 'int', width: 120},
                    {name: 'DispNabKids', header: 'В т.ч. дети', type: 'int', width: 120},
                    {name: 'RazObrCount', header: 'В т.ч. разовые <br>посещения в связи <br>с заболеваниями', type: 'int', width: 120},
                    {name: 'RazObrCountAdults', header: 'В т.ч. взрослые', type: 'int', width: 120},
                    {name: 'RazObrCountKids', header: 'В т.ч. дети', type: 'int', width: 120},
                    {name: 'MedReab', header: 'В т.ч. посещения <br>по медицинской <br>реабилитации', type: 'int', width: 120},
                    {name: 'MedReabAdults', header: 'В т.ч. взрослые', type: 'int', width: 120},
                    {name: 'MedReabKids', header: 'В т.ч. дети', type: 'int', width: 120},
                    {name: 'OtherPurp', header: 'В т.ч. посещения <br>с другой <br>целью', type: 'int', width: 120},
                    {name: 'OtherPurpAdults', header: 'В т.ч. взрослые', type: 'int', width: 120},
                    {name: 'OtherPurpKids', header: 'В т.ч. дети', type: 'int', width: 120},
                    {name: 'PlanCatData_KP', header: 'КП', type: 'float', width: 120, css : win.css},
                    {name: 'PlanCatData_KpAdults', header: win.c_plnkp_a, type: 'float', width: 120, editor: new Ext.form.NumberField(), css : win.css},
                    {name: 'PlanCatData_KpKids', header: 'в т.ч. дети', type: 'float', width: 120, editor: new Ext.form.NumberField(), css : win.css},
                    {name: 'PlanCatData_KpEmer', header: 'КП травмпункт', type: 'float', width: 120, editor: new Ext.form.NumberField(), css : win.css}, // отображается только для неотложки
                    {name: 'PlanCatData_PlanKP', /*hidden: true,*/ header: 'План по КП', type: 'float', width: 120, css : win.css},
                    {name: 'PlanCatData_PlanKpAdults', /*hidden: true,*/ header: 'В т.ч. взрослые', type: 'float', width: 120, editor: new Ext.form.NumberField(), css : win.css},
                    {name: 'PlanCatData_PlanKpKids', /*hidden: true,*/ header: 'В т.ч. дети', type: 'float', width: 120, editor: new Ext.form.NumberField(), css : win.css},

                    {name: 'PlanCatData_DispNabKP', header: 'В т.ч. для <br>ДН по КП', type: 'float', width: 120},
                    {name: 'PlanCatData_DispNabKPAdults', header: 'В т.ч. взрослые', type: 'float', width: 120},
                    {name: 'PlanCatData_DispNabKPKids', header: 'В т.ч. дети', type: 'float', width: 120},
                    {name: 'PlanCatData_RazObrKP', header: 'В т.ч. разовые <br>по заболеванию <br>по КП', type: 'float', width: 120},
                    {name: 'PlanCatData_RazObrKPAdults', header: 'В т.ч. взрослые', type: 'float', width: 120},
                    {name: 'PlanCatData_RazObrKPKids', header: 'В т.ч. дети', type: 'float', width: 120},
                    {name: 'PlanCatData_MidMedStaffKP', header: 'В т.ч. средний <br>МП', type: 'float', width: 120},
                    {name: 'PlanCatData_MidMedStaffKPAdults', header: 'В т.ч. взрослые', type: 'float', width: 120},
                    {name: 'PlanCatData_MidMedStaffKPKids', header: 'В т.ч. дети', type: 'float', width: 120},
                    {name: 'PlanCatData_OtherPurpKP', header: 'В т.ч. с другими <br>целями по КП', type: 'float', width: 120},
                    {name: 'PlanCatData_OtherPurpKPAdults', header: 'В т.ч. взрослые', type: 'float', width: 120},
                    {name: 'PlanCatData_OtherPurpKPKids', header: 'В т.ч. дети', type: 'float', width: 120}
                ],
                listeners: 
                {
                    'render': function(panel)
                    {
                        if (panel.header)
                        {
                            panel.header.on({
                                'click': {
                                    fn: this.toggleCollapse,
                                    scope: panel
                                }
                            });
                        }
                    },
                    'collapse': function(panel) 
                    {
                        win.doLayout();                        
                    },
                    'expand': function(panel) 
                    {
                        win.doLayout();                        
                    }
                },
                onRowSelect: function(sm, index, record) {
                    var win = Ext.getCmp('swVolRequestWindow');
                    win.currentCategory = record.data.SprPlanCat_id;
                    win.selectCat(record);
                    if (!win.isMZ)
                    {
                        sm.grid.getColumnModel().setEditable(sm.grid.getColumnModel().findColumnIndex('PlanCatData_KP'), false);
                        sm.grid.getColumnModel().setEditable(sm.grid.getColumnModel().findColumnIndex('PlanCatData_KpAdults'), false);
                        sm.grid.getColumnModel().setEditable(sm.grid.getColumnModel().findColumnIndex('PlanCatData_KpKids'), false);
                        sm.grid.getColumnModel().setEditable(sm.grid.getColumnModel().findColumnIndex('PlanCatData_KpEmer'), false);
                    }
                    
                },
                onAfterEdit: function(o)
                {
                    var win = Ext.getCmp('swVolRequestWindow');
                    var grid = o.grid;
                    if (o.value < 0)
                    {
                        sw.swMsg.show(
                            {
                                buttons: Ext.Msg.OK,
                                icon: Ext.Msg.WARNING,
                                msg: 'Значение не может быть отрицательным',
                                title: 'Внимание!'
                            }
                        );
                        return;
                    }
                    var params = {};
                    params.RequestList_id = win.RequestList_id;
                    params.SprPlanCat_id = win.currentCategory;
                    
                    params[o.field] = o.value;
                    params[o.field + '_o'] = o.originalValue;
                    Ext.Ajax.request(
                    {
                        failure: function(response, options) 
                        {
                            sw.swMsg.alert(lang['oshibka'], 'Возникли проблемы при сохранении записи');
                        },
                        params: params,
                        success: function(response, options)
                        {
                            grid.getStore().reload();
                        },
                        url: '/?c=VolPeriods&m=savePlanCatData'
                    });
                },
                actions: [
                        {name:'action_add', hidden: true, disabled: true},
                        {name:'action_edit', hidden: true, disabled: true},
                        {name:'action_view', hidden: true, disabled: true},
                        {name:'action_delete', hidden: true, disabled: true},
                        {name:'action_print', disabled: false,  hidden: false}
                ]
            });
            
            this.CatPanel = new Ext.Panel(
            {
                xtype: 'form',
                title: 'Категории планирования',
                collapsible: true,
                autoScroll: true,
                bodyBorder: false,
                id: 'idCatPanel',
                //bodyStyle: 'padding: 0',
                border: true,
                frame: false,
                layout: 'form',
                //autoHeight: true,
                //style: 'margin-top: 0px',
                labelAlign: 'right',
                listeners: 
                {
                    'render': function(panel) 
                    {
                        if (panel.header)
                        {
                            panel.header.on({
                                'click': {
                                    fn: this.toggleCollapse,
                                    scope: panel
                                }
                            });
                        }
                    },
                    'collapse': function(panel) 
                    {
                        win.doLayout();                        
                    },
                    'expand': function(panel) 
                    {
                        win.doLayout();                        
                    }
                },
                items: [win.VolumeTypeGrid]
            });
                
            this.AgeTabPanel = new Ext.TabPanel(
            {
                activeTab: 0,
                layoutOnTabChange: true,
                id: 'idAgeTabPanel',
                items: [
                        {
                            id: 'tab_all',
                            layout: 'form',
                            autoScroll: true,
                            title: 'Общее',
                            items: []
                        },
                        {   
                            id: 'tab_adults',
                            layout: 'form',
                            autoScroll: true,
                            title: 'Взрослые',
                            items: []
                        },
                        {
                            id: 'tab_kids',
                            layout: 'form',
                            autoScroll: true,
                            title: 'Дети',
                            items: []
                        }
                ],
                listeners:
                {
                    tabchange: function(tab, panel) {
                        var grid = Ext.getCmp('idObjGrid');
                        if (grid)
                        {
                            //try 
                        //{
                            //grid = Ext.getCmp('idObjGrid');
                            var planIndex = 0;
                            var PlanOldIndex = 0;
                            var PlanYoungIndex = 0;
                            var PlanKPOldIndex = 0;
                            var PlanKPYoungIndex = 0;
                            
                            planIndex = grid.getGrid().getColumnModel().findColumnIndex('RequestData_AllowPlan'); 
                            
                            if (win.currentCategory == 2)
                            {
                                PlanOldIndex = grid.getGrid().getColumnModel().findColumnIndex('RequestData_PlanOld');
                                PlanYoungIndex = grid.getGrid().getColumnModel().findColumnIndex('RequestData_PlanYoung');
                                PlanKPOldIndex = grid.getGrid().getColumnModel().findColumnIndex('RequestData_PlanKPOld');
                                PlanKPYoungIndex = grid.getGrid().getColumnModel().findColumnIndex('RequestData_PlanKPYoung');
                                
                                grid.getGrid().getColumnModel().setHidden(PlanOldIndex, true);
                                grid.getGrid().getColumnModel().setHidden(PlanYoungIndex, true);
                                grid.getGrid().getColumnModel().setHidden(PlanKPOldIndex, true);
                                grid.getGrid().getColumnModel().setHidden(PlanKPYoungIndex, true);
                            }
                            
                            if (grid) 
                            {
                                
                                switch(panel.id) 
                                {
                                    case 'tab_adults':
                                        win.searchParams.MesAgeGroup_id = 1;
                                        
                                        if (planIndex != -1)
                                        {
                                            grid.getGrid().getColumnModel().setHidden(planIndex, false);
                                        }
                                        
                                        if (win.currentCategory == 2)
                                        {
                                            grid.getGrid().getColumnModel().setHidden(PlanOldIndex, true);
                                            grid.getGrid().getColumnModel().setHidden(PlanYoungIndex, true);
                                            grid.getGrid().getColumnModel().setHidden(PlanKPOldIndex, true);
                                            grid.getGrid().getColumnModel().setHidden(PlanKPYoungIndex, true);
                                        }
                                        win.extraFieldsVisible = false;
                                        win.allowPlanVisible = true;
                                        break;
                                    case 'tab_kids':
                                        win.searchParams.MesAgeGroup_id = 2;
                                        
                                        if (planIndex != -1)
                                        {
                                            grid.getGrid().getColumnModel().setHidden(planIndex, false);
                                        }
                                        
                                        if (win.currentCategory == 2)
                                        {
                                            grid.getGrid().getColumnModel().setHidden(PlanOldIndex, true);
                                            grid.getGrid().getColumnModel().setHidden(PlanYoungIndex, true);
                                            grid.getGrid().getColumnModel().setHidden(PlanKPOldIndex, true);
                                            grid.getGrid().getColumnModel().setHidden(PlanKPYoungIndex, true);
                                        }
                                        win.extraFieldsVisible = false;
                                        win.allowPlanVisible = true;
                                        break;
                                    case 'tab_all':
                                        
                                        win.searchParams.MesAgeGroup_id = 3;
                                        
                                        if (planIndex >= 0)
                                        {
                                            grid.getGrid().getColumnModel().setHidden(planIndex, true);
                                        }
                                        
                                        if (win.currentCategory == 2)
                                        {
                                            grid.getGrid().getColumnModel().setHidden(PlanOldIndex, false);
                                            grid.getGrid().getColumnModel().setHidden(PlanYoungIndex, false);
                                            grid.getGrid().getColumnModel().setHidden(PlanKPOldIndex, false);
                                            grid.getGrid().getColumnModel().setHidden(PlanKPYoungIndex, false);
                                        }
                                        
                                        
                                        
                                        grid.getGrid().getColumnModel().setEditable(grid.getGrid().getColumnModel().findColumnIndex('RequestData_KP'), false);
                                        
                                        win.extraFieldsVisible = true;
                                        win.allowPlanVisible = false;
                                        
                                        break;
                                }      
                            }
                            
                            var rec = win.VolumeTypeGrid.getGrid().getSelectionModel().getSelected();
                            win.selectCat(rec);
                            
                            if (panel.id == 'tab_all')
                            {   
                                var ColumnCount = grid.getGrid().getColumnModel().getColumnCount(false);
                                for (var i = 0; i < ColumnCount; i++)
                                {
                                    //так почему-то не работает. Работает только через getCmp()
                                    //grid.getGrid().getColumnModel().setEditable(i,false);
                                    Ext.getCmp('idObjGrid').getGrid().getColumnModel().setEditable(i,false);
                                }
                            }
                            
                            win.hideRouteFields();
                            
                        grid.loadData({globalFilters: win.searchParams});
                        }
                    }
                }
            });
            
//            this.Splitter = new Ext.SplitBar(Ext.getCmp('idCatPlanGrid'), Ext.getCmp('idAgeTabPanel'));
//            this.Splitter.id = 'splitter_id';
//            this.Splitter.setAdapter() = 'splitter_id';)
//            this.Splitter.id = 'splitter_id';
//            this.Splitter.id = 'splitter_id';
//            this.Splitter.id = 'splitter_id';
            
            
            this.VolumeTypeAttributeValueGridFilters = new Ext.form.FormPanel(
            {
                xtype: 'form',
                labelAlign: 'top',
                layout: 'form',
                autoHeight: true,
                labelWidth: 50,
                frame: true,
                border: false,
                keys: [{
                    key: Ext.EventObject.ENTER,
                    fn: function (e) {
                        win.doFilterVolumeTypeAttributeValueGrid();
                    },
                    stopEvent: true
                }],
                items: [{
                        listeners: {
                                collapse: function (p) {
                                    win.doLayout();
                                },
                                expand: function (p) {
                                    win.doLayout();
                                    //win.createDynamicFiltersFor(2);
                                }
                        },
                        xtype: 'fieldset',
                        style: 'margin: 0px 5px',
                        title: lang['filtryi'],
                        collapsible: true,
                        autoHeight: true,
                        labelWidth: 150,
                        anchor: '-10',
                        layout: 'form',
                        items: [ 
                        {
                            layout: 'column',
                            border: false,
                            labelAlign: 'left',
                            items: 
                            [
                                {
                                    xtype: 'panel',
                                    id: 'filter2row1',
                                    name: 'mo',
                                    style: 'font-size: 12px;font-weight:normal;margin: 2px 2px 2px 0px;',
                                    items: 
                                        [
                                            { 
                                                xtype: 'label', 
                                                text: 'Наименование: ',
                                                name: 'labelmo', 
                                                id: 'idFilter2NameLabel',
                                                style: 'font-size: 12px;font-weight:normal;margin: 2px 2px 2px 0px;'
                                            },
                                            {
                                                xtype: 'textfield',
                                                id: 'idFilter2Name',
                                                width : 250,
                                                fieldLabel: 'Код',
                                                style: 'font-size: 12px;font-weight:normal;margin: 2px 2px 2px 2px;'
                                            },
                                            { 
                                                xtype: 'label', 
                                                text: 'Разрешено планирование: ',
                                                name: 'labelmo', 
                                                id: 'idFilter2AllowPlanLabel',
                                                style: 'font-size: 12px;font-weight:normal;margin: 2px 2px 2px 20px;'
                                            }
                                        ]
                                },
                                {
                                    xtype: 'panel',
                                    id: 'filter2row12',
                                    name: 'mo',
                                    style: 'font-size: 12px;font-weight:normal;margin: 4px 2px 2px 2px;',
                                    items: [{
                                                xtype : 'swcombo',
                                                id : 'idFilterAllowPlan',
                                                mode: 'local',
                                                typeCode: 'string',
                                                orderBy: 'SprAllowPlan_id',
                                                resizable: false,
                                                editable: false,
                                                allowBlank: true,
                                                displayField: 'SprAllowPlan_Value',
                                                valueField: 'SprAllowPlan_id',
                                                triggerAction: 'all', 
                                                store: new Ext.data.Store(
                                                {
                                                    autoLoad: true,
                                                    reader: new Ext.data.JsonReader(
                                                        {
                                                            id: 'AllowPlan_id'
                                                        },
                                                        [
                                                            { name: 'SprAllowPlan_id', mapping: 'SprAllowPlan_id' },
                                                            { name: 'SprAllowPlan_Value', mapping: 'SprAllowPlan_Value' }
                                                        ]
                                                    ),
                                                    url:'/?c=VolPeriods&m=loadSprAllowPlan',
                                                }),
                                                listeners:
                                                {

                                                },
                                                width : 70,
                                                fieldLabel: 'Разрешено планирование'
                                }]
                                }
                                
                            ]
                        },
                        
                        {
                        border: false,
                        layout: 'column',
                        anchor: '-10',
                        items: [ {
                                    layout: 'form',
                                    border: false,
                                    items: [{
                                            text: BTN_FILTER,
                                            xtype: 'button',
                                            handler: function () {
                                                    win.doFilterObj();
                                            },
                                            iconCls: 'search16'
                                    }]
                            }, {
                                    layout: 'form',
                                    bodyStyle: 'padding-left: 5px;',
                                    border: false,
                                    items: [{
                                            text: BTN_RESETFILTER,
                                            xtype: 'button',
                                            handler: function () {
                                                    win.doResetFiltersObjGrid();
                                                    win.doFilterObj();
                                            },
                                            iconCls: 'resetsearch16'
                                    }]
                            }]
                    }
                    ]
                }]
        });
            
            this.GridPanel = new Ext.FormPanel(
            {
                id: 'idGridPanel',
                border: 'false',
                region: 'center',
                layout: 'fit',
                items: [

                ]
            });
            
            this.upPanel = new Ext.Panel(
            {
                region: 'north',
                layout: 'form',
                autoHeight: true,
                items:[
                    this.InfoPanel,
                    this.VolumeTypeGridFilters,
                    this.CatPanel,
                    //this.VolumeTypeGrid,
                    this.AgeTabPanel,
                    this.VolumeTypeAttributeValueGridFilters
                ]
            });

            this.TabPanel = new Ext.Panel(
            {
                id: 'idpanel',
                region: 'center',
                border: true,
                layout: 'border',
                items: [
                    this.upPanel,
                    this.GridPanel
                ]
            });
            
            Ext.apply(this, {
                layout: 'border',
                items: [
                        win.TabPanel
                ],
                buttons: [
                    {
                        handler: function() 
                        {
                            win.doControl(win.RequestList_id, 3);
                            Ext.getCmp('idVolRequestsGrid2').getGrid().getStore().reload();
                            
                            Ext.getCmp('btnFormirovat').hide();
                        }.createDelegate(this),
                        iconCls: 'ok16',
                        text: 'Сформировать',
                        id: 'btnFormirovat'
                    },
                    {
                        handler: function() 
                        {
                            win.doControl(win.RequestList_id, 4);
                            
                        }.createDelegate(this),
                        iconCls: 'ok16',
                        text: 'Утвердить',
                        id: 'btnUtverdit'
                    },
                    {
                        handler: function() 
                        {
                            var RL = Ext.getCmp('swVolRequestEditWindow');
                            RL.printRequest(win.RequestList_id, 'analit');
                        }.createDelegate(this),
                        iconCls: 'print16',
                        hidden: true,
                        text: 'Печать аналит. форм',
                        id: 'btnPrintRequest'
                    },
                    {
                        handler: function() 
                        {
                            var RL = Ext.getCmp('swVolRequestEditWindow');
                            RL.printRequest(this.RequestList_id, 'federal');
                        }.createDelegate(this),
                        iconCls: 'print16',
                        hidden: true,
                        text: 'Печать фед. форм',
                        id: 'btnPrintRequest'
                    },
                    {
                        iconCls: 'print16',
                        text: 'Печатные формы',
                        id: 'btnPrintRequestMenu',
                        menu: [
                                {
                                    name: 'print_fed',
                                    iconCls: 'print16',
                                    text: 'Федеральная форма',
                                    handler: function()
                                    {
                                        var RL = Ext.getCmp('swVolRequestEditWindow');
                                        RL.printRequest(win.RequestList_id, 'federal');
                                    }
                                }, 
                                {
                                    name: 'print_analit',
                                    iconCls: 'print16',
                                    text: 'Аналитическая форма',
                                    handler: function()
                                    {
                                        var RL = Ext.getCmp('swVolRequestEditWindow');
                                        RL.printRequest(win.RequestList_id, 'analit');
                                    }
                                }, 
                                {
                                    name: 'print_pgg',
                                    iconCls: 'print16',
                                    text: 'По ПГГ',
                                    handler: function()
                                    {
                                        var RL = Ext.getCmp('swVolRequestEditWindow');
                                        RL.printRequest(win.RequestList_id, 'pgg');
                                    }
                                },
                                {
                                    name: 'print_events',
                                    iconCls: 'print16',
                                    text: 'По мероприятиям',
                                    handler: function()
                                    {
                                        var RL = Ext.getCmp('swVolRequestEditWindow');
                                        RL.printRequest(win.RequestList_id, 'events');
                                    }
                                }
                            ]
                    },
                    {
                            text: '-'
                    },
                    HelpButton(this, 1),
                    {
                            handler: function () {
                                win.doResetFiltersVolumeTypeGrid();
                                win.doResetFiltersObjGrid();
                                win.hide();
                            }.createDelegate(this),
                            iconCls: 'close16',
                            text: BTN_FRMCLOSE
                    }]
            });

            sw.Promed.swVolRequestWindow.superclass.initComponent.apply(this, arguments);
	
        }
});