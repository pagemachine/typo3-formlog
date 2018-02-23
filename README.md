# Form log [![Build Status](https://travis-ci.org/pagemachine/typo3-formlog.svg)](https://travis-ci.org/pagemachine/typo3-formlog) [![Latest Stable Version](https://poser.pugx.org/pagemachine/typo3-formlog/v/stable)](https://packagist.org/packages/pagemachine/typo3-formlog) [![Total Downloads](https://poser.pugx.org/pagemachine/typo3-formlog/downloads)](https://packagist.org/packages/pagemachine/typo3-formlog) [![Latest Unstable Version](https://poser.pugx.org/pagemachine/typo3-formlog/v/unstable)](https://packagist.org/packages/pagemachine/typo3-formlog) [![License](https://poser.pugx.org/pagemachine/typo3-formlog/license)](https://packagist.org/packages/pagemachine/typo3-formlog)

Form log for TYPO3

## Installation

This extension is installable from various sources:

1. Via [Composer](https://packagist.org/packages/pagemachine/typo3-formlog):

        composer require pagemachine/typo3-formlog

2. From the [TYPO3 Extension Repository](https://extensions.typo3.org/extension/formlog/)

## Start logging

Logging can be enabled for any form by adding the `LogFormData` finisher to its form definition:

```
finishers:
  - ...

  - identifier: LogFormData

  - identifier: Redirect
```

The `LogFormData` finisher should be the last finisher or right before the `Redirect` finisher if used. Logging after a redirect is not possible.

Additional variables stored in the `FinisherVariableProvider` can also be logged by using the `finisherVariables` option:

```
  - identifier: LogFormData
    options:
      finisherVariables:
        MyCustomFinisher:
          - myCustomVariable
```

The keys within `finisherVariables` are identifiers of finishers used in a form, the value of each entry is a list of finisher variable names.

## Form log module

By default the form log module displays a few basic fields like page, form identifier, language and date of form submission.

Additional columns can be added with the `dataFields` option in `ext_typoscript_setup.txt`:

```
module.tx_formlog {
  settings {
    dataFields {
      1 {
        name = email
        label = LLL:EXT:my_site/Resources/Private/Language/Extensions/Form/locallang.xlf:element.email.properties.label
      }
    }
  }
}
```

Within `dataFields` an arbitrary list of fields can be added where the `name` refers to the identifier of a form element and `label` is used to retrieve a translatable label. Usually one can simply use the same label that is used for the field within the form itself.

The option `finisherVariables` is similar and can be used to add finisher variables as additional columns:

```
module.tx_formlog {
  settings {
    finisherVariables {
      1 {
        name = myCustomVariable
        label = LLL:EXT:my_site/Resources/Private/Language/Extensions/Formlog/locallang.xlf:formlog.entry.finisherVariables.MyCustomFinisher.myCustomVariable
      }
    }
  }
}
```

## Form log export

Out of the box form log entries can be exported to CSV and Excel (XLSX). Basic fields of form log entries are exported by default, additional columns can be added with the `export` option in `ext_typoscript_setup.txt`:

```
module.tx_formlog {
  settings {
    export {
      columns {
        100 {
          property = data.firstname
          label = LLL:EXT:my_site/Resources/Private/Language/Extensions/Form/locallang.xlf:element.firstname.properties.label
        }
        101 {
          property = data.lastname
          label = LLL:EXT:my_site/Resources/Private/Language/Extensions/Form/locallang.xlf:element.lastname.properties.label
        }
        102 {
          property = data.email
          label = LLL:EXT:my_site/Resources/Private/Language/Extensions/Form/locallang.xlf:element.email.properties.label
        }
      }
    }
  }
}
```

The `property` refers to a property path in the `FormLogEntry` domain module. Simply speaking `data.*` provides access to form data by a form element identifier, e.g. `data.email` for the value of the form element `email`.

Similarly `finisherVariables.*` does the same for additional finisher variables by utilizing the finisher identifier and variable name:

```
module.tx_formlog {
  settings {
    export {
      columns {
        200 {
          property = finisherVariables.MyCustomFinisher.myCustomVariable
          label = LLL:EXT:my_site/Resources/Private/Language/Extensions/Formlog/locallang.xlf:formlog.entry.finisherVariables.MyCustomFinisher.myCustomVariable
        }
      }
    }
  }
}
```

Here `myCustomVariable` of `MyCustomFinisher` is added as column to the export.
