{{-- حقل نموذج: التسمية + المحتوى + تلميح + رسالة الخطأ بجانب الحقل نفسه --}}
@props(['name', 'label', 'required' => false, 'hint' => null, 'for' => null])
@php($for = $for ?? $name)
<div {{ $attributes->class(['min-w-0']) }}>
    <label for="{{ $for }}" class="label">{{ $label }}@if ($required) <span aria-hidden="true" class="text-danger">*</span>@endif</label>
    {{ $slot }}
    @if ($hint && ! $errors->has($name))
        <p id="{{ $for }}-hint" class="hint">{{ $hint }}</p>
    @endif
    @error($name)
        <p id="{{ $for }}-error" class="field-error"><x-icon name="alert" class="mt-0.5 size-4 shrink-0" /><span>{{ $message }}</span></p>
    @enderror
</div>
