!function(e){var t={};function r(o){if(t[o])return t[o].exports;var n=t[o]={i:o,l:!1,exports:{}};return e[o].call(n.exports,n,n.exports,r),n.l=!0,n.exports}r.m=e,r.c=t,r.d=function(e,t,o){r.o(e,t)||Object.defineProperty(e,t,{enumerable:!0,get:o})},r.r=function(e){"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(e,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(e,"__esModule",{value:!0})},r.t=function(e,t){if(1&t&&(e=r(e)),8&t)return e;if(4&t&&"object"==typeof e&&e&&e.__esModule)return e;var o=Object.create(null);if(r.r(o),Object.defineProperty(o,"default",{enumerable:!0,value:e}),2&t&&"string"!=typeof e)for(var n in e)r.d(o,n,function(t){return e[t]}.bind(null,n));return o},r.n=function(e){var t=e&&e.__esModule?function(){return e.default}:function(){return e};return r.d(t,"a",t),t},r.o=function(e,t){return Object.prototype.hasOwnProperty.call(e,t)},r.p="/",r(r.s=0)}([function(e,t,r){e.exports=r(1)},function(e,t,r){"use strict";function o(e){return(o="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(e){return typeof e}:function(e){return e&&"function"==typeof Symbol&&e.constructor===Symbol&&e!==Symbol.prototype?"symbol":typeof e})(e)}r.r(t);var n=document.baseURI,u=document.body.querySelector("form.userform"),c=[],f=function(){var e=new FormData;Array.from(document.body.querySelectorAll("form.userform [name]:not([type=hidden]):not([type=submit])")).forEach(function(t){var r=t.getAttribute("name"),n=function(e,t){var r=e.value;if("select"===e.getAttribute("type"))return e[e.selectedIndex].value;if("radio"===e.getAttribute("type")){var o="[name=".concat(t,"]:checked"),n=document.body.querySelector(o);return null!==n?n.value:""}if("checkbox"===e.getAttribute("type")){var u='[name="'.concat(t,'"]:checked'),c=Array.from(document.body.querySelectorAll(u)),f=[];return c.length>0?(c.forEach(function(e){f.push(e.value)}),f):""}return"file"===e.getAttribute("type")&&e.files.length>0?e.files[0]:r}(t,r);e.has(r)||("object"===o(n)&&"file"===t.getAttribute("type")?e.append(r,n):"object"===o(n)?n.forEach(function(t){e.append(r,t)}):e.append(r,n))});var t=new XMLHttpRequest;c.push(t),t.open("POST","".concat(n).concat("partialuserform/save"),!0),t.send(e)},i=function(e){e.addEventListener("click",f)};Array.from(document.body.querySelectorAll("form.userform ul li.step-button-wrapper button")).forEach(i),null!==u&&(u._submit=u.submit,u.submit=function(){confirm("Are you sure you want to submit this form?")&&(c.length&&c.forEach(function(e){e.abort()}),u._submit())})}]);