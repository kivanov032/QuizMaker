<?php

namespace Tests\Unit;

use App\Helpers\MailHelper;
use PHPUnit\Framework\TestCase;
use Random\RandomException;

class HelperTest extends TestCase
{

    /**
     * Тест на генерацию 6-разрядного числа
     * @throws RandomException
     */
    public function test_GenerateVerificationCodeReturnsSixDigits(): void
    {
        $code = MailHelper::generateVerificationCode();
        $this->assertEquals(6, strlen($code));
        $this->assertMatchesRegularExpression('/^\d{6}$/', $code);
    }
}
