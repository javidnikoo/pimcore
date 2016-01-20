<?php
/**
 * Pimcore
 *
 * This source file is subject to the GNU General Public License version 3 (GPLv3)
 * For the full copyright and license information, please view the LICENSE.md and gpl-3.0.txt
 * files that are distributed with this source code.
 *
 * @category   Pimcore
 * @package    Metadata
 * @copyright  Copyright (c) 2009-2016 pimcore GmbH (http://www.pimcore.org)
 * @license    http://www.pimcore.org/license     GNU General Public License version 3 (GPLv3)
 */

namespace Pimcore\Model\Metadata\Predefined;

use Pimcore\Model;

class Dao extends Model\Dao\JsonTable {

    /**
     *
     */
    public function configure()
    {
        parent::configure();
        $this->setFile("predefined-asset-metadata");
    }

    /**
     * @param null $id
     * @throws \Exception
     */
    public function getById($id = null) {

        if ($id != null) {
            $this->model->setId($id);
        }

        $data = $this->json->getById($this->model->getId());

        if(isset($data["id"])) {
            $this->assignVariablesToModel($data);
        } else {
            throw new \Exception("Predefined asset metadata with id: " . $this->model->getId() . " does not exist");
        }
    }


    /**
     * @param null $name
     * @param null $language
     * @throws \Exception
     */
    public function getByNameAndLanguage($name = null, $language = null) {

        $data = $this->json->fetchAll(function ($row) use ($name, $language) {
            $return = true;
            if($name && $row["name"] != $name) {
                $return = false;
            }
            if($language && $row["language"] != $language) {
                $return = false;
            }
            return $return;
        });

        if(count($data) && $data[0]["id"]) {
            $this->assignVariablesToModel($data[0]);
        } else {
            throw new \Exception("Predefined asset metadata with name: " . $name . " and language: " . $language . " does not exist");
        }
    }

    /**
     * @throws \Exception
     */
    public function save() {

        $ts = time();
        if(!$this->model->getCreationDate()) {
            $this->model->setCreationDate($ts);
        }
        $this->model->setModificationDate($ts);

        try {
            $dataRaw = get_object_vars($this->model);
            $data = [];
            $allowedProperties = ["id","name","description","language","type","data",
                "targetSubtype","config","creationDate","modificationDate"];

            foreach($dataRaw as $key => $value) {
                if(in_array($key, $allowedProperties)) {
                    $data[$key] = $value;
                }
            }
            $this->json->insertOrUpdate($data, $this->model->getId());
        }
        catch (\Exception $e) {
            throw $e;
        }

        if(!$this->model->getId()) {
            $this->model->setId($this->json->getLastInsertId());
        }
    }

    /**
     * Deletes object from database
     *
     * @return void
     */
    public function delete() {
        $this->json->delete($this->model->getId());
    }
}
