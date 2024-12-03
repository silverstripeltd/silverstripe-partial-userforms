const parseCSV = (csv) => {
    return csv.split(',').filter(item => !!item).map(item => parseInt(item));
};

const updateOneUntilMax = (csv, maximum) => {
    const values = parseCSV(csv);
    for (let counter = 1; counter <= maximum; counter++) {
        if (values.indexOf(counter) === -1) {
            values.push(counter);
            break;
        }
    }
    return values;
};

const duplicateFields = (event) => {
    event.preventDefault();
    const repeatButton = event.target;
    const hiddenInput = repeatButton.parentNode.querySelector('input[type=hidden]');
    const maximum = repeatButton.getAttribute('data-maximum');
    const values = updateOneUntilMax(hiddenInput.value, maximum);
    hiddenInput.value = values.join(',');
    toggleRepeatedFields(repeatButton);
};

const hideRepeatGroup = (event) => {
    event.preventDefault();
    const button = event.target;
    const buttonContainer = button.parentNode;
    if (button.getAttribute('data-warned') != 1) {
      // If repeated fields has values show alert message
      let hasValues = false;
      const elements = buttonContainer.querySelectorAll('input, select, textarea');
      for (let count = 0; count < elements.length; count++) {
        const currentElement = elements[count];
        if (currentElement.getAttribute('type') == 'hidden') {
          continue;
        }
        if (currentElement.value) {
          hasValues = true;
          break;
        }
      }
      if (hasValues) {
        button.setAttribute('data-warned', 1);
        return window.alert('Before deleting extra field set, make the values are cleared.');
      }
    }
    button.setAttribute('data-warned', 0);

    const mainContainer = buttonContainer.parentNode.parentNode;
    const source = mainContainer.querySelector('.repeat-source');
    const destination = mainContainer.querySelector('.repeat-destination');
    const counter = button.getAttribute('data-counter');
    const groupName = button.getAttribute('data-name');
    const hiddenInput = mainContainer.querySelector(`input[name="${groupName}"]`);
    const addButtonContainer = hiddenInput.parentNode;
    const values = parseCSV(hiddenInput.value).filter(item => item != counter);
    const resetForm = document.createElement('form');
    hiddenInput.value = values.join(',');
    buttonContainer.className = 'repeat-group animation-out';
    setTimeout(() => {
        resetForm.appendChild(buttonContainer);
        resetForm.reset(); //resets input values
        source.appendChild(buttonContainer);
        addButtonContainer.style.display = 'block';
    }, 500);
};

const initialiseRepeatedFields = (repeatButton) => {
    const hiddenData = JSON.parse(repeatButton.getAttribute('data-fields'));
    if (!hiddenData) {
        return;
    }
    const buttonContainer = repeatButton.parentNode;
    const mainContainer = buttonContainer.parentNode;
    const source = mainContainer.querySelector('.repeat-source');
    const destination = mainContainer.querySelector('.repeat-destination');
    const hiddenInput = buttonContainer.querySelector('input[type=hidden]');
    const fieldName = hiddenInput.getAttribute('name');
    const removeBtnLabel = repeatButton.getAttribute('data-remove-label');
    const removeBtnCss = repeatButton.getAttribute('data-remove-css') || '';
    let original;
    Object.keys(hiddenData).forEach(function (index) {
        for (let i = 0; i <= parseInt(hiddenData[index]); i++) {
            const counter = i ? `__${i}` : '';
            const groupId = `repeat-${fieldName}${counter}`;

            let groupContainer = source.querySelector(`#${groupId}`);
            if (!groupContainer) {
                groupContainer = document.createElement('div');
                groupContainer.setAttribute('id', groupId);
                groupContainer.className = 'repeat-group';
                const hr = document.createElement('hr');
                hr.style.clear = 'both';
                groupContainer.appendChild(hr);
                if (i > 0) {
                    let deleteButton = document.createElement('button');
                    deleteButton.className = 'delete-repeat-group ' + removeBtnCss;
                    deleteButton.textContent = removeBtnLabel || '\u00D7';
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

            let clonedField = source.querySelector(`#${index}${counter}`);
            if (clonedField) {
              groupContainer.appendChild(clonedField);
              if (i > 0) {
                let deleteButton = groupContainer.querySelector('.delete-repeat-group');
                deleteButton.insertAdjacentElement('beforebegin', clonedField);
              }
            }
        }
    });
    if (original) {
        destination.appendChild(original);
    }
};

const toggleRepeatedFields = (repeatButton) => {
    const buttonContainer = repeatButton.parentNode;
    const mainContainer = buttonContainer.parentNode;
    const source = mainContainer.querySelector('.repeat-source');
    const destination = mainContainer.querySelector('.repeat-destination');
    const hiddenInput = buttonContainer.querySelector('input[type=hidden]');
    const fieldName = hiddenInput.getAttribute('name');
    const maximum = repeatButton.getAttribute('data-maximum');
    const values = parseCSV(hiddenInput.value);
    values.forEach(counter => {
        let groupContainer = source.querySelector(`#repeat-${fieldName}__${counter}`);
        if (groupContainer) {
            groupContainer.className = 'repeat-group animation-in';
            destination.appendChild(groupContainer);
        }
    });
    buttonContainer.style.display = (values.filter(i => i).length >= maximum) ? 'none' : 'block';
};

window.addEventListener('DOMContentLoaded', (event) => {
    const form = document.body.querySelector('form.userform');
    const repeatButton = form.querySelectorAll('button.btn-add-more');

    if (repeatButton.length) {
        for (let index = 0; index < repeatButton.length; index++) {
            repeatButton[index].addEventListener('click', duplicateFields);
            initialiseRepeatedFields(repeatButton[index]);
            toggleRepeatedFields(repeatButton[index]);
        }
    }
});
