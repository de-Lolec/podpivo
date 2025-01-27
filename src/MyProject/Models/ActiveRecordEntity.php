<?php

namespace MyProject\Models;

use MyProject\Services\Db;

abstract class ActiveRecordEntity
{
    /** @var int */
    protected $id;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    public function __set(string $name, $value)
    {
        $camelCaseName = $this->underscoreToCamelCase($name);
        $this->$camelCaseName = $value;
    }

    private function underscoreToCamelCase(string $source): string
    {
        return lcfirst(str_replace('_', '', ucwords($source, '_')));
    }

    /**
     * @return static[]
     */
    public static function findAll(): array
    {
        $db = $db = Db::getInstance();
        return $db->query('SELECT * FROM `' . static::getTableName() . '`;', [], static::class);
    }

    /**
     * @param int $id
     * @return static|null
     */
    public static function getById(int $id): ?self
    {
        $db = $db = Db::getInstance();
        $entities = $db->query(
            'SELECT * FROM `' . static::getTableName() . '` WHERE id=:id;',
            [':id' => $id],
            static::class
        );
        return $entities ? $entities[0] : null;
    }
    public static function getByIdComment($filmId): array
    {
        $db = $db = Db::getInstance();
        return $db->query(
            'SELECT * FROM `' . static::getTableName() . '` WHERE film_id=:id;',
            [':id' => $filmId],
            static::class
        );

    }
    public static function getIdByOrig(?string $origName): ?self{
        $db = $db = Db::getInstance();
        $origName = $db->query(
            'SELECT * FROM `' . static::getTableName() . '` WHERE orig_name=:id;',
            [':id' => $origName],
            static::class
        );
        return $origName ? $origName[0] : null;
    }
    public static function getCommentCheck(string $textComm){
        $db = $db = Db::getInstance();
        $comment = $db->query(
            'SELECT * FROM `' . static::getTableName() . '` WHERE comment=:id;',
            [':id' => $textComm],
            static::class
        );
        return $comment ? $comment[0] : null;
    }
    public static function getUrlCheck(string $url){
        $db = $db = Db::getInstance();
        $url = $db->query(
            'SELECT * FROM `' . static::getTableName() . '` WHERE urllive=:id;',
            [':id' => $url],
            static::class
        );
        return $url ? $url[0] : null;
    }

    public static function sortMain($pivgrade, $genre, $country, $grade, $itemsPerPage, $number){
        $endSort = '';
        $endSortCount = '';
       if(!empty($pivgrade) && !empty($genre)){
           $endSort = self::getSortPlusA($genre, $pivgrade, $itemsPerPage, $number);
           $endSortCount = self::CountGetSortPlusA($genre, $pivgrade, $itemsPerPage, $number);
       }elseif (!empty($grade) && !empty($genre)){
           $endSort = self::getSortPlusA($genre, $grade, $itemsPerPage, $number);
           $endSortCount = self::CountGetSortPlusA($genre, $grade, $itemsPerPage, $number);
       } elseif (!empty($grade)){
           $endSort = self::getSortPlus($grade, $itemsPerPage, $number);
           $endSortCount = self::CountGetSortPlus($grade, $itemsPerPage, $number);
       }elseif (!empty($genre)){
           $endSort = self::getSort($genre, $itemsPerPage, $number);
           $endSortCount = self::CountGetSort($genre, $itemsPerPage, $number);
       }elseif(!empty($pivgrade)){
            $endSort = self::getSortPlus($pivgrade, $itemsPerPage, $number);
            $endSortCount = self::CountGetSortPlus($pivgrade, $itemsPerPage, $number);
        }
        return [
          'sort' => $endSort,
          'count' => $endSortCount,
        ];
    }

    public static function getSort($sort, $itemsPerPage, $pageNum){

        $db = $db = Db::getInstance();
        return $db->query(
            'SELECT * FROM `' . static::getTableName() . '` WHERE '. key($sort) . ' LIKE :genre LIMIT :iPerPage OFFSET :Num;',
            [':genre' => implode('',($sort[key($sort)])),
                ':iPerPage' => $itemsPerPage,
                ':Num' => ($pageNum - 1) * $itemsPerPage,
            ],
            static::class
        );
    }

    public static function getSortPlus($sortPlus, $itemsPerPage, $pageNum){

        $db = $db = Db::getInstance();
        return $db->query(
            'SELECT * FROM `' . static::getTableName() . '` ORDER BY '. $sortPlus  . ' DESC LIMIT :iPerPage OFFSET :Num;',
            [ ':iPerPage' => $itemsPerPage,
                ':Num' => ($pageNum - 1) * $itemsPerPage,
                ],
            static::class
        );
    }

    public static function getSortPlusA($genre, $type, $itemsPerPage, $pageNum){

        $db = $db = Db::getInstance();
        return $db->query(
            'SELECT * FROM `' . static::getTableName() . '` WHERE '. array_key_first($genre) . ' LIKE :id AND ' . array_key_last($genre) . ' LIKE :idi ORDER BY ' . $type . ' DESC LIMIT :iPerPage OFFSET :Num;',
            [':id' => '%' . implode('',(reset($genre))),
                ':idi' => '%' . implode('',end($genre)),
                ':iPerPage' => $itemsPerPage,
                ':Num' => ($pageNum - 1) * $itemsPerPage,
            ],
            static::class
        );
    }

