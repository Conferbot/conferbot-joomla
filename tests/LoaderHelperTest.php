<?php

/**
 * Unit tests for the pure helper behind the Conferbot Joomla plugin.
 */

use Conferbot\Plugin\System\Conferbot\Helper\LoaderHelper;
use PHPUnit\Framework\TestCase;

class LoaderHelperTest extends TestCase
{
    private const ID = '64a1b2c3d4e5f6a7b8c9d0e1';

    // -----------------------------------------------------------------
    // extractBotId
    // -----------------------------------------------------------------

    public function testExtractsBareId()
    {
        $this->assertSame(self::ID, LoaderHelper::extractBotId(self::ID));
    }

    public function testExtractsIdSurroundedByWhitespace()
    {
        $this->assertSame(self::ID, LoaderHelper::extractBotId("  \t" . self::ID . " \n"));
    }

    public function testExtractsIdFromFullEmbedSnippet()
    {
        $snippet = '<script>window.ConferbotWidget("' . self::ID . '", "live_chat", {});</script>';
        $this->assertSame(self::ID, LoaderHelper::extractBotId($snippet));
    }

    public function testExtractsIdFromDashboardUrl()
    {
        $url = 'https://app.conferbot.com/bots/' . self::ID . '/share?tab=embed';
        $this->assertSame(self::ID, LoaderHelper::extractBotId($url));
    }

    public function testExtractsUppercaseHexId()
    {
        $upper = strtoupper(self::ID);
        $this->assertSame($upper, LoaderHelper::extractBotId('id: ' . $upper));
    }

    public function testExtractReturnsEmptyStringWhenNothingMatches()
    {
        $this->assertSame('', LoaderHelper::extractBotId('paste your bot id here'));
        $this->assertSame('', LoaderHelper::extractBotId(''));
        $this->assertSame('', LoaderHelper::extractBotId('g4a1b2c3d4e5f6a7b8c9d0zz'));
    }

    // -----------------------------------------------------------------
    // isValidBotId
    // -----------------------------------------------------------------

    public function testValidationAcceptsCanonicalId()
    {
        $this->assertTrue(LoaderHelper::isValidBotId(self::ID));
        $this->assertTrue(LoaderHelper::isValidBotId(strtoupper(self::ID)));
    }

    public function testValidationIsStrictAboutLengthAndAlphabet()
    {
        $this->assertFalse(LoaderHelper::isValidBotId(substr(self::ID, 0, 23)), 'too short');
        $this->assertFalse(LoaderHelper::isValidBotId(self::ID . 'a'), 'too long');
        $this->assertFalse(LoaderHelper::isValidBotId('g' . substr(self::ID, 1)), 'non-hex character');
        $this->assertFalse(LoaderHelper::isValidBotId(''), 'empty');
    }

    public function testValidationRejectsIdEmbeddedInOtherText()
    {
        $this->assertFalse(LoaderHelper::isValidBotId(' ' . self::ID));
        $this->assertFalse(LoaderHelper::isValidBotId('id=' . self::ID));
        $this->assertFalse(LoaderHelper::isValidBotId('x' . self::ID));
        $this->assertFalse(LoaderHelper::isValidBotId(self::ID . ' '));
    }

    public function testDemoBotIdIsValidAndSurvivesExtraction()
    {
        $this->assertTrue(LoaderHelper::isValidBotId(LoaderHelper::DEMO_BOT_ID));
        $this->assertSame(LoaderHelper::DEMO_BOT_ID, LoaderHelper::extractBotId(LoaderHelper::DEMO_BOT_ID));
    }

    // -----------------------------------------------------------------
    // normaliseEmbedType
    // -----------------------------------------------------------------

    public function testEmbedTypeNormalisation()
    {
        $this->assertSame('live_chat', LoaderHelper::normaliseEmbedType('live_chat'));
        $this->assertSame('popup_chat', LoaderHelper::normaliseEmbedType('popup_chat'));
        $this->assertSame('live_chat', LoaderHelper::normaliseEmbedType('fullscreen'));
        $this->assertSame('live_chat', LoaderHelper::normaliseEmbedType(''));
    }

    // -----------------------------------------------------------------
    // buildOptions
    // -----------------------------------------------------------------

    private function site()
    {
        return array('name' => 'My Joomla Site', 'url' => 'https://example.org/', 'language' => 'en-GB');
    }

    public function testBuildOptionsForGuestHasNoUserKey()
    {
        $options = LoaderHelper::buildOptions($this->site(), null);

        $this->assertArrayNotHasKey('user', $options);
        $this->assertSame('joomla', $options['customData']['platform']);
        $this->assertSame('My Joomla Site', $options['customData']['siteName']);
        $this->assertSame('https://example.org/', $options['customData']['siteUrl']);
        $this->assertSame('en-GB', $options['customData']['siteLanguage']);
    }

