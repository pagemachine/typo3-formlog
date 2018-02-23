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

By default the form log module displays a few basic fields like page, form identifier, language and date of form submission as columns.

Additional columns can be added with the `list.columns` setting in `ext_typoscript_setup.txt`:

```
module.tx_formlog {
  settings {
    list {
      columns {
        100 {
          property = data.email
          label = LLL:EXT:my_site/Resources/Private/Language/Extensions/Form/locallang.xlf:element.email.properties.label
        }
      }
    }
  }
}
```

Within `list.columns` an arbitrary list of columns can be added where The `property` option refers to a property path in the `FormLogEntry` domain module. Simply speaking `data.*` provides access to form data by a form element identifier, e.g. `data.email` for the value of the form element `email`. The `label` option is used to retrieve a translatable label. Usually one can simply use the same label that is used for the field within the form itself.

Similarly `finisherVariables.*` does the same for additional finisher variables by utilizing the finisher identifier and variable name:

```
module.tx_formlog {
  settings {
    list {
      columns {
        200 {
          name = finisherVariables.MyCustomFinisher.myCustomVariable
          label = LLL:EXT:my_site/Resources/Private/Language/Extensions/Formlog/locallang.xlf:formlog.entry.finisherVariables.MyCustomFinisher.myCustomVariable
        }
      }
    }
  }
}
```

Here `myCustomVariable` of `MyCustomFinisher` is added as column to the list.

## Form log export

Out of the box form log entries can be exported to CSV and Excel (XLSX). Basic fields of form log entries are exported by default, additional columns can be added with the `export.columns` setting in `ext_typoscript_setup.txt` which is configured exactly the same as the `list.columns` setting:

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
        200 {
          property = finisherVariables.MyCustomFinisher.myCustomVariable
          label = LLL:EXT:my_site/Resources/Private/Language/Extensions/Formlog/locallang.xlf:formlog.entry.finisherVariables.MyCustomFinisher.myCustomVariable
        }
      }
    }
  }
}
```
