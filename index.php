<?php
declare(strict_types=1);

ini_set('display_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/App/autoload.php';

use App\MainApp;
use Controllers\AuthController;
use Controllers\RtmpController;
use Controllers\DeviceController;
use Controllers\DashBoardController;
use Controllers\FilesController;

$app = new MainApp();

$app->addRoute('/rtmpauth-nms', [RtmpController::class]);
$app->addRoute('/rtmpublishpauth', [RtmpController::class, 'publishAuth'], false);
// $app->addRoute('/rtmpplayhauth', RtmpController::class, false, 'playAuth');
$app->addRoute('/auth', [AuthController::class], false);
$app->addRoute('/device', [DeviceController::class]);
$app->addRoute('/dashboard', [DashBoardController::class]);
$app->addRoute('/register', [AuthController::class], false);
// $app->addRoute('/testing', TestController::class, false);
$app->addRoute('/upload', [FilesController::class]); //to deprication
$app->addRoute('/files', [FilesController::class]);
$app->addRoute('/profile', [AuthController::class]);
// $app->addRoute('/steps', StepsController::class);
// $app->addRoute('/filesops', FilesController::class);

// $app->addRoute('/auth1', function () {
//     return 'Callback function';
//     pprint($this);
// });

// sleep(2);
$app->run();
