# Form log ![CI](https://github.com/pagemachine/typo3-formlog/workflows/CI/badge.svg)

Form log for TYPO3

## Installation

This extension is installable from various sources:

1. Via [Composer](https://packagist.org/packages/pagemachine/typo3-formlog):

        composer require pagemachine/typo3-formlog

2. From the [TYPO3 Extension Repository](https://extensions.typo3.org/extension/formlog/)

## Purpose

One feature absent in the [TYPO3 form extension](https://docs.typo3.org/typo3cms/extensions/form/) is logging of form data. If you have ever used the now defunct Formhandler extension you know how valuable this can be. You can easily check if form submissions are working as they should, see internal values are generated correctly and perform some basic analysis thanks to exports to CSV or similar.

See our blog post [about the TYPO3 form log extension](https://www.pagemachine.de/blog/typo3-form-log/).

## Start logging

Logging can be enabled for any form by adding the `LogFormData` finisher to its form definition:

```
finishers:
  - ...

  - identifier: LogFormData

  - identifier: Redirect
```

The `LogFormData` finisher should be the last finisher or right before the `Redirect` finisher if used. Logging after a redirect is not possible.

## Configuration

### Save values from hidden or composite Elements

Some form elements, such as fieldsets or other containers, do not hold a form value by themselves; only their child elements hold values. By default, all form elements are saved, even those that never contain values, which can result in empty columns in the log records and exports.

To prevent this, set the finisher option `includeHiddenElements` to `false`. This option ensures that values from composite elements are not saved, similar to how the `EmailFinisher` only includes fields in emails that actually contain a value.

Additionally, you can use the finisher option `skipElementsTypes` to exclude specific form elements (comma separated list). By default, this option excludes `ContentElement` and `StaticText` elements, as they will never have a value.

### Logging of finisher variables

Additional variables stored in the `FinisherVariableProvider` can also be logged by using the `finisherVariables` option:

```
  - identifier: LogFormData
    options:
      finisherVariables:
        MyCustomFinisher:
          - myCustomVariable
```

The keys within `finisherVariables` are identifiers of finishers used in a form, the value of each entry is a list of finisher variable names. In the example here the finisher variable `myCustomVariable` provided by the finisher `MyCustomFinisher` will be logged.

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

Within `list.columns` an arbitrary list of columns can be added where the `property` option refers to a property path in the `FormLogEntry` domain model. Simply speaking `data.*` provides access to form data by a form element identifier, e.g. `data.email` for the value of the form element `email`. The `label` option is used to retrieve a translatable label. Usually one can simply use the same label that is used for the field within the form itself.

Similarly `finisherVariables.*` does the same for additional finisher variables by utilizing the finisher identifier and variable name:

```
module.tx_formlog {
  settings {
    list {
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

## Log entry cleanup

The _Table garbage collection_ Scheduler task can be set up to automatically delete old form log entries. Select `tx_formlog_entries` as _Table to clean up_ and a suitable value for _Delete entries older than given number of days_, 180 by default.

## Testing

All tests can be executed with the shipped Docker Compose definition:

    docker compose run --rm app composer build

## Thanks

Development of this package was proudly sponsored by [TÜV Hessen](https://www.tuev-hessen.de/).
