<?php

namespace DataProvider\Infrastructure\Sql;

interface SqlPostSpecification
{
    /**
     * @return string
     */
    public function toSqlClauses();
}