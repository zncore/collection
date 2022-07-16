<?php

namespace ZnCore\Collection\Helpers;

use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Expr\Comparison;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use ZnCore\Code\Helpers\PropertyHelper;
use ZnCore\Collection\Interfaces\Enumerable;
use ZnCore\Collection\Libs\Collection;

/**
 * Хэлпер для работы с коллекциями.
 */
class CollectionHelper
{

    /**
     * Фильтрация коллекции по условию.
     *
     * @param Enumerable $collection
     * @param string $field Имя поля
     * @param string $operator Оператор сравнения
     * @param mixed $value Значение
     * @return Enumerable
     */
    public static function where(Enumerable $collection, $field, $operator, $value)
    {
        $expr = new Comparison($field, $operator, $value);
        $criteria = new Criteria();
        $criteria->andWhere($expr);
        return $collection->matching($criteria);
    }

    /**
     * Слияние коллекций.
     *
     * @param Enumerable $collection Коллекция 1
     * @param Enumerable $source Коллекция 2
     * @return Enumerable
     */
    public static function merge(Enumerable $collection, Enumerable $source): Enumerable
    {
        $result = clone $collection;
        self::appendCollection($result, $source);
        return $result;
    }

    /**
     * Добавить коллекцию элементов.
     *
     * @param Enumerable $collection Исходная коллекция
     * @param Enumerable $source Добавляемая коллекция
     */
    public static function appendCollection(Enumerable $collection, Enumerable $source): void
    {
        foreach ($source as $item) {
            $collection->add($item);
        }
    }

    /**
     * Разделить коллекцию на куски.
     *
     * @param Enumerable $collection Исходная коллекция
     * @param int $size Размер куска
     * @return Enumerable Коллекция коллекций
     */
    public static function chunk(Enumerable $collection, int $size): Enumerable
    {
        if ($size <= 0) {
            return new Collection();
        }
        $chunks = [];
        foreach (array_chunk($collection->toArray(), $size, true) as $chunk) {
            $chunks[] = new Collection($chunk);
        }
        return new Collection($chunks);
    }

    /**
     * Преобразовать коллекцию в индексированный массив.
     * 
     * @param Enumerable $collection Исходная коллекция
     * @param string $fieldName Имя поля для индекса (должно быть уникальным)
     * @return array
     */
    public static function indexing(Enumerable $collection, string $fieldName): array
    {
        $array = [];
        foreach ($collection as $item) {
            $pkValue = PropertyHelper::getValue($item, $fieldName);
            $array[$pkValue] = $item;
        }
        return $array;
    }

    /**
     * Создать коллекцию сущностей.
     * 
     * @param string $entityClass Имя класса сущности
     * @param array $data Массив значений атрибутов сущности
     * @param array $filedsOnly Назначать только указанные атрибуты
     * @return Enumerable
     */
    public static function create(string $entityClass, array $data = [], array $filedsOnly = []): Enumerable
    {
        foreach ($data as $key => $item) {
            $entity = new $entityClass;
            PropertyHelper::setAttributes($entity, $item, $filedsOnly);
            $data[$key] = $entity;
        }
        $collection = new Collection($data);
        return $collection;
    }

    /**
     * Преобразовать коллекцию в массив.
     * 
     * @param Enumerable $collection Исходная коллекция
     * @return array
     */
    public static function toArray(Enumerable $collection): array
    {
        $serializer = new Serializer([new ObjectNormalizer()]);
        $normalizeHandler = function ($value) use ($serializer) {
            return $serializer->normalize($value);
            //return is_object($value) ? EntityHelper::toArray($value) : $value;
        };
        $normalizeCollection = $collection->map($normalizeHandler);
        return $normalizeCollection->toArray();
    }

    /**
     * Получить массив значений одного атрибута.
     * 
     * @param Enumerable $collection Исходная коллекция
     * @param string $key Имя атрибута
     * @return array Массив значений атрибута
     */
    public static function getColumn(Enumerable $collection, string $key): array
    {
        $array = [];
        foreach ($collection as $entity) {
            $array[] = PropertyHelper::getValue($entity, $key);
        }
        $array = array_values($array);
        return $array;
    }
}
