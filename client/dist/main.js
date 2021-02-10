/******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId]) {
/******/ 			return installedModules[moduleId].exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			i: moduleId,
/******/ 			l: false,
/******/ 			exports: {}
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.l = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		if(!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, { enumerable: true, get: getter });
/******/ 		}
/******/ 	};
/******/
/******/ 	// define __esModule on exports
/******/ 	__webpack_require__.r = function(exports) {
/******/ 		if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 			Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 		}
/******/ 		Object.defineProperty(exports, '__esModule', { value: true });
/******/ 	};
/******/
/******/ 	// create a fake namespace object
/******/ 	// mode & 1: value is a module id, require it
/******/ 	// mode & 2: merge all properties of value into the ns
/******/ 	// mode & 4: return value when already ns object
/******/ 	// mode & 8|1: behave like require
/******/ 	__webpack_require__.t = function(value, mode) {
/******/ 		if(mode & 1) value = __webpack_require__(value);
/******/ 		if(mode & 8) return value;
/******/ 		if((mode & 4) && typeof value === 'object' && value && value.__esModule) return value;
/******/ 		var ns = Object.create(null);
/******/ 		__webpack_require__.r(ns);
/******/ 		Object.defineProperty(ns, 'default', { enumerable: true, value: value });
/******/ 		if(mode & 2 && typeof value != 'string') for(var key in value) __webpack_require__.d(ns, key, function(key) { return value[key]; }.bind(null, key));
/******/ 		return ns;
/******/ 	};
/******/
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = function(module) {
/******/ 		var getter = module && module.__esModule ?
/******/ 			function getDefault() { return module['default']; } :
/******/ 			function getModuleExports() { return module; };
/******/ 		__webpack_require__.d(getter, 'a', getter);
/******/ 		return getter;
/******/ 	};
/******/
/******/ 	// Object.prototype.hasOwnProperty.call
/******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "/";
/******/
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = 0);
/******/ })
/************************************************************************/
/******/ ({

/***/ "./client/src/js/main.js":
/*!*******************************!*\
  !*** ./client/src/js/main.js ***!
  \*******************************/
/*! no exports provided */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _polyfill_arrayFrom__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./polyfill/arrayFrom */ "./client/src/js/polyfill/arrayFrom.js");
/* harmony import */ var _partialuserforms_partialsubmission__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./partialuserforms/partialsubmission */ "./client/src/js/partialuserforms/partialsubmission.js");
/* harmony import */ var _partialuserforms_partialstorage__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./partialuserforms/partialstorage */ "./client/src/js/partialuserforms/partialstorage.js");



Object(_polyfill_arrayFrom__WEBPACK_IMPORTED_MODULE_0__["default"])();
Object(_partialuserforms_partialsubmission__WEBPACK_IMPORTED_MODULE_1__["default"])();
Object(_partialuserforms_partialstorage__WEBPACK_IMPORTED_MODULE_2__["default"])();

/***/ }),

/***/ "./client/src/js/partialuserforms/partialstorage.js":
/*!**********************************************************!*\
  !*** ./client/src/js/partialuserforms/partialstorage.js ***!
  \**********************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony default export */ __webpack_exports__["default"] = (function () {// @todo, add the option to prefill
});

/***/ }),

