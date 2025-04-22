<?php
namespace App\ModelQuery;

use Illuminate\Support\Facades\DB;

class PromotionModel
{
    public function caculatorParallel($value1, $code, $value2)
    {
        switch ($code) {
            case '+':
                return $value1 + $value2;
            case '-':
                return $value1 - $value2;
            case '*':
                return $value1 * $value2;
            case '/':
                return $value2 != 0 ? $value1 / $value2 : 'Lỗi: chia cho 0';
            case '>':
                return $value1 > $value2;
            case '<':
                return $value1 < $value2;
            case '==':
                return $value1 == $value2;
            case '===':
                return $value1 === $value2;
            default:
                return 'Toán tử không hợp lệ';
        }
    }
}
