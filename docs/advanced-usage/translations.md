---
title: Dhivehi and translations
sidebar_position: 6
---

Every string the package shows goes through `__()`, and the package ships Dhivehi in `lang/dv.json`. With the app's locale set to `dv`, role pages and messages are in Dhivehi:

```php
app()->setLocale('dv');

__('Role created'); // "ރޯލް އުފެއްދިއްޖެ"
```

# Permission descriptions and labels

A permission's description and its group's label are English strings used as translation keys. Translate them in a JSON file:

```json
// lang/dv.json
{
    "Exports": "އެކްސްޕޯޓްތައް",
    "View exports": "އެކްސްޕޯޓްތައް ބެލުން"
}
```

```php
$permission->translated_description; // "އެކްސްޕޯޓްތައް ބެލުން"
$permission->group_label;            // "އެކްސްޕޯޓްތައް"
$role->translated_description;
```

Packages ship their lines with `loadJsonTranslationsFrom()`. The app's own `lang/dv.json` is loaded after every package's, so any line can be overridden there.

Role descriptions are free text: they can be written in Thaana directly ("ލިޔުންތައް ބަދަލުކުރާ މީހުން"). Role names are identifiers, so they must be ASCII (`editor`).

# Translations to review

These Dhivehi lines are best guesses and should be checked by a native speaker:

| English | Dhivehi | Note |
| --- | --- | --- |
| Super Admin | ސުޕަރ އެޑްމިން | Transliterated |
| Roles | ރޯލްތައް | Transliterated; "ދައުރުތައް" is an alternative |
| Delete roles | ރޯލްތައް ފުހެލުން | "ފުހެލުން" for delete, as in javaabu/forms |
| Role created / updated / deleted | ރޯލް އުފެއްދިއްޖެ / ބަދަލުކުރެވިއްޖެ / ފުހެލެވިއްޖެ | Tone of the flash messages |
