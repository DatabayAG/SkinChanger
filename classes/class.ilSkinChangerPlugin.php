<?php declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

use SkinChanger\Repository\RoleSkinAllocationRepository;

/**
 * Class ilSkinChangerPlugin
 * @author  Marvin Beym <mbeym@databay.de>
 */
class ilSkinChangerPlugin extends ilUserInterfaceHookPlugin
{
    /**
     * @inheritdoc
     */
    public function getPluginName() : string
    {
        return "SkinChanger";
    }

    /**
     * Called by ilias event system to hook into afterLogin event.
     * @param $a_component
     * @param $a_event
     * @param $a_parameter
     * @return void
     * @throws ilSystemStyleException
     */
    public static function handleEvent($a_component, $a_event, $a_parameter) : void
    {
        if ($a_event != "afterLogin") {
            return;
        }

        global $DIC;
        $repository = RoleSkinAllocationRepository::getInstance();
        $user = $DIC->user();
        $review = $DIC->rbac()->review();

        $skinId = "";
        $styleId = "";

        $assignedRoles = $review->assignedRoles($DIC->user()->getId());
        $availableStyles = ilStyleDefinition::getAllSkinStyles();
        foreach ($assignedRoles as $assignedRole) {
            $assignedSkin = $repository->findSkinByRoleId((int) $assignedRole);
            if ($assignedSkin) {
                $skinId = $assignedSkin;
                break;
            }
        }

        foreach ($availableStyles as $availableStyle) {
            if ($availableStyle["skin_id"] == $skinId) {
                $styleId = $availableStyle["style_id"];
                break;
            }
        }

        if (!$styleId) {
            return;
        }

        if ($user->getPref("skin") != $skinId || $user->getPref("style") != $styleId) {
            $user->setPref("skin", $skinId);
            $user->setPref("style", $styleId);
            $user->writePrefs();
        }
    }

    /**
     * Called before plugin is uninstalled
     * @return bool
     */
    protected function beforeUninstall() : bool
    {
        global $DIC;

        if ($DIC->database()->tableExists('ui_uihk_skcr')) {
            $DIC->database()->dropTable('ui_uihk_skcr');
        }
        if ($DIC->database()->sequenceExists("ui_uihk_skcr")) {
            $DIC->database()->dropSequence("ui_uihk_skcr");
        }

        return parent::beforeUninstall();
    }
}
