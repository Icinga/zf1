<?php

use PHPUnit\Framework\TestCase;

/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Locale
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id $
 */

// define('TESTS_ZEND_LOCALE_BCMATH_ENABLED', false); // uncomment to disable use of bcmath extension by Zend_Date

/**
 * Zend_Locale
 */
require_once 'Zend/Locale.php';
require_once 'Zend/Cache.php';

/**
 * @category   Zend
 * @package    Zend_Locale
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @group      Zend_Locale
 */
class Zend_LocaleTest extends TestCase
{
    private $_cache = null;
    private $_locale = null;
    private $errorHandler = null;

    private $_errorOccurred = null;

    protected function setUp(): void
    {
        $this->_locale = setlocale(LC_ALL, 0);
        setlocale(LC_ALL, 'de');
        require_once 'Zend/Cache.php';
        $this->_cache = Zend_Cache::factory(
            'Core',
            'File',
            ['lifetime' => 120, 'automatic_serialization' => true],
            ['cache_dir' => dirname(__FILE__) . '/_files/']
        );
        Zend_Locale::resetObject();
        Zend_Locale::setCache($this->_cache);

        // compatibilityMode is true until 1.8 therefor we have to change it
        Zend_Locale::$compatibilityMode = false;
        putenv("HTTP_ACCEPT_LANGUAGE=,de,en-UK-US;q=0.5,fr_FR;q=0.2");
    }

    protected function tearDown(): void
    {
        if ($this->errorHandler) {
            restore_error_handler();
        }
        $this->_cache->clean(Zend_Cache::CLEANING_MODE_ALL);
        if (is_string($this->_locale) && strpos($this->_locale, ';')) {
            $locales = [];
            foreach (explode(';', $this->_locale) as $l) {
                $tmp = explode('=', $l);
                $locales[$tmp[0]] = count($tmp) > 1 ? $tmp[1] : $tmp[0];
            }
            setlocale(LC_ALL, $locales);
            return;
        }
        setlocale(LC_ALL, $this->_locale);
    }

    public static function tearDownAfterClass(): void
    {
        /**
         * Fix issue side effect Zend_Locale::$_auto cached when run
         * Zend_TranslateTest suite after Zend_LocateTest in same process
         */
        putenv("HTTP_ACCEPT_LANGUAGE");
        Zend_Locale::resetObject();
    }

    /**
     * Test that locale names that have been dropped from CLDR continue to
     * work.
     */
    public function testAliases()
    {
        $locale = new Zend_Locale('zh_CN');
        $this->assertEquals(true, $locale->isLocale('zh_CN'));
        $this->assertEquals('zh', $locale->getLanguage());
        $this->assertEquals('CN', $locale->getRegion());
        $this->assertEquals(true, Zend_Locale::isAlias($locale));
        $this->assertEquals(true, Zend_Locale::isAlias('zh_CN'));
        $this->assertEquals('zh_Hans_CN', Zend_Locale::getAlias('zh_CN'));

        $locale = new Zend_Locale('zh_Hans_CN');
        $this->assertEquals(true, $locale->isLocale('zh_Hans_CN'));
        $this->assertEquals('zh', $locale->getLanguage());
        $this->assertEquals('CN', $locale->getRegion());
        $this->assertEquals(false, Zend_Locale::isAlias('zh_Hans_CN'));
        $this->assertEquals('zh_Hans_CN', Zend_Locale::getAlias('zh_Hans_CN'));
    }

    /**
     * @group GH-337
     */
    public function testIsLocaleMethodWithAliases()
    {
        $this->assertEquals(true, Zend_Locale::isLocale('zh_CN'));
        $this->assertEquals(true, Zend_Locale::isLocale('zh_CN', false, false));
        $this->assertEquals(true, Zend_Locale::isLocale('zh_CN', true, true));
        $this->assertEquals(true, Zend_Locale::isLocale('zh_CN', false, true));
        $this->assertEquals(true, Zend_Locale::isLocale('zh_CN', true, false));

        $this->assertEquals(true, Zend_Locale::isLocale('zh_Hans_CN'));
        $this->assertEquals(true, Zend_Locale::isLocale('zh_Hans_CN', false, false));
        $this->assertEquals(true, Zend_Locale::isLocale('zh_Hans_CN', true, true));
        $this->assertEquals(true, Zend_Locale::isLocale('zh_Hans_CN', false, true));
        $this->assertEquals(true, Zend_Locale::isLocale('zh_Hans_CN', true, false));
    }

