/**
 * swEvnDirectionParamsSelectWindow - окно ввода параметров системного направления,
 * как частного случая электронного направления!
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      EvnDirection
 * @access       private
 * @copyright    Copyright (c) 2009-2013 Swan Ltd.
 * @author       A. Permyakov
 * @version      06.2013
 *
 * @class        sw.Promed.swEvnDirectionParamsSelectWindow
 * @extends      sw.Promed.BaseForm
 */
sw.Promed.swEvnDirectionParamsSelectWindow = Ext.extend(sw.Promed.BaseForm, {
    title: lang['vyibor_parametrov_napravleniya'],
	autoHeight: true,
	border: false,
	buttonAlign: 'right',
	closable: true,
	closeAction: 'hide',
	modal: true,
	plain: false,
	resizable: false,
    width: 650,
	/**
	 * Обработчик выбора параметров
	 * override
	 */
	onSelect: Ext.emptyFn,
	/**
	 * Обработчик клика по кнопке "Выбрать"
	 */
	doSelect: function() {
        var thas = this;
        if ( !this.form.isValid() ) {
            sw.swMsg.show({
                buttons: Ext.Msg.OK,
                fn: function() {
                    thas.formPanel.getFirstInvalidEl().focus(true);
                },
                icon: Ext.Msg.WARNING,
                msg: ERR_INVFIELDS_MSG,
                title: ERR_INVFIELDS_TIT
            });
            return false;
        }

        this.direction.DirType_id = this.form.findField('DirType_id').getValue();

        this.hide();
        this.onSelect(this.direction);
        return true;
	},
	/**
	 * Конструктор
	 */
	initComponent: function() {
        var thas = this;
		
		this.formPanel = new sw.Promed.FormPanel({
			autoHeight: true,
			border: false,
			frame: true,
			labelWidth: 150,
			layout: 'form',
			style: 'padding: 3px',
			items: [{
                fieldLabel: lang['tip_napravleniya'],
                allowBlank: false,
                comboSubject: 'DirType',
                typeCode: 'int',
                width: 420,
                xtype: 'swcommonsprcombo'
            }],
            reader: new Ext.data.JsonReader({
                success: Ext.emptyFn
            }, [
                { name: 'DirType_id' }
            ])
		});
		
		Ext.apply(this, {
			buttons: [{
				handler : function() {
                    thas.doSelect();
				},
				iconCls : 'ok16',
				text: lang['vyibrat']
			}, {
				text: '-'
			},
			HelpButton(this, -1),
			{
				handler: function() {
                    thas.hide();
				},
				iconCls : 'cancel16',
				onShiftTabAction: function () {
                    thas.buttons[0].focus();
				},
				onTabAction: function () {
                    thas.form.findField('DirType_id').focus(true);
				},
				text: BTN_FRMCANCEL
			}],
			items: [ 
				this.formPanel
			]
		});
		sw.Promed.swEvnDirectionParamsSelectWindow.superclass.initComponent.apply(this, arguments);

        this.form = this.formPanel.getForm();
	}, //end initComponent()
	/**
	 * Отображение окна
	 */
	show: function() {
		sw.Promed.swEvnDirectionParamsSelectWindow.superclass.show.apply(this, arguments);
        var thas = this;
        this.form.reset();

		if ( !arguments[0] || !arguments[0].direction ) {
			sw.swMsg.alert(lang['oshibka'], lang['otsutstvuyut_neobhodimyie_parametryi'], function() { thas.hide(); } );
			return false;
		}

        this.direction = arguments[0].direction;
        this.onHide = arguments[0].onHide || Ext.emptyFn;
		this.onSelect = arguments[0].onSelect || Ext.emptyFn;

        var dir_type_id;
        if (this.direction.DirType_id) {
            dir_type_id = parseInt(this.direction.DirType_id);
        }
        var dir_type_combo = this.form.findField('DirType_id');
        dir_type_combo.getStore().removeAll();
        var filters = {where: ''};
        switch (true)
        {
            case (this.direction.LpuUnitType_SysNick == 'parka'):
                //тип направления: На обследование
                filters.where = 'where DirType_id = 2';
                dir_type_id = 2;
                break;
            case (this.direction.MedServiceType_SysNick && this.direction.MedServiceType_SysNick.inlist(['vk','mse']) ):
                // тип направления: Направление на ВК или МСЭ
                filters.where = 'where DirType_id = 9';
                dir_type_id = 9;
                break;
            case (this.direction.LpuUnitType_SysNick == 'polka'):
                //тип направления: На консультацию
                //filter_flag = record.get('DirType_id').toString().inlist(['2','3','4','6','7','9']);
                filters.where = 'where DirType_id in (2,3,4,6,7,9)';
                dir_type_id = 3;
                break;
            case (this.direction.LpuUnitType_SysNick.inlist(['stac','dstac','hstac','pstac'])):
                //filter_flag = record.get('DirType_id').toString().inlist(['1','4','5','6']);
                filters.where = 'where DirType_id in (1,4,5,6)';
                break;
        }
        dir_type_combo.getStore().load({
            params: filters,
            callback: function(){
                var dir_type_rec = null;
                if (dir_type_id) {
                    dir_type_rec = dir_type_combo.getStore().getById(dir_type_id);
                }
                if (dir_type_rec) {
                    dir_type_combo.setValue(dir_type_id);
                } else {
                    dir_type_combo.setValue(null);
                    dir_type_combo.setRawValue('');
                }
                dir_type_combo.focus(true, 100);
            }
        });

		this.doLayout();
		this.syncSize();
        return true;
	} //end show()
});