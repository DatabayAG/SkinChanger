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
use SkinChanger\Form\Input\SelectAllocationInput\ilSelectAllocationInput;
use ilStyleDefinition;
use ilSystemStyleException;
use Exception;
use ilSkinChangerConfigGUI;
use ilCheckboxInputGUI;
use ilTextInputGUI;

/**
 * Class ConfigForm
 * @package SkinChanger\Form
 * @author  Marvin Beym <mbeym@databay.de>
 */
class ConfigForm extends ilPropertyFormGUI
{
    protected ilSkinChangerPlugin $plugin;
    protected ilToolbarGUI $toolbar;

    /**
     * @var RequestInterface|ServerRequestInterface
     */
    protected $request;

    /**
     * @var RoleSkinAllocationRepository
     */
    protected RoleSkinAllocationRepository $repository;

    /**
     * ConfigForm constructor.
     * @throws ilSystemStyleException
     */
    public function __construct()
    {
        global $DIC;
        parent::__construct();

        $this->request = $DIC->http()->request();
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->plugin = ilSkinChangerPlugin::getInstance();
        $this->toolbar = $DIC->toolbar();
        $this->repository = RoleSkinAllocationRepository::getInstance();

        $this->setTitle($this->plugin->txt("ui_uihk_skinchanger_config"));

        $enableAnonSkinChangeInput = new ilCheckboxInputGUI(
            $this->plugin->txt("enableAnonSkinChange"),
            "enableAnonSkinChange"
        );
        $enableAnonSkinChangeInput->setInfo($this->plugin->txt("enableAnonSkinChange_info"));
        $this->addItem($enableAnonSkinChangeInput);

        $anonSkinChangeUrlCleanerSuffix = new ilTextInputGUI(
            $this->plugin->txt("anonSkinChangeUrlCleanerSuffix"),
            "anonSkinChangeUrlCleanerSuffix"
        );
        $anonSkinChangeUrlCleanerSuffix->setInfo($this->plugin->txt("anonSkinChangeUrlCleanerSuffix_info"));
        $enableAnonSkinChangeInput->addSubItem($anonSkinChangeUrlCleanerSuffix);

        $enableAfterLoginSkinAllocation = new ilCheckboxInputGUI(
            $this->plugin->txt("enableAfterLoginSkinAllocation"),
            "enableAfterLoginSkinAllocation"
        );
        $enableAfterLoginSkinAllocation->setInfo($this->plugin->txt("enableAfterLoginSkinAllocation_info"));

        $this->addItem($enableAfterLoginSkinAllocation);

        $roleOptions = [];
        foreach ($DIC->rbac()->review()->getAssignableRoles() as $role) {
            $roleOptions[$role["rol_id"]] = $role["title"];
        }

        $skinOptions = [];
        foreach (ilStyleDefinition::getAllSkinStyles() as $style) {
            $skinOptions[$style["skin_id"]] = $style["skin_name"];
        }

        $selectAllocationInput = new ilSelectAllocationInput(
            $this->plugin,
            $this->plugin->txt("roleToSkinInput"),
            "roleToSkinAllocation"
        );
        $selectAllocationInput
            ->setKeyOptions($roleOptions)
            ->setValueOptions($skinOptions)
            ->setTableHeaders(
                $this->plugin->txt("role"),
                $this->plugin->txt("skin"),
                $this->plugin->txt("action")
            )
            ->setRequired(true)
            ->setInfo($this->plugin->txt("info_roleToSkinInput"));
        $this->addItem($selectAllocationInput);

        $this->setFormAction($this->ctrl->getFormActionByClass(ilSkinChangerConfigGUI::class, "saveConfiguration"));
        $this->addCommandButton("saveSettings", $this->plugin->txt("save"));
    }

    /**
     * Handles the form submit.
     * @return void
     * @throws Exception
     */
    public function handleSubmit() : void
    {

        /**
         * @var $allocations RoleSkinAllocation[]
         */
        $allocations = [];

        $this->plugin->settings->set("enableAnonSkinChange", (bool) $this->getInput("enableAnonSkinChange"));
        $this->plugin->settings->set(
            "enableAfterLoginSkinAllocation",
            (bool) $this->getInput("enableAfterLoginSkinAllocation")
        );
        $this->plugin->settings->set(
            "anonSkinChangeUrlCleanerSuffix",
            $this->getInput("anonSkinChangeUrlCleanerSuffix")
        );

        /**
         * @var ilSelectAllocationInput $selectAllocationInput
         */
        $selectAllocationInput = $this->getItemByPostVar("roleToSkinAllocation");
        $keyValuePairs = $selectAllocationInput->convertPostToKeyValuePair();

        foreach ($keyValuePairs as $key => $value) {
            $allocations[] = (new RoleSkinAllocation())
                ->setRolId((int) $key)
                ->setSkinId((string) $value);
        }

        $this->repository->deleteAll();

        foreach ($allocations as $allocation) {
            $this->repository->create($allocation);
        }
    }

    /**
     * @param RoleSkinAllocation[] $roleSkinAllocations
     * @return void
     */
    public function bindObject(array $roleSkinAllocations)
    {
        $keyValuePairs = [];
        foreach ($roleSkinAllocations as $allocation) {
            array_push($keyValuePairs, [$allocation->getRolId() => $allocation->getSkinId()]);
        }
        $values = [
            "roleToSkinAllocation" => $keyValuePairs,
            "enableAnonSkinChange" => (bool) $this->plugin->settings->get("enableAnonSkinChange", false),
            "enableAfterLoginSkinAllocation" => (bool) $this->plugin->settings->get(
                "enableAfterLoginSkinAllocation",
                true
            ),
            "anonSkinChangeUrlCleanerSuffix" => $this->plugin->settings->get("anonSkinChangeUrlCleanerSuffix", "")
        ];
        $this->setValuesByArray($values, true);
    }
}
