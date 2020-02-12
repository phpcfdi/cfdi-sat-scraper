<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Tests\Unit\Internal;

use PhpCfdi\CfdiSatScraper\Internal\HtmlForm;
use PhpCfdi\CfdiSatScraper\Tests\TestCase;

final class HtmlFormTest extends TestCase
{
    public function testGetFormValues(): void
    {
        $form = '<form>';
        $form .= '<input name="key" value="value">';
        $form .= '<select name="otherKey">';
        $form .= '<option value="option1">option1</option>';
        $form .= '<option value="option2">option2</option>';
        $form .= '</select>';
        $form .= '</form>';

        $htmlForm = new HtmlForm($form, 'form');
        $elements = $htmlForm->getFormValues();

        $expected = [
          'key' => 'value',
          'otherKey' => '',
        ];

        $this->assertSame($expected, $elements);
    }

    public function testReadInputValues(): void
    {
        $form = '<form>';
        $form .= '<input name="key" value="value">';
        $form .= '<select name="otherKey">';
        $form .= '<option value="option1">option1</option>';
        $form .= '<option value="option2">option2</option>';
        $form .= '</select>';
        $form .= '</form>';

        $htmlForm = new HtmlForm($form, 'form');
        $elements = $htmlForm->readInputValues();

        $this->assertSame(['key' => 'value'], $elements);
    }

    public function testReadSelectValues(): void
    {
        $form = '<form>';
        $form .= '<input name="key" value="value">';
        $form .= '<select name="otherKey">';
        $form .= '<option value="option1">option1</option>';
        $form .= '<option value="option2">option2</option>';
        $form .= '</select>';
        $form .= '</form>';

        $htmlForm = new HtmlForm($form, 'form');
        $elements = $htmlForm->readSelectValues();

        $this->assertSame(['otherKey' => ''], $elements);
    }

    public function testReadSelectValuesWithSelected(): void
    {
        $form = '<form>';
        $form .= '<input name="key" value="value">';
        $form .= '<select name="otherKey">';
        $form .= '<option value="option1">option1</option>';
        $form .= '<option value="option2" selected>option2</option>';
        $form .= '</select>';
        $form .= '</form>';

        $htmlForm = new HtmlForm($form, 'form');
        $elements = $htmlForm->readSelectValues();

        $this->assertSame(['otherKey' => 'option2'], $elements);
    }

    public function testReadSelectValuesWithExcluded(): void
    {
        $form = '<form>';
        $form .= '<select name="foo">';
        $form .= '<option value="x-foo" selected>x-foo</option>';
        $form .= '<option value="x-bar">x-bar</option>';
        $form .= '</select>';
        $form .= '<select name="bar">';
        $form .= '<option value="x-foo">x-foo</option>';
        $form .= '<option value="x-bar" selected>x-bar</option>';
        $form .= '</select>';
        $form .= '</form>';

        $htmlForm = new HtmlForm($form, 'form', ['/^foo$/']);
        $elements = $htmlForm->readSelectValues();

        $this->assertSame(['bar' => 'x-bar'], $elements);
    }

    public function testReadFormElementsValuesWithoutElement(): void
    {
        $form = '<form>';
        $form .= '<input name="key" value="value">';
        $form .= '<select name="otherKey">';
        $form .= '<option value="option1">option1</option>';
        $form .= '<option value="option2">option2</option>';
        $form .= '</select>';
        $form .= '</form>';

        $htmlForm = new HtmlForm($form, 'form');
        $elements = $htmlForm->readFormElementsValues('textarea');

        $this->assertCount(0, $elements);
    }

    public function testReadFormElementsValuesWithElement(): void
    {
        $form = '<form>';
        $form .= '<input name="key" value="myValue">';
        $form .= '</form>';

        $htmlForm = new HtmlForm($form, 'form');
        $elements = $htmlForm->readFormElementsValues('input');

        $this->assertSame(['key' => 'myValue'], $elements);
    }

    public function testReadFormElementsValuesOutOfTheParentElement(): void
    {
        $form = '<form>';
        $form .= '</form>';
        $form .= '<input name="key" value="myValue">';

        $htmlForm = new HtmlForm($form, 'form');
        $elements = $htmlForm->readFormElementsValues('input');

        $this->assertCount(0, $elements);
    }

    public function testReadInputValuesRadios(): void
    {
        $form = '<form>';
        $form .= '<input name="foo" type="radio" value="1">';
        $form .= '<input name="foo" type="radio" value="2" checked="checked">';
        $form .= '<input name="bar" type="radio" value="1" checked>';
        $form .= '<input name="bar" type="radio" value="2">';
        $form .= '</form>';

        $htmlForm = new HtmlForm($form, 'form');
        $elements = $htmlForm->readInputValues();
        $this->assertSame(['foo' => '2', 'bar' => '1'], $elements);
    }

    public function testReadFormElementsValuesIgnoringType(): void
    {
        $form = '<form>';
        $form .= '<input name="hide" type="hidden" value="x-hidden">';
        $form .= '<input name="show" type="text" value="x-text">';
        $form .= '</form>';

        $htmlForm = new HtmlForm($form, 'form');
        $elements = $htmlForm->readFormElementsValues('input', ['hidden']);

        $this->assertSame(['show' => 'x-text'], $elements);
    }

    public function testReadFormElementsValuesIgnoringNamePattern(): void
    {
        $form = '<form>';
        $form .= '<input name="ignore_1" value="">';
        $form .= '<input name="ignore_2" value="">';
        $form .= '<input name="no-ignore" value="">';
        $form .= '</form>';

        $htmlForm = new HtmlForm($form, 'form', ['/^ignore.+/']);
        $elements = $htmlForm->readFormElementsValues('input');

        $this->assertSame(['no-ignore' => ''], $elements);
    }
}
