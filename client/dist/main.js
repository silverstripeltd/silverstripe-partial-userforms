!function(e){var t={};function r(n){if(t[n])return t[n].exports;var o=t[n]={i:n,l:!1,exports:{}};return e[n].call(o.exports,o,o.exports,r),o.l=!0,o.exports}r.m=e,r.c=t,r.d=function(e,t,n){r.o(e,t)||Object.defineProperty(e,t,{enumerable:!0,get:n})},r.r=function(e){"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(e,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(e,"__esModule",{value:!0})},r.t=function(e,t){if(1&t&&(e=r(e)),8&t)return e;if(4&t&&"object"==typeof e&&e&&e.__esModule)return e;var n=Object.create(null);if(r.r(n),Object.defineProperty(n,"default",{enumerable:!0,value:e}),2&t&&"string"!=typeof e)for(var o in e)r.d(n,o,function(t){return e[t]}.bind(null,o));return n},r.n=function(e){var t=e&&e.__esModule?function(){return e.default}:function(){return e};return r.d(t,"a",t),t},r.o=function(e,t){return Object.prototype.hasOwnProperty.call(e,t)},r.p="/",r(r.s=0)}([function(e,t,r){e.exports=r(1)},function(e,t,r){"use strict";function n(e){return(n="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(e){return typeof e}:function(e){return e&&"function"==typeof Symbol&&e.constructor===Symbol&&e!==Symbol.prototype?"symbol":typeof e})(e)}r.r(t);var o=document.baseURI,u=document.body.querySelector("form.userform"),a=u.querySelector("button.step-button-save"),i=u.querySelector("button.step-button-next"),c=u.querySelector("a.step-button-share"),l=u.querySelector("[type=submit]"),s=u.querySelector("button.btn-add-more"),f=[],d=function(){var e=new FormData;Array.from(u.querySelectorAll("[name]:not([type=submit])")).forEach(function(t){var r=t.getAttribute("name"),o=function(e,t){var r=e.value;if("select"===e.getAttribute("type"))return e[e.selectedIndex].value;if("radio"===e.getAttribute("type")){var n="[name=".concat(t,"]:checked"),o=document.body.querySelector(n);return null!==o?o.value:""}if("checkbox"===e.getAttribute("type")){var u='[name="'.concat(t,'"]:checked'),a=Array.from(document.body.querySelectorAll(u)),i=[];return a.length>0?(a.forEach(function(e){i.push(e.value)}),i):""}return"file"===e.getAttribute("type")&&e.files.length>0?e.files[0]:r}(t,r);e.has(r)||("object"===n(o)&&"file"===t.getAttribute("type")?e.append(r,o):"object"===n(o)?o.forEach(function(t){e.append(r,t)}):e.append(r,o))});var t=u.querySelector("[name=PartialID]");t&&e.append("PartialID",t.value);var r=new XMLHttpRequest;r.onreadystatechange=function(){1===r.readyState?(a.setAttribute("disabled","disabled"),l.setAttribute("disabled","disabled")):4===r.readyState&&(a.removeAttribute("disabled"),l.removeAttribute("disabled"),u.classList.remove("dirty"),409===r.status&&alert(r.responseText))},f.push(r),r.open("POST","".concat(o).concat("partialuserform/save"),!0),r.send(e)},b=function(e,t,r,n){e.querySelectorAll("[".concat(t,"*=").concat(r)).forEach(function(e){var o=e.getAttribute(t).replace(new RegExp(r),n);e.setAttribute(t,o)})},p=function(e){e.preventDefault();var t=e.target,r=t.parentNode,n=r.parentNode,o=n.querySelector(".repeat-source"),u=n.querySelector(".repeat-destination"),a=JSON.parse(t.getAttribute("data"));Object.keys(a).forEach(function(e){var n=e+"__"+(a[e].length+1),i=o.querySelector("#"+e).cloneNode(!0);i.setAttribute("id",n),b(i,"id",e,n),b(i,"name",e,n),b(i,"for",e,n),u.append(i),a[e].push(n),t.setAttribute("data",JSON.stringify(a)),r.querySelector("input[type=hidden]").setAttribute("value",JSON.stringify(a))})};a.addEventListener("click",d),i.addEventListener("click",d),c.addEventListener("click",d),s.addEventListener("click",p),null!==u&&(u._submit=u.submit,u.submit=function(){f.length&&f.forEach(function(e){e.abort()}),u._submit()})}]);