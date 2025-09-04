<?php

namespace Djigir\LaravelFormBuilder\Tests;

use Orchestra\Testbench\TestCase;
use Djigir\LaravelFormBuilder\Facades\Form;
use Djigir\LaravelFormBuilder\FormServiceProvider;
use Illuminate\Support\HtmlString;

class FormBuilderTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [FormServiceProvider::class];
    }

    public function test_can_create_text_input()
    {
        $result = Form::text('username');
        
        $this->assertInstanceOf(HtmlString::class, $result);
        $this->assertStringContainsString('type="text"', (string) $result);
        $this->assertStringContainsString('name="username"', (string) $result);
    }

    public function test_can_create_form_open()
    {
        $result = Form::open(['action' => '/test']);
        
        $this->assertInstanceOf(HtmlString::class, $result);
        $this->assertStringContainsString('<form', (string) $result);
        $this->assertStringContainsString('action="/test"', (string) $result);
    }

    public function test_can_create_form_close()
    {
        $result = Form::close();
        
        $this->assertInstanceOf(HtmlString::class, $result);
        $this->assertEquals('</form>', (string) $result);
    }

    public function test_can_create_select()
    {
        $options = ['1' => 'Option 1', '2' => 'Option 2'];
        $result = Form::select('options', $options);
        
        $this->assertInstanceOf(HtmlString::class, $result);
        $this->assertStringContainsString('<select name="options">', (string) $result);
        $this->assertStringContainsString('<option value="1">Option 1</option>', (string) $result);
        $this->assertStringContainsString('<option value="2">Option 2</option>', (string) $result);
    }
}