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

    /** @inheritDoc */
    public function getHTML($a_comp, $a_part, $a_par = array()) : array
    {
        return parent::getHTML($a_comp, $a_part, $a_par);
    }

    /** @inheritDoc */
    public function modifyGUI($a_comp, $a_part, $a_par = array())
    {
    }
}
