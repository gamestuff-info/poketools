<?php

namespace App\Doctrine\ORM\Functions;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;

/**
 * SUM(value_1, value_2, ..., value_n)
 */
class ValueSum extends FunctionNode
{
    use VariadicFunctionTrait;

    /**
     * @inheritDoc
     */
    public function parse(Parser $parser)
    {
        $this->parseVariadic($parser, fn(Parser $parser): mixed => $parser->ArithmeticPrimary());
    }

    /**
     * @inheritDoc
     */
    public function getSql(SqlWalker $sqlWalker)
    {
        return $this->getVariadicSql('SUM', $sqlWalker);
    }
}
