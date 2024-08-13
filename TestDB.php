<?php

declare(strict_types=1);

ini_set('display_errors', 1);
error_reporting(E_ALL | ~E_NOTICE);

global $settings;
require __DIR__ . '/App/settings.php';
require __DIR__ . '/App/autoload.php';
require __DIR__ . '/vendor/autoload.php';


use PHPUnit\Framework\TestCase;
use App\DataBase;
use Models\TestModel;

global $errorHandler;


class TestDB extends TestCase
{

    /** @var TestModel */
    private $model;

    public function setUp(): void
    {
        $this->model = new TestModel();
    }

    private function log($var)
    {
        fwrite(STDERR, print_r($var, TRUE));
    }

    private function _connectToDB(): bool
    {
        global $settings;
        // include __DIR__ . '/App/settings.php';
        // $this->log($settings);
        return DataBase::setConnection(
            $settings['DB']['dbhost'],
            $settings['DB']['dbbase'],
            $settings['DB']['dbuser'],
            $settings['DB']['dbpass'],
            $settings['DB']['dblevel']
        );
        $this->model = new TestModel();
    }

    public function testConnectToDB()
    {
        $result = $this->_connectToDB();
        // $error = DataBase::getLastError();
        $this->assertTrue($result, "Error connect to DB");
    }

    public function testCreateTestTable()
    {
        $result = $this->model->createTable();
        $this->assertTrue($result, "Error create table");
    }

    public function testInserty()
    {
        $result = $this->model->insertRows();
        $this->assertTrue($result, "Error insert into table");
    }

    public function testSelectAfterInsert()
    {
        $result = $this->model->select1();
        $this->assertTrue($result, "Error select");
    }

    public function testUpdate()
    {
        $result = $this->model->updateRows();
        $this->assertTrue($result, "Error update table");
    }

    public function testSelectAfterUpdate()
    {
        $result = $this->model->select2();
        $this->assertTrue($result, "Error select");
    }

    public function testStoredProcedure()
    {
        $result = $this->model->callStoredProcedure();
        $this->assertTrue($result, "Error call procedure");
    }    

    public function testSelectAfterProcedure()
    {
        $result = $this->model->select3();
        $this->assertTrue($result, "Error select");
    }

    public function testInsertDTO()
    {
        $result = $this->model->insertDTO();
        $this->assertTrue($result, "Error select");
    }

    public function testSelectAfterInsertPDO()
    {
        $result = $this->model->select4();
        $this->assertTrue($result, "Error select");
    }
}

// $a = new TestDB('');
// $a->testConnectToDB();
