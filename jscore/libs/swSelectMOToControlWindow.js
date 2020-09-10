//Форма "Выбор МО для управления"

sw.Promed.swSelectMOToControlWindow = Ext.extend(sw.Promed.BaseForm, {
    title: 'Выбор МО для управления',
    modal: true,
    closable: true,
    closeAction: 'hide',
    width: 450,
    height: 220,
    callback: Ext.emptyFn,
    listeners: {
        'hide': function() {
            this.callback();
            //Ext.getCmp('select_mo_win').setVisible(false);
        }
    },
    show: function(){
        sw.Promed.swSelectMOToControlWindow.superclass.show.apply(this, arguments);

        this.callback = Ext.emptyFn;

        if ( arguments[0] && arguments[0].callback ) {
            this.callback = arguments[0].callback;
        }

        this.grid.loadData();
    },
    initComponent: function()
    {
        var me = this;

        me.grid = new sw.Promed.ViewFrame({
            selectionModel: 'multiselect',
            dataUrl: '/?c=LpuStructure&m=getLpuListWithSmp',
            layout: 'fit',
            region: 'center',
            paging: false,
            root: '',
            totalProperty: 'totalCount',
            toolbar: false,
            singleSelect: false,
            multi: true,
            //simpleSelect: true,
            autoLoadData: false,
            useEmptyRecord: false,
            noSelectFirstRowOnFocus: true,
            height: 150,
            onLoadData: function(){
                Ext.Ajax.request({
                    callback: function (options, success, response) {
                        if (!success) {
                            return;
                        }

                        var res = Ext.util.JSON.decode(response.responseText),
                            rows = [];

                        res.lpuWorkAccess.forEach(function (el){
                            rows.push(me.grid.getIndexByValue(el));
                        });
                        me.grid.ViewGridPanel.getSelectionModel().selectRows(rows);
                    },
                    url: '/?c=Options&m=getLpuWorkAccess'
                });
            },
            stringfields: [
                {name: 'Lpu_Nick', header: 'Медицинские организации', hidden: false, sortable: true, width: 380 },
                {name: 'Lpu_id', type: 'int', header: 'ID', key: true}
            ]
        });

        Ext.apply(me, {
            buttons: [
                {
                    text: BTN_FRMSAVE,
                    iconCls: 'save16',
                    handler: function()
                    {
                        me.doSave();
                    }
                },
                {
                    text:'-'
                },
                HelpButton(this, TABINDEX_AF + 10),
                {
                    handler: function() {
                        me.hide();
                    },
                    iconCls: 'cancel16',
                    tabIndex: TABINDEX_AF + 11,
                    text: BTN_FRMCLOSE
                }
            ],
            items: [
                me.grid
            ]
        });

        sw.Promed.swSelectMOToControlWindow.superclass.initComponent.apply(this, arguments);
    },
    doSave: function(){

      var me = this,
          collectChecked = [];

        me.grid.getMultiSelections().forEach(function (el){
            collectChecked.push(el.get('Lpu_id'))
        });

        Ext.Ajax.request({
            url: '/?c=Options&m=saveLpuWorkAccess',
            params: {
                'lpuWorkAccess': [collectChecked]
            },
            callback: function (opt, success, response) {
                if (success) {
                    var CenterDisasterMedicineWindow = Ext.getCmp('swWorkPlaceCenterDisasterMedicineWindow');
                    if(CenterDisasterMedicineWindow){
                        CenterDisasterMedicineWindow.reloadStores();
                    }
                    me.hide();
                }
            }
        });

    }

});