/***/ "./client/src/js/partialuserforms/partialsubmission.js":
/*!*************************************************************!*\
  !*** ./client/src/js/partialuserforms/partialsubmission.js ***!
  \*************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
function _typeof(obj) { if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

var baseDomain = document.baseURI || window.location.protocol + '//' + window.location.hostname + (window.location.port ? ':' + window.location.port : '' + '/');
var submitURL = 'partialuserform/save';
var form = document.body.querySelector('form.userform');

var formElements = function formElements() {
  return Array.from(form.querySelectorAll('[name]:not([type=submit])'));
};

var saveButton = form.querySelector('button.step-button-save');
var nextButton = form.querySelector('button.step-button-next');
var shareButton = form.querySelector('a.step-button-share');
var submitButton = form.querySelector('[type=submit]');
var repeatButton = form.querySelectorAll('button.btn-add-more');
var removeFileButton = form.querySelectorAll('a.partial-file-remove');
var uploadButtons = form.querySelectorAll('input[type=file]');
var requests = [];

var getElementValue = function getElementValue(element, fieldName) {
  var value = element.value;

  if (element.getAttribute('type') === 'select') {
    return element[element.selectedIndex].value;
  }

  if (element.getAttribute('type') === 'radio') {
    var name = "[name=".concat(fieldName, "]:checked");
    var checkedElement = document.body.querySelector(name);
    return checkedElement !== null ? checkedElement.value : "";
  }

  if (element.getAttribute('type') === 'checkbox') {
    var _name = "[name=\"".concat(fieldName, "\"]:checked");

    var checkedElements = Array.from(document.body.querySelectorAll(_name));
    var valueArray = [];

    if (checkedElements.length > 0) {
      checkedElements.forEach(function (element) {
        valueArray.push(element.value);
      });
      return valueArray;
    }

    return "";
  }

  if (element.getAttribute('type') === 'file' && element.files.length > 0) {
    return element.files[0];
  }

  return value;
};

var submitPartial = function submitPartial() {
  // This will trigger recaptcha validation
  grecaptcha.execute(recaptchaId);
  var data = new FormData();
  formElements().forEach(function (element) {
    var fieldName = element.getAttribute('name');
    var value = getElementValue(element, fieldName);

    if (_typeof(value) === 'object' && element.getAttribute('type') === 'file') {
      data.append(fieldName, value);
    } else if (_typeof(value) === 'object') {
      value.forEach(function (arrayValue) {
        data.append(fieldName, arrayValue);
      });
    } else {
      data.append(fieldName, value);
    }
  });
  /** global: XMLHttpRequest */

  var httpRequest = new XMLHttpRequest();

  httpRequest.onreadystatechange = function () {
    if (httpRequest.readyState === 1) {
      saveButton.setAttribute('disabled', 'disabled');
      submitButton.setAttribute('disabled', 'disabled');
    } else if (httpRequest.readyState === 4) {
      saveButton.removeAttribute('disabled');
      submitButton.removeAttribute('disabled');
      form.classList.remove('dirty');

      if (httpRequest.status === 409) {
        alert(httpRequest.responseText);
      }
    }
  };

  requests.push(httpRequest);
  httpRequest.open('POST', "".concat(baseDomain).concat(submitURL), true);
  httpRequest.send(data);
  return httpRequest;
};

var replaceExistingAttribute = function replaceExistingAttribute(dom, attr, previous, latest) {
  var matches = dom.querySelectorAll("[".concat(attr, "*=").concat(previous));
  matches.forEach(function (item) {
    var oldValue = item.getAttribute(attr);
    var newValue = oldValue.replace(new RegExp(previous), latest);
    item.setAttribute(attr, newValue);
  });
};

var createUploadLinksHolder = function createUploadLinksHolder(name, elementHolder) {
  var span = elementHolder.querySelector("span#".concat(name));

  if (span) {
    while (span.firstChild) {
      span.removeChild(span.lastChild);
    }
  } else {
    span = document.createElement('span');
    span.setAttribute('id', name);
    elementHolder.appendChild(span);
  }

  span.setAttribute('class', 'right-title');
  return span;
};

var removePartialFile = function removePartialFile(event) {
  event.preventDefault();
  var link = event.target;
  var form = new FormData();
  var holder = link.parentNode.parentNode;
  var name = link.parentNode.getAttribute('id');
  var disabled = link.getAttribute('data-disabled');
  var linkData = JSON.parse(link.getAttribute('data-file-remove'));
  var span = createUploadLinksHolder(name, holder);

  if (disabled === 'disabled') {
    span.appendChild(document.createTextNode('Still in progress...'));
    span.setAttribute('class', 'right-title loading');
    return;
  }

  Object.keys(linkData).forEach(function (name) {
    form.append(name, linkData[name]);
  });
  link.setAttribute('data-disabled', 'disabled');
  span.appendChild(document.createTextNode('Removing selected file...'));
  span.setAttribute('class', 'right-title loading');
  /** global: XMLHttpRequest */

  var httpRequest = new XMLHttpRequest();

  httpRequest.onreadystatechange = function () {
    if (httpRequest.readyState === 4) {
      if (httpRequest.status === 409) {
        alert(httpRequest.responseText);
      } else {
        span = createUploadLinksHolder(name, holder);
        span.appendChild(document.createTextNode('We accept .png, .jpg and .pdf'));
      }
    }
  };

  requests.push(httpRequest);
  httpRequest.open('POST', "".concat(baseDomain, "partialuserform/remove-file"), true);
  httpRequest.send(form);
};

