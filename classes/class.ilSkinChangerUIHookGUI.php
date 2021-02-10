<?php declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\DI\HTTPServices;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ServerRequestInterface;

require_once __DIR__ . '/../vendor/autoload.php';

/**
 * Class ilSkinChangerUIHookGUI
 * @author            Marvin Beym <mbeym@databay.de>
 * @ilCtrl_isCalledBy ilSkinChangerUIHookGUI: ilUIPluginRouterGUI
 */
class ilSkinChangerUIHookGUI extends ilUIHookPluginGUI
{
    //rol_id's added to this array will be removed from the selectable allocation roles.
    private const blacklistedRoles = ["14"];
    private ilCtrl $ctrl;
    private ilObjUser $user;
    private HTTPServices $http;
    /**
     * @var RequestInterface|ServerRequestInterface
     */
    private $request;
    private ?ilUserInterfaceHookPlugin $plugin;

    /**
     * ilRepositoryResubmissionUIHookGUI constructor.
     */
    public function __construct()
    {
        global $DIC;
        $this->ctrl = $DIC->ctrl();
        $this->user = $DIC->user();
        $this->http = $DIC->http();
        $this->request = $DIC->http()->request();
        $this->plugin = new ilSkinChangerPlugin();
    }

    /** @inheritDoc */
    public function checkGotoHook($a_target) : array
    {
        $pluginCommand = "skinChangeThroughLink";
        if ($a_target == $pluginCommand) {
            $this->ctrl->setTargetScript('ilias.php');
            $url = $this->ctrl->getLinkTargetByClass(
                [ilUIPluginRouterGUI::class, self::class],
                $pluginCommand
            );

            $query = parse_url($url, PHP_URL_QUERY);
            if (is_string($query)) {
                $parameters = [];
                parse_str($query, $parameters);
                if (!is_array($parameters) || !isset($parameters['baseClass'])) {
                    $url = ilUtil::appendUrlParameterString($url, 'baseClass=' . ilUIPluginRouterGUI::class);
                }
            }

            foreach ($this->request->getQueryParams() as $key => $queryParam) {
                if (in_array($key, ["target"])) {
                    continue;
                }
                $url = ilUtil::appendUrlParameterString($url, "{$key}={$queryParam}");
            }

            $this->ctrl->redirectToURL($url);
        }

        return parent::checkGotoHook($a_target); // TODO: Change the autogenerated stub
    }

    /**
     * @throws ilSystemStyleException
     * @return void
     */
    public function skinChangeThroughLink() : void
    {
        $skinId = $this->request->getQueryParams()["skin"];
        $styleId = "";

        if (!$skinId) {
            ilUtil::sendFailure($this->plugin->txt("skinParameterMissingInUrl"), true);
            $this->redirectToDashboard();
        }

        $foundSkin = false;
        foreach (ilStyleDefinition::getAllSkinStyles() as $skinStyle) {
            if ($skinId == $skinStyle["skin_id"]) {
                $foundSkin = true;
                $styleId = $skinStyle["style_id"];
                break;
            }
        }

        if (!$foundSkin) {
            ilUtil::sendFailure($this->plugin->txt("requestedSkinNotFound"), true);
            $this->redirectToDashboard();
        }

        if ($this->user->getPref("skinOverride") != $skinId) {
            $this->user->setPref("skinOverride", $skinId);
            $this->user->writePrefs();
            ilSkinChangerPlugin::setUserSkin($this->user, $skinId, $styleId);
        }

        $this->redirectToDashboard();
    }

    public function executeCommand()
    {
        if (!isset($this->request->getQueryParams()["cmd"])) {
            ilUtil::sendFailure($this->plugin->txt("cmdNotFound"), true);
            $this->redirectToDashboard();
        }

        $cmd = $this->request->getQueryParams()["cmd"];

        if ($this->user->isAnonymous()) {
            $additionalParameters = '';

            $target = ilUtil::stripSlashes((string) ($this->http->request()->getQueryParams()['target'] ?? ''));
            if (strlen($target) > 0) {
                $additionalParameters .= '&target=' . $target;
            }

            if (defined('CLIENT_ID')) {
                $additionalParameters .= '&client_id=' . CLIENT_ID;
            }
            $this->ctrl->redirectToURL('login.php?cmd=force_login' . $additionalParameters);
        }

        $this->performCommand($cmd);
    }

    /**
     * Calls the function for a recieved command
     * @param $cmd
     * @return void
     */
    public function performCommand($cmd)
    {
        switch (true) {
            case method_exists($this, $cmd):
                $this->{$cmd}();
                break;
        }
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
     * @param $html
     * @return array
     * @throws ilSystemStyleException
     */
    private function modifyKVP($html) : array
    {
        global $DIC;
        $roles = array_filter($DIC->rbac()->review()->getAssignableRoles(), function ($role) {
            foreach (self::blacklistedRoles as $blacklistedRole) {
                return $role["rol_id"] != $blacklistedRole;
            }
            return true;
        });
        $availableStyles = ilStyleDefinition::getAllSkinStyles();

        $roleOptions = [];
        foreach ($roles as $role) {
            $roleOptions[$role["rol_id"]] = $role["title"];
        }

        $skinOptions = [];
        foreach ($availableStyles as $style) {
            $skinOptions[$style["skin_id"]] = $style["skin_name"];
        }

        $roleSelectInput = new ilSelectInputGUI("t", "ROLE_POSTVAR");
        $roleSelectInput->setOptions($roleOptions);

        $skinSelectInput = new ilSelectInputGUI("t", "SKIN_POSTVAR");
        $skinSelectInput->setOptions($skinOptions);

        $html = preg_replace(
            "/<input.*type=\"text\".*KEY_ID.*/",
            $this->modifySelectHtml($roleSelectInput, "KEY_ID", "key"),
            $html
        );
        $html = preg_replace(
            "/<input.*type=\"text\".*VALUE_ID.*/",
            $this->modifySelectHtml($skinSelectInput, "VALUE_ID", "value"),
            $html
        );
        return [
            'mode' => \ilUIHookPluginGUI::REPLACE,
            'html' => $html
        ];
    }

    /**
     * Modifies the html of a select input to set the id and name.
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
     * @param string $a_comp
     * @param string $a_part
     * @param array  $a_par
     */
    public function modifyGUI($a_comp, $a_part, $a_par = array())
    {
    }

    private function redirectToDashboard()
    {
        $this->ctrl->redirectByClass(ilDashboardGUI::class, "show");
    }
}
