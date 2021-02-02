<?php declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once __DIR__ . '/../vendor/autoload.php';

/**
 * Class ilSkinChangerUIHookGUI
 * @author            Marvin Beym <mbeym@databay.de>
 * @ilCtrl_isCalledBy ilSkinChangerUIHookGUI: ilUIPluginRouterGUI
 */
class ilSkinChangerUIHookGUI extends ilUIHookPluginGUI
{
    /**
     * ilRepositoryResubmissionUIHookGUI constructor.
     */
    public function __construct()
    {
    }

    /**
     * @param string $a_comp
     * @param string $a_part
     * @param array  $a_par
     * @return array
     * @throws ilSystemStyleException
     */
    public function getHTML($a_comp, $a_part, $a_par = array()) : array
    {
        if ($a_par["tpl_id"] == "Modules/TestQuestionPool/tpl.prop_kvpwizardinput.html") {
            return $this->modifyKVP($a_par["html"]);
        }

        return array();
    }

    /**
     * Modifies the ilias key value pair input to replace it with select inputs.
     *
     * @param $html
     * @return array
     * @throws ilSystemStyleException
     */
    private function modifyKVP($html) : array
    {
        global $DIC;
        $roles = $DIC->rbac()->review()->getAssignableRoles();
        $availableStyles = ilStyleDefinition::getAllSkinStyles();

        $skinOptions = [];
        foreach ($availableStyles as $style) {
            $skinOptions[$style["skin_id"]] = $style["skin_name"];
        }

        $roleOptions = [];
        foreach ($roles as $role) {
            $roleOptions[$role["rol_id"]] = $role["title"];
        }

        $roleSelectInput = new ilSelectInputGUI("t", "ROLE_POSTVAR");
        $roleSelectInput->setOptions($roleOptions);

        $skinSelectInput = new ilSelectInputGUI("t", "SKIN_POSTVAR");
        $skinSelectInput->setOptions($skinOptions);

        $html = preg_replace("/<input.*type=\"text\".*KEY_ID.*/", $this->modifySelectHtml($roleSelectInput, "KEY_ID", "key"), $html);
        $html = preg_replace("/<input.*type=\"text\".*VALUE_ID.*/", $this->modifySelectHtml($skinSelectInput, "VALUE_ID", "value"), $html);
        return [
            'mode' => \ilUIHookPluginGUI::REPLACE,
            'html' => $html
        ];
    }

    /**
     * Modifies the html of a select input to set the id and name.
     *
     * @param ilSelectInputGUI $selectInput
     * @param                  $idValue
     * @param                  $postArrayValue
     * @return string
     */
    private function modifySelectHtml(ilSelectInputGUI $selectInput, $idValue, $postArrayValue) : string
    {
        $postVar = $selectInput->getPostVar();
        $html = $selectInput->render();
        $html = str_replace("id=\"{$postVar}\"", "id=\"{{$idValue}}\"", $html);
        $html = str_replace("name=\"{$postVar}\"", "name=\"{POST_VAR}[{$postArrayValue}][{ROW_NUMBER}]\"", $html);
        return $html;
    }

    /**
     * UIHook modifyGUI function.
     *
     * @param string $a_comp
     * @param string $a_part
     * @param array  $a_par
     */
    public function modifyGUI($a_comp, $a_part, $a_par = array())
    {
    }
}
