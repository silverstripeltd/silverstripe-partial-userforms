!function(e){var t={};function r(n){if(t[n])return t[n].exports;var a=t[n]={i:n,l:!1,exports:{}};return e[n].call(a.exports,a,a.exports,r),a.l=!0,a.exports}r.m=e,r.c=t,r.d=function(e,t,n){r.o(e,t)||Object.defineProperty(e,t,{enumerable:!0,get:n})},r.r=function(e){"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(e,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(e,"__esModule",{value:!0})},r.t=function(e,t){if(1&t&&(e=r(e)),8&t)return e;if(4&t&&"object"==typeof e&&e&&e.__esModule)return e;var n=Object.create(null);if(r.r(n),Object.defineProperty(n,"default",{enumerable:!0,value:e}),2&t&&"string"!=typeof e)for(var a in e)r.d(n,a,function(t){return e[t]}.bind(null,a));return n},r.n=function(e){var t=e&&e.__esModule?function(){return e.default}:function(){return e};return r.d(t,"a",t),t},r.o=function(e,t){return Object.prototype.hasOwnProperty.call(e,t)},r.p="/",r(r.s=0)}([function(e,t,r){e.exports=r(3)},,,function(e,t,r){"use strict";r.r(t);var n=function(){var e;try{e=Symbol.iterator?Symbol.iterator:"Symbol(Symbol.iterator)"}catch(t){e="Symbol(Symbol.iterator)"}var t=Object.prototype.toString,r=function(e){return"function"==typeof e||"[object Function]"===t.call(e)},n=Math.pow(2,53)-1,a=function(e){var t=function(e){var t=Number(e);return isNaN(t)?0:0!==t&&isFinite(t)?(t>0?1:-1)*Math.floor(Math.abs(t)):t}(e);return Math.min(Math.max(t,0),n)},o=function(t,r){var n=t&&r[e]();return function(e){return t?n.next():r[e]}},i=function(e,t,r,n,a,o){for(var i=0;i<r||a;){var l=n(i),c=a?l.value:l;if(a&&l.done)return t;t[i]=o?void 0===e?o(c,i):o.call(e,c,i):c,i+=1}if(a)throw new TypeError("Array.from: provided arrayLike or iterator has length more then 2 ** 52 - 1");return t.length=r,t};return function(t){var n=this,l=Object(t),c=r(l[e]);if(null==t&&!c)throw new TypeError("Array.from requires an array-like object or iterator - not null or undefined");var u,d=arguments.length>1?arguments[1]:void 0;if(void 0!==d){if(!r(d))throw new TypeError("Array.from: when provided, the second argument must be a function");arguments.length>2&&(u=arguments[2])}var s=a(l.length),f=r(n)?Object(new n(s)):new Array(s);return i(u,f,s,o(c,l),c,d)}};function a(e){return(a="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(e){return typeof e}:function(e){return e&&"function"==typeof Symbol&&e.constructor===Symbol&&e!==Symbol.prototype?"symbol":typeof e})(e)}var o=window.origin,i=document.body.querySelector("form.userform"),l=i.querySelector("button.step-button-save"),c=i.querySelector("button.step-button-next"),u=i.querySelector("a.step-button-share"),d=i.querySelector("[type=submit]"),s=(i.querySelectorAll("button.btn-add-more"),i.querySelectorAll("a.partial-file-remove")),f=i.querySelectorAll("input[type=file]"),p=[],b=function(){"undefined"!=typeof grecaptcha&&grecaptcha.execute(A);var e=new FormData;Array.from(i.querySelectorAll("[name]:not([type=submit])")).forEach(function(t){var r=t.getAttribute("name"),n=function(e,t){var r=e.value;if("select"===e.getAttribute("type"))return e[e.selectedIndex].value;if("radio"===e.getAttribute("type")){var n="[name=".concat(t,"]:checked"),a=document.body.querySelector(n);return null!==a?a.value:""}if("checkbox"===e.getAttribute("type")){var o='[name="'.concat(t,'"]:checked'),i=Array.from(document.body.querySelectorAll(o)),l=[];return i.length>0?(i.forEach(function(e){l.push(e.value)}),l):""}return"file"===e.getAttribute("type")&&e.files.length>0?e.files[0]:r}(t,r);"object"===a(n)&&"file"===t.getAttribute("type")?e.append(r,n):"object"===a(n)?n.forEach(function(t){e.append(r,t)}):e.append(r,n)});var t=new XMLHttpRequest;return t.onreadystatechange=function(){1===t.readyState?(l.setAttribute("disabled","disabled"),d.setAttribute("disabled","disabled")):4===t.readyState&&(l.removeAttribute("disabled"),d.removeAttribute("disabled"),i.classList.remove("dirty"),409===t.status&&alert(t.responseText))},p.push(t),t.open("POST","".concat(o,"/").concat("partialuserform/save"),!0),t.send(e),t},m=function(e,t){var r=t.querySelector("span#".concat(e));if(r)for(;r.firstChild;)r.removeChild(r.lastChild);else(r=document.createElement("span")).setAttribute("id",e),t.appendChild(r);return r.setAttribute("class","right-title"),r},y=function(e){e.preventDefault();var t=e.target,r=new FormData,n=t.parentNode.parentNode,a=t.parentNode.getAttribute("id"),i=t.getAttribute("data-disabled"),l=JSON.parse(t.getAttribute("data-file-remove")),c=m(a,n);if("disabled"===i)return c.appendChild(document.createTextNode("Still in progress...")),void c.setAttribute("class","right-title loading");Object.keys(l).forEach(function(e){r.append(e,l[e])}),t.setAttribute("data-disabled","disabled"),c.appendChild(document.createTextNode("Removing selected file...")),c.setAttribute("class","right-title loading");var u=new XMLHttpRequest;u.onreadystatechange=function(){4===u.readyState&&(409===u.status?alert(u.responseText):(c=m(a,n)).appendChild(document.createTextNode("We accept .png, .jpg and .pdf")))},p.push(u),u.open("POST","".concat(o,"/partialuserform/remove-file"),!0),u.send(r)},h=function(e){var t="".concat(window.location,"/").concat(e.Name);t=t.replace("partial/","partialfiledownload/");var r=e.Name,n=document.body.querySelector("[name=".concat(r,"]")).parentNode.parentNode,a=m("".concat(r,"_right_title"),n);a.appendChild(document.createTextNode("View "));var o=document.createElement("a");o.setAttribute("class","external"),o.setAttribute("rel","external"),o.setAttribute("title","Open external link"),o.setAttribute("href",t),o.setAttribute("target","_blank"),o.appendChild(document.createTextNode(e.Title)),a.appendChild(o),a.appendChild(document.createTextNode("  "));var i=document.createElement("a");i.setAttribute("class","partial-file-remove"),i.setAttribute("href","javascript:;"),i.setAttribute("data-disabled",""),i.setAttribute("data-file-remove",'{"PartialID":'.concat(e.PartialID,',"FileID":').concat(e.FileID,"}")),i.appendChild(document.createTextNode("Remove ✗")),i.addEventListener("click",y),a.appendChild(i)},v=function(e){var t=e.target;if(t.value){var r=t.parentNode,n=b(),a=r.parentNode,o=t.getAttribute("name"),i=m("".concat(o,"_right_title"),a);i.appendChild(document.createTextNode("Uploading selected file...")),i.setAttribute("class","right-title loading"),n.onload=function(){if(201==n.status)for(var e=JSON.parse(n.responseText),t=0;t<e.length;t++)e[t].Name===o&&h(e[t]);i.setAttribute("class","right-title")}}},g=function(e){e.preventDefault();var t=e.target,r=b();r.onload=function(){201==r.status&&(document.location.href=t.getAttribute("href"))}},A=null;window.loadRecaptcha=function(){var e=document.getElementById("g-recaptcha-partial-userform"),t={sitekey:e.dataset.sitekey,size:"invisible",callback:handleAction,"expired-callback":function(){return alert("Expired reCAPTCHA response; you will need to re-verify."),!1}};A=grecaptcha.render(e,t)},window.handleAction=function(){grecaptcha.execute(A),grecaptcha.reset(A)};n(),Array.from||(Array.from=n()),function(){if(l&&l.addEventListener("click",b),c&&c.addEventListener("click",b),u&&u.addEventListener("click",g),s.length)for(var e=0;e<s.length;e++)s[e].addEventListener("click",y);if(f.length)for(var t=0;t<f.length;t++)f[t].addEventListener("change",v)}(),null!==i&&(i._submit=i.submit,i.submit=function(){p.length&&p.forEach(function(e){e.abort()}),i._submit()})}]);