    /**
     * test for object creation
     * expected object instance
     */
    public function testObjectCreation()
    {
        $this->assertTrue(Zend_Locale::isLocale('de'));

        $this->assertTrue(new Zend_Locale() instanceof Zend_Locale);
        $this->assertTrue(new Zend_Locale('root') instanceof Zend_Locale);
        try {
            $locale = new Zend_Locale(Zend_Locale::ENVIRONMENT);
            $this->assertTrue($locale instanceof Zend_Locale);
        } catch (Zend_Locale_Exception $e) {
            // ignore environments where the locale can not be detected
            $this->assertStringContainsString('Autodetection', $e->getMessage());
        }

        try {
            $this->assertTrue(new Zend_Locale(Zend_Locale::BROWSER) instanceof Zend_Locale);
        } catch (Zend_Locale_Exception $e) {
            // ignore environments where the locale can not be detected
            $this->assertStringContainsString('Autodetection', $e->getMessage());
        }

        $locale = new Zend_Locale('de');
        $this->assertTrue(new Zend_Locale($locale) instanceof Zend_Locale);

        $locale = new Zend_Locale('auto');
        $this->assertTrue(new Zend_Locale($locale) instanceof Zend_Locale);

        // compatibility tests
        $this->setErrorHandler();
        Zend_Locale::$compatibilityMode = true;
        $this->assertEquals('de', Zend_Locale::isLocale('de_ABC'));
    }

    /**
     * test for serialization
     * expected string
     */
    public function testSerialize()
    {
        $value = new Zend_Locale('de_DE');
        $serial = $value->serialize();
        $this->assertTrue(!empty($serial));

        $newvalue = unserialize($serial);
        $this->assertTrue($value->equals($newvalue));
    }

    /**
     * test toString
     * expected string
     */
    public function testToString()
    {
        $value = new Zend_Locale('de_DE');
        $this->assertEquals('de_DE', $value->toString());
        $this->assertEquals('de_DE', $value->__toString());
    }

    /**
     * test getOrder
     * expected true
     */
    public function testgetOrder()
    {
        Zend_Locale::setDefault('de');
        $value = new Zend_Locale();
        $default = $value->getOrder();
        $this->assertTrue(array_key_exists('de', $default));

        $default = $value->getOrder(Zend_Locale::BROWSER);
        $this->assertTrue(is_array($default));

        $default = $value->getOrder(Zend_Locale::ENVIRONMENT);
        $this->assertTrue(is_array($default));

        $default = $value->getOrder(Zend_Locale::ZFDEFAULT);
        $this->assertTrue(is_array($default));
    }

    /**
     * test getEnvironment
     * expected true
     */
    public function Zend_LocaleDetail()
    {
        $value = new Zend_Locale('de_AT');
        $this->assertEquals('de', $value->getLanguage());
        $this->assertEquals('AT', $value->getRegion());

        $value = new Zend_Locale('en_US');
        $this->assertEquals('en', $value->getLanguage());
        $this->assertEquals('US', $value->getRegion());

        $value = new Zend_Locale('en');
        $this->assertEquals('en', $value->getLanguage());
        $this->assertFalse($value->getRegion());
    }

    /**
     * test getEnvironment
     * expected true
     */
    public function testEnvironment()
    {
        $value = new Zend_Locale();
        $default = $value->getEnvironment();
        $this->assertTrue(is_array($default));
    }

    /**
     * test getBrowser
     * expected true
     */
    public function testBrowser()
    {
        $value = new Zend_Locale();
        $default = $value->getBrowser();
        $this->assertTrue(is_array($default));
    }

    /**
     * test clone
     * expected true
     */
    public function testCloning()
    {
        $value = new Zend_Locale('de_DE');
        $newvalue = clone $value;
        $this->assertEquals($value->toString(), $newvalue->toString());
    }

    /**
     * test setLocale
     * expected true
     */
    public function testsetLocale()
    {
        $value = new Zend_Locale('de_DE');
        $value->setLocale('en_US');
        $this->assertEquals('en_US', $value->toString());

        $value->setLocale('en_AA');
        $this->assertEquals('en', $value->toString());

        $value->setLocale('xx_AA');
        $this->assertEquals('root', $value->toString());

        $value->setLocale('auto');
        $this->assertTrue(is_string($value->toString()));

        try {
            $value->setLocale('browser');
            $this->assertTrue(is_string($value->toString()));
        } catch (Zend_Locale_Exception $e) {
            // ignore environments where the locale can not be detected
            $this->assertStringContainsString('Autodetection', $e->getMessage());
        }

        try {
            $value->setLocale('environment');
            $this->assertTrue(is_string($value->toString()));
        } catch (Zend_Locale_Exception $e) {
            // ignore environments where the locale can not be detected
            $this->assertStringContainsString('Autodetection', $e->getMessage());
        }
    }

    /**
     * test getLanguageTranslationList
     * expected true
     */
    public function testgetLanguageTranslationList()
    {
        $this->setErrorHandler();
        $list = Zend_Locale::getLanguageTranslationList();
        $this->assertTrue(is_array($list));
        $list = Zend_Locale::getLanguageTranslationList('de');
        $this->assertTrue(is_array($list));
    }

