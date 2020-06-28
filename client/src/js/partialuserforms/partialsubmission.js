const baseDomain = document.baseURI;
const submitURL = 'partialuserform/save';
const form = document.body.querySelector('form.userform');
const formElements = () => Array.from(form.querySelectorAll('[name]:not([type=submit])'));
const saveButton = form.querySelector('button.step-button-save');
const nextButton = form.querySelector('button.step-button-next');
const shareButton = form.querySelector('a.step-button-share');
const submitButton = form.querySelector('[type=submit]');
const repeatButton = form.querySelectorAll('button.btn-add-more');
const removeFileButton = form.querySelectorAll('a.partial-file-remove');
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
  const data = new FormData();
  formElements().forEach(element => {
    const fieldName = element.getAttribute('name');
    const value = getElementValue(element, fieldName);
    if (!data.has(fieldName)) {
      if (typeof value === 'object' && element.getAttribute('type') === 'file') {
        data.append(fieldName, value);
      } else if (typeof value === 'object') {
        value.forEach((arrayValue) => {
          data.append(fieldName, arrayValue);
        });
      } else {
        data.append(fieldName, value);
      }
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
};

const replaceExistingAttribute = (dom, attr, previous, latest) => {
  var matches = dom.querySelectorAll(`[${attr}*=${previous}`);
  matches.forEach( function (item) {
    var oldValue = item.getAttribute(attr);
    var newValue = oldValue.replace(new RegExp(previous), latest);
    item.setAttribute(attr, newValue);
  });
};

const removePartialFile = (event) => {
  event.preventDefault();

  const link = event.target;
  const form = new FormData();
  const linkData = JSON.parse(link.getAttribute('data-file-remove'));
  let disabled = link.getAttribute('data-disabled');
  Object.keys(linkData).forEach(function (name) {
    form.append(name, linkData[name]);
  });

  if (disabled === 'disabled') {
    console.log('button disabled');
    return;
  }

  link.setAttribute('data-disabled', 'disabled');

  /** global: XMLHttpRequest */
  const httpRequest = new XMLHttpRequest();
  httpRequest.onreadystatechange = () => {
    if (httpRequest.readyState === 4) {
      if (httpRequest.status === 409) {
        alert(httpRequest.responseText);
      } else {
        link.parentNode.innerHTML = '';
      }
    }
  };

  requests.push(httpRequest);
  httpRequest.open('POST', `${baseDomain}partialuserform/remove-file`, true);
  httpRequest.send(form);
};

const attachSavePartial = () => {
  if (saveButton) {
    saveButton.addEventListener('click', submitPartial);
  }
  if (nextButton) {
    nextButton.addEventListener('click', submitPartial);
  }
  if (shareButton) {
    shareButton.addEventListener('click', submitPartial);
  }
  if (removeFileButton.length) {
    for (let index = 0; index < removeFileButton.length; index++) {
      removeFileButton[index].addEventListener('click', removePartialFile);
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
