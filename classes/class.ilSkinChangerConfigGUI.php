<?php declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

use SkinChanger\Form\ConfigForm;
use SkinChanger\Repository\RoleSkinAllocationRepository;
use ILIAS\Filesystem\Stream\Streams;
use ILIAS\DI\HTTPServices;
use ILIAS\HTTP\Response\Sender\ResponseSendingException;

require_once __DIR__ . '/../vendor/autoload.php';

/**
 * Class ilSkinChangerConfigGUI
 * @author  Marvin Beym <mbeym@databay.de>
 */
class ilSkinChangerConfigGUI extends ilPluginConfigGUI
{
    private ilGlobalPageTemplate $tpl;
    private ilCtrl $ctrl;
    private RoleSkinAllocationRepository $repository;
    private HTTPServices $http;

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
     * @throws ilPluginException
     */
    public function showSettings()
    {
        /** @var ilSkinChangerPlugin $this */
        $form = new ConfigForm($this->getPluginObject());

        $this->tpl->setContent($form->getHTML());
    }

    /**
     * Saves plugin configuration
     * @return void
     */
    public function saveSettings() : void
    {
        $form = new ConfigForm($this->getPluginObject());
        $form->setValuesByPost();
        if ($form->checkInput()) {
            $form->handleSubmit();

            ilUtil::sendSuccess($this->getPluginObject()->txt("updateSuccessful"), true);
            $this->ctrl->redirectByClass(ilobjcomponentsettingsgui::class, "view");
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
     * Function called by ajax and returns an array of allocations from the database.
     * Returns json array with key => value.
     * @return void
     * @throws ResponseSendingException
     */
    public function ajax_allocations() : void
    {
        $allocations = $this->repository->readAll();
        $data = [];

        foreach ($allocations as $key => $allocation) {
            $data[$key] = [
                "key" => $allocation->getRolId(),
                "value" => $allocation->getSkinId()
            ];
        }
        $responseStream = Streams::ofString(json_encode($data));
        $this->http->saveResponse($this->http->response()->withBody($responseStream));
        $this->http->sendResponse();
        $this->http->close();
    }

    /**
     * Returns the default command
     * @return string
     */
    private function getDefaultCommand() : string
    {
        return "showSettings";
    }
}
