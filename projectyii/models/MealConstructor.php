<?php

namespace app\models;

use yii\base\Model;

class MealConstructor extends Model
{
    /**
     * @param string $code Код набора ингредиентов.
     *
     * @return array Массив вариаций.
     *
     * @throws \Exception
     */
    public static function getVariations(string $code): array
    {
        $config = self::parseCode($code);

        $types = IngredientType::find()
            ->where(['code' => array_keys($config)])
            ->indexBy('id')
            ->asArray()
            ->all();

        if ($diff = array_diff(array_keys($config), array_column($types, 'code'))) {
            throw new \Exception(sprintf('Types of ingredients [%s] are not presented in database.', implode(',', $diff)));
        }

        $ingredients = Ingredient::find()
            ->where(['type_id' => array_keys($types)])
            ->indexBy('id')
            ->asArray()
            ->all();

        $combinationsByType = [];

        foreach ($types as $typeId => $type) {
            $ingredientsByType = array_filter($ingredients, function ($ingredient) use ($typeId) {
                return $ingredient['type_id'] == $typeId;
            });

            $ingredientIds = array_column($ingredientsByType, 'id');

            if (count($ingredientsByType) < $config[$type['code']]) {
                throw new \Exception(sprintf("Number of wanted elements of type '%s' is greater then available in database.", $type['title']));
            }

            $combinationsByType[$typeId] = self::getCombinationsOfSize($ingredientIds, $config[$type['code']]);
        }

        $variations = self::crossJoin($combinationsByType);

        $result = [];

        foreach ($variations as $variation) {
            $element = [
                'products' => [],
                'price' => 0
            ];
            foreach ($variation as $typeId => $ingredientsJson) {
                $ingredientIds = json_decode($ingredientsJson, true);
                foreach ($ingredientIds as $ingredientId) {
                    $element['products'] [] = [
                        'type' => $types[$typeId]['title'],
                        'value' => $ingredients[$ingredientId]['title']
                    ];
                    $element['price'] += $ingredients[$ingredientId]['price'];
                }
            }
            $result []= $element;
        }

        return $result;
    }

    /**
     * Taken from Laravel Arr::crossJoin.
     * Cross join the given arrays, returning all possible permutations.
     *
     * @param  array $array
     * @return array
     */
    public static function crossJoin(array $array): array
    {
        $results = [[]];

        foreach ($array as $index => $subArray) {
            $append = [];

            foreach ($results as $product) {
                foreach ($subArray as $item) {
                    $product[$index] = $item;

                    $append[] = $product;
                }
            }

            $results = $append;
        }

        return $results;
    }

    /**
     * Преобразует строку с набором ингредиентов в массив 'Код элемента' => количество элементов.
     *
     * @param string $code
     * @return array
     */
    private static function parseCode(string $code): array
    {
        $config = [];
        $elems = str_split($code);
        foreach ($elems as $elem) {
            if (isset($config[$elem])) {
                $config[$elem]++;
            } else {
                $config[$elem] = 1;
            }
        }

        return $config;
    }

    /**
     * @param array $array Входной массив значений.
     * @param array $data Входной массив - элемент сочетания.
     * @param int $start Индекс начального элемента итерации.
     * @param int $end Индекс конечного элемента итерации.
     * @param int $index Индекс текущего элемента итерации.
     * @param array $result Результирующий массив.
     *
     * @return void
     */
    public static function getCombinations(array $array, array $data, int $start, int $end, int $index, array &$result)
    {
        if ($index == count($data)) {
           $result[] = json_encode($data);
        } else {
            if ($start <= $end) {
                $data[$index] = $array[$start];

                self::getCombinations($array, $data, $start+1, $end, $index+1, $result);
                self::getCombinations($array, $data, $start+1, $end, $index, $result);
            }
        }
    }

    /**
     * Возвращает все сочетания элементов массива $array длины $size.
     *
     * @param array $array Входной массив.
     * @param int $size Размер сочетаний.
     *
     * @return array
     */
    public static function getCombinationsOfSize(array $array, int $size): array
    {
        $data = array_fill(0, $size, "");
        $result = [];
        self::getCombinations($array, $data,0,sizeof($array) - 1,0,$result);
        return $result;
    }
}