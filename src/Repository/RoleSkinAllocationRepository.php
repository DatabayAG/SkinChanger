<?php /** @noinspection SqlResolve */
declare(strict_types=1);
/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace SkinChanger\Repository;

use ilDBInterface;
use SkinChanger\Model\RoleSkinAllocation;

/**
 * Class RoleSkinAllocationRepository
 * @package SkinChanger\Repository
 * @author  Marvin Beym <mbeym@databay.de>
 */
class RoleSkinAllocationRepository
{
    private static ?RoleSkinAllocationRepository $instance = null;
    protected ilDBInterface $db;
    protected string $tablename = 'ui_uihk_skcr_alloc';

    /**
     * RoleSkinAllocationRepository constructor.
     * @param ilDBInterface|null $db
     */
    public function __construct(ilDBInterface $db = null)
    {
        if ($db) {
            $this->db = $db;
        } else {
            global $DIC;
            $this->db = $DIC->database();
        }
    }

    /**
     * Returns the instance of the repository to prevent recreation of the whole object.
     * @param ilDBInterface|null $db
     * @return static
     */
    public static function getInstance(ilDBInterface $db = null) : self
    {
        if (self::$instance) {
            return self::$instance;
        }
        return self::$instance = new self($db);
    }

    /**
     * Creates a new row in the role => skin allocation database table.
     * @param RoleSkinAllocation $allocation
     */
    public function create(RoleSkinAllocation $allocation)
    {
        $this->db->manipulateF(
            "INSERT INTO " . $this->tablename . " (rol_id, skin_id) VALUES " .
            "(%s, %s)",
            ["integer", "text"],
            [
                $allocation->getRolId(),
                $allocation->getSkinId(),
            ]
        );
    }

    /**
     * Returns all rows from the role => skin allocation database table.
     * @return RoleSkinAllocation[]
     */
    public function readAll() : array
    {
        $result = $this->db->query("SELECT * FROM {$this->tablename}");
        $data = $this->db->fetchAll($result);

        return $this->assocArrToObjArr($data);
    }

    /**
     * Removes a row from the database table by role id.
     * @param $rolId
     * @return bool
     */
    public function remove($rolId) : bool
    {
        $affected_rows = $this->db->manipulate("DELETE FROM {$this->tablename} WHERE rol_id = {$this->db->quote($rolId, "integer")}");
        return $affected_rows == 1;
    }

    /**
     * Converts an associative array into an array of RoleSkinAllocation
     * @param $data
     * @return RoleSkinAllocation[]
     */
    protected function assocArrToObjArr(array $data) : array
    {
        foreach ($data as $key => $value) {
            $data[$key] = new RoleSkinAllocation();
            $data[$key]
                ->setRolId((int) $value["rol_id"])
                ->setSkinId((string) $value["skin_id"]);
        }
        return $data;
    }

    /**
     * Removes all rows from the database table
     * @return bool
     */
    public function deleteAll() : bool
    {
        $affected_rows = $this->db->manipulate("DELETE FROM {$this->tablename}");
        return $affected_rows == 1;
    }

    public function findSkinByRoleId(int $rol_id)
    {
        $result = $this->db->query(
            "SELECT skin_id FROM {$this->tablename} WHERE rol_id = {$this->db->quote($rol_id, "integer")}"
        );
        return $this->db->fetchAssoc($result)["skin_id"];
    }
}
