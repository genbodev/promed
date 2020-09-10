/**
 * swExportForLaborDepWindow - Выгрузка данных для Министерства Труда
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * @package      All
 * @access       public
 * @autor        Samir Abakhri
 * @copyright    Copyright (c) 2014 Swan Ltd.
 * @version      10.10.2014
 */

sw.Promed.swExportForLaborDepWindow = Ext.extend(sw.Promed.BaseForm,{
    autoHeight: true,
    objectName: 'swExportForLaborDepWindow',
    objectSrc: '/jscore/Forms/Common/swExportForLaborDepWindow.js',
    title: WND_EXP_LABDEP,
    layout: 'border',
    id: 'swExportForLaborDepWindow',
    modal: true,
    shim: false,
    resizable: false,
    maximizable: false,
    listeners:{
        hide: function(){
            this.onHide();
        }
    },
    onHide: Ext.emptyFn,
    width: 500,
    getLoadMask: function(){
        if (!this.loadMask){
            this.loadMask = new Ext.LoadMask(Ext.get(this.id), { msg: 'Подождите. �?дет формирование. ' });
        }
        return this.loadMask;
    },
    show: function(){
        sw.Promed.swExportForLaborDepWindow.superclass.show.apply(this, arguments);

        var _this = this,
            base_form = _this.mainPanel.getForm(),
            KLAreaStore = base_form.findField('KLAreaStat_idEdit').getStore();

        if ( getGlobalOptions().region && getGlobalOptions().region.name ) {
            _this.setTitle(WND_EXP_LABDEP + ' ' + getGlobalOptions().region.name);
        }

        _this.mainPanel.getForm().findField('PrivilegeType_id').setValue(2273);//84. Дети-инвалиды

        if (!Ext.isEmpty(KLAreaStore) && KLAreaStore.indexOfId(9999999) == -1) {
            KLAreaStore.loadData([{
                KLAreaStat_id: 9999999,
                KLAreaStat_Code: 0,
                KLArea_Name: lang['vse']
            }], true);
        }

        base_form.findField('KLAreaStat_idEdit').setValue(9999999);
    },
    submit: function(){
        var _this = this,
            base_form = _this.mainPanel.getForm();

		var loadMask = new Ext.LoadMask(Ext.get('swExportForLaborDepWindow'), { msg: "lang['vyipolnyaetsya_vyigruzka_dannyih_dlya_ministerstva_truda']" });
		loadMask.show();

        //надо помнить что у стандартного сабита таймаут 5 минут, и если запросы будут превышать это время то надо будет использовать ajax
		base_form.submit({
            params:
            {
                KLArea_Name: base_form.findField('KLAreaStat_idEdit').getFieldValue('KLArea_Name')
            },
            failure: function(result_form, action)
            {
                if (action.result)
                {
                    if (action.result.Error_Code && action.result.Error_Message)
                    {
                        Ext.Msg.alert(lang['oshibka_#']+action.result.Error_Code, action.result.Error_Message);
                    }
                    else
                    {
                        //Ext.Msg.alert('Ошибка #100003', 'При сохранении произошла ошибка!');
                    }
                }
                loadMask.hide();
            },
            success: function(result_form, action)
            {
                loadMask.hide();
                if (action.result && action.result.filename)
                {

                    var id_salt = Math.random();
                    var win_id = 'showdoc_' + Math.floor(id_salt * 10000);
                    window.open(action.result.filename, win_id);
                }
                else
                    Ext.Msg.alert(lang['oshibka_#100005'], lang['pri_sohranenii_proizoshla_oshibka']);
            }
        });

    },
    initComponent: function() {
        var _this = this;

        this.mainPanel = new sw.Promed.FormPanel({
            region: 'center',
            layout: 'form',
            border: false,
            frame: true,
            style: 'padding: 10px;',
            labelWidth: 150,
            id: 'EFLD_mainPanel',
            items:[{
                allowBlank: false,
                fieldLabel: lang['period'],
                plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false) ],
                name: 'LabExp_Period',
                width: 200,
                tabIndex: TABINDEX_FLDW + 1,
                xtype: 'daterangefield'
            },{
                allowBlank: false,
                fieldLabel: lang['lgotnaya_kategoriya'],
                name: 'PrivilegeType_id',
                width: 200,
                tabIndex: TABINDEX_FLDW + 5,
                xtype: 'swprivilegetypecombo'
            },{
                xtype: 'swklareastatcombo',
                tabIndex: TABINDEX_FLDW + 10,
                hiddenName: 'KLAreaStat_idEdit',
                id: 'KLAreaStat_Combo',
                width: 200,
                enableKeyEvents: true
            } ],
			reader: new Ext.data.JsonReader(
			{
				success: function()
				{
				//alert('success');
				}
			},
			[
				{ name: 'LabExp_Period' },
				{ name: 'PrivilegeType_id' },
				{ name: 'KLAreaStat_idEdit' }
			]
			),
            url:'/?c=Privilege&m=exportForLaborDep'
        });

        Ext.apply(this,{
            region: 'center',
            layout: 'form',
            buttons:[{
                text: lang['sformirovat'],
                id: 'lsqefOk',
                iconCls: 'ok16',
                handler: function() {
                    _this.submit();
                }
            },{
                text: '-'
            }
                //HelpButton(this, -1),
                /*{
                    iconCls: 'cancel16',
                    text: BTN_FRMCLOSE,
                    handler: function() {this.hide();}.createDelegate(this)
                }*/],
            items:[
                _this.mainPanel
            ]

        });
        sw.Promed.swExportForLaborDepWindow.superclass.initComponent.apply(this, arguments);
    }
});