/**
* swWorkDataEditWindow - окно редактирования/добавления износа.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2009-2014 Swan Ltd.
* @author       Samir Abakhri
* @version      24.05.2014
*/

sw.Promed.swWorkDataEditWindow = Ext.extend(sw.Promed.BaseForm, 
{
	action: null,
	autoHeight: true,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	draggable: true,
	split: true,
	width: 500,
	layout: 'form',
	id: 'WorkDataEditWindow',
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
	resizable: false,
    doSave: function()
	{
        var _this = this,
		    form = this.findById('WorkDataEditForm'),
            base_form = form.getForm(),
            data = {};

		if ( !form.getForm().isValid() )
		{
			sw.swMsg.show(
			{
				buttons: Ext.Msg.OK,
				fn: function()
				{
					form.getFirstInvalidEl().focus(true);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

        if ((base_form.findField('WorkData_WorkPeriod').getValue()).getDate() != 1) {
            base_form.findField('WorkData_WorkPeriod').focus(true);
            sw.swMsg.alert(lang['soobschenie'], lang['periodom_ekspluatatsii_doljno_byit_pervoe_chislo_mesyatsa_sohranenie_nevozmojno']);
            return false;
        }

        if (Ext.isEmpty(base_form.findField('WorkData_AvgUse').getValue())) {
            sw.swMsg.alert('Сообщение', 'Поле \'Среднее количество применений за период\' обязательно для заполнения. Сохранение невозможно.');
            return false;
        }

        if(!_this.evaluate()) {
            return false;
        }

        if  (!!_this.deniedWorkDataList) {
            for (var $i = 0;$i < _this.deniedWorkDataList.length; $i++ ) {
                if ((_this.deniedWorkDataList[$i] - base_form.findField('WorkData_WorkPeriod').getValue()) == 0) {
                    sw.swMsg.show(
                    {
                        buttons: Ext.Msg.OK,
                        icon: Ext.Msg.WARNING,
                        msg: lang['na_vvedennuyu_datu_perioda_uje_suschestvuet_zapis'],
                        title: ERR_INVFIELDS_TIT
                    });
                    return false;
                }
            }
        }

        data.deniedWorkDataData = {
            'WorkData_id': base_form.findField('WorkData_id').getValue(),
            'WorkData_WorkPeriod': base_form.findField('WorkData_WorkPeriod').getValue(),
            'WorkData_DayChange': base_form.findField('WorkData_DayChange').getValue(),
            'WorkData_CountUse': base_form.findField('WorkData_CountUse').getValue(),
            'WorkData_KolDay': base_form.findField('WorkData_KolDay').getValue(),
            'WorkData_AvgUse': base_form.findField('WorkData_AvgUse').getValue()
        };

        this.formStatus = 'edit';
        this.callback(data);
        this.hide();
		return true;
	},
	show: function() 
	{
		sw.Promed.swWorkDataEditWindow.superclass.show.apply(this, arguments);
		var _this = this;
		if (!arguments[0])
		{
			sw.swMsg.show(
			{
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
		this.findById('WorkDataEditForm').getForm().reset();
		this.callback = Ext.emptyFn;
		this.onHide = Ext.emptyFn;

		if (arguments[0].WorkData_id)
			this.WorkData_id = arguments[0].WorkData_id;
		else
			this.WorkData_id = null;

		if (arguments[0].deniedWorkDataList) {
            this.deniedWorkDataList = arguments[0].deniedWorkDataList;
        }
		else
			this.deniedWorkDataList = null;

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
		
		if (arguments[0].action) 
		{
			this.action = arguments[0].action;
		}
		else 
		{
			if ( ( this.WorkData_id ) && ( this.WorkData_id > 0 ) )
				this.action = "edit";
			else 
				this.action = "add";
		}
		
		var form = this.findById('WorkDataEditForm');
		form.getForm().setValues(arguments[0].formParams);

		var loadMask = new Ext.LoadMask(this.getEl(),{msg: LOAD_WAIT});
		loadMask.show();
		switch (this.action)
		{
			case 'add':
				this.setTitle(lang['ekspluatatsionnyie_dannyie_dobavlenie']);
				this.enableEdit(true);
				loadMask.hide();
				form.getForm().clearInvalid();
                _this.WorkDataEditForm.getForm().findField('WorkData_WorkPeriod').setValue(new Date(new Date().getFullYear(), new Date().getMonth(), 1));
				break;
			case 'edit':
				this.setTitle(lang['ekspluatatsionnyie_dannyie_redaktirovanie']);
				this.enableEdit(true);
				break;
			case 'view':
				this.setTitle(lang['ekspluatatsionnyie_dannyie_prosmotr']);
				this.enableEdit(false);
				break;
		}

        form.getForm().findField('WorkData_AvgUse').disable();

		loadMask.hide();
		if ( this.action != 'view' )
			form.getForm().findField('WorkData_WorkPeriod').focus(true, 100);
		else
			this.buttons[3].focus();
	},
	evaluate: function() {

        var base_form = this.WorkDataEditForm.getForm(),
            WorkData_CountUse = base_form.findField('WorkData_CountUse').getValue(),
            WorkData_KolDay = base_form.findField('WorkData_KolDay').getValue(),
            WorkData_DayChange = base_form.findField('WorkData_DayChange').getValue();

        if (WorkData_CountUse > 0 && WorkData_KolDay > 0 && WorkData_DayChange > 0) {
            base_form.findField('WorkData_AvgUse').setValue(Math.round( WorkData_CountUse/WorkData_KolDay/WorkData_DayChange * 100 ) / 100);
            return true;
        } else {
            sw.swMsg.alert(lang['soobschenie'], lang['dlya_rascheta_srednego_kolichestva_primeneniy_zapolnite_vse_polya_znachenimyami_bolshe_nulya']);
            return false;
        }
    },
	initComponent: function() 
	{
        var _this = this;
		// Форма с полями 
		this.WorkDataEditForm = new Ext.form.FormPanel(
		{
			autoHeight: true,
			bodyStyle: 'padding: 5px',
			border: false,
			buttonAlign: 'left',
			frame: true,
			id: 'WorkDataEditForm',
			labelAlign: 'right',
            labelWidth: 280,
			items: 
			[{
				name: 'WorkData_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'MedProductCard_id',
				value: 0,
				xtype: 'hidden'
			},{
                allowBlank: false,
                xtype: 'swdatefield',
                fieldLabel: lang['period_ekspluatatsii'],
                tooltip : lang['ukazyivaetsya_pervoe_chislo_mesyatsa'],
                format: 'd.m.Y',
                width: 100,
                name: 'WorkData_WorkPeriod',
                listeners:{
                    'change': function(oldValue, newValue, combo){
                        if (!Ext.isEmpty(newValue)){
                            if (newValue.getDate() != 1) {
                                _this.WorkDataEditForm.getForm().findField('WorkData_WorkPeriod').focus(true);
                                sw.swMsg.alert(lang['soobschenie'], lang['periodom_ekspluatatsii_doljno_byit_pervoe_chislo_mesyatsa']);
                                return false;
                            }
                        }
                    }
                },
                plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ]
            },{
                allowBlank: false,
                fieldLabel: lang['kolichestvo_smen_v_sutki'],
                name: 'WorkData_DayChange',
                width: 100,
                allowDecimals:true,
                xtype: 'numberfield'
            },{
                allowBlank: false,
                fieldLabel: lang['obschee_kolichestvo_primeneniy_za_period'],
                name: 'WorkData_CountUse',
                width: 100,
                allowDecimals:false,
                listeners: {
                    change: function(c, n, o) {
                        if (n.toString().indexOf('.') != -1){
                            _this.WorkDataEditForm.getForm().findField('WorkData_CountUse').setValue('');
                            sw.swMsg.alert(lang['vnimanie'], lang['kolichestvo_dney_doljno_byit_tselyim_chislom'], function(){
                                _this.WorkDataEditForm.getForm().findField('WorkData_CountUse').focus(500);
                            });
                        }
                    }
                },
                xtype: 'numberfield'
            },{
                allowBlank: false,
                fieldLabel: lang['kolichestvo_rabochih_dney_v_periode'],
                name: 'WorkData_KolDay',
                width: 100,
                allowDecimals:true,
                xtype: 'numberfield'
            },{
                border: false,
                layout: 'column',
                labelWidth: 280,
                items: [{
                    border: false,
                    columnWidth: .85,
                    layout: 'form',
                    items: [{
                        allowBlank: false,
                        fieldLabel: lang['srednee_kolichestvo_primeneniy_v_smenu'],
                        name: 'WorkData_AvgUse',
                        width: 100,
                        allowDecimals:true,
                        xtype: 'numberfield'
                    }]
                },{
                    border: false,
                    columnWidth: .10,
                    layout: 'form',
                    labelWidth: 10,
                    items: [{
                        text:'=',
                        tooltip:lang['raschet_srednego_kolichestva_primeneniy_v_smenu'],
                        handler:function () {
                            _this.evaluate();
                        },
                        id:_this.id + '_copyBtn',
                        xtype:'button'
                    }]
                }]
            }],
			keys: 
			[{
				alt: true,
				fn: function(inp, e) 
				{
					switch (e.getKey()) 
					{
						case Ext.EventObject.C:
							if (this.action != 'view') 
							{
								this.doSave(false);
							}
							break;
						case Ext.EventObject.J:
							this.hide();
							break;
					}
				},
				key: [ Ext.EventObject.C, Ext.EventObject.J ],
				scope: this,
				stopEvent: true
			}],
			reader: new Ext.data.JsonReader(
			{
				success: function() 
				{ 
					//
				}
			}, 
			[
				{ name: 'WorkData_id' },
				{ name: 'MedProductCard_id' },
				{ name: 'WorkData_KolDay' },
				{ name: 'WorkData_WorkPeriod' },
				{ name: 'WorkData_DayChange' },
				{ name: 'WorkData_CountUse' },
				{ name: 'WorkData_AvgUse' }
			]),
			url: '/?c=LpuPassport&m=saveWorkData'
		});
		Ext.apply(this, 
		{
			buttons: 
			[{
				handler: function() 
				{
					_this.doSave();
				},
				iconCls: 'save16',
				tabIndex: TABINDEX_ORSEW + 3,
				text: BTN_FRMSAVE
			}, 
			{
				text: '-'
			},
			HelpButton(this),
			{
				handler: function() 
				{
					_this.hide();
				},
				iconCls: 'cancel16',
				tabIndex: TABINDEX_ORSEW + 4,
				text: BTN_FRMCANCEL
			}],
			items: [this.WorkDataEditForm]
		});
		sw.Promed.swWorkDataEditWindow.superclass.initComponent.apply(this, arguments);
	}
});