    /**
     * test getLanguageTranslation
     * expected true
     */
    public function testgetLanguageTranslation()
    {
        $this->setErrorHandler();
        $this->assertEquals('Deutsch', Zend_Locale::getLanguageTranslation('de', 'de_AT'));
        $this->assertEquals('German', Zend_Locale::getLanguageTranslation('de', 'en'));
        $this->assertFalse(Zend_Locale::getLanguageTranslation('xyz'));
        $this->assertTrue(is_string(Zend_Locale::getLanguageTranslation('de', 'auto')));
    }

    /**
     * test getScriptTranslationList
     * expected true
     */
    public function testgetScriptTranslationList()
    {
        $this->setErrorHandler();
        $list = Zend_Locale::getScriptTranslationList();
        $this->assertTrue(is_array($list));

        $list = Zend_Locale::getScriptTranslationList('de');
        $this->assertTrue(is_array($list));
    }

    /**
     * test getScriptTranslationList
     * expected true
     */
    public function testgetScriptTranslation()
    {
        $this->setErrorHandler();
        $this->assertEquals('Arabisch', Zend_Locale::getScriptTranslation('Arab', 'de_AT'));
        $this->assertEquals('Arabic', Zend_Locale::getScriptTranslation('Arab', 'en'));
        $this->assertFalse(Zend_Locale::getScriptTranslation('xyz'));
    }

    /**
     * test getCountryTranslationList
     * expected true
     */
    public function testgetCountryTranslationList()
    {
        $this->setErrorHandler();
        $list = Zend_Locale::getCountryTranslationList();
        $this->assertTrue(is_array($list));

        $list = Zend_Locale::getCountryTranslationList('de');
        $this->assertEquals("Vereinigte Staaten", $list['US']);
    }

    /**
     * test getCountryTranslation
     * expected true
     */
    public function testgetCountryTranslation()
    {
        $this->setErrorHandler();
        $this->assertEquals('Deutschland', Zend_Locale::getCountryTranslation('DE', 'de_DE'));
        $this->assertEquals('Germany', Zend_Locale::getCountryTranslation('DE', 'en'));
        $this->assertFalse(Zend_Locale::getCountryTranslation('xyz'));
    }

    /**
     * test getTerritoryTranslationList
     * expected true
     */
    public function testgetTerritoryTranslationList()
    {
        $this->setErrorHandler();
        $list = Zend_Locale::getTerritoryTranslationList();
        $this->assertTrue(is_array($list));

        $list = Zend_Locale::getTerritoryTranslationList('de');
        $this->assertTrue(is_array($list));
    }

    /**
     * test getTerritoryTranslation
     * expected true
     */
    public function testgetTerritoryTranslation()
    {
        $this->setErrorHandler();
        $this->assertEquals('Afrika', Zend_Locale::getTerritoryTranslation('002', 'de_AT'));
        $this->assertEquals('Africa', Zend_Locale::getTerritoryTranslation('002', 'en'));
        $this->assertFalse(Zend_Locale::getTerritoryTranslation('xyz'));
        $this->assertTrue(is_string(Zend_Locale::getTerritoryTranslation('002', 'auto')));
    }

