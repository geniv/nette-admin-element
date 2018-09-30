Admin element
=============

Installation
------------

```sh
$ composer require geniv/nette-admin-element
```
or
```json
"geniv/nette-admin-element": ">=1.0.0"
```

require:
```json
"php": ">=7.0.0",
"nette/nette": ">=2.4.0",
"dibi/dibi": ">=3.0.0",
"geniv/nette-thumbnail": ">=1.0.0"
```

Include in application
----------------------

neon configure:
```neon
# admin element
adminElement:
    elements:
        label: AdminElement\Elements\LabelElement
        hidden: AdminElement\Elements\HiddenElement
        text: AdminElement\Elements\TextElement
        textarea: AdminElement\Elements\TextAreaElement
        editor: AdminElement\Elements\EditorElement
        password: AdminElement\Elements\PasswordElement
        date: AdminElement\Elements\DateElement
        datepicker: AdminElement\Elements\DatePickerElement
        time: AdminElement\Elements\TimeElement
        timepicker: AdminElement\Elements\TimePickerElement
        datetime: AdminElement\Elements\DateTimeElement
        datetimepicker: AdminElement\Elements\DateTimePickerElement
        checkbox: AdminElement\Elements\CheckboxElement
        position: AdminElement\Elements\PositionElement
        upload: AdminElement\Elements\UploadElement
        uploadimage: AdminElement\Elements\UploadImageElement
        radio: AdminElement\Elements\RadioElement
        select: AdminElement\Elements\SelectElement
        fkselect: AdminElement\Elements\FkSelectElement
        foreignfkpk: AdminElement\Elements\ForeignFkPkElement
        foreignfkwhere: AdminElement\Elements\ForeignFkWhereElement
        foreignlabel: AdminElement\Elements\ForeignLabelElement
        foreignhidden: AdminElement\Elements\ForeignHiddenElement
        foreigntext: AdminElement\Elements\ForeignTextElement
        foreigntextarea: AdminElement\Elements\ForeignTextAreaElement
        foreigneditor: AdminElement\Elements\ForeignEditorElement
        foreignselect: AdminElement\Elements\ForeignSelectElement
        foreignupload: AdminElement\Elements\ForeignUploadElement
        foreignuploadimage: AdminElement\Elements\ForeignUploadImageElement
        foreigndatetime: AdminElement\Elements\ForeignDateTimeElement
        foreigndatetimepicker: AdminElement\Elements\ForeignDateTimePickerElement
        foreigncheckbox: AdminElement\Elements\ForeignCheckboxElement
        foreignposition: AdminElement\Elements\ForeignPositionElement
```

neon configure extension:
```neon
extensions:
    adminElement: AdminElement\Bridges\Nette\Extension
```

presenters - startup:
```php
$this->template->formRendererPath = WrapperSection::getFormRendererPath();
```

presenters - grid table component:
```php
$visualPaginator->setTemplatePath(__DIR__ . '/templates/visualPaginator.latte');
$gridTable->setSortable((bool) $this->getParameter('sortable'));
$gridTable->setVisualPaginator($visualPaginator);
$gridTable->setItemPerPage($this->wrapperSection->getDatabaseLimit());

$gridTable->setTemplatePath(__DIR__ . '/templates/gridTable.latte');
$gridTable->setSource($this->wrapperSection->getSource());
$pk = $this->wrapperSection->getDatabasePk();
$gridTable->setPrimaryKey($pk);
$gridTable->setDefaultOrder($this->wrapperSection->getDatabaseOrderDefault());

$elements = $this->wrapperSection->getElements();

$gridTable->addColumn($pk, '#');

$items = $this->wrapperSection->getItemsByShow(WrapperSection::ACTION_LIST);
foreach ($items as $idItem => $configure) {
    $elem = $elements[$idItem]; // load element
    $column = $gridTable->addColumn($idItem, $elem->getTranslateNameContent());
    $column->setOrdering($configure['ordering']);
    $column->setData($configure);

    $column->setCallback(function ($data) use ($elem) { return $elem->getRenderRow($data); });
    switch ($configure['type']) {
        case 'checkbox':
        case 'foreigncheckbox':
            $column->setTemplatePath(__DIR__ . '/templates/gridTableCheckbox.latte');
            break;

        case  'foreignfkwhere':
            $column->setTemplatePath(__DIR__ . '/templates/gridTableForeignFkWhere.latte');
            break;
    }
}
```

