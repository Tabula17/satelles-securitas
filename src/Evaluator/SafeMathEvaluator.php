<?php

namespace Tabula17\Satelles\Securitas\Evaluator;

use Tabula17\Satelles\Securitas\Exception\MathEvaluationException;

class SafeMathEvaluator
{
    private const string ALLOWED_CHARS = '/^[\d+\-*\/\s().,]+$/';
    private const int MAX_EXPRESSION_LENGTH = 100;

    /**
     * Evalúa una expresión matemática de forma segura
     *
     * @throws MathEvaluationException Si la expresión no es segura
     */
    public function evaluate(string $expression): float
    {
        $this->validateExpression($expression);

        // Eliminar espacios y validar estructura
        $expression = str_replace(' ', '', $expression);

        return $this->compute($expression);
    }

    private function validateExpression(string $expression): void
    {
        if (strlen($expression) > self::MAX_EXPRESSION_LENGTH) {
            throw new MathEvaluationException("Expresión demasiado larga");
        }

        if (!preg_match(self::ALLOWED_CHARS, $expression)) {
            throw new MathEvaluationException("Caracteres no permitidos en la expresión");
        }

        // Validar paréntesis balanceados
        if (substr_count($expression, '(') !== substr_count($expression, ')')) {
            throw new MathEvaluationException("Paréntesis no balanceados");
        }
    }

    private function compute(string $expression): float
    {
        // Implementación segura paso a paso
        $result = 0;
        $tokens = $this->tokenize($expression);
        $stack = new \SplStack();

        // Algoritmo Shunting-yard para notación polaca inversa
        $rpn = $this->convertToRPN($tokens);

        // Evaluación de la notación polaca inversa
        foreach ($rpn as $token) {
            if (is_numeric($token)) {
                $stack->push((float)$token);
            } else {
                $b = $stack->pop();
                $a = $stack->pop();
                $stack->push($this->applyOperator($token, $a, $b));
            }
        }

        return $stack->pop();
    }

    private function tokenize(string $expression): array
    {
        // Implementación segura de tokenización
        $tokens = [];
        $current = '';

        foreach (str_split($expression) as $char) {
            if (is_numeric($char) || $char === '.') {
                $current .= $char;
            } else {
                if ($current !== '') {
                    $tokens[] = $current;
                    $current = '';
                }
                $tokens[] = $char;
            }
        }

        if ($current !== '') {
            $tokens[] = $current;
        }

        return $tokens;
    }

    private function convertToRPN(array $tokens): array
    {
        // Implementación segura del algoritmo Shunting-yard
        $output = [];
        $operators = new \SplStack();
        $precedence = ['+' => 1, '-' => 1, '*' => 2, '/' => 2];

        foreach ($tokens as $token) {
            if (is_numeric($token)) {
                $output[] = $token;
            } elseif ($token === '(') {
                $operators->push($token);
            } elseif ($token === ')') {
                while ($operators->top() !== '(') {
                    $output[] = $operators->pop();
                }
                $operators->pop();
            } else {
                while (!$operators->isEmpty() &&
                    $operators->top() !== '(' &&
                    $precedence[$token] <= $precedence[$operators->top()]) {
                    $output[] = $operators->pop();
                }
                $operators->push($token);
            }
        }

        while (!$operators->isEmpty()) {
            $output[] = $operators->pop();
        }

        return $output;
    }

    private function applyOperator(string $operator, float $a, float $b): float
    {
        return match ($operator) {
            '+' => $a + $b,
            '-' => $a - $b,
            '*' => $a * $b,
            '/' => $b != 0 ? $a / $b : throw new MathEvaluationException("División por cero"),
            default => throw new MathEvaluationException("Operador no válido")
        };
    }
}