    /**
     * test getTranslation
     * expected true
     */
    public function testgetTranslation()
    {
        try {
            $temp = Zend_Locale::getTranslation('xx');
            $this->fail();
        } catch (Zend_Locale_Exception $e) {
            $this->assertStringContainsString('Unknown detail (', $e->getMessage());
        }

        $this->assertEquals('Deutsch', Zend_Locale::getTranslation('de', 'language', 'de_DE'));
        $this->assertEquals('German', Zend_Locale::getTranslation('de', 'language', 'en'));
        $this->assertFalse(Zend_Locale::getTranslation('xx', 'language'));

        $this->assertEquals('Lateinisch', Zend_Locale::getTranslation('Latn', 'script', 'de_DE'));
        $this->assertEquals('Latin', Zend_Locale::getTranslation('Latn', 'script', 'en'));
        $this->assertFalse(Zend_Locale::getTranslation('xyxy', 'script'));

        $this->assertEquals('Österreich', Zend_Locale::getTranslation('AT', 'country', 'de_DE'));
        $this->assertEquals('Austria', Zend_Locale::getTranslation('AT', 'country', 'en'));
        $this->assertFalse(Zend_Locale::getTranslation('xx', 'country'));

        $this->assertEquals('Afrika', Zend_Locale::getTranslation('002', 'territory', 'de_DE'));
        $this->assertEquals('Africa', Zend_Locale::getTranslation('002', 'territory', 'en'));
        $this->assertFalse(Zend_Locale::getTranslation('xxx', 'territory'));

        $this->assertEquals('Januar', Zend_Locale::getTranslation('1', 'month', 'de_DE'));
        $this->assertEquals('January', Zend_Locale::getTranslation('1', 'month', 'en'));
        $this->assertFalse(Zend_Locale::getTranslation('x', 'month'));

        $this->assertEquals('Jan.', Zend_Locale::getTranslation(['gregorian', 'format', 'abbreviated', '1'], 'month', 'de_DE'));
        $this->assertEquals('Jan', Zend_Locale::getTranslation(['gregorian', 'format', 'abbreviated', '1'], 'month', 'en'));
        $this->assertFalse(Zend_Locale::getTranslation(['gregorian', 'format', 'abbreviated', 'x'], 'month'));

        $this->assertEquals('J', Zend_Locale::getTranslation(['gregorian', 'stand-alone', 'narrow', '1'], 'month', 'de_DE'));
        $this->assertEquals('J', Zend_Locale::getTranslation(['gregorian', 'stand-alone', 'narrow', '1'], 'month', 'en'));
        $this->assertFalse(Zend_Locale::getTranslation(['gregorian', 'stand-alone', 'narrow', 'x'], 'month'));

        $this->assertEquals('Sonntag', Zend_Locale::getTranslation('sun', 'day', 'de_DE'));
        $this->assertEquals('Sunday', Zend_Locale::getTranslation('sun', 'day', 'en'));
        $this->assertFalse(Zend_Locale::getTranslation('xxx', 'day'));

        $this->assertEquals('So.', Zend_Locale::getTranslation(['gregorian', 'format', 'abbreviated', 'sun'], 'day', 'de_DE'));
        $this->assertEquals('Sun', Zend_Locale::getTranslation(['gregorian', 'format', 'abbreviated', 'sun'], 'day', 'en'));
        $this->assertFalse(Zend_Locale::getTranslation(['gregorian', 'format', 'abbreviated', 'xxx'], 'day'));

        $this->assertEquals('S', Zend_Locale::getTranslation(['gregorian', 'stand-alone', 'narrow', 'sun'], 'day', 'de_DE'));
        $this->assertEquals('S', Zend_Locale::getTranslation(['gregorian', 'stand-alone', 'narrow', 'sun'], 'day', 'en'));
        $this->assertFalse(Zend_Locale::getTranslation(['gregorian', 'stand-alone', 'narrow', 'xxx'], 'day'));

        $this->assertEquals('EEEE, d. MMMM y', Zend_Locale::getTranslation('full', 'date', 'de_DE'));
        $this->assertEquals('EEEE, MMMM d, y', Zend_Locale::getTranslation('full', 'date', 'en'));
        $this->assertFalse(Zend_Locale::getTranslation('xxxx', 'date'));

        $this->assertEquals("HH:mm:ss zzzz", Zend_Locale::getTranslation('full', 'time', 'de_DE'));
        $this->assertEquals('h:mm:ss a zzzz', Zend_Locale::getTranslation('full', 'time', 'en'));
        $this->assertFalse(Zend_Locale::getTranslation('xxxx', 'time'));

        $this->assertEquals('Wien', Zend_Locale::getTranslation('Europe/Vienna', 'citytotimezone', 'de_DE'));
        $this->assertEquals("St. John’s", Zend_Locale::getTranslation('America/St_Johns', 'citytotimezone', 'en'));
        $this->assertFalse(Zend_Locale::getTranslation('xxxx', 'citytotimezone'));

        $this->assertEquals('Euro', Zend_Locale::getTranslation('EUR', 'nametocurrency', 'de_DE'));
        $this->assertEquals('Euro', Zend_Locale::getTranslation('EUR', 'nametocurrency', 'en'));
        $this->assertFalse(Zend_Locale::getTranslation('xxx', 'nametocurrency'));

        $this->assertEquals('EUR', Zend_Locale::getTranslation('Euro', 'currencytoname', 'de_DE'));
        $this->assertEquals('EUR', Zend_Locale::getTranslation('Euro', 'currencytoname', 'en'));
        $this->assertFalse(Zend_Locale::getTranslation('xxx', 'currencytoname'));

        /*
         * this is not fully working, since the cdlr 23 have not fully declared all currency symbols in root.
         */
        $this->assertEquals('CHF', Zend_Locale::getTranslation('CHF', 'currencysymbol', 'de_CH'));
        $this->assertEquals('CHF', Zend_Locale::getTranslation('CHF', 'currencysymbol', 'rm'));
        $this->assertFalse(Zend_Locale::getTranslation('xxx', 'currencysymbol'));

        $this->assertEquals('EUR', Zend_Locale::getTranslation('AT', 'currencytoregion', 'de_DE'));
        $this->assertEquals('EUR', Zend_Locale::getTranslation('AT', 'currencytoregion', 'en'));
        $this->assertFalse(Zend_Locale::getTranslation('xxx', 'currencytoregion'));

        $this->assertEquals('015 011 017 014 018', Zend_Locale::getTranslation('002', 'regiontoterritory', 'de_DE'));
        $this->assertEquals('015 011 017 014 018', Zend_Locale::getTranslation('002', 'regiontoterritory', 'en'));
        $this->assertFalse(Zend_Locale::getTranslation('xxx', 'regiontoterritory'));

        $this->assertEquals('AT BE CH DE LI LU', Zend_Locale::getTranslation('de', 'territorytolanguage', 'de_DE'));
        $this->assertEquals('AT BE CH DE LI LU', Zend_Locale::getTranslation('de', 'territorytolanguage', 'en'));
        $this->assertFalse(Zend_Locale::getTranslation('xxx', 'territorytolanguage'));
    }

