<?php declare(strict_types=1);
/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

use SkinChanger\Form\ConfigForm;
use SkinChanger\Model\RoleSkinAllocation;
use SkinChanger\Repository\RoleSkinAllocationRepository;

require_once __DIR__ . '/../vendor/autoload.php';

/**
 * Class ilSkinChangerConfigGUI
 *
 * @author  Marvin Beym <mbeym@databay.de>
 */
class ilSkinChangerConfigGUI extends ilPluginConfigGUI
{
    /**
     * @var ilGlobalPageTemplate
     */
    private ilGlobalPageTemplate $tpl;

    /**
     * ilSkinChangerConfigGUI constructor.
     */
    public function __construct()
    {
        global $DIC;
        $this->tpl = $DIC->ui()->mainTemplate();
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
     *
     * @return void
     */
    public function saveSettings(): void
    {
        global $DIC;
        $request = $DIC->http()->request();
        $requestBody = $request->getParsedBody();

        $rows = $requestBody["rows"];

        /**
         * @var $roleToSkinAllocations RoleSkinAllocation[]
         */
        $repository = RoleSkinAllocationRepository::getInstance();

        /**
         * @var $allocations RoleSkinAllocation[]
         */
        $allocations = [];
        for ($i = 0; $i < count($rows["key"]); $i++) {
            $allocations[$i] = (new RoleSkinAllocation())
                ->setRolId((int) $rows["key"][$i])
                ->setSkinId((string) $rows["value"][$i]);
        }


        //Todo: alternative wäre die ganze Datenbank zu leeren und dann neu zu schreiben mit den gerade erstellten zuweisungen
        //Todo: Frage ist was effizienter ist.
        //Todo: Doppelte eintraäge müssen noch gefiltert werden, wenn user mehrer mit gleicher rolle macht
        //z.B.  Admin => ilias
        //      Admin => databay
        //Dann sollte der Admin => ilias entfernt werden, weil => databay weiter unten ist. Dann auch nur Admin => databay speichern.
        //Das würde durch das komplette neu schreiben der Datenbank einfacher gehen.
        $existingAllocations = $repository->readAll();
        $allocationsToRemove = array_filter($existingAllocations, function ($existingAllocation) use ($allocations) {
            $counter = 0;
            foreach ($allocations as $allocation) {
                $counter += $allocation->getRolId() != $existingAllocation->getRolId() ? 1 : 0;
            }
            return $counter == count($allocations);
        });

        foreach ($allocationsToRemove as $allocation) {
            $repository->remove($allocation->getId());
        }

        foreach ($allocations as $allocation) {
            $repository->create($allocation);
        }
    }

    /**
     * Calls the function for a received command
     *
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
     *
     * @return string
     */
    private function getDefaultCommand() : string
    {
        return "showSettings";
    }
}
