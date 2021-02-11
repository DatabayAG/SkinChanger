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

    /**
     * ConfigForm constructor.
     * @param ilSkinChangerPlugin $plugin
     * @throws ilSystemStyleException
     */
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

        $roleOptions = [];
        foreach ($DIC->rbac()->review()->getAssignableRoles() as $role) {
            $roleOptions[$role["rol_id"]] = $role["title"];
        }

        $skinOptions = [];
        foreach (ilStyleDefinition::getAllSkinStyles() as $style) {
            $skinOptions[$style["skin_id"]] = $style["skin_name"];
        }

        $selectAllocationInput = new ilSelectAllocationInput(
            $plugin,
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

        $keyValuePairs = ilSelectAllocationInput::convertPostToKeyValuePair("roleToSkinAllocation");

        foreach ($keyValuePairs as $key => $value) {
            array_push($allocations, (new RoleSkinAllocation())
                ->setRolId((int) $key)
                ->setSkinId((string) $value));
        }

        $this->repository->deleteAll();

        foreach ($allocations as $allocation) {
            $this->repository->create($allocation);
        }
    }

    /**
     * @param RoleSkinAllocation[] $roleSkinAllocations
     */
    public function bindObject(array $roleSkinAllocations)
    {
        $keyValuePairs = [];
        foreach ($roleSkinAllocations as $allocation) {
            array_push($keyValuePairs, [$allocation->getRolId() => $allocation->getSkinId()]);
        }
        $values = [
            "roleToSkinAllocation" => $keyValuePairs
        ];
        $this->setValuesByArray($values, true);
    }
}
