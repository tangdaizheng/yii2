<?php

namespace yiiunit\framework\validators;

use DateTime;
use yii\validators\DateValidator;
use yiiunit\data\validators\models\FakedValidationModel;
use yiiunit\framework\i18n\IntlTestHelper;
use yiiunit\TestCase;

/**
 * @group validators
 */
class DateValidatorTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();

        IntlTestHelper::setIntlStatus($this);

        $this->mockApplication([
            'timeZone' => 'UTC',
            'language' => 'ru-RU',
        ]);
    }

    protected function tearDown()
    {
        parent::tearDown();
        IntlTestHelper::resetIntlStatus();
    }

    public function testEnsureMessageIsSet()
    {
        $val = new DateValidator;
        $this->assertTrue($val->message !== null && strlen($val->message) > 1);
    }

    /**
     * @dataProvider provideTimezones
     */
    public function testIntlValidateValue($timezone)
    {
        date_default_timezone_set($timezone);
        $this->testValidateValue($timezone);

        $this->mockApplication([
            'language' => 'en-GB',
            'components' => [
                'formatter' => [
                    'dateFormat' => 'short',
                ]
            ]
        ]);
        $val = new DateValidator();
        $this->assertTrue($val->validate('31/5/2017'));
        $this->assertFalse($val->validate('5/31/2017'));
        $val = new DateValidator(['format' => 'short', 'locale' => 'en-GB']);
        $this->assertTrue($val->validate('31/5/2017'));
        $this->assertFalse($val->validate('5/31/2017'));

        $this->mockApplication([
            'language' => 'de-DE',
            'components' => [
                'formatter' => [
                    'dateFormat' => 'short',
                ]
            ]
        ]);
        $val = new DateValidator();
        $this->assertTrue($val->validate('31.5.2017'));
        $this->assertFalse($val->validate('5.31.2017'));
        $val = new DateValidator(['format' => 'short', 'locale' => 'de-DE']);
        $this->assertTrue($val->validate('31.5.2017'));
        $this->assertFalse($val->validate('5.31.2017'));
    }

    /**
     * @dataProvider provideTimezones
     */
    public function testValidateValue($timezone)
    {
        date_default_timezone_set($timezone);

        // test PHP format
        $val = new DateValidator(['format' => 'php:Y-m-d']);
        $this->assertFalse($val->validate('3232-32-32'));
        $this->assertTrue($val->validate('2013-09-13'));
        $this->assertFalse($val->validate('31.7.2013'));
        $this->assertFalse($val->validate('31-7-2013'));
        $this->assertFalse($val->validate('20121212'));
        $this->assertFalse($val->validate('asdasdfasfd'));
        $this->assertFalse($val->validate('2012-12-12foo'));
        $this->assertFalse($val->validate(''));
        $this->assertFalse($val->validate(time()));
        $val->format = 'php:U';
        $this->assertTrue($val->validate(time()));
        $val->format = 'php:d.m.Y';
        $this->assertTrue($val->validate('31.7.2013'));
        $val->format = 'php:Y-m-!d H:i:s';
        $this->assertTrue($val->validate('2009-02-15 15:16:17'));

        // test ICU format
        $val = new DateValidator(['format' => 'yyyy-MM-dd']);
        $this->assertFalse($val->validate('3232-32-32'));
        $this->assertTrue($val->validate('2013-09-13'));
        $this->assertFalse($val->validate('31.7.2013'));
        $this->assertFalse($val->validate('31-7-2013'));
        $this->assertFalse($val->validate('20121212'));
        $this->assertFalse($val->validate('asdasdfasfd'));
        $this->assertFalse($val->validate('2012-12-12foo'));
        $this->assertFalse($val->validate(''));
        $this->assertFalse($val->validate(time()));
        $val->format = 'dd.MM.yyyy';
        $this->assertTrue($val->validate('31.7.2013'));
        $val->format = 'yyyy-MM-dd HH:mm:ss';
        $this->assertTrue($val->validate('2009-02-15 15:16:17'));
    }

    /**
     * @dataProvider provideTimezones
     */
    public function testIntlValidateAttributePHPFormat($timezone)
    {
        $this->testValidateAttributePHPFormat($timezone);
    }

    /**
     * @dataProvider provideTimezones
     */
    public function testValidateAttributePHPFormat($timezone)
    {
        date_default_timezone_set($timezone);

        // error-array-add
        $val = new DateValidator(['format' => 'php:Y-m-d']);
        $model = new FakedValidationModel;
        $model->attr_date = '2013-09-13';
        $val->validateAttribute($model, 'attr_date');
        $this->assertFalse($model->hasErrors('attr_date'));
        $model = new FakedValidationModel;
        $model->attr_date = '1375293913';
        $val->validateAttribute($model, 'attr_date');
        $this->assertTrue($model->hasErrors('attr_date'));
        //// timestamp attribute
        $val = new DateValidator(['format' => 'php:Y-m-d', 'timestampAttribute' => 'attr_timestamp']);
        $model = new FakedValidationModel;
        $model->attr_date = '2013-09-13';
        $model->attr_timestamp = true;
        $val->validateAttribute($model, 'attr_date');
        $this->assertFalse($model->hasErrors('attr_date'));
        $this->assertFalse($model->hasErrors('attr_timestamp'));
        $this->assertEquals(
            1379030400, // 2013-09-13 00:00:00
            $model->attr_timestamp
        );
        $val = new DateValidator(['format' => 'php:Y-m-d']);
        $model = FakedValidationModel::createWithAttributes(['attr_date' => []]);
        $val->validateAttribute($model, 'attr_date');
        $this->assertTrue($model->hasErrors('attr_date'));

    }

    /**
     * @dataProvider provideTimezones
     */
    public function testIntlValidateAttributeICUFormat($timezone)
    {
        $this->testValidateAttributeICUFormat($timezone);
    }

    /**
     * @dataProvider provideTimezones
     */
    public function testValidateAttributeICUFormat($timezone)
    {
        date_default_timezone_set($timezone);

        // error-array-add
        $val = new DateValidator(['format' => 'yyyy-MM-dd']);
        $model = new FakedValidationModel;
        $model->attr_date = '2013-09-13';
        $val->validateAttribute($model, 'attr_date');
        $this->assertFalse($model->hasErrors('attr_date'));
        $model = new FakedValidationModel;
        $model->attr_date = '1375293913';
        $val->validateAttribute($model, 'attr_date');
        $this->assertTrue($model->hasErrors('attr_date'));
        //// timestamp attribute
        $val = new DateValidator(['format' => 'yyyy-MM-dd', 'timestampAttribute' => 'attr_timestamp']);
        $model = new FakedValidationModel;
        $model->attr_date = '2013-09-13';
        $model->attr_timestamp = true;
        $val->validateAttribute($model, 'attr_date');
        $this->assertFalse($model->hasErrors('attr_date'));
        $this->assertFalse($model->hasErrors('attr_timestamp'));
        $this->assertSame(
            1379030400, // 2013-09-13 00:00:00
            $model->attr_timestamp
        );
        $val = new DateValidator(['format' => 'yyyy-MM-dd']);
        $model = FakedValidationModel::createWithAttributes(['attr_date' => []]);
        $val->validateAttribute($model, 'attr_date');
        $this->assertTrue($model->hasErrors('attr_date'));
        $val = new DateValidator(['format' => 'yyyy-MM-dd']);
        $model = FakedValidationModel::createWithAttributes(['attr_date' => '2012-12-12foo']);
        $val->validateAttribute($model, 'attr_date');
        $this->assertTrue($model->hasErrors('attr_date'));
    }

    public function testIntlMultibyteString()
    {
        $val = new DateValidator(['format' => 'dd MMM yyyy', 'locale' => 'de_DE']);
        $model = FakedValidationModel::createWithAttributes(['attr_date' => '12 Mai 2014']);
        $val->validateAttribute($model, 'attr_date');
        $this->assertFalse($model->hasErrors('attr_date'));

        $val = new DateValidator(['format' => 'dd MMM yyyy', 'locale' => 'ru_RU']);
        $model = FakedValidationModel::createWithAttributes(['attr_date' => '12 мая 2014']);
        $val->validateAttribute($model, 'attr_date');
        $this->assertFalse($model->hasErrors('attr_date'));
    }

    public function provideTimezones()
    {
        return [
            ['UTC'],
            ['Europe/Berlin'],
            ['America/Jamaica'],
        ];
    }

    public function timestampFormatProvider()
    {
        $return = [];
        foreach($this->provideTimezones() as $appTz) {
            foreach ($this->provideTimezones() as $tz) {
                $return[] = ['yyyy-MM-dd', '2013-09-13', '2013-09-13', $tz[0], $appTz[0]];
                // regardless of timezone, a simple date input should always result in 00:00:00 time
                $return[] = ['yyyy-MM-dd HH:mm:ss', '2013-09-13', '2013-09-13 00:00:00', $tz[0], $appTz[0]];
                $return[] = ['php:Y-m-d', '2013-09-13', '2013-09-13', $tz[0], $appTz[0]];
                $return[] = ['php:Y-m-d H:i:s', '2013-09-13', '2013-09-13 00:00:00', $tz[0], $appTz[0]];
                $return[] = ['php:U', '2013-09-13', "1379030400", $tz[0], $appTz[0]];
                $return[] = [null, '2013-09-13', 1379030400, $tz[0], $appTz[0]];
            }
        }
        return $return;
    }

    /**
     * @dataProvider timestampFormatProvider
     */
    public function testIntlTimestampAttributeFormat($format, $date, $expectedDate, $timezone, $appTimezone)
    {
        $this->testTimestampAttributeFormat($format, $date, $expectedDate, $timezone, $appTimezone);
    }

    /**
     * @dataProvider timestampFormatProvider
     */
    public function testTimestampAttributeFormat($format, $date, $expectedDate, $timezone, $appTimezone)
    {
        date_default_timezone_set($timezone);

        $val = new DateValidator(['format' => 'yyyy-MM-dd', 'timestampAttribute' => 'attr_timestamp', 'timestampAttributeFormat' => $format, 'timeZone' => $appTimezone]);
        $model = new FakedValidationModel;
        $model->attr_date = $date;
        $model->attr_timestamp = true;
        $val->validateAttribute($model, 'attr_date');
        $this->assertFalse($model->hasErrors('attr_date'));
        $this->assertFalse($model->hasErrors('attr_timestamp'));
        $this->assertSame($expectedDate, $model->attr_timestamp);
    }

    /**
     * @dataProvider provideTimezones
     */
    public function testIntlValidationWithTime($timezone)
    {
        $this->testValidationWithTime($timezone);
    }

    /**
     * @dataProvider provideTimezones
     */
    public function testValidationWithTime($timezone)
    {
        date_default_timezone_set($timezone);

        $val = new DateValidator(['format' => 'yyyy-MM-dd HH:mm:ss', 'timestampAttribute' => 'attr_timestamp', 'timeZone' => 'UTC']);
        $model = new FakedValidationModel;
        $model->attr_date = '2013-09-13 14:23:15';
        $model->attr_timestamp = true;
        $val->validateAttribute($model, 'attr_date');
        $this->assertFalse($model->hasErrors('attr_date'));
        $this->assertFalse($model->hasErrors('attr_timestamp'));
        $this->assertSame(1379082195, $model->attr_timestamp);

        $val = new DateValidator(['format' => 'yyyy-MM-dd HH:mm:ss', 'timestampAttribute' => 'attr_timestamp', 'timeZone' => 'Europe/Berlin']);
        $model = new FakedValidationModel;
        $model->attr_date = '2013-09-13 16:23:15';
        $model->attr_timestamp = true;
        $val->validateAttribute($model, 'attr_date');
        $this->assertFalse($model->hasErrors('attr_date'));
        $this->assertFalse($model->hasErrors('attr_timestamp'));
        $this->assertSame(1379082195, $model->attr_timestamp);
    }
}
