const baseDomain = document.baseURI;
const submitURL = 'partialuserform/save';
const form = document.body.querySelector('form.userform');
const formElements = () => Array.from(form.querySelectorAll('[name]:not([type=submit])'));
const saveButton = form.querySelector('button.step-button-save');
const nextButton = form.querySelector('button.step-button-next');
const shareButton = form.querySelector('a.step-button-share');
const submitButton = form.querySelector('[type=submit]');
const repeatButton = form.querySelector('button.btn-add-more');
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

  // Pass partial params if available
  const partialID = form.querySelector('[name=PartialID]');
  if (partialID) {
    data.append('PartialID', partialID.value);
  }

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

const duplicateFields = (event) => {
  event.preventDefault();
  const repeatButton = event.target;
  const hiddenInput = repeatButton.parentNode.querySelector('input[type=hidden]');
  hiddenInput.value = parseInt(hiddenInput.value) + 1;
  toggleRepeatedFields(repeatButton);
};

const toggleRepeatedFields = (repeatButton) => {
  const buttonContainer = repeatButton.parentNode;
  const mainContainer = buttonContainer.parentNode;
  const destination = mainContainer.querySelector('.repeat-destination');
  const hiddenInput = buttonContainer.querySelector('input[type=hidden]');
  const hiddenData = JSON.parse(repeatButton.getAttribute('data'));
  Object.keys(hiddenData).forEach(function (index) {
    for (var i = 1; i <= parseInt(hiddenData[index]); i++) {
      var fieldName = '#' + index + '__' + i;
      var clonedField = destination.querySelector(fieldName);
      if (i <= parseInt(hiddenInput.value)) {
        clonedField.style.display = 'block';
      } else {
        clonedField.style.display = 'none';
      }
      if (parseInt(hiddenInput.value) >= parseInt(hiddenData[index])) {
        buttonContainer.style.display = 'none';
      }
    }
  });
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
  if (repeatButton) {
    repeatButton.addEventListener('click', duplicateFields);
    toggleRepeatedFields(repeatButton);
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
