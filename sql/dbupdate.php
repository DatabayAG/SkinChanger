<#1>
<?php
/** @var $ilDB \ilDBInterface */
if (!$ilDB->tableExists("ui_uihk_skcr")) {
    $ilDB->createTable("ui_uihk_skcr", [
        'id' => [
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
            'default' => 0
        ],
    ]);
    $ilDB->addPrimaryKey("ui_uihk_skcr", ["id"]);
}
?>
<#2>
<?php
if (!$ilDB->tableExists("ui_uihk_skcr_alloc")) {
    $ilDB->createTable("ui_uihk_skcr_alloc", [
        'rol_id' => [
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
        ],
        'skin_id' => [
            'type' => 'text',
            'length' => 127,
            'notnull' => true,
        ],
    ]);
    $ilDB->addPrimaryKey("ui_uihk_skcr_alloc", ["rol_id", "skin_id"]);
}
?>