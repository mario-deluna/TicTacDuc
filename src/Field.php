<?php

namespace TicTacDuc;

class Field
{
    public static $available = [
        '0x0', '0x1', '0x2',
        '1x0', '1x1', '1x2',
        '2x0', '2x1', '2x2',
    ];

    public static function render(array $data)
    {
        foreach (static::$available as $cord) {
            if (!isset($data[$cord])) {
                $data[$cord] = '';
            }
        }

        ob_start();

        require __DIR__ . '/../view.php';

        return ob_get_clean();
    }
}
