<?php

declare(strict_types=1);

namespace Models;

use Exception;
use DTO\TestDTO;

class TestModel extends BasicModel
{
	protected $_table = 'testTableRows';
	protected $_id = 'row_id';
	protected $_fields = ['row_id', 'row_name', 'row_descr', 'row_status'];


	public function createTable() {
		$query1 = "DROP TABLE IF EXISTS `testTableRows`;";
		$query2 = "CREATE TABLE `testTableRows` (
            `row_id` INT(10) NOT NULL AUTO_INCREMENT,
            `row_name` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
			`row_descr` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
			`row_status` TINYINT NULL DEFAULT NULL,
            PRIMARY KEY (`row_id`) USING BTREE
        )
        COLLATE='utf8mb4_unicode_ci'
        ENGINE=InnoDB;";
		$query3 = "DROP PROCEDURE IF EXISTS `testProcedure`;";
		$query4 = "CREATE PROCEDURE `testProcedure`(
			IN `p_row_id` INT,
			IN `p_row_name` VARCHAR(255)
		)
		BEGIN
			DECLARE ex_row_id INT;
			
			SELECT row_id INTO ex_row_id 
			FROM testTableRows
			WHERE row_id = p_row_id;
					
			IF(ex_row_id IS NULL) THEN
				INSERT INTO testTableRows(row_name) VALUES(p_row_name);
			ELSE
				UPDATE testTableRows SET row_name = p_row_name
					WHERE row_id = p_row_id;
			END IF;
		END";
		
		try {
			$result = $this->queryRaw($query1);
			$result = $this->queryRaw($query2);
			$result = $this->queryRaw($query3);
			$result = $this->queryRaw($query4);
			return true;
		}
		catch (Exception $e) {
			return $e;
		}
	}

	public function insertRows() {
		for($i=1;$i<=10;$i++) {
			$this->_fields['row_name'] = 'Name' . $i;
			try {
				$this->insert();
			}
			catch(Exception $e) {
				return $e;
			}
		}

		try {
			$this->fieldsSet(['row_name' => 'CustomName'])->insert();
		}
		catch(Exception $e) {
			return $e;
		}

		return true;
	}

	public function select1() {
		$result = false;
		try {
			$result = $this->getAll();
			if(count($result) != 11) return $result;
			$result = $this->getOne();
			if(!isset($result['row_id'])) return $result;
		}
		catch(Exception $e) {
			return $e;
		}
		return true;
	}

	public function updateRows() {
		$this->_fields['row_id'] = 5;
		$this->_fields['row_name'] = 'Name5new';
		try {
			$this->update();
			$this->from('testTableRows')->fieldsSet(['row_name' => 'Name6new'])
				->where('row_id = :row_id', ['row_id' => 6])->update();
		}
		catch(Exception $e) {
			return $e;
		}
		return true;
	}

	public function select2() {
		$result = false;
		try {
			$result = $this->from('testTableRows')->fields('row_name')
				->where('row_name like :row_name', ['row_name' => '%new%'])->orderBy('row_name DESC')
				->limit(0,1)->getAll();
			if(count($result) == 0) return false;
		}
		catch(Exception $e) {
			return $e;
		}
		return true;
	}

	public function callStoredProcedure() {
		try {
			$this->callPrc("testProcedure",['row_id' => 1, 'row_name' => 'FromPRC']);
			$this->callPrc("testProcedure",['row_id' => 0, 'row_name' => 'FromPRC']);
		}
		catch(Exception $e) {
			return $e;
		}
		return true;
	}

	public function select3() {
		$result = false;
		try {
			$query = 'SELECT * FROM testTableRows WHERE row_name like :row_name';
			$result = $this->setQuery($query, ['row_name' => '%FromPRC%'])->getAll();
			if(count($result) != 2) return false;
		}
		catch(Exception $e) {
			return $e;
		}
		return true;
	}

	public function insertDTO() {
		$ids = [];
		$testVar = new TestDTO(['row_id' => 0, 'row_name' => 'FromPDO1']);
		$this->loadData($testVar);
		$this->upsert();
		$ids[0] = $this->getLastID();
		$testVar->row_name = 'FromPDO2';
		$this->loadData($testVar);
		$this->upsert();
		$ids[1] = $this->getLastID();
		$testVar->row_name = 'FromPDO3';
		$this->upsert($testVar);
		$ids[3] = $this->getLastID();		
		foreach($ids as $id) {
			$testVar->row_id = $id;
			$testVar->row_name = 'FromPDO';
			$this->loadData($testVar);
			$this->upsert();
		}
		return true;
	}

	public function select4() {
		$result = false;
		try {
			$query = 'SELECT * FROM testTableRows WHERE row_name like :row_name';
			$result = $this->setQuery($query, ['row_name' => '%FromPDO%'])->getAll();
			if(count($result) != 3) return false;
		}
		catch(Exception $e) {
			return $e;
		}
		return true;
	}
}
