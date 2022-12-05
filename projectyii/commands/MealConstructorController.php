<?php

namespace app\commands;

use app\models\MealConstructor;
use yii\console\Controller;

class MealConstructorController extends Controller
{
    public function actionMakeVariantsFromCode(string $code = 'dcciii')
    {
        echo json_encode(MealConstructor::getVariations($code), JSON_UNESCAPED_UNICODE + JSON_PRETTY_PRINT);
    }
}