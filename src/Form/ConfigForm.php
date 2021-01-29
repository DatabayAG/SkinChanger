<?php
/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace SkinChanger\Form;

use ilPropertyFormGUI;
use ilSkinChangerPlugin;
use ilToolbarGUI;
use ilNestedListInputGUI;
use ilMultipleNestedOrderingElementsInputGUI;
use ilMatrixRowWizardInputGUI;

/**
 * Class ConfigForm
 * @package SkinChanger\Form
 * @author  Marvin Beym <mbeym@databay.de>
 */
class ConfigForm extends ilPropertyFormGUI
{
    private ilSkinChangerPlugin $plugin;
    private ilToolbarGUI $toolbar;

    public function __construct(ilSkinChangerPlugin $plugin)
    {
        Global $DIC;
        parent::__construct();
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->plugin = $plugin;
        $this->toolbar = $DIC->toolbar();

        $this->setTitle($plugin->txt("ui_uihk_skinchanger_config"));

        $rows = new \ilKVPWizardInputGUI("", "rows");
        $rows->setRequired(true);
        $rows->setTitle($this->plugin->txt("roleToSkinInput"));
        $rows->setKeyName($this->plugin->txt("role"));
        $rows->setValueName($this->plugin->txt("skin"));
        $rows->setInfo($this->plugin->txt("info_roleToSkinInput"));

        $rows->setValues(["firstInput_key" => "firstInput_valuey"]);
        $this->addItem($rows);

        $this->setFormAction($this->ctrl->getFormActionByClass(\ilSkinChangerConfigGUI::class, "saveConfiguration"));
        $this->addCommandButton("saveSettings", $this->plugin->txt("save"));
        $this->tpl->addJavaScript($this->plugin->getDirectory() . '/templates/js/kvpWizardHandler.js');
    }
}
