<?php


namespace App\Doctrine\ORM\Functions;

use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;

/**
 * Parse SQL Variadic functions.
 */
trait VariadicFunctionTrait
{
    protected array $expressions = [];

    /**
     * Parse a variadic function.
     *
     * @param Parser $parser
     * @param callable(Parser):mixed $valueGetter
     *  Likely `$parser->ArithmeticPrimary()`, but may be others as well.
     *
     * @throws \Doctrine\ORM\Query\QueryException
     */
    protected function parseVariadic(Parser $parser, callable $valueGetter): void
    {
        $this->expressions = [];
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);
        for (; ;) {
            $this->expressions[] = $valueGetter($parser);
            if ($parser->getLexer()->isNextToken(Lexer::T_CLOSE_PARENTHESIS)) {
                $parser->match(Lexer::T_CLOSE_PARENTHESIS);
                break;
            }
            $parser->match(Lexer::T_COMMA);
        };
    }

    /**
     * Generate the SQL
     *
     * @param string $name
     *  SQL function name.
     * @param SqlWalker $sqlWalker
     *
     * @return string
     */
    protected function getVariadicSql(string $name, SqlWalker $sqlWalker): string
    {
        return sprintf(
            '%s(%s)',
            $name,
            implode(', ', array_map(fn($expr) => $expr->dispatch($sqlWalker), $this->expressions))
        );
    }
}