    public function testBuildOptionsForLoggedInUser()
    {
        $options = LoaderHelper::buildOptions(
            $this->site(),
            array('id' => 42, 'name' => 'Ada Lovelace', 'email' => 'ada@example.org')
        );

        $this->assertSame('joomla-42', $options['user']['id']);
        $this->assertSame('Ada Lovelace', $options['user']['name']);
        $this->assertSame('ada@example.org', $options['user']['email']);
        $this->assertSame('joomla', $options['customData']['platform']);
    }

    public function testBuildOptionsTreatsUserWithoutIdAsGuest()
    {
        $options = LoaderHelper::buildOptions($this->site(), array('id' => 0, 'name' => 'Ghost'));
        $this->assertArrayNotHasKey('user', $options);

        $options = LoaderHelper::buildOptions($this->site(), array('name' => 'No Id'));
        $this->assertArrayNotHasKey('user', $options);
    }

    public function testBuildOptionsToleratesMissingSiteKeys()
    {
        $options = LoaderHelper::buildOptions(array());

        $this->assertSame('', $options['customData']['siteName']);
        $this->assertSame('', $options['customData']['siteUrl']);
        $this->assertSame('', $options['customData']['siteLanguage']);
        $this->assertSame('joomla', $options['customData']['platform']);
    }

    // -----------------------------------------------------------------
    // toJson - script safety
    // -----------------------------------------------------------------

    public function testJsonNeutralisesScriptBreakoutInUserName()
    {
        $options = LoaderHelper::buildOptions(
            $this->site(),
            array('id' => 7, 'name' => '</script><script>alert(1)</script>', 'email' => 'x@example.org')
        );
        $json = LoaderHelper::toJson($options);

        $this->assertStringNotContainsString('</script>', $json);
        $this->assertStringNotContainsString('<', $json);
        $this->assertStringNotContainsString('>', $json);

        $decoded = json_decode($json, true);
        $this->assertSame('</script><script>alert(1)</script>', $decoded['user']['name'], 'value round-trips intact');
    }

    public function testJsonEscapesQuotesAmpersandsAndApostrophes()
    {
        $json = LoaderHelper::toJson(array('customData' => array('siteName' => 'Bob\'s "Fish" & Chips')));

        $this->assertStringNotContainsString("'", $json);
        $this->assertStringNotContainsString('&', $json);
        $this->assertStringContainsString(trim(json_encode("'", JSON_HEX_APOS), '"'), $json, 'apostrophe hex-escaped');
        $this->assertStringContainsString(trim(json_encode('&', JSON_HEX_AMP), '"'), $json, 'ampersand hex-escaped');
        $this->assertStringContainsString(trim(json_encode('"', JSON_HEX_QUOT), '"'), $json, 'double quote hex-escaped');
    }

    // -----------------------------------------------------------------
    // render
    // -----------------------------------------------------------------

    public function testRenderContainsAllContractPieces()
    {
        $optionsJson = LoaderHelper::toJson(LoaderHelper::buildOptions($this->site()));
        $html = LoaderHelper::render(self::ID, 'popup_chat', LoaderHelper::DEFAULT_SCRIPT_URL, $optionsJson);

        $this->assertStringContainsString(self::ID, $html);
        $this->assertStringContainsString('popup_chat', $html);
        $this->assertStringContainsString('https://cdn.conferbot.com/dist/v1/widget.min.js', $html);
        $this->assertStringContainsString("'conferbot-js'", $html);
        $this->assertStringContainsString('window.ConferbotWidget(', $html);
        $this->assertStringContainsString('js.async = true;', $html);
        $this->assertStringContainsString($optionsJson, $html, 'options JSON inlined verbatim');
    }

    public function testRenderIsASingleScriptTag()
    {
        $html = LoaderHelper::render(self::ID, 'live_chat', LoaderHelper::DEFAULT_SCRIPT_URL, '{}');

        $this->assertStringStartsWith('<script', $html);
        $this->assertSame(1, substr_count($html, '</script>'));
        $this->assertStringContainsString('js.onload = function ()', $html);
    }

    public function testLoggedInUserIsSignedWithTheWorkspaceSecret()
    {
        $site = array('name' => 'Site', 'url' => 'https://s.example/', 'language' => 'en-GB');
        $user = array('id' => 42, 'name' => 'Jane', 'email' => 'jane@example.com');
        $options = LoaderHelper::buildOptions($site, $user, 's3cret');
        $this->assertSame(hash_hmac('sha256', 'joomla-42', 's3cret'), $options['user']['hash']);
        $this->assertArrayNotHasKey('hash', LoaderHelper::buildOptions($site, $user)['user']);
        $this->assertArrayNotHasKey('user', LoaderHelper::buildOptions($site, null, 's3cret'));
        $this->assertStringNotContainsString('s3cret', LoaderHelper::toJson($options));
    }
}