var createUploadLinks = function createUploadLinks(record) {
  var name = record['Name'];
  var field = document.body.querySelector("[name=".concat(name, "]"));
  var holder = field.parentNode.parentNode;
  var span = createUploadLinksHolder("".concat(name, "_right_title"), holder);
  span.appendChild(document.createTextNode('View '));
  var viewLink = document.createElement('a');
  viewLink.setAttribute('class', 'external');
  viewLink.setAttribute('rel', 'external');
  viewLink.setAttribute('title', 'Open external link');
  viewLink.setAttribute('href', record['Link']);
  viewLink.setAttribute('target', '_blank');
  viewLink.appendChild(document.createTextNode(record['Title']));
  span.appendChild(viewLink);
  span.appendChild(document.createTextNode(" \xA0"));
  var removeLink = document.createElement('a');
  removeLink.setAttribute('class', 'partial-file-remove');
  removeLink.setAttribute('href', 'javascript:;');
  removeLink.setAttribute('data-disabled', '');
  removeLink.setAttribute('data-file-remove', "{\"PartialID\":".concat(record['PartialID'], ",\"FileID\":").concat(record['FileID'], "}"));
  removeLink.appendChild(document.createTextNode('Remove ✗'));
  removeLink.addEventListener('click', removePartialFile);
  span.appendChild(removeLink);
};

var uploadPartialHandler = function uploadPartialHandler(event) {
  var uploadField = event.target;

  if (!uploadField.value) {
    return;
  }

  var parentHolder = uploadField.parentNode;
  var httpRequest = submitPartial();
  var holder = parentHolder.parentNode;
  var name = uploadField.getAttribute('name');
  var span = createUploadLinksHolder("".concat(name, "_right_title"), holder);
  span.appendChild(document.createTextNode('Uploading selected file...'));
  span.setAttribute('class', 'right-title loading');

  httpRequest.onload = function () {
    if (httpRequest.status == 201) {
      var records = JSON.parse(httpRequest.responseText);

      for (var index = 0; index < records.length; index++) {
        if (records[index]['Name'] === name) {
          createUploadLinks(records[index]);
        }
      }
    }

    span.setAttribute('class', 'right-title');
  };
};

var sharePartial = function sharePartial(event) {
  event.preventDefault();
  var linkTag = event.target;
  var httpRequest = submitPartial();

  httpRequest.onload = function () {
    if (httpRequest.status == 201) {
      document.location.href = linkTag.getAttribute('href');
    }
  };
};

var attachSavePartial = function attachSavePartial() {
  if (saveButton) {
    saveButton.addEventListener('click', submitPartial);
  }

  if (nextButton) {
    nextButton.addEventListener('click', submitPartial);
  }

  if (shareButton) {
    shareButton.addEventListener('click', sharePartial);
  }

  if (removeFileButton.length) {
    for (var index = 0; index < removeFileButton.length; index++) {
      removeFileButton[index].addEventListener('click', removePartialFile);
    }
  }

  if (uploadButtons.length) {
    for (var _index = 0; _index < uploadButtons.length; _index++) {
      uploadButtons[_index].addEventListener('change', uploadPartialHandler);
    }
  }
};

var abortPendingSubmissions = function abortPendingSubmissions() {
  // Clear all pending partial submissions on submit
  if (form !== null) {
    form._submit = form.submit; // Save reference

    form.submit = function () {
      // Abort all requests
      if (requests.length) {
        requests.forEach(function (xhr) {
          xhr.abort();
        });
      }

      form._submit();
    };
  }
};

/* harmony default export */ __webpack_exports__["default"] = (function () {
  attachSavePartial();
  abortPendingSubmissions();
});
var recaptchaId = null;
/*
  - Function called onload in https://www.google.com/recaptcha/api.js?render=explicit&onload=loadRecaptcha
  - Above script src added in src/controllers/PartialUserFormController.php
  - Assumption
    <div id="g-recaptcha-partial-userform" data-sitekey="$RecaptchaSitekey"></div> tag is present in template
**/

window.loadRecaptcha = function () {
  var captchaElement = document.getElementById('g-recaptcha-partial-userform');
  var captchaOptions = {
    sitekey: captchaElement.dataset.sitekey,
    size: 'invisible',
    callback: handleAction,
    'expired-callback': function expiredCallback() {
      alert('Expired reCAPTCHA response and the you will need to re-verify.');
      return false;
    }
  };
  recaptchaId = grecaptcha.render(captchaElement, captchaOptions);
}; // reCaptcha callback - Do the main processing here


window.handleAction = function () {
  // if there is no previous reponse then execute recaptcha else do nothing (As we are not processing any form data here, it's done in submitPartial function)
  grecaptcha.execute(recaptchaId); // Once recaptcha is validated reset recaptcha token for next action captcha validation

  grecaptcha.reset(recaptchaId);
};

