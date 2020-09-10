//Форма поиска диагноза

Ext.define('sw.tools.swDiagSearchTreeWindow', {
    extend: 'Ext.window.Window',
    alias: 'widget.swDiagSearchTreeWindow',
    title: 'Диагноз: Поиск',
    width: 800,
    height: 555,
    layout: 'fit',
    modal: true,
    MKB: null,
    FilterDiagCode: null,
    filterDate: '',
    //>>>> Унаследовано
    MorbusType_SysNick: '',//Тип заболевания/нозологии
    PersonRegisterType_SysNick: '',
    withGroups: '',
    baseFilterFn: '',
    searchDiagCode: '',
    searchDiagName: '',
    onSelectDiag: Ext.emptyFn(),
    initComponent: function(){
        var me = this;
        me.TreeData = null;
        me.TreeStore = Ext.create('Ext.data.TreeStore',{
            idProperty: 'Diag_id',
            storeId: 'diagTreeStore',
            fields: [
                {name: 'Diag_id', type: 'int'},
                {name: 'id', type: 'int'},
                {name: 'DiagFinance_IsOms', type: 'int'},
                {name: 'DiagLevel_id', type: 'int'},
                {name: 'Diag_Name', type: 'string'},
                {name: 'text', type: 'string'},
                {name: 'Diag_Code', type: 'string'},
                {name: 'leaf', type: 'boolean'}
            ],
            root:{
                leaf: false,
                expanded: true
            },
            autoLoad:false,
            proxy: {
                limitParam: undefined,
                startParam: undefined,
                paramName: undefined,
                pageParam: undefined,
                type: 'ajax',
                url: '/?c=Diag&m=getDiagTreeSearchData',
                reader: {
                    type: 'json'
                },
                actionMethods: {
                    create : 'POST',
                    read   : 'POST',
                    update : 'POST',
                    destroy: 'POST'
                },
                extraParams: {
                    DiagLevel_id: 1,
                    Diag_Date: Ext.Date.format(new Date(), "d.m.Y"),
                    node: 'root'
                }
            }
        });

        this.TreePanel = Ext.create('Ext.tree.Panel',{
            height: 500,
            border: false,
            cls:'larger-text',
            rootVisible: false,
            id: this.id+'_TreePanel',
            displayField: 'text',
            store: me.TreeStore,
            dockedItems:[
                {
                    xtype: 'toolbar',
                    dock: 'top',
                    items: [
                        {
                            xtype: 'swDiag',
                            name: 'Diag_Code',
                            autoFilter: false,
                            width: 220,
                            translate: false,
                            labelAlign: 'right',
                            triggerFind: false,
                            triggerClear: false,
                            hideTrigger: true,
                            minChars: 2,
                            fieldLabel: 'Код',
                            listeners:{
                                select: function(cmb,rec){
                                    me.selectDiag(rec[0])
                                },
                                change:function(cmb,newVal){
                                    if(!this.autoFilter && newVal){
                                        var q = newVal.toString();
                                        q = (q)?q.split(' ')[0]:'';
                                        // если там есть русские символы, то делаем их нерусскимми (код же в английской транскрипции)
                                        q = LetterChange(q.charAt(0)) + q.slice(1, q.length);

                                        cmb.store.filterBy(function(rec){
                                            return (rec.get('Diag_Code').indexOf(q.toUpperCase()) != -1);
                                        });
                                        cmb.setRawValue(q);
                                        if(cmb.getPicker().isVisible()){
                                            cmb.expand();
                                        }
                                    }else{
                                        return false
                                    }
                                }
                            }

                        },{
                            xtype: 'swDiag',
                            name: 'Diag_Name',
                            width: 285,
                            translate: false,
                            autoFilter: true,
                            labelAlign: 'right',
                            triggerFind: false,
                            triggerClear: false,
                            minChars: 2,
                            hideTrigger: true,
                            fieldLabel: 'Название',
                            listeners:{
                                'select': function(cmb,rec){
                                    me.selectDiag(rec[0])
                                }
                            }

                        },{
                            xtype: 'button',
                            refId: 'searchBtn',
                            iconCls: 'search16',
                            text: 'Найти',
                            margin: '0 10',
                            handler: function(){
                                var Diag_Code = me.TreePanel.down('swDiag[name=Diag_Code]').getRawValue(),
                                    Diag_Name = me.TreePanel.down('swDiag[name=Diag_Name]').getRawValue();
                                if(Diag_Code.length > 1 || Diag_Name.length > 1){
                                    me.TreeStore.getProxy().extraParams = {
                                        DiagLevel_id: 0,
                                        Diag_Date: Ext.Date.format(new Date(), "d.m.Y"),
                                        Diag_Code: me.TreePanel.down('swDiag[name=Diag_Code]').getRawValue(),
                                        Diag_Name: me.TreePanel.down('swDiag[name=Diag_Name]').getRawValue(),
                                        node: 'root'
                                    };
                                    me.TreeStore.reload()
                                }else{
                                    Ext.Msg.alert('Ошибка','Введите условия поиска, не менее двух символов');
                                    return false;
                                }

                            }
                        },
                        {
                            xtype: 'button',
                            refId: 'resetBtn',
                            iconCls: 'reset16',
                            text: 'Сброс',
                            margin: '0 10',
                            handler: function(){
                                me.TreePanel.down('swDiag[name=Diag_Code]').clearValue();
                                me.TreePanel.down('swDiag[name=Diag_Name]').clearValue();
                                me.TreeStore.getProxy().extraParams = {
                                    DiagLevel_id: 1,
                                    node: 'root'
                                };
                                me.TreeStore.load()
                            }
                        }
                    ]
                },
                {
                    xtype: 'toolbar',
                    dock: 'bottom',
                    items: [
                        {
                            xtype: 'button',
                            refId: 'selectBtn',
                            iconCls: 'ok16',
                            text: 'Выбрать',
                            margin: '0 10',
                            handler: function(){
                                me.selectDiag()
                            }
                        },
                        '->',
                        {
                            xtype: 'button',
                            refId: 'cancelBtn',
                            iconCls: 'cancel16',
                            text: 'Закрыть',
                            margin: '0 10',
                            handler: function(){
                                me.close()
                            }
                        }
                    ]
                }
            ],
            listeners: {
                'beforeitemclick': function( tree, record){

                    me.TreeStore.getProxy().setExtraParam('DiagLevel_id', record.get('DiagLevel_id'));
                    me.TreeStore.getProxy().setExtraParam('node', record.get('id'))

                    if(!me.TreeStore.getProxy().extraParams.hasOwnProperty('Diag_Code'))
                        me.TreeStore.getProxy().setExtraParam('Diag_Code',record.get('DiagLevel_id')== 3 ? record.get('Diag_Code') : null);

                },
                'itemdblclick': function(tree,record){
                    if(record.get('leaf') != 0)
                        me.selectDiag()
                }
            }

        });


        Ext.applyIf(this,{
            items: this.TreePanel

        });
        this.callParent(arguments)
    },
    selectDiag: function(record){
        var me = this,
        rec = record ? record : me.TreePanel.getSelectionModel().getSelection()[0];

        if(!rec || !rec.get('Diag_id')){
            Ext.Msg.alert('Ошибка','Вы ничего не выбрали');
            return false;
        }

		if( rec.get('DiagLevel_id') <4 ){
			return false;
		}

        this.onSelectDiag(rec);
        this.close();

    },
    show: function(){

        if (arguments[0] && arguments[0].onSelectDiag)
        {
            this.onSelectDiag = arguments[0].onSelectDiag;
        }
        this.callParent(arguments)
    }

});