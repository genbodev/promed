/**
 * swCompareECISMedStaffFact - окно экспорта направлений на МСЭ
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      	Mse
 * @access       	public
 * @copyright		Copyright (c) 2018 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			02.02.2018
 */
/*NO PARSE JSON*/

sw.Promed.swCompareECISMedStaffFact = Ext.extend(sw.Promed.BaseForm, {
	id: 'swCompareECISMedStaffFact',
	width: 470,
	autoHeight: true,
	modal: true,
	title: langs('Сравнение кадров ЕЦИС-ПроМед'),


	show: function() {
		var win = this;
		//хитрый костыль, призванный обходить декодирование запроса. который никак не хочет принимать html страницу
		win.FormPanel.getForm().errorReader = {
			read: function (response) {
				try{
					var res = Ext.decode(response.responseText);

					if(res.Error_Msg){
						sw.swMsg.alert(langs('Ошибка'), langs(res.Error_Msg));
					}

				}
				catch(error){
					return {success: true, errors: false, records: null};
				}
			}
		};

		var date = new Date();
		var firstDay = new Date(date.getFullYear(), date.getMonth(), 1);

		win.FormPanel.getForm().findField('compareDate').setValue(firstDay.format('d.m.Y'));

		sw.Promed.swCompareECISMedStaffFact.superclass.show.apply(this, arguments);
	},

	doCompare: function(win){
		win.FormPanel.getForm().submit(
			{
				callback: function(){
				},
				failure: function (result_form, action) {
				},
				success: function (result_form, action) {
					var newWin = window.open('', '_blank');
					newWin.document.write(action.response.responseText);
					newWin.document.title = "Сравнительный отчет";

					var style = newWin.document.createElement('style');
					newWin.document.head.appendChild(style);

					//генерим css
					var arrStyle = [];

					arrStyle.push("body {Font-family:'Times New Roman', Times, serif; font-size:10pt;}");
					arrStyle.push("p {margin:0 0 10px}");
					arrStyle.push("table {font-size:12pt; vertical-align: top;}");
					arrStyle.push("table.compare-table {border-collapse:collapse; border:1px solid #000;}");
					arrStyle.push("table.compare-table td {border:1px solid #000; text-align: center; font-size: 13px; padding: 10px;}");
					arrStyle.push("table.compare-table th {border:1px solid #000; text-align: center; font-size: 13px; padding: 10px;}");
					arrStyle.push("span {display:inline-block; width:30px}");

					arrStyle = arrStyle.join("\n");

					style.innerHTML = arrStyle;
				}
			}
		);


	},

	initComponent: function() {
		var wnd = this;

		wnd.FormPanel = new Ext.FormPanel({
			frame: true,
			autoHeight: true,
			labelAlign: 'right',
			labelWidth: 120,
			fileUpload: true,
			//id: 'compareMedStaffFact',
			bodyStyle: 'margin-top: 10px;',
			layout: 'form',
			url: '/?c=MedPersonal&m=compareMedStaffFactECISxml',
			items: [

				{
					xtype: 'fieldset',
					autoHeight: true,
					title: langs('Выберите параметры сравнения'),
					items: [
						{
							fieldLabel: langs('Дата вызова'),
							format: 'd.m.Y',
							name: 'compareDate',
							plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
							width: 100,
							allowBlank: false,
							//maxValue: new Date(),
							xtype: 'swdatefield',
							listeners: {

							}
						},
						{
							xtype: 'fileuploadfield',
							anchor: '95%',
							allowBlank: false,
							emptyText: 'Выберите Файл',
							fieldLabel: 'Файл ЕЦИС',
							name: 'compareEcisFile',
							allowedExtensions: ['xml']
						}
					]
				}
			]
		});

		Ext.apply(wnd,
			{
				buttons: [
					{
						handler: function () {
							wnd.doCompare(wnd);
						},
						iconCls: 'database-export16',
						text: langs('Сформировать')
					},
					{
						text: '-'
					},
					HelpButton(wnd),
					{
						handler: function()
						{
							wnd.hide();
						}.createDelegate(wnd),
						iconCls: 'cancel16',
						text: BTN_FRMCLOSE
					}
				],
				items: [
					wnd.FormPanel
				]
			});

		sw.Promed.swCompareECISMedStaffFact.superclass.initComponent.apply(wnd, arguments);
	}
});