    /**
     * test getTranslationList
     * expected true
     */
    public function testgetTranslationList()
    {
        try {
            $temp = Zend_Locale::getTranslationList();
            $this->fail();
        } catch (Zend_Locale_Exception $e) {
            $this->assertStringContainsString('Unknown list (', $e->getMessage());
        }

        $this->assertTrue(in_array('Deutsch', Zend_Locale::getTranslationList('language', 'de_DE')));
        $this->assertTrue(in_array('German', Zend_Locale::getTranslationList('language', 'en')));

        $this->assertTrue(in_array('Lateinisch', Zend_Locale::getTranslationList('script', 'de_DE')));
        $this->assertTrue(in_array('Latin', Zend_Locale::getTranslationList('script', 'en')));

        $this->assertTrue(in_array('Afrika', Zend_Locale::getTranslationList('territory', 'de_DE')));
        $this->assertTrue(in_array('Africa', Zend_Locale::getTranslationList('territory', 'en')));

        $this->assertTrue(in_array('Chinesischer Kalender', Zend_Locale::getTranslationList('type', 'de_DE', 'calendar')));
        $this->assertTrue(in_array('Chinese Calendar', Zend_Locale::getTranslationList('type', 'en', 'calendar')));

        $this->assertTrue(in_array('Januar', Zend_Locale::getTranslationList('month', 'de_DE')));
        $this->assertTrue(in_array('January', Zend_Locale::getTranslationList('month', 'en')));

        $this->assertTrue(in_array('Jan.', Zend_Locale::getTranslationList('month', 'de_DE', ['gregorian', 'format', 'abbreviated'])));
        $this->assertTrue(in_array('Jan', Zend_Locale::getTranslationList('month', 'en', ['gregorian', 'format', 'abbreviated'])));

        $this->assertTrue(in_array('J', Zend_Locale::getTranslationList('month', 'de_DE', ['gregorian', 'stand-alone', 'narrow'])));
        $this->assertTrue(in_array('J', Zend_Locale::getTranslationList('month', 'en', ['gregorian', 'stand-alone', 'narrow'])));

        $this->assertTrue(in_array('Sonntag', Zend_Locale::getTranslationList('day', 'de_DE')));
        $this->assertTrue(in_array('Sunday', Zend_Locale::getTranslationList('day', 'en')));

        $this->assertTrue(in_array('So.', Zend_Locale::getTranslationList('day', 'de_DE', ['gregorian', 'format', 'abbreviated'])));
        $this->assertTrue(in_array('Sun', Zend_Locale::getTranslationList('day', 'en', ['gregorian', 'format', 'abbreviated'])));

        $this->assertTrue(in_array('S', Zend_Locale::getTranslationList('day', 'de_DE', ['gregorian', 'stand-alone', 'narrow'])));
        $this->assertTrue(in_array('S', Zend_Locale::getTranslationList('day', 'en', ['gregorian', 'stand-alone', 'narrow'])));

        $this->assertTrue(in_array('EEEE, d. MMMM y', Zend_Locale::getTranslationList('date', 'de_DE')));
        $this->assertTrue(in_array('EEEE, MMMM d, y', Zend_Locale::getTranslationList('date', 'en')));

        $this->assertTrue(in_array("HH:mm:ss zzzz", Zend_Locale::getTranslationList('time', 'de_DE')));
        $this->assertTrue(in_array("h:mm:ss a z", Zend_Locale::getTranslationList('time', 'en')));

        $this->assertTrue(in_array('Wien', Zend_Locale::getTranslationList('citytotimezone', 'de_DE')));
        $this->assertTrue(in_array("St. John’s", Zend_Locale::getTranslationList('citytotimezone', 'en')));

        $this->assertTrue(in_array('Euro', Zend_Locale::getTranslationList('nametocurrency', 'de_DE')));
        $this->assertTrue(in_array('Euro', Zend_Locale::getTranslationList('nametocurrency', 'en')));

        $this->assertTrue(in_array('EUR', Zend_Locale::getTranslationList('currencytoname', 'de_DE')));
        $this->assertTrue(in_array('EUR', Zend_Locale::getTranslationList('currencytoname', 'en')));

        /*
         * this is not fully working, since the cdlr 23 have not fully declared all currency symbols in root.
         */
        $this->assertTrue(in_array('CHF', Zend_Locale::getTranslationList('currencysymbol', 'de_CH')));
        $this->assertFalse(in_array('CHF', Zend_Locale::getTranslationList('currencysymbol', 'en')));

        $this->assertTrue(in_array('EUR', Zend_Locale::getTranslationList('currencytoregion', 'de_DE')));
        $this->assertTrue(in_array('EUR', Zend_Locale::getTranslationList('currencytoregion', 'en')));

        $this->assertTrue(in_array('AU NF NZ', Zend_Locale::getTranslationList('regiontoterritory', 'de_DE')));
        $this->assertTrue(in_array('AU NF NZ', Zend_Locale::getTranslationList('regiontoterritory', 'en')));

        $this->assertTrue(in_array('CZ', Zend_Locale::getTranslationList('territorytolanguage', 'de_DE')));
        $this->assertTrue(in_array('CZ', Zend_Locale::getTranslationList('territorytolanguage', 'en')));

        $char = Zend_Locale::getTranslationList('characters', 'de_DE');
        $this->assertEquals("[a ä b c d e f g h i j k l m n o ö p q r s ß t u ü v w x y z]", $char['characters']);
        $this->assertEquals("[á à ă â å ã ā æ ç é è ĕ ê ë ē ğ í ì ĭ î ï İ ī ı ñ ó ò ŏ ô ø ō œ ş ú ù ŭ û ū ÿ]", $char['auxiliary']);
        /* currencySymbol is deprecated in cdlr. */
        // $this->assertEquals("[a-z]", $char['currencySymbol']);

        $char = Zend_Locale::getTranslationList('characters', 'en');
        $this->assertEquals("[a b c d e f g h i j k l m n o p q r s t u v w x y z]", $char['characters']);
        $this->assertEquals("[á à ă â å ä ã ā æ ç é è ĕ ê ë ē í ì ĭ î ï ī ñ ó ò ŏ ô ö ø ō œ ú ù ŭ û ü ū ÿ]", $char['auxiliary']);
        /* currencySymbol is deprecated in cdlr. */
        // $this->assertEquals("[a-c č d-l ł m-z]", $char['currencySymbol']);
    }