    public static function CountGetSort($sort, $itemsPerPage, $pageNum){

        $db = $db = Db::getInstance();
        $result = $db->query('SELECT COUNT(*) AS cnt FROM `' . static::getTableName() . '` WHERE '. key($sort) . ' LIKE :genre;',
            [':genre' => implode('',($sort[key($sort)])),
                ],
            static::class
        );
        return ceil($result[0]->cnt / $itemsPerPage);
    }

    public static function CountGetSortPlus($sortPlus, $itemsPerPage, $pageNum){

        $db = $db = Db::getInstance();
        $result = $db->query('SELECT COUNT(*) AS cnt FROM `' . static::getTableName() . '` ORDER BY '. $sortPlus  . ' DESC;',
            [],
            static::class
        );
        return ceil($result[0]->cnt / $itemsPerPage);
    }

    public static function CountGetSortPlusA($genre, $type, $itemsPerPage, $pageNum){

        $db = $db = Db::getInstance();
        $result = $db->query('SELECT COUNT(*) AS cnt FROM `' . static::getTableName() . '` WHERE '. array_key_first($genre) . ' LIKE :id AND ' . array_key_last($genre) . ' LIKE :idi;',
            [':id' => '%' . implode('',(reset($genre))),
                ':idi' => '%' . implode('',end($genre))
            ],
            static::class
        );
        return ceil($result[0]->cnt / $itemsPerPage);
    }


    public function save(): void
    {
        $mappedProperties = $this->mapPropertiesToDbFormat();
        if ($this->id !== null) {
            $this->update($mappedProperties);
        } else {
            $this->insert($mappedProperties);
        }
    }

    public static function getPagesCount(int $itemsPerPage): int
    {
        $db = Db::getInstance();
        $result = $db->query('SELECT COUNT(*) AS cnt FROM ' . static::getTableName() . ';');
        return ceil($result[0]->cnt / $itemsPerPage);
    }
    
    public static function getPage(int $pageNum, int $itemsPerPage): array
    {
        $db = $db = Db::getInstance();
        return $db->query(
            sprintf(
                'SELECT * FROM `%s` ORDER BY id DESC LIMIT %d OFFSET %d;',
                static::getTableName(),
                $itemsPerPage,
                ($pageNum - 1) * $itemsPerPage
            ),
            [],
            static::class
        );
    }

    abstract protected static function getTableName(): string;

    private function update(array $mappedProperties): void
    {
        $columns2params = [];
        $params2values = [];
        $index = 1;
        foreach ($mappedProperties as $column => $value) {
            $param = ':param' . $index; // :param1
            $columns2params[] = $column . ' = ' . $param; // column1 = :param1
            $params2values[$param] = $value; // [:param1 => value1]
            $index++;
        }
        $sql = 'UPDATE ' . static::getTableName() . ' SET ' . implode(', ', $columns2params) . ' WHERE id = ' . $this->id;
        $db = Db::getInstance();
        $db->query($sql, $params2values, static::class);
    }

    private function insert(array $mappedProperties): void
    {
        $filteredProperties = array_filter($mappedProperties);

        $columns = [];
        $paramsNames = [];
        $params2values = [];
        foreach ($filteredProperties as $columnName => $value) {
            $columns[] = '`' . $columnName. '`';
            $paramName = ':' . $columnName;
            $paramsNames[] = $paramName;
            $params2values[$paramName] = $value;
        }

        $columnsViaSemicolon = implode(', ', $columns);
        $paramsNamesViaSemicolon = implode(', ', $paramsNames);

        $sql = 'INSERT INTO ' . static::getTableName() . ' (' . $columnsViaSemicolon . ') VALUES (' . $paramsNamesViaSemicolon . ');';

        $db = Db::getInstance();
        $db->query($sql, $params2values, static::class);
        $this->id = $db->getLastInsertId();

    }


    private function mapPropertiesToDbFormat(): array
    {
        $reflector = new \ReflectionObject($this);
        $properties = $reflector->getProperties();

        $mappedProperties = [];
        foreach ($properties as $property) {
            $propertyName = $property->getName();
            $propertyNameAsUnderscore = $this->camelCaseToUnderscore($propertyName);
            $mappedProperties[$propertyNameAsUnderscore] = $this->$propertyName;
        }

        return $mappedProperties;
    }

    public static function findOneByColumn(string $columnName, $value): ?self
    {
        $db = Db::getInstance();
        $result = $db->query(
            'SELECT * FROM `' . static::getTableName() . '` WHERE `' . $columnName . '` = :value LIMIT 1;',
            [':value' => $value],
            static::class
        );
        if ($result === []) {
            return null;
        }
        return $result[0];
    }

    private function camelCaseToUnderscore(string $source): string
    {
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $source));
    }
}