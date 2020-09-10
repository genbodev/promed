/**
* swPersonToothCardEditWindow - Форма редактирования состояний зуба.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Stom
* @access       public
* @autor		Alexander Permyakov
* @copyright    Copyright (c) 2014 Swan Ltd.
* @version      5.2014
*/

sw.Promed.swPersonToothCardEditWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'swPersonToothCardEditWindow',
	buttonAlign: 'left',
	closable: true,
	closeAction: 'hide',
    draggable: true,
    width: 485,
    height: 290,
    minHeight: 290,
    minWidth: 485,
    layout: 'border',
    listeners: {
        'hide': function() {
            this.onHide();
        }
    },
    maximizable: false,
    modal: true,
    plain: true,
    resizable: true,
	doReset: function() {
        var me = this,
            checkedList = [],
            i, state, type;
        me.formPanel.getForm().reset();
        me.newToothData = {
            // эти атрибуты не могут измениться
            Tooth_SysNum: me.tooth.Tooth_SysNum,
            JawPartType_Code: me.tooth.JawPartType_Code,
            // эти атрибуты пока не могут измениться
            PersonToothCard_IsSuperSet: me.tooth.PersonToothCard_IsSuperSet,
            ToothPositionType_aid: me.tooth.ToothPositionType_aid,
            ToothPositionType_bid: me.tooth.ToothPositionType_bid,
            // остальные атрибуты могут измениться
            ToothType: null, // ToothStateClass_id типа зуба
            Tooth_id: me.tooth.Tooth_id,
            Tooth_Code: me.tooth.Tooth_Code,
            states: []
        };
        // в me.tooth - исходные данные
        me.tooth.ToothType = null;
        me.setTitle(lang['zub_№'] + me.tooth.Tooth_Code);
        for(i=0; i < me.tooth.states.length; i++) {
            state = me.tooth.states[i];
            if (state['ToothStateClass_id'].toString().inlist(['12','15','13','14'])) {
                me.tooth.ToothType = state['ToothStateClass_id'];
                type = state['ToothStateClass_id'];
            } else if (!state['ToothSurfaceType_id']) {
                checkedList.push(state['ToothStateClass_id']);
            }
        }
        if (!type) {
            type = sw.Promed.StomHelper.ToothMap.getDefaultToothType(me.Person_Age, me.tooth.Tooth_SysNum);
        }
        me.toothTypePanel.renderTypes([
            {
                ToothStateClass_Code: '1', ToothStateClass_Name: lang['postoyannyiy'],
                ToothStateClass_id: 12
            },
            {
                ToothStateClass_Code: '2', ToothStateClass_Name: lang['molochnyiy'],
                ToothStateClass_id: 13
            },
            {
                ToothStateClass_Code: '3', ToothStateClass_Name: lang['otsutstvuet'],
                ToothStateClass_id: 14
            },
            {
                ToothStateClass_Code: '4', ToothStateClass_Name: lang['iskusstvennyiy'],
                ToothStateClass_id: 15
            }
        ]);
        me.toothTypePanel.setValue(type);
        me.checkBoxGroup.setValue(checkedList.toString());
    },
    doSave: function() {
		var me = this,
            toothChanged = (!me.newToothData.Tooth_id),
            allowCancelAllState = false,
            state, i,
            canceled = [],
            newStates = [];
        // обработать и передать изменения
        if (!me.newToothData.ToothType) {
            sw.swMsg.alert(lang['oshibka'], lang['ne_ukazan_tip_zuba']);
            return false;
        }
        // в me.tooth - исходные данные
        switch (true) {
            case (!me.tooth.ToothType):
                // изначально зуб был невыросшим,
                // но в БД могут быть активные состояния созданные при оказании услуг
                //newStates.push(me.newToothData.ToothType);
                break;
            case (me.tooth.ToothType != me.newToothData.ToothType):
                // тип зуба изменился
                allowCancelAllState = true;
                state = sw.Promed.StomHelper.ToothMap.hasType(me.tooth.states, me.tooth.ToothType);
                if (state) {
                    canceled.push(state['PersonToothCard_id']);
                } else  {
                    sw.swMsg.alert(lang['oshibka'], lang['ne_udalos_poluchit_tip_zuba']);
                    return false;
                }
                //newStates.push(me.newToothData.ToothType);
                break;
            default:
                // тип зуба НЕ изменился
                state = sw.Promed.StomHelper.ToothMap.hasType(me.tooth.states, me.tooth.ToothType);
                if (!state) {
                    sw.swMsg.alert(lang['oshibka'], lang['ne_udalos_poluchit_tip_zuba']);
                    return false;
                }
                break;
        }
        // отменяем состояния поверхностей
        if (allowCancelAllState) {
            var onlySurfaceList = ['2','5'];
            for (i=0; i < onlySurfaceList.length; i++) {
                state = sw.Promed.StomHelper.ToothMap.hasState(me.tooth.states, onlySurfaceList[i], true);
                if (state) {
                    canceled.push(state['PersonToothCard_id']);
                }
            }
        }

        // обрабатываем изменения состояний только зуба с формы
        me.checkBoxGroup.items.each(function(item){
            var state = sw.Promed.StomHelper.ToothMap.hasState(me.tooth.states, item.value);
            if (item.checked  && !item.disabled){
                if (state && !toothChanged && !allowCancelAllState) {
                    // не изменилось
                } else {
                    // новое
                    newStates.push(item.value);
                }
                if (state && (toothChanged || allowCancelAllState)) {
                    // отменено
                    canceled.push(state['PersonToothCard_id']);
                }
            }
            if (!item.checked && state){
                // отменено
                canceled.push(state['PersonToothCard_id']);
            }
        });
        me.callback(me.newToothData, newStates, canceled);
        me.hide();
        return true;
	},
	initComponent: function() {
        var me = this;

        me.newToothData = {
            Tooth_SysNum: null,
            JawPartType_Code: null,
            PersonToothCard_IsSuperSet: null,
            ToothPositionType_aid: null,
            ToothPositionType_bid: null,
            ToothType: null,
            Tooth_id: null,
            Tooth_Code: null,
            states: []
        };

        var checkBoxItemsData = [
                [1,'radix','<span style="font-weight: bold">R</span> Корень']
                ,[4,'periodontitis','<span style="font-weight: bold">Pt</span> Периодонтит']
                ,[10,'crown','<span style="font-weight: bold">К</span> Коронка']
                ,[6,'alveolysis','<span style="font-weight: bold">А</span> Пародонтоз']
                ,[2,'caries','<span style="font-weight: bold">С</span> Кариес']
                ,[7,'mobility','<span style="font-weight: bold">П1</span> Подвижность I степени']
                ,[5,'seal','<span style="font-weight: bold">П</span> Пломбированный']
                ,[8,'mobility','<span style="font-weight: bold">П2</span> Подвижность II степени']
                ,[3,'pulpitis','<span style="font-weight: bold">Р</span> Пульпит']
                ,[9,'mobility','<span style="font-weight: bold">П3</span> Подвижность III степени']
            ],
            checkBoxItems = [],
            checkBoxGroupId = 'PTCEW_checkBoxGroup',
            i, value;
        for (i=0; i < checkBoxItemsData.length; i++) {
            value = checkBoxItemsData[i][0];
            checkBoxItems.push(new Ext.form.Checkbox({
                value: value,
                name: checkBoxItemsData[i][1],
                boxLabel: checkBoxItemsData[i][2],
                ownerCmp: me,
                checked: false,
                id: checkBoxGroupId +'_'+ value,
                listeners: {
                    check: function(box, checked) {
                        var rules = me.ToothStateClassRelation[box.value];
                        if (!checked) {
                            me.checkBoxGroup.items.each(function(item){
                                if (item.value.toString().inlist(rules['Deactivate'])) {
                                    if (1 == box.value && item.value!=2 && item.value!=5) {
                                        item.enable();
                                    }
                                }
                            });
                            return true;
                        }
                        me.checkBoxGroup.items.each(function(item){
                            if (item.value.toString().inlist(rules['Deactivate'])) {
                                item.setValue(false);
                                if (1 == box.value) {
                                    item.disable();
                                }
                            }
                        });
                        return true;
                    }
                }
            }));
        }

        me.checkBoxGroup = new Ext.form.CheckboxGroup({
            id: checkBoxGroupId,
            name: 'ToothStateClass_id_list',
            value: '',// строка с id разделенными запятой
            fieldLabel: '',
            hideLabel: true,
            columns: 2,
            items: checkBoxItems,
            getRawValue: function() {
                var out = [];
                this.items.each(function(item){
                    if(item.checked){
                        out.push(item.boxLabel);
                    }
                });
                return out.join(', ');
            },
            getValue: function() {
                var out = [];
                this.items.each(function(item){
                    if(item.checked){
                        out.push(item.value);
                    }
                });
                this.value = out.join(',');
                return this.value;
            },
            setValue: function(value) {
                if(typeof value != 'string') value = '';
                var id_list = value.split(',') || [];
                this.items.each(function(item){
                    item.setValue(item.value.toString().inlist(id_list));
                });
                this.value = value;
            }
        });

        me.toothTypePanel = new Ext.Panel({
            id: me.getId()+'ToothTypePanel',
            layout: 'fit',
            border: false,
            width: '99%',
            style: 'margin-bottom: 20px;',
            toothTypes: [],
            toothTypeCodes: {},
            setValue:function(val)
            {
                /*log({
                    sourceToothType: me.tooth.ToothType,
                    oldToothType: me.newToothData.ToothType,
                    newToothType: val
                });*/
                if (me.newToothData.ToothType != val && me.toothTypePanel.onChangeType(val)) {
                    me.newToothData.ToothType = val;
                    var type_list = Ext.query("div[class*=ToothType]", this.body.dom),
                        el, i, code, id;
                    for (i=0; i < type_list.length; i++) {
                        el = new Ext.Element(type_list[i]);
                        for (id in this.toothTypeCodes) {
                            code = this.toothTypeCodes[id];
                            if (id == val && el.hasClass('Code' + code)) {
                                el.setStyle('background-image', 'url(\'/img/toothmap/bg_o' + code + '.png\')');
                            }
                            if (id != val && el.hasClass('Code' + code)) {
                                el.setStyle('background-image', 'url(\'/img/toothmap/bg_n' + code + '.png\')');
                            }
                        }
                    }
                }
            },
            getValue: function()
            {
                return me.newToothData.ToothType;
            },
            onChangeType: function(id)
            {
                var rules = me.ToothStateClassRelation[id];
                
                if (id == 13 && (me.tooth.ToothType == 12 || me.tooth.ToothType == 15)) {
                    // выводим это сообщение, если в БД есть сведения о типе зуба
                    // и его состояния "кариес" или "пломба" - так и не понял почему такое условие?
                    if (sw.Promed.StomHelper.ToothMap.hasState(me.tooth.states, 2, true)
                        || sw.Promed.StomHelper.ToothMap.hasState(me.tooth.states, 5, true)
                    ) {
                        sw.swMsg.alert(lang['soobschenie'], lang['iskustvennyiy_i_postoyannyiy_zub_ne_mogut_zamenitsya_molochnyim']);
                        me.toothTypePanel.setValue(me.tooth.ToothType);
                        return false;
                    }
                }
                if (id == 15 && me.newToothData.Tooth_Code > 50) {
                    // молочный зуб нельзя заменить искуственным
                    if (me.tooth.ToothType == 13) {
                        sw.swMsg.alert(lang['soobschenie'], lang['smena_tipa_zuba_nevozmojna']);
                        me.toothTypePanel.setValue(13);
                        return false;
                    } else {
                        me.newToothData.Tooth_Code = me.newToothData.Tooth_Code - 40;
                        me.newToothData.Tooth_id = null;
                        me.setTitle(lang['zub_№'] + me.newToothData.Tooth_Code);
                    }
                }
                me.checkBoxGroup.items.each(function(item){
                    item.disable();
                    if (item.value.toString().inlist(rules['Compatible'])) {
                        item.enable();
                    } else {
                        item.setValue(false);
                    }
                    if (item.value.toString().inlist(['2','5'])) {
                        item.disable();
                    }
                });
                if (id == 12 && me.newToothData.Tooth_Code > 50) {
                    // Обработка замены молочного зуба постоянным
                    me.newToothData.Tooth_Code = me.newToothData.Tooth_Code - 40;
                    me.newToothData.Tooth_id = null;
                    me.setTitle(lang['zub_№'] + me.newToothData.Tooth_Code);
                }
                if (id == 13 && me.newToothData.Tooth_Code < 50) {
                    me.newToothData.Tooth_Code = me.newToothData.Tooth_Code + 40;
                    me.newToothData.Tooth_id = null;
                    me.setTitle(lang['zub_№'] + me.newToothData.Tooth_Code);
                }
                return true;
            },
            onMouseOut: function(val)
            {
                var code = this.toothTypeCodes[val],
                    isChecked = (this.getValue() == val),
                    type_list = Ext.query("div[class*=Code" + code +"]", this.body.dom),
                    el, i;
                for (i=0; i < type_list.length; i++) {
                    el = new Ext.Element(type_list[i]);
                    if (isChecked) {
                        el.setStyle('background-image', 'url(\'/img/toothmap/bg_o' + code + '.png\')');
                    } else {
                        el.setStyle('background-image', 'url(\'/img/toothmap/bg_n' + code + '.png\')');
                    }
                }
            },
            onMouseOver: function(val)
            {
                var code = this.toothTypeCodes[val],
                    type_list = Ext.query("div[class*=Code" + code +"]", this.body.dom),
                    el, i;
                for (i=0; i < type_list.length; i++) {
                    el = new Ext.Element(type_list[i]);
                    el.setStyle('background-image', 'url(\'/img/toothmap/bg_h' + code + '.png\')');
                }
            },
            renderTypes: function(types)
            {
                this.toothTypeCodes = {};
                var width = 0, i;
                for (i = 0; i < types.length; i++) {
                    switch (parseInt(types[i].ToothStateClass_Code)) {
                        case 1: types[i].bgWidth = 102; break;
                        case 2: types[i].bgWidth = 95; break;
                        case 3: types[i].bgWidth = 104; break;
                        default: types[i].bgWidth = 118; break;
                    }
                    width += types[i].bgWidth;
                    this.toothTypeCodes[types[i].ToothStateClass_id] = types[i].ToothStateClass_Code;
                }
                this.toothTypes = types;
                var wrapStyle = 'width: ' + width +'px; height: 28px;' +
                    'padding: 0; margin: 0; border: 0;';
                var commonStyle = 'width: {bgWidth}px; height: 28px; ' +
                    'text-align: center; color: #000; float: left;';
                var contentTpl = '<div style="margin: 6px;">' +
                    //'<span style="font-weight: bold;">{ToothStateClass_Code}</span>' +
                    ' {ToothStateClass_Name}' +
                    '</div>';
                var itemHandlers = ' onclick="Ext.getCmp(\'' +
                    me.toothTypePanel.getId() +
                    '\').setValue(\'{ToothStateClass_id}\');"';
                var commonHandlers = ' class="ToothType Code{ToothStateClass_Code}"' +
                    ' onmouseover="Ext.getCmp(\'' +
                    me.toothTypePanel.getId() +
                    '\').onMouseOver(\'{ToothStateClass_id}\');"' +
                    ' onmouseout="Ext.getCmp(\'' +
                    me.toothTypePanel.getId() +
                    '\').onMouseOut(\'{ToothStateClass_id}\');"';
                var itemStyle = ' background-image: url(\'/img/toothmap/bg_n{ToothStateClass_Code}.png\'); cursor: pointer;';
                var checkedItemStyle = ' background-image: url(\'/img/toothmap/bg_o{ToothStateClass_Code}.png\'); ';

                var tpl = new Ext.XTemplate(
                    '<div style="' + wrapStyle + '">',
                    '<tpl for=".">',
                    '<tpl if="this.isChecked(values.ToothStateClass_id)">',
                    '<div style="' + commonStyle + checkedItemStyle + '"' + commonHandlers + itemHandlers +'>',
                    contentTpl,
                    '</div>',
                    '</tpl>',
                    '<tpl if="!this.isChecked(values.ToothStateClass_id)">',
                    '<div style="' + commonStyle + itemStyle + '"' + commonHandlers + itemHandlers +'>',
                    contentTpl,
                    '</div>',
                    '</tpl>',
                    '</tpl>',
                    '</div>',
                    {
                        isChecked: function(id) {
                            return (me.newToothData.ToothType == id);
                        }
                    }
                );
                tpl.overwrite(this.body, this.toothTypes);
            }
        });

        me.formPanel = new Ext.form.FormPanel({
            region: 'center',
            bodyStyle: 'padding-top: 20px; padding-left: 25px;',
            items: [
                me.toothTypePanel,
                me.checkBoxGroup
            ]
        });

		Ext.apply(me, {
			buttons: [{
				handler: function() {
                    me.doSave();
				},
				iconCls: 'save16',
				text: BTN_FRMSAVE
			}, {
				text: '-'
			},
			HelpButton(me),
			{
				handler: function() {
                    me.hide();
				},
				iconCls: 'cancel16',
                tooltip: BTN_FRMCANCEL_TIP,
                text: BTN_FRMCANCEL
			}],
			items: [me.formPanel]
		});
		sw.Promed.swPersonToothCardEditWindow.superclass.initComponent.apply(this, arguments);
	},
	show: function() {
		sw.Promed.swPersonToothCardEditWindow.superclass.show.apply(this, arguments);
        
        var me = this;
        me.restore();
        me.center();

        me.onHide = Ext.emptyFn;

        if ( !arguments[0] ||
            !arguments[0].ToothStateClassRelation ||
            !arguments[0].tooth || !arguments[0].tooth['Tooth_Code']
        ) {
            sw.swMsg.alert(lang['soobschenie'], lang['nevernyie_parametryi'], function() {
                me.hide();
            });
            return false;
        }
        me.tooth = arguments[0].tooth;
        me.ToothStateClassRelation = arguments[0].ToothStateClassRelation;
        me.Person_Age = arguments[0].Person_Age || 0;
        me.onHide = arguments[0].onHide || Ext.emptyFn;
        me.callback = arguments[0].callback || Ext.emptyFn;
        me.doReset();
        return true;
	}
});
