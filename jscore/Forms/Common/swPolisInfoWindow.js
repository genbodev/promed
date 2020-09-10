/**
* swPolisInfoWindow - Окно отображения информации о полисе
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2013 Swan Ltd.
* @author       Samir Abakhri
* @version      22.12.2013
*/
sw.Promed.swPolisInfoWindow = Ext.extend(sw.Promed.BaseForm,
    {
        action: null,
        autoHeight: true,
        buttonAlign: 'left',
        callback: Ext.emptyFn,
        closable: true,
        closeAction: 'hide',
        draggable: true,
        split: true,
        width: 800,
        layout: 'form',
        id: 'PolisInfoWindow',
        listeners:
        {
            hide: function()
            {
                this.onHide();
            }
        },
        modal: true,
        onHide: Ext.emptyFn,
        plain: true,
        resizable: true,
        show: function()
        {
            sw.Promed.swPolisInfoWindow.superclass.show.apply(this, arguments);
            var current_window = this;
            if (!arguments[0])
            {
                sw.swMsg.show({
                    buttons: Ext.Msg.OK,
                    icon: Ext.Msg.ERROR,
                    msg: lang['oshibka_otkryitiya_formyi_ne_ukazanyi_nujnyie_vhodnyie_parametryi'],
                    title: lang['oshibka'],
                    fn: function() {
                        this.hide();
                    }
                });
            }

            this.focus();
            this.findById('PolisInfoForm').getForm().reset();
            this.callback = Ext.emptyFn;
            this.onHide = Ext.emptyFn;

            if (arguments[0].Person_id)
                this.Person_id = arguments[0].Person_id;
            else
                this.Person_id = null;

            if (arguments[0].callback)
            {
                this.callback = arguments[0].callback;
            }
            if (arguments[0].owner)
            {
                this.owner = arguments[0].owner;
            }
            if (arguments[0].onHide)
            {
                this.onHide = arguments[0].onHide;
            }

            var form = this.findById('PolisInfoForm');
            form.getForm().setValues(arguments[0]);

            var loadMask = new Ext.LoadMask(this.getEl(),{msg: LOAD_WAIT});
            loadMask.show();

            this.setTitle(lang['dannyie_polisa']);
            this.enableEdit(true);
            loadMask.hide();

            form.getForm().load({
                    params:
                    {
                        Person_id: current_window.Person_id
                    },
                    failure: function(f, o, a)
                    {
                        loadMask.hide();
                        sw.swMsg.show(
                            {
                                buttons: Ext.Msg.OK,
                                fn: function()
                                {
                                    current_window.hide();
                                },
                                icon: Ext.Msg.ERROR,
                                msg: lang['oshibka_zaprosa_k_serveru_poprobuyte_povtorit_operatsiyu'],
                                title: lang['oshibka']
                            });
                    },
                    success: function()
                    {
                        loadMask.hide();
                    },
                    url: '/?c=Person&m=getPersonPolisInfo'
            });

        },
        initComponent: function()
        {
            // Форма с полями 
            this.PolisInfoForm = new Ext.form.FormPanel(
                {
                    autoHeight: true,
                    bodyStyle: 'padding: 5px',
                    border: false,
                    buttonAlign: 'left',
                    frame: true,
                    id: 'PolisInfoForm',
                    labelAlign: 'right',
                    labelWidth: 180,
                    items:
                        [/*{
                            id: 'LPEW_Lpu_id',
                            name: 'Lpu_id',
                            value: 0,
                            xtype: 'hidden'
                        },*/{
                            fieldLabel: lang['fio'],
                            xtype: 'textfield',
                            anchor: '100%',
                            name: 'Polis_FIO',
                            tabIndex: TABINDEX_LPEEW + 3
                        },{
                            fieldLabel: lang['seriya_nomer_polisa'],
                            xtype: 'textfield',
                            anchor: '100%',
                            name: 'PolisSerNum',
                            tabIndex: TABINDEX_LPEEW + 3
                        },{
                            fieldLabel: lang['smo_prikrepleniya'],
                            xtype: 'textfield',
                            anchor: '100%',
                            name: 'PolisOrgSmo',
                            tabIndex: TABINDEX_LPEEW + 3
                        },{
                            fieldLabel: lang['data_nachala_deystviya'],
                            xtype: 'swdatefield',
                            anchor: '100%',
                            format: 'd.m.Y',
				            plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
                            anchor: '100%',
                            name: 'Polis_begDate',
                            tabIndex: TABINDEX_LPEEW + 3
                        },{
                            fieldLabel: lang['data_okonchaniya_deystviya'],
                            xtype: 'swdatefield',
                            anchor: '100%',
                            format: 'd.m.Y',
				            plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
                            name: 'Polis_endDate',
                            tabIndex: TABINDEX_LPEEW + 3
                        }],
                    reader: new Ext.data.JsonReader(
                        {
                            success: function()
                            {
                                //
                            }
                        },
                        [
                            {name: 'Polis_FIO'},
                            {name: 'PolisSerNum'},
                            {name: 'PolisOrgSmo'},
                            {name: 'Polis_begDate'},
                            {name: 'Polis_endDate'}
                        ])
                });
            Ext.apply(this,
                {
                    buttons:
                        [
                        {
                            text: '-'
                        },
                        //HelpButton(this),
                        {
                            handler: function()
                            {
                                this.ownerCt.hide();
                            },
                            iconCls: 'cancel16',
                            tabIndex: TABINDEX_LPEEW + 17,
                            text: BTN_FRMCANCEL
                        }],
                    items: [this.PolisInfoForm]
                });
            sw.Promed.swPolisInfoWindow.superclass.initComponent.apply(this, arguments);
        }
    });