    /**
     * test for equality
     * expected string
     */
    public function testEquals()
    {
        $value = new Zend_Locale('de_DE');
        $serial = new Zend_Locale('de_DE');
        $serial2 = new Zend_Locale('de_AT');
        $this->assertTrue($value->equals($serial));
        $this->assertFalse($value->equals($serial2));
    }

    /**
     * test getQuestion
     * expected true
     */
    public function testgetQuestion()
    {
        $list = Zend_Locale::getQuestion();
        $this->assertTrue(isset($list['yes']));

        $list = Zend_Locale::getQuestion('de');
        $this->assertEquals('ja', $list['yes']);

        $this->assertTrue(is_array(Zend_Locale::getQuestion('auto')));

        try {
            $this->assertTrue(is_array(Zend_Locale::getQuestion('browser')));
        } catch (Zend_Locale_Exception $e) {
            $this->assertStringContainsString('Autodetection', $e->getMessage());
        }

        try {
            $this->assertTrue(is_array(Zend_Locale::getQuestion('environment')));
        } catch (Zend_Locale_Exception $e) {
            $this->assertStringContainsString('ocale', $e->getMessage());
        }
    }

    /**
     * test getBrowser
     * expected true
     */
    public function testgetBrowser()
    {
        Zend_Locale::resetObject();
        $value = new Zend_Locale();
        $list = $value->getBrowser();
        if (empty($list)) {
            $this->markTestSkipped('Browser autodetection not possible in current environment');
        }
        $this->assertTrue(isset($list['de']));
        $this->assertEquals(['de' => 1.0, 'en_UK' => 0.5, 'en_US' => 0.5,
                                  'en' => 0.5, 'fr_FR' => 0.2, 'fr' => 0.2], $list);

        Zend_Locale::resetObject();
        putenv("HTTP_ACCEPT_LANGUAGE=");
        unset($_SERVER['HTTP_ACCEPT_LANGUAGE']);
        $ref = new ReflectionProperty('Zend_Locale', '_browser');
        $ref->setValue(null, null);

        $value = new Zend_Locale();
        $list = $value->getBrowser();
        $this->assertEquals([], $list);
    }

    /**
     * test getHttpCharset
     * expected true
     */
    public function testgetHttpCharset()
    {
        Zend_Locale::resetObject();
        putenv("HTTP_ACCEPT_CHARSET=");
        $value = new Zend_Locale();
        $list = $value->getHttpCharset();
        $this->assertTrue(empty($list));

        Zend_Locale::resetObject();
        putenv("HTTP_ACCEPT_CHARSET=,iso-8859-1, utf-8, utf-16, *;q=0.1");
        $value = new Zend_Locale();
        $list = $value->getHttpCharset();
        $this->assertTrue(isset($list['utf-8']));
    }

