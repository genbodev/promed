var $jscomp = $jscomp || {};
$jscomp.scope = {};
$jscomp.ASSUME_ES5 = false;
$jscomp.ASSUME_NO_NATIVE_MAP = false;
$jscomp.ASSUME_NO_NATIVE_SET = false;
$jscomp.defineProperty = $jscomp.ASSUME_ES5 || typeof Object.defineProperties == 'function' ? Object.defineProperty : function(target, property, descriptor) {
  descriptor = descriptor;
  if (target == Array.prototype || target == Object.prototype) {
    return;
  }
  target[property] = descriptor.value;
};
$jscomp.getGlobal = function(maybeGlobal) {
  return typeof window != 'undefined' && window === maybeGlobal ? maybeGlobal : typeof global != 'undefined' && global != null ? global : maybeGlobal;
};
$jscomp.global = $jscomp.getGlobal(this);
$jscomp.polyfill = function(target, polyfill, fromLang, toLang) {
  if (!polyfill) {
    return;
  }
  var obj = $jscomp.global;
  var split = target.split('.');
  for (var i = 0; i < split.length - 1; i++) {
    var key = split[i];
    if (!(key in obj)) {
      obj[key] = {};
    }
    obj = obj[key];
  }
  var property = split[split.length - 1];
  var orig = obj[property];
  var impl = polyfill(orig);
  if (impl == orig || impl == null) {
    return;
  }
  $jscomp.defineProperty(obj, property, {configurable:true, writable:true, value:impl});
};
$jscomp.polyfill('Array.prototype.copyWithin', function(orig) {
  if (orig) {
    return orig;
  }
  var polyfill = function(target, start, opt_end) {
    var len = this.length;
    target = Number(target);
    start = Number(start);
    opt_end = Number(opt_end != null ? opt_end : len);
    if (target < start) {
      opt_end = Math.min(opt_end, len);
      while (start < opt_end) {
        if (start in this) {
          this[target++] = this[start++];
        } else {
          delete this[target++];
          start++;
        }
      }
    } else {
      opt_end = Math.min(opt_end, len + start - target);
      target += opt_end - start;
      while (opt_end > start) {
        if (--opt_end in this) {
          this[--target] = this[opt_end];
        } else {
          delete this[target];
        }
      }
    }
    return this;
  };
  return polyfill;
}, 'es6', 'es3');
$jscomp.SYMBOL_PREFIX = 'jscomp_symbol_';
$jscomp.initSymbol = function() {
  $jscomp.initSymbol = function() {
  };
  if (!$jscomp.global['Symbol']) {
    $jscomp.global['Symbol'] = $jscomp.Symbol;
  }
};
$jscomp.Symbol = function() {
  var counter = 0;
  function Symbol(opt_description) {
    return $jscomp.SYMBOL_PREFIX + (opt_description || '') + counter++;
  }
  return Symbol;
}();
$jscomp.initSymbolIterator = function() {
  $jscomp.initSymbol();
  var symbolIterator = $jscomp.global['Symbol'].iterator;
  if (!symbolIterator) {
    symbolIterator = $jscomp.global['Symbol'].iterator = $jscomp.global['Symbol']('iterator');
  }
  if (typeof Array.prototype[symbolIterator] != 'function') {
    $jscomp.defineProperty(Array.prototype, symbolIterator, {configurable:true, writable:true, value:function() {
      return $jscomp.arrayIterator(this);
    }});
  }
  $jscomp.initSymbolIterator = function() {
  };
};
$jscomp.arrayIterator = function(array) {
  var index = 0;
  return $jscomp.iteratorPrototype(function() {
    if (index < array.length) {
      return {done:false, value:array[index++]};
    } else {
      return {done:true};
    }
  });
};
$jscomp.iteratorPrototype = function(next) {
  $jscomp.initSymbolIterator();
  var iterator = {next:next};
  iterator[$jscomp.global['Symbol'].iterator] = function() {
    return this;
  };
  return iterator;
};
$jscomp.iteratorFromArray = function(array, transform) {
  $jscomp.initSymbolIterator();
  if (array instanceof String) {
    array = array + '';
  }
  var i = 0;
  var iter = {next:function() {
    if (i < array.length) {
      var index = i++;
      return {value:transform(index, array[index]), done:false};
    }
    iter.next = function() {
      return {done:true, value:void 0};
    };
    return iter.next();
  }};
  iter[Symbol.iterator] = function() {
    return iter;
  };
  return iter;
};
$jscomp.polyfill('Array.prototype.entries', function(orig) {
  if (orig) {
    return orig;
  }
  var polyfill = function() {
    return $jscomp.iteratorFromArray(this, function(i, v) {
      return [i, v];
    });
  };
  return polyfill;
}, 'es6', 'es3');
$jscomp.polyfill('Array.prototype.fill', function(orig) {
  if (orig) {
    return orig;
  }
  var polyfill = function(value, opt_start, opt_end) {
    var length = this.length || 0;
    if (opt_start < 0) {
      opt_start = Math.max(0, length + opt_start);
    }
    if (opt_end == null || opt_end > length) {
      opt_end = length;
    }
    opt_end = Number(opt_end);
    if (opt_end < 0) {
      opt_end = Math.max(0, length + opt_end);
    }
    for (var i = Number(opt_start || 0); i < opt_end; i++) {
      this[i] = value;
    }
    return this;
  };
  return polyfill;
}, 'es6', 'es3');
$jscomp.findInternal = function(array, callback, thisArg) {
  if (array instanceof String) {
    array = String(array);
  }
  var len = array.length;
  for (var i = 0; i < len; i++) {
    var value = array[i];
    if (callback.call(thisArg, value, i, array)) {
      return {i:i, v:value};
    }
  }
  return {i:-1, v:void 0};
};
$jscomp.polyfill('Array.prototype.find', function(orig) {
  if (orig) {
    return orig;
  }
  var polyfill = function(callback, opt_thisArg) {
    return $jscomp.findInternal(this, callback, opt_thisArg).v;
  };
  return polyfill;
}, 'es6', 'es3');
$jscomp.polyfill('Array.prototype.findIndex', function(orig) {
  if (orig) {
    return orig;
  }
  var polyfill = function(callback, opt_thisArg) {
    return $jscomp.findInternal(this, callback, opt_thisArg).i;
  };
  return polyfill;
}, 'es6', 'es3');
$jscomp.polyfill('Array.from', function(orig) {
  if (orig) {
    return orig;
  }
  var polyfill = function(arrayLike, opt_mapFn, opt_thisArg) {
    $jscomp.initSymbolIterator();
    opt_mapFn = opt_mapFn != null ? opt_mapFn : function(x) {
      return x;
    };
    var result = [];
    var iteratorFunction = arrayLike[Symbol.iterator];
    if (typeof iteratorFunction == 'function') {
      arrayLike = iteratorFunction.call(arrayLike);
      var next;
      while (!(next = arrayLike.next()).done) {
        result.push(opt_mapFn.call(opt_thisArg, next.value));
      }
    } else {
      var len = arrayLike.length;
      for (var i = 0; i < len; i++) {
        result.push(opt_mapFn.call(opt_thisArg, arrayLike[i]));
      }
    }
    return result;
  };
  return polyfill;
}, 'es6', 'es3');
$jscomp.polyfill('Object.is', function(orig) {
  if (orig) {
    return orig;
  }
  var polyfill = function(left, right) {
    if (left === right) {
      return left !== 0 || 1 / left === 1 / right;
    } else {
      return left !== left && right !== right;
    }
  };
  return polyfill;
}, 'es6', 'es3');
$jscomp.polyfill('Array.prototype.includes', function(orig) {
  if (orig) {
    return orig;
  }
  var includes = function(searchElement, opt_fromIndex) {
    var array = this;
    if (array instanceof String) {
      array = String(array);
    }
    var len = array.length;
    for (var i = opt_fromIndex || 0; i < len; i++) {
      if (array[i] == searchElement || Object.is(array[i], searchElement)) {
        return true;
      }
    }
    return false;
  };
  return includes;
}, 'es7', 'es3');
$jscomp.polyfill('Array.prototype.keys', function(orig) {
  if (orig) {
    return orig;
  }
  var polyfill = function() {
    return $jscomp.iteratorFromArray(this, function(i) {
      return i;
    });
  };
  return polyfill;
}, 'es6', 'es3');
$jscomp.polyfill('Array.of', function(orig) {
  if (orig) {
    return orig;
  }
  var polyfill = function(var_args) {
    return Array.from(arguments);
  };
  return polyfill;
}, 'es6', 'es3');
$jscomp.polyfill('Array.prototype.values', function(orig) {
  if (orig) {
    return orig;
  }
  var polyfill = function() {
    return $jscomp.iteratorFromArray(this, function(k, v) {
      return v;
    });
  };
  return polyfill;
}, 'es6', 'es3');
$jscomp.makeIterator = function(iterable) {
  $jscomp.initSymbolIterator();
  var iteratorFunction = iterable[Symbol.iterator];
  return iteratorFunction ? iteratorFunction.call(iterable) : $jscomp.arrayIterator(iterable);
};
$jscomp.FORCE_POLYFILL_PROMISE = false;
$jscomp.polyfill('Promise', function(NativePromise) {
  if (NativePromise && !$jscomp.FORCE_POLYFILL_PROMISE) {
    return NativePromise;
  }
  function AsyncExecutor() {
    this.batch_ = null;
  }
  AsyncExecutor.prototype.asyncExecute = function(f) {
    if (this.batch_ == null) {
      this.batch_ = [];
      this.asyncExecuteBatch_();
    }
    this.batch_.push(f);
    return this;
  };
  AsyncExecutor.prototype.asyncExecuteBatch_ = function() {
    var self = this;
    this.asyncExecuteFunction(function() {
      self.executeBatch_();
    });
  };
  var nativeSetTimeout = $jscomp.global['setTimeout'];
  AsyncExecutor.prototype.asyncExecuteFunction = function(f) {
    nativeSetTimeout(f, 0);
  };
  AsyncExecutor.prototype.executeBatch_ = function() {
    while (this.batch_ && this.batch_.length) {
      var executingBatch = this.batch_;
      this.batch_ = [];
      for (var i = 0; i < executingBatch.length; ++i) {
        var f = executingBatch[i];
        delete executingBatch[i];
        try {
          f();
        } catch (error) {
          this.asyncThrow_(error);
        }
      }
    }
    this.batch_ = null;
  };
  AsyncExecutor.prototype.asyncThrow_ = function(exception) {
    this.asyncExecuteFunction(function() {
      throw exception;
    });
  };
  var PromiseState = {PENDING:0, FULFILLED:1, REJECTED:2};
  var PolyfillPromise = function(executor) {
    this.state_ = PromiseState.PENDING;
    this.result_ = undefined;
    this.onSettledCallbacks_ = [];
    var resolveAndReject = this.createResolveAndReject_();
    try {
      executor(resolveAndReject.resolve, resolveAndReject.reject);
    } catch (e) {
      resolveAndReject.reject(e);
    }
  };
  PolyfillPromise.prototype.createResolveAndReject_ = function() {
    var thisPromise = this;
    var alreadyCalled = false;
    function firstCallWins(method) {
      return function(x) {
        if (!alreadyCalled) {
          alreadyCalled = true;
          method.call(thisPromise, x);
        }
      };
    }
    return {resolve:firstCallWins(this.resolveTo_), reject:firstCallWins(this.reject_)};
  };
  PolyfillPromise.prototype.resolveTo_ = function(value) {
    if (value === this) {
      this.reject_(new TypeError('A Promise cannot resolve to itself'));
    } else {
      if (value instanceof PolyfillPromise) {
        this.settleSameAsPromise_(value);
      } else {
        if (isObject(value)) {
          this.resolveToNonPromiseObj_(value);
        } else {
          this.fulfill_(value);
        }
      }
    }
  };
  PolyfillPromise.prototype.resolveToNonPromiseObj_ = function(obj) {
    var thenMethod = undefined;
    try {
      thenMethod = obj.then;
    } catch (error) {
      this.reject_(error);
      return;
    }
    if (typeof thenMethod == 'function') {
      this.settleSameAsThenable_(thenMethod, obj);
    } else {
      this.fulfill_(obj);
    }
  };
  function isObject(value) {
    switch(typeof value) {
      case 'object':
        return value != null;
      case 'function':
        return true;
      default:
        return false;
    }
  }
  PolyfillPromise.prototype.reject_ = function(reason) {
    this.settle_(PromiseState.REJECTED, reason);
  };
  PolyfillPromise.prototype.fulfill_ = function(value) {
    this.settle_(PromiseState.FULFILLED, value);
  };
  PolyfillPromise.prototype.settle_ = function(settledState, valueOrReason) {
    if (this.state_ != PromiseState.PENDING) {
      throw new Error('Cannot settle(' + settledState + ', ' + valueOrReason | '): Promise already settled in state' + this.state_);
    }
    this.state_ = settledState;
    this.result_ = valueOrReason;
    this.executeOnSettledCallbacks_();
  };
  PolyfillPromise.prototype.executeOnSettledCallbacks_ = function() {
    if (this.onSettledCallbacks_ != null) {
      var callbacks = this.onSettledCallbacks_;
      for (var i = 0; i < callbacks.length; ++i) {
        callbacks[i].call();
        callbacks[i] = null;
      }
      this.onSettledCallbacks_ = null;
    }
  };
  var asyncExecutor = new AsyncExecutor;
  PolyfillPromise.prototype.settleSameAsPromise_ = function(promise) {
    var methods = this.createResolveAndReject_();
    promise.callWhenSettled_(methods.resolve, methods.reject);
  };
  PolyfillPromise.prototype.settleSameAsThenable_ = function(thenMethod, thenable) {
    var methods = this.createResolveAndReject_();
    try {
      thenMethod.call(thenable, methods.resolve, methods.reject);
    } catch (error) {
      methods.reject(error);
    }
  };
  PolyfillPromise.prototype.then = function(onFulfilled, onRejected) {
    var resolveChild;
    var rejectChild;
    var childPromise = new PolyfillPromise(function(resolve, reject) {
      resolveChild = resolve;
      rejectChild = reject;
    });
    function createCallback(paramF, defaultF) {
      if (typeof paramF == 'function') {
        return function(x) {
          try {
            resolveChild(paramF(x));
          } catch (error) {
            rejectChild(error);
          }
        };
      } else {
        return defaultF;
      }
    }
    this.callWhenSettled_(createCallback(onFulfilled, resolveChild), createCallback(onRejected, rejectChild));
    return childPromise;
  };
  PolyfillPromise.prototype['catch'] = function(onRejected) {
    return this.then(undefined, onRejected);
  };
  PolyfillPromise.prototype.callWhenSettled_ = function(onFulfilled, onRejected) {
    var thisPromise = this;
    function callback() {
      switch(thisPromise.state_) {
        case PromiseState.FULFILLED:
          onFulfilled(thisPromise.result_);
          break;
        case PromiseState.REJECTED:
          onRejected(thisPromise.result_);
          break;
        default:
          throw new Error('Unexpected state: ' + thisPromise.state_);
      }
    }
    if (this.onSettledCallbacks_ == null) {
      asyncExecutor.asyncExecute(callback);
    } else {
      this.onSettledCallbacks_.push(function() {
        asyncExecutor.asyncExecute(callback);
      });
    }
  };
  function resolvingPromise(opt_value) {
    if (opt_value instanceof PolyfillPromise) {
      return opt_value;
    } else {
      return new PolyfillPromise(function(resolve, reject) {
        resolve(opt_value);
      });
    }
  }
  PolyfillPromise['resolve'] = resolvingPromise;
  PolyfillPromise['reject'] = function(opt_reason) {
    return new PolyfillPromise(function(resolve, reject) {
      reject(opt_reason);
    });
  };
  PolyfillPromise['race'] = function(thenablesOrValues) {
    return new PolyfillPromise(function(resolve, reject) {
      var iterator = $jscomp.makeIterator(thenablesOrValues);
      for (var iterRec = iterator.next(); !iterRec.done; iterRec = iterator.next()) {
        resolvingPromise(iterRec.value).callWhenSettled_(resolve, reject);
      }
    });
  };
  PolyfillPromise['all'] = function(thenablesOrValues) {
    var iterator = $jscomp.makeIterator(thenablesOrValues);
    var iterRec = iterator.next();
    if (iterRec.done) {
      return resolvingPromise([]);
    } else {
      return new PolyfillPromise(function(resolveAll, rejectAll) {
        var resultsArray = [];
        var unresolvedCount = 0;
        function onFulfilled(i) {
          return function(ithResult) {
            resultsArray[i] = ithResult;
            unresolvedCount--;
            if (unresolvedCount == 0) {
              resolveAll(resultsArray);
            }
          };
        }
        do {
          resultsArray.push(undefined);
          unresolvedCount++;
          resolvingPromise(iterRec.value).callWhenSettled_(onFulfilled(resultsArray.length - 1), rejectAll);
          iterRec = iterator.next();
        } while (!iterRec.done);
      });
    }
  };
  return PolyfillPromise;
}, 'es6', 'es3');
$jscomp.executeAsyncGenerator = function(generator) {
  function passValueToGenerator(value) {
    return generator.next(value);
  }
  function passErrorToGenerator(error) {
    return generator['throw'](error);
  }
  return new Promise(function(resolve, reject) {
    function handleGeneratorRecord(genRec) {
      if (genRec.done) {
        resolve(genRec.value);
      } else {
        Promise.resolve(genRec.value).then(passValueToGenerator, passErrorToGenerator).then(handleGeneratorRecord, reject);
      }
    }
    handleGeneratorRecord(generator.next());
  });
};
$jscomp.owns = function(obj, prop) {
  return Object.prototype.hasOwnProperty.call(obj, prop);
};
$jscomp.polyfill('WeakMap', function(NativeWeakMap) {
  function isConformant() {
    if (!NativeWeakMap || !Object.seal) {
      return false;
    }
    try {
      var x = Object.seal({});
      var y = Object.seal({});
      var map = new NativeWeakMap([[x, 2], [y, 3]]);
      if (map.get(x) != 2 || map.get(y) != 3) {
        return false;
      }
      map['delete'](x);
      map.set(y, 4);
      return !map.has(x) && map.get(y) == 4;
    } catch (err) {
      return false;
    }
  }
  if (isConformant()) {
    return NativeWeakMap;
  }
  var prop = '$jscomp_hidden_' + Math.random().toString().substring(2);
  function insert(target) {
    if (!$jscomp.owns(target, prop)) {
      var obj = {};
      $jscomp.defineProperty(target, prop, {value:obj});
    }
  }
  function patch(name) {
    var prev = Object[name];
    if (prev) {
      Object[name] = function(target) {
        insert(target);
        return prev(target);
      };
    }
  }
  patch('freeze');
  patch('preventExtensions');
  patch('seal');
  var index = 0;
  var PolyfillWeakMap = function(opt_iterable) {
    this.id_ = (index += Math.random() + 1).toString();
    if (opt_iterable) {
      $jscomp.initSymbol();
      $jscomp.initSymbolIterator();
      var iter = $jscomp.makeIterator(opt_iterable);
      var entry;
      while (!(entry = iter.next()).done) {
        var item = entry.value;
        this.set(item[0], item[1]);
      }
    }
  };
  PolyfillWeakMap.prototype.set = function(key, value) {
    insert(key);
    if (!$jscomp.owns(key, prop)) {
      throw new Error('WeakMap key fail: ' + key);
    }
    key[prop][this.id_] = value;
    return this;
  };
  PolyfillWeakMap.prototype.get = function(key) {
    return $jscomp.owns(key, prop) ? key[prop][this.id_] : undefined;
  };
  PolyfillWeakMap.prototype.has = function(key) {
    return $jscomp.owns(key, prop) && $jscomp.owns(key[prop], this.id_);
  };
  PolyfillWeakMap.prototype['delete'] = function(key) {
    if (!$jscomp.owns(key, prop) || !$jscomp.owns(key[prop], this.id_)) {
      return false;
    }
    return delete key[prop][this.id_];
  };
  return PolyfillWeakMap;
}, 'es6', 'es3');
$jscomp.MapEntry = function() {
  this.previous;
  this.next;
  this.head;
  this.key;
  this.value;
};
$jscomp.polyfill('Map', function(NativeMap) {
  var isConformant = !$jscomp.ASSUME_NO_NATIVE_MAP && function() {
    if (!NativeMap || !NativeMap.prototype.entries || typeof Object.seal != 'function') {
      return false;
    }
    try {
      NativeMap = NativeMap;
      var key = Object.seal({x:4});
      var map = new NativeMap($jscomp.makeIterator([[key, 's']]));
      if (map.get(key) != 's' || map.size != 1 || map.get({x:4}) || map.set({x:4}, 't') != map || map.size != 2) {
        return false;
      }
      var iter = map.entries();
      var item = iter.next();
      if (item.done || item.value[0] != key || item.value[1] != 's') {
        return false;
      }
      item = iter.next();
      if (item.done || item.value[0].x != 4 || item.value[1] != 't' || !iter.next().done) {
        return false;
      }
      return true;
    } catch (err) {
      return false;
    }
  }();
  if (isConformant) {
    return NativeMap;
  }
  $jscomp.initSymbol();
  $jscomp.initSymbolIterator();
  var idMap = new WeakMap;
  var PolyfillMap = function(opt_iterable) {
    this.data_ = {};
    this.head_ = createHead();
    this.size = 0;
    if (opt_iterable) {
      var iter = $jscomp.makeIterator(opt_iterable);
      var entry;
      while (!(entry = iter.next()).done) {
        var item = entry.value;
        this.set(item[0], item[1]);
      }
    }
  };
  PolyfillMap.prototype.set = function(key, value) {
    var r = maybeGetEntry(this, key);
    if (!r.list) {
      r.list = this.data_[r.id] = [];
    }
    if (!r.entry) {
      r.entry = {next:this.head_, previous:this.head_.previous, head:this.head_, key:key, value:value};
      r.list.push(r.entry);
      this.head_.previous.next = r.entry;
      this.head_.previous = r.entry;
      this.size++;
    } else {
      r.entry.value = value;
    }
    return this;
  };
  PolyfillMap.prototype['delete'] = function(key) {
    var r = maybeGetEntry(this, key);
    if (r.entry && r.list) {
      r.list.splice(r.index, 1);
      if (!r.list.length) {
        delete this.data_[r.id];
      }
      r.entry.previous.next = r.entry.next;
      r.entry.next.previous = r.entry.previous;
      r.entry.head = null;
      this.size--;
      return true;
    }
    return false;
  };
  PolyfillMap.prototype.clear = function() {
    this.data_ = {};
    this.head_ = this.head_.previous = createHead();
    this.size = 0;
  };
  PolyfillMap.prototype.has = function(key) {
    return !!maybeGetEntry(this, key).entry;
  };
  PolyfillMap.prototype.get = function(key) {
    var entry = maybeGetEntry(this, key).entry;
    return entry && entry.value;
  };
  PolyfillMap.prototype.entries = function() {
    return makeIterator(this, function(entry) {
      return [entry.key, entry.value];
    });
  };
  PolyfillMap.prototype.keys = function() {
    return makeIterator(this, function(entry) {
      return entry.key;
    });
  };
  PolyfillMap.prototype.values = function() {
    return makeIterator(this, function(entry) {
      return entry.value;
    });
  };
  PolyfillMap.prototype.forEach = function(callback, opt_thisArg) {
    var iter = this.entries();
    var item;
    while (!(item = iter.next()).done) {
      var entry = item.value;
      callback.call(opt_thisArg, entry[1], entry[0], this);
    }
  };
  PolyfillMap.prototype[Symbol.iterator] = PolyfillMap.prototype.entries;
  var maybeGetEntry = function(map, key) {
    var id = getId(key);
    var list = map.data_[id];
    if (list && $jscomp.owns(map.data_, id)) {
      for (var index = 0; index < list.length; index++) {
        var entry = list[index];
        if (key !== key && entry.key !== entry.key || key === entry.key) {
          return {id:id, list:list, index:index, entry:entry};
        }
      }
    }
    return {id:id, list:list, index:-1, entry:undefined};
  };
  var makeIterator = function(map, func) {
    var entry = map.head_;
    return $jscomp.iteratorPrototype(function() {
      if (entry) {
        while (entry.head != map.head_) {
          entry = entry.previous;
        }
        while (entry.next != entry.head) {
          entry = entry.next;
          return {done:false, value:func(entry)};
        }
        entry = null;
      }
      return {done:true, value:void 0};
    });
  };
  var createHead = function() {
    var head = {};
    head.previous = head.next = head.head = head;
    return head;
  };
  var mapIndex = 0;
  var getId = function(obj) {
    var type = obj && typeof obj;
    if (type == 'object' || type == 'function') {
      obj = obj;
      if (!idMap.has(obj)) {
        var id = '' + ++mapIndex;
        idMap.set(obj, id);
        return id;
      }
      return idMap.get(obj);
    }
    return 'p_' + obj;
  };
  return PolyfillMap;
}, 'es6', 'es3');
$jscomp.polyfill('Math.acosh', function(orig) {
  if (orig) {
    return orig;
  }
  var polyfill = function(x) {
    x = Number(x);
    return Math.log(x + Math.sqrt(x * x - 1));
  };
  return polyfill;
}, 'es6', 'es3');
$jscomp.polyfill('Math.asinh', function(orig) {
  if (orig) {
    return orig;
  }
  var polyfill = function(x) {
    x = Number(x);
    if (x === 0) {
      return x;
    }
    var y = Math.log(Math.abs(x) + Math.sqrt(x * x + 1));
    return x < 0 ? -y : y;
  };
  return polyfill;
}, 'es6', 'es3');
$jscomp.polyfill('Math.log1p', function(orig) {
  if (orig) {
    return orig;
  }
  var polyfill = function(x) {
    x = Number(x);
    if (x < 0.25 && x > -0.25) {
      var y = x;
      var d = 1;
      var z = x;
      var zPrev = 0;
      var s = 1;
      while (zPrev != z) {
        y *= x;
        s *= -1;
        z = (zPrev = z) + s * y / ++d;
      }
      return z;
    }
    return Math.log(1 + x);
  };
  return polyfill;
}, 'es6', 'es3');
$jscomp.polyfill('Math.atanh', function(orig) {
  if (orig) {
    return orig;
  }
  var log1p = Math.log1p;
  var polyfill = function(x) {
    x = Number(x);
    return (log1p(x) - log1p(-x)) / 2;
  };
  return polyfill;
}, 'es6', 'es3');
$jscomp.polyfill('Math.cbrt', function(orig) {
  if (orig) {
    return orig;
  }
  var polyfill = function(x) {
    if (x === 0) {
      return x;
    }
    x = Number(x);
    var y = Math.pow(Math.abs(x), 1 / 3);
    return x < 0 ? -y : y;
  };
  return polyfill;
}, 'es6', 'es3');
$jscomp.polyfill('Math.clz32', function(orig) {
  if (orig) {
    return orig;
  }
  var polyfill = function(x) {
    x = Number(x) >>> 0;
    if (x === 0) {
      return 32;
    }
    var result = 0;
    if ((x & 4294901760) === 0) {
      x <<= 16;
      result += 16;
    }
    if ((x & 4278190080) === 0) {
      x <<= 8;
      result += 8;
    }
    if ((x & 4026531840) === 0) {
      x <<= 4;
      result += 4;
    }
    if ((x & 3221225472) === 0) {
      x <<= 2;
      result += 2;
    }
    if ((x & 2147483648) === 0) {
      result++;
    }
    return result;
  };
  return polyfill;
}, 'es6', 'es3');
$jscomp.polyfill('Math.cosh', function(orig) {
  if (orig) {
    return orig;
  }
  var exp = Math.exp;
  var polyfill = function(x) {
    x = Number(x);
    return (exp(x) + exp(-x)) / 2;
  };
  return polyfill;
}, 'es6', 'es3');
$jscomp.polyfill('Math.expm1', function(orig) {
  if (orig) {
    return orig;
  }
  var polyfill = function(x) {
    x = Number(x);
    if (x < .25 && x > -.25) {
      var y = x;
      var d = 1;
      var z = x;
      var zPrev = 0;
      while (zPrev != z) {
        y *= x / ++d;
        z = (zPrev = z) + y;
      }
      return z;
    }
    return Math.exp(x) - 1;
  };
  return polyfill;
}, 'es6', 'es3');
$jscomp.polyfill('Math.hypot', function(orig) {
  if (orig) {
    return orig;
  }
  var polyfill = function(x, y, var_args) {
    x = Number(x);
    y = Number(y);
    var i, z, sum;
    var max = Math.max(Math.abs(x), Math.abs(y));
    for (i = 2; i < arguments.length; i++) {
      max = Math.max(max, Math.abs(arguments[i]));
    }
    if (max > 1e100 || max < 1e-100) {
      x = x / max;
      y = y / max;
      sum = x * x + y * y;
      for (i = 2; i < arguments.length; i++) {
        z = Number(arguments[i]) / max;
        sum += z * z;
      }
      return Math.sqrt(sum) * max;
    } else {
      sum = x * x + y * y;
      for (i = 2; i < arguments.length; i++) {
        z = Number(arguments[i]);
        sum += z * z;
      }
      return Math.sqrt(sum);
    }
  };
  return polyfill;
}, 'es6', 'es3');
$jscomp.polyfill('Math.imul', function(orig) {
  if (orig) {
    return orig;
  }
  var polyfill = function(a, b) {
    a = Number(a);
    b = Number(b);
    var ah = a >>> 16 & 65535;
    var al = a & 65535;
    var bh = b >>> 16 & 65535;
    var bl = b & 65535;
    var lh = ah * bl + al * bh << 16 >>> 0;
    return al * bl + lh | 0;
  };
  return polyfill;
}, 'es6', 'es3');
$jscomp.polyfill('Math.log10', function(orig) {
  if (orig) {
    return orig;
  }
  var polyfill = function(x) {
    return Math.log(x) / Math.LN10;
  };
  return polyfill;
}, 'es6', 'es3');
$jscomp.polyfill('Math.log2', function(orig) {
  if (orig) {
    return orig;
  }
  var polyfill = function(x) {
    return Math.log(x) / Math.LN2;
  };
  return polyfill;
}, 'es6', 'es3');
$jscomp.polyfill('Math.sign', function(orig) {
  if (orig) {
    return orig;
  }
  var polyfill = function(x) {
    x = Number(x);
    return x === 0 || isNaN(x) ? x : x > 0 ? 1 : -1;
  };
  return polyfill;
}, 'es6', 'es3');
$jscomp.polyfill('Math.sinh', function(orig) {
  if (orig) {
    return orig;
  }
  var exp = Math.exp;
  var polyfill = function(x) {
    x = Number(x);
    if (x === 0) {
      return x;
    }
    return (exp(x) - exp(-x)) / 2;
  };
  return polyfill;
}, 'es6', 'es3');
$jscomp.polyfill('Math.tanh', function(orig) {
  if (orig) {
    return orig;
  }
  var polyfill = function(x) {
    x = Number(x);
    if (x === 0) {
      return x;
    }
    var y = Math.exp(-2 * Math.abs(x));
    var z = (1 - y) / (1 + y);
    return x < 0 ? -z : z;
  };
  return polyfill;
}, 'es6', 'es3');
$jscomp.polyfill('Math.trunc', function(orig) {
  if (orig) {
    return orig;
  }
  var polyfill = function(x) {
    x = Number(x);
    if (isNaN(x) || x === Infinity || x === -Infinity || x === 0) {
      return x;
    }
    var y = Math.floor(Math.abs(x));
    return x < 0 ? -y : y;
  };
  return polyfill;
}, 'es6', 'es3');
$jscomp.polyfill('Number.EPSILON', function(orig) {
  return Math.pow(2, -52);
}, 'es6', 'es3');
$jscomp.polyfill('Number.MAX_SAFE_INTEGER', function() {
  return 9007199254740991;
}, 'es6', 'es3');
$jscomp.polyfill('Number.MIN_SAFE_INTEGER', function() {
  return -9007199254740991;
}, 'es6', 'es3');
$jscomp.polyfill('Number.isFinite', function(orig) {
  if (orig) {
    return orig;
  }
  var polyfill = function(x) {
    if (typeof x !== 'number') {
      return false;
    }
    return !isNaN(x) && x !== Infinity && x !== -Infinity;
  };
  return polyfill;
}, 'es6', 'es3');
$jscomp.polyfill('Number.isInteger', function(orig) {
  if (orig) {
    return orig;
  }
  var polyfill = function(x) {
    if (!Number.isFinite(x)) {
      return false;
    }
    return x === Math.floor(x);
  };
  return polyfill;
}, 'es6', 'es3');
$jscomp.polyfill('Number.isNaN', function(orig) {
  if (orig) {
    return orig;
  }
  var polyfill = function(x) {
    return typeof x === 'number' && isNaN(x);
  };
  return polyfill;
}, 'es6', 'es3');
$jscomp.polyfill('Number.isSafeInteger', function(orig) {
  if (orig) {
    return orig;
  }
  var polyfill = function(x) {
    return Number.isInteger(x) && Math.abs(x) <= Number.MAX_SAFE_INTEGER;
  };
  return polyfill;
}, 'es6', 'es3');
$jscomp.polyfill('Object.assign', function(orig) {
  if (orig) {
    return orig;
  }
  var polyfill = function(target, var_args) {
    for (var i = 1; i < arguments.length; i++) {
      var source = arguments[i];
      if (!source) {
        continue;
      }
      for (var key in source) {
        if ($jscomp.owns(source, key)) {
          target[key] = source[key];
        }
      }
    }
    return target;
  };
  return polyfill;
}, 'es6', 'es3');
$jscomp.polyfill('Object.entries', function(orig) {
  if (orig) {
    return orig;
  }
  var entries = function(obj) {
    var result = [];
    for (var key in obj) {
      if ($jscomp.owns(obj, key)) {
        result.push([key, obj[key]]);
      }
    }
    return result;
  };
  return entries;
}, 'es8', 'es3');
$jscomp.polyfill('Object.getOwnPropertySymbols', function(orig) {
  if (orig) {
    return orig;
  }
  return function() {
    return [];
  };
}, 'es6', 'es5');
$jscomp.polyfill('Reflect.ownKeys', function(orig) {
  if (orig) {
    return orig;
  }
  var symbolPrefix = 'jscomp_symbol_';
  function isSymbol(key) {
    return key.substring(0, symbolPrefix.length) == symbolPrefix;
  }
  var polyfill = function(target) {
    var keys = [];
    var names = Object.getOwnPropertyNames(target);
    var symbols = Object.getOwnPropertySymbols(target);
    for (var i = 0; i < names.length; i++) {
      (isSymbol(names[i]) ? symbols : keys).push(names[i]);
    }
    return keys.concat(symbols);
  };
  return polyfill;
}, 'es6', 'es5');
$jscomp.polyfill('Object.getOwnPropertyDescriptors', function(orig) {
  if (orig) {
    return orig;
  }
  var getOwnPropertyDescriptors = function(obj) {
    var result = {};
    var keys = Reflect.ownKeys(obj);
    for (var i = 0; i < keys.length; i++) {
      result[keys[i]] = Object.getOwnPropertyDescriptor(obj, keys[i]);
    }
    return result;
  };
  return getOwnPropertyDescriptors;
}, 'es8', 'es5');
$jscomp.underscoreProtoCanBeSet = function() {
  var x = {a:true};
  var y = {};
  try {
    y.__proto__ = x;
    return y.a;
  } catch (e) {
  }
  return false;
};
$jscomp.setPrototypeOf = typeof Object.setPrototypeOf == 'function' ? Object.setPrototypeOf : $jscomp.underscoreProtoCanBeSet() ? function(target, proto) {
  target.__proto__ = proto;
  if (target.__proto__ !== proto) {
    throw new TypeError(target + ' is not extensible');
  }
  return target;
} : null;
$jscomp.polyfill('Object.setPrototypeOf', function(orig) {
  return orig || $jscomp.setPrototypeOf;
}, 'es6', 'es5');
$jscomp.polyfill('Object.values', function(orig) {
  if (orig) {
    return orig;
  }
  var values = function(obj) {
    var result = [];
    for (var key in obj) {
      if ($jscomp.owns(obj, key)) {
        result.push(obj[key]);
      }
    }
    return result;
  };
  return values;
}, 'es8', 'es3');
$jscomp.polyfill('Reflect.apply', function(orig) {
  if (orig) {
    return orig;
  }
  var apply = Function.prototype.apply;
  var polyfill = function(target, thisArg, argList) {
    return apply.call(target, thisArg, argList);
  };
  return polyfill;
}, 'es6', 'es3');
$jscomp.objectCreate = $jscomp.ASSUME_ES5 || typeof Object.create == 'function' ? Object.create : function(prototype) {
  var ctor = function() {
  };
  ctor.prototype = prototype;
  return new ctor;
};
$jscomp.construct = function() {
  function reflectConstructWorks() {
    function Base() {
    }
    function Derived() {
    }
    new Base;
    Reflect.construct(Base, [], Derived);
    return new Base instanceof Base;
  }
  if (typeof Reflect != 'undefined' && Reflect.construct) {
    if (reflectConstructWorks()) {
      return Reflect.construct;
    }
    var brokenConstruct = Reflect.construct;
    var patchedConstruct = function(target, argList, opt_newTarget) {
      var out = brokenConstruct(target, argList);
      if (opt_newTarget) {
        Reflect.setPrototypeOf(out, opt_newTarget.prototype);
      }
      return out;
    };
    return patchedConstruct;
  }
  function construct(target, argList, opt_newTarget) {
    if (opt_newTarget === undefined) {
      opt_newTarget = target;
    }
    var proto = opt_newTarget.prototype || Object.prototype;
    var obj = $jscomp.objectCreate(proto);
    var apply = Function.prototype.apply;
    var out = apply.call(target, obj, argList);
    return out || obj;
  }
  return construct;
}();
$jscomp.polyfill('Reflect.construct', function(orig) {
  return $jscomp.construct;
}, 'es6', 'es3');
$jscomp.polyfill('Reflect.defineProperty', function(orig) {
  if (orig) {
    return orig;
  }
  var polyfill = function(target, propertyKey, attributes) {
    try {
      Object.defineProperty(target, propertyKey, attributes);
      var desc = Object.getOwnPropertyDescriptor(target, propertyKey);
      if (!desc) {
        return false;
      }
      return desc.configurable === (attributes.configurable || false) && desc.enumerable === (attributes.enumerable || false) && ('value' in desc ? desc.value === attributes.value && desc.writable === (attributes.writable || false) : desc.get === attributes.get && desc.set === attributes.set);
    } catch (err) {
      return false;
    }
  };
  return polyfill;
}, 'es6', 'es5');
$jscomp.polyfill('Reflect.deleteProperty', function(orig) {
  if (orig) {
    return orig;
  }
  var polyfill = function(target, propertyKey) {
    if (!$jscomp.owns(target, propertyKey)) {
      return true;
    }
    try {
      return delete target[propertyKey];
    } catch (err) {
      return false;
    }
  };
  return polyfill;
}, 'es6', 'es3');
$jscomp.polyfill('Reflect.getOwnPropertyDescriptor', function(orig) {
  return orig || Object.getOwnPropertyDescriptor;
}, 'es6', 'es5');
$jscomp.polyfill('Reflect.getPrototypeOf', function(orig) {
  return orig || Object.getPrototypeOf;
}, 'es6', 'es5');
$jscomp.findDescriptor = function(target, propertyKey) {
  var obj = target;
  while (obj) {
    var property = Reflect.getOwnPropertyDescriptor(obj, propertyKey);
    if (property) {
      return property;
    }
    obj = Reflect.getPrototypeOf(obj);
  }
  return undefined;
};
$jscomp.polyfill('Reflect.get', function(orig) {
  if (orig) {
    return orig;
  }
  var polyfill = function(target, propertyKey, opt_receiver) {
    if (arguments.length <= 2) {
      return target[propertyKey];
    }
    var property = $jscomp.findDescriptor(target, propertyKey);
    if (property) {
      return property.get ? property.get.call(opt_receiver) : property.value;
    }
    return undefined;
  };
  return polyfill;
}, 'es6', 'es5');
$jscomp.polyfill('Reflect.has', function(orig) {
  if (orig) {
    return orig;
  }
  var polyfill = function(target, propertyKey) {
    return propertyKey in target;
  };
  return polyfill;
}, 'es6', 'es3');
$jscomp.polyfill('Reflect.isExtensible', function(orig) {
  if (orig) {
    return orig;
  }
  if ($jscomp.ASSUME_ES5 || typeof Object.isExtensible == 'function') {
    return Object.isExtensible;
  }
  return function() {
    return true;
  };
}, 'es6', 'es3');
$jscomp.polyfill('Reflect.preventExtensions', function(orig) {
  if (orig) {
    return orig;
  }
  if (!($jscomp.ASSUME_ES5 || typeof Object.preventExtensions == 'function')) {
    return function() {
      return false;
    };
  }
  var polyfill = function(target) {
    Object.preventExtensions(target);
    return !Object.isExtensible(target);
  };
  return polyfill;
}, 'es6', 'es3');
$jscomp.polyfill('Reflect.set', function(orig) {
  if (orig) {
    return orig;
  }
  var polyfill = function(target, propertyKey, value, opt_receiver) {
    var property = $jscomp.findDescriptor(target, propertyKey);
    if (!property) {
      if (Reflect.isExtensible(target)) {
        target[propertyKey] = value;
        return true;
      }
      return false;
    }
    if (property.set) {
      property.set.call(arguments.length > 3 ? opt_receiver : target, value);
      return true;
    } else {
      if (property.writable && !Object.isFrozen(target)) {
        target[propertyKey] = value;
        return true;
      }
    }
    return false;
  };
  return polyfill;
}, 'es6', 'es5');
$jscomp.polyfill('Reflect.setPrototypeOf', function(orig) {
  if (orig) {
    return orig;
  } else {
    if ($jscomp.setPrototypeOf) {
      var setPrototypeOf = $jscomp.setPrototypeOf;
      var polyfill = function(target, proto) {
        try {
          setPrototypeOf(target, proto);
          return true;
        } catch (e) {
          return false;
        }
      };
      return polyfill;
    } else {
      return null;
    }
  }
}, 'es6', 'es5');
$jscomp.polyfill('Set', function(NativeSet) {
  var isConformant = !$jscomp.ASSUME_NO_NATIVE_SET && function() {
    if (!NativeSet || !NativeSet.prototype.entries || typeof Object.seal != 'function') {
      return false;
    }
    try {
      NativeSet = NativeSet;
      var value = Object.seal({x:4});
      var set = new NativeSet($jscomp.makeIterator([value]));
      if (!set.has(value) || set.size != 1 || set.add(value) != set || set.size != 1 || set.add({x:4}) != set || set.size != 2) {
        return false;
      }
      var iter = set.entries();
      var item = iter.next();
      if (item.done || item.value[0] != value || item.value[1] != value) {
        return false;
      }
      item = iter.next();
      if (item.done || item.value[0] == value || item.value[0].x != 4 || item.value[1] != item.value[0]) {
        return false;
      }
      return iter.next().done;
    } catch (err) {
      return false;
    }
  }();
  if (isConformant) {
    return NativeSet;
  }
  $jscomp.initSymbol();
  $jscomp.initSymbolIterator();
  var PolyfillSet = function(opt_iterable) {
    this.map_ = new Map;
    if (opt_iterable) {
      var iter = $jscomp.makeIterator(opt_iterable);
      var entry;
      while (!(entry = iter.next()).done) {
        var item = entry.value;
        this.add(item);
      }
    }
    this.size = this.map_.size;
  };
  PolyfillSet.prototype.add = function(value) {
    this.map_.set(value, value);
    this.size = this.map_.size;
    return this;
  };
  PolyfillSet.prototype['delete'] = function(value) {
    var result = this.map_['delete'](value);
    this.size = this.map_.size;
    return result;
  };
  PolyfillSet.prototype.clear = function() {
    this.map_.clear();
    this.size = 0;
  };
  PolyfillSet.prototype.has = function(value) {
    return this.map_.has(value);
  };
  PolyfillSet.prototype.entries = function() {
    return this.map_.entries();
  };
  PolyfillSet.prototype.values = function() {
    return this.map_.values();
  };
  PolyfillSet.prototype.keys = PolyfillSet.prototype.values;
  PolyfillSet.prototype[Symbol.iterator] = PolyfillSet.prototype.values;
  PolyfillSet.prototype.forEach = function(callback, opt_thisArg) {
    var set = this;
    this.map_.forEach(function(value) {
      return callback.call(opt_thisArg, value, value, set);
    });
  };
  return PolyfillSet;
}, 'es6', 'es3');
$jscomp.checkStringArgs = function(thisArg, arg, func) {
  if (thisArg == null) {
    throw new TypeError("The 'this' value for String.prototype." + func + ' must not be null or undefined');
  }
  if (arg instanceof RegExp) {
    throw new TypeError('First argument to String.prototype.' + func + ' must not be a regular expression');
  }
  return thisArg + '';
};
$jscomp.polyfill('String.prototype.codePointAt', function(orig) {
  if (orig) {
    return orig;
  }
  var polyfill = function(position) {
    var string = $jscomp.checkStringArgs(this, null, 'codePointAt');
    var size = string.length;
    position = Number(position) || 0;
    if (!(position >= 0 && position < size)) {
      return void 0;
    }
    position = position | 0;
    var first = string.charCodeAt(position);
    if (first < 55296 || first > 56319 || position + 1 === size) {
      return first;
    }
    var second = string.charCodeAt(position + 1);
    if (second < 56320 || second > 57343) {
      return first;
    }
    return (first - 55296) * 1024 + second + 9216;
  };
  return polyfill;
}, 'es6', 'es3');
$jscomp.polyfill('String.prototype.endsWith', function(orig) {
  if (orig) {
    return orig;
  }
  var polyfill = function(searchString, opt_position) {
    var string = $jscomp.checkStringArgs(this, searchString, 'endsWith');
    searchString = searchString + '';
    if (opt_position === void 0) {
      opt_position = string.length;
    }
    var i = Math.max(0, Math.min(opt_position | 0, string.length));
    var j = searchString.length;
    while (j > 0 && i > 0) {
      if (string[--i] != searchString[--j]) {
        return false;
      }
    }
    return j <= 0;
  };
  return polyfill;
}, 'es6', 'es3');
$jscomp.polyfill('String.fromCodePoint', function(orig) {
  if (orig) {
    return orig;
  }
  var polyfill = function(var_args) {
    var result = '';
    for (var i = 0; i < arguments.length; i++) {
      var code = Number(arguments[i]);
      if (code < 0 || code > 1114111 || code !== Math.floor(code)) {
        throw new RangeError('invalid_code_point ' + code);
      }
      if (code <= 65535) {
        result += String.fromCharCode(code);
      } else {
        code -= 65536;
        result += String.fromCharCode(code >>> 10 & 1023 | 55296);
        result += String.fromCharCode(code & 1023 | 56320);
      }
    }
    return result;
  };
  return polyfill;
}, 'es6', 'es3');
$jscomp.polyfill('String.prototype.includes', function(orig) {
  if (orig) {
    return orig;
  }
  var polyfill = function(searchString, opt_position) {
    var string = $jscomp.checkStringArgs(this, searchString, 'includes');
    return string.indexOf(searchString, opt_position || 0) !== -1;
  };
  return polyfill;
}, 'es6', 'es3');
$jscomp.polyfill('String.prototype.repeat', function(orig) {
  if (orig) {
    return orig;
  }
  var polyfill = function(copies) {
    var string = $jscomp.checkStringArgs(this, null, 'repeat');
    if (copies < 0 || copies > 1342177279) {
      throw new RangeError('Invalid count value');
    }
    copies = copies | 0;
    var result = '';
    while (copies) {
      if (copies & 1) {
        result += string;
      }
      if (copies >>>= 1) {
        string += string;
      }
    }
    return result;
  };
  return polyfill;
}, 'es6', 'es3');
$jscomp.stringPadding = function(padString, padLength) {
  var padding = padString !== undefined ? String(padString) : ' ';
  if (!(padLength > 0) || !padding) {
    return '';
  }
  var repeats = Math.ceil(padLength / padding.length);
  return padding.repeat(repeats).substring(0, padLength);
};
$jscomp.polyfill('String.prototype.padEnd', function(orig) {
  if (orig) {
    return orig;
  }
  var padEnd = function(targetLength, opt_padString) {
    var string = $jscomp.checkStringArgs(this, null, 'padStart');
    var padLength = targetLength - string.length;
    return string + $jscomp.stringPadding(opt_padString, padLength);
  };
  return padEnd;
}, 'es8', 'es3');
$jscomp.polyfill('String.prototype.padStart', function(orig) {
  if (orig) {
    return orig;
  }
  var padStart = function(targetLength, opt_padString) {
    var string = $jscomp.checkStringArgs(this, null, 'padStart');
    var padLength = targetLength - string.length;
    return $jscomp.stringPadding(opt_padString, padLength) + string;
  };
  return padStart;
}, 'es8', 'es3');
$jscomp.polyfill('String.prototype.startsWith', function(orig) {
  if (orig) {
    return orig;
  }
  var polyfill = function(searchString, opt_position) {
    var string = $jscomp.checkStringArgs(this, searchString, 'startsWith');
    searchString = searchString + '';
    var strLen = string.length;
    var searchLen = searchString.length;
    var i = Math.max(0, Math.min(opt_position | 0, string.length));
    var j = 0;
    while (j < searchLen && i < strLen) {
      if (string[i++] != searchString[j++]) {
        return false;
      }
    }
    return j >= searchLen;
  };
  return polyfill;
}, 'es6', 'es3');
$jscomp.arrayFromIterator = function(iterator) {
  var i;
  var arr = [];
  while (!(i = iterator.next()).done) {
    arr.push(i.value);
  }
  return arr;
};
$jscomp.arrayFromIterable = function(iterable) {
  if (iterable instanceof Array) {
    return iterable;
  } else {
    return $jscomp.arrayFromIterator($jscomp.makeIterator(iterable));
  }
};
$jscomp.inherits = function(childCtor, parentCtor) {
  childCtor.prototype = $jscomp.objectCreate(parentCtor.prototype);
  childCtor.prototype.constructor = childCtor;
  if ($jscomp.setPrototypeOf) {
    var setPrototypeOf = $jscomp.setPrototypeOf;
    setPrototypeOf(childCtor, parentCtor);
  } else {
    for (var p in parentCtor) {
      if (p == 'prototype') {
        continue;
      }
      if (Object.defineProperties) {
        var descriptor = Object.getOwnPropertyDescriptor(parentCtor, p);
        if (descriptor) {
          Object.defineProperty(childCtor, p, descriptor);
        }
      } else {
        childCtor[p] = parentCtor[p];
      }
    }
  }
  childCtor.superClass_ = parentCtor.prototype;
};
$jscomp.polyfill('WeakSet', function(NativeWeakSet) {
  function isConformant() {
    if (!NativeWeakSet || !Object.seal) {
      return false;
    }
    try {
      var x = Object.seal({});
      var y = Object.seal({});
      var set = new NativeWeakSet([x]);
      if (!set.has(x) || set.has(y)) {
        return false;
      }
      set['delete'](x);
      set.add(y);
      return !set.has(x) && set.has(y);
    } catch (err) {
      return false;
    }
  }
  if (isConformant()) {
    return NativeWeakSet;
  }
  var PolyfillWeakSet = function(opt_iterable) {
    this.map_ = new WeakMap;
    if (opt_iterable) {
      $jscomp.initSymbol();
      $jscomp.initSymbolIterator();
      var iter = $jscomp.makeIterator(opt_iterable);
      var entry;
      while (!(entry = iter.next()).done) {
        var item = entry.value;
        this.add(item);
      }
    }
  };
  PolyfillWeakSet.prototype.add = function(elem) {
    this.map_.set(elem, true);
    return this;
  };
  PolyfillWeakSet.prototype.has = function(elem) {
    return this.map_.has(elem);
  };
  PolyfillWeakSet.prototype['delete'] = function(elem) {
    return this.map_['delete'](elem);
  };
  return PolyfillWeakSet;
}, 'es6', 'es3');
try {
  if (Array.prototype.values.toString().indexOf('[native code]') == -1) {
    delete Array.prototype.values;
  }
} catch (e) {
}
Ext.define('Ext.ux.Gauge', {extend:'Ext.Gadget', xtype:'gauge', requires:['Ext.util.Region'], config:{padding:10, trackStart:135, trackLength:270, angleOffset:0, minValue:0, maxValue:100, value:50, clockwise:true, textTpl:['\x3ctpl\x3e{value:number("0.00")}%\x3c/tpl\x3e'], textAlign:'c-c', trackStyle:{outerRadius:'100%', innerRadius:'100% - 20', round:false}, valueStyle:{outerRadius:'100% - 2', innerRadius:'100% - 18', round:false}, animation:true}, baseCls:Ext.baseCSSPrefix + 'gauge', template:[{reference:'bodyElement', 
children:[{reference:'textElement', cls:Ext.baseCSSPrefix + 'gauge-text'}]}], defaultBindProperty:'value', pathAttributes:{fill:true, fillOpacity:true, stroke:true, strokeOpacity:true, strokeWidth:true}, easings:{linear:Ext.identityFn, 'in':function(t) {
  return t * t * t;
}, out:function(t) {
  return --t * t * t + 1;
}, inOut:function(t) {
  return t < 0.5 ? 4 * t * t * t : (t - 1) * (2 * t - 2) * (2 * t - 2) + 1;
}}, resizeDelay:0, resizeTimerId:0, size:null, svgNS:'http://www.w3.org/2000/svg', svg:null, defs:null, trackArc:null, valueArc:null, trackGradient:null, valueGradient:null, fx:null, fxValue:0, fxAngleOffset:0, constructor:function(config) {
  var me = this;
  me.fitSectorInRectCache = {startAngle:null, lengthAngle:null, minX:null, maxX:null, minY:null, maxY:null};
  me.interpolator = me.createInterpolator();
  me.callParent([config]);
  me.el.on('resize', 'onElementResize', me);
}, doDestroy:function() {
  var me = this;
  Ext.undefer(me.resizeTimerId);
  me.el.un('resize', 'onElementResize', me);
  me.stopAnimation();
  me.svg = Ext.destroy(me.svg);
  me.callParent();
}, onElementResize:function(element, size) {
  this.handleResize(size);
}, handleResize:function(size, instantly) {
  var me = this, el = me.element;
  if (!(el && (size = size || el.getSize()) && size.width && size.height)) {
    return;
  }
  me.resizeTimerId = Ext.undefer(me.resizeTimerId);
  if (!instantly && me.resizeDelay) {
    me.resizeTimerId = Ext.defer(me.handleResize, me.resizeDelay, me, [size, true]);
    return;
  }
  me.size = size;
  me.resizeHandler(size);
}, updateMinValue:function(minValue) {
  var me = this;
  me.interpolator.setDomain(minValue, me.getMaxValue());
  if (!me.isConfiguring) {
    me.render();
  }
}, updateMaxValue:function(maxValue) {
  var me = this;
  me.interpolator.setDomain(me.getMinValue(), maxValue);
  if (!me.isConfiguring) {
    me.render();
  }
}, updateAngleOffset:function(angleOffset, oldAngleOffset) {
  var me = this, animation = me.getAnimation();
  me.fxAngleOffset = angleOffset;
  if (!me.isConfiguring) {
    if (animation.duration) {
      me.animate(oldAngleOffset, angleOffset, animation.duration, me.easings[animation.easing], function(angleOffset) {
        me.fxAngleOffset = angleOffset;
        me.render();
      });
    } else {
      me.render();
    }
  }
}, applyTrackStart:function(trackStart) {
  if (trackStart < 0 || trackStart >= 360) {
    Ext.raise("'trackStart' should be within [0, 360).");
  }
  return trackStart;
}, applyTrackLength:function(trackLength) {
  if (trackLength <= 0 || trackLength > 360) {
    Ext.raise("'trackLength' should be within (0, 360].");
  }
  return trackLength;
}, updateTrackStart:function(trackStart) {
  var me = this;
  if (!me.isConfiguring) {
    me.render();
  }
}, updateTrackLength:function(trackLength) {
  var me = this;
  me.interpolator.setRange(0, trackLength);
  if (!me.isConfiguring) {
    me.render();
  }
}, applyPadding:function(padding) {
  if (typeof padding === 'string') {
    var ratio = parseFloat(padding) / 100;
    return function(x) {
      return x * ratio;
    };
  }
  return function() {
    return padding;
  };
}, updatePadding:function() {
  if (!this.isConfiguring) {
    this.render();
  }
}, applyValue:function(value) {
  var minValue = this.getMinValue(), maxValue = this.getMaxValue();
  return Math.min(Math.max(value, minValue), maxValue);
}, updateValue:function(value, oldValue) {
  var me = this, animation = me.getAnimation();
  me.fxValue = value;
  if (!me.isConfiguring) {
    me.writeText();
    if (animation.duration) {
      me.animate(oldValue, value, animation.duration, me.easings[animation.easing], function(value) {
        me.fxValue = value;
        me.render();
      });
    } else {
      me.render();
    }
  }
}, applyTextTpl:function(textTpl) {
  if (textTpl && !textTpl.isTemplate) {
    textTpl = new Ext.XTemplate(textTpl);
  }
  return textTpl;
}, updateTextTpl:function() {
  this.writeText();
  if (!this.isConfiguring) {
    this.centerText();
  }
}, writeText:function(options) {
  var me = this, value = me.getValue(), minValue = me.getMinValue(), maxValue = me.getMaxValue(), delta = maxValue - minValue, textTpl = me.getTextTpl();
  textTpl.overwrite(me.textElement, {value:value, percent:(value - minValue) / delta * 100, minValue:minValue, maxValue:maxValue, delta:delta});
}, centerText:function(cx, cy, sectorRegion, innerRadius, outerRadius) {
  var textElement = this.textElement, textAlign = this.getTextAlign(), alignedRegion, textBox;
  if (Ext.Number.isEqual(innerRadius, 0, 0.1) || sectorRegion.isOutOfBound({x:cx, y:cy})) {
    alignedRegion = textElement.getRegion().alignTo({align:textAlign, target:sectorRegion});
    textElement.setLeft(alignedRegion.left);
    textElement.setTop(alignedRegion.top);
  } else {
    textBox = textElement.getBox();
    textElement.setLeft(cx - textBox.width / 2);
    textElement.setTop(cy - textBox.height / 2);
  }
}, camelCaseRe:/([a-z])([A-Z])/g, camelToHyphen:function(name) {
  return name.replace(this.camelCaseRe, '$1-$2').toLowerCase();
}, applyTrackStyle:function(trackStyle) {
  var me = this, trackGradient;
  trackStyle.innerRadius = me.getRadiusFn(trackStyle.innerRadius);
  trackStyle.outerRadius = me.getRadiusFn(trackStyle.outerRadius);
  if (Ext.isArray(trackStyle.fill)) {
    trackGradient = me.getTrackGradient();
    me.setGradientStops(trackGradient, trackStyle.fill);
    trackStyle.fill = 'url(#' + trackGradient.getAttribute('id') + ')';
  }
  return trackStyle;
}, updateTrackStyle:function(trackStyle) {
  var me = this, trackArc = Ext.fly(me.getTrackArc()), name;
  for (name in trackStyle) {
    if (name in me.pathAttributes) {
      trackArc.setStyle(me.camelToHyphen(name), trackStyle[name]);
    }
  }
}, applyValueStyle:function(valueStyle) {
  var me = this, valueGradient;
  valueStyle.innerRadius = me.getRadiusFn(valueStyle.innerRadius);
  valueStyle.outerRadius = me.getRadiusFn(valueStyle.outerRadius);
  if (Ext.isArray(valueStyle.fill)) {
    valueGradient = me.getValueGradient();
    me.setGradientStops(valueGradient, valueStyle.fill);
    valueStyle.fill = 'url(#' + valueGradient.getAttribute('id') + ')';
  }
  return valueStyle;
}, updateValueStyle:function(valueStyle) {
  var me = this, valueArc = Ext.fly(me.getValueArc()), name;
  for (name in valueStyle) {
    if (name in me.pathAttributes) {
      valueArc.setStyle(me.camelToHyphen(name), valueStyle[name]);
    }
  }
}, getRadiusFn:function(radius) {
  var result, pos, ratio, increment = 0;
  if (Ext.isNumber(radius)) {
    result = function() {
      return radius;
    };
  } else {
    if (Ext.isString(radius)) {
      radius = radius.replace(/ /g, '');
      ratio = parseFloat(radius) / 100;
      pos = radius.search('%');
      if (pos < radius.length - 1) {
        increment = parseFloat(radius.substr(pos + 1));
      }
      result = function(radius) {
        return radius * ratio + increment;
      };
      result.ratio = ratio;
    }
  }
  return result;
}, getSvg:function() {
  var me = this, svg = me.svg;
  if (!svg) {
    svg = me.svg = Ext.get(document.createElementNS(me.svgNS, 'svg'));
    me.bodyElement.append(svg);
  }
  return svg;
}, getTrackArc:function() {
  var me = this, trackArc = me.trackArc;
  if (!trackArc) {
    trackArc = me.trackArc = document.createElementNS(me.svgNS, 'path');
    me.getSvg().append(trackArc, true);
    trackArc.setAttribute('class', Ext.baseCSSPrefix + 'gauge-track');
  }
  return trackArc;
}, getValueArc:function() {
  var me = this, valueArc = me.valueArc;
  me.getTrackArc();
  if (!valueArc) {
    valueArc = me.valueArc = document.createElementNS(me.svgNS, 'path');
    me.getSvg().append(valueArc, true);
    valueArc.setAttribute('class', Ext.baseCSSPrefix + 'gauge-value');
  }
  return valueArc;
}, getDefs:function() {
  var me = this, defs = me.defs;
  if (!defs) {
    defs = me.defs = document.createElementNS(me.svgNS, 'defs');
    me.getSvg().append(defs);
  }
  return defs;
}, setGradientSize:function(gradient, x1, y1, x2, y2) {
  gradient.setAttribute('x1', x1);
  gradient.setAttribute('y1', y1);
  gradient.setAttribute('x2', x2);
  gradient.setAttribute('y2', y2);
}, resizeGradients:function(size) {
  var me = this, trackGradient = me.getTrackGradient(), valueGradient = me.getValueGradient(), x1 = 0, y1 = size.height / 2, x2 = size.width, y2 = size.height / 2;
  me.setGradientSize(trackGradient, x1, y1, x2, y2);
  me.setGradientSize(valueGradient, x1, y1, x2, y2);
}, setGradientStops:function(gradient, stops) {
  var ln = stops.length, i, stopCfg, stopEl;
  while (gradient.firstChild) {
    gradient.removeChild(gradient.firstChild);
  }
  for (i = 0; i < ln; i++) {
    stopCfg = stops[i];
    stopEl = document.createElementNS(this.svgNS, 'stop');
    gradient.appendChild(stopEl);
    stopEl.setAttribute('offset', stopCfg.offset);
    stopEl.setAttribute('stop-color', stopCfg.color);
    'opacity' in stopCfg && stopEl.setAttribute('stop-opacity', stopCfg.opacity);
  }
}, getTrackGradient:function() {
  var me = this, trackGradient = me.trackGradient;
  if (!trackGradient) {
    trackGradient = me.trackGradient = document.createElementNS(me.svgNS, 'linearGradient');
    trackGradient.setAttribute('gradientUnits', 'userSpaceOnUse');
    me.getDefs().appendChild(trackGradient);
    Ext.get(trackGradient);
  }
  return trackGradient;
}, getValueGradient:function() {
  var me = this, valueGradient = me.valueGradient;
  if (!valueGradient) {
    valueGradient = me.valueGradient = document.createElementNS(me.svgNS, 'linearGradient');
    valueGradient.setAttribute('gradientUnits', 'userSpaceOnUse');
    me.getDefs().appendChild(valueGradient);
    Ext.get(valueGradient);
  }
  return valueGradient;
}, getArcPoint:function(centerX, centerY, radius, degrees) {
  var radians = degrees / 180 * Math.PI;
  return [centerX + radius * Math.cos(radians), centerY + radius * Math.sin(radians)];
}, isCircle:function(startAngle, endAngle) {
  return Ext.Number.isEqual(Math.abs(endAngle - startAngle), 360, 0.001);
}, getArcPath:function(centerX, centerY, innerRadius, outerRadius, startAngle, endAngle, round) {
  var me = this, isCircle = me.isCircle(startAngle, endAngle), endAngle = endAngle - 0.01, innerStartPoint = me.getArcPoint(centerX, centerY, innerRadius, startAngle), innerEndPoint = me.getArcPoint(centerX, centerY, innerRadius, endAngle), outerStartPoint = me.getArcPoint(centerX, centerY, outerRadius, startAngle), outerEndPoint = me.getArcPoint(centerX, centerY, outerRadius, endAngle), large = endAngle - startAngle <= 180 ? 0 : 1, path = ['M', innerStartPoint[0], innerStartPoint[1], 'A', innerRadius, 
  innerRadius, 0, large, 1, innerEndPoint[0], innerEndPoint[1]], capRadius = (outerRadius - innerRadius) / 2;
  if (isCircle) {
    path.push('M', outerEndPoint[0], outerEndPoint[1]);
  } else {
    if (round) {
      path.push('A', capRadius, capRadius, 0, 0, 0, outerEndPoint[0], outerEndPoint[1]);
    } else {
      path.push('L', outerEndPoint[0], outerEndPoint[1]);
    }
  }
  path.push('A', outerRadius, outerRadius, 0, large, 0, outerStartPoint[0], outerStartPoint[1]);
  if (round && !isCircle) {
    path.push('A', capRadius, capRadius, 0, 0, 0, innerStartPoint[0], innerStartPoint[1]);
  }
  path.push('Z');
  return path.join(' ');
}, resizeHandler:function(size) {
  var me = this, svg = me.getSvg();
  svg.setSize(size);
  me.resizeGradients(size);
  me.render();
}, createInterpolator:function(rangeCheck) {
  var domainStart = 0, domainDelta = 1, rangeStart = 0, rangeEnd = 1;
  var interpolator = function(x, invert) {
    var t = 0;
    if (domainDelta) {
      t = (x - domainStart) / domainDelta;
      if (rangeCheck) {
        t = Math.max(0, t);
        t = Math.min(1, t);
      }
      if (invert) {
        t = 1 - t;
      }
    }
    return (1 - t) * rangeStart + t * rangeEnd;
  };
  interpolator.setDomain = function(a, b) {
    domainStart = a;
    domainDelta = b - a;
    return this;
  };
  interpolator.setRange = function(a, b) {
    rangeStart = a;
    rangeEnd = b;
    return this;
  };
  interpolator.getDomain = function() {
    return [domainStart, domainStart + domainDelta];
  };
  interpolator.getRange = function() {
    return [rangeStart, rangeEnd];
  };
  return interpolator;
}, applyAnimation:function(animation) {
  if (true === animation) {
    animation = {};
  } else {
    if (false === animation) {
      animation = {duration:0};
    }
  }
  if (!('duration' in animation)) {
    animation.duration = 1000;
  }
  if (!(animation.easing in this.easings)) {
    animation.easing = 'out';
  }
  return animation;
}, updateAnimation:function() {
  this.stopAnimation();
}, animate:function(from, to, duration, easing, fn, scope) {
  var me = this, start = Ext.now(), interpolator = me.createInterpolator().setRange(from, to);
  function frame() {
    var now = Ext.AnimationQueue.frameStartTime, t = Math.min(now - start, duration) / duration, value = interpolator(easing(t));
    if (scope) {
      if (typeof fn === 'string') {
        scope[fn].call(scope, value);
      } else {
        fn.call(scope, value);
      }
    } else {
      fn(value);
    }
    if (t >= 1) {
      Ext.AnimationQueue.stop(frame, scope);
      me.fx = null;
    }
  }
  me.stopAnimation();
  Ext.AnimationQueue.start(frame, scope);
  me.fx = {frame:frame, scope:scope};
}, stopAnimation:function() {
  var me = this;
  if (me.fx) {
    Ext.AnimationQueue.stop(me.fx.frame, me.fx.scope);
    me.fx = null;
  }
}, unitCircleExtrema:{0:[1, 0], 90:[0, 1], 180:[-1, 0], 270:[0, -1], 360:[1, 0], 450:[0, 1], 540:[-1, 0], 630:[0, -1]}, getUnitSectorExtrema:function(startAngle, lengthAngle) {
  var extrema = this.unitCircleExtrema, points = [], angle;
  for (angle in extrema) {
    if (angle > startAngle && angle < startAngle + lengthAngle) {
      points.push(extrema[angle]);
    }
  }
  return points;
}, fitSectorInRect:function(width, height, startAngle, lengthAngle, ratio) {
  if (Ext.Number.isEqual(lengthAngle, 360, 0.001)) {
    return {cx:width / 2, cy:height / 2, radius:Math.min(width, height) / 2, region:new Ext.util.Region(0, width, height, 0)};
  }
  var me = this, points, xx, yy, minX, maxX, minY, maxY, cache = me.fitSectorInRectCache, sameAngles = cache.startAngle === startAngle && cache.lengthAngle === lengthAngle;
  if (sameAngles) {
    minX = cache.minX;
    maxX = cache.maxX;
    minY = cache.minY;
    maxY = cache.maxY;
  } else {
    points = me.getUnitSectorExtrema(startAngle, lengthAngle).concat([me.getArcPoint(0, 0, 1, startAngle), me.getArcPoint(0, 0, ratio, startAngle), me.getArcPoint(0, 0, 1, startAngle + lengthAngle), me.getArcPoint(0, 0, ratio, startAngle + lengthAngle)]);
    xx = points.map(function(point) {
      return point[0];
    });
    yy = points.map(function(point) {
      return point[1];
    });
    minX = Math.min.apply(null, xx);
    maxX = Math.max.apply(null, xx);
    minY = Math.min.apply(null, yy);
    maxY = Math.max.apply(null, yy);
    cache.startAngle = startAngle;
    cache.lengthAngle = lengthAngle;
    cache.minX = minX;
    cache.maxX = maxX;
    cache.minY = minY;
    cache.maxY = maxY;
  }
  var sectorWidth = maxX - minX, sectorHeight = maxY - minY, scaleX = width / sectorWidth, scaleY = height / sectorHeight, scale = Math.min(scaleX, scaleY), sectorRegion = new Ext.util.Region(minY * scale, maxX * scale, maxY * scale, minX * scale), rectRegion = new Ext.util.Region(0, width, height, 0), alignedRegion = sectorRegion.alignTo({align:'c-c', target:rectRegion}), dx = alignedRegion.left - minX * scale, dy = alignedRegion.top - minY * scale;
  return {cx:dx, cy:dy, radius:scale, region:alignedRegion};
}, fitSectorInPaddedRect:function(width, height, padding, startAngle, lengthAngle, ratio) {
  var result = this.fitSectorInRect(width - padding * 2, height - padding * 2, startAngle, lengthAngle, ratio);
  result.cx += padding;
  result.cy += padding;
  result.region.translateBy(padding, padding);
  return result;
}, normalizeAngle:function(angle) {
  return (angle % 360 + 360) % 360;
}, render:function() {
  if (!this.size) {
    return;
  }
  var me = this, trackArc = me.getTrackArc(), valueArc = me.getValueArc(), clockwise = me.getClockwise(), value = me.fxValue, angleOffset = me.fxAngleOffset, trackLength = me.getTrackLength(), width = me.size.width, height = me.size.height, paddingFn = me.getPadding(), padding = paddingFn(Math.min(width, height)), trackStart = me.normalizeAngle(me.getTrackStart() + angleOffset), trackEnd = trackStart + trackLength, valueLength = me.interpolator(value), trackStyle = me.getTrackStyle(), valueStyle = 
  me.getValueStyle(), sector = me.fitSectorInPaddedRect(width, height, padding, trackStart, trackLength, trackStyle.innerRadius.ratio), cx = sector.cx, cy = sector.cy, radius = sector.radius, trackInnerRadius = Math.max(0, trackStyle.innerRadius(radius)), trackOuterRadius = Math.max(0, trackStyle.outerRadius(radius)), valueInnerRadius = Math.max(0, valueStyle.innerRadius(radius)), valueOuterRadius = Math.max(0, valueStyle.outerRadius(radius)), trackPath = me.getArcPath(cx, cy, trackInnerRadius, trackOuterRadius, 
  trackStart, trackEnd, trackStyle.round), valuePath = me.getArcPath(cx, cy, valueInnerRadius, valueOuterRadius, clockwise ? trackStart : trackEnd - valueLength, clockwise ? trackStart + valueLength : trackEnd, valueStyle.round);
  me.centerText(cx, cy, sector.region, trackInnerRadius, trackOuterRadius);
  trackArc.setAttribute('d', trackPath);
  valueArc.setAttribute('d', valuePath);
}});
Ext.define('Ext.ux.ajax.Simlet', function() {
  var urlRegex = /([^?#]*)(#.*)?$/, dateRegex = /^(\d{4})-(\d{2})-(\d{2})T(\d{2}):(\d{2}):(\d{2}(?:\.\d*)?)Z$/, intRegex = /^[+-]?\d+$/, floatRegex = /^[+-]?\d+\.\d+$/;
  function parseParamValue(value) {
    var m;
    if (Ext.isDefined(value)) {
      value = decodeURIComponent(value);
      if (intRegex.test(value)) {
        value = parseInt(value, 10);
      } else {
        if (floatRegex.test(value)) {
          value = parseFloat(value);
        } else {
          if (!!(m = dateRegex.exec(value))) {
            value = new Date(Date.UTC(+m[1], +m[2] - 1, +m[3], +m[4], +m[5], +m[6]));
          }
        }
      }
    }
    return value;
  }
  return {alias:'simlet.basic', isSimlet:true, responseProps:['responseText', 'responseXML', 'status', 'statusText', 'responseHeaders'], status:200, statusText:'OK', constructor:function(config) {
    Ext.apply(this, config);
  }, doGet:function(ctx) {
    return this.handleRequest(ctx);
  }, doPost:function(ctx) {
    return this.handleRequest(ctx);
  }, doRedirect:function(ctx) {
    return false;
  }, doDelete:function(ctx) {
    var me = this, xhr = ctx.xhr, records = xhr.options.records;
    me.removeFromData(ctx, records);
  }, exec:function(xhr) {
    var me = this, ret = {}, method = 'do' + Ext.String.capitalize(xhr.method.toLowerCase()), fn = me[method];
    if (fn) {
      ret = fn.call(me, me.getCtx(xhr.method, xhr.url, xhr));
    } else {
      ret = {status:405, statusText:'Method Not Allowed'};
    }
    return ret;
  }, getCtx:function(method, url, xhr) {
    return {method:method, params:this.parseQueryString(url), url:url, xhr:xhr};
  }, handleRequest:function(ctx) {
    var me = this, ret = {}, val;
    Ext.Array.forEach(me.responseProps, function(prop) {
      if (prop in me) {
        val = me[prop];
        if (Ext.isFunction(val)) {
          val = val.call(me, ctx);
        }
        ret[prop] = val;
      }
    });
    return ret;
  }, openRequest:function(method, url, options, async) {
    var ctx = this.getCtx(method, url), redirect = this.doRedirect(ctx), xhr;
    if (options.action === 'destroy') {
      method = 'delete';
    }
    if (redirect) {
      xhr = redirect;
    } else {
      xhr = new Ext.ux.ajax.SimXhr({mgr:this.manager, simlet:this, options:options});
      xhr.open(method, url, async);
    }
    return xhr;
  }, parseQueryString:function(str) {
    var m = urlRegex.exec(str), ret = {}, key, value, i, n;
    if (m && m[1]) {
      var pair, parts = m[1].split('\x26');
      for (i = 0, n = parts.length; i < n; ++i) {
        if ((pair = parts[i].split('\x3d'))[0]) {
          key = decodeURIComponent(pair.shift());
          value = parseParamValue(pair.length > 1 ? pair.join('\x3d') : pair[0]);
          if (!(key in ret)) {
            ret[key] = value;
          } else {
            if (Ext.isArray(ret[key])) {
              ret[key].push(value);
            } else {
              ret[key] = [ret[key], value];
            }
          }
        }
      }
    }
    return ret;
  }, redirect:function(method, url, params) {
    switch(arguments.length) {
      case 2:
        if (typeof url == 'string') {
          break;
        }
        params = url;
      case 1:
        url = method;
        method = 'GET';
        break;
    }
    if (params) {
      url = Ext.urlAppend(url, Ext.Object.toQueryString(params));
    }
    return this.manager.openRequest(method, url);
  }, removeFromData:function(ctx, records) {
    var me = this, data = me.getData(ctx), model = ctx.xhr.options.proxy && ctx.xhr.options.proxy.getModel() || {}, idProperty = model.idProperty || 'id';
    Ext.each(records, function(record) {
      var id = record.get(idProperty);
      for (var i = data.length; i-- > 0;) {
        if (data[i][idProperty] === id) {
          me.deleteRecord(i);
          break;
        }
      }
    });
  }};
}());
Ext.define('Ext.ux.ajax.DataSimlet', function() {
  function makeSortFn(def, cmp) {
    var order = def.direction, sign = order && order.toUpperCase() === 'DESC' ? -1 : 1;
    return function(leftRec, rightRec) {
      var lhs = leftRec[def.property], rhs = rightRec[def.property], c = lhs < rhs ? -1 : rhs < lhs ? 1 : 0;
      if (c || !cmp) {
        return c * sign;
      }
      return cmp(leftRec, rightRec);
    };
  }
  function makeSortFns(defs, cmp) {
    for (var sortFn = cmp, i = defs && defs.length; i;) {
      sortFn = makeSortFn(defs[--i], sortFn);
    }
    return sortFn;
  }
  return {extend:'Ext.ux.ajax.Simlet', buildNodes:function(node, path) {
    var me = this, nodeData = {data:[]}, len = node.length, children, i, child, name;
    me.nodes[path] = nodeData;
    for (i = 0; i < len; ++i) {
      nodeData.data.push(child = node[i]);
      name = child.text || child.title;
      child.id = path ? path + '/' + name : name;
      children = child.children;
      if (!(child.leaf = !children)) {
        delete child.children;
        me.buildNodes(children, child.id);
      }
    }
  }, deleteRecord:function(pos) {
    if (this.data && typeof this.data !== 'function') {
      Ext.Array.removeAt(this.data, pos);
    }
  }, fixTree:function(ctx, tree) {
    var me = this, node = ctx.params.node, nodes;
    if (!(nodes = me.nodes)) {
      me.nodes = nodes = {};
      me.buildNodes(tree, '');
    }
    node = nodes[node];
    if (node) {
      if (me.node) {
        me.node.sortedData = me.sortedData;
        me.node.currentOrder = me.currentOrder;
      }
      me.node = node;
      me.data = node.data;
      me.sortedData = node.sortedData;
      me.currentOrder = node.currentOrder;
    } else {
      me.data = null;
    }
  }, getData:function(ctx) {
    var me = this, params = ctx.params, order = (params.filter || '') + (params.group || '') + '-' + (params.sort || '') + '-' + (params.dir || ''), tree = me.tree, dynamicData, data, fields, sortFn;
    if (tree) {
      me.fixTree(ctx, tree);
    }
    data = me.data;
    if (typeof data === 'function') {
      dynamicData = true;
      data = data.call(this, ctx);
    }
    if (!data || order === '--') {
      return data || [];
    }
    if (!dynamicData && order == me.currentOrder) {
      return me.sortedData;
    }
    ctx.filterSpec = params.filter && Ext.decode(params.filter);
    ctx.groupSpec = params.group && Ext.decode(params.group);
    fields = params.sort;
    if (params.dir) {
      fields = [{direction:params.dir, property:fields}];
    } else {
      fields = Ext.decode(params.sort);
    }
    if (ctx.filterSpec) {
      var filters = new Ext.util.FilterCollection;
      filters.add(this.processFilters(ctx.filterSpec));
      data = Ext.Array.filter(data, filters.getFilterFn());
    }
    sortFn = makeSortFns(ctx.sortSpec = fields);
    if (ctx.groupSpec) {
      sortFn = makeSortFns([ctx.groupSpec], sortFn);
    }
    data = Ext.isArray(data) ? data.slice(0) : data;
    if (sortFn) {
      Ext.Array.sort(data, sortFn);
    }
    me.sortedData = data;
    me.currentOrder = order;
    return data;
  }, processFilters:Ext.identityFn, getPage:function(ctx, data) {
    var ret = data, length = data.length, start = ctx.params.start || 0, end = ctx.params.limit ? Math.min(length, start + ctx.params.limit) : length;
    if (start || end < length) {
      ret = ret.slice(start, end);
    }
    return ret;
  }, getGroupSummary:function(groupField, rows, ctx) {
    return rows[0];
  }, getSummary:function(ctx, data, page) {
    var me = this, groupField = ctx.groupSpec.property, accum, todo = {}, summary = [], fieldValue, lastFieldValue;
    Ext.each(page, function(rec) {
      fieldValue = rec[groupField];
      todo[fieldValue] = true;
    });
    function flush() {
      if (accum) {
        summary.push(me.getGroupSummary(groupField, accum, ctx));
        accum = null;
      }
    }
    Ext.each(data, function(rec) {
      fieldValue = rec[groupField];
      if (lastFieldValue !== fieldValue) {
        flush();
        lastFieldValue = fieldValue;
      }
      if (!todo[fieldValue]) {
        return !summary.length;
      }
      if (accum) {
        accum.push(rec);
      } else {
        accum = [rec];
      }
      return true;
    });
    flush();
    return summary;
  }};
}());
Ext.define('Ext.ux.ajax.JsonSimlet', {extend:'Ext.ux.ajax.DataSimlet', alias:'simlet.json', doGet:function(ctx) {
  var me = this, data = me.getData(ctx), page = me.getPage(ctx, data), reader = ctx.xhr.options.proxy && ctx.xhr.options.proxy.getReader(), root = reader && reader.getRootProperty(), ret = me.callParent(arguments), response = {};
  if (root && Ext.isArray(page)) {
    response[root] = page;
    response[reader.getTotalProperty()] = data.length;
  } else {
    response = page;
  }
  if (ctx.groupSpec) {
    response.summaryData = me.getSummary(ctx, data, page);
  }
  ret.responseText = Ext.encode(response);
  return ret;
}, doPost:function(ctx) {
  return this.doGet(ctx);
}});
Ext.define('Ext.ux.ajax.PivotSimlet', {extend:'Ext.ux.ajax.JsonSimlet', alias:'simlet.pivot', lastPost:null, lastResponse:null, keysSeparator:'', grandTotalKey:'', doPost:function(ctx) {
  var me = this, ret = me.callParent(arguments);
  me.lastResponse = me.processData(me.getData(ctx), Ext.decode(ctx.xhr.body));
  ret.responseText = Ext.encode(me.lastResponse);
  return ret;
}, processData:function(data, params) {
  var me = this, len = data.length, response = {success:true, leftAxis:[], topAxis:[], results:[]}, leftAxis = new Ext.util.MixedCollection, topAxis = new Ext.util.MixedCollection, results = new Ext.util.MixedCollection, i, j, k, leftKeys, topKeys, item, agg;
  me.lastPost = params;
  me.keysSeparator = params.keysSeparator;
  me.grandTotalKey = params.grandTotalKey;
  for (i = 0; i < len; i++) {
    leftKeys = me.extractValues(data[i], params.leftAxis, leftAxis);
    topKeys = me.extractValues(data[i], params.topAxis, topAxis);
    me.addResult(data[i], me.grandTotalKey, me.grandTotalKey, results);
    for (j = 0; j < leftKeys.length; j++) {
      me.addResult(data[i], leftKeys[j], me.grandTotalKey, results);
      for (k = 0; k < topKeys.length; k++) {
        me.addResult(data[i], leftKeys[j], topKeys[k], results);
      }
    }
    for (j = 0; j < topKeys.length; j++) {
      me.addResult(data[i], me.grandTotalKey, topKeys[j], results);
    }
  }
  response.leftAxis = leftAxis.getRange();
  response.topAxis = topAxis.getRange();
  len = results.getCount();
  for (i = 0; i < len; i++) {
    item = results.getAt(i);
    item.values = {};
    for (j = 0; j < params.aggregate.length; j++) {
      agg = params.aggregate[j];
      item.values[agg.id] = me[agg.aggregator](item.records, agg.dataIndex, item.leftKey, item.topKey);
    }
    delete item.records;
    response.results.push(item);
  }
  leftAxis.clear();
  topAxis.clear();
  results.clear();
  return response;
}, getKey:function(value) {
  var me = this;
  me.keysMap = me.keysMap || {};
  if (!Ext.isDefined(me.keysMap[value])) {
    me.keysMap[value] = Ext.id();
  }
  return me.keysMap[value];
}, extractValues:function(record, dimensions, col) {
  var len = dimensions.length, keys = [], i, j, key, item, dim;
  key = '';
  for (j = 0; j < len; j++) {
    dim = dimensions[j];
    key += (j > 0 ? this.keysSeparator : '') + this.getKey(record[dim.dataIndex]);
    item = col.getByKey(key);
    if (!item) {
      item = col.add(key, {key:key, value:record[dim.dataIndex], dimensionId:dim.id});
    }
    keys.push(key);
  }
  return keys;
}, addResult:function(record, leftKey, topKey, results) {
  var item = results.getByKey(leftKey + '/' + topKey);
  if (!item) {
    item = results.add(leftKey + '/' + topKey, {leftKey:leftKey, topKey:topKey, records:[]});
  }
  item.records.push(record);
}, sum:function(records, measure, rowGroupKey, colGroupKey) {
  var length = records.length, total = 0, i;
  for (i = 0; i < length; i++) {
    total += Ext.Number.from(records[i][measure], 0);
  }
  return total;
}, avg:function(records, measure, rowGroupKey, colGroupKey) {
  var length = records.length, total = 0, i;
  for (i = 0; i < length; i++) {
    total += Ext.Number.from(records[i][measure], 0);
  }
  return length > 0 ? total / length : 0;
}, min:function(records, measure, rowGroupKey, colGroupKey) {
  var data = [], length = records.length, i, v;
  for (i = 0; i < length; i++) {
    data.push(records[i][measure]);
  }
  v = Ext.Array.min(data);
  return v;
}, max:function(records, measure, rowGroupKey, colGroupKey) {
  var data = [], length = records.length, i;
  for (i = 0; i < length; i++) {
    data.push(records[i][measure]);
  }
  v = Ext.Array.max(data);
  return v;
}, count:function(records, measure, rowGroupKey, colGroupKey) {
  return records.length;
}, variance:function(records, measure, rowGroupKey, colGroupKey) {
  var me = Ext.pivot.Aggregators, length = records.length, avg = me.avg.apply(me, arguments), total = 0, i;
  if (avg > 0) {
    for (i = 0; i < length; i++) {
      total += Math.pow(Ext.Number.from(records[i][measure], 0) - avg, 2);
    }
  }
  return total > 0 && length > 1 ? total / (length - 1) : 0;
}, varianceP:function(records, measure, rowGroupKey, colGroupKey) {
  var me = Ext.pivot.Aggregators, length = records.length, avg = me.avg.apply(me, arguments), total = 0, i;
  if (avg > 0) {
    for (i = 0; i < length; i++) {
      total += Math.pow(Ext.Number.from(records[i][measure], 0) - avg, 2);
    }
  }
  return total > 0 && length > 0 ? total / length : 0;
}, stdDev:function(records, measure, rowGroupKey, colGroupKey) {
  var me = Ext.pivot.Aggregators, v = me.variance.apply(me, arguments);
  return v > 0 ? Math.sqrt(v) : 0;
}, stdDevP:function(records, measure, rowGroupKey, colGroupKey) {
  var me = Ext.pivot.Aggregators, v = me.varianceP.apply(me, arguments);
  return v > 0 ? Math.sqrt(v) : 0;
}});
Ext.define('Ext.ux.ajax.SimXhr', {readyState:0, mgr:null, simlet:null, constructor:function(config) {
  var me = this;
  Ext.apply(me, config);
  me.requestHeaders = {};
}, abort:function() {
  var me = this;
  if (me.timer) {
    Ext.undefer(me.timer);
    me.timer = null;
  }
  me.aborted = true;
}, getAllResponseHeaders:function() {
  var headers = [];
  if (Ext.isObject(this.responseHeaders)) {
    Ext.Object.each(this.responseHeaders, function(name, value) {
      headers.push(name + ': ' + value);
    });
  }
  return headers.join('\r\n');
}, getResponseHeader:function(header) {
  var headers = this.responseHeaders;
  return headers && headers[header] || null;
}, open:function(method, url, async, user, password) {
  var me = this;
  me.method = method;
  me.url = url;
  me.async = async !== false;
  me.user = user;
  me.password = password;
  me.setReadyState(1);
}, overrideMimeType:function(mimeType) {
  this.mimeType = mimeType;
}, schedule:function() {
  var me = this, delay = me.simlet.delay || me.mgr.delay;
  if (delay) {
    me.timer = Ext.defer(function() {
      me.onTick();
    }, delay);
  } else {
    me.onTick();
  }
}, send:function(body) {
  var me = this;
  me.body = body;
  if (me.async) {
    me.schedule();
  } else {
    me.onComplete();
  }
}, setReadyState:function(state) {
  var me = this;
  if (me.readyState != state) {
    me.readyState = state;
    me.onreadystatechange();
  }
}, setRequestHeader:function(header, value) {
  this.requestHeaders[header] = value;
}, onreadystatechange:Ext.emptyFn, onComplete:function() {
  var me = this, callback;
  me.readyState = 4;
  Ext.apply(me, me.simlet.exec(me));
  callback = me.jsonpCallback;
  if (callback) {
    var text = callback + '(' + me.responseText + ')';
    eval(text);
  }
}, onTick:function() {
  var me = this;
  me.timer = null;
  me.onComplete();
  me.onreadystatechange && me.onreadystatechange();
}});
Ext.define('Ext.ux.ajax.SimManager', {singleton:true, requires:['Ext.data.Connection', 'Ext.ux.ajax.SimXhr', 'Ext.ux.ajax.Simlet', 'Ext.ux.ajax.JsonSimlet'], defaultType:'basic', delay:150, ready:false, constructor:function() {
  this.simlets = [];
}, getSimlet:function(url) {
  var me = this, index = url.indexOf('?'), simlets = me.simlets, len = simlets.length, i, simlet, simUrl, match;
  if (index < 0) {
    index = url.indexOf('#');
  }
  if (index > 0) {
    url = url.substring(0, index);
  }
  for (i = 0; i < len; ++i) {
    simlet = simlets[i];
    simUrl = simlet.url;
    if (simUrl instanceof RegExp) {
      match = simUrl.test(url);
    } else {
      match = simUrl === url;
    }
    if (match) {
      return simlet;
    }
  }
  return me.defaultSimlet;
}, getXhr:function(method, url, options, async) {
  var simlet = this.getSimlet(url);
  if (simlet) {
    return simlet.openRequest(method, url, options, async);
  }
  return null;
}, init:function(config) {
  var me = this;
  Ext.apply(me, config);
  if (!me.ready) {
    me.ready = true;
    if (!('defaultSimlet' in me)) {
      me.defaultSimlet = new Ext.ux.ajax.Simlet({status:404, statusText:'Not Found'});
    }
    me._openRequest = Ext.data.Connection.prototype.openRequest;
    Ext.data.request.Ajax.override({openRequest:function(options, requestOptions, async) {
      var xhr = !options.nosim && me.getXhr(requestOptions.method, requestOptions.url, options, async);
      if (!xhr) {
        xhr = this.callParent(arguments);
      }
      return xhr;
    }});
    if (Ext.data.JsonP) {
      Ext.data.JsonP.self.override({createScript:function(url, params, options) {
        var fullUrl = Ext.urlAppend(url, Ext.Object.toQueryString(params)), script = !options.nosim && me.getXhr('GET', fullUrl, options, true);
        if (!script) {
          script = this.callParent(arguments);
        }
        return script;
      }, loadScript:function(request) {
        var script = request.script;
        if (script.simlet) {
          script.jsonpCallback = request.params[request.callbackKey];
          script.send(null);
          request.script = document.createElement('script');
        } else {
          this.callParent(arguments);
        }
      }});
    }
  }
  return me;
}, openRequest:function(method, url, async) {
  var opt = {method:method, url:url};
  return this._openRequest.call(Ext.data.Connection.prototype, {}, opt, async);
}, register:function(simlet) {
  var me = this;
  me.init();
  function reg(one) {
    var simlet = one;
    if (!simlet.isSimlet) {
      simlet = Ext.create('simlet.' + (simlet.type || simlet.stype || me.defaultType), one);
    }
    me.simlets.push(simlet);
    simlet.manager = me;
  }
  if (Ext.isArray(simlet)) {
    Ext.each(simlet, reg);
  } else {
    if (simlet.isSimlet || simlet.url) {
      reg(simlet);
    } else {
      Ext.Object.each(simlet, function(url, s) {
        s.url = url;
        reg(s);
      });
    }
  }
  return me;
}});
Ext.define('Ext.ux.ajax.XmlSimlet', {extend:'Ext.ux.ajax.DataSimlet', alias:'simlet.xml', xmlTpl:['\x3c{root}\x3e\n', '\x3ctpl for\x3d"data"\x3e', '    \x3c{parent.record}\x3e\n', '\x3ctpl for\x3d"parent.fields"\x3e', '        \x3c{name}\x3e{[parent[values.name]]}\x3c/{name}\x3e\n', '\x3c/tpl\x3e', '    \x3c/{parent.record}\x3e\n', '\x3c/tpl\x3e', '\x3c/{root}\x3e'], doGet:function(ctx) {
  var me = this, data = me.getData(ctx), page = me.getPage(ctx, data), proxy = ctx.xhr.options.operation.getProxy(), reader = proxy && proxy.getReader(), model = reader && reader.getModel(), ret = me.callParent(arguments), response = {data:page, reader:reader, fields:model && model.fields, root:reader && reader.getRootProperty(), record:reader && reader.record}, tpl, xml, doc;
  if (ctx.groupSpec) {
    response.summaryData = me.getSummary(ctx, data, page);
  }
  if (me.xmlTpl) {
    tpl = Ext.XTemplate.getTpl(me, 'xmlTpl');
    xml = tpl.apply(response);
  } else {
    xml = data;
  }
  if (typeof DOMParser != 'undefined') {
    doc = (new DOMParser).parseFromString(xml, 'text/xml');
  } else {
    doc = new ActiveXObject('Microsoft.XMLDOM');
    doc.async = false;
    doc.loadXML(xml);
  }
  ret.responseText = xml;
  ret.responseXML = doc;
  return ret;
}, fixTree:function() {
  this.callParent(arguments);
  var buffer = [];
  this.buildTreeXml(this.data, buffer);
  this.data = buffer.join('');
}, buildTreeXml:function(nodes, buffer) {
  var rootProperty = this.rootProperty, recordProperty = this.recordProperty;
  buffer.push('\x3c', rootProperty, '\x3e');
  Ext.Array.forEach(nodes, function(node) {
    buffer.push('\x3c', recordProperty, '\x3e');
    for (var key in node) {
      if (key == 'children') {
        this.buildTreeXml(node.children, buffer);
      } else {
        buffer.push('\x3c', key, '\x3e', node[key], '\x3c/', key, '\x3e');
      }
    }
    buffer.push('\x3c/', recordProperty, '\x3e');
  });
  buffer.push('\x3c/', rootProperty, '\x3e');
}});
Ext.define('Ext.ux.event.Driver', {extend:'Ext.util.Observable', active:null, specialKeysByName:{PGUP:33, PGDN:34, END:35, HOME:36, LEFT:37, UP:38, RIGHT:39, DOWN:40}, specialKeysByCode:{}, getTextSelection:function(el) {
  var doc = el.ownerDocument, range, range2, start, end;
  if (typeof el.selectionStart === 'number') {
    start = el.selectionStart;
    end = el.selectionEnd;
  } else {
    if (doc.selection) {
      range = doc.selection.createRange();
      range2 = el.createTextRange();
      range2.setEndPoint('EndToStart', range);
      start = range2.text.length;
      end = start + range.text.length;
    }
  }
  return [start, end];
}, getTime:function() {
  return (new Date).getTime();
}, getTimestamp:function() {
  var d = this.getTime();
  return d - this.startTime;
}, onStart:function() {
}, onStop:function() {
}, start:function() {
  var me = this;
  if (!me.active) {
    me.active = new Date;
    me.startTime = me.getTime();
    me.onStart();
    me.fireEvent('start', me);
  }
}, stop:function() {
  var me = this;
  if (me.active) {
    me.active = null;
    me.onStop();
    me.fireEvent('stop', me);
  }
}}, function() {
  var proto = this.prototype;
  Ext.Object.each(proto.specialKeysByName, function(name, value) {
    proto.specialKeysByCode[value] = name;
  });
});
Ext.define('Ext.ux.event.Maker', {eventQueue:[], startAfter:500, timerIncrement:500, currentTiming:0, constructor:function(config) {
  var me = this;
  me.currentTiming = me.startAfter;
  if (!Ext.isArray(config)) {
    config = [config];
  }
  Ext.Array.each(config, function(item) {
    item.el = item.el || 'el';
    Ext.Array.each(Ext.ComponentQuery.query(item.cmpQuery), function(cmp) {
      var event = {}, x, y, el;
      if (!item.domQuery) {
        el = cmp[item.el];
      } else {
        el = cmp.el.down(item.domQuery);
      }
      event.target = '#' + el.dom.id;
      event.type = item.type;
      event.button = config.button || 0;
      x = el.getX() + el.getWidth() / 2;
      y = el.getY() + el.getHeight() / 2;
      event.xy = [x, y];
      event.ts = me.currentTiming;
      me.currentTiming += me.timerIncrement;
      me.eventQueue.push(event);
    });
    if (item.screenshot) {
      me.eventQueue[me.eventQueue.length - 1].screenshot = true;
    }
  });
  return me.eventQueue;
}});
Ext.define('Ext.ux.event.Player', function(Player) {
  var defaults = {}, mouseEvents = {}, keyEvents = {}, doc, uiEvents = {}, bubbleEvents = {resize:1, reset:1, submit:1, change:1, select:1, error:1, abort:1};
  Ext.each(['click', 'dblclick', 'mouseover', 'mouseout', 'mousedown', 'mouseup', 'mousemove'], function(type) {
    bubbleEvents[type] = defaults[type] = mouseEvents[type] = {bubbles:true, cancelable:type != 'mousemove', detail:1, screenX:0, screenY:0, clientX:0, clientY:0, ctrlKey:false, altKey:false, shiftKey:false, metaKey:false, button:0};
  });
  Ext.each(['keydown', 'keyup', 'keypress'], function(type) {
    bubbleEvents[type] = defaults[type] = keyEvents[type] = {bubbles:true, cancelable:true, ctrlKey:false, altKey:false, shiftKey:false, metaKey:false, keyCode:0, charCode:0};
  });
  Ext.each(['blur', 'change', 'focus', 'resize', 'scroll', 'select'], function(type) {
    defaults[type] = uiEvents[type] = {bubbles:type in bubbleEvents, cancelable:false, detail:1};
  });
  var inputSpecialKeys = {8:function(target, start, end) {
    if (start < end) {
      target.value = target.value.substring(0, start) + target.value.substring(end);
    } else {
      if (start > 0) {
        target.value = target.value.substring(0, --start) + target.value.substring(end);
      }
    }
    this.setTextSelection(target, start, start);
  }, 46:function(target, start, end) {
    if (start < end) {
      target.value = target.value.substring(0, start) + target.value.substring(end);
    } else {
      if (start < target.value.length - 1) {
        target.value = target.value.substring(0, start) + target.value.substring(start + 1);
      }
    }
    this.setTextSelection(target, start, start);
  }};
  return {extend:'Ext.ux.event.Driver', keyFrameEvents:{click:true}, pauseForAnimations:true, speed:1, stallTime:0, _inputSpecialKeys:{INPUT:inputSpecialKeys, TEXTAREA:Ext.apply({}, inputSpecialKeys)}, tagPathRegEx:/(\w+)(?:\[(\d+)\])?/, constructor:function(config) {
    var me = this;
    me.callParent(arguments);
    me.timerFn = function() {
      me.onTick();
    };
    me.attachTo = me.attachTo || window;
    doc = me.attachTo.document;
  }, getElementFromXPath:function(xpath) {
    var me = this, parts = xpath.split('/'), regex = me.tagPathRegEx, i, n, m, count, tag, child, el = me.attachTo.document;
    el = parts[0] == '~' ? el.body : el.getElementById(parts[0].substring(1));
    for (i = 1, n = parts.length; el && i < n; ++i) {
      m = regex.exec(parts[i]);
      count = m[2] ? parseInt(m[2], 10) : 1;
      tag = m[1].toUpperCase();
      for (child = el.firstChild; child; child = child.nextSibling) {
        if (child.tagName == tag) {
          if (count == 1) {
            break;
          }
          --count;
        }
      }
      el = child;
    }
    return el;
  }, offsetToRangeCharacterMove:function(el, offset) {
    return offset - (el.value.slice(0, offset).split('\r\n').length - 1);
  }, setTextSelection:function(el, startOffset, endOffset) {
    if (startOffset < 0) {
      startOffset += el.value.length;
    }
    if (endOffset == null) {
      endOffset = startOffset;
    }
    if (endOffset < 0) {
      endOffset += el.value.length;
    }
    if (typeof el.selectionStart === 'number') {
      el.selectionStart = startOffset;
      el.selectionEnd = endOffset;
    } else {
      var range = el.createTextRange();
      var startCharMove = this.offsetToRangeCharacterMove(el, startOffset);
      range.collapse(true);
      if (startOffset == endOffset) {
        range.move('character', startCharMove);
      } else {
        range.moveEnd('character', this.offsetToRangeCharacterMove(el, endOffset));
        range.moveStart('character', startCharMove);
      }
      range.select();
    }
  }, getTimeIndex:function() {
    var t = this.getTimestamp() - this.stallTime;
    return t * this.speed;
  }, makeToken:function(eventDescriptor, signal) {
    var me = this, t0;
    eventDescriptor[signal] = true;
    eventDescriptor.defer = function() {
      eventDescriptor[signal] = false;
      t0 = me.getTime();
    };
    eventDescriptor.finish = function() {
      eventDescriptor[signal] = true;
      me.stallTime += me.getTime() - t0;
      me.schedule();
    };
  }, nextEvent:function(eventDescriptor) {
    var me = this, index = ++me.queueIndex;
    if (me.keyFrameEvents[eventDescriptor.type]) {
      Ext.Array.insert(me.eventQueue, index, [{keyframe:true, ts:eventDescriptor.ts}]);
    }
  }, peekEvent:function() {
    return this.eventQueue[this.queueIndex] || null;
  }, replaceEvent:function(index, events) {
    for (var t, i = 0, n = events.length; i < n; ++i) {
      if (i) {
        t = events[i - 1];
        delete t.afterplay;
        delete t.screenshot;
        delete events[i].beforeplay;
      }
    }
    Ext.Array.replace(this.eventQueue, index == null ? this.queueIndex : index, 1, events);
  }, processEvents:function() {
    var me = this, animations = me.pauseForAnimations && me.attachTo.Ext.fx.Manager.items, eventDescriptor;
    while ((eventDescriptor = me.peekEvent()) !== null) {
      if (animations && animations.getCount()) {
        return true;
      }
      if (eventDescriptor.keyframe) {
        if (!me.processKeyFrame(eventDescriptor)) {
          return false;
        }
        me.nextEvent(eventDescriptor);
      } else {
        if (eventDescriptor.ts <= me.getTimeIndex() && me.fireEvent('beforeplay', me, eventDescriptor) !== false && me.playEvent(eventDescriptor)) {
          me.nextEvent(eventDescriptor);
        } else {
          return true;
        }
      }
    }
    me.stop();
    return false;
  }, processKeyFrame:function(eventDescriptor) {
    var me = this;
    if (!eventDescriptor.defer) {
      me.makeToken(eventDescriptor, 'done');
      me.fireEvent('keyframe', me, eventDescriptor);
    }
    return eventDescriptor.done;
  }, injectEvent:function(target, event) {
    var me = this, type = event.type, options = Ext.apply({}, event, defaults[type]), handler;
    if (type === 'type') {
      handler = me._inputSpecialKeys[target.tagName];
      if (handler) {
        return me.injectTypeInputEvent(target, event, handler);
      }
      return me.injectTypeEvent(target, event);
    }
    if (type === 'focus' && target.focus) {
      target.focus();
      return true;
    }
    if (type === 'blur' && target.blur) {
      target.blur();
      return true;
    }
    if (type === 'scroll') {
      target.scrollLeft = event.pos[0];
      target.scrollTop = event.pos[1];
      return true;
    }
    if (type === 'mduclick') {
      return me.injectEvent(target, Ext.applyIf({type:'mousedown'}, event)) && me.injectEvent(target, Ext.applyIf({type:'mouseup'}, event)) && me.injectEvent(target, Ext.applyIf({type:'click'}, event));
    }
    if (mouseEvents[type]) {
      return Player.injectMouseEvent(target, options, me.attachTo);
    }
    if (keyEvents[type]) {
      return Player.injectKeyEvent(target, options, me.attachTo);
    }
    if (uiEvents[type]) {
      return Player.injectUIEvent(target, type, options.bubbles, options.cancelable, options.view || me.attachTo, options.detail);
    }
    return false;
  }, injectTypeEvent:function(target, event) {
    var me = this, text = event.text, xlat = [], ch, chUp, i, n, sel, upper, isInput;
    if (text) {
      delete event.text;
      upper = text.toUpperCase();
      for (i = 0, n = text.length; i < n; ++i) {
        ch = text.charCodeAt(i);
        chUp = upper.charCodeAt(i);
        xlat.push(Ext.applyIf({type:'keydown', charCode:chUp, keyCode:chUp}, event), Ext.applyIf({type:'keypress', charCode:ch, keyCode:ch}, event), Ext.applyIf({type:'keyup', charCode:chUp, keyCode:chUp}, event));
      }
    } else {
      xlat.push(Ext.applyIf({type:'keydown', charCode:event.keyCode}, event), Ext.applyIf({type:'keyup', charCode:event.keyCode}, event));
    }
    for (i = 0, n = xlat.length; i < n; ++i) {
      me.injectEvent(target, xlat[i]);
    }
    return true;
  }, injectTypeInputEvent:function(target, event, handler) {
    var me = this, text = event.text, sel, n;
    if (handler) {
      sel = me.getTextSelection(target);
      if (text) {
        n = sel[0];
        target.value = target.value.substring(0, n) + text + target.value.substring(sel[1]);
        n += text.length;
        me.setTextSelection(target, n, n);
      } else {
        if (!(handler = handler[event.keyCode])) {
          if ('caret' in event) {
            me.setTextSelection(target, event.caret, event.caret);
          } else {
            if (event.selection) {
              me.setTextSelection(target, event.selection[0], event.selection[1]);
            }
          }
          return me.injectTypeEvent(target, event);
        }
        handler.call(this, target, sel[0], sel[1]);
        return true;
      }
    }
    return true;
  }, playEvent:function(eventDescriptor) {
    var me = this, target = me.getElementFromXPath(eventDescriptor.target), event;
    if (!target) {
      return false;
    }
    if (!me.playEventHook(eventDescriptor, 'beforeplay')) {
      return false;
    }
    if (!eventDescriptor.injected) {
      eventDescriptor.injected = true;
      event = me.translateEvent(eventDescriptor, target);
      me.injectEvent(target, event);
    }
    return me.playEventHook(eventDescriptor, 'afterplay');
  }, playEventHook:function(eventDescriptor, hookName) {
    var me = this, doneName = hookName + '.done', firedName = hookName + '.fired', hook = eventDescriptor[hookName];
    if (hook && !eventDescriptor[doneName]) {
      if (!eventDescriptor[firedName]) {
        eventDescriptor[firedName] = true;
        me.makeToken(eventDescriptor, doneName);
        if (me.eventScope && Ext.isString(hook)) {
          hook = me.eventScope[hook];
        }
        if (hook) {
          hook.call(me.eventScope || me, eventDescriptor);
        }
      }
      return false;
    }
    return true;
  }, schedule:function() {
    var me = this;
    if (!me.timer) {
      me.timer = Ext.defer(me.timerFn, 10);
    }
  }, _translateAcross:['type', 'button', 'charCode', 'keyCode', 'caret', 'pos', 'text', 'selection'], translateEvent:function(eventDescriptor, target) {
    var me = this, event = {}, modKeys = eventDescriptor.modKeys || '', names = me._translateAcross, i = names.length, name, xy;
    while (i--) {
      name = names[i];
      if (name in eventDescriptor) {
        event[name] = eventDescriptor[name];
      }
    }
    event.altKey = modKeys.indexOf('A') > 0;
    event.ctrlKey = modKeys.indexOf('C') > 0;
    event.metaKey = modKeys.indexOf('M') > 0;
    event.shiftKey = modKeys.indexOf('S') > 0;
    if (target && 'x' in eventDescriptor) {
      xy = Ext.fly(target).getXY();
      xy[0] += eventDescriptor.x;
      xy[1] += eventDescriptor.y;
    } else {
      if ('x' in eventDescriptor) {
        xy = [eventDescriptor.x, eventDescriptor.y];
      } else {
        if ('px' in eventDescriptor) {
          xy = [eventDescriptor.px, eventDescriptor.py];
        }
      }
    }
    if (xy) {
      event.clientX = event.screenX = xy[0];
      event.clientY = event.screenY = xy[1];
    }
    if (eventDescriptor.key) {
      event.keyCode = me.specialKeysByName[eventDescriptor.key];
    }
    if (eventDescriptor.type === 'wheel') {
      if ('onwheel' in me.attachTo.document) {
        event.wheelX = eventDescriptor.dx;
        event.wheelY = eventDescriptor.dy;
      } else {
        event.type = 'mousewheel';
        event.wheelDeltaX = -40 * eventDescriptor.dx;
        event.wheelDeltaY = event.wheelDelta = -40 * eventDescriptor.dy;
      }
    }
    return event;
  }, onStart:function() {
    var me = this;
    me.queueIndex = 0;
    me.schedule();
  }, onStop:function() {
    var me = this;
    if (me.timer) {
      Ext.undefer(me.timer);
      me.timer = null;
    }
  }, onTick:function() {
    var me = this;
    me.timer = null;
    if (me.processEvents()) {
      me.schedule();
    }
  }, statics:{ieButtonCodeMap:{0:1, 1:4, 2:2}, injectKeyEvent:function(target, options, view) {
    var type = options.type, customEvent = null;
    if (type === 'textevent') {
      type = 'keypress';
    }
    view = view || window;
    if (doc.createEvent) {
      try {
        customEvent = doc.createEvent('KeyEvents');
        customEvent.initKeyEvent(type, options.bubbles, options.cancelable, view, options.ctrlKey, options.altKey, options.shiftKey, options.metaKey, options.keyCode, options.charCode);
      } catch (ex) {
        try {
          customEvent = doc.createEvent('Events');
        } catch (uierror) {
          customEvent = doc.createEvent('UIEvents');
        } finally {
          customEvent.initEvent(type, options.bubbles, options.cancelable);
          customEvent.view = view;
          customEvent.altKey = options.altKey;
          customEvent.ctrlKey = options.ctrlKey;
          customEvent.shiftKey = options.shiftKey;
          customEvent.metaKey = options.metaKey;
          customEvent.keyCode = options.keyCode;
          customEvent.charCode = options.charCode;
        }
      }
      target.dispatchEvent(customEvent);
    } else {
      if (doc.createEventObject) {
        customEvent = doc.createEventObject();
        customEvent.bubbles = options.bubbles;
        customEvent.cancelable = options.cancelable;
        customEvent.view = view;
        customEvent.ctrlKey = options.ctrlKey;
        customEvent.altKey = options.altKey;
        customEvent.shiftKey = options.shiftKey;
        customEvent.metaKey = options.metaKey;
        customEvent.keyCode = options.charCode > 0 ? options.charCode : options.keyCode;
        target.fireEvent('on' + type, customEvent);
      } else {
        return false;
      }
    }
    return true;
  }, injectMouseEvent:function(target, options, view) {
    var type = options.type, customEvent = null;
    view = view || window;
    if (doc.createEvent) {
      customEvent = doc.createEvent('MouseEvents');
      if (customEvent.initMouseEvent) {
        customEvent.initMouseEvent(type, options.bubbles, options.cancelable, view, options.detail, options.screenX, options.screenY, options.clientX, options.clientY, options.ctrlKey, options.altKey, options.shiftKey, options.metaKey, options.button, options.relatedTarget);
      } else {
        customEvent = doc.createEvent('UIEvents');
        customEvent.initEvent(type, options.bubbles, options.cancelable);
        customEvent.view = view;
        customEvent.detail = options.detail;
        customEvent.screenX = options.screenX;
        customEvent.screenY = options.screenY;
        customEvent.clientX = options.clientX;
        customEvent.clientY = options.clientY;
        customEvent.ctrlKey = options.ctrlKey;
        customEvent.altKey = options.altKey;
        customEvent.metaKey = options.metaKey;
        customEvent.shiftKey = options.shiftKey;
        customEvent.button = options.button;
        customEvent.relatedTarget = options.relatedTarget;
      }
      if (options.relatedTarget && !customEvent.relatedTarget) {
        if (type == 'mouseout') {
          customEvent.toElement = options.relatedTarget;
        } else {
          if (type == 'mouseover') {
            customEvent.fromElement = options.relatedTarget;
          }
        }
      }
      target.dispatchEvent(customEvent);
    } else {
      if (doc.createEventObject) {
        customEvent = doc.createEventObject();
        customEvent.bubbles = options.bubbles;
        customEvent.cancelable = options.cancelable;
        customEvent.view = view;
        customEvent.detail = options.detail;
        customEvent.screenX = options.screenX;
        customEvent.screenY = options.screenY;
        customEvent.clientX = options.clientX;
        customEvent.clientY = options.clientY;
        customEvent.ctrlKey = options.ctrlKey;
        customEvent.altKey = options.altKey;
        customEvent.metaKey = options.metaKey;
        customEvent.shiftKey = options.shiftKey;
        customEvent.button = Player.ieButtonCodeMap[options.button] || 0;
        customEvent.relatedTarget = options.relatedTarget;
        target.fireEvent('on' + type, customEvent);
      } else {
        return false;
      }
    }
    return true;
  }, injectUIEvent:function(target, options, view) {
    var customEvent = null;
    view = view || window;
    if (doc.createEvent) {
      customEvent = doc.createEvent('UIEvents');
      customEvent.initUIEvent(options.type, options.bubbles, options.cancelable, view, options.detail);
      target.dispatchEvent(customEvent);
    } else {
      if (doc.createEventObject) {
        customEvent = doc.createEventObject();
        customEvent.bubbles = options.bubbles;
        customEvent.cancelable = options.cancelable;
        customEvent.view = view;
        customEvent.detail = options.detail;
        target.fireEvent('on' + options.type, customEvent);
      } else {
        return false;
      }
    }
    return true;
  }}};
});
Ext.define('Ext.ux.event.Recorder', function(Recorder) {
  function apply() {
    var a = arguments, n = a.length, obj = {kind:'other'}, i;
    for (i = 0; i < n; ++i) {
      Ext.apply(obj, arguments[i]);
    }
    if (obj.alt && !obj.event) {
      obj.event = obj.alt;
    }
    return obj;
  }
  function key(extra) {
    return apply({kind:'keyboard', modKeys:true, key:true}, extra);
  }
  function mouse(extra) {
    return apply({kind:'mouse', button:true, modKeys:true, xy:true}, extra);
  }
  var eventsToRecord = {keydown:key(), keypress:key(), keyup:key(), dragmove:mouse({alt:'mousemove', pageCoords:true, whileDrag:true}), mousemove:mouse({pageCoords:true}), mouseover:mouse(), mouseout:mouse(), click:mouse(), wheel:mouse({wheel:true}), mousedown:mouse({press:true}), mouseup:mouse({release:true}), scroll:apply({listen:false}), focus:apply(), blur:apply()};
  for (var key in eventsToRecord) {
    if (!eventsToRecord[key].event) {
      eventsToRecord[key].event = key;
    }
  }
  eventsToRecord.wheel.event = null;
  return {extend:'Ext.ux.event.Driver', eventsToRecord:eventsToRecord, ignoreIdRegEx:/ext-gen(?:\d+)/, inputRe:/^(input|textarea)$/i, constructor:function(config) {
    var me = this, events = config && config.eventsToRecord;
    if (events) {
      me.eventsToRecord = Ext.apply(Ext.apply({}, me.eventsToRecord), events);
      delete config.eventsToRecord;
    }
    me.callParent(arguments);
    me.clear();
    me.modKeys = [];
    me.attachTo = me.attachTo || window;
  }, clear:function() {
    this.eventsRecorded = [];
  }, listenToEvent:function(event) {
    var me = this, el = me.attachTo.document.body, fn = function() {
      return me.onEvent.apply(me, arguments);
    }, cleaner = {};
    if (el.attachEvent && el.ownerDocument.documentMode < 10) {
      event = 'on' + event;
      el.attachEvent(event, fn);
      cleaner.destroy = function() {
        if (fn) {
          el.detachEvent(event, fn);
          fn = null;
        }
      };
    } else {
      el.addEventListener(event, fn, true);
      cleaner.destroy = function() {
        if (fn) {
          el.removeEventListener(event, fn, true);
          fn = null;
        }
      };
    }
    return cleaner;
  }, coalesce:function(rec, ev) {
    var me = this, events = me.eventsRecorded, length = events.length, tail = length && events[length - 1], tail2 = length > 1 && events[length - 2], tail3 = length > 2 && events[length - 3];
    if (!tail) {
      return false;
    }
    if (rec.type === 'mousemove') {
      if (tail.type === 'mousemove' && rec.ts - tail.ts < 200) {
        rec.ts = tail.ts;
        events[length - 1] = rec;
        return true;
      }
    } else {
      if (rec.type === 'click') {
        if (tail2 && tail.type === 'mouseup' && tail2.type === 'mousedown') {
          if (rec.button == tail.button && rec.button == tail2.button && rec.target == tail.target && rec.target == tail2.target && me.samePt(rec, tail) && me.samePt(rec, tail2)) {
            events.pop();
            tail2.type = 'mduclick';
            return true;
          }
        }
      } else {
        if (rec.type === 'keyup') {
          if (tail2 && tail.type === 'keypress' && tail2.type === 'keydown') {
            if (rec.target === tail.target && rec.target === tail2.target) {
              events.pop();
              tail2.type = 'type';
              tail2.text = String.fromCharCode(tail.charCode);
              delete tail2.charCode;
              delete tail2.keyCode;
              if (tail3 && tail3.type === 'type') {
                if (tail3.text && tail3.target === tail2.target) {
                  tail3.text += tail2.text;
                  events.pop();
                }
              }
              return true;
            }
          } else {
            if (me.completeKeyStroke(tail, rec)) {
              tail.type = 'type';
              me.completeSpecialKeyStroke(ev.target, tail, rec);
              return true;
            } else {
              if (tail.type === 'scroll' && me.completeKeyStroke(tail2, rec)) {
                tail2.type = 'type';
                me.completeSpecialKeyStroke(ev.target, tail2, rec);
                events.pop();
                events.pop();
                events.push(tail, tail2);
                return true;
              }
            }
          }
        }
      }
    }
    return false;
  }, completeKeyStroke:function(down, up) {
    if (down && down.type === 'keydown' && down.keyCode === up.keyCode) {
      delete down.charCode;
      return true;
    }
    return false;
  }, completeSpecialKeyStroke:function(target, down, up) {
    var key = this.specialKeysByCode[up.keyCode];
    if (key && this.inputRe.test(target.tagName)) {
      delete down.keyCode;
      down.key = key;
      down.selection = this.getTextSelection(target);
      if (down.selection[0] === down.selection[1]) {
        down.caret = down.selection[0];
        delete down.selection;
      }
      return true;
    }
    return false;
  }, getElementXPath:function(el) {
    var me = this, good = false, xpath = [], count, sibling, t, tag;
    for (t = el; t; t = t.parentNode) {
      if (t == me.attachTo.document.body) {
        xpath.unshift('~');
        good = true;
        break;
      }
      if (t.id && !me.ignoreIdRegEx.test(t.id)) {
        xpath.unshift('#' + t.id);
        good = true;
        break;
      }
      for (count = 1, sibling = t; !!(sibling = sibling.previousSibling);) {
        if (sibling.tagName == t.tagName) {
          ++count;
        }
      }
      tag = t.tagName.toLowerCase();
      if (count < 2) {
        xpath.unshift(tag);
      } else {
        xpath.unshift(tag + '[' + count + ']');
      }
    }
    return good ? xpath.join('/') : null;
  }, getRecordedEvents:function() {
    return this.eventsRecorded;
  }, onEvent:function(ev) {
    var me = this, e = new Ext.event.Event(ev), info = me.eventsToRecord[e.type], root, modKeys, elXY, rec = {type:e.type, ts:me.getTimestamp(), target:me.getElementXPath(e.target)}, xy;
    if (!info || !rec.target) {
      return;
    }
    root = e.target.ownerDocument;
    root = root.defaultView || root.parentWindow;
    if (root !== me.attachTo) {
      return;
    }
    if (me.eventsToRecord.scroll) {
      me.syncScroll(e.target);
    }
    if (info.xy) {
      xy = e.getXY();
      if (info.pageCoords || !rec.target) {
        rec.px = xy[0];
        rec.py = xy[1];
      } else {
        elXY = Ext.fly(e.getTarget()).getXY();
        xy[0] -= elXY[0];
        xy[1] -= elXY[1];
        rec.x = xy[0];
        rec.y = xy[1];
      }
    }
    if (info.button) {
      if ('buttons' in ev) {
        rec.button = ev.buttons;
      } else {
        rec.button = ev.button;
      }
      if (!rec.button && info.whileDrag) {
        return;
      }
    }
    if (info.wheel) {
      rec.type = 'wheel';
      if (info.event === 'wheel') {
        rec.dx = ev.deltaX;
        rec.dy = ev.deltaY;
      } else {
        if (typeof ev.wheelDeltaX === 'number') {
          rec.dx = -1 / 40 * ev.wheelDeltaX;
          rec.dy = -1 / 40 * ev.wheelDeltaY;
        } else {
          if (ev.wheelDelta) {
            rec.dy = -1 / 40 * ev.wheelDelta;
          } else {
            if (ev.detail) {
              rec.dy = ev.detail;
            }
          }
        }
      }
    }
    if (info.modKeys) {
      me.modKeys[0] = e.altKey ? 'A' : '';
      me.modKeys[1] = e.ctrlKey ? 'C' : '';
      me.modKeys[2] = e.metaKey ? 'M' : '';
      me.modKeys[3] = e.shiftKey ? 'S' : '';
      modKeys = me.modKeys.join('');
      if (modKeys) {
        rec.modKeys = modKeys;
      }
    }
    if (info.key) {
      rec.charCode = e.getCharCode();
      rec.keyCode = e.getKey();
    }
    if (me.coalesce(rec, e)) {
      me.fireEvent('coalesce', me, rec);
    } else {
      me.eventsRecorded.push(rec);
      me.fireEvent('add', me, rec);
    }
  }, onStart:function() {
    var me = this, ddm = me.attachTo.Ext.dd.DragDropManager, evproto = me.attachTo.Ext.EventObjectImpl.prototype, special = [];
    Recorder.prototype.eventsToRecord.wheel.event = 'onwheel' in me.attachTo.document ? 'wheel' : 'mousewheel';
    me.listeners = [];
    Ext.Object.each(me.eventsToRecord, function(name, value) {
      if (value && value.listen !== false) {
        if (!value.event) {
          value.event = name;
        }
        if (value.alt && value.alt !== name) {
          if (!me.eventsToRecord[value.alt]) {
            special.push(value);
          }
        } else {
          me.listeners.push(me.listenToEvent(value.event));
        }
      }
    });
    Ext.each(special, function(info) {
      me.eventsToRecord[info.alt] = info;
      me.listeners.push(me.listenToEvent(info.alt));
    });
    me.ddmStopEvent = ddm.stopEvent;
    ddm.stopEvent = Ext.Function.createSequence(ddm.stopEvent, function(e) {
      me.onEvent(e);
    });
    me.evStopEvent = evproto.stopEvent;
    evproto.stopEvent = Ext.Function.createSequence(evproto.stopEvent, function() {
      me.onEvent(this);
    });
  }, onStop:function() {
    var me = this;
    Ext.destroy(me.listeners);
    me.listeners = null;
    me.attachTo.Ext.dd.DragDropManager.stopEvent = me.ddmStopEvent;
    me.attachTo.Ext.EventObjectImpl.prototype.stopEvent = me.evStopEvent;
  }, samePt:function(pt1, pt2) {
    return pt1.x == pt2.x && pt1.y == pt2.y;
  }, syncScroll:function(el) {
    var me = this, ts = me.getTimestamp(), oldX, oldY, x, y, scrolled, rec;
    for (var p = el; p; p = p.parentNode) {
      oldX = p.$lastScrollLeft;
      oldY = p.$lastScrollTop;
      x = p.scrollLeft;
      y = p.scrollTop;
      scrolled = false;
      if (oldX !== x) {
        if (x) {
          scrolled = true;
        }
        p.$lastScrollLeft = x;
      }
      if (oldY !== y) {
        if (y) {
          scrolled = true;
        }
        p.$lastScrollTop = y;
      }
      if (scrolled) {
        me.eventsRecorded.push(rec = {type:'scroll', target:me.getElementXPath(p), ts:ts, pos:[x, y]});
        me.fireEvent('add', me, rec);
      }
      if (p.tagName === 'BODY') {
        break;
      }
    }
  }};
});
Ext.define('Ext.ux.rating.Picker', {extend:'Ext.Gadget', xtype:'rating', focusable:true, cachedConfig:{family:'monospace', glyphs:'☆★', minimum:1, limit:5, overStyle:null, rounding:1, scale:'125%', selectedStyle:null, tip:null, trackOver:true, value:null, tooltipText:null, trackingValue:null}, config:{animate:null}, element:{cls:'u' + Ext.baseCSSPrefix + 'rating-picker', reference:'element', children:[{reference:'innerEl', cls:'u' + Ext.baseCSSPrefix + 'rating-picker-inner', listeners:{click:'onClick', 
mousemove:'onMouseMove', mouseenter:'onMouseEnter', mouseleave:'onMouseLeave'}, children:[{reference:'valueEl', cls:'u' + Ext.baseCSSPrefix + 'rating-picker-value'}, {reference:'trackerEl', cls:'u' + Ext.baseCSSPrefix + 'rating-picker-tracker'}]}]}, defaultBindProperty:'value', twoWayBindable:'value', overCls:'u' + Ext.baseCSSPrefix + 'rating-picker-over', trackOverCls:'u' + Ext.baseCSSPrefix + 'rating-picker-track-over', applyGlyphs:function(value) {
  if (typeof value === 'string') {
    if (value.length !== 2) {
      Ext.raise('Expected 2 characters for "glyphs" not "' + value + '".');
    }
    value = [value.charAt(0), value.charAt(1)];
  } else {
    if (typeof value[0] === 'number') {
      value = [String.fromCharCode(value[0]), String.fromCharCode(value[1])];
    }
  }
  return value;
}, applyOverStyle:function(style) {
  this.trackerEl.applyStyles(style);
}, applySelectedStyle:function(style) {
  this.valueEl.applyStyles(style);
}, applyTip:function(tip) {
  if (tip && typeof tip !== 'function') {
    if (!tip.isTemplate) {
      tip = new Ext.XTemplate(tip);
    }
    tip = tip.apply.bind(tip);
  }
  return tip;
}, applyTrackingValue:function(value) {
  return this.applyValue(value);
}, applyValue:function(v) {
  if (v !== null) {
    var rounding = this.getRounding(), limit = this.getLimit(), min = this.getMinimum();
    v = Math.round(Math.round(v / rounding) * rounding * 1000) / 1000;
    v = v < min ? min : v > limit ? limit : v;
  }
  return v;
}, onClick:function(event) {
  var value = this.valueFromEvent(event);
  this.setValue(value);
}, onMouseEnter:function() {
  this.element.addCls(this.overCls);
}, onMouseLeave:function() {
  this.element.removeCls(this.overCls);
}, onMouseMove:function(event) {
  var value = this.valueFromEvent(event);
  this.setTrackingValue(value);
}, updateFamily:function(family) {
  this.element.setStyle('fontFamily', "'" + family + "'");
}, updateGlyphs:function() {
  this.refreshGlyphs();
}, updateLimit:function() {
  this.refreshGlyphs();
}, updateScale:function(size) {
  this.element.setStyle('fontSize', size);
}, updateTip:function() {
  this.refreshTip();
}, updateTooltipText:function(text) {
  this.setTooltip(text);
}, updateTrackingValue:function(value) {
  var me = this, trackerEl = me.trackerEl, newWidth = me.valueToPercent(value);
  trackerEl.setStyle('width', newWidth);
  me.refreshTip();
}, updateTrackOver:function(trackOver) {
  this.element.toggleCls(this.trackOverCls, trackOver);
}, updateValue:function(value, oldValue) {
  var me = this, animate = me.getAnimate(), valueEl = me.valueEl, newWidth = me.valueToPercent(value), column, record;
  if (me.isConfiguring || !animate) {
    valueEl.setStyle('width', newWidth);
  } else {
    valueEl.stopAnimation();
    valueEl.animate(Ext.merge({from:{width:me.valueToPercent(oldValue)}, to:{width:newWidth}}, animate));
  }
  me.refreshTip();
  if (!me.isConfiguring) {
    if (me.hasListeners.change) {
      me.fireEvent('change', me, value, oldValue);
    }
    column = me.getWidgetColumn && me.getWidgetColumn();
    record = column && me.getWidgetRecord && me.getWidgetRecord();
    if (record && column.dataIndex) {
      record.set(column.dataIndex, value);
    }
  }
}, afterCachedConfig:function() {
  this.refresh();
  return this.callParent(arguments);
}, initConfig:function(instanceConfig) {
  this.isConfiguring = true;
  this.callParent([instanceConfig]);
  this.refresh();
}, setConfig:function() {
  var me = this;
  me.isReconfiguring = true;
  me.callParent(arguments);
  me.isReconfiguring = false;
  me.refresh();
  return me;
}, privates:{getGlyphTextNode:function(dom) {
  var node = dom.lastChild;
  if (!node || node.nodeType !== 3) {
    node = dom.ownerDocument.createTextNode('');
    dom.appendChild(node);
  }
  return node;
}, getTooltipData:function() {
  var me = this;
  return {component:me, tracking:me.getTrackingValue(), trackOver:me.getTrackOver(), value:me.getValue()};
}, refresh:function() {
  var me = this;
  if (me.invalidGlyphs) {
    me.refreshGlyphs(true);
  }
  if (me.invalidTip) {
    me.refreshTip(true);
  }
}, refreshGlyphs:function(now) {
  var me = this, later = !now && (me.isConfiguring || me.isReconfiguring), el, glyphs, limit, on, off, trackerEl, valueEl;
  if (!later) {
    el = me.getGlyphTextNode(me.innerEl.dom);
    valueEl = me.getGlyphTextNode(me.valueEl.dom);
    trackerEl = me.getGlyphTextNode(me.trackerEl.dom);
    glyphs = me.getGlyphs();
    limit = me.getLimit();
    for (on = off = ''; limit--;) {
      off += glyphs[0];
      on += glyphs[1];
    }
    el.nodeValue = off;
    valueEl.nodeValue = on;
    trackerEl.nodeValue = on;
  }
  me.invalidGlyphs = later;
}, refreshTip:function(now) {
  var me = this, later = !now && (me.isConfiguring || me.isReconfiguring), data, text, tooltip;
  if (!later) {
    tooltip = me.getTip();
    if (tooltip) {
      data = me.getTooltipData();
      text = tooltip(data);
      me.setTooltipText(text);
    }
  }
  me.invalidTip = later;
}, valueFromEvent:function(event) {
  var me = this, el = me.innerEl, ex = event.getX(), rounding = me.getRounding(), cx = el.getX(), x = ex - cx, w = el.getWidth(), limit = me.getLimit(), v;
  if (me.getInherited().rtl) {
    x = w - x;
  }
  v = x / w * limit;
  v = Math.ceil(v / rounding) * rounding;
  return v;
}, valueToPercent:function(value) {
  value = value / this.getLimit() * 100;
  return value + '%';
}}});
