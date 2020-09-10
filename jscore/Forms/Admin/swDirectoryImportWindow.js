/**
 * swDirectoryImportWindow - окно для импортирования данных в локальные справочники
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Admin
 * @access       public
 * @copyright    Copyright (c) 2009 Swan Ltd.
 * @author       Самир Абахри
 * @version      25.04.2014
 * @comment
 * @prefix       DIW
 *
 * @input data:
 * 		action - действие (add, edit, view)
 *     	LocalDbList_id - Id строки таблицы
 */
sw.Promed.swDirectoryImportWindow = Ext.extend(sw.Promed.BaseForm,
{
    action: null,
    //autoHeight: true,
    Height: 700,
    buttonAlign: 'left',
    callback: Ext.emptyFn,
    closable: true,
    closeAction: 'hide',
    draggable: true,
    //autoScroll: true,
    split: true,
    dataStore: [],
    enableFileDescription: false, //определяет наличие на форме поля для описания файла
    width: 600,
    y: 50,
    layout: 'form',
    comboCount: 0,
    Directory_Name: '',
    LocalDirectory_ImportPath: null,
    LocalDirectory_FileType: null,
    id: 'swDirectoryImportWindow',
    modal: true,
    onHide: Ext.emptyFn,
    plain: true,
    resizable: false,
    doSave: function(addParams) {
        var form = this.MainForm;

        if (!form.getForm().isValid()) {
            sw.swMsg.show({
                buttons: Ext.Msg.OK,
                fn: function() {
                    //form.getFirstInvalidEl().focus(true);
                },
                icon: Ext.Msg.WARNING,
                msg: ERR_INVFIELDS_MSG,
                title: ERR_INVFIELDS_TIT
            });
            return false;
        }

        /*if ( form.getForm().findField('LocalDirectory_needUnpack').checked ) {

            if (Ext.isEmpty(form.getForm().findField('LocalDirectory_FileMask'))) {
                sw.swMsg.show({
                    buttons: Ext.Msg.OK,
                    fn: function() {
                        form.getForm().findField('LocalDirectory_FileMask').focus(true);
                    },
                    icon: Ext.Msg.WARNING,
                    msg: lang['vvedite_masku_fayla_v_arhive'],
                    title: lang['vnimanie']
                });
                return false;
            }
        }*/

        this.submit(addParams);
        return true;
    },
    submit: function(addParams) {
        var _this = this,
            form = _this.DirectoryImportGrid.getGrid(),
            LocalDirectory_ComboValues = {},
            LocalDirectory_isPK = {},
            params ={},
            pkNotDefined = true;

        form.fileUpload = false;

        form.getStore().data.items.forEach(function(rec){

            LocalDirectory_ComboValues[rec.get('field_name')] = rec.get('file_field');
            LocalDirectory_isPK[rec.get('field_name')] = rec.get('isPK');

            if (!Ext.isEmpty(rec.get('isPK'))){
                pkNotDefined = false;
            }
        });

        /*for (var $i=1; $i <= _this.comboCount; $i++) {
            LocalDirectory_ComboValues[_this.ComboForm.findById('cb_' + $i).hiddenName] = _this.ComboForm.findById('cb_' + $i).getValue();
            LocalDirectory_isPK[_this.ComboForm.findById('ck_' + $i).hiddenName] = _this.ComboForm.findById('ck_' + $i).getValue();

            if (_this.ComboForm.findById('ck_' + $i).getValue()){
                pkNotDefined = false;
            }
        }*/

        if (pkNotDefined) {
            sw.swMsg.show({
                buttons: Ext.Msg.OK,
                fn: function() {
                },
                icon: Ext.Msg.WARNING,
                msg: lang['neobhodimo_ukzat_pervichnyiy_klyuch'],
                title: lang['vnimanie']
            });
            return false;
        }

        LocalDirectory_isPK = Ext.encode(LocalDirectory_isPK);
        LocalDirectory_ComboValues = Ext.encode(LocalDirectory_ComboValues);

        params.LocalDirectory_ImportPath = _this.LocalDirectory_ImportPath;
        params.LocalDirectory_FileType = _this.LocalDirectory_FileType;
        params.LocalDirectory_ComboValues = LocalDirectory_ComboValues;
        params.LocalDirectory_isPK = LocalDirectory_isPK;
        params.Directory_Name = _this.Directory_Name;
        params.start = 0;
        params.limit = 100;

        if (addParams != undefined) {
			for(var par in addParams) {
				if (par != 'remove') {
					params[par] = addParams[par];
				}
			}
		}

        Ext.Ajax.request({
            url:'/?c=MongoDBWork&m=saveDirectoryChanges',
            params: params,
            callback: function(opt, success, response) {

                if (success && response.responseText != '') {
                    var result = Ext.util.JSON.decode(response.responseText);
                    if (result.success) {
                        log(result);
                        if (result.data) {
                            var Fields = [];

                            for(var index in result.data[0]) {

                                var column = {};

                                column.header = index;
                                column.keyName = index;
                                column.id = index;
                                column.dataIndex = index;
                                column.name = index;
                                column.width = 120;
                                column.type = (['begDT', 'endDT', 'begDate', 'endDate', 'insDT', 'updDT', 'insDate', 'updDate'].indexOf(index) != '-1')?'date':'string';
                                column.sortable = true;
                                Fields.push(column);
                            }

                            //store
                            var store = new sw.Promed.Store({
                                root: 'data',
                                totalProperty: 'totalCount',
                                fields: Fields,
                                url : '/?c=MongoDBWork&m=saveDirectoryChanges'
                            });

                            var cm = new Ext.grid.ColumnModel(Fields);

                            var review_params = {
                                LocalDirectory_ImportPath: _this.LocalDirectory_ImportPath,
                                LocalDirectory_FileType: _this.LocalDirectory_FileType,
                                LocalDirectory_ComboValues: LocalDirectory_ComboValues,
                                LocalDirectory_isPK: LocalDirectory_isPK,
                                Directory_Name: _this.Directory_Name,
                                store: store,
                                cm: cm
                            };

                            getWnd('swDirectoryImportPreviewWindow').show(review_params);

                        } else if (result.Info_Message) {
                            sw.swMsg.show({
                                buttons: Ext.Msg.OK,
                                fn: function() {
                                    /*_this.ComboForm.findById('StructuredParamsList').destroy();
                                    if (!Ext.isEmpty(_this.ComboForm.findById('StructuredParamsGroupList'))){
                                        _this.ComboForm.findById('StructuredParamsGroupList').destroy();
                                    }*/
                                    _this.MainForm.enable();
                                    _this.hide();
                                },
                                icon: Ext.Msg.INFO,
                                msg: result.Info_Message,
                                title: lang['soobschenie']
                            });
                        } else {
                            sw.swMsg.show({
                                buttons: Ext.Msg.OK,
                                fn: function() {
                                    /*_this.ComboForm.findById('StructuredParamsList').destroy();
                                    if (!Ext.isEmpty(_this.ComboForm.findById('StructuredParamsGroupList'))){
                                        _this.ComboForm.findById('StructuredParamsGroupList').destroy();
                                    }*/
                                    _this.MainForm.enable();
                                    _this.hide();
                                },
                                icon: Ext.Msg.INFO,
                                msg: lang['spravochnik_uspeshno_obnovlen'],
                                title: lang['soobschenie']
                            });
                        }
                    } else {
                        sw.swMsg.show({
                            buttons: Ext.Msg.OK,
                            fn: function() {
                                //_this.hide();
                            },
                            icon: Ext.Msg.ERROR,
                            msg: lang['pri_vyipolnenii_operatsii_sohraneniya_proizoshla_oshibka_pojaluysta_povtorite_popyitku_chut_pozje'],
                            title: lang['oshibka']
                        });
                    }
                } else {
                    log(response);
                    sw.swMsg.show({
                        buttons: Ext.Msg.OK,
                        fn: function() {
                            //_this.hide();
                        },
                        icon: Ext.Msg.ERROR,
                        msg: lang['pri_popyitke_obnovit_spravochnik_proizoshla_oshibka'],
                        title: lang['oshibka']
                    });
                }
            }

            /*if (success && response.responseText != '') {
                var result = Ext.util.JSON.decode(response.responseText);
                if (result.success) {
                    g.getGrid().getStore().reload();
                    showSysMsg(lang['rezultatyi_analizov_poluchenyi_s_analizatora_i_sohranenyi_v_probe'],lang['poluchenie_rezultatov']);
                } else {
                    sw.swMsg.show({
                        buttons: Ext.Msg.OK,
                        fn: function() {
                        },
                        icon: Ext.Msg.WARNING,
                        msg: result.Error_Msg,
                        title: lang['poluchenie_rezultatov']
                    });
                }
            }*/
        });

        //_this.getLoadMask('Запись сохраняется, подождите...').show();
        /*form.submit({
            params: params,
            failure: function(result_form, action)  {
                _this.getLoadMask().hide();

                sw.swMsg.show({
                    buttons: Ext.Msg.OK,
                    fn: function() {
                        //_this.hide();
                    },
                    icon: Ext.Msg.ERROR,
                    msg: lang['pri_popyitke_obnovit_spravochnik_proizoshla_oshibka'],
                    title: lang['oshibka']
                });
            },
            success: function(result_form, action) {

                if (action.result) {
                    if (action.result.data) {

                        //Columns
                        var Fields = [];

                        for(var index in action.result.data[0]) {

                            var column = {};

                            column.header = index;
                            column.keyName = index;
                            column.id = index;
                            column.dataIndex = index;
                            column.name = index;
                            column.width = 120;
                            column.type = (['begDT', 'endDT', 'begDate', 'endDate', 'insDT', 'updDT', 'insDate', 'updDate'].indexOf(index) != '-1')?'date':'string';
                            column.sortable = true;
                            Fields.push(column);
                        }

                        //store
                        var store = new sw.Promed.Store({
                            root: 'data',
                            totalProperty: 'totalCount',
                            fields: Fields,
                            url : '/?c=MongoDBWork&m=saveDirectoryChanges'
                        });

                        var cm = new Ext.grid.ColumnModel(Fields);

                        var review_params = {
                            LocalDirectory_ImportPath: _this.LocalDirectory_ImportPath,
                            LocalDirectory_FileType: _this.LocalDirectory_FileType,
                            LocalDirectory_ComboValues: LocalDirectory_ComboValues,
                            LocalDirectory_isPK: LocalDirectory_isPK,
                            Directory_Name: _this.Directory_Name,
                            store: store,
                            cm: cm
                        };

                        getWnd('swDirectoryImportPreviewWindow').show(review_params);

                    } else {
                        sw.swMsg.show({
                            buttons: Ext.Msg.OK,
                            fn: function() {
                                _this.ComboForm.findById('StructuredParamsList').destroy();
                                if (!Ext.isEmpty(_this.ComboForm.findById('StructuredParamsGroupList'))){
                                    _this.ComboForm.findById('StructuredParamsGroupList').destroy();
                                }
                                _this.MainForm.enable();
                                _this.hide();
                            },
                            icon: Ext.Msg.INFO,
                            msg: lang['spravochnik_uspeshno_obnovlen'],
                            title: lang['soobschenie']
                        });
                    }
                } else {
                    sw.swMsg.show({
                        buttons: Ext.Msg.OK,
                        fn: function() {
                            //_this.hide();
                        },
                        icon: Ext.Msg.ERROR,
                        msg: lang['pri_vyipolnenii_operatsii_sohraneniya_proizoshla_oshibka_pojaluysta_povtorite_popyitku_chut_pozje'],
                        title: lang['oshibka']
                    });
                }
            }
        });*/
        return true;
    },
    show: function() {
        sw.Promed.swDirectoryImportWindow.superclass.show.apply(this, arguments);
        var _this = this;
        var base_form = _this.MainForm.getForm();
        var i = 1;
        base_form.reset();
        _this.callback = Ext.emptyFn;
        _this.onHide = Ext.emptyFn;

        if (arguments[0].DirectoryColumnNames)
            _this.DirectoryColumnNames = arguments[0].DirectoryColumnNames;
        else
            _this.DirectoryColumnNames = null;

        if (arguments[0].DirectoryColumnGroupNames)
            _this.DirectoryColumnGroupNames = arguments[0].DirectoryColumnGroupNames;
        else
            _this.DirectoryColumnGroupNames = null;

        if (arguments[0].Directory_Name)
            _this.Directory_Name = arguments[0].Directory_Name;
        else
            _this.Directory_Name = null;

        if (arguments[0].Directory_Schema)
            _this.Directory_Schema = arguments[0].Directory_Schema;
        else
            _this.Directory_Schema = null;

        _this.setTitle(lang['import_dannyih_spravochnika'] + _this.Directory_Name);

        this.DirectoryImportGrid.getGrid().getStore().removeAll();

        if (!_this.Directory_Name.inlist([ 'LpuSectionProfile', 'MedSpecOms' ])) {
            this.DirectoryImportGrid.getGrid().getStore().load({params:{Directory_Name: _this.Directory_Name, Directory_Schema: _this.Directory_Schema}});
            this.DirectoryImportGrid.disable();
        } else {
            this.DirectoryImportGrid.getGrid().getStore().load({params:{Directory_Name: _this.Directory_Name, Directory_Schema: _this.Directory_Schema, Group: 1}});
            this.DirectoryImportGrid.disable();
        }

        /*
        var fset = new Ext.form.FieldSet({
            title: lang['sootvetstvie_poley_spravochnika_polyam_v_importiruemom_fayle'],
            xtype: 'fieldset',
            style: 'padding: 4px',
            labelWidth: 250,
            hidden: false,
            autoHeight: true,
            autoWidth: true,
            scrollable: true,
            autoScroll: true,
            id: 'StructuredParamsList',
            region: 'center'
        });


        _this.DirectoryColumnNames.forEach(function(element, index, array) {

            if (element != 'Region_id') {
                var check = {
                    'id' : 'ck_' + i,
                    'fieldLabel' : element + lang['-_pervichnyiy_klyuch'],
                    'name' : element + '_check',
                    'inputValue' : element,
                    'hiddenName': element,
                    'labelSeparator' : '',
                    'cls': 'labelHover',
                    'xtype': 'checkbox'
                };

                var combo = {
                    'id' : 'cb_' + i,
                    'fieldLabel' : element,
                    'name' : element + '_combo',
                    'inputValue' : element,
                    'labelSeparator' : '',
                    'mode': 'local',
                    'editable': false,
                    'triggerAction': 'all',
                    'displayField': 'Field_Name',
                    'valueField': 'Field_Name',
                    'tpl': '<tpl for="."><div class="x-combo-list-item" style="color:red;">{Field_Name}</div></tpl>',
                    'hiddenName': element,
                    'cls': 'labelHover',
                    'width': 200,
                    'xtype': 'combo'
                };

                var checkbox = new Ext.form.Checkbox(check);
                var combobox = new Ext.form.ComboBox(combo);

                fset.add(checkbox);
                fset.add(combobox);
                i++;

                //Определяем есть ли в справочнике даты действия записи, если нет - не даём работать с таким справочником
                if ((element.indexOf('begDate') != -1) || (element.indexOf('begDT') != -1)) {
                    begDate = true;
                }

                if ((element.indexOf('endDate') != -1) || (element.indexOf('endDT') != -1)) {
                    endDate = true;
                }
            }
        });

        _this.ComboForm.add(fset);

        if (!Ext.isEmpty(_this.DirectoryColumnGroupNames)) {

            var grouPfset = new Ext.form.FieldSet({
                title: lang['sootvetstvie_poley_spravochnika_polyam_v_sopryajennoy_tablitse'] + _this.Directory_Name + ' GROUP',
                xtype: 'fieldset',
                style: 'padding: 4px',
                labelWidth: 250,
                hidden: false,
                autoHeight: true,
                autoWidth: true,
                scrollable: true,
                autoScroll: true,
                id: 'StructuredParamsGroupList',
                region: 'center'
            });

            _this.DirectoryColumnGroupNames.forEach(function(element, index, array) {
                if (element != 'Region_id') {
                    var check = {
                        'id' : 'ck_' + i,
                        'fieldLabel' : element + lang['-_pervichnyiy_klyuch'],
                        'name' : element + '_check',
                        'inputValue' : element,
                        'hiddenName': element,
                        'labelSeparator' : '',
                        'cls': 'labelHover',
                        'xtype': 'checkbox'
                    };

                    var combo = {
                        'id' : 'cb_' + i,
                        'fieldLabel' : element,
                        'name' : element + '_combo',
                        'inputValue' : element,
                        'labelSeparator' : '',
                        'mode': 'local',
                        'editable': false,
                        'triggerAction': 'all',
                        'displayField': 'Field_Name',
                        'valueField': 'Field_Name',
                        'tpl': '<tpl for="."><div class="x-combo-list-item" style="color:red;">{Field_Name}</div></tpl>',
                        'hiddenName': element,
                        'cls': 'labelHover',
                        'width': 200,
                        'xtype': 'combo'
                    };

                    var checkbox = new Ext.form.Checkbox(check);
                    var combobox = new Ext.form.ComboBox(combo);

                    grouPfset.add(checkbox);
                    grouPfset.add(combobox);
                    i++;

                    //Определяем есть ли в справочнике даты действия записи, если нет - не даём работать с таким справочником
                    if ((element.indexOf('begDate') != -1) || (element.indexOf('begDT') != -1)) {
                        begDate = true;
                    }

                    if ((element.indexOf('endDate') != -1) || (element.indexOf('endDT') != -1)) {
                        endDate = true;
                    }
                }
            });

            _this.ComboForm.add(grouPfset);
        }

        _this.comboCount = i - 1;
        _this.ComboForm.doLayout();
        Ext.getCmp('ComboForm').disable();
        */

    },
    getCombosStore: function() {

        var _this = this,
            form = _this.MainForm.getForm(),
            base_form = this.MainForm,
            LocalDirectory_FileType = form.findField('LocalDirectory_FileType').getValue();

        _this.LocalDirectory_FileType = LocalDirectory_FileType;

        if (Ext.isEmpty(form.findField('LocalDirectory_ImportPath').getValue())) {
            sw.swMsg.show({
                buttons: Ext.Msg.OK,
                icon: Ext.Msg.WARNING,
                msg: lang['ne_vyibran_fayl_spravochnika'],
                title: ERR_INVFIELDS_TIT
            });
            return false;
        }

        if (!base_form.getForm().isValid()) {
            sw.swMsg.show({
                buttons: Ext.Msg.OK,
                fn: function() {
                    //form.getFirstInvalidEl().focus(true);
                },
                icon: Ext.Msg.WARNING,
                msg: ERR_INVFIELDS_MSG,
                title: ERR_INVFIELDS_TIT
            });
            return false;
        }

        //_this.getLoadMask('Загрузка списка полей выбранного файла...').show();
        form.submit({
            params: {LocalDirectory_FileType: LocalDirectory_FileType},
            failure: function(result_form, action)
            {
                if ( action.result )
                {
                    if ( action.result.Error_Msg )
                    {
                        log(action.result.Error_Msg);
                        sw.swMsg.alert(lang['oshibka'], action.result.Error_Msg);
                    }
                    else
                    {
                        sw.swMsg.alert(lang['oshibka'], lang['vo_vremya_vyipolneniya_operatsii_polucheniya_spiska_poley_zagrujaemogo_fayla_proizoshla_oshibka_pojaluysta_povtorite_popyitku_chut_pozje']);
                    }
                }
                _this.getLoadMask().hide();
            },
            success: function(result_form, action)
            {
                _this.getLoadMask().hide();
                var answer = action.result;

                if (answer)
                {
                    if (answer.store)
                    {
                        sw.swMsg.show(
                        {
                            buttons: Ext.Msg.OK,
                            icon: Ext.Msg.INFO,
                            msg: lang['shablon_uspeshno_zagrujen'],
                            title: lang['soobschenie']
                        });

                        var dataStore = [];

                        for (var j = 0; j < answer.store.length; j++) {
                            if (answer.store[j] != 'deleted') {
                                dataStore.push([answer.store[j]]);
                            }
                        }

                        _this.dataStore = dataStore;

                    }
                    else {
                        sw.swMsg.show(
                            {
                                buttons: Ext.Msg.OK,
                                fn: function()
                                {
                                    //_this.MainForm.hide();
                                },
                                icon: Ext.Msg.ERROR,
                                msg: lang['vo_vremya_vyipolneniya_operatsii_zagruzki_shablona_proizoshla_oshibka_pojaluysta_povtorite_popyitku_chut_pozje'],
                                title: lang['oshibka']
                            });
                    }

                    if (answer.filepath) {
                        _this.LocalDirectory_ImportPath = answer.filepath;
                    }

                    if (answer.filepath && answer.store){
                        _this.DirectoryImportGrid.enable();
                        /*if (_this.Directory_Name.inlist([ 'LpuSectionProfile', 'MedSpecOms' ])) {
                            _this.DirectoryImportGroupGrid.enable();
                        }*/
                        _this.buttons[0].enable();
                        _this.buttons[1].enable();

                        _this.MainForm.disable();
                        _this.buttons[2].disable();
                    }
                }
            }
            /*success: function(result_form, action) //Для обычного дизайна
            {
                _this.getLoadMask().hide();
                var answer = action.result;

                if (answer)
                {
                    if (answer.store)
                    {
                        sw.swMsg.show(
                        {
                            buttons: Ext.Msg.OK,
                            icon: Ext.Msg.INFO,
                            msg: lang['shablon_uspeshno_zagrujen'],
                            title: lang['soobschenie']
                        });

                        var dataStore = [];

                        for (var j = 0; j < answer.store.length; j++) {
                            if (answer.store[j] != 'deleted') {
                                dataStore.push([answer.store[j]]);
                            }
                        }

                        for (var i = 1; i <= _this.comboCount; i++) {

                            _this.ComboForm.findById('cb_' + i).store = new Ext.data.SimpleStore({
                                key: 'Field_Name',
                                fields: [
                                    { name: 'Field_Name', type: 'string'}
                                ],
                                data: dataStore,
                                autoLoad: false
                            });

                            //_this.ComboForm.findById('cb_' + i).store.load();
                        }

                    }
                    else {
                        sw.swMsg.show(
                            {
                                buttons: Ext.Msg.OK,
                                fn: function()
                                {
                                    //_this.MainForm.hide();
                                },
                                icon: Ext.Msg.ERROR,
                                msg: lang['vo_vremya_vyipolneniya_operatsii_zagruzki_shablona_proizoshla_oshibka_pojaluysta_povtorite_popyitku_chut_pozje'],
                                title: lang['oshibka']
                            });
                    }

                    if (answer.filepath) {
                        _this.LocalDirectory_ImportPath = answer.filepath;
                    }

                    if (answer.filepath && answer.store){
                        Ext.getCmp('ComboForm').enable();
                        _this.buttons[0].enable();
                        _this.buttons[1].enable();

                        Ext.getCmp('MainForm').disable();
                        _this.buttons[2].disable();
                    }
                }
            }*/
        });
    },
    initComponent: function()
    {
        // Форма с полями
        var _this = this;

        this.DirectoryImportGrid = new sw.Promed.ViewFrame({
            actions: [
                { name: 'action_add', disabled: true, hidden: true },
                { name: 'action_edit', disabled: true, hidden: true },
                { name: 'action_view', disabled: true, hidden: true },
                { name: 'action_delete', disabled: true, hidden: true },
                { name: 'action_refresh', disabled: true, hidden: true },
                { name: 'action_print', disabled: true, hidden: true },
                { name: 'action_save', disabled: true, hidden: true }
            ],
            autoLoadData: false,
            dataUrl: '/?c=MongoDBWork&m=loadDirectoryFieldList',
            height: 300,
            autoWidth: true,
            //autoHeight: true,
            autoScroll: true,
            title: lang['sootvetstvie_poley_obnovlyaemogo_spravochnika_i_importiruemogo_fayla'],
            id: 'DIW_DirectoryImportGrid',
            onAfterEdit: function(o) {
                o.record.commit();
            },
            onLoadData: function() {
                this.doLayout();
            },
            region: 'center',
            saveAllParams: false,
            saveAtOnce: false,
            stringfields: [
                { name: 'cm', type: 'string', hidden: true },
                { name: 'field_name', type: 'string', header: lang['pole_v_spravochnike'], hidden: false, width: 200 },
                { name: 'isPK', sortable: false, type: 'checkcolumnedit', isparams: true, header: lang['pervichnyiy_klyuch'], width: 100 },
                { name: 'file_field', editor: new sw.Promed.SwDirectoryImportCombo({
					allowBlank: true,
					listWidth: 150,
					listeners: {
						'show': function() {
							//var combo = this;
							//var base_form = that.findById('LabSampleEditForm').getForm();

                            this.getStore().loadData(_this.dataStore);
							/*combo.record = EvnUslugaDataGrid.getGrid().getSelectionModel().getSelected();
							this.getStore().removeAll();
							this.getStore().load({
								params: {
									Analyzer_id: rec.get('Analyzer_id'),
									UnitOld_id: combo.record.get('Unit_id'),
									UslugaComplex_id: combo.record.get('UslugaComplex_id'),
									RefValues_id: combo.record.get('RefValues_id'),
									MedService_id: that.MedService_id
								}
							});*/
						}/*,
						'change': function(combo, newValue) {
							if (combo.record) {
								combo.record.set('Unit_id', combo.getValue());
								combo.record.commit();

								coeffRefValues(combo.record, combo.getFieldValue('Unit_Coeff'));
							}
						},
						'blur': function(combo, newValue) {
							var grid = EvnUslugaDataGrid.getGrid();
							grid.stopEditing();
						}*/
					}
				}), type: 'string', header: lang['sootvetstvuyuschee_pole_v_fayle'], width: 200}
            ],
            toolbar: false
        });

        /*this.DirectoryImportGroupGrid = new sw.Promed.ViewFrame({
            actions: [
                { name: 'action_add', disabled: true, hidden: true },
                { name: 'action_edit', disabled: true, hidden: true },
                { name: 'action_view', disabled: true, hidden: true },
                { name: 'action_delete', disabled: true, hidden: true },
                { name: 'action_refresh', disabled: true, hidden: true },
                { name: 'action_print', disabled: true, hidden: true },
                { name: 'action_save', disabled: true, hidden: true }
            ],
            autoLoadData: false,
            dataUrl: '/?c=MongoDBWork&m=loadDirectoryFieldList',
            height: 200,
            autoWidth: true,
            disabled: true,
            //hidden: !_this.Directory_Name.inlist([ 'LpuSectionProfile', 'MedSpecOms' ]),
            autoScroll: true,
            title: lang['sootvetstvie_poley_svyazannoy_tablitsyi_i_importiruemogo_fayla'],
            id: 'DIW_DirectoryImportGroupGrid',
            onAfterEdit: function(o) {
                o.record.commit();
            },
            onLoadData: function() {
                this.doLayout();
            },
            region: 'center',
            saveAllParams: false,
            saveAtOnce: false,
            stringfields: [
                { name: 'type', type: 'string', hidden: true },
                { name: 'field_name', type: 'string', header: lang['pole_v_spravochnike'], hidden: false, width: 120 },
                { name: 'isPK', sortable: false, type: 'checkcolumnedit', isparams: true, header: lang['pervichnyiy_klyuch'], width: 100 },
                { name: 'file_field', editor: new sw.Promed.SwDirectoryImportCombo({
					allowBlank: true,
					listWidth: 150,
					listeners: {
						'show': function() {
                            this.getStore().loadData(_this.dataStore);
						}
					}
				}), type: 'string', header: lang['sootvetstvuyuschie_pole_v_fayle'], width: 200}
            ],
            toolbar: false
        });*/

        /*this.ComboForm = new sw.Promed.FormPanel({
            id: 'ComboForm',
            frame: true,
            border: true,
            region: 'center',
            hidden: false,
            fileUpload: true,
            layout: 'form',
            labelAlign: 'right',
            hideLabel: true,
            //autoWidth: true,
            autoHeight: true,
            autoScroll: true,
            header: false,
            hideTitle: true,
            url:'/?c=MongoDBWork&m=saveDirectoryChanges'
        });*/

        this.MainForm = new sw.Promed.FormPanel({
            id: 'MainForm',
            frame: true,
            border: true,
            region: 'center',
            hidden: false,
            fileUpload: true,
            layout: 'form',
            labelAlign: 'right',
            labelWidth: 250,
            hideLabel: true,
            autoWidth: true,
            autoHeight: true,
            //autoScroll: true,
            header: false,
            hideTitle: true,
            url: '/?c=MongoDBWork&m=loadCombosStore',
            items: [
            {
                fieldLabel: lang['neobhodimost_raspakovki_arhiva'],
                name: 'LocalDirectory_needUnpack',
                handler: function(ctl, val) {
                    if (val) {
                        _this.MainForm.getForm().findField('LocalDirectory_FileMask').allowBlank = false;
                        _this.MainForm.getForm().findField('LocalDirectory_FileMask').enable();
                        _this.MainForm.getForm().findField('LocalDirectory_FileMask').setValue('');
                        _this.MainForm.getForm().findField('LocalDirectory_FileType').allowBlank = true;
                        _this.MainForm.getForm().findField('LocalDirectory_FileType').setValue('');
                        _this.MainForm.getForm().findField('LocalDirectory_FileType').disable();
                        _this.MainForm.getForm().findField('LocalDirectory_ImportFile').setValue('');
                        _this.MainForm.getForm().findField('LocalDirectory_ImportPath').setValue('');
                    } else {
                        _this.MainForm.getForm().findField('LocalDirectory_FileMask').allowBlank = true;
                        _this.MainForm.getForm().findField('LocalDirectory_FileMask').disable();
                        _this.MainForm.getForm().findField('LocalDirectory_FileMask').setValue('');
                        _this.MainForm.getForm().findField('LocalDirectory_FileType').enable();
                        _this.MainForm.getForm().findField('LocalDirectory_FileType').allowBlank = false;
                        _this.MainForm.getForm().findField('LocalDirectory_ImportPath').setValue('');
                        _this.MainForm.getForm().findField('LocalDirectory_ImportFile').setValue('');
                    }
                },
                width: 200,
                xtype: 'checkbox'
            },{
                fieldLabel: lang['maska_fayla'],
                name: 'LocalDirectory_FileMask',
                //allowBlank: false,
                disabled: true,
                width: 200,
                xtype: 'textfield'
            },{
                allowBlank: false,
                fieldLabel: lang['tip_fayla'],
                mode: 'local',
                store: new Ext.data.SimpleStore(
                    {
                        key: 'FileType_id',
                        sortInfo: { field: 'FileType_Code' },
                        fields: [
                            { name: 'FileType_id', type: 'int'},
                            { name: 'FileType_Code', type: 'int'},
                            { name: 'FileType_Name', type: 'string'}
                        ],
                        data: [
                            [ 1, 1, 'XML' ],
                            [ 2, 2, 'DBF' ]
                        ]
                    }),
                editable: false,
                triggerAction: 'all',
                displayField: 'FileType_Name',
                valueField: 'FileType_Code',
                tpl: '<tpl for="."><div class="x-combo-list-item" style="color:red;">{FileType_Name}</div></tpl>',
                hiddenName: 'LocalDirectory_FileType',
                name: 'LocalDirectory_FileType',
                listeners: {
                    'change': function(combo, newValue, oldValue){
                        _this.MainForm.getForm().findField('LocalDirectory_ImportFile').setValue('');
                        _this.MainForm.getForm().findField('LocalDirectory_ImportPath').setValue('');
                    }
                },
                width: 200,
                xtype: 'combo'
            },{
                fieldLabel: lang['ukazat_istochnik'],
                name: 'LocalDirectory_ImportPath',
                allowBlank: false,
                width: 200,
                /*listeners: {
                    'change': function(combo, newValue, oldValue){
                        _this.MainForm.getForm().findField('LocalDirectory_ImportPath').setValue(value);
                        _this.MainForm.getForm().findField('LocalDirectory_FileType').disable();
                    }
                },*/
                xtype: 'textfield'
            }, {
                xtype: 'fileuploadfield',
                buttonOnly: true,
                width: 80,
                labelWidth: 10,
                id: 'FileImportPath',
                //emptyText: 'Введите путь до файла',
                fieldLabel: lang['ili_vyibrat_fayl'],
                listeners: {
                    'fileselected': function(field, value){
                        var accessTypes = [];

                        if (_this.MainForm.getForm().findField('LocalDirectory_needUnpack').checked) {
                            accessTypes = ['RAR', 'ARJ', 'ZIP'];
                        } else if (_this.MainForm.getForm().findField('LocalDirectory_FileType').getValue() == 1) {
                            accessTypes = ['XML'];
                        } else if (_this.MainForm.getForm().findField('LocalDirectory_FileType').getValue() == 2) {
                            accessTypes = ['DBF'];
                        }

                        if( !(new RegExp(accessTypes.join('|'), 'ig')).test(value) ) {
                            _this.buttons[2].disable();
                            var errmsg = lang['dannyiy_tip_fayla_ne_podderjivaetsya_podderjivaemyie_tipyi'];
                            for(var i=0; i<accessTypes.length; i++) {
                                errmsg += '*.' + accessTypes[i] + (i+1 < accessTypes.length ? ', ' : '' );
                            }
                            sw.swMsg.alert(lang['oshibka'], errmsg, field.reset.createDelegate(field));
                            return false;
                        }

                        _this.buttons[2].enable();
                        _this.MainForm.getForm().findField('LocalDirectory_ImportPath').setValue(value);
                    }
                },
                name: 'LocalDirectory_ImportFile'
            }]
        });

        Ext.apply(this,
        {
            buttons: [{
                handler: function() {
                    var addParams = [];
                    addParams.mode = 'save';
                    _this.doSave('save');
                },
                disabled: true,
                iconCls: 'save16',
                text: lang['obnovit_dannyie']
            },{
                handler: function() {
                    var addParams = [];
                    addParams.mode = 'view';
                    _this.doSave(addParams);
                },
                iconCls: 'search16',
                disabled: true,
                text: lang['predvaritelnyiy_prosmotr']
            },{
                handler: function() {
                    _this.getCombosStore();
                },
                iconCls: 'search16',
                disabled: true,
                text: lang['zagruzit_spravochnik']
            }, {
                text: '-'
            },
            //HelpButton(this),
            {
                handler: function() {
                    //_this.DirectoryImportGrid.findById('StructuredParamsList').destroy();
                    //_this.DirectoryImportGrid.destroy();
                    /*if (!Ext.isEmpty(_this.ComboForm.findById('StructuredParamsGroupList'))){
                        _this.ComboForm.findById('StructuredParamsGroupList').destroy();
                    }*/
                    _this.MainForm.enable();
                    _this.hide();
                },
                iconCls: 'cancel16',
                text: BTN_FRMCANCEL
            }],
            items: [
                _this.MainForm,
                _this.DirectoryImportGrid
            ]
        });
        sw.Promed.swDirectoryImportWindow.superclass.initComponent.apply(this, arguments);
    }
});