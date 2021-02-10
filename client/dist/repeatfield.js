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
/******/ 	return __webpack_require__(__webpack_require__.s = 1);
/******/ })
/************************************************************************/
/******/ ({

/***/ "./client/src/js/repeatfield.js":
/*!**************************************!*\
  !*** ./client/src/js/repeatfield.js ***!
  \**************************************/
/*! no static exports found */
/***/ (function(module, exports) {

var parseCSV = function parseCSV(csv) {
  return csv.split(',').filter(function (item) {
    return !!item;
  }).map(function (item) {
    return parseInt(item);
  });
};

var updateOneUntilMax = function updateOneUntilMax(csv, maximum) {
  var values = parseCSV(csv);

  for (var counter = 1; counter <= maximum; counter++) {
    if (values.indexOf(counter) === -1) {
      values.push(counter);
      break;
    }
  }

  return values;
};

var duplicateFields = function duplicateFields(event) {
  event.preventDefault();
  var repeatButton = event.target;
  var hiddenInput = repeatButton.parentNode.querySelector('input[type=hidden]');
  var maximum = repeatButton.getAttribute('data-maximum');
  var values = updateOneUntilMax(hiddenInput.value, maximum);
  hiddenInput.value = values.join(',');
  toggleRepeatedFields(repeatButton);
};

var hideRepeatGroup = function hideRepeatGroup(event) {
  event.preventDefault();
  var button = event.target;

  if (button.getAttribute('data-warned') != 1) {
    button.setAttribute('data-warned', 1);
    return window.alert('Before deleting extra field set, make the values are cleared.');
  }

  button.setAttribute('data-warned', 0);
  var buttonContainer = button.parentNode;
  var mainContainer = buttonContainer.parentNode.parentNode;
  var source = mainContainer.querySelector('.repeat-source');
  var destination = mainContainer.querySelector('.repeat-destination');
  var counter = button.getAttribute('data-counter');
  var groupName = button.getAttribute('data-name');
  var hiddenInput = mainContainer.querySelector("#".concat(groupName));
  var addButtonContainer = hiddenInput.parentNode;
  var values = parseCSV(hiddenInput.value).filter(function (item) {
    return item != counter;
  });
  var resetForm = document.createElement('form');
  hiddenInput.value = values.join(',');
  buttonContainer.className = 'repeat-group animation-out';
  setTimeout(function () {
    resetForm.appendChild(buttonContainer);
    resetForm.reset(); //resets input values

    source.appendChild(buttonContainer);
    addButtonContainer.style.display = 'block';
  }, 500);
};

var initialiseRepeatedFields = function initialiseRepeatedFields(repeatButton) {
  var hiddenData = JSON.parse(repeatButton.getAttribute('data-fields'));

  if (!hiddenData) {
    return;
  }

  var buttonContainer = repeatButton.parentNode;
  var mainContainer = buttonContainer.parentNode;
  var source = mainContainer.querySelector('.repeat-source');
  var destination = mainContainer.querySelector('.repeat-destination');
  var hiddenInput = buttonContainer.querySelector('input[type=hidden]');
  var fieldName = hiddenInput.getAttribute('name');
  var removeBtnLabel = repeatButton.getAttribute('data-remove-label');
  var removeBtnCss = repeatButton.getAttribute('data-remove-css') || '';
  var original;
  Object.keys(hiddenData).forEach(function (index) {
    for (var i = 0; i <= parseInt(hiddenData[index]); i++) {
      var counter = i ? "__".concat(i) : '';
      var groupId = "repeat-".concat(fieldName).concat(counter);
      var groupContainer = source.querySelector("#".concat(groupId));

      if (!groupContainer) {
        groupContainer = document.createElement('div');
        groupContainer.setAttribute('id', groupId);
        groupContainer.className = 'repeat-group';
        var hr = document.createElement('hr');
        hr.style.clear = 'both';
        groupContainer.appendChild(hr);

        if (i > 0) {
          var deleteButton = document.createElement('button');
          deleteButton.className = 'delete-repeat-group ' + removeBtnCss;
          deleteButton.textContent = removeBtnLabel || "\xD7";
          deleteButton.setAttribute('data-counter', i);
          deleteButton.setAttribute('data-name', fieldName);
          deleteButton.setAttribute('data-warned', 0);
          groupContainer.appendChild(deleteButton);
          deleteButton.addEventListener('click', hideRepeatGroup);
        }

        source.appendChild(groupContainer);

        if (i === 0) {
          original = groupContainer;
        }
      }

      var clonedField = source.querySelector("#".concat(index).concat(counter));
      groupContainer.appendChild(clonedField);

      if (i > 0) {
        var _deleteButton = groupContainer.querySelector('.delete-repeat-group');

        _deleteButton.insertAdjacentElement('beforebegin', clonedField);
      }
    }
  });

  if (original) {
    destination.appendChild(original);
  }
};

var toggleRepeatedFields = function toggleRepeatedFields(repeatButton) {
  var buttonContainer = repeatButton.parentNode;
  var mainContainer = buttonContainer.parentNode;
  var source = mainContainer.querySelector('.repeat-source');
  var destination = mainContainer.querySelector('.repeat-destination');
  var hiddenInput = buttonContainer.querySelector('input[type=hidden]');
  var fieldName = hiddenInput.getAttribute('name');
  var maximum = repeatButton.getAttribute('data-maximum');
  var values = parseCSV(hiddenInput.value);
  values.forEach(function (counter) {
    var groupContainer = source.querySelector("#repeat-".concat(fieldName, "__").concat(counter));

    if (groupContainer) {
      groupContainer.className = 'repeat-group animation-in';
      destination.appendChild(groupContainer);
    }
  });
  buttonContainer.style.display = values.filter(function (i) {
    return i;
  }).length >= maximum ? 'none' : 'block';
};

window.addEventListener('DOMContentLoaded', function (event) {
  var form = document.body.querySelector('form.userform');
  var repeatButton = form.querySelectorAll('button.btn-add-more');

  if (repeatButton.length) {
    for (var index = 0; index < repeatButton.length; index++) {
      repeatButton[index].addEventListener('click', duplicateFields);
      initialiseRepeatedFields(repeatButton[index]);
      toggleRepeatedFields(repeatButton[index]);
    }
  }
});

/***/ }),

/***/ 1:
/*!********************************************!*\
  !*** multi ./client/src/js/repeatfield.js ***!
  \********************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

module.exports = __webpack_require__(/*! /Users/amolw/clients/mbie27/vendor/firesphere/partialuserforms/client/src/js/repeatfield.js */"./client/src/js/repeatfield.js");


/***/ })

/******/ });