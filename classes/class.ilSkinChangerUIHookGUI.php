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
    private ?string $skinId = null;
    protected ilCtrl $ctrl;
    protected ilObjUser $user;
    protected HTTPServices $http;
    /**
     * @var RequestInterface|ServerRequestInterface
     */
    protected $request;
    protected ?ilSkinChangerPlugin $plugin;

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
        $this->plugin = ilSkinChangerPlugin::getInstance();
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
     * @return null|string[]
     */
    private function getSkinData(?string $skinIdOverride = null) : ?array
    {
        $skinId = $skinIdOverride ?? $this->request->getQueryParams()["skin"];

        $styleId = "";

        if (!$skinId) {
            return null;
        }

        $foundSkin = false;
        try {
            foreach (ilStyleDefinition::getAllSkinStyles() as $skinStyle) {
                if ($skinId === $skinStyle["skin_id"]) {
                    $foundSkin = true;
                    $styleId = $skinStyle["style_id"];
                    break;
                }
            }
        } catch (Exception $ex) {
            return null;
        }

        if (!$foundSkin) {
            return null;
        }

        return [
            "skinId" => $skinId,
            "styleId" => $styleId,
        ];
    }

    /**
     * @return void
     */
    public function skinChangeThroughLink() : void
    {
        if (!(bool) $this->plugin->settings->get("allowSkinOverride") || !(bool) $this->plugin->settings->get("enableAfterLoginSkinAllocation")) {
            $this->redirectToDashboard();
        }

        $skinData = $this->getSkinData();

        if (!$skinData) {
            ilUtil::sendFailure($this->plugin->txt("requestedSkinNotFound"), true);
            $this->redirectToDashboard();
        }

        $skinId = $skinData["skinId"];
        $styleId = $skinData["styleId"];

        if ($this->user->getPref("skinOverride") !== $skinId) {
            $this->user->setPref("skinOverride", $skinId);
            $this->user->writePrefs();
            $this->plugin->setUserSkin($this->user, $skinId, $styleId);
        }

        if ($this->user->isAnonymous() || $this->user->getLogin() === null) {
            $this->ctrl->redirectToURL('login.php');
        } else {
            $this->redirectToDashboard();
        }
    }

    /**
     * Executes the requested command.
     * @return void
     */
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

            if (!(bool) ilSkinChangerPlugin::getInstance()->settings->get("enableAnonSkinChange")) {
                if (defined('CLIENT_ID')) {
                    $additionalParameters .= '&client_id=' . CLIENT_ID;
                }
                $this->ctrl->redirectToURL('login.php?cmd=force_login' . $additionalParameters);
            }
        }

        $this->performCommand($cmd);
    }

    /**
     * Calls the function for a received command
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

    public function getHTML($a_comp, $a_part, $a_par = array()) : array
    {
        $tplId = $a_par["tpl_id"];
        $html = $a_par["html"];

        if (!$this->user->isAnonymous() || !(bool) ilSkinChangerPlugin::getInstance()->settings->get("enableAnonSkinChange")) {
            if ($this->user->id !== 0) {
                return $this->uiHookResponse();
            }
        }
        $query = $this->request->getQueryParams();

        if ($a_part === "redirect" && $query["target"] === "skinChangeThroughLink" && isset($query["skin"])) {
            ilUtil::setCookie("anonSkinChange", $query["skin"]);
        }

        if (!$html && !$a_part) {
            return $this->uiHookResponse();
        }

        $skinIdOverride = $_COOKIE["anonSkinChange"] ?? null;

        if ($skinIdOverride === null) {
            return $this->uiHookResponse();
        }

        $skinData = $this->getSkinData($skinIdOverride);

        if (!$skinData) {
            ilUtil::sendFailure($this->plugin->txt("requestedSkinNotFound"), true);
            $this->redirectToDashboard();
        }

        $skinId = $skinData["skinId"];
        $styleId = $skinData["styleId"];

        if ($tplId === "Services/Init/tpl.login.html" && $a_part === "template_add") {
            $skinFolderPath = $this->getSkinFolder($skinId);
            if (!$skinFolderPath) {
                return $this->uiHookResponse();
            }

            return $this->uiHookResponse(self::REPLACE, file_get_contents("$skinFolderPath/Services/Init/tpl.login.html"));
        }

        if ($tplId === "src/UI/templates/default/Layout/tpl.standardpage.html" && $a_part === "template_get") {
            $match = [];
            if (!preg_match(
                '/\/skin.+\/(.+\.css)|default\/(delos\.css)/m',
                $html,
                $match
            ) || !$match || count($match) < 2) {
                return $this->uiHookResponse();
            }

            try {
                $currentStyle = ilStyleDefinition::getCurrentStyle();
                $currentSkin = ilStyleDefinition::getCurrentSkin();
            } catch (Exception $ex) {
                return $this->uiHookResponse();
            }

            if ($currentSkin === "default") {
                $currentCssPath = "./templates/";
                $newCssPath = "./templates/";
                if ($skinId !== "default") {
                    $newCssPath = "./Customizing/global/skin/";
                }
            } else {
                $currentCssPath = "./Customizing/global/skin/";
                $newCssPath = "./Customizing/global/skin/";

                if ($skinId === "default") {
                    $newCssPath = "./templates/";
                }
            }
            $currentCssPath .= "$currentSkin/$currentStyle.css";
            $newCssPath .= "$skinId/$styleId.css";

            if ($currentCssPath !== $newCssPath) {
                $html = str_replace($currentCssPath, $newCssPath, $html);
            }

            $anonSkinChangeUrlCleanerSuffix = $this->plugin->settings->get("anonSkinChangeUrlCleanerSuffix", "");

            $html = str_replace(
                "</head>",
                "<script src=\"{$this->plugin->jsFolder("urlCleaner.js")}\"></script><div style='display: none;' anonSkinId='$skinId' id='skinChange_temp_urlCleaner'>$anonSkinChangeUrlCleanerSuffix</div></head>",
                $html
            );

            return $this->uiHookResponse(self::REPLACE, $html);
        }

        return $this->uiHookResponse();
    }

    /** @inheritDoc */
    public function modifyGUI($a_comp, $a_part, $a_par = array())
    {
    }

    /**
     * Redirects the user to the dashboard page
     * @return void
     */
    protected function redirectToDashboard()
    {
        $this->ctrl->redirectByClass(ilDashboardGUI::class, "show");
    }

    protected function getSkinFolder(string $skinId) : ?string
    {
        $skinFolderPath = dirname($this->plugin->getDirectory(), 5) . "/skin";

        if (!is_dir($skinFolderPath)) {
            return null;
        }

        foreach (scandir($skinFolderPath) as $folder) {
            if ($folder === "." || $folder === "..") {
                continue;
            }

            $templateFile = "$skinFolderPath/$folder/template.xml";

            if (!is_file($templateFile)) {
                continue;
            }

            $templateXmlElement = simplexml_load_string(file_get_contents($templateFile));

            $foundSkinId = (string) $templateXmlElement->style->attributes()["id"];

            if ($foundSkinId === $skinId) {
                return "$skinFolderPath/$folder";
            }
        }
        return null;
    }

    /**
     * Returns the array used to replace the html content
     * @param string $mode
     * @param string $html
     * @return string[]
     */
    protected function uiHookResponse(string $mode = ilUIHookPluginGUI::KEEP, string $html = "") : array
    {
        return ['mode' => $mode, 'html' => $html];
    }
}