presenters - action default:
```php
$this->wrapperSection->getById($idSection, WrapperSection::ACTION_LIST);

$this->template->sectionName = $this->wrapperSection->getSectionName();
$this->template->subSectionName = $this->wrapperSection->getSubsectionName($idSubSection);

$this->template->idSubSection = $this->idSubSection = $idSubSection;
if ($idSubSection) {
    $this->wrapperSection->setSubSectionId($idSubSection);
}

$this->template->page = $page;
$this->template->sortable = $sortable;
$this->template->isSortable = $this->wrapperSection->isSortableConfigure();
$this['gridTable']->setPage((int) $page);
```

presenters - action detail:
```php
$this->wrapperSection->getById($idSection, WrapperSection::ACTION_DETAIL);

$this->template->sectionId = $id;
$this->template->sectionName = $this->wrapperSection->getSectionName();
$this->template->subSectionName = $this->wrapperSection->getSubsectionName($idSubSection);

$this->template->detail = $this->wrapperSection->getDetailContainerContent($id);

$this->template->idSubSection = $this->idSubSection = $idSubSection;
if ($idSubSection) {
    $this->wrapperSection->setSubSectionId($idSubSection);
}

$this->template->page = $page;
```

presenters - add edit component:
```php
$form = new Form($this, $name);
$form->setTranslator($this->translator);
$this->wrapperSection->getFormContainerContent($form);
// internal add id element
$form->addHidden('id');
$form->addSubmit('send', 'content-form-' . $this->action . '-send');
$form->addSubmit('ajaxSend', 'content-form-' . $this->action . '-ajax-send')
    ->setOption('class', 'ajax-send hidden');
```

presenters - action add:
```php
$this->wrapperSection->getById($idSection, WrapperSection::ACTION_ADD);

$this->template->sectionName = $this->wrapperSection->getSectionName();
$this->template->subSectionName = $this->wrapperSection->getSubsectionName($idSubSection);

$this->template->idSubSection = $this->idSubSection = $idSubSection;
if ($idSubSection) {
    $this->wrapperSection->setSubSectionId($idSubSection);
}

$this->template->page = $page;
$this->template->listItems = $this->wrapperSection->getItems();

// define form success
$this->setOnSuccessAdd();
```

presenters - callback add:
```php
// define form success
$this['formAddEdit']->onSuccess[] = function (Form $form, array $values) {
    try {
        $result = $this->wrapperSection->onSuccessInsert($values);
        if ($result > 0) {
            $this->flashMessage($this->translator->translate('content-form-add-onsuccess', [$result]), 'success');
        } else {
            $this->flashMessage($this->translator->translate('content-form-add-onsuccess-fail', [$result]), 'danger');
        }
        $this->redirect('default', [$this->getParameter('idSection'), $this->wrapperSection->getSubSectionId(), $this->getParameter('page')]);
    } catch (Exception $e) {
        $this->flashMessage($e->getMessage(), 'danger');
        $this->redirect('this');
    }
};
```

presenters - action edit:
```php
$this->wrapperSection->getById($idSection, WrapperSection::ACTION_EDIT);

$this->template->sectionId = $id;
$this->template->sectionName = $this->wrapperSection->getSectionName();
$this->template->subSectionName = $this->wrapperSection->getSubsectionName($idSubSection);

$this->template->idSubSection = $this->idSubSection = $idSubSection;
if ($idSubSection) {
    $this->wrapperSection->setSubSectionId($idSubSection);
}

$this->template->page = $page;
$this->template->listItems = $this->wrapperSection->getItems();

// define form success
$this->setOnSuccessEdit();
```

