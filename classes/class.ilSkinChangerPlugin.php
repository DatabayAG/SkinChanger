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

        $assignedRoles = $review->assignedRoles($user->getId());

        foreach ($assignedRoles as $assignedRole) {
            $assignedSkin = $repository->findSkinByRoleId((int) $assignedRole);
            if ($assignedSkin) {
                $skinId = $assignedSkin;
                break;
            }
        }

        foreach (ilStyleDefinition::getAllSkinStyles() as $availableStyle) {
            if ($availableStyle["skin_id"] == $skinId) {
                $styleId = $availableStyle["style_id"];
                break;
            }
        }

        if (!$styleId) {
            return;
        }

        //Checks if user changed his skin using the override link and if so changes the users skin to the defined override one.
        if (($override = self::checkUserHasOverriddenSkin($user))) {
            self::setUserSkin($user, $override["skinId"], $override["styleId"]);
            return;
        }

        self::setUserSkin($user, $skinId, $styleId);
    }

    /**
     * Checks if the user has overridden his skin using the override link
     * and returns the skinId and styleId for changing to it.
     * @param ilObjUser $user
     * @return string[]|null
     * @throws ilSystemStyleException
     */
    private static function checkUserHasOverriddenSkin(ilObjUser $user) : ?array
    {
        if (($skinOverride = $user->getPref("skinOverride"))) {
            foreach (ilStyleDefinition::getAllSkinStyles() as $availableStyle) {
                if ($availableStyle["skin_id"] == $skinOverride) {
                    return ["skinId" => $skinOverride, "styleId" => $availableStyle["style_id"]];
                }
            }
        }
        return null;
    }

    /**
     * Sets the user skin and checks if the skin is already the desired skin.
     * @param $user
     * @param $skinId
     * @param $styleId
     */
    protected static function setUserSkin($user, $skinId, $styleId)
    {
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
        $database = $DIC->database();
        if ($database->tableExists('ui_uihk_skcr')) {
            $database->dropTable('ui_uihk_skcr');
        }
        if ($database->tableExists("ui_uihk_skcr_alloc")) {
            $database->dropTable("ui_uihk_skcr_alloc");
        }

        return parent::beforeUninstall();
    }
}
