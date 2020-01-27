<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Tests\Unit;

use PhpCfdi\CfdiSatScraper\HtmlForm;
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

        $this->assertCount(2, $elements);
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

        $expected = [
            'key' => 'value',
        ];

        $this->assertCount(1, $elements);
        $this->assertSame($expected, $elements);
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

        $expected = [
            'otherKey' => '',
        ];

        $this->assertCount(1, $elements);
        $this->assertSame($expected, $elements);
    }

    public function testReadAndGetValuesWithoutElement(): void
    {
        $form = '<form>';
        $form .= '<input name="key" value="value">';
        $form .= '<select name="otherKey">';
        $form .= '<option value="option1">option1</option>';
        $form .= '<option value="option2">option2</option>';
        $form .= '</select>';
        $form .= '</form>';

        $htmlForm = new HtmlForm($form, 'form');
        $elements = $htmlForm->readAndGetValues('textarea');

        $this->assertCount(0, $elements);
    }

    public function testReadAndGetValuesWithElement(): void
    {
        $form = '<form>';
        $form .= '<input name="key" value="myValue">';
        $form .= '</form>';

        $htmlForm = new HtmlForm($form, 'form');
        $elements = $htmlForm->readAndGetValues('input');

        $expected = [
            'key' => 'myValue',
        ];

        $this->assertCount(1, $elements);
        $this->assertSame($expected, $elements);
    }

    public function testReadAndGetValuesOutOfTheParentElement(): void
    {
        $form = '<form>';
        $form .= '</form>';
        $form .= '<input name="key" value="myValue">';

        $htmlForm = new HtmlForm($form, 'form');
        $elements = $htmlForm->readAndGetValues('input');

        $this->assertCount(0, $elements);
        $this->assertSame([], $elements);
    }
}