presenters - callback edit:
```php
$pk = $this->wrapperSection->getDatabasePk();

$result = $this->wrapperSection->onSuccessUpdate($values);

// load #id value
$id = $values[$pk];
if ($result > 0) {  // change values
    $this->flashMessage($this->translator->translate('content-form-edit-onsuccess', [$id]), 'success');
} else if ($result === 0) { // no change values
    $this->flashMessage($this->translator->translate('content-form-edit-onsuccess-no-change', [$id]), 'info');
} else {
    $this->flashMessage($this->translator->translate('content-form-edit-onsuccess-fail', [$id]), 'danger');
}

// redirect only for send button
if ($form['send']->isSubmittedBy()) {
    $this->redirect('default', [$this->getParameter('idSection'), $this->wrapperSection->getSubSectionId(), $this->getParameter('page')]);
}
```

presenters - action delete:
```php
$this->wrapperSection->getById($idSection, WrapperSection::ACTION_DELETE);

if ($idSubSection) {
    $this->wrapperSection->setSubSectionId($idSubSection);
}

$result = $this->wrapperSection->onSuccessDelete($id);
if ($result > 0) {
    $this->flashMessage($this->translator->translate('content-form-delete-onsuccess', [$id]), 'success');
} else if ($result === 0) { // no change values
    $this->flashMessage($this->translator->translate('content-form-delete-onsuccess-no-change', [$id]), 'info');
} else {
    $this->flashMessage($this->translator->translate('content-form-delete-onsuccess-fail', [$id]), 'danger');
}
$this->redirect('default', [$this->getParameter('idSection'), $this->wrapperSection->getSubSectionId(), $page]);
```

presenters - handle sortable:
```php
if ($this->isAjax()) {
    if ($this->wrapperSection->saveSortablePosition($values)) {
        $this->flashMessage($this->translator->translate('content-form-sortable-onsuccess'), 'success');
    }
    $this->redirect('default', [$this->getParameter('idSection'), $this->wrapperSection->getSubSectionId()]);
}
```

presenters - action add - foreign:
```php
$this->wrapperSection->getById($idSection, WrapperSection::ACTION_ADD);

$this->template->sectionName = $this->wrapperSection->getSectionName();
$this->template->subSectionName = $this->wrapperSection->getSubsectionName($idSubSection);

$this->template->idSubSection = $this->idSubSection = $idSubSection;
if ($idSubSection) {
    $this->wrapperSection->setSubSectionId($idSubSection);
}

$this->template->page = $page;
$this->template->listItems = $this->wrapperSection->getItems();

// define form success
$this->setOnSuccessAdd();

// remove ajaxSend submit button for ADD
unset($this['formAddEdit']['ajaxSend']);

// set FkId
$this->wrapperSection->setFkId($fkId);
```

presenters - action edit - foreign:
```php
$this->wrapperSection->getById($idSection, WrapperSection::ACTION_EDIT);

$this->template->sectionId = $id;
$this->template->sectionName = $this->wrapperSection->getSectionName();
$this->template->subSectionName = $this->wrapperSection->getSubsectionName($idSubSection);

$this->template->idSubSection = $this->idSubSection = $idSubSection;
if ($idSubSection) {
    $this->wrapperSection->setSubSectionId($idSubSection);
}

$this->template->page = $page;
$this->template->listItems = $this->wrapperSection->getItems();

// define form success
$this->setOnSuccessEdit();

// set FkId
$this->wrapperSection->setFkId($fkId);
$this['switchFkId']->addVariableTemplate('hasLocale', $this->wrapperSection->getMByIdN($id));

//TODO ACL block for user leve?!?! - musi umet zobrazovat jen data pro danou ACL roli!

$defaults = $this->wrapperSection->setDefaults($this->wrapperSection->getDataById($id));
```

presenters - handle delete switch fkId:
```php
if ($this->wrapperSection->deleteForeignSection($id, $fkIdDelete)) {
    $this->flashMessage($this->translator->translate('content-foreign-delete-switch-fkid-onsuccess', [$fkIdDelete]), 'success');
}
$this->redirect('this', $this->getParameter('idSection'));
```

presenters - switch fkId component:
```php
$htmlSelect->setTemplatePath(__DIR__ . '/templates/ContentForeign/switchFkId.latte');
$htmlSelect->setRoute('SwitchFkId!');
$htmlSelect->setItems($this->wrapperSection->getForeignItems());
$htmlSelect->setActiveValue($this->wrapperSection->getFkId());
```
