/**
 * swEvnSectionParamsSelectWindow - окно выбора палаты и лечащего врача профильного отделения стационара
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Hospital
 * @copyright    Copyright (c) 2009-2013 Swan Ltd.
 * @author       A. Permyakov
 * @version      07.2013
 *
 * @class        sw.Promed.swEvnSectionParamsSelectWindow
 * @extends      sw.Promed.BaseForm
 */
sw.Promed.swEvnSectionParamsSelectWindow = Ext.extend(sw.Promed.BaseForm, {
    title: lang['vyibor_palatyi_i_lechaschego_vracha'],
	autoHeight: true,
	border: false,
	buttonAlign: 'right',
	closable: true,
	closeAction: 'hide',
	modal: true,
	plain: false,
	resizable: false,
    width: 650,
	/**
	 * Обработчик выбора параметров
	 * override
	 */
	onSelect: Ext.emptyFn,
	/**
	 * Обработчик клика по кнопке "Выбрать"
	 */
	doSelect: function() {
        var thas = this;
        if ( !this.form.isValid() ) {
            sw.swMsg.show({
                buttons: Ext.Msg.OK,
                fn: function() {
                    thas.formPanel.getFirstInvalidEl().focus(true);
                },
                icon: Ext.Msg.WARNING,
                msg: ERR_INVFIELDS_MSG,
                title: ERR_INVFIELDS_TIT
            });
            return false;
        }

        this.params.LpuSectionWard_id = this.form.findField('LpuSectionWard_id').getValue();
        var record = this.form.findField('MedStaffFact_id').getStore().getById(this.form.findField('MedStaffFact_id').getValue());
        if (record) {
            this.params.MedStaffFact_id = this.form.findField('MedStaffFact_id').getValue();
            this.params.MedPersonal_id = record.get('MedPersonal_id');
            this.params.LpuSection_id = record.get('LpuSection_id');
        }

        this.hide();
        this.onSelect(this.params);
        return true;
	},
    /**
     * Загружаем список палат, в котором должны быть: указанная палата и остальные палаты профильного отделения, соответствующие полу пациента (включая общие палаты), в которых есть свободные места
     */
    wardOnSexFilter: function() {
        var filterdate = getGlobalOptions().date;
        if (this.form.findField('EvnSection_setDate').getValue()) {
            filterdate = Ext.util.Format.date(this.form.findField('EvnSection_setDate').getValue(), 'd.m.Y');
        }
        sw.Promed.LpuSectionWard.filterWardBySex({
            date: filterdate,
            LpuSection_id: this.form.findField('LpuSection_id').getValue(),
            Sex_id: this.params.Sex_id,
            lpuSectionWardCombo: this.form.findField('LpuSectionWard_id'),
            win: this
        }, 'freelyprofil');
    },
	/**
	 * Конструктор
	 */
	initComponent: function() {
        var thas = this;
		
		this.formPanel = new sw.Promed.FormPanel({
			autoHeight: true,
			border: false,
			frame: true,
			labelWidth: 150,
			layout: 'form',
			style: 'padding: 3px',
			items: [{
                id: 'ESecPS_EvnSection_setDate',
                name: 'EvnSection_setDate',
                listeners: {
                    change: function (field, newValue) {
                        var lpu_section_id = thas.form.findField('LpuSection_id').getValue();
                        var med_staff_fact_id = thas.form.findField('MedStaffFact_id').getValue();

                        thas.form.findField('LpuSection_id').clearValue();
                        thas.form.findField('MedStaffFact_id').clearValue();

                        if (!newValue) {
                            setLpuSectionGlobalStoreFilter({
                                isStac:true
                            });
                            setMedStaffFactGlobalStoreFilter({
                                //dateTo:Ext.util.Format.date(evn_section_dis_date, 'd.m.Y'),
								EvnClass_SysNick: 'EvnSection',
                                isStac:true/*,
                                isDoctor:true*/
                            });
                        } else {
                            setLpuSectionGlobalStoreFilter({
                                // allowLowLevel: 'yes',
                                isStac:true,
                                onDate:Ext.util.Format.date(newValue, 'd.m.Y')
                            });
                            setMedStaffFactGlobalStoreFilter({
                                dateFrom:Ext.util.Format.date(newValue, 'd.m.Y'),
                                //dateTo:Ext.util.Format.date(evn_section_dis_date, 'd.m.Y'),
								EvnClass_SysNick: 'EvnSection',
                                isStac:true/*,
                                isDoctor:true*/
                            });
                        }
                        thas.form.findField('LpuSection_id').getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));
                        thas.form.findField('MedStaffFact_id').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));

                        if (thas.form.findField('LpuSection_id').getStore().getById(lpu_section_id)) {
                            thas.form.findField('LpuSection_id').setValue(lpu_section_id);
                            thas.form.findField('LpuSection_id').fireEvent('change', thas.form.findField('LpuSection_id'), lpu_section_id);
                        }
                        else {
                            thas.form.findField('LpuSection_id').fireEvent('change', thas.form.findField('LpuSection_id'), null);
                        }

                        if (thas.form.findField('MedStaffFact_id').getStore().getById(med_staff_fact_id)) {
                            thas.form.findField('MedStaffFact_id').setValue(med_staff_fact_id);
                        }
                    }
                },
                xtype:'swdatefield'
            },
                {
                hiddenName: 'LpuSection_id',
                listeners: {
                    change: function () {
                        thas.wardOnSexFilter();
                    }
                },
                id: 'ESecPS_LpuSectionCombo',
                linkedElements: [
                    'ESecPS_LpuSectionWardCombo',
                    'ESecPS_MedStaffFactCombo'
                ],
                width: 400,
                disabled: true,
                xtype: 'swlpusectionglobalcombo'
            }, {
                fieldLabel: lang['palata'],
                //allowBlank: false,
                hiddenName: 'LpuSectionWard_id',
                id: 'ESecPS_LpuSectionWardCombo',
                parentElementId: 'ESecPS_LpuSectionCombo',
                width: 400,
                xtype: 'swlpusectionwardglobalcombo'
            }, {
                allowBlank: false,
                dateFieldId: 'ESecPS_EvnSection_setDate',
                enableOutOfDateValidation: true,
                fieldLabel: lang['vrach'],
                hiddenName: 'MedStaffFact_id',
                id: 'ESecPS_MedStaffFactCombo',
                listWidth: 550,
                parentElementId: 'ESecPS_LpuSectionCombo',
                width: 400,
                xtype: 'swmedstafffactglobalcombo'
            }],
            reader: new Ext.data.JsonReader({
                success: Ext.emptyFn
            }, [
                { name: 'DirType_id' }
            ])
		});
		
		Ext.apply(this, {
			buttons: [{
				handler : function() {
                    thas.doSelect();
				},
				iconCls : 'ok16',
				text: lang['vyibrat']
			}, {
				text: '-'
			},
			HelpButton(this, -1),
			{
				handler: function() {
                    thas.hide();
				},
				iconCls : 'cancel16',
				onShiftTabAction: function () {
                    thas.buttons[0].focus();
				},
				onTabAction: function () {
                    thas.form.findField('DirType_id').focus(true);
				},
				text: BTN_FRMCANCEL
			}],
			items: [ 
				this.formPanel
			]
		});
		sw.Promed.swEvnSectionParamsSelectWindow.superclass.initComponent.apply(this, arguments);

        this.form = this.formPanel.getForm();
	}, //end initComponent()
	/**
	 * Отображение окна
	 */
	show: function() {
		sw.Promed.swEvnSectionParamsSelectWindow.superclass.show.apply(this, arguments);
        var thas = this;
        this.form.reset();

		if (
            !arguments[0] || !arguments[0].params || !arguments[0].params.LpuSection_id
            || !arguments[0].params.MedStaffFact_id
        ) {
			sw.swMsg.alert(lang['oshibka'], lang['otsutstvuyut_neobhodimyie_parametryi'], function() { thas.hide(); } );
			return false;
		}

        this.params = arguments[0].params;
        this.onHide = arguments[0].onHide || Ext.emptyFn;
		this.onSelect = arguments[0].onSelect || Ext.emptyFn;

        if (!this.params.EvnSection_setDate) {
            this.params.EvnSection_setDate = Date.parseDate(getGlobalOptions().date, 'd.m.Y', true);
        }
        this.form.findField('LpuSection_id').setValue(this.params.LpuSection_id);
        this.form.findField('MedStaffFact_id').setValue(this.params.MedStaffFact_id);
        this.form.findField('EvnSection_setDate').hideContainer();
        this.form.findField('EvnSection_setDate').setValue(this.params.EvnSection_setDate);
        this.form.findField('EvnSection_setDate').fireEvent('change', this.form.findField('EvnSection_setDate'), this.form.findField('EvnSection_setDate').getValue());

		this.doLayout();
		this.syncSize();
        return true;
	} //end show()
});