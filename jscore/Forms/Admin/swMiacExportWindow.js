/**
* swMiacExportWindow - окно выгрузки в МИАЦ.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2009 - 2011 Swan Ltd.
* @author       Пшеницын Иван
* @version      28.03.2011
* @comment      Префикс для id компонентов MEW (MiacExportWindow)
*
*/

sw.Promed.swMiacExportWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: true,
	buttonAlign: 'left',
	modal: true,
	closable: true,
	closeAction: 'hide',
	draggable: true,
	id: 'MiacExportWindow',
	title: lang['vyigruzka_dannyih_v_miats'],
	width: 400,
	layout: 'form',
	resizable: false,
	onHide: Ext.emptyFn,
	plain: true,
	initComponent: function()
	{
		this.Panel = new Ext.form.FormPanel(
		{
			autoHeight: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			border: false,
			frame: true,
			id: 'MiacExportPanel',
			labelAlign: 'right',
			labelWidth: 50,
			layout: 'form',
			items: [{
				fieldLabel: lang['period'],
				id: 'MEW_InsDate_Range',
				name: 'InsDate_Range',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', true) ],
				tabIndex: 10500,
				width: 170,
				xtype: 'daterangefield'
			}, {
				checked: true,
				id: 'MEW_Marker_R',
				fieldLabel: 'R',
				tabIndex: 10502,
				xtype: 'checkbox'
			}, {
				checked: true,
				id: 'MEW_Marker_U',
				fieldLabel: 'U',
				tabIndex: 10503,
				xtype: 'checkbox'
			}, {
				checked: true,
				id: 'MEW_Marker_D',
				fieldLabel: 'D',
				tabIndex: 10504,
				xtype: 'checkbox'
			}],
			validateOnBlur: true
		});
		
		Ext.apply(this, 
		{
			autoHeight: true,
			buttons: [
			{
				id: 'rdfOk',
				handler: function() 
				{
					this.ownerCt.doExport();
				},
				iconCls: 'refresh16',
				tabIndex: 10505,
				text: lang['vyigruzit']
			}, 
			{
				text: '-'
			},
			HelpButton(this),
			{
				handler: function() 
				{
					this.ownerCt.hide();
				},
				iconCls: 'cancel16',
				tabIndex: 10506,
				onTabElement: 'rdfOk',
				text: BTN_FRMCANCEL
			}],
			items: [this.Panel]
		});
		sw.Promed.swMiacExportWindow.superclass.initComponent.apply(this, arguments);
	},
	
	listeners: 
	{
		'hide': function() 
		{
			this.onHide();
		}
	},
	doExport: function()
	{
		if ( !Ext.getCmp('MEW_InsDate_Range').isValid(true) )
		{
			Ext.Msg.alert("Ошибка", "Неверное задан диапазон дат");
			return false;
		}
		
		var params = {
			range1: Ext.util.Format.date(Ext.getCmp('MEW_InsDate_Range').getValue1(), 'd.m.Y'),
			range2: Ext.util.Format.date(Ext.getCmp('MEW_InsDate_Range').getValue2(), 'd.m.Y'),
			marker_r: Ext.getCmp('MEW_Marker_R').getValue() === true ? 1 : 0,
			marker_u: Ext.getCmp('MEW_Marker_U').getValue() === true ? 1 : 0,
			marker_d: Ext.getCmp('MEW_Marker_D').getValue() === true ? 1 : 0
		};

		if ( params['marker_r'] == 0 && params['marker_u'] == 0 && params['marker_d'] == 0 )
		{
			Ext.Msg.alert("Ошибка", "Не выбран ни один файл для выгрузки.");
			return false;
		}
	
		var form = this;
		form.getLoadMask().show();
		Ext.Ajax.request(
		{
			url: form.formUrl,
			params: params,
			callback: function(options, success, response) 
			{
				form.getLoadMask().hide();
				if ( success )
				{
					var result = Ext.util.JSON.decode(response.responseText);

					if (
						(!result['marker_r_link'] || result['marker_r_link'] == '') &&
						(!result['marker_u_link'] || result['marker_u_link'] == '') &&
						(!result['marker_d_link'] || result['marker_d_link'] == '')
					)
						var files = lang['ne_sgenerirovano_ni_odnogo_fayla'];
					else
						var files = lang['sgenerirovanyi_faylyi'];

					if ( result['marker_r_link'] && result['marker_r_link'] != '' )
						files += '<br/>Рецепты: <a target="_blank" href="' + result['marker_r_link'] + '">' + result['marker_r_filename'] + '</a>';
					
					if ( result['marker_u_link'] && result['marker_u_link'] != '' )
						files += '<br/>Посещения: <a target="_blank" href="' + result['marker_u_link'] + '">' + result['marker_u_filename'] + '</a>';

					if ( result['marker_d_link'] && result['marker_d_link'] != '' )
						files += '<br/>Случаи нетрудоспособности: <a target="_blank" href="' + result['marker_d_link'] + '">' + result['marker_d_filename'] + '</a>';

					Ext.Msg.alert(lang['zagruzka_faylov'], files);
				}
				else 
				{
					var result = Ext.util.JSON.decode(response.responseText);
					Ext.Msg.alert(lang['oshibka'], result.Error_Msg);
				}
			}
		});
	},
	getLoadMask: function()
	{
		if (!this.loadMask)
		{
			this.loadMask = new Ext.LoadMask(Ext.get(this.id), { msg: lang['podojdite_idet_formirovanie'] });
		}
		return this.loadMask;
	},
	show: function() 
	{
		sw.Promed.swMiacExportWindow.superclass.show.apply(this, arguments);
		var form = this;
		
		form.onHide = Ext.emptyFn;
		Ext.getCmp('rdfOk').enable();

		var dt = new Date();
		// последний день предыдущего месяца
		var last_day = dt.getFirstDateOfMonth().add(Date.DAY, -1).clearTime();
		// первый день предыдущего месяца
		var first_day = last_day.getFirstDateOfMonth().clearTime();
		Ext.getCmp('MEW_InsDate_Range').setValue(Ext.util.Format.date(first_day, 'd.m.Y')+' - '+Ext.util.Format.date(last_day, 'd.m.Y'));

		Ext.getCmp('MEW_InsDate_Range').focus(true, 500);
		
		/*if ( !arguments[0] )
		{
			sw.swMsg.show(
			{
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.ERROR,
				msg: lang['oshibka_otkryitiya_formyi'] + form.id + lang['ne_ukazanyi_neobhodimyie_vhodnyie_parametryi'],
				title: lang['oshibka']
			});
			this.hide();
		}*/

		if (arguments[0] && arguments[0].url)
		{
			form.formUrl = arguments[0].url;
		}
		else 
		{
			form.formUrl = '/?c=MiacExport&m=getMiacExportFileLink';
		}
	}
});