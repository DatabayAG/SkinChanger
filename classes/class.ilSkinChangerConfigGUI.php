<?php declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

use SkinChanger\Form\ConfigForm;
use SkinChanger\Repository\RoleSkinAllocationRepository;
use ILIAS\DI\HTTPServices;

require_once __DIR__ . '/../vendor/autoload.php';

/**
 * Class ilSkinChangerConfigGUI
 * @author  Marvin Beym <mbeym@databay.de>
 */
class ilSkinChangerConfigGUI extends ilPluginConfigGUI
{
    protected ilGlobalPageTemplate $tpl;
    protected ilCtrl $ctrl;
    protected RoleSkinAllocationRepository $repository;
    protected HTTPServices $http;

    /**
     * ilSkinChangerConfigGUI constructor.
     */
    public function __construct()
    {
        global $DIC;
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->repository = RoleSkinAllocationRepository::getInstance();
        $this->http = $DIC->http();
        $this->ctrl = $DIC->ctrl();
    }

    /**
     * Shows the plugin configuration
     * @return void
     * @throws ilPluginException|ilSystemStyleException
     */
    public function showSettings()
    {
        /** @var ilSkinChangerPlugin $this */
        $form = new ConfigForm($this->getPluginObject());
        $form->bindObject($this->repository->readAll());
        $this->tpl->setContent($form->getHTML());
    }

    /**
     * Saves plugin configuration
     * @return void
     * @throws ilSystemStyleException
     */
    public function saveSettings() : void
    {
        $form = new ConfigForm($this->getPluginObject());
        $form->setValuesByPost();
        if ($form->checkInput()) {
            $form->handleSubmit();

            ilUtil::sendSuccess($this->getPluginObject()->txt("updateSuccessful"), true);
        }

        $this->tpl->setContent($form->getHTML());
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
            default:
                $this->{$this->getDefaultCommand()}();
        }
    }

    /**
     * Returns the default command
     * @return string
     */
    protected function getDefaultCommand() : string
    {
        return "showSettings";
    }
}
