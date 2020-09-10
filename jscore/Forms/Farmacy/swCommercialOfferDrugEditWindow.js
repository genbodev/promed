/**
 * swCommercialOfferDrugEditWindow - окно редактирования cписка медикаментов коммерческих предложений
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Farmacy
 * @access       public
 * @copyright    Copyright (c) 2013 Swan Ltd.
 * @author       Salakhov R.
 * @version      06.2013
 * @comment
 */
sw.Promed.swCommercialOfferDrugEditWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: false,
	title: langs('Медикамент коммерческого предложения'),
	layout: 'border',
	id: 'CommercialOfferDrugEditWindow',
	modal: true,
	shim: false,
	width: 750,
	height: 180,
	resizable: false,
	maximizable: false,
	maximized: false,
	doSave:  function() {
        var index = null;
		var wnd = this;

		if ( !this.form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					wnd.findById('CommercialOfferDrugEditForm').getFirstInvalidEl().focus(true);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
		var params = this.form.getValues();

		/*if (Ext.isEmpty(this.dpf_combo.getValue())) {
			sw.swMsg.alert(langs('Ошибка'), langs('Не указан медикамент'), function() { wnd.hide(); });
			return false;
		}*/

        var gu_combo = this.form.findField('GoodsUnit_id');
        params.GoodsUnit_Name = '';
        if (!Ext.isEmpty(params.GoodsUnit_id)) {
            index = gu_combo.getStore().findBy(function(rec) { return rec.get('GoodsUnit_id') == params.GoodsUnit_id; });
            if (index == -1) {
                return false;
            }
            params.GoodsUnit_Name = gu_combo.getStore().getAt(index).get('GoodsUnit_Name');
        }

        var f_params = new Object();
        if (!Ext.isEmpty(params.DrugPrepFas_id)) {
            f_params.DrugPrepFas_id = params.DrugPrepFas_id;
        } else if (!Ext.isEmpty(params.Drug_id)) {
            f_params.Drug_id = params.Drug_id;
        }
        f_params.Org_id = params.Org_did;

		Ext.Ajax.request({ //дописываем недостающие данные
			params: f_params,
			failure:function () {
				wnd.callback(params);
				wnd.hide();
			},
			success: function (response) {
				var result = Ext.util.JSON.decode(response.responseText);
				if (result[0]) {
					Ext.apply(params, result[0]);
				}
				wnd.callback(params);
				wnd.hide();
			},
			url:'/?c=CommercialOffer&m=getCommercialOfferDrugContext'
		});
		return true;
	},
	setDisabled: function(disable) {
		var wnd = this;
		var form = this.form;
		var field_arr = [
			'Drug_id',
			'CommercialOfferDrug_Price'
		];

		for (var i in field_arr) if (form.findField(field_arr[i])) {
			if (disable) {
                form.findField(field_arr[i]).disable();
            } else {
                form.findField(field_arr[i]).enable();
            }
		}

		if (disable) {
			wnd.buttons[0].disable();
		} else {
			wnd.buttons[0].enable();
		}
	},
    setFieldLabel: function(field, label) {
        var el = field.el.dom.parentNode.parentNode;
        if(el.children[0].tagName.toLowerCase() === 'label') {
            el.children[0].innerHTML = label+':';
        } else if (el.parentNode.children[0].tagName.toLowerCase() === 'label') {
            el.parentNode.children[0].innerHTML = label+':';
        }
        if (field.fieldLabel) {
            field.fieldLabel = label;
        }
    },
    setRegionSettings: function() { //отображение полей с учетом особенностей региона
        var org_type = getGlobalOptions().orgtype;
        var region_nick = getRegionNick();
        var sk_farm_fieldset_show = false;
        var window_height = 180;

        this.drug_combo.getStore().baseParams.Reg_Num = null;

        if (region_nick == 'kz') {
            this.dpf_combo.allowBlank = true;
            this.dpf_combo.hideContainer();
            this.drug_combo.allowBlank = false;
            this.setFieldLabel(this.drug_combo, langs('Медикамент'));
            this.form.findField('CommercialOfferDrug_Price').decimalPrecision = 4; //4 знака после запятой в цене для Казахстана
            this.form.findField('GoodsUnit_id').showContainer();

            if (this.isSkFarm && this.action != 'add') { //если поставщик в коммерческом предложении – СК Фармация
                sk_farm_fieldset_show = true;
                if (!Ext.isEmpty(this.params.CommercialOfferDrug_RegCertName)) {
                    this.drug_combo.getStore().baseParams.Reg_Num = this.params.CommercialOfferDrug_RegCertName;
                }
            }
        } else {
            this.dpf_combo.allowBlank = false;
            this.dpf_combo.showContainer();
            this.drug_combo.allowBlank = true;
            this.setFieldLabel(this.drug_combo, langs('Упаковка'));
            this.form.findField('CommercialOfferDrug_Price').decimalPrecision = 2;
            this.form.findField('GoodsUnit_id').hideContainer();
        }

        if (sk_farm_fieldset_show) {
            window_height += 332;
            this.form.findField('KM_Drug_MnnName').ownerCt.show();
        } else {
            this.form.findField('KM_Drug_MnnName').ownerCt.hide();
        }

        this.setHeight(window_height);
        this.doLayout();
    },
	show: function() {
		var wnd = this;
		sw.Promed.swCommercialOfferDrugEditWindow.superclass.show.apply(this, arguments);
		this.action = '';
		this.callback = Ext.emptyFn;
		this.CommercialOfferDrug_id = null;
        this.isSkFarm = false; //признак того что поставщик - СК Фармация
        this.params = new Object();

		if ( !arguments[0] ) {
			sw.swMsg.alert(langs('Ошибка'), langs('Не указаны входные данные'), function() { wnd.hide(); });
			return false;
		}
		if ( arguments[0].action ) {
			this.action = arguments[0].action;
		}
		if ( arguments[0].callback && typeof arguments[0].callback == 'function' ) {
			this.callback = arguments[0].callback;
		}
		if ( arguments[0].owner ) {
			this.owner = arguments[0].owner;
		}
		if ( arguments[0].CommercialOfferDrug_id ) {
			this.CommercialOfferDrug_id = arguments[0].CommercialOfferDrug_id;
		}
		if ( arguments[0].params ) {
            Ext.apply(this.params, arguments[0].params);
            if( arguments[0].params.isSkFarm ) {
                this.isSkFarm = true;
            }
        }

		this.form.reset();
        this.dpf_combo.fullReset();
        this.drug_combo.fullReset();

		this.setTitle(langs('Медикамент коммерческого предложения'));
        this.setRegionSettings();

		var loadMask = new Ext.LoadMask(this.form.getEl(), {msg:langs('Загрузка...')});
		loadMask.show();
		switch (arguments[0].action) {
			case 'add':
				this.setDisabled(false);
				this.setTitle(this.title+langs(': Добавление'));
                if (arguments[0].params) {
                    this.form.setValues(arguments[0].params);
                }
				loadMask.hide();
				break;
			case 'edit':
			case 'view':
				this.setTitle(this.title+(this.action == 'view' ? langs(': Просмотр') : langs(': Редактирование')));
				this.setDisabled(this.action == 'view');
				if (arguments[0].params) {
					this.form.setValues(arguments[0].params);

					var drug_id = arguments[0].params.Drug_id;
					var drugprepfas_id = arguments[0].params.DrugPrepFas_id;

                    wnd.dpf_combo.setValueById(drugprepfas_id);
                    wnd.drug_combo.setValueById(drug_id);
				}
				loadMask.hide();
				break;
		}
		return true;
	},
	initComponent: function() {
		var wnd = this;

        wnd.dpf_combo = new sw.Promed.SwCustomOwnerCombo({
            anchor: '100%',
            name: 'DrugPrepFas_id',
            fieldLabel: langs('Медикамент'),
            hiddenName: 'DrugPrepFas_id',
            displayField: 'DrugPrep_Name',
            valueField: 'DrugPrepFas_id',
            allowBlank: false,
            store: new Ext.data.SimpleStore({
                autoLoad: false,
                fields: [
                    { name: 'DrugPrepFas_id', mapping: 'DrugPrepFas_id' },
                    { name: 'DrugPrep_Name', mapping: 'DrugPrep_Name' },
                    { name: 'DrugPrepFasCode_Code', mapping: 'DrugPrepFasCode_Code' }
                ],
                key: 'DrugPrepFas_id',
                sortInfo: { field: 'DrugPrep_Name' },
                url:'/?c=CommercialOffer&m=loadRlsDrugPrepFasCombo'
            }),
            ownerWindow: wnd,
            childrenList: ['Drug_id'],
            tpl: new Ext.XTemplate(
                '<tpl for="."><div class="x-combo-list-item">',
                '<table style="width:100%;border: 0;"><td style="width:80%;"><h3>{DrugPrep_Name}</h3></td><td style="width:20%;"></td></tr></table>',
                '</div></tpl>'
            )
        });

        wnd.drug_combo = new sw.Promed.SwCustomRemoteCombo({
            anchor: '100%',
            name: 'Drug_id',
            fieldLabel: langs('Упаковка'),
            hiddenName: 'Drug_id',
            displayField: 'Drug_Name',
            valueField: 'Drug_id',
            allowBlank: true,
            store: new Ext.data.SimpleStore({
                autoLoad: false,
                fields: [
                    { name: 'Drug_id', mapping: 'Drug_id' },
                    { name: 'Drug_Name', mapping: 'Drug_Name' },
                    { name: 'DrugNomen_Code', mapping: 'DrugNomen_Code' }
                ],
                key: 'Drug_id',
                sortInfo: { field: 'Drug_Name' },
                url:'/?c=CommercialOffer&m=loadRlsDrugCombo'
            }),
            tpl: new Ext.XTemplate(
                '<tpl for="."><div class="x-combo-list-item">',
                '<table style="width:100%;border: 0;"><td style="width:80%;"><h3>{Drug_Name}</h3></td><td style="width:20%;"></td></tr></table>',
                '</div></tpl>'
            ),
            initComponent: function() {
                sw.Promed.SwCustomRemoteCombo.superclass.initComponent.apply(this, arguments);

                this.getStore().baseParams = new Object();
                this.defaultBaseParams = new Object();

                this.trigger3Class = 'x-form-search-trigger';
                this.triggerConfig = {
                    tag: 'span',
                    cls: 'x-form-twin-triggers',
                    cn: [
                        {tag: "img", src: Ext.BLANK_IMAGE_URL, cls: "x-form-trigger " + this.trigger1Class},
                        {tag: "img", src: Ext.BLANK_IMAGE_URL, cls: "x-form-trigger " + this.trigger2Class},
                        {tag: "img", src: Ext.BLANK_IMAGE_URL, cls: "x-form-trigger " + this.trigger3Class}
                    ]
                };

                this.formList = new sw.Promed.swListSearchWindow({
                    title: langs('Поиск медикамента'),
                    id: 'code_DrugWinSearch',
                    object: 'Drug',
                    store: this.getStore(),
                    useBaseParams: false
                });
            },
            onTrigger3Click: function() {
                if (!this.disabled) {
                    var combo = this;

                    this.formList.show({
                        onSelect: function(data) {
                            combo.setValueById(data['Drug_id']);
                        }
                    });
                }
                return false;
            }
        });

		var form = new Ext.Panel({
			autoScroll: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			border: false,
			frame: true,
			region: 'center',
			labelAlign: 'right',
			items: [{
				xtype: 'form',
				autoHeight: true,
				id: 'CommercialOfferDrugEditForm',
				style: 'margin-bottom: 0.5em;',
				bodyStyle:'background:#DFE8F6;padding:5px;',
				border: true,
				labelWidth: 100,
				collapsible: true,
				region: 'north',
				items: [{
					name: 'CommercialOfferDrug_id',
					xtype: 'hidden',
					value: 0
				},{
					name: 'Org_did',
					xtype: 'hidden'
				},
				wnd.dpf_combo,
				wnd.drug_combo,
                {
                    layout: 'form',
                    items: [{
                        xtype: 'swcommonsprcombo',
                        comboSubject: 'GoodsUnit',
                        fieldLabel: 'Ед. учета',
                        hiddenName: 'GoodsUnit_id',
                        width: 350
                    }]
                }, {
					fieldLabel: langs('Цена'),
					name: 'CommercialOfferDrug_Price',
					xtype: 'numberfield',
					allowDecimals: true,
					allowNegative: false,
					allowBlank: false,
					width: 350
				}, {
                    xtype: 'fieldset',
                    title: 'Данные прайса СК Фармация',
                    autoHeight: true,
                    labelWidth: 210,
                    style: 'margin-top: 1em;',
                    items: [{
                        xtype: 'textfield',
                        name: 'KM_Drug_MnnName',
                        fieldLabel: 'Тип медикамента',
                        anchor: '100%',
                        disabled: true
                    }, {
                        xtype: 'textfield',
                        name: 'CommercialOfferDrug_MnnName',
                        fieldLabel: 'Непатентованное наименование',
                        anchor: '100%',
                        disabled: true
                    }, {
                        xtype: 'textfield',
                        name: 'CommercialOfferDrug_PharmName',
                        fieldLabel: 'Наименование',
                        anchor: '100%',
                        disabled: true
                    }, {
                        xtype: 'textfield',
                        name: 'CommercialOfferDrug_Form',
                        fieldLabel: 'Форма выпуска',
                        anchor: '100%',
                        disabled: true
                    }, {
                        xtype: 'textfield',
                        name: 'CommercialOfferDrug_Package',
                        fieldLabel: 'Кол-во в уп.',
                        anchor: '100%',
                        disabled: true
                    }, {
                        xtype: 'textfield',
                        name: 'CommercialOfferDrug_UnitName',
                        fieldLabel: 'Ед.изм.',
                        anchor: '100%',
                        disabled: true
                    }, {
                        xtype: 'textfield',
                        name: 'CommercialOfferDrug_RegCertName',
                        fieldLabel: 'РУ',
                        anchor: '100%',
                        disabled: true
                    }, {
                        xtype: 'textfield',
                        name: 'CommercialOfferDrug_ProdName',
                        fieldLabel: 'Производитель',
                        anchor: '100%',
                        disabled: true
                    }, {
                        xtype: 'textfield',
                        name: 'CommercialOfferDrug_ProdCountry',
                        fieldLabel: 'Срана производства',
                        anchor: '100%',
                        disabled: true
                    }, {
                        xtype: 'textfield',
                        name: 'CommercialOfferDrug_PriceDetail',
                        fieldLabel: 'Код СКП',
                        anchor: '100%',
                        disabled: true
                    }, {
                        xtype: 'textfield',
                        name: 'CommercialOfferDrug_PrevPriceDetail',
                        fieldLabel: 'Код СКП (пред.)',
                        anchor: '100%',
                        disabled: true
                    }, {
                        xtype: 'textfield',
                        name: 'CommercialOfferDrug_updDT',
                        fieldLabel: 'Дата обновления',
                        anchor: '100%',
                        disabled: true
                    }]
                }]
			}],
			reader: new Ext.data.JsonReader({
				success: Ext.emptyFn
			}, [
				{name: 'CommercialOfferDrug_id'},
				{name: 'CommercialOffer_id'},
				{name: 'Drug_id'},
				{name: 'CommercialOfferDrug_Price'}
			])
		});
		Ext.apply(this, {
			layout: 'border',
			buttons: [
				{
					handler: function() {
						this.ownerCt.doSave();
					},
					iconCls: 'save16',
					text: BTN_FRMSAVE
				},
				{
					text: '-'
				},
				HelpButton(this, 0),
				{
					handler: function() {
						this.ownerCt.hide();
					},
					iconCls: 'cancel16',
					text: BTN_FRMCANCEL
				}
			],
			items:[form]
		});
		sw.Promed.swCommercialOfferDrugEditWindow.superclass.initComponent.apply(this, arguments);
		this.form = this.findById('CommercialOfferDrugEditForm').getForm();
	}
});