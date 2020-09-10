/**
 * swMedServiceSelectWindow - Форма выбора службы по известной услуге
 *
 * Порядок отображения служб:
 * Последние N служб, на которые данный врач создавал направления.
 * Наше отделение
 * Наша группа отделений
 * Наше подразделение
 * Наше ЛПУ
 * службы в других ЛПУ
 *
 * В графе служба для услуг лабораторной диагностики показываем пункты забора
 * (размножая услугу лаборатории для каждого связанного ПЗ).
 * В остальных случаях показываем непосредственно службу.
 *
 * Расписание для отображения ищем в следующем порядке приоритетов до первого успеха:
 * услуга на ПЗ,
 * сам ПЗ,
 * услуга в лаборатории,
 * сама лаборатория.
 * Если расписание есть, показываем гиперссылкой первое свободное время,
 * в противном случае показываем "поставить в очередь".
 * По гиперссылке открываем расписание, в котором можно выбрать другое время.
 * После закрытия формы выбора бирки, в графе "расписание" должно отобразиться новое время.
 *
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      EvnPrescr
* @access       public
* @copyright    Copyright (c) 2009-2013 Swan Ltd.
* @version      09.2013
*/

sw.Promed.swMedServiceSelectWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swMedServiceSelectWindow',
	objectSrc: '/jscore/Forms/Prescription/swMedServiceSelectWindow.js',
	collapsible: false,
	draggable: true,
	height: 550,
	id: 'MedServiceSelectWindow',
    buttonAlign: 'left',
    closeAction: 'hide',
	maximized: true,
	minHeight: 550,
	minWidth: 800,
	modal: true,
	resizable: false,
	plain: true,
	width: 800,
    title: lang['vyibor_slujbyi'],
    listeners:
    {
        hide: function(win)
        {
            win.onHide();
        }
    },

    callback: Ext.emptyFn,
    onHide: Ext.emptyFn,
    userMedStaffFact: {},
    UslugaComplex_id: 0,
    PrescriptionType_Code: 0,

    /**
     * Показываем окно
     * @return {Boolean}
     */
	show: function() {
        sw.Promed.swMedServiceSelectWindow.superclass.show.apply(this, arguments);
		var thas = this;
		if (!arguments[0]
            || !arguments[0].userMedStaffFact
            || !arguments[0].PrescriptionType_Code
        ) {
			sw.swMsg.alert(lang['oshibka'], lang['nevernyie_parametryi'], function() { thas.hide(); } );
			return false;
		}
        this.PrescriptionType_Code = arguments[0].PrescriptionType_Code;
        this.userMedStaffFact = arguments[0].userMedStaffFact;
        this.isOnlyPolka = arguments[0].isOnlyPolka||0;
        this.UslugaComplex_id = arguments[0].UslugaComplex_id||null;
        this.filterByLpu_id = arguments[0].filterByLpu_id||null;
        this.filterByLpu_str = arguments[0].filterByLpu_str||'';
        this.callback = Ext.emptyFn;
        if (typeof arguments[0].callback == 'function') {
            this.callback = arguments[0].callback;
        }
        this.onHide = Ext.emptyFn;
        if (typeof arguments[0].onHide == 'function') {
            this.onHide = arguments[0].onHide;
        }
        this.loadMedServiceFrame();
        return true;
	},
    /**
     * Загрузка данных в грид
     */
    loadMedServiceFrame:function(){
        this.MedServiceFrame.removeAll();
        var baseParams = {};
        baseParams.userLpuSection_id = this.userMedStaffFact.LpuSection_id;
        baseParams.filterByUslugaComplex_id = this.UslugaComplex_id;
        baseParams.filterByLpu_id = this.filterByLpu_id;
        baseParams.filterByLpu_str = this.filterByLpu_str;
        baseParams.PrescriptionType_Code = this.PrescriptionType_Code;
        baseParams.isOnlyPolka = this.isOnlyPolka;
        baseParams.start = 0;
        baseParams.limit = 100;
        this.MedServiceFrame.loadData({
            globalFilters: baseParams
        });
    },
    /**
     * Действия по нажатию кнопки выбор
     */
    doSelect: function(){
        var rec = this.MedServiceFrame.getGrid().getSelectionModel().getSelected();
        if (!rec || !rec.get('MedService_id')) {
            sw.swMsg.alert(lang['soobschenie'], lang['vyi_ne_vyibrali_slujbu']);
            return false;
        }
        this.callback(rec);
        this.hide();
        return true;
    },
    /**
     * По гиперссылке открываем расписание, в котором можно выбрать другое время.
     * После закрытия формы выбора бирки, в графе "расписание" должно отобразиться новое время.
     */
    doApply: function(key){
        var store = this.MedServiceFrame.getGrid().getStore();
        var rec = store.getById(key);
        if (!rec) {
            return false;
        }

		if (rec.get('withResource')) {
			var datetime = Date.parseDate(rec.data.TimetableResource_begTime, 'd.m.Y H:i');
			var rs_data = {
				Lpu_id: rec.data.Lpu_id,
				MedService_id: rec.data.MedService_id,
				MedServiceType_id: rec.data.MedServiceType_id,
				MedService_Nick: rec.data.MedService_Nick,
				MedService_Name: rec.data.MedService_Name,
				MedServiceType_SysNick: rec.data.MedServiceType_SysNick,
				UslugaComplexMedService_id: rec.data.UslugaComplexMedService_id,
				Resource_id: rec.data.Resource_id,
				Resource_Name: rec.data.Resource_Name,
				date: datetime.format('d.m.Y')
			};

			sw.Promed.EvnPrescr.openTimetable({
				Resource: rs_data,
				callback: function (ttr) {
					if (ttr.TimetableResource_id > 0) {
						rec.set('TimetableResource_id', ttr.TimetableResource_id);
						rec.set('TimetableResource_begTime', ttr.TimetableResource_begTime);
						rec.commit();
					}
					getWnd('swTTRScheduleRecordWindow').hide();
				}
				//,userClearTimeMS: function() {}
			});
		} else {
			var datetime = Date.parseDate(rec.data.TimetableMedService_begTime, 'd.m.Y H:i');
			var ms_data = {
				Lpu_id: rec.data.Lpu_id,
				MedService_id: rec.data.MedService_id,
				MedServiceType_id: rec.data.MedServiceType_id,
				MedService_Nick: rec.data.MedService_Nick,
				MedService_Name: rec.data.MedService_Name,
				MedServiceType_SysNick: rec.data.MedServiceType_SysNick,
				date: datetime.format('d.m.Y')
			};
			if (rec.data.lab_MedService_id && rec.data.lab_MedService_id == rec.data.ttms_MedService_id) {
				ms_data.MedService_id = rec.data.lab_MedService_id;
			}
			// если это назначение лабораторной диагностики
			// и есть пункт забора
			// и у пункта забора есть расписание
			if (rec.data.pzm_MedService_id && rec.data.pzm_MedService_id == rec.data.ttms_MedService_id) {
				//то будем записывать в пункт забора
				ms_data.Lpu_id = rec.data.pzm_Lpu_id;
				ms_data.MedService_id = rec.data.pzm_MedService_id;
				ms_data.MedServiceType_id = rec.data.pzm_MedServiceType_id;
				ms_data.MedServiceType_SysNick = rec.data.pzm_MedServiceType_SysNick;
				ms_data.MedService_Nick = rec.data.pzm_MedService_Nick;
				ms_data.MedService_Name = rec.data.pzm_MedService_Name;
			}
			if (rec.data.ttms_MedService_id == "") {
				ms_data.UslugaComplexMedService_id = rec.data.UslugaComplexMedService_id;
			}

			sw.Promed.EvnPrescr.openTimetable({
				MedService: ms_data,
				callback: function (ttms) {
					if (ttms.TimetableMedService_id > 0) {
						rec.set('TimetableMedService_id', ttms.TimetableMedService_id);
						rec.set('TimetableMedService_begTime', ttms.TimetableMedService_begTime);
						rec.commit();
					}
					getWnd('swTTMSScheduleRecordWindow').hide();
				}
				//,userClearTimeMS: function() {}
			});
		}

        return true;
    },

    /**
     * Декларируем компоненты формы и создаем форму
     */
	initComponent: function() {
        var thas = this;
        var gridFieldKey = 'UslugaComplexMedService_key';
        this.MedServiceFrame = new sw.Promed.ViewFrame({
            id: 'MedServiceSelectViewFrame',
            actions: [
                {name:'action_add', hidden: true, disabled: true},
                {name:'action_edit', hidden: true, disabled: true},
                {name:'action_view', hidden: true, disabled: true},
                {name:'action_delete', hidden: true, disabled: true},
                {name:'action_refresh', hidden: true, disabled: true},
                {name:'action_print', hidden: true, disabled: true},
                {name:'action_resetfilter', hidden: true, disabled: true},
                {name:'action_save', hidden: true, disabled: true}
            ],
            stringfields: [
                {name: 'UslugaComplexMedService_key', type: 'string', hidden: true, key: true},
                {name: 'UslugaComplexMedService_id', type: 'int', hidden: true},
                {name: 'isComposite', type: 'int', hidden: true},
                {name: 'MedService_id', type: 'int', hidden: true},
                {name: 'UslugaComplex_id', type: 'int', hidden: true},
                {name: 'LpuUnit_id', type: 'int', hidden: true},
                {name: 'Lpu_id', type: 'int', hidden: true},
                {name: 'LpuBuilding_id', type: 'int', hidden: true},
                {name: 'LpuSection_id', type: 'int', hidden: true},
                {name: 'LpuUnitType_id', type: 'int', hidden: true},
                {name: 'LpuSectionProfile_id', type: 'int', hidden: true},
                {name: 'MedServiceType_id', type: 'int', hidden: true},
                {name: 'LpuUnitType_SysNick', type: 'string', hidden: true},
                {name: 'MedServiceType_SysNick', type: 'string', hidden: true},
				{name: 'Resource_id', type: 'int', hidden: true},
				{name: 'Resource_Name', type: 'string', hidden: true},
				{name: 'withResource', type: 'int', hidden: true},
                {name: 'UslugaComplex_Code', type: 'string', hidden: true},
                {name: 'UslugaComplex_Name', type: 'string', hidden: true},
                {name: 'UslugaComplex_FullName', header: lang['usluga'], sortable: true, autoexpand: true, autoExpandMin: 150, renderer: function(value, cellEl, rec){
                    if (!rec.get('UslugaComplex_Name')) return '';
                    if (getPrescriptionOptions().enable_show_service_code)
                        return rec.get('UslugaComplex_Code') +" "+rec.get('UslugaComplex_Name');
                    else
                        return rec.get('UslugaComplex_Name');
                }},
                { name: 'MedService_Nick', header: lang['slujba'], width: 150, sortable: true, renderer: function(value, cellEl, rec){
                    if (rec.get('pzm_MedService_Nick')) {
                        return rec.get('pzm_MedService_Nick');
                    }
                    return rec.get('MedService_Nick');
                }},
                { name: 'MedService_Name', type: 'string', hidden: true},
                { name: 'Lpu_Nick', type: 'string', header: lang['lpu'], width: 150, sortable: true},
                { name: 'LpuBuilding_Name', type: 'string', header: lang['podrazdelenie'], width: 150, sortable: true},
                { name: 'LpuUnit_Name', type: 'string', header: lang['gruppa_otdeleniy'], width: 150, sortable: true},
                { name: 'LpuSection_Name', type: 'string', header: lang['otdelenie'], width: 150, sortable: true},
                { name: 'LpuUnit_Address', type: 'string', header: lang['adres'], width: 200, sortable: true},

                {name: 'ttms_MedService_id', type: 'int', hidden: true},
				{name: 'ttr_Resource_id', type: 'int', hidden: true},
                {name: 'lab_MedService_id', type: 'int', hidden: true},
                {name: 'pzm_MedService_id', type: 'int', hidden: true},
                {name: 'pzm_Lpu_id', type: 'int', hidden: true},
                {name: 'pzm_MedServiceType_id', type: 'int', hidden: true},
                {name: 'pzm_MedServiceType_SysNick', type: 'string', hidden: true},
                {name: 'pzm_MedService_Nick', type: 'string', hidden: true},
                {name: 'pzm_MedService_Name', type: 'string', hidden: true},
                {name: 'TimetableMedService_id', type: 'int', hidden: true},//выбранная бирка
                {name: 'TimetableMedService_begTime', type: 'string', hidden: true},//первое свободное время или выбранное время
				{name: 'TimetableResource_id', type: 'int', hidden: true},
				{name: 'TimetableResource_begTime', type: 'string', hidden: true},
                { name: 'timetable', header: lang['raspisanie'], width: 100, sortable: true, renderer: function(value, cellEl, rec){
					var begTime = rec.get('withResource')?rec.get('TimetableResource_begTime'):rec.get('TimetableMedService_begTime');
                    if (begTime) {
                        var dt = Date.parseDate(begTime, 'd.m.Y H:i');
                        return '<a href="#" ' +
                            'id="apply_link_'+ rec.get(gridFieldKey) +'" '+
                            'onclick="Ext.getCmp(\'MedServiceSelectWindow\').doApply('+
                            "'"+ rec.get(gridFieldKey) +"'"+
                            ')">'+ dt.format('j M H:i').toLowerCase() +'</a>';
                    }
                    return lang['v_ochered'];
                } }
            ],
            autoLoadData: false,
            border: true,
            dataUrl: '/?c=MedService&m=getMedServiceSelectList',
            object: 'MedService',
            layout: 'fit',
            height: 300,
            root: 'data',
            totalProperty: 'totalCount',
            paging: true,
            remoteSort: true,
            region: 'center',
            toolbar: false,
            onLoadData: function() {
                //this.getGrid().getStore()
            },
            onDblClick: function() {
                thas.doSelect();
            },
            onEnter: function() {
                thas.doSelect();
            }
        });

    	Ext.apply(this, {
			buttonAlign: "right",
			buttons: [{
				handler: function() {
					thas.doSelect();
				},
				iconCls: 'ok16',
				text: lang['vyibrat']
			}, {
				text: '-'
			},
			HelpButton(this),
			{
				handler: function() {
					thas.hide();
				},
				iconCls: 'cancel16',
				text: BTN_FRMCLOSE
			}],
            border: false,
            layout: 'border',
			items: [
                this.MedServiceFrame
            ]
		});
		sw.Promed.swMedServiceSelectWindow.superclass.initComponent.apply(this, arguments);
	}
});