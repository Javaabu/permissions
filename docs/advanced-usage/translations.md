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

Role descriptions are free text: they can be written in Thaana directly ("ލިޔުންތައް ބަދަލުކުރާ މީހުން"), and the description field uses `dir="auto"` so either language reads the right way. To keep a description in several languages, use [translatable role descriptions](translatable-roles.md): the Dhivehi input then gets `dir="rtl"`, `lang="dv"` and the Thaana keyboard. Role names are identifiers, so they must be ASCII (`editor`) and are shown left to right.

# Right to left

The Blade and React pages use logical utilities only (`ms-`, `pe-`, `text-start`, `gap-`), so they flip when the page is `dir="rtl"`. A test fails if a Blade view uses `ml-`, `text-left` and the like. Set the direction in your layout:

```blade
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'dv' ? 'rtl' : 'ltr' }}">
```

The React pages get their text in the `strings` prop, translated on the server, so there's nothing to load in the browser.

# Translations to review

These Dhivehi lines are best guesses and should be checked by a native speaker:

| English | Dhivehi | Note |
| --- | --- | --- |
| Super Admin | ސުޕަރ އެޑްމިން | Transliterated |
| Roles | ރޯލްތައް | Transliterated; "ދައުރުތައް" is an alternative |
| Delete roles | ރޯލްތައް ފުހެލުން | "ފުހެލުން" for delete, as in javaabu/forms |
| Role created / updated / deleted | ރޯލް އުފެއްދިއްޖެ / ބަދަލުކުރެވިއްޖެ / ފުހެލެވިއްޖެ | Tone of the flash messages |
| Details | މަޢުލޫމާތު | Card title on the role pages |
| Guard | ގާޑު | Transliterated; an auth guard such as `web_admin` |
| Letters, numbers, dashes and underscores, e.g. content_editor | އިނގިރޭސި އަކުރު، ނަންބަރު، ޑޭޝް އަދި އަންޑަސްކޯ، މިސާލަކަށް content_editor | Help text of the name field |
| Select none | އެއްވެސް އެއްޗެއް ޚިޔާރު ނުކުރާ | "Select all" is javaabu/forms' ހުރިހާ އެއްޗެއް ޚިޔާރުކުރޭ |
| Delete this role? Users with it lose its permissions. | މި ރޯލް ފުހެލަންވީތޯ؟ މި ރޯލް ލިބިފައިވާ ފަރާތްތަކަށް އޭގެ ހުއްދަތައް ގެއްލޭނެ. | Confirmation |
| No roles match your search. | ހޯދި ގޮތާ ގުޅޭ އެއްވެސް ރޯލެއް ނުފެނުނު. | Empty search |
| :count permissions | :count ހުއްދަ | No plural form in Dhivehi |
| Save / Edit / View | ސޭވްކުރޭ / ބަދަލުކުރޭ / ބަލާ | Button verbs; javaabu/forms and exports use the same forms |
