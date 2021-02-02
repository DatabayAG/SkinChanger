<?php
/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace SkinChanger\Form;

use ilPropertyFormGUI;
use ilSkinChangerPlugin;
use ilToolbarGUI;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ServerRequestInterface;
use SkinChanger\Repository\RoleSkinAllocationRepository;
use SkinChanger\Model\RoleSkinAllocation;
use ilSkinChangerConfigGUI;

/**
 * Class ConfigForm
 * @package SkinChanger\Form
 * @author  Marvin Beym <mbeym@databay.de>
 */
class ConfigForm extends ilPropertyFormGUI
{
    private ilSkinChangerPlugin $plugin;
    private ilToolbarGUI $toolbar;
    /**
     * @var RequestInterface|ServerRequestInterface
     */
    private $request;
    /**
     * @var RoleSkinAllocationRepository
     */
    private RoleSkinAllocationRepository $repository;

    public function __construct(ilSkinChangerPlugin $plugin)
    {
        global $DIC;
        parent::__construct();

        $this->request = $DIC->http()->request();
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->plugin = $plugin;
        $this->toolbar = $DIC->toolbar();
        $this->repository = RoleSkinAllocationRepository::getInstance();

        $this->setTitle($plugin->txt("ui_uihk_skinchanger_config"));

        $rows = new \ilKVPWizardInputGUI("", "rows");
        $rows->setRequired(true);
        $rows->setTitle($this->plugin->txt("roleToSkinInput"));
        $rows->setKeyName($this->plugin->txt("role"));
        $rows->setValueName($this->plugin->txt("skin"));
        $rows->setInfo($this->plugin->txt("info_roleToSkinInput"));

        $numberOfAllocations = count($this->repository->readAll());

        //Gets overridden by javascript code that replaces <input> elements with <select> elements
        //Only used to define how many rows the allocation table has.
        $allocations = [];
        for ($i = 0; $i < $numberOfAllocations; $i++) {
            $allocations[$i . "_key"] = $i . "_value";
        }
        if ($numberOfAllocations < 1) {
            $allocations[$numberOfAllocations . "_key"] = $numberOfAllocations . "_value";
        }

        $rows->setValues($allocations);
        $this->addItem($rows);

        $ajaxAllocationsUrl = new \ilHiddenInputGUI("ajax_allocations_url");
        $ajaxAllocationsUrl->setValue(
            $this->ctrl->getLinkTargetByClass(
                ilSkinChangerConfigGUI::class,
                "ajax_allocations",
                "",
                true
            )
        );
        $this->addItem($ajaxAllocationsUrl);

        $this->setFormAction($this->ctrl->getFormActionByClass(\ilSkinChangerConfigGUI::class, "saveConfiguration"));

        $this->addCommandButton("saveSettings", $this->plugin->txt("save"));
        $this->tpl->addJavaScript($this->plugin->getDirectory() . '/templates/js/kvpWizardHandler.js');
    }

    public function handleSubmit()
    {
        $requestBody = $this->request->getParsedBody();

        $rows = $requestBody["rows"];

        /**
         * @var $allocations RoleSkinAllocation[]
         */
        $allocations = [];
        for ($i = 0; $i < count($rows["key"]); $i++) {
            $newAllocation = (new RoleSkinAllocation())
                ->setRolId((int) $rows["key"][$i])
                ->setSkinId((string) $rows["value"][$i]);

            $allocationAlreadyExists = false;

            foreach ($allocations as $allocation) {
                if ($allocation->getRolId() == $newAllocation->getRolId()) {
                    $allocationAlreadyExists = true;
                    break;
                }
            }
            if (!$allocationAlreadyExists) {
                $allocations[$i] = $newAllocation;
            }
        }
        $this->repository->deleteAll();

        foreach ($allocations as $allocation) {
            $this->repository->create($allocation);
        }
    }
}
