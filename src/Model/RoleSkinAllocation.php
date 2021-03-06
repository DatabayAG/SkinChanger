<?php declare(strict_types=1);
/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace SkinChanger\Model;

/**
 * Class RoleSkinAllocation
 * @package ${NAMESPACE}
 * @author  Marvin Beym <mbeym@databay.de>
 */
class RoleSkinAllocation
{
    protected int $rol_id = 0;
    protected string $skin_id = "";

    /**
     * @return int
     */
    public function getRolId() : int
    {
        return $this->rol_id;
    }

    /**
     * @param int $rol_id
     * @return RoleSkinAllocation
     */
    public function setRolId(int $rol_id) : RoleSkinAllocation
    {
        $this->rol_id = $rol_id;
        return $this;
    }

    /**
     * @return string
     */
    public function getSkinId() : string
    {
        return $this->skin_id;
    }

    /**
     * @param string $skin_id
     * @return RoleSkinAllocation
     */
    public function setSkinId(string $skin_id) : RoleSkinAllocation
    {
        $this->skin_id = $skin_id;
        return $this;
    }
}
