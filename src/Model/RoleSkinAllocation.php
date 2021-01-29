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
    /**
     * @var int|null
     */
    private ?int $id = null;
    private int $rol_id = 0;
    private string $skin_id = "";

    /**
     * @return int|null
     */
    public function getId() : ?int
    {
        return $this->id;
    }

    /**
     * @param int|null $id
     * @return RoleSkinAllocation
     */
    public function setId(?int $id) : RoleSkinAllocation
    {
        $this->id = $id;
        return $this;
    }

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