    /**
     * test isLocale
     * expected boolean
     */
    public function testIsLocale()
    {
        $locale = new Zend_Locale('ar');
        $this->assertTrue(Zend_Locale::isLocale($locale));
        $this->assertTrue(Zend_Locale::isLocale('de'));
        $this->assertTrue(Zend_Locale::isLocale('de_AT'));
        $this->assertTrue(Zend_Locale::isLocale('de_xx'));
        $this->assertFalse(Zend_Locale::isLocale('yy'));
        $this->assertFalse(Zend_Locale::isLocale(1234));
        $this->assertFalse(Zend_Locale::isLocale('', true));
        $this->assertFalse(Zend_Locale::isLocale('', false));
        $this->assertTrue(Zend_Locale::isLocale('auto'));
        $this->assertTrue(Zend_Locale::isLocale('browser'));
        if (count(Zend_Locale::getEnvironment()) !== 0) {
            $this->assertTrue(Zend_Locale::isLocale('environment'));
        }

        $this->setErrorHandler();
        Zend_Locale::$compatibilityMode = true;
        $this->assertTrue(Zend_Locale::isLocale($locale)); // compatibilty makes no odds when testing a Zend_Locale instance
        $this->assertTrue(Zend_Locale::isLocale('de')); // compatibilty makes no odds when testing a known valid locale
        $this->assertTrue(Zend_Locale::isLocale('de_AT'));// compatibilty makes no odds when testing a known valid locale
        $this->assertEquals('de', Zend_Locale::isLocale('de_xx'));
        $this->assertFalse(Zend_Locale::isLocale('yy'));
        $this->assertFalse(Zend_Locale::isLocale(1234));
        $this->assertFalse(Zend_Locale::isLocale('', true));
        $this->assertFalse(Zend_Locale::isLocale('', false));
        $this->assertTrue(is_string(Zend_Locale::isLocale('auto')));
        $this->assertTrue(is_string(Zend_Locale::isLocale('browser')));
        if (count(Zend_Locale::getEnvironment()) !== 0) {
            $this->assertTrue(is_string(Zend_Locale::isLocale('environment')));
        }
    }

    /**
     * test isLocale
     * expected boolean
     */
    public function testGetLocaleList()
    {
        $this->assertTrue(is_array(Zend_Locale::getLocaleList()));
    }

    /**
     * test setDefault
     * expected true
     */
    public function testsetDefault()
    {
        try {
            Zend_Locale::setDefault('auto');
            $this->fail();
        } catch (Zend_Locale_Exception $e) {
            $this->assertStringContainsString("full qualified locale", $e->getMessage());
        }

        try {
            Zend_Locale::setDefault('de_XX');
            $locale = new Zend_Locale();
            $this->assertTrue($locale instanceof Zend_Locale); // should defer to 'de' or any other standard locale
        } catch (Zend_Locale_Exception $e) {
            $this->fail(); // de_XX should automatically degrade to 'de'
        }

        try {
            Zend_Locale::setDefault('xy_ZZ');
            $this->fail();
        } catch (Zend_Locale_Exception $e) {
            $this->assertStringContainsString("Unknown locale", $e->getMessage());
        }

        try {
            Zend_Locale::setDefault('de', 101);
            $this->fail();
        } catch (Zend_Locale_Exception $e) {
            $this->assertStringContainsString("Quality must be between", $e->getMessage());
        }

        try {
            Zend_Locale::setDefault('de', 90);
            $locale = new Zend_Locale();
            $this->assertTrue($locale instanceof Zend_Locale); // should defer to 'de' or any other standard locale
        } catch (Zend_Locale_Exception $e) {
            $this->fail();
        }

        try {
            Zend_Locale::setDefault('de-AT', 90);
            $locale = new Zend_Locale();
            $this->assertTrue($locale instanceof Zend_Locale);
        } catch (Zend_Locale_Exception $e) {
            $this->fail();
        }
    }

    /**
     * Test getDefault
     */
    public function testgetDefault()
    {
        Zend_Locale::setDefault('de');
        $this->assertTrue(array_key_exists('de', Zend_Locale::getDefault()));

        // compatibility tests
        $this->setErrorHandler();
        Zend_Locale::$compatibilityMode = true;
        $this->assertTrue(array_key_exists('de', Zend_Locale::getDefault(Zend_Locale::BROWSER)));
    }

    /**
     * Caching method tests
     */
    public function testCaching()
    {
        $cache = Zend_Locale::getCache();
        $this->assertTrue($cache instanceof Zend_Cache_Core);
        $this->assertTrue(Zend_Locale::hasCache());

        Zend_Locale::clearCache();
        $this->assertTrue(Zend_Locale::hasCache());

        Zend_Locale::removeCache();
        $this->assertFalse(Zend_Locale::hasCache());
    }

    /**
     * Caching method tests
     */
    public function testFindingTheProperLocale()
    {
        $this->assertTrue(is_string(Zend_Locale::findLocale()));
        $this->assertEquals('de', Zend_Locale::findLocale('de'));
        $this->assertEquals('de', Zend_Locale::findLocale('de_XX'));

        try {
            $locale = Zend_Locale::findLocale('xx_YY');
            $this->fail();
        } catch (Zend_Locale_Exception $e) {
            $this->assertStringContainsString('is no known locale', $e->getMessage());
        }

        Zend_Registry::set('Zend_Locale', 'de');
        $this->assertEquals('de', Zend_Locale::findLocale());
    }

