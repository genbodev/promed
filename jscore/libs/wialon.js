(function(){

if (!window.qx) window.qx = {};

qx.$$start = new Date();
  
if (!qx.$$environment) qx.$$environment = {};
var envinfo = {"qx.application":"wialon.Application","qx.debug":false,"qx.debug.databinding":false,"qx.debug.dispose":false,"qx.optimization.basecalls":true,"qx.optimization.comments":true,"qx.optimization.privates":true,"qx.optimization.strings":true,"qx.optimization.variables":true,"qx.optimization.variants":true,"qx.revision":"","qx.theme":"qx.theme.Modern","qx.version":"2.0.1"};
for (var k in envinfo) qx.$$environment[k] = envinfo[k];

if (!qx.$$libraries) qx.$$libraries = {};
var libinfo = {"__out__":{"sourceUri":"script"},"qx":{"resourceUri":"resource","sourceUri":"script","sourceViewUri":"https://github.com/qooxdoo/qooxdoo/blob/%{qxGitBranch}/framework/source/class/%{classFilePath}#L%{lineNumber}"},"wialon":{"resourceUri":"resource","sourceUri":"script"}};
for (var k in libinfo) qx.$$libraries[k] = libinfo[k];

qx.$$resources = {};
qx.$$translations = {"C":null};
qx.$$locales = {"C":null};
qx.$$packageData = {};

qx.$$loader = {
  parts : {"boot":[0]},
  packages : {"0":{"uris":["__out__:wialon.7c59fd53be59.js"]}},
  urisBefore : [],
  cssBefore : [],
  boot : "boot",
  closureParts : {},
  bootIsInline : true,
  addNoCacheParam : false,
  
  decodeUris : function(compressedUris)
  {
    var libs = qx.$$libraries;
    var uris = [];
    for (var i=0; i<compressedUris.length; i++)
    {
      var uri = compressedUris[i].split(":");
      var euri;
      if (uri.length==2 && uri[0] in libs) {
        var prefix = libs[uri[0]].sourceUri;
        euri = prefix + "/" + uri[1];
      } else {
        euri = compressedUris[i];
      }
      if (qx.$$loader.addNoCacheParam) {
        euri += "?nocache=" + Math.random();
      }
      euri = euri.replace("../", "");
euri = euri.replace("source", "");
if (euri.substr(0, 1) != "/")
	euri = "/" + euri;
euri = "/wsdk" + euri;
if (typeof wialonSDKExternalUrl != "undefined")
	euri = wialonSDKExternalUrl + euri;
      uris.push(euri);
    }
    return uris;      
  }
};  

function loadScript(uri, callback) {
  var elem = document.createElement("script");
  elem.charset = "utf-8";
  elem.src = uri;
  elem.onreadystatechange = elem.onload = function() {
    if (!this.readyState || this.readyState == "loaded" || this.readyState == "complete") {
      elem.onreadystatechange = elem.onload = null;
      if (typeof callback === "function") {
        callback();
      }
    }
  };

  if (isLoadParallel) {
    elem.async = null;
  }

  var head = document.getElementsByTagName("head")[0];
  head.appendChild(elem);
}

function loadCss(uri) {
  var elem = document.createElement("link");
  elem.rel = "stylesheet";
  elem.type= "text/css";
  elem.href= uri;
  var head = document.getElementsByTagName("head")[0];
  head.appendChild(elem);
}

var isWebkit = /AppleWebKit\/([^ ]+)/.test(navigator.userAgent);
var isLoadParallel = 'async' in document.createElement('script');

function loadScriptList(list, callback) {
  if (list.length == 0) {
    callback();
    return;
  }

  var item;

  if (isLoadParallel) {
    while (list.length) {
      item = list.shift();
      if (list.length) {
        loadScript(item);
      } else {
        loadScript(item, callback);
      }
    }
  } else {
    item = list.shift();
    loadScript(item,  function() {
      if (isWebkit) {
        // force async, else Safari fails with a "maximum recursion depth exceeded"
        window.setTimeout(function() {
          loadScriptList(list, callback);
        }, 0);
      } else {
        loadScriptList(list, callback);
      }
    });
  }
}

var fireContentLoadedEvent = function() {
  qx.$$domReady = true;
  document.removeEventListener('DOMContentLoaded', fireContentLoadedEvent, false);
};
if (document.addEventListener) {
  document.addEventListener('DOMContentLoaded', fireContentLoadedEvent, false);
}

qx.$$loader.importPackageData = function (dataMap, callback) {
  if (dataMap["resources"]){
    var resMap = dataMap["resources"];
    for (var k in resMap) qx.$$resources[k] = resMap[k];
  }
  if (dataMap["locales"]){
    var locMap = dataMap["locales"];
    var qxlocs = qx.$$locales;
    for (var lang in locMap){
      if (!qxlocs[lang]) qxlocs[lang] = locMap[lang];
      else 
        for (var k in locMap[lang]) qxlocs[lang][k] = locMap[lang][k];
    }
  }
  if (dataMap["translations"]){
    var trMap   = dataMap["translations"];
    var qxtrans = qx.$$translations;
    for (var lang in trMap){
      if (!qxtrans[lang]) qxtrans[lang] = trMap[lang];
      else 
        for (var k in trMap[lang]) qxtrans[lang][k] = trMap[lang][k];
    }
  }
  if (callback){
    callback(dataMap);
  }
}

qx.$$loader.signalStartup = function () 
{
  qx.$$loader.scriptLoaded = true;
  if (window.qx && qx.event && qx.event.handler && qx.event.handler.Application) {
    qx.event.handler.Application.onScriptLoaded();
    qx.$$loader.applicationHandlerReady = true; 
  } else {
    qx.$$loader.applicationHandlerReady = false;
  }
}

// Load all stuff
qx.$$loader.init = function(){
  var l=qx.$$loader;
  if (l.cssBefore.length>0) {
    for (var i=0, m=l.cssBefore.length; i<m; i++) {
      loadCss(l.cssBefore[i]);
    }
  }
  if (l.urisBefore.length>0){
    loadScriptList(l.urisBefore, function(){
      l.initUris();
    });
  } else {
    l.initUris();
  }
}

// Load qooxdoo boot stuff
qx.$$loader.initUris = function(){
  var l=qx.$$loader;
  var bootPackageHash=l.parts[l.boot][0];
  if (l.bootIsInline){
    l.importPackageData(qx.$$packageData[bootPackageHash]);
    l.signalStartup();
  } else {
    loadScriptList(l.decodeUris(l.packages[l.parts[l.boot][0]].uris), function(){
      // Opera needs this extra time to parse the scripts
      window.setTimeout(function(){
        l.importPackageData(qx.$$packageData[bootPackageHash] || {});
        l.signalStartup();
      }, 0);
    });
  }
}
})();

qx.$$packageData['0']={"locales":{},"resources":{},"translations":{"C":{}}};

(function(){

  var m = ".prototype",k = "function",j = "Boolean",h = "Error",g = "constructor",f = "warn",e = "default",d = "hasOwnProperty",c = "string",b = "toLocaleString",K = "RegExp",J = '\", "',I = "info",H = "BROKEN_IE",G = "isPrototypeOf",F = "Date",E = "qx.Bootstrap",D = "]",C = "Class",B = "error",t = "[Class ",u = "valueOf",r = "Number",s = "debug",p = "ES5",q = "Object",n = '"',o = "",v = "Array",w = "()",y = "String",x = "Function",A = "toString",z = ".";
  if(!window.qx){

    window.qx = {
    };
  };
  qx.Bootstrap = {
    genericToString : function(){

      return t + this.classname + D;
    },
    createNamespace : function(name, L){

      var N = name.split(z);
      var parent = window;
      var M = N[0];
      for(var i = 0,O = N.length - 1;i < O;i++,M = N[i]){

        if(!parent[M]){

          parent = parent[M] = {
          };
        } else {

          parent = parent[M];
        };
      };
      parent[M] = L;
      return M;
    },
    setDisplayName : function(P, Q, name){

      P.displayName = Q + z + name + w;
    },
    setDisplayNames : function(R, S){

      for(var name in R){

        var T = R[name];
        if(T instanceof Function){

          T.displayName = S + z + name + w;
        };
      };
    },
    define : function(name, U){

      if(!U){

        var U = {
          statics : {
          }
        };
      };
      var ba;
      var X = null;
      qx.Bootstrap.setDisplayNames(U.statics, name);
      if(U.members || U.extend){

        qx.Bootstrap.setDisplayNames(U.members, name + m);
        ba = U.construct || new Function;
        if(U.extend){

          this.extendClass(ba, ba, U.extend, name, Y);
        };
        var V = U.statics || {
        };
        for(var i = 0,bb = qx.Bootstrap.getKeys(V),l = bb.length;i < l;i++){

          var bc = bb[i];
          ba[bc] = V[bc];
        };
        X = ba.prototype;
        var W = U.members || {
        };
        for(var i = 0,bb = qx.Bootstrap.getKeys(W),l = bb.length;i < l;i++){

          var bc = bb[i];
          X[bc] = W[bc];
        };
      } else {

        ba = U.statics || {
        };
      };
      var Y = name ? this.createNamespace(name, ba) : o;
      ba.name = ba.classname = name;
      ba.basename = Y;
      ba.$$type = C;
      if(!ba.hasOwnProperty(A)){

        ba.toString = this.genericToString;
      };
      if(U.defer){

        U.defer(ba, X);
      };
      qx.Bootstrap.$$registry[name] = ba;
      return ba;
    }
  };
  qx.Bootstrap.define(E, {
    statics : {
      LOADSTART : qx.$$start || new Date(),
      DEBUG : (function(){

        var bd = true;
        if(qx.$$environment && qx.$$environment["qx.debug"] === false){

          bd = false;
        };
        return bd;
      })(),
      getEnvironmentSetting : function(be){

        if(qx.$$environment){

          return qx.$$environment[be];
        };
      },
      setEnvironmentSetting : function(bf, bg){

        if(!qx.$$environment){

          qx.$$environment = {
          };
        };
        if(qx.$$environment[bf] === undefined){

          qx.$$environment[bf] = bg;
        };
      },
      createNamespace : qx.Bootstrap.createNamespace,
      define : qx.Bootstrap.define,
      setDisplayName : qx.Bootstrap.setDisplayName,
      setDisplayNames : qx.Bootstrap.setDisplayNames,
      genericToString : qx.Bootstrap.genericToString,
      extendClass : function(bh, bi, bj, name, bk){

        var bn = bj.prototype;
        var bm = new Function;
        bm.prototype = bn;
        var bl = new bm;
        bh.prototype = bl;
        bl.name = bl.classname = name;
        bl.basename = bk;
        bi.base = bh.superclass = bj;
        bi.self = bh.constructor = bl.constructor = bh;
      },
      getByName : function(name){

        return qx.Bootstrap.$$registry[name];
      },
      $$registry : {
      },
      objectGetLength : function(bo){

        var length = 0;
        for(var bp in bo){

          length++;
        };
        return length;
      },
      objectMergeWith : function(bq, br, bs){

        if(bs === undefined){

          bs = true;
        };
        for(var bt in br){

          if(bs || bq[bt] === undefined){

            bq[bt] = br[bt];
          };
        };
        return bq;
      },
      __a : [G, d, b, A, u, g],
      getKeys : ({
        "ES5" : Object.keys,
        "BROKEN_IE" : function(bu){

          var bv = [];
          var bx = Object.prototype.hasOwnProperty;
          for(var by in bu){

            if(bx.call(bu, by)){

              bv.push(by);
            };
          };
          var bw = qx.Bootstrap.__a;
          for(var i = 0,a = bw,l = a.length;i < l;i++){

            if(bx.call(bu, a[i])){

              bv.push(a[i]);
            };
          };
          return bv;
        },
        "default" : function(bz){

          var bA = [];
          var bB = Object.prototype.hasOwnProperty;
          for(var bC in bz){

            if(bB.call(bz, bC)){

              bA.push(bC);
            };
          };
          return bA;
        }
      })[typeof (Object.keys) == k ? p : (function(){

        for(var bD in {
          toString : 1
        }){

          return bD;
        };
      })() !== A ? H : e],
      getKeysAsString : function(bE){

        var bF = qx.Bootstrap.getKeys(bE);
        if(bF.length == 0){

          return o;
        };
        return n + bF.join(J) + n;
      },
      __b : {
        "[object String]" : y,
        "[object Array]" : v,
        "[object Object]" : q,
        "[object RegExp]" : K,
        "[object Number]" : r,
        "[object Boolean]" : j,
        "[object Date]" : F,
        "[object Function]" : x,
        "[object Error]" : h
      },
      bind : function(bG, self, bH){

        var bI = Array.prototype.slice.call(arguments, 2, arguments.length);
        return function(){

          var bJ = Array.prototype.slice.call(arguments, 0, arguments.length);
          return bG.apply(self, bI.concat(bJ));
        };
      },
      firstUp : function(bK){

        return bK.charAt(0).toUpperCase() + bK.substr(1);
      },
      firstLow : function(bL){

        return bL.charAt(0).toLowerCase() + bL.substr(1);
      },
      getClass : function(bM){

        var bN = Object.prototype.toString.call(bM);
        return (qx.Bootstrap.__b[bN] || bN.slice(8, -1));
      },
      isString : function(bO){

        return (bO !== null && (typeof bO === c || qx.Bootstrap.getClass(bO) == y || bO instanceof String || (!!bO && !!bO.$$isString)));
      },
      isArray : function(bP){

        return (bP !== null && (bP instanceof Array || (bP && qx.data && qx.data.IListData && qx.util.OOUtil.hasInterface(bP.constructor, qx.data.IListData)) || qx.Bootstrap.getClass(bP) == v || (!!bP && !!bP.$$isArray)));
      },
      isObject : function(bQ){

        return (bQ !== undefined && bQ !== null && qx.Bootstrap.getClass(bQ) == q);
      },
      isFunction : function(bR){

        return qx.Bootstrap.getClass(bR) == x;
      },
      $$logs : [],
      debug : function(bS, bT){

        qx.Bootstrap.$$logs.push([s, arguments]);
      },
      info : function(bU, bV){

        qx.Bootstrap.$$logs.push([I, arguments]);
      },
      warn : function(bW, bX){

        qx.Bootstrap.$$logs.push([f, arguments]);
      },
      error : function(bY, ca){

        qx.Bootstrap.$$logs.push([B, arguments]);
      },
      trace : function(cb){
      }
    }
  });
})();
(function(){

  var o = "qx.Mixin",n = ".prototype",m = "]",k = "constructor",j = "Array",h = "destruct",g = '" in property "',f = "Mixin",e = "[Mixin ",d = '" in member "',a = 'Conflict between mixin "',c = '"!',b = '" and "';
  qx.Bootstrap.define(o, {
    statics : {
      define : function(name, p){

        if(p){

          if(p.include && !(qx.Bootstrap.getClass(p.include) === j)){

            p.include = [p.include];
          };
          {
          };
          var r = p.statics ? p.statics : {
          };
          qx.Bootstrap.setDisplayNames(r, name);
          for(var q in r){

            if(r[q] instanceof Function){

              r[q].$$mixin = r;
            };
          };
          if(p.construct){

            r.$$constructor = p.construct;
            qx.Bootstrap.setDisplayName(p.construct, name, k);
          };
          if(p.include){

            r.$$includes = p.include;
          };
          if(p.properties){

            r.$$properties = p.properties;
          };
          if(p.members){

            r.$$members = p.members;
            qx.Bootstrap.setDisplayNames(p.members, name + n);
          };
          for(var q in r.$$members){

            if(r.$$members[q] instanceof Function){

              r.$$members[q].$$mixin = r;
            };
          };
          if(p.events){

            r.$$events = p.events;
          };
          if(p.destruct){

            r.$$destructor = p.destruct;
            qx.Bootstrap.setDisplayName(p.destruct, name, h);
          };
        } else {

          var r = {
          };
        };
        r.$$type = f;
        r.name = name;
        r.toString = this.genericToString;
        r.basename = qx.Bootstrap.createNamespace(name, r);
        this.$$registry[name] = r;
        return r;
      },
      checkCompatibility : function(s){

        var v = this.flatten(s);
        var w = v.length;
        if(w < 2){

          return true;
        };
        var z = {
        };
        var y = {
        };
        var x = {
        };
        var u;
        for(var i = 0;i < w;i++){

          u = v[i];
          for(var t in u.events){

            if(x[t]){

              throw new Error(a + u.name + b + x[t] + d + t + c);
            };
            x[t] = u.name;
          };
          for(var t in u.properties){

            if(z[t]){

              throw new Error(a + u.name + b + z[t] + g + t + c);
            };
            z[t] = u.name;
          };
          for(var t in u.members){

            if(y[t]){

              throw new Error(a + u.name + b + y[t] + d + t + c);
            };
            y[t] = u.name;
          };
        };
        return true;
      },
      isCompatible : function(A, B){

        var C = qx.util.OOUtil.getMixins(B);
        C.push(A);
        return qx.Mixin.checkCompatibility(C);
      },
      getByName : function(name){

        return this.$$registry[name];
      },
      isDefined : function(name){

        return this.getByName(name) !== undefined;
      },
      getTotalNumber : function(){

        return qx.Bootstrap.objectGetLength(this.$$registry);
      },
      flatten : function(D){

        if(!D){

          return [];
        };
        var E = D.concat();
        for(var i = 0,l = D.length;i < l;i++){

          if(D[i].$$includes){

            E.push.apply(E, this.flatten(D[i].$$includes));
          };
        };
        return E;
      },
      genericToString : function(){

        return e + this.name + m;
      },
      $$registry : {
      },
      __c : null,
      __d : function(){
      }
    }
  });
})();
(function(){

  var a = "qx.util.OOUtil";
  qx.Bootstrap.define(a, {
    statics : {
      classIsDefined : function(name){

        return qx.Bootstrap.getByName(name) !== undefined;
      },
      getPropertyDefinition : function(b, name){

        while(b){

          if(b.$$properties && b.$$properties[name]){

            return b.$$properties[name];
          };
          b = b.superclass;
        };
        return null;
      },
      hasProperty : function(c, name){

        return !!qx.util.OOUtil.getPropertyDefinition(c, name);
      },
      getEventType : function(d, name){

        var d = d.constructor;
        while(d.superclass){

          if(d.$$events && d.$$events[name] !== undefined){

            return d.$$events[name];
          };
          d = d.superclass;
        };
        return null;
      },
      supportsEvent : function(e, name){

        return !!qx.util.OOUtil.getEventType(e, name);
      },
      getByInterface : function(f, g){

        var h,i,l;
        while(f){

          if(f.$$implements){

            h = f.$$flatImplements;
            for(i = 0,l = h.length;i < l;i++){

              if(h[i] === g){

                return f;
              };
            };
          };
          f = f.superclass;
        };
        return null;
      },
      hasInterface : function(j, k){

        return !!qx.util.OOUtil.getByInterface(j, k);
      },
      getMixins : function(m){

        var n = [];
        while(m){

          if(m.$$includes){

            n.push.apply(n, m.$$flatIncludes);
          };
          m = m.superclass;
        };
        return n;
      }
    }
  });
})();
(function(){

  var h = "",g = ".png",f = "wialon.item.MPoi",e = "/avl_poi_image/",d = "undefined",c = "string",b = "/",a = "resource/upload_poi_image";
  qx.Mixin.define(f, {
    members : {
      getPoiImageUrl : function(i, j){

        if(typeof j == d || !j)j = 32;
        return wialon.core.Session.getInstance().getBaseUrl() + e + this.getId() + b + i.id + b + j + b + i.i + g;
      },
      setPoiImage : function(k, l, m){

        if(typeof l == c)return wialon.core.Uploader.getInstance().uploadFiles([], a, {
          fileUrl : l,
          itemId : this.getId(),
          poiId : k.id
        }, m, true); else if(l === null || l === undefined)return wialon.core.Uploader.getInstance().uploadFiles([], a, {
          fileUrl : h,
          itemId : this.getId(),
          poiId : k.id
        }, m, true);;
        return wialon.core.Uploader.getInstance().uploadFiles([l], a, {
          itemId : this.getId(),
          poiId : k.id
        }, m, true);
      }
    }
  });
})();
(function(){

  var cs = "qx.blankpage",cr = "qx.bom.client.Stylesheet.getInsertRule",cq = "qx.bom.client.Html.getDataset",cp = "qx.bom.client.PhoneGap.getPhoneGap",co = '] found, and no default ("default") given',cn = "qx.bom.client.Html.getAudioAif",cm = "qx.bom.client.CssTransform.get3D",cl = ' type)',ck = "qx.bom.client.Xml.getAttributeNS",cj = "qx.bom.client.Stylesheet.getRemoveImport",bt = "qx.bom.client.Css.getUserModify",bs = "qx.bom.client.Css.getFilterGradient",br = "qx.bom.client.Event.getHashChange",bq = "qx.bom.client.Plugin.getWindowsMedia",bp = "qx.bom.client.Html.getVideo",bo = "qx.bom.client.Device.getName",bn = "qx.bom.client.Event.getTouch",bm = "qx.optimization.strings",bl = "qx.debug.property.level",bk = "qx.optimization.variables",cz = "qx.bom.client.EcmaScript.getStackTrace",cA = "qx.bom.client.Xml.getSelectSingleNode",cx = "qx.bom.client.Xml.getImplementation",cy = "qx.bom.client.Html.getConsole",cv = "qx.bom.client.Engine.getVersion",cw = "qx.bom.client.Plugin.getQuicktime",ct = "qx.bom.client.Html.getNaturalDimensions",cu = "qx.bom.client.Xml.getSelectNodes",cB = "qx.bom.client.Xml.getElementsByTagNameNS",cC = "qx.bom.client.Html.getDataUrl",bR = "qx.bom.client.Flash.isAvailable",bQ = "qx.bom.client.Html.getCanvas",bT = "qx.bom.client.Css.getBoxModel",bS = "qx.bom.client.Plugin.getSilverlight",bV = "qx/static/blank.html",bU = "qx.bom.client.Css.getUserSelect",bX = "qx.bom.client.Css.getRadialGradient",bW = "module.property",bP = "qx.bom.client.Plugin.getWindowsMediaVersion",bO = "qx.bom.client.Stylesheet.getCreateStyleSheet",a = 'No match for variant "',b = "qx.bom.client.Locale.getLocale",c = "module.events",d = "module.databinding",e = "qx.bom.client.Html.getFileReader",f = "qx.bom.client.Css.getBorderImage",g = "qx.bom.client.Stylesheet.getDeleteRule",h = "qx.bom.client.Plugin.getDivXVersion",j = "qx.bom.client.Scroll.scrollBarOverlayed",k = "qx.bom.client.Plugin.getPdfVersion",cQ = ":",cP = "qx.bom.client.Css.getLinearGradient",cO = "qx.bom.client.Transport.getXmlHttpRequest",cN = "qx.bom.client.Css.getBorderImageSyntax",cU = "qx.bom.client.Html.getClassList",cT = "qx.bom.client.Event.getHelp",cS = "qx.optimization.comments",cR = "qx.bom.client.Locale.getVariant",cW = "qx.bom.client.Css.getBoxSizing",cV = "qx.bom.client.OperatingSystem.getName",J = "module.logger",K = "qx.bom.client.Css.getOverflowXY",H = "qx.mobile.emulatetouch",I = "qx.bom.client.Html.getAudioWav",N = "qx.bom.client.Browser.getName",O = "qx.bom.client.Css.getInlineBlock",L = "qx.bom.client.Plugin.getPdf",M = "qx.dynlocale",F = '" (',G = "qx.bom.client.Html.getAudio",s = "qx.core.Environment",r = "qx.bom.client.CssTransform.getSupport",u = "qx.bom.client.Html.getTextContent",t = "qx.bom.client.Css.getPlaceholder",o = "qx.bom.client.Css.getFloat",n = ' in variants [',q = "false",p = "qx.bom.client.Css.getBoxShadow",m = "qx.bom.client.Html.getXul",l = "qx.bom.client.Xml.getCreateNode",T = "qxenv",U = "qx.bom.client.Html.getSessionStorage",V = "qx.bom.client.Html.getAudioAu",W = "qx.bom.client.Css.getOpacity",P = "qx.bom.client.Css.getFilterTextShadow",Q = "qx.bom.client.Html.getVml",R = "qx.bom.client.Css.getRgba",S = "qx.bom.client.Transport.getMaxConcurrentRequestCount",X = "qx.bom.client.Css.getBorderRadius",Y = "qx.bom.client.Event.getPointer",C = "qx.bom.client.Transport.getSsl",B = "qx.bom.client.Html.getWebWorker",A = "qx.bom.client.Json.getJson",z = "qx.bom.client.Browser.getQuirksMode",y = "qx.debug.dispose",x = "qx.bom.client.Css.getTextOverflow",w = "qx.bom.client.Xml.getQualifiedItem",v = "qx.bom.client.Html.getVideoOgg",E = "&",D = "qx.bom.client.Device.getType",ba = "qx.bom.client.Browser.getDocumentMode",bb = "qx.allowUrlVariants",bc = "qx.bom.client.Html.getContains",bd = "qx.bom.client.Plugin.getActiveX",be = ".",bf = "qx.bom.client.Xml.getDomProperties",bg = "qx.bom.client.CssAnimation.getSupport",bh = "qx.debug.databinding",bi = "qx.optimization.basecalls",bj = "qx.bom.client.Browser.getVersion",bx = "qx.bom.client.Css.getUserSelectNone",bw = "qx.bom.client.Html.getSvg",bv = "qx.optimization.privates",bu = "qx.bom.client.Plugin.getDivX",bB = "qx.bom.client.Runtime.getName",bA = "qx.bom.client.Html.getLocalStorage",bz = "qx.bom.client.Flash.getStrictSecurityModel",by = "qx.aspects",bD = "qx.debug",bC = "qx.dynamicmousewheel",bK = "qx.bom.client.Html.getAudioMp3",bL = "qx.bom.client.Engine.getName",bI = "qx.bom.client.Html.getUserDataStorage",bJ = "qx.bom.client.Plugin.getGears",bG = "qx.bom.client.Plugin.getQuicktimeVersion",bH = "qx.bom.client.Html.getAudioOgg",bE = "qx.bom.client.Css.getTextShadow",bF = "qx.bom.client.Plugin.getSilverlightVersion",bM = "qx.bom.client.Html.getCompareDocumentPosition",bN = "qx.bom.client.Flash.getExpressInstall",cc = "qx.bom.client.OperatingSystem.getVersion",cb = "qx.bom.client.Html.getXPath",ce = "qx.bom.client.Html.getGeoLocation",cd = "qx.bom.client.Css.getAppearance",cg = "qx.mobile.nativescroll",cf = "qx.bom.client.Xml.getDomParser",ci = "qx.bom.client.Stylesheet.getAddImport",ch = "qx.optimization.variants",ca = "qx.bom.client.Html.getVideoWebm",bY = "qx.bom.client.Flash.getVersion",cJ = "qx.bom.client.Css.getLegacyWebkitGradient",cK = "qx.bom.client.PhoneGap.getNotification",cL = "qx.bom.client.Html.getVideoH264",cM = "qx.bom.client.Xml.getCreateElementNS",cF = "qx.core.Environment for a list of predefined keys.",cG = " is not a valid key. Please see the API-doc of ",cH = "default",cI = "|",cD = "true",cE = "qx.allowUrlSettings";
  qx.Bootstrap.define(s, {
    statics : {
      _checks : {
      },
      _asyncChecks : {
      },
      __e : {
      },
      _checksMap : {
        "engine.version" : cv,
        "engine.name" : bL,
        "browser.name" : N,
        "browser.version" : bj,
        "browser.documentmode" : ba,
        "browser.quirksmode" : z,
        "runtime.name" : bB,
        "device.name" : bo,
        "device.type" : D,
        "locale" : b,
        "locale.variant" : cR,
        "os.name" : cV,
        "os.version" : cc,
        "os.scrollBarOverlayed" : j,
        "plugin.gears" : bJ,
        "plugin.activex" : bd,
        "plugin.quicktime" : cw,
        "plugin.quicktime.version" : bG,
        "plugin.windowsmedia" : bq,
        "plugin.windowsmedia.version" : bP,
        "plugin.divx" : bu,
        "plugin.divx.version" : h,
        "plugin.silverlight" : bS,
        "plugin.silverlight.version" : bF,
        "plugin.flash" : bR,
        "plugin.flash.version" : bY,
        "plugin.flash.express" : bN,
        "plugin.flash.strictsecurity" : bz,
        "plugin.pdf" : L,
        "plugin.pdf.version" : k,
        "io.maxrequests" : S,
        "io.ssl" : C,
        "io.xhr" : cO,
        "event.touch" : bn,
        "event.pointer" : Y,
        "event.help" : cT,
        "event.hashchange" : br,
        "ecmascript.stacktrace" : cz,
        "html.webworker" : B,
        "html.filereader" : e,
        "html.geolocation" : ce,
        "html.audio" : G,
        "html.audio.ogg" : bH,
        "html.audio.mp3" : bK,
        "html.audio.wav" : I,
        "html.audio.au" : V,
        "html.audio.aif" : cn,
        "html.video" : bp,
        "html.video.ogg" : v,
        "html.video.h264" : cL,
        "html.video.webm" : ca,
        "html.storage.local" : bA,
        "html.storage.session" : U,
        "html.storage.userdata" : bI,
        "html.classlist" : cU,
        "html.xpath" : cb,
        "html.xul" : m,
        "html.canvas" : bQ,
        "html.svg" : bw,
        "html.vml" : Q,
        "html.dataset" : cq,
        "html.dataurl" : cC,
        "html.console" : cy,
        "html.stylesheet.createstylesheet" : bO,
        "html.stylesheet.insertrule" : cr,
        "html.stylesheet.deleterule" : g,
        "html.stylesheet.addimport" : ci,
        "html.stylesheet.removeimport" : cj,
        "html.element.contains" : bc,
        "html.element.compareDocumentPosition" : bM,
        "html.element.textcontent" : u,
        "html.image.naturaldimensions" : ct,
        "json" : A,
        "css.textoverflow" : x,
        "css.placeholder" : t,
        "css.borderradius" : X,
        "css.borderimage" : f,
        "css.borderimage.standardsyntax" : cN,
        "css.boxshadow" : p,
        "css.gradient.linear" : cP,
        "css.gradient.filter" : bs,
        "css.gradient.radial" : bX,
        "css.gradient.legacywebkit" : cJ,
        "css.boxmodel" : bT,
        "css.rgba" : R,
        "css.userselect" : bU,
        "css.userselect.none" : bx,
        "css.usermodify" : bt,
        "css.appearance" : cd,
        "css.float" : o,
        "css.boxsizing" : cW,
        "css.animation" : bg,
        "css.transform" : r,
        "css.transform.3d" : cm,
        "css.inlineblock" : O,
        "css.opacity" : W,
        "css.overflowxy" : K,
        "css.textShadow" : bE,
        "css.textShadow.filter" : P,
        "phonegap" : cp,
        "phonegap.notification" : cK,
        "xml.implementation" : cx,
        "xml.domparser" : cf,
        "xml.selectsinglenode" : cA,
        "xml.selectnodes" : cu,
        "xml.getelementsbytagnamens" : cB,
        "xml.domproperties" : bf,
        "xml.attributens" : ck,
        "xml.createnode" : l,
        "xml.getqualifieditem" : w,
        "xml.createelementns" : cM
      },
      get : function(cX){

        if(this.__e[cX] != undefined){

          return this.__e[cX];
        };
        var db = this._checks[cX];
        if(db){

          var dc = db();
          this.__e[cX] = dc;
          return dc;
        };
        var da = this._getClassNameFromEnvKey(cX);
        if(da[0] != undefined){

          var dd = da[0];
          var cY = da[1];
          var dc = dd[cY]();
          this.__e[cX] = dc;
          return dc;
        };
        if(qx.Bootstrap.DEBUG){

          qx.Bootstrap.warn(cX + cG + cF);
          qx.Bootstrap.trace(this);
        };
      },
      _getClassNameFromEnvKey : function(de){

        var dk = this._checksMap;
        if(dk[de] != undefined){

          var dg = dk[de];
          var dj = dg.lastIndexOf(be);
          if(dj > -1){

            var di = dg.slice(0, dj);
            var df = dg.slice(dj + 1);
            var dh = qx.Bootstrap.getByName(di);
            if(dh != undefined){

              return [dh, df];
            };
          };
        };
        return [undefined, undefined];
      },
      getAsync : function(dl, dm, self){

        var dr = this;
        if(this.__e[dl] != undefined){

          window.setTimeout(function(){

            dm.call(self, dr.__e[dl]);
          }, 0);
          return;
        };
        var dq = this._asyncChecks[dl];
        if(dq){

          dq(function(dt){

            dr.__e[dl] = dt;
            dm.call(self, dt);
          });
          return;
        };
        var dp = this._getClassNameFromEnvKey(dl);
        if(dp[0] != undefined){

          var ds = dp[0];
          var dn = dp[1];
          ds[dn](function(du){

            dr.__e[dl] = du;
            dm.call(self, du);
          });
          return;
        };
        if(qx.Bootstrap.DEBUG){

          qx.Bootstrap.warn(dl + cG + cF);
          qx.Bootstrap.trace(this);
        };
      },
      select : function(dv, dw){

        return this.__f(this.get(dv), dw);
      },
      selectAsync : function(dx, dy, self){

        this.getAsync(dx, function(dz){

          var dA = this.__f(dx, dy);
          dA.call(self, dz);
        }, this);
      },
      __f : function(dB, dC){

        var dE = dC[dB];
        if(dC.hasOwnProperty(dB)){

          return dE;
        };
        for(var dD in dC){

          if(dD.indexOf(cI) != -1){

            var dF = dD.split(cI);
            for(var i = 0;i < dF.length;i++){

              if(dF[i] == dB){

                return dC[dD];
              };
            };
          };
        };
        if(dC[cH] !== undefined){

          return dC[cH];
        };
        if(qx.Bootstrap.DEBUG){

          throw new Error(a + dB + F + (typeof dB) + cl + n + qx.Bootstrap.getKeysAsString(dC) + co);
        };
      },
      filter : function(dG){

        var dI = [];
        for(var dH in dG){

          if(this.get(dH)){

            dI.push(dG[dH]);
          };
        };
        return dI;
      },
      invalidateCacheKey : function(dJ){

        delete this.__e[dJ];
      },
      add : function(dK, dL){

        if(this._checks[dK] == undefined){

          if(dL instanceof Function){

            this._checks[dK] = dL;
          } else {

            this._checks[dK] = this.__i(dL);
          };
        };
      },
      addAsync : function(dM, dN){

        if(this._checks[dM] == undefined){

          this._asyncChecks[dM] = dN;
        };
      },
      getChecks : function(){

        return this._checks;
      },
      getAsyncChecks : function(){

        return this._asyncChecks;
      },
      _initDefaultQxValues : function(){

        this.add(cD, function(){

          return true;
        });
        this.add(cE, function(){

          return false;
        });
        this.add(bb, function(){

          return false;
        });
        this.add(bl, function(){

          return 0;
        });
        this.add(bD, function(){

          return true;
        });
        this.add(by, function(){

          return false;
        });
        this.add(M, function(){

          return true;
        });
        this.add(H, function(){

          return false;
        });
        this.add(cg, function(){

          return false;
        });
        this.add(cs, function(){

          return bV;
        });
        this.add(bC, function(){

          return true;
        });
        this.add(bh, function(){

          return false;
        });
        this.add(y, function(){

          return false;
        });
        this.add(bi, function(){

          return false;
        });
        this.add(cS, function(){

          return false;
        });
        this.add(bv, function(){

          return false;
        });
        this.add(bm, function(){

          return false;
        });
        this.add(bk, function(){

          return false;
        });
        this.add(ch, function(){

          return false;
        });
        this.add(d, function(){

          return true;
        });
        this.add(J, function(){

          return true;
        });
        this.add(bW, function(){

          return true;
        });
        this.add(c, function(){

          return true;
        });
      },
      __g : function(){

        if(qx && qx.$$environment){

          for(var dP in qx.$$environment){

            var dO = qx.$$environment[dP];
            this._checks[dP] = this.__i(dO);
          };
        };
      },
      __h : function(){

        if(window.document && window.document.location){

          var dQ = window.document.location.search.slice(1).split(E);
          for(var i = 0;i < dQ.length;i++){

            var dS = dQ[i].split(cQ);
            if(dS.length != 3 || dS[0] != T){

              continue;
            };
            var dT = dS[1];
            var dR = decodeURIComponent(dS[2]);
            if(dR == cD){

              dR = true;
            } else if(dR == q){

              dR = false;
            } else if(/^(\d|\.)+$/.test(dR)){

              dR = parseFloat(dR);
            };;
            this._checks[dT] = this.__i(dR);
          };
        };
      },
      __i : function(dU){

        return qx.Bootstrap.bind(function(dV){

          return dV;
        }, null, dU);
      }
    },
    defer : function(dW){

      dW._initDefaultQxValues();
      dW.__g();
      if(dW.get(cE) === true){

        dW.__h();
      };
    }
  });
})();
(function(){

  var d = "qx.core.Aspect",c = "before",b = "*",a = "static";
  qx.Bootstrap.define(d, {
    statics : {
      __j : [],
      wrap : function(e, f, g){

        var m = [];
        var h = [];
        var l = this.__j;
        var k;
        for(var i = 0;i < l.length;i++){

          k = l[i];
          if((k.type == null || g == k.type || k.type == b) && (k.name == null || e.match(k.name))){

            k.pos == -1 ? m.push(k.fcn) : h.push(k.fcn);
          };
        };
        if(m.length === 0 && h.length === 0){

          return f;
        };
        var j = function(){

          for(var i = 0;i < m.length;i++){

            m[i].call(this, e, f, g, arguments);
          };
          var n = f.apply(this, arguments);
          for(var i = 0;i < h.length;i++){

            h[i].call(this, e, f, g, arguments, n);
          };
          return n;
        };
        if(g !== a){

          j.self = f.self;
          j.base = f.base;
        };
        f.wrapper = j;
        j.original = f;
        return j;
      },
      addAdvice : function(o, p, q, name){

        this.__j.push({
          fcn : o,
          pos : p === c ? -1 : 1,
          type : q,
          name : name
        });
      }
    }
  });
})();
(function(){

  var t = 'Implementation of method "',s = "function",r = "Boolean",q = "qx.Interface",p = 'The event "',o = 'The property "',n = "Interface",m = "toggle",k = "]",j = "[Interface ",c = "is",h = "Array",f = 'Implementation of member "',b = '"',a = '" is not supported by Class "',e = '" required by interface "',d = '" is missing in class "',g = '"!';
  qx.Bootstrap.define(q, {
    statics : {
      define : function(name, u){

        if(u){

          if(u.extend && !(qx.Bootstrap.getClass(u.extend) === h)){

            u.extend = [u.extend];
          };
          {
          };
          var v = u.statics ? u.statics : {
          };
          if(u.extend){

            v.$$extends = u.extend;
          };
          if(u.properties){

            v.$$properties = u.properties;
          };
          if(u.members){

            v.$$members = u.members;
          };
          if(u.events){

            v.$$events = u.events;
          };
        } else {

          var v = {
          };
        };
        v.$$type = n;
        v.name = name;
        v.toString = this.genericToString;
        v.basename = qx.Bootstrap.createNamespace(name, v);
        qx.Interface.$$registry[name] = v;
        return v;
      },
      getByName : function(name){

        return this.$$registry[name];
      },
      isDefined : function(name){

        return this.getByName(name) !== undefined;
      },
      getTotalNumber : function(){

        return qx.Bootstrap.objectGetLength(this.$$registry);
      },
      flatten : function(w){

        if(!w){

          return [];
        };
        var x = w.concat();
        for(var i = 0,l = w.length;i < l;i++){

          if(w[i].$$extends){

            x.push.apply(x, this.flatten(w[i].$$extends));
          };
        };
        return x;
      },
      __k : function(y, z, A, B){

        var F = A.$$members;
        if(F){

          for(var E in F){

            if(qx.Bootstrap.isFunction(F[E])){

              var D = this.__l(z, E);
              var C = D || qx.Bootstrap.isFunction(y[E]);
              if(!C){

                throw new Error(t + E + d + z.classname + e + A.name + b);
              };
              var G = B === true && !D && !qx.util.OOUtil.hasInterface(z, A);
              if(G){

                y[E] = this.__o(A, y[E], E, F[E]);
              };
            } else {

              if(typeof y[E] === undefined){

                if(typeof y[E] !== s){

                  throw new Error(f + E + d + z.classname + e + A.name + b);
                };
              };
            };
          };
        };
      },
      __l : function(H, I){

        var M = I.match(/^(is|toggle|get|set|reset)(.*)$/);
        if(!M){

          return false;
        };
        var J = qx.Bootstrap.firstLow(M[2]);
        var K = qx.util.OOUtil.getPropertyDefinition(H, J);
        if(!K){

          return false;
        };
        var L = M[0] == c || M[0] == m;
        if(L){

          return qx.util.OOUtil.getPropertyDefinition(H, J).check == r;
        };
        return true;
      },
      __m : function(N, O){

        if(O.$$properties){

          for(var P in O.$$properties){

            if(!qx.util.OOUtil.getPropertyDefinition(N, P)){

              throw new Error(o + P + a + N.classname + g);
            };
          };
        };
      },
      __n : function(Q, R){

        if(R.$$events){

          for(var S in R.$$events){

            if(!qx.util.OOUtil.supportsEvent(Q, S)){

              throw new Error(p + S + a + Q.classname + g);
            };
          };
        };
      },
      assertObject : function(T, U){

        var W = T.constructor;
        this.__k(T, W, U, false);
        this.__m(W, U);
        this.__n(W, U);
        var V = U.$$extends;
        if(V){

          for(var i = 0,l = V.length;i < l;i++){

            this.assertObject(T, V[i]);
          };
        };
      },
      assert : function(X, Y, ba){

        this.__k(X.prototype, X, Y, ba);
        this.__m(X, Y);
        this.__n(X, Y);
        var bb = Y.$$extends;
        if(bb){

          for(var i = 0,l = bb.length;i < l;i++){

            this.assert(X, bb[i], ba);
          };
        };
      },
      genericToString : function(){

        return j + this.name + k;
      },
      $$registry : {
      },
      __o : function(){
      },
      __c : null,
      __d : function(){
      }
    }
  });
})();
(function(){

  var g = "qx.lang.Core",f = "\\\\",e = "\\\"",d = '"',c = "[object Error]",b = "emulated",a = "native";
  qx.Bootstrap.define(g, {
    statics : {
      errorToString : {
        "native" : Error.prototype.toString,
        "emulated" : function(){

          return this.message;
        }
      }[(!Error.prototype.toString || Error.prototype.toString() == c) ? b : a],
      arrayIndexOf : {
        "native" : Array.prototype.indexOf,
        "emulated" : function(h, j){

          if(j == null){

            j = 0;
          } else if(j < 0){

            j = Math.max(0, this.length + j);
          };
          for(var i = j;i < this.length;i++){

            if(this[i] === h){

              return i;
            };
          };
          return -1;
        }
      }[Array.prototype.indexOf ? a : b],
      arrayLastIndexOf : {
        "native" : Array.prototype.lastIndexOf,
        "emulated" : function(k, m){

          if(m == null){

            m = this.length - 1;
          } else if(m < 0){

            m = Math.max(0, this.length + m);
          };
          for(var i = m;i >= 0;i--){

            if(this[i] === k){

              return i;
            };
          };
          return -1;
        }
      }[Array.prototype.lastIndexOf ? a : b],
      arrayForEach : {
        "native" : Array.prototype.forEach,
        "emulated" : function(n, o){

          var l = this.length;
          for(var i = 0;i < l;i++){

            var p = this[i];
            if(p !== undefined){

              n.call(o || window, p, i, this);
            };
          };
        }
      }[Array.prototype.forEach ? a : b],
      arrayFilter : {
        "native" : Array.prototype.filter,
        "emulated" : function(q, r){

          var s = [];
          var l = this.length;
          for(var i = 0;i < l;i++){

            var t = this[i];
            if(t !== undefined){

              if(q.call(r || window, t, i, this)){

                s.push(this[i]);
              };
            };
          };
          return s;
        }
      }[Array.prototype.filter ? a : b],
      arrayMap : {
        "native" : Array.prototype.map,
        "emulated" : function(u, v){

          var w = [];
          var l = this.length;
          for(var i = 0;i < l;i++){

            var x = this[i];
            if(x !== undefined){

              w[i] = u.call(v || window, x, i, this);
            };
          };
          return w;
        }
      }[Array.prototype.map ? a : b],
      arraySome : {
        "native" : Array.prototype.some,
        "emulated" : function(y, z){

          var l = this.length;
          for(var i = 0;i < l;i++){

            var A = this[i];
            if(A !== undefined){

              if(y.call(z || window, A, i, this)){

                return true;
              };
            };
          };
          return false;
        }
      }[Array.prototype.some ? a : b],
      arrayEvery : {
        "native" : Array.prototype.every,
        "emulated" : function(B, C){

          var l = this.length;
          for(var i = 0;i < l;i++){

            var D = this[i];
            if(D !== undefined){

              if(!B.call(C || window, D, i, this)){

                return false;
              };
            };
          };
          return true;
        }
      }[Array.prototype.every ? a : b],
      stringQuote : {
        "native" : String.prototype.quote,
        "emulated" : function(){

          return d + this.replace(/\\/g, f).replace(/\"/g, e) + d;
        }
      }[String.prototype.quote ? a : b]
    }
  });
  if(!Error.prototype.toString || Error.prototype.toString() == c){

    Error.prototype.toString = qx.lang.Core.errorToString;
  };
  if(!Array.prototype.indexOf){

    Array.prototype.indexOf = qx.lang.Core.arrayIndexOf;
  };
  if(!Array.prototype.lastIndexOf){

    Array.prototype.lastIndexOf = qx.lang.Core.arrayLastIndexOf;
  };
  if(!Array.prototype.forEach){

    Array.prototype.forEach = qx.lang.Core.arrayForEach;
  };
  if(!Array.prototype.filter){

    Array.prototype.filter = qx.lang.Core.arrayFilter;
  };
  if(!Array.prototype.map){

    Array.prototype.map = qx.lang.Core.arrayMap;
  };
  if(!Array.prototype.some){

    Array.prototype.some = qx.lang.Core.arraySome;
  };
  if(!Array.prototype.every){

    Array.prototype.every = qx.lang.Core.arrayEvery;
  };
  if(!String.prototype.quote){

    String.prototype.quote = qx.lang.Core.stringQuote;
  };
})();
(function(){

  var bC = 'qx.lang.Type.isString(value) && qx.util.ColorUtil.isValidPropertyValue(value)',bB = 'value !== null && qx.theme.manager.Font.getInstance().isDynamic(value)',bA = 'value !== null && value.nodeType === 9 && value.documentElement',bz = 'value !== null && value.$$type === "Mixin"',by = 'return init;',bx = 'var init=this.',bw = 'value !== null && value.nodeType === 1 && value.attributes',bv = "var parent = this.getLayoutParent();",bu = "Error in property ",bt = 'qx.core.Assert.assertInstance(value, Date, msg) || true',bi = "if (!parent) return;",bh = " in method ",bg = 'qx.core.Assert.assertInstance(value, Error, msg) || true',bf = 'Undefined value is not allowed!',be = "inherit",bd = 'Is invalid!',bc = "MSIE 6.0",bb = "': ",ba = " of class ",Y = 'value !== null && value.nodeType !== undefined',bJ = 'value !== null && qx.theme.manager.Decoration.getInstance().isValidPropertyValue(value)',bK = "module.events",bH = 'qx.core.Assert.assertPositiveInteger(value, msg) || true',bI = 'if(init==qx.core.Property.$$inherit)init=null;',bF = 'value !== null && value.$$type === "Interface"',bG = 'var inherit=prop.$$inherit;',bD = "var value = parent.",bE = "$$useinit_",bL = "(value);",bM = 'Requires exactly one argument!',bm = "$$runtime_",bl = "$$user_",bo = 'qx.core.Assert.assertArray(value, msg) || true',bn = 'qx.core.Assert.assertPositiveNumber(value, msg) || true',bq = "Boolean",bp = 'return value;',bs = 'if(init==qx.core.Property.$$inherit)throw new Error("Inheritable property ',br = 'Does not allow any arguments!',bk = "()",bj = "var a=arguments[0] instanceof Array?arguments[0]:arguments;",b = 'value !== null && value.$$type === "Theme"',c = "())",d = 'return null;',e = 'qx.core.Assert.assertObject(value, msg) || true',f = 'qx.core.Assert.assertString(value, msg) || true',g = "if (value===undefined) value = parent.",h = 'value !== null && value.$$type === "Class"',j = 'qx.core.Assert.assertFunction(value, msg) || true',k = "object",m = "$$init_",bQ = "$$theme_",bP = "Unknown reason: ",bO = 'qx.core.Assert.assertMap(value, msg) || true',bN = 'qx.core.Assert.assertNumber(value, msg) || true',bU = 'Null value is not allowed!',bT = 'qx.core.Assert.assertInteger(value, msg) || true',bS = "rv:1.8.1",bR = "shorthand",bW = 'qx.core.Assert.assertInstance(value, RegExp, msg) || true',bV = 'value !== null && value.type !== undefined',I = 'value !== null && value.document',J = 'throw new Error("Property ',G = "(!this.",H = 'qx.core.Assert.assertBoolean(value, msg) || true',M = "toggle",N = "$$inherit_",K = " with incoming value '",L = "a=qx.lang.Array.fromShortHand(qx.lang.Array.fromArguments(a));",E = "qx.core.Property",F = "is",u = 'Could not change or apply init value after constructing phase!',t = "();",w = 'else ',v = 'if(this.',q = "resetRuntime",p = "return this.",s = "get",r = ";",o = "(a[",n = ' of an instance of ',S = "refresh",T = ' is not (yet) ready!");',U = "]);",V = "resetThemed",O = 'else if(this.',P = "reset",Q = "setRuntime",R = "init",W = "set",X = "setThemed",D = '!==undefined)',C = "this.",B = "",A = 'return this.',z = "string",y = "boolean",x = ';';
  qx.Bootstrap.define(E, {
    statics : {
      __p : function(){

        if(qx.core.Environment.get(bK)){

          qx.event.type.Data;
          qx.event.dispatch.Direct;
        };
      },
      __q : {
        "Boolean" : H,
        "String" : f,
        "Number" : bN,
        "Integer" : bT,
        "PositiveNumber" : bn,
        "PositiveInteger" : bH,
        "Error" : bg,
        "RegExp" : bW,
        "Object" : e,
        "Array" : bo,
        "Map" : bO,
        "Function" : j,
        "Date" : bt,
        "Node" : Y,
        "Element" : bw,
        "Document" : bA,
        "Window" : I,
        "Event" : bV,
        "Class" : h,
        "Mixin" : bz,
        "Interface" : bF,
        "Theme" : b,
        "Color" : bC,
        "Decorator" : bJ,
        "Font" : bB
      },
      __r : {
        "Node" : true,
        "Element" : true,
        "Document" : true,
        "Window" : true,
        "Event" : true
      },
      $$inherit : be,
      $$store : {
        runtime : {
        },
        user : {
        },
        theme : {
        },
        inherit : {
        },
        init : {
        },
        useinit : {
        }
      },
      $$method : {
        get : {
        },
        set : {
        },
        reset : {
        },
        init : {
        },
        refresh : {
        },
        setRuntime : {
        },
        resetRuntime : {
        },
        setThemed : {
        },
        resetThemed : {
        }
      },
      $$allowedKeys : {
        name : z,
        dereference : y,
        inheritable : y,
        nullable : y,
        themeable : y,
        refine : y,
        init : null,
        apply : z,
        event : z,
        check : null,
        transform : z,
        deferredInit : y,
        validate : null
      },
      $$allowedGroupKeys : {
        name : z,
        group : k,
        mode : z,
        themeable : y
      },
      $$inheritable : {
      },
      __s : function(bX){

        var bY = this.__t(bX);
        if(!bY.length){

          var ca = function(){
          };
        } else {

          ca = this.__u(bY);
        };
        bX.prototype.$$refreshInheritables = ca;
      },
      __t : function(cb){

        var cd = [];
        while(cb){

          var cc = cb.$$properties;
          if(cc){

            for(var name in this.$$inheritable){

              if(cc[name] && cc[name].inheritable){

                cd.push(name);
              };
            };
          };
          cb = cb.superclass;
        };
        return cd;
      },
      __u : function(ce){

        var ci = this.$$store.inherit;
        var ch = this.$$store.init;
        var cg = this.$$method.refresh;
        var cf = [bv, bi];
        for(var i = 0,l = ce.length;i < l;i++){

          var name = ce[i];
          cf.push(bD, ci[name], r, g, ch[name], r, C, cg[name], bL);
        };
        return new Function(cf.join(B));
      },
      attachRefreshInheritables : function(cj){

        cj.prototype.$$refreshInheritables = function(){

          qx.core.Property.__s(cj);
          return this.$$refreshInheritables();
        };
      },
      attachMethods : function(ck, name, cl){

        cl.group ? this.__v(ck, cl, name) : this.__w(ck, cl, name);
      },
      __v : function(cm, cn, name){

        var cu = qx.Bootstrap.firstUp(name);
        var ct = cm.prototype;
        var cv = cn.themeable === true;
        {
        };
        var cw = [];
        var cq = [];
        if(cv){

          var co = [];
          var cs = [];
        };
        var cr = bj;
        cw.push(cr);
        if(cv){

          co.push(cr);
        };
        if(cn.mode == bR){

          var cp = L;
          cw.push(cp);
          if(cv){

            co.push(cp);
          };
        };
        for(var i = 0,a = cn.group,l = a.length;i < l;i++){

          {
          };
          cw.push(C, this.$$method.set[a[i]], o, i, U);
          cq.push(C, this.$$method.reset[a[i]], t);
          if(cv){

            {
            };
            co.push(C, this.$$method.setThemed[a[i]], o, i, U);
            cs.push(C, this.$$method.resetThemed[a[i]], t);
          };
        };
        this.$$method.set[name] = W + cu;
        ct[this.$$method.set[name]] = new Function(cw.join(B));
        this.$$method.reset[name] = P + cu;
        ct[this.$$method.reset[name]] = new Function(cq.join(B));
        if(cv){

          this.$$method.setThemed[name] = X + cu;
          ct[this.$$method.setThemed[name]] = new Function(co.join(B));
          this.$$method.resetThemed[name] = V + cu;
          ct[this.$$method.resetThemed[name]] = new Function(cs.join(B));
        };
      },
      __w : function(cx, cy, name){

        var cA = qx.Bootstrap.firstUp(name);
        var cC = cx.prototype;
        {
        };
        if(cy.dereference === undefined && typeof cy.check === z){

          cy.dereference = this.__x(cy.check);
        };
        var cB = this.$$method;
        var cz = this.$$store;
        cz.runtime[name] = bm + name;
        cz.user[name] = bl + name;
        cz.theme[name] = bQ + name;
        cz.init[name] = m + name;
        cz.inherit[name] = N + name;
        cz.useinit[name] = bE + name;
        cB.get[name] = s + cA;
        cC[cB.get[name]] = function(){

          return qx.core.Property.executeOptimizedGetter(this, cx, name, s);
        };
        cB.set[name] = W + cA;
        cC[cB.set[name]] = function(cD){

          return qx.core.Property.executeOptimizedSetter(this, cx, name, W, arguments);
        };
        cB.reset[name] = P + cA;
        cC[cB.reset[name]] = function(){

          return qx.core.Property.executeOptimizedSetter(this, cx, name, P);
        };
        if(cy.inheritable || cy.apply || cy.event || cy.deferredInit){

          cB.init[name] = R + cA;
          cC[cB.init[name]] = function(cE){

            return qx.core.Property.executeOptimizedSetter(this, cx, name, R, arguments);
          };
        };
        if(cy.inheritable){

          cB.refresh[name] = S + cA;
          cC[cB.refresh[name]] = function(cF){

            return qx.core.Property.executeOptimizedSetter(this, cx, name, S, arguments);
          };
        };
        cB.setRuntime[name] = Q + cA;
        cC[cB.setRuntime[name]] = function(cG){

          return qx.core.Property.executeOptimizedSetter(this, cx, name, Q, arguments);
        };
        cB.resetRuntime[name] = q + cA;
        cC[cB.resetRuntime[name]] = function(){

          return qx.core.Property.executeOptimizedSetter(this, cx, name, q);
        };
        if(cy.themeable){

          cB.setThemed[name] = X + cA;
          cC[cB.setThemed[name]] = function(cH){

            return qx.core.Property.executeOptimizedSetter(this, cx, name, X, arguments);
          };
          cB.resetThemed[name] = V + cA;
          cC[cB.resetThemed[name]] = function(){

            return qx.core.Property.executeOptimizedSetter(this, cx, name, V);
          };
        };
        if(cy.check === bq){

          cC[M + cA] = new Function(p + cB.set[name] + G + cB.get[name] + c);
          cC[F + cA] = new Function(p + cB.get[name] + bk);
        };
      },
      __x : function(cI){

        return !!this.__r[cI];
      },
      __y : function(cJ){

        return this.__r[cJ] || qx.util.OOUtil.classIsDefined(cJ) || (qx.Interface && qx.Interface.isDefined(cJ));
      },
      __z : {
        '0' : u,
        '1' : bM,
        '2' : bf,
        '3' : br,
        '4' : bU,
        '5' : bd
      },
      error : function(cK, cL, cM, cN, cO){

        var cP = cK.constructor.classname;
        var cQ = bu + cM + ba + cP + bh + this.$$method[cN][cM] + K + cO + bb;
        throw new Error(cQ + (this.__z[cL] || bP + cL));
      },
      __A : function(cR, cS, name, cT, cU, cV){

        var cW = this.$$method[cT][name];
        
        cS[cW] = new Function("value", cU.join(""));
        if(qx.core.Environment.get("qx.aspects")){

          cS[cW] = qx.core.Aspect.wrap(cR.classname + "." + cW, cS[cW], "property");
        };
        qx.Bootstrap.setDisplayName(cS[cW], cR.classname + ".prototype", cW);
        if(cV === undefined){

          return cR[cW]();
        } else 
        return cR[cW](cV[0]);;
      },
      executeOptimizedGetter : function(cX, cY, name, da){

        var dc = cY.$$properties[name];
        var de = cY.prototype;
        var db = [];
        var dd = this.$$store;
        db.push(v, dd.runtime[name], D);
        db.push(A, dd.runtime[name], x);
        if(dc.inheritable){

          db.push(O, dd.inherit[name], D);
          db.push(A, dd.inherit[name], x);
          db.push(w);
        };
        db.push(v, dd.user[name], D);
        db.push(A, dd.user[name], x);
        if(dc.themeable){

          db.push(O, dd.theme[name], D);
          db.push(A, dd.theme[name], x);
        };
        if(dc.deferredInit && dc.init === undefined){

          db.push(O, dd.init[name], D);
          db.push(A, dd.init[name], x);
        };
        db.push(w);
        if(dc.init !== undefined){

          if(dc.inheritable){

            db.push(bx, dd.init[name], x);
            if(dc.nullable){

              db.push(bI);
            } else if(dc.init !== undefined){

              db.push(A, dd.init[name], x);
            } else {

              db.push(bs, name, n, cY.classname, T);
            };
            db.push(by);
          } else {

            db.push(A, dd.init[name], x);
          };
        } else if(dc.inheritable || dc.nullable){

          db.push(d);
        } else {

          db.push(J, name, n, cY.classname, T);
        };
        return this.__A(cX, de, name, da, db);
      },
      executeOptimizedSetter : function(df, dg, name, dh, di){

        var dn = dg.$$properties[name];
        var dm = dg.prototype;
        var dk = [];
        var dj = dh === W || dh === X || dh === Q || (dh === R && dn.init === undefined);
        var dl = dn.apply || dn.event || dn.inheritable;
        var dp = this.__B(dh, name);
        this.__C(dk, dn, name, dh, dj);
        if(dj){

          this.__D(dk, dg, dn, name);
        };
        if(dl){

          this.__E(dk, dj, dp, dh);
        };
        if(dn.inheritable){

          dk.push(bG);
        };
        {
        };
        if(!dl){

          this.__G(dk, name, dh, dj);
        } else {

          this.__H(dk, dn, name, dh, dj);
        };
        if(dn.inheritable){

          this.__I(dk, dn, name, dh);
        } else if(dl){

          this.__J(dk, dn, name, dh);
        };
        if(dl){

          this.__K(dk, dn, name);
          if(dn.inheritable && dm._getChildren){

            this.__L(dk, name);
          };
        };
        if(dj){

          dk.push(bp);
        };
        return this.__A(df, dm, name, dh, dk, di);
      },
      __B : function(dq, name){

        if(dq === "setRuntime" || dq === "resetRuntime"){

          var dr = this.$$store.runtime[name];
        } else if(dq === "setThemed" || dq === "resetThemed"){

          dr = this.$$store.theme[name];
        } else if(dq === "init"){

          dr = this.$$store.init[name];
        } else {

          dr = this.$$store.user[name];
        };;
        return dr;
      },
      __C : function(ds, dt, name, du, dv){

        
        if(!dt.nullable || dt.check || dt.inheritable){

          ds.push('var prop=qx.core.Property;');
        };
        if(du === "set"){

          ds.push('if(value===undefined)prop.error(this,2,"', name, '","', du, '",value);');
        };
      },
      __D : function(dw, dx, dy, name){

        if(dy.transform){

          dw.push('value=this.', dy.transform, '(value);');
        };
        if(dy.validate){

          if(typeof dy.validate === "string"){

            dw.push('this.', dy.validate, '(value);');
          } else if(dy.validate instanceof Function){

            dw.push(dx.classname, '.$$properties.', name);
            dw.push('.validate.call(this, value);');
          };
        };
      },
      __E : function(dz, dA, dB, dC){

        var dD = (dC === "reset" || dC === "resetThemed" || dC === "resetRuntime");
        if(dA){

          dz.push('if(this.', dB, '===value)return value;');
        } else if(dD){

          dz.push('if(this.', dB, '===undefined)return;');
        };
      },
      __F : undefined,
      __G : function(dE, name, dF, dG){

        if(dF === "setRuntime"){

          dE.push('this.', this.$$store.runtime[name], '=value;');
        } else if(dF === "resetRuntime"){

          dE.push('if(this.', this.$$store.runtime[name], '!==undefined)');
          dE.push('delete this.', this.$$store.runtime[name], ';');
        } else if(dF === "set"){

          dE.push('this.', this.$$store.user[name], '=value;');
        } else if(dF === "reset"){

          dE.push('if(this.', this.$$store.user[name], '!==undefined)');
          dE.push('delete this.', this.$$store.user[name], ';');
        } else if(dF === "setThemed"){

          dE.push('this.', this.$$store.theme[name], '=value;');
        } else if(dF === "resetThemed"){

          dE.push('if(this.', this.$$store.theme[name], '!==undefined)');
          dE.push('delete this.', this.$$store.theme[name], ';');
        } else if(dF === "init" && dG){

          dE.push('this.', this.$$store.init[name], '=value;');
        };;;;;;
      },
      __H : function(dH, dI, name, dJ, dK){

        if(dI.inheritable){

          dH.push('var computed, old=this.', this.$$store.inherit[name], ';');
        } else {

          dH.push('var computed, old;');
        };
        dH.push('if(this.', this.$$store.runtime[name], '!==undefined){');
        if(dJ === "setRuntime"){

          dH.push('computed=this.', this.$$store.runtime[name], '=value;');
        } else if(dJ === "resetRuntime"){

          dH.push('delete this.', this.$$store.runtime[name], ';');
          dH.push('if(this.', this.$$store.user[name], '!==undefined)');
          dH.push('computed=this.', this.$$store.user[name], ';');
          dH.push('else if(this.', this.$$store.theme[name], '!==undefined)');
          dH.push('computed=this.', this.$$store.theme[name], ';');
          dH.push('else if(this.', this.$$store.init[name], '!==undefined){');
          dH.push('computed=this.', this.$$store.init[name], ';');
          dH.push('this.', this.$$store.useinit[name], '=true;');
          dH.push('}');
        } else {

          dH.push('old=computed=this.', this.$$store.runtime[name], ';');
          if(dJ === "set"){

            dH.push('this.', this.$$store.user[name], '=value;');
          } else if(dJ === "reset"){

            dH.push('delete this.', this.$$store.user[name], ';');
          } else if(dJ === "setThemed"){

            dH.push('this.', this.$$store.theme[name], '=value;');
          } else if(dJ === "resetThemed"){

            dH.push('delete this.', this.$$store.theme[name], ';');
          } else if(dJ === "init" && dK){

            dH.push('this.', this.$$store.init[name], '=value;');
          };;;;
        };
        dH.push('}');
        dH.push('else if(this.', this.$$store.user[name], '!==undefined){');
        if(dJ === "set"){

          if(!dI.inheritable){

            dH.push('old=this.', this.$$store.user[name], ';');
          };
          dH.push('computed=this.', this.$$store.user[name], '=value;');
        } else if(dJ === "reset"){

          if(!dI.inheritable){

            dH.push('old=this.', this.$$store.user[name], ';');
          };
          dH.push('delete this.', this.$$store.user[name], ';');
          dH.push('if(this.', this.$$store.runtime[name], '!==undefined)');
          dH.push('computed=this.', this.$$store.runtime[name], ';');
          dH.push('if(this.', this.$$store.theme[name], '!==undefined)');
          dH.push('computed=this.', this.$$store.theme[name], ';');
          dH.push('else if(this.', this.$$store.init[name], '!==undefined){');
          dH.push('computed=this.', this.$$store.init[name], ';');
          dH.push('this.', this.$$store.useinit[name], '=true;');
          dH.push('}');
        } else {

          if(dJ === "setRuntime"){

            dH.push('computed=this.', this.$$store.runtime[name], '=value;');
          } else if(dI.inheritable){

            dH.push('computed=this.', this.$$store.user[name], ';');
          } else {

            dH.push('old=computed=this.', this.$$store.user[name], ';');
          };
          if(dJ === "setThemed"){

            dH.push('this.', this.$$store.theme[name], '=value;');
          } else if(dJ === "resetThemed"){

            dH.push('delete this.', this.$$store.theme[name], ';');
          } else if(dJ === "init" && dK){

            dH.push('this.', this.$$store.init[name], '=value;');
          };;
        };
        dH.push('}');
        if(dI.themeable){

          dH.push('else if(this.', this.$$store.theme[name], '!==undefined){');
          if(!dI.inheritable){

            dH.push('old=this.', this.$$store.theme[name], ';');
          };
          if(dJ === "setRuntime"){

            dH.push('computed=this.', this.$$store.runtime[name], '=value;');
          } else if(dJ === "set"){

            dH.push('computed=this.', this.$$store.user[name], '=value;');
          } else if(dJ === "setThemed"){

            dH.push('computed=this.', this.$$store.theme[name], '=value;');
          } else if(dJ === "resetThemed"){

            dH.push('delete this.', this.$$store.theme[name], ';');
            dH.push('if(this.', this.$$store.init[name], '!==undefined){');
            dH.push('computed=this.', this.$$store.init[name], ';');
            dH.push('this.', this.$$store.useinit[name], '=true;');
            dH.push('}');
          } else if(dJ === "init"){

            if(dK){

              dH.push('this.', this.$$store.init[name], '=value;');
            };
            dH.push('computed=this.', this.$$store.theme[name], ';');
          } else if(dJ === "refresh"){

            dH.push('computed=this.', this.$$store.theme[name], ';');
          };;;;;
          dH.push('}');
        };
        dH.push('else if(this.', this.$$store.useinit[name], '){');
        if(!dI.inheritable){

          dH.push('old=this.', this.$$store.init[name], ';');
        };
        if(dJ === "init"){

          if(dK){

            dH.push('computed=this.', this.$$store.init[name], '=value;');
          } else {

            dH.push('computed=this.', this.$$store.init[name], ';');
          };
        } else if(dJ === "set" || dJ === "setRuntime" || dJ === "setThemed" || dJ === "refresh"){

          dH.push('delete this.', this.$$store.useinit[name], ';');
          if(dJ === "setRuntime"){

            dH.push('computed=this.', this.$$store.runtime[name], '=value;');
          } else if(dJ === "set"){

            dH.push('computed=this.', this.$$store.user[name], '=value;');
          } else if(dJ === "setThemed"){

            dH.push('computed=this.', this.$$store.theme[name], '=value;');
          } else if(dJ === "refresh"){

            dH.push('computed=this.', this.$$store.init[name], ';');
          };;;
        };
        dH.push('}');
        if(dJ === "set" || dJ === "setRuntime" || dJ === "setThemed" || dJ === "init"){

          dH.push('else{');
          if(dJ === "setRuntime"){

            dH.push('computed=this.', this.$$store.runtime[name], '=value;');
          } else if(dJ === "set"){

            dH.push('computed=this.', this.$$store.user[name], '=value;');
          } else if(dJ === "setThemed"){

            dH.push('computed=this.', this.$$store.theme[name], '=value;');
          } else if(dJ === "init"){

            if(dK){

              dH.push('computed=this.', this.$$store.init[name], '=value;');
            } else {

              dH.push('computed=this.', this.$$store.init[name], ';');
            };
            dH.push('this.', this.$$store.useinit[name], '=true;');
          };;;
          dH.push('}');
        };
      },
      __I : function(dL, dM, name, dN){

        dL.push('if(computed===undefined||computed===inherit){');
        if(dN === "refresh"){

          dL.push('computed=value;');
        } else {

          dL.push('var pa=this.getLayoutParent();if(pa)computed=pa.', this.$$store.inherit[name], ';');
        };
        dL.push('if((computed===undefined||computed===inherit)&&');
        dL.push('this.', this.$$store.init[name], '!==undefined&&');
        dL.push('this.', this.$$store.init[name], '!==inherit){');
        dL.push('computed=this.', this.$$store.init[name], ';');
        dL.push('this.', this.$$store.useinit[name], '=true;');
        dL.push('}else{');
        dL.push('delete this.', this.$$store.useinit[name], ';}');
        dL.push('}');
        dL.push('if(old===computed)return value;');
        dL.push('if(computed===inherit){');
        dL.push('computed=undefined;delete this.', this.$$store.inherit[name], ';');
        dL.push('}');
        dL.push('else if(computed===undefined)');
        dL.push('delete this.', this.$$store.inherit[name], ';');
        dL.push('else this.', this.$$store.inherit[name], '=computed;');
        dL.push('var backup=computed;');
        if(dM.init !== undefined && dN !== "init"){

          dL.push('if(old===undefined)old=this.', this.$$store.init[name], ";");
        } else {

          dL.push('if(old===undefined)old=null;');
        };
        dL.push('if(computed===undefined||computed==inherit)computed=null;');
      },
      __J : function(dO, dP, name, dQ){

        if(dQ !== "set" && dQ !== "setRuntime" && dQ !== "setThemed"){

          dO.push('if(computed===undefined)computed=null;');
        };
        dO.push('if(old===computed)return value;');
        if(dP.init !== undefined && dQ !== "init"){

          dO.push('if(old===undefined)old=this.', this.$$store.init[name], ";");
        } else {

          dO.push('if(old===undefined)old=null;');
        };
      },
      __K : function(dR, dS, name){

        if(dS.apply){

          dR.push('this.', dS.apply, '(computed, old, "', name, '");');
        };
        if(dS.event){

          dR.push("var reg=qx.event.Registration;", "if(reg.hasListener(this, '", dS.event, "')){", "reg.fireEvent(this, '", dS.event, "', qx.event.type.Data, [computed, old]", ")}");
        };
      },
      __L : function(dT, name){

        dT.push('var a=this._getChildren();if(a)for(var i=0,l=a.length;i<l;i++){');
        dT.push('if(a[i].', this.$$method.refresh[name], ')a[i].', this.$$method.refresh[name], '(backup);');
        dT.push('}');
      }
    },
    defer : function(dU){

      var dW = navigator.userAgent.indexOf(bc) != -1;
      var dV = navigator.userAgent.indexOf(bS) != -1;
      if(dW || dV){

        dU.__x = dU.__y;
      };
    }
  });
})();
(function(){

  var k = "[Class ",j = "]",h = "constructor",g = "extend",f = "qx.Class",e = "qx.aspects",d = "Array",c = ".",b = "static";
  qx.Bootstrap.define(f, {
    statics : {
      __M : qx.core.Environment.get("module.property") ? qx.core.Property : null,
      define : function(name, m){

        if(!m){

          var m = {
          };
        };
        if(m.include && !(qx.Bootstrap.getClass(m.include) === d)){

          m.include = [m.include];
        };
        if(m.implement && !(qx.Bootstrap.getClass(m.implement) === d)){

          m.implement = [m.implement];
        };
        var n = false;
        if(!m.hasOwnProperty(g) && !m.type){

          m.type = b;
          n = true;
        };
        {
        };
        var o = this.__P(name, m.type, m.extend, m.statics, m.construct, m.destruct, m.include);
        if(m.extend){

          if(m.properties){

            this.__R(o, m.properties, true);
          };
          if(m.members){

            this.__T(o, m.members, true, true, false);
          };
          if(m.events){

            this.__Q(o, m.events, true);
          };
          if(m.include){

            for(var i = 0,l = m.include.length;i < l;i++){

              this.__X(o, m.include[i], false);
            };
          };
        };
        if(m.environment){

          for(var p in m.environment){

            qx.core.Environment.add(p, m.environment[p]);
          };
        };
        if(m.implement){

          for(var i = 0,l = m.implement.length;i < l;i++){

            this.__V(o, m.implement[i]);
          };
        };
        {
        };
        if(m.defer){

          m.defer.self = o;
          m.defer(o, o.prototype, {
            add : function(name, q){

              var r = {
              };
              r[name] = q;
              qx.Class.__R(o, r, true);
            }
          });
        };
        return o;
      },
      undefine : function(name){

        delete this.$$registry[name];
        var s = name.split(c);
        var u = [window];
        for(var i = 0;i < s.length;i++){

          u.push(u[i][s[i]]);
        };
        for(var i = u.length - 1;i >= 1;i--){

          var t = u[i];
          var parent = u[i - 1];
          if(qx.Bootstrap.isFunction(t) || qx.Bootstrap.objectGetLength(t) === 0){

            delete parent[s[i - 1]];
          } else {

            break;
          };
        };
      },
      isDefined : qx.util.OOUtil.classIsDefined,
      getTotalNumber : function(){

        return qx.Bootstrap.objectGetLength(this.$$registry);
      },
      getByName : qx.Bootstrap.getByName,
      include : function(v, w){

        {
        };
        qx.Class.__X(v, w, false);
      },
      patch : function(x, y){

        {
        };
        qx.Class.__X(x, y, true);
      },
      isSubClassOf : function(z, A){

        if(!z){

          return false;
        };
        if(z == A){

          return true;
        };
        if(z.prototype instanceof A){

          return true;
        };
        return false;
      },
      getPropertyDefinition : qx.util.OOUtil.getPropertyDefinition,
      getProperties : function(B){

        var C = [];
        while(B){

          if(B.$$properties){

            C.push.apply(C, qx.Bootstrap.getKeys(B.$$properties));
          };
          B = B.superclass;
        };
        return C;
      },
      getByProperty : function(D, name){

        while(D){

          if(D.$$properties && D.$$properties[name]){

            return D;
          };
          D = D.superclass;
        };
        return null;
      },
      hasProperty : qx.util.OOUtil.hasProperty,
      getEventType : qx.util.OOUtil.getEventType,
      supportsEvent : qx.util.OOUtil.supportsEvent,
      hasOwnMixin : function(E, F){

        return E.$$includes && E.$$includes.indexOf(F) !== -1;
      },
      getByMixin : function(G, H){

        var I,i,l;
        while(G){

          if(G.$$includes){

            I = G.$$flatIncludes;
            for(i = 0,l = I.length;i < l;i++){

              if(I[i] === H){

                return G;
              };
            };
          };
          G = G.superclass;
        };
        return null;
      },
      getMixins : qx.util.OOUtil.getMixins,
      hasMixin : function(J, K){

        return !!this.getByMixin(J, K);
      },
      hasOwnInterface : function(L, M){

        return L.$$implements && L.$$implements.indexOf(M) !== -1;
      },
      getByInterface : qx.util.OOUtil.getByInterface,
      getInterfaces : function(N){

        var O = [];
        while(N){

          if(N.$$implements){

            O.push.apply(O, N.$$flatImplements);
          };
          N = N.superclass;
        };
        return O;
      },
      hasInterface : qx.util.OOUtil.hasInterface,
      implementsInterface : function(P, Q){

        var R = P.constructor;
        if(this.hasInterface(R, Q)){

          return true;
        };
        try{

          qx.Interface.assertObject(P, Q);
          return true;
        } catch(S) {
        };
        try{

          qx.Interface.assert(R, Q, false);
          return true;
        } catch(T) {
        };
        return false;
      },
      getInstance : function(){

        if(!this.$$instance){

          this.$$allowconstruct = true;
          this.$$instance = new this;
          delete this.$$allowconstruct;
        };
        return this.$$instance;
      },
      genericToString : function(){

        return k + this.classname + j;
      },
      $$registry : qx.Bootstrap.$$registry,
      __c : null,
      __N : null,
      __d : function(){
      },
      __O : function(){
      },
      __P : function(name, U, V, W, X, Y, ba){

        var bd;
        if(!V && qx.core.Environment.get("qx.aspects") == false){

          bd = W || {
          };
          qx.Bootstrap.setDisplayNames(bd, name);
        } else {

          var bd = {
          };
          if(V){

            if(!X){

              X = this.__Y();
            };
            if(this.__bb(V, ba)){

              bd = this.__bc(X, name, U);
            } else {

              bd = X;
            };
            if(U === "singleton"){

              bd.getInstance = this.getInstance;
            };
            qx.Bootstrap.setDisplayName(X, name, "constructor");
          };
          if(W){

            qx.Bootstrap.setDisplayNames(W, name);
            var be;
            for(var i = 0,a = qx.Bootstrap.getKeys(W),l = a.length;i < l;i++){

              be = a[i];
              var bb = W[be];
              if(qx.core.Environment.get("qx.aspects")){

                if(bb instanceof Function){

                  bb = qx.core.Aspect.wrap(name + "." + be, bb, "static");
                };
                bd[be] = bb;
              } else {

                bd[be] = bb;
              };
            };
          };
        };
        var bc = name ? qx.Bootstrap.createNamespace(name, bd) : "";
        bd.name = bd.classname = name;
        bd.basename = bc;
        bd.$$type = "Class";
        if(U){

          bd.$$classtype = U;
        };
        if(!bd.hasOwnProperty("toString")){

          bd.toString = this.genericToString;
        };
        if(V){

          qx.Bootstrap.extendClass(bd, X, V, name, bc);
          if(Y){

            if(qx.core.Environment.get("qx.aspects")){

              Y = qx.core.Aspect.wrap(name, Y, "destructor");
            };
            bd.$$destructor = Y;
            qx.Bootstrap.setDisplayName(Y, name, "destruct");
          };
        };
        this.$$registry[name] = bd;
        return bd;
      },
      __Q : function(bf, bg, bh){

        var bi,bi;
        {
        };
        if(bf.$$events){

          for(var bi in bg){

            bf.$$events[bi] = bg[bi];
          };
        } else {

          bf.$$events = bg;
        };
      },
      __R : function(bj, bk, bl){

        if(!qx.core.Environment.get("module.property")){

          throw new Error("Property module disabled.");
        };
        var bm;
        if(bl === undefined){

          bl = false;
        };
        var bn = bj.prototype;
        for(var name in bk){

          bm = bk[name];
          {
          };
          bm.name = name;
          if(!bm.refine){

            if(bj.$$properties === undefined){

              bj.$$properties = {
              };
            };
            bj.$$properties[name] = bm;
          };
          if(bm.init !== undefined){

            bj.prototype["$$init_" + name] = bm.init;
          };
          if(bm.event !== undefined){

            if(!qx.core.Environment.get("module.events")){

              throw new Error("Events module not enabled.");
            };
            var event = {
            };
            event[bm.event] = "qx.event.type.Data";
            this.__Q(bj, event, bl);
          };
          if(bm.inheritable){

            this.__M.$$inheritable[name] = true;
            if(!bn.$$refreshInheritables){

              this.__M.attachRefreshInheritables(bj);
            };
          };
          if(!bm.refine){

            this.__M.attachMethods(bj, name, bm);
          };
        };
      },
      __S : null,
      __T : function(bo, bp, bq, br, bs){

        var bt = bo.prototype;
        var bv,bu;
        qx.Bootstrap.setDisplayNames(bp, bo.classname + ".prototype");
        for(var i = 0,a = qx.Bootstrap.getKeys(bp),l = a.length;i < l;i++){

          bv = a[i];
          bu = bp[bv];
          {
          };
          if(br !== false && bu instanceof Function && bu.$$type == null){

            if(bs == true){

              bu = this.__U(bu, bt[bv]);
            } else {

              if(bt[bv]){

                bu.base = bt[bv];
              };
              bu.self = bo;
            };
            if(qx.core.Environment.get("qx.aspects")){

              bu = qx.core.Aspect.wrap(bo.classname + "." + bv, bu, "member");
            };
          };
          bt[bv] = bu;
        };
      },
      __U : function(bw, bx){

        if(bx){

          return function(){

            var bz = bw.base;
            bw.base = bx;
            var by = bw.apply(this, arguments);
            bw.base = bz;
            return by;
          };
        } else {

          return bw;
        };
      },
      __V : function(bA, bB){

        {
        };
        var bC = qx.Interface.flatten([bB]);
        if(bA.$$implements){

          bA.$$implements.push(bB);
          bA.$$flatImplements.push.apply(bA.$$flatImplements, bC);
        } else {

          bA.$$implements = [bB];
          bA.$$flatImplements = bC;
        };
      },
      __W : function(bD){

        var name = bD.classname;
        var bE = this.__bc(bD, name, bD.$$classtype);
        for(var i = 0,a = qx.Bootstrap.getKeys(bD),l = a.length;i < l;i++){

          bF = a[i];
          bE[bF] = bD[bF];
        };
        bE.prototype = bD.prototype;
        var bH = bD.prototype;
        for(var i = 0,a = qx.Bootstrap.getKeys(bH),l = a.length;i < l;i++){

          bF = a[i];
          var bI = bH[bF];
          if(bI && bI.self == bD){

            bI.self = bE;
          };
        };
        for(var bF in this.$$registry){

          var bG = this.$$registry[bF];
          if(!bG){

            continue;
          };
          if(bG.base == bD){

            bG.base = bE;
          };
          if(bG.superclass == bD){

            bG.superclass = bE;
          };
          if(bG.$$original){

            if(bG.$$original.base == bD){

              bG.$$original.base = bE;
            };
            if(bG.$$original.superclass == bD){

              bG.$$original.superclass = bE;
            };
          };
        };
        qx.Bootstrap.createNamespace(name, bE);
        this.$$registry[name] = bE;
        return bE;
      },
      __X : function(bJ, bK, bL){

        {
        };
        if(this.hasMixin(bJ, bK)){

          return;
        };
        var bO = bJ.$$original;
        if(bK.$$constructor && !bO){

          bJ = this.__W(bJ);
        };
        var bN = qx.Mixin.flatten([bK]);
        var bM;
        for(var i = 0,l = bN.length;i < l;i++){

          bM = bN[i];
          if(bM.$$events){

            this.__Q(bJ, bM.$$events, bL);
          };
          if(bM.$$properties){

            this.__R(bJ, bM.$$properties, bL);
          };
          if(bM.$$members){

            this.__T(bJ, bM.$$members, bL, bL, bL);
          };
        };
        if(bJ.$$includes){

          bJ.$$includes.push(bK);
          bJ.$$flatIncludes.push.apply(bJ.$$flatIncludes, bN);
        } else {

          bJ.$$includes = [bK];
          bJ.$$flatIncludes = bN;
        };
      },
      __Y : function(){

        function bP(){

          bP.base.apply(this, arguments);
        };
        return bP;
      },
      __ba : function(){

        return function(){
        };
      },
      __bb : function(bQ, bR){

        {
        };
        if(bQ && bQ.$$includes){

          var bS = bQ.$$flatIncludes;
          for(var i = 0,l = bS.length;i < l;i++){

            if(bS[i].$$constructor){

              return true;
            };
          };
        };
        if(bR){

          var bT = qx.Mixin.flatten(bR);
          for(var i = 0,l = bT.length;i < l;i++){

            if(bT[i].$$constructor){

              return true;
            };
          };
        };
        return false;
      },
      __bc : function(bU, name, bV){

        var bX = function(){

          var cb = bX;
          {
          };
          var ca = cb.$$original.apply(this, arguments);
          if(cb.$$includes){

            var bY = cb.$$flatIncludes;
            for(var i = 0,l = bY.length;i < l;i++){

              if(bY[i].$$constructor){

                bY[i].$$constructor.apply(this, arguments);
              };
            };
          };
          {
          };
          return ca;
        };
        if(qx.core.Environment.get(e)){

          var bW = qx.core.Aspect.wrap(name, bX, h);
          bX.$$original = bU;
          bX.constructor = bW;
          bX = bW;
        };
        bX.$$original = bU;
        bU.wrapper = bX;
        return bX;
      }
    },
    defer : function(){

      if(qx.core.Environment.get(e)){

        for(var cc in qx.Bootstrap.$$registry){

          var cd = qx.Bootstrap.$$registry[cc];
          for(var ce in cd){

            if(cd[ce] instanceof Function){

              cd[ce] = qx.core.Aspect.wrap(cc + c + ce, cd[ce], b);
            };
          };
        };
      };
    }
  });
})();
(function(){

  var k = "join",j = "toLocaleUpperCase",h = "shift",g = "substr",f = "filter",e = "unshift",d = "match",c = "quote",b = "qx.lang.Generics",a = "localeCompare",I = "sort",H = "some",G = "charAt",F = "split",E = "substring",D = "pop",C = "toUpperCase",B = "replace",A = "push",z = "charCodeAt",t = "every",u = "reverse",q = "search",r = "forEach",o = "map",p = "toLowerCase",m = "splice",n = "toLocaleLowerCase",v = "indexOf",w = "lastIndexOf",y = "slice",x = "concat";
  qx.Class.define(b, {
    statics : {
      __bd : {
        "Array" : [k, u, I, A, D, h, e, m, x, y, v, w, r, o, f, H, t],
        "String" : [c, E, p, C, G, z, v, w, n, j, a, d, q, B, F, g, x, y]
      },
      __be : function(J, K){

        return function(s){

          return J.prototype[K].apply(s, Array.prototype.slice.call(arguments, 1));
        };
      },
      __bf : function(){

        var L = qx.lang.Generics.__bd;
        for(var P in L){

          var N = window[P];
          var M = L[P];
          for(var i = 0,l = M.length;i < l;i++){

            var O = M[i];
            if(!N[O]){

              N[O] = qx.lang.Generics.__be(N, O);
            };
          };
        };
      }
    },
    defer : function(Q){

      Q.__bf();
    }
  });
})();
(function(){

  var a = "qx.data.MBinding";
  qx.Mixin.define(a, {
    members : {
      bind : function(b, c, d, e){

        return qx.data.SingleValueBinding.bind(this, b, c, d, e);
      },
      removeBinding : function(f){

        qx.data.SingleValueBinding.removeBindingFromObject(this, f);
      },
      removeAllBindings : function(){

        qx.data.SingleValueBinding.removeAllBindingsForObject(this);
      },
      getBindings : function(){

        return qx.data.SingleValueBinding.getAllBindingsForObject(this);
      }
    }
  });
})();
(function(){

  var m = "Boolean",l = ") to the object '",k = "Please use only one array at a time: ",h = "Integer",g = " of object ",f = "qx.data.SingleValueBinding",d = "No number or 'last' value hast been given",c = "Binding property ",b = "Binding could not be found!",a = "Binding from '",M = "PositiveNumber",L = "PositiveInteger",K = "Binding does not exist!",J = " in an array binding: ",I = ").",H = "Date",G = " not possible: No event available. ",F = ". Error message: ",E = "set",D = "deepBinding",u = "item",v = "reset",s = "Failed so set value ",t = " does not work.",q = "' (",r = " on ",n = "String",p = "Number",w = "change",x = "]",z = ".",y = "last",B = "[",A = "",C = "get";
  qx.Class.define(f, {
    statics : {
      __bg : {
      },
      bind : function(N, O, P, Q, R){

        var bd = this.__bi(N, O, P, Q, R);
        var X = O.split(z);
        var T = this.__bo(X);
        var bc = [];
        var Y = [];
        var ba = [];
        var V = [];
        var W = N;
        try{

          for(var i = 0;i < X.length;i++){

            if(T[i] !== A){

              V.push(w);
            } else {

              V.push(this.__bj(W, X[i]));
            };
            bc[i] = W;
            if(i == X.length - 1){

              if(T[i] !== A){

                var bh = T[i] === y ? W.length - 1 : T[i];
                var S = W.getItem(bh);
                this.__bn(S, P, Q, R, N);
                ba[i] = this.__bp(W, V[i], P, Q, R, T[i]);
              } else {

                if(X[i] != null && W[C + qx.lang.String.firstUp(X[i])] != null){

                  var S = W[C + qx.lang.String.firstUp(X[i])]();
                  this.__bn(S, P, Q, R, N);
                };
                ba[i] = this.__bp(W, V[i], P, Q, R);
              };
            } else {

              var be = {
                index : i,
                propertyNames : X,
                sources : bc,
                listenerIds : ba,
                arrayIndexValues : T,
                targetObject : P,
                targetPropertyChain : Q,
                options : R,
                listeners : Y
              };
              var bb = qx.lang.Function.bind(this.__bh, this, be);
              Y.push(bb);
              ba[i] = W.addListener(V[i], bb);
            };
            if(W[C + qx.lang.String.firstUp(X[i])] == null){

              W = null;
            } else if(T[i] !== A){

              W = W[C + qx.lang.String.firstUp(X[i])](T[i]);
            } else {

              W = W[C + qx.lang.String.firstUp(X[i])]();
            };
            if(!W){

              break;
            };
          };
        } catch(bi) {

          for(var i = 0;i < bc.length;i++){

            if(bc[i] && ba[i]){

              bc[i].removeListenerById(ba[i]);
            };
          };
          var bg = bd.targets;
          var U = bd.listenerIds[i];
          for(var i = 0;i < bg.length;i++){

            if(bg[i] && U[i]){

              bg[i].removeListenerById(U[i]);
            };
          };
          throw bi;
        };
        var bf = {
          type : D,
          listenerIds : ba,
          sources : bc,
          targetListenerIds : bd.listenerIds,
          targets : bd.targets
        };
        this.__bq(bf, N, O, P, Q);
        return bf;
      },
      __bh : function(bj){

        if(bj.options && bj.options.onUpdate){

          bj.options.onUpdate(bj.sources[bj.index], bj.targetObject);
        };
        for(var j = bj.index + 1;j < bj.propertyNames.length;j++){

          var bn = bj.sources[j];
          bj.sources[j] = null;
          if(!bn){

            continue;
          };
          bn.removeListenerById(bj.listenerIds[j]);
        };
        var bn = bj.sources[bj.index];
        for(var j = bj.index + 1;j < bj.propertyNames.length;j++){

          if(bj.arrayIndexValues[j - 1] !== A){

            bn = bn[C + qx.lang.String.firstUp(bj.propertyNames[j - 1])](bj.arrayIndexValues[j - 1]);
          } else {

            bn = bn[C + qx.lang.String.firstUp(bj.propertyNames[j - 1])]();
          };
          bj.sources[j] = bn;
          if(!bn){

            this.__bk(bj.targetObject, bj.targetPropertyChain);
            break;
          };
          if(j == bj.propertyNames.length - 1){

            if(qx.Class.implementsInterface(bn, qx.data.IListData)){

              var bo = bj.arrayIndexValues[j] === y ? bn.length - 1 : bj.arrayIndexValues[j];
              var bl = bn.getItem(bo);
              this.__bn(bl, bj.targetObject, bj.targetPropertyChain, bj.options, bj.sources[bj.index]);
              bj.listenerIds[j] = this.__bp(bn, w, bj.targetObject, bj.targetPropertyChain, bj.options, bj.arrayIndexValues[j]);
            } else {

              if(bj.propertyNames[j] != null && bn[C + qx.lang.String.firstUp(bj.propertyNames[j])] != null){

                var bl = bn[C + qx.lang.String.firstUp(bj.propertyNames[j])]();
                this.__bn(bl, bj.targetObject, bj.targetPropertyChain, bj.options, bj.sources[bj.index]);
              };
              var bm = this.__bj(bn, bj.propertyNames[j]);
              bj.listenerIds[j] = this.__bp(bn, bm, bj.targetObject, bj.targetPropertyChain, bj.options);
            };
          } else {

            if(bj.listeners[j] == null){

              var bk = qx.lang.Function.bind(this.__bh, this, bj);
              bj.listeners.push(bk);
            };
            if(qx.Class.implementsInterface(bn, qx.data.IListData)){

              var bm = w;
            } else {

              var bm = this.__bj(bn, bj.propertyNames[j]);
            };
            bj.listenerIds[j] = bn.addListener(bm, bj.listeners[j]);
          };
        };
      },
      __bi : function(bp, bq, br, bs, bt){

        var bx = bs.split(z);
        var bv = this.__bo(bx);
        var bC = [];
        var bB = [];
        var bz = [];
        var by = [];
        var bw = br;
        for(var i = 0;i < bx.length - 1;i++){

          if(bv[i] !== A){

            by.push(w);
          } else {

            try{

              by.push(this.__bj(bw, bx[i]));
            } catch(e) {

              break;
            };
          };
          bC[i] = bw;
          var bA = function(){

            for(var j = i + 1;j < bx.length - 1;j++){

              var bF = bC[j];
              bC[j] = null;
              if(!bF){

                continue;
              };
              bF.removeListenerById(bz[j]);
            };
            var bF = bC[i];
            for(var j = i + 1;j < bx.length - 1;j++){

              var bD = qx.lang.String.firstUp(bx[j - 1]);
              if(bv[j - 1] !== A){

                var bG = bv[j - 1] === y ? bF.getLength() - 1 : bv[j - 1];
                bF = bF[C + bD](bG);
              } else {

                bF = bF[C + bD]();
              };
              bC[j] = bF;
              if(bB[j] == null){

                bB.push(bA);
              };
              if(qx.Class.implementsInterface(bF, qx.data.IListData)){

                var bE = w;
              } else {

                try{

                  var bE = qx.data.SingleValueBinding.__bj(bF, bx[j]);
                } catch(e) {

                  break;
                };
              };
              bz[j] = bF.addListener(bE, bB[j]);
            };
            qx.data.SingleValueBinding.updateTarget(bp, bq, br, bs, bt);
          };
          bB.push(bA);
          bz[i] = bw.addListener(by[i], bA);
          var bu = qx.lang.String.firstUp(bx[i]);
          if(bw[C + bu] == null){

            bw = null;
          } else if(bv[i] !== A){

            bw = bw[C + bu](bv[i]);
          } else {

            bw = bw[C + bu]();
          };
          if(!bw){

            break;
          };
        };
        return {
          listenerIds : bz,
          targets : bC
        };
      },
      updateTarget : function(bH, bI, bJ, bK, bL){

        var bM = this.getValueFromObject(bH, bI);
        bM = qx.data.SingleValueBinding.__br(bM, bJ, bK, bL, bH);
        this.__bl(bJ, bK, bM);
      },
      getValueFromObject : function(o, bN){

        var bR = this.__bm(o, bN);
        var bP;
        if(bR != null){

          var bT = bN.substring(bN.lastIndexOf(z) + 1, bN.length);
          if(bT.charAt(bT.length - 1) == x){

            var bO = bT.substring(bT.lastIndexOf(B) + 1, bT.length - 1);
            var bQ = bT.substring(0, bT.lastIndexOf(B));
            var bS = bR[C + qx.lang.String.firstUp(bQ)]();
            if(bO == y){

              bO = bS.length - 1;
            };
            if(bS != null){

              bP = bS.getItem(bO);
            };
          } else {

            bP = bR[C + qx.lang.String.firstUp(bT)]();
          };
        };
        return bP;
      },
      __bj : function(bU, bV){

        var bW = this.__bs(bU, bV);
        if(bW == null){

          if(qx.Class.supportsEvent(bU.constructor, bV)){

            bW = bV;
          } else if(qx.Class.supportsEvent(bU.constructor, w + qx.lang.String.firstUp(bV))){

            bW = w + qx.lang.String.firstUp(bV);
          } else {

            throw new qx.core.AssertionError(c + bV + g + bU + G);
          };
        };
        return bW;
      },
      __bk : function(bX, bY){

        var ca = this.__bm(bX, bY);
        if(ca != null){

          var cb = bY.substring(bY.lastIndexOf(z) + 1, bY.length);
          if(cb.charAt(cb.length - 1) == x){

            this.__bl(bX, bY, null);
            return;
          };
          if(ca[v + qx.lang.String.firstUp(cb)] != undefined){

            ca[v + qx.lang.String.firstUp(cb)]();
          } else {

            ca[E + qx.lang.String.firstUp(cb)](null);
          };
        };
      },
      __bl : function(cc, cd, ce){

        var ci = this.__bm(cc, cd);
        if(ci != null){

          var cj = cd.substring(cd.lastIndexOf(z) + 1, cd.length);
          if(cj.charAt(cj.length - 1) == x){

            var cf = cj.substring(cj.lastIndexOf(B) + 1, cj.length - 1);
            var ch = cj.substring(0, cj.lastIndexOf(B));
            var cg = cc;
            if(!qx.Class.implementsInterface(cg, qx.data.IListData)){

              cg = ci[C + qx.lang.String.firstUp(ch)]();
            };
            if(cf == y){

              cf = cg.length - 1;
            };
            if(cg != null){

              cg.setItem(cf, ce);
            };
          } else {

            ci[E + qx.lang.String.firstUp(cj)](ce);
          };
        };
      },
      __bm : function(ck, cl){

        var co = cl.split(z);
        var cp = ck;
        for(var i = 0;i < co.length - 1;i++){

          try{

            var cn = co[i];
            if(cn.indexOf(x) == cn.length - 1){

              var cm = cn.substring(cn.indexOf(B) + 1, cn.length - 1);
              cn = cn.substring(0, cn.indexOf(B));
            };
            if(cn != A){

              cp = cp[C + qx.lang.String.firstUp(cn)]();
            };
            if(cm != null){

              if(cm == y){

                cm = cp.length - 1;
              };
              cp = cp.getItem(cm);
              cm = null;
            };
          } catch(cq) {

            return null;
          };
        };
        return cp;
      },
      __bn : function(cr, cs, ct, cu, cv){

        cr = this.__br(cr, cs, ct, cu, cv);
        if(cr === undefined){

          this.__bk(cs, ct);
        };
        if(cr !== undefined){

          try{

            this.__bl(cs, ct, cr);
            if(cu && cu.onUpdate){

              cu.onUpdate(cv, cs, cr);
            };
          } catch(e) {

            if(!(e instanceof qx.core.ValidationError)){

              throw e;
            };
            if(cu && cu.onSetFail){

              cu.onSetFail(e);
            } else {

              qx.log.Logger.warn(s + cr + r + cs + F + e);
            };
          };
        };
      },
      __bo : function(cw){

        var cx = [];
        for(var i = 0;i < cw.length;i++){

          var name = cw[i];
          if(qx.lang.String.endsWith(name, x)){

            var cy = name.substring(name.indexOf(B) + 1, name.indexOf(x));
            if(name.indexOf(x) != name.length - 1){

              throw new Error(k + name + t);
            };
            if(cy !== y){

              if(cy == A || isNaN(parseInt(cy, 10))){

                throw new Error(d + J + name + t);
              };
            };
            if(name.indexOf(B) != 0){

              cw[i] = name.substring(0, name.indexOf(B));
              cx[i] = A;
              cx[i + 1] = cy;
              cw.splice(i + 1, 0, u);
              i++;
            } else {

              cx[i] = cy;
              cw.splice(i, 1, u);
            };
          } else {

            cx[i] = A;
          };
        };
        return cx;
      },
      __bp : function(cz, cA, cB, cC, cD, cE){

        var cF;
        {
        };
        var cH = function(cI, e){

          if(cI !== A){

            if(cI === y){

              cI = cz.length - 1;
            };
            var cL = cz.getItem(cI);
            if(cL === undefined){

              qx.data.SingleValueBinding.__bk(cB, cC);
            };
            var cJ = e.getData().start;
            var cK = e.getData().end;
            if(cI < cJ || cI > cK){

              return;
            };
          } else {

            var cL = e.getData();
          };
          {
          };
          cL = qx.data.SingleValueBinding.__br(cL, cB, cC, cD, cz);
          {
          };
          try{

            if(cL !== undefined){

              qx.data.SingleValueBinding.__bl(cB, cC, cL);
            } else {

              qx.data.SingleValueBinding.__bk(cB, cC);
            };
            if(cD && cD.onUpdate){

              cD.onUpdate(cz, cB, cL);
            };
          } catch(e) {

            if(!(e instanceof qx.core.ValidationError)){

              throw e;
            };
            if(cD && cD.onSetFail){

              cD.onSetFail(e);
            } else {

              qx.log.Logger.warn(s + cL + r + cB + F + e);
            };
          };
        };
        if(!cE){

          cE = A;
        };
        cH = qx.lang.Function.bind(cH, cz, cE);
        var cG = cz.addListener(cA, cH);
        return cG;
      },
      __bq : function(cM, cN, cO, cP, cQ){

        if(this.__bg[cN.toHashCode()] === undefined){

          this.__bg[cN.toHashCode()] = [];
        };
        this.__bg[cN.toHashCode()].push([cM, cN, cO, cP, cQ]);
      },
      __br : function(cR, cS, cT, cU, cV){

        if(cU && cU.converter){

          var cX;
          if(cS.getModel){

            cX = cS.getModel();
          };
          return cU.converter(cR, cX, cV, cS);
        } else {

          var da = this.__bm(cS, cT);
          var db = cT.substring(cT.lastIndexOf(z) + 1, cT.length);
          if(da == null){

            return cR;
          };
          var cY = qx.Class.getPropertyDefinition(da.constructor, db);
          var cW = cY == null ? A : cY.check;
          return this.__bt(cR, cW);
        };
      },
      __bs : function(dc, dd){

        var de = qx.Class.getPropertyDefinition(dc.constructor, dd);
        if(de == null){

          return null;
        };
        return de.event;
      },
      __bt : function(df, dg){

        var dh = qx.lang.Type.getClass(df);
        if((dh == p || dh == n) && (dg == h || dg == L)){

          df = parseInt(df, 10);
        };
        if((dh == m || dh == p || dh == H) && dg == n){

          df = df + A;
        };
        if((dh == p || dh == n) && (dg == p || dg == M)){

          df = parseFloat(df);
        };
        return df;
      },
      removeBindingFromObject : function(di, dj){

        if(dj.type == D){

          for(var i = 0;i < dj.sources.length;i++){

            if(dj.sources[i]){

              dj.sources[i].removeListenerById(dj.listenerIds[i]);
            };
          };
          for(var i = 0;i < dj.targets.length;i++){

            if(dj.targets[i]){

              dj.targets[i].removeListenerById(dj.targetListenerIds[i]);
            };
          };
        } else {

          di.removeListenerById(dj);
        };
        var dk = this.__bg[di.toHashCode()];
        if(dk != undefined){

          for(var i = 0;i < dk.length;i++){

            if(dk[i][0] == dj){

              qx.lang.Array.remove(dk, dk[i]);
              return;
            };
          };
        };
        throw new Error(b);
      },
      removeAllBindingsForObject : function(dl){

        {
        };
        var dm = this.__bg[dl.toHashCode()];
        if(dm != undefined){

          for(var i = dm.length - 1;i >= 0;i--){

            this.removeBindingFromObject(dl, dm[i][0]);
          };
        };
      },
      getAllBindingsForObject : function(dn){

        if(this.__bg[dn.toHashCode()] === undefined){

          this.__bg[dn.toHashCode()] = [];
        };
        return this.__bg[dn.toHashCode()];
      },
      removeAllBindings : function(){

        for(var dq in this.__bg){

          var dp = qx.core.ObjectRegistry.fromHashCode(dq);
          if(dp == null){

            delete this.__bg[dq];
            continue;
          };
          this.removeAllBindingsForObject(dp);
        };
        this.__bg = {
        };
      },
      getAllBindings : function(){

        return this.__bg;
      },
      showBindingInLog : function(dr, ds){

        var du;
        for(var i = 0;i < this.__bg[dr.toHashCode()].length;i++){

          if(this.__bg[dr.toHashCode()][i][0] == ds){

            du = this.__bg[dr.toHashCode()][i];
            break;
          };
        };
        if(du === undefined){

          var dt = K;
        } else {

          var dt = a + du[1] + q + du[2] + l + du[3] + q + du[4] + I;
        };
        qx.log.Logger.debug(dt);
      },
      showAllBindingsInLog : function(){

        for(var dw in this.__bg){

          var dv = qx.core.ObjectRegistry.fromHashCode(dw);
          for(var i = 0;i < this.__bg[dw].length;i++){

            this.showBindingInLog(dv, this.__bg[dw][i][0]);
          };
        };
      }
    }
  });
})();
(function(){

  var p = "]",o = '\\u',n = "undefined",m = '\\$1',l = "0041-005A0061-007A00AA00B500BA00C0-00D600D8-00F600F8-02C102C6-02D102E0-02E402EC02EE0370-037403760377037A-037D03860388-038A038C038E-03A103A3-03F503F7-0481048A-05250531-055605590561-058705D0-05EA05F0-05F20621-064A066E066F0671-06D306D506E506E606EE06EF06FA-06FC06FF07100712-072F074D-07A507B107CA-07EA07F407F507FA0800-0815081A082408280904-0939093D09500958-0961097109720979-097F0985-098C098F09900993-09A809AA-09B009B209B6-09B909BD09CE09DC09DD09DF-09E109F009F10A05-0A0A0A0F0A100A13-0A280A2A-0A300A320A330A350A360A380A390A59-0A5C0A5E0A72-0A740A85-0A8D0A8F-0A910A93-0AA80AAA-0AB00AB20AB30AB5-0AB90ABD0AD00AE00AE10B05-0B0C0B0F0B100B13-0B280B2A-0B300B320B330B35-0B390B3D0B5C0B5D0B5F-0B610B710B830B85-0B8A0B8E-0B900B92-0B950B990B9A0B9C0B9E0B9F0BA30BA40BA8-0BAA0BAE-0BB90BD00C05-0C0C0C0E-0C100C12-0C280C2A-0C330C35-0C390C3D0C580C590C600C610C85-0C8C0C8E-0C900C92-0CA80CAA-0CB30CB5-0CB90CBD0CDE0CE00CE10D05-0D0C0D0E-0D100D12-0D280D2A-0D390D3D0D600D610D7A-0D7F0D85-0D960D9A-0DB10DB3-0DBB0DBD0DC0-0DC60E01-0E300E320E330E40-0E460E810E820E840E870E880E8A0E8D0E94-0E970E99-0E9F0EA1-0EA30EA50EA70EAA0EAB0EAD-0EB00EB20EB30EBD0EC0-0EC40EC60EDC0EDD0F000F40-0F470F49-0F6C0F88-0F8B1000-102A103F1050-1055105A-105D106110651066106E-10701075-1081108E10A0-10C510D0-10FA10FC1100-1248124A-124D1250-12561258125A-125D1260-1288128A-128D1290-12B012B2-12B512B8-12BE12C012C2-12C512C8-12D612D8-13101312-13151318-135A1380-138F13A0-13F41401-166C166F-167F1681-169A16A0-16EA1700-170C170E-17111720-17311740-17511760-176C176E-17701780-17B317D717DC1820-18771880-18A818AA18B0-18F51900-191C1950-196D1970-19741980-19AB19C1-19C71A00-1A161A20-1A541AA71B05-1B331B45-1B4B1B83-1BA01BAE1BAF1C00-1C231C4D-1C4F1C5A-1C7D1CE9-1CEC1CEE-1CF11D00-1DBF1E00-1F151F18-1F1D1F20-1F451F48-1F4D1F50-1F571F591F5B1F5D1F5F-1F7D1F80-1FB41FB6-1FBC1FBE1FC2-1FC41FC6-1FCC1FD0-1FD31FD6-1FDB1FE0-1FEC1FF2-1FF41FF6-1FFC2071207F2090-209421022107210A-211321152119-211D212421262128212A-212D212F-2139213C-213F2145-2149214E218321842C00-2C2E2C30-2C5E2C60-2CE42CEB-2CEE2D00-2D252D30-2D652D6F2D80-2D962DA0-2DA62DA8-2DAE2DB0-2DB62DB8-2DBE2DC0-2DC62DC8-2DCE2DD0-2DD62DD8-2DDE2E2F300530063031-3035303B303C3041-3096309D-309F30A1-30FA30FC-30FF3105-312D3131-318E31A0-31B731F0-31FF3400-4DB54E00-9FCBA000-A48CA4D0-A4FDA500-A60CA610-A61FA62AA62BA640-A65FA662-A66EA67F-A697A6A0-A6E5A717-A71FA722-A788A78BA78CA7FB-A801A803-A805A807-A80AA80C-A822A840-A873A882-A8B3A8F2-A8F7A8FBA90A-A925A930-A946A960-A97CA984-A9B2A9CFAA00-AA28AA40-AA42AA44-AA4BAA60-AA76AA7AAA80-AAAFAAB1AAB5AAB6AAB9-AABDAAC0AAC2AADB-AADDABC0-ABE2AC00-D7A3D7B0-D7C6D7CB-D7FBF900-FA2DFA30-FA6DFA70-FAD9FB00-FB06FB13-FB17FB1DFB1F-FB28FB2A-FB36FB38-FB3CFB3EFB40FB41FB43FB44FB46-FBB1FBD3-FD3DFD50-FD8FFD92-FDC7FDF0-FDFBFE70-FE74FE76-FEFCFF21-FF3AFF41-FF5AFF66-FFBEFFC2-FFC7FFCA-FFCFFFD2-FFD7FFDA-FFDC",k = '-',j = "qx.lang.String",h = "(^|[^",g = "0",f = "%",c = ' ',e = '\n',d = "])[",b = "g",a = "";
  qx.Bootstrap.define(j, {
    statics : {
      __bu : l,
      __bv : null,
      __bw : {
      },
      camelCase : function(q){

        var r = this.__bw[q];
        if(!r){

          r = q.replace(/\-([a-z])/g, function(s, t){

            return t.toUpperCase();
          });
          this.__bw[q] = r;
        };
        return r;
      },
      hyphenate : function(u){

        var v = this.__bw[u];
        if(!v){

          v = u.replace(/[A-Z]/g, function(w){

            return (k + w.charAt(0).toLowerCase());
          });
          this.__bw[u] = v;
        };
        return v;
      },
      capitalize : function(x){

        if(this.__bv === null){

          var y = o;
          this.__bv = new RegExp(h + this.__bu.replace(/[0-9A-F]{4}/g, function(z){

            return y + z;
          }) + d + this.__bu.replace(/[0-9A-F]{4}/g, function(A){

            return y + A;
          }) + p, b);
        };
        return x.replace(this.__bv, function(B){

          return B.toUpperCase();
        });
      },
      clean : function(C){

        return this.trim(C.replace(/\s+/g, c));
      },
      trimLeft : function(D){

        return D.replace(/^\s+/, a);
      },
      trimRight : function(E){

        return E.replace(/\s+$/, a);
      },
      trim : function(F){

        return F.replace(/^\s+|\s+$/g, a);
      },
      startsWith : function(G, H){

        return G.indexOf(H) === 0;
      },
      endsWith : function(I, J){

        return I.substring(I.length - J.length, I.length) === J;
      },
      repeat : function(K, L){

        return K.length > 0 ? new Array(L + 1).join(K) : a;
      },
      pad : function(M, length, N){

        var O = length - M.length;
        if(O > 0){

          if(typeof N === n){

            N = g;
          };
          return this.repeat(N, O) + M;
        } else {

          return M;
        };
      },
      firstUp : qx.Bootstrap.firstUp,
      firstLow : qx.Bootstrap.firstLow,
      contains : function(P, Q){

        return P.indexOf(Q) != -1;
      },
      format : function(R, S){

        var T = R;
        var i = S.length;
        while(i--){

          T = T.replace(new RegExp(f + (i + 1), b), S[i] + a);
        };
        return T;
      },
      escapeRegexpChars : function(U){

        return U.replace(/([.*+?^${}()|[\]\/\\])/g, m);
      },
      toArray : function(V){

        return V.split(/\B|\b/g);
      },
      stripTags : function(W){

        return W.replace(/<\/?[^>]+>/gi, a);
      },
      stripScripts : function(X, Y){

        var bb = a;
        var ba = X.replace(/<script[^>]*>([\s\S]*?)<\/script>/gi, function(){

          bb += arguments[1] + e;
          return a;
        });
        if(Y === true){

          qx.lang.Function.globalEval(bb);
        };
        return ba;
      }
    }
  });
})();
(function(){

  var k = "[object Array]",j = "qx.lang.Array",h = "Cannot clean-up map entry doneObjects[",g = "]",f = "qx",e = "number",d = "][",c = "string",b = "mshtml",a = "engine.name";
  qx.Bootstrap.define(j, {
    statics : {
      toArray : function(m, n){

        return this.cast(m, Array, n);
      },
      cast : function(o, p, q){

        if(o.constructor === p){

          return o;
        };
        if(qx.data && qx.data.IListData){

          if(qx.Class && qx.Class.hasInterface(o, qx.data.IListData)){

            var o = o.toArray();
          };
        };
        var r = new p;
        if((qx.core.Environment.get(a) == b)){

          if(o.item){

            for(var i = q || 0,l = o.length;i < l;i++){

              r.push(o[i]);
            };
            return r;
          };
        };
        if(Object.prototype.toString.call(o) === k && q == null){

          r.push.apply(r, o);
        } else {

          r.push.apply(r, Array.prototype.slice.call(o, q || 0));
        };
        return r;
      },
      fromArguments : function(s, t){

        return Array.prototype.slice.call(s, t || 0);
      },
      fromCollection : function(u){

        if((qx.core.Environment.get(a) == b)){

          if(u.item){

            var v = [];
            for(var i = 0,l = u.length;i < l;i++){

              v[i] = u[i];
            };
            return v;
          };
        };
        return Array.prototype.slice.call(u, 0);
      },
      fromShortHand : function(w){

        var y = w.length;
        var x = qx.lang.Array.clone(w);
        switch(y){case 1:
        x[1] = x[2] = x[3] = x[0];
        break;case 2:
        x[2] = x[0];case 3:
        x[3] = x[1];};
        return x;
      },
      clone : function(z){

        return z.concat();
      },
      insertAt : function(A, B, i){

        A.splice(i, 0, B);
        return A;
      },
      insertBefore : function(C, D, E){

        var i = C.indexOf(E);
        if(i == -1){

          C.push(D);
        } else {

          C.splice(i, 0, D);
        };
        return C;
      },
      insertAfter : function(F, G, H){

        var i = F.indexOf(H);
        if(i == -1 || i == (F.length - 1)){

          F.push(G);
        } else {

          F.splice(i + 1, 0, G);
        };
        return F;
      },
      removeAt : function(I, i){

        return I.splice(i, 1)[0];
      },
      removeAll : function(J){

        J.length = 0;
        return this;
      },
      append : function(K, L){

        {
        };
        Array.prototype.push.apply(K, L);
        return K;
      },
      exclude : function(M, N){

        {
        };
        for(var i = 0,P = N.length,O;i < P;i++){

          O = M.indexOf(N[i]);
          if(O != -1){

            M.splice(O, 1);
          };
        };
        return M;
      },
      remove : function(Q, R){

        var i = Q.indexOf(R);
        if(i != -1){

          Q.splice(i, 1);
          return R;
        };
      },
      contains : function(S, T){

        return S.indexOf(T) !== -1;
      },
      equals : function(U, V){

        var length = U.length;
        if(length !== V.length){

          return false;
        };
        for(var i = 0;i < length;i++){

          if(U[i] !== V[i]){

            return false;
          };
        };
        return true;
      },
      sum : function(W){

        var X = 0;
        for(var i = 0,l = W.length;i < l;i++){

          X += W[i];
        };
        return X;
      },
      max : function(Y){

        {
        };
        var i,bb = Y.length,ba = Y[0];
        for(i = 1;i < bb;i++){

          if(Y[i] > ba){

            ba = Y[i];
          };
        };
        return ba === undefined ? null : ba;
      },
      min : function(bc){

        {
        };
        var i,be = bc.length,bd = bc[0];
        for(i = 1;i < be;i++){

          if(bc[i] < bd){

            bd = bc[i];
          };
        };
        return bd === undefined ? null : bd;
      },
      unique : function(bf){

        var bp = [],bh = {
        },bk = {
        },bm = {
        };
        var bl,bg = 0;
        var bq = f + qx.lang.Date.now();
        var bi = false,bo = false,br = false;
        for(var i = 0,bn = bf.length;i < bn;i++){

          bl = bf[i];
          if(bl === null){

            if(!bi){

              bi = true;
              bp.push(bl);
            };
          } else if(bl === undefined){
          } else if(bl === false){

            if(!bo){

              bo = true;
              bp.push(bl);
            };
          } else if(bl === true){

            if(!br){

              br = true;
              bp.push(bl);
            };
          } else if(typeof bl === c){

            if(!bh[bl]){

              bh[bl] = 1;
              bp.push(bl);
            };
          } else if(typeof bl === e){

            if(!bk[bl]){

              bk[bl] = 1;
              bp.push(bl);
            };
          } else {

            var bj = bl[bq];
            if(bj == null){

              bj = bl[bq] = bg++;
            };
            if(!bm[bj]){

              bm[bj] = bl;
              bp.push(bl);
            };
          };;;;;
        };
        for(var bj in bm){

          try{

            delete bm[bj][bq];
          } catch(bs) {

            try{

              bm[bj][bq] = null;
            } catch(bt) {

              throw new Error(h + bj + d + bq + g);
            };
          };
        };
        return bp;
      }
    }
  });
})();
(function(){

  var j = "[object Opera]",i = "[^\\.0-9]",h = "4.0",g = "1.9.0.0",f = "Version/",e = "9.0",d = "8.0",c = "Gecko",b = "AppleWebKit/",a = "opera",w = "engine.version",v = "mshtml",u = "engine.name",t = "webkit",s = "5.0",r = "qx.bom.client.Engine",q = "function",p = "gecko",o = "Maple",n = "Unsupported client: ",l = "",m = "! Assumed gecko version 1.9.0.0 (Firefox 3.0).",k = ".";
  qx.Bootstrap.define(r, {
    statics : {
      getVersion : function(){

        var A = window.navigator.userAgent;
        var y = l;
        if(qx.bom.client.Engine.__bx()){

          if(/Opera[\s\/]([0-9]+)\.([0-9])([0-9]*)/.test(A)){

            if(A.indexOf(f) != -1){

              var z = A.match(/Version\/(\d+)\.(\d+)/);
              y = z[1] + k + z[2].charAt(0) + k + z[2].substring(1, z[2].length);
            } else {

              y = RegExp.$1 + k + RegExp.$2;
              if(RegExp.$3 != l){

                y += k + RegExp.$3;
              };
            };
          };
        } else if(qx.bom.client.Engine.__by()){

          if(/AppleWebKit\/([^ ]+)/.test(A)){

            y = RegExp.$1;
            var B = RegExp(i).exec(y);
            if(B){

              y = y.slice(0, B.index);
            };
          };
        } else if(qx.bom.client.Engine.__bA() || qx.bom.client.Engine.__bz()){

          if(/rv\:([^\);]+)(\)|;)/.test(A)){

            y = RegExp.$1;
          };
        } else if(qx.bom.client.Engine.__bB()){

          if(/MSIE\s+([^\);]+)(\)|;)/.test(A)){

            y = RegExp.$1;
            if(y < 8 && /Trident\/([^\);]+)(\)|;)/.test(A)){

              if(RegExp.$1 == h){

                y = d;
              } else if(RegExp.$1 == s){

                y = e;
              };
            };
          };
        } else {

          var x = window.qxFail;
          if(x && typeof x === q){

            y = x().FULLVERSION;
          } else {

            y = g;
            qx.Bootstrap.warn(n + A + m);
          };
        };;;
        return y;
      },
      getName : function(){

        var name;
        if(qx.bom.client.Engine.__bx()){

          name = a;
        } else if(qx.bom.client.Engine.__by()){

          name = t;
        } else if(qx.bom.client.Engine.__bA() || qx.bom.client.Engine.__bz()){

          name = p;
        } else if(qx.bom.client.Engine.__bB()){

          name = v;
        } else {

          var C = window.qxFail;
          if(C && typeof C === q){

            name = C().NAME;
          } else {

            name = p;
            qx.Bootstrap.warn(n + window.navigator.userAgent + m);
          };
        };;;
        return name;
      },
      __bx : function(){

        return window.opera && Object.prototype.toString.call(window.opera) == j;
      },
      __by : function(){

        return window.navigator.userAgent.indexOf(b) != -1;
      },
      __bz : function(){

        return window.navigator.userAgent.indexOf(o) != -1;
      },
      __bA : function(){

        return window.controllers && window.navigator.product === c && window.navigator.userAgent.indexOf(o) == -1;
      },
      __bB : function(){

        return window.navigator.cpuClass && /MSIE\s+([^\);]+)(\)|;)/.test(window.navigator.userAgent);
      }
    },
    defer : function(D){

      qx.core.Environment.add(w, D.getVersion);
      qx.core.Environment.add(u, D.getName);
    }
  });
})();
(function(){

  var a = "qx.lang.Date";
  qx.Bootstrap.define(a, {
    statics : {
      now : function(){

        return +new Date;
      }
    }
  });
})();
(function(){

  var g = 'anonymous()',f = "qx.globalErrorHandling",e = "qx.lang.Function",d = ".constructor()",c = ".",b = ".prototype.",a = "()";
  qx.Bootstrap.define(e, {
    statics : {
      getCaller : function(h){

        return h.caller ? h.caller.callee : h.callee.caller;
      },
      getName : function(i){

        if(i.displayName){

          return i.displayName;
        };
        if(i.$$original || i.wrapper || i.classname){

          return i.classname + d;
        };
        if(i.$$mixin){

          for(var k in i.$$mixin.$$members){

            if(i.$$mixin.$$members[k] == i){

              return i.$$mixin.name + b + k + a;
            };
          };
          for(var k in i.$$mixin){

            if(i.$$mixin[k] == i){

              return i.$$mixin.name + c + k + a;
            };
          };
        };
        if(i.self){

          var l = i.self.constructor;
          if(l){

            for(var k in l.prototype){

              if(l.prototype[k] == i){

                return l.classname + b + k + a;
              };
            };
            for(var k in l){

              if(l[k] == i){

                return l.classname + c + k + a;
              };
            };
          };
        };
        var j = i.toString().match(/function\s*(\w*)\s*\(.*/);
        if(j && j.length >= 1 && j[1]){

          return j[1] + a;
        };
        return g;
      },
      globalEval : function(m){

        if(window.execScript){

          return window.execScript(m);
        } else {

          return eval.call(window, m);
        };
      },
      empty : function(){
      },
      returnTrue : function(){

        return true;
      },
      returnFalse : function(){

        return false;
      },
      returnNull : function(){

        return null;
      },
      returnThis : function(){

        return this;
      },
      returnZero : function(){

        return 0;
      },
      create : function(n, o){

        {
        };
        if(!o){

          return n;
        };
        if(!(o.self || o.args || o.delay != null || o.periodical != null || o.attempt)){

          return n;
        };
        return function(event){

          {
          };
          var q = qx.lang.Array.fromArguments(arguments);
          if(o.args){

            q = o.args.concat(q);
          };
          if(o.delay || o.periodical){

            var p = function(){

              return n.apply(o.self || this, q);
            };
            if(qx.core.Environment.get(f)){

              p = qx.event.GlobalError.observeMethod(p);
            };
            if(o.delay){

              return window.setTimeout(p, o.delay);
            };
            if(o.periodical){

              return window.setInterval(p, o.periodical);
            };
          } else if(o.attempt){

            var r = false;
            try{

              r = n.apply(o.self || this, q);
            } catch(s) {
            };
            return r;
          } else {

            return n.apply(o.self || this, q);
          };
        };
      },
      bind : function(t, self, u){

        return this.create(t, {
          self : self,
          args : arguments.length > 2 ? qx.lang.Array.fromArguments(arguments, 2) : null
        });
      },
      curry : function(v, w){

        return this.create(v, {
          args : arguments.length > 1 ? qx.lang.Array.fromArguments(arguments, 1) : null
        });
      },
      listener : function(x, self, y){

        if(arguments.length < 3){

          return function(event){

            return x.call(self || this, event || window.event);
          };
        } else {

          var z = qx.lang.Array.fromArguments(arguments, 2);
          return function(event){

            var A = [event || window.event];
            A.push.apply(A, z);
            x.apply(self || this, A);
          };
        };
      },
      attempt : function(B, self, C){

        return this.create(B, {
          self : self,
          attempt : true,
          args : arguments.length > 2 ? qx.lang.Array.fromArguments(arguments, 2) : null
        })();
      },
      delay : function(D, E, self, F){

        return this.create(D, {
          delay : E,
          self : self,
          args : arguments.length > 3 ? qx.lang.Array.fromArguments(arguments, 3) : null
        })();
      },
      periodical : function(G, H, self, I){

        return this.create(G, {
          periodical : H,
          self : self,
          args : arguments.length > 3 ? qx.lang.Array.fromArguments(arguments, 3) : null
        })();
      }
    }
  });
})();
(function(){

  var c = "qx.event.type.Data",b = "qx.event.type.Event",a = "qx.data.IListData";
  qx.Interface.define(a, {
    events : {
      "change" : c,
      "changeLength" : b
    },
    members : {
      getItem : function(d){
      },
      setItem : function(e, f){
      },
      splice : function(g, h, i){
      },
      contains : function(j){
      },
      getLength : function(){
      },
      toArray : function(){
      }
    }
  });
})();
(function(){

  var c = ": ",b = "qx.type.BaseError",a = "";
  qx.Class.define(b, {
    extend : Error,
    construct : function(d, e){

      var f = Error.call(this, e);
      if(f.stack){

        this.stack = f.stack;
      };
      if(f.stacktrace){

        this.stacktrace = f.stacktrace;
      };
      if(!(f.stack || f.stacktrace)){

        this.__bC = qx.dev.StackTrace.getStackTraceFromCaller(arguments);
      };
      this.__bD = d || a;
      this.message = e || qx.type.BaseError.DEFAULTMESSAGE;
    },
    statics : {
      DEFAULTMESSAGE : "error"
    },
    members : {
      __bC : null,
      __bD : null,
      message : null,
      getComment : function(){

        return this.__bD;
      },
      getStackTrace : function(){

        if(this.stack || this.stacktrace){

          return qx.dev.StackTrace.getStackTraceFromError(this);
        } else if(this.__bC){

          return this.__bC;
        };
        return [];
      },
      toString : function(){

        return this.__bD + (this.message ? c + this.message : a);
      }
    }
  });
})();
(function(){

  var s = "anonymous",r = "...",q = "qx.dev.StackTrace",p = "",o = "\n",n = "?",m = "/source/class/",l = "FILENAME_TO_CLASSNAME must return a string!",k = "stack",j = "FORMAT_STACKTRACE must return an array of strings!",c = "prototype",h = "stacktrace",f = "Error created at",b = "Backtrace:",a = "function",e = "ecmascript.stacktrace",d = ".",g = ":";
  qx.Bootstrap.define(q, {
    statics : {
      FILENAME_TO_CLASSNAME : null,
      FORMAT_STACKTRACE : null,
      getStackTrace : function(){

        var x = [];
        try{

          throw new Error();
        } catch(I) {

          if(qx.core.Environment.get(e)){

            var C = qx.dev.StackTrace.getStackTraceFromError(I);
            var A = qx.dev.StackTrace.getStackTraceFromCaller(arguments);
            qx.lang.Array.removeAt(C, 0);
            var x = A.length > C.length ? A : C;
            for(var i = 0;i < Math.min(A.length, C.length);i++){

              var y = A[i];
              if(y.indexOf(s) >= 0){

                continue;
              };
              var w;
              var G = y.split(d);
              var z = /(.*?)\(/.exec(G[G.length - 1]);
              if(z && z.length == 2){

                w = z[1];
                G.pop();
              };
              if(G[G.length - 1] == c){

                G.pop();
              };
              var E = G.join(d);
              var v = C[i];
              var H = v.split(g);
              var D = H[0];
              var t = H[1];
              var u;
              if(H[2]){

                u = H[2];
              };
              if(qx.Class.getByName(D)){

                var B = D;
              } else {

                B = E;
              };
              var F = B + d;
              if(w){

                F += w + g;
              };
              F += t;
              if(u){

                F += g + u;
              };
              x[i] = F;
            };
          } else {

            x = this.getStackTraceFromCaller(arguments);
          };
        };
        return x;
      },
      getStackTraceFromCaller : function(J){

        var O = [];
        var N = qx.lang.Function.getCaller(J);
        var K = {
        };
        while(N){

          var L = qx.lang.Function.getName(N);
          O.push(L);
          try{

            N = N.caller;
          } catch(P) {

            break;
          };
          if(!N){

            break;
          };
          var M = qx.core.ObjectRegistry.toHashCode(N);
          if(K[M]){

            O.push(r);
            break;
          };
          K[M] = N;
        };
        return O;
      },
      getStackTraceFromError : function(Q){

        var U = [];
        if(qx.core.Environment.get(e) === k){

          if(!Q.stack){

            return U;
          };
          var bg = /@(.+):(\d+)$/gm;
          var T;
          while((T = bg.exec(Q.stack)) != null){

            var W = T[1];
            var be = T[2];
            var bc = this.__bE(W);
            U.push(bc + g + be);
          };
          if(U.length > 0){

            return this.__bG(U);
          };
          var bg = /at (.*)/gm;
          var bf = /\((.*?)(:[^\/].*)\)/;
          var bb = /(.*?)(:[^\/].*)/;
          var T;
          while((T = bg.exec(Q.stack)) != null){

            var ba = bf.exec(T[1]);
            if(!ba){

              ba = bb.exec(T[1]);
            };
            if(ba){

              var bc = this.__bE(ba[1]);
              U.push(bc + ba[2]);
            } else {

              U.push(T[1]);
            };
          };
        } else if(qx.core.Environment.get(e) === h){

          var S = Q.stacktrace;
          if(!S){

            return U;
          };
          if(S.indexOf(f) >= 0){

            S = S.split(f)[0];
          };
          var bg = /line\ (\d+?),\ column\ (\d+?)\ in\ (?:.*?)\ in\ (.*?):[^\/]/gm;
          var T;
          while((T = bg.exec(S)) != null){

            var be = T[1];
            var V = T[2];
            var W = T[3];
            var bc = this.__bE(W);
            U.push(bc + g + be + g + V);
          };
          if(U.length > 0){

            return this.__bG(U);
          };
          var bg = /Line\ (\d+?)\ of\ linked\ script\ (.*?)$/gm;
          var T;
          while((T = bg.exec(S)) != null){

            var be = T[1];
            var W = T[2];
            var bc = this.__bE(W);
            U.push(bc + g + be);
          };
        } else if(Q.message && Q.message.indexOf(b) >= 0){

          var Y = qx.lang.String.trim(Q.message.split(b)[1]);
          var X = Y.split(o);
          for(var i = 0;i < X.length;i++){

            var R = X[i].match(/\s*Line ([0-9]+) of.* (\S.*)/);
            if(R && R.length >= 2){

              var be = R[1];
              var bd = this.__bE(R[2]);
              U.push(bd + g + be);
            };
          };
        } else if(Q.sourceURL && Q.line){

          U.push(this.__bE(Q.sourceURL) + g + Q.line);
        };;;
        return this.__bG(U);
      },
      __bE : function(bh){

        if(typeof qx.dev.StackTrace.FILENAME_TO_CLASSNAME == a){

          var bi = qx.dev.StackTrace.FILENAME_TO_CLASSNAME(bh);
          if(false && !qx.lang.Type.isString(bi)){

            throw new Error(l);
          };
          return bi;
        };
        return qx.dev.StackTrace.__bF(bh);
      },
      __bF : function(bj){

        var bn = m;
        var bk = bj.indexOf(bn);
        var bm = bj.indexOf(n);
        if(bm >= 0){

          bj = bj.substring(0, bm);
        };
        var bl = (bk == -1) ? bj : bj.substring(bk + bn.length).replace(/\//g, d).replace(/\.js$/, p);
        return bl;
      },
      __bG : function(bo){

        if(typeof qx.dev.StackTrace.FORMAT_STACKTRACE == a){

          bo = qx.dev.StackTrace.FORMAT_STACKTRACE(bo);
          if(false && !qx.lang.Type.isArray(bo)){

            throw new Error(j);
          };
        };
        return bo;
      }
    }
  });
})();
(function(){

  var d = "stack",c = "ecmascript.stacktrace",b = "stacktrace",a = "qx.bom.client.EcmaScript";
  qx.Bootstrap.define(a, {
    statics : {
      getStackTrace : function(){

        try{

          throw new Error();
        } catch(e) {

          return e.stacktrace ? b : e.stack ? d : null;
        };
      }
    },
    defer : function(f){

      qx.core.Environment.add(c, f.getStackTrace);
    }
  });
})();
(function(){

  var m = "-",k = "",j = "qx.core.ObjectRegistry",h = "Disposed ",g = "-0",f = " objects",e = "Could not dispose object ",d = ": ",c = "$$hash";
  qx.Class.define(j, {
    statics : {
      inShutDown : false,
      __j : {
      },
      __bH : 0,
      __bI : [],
      __bJ : k,
      __bK : {
      },
      register : function(n){

        var q = this.__j;
        if(!q){

          return;
        };
        var p = n.$$hash;
        if(p == null){

          var o = this.__bI;
          if(o.length > 0 && true){

            p = o.pop();
          } else {

            p = (this.__bH++) + this.__bJ;
          };
          n.$$hash = p;
          {
          };
        };
        {
        };
        q[p] = n;
      },
      unregister : function(r){

        var s = r.$$hash;
        if(s == null){

          return;
        };
        var t = this.__j;
        if(t && t[s]){

          delete t[s];
          this.__bI.push(s);
        };
        try{

          delete r.$$hash;
        } catch(u) {

          if(r.removeAttribute){

            r.removeAttribute(c);
          };
        };
      },
      toHashCode : function(v){

        {
        };
        var x = v.$$hash;
        if(x != null){

          return x;
        };
        var w = this.__bI;
        if(w.length > 0){

          x = w.pop();
        } else {

          x = (this.__bH++) + this.__bJ;
        };
        return v.$$hash = x;
      },
      clearHashCode : function(y){

        {
        };
        var z = y.$$hash;
        if(z != null){

          this.__bI.push(z);
          try{

            delete y.$$hash;
          } catch(A) {

            if(y.removeAttribute){

              y.removeAttribute(c);
            };
          };
        };
      },
      fromHashCode : function(B){

        return this.__j[B] || null;
      },
      shutdown : function(){

        this.inShutDown = true;
        var D = this.__j;
        var F = [];
        for(var E in D){

          F.push(E);
        };
        F.sort(function(a, b){

          return parseInt(b, 10) - parseInt(a, 10);
        });
        var C,i = 0,l = F.length;
        while(true){

          try{

            for(;i < l;i++){

              E = F[i];
              C = D[E];
              if(C && C.dispose){

                C.dispose();
              };
            };
          } catch(G) {

            qx.Bootstrap.error(this, e + C.toString() + d + G, G);
            if(i !== l){

              i++;
              continue;
            };
          };
          break;
        };
        qx.Bootstrap.debug(this, h + l + f);
        delete this.__j;
      },
      getRegistry : function(){

        return this.__j;
      },
      getNextHash : function(){

        return this.__bH;
      },
      getPostId : function(){

        return this.__bJ;
      },
      getStackTraces : function(){

        return this.__bK;
      }
    },
    defer : function(H){

      if(window && window.top){

        var frames = window.top.frames;
        for(var i = 0;i < frames.length;i++){

          if(frames[i] === window){

            H.__bJ = m + (i + 1);
            return;
          };
        };
      };
      H.__bJ = g;
    }
  });
})();
(function(){

  var f = "qx.lang.Type",e = "Error",d = "RegExp",c = "Date",b = "Number",a = "Boolean";
  qx.Bootstrap.define(f, {
    statics : {
      getClass : qx.Bootstrap.getClass,
      isString : qx.Bootstrap.isString,
      isArray : qx.Bootstrap.isArray,
      isObject : qx.Bootstrap.isObject,
      isFunction : qx.Bootstrap.isFunction,
      isRegExp : function(g){

        return this.getClass(g) == d;
      },
      isNumber : function(h){

        return (h !== null && (this.getClass(h) == b || h instanceof Number));
      },
      isBoolean : function(i){

        return (i !== null && (this.getClass(i) == a || i instanceof Boolean));
      },
      isDate : function(j){

        return (j !== null && (this.getClass(j) == c || j instanceof Date));
      },
      isError : function(k){

        return (k !== null && (this.getClass(k) == e || k instanceof Error));
      }
    }
  });
})();
(function(){

  var a = "qx.core.AssertionError";
  qx.Class.define(a, {
    extend : qx.type.BaseError,
    construct : function(b, c){

      qx.type.BaseError.call(this, b, c);
      this.__bL = qx.dev.StackTrace.getStackTrace();
    },
    members : {
      __bL : null,
      getStackTrace : function(){

        return this.__bL;
      }
    }
  });
})();
(function(){

  var a = "qx.core.ValidationError";
  qx.Class.define(a, {
    extend : qx.type.BaseError
  });
})();
(function(){

  var a = "qx.lang.RingBuffer";
  qx.Class.define(a, {
    extend : Object,
    construct : function(b){

      this.setMaxEntries(b || 50);
    },
    members : {
      __bM : 0,
      __bN : 0,
      __bO : false,
      __bP : 0,
      __bQ : null,
      __bR : null,
      setMaxEntries : function(c){

        this.__bR = c;
        this.clear();
      },
      getMaxEntries : function(){

        return this.__bR;
      },
      addEntry : function(d){

        this.__bQ[this.__bM] = d;
        this.__bM = this.__bS(this.__bM, 1);
        var e = this.getMaxEntries();
        if(this.__bN < e){

          this.__bN++;
        };
        if(this.__bO && (this.__bP < e)){

          this.__bP++;
        };
      },
      mark : function(){

        this.__bO = true;
        this.__bP = 0;
      },
      clearMark : function(){

        this.__bO = false;
      },
      getAllEntries : function(){

        return this.getEntries(this.getMaxEntries(), false);
      },
      getEntries : function(f, g){

        if(f > this.__bN){

          f = this.__bN;
        };
        if(g && this.__bO && (f > this.__bP)){

          f = this.__bP;
        };
        if(f > 0){

          var i = this.__bS(this.__bM, -1);
          var h = this.__bS(i, -f + 1);
          var j;
          if(h <= i){

            j = this.__bQ.slice(h, i + 1);
          } else {

            j = this.__bQ.slice(h, this.__bN).concat(this.__bQ.slice(0, i + 1));
          };
        } else {

          j = [];
        };
        return j;
      },
      clear : function(){

        this.__bQ = new Array(this.getMaxEntries());
        this.__bN = 0;
        this.__bP = 0;
        this.__bM = 0;
      },
      __bS : function(k, l){

        var m = this.getMaxEntries();
        var n = (k + l) % m;
        if(n < 0){

          n += m;
        };
        return n;
      }
    }
  });
})();
(function(){

  var a = "qx.log.appender.RingBuffer";
  qx.Class.define(a, {
    extend : qx.lang.RingBuffer,
    construct : function(b){

      this.setMaxMessages(b || 50);
    },
    members : {
      setMaxMessages : function(c){

        this.setMaxEntries(c);
      },
      getMaxMessages : function(){

        return this.getMaxEntries();
      },
      process : function(d){

        this.addEntry(d);
      },
      getAllLogEvents : function(){

        return this.getAllEntries();
      },
      retrieveLogEvents : function(e, f){

        return this.getEntries(e, f);
      },
      clearHistory : function(){

        this.clear();
      }
    }
  });
})();
(function(){

  var k = "qx.log.Logger",j = "[",h = "#",g = "warn",f = "document",e = "{...(",d = "text[",c = "[...(",b = "\n",a = ")}",H = ")]",G = "object",F = "...(+",E = "array",D = ")",C = "info",B = "instance",A = "string",z = "null",y = "class",s = "number",t = "stringify",q = "]",r = "date",o = "unknown",p = "function",m = "boolean",n = "debug",u = "map",v = "node",x = "error",w = "undefined";
  qx.Class.define(k, {
    statics : {
      __bT : n,
      setLevel : function(I){

        this.__bT = I;
      },
      getLevel : function(){

        return this.__bT;
      },
      setTreshold : function(J){

        this.__bW.setMaxMessages(J);
      },
      getTreshold : function(){

        return this.__bW.getMaxMessages();
      },
      __bU : {
      },
      __bV : 0,
      register : function(K){

        if(K.$$id){

          return;
        };
        var M = this.__bV++;
        this.__bU[M] = K;
        K.$$id = M;
        var L = this.__bX;
        var N = this.__bW.getAllLogEvents();
        for(var i = 0,l = N.length;i < l;i++){

          if(L[N[i].level] >= L[this.__bT]){

            K.process(N[i]);
          };
        };
      },
      unregister : function(O){

        var P = O.$$id;
        if(P == null){

          return;
        };
        delete this.__bU[P];
        delete O.$$id;
      },
      debug : function(Q, R){

        qx.log.Logger.__bY(n, arguments);
      },
      info : function(S, T){

        qx.log.Logger.__bY(C, arguments);
      },
      warn : function(U, V){

        qx.log.Logger.__bY(g, arguments);
      },
      error : function(W, X){

        qx.log.Logger.__bY(x, arguments);
      },
      trace : function(Y){

        var ba = qx.dev.StackTrace.getStackTrace();
        qx.log.Logger.__bY(C, [(typeof Y !== w ? [Y].concat(ba) : ba).join(b)]);
      },
      deprecatedMethodWarning : function(bb, bc){

        var bd;
        {
        };
      },
      deprecatedClassWarning : function(be, bf){

        var bg;
        {
        };
      },
      deprecatedEventWarning : function(bh, event, bi){

        var bj;
        {
        };
      },
      deprecatedMixinWarning : function(bk, bl){

        var bm;
        {
        };
      },
      deprecatedConstantWarning : function(bn, bo, bp){

        var self,bq;
        {
        };
      },
      deprecateMethodOverriding : function(br, bs, bt, bu){

        var bv;
        {
        };
      },
      clear : function(){

        this.__bW.clearHistory();
      },
      __bW : new qx.log.appender.RingBuffer(50),
      __bX : {
        debug : 0,
        info : 1,
        warn : 2,
        error : 3
      },
      __bY : function(bw, bx){

        var bC = this.__bX;
        if(bC[bw] < bC[this.__bT]){

          return;
        };
        var bz = bx.length < 2 ? null : bx[0];
        var bB = bz ? 1 : 0;
        var by = [];
        for(var i = bB,l = bx.length;i < l;i++){

          by.push(this.__cb(bx[i], true));
        };
        var bD = new Date;
        var bE = {
          time : bD,
          offset : bD - qx.Bootstrap.LOADSTART,
          level : bw,
          items : by,
          win : window
        };
        if(bz){

          if(bz.$$hash !== undefined){

            bE.object = bz.$$hash;
          } else if(bz.$$type){

            bE.clazz = bz;
          };
        };
        this.__bW.process(bE);
        var bF = this.__bU;
        for(var bA in bF){

          bF[bA].process(bE);
        };
      },
      __ca : function(bG){

        if(bG === undefined){

          return w;
        } else if(bG === null){

          return z;
        };
        if(bG.$$type){

          return y;
        };
        var bH = typeof bG;
        if(bH === p || bH == A || bH === s || bH === m){

          return bH;
        } else if(bH === G){

          if(bG.nodeType){

            return v;
          } else if(bG.classname){

            return B;
          } else if(bG instanceof Array){

            return E;
          } else if(bG instanceof Error){

            return x;
          } else if(bG instanceof Date){

            return r;
          } else {

            return u;
          };;;;
        };
        if(bG.toString){

          return t;
        };
        return o;
      },
      __cb : function(bI, bJ){

        var bQ = this.__ca(bI);
        var bM = o;
        var bL = [];
        switch(bQ){case z:case w:
        bM = bQ;
        break;case A:case s:case m:case r:
        bM = bI;
        break;case v:
        if(bI.nodeType === 9){

          bM = f;
        } else if(bI.nodeType === 3){

          bM = d + bI.nodeValue + q;
        } else if(bI.nodeType === 1){

          bM = bI.nodeName.toLowerCase();
          if(bI.id){

            bM += h + bI.id;
          };
        } else {

          bM = v;
        };;
        break;case p:
        bM = qx.lang.Function.getName(bI) || bQ;
        break;case B:
        bM = bI.basename + j + bI.$$hash + q;
        break;case y:case t:
        bM = bI.toString();
        break;case x:
        bL = qx.dev.StackTrace.getStackTraceFromError(bI);
        bM = bI.toString();
        break;case E:
        if(bJ){

          bM = [];
          for(var i = 0,l = bI.length;i < l;i++){

            if(bM.length > 20){

              bM.push(F + (l - i) + D);
              break;
            };
            bM.push(this.__cb(bI[i], false));
          };
        } else {

          bM = c + bI.length + H;
        };
        break;case u:
        if(bJ){

          var bK;
          var bP = [];
          for(var bO in bI){

            bP.push(bO);
          };
          bP.sort();
          bM = [];
          for(var i = 0,l = bP.length;i < l;i++){

            if(bM.length > 20){

              bM.push(F + (l - i) + D);
              break;
            };
            bO = bP[i];
            bK = this.__cb(bI[bO], false);
            bK.key = bO;
            bM.push(bK);
          };
        } else {

          var bN = 0;
          for(var bO in bI){

            bN++;
          };
          bM = e + bN + a;
        };
        break;};
        return {
          type : bQ,
          text : bM,
          trace : bL
        };
      }
    },
    defer : function(bR){

      var bS = qx.Bootstrap.$$logs;
      for(var i = 0;i < bS.length;i++){

        bR.__bY(bS[i][0], bS[i][1]);
      };
      qx.Bootstrap.debug = bR.debug;
      qx.Bootstrap.info = bR.info;
      qx.Bootstrap.warn = bR.warn;
      qx.Bootstrap.error = bR.error;
      qx.Bootstrap.trace = bR.trace;
    }
  });
})();
(function(){

  var d = "qx.core.MProperty",c = "reset",b = "get",a = "set";
  qx.Mixin.define(d, {
    members : {
      set : function(e, f){

        var h = qx.core.Property.$$method.set;
        if(qx.Bootstrap.isString(e)){

          if(!this[h[e]]){

            if(this[a + qx.Bootstrap.firstUp(e)] != undefined){

              this[a + qx.Bootstrap.firstUp(e)](f);
              return this;
            };
            {
            };
          };
          return this[h[e]](f);
        } else {

          for(var g in e){

            if(!this[h[g]]){

              if(this[a + qx.Bootstrap.firstUp(g)] != undefined){

                this[a + qx.Bootstrap.firstUp(g)](e[g]);
                continue;
              };
              {
              };
            };
            this[h[g]](e[g]);
          };
          return this;
        };
      },
      get : function(i){

        var j = qx.core.Property.$$method.get;
        if(!this[j[i]]){

          if(this[b + qx.Bootstrap.firstUp(i)] != undefined){

            return this[b + qx.Bootstrap.firstUp(i)]();
          };
          {
          };
        };
        return this[j[i]]();
      },
      reset : function(k){

        var l = qx.core.Property.$$method.reset;
        if(!this[l[k]]){

          if(this[c + qx.Bootstrap.firstUp(k)] != undefined){

            this[c + qx.Bootstrap.firstUp(k)]();
            return;
          };
          {
          };
        };
        this[l[k]]();
      }
    }
  });
})();
(function(){

  var e = "info",d = "debug",c = "warn",b = "qx.core.MLogging",a = "error";
  qx.Mixin.define(b, {
    members : {
      __cc : qx.log.Logger,
      debug : function(f){

        this.__cd(d, arguments);
      },
      info : function(g){

        this.__cd(e, arguments);
      },
      warn : function(h){

        this.__cd(c, arguments);
      },
      error : function(i){

        this.__cd(a, arguments);
      },
      trace : function(){

        this.__cc.trace(this);
      },
      __cd : function(j, k){

        var l = qx.lang.Array.fromArguments(k);
        l.unshift(this);
        this.__cc[j].apply(this.__cc, l);
      }
    }
  });
})();
(function(){

  var c = "qx.dom.Node",b = "";
  qx.Bootstrap.define(c, {
    statics : {
      ELEMENT : 1,
      ATTRIBUTE : 2,
      TEXT : 3,
      CDATA_SECTION : 4,
      ENTITY_REFERENCE : 5,
      ENTITY : 6,
      PROCESSING_INSTRUCTION : 7,
      COMMENT : 8,
      DOCUMENT : 9,
      DOCUMENT_TYPE : 10,
      DOCUMENT_FRAGMENT : 11,
      NOTATION : 12,
      getDocument : function(d){

        return d.nodeType === this.DOCUMENT ? d : d.ownerDocument || d.document;
      },
      getWindow : function(e){

        if(e.nodeType == null){

          return e;
        };
        if(e.nodeType !== this.DOCUMENT){

          e = e.ownerDocument;
        };
        return e.defaultView || e.parentWindow;
      },
      getDocumentElement : function(f){

        return this.getDocument(f).documentElement;
      },
      getBodyElement : function(g){

        return this.getDocument(g).body;
      },
      isNode : function(h){

        return !!(h && h.nodeType != null);
      },
      isElement : function(j){

        return !!(j && j.nodeType === this.ELEMENT);
      },
      isDocument : function(k){

        return !!(k && k.nodeType === this.DOCUMENT);
      },
      isText : function(l){

        return !!(l && l.nodeType === this.TEXT);
      },
      isWindow : function(m){

        return !!(m && m.history && m.location && m.document);
      },
      isNodeName : function(n, o){

        if(!o || !n || !n.nodeName){

          return false;
        };
        return o.toLowerCase() == qx.dom.Node.getName(n);
      },
      getName : function(p){

        if(!p || !p.nodeName){

          return null;
        };
        return p.nodeName.toLowerCase();
      },
      getText : function(q){

        if(!q || !q.nodeType){

          return null;
        };
        switch(q.nodeType){case 1:
        var i,a = [],r = q.childNodes,length = r.length;
        for(i = 0;i < length;i++){

          a[i] = this.getText(r[i]);
        };
        return a.join(b);case 2:case 3:case 4:
        return q.nodeValue;};
        return null;
      },
      isBlockNode : function(s){

        if(!qx.dom.Node.isElement(s)){

          return false;
        };
        s = qx.dom.Node.getName(s);
        return /^(body|form|textarea|fieldset|ul|ol|dl|dt|dd|li|div|hr|p|h[1-6]|quote|pre|table|thead|tbody|tfoot|tr|td|th|iframe|address|blockquote)$/.test(s);
      }
    }
  });
})();
(function(){

  var j = "HTMLEvents",i = "engine.name",h = "qx.bom.Event",g = "return;",f = "mouseover",d = "gecko",c = "function",b = "undefined",a = "on";
  qx.Bootstrap.define(h, {
    statics : {
      addNativeListener : function(k, l, m, n){

        if(k.addEventListener){

          k.addEventListener(l, m, !!n);
        } else if(k.attachEvent){

          k.attachEvent(a + l, m);
        } else if(typeof k[a + l] != b){

          k[a + l] = m;
        } else {

          {
          };
        };;
      },
      removeNativeListener : function(o, p, q, r){

        if(o.removeEventListener){

          o.removeEventListener(p, q, !!r);
        } else if(o.detachEvent){

          try{

            o.detachEvent(a + p, q);
          } catch(e) {

            if(e.number !== -2146828218){

              throw e;
            };
          };
        } else if(typeof o[a + p] != b){

          o[a + p] = null;
        } else {

          {
          };
        };;
      },
      getTarget : function(e){

        return e.target || e.srcElement;
      },
      getRelatedTarget : function(e){

        if(e.relatedTarget !== undefined){

          if((qx.core.Environment.get(i) == d)){

            try{

              e.relatedTarget && e.relatedTarget.nodeType;
            } catch(e) {

              return null;
            };
          };
          return e.relatedTarget;
        } else if(e.fromElement !== undefined && e.type === f){

          return e.fromElement;
        } else if(e.toElement !== undefined){

          return e.toElement;
        } else {

          return null;
        };;
      },
      preventDefault : function(e){

        if(e.preventDefault){

          e.preventDefault();
        } else {

          try{

            e.keyCode = 0;
          } catch(s) {
          };
          e.returnValue = false;
        };
      },
      stopPropagation : function(e){

        if(e.stopPropagation){

          e.stopPropagation();
        } else {

          e.cancelBubble = true;
        };
      },
      fire : function(t, u){

        if(document.createEvent){

          var v = document.createEvent(j);
          v.initEvent(u, true, true);
          return !t.dispatchEvent(v);
        } else {

          var v = document.createEventObject();
          return t.fireEvent(a + u, v);
        };
      },
      supportsEvent : function(w, x){

        var y = a + x;
        var z = (y in w);
        if(!z){

          z = typeof w[y] == c;
          if(!z && w.setAttribute){

            w.setAttribute(y, g);
            z = typeof w[y] == c;
            w.removeAttribute(y);
          };
        };
        return z;
      }
    }
  });
})();
(function(){

  var r = "UNKNOWN_",q = "__cj",p = "c",o = "DOM_",n = "__ci",m = "WIN_",k = "QX_",j = "qx.event.Manager",h = "capture",g = "DOCUMENT_",c = "unload",f = "",e = "_",b = "|",a = "|bubble",d = "|capture";
  qx.Class.define(j, {
    extend : Object,
    construct : function(s, t){

      this.__ce = s;
      this.__cf = qx.core.ObjectRegistry.toHashCode(s);
      this.__cg = t;
      if(s.qx !== qx){

        var self = this;
        qx.bom.Event.addNativeListener(s, c, qx.event.GlobalError.observeMethod(function(){

          qx.bom.Event.removeNativeListener(s, c, arguments.callee);
          self.dispose();
        }));
      };
      this.__ch = {
      };
      this.__ci = {
      };
      this.__cj = {
      };
      this.__ck = {
      };
    },
    statics : {
      __cl : 0,
      getNextUniqueId : function(){

        return (this.__cl++) + f;
      }
    },
    members : {
      __cg : null,
      __ch : null,
      __cj : null,
      __cm : null,
      __ci : null,
      __ck : null,
      __ce : null,
      __cf : null,
      getWindow : function(){

        return this.__ce;
      },
      getWindowId : function(){

        return this.__cf;
      },
      getHandler : function(u){

        var v = this.__ci[u.classname];
        if(v){

          return v;
        };
        return this.__ci[u.classname] = new u(this);
      },
      getDispatcher : function(w){

        var x = this.__cj[w.classname];
        if(x){

          return x;
        };
        return this.__cj[w.classname] = new w(this, this.__cg);
      },
      getListeners : function(y, z, A){

        var B = y.$$hash || qx.core.ObjectRegistry.toHashCode(y);
        var D = this.__ch[B];
        if(!D){

          return null;
        };
        var E = z + (A ? d : a);
        var C = D[E];
        return C ? C.concat() : null;
      },
      getAllListeners : function(){

        return this.__ch;
      },
      serializeListeners : function(F){

        var M = F.$$hash || qx.core.ObjectRegistry.toHashCode(F);
        var O = this.__ch[M];
        var K = [];
        if(O){

          var I,N,G,J,L;
          for(var H in O){

            I = H.indexOf(b);
            N = H.substring(0, I);
            G = H.charAt(I + 1) == p;
            J = O[H];
            for(var i = 0,l = J.length;i < l;i++){

              L = J[i];
              K.push({
                self : L.context,
                handler : L.handler,
                type : N,
                capture : G
              });
            };
          };
        };
        return K;
      },
      toggleAttachedEvents : function(P, Q){

        var V = P.$$hash || qx.core.ObjectRegistry.toHashCode(P);
        var X = this.__ch[V];
        if(X){

          var S,W,R,T;
          for(var U in X){

            S = U.indexOf(b);
            W = U.substring(0, S);
            R = U.charCodeAt(S + 1) === 99;
            T = X[U];
            if(Q){

              this.__cn(P, W, R);
            } else {

              this.__co(P, W, R);
            };
          };
        };
      },
      hasListener : function(Y, ba, bb){

        {
        };
        var bc = Y.$$hash || qx.core.ObjectRegistry.toHashCode(Y);
        var be = this.__ch[bc];
        if(!be){

          return false;
        };
        var bf = ba + (bb ? d : a);
        var bd = be[bf];
        return !!(bd && bd.length > 0);
      },
      importListeners : function(bg, bh){

        {
        };
        var bn = bg.$$hash || qx.core.ObjectRegistry.toHashCode(bg);
        var bo = this.__ch[bn] = {
        };
        var bk = qx.event.Manager;
        for(var bi in bh){

          var bl = bh[bi];
          var bm = bl.type + (bl.capture ? d : a);
          var bj = bo[bm];
          if(!bj){

            bj = bo[bm] = [];
            this.__cn(bg, bl.type, bl.capture);
          };
          bj.push({
            handler : bl.listener,
            context : bl.self,
            unique : bl.unique || (bk.__cl++) + f
          });
        };
      },
      addListener : function(bp, bq, br, self, bs){

        var bw;
        {
        };
        var bx = bp.$$hash || qx.core.ObjectRegistry.toHashCode(bp);
        var bz = this.__ch[bx];
        if(!bz){

          bz = this.__ch[bx] = {
          };
        };
        var bv = bq + (bs ? d : a);
        var bu = bz[bv];
        if(!bu){

          bu = bz[bv] = [];
        };
        if(bu.length === 0){

          this.__cn(bp, bq, bs);
        };
        var by = (qx.event.Manager.__cl++) + f;
        var bt = {
          handler : br,
          context : self,
          unique : by
        };
        bu.push(bt);
        return bv + b + by;
      },
      findHandler : function(bA, bB){

        var bN = false,bF = false,bO = false,bC = false;
        var bL;
        if(bA.nodeType === 1){

          bN = true;
          bL = o + bA.tagName.toLowerCase() + e + bB;
        } else if(bA.nodeType === 9){

          bC = true;
          bL = g + bB;
        } else if(bA == this.__ce){

          bF = true;
          bL = m + bB;
        } else if(bA.classname){

          bO = true;
          bL = k + bA.classname + e + bB;
        } else {

          bL = r + bA + e + bB;
        };;;
        var bH = this.__ck;
        if(bH[bL]){

          return bH[bL];
        };
        var bK = this.__cg.getHandlers();
        var bG = qx.event.IEventHandler;
        var bI,bJ,bE,bD;
        for(var i = 0,l = bK.length;i < l;i++){

          bI = bK[i];
          bE = bI.SUPPORTED_TYPES;
          if(bE && !bE[bB]){

            continue;
          };
          bD = bI.TARGET_CHECK;
          if(bD){

            var bM = false;
            if(bN && ((bD & bG.TARGET_DOMNODE) != 0)){

              bM = true;
            } else if(bF && ((bD & bG.TARGET_WINDOW) != 0)){

              bM = true;
            } else if(bO && ((bD & bG.TARGET_OBJECT) != 0)){

              bM = true;
            } else if(bC && ((bD & bG.TARGET_DOCUMENT) != 0)){

              bM = true;
            };;;
            if(!bM){

              continue;
            };
          };
          bJ = this.getHandler(bK[i]);
          if(bI.IGNORE_CAN_HANDLE || bJ.canHandleEvent(bA, bB)){

            bH[bL] = bJ;
            return bJ;
          };
        };
        return null;
      },
      __cn : function(bP, bQ, bR){

        var bS = this.findHandler(bP, bQ);
        if(bS){

          bS.registerEvent(bP, bQ, bR);
          return;
        };
        {
        };
      },
      removeListener : function(bT, bU, bV, self, bW){

        var cb;
        {
        };
        var cc = bT.$$hash || qx.core.ObjectRegistry.toHashCode(bT);
        var cd = this.__ch[cc];
        if(!cd){

          return false;
        };
        var bX = bU + (bW ? d : a);
        var bY = cd[bX];
        if(!bY){

          return false;
        };
        var ca;
        for(var i = 0,l = bY.length;i < l;i++){

          ca = bY[i];
          if(ca.handler === bV && ca.context === self){

            qx.lang.Array.removeAt(bY, i);
            if(bY.length == 0){

              this.__co(bT, bU, bW);
            };
            return true;
          };
        };
        return false;
      },
      removeListenerById : function(ce, cf){

        var cl;
        {
        };
        var cj = cf.split(b);
        var co = cj[0];
        var cg = cj[1].charCodeAt(0) == 99;
        var cn = cj[2];
        var cm = ce.$$hash || qx.core.ObjectRegistry.toHashCode(ce);
        var cp = this.__ch[cm];
        if(!cp){

          return false;
        };
        var ck = co + (cg ? d : a);
        var ci = cp[ck];
        if(!ci){

          return false;
        };
        var ch;
        for(var i = 0,l = ci.length;i < l;i++){

          ch = ci[i];
          if(ch.unique === cn){

            qx.lang.Array.removeAt(ci, i);
            if(ci.length == 0){

              this.__co(ce, co, cg);
            };
            return true;
          };
        };
        return false;
      },
      removeAllListeners : function(cq){

        var cu = cq.$$hash || qx.core.ObjectRegistry.toHashCode(cq);
        var cw = this.__ch[cu];
        if(!cw){

          return false;
        };
        var cs,cv,cr;
        for(var ct in cw){

          if(cw[ct].length > 0){

            cs = ct.split(b);
            cv = cs[0];
            cr = cs[1] === h;
            this.__co(cq, cv, cr);
          };
        };
        delete this.__ch[cu];
        return true;
      },
      deleteAllListeners : function(cx){

        delete this.__ch[cx];
      },
      __co : function(cy, cz, cA){

        var cB = this.findHandler(cy, cz);
        if(cB){

          cB.unregisterEvent(cy, cz, cA);
          return;
        };
        {
        };
      },
      dispatchEvent : function(cC, event){

        var cH;
        {
        };
        var cI = event.getType();
        if(!event.getBubbles() && !this.hasListener(cC, cI)){

          qx.event.Pool.getInstance().poolObject(event);
          return true;
        };
        if(!event.getTarget()){

          event.setTarget(cC);
        };
        var cG = this.__cg.getDispatchers();
        var cF;
        var cE = false;
        for(var i = 0,l = cG.length;i < l;i++){

          cF = this.getDispatcher(cG[i]);
          if(cF.canDispatchEvent(cC, event, cI)){

            cF.dispatchEvent(cC, event, cI);
            cE = true;
            break;
          };
        };
        if(!cE){

          {
          };
          return true;
        };
        var cD = event.getDefaultPrevented();
        qx.event.Pool.getInstance().poolObject(event);
        return !cD;
      },
      dispose : function(){

        this.__cg.removeManager(this);
        qx.util.DisposeUtil.disposeMap(this, n);
        qx.util.DisposeUtil.disposeMap(this, q);
        this.__ch = this.__ce = this.__cm = null;
        this.__cg = this.__ck = null;
      }
    }
  });
})();
(function(){

  var b = "qx.event.GlobalError",a = "qx.globalErrorHandling";
  qx.Bootstrap.define(b, {
    statics : {
      __cp : function(){

        if(qx.core && qx.core.Environment){

          return qx.core.Environment.get(a);
        } else {

          return !!qx.Bootstrap.getEnvironmentSetting(a);
        };
      },
      setErrorHandler : function(c, d){

        this.__cq = c || null;
        this.__cr = d || window;
        if(this.__cp()){

          if(c && window.onerror){

            var e = qx.Bootstrap.bind(this.__ct, this);
            if(this.__cs == null){

              this.__cs = window.onerror;
            };
            var self = this;
            window.onerror = function(f, g, h){

              self.__cs(f, g, h);
              e(f, g, h);
            };
          };
          if(c && !window.onerror){

            window.onerror = qx.Bootstrap.bind(this.__ct, this);
          };
          if(this.__cq == null){

            if(this.__cs != null){

              window.onerror = this.__cs;
              this.__cs = null;
            } else {

              window.onerror = null;
            };
          };
        };
      },
      __ct : function(i, j, k){

        if(this.__cq){

          this.handleError(new qx.core.WindowError(i, j, k));
          return true;
        };
      },
      observeMethod : function(l){

        if(this.__cp()){

          var self = this;
          return function(){

            if(!self.__cq){

              return l.apply(this, arguments);
            };
            try{

              return l.apply(this, arguments);
            } catch(m) {

              self.handleError(new qx.core.GlobalError(m, arguments));
            };
          };
        } else {

          return l;
        };
      },
      handleError : function(n){

        if(this.__cq){

          this.__cq.call(this.__cr, n);
        };
      }
    },
    defer : function(o){

      if(qx.core && qx.core.Environment){

        qx.core.Environment.add(a, true);
      } else {

        qx.Bootstrap.setEnvironmentSetting(a, true);
      };
      o.setErrorHandler(null, null);
    }
  });
})();
(function(){

  var b = "",a = "qx.core.WindowError";
  qx.Bootstrap.define(a, {
    extend : Error,
    construct : function(c, d, e){

      var f = Error.call(this, c);
      if(f.stack){

        this.stack = f.stack;
      };
      if(f.stacktrace){

        this.stacktrace = f.stacktrace;
      };
      this.__cu = c;
      this.__cv = d || b;
      this.__cw = e === undefined ? -1 : e;
    },
    members : {
      __cu : null,
      __cv : null,
      __cw : null,
      toString : function(){

        return this.__cu;
      },
      getUri : function(){

        return this.__cv;
      },
      getLineNumber : function(){

        return this.__cw;
      }
    }
  });
})();
(function(){

  var b = "GlobalError: ",a = "qx.core.GlobalError";
  qx.Bootstrap.define(a, {
    extend : Error,
    construct : function(c, d){

      if(qx.Bootstrap.DEBUG){

        qx.core.Assert.assertNotUndefined(c);
      };
      this.__cu = b + (c && c.message ? c.message : c);
      var e = Error.call(this, this.__cu);
      if(e.stack){

        this.stack = e.stack;
      };
      if(e.stacktrace){

        this.stacktrace = e.stacktrace;
      };
      this.__cx = d;
      this.__cy = c;
    },
    members : {
      __cy : null,
      __cx : null,
      __cu : null,
      toString : function(){

        return this.__cu;
      },
      getArguments : function(){

        return this.__cx;
      },
      getSourceException : function(){

        return this.__cy;
      }
    },
    destruct : function(){

      this.__cy = null;
      this.__cx = null;
      this.__cu = null;
    }
  });
})();
(function(){

  var p = " != ",o = "qx.core.Object",n = "Expected value to be an array but found ",m = ") was fired.",k = "Expected value to be an integer >= 0 but found ",j = "' to be not equal with '",h = "' to '",g = "Expected object '",f = "Called assertTrue with '",d = "Expected value to be a map but found ",bC = "The function did not raise an exception!",bB = "Expected value to be undefined but found ",bA = "Expected value to be a DOM element but found  '",bz = "Expected value to be a regular expression but found ",by = "' to implement the interface '",bx = "Expected value to be null but found ",bw = "Invalid argument 'type'",bv = "Called assert with 'false'",bu = "Assertion error! ",bt = "null",w = "' but found '",x = "'undefined'",u = "' must must be a key of the map '",v = "The String '",s = "Expected value to be a string but found ",t = "Expected value not to be undefined but found undefined!",q = "qx.util.ColorUtil",r = ": ",E = "The raised exception does not have the expected type! ",F = ") not fired.",U = "qx.core.Assert",Q = "Expected value to be typeof object but found ",bd = "' (identical) but found '",X = "' must have any of the values defined in the array '",bp = "Expected value to be a number but found ",bj = "Called assertFalse with '",L = "qx.ui.core.Widget",bs = "Expected value to be a qooxdoo object but found ",br = "' arguments.",bq = "Expected value '%1' to be in the range '%2'..'%3'!",J = "Array[",N = "' does not match the regular expression '",P = "' to be not identical with '",S = "Expected [",V = "' arguments but found '",Y = "', which cannot be converted to a CSS color!",bf = "qx.core.AssertionError",bl = "Expected value to be a boolean but found ",y = "Expected value not to be null but found null!",z = "))!",M = "Expected value to be a qooxdoo widget but found ",bc = "Expected value to be typeof '",bb = "\n Stack trace: \n",ba = "Expected value to be typeof function but found ",bh = "Expected value to be an integer but found ",bg = "Called fail().",W = "The parameter 're' must be a string or a regular expression.",be = "qx.util.ColorUtil not available! Your code must have a dependency on 'qx.util.ColorUtil'",a = "Expected value to be a number >= 0 but found ",bk = "Expected value to be instanceof '",A = "], but found [",B = "Wrong number of arguments given. Expected '",R = "object",b = "Event (",c = "Expected value to be the CSS color '",I = "' but found ",C = "]",D = ", ",H = "The value '",T = ")), but found value '",bn = "' (rgb(",bm = ",",O = "'",bo = "Expected '",K = "'!",bi = "!",G = "";
  qx.Class.define(U, {
    statics : {
      __cz : true,
      __cA : function(bD, bE){

        var bI = G;
        for(var i = 1,l = arguments.length;i < l;i++){

          bI = bI + this.__cB(arguments[i] === undefined ? x : arguments[i]);
        };
        var bH = G;
        if(bI){

          bH = bD + r + bI;
        } else {

          bH = bD;
        };
        var bG = bu + bH;
        if(qx.Class.isDefined(bf)){

          var bF = new qx.core.AssertionError(bD, bI);
          if(this.__cz){

            qx.Bootstrap.error(bG + bb + bF.getStackTrace());
          };
          throw bF;
        } else {

          if(this.__cz){

            qx.Bootstrap.error(bG);
          };
          throw new Error(bG);
        };
      },
      __cB : function(bJ){

        var bK;
        if(bJ === null){

          bK = bt;
        } else if(qx.lang.Type.isArray(bJ) && bJ.length > 10){

          bK = J + bJ.length + C;
        } else if((bJ instanceof Object) && (bJ.toString == null)){

          bK = qx.lang.Json.stringify(bJ, null, 2);
        } else {

          try{

            bK = bJ.toString();
          } catch(e) {

            bK = G;
          };
        };;
        return bK;
      },
      assert : function(bL, bM){

        bL == true || this.__cA(bM || G, bv);
      },
      fail : function(bN, bO){

        var bP = bO ? G : bg;
        this.__cA(bN || G, bP);
      },
      assertTrue : function(bQ, bR){

        (bQ === true) || this.__cA(bR || G, f, bQ, O);
      },
      assertFalse : function(bS, bT){

        (bS === false) || this.__cA(bT || G, bj, bS, O);
      },
      assertEquals : function(bU, bV, bW){

        bU == bV || this.__cA(bW || G, bo, bU, w, bV, K);
      },
      assertNotEquals : function(bX, bY, ca){

        bX != bY || this.__cA(ca || G, bo, bX, j, bY, K);
      },
      assertIdentical : function(cb, cc, cd){

        cb === cc || this.__cA(cd || G, bo, cb, bd, cc, K);
      },
      assertNotIdentical : function(ce, cf, cg){

        ce !== cf || this.__cA(cg || G, bo, ce, P, cf, K);
      },
      assertNotUndefined : function(ch, ci){

        ch !== undefined || this.__cA(ci || G, t);
      },
      assertUndefined : function(cj, ck){

        cj === undefined || this.__cA(ck || G, bB, cj, bi);
      },
      assertNotNull : function(cl, cm){

        cl !== null || this.__cA(cm || G, y);
      },
      assertNull : function(cn, co){

        cn === null || this.__cA(co || G, bx, cn, bi);
      },
      assertJsonEquals : function(cp, cq, cr){

        this.assertEquals(qx.lang.Json.stringify(cp), qx.lang.Json.stringify(cq), cr);
      },
      assertMatch : function(cs, ct, cu){

        this.assertString(cs);
        this.assert(qx.lang.Type.isRegExp(ct) || qx.lang.Type.isString(ct), W);
        cs.search(ct) >= 0 || this.__cA(cu || G, v, cs, N, ct.toString(), K);
      },
      assertArgumentsCount : function(cv, cw, cx, cy){

        var cz = cv.length;
        (cz >= cw && cz <= cx) || this.__cA(cy || G, B, cw, h, cx, V, arguments.length, br);
      },
      assertEventFired : function(cA, event, cB, cC, cD){

        var cF = false;
        var cE = function(e){

          if(cC){

            cC.call(cA, e);
          };
          cF = true;
        };
        var cG;
        try{

          cG = cA.addListener(event, cE, cA);
          cB.call(cA);
        } catch(cH) {

          throw cH;
        }finally{

          try{

            cA.removeListenerById(cG);
          } catch(cI) {
          };
        };
        cF === true || this.__cA(cD || G, b, event, F);
      },
      assertEventNotFired : function(cJ, event, cK, cL){

        var cN = false;
        var cM = function(e){

          cN = true;
        };
        var cO = cJ.addListener(event, cM, cJ);
        cK.call();
        cN === false || this.__cA(cL || G, b, event, m);
        cJ.removeListenerById(cO);
      },
      assertException : function(cP, cQ, cR, cS){

        var cQ = cQ || Error;
        var cT;
        try{

          this.__cz = false;
          cP();
        } catch(cU) {

          cT = cU;
        }finally{

          this.__cz = true;
        };
        if(cT == null){

          this.__cA(cS || G, bC);
        };
        cT instanceof cQ || this.__cA(cS || G, E, cQ, p, cT);
        if(cR){

          this.assertMatch(cT.toString(), cR, cS);
        };
      },
      assertInArray : function(cV, cW, cX){

        cW.indexOf(cV) !== -1 || this.__cA(cX || G, H, cV, X, cW, O);
      },
      assertArrayEquals : function(cY, da, db){

        this.assertArray(cY, db);
        this.assertArray(da, db);
        db = db || S + cY.join(D) + A + da.join(D) + C;
        if(cY.length !== da.length){

          this.fail(db, true);
        };
        for(var i = 0;i < cY.length;i++){

          if(cY[i] !== da[i]){

            this.fail(db, true);
          };
        };
      },
      assertKeyInMap : function(dc, dd, de){

        dd[dc] !== undefined || this.__cA(de || G, H, dc, u, dd, O);
      },
      assertFunction : function(df, dg){

        qx.lang.Type.isFunction(df) || this.__cA(dg || G, ba, df, bi);
      },
      assertString : function(dh, di){

        qx.lang.Type.isString(dh) || this.__cA(di || G, s, dh, bi);
      },
      assertBoolean : function(dj, dk){

        qx.lang.Type.isBoolean(dj) || this.__cA(dk || G, bl, dj, bi);
      },
      assertNumber : function(dl, dm){

        (qx.lang.Type.isNumber(dl) && isFinite(dl)) || this.__cA(dm || G, bp, dl, bi);
      },
      assertPositiveNumber : function(dn, dp){

        (qx.lang.Type.isNumber(dn) && isFinite(dn) && dn >= 0) || this.__cA(dp || G, a, dn, bi);
      },
      assertInteger : function(dq, dr){

        (qx.lang.Type.isNumber(dq) && isFinite(dq) && dq % 1 === 0) || this.__cA(dr || G, bh, dq, bi);
      },
      assertPositiveInteger : function(ds, dt){

        var du = (qx.lang.Type.isNumber(ds) && isFinite(ds) && ds % 1 === 0 && ds >= 0);
        du || this.__cA(dt || G, k, ds, bi);
      },
      assertInRange : function(dv, dw, dx, dy){

        (dv >= dw && dv <= dx) || this.__cA(dy || G, qx.lang.String.format(bq, [dv, dw, dx]));
      },
      assertObject : function(dz, dA){

        var dB = dz !== null && (qx.lang.Type.isObject(dz) || typeof dz === R);
        dB || this.__cA(dA || G, Q, (dz), bi);
      },
      assertArray : function(dC, dD){

        qx.lang.Type.isArray(dC) || this.__cA(dD || G, n, dC, bi);
      },
      assertMap : function(dE, dF){

        qx.lang.Type.isObject(dE) || this.__cA(dF || G, d, dE, bi);
      },
      assertRegExp : function(dG, dH){

        qx.lang.Type.isRegExp(dG) || this.__cA(dH || G, bz, dG, bi);
      },
      assertType : function(dI, dJ, dK){

        this.assertString(dJ, bw);
        typeof (dI) === dJ || this.__cA(dK || G, bc, dJ, I, dI, bi);
      },
      assertInstance : function(dL, dM, dN){

        var dO = dM.classname || dM + G;
        dL instanceof dM || this.__cA(dN || G, bk, dO, I, dL, bi);
      },
      assertInterface : function(dP, dQ, dR){

        qx.Class.implementsInterface(dP, dQ) || this.__cA(dR || G, g, dP, by, dQ, K);
      },
      assertCssColor : function(dS, dT, dU){

        var dV = qx.Class.getByName(q);
        if(!dV){

          throw new Error(be);
        };
        var dX = dV.stringToRgb(dS);
        try{

          var dW = dV.stringToRgb(dT);
        } catch(ea) {

          this.__cA(dU || G, c, dS, bn, dX.join(bm), T, dT, Y);
        };
        var dY = dX[0] == dW[0] && dX[1] == dW[1] && dX[2] == dW[2];
        dY || this.__cA(dU || G, c, dX, bn, dX.join(bm), T, dT, bn, dW.join(bm), z);
      },
      assertElement : function(eb, ec){

        !!(eb && eb.nodeType === 1) || this.__cA(ec || G, bA, eb, K);
      },
      assertQxObject : function(ed, ee){

        this.__cC(ed, o) || this.__cA(ee || G, bs, ed, bi);
      },
      assertQxWidget : function(ef, eg){

        this.__cC(ef, L) || this.__cA(eg || G, M, ef, bi);
      },
      __cC : function(eh, ei){

        if(!eh){

          return false;
        };
        var ej = eh.constructor;
        while(ej){

          if(ej.classname === ei){

            return true;
          };
          ej = ej.superclass;
        };
        return false;
      }
    }
  });
})();
(function(){

  var p = 'String',o = 'Boolean',m = '\\\\',l = '\\f',h = '\\t',g = '{\n',f = '[]',e = "qx.lang.JsonImpl",d = 'Z',b = '\\n',ba = 'Object',Y = '{}',X = '@',W = '.',V = '(',U = 'Array',T = 'T',S = '\\r',R = '{',Q = 'JSON.parse',x = ' ',y = '[',u = 'Number',w = ')',s = '[\n',t = '\\"',q = '\\b',r = ': ',z = 'object',A = 'function',H = ',',F = '\n',K = '\\u',J = ',\n',M = '0000',L = 'string',C = "Cannot stringify a recursive object.",P = '0',O = '-',N = '}',B = ']',D = 'null',E = '"',G = ':',I = '';
  qx.Bootstrap.define(e, {
    extend : Object,
    construct : function(){

      this.stringify = qx.lang.Function.bind(this.stringify, this);
      this.parse = qx.lang.Function.bind(this.parse, this);
    },
    members : {
      __cD : null,
      __cE : null,
      __cF : null,
      __cG : null,
      stringify : function(bb, bc, bd){

        this.__cD = I;
        this.__cE = I;
        this.__cG = [];
        if(qx.lang.Type.isNumber(bd)){

          var bd = Math.min(10, Math.floor(bd));
          for(var i = 0;i < bd;i += 1){

            this.__cE += x;
          };
        } else if(qx.lang.Type.isString(bd)){

          if(bd.length > 10){

            bd = bd.slice(0, 10);
          };
          this.__cE = bd;
        };
        if(bc && (qx.lang.Type.isFunction(bc) || qx.lang.Type.isArray(bc))){

          this.__cF = bc;
        } else {

          this.__cF = null;
        };
        return this.__cH(I, {
          '' : bb
        });
      },
      __cH : function(be, bf){

        var bi = this.__cD,bg,bj = bf[be];
        if(bj && qx.lang.Type.isFunction(bj.toJSON)){

          bj = bj.toJSON(be);
        } else if(qx.lang.Type.isDate(bj)){

          bj = this.dateToJSON(bj);
        };
        if(typeof this.__cF === A){

          bj = this.__cF.call(bf, be, bj);
        };
        if(bj === null){

          return D;
        };
        if(bj === undefined){

          return undefined;
        };
        switch(qx.lang.Type.getClass(bj)){case p:
        return this.__cI(bj);case u:
        return isFinite(bj) ? String(bj) : D;case o:
        return String(bj);case U:
        this.__cD += this.__cE;
        bg = [];
        if(this.__cG.indexOf(bj) !== -1){

          throw new TypeError(C);
        };
        this.__cG.push(bj);
        var length = bj.length;
        for(var i = 0;i < length;i += 1){

          bg[i] = this.__cH(i, bj) || D;
        };
        this.__cG.pop();
        if(bg.length === 0){

          var bh = f;
        } else if(this.__cD){

          bh = s + this.__cD + bg.join(J + this.__cD) + F + bi + B;
        } else {

          bh = y + bg.join(H) + B;
        };
        this.__cD = bi;
        return bh;case ba:
        this.__cD += this.__cE;
        bg = [];
        if(this.__cG.indexOf(bj) !== -1){

          throw new TypeError(C);
        };
        this.__cG.push(bj);
        if(this.__cF && typeof this.__cF === z){

          var length = this.__cF.length;
          for(var i = 0;i < length;i += 1){

            var k = this.__cF[i];
            if(typeof k === L){

              var v = this.__cH(k, bj);
              if(v){

                bg.push(this.__cI(k) + (this.__cD ? r : G) + v);
              };
            };
          };
        } else {

          for(var k in bj){

            if(Object.hasOwnProperty.call(bj, k)){

              var v = this.__cH(k, bj);
              if(v){

                bg.push(this.__cI(k) + (this.__cD ? r : G) + v);
              };
            };
          };
        };
        this.__cG.pop();
        if(bg.length === 0){

          var bh = Y;
        } else if(this.__cD){

          bh = g + this.__cD + bg.join(J + this.__cD) + F + bi + N;
        } else {

          bh = R + bg.join(H) + N;
        };
        this.__cD = bi;
        return bh;};
      },
      dateToJSON : function(bk){

        var bl = function(n){

          return n < 10 ? P + n : n;
        };
        var bm = function(n){

          var bn = bl(n);
          return n < 100 ? P + bn : bn;
        };
        return isFinite(bk.valueOf()) ? bk.getUTCFullYear() + O + bl(bk.getUTCMonth() + 1) + O + bl(bk.getUTCDate()) + T + bl(bk.getUTCHours()) + G + bl(bk.getUTCMinutes()) + G + bl(bk.getUTCSeconds()) + W + bm(bk.getUTCMilliseconds()) + d : null;
      },
      __cI : function(bo){

        var bp = {
          '\b' : q,
          '\t' : h,
          '\n' : b,
          '\f' : l,
          '\r' : S,
          '"' : t,
          '\\' : m
        };
        var bq = /[\\\"\x00-\x1f\x7f-\x9f\u00ad\u0600-\u0604\u070f\u17b4\u17b5\u200c-\u200f\u2028-\u202f\u2060-\u206f\ufeff\ufff0-\uffff]/g;
        bq.lastIndex = 0;
        if(bq.test(bo)){

          return E + bo.replace(bq, function(a){

            var c = bp[a];
            return typeof c === L ? c : K + (M + a.charCodeAt(0).toString(16)).slice(-4);
          }) + E;
        } else {

          return E + bo + E;
        };
      },
      parse : function(br, bs){

        var bt = /[\u0000\u00ad\u0600-\u0604\u070f\u17b4\u17b5\u200c-\u200f\u2028-\u202f\u2060-\u206f\ufeff\ufff0-\uffff]/g;
        bt.lastIndex = 0;
        if(bt.test(br)){

          br = br.replace(bt, function(a){

            return K + (M + a.charCodeAt(0).toString(16)).slice(-4);
          });
        };
        if(/^[\],:{}\s]*$/.test(br.replace(/\\(?:["\\\/bfnrt]|u[0-9a-fA-F]{4})/g, X).replace(/"[^"\\\n\r]*"|true|false|null|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?/g, B).replace(/(?:^|:|,)(?:\s*\[)+/g, I))){

          var j = eval(V + br + w);
          return typeof bs === A ? this.__cJ({
            '' : j
          }, I, bs) : j;
        };
        throw new SyntaxError(Q);
      },
      __cJ : function(bu, bv, bw){

        var bx = bu[bv];
        if(bx && typeof bx === z){

          for(var k in bx){

            if(Object.hasOwnProperty.call(bx, k)){

              var v = this.__cJ(bx, k, bw);
              if(v !== undefined){

                bx[k] = v;
              } else {

                delete bx[k];
              };
            };
          };
        };
        return bw.call(bu, bv, bx);
      }
    }
  });
})();
(function(){

  var g = "prop",f = "qx.bom.client.Json",e = "JSON",d = '{"x":1}',c = "json",b = "val",a = "repl";
  qx.Bootstrap.define(f, {
    statics : {
      getJson : function(){

        return (qx.Bootstrap.getClass(window.JSON) == e && JSON.parse(d).x === 1 && JSON.stringify({
          "prop" : b
        }, function(k, v){

          return k === g ? a : v;
        }).indexOf(a) > 0);
      }
    },
    defer : function(h){

      qx.core.Environment.add(c, h.getJson);
    }
  });
})();
(function(){

  var a = "qx.lang.Json";
  qx.Bootstrap.define(a, {
    statics : {
      JSON : qx.core.Environment.get("json") ? window.JSON : new qx.lang.JsonImpl(),
      stringify : null,
      parse : null
    },
    defer : function(b){

      b.stringify = b.JSON.stringify;
      b.parse = b.JSON.parse;
    }
  });
})();
(function(){

  var a = "qx.event.IEventHandler";
  qx.Interface.define(a, {
    statics : {
      TARGET_DOMNODE : 1,
      TARGET_WINDOW : 2,
      TARGET_OBJECT : 4,
      TARGET_DOCUMENT : 8
    },
    members : {
      canHandleEvent : function(b, c){
      },
      registerEvent : function(d, e, f){
      },
      unregisterEvent : function(g, h, i){
      }
    }
  });
})();
(function(){

  var c = "qx.event.Registration";
  qx.Class.define(c, {
    statics : {
      __cK : {
      },
      getManager : function(d){

        if(d == null){

          {
          };
          d = window;
        } else if(d.nodeType){

          d = qx.dom.Node.getWindow(d);
        } else if(!qx.dom.Node.isWindow(d)){

          d = window;
        };;
        var f = d.$$hash || qx.core.ObjectRegistry.toHashCode(d);
        var e = this.__cK[f];
        if(!e){

          e = new qx.event.Manager(d, this);
          this.__cK[f] = e;
        };
        return e;
      },
      removeManager : function(g){

        var h = g.getWindowId();
        delete this.__cK[h];
      },
      addListener : function(i, j, k, self, l){

        return this.getManager(i).addListener(i, j, k, self, l);
      },
      removeListener : function(m, n, o, self, p){

        return this.getManager(m).removeListener(m, n, o, self, p);
      },
      removeListenerById : function(q, r){

        return this.getManager(q).removeListenerById(q, r);
      },
      removeAllListeners : function(s){

        return this.getManager(s).removeAllListeners(s);
      },
      deleteAllListeners : function(t){

        var u = t.$$hash;
        if(u){

          this.getManager(t).deleteAllListeners(u);
        };
      },
      hasListener : function(v, w, x){

        return this.getManager(v).hasListener(v, w, x);
      },
      serializeListeners : function(y){

        return this.getManager(y).serializeListeners(y);
      },
      createEvent : function(z, A, B){

        {
        };
        if(A == null){

          A = qx.event.type.Event;
        };
        var C = qx.event.Pool.getInstance().getObject(A);
        B ? C.init.apply(C, B) : C.init();
        if(z){

          C.setType(z);
        };
        return C;
      },
      dispatchEvent : function(D, event){

        return this.getManager(D).dispatchEvent(D, event);
      },
      fireEvent : function(E, F, G, H){

        var I;
        {
        };
        var J = this.createEvent(F, G || null, H);
        return this.getManager(E).dispatchEvent(E, J);
      },
      fireNonBubblingEvent : function(K, L, M, N){

        {
        };
        var O = this.getManager(K);
        if(!O.hasListener(K, L, false)){

          return true;
        };
        var P = this.createEvent(L, M || null, N);
        return O.dispatchEvent(K, P);
      },
      PRIORITY_FIRST : -32000,
      PRIORITY_NORMAL : 0,
      PRIORITY_LAST : 32000,
      __ci : [],
      addHandler : function(Q){

        {
        };
        this.__ci.push(Q);
        this.__ci.sort(function(a, b){

          return a.PRIORITY - b.PRIORITY;
        });
      },
      getHandlers : function(){

        return this.__ci;
      },
      __cj : [],
      addDispatcher : function(R, S){

        {
        };
        this.__cj.push(R);
        this.__cj.sort(function(a, b){

          return a.PRIORITY - b.PRIORITY;
        });
      },
      getDispatchers : function(){

        return this.__cj;
      }
    }
  });
})();
(function(){

  var a = "qx.core.MEvents";
  qx.Mixin.define(a, {
    members : {
      __cL : qx.event.Registration,
      addListener : function(b, c, self, d){

        if(!this.$$disposed){

          return this.__cL.addListener(this, b, c, self, d);
        };
        return null;
      },
      addListenerOnce : function(f, g, self, h){

        var i = function(e){

          this.removeListener(f, i, this, h);
          g.call(self || this, e);
        };
        return this.addListener(f, i, this, h);
      },
      removeListener : function(j, k, self, l){

        if(!this.$$disposed){

          return this.__cL.removeListener(this, j, k, self, l);
        };
        return false;
      },
      removeListenerById : function(m){

        if(!this.$$disposed){

          return this.__cL.removeListenerById(this, m);
        };
        return false;
      },
      hasListener : function(n, o){

        return this.__cL.hasListener(this, n, o);
      },
      dispatchEvent : function(p){

        if(!this.$$disposed){

          return this.__cL.dispatchEvent(this, p);
        };
        return true;
      },
      fireEvent : function(q, r, s){

        if(!this.$$disposed){

          return this.__cL.fireEvent(this, q, r, s);
        };
        return true;
      },
      fireNonBubblingEvent : function(t, u, v){

        if(!this.$$disposed){

          return this.__cL.fireNonBubblingEvent(this, t, u, v);
        };
        return true;
      },
      fireDataEvent : function(w, x, y, z){

        if(!this.$$disposed){

          if(y === undefined){

            y = null;
          };
          return this.__cL.fireNonBubblingEvent(this, w, qx.event.type.Data, [x, y, !!z]);
        };
        return true;
      }
    }
  });
})();
(function(){

  var a = "qx.event.IEventDispatcher";
  qx.Interface.define(a, {
    members : {
      canDispatchEvent : function(b, event, c){

        this.assertInstance(event, qx.event.type.Event);
        this.assertString(c);
      },
      dispatchEvent : function(d, event, e){

        this.assertInstance(event, qx.event.type.Event);
        this.assertString(e);
      }
    }
  });
})();
(function(){

  var k = "module.events",j = "Cloning only possible with properties.",h = "qx.core.Object",g = "[",f = "$$user_",e = "]",d = "rv:1.8.1",c = "MSIE 6.0",b = "Object",a = "module.property";
  qx.Class.define(h, {
    extend : Object,
    include : qx.core.Environment.filter({
      "module.databinding" : qx.data.MBinding,
      "module.logger" : qx.core.MLogging,
      "module.events" : qx.core.MEvents,
      "module.property" : qx.core.MProperty
    }),
    construct : function(){

      qx.core.ObjectRegistry.register(this);
    },
    statics : {
      $$type : b
    },
    members : {
      __M : qx.core.Environment.get("module.property") ? qx.core.Property : null,
      toHashCode : function(){

        return this.$$hash;
      },
      toString : function(){

        return this.classname + g + this.$$hash + e;
      },
      base : function(m, n){

        {
        };
        if(arguments.length === 1){

          return m.callee.base.call(this);
        } else {

          return m.callee.base.apply(this, Array.prototype.slice.call(arguments, 1));
        };
      },
      self : function(o){

        return o.callee.self;
      },
      clone : function(){

        if(!qx.core.Environment.get(a)){

          throw new Error(j);
        };
        var q = this.constructor;
        var p = new q;
        var s = qx.Class.getProperties(q);
        var r = this.__M.$$store.user;
        var t = this.__M.$$method.set;
        var name;
        for(var i = 0,l = s.length;i < l;i++){

          name = s[i];
          if(this.hasOwnProperty(r[name])){

            p[t[name]](this[r[name]]);
          };
        };
        return p;
      },
      __cM : null,
      setUserData : function(u, v){

        if(!this.__cM){

          this.__cM = {
          };
        };
        this.__cM[u] = v;
      },
      getUserData : function(w){

        if(!this.__cM){

          return null;
        };
        var x = this.__cM[w];
        return x === undefined ? null : x;
      },
      isDisposed : function(){

        return this.$$disposed || false;
      },
      dispose : function(){

        var C,A,z,D;
        if(this.$$disposed){

          return;
        };
        this.$$disposed = true;
        this.$$instance = null;
        this.$$allowconstruct = null;
        {
        };
        var B = this.constructor;
        var y;
        while(B.superclass){

          if(B.$$destructor){

            B.$$destructor.call(this);
          };
          if(B.$$includes){

            y = B.$$flatIncludes;
            for(var i = 0,l = y.length;i < l;i++){

              if(y[i].$$destructor){

                y[i].$$destructor.call(this);
              };
            };
          };
          B = B.superclass;
        };
        if(this.__cN){

          this.__cN();
        };
        {
        };
      },
      __cN : null,
      __cO : function(){

        var E = qx.Class.getProperties(this.constructor);
        for(var i = 0,l = E.length;i < l;i++){

          delete this[f + E[i]];
        };
      },
      _disposeObjects : function(F){

        qx.util.DisposeUtil.disposeObjects(this, arguments);
      },
      _disposeSingletonObjects : function(G){

        qx.util.DisposeUtil.disposeObjects(this, arguments, true);
      },
      _disposeArray : function(H){

        qx.util.DisposeUtil.disposeArray(this, H);
      },
      _disposeMap : function(I){

        qx.util.DisposeUtil.disposeMap(this, I);
      }
    },
    environment : {
      "qx.debug.dispose.level" : 0
    },
    defer : function(J, K){

      var M = navigator.userAgent.indexOf(c) != -1;
      var L = navigator.userAgent.indexOf(d) != -1;
      if(M || L){

        K.__cN = K.__cO;
      };
    },
    destruct : function(){

      if(qx.core.Environment.get(k)){

        if(!qx.core.ObjectRegistry.inShutDown){

          qx.event.Registration.removeAllListeners(this);
        } else {

          qx.event.Registration.deleteAllListeners(this);
        };
      };
      qx.core.ObjectRegistry.unregister(this);
      this.__cM = null;
      if(qx.core.Environment.get(a)){

        var P = this.constructor;
        var T;
        var U = this.__M.$$store;
        var R = U.user;
        var S = U.theme;
        var N = U.inherit;
        var Q = U.useinit;
        var O = U.init;
        while(P){

          T = P.$$properties;
          if(T){

            for(var name in T){

              if(T[name].dereference){

                this[R[name]] = this[S[name]] = this[N[name]] = this[Q[name]] = this[O[name]] = undefined;
              };
            };
          };
          P = P.superclass;
        };
      };
    }
  });
})();
(function(){

  var k = " is a singleton! Please use disposeSingleton instead.",j = "undefined",h = "qx.util.DisposeUtil",g = "!",f = "The map field: ",e = "The array field: ",d = "The object stored in key ",c = "Has no disposable object under key: ",b = " of object: ",a = " has non disposable entries: ";
  qx.Class.define(h, {
    statics : {
      disposeObjects : function(m, n, o){

        var name;
        for(var i = 0,l = n.length;i < l;i++){

          name = n[i];
          if(m[name] == null || !m.hasOwnProperty(name)){

            continue;
          };
          if(!qx.core.ObjectRegistry.inShutDown){

            if(m[name].dispose){

              if(!o && m[name].constructor.$$instance){

                throw new Error(d + name + k);
              } else {

                m[name].dispose();
              };
            } else {

              throw new Error(c + name + g);
            };
          };
          m[name] = null;
        };
      },
      disposeArray : function(p, q){

        var s = p[q];
        if(!s){

          return;
        };
        if(qx.core.ObjectRegistry.inShutDown){

          p[q] = null;
          return;
        };
        try{

          var r;
          for(var i = s.length - 1;i >= 0;i--){

            r = s[i];
            if(r){

              r.dispose();
            };
          };
        } catch(t) {

          throw new Error(e + q + b + p + a + t);
        };
        s.length = 0;
        p[q] = null;
      },
      disposeMap : function(u, v){

        var x = u[v];
        if(!x){

          return;
        };
        if(qx.core.ObjectRegistry.inShutDown){

          u[v] = null;
          return;
        };
        try{

          var w;
          for(var y in x){

            w = x[y];
            if(x.hasOwnProperty(y) && w){

              w.dispose();
            };
          };
        } catch(z) {

          throw new Error(f + v + b + u + a + z);
        };
        u[v] = null;
      },
      disposeTriggeredBy : function(A, B){

        var C = B.dispose;
        B.dispose = function(){

          C.call(B);
          A.dispose();
        };
      },
      destroyContainer : function(D){

        {
        };
        var E = [];
        this._collectContainerChildren(D, E);
        var F = E.length;
        for(var i = F - 1;i >= 0;i--){

          E[i].destroy();
        };
        D.destroy();
      },
      _collectContainerChildren : function(G, H){

        var J = G.getChildren();
        for(var i = 0;i < J.length;i++){

          var I = J[i];
          H.push(I);
          if(this.__cP(I)){

            this._collectContainerChildren(I, H);
          };
        };
      },
      __cP : function(K){

        var L = [qx.ui.container.Composite, qx.ui.container.Scroll, qx.ui.container.SlideBar, qx.ui.container.Stack];
        for(var i = 0,l = L.length;i < l;i++){

          if(typeof L[i] !== j && qx.Class.isSubClassOf(K.constructor, L[i])){

            return true;
          };
        };
        return false;
      }
    }
  });
})();
(function(){

  var a = "qx.event.type.Event";
  qx.Class.define(a, {
    extend : qx.core.Object,
    statics : {
      CAPTURING_PHASE : 1,
      AT_TARGET : 2,
      BUBBLING_PHASE : 3
    },
    members : {
      init : function(b, c){

        {
        };
        this._type = null;
        this._target = null;
        this._currentTarget = null;
        this._relatedTarget = null;
        this._originalTarget = null;
        this._stopPropagation = false;
        this._preventDefault = false;
        this._bubbles = !!b;
        this._cancelable = !!c;
        this._timeStamp = (new Date()).getTime();
        this._eventPhase = null;
        return this;
      },
      clone : function(d){

        if(d){

          var e = d;
        } else {

          var e = qx.event.Pool.getInstance().getObject(this.constructor);
        };
        e._type = this._type;
        e._target = this._target;
        e._currentTarget = this._currentTarget;
        e._relatedTarget = this._relatedTarget;
        e._originalTarget = this._originalTarget;
        e._stopPropagation = this._stopPropagation;
        e._bubbles = this._bubbles;
        e._preventDefault = this._preventDefault;
        e._cancelable = this._cancelable;
        return e;
      },
      stop : function(){

        if(this._bubbles){

          this.stopPropagation();
        };
        if(this._cancelable){

          this.preventDefault();
        };
      },
      stopPropagation : function(){

        {
        };
        this._stopPropagation = true;
      },
      getPropagationStopped : function(){

        return !!this._stopPropagation;
      },
      preventDefault : function(){

        {
        };
        this._preventDefault = true;
      },
      getDefaultPrevented : function(){

        return !!this._preventDefault;
      },
      getType : function(){

        return this._type;
      },
      setType : function(f){

        this._type = f;
      },
      getEventPhase : function(){

        return this._eventPhase;
      },
      setEventPhase : function(g){

        this._eventPhase = g;
      },
      getTimeStamp : function(){

        return this._timeStamp;
      },
      getTarget : function(){

        return this._target;
      },
      setTarget : function(h){

        this._target = h;
      },
      getCurrentTarget : function(){

        return this._currentTarget || this._target;
      },
      setCurrentTarget : function(i){

        this._currentTarget = i;
      },
      getRelatedTarget : function(){

        return this._relatedTarget;
      },
      setRelatedTarget : function(j){

        this._relatedTarget = j;
      },
      getOriginalTarget : function(){

        return this._originalTarget;
      },
      setOriginalTarget : function(k){

        this._originalTarget = k;
      },
      getBubbles : function(){

        return this._bubbles;
      },
      setBubbles : function(l){

        this._bubbles = l;
      },
      isCancelable : function(){

        return this._cancelable;
      },
      setCancelable : function(m){

        this._cancelable = m;
      }
    },
    destruct : function(){

      this._target = this._currentTarget = this._relatedTarget = this._originalTarget = null;
    }
  });
})();
(function(){

  var d = "qx.util.ObjectPool",c = "Class needs to be defined!",b = "Object is already pooled: ",a = "Integer";
  qx.Class.define(d, {
    extend : qx.core.Object,
    construct : function(e){

      qx.core.Object.call(this);
      this.__cQ = {
      };
      if(e != null){

        this.setSize(e);
      };
    },
    properties : {
      size : {
        check : a,
        init : Infinity
      }
    },
    members : {
      __cQ : null,
      getObject : function(f){

        if(this.$$disposed){

          return new f;
        };
        if(!f){

          throw new Error(c);
        };
        var g = null;
        var h = this.__cQ[f.classname];
        if(h){

          g = h.pop();
        };
        if(g){

          g.$$pooled = false;
        } else {

          g = new f;
        };
        return g;
      },
      poolObject : function(j){

        if(!this.__cQ){

          return;
        };
        var k = j.classname;
        var m = this.__cQ[k];
        if(j.$$pooled){

          throw new Error(b + j);
        };
        if(!m){

          this.__cQ[k] = m = [];
        };
        if(m.length > this.getSize()){

          if(j.destroy){

            j.destroy();
          } else {

            j.dispose();
          };
          return;
        };
        j.$$pooled = true;
        m.push(j);
      }
    },
    destruct : function(){

      var p = this.__cQ;
      var n,o,i,l;
      for(n in p){

        o = p[n];
        for(i = 0,l = o.length;i < l;i++){

          o[i].dispose();
        };
      };
      delete this.__cQ;
    }
  });
})();
(function(){

  var b = "singleton",a = "qx.event.Pool";
  qx.Class.define(a, {
    extend : qx.util.ObjectPool,
    type : b,
    construct : function(){

      qx.util.ObjectPool.call(this, 30);
    }
  });
})();
(function(){

  var a = "qx.event.dispatch.Direct";
  qx.Class.define(a, {
    extend : qx.core.Object,
    implement : qx.event.IEventDispatcher,
    construct : function(b){

      this._manager = b;
    },
    statics : {
      PRIORITY : qx.event.Registration.PRIORITY_LAST
    },
    members : {
      canDispatchEvent : function(c, event, d){

        return !event.getBubbles();
      },
      dispatchEvent : function(e, event, f){

        var j,g;
        {
        };
        event.setEventPhase(qx.event.type.Event.AT_TARGET);
        var k = this._manager.getListeners(e, f, false);
        if(k){

          for(var i = 0,l = k.length;i < l;i++){

            var h = k[i].context || e;
            {
            };
            k[i].handler.call(h, event);
          };
        };
      }
    },
    defer : function(m){

      qx.event.Registration.addDispatcher(m);
    }
  });
})();
(function(){

  var a = "qx.event.handler.Object";
  qx.Class.define(a, {
    extend : qx.core.Object,
    implement : qx.event.IEventHandler,
    statics : {
      PRIORITY : qx.event.Registration.PRIORITY_LAST,
      SUPPORTED_TYPES : null,
      TARGET_CHECK : qx.event.IEventHandler.TARGET_OBJECT,
      IGNORE_CAN_HANDLE : false
    },
    members : {
      canHandleEvent : function(b, c){

        return qx.Class.supportsEvent(b.constructor, c);
      },
      registerEvent : function(d, e, f){
      },
      unregisterEvent : function(g, h, i){
      }
    },
    defer : function(j){

      qx.event.Registration.addHandler(j);
    }
  });
})();
(function(){

  var a = "qx.event.type.Data";
  qx.Class.define(a, {
    extend : qx.event.type.Event,
    members : {
      __cR : null,
      __cS : null,
      init : function(b, c, d){

        qx.event.type.Event.prototype.init.call(this, false, d);
        this.__cR = b;
        this.__cS = c;
        return this;
      },
      clone : function(e){

        var f = qx.event.type.Event.prototype.clone.call(this, e);
        f.__cR = this.__cR;
        f.__cS = this.__cS;
        return f;
      },
      getData : function(){

        return this.__cR;
      },
      getOldData : function(){

        return this.__cS;
      }
    },
    destruct : function(){

      this.__cR = this.__cS = null;
    }
  });
})();
(function(){

  var co = "unit/update_sensor",cn = "resource/update_poi",cm = "unitSensors",cl = "adminField",ck = "resource/update_drivers_group",cj = "wialon events error: ",ci = "render",ch = "u",cg = "avl_route",cf = "item/delete_item",bt = "singleton",bs = "resourcePois",br = "aflds",bq = "item/update_admin_field",bp = "serverUpdated",bo = "core/check_items_billing",bn = "http://search.mapsviewer.com/",bm = "resource/get_job_data",bl = "//",bk = "resource/get_poi_data",cv = "routeRounds",cw = "report/get_report_data",ct = "report",cu = "unitFuelSettings",cr = "route/get_round_data",cs = "flds",cp = "userNotification",cq = "si",cx = "core/get_hw_types",cy = "core/search_item",bR = "=",bQ = "core/create_resource",bT = "core/create_user",bS = "customField",bV = "core/create_unit",bU = "; ",bX = "usnf",bW = "unitCommandDefinitions",bP = "userNotifications",bO = "itemAdminFields",a = "routeSchedules",b = "core/login",c = "route/update_round",d = "geocode",e = "core/create_auth_hash",f = "core/update_data_flags",g = "serviceInterval",h = "core/create_retranslator",j = "resourceReports",k = "core/reset_password_perform",cC = "core/create_route",cB = "unitReportSettings",cA = "sensor",cz = "qx.event.type.Data",cG = "search",cF = "resource/update_zone",cE = "__de",cD = "/avl_evts",cI = "zone",cH = "round",J = "rr",K = "core/create_unit_group",H = "unf",I = "core/get_account_data",N = "fileUploaded",O = "resourceDrivers",L = "commandDefinition",M = "unitServiceIntervals",F = "report/update_report",G = "resource/update_job",s = "rs",r = "zl",u = "en",t = "unitEventRegistrar",o = 'undefined',n = "user/update_user_notification",q = "d",p = "trlrs",m = "user/send_sms",l = "core/get_hw_cmds",T = "route/update_schedule",U = "lang",V = "m",W = "resource/update_trailers_group",P = "report/get_report_tables",Q = "resourceAccounts",R = "trailer",S = "notification",X = "unit/update_command_definition",Y = "itemIcon",C = "schedule",B = "drvrs",A = "driver",z = "core/search_items",y = "core/duplicate",x = "__df",w = "trlrsgr",v = "trailersGroup",E = "itemCustomFields",D = "resource/update_notification",ba = "wialon.core.Session",bb = "item/update_custom_field",bc = "unit/update_service_interval",bd = "invalidSession",be = "drvrsgr",bf = "resourceJobs",bg = "core/use_auth_hash",bh = "resourceTrailers",bi = "__dc",bj = "resourceTrailerGroups",bx = "core/logout",bw = "driversGroup",bv = "resourceNotifications",bu = "job",bB = "resource/update_trailer",bA = "core/reset_password_request",bz = "resource/get_notification_data",by = "ujb",bD = "unitMessagesFilter",bC = "cml",bK = "unitTripDetector",bL = "http://geocode.mapsviewer.com/",bI = "resource/get_zone_data",bJ = "resourceZones",bG = "rep",bH = "http://render.mapsviewer.com/",bE = "sens",bF = "resource/update_driver",bM = "resourceDriverGroups",bN = "qx.event.type.Event",ca = "poi",bY = "__cY",cc = "itemDeleted",cb = "object",ce = "",cd = "undefined";
  qx.Class.define(ba, {
    extend : qx.core.Object,
    type : bt,
    construct : function(){

      qx.core.Object.call(this);
      this.__cT = {
      };
      this.__cU = {
      };
      this._libraries = {
      };
    },
    members : {
      __cV : 0,
      __cW : ce,
      __cX : 0,
      __cY : null,
      __da : 0,
      __db : null,
      __dc : null,
      __dd : null,
      __cT : null,
      __cU : null,
      _libraries : null,
      __Sa : ce,
      __de : null,
      __df : null,
      __dg : ce,
      __dh : false,
      __di : ce,
      __dj : ce,
      __dk : ce,
      __dl : [],
      initSession : function(cJ, cK, cL, cM){

        if(this.__cV)return false;
        wialon.item.Item.registerProperties();
        wialon.item.User.registerProperties();
        wialon.item.Unit.registerProperties();
        wialon.item.Resource.registerProperties();
        wialon.item.UnitGroup.registerProperties();
        wialon.item.Retranslator.registerProperties();
        wialon.item.Route.registerProperties();
        this.__dg = cJ;
        if(typeof cK != cd)this.__di = cK;
        if(typeof cL != cd && !isNaN(parseInt(cL))){

          var cN = parseInt(cL);
          if(cN & 0x800)this.__dh = true;
        };
        if(typeof cM != cd)this.__dk = cM;
        this.__de = new wialon.render.Renderer;
        this.__df = new wialon.core.MessagesLoader;
        this.__cV = 1;
        return true;
      },
      isInitialized : function(){

        return this.__cV;
      },
      getAuthUser : function(){

        return this.__Sa;
      },
      login : function(cO, cP, cQ, cR){

        cR = wialon.util.Helper.wrapCallback(cR);
        if(this.__cY || !this.__cV){

          cR(2);
          return;
        };
        wialon.core.Remote.getInstance().remoteCall(b, {
          user : cO,
          password : cP,
          operateAs : cQ,
          loginHash : this.__dj,
          appName : this.__di,
          checkService : this.__dk
        }, qx.lang.Function.bind(this.__dt, this, cR));
      },
      loginAuthHash : function(cS, cT){

        cT = wialon.util.Helper.wrapCallback(cT);
        if(this.__cY || !this.__cV){

          cT(2);
          return;
        };
        wialon.core.Remote.getInstance().remoteCall(bg, {
          authHash : cS
        }, qx.lang.Function.bind(this.__dt, this, cT));
      },
      duplicate : function(cU, cV, cW, cX){

        cX = wialon.util.Helper.wrapCallback(cX);
        if(this.__cY || !this.__cV){

          cX(2);
          return;
        };
        this.__cW = cU;
        wialon.core.Remote.getInstance().remoteCall(y, {
          operateAs : cV,
          continueCurrentSession : cW
        }, qx.lang.Function.bind(this.__dt, this, cX));
      },
      logout : function(cY){

        cY = wialon.util.Helper.wrapCallback(cY);
        if(!this.__cY){

          cY(2);
          return;
        };
        wialon.core.Remote.getInstance().remoteCall(bx, null, qx.lang.Function.bind(function(da, db){

          if(da){

            cY(da);
            return;
          };
          this.__dp();
          cY(0);
        }, this));
      },
      createAuthHash : function(dc){

        dc = wialon.util.Helper.wrapCallback(dc);
        if(!this.__cY){

          dc(2);
          return;
        };
        wialon.core.Remote.getInstance().remoteCall(e, {
        }, dc);
      },
      updateDataFlags : function(dd, de){

        de = wialon.util.Helper.wrapCallback(de);
        if(!this.__cY || typeof dd != cb){

          de(2);
          return;
        };
        wialon.core.Remote.getInstance().remoteCall(f, {
          spec : dd
        }, qx.lang.Function.bind(this.__du, this, de));
      },
      searchItems : function(df, dg, dh, di, dj, dk){

        dk = wialon.util.Helper.wrapCallback(dk);
        if(!this.__cY || typeof df != cb){

          dk(2, null);
          return;
        };
        wialon.core.Remote.getInstance().remoteCall(z, {
          spec : df,
          force : dg ? 1 : 0,
          flags : dh,
          from : di,
          to : dj
        }, qx.lang.Function.bind(this.__dv, this, dk));
      },
      searchItem : function(dl, dm, dn){

        dn = wialon.util.Helper.wrapCallback(dn);
        if(!this.__cY){

          dn(2, null);
          return;
        };
        wialon.core.Remote.getInstance().remoteCall(cy, {
          id : dl,
          flags : dm
        }, qx.lang.Function.bind(this.__dw, this, dn));
      },
      loadLibrary : function(dp){

        if(typeof this._libraries[dp] != cd)return true;
        if(dp == bP)wialon.item.PluginsManager.bindPropItem(wialon.item.User, bX, cp, n); else if(dp == E)wialon.item.PluginsManager.bindPropItem(wialon.item.Item, cs, bS, bb); else if(dp == bO)wialon.item.PluginsManager.bindPropItem(wialon.item.Item, br, cl, bq); else if(dp == Y){

          qx.Class.include(wialon.item.Unit, wialon.item.MIcon);
          qx.Class.include(wialon.item.UnitGroup, wialon.item.MIcon);
        } else if(dp == bW)wialon.item.PluginsManager.bindPropItem(wialon.item.Unit, bC, L, X); else if(dp == cm){

          wialon.item.PluginsManager.bindPropItem(wialon.item.Unit, bE, cA, co);
          qx.Class.include(wialon.item.Unit, wialon.item.MUnitSensor);
        } else if(dp == M)wialon.item.PluginsManager.bindPropItem(wialon.item.Unit, cq, g, bc); else if(dp == bK)qx.Class.include(wialon.item.Unit, wialon.item.MUnitTripDetector); else if(dp == bD)qx.Class.include(wialon.item.Unit, wialon.item.MUnitMessagesFilter); else if(dp == t)qx.Class.include(wialon.item.Unit, wialon.item.MUnitEventRegistrar); else if(dp == cB)qx.Class.include(wialon.item.Unit, wialon.item.MUnitReportSettings); else if(dp == cu)qx.Class.include(wialon.item.Unit, wialon.item.MUnitFuelSettings); else if(dp == bv)wialon.item.PluginsManager.bindPropItem(wialon.item.Resource, H, S, D, bz); else if(dp == bf)wialon.item.PluginsManager.bindPropItem(wialon.item.Resource, by, bu, G, bm); else if(dp == bJ){

          qx.Class.include(wialon.item.Resource, wialon.item.MZone);
          wialon.item.PluginsManager.bindPropItem(wialon.item.Resource, r, cI, cF, bI);
        } else if(dp == bs){

          qx.Class.include(wialon.item.Resource, wialon.item.MPoi);
          wialon.item.PluginsManager.bindPropItem(wialon.item.Resource, ca, ca, cn, bk);
        } else if(dp == O){

          qx.Class.include(wialon.item.Resource, wialon.item.MDriver);
          wialon.item.MDriver.registerDriverProperties();
          wialon.item.PluginsManager.bindPropItem(wialon.item.Resource, B, A, bF);
        } else if(dp == bM){

          qx.Class.include(wialon.item.Resource, wialon.item.MDriver);
          wialon.item.MDriver.registerDriverProperties();
          wialon.item.PluginsManager.bindPropItem(wialon.item.Resource, be, bw, ck);
        } else if(dp == bh){

          qx.Class.include(wialon.item.Resource, wialon.item.MDriver);
          wialon.item.MDriver.registerDriverProperties();
          wialon.item.PluginsManager.bindPropItem(wialon.item.Resource, p, R, bB);
        } else if(dp == bj){

          qx.Class.include(wialon.item.Resource, wialon.item.MDriver);
          wialon.item.MDriver.registerDriverProperties();
          wialon.item.PluginsManager.bindPropItem(wialon.item.Resource, w, v, W);
        } else if(dp == Q)qx.Class.include(wialon.item.Resource, wialon.item.MAccount); else if(dp == j){

          qx.Class.include(wialon.item.Resource, wialon.item.MReport);
          wialon.item.PluginsManager.bindPropItem(wialon.item.Resource, bG, ct, F, cw);
        } else if(dp == cv)wialon.item.PluginsManager.bindPropItem(wialon.item.Route, J, cH, c, cr); else if(dp == a)wialon.item.PluginsManager.bindPropItem(wialon.item.Route, s, C, T); else return false;;;;;;;;;;;;;;;;;;;;;;;;
        this._libraries[dp] = 1;
        return true;
      },
      getHwTypes : function(dq){

        dq = wialon.util.Helper.wrapCallback(dq);
        if(!this.__cY){

          dq(2);
          return;
        };
        wialon.core.Remote.getInstance().remoteCall(cx, {
        }, dq);
      },
      getHwCommands : function(dr, ds, dt){

        dt = wialon.util.Helper.wrapCallback(dt);
        if(!this.__cY){

          dt(2);
          return;
        };
        wialon.core.Remote.getInstance().remoteCall(l, {
          deviceTypeId : dr,
          unitId : ds
        }, dt);
      },
      createUnit : function(du, name, dv, dw, dx){

        dx = wialon.util.Helper.wrapCallback(dx);
        if(!this.__cY){

          dx(2);
          return;
        };
        wialon.core.Remote.getInstance().remoteCall(bV, {
          creatorId : du.getId(),
          name : name,
          hwTypeId : dv,
          dataFlags : dw
        }, qx.lang.Function.bind(this.__dw, this, dx));
      },
      createUser : function(dy, name, dz, dA, dB){

        dB = wialon.util.Helper.wrapCallback(dB);
        if(!this.__cY){

          dB(2);
          return;
        };
        wialon.core.Remote.getInstance().remoteCall(bT, {
          creatorId : dy.getId(),
          name : name,
          password : dz,
          dataFlags : dA
        }, qx.lang.Function.bind(this.__dw, this, dB));
      },
      createUnitGroup : function(dC, name, dD, dE){

        dE = wialon.util.Helper.wrapCallback(dE);
        if(!this.__cY){

          dE(2);
          return;
        };
        wialon.core.Remote.getInstance().remoteCall(K, {
          creatorId : dC.getId(),
          name : name,
          dataFlags : dD
        }, qx.lang.Function.bind(this.__dw, this, dE));
      },
      createRetranslator : function(dF, name, dG, dH, dI){

        dI = wialon.util.Helper.wrapCallback(dI);
        if(!this.__cY){

          dI(2);
          return;
        };
        wialon.core.Remote.getInstance().remoteCall(h, {
          creatorId : dF.getId(),
          name : name,
          config : dG,
          dataFlags : dH
        }, qx.lang.Function.bind(this.__dw, this, dI));
      },
      createRoute : function(dJ, name, dK, dL){

        dL = wialon.util.Helper.wrapCallback(dL);
        if(!this.__cY){

          dL(2);
          return;
        };
        var self = this;
        wialon.core.Session.getInstance().checkItemsBilling([dJ.getAccountId()], cg, 1, function(dM, dN){

          if(!dM && dN.length){

            wialon.core.Remote.getInstance().remoteCall(cC, {
              creatorId : dJ.getId(),
              name : name,
              dataFlags : dK
            }, qx.lang.Function.bind(self.__dw, self, dL));
          } else {

            dL(7);
          };
        });
      },
      createResource : function(dO, name, dP, dQ){

        dQ = wialon.util.Helper.wrapCallback(dQ);
        if(!this.__cY){

          dQ(2);
          return;
        };
        wialon.core.Remote.getInstance().remoteCall(bQ, {
          creatorId : dO.getId(),
          name : name,
          dataFlags : dP
        }, qx.lang.Function.bind(this.__dw, this, dQ));
      },
      deleteItem : function(dR, dS){

        dS = wialon.util.Helper.wrapCallback(dS);
        wialon.core.Remote.getInstance().remoteCall(cf, {
          itemId : dR.getId()
        }, qx.lang.Function.bind(this.__dx, this, dS, dR.getId()));
      },
      updateItem : function(dT, dU){

        if(!dT || !dU)return;
        for(var dW in dU){

          var dV = this.__cT[dW];
          if(typeof dV != cd)dV(dT, dU[dW]);
        };
      },
      resetPasswordRequest : function(dX, dY, ea, eb, ec){

        ec = wialon.util.Helper.wrapCallback(ec);
        if(!this.__cV){

          ec(2);
          return;
        };
        var ee = document.cookie.split(bU);
        var ed = u;
        for(var i = 0;i < ee.length;i++){

          var ef = ee[i].split(bR);
          if(ef.length == 2 && ef[0] == U){

            ed = ef[1];
            break;
          };
        };
        wialon.core.Remote.getInstance().remoteCall(bA, {
          user : dX,
          email : dY,
          emailFrom : ea,
          url : eb,
          lang : ed
        }, ec);
      },
      resetPasswordPerform : function(eg, eh, ei){

        ei = wialon.util.Helper.wrapCallback(ei);
        if(!this.__cV){

          ei(2);
          return;
        };
        wialon.core.Remote.getInstance().remoteCall(k, {
          user : eg,
          code : eh
        }, ei);
      },
      sendSms : function(ej, ek, el){

        wialon.core.Remote.getInstance().remoteCall(m, {
          phoneNumber : ej,
          smsText : ek
        }, wialon.util.Helper.wrapCallback(el));
      },
      getAccountData : function(em, en){

        wialon.core.Remote.getInstance().remoteCall(I, {
          type : em ? 2 : 1
        }, wialon.util.Helper.wrapCallback(en));
      },
      checkItemsBilling : function(eo, ep, eq, er){

        wialon.core.Remote.getInstance().remoteCall(bo, {
          items : eo,
          serviceName : ep,
          accessFlags : eq
        }, wialon.util.Helper.wrapCallback(er));
      },
      getReportTables : function(es){

        wialon.core.Remote.getInstance().remoteCall(P, {
        }, wialon.util.Helper.wrapCallback(es));
      },
      setLoginHash : function(et){

        this.__dj = et;
      },
      getCurrUser : function(){

        return this.__cY;
      },
      getServerTime : function(){

        return this.__da;
      },
      getItem : function(eu){

        if(!this.__dc)return null;
        var ev = this.__dc[parseInt(eu)];
        if(typeof ev != o)return ev;
        return null;
      },
      getItems : function(ew){

        if(!this.__dc || !this.__dd)return null;
        if(typeof ew == cd || ew == ce){

          var ey = new Array;
          for(var ez in this.__dc)ey.push(this.__dc[ez]);
          return ey;
        } else {

          var ex = this.__dd[ew];
          if(typeof ex != cd)return ex;
        };
        return (new Array);
      },
      registerConstructor : function(eA, eB){

        if(typeof this.__cU[eA] != cd)return;
        this.__cU[eA] = eB;
      },
      registerProperty : function(eC, eD){

        if(typeof this.__cT[eC] != cd)return;
        this.__cT[eC] = eD;
      },
      getBaseUrl : function(){

        return this.__dg;
      },
      getBaseGisUrl : function(eE){

        if(!this.__dh && this.__dg != ce){

          var eF = this.__dg.split(bl);
          if(eF.length >= 2){

            if(eE == ci)return bH + eF[1]; else if(eE == cG)return bn + eF[1]; else if(eE == d)return bL + eF[1];;;
          };
        };
        return this.__dg;
      },
      getId : function(){

        return this.__cW;
      },
      getRenderer : function(){

        return this.__de;
      },
      getMessagesLoader : function(){

        return this.__df;
      },
      __dm : function(){

        if(!this.__cW || !this.__cX)return;
        wialon.core.Remote.getInstance().ajaxRequest(this.__dg + cD, {
          sid : this.__cW
        }, qx.lang.Function.bind(this.__dn, this), 60);
      },
      __dn : function(eG, eH){

        if(eG != 0){

          if(eG == 1){

            this.fireEvent(bd);
            this.__dp();
          } else if(this.__cX)qx.lang.Function.delay(this.__dm, this.__cX * 1000, this);;
          return;
        };
        try{

          this.__da = eH.tm;
          for(var i = 0;i < eH.events.length;i++){

            var eI = eH.events[i];
            if(eI.i > 0){

              var eJ = this.getItem(eI.i);
              if(eJ && typeof eJ != cd){

                if(eI.t == ch)this.updateItem(eJ, eI.d); else if(eI.t == V)eJ.handleMessage(eI.d); else if(eI.t == q)this._onItemDeleted(eJ);;;
              } else this.__dl.push(eI);
            } else if(eI.i == -1){

              this.fireDataEvent(N, eI.d, null);
            };
          };
        } catch(eK) {

          this.error(cj + eK.message);
        };
        if(this.__cX)qx.lang.Function.delay(this.__dm, this.__cX * 1000, this);
        this.fireEvent(bp);
      },
      __do : function(eL){

        if(!eL || this.__cY)return false;
        this.__cW = eL.eid;
        this.__cX = 2;
        this.__da = eL.tm;
        this.__Sa = eL.au;
        this.__db = {
        };
        for(var eM in eL.classes)this.__db[eL.classes[eM]] = eM;
        this.__dc = {
        };
        this.__dd = {
        };
        this.__cY = this.__dq(eL.user, wialon.item.User.defaultDataFlags());
        this.__dr(this.__cY);
        if(this.__cX)qx.lang.Function.delay(this.__dm, this.__cX * 1000, this);
        return true;
      },
      __dp : function(){

        this.__cV = 0;
        this.__cW = ce;
        this.__cY = null;
        this.__da;
        this.__dc = null;
        this.__dd = null;
        this.__cX = 0;
        this.__de = null;
        this.__df = null;
        this.__dg = ce;
        this.__dh = false;
        this.__cT = {
        };
        this.__cU = {
        };
        this.__Sa = ce;
        this._libraries = {
        };
        this._disposeMap(bi);
        this._disposeObjects(bY);
        this.__db = null;
        this.__dd = null;
      },
      __dq : function(eN, eO){

        if(!eN || !eO)return null;
        eN.tp = this.__db[eN.cls];
        if(typeof eN.tp == cd)return null;
        var eP;
        var eQ = this.__cU[eN.tp];
        if(typeof eQ == cd)return null;
        eP = new eQ(eN, eO);
        this.updateItem(eP, eN);
        if(eP && this.__dl && this.__dl.length){

          for(var i = 0;i < this.__dl.length;i++){

            if(typeof this.__dl[i] == cb && eP.getId() == this.__dl[i].i){

              this.updateItem(eP, this.__dl[i].d);
              delete this.__dl[i];
              break;
            };
          };
        };
        return eP;
      },
      __dr : function(eR){

        if(!eR || !this.__dc)return;
        this.__dc[eR.getId()] = eR;
        var eS = this.__dd[eR.getType()];
        if(typeof eS == cd){

          this.__dd[eR.getType()] = new Array;
          eS = this.__dd[eR.getType()];
        };
        eS.push(eR);
      },
      __ds : function(eT){

        if(!eT)return;
        if(typeof this.__dc[eT.getId()] != cd)delete this.__dc[eT.getId()];
        var eU = this.__dd[eT.getType()];
        if(typeof eU != cd)qx.lang.Array.remove(eU, eT);
        eT.dispose();
      },
      _onItemDeleted : function(eV){

        if(!eV)return;
        eV.fireEvent(cc);
        this.__ds(eV);
      },
      __dt : function(eW, eX, eY){

        if(eX || !eY){

          eW(eX);
          return;
        };
        if(this.__do(eY))eW(0); else eW(6);
      },
      __du : function(fa, fb, fc){

        if(fb || !fc){

          fa(fb);
          return;
        };
        for(var i = 0;i < fc.length;i++){

          var fe = fc[i].f;
          var fd = fc[i].i;
          var ff = fc[i].d;
          var fg = this.__dc[fd];
          if(typeof fg == cd && fe != 0 && ff){

            var fg = this.__dq(ff, fe);
            if(fg)this.__dr(fg);
          } else {

            if(fe == 0)this.__ds(fg); else {

              if(typeof fg == cd)return;
              if(ff)this.updateItem(fg, ff);
              fg.setDataFlags(fe);
            };
          };
          ff = null;
        };
        fa(0);
      },
      __dv : function(fh, fi, fj){

        if(fi || !fj){

          fh(fi, null);
          return;
        };
        var fl = {
          searchSpec : fj.searchSpec,
          dataFlags : fj.dataFlags,
          totalItemsCount : fj.totalItemsCount,
          indexFrom : fj.indexFrom,
          indexTo : fj.indexTo,
          items : []
        };
        for(var i = 0;i < fj.items.length;i++){

          var fk = this.__dq(fj.items[i], fj.dataFlags);
          if(fk)fl.items.push(fk);
        };
        fh(0, fl);
      },
      __dw : function(fm, fn, fo){

        if(fn || !fo){

          fm(fn, null);
          return;
        };
        var fp = this.__dq(fo.item, fo.flags);
        fm((fp === null ? 6 : 0), fp);
      },
      __dx : function(fq, fr, fs, ft){

        if(!fs){

          var fu = this.getItem(fr);
          if(fu){

            fu.fireEvent(cc);
            this.__ds(fu);
          };
        };
        fq(fs);
      }
    },
    destruct : function(){

      this.__dp();
      this._disposeObjects(bY, cE, x);
    },
    events : {
      "serverUpdated" : bN,
      "invalidSession" : bN,
      "fileUploaded" : cz
    }
  });
})();
(function(){

  var j = "prp",i = "item/add_log_record",h = "string",g = "delete_item",f = "Object",e = "prpu",d = "bact",c = "custom_msg",b = "qx.event.type.Event",a = "nm",B = "item/update_custom_property",A = "changeUserAccess",z = "update_name",y = "String",x = "changeDataFlags",w = "number",v = "item/update_name",u = "messageRegistered",t = "changeCustomProperty",s = "uacl",q = "wialon.item.Item",r = "changeName",o = "crt",p = "update_access",m = "undefined",n = "Integer",k = "qx.event.type.Data",l = "";
  qx.Class.define(q, {
    extend : qx.core.Object,
    construct : function(C, D){

      qx.core.Object.call(this);
      this.setDataFlags(D);
      this._id = C.id;
      this._type = C.tp;
    },
    properties : {
      dataFlags : {
        init : null,
        check : n,
        event : x
      },
      name : {
        init : null,
        check : y,
        event : r
      },
      userAccess : {
        init : null,
        check : n,
        event : A
      },
      customProps : {
        init : null,
        check : f
      },
      creatorId : {
        init : null,
        check : n
      },
      accountId : {
        init : null,
        check : n
      }
    },
    members : {
      _id : 0,
      _type : l,
      getId : function(){

        return this._id;
      },
      getType : function(){

        return this._type;
      },
      getCustomProperty : function(E, F){

        var H = this.getCustomProps();
        if(H){

          var G = H[E];
          if(typeof G != m)return G;
        };
        if(typeof F != m)return F;
        return l;
      },
      setCustomProperty : function(I, J){

        var L = this.getCustomProps();
        if(L){

          var K = L[I];
          if(typeof K == m)K = l;
          if(J != l)L[I] = J; else if(K != l)delete L[I];;
          if(J != K)this.fireDataEvent(t, {
            n : I,
            v : J
          }, {
            n : I,
            v : K
          });
        };
      },
      handleMessage : function(M){

        this.fireDataEvent(u, M, null);
      },
      updateCustomProperty : function(N, O, P){

        wialon.core.Remote.getInstance().remoteCall(B, {
          itemId : this.getId(),
          name : N,
          value : (typeof O == h || typeof O == w) ? O : l
        }, qx.lang.Function.bind(this.__dy, this, wialon.util.Helper.wrapCallback(P)));
      },
      updateName : function(name, Q){

        wialon.core.Remote.getInstance().remoteCall(v, {
          itemId : this.getId(),
          name : name
        }, qx.lang.Function.bind(this._onUpdateProperties, this, wialon.util.Helper.wrapCallback(Q)));
      },
      addLogRecord : function(R, S, T, U){

        wialon.core.Remote.getInstance().remoteCall(i, {
          itemId : this.getId(),
          action : R,
          newValue : S || l,
          oldValue : T || l
        }, wialon.util.Helper.wrapCallback(U));
      },
      __dy : function(V, W, X){

        if(W == 0 && X)this.setCustomProperty(X.n, X.v);
        V(W);
      },
      _onUpdateProperties : function(Y, ba, bb){

        if(ba == 0 && bb)wialon.core.Session.getInstance().updateItem(this, bb);
        Y(ba);
      }
    },
    statics : {
      dataFlag : {
        base : 0x00000001,
        customProps : 0x00000002,
        billingProps : 0x00000004,
        customFields : 0x00000008,
        image : 0x00000010,
        messages : 0x00000020,
        guid : 0x00000040,
        adminFields : 0x00000080
      },
      accessFlag : {
        view : 0x1,
        viewProperties : 0x2,
        setAcl : 0x4,
        deleteItem : 0x8,
        editName : 0x10,
        viewCFields : 0x20,
        editCFields : 0x40,
        editOther : 0x80,
        editImage : 0x100,
        execReports : 0x200,
        editSubItems : 0x400,
        manageLog : 0x800,
        viewAFields : 0x1000,
        editAFields : 0x2000
      },
      messageFlag : {
        typeMask : 0xFF00,
        typeUnitData : 0x0000,
        typeUnitSMS : 0x0100,
        typeUnitCmd : 0x0200,
        typeUnitEvent : 0x0600,
        typeUserLog : 0x0400,
        typeNotification : 0x0300,
        typeBalance : 0x0500,
        typeAgroCultivation : 0x0700,
        typeDriverSMS : 0x0900,
        typeLogRecord : 0x1000,
        typeOther : 0xFF00
      },
      logMessageAction : {
        itemCustomMessage : c,
        itemUpdatedName : z,
        itemUpdatedUserAccess : p,
        itemDeleted : g
      },
      registerProperties : function(){

        var bc = wialon.core.Session.getInstance();
        bc.registerProperty(a, this.remoteUpdateName);
        bc.registerProperty(s, this.remoteUpdateUserAccess);
        bc.registerProperty(j, this.remoteUpdateCustomProps);
        bc.registerProperty(e, this.remoteUpdateCustomProp);
        bc.registerProperty(o, this.remoteUpdateCreatorId);
        bc.registerProperty(d, this.remoteUpdateAccountId);
      },
      remoteUpdateName : function(bd, be){

        bd.setName(be);
      },
      remoteUpdateUserAccess : function(bf, bg){

        bf.setUserAccess(bg);
      },
      remoteUpdateCustomProps : function(bh, bi){

        bh.setCustomProps(bi);
      },
      remoteUpdateCustomProp : function(bj, bk){

        for(var bl in bk){

          bj.setCustomProperty(bl, bk[bl]);
        };
      },
      remoteUpdateCreatorId : function(bm, bn){

        bm.setCreatorId(bn);
      },
      remoteUpdateAccountId : function(bo, bp){

        bo.setAccountId(bp);
      }
    },
    events : {
      "changeName" : k,
      "changeDataFlags" : k,
      "changeUserAccess" : k,
      "changeCustomProperty" : k,
      "itemDeleted" : b,
      "messageRegistered" : k
    }
  });
})();
(function(){

  var k = "singleton",j = "wialon.core.Remote",h = "error",g = "/gis_post?1",f = "abort",e = "&sid=",d = "//",c = ":",b = "core/batch",a = "/gis_geocode",x = "/wialon/post.html",w = "/gis_search",v = "success",u = "statusError",t = "/gis_post?2",s = /*"/wialon/ajax.html?svc="*/"ajax.html?svc=",r = "timeout",q = "object",p = "sdk",o = "geocode",m = "search",n = "undefined",l = "";
  qx.Class.define(j, {
    extend : qx.core.Object,
    type : k,
    construct : function(){

      qx.core.Object.call(this);
      this._req = {
      };
//      this._req[p] = new wialon.core.PostMessage(this.__dC(wialon.core.Session.getInstance().getBaseUrl()) + x, 0);
		this._req[p] = new wialon.core.PostMessage("https://hst-api.wialon.com" + x, 0);
      this._req[m] = new wialon.core.PostMessage(this.__dC(wialon.core.Session.getInstance().getBaseGisUrl(m)) + g, 1);
      this._req[o] = new wialon.core.PostMessage(this.__dC(wialon.core.Session.getInstance().getBaseGisUrl(o)) + t, 2);
    },
    members : {
      __dz : null,
      __dA : [],
      __dB : l,
      remoteCall : function(y, z, A, B){

        A = wialon.util.Helper.wrapCallback(A);
        if(typeof B == n)B = 30;
        var C = wialon.core.Session.getInstance().getBaseUrl() + s + y + e + wialon.core.Session.getInstance().getId();
        if(this.__dz)this.__dz.push({
          svc : y,
          params : z ? z : {
          },
          callback : A,
          timeout : B
        }); else {

          var D = {
            params : {
            }
          };
          if(z)D = {
            params : z
          };
          this.ajaxRequest(C, D, A, B);
        };
      },
      startBatch : function(E){

        if(this.__dz)return 0;
        if(E)this.__dB = E;
        this.__dz = new Array;
        return 1;
      },
      finishBatch : function(F, G){

        F = wialon.util.Helper.wrapCallback(F);
        if(!this.__dz){

          F(2, 2);
          return;
        };
        this.__dA.push(F);
        if(this.__dB && G != this.__dB){

          this.__dA.push(F);
          return;
        };
        F = wialon.util.Helper.wrapCallback(this.__dA);
        if(!this.__dz.length){

          this.__dB = l;
          this.__dA = [];
          this.__dz = null;
          F(0, 0);
          return;
        };
        var J = 0;
        var I = [];
        var H = [];
        for(var i = 0;i < this.__dz.length;i++){

          var K = this.__dz[i];
          I.push({
            svc : K.svc,
            params : K.params
          });
          H.push(K.callback);
          if(K.timeout > J)J = K.timeout;
        };
        this.__dz = null;
        this.__dB = l;
        this.__dA = [];
        this.remoteCall(b, I, qx.lang.Function.bind(this.__dG, this, F, H), J);
      },
      ajaxRequest : function(L, M, N, O){

        var P = p;
        if(L.match(a))P = o; else if(L.match(w))P = m;;
        this._req[P].send(L, M, qx.lang.Function.bind(this.__dD, this, N), qx.lang.Function.bind(this.__dE, this, N), O);
      },
      jsonRequest : function(Q, R, S, T, U){

        var W = new qx.io.request.Jsonp(Q);
        var V = null;
        W.setCache(false);
        if(U)W.setCallbackName(U);
        W.setTimeout(T * 1000);
        if(R){

          if(typeof R == q)W.setRequestData(R); else W.setRequestData({
            params : R
          });
        };
        if(S){

          W.addListener(v, qx.lang.Function.bind(this.__dF, this, S, W));
          V = qx.lang.Function.bind(this.__dE, this, S, W);
          W.addListener(h, V);
          W.addListener(f, V);
          W.addListener(r, V);
          W.addListener(u, V);
        };
        W.send();
        W = null;
        V = null;
      },
      setBaseUrl : function(X){

        this.__dg = X;
      },
      __dC : function(Y){

        return Y ? Y : document.location.protocol + d + document.location.hostname + (document.location.port.length ? c + document.location.port : l);
      },
      __dD : function(ba, bb){

        this.__dH(bb, ba);
      },
      __dE : function(bc, bd){

        bc(5, null);
      },
      __dF : function(be, bf){

        this.__dH(bf.getResponse(), be);
      },
      __dG : function(bg, bh, bi, bj){

        if(bi == 0 && (!bj || !bh || bh.length != bj.length))bi = 3;
        if(bi){

          for(var i = 0;i < bh.length;i++)bh[i] ? bh[i](bi) : null;
          bg(bi, bi);
          return;
        };
        var bl = 0;
        var bk = 0;
        for(var i = 0;i < bj.length;i++){

          this.__dH(bj[i], bh[i]);
          if(bj[i])bl = bj[i];
          if(typeof bj[i].error != n)bk++;
        };
        bg(0, bl, bk);
      },
      __dH : function(bm, bn){

        if(bm && typeof bm.error != n && bm.error != 0)bn(bm.error, null); else if(bm)bn(0, bm); else bn(3, null);;
      }
    }
  });
})();
(function(){

  var r = "&",q = "onmessage",p = "none",o = "{id: 0, source:'",m = "src",l = "onload",k = "message",j = "wialon.core.PostMessage",h = "load",g = "iframe",c = "sid",f = "'}",e = "&sid=",b = "=",a = "object",d = "";
  qx.Class.define(j, {
    extend : qx.core.Object,
    construct : function(s, t){

      qx.core.Object.call(this);
      this._url = s;
      this._id = this._url;
      this._io = null;
      this._callbacks = {
      };
    },
    members : {
      send : function(u, v, w, x, y){

        if(!this._io){

          this._io = document.createElement(g);
          this._io.style.display = p;
          if(window.attachEvent)this._io.attachEvent(l, qx.lang.Function.bind(this.__dJ, this)); else this._io.addEventListener(h, qx.lang.Function.bind(this.__dJ, this), false);
          this._io.setAttribute(m, this._url);
          document.body.appendChild(this._io);
          if(window.addEventListener)window.addEventListener(k, qx.lang.Function.bind(this.__dI, this), false); else window.attachEvent(q, qx.lang.Function.bind(this.__dI, this));
        };
        var A = {
          id : ++this._counter,
          url : u,
          params : this.__dL(v),
          source : this._id
        };
        var z = this._io.contentWindow;
        if(z){

          var B = wialon.util.Json.stringify(A);
          this._callbacks[this._counter] = [w, x, B, 0, y];
          if(y)this._callbacks[this._counter].push(setTimeout(qx.lang.Function.bind(this.__dK, this, this._counter), y * 1000));
          if(this._frameReady)z.postMessage(B, this._url); else this._requests.push(B);
        } else x();
      },
      _url : d,
      _io : null,
      _id : 0,
      _callbacks : {
      },
      _requests : [],
      _frameReady : false,
      _timeout : 0,
      _counter : 0,
      __dI : function(event){

        var D = wialon.util.Json.parse(event.data);
        if(D.source != this._id)return;
        if(!D.id){

          this._frameReady = true;
          this.__dJ();
          return;
        };
        var C = this._callbacks[D.id];
        if(C){

          if(D && D.text && D.text.error && D.text.error == 1003 && C[3] < 3){

            C[3]++;
            if(C[4] && C[5]){

              clearTimeout(C[5]);
              C[5] = setTimeout(qx.lang.Function.bind(this.__dK, this, this._counter), C[4] * 1000);
            };
            if(this._io.contentWindow){

              setTimeout(qx.lang.Function.bind(function(E){

                this._io.contentWindow.postMessage(E, this._url);
              }, this, C[2]), Math.random() * 1000);
              return;
            };
          };
          if(C[D.error])C[D.error](D.text);
          if(C[4] && C[5])clearTimeout(C[5]);
          delete this._callbacks[D.id];
        };
      },
      __dJ : function(){

        if(!this._frameReady){

          this._io.contentWindow.postMessage(o + this._id + f, this._url);
          return;
        };
        for(var i = 0;i < this._requests.length;i++)this._io.contentWindow.postMessage(this._requests[i], this._url);
        this._requests = [];
      },
      __dK : function(F){

        var G = this._callbacks[F];
        if(G){

          if(G[1])G[1]();
          delete this._callbacks[F];
        };
      },
      __dL : function(H){

        var I = [];
        var J = false;
        if(typeof H == a){

          for(var n in H){

            if(typeof H[n] == a)I.push(n + b + encodeURIComponent(wialon.util.Json.stringify(H[n]))); else I.push(n + b + encodeURIComponent(H[n]));
            if(n == c)J = true;
          };
          return I.join(r) + (!J ? e + wialon.core.Session.getInstance().getId() : d);
        };
        return !J ? e + wialon.core.Session.getInstance().getId() : d;
      }
    }
  });
})();
(function(){

  var q = '\\u00',p = "array",o = '\\\\',n = '\\f',m = ']',k = "static",j = "wialon.util.Json",h = "null",g = '\\"',d = '(',I = ':',H = '\\t',G = "number",F = '\\r',E = '{',D = '\\b',C = '[',B = ')',A = '\\n',z = '}',w = '',y = ',',t = "",u = 'null',r = 'string',s = '"';
  qx.Class.define(j, {
    type : k,
    statics : {
      stringify : function(J){

        var f = null;
        if(isNaN(J))f = this.__dM[typeof J]; else if(J instanceof Array)f = this.__dM[p]; else f = this.__dM[G];;
        if(f)return f.apply(this, [J]);
        return t;
      },
      parse : function(K, L){

        if(L === undefined)L = false;
        if(L && !/^("(\\.|[^"\\\n\r])*?"|[,:{}\[\]0-9.\-+Eaeflnr-u \n\r\t])+?$/.test(K))return undefined;
        if(!K || K == t)return {
        };
        var M = null;
        try{

          M = eval(d + K + B);
        } catch(e) {

          try{

            M = eval(s + K + s);
          } catch(e) {

            return null;
          };
        };
        return M;
      },
      compareObjects : function(N, O){

        if((N == null && O != null) || (O == null && N != null))return false;
        return this.stringify(N) == this.stringify(O);
      },
      __dM : {
        'array' : function(x){

          var a = [C],b,f,i,l = x.length,v;
          for(i = 0;i < l;i += 1){

            v = x[i];
            f = this.__dM[typeof v];
            if(f){

              v = f.apply(this, [v]);
              if(typeof v == r){

                if(b){

                  a[a.length] = y;
                };
                a[a.length] = v;
                b = true;
              };
            };
          };
          a[a.length] = m;
          return a.join(w);
        },
        'boolean' : function(x){

          return String(x);
        },
        'null' : function(x){

          return h;
        },
        'number' : function(x){

          return isFinite(x) ? String(x) : u;
        },
        'object' : function(x){

          if(x){

            if(x instanceof Array){

              return this.__dM.array.apply(this, [x]);
            };
            var a = [E],b,f,i,v;
            for(i in x){

              v = x[i];
              f = this.__dM[typeof v];
              if(f){

                v = f.apply(this, [v]);
                if(typeof v == r){

                  if(b){

                    a[a.length] = y;
                  };
                  a.push(this.__dM.string.apply(this, [i]), I, v);
                  b = true;
                };
              };
            };
            a[a.length] = z;
            return a.join(w);
          };
          return u;
        },
        'string' : function(x){

          if(/["\\\x00-\x1f]/.test(x)){

            x = x.replace(/([\x00-\x1f\\"])/g, function(a, b){

              var P = {
                '\b' : D,
                '\t' : H,
                '\n' : A,
                '\f' : n,
                '\r' : F,
                '"' : g,
                '\\' : o
              };
              var c = P[b];
              if(c){

                return c;
              };
              c = b.charCodeAt();
              return q + Math.floor(c / 16).toString(16) + (c % 16).toString(16);
            });
          };
          return s + x + s;
        }
      }
    }
  });
})();
(function(){

  var p = "\\+",o = "\\)",n = "static",m = '^',l = "resource/get_zones_by_point",j = "\\^",h = "\\[",g = "wialon.util.Helper",f = "\\]",e = "\\.",D = '$',C = "\\(",B = "\\{",A = "\\\\",z = "\\}",y = "?",w = ".*",v = "\\$",u = ".",t = "undefined",q = "function",s = "*";
  qx.Class.define(g, {
    type : n,
    statics : {
      filterItems : function(E, F){

        if(!E)return null;
        var H = new Array;
        for(var i = 0;i < E.length;i++){

          var G = E[i];
          if(!G || wialon.util.Number.and(G.getUserAccess(), F) != F)continue;
          H.push(G);
        };
        return H;
      },
      searchObject : function(I, J, K){

        if(!I || !J || !K)return null;
        for(var L in I){

          if(typeof I[L][J] == t || I[L][J] != K)continue;
          return I[L];
        };
        return null;
      },
      sortItems : function(M, N){

        if(!M)return null;
        if(typeof N != q)N = function(a){

          return a.getName();
        };
        var O = function(a, b){

          var R = function(S){

            return S.match(/\d+|\D+/g);
          };
          var P = R(N(a).toLowerCase());
          var Q = R(N(b).toLowerCase());
          if(!P || !Q || !P.length || !Q.length){

            if(!P || !P.length)return -1;
            if(!Q || !Q.length)return 1;
            return 0;
          };
          for(var x = 0;P[x] && Q[x];x++){

            if(P[x] !== Q[x]){

              var c = Number(P[x]),d = Number(Q[x]);
              if(c == P[x] && d == Q[x])return c - d; else return (P[x] > Q[x]) ? 1 : -1;
            };
          };
          return P.length - Q.length;
        };
        return M.sort(O);
      },
      getZonesInPoint : function(T, U){

        wialon.core.Remote.getInstance().remoteCall(l, {
          spec : T
        }, wialon.util.Helper.wrapCallback(U));
      },
      wildcardCompare : function(V, W, X){

        if(V == null || W == null)return null;
        if(X && W.indexOf(s) == -1 && W.indexOf(y) == -1)W = s + W + s;
        var Y = W.toLowerCase();
        Y = Y.replace(/\\/g, A);
        Y = Y.replace(/\./g, e);
        Y = Y.replace(/\?/g, u);
        Y = Y.replace(/\*/g, w);
        Y = Y.replace(/\^/g, j);
        Y = Y.replace(/\$/g, v);
        Y = Y.replace(/\+/g, p);
        Y = Y.replace(/\(/g, C);
        Y = Y.replace(/\)/g, o);
        Y = Y.replace(/\[/g, h);
        Y = Y.replace(/\]/g, f);
        Y = Y.replace(/\{/g, B);
        Y = Y.replace(/\}/g, z);
        var ba = V.toLowerCase().match(new RegExp(m + Y + D));
        return ba != null ? true : false;
      },
      wrapCallback : function(bc){

        return qx.lang.Function.bind(this.__cq, this, bc);
      },
      countProps : function(bd){

        var be = 0;
        for(var k in bd){

          if(bd.hasOwnProperty(k)){

            be++;
          };
        };
        return be;
      },
      objectsEqual : function(bf, bg){

        if(typeof (bf) !== typeof (bg)){

          return false;
        };
        if(typeof (bf) === q){

          return bf.toString() === bg.toString();
        };
        if(bf instanceof Object && bg instanceof Object){

          if(this.countProps(bf) !== this.countProps(bg)){

            return false;
          };
          var r = true;
          for(var k in bf){

            r = this.objectsEqual(bf[k], bg[k]);
            if(!r){

              return false;
            };
          };
          return true;
        } else {

          return bf === bg;
        };
      },
      __cq : function(){

        if(!arguments.length)return;
        var bh = arguments[0];
        if(!bh)return;
        var bi = Array.prototype.slice.call(arguments, 1);
        if(!(bh instanceof Array))bh = [bh];
        for(var i = 0;i < bh.length;i++)bh[i].apply(this, bi);
      }
    }
  });
})();
(function(){

  var d = "number",c = "wialon.util.Number",b = "static",a = "string";
  qx.Class.define(c, {
    type : b,
    statics : {
      or : function(e){

        var g = this.__dN();
        for(var i = 0;i < arguments.length;i++){

          var f = this.__dN(arguments[i]);
          g[0] = (g[0] | f[0]) >>> 0;
          g[1] = (g[1] | f[1]) >>> 0;
        };
        return g[0] * 0x100000000 + g[1];
      },
      xor : function(h){

        var k = this.__dN();
        for(var i = 0;i < arguments.length;i++){

          var j = this.__dN(arguments[i]);
          k[0] = (k[0] ^ j[0]) >>> 0;
          k[1] = (k[1] ^ j[1]) >>> 0;
        };
        return k[0] * 0x100000000 + k[1];
      },
      and : function(l){

        var n = [0xFFFFFFFF, 0xFFFFFFFF];
        for(var i = 0;i < arguments.length;i++){

          var m = this.__dN(arguments[i]);
          n[0] = (n[0] & m[0]) >>> 0;
          n[1] = (n[1] & m[1]) >>> 0;
        };
        return n[0] * 0x100000000 + n[1];
      },
      not : function(o){

        var p = this.__dN(o);
        p[0] = ((~p[0]) & 0x1FFFFF) >>> 0;
        p[1] = (~p[1]) >>> 0;
        return p[0] * 0x100000000 + p[1];
      },
      exclude : function(q){

        if(!arguments.length)return 0;
        var s = this.__dN(arguments[0]);
        for(var i = 1;i < arguments.length;i++){

          var r = this.__dN(this.not(arguments[i]));
          s[0] = (s[0] & r[0]) >>> 0;
          s[1] = (s[1] & r[1]) >>> 0;
        };
        return s[0] * 0x100000000 + s[1];
      },
      umax : function(){

        return 0x1FFFFFFFFFFFFF;
      },
      __dN : function(t){

        var v = [0, 0];
        if(typeof t == d){

          if(t == -1)return [0x1FFFFF, 0xFFFFFFFF];
          t = t.toString(16);
        };
        if(typeof t == a && t.length && t.length <= 16){

          var u = [0, 0];
          for(var i = t.length;i > 0;i--)v[t.length - i < 8 ? 1 : 0] |= parseInt(t[i - 1], 16) << (((t.length - i) * 4) % 32);
        };
        v[0] = v[0] >>> 0;
        v[1] = v[1] >>> 0;
        return v;
      }
    }
  });
})();
(function(){

  var j = "loadEnd",i = "qx.io.request.AbstractRequest",h = "changePhase",g = "Open low-level request with method: ",f = "sent",e = "Abort request",d = "'",c = "qx.io.request.authentication.IAuthentication",b = "error",a = "Send low-level request",L = "loading",K = ", url: ",J = "String",I = "",H = "opened",G = "Response is of type: '",F = "POST",E = "success",D = "Request completed with HTTP status: ",C = "Fire readyState: ",q = "statusError",r = "readyStateChange",o = "abstract",p = "unsent",m = "changeResponse",n = "Number",k = "Content-Type",l = "timeout",s = "undefined",t = ", async: ",w = "qx.event.type.Data",v = "load",y = "abort",x = "Abstract method call",A = "GET",z = "fail",u = "qx.debug.io",B = "qx.event.type.Event";
  qx.Class.define(i, {
    type : o,
    extend : qx.core.Object,
    construct : function(M){

      qx.core.Object.call(this);
      if(M !== undefined){

        this.setUrl(M);
      };
      this.__dO = {
      };
      var N = this._transport = this._createTransport();
      this._setPhase(p);
      this.__dP = qx.lang.Function.bind(this._onReadyStateChange, this);
      this.__dQ = qx.lang.Function.bind(this._onLoad, this);
      this.__dR = qx.lang.Function.bind(this._onLoadEnd, this);
      this.__dS = qx.lang.Function.bind(this._onAbort, this);
      this.__dT = qx.lang.Function.bind(this._onTimeout, this);
      this.__dU = qx.lang.Function.bind(this._onError, this);
      N.onreadystatechange = this.__dP;
      N.onload = this.__dQ;
      N.onloadend = this.__dR;
      N.onabort = this.__dS;
      N.ontimeout = this.__dT;
      N.onerror = this.__dU;
    },
    events : {
      "readyStateChange" : B,
      "success" : B,
      "load" : B,
      "loadEnd" : B,
      "abort" : B,
      "timeout" : B,
      "error" : B,
      "statusError" : B,
      "fail" : B,
      "changeResponse" : w,
      "changePhase" : w
    },
    properties : {
      url : {
        check : J
      },
      timeout : {
        check : n,
        nullable : true,
        init : 0
      },
      requestData : {
        check : function(O){

          return qx.lang.Type.isString(O) || qx.Class.isSubClassOf(O.constructor, qx.core.Object) || qx.lang.Type.isObject(O);
        },
        nullable : true
      },
      authentication : {
        check : c,
        nullable : true
      }
    },
    members : {
      __dP : null,
      __dQ : null,
      __dR : null,
      __dS : null,
      __dT : null,
      __dU : null,
      __dV : null,
      __dW : null,
      __dX : null,
      __dO : null,
      __dY : null,
      _transport : null,
      _createTransport : function(){

        throw new Error(x);
      },
      _getConfiguredUrl : function(){
      },
      _getConfiguredRequestHeaders : function(){
      },
      _getParsedResponse : function(){

        throw new Error(x);
      },
      _getMethod : function(){

        return A;
      },
      _isAsync : function(){

        return true;
      },
      send : function(){

        var T = this._transport,P,S,Q,R;
        P = this._getConfiguredUrl();
        if(/\#/.test(P)){

          P = P.replace(/\#.*/, I);
        };
        T.timeout = this.getTimeout();
        S = this._getMethod();
        Q = this._isAsync();
        if(qx.core.Environment.get(u)){

          this.debug(g + S + K + P + t + Q);
        };
        T.open(S, P, Q);
        this._setPhase(H);
        R = this._serializeData(this.getRequestData());
        this._setRequestHeaders();
        if(qx.core.Environment.get(u)){

          this.debug(a);
        };
        S == A ? T.send() : T.send(R);
        this._setPhase(f);
      },
      abort : function(){

        if(qx.core.Environment.get(u)){

          this.debug(e);
        };
        this.__dW = true;
        this.__dX = y;
        this._transport.abort();
      },
      _setRequestHeaders : function(){

        var W = this._transport,U = this._getAllRequestHeaders();
        for(var V in U){

          W.setRequestHeader(V, U[V]);
        };
      },
      _getAllRequestHeaders : function(){

        var X = qx.lang.Object.merge({
        }, this._getConfiguredRequestHeaders(), this.__ea(), this.__dY, this.__dO);
        return X;
      },
      __ea : function(){

        var ba = this.getAuthentication(),Y = {
        };
        if(ba){

          ba.getAuthHeaders().forEach(function(bb){

            Y[bb.key] = bb.value;
          });
          return Y;
        };
      },
      setRequestHeader : function(bc, bd){

        this.__dO[bc] = bd;
      },
      getRequestHeader : function(be){

        return this.__dO[be];
      },
      removeRequestHeader : function(bf){

        if(this.__dO[bf]){

          delete this.__dO[bf];
        };
      },
      getTransport : function(){

        return this._transport;
      },
      getReadyState : function(){

        return this._transport.readyState;
      },
      getPhase : function(){

        return this.__dX;
      },
      getStatus : function(){

        return this._transport.status;
      },
      getStatusText : function(){

        return this._transport.statusText;
      },
      getResponseText : function(){

        return this._transport.responseText;
      },
      getAllResponseHeaders : function(){

        return this._transport.getAllResponseHeaders();
      },
      getResponseHeader : function(bg){

        return this._transport.getResponseHeader(bg);
      },
      getResponseContentType : function(){

        return this.getResponseHeader(k);
      },
      isDone : function(){

        return this.getReadyState() === 4;
      },
      getResponse : function(){

        return this.__dV;
      },
      _setResponse : function(bh){

        var bi = bh;
        if(this.__dV !== bh){

          this.__dV = bh;
          this.fireEvent(m, qx.event.type.Data, [this.__dV, bi]);
        };
      },
      _onReadyStateChange : function(){

        var bj = this.getReadyState();
        if(qx.core.Environment.get(u)){

          this.debug(C + bj);
        };
        this.fireEvent(r);
        if(this.__dW){

          return;
        };
        if(bj === 3){

          this._setPhase(L);
        };
        if(this.isDone()){

          this.__eb();
        };
      },
      __eb : function(){

        var bk;
        if(qx.core.Environment.get(u)){

          this.debug(D + this.getStatus());
        };
        this._setPhase(v);
        if(qx.util.Request.isSuccessful(this.getStatus())){

          if(qx.core.Environment.get(u)){

            this.debug(G + this.getResponseContentType() + d);
          };
          bk = this._getParsedResponse();
          this._setResponse(bk);
          this._fireStatefulEvent(E);
        } else {

          if(this.getStatus() !== 0){

            this._fireStatefulEvent(q);
            this.fireEvent(z);
          };
        };
      },
      _onLoad : function(){

        this.fireEvent(v);
      },
      _onLoadEnd : function(){

        this.fireEvent(j);
      },
      _onAbort : function(){

        this._fireStatefulEvent(y);
      },
      _onTimeout : function(){

        this._fireStatefulEvent(l);
        this.fireEvent(z);
      },
      _onError : function(){

        this.fireEvent(b);
        this.fireEvent(z);
      },
      _fireStatefulEvent : function(bl){

        {
        };
        this._setPhase(bl);
        this.fireEvent(bl);
      },
      _setPhase : function(bm){

        var bn = this.__dX;
        {
        };
        this.__dX = bm;
        this.fireDataEvent(h, bm, bn);
      },
      _serializeData : function(bo){

        var bp = typeof this.getMethod !== s && this.getMethod() == F;
        if(!bo){

          return;
        };
        if(qx.lang.Type.isString(bo)){

          return bo;
        };
        if(qx.Class.isSubClassOf(bo.constructor, qx.core.Object)){

          return qx.util.Serializer.toUriParameter(bo);
        };
        if(qx.lang.Type.isObject(bo)){

          return qx.lang.Object.toUriParameter(bo, bp);
        };
      }
    },
    environment : {
      "qx.debug.io" : false
    },
    destruct : function(){

      var br = this._transport,bq = function(){
      };
      if(this._transport){

        br.onreadystatechange = br.onload = br.onloadend = br.onabort = br.ontimeout = br.onerror = bq;
        br.dispose();
      };
    }
  });
})();
(function(){

  var d = "&",c = "qx.lang.Object",b = "=",a = "+";
  qx.Bootstrap.define(c, {
    statics : {
      empty : function(e){

        {
        };
        for(var f in e){

          if(e.hasOwnProperty(f)){

            delete e[f];
          };
        };
      },
      isEmpty : function(g){

        {
        };
        for(var h in g){

          return false;
        };
        return true;
      },
      hasMinLength : function(j, k){

        {
        };
        if(k <= 0){

          return true;
        };
        var length = 0;
        for(var m in j){

          if((++length) >= k){

            return true;
          };
        };
        return false;
      },
      getLength : qx.Bootstrap.objectGetLength,
      getKeys : qx.Bootstrap.getKeys,
      getKeysAsString : qx.Bootstrap.getKeysAsString,
      getValues : function(n){

        {
        };
        var p = [];
        var o = this.getKeys(n);
        for(var i = 0,l = o.length;i < l;i++){

          p.push(n[o[i]]);
        };
        return p;
      },
      mergeWith : qx.Bootstrap.objectMergeWith,
      carefullyMergeWith : function(q, r){

        {
        };
        return qx.lang.Object.mergeWith(q, r, false);
      },
      merge : function(s, t){

        {
        };
        var u = arguments.length;
        for(var i = 1;i < u;i++){

          qx.lang.Object.mergeWith(s, arguments[i]);
        };
        return s;
      },
      clone : function(v, w){

        if(qx.lang.Type.isObject(v)){

          var x = {
          };
          for(var y in v){

            if(w){

              x[y] = qx.lang.Object.clone(v[y], w);
            } else {

              x[y] = v[y];
            };
          };
          return x;
        } else if(qx.lang.Type.isArray(v)){

          var x = [];
          for(var i = 0;i < v.length;i++){

            if(w){

              x[i] = qx.lang.Object.clone(v[i]);
            } else {

              x[i] = v[i];
            };
          };
          return x;
        };
        return v;
      },
      invert : function(z){

        {
        };
        var A = {
        };
        for(var B in z){

          A[z[B].toString()] = B;
        };
        return A;
      },
      getKeyFromValue : function(C, D){

        {
        };
        for(var E in C){

          if(C.hasOwnProperty(E) && C[E] === D){

            return E;
          };
        };
        return null;
      },
      contains : function(F, G){

        {
        };
        return this.getKeyFromValue(F, G) !== null;
      },
      select : function(H, I){

        {
        };
        return I[H];
      },
      fromArray : function(J){

        {
        };
        var K = {
        };
        for(var i = 0,l = J.length;i < l;i++){

          {
          };
          K[J[i].toString()] = true;
        };
        return K;
      },
      toUriParameter : function(L, M){

        var P,N = [];
        for(P in L){

          if(L.hasOwnProperty(P)){

            var O = L[P];
            if(O instanceof Array){

              for(var i = 0;i < O.length;i++){

                this.__ec(P, O[i], N, M);
              };
            } else {

              this.__ec(P, O, N, M);
            };
          };
        };
        return N.join(d);
      },
      __ec : function(Q, R, S, T){

        var U = window.encodeURIComponent;
        if(T){

          S.push(U(Q).replace(/%20/g, a) + b + U(R).replace(/%20/g, a));
        } else {

          S.push(U(Q) + b + U(R));
        };
      }
    }
  });
})();
(function(){

  var b = "//",a = "qx.util.Request";
  qx.Bootstrap.define(a, {
    statics : {
      isCrossDomain : function(c){

        var e = qx.util.Uri.parseUri(c),location = window.location;
        if(!location){

          return false;
        };
        var d = location.protocol;
        if(!(c.indexOf(b) !== -1)){

          return false;
        };
        if(d.substr(0, d.length - 1) == e.protocol && location.host === e.host && location.port === e.port){

          return false;
        };
        return true;
      },
      isSuccessful : function(status){

        return (status >= 200 && status < 300 || status === 304);
      },
      methodAllowsRequestBody : function(f){

        return !((/^(GET)|(HEAD)$/).test(f));
      }
    }
  });
})();
(function(){

  var k = "file",j = "strict",h = "anchor",g = "div",f = "query",e = "source",d = "password",c = "host",b = "protocol",a = "user",A = "directory",z = "loose",y = "relative",x = "queryKey",w = "qx.util.Uri",v = "",u = "path",t = "authority",s = '">0</a>',r = "&",p = "port",q = '<a href="',l = "userInfo",n = "?";
  qx.Bootstrap.define(w, {
    statics : {
      parseUri : function(B, C){

        var D = {
          key : [e, b, t, l, a, d, c, p, y, u, A, k, f, h],
          q : {
            name : x,
            parser : /(?:^|&)([^&=]*)=?([^&]*)/g
          },
          parser : {
            strict : /^(?:([^:\/?#]+):)?(?:\/\/((?:(([^:@]*)(?::([^:@]*))?)?@)?([^:\/?#]*)(?::(\d*))?))?((((?:[^?#\/]*\/)*)([^?#]*))(?:\?([^#]*))?(?:#(.*))?)/,
            loose : /^(?:(?![^:@]+:[^:@\/]*@)([^:\/?#.]+):)?(?:\/\/)?((?:(([^:@]*)(?::([^:@]*))?)?@)?([^:\/?#]*)(?::(\d*))?)(((\/(?:[^?#](?![^?#\/]*\.[^?#\/.]+(?:[?#]|$)))*\/?)?([^?#\/]*))(?:\?([^#]*))?(?:#(.*))?)/
          }
        };
        var o = D,m = D.parser[C ? j : z].exec(B),E = {
        },i = 14;
        while(i--){

          E[o.key[i]] = m[i] || v;
        };
        E[o.q.name] = {
        };
        E[o.key[12]].replace(o.q.parser, function(F, G, H){

          if(G){

            E[o.q.name][G] = H;
          };
        });
        return E;
      },
      appendParamsToUrl : function(I, J){

        if(J === undefined){

          return I;
        };
        {
        };
        if(qx.lang.Type.isObject(J)){

          J = qx.lang.Object.toUriParameter(J);
        };
        if(!J){

          return I;
        };
        return I += (/\?/).test(I) ? r + J : n + J;
      },
      getAbsolute : function(K){

        var L = document.createElement(g);
        L.innerHTML = q + K + s;
        return L.firstChild.href;
      }
    }
  });
})();
(function(){

  var u = "&",t = "null",s = '\\t',r = '\\"',q = '\\n',p = '\\b',o = "=",n = "qx.util.Serializer",m = '\\r',l = '\\\\',d = '\\f',k = "}",g = "]",c = '":',b = "",f = "get",e = "{",h = "[",a = ",",j = '"';
  qx.Class.define(n, {
    statics : {
      toUriParameter : function(v, w, x){

        var B = b;
        var C = qx.util.PropertyUtil.getAllProperties(v.constructor);
        for(var name in C){

          if(C[name].group != undefined){

            continue;
          };
          var y = v[f + qx.lang.String.firstUp(name)]();
          if(qx.lang.Type.isArray(y)){

            var A = qx.data && qx.data.IListData && qx.Class.hasInterface(y && y.constructor, qx.data.IListData);
            for(var i = 0;i < y.length;i++){

              var z = A ? y.getItem(i) : y[i];
              B += this.__ec(name, z, w);
            };
          } else if(qx.lang.Type.isDate(y) && x != null){

            B += this.__ec(name, x.format(y), w);
          } else {

            B += this.__ec(name, y, w);
          };
        };
        return B.substring(0, B.length - 1);
      },
      __ec : function(name, D, E){

        if(D instanceof qx.core.Object && E != null){

          var F = encodeURIComponent(E(D));
          if(F === undefined){

            var F = encodeURIComponent(D);
          };
        } else {

          var F = encodeURIComponent(D);
        };
        return encodeURIComponent(name) + o + F + u;
      },
      toNativeObject : function(G, H, I){

        var L;
        if(G == null){

          return null;
        };
        if(qx.data && qx.data.IListData && qx.Class.hasInterface(G.constructor, qx.data.IListData)){

          L = [];
          for(var i = 0;i < G.getLength();i++){

            L.push(qx.util.Serializer.toNativeObject(G.getItem(i), H, I));
          };
          return L;
        };
        if(qx.lang.Type.isArray(G)){

          L = [];
          for(var i = 0;i < G.length;i++){

            L.push(qx.util.Serializer.toNativeObject(G[i], H, I));
          };
          return L;
        };
        if(G instanceof qx.core.Object){

          if(H != null){

            var M = H(G);
            if(M != undefined){

              return M;
            };
          };
          L = {
          };
          var N = qx.util.PropertyUtil.getAllProperties(G.constructor);
          for(var name in N){

            if(N[name].group != undefined){

              continue;
            };
            var K = G[f + qx.lang.String.firstUp(name)]();
            L[name] = qx.util.Serializer.toNativeObject(K, H, I);
          };
          return L;
        };
        if(qx.lang.Type.isDate(G) && I != null){

          return I.format(G);
        };
        if(qx.locale && qx.locale.LocalizedString && G instanceof qx.locale.LocalizedString){

          return G.toString();
        };
        if(qx.lang.Type.isObject(G)){

          L = {
          };
          for(var J in G){

            L[J] = qx.util.Serializer.toNativeObject(G[J], H, I);
          };
          return L;
        };
        return G;
      },
      toJson : function(O, P, Q){

        var T = b;
        if(O == null){

          return t;
        };
        if(qx.data && qx.data.IListData && qx.Class.hasInterface(O.constructor, qx.data.IListData)){

          T += h;
          for(var i = 0;i < O.getLength();i++){

            T += qx.util.Serializer.toJson(O.getItem(i), P, Q) + a;
          };
          if(T != h){

            T = T.substring(0, T.length - 1);
          };
          return T + g;
        };
        if(qx.lang.Type.isArray(O)){

          T += h;
          for(var i = 0;i < O.length;i++){

            T += qx.util.Serializer.toJson(O[i], P, Q) + a;
          };
          if(T != h){

            T = T.substring(0, T.length - 1);
          };
          return T + g;
        };
        if(O instanceof qx.core.Object){

          if(P != null){

            var U = P(O);
            if(U != undefined){

              return j + U + j;
            };
          };
          T += e;
          var V = qx.util.PropertyUtil.getAllProperties(O.constructor);
          for(var name in V){

            if(V[name].group != undefined){

              continue;
            };
            var S = O[f + qx.lang.String.firstUp(name)]();
            T += j + name + c + qx.util.Serializer.toJson(S, P, Q) + a;
          };
          if(T != e){

            T = T.substring(0, T.length - 1);
          };
          return T + k;
        };
        if(O instanceof qx.locale.LocalizedString){

          O = O.toString();
        };
        if(qx.lang.Type.isDate(O) && Q != null){

          return j + Q.format(O) + j;
        };
        if(qx.lang.Type.isObject(O)){

          T += e;
          for(var R in O){

            T += j + R + c + qx.util.Serializer.toJson(O[R], P, Q) + a;
          };
          if(T != e){

            T = T.substring(0, T.length - 1);
          };
          return T + k;
        };
        if(qx.lang.Type.isString(O)){

          O = O.replace(/([\\])/g, l);
          O = O.replace(/(["])/g, r);
          O = O.replace(/([\r])/g, m);
          O = O.replace(/([\f])/g, d);
          O = O.replace(/([\n])/g, q);
          O = O.replace(/([\t])/g, s);
          O = O.replace(/([\b])/g, p);
          return j + O + j;
        };
        if(qx.lang.Type.isDate(O) || qx.lang.Type.isRegExp(O)){

          return j + O + j;
        };
        return O + b;
      }
    }
  });
})();
(function(){

  var d = "qx.util.PropertyUtil",c = "$$theme_",b = "$$user_",a = "$$init_";
  qx.Class.define(d, {
    statics : {
      getProperties : function(e){

        return e.$$properties;
      },
      getAllProperties : function(f){

        var i = {
        };
        var j = f;
        while(j != qx.core.Object){

          var h = this.getProperties(j);
          for(var g in h){

            i[g] = h[g];
          };
          j = j.superclass;
        };
        return i;
      },
      getUserValue : function(k, l){

        return k[b + l];
      },
      setUserValue : function(m, n, o){

        m[b + n] = o;
      },
      deleteUserValue : function(p, q){

        delete (p[b + q]);
      },
      getInitValue : function(r, s){

        return r[a + s];
      },
      setInitValue : function(t, u, v){

        t[a + u] = v;
      },
      deleteInitValue : function(w, x){

        delete (w[a + x]);
      },
      getThemeValue : function(y, z){

        return y[c + z];
      },
      setThemeValue : function(A, B, C){

        A[c + B] = C;
      },
      deleteThemeValue : function(D, E){

        delete (D[c + E]);
      },
      setThemed : function(F, G, H){

        var I = qx.core.Property.$$method.setThemed;
        F[I[G]](H);
      },
      resetThemed : function(J, K){

        var L = qx.core.Property.$$method.resetThemed;
        J[L[K]]();
      }
    }
  });
})();
(function(){

  var c = "qx.io.request.Jsonp",b = "Boolean",a = "qx.event.type.Event";
  qx.Class.define(c, {
    extend : qx.io.request.AbstractRequest,
    events : {
      "success" : a,
      "load" : a,
      "statusError" : a
    },
    properties : {
      cache : {
        check : b,
        init : true
      }
    },
    members : {
      _createTransport : function(){

        return new qx.bom.request.Jsonp();
      },
      _getConfiguredUrl : function(){

        var d = this.getUrl(),e;
        if(this.getRequestData()){

          e = this._serializeData(this.getRequestData());
          d = qx.util.Uri.appendParamsToUrl(d, e);
        };
        if(!this.getCache()){

          d = qx.util.Uri.appendParamsToUrl(d, {
            nocache : new Date().valueOf()
          });
        };
        return d;
      },
      _getParsedResponse : function(){

        return this._transport.responseJson;
      },
      setCallbackParam : function(f){

        this._transport.setCallbackParam(f);
      },
      setCallbackName : function(name){

        this._transport.setCallbackName(name);
      }
    }
  });
})();
(function(){

  var j = "url: ",i = "Invalid state",h = "head",g = "script",f = "load",e = "Unknown response headers",d = "abort",c = "Received native readyState: loaded",b = "readystatechange",a = "Response header cannot be determined for ",D = "opera",C = "unknown",B = "Open native request with ",A = "Response headers cannot be determined for",z = "Detected error",y = "Send native request",x = "on",w = "timeout",v = "Unknown environment key at this phase",u = "Received native load",q = "error",r = "qx.debug",o = "requests made with script transport.",p = "loadend",m = "",n = "mshtml",k = "browser.documentmode",l = "engine.name",s = "qx.debug.io",t = "qx.bom.request.Script";
  qx.Bootstrap.define(t, {
    construct : function(){

      this.__el();
      this.__ed = qx.Bootstrap.bind(this._onNativeLoad, this);
      this.__ee = qx.Bootstrap.bind(this._onNativeError, this);
      this.__dT = qx.Bootstrap.bind(this._onTimeout, this);
      this.__ef = document.head || document.getElementsByTagName(h)[0] || document.documentElement;
      this._emitter = new qx.event.Emitter();
      this.timeout = this.__en() ? 0 : 15000;
    },
    events : {
      "readystatechange" : t,
      "error" : t,
      "loadend" : t,
      "timeout" : t,
      "abort" : t,
      "load" : t
    },
    members : {
      readyState : null,
      status : null,
      statusText : null,
      timeout : null,
      __eg : null,
      on : function(name, E, F){

        this._emitter.on(name, E, F);
        return this;
      },
      open : function(G, H){

        if(this.__ej){

          return;
        };
        this.__el();
        this.__dW = null;
        this.__eh = H;
        if(this.__eq(s)){

          qx.Bootstrap.debug(qx.bom.request.Script, B + j + H);
        };
        this._readyStateChange(1);
      },
      setRequestHeader : function(I, J){

        if(this.__ej){

          return;
        };
        var K = {
        };
        if(this.readyState !== 1){

          throw new Error(i);
        };
        K[I] = J;
        this.__eh = qx.util.Uri.appendParamsToUrl(this.__eh, K);
        return this;
      },
      send : function(){

        if(this.__ej){

          return;
        };
        var N = this.__eo(),L = this.__ef,M = this;
        if(this.timeout > 0){

          this.__ei = window.setTimeout(this.__dT, this.timeout);
        };
        if(this.__eq(s)){

          qx.Bootstrap.debug(qx.bom.request.Script, y);
        };
        L.insertBefore(N, L.firstChild);
        window.setTimeout(function(){

          M._readyStateChange(2);
          M._readyStateChange(3);
        });
        return this;
      },
      abort : function(){

        if(this.__ej){

          return;
        };
        this.__dW = true;
        this.__ep();
        this._emit(d);
        return this;
      },
      _emit : function(event){

        this[x + event]();
        this._emitter.emit(event, this);
      },
      onreadystatechange : function(){
      },
      onload : function(){
      },
      onloadend : function(){
      },
      onerror : function(){
      },
      onabort : function(){
      },
      ontimeout : function(){
      },
      getResponseHeader : function(O){

        if(this.__ej){

          return;
        };
        if(this.__eq(r)){

          qx.Bootstrap.debug(a + o);
        };
        return C;
      },
      getAllResponseHeaders : function(){

        if(this.__ej){

          return;
        };
        if(this.__eq(r)){

          qx.Bootstrap.debug(A + o);
        };
        return e;
      },
      setDetermineSuccess : function(P){

        this.__eg = P;
      },
      dispose : function(){

        var Q = this.__ek;
        if(!this.__ej){

          if(Q){

            Q.onload = Q.onreadystatechange = null;
            this.__ep();
          };
          if(this.__ei){

            window.clearTimeout(this.__ei);
          };
          this.__ej = true;
        };
      },
      _getUrl : function(){

        return this.__eh;
      },
      _getScriptElement : function(){

        return this.__ek;
      },
      _onTimeout : function(){

        this.__em();
        if(!this.__en()){

          this._emit(q);
        };
        this._emit(w);
        if(!this.__en()){

          this._emit(p);
        };
      },
      _onNativeLoad : function(){

        var T = this.__ek,R = this.__eg,S = this;
        if(this.__dW){

          return;
        };
        if(this.__eq(l) === n && this.__eq(k) < 9){

          if(!(/loaded|complete/).test(T.readyState)){

            return;
          } else {

            if(this.__eq(s)){

              qx.Bootstrap.debug(qx.bom.request.Script, c);
            };
          };
        };
        if(this.__eq(s)){

          qx.Bootstrap.debug(qx.bom.request.Script, u);
        };
        if(R){

          if(!this.status){

            this.status = R() ? 200 : 500;
          };
        };
        if(this.status === 500){

          if(this.__eq(s)){

            qx.Bootstrap.debug(qx.bom.request.Script, z);
          };
        };
        if(this.__ei){

          window.clearTimeout(this.__ei);
        };
        window.setTimeout(function(){

          S._success();
          S._readyStateChange(4);
          S._emit(f);
          S._emit(p);
        });
      },
      _onNativeError : function(){

        this.__em();
        this._emit(q);
        this._emit(p);
      },
      __ek : null,
      __ef : null,
      __eh : m,
      __ed : null,
      __ee : null,
      __dT : null,
      __ei : null,
      __dW : null,
      __ej : null,
      __el : function(){

        this.readyState = 0;
        this.status = 0;
        this.statusText = m;
      },
      _readyStateChange : function(U){

        this.readyState = U;
        this._emit(b);
      },
      _success : function(){

        this.__ep();
        this.readyState = 4;
        if(!this.status){

          this.status = 200;
        };
        this.statusText = m + this.status;
      },
      __em : function(){

        this.__ep();
        this.readyState = 4;
        this.status = 0;
        this.statusText = null;
      },
      __en : function(){

        var W = this.__eq(l) === n && this.__eq(k) < 9;
        var V = this.__eq(l) === D;
        return !(W || V);
      },
      __eo : function(){

        var X = this.__ek = document.createElement(g);
        X.src = this.__eh;
        X.onerror = this.__ee;
        X.onload = this.__ed;
        if(this.__eq(l) === n && this.__eq(k) < 9){

          X.onreadystatechange = this.__ed;
        };
        return X;
      },
      __ep : function(){

        var Y = this.__ek;
        if(Y && Y.parentNode){

          this.__ef.removeChild(Y);
        };
      },
      __eq : function(ba){

        if(qx && qx.core && qx.core.Environment){

          return qx.core.Environment.get(ba);
        } else {

          if(ba === l){

            return qx.bom.client.Engine.getName();
          };
          if(ba === k){

            return qx.bom.client.Browser.getDocumentMode();
          };
          if(ba == s){

            return false;
          };
          throw new Error(v);
        };
      }
    },
    defer : function(){

      if(qx && qx.core && qx.core.Environment){

        qx.core.Environment.add(s, false);
      };
    }
  });
})();
(function(){

  var b = "qx.event.Emitter",a = "*";
  qx.Bootstrap.define(b, {
    extend : Object,
    statics : {
      __er : []
    },
    members : {
      __es : null,
      __et : null,
      on : function(name, c, d){

        this.__eu(name).push({
          listener : c,
          ctx : d
        });
        qx.event.Emitter.__er.push({
          name : name,
          listener : c,
          ctx : d
        });
        return qx.event.Emitter.__er.length - 1;
      },
      once : function(name, e, f){

        this.__eu(name).push({
          listener : e,
          ctx : f,
          once : true
        });
        qx.event.Emitter.__er.push({
          name : name,
          listener : e,
          ctx : f
        });
        return qx.event.Emitter.__er.length - 1;
      },
      off : function(name, g, h){

        var k = this.__eu(name);
        for(var i = k.length - 1;i >= 0;i--){

          var j = k[i];
          if(j.listener == g && j.ctx == h){

            k.splice(i, 1);
            return i;
          };
        };
        return null;
      },
      offById : function(l){

        var m = qx.event.Emitter.__er[l];
        this.off(m.name, m.listener, m.ctx);
      },
      addListener : function(name, n, o){

        return this.on(name, n, o);
      },
      addListenerOnce : function(name, p, q){

        return this.once(name, p, q);
      },
      removeListener : function(name, r, s){

        this.off(name, r, s);
      },
      removeListenerById : function(t){

        this.offById(t);
      },
      emit : function(name, u){

        var w = this.__eu(name);
        for(var i = w.length - 1;i >= 0;i--){

          var v = w[i];
          v.listener.call(v.ctx, u);
          if(v.once){

            w.splice(i, 1);
          };
        };
        w = this.__eu(a);
        for(var i = w.length - 1;i >= 0;i--){

          var v = w[i];
          v.listener.call(v.ctx, u);
        };
      },
      getListeners : function(){

        return this.__es;
      },
      __eu : function(name){

        if(this.__es == null){

          this.__es = {
          };
        };
        if(this.__es[name] == null){

          this.__es[name] = [];
        };
        return this.__es[name];
      }
    }
  });
})();
(function(){

  var j = "rim_tabletos",i = "Darwin",h = "os.version",g = "2003",f = ")",e = "iPhone",d = "android",c = "unix",b = "ce",a = "7",bf = "SymbianOS",be = "os.name",bd = "|",bc = "MacPPC",bb = "iPod",ba = "\.",Y = "Win64",X = "linux",W = "me",V = "Macintosh",q = "Android",r = "Windows",o = "ios",p = "vista",m = "8",n = "blackberry",k = "(",l = "win",u = "Linux",v = "BSD",D = "Mac OS X",B = "iPad",L = "X11",G = "xp",R = "symbian",P = "qx.bom.client.OperatingSystem",x = "g",U = "Win32",T = "osx",S = "webOS",w = "RIM Tablet OS",z = "BlackBerry",A = "nt4",C = "MacIntel",E = "webos",H = "10.1",M = "10.3",Q = "10.7",s = "10.5",t = "95",y = "10.2",K = "98",J = "2000",I = "10.6",O = "10.0",N = "10.4",F = "";
  qx.Bootstrap.define(P, {
    statics : {
      getName : function(){

        if(!navigator){

          return F;
        };
        var bg = navigator.platform || F;
        var bh = navigator.userAgent || F;
        if(bg.indexOf(r) != -1 || bg.indexOf(U) != -1 || bg.indexOf(Y) != -1){

          return l;
        } else if(bg.indexOf(V) != -1 || bg.indexOf(bc) != -1 || bg.indexOf(C) != -1 || bg.indexOf(D) != -1){

          return T;
        } else if(bh.indexOf(w) != -1){

          return j;
        } else if(bh.indexOf(S) != -1){

          return E;
        } else if(bg.indexOf(bb) != -1 || bg.indexOf(e) != -1 || bg.indexOf(B) != -1){

          return o;
        } else if(bh.indexOf(q) != -1){

          return d;
        } else if(bg.indexOf(u) != -1){

          return X;
        } else if(bg.indexOf(L) != -1 || bg.indexOf(v) != -1 || bg.indexOf(i) != -1){

          return c;
        } else if(bg.indexOf(bf) != -1){

          return R;
        } else if(bg.indexOf(z) != -1){

          return n;
        };;;;;;;;;
        return F;
      },
      __ev : {
        "Windows NT 6.2" : m,
        "Windows NT 6.1" : a,
        "Windows NT 6.0" : p,
        "Windows NT 5.2" : g,
        "Windows NT 5.1" : G,
        "Windows NT 5.0" : J,
        "Windows 2000" : J,
        "Windows NT 4.0" : A,
        "Win 9x 4.90" : W,
        "Windows CE" : b,
        "Windows 98" : K,
        "Win98" : K,
        "Windows 95" : t,
        "Win95" : t,
        "Mac OS X 10_7" : Q,
        "Mac OS X 10.7" : Q,
        "Mac OS X 10_6" : I,
        "Mac OS X 10.6" : I,
        "Mac OS X 10_5" : s,
        "Mac OS X 10.5" : s,
        "Mac OS X 10_4" : N,
        "Mac OS X 10.4" : N,
        "Mac OS X 10_3" : M,
        "Mac OS X 10.3" : M,
        "Mac OS X 10_2" : y,
        "Mac OS X 10.2" : y,
        "Mac OS X 10_1" : H,
        "Mac OS X 10.1" : H,
        "Mac OS X 10_0" : O,
        "Mac OS X 10.0" : O
      },
      getVersion : function(){

        var bk = [];
        for(var bj in qx.bom.client.OperatingSystem.__ev){

          bk.push(bj);
        };
        var bl = new RegExp(k + bk.join(bd).replace(/\./g, ba) + f, x);
        var bi = bl.exec(navigator.userAgent);
        if(bi && bi[1]){

          return qx.bom.client.OperatingSystem.__ev[bi[1]];
        };
        return F;
      }
    },
    defer : function(bm){

      qx.core.Environment.add(be, bm.getName);
      qx.core.Environment.add(h, bm.getVersion);
    }
  });
})();
(function(){

  var j = "CSS1Compat",i = "android",h = "operamini",g = "gecko",f = "browser.quirksmode",e = "browser.name",d = "mobile chrome",c = "iemobile",b = "prism|Fennec|Camino|Kmeleon|Galeon|Netscape|SeaMonkey|Namoroka|Firefox",a = "opera mobi",H = "Mobile Safari",G = "Maple",F = "operamobile",E = "ie",D = "mobile safari",C = "IEMobile|Maxthon|MSIE",B = "qx.bom.client.Browser",A = "(Maple )([0-9]+\.[0-9]+\.[0-9]*)",z = "opera mini",y = "browser.version",q = "opera",r = "Opera Mini|Opera Mobi|Opera",o = "AdobeAIR|Titanium|Fluid|Chrome|Android|Epiphany|Konqueror|iCab|OmniWeb|Maxthon|Pre|Mobile Safari|Safari",p = "webkit",m = "browser.documentmode",n = "5.0",k = "Mobile/",l = "msie",s = "maple",t = ")(/| )([0-9]+\.[0-9])",v = "(",u = "ce",x = "",w = "mshtml";
  qx.Bootstrap.define(B, {
    statics : {
      getName : function(){

        var L = navigator.userAgent;
        var K = new RegExp(v + qx.bom.client.Browser.__ew + t);
        var J = L.match(K);
        if(!J){

          return x;
        };
        var name = J[1].toLowerCase();
        var I = qx.bom.client.Engine.getName();
        if(I === p){

          if(name === i){

            name = d;
          } else if(L.indexOf(H) !== -1 || L.indexOf(k) !== -1){

            name = D;
          };
        } else if(I === w){

          if(name === l){

            name = E;
            if(qx.bom.client.OperatingSystem.getVersion() === u){

              name = c;
            };
          };
        } else if(I === q){

          if(name === a){

            name = F;
          } else if(name === z){

            name = h;
          };
        } else if(I === g){

          if(L.indexOf(G) !== -1){

            name = s;
          };
        };;;
        return name;
      },
      getVersion : function(){

        var P = navigator.userAgent;
        var O = new RegExp(v + qx.bom.client.Browser.__ew + t);
        var N = P.match(O);
        if(!N){

          return x;
        };
        var name = N[1].toLowerCase();
        var M = N[3];
        if(P.match(/Version(\/| )([0-9]+\.[0-9])/)){

          M = RegExp.$2;
        };
        if(qx.bom.client.Engine.getName() == w){

          M = qx.bom.client.Engine.getVersion();
          if(name === l && qx.bom.client.OperatingSystem.getVersion() == u){

            M = n;
          };
        };
        if(qx.bom.client.Browser.getName() == s){

          O = new RegExp(A);
          N = P.match(O);
          if(!N){

            return x;
          };
          M = N[2];
        };
        return M;
      },
      getDocumentMode : function(){

        if(document.documentMode){

          return document.documentMode;
        };
        return 0;
      },
      getQuirksMode : function(){

        if(qx.bom.client.Engine.getName() == w && parseFloat(qx.bom.client.Engine.getVersion()) >= 8){

          return qx.bom.client.Engine.DOCUMENT_MODE === 5;
        } else {

          return document.compatMode !== j;
        };
      },
      __ew : {
        "webkit" : o,
        "gecko" : b,
        "mshtml" : C,
        "opera" : r
      }[qx.bom.client.Engine.getName()]
    },
    defer : function(Q){

      qx.core.Environment.add(e, Q.getName),qx.core.Environment.add(y, Q.getVersion),qx.core.Environment.add(m, Q.getDocumentMode),qx.core.Environment.add(f, Q.getQuirksMode);
    }
  });
})();
(function(){

  var l = "qx.bom.request.Jsonp",k = "callback",j = "open",i = "dispose",h = "",g = "_onNativeLoad",f = "Expecting JavaScript response to call: ",e = "].callback",d = " already exists",c = "Callback ",a = "qx.bom.request.Jsonp[",b = "qx.debug.io";
  qx.Bootstrap.define(l, {
    extend : qx.bom.request.Script,
    construct : function(){

      qx.bom.request.Script.apply(this);
      this.__eD();
    },
    members : {
      responseJson : null,
      __bV : null,
      __ex : null,
      __ey : null,
      __ez : null,
      __eA : null,
      __ej : null,
      open : function(m, n){

        if(this.__ej){

          return;
        };
        var o = {
        },q,p,r = this;
        this.responseJson = null;
        this.__ez = false;
        q = this.__ex || k;
        p = this.__ey || a + this.__bV + e;
        if(!this.__ey){

          this.constructor[this.__bV] = this;
        } else {

          if(!window[this.__ey]){

            this.__eA = true;
            window[this.__ey] = function(s){

              r.callback(s);
            };
          } else {

            if(qx.core.Environment.get(b)){

              qx.Bootstrap.debug(qx.bom.request.Jsonp, c + this.__ey + d);
            };
          };
        };
        if(qx.core.Environment.get(b)){

          qx.Bootstrap.debug(qx.bom.request.Jsonp, f + p);
        };
        o[q] = p;
        n = qx.util.Uri.appendParamsToUrl(n, o);
        this.__eC(j, [m, n]);
      },
      callback : function(t){

        if(this.__ej){

          return;
        };
        this.__ez = true;
        {
        };
        this.responseJson = t;
        this.constructor[this.__bV] = undefined;
        this.__eB();
      },
      setCallbackParam : function(u){

        this.__ex = u;
        return this;
      },
      setCallbackName : function(name){

        this.__ey = name;
        return this;
      },
      dispose : function(){

        this.__eB();
        this.__eC(i);
      },
      _onNativeLoad : function(){

        this.status = this.__ez ? 200 : 500;
        this.__eC(g);
      },
      __eB : function(){

        if(this.__eA && window[this.__ey]){

          window[this.__ey] = undefined;
          this.__eA = false;
        };
      },
      __eC : function(v, w){

        qx.bom.request.Script.prototype[v].apply(this, w || []);
      },
      __eD : function(){

        this.__bV = (new Date().valueOf()) + (h + Math.random()).substring(2, 5);
      }
    }
  });
})();
(function(){

  var t = "wialon.item.User",s = "changeHostsMask",r = "create_user",q = "user/update_item_access",p = "fl",o = "user/update_password",n = "delete_user_notify",m = "user/get_items_access",l = "create_user_notify",k = "update_user_pass",d = "user",j = "hm",g = "update_hosts_mask",c = "update_user_flags",b = "user/update_user_flags",f = "user/update_hosts_mask",e = "Integer",h = "String",a = "changeUserFlags",i = "qx.event.type.Data";
  qx.Class.define(t, {
    extend : wialon.item.Item,
    properties : {
      userFlags : {
        init : null,
        check : e,
        event : a
      },
      hostsMask : {
        init : null,
        check : h,
        event : s
      }
    },
    members : {
      getItemsAccess : function(u, v, w){

        wialon.core.Remote.getInstance().remoteCall(m, {
          userId : this.getId(),
          directAccess : u,
          itemSuperclass : v
        }, wialon.util.Helper.wrapCallback(w));
      },
      updateItemAccess : function(x, y, z){

        wialon.core.Remote.getInstance().remoteCall(q, {
          userId : this.getId(),
          itemId : x.getId(),
          accessMask : y
        }, wialon.util.Helper.wrapCallback(z));
      },
      updateUserFlags : function(A, B, C){

        wialon.core.Remote.getInstance().remoteCall(b, {
          userId : this.getId(),
          flags : A,
          flagsMask : B
        }, qx.lang.Function.bind(this._onUpdateProperties, this, wialon.util.Helper.wrapCallback(C)));
      },
      updateHostsMask : function(D, E){

        wialon.core.Remote.getInstance().remoteCall(f, {
          userId : this.getId(),
          hostsMask : D
        }, qx.lang.Function.bind(this._onUpdateProperties, this, wialon.util.Helper.wrapCallback(E)));
      },
      updatePassword : function(F, G, H){

        wialon.core.Remote.getInstance().remoteCall(o, {
          userId : this.getId(),
          oldPassword : F,
          newPassword : G
        }, wialon.util.Helper.wrapCallback(H));
      }
    },
    statics : {
      dataFlag : {
        flags : 0x00000100,
        notifications : 0x00000200,
        connSettings : 0x00000400
      },
      accessFlag : {
        setItemsAccess : 0x100000,
        operateAs : 0x200000,
        editUserFlags : 0x400000
      },
      defaultDataFlags : function(){

        return wialon.item.Item.dataFlag.base | wialon.item.Item.dataFlag.CustomProps | wialon.item.Item.dataFlag.billingProps | wialon.item.User.dataFlag.flags;
      },
      userFlag : {
        isDisabled : 0x00000001,
        cantChangePassword : 0x00000002,
        canCreateItems : 0x00000004,
        isReadonly : 0x00000010,
        canSendSMS : 0x00000020
      },
      logMessageAction : {
        userCreated : r,
        userUpdatedHostsMask : g,
        userUpdatedPassword : k,
        userUpdatedFlags : c,
        userCreatedNotification : l,
        userDeletedNotification : n
      },
      registerProperties : function(){

        var I = wialon.core.Session.getInstance();
        I.registerConstructor(d, wialon.item.User);
        I.registerProperty(p, this.remoteUpdateUserFlags);
        I.registerProperty(j, this.remoteUpdateHostsMask);
      },
      remoteUpdateUserFlags : function(J, K){

        J.setUserFlags(K);
      },
      remoteUpdateHostsMask : function(L, M){

        L.setHostsMask(M);
      }
    },
    events : {
      "changeUserFlags" : i,
      "changeHostsMask" : i
    }
  });
})();
(function(){

  var bv = "download_file",bu = "cneh",bt = "update_unit_phone",bs = "changeAccessPassword",br = "update_unit_uid",bq = "check_config",bp = "changeDeviceTypeId",bo = "changeMessageParams",bn = "changeDriverCode",bm = "pos",bb = "unit/update_traffic_counter",ba = "update_unit_trip_cfg",Y = "update_alias",X = "unit/update_mileage_counter",W = "update_msgs_filter_cfg",V = "update_unit_milcounter",U = "delete_unit_msg",T = "unit/update_calc_flags",S = "bind_unit_trailer",R = "delete_alias",bC = "unit/update_eh_counter",bD = "&time=",bA = "changeLastMessage",bB = "unbind_unit_driver",by = "hw",bz = "update_unit_calcflags",bw = "ud",bx = "get",bE = "&msgIndex=",bF = "psw",bf = "update_unit_phone2",be = "cfl",bh = "avl_unit",bg = "ph",bj = "import_unit_msgs",bi = "uid",bl = "unit/exec_cmd",bk = "update_unit_bytecounter",bd = "create_alias",bc = "lmsg",a = "changePhoneNumber2",b = "changeMileageCounter",c = "&svc=unit/update_hw_params&params=",d = "unit/update_access_password",e = "function",f = "delete_service_interval",g = "changeTrafficCounter",h = "wialon.item.Unit",i = "changePhoneNumber",j = "update_unit_report_cfg",bJ = "/avl_msg_photo.jpeg?sid=",bI = "update_unit_pass",bH = "cmds",bG = "ph2",bN = "changeUniqueId",bM = "cnm",bL = "unbind_unit_trailer",bK = "unit/update_device_type",bP = "unit/update_phone",bO = "bind_unit_driver",B = "update_unit_hw",C = "prms",z = "changeEngineHoursCounter",A = "unit/update_phone2",F = "cnkb",G = "update_unit_ehcounter",D = "update_sensor",E = "changePosition",x = "update_unit_fuel_cfg",y = "Array",r = "",q = "update_service_interval",t = "changeCalcFlags",s = "changeCommands",n = "create_sensor",m = "drv",p = "update_unit_hw_config",o = "delete_sensor",l = "/adfurl",k = "create_service_interval",L = /*"/wialon/ajax.html?sid="*/"/ajax.html?sid=",M = "import_unit_cfg",N = "&unitIndex=",O = "delete_unit_msgs",H = "create_unit",I = "undefined",J = "set",K = "Object",P = "object",Q = "unit/update_hw_params",w = "Integer",v = "String",u = "qx.event.type.Data";
  qx.Class.define(h, {
    extend : wialon.item.Item,
    properties : {
      uniqueId : {
        init : null,
        check : v,
        event : bN
      },
      deviceTypeId : {
        init : null,
        check : w,
        event : bp
      },
      phoneNumber : {
        init : null,
        check : v,
        event : i
      },
      phoneNumber2 : {
        init : null,
        check : v,
        event : a
      },
      accessPassword : {
        init : null,
        check : v,
        event : bs
      },
      commands : {
        init : null,
        check : y,
        event : s
      },
      position : {
        init : null,
        check : K,
        event : E,
        nullable : true
      },
      lastMessage : {
        init : null,
        check : K,
        event : bA,
        nullable : true
      },
      driverCode : {
        init : null,
        check : v,
        event : bn
      },
      calcFlags : {
        init : null,
        check : w,
        event : t
      },
      mileageCounter : {
        init : null,
        check : w,
        event : b
      },
      engineHoursCounter : {
        init : null,
        check : w,
        event : z
      },
      trafficCounter : {
        init : null,
        check : w,
        event : g
      },
      messageParams : {
        init : null,
        check : K,
        event : bo,
        nullable : true
      }
    },
    members : {
      remoteCommand : function(bQ, bR, bS, bT, bU, bV){

        if(bU && typeof bU == e){

          bV = bU;
          bU = 0;
        };
        wialon.core.Remote.getInstance().remoteCall(bl, {
          itemId : this.getId(),
          commandName : bQ,
          linkType : bR,
          param : bS,
          timeout : bT,
          flags : bU
        }, wialon.util.Helper.wrapCallback(bV));
      },
      updateDeviceSettings : function(bW, bX, bY){

        wialon.core.Remote.getInstance().remoteCall(bK, {
          itemId : this.getId(),
          deviceTypeId : bW,
          uniqueId : bX
        }, qx.lang.Function.bind(this._onUpdateProperties, this, wialon.util.Helper.wrapCallback(bY)));
      },
      updatePhoneNumber : function(ca, cb){

        wialon.core.Remote.getInstance().remoteCall(bP, {
          itemId : this.getId(),
          phoneNumber : ca
        }, qx.lang.Function.bind(this._onUpdateProperties, this, wialon.util.Helper.wrapCallback(cb)));
      },
      updatePhoneNumber2 : function(cc, cd){

        wialon.core.Remote.getInstance().remoteCall(A, {
          itemId : this.getId(),
          phoneNumber : cc,
          second : 1
        }, qx.lang.Function.bind(this._onUpdateProperties, this, wialon.util.Helper.wrapCallback(cd)));
      },
      updateAccessPassword : function(ce, cf){

        wialon.core.Remote.getInstance().remoteCall(d, {
          itemId : this.getId(),
          accessPassword : ce
        }, qx.lang.Function.bind(this._onUpdateProperties, this, wialon.util.Helper.wrapCallback(cf)));
      },
      updateMileageCounter : function(cg, ch){

        wialon.core.Remote.getInstance().remoteCall(X, {
          itemId : this.getId(),
          newValue : cg
        }, qx.lang.Function.bind(this._onUpdateProperties, this, wialon.util.Helper.wrapCallback(ch)));
      },
      updateEngineHoursCounter : function(ci, cj){

        wialon.core.Remote.getInstance().remoteCall(bC, {
          itemId : this.getId(),
          newValue : ci
        }, qx.lang.Function.bind(this._onUpdateProperties, this, wialon.util.Helper.wrapCallback(cj)));
      },
      updateTrafficCounter : function(ck, cl, cm){

        wialon.core.Remote.getInstance().remoteCall(bb, {
          itemId : this.getId(),
          newValue : ck,
          regReset : cl || 0
        }, qx.lang.Function.bind(this._onUpdateProperties, this, wialon.util.Helper.wrapCallback(cm)));
      },
      updateCalcFlags : function(cn, co){

        wialon.core.Remote.getInstance().remoteCall(T, {
          itemId : this.getId(),
          newValue : cn
        }, qx.lang.Function.bind(this._onUpdateProperties, this, wialon.util.Helper.wrapCallback(co)));
      },
      handleMessage : function(cp){

        if(cp && cp.tp == bw){

          var cq = this.getLastMessage();
          if(!cq || cq.t < cp.t){

            if(!cq)this.setLastMessage(cp); else {

              var ct = qx.lang.Object.clone(cp);
              if(cp.p)ct.p = qx.lang.Object.clone(cp.p);
              qx.lang.Object.carefullyMergeWith(ct, cq);
              if(cq.p)qx.lang.Object.carefullyMergeWith(ct.p, cq.p);
              if(!ct.pos)ct.pos = cq.pos;
              this.setLastMessage(ct);
            };
          };
          if(cp.pos){

            var cs = this.getPosition();
            if(!cs || cs.t < cp.t){

              var cr = qx.lang.Object.clone(cp.pos);
              cr.t = cp.t;
              this.setPosition(cr);
            };
          };
        };
        wialon.item.Item.prototype.handleMessage.call(this, cp);
      },
      getMessageImageUrl : function(cu, cv, cw){

        if(!cw)cw = r;
        return wialon.core.Session.getInstance().getBaseUrl() + l + cw + bJ + wialon.core.Session.getInstance().getId() + bD + cu + N + this.getId() + bE + cv;
      },
      downloadHwParamFile : function(cx, cy, cz){

        return wialon.core.Session.getInstance().getBaseUrl() + L + wialon.core.Session.getInstance().getId() + c + qx.lang.Json.stringify({
          itemId : this.getId(),
          hwId : cx,
          fileId : cy,
          action : bv
        });
      },
      updateHwParams : function(cA, cB, cC, cD){

        if(cC && cC.length && (typeof cB.full_data != I && !cB.full_data))wialon.core.Uploader.getInstance().uploadFiles(cC, Q, {
          itemId : this.getId(),
          hwId : cA,
          params_data : cB,
          action : J
        }, wialon.util.Helper.wrapCallback(cD), true, 30000); else wialon.core.Remote.getInstance().remoteCall(Q, {
          itemId : this.getId(),
          hwId : cA,
          params_data : cB,
          action : J
        }, wialon.util.Helper.wrapCallback(cD));
      }
    },
    statics : {
      dataFlag : {
        restricted : 0x00000100,
        commands : 0x00000200,
        lastMessage : 0x00000400,
        driverCode : 0x00000800,
        sensors : 0x00001000,
        counters : 0x00002000,
        routeControl : 0x00004000,
        maintenance : 0x00008000,
        log : 0x00010000,
        reportSettings : 0x00020000,
        other : 0x00040000,
        commandAliases : 0x00080000,
        messageParams : 0x00100000
      },
      accessFlag : {
        editDevice : 0x100000,
        editSensors : 0x200000,
        editCounters : 0x400000,
        deleteMessages : 0x800000,
        executeCommands : 0x1000000,
        registerEvents : 0x2000000,
        viewRoutes : 0x4000000,
        editRoutes : 0x8000000,
        viewServiceIntervals : 0x10000000,
        editServiceIntervals : 0x20000000,
        importMessages : 0x40000000,
        exportMessages : 0x80000000,
        viewCmdAliases : 0x400000000,
        editCmdAliases : 0x800000000,
        viewEvents : 0x1000000000,
        editEvents : 0x2000000000,
        editReportSettings : 0x4000000000,
        monitorState : 0x8000000000
      },
      calcFlag : {
        mileageMask : 0xF,
        mileageGps : 0x0,
        mileageAbsOdometer : 0x1,
        mileageRelOdometer : 0x2,
        mileageGpsIgn : 0x3,
        engineHoursMask : 0xF0,
        engineHoursIgn : 0x10,
        engineHoursAbs : 0x20,
        engineHoursRel : 0x40,
        mileageAuto : 0x100,
        engineHoursAuto : 0x200,
        trafficAuto : 0x400
      },
      dataMessageFlag : {
        position : 0x1,
        inputs : 0x2,
        outputs : 0x4,
        alarm : 0x10,
        driverCode : 0x20
      },
      eventMessageFlag : {
        typeMask : 0x0F,
        typeSimple : 0x0,
        typeViolation : 0x1,
        typeMaintenance : 0x2,
        typeRouteControl : 0x4,
        typeDrivingInfo : 0x8,
        maintenanceMask : 0x0,
        maintenanceService : 0x10,
        maintenanceFilling : 0x20
      },
      execCmdFlag : {
        primaryPhone : 0x01,
        secondaryPhone : 0x02
      },
      logMessageAction : {
        unitCreated : H,
        unitUpdatedPassword : bI,
        unitUpdatedPhone : bt,
        unitUpdatedPhone2 : bf,
        unitUpdatedCalcFlags : bz,
        unitChangeMilageCounter : V,
        unitChangeByteCounter : bk,
        unitChangeEngineHoursCounter : G,
        unitUpdatedUniqueId : br,
        unitUpdatedHwType : B,
        unitUpdatedHwConfig : p,
        unitUpdatedFuelConsumptionSettings : x,
        unitUpdatedTripDetectorSettings : ba,
        unitCreatedSensor : n,
        unitUpdatedSensor : D,
        unitDeletedSensor : o,
        unitCreatedCommandAlias : bd,
        unitUpdatedCommandAlias : Y,
        unitDeletedCommandAlias : R,
        unitCreatedServiceInterval : k,
        unitUpdatedServiceInterval : q,
        unitDeletedServiceInterval : f,
        unitSettingsImported : M,
        unitMessagesImported : bj,
        unitMessageDeleted : U,
        unitMessagesDeleted : O,
        unitDriverBinded : bO,
        unitDriverUnbinded : bB,
        unitTrailerBinded : S,
        unitTrailerUnbinded : bL,
        unitReportSettingsUpdated : j,
        unitMessagesFilterSettingsUpdated : W
      },
      registerProperties : function(){

        var cE = wialon.core.Session.getInstance();
        cE.registerConstructor(bh, wialon.item.Unit);
        cE.registerProperty(bi, this.remoteUpdateUniqueId);
        cE.registerProperty(by, this.remoteUpdateDeviceTypeId);
        cE.registerProperty(bg, this.remoteUpdatePhoneNumber);
        cE.registerProperty(bG, this.remoteUpdatePhoneNumber2);
        cE.registerProperty(bF, this.remoteUpdateAccessPassword);
        cE.registerProperty(bH, this.remoteUpdateCommands);
        cE.registerProperty(bm, this.remoteUpdatePosition);
        cE.registerProperty(bc, this.remoteUpdateLastMessage);
        cE.registerProperty(m, this.remoteUpdateDriverCode);
        cE.registerProperty(be, this.remoteUpdateCalcFlags);
        cE.registerProperty(bM, this.remoteUpdateMileageCounter);
        cE.registerProperty(bu, this.remoteUpdateEngineHoursCounter);
        cE.registerProperty(F, this.remoteUpdateTrafficCounter);
        cE.registerProperty(C, this.remoteUpdateMessageParams);
        wialon.item.MIcon.registerIconProperties();
      },
      remoteUpdateUniqueId : function(cF, cG){

        cF.setUniqueId(cG);
      },
      remoteUpdateDeviceTypeId : function(cH, cI){

        cH.setDeviceTypeId(cI);
      },
      remoteUpdatePhoneNumber : function(cJ, cK){

        cJ.setPhoneNumber(cK);
      },
      remoteUpdatePhoneNumber2 : function(cL, cM){

        cL.setPhoneNumber2(cM);
      },
      remoteUpdateAccessPassword : function(cN, cO){

        cN.setAccessPassword(cO);
      },
      remoteUpdateCommands : function(cP, cQ){

        cP.setCommands(cQ);
      },
      remoteUpdatePosition : function(cR, cS){

        cR.setPosition(cS);
      },
      remoteUpdateLastMessage : function(cT, cU){

        cT.setLastMessage(cU);
      },
      remoteUpdateDriverCode : function(cV, cW){

        cV.setDriverCode(cW);
      },
      remoteUpdateCalcFlags : function(cX, cY){

        cX.setCalcFlags(cY);
      },
      remoteUpdateMileageCounter : function(da, db){

        da.setMileageCounter(db);
      },
      remoteUpdateEngineHoursCounter : function(dc, dd){

        dc.setEngineHoursCounter(dd);
      },
      remoteUpdateTrafficCounter : function(de, df){

        de.setTrafficCounter(df);
      },
      remoteUpdateMessageParams : function(dg, dh){

        if(typeof dh != P)return;
        var di = dg.getMessageParams();
        if(!di)di = {
        }; else di = qx.lang.Object.clone(di);
        for(var dj in dh){

          if(typeof dh[dj] == P)di[dj] = dh[dj]; else if(typeof di[dj] == P)di[dj].at = dh[dj];;
        };
        dg.setMessageParams(di);
      },
      checkHwConfig : function(dk, dl){

        wialon.core.Remote.getInstance().remoteCall(Q, {
          hwId : dk,
          action : bq
        }, wialon.util.Helper.wrapCallback(dl));
      },
      getHwParams : function(dm, dn, dp, dq){

        wialon.core.Remote.getInstance().remoteCall(Q, {
          itemId : dm,
          hwId : dn,
          fullData : dp ? 1 : 0,
          action : bx
        }, wialon.util.Helper.wrapCallback(dq));
      }
    },
    events : {
      "changeUniqueId" : u,
      "changeDeviceTypeId" : u,
      "changePhoneNumber" : u,
      "changePhoneNumber2" : u,
      "changeAccessPassword" : u,
      "changeCommands" : u,
      "changePosition" : u,
      "changeLastMessage" : u,
      "changeDriverCode" : u,
      "changeCalcFlags" : u,
      "changeMileageCounter" : u,
      "changeEngineHoursCounter" : u,
      "changeTrafficCounter" : u,
      "changeMessageParams" : u
    }
  });
})();
(function(){

  var l = "wialon.core.Uploader",k = "<",j = "singleton",h = "action",g = ">",e = "fileUploaded",d = "onload",c = "multipart/form-data",b = "load",a = "enctype",G = "target",F = "",E = "&sid=",D = "method",C = "form",B = "POST",A = "params",z = "jUploadFrame",y = "iframe",x = /*"/wialon/ajax.html?svc="*/"/ajax.html?svc=",s = "jUploadForm",t = "object",q = "function",r = "eventHash",o = "hidden",p = "name",m = "input",n = "none",u = "undefined",v = "id",w = "hash";
  qx.Class.define(l, {
    extend : qx.core.Object,
    type : j,
    members : {
      __eE : null,
      __eF : {
      },
      __eG : 1024 * 1024 * 16,
      uploadFiles : function(H, I, J, K, L, M){

        if(!(H instanceof Array))return false;
        K = wialon.util.Helper.wrapCallback(K);
        var X = (new Date()).getTime();
        var W = s + X;
        var S = z + X;
        var Q = wialon.core.Session.getInstance().getBaseUrl() + x + I + E + wialon.core.Session.getInstance().getId();
        var P = document.createElement(C);
        if(!J)J = {
        };
        J[r] = W;
        var U = document.createElement(m);
        U.name = A;
        U.type = o;
        U.value = (wialon.util.Json.stringify(J).replace(/&lt;/g, k).replace(/&gt;/g, g));
        P.appendChild(U);
        var U = document.createElement(m);
        U.name = r;
        U.type = o;
        U.value = W;
        P.appendChild(U);
        var T = document.createElement(y);
        P.setAttribute(h, Q);
        P.setAttribute(D, B);
        P.setAttribute(p, W);
        P.setAttribute(v, W);
        P.setAttribute(a, c);
        P.style.display = n;
        var V = 0;
        for(var i = 0;i < H.length;i++){

          var O = H[i];
          var N = document.getElementById(O.id);
          var ba = 0;
          if(N && typeof N.files == t && N.files.length){

            var f = N.files[0];
            ba = typeof f.fileSize != u ? f.fileSize : (typeof f.size != u ? f.size : 0);
          };
          O.parentNode.insertBefore(O.cloneNode(true), O);
          O.setAttribute(v, F);
          P.appendChild(O);
          V += ba;
        };
        document.body.appendChild(P);
        T.setAttribute(v, S);
        T.setAttribute(p, S);
        T.style.display = n;
        document.body.appendChild(T);
        var R = qx.lang.Function.bind(this.__eH, this, {
          callback : K,
          io : T,
          form : P,
          phase : 0
        });
        if(V > this.__eG){

          R();
          return;
        };
        if(!L){

          if(window.attachEvent)T.attachEvent(d, R); else T.addEventListener(b, R, false);
        } else {

          if(!this.__eE)this.__eE = wialon.core.Session.getInstance().addListener(e, this.__eI, this);
          this.__eF[W] = R;
        };
        P.setAttribute(G, S);
        P.submit();
        if(M && L){

          var Y = qx.lang.Function.bind(function(){

            if(typeof this.__eF[W] == q)this.__eF[W]();
          }, this);
          setTimeout(Y, M * 1000);
        };
        return true;
      },
      __eH : function(bb, event){

        bb.io.parentNode.removeChild(bb.io);
        bb.form.parentNode.removeChild(bb.form);
        bb.io = null;
        bb.form = null;
        bb.callback(event ? 0 : 6, (event && typeof event.preventDefault == q) ? null : event);
      },
      __eI : function(event){

        var bd = event.getData();
        if(!bd || typeof bd[w] == u)return;
        var bc = this.__eF[bd[w]];
        if(!bc)return;
        bc(bd);
        delete this.__eF[bd[w]];
      }
    }
  });
})();
(function(){

  var m = "undefined",l = "wialon.item.MIcon",k = "number",j = ".png",i = "unit/update_image",h = "changeIcon",g = "/avl_item_image/",f = "Integer",e = "string",d = "ugi",a = "qx.event.type.Data",c = "unit/upload_image",b = "/";
  qx.Mixin.define(l, {
    properties : {
      iconCookie : {
        init : null,
        check : f,
        event : h
      }
    },
    members : {
      getIconUrl : function(n){

        if(typeof n == m || !n)n = 32;
        return wialon.core.Session.getInstance().getBaseUrl() + g + this.getId() + b + n + b + this.getIconCookie() + j;
      },
      updateIcon : function(o, p){

        if(typeof o == e)return wialon.core.Uploader.getInstance().uploadFiles([], c, {
          fileUrl : o,
          itemId : this.getId()
        }, p, true); else if(typeof o == k)return wialon.core.Remote.getInstance().remoteCall(i, {
          itemId : this.getId(),
          oldItemId : o
        }, p);;
        return wialon.core.Uploader.getInstance().uploadFiles([o], c, {
          itemId : this.getId()
        }, p, true);
      }
    },
    statics : {
      registerIconProperties : function(){

        var q = wialon.core.Session.getInstance();
        q.registerProperty(d, this.remoteUpdateIconCookie);
      },
      remoteUpdateIconCookie : function(r, s){

        r.setIconCookie(s);
      }
    },
    events : {
      "changeIcon" : a
    }
  });
})();
(function(){

  var j = "create_resource",i = "import_zones",h = "delete_notify",g = "create_driver",f = "update_driver",e = "switch_job",d = "update_driver_units",c = "avl_resource",b = "update_zone",a = "create_drivers_group",C = "delete_drivers_group",B = "update_report",A = "delete_driver",z = "create_zone",y = "delete_zone",x = "update_poi",w = "delete_job",v = "update_notify",u = "wialon.item.Resource",t = "create_notify",q = "update_drivers_group",r = "delete_report",o = "delete_poi",p = "switch_notify",m = "create_report",n = "update_job",k = "import_pois",l = "create_job",s = "create_poi";
  qx.Class.define(u, {
    extend : wialon.item.Item,
    statics : {
      dataFlag : {
        drivers : 0x00000100,
        jobs : 0x00000200,
        notifications : 0x00000400,
        poi : 0x00000800,
        zones : 0x00001000,
        reports : 0x00002000,
        agro : 0x01000000,
        driverUnits : 0x00004000,
        driverGroups : 0x00008000,
        trailers : 0x00010000,
        trailerGroups : 0x00020000,
        trailerUnits : 0x00040000
      },
      accessFlag : {
        viewNotifications : 0x100000,
        editNotifications : 0x200000,
        viewPoi : 0x400000,
        editPoi : 0x800000,
        viewZones : 0x1000000,
        editZones : 0x2000000,
        viewJobs : 0x4000000,
        editJobs : 0x8000000,
        viewReports : 0x10000000,
        editReports : 0x20000000,
        viewDrivers : 0x40000000,
        editDrivers : 0x80000000,
        manageAccount : 0x100000000,
        agroEditCultivations : 0x10000000000,
        agroView : 0x20000000000,
        agroEdit : 0x40000000000,
        viewDriverGroups : 0x200000000,
        editDriverGroups : 0x400000000,
        viewDriverUnits : 0x800000000,
        editDriverUnits : 0x1000000000,
        viewTrailers : 0x100000000000,
        editTrailers : 0x200000000000,
        viewTrailerGroups : 0x400000000000,
        editTrailerGroups : 0x800000000000,
        viewTrailerUnits : 0x1000000000000,
        editTrailerUnits : 0x2000000000000
      },
      logMessageAction : {
        resourceCreated : j,
        resourceCreatedZone : z,
        resourceUpdatedZone : b,
        resourceDeletedZone : y,
        resourceCreatedPoi : s,
        resourceUpdatedPoi : x,
        resourceDeletedPoi : o,
        resourceCreatedJob : l,
        resourceSwitchedJob : e,
        resourceUpdatedJob : n,
        resourceDeletedJob : w,
        resourceCreatedNotification : t,
        resourceSwitchedNotification : p,
        resourceUpdatedNotification : v,
        resourceDeletedNotification : h,
        resourceCreatedDriver : g,
        resourceUpdatedDriver : f,
        resourceDeletedDriver : A,
        resourceCreatedDriversGroup : a,
        resourceUpdatedDriversGroup : q,
        resourceDeletedDriversGroup : C,
        resourceUpdatedDriverUnits : d,
        resourceCreatedReport : m,
        resourceUpdatedReport : B,
        resourceDeletedReport : r,
        resourceImportedPois : k,
        resourceImportedZones : i
      },
      registerProperties : function(){

        var D = wialon.core.Session.getInstance();
        D.registerConstructor(c, wialon.item.Resource);
      }
    }
  });
})();
(function(){

  var j = "create_group",i = "Array",h = "u",g = "wialon.item.UnitGroup",f = "avl_unit",e = "units_group",d = "qx.event.type.Data",c = "changeUnits",b = "unit_group/update_units",a = "avl_unit_group";
  qx.Class.define(g, {
    extend : wialon.item.Item,
    properties : {
      units : {
        init : null,
        check : i,
        event : c
      }
    },
    members : {
      updateUnits : function(k, l){

        wialon.core.Remote.getInstance().remoteCall(b, {
          itemId : this.getId(),
          units : k
        }, qx.lang.Function.bind(this._onUpdateProperties, this, wialon.util.Helper.wrapCallback(l)));
      }
    },
    statics : {
      registerProperties : function(){

        var m = wialon.core.Session.getInstance();
        m.registerConstructor(a, wialon.item.UnitGroup);
        m.registerProperty(h, this.remoteUpdateUnits);
        wialon.item.MIcon.registerIconProperties();
      },
      logMessageAction : {
        unitGroupCreated : j,
        unitGroupUnitsUpdated : e
      },
      remoteUpdateUnits : function(n, o){

        var p = n.getUnits();
        if(p && wialon.util.Json.compareObjects(o, p))return;
        n.setUnits(o);
      },
      checkUnit : function(q, r){

        if(!q || q.getType() != a || !r || r.getType() != f)return false;
        var s = q.getUnits();
        var t = r.getId();
        return (s.indexOf(t) != -1 ? true : false);
      }
    },
    events : {
      "changeUnits" : d
    }
  });
})();
(function(){

  var u = "changeConfig",t = "update_retranslator",s = "retranslator/update_units",r = "units_retranslator",q = "Boolean",p = "changeOperating",o = "create_retranslator",n = "changeStopTime",m = "changeUnits",l = "avl_retranslator",e = "retranslator/update_config",k = "rtru",h = "switch_retranslator",c = "rtro",b = "rtrst",g = "rtrc",f = "Integer",i = "wialon.item.Retranslator",a = "retranslator/update_operating",j = "Object",d = "qx.event.type.Data";
  qx.Class.define(i, {
    extend : wialon.item.Item,
    properties : {
      operating : {
        init : null,
        check : q,
        event : p
      },
      stopTime : {
        init : null,
        check : f,
        event : n
      },
      config : {
        init : null,
        check : j,
        event : u
      },
      units : {
        init : null,
        check : j,
        event : m
      }
    },
    members : {
      updateOperating : function(v, w){

        wialon.core.Remote.getInstance().remoteCall(a, {
          itemId : this.getId(),
          operate : v
        }, qx.lang.Function.bind(this._onUpdateProperties, this, wialon.util.Helper.wrapCallback(w)));
      },
      updateOperatingWithTimeout : function(x, y, z, A){

        var B;
        if(z)B = y; else B = wialon.core.Session.getInstance().getServerTime() + y;
        wialon.core.Remote.getInstance().remoteCall(a, {
          itemId : this.getId(),
          operate : x,
          stopTime : B
        }, qx.lang.Function.bind(this._onUpdateProperties, this, wialon.util.Helper.wrapCallback(A)));
      },
      updateConfig : function(C, D){

        wialon.core.Remote.getInstance().remoteCall(e, {
          itemId : this.getId(),
          config : C
        }, qx.lang.Function.bind(this._onUpdateProperties, this, wialon.util.Helper.wrapCallback(D)));
      },
      updateUnits : function(E, F){

        wialon.core.Remote.getInstance().remoteCall(s, {
          itemId : this.getId(),
          units : E
        }, qx.lang.Function.bind(this._onUpdateProperties, this, wialon.util.Helper.wrapCallback(F)));
      }
    },
    statics : {
      dataFlag : {
        state : 0x00000100,
        units : 0x00000200
      },
      accessFlag : {
        editSettings : 0x100000,
        editUnits : 0x200000
      },
      logMessageAction : {
        retranslatorCreated : o,
        retranslatorUpdated : t,
        retranslatorUnitsUpdated : r,
        retranslatorSwitched : h
      },
      registerProperties : function(){

        var G = wialon.core.Session.getInstance();
        G.registerConstructor(l, wialon.item.Retranslator);
        G.registerProperty(c, this.remoteUpdateOperating);
        G.registerProperty(b, this.remoteUpdateStopTime);
        G.registerProperty(g, this.remoteUpdateConfig);
        G.registerProperty(k, this.remoteUpdateUnits);
      },
      remoteUpdateOperating : function(H, I){

        H.setOperating(I ? true : false);
      },
      remoteUpdateStopTime : function(J, K){

        J.setStopTime(K);
      },
      remoteUpdateConfig : function(L, M){

        L.setConfig(M ? M : {
        });
      },
      remoteUpdateUnits : function(N, O){

        var P = N.getUnits();
        if(P && wialon.util.Json.compareObjects(O, P))return;
        N.setUnits(O);
      }
    },
    events : {
      "changeOperating" : d,
      "changeStopTime" : d,
      "changeConfig" : d,
      "changeUnits" : d
    }
  });
})();
(function(){

  var j = "create_schedule",i = "update_route_points",h = "rpts",g = "route/get_schedule_time",f = "update_round",e = "Object",d = "update_route_cfg",c = "delete_round",b = "Array",a = "route/load_rounds",w = "update_schedule",v = "wialon.item.Route",u = "delete_schedule",t = "create_route",s = "route/get_all_rounds",r = "changeConfig",q = "changeCheckPoints",p = "route/update_checkpoints",o = "rcfg",n = "create_round",l = "route/update_config",m = "avl_route",k = "qx.event.type.Data";
  qx.Class.define(v, {
    extend : wialon.item.Item,
    properties : {
      config : {
        init : null,
        check : e,
        nullable : true,
        event : r
      },
      checkPoints : {
        init : null,
        check : b,
        event : q
      }
    },
    members : {
      updateConfig : function(x, y){

        wialon.core.Remote.getInstance().remoteCall(l, {
          itemId : this.getId(),
          config : x
        }, qx.lang.Function.bind(this._onUpdateProperties, this, wialon.util.Helper.wrapCallback(y)));
      },
      getNextRoundTime : function(z, A, B, C){

        wialon.core.Remote.getInstance().remoteCall(g, {
          itemId : this.getId(),
          scheduleId : z,
          timeFrom : A,
          timeTo : B
        }, wialon.util.Helper.wrapCallback(C));
      },
      loadRoundsHistory : function(D, E, F, G){

        wialon.core.Remote.getInstance().remoteCall(a, {
          itemId : this.getId(),
          timeFrom : D,
          timeTo : E,
          fullJson : F
        }, wialon.util.Helper.wrapCallback(G));
      },
      updateCheckPoints : function(H, I){

        wialon.core.Remote.getInstance().remoteCall(p, {
          itemId : this.getId(),
          checkPoints : H
        }, qx.lang.Function.bind(this._onUpdateProperties, this, wialon.util.Helper.wrapCallback(I)));
      },
      getRouteRounds : function(J, K, L, M){

        wialon.core.Remote.getInstance().remoteCall(s, {
          itemId : this.getId(),
          timeFrom : J,
          timeTo : K,
          fullJson : L
        }, wialon.util.Helper.wrapCallback(M));
      }
    },
    statics : {
      dataFlag : {
        config : 0x00000100,
        checkPoints : 0x00000200,
        schedules : 0x00000400,
        rounds : 0x00000800
      },
      accessFlag : {
        editSettings : 0x100000
      },
      states : {
        stateInactive : 0x010000,
        stateFinshed : 0x020000,
        stateCheckingArrive : 0x040000,
        stateCheckingDeparture : 0x080000,
        stateTimeLate : 0x200000,
        stateTimeEarly : 0x400000,
        stateDisabled : 0x800000,
        stateAborted : 0x0100000,
        eventControlStarted : 0x1,
        eventControlFinished : 0x2,
        eventControlAborted : 0x4,
        eventPointArrived : 0x8,
        eventPointSkipped : 0x10,
        eventPointDepartured : 0x20,
        eventControlLate : 0x40,
        eventControlEarly : 0x80,
        eventControlInTime : 0x100
      },
      routePointFlag : {
        simple : 0x1,
        geozone : 0x2,
        unit : 0x4
      },
      scheduleFlag : {
        relative : 0x1,
        relativeDaily : 0x2,
        absolute : 0x4
      },
      roundFlag : {
        autoDelete : 0x2,
        allowSkipPoints : 0x10,
        generateEvents : 0x20,
        arbituaryPoints : 0x40
      },
      logMessageAction : {
        routeCreated : t,
        routeUpdatedPoints : i,
        routeUpdatedConfiguration : d,
        routeCreatedRound : n,
        routeUpdatedRound : f,
        routeDeletedRound : c,
        routeCreatedSchedule : j,
        routeUpdatedSchedule : w,
        routeDeletedSchedule : u
      },
      registerProperties : function(){

        var N = wialon.core.Session.getInstance();
        N.registerConstructor(m, wialon.item.Route);
        N.registerProperty(h, this.remoteUpdateCheckPoints);
        N.registerProperty(o, this.remoteUpdateConfig);
      },
      remoteUpdateCheckPoints : function(O, P){

        O.setCheckPoints(P);
      },
      remoteUpdateConfig : function(Q, R){

        Q.setConfig(R);
      }
    },
    events : {
      "changeCheckPoints" : k,
      "changeConfig" : k
    }
  });
})();
(function(){

  var k = "render/create_poi_layer",j = ".png",h = "wialon.render.Renderer",g = "__eP",f = "render/remove_all_layers",e = "Integer",d = "Object",c = "qx.event.type.Event",b = "render/create_messages_layer",a = "/",A = "render/remove_layer",w = "",v = "__eJ",u = "/avl_hittest_pos",t = "render/set_locale",s = "/adfurl",r = "render/enable_layer",q = "changeVersion",p = "render/create_zones_layer",o = "/avl_render/",m = "report",n = "_",l = "undefined";
  qx.Class.define(h, {
    extend : qx.core.Object,
    construct : function(){

      qx.core.Object.call(this);
      this.__eJ = new Array;
    },
    properties : {
      version : {
        init : 0,
        check : e,
        event : q
      },
      reportResult : {
        init : null,
        check : d,
        nullable : true,
        apply : g
      }
    },
    members : {
      __eJ : null,
      getLayers : function(){

        return this.__eJ;
      },
      getReportLayer : function(){

        for(var i = 0;i < this.__eJ.length;i++)if(this.__eJ[i].getName().substr(0, 6) == m)return this.__eJ[i];;
        return null;
      },
      getTileUrl : function(x, y, z){

        return wialon.core.Session.getInstance().getBaseUrl() + s + this.getVersion() + o + x + n + y + n + (17 - z) + a + wialon.core.Session.getInstance().getId() + j;
      },
      setLocale : function(B, C, D){

        wialon.core.Remote.getInstance().remoteCall(t, {
          tzOffset : B,
          language : C
        }, wialon.util.Helper.wrapCallback(D));
      },
      createMessagesLayer : function(E, F){

        wialon.core.Remote.getInstance().remoteCall(b, E, qx.lang.Function.bind(this.__eK, this, wialon.util.Helper.wrapCallback(F)));
      },
      createPoiLayer : function(G, H, I, J){

        for(var i = this.__eJ.length - 1;i >= 0;i--){

          if(this.__eJ[i].getName() == G){

            this.__eJ[i].dispose();
            qx.lang.Array.remove(this.__eJ, this.__eJ[i]);
          };
        };
        wialon.core.Remote.getInstance().remoteCall(k, {
          layerName : G,
          pois : H,
          flags : I
        }, qx.lang.Function.bind(this.__eL, this, wialon.util.Helper.wrapCallback(J)));
      },
      createZonesLayer : function(K, L, M, N){

        for(var i = this.__eJ.length - 1;i >= 0;i--){

          if(this.__eJ[i].getName() == K){

            this.__eJ[i].dispose();
            qx.lang.Array.remove(this.__eJ, this.__eJ[i]);
          };
        };
        wialon.core.Remote.getInstance().remoteCall(p, {
          layerName : K,
          zones : L,
          flags : M
        }, qx.lang.Function.bind(this.__eL, this, wialon.util.Helper.wrapCallback(N)));
      },
      removeLayer : function(O, P){

        wialon.core.Remote.getInstance().remoteCall(A, {
          layerName : O.getName()
        }, qx.lang.Function.bind(this.__eM, this, wialon.util.Helper.wrapCallback(P), O));
      },
      enableLayer : function(Q, R, S){

        wialon.core.Remote.getInstance().remoteCall(r, {
          layerName : Q.getName(),
          enable : R ? 1 : 0
        }, qx.lang.Function.bind(this.__eO, this, wialon.util.Helper.wrapCallback(S), Q));
      },
      removeAllLayers : function(T){

        wialon.core.Remote.getInstance().remoteCall(f, {
        }, qx.lang.Function.bind(this.__eN, this, wialon.util.Helper.wrapCallback(T)));
      },
      hitTest : function(U, V, W, X, Y, ba){

        wialon.core.Remote.getInstance().ajaxRequest(wialon.core.Session.getInstance().getBaseUrl() + u, {
          sid : wialon.core.Session.getInstance().getId(),
          lat : U,
          lon : V,
          scale : W,
          radius : X,
          layerName : w + Y
        }, wialon.util.Helper.wrapCallback(ba), 60);
      },
      __eK : function(bb, bc, bd){

        var be = null;
        if(bc == 0 && bd){

          if(typeof bd.name != l){

            be = new wialon.render.MessagesLayer(bd);
            this.__eJ.push(be);
          };
          this.setVersion(this.getVersion() + 1);
        };
        bb(bc, be);
      },
      __eL : function(bf, bg, bh){

        var bi = null;
        if(bg == 0 && bh){

          if(typeof bh.name != l){

            bi = new wialon.render.Layer(bh);
            this.__eJ.push(bi);
          };
          this.setVersion(this.getVersion() + 1);
        };
        bf(bg, bi);
      },
      __eM : function(bj, bk, bl, bm){

        if(bl){

          bj(bl);
          return;
        };
        qx.lang.Array.remove(this.__eJ, bk);
        bk.dispose();
        this.setVersion(this.getVersion() + 1);
        bj(bl);
      },
      __eN : function(bn, bo, bp){

        if(bo){

          bn(bo);
          return;
        };
        if(this.__eJ.length){

          for(var i = 0;i < this.__eJ.length;i++)this.__eJ[i].dispose();
          qx.lang.Array.removeAll(this.__eJ);
          this.setVersion(this.getVersion() + 1);
        };
        bn(bo);
      },
      __eO : function(bq, br, bs, bt){

        if(bs){

          bq(bs);
          return;
        };
        var bu = bt.enabled ? true : false;
        if(bu != br.getEnabled()){

          br.setEnabled(bu);
          this.setVersion(this.getVersion() + 1);
        };
        bq(bs);
      },
      __eP : function(bv){

        var bw = false;
        for(var i = 0;i < this.__eJ.length;i++)if(this.__eJ[i].getName().substr(0, 6) == m){

          this.__eJ.splice(i, 1);
          bw = true;
          break;
        };
        if(bv){

          var by = bv.getLayerData();
          if(by){

            var bx = by.units ? new wialon.render.MessagesLayer(by) : new wialon.render.Layer(by);
            this.__eJ.push(bx);
            bv.setLayer(bx);
            bw = true;
          };
        };
        if(bw)this.setVersion(this.getVersion() + 1);
      }
    },
    statics : {
      PoiFlag : {
        renderLabels : 0x01,
        enableGroups : 0x02
      },
      ZonesFlag : {
        renderLabels : 0x01
      }
    },
    destruct : function(){

      this._disposeArray(v);
    },
    events : {
      "changeVersion" : c
    }
  });
})();
(function(){

  var b = "wialon.render.Layer",a = "Boolean";
  qx.Class.define(b, {
    extend : qx.core.Object,
    construct : function(c){

      qx.core.Object.call(this, c);
      this._data = c;
    },
    properties : {
      enabled : {
        init : true,
        check : a
      }
    },
    members : {
      _data : null,
      getName : function(){

        return this._data.name;
      },
      getBounds : function(){

        return this._data.bounds;
      }
    }
  });
})();
(function(){

  var k = "&msgIndex=",j = "/adfurl",i = "/avl_hittest_time",h = "",g = "&layerName=",f = "&unitIndex=",e = "/avl_msg_photo.jpeg?sid=",d = "wialon.render.MessagesLayer",c = "render/delete_message",b = "render/get_messages",a = "number";
  qx.Class.define(d, {
    extend : wialon.render.Layer,
    members : {
      getUnitsCount : function(){

        return this._data.units ? this._data.units.length : 0;
      },
      getUnitId : function(l){

        if(typeof l != a)return this._data.units[0].id;
        return this._data.units[l >= 0 ? l : 0].id;
      },
      getMaxSpeed : function(m){

        if(typeof m != a)return this._data.units[0].max_speed;
        return this._data.units[m >= 0 ? m : 0].max_speed;
      },
      getMileage : function(n){

        if(typeof n != a)return this._data.units[0].mileage;
        return this._data.units[n >= 0 ? n : 0].mileage;
      },
      getMessagesCount : function(o){

        if(typeof o != a)return this._data.units[0].msgs.count;
        return this._data.units[o >= 0 ? o : 0].msgs.count;
      },
      getFirstPoint : function(p){

        if(typeof p != a)return this._data.units[0].msgs.first;
        return this._data.units[p >= 0 ? p : 0].msgs.first;
      },
      getLastPoint : function(q){

        if(typeof q != a)return this._data.units[0].msgs.last;
        return this._data.units[q >= 0 ? q : 0].msgs.last;
      },
      getMessageImageUrl : function(r, s, t){

        if(!t)t = h;
        return wialon.core.Session.getInstance().getBaseUrl() + j + t + e + wialon.core.Session.getInstance().getId() + g + this.getName() + f + r + k + s;
      },
      getMessages : function(u, v, w, x){

        wialon.core.Remote.getInstance().remoteCall(b, {
          layerName : this.getName(),
          indexFrom : v,
          indexTo : w,
          unitId : this.getUnitId(u)
        }, wialon.util.Helper.wrapCallback(x));
      },
      deleteMessage : function(y, z, A){

        wialon.core.Remote.getInstance().remoteCall(c, {
          layerName : this.getName(),
          msgIndex : z,
          unitId : this.getUnitId(y)
        }, wialon.util.Helper.wrapCallback(A));
      },
      hitTest : function(B, C, D, E){

        wialon.core.Remote.getInstance().ajaxRequest(wialon.core.Session.getInstance().getBaseUrl() + i, {
          sid : wialon.core.Session.getInstance().getId(),
          unitId : this.getUnitId(B),
          layerName : this.getName(),
          time : C,
          revert : D ? true : false
        }, wialon.util.Helper.wrapCallback(E), 60);
      }
    },
    statics : {
    }
  });
})();
(function(){

  var f = "messages/delete_message",e = "wialon.core.MessagesLoader",d = "messages/load_last",c = "messages/unload",b = "messages/get_messages",a = "messages/load_interval";
  qx.Class.define(e, {
    extend : qx.core.Object,
    members : {
      loadInterval : function(g, h, i, j, k, l, m){

        wialon.core.Remote.getInstance().remoteCall(a, {
          itemId : g,
          timeFrom : h,
          timeTo : i,
          flags : j,
          flagsMask : k,
          loadCount : l
        }, wialon.util.Helper.wrapCallback(m));
      },
      loadLast : function(n, o, p, q, r, s, t){

        wialon.core.Remote.getInstance().remoteCall(d, {
          itemId : n,
          lastTime : o,
          lastCount : p,
          flags : q,
          flagsMask : r,
          loadCount : s
        }, wialon.util.Helper.wrapCallback(t));
      },
      unload : function(u){

        wialon.core.Remote.getInstance().remoteCall(c, {
        }, wialon.util.Helper.wrapCallback(u));
      },
      getMessages : function(v, w, x){

        wialon.core.Remote.getInstance().remoteCall(b, {
          indexFrom : v,
          indexTo : w
        }, wialon.util.Helper.wrapCallback(x));
      },
      deleteMessage : function(y, z){

        wialon.core.Remote.getInstance().remoteCall(f, {
          msgIndex : y
        }, wialon.util.Helper.wrapCallback(z));
      }
    }
  });
})();
(function(){

  var v = "Data",u = "wialon.item.PluginsManager",t = "wialon.item.M",s = "mixinDef = wialon.item.M",r = "function",q = "static",p = "set",o = "u",n = "object",m = "Object",e = "string",l = "qx.event.type.Data",h = "create",c = "delete",b = "remoteUpdate",g = "remoteCreate",f = "s",j = "get",a = "undefined",k = "update",d = "modify";
  qx.Class.define(u, {
    type : q,
    statics : {
      bindPropItem : function(w, x, y, z, A){

        var D = y.substr(0, 1).toUpperCase() + y.substr(1);
        var B = D + f;
        var F = null;
        eval(s + B);
        if(qx.Class.hasMixin(w, F))return;
        var C = {
          members : {
          },
          properties : {
          },
          statics : {
          },
          events : {
          }
        };
        C.events[k + D] = l;
        C.properties[y + f] = {
          init : null,
          check : m
        };
        C.members[j + D] = function(H){

          var J = this[j + B]();
          if(!J)return null;
          var I = J[H];
          if(typeof I == a)return null;
          return I;
        };
        C.members[d + B] = function(K, L, M){

          var N = this[j + B]();
          var O = false;
          if(K && typeof K == n){

            O = K.skipFlag;
            K = wialon.util.Helper.wrapCallback(K.callback);
          } else {

            K = wialon.util.Helper.wrapCallback(K);
          };
          var P = null;
          if(L == 0 && N && M instanceof Array && M.length == 2){

            var Q = M[0];
            P = M[1];
            var R = N[Q];
            if(typeof R == a)R = null;
            if(P != null)N[Q] = P; else if(R && !O)delete N[Q];;
            if(!O && wialon.util.Json.stringify(P) != wialon.util.Json.stringify(R))this.fireDataEvent(k + D, P, R);
          };
          K(L, P);
        };
        if(z && z.length){

          C.members[h + D] = function(S, T){

            T = wialon.util.Helper.wrapCallback(T);
            if(S){

              S = qx.lang.Object.clone(S);
              S.itemId = this.getId();
              S.id = 0;
              S.callMode = h;
            };
            wialon.core.Remote.getInstance().remoteCall(z, S, qx.lang.Function.bind(this[d + B], this, T));
          };
          C.members[k + D] = function(U, V, W){

            V = wialon.util.Helper.wrapCallback(V);
            if(U){

              U = qx.lang.Object.clone(U);
              U.itemId = this.getId();
              U.callMode = typeof W == e ? W : k;
            };
            wialon.core.Remote.getInstance().remoteCall(z, U, qx.lang.Function.bind(this[d + B], this, V));
          };
          C.members[c + D] = function(X, Y, ba){

            if(typeof ba == a)ba = false;
            Y = wialon.util.Helper.wrapCallback(Y);
            wialon.core.Remote.getInstance().remoteCall(z, {
              itemId : this.getId(),
              id : X,
              callMode : c
            }, qx.lang.Function.bind(this[d + B], this, {
              callback : Y,
              skipFlag : ba
            }));
          };
        };
        if(A && A.length){

          C.members[j + B + v] = function(bb, bc, bd){

            if(bc && typeof bc == r){

              bd = bc;
              bc = 0;
            };
            bd = wialon.util.Helper.wrapCallback(bd);
            var be = {
              itemId : this.getId(),
              col : []
            };
            for(var i = 0;i < bb.length;i++){

              if(typeof bb[i].id == a)be.col.push(bb[i]); else be.col.push(bb[i].id);
            };
            be.flags = bc;
            wialon.core.Remote.getInstance().remoteCall(A, be, bd);
          };
        };
        C.statics[g + D] = function(bf, bg){

          bf[p + B](bg);
        };
        C.statics[b + D] = function(bh, bi){

          bh[d + B](null, 0, bi);
        };
        var E = wialon.core.Session.getInstance();
        E.registerProperty(x, qx.lang.Function.bind(C.statics[g + D], C));
        E.registerProperty(x + o, qx.lang.Function.bind(C.statics[b + D], C));
        var G = qx.Mixin.define(t + B, C);
        qx.Class.include(w, G);
      }
    }
  });
})();
(function(){

  var k = "const0",j = "sats",h = "lon",g = '_',f = "speed",e = ']',d = "altitude",c = ":",b = "time",a = "lat",H = '.',G = ':',F = "n",E = "wialon.item.MUnitSensor",D = ' ',C = "out",B = "unit/calc_last_message",A = "course",z = "unit/calc_sensors",y = "in",r = '^',s = '*',p = "const",q = '#',n = '/',o = '[',l = '+',m = "string",t = "",u = '-',w = ')',v = "undefined",x = '(';
  qx.Mixin.define(E, {
    members : {
      calculateSensorValue : function(I, J, K){

        if(!I)return wialon.item.MUnitSensor.invalidValue;
        if(typeof J == v || !J)J = null;
        if(typeof K == v || !K)K = null;
        return this.__eR(I, J, K, null);
      },
      remoteCalculateLastMessage : function(L, M){

        if(!L || !(L instanceof Array))L = [];
        wialon.core.Remote.getInstance().remoteCall(B, {
          sensors : L,
          unitId : this.getId()
        }, wialon.util.Helper.wrapCallback(M));
      },
      remoteCalculateMsgs : function(N, O, P, Q, R){

        wialon.core.Remote.getInstance().remoteCall(z, {
          source : N,
          unitId : this.getId(),
          indexFrom : O,
          indexTo : P,
          sensorId : Q
        }, wialon.util.Helper.wrapCallback(R));
      },
      getValue : function(S, T){

        if(!S)return wialon.item.MUnitSensor.invalidValue;
        return this.__eS(S.p, T);
      },
      __eQ : {
      },
      __eR : function(U, V, W, X){

        if(!U)return wialon.item.MUnitSensor.invalidValue;
        var Y = false;
        var bd = U.id;
        if(X){

          if(X[bd])return wialon.item.MUnitSensor.invalidValue;
        } else {

          X = new Object;
          Y = true;
        };
        X[bd] = 1;
        var bc = this.__eU(U, V, W, X);
        if(typeof (bc) == m)return bc;
        if(bc != wialon.item.MUnitSensor.invalidValue)bc = this.__eT(U, bc);
        if(U.vs && U.vt){

          var ba = this.getSensor(U.vs);
          if(!ba){

            delete X[bd];
            return wialon.item.MUnitSensor.invalidValue;
          };
          var bb = this.__eR(ba, V, W, X);
          if(bc != wialon.item.MUnitSensor.invalidValue && bb != wialon.item.MUnitSensor.invalidValue){

            if(U.vt == wialon.item.MUnitSensor.validation.logicalAnd){

              if(bc && bb)bc = 1; else bc = 0;
            } else if(U.vt == wialon.item.MUnitSensor.validation.noneZero){

              if(!bb){

                delete X[bd];
                bc = wialon.item.MUnitSensor.invalidValue;
              };
            } else if(U.vt == wialon.item.MUnitSensor.validation.mathAnd){

              bc = Math.ceil(bb) & Math.ceil(bc);
            } else if(U.vt == wialon.item.MUnitSensor.validation.logicalOr){

              if(bc || bb)bc = 1;
            } else if(U.vt == wialon.item.MUnitSensor.validation.mathOr){

              bc = Math.ceil(bb) | Math.ceil(bc);
            } else if(U.vt == wialon.item.MUnitSensor.validation.summarize)bc += bb; else if(U.vt == wialon.item.MUnitSensor.validation.subtructValidator)bc -= bb; else if(U.vt == wialon.item.MUnitSensor.validation.subtructValue)bc = bb - bc; else if(U.vt == wialon.item.MUnitSensor.validation.multiply)bc *= bb; else if(U.vt == wialon.item.MUnitSensor.validation.divideValidator){

              if(bb)bc /= bb; else bc = wialon.item.MUnitSensor.invalidValue;
            } else if(U.vt == wialon.item.MUnitSensor.validation.divideValue){

              if(bc)bc = bb / bc; else bc = wialon.item.MUnitSensor.invalidValue;
            };;;;;;;;;;
          } else if(U.vt == wialon.item.MUnitSensor.validation.replaceOnError){

            if(bc == wialon.item.MUnitSensor.invalidValue)bc = bb;
          } else bc = wialon.item.MUnitSensor.invalidValue;;
        };
        delete X[bd];
        return bc;
      },
      __eS : function(be, bf){

        if(!bf)return wialon.item.MUnitSensor.invalidValue;
        var bi = wialon.item.MUnitSensor.invalidValue;
        var bj = bf.p;
        var bg = be.split(c);
        if(bj && typeof bj[bg[0]] != v)bi = bj[bg[0]]; else if(be == f){

          if(!bf.pos)return wialon.item.MUnitSensor.invalidValue;
          bi = bf.pos.s;
        } else if(be == j){

          if(!bf.pos)return wialon.item.MUnitSensor.invalidValue;
          bi = bf.pos.sc;
        } else if(be == d){

          if(!bf.pos)return wialon.item.MUnitSensor.invalidValue;
          bi = bf.pos.z;
        } else if(be == A){

          if(!bf.pos)return wialon.item.MUnitSensor.invalidValue;
          bi = bf.pos.c;
        } else if(be == a){

          if(!bf.pos)return wialon.item.MUnitSensor.invalidValue;
          bi = bf.pos.y;
        } else if(be == h){

          if(!bf.pos)return wialon.item.MUnitSensor.invalidValue;
          bi = bf.pos.x;
        } else if(be.substr(0, 2) == y){

          if(!(bf.f & 0x2))return wialon.item.MUnitSensor.invalidValue;
          var bh = parseInt(be.substr(2));
          if(bh < 1 || bh > 32 || isNaN(bh))return this.__eT(bf.i);
          var bk = 1 << (bh - 1);
          bi = (bf.i & bk) ? 1 : 0;
        } else if(be.substr(0, 3) == C){

          if(!(bf.f & 0x4))return wialon.item.MUnitSensor.invalidValue;
          var bh = parseInt(be.substr(3));
          if(bh < 1 || bh > 32 || isNaN(bh))return this.__eT(bf.o);
          var bk = 1 << (bh - 1);
          bi = (bf.o & bk) ? 1 : 0;
        } else if(be.substr(0, 5) == p){

          bi = parseFloat(be.substr(5));
        } else if(be.substr(0, 4) == b){

          bi = bf.t;
        };;;;;;;;;;
        if(bg.length > 1 && bi != wialon.item.MUnitSensor.invalidValue){

          var bl = parseInt(bi);
          bi = (bl & (1 << (bg[1] - 1))) ? 1 : 0;
        };
        return bi;
      },
      __eT : function(bm, bn){

        if(!bm || isNaN(bn))return wialon.item.MUnitSensor.invalidValue;
        var bo = bn;
        for(var i = 0;i < bm.tbl.length;i++){

          if(i != 0 && bm.tbl[i].x > bn)return bo;
          bo = parseFloat(bm.tbl[i].a) * parseFloat(bn) + parseFloat(bm.tbl[i].b);
        };
        return bo;
      },
      __eU : function(bp, bq, br, bs){

        if(!bp || typeof bp.p != m || !bp.p.length)return wialon.item.MUnitSensor.invalidValue;
        var bu = this.__eQ[bp.p];
        if(typeof bu == v){

          bu = this.__eV(bp.p);
          if(!bu.length)return wialon.item.MUnitSensor.invalidValue;
          this.__eQ[bp.p] = bu;
        };
        var bw = [];
        var bt = 0;
        for(var i = 0;i < bu.length;i++){

          var bv = bu[i];
          var bx = bw.length;
          if(bv[0] == s && bx > 1){

            bw[bx - 2] = bw[bx - 2] * bw[bx - 1];
            bw.pop();
          } else if(bv[0] == n && bx > 1){

            if(bw[bx - 1] == 0)return wialon.item.MUnitSensor.invalidValue;
            bw[bx - 2] = bw[bx - 2] / bw[bx - 1];
            bw.pop();
          } else if(bv[0] == l && bx > 1){

            bw[bx - 2] = bw[bx - 2] + bw[bx - 1];
            bw.pop();
          } else if(bv[0] == u){

            if(bx > 1){

              bw[bx - 2] = bw[bx - 2] - bw[bx - 1];
              bw.pop();
            } else if(bx == 1)bw[bx - 1] = -bw[bx - 1];;
          } else if(bv[0] == r && bx > 1){

            bw[bx - 2] = Math.pow(bw[bx - 2], bw[bx - 1]);
            bw.pop();
          } else {

            if(bv[0] == o){

              var bp = wialon.util.Helper.searchObject(this.getSensors(), F, bv.slice(1));
              if(!bp)return wialon.item.MUnitSensor.invalidValue;
              bt = this.__eR(bp, bq, br, bs);
              if(bt == wialon.item.MUnitSensor.invalidValue)return wialon.item.MUnitSensor.invalidValue;
              bw.push(bt);
            } else {

              bt = wialon.item.MUnitSensor.invalidValue;
              if(bv[0] == q)bt = this.__eS(bv.slice(1), br); else bt = this.__eS(bv, bq);
              if(typeof (bt) == m)return bt;
              if(bt == wialon.item.MUnitSensor.invalidValue)return wialon.item.MUnitSensor.invalidValue;
              bw.push(bt);
            };
          };;;;
        };
        return bw.length == 1 ? bw[0] : wialon.item.MUnitSensor.invalidValue;
      },
      __eV : function(by){

        var bJ = by.length;
        var bI = t;
        var bC = [];
        var bD = [];
        var bB = 0;
        var bF = false;
        var bK = false;
        for(var i = 0;i < bJ;i++){

          if(by[i] == D){

            if(!bB)continue;
          } else if(by[i] == o)bF = true; else if(by[i] == e)bF = false;;;
          var bH = by[i].charCodeAt(0);
          var bG = (bH > 47 && bH < 58) || (bH > 64 && bH < 91) || (bH > 96 && bH < 123);
          if(bF || bG || by[i] == g || by[i] == q || by[i] == H || by[i] == G || (by[i] == u && bI == p)){

            bI += by[i];
            bB++;
            if(i < bJ - 1)continue;
          };
          if(bB > 1 && this.__eW(bI) == -1){

            bK = false;
            bD.push(bI);
          };
          bI = by[i];
          var bA = this.__eW(bI);
          if(bA != -1){

            if(by[i] == u && bK)bD.push(k);
            if(by[i] == x)bK = true; else bK = false;
            if(bC.length){

              if(by[i] == x)bC.push(bI); else if(by[i] == w){

                while(bC.length){

                  var bE = bC[bC.length - 1];
                  bC.pop();
                  if(bE[0] != x)bD.push(bE); else break;
                };
              } else {

                while(bC.length){

                  var bE = bC[bC.length - 1];
                  var bz = this.__eW(bE);
                  if(bz >= bA){

                    if(bE[0] != x && bE[0] != w)bD.push(bC[bC.length - 1]);
                    bC.pop();
                  } else break;
                };
                bC.push(bI);
              };
            } else bC.push(bI);
          };
          bI = t;
          bB = 0;
        };
        while(bC.length){

          var bE = bC[bC.length - 1];
          if(bE[0] != w && bE[0] != x)bD.push(bE);
          bC.pop();
        };
        if(!bD.length)bD.push(by);
        return bD;
      },
      __eW : function(bL){

        if(bL == t)return -1;
        switch(bL[0]){case r:
        return 4;case s:case n:
        return 3;case u:case l:
        return 2;case w:
        return 1;case x:
        return 0;};
        return -1;
      }
    },
    statics : {
      invalidValue : -348201.3876,
      validation : {
        logicalAnd : 0x01,
        logicalOr : 0x02,
        mathAnd : 0x03,
        mathOr : 0x04,
        summarize : 0x05,
        subtructValidator : 0x06,
        subtructValue : 0x07,
        multiply : 0x08,
        divideValidator : 0x09,
        divideValue : 0x0A,
        noneZero : 0x0B,
        replaceOnError : 0x0C
      }
    }
  });
})();
(function(){

  var d = "wialon.item.MUnitTripDetector",c = "unit/get_trip_detector",b = "unit/get_trips",a = "unit/update_trip_detector";
  qx.Mixin.define(d, {
    members : {
      getTripDetector : function(e){

        wialon.core.Remote.getInstance().remoteCall(c, {
          itemId : this.getId()
        }, wialon.util.Helper.wrapCallback(e));
      },
      getTrips : function(f, g, h, i){

        wialon.core.Remote.getInstance().remoteCall(b, {
          itemId : this.getId(),
          timeFrom : f,
          timeTo : g,
          msgsSource : h
        }, wialon.util.Helper.wrapCallback(i));
      },
      updateTripDetector : function(j, k, l, m, n, o, p, q, r){

        wialon.core.Remote.getInstance().remoteCall(a, {
          itemId : this.getId(),
          type : j,
          gpsCorrection : k,
          minSat : l,
          minMovingSpeed : m,
          minStayTime : n,
          maxMessagesDistance : o,
          minTripTime : p,
          minTripDistance : q
        }, wialon.util.Helper.wrapCallback(r));
      }
    },
    statics : {
      tripDetectionType : {
        gpsSpeed : 1,
        gpsPosition : 2,
        ignitionSensor : 3,
        mileageSensorAbsolute : 4,
        mileageSensorRelative : 5
      }
    }
  });
})();
(function(){

  var c = "wialon.item.MUnitMessagesFilter",b = "unit/get_messages_filter",a = "unit/update_messages_filter";
  qx.Mixin.define(c, {
    members : {
      getMessagesFilter : function(d){

        wialon.core.Remote.getInstance().remoteCall(b, {
          itemId : this.getId()
        }, wialon.util.Helper.wrapCallback(d));
      },
      updateMessagesFilter : function(e, f, g, h, i, j){

        wialon.core.Remote.getInstance().remoteCall(a, {
          itemId : this.getId(),
          enabled : e,
          skipInvalid : f,
          minSats : g,
          maxHdop : h,
          maxSpeed : i
        }, wialon.util.Helper.wrapCallback(j));
      }
    }
  });
})();
(function(){

  var f = "unit/registry_maintenance_event",e = "wialon.item.MUnitEventRegistrar",d = "unit/registry_status_event",c = "unit/registry_insurance_event",b = "unit/registry_custom_event",a = "unit/registry_fuel_filling_event";
  qx.Mixin.define(e, {
    members : {
      registryStatusEvent : function(g, h, i, j){

        wialon.core.Remote.getInstance().remoteCall(d, {
          date : g,
          description : h,
          params : i,
          itemId : this.getId()
        }, wialon.util.Helper.wrapCallback(j));
      },
      registryInsuranceEvent : function(k, l, m, n){

        wialon.core.Remote.getInstance().remoteCall(c, {
          type : l,
          case_num : m,
          description : k,
          itemId : this.getId()
        }, wialon.util.Helper.wrapCallback(n));
      },
      registryCustomEvent : function(o, p, x, y, q, r){

        wialon.core.Remote.getInstance().remoteCall(b, {
          date : o,
          x : x,
          y : y,
          description : p,
          violation : q,
          itemId : this.getId()
        }, wialon.util.Helper.wrapCallback(r));
      },
      registryFuelFillingEvent : function(s, t, x, y, location, u, v, w, z){

        wialon.core.Remote.getInstance().remoteCall(a, {
          date : s,
          volume : u,
          cost : v,
          location : location,
          deviation : w,
          x : x,
          y : y,
          description : t,
          itemId : this.getId()
        }, wialon.util.Helper.wrapCallback(z));
      },
      registryMaintenanceEvent : function(A, B, x, y, location, C, D, E, F, G, H, I){

        wialon.core.Remote.getInstance().remoteCall(f, {
          date : A,
          info : C,
          duration : D,
          cost : E,
          location : location,
          x : x,
          y : y,
          description : B,
          mileage : F,
          eh : G,
          done_svcs : H,
          itemId : this.getId()
        }, wialon.util.Helper.wrapCallback(I));
      }
    }
  });
})();
(function(){

  var c = "wialon.item.MUnitReportSettings",b = "unit/get_report_settings",a = "unit/update_report_settings";
  qx.Mixin.define(c, {
    members : {
      getReportSettings : function(d){

        wialon.core.Remote.getInstance().remoteCall(b, {
          itemId : this.getId()
        }, wialon.util.Helper.wrapCallback(d));
      },
      updateReportSettings : function(e, f){

        wialon.core.Remote.getInstance().remoteCall(a, {
          itemId : this.getId(),
          params : e
        }, wialon.util.Helper.wrapCallback(f));
      }
    }
  });
})();
(function(){

  var g = "unit/update_fuel_rates_params",f = "unit/update_fuel_math_params",e = "unit/update_fuel_impulse_params",d = "wialon.item.MUnitFuelSettings",c = "unit/update_fuel_level_params",b = "unit/get_fuel_settings",a = "unit/update_fuel_calc_types";
  qx.Mixin.define(d, {
    members : {
      getFuelSettings : function(h){

        wialon.core.Remote.getInstance().remoteCall(b, {
          itemId : this.getId()
        }, wialon.util.Helper.wrapCallback(h));
      },
      updateFuelCalcTypes : function(i, j){

        wialon.core.Remote.getInstance().remoteCall(a, {
          itemId : this.getId(),
          calcTypes : i
        }, wialon.util.Helper.wrapCallback(j));
      },
      updateFuelLevelParams : function(k, l, m, n, o, p, q){

        wialon.core.Remote.getInstance().remoteCall(c, {
          itemId : this.getId(),
          flags : k,
          ignoreStayTimeout : l,
          minFillingVolume : m,
          minTheftTimeout : n,
          minTheftVolume : o,
          filterQuality : p
        }, wialon.util.Helper.wrapCallback(q));
      },
      updateFuelConsMath : function(r, s, t, u, v){

        wialon.core.Remote.getInstance().remoteCall(f, {
          itemId : this.getId(),
          idling : r,
          urban : s,
          suburban : t,
          loadCoef : u
        }, wialon.util.Helper.wrapCallback(v));
      },
      updateFuelConsRates : function(w, x, y, z, A, B, C, D, E){

        wialon.core.Remote.getInstance().remoteCall(g, {
          itemId : this.getId(),
          idlingSummer : w,
          idlingWinter : x,
          consSummer : y,
          consWinter : z,
          winterMonthFrom : A,
          winterDayFrom : B,
          winterMonthTo : C,
          winterDayTo : D
        }, wialon.util.Helper.wrapCallback(E));
      },
      updateFuelConsImpulse : function(F, G, H){

        wialon.core.Remote.getInstance().remoteCall(e, {
          itemId : this.getId(),
          maxImpulses : F,
          skipZero : G
        }, wialon.util.Helper.wrapCallback(H));
      }
    },
    statics : {
      fuelCalcType : {
        math : 0x01,
        levelSensors : 0x02,
        levelSensorsMath : 0x04,
        absConsSensors : 0x08,
        impConsSensors : 0x10,
        instConsSensors : 0x20,
        rates : 0x40
      },
      fuelLevelFlag : {
        mergeSensors : 0x01,
        smoothData : 0x02,
        splitConsSensors : 0x04,
        requireStay : 0x08,
        calcByTime : 0x10,
        calcFillingsByRaw : 0x40,
        calcTheftsByRaw : 0x80,
        detectTheftsInMotion : 0x100,
        calcFillingsByTime : 0x200,
        calcTheftsByTime : 0x400,
        calcConsumptionByTime : 0x800
      }
    }
  });
})();
(function(){

  var a = "wialon.item.MZone";
  qx.Mixin.define(a, {
    members : {
    },
    statics : {
      flags : {
        area : 0x00000001
      }
    }
  });
})();
(function(){

  var j = "resource/cleanup_driver_interval",i = "changeDriverUnits",h = "wialon.item.MDriver",g = "/1/",f = "/2/",e = "changeTrailerUnits",d = "resource/get_driver_bindings",c = "resource/update_trailer_units",b = "resource/bind_unit_driver",a = "resource/cleanup_trailer_interval",y = "resource/get_trailer_bindings",x = "resource/bind_unit_trailer",w = "resource/update_driver_units",v = ".png",u = "Array",t = "resource/upload_trailer_image",s = "qx.event.type.Data",r = "resource/upload_driver_image",q = "trlrun",p = "/avl_driver_image/",n = "undefined",o = "drvrun",l = "object",m = "/",k = "number";
  qx.Mixin.define(h, {
    construct : function(){

      var z = wialon.core.Session.getInstance();
      z.registerProperty(o, qx.lang.Function.bind(function(A, B){

        A.setDriverUnits(B);
      }, this));
      z.registerProperty(q, qx.lang.Function.bind(function(C, D){

        C.setTrailerUnits(D);
      }, this));
    },
    properties : {
      driverUnits : {
        init : null,
        check : u,
        event : i
      },
      trailerUnits : {
        init : null,
        check : u,
        event : e
      }
    },
    members : {
      updateDriverUnits : function(E, F){

        wialon.core.Remote.getInstance().remoteCall(w, {
          itemId : this.getId(),
          units : E
        }, qx.lang.Function.bind(this._onUpdateProperties, this, wialon.util.Helper.wrapCallback(F)));
      },
      updateTrailerUnits : function(G, H){

        wialon.core.Remote.getInstance().remoteCall(c, {
          itemId : this.getId(),
          units : G
        }, qx.lang.Function.bind(this._onUpdateProperties, this, wialon.util.Helper.wrapCallback(H)));
      },
      getDriverImageUrl : function(I, J){

        if(typeof J == n || !J)J = 32;
        return wialon.core.Session.getInstance().getBaseUrl() + p + this.getId() + m + I.id + m + J + g + I.ck + v;
      },
      getTrailerImageUrl : function(K, L){

        if(typeof L == n || !L)L = 32;
        return wialon.core.Session.getInstance().getBaseUrl() + p + this.getId() + m + K.id + m + L + f + K.ck + v;
      },
      setDriverImage : function(M, N, O){

        if(typeof N == l && typeof N.resId == k && typeof N.drvId == k)return wialon.core.Remote.getInstance().remoteCall(r, {
          itemId : this.getId(),
          driverId : M.id,
          oldItemId : N.resId,
          oldDrvId : N.drvId
        }, O);
        return wialon.core.Uploader.getInstance().uploadFiles([N], r, {
          itemId : this.getId(),
          driverId : M.id
        }, O);
      },
      setTrailerImage : function(P, Q, R){

        if(typeof Q == l && typeof Q.resId == k && typeof Q.trId == k)return wialon.core.Remote.getInstance().remoteCall(t, {
          itemId : this.getId(),
          trailerId : P.id,
          oldItemId : Q.resId,
          oldTrId : Q.trId
        }, R);
        return wialon.core.Uploader.getInstance().uploadFiles([Q], t, {
          itemId : this.getId(),
          trailerId : P.id
        }, R);
      },
      bindDriverToUnit : function(S, T, U, V, W){

        var X = 0;
        var Y = 0;
        if(S)X = S.id;
        if(T)Y = T.getId();
        wialon.core.Remote.getInstance().remoteCall(b, {
          resourceId : this.getId(),
          driverId : X,
          time : U,
          unitId : Y,
          mode : V
        }, wialon.util.Helper.wrapCallback(W));
      },
      bindTrailerToUnit : function(ba, bb, bc, bd, be){

        var bf = 0;
        var bg = 0;
        if(ba)bf = ba.id;
        if(bb)bg = bb.getId();
        wialon.core.Remote.getInstance().remoteCall(x, {
          resourceId : this.getId(),
          trailerId : bf,
          time : bc,
          unitId : bg,
          mode : bd
        }, wialon.util.Helper.wrapCallback(be));
      },
      cleanupDriverInterval : function(bh, bi, bj, bk){

        var bl = 0;
        if(bh)bl = bh.id;
        wialon.core.Remote.getInstance().remoteCall(j, {
          resourceId : this.getId(),
          driverId : bl,
          timeFrom : bi,
          timeTo : bj
        }, wialon.util.Helper.wrapCallback(bk));
      },
      cleanupTrailerInterval : function(bm, bn, bo, bp){

        var bq = 0;
        if(bm)bq = bm.id;
        wialon.core.Remote.getInstance().remoteCall(a, {
          resourceId : this.getId(),
          trailerId : bq,
          timeFrom : bn,
          timeTo : bo
        }, wialon.util.Helper.wrapCallback(bp));
      },
      getDriverBindings : function(br, bs, bt, bu, bv){

        var bw = 0;
        var bx = 0;
        if(bs)bw = bs.id;
        if(br)bx = br.getId();
        wialon.core.Remote.getInstance().remoteCall(d, {
          resourceId : this.getId(),
          unitId : bx,
          driverId : bw,
          timeFrom : bt,
          timeTo : bu
        }, wialon.util.Helper.wrapCallback(bv));
      },
      getTrailerBindings : function(by, bz, bA, bB, bC){

        var bD = 0;
        var bE = 0;
        if(bz)bD = bz.id;
        if(by)bE = by.getId();
        wialon.core.Remote.getInstance().remoteCall(y, {
          resourceId : this.getId(),
          unitId : bE,
          trailerId : bD,
          timeFrom : bA,
          timeTo : bB
        }, wialon.util.Helper.wrapCallback(bC));
      }
    },
    statics : {
      registerDriverProperties : function(){

        var bF = wialon.core.Session.getInstance();
        bF.registerProperty(o, this.remoteUpdateDriverUnits);
        bF.registerProperty(q, this.remoteUpdateTrailerUnits);
      },
      remoteUpdateDriverUnits : function(bG, bH){

        bG.setDriverUnits(bH);
      },
      remoteUpdateTrailerUnits : function(bI, bJ){

        bI.setTrailerUnits(bJ);
      },
      flags : {
        driver : 0x01,
        trailer : 0x02,
        assignmentRestriction : 0x04
      }
    },
    events : {
      "changeDriverUnits" : s,
      "changeTrailerUnits" : s
    }
  });
})();
(function(){

  var j = "create_account",i = "account/enable_account",h = "account/update_sub_plans",g = "account/get_account_history",f = "update_account_min_days",e = "account/update_dealer_rights",d = "account/create_account",c = "account/update_min_days",b = "switch_account",a = "update_account_history_period",y = "account/update_flags",x = "account/do_payment",w = "wialon.item.MAccount",v = "update_account_flags",u = "create_account_service",t = "account/delete_account",s = "update_dealer_rights",r = "update_account_service",q = "account/update_plan",p = "update_account_plan",n = "account/update_history_period",o = "delete_account_service",l = "account/get_account_data",m = "update_account_subplans",k = "account/update_billing_service";
  qx.Mixin.define(w, {
    members : {
      getAccountData : function(z){

        wialon.core.Remote.getInstance().remoteCall(l, {
          itemId : this.getId()
        }, wialon.util.Helper.wrapCallback(z));
      },
      getAccountHistory : function(A, B, C){

        wialon.core.Remote.getInstance().remoteCall(g, {
          itemId : this.getId(),
          days : A,
          tz : B
        }, wialon.util.Helper.wrapCallback(C));
      },
      updateDealerRights : function(D, E){

        wialon.core.Remote.getInstance().remoteCall(e, {
          itemId : this.getId(),
          enable : D
        }, wialon.util.Helper.wrapCallback(E));
      },
      updatePlan : function(F, G){

        wialon.core.Remote.getInstance().remoteCall(q, {
          itemId : this.getId(),
          plan : F
        }, wialon.util.Helper.wrapCallback(G));
      },
      updateFlags : function(H, I){

        wialon.core.Remote.getInstance().remoteCall(y, {
          itemId : this.getId(),
          flags : H
        }, wialon.util.Helper.wrapCallback(I));
      },
      updateMinDays : function(J, K){

        wialon.core.Remote.getInstance().remoteCall(c, {
          itemId : this.getId(),
          minDays : J
        }, wialon.util.Helper.wrapCallback(K));
      },
      updateHistoryPeriod : function(L, M){

        wialon.core.Remote.getInstance().remoteCall(n, {
          itemId : this.getId(),
          historyPeriod : L
        }, wialon.util.Helper.wrapCallback(M));
      },
      updateBillingService : function(name, N, O, P, Q){

        wialon.core.Remote.getInstance().remoteCall(k, {
          itemId : this.getId(),
          name : name,
          type : N,
          intervalType : O,
          costTable : P
        }, wialon.util.Helper.wrapCallback(Q));
      },
      enableAccount : function(R, S){

        wialon.core.Remote.getInstance().remoteCall(i, {
          itemId : this.getId(),
          enable : R
        }, wialon.util.Helper.wrapCallback(S));
      },
      updateSubPlans : function(T, U){

        wialon.core.Remote.getInstance().remoteCall(h, {
          itemId : this.getId(),
          plans : T
        }, wialon.util.Helper.wrapCallback(U));
      },
      doPayment : function(V, W, X, Y){

        wialon.core.Remote.getInstance().remoteCall(x, {
          itemId : this.getId(),
          balanceUpdate : V,
          daysUpdate : W,
          description : X
        }, wialon.util.Helper.wrapCallback(Y));
      },
      createAccount : function(ba, bb){

        wialon.core.Remote.getInstance().remoteCall(d, {
          itemId : this.getId(),
          plan : ba
        }, wialon.util.Helper.wrapCallback(bb));
      },
      deleteAccount : function(bc){

        wialon.core.Remote.getInstance().remoteCall(t, {
          itemId : this.getId()
        }, wialon.util.Helper.wrapCallback(bc));
      }
    },
    statics : {
      billingPlanFlag : {
        blockAccount : 0x1,
        denyServices : 0x2,
        allowUnknownServices : 0x4,
        restrictDeviceListedOnly : 0x8,
        restrictDeviceNotListedOnly : 0x10,
        subtractDays : 0x20,
        overridePlanFlags : 0x40
      },
      billingIntervalType : {
        none : 0,
        hourly : 1,
        daily : 2,
        weekly : 3,
        monthly : 4
      },
      billingServiceType : {
        onDemand : 1,
        periodic : 2
      },
      logMessageAction : {
        accountCreated : j,
        accountSwitched : b,
        accountUpdateDealerRights : s,
        accountUpdateFlags : v,
        accountUpdateMinDays : f,
        accountUpdatedHistoryPeriod : a,
        accountUpdatePlan : p,
        accountUpdateSubplans : m,
        accountCreatedService : u,
        accountUpdatedService : r,
        accountDeletedService : o
      }
    }
  });
})();
(function(){

  var c = "report/cleanup_result",b = "report/exec_report",a = "wialon.item.MReport";
  qx.Mixin.define(a, {
    members : {
      execReport : function(d, e, f, g, h){

        var i = null;
        if(!d.id)i = d;
        wialon.core.Remote.getInstance().remoteCall(b, {
          reportResourceId : this.getId(),
          reportTemplateId : d.id,
          reportTemplate : i,
          reportObjectId : e,
          reportObjectSecId : f,
          interval : g
        }, qx.lang.Function.bind(this.__eX, this, wialon.util.Helper.wrapCallback(h)), 180);
      },
      cleanupResult : function(j){

        wialon.core.Remote.getInstance().remoteCall(c, {
        }, qx.lang.Function.bind(this.__eY, this, wialon.util.Helper.wrapCallback(j)));
      },
      __eX : function(k, l, m){

        var n = null;
        if(l == 0 && m){

          n = new wialon.report.ReportResult(m);
          var o = wialon.core.Session.getInstance().getRenderer();
          if(o)o.setReportResult(n);
        };
        k(l, n);
      },
      __eY : function(p, q, r){

        var s = wialon.core.Session.getInstance().getRenderer();
        if(s)s.setReportResult(null);
        p(q);
      }
    },
    statics : {
      intervalFlag : {
        absolute : 0x00,
        useCurrentTime : 0x01,
        prevHour : 0x40,
        prevDay : 0x02,
        prevWeek : 0x04,
        prevMonth : 0x08,
        prevYear : 0x10,
        currTimeAndPrev : 0x20
      },
      tableFlag : {
      },
      columnFlag : {
      }
    }
  });
})();
(function(){

  var k = "report/get_result_subrows",j = "&svc=report/export_result&params=",i = "&svc=report/get_result_photo&params=",h = "report/select_result_rows",g = "wialon.report.ReportResult",f = "&svc=report/get_result_map&params=",e = "report/get_result_rows",d = "report/hittest_chart",c = "&svc=report/get_result_chart&params=",b = "Object",a = /*"/wialon/ajax.html?sid="*/"/ajax.html?sid=";
  qx.Class.define(g, {
    extend : qx.core.Object,
    construct : function(l){

      qx.core.Object.call(this, l);
      this._data = l;
    },
    properties : {
      layer : {
        init : null,
        check : b,
        nullable : true
      }
    },
    members : {
      _data : null,
      getTables : function(){

        return this._data.reportResult.tables;
      },
      isRendered : function(){

        return this._data.reportResult.msgsRendered;
      },
      isEmpty : function(){

        var m = 0,n = 0,o = 0;
        if(this._data.reportResult.tables)m = this._data.reportResult.tables.length;
        if(this._data.reportResult.stats)n = this._data.reportResult.stats.length;
        if(this._data.reportResult.attachments)o = this._data.reportResult.attachments.length;
        if(!m && !n && !o)return true;
        return false;
      },
      getTableRows : function(p, q, r, s){

        wialon.core.Remote.getInstance().remoteCall(e, {
          tableIndex : p,
          indexFrom : q,
          indexTo : r
        }, wialon.util.Helper.wrapCallback(s));
      },
      getRowDetail : function(t, u, v){

        wialon.core.Remote.getInstance().remoteCall(k, {
          tableIndex : t,
          rowIndex : u
        }, wialon.util.Helper.wrapCallback(v));
      },
      selectRows : function(w, x, y){

        wialon.core.Remote.getInstance().remoteCall(h, {
          tableIndex : w,
          config : x
        }, wialon.util.Helper.wrapCallback(y));
      },
      getMessages : function(z, A, B){

        B = wialon.util.Helper.wrapCallback(B);
        var C = this.getLayer();
        if(C && C instanceof wialon.render.MessagesLayer)C.getMessages(0, z, A, B); else B(3);
      },
      getStatistics : function(){

        return this._data.reportResult.stats;
      },
      getAttachments : function(){

        return this._data.reportResult.attachments;
      },
      getChartUrl : function(D, E, F, G, H, I, J, K){

        var L = {
          reportResourceId : this._data.reportResourceId,
          attachmentIndex : D,
          action : E,
          width : F,
          height : G,
          autoScaleY : H,
          pixelFrom : I,
          pixelTo : J,
          flags : K,
          rnd : (new Date).getTime()
        };
        return wialon.core.Session.getInstance().getBaseUrl() + a + wialon.core.Session.getInstance().getId() + c + wialon.util.Json.stringify(L);
      },
      hitTestChart : function(M, N, O, P){

        wialon.core.Remote.getInstance().remoteCall(d, {
          attachmentIndex : M,
          datasetIndex : N,
          pixelX : O
        }, wialon.util.Helper.wrapCallback(P));
      },
      getExportUrl : function(Q, R){

        var S = qx.lang.Object.clone(R);
        S.format = Q;
        return wialon.core.Session.getInstance().getBaseUrl() + a + wialon.core.Session.getInstance().getId() + j + wialon.util.Json.stringify(S);
      },
      getMapUrl : function(T, U){

        var V = {
          width : T,
          height : U,
          rnd : (new Date).getTime()
        };
        return wialon.core.Session.getInstance().getBaseUrl() + a + wialon.core.Session.getInstance().getId() + f + wialon.util.Json.stringify(V);
      },
      getPhotoUrl : function(W, X){

        var Y = {
          attachmentIndex : W,
          border : X,
          rnd : (new Date).getTime()
        };
        return wialon.core.Session.getInstance().getBaseUrl() + a + wialon.core.Session.getInstance().getId() + i + wialon.util.Json.stringify(Y);
      },
      getLayerData : function(){

        return this._data.reportLayer;
      }
    },
    statics : {
      chartFlag : {
        headerTop : 0x01,
        headerBottom : 0x02,
        headerNone : 0x04,
        axisUpDown : 0x40,
        axisDownUp : 0x80,
        legendTop : 0x100,
        legendBottom : 0x200,
        legendLeft : 0x400,
        legendShowAlways : 0x1000
      },
      exportFormat : {
        html : 0x1,
        pdf : 0x2,
        xls : 0x4,
        xlsx : 0x8,
        xml : 0x10,
        csv : 0x20
      }
    }
  });
})();
(function(){

  var g = "apps/list",f = "apps/update",e = "apps/delete",d = "apps/check_top_service",c = "apps/create",b = "wialon.util.Apps",a = "static";
  qx.Class.define(b, {
    type : a,
    statics : {
      createApplication : function(name, h, i, j, k, l, m, n, o){

        o = wialon.util.Helper.wrapCallback(o);
        wialon.core.Remote.getInstance().remoteCall(c, {
          name : name,
          description : h,
          url : i,
          flags : j,
          langs : k,
          sortOrder : l,
          requiredServicesList : m,
          billingPlans : n
        }, o);
      },
      updateApplication : function(p, name, q, r, s, t, u, v, w, x){

        x = wialon.util.Helper.wrapCallback(x);
        wialon.core.Remote.getInstance().remoteCall(f, {
          id : p,
          name : name,
          description : q,
          url : r,
          flags : s,
          langs : t,
          sortOrder : u,
          requiredServicesList : v,
          billingPlans : w
        }, x);
      },
      deleteApplication : function(y, z){

        z = wialon.util.Helper.wrapCallback(z);
        wialon.core.Remote.getInstance().remoteCall(e, {
          id : y
        }, z);
      },
      getApplications : function(A, B, C){

        C = wialon.util.Helper.wrapCallback(C);
        wialon.core.Remote.getInstance().remoteCall(g, {
          manageMode : A,
          filterLang : B
        }, C);
      },
      remoteCheckTopService : function(D){

        D = wialon.util.Helper.wrapCallback(D);
        wialon.core.Remote.getInstance().remoteCall(d, {
        }, D);
      },
      urlFlags : {
        sid : 0x00000001,
        user : 0x00000002,
        baseUrl : 0x00000004,
        hostUrl : 0x00000008,
        lang : 0x00000010,
        authHash : 0x00000020
      }
    }
  });
})();
(function(){

  var j = "update_agro_machine",i = "plots",h = "agro/update_machine",g = "delete_agro_crop",f = "create_agro_machine",e = "agroUnit",d = "crop",c = "agro/update_plot_group",b = "agro/get_plot_data",a = "plotGroup",ba = "create_agro_equip",Y = "aplt",X = "machine",W = "delete_agro_cul_type",V = "delete_agro_machine",U = "update_agro_plot_group",T = "delete_agro_equip",S = "cultivationType",R = "acltt",Q = "delete_agro_msg",q = "create_agro_plot_group",r = "plot",o = "agro/update_equipment",p = "wialon.agro.MAgro",m = "update_agro_crop",n = "amch",k = "update_agro_unit_cfg",l = "equipment",u = "update_agro_fuel",v = "cultivationTypes",D = "machines",B = "delete_agro_plot",I = "agro/update_crop",F = "apltg",M = "equipments",K = "plotGroups",x = "delete_agro_plot_group",P = "create_agro_cul_type",O = "import_agro_plots",N = "agro/update_plot",w = "agro/update_cultivation_type",z = "aequ",A = "create_agro_crop",C = "fuelRates",E = "create_agro_plot",G = "update_agro_plot",J = "update_agro_props",L = "crops",s = "update_agro_equip",t = "update_agro_cul_type",y = "undefined",H = "aclt";
  qx.Mixin.define(p, {
    members : {
      loadAgroLibrary : function(bb){

        if(!this._libraries)return false;
        if(typeof this._libraries[bb] != y)return true;
        if(bb == i)wialon.item.PluginsManager.bindPropItem(wialon.item.Resource, Y, r, N, b); else if(bb == K)wialon.item.PluginsManager.bindPropItem(wialon.item.Resource, F, a, c); else if(bb == D)wialon.item.PluginsManager.bindPropItem(wialon.item.Resource, n, X, h); else if(bb == M)wialon.item.PluginsManager.bindPropItem(wialon.item.Resource, z, l, o); else if(bb == v)wialon.item.PluginsManager.bindPropItem(wialon.item.Resource, R, S, w); else if(bb == L)wialon.item.PluginsManager.bindPropItem(wialon.item.Resource, H, d, I); else if(bb == C)qx.Class.include(wialon.item.Resource, wialon.agro.MFuelRates); else if(bb == e)qx.Class.include(wialon.item.Unit, wialon.agro.MAgroUnit); else return false;;;;;;;;
        this._libraries[bb] = 1;
        return true;
      },
      logMessageAction : {
        agroCreatedCrop : A,
        agroUpdatedCrop : m,
        agroDeletedCrop : g,
        agroCreatedCultivationType : P,
        agroUpdatedCultivationType : t,
        agroDeletedCultivationType : W,
        agroCreatedEquipment : ba,
        agroUpdatedEquipment : s,
        agroDeletedEquipment : T,
        agroCreatedMachine : f,
        agroUpdatedMachine : j,
        agroDeletedMachine : V,
        agroCreatedPlot : E,
        agroUpdatedPlot : G,
        agroDeletedPlot : B,
        agroCreatedPlotGroup : q,
        agroUpdatedPlotGroup : U,
        agroDeletedPlotGroup : x,
        agroDeletedMessage : Q,
        agroUpdatedProperties : J,
        agroUpdatedUnitSettings : k,
        agroUpdatedFuelRates : u,
        agroImportedAgroPlots : O
      }
    }
  });
})();
(function(){

  var c = "wialon.agro.MFuelRates",b = "agro/get_fuel_rates",a = "agro/update_fuel_rates";
  qx.Mixin.define(c, {
    members : {
      getFuelRates : function(d){

        wialon.core.Remote.getInstance().remoteCall(b, {
          itemId : this.getId()
        }, wialon.util.Helper.wrapCallback(d));
      },
      updateFuelRates : function(e, f){

        wialon.core.Remote.getInstance().remoteCall(a, {
          itemId : this.getId(),
          rates : e
        }, wialon.util.Helper.wrapCallback(f));
      }
    }
  });
})();
(function(){

  var c = "agro/update_agro_props",b = "wialon.agro.MAgroUnit",a = "agro/get_agro_props";
  qx.Mixin.define(b, {
    members : {
      getAgroProps : function(d){

        wialon.core.Remote.getInstance().remoteCall(a, {
          itemId : this.getId()
        }, wialon.util.Helper.wrapCallback(d));
      },
      updateAgroProps : function(e, f){

        wialon.core.Remote.getInstance().remoteCall(c, {
          itemId : this.getId(),
          props : e
        }, wialon.util.Helper.wrapCallback(f));
      }
    }
  });
})();
(function(){

  var n = "Integer",m = "wialon.util.MDataFlagsHelper",l = "",k = "itemCreated",h = "qx.event.type.Event",g = "*",f = "type",e = "col",d = "undefined",c = "string",a = "object",b = "id";
  qx.Mixin.define(m, {
    members : {
      properties : {
        newItemsCheckingTimeout : {
          init : 600,
          check : n
        }
      },
      startBatch : function(){

        if(this.__dz)return 0;
        this.__dz = new Array;
        return 1;
      },
      finishBatch : function(o){

        o = wialon.util.Helper.wrapCallback(o);
        if(!this.__dz){

          o(2);
          return;
        };
        if(!this.__dz.length){

          this.__dz = null;
          o(0);
          return;
        };
        this.__fm(this.__dz);
        this.__dz = null;
      },
      addItems : function(p, q, r){

        r = wialon.util.Helper.wrapCallback(r);
        if(typeof q != a)return r(2);
        var s = {
          owner : p,
          spec : q,
          callback : r,
          mode : 1
        };
        if(this.__dz)this.__dz.push(s); else this.__fm([s]);
      },
      removeItems : function(t, u, v){

        v = wialon.util.Helper.wrapCallback(v);
        if(typeof u != a)return v(2);
        var w = {
          owner : t,
          spec : u,
          callback : v,
          mode : 2
        };
        if(this.__dz)this.__dz.push(w); else this.__fm([w]);
      },
      getItemsByOwner : function(x, y){

        if(typeof x != c || !x.length)return [];
        var z = [];
        for(var B in this.__fj){

          var A = false;
          var C = wialon.core.Session.getInstance().getItem(B);
          if(!C)continue;
          if(y && y != C.getType())continue;
          if(this.__fj[B][x])z.push(C);
        };
        return z;
      },
      getItemDataFlags : function(D, E){

        if(typeof D != c || !D.length)return 0;
        var F = this.__fj[E];
        if(!F || !F[D])return 0;
        return F[D];
      },
      getItemByOwner : function(G, H){

        if(typeof G != c || !G.length)return null;
        var I = this.__fj[H];
        if(!I || !I[G])return null;
        return wialon.core.Session.getInstance().getItem(H);
      },
      startItemsCreationChecking : function(J){

        if(typeof this.__fk[J] != d)return;
        this.__fk[J] = {
        };
        this.findNewItems(J, true);
      },
      finishItemsCreationChecking : function(K){

        if(typeof this.__fk[K] == d)return;
        delete this.__fk[K];
      },
      findNewItems : function(L, M){

        clearTimeout(this.__fi);
        wialon.core.Remote.getInstance().startBatch();
        for(var O in this.__fk){

          if(L && L != O)continue;
          var N = qx.lang.Function.bind(function(P, Q, R){

            if(Q)return;
            var V = [];
            var U = [];
            for(var i = 0;i < R.items.length;i++){

              if(this.__fk[P.itemsType][R.items[i].getId()])continue;
              this.__fk[P.itemsType][R.items[i].getId()] = 1;
              V.push({
                type : b,
                data : R.items[i].getId(),
                flags : 0,
                mode : 0
              });
              V.push({
                type : b,
                data : R.items[i].getId(),
                flags : wialon.item.Item.dataFlag.base,
                mode : 1
              });
              U.push(R.items[i].getId());
            };
            if(!P.skipEvent && U.length > 0){

              wialon.core.Session.getInstance().updateDataFlags(V, qx.lang.Function.bind(function(X, Y){

                if(Y)return;
                var ba = [];
                for(var i = 0;i < X.length;i++){

                  var bb = wialon.core.Session.getInstance().getItem(X[i]);
                  if(!bb)continue;
                  ba.push(bb);
                };
                wialon.core.Session.getInstance().fireDataEvent(k, ba, null);
              }, this, U));
            };
            var S = 0;
            for(var W in this.__fk[P.itemsType])S++;
            if(S > R.items.length){

              for(var W in this.__fk[P.itemsType]){

                var T = 0;
                for(var i = 0;i < R.items.length;i++){

                  if(R.items[i].getId() == W){

                    T = W;
                    break;
                  };
                };
                if(T)continue;
                delete this.__fk[P.itemsType][W];
                this._onItemDeleted(this.getItem(W));
              };
            };
          }, this, {
            itemsType : O,
            skipEvent : M ? 1 : 0
          });
          wialon.core.Session.getInstance().searchItems({
            itemsType : O,
            propName : g,
            propValueMask : g,
            sortType : l
          }, 1, wialon.item.Item.dataFlag.base, 0, 0xFFFFFFFF, N);
        };
        wialon.core.Remote.getInstance().finishBatch(qx.lang.Function.bind(function(){

          this.__fi = setTimeout(qx.lang.Function.bind(this.findNewItems, this), this.__fl * 1000);
        }, this));
      },
      __fj : {
      },
      __dz : null,
      __fi : null,
      __fk : {
      },
      __fl : 600,
      __fm : function(bc){

        if(!bc instanceof Array)return;
        wialon.core.Remote.getInstance().startBatch();
        for(var i = 0;i < bc.length;i++){

          var bg = bc[i];
          if(typeof bg != a)continue;
          bg.spec.mode = bg.mode;
          if(bg.mode == 1){

            var bj = qx.lang.Function.bind(this.__fn, this, bg);
            wialon.core.Session.getInstance().updateDataFlags([bg.spec], bj);
          } else if(bg.mode == 2){

            var bh = [];
            if(bg.spec.type == b)bh.push(bg.spec.data); else if(bg.spec.type == e)bh = bh.concat(bg.spec.data); else if(bg.spec.type == f){

              for(var bn in this.__fj){

                var be = wialon.core.Session.getInstance().getItem(bn);
                if(be && be.getType() == bg.spec.data)bh.push(bn);
              };
            };;
            if(!bh.length)continue;
            var bd = {
            };
            var bl = bg.spec.flags;
            for(var i = 0;i < bh.length;i++){

              var bf = this.__fj[bh[i]];
              if(!bf)continue;
              if(!bf[bg.owner])continue;
              for(var j = 0;j < 64;j++){

                var bk = (1 << j);
                if(!(bl & bk) || !(bf[bg.owner] & bk))continue;
                bf[bg.owner] ^= bk;
                var bi = true;
                for(var bm in bf)if(bf.hasOwnProperty(bm) && (bf[bm] & bk)){

                  bi = false;
                  break;
                };
                if(bi){

                  if(typeof bd[bh[i]] == d)bd[bh[i]] = {
                    type : b,
                    data : bh[i],
                    flags : 0,
                    mode : 2
                  };
                  bd[bh[i]].flags |= bk;
                };
              };
            };
            for(var bn in bd)if(bd.hasOwnProperty(bn))wialon.core.Session.getInstance().updateDataFlags([bd[bn]]);;
            if(bg.callback)bg.callback();
          };
        };
        wialon.core.Remote.getInstance().finishBatch();
      },
      __fn : function(bo, bp){

        var br = (new Date()).getTime();
        if(!bo)return;
        if(bp)return bo.callback ? bo.callback() : null;
        var bs = [];
        if(bo.spec.type == b)bs.push(bo.spec.data); else if(bo.spec.type == e)bs = bs.concat(bo.spec.data); else if(bo.spec.type == f){

          var bq = wialon.core.Session.getInstance().getItems(bo.spec.data);
          for(var i = 0;i < bq.length;i++)bs.push(bq[i].getId());
        };;
        for(var i = 0;i < bs.length;i++){

          var bt = bs[i];
          if(!this.__fj[bt])this.__fj[bt] = {
          };
          if(!this.__fj[bt][bo.owner])this.__fj[bt][bo.owner] = 0;
          this.__fj[bt][bo.owner] |= bo.spec.flags;
        };
        return bo.callback ? bo.callback() : null;
      }
    },
    events : {
      "itemCreated" : h
    }
  });
})();
(function(){

  var c = "route/optimize",b = "static",a = "wialon.util.Routing";
  qx.Class.define(a, {
    type : b,
    statics : {
      remoteOptimizeCourierRoute : function(d, e, f, g){

        g = wialon.util.Helper.wrapCallback(g);
        wialon.core.Remote.getInstance().remoteCall(c, {
          pathMatrix : d,
          pointSchedules : e,
          flags : f
        }, g);
      },
      remoteOptimizeFlag : {
        fitSchedule : 0x1,
        optimizeDuration : 0x2,
        optimizeTime : 0x4,
        fixFirstPoint : 0x8,
        fixLastPoint : 0x10
      }
    }
  });
})();
(function(){

  var k = "plots",j = "Comic Sans MS",h = "render",g = "wialon.agro.Helper",f = "agro/update_unit_settings",e = "Courier New",d = "Arial Black",c = "static",b = "agro/get_units_in_plots",a = "DejaVuSans-BoldOblique",M = "Impact",L = "Arial",K = "Georgia",J = "DejaVuSans",I = "agro/delete_cultivation_msg",H = "Times New Roman",G = "Trebuchet MS",F = "clear",E = "register_ex",D = "&svc=agro/export_plots&params=",r = "agro/convert_plots",s = "Verdana",p = "&svc=agro/print_plots&params=",q = "agro/update_cultivation_msg",n = "DejaVuSans-Oblique",o = "DejaVuSans-Bold",l = "agro/create_plots_layer",m = "agro/get_unit_settings",t = "agro/import_plots",u = "uploadTrack",x = "",w = "register",z = "agro/upload_plot",y = /*"/wialon/ajax.html?sid="*/"/wialon/ajax.html?sid=",B = "agro/get_cultivations",A = "upload",v = "undefined",C = "agro/upload_cultivation";
  qx.Class.define(g, {
    type : c,
    statics : {
      getCultivations : function(N, O, P, Q, R, S, T){

        var V = wialon.core.Session.getInstance().getRenderer();
        if(!V)return;
        var U = V.getLayers();
        for(var i = U.length - 1;i >= 0;i--){

          if(U[i].getName() == R){

            U[i].dispose();
            qx.lang.Array.remove(U, U[i]);
          };
        };
        wialon.core.Remote.getInstance().remoteCall(B, {
          plotItemId : N,
          plotId : O,
          timeFrom : P,
          timeTo : Q,
          layerName : typeof R == v ? x : R,
          paintingScheme : S ? S : null
        }, qx.lang.Function.bind(this.__fa, this, wialon.util.Helper.wrapCallback(T)), 300);
      },
      getCultivationsList : function(W, X, Y, ba, bb){

        wialon.core.Remote.getInstance().remoteCall(B, {
          plotItemId : W,
          plotId : X,
          timeFrom : Y,
          timeTo : ba,
          layerName : x,
          paintingScheme : null
        }, wialon.util.Helper.wrapCallback(bb), 300);
      },
      uploadCultivation : function(bc, bd, be, bf){

        wialon.core.Uploader.getInstance().uploadFiles(bc, C, {
          tzOffset : bd,
          color : be,
          callMode : A
        }, qx.lang.Function.bind(this.__fb, this, wialon.util.Helper.wrapCallback(bf)), true);
      },
      updateCultivationLayer : function(bg, bh, bi, bj){

        wialon.core.Remote.getInstance().remoteCall(C, {
          time : bg,
          action : bh,
          color : bi,
          callMode : h
        }, wialon.util.Helper.wrapCallback(bj), 300);
      },
      uploadUnitCultivation : function(bk, bl, bm, bn, bo, bp, bq, br, bs, bt, bu, bv, bw, bx){

        wialon.core.Remote.getInstance().remoteCall(C, {
          unitId : bk,
          timeFrom : bl,
          timeTo : bm,
          switchSensorId : bn,
          widthSensorId : bo,
          flags : bp,
          tzOffset : bq,
          color : br,
          defaultWidth : bs,
          plotItemId : bt,
          plotId : bu,
          withinPlot : bv ? 1 : 0,
          callMode : u,
          filter : bw
        }, qx.lang.Function.bind(this.__fb, this, wialon.util.Helper.wrapCallback(bx)), 300);
      },
      uploadPlot : function(by, bz, bA){

        wialon.core.Uploader.getInstance().uploadFiles(by, z, {
          tzOffset : bz,
          callMode : A
        }, wialon.util.Helper.wrapCallback(bA), true);
      },
      uploadUnitPlot : function(bB, bC, bD, bE, bF){

        wialon.core.Remote.getInstance().remoteCall(z, {
          unitId : bB,
          timeFrom : bC,
          timeTo : bD,
          switchSensorId : bE,
          callMode : u
        }, wialon.util.Helper.wrapCallback(bF), 300);
      },
      clearUploadedCultivation : function(bG){

        wialon.core.Remote.getInstance().remoteCall(C, {
          callMode : F
        }, wialon.util.Helper.wrapCallback(bG), 300);
      },
      registerUploadedCultivation : function(bH, bI, bJ, bK, bL, bM, bN, bO, bP, bQ, bR, bS, bT, bU){

        wialon.core.Remote.getInstance().remoteCall(C, {
          plotItemId : bH,
          plotId : bI,
          ctypeItemId : bJ,
          ctypeId : bK,
          machineItemId : bL,
          machineId : bM,
          equipItemId : bN,
          equipId : bO,
          description : bP,
          timeFrom : bQ,
          timeTo : bR,
          unitId : bS,
          fuelFlags : bT,
          callMode : w
        }, wialon.util.Helper.wrapCallback(bU), 300);
      },
      registerUnitCultivation : function(bV, bW, bX, bY, ca, cb, cc, cd, ce, cf, cg, ch, ci, cj, ck){

        wialon.core.Remote.getInstance().remoteCall(C, {
          plotItemId : bV,
          plotId : bW,
          ctypeItemId : bX,
          ctypeId : bY,
          machineItemId : ca,
          machineId : cb,
          equipItemId : cc,
          equipId : cd,
          description : ce,
          timeFrom : cf,
          timeTo : cg,
          tzOffset : ch,
          unitId : ci,
          filter : cj,
          callMode : E
        }, wialon.util.Helper.wrapCallback(ck), 300);
      },
      createPlotsLayer : function(cl, cm, cn, co){

        var cq = wialon.core.Session.getInstance().getRenderer();
        if(!cq)return;
        var cp = cq.getLayers();
        for(var i = cp.length - 1;i >= 0;i--){

          if(cp[i].getName() == cl){

            cp[i].dispose();
            qx.lang.Array.remove(cp, cp[i]);
          };
        };
        wialon.core.Remote.getInstance().remoteCall(l, {
          layerName : cl,
          plots : cm,
          flags : cn
        }, qx.lang.Function.bind(this.__eL, this, wialon.util.Helper.wrapCallback(co)), 300);
      },
      getPrintUrl : function(cr, cs, ct, cu, cv, cw, cx, cy, cz, cA){

        var cB = {
          fileType : cr,
          isPlotGroup : cs,
          plots : ct,
          imageFlags : cu,
          plotFlags : cv,
          mapScale : cw,
          font : cx,
          fontSize : cy,
          fontColor : cz,
          lang : cA,
          rnd : (new Date).getTime()
        };
        return wialon.core.Session.getInstance().getBaseUrl() + y + wialon.core.Session.getInstance().getId() + p + wialon.util.Json.stringify(cB);
      },
      getUnitSettings : function(cC, cD){

        wialon.core.Remote.getInstance().remoteCall(m, {
          itemId : this.getId()
        }, wialon.util.Helper.wrapCallback(cD), 300);
      },
      updateUnitSettings : function(cE, cF, cG, cH, cI){

        wialon.core.Remote.getInstance().remoteCall(f, {
          unitId : cE,
          machineItemId : cF,
          machineId : cG,
          settings : cH
        }, wialon.util.Helper.wrapCallback(cI), 300);
      },
      convertPlots : function(cJ, cK, cL){

        wialon.core.Remote.getInstance().remoteCall(r, {
          resourceId : cJ,
          plots : cK
        }, wialon.util.Helper.wrapCallback(cL), 300);
      },
      updateCultivationMsg : function(cM, cN, cO, cP, cQ, cR, cS){

        wialon.core.Remote.getInstance().remoteCall(q, {
          plotItemId : cM,
          plotId : cN,
          timeFrom : cO,
          timeTo : cP,
          msgIndex : cQ,
          params : cR
        }, wialon.util.Helper.wrapCallback(cS), 300);
      },
      deleteCultivationMsg : function(cT, cU, cV, cW, cX, cY){

        wialon.core.Remote.getInstance().remoteCall(I, {
          plotItemId : cT,
          plotId : cU,
          timeFrom : cV,
          timeTo : cW,
          msgIndex : cX
        }, wialon.util.Helper.wrapCallback(cY), 300);
      },
      getPlotsUrl : function(da, db, dc){

        return wialon.core.Session.getInstance().getBaseUrl() + y + wialon.core.Session.getInstance().getId() + D + wialon.util.Json.stringify({
          fileName : da ? da : k,
          plots : db,
          tzOffset : dc
        });
      },
      importPlot : function(dd, de, df){

        wialon.core.Uploader.getInstance().uploadFiles([dd], t, {
          tzOffset : de,
          callMode : A
        }, wialon.util.Helper.wrapCallback(df), true);
      },
      registerPlots : function(dg, dh, di, dj){

        wialon.core.Remote.getInstance().remoteCall(t, {
          resourceId : dg,
          groupId : dh,
          config : di,
          callMode : w
        }, wialon.util.Helper.wrapCallback(dj), 300);
      },
      getUnitsInPlots : function(dk){

        wialon.core.Remote.getInstance().remoteCall(b, {
        }, wialon.util.Helper.wrapCallback(dk), 300);
      },
      print : {
        fileType : {
          svg : 0x01,
          png : 0x02
        },
        imageFlag : {
          a0 : 0x01,
          a1 : 0x02,
          a2 : 0x04,
          a3 : 0x08,
          a4 : 0x10,
          attachMap : 0x20,
          colored : 0x40
        },
        mapScale : {
          normal : 0x00,
          x2 : 0x01,
          x4 : 0x02,
          x6 : 0x03,
          x8 : 0x04,
          x10 : 0x05,
          x20 : 0x06,
          x50 : 0x07,
          x100 : 0x08,
          x200 : 0x09,
          x400 : 0x0A,
          x1000 : 0x0B
        },
        font : {
          dejaVuSans : J,
          dejaVuSansOblique : n,
          dejaVuSansBold : o,
          dejaVuSansBoldOblique : a,
          arial : L,
          arialBlack : d,
          courierNew : e,
          comicSansMS : j,
          georgia : K,
          impact : M,
          timesNewRoman : H,
          trebuchetMS : G,
          verdana : s
        },
        plotFlag : {
          placementHorizontal : 0x00,
          landscape : 0x01,
          rotate90CCW : 0x02,
          plotName : 0x04,
          plotDescription : 0x08,
          plotArea : 0x10,
          usefulPlotArea : 0x20,
          crop : 0x40,
          placementVertical : 0x80
        }
      },
      __eL : function(dl, dm, dn){

        var dq = wialon.core.Session.getInstance().getRenderer();
        if(!dq)return;
        var dp = null;
        if(dm == 0 && dn){

          if(typeof dn.name != v){

            dp = new wialon.render.Layer(dn);
            dq.getLayers().push(dp);
          };
          dq.setVersion(dq.getVersion() + 1);
        };
        dl(dm, dp);
      },
      __fa : function(dr, ds, dt){

        var dv = wialon.core.Session.getInstance().getRenderer();
        if(!dv)return;
        var du = null;
        if(ds == 0 && dt && dt.layer){

          if(typeof dt.layer.name != v){

            du = new wialon.render.Layer(dt.layer);
            dv.getLayers().push(du);
          };
          dv.setVersion(dv.getVersion() + 1);
        };
        dr(ds, {
          layer : du,
          cultivation : dt.cultivation
        });
      },
      __fb : function(dw, dx, dy){

        var dB = wialon.core.Session.getInstance().getRenderer();
        if(!dB)return;
        var dA = null;
        if(dx == 0 && dy && dy.data && dy.data.layer){

          var dz = dB.getLayers();
          for(var i = dz.length - 1;i >= 0;i--){

            if(dz[i].getName() == dy.data.layer.name){

              dz[i].dispose();
              qx.lang.Array.remove(dz, dz[i]);
            };
          };
          if(typeof dy.data.layer.name != v){

            dA = new wialon.render.Layer(dy.data.layer);
            dB.getLayers().push(dA);
          };
          dB.setVersion(dB.getVersion() + 1);
        };
        dw(dx, {
          layer : dA,
          registrar : (dy && dy.data) ? dy.data.registrar : []
        });
      }
    }
  });
})();
(function(){

  var u = "string",t = "exchange/import_zones_save",s = "&svc=exchange/export_messages&params=",r = "core/search_item",q = ">",p = "exchange/import_json",o = "&svc=exchange/export_pois&params=",n = "&svc=exchange/export_zones&params=",m = "txt",l = "kml",e = "exchange/import_pois_save",k = "exchange/import_xml",h = "static",c = "&svc=exchange/export_json&params=",b = "wlb",g = "<",f = "wialon.exchange.Exchange",i = "plt",a = "wln",j = "",d = /*"/wialon/ajax.html?sid="*/"/wialon/ajax.html?sid=";
  qx.Class.define(f, {
    type : h,
    statics : {
      msgExportFormat : {
        plt : i,
        nmea : m,
        kml : l,
        wln : a,
        wlb : b
      },
      getJsonExportUrl : function(v, w){

        if(typeof w != u || !w.length)w = (new Date()).getTime();
        var x = {
          json : v,
          fileName : w
        };
        return wialon.core.Session.getInstance().getBaseUrl() + d + wialon.core.Session.getInstance().getId() + c + encodeURI(qx.lang.Json.stringify(x).replace(/&lt;/g, g).replace(/&gt;/g, q));
      },
      importJson : function(y, z){

        wialon.core.Uploader.getInstance().uploadFiles(y, p, {
        }, z, true);
      },
      importXml : function(A, B){

        wialon.core.Uploader.getInstance().uploadFiles(A, k, {
        }, B, true);
      },
      getMessagesExportUrl : function(C, D, E){

        var F = {
          layerName : C,
          format : D,
          compress : E
        };
        return wialon.core.Session.getInstance().getBaseUrl() + d + wialon.core.Session.getInstance().getId() + s + qx.lang.Json.stringify(F);
      },
      getPOIsExportUrl : function(G, H, I){

        if(!H || !H.length)return j;
        var J = {
          fileName : G,
          pois : H,
          compress : I
        };
        return wialon.core.Session.getInstance().getBaseUrl() + d + wialon.core.Session.getInstance().getId() + o + qx.lang.Json.stringify(J);
      },
      getZonesExportUrl : function(K, L, M){

        if(!L || !L.length)return j;
        var N = {
          fileName : K,
          zones : L,
          compress : M
        };
        return wialon.core.Session.getInstance().getBaseUrl() + d + wialon.core.Session.getInstance().getId() + n + qx.lang.Json.stringify(N);
      },
      importPois : function(O, P, Q){

        return wialon.core.Remote.getInstance().remoteCall(e, {
          itemId : O,
          pois : P
        }, qx.lang.Function.bind(this.__fc, this, Q));
      },
      importZones : function(R, S, T){

        return wialon.core.Remote.getInstance().remoteCall(t, {
          itemId : R,
          zones : S
        }, qx.lang.Function.bind(this.__fc, this, T));
      },
      getItemJson : function(U, V){

        V = wialon.util.Helper.wrapCallback(V);
        wialon.core.Remote.getInstance().remoteCall(r, {
          id : U,
          flags : wialon.util.Number.umax()
        }, qx.lang.Function.bind(V));
      },
      __fc : function(W, X, Y){

        if(X || !Y){

          W(X);
          return;
        };
        W(0, Y);
      }
    }
  });
})();
(function(){

  var j = "",i = "/gis_geocode",h = "number",g = "/gis_searchintelli",f = "geocode",e = "wialon.util.Gis",d = "/gis_search",c = "static",b = "search",a = "string";
  qx.Class.define(e, {
    type : c,
    statics : {
      geocodingFlags : {
      },
      searchFlags : {
      },
      geocodingParams : {
        flags : 0,
        city_radius : 0,
        dist_from_unit : 0,
        txt_dist : j
      },
      getLocations : function(k, l){

        l = wialon.util.Helper.wrapCallback(l);
        if(!k){

          l(2, null);
          return;
        };
        var n = qx.lang.Object.clone(this.geocodingParams);
        n.coords = wialon.util.Json.stringify(k);
        var m = wialon.core.Session.getInstance().getCurrUser();
        if(m)n.uid = m.getId();
        wialon.core.Remote.getInstance().ajaxRequest(wialon.core.Session.getInstance().getBaseGisUrl(f) + i, n, l, (k && k.length > 1) ? 10 : 2);
      },
      searchByString : function(o, p, q, r){

        r = wialon.util.Helper.wrapCallback(r);
        if(typeof o != a || typeof q != h){

          r(2, null);
          return;
        };
        var t = {
          phrase : o,
          flags : p,
          count : q
        };
        var s = wialon.core.Session.getInstance().getCurrUser();
        if(s)t.uid = s.getId();
        wialon.core.Remote.getInstance().ajaxRequest(wialon.core.Session.getInstance().getBaseGisUrl(b) + g, t, r, 10);
      },
      search : function(u, v, w, x, y, z, A){

        A = wialon.util.Helper.wrapCallback(A);
        if(typeof u != a || typeof v != a || typeof w != a || typeof x != a){

          A(2, null);
          return;
        };
        var C = {
          country : u,
          region : v,
          city : w,
          street : x,
          flags : y,
          count : z
        };
        var B = wialon.core.Session.getInstance().getCurrUser();
        if(B)C.uid = B.getId();
        wialon.core.Remote.getInstance().ajaxRequest(wialon.core.Session.getInstance().getBaseGisUrl(b) + d, C, A, 10);
      }
    }
  });
})();
(function(){

  var x = "-",w = "'",u = "wialon.util.Geometry",t = "00",q = "static",n = "",m = "&deg;",j = " ",h = "object",e = "0";
  qx.Class.define(u, {
    type : q,
    statics : {
      getDistance : function(y, z, A, B){

        var k = Math.PI / 180;
        var I = 1 / 298.257;
        var c,d,f,g,E,D,l,o,r,s,M,H,K,F,L,G,J,C;
        if(y == A && z == B)return 0;
        f = (y + A) / 2;
        g = (y - A) / 2;
        l = (z - B) / 2;
        K = Math.sin(g * k);
        F = Math.cos(g * k);
        L = Math.sin(f * k);
        G = Math.cos(f * k);
        J = Math.sin(l * k);
        C = Math.cos(l * k);
        M = Math.pow(K * C, 2);
        H = Math.pow(G * J, 2);
        s = M + H;
        M = Math.pow(F * C, 2);
        H = Math.pow(L * J, 2);
        c = M + H;
        o = Math.atan(Math.sqrt(s / c));
        r = Math.sqrt(s * c) / o;
        d = 2 * o * 6378.137;
        E = (3 * r - 1) / (2 * c);
        D = (3 * r + 1) / (2 * s);
        M = L * F;
        M = M * M * E * I + 1;
        H = G * K;
        H = H * H * D * I;
        return d * (M - H) * 1000;
      },
      getCoordDegrees : function(N, O, P, Q, R, S){

        if(!S)S = m;
        return N.toFixed(6) + S;
      },
      getCoordMinutes : function(T, U, V, W, X, Y){

        if(!Y)Y = m;
        var v = Number(T);
        var ba = (v < 0) ? x : n;
        var bc = v > 0 ? W : X;
        v = Math.abs(v);
        var bd = Math.floor(v);
        var bb = (v - bd) * 60.0;
        var p = String(bb);
        if(bb < 10)p = e + bb;
        var be = n;
        if(U == 2){

          if(bd >= 0 && bd < 10)be = e + bd; else be = bd;
        } else if(U == 3){

          if(bd >= 0 && bd < 10)be = t + bd; else if(bd >= 10 && bd < 100)be = e + bd; else be = bd;;
        };
        be = ba + be;
        return bc + j + be + Y + j + p.substr(0, V + 3) + w;
      },
      getCoord : function(bf, bg, bh, bi, bj, bk){

        return this.getCoordMinutes(bf, bg, bh, bi, bj, bk);
      },
      getDistanceToLine : function(bl, bm, bn, bo, bp, bq, br){

        var bt = {
        };
        if(bl == bn && bm == bo)return this.getDistance(bl, bm, bp, bq);
        var bu = 0;
        var bs = 0;
        if(bm != bo){

          var a = (bl - bn) / (bm - bo);
          var b = bl - bm * a;
          bu = (bq + a * bp - a * b) / (a * a + 1.0);
          bs = bu * a + b;
        } else {

          var a = (bm - bo) / (bl - bn);
          var b = bm - bl * a;
          bs = (bp + a * bq - a * b) / (a * a + 1.0);
          bu = bs * a + b;
        };
        if(!br)return this.getDistance(bs, bu, bp, bq);
        if(bu < bm && bu < bo || bu > bm && bu > bo || bs < bl && bs < bn || bs > bl && bs > bn)return -1; else return this.getDistance(bs, bu, bp, bq);
      },
      pointInShape : function(bv, bw, bx, by, bz){

        if(!bv || typeof bv != h)return false;
        var bC = bv.length;
        if(bv.length > 2 && bw == 0){

          if(bz && !(by >= bz.min_y && by <= bz.max_y && bx >= bz.min_x && bx <= bz.max_x))return;
          var bI = 0;
          var bF = 0;
          var bE = 0;
          var bA = 0;
          var bK = 0;
          var bJ = 0;
          var bG = 0;
          var bB = 0;
          var bD = false;
          bE = bv[bC - 1].x;
          bA = bv[bC - 1].y;
          for(var i = 0;i < bC;i++){

            bI = bv[i].x;
            bF = bv[i].y;
            if(bI > bE){

              bK = bE;
              bG = bI;
              bJ = bA;
              bB = bF;
            } else {

              bK = bI;
              bG = bE;
              bJ = bF;
              bB = bA;
            };
            if((bI < bx) == (bx <= bE) && (by - bJ) * (bG - bK) < (bB - bJ) * (bx - bK)){

              bD = !bD;
            };
            bE = bI;
            bA = bF;
          };
          return bD;
        } else if(bv.length > 1 && bw){

          if(bz && !(by >= bz.min_y && by <= bz.max_y && bx >= bz.min_x && bx <= bz.max_x))return;
          var bM = 0;
          var bL = 0;
          for(var i = 0;i < bC;i++){

            var bH = this.getDistance(bv[i].y, bv[i].x, by, bx);
            if(bw && bH != -1 && bH <= bw)return true;
            if(bw){

              if(bH != -1 && bH <= bw / 2)return true;
              if(i > 0){

                var bH = this.getDistanceToLine(bv[i].y, bv[i].x, bM, bL, by, bx, true);
                if(bH != -1 && bH <= bw / 2)return true;
              };
            };
            bM = bv[i].y;
            bL = bv[i].x;
          };
        } else if(bv.length == 1 && bw){

          var p = bv[0];
          bH = this.getDistance(p.y, p.x, by, bx);
          if(bH != -1 && bH <= bw)return true;
        };;
        return false;
      },
      getShapeCenter : function(bN){

        if(!bN || typeof bN != h)return;
        var bS = bN.length;
        var bQ = 0xFFFFFFFF;
        var bR = 0xFFFFFFFF;
        var bO = -0xFFFFFFFF;
        var bP = -0xFFFFFFFF;
        for(var i = 0;i < bS;i++){

          if(bN[i].x < bQ)bQ = bN[i].x;
          if(bN[i].x > bO)bO = bN[i].x;
          if(bN[i].y < bR)bR = bN[i].y;
          if(bN[i].y > bP)bP = bN[i].y;
        };
        return {
          x : (bO + bQ) / 2,
          y : (bP + bR) / 2
        };
      },
      calculateBoundary : function(bT){

        var bX = 0;
        var ca = 0;
        var bW = 0;
        var cc = 0;
        var bU = 0;
        if(!bX && !ca && !bW && !cc){

          var bU = 0;
          for(var i = 0;i < bT.length;i++){

            var cb = bT[i];
            if(!bX && !ca && !bW && !cc){

              bW = cb.y;
              bX = cb.y;
              cc = cb.x;
              ca = cb.x;
              bU = cb.w;
            } else {

              if(cc > cb.x)cc = cb.x;
              if(ca < cb.x)ca = cb.x;
              if(bW > cb.y)bW = cb.y;
              if(bX < cb.y)bX = cb.y;
              if(cb.radius > bU)bU = cb.w;
            };
          };
          var bV = wialon.util.Geometry.getDistance(bW, cc, bW + 1, cc);
          var bY = wialon.util.Geometry.getDistance(bW, cc, bW, cc + 1);
          if(bV && bY){

            bW -= bU / bV;
            cc -= bU / bY;
            bX += bU / bV;
            ca += bU / bY;
          };
        };
        return {
          min_y : bW,
          min_x : cc,
          max_y : bX,
          max_x : ca
        };
      }
    }
  });
})();
(function(){

  var x = "wialon.util.String",w = "null",v = 'x',u = 'c',t = 'b',s = 'X',r = 'o',q = '-',p = 'f',o = "x",e = 's',n = 'd',h = "static",c = ":",b = "0",g = '',f = '%',k = "undefined",a = "string",m = "",d = ' ';
  qx.Class.define(x, {
    type : h,
    statics : {
      wrapString : function(y){

        if(typeof y == k || !y.length)y = m;
        return y;
      },
      xor : function(z, A){

        var B = [];
        for(var i = 0;i < z.length;i++)B.push(z.charCodeAt(i) ^ A.charCodeAt(i % A.length));
        return B.join(c);
      },
      unxor : function(C, D){

        var E = m;
        if(C == m)return C;
        C = C.split(c);
        for(var i = 0;i < C.length;i++)E += String.fromCharCode(C[i] ^ D.charCodeAt(i % D.length));
        return E;
      },
      isValidText : function(F){

        if(F === w)return false;
        var H = m + F;
        var G = /([\"\{\}\\])/i;
        return (H != null && typeof H === a && (!H.length || !G.test(H)));
      },
      isValidName : function(name, I){

        var K = m + name;
        if(I == null)return (K != null && this.isValidText(K) && K.length > 0 && K[0] != d && K[K.length - 1] != d);
        var L = (I.min != null ? I.min : 1);
        var J = (I.max != null ? I.max : 4096);
        return (K != null && this.isValidText(K) && K.length >= L && K.length <= J && K[0] != d && K[K.length - 1] != d);
      },
      isValidEmail : function(M){

        return (/^([a-z0-9_\-]+\.)*[a-z0-9_\-]+@([a-z0-9][a-z0-9\-]*[a-z0-9]\.)+[a-z]{2,4}$/i).test(M);
      },
      isValidPhone : function(N){

        var O = m + N;
        return (O != null && this.isValidText(O) && (/^[+]{1,1}[\d]{7,16}$/i).test(O));
      },
      stringMatchTemplates : function(P, Q, R){

        if(typeof P != a || !P.length || !(Q instanceof Array))return true;
        if(typeof R != a || R.length != 1)R = o;
        for(var i = 0;i < Q.length;i++){

          var T = Q[i];
          if(typeof T != a || T.length != P.length)continue;
          var S = true;
          for(var j = 0;j < P.length;j++){

            if(P[j] != T[j] && T[j].toLowerCase() != R[0]){

              S = false;
              break;
            };
          };
          if(S)return true;
        };
        return false;
      },
      sprintf : function(){

        if(typeof arguments == k){

          return null;
        };
        if(arguments.length < 1){

          return null;
        };
        if(typeof arguments[0] != a){

          return null;
        };
        if(typeof RegExp == k){

          return null;
        };
        var V = arguments[0];
        var ba = new RegExp(/(%([%]|(\-)?(\+|\x20)?(0)?(\d+)?(\.(\d)?)?([bcdfosxX])))/g);
        var W = new Array();
        var be = new Array();
        var X = 0;
        var Y = 0;
        var bc = 0;
        var bg = 0;
        var bd = g;
        var bf = null;
        while(bf = ba.exec(V)){

          if(bf[9]){

            X += 1;
          };
          Y = bg;
          bc = ba.lastIndex - bf[0].length;
          be[be.length] = V.substring(Y, bc);
          bg = ba.lastIndex;
          W[W.length] = {
            match : bf[0],
            left : bf[3] ? true : false,
            sign : bf[4] || g,
            pad : bf[5] || d,
            min : bf[6] || 0,
            precision : bf[8],
            code : bf[9] || f,
            negative : parseFloat(arguments[X]) < 0 ? true : false,
            argument : String(arguments[X])
          };
        };
        be[be.length] = V.substring(bg);
        if(W.length == 0){

          return V;
        };
        if((arguments.length - 1) < X){

          return null;
        };
        var U = null;
        var bf = null;
        var i = null;
        var bb = null;
        for(i = 0;i < W.length;i++){

          if(W[i].code == f){

            bb = f;
          } else if(W[i].code == t){

            W[i].argument = String(Math.abs(parseInt(W[i].argument)).toString(2));
            bb = this.__fd(W[i], true);
          } else if(W[i].code == u){

            W[i].argument = String(String.fromCharCode(parseInt(Math.abs(parseInt(W[i].argument)))));
            bb = this.__fd(W[i], true);
          } else if(W[i].code == n){

            W[i].argument = String(Math.abs(parseInt(W[i].argument)));
            bb = this.__fd(W[i]);
          } else if(W[i].code == p){

            W[i].argument = String(Math.abs(parseFloat(W[i].argument)).toFixed(W[i].precision ? W[i].precision : 6));
            bb = this.__fd(W[i]);
          } else if(W[i].code == r){

            W[i].argument = String(Math.abs(parseInt(W[i].argument)).toString(8));
            bb = this.__fd(W[i]);
          } else if(W[i].code == e){

            W[i].argument = W[i].argument.substring(0, W[i].precision ? W[i].precision : W[i].argument.length);
            bb = this.__fd(W[i], true);
          } else if(W[i].code == v){

            W[i].argument = String(Math.abs(parseInt(W[i].argument)).toString(16));
            bb = this.__fd(W[i]);
          } else if(W[i].code == s){

            W[i].argument = String(Math.abs(parseInt(W[i].argument)).toString(16));
            bb = this.__fd(W[i]).toUpperCase();
          } else {

            bb = W[i].match;
          };;;;;;;;
          bd += be[i];
          bd += bb;
        };
        bd += be[i];
        return bd;
      },
      __fd : function(bh, bi){

        if(bi){

          bh.sign = g;
        } else {

          bh.sign = bh.negative ? q : bh.sign;
        };
        var l = bh.min - bh.argument.length + 1 - bh.sign.length;
        var bj = new Array(l < 0 ? 0 : l).join(bh.pad);
        if(!bh.left){

          if(bh.pad == b || bi){

            return bh.sign + bj + bh.argument;
          } else {

            return bj + bh.sign + bh.argument;
          };
        } else {

          if(bh.pad == b || bi){

            return bh.sign + bh.argument + bj.replace(/0/g, d);
          } else {

            return bh.sign + bh.argument + bj;
          };
        };
      }
    }
  });
})();
(function(){

  var o = "Invalid input",n = "Error performing request",m = "",l = "Item with such unique property already exists",k = "wialon.core.Errors",j = "Invalid service",i = "Invalid result",h = "Access denied",g = "Authorization server is unavailable, please try again later",f = "Invalid user name or password",c = "Only one request of given time is allowed at the moment",e = "Invalid session",d = "No message for selected interval",b = "static",a = "Unknown error";
  qx.Class.define(k, {
    type : b,
    statics : {
      getErrorText : function(p){

        switch(p){case 0:
        return m;case 1:
        return e;case 2:
        return j;case 3:
        return i;case 4:
        return o;case 5:
        return n;case 6:
        break;case 7:
        return h;case 8:
        return f;case 9:
        return g;case 1001:
        return d;case 1002:
        return l;case 1003:
        return c;default:
        break;};
        return a;
      }
    }
  });
})();
(function(){

  var l = "Y-MM-dd",k = "Y-MM-dd HH:mm:ss",j = "wialon.util.DateTime",h = "tz",g = "0",f = "HH:mm",e = "static",c = "",b = "undefined",a = "string";
  qx.Class.define(j, {
    type : e,
    statics : {
      formatTime : function(m, n, o){

        if(!m)return c;
        var r = o;
        m = this.userTime(m);
        var d = new Date(m * 1000);
        if(!r || typeof r != a){

          r = k;
          if(n){

            var q = new Date(this.userTime(wialon.core.Session.getInstance().getServerTime()) * 1000);
            if((d.getUTCFullYear() == q.getUTCFullYear() && d.getUTCMonth() == q.getUTCMonth() && d.getUTCDate() == q.getUTCDate()) || n == 2)r = f;
          };
        };
        var p = {
          "Y" : d.getUTCFullYear(),
          "MM" : this.__fe(d.getUTCMonth() + 1),
          "dd" : this.__fe(d.getUTCDate()),
          "HH" : this.__fe(d.getUTCHours()),
          "mm" : this.__fe(d.getUTCMinutes()),
          "ss" : this.__fe(d.getUTCSeconds())
        };
        for(var i in p)r = r.replace(i, p[i]);
        return r;
      },
      formatDate : function(s, u){

        if(!s)return c;
        var w = u;
        if(!w || typeof w != a)w = l;
        s = this.userTime(s);
        var d = new Date(s * 1000);
        var v = {
          "Y" : d.getUTCFullYear(),
          "MM" : this.__fe(d.getUTCMonth() + 1),
          "dd" : this.__fe(d.getUTCDate())
        };
        for(var i in v)w = w.replace(i, v[i]);
        return w;
      },
      getTimezone : function(){

        var x = -(new Date()).getTimezoneOffset() * 60;
        var y = wialon.core.Session.getInstance().getCurrUser();
        if(!y)return x;
        return parseInt(y.getCustomProperty(h, x)) >>> 0;
      },
      getTimezoneOffset : function(){

        var z = this.getTimezone();
        if((z & this.__ff.TZ_TYPE_MASK) != this.__ff.TZ_TYPE_WITH_DST)return z & this.__ff.TZ_OFFSET_MASK;
        return parseInt(z & 0x80000000 ? ((z & 0xFFFF) | 0xFFFF0000) : (z & 0xFFFF));
      },
      getDSTOffset : function(A){

        if(!A)return 0;
        var E = this.getTimezone();
        var I = E & this.__ff.TZ_TYPE_MASK;
        var O = this.getTimezoneOffset(E);
        if((I == this.__ff.TZ_TYPE_WITH_DST && (E & this.__ff.TZ_DST_TYPE_MASK) == this.__ff.TZ_DST_TYPE_NONE) || (I != this.__ff.TZ_TYPE_WITH_DST && (E & this.__ff.TZ_DISABLE_DST_BIT)))return 0;
        if((I == this.__ff.TZ_TYPE_WITH_DST && (E & this.__ff.TZ_DST_TYPE_MASK) == this.__ff.TZ_DST_TYPE_SERVER) || I != this.__ff.TZ_TYPE_WITH_DST){

          var B = new Date();
          B.setTime(A * 1000);
          var J = new Date();
          J.setTime((A - 90 * 86400) * 1000);
          var K = new Date();
          K.setTime((A + 150 * 86400) * 1000);
          if(B.getTimezoneOffset() < J.getTimezoneOffset() || B.getTimezoneOffset() < K.getTimezoneOffset())return 3600;
          return 0;
        };
        var G = E & this.__ff.TZ_CUSTOM_DST_MASK;
        var N = new Date((A + O) * 1000);
        var F = N.getTime() / 1000;
        var M = 0;
        var C = 0;
        var L = N.getUTCFullYear();
        if(typeof this.__fh.from[G | L] == b || typeof this.__fh.to[G | L] == b){

          switch(G){case this.__fg.DST_MAR2SUN2AM_NOV1SUN2AM:
          M = this.getWdayTime(L, 2, 2, 0, 0, 2);
          C = this.getWdayTime(L, 10, 1, 0, 0, 2);
          break;case this.__fg.DST_MAR6SUN_OCT6SUN:
          M = this.getWdayTime(L, 2, 6, 0);
          C = this.getWdayTime(L, 9, 6, 0);
          break;case this.__fg.DST_MAR6SUN1AM_OCT6SUN1AM:
          M = this.getWdayTime(L, 2, 6, 0, 0, 1);
          C = this.getWdayTime(L, 9, 6, 0, 2);
          break;case this.__fg.DST_MAR6THU_SEP6FRI:
          M = this.getWdayTime(L, 2, 6, 4);
          C = this.getWdayTime(L, 8, 6, 5);
          break;case this.__fg.DST_MAR6SUN2AM_OCT6SUN2AM:
          M = this.getWdayTime(L, 2, 6, 0, 0, 2);
          C = this.getWdayTime(L, 9, 6, 0, 0, 2);
          if(L > 2011)return 0; else if(L == 2011)if(A < M)return -3600;;;
          return (A < M || A > C) ? -3600 : 0;
          break;case this.__fg.DST_MAR30_SEP21:
          M = this.getWdayTime(L, 2, 0, -1, 30);
          C = this.getWdayTime(L, 8, 0, -1, 21);
          break;case this.__fg.DST_APR1SUN2AM_OCT6SUN2AM:
          M = this.getWdayTime(L, 3, 1, 0, 0, 2);
          C = this.getWdayTime(L, 9, 6, 0, 0, 2);
          break;case this.__fg.DST_APR1_OCT6SUN:
          M = this.getWdayTime(L, 3, 0, -1, 1);
          C = this.getWdayTime(L, 9, 6, 0);
          break;case this.__fg.DST_APR6THU_SEP6THU:
          M = this.getWdayTime(L, 3, 6, 4);
          C = this.getWdayTime(L, 8, 6, 4);
          break;case this.__fg.DST_APR1_OCT1:
          M = this.getWdayTime(L, 3, 0, -1, 1);
          C = this.getWdayTime(L, 9, 0, -1, 1);
          break;case this.__fg.DST_MAR21_22SUN_SEP20_21SUN:
          if(this.isLeapYear(L)){

            M = this.getWdayTime(L, 2, 0, -1, 21);
            C = this.getWdayTime(L, 8, 0, -1, 20, 23, 0, 0);
          } else {

            M = this.getWdayTime(L, 2, 0, -1, 22);
            C = this.getWdayTime(L, 8, 0, -1, 21, 23, 0, 0);
          };
          break;case this.__fg.DST_SEP1SUNAFTER7_APR1SUNAFTER5:
          M = this.getWdayTime(L, 8, 1, 0, 7);
          C = this.getWdayTime(L, 3, 1, 0, 5);
          break;case this.__fg.DST_SEP1SUN_APR1SUN:
          M = this.getWdayTime(L, 8, 1, 0);
          C = this.getWdayTime(L, 3, 1, 0);
          break;case this.__fg.DST_SEP6SUN_APR1SUN:
          M = this.getWdayTime(L, 8, 6, 0);
          C = this.getWdayTime(L, 3, 1, 0);
          break;case this.__fg.DST_OCT2SUN_MAR2SUN:
          M = this.getWdayTime(L, 9, 2, 0);
          C = this.getWdayTime(L, 2, 2, 0);
          break;case this.__fg.DST_OCT1SUN_FEB3SUN:
          M = this.getWdayTime(L, 9, 1, 0);
          C = this.getWdayTime(L, 1, 3, 0);
          break;case this.__fg.DST_OCT3SUN_MAR2SUN:
          M = this.getWdayTime(L, 9, 3, 0);
          C = this.getWdayTime(L, 2, 2, 0);
          break;case this.__fg.DST_OCT1SUN_MAR2SUN:
          M = this.getWdayTime(L, 9, 1, 0);
          C = this.getWdayTime(L, 2, 2, 0);
          break;case this.__fg.DST_OCT1SUN_APR1SUN:
          M = this.getWdayTime(L, 9, 1, 0);
          C = this.getWdayTime(L, 3, 1, 0);
          break;case this.__fg.DST_OCT1SUN_MAR6SUN:
          M = this.getWdayTime(L, 9, 1, 0);
          C = this.getWdayTime(L, 2, 6, 0);
          break;default:
          return 0;};
          this.__fh.from[G | L] = M;
          if(C % 2 == 0)C--;
          this.__fh.to[G | L] = C;
        } else {

          M = this.__fh.from[G | L];
          C = this.__fh.to[G | L];
        };
        var H = (E & this.__ff.TZ_DST_TYPE_MASK) == this.__ff.TZ_DST_TYPE_CUSTOM_UTC ? M : M - O;
        var D = (E & this.__ff.TZ_DST_TYPE_MASK) == this.__ff.TZ_DST_TYPE_CUSTOM_UTC ? C : C - O;
        if(G >= this.__fg.DST_SOUTHERN_SEMISPHERE)return (A <= H && A >= D) ? 0 : 3600;
        return (A >= H && A <= D) ? 3600 : 0;
      },
      isLeapYear : function(P){

        if(P % 4 == 0 && P % 100 != 0)return true; else if(P % 4 == 0 && P % 100 == 0 && P % 400 == 0)return true;;
        return false;
      },
      getWdayTime : function(Q, R, S, T, U, V, W, X){

        var Y = new Date();
        Y.setUTCFullYear(Q);
        Y.setUTCMonth(R);
        Y.setUTCDate(1);
        Y.setUTCHours(0);
        Y.setUTCMilliseconds(0);
        Y.setUTCMinutes(0);
        Y.setUTCSeconds(0);
        var ba = 0;
        if(T == -1)ba = U; else {

          if(Y.getUTCDay() <= T)ba = (T - Y.getUTCDay()) + 1; else ba = 8 - (Y.getUTCDay() - T);
          if(S < 6){

            if(U){

              while(ba <= U)ba += 7;
            } else if(S)ba += 7 * (S - 1);;
          } else {

            var bb = this.getMonthDays(R, Q);
            if(ba + 4 * 7 <= bb)ba += 4 * 7; else ba += 3 * 7;
          };
        };
        Y.setUTCDate(ba);
        if(V)Y.setUTCHours(V);
        if(W)Y.setUTCMinutes(W);
        if(X)Y.setUTCSeconds(X);
        return parseInt(Y.getTime() / 1000);
      },
      getMonthDays : function(bc, bd){

        if(bc < 0 || !bd)return 0;
        var be = [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
        if(bc >= be.length)return 0;
        if(bc == 1 && this.getYearDays(bd) == 365)return 29;
        return be[bc];
      },
      getYearDays : function(bf){

        if(!bf)return 0;
        if((bf % 4) == 0){

          if((bf % 100) == 0)return ((bf % 400) == 0) ? 365 : 364;
          return 365;
        };
        return 364;
      },
      userTime : function(bg){

        return bg + this.getDSTOffset(bg) + this.getTimezoneOffset();
      },
      absoluteTime : function(bh){

        var t = bh - this.getTimezoneOffset();
        var bi = this.getDSTOffset(t);
        var bj = this.getDSTOffset(t - 3600);
        if(bi == bj)return t - bi;
        return t;
      },
      __fe : function(i){

        if(i < 10)i = g + i;
        return i;
      },
      __ff : {
        TZ_TYPE_MASK : 0x0C000000,
        TZ_TYPE_WITH_DST : 0x08000000,
        TZ_DST_TYPE_MASK : 0x03000000,
        TZ_DST_TYPE_NONE : 0x00000000,
        TZ_DST_TYPE_SERVER : 0x01000000,
        TZ_DST_TYPE_CUSTOM : 0x02000000,
        TZ_CUSTOM_DST_MASK : 0x00FF0000,
        TZ_DST_TYPE_CUSTOM_UTC : 0x03000000
      },
      __fg : {
        DST_MAR2SUN2AM_NOV1SUN2AM : 0x00010000,
        DST_MAR6SUN_OCT6SUN : 0x00020000,
        DST_MAR6SUN1AM_OCT6SUN1AM : 0x00030000,
        DST_MAR6THU_SEP6FRI : 0x00040000,
        DST_MAR6SUN2AM_OCT6SUN2AM : 0x00050000,
        DST_MAR30_SEP21 : 0x00060000,
        DST_APR1SUN2AM_OCT6SUN2AM : 0x00070000,
        DST_APR1_OCT6SUN : 0x00080000,
        DST_APR6THU_SEP6THU : 0x00090000,
        DST_APR6THU_UNKNOWN : 0x000A0000,
        DST_APR1_OCT1 : 0x000B0000,
        DST_MAR21_22SUN_SEP20_21SUN : 0x000C0000,
        DST_SOUTHERN_SEMISPHERE : 0x00200000,
        DST_SEP1SUNAFTER7_APR1SUNAFTER5 : 0x00200000,
        DST_SEP1SUN_APR1SUN : 0x00210000,
        DST_SEP6SUN_APR1SUN : 0x00220000,
        DST_OCT2SUN_MAR2SUN : 0x00230000,
        DST_OCT1SUN_FEB3SUN : 0x00240000,
        DST_OCT3SUN_MAR2SUN : 0x00250000,
        DST_OCT1SUN_MAR2SUN : 0x00260000,
        DST_OCT1SUN_APR1SUN : 0x00270000,
        DST_OCT1SUN_MAR6SUN : 0x00280000
      },
      __fh : {
        from : {
        },
        to : {
        }
      }
    }
  });
})();

qx.$$loader.init();

