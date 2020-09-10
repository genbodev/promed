/**
 * swXmlMarkerEditWindow - Параметры маркера документа
 */
 
/*NO PARSE JSON*/

sw.Promed.swXmlMarkerEditWindow = Ext.extend(sw.Promed.BaseForm,
{
	codeRefresh: true,
	objectName: 'swXmlMarkerEditWindow',
	objectSrc: '/jscore/Forms/Common/swXmlMarkerEditWindow.js',
	
	closable: true,
	closeAction: 'hide',
	winTitle: lang['parametryi_markera_dokumenta'],
	iconCls: 'template16',
	id: 'swXmlMarkerEditWindow',
	readOnly: false,

	callback: Ext.emptyFn,
	onHide: Ext.emptyFn,
	listeners: 
	{
		hide: function()
		{
			this.onHide();
		}
	},
	
	/**
	 * Смена шага
	 *
	 * @param {string} step - Название шага
	 */
	setStep: function(step)
	{
		this.Wizard.params.prevStep = this.Wizard.params.step;
		this.Wizard.params.step = step;
		switch(step) {
			case "SelectXmlMarkerType": // Выбор типа маркера
				this.Wizard.Panel.layout.setActiveItem('dmwWizard_XmlMarkerType');
				this.Wizard.InputParams.doReset(false);
				this.Wizard.SelectXmlMarkerType.loadData({
					globalFilters: {}
				});
				break;
			case "InputParams": // Выбор подразделения ЛПУ
				this.Wizard.Panel.layout.setActiveItem('dmwWizard_XmlMarkerForm');
				this.Wizard.InputParams.doReset(true);
                var bf = this.Wizard.InputParams.getForm();
                var fieldXmlDataSection = bf.findField('XmlDataSection_id');
                var index = fieldXmlDataSection.getStore().findBy(function(rec) { return rec.get('XmlDataSection_SysNick') == 'autoname'; });
                var rec = fieldXmlDataSection.getStore().getAt(index);
                if (rec) {
                    fieldXmlDataSection.getStore().remove(rec);
                }
				break;
			default:
				break;
		}
        this.doLayout();
        this.syncSize();
		this.refreshWindowTitle();
		this.Wizard.Panel.doLayout();
	},
	/**
	 * Возврат на предыдущий шаг
	 */
	prevStep: function()
	{
		switch(this.Wizard.params.step) {
			case "InputParams":
				this.setStep('SelectXmlMarkerType');
				break;
			default:
				break;
		}
	},
	/**
	 * Обновление заголовка окна
	 */
	refreshWindowTitle: function() {
		switch(this.Wizard.params.step) {
			case "SelectXmlMarkerType":
				this.setTitle(this.winTitle + ' | Выбор типа маркера');
				break;
			case "InputParams":
				this.setTitle(this.winTitle + ' | Ввод данных');
				break;
			default:
				this.setTitle(this.winTitle);
				break;
		}
	},
	/**
	 * Получение текущего шага
	 */
	getStep: function()
	{
		return this.Wizard.params.step;
	},
	/**
	 *
	 */
	_onSelectXmlMarkerType: function(type)
	{
        this.Wizard.params['XmlMarkerType_Code'] = type;
        var form = this.Wizard.InputParams;
        var base_form = this.Wizard.InputParams.getForm();
        var fieldXmlType = base_form.findField('XmlType_id');
        var fieldUslugaComplexAttributeType = base_form.findField('UslugaComplexAttributeType_id');
        var fieldXmlDataSection = base_form.findField('XmlDataSection_id');
        var fieldXmlDataSelectType = base_form.findField('XmlDataSelectType_id');
        var fieldSqlOrderType = base_form.findField('SqlOrderType_id');
        var fieldCode2011list = base_form.findField('code2011list');
		switch(parseInt(type)){
            case 1: // Маркер списка документов одного типа
            case 2: // Маркер одного документа
            case 3: // Маркер одного раздела документа
                form.fieldForFocus = 'XmlType_id';
                fieldXmlType.setAllowBlank(false);
                fieldXmlType.setContainerVisible(true);
                fieldUslugaComplexAttributeType.setAllowBlank(true);
                fieldUslugaComplexAttributeType.setContainerVisible(false);
                fieldXmlDataSection.setAllowBlank(type!=3);
                fieldXmlDataSection.setContainerVisible(type==3);
                fieldXmlDataSelectType.setAllowBlank(type==1);
                fieldXmlDataSelectType.setContainerVisible(type!=1);
                fieldSqlOrderType.setAllowBlank(type!=1);
                fieldSqlOrderType.setContainerVisible(type==1);
                fieldCode2011list.setAllowBlank(true);
                fieldCode2011list.setContainerVisible(false);
                break;
            case 10: // Маркер списка протоколов услуг одного типа
            case 11: // Маркер одного раздела протоколов услуг одного типа
            case 12: // Маркер списка протоколов услуг по коду ГОСТ-2011
            case 13: // Маркер одного раздела протоколов услуг по коду ГОСТ-2011
                form.fieldForFocus = 'UslugaComplexAttributeType_id';
                fieldXmlType.setAllowBlank(true);
                fieldXmlType.setContainerVisible(false);
                fieldUslugaComplexAttributeType.setAllowBlank(true);
                fieldUslugaComplexAttributeType.setContainerVisible(true);
                fieldXmlDataSection.setAllowBlank(!(type==11||type==13));
                fieldXmlDataSection.setContainerVisible(type==11||type==13);
                fieldXmlDataSelectType.setAllowBlank(true);
                fieldXmlDataSelectType.setContainerVisible(false);
                fieldSqlOrderType.setAllowBlank(false);
                fieldSqlOrderType.setContainerVisible(true);
                fieldCode2011list.setAllowBlank(!(type==12||type==13));
                fieldCode2011list.setContainerVisible(type==12||type==13);
                break;
			default:
                this.hide();
                return false;
		}
		fieldXmlDataSelectType.getStore().clearFilter();
		fieldXmlDataSelectType.lastQuery = '';
		fieldXmlDataSelectType.getStore().filterBy(function(rec) {
			return (rec.get('XmlDataSelectType_id') <= 2 || type == 3);
		});
		this.setStep('InputParams');
        return true;
	},
	/**
	 *
	 */
	applyParams: function()
	{
        var form = this.Wizard.InputParams;
        var base_form = this.Wizard.InputParams.getForm();
        if ( !base_form.isValid() ) {
            sw.swMsg.show({
                buttons: Ext.Msg.OK,
                fn: function() {
                    form.getFirstInvalidEl().focus(true);
                },
                icon: Ext.Msg.WARNING,
                msg: ERR_INVFIELDS_MSG,
                title: ERR_INVFIELDS_TIT
            });
            return false;
        }
        var t = new Ext.Template(
            '<div><span class="{cls}" codelist="{codelist}">',
            '{text:trim}',
            '</span></div>'
        );
        var type = this.Wizard.params['XmlMarkerType_Code'] || '';
        var data = {
            cls: 'XmlMarker'+type,
            codelist: '',
            text: ''
        };
        var fieldXmlType = base_form.findField('XmlType_id');
        var fieldUslugaComplexAttributeType = base_form.findField('UslugaComplexAttributeType_id');
        var fieldXmlDataSection = base_form.findField('XmlDataSection_id');
        var fieldCode2011list = base_form.findField('code2011list');
        var index = -1;
        var fieldXmlDataSelectType = base_form.findField('XmlDataSelectType_id');
        var fieldSqlOrderType = base_form.findField('SqlOrderType_id');
        var fieldXmlDataLevel = base_form.findField('XmlDataLevel_id');
        switch(parseInt(type)){
            case 1: // Маркер списка документов одного типа
            case 2: // Маркер одного документа
            case 3: // Маркер одного раздела документа
                data.cls += ' '+ fieldXmlType.getValue();
                data.text += fieldXmlType.getStore().getById(fieldXmlType.getValue()).get('XmlType_Name');
                if (type==1) {
                    index = fieldSqlOrderType.getStore().findBy(function(rec) { return rec.get('SqlOrderType_id') == fieldSqlOrderType.getValue(); });
                    data.cls += '_'+ fieldSqlOrderType.getStore().getAt(index).get('SqlOrderType_SysNick');
                    data.text += ' '+ fieldSqlOrderType.getRawValue();
                } else {
                    index = fieldXmlDataSelectType.getStore().findBy(function(rec) { return rec.get('XmlDataSelectType_id') == fieldXmlDataSelectType.getValue(); });
                    data.cls += '_'+ fieldXmlDataSelectType.getStore().getAt(index).get('XmlDataSelectType_SysNick');
                    data.text += ' '+ fieldXmlDataSelectType.getRawValue();
                }
                if (type==3) {
                    data.cls += '_'+ fieldXmlDataSection.getStore().getById(fieldXmlDataSection.getValue()).get('XmlDataSection_SysNick');
                    data.text += ' '+ fieldXmlDataSection.getStore().getById(fieldXmlDataSection.getValue()).get('XmlDataSection_Name');
                }
				index = fieldXmlDataLevel.getStore().findBy(function(rec) { return rec.get('XmlDataLevel_id') == fieldXmlDataLevel.getValue(); });
				data.cls += '_'+ fieldXmlDataLevel.getStore().getAt(index).get('XmlDataLevel_SysNick');
				data.text += ' '+ fieldXmlDataLevel.getRawValue();
                break;
            case 10: // Маркер списка протоколов услуг одного типа
            case 11: // Маркер одного раздела протоколов услуг одного типа
            case 12: // Маркер списка протоколов услуг по коду ГОСТ-2011
            case 13: // Маркер одного раздела протоколов услуг по коду ГОСТ-2011
                data.cls += ' '+ fieldUslugaComplexAttributeType.getValue();
                data.text += fieldUslugaComplexAttributeType.getStore().getById(fieldUslugaComplexAttributeType.getValue()).get('UslugaComplexAttributeType_Name');
                index = fieldSqlOrderType.getStore().findBy(function(rec) { return rec.get('SqlOrderType_id') == fieldSqlOrderType.getValue(); });
                data.cls += '_'+ fieldSqlOrderType.getStore().getAt(index).get('SqlOrderType_SysNick');
                data.text += ' '+ fieldSqlOrderType.getRawValue();
                if (type==11||type==13) {
                    data.cls += '_'+ fieldXmlDataSection.getStore().getById(fieldXmlDataSection.getValue()).get('XmlDataSection_SysNick');
                    data.text += ' '+ fieldXmlDataSection.getStore().getById(fieldXmlDataSection.getValue()).get('XmlDataSection_Name');
                }
                if (type==12||type==13) {
                    data.codelist = fieldCode2011list.getValue();
                    var reg = /[AB]+[\.]?[0-9]+[\.]?[0-9]+[\.]?[0-9]+[,]*?/ig;
                    if (!reg.test(data.codelist)) {
                        sw.swMsg.show({
                            buttons: Ext.Msg.OK,
                            fn: function() {
                                fieldCode2011list.focus(true);
                            },
                            icon: Ext.Msg.WARNING,
                            msg: lang['proverte_format_spiska_kodov_gost-2011'],
                            title: ERR_INVFIELDS_TIT
                        });
                        return false;
                    }
                    data.text += lang['spisok_kodov_gost-2011']+ data.codelist;
                }
                break;
            default:
                this.hide();
                return false;
        }
        this.hide();
        this.callback(t.apply(data));
        return true;
	},

	show: function()
	{
		sw.Promed.swXmlMarkerEditWindow.superclass.show.apply(this, arguments);
		this.callback = (arguments[0] && typeof arguments[0].callback == 'function') ? arguments[0].callback : Ext.emptyFn;
		this.onHide =  (arguments[0] && typeof arguments[0].onHide == 'function') ? arguments[0].onHide : Ext.emptyFn;
		this.Wizard.params = {
			step: 'SelectXmlMarkerType'
		};
		// В форму можно передать сразу тип направления, тогда произойдет переход на следующий шаг
		if ( arguments[0] && typeof arguments[0].XmlMarkerTypeData == 'object' ) {
			this.Wizard.params['XmlMarkerType_id'] = arguments[0].XmlMarkerTypeData.XmlMarkerType_id;
			this.Wizard.params['XmlMarkerType_Name'] = arguments[0].XmlMarkerTypeData.XmlMarkerType_Name;
			this._onSelectXmlMarkerType(arguments[0].XmlMarkerTypeData.XmlMarkerType_Code);
			//нужно в гриде типов также выделить запись
			this.Wizard.SelectXmlMarkerType.loadData({
				globalFilters: {}
			});
		} else {
			// При открытии формы по умолчанию возвращаемся на первый шаг
			this.setStep('SelectXmlMarkerType');
			this.refreshWindowTitle();
		}
	},
	
	initComponent: function()
	{
		var thas = this;
		/**
		 * содержит в себе все формы и выбранные данные
		 */
		this.Wizard = {};
		
		/**
		 * Параметры
		 */
		this.Wizard.params = {
			step: 'SelectXmlMarkerType'
		};
		
		/**
		 * Панель списка типов маркеров
		 */
		this.Wizard.SelectXmlMarkerType = new sw.Promed.ViewFrame(
		{
			id: 'dmwWizard_XmlMarkerType',
			region: 'center',
			object: 'XmlMarkerType',
			border: true,
			dataUrl: '/?c=EvnXml&m=loadXmlMarkerTypeList',
			toolbar: false,
			autoLoadData: false,
			paging: false,
			isScrollToTopOnLoad: false,

			stringfields:
			[
				{name: 'XmlMarkerType_id', type: 'int', header: 'ID', key: true},
				{name: 'XmlMarkerType_Code', hidden: true, isparams: true},
				{name: 'XmlMarkerType_Name', id: 'autoexpand', header: lang['tip_markera']}
			],
			actions:
			[
				{name:'action_add', disabled: true, hidden: true},
				{name:'action_edit', disabled: false, hidden: true, handler: function()
					{
						var rec = thas.Wizard.SelectXmlMarkerType.getGrid().getSelectionModel().getSelected();
						if (!rec)
							return false;
						// В зависимости от выбранного типа делаем разное
						thas.Wizard.params['XmlMarkerType_id'] = rec.get('XmlMarkerType_id');
						thas.Wizard.params['XmlMarkerType_Name'] = rec.get('XmlMarkerType_Name');
						thas._onSelectXmlMarkerType(rec.get('XmlMarkerType_Code'));
						return true;
					}
				},
				{name:'action_view', disabled: true, hidden: true},
				{name:'action_delete', disabled: true, hidden: true}
			],
			onLoadData: function(isData)
			{
				var grid = thas.Wizard.SelectXmlMarkerType.getGrid();
				// После того как загрузили данные, надо снова выбрать предыдущую запись
				if ((thas.Wizard.params.XmlMarkerType_id) && (isData))
				{
					GridAtRecord(grid, 'XmlMarkerType_id', thas.Wizard.params.XmlMarkerType_id, 2);
				}
				thas.refreshWindowTitle();
			}
		});
		

		
		this.XmlDataSelectTypeStore = new Ext.data.SimpleStore({
			key: 'XmlDataSelectType_id',
			fields:
			[
				{name: 'XmlDataSelectType_id', type: 'int'},
				{name: 'XmlDataSelectType_SysNick', type: 'string'},
				{name: 'XmlDataSelectType_Name', type: 'string'}
			],
			data: [
				[1,'first',lang['pervyiy_dokument']], 
				[2,'last',lang['posledniy_dokument']],
				[3,'firstused','Первый документ, в котором создан раздел'], 
				[4,'lastused','Последний документ, в котором создан раздел']
			]
		});

		this.SqlOrderTypeStore = new Ext.data.SimpleStore({
			key: 'SqlOrderType_id',
			fields:
			[
				{name: 'SqlOrderType_id', type: 'int'},
                {name: 'SqlOrderType_SysNick', type: 'string'},
				{name: 'SqlOrderType_Name', type: 'string'}
			],
			data: [
				[1,'asc',lang['v_pryamoy_hronologicheskoy_posledovatelnosti']],
				[2,'desc',lang['v_obratnoy_hronologicheskoy_posledovatelnosti']]
			]
		});

		/**
		 * Панель формы ввода
		 */
		this.Wizard.InputParams = new Ext.FormPanel(
		{
            id: 'dmwWizard_XmlMarkerForm',
			region: 'center',
			border: false,
			frame: true,
			autoHeight: true,
            layout: 'form',
            labelAlign: 'right',
            labelWidth: 150,
            bbar:
			[{
				xtype: 'button',
				text: lang['vstavit'],
				iconCls: 'ok16',
				handler: function()
				{
					thas.applyParams();
				}
			},
			{
				xtype: 'button',
				text: lang['sbros'],
				iconCls: 'reset16',
				handler: function()
				{
					// Очистка полей
					thas.Wizard.InputParams.doReset(true);
				}
			},
			{
				xtype: 'tbseparator'
			}
			],
			items: [{
				fieldLabel: lang['tip_dokumenta'],
				width: 300,
                listWidth: 300,
				comboSubject: 'XmlType',
				typeCode: 'int',
				hiddenName: 'XmlType_id',
				autoLoad: true,
				xtype: 'swcommonsprcombo'
			},{
				fieldLabel: lang['tip_uslugi'],
				width: 300,
                listWidth: 300,
				comboSubject: 'UslugaComplexAttributeType',
				typeCode: 'int',
				hiddenName: 'UslugaComplexAttributeType_id',
				allowSysNick: true,
				autoLoad: true,
				xtype: 'swcommonsprcombo'
			},{
				fieldLabel: lang['uroven'],
				width: 300,
                listWidth: 300,
				valueField: 'XmlDataLevel_id',
				displayField: 'XmlDataLevel_Name',
				hiddenName: 'XmlDataLevel_id',
				comboData: [
					[1, 'section', 'Текущее движение/посещение'],
					[2, 'evn', 'Случай лечения (ТАП/КВС)'],
					[3, 'priem', 'Движение в приемном']
				],
				comboFields: [
					{name: 'XmlDataLevel_id', type:'int'},
					{name: 'XmlDataLevel_SysNick', type:'string'},
					{name: 'XmlDataLevel_Name', type:'string'}
				],
				value: 1,
				allowBlank: false,
				xtype: 'swstoreinconfigcombo'
			},{
				fieldLabel: lang['imya_razdela'],
				width: 300,
                listWidth: 300,
				comboSubject: 'XmlDataSection',
				typeCode: 'int',
				hiddenName: 'XmlDataSection_id',
				allowSysNick: true,
				autoLoad: true,
				xtype: 'swcommonsprcombo'
			},{
				fieldLabel: lang['poryadkovyiy_nomer_dokumenta'],
				width: 300,
                listWidth: 300,
				tabIndex: TABINDEX_ETSW+29,
				mode: 'local',
				value: 2,
				hiddenName: 'XmlDataSelectType_id',
				editable: false,
				triggerAction: 'all',
				displayField: 'XmlDataSelectType_Name',
				valueField: 'XmlDataSelectType_id',
				tpl: '<tpl for="."><div class="x-combo-list-item">{XmlDataSelectType_Name}</div></tpl>',
				store: this.XmlDataSelectTypeStore,
				xtype: 'combo'
			},{
				fieldLabel: lang['poryadok_sortirovki'],
                width: 300,
                listWidth: 300,
				tabIndex: TABINDEX_ETSW+31,
				mode: 'local',
				value: 1,
				hiddenName: 'SqlOrderType_id',
				editable: false,
				triggerAction: 'all',
				displayField: 'SqlOrderType_Name',
				valueField: 'SqlOrderType_id',
				tpl: '<tpl for="."><div class="x-combo-list-item">{SqlOrderType_Name}</div></tpl>',
				store: this.SqlOrderTypeStore,
				xtype: 'combo'
            },{
                width: 300,
                fieldLabel: lang['spisok_kodov_gost-2011'],
                name: 'code2011list',
                value: '',
                xtype: 'textfield'
			}],
            fieldForFocus: 'XmlType_id',
			/**
			 * Очистка
			 */
			doReset: function(allow_set_focus)
			{
                var base_form = thas.Wizard.InputParams.getForm();
                base_form.reset();
                if (allow_set_focus && this.fieldForFocus) {
                    base_form.findField(this.fieldForFocus).focus(true, 250);
                }
			}
		});
		
		/**
		 * Главная панель
		 */
		this.Wizard.Panel = new Ext.Panel(
		{
			region: 'center',
			layout: 'card',
			border: false,
			activeItem: 0, 
			defaults: 
			{
				border:false
			},
			items: 
			[
				this.Wizard.SelectXmlMarkerType,
				this.Wizard.InputParams
			]
		});
		
		Ext.apply(this, 
		{
			layout: 'border',
			items: 
			[
				this.Wizard.Panel
			],
			buttons: 
			[
			{
				iconCls: 'arrow-previous16',
				text: lang['nazad'],
				handler: function() { thas.prevStep(); }
			},
			{
				iconCls: 'home16',
				text: lang['v_nachalo'],
				handler: function() { 
					thas.setStep('SelectXmlMarkerType'); 
				}
			},
			{
				text: '-'
			}, 
			{
				text: BTN_FRMHELP,
				iconCls: 'help16',
				handler: function() {
					ShowHelp(thas.winTitle);
				},
				tabIndex: TABINDEX_DMW + 18
			}, 
			{
				iconCls: 'cancel16',
				text: BTN_FRMCLOSE,
				handler: function() { thas.hide(); }
			}]
		});
		
		sw.Promed.swXmlMarkerEditWindow.superclass.initComponent.apply(this, arguments);
	}
});