    /**
     * test isLocale
     * expected boolean
     */
    public function testZF3617()
    {
        $value = new Zend_Locale('en-US');
        $this->assertEquals('en_US', $value->toString());
    }

    /**
     * @ZF4963
     */
    public function testZF4963()
    {
        $value = new Zend_Locale();
        $locale = $value->toString();
        $this->assertTrue(!empty($locale));

        $this->assertFalse(Zend_Locale::isLocale(null));

        $value = new Zend_Locale(0);
        $value = $value->toString();
        $this->assertTrue(!empty($value));

        $this->assertFalse(Zend_Locale::isLocale(0));
    }

    /**
     * test MultiPartLocales
     * expected boolean
     */
    public function testLongLocale()
    {
        $locale = new Zend_Locale('de_Latn_DE');
        $this->assertEquals('de_DE', $locale->toString());
        $this->assertTrue(Zend_Locale::isLocale('de_Latn_CAR_DE_sup3_win'));

        $locale = new Zend_Locale('de_Latn_DE');
        $this->assertEquals('de_DE', $locale->toString());

        $this->assertEquals('fr_FR', Zend_Locale::findLocale('fr-Arab-FR'));
    }

    /**
     * test SunLocales
     * expected boolean
     */
    public function testSunLocale()
    {
        $this->assertTrue(Zend_Locale::isLocale('de_DE.utf8'));
        $this->assertFalse(Zend_Locale::isLocale('de.utf8.DE'));
    }

    /**
     * @ZF-8030
     */
    public function testFailedLocaleOnPreTranslations()
    {
        $this->assertEquals('Andorra', Zend_Locale::getTranslation('AD', 'country', 'gl_GL'));
    }

    /**
     * @ZF-9488
     */
    public function testTerritoryToGetLocale()
    {
        $value = Zend_Locale::findLocale('US');
        $this->assertEquals('en_US', $value);

        $value = new Zend_Locale('US');
        $this->assertEquals('en_US', $value->toString());

        $value = new Zend_Locale('TR');
        $this->assertEquals('tr_TR', $value->toString());
    }
    /**
     * @group ZF-11072
     */
    public function testTranslationReturnsZeroAsNumber()
    {
        $this->assertFalse(Zend_Locale::getTranslation('USD', 'CurrencyFraction'));
        $this->assertEquals('0', Zend_Locale::getTranslation('JPY', 'CurrencyFraction'));
        $this->assertEquals('2', Zend_Locale::getTranslation('CHF', 'CurrencyFraction'));
        $this->assertEquals('3', Zend_Locale::getTranslation('BHD', 'CurrencyFraction'));
        $this->assertEquals('2', Zend_Locale::getTranslation('DEFAULT', 'CurrencyFraction'));
    }

    public function testEachDataFileShouldPresentAsLocaleData()
    {
        if (version_compare(PHP_VERSION, '5.3.2', 'lt')) {
            $this->markTestSkipped('ReflectionMethod::setAccessible can only be run under 5.3.2 or later');
        }

        $dir = new DirectoryIterator(
            dirname(__FILE__) . '/../../library/Zend/Locale/Data'
        );
        $skip = [
            'characters.xml',
            'coverageLevels.xml',
            'dayPeriods.xml',
            'genderList.xml',
            'languageInfo.xml',
            'likelySubtags.xml',
            'metaZones.xml',
            'numberingSystems.xml',
            'postalCodeData.xml',
            'supplementalData.xml',
            'supplementalMetadata.xml',
            'telephoneCodeData.xml',
            'Translation.php',
            'windowsZones.xml',
        ];

        $files = ['root'];
        /** @var SplFileInfo $fileinfo */
        foreach ($dir as $fileinfo) {
            if (!$fileinfo->isDot()
                && !in_array($fileinfo->getBasename(), $skip)
            ) {
                $files[] = $fileinfo->getBasename('.xml');
            }
        }

        $class = new ReflectionClass('Zend_Locale');
        $property = $class->getProperty('_localeData');

        $locale = new Zend_Locale();
        $localeData = $property->getValue($locale);
        $localeData = array_keys($localeData);

        $this->assertEquals([], array_diff($files, $localeData));
    }

    public function setErrorHandler()
    {
        set_error_handler([$this, 'errorHandlerIgnore']);
        $this->errorHandler = true;
    }

    /**
     * Ignores a raised PHP error when in effect, but throws a flag to indicate an error occurred
     *
     * @param  integer $errno
     * @param  string  $errstr
     * @param  string  $errfile
     * @param  integer $errline
     * @param  array   $errcontext
     * @return void
     */
    public function errorHandlerIgnore($errno, $errstr, $errfile, $errline, array $errcontext = [])
    {
        $this->_errorOccurred = true;
    }
}
