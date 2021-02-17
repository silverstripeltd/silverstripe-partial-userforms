const baseDomain = document.baseURI
    || (window.location.protocol + '//' + window.location.hostname + (window.location.port ? ':' + window.location.port : '' + '/'));
const submitURL = 'partialuserform/save';
const form = document.body.querySelector('form.userform');
const formElements = () => Array.from(form.querySelectorAll('[name]:not([type=submit])'));
const saveButton = form.querySelector('button.step-button-save');
const nextButton = form.querySelector('button.step-button-next');
const shareButton = form.querySelector('a.step-button-share');
const submitButton = form.querySelector('[type=submit]');
const repeatButton = form.querySelectorAll('button.btn-add-more');
const removeFileButton = form.querySelectorAll('a.partial-file-remove');
const uploadButtons = form.querySelectorAll('input[type=file]');
const requests = [];

const getElementValue = (element, fieldName) => {
  const value = element.value;
  if (element.getAttribute('type') === 'select') {
    return element[element.selectedIndex].value;
  }
  if (element.getAttribute('type') === 'radio') {
    const name = `[name=${fieldName}]:checked`;
    const checkedElement = document.body.querySelector(name);
    return checkedElement !== null ? checkedElement.value : "";
  }
  if (element.getAttribute('type') === 'checkbox') {
    const name = `[name="${fieldName}"]:checked`;
    const checkedElements = Array.from(document.body.querySelectorAll(name));
    const valueArray = [];
    if (checkedElements.length > 0) {
      checkedElements.forEach((element) => {
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

const submitPartial = () => {
  // grecaptcha is initialied in src/controllers/PartialUserFormController.php by adding https://www.google.com/recaptcha/api.js
  // This will trigger recaptcha validation if it's enabled in CMS
  if (typeof grecaptcha !== 'undefined') {
    grecaptcha.execute(recaptchaId);
  }

  const data = new FormData();
  formElements().forEach(element => {
    const fieldName = element.getAttribute('name');
    const value = getElementValue(element, fieldName);
      if (typeof value === 'object' && element.getAttribute('type') === 'file') {
        data.append(fieldName, value);
      } else if (typeof value === 'object') {
        value.forEach((arrayValue) => {
          data.append(fieldName, arrayValue);
        });
      } else {
        data.append(fieldName, value);
      }
  });

  /** global: XMLHttpRequest */
  const httpRequest = new XMLHttpRequest();
  httpRequest.onreadystatechange = () => {
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
  httpRequest.open('POST', `${baseDomain}${submitURL}`, true);
  httpRequest.send(data);

  return httpRequest;
};

const replaceExistingAttribute = (dom, attr, previous, latest) => {
  var matches = dom.querySelectorAll(`[${attr}*=${previous}`);
  matches.forEach( function (item) {
    var oldValue = item.getAttribute(attr);
    var newValue = oldValue.replace(new RegExp(previous), latest);
    item.setAttribute(attr, newValue);
  });
};

const createUploadLinksHolder = (name, elementHolder) => {
  let span = elementHolder.querySelector(`span#${name}`);
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

const removePartialFile = (event) => {
  event.preventDefault();

  const link = event.target;
  const form = new FormData();
  const holder = link.parentNode.parentNode;
  const name = link.parentNode.getAttribute('id');
  const disabled = link.getAttribute('data-disabled');
  const linkData = JSON.parse(link.getAttribute('data-file-remove'));
  let span = createUploadLinksHolder(name, holder);

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
  const httpRequest = new XMLHttpRequest();
  httpRequest.onreadystatechange = () => {
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
  httpRequest.open('POST', `${baseDomain}partialuserform/remove-file`, true);
  httpRequest.send(form);
};

const createUploadLinks = (record) => {
  const name = record['Name'];
  const field = document.body.querySelector(`[name=${name}]`);
  const holder = field.parentNode.parentNode;
  let span = createUploadLinksHolder(`${name}_right_title`, holder);

  span.appendChild(document.createTextNode('View '));

  const viewLink = document.createElement('a');
  viewLink.setAttribute('class', 'external');
  viewLink.setAttribute('rel', 'external');
  viewLink.setAttribute('title', 'Open external link');
  viewLink.setAttribute('href', record['Link']);
  viewLink.setAttribute('target', '_blank');
  viewLink.appendChild(document.createTextNode(record['Title']));
  span.appendChild(viewLink);
  span.appendChild(document.createTextNode(" \u00A0"));

  const removeLink = document.createElement('a');
  removeLink.setAttribute('class', 'partial-file-remove');
  removeLink.setAttribute('href', 'javascript:;');
  removeLink.setAttribute('data-disabled', '');
  removeLink.setAttribute('data-file-remove', `{"PartialID":${record['PartialID']},"FileID":${record['FileID']}}`);
  removeLink.appendChild(document.createTextNode('Remove ✗'));
  removeLink.addEventListener('click', removePartialFile);
  span.appendChild(removeLink);
};

const uploadPartialHandler = (event) => {
  const uploadField = event.target;
  if (!uploadField.value) {
    return;
  }
  const parentHolder = uploadField.parentNode;
  const httpRequest = submitPartial();
  const holder = parentHolder.parentNode;
  const name = uploadField.getAttribute('name');
  let span = createUploadLinksHolder(`${name}_right_title`, holder);

  span.appendChild(document.createTextNode('Uploading selected file...'));
  span.setAttribute('class', 'right-title loading');

  httpRequest.onload = () => {
    if (httpRequest.status == 201) {
      const records = JSON.parse(httpRequest.responseText);
      for (let index = 0; index < records.length; index++) {
        if (records[index]['Name'] === name) {
          createUploadLinks(records[index]);
        }
      }
    }

    span.setAttribute('class', 'right-title');
  }
};

const sharePartial = (event) => {
  event.preventDefault();
  const linkTag = event.target;
  const httpRequest = submitPartial();

  httpRequest.onload = () => {
    if (httpRequest.status == 201) {
      document.location.href = linkTag.getAttribute('href');
    }
  }
};

const attachSavePartial = () => {
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
    for (let index = 0; index < removeFileButton.length; index++) {
      removeFileButton[index].addEventListener('click', removePartialFile);
    }
  }

  if (uploadButtons.length) {
    for (let index = 0; index < uploadButtons.length; index++) {
      uploadButtons[index].addEventListener('change', uploadPartialHandler);
    }
  }
};

const abortPendingSubmissions = () => {
  // Clear all pending partial submissions on submit
  if (form !== null) {
    form._submit = form.submit; // Save reference
    form.submit = () => {
      // Abort all requests
      if (requests.length) {
        requests.forEach(xhr => {
          xhr.abort();
        });
      }
      form._submit();
    };
  }
};

export default function () {
  attachSavePartial();
  abortPendingSubmissions();
}

let recaptchaId = null;
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
    'expired-callback': function () {
      alert('Expired reCAPTCHA response; you will need to re-verify.');
      return false;
    },
  };
  recaptchaId = grecaptcha.render(captchaElement, captchaOptions);
};

// reCaptcha callback - Do the main processing here
window.handleAction = function () {
  // if there is no previous reponse then execute recaptcha else do nothing (As we are not processing any form data here, it's done in submitPartial function)
  grecaptcha.execute(recaptchaId);
  // Once recaptcha is validated reset recaptcha token for next action captcha validation
  grecaptcha.reset(recaptchaId);
};
