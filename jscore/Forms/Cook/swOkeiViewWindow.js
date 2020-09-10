/**
 * swOkeiViewWindow - окно просмотра единиц измерения
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Cook
 * @access			public
 * @copyright		Copyright (c) 2013 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			01.10.2013
 */

sw.Promed.swOkeiViewWindow = Ext.extend(sw.Promed.BaseForm,
    {
        closable: true,
        closeAction: 'hide',
        draggable: true,
        maximized: true,
        maximizable: true,
        id: 'swOkeiViewWindow',
        objectName: 'swOkeiViewWindow',
        objectSrc: '/jscore/Forms/Cook/swOkeiViewWindow.js',
        title: lang['spravochnik_edinits_izmereniya'],
        readOnly: false,

        show: function()
        {
            sw.Promed.swOkeiViewWindow.superclass.show.apply(this, arguments);

            var loadMask = new Ext.LoadMask(Ext.get('swOkeiViewWindow'), {msg: LOAD_WAIT});
            loadMask.show();

            var wnd = this;
            wnd.OkeiTypeGrid.getGrid().getStore().load();

            loadMask.hide();
        },

        initComponent: function()
        {
            var wnd = this;

            this.OkeiTypeGrid = new sw.Promed.ViewFrame(
                {
                    id: 'OkeiTypeGridPanel',
                    tbar: this.gridToolbar,
                    region: 'north',
                    paging: false,
                    dataUrl: '/?c=OkeiType&m=loadOkeiTypeGrid',
                    keys: [],
                    autoLoadData: false,
                    toolbar: false,
                    stringfields:
                        [
                            {name: 'OkeiType_id', type: 'int', header: 'ID', key: true},
                            {name: 'OkeiType_Code', type: 'string', header: lang['kod'], width: 200},
                            {name: 'OkeiType_Name', type: 'string', header: lang['naimenovanie'], id: 'autoexpand'}
                        ],
                    onRowSelect: function(sm,index,record)
                    {
                        var OkeiType_id = record.get('OkeiType_id');
                        var grid = wnd.OkeiGrid.getGrid();
                        grid.getStore().load({
                            params: {
                                OkeiType_id: OkeiType_id,
                                start: 0
                            }
                        });
                    }
                });

            this.OkeiGrid = new sw.Promed.ViewFrame(
                {
                    id: 'OkeiGridPanel',
                    tbar: this.gridToolbar,
                    region: 'center',
                    layout: 'fit',
                    dataUrl: '/?c=Okei&m=loadOkeiGrid',
                    keys: [],
                    pageSize: 100,
                    paging: true,
                    root: 'data',
                    totalProperty: 'totalCount',
                    autoLoadData: false,
                    toolbar: false,
                    stringfields:
                        [
                            {name: 'Okei_id', type: 'int', header: 'ID', key: true},
                            {name: 'OkeiType_id', type: 'int', hidden: true},
                            {name: 'Okei_cid', type: 'int', hidden: true},
                            {name: 'Okei_Code', type: 'string', header: lang['kod'], width: 150},
                            {name: 'Okei_NationSymbol', type: 'string', header: lang['oboznachenie'], width: 150},
                            {name: 'Okei_Name', type: 'string', header: lang['naimenovanie'], id: 'autoexpand'},
                            {name: 'OkeiType_Name', type: 'string', header: lang['mera_izmereniya'], width: 150},
                            {name: 'Okei_IsMain', header: lang['osnovnaya_mera_izmereniya'], width: 150,
                                renderer: sw.Promed.Format.checkColumn},
                            {name: 'Okei_cName', type: 'string', header: lang['naimenovanie_osnovnoy_edinitsyi'], width: 150},
                            {name: 'Okei_UnitConversion', type: 'float', header: lang['koeffitsient'], width: 150}
                        ]
                });

            Ext.apply(this,
                {
                    layout: 'border',
                    items:
                        [
                            this.OkeiTypeGrid,
                            this.OkeiGrid
                        ],
                    buttons:
                    [{
                    text: '-'
                    },
                    //HelpButton(this, TABINDEX_MPSCHED + 98),
                    {
                        iconCls: 'cancel16',
                        text: BTN_FRMCLOSE,
                        handler: function() {this.hide();}.createDelegate(this)
                    }]
                });
            sw.Promed.swOkeiViewWindow.superclass.initComponent.apply(this, arguments);
        }
    });


