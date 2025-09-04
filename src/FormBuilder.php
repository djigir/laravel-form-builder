<?php

namespace Djigir\LaravelFormBuilder;

use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class FormBuilder
{
    protected $model = null;
    protected $skipValueTypes = ['file', 'password', 'image'];

    /**
     * Open a form
     */
    public function open(array $options = []): HtmlString
    {
        $attributes = [];

        if (isset($options['route'])) {
            $route = $options['route'];
            $attributes['action'] = is_array($route) ? route($route[0], $route[1] ?? []) : route($route);
        } elseif (isset($options['url'])) {
            $attributes['action'] = url($options['url']);
        } elseif (isset($options['action'])) {
            $attributes['action'] = $options['action'];
        }

        $method = strtoupper($options['method'] ?? 'POST');
        $attributes['method'] = in_array($method, ['GET', 'POST']) ? $method : 'POST';

        if (isset($options['files']) && $options['files']) {
            $attributes['enctype'] = 'multipart/form-data';
        }

        if (isset($options['enctype'])) {
            $attributes['enctype'] = $options['enctype'];
        }

        // Копируем остальные атрибуты
        foreach ($options as $key => $value) {
            if (!in_array($key, ['route', 'url', 'action', 'method', 'files'])) {
                $attributes[$key] = $value;
            }
        }

        $html = '<form';
        foreach ($attributes as $key => $value) {
            $html .= ' ' . $key . '="' . e($value) . '"';
        }
        $html .= '>';

        // Add CSRF token for POST requests
        if ($attributes['method'] === 'POST') {
            $html .= csrf_field();
        }

        // Add method spoofing for PUT, PATCH, DELETE
        if (!in_array($method, ['GET', 'POST'])) {
            $html .= '<input type="hidden" name="_method" value="' . $method . '">';
        }

        return new HtmlString($html);
    }

    /**
     * Open a form with model binding
     */
    public function model($model, array $options = []): HtmlString
    {
        $this->model = $model;
        return $this->open($options);
    }

    /**
     * Close a form
     */
    public function close(): HtmlString
    {
        $this->model = null;
        return new HtmlString('</form>');
    }

    /**
     * Create a label
     */
    public function label(string $name, $value = null, array $options = []): HtmlString
    {
        $value = $value ?: $this->formatLabel($name);

        $html = '<label for="' . $name . '"';

        foreach ($options as $key => $attr) {
            if ($key !== 'for') {
                $html .= ' ' . $key . '="' . e($attr) . '"';
            }
        }

        $html .= '>' . e($value) . '</label>';

        return new HtmlString($html);
    }

    /**
     * Create a text input
     */
    public function text(string $name, $value = null, array $options = []): HtmlString
    {
        return $this->input('text', $name, $value, $options);
    }

    /**
     * Create an email input
     */
    public function email(string $name, $value = null, array $options = []): HtmlString
    {
        return $this->input('email', $name, $value, $options);
    }

    /**
     * Create a password input
     */
    public function password(string $name, array $options = []): HtmlString
    {
        return $this->input('password', $name, '', $options);
    }

    /**
     * Create a tel input
     */
    public function tel(string $name, $value = null, array $options = []): HtmlString
    {
        return $this->input('tel', $name, $value, $options);
    }

    /**
     * Create a number input
     */
    public function number(string $name, $value = null, array $options = []): HtmlString
    {
        return $this->input('number', $name, $value, $options);
    }

    /**
     * Create a date input
     */
    public function date(string $name, $value = null, array $options = []): HtmlString
    {
        return $this->input('date', $name, $value, $options);
    }

    /**
     * Create a datetime-local input
     */
    public function datetime(string $name, $value = null, array $options = []): HtmlString
    {
        return $this->input('datetime-local', $name, $value, $options);
    }

    /**
     * Create a datetime input (alias)
     */
    public function datetimeLocal(string $name, $value = null, array $options = []): HtmlString
    {
        return $this->datetime($name, $value, $options);
    }

    /**
     * Create a time input
     */
    public function time(string $name, $value = null, array $options = []): HtmlString
    {
        return $this->input('time', $name, $value, $options);
    }

    /**
     * Create a url input
     */
    public function url(string $name, $value = null, array $options = []): HtmlString
    {
        return $this->input('url', $name, $value, $options);
    }

    /**
     * Create a search input
     */
    public function search(string $name, $value = null, array $options = []): HtmlString
    {
        return $this->input('search', $name, $value, $options);
    }

    /**
     * Create a color input
     */
    public function color(string $name, $value = null, array $options = []): HtmlString
    {
        return $this->input('color', $name, $value, $options);
    }

    /**
     * Create a range input
     */
    public function range(string $name, $value = null, array $options = []): HtmlString
    {
        return $this->input('range', $name, $value, $options);
    }

    /**
     * Create a file input
     */
    public function file(string $name, array $options = []): HtmlString
    {
        return $this->input('file', $name, null, $options);
    }

    /**
     * Create a hidden input
     */
    public function hidden(string $name, $value = null, array $options = []): HtmlString
    {
        return $this->input('hidden', $name, $value, $options);
    }

    /**
     * Create a submit button
     */
    public function submit($value = null, array $options = []): HtmlString
    {
        $value = $value ?: 'Submit';

        $html = '<input type="submit" value="' . e($value) . '"';

        foreach ($options as $key => $attr) {
            $html .= ' ' . $key . '="' . e($attr) . '"';
        }

        $html .= '>';

        return new HtmlString($html);
    }

    /**
     * Create a button
     */
    public function button($value = null, array $options = []): HtmlString
    {
        $type = $options['type'] ?? 'button';
        unset($options['type']);

        $html = '<button type="' . $type . '"';

        foreach ($options as $key => $attr) {
            $html .= ' ' . $key . '="' . e($attr) . '"';
        }

        $html .= '>' . e($value) . '</button>';

        return new HtmlString($html);
    }

    /**
     * Create a textarea
     */
    public function textarea(string $name, $value = null, array $options = []): HtmlString
    {
        $value = $this->getValueAttribute($name, $value);

        $html = '<textarea name="' . $name . '"';

        foreach ($options as $key => $attr) {
            $html .= ' ' . $key . '="' . e($attr) . '"';
        }

        $html .= '>' . e($value ?? '') . '</textarea>';

        return new HtmlString($html);
    }

    /**
     * Create a select dropdown
     */
    public function select(string $name, array $options = [], $selected = null, array $attributes = []): HtmlString
    {
        $value = $this->getValueAttribute($name, $selected);

        $html = '<select name="' . $name . '"';

        foreach ($attributes as $key => $attr) {
            $html .= ' ' . $key . '="' . e($attr) . '"';
        }

        $html .= '>';

        foreach ($options as $optionValue => $optionLabel) {
            $isSelected = $this->isSelected($value, $optionValue) ? ' selected' : '';
            $html .= '<option value="' . e($optionValue) . '"' . $isSelected . '>' . e($optionLabel) . '</option>';
        }

        $html .= '</select>';

        return new HtmlString($html);
    }

    /**
     * Create a select range dropdown
     */
    public function selectRange(string $name, int $start, int $end, $selected = null, array $attributes = []): HtmlString
    {
        $options = [];
        for ($i = $start; $i <= $end; $i++) {
            $options[$i] = $i;
        }

        return $this->select($name, $options, $selected, $attributes);
    }

    /**
     * Create a select year dropdown
     */
    public function selectYear(string $name, int $startYear = null, int $endYear = null, $selected = null, array $attributes = []): HtmlString
    {
        $startYear = $startYear ?? (date('Y') - 100);
        $endYear = $endYear ?? (date('Y') + 10);

        $options = [];
        for ($year = $endYear; $year >= $startYear; $year--) {
            $options[$year] = $year;
        }

        return $this->select($name, $options, $selected, $attributes);
    }

    /**
     * Create a select month dropdown
     */
    public function selectMonth(string $name, $selected = null, array $attributes = []): HtmlString
    {
        $options = [];
        for ($month = 1; $month <= 12; $month++) {
            $options[$month] = date('F', mktime(0, 0, 0, $month, 1));
        }

        return $this->select($name, $options, $selected, $attributes);
    }

    /**
     * Create a checkbox input
     */
    public function checkbox(string $name, $value = 1, $checked = null, array $options = []): HtmlString
    {
        $isChecked = $this->getCheckedState($name, $value, $checked);

        $html = '';

        // Добавляем скрытое поле только если не передан параметр 'no-hidden'
        if (!isset($options['no-hidden'])) {
            $html .= '<input type="hidden" name="' . $name . '" value="0">';
        }
        unset($options['no-hidden']);

        $html .= '<input type="checkbox" name="' . $name . '" value="' . e($value) . '"';

        if ($isChecked) {
            $html .= ' checked';
        }

        foreach ($options as $key => $attr) {
            $html .= ' ' . $key . '="' . e($attr) . '"';
        }

        $html .= '>';

        return new HtmlString($html);
    }

    /**
     * Create a radio input
     */
    public function radio(string $name, $value, $checked = null, array $options = []): HtmlString
    {
        $isChecked = $this->getCheckedState($name, $value, $checked);

        $html = '<input type="radio" name="' . $name . '" value="' . e($value) . '"';

        if ($isChecked) {
            $html .= ' checked';
        }

        foreach ($options as $key => $attr) {
            $html .= ' ' . $key . '="' . e($attr) . '"';
        }

        $html .= '>';

        return new HtmlString($html);
    }

    /**
     * Create a generic input
     */
    protected function input(string $type, string $name, $value, array $options = []): HtmlString
    {
        if (!in_array($type, $this->skipValueTypes)) {
            $value = $this->getValueAttribute($name, $value);
        }

        $html = '<input type="' . $type . '" name="' . $name . '"';

        if (!in_array($type, $this->skipValueTypes) && $value !== null) {
            $html .= ' value="' . e($value) . '"';
        }

        foreach ($options as $key => $attr) {
            $html .= ' ' . $key . '="' . e($attr) . '"';
        }

        $html .= '>';

        return new HtmlString($html);
    }

    /**
     * Get the value attribute for an input
     */
    protected function getValueAttribute(string $name, $value = null)
    {
        if ($value !== null) {
            return $value;
        }

        // Check old input first (для валидации)
        if (old($name) !== null) {
            return old($name);
        }

        // Check model value
        if ($this->model) {
            return data_get($this->model, $name);
        }

        return null;
    }

    /**
     * Get the checked state for checkboxes and radios
     */
    protected function getCheckedState(string $name, $value, $checked = null): bool
    {
        if ($checked !== null) {
            return (bool)$checked;
        }

        // Check old input first
        $oldValue = old($name);
        if ($oldValue !== null) {
            return $oldValue == $value;
        }

        // Check model value
        if ($this->model) {
            $modelValue = data_get($this->model, $name);
            return $modelValue == $value;
        }

        return false;
    }

    /**
     * Check if option is selected
     */
    protected function isSelected($value, $optionValue): bool
    {
        if (is_array($value)) {
            return in_array((string)$optionValue, array_map('strval', $value));
        }

        return (string)$value === (string)$optionValue;
    }

    /**
     * Format label text from field name
     */
    protected function formatLabel(string $name): string
    {
        return Str::title(str_replace(['_', '-'], ' ', $name));
    }
}