<?php declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

use SkinChanger\Repository\RoleSkinAllocationRepository;

/**
 * Class ilSkinChangerPlugin
 * @author  Marvin Beym <mbeym@databay.de>
 */
class ilSkinChangerPlugin extends ilUserInterfaceHookPlugin
{
    /** @var string */
    public const CTYPE = "Services";
    /** @var string */
    public const CNAME = "UIComponent";
    /** @var string */
    public const SLOT_ID = "uihk";
    /** @var string */
    public const PNAME = "SkinChanger";

    /**
     * @var int[]
     */
    protected const blacklistedUserIds = [ANONYMOUS_USER_ID];

    /**
     * @var ilSkinChangerPlugin|null
     */
    private static $instance = null;
    public ilSetting $settings;

    public function __construct()
    {
        $this->settings = new ilSetting(self::class);
        parent::__construct();
    }

    /**
     * @inheritdoc
     */
    public function getPluginName() : string
    {
        return self::PNAME;
    }

    /**
     * @return ilSkinChangerPlugin
     * @noinspection PhpIncompatibleReturnTypeInspection
     */
    public static function getInstance() : ilSkinChangerPlugin
    {
        return self::$instance ?? (self::$instance = ilPluginAdmin::getPluginObject(
            self::CTYPE,
            self::CNAME,
            self::SLOT_ID,
            self::PNAME
        ));
    }

    /**
     * Called by ilias event system to hook into afterLogin event.
     * @param $a_component
     * @param $a_event
     * @param $a_parameter
     * @return void
     * @throws ilSystemStyleException
     */
    public function handleEvent($a_component, $a_event, $a_parameter) : void
    {
        if ($a_event !== "afterLogin" || PHP_SAPI === 'cli') {
            return;
        }

        global $DIC;
        $repository = RoleSkinAllocationRepository::getInstance();
        $user = $DIC->user();
        $review = $DIC->rbac()->review();

        $skinId = "";
        $styleId = "";

        $assignedRoles = $review->assignedRoles($user->getId());

        foreach (self::blacklistedUserIds as $userId) {
            if ($user->getId() == $userId) {
                return;
            }
        }

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
        if (($override = $this->checkUserHasOverriddenSkin($user))) {
            $this->setUserSkin($user, $override["skinId"], $override["styleId"]);
            return;
        }

        $this->setUserSkin($user, $skinId, $styleId);
    }

    /**
     * Checks if the user has overridden his skin using the override link
     * and returns the skinId and styleId for changing to it.
     * @param ilObjUser $user
     * @return string[]|null
     * @throws ilSystemStyleException
     */
    public function checkUserHasOverriddenSkin(ilObjUser $user) : ?array
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

    public function assetsFolder(string $file = "") : string
    {
        return $this->getDirectory() . "/assets/$file";
    }

    public function cssFolder(string $file = "") : string
    {
        return $this->assetsFolder("css/$file");
    }

    public function imagesFolder(string $file = "") : string
    {
        return $this->assetsFolder("images/$file");
    }

    public function templatesFolder(string $file = "") : string
    {
        return $this->assetsFolder("templates/$file");
    }

    public function jsFolder(string $file = "") : string
    {
        return $this->assetsFolder("js/$file");
    }

    /**
     * Sets the user skin and checks if the skin is already the desired skin.
     * @param ilObjUser $user
     * @param string    $skinId
     * @param string    $styleId
     */
    public function setUserSkin(ilObjUser $user, string $skinId, string $styleId) : void
    {
        if ($user->getPref("skin") !== $skinId || $user->getPref("style") !== $styleId) {
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
        $this->settings->deleteAll();
        return parent::beforeUninstall();
    }
}
