<?php declare(strict_types=1);
/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\DI\Container;

/**
 * Class ilSkinChangerPlugin
 *
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
     * Called before plugin is uninstalled
     *
     * @return bool
     */
    protected function beforeUninstall() : bool
    {
        global $DIC;

        if ($DIC->database()->tableExists('ui_uihk_skcr')) {
            $DIC->database()->dropTable('ui_uihk_skcr');
        }

        return parent::beforeUninstall();
    }
}
