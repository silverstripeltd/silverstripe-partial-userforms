<?php

namespace Firesphere\PartialUserforms\Extensions;

use InvalidArgumentException;
use SilverStripe\Forms\FormField;
use SilverStripe\UserForms\Model\EditableFormField;
use SilverStripe\UserForms\Form\UserFormsRequiredFieldsValidator;

/**
 * This is exact copy of vendor/silverstripe/userforms/code/Form/UserFormsRequiredFieldsValidator.php
 * What's changed:
 * https://github.com/silverstripe/silverstripe-userforms/blob/6.2.6/code/Form/UserFormsRequiredFieldsValidator.php#L113-L117
 * the above lines are replace by following single line code
 * $error = (count($value ?? [])) ? false : true;
 *
 * This change has been added to fix the bug https://mbiessd.atlassian.net/browse/MWP-735
 * or as described in https://github.com/silverstripeltd/silverstripe-partial-userforms/pull/8
 */
class UserFormsRequiredFieldsExtension extends UserFormsRequiredFieldsValidator
{
    /**
     * Allows validation of fields via specification of a php function for
     * validation which is executed after the form is submitted.
     *
     * @param array $data
     *
     * @return bool
     */
    public function php($data)
    {
        $valid = true;
        $fields = $this->form->Fields();

        foreach ($fields as $field) {
            $valid = ($field->validate($this) && $valid);
        }

        if (empty($this->required)) {
            return $valid;
        }

        foreach ($this->required as $fieldName) {
            if (!$fieldName) {
                continue;
            }

            // get form field
            if ($fieldName instanceof FormField) {
                $formField = $fieldName;
                $fieldName = $fieldName->getName();
            } else {
                $formField = $fields->dataFieldByName($fieldName);
            }

            // get editable form field - owns display rules for field
            $editableFormField = $this->getEditableFormFieldByName($fieldName);

            // Validate if the field is displayed
            $error =
                $editableFormField->isDisplayed($data) &&
                $this->validateRequired($formField, $data);

            // handle error case
            if ($formField && $error) {
                $this->handleError($formField, $fieldName);
                $valid = false;
            }
        }

        return $valid;
    }

    /**
     * Retrieve an Editable Form field by its name.
     * @param string $name
     * @return EditableFormField
     */
    private function getEditableFormFieldByName($name)
    {
        $field = EditableFormField::get()->filter(['Name' => $name])->first();

        if ($field) {
            return $field;
        }

        // This should happen if form field data got corrupted
        throw new InvalidArgumentException(sprintf(
            'Could not find EditableFormField with name `%s`',
            $name
        ));
    }

    /**
     * Check if the validation rules for the specified field are met by the provided data.
     *
     * @note Logic replicated from php() method of parent class `SilverStripe\Forms\RequiredFields`
     * @param EditableFormField $field
     * @param array $data
     * @return bool
     */
    private function validateRequired(FormField $field, array $data)
    {
        $error = false;
        $fieldName = $field->getName();
        // submitted data for file upload fields come back as an array
        $value = isset($data[$fieldName]) ? $data[$fieldName] : null;

        if (is_array($value)) {
            $error = (count($value ?? [])) ? false : true;
        } else {
            // assume a string or integer
            $error = (strlen($value ?? '')) ? false : true;
        }

        return $error;
    }

    /**
     * Register an error for the provided field.
     * @param FormField $formField
     * @param string $fieldName
     * @return void
     */
    private function handleError(FormField $formField, $fieldName)
    {
        $errorMessage = _t(
            'SilverStripe\\Forms\\Form.FIELDISREQUIRED',
            '{name} is required',
            [
                'name' => strip_tags(
                    '"' . ($formField->Title() ? $formField->Title() : $fieldName) . '"'
                )
            ]
        );

        if ($msg = $formField->getCustomValidationMessage()) {
            $errorMessage = $msg;
        }

        $this->validationError($fieldName, $errorMessage, "required");
    }
}
