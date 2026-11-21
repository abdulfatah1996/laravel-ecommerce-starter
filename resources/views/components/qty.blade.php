{{-- عدّاد كمية: أزرار ± بحجم لمس 48px؛ data-qty-autosubmit يرسل نموذج السلة تلقائيًا --}}
@props(['name' => 'quantity', 'value' => 1, 'min' => 1, 'max' => 20, 'id' => null, 'form' => null, 'autosubmit' => false, 'label' => 'الكمية'])
<div class="qty" data-qty @if ($autosubmit) data-qty-autosubmit @endif>
    <button type="button" data-qty-minus aria-label="إنقاص {{ $label }}"><span aria-hidden="true">−</span></button>
    <input @if ($id) id="{{ $id }}" @endif type="number" name="{{ $name }}" value="{{ $value }}" min="{{ $min }}" max="{{ $max }}" inputmode="numeric" aria-label="{{ $label }}" @if ($form) form="{{ $form }}" @endif>
    <button type="button" data-qty-plus aria-label="زيادة {{ $label }}"><span aria-hidden="true">+</span></button>
</div>
