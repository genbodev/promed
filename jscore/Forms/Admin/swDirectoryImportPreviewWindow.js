/**
 * swDirectoryImportPreviewWindow - окно предвариетнлього просмотра обновляемого справочника
 *
 * WebExpert - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Check
 * @access       public
 * @copyright    Copyright (c) 2013 Swan Ltd.
 * @author       Samir Abakhri
 * @version      28.03.2014
 */

sw.Promed.swDirectoryImportPreviewWindow = Ext.extend(sw.Promed.BaseForm, {

    /*autoHeight: true,
    //Height: 600,
    buttonAlign: 'left',
    callback: Ext.emptyFn,
    closable: true,
    closeAction: 'hide',
    draggable: true,
    split: true,
    enableFileDescription: false, //определяет наличие на форме поля для описания файла
    width: 600,
    y: 50,
    layout: 'form',
    comboCount: 0,
    id: 'DirectoryImportPreviewWindow',
    modal: true,
    onHide: Ext.emptyFn,
    plain: true,
    resizable: false,*/
    buttonAlign: 'left',
    closable: true,
    closeAction: 'hide',
    collapsible: true,
    monitorResize: true,
    draggable: true,
    height: 900,
    //y: 50,
    split: true,
    id: 'DirectoryImportPreviewWindow',
    layout: 'border',
    maximizable: true,
    minHeight: 800,
    minWidth: 500,
    modal: true,
    plain: true,
    resizable: true,
    title: '',
    width: 900,
    initComponent: function() {
        var form = this;
        this.DirectoryPreviewGrid = new sw.Promed.ViewFrame(
        {
            id: 'DirectoryImportPreviewViewGrid',
            title:'',
            object: '',
            autoLoadData: false,
            region: 'center',
            paging: true,
            pageSize: 100,
            toolbar: false,
            root: 'data',
            totalProperty: 'totalCount',
            stringfields:
            [
                { name: 'id', type: 'int', header: 'ID', key: true }
            ]

        });

        Ext.apply(this,
            {
            items:
            [
                this.DirectoryPreviewGrid
            ],
            buttons:
            [{
                text: '-'
            },
                HelpButton(this),
                {
                    iconCls: 'close16',
                    onTabAction: function()
                    {
                        form.filtersPanel.getForm().findField('KLAreaStat_id').focus();
                    },
                    handler: function() {
                        form.hide();
                    },
                    text: BTN_FRMCLOSE
            }]
        });
        sw.Promed.swDirectoryImportPreviewWindow.superclass.initComponent.apply(this, arguments);
    },
    keys: [{
        alt: true,
        fn: function(inp, e) {

        },
        key: [ Ext.EventObject.J, Ext.EventObject.C, Ext.EventObject.N ],
        stopEvent: true
    }],
    show: function() {
        sw.Promed.swDirectoryImportPreviewWindow.superclass.show.apply(this, arguments);
        this.restore();
        this.center();
        this.maximize();
        this.doLayout();

		if( !arguments[0] || !arguments[0].store || !arguments[0].cm || !arguments[0].Directory_Name) {
			sw.swMsg.alert(lang['oshibka'], lang['nevernyie_parametryi'], this.hide.createDelegate(this, []));
			return;
		}


		this.setTitle(lang['predvaritelnyiy_prosmotr_spravochnika'] + arguments[0].Directory_Name);

        var store = arguments[0].store,
            cm = arguments[0].cm,
            _this = this,
            params = {
                LocalDirectory_ImportPath: arguments[0].LocalDirectory_ImportPath,
                LocalDirectory_FileType: arguments[0].LocalDirectory_FileType,
                LocalDirectory_ComboValues: arguments[0].LocalDirectory_ComboValues,
                LocalDirectory_isPK: arguments[0].LocalDirectory_isPK,
                Directory_Name: arguments[0].Directory_Name,
                mode: 'view',
                start: 0,
                limit: _this.DirectoryPreviewGrid.pageSize
            };


        _this.DirectoryPreviewGrid.ViewGridPanel.reconfigure(store, cm);
        _this.DirectoryPreviewGrid.ViewGridPanel.getBottomToolbar().unbind(store);
        _this.DirectoryPreviewGrid.ViewGridPanel.getBottomToolbar().bind(store);
        _this.DirectoryPreviewGrid.loadData({globalFilters:params,  callback: function (){
            _this.DirectoryPreviewGrid.ViewGridPanel.getTopToolbar().items.last().el.innerHTML = '0 / '+ _this.DirectoryPreviewGrid.ViewGridPanel.getStore().getCount();
        }});
        _this.DirectoryPreviewGrid.ViewGridModel.addListener('rowselect', function(sm, rowIdx, record){
            var count = _this.DirectoryPreviewGrid.ViewGridPanel.getStore().getCount();
            var rowNum = rowIdx + 1;
            _this.DirectoryPreviewGrid.ViewGridPanel.getTopToolbar().items.last().el.innerHTML = rowNum+' / '+count;
        }, this);
    }
});