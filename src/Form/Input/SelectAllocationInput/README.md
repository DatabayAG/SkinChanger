# SelectAllocationInput

An input for ilias forms.

Allows allocating a key to a value using select inputs.

## Example

![Example](https://i.imgur.com/jAYC7WT.png)

## How to use/setup

````php
<?php
$selectAllocationInput = new ilSelectAllocationInput(
    $plugin,
    "Title",
    "postvar",
);

$selectAllocationInput
    ->setKeyOptions([
        "first_key" => "Banana", 
        "second_key" => "Apple"
    ])
    ->setValueOptions([
        "first_value" => "Juice",
        "second_value" => "Shake",
        "third_value" => "Something else"
    ])
    ->setTableHeaders(
        "Fruit",
        "Preparation",
        "Action"
    )
    ->setRequired(true)
    ->setInfo("Im the info text");
````

This input can then be added to your form using.

````php
$form->addItem($selectAllocationInput);
````

With **setOptions()** you can predefine rows of key value allocations.  
This is **OPTIONAL** as more rows can be added later using buttons.  
If **setOptions()** is not used an inital row will be added with the first select options selected.

````php
$selectAllocationInput->setOptions(
    [
        "first_key" => "second_value",
        "second_key" => "first_value",
    ]
);
````

## Post data returned

![Returned POST Data](https://i.imgur.com/Zby2xpy.png)

## Convert post data into usable key => value pairs.

````php
$selectAllocationInput = $form->getItemByPostVar("postvar");
$keyValuePairs = $selectAllocationInput->convertPostToKeyValuePair();
````

This will convert the post data above into:

````
first_key   => second_value,
second_key  => first_value,