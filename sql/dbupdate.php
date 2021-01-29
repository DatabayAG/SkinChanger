<#1>
<?php
/** @var $ilDB \ilDBInterface */
if (!$ilDB->tableExists('ui_uihk_skinchanger')) {
    $ilDB->createTable('ui_uihk_skinchanger', [
        'id' => [
            'type'     => 'integer',
            'length'   => 4,
            'notnull'  => true,
            'default'  => 0
        ],
        'obj_id' => [
            'type'     => 'integer',
            'length'   => 4,
            'notnull'  => true,
            'default'  => 0
        ],
    ]);
    $ilDB->addPrimaryKey('ui_uihk_skinchanger', ['id']);
    $ilDB->createSequence('ui_uihk_skinchanger');
}
?>
<#2>
<?php
if (!$ilDB->tableExists('ui_uihk_skcr_alloc')) {
    $ilDB->createTable('ui_uihk_skcr_alloc', [
        'id' => [
            'type'     => 'integer',
            'length'   => 4,
            'notnull'  => true,
            'default'  => 0
        ],
        'rol_id' => [
            'type'     => 'integer',
            'length'   => 4,
            'notnull'  => true,
        ],
        'skin_id' => [
            'type'     => 'text',
            'length'   => 127,
            'notnull'  => true,
        ],
    ]);
    $ilDB->addPrimaryKey("ui_uihk_skcr_alloc", ["id"]);
}
if ($ilDB->tableExists("ui_uihk_skinchanger") && !$ilDB->tableExists("ui_uihk_skcr")) {
    $ilDB->renameTable("ui_uihk_skinchanger", "ui_uihk_skcr");
}
?>
<#3>
<?php
$ilDB->dropTable("ui_uihk_skinchanger", false);
$ilDB->dropTable("ui_uihk_skinchanger_seq", false);
$ilDB->dropTable("ui_uihk_skinchanger_alloc", false);
$ilDB->dropTable("ui_uihk_skcr", false);
$ilDB->dropTable("ui_uihk_skcr_seq", false);
$ilDB->dropTable("ui_uihk_skcr_alloc", false);
?>
<#4>
<?php
if (!$ilDB->tableExists("ui_uihk_skcr")) {
    $ilDB->createTable("ui_uihk_skcr", [
        'id' => [
            'type'     => 'integer',
            'length'   => 4,
            'notnull'  => true,
            'default'  => 0
        ],
        'rol_id' => [
            'type'     => 'integer',
            'length'   => 4,
            'notnull'  => true,
        ],
        'skin_id' => [
            'type'     => 'text',
            'length'   => 127,
            'notnull'  => true,
        ],
    ]);
}
?>
<#5>
<?php
if ($ilDB->tableExists('ui_uihk_skcr')) {
    $ilDB->createSequence('ui_uihk_skcr');
}
?>