/***/ }),

/***/ "./client/src/js/polyfill/arrayFrom.js":
/*!*********************************************!*\
  !*** ./client/src/js/polyfill/arrayFrom.js ***!
  \*********************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
// Production steps of ECMA-262, Edition 6, 22.1.2.1
var arrayFrom = function arrayFrom() {
  var symbolIterator;

  try {
    symbolIterator = Symbol.iterator ? Symbol.iterator : 'Symbol(Symbol.iterator)';
  } catch (_unused) {
    symbolIterator = 'Symbol(Symbol.iterator)';
  }

  var toStr = Object.prototype.toString;

  var isCallable = function isCallable(fn) {
    return typeof fn === 'function' || toStr.call(fn) === '[object Function]';
  };

  var toInteger = function toInteger(value) {
    var number = Number(value);
    if (isNaN(number)) return 0;
    if (number === 0 || !isFinite(number)) return number;
    return (number > 0 ? 1 : -1) * Math.floor(Math.abs(number));
  };

  var maxSafeInteger = Math.pow(2, 53) - 1;

  var toLength = function toLength(value) {
    var len = toInteger(value);
    return Math.min(Math.max(len, 0), maxSafeInteger);
  };

  var setGetItemHandler = function setGetItemHandler(isIterator, items) {
    var iterator = isIterator && items[symbolIterator]();
    return function getItem(k) {
      return isIterator ? iterator.next() : items[k];
    };
  };

  var getArray = function getArray(T, A, len, getItem, isIterator, mapFn) {
    // 16. Let k be 0.
    var k = 0; // 17. Repeat, while k < len… or while iterator is done (also steps a - h)

    while (k < len || isIterator) {
      var item = getItem(k);
      var kValue = isIterator ? item.value : item;

      if (isIterator && item.done) {
        return A;
      } else {
        if (mapFn) {
          A[k] = typeof T === 'undefined' ? mapFn(kValue, k) : mapFn.call(T, kValue, k);
        } else {
          A[k] = kValue;
        }
      }

      k += 1;
    }

    if (isIterator) {
      throw new TypeError('Array.from: provided arrayLike or iterator has length more then 2 ** 52 - 1');
    } else {
      A.length = len;
    }

    return A;
  }; // The length property of the from method is 1.


  return function from(arrayLikeOrIterator
  /*, mapFn, thisArg */
  ) {
    // 1. Let C be the this value.
    var C = this; // 2. Let items be ToObject(arrayLikeOrIterator).

    var items = Object(arrayLikeOrIterator);
    var isIterator = isCallable(items[symbolIterator]); // 3. ReturnIfAbrupt(items).

    if (arrayLikeOrIterator == null && !isIterator) {
      throw new TypeError('Array.from requires an array-like object or iterator - not null or undefined');
    } // 4. If mapfn is undefined, then let mapping be false.


    var mapFn = arguments.length > 1 ? arguments[1] : void undefined;
    var T;

    if (typeof mapFn !== 'undefined') {
      // 5. else
      // 5. a If IsCallable(mapfn) is false, throw a TypeError exception.
      if (!isCallable(mapFn)) {
        throw new TypeError('Array.from: when provided, the second argument must be a function');
      } // 5. b. If thisArg was supplied, let T be thisArg; else let T be undefined.


      if (arguments.length > 2) {
        T = arguments[2];
      }
    } // 10. Let lenValue be Get(items, "length").
    // 11. Let len be ToLength(lenValue).


    var len = toLength(items.length); // 13. If IsConstructor(C) is true, then
    // 13. a. Let A be the result of calling the [[Construct]] internal method
    // of C with an argument list containing the single item len.
    // 14. a. Else, Let A be ArrayCreate(len).

    var A = isCallable(C) ? Object(new C(len)) : new Array(len);
    return getArray(T, A, len, setGetItemHandler(isIterator, items), isIterator, mapFn);
  };
};

var polyfillArrayFrom = function polyfillArrayFrom() {
  if (!Array.from) {
    Array.from = arrayFrom();
  }
};

/* harmony default export */ __webpack_exports__["default"] = (function () {
  arrayFrom();
  polyfillArrayFrom();
});

/***/ }),

/***/ 0:
/*!*************************************!*\
  !*** multi ./client/src/js/main.js ***!
  \*************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

module.exports = __webpack_require__(/*! /Users/amolw/clients/mbie27/vendor/firesphere/partialuserforms/client/src/js/main.js */"./client/src/js/main.js");


/***/ })

/******/ });