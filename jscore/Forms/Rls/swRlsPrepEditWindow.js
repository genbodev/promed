/**
* Справочник РЛС: Форма выбора типа добавляемого лек.средства
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      All
* @access       public
* @autor		Dmitry Storozhev aka nekto_O
* @copyright    Copyright (c) 2011 Swan Ltd.
* @version      14.11.2011
*/

sw.Promed.swRlsPrepEditWindow = Ext.extend(sw.Promed.BaseForm,
{
	title: '',
	modal: true,
	maximizable: true,
	maximized: true,
	shim: false,
	plain: false,
	resizable: false,
	onSelect: Ext.emptyFn,
	layout: 'form',
	buttonAlign: "right",
	objectName: 'swRlsPrepEditWindow',
	closeAction: 'hide',
	id: 'swRlsPrepEditWindow',
	objectSrc: '/jscore/Forms/Rls/swRlsPrepEditWindow.js',
	buttons: [
		{
			handler: function()
			{
				this.ownerCt.doSave();
			},
			iconCls: 'save16',
			text: lang['sohranit']
		},
		'-',
		{
			text      : lang['otmena'],
			tabIndex  : -1,
			tooltip   : lang['otmena'],
			iconCls   : 'cancel16',
			handler   : function()
			{
				this.ownerCt.hide();
			}
		}
	],
	
	listeners: {
		hide: function(w){
			w.disableFields(false);
			w.CommonForm.getForm().reset();
			w.CommonForm2.getForm().reset();
			w.ImagePanel.getForm().reset();
			w.AutorPanel.getForm().reset();
			w.CommonForm.hide();
			w.CommonForm2.hide();
			w.overwriteTpl();
			w.Image.setTitle(' ');
			w.ImagePanel.find('xtype', 'fileuploadfield')[0].file_uploaded = false;
			w.ActmattersGrid.getGrid().getStore().removeAll();
			w.MkbGrid.getGrid().getStore().removeAll();
			
			var pBar = w.ImagePanel.find('name', 'progressbar')[0];
			pBar.updateProgress(0, lang['zagrujeno_0%'], false);
			pBar.setVisible(false);

			if(typeof w.callback == 'function'){
				w.callback();
			}
		}
	},
	
	show: function()
	{
		sw.Promed.swRlsPrepEditWindow.superclass.show.apply(this, arguments);
		
		if(!arguments[0] || !arguments[0].PrepType_id || !arguments[0].action){
			sw.swMsg.alert(lang['oshibka'], lang['nevernyie_parametryi']);
			this.hide();
			return false;
		}

		this.callback = null;
		if(arguments[0].callback){
			this.callback = arguments[0].callback;
		}
		
		this.action = arguments[0].action;
		this.PrepType_id = arguments[0].PrepType_id;
		this.defineTitle();
		
		var win = this;
		var b_f = this.getBaseForm();
		
		switch(this.PrepType_id){
			case 1:
			case 2:
				this.CommonForm.setVisible(true);
				b_f.findField('TN_DF_LIMP').setValue(1);
				b_f.findField('TRADENAMES_DRUGFORMS').setValue(1);
				this.PrepDataPanel.collapse();
				this.ActPanel.collapse();
				this.MkbPanel.collapse();
			break;
			case 3:
				this.CommonForm2.setVisible(true);
			break;
		}
		this.doLayout();

		if(this.action != 'add') {
			if(!arguments[0].Nomen_id){
				sw.swMsg.alert(lang['oshibka'], lang['nevernyie_parametryi_formyi']);
				this.hide();
				return false;
			}
			var lm = this.getLoadMask(lang['zagruzka_dannyih']);
			lm.show();
			b_f.load({
				url: '/?c=Rls&m=getPrep',
				params: {
					Nomen_id: arguments[0].Nomen_id,
					PrepType_id: arguments[0].PrepType_id
				},
				failure: function(){
					lm.hide();
					//
				},
				success: function(frm, r){
					lm.hide();
					var resp_obj = Ext.util.JSON.decode(r.response.responseText)[0];
					var f1, f2, f3, f4, f5, f6, f7, f8, f9, f10;
					
					// Лекарственная форма
					if(b_f.findField('CLSDRUGFORMS_ID')){
						f1 = b_f.findField('CLSDRUGFORMS_ID');
						win.setFieldsValue(f1, 'CLSDRUGFORMS');
					}
					
					// Вид ЛФ
					if(b_f.findField('vidLF')) {
						var vlfcombo = b_f.findField('vidLF');
						if( resp_obj.DFMASSID != '' ) {
							vlfcombo.setValue(1);
							vlfcombo.fireEvent('select', vlfcombo, vlfcombo.getStore().getAt(0), 0 );
						} else if ( resp_obj.DFCONCID != '' ) {
							vlfcombo.setValue(2);
							vlfcombo.fireEvent('select', vlfcombo, vlfcombo.getStore().getAt(1), 1 );
						} else if ( resp_obj.DFACTID != '' ) {
							vlfcombo.setValue(3);
							vlfcombo.fireEvent('select', vlfcombo, vlfcombo.getStore().getAt(2), 2 );
						}
					}

					// Название характеристики ЛФ
					if(b_f.findField('DFCHARID')){
						f2 = b_f.findField('DFCHARID');
						win.setFieldsValue(f2, 'DRUGFORMCHARS');
					}
					
					// Название перв. упаковки
					if(b_f.findField('NOMEN_PPACKID')){
						f3 = b_f.findField('NOMEN_PPACKID');
						win.setFieldsValue(f3, 'DRUGPACK');
					}
					
					// Ед. изм. первичной упаковки
					if(b_f.findField('measure')) {
						var meascombo = b_f.findField('measure');
						if(resp_obj.NOMEN_PPACKVOLUME != null) {
							meascombo.setValue(1);
							meascombo.fireEvent('select', meascombo, meascombo.getStore().getAt(0), 0 );
						} else if(resp_obj.NOMEN_PPACKMASS != null) {
							meascombo.setValue(2);
							meascombo.fireEvent('select', meascombo, meascombo.getStore().getAt(1), 1 );
						}
					}
					
					// Назв. комплекта к перв. упаковке
					if(b_f.findField('NOMEN_SETID')){
						f4 = b_f.findField('NOMEN_SETID');
						win.setFieldsValue(f4, 'DRUGSET');
					}
					
					// Название втор. упаковки
					if(b_f.findField('NOMEN_UPACKID')){
						f5 = b_f.findField('NOMEN_UPACKID');
						win.setFieldsValue(f5, 'DRUGPACK');
					}
					
					// Название трет. упаковки
					if(b_f.findField('NOMEN_SPACKID')){
						f6 = b_f.findField('NOMEN_SPACKID');
						win.setFieldsValue(f6, 'DRUGPACK');
					}

					// Производитель
					if(b_f.findField('FIRMS_ID')){
						f7 = b_f.findField('FIRMS_ID');
						win.setFieldsValue(f7, 'FIRMS');
					}
					
					// Классификация АТХ
					if(b_f.findField('CLSATC_ID')){
						f8 = b_f.findField('CLSATC_ID');
						win.setFieldsValue(f8, 'CLSATC');
					}
						
					// Классификация МКБ-10
					/*if(b_f.findField('CLSIIC_ID')){
						f9 = b_f.findField('CLSIIC_ID');
						win.setFieldsValue(f9, 'CLSIIC');
					}*/
					win.MkbGrid.getGrid().getStore().loadData(resp_obj.clsiics, true);
					
					// Фармакологическая группа
					if(b_f.findField('CLSPHARMAGROUP_ID')){
						f10 = b_f.findField('CLSPHARMAGROUP_ID');
						win.setFieldsValue(f10, 'CLSPHARMAGROUP');
					}
					
					// Добавляем ДВ в грид
					win.ActmattersGrid.getGrid().getStore().loadData(resp_obj.actmatters, true);
					
					// Покажем картинку (если есть)
					win.overwriteTpl(resp_obj);
					
					// Панель "Автор"
					resp_obj.autor_date_ins = Ext.util.Format.date(resp_obj.autor_date_ins, 'd.m.Y H:i:s');
					resp_obj.autor_date_upd = Ext.util.Format.date(resp_obj.autor_date_upd, 'd.m.Y H:i:s');
					win.AutorPanel.getForm().setValues(resp_obj);
					
					if(win.action == 'edit')
						win.disableformFields(true);
					
					if(win.action == 'view')
						win.disableFields(true);
					
					b_f.findField('PREPFULLNAME').disable();
				}
			});
		} else {
			var resp_obj = {};
			resp_obj.autor_orgname_ins = getGlobalOptions().lpu_name;
			resp_obj.autor_orgname_upd = resp_obj.autor_orgname_ins;
			
			resp_obj.autor_username_ins = (getGlobalOptions().CurMedPersonal_FIO) ? getGlobalOptions().CurMedPersonal_FIO : '-';
			resp_obj.autor_username_upd = resp_obj.autor_username_ins;
			
			resp_obj.autor_date_ins = Ext.util.Format.date(Date(), 'd.m.Y H:i:s');
			resp_obj.autor_date_upd = resp_obj.autor_date_ins;
			
			this.AutorPanel.getForm().setValues(resp_obj);
			b_f.findField('PREPFULLNAME').disable();
			b_f.isValid();
		}
        this.MainPanel1.findById('MP_CLSDRUGFORMS_ID').getStore().load();
	},
	
	setFieldsValue: function(field, obj)
	{
		if(field.getValue() == null || field.getValue() == '' || field.getValue() == 0){
			field.reset();
			return false;
		}
		field.getStore().baseParams = {
			object: obj,
			stringfield: field.displayField
		};
		field.getStore().baseParams[obj+'_ID'] = field.getValue();
		field.getStore().load({
			callback: function(){
				field.setValue(field.getValue());
				field.fireEvent('change');
			}
		});
	},
	
	defineTitle: function()
	{
		var title = lang['spravochnik_medikamentov'];
		switch(this.action){
			case 'add':
				title += lang['_dobavlenie'];
			break;
			case 'edit':
				title += lang['_redaktirovanie'];
			break;
			case 'view':
				title += lang['_prosmotr'];
			break;
		}
		
		switch(this.PrepType_id){
			case 1:
				title += (' ' + lang['lekarstvennogo_sredstva_rls']);
			break;
			case 2:
				title += (' ' + lang['lekarstvennogo_sredstva_ekstemporalnogo']);
			break;
			case 3:
				title += (' ' + lang['meditsinskogo_tovara']);
			break;
		}
		this.setTitle(title);
	},
	
	doSave: function()
	{
		var win = this;
		var grid = this.ActmattersGrid.ViewGridPanel;
		var params = {},
			form = null;
		if(this.PrepType_id == 2){
			form = this.CommonForm.getForm();
		} else if (this.PrepType_id == 3){
			form = this.CommonForm2.getForm();
		}
		
		if(!form.isValid()){
			sw.swMsg.alert(lang['oshibka'], lang['ne_zapolnenyi_obyazatelnyie_polya']);
			return false;
		}
		
		// Собираем все ДВ, добавленные в грид
		if(this.PrepType_id == 2 && grid.getStore().getCount()>0){
			var actarr = [];
			grid.getStore().each(function(rec){
				actarr.push(rec.get('ACTMATTERS_ID'));
			});
			params.ACTMATTERS = actarr.join('|');
		}
		// то же самое с диагнозами МКБ-10
		if( this.PrepType_id == 2 && this.MkbGrid.getGrid().getStore().getCount() > 0 ) {
			var mkbs = [];
			this.MkbGrid.getGrid().getStore().each(function(r) {
				mkbs.push(r.get('CLSIIC_ID'));
			});
			params.CLSIICS = escape(mkbs.join('|'));
		}
		
		params.PrepType_id = this.PrepType_id;
		
		// Если файл загружен
		if(this.ImagePanel.find('xtype', 'fileuploadfield')[0].file_uploaded){
			params.file_uploaded = 1;
		}
		
		var lm = this.getLoadMask(lang['sohranenie']);
		lm.show();
		form.submit({
			params: params,
			success: function(f, r){
				lm.hide();
				win.hide();
			},
			failure: function(){
				lm.hide();
			}
		});
	},
	
	addActmatterInGrid: function()
	{
		var grid = this.ActmattersGrid;
		var combo = this.CommonForm.getForm().findField('ACTMATTERS_ID');
		if(!grid.formList){
			grid.formList = new sw.Promed.swListSearchWindow({
				title: lang['deystvuyuschee_veschestvo_poisk'],
				id: 'Actmatters_SearchWindow',
				object: 'rls.Actmatters',
				prefix: 'actmatters',
				useBaseParams: true,
				store: new Ext.data.Store({
					autoLoad: false,
					baseParams: {
						object: 'ACTMATTERS',
						stringfield: 'RUSNAME'
					},
					reader: new Ext.data.JsonReader({
						id: 'ACTMATTERS_ID'
					}, [{
						mapping: 'ACTMATTERS_ID',
						name: 'ACTMATTERS_ID',
						type: 'int'
					},{
						mapping: 'RUSNAME',
						name: 'RUSNAME',
						type: 'string'
					}]),
					url: '/?c=Rls&m=getDataForComboStore'
				})
			});
		}
		grid.formList.show({
			onSelect: function(data){
				combo.setValue(data.ACTMATTERS_ID);
				var idx = combo.getStore().find('ACTMATTERS_ID', new RegExp( '^' + combo.getValue() + '$'));
				var rec = combo.getStore().getAt(idx);
				if (grid.ViewGridPanel.getStore().find('ACTMATTERS_ID', new RegExp('^' + rec.get('ACTMATTERS_ID') + '$')) > -1)
					return false;
				grid.ViewGridPanel.getStore().loadData([{
					ACTMATTERS_ID: rec.get('ACTMATTERS_ID'),
					RUSNAME: rec.get('RUSNAME'),
					LATNAME: rec.get('LATNAME')
				}], true);
				grid.ViewGridPanel.getView().refresh();
			}
		});
	},
	
	deleteActmatterInGrid: function()
	{
		var grid = this.ActmattersGrid.ViewGridPanel;
		if (!grid.getSelectionModel().getSelected())
			return false;
		
		var delIdx = grid.getStore().find('ACTMATTERS_ID',	new RegExp('^' + grid.getSelectionModel().getSelected().get('ACTMATTERS_ID') + '$'));
		if (delIdx >= 0)
			grid.getStore().removeAt(delIdx);
		grid.getView().refresh();
	},
	
	overwriteTpl: function(obj)	{
		if(!obj){
			var obj = {};
			obj.file_url = '';
		}
		this.Image.tpl = new Ext.Template(this.ImgTpl);
		this.Image.tpl.overwrite(this.Image.body, obj);
	},
	
	showSteer: function(name, show)
	{
		var steer = this.find('name', name)[0];
		if(show){
			steer.setVisible(true);
			setTimeout(function(){ this.showSteer(name, false); }.createDelegate(this), 10000);
		} else {
			steer.setVisible(false);
		}
	},
	
	disableformFields: function(y)
	{
		var form = (this.PrepType_id == 2) ? this.CommonForm : this.CommonForm2,
			b_f = form.getForm(),
			formFields = form.formFields;
		for(var j=0; j<formFields.length; j++){
			if(y) b_f.findField(formFields[j]).disable();
			else b_f.findField(formFields[j]).enable();
		}
	},
	
	disableFields: function(isView) {
		this.findBy(function(field){
			if(field.disable && field.xtype && !field.xtype.inlist(['panel', 'fieldset'])){
				if(isView) field.disable();
				else field.enable();
			}
		});
		
		this.ActmattersGrid.ViewActions.action_add.setDisabled(isView);
		this.ActmattersGrid.ViewActions.action_delete.setDisabled(isView);
		this.MkbGrid.ViewActions.action_add.setDisabled(isView);
		this.MkbGrid.ViewActions.action_delete.setDisabled(isView);
		this.buttons[0].setVisible(!isView);
	},
	
	getXHR: function () {
		var xmlhttp;
		try {
			xmlhttp = new ActiveXObject("Msxml2.XMLHTTP");
		} 
		catch (e) {
			try {
				xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
			} 
			catch (E) {
				xmlhttp = false;
			}
		}
		if (!xmlhttp && typeof XMLHttpRequest != 'undefined') {
			xmlhttp = new XMLHttpRequest();
		}
		return xmlhttp;
	},
	
	setUploadProgress: function( pg ) {
		var pBar = this.ImagePanel.find('name', 'progressbar')[0];
		if( !pBar.isVisible() )
			pBar.setVisible(true);
		pBar.updateProgress(pg/100, lang['zagrujeno']+pg+'%', true);
	},
	
	// Тупо для теста загрузки с пом-ю FileAPI интересножблеать=)
	loadFileWithFileAPI: function( cb )
	{
		var field = this.ImagePanel.find('xtype', 'fileuploadfield')[0];
		try {
			// Исходный файл
			var file = field.fileInput.dom.files[0];
			if( file.size > 2 * 1024 * 1024 ) {
				sw.swMsg.alert(lang['oshibka'], lang['nelzya_zagruzit_fayl_obyemom_bolee_2mb']);
				return true;
			}
			
			if ( typeof FileReader == 'function' ) {
				var reader = new FileReader();
				
				// После того как reader прочитал файлик
				reader.onload = function(e) {
					var xhr = this.getXHR();
					
					xhr.upload.onprogress = function(e) { //onprogress
						if( e.lengthComputable ) {
							this.setUploadProgress(Math.floor((e.loaded/e.total)*100));
						}
					}.createDelegate(this);
					xhr.upload.onload = function(e) {
						this.setUploadProgress(100);
					}.createDelegate(this);
					
					xhr.onreadystatechange = function () {
						if (xhr.readyState == 4) {
							if(xhr.status == 200) {
								cb.call(this, this.ImagePanel.getForm(), { response: xhr } );
							} else {
								return false;
							}
						}
					}.createDelegate(this);
					
					xhr.open('POST', this.ImagePanel.url, true);
					var bdy = "xxxxxxxxx"; // граница
					// формируем заголовок запроса
					xhr.setRequestHeader('Content-Type', 'multipart/form-data, boundary='+bdy);
					xhr.setRequestHeader('Cache-Control', 'no-cache');
					// формируем тело запроса
					var body = "--" + bdy + "\r\n";
					body += "Content-Disposition: form-data; name='file'; filename='" + unescape( encodeURIComponent( file.name ) ) + "'\r\n";
					body += "Content-Type: application/octet-stream\r\n\r\n";
					body += reader.result + "\r\n";
					body += "--" + bdy + "--";
					
					if( xhr.sendAsBinary ) { // для FF
						xhr.sendAsBinary(body);
					} else {
						var ords = Array.prototype.map.call(body, function(x) {
							return x.charCodeAt(0) & 0xff;
						}),
						ui8a = new Uint8Array(ords);
						xhr.send(ui8a.buffer);
					}
				}.createDelegate(this);
				this.setUploadProgress(0);
				reader.readAsBinaryString(file);
				return true;
			} else {
				return false;
			}
		} catch (E) {
			// HTML5 FileAPI не поддерживается :(
			//log('exception: '+E);
			return false;
		}
	},
	
	getBaseForm: function() {
		return this['CommonForm'+(this.PrepType_id.inlist([1,2]) ? '' : '2')].getForm();
	},
	
	getStringValue: function(f) {
		return f['get' + (/id/ig.test(f.hiddenName || f.name) ? 'Raw' : '') + 'Value']();
	},
	
	setPrepFullName: function() {
		var form = this.getBaseForm(),
			field = form.findField('PREPFULLNAME'),
			v = '';
		
		var fields = [
			'TRADENAMES_NAME'
			,'CLSDRUGFORMS_ID'
			,'DFMASS + DFMASSID'
			,'DFCONC + DFCONCID'
			,'DFACT + DFACTID'
			,'DRUGDOSE'
			,'DFSIZE + DFSIZEID'
			,'DFCHARID'
			,'NOMEN_PPACKMASS + NOMEN_PPACKMASSUNID'
			,'NOMEN_PPACKVOLUME + NOMEN_PPACKCUBUNID'
			,'NOMEN_UPACKINSPACK'
			,'NOMEN_PPACKID'
			,'NOMEN_UPACKID'
			,'NOMEN_SPACKID'
			,'FIRMS_ID'
			,'NOMEN_EANCODE'
			,'REGCERT_REGNUM'
		];
		for(var i=0; i<fields.length; i++) {
			if(form.findField(fields[i])) {
				// Имеем дело с обычным полем
				v += this.getStringValue(form.findField(fields[i]));
			} else if(/\+/g.test(fields[i])) {
				// Имеем дело с составным полем
				var ms = fields[i].match(/(\w+)[\s\+]+(\w+)/i);
				if(ms.length == 3) {
					for(var j=0; j<ms.length; j++) {
						if( form.findField(ms[j]) ) {
							v += this.getStringValue(form.findField(ms[j]));
						}
					}
				}
			}
			if( v != '' && i < fields.length-1 && v.charAt(v.length-2)+v.charAt(v.length-1) != ', ' ) {
				v += ', ';
			}
		}
		if( v.charAt(v.length-2)+v.charAt(v.length-1) == ', ' )
			v = v.slice(0, v.length-2);
		
		field.setValue(v);
	},
	
	addMkb: function() {
		var grid = this.MkbGrid;
		
		if( !grid.formList ) {
			grid.formList = new sw.Promed.swListSearchWindow({
				title: lang['mkb-10_poisk'],
				id: grid.id + '_SearchWindow',
				object: 'rls.CLSIIC',
				prefix: 'clsiic',
				useBaseParams: true,
				
				store: new Ext.data.Store({
					autoLoad: false,
					baseParams: {
						object: 'CLSIIC',
						stringfield: 'NAME'
					},
					reader: new Ext.data.JsonReader({
						id: 'CLSIIC_ID'
					}, [{
						mapping: 'CLSIIC_ID',
						name: 'CLSIIC_ID',
						type: 'int'
					},{
						mapping: 'NAME',
						name: 'NAME',
						type: 'string'
					}]),
					url: '/?c=Rls&m=getDataForComboStore'
				})
			
			});
		}
		
		grid.formList.show({
			onSelect: function(data) {
				if( grid.getGrid().getStore().find('CLSIIC_ID', new RegExp('^' + data.CLSIIC_ID + '$')) > -1 )
					return false;
				
				grid.getGrid().getStore().loadData([data], true);
				grid.ViewGridPanel.getView().refresh();
			}
		});
	},
	
	deleteMkb: function() {
		var grid = this.MkbGrid.getGrid(),
			record = grid.getSelectionModel().getSelected();
		if( !record ) return false;
		grid.getStore().remove(record);
	},
	
	initComponent: function()
	{
		var cur_win = this;
	
		this.inT = function(){
			var ts = this.trigger.select('.x-form-trigger', true);
			//this.wrap.setStyle('overflow', 'hidden');
			var triggerField = this;
			ts.each(function(t, all, index){
				t.hide = function(){
					var w = triggerField.wrap.getWidth();
					this.dom.style.display = 'none';
					triggerField.el.setWidth(w-triggerField.trigger.getWidth());
				};
				t.show = function(){
					var w = triggerField.wrap.getWidth();
					this.dom.style.display = '';
					triggerField.el.setWidth(w-triggerField.trigger.getWidth());
				};
				var triggerIndex = 'Trigger'+(index+1);
				if(this['hide'+triggerIndex]){
					t.dom.style.display = 'none';
				}
				t.on("click", this['on'+triggerIndex+'Click'], this, {preventDefault:true});
				t.addClassOnOver('x-form-trigger-over');
				t.addClassOnClick('x-form-trigger-click');
			}, this);
			this.triggers = ts.elements;
		};
	
		this.onTrigger1Click = function(){
			if(this.isExpanded()){
				this.collapse();
				return false;
			}
			if(this.getRawValue() != ''){
				if(this.getStore().getCount()>0){
					this.focus(true);
					this.expand();
                    this.restrictHeight();
				}
			} else {
				this.focus(true);
			}
		};
		
		this.dQ = function(q, forceAll) {
			var combo = this;
			//if(q.length<1) return false;
			combo.fireEvent('beforequery', combo);
			combo.getStore().baseParams[combo.displayField] = q;
			combo.getStore().load();
		};
		
		this.steerMsg = '<p style="border: 1px solid #000; color: red; padding: 3px;">'+
			lang['vnimatelno_otnesites_k_zapolneniyu_etogo_polya_poskolku_posle_sohraneniya_v_rejime_redaktirovaniya_ono_budet_nedostuno'];
		
		this.MainPanel1 = new sw.Promed.Panel({
			title: lang['osnovnaya'],
			defaults: {
				labelAlign: 'right',
				border: false
			},
			items: [
				{
					layout: 'form',
					width: 600,
					labelWidth: 200,
					items: [
						{
							xtype: 'hidden',
							name: 'Prep_id'
						}, {
							xtype: 'hidden',
							name: 'TRADENAMES_ID'
						}, {
							xtype: 'hidden',
							name: 'LATINNAMES_ID'
						}, {
							xtype: 'hidden',
							name: 'REGCERT_ID'
						}, {
							xtype: 'hidden',
							name: 'DRUGLIFETIME_ID'
						}, {
							xtype: 'hidden',
							name: 'NOMEN_ID'
						}, {
							xtype: 'hidden',
							name: 'DESCRIPTIONS_ID'
						}, {
							xtype: 'hidden',
							name: 'IDENT_WIND_STR_id'
						}, {
							xtype: 'textarea',
							anchor: '100%',
							allowBlank: false,
							grow: true,
							fieldLabel: lang['otobrajaemoe_nazvanie'],
							name: 'PREPFULLNAME'
						}, {
							xtype: 'textfield',
							anchor: '100%',
							allowBlank: false,
							listeners: {
								/*focus: function(field){
									if(this.action == 'add'){
										this.showSteer('TRADENAMES_msg', true);
									}
								}.createDelegate(this),
								blur: function(){
									if(this.action == 'add'){
										this.showSteer('TRADENAMES_msg', false);
									}
								}.createDelegate(this)
								*/
								change: this.setPrepFullName.createDelegate(this)
							},
							fieldLabel: lang['torgovoe_nazvanie'],
							name: 'TRADENAMES_NAME'
						},
						{
							hidden: true,
							border: false,
							bodyStyle: 'margin: 0px 0px 5px 205px;',
							html: cur_win.steerMsg,
							name: 'TRADENAMES_msg'
						},
						{
							xtype: 'textfield',
							anchor: '100%',
							/*listeners: {
								focus: function(field){
									if(this.action == 'add'){
										this.showSteer('LATINNAMES_msg', true);
									}
								}.createDelegate(this)
							},*/
							name: 'LATINNAMES_NAME',
							fieldLabel: lang['latinskoe_nazvanie']
						},
						{
							hidden: true,
							border: false,
							bodyStyle: 'margin: 0px 0px 5px 205px;',
							html: cur_win.steerMsg,
							name: 'LATINNAMES_msg'
						},
						/*{
							xtype: 'swbaselocalcombo',
							anchor: '100%',
							store: new Ext.data.Store({
								autoLoad: false,
								listeners: {
									load: function(s) {
										var c = this.CommonForm.getForm().findField('TRADENAMES_ID');
										if(s.getCount() == 0) {
											c.reset();
											return false;
										}
									}.createDelegate(this)
								},
								baseParams: {
									object: 'TRADENAMES'
								},
								reader: new Ext.data.JsonReader({
									id: 'TRADENAMES_ID'
								}, [{
									mapping: 'TRADENAMES_ID',
									name: 'TRADENAMES_ID',
									type: 'int'
								},{
									mapping: 'INAME',
									name: 'INAME',
									type: 'string'
								}]),
								url: '/?c=Rls&m=getDataForComboStore'
							}),
							initTrigger: this.inT,
							triggerConfig:	{
								tag:'span', cls:'x-form-twin-triggers', cn:[
								{tag: "img", src: Ext.BLANK_IMAGE_URL, cls: "x-form-trigger x-form-select-trigger"},
								{tag: "img", src: Ext.BLANK_IMAGE_URL, cls: "x-form-trigger x-form-search-trigger"}
							]},
							onTrigger1Click: this.onTrigger1Click,
							onTrigger2Click: function(){
								var c = this;
								if(!this.formList){
									this.getStore().baseParams.stringfield = this.displayField;
									this.formList = new sw.Promed.swListSearchWindow({
										title: lang['poisk_preparata_po_torgovomu_nazvaniyu'],
										id: 'TRADENAMES_SearchWindow',
										object: 'rls.TRADENAMES',
										useBaseParams: true,
										store: this.getStore()
									});
								}
								this.formList.show({
									onSelect: function(data){
										c.getStore().removeAll();
										c.focus(true);
										c.getStore().load({
											params: {
												TRADENAMES_ID: data[c.hiddenName]
											},
											callback: function(){
												c.setValue(data[c.hiddenName]);
												c.collapse();
												c.focus(true, 100);
											}
										});
									}
								});
							},
							doQuery: function(q, forceAll) {
								var combo = this;
								if(q.length<=2) return false;
								combo.fireEvent('beforequery', combo);
								var where = combo.displayField+' like \''+q+'%\'';
								combo.getStore().baseParams.where = where;
								combo.getStore().baseParams.stringfield = combo.displayField;
								combo.getStore().load();
							},
							hiddenName: 'TRADENAMES_ID',
							triggerAction: 'All',
							forceSelection: true,
							selectOnFocus: true,
							resizable: true,
							emptyText: lang['vvedite_torgovoe_nazvanie_preparata'],
							displayField: 'INAME',
							valueField: 'TRADENAMES_ID',
							fieldLabel: lang['torgovoe_nazvanie']
						}, {
							xtype: 'swbaselocalcombo',
							emptyText: lang['vvedite_latinskoe_nazvanie_preparata'],
							anchor: '100%',
							store: new Ext.data.Store({
								autoLoad: false,
								listeners: {
									load: function(s) {
										var c = this.CommonForm.getForm().findField('LATINNAMES_ID');
										if(s.getCount() == 0) {
											c.reset();
											return false;
										}
									}.createDelegate(this)
								},
								baseParams: {
									object: 'LATINNAMES'
								},
								reader: new Ext.data.JsonReader({
									id: 'LATINNAMES_ID'
								}, [{
									mapping: 'LATINNAMES_ID',
									name: 'LATINNAMES_ID',
									type: 'int'
								},{
									mapping: 'NAME',
									name: 'NAME',
									type: 'string'
								}]),
								url: '/?c=Rls&m=getDataForComboStore'
							}),
							doQuery: function(q, forceAll) {
								var combo = this;
								if(q.length<=2) return false;
								combo.fireEvent('beforequery', combo);
								var where = combo.displayField+' like \''+q+'%\'';
								combo.getStore().baseParams.where = where;
								combo.getStore().baseParams.stringfield = combo.displayField;
								combo.getStore().load();
							},
							hiddenName: 'LATINNAMES_ID',
							displayField: 'NAME',
							triggerAction: 'none',
							valueField: 'LATINNAMES_ID',
							fieldLabel: lang['latinskoe_nazvanie']
						},*/ {
							xtype: 'swbaselocalcombo',
							anchor: '100%',
							store: new Ext.data.Store({
								autoLoad: false,
								listeners: {
									load: function(s) {
										var c = this.CommonForm.getForm().findField('CLSDRUGFORMS_ID');
										if(s.getCount() == 0) {
											c.reset();
											return false;
										}
									}.createDelegate(this)
								},
								baseParams: {
									object: 'CLSDRUGFORMS',
									stringfield: 'FULLNAME'
								},
								reader: new Ext.data.JsonReader({
									id: 'CLSDRUGFORMS_ID'
								}, [{
									mapping: 'CLSDRUGFORMS_ID',
									name: 'CLSDRUGFORMS_ID',
									type: 'int'
								},{
									mapping: 'FULLNAME',
									name: 'FULLNAME',
									type: 'string'
								}]),
								url: '/?c=Rls&m=getDataForComboStore'
							}),
							initTrigger: this.inT,
							triggerConfig:	{
								tag:'span', cls:'x-form-twin-triggers', cn:[
								{tag: "img", src: Ext.BLANK_IMAGE_URL, cls: "x-form-trigger x-form-select-trigger"},
								{tag: "img", src: Ext.BLANK_IMAGE_URL, cls: "x-form-trigger x-form-search-trigger"}
							]},
							onTrigger1Click: this.onTrigger1Click,
							onTrigger2Click: function(){
								var c = this;
								if(!this.formList){
									this.getStore().baseParams.stringfield = this.displayField;
									this.formList = new sw.Promed.swListSearchWindow({
										title: lang['poisk_lekarstvennoy_formyi'],
										id: 'CLSDRUGFORMS_SearchWindow',
										object: 'rls.CLSDRUGFORMS',
										useBaseParams: true,
										store: this.getStore()
									});
								}
								this.formList.show({
									onSelect: function(data){
										//c.getStore().removeAll();
										c.focus(true);
										c.getStore().baseParams.CLSDRUGFORMS_ID = data[c.hiddenName];
										c.getStore().load({
											callback: function() {
												c.setValue(data[c.hiddenName]);
												c.fireEvent('select', c, c.getStore().getAt(0), 0);
												c.collapse();
												c.focus(true, 100);
											}
										});
									}
								});
							},
							listeners: {
								select: function(c, r, i) {
									if(c.oldValue == c.getValue())
										return false;
									c.oldValue = c.getValue();
									var form = this.CommonForm.getForm();
									form.findField('vidLF').reset();
									form.findField('DFCONC').reset();
									form.findField('DFCONCID').reset();									
									form.findField('DFMASS').reset();
									form.findField('DFMASSID').reset();
									form.findField('DFACT').reset();
									form.findField('DFACTID').reset();
									form.findField('DRUGDOSE').reset();
									form.findField('DRUGLIFETIME_TEXT').reset();
									form.findField('DFSIZE').reset();
									form.findField('DFSIZEID').reset();
									form.findField('DFCHARID').reset();
									form.findField('NOMEN_DRUGSINPPACK').reset();
									form.findField('NOMEN_PPACKID').reset();
									form.findField('NOMEN_PPACKVOLUME').reset();
									form.findField('NOMEN_PPACKCUBUNID').reset();
									form.findField('NOMEN_SETID').reset();
									form.findField('NOMEN_PPACKINUPACK').reset();
									form.findField('NOMEN_UPACKID').reset();
									form.findField('NOMEN_UPACKINSPACK').reset();
									form.findField('NOMEN_SPACKID').reset();
									form.findField('NOMEN_PPACKMASS').reset();
									form.findField('NOMEN_PPACKMASSUNID').reset();
									form.findField('measure').reset();
								}.createDelegate(this),
								change: this.setPrepFullName.createDelegate(this)
							},
							oldValue: '',
                            lastQuery: '',
							emptyText: lang['vvedite_nazvanie_lekarstvennoy_formyi'],
							doQuery: this.dQ,
							hiddenName: 'CLSDRUGFORMS_ID',
							allowBlank: false,
                            id: 'MP_CLSDRUGFORMS_ID',
							triggerAction: 'none',
							displayField: 'FULLNAME',
							valueField: 'CLSDRUGFORMS_ID',
							fieldLabel: lang['lekarstvennaya_forma']
						}, {
							xtype: 'swbaselocalcombo',
							allowBlank: false,
							editable: false,
							triggerAction: 'all',
							mode: 'local',
							listeners: {
								render: function(c){
									c.setValue(1);
								},
								select: function(c, r, i){
									switch(c.getValue()){
										case 1:
											this.find('name', 'MASSUNITS')[0].setVisible(true);
											this.find('name', 'CONCENUNITS')[0].setVisible(false);
											this.find('name', 'ACTUNITS')[0].setVisible(false);
										break;
										case 2:
											this.find('name', 'MASSUNITS')[0].setVisible(false);
											this.find('name', 'CONCENUNITS')[0].setVisible(true);
											this.find('name', 'ACTUNITS')[0].setVisible(false);
										break;
										case 3:
											this.find('name', 'MASSUNITS')[0].setVisible(false);
											this.find('name', 'CONCENUNITS')[0].setVisible(false);
											this.find('name', 'ACTUNITS')[0].setVisible(true);
										break;
									}
									this.syncSize();
									this.doLayout();
								}.createDelegate(this)
							},
							store: new Ext.data.Store({
								autoLoad: true,
								data: [ [1, lang['massa_lekarstvennoy_formyi']], [2, lang['obyemnaya_chast_kontsentratsiya_lekarstvennoy_formyi']], [3, lang['kolichestvo_edinits_deystviya_lekarstvennoy_formyi']] ],
								reader: new Ext.data.ArrayReader({
									idIndex: 0
								}, [
									{mapping: 0, name: 'id'},
									{mapping: 1, name: 'name'}
								])
							}),
							hiddenName: 'vidLF',
							displayField: 'name',
							valueField: 'id',
							anchor: '100%',
							fieldLabel: lang['vid_lf']
						}
					]
				}, {
					layout: 'column',
					width: 600,
					name: 'MASSUNITS',
					defaults: {
						border: false
					},
					items: [
						{
							layout: 'form',
							columnWidth: .5,
							labelWidth: 200,
							items: [
								{
									xtype: 'numberfield',
									anchor: '100%',
									decimalPrecision: 0,
									name: 'DFMASS',
									listeners: {
										change: this.setPrepFullName.createDelegate(this)
									},
									fieldLabel: lang['kol-vo_lf']
								}
							]
						}, {
							layout: 'form',
							labelWidth: 160,
							columnWidth: .5,
							items: [
								{
									xtype: 'swbaselocalcombo',
									anchor: '100%',
									store: new Ext.data.Store({
										autoLoad: true,
										baseParams: {
											object: 'MASSUNITS',
											stringfield: 'SHORTNAME',
											where: '1=1'
										},
										reader: new Ext.data.JsonReader({
											id: 'MASSUNITS_ID'
										}, [{
											mapping: 'MASSUNITS_ID',
											name: 'MASSUNITS_ID',
											type: 'int'
										},{
											mapping: 'SHORTNAME',
											name: 'SHORTNAME',
											type: 'string'
										}]),
										url: '/?c=Rls&m=getDataForComboStore'
									}),
									listeners: {
										change: this.setPrepFullName.createDelegate(this)
									},
									displayField: 'SHORTNAME',
									valueField: 'MASSUNITS_ID',
									hiddenName: 'DFMASSID',
									fieldLabel: lang['nazvanie_lf']
								}
							]
						}
					]
				}, {
					layout: 'column',
					width: 600,
					name: 'CONCENUNITS',
					hidden: true,
					defaults: {
						border: false
					},
					items: [
						{
							layout: 'form',
							columnWidth: .5,
							labelWidth: 200,
							items: [
								{
									xtype: 'numberfield',
									anchor: '100%',
									listeners: {
										change: this.setPrepFullName.createDelegate(this)
									},
									decimalPrecision: 0,
									name: 'DFCONC',
									fieldLabel: lang['kol-vo_lf']
								}
							]
						}, {
							layout: 'form',
							labelWidth: 160,
							columnWidth: .5,
							items: [
								{
									xtype: 'swbaselocalcombo',
									anchor: '100%',
									store: new Ext.data.Store({
										autoLoad: true,
										baseParams: {
											object: 'CONCENUNITS',
											stringfield: 'SHORTNAME',
											where: '1=1'
										},
										reader: new Ext.data.JsonReader({
											id: 'CONCENUNITS_ID'
										}, [{
											mapping: 'CONCENUNITS_ID',
											name: 'CONCENUNITS_ID',
											type: 'int'
										},{
											mapping: 'SHORTNAME',
											name: 'SHORTNAME',
											type: 'string'
										}]),
										url: '/?c=Rls&m=getDataForComboStore'
									}),
									listeners: {
										change: this.setPrepFullName.createDelegate(this)
									},
									displayField: 'SHORTNAME',
									valueField: 'CONCENUNITS_ID',
									hiddenName: 'DFCONCID',
									fieldLabel: lang['nazvanie_lf']
								}
							]
						}
					]
				}, {
					layout: 'column',
					width: 600,
					name: 'ACTUNITS',
					hidden: true,
					defaults: {
						border: false
					},
					items: [
						{
							layout: 'form',
							columnWidth: .5,
							labelWidth: 200,
							items: [
								{
									xtype: 'numberfield',
									anchor: '100%',
									decimalPrecision: 0,
									listeners: {
										change: this.setPrepFullName.createDelegate(this)
									},
									name: 'DFACT',
									fieldLabel: lang['kol-vo_lf']
								}
							]
						}, {
							layout: 'form',
							columnWidth: .5,
							labelWidth: 160,
							items: [
								{
									xtype: 'swbaselocalcombo',
									anchor: '100%',
									store: new Ext.data.Store({
										autoLoad: true,
										baseParams: {
											object: 'ACTUNITS',
											stringfield: 'SHORTNAME',
											where: '1=1'
										},
										reader: new Ext.data.JsonReader({
											id: 'ACTUNITS_ID'
										}, [{
											mapping: 'ACTUNITS_ID',
											name: 'ACTUNITS_ID',
											type: 'int'
										},{
											mapping: 'SHORTNAME',
											name: 'SHORTNAME',
											type: 'string'
										}]),
										url: '/?c=Rls&m=getDataForComboStore'
									}),
									listeners: {
										change: this.setPrepFullName.createDelegate(this)
									},
									displayField: 'SHORTNAME',
									valueField: 'ACTUNITS_ID',
									hiddenName: 'DFACTID',
									fieldLabel: lang['nazvanie_lf']
								}
							]
						}
					]
				}, {
					layout: 'column',
					width: 600,
					defaults: {
						border: false
					},
					items: [
						{
							layout: 'form',
							columnWidth: .5,
							labelWidth: 200,
							items: [
								{
									xtype: 'numberfield',
									anchor: '100%',
									name: 'DRUGDOSE',
									listeners: {
										change: this.setPrepFullName.createDelegate(this)
									},
									decimalPrecision: 0,
									fieldLabel: lang['kol-vo_doz_v_upakovke']
								}
							]
						}, {
							layout: 'form',
							labelWidth: 160,
							columnWidth: .5,
							items: [
								{
									xtype: 'textfield',
									anchor: '100%',
									allowBlank: false,
									name: 'DRUGLIFETIME_TEXT',
									fieldLabel: lang['srok_godnosti']
								}
							]
						}
					]
				}, {
					layout: 'column',
					width: 600,
					defaults: {
						border: false
					},
					items: [
						{
							layout: 'form',
							columnWidth: .5,
							labelWidth: 200,
							items: [
								{
									xtype: 'textfield',
									anchor: '100%',
									listeners: {
										change: function(f){
											var field = this.CommonForm.getForm().findField('DFSIZEID');
											field.setAllowBlank((f.getValue() == '')?true:false);
											field.validate();
											this.setPrepFullName();
										}.createDelegate(this)
									},
									name: 'DFSIZE',
									fieldLabel: lang['razmeryi_lf']
								}
							]
						}, {
							layout: 'form',
							labelWidth: 160,
							columnWidth: .5,
							items: [
								{
									xtype: 'swbaselocalcombo',
									anchor: '100%',
									store: new Ext.data.Store({
										autoLoad: true,
										baseParams: {
											object: 'SIZEUNITS',
											stringfield: 'SHORTNAME',
											where: '1=1'
										},
										reader: new Ext.data.JsonReader({
											id: 'SIZEUNITS_ID'
										}, [{
											mapping: 'SIZEUNITS_ID',
											name: 'SIZEUNITS_ID',
											type: 'int'
										},{
											mapping: 'SHORTNAME',
											name: 'SHORTNAME',
											type: 'string'
										}]),
										url: '/?c=Rls&m=getDataForComboStore'
									}),
									listeners: {
										change: this.setPrepFullName.createDelegate(this)
									},
									displayField: 'SHORTNAME',
									valueField: 'SIZEUNITS_ID',
									hiddenName: 'DFSIZEID',
									fieldLabel: lang['nazvanie_razmera_lf']
								}
							]
						}
					]
				}, {
					layout: 'form',
					labelWidth: 200,
					width: 600,
					items: [
						{
							xtype: 'swbaselocalcombo',
							emptyText: lang['vvedite_nazvanie_harakteristiki'],
							anchor: '100%',
							store: new Ext.data.Store({
								autoLoad: false,
								listeners: {
									load: function(s) {
										var c = this.CommonForm.getForm().findField('DFCHARID');
										if(s.getCount() == 0) {
											c.reset();
											return false;
										}
									}.createDelegate(this)
								},
								baseParams: {
									object: 'DRUGFORMCHARS'
								},
								reader: new Ext.data.JsonReader({
									id: 'DRUGFORMCHARS_ID'
								}, [{
									mapping: 'DRUGFORMCHARS_ID',
									name: 'DRUGFORMCHARS_ID',
									type: 'int'
								},{
									mapping: 'SHORTNAME',
									name: 'SHORTNAME',
									type: 'string'
								}]),
								url: '/?c=Rls&m=getDataForComboStore'
							}),
							doQuery: function(q, forceAll) {
								var combo = this;
								//if(q.length<1) return false;
								combo.fireEvent('beforequery', combo);
								var where = combo.displayField+' like \''+q+'%\'';
								combo.getStore().baseParams[combo.valueField] = null;
								combo.getStore().baseParams.where = where;
								combo.getStore().baseParams.stringfield = combo.displayField;
								combo.getStore().load();
							},
							listeners: {
								change: this.setPrepFullName.createDelegate(this)
							},
							hiddenName: 'DFCHARID',
							triggerAction: 'none',
							displayField: 'SHORTNAME',
							valueField: 'DRUGFORMCHARS_ID',
							fieldLabel: lang['nazvanie_harakteristiki_lf']
						}
					]
				}, {
					layout: 'column',
					width: 600,
					defaults: {
						border: false
					},
					items: [
						{
							layout: 'form',
							columnWidth: .5,
							labelWidth: 200,
							items: [
								{
									xtype: 'numberfield',
									anchor: '100%',
									allowDecimals: false,
									allowNegative: false,
									name: 'NOMEN_DRUGSINPPACK',
									fieldLabel: lang['kol-vo_prep_v_perv_upakovke']
								}
							]
						}, {
							layout: 'form',
							columnWidth: .5,
							labelWidth: 160,
							items: [
								{
									xtype: 'swbaselocalcombo',
									anchor: '100%',
									editable: false,
									store: new Ext.data.Store({
										autoLoad: false,
										listeners: {
											load: function(s) {
												var c = this.CommonForm.getForm().findField('NOMEN_PPACKID');
												if(s.getCount() == 0) {
													c.reset();
													return false;
												}
											}.createDelegate(this)
										},
										baseParams: {
											object: 'DRUGPACK'
										},
										reader: new Ext.data.JsonReader({
											id: 'DRUGPACK_ID'
										}, [{
											mapping: 'DRUGPACK_ID',
											name: 'DRUGPACK_ID',
											type: 'int'
										},{
											mapping: 'FULLNAME',
											name: 'FULLNAME',
											type: 'string'
										}]),
										url: '/?c=Rls&m=getDataForComboStore'
									}),
									doQuery: function(q, forceAll) {
										var combo = this;
										//if(q.length<1) return false;
										combo.fireEvent('beforequery', combo);
										var where = combo.displayField+' like \''+q+'%\'';
										combo.getStore().baseParams[combo.valueField] = null;
										combo.getStore().baseParams.where = where;
										combo.getStore().baseParams.stringfield = combo.displayField;
										combo.getStore().load();
									},
									listeners: {
										change: this.setPrepFullName.createDelegate(this)
									},
									hiddenName: 'NOMEN_PPACKID',
									triggerAction: 'none',
									displayField: 'FULLNAME',
									listWidth: 200,
									valueField: 'DRUGPACK_ID',
									fieldLabel: lang['nazvanie_perv_upakovki']
								}
							]
						}
					]
				}, {
					layout: 'form',
					width: 600,
					labelWidth: 200,
					items: [
						{
							xtype: 'swbaselocalcombo',
							anchor: '100%',
							editable: false,
							allowBlank: false,
							listeners: {
								render: function(c){
									c.setValue(1);
								},
								select: function(c, r, i){
									switch(c.getValue()){
										case 1:
											this.CommonForm.find('name', 'CUBICUNITS')[0].setVisible(true);
											this.CommonForm.find('name', 'MASSUNITS2')[0].setVisible(false);
										break;
										case 2:
											this.CommonForm.find('name', 'CUBICUNITS')[0].setVisible(false);
											this.CommonForm.find('name', 'MASSUNITS2')[0].setVisible(true);
										break;
									}
									this.doLayout();
								}.createDelegate(this)
							},
							triggerAction: 'all',
							mode: 'local',
							store: new Ext.data.Store({
								autoLoad: true,
								data: [ [1, lang['obyem_pervichnoy_upakovki']], [2, lang['massa_pervichnoy_upakovki']] ],
								reader: new Ext.data.ArrayReader({
									idIndex: 0
								}, [
									{mapping: 0, name: 'id'},
									{mapping: 1, name: 'name'}
								])
							}),
							displayField: 'name',
							valueField: 'id',
							hiddenName: 'measure',
							fieldLabel: lang['ed_izm_pervichnoy_upakovki']
						}
					]
				}, {
					layout: 'column',
					width: 600,
					name: 'CUBICUNITS',
					defaults: {
						border: false
					},
					items: [
						{
							layout: 'form',
							labelWidth: 200,
							columnWidth: .5,
							items: [
								{
									xtype: 'numberfield',
									anchor: '100%',
									decimalPrecision: 0,
									listeners: {
										change: this.setPrepFullName.createDelegate(this)
									},
									name: 'NOMEN_PPACKVOLUME',
									fieldLabel: lang['kol-vo']
								}
							]
						}, {
							layout: 'form',
							columnWidth: .5,
							items: [
								{
									xtype: 'swbaselocalcombo',
									anchor: '100%',
									triggerAction: 'all',
									mode: 'local',
									editable: false,
									store: new Ext.data.Store({
										autoLoad: true,
										baseParams: {
											object: 'CUBICUNITS',
											stringfield: 'SHORTNAME',
											where: '1=1'
										},
										reader: new Ext.data.JsonReader({
											id: 'CUBICUNITS_ID'
										}, [{
											mapping: 'CUBICUNITS_ID',
											name: 'CUBICUNITS_ID',
											type: 'int'
										},{
											mapping: 'SHORTNAME',
											name: 'SHORTNAME',
											type: 'string'
										}]),
										url: '/?c=Rls&m=getDataForComboStore'
									}),
									listeners: {
										change: this.setPrepFullName.createDelegate(this)
									},
									hiddenName: 'NOMEN_PPACKCUBUNID',
									valueField: 'CUBICUNITS_ID',
									displayField: 'SHORTNAME',
									fieldLabel: lang['nazvanie']
								}
							]
						}
					]
				}, {
					layout: 'column',
					width: 600,
					hidden: true,
					name: 'MASSUNITS2',
					defaults: {
						border: false
					},
					items: [
						{
							layout: 'form',
							labelWidth: 200,
							columnWidth: .5,
							items: [
								{
									xtype: 'numberfield',
									anchor: '100%',
									decimalPrecision: 0,
									listeners: {
										change: this.setPrepFullName.createDelegate(this)
									},
									name: 'NOMEN_PPACKMASS',
									fieldLabel: lang['kol-vo']
								}
							]
						}, {
							layout: 'form',
							columnWidth: .5,
							items: [
								{
									xtype: 'swbaselocalcombo',
									anchor: '100%',
									editable: false,
									triggerAction: 'all',
									mode: 'local',
									store: new Ext.data.Store({
										autoLoad: true,
										baseParams: {
											object: 'MASSUNITS',
											stringfield: 'SHORTNAME',
											where: '1=1'
										},
										reader: new Ext.data.JsonReader({
											id: 'MASSUNITS_ID'
										}, [{
											mapping: 'MASSUNITS_ID',
											name: 'MASSUNITS_ID',
											type: 'int'
										},{
											mapping: 'SHORTNAME',
											name: 'SHORTNAME',
											type: 'string'
										}]),
										url: '/?c=Rls&m=getDataForComboStore'
									}),
									listeners: {
										change: this.setPrepFullName.createDelegate(this)
									},
									hiddenName: 'NOMEN_PPACKMASSUNID',
									valueField: 'MASSUNITS_ID',
									displayField: 'SHORTNAME',
									fieldLabel: lang['nazvanie']
								}
							]
						}
					]
				}, {
					layout: 'form',
					labelWidth: 200,
					width: 600,
					items: [
						{
							xtype: 'swbaselocalcombo',
							anchor: '100%',
							store: new Ext.data.Store({
								autoLoad: false,
								listeners: {
									load: function(s) {
										var c = this.CommonForm.getForm().findField('NOMEN_SETID');
										if(s.getCount() == 0) {
											c.reset();
											return false;
										}
									}.createDelegate(this)
								},
								baseParams: {
									object: 'DRUGSET'
								},
								reader: new Ext.data.JsonReader({
									id: 'DRUGSET_ID'
								}, [{
									mapping: 'DRUGSET_ID',
									name: 'DRUGSET_ID',
									type: 'int'
								},{
									mapping: 'SHORTNAME',
									name: 'SHORTNAME',
									type: 'string'
								}]),
								url: '/?c=Rls&m=getDataForComboStore'
							}),
							doQuery: function(q, forceAll) {
								var combo = this;
								//if(q.length<1) return false;
								combo.fireEvent('beforequery', combo);
								var where = combo.displayField+' like \''+q+'%\'';
								combo.getStore().baseParams[combo.valueField] = null;
								combo.getStore().baseParams.where = where;
								combo.getStore().baseParams.stringfield = combo.displayField;
								combo.getStore().load();
							},
							hiddenName: 'NOMEN_SETID',
							triggerAction: 'none',
							displayField: 'SHORTNAME',
							valueField: 'DRUGSET_ID',
							emptyText: lang['vvedite_nazvanie_komplekta_k_pervichnoy_upakovke'],
							fieldLabel: lang['nazv_komplekta_k_perv_upakovke']
						}
					]
				}, {
					layout: 'column',
					width: 600,
					defaults: {
						border: false
					},
					items: [
						{
							layout: 'form',
							columnWidth: .5,
							labelWidth: 200,
							items: [
								{
									xtype: 'numberfield',
									anchor: '100%',
									decimalPrecision: 0,
									name: 'NOMEN_PPACKINUPACK',
									fieldLabel: lang['kol-vo_perv_upakovok_vo_vtor']
								}
							]
						}, {
							layout: 'form',
							labelWidth: 160,
							columnWidth: .5,
							items: [
								{
									xtype: 'swbaselocalcombo',
									anchor: '100%',
									store: new Ext.data.Store({
										autoLoad: false,
										listeners: {
											load: function(s) {
												var c = this.CommonForm.getForm().findField('NOMEN_UPACKID');
												if(s.getCount() == 0) {
													c.reset();
													return false;
												}
											}.createDelegate(this)
										},
										baseParams: {
											object: 'DRUGPACK'
										},
										reader: new Ext.data.JsonReader({
											id: 'DRUGPACK_ID'
										}, [{
											mapping: 'DRUGPACK_ID',
											name: 'DRUGPACK_ID',
											type: 'int'
										},{
											mapping: 'FULLNAME',
											name: 'FULLNAME',
											type: 'string'
										}]),
										url: '/?c=Rls&m=getDataForComboStore'
									}),
									doQuery: function(q, forceAll) {
										var combo = this;
										//if(q.length<1) return false;
										combo.fireEvent('beforequery', combo);
										var where = combo.displayField+' like \''+q+'%\'';
										combo.getStore().baseParams[combo.valueField] = null;
										combo.getStore().baseParams.where = where;
										combo.getStore().baseParams.stringfield = combo.displayField;
										combo.getStore().load();
									},
									listeners: {
										change: this.setPrepFullName.createDelegate(this)
									},
									hiddenName: 'NOMEN_UPACKID',
									triggerAction: 'none',
									listWidth: 200,
									displayField: 'FULLNAME',
									valueField: 'DRUGPACK_ID',
									fieldLabel: lang['nazvanie_vtor_upakovki']
								}
							]
						}
					]
				}, {
					layout: 'column',
					width: 600,
					defaults: {
						border: false
					},
					items: [
						{
							layout: 'form',
							columnWidth: .5,
							labelWidth: 200,
							items: [
								{
									xtype: 'numberfield',
									anchor: '100%',
									listeners: {
										change: this.setPrepFullName.createDelegate(this)
									},
									decimalPrecision: 0,
									name: 'NOMEN_UPACKINSPACK',
									fieldLabel: lang['kol-vo_vtor_upakovok_vo_tret']
								}
							]
						}, {
							layout: 'form',
							labelWidth: 160,
							columnWidth: .5,
							items: [
								{
									xtype: 'swbaselocalcombo',
									anchor: '100%',
									store: new Ext.data.Store({
										autoLoad: false,
										listeners: {
											load: function(s) {
												var c = this.CommonForm.getForm().findField('NOMEN_SPACKID');
												if(s.getCount() == 0) {
													c.reset();
													return false;
												}
											}.createDelegate(this)
										},
										baseParams: {
											object: 'DRUGPACK'
										},
										reader: new Ext.data.JsonReader({
											id: 'DRUGPACK_ID'
										}, [{
											mapping: 'DRUGPACK_ID',
											name: 'DRUGPACK_ID',
											type: 'int'
										},{
											mapping: 'FULLNAME',
											name: 'FULLNAME',
											type: 'string'
										}]),
										url: '/?c=Rls&m=getDataForComboStore'
									}),
									doQuery: function(q, forceAll) {
										var combo = this;
										//if(q.length<1) return false;
										combo.fireEvent('beforequery', combo);
										var where = combo.displayField+' like \''+q+'%\'';
										combo.getStore().baseParams[combo.valueField] = null;
										combo.getStore().baseParams.where = where;
										combo.getStore().baseParams.stringfield = combo.displayField;
										combo.getStore().load();
									},
									listeners: {
										change: this.setPrepFullName.createDelegate(this)
									},
									hiddenName: 'NOMEN_SPACKID',
									triggerAction: 'none',
									listWidth: 200,
									displayField: 'FULLNAME',
									valueField: 'DRUGPACK_ID',
									fieldLabel: lang['nazvanie_tret_upakovki']
								}
							]
						}
					]
				}, {
					layout: 'form',
					width: 600,
					labelWidth: 200,
					items: [
						{
							xtype: 'swbaselocalcombo',
							anchor: '100%',
							store: new Ext.data.Store({
								autoLoad: false,
								listeners: {
									load: function(s) {
										var c = this.CommonForm.getForm().findField('FIRMS_ID');
										if(s.getCount() == 0) {
											c.reset();
											return false;
										}
									}.createDelegate(this)
								},
								reader: new Ext.data.JsonReader({
									id: 'FIRMS_ID'
								}, [
									{ mapping: 'FIRMS_ID', name: 'FIRMS_ID', type: 'int' },
									{ mapping: 'FIRMS_NAME', name: 'FIRMS_NAME', type: 'string'	}
								]),
								url: '/?c=Rls&m=getFirm'
							}),
							initTrigger: this.inT,
							triggerConfig:	{
								tag:'span', cls:'x-form-twin-triggers', cn:[
								{tag: "img", src: Ext.BLANK_IMAGE_URL, cls: "x-form-trigger x-form-select-trigger"},
								{tag: "img", src: Ext.BLANK_IMAGE_URL, cls: "x-form-trigger x-form-search-trigger"}
							]},
							doQuery: function(q, fA){
								this.getStore().baseParams = {};
								this.getStore().baseParams[this.displayField] = q;
								this.getStore().load();
							},
							onTrigger1Click: this.onTrigger1Click,
							onTrigger2Click: function(){
								var c = this;
								getWnd('swRlsFirmsSearchWindow').show({
									onSelect: function(data){
										c.getStore().removeAll();
										c.focus(true);
										c.getStore().baseParams = {FIRMS_ID: data[c.valueField]};
										c.getStore().load({
											callback: function(){
												c.setValue(data[c.valueField]);
												c.collapse();
												c.focus(true, 100);
											}
										});
									}
								});
							},
							listeners: {
								change: this.setPrepFullName.createDelegate(this)
							},
							emptyText: lang['vvedite_naimenovanie'],
							triggerAction: 'none',
							valueField: 'FIRMS_ID',
							displayField: 'FIRMS_NAME',
							hiddenName: 'FIRMS_ID',
							allowBlank: false,
							fieldLabel: lang['proizvoditel']
						}
					]
				}, {
					layout: 'column',
					width: 600,
					defaults: {
						border: false
					},
					items: [
						{
							layout: 'form',
							columnWidth: .5,
							labelWidth: 200,
							items: [
								{
									xtype: 'textfield',
									anchor: '100%',
									listeners: {
										change: this.setPrepFullName.createDelegate(this)
									},
									name: 'NOMEN_EANCODE',
									fieldLabel: lang['kod_ean']
								}
							]
						}, {
							layout: 'form',
							labelWidth: 160,
							columnWidth: .5,
							items: [
								{
									xtype: 'textfield',
									anchor: '100%',
									listeners: {
										change: this.setPrepFullName.createDelegate(this)
									},
									name: 'REGCERT_REGNUM',
									allowBlank: false,
									fieldLabel: lang['nomer_registratsii']
								}
							]
						}
					]
				}, {
					layout: 'column',
					width: 600,
					defaults: {
						border: false
					},
					items: [
						{
							layout: 'form',
							columnWidth: .5,
							labelWidth: 200,
							items: [
								{
									xtype: 'swdatefield',
									name: 'REGCERT_REGDATE',
									fieldLabel: lang['data_registratsii']
								}
							]
						}, {
							layout: 'form',
							labelWidth: 200,
							columnWidth: .5,
							items: [
								{
									xtype: 'swdatefield',
									name: 'REGCERT_ENDDATE',
									fieldLabel: lang['data_prekr_sroka_deystviya']
								}
							]
						}
					]
				},
				{
					layout: 'form',
					width: 650,
					labelWidth: 200,
					items: [
						{
							xtype: 'swbaselocalcombo',
							anchor: '100%',
							store: new Ext.data.Store({
								autoLoad: false,
								listeners: {
									load: function(s) {
										var c = this.CommonForm.getForm().findField('CLSATC_ID');
										if(s.getCount() == 0) {
											c.reset();
											return false;
										}
									}.createDelegate(this)
								},
								baseParams: {
									object: 'CLSATC',
									stringfield: 'NAME'
								},
								reader: new Ext.data.JsonReader({
									id: 'CLSATC_ID'
								}, [{
									mapping: 'CLSATC_ID',
									name: 'CLSATC_ID',
									type: 'int'
								},{
									mapping: 'NAME',
									name: 'NAME',
									type: 'string'
								}]),
								url: '/?c=Rls&m=getDataForComboStore'
							}),
							initTrigger: this.inT,
							triggerConfig:	{
								tag:'span', cls:'x-form-twin-triggers', cn:[
								{tag: "img", src: Ext.BLANK_IMAGE_URL, cls: "x-form-trigger x-form-select-trigger"},
								{tag: "img", src: Ext.BLANK_IMAGE_URL, cls: "x-form-trigger x-form-search-trigger"}
							]},
							onTrigger1Click: this.onTrigger1Click,
							onTrigger2Click: function(){
								var c = this;
								if(!this.formList){
									this.getStore().baseParams.stringfield = this.displayField;
									this.formList = new sw.Promed.swListSearchWindow({
										title: lang['klassifikatsiya_ath_poisk'],
										id: 'CLSATC_SearchWindow',
										object: 'rls.CLSATC',
										prefix: 'clsatc',
										useBaseParams: true,
										store: this.getStore()
									});
								}
								this.formList.show({
									onSelect: function(data){
										c.getStore().removeAll();
										c.focus(true);
										c.getStore().load({
											params: {
												CLSATC_ID: data[c.hiddenName]
											},
											callback: function(){
												c.setValue(data[c.hiddenName]);
												c.collapse();
												c.focus(true, 100);
											}
										});
									}
								});
							},
							doQuery: this.dQ,
							hiddenName: 'CLSATC_ID',
							triggerAction: 'none',
							displayField: 'NAME',
							valueField: 'CLSATC_ID',
							fieldLabel: lang['klassifikatsiya_ath']
						}, /*{
							xtype: 'swbaselocalcombo',
							anchor: '100%',
							store: new Ext.data.Store({
								autoLoad: false,
								listeners: {
									load: function(s) {
										var c = this.CommonForm.getForm().findField('CLSIIC_ID');
										if(s.getCount() == 0) {
											c.reset();
											return false;
										}
									}.createDelegate(this)
								},
								baseParams: {
									object: 'CLSIIC',
									stringfield: 'NAME'
								},
								reader: new Ext.data.JsonReader({
									id: 'CLSIIC_ID'
								}, [{
									mapping: 'CLSIIC_ID',
									name: 'CLSIIC_ID',
									type: 'int'
								},{
									mapping: 'NAME',
									name: 'NAME',
									type: 'string'
								}]),
								url: '/?c=Rls&m=getDataForComboStore'
							}),
							initTrigger: this.inT,
							triggerConfig:	{
								tag:'span', cls:'x-form-twin-triggers', cn:[
								{tag: "img", src: Ext.BLANK_IMAGE_URL, cls: "x-form-trigger x-form-select-trigger"},
								{tag: "img", src: Ext.BLANK_IMAGE_URL, cls: "x-form-trigger x-form-search-trigger"}
							]},
							onTrigger1Click: this.onTrigger1Click,
							onTrigger2Click: function(){
								var c = this;
								if(!this.formList){
									this.getStore().baseParams.stringfield = this.displayField;
									this.formList = new sw.Promed.swListSearchWindow({
										title: lang['klassifikatsiya_mkb-10_poisk'],
										id: 'CLSIIC_SearchWindow',
										object: 'rls.CLSIIC',
										prefix: 'clsiic',
										useBaseParams: true,
										store: this.getStore()
									});
								}
								this.formList.show({
									onSelect: function(data){
										c.getStore().removeAll();
										c.focus(true);
										c.getStore().load({
											params: {
												CLSIIC_ID: data[c.hiddenName]
											},
											callback: function(){
												c.setValue(data[c.hiddenName]);
												c.collapse();
												c.focus(true, 100);
											}
										});
									}
								});
							},
							doQuery: this.dQ,
							hiddenName: 'CLSIIC_ID',
							triggerAction: 'none',
							displayField: 'NAME',
							valueField: 'CLSIIC_ID',
							fieldLabel: lang['klassifikatsiya_mkb-10']
						},*/ {
							xtype: 'swbaselocalcombo',
							anchor: '100%',
							store: new Ext.data.Store({
								autoLoad: false,
								listeners: {
									load: function(s) {
										var c = this.CommonForm.getForm().findField('CLSPHARMAGROUP_ID');
										if(s.getCount() == 0) {
											c.reset();
											return false;
										}
									}.createDelegate(this)
								},
								baseParams: {
									object: 'CLSPHARMAGROUP',
									stringfield: 'NAME'
								},
								reader: new Ext.data.JsonReader({
									id: 'CLSPHARMAGROUP_ID'
								}, [{
									mapping: 'CLSPHARMAGROUP_ID',
									name: 'CLSPHARMAGROUP_ID',
									type: 'int'
								},{
									mapping: 'NAME',
									name: 'NAME',
									type: 'string'
								}]),
								url: '/?c=Rls&m=getDataForComboStore'
							}),
							initTrigger: this.inT,
							triggerConfig:	{
								tag:'span', cls:'x-form-twin-triggers', cn:[
								{tag: "img", src: Ext.BLANK_IMAGE_URL, cls: "x-form-trigger x-form-select-trigger"},
								{tag: "img", src: Ext.BLANK_IMAGE_URL, cls: "x-form-trigger x-form-search-trigger"}
							]},
							onTrigger1Click: this.onTrigger1Click,
							onTrigger2Click: function(){
								var c = this;
								if(!this.formList){
									this.getStore().baseParams.stringfield = this.displayField;
									this.formList = new sw.Promed.swListSearchWindow({
										title: lang['farmakologicheskaya_gruppa_poisk'],
										id: 'CLSPHARMAGROUP_SearchWindow',
										object: 'rls.CLSPHARMAGROUP',
										prefix: 'clspharmagroup',
										useBaseParams: true,
										store: this.getStore()
									});
								}
								this.formList.show({
									onSelect: function(data){
										c.getStore().removeAll();
										c.focus(true);
										c.getStore().load({
											params: {
												CLSPHARMAGROUP_ID: data[c.hiddenName]
											},
											callback: function(){
												c.setValue(data[c.hiddenName]);
												c.collapse();
												c.focus(true, 100);
											}
										});
									}
								});
							},
							doQuery: this.dQ,
							hiddenName: 'CLSPHARMAGROUP_ID',
							triggerAction: 'none',
							displayField: 'NAME',
							valueField: 'CLSPHARMAGROUP_ID',
							fieldLabel: lang['farmakologicheskaya_gruppa']
						}
					]
				}, {
					layout: 'form',
					width: 650,
					labelWidth: 550,
					items: [
						{
							comboSubject: 'YesNo',
							xtype: 'swcommonsprcombo',
							anchor: '100%',
							hiddenName: 'TN_DF_LIMP',
							allowBlank: false,
							fieldLabel: lang['otnositsya_li_k_jiznennovajnyim_lek_sredstvam_po_klassif_mz_rf_po_torg_nazvaniyam']
						}, {
							comboSubject: 'YesNo',
							xtype: 'swcommonsprcombo',
							anchor: '100%',
							hiddenName: 'TRADENAMES_DRUGFORMS',
							allowBlank: false,
							fieldLabel: lang['yavlyaetsya_li_preparatom_lgotnogo_assortimenta_cherez_torg_nazvanie_i_lek_formu']
						}
					]
				}
			]
		});
		
		this.MainPanel2 = new sw.Promed.Panel({
			title: lang['osnovnaya'],
			defaults: {
				border: false,
				width: 800,
				labelAlign: 'right'
			},
			items: [
				{
					layout: 'column',
					defaults: {
						border: false
					},
					items: [
						{
							layout: 'form',
							columnWidth: .6,
							labelWidth: 140,
							items: [
								{
									xtype: 'hidden',
									name: 'Prep_id'
								}, {
									xtype: 'hidden',
									name: 'TRADENAMES_ID'
								}, {
									xtype: 'hidden',
									name: 'LATINNAMES_ID'
								}, {
									xtype: 'hidden',
									name: 'REGCERT_ID'
								}, {
									xtype: 'hidden',
									name: 'DRUGLIFETIME_ID'
								}, {
									xtype: 'hidden',
									name: 'NOMEN_ID'
								}, {
									xtype: 'hidden',
									name: 'DESCRIPTIONS_ID'
								}, {
									xtype: 'hidden',
									name: 'IDENT_WIND_STR_id'
								}, {
									xtype: 'textarea',
									anchor: '100%',
									grow: true,
									allowBlank: false,
									fieldLabel: lang['otobrajaemoe_nazvanie'],
									name: 'PREPFULLNAME'
								},
								/*{
									xtype: 'swbaselocalcombo',
									anchor: '100%',
									store: new Ext.data.Store({
										autoLoad: false,
										listeners: {
											load: function(s) {
												var c = this.CommonForm2.getForm().findField('TRADENAMES_ID');
												if(s.getCount() == 0) {
													c.reset();
													return false;
												}
											}.createDelegate(this)
										},
										baseParams: {
											object: 'TRADENAMES'
										},
										reader: new Ext.data.JsonReader({
											id: 'TRADENAMES_ID'
										}, [{
											mapping: 'TRADENAMES_ID',
											name: 'TRADENAMES_ID',
											type: 'int'
										},{
											mapping: 'INAME',
											name: 'INAME',
											type: 'string'
										}]),
										url: '/?c=Rls&m=getDataForComboStore'
									}),
									initTrigger: this.inT,
									triggerConfig:	{
										tag:'span', cls:'x-form-twin-triggers', cn:[
										{tag: "img", src: Ext.BLANK_IMAGE_URL, cls: "x-form-trigger x-form-select-trigger"},
										{tag: "img", src: Ext.BLANK_IMAGE_URL, cls: "x-form-trigger x-form-search-trigger"}
									]},
									onTrigger1Click: this.onTrigger1Click,
									onTrigger2Click: function(){
										var c = this;
										if(!this.formList){
											this.getStore().baseParams.stringfield = this.displayField;
											this.formList = new sw.Promed.swListSearchWindow({
												title: lang['poisk_preparata_po_torgovomu_nazvaniyu'],
												id: 'TRADENAMES_SearchWindow',
												object: 'rls.TRADENAMES',
												useBaseParams: true,
												store: this.getStore()
											});
										}
										this.formList.show({
											onSelect: function(data){
												c.getStore().removeAll();
												c.focus(true);
												c.getStore().load({
													params: {
														TRADENAMES_ID: data[c.hiddenName]
													},
													callback: function(){
														c.setValue(data[c.hiddenName]);
														c.collapse();
														c.focus(true, 100);
													}
												});
											}
										});
									},
									doQuery: function(q, forceAll) {
										var combo = this;
										if(q.length<=2) return false;
										combo.fireEvent('beforequery', combo);
										var where = combo.displayField+' like \''+q+'%\'';
										combo.getStore().baseParams.where = where;
										combo.getStore().baseParams.stringfield = combo.displayField;
										combo.getStore().load();
									},
									hiddenName: 'TRADENAMES_ID',
									triggerAction: 'All',
									forceSelection: true,
									selectOnFocus: true,
									resizable: true,
									emptyText: lang['vvedite_torgovoe_nazvanie_preparata'],
									displayField: 'INAME',
									valueField: 'TRADENAMES_ID',
									fieldLabel: lang['torgovoe_nazvanie']
								}*/
								{
									xtype: 'textfield',
									anchor: '100%',
									listeners: {
										change: this.setPrepFullName.createDelegate(this)
									},
									allowBlank: false,
									fieldLabel: lang['torgovoe_nazvanie'],
									name: 'TRADENAMES_NAME'
								},
								{
									xtype: 'textfield',
									anchor: '100%',
									allowBlank: false,
									name: 'LATINNAMES_NAME',
									fieldLabel: lang['latinskoe_nazvanie']
								}
							]
						},
						{
							layout: 'form',
							labelWidth: 100,
							columnWidth: .4,
							items: [
								{
									xtype: 'textfield',
									anchor: '100%',
									allowBlank: false,
									name: 'DRUGLIFETIME_TEXT',
									fieldLabel: lang['srok_godnosti']
								}
							]
						}
					]
				}, {
					layout: 'column',
					defaults: {
						border: false
					},
					items: [
						{
							layout: 'form',
							labelWidth: 200,
							columnWidth: .5,
							items: [
								{
									xtype: 'numberfield',
									anchor: '100%',
									enableKeyEvents: true,
									allowDecimals: false,
									allowNegative: false,
									name: 'NOMEN_DRUGSINPPACK',
									fieldLabel: lang['kol-vo_prep_v_perv_upakovke']
								}
							]
						},
						{
							layout: 'form',
							labelWidth: 180,
							columnWidth: .5,
							items: [
								{
									xtype: 'swbaselocalcombo',
									anchor: '100%',
									store: new Ext.data.Store({
										autoLoad: false,
										listeners: {
											load: function(s) {
												var c = this.CommonForm2.getForm().findField('NOMEN_PPACKID');
												if(s.getCount() == 0) {
													c.reset();
													return false;
												}
											}.createDelegate(this)
										},
										baseParams: {
											object: 'DRUGPACK'
										},
										reader: new Ext.data.JsonReader({
											id: 'DRUGPACK_ID'
										}, [{
											mapping: 'DRUGPACK_ID',
											name: 'DRUGPACK_ID',
											type: 'int'
										},{
											mapping: 'FULLNAME',
											name: 'FULLNAME',
											type: 'string'
										}]),
										url: '/?c=Rls&m=getDataForComboStore'
									}),
									doQuery: function(q, forceAll) {
										var combo = this;
										//if(q.length<1) return false;
										combo.fireEvent('beforequery', combo);
										var where = combo.displayField+' like \''+q+'%\'';
										combo.getStore().baseParams[combo.valueField] = null;
										combo.getStore().baseParams.where = where;
										combo.getStore().baseParams.stringfield = combo.displayField;
										combo.getStore().load();
									},
									listeners: {
										change: this.setPrepFullName.createDelegate(this)
									},
									hiddenName: 'NOMEN_PPACKID',
									triggerAction: 'none',
									listWidth: 250,
									displayField: 'FULLNAME',
									valueField: 'DRUGPACK_ID',
									fieldLabel: lang['nazvanie_perv_upakovki']
								}
							]
						}
					]
				}, {
					layout: 'column',
					defaults: {
						border: false
					},
					items: [
						{
							layout: 'form',
							labelWidth: 140,
							columnWidth: .6,
							items: [
								{
									xtype: 'swbaselocalcombo',
									anchor: '100%',
									store: new Ext.data.Store({
										autoLoad: false,
										listeners: {
											load: function(s) {
												var c = this.CommonForm2.getForm().findField('FIRMS_ID');
												if(s.getCount() == 0) {
													c.reset();
													return false;
												}
											}.createDelegate(this)
										},
										reader: new Ext.data.JsonReader({
											id: 'FIRMS_ID'
										}, [
											{ mapping: 'FIRMS_ID', name: 'FIRMS_ID', type: 'int' },
											{ mapping: 'FIRMS_NAME', name: 'FIRMS_NAME', type: 'string'	}
										]),
										url: '/?c=Rls&m=getFirm'
									}),
									initTrigger: this.inT,
									triggerConfig:	{
										tag:'span', cls:'x-form-twin-triggers', cn:[
										{tag: "img", src: Ext.BLANK_IMAGE_URL, cls: "x-form-trigger x-form-select-trigger"},
										{tag: "img", src: Ext.BLANK_IMAGE_URL, cls: "x-form-trigger x-form-search-trigger"}
									]},
									doQuery: function(q, fA){
										this.getStore().baseParams = {};
										this.getStore().baseParams[this.displayField] = q;
										this.getStore().load();
									},
									onTrigger1Click: this.onTrigger1Click,
									onTrigger2Click: function(){
										var c = this;
										getWnd('swRlsFirmsSearchWindow').show({
											onSelect: function(data){
												c.getStore().removeAll();
												c.focus(true);
												c.getStore().baseParams = {FIRMS_ID: data[c.valueField]};
												c.getStore().load({
													callback: function(){
														c.setValue(data[c.valueField]);
														c.collapse();
														c.focus(true, 100);
													}
												});
											}
										});
									},
									listeners: {
										change: this.setPrepFullName.createDelegate(this)
									},
									emptyText: lang['vvedite_naimenovanie'],
									triggerAction: 'all',
									valueField: 'FIRMS_ID',
									displayField: 'FIRMS_NAME',
									hiddenName: 'FIRMS_ID',
									allowBlank: false,
									fieldLabel: lang['proizvoditel']
								}
							]
						},
						{
							layout: 'form',
							columnWidth: .4,
							items: [
								{
									xtype: 'textfield',
									anchor: '100%',
									listeners: {
										change: this.setPrepFullName.createDelegate(this)
									},
									name: 'NOMEN_EANCODE',
									fieldLabel: lang['kod_ean']
								}
							]
						}
					]
				}
			]
		});
		
		this.PrepDataPanel = new sw.Promed.Panel({
			title: lang['svedeniya'],
			autoScroll: true,
			items: [
				{
					layout: 'form',
					border: false,
					defaults: {
						anchor: '100%'
					},
					labelAlign: 'right',
					width: '95%',
					labelWidth: 250,
					items: [
						{
							xtype: 'textarea',
							allowBlank: false,
							name: 'DESCTEXTES_COMPOSITION',
							fieldLabel: lang['sostav_i_forma_vyipuska']
						}, {
							xtype: 'textarea',
							name: 'DESCTEXTES_CHARACTERS',
							fieldLabel: lang['harakteristika']
						}, {
							xtype: 'textarea',
							name: 'DESCTEXTES_PHARMAACTIONS',
							fieldLabel: lang['farmakologicheskoe_deystvie']
						}, {
							xtype: 'textarea',
							name: 'DESCTEXTES_ACTONORG',
							fieldLabel: lang['deystvie_na_organizm']
						}, {
							xtype: 'textarea',
							name: 'DESCTEXTES_COMPONENTSPROPERTIES',
							fieldLabel: lang['svoystva_komponentov']
						}, {
							xtype: 'textarea',
							name: 'DESCTEXTES_PHARMAKINETIC',
							fieldLabel: lang['farmakokinetika']
						}, {
							xtype: 'textarea',
							name: 'DESCTEXTES_PHARMADYNAMIC',
							fieldLabel: lang['farmakodinamika']
						}, {
							xtype: 'textarea',
							name: 'DESCTEXTES_CLINICALPHARMACOLOGY',
							fieldLabel: lang['klinicheskaya_farmakologiya']
						}, {
							xtype: 'textarea',
							name: 'DESCTEXTES_DIRECTION',
							fieldLabel: lang['instruktsiya']
						}, {
							xtype: 'textarea',
							name: 'DESCTEXTES_INDICATIONS',
							fieldLabel: lang['pokazaniya']
						}, {
							xtype: 'textarea',
							name: 'DESCTEXTES_RECOMMENDATIONS',
							fieldLabel: lang['rekomenduetsya']
						}, {
							xtype: 'textarea',
							name: 'DESCTEXTES_CONTRAINDICATIONS',
							fieldLabel: lang['protivopokazaniya']
						}, {
							xtype: 'textarea',
							name: 'DESCTEXTES_PREGNANCYUSE',
							fieldLabel: lang['primen_pri_berem-ti_i_korml_grudyu']
						}, {
							xtype: 'textarea',
							name: 'DESCTEXTES_SIDEACTIONS',
							fieldLabel: lang['pobochnyie_deystviya']
						}, {
							xtype: 'textarea',
							name: 'DESCTEXTES_INTERACTIONS',
							fieldLabel: lang['vzaimodeystvie']
						}, {
							xtype: 'textarea',
							allowBlank: false,
							name: 'DESCTEXTES_USEMETHODANDDOSES',
							fieldLabel: lang['sposob_primeneniya_i_dozyi']
						}, {
							xtype: 'textarea',
							name: 'DESCTEXTES_INSTRFORPAC',
							fieldLabel: lang['instruktsiya_dlya_patsienta']
						}, {
							xtype: 'textarea',
							name: 'DESCTEXTES_OVERDOSE',
							fieldLabel: lang['peredozirovka']
						}, {
							xtype: 'textarea',
							name: 'DESCTEXTES_PRECAUTIONS',
							fieldLabel: lang['meryi_predostorojnosti']
						}, {
							xtype: 'textarea',
							name: 'DESCTEXTES_SPECIALGUIDELINES',
							fieldLabel: lang['osobyie_ukazaniya']
						}
					]
				}
			]
		});
		
		this.ActmattersGrid = new sw.Promed.ViewFrame({
			autoExpandColumn: 'autoexpand',
			id: this.id + '_ActmattersGrid',
			autoExpandMin: 100,
			pageSize: 20,
			style: 'border: 1px solid #000;',
			actions: [
				{ name: 'action_add', handler: function(){this.addActmatterInGrid(true);}.createDelegate(this) },
				{ name: 'action_edit', hidden: true },
				{ name: 'action_view', hidden: true },
				{ name: 'action_delete', handler: function(){this.deleteActmatterInGrid();}.createDelegate(this) },
				{ name: 'action_refresh', hidden: true },
				{ name: 'action_print', hidden: true }
			],
			autoLoadData: false,
			stripeRows: true,
			stringfields: [
				{ name: 'ACTMATTERS_ID', type: 'int', hidden: true, key: true },
				{ name: 'RUSNAME', type: 'string', header: lang['deystvuyuschee_veschestvo'], width: 300 },
				{ name: 'LATNAME', type: 'string', header: lang['latinskoe_nazvanie_deystvuyuschego_veschestva'], id: 'autoexpand' }
			],
			paging: true,
			totalProperty: 'totalCount'
		});
		
		this.ActPanel = new sw.Promed.Panel({
			title: lang['deystvuyuschee_veschestvo'],
			defaults: {
				border: false,
				labelAlign: 'right'
			},
			items: [
				/*{
					layout: 'form',
					labelWidth: 300,
					items: [
						{
							xtype: 'swbaselocalcombo',
							width: 200,
							store: new Ext.data.Store({
								autoLoad: true,
								baseParams: {
									object: 'STRONGGROUPS',
									stringfield: 'NAME'
								},
								reader: new Ext.data.JsonReader({
									id: 'STRONGGROUPS_ID'
								}, [{
									mapping: 'STRONGGROUPS_ID',
									name: 'STRONGGROUPS_ID',
									type: 'int'
								},{
									mapping: 'NAME',
									name: 'NAME',
									type: 'string'
								}]),
								url: '/?c=Rls&m=getDataForComboStore'
							}),
							editable: false,
							hiddenName: 'STRONGGROUPS_ID',
							displayField: 'NAME',
							mode: 'local',
							valueField: 'STRONGGROUPS_ID',
							fieldLabel: lang['gruppa_silnodeystvuyuschih_i_yadovityih_veschestv']
						}
					]
				},*/ {
					layout: 'column',
					labelWidth: 200,
					hidden: true,
					defaults: {
						border: false
					},
					colName: 'ActmatterSearchColumn',
					width: 700,
					items: [
						{
							layout: 'form',
							columnWidth: .7,
							items: [
								{
									xtype: 'swbaselocalcombo',
									anchor: '100%',
									store: new Ext.data.Store({
										autoLoad: true,
										baseParams: {
											object: 'ACTMATTERS',
											codeField: 'LATNAME',
											stringfield: 'RUSNAME'
										},
										reader: new Ext.data.JsonReader({
											id: 'ACTMATTERS_ID'
										}, [{
											mapping: 'ACTMATTERS_ID',
											name: 'ACTMATTERS_ID',
											type: 'int'
										},{
											mapping: 'LATNAME',
											name: 'LATNAME',
											type: 'string'
										},{
											mapping: 'RUSNAME',
											name: 'RUSNAME',
											type: 'string'
										}]),
										url: '/?c=Rls&m=getDataForComboStore'
									}),
									onTriggerClick: this.onTrigger1Click,
									doQuery: this.dQ,
									emptyText: lang['vvedite_nazvanie_deystvuyuschego_veschestva'],
									hiddenName: 'ACTMATTERS_ID',
									triggerAction: 'none',
									valueField: 'ACTMATTERS_ID',
									displayField: 'RUSNAME',
									fieldLabel: lang['deystvuyuschee_veschestvo']
								}
							]
						}, {
							layout: 'form',
							columnWidth: .3,
							items: [
								{
									xtype: 'button',
									style: 'margin-left: 5px;',
									handler: function(){
										this.addActmatterInGrid();
									}.createDelegate(this),
									text: lang['dobavit']
								}
							]
						}
					]
				},
				this.ActmattersGrid
			]
		});
		
		this.MkbGrid = new sw.Promed.ViewFrame({
			autoExpandColumn: 'autoexpand',
			id: this.id + '_MkbGrid',
			autoExpandMin: 100,
			pageSize: 20,
			style: 'border: 1px solid #000;',
			actions: [
				{ name: 'action_add', handler: this.addMkb.createDelegate(this) },
				{ name: 'action_edit', hidden: true },
				{ name: 'action_view', hidden: true },
				{ name: 'action_delete', handler: this.deleteMkb.createDelegate(this) },
				{ name: 'action_refresh', hidden: true },
				{ name: 'action_print', hidden: true }
			],
			autoLoadData: false,
			stripeRows: true,
			stringfields: [
				{ name: 'CLSIIC_ID', type: 'int', hidden: true, key: true },
				{ name: 'NAME', type: 'string', header: lang['mkb-10'], id: 'autoexpand' }
			],
			paging: true,
			totalProperty: 'totalCount'
		});
		
		this.MkbPanel = new sw.Promed.Panel({
			title: lang['klassifikatsiya_mkb-10'],
			defaults: {
				border: false,
				labelAlign: 'right'
			},
			items: [this.MkbGrid]
		});
		
		this.ImgTpl = [
			'<div><a href="{file_url}" target="_blank"><img style="text-align: center;" height="200" width="200" src="{file_url}" /></a></div>'
		];
		
		this.Image = new Ext.form.FieldSet({
			autoHeight: true,
			width: 570,
			style: 'margin-left: 10px;',
			title: ' '
		});
		
		this.ImagePanel = new Ext.form.FormPanel({
			bodyStyle: 'padding: 0px 3px 3px 3px; border-top: 0px; border-bottom: 0px;',
			defaults: {
				collapsible: true,
				bodyStyle: 'padding: 3px;'
			},
			url: '/?c=Rls&m=preuploadImage',
			fileUpload: true,
			items: [
				{
					layout: 'form',
					titleCollapse: true,
					animCollapse: false,
					title: lang['fayl_s_risunkom'],
					items: [
						{
							layout: 'column',
							border: false,
							defaults: {
								border: false
							},
							items: [
								{
									layout: 'form',
									width: 580,
									labelAlign: 'right',
									labelWidth: 130,
									items: [
										{
											xtype: 'fileuploadfield',
											anchor: '100%',
											file_uploaded: false,
											name: 'file',
											listeners: {
												fileselected: function(f, v) {
													if(f.dasabled){
														f.reset();
														return false;
													}
													var allowedFiles = ['gif', 'jpg', 'jpeg'], // допустимые расширения
														re = new RegExp(allowedFiles.join('|'), 'ig');
													if(!re.test(v)) {
														sw.swMsg.alert(lang['oshibka'], lang['dannyiy_tip_zagrujaemogo_fayla_ne_podderjivaetsya']);
														f.reset();
														return false;
													}
													var pBar = this.ImagePanel.find('name', 'progressbar')[0];
													pBar.updateProgress(0, lang['zagrujeno_0%'], false);
													
													// пока так:
													this.ImagePanel.find('xtype', 'button')[0].handler();
												}.createDelegate(this)
											},
											buttonText: lang['vyibrat'],
											fieldLabel: lang['fayl_s_risunkom']
										}
									]
								}, {
									layout: 'form',
									hidden: true,
									items: [
										{
											xtype: 'button',
											style: 'margin-left: 5px;',
											text: lang['zagruzit'],
											handler: function(){
												var form = this.ImagePanel.getForm();
												var field = this.ImagePanel.find('xtype', 'fileuploadfield')[0];
												
												var cb = function(f, r) {
													var obj = Ext.util.JSON.decode(r.response.responseText);
													this.overwriteTpl(obj);
													this.Image.setTitle(f.findField('file').getRawValue());
													this.doLayout();
													field.file_uploaded = true;
												}
												
												if ( !this.loadFileWithFileAPI(cb) ) {
													form.submit({
														url: this.ImagePanel.url,
														success: cb.createDelegate(this)
													});
												}
											}.createDelegate(this)
										}
									]
								}
							]
						}, new Ext.ProgressBar({
							hidden: true,
							name: 'progressbar',
							height: 20,
							width: 570,
							text: 'text',
							style: 'margin: 10px;'
						}),
						this.Image
					]
				}
			]
		});
		
		this.AutorPanel = new Ext.form.FormPanel({
			bodyStyle: 'padding: 0px 3px 3px 3px; border-top: 0px;',
			defaults: {
				bodyStyle: 'padding: 3px;',
				collapsible: true
			},
			autoHeight: true,
			items: [
				new sw.Promed.Panel({
					title: lang['avtor'],
					collapsed: true,
					defaults: {
						labelAlign: 'right'
					},
					items: [
						{
							xtype: 'fieldset',
							autoHeight: true,
							defaults: {
								width: 500,
								readOnly: true,
								style: 'border: 0px; background: #fff;'
							},
							title: lang['sozdanie'],
							items: [
								{
									xtype: 'textfield',
									name: 'autor_date_ins',
									fieldLabel: lang['data']
								}, {
									xtype: 'textfield',
									name: 'autor_username_ins',
									fieldLabel: lang['polzovatel']
								}, {
									xtype: 'textfield',
									name: 'autor_orgname_ins',
									fieldLabel: lang['organizatsiya']
								}
							]
						}, {
							xtype: 'fieldset',
							autoHeight: true,
							defaults: {
								width: 500,
								readOnly: true,
								style: 'border: 0px; background: #fff;'
							},
							title: lang['poslednee_izmenenie'],
							items: [
								{
									xtype: 'textfield',
									name: 'autor_date_upd',
									fieldLabel: lang['data']
								}, {
									xtype: 'textfield',
									name: 'autor_username_upd',
									fieldLabel: lang['polzovatel']
								}, {
									xtype: 'textfield',
									name: 'autor_orgname_upd',
									fieldLabel: lang['organizatsiya']
								}
							]
						}
					]
				})
			]
		});
	
		this.CommonForm = new Ext.form.FormPanel({
			bodyStyle: 'padding: 3px; border-bottom: 0px;',
			hidden: true,
			url: '/?c=Rls&m=savePrep',
			formFields: [
				'TRADENAMES_NAME',
				'LATINNAMES_NAME'
			],
			autoScroll: true,
			defaults: {
				collapsible: true,
				bodyStyle: 'padding: 3px;',
				style: 'margin-top: 3px;'
			},
			keys: [{
				fn: function(k, e) {
					var f = this.CommonForm.find('hasFocus', true)[0]; // фокус может быть только на однои поле
					if(f.xtype == 'numberfield') {
						e.stopEvent();
						return false;
					}
				},
				key: [ 190, 191 ],
				scope: this
			}],
			items: [this.MainPanel1, this.PrepDataPanel, this.ActPanel, this.MkbPanel],
			reader: new Ext.data.JsonReader(
			{
				success: function(){}
			},
			[
				{ name: 'NOMEN_ID' },
				{ name: 'Prep_id' },
				{ name: 'REGCERT_ID' },
				{ name: 'TRADENAMES_ID' },
				{ name: 'TRADENAMES_NAME' },
				{ name: 'LATINNAMES_ID' },
				{ name: 'LATINNAMES_NAME' },
				{ name: 'CLSDRUGFORMS_ID' },
				{ name: 'DFMASS' },
				{ name: 'DFMASSID' },
				{ name: 'DFCONC' },
				{ name: 'DFCONCID' },
				{ name: 'DFACT' },
				{ name: 'DFACTID' },
				{ name: 'DRUGDOSE' },
				{ name: 'DRUGLIFETIME_ID' },
				{ name: 'DRUGLIFETIME_TEXT' },
				{ name: 'DFSIZE' },
				{ name: 'DFSIZEID' },
				{ name: 'DFCHARID' },
				{ name: 'PrepType_id' },
				{ name: 'IDENT_WIND_STR_id' },
				
				{ name: 'NOMEN_DRUGSINPPACK' },
				{ name: 'NOMEN_PPACKID' },
				{ name: 'NOMEN_PPACKVOLUME' },
				{ name: 'NOMEN_PPACKCUBUNID' },
				{ name: 'NOMEN_PPACKMASS' },
				{ name: 'NOMEN_PPACKMASSUNID' },
				{ name: 'NOMEN_SETID' },
				{ name: 'NOMEN_PPACKINUPACK' },
				{ name: 'NOMEN_UPACKID' },
				{ name: 'NOMEN_UPACKINSPACK' },
				{ name: 'NOMEN_SPACKID' },
				{ name: 'FIRMS_ID' },
				{ name: 'NOMEN_EANCODE' },
				{ name: 'REGCERT_REGNUM' },
				{ name: 'REGCERT_REGDATE' },
				{ name: 'REGCERT_ENDDATE' },
				{ name: 'CLSATC_ID' },
				//{ name: 'CLSIIC_ID' },
				{ name: 'CLSPHARMAGROUP_ID' },
				{ name: 'TN_DF_LIMP' },
				{ name: 'TRADENAMES_DRUGFORMS' },
				
				{ name: 'DESCRIPTIONS_ID' },
				{ name: 'DESCTEXTES_COMPOSITION' },
				{ name: 'DESCTEXTES_CHARACTERS' },
				{ name: 'DESCTEXTES_PHARMAACTIONS' },
				{ name: 'DESCTEXTES_ACTONORG' },
				{ name: 'DESCTEXTES_COMPONENTSPROPERTIES' },
				{ name: 'DESCTEXTES_PHARMAKINETIC' },
				{ name: 'DESCTEXTES_PHARMADYNAMIC' },
				{ name: 'DESCTEXTES_CLINICALPHARMACOLOGY' },
				{ name: 'DESCTEXTES_DIRECTION' },
				{ name: 'DESCTEXTES_INDICATIONS' },
				{ name: 'DESCTEXTES_RECOMMENDATIONS' },
				{ name: 'DESCTEXTES_CONTRAINDICATIONS' },
				{ name: 'DESCTEXTES_PREGNANCYUSE' },
				{ name: 'DESCTEXTES_SIDEACTIONS' },
				{ name: 'DESCTEXTES_INTERACTIONS' },
				{ name: 'DESCTEXTES_USEMETHODANDDOSES' },
				{ name: 'DESCTEXTES_INSTRFORPAC' },
				{ name: 'DESCTEXTES_OVERDOSE' },
				{ name: 'DESCTEXTES_PRECAUTIONS' },
				{ name: 'DESCTEXTES_SPECIALGUIDELINES' }
			])
		});
		
		this.CommonForm2 = new Ext.form.FormPanel({
			bodyStyle: 'padding: 3px; border-bottom: 0px;',
			hidden: true,
			url: '/?c=Rls&m=savePrep',
			formFields: [
				'TRADENAMES_NAME'
			],
			autoScroll: true,
			defaults: {
				collapsible: true,
				bodyStyle: 'padding: 3px;',
				style: 'margin-top: 3px;'
			},
			keys: [{
				fn: function(k, e) {
					var f = this.CommonForm2.find('hasFocus', true)[0]; // фокус может быть только на однои поле
					if(f.xtype == 'numberfield') {
						e.stopEvent();
						return false;
					}
				},
				key: [ 190, 191 ],
				scope: this
			}],
			items: [this.MainPanel2],
			reader: new Ext.data.JsonReader(
			{
				success: function(){}
			},
			[
				{ name: 'NOMEN_ID' },
				{ name: 'Prep_id' },
				{ name: 'REGCERT_ID' },
				{ name: 'TRADENAMES_ID' },
				{ name: 'TRADENAMES_NAME' },
				{ name: 'LATINNAMES_ID' },
				{ name: 'LATINNAMES_NAME' },
				{ name: 'CLSDRUGFORMS_ID' },
				{ name: 'DRUGLIFETIME_ID' },
				{ name: 'DRUGLIFETIME_TEXT' },
				{ name: 'PrepType_id' },
				{ name: 'IDENT_WIND_STR_id' },
				{ name: 'NOMEN_DRUGSINPPACK' },
				{ name: 'NOMEN_PPACKID' },
				{ name: 'FIRMS_ID' },
				{ name: 'NOMEN_EANCODE' },
				{ name: 'CLSPHARMAGROUP_ID' },
				
				{ name: 'DESCRIPTIONS_ID' }
			])
		});
		
		Ext.apply(this,	{
			autoScroll: true,
			items: [this.CommonForm, this.CommonForm2, this.ImagePanel, this.AutorPanel]
		});
		sw.Promed.swRlsPrepEditWindow.superclass.initComponent.apply(this, arguments);
	}
});